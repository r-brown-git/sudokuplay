<?php
class Game extends AppModel {

    const STATUS_PAST = 'archive';
    const STATUS_CURRENT = 'current';
    const STATUS_FUTURE = 'future';

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