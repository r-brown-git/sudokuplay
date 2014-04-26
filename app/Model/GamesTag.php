<?php
class GamesTag extends AppModel {
    public $belongsTo = array(
        'Tag' => array(
            'className' => 'Tag',
            'foreignKey' => 'tag_id',
        )
    );
}