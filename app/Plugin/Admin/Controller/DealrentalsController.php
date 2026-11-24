<?php
App::uses('CakeNumber', 'Utility');
App::uses('Router', 'Routing');

class DealrentalsController extends AdminAppController
{
    public $helpers = array('Html', 'Form', 'Session', 'Paginator', 'Tinymce', 'NumToWord');
    public $components = array('Session', 'Paginator');
    public $uses = array('Admin.DealRental', 'Admin.DealRentalReceipt', 'Admin.DealRentalMonthlyExpense', 'Admin.DealRentalSetting', 'Admin.DealRentalSchedule', 'Admin.DealRentalReceiptAllocation', 'Admin.DealRentalTenantCollection', 'Admin.DealRentalMaintenance', 'Admin.DealRentalInspection', 'Admin.DealRentalIncrement');
    public $paginate = array('page' => 1, 'order' => array('DealRental.id' => 'desc'));

    public function beforeFilter()
    {
        parent::beforeFilter();
        if ($this->_isSuperAdminSession((array)$this->adminValue)) {
            $this->userPermissionArr = $this->_defaultFullPermission();
            $this->set('userPermissionArr', $this->userPermissionArr);
            $this->set('allowAddAction', true);
            $this->set('allowEditAction', true);
            $this->set('allowDeleteAction', true);
            $this->set('allowViewAction', true);
        }
        if ($this->_shouldRunControllerBootstrap('dealrentals.schema', '2026-07-16.rentincrements.1') || !$this->_rentalTablesReady() || !$this->_rentalSettingsTableReady() || !$this->_rentalScheduleTablesReady() || !$this->_rentalOperationTablesReady() || !$this->_rentalOperationColumnsReady() || !$this->_rentalClientAccessColumnReady() || !$this->_rentalIncrementSchemaReady() || !$this->_rentalAccountNumberColumnsReady() || !$this->_rentalAccountingColumnsReady() || !$this->_rentalPaymentAccountsReady()) {
            $this->_ensureRentalSchema();
            $this->_ensureRentalClientAccessSchema();
            $this->_ensureRentalIncrementSchema();
            $this->_ensureRentalAccountNumberSchema();
            $this->_ensureRentalAccountingSchema();
            $this->_ensureRentalOperationSchema();
            $this->_ensureRentalPaymentAccounts();
            $this->_migrateRentalAccountNumbers();
            $this->_ensureRentalTemplate();
            try {
                $this->DealRental->getDataSource()->flushMethodCache();
                $this->DealRental->schema(true);
                $this->DealRentalReceipt->schema(true);
                $this->DealRentalMonthlyExpense->schema(true);
                $this->DealRentalSchedule->schema(true);
                $this->DealRentalReceiptAllocation->schema(true);
                $this->DealRentalTenantCollection->schema(true);
                $this->DealRentalMaintenance->schema(true);
                $this->DealRentalInspection->schema(true);
                $this->DealRentalIncrement->schema(true);
            } catch (Exception $e) {
            }
            $this->_backfillRentalSchedules();
        }
    }

    protected function _rentalTablesReady()
    {
        try {
            $rows = $this->DealRental->query("SHOW TABLES LIKE 'deal_rentals'");
            return !empty($rows);
        } catch (Exception $e) {
            return false;
        }
    }

    protected function _rentalSettingsTableReady()
    {
        try {
            $rows = $this->DealRentalSetting->query("SHOW TABLES LIKE 'deal_rental_settings'");
            return !empty($rows);
        } catch (Exception $e) {
            return false;
        }
    }

    protected function _rentalScheduleTablesReady()
    {
        try {
            $scheduleRows = $this->DealRental->query("SHOW TABLES LIKE 'deal_rental_schedules'");
            $allocationRows = $this->DealRental->query("SHOW TABLES LIKE 'deal_rental_receipt_allocations'");
            return !empty($scheduleRows) && !empty($allocationRows);
        } catch (Exception $e) {
            return false;
        }
    }

