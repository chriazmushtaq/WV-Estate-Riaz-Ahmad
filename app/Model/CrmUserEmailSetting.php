<?php
class CrmUserEmailSetting extends AppModel
{
    public $useTable = 'crm_user_email_settings';
    public $cacheSources = false;
    public $belongsTo = array(
        'User' => array('className' => 'User', 'foreignKey' => 'user_id')
    );
}
?>
