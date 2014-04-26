<table class="table w100p">
    <tr>
        <th>#</th>
        <th>Логин</th>
    </tr>
    <? foreach ($users as $aUser) { ?>
    <tr>
        <td class="w30"><?=$aUser['User']['id']?></td>
        <td><?=$aUser['User']['login']?></td>
    </tr>
    <? } ?>
</table>