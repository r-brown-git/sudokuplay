<?php
class User extends AppModel {

    const START_POINTS = 100;

    public function getTopUsers() {
        $users = $this->find('all', array(
            'order' => array('id' => 'ASC'),
            'limit' => 5,
        ));
        return $users;
    }
}