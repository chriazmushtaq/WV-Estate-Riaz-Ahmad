<?php
class PropertyHistory extends AppModel
{
    public $validationDomain = 'validation';
    public $belongsTo = array(
        'Property',
        'Deal',
        'Client',
        'Project',
        'Actor' => array('className' => 'User', 'foreignKey' => 'actor_user_id')
    );
}
?>
