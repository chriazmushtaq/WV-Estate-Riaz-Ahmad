<?php
App::uses('AppModel', 'Model');

class FacebookFormMapping extends AppModel
{
    public $useTable = 'facebook_form_mappings';
    public $primaryKey = 'id';

    public $belongsTo = array(
        'Project' => array(
            'className' => 'Project',
            'foreignKey' => 'project_id'
        ),
        'Status' => array(
            'className' => 'Status',
            'foreignKey' => 'default_status_id'
        )
    );
}