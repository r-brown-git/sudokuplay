<?=$this->Form->create('FormUserLogin', array(
    'inputDefaults' => array(
        'format' => array('input', 'error'),
    ),
))?>
<?=$this->element('external_auth')?>
<table class="login-table">
    <tr>
        <td class="login-lable"><label>Логин</label></td>
        <td>
            <?=$this->Form->input('FormUserLogin.login', array(
                'type' => 'text',
            ))?>
        </td>
    </tr>
    <tr>
        <td class="login-lable"><label>Пароль</label></td>
        <td>
            <?=$this->Form->input('FormUserLogin.password', array(
                'type' => 'password',
            ))?>
        </td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=$this->Form->submit('Вход', array())?>
        </td>
    </tr>
</table>
<?=$this->Form->end()?>