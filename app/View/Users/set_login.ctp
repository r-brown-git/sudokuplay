<?=$this->Form->create('FormUserRegister', array(
    'inputDefaults' => array(
        'format' => array('input', 'error'),
    ),
))?>
    <table class="login-table">
        <tr>
            <td class="login-lable"><label>Логин</label></td>
            <td>
                <?=$this->Form->input('FormUserRegister.login', array(
                    'type' => 'text',
                ))?>
            </td>
        </tr>
        <tr>
            <td></td>
            <td>
                <?=$this->Form->submit('Выбор', array())?>
            </td>
        </tr>
    </table>
<?=$this->Form->end()?>