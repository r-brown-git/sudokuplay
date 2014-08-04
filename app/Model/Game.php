<?php
class Game extends AppModel {

    public function getActiveGamesCount() {
        return $this->find('count', array(
            'conditions' => array(
                'OR' => array(
                    array('Game.game_begin >' => date(DATE_SQL)),
                    'AND' => array(
                        array('Game.game_begin <=' => date(DATE_SQL)),
                        array('Game.game_end' => '0000-00-00 00:00:00'),
                    ),
                )
            ),
        ));
    }
}