<div id="user-browser">
<table>
    <? foreach ($users as $k => $aUser) { ?>
        <? if ($k % 3 == 0) { ?><tr><? } ?>
        <td>
            <div class="user-info ">
                <div class="user-avatar48">
                    <a href="<?=$this->Html->url('/users/show/'.$aUser['User']['id'])?>">
                        <div class="avatar-wrapper-48">
                            <img src="/uploads/avatar/mm48.png">
                        </div>
                    </a>
                </div>
                <div class="user-details">
                    <a href="<?=$this->Html->url('/users/show/'.$aUser['User']['id'])?>"><?=htmlspecialchars($aUser['User']['login'])?></a><br>
                    <?=$aUser['User']['points']?><? if ($aUser['User']['points']) { ?> в <?=$this->Game->ruGamesPreposionPluralize($aUser[0]['gamesCount'])?><? } ?><br>
                    <?
                    $class = $aUser['User']['luck'] != 0 ? ($aUser['User']['luck'] > 0 ? 'luck-up' : 'luck-down') : '';
                    ?>
                    <span class="<?=$class?>"><?=str_replace('.', ',', floatval($aUser['User']['luck']))?>%</span>
                </div>
            </div>
        </td>
        <? if ($k % 3 == 2) { ?></tr><? } ?>
    <? } ?>
</table>

<? if ($this->Paginator->params['paging']['User']['pageCount'] > 1) { ?>
<div class="pager">
    <?=$this->Paginator->numbers(array(
        'separator' => false,
        'ellipsis' => '<span class="page-numbers dots">…</span>',
        'tag' => 'span',
        'class' => 'page-numbers',
        'currentClass' => 'current',
        'currentTag' => 'span',
        'modulus' => 4,
        'first' => 2,
        'last' => 2
    ))?>
</div>
<? } ?>
</div>

<script type="text/javascript">
    $(function() {
        sudokuplay.usersIndex();
    });
</script>