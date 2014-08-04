<?php
/**
 * User: Hw0xxAYM
 * Date: 15.07.14
 * Time: 17:19
 */
class UsersCookieComponent extends Component {

    const USID_LIFETIME = '1 day'; // сессия живет столько с момента последнего действия

    public $controller;

    public $uses = array(
        'User',
        'UsersSession',
    );

    public $components = array(
        'Cookie',
    );

    public function initialize(Controller $controller) {
        $this->controller = $controller;
        foreach ($this->uses as $aModel) {
            $this->controller->loadModel($aModel);
        }
        foreach ($this->components as $aComponent) {
            $this->controller->{$aComponent} = $this->controller->Components->load($aComponent);
        }
    }

    // при логауте юзера убиваем сессию на текущем устройстве
    public function deleteUsid($userId) {
        $this->controller->UsersSession->deleteAll(array(
            'UsersSession.user_id' => $userId,
            'UsersSession.ip' => env('REMOTE_ADDR'),
            'UsersSession.user_agent' => env('HTTP_USER_AGENT'),
        ));
        $this->controller->Cookie->delete('usid');
    }

    public function createUsid($userId) {
        $hash = Security::hash(uniqid(), 'sha1', false);

        $this->controller->UsersSession->create();
        $this->controller->UsersSession->save(array(
            'id' => 0,
            'user_id' => $userId,
            'hash' => $hash,
            'ip' => env('REMOTE_ADDR'),
            'user_agent' => env('HTTP_USER_AGENT'),
            'created' => date(DATE_SQL),
            'last_auth' => date(DATE_SQL),
            'last_connect' => date(DATE_SQL),
        ));

        $this->controller->Cookie->write('usid', $hash, false, self::USID_LIFETIME);
    }

    public function cookieAuth() {
        $result = null;
        $hash = $this->controller->Cookie->read('usid');
        if ($hash) {
            $session = $this->controller->UsersSession->find('first', array(
                'conditions' => array(
                    'UsersSession.hash' => $hash,
                )
            ));
            if ($session) {
                if ($session['UsersSession']['last_connect'] >= date(DATE_SQL, strtotime('-'.self::USID_LIFETIME))) {
                    $user = $this->controller->User->find('first', array(
                        'conditions' => array(
                            'User.id' => $session['UsersSession']['user_id'],
                        )
                    ));
                    if ($user) {
                        $result = $user['User'];
                        $this->controller->UsersSession->save(array(
                            'id' => $session['UsersSession']['id'],
                            'ip' => env('REMOTE_ADDR'),
                            'user_agent' => env('HTTP_USER_AGENT'),
                            'last_auth' => date(DATE_SQL),
                            'last_connect' => date(DATE_SQL),
                        ));
                    }
                } else {
                    // удаляем старую сессию
                    $this->controller->UsersSession->delete($session['UsersSession']['id']);
                }
            }
        }
        return $result;
    }
}