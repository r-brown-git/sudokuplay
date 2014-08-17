<? if ($tabs) { ?>
<ul class="tabs">
    <? foreach ($tabs as $aTab) { ?>
    <li>
        <?
        $pass = $aTab['href'];
        unset($pass['controller'], $pass['action']);
        $youarehere = $this->request->params['controller'] == $aTab['href']['controller'] &&
            $this->request->params['action'] == $aTab['href']['action'] &&
            $this->request->params['pass'] == $pass;
        $tabClass = 'tab' . ($youarehere ? ' youarehere' : '');
        ?>
        <div class="<?=$tabClass?>">
            <? if ($aTab['bounty'] > 0) { ?><span class="bounty"><?=$aTab['bounty']?></span><? } ?>
            <a href="<?=$this->Html->url($aTab['href'])?>">
                <?=htmlspecialchars($aTab['title'])?>
            </a>
        </div>
    </li>
    <? } ?>
</ul>
<? } ?>