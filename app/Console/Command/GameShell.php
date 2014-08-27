<?php
/**
 * User: Hw0xxAYM
 * Date: 18.08.14
 * Time: 4:07
 */

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
    public function parse() {
        if ($this->Game->getCurrentGamesCount() <= $this->GamesUser->getOnlinePayersCount() / 2) { // если игр в 2 раза меньше, чем игроков, добавляем
            $this->GameParser->execute();
        }
    }

    /**
     * Отключает игроков, которые не проявляли активность в течение 5 минут
     */
    public function disconnectTimeout() {
        $this->GamesUser->updateAll(
            array('GamesUser.active' => 0),
            array('DATE_ADD(GamesUser.last_connect, INTERVAL 5 MINUTE) < NOW()')
        );
    }
}