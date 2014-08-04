<?php
App::uses('AppModel', 'Model');
class GamesUser extends AppModel {

    /**
     * Возвращает юзеров онлайн для заданной игры
     *
     * @param $gameId
     * @return json
     */
    public function getOnlineUsers($gameId) {
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

}