<?php
class Dealtransfer extends AppModel
{
    public $useTable = 'deal_transfers';
    public $recursive = -1;
    public $belongsTo = array(
        'SourceDeal' => array(
            'className' => 'Deal',
            'foreignKey' => 'source_deal_id'
        ),
        'TargetDeal' => array(
            'className' => 'Deal',
            'foreignKey' => 'target_deal_id'
        ),
        'SourceClient' => array(
            'className' => 'Client',
            'foreignKey' => 'source_client_id'
        ),
        'TargetClient' => array(
            'className' => 'Client',
            'foreignKey' => 'target_client_id'
        ),
        'SourceAgency' => array(
            'className' => 'Agency',
            'foreignKey' => 'source_project_id'
        ),
        'TargetAgency' => array(
            'className' => 'Agency',
            'foreignKey' => 'target_project_id'
        )
    );
}
?>
