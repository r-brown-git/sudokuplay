<?php
class GoogleAuthComponent extends Component {
    const CLIENT_ID = '581782785406.apps.googleusercontent.com';
    const EMAIL_ADDRESS = '581782785406@developer.gserviceaccount.com';
    const CLIENT_SECRET = 'm93r2V4rR-c0Fou8HpdoO8Jc';
    const REDIRECT_URI = 'http://sudokuplay.ru/users/login';

    public function getLink() {
        $params = array(
            'redirect_uri'  => self::REDIRECT_URI,
            'response_type' => 'code',
            'client_id'     => self::CLIENT_ID,
            'scope'         => 'https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile',
        );

        $result = 'https://accounts.google.com/o/oauth2/auth' . '?' . urldecode(http_build_query($params));
    }

    /*protected function _getToken($code) {
        $params = array(
            'client_id' => self::CLIENT_ID,
            'client_secret' => self::CLIENT_SECRET,
            'code' => $code,
            'redirect_uri' => self::REDIRECT_URI
        );

        $streamContext = stream_context_create([
            'http' => [
                'ignore_errors' => true,
            ],
        ]);
        $answer = file_get_contents('https://oauth.vk.com/access_token' . '?' . urldecode(http_build_query($params)), false, $streamContext);
        $token = json_decode($answer, true);
        if (!isset($token['access_token'])) {
            return false;
        }
        return $token;
    }

    public function getUserInfo($code) {
        $token = $this->_getToken($code);
        $result = false;
        if ($token) {
            $params = array(
                'uids'         => $token['user_id'],
                'fields'       => 'uid,first_name,nickname,last_name,sex,bdate,photo_big',
                'access_token' => $token['access_token'],
            );

            $streamContext = stream_context_create([
                'http' => [
                    'ignore_errors' => true,
                ],
            ]);
            $answer = file_get_contents('https://api.vk.com/method/users.get' . '?' . urldecode(http_build_query($params)), false, $streamContext);
            $userInfo = json_decode($answer, true);
            if (isset($userInfo['response'][0]['uid'])) {
                $result = $userInfo['response'][0];
            }
        }
        return $result;
    }*/
}