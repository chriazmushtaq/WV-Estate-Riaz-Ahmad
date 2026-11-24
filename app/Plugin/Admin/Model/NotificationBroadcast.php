<?php
App::uses('AdminAppModel', 'Admin.Model');

class NotificationBroadcast extends AdminAppModel
{
    public $useTable = 'notification_broadcasts';

    public $belongsTo = array(
        'Sender' => array(
            'className' => 'User',
            'foreignKey' => 'sender_id',
            'fields' => array('Sender.id', 'Sender.name')
        )
    );
}
