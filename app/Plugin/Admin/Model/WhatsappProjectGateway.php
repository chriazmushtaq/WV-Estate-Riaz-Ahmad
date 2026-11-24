<?php
App::uses('AdminAppModel', 'Admin.Model');
class WhatsappProjectGateway extends AdminAppModel {
    public $useTable = 'whatsapp_project_gateways';
    public $belongsTo = array(
        'Project' => array(
            'className' => 'Admin.Project',
            'foreignKey' => 'project_id'
        ),
        'FacebookAccount' => array(
            'className' => 'Admin.FacebookAccount',
            'foreignKey' => 'facebook_account_id'
        )
    );
}