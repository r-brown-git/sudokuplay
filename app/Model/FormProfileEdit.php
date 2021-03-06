<?php
class FormProfileEdit extends AppModel {

    public $useTable = 'users';

    public $validate = array(
        'password' => array(
            'rule' => array('minLength', 3),
            'required' => false,
            'message' => 'От 3 символов',
        ),
        'email' => array(
            'rule' => 'email',
            'message' => 'Укажите email',
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
            'rule' => array('inList', array('0', '1', '2')),
            'message' => 'Выберите пол',
            'allowEmpty' => true,
        ),
    );
}