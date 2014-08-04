<div id="user-info-container">
    <div class="user-header-left">
        <div class="gravatar">
            <a href="/">
                <div class="gravatar-wrapper-128">
                    <img class="logo" alt="" src="/uploads/avatar/mm48.png">
                </div>
            </a>
            <div class="reputation">
                <span>
                    <?=number_format($profile['Calc']['top'], 0, '.', '&nbsp;')?>
                </span>
                место в топе
            </div>
        </div>
        <div class="data">
            <table>
                <tbody>
                    <tr>
                        <th>профиль</th>
                        <td>логин</td>
                        <td><b><?=htmlspecialchars($profile['User']['login'])?></b></td>
                    </tr>
                    <tr>
                        <th></th>
                        <td>откуда</td>
                        <td><?=htmlspecialchars($profile['UsersProfile']['location'])?></td>
                    </tr>
                    <tr>
                        <th></th>
                        <td>день рождения</td>
                        <td><?=$this->DateTime->formatBirthday($profile['UsersProfile']['birthday'])?></td>
                    </tr>
                    <tr><td>&nbsp;</td></tr>
                    <tr>
                        <th>активность</th>
                        <td>на сайте</td>
                        <td><?=$this->DateTime->formatTimeFrom($profile['User']['registered'])?></td>
                    </tr>
                    <tr>
                        <th></th>
                        <td>посл активность</td>
                        <td>9 мин назад</td>
                    </tr>
                    <tr>
                        <th></th>
                        <td>смурфы</td>
                        <td><a href="/">user1</a>, user2, user3</td>
                    </tr>
                    <tr><td>&nbsp;</td></tr>
                    <tr>
                        <th>статистика</th>
                        <td>участие в играх</td>
                        <td>47</td>
                    </tr>
                    <tr>
                        <th></th>
                        <td>всего баллов</td>
                        <td><?=htmlspecialchars($profile['User']['points'])?></td>
                    </tr>
                    <tr>
                        <th></th>
                        <td>опыт</td>
                        <td><b>123,2</b></td>
                    </tr>
                    <!--<tr><td>&nbsp;</td></tr>
                    <tr>
                        <th>дополнительно</th>
                        <td colspan="2"><div class="user-about-me">qew</div></td>
                    </tr>-->
                </tbody>
            </table>
        </div>
        <? if ($profile['User']['id'] == $cur_user['id']) { ?>
        <div class="user-edit-link">
            <a href="<?=$this->Html->url('/users/edit')?>">редактировать</a>
        </div>
        <? } ?>
        <br>
    </div>
</div>