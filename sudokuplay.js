
var io      = require('socket.io').listen(8080),
    crypto  = require('crypto'),
    fs      = require('fs'),
    mysql   = require('mysql');

io.set('log level', 1);

var apppath = 'e:/serv/sudokuplay/www/app/';

var connection = mysql.createConnection({
  host     : 'localhost',
  user     : 'root',
  password : 'toor',
  charset  : 'utf8_general_ci',
  database : 'sudokuplay',
});
connection.connect();

io.sockets.on('connection', function(socket) {

    var players = [];

    var socketJsonSend = function(response, object) {
        object.response = response;
        object.datetime = getDateSql();
        socket.json.send(object);
    }

    var socketBroadcastJsonSend = function(response, object) {
        object.response = response;
        object.datetime = getDateSql();
        socket.broadcast.json.send(object);
    }

    socket.on('message', function (messageStr) {
        var msg = JSON.parse(messageStr),
            userId = msg.auth.id,
            login = '',
            points = 0,
            authorized = false,
            datesql = getDateSql(),
            useragent = socket.handshake.headers['user-agent'],
            ipaddress = socket.handshake.address.address;

        connection.query(
            "SELECT sus.id, sus.hash as hash, su.login as login, su.points as points FROM sud_users_sessions sus " +
            "LEFT JOIN sud_users su ON sus.user_id = su.id " + 
            "WHERE sus.user_id=? AND sus.ip=? AND sus.user_agent=? LIMIT 0,1", [userId, ipaddress, useragent],
            function(err, rows) {
                if (err) throw err;
                checkAuth(rows);
            }
        );

        var checkAuth = function(rows) {
            if (rows.length > 0) {
                if (msg.auth.key == crypto.createHash('md5').update(rows[0].hash).digest('hex')) {
                    authorized = true;
                }
            }
            if (authorized) {
                // обновляем статус онлайн у юзера
                connection.query("UPDATE sud_users_sessions SET last_connect = NOW() WHERE id = ?", [rows[0].id], function(err, rows) {
                    if (err) throw err;
                });
                login = rows[0].login;
                points = rows[0].points;
                handleMessage();
            } else {
                socketJsonSend('error', datesql, {'message': 'notAuthorized'});
            }
        }

        var handleMessage = function() {
            if (msg.request == 'newMessage') { // новое сообщение в чат
                var text = msg.text;
                connection.query(
                    "INSERT INTO sud_chat_messages (date, user_id, message) VALUES(NOW(), ?, ?)", [userId, msg.text],
                    function(err, rows) {
                        if (err) throw err;
                        socketJsonSend('messageAdded', {'userId': userId, 'login': login, 'text': text});
                        socketBroadcastJsonSend('messageAdded', {'userId': userId, 'login': login, 'text': text});
                    }
                );
            } else if (msg.request == 'checkCell') { // попытка заполнить ячейку
                var gameId = msg.gameId,
                    cell = msg.cell,
                    value = msg.value;
                // проверяем разрешено ли юзеру играть в этой игре, выбрав при этом поля игры
                connection.query(
                    "SELECT sg.all_table, sg.pos_unknown, sg.ratio, sg.mistake_cost, sg.mistakes_max, sgu.mistakes, " +
                        "UNIX_TIMESTAMP(NOW())-UNIX_TIMESTAMP(sg.game_begin) as diff, " +
                        "UNIX_TIMESTAMP(NOW())-UNIX_TIMESTAMP(IF(TIME(sgu.last_found),sgu.last_found,sg.game_begin)) as guessTime " +
                        "FROM sud_games_users sgu " +
                        "LEFT JOIN sud_games sg ON sgu.game_id = sg.id " +
                        "WHERE sgu.game_id=? AND sgu.user_id=? AND sgu.active=1 LIMIT 0,1", [gameId, userId],
                    function(err, rows) {
                        if (err) throw err;
                        if (rows.length > 0) {
                            isAllCorrect(rows[0]);
                        } else {
                            socketJsonSend('error', {'reason':'accessDeny'});
                        }
                    }
                );

                var isAllCorrect = function(row) {
                    // если ли ячейка в списке тех, которые надо угадать
                    var posUnknown = row.pos_unknown,
                        posFound = [],
                        allTable = row.all_table,
                        guessTime = row.guessTime == 0 ? 1 : row.guessTime,
                        diff = row.diff,
                        n = row.pos_unknown.length,
                        ratio = row.ratio,
                        mistakeCost = row.mistake_cost,
                        mistakesMax = row.mistakes_max,
                        mistakes = row.mistakes,
                        isUnknown = false;

                    for (var i=0; i<n; i+=2) {
                        if (parseInt(posUnknown[i] + posUnknown[i+1]) == cell) {
                            isUnknown = true;
                            break;
                        }
                    }
                    if (isUnknown) {
                        // не угадал ли кто-то ячейку до нас
                        var gameFile = apppath + 'webroot/games_data/_'+(gameId % 10)+'/'+gameId+'.txt';
                        fs.readFile(gameFile, {'encoding':'utf8'}, function(err, data) {
                            if (err) throw err;
                            posFound = data.split("\n");
                            var alreadyFound = false;
                            for (var i in posFound) {
                                if (posFound[i].split(':')[0] == cell) {
                                    alreadyFound = true;
                                    break;
                                }
                            }
                            if (!alreadyFound) {
                                updateCell();
                            } else {
                                socketJsonSend('cellChecked', {'guess': 'alreadyFound', 'cell': cell, 'value': value, 'reward': 0});
                            }
                        });
                    } else {
                        socketJsonSend('error', {'reason':'wrongField'});
                    }

                    var updateCell = function() {
                        var addPoints = 0;

                        var isCorrect = allTable.substring(cell, parseInt(cell) + 1) == value;
                        var isEnd = false;

                        if (isCorrect) {
                            fs.appendFile(gameFile, cell + ':' + userId + ':' + diff + '\n', function(err) {
                                if (err) throw err;
                            });

                            // проверяем не завершена ли этим ходом игра (так правильно и + 1 к posFound прибавлять не надо)
                            isEnd = posFound.length == posUnknown.length / 2;

                            var bonus = guessTime <= 100 ? 100/guessTime : 0;
                            addPoints = ratio * (bonus + 10);
                        } else {
                            addPoints = -1 * mistakeCost;
                            mistakes++;
                        }

                        if (isEnd) {
                            connection.query(
                                "UPDATE sud_games SET game_end = NOW() WHERE id=?", gameId,
                                function(err, rows) {
                                    if (err) throw err;
                                }
                            );
                            connection.query(
                                "UPDATE sud_games_users sgu LEFT JOIN sud_users su ON sgu.user_id = su.id " +
                                "SET sgu.active = 0, su.luck = get_luck(sgu.game_id, sgu.user_id) " +
                                "WHERE sgu.game_id = ?", [gameId],
                                function(err, rows) {
                                    if (err) throw err;
                                }
                            );
                        }

                        var fields = ['points = points + (?)'];
                        if (isCorrect) {
                            fields.push('last_found = NOW()');
                        } else {
                            fields.push('mistakes = mistakes+1');
                            if (mistakes > mistakesMax) {
                                fields.push('banned = 1');
                            }
                        }

                        connection.query(
                            'UPDATE sud_games_users SET ' + fields.join(', ') + ' WHERE game_id=? AND user_id=?', [Math.round(addPoints), gameId, userId],
                            function(err, rows) {
                                if (err) throw err
                            }
                        );

                        connection.query(
                            "UPDATE sud_users SET points = points + (?) WHERE id=?", [addPoints, userId],
                            function(err, rows) {
                                if (err) throw err;
                            }
                        );

                        var sendingData = {
                            'guess': isCorrect ? 'right' : 'wrong', // может быть также alreadyFound
                            'finish': isEnd,
                            'ban': (mistakes > mistakesMax),
                            'cell': cell, 
                            'value': value, 
                            'reward': addPoints,
                        };
                        
                        socketJsonSend('cellChecked', sendingData);
                        
                        sendingData['userId'] = userId;
                        sendingData['login'] = login;
                        
                        socketBroadcastJsonSend('cellFound', sendingData);
                    }
                }
            } else if (msg.request == 'enteredTheGame') { // оповестил, что зашел в игру
                var gameId = msg.gameId;
                // проверяем разрешено ли юзеру играть в этой игре
                connection.query(
                    "SELECT sgu.points FROM sud_games_users sgu " +
                        "WHERE sgu.game_id=? AND sgu.user_id=? AND sgu.active=1 LIMIT 0,1", [gameId, userId],
                    function(err, rows) {
                        if (err) throw err;
                        if (rows.length > 0) {
                            // оповещаем остальных игроков, только бродкаст
                            socketBroadcastJsonSend('playerEntered', {'userId': userId, 'login': login, 'gamePoints': rows[0].points, 'gameId': gameId});
                            players[socket.id] = {'userId': userId, 'gameId': gameId};
                        } else {
                            socketJsonSend('error', {'reason':'accessDeny'});
                        }
                    }
                );
            }
        }
	});

    // если игрок зашел и к игре есть доступ, то делаем возможным событие выхода
    socket.on('disconnect', function() {
        var player = players[socket.id];
        if (typeof(player) === 'object') {
            connection.query("UPDATE sud_games_users sgu SET sgu.active = 0 WHERE game_id=? AND user_id=?", [player.gameId, player.userId],
                function(err, rows) {
                    if (err) throw err;
                }
            );
            socketBroadcastJsonSend('playerLeave', {'userId': player.userId, 'gameId': player.gameId});
        }
    });
});


var getDateSql = function() {
    var date;
    date = new Date();
    date = date.getUTCFullYear() + '-' +
        ('00' + (date.getUTCMonth()+1)).slice(-2) + '-' +
        ('00' + date.getUTCDate()).slice(-2) + ' ' +
        ('00' + date.getUTCHours()).slice(-2) + ':' +
        ('00' + date.getUTCMinutes()).slice(-2) + ':' +
        ('00' + date.getUTCSeconds()).slice(-2);
    return date;
}