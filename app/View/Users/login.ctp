<?=$this->Form->create('FormUserLogin', array(
    'inputDefaults' => array(
        'format' => array('input', 'error'),
    ),
))?>
<span class="auth-icons">
    <a href="<?=$this->Html->url('/users/externalAuth/vk', true)?>"><img src="/auth_logo/vk.png" /></a>
    <a href="<?=$this->Html->url('/users/externalAuth/google', true)?>"><img src="/auth_logo/google.png" /></a>
</span>
<table class="login-table">
    <tr>
        <td class="w60"><label>Логин</label></td>
        <td>
            <?=$this->Form->input('FormUserLogin.login', array(
                'type' => 'text',
            ))?>
        </td>
    </tr>
    <tr>
        <td><label>Пароль</label></td>
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