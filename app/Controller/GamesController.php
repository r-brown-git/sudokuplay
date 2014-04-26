<?
class GamesController extends AppController {

    const STATUS_PLANNED = 'planned';
    const STATUS_PROCESS = 'process';
    const STATUS_COMPLETE = 'complete';

    public $uses = array(
        'Game',
        'GamesTag',
        'Tag',
    );

    public $pageTitle = 'Игры';

    public function beforeFilter() {
        parent::beforeFilter();
        if ($this->curUser['group_id'] == 0) {
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
                )
            ),
        ));
        $games = $this->Game->find('all', array(
            'conditions' => array('OR' => array(
                array('Game.status' => self::STATUS_PLANNED),
                array('Game.status' => self::STATUS_PROCESS),
            )),
        ));

        $this->set('games', $games);
    }

    /**
     * Экшен игры
     *
     * @param   int $gameId
     * @throws  NotFoundException
     */
    public function show($gameId = 0) {
        $game = $this->Game->findById($gameId);
        if (!$game) {
            throw new NotFoundException();
        }
        $found = $this->_getFoundCells($gameId);

        $this->set('game', $game);
        $this->set('found', $found);
    }

    /**
     * Возвращает содержимое файла состояния игрового поля
     *
     * @param   int $gameId
     * @return  string
     */
    protected function _getFoundCells($gameId) {
        $content = '';
        $gameFile = GAMES . '_' . ($gameId % 10) . DS . $gameId . '.txt';
        if (file_exists($gameFile)) {
            $content = file_get_contents($gameFile);
        }
        return $content;
    }
}