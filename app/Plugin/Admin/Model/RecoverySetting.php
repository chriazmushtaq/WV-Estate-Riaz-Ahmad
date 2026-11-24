<?php
App::uses('ConnectionManager', 'Model');

class RecoverySetting extends AppModel
{
    public $useTable = 'recovery_settings';
    public $cacheSources = false;
    public $validationDomain = 'validation';

    public function ensureSchema()
    {
        $db = ConnectionManager::getDataSource($this->useDbConfig);
        $db->rawQuery("CREATE TABLE IF NOT EXISTS `recovery_settings` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `project_id` int(11) NOT NULL DEFAULT 0,
            `default_next_action_days` int(11) NOT NULL DEFAULT 1,
            `escalation_days` int(11) NOT NULL DEFAULT 30,
            `high_priority_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
            `auto_assign_enabled` tinyint(1) NOT NULL DEFAULT 0,
            `created` datetime DEFAULT NULL,
            `modified` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `project_id` (`project_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3");
    }
}
?>
