<?php
App::uses('AppModel', 'Model');

class UsersGroup extends AppModel {
    const GUEST = 0;
    const REGISTERED = 1;
    const EMAIL_CONFIRM = 2; // подтвердили email
    const EXTERNAL = 3; // вошли через соцсети, указали логин
    const EXTERNAL_REG = 4; // только что зарегистрировался через соцсеть
    const ADMIN = 7;
}