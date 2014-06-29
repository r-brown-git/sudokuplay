/**
 * Created by ASUS on 29.06.14.
 */
var sp = {
    profileEdit: function() {
        $('#change-picture').click(function() {
            //$('input-gravatar').
            $.post('/users/newgravatar', {}, function(e) {
                var key = '',
                    md5key = '';
                if (e.status == 'ok') {
                    key = e.key;
                    md5key = e.md5key;
                }
                if (key) {
                    $('.logo').attr('src', 'http://www.gravatar.com/avatar/' + md5key + '/?s=128&d=wavatar');
                } else {
                    $('.logo').attr('src', 'http://www.gravatar.com/avatar/?s=128&d=mm');
                }
                $('#input-gravatar').val(key);
            }, 'json');
            return false;
        });
    }
}