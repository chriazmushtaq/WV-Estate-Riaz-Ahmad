<?php
App::uses('ConnectionManager', 'Model');

class RecoveryCase extends AppModel
{
    public $useTable = 'recovery_cases';
    public $cacheSources = false;
    public $validationDomain = 'validation';

    public function ensureSchema()
    {
        $db = ConnectionManager::getDataSource($this->useDbConfig);
        $db->rawQuery("CREATE TABLE IF NOT EXISTS `recovery_cases` (
            `id` bigint(20) NOT NULL AUTO_INCREMENT,
            `project_id` int(11) NOT NULL DEFAULT 0,
            `deal_id` int(11) NOT NULL DEFAULT 0,
            `client_id` int(11) NOT NULL DEFAULT 0,
            `recovery_officer_id` bigint(20) NOT NULL DEFAULT 0,
            `assigned_user_id` int(11) NOT NULL DEFAULT 0,
            `status` varchar(40) NOT NULL DEFAULT 'New',
            `priority` varchar(20) NOT NULL DEFAULT 'Normal',
            `promise_date` date DEFAULT NULL,
            `next_action_date` date DEFAULT NULL,
            `last_contact_at` datetime DEFAULT NULL,
            `last_note` text,
            `created_by` int(11) NOT NULL DEFAULT 0,
            `modified_by` int(11) NOT NULL DEFAULT 0,
            `created` datetime DEFAULT NULL,
            `modified` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `deal_id` (`deal_id`),
            KEY `project_id` (`project_id`),
            KEY `client_id` (`client_id`),
            KEY `recovery_officer_id` (`recovery_officer_id`),
            KEY `assigned_user_id` (`assigned_user_id`),
            KEY `status` (`status`),
            KEY `next_action_date` (`next_action_date`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3");
    }
}
?>
