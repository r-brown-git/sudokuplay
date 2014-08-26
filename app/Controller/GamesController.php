<?
App::uses('UsersGroup', 'Model');
class GamesController extends AppController {

    public $uses = array(
        'Game',
        'GamesTag',
        'Tag',
        'GamesUser',
    );

    public $pageTitle = ['/games' => 'Игры'];

    public $tabs = [
        'games.current' => [
            'title' => 'Открытые',
            'href' => [
                'controller' => 'games',
                'action' => 'index',
            ],
            'bounty' => false
        ],
        /*'games.archive' => [
            'title' => 'Архив',
            'href' => [
                'controller' => 'games',
                'action' => 'index',
                'archive',
            ],
            'bounty' => false
        ],*/
    ];

    public function beforeFilter() {
        parent::beforeFilter();
        if ($this->curUser['group_id'] == UsersGroup::GUEST) {
            $this->Auth->deny('show');
        }
    }

    /**
     * Список игр
     */
    public function index() {
        $this->Game->recursive = 2;
        $this->Game->bindModel(array(
            'hasMany' => array(
                'GamesTag' => array(
                    'className' => 'GamesTag',
                    'foreignKey' => 'game_id',
                ),
                'GamesUser' => array(
                    'className' => 'GamesUser',
                    'foreignKey' => 'game_id',
                    'conditions' => array('GamesUser.active' => true),
                ),
            ),
        ));
        $games = $this->Game->find('all', array(
            'conditions' => array(
                'OR' => array(
                    array('Game.game_begin >' => date(DATE_SQL)),
                    'AND' => array(
                        array('Game.game_begin <=' => date(DATE_SQL)),
                        array('Game.game_end' => '0000-00-00 00:00:00'),
                    ),
                ),
            ),
            'order' => array('Game.game_begin ASC'),
        ));

        foreach ($games as $n => $aGame) {
            $games[$n]['Extra']['completed_state'] = $aGame['Game']['pos_unknown'] ? round( 100 * count($this->_getFoundCells($aGame['Game']['id'])) / (strlen($aGame['Game']['pos_unknown']) / 2), 2) : 100;
        }

        $this->set('games', $games);
    }

    /**
     * Экшен игры
     *
     * @param   int $gameId
     * @throws  NotFoundException
     */
    public function show($gameId = 0) {
        $game = $this->Game->find('first', array(
            'conditions' => array(
                'Game.id' => $gameId,
                'OR' => array(
                    array('Game.game_begin >' => date(DATE_SQL)),
                    'AND' => array(
                        array('Game.game_begin <=' => date(DATE_SQL)),
                        array('Game.game_end' => '0000-00-00 00:00:00'),
                    ),
                )
            )
        ));
        if (!$game) {
            throw new NotFoundException();
        }
        $this->pageTitle[] = $game['Game']['title'];

        $gameFile = GAMES . '_' . ($gameId % 10) . DS . $gameId . '.txt';
        if (!file_exists($gameFile)) {
            touch($gameFile);
        }

        $gameUser = $this->GamesUser->find('first', array(
            'conditions' => array(
                'GamesUser.game_id' => $game['Game']['id'],
                'GamesUser.user_id' => $this->curUser['id'],
            ),
        ));
        if (!$gameUser) {
            $this->GamesUser->create();
            $this->GamesUser->save(array(
                'id' => 0,
                'game_id' => $game['Game']['id'],
                'user_id' => $this->curUser['id'],
                'last_connect' => date(DATE_SQL),
                'mistakes' => 0,
                'points' => 0,
                'active' => true,
                'banned' => false,
            ));
        } else {
            $this->GamesUser->save(array(
                'id' => $gameUser['GamesUser']['id'],
                'last_connect' => date(DATE_SQL),
                'active' => true,
            ));
            if ($gameUser['GamesUser']['banned']) {
                throw new ForbiddenException();
            }
        }

        $this->set('points', $this->User->findById($this->curUser['id'])['User']['points']);
        $this->set('mistakes', $gameUser ? $gameUser['GamesUser']['mistakes'] : 0);
        $this->set('game', $game);
        $this->set('found', $this->_getFoundCells($gameId));
        $this->set('online_users', $this->GamesUser->getOnlineUsersForGame($gameId));
    }

    /**
     * Возвращает содержимое файла состояния игрового поля
     *
     * @param   int $gameId
     * @return  string
     */
    protected function _getFoundCells($gameId) {
        $found = array();
        $gameFile = GAMES . '_' . ($gameId % 10) . DS . $gameId . '.txt';
        if (file_exists($gameFile)) {
            $lines = file($gameFile);
            foreach ($lines as $aLine) {
                list($n, $userId, $time) = explode(':', $aLine);
                $found[$n] = array(
                    'user' => $userId,
                    'time' => $time,
                );
            }
        }
        return $found;
    }
}