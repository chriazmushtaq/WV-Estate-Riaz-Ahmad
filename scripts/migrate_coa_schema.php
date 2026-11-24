<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=whitevillasfinal;charset=utf8', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "1. Connected to database.\n";

    // 1. Chart of Accounts Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `ardaccounts_chart_of_accounts` (
      `id` bigint(20) NOT NULL AUTO_INCREMENT,
      `project_id` int(11) NOT NULL DEFAULT 0,
      `account_code` varchar(30) NOT NULL,
      `account_name` varchar(150) NOT NULL,
      `parent_code` varchar(30) DEFAULT '',
      `account_type` varchar(20) NOT NULL, -- 'asset','liability','equity','income','expense'
      `normal_balance` varchar(10) NOT NULL DEFAULT 'debit', -- 'debit','credit'
      `category` varchar(80) DEFAULT '',
      `is_header` tinyint(1) NOT NULL DEFAULT 0, -- 1 for group/header (e.g. 10000, 11000), 0 for posting ledger
      `is_system_default` tinyint(1) NOT NULL DEFAULT 1,
      `is_active` tinyint(1) NOT NULL DEFAULT 1,
      `created` datetime DEFAULT NULL,
      `modified` datetime DEFAULT NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `uniq_project_code` (`project_id`, `account_code`),
      KEY `idx_project_type` (`project_id`, `account_type`),
      KEY `idx_parent_code` (`parent_code`),
      KEY `idx_is_active` (`is_active`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "2. Checked/created ardaccounts_chart_of_accounts table.\n";

    // 2. Safe ALTER for ardaccounts_ledgers
    $ledgerCols = array();
    $stmt = $pdo->query("DESCRIBE ardaccounts_ledgers");
    while ($col = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $ledgerCols[strtolower($col['Field'])] = true;
    }

    if (!isset($ledgerCols['account_code'])) {
        $pdo->exec("ALTER TABLE `ardaccounts_ledgers` ADD COLUMN `account_code` varchar(30) DEFAULT '' AFTER `category`");
        $pdo->exec("ALTER TABLE `ardaccounts_ledgers` ADD KEY `idx_account_code` (`account_code`)");
        echo " - Added account_code to ardaccounts_ledgers\n";
    }
    if (!isset($ledgerCols['account_type'])) {
        $pdo->exec("ALTER TABLE `ardaccounts_ledgers` ADD COLUMN `account_type` varchar(20) DEFAULT '' AFTER `account_code`");
        $pdo->exec("ALTER TABLE `ardaccounts_ledgers` ADD KEY `idx_account_type` (`account_type`)");
        echo " - Added account_type to ardaccounts_ledgers\n";
    }
    if (!isset($ledgerCols['normal_balance'])) {
        $pdo->exec("ALTER TABLE `ardaccounts_ledgers` ADD COLUMN `normal_balance` varchar(10) DEFAULT '' AFTER `account_type`");
        echo " - Added normal_balance to ardaccounts_ledgers\n";
    }
    if (!isset($ledgerCols['parent_id'])) {
        $pdo->exec("ALTER TABLE `ardaccounts_ledgers` ADD COLUMN `parent_id` bigint(20) DEFAULT 0 AFTER `normal_balance`");
        echo " - Added parent_id to ardaccounts_ledgers\n";
    }
    if (!isset($ledgerCols['coa_id'])) {
        $pdo->exec("ALTER TABLE `ardaccounts_ledgers` ADD COLUMN `coa_id` bigint(20) DEFAULT 0 AFTER `parent_id`");
        $pdo->exec("ALTER TABLE `ardaccounts_ledgers` ADD KEY `idx_coa_id` (`coa_id`)");
        echo " - Added coa_id to ardaccounts_ledgers\n";
    }
    echo "3. Checked/updated ardaccounts_ledgers columns.\n";

    // 3. Multi-line Vouchers Header
    $pdo->exec("CREATE TABLE IF NOT EXISTS `ardaccounts_vouchers` (
      `id` bigint(20) NOT NULL AUTO_INCREMENT,
      `project_id` int(11) NOT NULL DEFAULT 0,
      `voucher_no` varchar(80) NOT NULL,
      `voucher_type` varchar(20) NOT NULL, -- 'BRV','CRV','BPV','CPV','JV','CV','PV'
      `voucher_date` date NOT NULL,
      `narration` text DEFAULT NULL,
      `total_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
      `source_module` varchar(60) DEFAULT 'manual',
      `source_id` bigint(20) NOT NULL DEFAULT 0,
      `status` varchar(20) NOT NULL DEFAULT 'Posted',
      `created_by` int(11) NOT NULL DEFAULT 0,
      `approved_by` int(11) DEFAULT NULL,
      `approved_at` datetime DEFAULT NULL,
      `posted_by` int(11) DEFAULT NULL,
      `posted_at` datetime DEFAULT NULL,
      `created` datetime DEFAULT NULL,
      `modified` datetime DEFAULT NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `uniq_project_voucher_no` (`project_id`, `voucher_no`),
      KEY `idx_project_date` (`project_id`, `voucher_date`),
      KEY `idx_source` (`source_module`, `source_id`),
      KEY `idx_status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "4. Checked/created ardaccounts_vouchers table.\n";

    // 4. Multi-line Voucher Items (General Ledger Lines)
    $pdo->exec("CREATE TABLE IF NOT EXISTS `ardaccounts_voucher_items` (
      `id` bigint(20) NOT NULL AUTO_INCREMENT,
      `voucher_id` bigint(20) NOT NULL,
      `project_id` int(11) NOT NULL DEFAULT 0,
      `ledger_id` bigint(20) NOT NULL DEFAULT 0,
      `coa_id` bigint(20) NOT NULL DEFAULT 0,
      `account_code` varchar(30) DEFAULT '',
      `debit` decimal(15,2) NOT NULL DEFAULT 0.00,
      `credit` decimal(15,2) NOT NULL DEFAULT 0.00,
      `cost_center_site_id` bigint(20) DEFAULT 0,
      `property_id` int(11) DEFAULT 0,
      `deal_id` int(11) DEFAULT 0,
      `party_type` varchar(40) DEFAULT '',
      `party_id` bigint(20) DEFAULT 0,
      `narration` varchar(255) DEFAULT '',
      `created` datetime DEFAULT NULL,
      PRIMARY KEY (`id`),
      KEY `idx_voucher_id` (`voucher_id`),
      KEY `idx_project_ledger` (`project_id`, `ledger_id`),
      KEY `idx_coa_id` (`coa_id`),
      KEY `idx_deal_prop` (`deal_id`, `property_id`),
      KEY `idx_party` (`party_type`, `party_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "5. Checked/created ardaccounts_voucher_items table.\n";

    echo "ALL SCHEMA MIGRATIONS COMPLETED SUCCESSFULLY!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
