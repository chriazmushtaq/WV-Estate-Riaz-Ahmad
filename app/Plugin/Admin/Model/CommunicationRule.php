<?php
App::uses('ConnectionManager', 'Model');

class CommunicationRule extends AppModel
{
    public $useTable = 'communication_rules';
    public $cacheSources = false;

    public function ensureSchema()
    {
        $db = ConnectionManager::getDataSource($this->useDbConfig);
        $db->rawQuery("CREATE TABLE IF NOT EXISTS `communication_rules` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `project_id` int(11) NOT NULL DEFAULT 0,
            `name` varchar(191) NOT NULL DEFAULT '',
            `event_key` varchar(60) NOT NULL DEFAULT 'custom_bulk',
            `trigger_timing` varchar(30) NOT NULL DEFAULT 'immediate',
            `offset_days` int(11) NOT NULL DEFAULT 0,
            `channels` varchar(120) NOT NULL DEFAULT '',
            `sms_template_id` int(11) NOT NULL DEFAULT 0,
            `whatsapp_template_id` int(11) NOT NULL DEFAULT 0,
            `email_template_id` int(11) NOT NULL DEFAULT 0,
            `letter_template_id` int(11) NOT NULL DEFAULT 0,
            `daily_limit` int(11) NOT NULL DEFAULT 100,
            `status` varchar(20) NOT NULL DEFAULT 'Active',
            `created_by` int(11) NOT NULL DEFAULT 0,
            `modified_by` int(11) NOT NULL DEFAULT 0,
            `created` datetime DEFAULT NULL,
            `modified` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `project_id` (`project_id`),
            KEY `event_key` (`event_key`),
            KEY `status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8");
        $this->_ensureColumns(array(
            'sms_template_id' => "ALTER TABLE `communication_rules` ADD `sms_template_id` int(11) NOT NULL DEFAULT 0 AFTER `channels`",
            'whatsapp_template_id' => "ALTER TABLE `communication_rules` ADD `whatsapp_template_id` int(11) NOT NULL DEFAULT 0 AFTER `sms_template_id`",
            'email_template_id' => "ALTER TABLE `communication_rules` ADD `email_template_id` int(11) NOT NULL DEFAULT 0 AFTER `whatsapp_template_id`",
            'letter_template_id' => "ALTER TABLE `communication_rules` ADD `letter_template_id` int(11) NOT NULL DEFAULT 0 AFTER `email_template_id`",
            'daily_limit' => "ALTER TABLE `communication_rules` ADD `daily_limit` int(11) NOT NULL DEFAULT 100 AFTER `letter_template_id`",
            'created_by' => "ALTER TABLE `communication_rules` ADD `created_by` int(11) NOT NULL DEFAULT 0 AFTER `status`",
            'modified_by' => "ALTER TABLE `communication_rules` ADD `modified_by` int(11) NOT NULL DEFAULT 0 AFTER `created_by`"
        ));
    }

    protected function _ensureColumns($columns)
    {
        foreach((array)$columns as $columnName => $sql)
        {
            try
            {
                $rows = (array)$this->query("SHOW COLUMNS FROM `communication_rules` LIKE '".addslashes($columnName)."'");
                if(empty($rows))
                {
                    $this->query($sql);
                }
            }
            catch(Exception $e)
            {
            }
        }
    }

    public function normalizeSaveData($data, $userId = 0)
    {
        $data = (array)$data;
        $channels = isset($data['channels']) ? $data['channels'] : array();
        if(!is_array($channels))
        {
            $channels = explode(',', (string)$channels);
        }
        $cleanChannels = array();
        foreach($channels as $channel)
        {
            $channel = strtolower(trim((string)$channel));
            if(in_array($channel, array('sms', 'whatsapp', 'email', 'letter'), true))
            {
                $cleanChannels[$channel] = $channel;
            }
        }
        $status = trim((string)(isset($data['status']) ? $data['status'] : 'Active'));
        if(!in_array($status, array('Active', 'Paused'), true))
        {
            $status = 'Active';
        }
        $timing = strtolower(trim((string)(isset($data['trigger_timing']) ? $data['trigger_timing'] : 'immediate')));
        if(!in_array($timing, array('immediate', 'before', 'after', 'on_date'), true))
        {
            $timing = 'immediate';
        }
        $dailyLimit = (int)(isset($data['daily_limit']) ? $data['daily_limit'] : 100);
        if($dailyLimit <= 0)
        {
            $dailyLimit = 100;
        }
        if($dailyLimit > 5000)
        {
            $dailyLimit = 5000;
        }
        $saveData = array(
            'project_id' => !empty($data['project_id']) ? (int)$data['project_id'] : 0,
            'name' => trim((string)(isset($data['name']) ? $data['name'] : '')),
            'event_key' => substr(trim((string)(isset($data['event_key']) ? $data['event_key'] : 'custom_bulk')), 0, 60),
            'trigger_timing' => $timing,
            'offset_days' => (int)(isset($data['offset_days']) ? $data['offset_days'] : 0),
            'channels' => implode(',', array_values($cleanChannels)),
            'sms_template_id' => !empty($data['sms_template_id']) ? (int)$data['sms_template_id'] : 0,
            'whatsapp_template_id' => !empty($data['whatsapp_template_id']) ? (int)$data['whatsapp_template_id'] : 0,
            'email_template_id' => !empty($data['email_template_id']) ? (int)$data['email_template_id'] : 0,
            'letter_template_id' => !empty($data['letter_template_id']) ? (int)$data['letter_template_id'] : 0,
            'daily_limit' => $dailyLimit,
            'status' => $status,
            'modified_by' => (int)$userId
        );
        if(empty($data['id']))
        {
            $saveData['created_by'] = (int)$userId;
        }
        else
        {
            $saveData['id'] = (int)$data['id'];
        }
        return $saveData;
    }
}
?>
