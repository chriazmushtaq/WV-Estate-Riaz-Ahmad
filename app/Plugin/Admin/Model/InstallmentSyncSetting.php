<?php
App::uses('AppModel', 'Model');
App::uses('ConnectionManager', 'Model');

class InstallmentSyncSetting extends AppModel
{
    public $useTable = 'installment_sync_settings';

    public function __construct($id = false, $table = null, $ds = null)
    {
        $this->_ensureTableSchema();
        parent::__construct($id, $table, $ds);
    }

    protected function _ensureTableSchema()
    {
        try {
            $db = ConnectionManager::getDataSource('default');
            $sql = "CREATE TABLE IF NOT EXISTS `installment_sync_settings` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `project_id` int(11) DEFAULT 0,
                `calendar_sync_enabled` tinyint(1) NOT NULL DEFAULT 1,
                `calendar_reminder_days` varchar(100) NOT NULL DEFAULT '7,3,1',
                `calendar_alarm_time` varchar(10) NOT NULL DEFAULT '09:00',
                `early_payment_calculator_enabled` tinyint(1) NOT NULL DEFAULT 1,
                `early_payment_discount_mode` varchar(30) NOT NULL DEFAULT 'percentage',
                `early_payment_discount_percent` decimal(5,2) NOT NULL DEFAULT 5.00,
                `early_payment_annual_rate` decimal(5,2) NOT NULL DEFAULT 8.00,
                `early_payment_min_installments` int(11) NOT NULL DEFAULT 3,
                `require_all_overdue` tinyint(1) NOT NULL DEFAULT 1,
                `allow_overdue_discount_package` tinyint(1) NOT NULL DEFAULT 1,
                `overdue_special_discount_percent` decimal(5,2) NOT NULL DEFAULT 0.00,
                `early_payment_terms_en` text,
                `early_payment_terms_ur` text,
                `created` datetime DEFAULT NULL,
                `modified` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `project_id` (`project_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

            $connection = method_exists($db, 'getConnection') ? $db->getConnection() : null;
            if (is_object($connection) && method_exists($connection, 'exec')) {
                $connection->exec($sql);
            } else {
                $db->rawQuery($sql);
            }

            // Ensure extra columns if table already existed
            $this->_addColumnIfMissing($db, $connection, 'require_all_overdue', 'tinyint(1) NOT NULL DEFAULT 1');
            $this->_addColumnIfMissing($db, $connection, 'allow_overdue_discount_package', 'tinyint(1) NOT NULL DEFAULT 1');
            $this->_addColumnIfMissing($db, $connection, 'overdue_special_discount_percent', 'decimal(5,2) NOT NULL DEFAULT 0.00');
        } catch (Exception $e) {
            // Ignore
        }
    }

    protected function _addColumnIfMissing($db, $connection, $column, $definition)
    {
        try {
            $colSql = "ALTER TABLE `installment_sync_settings` ADD COLUMN `{$column}` {$definition};";
            if (is_object($connection) && method_exists($connection, 'exec')) {
                $connection->exec($colSql);
            } else {
                $db->rawQuery($colSql);
            }
        } catch (Exception $e) {
            // Column already exists
        }
    }

    public function ensureSchema()
    {
        $this->_ensureTableSchema();
        try {
            $this->schema(true);
        } catch (Exception $e) {
            // Ignore
        }
        return true;
    }

    public function getSettings($projectId = 0)
    {
        $this->ensureSchema();
        $row = $this->find('first', array(
            'conditions' => array('InstallmentSyncSetting.project_id' => (int)$projectId),
            'recursive' => -1
        ));

        if (empty($row)) {
            // Check global settings (project_id = 0)
            $row = $this->find('first', array(
                'conditions' => array('InstallmentSyncSetting.project_id' => 0),
                'recursive' => -1
            ));
        }

        if (empty($row)) {
            $defaults = array(
                'InstallmentSyncSetting' => array(
                    'project_id' => (int)$projectId,
                    'calendar_sync_enabled' => 1,
                    'calendar_reminder_days' => '7,3,1',
                    'calendar_alarm_time' => '09:00',
                    'early_payment_calculator_enabled' => 1,
                    'early_payment_discount_mode' => 'percentage',
                    'early_payment_discount_percent' => 5.00,
                    'early_payment_annual_rate' => 8.00,
                    'early_payment_min_installments' => 3,
                    'require_all_overdue' => 1,
                    'allow_overdue_discount_package' => 1,
                    'overdue_special_discount_percent' => 0.00,
                    'early_payment_terms_en' => 'Early payment discount is applicable on scheduled advance installments upon society management approval. Overdue installments carry no discount and must be cleared prior to or alongside advance settlement.',
                    'early_payment_terms_ur' => 'قبل از وقت پیشگی اقساط کی ادائیگی پر سوسائٹی کے مروجہ قوانین کے تحت خصوصی رعایت قابل اطلاق ہوگی۔ واجب الادا پرانی اقساط پر کوئی رعایت نہیں ہوگی اور ان کی پہلے کلیئرنس لازمی ہے۔'
                )
            );
            $this->create();
            $this->save($defaults);
            return $defaults['InstallmentSyncSetting'];
        }

        return $row['InstallmentSyncSetting'];
    }
}
