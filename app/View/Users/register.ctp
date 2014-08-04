<?=$this->Form->create('FormUserRegister', array(
    'inputDefaults' => array(
        'format' => array('input', 'error'),
    ),
))?>
<?=$this->element('external_auth')?>
<table class="login-table">
    <tr>
        <td class="register-table"><label>Логин</label></td>
        <td>
            <?=$this->Form->input('FormUserRegister.login', array(
                'type' => 'text',
            ))?>
        </td>
    </tr>
    <tr>
        <td class="register-table"><label>Пароль</label></td>
        <td>
            <?=$this->Form->input('FormUserRegister.password', array(
                'type' => 'password',
            ))?>
        </td>
    </tr>
    <tr>
        <td class="register-table"><label>Повтор пароля</label></td>
        <td>
            <?=$this->Form->input('FormUserRegister.password2', array(
                'type' => 'password',
            ))?>
        </td>
    </tr>
    <tr>
        <td></td>
        <td>
            <?=$this->Form->submit('Зарегистрироваться', array())?>
        </td>
    </tr>
</table>
<?=$this->Form->end()?>