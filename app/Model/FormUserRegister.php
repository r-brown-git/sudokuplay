<?php
class FormUserRegister extends AppModel {

    public $useTable = 'users';

    public $validate = array(
        'login' => array(
            'unique' => array(
                'rule' => 'isUnique',
                'required' => true,
                'message' => 'Этот логин занят',
            ),
            'between' => array(
                'rule'    => array('between', 4, 15),
                'message' => 'Логин должен быть от 4 до 15 символов',
            ),
        ),
        'password' => array(
            'rule' => array('minLength', 3),
            'required' => true,
            'message' => 'Пароль должен быть длиннее 3 символов',
        ),
        'password2' => array(
            'rule' => 'notEmpty',
            'required' => true,
            'allowEmpty' => false,
            'message' => 'Введите пароль еще раз',
        ),
    );
}