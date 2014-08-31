<?php
/**
 * User: Hw0xxAYM
 * Date: 25.07.14
 * Time: 1:34
 */

class UsersSession extends Model {
    const ONLINE_DELAY = '-5 minutes';

    public function getOnlineUsersCount() {
        return intval($this->find('count', array(
            'conditions' => array(
                'UsersSession.active' => true,
            ),
            'group' => array(
                'UsersSession.user_id',
            ),
        )));
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