    protected function _rentalOperationTablesReady()
    {
        try {
            foreach (array('deal_rental_tenant_collections', 'deal_rental_maintenance_requests', 'deal_rental_inspections') as $table) {
                if (empty($this->DealRental->query("SHOW TABLES LIKE '".$table."'"))) {
                    return false;
                }
            }
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    protected function _rentalOperationColumnsReady()
    {
        try {
            $rows = $this->DealRental->query("SHOW COLUMNS FROM `deal_rental_inspections` LIKE 'photos_json'");
            return !empty($rows);
        } catch (Exception $e) {
            return false;
        }
    }

    protected function _rentalClientAccessColumnReady()
    {
        try {
            $rows = $this->DealRental->query("SHOW COLUMNS FROM `deal_rentals` LIKE 'client_portal_access'");
            return !empty($rows);
        } catch (Exception $e) {
            return false;
        }
    }

    protected function _ensureRentalClientAccessSchema()
    {
        if ($this->_rentalClientAccessColumnReady()) {
            return true;
        }
        try {
            $this->DealRental->query("ALTER TABLE `deal_rentals` ADD COLUMN `client_portal_access` varchar(10) NOT NULL DEFAULT 'Active' AFTER `status`, ADD KEY `client_portal_access` (`client_portal_access`)");
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    protected function _rentalIncrementSchemaReady()
    {
        try {
            $table = $this->DealRental->query("SHOW TABLES LIKE 'deal_rental_increments'");
            foreach (array('rent_increment_enabled', 'rent_increment_type', 'rent_increment_value', 'rent_increment_first_date') as $column) {
                if (empty($this->DealRental->query("SHOW COLUMNS FROM `deal_rentals` LIKE '".$column."'"))) {
                    return false;
                }
            }
            foreach (array('rent_increment_enabled', 'rent_increment_type', 'rent_increment_value') as $column) {
                if (empty($this->DealRental->query("SHOW COLUMNS FROM `deal_rental_settings` LIKE '".$column."'"))) {
                    return false;
                }
            }
            return !empty($table);
        } catch (Exception $e) {
            return false;
        }
    }

    protected function _ensureRentalIncrementSchema()
    {
        $sql = array(
            "ALTER TABLE `deal_rental_settings` ADD COLUMN `rent_increment_enabled` tinyint(1) NOT NULL DEFAULT 0 AFTER `rent_due_days_before`",
            "ALTER TABLE `deal_rental_settings` ADD COLUMN `rent_increment_type` varchar(12) NOT NULL DEFAULT 'Percentage' AFTER `rent_increment_enabled`",
            "ALTER TABLE `deal_rental_settings` ADD COLUMN `rent_increment_value` decimal(15,2) NOT NULL DEFAULT 0.00 AFTER `rent_increment_type`",
            "ALTER TABLE `deal_rentals` ADD COLUMN `rent_increment_enabled` tinyint(1) NOT NULL DEFAULT 0 AFTER `client_portal_access`",
            "ALTER TABLE `deal_rentals` ADD COLUMN `rent_increment_type` varchar(12) NOT NULL DEFAULT 'Percentage' AFTER `rent_increment_enabled`",
            "ALTER TABLE `deal_rentals` ADD COLUMN `rent_increment_value` decimal(15,2) NOT NULL DEFAULT 0.00 AFTER `rent_increment_type`",
            "ALTER TABLE `deal_rentals` ADD COLUMN `rent_increment_first_date` date DEFAULT NULL AFTER `rent_increment_value`",
            "CREATE TABLE IF NOT EXISTS `deal_rental_increments` (
                `id` bigint(20) NOT NULL AUTO_INCREMENT,
                `deal_rental_id` bigint(20) NOT NULL,
                `project_id` int(11) NOT NULL DEFAULT 0,
                `effective_date` date NOT NULL,
                `increment_type` varchar(12) NOT NULL DEFAULT 'Percentage',
                `increment_value` decimal(15,2) NOT NULL DEFAULT 0.00,
                `old_rent` decimal(15,2) NOT NULL DEFAULT 0.00,
                `new_rent` decimal(15,2) NOT NULL DEFAULT 0.00,
                `status` varchar(12) NOT NULL DEFAULT 'Pending',
                `approved_by` int(11) NOT NULL DEFAULT 0,
                `approved_at` datetime DEFAULT NULL,
                `created` datetime DEFAULT NULL,
                `modified` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `rental_effective_date` (`deal_rental_id`,`effective_date`),
                KEY `deal_rental_id` (`deal_rental_id`),
                KEY `project_id` (`project_id`),
                KEY `status` (`status`),
                KEY `effective_date` (`effective_date`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3"
        );
        foreach ($sql as $query) {
            try {
                $this->DealRental->query($query);
            } catch (Exception $e) {
            }
        }
        return $this->_rentalIncrementSchemaReady();
    }

    protected function _ensureRentalOperationSchema()
    {
        $sql = array(
            "CREATE TABLE IF NOT EXISTS `deal_rental_tenant_collections` (
                `id` bigint(20) NOT NULL AUTO_INCREMENT,
                `deal_rental_id` bigint(20) NOT NULL,
                `deal_id` bigint(20) NOT NULL,
                `client_id` bigint(20) NOT NULL DEFAULT 0,
                `project_id` int(11) NOT NULL DEFAULT 0,
                `collection_no` varchar(60) NOT NULL DEFAULT '',
                `rent_month` varchar(7) NOT NULL DEFAULT '',
                `collection_date` date DEFAULT NULL,
                `rent_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
                `late_fee` decimal(15,2) NOT NULL DEFAULT 0.00,
                `other_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
                `total_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
                `payment_mode` varchar(60) DEFAULT '',
                `source_ledger_id` bigint(20) NOT NULL DEFAULT 0,
                `voucher_transaction_id` bigint(20) NOT NULL DEFAULT 0,
                `remarks` text,
                `created_by` int(11) NOT NULL DEFAULT 0,
                `created` datetime DEFAULT NULL,
                `modified` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `collection_no` (`collection_no`),
                KEY `deal_rental_id` (`deal_rental_id`),
                KEY `project_id` (`project_id`),
                KEY `rent_month` (`rent_month`),
                KEY `source_ledger_id` (`source_ledger_id`),
                KEY `voucher_transaction_id` (`voucher_transaction_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3",
            "CREATE TABLE IF NOT EXISTS `deal_rental_maintenance_requests` (
                `id` bigint(20) NOT NULL AUTO_INCREMENT,
                `deal_rental_id` bigint(20) NOT NULL,
                `deal_id` bigint(20) NOT NULL,
                `client_id` bigint(20) NOT NULL DEFAULT 0,
                `project_id` int(11) NOT NULL DEFAULT 0,
                `request_no` varchar(60) NOT NULL DEFAULT '',
                `request_date` date DEFAULT NULL,
                `category` varchar(60) NOT NULL DEFAULT 'General',
                `priority` varchar(20) NOT NULL DEFAULT 'Normal',
                `status` varchar(30) NOT NULL DEFAULT 'Submitted',
                `title` varchar(180) NOT NULL DEFAULT '',
                `description` text,
                `estimated_cost` decimal(15,2) NOT NULL DEFAULT 0.00,
                `approved_cost` decimal(15,2) NOT NULL DEFAULT 0.00,
                `actual_cost` decimal(15,2) NOT NULL DEFAULT 0.00,
                `owner_charge_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
                `agency_charge_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
                `resolution_notes` text,
                `submitted_from` varchar(20) NOT NULL DEFAULT 'Admin',
                `created_by` int(11) NOT NULL DEFAULT 0,
                `created` datetime DEFAULT NULL,
                `modified` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `request_no` (`request_no`),
                KEY `deal_rental_id` (`deal_rental_id`),
                KEY `client_id` (`client_id`),
                KEY `project_id` (`project_id`),
                KEY `status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3",
            "CREATE TABLE IF NOT EXISTS `deal_rental_inspections` (
                `id` bigint(20) NOT NULL AUTO_INCREMENT,
                `deal_rental_id` bigint(20) NOT NULL,
                `deal_id` bigint(20) NOT NULL,
                `client_id` bigint(20) NOT NULL DEFAULT 0,
                `project_id` int(11) NOT NULL DEFAULT 0,
                `inspection_type` varchar(30) NOT NULL DEFAULT 'Periodic',
                `inspection_date` date DEFAULT NULL,
                `status` varchar(30) NOT NULL DEFAULT 'Scheduled',
                `inspector_name` varchar(160) DEFAULT '',
                `electricity_meter` varchar(60) DEFAULT '',
                `gas_meter` varchar(60) DEFAULT '',
                `water_meter` varchar(60) DEFAULT '',
                `keys_count` int(11) NOT NULL DEFAULT 0,
                `security_deposit_status` varchar(40) DEFAULT '',
                `condition_notes` text,
                `photos_json` text,
                `client_acknowledged` tinyint(1) NOT NULL DEFAULT 0,
                `created_by` int(11) NOT NULL DEFAULT 0,
                `created` datetime DEFAULT NULL,
                `modified` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `deal_rental_id` (`deal_rental_id`),
                KEY `client_id` (`client_id`),
                KEY `project_id` (`project_id`),
                KEY `inspection_date` (`inspection_date`),
                KEY `status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3"
        );
        foreach ($sql as $query) {
            $this->DealRental->query($query);
        }
        if (!$this->_rentalOperationColumnsReady()) {
            try {
                $this->DealRental->query("ALTER TABLE `deal_rental_inspections` ADD COLUMN `photos_json` text AFTER `condition_notes`");
            } catch (Exception $e) {
            }
        }
        Cache::clear(false, '_cake_model_');
        return $this->_rentalOperationTablesReady();
    }

    protected function _rentalAccountNumberColumnsReady()
    {
        try {
            $required = array('rental_receipt_prefix', 'rental_receipt_next_no', 'rental_agreement_prefix', 'rental_agreement_next_no');
            $rows = $this->DealRental->query("SHOW COLUMNS FROM `ardaccounts_settings`");
            $found = array();
            foreach ((array)$rows as $row) {
                $column = isset($row['COLUMNS']['Field']) ? $row['COLUMNS']['Field'] : (isset($row[0]['Field']) ? $row[0]['Field'] : '');
                if ($column !== '') {
                    $found[$column] = true;
                }
            }
            return count(array_diff($required, array_keys($found))) === 0;
        } catch (Exception $e) {
            return false;
        }
    }

    protected function _rentalAccountingColumnsReady()
    {
        try {
            $required = array('source_ledger_id', 'voucher_transaction_id', 'deal_payment_receipt_no');
            $rows = $this->DealRentalReceipt->query("SHOW COLUMNS FROM `deal_rental_receipts`");
            $found = array();
            foreach ((array)$rows as $row) {
                $column = isset($row['COLUMNS']['Field']) ? $row['COLUMNS']['Field'] : (isset($row[0]['Field']) ? $row[0]['Field'] : '');
                if ($column !== '') {
                    $found[$column] = true;
                }
            }
            return count(array_diff($required, array_keys($found))) === 0;
        } catch (Exception $e) {
            return false;
        }
    }

    protected function _ensureRentalAccountingSchema()
    {
        $sql = array(
            "ALTER TABLE `deal_rental_receipts` ADD COLUMN `deal_payment_receipt_no` varchar(60) DEFAULT NULL AFTER `payment_mode`",
            "ALTER TABLE `deal_rental_receipts` ADD COLUMN `source_ledger_id` bigint(20) NOT NULL DEFAULT 0 AFTER `payment_mode`",
            "ALTER TABLE `deal_rental_receipts` ADD COLUMN `voucher_transaction_id` bigint(20) NOT NULL DEFAULT 0 AFTER `source_ledger_id`",
            "ALTER TABLE `deal_rental_receipts` ADD UNIQUE KEY `deal_payment_rental_adjustment` (`deal_id`,`deal_payment_receipt_no`)",
            "ALTER TABLE `deal_rental_receipts` ADD KEY `source_ledger_id` (`source_ledger_id`)",
            "ALTER TABLE `deal_rental_receipts` ADD KEY `voucher_transaction_id` (`voucher_transaction_id`)"
        );
        foreach ($sql as $query) {
            try {
                $this->DealRentalReceipt->query($query);
            } catch (Exception $e) {
            }
        }
        try {
            Cache::clear(false, '_cake_model_');
            $db = $this->DealRentalReceipt->getDataSource();
            $cacheSources = $db->cacheSources;
            $db->cacheSources = false;
            $this->DealRentalReceipt->schema(true);
            $db->cacheSources = $cacheSources;
        } catch (Exception $e) {
        }
        return $this->_rentalAccountingColumnsReady();
    }

    protected function _rentalPaymentAccountsReady()
    {
        try {
            $rows = $this->DealRental->query("SELECT COUNT(*) AS missing_count FROM `projects` p LEFT JOIN `ardaccounts_ledgers` e ON e.`project_id`=p.`id` AND e.`name`='Rental-Payments' AND e.`type`='expense' AND e.`is_trashed`=0 LEFT JOIN `ardaccounts_ledgers` i ON i.`project_id`=p.`id` AND i.`name`='Rental-Income' AND i.`type`='income' AND i.`is_trashed`=0 WHERE e.`id` IS NULL OR i.`id` IS NULL");
            $count = isset($rows[0][0]['missing_count']) ? (int)$rows[0][0]['missing_count'] : 1;
            return $count === 0;
        } catch (Exception $e) {
            return false;
        }
    }

    protected function _ensureRentalPaymentAccounts()
    {
        try {
            $projects = $this->DealRental->query("SELECT `id` FROM `projects` WHERE `id`>0");
            foreach ((array)$projects as $project) {
                $row = isset($project['projects']) ? $project['projects'] : (isset($project[0]) ? $project[0] : array());
                if (!empty($row['id'])) {
                    $this->_ardEnsureRentalPaymentLedger((int)$row['id']);
                    $this->_ardEnsureRentalIncomeLedger((int)$row['id']);
                }
            }
        } catch (Exception $e) {
            CakeLog::write('error', 'Rental payment account bootstrap failed: '.$e->getMessage());
            return false;
        }
        return true;
    }

    protected function _ensureRentalAccountNumberSchema()
    {
        $sql = array(
            "ALTER TABLE `ardaccounts_settings` ADD COLUMN `rental_receipt_prefix` varchar(20) DEFAULT 'RR-' AFTER `deal_payment_receipt_next_no`",
            "ALTER TABLE `ardaccounts_settings` ADD COLUMN `rental_receipt_next_no` int(11) NOT NULL DEFAULT 1 AFTER `rental_receipt_prefix`",
            "ALTER TABLE `ardaccounts_settings` ADD COLUMN `rental_agreement_prefix` varchar(20) DEFAULT 'RA-' AFTER `rental_receipt_next_no`",
            "ALTER TABLE `ardaccounts_settings` ADD COLUMN `rental_agreement_next_no` int(11) NOT NULL DEFAULT 1 AFTER `rental_agreement_prefix`"
        );
        foreach ($sql as $query) {
            try {
                $this->DealRental->query($query);
            } catch (Exception $e) {
            }
        }
        return $this->_rentalAccountNumberColumnsReady();
    }

    protected function _migrateRentalAccountNumbers()
    {
        if (!$this->_rentalAccountNumberColumnsReady() || !$this->_rentalSettingsTableReady()) {
            return false;
        }
        $rows = $this->DealRentalSetting->find('all', array(
            'conditions' => array('DealRentalSetting.project_id >' => 0),
            'recursive' => -1
        ));
        $db = $this->DealRental->getDataSource();
        foreach ((array)$rows as $row) {
            $setting = (array)$row['DealRentalSetting'];
            $projectId = (int)$setting['project_id'];
            $agreementPrefix = substr(trim((string)$setting['agreement_prefix']), 0, 20);
            $receiptPrefix = substr(trim((string)$setting['receipt_prefix']), 0, 20);
            $agreementPrefix = $agreementPrefix !== '' ? $agreementPrefix : 'RA-';
            $receiptPrefix = $receiptPrefix !== '' ? $receiptPrefix : 'RR-';
            $agreementNext = max(1, (int)$setting['agreement_next_no']);
            $receiptNext = max(1, (int)$setting['receipt_next_no']);
            $agreementPrefixQ = $db->value($agreementPrefix, 'string');
            $receiptPrefixQ = $db->value($receiptPrefix, 'string');
            $this->DealRental->query("INSERT INTO `ardaccounts_settings`
                (`project_id`,`rental_receipt_prefix`,`rental_receipt_next_no`,`rental_agreement_prefix`,`rental_agreement_next_no`,`created`,`modified`)
                VALUES (".$projectId.",".$receiptPrefixQ.",".$receiptNext.",".$agreementPrefixQ.",".$agreementNext.",NOW(),NOW())
                ON DUPLICATE KEY UPDATE
                    `rental_receipt_prefix`=IF(`rental_receipt_next_no`<=1 AND (`rental_receipt_prefix`='' OR `rental_receipt_prefix`='RR-'),VALUES(`rental_receipt_prefix`),`rental_receipt_prefix`),
                    `rental_receipt_next_no`=IF(`rental_receipt_prefix`=VALUES(`rental_receipt_prefix`) OR `rental_receipt_prefix`='' OR `rental_receipt_prefix`='RR-',GREATEST(`rental_receipt_next_no`,VALUES(`rental_receipt_next_no`)),`rental_receipt_next_no`),
                    `rental_agreement_prefix`=IF(`rental_agreement_next_no`<=1 AND (`rental_agreement_prefix`='' OR `rental_agreement_prefix`='RA-'),VALUES(`rental_agreement_prefix`),`rental_agreement_prefix`),
                    `rental_agreement_next_no`=IF(`rental_agreement_prefix`=VALUES(`rental_agreement_prefix`) OR `rental_agreement_prefix`='' OR `rental_agreement_prefix`='RA-',GREATEST(`rental_agreement_next_no`,VALUES(`rental_agreement_next_no`)),`rental_agreement_next_no`),
                    `modified`=NOW()");
        }
        return true;
    }

    protected function _ensureRentalSchema()
    {
        $sql = array(
            "CREATE TABLE IF NOT EXISTS `deal_rentals` (
                `id` bigint(20) NOT NULL AUTO_INCREMENT,
                `deal_id` bigint(20) NOT NULL,
                `client_id` bigint(20) NOT NULL DEFAULT 0,
                `project_id` int(11) NOT NULL DEFAULT 0,
                `agreement_no` varchar(60) NOT NULL DEFAULT '',
                `tenant_name` varchar(160) NOT NULL DEFAULT '',
                `tenant_cnic` varchar(40) DEFAULT '',
                `tenant_mobile` varchar(40) DEFAULT '',
                `tenant_email` varchar(140) DEFAULT '',
                `tenant_address` text,
                `monthly_rent` decimal(15,2) NOT NULL DEFAULT 0.00,
                `security_deposit` decimal(15,2) NOT NULL DEFAULT 0.00,
                `start_date` date DEFAULT NULL,
                `end_date` date DEFAULT NULL,
                `due_day` int(2) NOT NULL DEFAULT 1,
                `status` varchar(20) NOT NULL DEFAULT 'Active',
                `client_portal_access` varchar(10) NOT NULL DEFAULT 'Active',
                `rent_increment_enabled` tinyint(1) NOT NULL DEFAULT 0,
                `rent_increment_type` varchar(12) NOT NULL DEFAULT 'Percentage',
                `rent_increment_value` decimal(15,2) NOT NULL DEFAULT 0.00,
                `rent_increment_first_date` date DEFAULT NULL,
                `notes` text,
                `created_by` int(11) NOT NULL DEFAULT 0,
                `created` datetime DEFAULT NULL,
                `modified` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `deal_id` (`deal_id`),
                KEY `client_id` (`client_id`),
                KEY `project_id` (`project_id`),
                KEY `status` (`status`),
                KEY `client_portal_access` (`client_portal_access`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3",
            "CREATE TABLE IF NOT EXISTS `deal_rental_receipts` (
                `id` bigint(20) NOT NULL AUTO_INCREMENT,
                `deal_rental_id` bigint(20) NOT NULL,
                `deal_id` bigint(20) NOT NULL,
                `client_id` bigint(20) NOT NULL DEFAULT 0,
                `project_id` int(11) NOT NULL DEFAULT 0,
                `receipt_no` varchar(60) NOT NULL DEFAULT '',
                `rent_month` varchar(7) NOT NULL DEFAULT '',
                `receipt_date` date DEFAULT NULL,
                `rent_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
                `expense_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
                `net_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
                `payment_mode` varchar(40) DEFAULT '',
                `deal_payment_receipt_no` varchar(60) DEFAULT NULL,
                `source_ledger_id` bigint(20) NOT NULL DEFAULT 0,
                `voucher_transaction_id` bigint(20) NOT NULL DEFAULT 0,
                `expense_id` bigint(20) NOT NULL DEFAULT 0,
                `expenses_payment_id` bigint(20) NOT NULL DEFAULT 0,
                `remarks` text,
                `created_by` int(11) NOT NULL DEFAULT 0,
                `created` datetime DEFAULT NULL,
                `modified` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `receipt_no` (`receipt_no`),
                UNIQUE KEY `deal_payment_rental_adjustment` (`deal_id`,`deal_payment_receipt_no`),
                KEY `deal_rental_id` (`deal_rental_id`),
                KEY `deal_id` (`deal_id`),
                KEY `project_id` (`project_id`),
                KEY `source_ledger_id` (`source_ledger_id`),
                KEY `voucher_transaction_id` (`voucher_transaction_id`),
                KEY `rent_month` (`rent_month`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3",
            "CREATE TABLE IF NOT EXISTS `deal_rental_monthly_expenses` (
                `id` bigint(20) NOT NULL AUTO_INCREMENT,
                `receipt_id` bigint(20) NOT NULL DEFAULT 0,
                `deal_rental_id` bigint(20) NOT NULL,
                `deal_id` bigint(20) NOT NULL,
                `client_id` bigint(20) NOT NULL DEFAULT 0,
                `project_id` int(11) NOT NULL DEFAULT 0,
                `expense_month` varchar(7) NOT NULL DEFAULT '',
                `expense_date` date DEFAULT NULL,
                `amount` decimal(15,2) NOT NULL DEFAULT 0.00,
                `expense_id` bigint(20) NOT NULL DEFAULT 0,
                `remarks` text,
                `created` datetime DEFAULT NULL,
                `modified` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `receipt_id` (`receipt_id`),
                KEY `deal_rental_id` (`deal_rental_id`),
                KEY `deal_id` (`deal_id`),
                KEY `project_id` (`project_id`),
                KEY `expense_month` (`expense_month`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3"
            ,
            "CREATE TABLE IF NOT EXISTS `deal_rental_schedules` (
                `id` bigint(20) NOT NULL AUTO_INCREMENT,
                `deal_rental_id` bigint(20) NOT NULL,
                `deal_id` bigint(20) NOT NULL,
                `client_id` bigint(20) NOT NULL DEFAULT 0,
                `project_id` int(11) NOT NULL DEFAULT 0,
                `rent_month` varchar(7) NOT NULL DEFAULT '',
                `due_date` date DEFAULT NULL,
                `rent_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
                `paid_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
                `balance_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
                `status` varchar(24) NOT NULL DEFAULT 'Upcoming',
                `created` datetime DEFAULT NULL,
                `modified` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `rental_month` (`deal_rental_id`,`rent_month`),
                KEY `deal_rental_id` (`deal_rental_id`),
                KEY `deal_id` (`deal_id`),
                KEY `project_id` (`project_id`),
                KEY `due_date` (`due_date`),
                KEY `status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3",
            "CREATE TABLE IF NOT EXISTS `deal_rental_receipt_allocations` (
                `id` bigint(20) NOT NULL AUTO_INCREMENT,
                `receipt_id` bigint(20) NOT NULL,
                `schedule_id` bigint(20) NOT NULL,
                `deal_rental_id` bigint(20) NOT NULL,
                `allocated_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
                `created` datetime DEFAULT NULL,
                `modified` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `receipt_schedule` (`receipt_id`,`schedule_id`),
                KEY `receipt_id` (`receipt_id`),
                KEY `schedule_id` (`schedule_id`),
                KEY `deal_rental_id` (`deal_rental_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3",
            "CREATE TABLE IF NOT EXISTS `deal_rental_settings` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `project_id` int(11) NOT NULL DEFAULT 0,
                `agreement_prefix` varchar(20) NOT NULL DEFAULT 'RA-',
                `agreement_next_no` int(11) NOT NULL DEFAULT 1,
                `receipt_prefix` varchar(20) NOT NULL DEFAULT 'RR-',
                `receipt_next_no` int(11) NOT NULL DEFAULT 1,
                `eligible_deal_type` varchar(20) NOT NULL DEFAULT 'both',
                `property_type_ids` varchar(255) DEFAULT '',
                `sms_enabled` tinyint(1) NOT NULL DEFAULT 0,
                `sms_template` text,
                `email_enabled` tinyint(1) NOT NULL DEFAULT 0,
                `email_subject` varchar(180) DEFAULT '',
                `email_body` text,
                `whatsapp_enabled` tinyint(1) NOT NULL DEFAULT 0,
                `whatsapp_template` text,
                `rent_due_days_before` int(3) NOT NULL DEFAULT 3,
                `rent_increment_enabled` tinyint(1) NOT NULL DEFAULT 0,
                `rent_increment_type` varchar(12) NOT NULL DEFAULT 'Percentage',
                `rent_increment_value` decimal(15,2) NOT NULL DEFAULT 0.00,
                `created` datetime DEFAULT NULL,
                `modified` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `project_id` (`project_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3"
        );
        foreach ($sql as $q) {
            try {
                $this->DealRental->query($q);
            } catch (Exception $e) {
            }
        }
    }

    protected function _ensureRentalTemplate()
    {
        $this->loadModel('Template');
        $defaults = array(
            'RLT_RENTAL_AGREEMENT' => array('name' => 'Rental Agreement', 'description' => $this->_rentalAgreementTemplateBody()),
            'RLT_RENTAL_RECEIPT' => array('name' => 'Rental Receipt', 'description' => $this->_rentalReceiptTemplateBody())
        );
        foreach ($defaults as $type => $default) {
            $exists = $this->Template->find('first', array(
                'fields' => array('Template.id'),
                'conditions' => array('Template.type' => $type),
                'recursive' => -1
            ));
            if (!empty($exists['Template']['id'])) {
                continue;
            }
            $this->Template->create();
            $this->Template->save(array('Template' => array(
                'name' => $default['name'],
                'type' => $type,
                'description' => $default['description'],
                'status' => 'Published',
                'is_new' => 'No'
            )), false);
        }
    }

    protected function _rentalAgreementTemplateBody()
    {
        return '<div style="font-family:Arial,Helvetica,sans-serif;color:#111;line-height:1.6;font-size:13px;"><h2 style="text-align:center;margin:0 0 12px;">RENTAL AGREEMENT</h2><p><strong>Agreement No:</strong> {{agreement_no}}<br><strong>Date:</strong> {{letter_date}}</p><p>This rental agreement is made between <strong>{{site_organization}}</strong> and tenant <strong>{{tenant_name}}</strong> CNIC <strong>{{tenant_cnic}}</strong> for property/file <strong>{{property_name}}</strong> under deal <strong>{{deal_invoice_no}}</strong>.</p><table style="width:100%;border-collapse:collapse;margin:12px 0;"><tr><td style="border:1px solid #ccc;padding:7px;"><strong>Monthly Rent</strong><br>{{monthly_rent}}</td><td style="border:1px solid #ccc;padding:7px;"><strong>Security Deposit</strong><br>{{security_deposit}}</td><td style="border:1px solid #ccc;padding:7px;"><strong>Period</strong><br>{{start_date}} to {{end_date}}</td></tr></table><p>The tenant shall pay rent every month on or before day {{due_day}}. Any maintenance, utility, society or agreed rental expense may be recorded separately in the rental income module.</p><p><strong>Tenant Contact:</strong> {{tenant_mobile}} {{tenant_email}}<br><strong>Tenant Address:</strong> {{tenant_address}}</p><br><table style="width:100%;margin-top:45px;"><tr><td style="text-align:center;border-top:1px solid #111;padding-top:8px;">Tenant Signature</td><td style="text-align:center;border-top:1px solid #111;padding-top:8px;">Authorized Signature</td></tr></table></div>';
    }

    protected function _rentalReceiptTemplateBody()
    {
        $file = APP.'Lib'.DS.'LetterTemplates'.DS.'rental_receipt.html';
        if (is_readable($file)) {
            $body = file_get_contents($file);
            if (trim((string)$body) !== '') {
                return (string)$body;
            }
        }
        return '<h2 style="text-align:center;">RENTAL PAYMENT RECEIPT</h2><p><strong>Receipt:</strong> {{receipt_no}}<br><strong>Date:</strong> {{receipt_date}}<br><strong>Tenant:</strong> {{tenant_name}}<br><strong>Agreement:</strong> {{agreement_no}}<br><strong>Rent Month:</strong> {{rent_month_label}}</p><p><strong>Amount Received:</strong> {{rent_amount}}<br><strong>Amount in Words:</strong> {{rent_amount_words}}</p>';
    }

    public function index()
    {
        $conditions = array();
        if (!$this->_isSuperAdminSession((array)$this->adminValue)) {
            $conditions['DealRental.project_id'] = (int)$this->agencyId;
        }
        if (!empty($this->request->query['status'])) {
            $conditions['DealRental.status'] = trim((string)$this->request->query['status']);
        }
        if (!empty($this->request->query['client_portal_access'])) {
            $portalAccess = trim((string)$this->request->query['client_portal_access']);
            if (in_array($portalAccess, array('Active', 'Blocked'), true)) {
                $conditions['DealRental.client_portal_access'] = $portalAccess;
            }
        }
        if (!empty($this->request->query['deal_id'])) {
            $conditions['DealRental.deal_id'] = (int)$this->request->query['deal_id'];
        }
        $this->Paginator->settings = $this->paginate;
        $this->Paginator->settings['conditions'] = $conditions;
        $this->Paginator->settings['limit'] = $this->pageLimit;
        $this->Paginator->settings['maxLimit'] = $this->maxLimit;
        $this->Paginator->settings['recursive'] = 0;
        $rentals = $this->Paginator->paginate('DealRental');
        $rentalIds = array();
        foreach ((array)$rentals as $rentalRow) {
            if (!empty($rentalRow['DealRental']['id'])) {
                $rentalIds[] = (int)$rentalRow['DealRental']['id'];
            }
        }
        $clientAccessMap = $this->_rentalClientAccessMap($rentalIds);
        foreach ($rentals as &$rentalRow) {
            $rentalId = !empty($rentalRow['DealRental']['id']) ? (int)$rentalRow['DealRental']['id'] : 0;
            $rentalRow['DealRental']['client_portal_access'] = isset($clientAccessMap[$rentalId]) ? $clientAccessMap[$rentalId] : 'Unknown';
        }
        unset($rentalRow);
        $paidRentalMap = $this->_rentalPaidRentMap($rentalIds);
        $rentalScheduleTotals = $this->_rentalScheduleTotalsMap($rentalIds);
        $rentalPageTotals = array('monthly_rent' => 0, 'paid' => 0, 'due' => 0);
        foreach ((array)$rentals as $rentalRow) {
            $rentalId = !empty($rentalRow['DealRental']['id']) ? (int)$rentalRow['DealRental']['id'] : 0;
            $rentalPageTotals['monthly_rent'] += !empty($rentalRow['DealRental']['monthly_rent']) ? (float)$rentalRow['DealRental']['monthly_rent'] : 0;
            if (isset($rentalScheduleTotals[$rentalId])) {
                $rentalPageTotals['paid'] += (float)$rentalScheduleTotals[$rentalId]['paid'];
                $rentalPageTotals['due'] += (float)$rentalScheduleTotals[$rentalId]['due'];
            }
        }
        foreach ($rentalPageTotals as $key => $value) {
            $rentalPageTotals[$key] = round((float)$value, 2);
        }
        $this->set(compact('rentals', 'paidRentalMap', 'rentalScheduleTotals', 'rentalPageTotals'));
        $this->set('dealOptions', $this->_dealOptions(true));
    }

    public function dashboard()
    {
        return $this->redirect(array('action' => 'index'));
    }

    public function settings($projectId = null)
    {
        $isSuperAdmin = $this->_isSuperAdminSession((array)$this->adminValue);
        $projectOptions = $this->_rentalSettingsProjectOptions();
        if (!$isSuperAdmin) {
            $projectId = (int)$this->agencyId;
        } else {
            $postedProjectId = !empty($this->request->data['DealRentalSetting']['project_id']) ? (int)$this->request->data['DealRentalSetting']['project_id'] : 0;
            $queryProjectId = !empty($this->request->query['project_id']) ? (int)$this->request->query['project_id'] : 0;
            if ($this->request->is(array('post', 'put')) && ($postedProjectId <= 0 || !isset($projectOptions[$postedProjectId]))) {
                throw new NotFoundException(__('Invalid agency for rental settings.'));
            }
            $projectId = $postedProjectId > 0 ? $postedProjectId : ($queryProjectId > 0 ? $queryProjectId : (int)$projectId);
            if ($projectId <= 0 || !isset($projectOptions[$projectId])) {
                $projectIds = array_keys($projectOptions);
                $projectId = !empty($projectIds) ? (int)reset($projectIds) : 0;
            }
        }
        if ($projectId <= 0 || !isset($projectOptions[$projectId])) {
            throw new NotFoundException(__('No valid agency is available for rental settings.'));
        }
        $setting = $this->_rentalSettings($projectId);
        $setting = $this->_applyRentalAccountNumbersToSetting($projectId, $setting);
        $this->set('typeOptions', $this->_propertyTypeOptions());
        $this->set('dealTypeOptions', array('active' => __('Active Deals'), 'paid' => __('Paid Deals'), 'both' => __('Both Active & Paid')));
        $this->set(compact('projectOptions', 'projectId', 'isSuperAdmin'));
        if ($this->request->is(array('post', 'put'))) {
            $data = isset($this->request->data['DealRentalSetting']) ? (array)$this->request->data['DealRentalSetting'] : array();
            $save = $this->_settingsSaveData($data, $setting, $projectId);
            if ($this->DealRentalSetting->save(array('DealRentalSetting' => $save), false)) {
                $this->_syncRentalAccountNumbersFromSettings($projectId, $save);
                $this->Session->setFlash(__('Rental settings have been saved.'), 'flash', array('alert' => 'success'));
                return $this->redirect(array('action' => 'settings', $projectId));
            }
            $this->Session->setFlash(__('Rental settings could not be saved.'), 'flash', array('alert' => 'danger'));
        } else {
            $this->request->data = array('DealRentalSetting' => $setting);
            $this->request->data['DealRentalSetting']['project_id'] = $projectId;
            $this->request->data['DealRentalSetting']['property_type_ids'] = $this->_csvToIds(isset($setting['property_type_ids']) ? $setting['property_type_ids'] : '');
        }
    }

    public function add($dealId = null)
    {
        $this->_prepareForm(true);
        if ($this->request->is('post')) {
            $data = $this->_rentalSaveData($this->request->data);
            $dealIdToSave = !empty($data['DealRental']['deal_id']) ? (int)$data['DealRental']['deal_id'] : 0;
            if ($this->_dealAlreadyHasRental($dealIdToSave)) {
                $this->Session->setFlash(__('A rental agreement already exists for the selected deal.'), 'flash', array('alert' => 'danger'));
                return;
            }
            if (empty($data['DealRental']['agreement_no'])) {
                $data['DealRental']['agreement_no'] = $this->_nextAgreementNo((int)$data['DealRental']['project_id']);
            }
            $this->DealRental->create();
            if ($this->DealRental->save($data, false)) {
                $savedRentalId = (int)$this->DealRental->id;
                $savedRental = $this->_rentalRow($savedRentalId);
                $this->_syncRentalIncrementProposals($savedRental);
                $this->_ensureRentalScheduleRows($savedRental);
                $this->_rebuildRentalScheduleAllocations($savedRentalId);
                $this->Session->setFlash(__('Rental agreement has been saved.'), 'flash', array('alert' => 'success'));
                return $this->redirect(array('action' => 'view', $savedRentalId));
            }
            $this->Session->setFlash(__('Rental agreement could not be saved.'), 'flash', array('alert' => 'danger'));
        } elseif ((int)$dealId > 0) {
            $this->request->data['DealRental']['deal_id'] = (int)$dealId;
        }
    }

    public function edit($id = null)
    {
        $id = (int)$id;
        $row = $this->_rentalRow($id);
        if (empty($row['DealRental']['id'])) {
            throw new NotFoundException(__('Invalid rental agreement'));
        }
        $this->_prepareForm(true, !empty($row['DealRental']['deal_id']) ? (int)$row['DealRental']['deal_id'] : 0);
        if ($this->request->is(array('post', 'put'))) {
            $data = $this->_rentalSaveData($this->request->data, $row);
            $dealIdToSave = !empty($data['DealRental']['deal_id']) ? (int)$data['DealRental']['deal_id'] : 0;
            if ($this->_dealAlreadyHasRental($dealIdToSave, $id)) {
                $this->Session->setFlash(__('A rental agreement already exists for the selected deal.'), 'flash', array('alert' => 'danger'));
                return;
            }
            $data['DealRental']['id'] = $id;
            if ($this->DealRental->save($data, false)) {
                $savedRental = $this->_rentalRow($id);
                $this->_syncRentalIncrementProposals($savedRental);
                $this->_ensureRentalScheduleRows($savedRental);
                $this->_rebuildRentalScheduleAllocations($id);
                $this->Session->setFlash(__('Rental agreement has been updated.'), 'flash', array('alert' => 'success'));
                return $this->redirect(array('action' => 'view', $id));
            }
            $this->Session->setFlash(__('Rental agreement could not be updated.'), 'flash', array('alert' => 'danger'));
        } else {
            $this->request->data = $row;
        }
    }

    public function view($id = null)
    {
        $id = (int)$id;
        $rental = $this->_rentalRow($id);
        if (empty($rental['DealRental']['id'])) {
            throw new NotFoundException(__('Invalid rental agreement'));
        }
        if ($this->request->is('post') && !empty($this->request->data['RentalOperation']['type'])) {
            if ($this->_handleRentalOperationPost($rental)) {
                return $this->redirect(array('action' => 'view', $id));
            }
        }
        $this->_syncRentalIncrementProposals($rental);
        $this->_ensureRentalScheduleRows($rental);
        $this->_rebuildRentalScheduleAllocations($id);
        $schedules = $this->DealRentalSchedule->find('all', array(
            'conditions' => array('DealRentalSchedule.deal_rental_id' => $id),
            'order' => array('DealRentalSchedule.due_date' => 'asc', 'DealRentalSchedule.id' => 'asc'),
            'recursive' => -1
        ));
        $scheduleSummary = $this->_rentalScheduleSummary($id, $schedules);
        $receipts = $this->DealRentalReceipt->find('all', array(
            'conditions' => array('DealRentalReceipt.deal_rental_id' => $id),
            'order' => array('DealRentalReceipt.receipt_date' => 'desc', 'DealRentalReceipt.id' => 'desc'),
            'recursive' => -1
        ));
        $receiptLabels = array();
        foreach ((array)$receipts as $receiptRow) {
            $receiptData = (array)$receiptRow['DealRentalReceipt'];
            $receiptLabels[(int)$receiptData['id']] = !empty($receiptData['receipt_no']) ? $receiptData['receipt_no'] : '#'.(int)$receiptData['id'];
        }
        $scheduleReceiptMap = array();
        $allocationRows = $this->DealRentalReceiptAllocation->find('all', array(
            'fields' => array('DealRentalReceiptAllocation.schedule_id', 'DealRentalReceiptAllocation.receipt_id'),
            'conditions' => array('DealRentalReceiptAllocation.deal_rental_id' => $id),
            'order' => array('DealRentalReceiptAllocation.id' => 'asc'),
            'recursive' => -1
        ));
        foreach ((array)$allocationRows as $allocationRow) {
            $allocation = (array)$allocationRow['DealRentalReceiptAllocation'];
            $scheduleId = (int)$allocation['schedule_id'];
            $receiptId = (int)$allocation['receipt_id'];
            if ($scheduleId <= 0 || $receiptId <= 0) {
                continue;
            }
            if (!isset($scheduleReceiptMap[$scheduleId])) {
                $scheduleReceiptMap[$scheduleId] = array();
            }
            $scheduleReceiptMap[$scheduleId][$receiptId] = isset($receiptLabels[$receiptId]) ? $receiptLabels[$receiptId] : '#'.$receiptId;
        }
        $latestPaidScheduleId = 0;
        foreach ((array)$schedules as $scheduleRow) {
            $schedule = (array)$scheduleRow['DealRentalSchedule'];
            if ((float)$schedule['paid_amount'] > 0.009 && (float)$schedule['balance_amount'] <= 0.009) {
                $latestPaidScheduleId = (int)$schedule['id'];
            }
        }
        $latestPaidReceiptId = 0;
        if ($latestPaidScheduleId > 0 && !empty($scheduleReceiptMap[$latestPaidScheduleId])) {
            foreach ((array)$receipts as $receiptRow) {
                $receiptId = (int)$receiptRow['DealRentalReceipt']['id'];
                if (isset($scheduleReceiptMap[$latestPaidScheduleId][$receiptId])) {
                    $latestPaidReceiptId = $receiptId;
                    break;
                }
            }
        }
        $monthlyExpenses = $this->DealRentalMonthlyExpense->find('all', array(
            'conditions' => array('DealRentalMonthlyExpense.deal_rental_id' => $id),
            'order' => array('DealRentalMonthlyExpense.expense_date' => 'desc', 'DealRentalMonthlyExpense.id' => 'desc'),
            'recursive' => -1
        ));
        $tenantCollections = $this->DealRentalTenantCollection->find('all', array(
            'conditions' => array('DealRentalTenantCollection.deal_rental_id' => $id),
            'order' => array('DealRentalTenantCollection.collection_date' => 'desc', 'DealRentalTenantCollection.id' => 'desc'),
            'recursive' => -1
        ));
        $maintenanceRequests = $this->DealRentalMaintenance->find('all', array(
            'conditions' => array('DealRentalMaintenance.deal_rental_id' => $id),
            'order' => array('DealRentalMaintenance.request_date' => 'desc', 'DealRentalMaintenance.id' => 'desc'),
            'recursive' => -1
        ));
        $inspections = $this->DealRentalInspection->find('all', array(
            'conditions' => array('DealRentalInspection.deal_rental_id' => $id),
            'order' => array('DealRentalInspection.inspection_date' => 'desc', 'DealRentalInspection.id' => 'desc'),
            'recursive' => -1
        ));
        $profitability = $this->_rentalProfitabilitySummary($tenantCollections, $receipts, $maintenanceRequests);
        $paymentAccounts = $this->_rentalPayoutLedgerOptions((int)$rental['DealRental']['project_id']);
        $rentIncrements = $this->DealRentalIncrement->find('all', array(
            'conditions' => array('DealRentalIncrement.deal_rental_id' => $id),
            'order' => array('DealRentalIncrement.effective_date' => 'asc', 'DealRentalIncrement.id' => 'asc'),
            'recursive' => -1
        ));
        $this->set(compact('rental', 'receipts', 'monthlyExpenses', 'schedules', 'scheduleSummary', 'scheduleReceiptMap', 'latestPaidScheduleId', 'latestPaidReceiptId', 'tenantCollections', 'maintenanceRequests', 'inspections', 'profitability', 'paymentAccounts', 'rentIncrements'));
    }

    protected function _handleRentalOperationPost($rental)
    {
        $type = strtolower(trim((string)$this->request->data['RentalOperation']['type']));
        if ($type === 'tenant_collection') {
            return $this->_saveRentalTenantCollection($rental);
        }
        if ($type === 'delete_tenant_collection') {
            return $this->_deleteRentalTenantCollection($rental);
        }
        if ($type === 'maintenance') {
            return $this->_saveRentalMaintenance($rental);
        }
        if ($type === 'delete_maintenance') {
            return $this->_deleteRentalMaintenance($rental);
        }
        if ($type === 'inspection') {
            return $this->_saveRentalInspection($rental);
        }
        if ($type === 'delete_inspection') {
            return $this->_deleteRentalInspection($rental);
        }
        $this->Session->setFlash(__('Invalid rental operation.'), 'flash', array('alert' => 'danger'));
        return false;
    }

    protected function _saveRentalTenantCollection($rental)
    {
        $r = (array)$rental['DealRental'];
        $posted = isset($this->request->data['DealRentalTenantCollection']) ? (array)$this->request->data['DealRentalTenantCollection'] : array();
        $sourceLedgerId = !empty($posted['source_ledger_id']) ? (int)$posted['source_ledger_id'] : 0;
        $rentAmount = $this->_amount($posted, 'rent_amount');
        $lateFee = $this->_amount($posted, 'late_fee');
        $otherAmount = $this->_amount($posted, 'other_amount');
        $total = round($rentAmount + $lateFee + $otherAmount, 2);
        if ($total <= 0 || !$this->_validRentalPayoutSourceLedger((int)$r['project_id'], $sourceLedgerId)) {
            $this->Session->setFlash(__('Enter a collection amount and select a valid agency Cash/Bank account.'), 'flash', array('alert' => 'danger'));
            return false;
        }
        $source = $this->_rentalPayoutSourceLedger((int)$r['project_id'], $sourceLedgerId);
        $collectionNo = 'RTC-'.(int)$r['project_id'].'-'.date('YmdHis').'-'.mt_rand(100, 999);
        $data = array('DealRentalTenantCollection' => array(
            'deal_rental_id' => (int)$r['id'],
            'deal_id' => (int)$r['deal_id'],
            'client_id' => (int)$r['client_id'],
            'project_id' => (int)$r['project_id'],
            'collection_no' => $collectionNo,
            'rent_month' => !empty($posted['rent_month']) ? substr(trim((string)$posted['rent_month']), 0, 7) : date('Y-m'),
            'collection_date' => $this->_dateForSql(isset($posted['collection_date']) ? $posted['collection_date'] : date('Y-m-d')),
            'rent_amount' => $rentAmount,
            'late_fee' => $lateFee,
            'other_amount' => $otherAmount,
            'total_amount' => $total,
            'payment_mode' => !empty($source['Ardledger']['name']) ? $source['Ardledger']['name'] : '',
            'source_ledger_id' => $sourceLedgerId,
            'remarks' => isset($posted['remarks']) ? trim((string)$posted['remarks']) : '',
            'created_by' => (int)$this->luserId
        ));
        $this->_ensureBusinessAccountingSchema();
        $db = $this->DealRentalTenantCollection->getDataSource();
        $db->begin();
        try {
            $this->DealRentalTenantCollection->create();
            if (!$this->DealRentalTenantCollection->save($data, false)) {
                throw new Exception(__('Tenant collection could not be saved.'));
            }
            $collectionId = (int)$this->DealRentalTenantCollection->id;
            if (!$this->_syncRentalCollectionVoucher($collectionId)) {
                throw new Exception(__('Tenant collection could not be posted to Accounts.'));
            }
            $db->commit();
            $this->Session->setFlash(__('Tenant collection has been saved and posted to Rental-Income.'), 'flash', array('alert' => 'success'));
            return true;
        } catch (Exception $e) {
            $db->rollback();
            CakeLog::write('error', 'Rental tenant collection failed: '.$e->getMessage());
            $this->Session->setFlash($e->getMessage(), 'flash', array('alert' => 'danger'));
            return false;
        }
    }

    protected function _deleteRentalTenantCollection($rental)
    {
        $id = !empty($this->request->data['RentalOperation']['id']) ? (int)$this->request->data['RentalOperation']['id'] : 0;
        $row = $this->DealRentalTenantCollection->find('first', array('conditions' => array('DealRentalTenantCollection.id' => $id, 'DealRentalTenantCollection.deal_rental_id' => (int)$rental['DealRental']['id']), 'recursive' => -1));
        if (empty($row['DealRentalTenantCollection']['id'])) {
            $this->Session->setFlash(__('Invalid tenant collection.'), 'flash', array('alert' => 'danger'));
            return false;
        }
        if (!$this->_ardVoidAutoVoucherBySource((int)$row['DealRentalTenantCollection']['project_id'], 'rental_tenant_collection', $id, 'Tenant collection deleted', true)) {
            $this->Session->setFlash(__('The linked income voucher could not be reversed.'), 'flash', array('alert' => 'danger'));
            return false;
        }
        $this->DealRentalTenantCollection->delete($id, false);
        $this->Session->setFlash(__('Tenant collection deleted and its income voucher reversed.'), 'flash', array('alert' => 'success'));
        return true;
    }

    protected function _saveRentalMaintenance($rental)
    {
        $r = (array)$rental['DealRental'];
        $posted = isset($this->request->data['DealRentalMaintenance']) ? (array)$this->request->data['DealRentalMaintenance'] : array();
        $id = !empty($posted['id']) ? (int)$posted['id'] : 0;
        if ($id > 0 && !$this->DealRentalMaintenance->find('count', array('conditions' => array('DealRentalMaintenance.id' => $id, 'DealRentalMaintenance.deal_rental_id' => (int)$r['id']), 'recursive' => -1))) {
            $this->Session->setFlash(__('Invalid maintenance request.'), 'flash', array('alert' => 'danger'));
            return false;
        }
        $statuses = array('Submitted','In Review','Approved','In Progress','Resolved','Rejected');
        $priorities = array('Low','Normal','High','Urgent');
        $data = array(
            'id' => $id > 0 ? $id : null,
            'deal_rental_id' => (int)$r['id'], 'deal_id' => (int)$r['deal_id'], 'client_id' => (int)$r['client_id'], 'project_id' => (int)$r['project_id'],
            'request_no' => !empty($posted['request_no']) ? trim((string)$posted['request_no']) : 'MR-'.(int)$r['project_id'].'-'.date('YmdHis').'-'.mt_rand(100,999),
            'request_date' => $this->_dateForSql(isset($posted['request_date']) ? $posted['request_date'] : date('Y-m-d')),
            'category' => !empty($posted['category']) ? substr(trim((string)$posted['category']), 0, 60) : 'General',
            'priority' => in_array(isset($posted['priority']) ? $posted['priority'] : '', $priorities, true) ? $posted['priority'] : 'Normal',
            'status' => in_array(isset($posted['status']) ? $posted['status'] : '', $statuses, true) ? $posted['status'] : 'Submitted',
            'title' => substr(trim((string)(isset($posted['title']) ? $posted['title'] : '')), 0, 180),
            'description' => trim((string)(isset($posted['description']) ? $posted['description'] : '')),
            'estimated_cost' => $this->_amount($posted, 'estimated_cost'), 'approved_cost' => $this->_amount($posted, 'approved_cost'), 'actual_cost' => $this->_amount($posted, 'actual_cost'),
            'owner_charge_amount' => $this->_amount($posted, 'owner_charge_amount'), 'agency_charge_amount' => $this->_amount($posted, 'agency_charge_amount'),
            'resolution_notes' => trim((string)(isset($posted['resolution_notes']) ? $posted['resolution_notes'] : '')),
            'submitted_from' => $id > 0 && !empty($posted['submitted_from']) ? $posted['submitted_from'] : 'Admin',
            'created_by' => (int)$this->luserId
        );
        if ($data['title'] === '') {
            $this->Session->setFlash(__('Maintenance title is required.'), 'flash', array('alert' => 'danger'));
            return false;
        }
        $this->DealRentalMaintenance->create();
        if ($this->DealRentalMaintenance->save(array('DealRentalMaintenance' => $data), false)) {
            $this->Session->setFlash(__('Maintenance request saved.'), 'flash', array('alert' => 'success'));
            return true;
        }
        $this->Session->setFlash(__('Maintenance request could not be saved.'), 'flash', array('alert' => 'danger'));
        return false;
    }

    protected function _deleteRentalMaintenance($rental)
    {
        $id = !empty($this->request->data['RentalOperation']['id']) ? (int)$this->request->data['RentalOperation']['id'] : 0;
        $deleted = $this->DealRentalMaintenance->deleteAll(array('DealRentalMaintenance.id' => $id, 'DealRentalMaintenance.deal_rental_id' => (int)$rental['DealRental']['id']), false);
        $this->Session->setFlash($deleted ? __('Maintenance request deleted.') : __('Maintenance request could not be deleted.'), 'flash', array('alert' => $deleted ? 'success' : 'danger'));
        return (bool)$deleted;
    }

    protected function _saveRentalInspection($rental)
    {
        $r = (array)$rental['DealRental'];
        $posted = isset($this->request->data['DealRentalInspection']) ? (array)$this->request->data['DealRentalInspection'] : array();
        $id = !empty($posted['id']) ? (int)$posted['id'] : 0;
        if ($id > 0 && !$this->DealRentalInspection->find('count', array('conditions' => array('DealRentalInspection.id' => $id, 'DealRentalInspection.deal_rental_id' => (int)$r['id']), 'recursive' => -1))) {
            $this->Session->setFlash(__('Invalid inspection record.'), 'flash', array('alert' => 'danger'));
            return false;
        }
        $types = array('Possession','Periodic','Handover');
        $statuses = array('Scheduled','Completed','Cancelled');
        $data = array(
            'id' => $id > 0 ? $id : null,
            'deal_rental_id' => (int)$r['id'], 'deal_id' => (int)$r['deal_id'], 'client_id' => (int)$r['client_id'], 'project_id' => (int)$r['project_id'],
            'inspection_type' => in_array(isset($posted['inspection_type']) ? $posted['inspection_type'] : '', $types, true) ? $posted['inspection_type'] : 'Periodic',
            'inspection_date' => $this->_dateForSql(isset($posted['inspection_date']) ? $posted['inspection_date'] : date('Y-m-d')),
            'status' => in_array(isset($posted['status']) ? $posted['status'] : '', $statuses, true) ? $posted['status'] : 'Scheduled',
            'inspector_name' => substr(trim((string)(isset($posted['inspector_name']) ? $posted['inspector_name'] : '')), 0, 160),
            'electricity_meter' => substr(trim((string)(isset($posted['electricity_meter']) ? $posted['electricity_meter'] : '')), 0, 60),
            'gas_meter' => substr(trim((string)(isset($posted['gas_meter']) ? $posted['gas_meter'] : '')), 0, 60),
            'water_meter' => substr(trim((string)(isset($posted['water_meter']) ? $posted['water_meter'] : '')), 0, 60),
            'keys_count' => max(0, (int)(isset($posted['keys_count']) ? $posted['keys_count'] : 0)),
            'security_deposit_status' => substr(trim((string)(isset($posted['security_deposit_status']) ? $posted['security_deposit_status'] : '')), 0, 40),
            'condition_notes' => trim((string)(isset($posted['condition_notes']) ? $posted['condition_notes'] : '')),
            'photos_json' => $this->_saveRentalInspectionPhotos(isset($posted['photos']) ? $posted['photos'] : array(), $id),
            'client_acknowledged' => !empty($posted['client_acknowledged']) ? 1 : 0,
            'created_by' => (int)$this->luserId
        );
        $this->DealRentalInspection->create();
        if ($this->DealRentalInspection->save(array('DealRentalInspection' => $data), false)) {
            $this->Session->setFlash(__('Inspection checklist saved.'), 'flash', array('alert' => 'success'));
            return true;
        }
        $this->Session->setFlash(__('Inspection checklist could not be saved.'), 'flash', array('alert' => 'danger'));
        return false;
    }

    protected function _saveRentalInspectionPhotos($files, $inspectionId = 0)
    {
        $saved = array();
        if ((int)$inspectionId > 0) {
            $existing = $this->DealRentalInspection->field('photos_json', array('DealRentalInspection.id' => (int)$inspectionId));
            $decoded = json_decode((string)$existing, true);
            if (is_array($decoded)) {
                $saved = $decoded;
            }
        }
        $normalized = array();
        if (isset($files['name']) && is_array($files['name'])) {
            foreach ($files['name'] as $index => $name) {
                $normalized[] = array(
                    'name' => $name,
                    'type' => isset($files['type'][$index]) ? $files['type'][$index] : '',
                    'tmp_name' => isset($files['tmp_name'][$index]) ? $files['tmp_name'][$index] : '',
                    'error' => isset($files['error'][$index]) ? $files['error'][$index] : UPLOAD_ERR_NO_FILE,
                    'size' => isset($files['size'][$index]) ? $files['size'][$index] : 0
                );
            }
        } elseif (is_array($files)) {
            foreach ($files as $file) {
                if (is_array($file)) {
                    $normalized[] = $file;
                }
            }
        }
        $allowed = array('jpg','jpeg','png','gif','webp');
        $directory = WWW_ROOT.'files'.DS.'rental_inspections'.DS;
        if (!is_dir($directory)) {
            @mkdir($directory, 0775, true);
        }
        foreach ($normalized as $file) {
            if (!isset($file['error']) || (int)$file['error'] === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            $extension = strtolower(pathinfo(isset($file['name']) ? $file['name'] : '', PATHINFO_EXTENSION));
            if ((int)$file['error'] !== UPLOAD_ERR_OK || !in_array($extension, $allowed, true) || (int)$file['size'] > 5242880 || empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name']) || @getimagesize($file['tmp_name']) === false) {
                continue;
            }
            $name = 'inspection-'.date('YmdHis').'-'.mt_rand(10000,99999).'.'.$extension;
            if (move_uploaded_file($file['tmp_name'], $directory.$name)) {
                $saved[] = $name;
            }
        }
        return json_encode(array_values(array_unique($saved)));
    }

    protected function _deleteRentalInspection($rental)
    {
        $id = !empty($this->request->data['RentalOperation']['id']) ? (int)$this->request->data['RentalOperation']['id'] : 0;
        $deleted = $this->DealRentalInspection->deleteAll(array('DealRentalInspection.id' => $id, 'DealRentalInspection.deal_rental_id' => (int)$rental['DealRental']['id']), false);
        $this->Session->setFlash($deleted ? __('Inspection record deleted.') : __('Inspection record could not be deleted.'), 'flash', array('alert' => $deleted ? 'success' : 'danger'));
        return (bool)$deleted;
    }

    protected function _rentalProfitabilitySummary($collections, $receipts, $maintenance)
    {
        $summary = array('tenant_income' => 0, 'owner_payouts' => 0, 'additional_expenses' => 0, 'maintenance_cost' => 0, 'net_profit' => 0);
        foreach ((array)$collections as $row) {
            $summary['tenant_income'] += (float)$row['DealRentalTenantCollection']['total_amount'];
        }
        foreach ((array)$receipts as $row) {
            $summary['owner_payouts'] += (float)$row['DealRentalReceipt']['rent_amount'];
            $summary['additional_expenses'] += (float)$row['DealRentalReceipt']['expense_amount'];
        }
        foreach ((array)$maintenance as $row) {
            $summary['maintenance_cost'] += (float)$row['DealRentalMaintenance']['agency_charge_amount'];
        }
        $summary['net_profit'] = $summary['tenant_income'] - $summary['owner_payouts'] - $summary['additional_expenses'] - $summary['maintenance_cost'];
        foreach ($summary as $key => $value) {
            $summary[$key] = round((float)$value, 2);
        }
        return $summary;
    }

    public function receipt($rentalId = null)
    {
        $rentalId = (int)$rentalId;
        $rental = $this->_rentalRow($rentalId);
        if (empty($rental['DealRental']['id'])) {
            throw new NotFoundException(__('Invalid rental agreement'));
        }
        $this->_ensureRentalScheduleRows($rental);
        $this->_rebuildRentalScheduleAllocations($rentalId);
        if ($this->request->is('post')) {
            $sourceLedgerId = !empty($this->request->data['DealRentalReceipt']['source_ledger_id']) ? (int)$this->request->data['DealRentalReceipt']['source_ledger_id'] : 0;
            if (!$this->_validRentalPayoutSourceLedger((int)$rental['DealRental']['project_id'], $sourceLedgerId)) {
                $this->Session->setFlash(__('Please select a valid Cash in Hand or Bank account for this agency.'), 'flash', array('alert' => 'danger'));
            } else {
                $data = $this->_receiptSaveData($this->request->data, $rental);
                $this->_ensureBusinessAccountingSchema();
                $db = $this->DealRentalReceipt->getDataSource();
                $db->begin();
                try {
                    $this->DealRentalReceipt->create();
                    if (!$this->DealRentalReceipt->save($data, false)) {
                        throw new Exception(__('Rental receipt could not be saved.'));
                    }
                    $receiptId = (int)$this->DealRentalReceipt->id;
                    if (!$this->_syncRentalPayoutVoucher($receiptId)) {
                        throw new Exception(__('Rental payment could not be posted to Accounts.'));
                    }
                    if (!$this->_syncReceiptExpense($receiptId)) {
                        throw new Exception(__('Rental expense details could not be saved.'));
                    }
                    $db->commit();
                    $this->_rebuildRentalScheduleAllocations($rentalId);
                    $this->Session->setFlash(__('Rental payment has been saved and posted to Accounts.'), 'flash', array('alert' => 'success'));
                    return $this->redirect(array('action' => 'view', $rentalId));
                } catch (Exception $e) {
                    $db->rollback();
                    CakeLog::write('error', 'Rental payment save failed: '.$e->getMessage());
                    $this->Session->setFlash($e->getMessage(), 'flash', array('alert' => 'danger'));
                }
            }
        }
        $selectedSchedule = $this->_selectedRentalSchedule($rentalId);
        if (!$this->request->is('post') && !empty($selectedSchedule['DealRentalSchedule']['id'])) {
            $schedule = $selectedSchedule['DealRentalSchedule'];
            $this->request->data['DealRentalReceipt']['rent_month'] = $schedule['rent_month'];
            $this->request->data['DealRentalReceipt']['rent_amount'] = $schedule['balance_amount'];
            $this->request->data['DealRentalReceipt']['receipt_date'] = date('Y-m-d');
        }
        $paymentAccounts = $this->_rentalPayoutLedgerOptions((int)$rental['DealRental']['project_id']);
        $this->set(compact('rental', 'selectedSchedule', 'paymentAccounts'));
    }

    public function editreceipt($id = null)
    {
        $id = (int)$id;
        $receipt = $this->_receiptRow($id);
        if (empty($receipt['DealRentalReceipt']['id'])) {
            throw new NotFoundException(__('Invalid rental receipt'));
        }
        $rental = $this->_rentalRow((int)$receipt['DealRentalReceipt']['deal_rental_id']);
        if ($this->request->is(array('post', 'put'))) {
            $sourceLedgerId = !empty($this->request->data['DealRentalReceipt']['source_ledger_id']) ? (int)$this->request->data['DealRentalReceipt']['source_ledger_id'] : 0;
            if (!$this->_validRentalPayoutSourceLedger((int)$rental['DealRental']['project_id'], $sourceLedgerId)) {
                $this->Session->setFlash(__('Please select a valid Cash in Hand or Bank account for this agency.'), 'flash', array('alert' => 'danger'));
            } else {
                $data = $this->_receiptSaveData($this->request->data, $rental, $receipt);
                $data['DealRentalReceipt']['id'] = $id;
                $this->_ensureBusinessAccountingSchema();
                $db = $this->DealRentalReceipt->getDataSource();
                $db->begin();
                try {
                    if (!$this->DealRentalReceipt->save($data, false)) {
                        throw new Exception(__('Rental receipt could not be updated.'));
                    }
                    if (!$this->_syncRentalPayoutVoucher($id)) {
                        throw new Exception(__('Rental payment could not be updated in Accounts.'));
                    }
                    if (!$this->_syncReceiptExpense($id)) {
                        throw new Exception(__('Rental expense details could not be updated.'));
                    }
                    $db->commit();
                    $this->_rebuildRentalScheduleAllocations((int)$receipt['DealRentalReceipt']['deal_rental_id']);
                    $this->Session->setFlash(__('Rental payment and Accounts posting have been updated.'), 'flash', array('alert' => 'success'));
                    return $this->redirect(array('action' => 'view', (int)$receipt['DealRentalReceipt']['deal_rental_id']));
                } catch (Exception $e) {
                    $db->rollback();
                    CakeLog::write('error', 'Rental payment update failed: '.$e->getMessage());
                    $this->Session->setFlash($e->getMessage(), 'flash', array('alert' => 'danger'));
                }
            }
        } else {
            $this->request->data = $receipt;
        }
        $paymentAccounts = $this->_rentalPayoutLedgerOptions((int)$rental['DealRental']['project_id']);
        $this->set(compact('rental', 'receipt', 'paymentAccounts'));
        $this->render('receipt');
    }

    public function delete($id = null)
    {
        $id = (int)$id;
        $row = $this->_rentalRow($id);
        if (empty($row['DealRental']['id'])) {
            throw new NotFoundException(__('Invalid rental agreement'));
        }
        if ($this->_rentalHasPaidRent($id)) {
            $this->Session->setFlash(__('This rental agreement cannot be deleted because paid rent has already been recorded.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        $this->DealRentalIncrement->deleteAll(array('DealRentalIncrement.deal_rental_id' => $id), false);
        if ($this->DealRental->delete($id, false)) {
            $this->Session->setFlash(__('Rental agreement has been deleted.'), 'flash', array('alert' => 'success'));
        }
        return $this->redirect(array('action' => 'index'));
    }

    public function approveincrement($id = null)
    {
        if (!$this->request->is('post')) {
            throw new MethodNotAllowedException();
        }
        if (empty($this->userPermissionArr['update_right'])) {
            throw new ForbiddenException(__('You do not have permission to approve rent increments.'));
        }
        $increment = $this->DealRentalIncrement->find('first', array(
            'conditions' => array('DealRentalIncrement.id' => (int)$id),
            'recursive' => -1
        ));
        $rentalId = !empty($increment['DealRentalIncrement']['deal_rental_id']) ? (int)$increment['DealRentalIncrement']['deal_rental_id'] : 0;
        $rental = $this->_rentalRow($rentalId);
        if (empty($rental['DealRental']['id']) || empty($increment['DealRentalIncrement']['id'])) {
            throw new NotFoundException(__('Invalid rent increment proposal'));
        }
        if ($increment['DealRentalIncrement']['status'] !== 'Pending') {
            $this->Session->setFlash(__('Only pending rent increments can be approved.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'view', $rentalId, '#' => 'rent-increments'));
        }
        $oldestPending = $this->DealRentalIncrement->find('first', array(
            'conditions' => array('DealRentalIncrement.deal_rental_id' => $rentalId, 'DealRentalIncrement.status' => 'Pending'),
            'order' => array('DealRentalIncrement.effective_date' => 'asc', 'DealRentalIncrement.id' => 'asc'),
            'recursive' => -1
        ));
        if (empty($oldestPending['DealRentalIncrement']['id']) || (int)$oldestPending['DealRentalIncrement']['id'] !== (int)$id) {
            $this->Session->setFlash(__('Approve or reject the oldest pending increment first.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'view', $rentalId, '#' => 'rent-increments'));
        }
        $proposal = $increment['DealRentalIncrement'];
        $baseRent = (float)$rental['DealRental']['monthly_rent'];
        $oldRent = $this->_approvedRentAtDate($rentalId, $baseRent, $proposal['effective_date']);
        $newRent = $this->_incrementedRent($oldRent, $proposal['increment_type'], (float)$proposal['increment_value']);
        $saved = $this->DealRentalIncrement->save(array('DealRentalIncrement' => array(
            'id' => (int)$proposal['id'],
            'old_rent' => $oldRent,
            'new_rent' => $newRent,
            'status' => 'Approved',
            'approved_by' => !empty($this->adminValue['User']['id']) ? (int)$this->adminValue['User']['id'] : 0,
            'approved_at' => date('Y-m-d H:i:s')
        )), false);
        if ($saved) {
            $rental = $this->_rentalRow($rentalId);
            $this->_syncRentalIncrementProposals($rental);
            $this->_ensureRentalScheduleRows($rental);
            $this->_rebuildRentalScheduleAllocations($rentalId);
            $this->Session->setFlash(__('Rent increment approved and future unpaid schedules updated.'), 'flash', array('alert' => 'success'));
        } else {
            $this->Session->setFlash(__('Rent increment could not be approved.'), 'flash', array('alert' => 'danger'));
        }
        return $this->redirect(array('action' => 'view', $rentalId, '#' => 'rent-increments'));
    }

    public function rejectincrement($id = null)
    {
        if (!$this->request->is('post')) {
            throw new MethodNotAllowedException();
        }
        if (empty($this->userPermissionArr['update_right'])) {
            throw new ForbiddenException(__('You do not have permission to reject rent increments.'));
        }
        $increment = $this->DealRentalIncrement->find('first', array('conditions' => array('DealRentalIncrement.id' => (int)$id), 'recursive' => -1));
        $rentalId = !empty($increment['DealRentalIncrement']['deal_rental_id']) ? (int)$increment['DealRentalIncrement']['deal_rental_id'] : 0;
        $rental = $this->_rentalRow($rentalId);
        if (empty($rental['DealRental']['id']) || empty($increment['DealRentalIncrement']['id'])) {
            throw new NotFoundException(__('Invalid rent increment proposal'));
        }
        if ($increment['DealRentalIncrement']['status'] === 'Pending') {
            $this->DealRentalIncrement->save(array('DealRentalIncrement' => array('id' => (int)$id, 'status' => 'Rejected')), false);
            $this->_syncRentalIncrementProposals($rental);
            $this->Session->setFlash(__('Rent increment proposal rejected. Rental schedules were not changed.'), 'flash', array('alert' => 'success'));
        }
        return $this->redirect(array('action' => 'view', $rentalId, '#' => 'rent-increments'));
    }

    public function deletereceipt($id = null)
    {
        $id = (int)$id;
        $row = $this->_receiptRow($id);
        if (empty($row['DealRentalReceipt']['id'])) {
            throw new NotFoundException(__('Invalid rental receipt'));
        }
        $rentalId = (int)$row['DealRentalReceipt']['deal_rental_id'];
        if (!$this->_ardVoidAutoVoucherBySource((int)$row['DealRentalReceipt']['project_id'], 'rental_payment', $id, 'Rental payment deleted', true)) {
            $this->Session->setFlash(__('Rental payment could not be deleted because its Accounts voucher could not be reversed.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'view', $rentalId));
        }
        $this->_deleteLinkedExpense($row['DealRentalReceipt']);
        $this->DealRentalReceipt->delete($id, false);
        $this->_rebuildRentalScheduleAllocations($rentalId);
        $this->Session->setFlash(__('Rental receipt has been deleted.'), 'flash', array('alert' => 'success'));
        return $this->redirect(array('action' => 'view', $rentalId));
    }

    public function reports()
    {
        $query = (array)$this->request->query;
        $allDates = !empty($query['all_dates']);
        $hasDateFilter = array_key_exists('from', $query) || array_key_exists('to', $query);
        if ($allDates) {
            $fromDate = null;
            $toDate = null;
        } elseif ($hasDateFilter) {
            $fromDate = $this->_rentalReportDate(isset($query['from']) ? $query['from'] : '');
            $toDate = $this->_rentalReportDate(isset($query['to']) ? $query['to'] : '');
        } else {
            $fromDate = date('Y-m-01');
            $toDate = date('Y-m-t');
        }
        if ($fromDate && $toDate && $fromDate > $toDate) {
            $swapDate = $fromDate;
            $fromDate = $toDate;
            $toDate = $swapDate;
            $this->Session->setFlash(__('The report dates were reversed and have been corrected.'), 'flash', array('alert' => 'warning'));
        }

        $isSuperAdmin = $this->_isSuperAdminSession((array)$this->adminValue);
        $selectedProjectId = $isSuperAdmin ? (!empty($query['project_id']) ? (int)$query['project_id'] : 0) : (int)$this->agencyId;
        $selectedRentalId = !empty($query['rental_id']) ? (int)$query['rental_id'] : 0;
        $selectedStatus = !empty($query['status']) && in_array($query['status'], array('Active', 'Closed', 'Cancelled'), true) ? $query['status'] : '';

        $scopeConditions = array();
        if (!$isSuperAdmin || $selectedProjectId > 0) {
            $scopeConditions['DealRental.project_id'] = $selectedProjectId;
        }
        $optionRows = $this->DealRental->find('all', array(
            'fields' => array('DealRental.id', 'DealRental.agreement_no', 'DealRental.deal_id', 'Client.name', 'Deal.invoice_no'),
            'conditions' => $scopeConditions,
            'order' => array('Client.name' => 'asc', 'DealRental.id' => 'desc'),
            'recursive' => 0
        ));
        $rentalOptions = array();
        foreach ((array)$optionRows as $optionRow) {
            $rental = (array)$optionRow['DealRental'];
            $label = !empty($optionRow['Client']['name']) ? $optionRow['Client']['name'] : __('No Client Name');
            $label .= ' - '.(!empty($rental['agreement_no']) ? $rental['agreement_no'] : '#'.(int)$rental['id']);
            if (!empty($optionRow['Deal']['invoice_no'])) {
                $label .= ' - '.$optionRow['Deal']['invoice_no'];
            }
            $rentalOptions[(int)$rental['id']] = $label;
        }

        $rentalConditions = $scopeConditions;
        if ($selectedRentalId > 0) {
            $rentalConditions['DealRental.id'] = $selectedRentalId;
        }
        if ($selectedStatus !== '') {
            $rentalConditions['DealRental.status'] = $selectedStatus;
        }
        $rentals = $this->DealRental->find('all', array(
            'conditions' => $rentalConditions,
            'order' => array('Client.name' => 'asc', 'DealRental.id' => 'desc'),
            'recursive' => 0
        ));
        $rentalIds = array();
        foreach ((array)$rentals as $rentalRow) {
            $rentalIds[] = (int)$rentalRow['DealRental']['id'];
        }

        $today = date('Y-m-d');
        $scheduleMap = $this->_rentalReportAggregateMap($this->DealRentalSchedule, $rentalIds, 'due_date', $fromDate, $toDate, array(
            'scheduled' => 'DealRentalSchedule.rent_amount',
            'schedule_paid' => 'DealRentalSchedule.paid_amount',
            'outstanding' => 'DealRentalSchedule.balance_amount',
            'overdue' => "CASE WHEN DealRentalSchedule.due_date < '".$today."' THEN DealRentalSchedule.balance_amount ELSE 0 END",
            'upcoming' => "CASE WHEN DealRentalSchedule.due_date >= '".$today."' THEN DealRentalSchedule.balance_amount ELSE 0 END"
        ));
        $receiptMap = $this->_rentalReportAggregateMap($this->DealRentalReceipt, $rentalIds, 'receipt_date', $fromDate, $toDate, array(
            'owner_payouts' => 'DealRentalReceipt.rent_amount',
            'receipt_expenses' => 'DealRentalReceipt.expense_amount',
            'owner_outflow' => '(DealRentalReceipt.rent_amount + DealRentalReceipt.expense_amount)'
        ));
        $collectionMap = $this->_rentalReportAggregateMap($this->DealRentalTenantCollection, $rentalIds, 'collection_date', $fromDate, $toDate, array(
            'tenant_rent' => 'DealRentalTenantCollection.rent_amount',
            'late_fees' => 'DealRentalTenantCollection.late_fee',
            'other_income' => 'DealRentalTenantCollection.other_amount',
            'tenant_income' => 'DealRentalTenantCollection.total_amount'
        ));
        $expenseMap = $this->_rentalReportAggregateMap($this->DealRentalMonthlyExpense, $rentalIds, 'expense_date', $fromDate, $toDate, array(
            'additional_expenses' => 'DealRentalMonthlyExpense.amount'
        ));
        $maintenanceMap = $this->_rentalReportAggregateMap($this->DealRentalMaintenance, $rentalIds, 'request_date', $fromDate, $toDate, array(
            'maintenance_cost' => 'DealRentalMaintenance.agency_charge_amount',
            'owner_charges' => 'DealRentalMaintenance.owner_charge_amount'
        ));
        $creditMap = $this->_rentalReportCreditMap($rentalIds, $fromDate, $toDate);

        $metricKeys = array('scheduled', 'schedule_paid', 'outstanding', 'overdue', 'upcoming', 'owner_payouts', 'owner_outflow', 'tenant_rent', 'late_fees', 'other_income', 'tenant_income', 'additional_expenses', 'maintenance_cost', 'owner_charges', 'credit', 'net_profit');
        $totals = array_fill_keys($metricKeys, 0);
        $reportRows = array();
        foreach ((array)$rentals as $rentalRow) {
            $rentalId = (int)$rentalRow['DealRental']['id'];
            $metrics = array_fill_keys($metricKeys, 0);
            foreach (array($scheduleMap, $receiptMap, $collectionMap, $expenseMap, $maintenanceMap, $creditMap) as $metricMap) {
                if (!empty($metricMap[$rentalId])) {
                    foreach ($metricMap[$rentalId] as $key => $value) {
                        if (array_key_exists($key, $metrics)) {
                            $metrics[$key] = round((float)$value, 2);
                        }
                    }
                }
            }
            $metrics['net_profit'] = round($metrics['tenant_income'] - $metrics['owner_payouts'] - $metrics['additional_expenses'] - $metrics['maintenance_cost'], 2);
            foreach ($metrics as $key => $value) {
                $totals[$key] += (float)$value;
            }
            $rentalRow['Report'] = $metrics;
            $reportRows[] = $rentalRow;
        }
        foreach ($totals as $key => $value) {
            $totals[$key] = round((float)$value, 2);
        }

        $receiptConditions = $this->_rentalReportDetailConditions('DealRentalReceipt', $rentalIds, 'receipt_date', $fromDate, $toDate);
        $collectionConditions = $this->_rentalReportDetailConditions('DealRentalTenantCollection', $rentalIds, 'collection_date', $fromDate, $toDate);
        $expenseConditions = $this->_rentalReportDetailConditions('DealRentalMonthlyExpense', $rentalIds, 'expense_date', $fromDate, $toDate);
        $maintenanceConditions = $this->_rentalReportDetailConditions('DealRentalMaintenance', $rentalIds, 'request_date', $fromDate, $toDate);
        $receipts = empty($rentalIds) ? array() : $this->DealRentalReceipt->find('all', array('conditions' => $receiptConditions, 'order' => array('DealRentalReceipt.receipt_date' => 'desc', 'DealRentalReceipt.id' => 'desc'), 'recursive' => 0));
        $tenantCollections = empty($rentalIds) ? array() : $this->DealRentalTenantCollection->find('all', array('conditions' => $collectionConditions, 'order' => array('DealRentalTenantCollection.collection_date' => 'desc', 'DealRentalTenantCollection.id' => 'desc'), 'recursive' => 0));
        $monthlyExpenses = empty($rentalIds) ? array() : $this->DealRentalMonthlyExpense->find('all', array('conditions' => $expenseConditions, 'order' => array('DealRentalMonthlyExpense.expense_date' => 'desc', 'DealRentalMonthlyExpense.id' => 'desc'), 'recursive' => 0));
        $maintenanceRequests = empty($rentalIds) ? array() : $this->DealRentalMaintenance->find('all', array('conditions' => $maintenanceConditions, 'order' => array('DealRentalMaintenance.request_date' => 'desc', 'DealRentalMaintenance.id' => 'desc'), 'recursive' => 0));
        foreach($receipts as &$receiptRow) {
            if(!empty($receiptRow['DealRentalReceipt']['receipt_date'])) {$receiptRow['DealRentalReceipt']['receipt_date'] = date('d-M-Y', strtotime($receiptRow['DealRentalReceipt']['receipt_date']));}
            if(!empty($receiptRow['DealRentalReceipt']['rent_month'])) {$receiptRow['DealRentalReceipt']['rent_month'] = date('M Y', strtotime($receiptRow['DealRentalReceipt']['rent_month'].'-01'));}
        }
        unset($receiptRow);
        foreach($tenantCollections as &$collectionRow) {
            if(!empty($collectionRow['DealRentalTenantCollection']['collection_date'])) {$collectionRow['DealRentalTenantCollection']['collection_date'] = date('d-M-Y', strtotime($collectionRow['DealRentalTenantCollection']['collection_date']));}
            if(!empty($collectionRow['DealRentalTenantCollection']['rent_month'])) {$collectionRow['DealRentalTenantCollection']['rent_month'] = date('M Y', strtotime($collectionRow['DealRentalTenantCollection']['rent_month'].'-01'));}
        }
        unset($collectionRow);
        foreach($monthlyExpenses as &$expenseRow) {
            if(!empty($expenseRow['DealRentalMonthlyExpense']['expense_date'])) {$expenseRow['DealRentalMonthlyExpense']['expense_date'] = date('d-M-Y', strtotime($expenseRow['DealRentalMonthlyExpense']['expense_date']));}
            if(!empty($expenseRow['DealRentalMonthlyExpense']['expense_month'])) {$expenseRow['DealRentalMonthlyExpense']['expense_month'] = date('M Y', strtotime($expenseRow['DealRentalMonthlyExpense']['expense_month'].'-01'));}
        }
        unset($expenseRow);
        foreach($maintenanceRequests as &$maintenanceRow) {
            if(!empty($maintenanceRow['DealRentalMaintenance']['request_date'])) {$maintenanceRow['DealRentalMaintenance']['request_date'] = date('d-M-Y', strtotime($maintenanceRow['DealRentalMaintenance']['request_date']));}
        }
        unset($maintenanceRow);
        $projectOptions = $isSuperAdmin ? $this->_rentalSettingsProjectOptions() : array();
        $reportPeriod = $fromDate || $toDate ? trim(($fromDate ? date('d-M-Y', strtotime($fromDate)) : __('Beginning')).' '.__('to').' '.($toDate ? date('d-M-Y', strtotime($toDate)) : __('Today'))) : __('All Dates');
        $this->set(compact('reportRows', 'receipts', 'tenantCollections', 'monthlyExpenses', 'maintenanceRequests', 'totals', 'rentalOptions', 'projectOptions', 'isSuperAdmin', 'selectedProjectId', 'selectedRentalId', 'selectedStatus', 'fromDate', 'toDate', 'allDates', 'reportPeriod'));
    }

    protected function _rentalReportDate($value)
    {
        $value = trim((string)$value);
        if ($value === '') {
            return null;
        }
        $time = strtotime($value);
        return $time ? date('Y-m-d', $time) : null;
    }

    protected function _rentalReportDetailConditions($alias, $rentalIds, $dateField, $fromDate, $toDate)
    {
        $conditions = array($alias.'.deal_rental_id' => array_values(array_unique(array_map('intval', (array)$rentalIds))));
        if ($fromDate) {
            $conditions[$alias.'.'.$dateField.' >='] = $fromDate;
        }
        if ($toDate) {
            $conditions[$alias.'.'.$dateField.' <='] = $toDate;
        }
        return $conditions;
    }

    protected function _rentalReportAggregateMap($model, $rentalIds, $dateField, $fromDate, $toDate, $sumFields)
    {
        $rentalIds = array_values(array_unique(array_filter(array_map('intval', (array)$rentalIds))));
        if (empty($rentalIds)) {
            return array();
        }
        $alias = $model->alias;
        $fields = array($alias.'.deal_rental_id');
        foreach ((array)$sumFields as $key => $expression) {
            $fields[] = 'SUM('.$expression.') AS '.$key;
        }
        $conditions = $this->_rentalReportDetailConditions($alias, $rentalIds, $dateField, $fromDate, $toDate);
        $rows = $model->find('all', array('fields' => $fields, 'conditions' => $conditions, 'group' => array($alias.'.deal_rental_id'), 'recursive' => -1));
        $map = array();
        foreach ((array)$rows as $row) {
            $rentalId = !empty($row[$alias]['deal_rental_id']) ? (int)$row[$alias]['deal_rental_id'] : 0;
            if ($rentalId <= 0) {
                continue;
            }
            $map[$rentalId] = array();
            foreach ((array)$sumFields as $key => $expression) {
                $map[$rentalId][$key] = round(isset($row[0][$key]) ? (float)$row[0][$key] : 0, 2);
            }
        }
        return $map;
    }

    protected function _rentalReportCreditMap($rentalIds, $fromDate, $toDate)
    {
        $rentalIds = array_values(array_unique(array_filter(array_map('intval', (array)$rentalIds))));
        if (empty($rentalIds)) {
            return array();
        }
        $dateSql = '';
        if ($fromDate) {
            $dateSql .= " AND rr.receipt_date >= '".$fromDate."'";
        }
        if ($toDate) {
            $dateSql .= " AND rr.receipt_date <= '".$toDate."'";
        }
        $rows = $this->DealRentalReceipt->query(
            'SELECT rr.deal_rental_id, GREATEST(0, SUM(rr.rent_amount)-SUM(COALESCE(a.allocated_amount,0))) AS credit '
            .'FROM deal_rental_receipts rr LEFT JOIN (SELECT receipt_id, SUM(allocated_amount) AS allocated_amount FROM deal_rental_receipt_allocations GROUP BY receipt_id) a ON a.receipt_id=rr.id '
            .'WHERE rr.deal_rental_id IN ('.implode(',', $rentalIds).')'.$dateSql.' GROUP BY rr.deal_rental_id'
        );
        $map = array();
        foreach ((array)$rows as $row) {
            $rentalId = !empty($row['rr']['deal_rental_id']) ? (int)$row['rr']['deal_rental_id'] : (!empty($row[0]['deal_rental_id']) ? (int)$row[0]['deal_rental_id'] : 0);
            $credit = isset($row[0]['credit']) ? (float)$row[0]['credit'] : (!empty($row['rr']['credit']) ? (float)$row['rr']['credit'] : 0);
            if ($rentalId > 0) {
                $map[$rentalId] = array('credit' => round($credit, 2));
            }
        }
        return $map;
    }

    public function agreement($id = null)
    {
        $this->layout = null;
        $rental = $this->_rentalRow((int)$id);
        if (empty($rental['DealRental']['id'])) {
            throw new NotFoundException(__('Invalid rental agreement'));
        }
        $html = $this->_renderRentalAgreement($rental);
        $this->set(compact('rental', 'html'));
    }

    public function printreceipt($id = null)
    {
        $this->layout = null;
        $receipt = $this->_receiptRow((int)$id);
        if (empty($receipt['DealRentalReceipt']['id'])) {
            throw new NotFoundException(__('Invalid rental receipt'));
        }
        $rentalId = (int)$receipt['DealRentalReceipt']['deal_rental_id'];
        $rental = $this->_rentalRow($rentalId);
        if (empty($rental['DealRental']['id'])) {
            throw new NotFoundException(__('Invalid rental agreement'));
        }
        $schedules = $this->DealRentalSchedule->find('all', array(
            'conditions' => array('DealRentalSchedule.deal_rental_id' => $rentalId),
            'order' => array('DealRentalSchedule.due_date' => 'asc', 'DealRentalSchedule.id' => 'asc'),
            'recursive' => -1
        ));
        $scheduleSummary = $this->_rentalScheduleSummary($rentalId, $schedules);
        $allocations = $this->DealRentalReceiptAllocation->find('all', array(
            'conditions' => array('DealRentalReceiptAllocation.receipt_id' => (int)$receipt['DealRentalReceipt']['id']),
            'order' => array('DealRentalSchedule.due_date' => 'asc', 'DealRentalSchedule.id' => 'asc'),
            'recursive' => 0
        ));
        $templateData = $this->_rentalReceiptTemplateData($receipt, $rental, $scheduleSummary, $allocations);
        $receiptTemplateBody = $templateData['body'];
        $receiptReplacements = $templateData['replacements'];
        $this->set(compact('receipt', 'rental', 'scheduleSummary', 'allocations', 'receiptTemplateBody', 'receiptReplacements'));
    }

    protected function _prepareForm($excludeRentalDeals = false, $includeDealId = 0)
    {
        $this->set('dealOptions', $this->_dealOptions(false, $excludeRentalDeals, $includeDealId));
        $this->set('statusOptions', array('Active' => __('Active'), 'Closed' => __('Closed'), 'Cancelled' => __('Cancelled')));
        if (!$this->request->is(array('post', 'put')) && empty($this->request->data['DealRental']['id'])) {
            $settings = $this->_rentalSettings($this->_settingsProjectId());
            $this->request->data['DealRental']['rent_increment_enabled'] = !empty($settings['rent_increment_enabled']) ? 1 : 0;
            $this->request->data['DealRental']['rent_increment_type'] = !empty($settings['rent_increment_type']) ? $settings['rent_increment_type'] : 'Percentage';
            $this->request->data['DealRental']['rent_increment_value'] = isset($settings['rent_increment_value']) ? $settings['rent_increment_value'] : 0;
        }
    }

    protected function _dealOptions($rentalOnly = false, $excludeRentalDeals = false, $includeDealId = 0)
    {
        $this->loadModel('Admin.Deal');
        $conditions = array();
        if (!$this->_isSuperAdminSession((array)$this->adminValue)) {
            $conditions['Property.project_id'] = (int)$this->agencyId;
        }
        if ($rentalOnly) {
            $conditions[] = 'EXISTS (SELECT 1 FROM deal_rentals DealRentalFilter WHERE DealRentalFilter.deal_id = Deal.id)';
        } elseif ($excludeRentalDeals) {
            $includeDealId = max(0, (int)$includeDealId);
            $includeCurrent = $includeDealId > 0 ? ' OR Deal.id = '.$includeDealId : '';
            $conditions[] = '(NOT EXISTS (SELECT 1 FROM deal_rentals DealRentalFilter WHERE DealRentalFilter.deal_id = Deal.id)'.$includeCurrent.')';
        }
        $settings = $this->_rentalSettings($this->_settingsProjectId());
        $dealType = isset($settings['eligible_deal_type']) ? strtolower(trim((string)$settings['eligible_deal_type'])) : 'both';
        $propertyTypeIds = $this->_csvToIds(isset($settings['property_type_ids']) ? $settings['property_type_ids'] : '');
        if (!empty($propertyTypeIds)) {
            $conditions['Property.type_id'] = $propertyTypeIds;
        }
        if ($dealType === 'active') {
            $conditions['Deal.deal_status'] = 'Active';
        } elseif ($dealType === 'paid') {
            $conditions[] = 'COALESCE((SELECT SUM(dp.amount) FROM deals_payments dp WHERE dp.deal_id = Deal.id),0) >= COALESCE(Deal.total_amount,0)';
        } elseif ($dealType === 'both') {
            $conditions[] = "(Deal.deal_status = 'Active' OR COALESCE((SELECT SUM(dp.amount) FROM deals_payments dp WHERE dp.deal_id = Deal.id),0) >= COALESCE(Deal.total_amount,0))";
        }
        $rows = $this->Deal->find('all', array(
            'fields' => array('Deal.id', 'Deal.invoice_no', 'Client.name', 'Property.name'),
            'conditions' => $conditions,
            'recursive' => 0,
            'order' => array('Client.name' => 'asc', 'Deal.id' => 'desc'),
            'limit' => 500
        ));
        $options = array();
        foreach ((array)$rows as $row) {
            $deal = $row['Deal'];
            $label = !empty($row['Client']['name']) ? $row['Client']['name'] : __('No Client Name');
            $label .= ' - '.(!empty($deal['invoice_no']) ? $deal['invoice_no'] : 'Deal #'.(int)$deal['id']);
            if (!empty($row['Property']['name'])) {
                $label .= ' - '.$row['Property']['name'];
            }
            $label .= ' - #'.(int)$deal['id'];
            $options[(int)$deal['id']] = $label;
        }
        return $options;
    }

    protected function _dealAlreadyHasRental($dealId, $excludeRentalId = 0)
    {
        $dealId = (int)$dealId;
        if ($dealId <= 0) {
            return false;
        }
        $conditions = array('DealRental.deal_id' => $dealId);
        if ((int)$excludeRentalId > 0) {
            $conditions['DealRental.id !='] = (int)$excludeRentalId;
        }
        if (!$this->_isSuperAdminSession((array)$this->adminValue)) {
            $conditions['DealRental.project_id'] = (int)$this->agencyId;
        }
        return $this->DealRental->find('count', array('conditions' => $conditions, 'recursive' => -1)) > 0;
    }

    protected function _rentalSettingsProjectOptions()
    {
        $this->loadModel('Project');
        $conditions = array();
        if (!$this->_isSuperAdminSession((array)$this->adminValue)) {
            $conditions['Project.id'] = (int)$this->agencyId;
        }
        return (array)$this->Project->find('list', array(
            'fields' => array('Project.id', 'Project.name'),
            'conditions' => $conditions,
            'order' => array('Project.name' => 'asc'),
            'recursive' => -1
        ));
    }

    protected function _settingsProjectId()
    {
        return $this->_isSuperAdminSession((array)$this->adminValue) ? 0 : (int)$this->agencyId;
    }

    protected function _defaultRentalSettings($projectId)
    {
        return array(
            'id' => 0,
            'project_id' => (int)$projectId,
            'agreement_prefix' => 'RA-',
            'agreement_next_no' => 1,
            'receipt_prefix' => 'RR-',
            'receipt_next_no' => 1,
            'eligible_deal_type' => 'both',
            'property_type_ids' => '',
            'sms_enabled' => 0,
            'sms_template' => 'Dear {{tenant_name}}, your rental agreement {{agreement_no}} is created for {{property_name}}. Monthly rent {{monthly_rent}}.',
            'email_enabled' => 0,
            'email_subject' => 'Rental Agreement {{agreement_no}}',
            'email_body' => '<p>Dear {{tenant_name}},</p><p>Your rental agreement <strong>{{agreement_no}}</strong> has been created for <strong>{{property_name}}</strong>.</p><p>Monthly Rent: <strong>{{monthly_rent}}</strong></p>',
            'whatsapp_enabled' => 0,
            'whatsapp_template' => 'Rental Agreement {{agreement_no}} for {{property_name}}. Monthly rent {{monthly_rent}}.',
            'rent_due_days_before' => 3,
            'rent_increment_enabled' => 0,
            'rent_increment_type' => 'Percentage',
            'rent_increment_value' => 0
        );
    }

    protected function _rentalSettings($projectId = 0)
    {
        $projectId = (int)$projectId;
        $row = (array)$this->DealRentalSetting->find('first', array(
            'conditions' => array('DealRentalSetting.project_id' => $projectId),
            'recursive' => -1
        ));
        if (!empty($row['DealRentalSetting']['id'])) {
            return array_merge($this->_defaultRentalSettings($projectId), (array)$row['DealRentalSetting']);
        }
        $defaults = $this->_defaultRentalSettings($projectId);
        if ($projectId > 0) {
            $globalRow = (array)$this->DealRentalSetting->find('first', array(
                'conditions' => array('DealRentalSetting.project_id' => 0),
                'recursive' => -1
            ));
            if (!empty($globalRow['DealRentalSetting'])) {
                $defaults = array_merge($defaults, (array)$globalRow['DealRentalSetting']);
                $defaults['id'] = 0;
                $defaults['project_id'] = $projectId;
                $defaults['agreement_next_no'] = 1;
                $defaults['receipt_next_no'] = 1;
            }
        }
        $this->DealRentalSetting->create();
        if ($this->DealRentalSetting->save(array('DealRentalSetting' => $defaults), false)) {
            $defaults['id'] = (int)$this->DealRentalSetting->id;
        }
        return $defaults;
    }

    protected function _applyRentalAccountNumbersToSetting($projectId, $setting)
    {
        if (!$this->_rentalAccountNumberColumnsReady()) {
            return $setting;
        }
        $rows = $this->DealRental->query("SELECT `rental_receipt_prefix`,`rental_receipt_next_no`,`rental_agreement_prefix`,`rental_agreement_next_no`
            FROM `ardaccounts_settings` WHERE `project_id`=".(int)$projectId." LIMIT 1");
        $account = array();
        if (!empty($rows[0])) {
            foreach ($rows[0] as $rowBlock) {
                if (is_array($rowBlock) && !empty($rowBlock)) {
                    $account = $rowBlock;
                    break;
                }
            }
        }
        if (!empty($account)) {
            $setting['receipt_prefix'] = !empty($account['rental_receipt_prefix']) ? $account['rental_receipt_prefix'] : $setting['receipt_prefix'];
            $setting['receipt_next_no'] = max(1, (int)$account['rental_receipt_next_no']);
            $setting['agreement_prefix'] = !empty($account['rental_agreement_prefix']) ? $account['rental_agreement_prefix'] : $setting['agreement_prefix'];
            $setting['agreement_next_no'] = max(1, (int)$account['rental_agreement_next_no']);
        }
        return $setting;
    }

    protected function _syncRentalAccountNumbersFromSettings($projectId, $setting)
    {
        if (!$this->_rentalAccountNumberColumnsReady() && !$this->_ensureRentalAccountNumberSchema()) {
            return false;
        }
        $db = $this->DealRental->getDataSource();
        $receiptPrefix = substr(trim((string)$setting['receipt_prefix']), 0, 20);
        $agreementPrefix = substr(trim((string)$setting['agreement_prefix']), 0, 20);
        $receiptPrefix = $receiptPrefix !== '' ? $receiptPrefix : 'RR-';
        $agreementPrefix = $agreementPrefix !== '' ? $agreementPrefix : 'RA-';
        $this->DealRental->query("INSERT INTO `ardaccounts_settings`
            (`project_id`,`rental_receipt_prefix`,`rental_receipt_next_no`,`rental_agreement_prefix`,`rental_agreement_next_no`,`created`,`modified`)
            VALUES (".(int)$projectId.",".$db->value($receiptPrefix, 'string').",".max(1, (int)$setting['receipt_next_no']).",".$db->value($agreementPrefix, 'string').",".max(1, (int)$setting['agreement_next_no']).",NOW(),NOW())
            ON DUPLICATE KEY UPDATE
                `rental_receipt_prefix`=VALUES(`rental_receipt_prefix`),
                `rental_receipt_next_no`=VALUES(`rental_receipt_next_no`),
                `rental_agreement_prefix`=VALUES(`rental_agreement_prefix`),
                `rental_agreement_next_no`=VALUES(`rental_agreement_next_no`),
                `modified`=NOW()");
        return true;
    }

    protected function _settingsSaveData($data, $existing, $projectId)
    {
        $save = $this->_defaultRentalSettings($projectId);
        $save['id'] = !empty($existing['id']) ? (int)$existing['id'] : null;
        $save['project_id'] = (int)$projectId;
        foreach (array('agreement_prefix','receipt_prefix','eligible_deal_type','sms_template','email_subject','email_body','whatsapp_template') as $field) {
            if (isset($data[$field])) {
                $save[$field] = is_string($data[$field]) ? trim($data[$field]) : $data[$field];
            }
        }
        $save['agreement_next_no'] = max(1, (int)(isset($data['agreement_next_no']) ? $data['agreement_next_no'] : $save['agreement_next_no']));
        $save['receipt_next_no'] = max(1, (int)(isset($data['receipt_next_no']) ? $data['receipt_next_no'] : $save['receipt_next_no']));
        $save['rent_due_days_before'] = max(0, (int)(isset($data['rent_due_days_before']) ? $data['rent_due_days_before'] : $save['rent_due_days_before']));
        $save['rent_increment_enabled'] = !empty($data['rent_increment_enabled']) ? 1 : 0;
        $incrementType = isset($data['rent_increment_type']) ? trim((string)$data['rent_increment_type']) : 'Percentage';
        $save['rent_increment_type'] = in_array($incrementType, array('Percentage', 'Fixed'), true) ? $incrementType : 'Percentage';
        $save['rent_increment_value'] = max(0, $this->_amount($data, 'rent_increment_value'));
        $save['sms_enabled'] = !empty($data['sms_enabled']) ? 1 : 0;
        $save['email_enabled'] = !empty($data['email_enabled']) ? 1 : 0;
        $save['whatsapp_enabled'] = !empty($data['whatsapp_enabled']) ? 1 : 0;
        $dealType = strtolower(trim((string)$save['eligible_deal_type']));
        $save['eligible_deal_type'] = in_array($dealType, array('active','paid','both'), true) ? $dealType : 'both';
        $save['property_type_ids'] = $this->_idsToCsv(isset($data['property_type_ids']) ? $data['property_type_ids'] : array());
        return $save;
    }

    protected function _propertyTypeOptions()
    {
        $this->loadModel('Type');
        return (array)$this->Type->find('list', array(
            'fields' => array('Type.id', 'Type.name'),
            'order' => array('Type.name' => 'asc'),
            'recursive' => -1
        ));
    }

    protected function _csvToIds($csv)
    {
        $ids = array();
        foreach (explode(',', (string)$csv) as $id) {
            $id = (int)$id;
            if ($id > 0) {
                $ids[$id] = $id;
            }
        }
        return array_values($ids);
    }

    protected function _idsToCsv($ids)
    {
        $clean = array();
        foreach ((array)$ids as $id) {
            $id = (int)$id;
            if ($id > 0) {
                $clean[$id] = $id;
            }
        }
        return implode(',', array_values($clean));
    }

    protected function _fetchDeal($dealId)
    {
        $this->loadModel('Admin.Deal');
        return (array)$this->Deal->find('first', array(
            'conditions' => array('Deal.id' => (int)$dealId),
            'recursive' => 0
        ));
    }

    protected function _backfillRentalSchedules()
    {
        if (!$this->_rentalScheduleTablesReady()) {
            return false;
        }
        $rentals = $this->DealRental->find('all', array('recursive' => -1));
        foreach ((array)$rentals as $rental) {
            if (empty($rental['DealRental']['id'])) {
                continue;
            }
            $this->_ensureRentalScheduleRows($rental);
            $this->_rebuildRentalScheduleAllocations((int)$rental['DealRental']['id']);
        }
        return true;
    }

    protected function _syncRentalIncrementProposals($rental)
    {
        if (empty($rental['DealRental']['id']) || !$this->_rentalIncrementSchemaReady()) {
            return false;
        }
        $r = (array)$rental['DealRental'];
        $rentalId = (int)$r['id'];
        if (empty($r['rent_increment_enabled']) || (float)$r['rent_increment_value'] <= 0) {
            return true;
        }
        $startDate = $this->_dateForSql(isset($r['start_date']) ? $r['start_date'] : '');
        $endDate = $this->_dateForSql(isset($r['end_date']) ? $r['end_date'] : '');
        $firstDate = $this->_dateForSql(isset($r['rent_increment_first_date']) ? $r['rent_increment_first_date'] : '');
        if (!$firstDate && $startDate) {
            $firstDate = date('Y-m-d', strtotime($startDate.' +1 year'));
        }
        if (!$firstDate || !$endDate || strtotime($firstDate) > strtotime($endDate)) {
            return true;
        }
        $type = isset($r['rent_increment_type']) && $r['rent_increment_type'] === 'Fixed' ? 'Fixed' : 'Percentage';
        $value = round((float)$r['rent_increment_value'], 2);
        $existingRows = $this->DealRentalIncrement->find('all', array(
            'conditions' => array('DealRentalIncrement.deal_rental_id' => $rentalId),
            'recursive' => -1
        ));
        $existingByDate = array();
        foreach ((array)$existingRows as $existingRow) {
            $existingByDate[$existingRow['DealRentalIncrement']['effective_date']] = $existingRow['DealRentalIncrement'];
        }
        $wantedDates = array();
        $cursor = new DateTime($firstDate);
        $lastDate = new DateTime($endDate);
        $guard = 0;
        while ($cursor <= $lastDate && $guard < 100) {
            $effectiveDate = $cursor->format('Y-m-d');
            $wantedDates[$effectiveDate] = true;
            $oldRent = $this->_approvedRentAtDate($rentalId, (float)$r['monthly_rent'], $effectiveDate);
            $newRent = $this->_incrementedRent($oldRent, $type, $value);
            $existing = isset($existingByDate[$effectiveDate]) ? $existingByDate[$effectiveDate] : array();
            if (empty($existing['id']) || $existing['status'] === 'Pending') {
                $this->DealRentalIncrement->create();
                $this->DealRentalIncrement->save(array('DealRentalIncrement' => array(
                    'id' => !empty($existing['id']) ? (int)$existing['id'] : null,
                    'deal_rental_id' => $rentalId,
                    'project_id' => !empty($r['project_id']) ? (int)$r['project_id'] : 0,
                    'effective_date' => $effectiveDate,
                    'increment_type' => $type,
                    'increment_value' => $value,
                    'old_rent' => $oldRent,
                    'new_rent' => $newRent,
                    'status' => !empty($existing['status']) ? $existing['status'] : 'Pending'
                )), false);
            }
            $cursor->modify('+1 year');
            $guard++;
        }
        foreach ($existingByDate as $effectiveDate => $existing) {
            if (!isset($wantedDates[$effectiveDate]) && $existing['status'] === 'Pending') {
                $this->DealRentalIncrement->delete((int)$existing['id'], false);
            }
        }
        return true;
    }

    protected function _approvedRentAtDate($rentalId, $baseRent, $effectiveDate)
    {
        $rent = round((float)$baseRent, 2);
        $rows = $this->DealRentalIncrement->find('all', array(
            'fields' => array('DealRentalIncrement.new_rent'),
            'conditions' => array(
                'DealRentalIncrement.deal_rental_id' => (int)$rentalId,
                'DealRentalIncrement.status' => 'Approved',
                'DealRentalIncrement.effective_date <' => $effectiveDate
            ),
            'order' => array('DealRentalIncrement.effective_date' => 'asc', 'DealRentalIncrement.id' => 'asc'),
            'recursive' => -1
        ));
        foreach ((array)$rows as $row) {
            $rent = round((float)$row['DealRentalIncrement']['new_rent'], 2);
        }
        return $rent;
    }

    protected function _incrementedRent($oldRent, $type, $value)
    {
        $oldRent = round(max(0, (float)$oldRent), 2);
        $value = max(0, (float)$value);
        $newRent = $type === 'Fixed' ? $oldRent + $value : $oldRent + ($oldRent * $value / 100);
        return round($newRent, 2);
    }

    protected function _approvedRentRates($rentalId)
    {
        $rates = array();
        $rows = $this->DealRentalIncrement->find('all', array(
            'fields' => array('DealRentalIncrement.effective_date', 'DealRentalIncrement.new_rent'),
            'conditions' => array('DealRentalIncrement.deal_rental_id' => (int)$rentalId, 'DealRentalIncrement.status' => 'Approved'),
            'order' => array('DealRentalIncrement.effective_date' => 'asc', 'DealRentalIncrement.id' => 'asc'),
            'recursive' => -1
        ));
        foreach ((array)$rows as $row) {
            $increment = $row['DealRentalIncrement'];
            $rates[substr($increment['effective_date'], 0, 7)] = round((float)$increment['new_rent'], 2);
        }
        return $rates;
    }

    protected function _ensureRentalScheduleRows($rental)
    {
        if (empty($rental['DealRental']['id']) || !$this->_rentalScheduleTablesReady()) {
            return false;
        }
        $r = (array)$rental['DealRental'];
        $rentalId = (int)$r['id'];
        $startDate = !empty($r['start_date']) ? $this->_dateForSql($r['start_date']) : null;
        $endDate = !empty($r['end_date']) ? $this->_dateForSql($r['end_date']) : null;
        $monthlyRent = isset($r['monthly_rent']) ? round((float)$r['monthly_rent'], 2) : 0;
        $startYear = $startDate ? (int)substr($startDate, 0, 4) : 0;
        $endYear = $endDate ? (int)substr($endDate, 0, 4) : 0;
        if (!$startDate || !$endDate || $startYear < 2000 || $startYear > 2100 || $endYear < 2000 || $endYear > 2100 || strtotime($endDate) < strtotime($startDate) || $monthlyRent <= 0) {
            return false;
        }
        $existingRows = $this->DealRentalSchedule->find('all', array(
            'conditions' => array('DealRentalSchedule.deal_rental_id' => $rentalId),
            'recursive' => -1
        ));
        $existingByMonth = array();
        foreach ((array)$existingRows as $existingRow) {
            $existingByMonth[$existingRow['DealRentalSchedule']['rent_month']] = $existingRow['DealRentalSchedule'];
        }
        $wantedMonths = array();
        $cursor = new DateTime(date('Y-m-01', strtotime($startDate)));
        $lastMonth = new DateTime(date('Y-m-01', strtotime($endDate)));
        $dueDay = max(1, min(31, isset($r['due_day']) ? (int)$r['due_day'] : 1));
        $approvedRates = $this->_approvedRentRates($rentalId);
        $scheduledRent = $monthlyRent;
        $guard = 0;
        while ($cursor <= $lastMonth && $guard < 600) {
            $rentMonth = $cursor->format('Y-m');
            foreach ($approvedRates as $effectiveMonth => $approvedRent) {
                if ($effectiveMonth <= $rentMonth) {
                    $scheduledRent = $approvedRent;
                }
            }
            $wantedMonths[$rentMonth] = true;
            $daysInMonth = (int)$cursor->format('t');
            $actualDueDay = min($dueDay, $daysInMonth);
            $dueDate = $rentMonth.'-'.str_pad((string)$actualDueDay, 2, '0', STR_PAD_LEFT);
            if ($rentMonth === date('Y-m', strtotime($startDate)) && strtotime($dueDate) < strtotime($startDate)) {
                $dueDate = $startDate;
            }
            if ($rentMonth === date('Y-m', strtotime($endDate)) && strtotime($dueDate) > strtotime($endDate)) {
                $dueDate = $endDate;
            }
            $existing = isset($existingByMonth[$rentMonth]) ? $existingByMonth[$rentMonth] : array();
            $hasPaidHistory = !empty($existing['paid_amount']) && (float)$existing['paid_amount'] > 0.009;
            $rowRentAmount = $hasPaidHistory && isset($existing['rent_amount']) ? round((float)$existing['rent_amount'], 2) : $scheduledRent;
            $rowPaidAmount = !empty($existing['paid_amount']) ? round((float)$existing['paid_amount'], 2) : 0;
            $save = array('DealRentalSchedule' => array(
                'id' => !empty($existing['id']) ? (int)$existing['id'] : null,
                'deal_rental_id' => $rentalId,
                'deal_id' => isset($r['deal_id']) ? (int)$r['deal_id'] : 0,
                'client_id' => isset($r['client_id']) ? (int)$r['client_id'] : 0,
                'project_id' => isset($r['project_id']) ? (int)$r['project_id'] : 0,
                'rent_month' => $rentMonth,
                'due_date' => $dueDate,
                'rent_amount' => $rowRentAmount,
                'paid_amount' => $rowPaidAmount,
                'balance_amount' => round(max(0, $rowRentAmount-$rowPaidAmount), 2),
                'status' => !empty($existing['status']) ? $existing['status'] : 'Upcoming'
            ));
            if (empty($existing['id'])) {
                $this->DealRentalSchedule->create();
            }
            $this->DealRentalSchedule->save($save, false);
            $cursor->modify('+1 month');
            $guard++;
        }
        foreach ($existingByMonth as $rentMonth => $existing) {
            if (!isset($wantedMonths[$rentMonth]) && (float)$existing['paid_amount'] <= 0.009) {
                $this->DealRentalSchedule->delete((int)$existing['id'], false);
            }
        }
        return true;
    }

    protected function _rebuildRentalScheduleAllocations($rentalId)
    {
        $rentalId = (int)$rentalId;
        if ($rentalId <= 0 || !$this->_rentalScheduleTablesReady()) {
            return false;
        }
        $schedules = $this->DealRentalSchedule->find('all', array(
            'conditions' => array('DealRentalSchedule.deal_rental_id' => $rentalId),
            'order' => array('DealRentalSchedule.due_date' => 'asc', 'DealRentalSchedule.id' => 'asc'),
            'recursive' => -1
        ));
        if (empty($schedules)) {
            return false;
        }
        $receipts = $this->DealRentalReceipt->find('all', array(
            'conditions' => array('DealRentalReceipt.deal_rental_id' => $rentalId),
            'order' => array('DealRentalReceipt.receipt_date' => 'asc', 'DealRentalReceipt.id' => 'asc'),
            'recursive' => -1
        ));
        $dataSource = $this->DealRentalSchedule->getDataSource();
        $dataSource->begin();
        try {
            $this->DealRentalReceiptAllocation->deleteAll(array('DealRentalReceiptAllocation.deal_rental_id' => $rentalId), false);
            $scheduleState = array();
            foreach ($schedules as $index => $scheduleRow) {
                $schedule = $scheduleRow['DealRentalSchedule'];
                $schedule['paid_amount'] = 0.0;
                $scheduleState[$index] = $schedule;
            }
            foreach ((array)$receipts as $receiptRow) {
                $receipt = $receiptRow['DealRentalReceipt'];
                $remaining = round(max(0, (float)$receipt['rent_amount']), 2);
                if ($remaining <= 0.009) {
                    continue;
                }
                $anchorMonth = !empty($receipt['rent_month']) ? substr((string)$receipt['rent_month'], 0, 7) : '';
                $startIndex = 0;
                if ($anchorMonth !== '') {
                    foreach ($scheduleState as $index => $schedule) {
                        if ($schedule['rent_month'] === $anchorMonth) {
                            $startIndex = $index;
                            break;
                        }
                    }
                }
                for ($index = $startIndex; $index < count($scheduleState) && $remaining > 0.009; $index++) {
                    $rentAmount = round((float)$scheduleState[$index]['rent_amount'], 2);
                    $alreadyPaid = round((float)$scheduleState[$index]['paid_amount'], 2);
                    $scheduleBalance = round(max(0, $rentAmount-$alreadyPaid), 2);
                    if ($scheduleBalance <= 0.009) {
                        continue;
                    }
                    $allocated = round(min($remaining, $scheduleBalance), 2);
                    $this->DealRentalReceiptAllocation->create();
                    if (!$this->DealRentalReceiptAllocation->save(array('DealRentalReceiptAllocation' => array(
                        'receipt_id' => (int)$receipt['id'],
                        'schedule_id' => (int)$scheduleState[$index]['id'],
                        'deal_rental_id' => $rentalId,
                        'allocated_amount' => $allocated
                    )), false)) {
                        throw new Exception(__('Unable to allocate rental receipt.'));
                    }
                    $scheduleState[$index]['paid_amount'] = round($alreadyPaid+$allocated, 2);
                    $remaining = round($remaining-$allocated, 2);
                }
            }
            $today = date('Y-m-d');
            foreach ($scheduleState as $schedule) {
                $rentAmount = round((float)$schedule['rent_amount'], 2);
                $paidAmount = round(min($rentAmount, (float)$schedule['paid_amount']), 2);
                $balanceAmount = round(max(0, $rentAmount-$paidAmount), 2);
                if ($balanceAmount <= 0.009) {
                    $status = 'Paid';
                } elseif ($paidAmount > 0.009) {
                    $status = 'Partially Paid';
                } elseif (!empty($schedule['due_date']) && $schedule['due_date'] < $today) {
                    $status = 'Overdue';
                } elseif (!empty($schedule['due_date']) && $schedule['due_date'] === $today) {
                    $status = 'Due';
                } else {
                    $status = 'Upcoming';
                }
                if (!$this->DealRentalSchedule->save(array('DealRentalSchedule' => array(
                    'id' => (int)$schedule['id'],
                    'paid_amount' => $paidAmount,
                    'balance_amount' => $balanceAmount,
                    'status' => $status
                )), false)) {
                    throw new Exception(__('Unable to update rental schedule.'));
                }
            }
            $dataSource->commit();
            return true;
        } catch (Exception $e) {
            $dataSource->rollback();
            CakeLog::write('error', 'Rental schedule allocation failed for rental '.$rentalId.': '.$e->getMessage());
            return false;
        }
    }

    protected function _selectedRentalSchedule($rentalId)
    {
        $rentalId = (int)$rentalId;
        $conditions = array('DealRentalSchedule.deal_rental_id' => $rentalId);
        if (!empty($this->request->query['schedule_id'])) {
            $conditions['DealRentalSchedule.id'] = (int)$this->request->query['schedule_id'];
        } else {
            $conditions['DealRentalSchedule.balance_amount >'] = 0.009;
        }
        if (!$this->_isSuperAdminSession((array)$this->adminValue)) {
            $conditions['DealRentalSchedule.project_id'] = (int)$this->agencyId;
        }
        return (array)$this->DealRentalSchedule->find('first', array(
            'conditions' => $conditions,
            'order' => array('DealRentalSchedule.due_date' => 'asc', 'DealRentalSchedule.id' => 'asc'),
            'recursive' => -1
        ));
    }

    protected function _rentalScheduleSummary($rentalId, $schedules = array())
    {
        $summary = array('scheduled' => 0, 'paid' => 0, 'outstanding' => 0, 'overdue' => 0, 'credit' => 0, 'paid_count' => 0, 'overdue_count' => 0, 'upcoming_count' => 0);
        foreach ((array)$schedules as $row) {
            $schedule = $row['DealRentalSchedule'];
            $summary['scheduled'] += (float)$schedule['rent_amount'];
            $summary['paid'] += (float)$schedule['paid_amount'];
            $summary['outstanding'] += (float)$schedule['balance_amount'];
            if ($schedule['status'] === 'Paid') {
                $summary['paid_count']++;
            } elseif ($schedule['status'] === 'Overdue' || $schedule['status'] === 'Partially Paid') {
                if (!empty($schedule['due_date']) && $schedule['due_date'] < date('Y-m-d')) {
                    $summary['overdue'] += (float)$schedule['balance_amount'];
                    $summary['overdue_count']++;
                }
            } else {
                $summary['upcoming_count']++;
            }
        }
        $receiptTotal = $this->DealRentalReceipt->find('first', array(
            'fields' => array('SUM(DealRentalReceipt.rent_amount) AS total_received'),
            'conditions' => array('DealRentalReceipt.deal_rental_id' => (int)$rentalId),
            'recursive' => -1
        ));
        $received = !empty($receiptTotal[0]['total_received']) ? (float)$receiptTotal[0]['total_received'] : 0;
        $summary['credit'] = round(max(0, $received-$summary['paid']), 2);
        foreach ($summary as $key => $value) {
            if (strpos($key, '_count') === false) {
                $summary[$key] = round((float)$value, 2);
            }
        }
        return $summary;
    }

    protected function _rentalScheduleTotalsMap($rentalIds)
    {
        $cleanIds = array_values(array_unique(array_filter(array_map('intval', (array)$rentalIds))));
        $totals = array();
        foreach ($cleanIds as $rentalId) {
            $totals[$rentalId] = array('paid' => 0, 'due' => 0);
        }
        if (empty($cleanIds)) {
            return $totals;
        }

        $rows = $this->DealRentalSchedule->find('all', array(
            'fields' => array(
                'DealRentalSchedule.deal_rental_id',
                'SUM(DealRentalSchedule.paid_amount) AS paid_total',
                'SUM(DealRentalSchedule.balance_amount) AS due_total'
            ),
            'conditions' => array('DealRentalSchedule.deal_rental_id' => $cleanIds),
            'group' => array('DealRentalSchedule.deal_rental_id'),
            'recursive' => -1
        ));
        foreach ((array)$rows as $row) {
            $rentalId = !empty($row['DealRentalSchedule']['deal_rental_id']) ? (int)$row['DealRentalSchedule']['deal_rental_id'] : 0;
            if ($rentalId <= 0) {
                continue;
            }
            $totals[$rentalId] = array(
                'paid' => round(!empty($row[0]['paid_total']) ? (float)$row[0]['paid_total'] : 0, 2),
                'due' => round(!empty($row[0]['due_total']) ? (float)$row[0]['due_total'] : 0, 2)
            );
        }
        return $totals;
    }

    protected function _rentalSaveData($data, $existing = array())
    {
        $row = isset($data['DealRental']) ? (array)$data['DealRental'] : array();
        $deal = $this->_fetchDeal((int)$row['deal_id']);
        if (empty($deal['Deal']['id'])) {
            return array('DealRental' => $row);
        }
        $projectId = !empty($deal['Property']['project_id']) ? (int)$deal['Property']['project_id'] : (int)$this->agencyId;
        $row['deal_id'] = (int)$deal['Deal']['id'];
        $row['client_id'] = !empty($deal['Deal']['client_id']) ? (int)$deal['Deal']['client_id'] : 0;
        $row['project_id'] = $projectId;
        $row['monthly_rent'] = $this->_amount($row, 'monthly_rent');
        $row['security_deposit'] = $this->_amount($row, 'security_deposit');
        $row['rent_increment_enabled'] = !empty($row['rent_increment_enabled']) ? 1 : 0;
        $incrementType = isset($row['rent_increment_type']) ? trim((string)$row['rent_increment_type']) : 'Percentage';
        $row['rent_increment_type'] = in_array($incrementType, array('Percentage', 'Fixed'), true) ? $incrementType : 'Percentage';
        $row['rent_increment_value'] = max(0, $this->_amount($row, 'rent_increment_value'));
        $row['due_day'] = max(1, min(31, (int)(isset($row['due_day']) ? $row['due_day'] : 1)));
        $row['start_date'] = $this->_safeRentalDate(isset($row['start_date']) ? $row['start_date'] : '', !empty($existing['DealRental']['start_date']) ? $existing['DealRental']['start_date'] : null);
        $row['end_date'] = $this->_safeRentalDate(isset($row['end_date']) ? $row['end_date'] : '', !empty($existing['DealRental']['end_date']) ? $existing['DealRental']['end_date'] : null);
        $defaultIncrementDate = !empty($row['start_date']) ? date('Y-m-d', strtotime($row['start_date'].' +1 year')) : null;
        $row['rent_increment_first_date'] = $this->_safeRentalDate(isset($row['rent_increment_first_date']) ? $row['rent_increment_first_date'] : '', !empty($existing['DealRental']['rent_increment_first_date']) ? $existing['DealRental']['rent_increment_first_date'] : $defaultIncrementDate);
        $submittedPortalAccess = isset($row['client_portal_access']) ? trim((string)$row['client_portal_access']) : '';
        $row['client_portal_access'] = strcasecmp($submittedPortalAccess, 'Blocked') === 0 ? 'Blocked' : 'Active';
        if (empty($row['created_by'])) {
            $row['created_by'] = isset($this->adminValue['User']['id']) ? (int)$this->adminValue['User']['id'] : 0;
        }
        if (!empty($existing['DealRental']['agreement_no']) && empty($row['agreement_no'])) {
            $row['agreement_no'] = $existing['DealRental']['agreement_no'];
        }
        return array('DealRental' => $row);
    }

    protected function _safeRentalDate($value, $fallback = null)
    {
        $date = $this->_dateForSql($value);
        $year = $date ? (int)substr($date, 0, 4) : 0;
        if($year >= 2000 && $year <= 2100)
        {
            return $date;
        }
        $fallbackDate = $this->_dateForSql($fallback);
        $fallbackYear = $fallbackDate ? (int)substr($fallbackDate, 0, 4) : 0;
        return ($fallbackYear >= 2000 && $fallbackYear <= 2100) ? $fallbackDate : null;
    }

    protected function _receiptSaveData($data, $rental, $existing = array())
    {
        $r = (array)$rental['DealRental'];
        $row = isset($data['DealRentalReceipt']) ? (array)$data['DealRentalReceipt'] : array();
        $row['deal_rental_id'] = (int)$r['id'];
        $row['deal_id'] = (int)$r['deal_id'];
        $row['client_id'] = (int)$r['client_id'];
        $row['project_id'] = (int)$r['project_id'];
        $row['rent_amount'] = $this->_amount($row, 'rent_amount');
        $row['expense_amount'] = $this->_amount($row, 'expense_amount');
        $row['net_amount'] = $row['rent_amount'] - $row['expense_amount'];
        $row['source_ledger_id'] = isset($row['source_ledger_id']) ? (int)$row['source_ledger_id'] : 0;
        $sourceLedger = $this->_rentalPayoutSourceLedger((int)$r['project_id'], $row['source_ledger_id']);
        $row['payment_mode'] = !empty($sourceLedger['Ardledger']['name']) ? substr(trim((string)$sourceLedger['Ardledger']['name']), 0, 40) : '';
        $row['receipt_date'] = $this->_dateForSql(isset($row['receipt_date']) ? $row['receipt_date'] : date('Y-m-d'));
        if (empty($row['rent_month'])) {
            $row['rent_month'] = date('Y-m', strtotime($row['receipt_date']));
        }
        if (empty($row['receipt_no'])) {
            $row['receipt_no'] = !empty($existing['DealRentalReceipt']['receipt_no']) ? $existing['DealRentalReceipt']['receipt_no'] : $this->_nextReceiptNo((int)$r['project_id']);
        }
        $row['created_by'] = isset($this->adminValue['User']['id']) ? (int)$this->adminValue['User']['id'] : 0;
        return array('DealRentalReceipt' => $row);
    }

    protected function _rentalRow($id)
    {
        $conditions = array('DealRental.id' => (int)$id);
        if (!$this->_isSuperAdminSession((array)$this->adminValue)) {
            $conditions['DealRental.project_id'] = (int)$this->agencyId;
        }
        $row = (array)$this->DealRental->find('first', array('conditions' => $conditions, 'recursive' => 0));
        if (!empty($row['DealRental']['id'])) {
            $accessMap = $this->_rentalClientAccessMap(array((int)$row['DealRental']['id']));
            $row['DealRental']['client_portal_access'] = isset($accessMap[(int)$row['DealRental']['id']]) ? $accessMap[(int)$row['DealRental']['id']] : 'Unknown';
        }
        return $row;
    }

    protected function _rentalClientAccessMap($rentalIds)
    {
        $cleanIds = array();
        foreach ((array)$rentalIds as $rentalId) {
            $rentalId = (int)$rentalId;
            if ($rentalId > 0) {
                $cleanIds[$rentalId] = $rentalId;
            }
        }
        if (empty($cleanIds)) {
            return array();
        }
        $map = array();
        try {
            $rows = (array)$this->DealRental->query(
                "SELECT `id`, `client_portal_access` FROM `deal_rentals` WHERE `id` IN (".implode(',', $cleanIds).")"
            );
            foreach ($rows as $rawRow) {
                foreach ((array)$rawRow as $rowPart) {
                    if (!is_array($rowPart) || !isset($rowPart['id'])) {
                        continue;
                    }
                    $rentalId = (int)$rowPart['id'];
                    $storedAccess = isset($rowPart['client_portal_access']) ? trim((string)$rowPart['client_portal_access']) : '';
                    $map[$rentalId] = strcasecmp($storedAccess, 'Blocked') === 0 ? 'Blocked' : (strcasecmp($storedAccess, 'Active') === 0 ? 'Active' : 'Unknown');
                }
            }
        } catch (Exception $e) {
        }
        return $map;
    }

    protected function _rentalPaidRentMap($rentalIds)
    {
        $cleanIds = array();
        foreach ((array)$rentalIds as $rentalId) {
            $rentalId = (int)$rentalId;
            if ($rentalId > 0) {
                $cleanIds[$rentalId] = $rentalId;
            }
        }
        if (empty($cleanIds)) {
            return array();
        }
        $map = array_fill_keys(array_values($cleanIds), false);
        try {
            $rows = (array)$this->DealRental->query(
                "SELECT dr.`id`, CASE WHEN EXISTS (SELECT 1 FROM `deal_rental_receipts` rr WHERE rr.`deal_rental_id`=dr.`id` AND rr.`rent_amount`>0) OR EXISTS (SELECT 1 FROM `deal_rental_schedules` rs WHERE rs.`deal_rental_id`=dr.`id` AND rs.`paid_amount`>0) THEN 1 ELSE 0 END AS `has_paid_rent` FROM `deal_rentals` dr WHERE dr.`id` IN (".implode(',', $cleanIds).")"
            );
            foreach ($rows as $rawRow) {
                $flat = array();
                foreach ((array)$rawRow as $rowPart) {
                    if (is_array($rowPart)) {
                        $flat = array_merge($flat, $rowPart);
                    }
                }
                if (!empty($flat['id'])) {
                    $map[(int)$flat['id']] = !empty($flat['has_paid_rent']);
                }
            }
        } catch (Exception $e) {
        }
        return $map;
    }

    protected function _rentalHasPaidRent($rentalId)
    {
        $rentalId = (int)$rentalId;
        $map = $this->_rentalPaidRentMap(array($rentalId));
        return !empty($map[$rentalId]);
    }

    protected function _receiptRow($id)
    {
        $conditions = array('DealRentalReceipt.id' => (int)$id);
        if (!$this->_isSuperAdminSession((array)$this->adminValue)) {
            $conditions['DealRentalReceipt.project_id'] = (int)$this->agencyId;
        }
        return (array)$this->DealRentalReceipt->find('first', array('conditions' => $conditions, 'recursive' => -1));
    }

    protected function _amount($row, $field)
    {
        return isset($row[$field]) ? (float)str_replace(',', '', $row[$field]) : 0;
    }

    protected function _dateForSql($value)
    {
        $value = trim((string)$value);
        if ($value === '') {
            return null;
        }
        $time = strtotime($value);
        return $time ? date('Y-m-d', $time) : $value;
    }

    protected function _nextAgreementNo($projectId)
    {
        $accountNumber = $this->_nextRentalAccountNumber((int)$projectId, 'rental_agreement_prefix', 'rental_agreement_next_no', 'RA-', 'agreement');
        if ($accountNumber !== '') {
            return $accountNumber;
        }
        $settings = $this->_rentalSettings((int)$projectId);
        $prefix = trim((string)$settings['agreement_prefix']);
        if ($prefix === '') {
            $prefix = 'RA-';
        }
        $nextNo = max(1, (int)$settings['agreement_next_no']);
        $number = $prefix.str_pad((string)$nextNo, 5, '0', STR_PAD_LEFT);
        $this->DealRentalSetting->updateAll(
            array('DealRentalSetting.agreement_next_no' => $nextNo + 1),
            array('DealRentalSetting.id' => (int)$settings['id'])
        );
        return $number;
    }

    protected function _nextReceiptNo($projectId)
    {
        $accountNumber = $this->_nextRentalAccountNumber((int)$projectId, 'rental_receipt_prefix', 'rental_receipt_next_no', 'RR-', 'receipt');
        if ($accountNumber !== '') {
            return $accountNumber;
        }
        $settings = $this->_rentalSettings((int)$projectId);
        $prefix = trim((string)$settings['receipt_prefix']);
        if ($prefix === '') {
            $prefix = 'RR-';
        }
        $nextNo = max(1, (int)$settings['receipt_next_no']);
        $number = $prefix.str_pad((string)$nextNo, 5, '0', STR_PAD_LEFT);
        $this->DealRentalSetting->updateAll(
            array('DealRentalSetting.receipt_next_no' => $nextNo + 1),
            array('DealRentalSetting.id' => (int)$settings['id'])
        );
        return $number;
    }

    protected function _nextRentalAccountNumber($projectId, $prefixField, $nextField, $defaultPrefix, $legacyType)
    {
        $projectId = (int)$projectId;
        $allowedFields = array(
            'rental_agreement_prefix' => 'rental_agreement_next_no',
            'rental_receipt_prefix' => 'rental_receipt_next_no'
        );
        if ($projectId <= 0 || !isset($allowedFields[$prefixField]) || $allowedFields[$prefixField] !== $nextField) {
            return '';
        }
        if (!$this->_rentalAccountNumberColumnsReady() && !$this->_ensureRentalAccountNumberSchema()) {
            return '';
        }

        $legacy = $this->_rentalSettings($projectId);
        $legacyPrefixField = $legacyType === 'agreement' ? 'agreement_prefix' : 'receipt_prefix';
        $legacyNextField = $legacyType === 'agreement' ? 'agreement_next_no' : 'receipt_next_no';
        $legacyPrefix = !empty($legacy[$legacyPrefixField]) ? substr(trim((string)$legacy[$legacyPrefixField]), 0, 20) : $defaultPrefix;
        $legacyNext = max(1, (int)$legacy[$legacyNextField]);
        $db = $this->DealRental->getDataSource();

        $db->begin();
        try {
            $this->DealRental->query("INSERT IGNORE INTO `ardaccounts_settings`
                (`project_id`,`rental_receipt_prefix`,`rental_receipt_next_no`,`rental_agreement_prefix`,`rental_agreement_next_no`,`created`,`modified`)
                VALUES (".$projectId.",'RR-',1,'RA-',1,NOW(),NOW())");
            $rows = $this->DealRental->query("SELECT `id`,`".$prefixField."`,`".$nextField."`
                FROM `ardaccounts_settings` WHERE `project_id`=".$projectId." LIMIT 1 FOR UPDATE");
            $row = array();
            if (!empty($rows[0])) {
                foreach ($rows[0] as $rowBlock) {
                    if (is_array($rowBlock) && !empty($rowBlock)) {
                        $row = $rowBlock;
                        break;
                    }
                }
            }
            if (empty($row['id'])) {
                throw new Exception(__('Unable to load rental numbering settings.'));
            }
            $prefix = isset($row[$prefixField]) ? substr(trim((string)$row[$prefixField]), 0, 20) : '';
            $nextNo = isset($row[$nextField]) ? max(1, (int)$row[$nextField]) : 1;
            if ($nextNo <= 1 && $legacyNext > 1 && ($prefix === '' || $prefix === $legacyPrefix || $prefix === $defaultPrefix)) {
                $nextNo = $legacyNext;
            }
            if ($prefix === '' || ($nextNo === $legacyNext && $legacyNext > 1 && $prefix === $defaultPrefix)) {
                $prefix = $legacyPrefix;
            }
            if ($prefix === '') {
                $prefix = $defaultPrefix;
            }
            $prefixQ = $db->value($prefix, 'string');
            $this->DealRental->query("UPDATE `ardaccounts_settings` SET `".$prefixField."`=".$prefixQ.", `".$nextField."`=".($nextNo + 1).", `modified`=NOW() WHERE `id`=".(int)$row['id']." LIMIT 1");
            $db->commit();

            $legacyPrefixQ = $db->value($prefix, 'string');
            $this->DealRental->query("UPDATE `deal_rental_settings` SET `".$legacyPrefixField."`=".$legacyPrefixQ.", `".$legacyNextField."`=".($nextNo + 1).", `modified`=NOW() WHERE `project_id`=".$projectId." LIMIT 1");
            return $prefix.str_pad((string)$nextNo, 5, '0', STR_PAD_LEFT);
        } catch (Exception $e) {
            $db->rollback();
            CakeLog::write('error', 'Rental number reservation failed for project '.$projectId.': '.$e->getMessage());
            return '';
        }
    }

    protected function _rentalPayoutLedgerOptions($projectId)
    {
        $this->loadModel('Admin.Ardledger');
        return (array)$this->Ardledger->find('list', array(
            'fields' => array('Ardledger.id', 'Ardledger.name'),
            'conditions' => array(
                'Ardledger.project_id' => (int)$projectId,
                'Ardledger.type' => 'bank',
                'Ardledger.is_trashed' => 0
            ),
            'order' => array('Ardledger.name' => 'asc'),
            'recursive' => -1
        ));
    }

    protected function _rentalPayoutSourceLedger($projectId, $ledgerId)
    {
        $this->loadModel('Admin.Ardledger');
        return (array)$this->Ardledger->find('first', array(
            'fields' => array('Ardledger.id', 'Ardledger.name', 'Ardledger.project_id', 'Ardledger.type'),
            'conditions' => array(
                'Ardledger.id' => (int)$ledgerId,
                'Ardledger.project_id' => (int)$projectId,
                'Ardledger.type' => 'bank',
                'Ardledger.is_trashed' => 0
            ),
            'recursive' => -1
        ));
    }

    protected function _validRentalPayoutSourceLedger($projectId, $ledgerId)
    {
        $row = $this->_rentalPayoutSourceLedger((int)$projectId, (int)$ledgerId);
        return !empty($row['Ardledger']['id']);
    }

    protected function _syncRentalPayoutVoucher($receiptId)
    {
        $receiptId = (int)$receiptId;
        $scope = '';
        if (!$this->_isSuperAdminSession((array)$this->adminValue)) {
            $scope = ' AND `project_id`='.(int)$this->agencyId;
        }
        $rows = $this->DealRentalReceipt->query("SELECT * FROM `deal_rental_receipts` WHERE `id`=".$receiptId.$scope." LIMIT 1");
        $row = !empty($rows[0]['deal_rental_receipts']) ? (array)$rows[0]['deal_rental_receipts'] : (isset($rows[0][0]) ? (array)$rows[0][0] : array());
        if (empty($row['id'])) {
            return false;
        }
        $projectId = (int)$row['project_id'];
        $sourceLedgerId = (int)$row['source_ledger_id'];
        if ((float)$row['rent_amount'] <= 0 || !$this->_validRentalPayoutSourceLedger($projectId, $sourceLedgerId)) {
            return false;
        }
        $expenseLedgerId = $this->_ardEnsureRentalPaymentLedger($projectId);
        if ($expenseLedgerId <= 0) {
            return false;
        }
        $description = 'Rental payment to client for '.$row['rent_month'].' against receipt '.$row['receipt_no'];
        $transactionId = $this->_ardPostAutoVoucher(
            $projectId,
            $sourceLedgerId,
            'expense',
            (float)$row['rent_amount'],
            (string)$row['receipt_no'],
            (string)$row['receipt_date'],
            $description,
            array(
                'expense_ledger_id' => $expenseLedgerId,
                'source_type' => 'rental_payment',
                'source_id' => (int)$row['id'],
                'existing_id' => !empty($row['voucher_transaction_id']) ? (int)$row['voucher_transaction_id'] : 0,
                'status' => 'Posted',
                'allow_source_update' => true,
                'skip_schema_bootstrap' => true
            )
        );
        if ($transactionId <= 0) {
            return false;
        }
        return (bool)$this->DealRentalReceipt->updateAll(
            array('DealRentalReceipt.voucher_transaction_id' => (int)$transactionId),
            array('DealRentalReceipt.id' => (int)$row['id'])
        );
    }

    protected function _syncRentalCollectionVoucher($collectionId)
    {
        $collectionId = (int)$collectionId;
        $scope = '';
        if (!$this->_isSuperAdminSession((array)$this->adminValue)) {
            $scope = ' AND `project_id`='.(int)$this->agencyId;
        }
        $rows = $this->DealRentalTenantCollection->query("SELECT * FROM `deal_rental_tenant_collections` WHERE `id`=".$collectionId.$scope." LIMIT 1");
        $row = !empty($rows[0]['deal_rental_tenant_collections']) ? (array)$rows[0]['deal_rental_tenant_collections'] : (isset($rows[0][0]) ? (array)$rows[0][0] : array());
        if (empty($row['id']) || (float)$row['total_amount'] <= 0 || !$this->_validRentalPayoutSourceLedger((int)$row['project_id'], (int)$row['source_ledger_id'])) {
            return false;
        }
        $incomeLedgerId = $this->_ardEnsureRentalIncomeLedger((int)$row['project_id']);
        if ($incomeLedgerId <= 0) {
            return false;
        }
        $transactionId = $this->_ardPostAutoVoucher(
            (int)$row['project_id'],
            (int)$row['source_ledger_id'],
            'income',
            (float)$row['total_amount'],
            (string)$row['collection_no'],
            (string)$row['collection_date'],
            'Tenant rental collection for '.$row['rent_month'].' against '.$row['collection_no'],
            array(
                'income_ledger_id' => $incomeLedgerId,
                'source_type' => 'rental_tenant_collection',
                'source_id' => (int)$row['id'],
                'existing_id' => !empty($row['voucher_transaction_id']) ? (int)$row['voucher_transaction_id'] : 0,
                'status' => 'Posted',
                'allow_source_update' => true,
                'skip_schema_bootstrap' => true
            )
        );
        if ($transactionId <= 0) {
            return false;
        }
        return (bool)$this->DealRentalTenantCollection->updateAll(
            array('DealRentalTenantCollection.voucher_transaction_id' => (int)$transactionId),
            array('DealRentalTenantCollection.id' => (int)$row['id'])
        );
    }

    protected function _syncArdJournalFromTransaction($transactionId, $sourceType = 'rental_payment', $sourceId = 0)
    {
        $this->loadModel('Admin.Ardtransaction');
        $this->loadModel('Admin.Ardledger');
        $this->loadModel('Admin.Ardjournalentry');
        $transaction = $this->Ardtransaction->find('first', array(
            'conditions' => array('Ardtransaction.id' => (int)$transactionId),
            'recursive' => -1
        ));
        if (empty($transaction['Ardtransaction']['id'])) {
            return false;
        }
        $tx = (array)$transaction['Ardtransaction'];
        $sourceName = (string)$this->Ardledger->field('name', array('Ardledger.id' => (int)$tx['ledger_id']));
        $expenseName = (string)$this->Ardledger->field('name', array('Ardledger.id' => (int)$tx['expense_ledger_id']));
        $incomeName = !empty($tx['income_ledger_id']) ? (string)$this->Ardledger->field('name', array('Ardledger.id' => (int)$tx['income_ledger_id'])) : '';
        $isIncome = strtolower(trim((string)$tx['type'])) === 'income';
        $existing = $this->Ardjournalentry->find('first', array(
            'fields' => array('Ardjournalentry.id'),
            'conditions' => array('Ardjournalentry.transaction_id' => (int)$transactionId),
            'recursive' => -1
        ));
        $data = array('Ardjournalentry' => array(
            'project_id' => (int)$tx['project_id'],
            'transaction_id' => (int)$transactionId,
            'source_type' => substr(trim((string)$sourceType), 0, 40),
            'source_id' => (int)$sourceId,
            'ref_no' => trim((string)$tx['ref_no']),
            'entry_date' => (string)$tx['entry_date'],
            'debit_account' => substr($isIncome ? ($sourceName !== '' ? $sourceName : 'Cash/Bank') : ($expenseName !== '' ? $expenseName : 'Rental-Payments'), 0, 160),
            'credit_account' => substr($isIncome ? ($incomeName !== '' ? $incomeName : 'Rental-Income') : ($sourceName !== '' ? $sourceName : 'Cash/Bank'), 0, 160),
            'amount' => round((float)$tx['amount'], 2),
            'description' => trim((string)$tx['description'])
        ));
        if (!empty($existing['Ardjournalentry']['id'])) {
            $data['Ardjournalentry']['id'] = (int)$existing['Ardjournalentry']['id'];
        }
        $this->Ardjournalentry->create();
        return (bool)$this->Ardjournalentry->save($data, false);
    }

    protected function _rentalExpenseCategoryId()
    {
        $this->loadModel('ExpenseCategory');
        $row = $this->ExpenseCategory->find('first', array(
            'fields' => array('ExpenseCategory.id'),
            'conditions' => array('LOWER(ExpenseCategory.name)' => 'rental expense'),
            'recursive' => -1
        ));
        if (!empty($row['ExpenseCategory']['id'])) {
            return (int)$row['ExpenseCategory']['id'];
        }
        $this->ExpenseCategory->create();
        $this->ExpenseCategory->save(array('ExpenseCategory' => array('name' => 'Rental Expense', 'status' => 'Active')), false);
        return (int)$this->ExpenseCategory->id;
    }

    protected function _syncReceiptExpense($receiptId)
    {
        $receipt = $this->_receiptRow((int)$receiptId);
        if (empty($receipt['DealRentalReceipt']['id'])) {
            return false;
        }
        $row = $receipt['DealRentalReceipt'];
        if ((float)$row['expense_amount'] <= 0) {
            $this->_deleteLinkedExpense($row);
            return true;
        }
        $this->loadModel('Expense');
        $this->loadModel('ExpensesPayment');
        $expenseId = (int)$row['expense_id'];
        $invoiceNo = 'RENT-EXP-'.$row['receipt_no'];
        $expenseData = array('Expense' => array(
            'id' => $expenseId > 0 ? $expenseId : null,
            'project_id' => (int)$row['project_id'],
            'vendor_id' => 0,
            'invoice_no' => $invoiceNo,
            'invoice_date' => $row['receipt_date'],
            'invoice_amount' => (float)$row['expense_amount'],
            'expense_category_id' => $this->_rentalExpenseCategoryId(),
            'remarks' => 'Auto rental expense from receipt '.$row['receipt_no']
        ));
        if ($expenseId <= 0) {
            $this->Expense->create();
        }
        if (!$this->Expense->save($expenseData, false)) {
            return false;
        }
        $expenseId = (int)$this->Expense->id;
        $paymentId = (int)$row['expenses_payment_id'];
        $paymentData = array('ExpensesPayment' => array(
            'id' => $paymentId > 0 ? $paymentId : null,
            'expense_id' => $expenseId,
            'paymenttype_id' => 0,
            'date' => $row['receipt_date'],
            'amount' => (float)$row['expense_amount'],
            'remarks' => 'Auto rental expense payment for receipt '.$row['receipt_no']
        ));
        if ($paymentId <= 0) {
            $this->ExpensesPayment->create();
        }
        $this->ExpensesPayment->save($paymentData, false);
        $paymentId = (int)$this->ExpensesPayment->id;
        $this->DealRentalReceipt->updateAll(array(
            'DealRentalReceipt.expense_id' => $expenseId,
            'DealRentalReceipt.expenses_payment_id' => $paymentId
        ), array('DealRentalReceipt.id' => (int)$row['id']));
        $monthly = $this->DealRentalMonthlyExpense->find('first', array(
            'conditions' => array('DealRentalMonthlyExpense.receipt_id' => (int)$row['id']),
            'recursive' => -1
        ));
        $monthlyData = array('DealRentalMonthlyExpense' => array(
            'id' => !empty($monthly['DealRentalMonthlyExpense']['id']) ? (int)$monthly['DealRentalMonthlyExpense']['id'] : null,
            'receipt_id' => (int)$row['id'],
            'deal_rental_id' => (int)$row['deal_rental_id'],
            'deal_id' => (int)$row['deal_id'],
            'client_id' => (int)$row['client_id'],
            'project_id' => (int)$row['project_id'],
            'expense_month' => $row['rent_month'],
            'expense_date' => $row['receipt_date'],
            'amount' => (float)$row['expense_amount'],
            'expense_id' => $expenseId,
            'remarks' => 'Auto rental monthly expense from receipt '.$row['receipt_no']
        ));
        if (empty($monthly['DealRentalMonthlyExpense']['id'])) {
            $this->DealRentalMonthlyExpense->create();
        }
        $this->DealRentalMonthlyExpense->save($monthlyData, false);
        return true;
    }

    protected function _deleteLinkedExpense($row)
    {
        
        $this->DealRentalMonthlyExpense->deleteAll(array('DealRentalMonthlyExpense.receipt_id' => (int)$row['id']), false);
        $this->loadModel('Expense');
        $this->loadModel('ExpensesPayment');
        if (!empty($row['expenses_payment_id'])) {
            $this->ExpensesPayment->delete((int)$row['expenses_payment_id'], false);
        }
        if (!empty($row['expense_id'])) {
            $this->Expense->delete((int)$row['expense_id'], false);
        }
    }

    protected function _rentalReceiptTemplateData($receipt, $rental, $summary, $allocations)
    {
        $this->loadModel('Template');
        $template = (array)$this->Template->find('first', array(
            'conditions' => array('Template.type' => 'RLT_RENTAL_RECEIPT', 'Template.status' => 'Published'),
            'order' => array('Template.id' => 'asc'),
            'recursive' => -1
        ));
        $body = !empty($template['Template']['description']) ? (string)$template['Template']['description'] : $this->_rentalReceiptTemplateBody();
        $x = (array)$receipt['DealRentalReceipt'];
        $r = (array)$rental['DealRental'];
        $deal = $this->_fetchDeal((int)$r['deal_id']);
        $agency = !empty($rental['Agency']) ? (array)$rental['Agency'] : array();
        $clientName = !empty($rental['Client']['name']) ? $rental['Client']['name'] : (!empty($deal['Client']['name']) ? $deal['Client']['name'] : $r['tenant_name']);
        $agencyName = !empty($agency['name']) ? $agency['name'] : (!empty($this->viewVars['siteOrganization']) ? $this->viewVars['siteOrganization'] : 'White Villas');
        $agencyAddress = !empty($agency['address']) ? $agency['address'] : (!empty($this->viewVars['siteAddress']) ? $this->viewVars['siteAddress'] : '');
        $contactParts = array();
        foreach (array('mobile', 'phone', 'email') as $contactField) {
            if (!empty($agency[$contactField]) && !in_array(trim((string)$agency[$contactField]), $contactParts, true)) {
                $contactParts[] = trim((string)$agency[$contactField]);
            }
        }
        if (empty($contactParts) && !empty($this->viewVars['siteEmail'])) {
            $contactParts[] = trim((string)$this->viewVars['siteEmail']);
        }
        $allocatedMonths = array();
        foreach ((array)$allocations as $allocationRow) {
            if (empty($allocationRow['DealRentalSchedule']['rent_month'])) {
                continue;
            }
            $monthLabel = date('M Y', strtotime($allocationRow['DealRentalSchedule']['rent_month'].'-01'));
            $allocatedAmount = !empty($allocationRow['DealRentalReceiptAllocation']['allocated_amount']) ? (float)$allocationRow['DealRentalReceiptAllocation']['allocated_amount'] : 0;
            $allocatedMonths[] = $monthLabel.' ('.$this->_rentalCurrency($allocatedAmount).')';
        }
        if (empty($allocatedMonths)) {
            $allocatedMonths[] = !empty($x['rent_month']) ? date('M Y', strtotime($x['rent_month'].'-01')) : '-';
        }
        $qrText = implode("\n", array(
            'Rental Receipt: '.$x['receipt_no'],
            'Agreement: '.$r['agreement_no'],
            'Tenant: '.$r['tenant_name'],
            'Rent Month: '.$x['rent_month'],
            'Amount: '.$this->_rentalCurrency((float)$x['rent_amount'])
        ));
        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=360x360&ecc=M&qzone=2&format=png&data='.rawurlencode($qrText);
        $agencyLogoUrl = $this->_rentalAgencyLogoUrl($agency);
        $templateRow = !empty($template['Template']) ? (array)$template['Template'] : array();
        $replacements = array(
            '{{receipt_no}}' => h($x['receipt_no']),
            '{{receipt_date}}' => h(!empty($x['receipt_date']) ? date('d-M-Y', strtotime($x['receipt_date'])) : ''),
            '{{print_date}}' => h(date('d-M-Y')),
            '{{rent_month}}' => h($x['rent_month']),
            '{{rent_month_label}}' => h(!empty($x['rent_month']) ? date('M Y', strtotime($x['rent_month'].'-01')) : ''),
            '{{rent_amount}}' => h($this->_rentalCurrency((float)$x['rent_amount'])),
            '{{rent_amount_words}}' => '',
            '{{expense_amount}}' => h($this->_rentalCurrency((float)$x['expense_amount'])),
            '{{net_amount}}' => h($this->_rentalCurrency((float)$x['net_amount'])),
            '{{payment_mode}}' => h($x['payment_mode']),
            '{{payment_reference}}' => h($x['remarks']),
            '{{allocated_months}}' => h(implode(', ', $allocatedMonths)),
            '{{agreement_no}}' => h($r['agreement_no']),
            '{{tenant_name}}' => h($r['tenant_name']),
            '{{tenant_cnic}}' => h($r['tenant_cnic']),
            '{{tenant_mobile}}' => h($r['tenant_mobile']),
            '{{tenant_email}}' => h($r['tenant_email']),
            '{{tenant_address}}' => h($r['tenant_address']),
            '{{client_name}}' => h($clientName),
            '{{deal_invoice_no}}' => h(!empty($deal['Deal']['invoice_no']) ? $deal['Deal']['invoice_no'] : '#'.(int)$r['deal_id']),
            '{{property_name}}' => h(!empty($deal['Property']['name']) ? $deal['Property']['name'] : ''),
            '{{agency_name}}' => h($agencyName),
            '{{project_name}}' => h($agencyName),
            '{{agency_address}}' => h($agencyAddress),
            '{{agency_contact}}' => h(implode(' | ', $contactParts)),
            '{{agency_phone}}' => h(!empty($agency['phone']) ? $agency['phone'] : (!empty($agency['mobile']) ? $agency['mobile'] : '')),
            '{{agency_email}}' => h(!empty($agency['email']) ? $agency['email'] : ''),
            '{{site_organization}}' => h(!empty($this->viewVars['siteOrganization']) ? $this->viewVars['siteOrganization'] : $agencyName),
            '{{total_rent_paid}}' => h($this->_rentalCurrency((float)$summary['paid'])),
            '{{outstanding_rent}}' => h($this->_rentalCurrency((float)$summary['outstanding'])),
            '{{overdue_rent}}' => h($this->_rentalCurrency((float)$summary['overdue'])),
            '{{advance_credit}}' => h($this->_rentalCurrency((float)$summary['credit'])),
            '{{receipt_qr_url}}' => h($qrUrl),
            '{{receipt_qr}}' => '<img src="'.h($qrUrl).'" alt="Rental Receipt QR">',
            '{{agency_logo_url}}' => h($agencyLogoUrl),
            '{{agency_logo}}' => $agencyLogoUrl !== '' ? '<img src="'.h($agencyLogoUrl).'" alt="'.h($agencyName).'">' : '',
            '{{ceo_signature}}' => $this->_rentalTemplateSignatureHtml($templateRow, 'ceo'),
            '{{chairman_signature}}' => $this->_rentalTemplateSignatureHtml($templateRow, 'chairman')
        );
        return array('body' => $body, 'replacements' => $replacements);
    }

    protected function _rentalCurrency($amount)
    {
        return trim((string)$this->currency).' '.number_format((float)$amount, 2);
    }

    protected function _rentalAgencyLogoUrl($agency)
    {
        $candidates = array();
        foreach (array('logo', 'image', 'photo') as $field) {
            if (!empty($agency[$field])) {
                $candidates[] = 'agency_thumb/'.trim((string)$agency[$field]);
                $candidates[] = trim((string)$agency[$field]);
            }
        }
        if (!empty($this->viewVars['frontLogo'])) {
            $candidates[] = trim((string)$this->viewVars['frontLogo']);
        }
        foreach ($candidates as $candidate) {
            if (preg_match('/^https?:\/\//i', $candidate)) {
                return $candidate;
            }
            $relative = trim(str_replace('\\', '/', $candidate), '/');
            if ($relative !== '' && file_exists(WWW_ROOT.'img'.DS.str_replace('/', DS, $relative))) {
                return Router::url('/img/'.$relative, true);
            }
        }
        return '';
    }

    protected function _rentalTemplateSignatureHtml($template, $type)
    {
        $fileField = $type.'_signature';
        $dirField = $type.'_signature_dir';
        if (empty($template[$fileField])) {
            return '';
        }
        $directory = !empty($template[$dirField]) ? trim((string)$template[$dirField], '/') : 'letter_signatures';
        $relative = trim($directory.'/'.$template[$fileField], '/');
        if (!file_exists(WWW_ROOT.'img'.DS.str_replace('/', DS, $relative))) {
            return '';
        }
        return '<img src="'.h(Router::url('/img/'.$relative, true)).'" alt="Signature" style="max-height:70px;max-width:180px;object-fit:contain;">';
    }

    protected function _renderRentalAgreement($rental)
    {
        $this->loadModel('Template');
        $template = $this->Template->find('first', array(
            'conditions' => array('Template.type' => 'RLT_RENTAL_AGREEMENT', 'Template.status' => 'Published'),
            'order' => array('Template.id' => 'desc'),
            'recursive' => -1
        ));
        $body = !empty($template['Template']['description']) ? (string)$template['Template']['description'] : $this->_rentalAgreementTemplateBody();
        $templateRow = !empty($template['Template']) ? (array)$template['Template'] : array();
        $r = (array)$rental['DealRental'];
        $deal = $this->_fetchDeal((int)$r['deal_id']);
        $agency = !empty($rental['Agency']) ? (array)$rental['Agency'] : array();
        $client = !empty($deal['Client']) ? (array)$deal['Client'] : (!empty($rental['Client']) ? (array)$rental['Client'] : array());
        $property = !empty($deal['Property']) ? (array)$deal['Property'] : array();
        $agencyName = !empty($agency['name']) ? $agency['name'] : (!empty($this->viewVars['siteOrganization']) ? $this->viewVars['siteOrganization'] : 'White Villas');
        $agencyAddress = !empty($agency['address']) ? $agency['address'] : (!empty($this->viewVars['siteAddress']) ? $this->viewVars['siteAddress'] : '');
        $agencyPhone = !empty($agency['phone']) ? $agency['phone'] : (!empty($agency['mobile']) ? $agency['mobile'] : '');
        $agencyEmail = !empty($agency['email']) ? $agency['email'] : (!empty($this->viewVars['siteEmail']) ? $this->viewVars['siteEmail'] : '');
        $agencyLogoUrl = $this->_rentalAgencyLogoUrl($agency);
        $replacements = array(
            '{{letter_date}}' => date('d-M-Y'),
            '{{today}}' => date('d-M-Y'),
            '{{print_date}}' => date('d-M-Y'),
            '{{agreement_no}}' => h($r['agreement_no']),
            '{{tenant_name}}' => h($r['tenant_name']),
            '{{tenant_cnic}}' => h($r['tenant_cnic']),
            '{{tenant_mobile}}' => h($r['tenant_mobile']),
            '{{tenant_email}}' => h($r['tenant_email']),
            '{{tenant_address}}' => h($r['tenant_address']),
            '{{monthly_rent}}' => h($this->_rentalCurrency((float)$r['monthly_rent'])),
            '{{security_deposit}}' => h($this->_rentalCurrency((float)$r['security_deposit'])),
            '{{start_date}}' => h(!empty($r['start_date']) ? date('d-M-Y', strtotime($r['start_date'])) : ''),
            '{{end_date}}' => h(!empty($r['end_date']) ? date('d-M-Y', strtotime($r['end_date'])) : ''),
            '{{due_day}}' => (int)$r['due_day'],
            '{{deal_invoice_no}}' => h(!empty($deal['Deal']['invoice_no']) ? $deal['Deal']['invoice_no'] : '#'.(int)$r['deal_id']),
            '{{deal_date}}' => h(!empty($deal['Deal']['date']) ? date('d-M-Y', strtotime($deal['Deal']['date'])) : ''),
            '{{deal_status}}' => h(!empty($deal['Deal']['deal_status']) ? $deal['Deal']['deal_status'] : ''),
            '{{property_name}}' => h(!empty($property['name']) ? $property['name'] : ''),
            '{{property_file}}' => h(!empty($property['name']) ? $property['name'] : ''),
            '{{property_area}}' => h(!empty($property['area']) ? $property['area'] : ''),
            '{{property_unit}}' => h(!empty($deal['Unit']['name']) ? $deal['Unit']['name'] : ''),
            '{{property_price}}' => h($this->_rentalCurrency(!empty($property['price']) ? (float)$property['price'] : 0)),
            '{{property_type}}' => h(!empty($deal['Type']['name']) ? $deal['Type']['name'] : ''),
            '{{property_contract}}' => h(!empty($deal['Contract']['name']) ? $deal['Contract']['name'] : ''),
            '{{client_name}}' => h(!empty($client['name']) ? $client['name'] : $r['tenant_name']),
            '{{client_cnic}}' => h(!empty($client['pincode']) ? $client['pincode'] : ''),
            '{{client_phone}}' => h(!empty($client['phone']) ? $client['phone'] : $r['tenant_mobile']),
            '{{client_mobile}}' => h(!empty($client['mobile']) ? $client['mobile'] : $r['tenant_mobile']),
            '{{client_email}}' => h(!empty($client['email']) ? $client['email'] : $r['tenant_email']),
            '{{client_address}}' => h(!empty($client['address']) ? $client['address'] : $r['tenant_address']),
            '{{agency_name}}' => h($agencyName),
            '{{project_name}}' => h($agencyName),
            '{{agency_address}}' => h($agencyAddress),
            '{{agency_phone}}' => h($agencyPhone),
            '{{agency_email}}' => h($agencyEmail),
            '{{agency_contact}}' => h(implode(' | ', array_filter(array($agencyPhone, $agencyEmail)))),
            '{{agency_logo_url}}' => h($agencyLogoUrl),
            '{{agency_logo}}' => $agencyLogoUrl !== '' ? '<img src="'.h($agencyLogoUrl).'" alt="'.h($agencyName).'">' : '',
            '{{site_name}}' => h(!empty($this->viewVars['siteName']) ? $this->viewVars['siteName'] : $agencyName),
            '{{site_organization}}' => h(!empty($this->viewVars['siteOrganization']) ? $this->viewVars['siteOrganization'] : $agencyName),
            '{{site_address}}' => h(!empty($this->viewVars['siteAddress']) ? $this->viewVars['siteAddress'] : $agencyAddress),
            '{{site_contact}}' => h(!empty($this->viewVars['contact']) ? $this->viewVars['contact'] : $agencyPhone),
            '{{site_email}}' => h(!empty($this->viewVars['siteEmail']) ? $this->viewVars['siteEmail'] : $agencyEmail),
            '{{ceo_signature}}' => $this->_rentalTemplateSignatureHtml($templateRow, 'ceo'),
            '{{chairman_signature}}' => $this->_rentalTemplateSignatureHtml($templateRow, 'chairman')
        );
        return strtr($body, $replacements);
    }
}
?>

