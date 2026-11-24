<?php
App::uses('ConnectionManager', 'Model');

class CommunicationLog extends AppModel
{
    public $useTable = 'communication_logs';
    public $cacheSources = false;

    public function ensureSchema()
    {
        $db = ConnectionManager::getDataSource($this->useDbConfig);
        $db->rawQuery("CREATE TABLE IF NOT EXISTS `communication_logs` (
            `id` bigint(20) NOT NULL AUTO_INCREMENT,
            `queue_id` bigint(20) NOT NULL DEFAULT 0,
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
            `message` longtext,
            `attachment_url` varchar(255) DEFAULT '',
            `delivery_url` varchar(255) DEFAULT '',
            `source_type` varchar(60) DEFAULT '',
            `source_id` int(11) NOT NULL DEFAULT 0,
            `status` varchar(30) NOT NULL DEFAULT 'sent',
            `error_text` text,
            `created_by` int(11) NOT NULL DEFAULT 0,
            `created` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `queue_id` (`queue_id`),
            KEY `project_id` (`project_id`),
            KEY `channel` (`channel`),
            KEY `status` (`status`),
            KEY `source` (`source_type`, `source_id`),
            KEY `recipient` (`recipient_type`, `recipient_id`),
            KEY `created` (`created`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8");
        $this->_ensureColumns(array(
            'delivery_url' => "ALTER TABLE `communication_logs` ADD `delivery_url` varchar(255) DEFAULT '' AFTER `attachment_url`"
        ));
    }

    protected function _ensureColumns($columns)
    {
        foreach((array)$columns as $columnName => $sql)
        {
            try
            {
                $rows = (array)$this->query("SHOW COLUMNS FROM `communication_logs` LIKE '".addslashes($columnName)."'");
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

    public function writeFromQueue($queueRow, $status, $errorText = '')
    {
        $queue = !empty($queueRow['CommunicationQueue']) ? (array)$queueRow['CommunicationQueue'] : (array)$queueRow;
        $data = array(
            'queue_id' => !empty($queue['id']) ? (int)$queue['id'] : 0,
            'project_id' => !empty($queue['project_id']) ? (int)$queue['project_id'] : 0,
            'rule_id' => !empty($queue['rule_id']) ? (int)$queue['rule_id'] : 0,
            'template_id' => !empty($queue['template_id']) ? (int)$queue['template_id'] : 0,
            'channel' => isset($queue['channel']) ? (string)$queue['channel'] : '',
            'recipient_type' => isset($queue['recipient_type']) ? (string)$queue['recipient_type'] : '',
            'recipient_id' => !empty($queue['recipient_id']) ? (int)$queue['recipient_id'] : 0,
            'recipient_name' => isset($queue['recipient_name']) ? (string)$queue['recipient_name'] : '',
            'recipient_mobile' => isset($queue['recipient_mobile']) ? (string)$queue['recipient_mobile'] : '',
            'recipient_email' => isset($queue['recipient_email']) ? (string)$queue['recipient_email'] : '',
            'subject' => isset($queue['subject']) ? (string)$queue['subject'] : '',
            'message' => isset($queue['body']) ? (string)$queue['body'] : '',
            'attachment_url' => isset($queue['attachment_url']) ? (string)$queue['attachment_url'] : '',
            'delivery_url' => isset($queue['delivery_url']) ? (string)$queue['delivery_url'] : '',
            'source_type' => isset($queue['source_type']) ? (string)$queue['source_type'] : '',
            'source_id' => !empty($queue['source_id']) ? (int)$queue['source_id'] : 0,
            'status' => $status,
            'error_text' => $errorText,
            'created_by' => !empty($queue['modified_by']) ? (int)$queue['modified_by'] : (!empty($queue['created_by']) ? (int)$queue['created_by'] : 0)
        );
        $this->create();
        return (bool)$this->save(array('CommunicationLog' => $data), array('validate' => false));
    }
}
?>
