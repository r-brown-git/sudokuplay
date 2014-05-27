<?php
class Group extends AppModel {
    const GUEST = 0;
    const REGISTERED = 1;
    const EMAIL_CONFIRM = 2; // подтвердили email
    const EXTERNAL = 3; // вошли через другие сервисы
    const ADMIN = 7;

}