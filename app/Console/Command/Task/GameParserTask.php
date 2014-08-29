<?php
/**
 * User: Hw0xxAYM
 * Date: 18.08.14
 * Time: 4:05
 */
App::uses('HttpSocket', 'Network/Http');
App::uses('Tag', 'Model');
App::uses('Game', 'Model');

/**
 * Class GameParserTask
 * Парсит игровое поле и добавляет новую игру.
 */
class GameParserTask extends Shell {

    const DEFAULT_COST = 10; // предполагалось, что за найденную ячейку дается 10 баллов
    const SYM_CENTER = 1;
    const SYM_180 = 2;
    const LEVEL_HARD = 3;
    const LEVEL_DIFFICULT = 4;

    public $uses = array(
        'Game',
        'GamesTag',
        'Tag',
    );

    public $HttpSocket;

    public function execute() {
        $this->HttpSocket = new HttpSocket(array(
            'timeout' => '30',
        ));

        $symmetrical = $this->_getSymmetrical();

        $level = $this->_getLevel($symmetrical);

        $ndigits = $this->_getNDigits($symmetrical, $level);

        list($puzzleId, $allTable, $posUnknown) = $this->_getCells($symmetrical, $level, $ndigits);

        $ratioToLevel = array(
            0 => 0.7,
            1 => 0.95,
            2 => 1.3,
            3 => 1.75,
            4 => 2.3,
        );
        $ratioShift = mt_rand(-20, 20) / 100; // -0.20 .. 0.20

        $ratio = round($ratioToLevel[$level] * (1 + 0.05 * (24 - $ndigits)) + $ratioShift, 2);

        $info = json_encode(array('puzzle_id' => $puzzleId, 'nx' => '3', 'ny' => '3', 'symmetrical' => $symmetrical, 'level' => $level, 'ndigits' => $ndigits));

        switch (true) {
            case $ndigits <= 20: $mistakesMax = 1; break;
            case $ndigits <= 25: $mistakesMax = 2; break;
            case $ndigits <= 30: $mistakesMax = 3; break;
            default: $mistakesMax = 0;
        }

        $this->Game->create();
        $this->Game->save(array(
            'id' => 0,
            'info' => $info,
            'all_table' => $allTable,
            'pos_unknown' => $posUnknown,
            'ratio' => $ratio,
            'mistake_cost' => self::DEFAULT_COST * $ratio * 2, // цена ошибки пусть будет = 2 найденных ячейки
            'mistakes_max' => $mistakesMax,
            'players_max' => 0,
            'game_begin' => '0000-00-00 00:00:00',
            'game_end' => '0000-00-00 00:00:00',
            'status' => Game::STATUS_FUTURE,
        ));
        $gameId = $this->Game->getLastInsertID();
        $this->Game->save(array(
            'id' => $gameId,
            'title' => '#' . $gameId,
        ));

        $tags = array();

        if ($symmetrical == self::SYM_CENTER) {
            $tags[] = Tag::SYM_CENTER;
            $this->out('Added sym_center tag');
        } else if ($symmetrical == self::SYM_180) {
            $tags[] = Tag::SYM_180;
            $this->out('Added sym_180 tag');
        }

        if ($level == self::LEVEL_HARD) {
            $tags[] = Tag::LEVEL_HARD;
            $this->out('Added level_hard tag');
        } else if ($level == self::LEVEL_DIFFICULT) {
            $tags[] = Tag::LEVEL_DIFFICULT;
            $this->out('Added level_difficult tag');
        }

        if ($ratioShift > 0.1) {
            $tags[] = Tag::MAX_BONUS;
            $this->out('Added max_bonus tag');
        }

        asort($tags);

        foreach ($tags as $aTag) {
            $this->GamesTag->create();
            $this->GamesTag->save(array(
                'id' => 0,
                'game_id' => $gameId,
                'tag_id' => $aTag,
            ));
        }
        $this->out('Done ' . $gameId);
    }

    private function _getCells($symmetrical, $level, $ndigits) {
        $response = $this->HttpSocket->get('http://lemo.dk/sudoku/sudoku.php', 'nx=3&ny=3&symmetrical=' . $symmetrical .
            '&level=' . $level . '&ndigits=' . $ndigits . '&solution');
        if (!$response->isOk()) {
            $this->out('Error fetching cells. Response code: '.$response->code, 1, Shell::QUIET);
            exit;
        }

        preg_match('/<h1>Puzzle no. (\d+)<\/h1>/', $response->body, $match);
        if (!$match) {
            $this->out('Error parse puzzle number', 1, Shell::QUIET);
            exit;
        }
        $puzzleId = $match[1];

        $this->out('#' . $puzzleId);

        preg_match_all('/<td class="s([\sa-z]*)">(\d)<\/td>/', $response->body, $matches);
        if (count($matches[0]) != 81) {
            $this->out('Error parse getNumberOfFilledCells <a> tags', 1, Shell::QUIET);
            exit;
        }

        $allTable = '';
        $posUnknown = '';

        for ($i = 0; $i < 81; $i++) {
            $allTable .= $matches[2][$i];
            if (strpos($matches[1][$i], 'solution') !== false) {
                $posUnknown .= ($i <= 9 ? '0'.$i : $i);
            }
        }

        return array(
            0 => $puzzleId,
            1 => $allTable,
            2 => $posUnknown,
        );
    }

