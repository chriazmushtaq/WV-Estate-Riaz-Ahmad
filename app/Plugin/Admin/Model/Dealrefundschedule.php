<?php
class Dealrefundschedule extends AppModel
{
    public $useTable = 'deal_refund_schedules';
    public $recursive = -1;
    public $belongsTo = array(
        'Dealrefund' => array(
            'className' => 'Dealrefund',
            'foreignKey' => 'refund_id'
        ),
        'Deal' => array(
            'className' => 'Deal',
            'foreignKey' => 'deal_id'
        ),
        'Client' => array(
            'className' => 'Client',
            'foreignKey' => 'client_id'
        ),
        'Agency' => array(
            'className' => 'Agency',
            'foreignKey' => 'project_id'
        )
    );
}
?>
