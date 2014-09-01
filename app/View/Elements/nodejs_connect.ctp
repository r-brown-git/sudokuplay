<?php
/**
 * User: Hw0xxAYM
 * Date: 01.09.14
 * Time: 3:13
 */
$nodeJsHost = Configure::read('nodeJsHost');
?>
<script src="<?=$nodeJsHost?>/socket.io/socket.io.js"></script>
<script type="text/javascript">
    var host = '<?=$nodeJsHost?>';
    var auth = {
        'id': <?=$cur_user['id']?>,
        'key': '<?=$usid ? md5($usid) : ''?>',
    };
    var params = {};
    params.gameId = <?=isset($game) ? $game['Game']['id'] : '0'?>;
    window.onload = client.connect(host, auth, params);
</script>