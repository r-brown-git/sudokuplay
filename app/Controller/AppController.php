<?php
/**
 * Application level Controller
 *
 * This file is application-wide controller file. You can put all
 * application-wide controller-related methods here.
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
App::uses('Controller', 'Controller');
App::uses('UsersGroup', 'Model');
App::uses('UsersSession', 'Model');

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @package		app.Controller
 * @link		http://book.cakephp.org/2.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller {

    public $components = array(
        'Auth' => array(
            'authenticate' => array('Form'),
            'loginRedirect' => array(
                'controller' => 'games',
                'action' => 'index'
            ),
            'logoutRedirect' => '/',
        ),
        'Session',
        'UsersCookie',
    );

    public $uses = array(
        'ChatMessage',
        'UsersSession',
        'Game',
    );

    public $layout = 'sudokuplay';

    public $pageTitle = array();

    public $tabs = array();

    /**
     * Данные авторизованного пользователя
     * @var array
     */
    public $curUser = array();

    public function beforeFilter() {

        $this->Auth->allow();

        $authData = $this->Session->read('User');
        if (!$authData) {
            $authData = $this->UsersCookie->cookieAuth();
        }
        if (!$authData) {
            $authData = array(
                'id' => 0,
                'login' => 'guest',
                'group_id' => 0,
            );
        }
        $this->curUser = $authData;

        if ($this->curUser['id']) {
            $this->UsersSession->updateAll(
                array(
                    'ip' => '"' . env('REMOTE_ADDR') . '"',
                    'user_agent' => '"' . env('HTTP_USER_AGENT') . '"',
                    'last_auth' => '"' . date(DATE_SQL) . '"',
                    'last_connect' => '"' . date(DATE_SQL) . '"',
                ),
                array(
                    'user_id' => $authData['id'],
                )
            );
        }

        $this->set('usid', $this->curUser['id'] != 0 ? $this->Cookie->read('usid') : null); // для запросов к сокету node.js
        $this->set('last_chat_messages', $this->ChatMessage->getLastChatMessages());

        $countOnlineUsers = $this->UsersSession->getOnlineUsersCount();
        $this->set('count_online_users', $countOnlineUsers);

        $countCurrentGames = $this->Game->getCurrentGamesCount();
        $this->set('count_current_games', $countCurrentGames);

        if (isset($this->tabs['games.current'])) {
            $this->tabs['games.current']['bounty'] = $countCurrentGames;
        }
        if (isset($this->tabs['users.online'])) {
            $this->tabs['users.online']['bounty'] = $countOnlineUsers;
        }
        $this->set('tabs', $this->tabs);

    }

    public function beforeRender() {
        $this->set('page_title', $this->pageTitle);
        $this->set('cur_user', $this->curUser);
    }
}
