<?php
class OkAuthComponent extends Component {
    const CLIENT_ID = '1090950656';
    const CLIENT_SECRET = '01AD4608E1BAEA22D5D852AF';
    const APPLICATION_KEY = 'CBAHJHACEBABABABA';

    public function getLink() {
        $params = array(
            'client_id'     => self::CLIENT_ID,
            'scope'         => 'SET_STATUS',
            'response_type' => 'code',
            'redirect_uri'  => 'http://'. $_SERVER['HTTP_HOST'] .'/users/login',
        );
        $result = 'http://www.odnoklassniki.ru/oauth/authorize?' . urldecode(http_build_query($params));
        return $result;
    }

    protected function _getToken($code) {
        $params = array(
            'code' => $code,
            'redirect_uri' => 'http://'. $_SERVER['HTTP_HOST'] .'/users/login',
            'grant_type' => 'authorization_code',
            'client_id' => self::CLIENT_ID,
            'client_secret' => self::CLIENT_SECRET,
        );

        $url = 'http://api.odnoklassniki.ru/oauth/token.do';

        $options = array(
            'http' => array(
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($params),
                'ignore_errors' => true,
            ),
        );

        $context = stream_context_create($options);

        $answer = file_get_contents($url, false, $context);

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
                'application_key' => self::APPLICATION_KEY,
                'fields' => 'UID,FIRST_NAME,LAST_NAME,GENDER,BIRTHDAY',
                'method' => 'users.getCurrentUser',
                'access_token' => $token['access_token'],
            );
            $params['sig'] = $this->_calculateSig($params);

            $context = stream_context_create(array(
                'http' => array(
                    'ignore_errors' => true,
                ),
            ));

            $answer = file_get_contents('http://api.odnoklassniki.ru/fb.do?' . urldecode(http_build_query($params)), false, $context);

            $userInfo = json_decode($answer, true);

            if (isset($userInfo['uid'])) {
                $result = array(
                    'service_user_id' => $userInfo['uid'],
                    'first_name' => $userInfo['first_name'],
                    'last_name' => $userInfo['last_name'],
                    'nickname' => '',
                    'sex' => $userInfo['gender'] == 'male' ? 'M' : ($userInfo['gender'] == 'female' ? 'F' : null),
                    'birthday' => $userInfo['birthday'],
                );
            }
        }
        return $result;
    }

    protected function _calculateSig($params) {
        $paramsNoToken = array_diff($params, ['access_token' => $params['access_token']]);
        ksort($paramsNoToken);
        $rawsig = '';
        foreach ($paramsNoToken as $key => $value) {
            $rawsig .= $key.'='.$value;
        }
        $rawsig .= md5($params['access_token'] . self::CLIENT_SECRET);
        $sig = md5($rawsig);
        return $sig;
    }
}