<?php
/**
 * User: Hw0xxAYM
 * Date: 18.08.14
 * Time: 4:07
 */
App::uses('Game', 'Model');

/**
 * Class GameShell
 */
class GameShell extends AppShell {
    public $tasks = array('GameParser');

    public $uses = array(
        'Game',
        'GamesUser',
    );

    /**
     * Парсит игру и добавляет в базу
     * Запускать раз в 10 минут
     */
    public function add() {
        $this->GameParser->execute(); // добавили в базу новую игру

        if ($this->Game->getCurrentGamesCount() <= $this->GamesUser->getOnlinePayersCount() / 2) { // если игр в 2 раза меньше, чем игроков, добавляем
            $closestGame = $this->Game->find('first', array(
                'conditions' => array(
                    'Game.status' => Game::STATUS_FUTURE,
                ),
                'order' => 'Game.id ASC',
            ));
            if ($closestGame) {
                $this->Game->save(array(
                    'id' => $closestGame['Game']['id'],
                    'game_begin' => date(DATE_SQL),
                    'status' => Game::STATUS_CURRENT,
                ));
            }
        }
    }
}