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
    );

    public $uses = array(
        'User',
    );

    public $layout = 'sudokuplay';

    public $pageTitle = array();

    /**
     * Данные авторизованного пользователя
     * @var array
     */
    public $curUser = array();

    public function beforeFilter() {
        $this->Auth->allow();

        $authData = (array)$this->Session->read('User');
        $guestData = array(
            'id' => 0,
            'login' => 'guest',
            'group_id' => 0,
        );
        $this->curUser = array_merge($guestData, $authData);

        $this->set('top_users', $this->User->getTopUsers());
    }

    public function beforeRender() {
        $this->set('page_title', $this->pageTitle);
        $this->set('cur_user', $this->curUser);
    }
}
