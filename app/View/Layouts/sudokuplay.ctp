<!DOCTYPE html>
<html>
<head>
<title>Судоку - одновременные игры онлайн<? if ($page_title) { echo ' | ' . implode(' - ', $page_title); } ?>
</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="keywords" content="судоку, sudoku, игра, играть, игроки, решать, онлайн, быстрее, одновременно, генерировать, алгоритм, партия, бонусы">
<meta name="description" content="Решайте судоку одновременно с другими участниками онлайн и находите ячейки быстрее соперников">

<meta name="robots" content="index,follow">
<link rel="stylesheet" type="text/css" href="/site/css/template.css">
<link rel="stylesheet" type="text/css" href="/site/css/extend.css">
<?=$this->Html->script('/js/jquery-1.10.1.min').PHP_EOL;?>
<?=$this->Html->script('/js/sprintf').PHP_EOL;?>
<?=$this->Html->script('/site/js/main').PHP_EOL;?>
<?=$this->Html->script('/site/js/client').PHP_EOL;?>

<? if ($cur_user['id']) {
    echo $this->element('nodejs_connect');
} ?>
</head>
<body>
<div class="body-wrapper">

<div id="custom-header"></div>
<div id="header">

    <div id="stats">
        <a href="<?=$this->Html->url('/users/index/online')?>"><?=$count_online_users?></a>
        <a href="<?=$this->Html->url('/users/index/online')?>"><img src="/site/images/player_icon.png" class="icon"></a>
        &nbsp;&nbsp;
        <a href="<?=$this->Html->url('/games')?>"><?=$count_current_games?></a>
        <a href="<?=$this->Html->url('/games')?>"><img src="/site/images/sudoku_icon.png" class="icon"></a>
    </div>
    <div id="topbar">
        <div id="hlinks">
            <? if ($cur_user['group_id'] > 0) { ?>
                <a href="<?=$this->Html->url('/users/edit')?>" title="профиль"><?=$cur_user['login']?></a>
                <span class="lsep">|</span>
                <a href="<?=$this->Html->url('/users/logout')?>" title="выход">выход</a>
            <? } else { ?>
                <a href="<?=$this->Html->url('/users/login')?>" title="вход">вход</a>
                <span class="lsep">|</span>
                <a href="<?=$this->Html->url('/users/register')?>" title="регистрация">регистрация</a>
            <? } ?>
        </div>
        <div id="hsearch">
            <form id="search" action="/search" method="get">
                <div>
                    <input name="q" placeholder="поиск игрока" tabindex="1" class="textbox" maxlength="240" size="20" type="text">
                </div>
            </form>
        </div>
    </div>

    <br class="cbt" />

    <div id="hlogo">
        <a href="/">sudokuplay.ru</a>
    </div>
    <div id="hmenus">
        <div class="nav mainnavs">
            <ul>
                <li>
                    <a href="<?=$this->Html->url('/games')?>" title="Все игры">Все игры</a>
                </li>
                <li>
                    <a href="<?=$this->Html->url('/pages/help')?>" title="Помощь">Помощь</a>
                </li>
                <li class="youarehere">
                    <a href="<?=$this->Html->url('/users')?>" title="Участники">Участники</a>
                </li>
            </ul>
        </div>
    </div>
</div>

<div id="content">
    <div id="mainbar">
        <div class="subheader">
            <h1><?
            $last = array_pop($page_title);
            foreach ($page_title as $url => $title) {
                echo '<a href="'.$this->Html->url($url).'">'. $title .'</a> <span class="delimiter">|</span> ';
            }
            echo '<span class="last">'. $last. '</span>';
            ?>
            </h1>

            <?=$this->element('tabs')?>
        </div>

        <div class="content">
            <?=$this->fetch('content')?>
        </div>
    </div>
    <div id="sidebar">
        <div class="module">
            <div id="chat"><?=$this->element('chat_messages')?></div>
            <? if ($cur_user['group_id'] != UsersGroup::GUEST) { ?>
                <input id="message-input" />
            <? } ?>
        </div>
    </div>
</div>

</div>
<div id="footer">
    <div class="cake-powered">
        <a href="http://www.cakephp.org/" rel="external" target="_blank" title="Powered by CakePHP Framework"><img src="/img/cake.power.gif" alt="Powered by CakePHP Framework" /></a>
    </div>
</div>

<script type="text/javascript">
    $(function() {
        sudokuplay.index();
    });
</script>
</body>
</html>