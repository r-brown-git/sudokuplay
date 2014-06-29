<div id="user-info-container">
    <div class="user-header-left">
        <div class="gravatar">
            <a href="/users/profile">
                <div class="gravatar-wrapper-128">
                    <? $src = 'http://www.gravatar.com/avatar/' . ($gravatarKey ? md5($gravatarKey) . '/?s=128&d=wavatar' : '?s=128&d=mm'); ?>
                    <img class="logo" alt="" src="<?=$src?>">
                </div>
            </a>
            <p class="change-picture-block">
                <a id="change-picture">изменить аватар</a>
            </p>
        </div>
        <div class="data">
            <h3><?=htmlspecialchars($cur_user['login'])?></h3>
            <?=$this->Form->create('Profile', array(
                'id' => 'profile-edit-form',
                'inputDefaults' => array(
                    'after' => '<div class="clear"></div>',
                    'maxlength' => '50',
                    'format' => array('before', 'label', 'input', 'after', 'error'),
                ),
            ));?>
            <?=$this->Form->input('UsersProfile.email', array(
                'type' => 'email',
                'label' => 'E-mail',
            ));?>
            <?=$this->Form->input('User.password', array(
                'type' => 'password',
                'label' => 'Пароль',
                //'placeholder' => '(без изменений)',
            ));?>
            <?=$this->Form->input('UsersProfile.location', array(
                'type' => 'text',
                'label' => 'Откуда'
            ));?>
            <div class="input select">
                <?=$this->Form->label('UsersProfile.sex', 'Пол');?>
                <?=$this->Form->select('UsersProfile.sex',
                    array('M' => 'Муж', 'F' => 'Жен',),
                    array('empty' => 'не указан')
                );?>
                <div class="clear"></div>
            </div>
            <?/*=$this->Form->input('UsersProfile.first_name', array(
                'type' => 'text',
                'label' => 'Имя'
            ));?>
            <?=$this->Form->input('UsersProfile.last_name', array(
                'type' => 'text',
                'label' => 'Фамилия'
            ));?>
            <?=$this->Form->input('UsersProfile.show_name', array(
                'type' => 'checkbox',
                'label' => 'Показывать имя, фамилию',
            ));?>
            <?=$this->Form->input('UsersProfile.birthday', array(
                'type' => 'text',
                'label' => 'Дата рождения',
            ));?>
            <?=$this->Form->input('UsersProfile.show_birthday', array(
                'type' => 'checkbox',
                'label' => 'Показывать дату рождения',
            ));*/?>
            <?=$this->Form->input('UsersProfile.gravatar', array(
                'type' => 'text',
                'label' => 'Ключ аватара',
                'id' => 'input-gravatar',
            ));?>
            <?=$this->Form->submit('Сохранить', array());?>
            <?=$this->Form->end();?>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(function() {
        sp.profileEdit();
    });
</script>