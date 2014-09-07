/**
 * Created by ASUS on 29.06.14.
 */
var sudokuplay = {
    index: function() {
        $('#message-input').trigger('focus');

        $('#chat').on('click', '.chat-profile-link', function(e) {
            $('#message-input')
                .val( $(this).attr('data-login') + ', ' + $('#message-input').val() )
                .trigger('focus');
            return false;
        });
    },

    usersEdit: function() {
    },

    usersIndex: function() {
        $('.page-numbers').css('cursor', 'pointer');
        $('.page-numbers').click(function() {
            var a = $(this).find('a');
            if (a.length) {
                document.location.href = a.attr('href');
            }
        });
    },

    selected: -1, // значение выбранной ячейки (нужно также в client.js)
    myPoints: 0, // общая сумма баллов
    myMistakes: 0, // количество ошибок, допущенных в этой игре

    gamesShow: function(myPoints, myMistakes) {
        sudokuplay.myPoints = myPoints;
        sudokuplay.myMistakes = myMistakes;
        $('#my-points').text(myPoints);

        $('.game td').mouseover(function() {
            var id = $(this).attr('id').substring(1);
            $('#c'+id).addClass('hover');
        });
        $('.game td').mouseout(function() {
            var id = $(this).attr('id').substring(1);
            $('#c'+id).removeClass('hover');
        });
        $('.game td').click(function() {
            if ($(this).hasClass('empty')) {
                var id = $(this).attr('id').substring(1);
                if (sudokuplay.selected == id) {
                    $('#c'+id).removeClass('selected');
                    sudokuplay.selected = -1;
                } else {
                    $('#c'+id).removeClass('wrong').addClass('selected');
                    $('#c'+sudokuplay.selected).removeClass('selected');
                    sudokuplay.selected = id;
                }
            }
        });
    },

    // перерисовка блока "юзеры в игре" для игры
    renderOnlineUsers: function(onlineUsers) {
        // сортируем по очкам
        Array.prototype.sort.call(onlineUsers, function( a, b ) {
            return parseInt(a.points) < parseInt(b.points) ? 1 : parseInt(a.points) > parseInt(b.points) ? -1 : 0;
        });

        var html = '';
        for (var i in onlineUsers) {
            html += '<div class="online-user">\
                <div class="score">\
                    <span>' + onlineUsers[i].points + '</span>\
                </div>\
                <div class="player-content">\
                    <a class="player-hyperlink" href="/users/show/' + onlineUsers[i].id + '">' + stripTags(onlineUsers[i].login) + '</a>\
                </div>\
                <br class="cbt" />\
            </div>';
        }
        $('#online-users').html(html);
    },

    addToMarquee: function(message) {
        $('.inner-marquee').html(message);
    },

    marquee: function() {
        var span = $('.invisible-span').html($('.inner-marquee').html()).css({'display':'none'});
        var move = parseInt(span.css('width')) - parseInt($('.outer-marquee').css('width'));
        if (move > 0) {
            var doMove = function() {
                $('.inner-marquee').animate({
                    marginLeft: -1 * move + 'px',
                }, 800, function() {
                    setTimeout(function() {
                        $('.inner-marquee').animate({
                            marginLeft: '0px',
                        }, 800, function() {
                            froze = false;
                        });
                    }, 300);
                });
            }
            var froze = false;
            $('.marquee').on('mouseenter', function() {
                if (!froze) {
                    froze = true;
                    doMove();
                }
            });
        }
    },
}

 // original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
// Strip HTML and PHP tags from a string
var stripTags = function ( str ) {
    return str.replace(/<\/?[^>]+>/gi, '');
}