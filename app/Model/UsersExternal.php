<?php
App::uses('AppModel', 'Model');

class UsersExternal extends AppModel {

    const VKONTAKTE = 1;
    const ODNOKLASSNIKI = 2;
    const GOOGLE = 3;

    public $useTable = 'users_external';

    public $primaryKey = 'user_id';
}