<?php
App::uses('User', 'Model');
App::uses('UsersGroup', 'Model');
App::uses('UsersSession', 'Model');
App::uses('UsersExternal', 'Model');
App::uses('String', 'Utility');

class UsersController extends AppController {
    public $uses = array(
        'User',
        'FormUserLogin',
        'FormUserRegister',
        'FormProfileEdit',
        'UsersExternal',
        'UsersProfile',
        'UsersGroup',
    );

    public $components = array(
        'VkAuth',
        'GoogleAuth',
        'OkAuth',
        'Paginator',
    );

    private $_authServices = array(
        UsersExternal::VKONTAKTE,
        UsersExternal::ODNOKLASSNIKI,
        UsersExternal::GOOGLE,
    );

    public $pageTitle = array('/users' => 'Участники');

    public $tabs = array(
        'users.all' => array(
            'title' => 'Все',
            'href' => array(
                'controller' => 'users',
                'action' => 'index',
            ),
            'bounty' => false
        ),
        'users.online' => array(
            'title' => 'Онлайн',
            'href' => array(
                'controller' => 'users',
                'action' => 'index',
                'online',
            ),
            'bounty' => false
        ),
    );

    public function beforeFilter() {
        parent::beforeFilter();
        if ($this->curUser['group_id'] == UsersGroup::GUEST) {
            $this->Auth->deny(array('show', 'edit')); // запрещаем экшены
        }
    }

    public function index($param = '') {
        $this->User->bindModel(array(
            'hasOne' => array(
                'UsersProfile',
                'UsersSession',
                'GamesUser',
            ),
        ), false);
        $this->Paginator->settings = array(
            'fields' => array(
                'User.*',
                'UsersProfile.*',
                'UsersSession.*',
                '(SELECT COUNT(*) FROM sud_games_users WHERE user_id = User.id) as gamesCount',
                'IF(User.registered, DATEDIFF(NOW(), User.registered), 0) AS daysCount',
            ),
            'limit' => 15,
            'order' => array(
                'User.points' => 'DESC',
                'User.registered' => 'DESC',
            ),
            'group' => array(
                'User.id'
            ),
        );

        $conditions = array();
        if ($param == 'online') {
            $conditions['UsersSession.active'] = true;
        }
        $users = $this->paginate('User', $conditions);
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

            $link = false; // получаем URL для сервиса
            if ($service == UsersExternal::VKONTAKTE) {
                $link = $this->VkAuth->getLink();
            } else if ($service == UsersExternal::ODNOKLASSNIKI) {
                $link = $this->OkAuth->getLink();
            }
        }

