<?php
class FormProfileEdit extends AppModel {

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
            'alphaNumeric' => array(
                'rule' => 'alphaNumeric',
                'message' => 'Допустимы только буквы и цифры',
            ),
        ),
        'password' => array(
            'rule' => array('minLength', 3),
            'required' => false,
            'message' => 'От 3 символов',
        ),
        'email' => array(
            'rule' => 'email',
            'message' => 'Укажите корректный email',
            'allowEmpty' => true,
        ),
        'location' => array(
            'between' => array(
                'rule'    => array('between', 3, 15),
                'message' => 'От 3 до 15 символов',
                'allowEmpty' => true,
            )
        ),
        'sex' => array(
            'rule' => array('inList', array('M', 'F')),
            'message' => 'Выберите пол',
            'allowEmpty' => true,
        ),
    );
}