<?php
class Audittrail extends AppModel
{
    public $useTable = 'audit_trails';
    public $recursive = -1;
    public $belongsTo = array(
        'User' => array(
            'className' => 'User',
            'foreignKey' => 'user_id'
        ),
        'Agency' => array(
            'className' => 'Agency',
            'foreignKey' => 'project_id'
        )
    );
}
?>
