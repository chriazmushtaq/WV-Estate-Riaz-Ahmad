<?php
App::uses('ConnectionManager', 'Model');

class RecoveryFollowup extends AppModel
{
    public $useTable = 'recovery_followups';
    public $cacheSources = false;
    public $validationDomain = 'validation';

    public function ensureSchema()
    {
        $db = ConnectionManager::getDataSource($this->useDbConfig);
        $db->rawQuery("CREATE TABLE IF NOT EXISTS `recovery_followups` (
            `id` bigint(20) NOT NULL AUTO_INCREMENT,
            `project_id` int(11) NOT NULL DEFAULT 0,
            `recovery_case_id` bigint(20) NOT NULL DEFAULT 0,
            `deal_id` int(11) NOT NULL DEFAULT 0,
            `plans_payment_id` int(11) NOT NULL DEFAULT 0,
            `client_id` int(11) NOT NULL DEFAULT 0,
            `recovery_officer_id` bigint(20) NOT NULL DEFAULT 0,
            `action_type` varchar(40) NOT NULL DEFAULT 'Call',
            `status` varchar(40) NOT NULL DEFAULT 'Contacted',
            `contact_no` varchar(60) DEFAULT '',
            `promise_date` date DEFAULT NULL,
            `next_action_date` date DEFAULT NULL,
            `note` text,
            `created_by` int(11) NOT NULL DEFAULT 0,
            `created` datetime DEFAULT NULL,
            `modified` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `project_id` (`project_id`),
            KEY `recovery_case_id` (`recovery_case_id`),
            KEY `deal_id` (`deal_id`),
            KEY `plans_payment_id` (`plans_payment_id`),
            KEY `client_id` (`client_id`),
            KEY `recovery_officer_id` (`recovery_officer_id`),
            KEY `status` (`status`),
            KEY `next_action_date` (`next_action_date`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3");
    }
}
?>
