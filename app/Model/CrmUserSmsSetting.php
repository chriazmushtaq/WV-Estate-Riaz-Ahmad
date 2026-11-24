<?php
class CrmUserSmsSetting extends AppModel
{
    public $useTable = 'crm_user_sms_settings';
    public $cacheSources = false;
    public $belongsTo = array(
        'User' => array('className' => 'User', 'foreignKey' => 'user_id')
    );
}
?>
