<?php
App::uses('ConnectionManager', 'Model');

class RecoveryOfficer extends AppModel
{
    public $useTable = 'recovery_officers';
    public $cacheSources = false;
    public $validationDomain = 'validation';

    public function ensureSchema()
    {
        $db = ConnectionManager::getDataSource($this->useDbConfig);
        $db->rawQuery("CREATE TABLE IF NOT EXISTS `recovery_officers` (
            `id` bigint(20) NOT NULL AUTO_INCREMENT,
            `project_id` int(11) NOT NULL DEFAULT 0,
            `user_id` int(11) NOT NULL DEFAULT 0,
            `name` varchar(160) NOT NULL DEFAULT '',
            `mobile` varchar(60) DEFAULT '',
            `email` varchar(180) DEFAULT '',
            `status` varchar(20) NOT NULL DEFAULT 'Active',
            `created_by` int(11) NOT NULL DEFAULT 0,
            `created` datetime DEFAULT NULL,
            `modified` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `project_id` (`project_id`),
            KEY `user_id` (`user_id`),
            KEY `status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3");
    }
}
?>
