<?php
class UsersExternal extends AppModel {
    public $useTable = 'users_external';

    public $belongsTo = [
        'User',
    ];
}