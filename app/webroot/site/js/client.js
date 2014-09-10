/**
 * Created by ASUS on 13.07.14.
 */
var client = {

    mySocketId: '',

    errorReasons : {
        'wrongField': 'Неверно указано поле. Хакер?',
        'gameDenied': 'Доступ к этой игре запрещен',
        'wrongAuth': 'Ошибка данных аутентификации',
        'wrongParams': 'Неверные параметры запроса',
    },

    connect: function(host, auth, params) {

        // обязательный параметр method
        var sendMessage = function(method, data) {
            data.method = method;
            if (client.mySocketId) {
                data.clientSocketId = client.mySocketId;
            }
            var string = JSON.stringify(data);
            socket.send(string);
        };

        var socket = io.connect(host);

        socket.on('connect', function() {

            // при подключении отправляем данные аутентификации
            if (auth.id && auth.key) {
                sendMessage('auth', {
                    'id': auth.id,
                    'key': auth.key,
                    'gameId': params.gameId ? params.gameId : 0,
                });
            }

            // при получении сообщения от сервера
            socket.on('message', function (msg) {
                if (msg.response == 'auth') {
                    if (msg.authorized == 'true') {
                        client.mySocketId = msg.socketId;
                    }
                } else if (msg.response == 'messageAdded') {
                    client.writeChat(msg.datetime, msg.userId, msg.login, msg.text);
                } else if (msg.response == 'cellFound' && params.gameId == msg.gameId) { // кто-то кроме меня нашел ячейку
                    client.cellFoundHandler(msg.guess, msg.cell, msg.value, msg.userId, msg.login, msg.reward);
                    if (msg.finish) {
                        client.gameFinishHandler(msg.login);
                    }
                    if (msg.ban) {
                        client.gameBanHandler(msg.login);
                    }
                } else if (msg.response == 'cellChecked') { // ответ на мой запрос значения ячейки
                    client.cellCheckedHandler(msg.guess, msg.cell, msg.value, msg.reward);
                    if (msg.finish) {
                        client.gameFinishHandler(null);
                    }
                    if (msg.ban) {
                        client.gameBanHandler(null);
                    }
                } else if (msg.response == 'playerEntered' && params.gameId == msg.gameId) { // только для тех, кто в этой игре
                    client.playerEnteredHandler(msg.userId, msg.login, msg.gamePoints);
                } else if (msg.response == 'playerLeave' && params.gameId == msg.gameId) {
                    client.playerLeavedHandler(msg.userId);
                } else if (msg.response == 'error') {
                    console.log(msg);
                    if (typeof(client.errorReasons[msg.reason]) !== 'undefined') {
                        sudokuplay.addToMarquee(client.errorReasons[msg.reason]);
                    }
                }
            });

            // При нажатии <Enter> или кнопки отправляем текст
            $('#message-input').keydown(function(e) {
                if (e.which == '13') {
                    var text = $('#message-input').val().trim();
                    if (text) {
                        sendMessage('writeChat', {
                            'text':text
                        });
                        $('#message-input').val('');
                    }
                }
            });

            if (params.gameId) {

                // При нажатии кнопки значения отправляем запрос
                $('.buttons input').click(function() {
                    var cell = sudokuplay.selected;
                    if (cell != -1) {
                        sendMessage('checkCell', {
                            'cell': cell,
                            'value': $(this).val(),
                        });
                    } else {
                        alert("Выберите ячейку !");
                    }
                });

                // При нажатии цифры на клавиатуре тоже отправляем запрос
                $(document).keydown(function(e) {
                    var cell = sudokuplay.selected;
                    if (cell != -1 && e.which >= 48 && e.which <= 57) {
                        sendMessage('checkCell', {
                            'cell': cell,
                            'value': String.fromCharCode(e.which),
                        });
                    }
                });
            }
        });
    },

    showReward: function(nickname, reward) {
        var message = nickname + ': ' + (reward < 0 ? '' : '+') + Math.round(reward);
        if (reward > 100) {
            message += '!';
        }
        sudokuplay.addToMarquee(message);
    },

    cellCheckedHandler: function(guess, cell, value, reward) {
        $('#c' + cell).removeClass('selected');
        sudokuplay.selected = -1;
        if (guess == 'wrong') {
            $('#c' + cell).addClass('wrong');
            setTimeout(function() {
                $('#c' + cell).removeClass('wrong');
            }, 1000);
            $('#mistakes-count').text(++sudokuplay.myMistakes);
        }
        if (guess == 'right' || guess == 'alreadyFound') {
            $('#c' + cell).removeClass('empty').addClass('found');
            $('#c' + cell).text(value);
            if (guess == 'alreadyFound') {
                sudokuplay.addToMarquee('Кто-то уже нашел эту ячейку');
            }
        }
        if (guess == 'wrong' || guess == 'right') {
            client.showReward('Вы', reward);
            sudokuplay.myPoints += Math.round(reward);
            $('#my-points').text(sudokuplay.myPoints);
            var i, n = onlineUsers.length,
                needRefresh = false;
            for (i = 0; i < n; i++) {
                if (onlineUsers[i].id == auth.id) {
                    onlineUsers[i].points = parseInt(onlineUsers[i].points) + Math.round(reward);
                    needRefresh = true;
                }
            }
            if (needRefresh) {
                sudokuplay.renderOnlineUsers(onlineUsers);
            }
        }
    },

    gameFinishHandler: function(login) {
        var message;
        if (!login) {
            message = 'Вы завершили';
        } else {
            message = login + ' завершил';
        }
        message += ' игру';
        sudokuplay.addToMarquee(message);
    },

    gameBanHandler: function(login) {
        var message;
        if (!login) {
            message = 'Вы забанены';
            $('.game td').unbind('click');
        } else {
            message = login + ' забанен';
        }
        message += ' в этой игре';
        sudokuplay.addToMarquee(message);
    },

    cellFoundHandler: function(guess, cell, value, userId, login, reward) {
        if (guess == 'right') {
            $('#c' + cell).removeClass('selected');
            sudokuplay.selected = -1;
            client.showReward(login, reward);
            $('#c' + cell).removeClass('empty').addClass('found');
            $('#c' + cell).text(value);
        }
        var i, n = onlineUsers.length,
            needRefresh = false;
        for (i = 0; i < n; i++) {
            if (onlineUsers[i].id == userId) {
                onlineUsers[i].points = parseInt(onlineUsers[i].points) + Math.round(reward);
                needRefresh = true;
            }
        }
        if (needRefresh) {
            sudokuplay.renderOnlineUsers(onlineUsers);
        }
    },

    playerEnteredHandler: function(userId, login, gamePoints) {
        // в гугл хроме и, возможно, где-то еще событие disconnect не срабатывает и юзер еще некоторое время
        // отмечен как онлайн. Чтобы не добавить его 2й раз, предварительно ищем юзера в списке
        var found = false,
            i,
            n = onlineUsers.length;

        for (i = 0; i < n; i++) {
            if (onlineUsers[i].id == userId) {
                found = true;
            }
        }
        if (!found) {
            onlineUsers.push({'id': userId, 'login': login, 'points': gamePoints});
            sudokuplay.renderOnlineUsers(onlineUsers);
        }
    },

    playerLeavedHandler: function(userId) {
        var shift = false, // надо сдвигать
            i,
            n = onlineUsers.length;

        for (i = 0; i < n; i++) {
            if (onlineUsers[i].id == userId) {
                shift = true;
            }
            if (shift && i != n - 1) { // если юзер не последний в списке
                onlineUsers[i] = onlineUsers[i + 1]; // сдвигаем вверх
            }
        }
        if (shift) {
            onlineUsers.pop(); // удаляем последний повторяющийся элемент
            sudokuplay.renderOnlineUsers(onlineUsers);
        }
    },

    writeChat: function(datetime, userid, userlogin, message) {
        var datetime = datetime.split(' ');
        document.querySelector('#chat').innerHTML += '\
        <div class="chat-block">\
            <div class="chat-time" title="' + datetime[0].replace(/-/g, '.') + ' ' + datetime[1] + '">' + datetime[1] + '</div>\
            <div class="chat-profile">\
                <img class="chat-profile-icon" src="/site/images/chat_profile_icon.png">\
                <a class="chat-profile-link" data-login="' + stripTags(userlogin) + '"><b>' + stripTags(userlogin) + '</b></a>\
            </div>\
            <div class="chat-message">' + stripTags(message) + '</div>\
        </div>';
        document.querySelector('#chat').scrollTop = document.querySelector('#chat').scrollHeight;
    },
}