<?php
App::uses('User', 'Model');
App::uses('Group', 'Model');
App::uses('VkAuth', 'Component');
App::uses('String', 'Utility');

class UsersController extends AppController {
    public $uses = [
        'User',
        'FormUserLogin',
        'FormUserRegister',
    ];

    public $components = [
        'VkAuth',
    ];

    public function index() {
        $this->pageTitle = 'Список пользователей';
        $users = $this->User->find('all', array(
            'order' => array('id' => 'ASC'),
        ));
        $this->set('users', $users);
    }

    public function login() {
        $this->pageTitle = 'Авторизация';
        if (!empty($this->request->query['code'])) {
            $userInfo = $this->VkAuth->getUserInfo($this->request->query['code']);
            $this->User->save([
                'id' => 0,
                'login' => $userInfo['nickname'] ? $userInfo['nickname'] : 'player' . $userInfo['uid'],
                'password' => substr(String::uuid(), 0, 8),
                'group_id' => Group::REGISTERED,
                'points' => User::START_POINTS,
                'date_registered' => $this->User->getDataSource()->expression('NOW()'),
            ]);
            $this->Session->write('User', $user['User']);
            $userId = $this->User->getLastInsertId();
            $this->Session->write('User', $this->User->findById($userId)['User']);
            $this->redirect('/games');
        }
        else if (!empty($this->request->data['FormUserLogin'])) {
            $this->FormUserLogin->set($this->request->data['FormUserLogin']);
            if ($this->FormUserLogin->validates()) {
                $user = $this->User->findByLogin($this->request->data['FormUserLogin']['login']);
                if (!empty($user) && $user['User']['password'] == $this->request->data['FormUserLogin']['password']) {
                    $this->Session->write('User', $user['User']);
                    $this->redirect('/games');
                } else {
                    $this->FormUserLogin->invalidate('password', 'Неправильное имя пользователя или пароль.');
                }
            }
        }

        $this->set('vk_auth_link', $this->VkAuth->getLink());
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
                    'date_registered' => $this->User->getDataSource()->expression('NOW()'),
                ]);
                $this->User->save();
                $userId = $this->User->getLastInsertId();
                $this->Session->write('User', $this->User->findById($userId)['User']);
                $this->redirect('/games/');
            }
        }
    }

    public function logout() {
        $this->Session->delete('User');
        $this->redirect('/');
    }
}