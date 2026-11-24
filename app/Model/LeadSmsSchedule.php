<?php
class LeadSmsSchedule extends AppModel
{
    public $useTable = 'lead_sms_schedules';
    public $cacheSources = false;

    protected function _createBaseTable()
    {
        $this->query(
            "CREATE TABLE IF NOT EXISTS `lead_sms_schedules` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `lead_id` int(11) NOT NULL,
                `project_id` int(11) DEFAULT NULL,
                `lead_user_id` int(11) DEFAULT NULL,
                `scheduled_by_user_id` int(11) NOT NULL,
                `template_id` int(11) NOT NULL DEFAULT 0,
                `template_name` varchar(191) DEFAULT NULL,
                `mobile` varchar(60) DEFAULT NULL,
                `message_template` longtext NULL,
                `message_rendered` longtext NULL,
                `gateway_mode` varchar(20) NOT NULL DEFAULT 'user',
                `gateway_label` varchar(191) DEFAULT NULL,
                `scheduled_at` datetime NOT NULL,
                `sent_at` datetime DEFAULT NULL,
                `failed_at` datetime DEFAULT NULL,
                `status` varchar(20) NOT NULL DEFAULT 'queued',
                `attempt_count` int(11) NOT NULL DEFAULT 0,
                `last_error` text NULL,
                `locked_at` datetime DEFAULT NULL,
                `meta_json` longtext NULL,
                `created` datetime DEFAULT NULL,
                `modified` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `lead_sms_schedules_status_scheduled` (`status`,`scheduled_at`),
                KEY `lead_sms_schedules_lead` (`lead_id`),
                KEY `lead_sms_schedules_user` (`scheduled_by_user_id`),
                KEY `lead_sms_schedules_locked` (`locked_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8"
        );
    }

    protected function _createBaseTableFallback()
    {
        $this->query(
            "CREATE TABLE IF NOT EXISTS lead_sms_schedules (
                id int(11) NOT NULL AUTO_INCREMENT,
                lead_id int(11) NOT NULL,
                project_id int(11) DEFAULT NULL,
                lead_user_id int(11) DEFAULT NULL,
                scheduled_by_user_id int(11) NOT NULL,
                template_id int(11) NOT NULL DEFAULT 0,
                template_name varchar(191) DEFAULT NULL,
                mobile varchar(60) DEFAULT NULL,
                message_template longtext NULL,
                message_rendered longtext NULL,
                gateway_mode varchar(20) NOT NULL DEFAULT 'user',
                gateway_label varchar(191) DEFAULT NULL,
                scheduled_at datetime NOT NULL,
                sent_at datetime DEFAULT NULL,
                failed_at datetime DEFAULT NULL,
                status varchar(20) NOT NULL DEFAULT 'queued',
                attempt_count int(11) NOT NULL DEFAULT 0,
                last_error text NULL,
                locked_at datetime DEFAULT NULL,
                meta_json longtext NULL,
                created datetime DEFAULT NULL,
                modified datetime DEFAULT NULL,
                PRIMARY KEY (id),
                KEY lead_sms_schedules_status_scheduled (status,scheduled_at),
                KEY lead_sms_schedules_lead (lead_id),
                KEY lead_sms_schedules_user (scheduled_by_user_id),
                KEY lead_sms_schedules_locked (locked_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8"
        );
    }

    public function ensureSchema()
    {
        static $isEnsured = null;
        if($isEnsured !== null)
        {
            return $isEnsured;
        }
        $isEnsured = false;
        try
        {
            try
            {
                $this->_createBaseTable();
            }
            catch(Exception $e)
            {
                try
                {
                    $this->_createBaseTableFallback();
                }
                catch(Exception $e2)
                {
                }
            }

            if(!$this->_tableExists())
            {
                $isEnsured = false;
                return false;
            }

            $requiredColumns = array(
                'lead_id' => "ALTER TABLE `lead_sms_schedules` ADD `lead_id` int(11) NOT NULL",
                'project_id' => "ALTER TABLE `lead_sms_schedules` ADD `project_id` int(11) DEFAULT NULL",
                'lead_user_id' => "ALTER TABLE `lead_sms_schedules` ADD `lead_user_id` int(11) DEFAULT NULL",
                'scheduled_by_user_id' => "ALTER TABLE `lead_sms_schedules` ADD `scheduled_by_user_id` int(11) NOT NULL DEFAULT 0",
                'template_id' => "ALTER TABLE `lead_sms_schedules` ADD `template_id` int(11) NOT NULL DEFAULT 0",
                'template_name' => "ALTER TABLE `lead_sms_schedules` ADD `template_name` varchar(191) DEFAULT NULL",
                'mobile' => "ALTER TABLE `lead_sms_schedules` ADD `mobile` varchar(60) DEFAULT NULL",
                'message_template' => "ALTER TABLE `lead_sms_schedules` ADD `message_template` longtext NULL",
                'message_rendered' => "ALTER TABLE `lead_sms_schedules` ADD `message_rendered` longtext NULL",
                'gateway_mode' => "ALTER TABLE `lead_sms_schedules` ADD `gateway_mode` varchar(20) NOT NULL DEFAULT 'user'",
                'gateway_label' => "ALTER TABLE `lead_sms_schedules` ADD `gateway_label` varchar(191) DEFAULT NULL",
                'scheduled_at' => "ALTER TABLE `lead_sms_schedules` ADD `scheduled_at` datetime NOT NULL",
                'sent_at' => "ALTER TABLE `lead_sms_schedules` ADD `sent_at` datetime DEFAULT NULL",
                'failed_at' => "ALTER TABLE `lead_sms_schedules` ADD `failed_at` datetime DEFAULT NULL",
                'status' => "ALTER TABLE `lead_sms_schedules` ADD `status` varchar(20) NOT NULL DEFAULT 'queued'",
                'attempt_count' => "ALTER TABLE `lead_sms_schedules` ADD `attempt_count` int(11) NOT NULL DEFAULT 0",
                'last_error' => "ALTER TABLE `lead_sms_schedules` ADD `last_error` text NULL",
                'locked_at' => "ALTER TABLE `lead_sms_schedules` ADD `locked_at` datetime DEFAULT NULL",
                'meta_json' => "ALTER TABLE `lead_sms_schedules` ADD `meta_json` longtext NULL"
            );
            foreach($requiredColumns as $columnName => $sql)
            {
                try
                {
                    $exists = (array)$this->query("SHOW COLUMNS FROM `lead_sms_schedules` LIKE '".addslashes($columnName)."'");
                    if(empty($exists))
                    {
                        $this->query($sql);
                    }
                }
                catch(Exception $e)
                {
                    // Keep running so partial schema issues don't block scheduling entirely.
                }
            }
            if($this->_tableExists())
            {
                $this->schema(true);
                $isEnsured = true;
            }
            else
            {
                $isEnsured = false;
            }
        }
        catch(Exception $e)
        {
            $isEnsured = false;
        }
        return $isEnsured;
    }

    protected function _tableExists()
    {
        try
        {
            $rows = (array)$this->query("SHOW TABLES LIKE 'lead_sms_schedules'");
            return !empty($rows);
        }
        catch(Exception $e)
        {
            return false;
        }
    }

    public function dueRows($limit = 200)
    {
        if(!$this->ensureSchema())
        {
            return array();
        }
        $limit = (int)$limit;
        if($limit <= 0)
        {
            $limit = 200;
        }
        $now = date('Y-m-d H:i:s');
        return (array)$this->find('all', array(
            'conditions' => array(
                'LeadSmsSchedule.status' => 'queued',
                'LeadSmsSchedule.scheduled_at <=' => $now
            ),
            'order' => array('LeadSmsSchedule.scheduled_at' => 'asc', 'LeadSmsSchedule.id' => 'asc'),
            'limit' => $limit,
            'recursive' => -1
        ));
    }

    public function markProcessing($id)
    {
        if(!$this->ensureSchema())
        {
            return false;
        }
        $id = (int)$id;
        if($id <= 0)
        {
            return false;
        }
        $this->query(
            "UPDATE `lead_sms_schedules` SET ".
            "`status`='processing', `locked_at`=NOW(), `modified`=NOW() ".
            "WHERE `id`={$id} AND `status`='queued' LIMIT 1"
        );
        return ($this->getAffectedRows() > 0);
    }

    public function markSent($id, $renderedMessage = '')
    {
        if(!$this->ensureSchema())
        {
            return false;
        }
        $id = (int)$id;
        if($id <= 0)
        {
            return false;
        }
        $renderedMessage = trim((string)$renderedMessage);
        $this->query(
            "UPDATE `lead_sms_schedules` SET ".
            "`status`='sent', `message_rendered`='".addslashes($renderedMessage)."', `sent_at`=NOW(), `locked_at`=NULL, `last_error`='', `modified`=NOW() ".
            "WHERE `id`={$id} LIMIT 1"
        );
        return ($this->getAffectedRows() > 0);
    }

    public function markFailed($id, $errorText = '')
    {
        if(!$this->ensureSchema())
        {
            return false;
        }
        $id = (int)$id;
        if($id <= 0)
        {
            return false;
        }
        $errorText = trim((string)$errorText);
        $this->query(
            "UPDATE `lead_sms_schedules` SET ".
            "`status`='failed', `failed_at`=NOW(), `locked_at`=NULL, `attempt_count`=`attempt_count`+1, `last_error`='".addslashes($errorText)."', `modified`=NOW() ".
            "WHERE `id`={$id} LIMIT 1"
        );
        return ($this->getAffectedRows() > 0);
    }

    public function resetStuckProcessing($staleMinutes = 30)
    {
        if(!$this->ensureSchema())
        {
            return false;
        }
        $staleMinutes = (int)$staleMinutes;
        if($staleMinutes <= 0)
        {
            $staleMinutes = 30;
        }
        $this->query(
            "UPDATE `lead_sms_schedules` SET `status`='queued', `locked_at`=NULL, `modified`=NOW() ".
            "WHERE `status`='processing' AND `locked_at` IS NOT NULL AND `locked_at` < DATE_SUB(NOW(), INTERVAL {$staleMinutes} MINUTE)"
        );
        return true;
    }
}
?>
