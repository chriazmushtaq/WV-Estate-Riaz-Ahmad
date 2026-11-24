<?php
class Dealtransfersetting extends AppModel
{
    public $useTable = 'deal_transfer_settings';
    public $recursive = -1;
    public $belongsTo = array(
        'Agency' => array(
            'className' => 'Agency',
            'foreignKey' => 'project_id'
        )
    );
}
?>
