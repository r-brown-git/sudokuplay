<?php
/**
 * User: Hw0xxAYM
 * Date: 18.07.14
 * Time: 17:29
 */
?>
<? foreach ($last_chat_messages as $aMessage) { ?>
<div class="chat-block">
    <div class="chat-time" title="<?=date('Y.m.d H:i:s', strtotime($aMessage['ChatMessage']['date']))?>"><?=date('H:i:s', strtotime($aMessage['ChatMessage']['date']))?></div>
    <div class="chat-profile">
        <img class="chat-profile-icon" src="/site/images/chat_profile_icon.png">
        <a class="chat-profile-link" data-login="<?=htmlspecialchars($aMessage['User']['login'])?>">
            <span class="chat-profile-b"><?=htmlspecialchars($aMessage['User']['login'])?></span>
        </a>
    </div>
    <div class="chat-message"><?=htmlspecialchars(strip_tags($aMessage['ChatMessage']['message']))?></div>
</div>
<? } ?>
<script type="text/javascript">
    document.querySelector('#chat').scrollTop = document.querySelector('#chat').scrollHeight;
</script>