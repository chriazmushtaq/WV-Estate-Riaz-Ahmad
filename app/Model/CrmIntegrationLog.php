<?php
class CrmIntegrationLog extends AppModel
{
    public $useTable = 'crm_integration_logs';
    public $cacheSources = false;
    public $belongsTo = array(
        'Project' => array('className' => 'Project', 'foreignKey' => 'project_id'),
        'User' => array('className' => 'User', 'foreignKey' => 'user_id'),
        'Lead' => array('className' => 'Lead', 'foreignKey' => 'lead_id')
    );
}
?>