    private function _getNDigits($symmetrical, $level) {
        $result = 0;
        $response = $this->HttpSocket->get('http://lemo.dk/sudoku/sudoku.php', 'nx=3&ny=3&symmetrical=' . $symmetrical . '&level=' . $level);
        if (!$response->isOk()) {
            $this->out('Error fetching nDigits. Response code: '.$response->code, 1, Shell::QUIET);
            exit;
        }

        preg_match_all('/<a href=".*?">([0-9]{2})<\/a><td align=right>([0-9]+)<tr>/', $response->body, $matches);
        if (count($matches) != 3) {
            $this->out('Error parse getNumberOfFilledCells <a> tags', 1, Shell::QUIET);
            exit;
        }
        $counts = $matches[2];
        $rand = mt_rand(1, array_sum($counts));
        $cells = '';
        foreach ($counts as $key => $aCount) {
            if (!$result) {
                if ($rand <= $aCount) {
                    $result = $matches[1][$key];
                } else {
                    $rand -= $aCount;
                }
            }
            $cells .= $matches[1][$key] . ': ' . $matches[2][$key] . ($key != count($matches[1]) - 1 ? ' | ' : '');
        }
        $this->out('Cells ('.$cells.') : '.$result);

        return $result;
    }

    private function _getLevel($symmetrical) {
        $result = 0;
        $response = $this->HttpSocket->get('http://lemo.dk/sudoku/sudoku.php', 'nx=3&ny=3&symmetrical=' . $symmetrical);
        if (!$response->isOk()) {
            $this->out('Error fetching level. Response code: '.$response->code, 1, Shell::QUIET);
            exit;
        }

        preg_match('/<table border=1>.*?<\/table>/is', $response->body, $matches);
        if (count($matches) != 1) {
            $this->out('Error parse getLevel <table> tag', 1, Shell::QUIET);
            exit;
        }

        preg_match_all('/<a href=".*?&level=([0-9])">(Very easy|Easy|Medium|Hard|Difficult)<\/a><td align=right>([0-9]+)</is', $response->body, $matches);
        if (count($matches) != 4) {
            $this->out('Error parse getLevel <a> tags', 1, Shell::QUIET);
            exit;
        }

        $counts = $matches[3];
        $rand = mt_rand(1, array_sum($counts));
        $levels = '';
        $legend = array(
            '0' => 'Very easy',
            '1' => 'Easy',
            '2' => 'Medium',
            '3' => 'Hard',
            '4' => 'Difficult',
        );
        foreach ($counts as $key => $aCount) {
            if (!$result) {
                if ($rand <= $aCount) {
                    $result = $key;
                } else {
                    $rand -= $aCount;
                }
            }
            $levels .= $legend[$key] . ': ' . $aCount . ($key != count($legend) - 1 ? ' | ' : '');
        }
        $this->out('Level ('.$levels.') : '.$legend[$result]);

        return $result;
    }

    /**
     * @return int
     */
    private function _getSymmetrical() {
        $result = 0;
        $response = $this->HttpSocket->get('http://lemo.dk/sudoku/sudoku.php');

        if (!$response->isOk()) {
            $this->out('Error fetching symmetrical. Response code: '.$response->code, 1, Shell::QUIET);
            exit;
        }

        preg_match('/<tr><td align=center>3x3.*?<tr>/is', $response->body, $matches);
        if (count($matches) != 1) {
            $this->out('Error parse getSymmetrical <tr> tag', 1, Shell::QUIET);
            exit;
        }
        preg_match_all('/<a.*?>(\d+)<\/a>/is', $matches[0], $matches);
        if (count($matches) != 2 || count($matches[0]) != 3) {
            $this->out('Error parse getSymmetrical <a> tags', 1, Shell::QUIET);
            exit;
        }
        $counts = $matches[1];
        $rand = mt_rand(1, array_sum($counts));

        foreach ($counts as $key => $aCount) {
            if ($rand <= $aCount) {
                $result = $key;
                break;
            } else {
                $rand -= $aCount;
            }
        }

        $legend = array(
            '0' => 'None',
            '1' => 'X&Y',
            '2' => '180 rotation',
        );
        $this->out('Symmetry (None: '.$counts[0].' | X&Y: '.$counts[1].' | 180 rotaion: '.$counts[2].') : '.$legend[$result]);

        return $result;
    }
}