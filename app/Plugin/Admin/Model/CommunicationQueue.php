<?php
App::uses('ConnectionManager', 'Model');

class CommunicationQueue extends AppModel
{
    public $useTable = 'communication_queue';
    public $cacheSources = false;

    public function ensureSchema()
    {
        $db = ConnectionManager::getDataSource($this->useDbConfig);
        $db->rawQuery("CREATE TABLE IF NOT EXISTS `communication_queue` (
            `id` bigint(20) NOT NULL AUTO_INCREMENT,
            `project_id` int(11) NOT NULL DEFAULT 0,
            `rule_id` int(11) NOT NULL DEFAULT 0,
            `template_id` int(11) NOT NULL DEFAULT 0,
            `channel` varchar(30) NOT NULL DEFAULT 'sms',
            `recipient_type` varchar(40) NOT NULL DEFAULT 'client',
            `recipient_id` int(11) NOT NULL DEFAULT 0,
            `recipient_name` varchar(191) DEFAULT '',
            `recipient_mobile` varchar(80) DEFAULT '',
            `recipient_email` varchar(191) DEFAULT '',
            `subject` varchar(191) DEFAULT '',
            `body` longtext,
            `attachment_url` varchar(255) DEFAULT '',
            `delivery_url` varchar(255) DEFAULT '',
            `source_type` varchar(60) DEFAULT '',
            `source_id` int(11) NOT NULL DEFAULT 0,
            `status` varchar(30) NOT NULL DEFAULT 'pending',
            `attempts` int(11) NOT NULL DEFAULT 0,
            `scheduled_at` datetime DEFAULT NULL,
            `sent_at` datetime DEFAULT NULL,
            `failed_at` datetime DEFAULT NULL,
            `error_text` text,
            `created_by` int(11) NOT NULL DEFAULT 0,
            `modified_by` int(11) NOT NULL DEFAULT 0,
            `created` datetime DEFAULT NULL,
            `modified` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `project_id` (`project_id`),
            KEY `rule_id` (`rule_id`),
            KEY `template_id` (`template_id`),
            KEY `channel` (`channel`),
            KEY `status` (`status`),
            KEY `scheduled_at` (`scheduled_at`),
            KEY `source` (`source_type`, `source_id`),
            KEY `recipient` (`recipient_type`, `recipient_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8");
        $this->_ensureColumns(array(
            'delivery_url' => "ALTER TABLE `communication_queue` ADD `delivery_url` varchar(255) DEFAULT '' AFTER `attachment_url`",
            'modified_by' => "ALTER TABLE `communication_queue` ADD `modified_by` int(11) NOT NULL DEFAULT 0 AFTER `created_by`"
        ));
    }

    protected function _ensureColumns($columns)
    {
        foreach((array)$columns as $columnName => $sql)
        {
            try
            {
                $rows = (array)$this->query("SHOW COLUMNS FROM `communication_queue` LIKE '".addslashes($columnName)."'");
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

    public function enqueue($data)
    {
        $data = array_merge(array(
            'project_id' => 0,
            'rule_id' => 0,
            'template_id' => 0,
            'channel' => 'sms',
            'recipient_type' => 'client',
            'recipient_id' => 0,
            'recipient_name' => '',
            'recipient_mobile' => '',
            'recipient_email' => '',
            'subject' => '',
            'body' => '',
            'attachment_url' => '',
            'delivery_url' => '',
            'source_type' => '',
            'source_id' => 0,
            'status' => 'pending',
            'attempts' => 0,
            'scheduled_at' => date('Y-m-d H:i:s'),
            'created_by' => 0,
            'modified_by' => 0
        ), (array)$data);
        $data['project_id'] = (int)$data['project_id'];
        $data['rule_id'] = (int)$data['rule_id'];
        $data['template_id'] = (int)$data['template_id'];
        $data['recipient_id'] = (int)$data['recipient_id'];
        $data['source_id'] = (int)$data['source_id'];
        $data['created_by'] = (int)$data['created_by'];
        $data['modified_by'] = (int)$data['modified_by'];
        $this->create();
        return (bool)$this->save(array('CommunicationQueue' => $data), array('validate' => false));
    }
}
?>
