<?php
class Dealrefundrequest extends AppModel
{
    public $useTable = 'deal_refund_requests';
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
        ),
        'Dealrefund' => array(
            'className' => 'Dealrefund',
            'foreignKey' => 'converted_refund_id'
        )
    );
}
?>
