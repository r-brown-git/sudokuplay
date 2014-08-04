<?php
App::uses('AppModel', 'Model');
class ChatMessage extends AppModel {

    const COUNT_LAST_MESSAGES = 12;

    public function getLastChatMessages() {
        $this->bindModel(array(
            'belongsTo' => array(
                'User',
            ),
        ));
        $chatMessages = $this->find('all', array(
            'order' => array(
                'ChatMessage.id DESC',
            ),
            'limit' => self::COUNT_LAST_MESSAGES,
        ));
        asort($chatMessages);
        return $chatMessages;
    }
}