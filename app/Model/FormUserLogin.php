<?php
class FormUserLogin extends AppModel {

    public $useTable = false;

    public $validate = array(
        'login' => array(
            'rule' => 'notEmpty',
            'required'   => true,
            'allowEmpty' => false,
            'message' => 'Введите логин',
        ),
        'password' => array(
            'rule' => 'notEmpty',
            'required' => true,
            'allowEmpty' => false,
            'message' => 'Введите пароль',
        ),
    );
}