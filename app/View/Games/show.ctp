<?
/*echo "<table cellspacing='0' cellpadding='0' class='game'>";
for ($i=1;$i<=9;$i++) {
    echo "<tr class='game'>\n";
    for ($j=1;$j<=9;$j++) {
        echo "<td class='game";
        if ($i>=4 && $i<=6 xor $j>=4 && $j<=6) echo " darkquad"; else echo " lightquad"; //для квадратов
        if ($i==3 || $i==6) echo " bottom";if ($i==4 || $i==7) echo " top";
        if ($j==3 || $j==6) echo " right";if ($j==4 || $j==7) echo " left";
        echo "' id='c$i$j'>";
        if (in_array($i.$j,$f))
            echo "<div class='found'>".$a[$i][$j]."</div>";
        else
            if (in_array($i.$j,$u))
                echo "<div class='empty' onClick='selectCell($i$j)' onMouseOver='overCell($i$j)' onMouseOut='outCell($i$j)'>&nbsp;</div>";
            else
                echo "<div class='empty'>".$a[$i][$j]."</div>";
        echo "</td>\n";
    }
    echo "</tr>\n";
}
echo "</table>";*/
?>

<div class="chat">
    Bob: div you ever play this game ? Ohh my post is too long<br />
    Ann: no, but i know similar.<br />
</div>

<div class="game">
    <table><tbody>
        <?
        $classes = $this->Game->getClasses();
        $unknownArr = $this->Game->getArrayFromString($game['Game']['pos_unknown']);
        $foundArr = $this->Game->getArrayFromString($found);

        for ($i=0; $i<81; $i++) {
            if ($i % 9 === 0) {
                echo '<tr>'.PHP_EOL;
            }
            $isUnknown = in_array($i, $unknownArr);
            if ($isUnknown) {
                $isFound = in_array($i, $foundArr);
                if ($isFound) {
                    $class2 = 'found';
                } else {
                    $class2 = 'empty';
                }
            } else {
                $class2 = 'given';
            }
            echo '<td class="'.$classes[$i].' '.$class2.'">'.(!$isUnknown || $isFound ? $game['Game']['all_table'][$i] : '').'</td>';
            if ($i % 9 === 8) {
                echo '</tr>'.PHP_EOL;
            }
        }
        ?>
    </tbody></table>
</div>

<div class="controls">
    <div class="stat">
        <a href="#">40</a>
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
        <input type="button" value="?">
    </div>
</div>