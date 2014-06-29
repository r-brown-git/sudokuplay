<? if ($games) {
    foreach ($games as $aGame) { ?>

        <div>
            <div id="game-mini-list">
                <div class="game-summary narrow">
                    <div class="cp">
                        <div class="column-number col1">
                            <div class="mini-counts">
                                <span title="0 votes">0</span>
                            </div>
                            <div>&nbsp;</div>
                        </div>
                        <div class="column-number col2">
                            <div class="mini-counts">
                                <span title="0 votes">0</span>
                            </div>
                            <div>&nbsp;</div>
                        </div>
                        <div class="column-number col3">
                            <div class="mini-counts">
                                <span title="0 votes">0</span>
                            </div>
                            <div>&nbsp;</div>
                        </div>
                    </div>
                    <div class="summary">
                        <h3>
                            <a class="question-hyperlink" href="<?=$this->Html->url('/games/'.$aGame['Game']['id'])?>">
                                <?=htmlspecialchars($aGame['Game']['title'])?>
                            </a>
                        </h3>
                        <div class="tags">
                            <? foreach($aGame['GamesTag'] as $aTag) {
                                if ($aTag['Tag']) { ?>
                                    <a class="post-tag" rel="tag" title="" href="/"><?=$aTag['Tag']['tag']?></a>
                                <? }
                            } ?>
                        </div>
                        <div class="started">
                            <a class="started-link" href="/">
                                <?=$this->DateTime->formatGameList($aGame['Game']['game_start'])?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
<?
    }
}
?>