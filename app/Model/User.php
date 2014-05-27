<?php
class User extends AppModel {

    const START_POINTS = 100;

    public function getTopUsers() {
        $users = $this->find('all', [
            'order' => ['id' => 'ASC'],
            'limit' => 5,
        ]);
        return $users;
    }
}