<?php
App::uses('AppModel', 'Model');
class GamesUser extends AppModel {
    const ONLINE_DELAY = '-5 minutes';

    /**
     * Возвращает юзеров онлайн для заданной игры
     *
     * @param $gameId
     * @return json
     */
    public function getOnlineUsersForGame($gameId) {
        $this->bindModel(array(
            'belongsTo' => array(
                'User',
            ),
        ));
        $users = $this->find('all', array(
            'conditions' => array(
                'GamesUser.game_id' => $gameId,
                'GamesUser.active' => true,
            ),
            'fields' => array(
                'User.id',
                'User.login',
                'GamesUser.points',
            ),
        ));
        foreach ($users as $i => $aUser) {
            $users[$i] = [
                'id' => intval($aUser['User']['id']),
                'login' => $aUser['User']['login'],
                'points' => intval($aUser['GamesUser']['points']),
            ];
        }
        $result = json_encode($users);

        return $result;
    }

    public function getOnlinePlayersCount() {
        return $this->find('count', array('conditions' => array(
            'GamesUser.active' => true,
        )));
    }

    /**
     * Отключает флаг онлайн для игроков, не проявлявших активность больше ONLINE_DELAY
     */
    public function disableInactivePlayers() {
        $this->updateAll(
            array(
                'GamesUser.active' => false,
            ),
            array(
                'GamesUser.active' => true,
                'GamesUser.last_connect <' => date(DATE_SQL, strtotime(self::ONLINE_DELAY)),
                'GamesUser.last_found <' => date(DATE_SQL, strtotime(self::ONLINE_DELAY)),
            )
        );
    }

}