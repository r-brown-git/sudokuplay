<?php
/* @var $game */
/* @var $found */
?>

<div class="game-left-block">
    <div class="module online-users">
        <div class="related">
            <div class="title">Сейчас в игре</div>
            <hr />
            <div id="online-users"></div>
        </div>
    </div>
    <div class="module marquee deny-select">
        <div class="outer-marquee">
            <div class="inner-marquee">Добро пожаловать!</div>
        </div>
        <span class="invisible-span"></span>
    </div>
</div>

<div class="game deny-select">
    <table><tbody>
        <?
        $classes = $this->Game->getClasses();
        $unknownArr = $this->Game->getArrayFromString($game['Game']['pos_unknown']);

        for ($i=0; $i<81; $i++) {
            if ($i % 9 === 0) {
                echo '<tr>'.PHP_EOL;
            }
            $isUnknown = in_array($i, $unknownArr);
            if ($isUnknown) {
                $isFound = isset($found[$i]);
                if ($isFound) {
                    $class2 = 'found';
                } else {
                    $class2 = 'empty';
                }
            } else {
                $class2 = 'given';
            }
            echo '<td id="c'.$i.'" class="'.$classes[$i].' '.$class2.'">'.(!$isUnknown || $isFound ? $game['Game']['all_table'][$i] : '').'</td>';
            if ($i % 9 === 8) {
                echo '</tr>'.PHP_EOL;
            }
        }
        ?>
    </tbody></table>
</div>

<div class="controls">
    <div class="stat">
        <a href="#" id="my-points"><?=$points?></a>
    </div>
    <div class="mistakes">
        1 из 2
    </div>
    <div class="buttons">
        <input type="button" value="1">
        <input type="button" value="2">
        <input type="button" value="3">
        <input type="button" value="4">
        <input type="button" value="5">
        <input type="button" value="6">
        <input type="button" value="7">
        <input type="button" value="8">
        <input type="button" value="9">
        <input type="button" value="0">
    </div>
</div>

<script type="text/javascript">
    var myPoints = <?=$points?>;
    var onlineUsers = <?=$online_users?>;
    $(function() {
        sudokuplay.gamesShow(myPoints);
        sudokuplay.renderOnlineUsers(onlineUsers);
        sudokuplay.marquee();
    });
</script>