        if (!$link) {
            $link = array('controller' => 'users', 'action' => 'login');
        }
        $this->redirect($link);
    }

    public function login() {
        $this->pageTitle = array('Авторизация');

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
            $this->UsersCookie->createUsid($user['User']['id']);
            $this->UsersGroup->id = $user['User']['group_id'];
            $this->redirect($this->UsersGroup->field('home_page'));
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

        if (in_array($service, $this->_authServices) && $code) {
            $userInfo = false;
            if ($service == UsersExternal::VKONTAKTE) {
                $userInfo = $this->VkAuth->getUserInfo($code);
            } else if ($service == UsersExternal::ODNOKLASSNIKI) {
                $userInfo = $this->OkAuth->getUserInfo($code);
            }

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
                        'group_id' => UsersGroup::EXTERNAL_REG,
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
                        'service_user_id' => $userInfo['service_user_id'],
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
        $this->pageTitle = array('Регистрация');
        if (!empty($this->data['FormUserRegister'])) {
            $this->FormUserRegister->set($this->data['FormUserRegister']);
            if ($this->data['FormUserRegister']['password'] != $this->data['FormUserRegister']['password2']) {
                $this->FormUserRegister->invalidate('password2', 'Пароли не совпадают');
            }
            if ($this->FormUserRegister->validates()) {
                $this->User->set($this->data['FormUserRegister']);
                $this->User->set(array(
                    'id' => 0,
                    'group_id' => UsersGroup::REGISTERED,
                    'points' => User::START_POINTS,
                    'registered' => $this->User->getDataSource()->expression('NOW()'),
                ));
                $this->User->save();
                $userId = $this->User->getLastInsertId();
                $user = $this->User->findById($userId);
                $this->Session->write('User', $user['User']);

                $this->UsersCookie->createUsid($userId);
                $this->redirect($this->Auth->redirect());
            }
        }
    }

    public function logout() {
        $this->Session->delete('User');
        $this->UsersCookie->deleteUsid();
        $this->redirect($this->Auth->logout());
    }

    public function show($userId = '') {
        throw new ForbiddenException();
        $this->User->bindModel(array(
            'hasOne' => array(
                'UsersProfile'
            ),
        ));
        if ($userId) {
            $user = $this->User->findById($userId);
        } else {
            $user = $this->User->findById($this->curUser['id']);
        }
        if (!$user) {
            throw new NotFoundException();
        }
        $this->pageTitle[] = $user['User']['login'];
        $user['Calc']['top'] = 1 + $this->User->find('count', ['conditions' => [
            'OR' => [
                'User.points >' => $user['User']['points'],
                'AND' => [
                    'User.points' => $user['User']['points'],
                    'User.registered <' => $user['User']['registered'],
                ]
            ]
        ]]);

        $this->set('profile', $user);
    }

    public function edit() {
        $this->User->bindModel(array(
            'hasOne' => array(
                'UsersProfile'
            ),
        ));
        $user = $this->User->findById($this->curUser['id']);
        if (!$user) {
            throw new NotFoundException();
        }
        $this->pageTitle[] = 'редактирование профиля';
        if (!empty($this->request->data)) {
            if (empty($this->request->data['User']['password'])) {
                $this->request->data['User']['password'] = $user['User']['password'];
            }
            if (empty($this->request->data['UsersProfile']['sex'])) {
                $this->request->data['UsersProfile']['sex'] = '0';
            }
            $this->FormProfileEdit->set(array_merge(
                $this->request->data['User'],
                $this->request->data['UsersProfile']
            ));
            if ($this->FormProfileEdit->validates()) {
                $this->request->data['User']['id'] = $user['User']['id'];
                $this->request->data['UsersProfile']['user_id'] = $user['User']['id'];
                $this->User->save(
                    $this->request->data['User'],
                    false,
                    array('id', 'password')
                );
                $user['User']['password'] = $this->request->data['User']['password'];
                $this->Session->write('User', $user['User']);
                $this->UsersProfile->save(
                    $this->request->data['UsersProfile'],
                    false,
                    array('user_id', 'email', 'nickname', 'sex', 'location')
                );
                $this->redirect($this->request->here);
            } else {
                $this->User->validationErrors = $this->FormProfileEdit->validationErrors;
                $this->UsersProfile->validationErrors = $this->FormProfileEdit->validationErrors;
            }
        } else {
            $this->request->data = $user;
        }
        $this->request->data['User']['password'] = '';
    }

    public function set_login() {
        $this->pageTitle[] = 'Выбор логина';
        if (!empty($this->request->data)) {
            $this->FormUserRegister->set($this->request->data);
            $this->FormUserRegister->validate['password'] = false;
            $this->FormUserRegister->validate['password2'] = false;
            if ($this->FormUserRegister->validates()) {
                $this->User->save(array(
                    'id' => $this->curUser['id'],
                    'login' => $this->request->data['FormUserRegister']['login'],
                    'group_id' => UsersGroup::EXTERNAL,
                ), false);
                $user = $this->User->findById($this->curUser['id']);
                $this->Session->write('User', $user['User']);

                $this->UsersGroup->id = $user['User']['group_id'];
                $this->redirect($this->UsersGroup->field('home_page'));
            }
        }
    }
}