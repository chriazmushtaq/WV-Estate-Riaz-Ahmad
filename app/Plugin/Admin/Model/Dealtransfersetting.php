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
                    $res = $db->query("SELECT MAX(`id`) AS max_id FROM `deal_transfer_settings`");
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
