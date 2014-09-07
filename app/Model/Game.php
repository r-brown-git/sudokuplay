<?php
class Game extends AppModel {

    const STATUS_ARCHIVE = 1;
    const STATUS_CURRENT = 2;
    const STATUS_FUTURE = 3;

    public function getCurrentGamesCount() {
        return $this->find('count', array(
            'conditions' => array(
                'Game.status' => self::STATUS_CURRENT,
                'Game.game_begin <=' => date(DATE_SQL),
                'Game.game_end' => '0000-00-00 00:00:00',
            ),
        ));
    }
}