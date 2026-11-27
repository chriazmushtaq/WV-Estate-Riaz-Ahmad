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

    public function beforeSave($options = array())
    {
        $alias = $this->alias;
        if(empty($this->id) && (empty($this->data[$alias]['id']) || (string)$this->data[$alias]['id'] === '0'))
        {
            try
            {
                $db = $this->getDataSource();
                if($db && is_object($db))
                {
                    $res = $db->query("SELECT MAX(`id`) AS max_id FROM `deal_transfers`");
                    $maxId = !empty($res[0][0]['max_id']) ? (int)$res[0][0]['max_id'] : 0;
                    $this->data[$alias]['id'] = $maxId + 1;
                }
            }
            catch(Exception $e) {}
        }
        return parent::beforeSave($options);
    }
}
?>
