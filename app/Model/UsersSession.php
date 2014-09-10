<?php
/**
 * User: Hw0xxAYM
 * Date: 25.07.14
 * Time: 1:34
 */

class UsersSession extends Model {
    const ONLINE_DELAY = '-5 minutes';

    public function getOnlineUsersCount() {
        $result = $this->find('first', array(
            'fields' => array(
                'COUNT(DISTINCT UsersSession.user_id) as count',
            ),
            'conditions' => array(
                'UsersSession.active' => true,
            ),
        ));
        return intval($result[0]['count']);
    }

    /**
     * Отключает флаг онлайн для пользователей, не обновлявших страницу больше ONLINE_DELAY
     */
    public function disableInactiveUsers() {
        $this->updateAll(
            array(
                'UsersSession.active' => false,
            ),
            array(
                'UsersSession.active' => true,
                'UsersSession.last_connect <' => date(DATE_SQL, strtotime(self::ONLINE_DELAY)),
            )
        );
    }
} 