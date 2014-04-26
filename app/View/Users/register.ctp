<?=$this->Form->create('FormUserRegister', array(
    'inputDefaults' => array(
        'format' => array('input', 'error'),
    ),
))?>
<table class="login-table">
    <tr>
        <td class="w130"><label>Логин</label></td>
        <td>
            <?=$this->Form->input('FormUserRegister.login', array(
                'type' => 'text',
            ))?>
        </td>
    </tr>
    <tr>
        <td><label>Пароль</label></td>
        <td>
            <?=$this->Form->input('FormUserRegister.password', array(
                'type' => 'password',
            ))?>
        </td>
    </tr>
    <tr>
        <td><label>Повтор пароля</label></td>
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