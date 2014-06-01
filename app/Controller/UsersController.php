<?php
App::uses('User', 'Model');
App::uses('Group', 'Model');
App::uses('String', 'Utility');

class UsersController extends AppController {
    public $uses = [
        'User',
        'FormUserLogin',
        'FormUserRegister',
        'UsersExternal',
        'UsersProfile',
    ];

    public $components = [
        'VkAuth',
        'GoogleAuth',
        'OkAuth',
    ];

    private $_authServices = [
        'vk',
        'google',
        'ok',
    ];

    public function index() {
        $this->pageTitle = 'Список пользователей';
        $users = $this->User->find('all', array(
            'order' => array('id' => 'ASC'),
        ));
        $this->set('users', $users);
    }

    /**
     * Аутентификация через OAuth
     * Если можно аутентифицироваться через этот сервис,
     * мы устанавливаем в сессию через какой сервис юзер хочет зайти
     * и редиректим на урл сервиса
     * @param string $service
     */
    public function oauth($service = '') {
        if (in_array($service, $this->_authServices)) {
            $this->Session->write('Auth', $service);
            $link = $this->{ucfirst($service) . 'Auth'}->getLink(); // получаем URL для сервиса
        } else {
            $link = ['controller' => 'users', 'action' => 'login'];
        }
        $this->redirect($link);
    }

    public function login() {
        $this->pageTitle = 'Авторизация';

        $authorized = false;
        if ($userId = $this->_checkExternalAuth()) {
            if ($userId && $user = $this->User->findById($userId)) {
                $authorized = true;
            }
        } else if (!empty($this->request->data['FormUserLogin'])) {
            $this->FormUserLogin->set($this->request->data['FormUserLogin']);
            if ($this->FormUserLogin->validates()) {
                $user = $this->User->findByLogin($this->request->data['FormUserLogin']['login']);
                if (!empty($user) && $user['User']['password'] == $this->request->data['FormUserLogin']['password']) {
                    $authorized = true;
                } else {
                    $this->FormUserLogin->invalidate('password', 'Неправильное имя пользователя или пароль.');
                }
            }
        }

        if ($authorized) {
            $this->Session->write('User', $user['User']);
            $this->redirect($this->Auth->redirect());
        } else {
            $this->set('vk_auth_link', $this->VkAuth->getLink());
            $this->set('google_auth_link', $this->GoogleAuth->getLink());
        }
    }

    /**
     * Возвращает инфу юзера с сервиса аутентификации
     * или false
     */
    private function _checkExternalAuth() {
        $result = false;
        $service = $this->Session->read('Auth');
        $code = !empty($this->request->query['code']) ? $this->request->query['code'] : false; // TODO: всегда ли code ?
        if ($service && $code) {
            $userInfo = $this->{ucfirst($service) . 'Auth'}->getUserInfo($code);
            if ($userInfo) {
                $external = $this->UsersExternal->find('first', ['conditions' => [
                    'UsersExternal.service' => $service,
                    'UsersExternal.service_user_id' => $userInfo['service_user_id'],
                ]]);
                if ($external) {
                    $userId = $external['UsersExternal']['user_id'];
                    $this->User->save([
                        'id' => $userId,
                        'last_login' => date(DATE_SQL),
                        'logins' => intval($external['User']['logins']) + 1,
                    ]);
                } else {
                    $this->User->create();
                    $this->User->save([
                        'id' => 0,
                        'password' => substr(String::uuid(), 0, 8),
                        'group_id' => Group::EXTERNAL,
                        'points' => User::START_POINTS,
                        'registered' => date(DATE_SQL),
                    ]);
                    $userId = $this->User->getLastInsertId();
                    $this->User->save([
                        'id' => $userId,
                        'login' => 'player' . $userId,
                    ]);

                    $this->UsersExternal->create();
                    $this->UsersExternal->save([
                        'user_id' => $userId,
                        'service' => $service,
                        'service_user_id' => $userInfo['service_user_id'], // TODO: возвращать
                    ]);

                    $this->UsersProfile->create();
                    $this->UsersProfile->save([
                        'user_id' => $userId,
                        'first_name' => $userInfo['first_name'],
                        'last_name' => $userInfo['last_name'],
                        'sex' => $userInfo['sex'],
                        'birthday' => $userInfo['birthday'],
                    ]);
                }

                $result = $userId;
            }
        }
        return $result;
    }

    public function register() {
        $this->pageTitle = 'Регистрация';
        if (!empty($this->data['FormUserRegister'])) {
            $this->FormUserRegister->set($this->data['FormUserRegister']);
            if ($this->data['FormUserRegister']['password'] != $this->data['FormUserRegister']['password2']) {
                $this->FormUserRegister->invalidate('password2', 'Пароли не совпадают');
            }
            if ($this->FormUserRegister->validates()) {
                $this->User->set($this->data['FormUserRegister']);
                $this->User->set([
                    'id' => 0,
                    'group_id' => Group::REGISTERED,
                    'points' => User::START_POINTS,
                    'registered' => $this->User->getDataSource()->expression('NOW()'),
                ]);
                $this->User->save();
                $userId = $this->User->getLastInsertId();
                $this->Session->write('User', $this->User->findById($userId)['User']);
                $this->redirect($this->Auth->redirect());
            }
        }
    }

    public function logout() {
        $this->Session->delete('User');
        $this->redirect($this->Auth->logout());
    }
}