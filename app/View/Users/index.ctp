<div id="user-browser">
<table>
    <? foreach ($users as $k => $aUser) { ?>
        <? if ($k % 3 == 0) { ?><tr><? } ?>
        <td>
            <div class="user-info ">
                <div class="user-gravatar48">
                    <a href="<?=$this->Html->url('/users/show/'.$aUser['User']['id'])?>">
                        <div class="gravatar-wrapper-48">
                            <img src="/uploads/avatar/mm48.png">
                        </div>
                    </a>
                </div>
                <div class="user-details">
                    <a href="<?=$this->Html->url('/users/show/'.$aUser['User']['id'])?>"><?=htmlspecialchars($aUser['User']['login'])?></a><br>
                    3431 in 25 days<br>
                    <span style="color:<?=rand(0,1) ? 'green' : 'red'?>"><?=rand(0,20)?>%</span>
                </div>
            </div>
        </td>
        <? if ($k % 3 == 2) { ?></tr><? } ?>
    <? } ?>
</table>
</div>