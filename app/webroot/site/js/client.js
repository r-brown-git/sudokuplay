/**
 * Created by ASUS on 13.07.14.
 */
var client = {

    connect: function(host, auth, gameId) {
        //if (navigator.userAgent.toLowerCase().indexOf('chrome') != -1) {
        //    var socket = io.connect(host, {'transports': ['xhr-polling']});
        //else
            var socket = io.connect(host);

        socket.on('connect', function() {
            // при получении сообщения от сервера
            socket.on('message', function (msg) {
                if (msg.response == 'error') {

                } else if (msg.response == 'messageAdded') {
                    client.writeChat(msg.datetime, msg.userId, msg.login, msg.text);
                } else if (msg.response == 'cellFound') { // кто-то кроме меня нашел ячейку
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
                } else if (msg.response == 'playerEntered' && gameId == msg.gameId) { // только для тех, кто в этой игре
                    client.playerEnteredHandler(msg.userId, msg.login, msg.gamePoints);
                } else if (msg.response == 'playerLeave' && gameId == msg.gameId) {
                    client.playerLeavedHandler(msg.userId);
                }
            });

            // При нажатии <Enter> или кнопки отправляем текст
            $('#message-input').keydown(function(e) {
                if (e.which == '13') {
                    var text = $('#message-input').val().trim();
                    if (text) {
                        sendMessage('newMessage', {
                            'text':text
                        });
                        $('#message-input').val('');
                    }
                }
            });

            if (gameId) {
                // При подключении к сокету на странице игры, оповещаем сервер, что зашли в эту игру
                sendMessage('enteredTheGame', {
                    'gameId':gameId
                });

                // При нажатии кнопки значения отправляем запрос
                $('.buttons input').click(function() {
                    var cell = sudokuplay.selected;
                    var value = $(this).val();
                    if (cell != -1) {
                        sendMessage('checkCell', {
                            'gameId':gameId,
                            'cell':cell,
                            'value':value
                        });
                    } else {
                        alert("Выберите ячейку !");
                    }
                });
            }
        });

        // добавляем к каждому сообщению данные аутентификации
        var sendMessage = function(request, data) {
            data.request = request;
            data.auth = {
                'id': auth.id,
                'key': auth.key,
            }
            var string = JSON.stringify(data);
            socket.send(string);
        };
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
        client.showReward('Вы', reward);
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
        }
        if (guess == 'wrong' || guess == 'right') {
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
                <a class="chat-profile-link" href="/users/show/' + userid + '"><b>' + stripTags(userlogin) + '</b></a>\
            </div>\
            <div class="chat-message">' + stripTags(message) + '</div>\
        </div>';
        document.querySelector('#chat').scrollTop = document.querySelector('#chat').scrollHeight;
    },
}