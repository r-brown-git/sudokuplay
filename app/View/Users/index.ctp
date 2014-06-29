<div id="user-browser">
<table>
    <? foreach ($users as $k => $aUser) { ?>
        <? if ($k % 3 == 0) { ?><tr><? } ?>
        <td>
            <div class="user-info ">
                <div class="user-gravatar48">
                    <a href="<?=$this->Html->url('/users/profile/'.$aUser['User']['id'])?>">
                        <div class="gravatar-wrapper-48">
                            <? $src = 'http://www.gravatar.com/avatar/' . ($aUser['UsersProfile']['gravatar'] ? md5($aUser['UsersProfile']['gravatar']) . '/?s=48&d=wavatar' : '?s=48&d=mm'); ?>
                            <img alt="" src="<?=$src?>">
                        </div>
                    </a>
                </div>
                <div class="user-details">
                    <a href="<?=$this->Html->url('/users/profile/'.$aUser['User']['id'])?>"><?=htmlspecialchars($aUser['User']['login'])?></a><br>
                    3431 in 25 days<br>
                    <span style="color:<?=rand(0,1) ? 'green' : 'red'?>"><?=rand(0,20)?>%</span>
                </div>
            </div>
        </td>
        <? if ($k % 3 == 2) { ?></tr><? } ?>
    <? } ?>
</table>
</div>