<?php
App::uses('ConnectionManager', 'Model');

class PaymentReminder extends AppModel
{
    public $useTable = 'payment_reminders';
    public $cacheSources = false;
    public $validationDomain = 'validation';

    public function ensureSchema()
    {
        $db = ConnectionManager::getDataSource($this->useDbConfig);
        $db->rawQuery("CREATE TABLE IF NOT EXISTS `payment_reminders` (
            `id` bigint(20) NOT NULL AUTO_INCREMENT,
            `project_id` int(11) NOT NULL DEFAULT 0,
            `deal_id` int(11) NOT NULL DEFAULT 0,
            `plans_payment_id` int(11) NOT NULL DEFAULT 0,
            `channel` varchar(20) NOT NULL DEFAULT 'sms',
            `reminder_type` varchar(30) NOT NULL DEFAULT 'before_due',
            `recipient_name` varchar(160) DEFAULT '',
            `recipient_mobile` varchar(60) DEFAULT '',
            `recipient_email` varchar(200) DEFAULT '',
            `due_date` date DEFAULT NULL,
            `amount` decimal(15,2) NOT NULL DEFAULT 0.00,
            `message` text,
            `status` varchar(30) NOT NULL DEFAULT 'draft',
            `sent_at` datetime DEFAULT NULL,
            `failed_at` datetime DEFAULT NULL,
            `error_text` text,
            `created_by` int(11) NOT NULL DEFAULT 0,
            `created` datetime DEFAULT NULL,
            `modified` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `project_id` (`project_id`),
            KEY `deal_id` (`deal_id`),
            KEY `plans_payment_id` (`plans_payment_id`),
            KEY `channel` (`channel`),
            KEY `reminder_type` (`reminder_type`),
            KEY `status` (`status`),
            KEY `due_date` (`due_date`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8");
    }
}
?>
