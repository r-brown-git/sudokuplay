<? if (!empty($top_users)) { ?>
<div class="module">
    <? foreach ($top_users as $aUser) { ?>
    <div class="record">
        <a href="/" title="">
            <div class="record-box">1293</div>
        </a>
        <a href="/"><?=$aUser['User']['login']?></a>
    </div>
    <div class="record-spacer"></div>
    <? } ?>
</div>
<? } ?>