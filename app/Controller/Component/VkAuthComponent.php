<?php
class VkAuthComponent extends Component {
    const CLIENT_ID = '4374131';
    const CLIENT_SECRET = 'vG14qxJWXfsKYv01VbPP';
    const REDIRECT_URI = 'http://sudokuplay.ru/users/login/';
    const API_VERSION = '5.21';

    public function getLink() {
        $params = array(
            'client_id'     => self::CLIENT_ID,
            'redirect_uri'  => self::REDIRECT_URI,
            'response_type' => 'code',
            'v' => self::API_VERSION,
        );
        $result = 'http://oauth.vk.com/authorize?' . urldecode(http_build_query($params));
        return $result;
    }

    protected function _getToken($code) {
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

    /**
     * service_user_id
     * first_name
     * last_name
     * nickname
     * sex
     * birthday
     */
    public function getUserInfo($code) {
        $token = $this->_getToken($code);
        $result = false;
        if ($token) {
            $params = array(
                'uids'         => $token['user_id'],
                'fields'       => 'uid,first_name,nickname,last_name,sex,bdate',
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
                $result = [
                    'service_user_id' => $userInfo['response'][0]['uid'],
                    'first_name' => $userInfo['response'][0]['first_name'],
                    'last_name' => $userInfo['response'][0]['last_name'],
                    'nickname' => $userInfo['response'][0]['nickname'],
                    'sex' => $userInfo['response'][0]['sex'] == 2 ? 'M' : ($userInfo['response'][0]['sex'] == 1 ? 'F' : null),
                    'birthday' => date(DATE_SQL, strtotime($userInfo['response'][0]['birthday'])),
                ];
            }
        }
        return $result;
    }
}