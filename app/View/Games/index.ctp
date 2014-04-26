<? if ($games) {
    foreach ($games as $aGame) { ?>

<div class="game-summary">
    <div class="game-descr">
        <h3><a href="<?=$this->Html->url('/games/'.$aGame['Game']['id'])?>"><?=$aGame['Game']['title']?></a></h3>
        <? if ($aGame['Game']['description']) { ?><div class="anounce"><?=$aGame['Game']['description']?></div><? } ?>
        <div class="limits">
        <? foreach($aGame['GamesTag'] as $aTag) {
            if ($aTag['Tag']) { ?>
                <a href="/" class="post-limit"><?=$aTag['Tag']['tag']?></a>
            <? }
        } ?>
        </div>
        <div class="started">
            <span title="2013-02-22 11:39:30">2ч 33м назад</span>
        </div>
    </div>
    <div class="cp">
        <!--<div class="col1">
            <div class="mini-counts">E</div>
            <div>тип</div>
        </div>-->
        <div class="col2">
            <div class="mini-counts">2.2</div>
            <div>множит</div>
        </div>
        <div class="col3">
            <div class="mini-counts">8/10</div>
            <div>в игре</div>
        </div>
    </div>
</div>

<?
    }
}
?>