<div id="user-info-container">
    <div class="user-header-left">
        <div class="avatar">
            <a href="/users/profile">
                <div class="avatar-wrapper-100">
                    <img class="logo" alt="" src="/uploads/avatar/mm100.png">
                </div>
            </a>
            <!--<p class="change-picture-block">
                <a id="change-picture">изменить аватар</a>
            </p>-->
        </div>
        <div class="data">
            <?=$this->Form->create('Profile', array(
                'id' => 'profile-edit-form',
                'inputDefaults' => array(
                    'after' => '<div class="clear"></div>',
                    'maxlength' => '50',
                    'format' => array('before', 'label', 'input', 'after', 'error'),
                ),
            ));?>
            <div class="input text">
                <label for="UserLogin">Логин</label>
                <div class="label" id="UserLogin">
                    <strong><?=htmlspecialchars($cur_user['login'])?></strong>
                    <a href="<?=$this->Html->url('/users/set_login')?>">(изменить)</a>
                </div>
                <div class="clear"></div>
            </div>
            <?=$this->Form->input('UsersProfile.email', array(
                'type' => 'email',
                'label' => 'E-mail',
                'class' => 'textbox',
            ));?>
            <?=$this->Form->input('User.password', array(
                'type' => 'password',
                'label' => 'Пароль',
                'placeholder' => '(без изменений)',
                'class' => 'textbox',
            ));?>
            <?=$this->Form->input('UsersProfile.location', array(
                'type' => 'text',
                'label' => 'Откуда',
                'class' => 'textbox',
            ));?>
            <div class="input select">
                <?=$this->Form->label('UsersProfile.sex', 'Пол');?>
                <?=$this->Form->select('UsersProfile.sex',
                    array(
                        UsersProfile::SEX_MALE => 'Муж',
                        UsersProfile::SEX_FEMALE => 'Жен',
                    ),
                    array(
                        'default' => '',
                        'empty' => 'не указан',
                    )
                );?>
                <div class="clear"></div>
            </div>
            <?=$this->Form->submit('Сохранить', array());?>
            <?=$this->Form->end();?>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(function() {
        sudokuplay.usersEdit();
    });
</script>