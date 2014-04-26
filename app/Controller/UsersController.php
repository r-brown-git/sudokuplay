<?php
App::uses('User', 'Model');
App::uses('Group', 'Model');
class UsersController extends AppController {
    public $uses = array(
        'User',
        'FormUserLogin',
        'FormUserRegister',
    );

    public function index() {
        $this->pageTitle = 'Список пользователей';
        $users = $this->User->find('all', array(
            'order' => array('id' => 'ASC'),
        ));
        $this->set('users', $users);
    }

    public function login() {
        $this->pageTitle = 'Авторизация';
        if (!empty($this->data['FormUserLogin'])) {
            $this->FormUserLogin->set($this->data['FormUserLogin']);
            if ($this->FormUserLogin->validates()) {
                $user = $this->User->findByLogin($this->data['FormUserLogin']['login']);
                if (!empty($user) && $user['User']['password'] == $this->data['FormUserLogin']['password']) {
                    $this->Session->write('User', $user['User']);
                    $this->redirect('/games');
                } else {
                    $this->FormUserLogin->invalidate('password', 'Неправильное имя пользователя или пароль.');
                }
            }
        }
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
                $this->User->set(array(
                    'id' => 0,
                    'group_id' => Group::REGISTERED,
                    'points' => User::START_POINTS,
                    'date_registered' => $this->User->getDataSource()->expression('NOW()'),
                ));
                $this->User->save();
                $userId = $this->User->getLastInsertId();
                $this->Session->write('User', array(
                    'id' => $userId,
                    'login' => $this->data['FormUserRegister']['login'],
                    'password' => $this->data['FormUserRegister']['password'],
                    'group_id' => Group::REGISTERED,
                ));
                $this->redirect('/games');
            }
        }
    }

    public function logout() {
        $this->Session->delete('User');
        $this->redirect('/');
    }
}