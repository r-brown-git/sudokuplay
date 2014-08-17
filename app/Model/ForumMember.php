<?php
/**
 * User: Hw0xxAYM
 * Date: 17.08.14
 * Time: 22:09
 */
class ForumMember extends AppModel {
    public $useDbConfig = 'forum';
    public $useTable = 'members';
    public $tablePrefix = 'smf_';

    public function addMember($user) {
        $salt = substr(str_shuffle(md5(time())), 0, 4);
        $this->save(array(
            'id_member' => 0,
            'member_name' => $user['login'],
            'date_registered' => time(),
            'posts' => '0',
            'id_group' => '0',
            'lngfile' => '',
            'last_login' => time(),
            'real_name' => $user['login'],
            'instant_messages' => '0',
            'unread_messages' => '0',
            'new_pm' => '0',
            'buddy_list' => '',
            'pm_ignore_list' => '',
            'pm_prefs' => '0',
            'mod_prefs' => '',
            'message_labels' => '',
            'passwd' => sha1(strtolower($user['login']) . htmlspecialchars($salt)),
            'openid_uri' => '',
            'email_address' => '',
            'personal_text' => '',
            'gender' => '0',
            'birthdate' => '0001-01-01',
            'website_title' => '',
            'website_url' => '',
            'location' => '',
            'icq' => '',
            'aim' => '',
            'yim' => '',
            'msn' => '',
            'hide_email' => '1',
            'show_online' => '1',
            'time_format' => '%d %B %Y, %H:%M:%S',
            'signature' => '',
            'time_offset' => '0',
            'avatar' => '',
            'pm_email_notify' => '0',
            'karma_bad' => '0',
            'karma_good' => '0',
            'usertitle' => '',
            'notify_announcements' => '1',
            'notify_regularity' => '1',
            'notify_send_body' => '0',
            'notify_types' => '2',
            'member_ip' => env('REMOTE_ADDR'),
            'member_ip2' => env('REMOTE_ADDR'),
            'secret_question' => '',
            'secret_answer' => '',
            'id_theme' => '1',
            'is_activated' => '1',
            'validation_code' => '',
            'id_msg_last_visit' => '0',
            'additional_groups' => '',
            'smiley_set' => '',
            'id_post_group' => '4',
            'total_time_logged_in' => '0',
            'password_salt' => $salt,
            'ignore_boards' => '',
            'warning' => '0',
            'passwd_flood' => '',
            'pm_receive_from' => '1',
        ));
    }

}