<?php
App::uses('AppModel', 'Model');

class FacebookLeadLog extends AppModel
{
    public $useTable = 'facebook_lead_logs';
    public $primaryKey = 'id';

    public $belongsTo = array(
        'Lead' => array(
            'className' => 'Lead',
            'foreignKey' => 'lead_id'
        ),
        'User' => array(
            'className' => 'User',
            'foreignKey' => 'assigned_user_id'
        )
    );
}