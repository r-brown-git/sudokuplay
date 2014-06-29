<?php
App::uses('User', 'Model');
App::uses('Group', 'Model');
App::uses('String', 'Utility');

class UsersController extends AppController {
    public $uses = [
        'User',
        'FormUserLogin',
        'FormUserRegister',
        'FormProfileEdit',
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

    public $pageTitle = ['/users' => 'Пользователи'];

    public function beforeFilter() {
        parent::beforeFilter();
        if ($this->curUser['group_id'] == Group::GUEST) {
            $this->Auth->deny('profile');
            $this->Auth->deny('edit');
        }
    }

    public function index() {
        $this->User->bindModel(array(
            'hasOne' => array(
                'UsersProfile'
            ),
        ));
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
        $this->pageTitle = ['Авторизация'];

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
            $this->User->save(array(
                'id' => $user['User']['id'],
                'last_login' => date(DATE_SQL),
                'logins' => ConnectionManager::getDataSource('default')->expression('`logins` + 1'),
            ));
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
                $external = $this->UsersExternal->find('first', array('conditions' => array(
                    'UsersExternal.service' => $service,
                    'UsersExternal.service_user_id' => $userInfo['service_user_id'],
                )));
                if ($external) {
                    $userId = $external['UsersExternal']['user_id'];
                } else {
                    $this->User->create();
                    $this->User->save(array(
                        'id' => 0,
                        'password' => substr(String::uuid(), 0, 8),
                        'group_id' => Group::EXTERNAL,
                        'points' => User::START_POINTS,
                        'registered' => date(DATE_SQL),
                    ));
                    $userId = $this->User->getLastInsertId();
                    $this->User->save(array(
                        'id' => $userId,
                        'login' => 'player' . $userId,
                    ));

                    $this->UsersExternal->create();
                    $this->UsersExternal->save(array(
                        'user_id' => $userId,
                        'service' => $service,
                        'service_user_id' => $userInfo['service_user_id'], // TODO: возвращать
                    ));

                    $this->UsersProfile->create();
                }
                $this->UsersProfile->save(array(
                    'user_id' => $userId,
                    'first_name' => $userInfo['first_name'],
                    'last_name' => $userInfo['last_name'],
                    'nickname' => $userInfo['nickname'],
                    'sex' => $userInfo['sex'],
                    'birthday' => $userInfo['birthday'],
                ));

                $result = $userId;
            }
        }
        return $result;
    }

    public function register() {
        $this->pageTitle = ['Регистрация'];
        if (!empty($this->data['FormUserRegister'])) {
            $this->FormUserRegister->set($this->data['FormUserRegister']);
            if ($this->data['FormUserRegister']['password'] != $this->data['FormUserRegister']['password2']) {
                $this->FormUserRegister->invalidate('password2', 'Пароли не совпадают');
            }
            if ($this->FormUserRegister->validates()) {
                $this->User->set($this->data['FormUserRegister']);
                $this->User->set(array(
                    'id' => 0,
                    'group_id' => Group::REGISTERED,
                    'points' => User::START_POINTS,
                    'registered' => $this->User->getDataSource()->expression('NOW()'),
                ));
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

    public function profile($userId = '') {
        $this->pageTitle[] = 'профиль';
        if ($userId) {
            $user = $this->User->findById($userId);
        } else {
            $user = $this->User->findById($this->curUser['id']);
        }
        if (!$user) {
            throw new NotFoundException();
        }
    }

    public function edit() {
        $this->pageTitle['/users/profile'] = 'профиль';
        $this->pageTitle[] = 'редактирование';
        $this->User->bindModel(array(
            'hasOne' => array(
                'UsersProfile'
            ),
        ));
        $user = $this->User->findById($this->curUser['id']);
        if (!$user) {
            throw new NotFoundException();
        }
        if (!empty($this->request->data)) {
            $this->FormProfileEdit->set(array_merge(
                $this->request->data['User'],
                $this->request->data['UsersProfile']
            ));
            if ($this->FormProfileEdit->validates()) {
                $this->request->data['UsersProfile']['user_id'] = $user['User']['id'];

                $this->User->save(
                    $this->request->data['User'],
                    false,
                    array('password')
                );
                $this->Session->write('User.password', $this->request->data['User']['password']);
                $this->UsersProfile->save(
                    $this->request->data['UsersProfile'],
                    false,
                    array('user_id', 'first_name', 'last_name', 'email', 'nickname', 'sex', 'birthday', 'location', 'gravatar')
                );
                $this->redirect($this->request->here);
            } else {
                $this->User->validationErrors = $this->FormProfileEdit->validationErrors;
                $this->UsersProfile->validationErrors = $this->FormProfileEdit->validationErrors;
            }
        } else {
            $this->request->data = $user;
        }
        $this->set('gravatarKey', $user['UsersProfile'] ? $user['UsersProfile']['gravatar'] : '');
    }

    public function newgravatar() {
        $this->layout = 'json';
        $result = array(
            'status' => 'error',
        );
        if ($this->request->is('ajax')) {
            if ($this->curUser['group_id'] > Group::GUEST) {
                $gravatarKey = substr(String::uuid(), 0, 8);
                if ($this->UsersProfile->save(array(
                    'user_id' => $this->curUser['id'],
                    'gravatar' => $gravatarKey,
                ))) {
                    $result['status'] = 'ok';
                    $result['key'] = $gravatarKey;
                    $result['md5key'] = md5($gravatarKey);
                }
            }
        }
        $this->set('result', $result);
        $this->render('../empty');
    }
}