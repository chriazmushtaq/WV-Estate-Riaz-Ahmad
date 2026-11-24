<?php
class CrmIntegrationRoute extends AppModel
{
    public $useTable = 'crm_integration_routes';
    public $cacheSources = false;
    public $belongsTo = array(
        'Project' => array('className' => 'Project', 'foreignKey' => 'project_id'),
        'Ugroup' => array('className' => 'Ugroup', 'foreignKey' => 'ugroup_id'),
        'User' => array('className' => 'User', 'foreignKey' => 'user_id'),
        'Status' => array('className' => 'Status', 'foreignKey' => 'status_id')
    );
}
?>
