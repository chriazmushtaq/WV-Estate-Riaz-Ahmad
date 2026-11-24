<?php
class Dealrefund extends AppModel
{
    public $useTable = 'deal_refunds';
    public $recursive = -1;
    public $belongsTo = array(
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
    public $hasMany = array(
        'Dealrefundschedule' => array(
            'className' => 'Dealrefundschedule',
            'foreignKey' => 'refund_id',
            'dependent' => true
        )
    );
}
?>
