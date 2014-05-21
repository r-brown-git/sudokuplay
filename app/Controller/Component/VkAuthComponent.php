<?php
class VkAuthComponent extends Component {
    const CLIENT_ID = '4374131';
    const CLIENT_SECRET = 'vG14qxJWXfsKYv01VbPP';
    const REDIRECT_URI = 'http://sudokuplay/users/login/';
    const API_VERSION = '5.21';

    public function getLink() {
        $params = array(
            'client_id'     => self::CLIENT_ID,
            'redirect_uri'  => self::REDIRECT_URI,
            'response_type' => 'code',
            'v' => self::API_VERSION,
        );
        $result = 'http://oauth.vk.com/authorize' . '?' . urldecode(http_build_query($params));
        return $result;
    }

    protected function _getToken($code) {
        $params = array(
            'client_id' => self::CLIENT_ID,
            'client_secret' => self::CLIENT_SECRET,
            'code' => $code,
            'redirect_uri' => self::REDIRECT_URI
        );

        // TODO: не file_get_contents
        $token = json_decode(file_get_contents('https://oauth.vk.com/access_token' . '?' . urldecode(http_build_query($params))), true);
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
            $userInfo = json_decode(file_get_contents('https://api.vk.com/method/users.get' . '?' . urldecode(http_build_query($params))), true);
            if (isset($userInfo['response'][0]['uid'])) {
                $result = $userInfo['response'][0];
            }
        }
        return $result;
    }
}