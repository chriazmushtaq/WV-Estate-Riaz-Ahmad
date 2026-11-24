<?php
App::uses('AppModel', 'Model');

class FacebookNotificationLog extends AppModel
{
    public $useTable = 'facebook_notification_logs';
    public $primaryKey = 'id';

    public $belongsTo = array(
        'Lead' => array(
            'className' => 'Lead',
            'foreignKey' => 'lead_id'
        )
    );
}
