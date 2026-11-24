<?php
App::uses('AppModel', 'Model');

class SystemUpdate extends AppModel
{
    public $useTable = 'system_updates_logs';

    public function ensureSchema()
    {
        $db = $this->getDataSource();
        $table = $db->fullTableName($this->useTable, false, false);

        $createSql = "CREATE TABLE IF NOT EXISTS `{$table}` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `patch_name` varchar(255) NOT NULL,
            `version` varchar(50) DEFAULT NULL,
            `patch_type` varchar(50) DEFAULT 'core_and_db',
            `files_updated` int(11) DEFAULT 0,
            `sql_queries_run` int(11) DEFAULT 0,
            `backup_file` varchar(255) DEFAULT NULL,
            `applied_by` varchar(100) DEFAULT NULL,
            `status` varchar(50) DEFAULT 'success',
            `notes` text DEFAULT NULL,
            `created_at` datetime NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

        try {
            $this->query($createSql);
        } catch (Exception $e) {
            CakeLog::write('error', 'Failed to ensure system_updates_logs table: ' . $e->getMessage());
        }
    }
}
