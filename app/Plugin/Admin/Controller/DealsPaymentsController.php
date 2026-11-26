<?php
App::uses('CakeTime', 'Utility');
App::uses('CakeNumber', 'Utility');
App::uses('Hash', 'Utility');
App::uses('CakeEmail', 'Network/Email');
class DealsPaymentsController extends AdminAppController {
    public $helpers = array('Html', 'Form','Session','NumToWord');
    public $components = array('Session');
    protected $_dealPaymentBlockedStatuses = array('Transferd','Transferred','Permanent Blocked','Pemanent Blocked','Refund','Refund-Running');
    protected $_lastReceiptSyncSummary = array();
    public function beforeFilter()
    {
        parent::beforeFilter();
        if($this->_shouldRunControllerBootstrap('dealspayments.schema', '2026-11-25.auto-increment.1'))
        {
            $this->_ensureDealPaymentTablesAutoIncrement();
            $this->_ensureArdReceiptAccountingTables();
            $this->_ensureDealPaymentReceiptPrefixColumns();
            $this->_ensureBusinessAccountingSchema();
            $this->_ensureDealCommunicationTable();
            $this->_ensureDealDocumentsTable();
            $this->_ensureDealsPaymentSlipColumns();
            $this->_ensureRentAdjustmentRentalSchema();
        }
    }

    protected function _ensureDealPaymentTablesAutoIncrement()
    {
        $this->loadModel('DealsPayment');
        foreach(array('deals_payments', 'plans_payments') as $tableName)
        {
            try
            {
                $cols = $this->DealsPayment->query("SHOW FULL COLUMNS FROM `{$tableName}` LIKE 'id'");
                if(!empty($cols[0]['COLUMNS']))
                {
                    $col = $cols[0]['COLUMNS'];
                    $extra = strtolower(isset($col['Extra']) ? $col['Extra'] : '');
                    $key = strtoupper(isset($col['Key']) ? $col['Key'] : '');
                    $type = isset($col['Type']) ? $col['Type'] : 'int(11)';
                    if(strpos($extra, 'auto_increment') === false)
                    {
                        if($key === 'PRI')
                        {
                            $this->DealsPayment->query("ALTER TABLE `{$tableName}` MODIFY `id` {$type} NOT NULL AUTO_INCREMENT");
                        }
                        else
                        {
                            $this->DealsPayment->query("ALTER TABLE `{$tableName}` MODIFY `id` {$type} NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (`id`)");
                        }
                    }
                }
            }
            catch(Exception $e) {}
        }
    }

    protected function _resolveDealProjectId($dealId)
    {
        $dealId = (int)$dealId;
        if($dealId <= 0)
        {
            return 0;
        }
        $this->loadModel('Deal');
        $dealRow = $this->Deal->find('first', array(
            'fields' => array('Deal.id', 'Property.project_id'),
            'joins' => array(
                array('table' => 'properties', 'alias' => 'Property', 'type' => 'LEFT', 'conditions' => array('Deal.property_id=Property.id'))
            ),
            'conditions' => array('Deal.id' => $dealId),
            'recursive' => -1
        ));
        if(!empty($dealRow['Property']['project_id']))
        {
            return (int)$dealRow['Property']['project_id'];
        }
        return 0;
    }

    protected function _canAccessDeal($dealId)
    {
        if ($this->_isSuperAdminSession() || strtoupper(trim((string)$this->userType)) === 'ADR') {
            return true;
        }
        $dealProjectId = $this->_resolveDealProjectId($dealId);
        if ($dealProjectId <= 0) {
            return false;
        }
        if ($this->_isAgencyAdminSession() || strtoupper(trim((string)$this->userType)) === 'AGY') {
            return ((int)$this->agencyId === (int)$dealProjectId);
        }
        if (strtoupper(trim((string)$this->userType)) === 'AGT') {
            $dealRow = $this->Deal->findById($dealId);
            if (!empty($dealRow['Deal']['user_id']) && (int)$dealRow['Deal']['user_id'] === (int)$this->luserId) {
                return true;
            }
            return (!empty($this->agencyId) && (int)$this->agencyId === (int)$dealProjectId);
        }
        return (!empty($this->agencyId) && (int)$this->agencyId === (int)$dealProjectId);
    }

    protected function _ensureArdReceiptAccountingTables()
    {
        $this->loadModel('Admin.Ardledger');
        $sql = array(
            "CREATE TABLE IF NOT EXISTS `ardaccounts_ledgers` (
                `id` bigint(20) NOT NULL AUTO_INCREMENT,
                `project_id` int(11) NOT NULL DEFAULT 0,
                `name` varchar(120) NOT NULL,
                `type` varchar(20) NOT NULL DEFAULT 'bank',
                `category` varchar(50) DEFAULT '',
                `account_no` varchar(60) DEFAULT '',
                `mobile_no` varchar(30) DEFAULT '',
                `address` varchar(255) DEFAULT '',
                `balance` decimal(15,2) NOT NULL DEFAULT 0.00,
                `is_trashed` tinyint(1) NOT NULL DEFAULT 0,
                `created` datetime DEFAULT NULL,
                `modified` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `project_id` (`project_id`),
                KEY `type` (`type`),
                KEY `is_trashed` (`is_trashed`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3",
            "CREATE TABLE IF NOT EXISTS `ardaccounts_transactions` (
                `id` bigint(20) NOT NULL AUTO_INCREMENT,
                `project_id` int(11) NOT NULL DEFAULT 0,
                `ledger_id` bigint(20) NOT NULL,
                `type` varchar(20) NOT NULL DEFAULT 'expense',
                `amount` decimal(15,2) NOT NULL DEFAULT 0.00,
                `description` text,
                `ref_no` varchar(80) DEFAULT '',
                `entry_date` date NOT NULL,
                `construction_site_id` bigint(20) DEFAULT 0,
                `created` datetime DEFAULT NULL,
                `modified` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `project_id` (`project_id`),
                KEY `ledger_id` (`ledger_id`),
                KEY `entry_date` (`entry_date`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3",
            "CREATE TABLE IF NOT EXISTS `ardaccounts_journal_entries` (
                `id` bigint(20) NOT NULL AUTO_INCREMENT,
                `project_id` int(11) NOT NULL DEFAULT 0,
                `transaction_id` bigint(20) NOT NULL DEFAULT 0,
                `source_type` varchar(40) NOT NULL DEFAULT 'voucher',
                `source_id` bigint(20) NOT NULL DEFAULT 0,
                `ref_no` varchar(80) DEFAULT '',
                `entry_date` date NOT NULL,
                `debit_account` varchar(160) NOT NULL,
                `credit_account` varchar(160) NOT NULL,
                `amount` decimal(15,2) NOT NULL DEFAULT 0.00,
                `description` text,
                `created` datetime DEFAULT NULL,
                `modified` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uniq_transaction` (`transaction_id`),
                KEY `project_id` (`project_id`),
                KEY `source_type` (`source_type`),
                KEY `source_id` (`source_id`),
                KEY `entry_date` (`entry_date`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3"
        );
        foreach($sql as $q)
        {
            try
            {
                $this->Ardledger->query($q);
            }
            catch (Exception $e)
            {
                // Keep flow running on schema variations.
            }
        }
        try
        {
            $this->Ardledger->query("ALTER TABLE `deals_payments` ADD COLUMN `ard_transaction_id` bigint(20) DEFAULT 0 AFTER `remarks`");
        }
        catch (Exception $e)
        {
            // Ignore if exists.
        }
        try
        {
            $this->Ardledger->query("ALTER TABLE `deals_payments` ADD COLUMN `ard_ledger_id` bigint(20) DEFAULT 0 AFTER `ard_transaction_id`");
        }
        catch (Exception $e)
        {
            // Ignore if exists.
        }
    }

    protected function _ensureDealPaymentReceiptPrefixColumns()
    {
        $this->loadModel('Admin.Ardsetting');
        $sql = array(
            "ALTER TABLE `ardaccounts_settings` ADD COLUMN `deal_payment_receipt_prefix` varchar(20) DEFAULT 'DPR-' AFTER `deal_invoice_next_no`",
            "ALTER TABLE `ardaccounts_settings` ADD COLUMN `deal_payment_receipt_next_no` int(11) NOT NULL DEFAULT 1 AFTER `deal_payment_receipt_prefix`",
            "ALTER TABLE `ardaccounts_settings` ADD COLUMN `deal_receipt_online_ledger_id` bigint(20) NOT NULL DEFAULT 0 AFTER `deal_payment_receipt_next_no`",
            "ALTER TABLE `ardaccounts_settings` ADD COLUMN `deal_receipt_cash_ledger_id` bigint(20) NOT NULL DEFAULT 0 AFTER `deal_receipt_online_ledger_id`",
            "ALTER TABLE `ardaccounts_settings` ADD COLUMN `paid_receipt_share_link_enabled` tinyint(1) NOT NULL DEFAULT 1 AFTER `deal_receipt_cash_ledger_id`",
            "ALTER TABLE `ardaccounts_settings` ADD COLUMN `paid_notification_popup_enabled` tinyint(1) NOT NULL DEFAULT 1 AFTER `paid_receipt_share_link_enabled`"
        );
        foreach($sql as $q)
        {
            try
            {
                $this->Ardsetting->query($q);
            }
            catch (Exception $e)
            {
                // Ignore if exists.
            }
        }
        try
        {
            // Reload schema so newly added columns are usable immediately in the same request.
            $this->Ardsetting->schema(true);
        }
        catch (Exception $e)
        {
            // Ignore schema refresh issues.
        }
    }

    protected function _isValidReceiptLedgerForProject($projectId, $ledgerId)
    {
        $this->loadModel('Admin.Ardledger');
        $projectId = (int)$projectId;
        $ledgerId = (int)$ledgerId;
        if($projectId <= 0 || $ledgerId <= 0)
        {
            return false;
        }
        $row = $this->Ardledger->find('first', array(
            'fields' => array('Ardledger.id'),
            'conditions' => array(
                'Ardledger.id' => $ledgerId,
                'Ardledger.project_id' => $projectId,
                'Ardledger.is_trashed' => 0,
                'Ardledger.type !=' => 'expense'
            ),
            'recursive' => -1
        ));
        return !empty($row['Ardledger']['id']);
    }

    protected function _findReceiptLedgerByName($projectId, $names = array())
    {
        $this->loadModel('Admin.Ardledger');
        $projectId = (int)$projectId;
        $names = (array)$names;
        if($projectId <= 0 || empty($names))
        {
            return 0;
        }
        foreach($names as $ledgerName)
        {
            $ledgerName = trim((string)$ledgerName);
            if($ledgerName === '')
            {
                continue;
            }
            $row = $this->Ardledger->find('first', array(
                'fields' => array('Ardledger.id'),
                'conditions' => array(
                    'Ardledger.project_id' => $projectId,
                    'Ardledger.name' => $ledgerName,
                    'Ardledger.is_trashed' => 0,
                    'Ardledger.type !=' => 'expense'
                ),
                'order' => array('Ardledger.id' => 'asc'),
                'recursive' => -1
            ));
            if(!empty($row['Ardledger']['id']))
            {
                return (int)$row['Ardledger']['id'];
            }
        }
        return 0;
    }

    protected function _findReceiptLedgerByLike($projectId, $patterns = array())
    {
        $this->loadModel('Admin.Ardledger');
        $projectId = (int)$projectId;
        $patterns = (array)$patterns;
        if($projectId <= 0 || empty($patterns))
        {
            return 0;
        }
        foreach($patterns as $pattern)
        {
            $pattern = trim((string)$pattern);
            if($pattern === '')
            {
                continue;
            }
            $row = $this->Ardledger->find('first', array(
                'fields' => array('Ardledger.id'),
                'conditions' => array(
                    'Ardledger.project_id' => $projectId,
                    'Ardledger.name LIKE' => $pattern,
                    'Ardledger.is_trashed' => 0,
                    'Ardledger.type !=' => 'expense'
                ),
                'order' => array('Ardledger.id' => 'asc'),
                'recursive' => -1
            ));
            if(!empty($row['Ardledger']['id']))
            {
                return (int)$row['Ardledger']['id'];
            }
        }
        return 0;
    }

    protected function _findFirstReceiptLedger($projectId, $types = array())
    {
        $this->loadModel('Admin.Ardledger');
        $projectId = (int)$projectId;
        $types = (array)$types;
        if($projectId <= 0)
        {
            return 0;
        }
        $conditions = array(
            'Ardledger.project_id' => $projectId,
            'Ardledger.is_trashed' => 0,
            'Ardledger.type !=' => 'expense'
        );
        if(!empty($types))
        {
            $conditions['Ardledger.type'] = $types;
        }
        $row = $this->Ardledger->find('first', array(
            'fields' => array('Ardledger.id'),
            'conditions' => $conditions,
            'order' => array('Ardledger.id' => 'asc'),
            'recursive' => -1
        ));
        if(!empty($row['Ardledger']['id']))
        {
            return (int)$row['Ardledger']['id'];
        }
        return 0;
    }

    protected function _getReceiptLedgerDefaultsFromSettings($projectId)
    {
        $projectId = (int)$projectId;
        $result = array(
            'online_ledger_id' => 0,
            'cash_ledger_id' => 0
        );
        if($projectId <= 0)
        {
            return $result;
        }
        $this->loadModel('Admin.Ardsetting');
        $this->_ensureDealPaymentReceiptPrefixColumns();
        try
        {
            $rows = $this->Ardsetting->query(
                "SELECT `deal_receipt_online_ledger_id`,`deal_receipt_cash_ledger_id`
                 FROM `ardaccounts_settings`
                 WHERE `project_id`=".(int)$projectId."
                 ORDER BY `id` ASC
                 LIMIT 1"
            );
            $raw = array();
            if(!empty($rows[0]['ardaccounts_settings']) && is_array($rows[0]['ardaccounts_settings']))
            {
                $raw = $rows[0]['ardaccounts_settings'];
            }
            elseif(!empty($rows[0][0]) && is_array($rows[0][0]))
            {
                $raw = $rows[0][0];
            }
            $onlineId = !empty($raw['deal_receipt_online_ledger_id']) ? (int)$raw['deal_receipt_online_ledger_id'] : 0;
            $cashId = !empty($raw['deal_receipt_cash_ledger_id']) ? (int)$raw['deal_receipt_cash_ledger_id'] : 0;
            if($this->_isValidReceiptLedgerForProject($projectId, $onlineId))
            {
                $result['online_ledger_id'] = $onlineId;
            }
            if($this->_isValidReceiptLedgerForProject($projectId, $cashId))
            {
                $result['cash_ledger_id'] = $cashId;
            }
        }
        catch(Exception $e)
        {
            // Keep defaults as 0.
        }
        return $result;
    }

    protected function _ensureDealsPaymentSlipColumns()
    {
        $this->loadModel('DealsPayment');
        $sql = array(
            "ALTER TABLE `deals_payments` ADD COLUMN `payment_slip` varchar(255) DEFAULT '' AFTER `remarks`",
            "ALTER TABLE `deals_payments` ADD COLUMN `payment_slip_dir` varchar(120) DEFAULT 'paymentslips' AFTER `payment_slip`",
            "ALTER TABLE `deals_payments` ADD COLUMN `payment_slip_mime` varchar(120) DEFAULT '' AFTER `payment_slip_dir`",
            "ALTER TABLE `deals_payments` ADD COLUMN `payment_slip_is_image` tinyint(1) NOT NULL DEFAULT 0 AFTER `payment_slip_mime`"
        );
        foreach($sql as $q)
        {
            try
            {
                $this->DealsPayment->query($q);
            }
            catch (Exception $e)
            {
                // Ignore if exists.
            }
        }
    }

    protected function _ensureDealCommunicationTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `deal_communications` (
            `id` bigint(20) NOT NULL AUTO_INCREMENT,
            `deal_id` int(11) NOT NULL DEFAULT 0,
            `project_id` int(11) NOT NULL DEFAULT 0,
            `client_id` int(11) NOT NULL DEFAULT 0,
            `contact_no` varchar(40) DEFAULT '',
            `entry_type` varchar(20) NOT NULL DEFAULT 'CALL',
            `subject` varchar(150) DEFAULT '',
            `message` text,
            `call_status` varchar(80) DEFAULT '',
            `followup_date` date DEFAULT NULL,
            `created_by` int(11) NOT NULL DEFAULT 0,
            `created` datetime DEFAULT NULL,
            `modified` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `deal_id` (`deal_id`),
            KEY `project_id` (`project_id`),
            KEY `client_id` (`client_id`),
            KEY `entry_type` (`entry_type`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3";
        try
        {
            $this->DealsPayment->query($sql);
        }
        catch (Exception $e)
        {
            // Keep flow running on schema variations.
        }
    }

    protected function _ensureDealDocumentsTable()
    {
        $tableExists = false;
        try
        {
            $existsRows = $this->DealsPayment->query("SHOW TABLES LIKE 'deal_documents'");
            $tableExists = !empty($existsRows);
        }
        catch (Exception $e)
        {
            $tableExists = false;
        }
        if($tableExists)
        {
            return;
        }
        $sqlList = array(
            "CREATE TABLE IF NOT EXISTS `deal_documents` (
                `id` bigint(20) NOT NULL AUTO_INCREMENT,
                `deal_id` int(11) NOT NULL DEFAULT 0,
                `project_id` int(11) NOT NULL DEFAULT 0,
                `client_id` int(11) NOT NULL DEFAULT 0,
                `document_type` varchar(40) NOT NULL DEFAULT 'Others',
                `document_date` date DEFAULT NULL,
                `photo` varchar(255) DEFAULT '',
                `dir` varchar(120) DEFAULT 'dealsdocuments',
                `mime_type` varchar(120) DEFAULT '',
                `is_image` tinyint(1) NOT NULL DEFAULT 0,
                `created_by` int(11) NOT NULL DEFAULT 0,
                `created` datetime DEFAULT NULL,
                `modified` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `deal_id` (`deal_id`),
                KEY `project_id` (`project_id`),
                KEY `client_id` (`client_id`),
                KEY `document_type` (`document_type`),
                KEY `document_date` (`document_date`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `deal_documents` (
                `id` bigint(20) NOT NULL AUTO_INCREMENT,
                `deal_id` int(11) NOT NULL DEFAULT 0,
                `project_id` int(11) NOT NULL DEFAULT 0,
                `client_id` int(11) NOT NULL DEFAULT 0,
                `document_type` varchar(40) NOT NULL DEFAULT 'Others',
                `document_date` date DEFAULT NULL,
                `photo` varchar(255) DEFAULT '',
                `dir` varchar(120) DEFAULT 'dealsdocuments',
                `mime_type` varchar(120) DEFAULT '',
                `is_image` tinyint(1) NOT NULL DEFAULT 0,
                `created_by` int(11) NOT NULL DEFAULT 0,
                `created` datetime DEFAULT NULL,
                `modified` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `deal_id` (`deal_id`),
                KEY `project_id` (`project_id`),
                KEY `client_id` (`client_id`),
                KEY `document_type` (`document_type`),
                KEY `document_date` (`document_date`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3"
        );
        foreach($sqlList as $sql)
        {
            try
            {
                $this->DealsPayment->query($sql);
                $existsRows = $this->DealsPayment->query("SHOW TABLES LIKE 'deal_documents'");
                if(!empty($existsRows))
                {
                    return;
                }
            }
            catch (Exception $e)
            {
                // Try next create statement variant.
            }
        }
    }

    protected function _normalizeDealDocumentDate($rawDate)
    {
        $rawDate = trim((string)$rawDate);
        if($rawDate === '')
        {
            return null;
        }
        if(preg_match('/^\d{4}-\d{2}-\d{2}$/', $rawDate))
        {
            return $rawDate;
        }
        $rawDate = str_replace('/', '-', $rawDate);
        $ts = strtotime($rawDate);
        if($ts === false)
        {
            return null;
        }
        return date('Y-m-d', $ts);
    }

    protected function _saveDealDocumentUpload($upload, $dirName = 'dealsdocuments')
    {
        $allowedExt = array('pdf','doc','docx','jpg','jpeg','png','gif','webp');
        $result = array(
            'ok' => false,
            'message' => __('Please select a valid document file.'),
            'file' => '',
            'dir' => $dirName,
            'mime' => '',
            'is_image' => 0
        );
        if(empty($upload) || empty($upload['tmp_name']))
        {
            $result['message'] = __('Please select a document/image/PDF file.');
            return $result;
        }
        $tmpName = (string)$upload['tmp_name'];
        $isUploaded = @is_uploaded_file($tmpName);
        if(!$isUploaded)
        {
            if(!@file_exists($tmpName))
            {
                $result['message'] = __('Please select a document/image/PDF file.');
                return $result;
            }
            // Fallback for browsers/crop flows that still submit valid tmp files
            // but are not flagged by is_uploaded_file in some environments.
            $tmpReal = @realpath($tmpName);
            $sysTmp = @realpath(sys_get_temp_dir());
            if($tmpReal === false || $sysTmp === false)
            {
                $result['message'] = __('Unable to validate uploaded temporary file.');
                return $result;
            }
            $tmpRealNorm = strtolower(str_replace('\\','/',$tmpReal));
            $sysTmpNorm = strtolower(rtrim(str_replace('\\','/',$sysTmp),'/') . '/');
            if(strpos($tmpRealNorm, $sysTmpNorm) !== 0)
            {
                $result['message'] = __('Unable to validate uploaded temporary file.');
                return $result;
            }
        }
        $originalName = isset($upload['name']) ? trim((string)$upload['name']) : '';
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        if($extension === '' || !in_array($extension, $allowedExt))
        {
            $result['message'] = __('Invalid file type. Allowed: PDF, DOC, DOCX, JPG, JPEG, PNG, GIF, WEBP.');
            return $result;
        }
        $targetDir = WWW_ROOT . 'img' . DS . $dirName;
        if(!is_dir($targetDir))
        {
            @mkdir($targetDir, 0775, true);
        }
        if(!is_dir($targetDir))
        {
            $result['message'] = __('Upload directory is not writable.');
            return $result;
        }
        $safeBase = preg_replace('/[^A-Za-z0-9_-]/', '', pathinfo($originalName, PATHINFO_FILENAME));
        if($safeBase === '')
        {
            $safeBase = 'deal_doc';
        }
        $fileName = date('YmdHis') . '_' . mt_rand(1000, 9999) . '_' . $safeBase . '.' . $extension;
        $targetFile = $targetDir . DS . $fileName;
        $moved = false;
        if($isUploaded)
        {
            $moved = @move_uploaded_file($tmpName, $targetFile);
        }
        if(!$moved)
        {
            $moved = @rename($tmpName, $targetFile);
        }
        if(!$moved)
        {
            $moved = @copy($tmpName, $targetFile);
            if($moved)
            {
                @unlink($tmpName);
            }
        }
        if(!$moved)
        {
            $result['message'] = __('Unable to upload file. Please try again.');
            return $result;
        }
        $mime = '';
        if(function_exists('mime_content_type'))
        {
            $detected = @mime_content_type($targetFile);
            if($detected !== false)
            {
                $mime = (string)$detected;
            }
        }
        $isImage = in_array($extension, array('jpg','jpeg','png','gif','webp')) ? 1 : 0;
        $result['ok'] = true;
        $result['message'] = __('Uploaded successfully.');
        $result['file'] = $fileName;
        $result['mime'] = $mime;
        $result['is_image'] = $isImage;
        return $result;
    }

    protected function _saveDealPaymentSlipUpload($upload, $dirName = 'paymentslips')
    {
        $allowedExt = array('pdf','doc','docx','jpg','jpeg','png','gif','webp');
        $result = array(
            'ok' => false,
            'message' => __('Please select a valid payment slip file.'),
            'file' => '',
            'dir' => $dirName,
            'mime' => '',
            'is_image' => 0
        );
        if(empty($upload) || !is_array($upload))
        {
            $result['message'] = __('Please select a payment slip file.');
            return $result;
        }
        $tmpName = isset($upload['tmp_name']) ? trim((string)$upload['tmp_name']) : '';
        $fileError = isset($upload['error']) ? (int)$upload['error'] : 0;
        $originalName = isset($upload['name']) ? trim((string)$upload['name']) : '';
        if($tmpName === '' && $fileError !== 0)
        {
            $result['message'] = __('Please select a payment slip file.');
            return $result;
        }
        if($tmpName === '' || !file_exists($tmpName))
        {
            $result['message'] = __('Unable to read uploaded payment slip.');
            return $result;
        }
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        if($extension === '' || !in_array($extension, $allowedExt))
        {
            $result['message'] = __('Invalid payment slip type. Allowed: PDF, DOC, DOCX, JPG, JPEG, PNG, GIF, WEBP.');
            return $result;
        }
        $targetDir = WWW_ROOT . 'img' . DS . $dirName;
        if(!is_dir($targetDir))
        {
            @mkdir($targetDir, 0775, true);
        }
        if(!is_dir($targetDir))
        {
            $result['message'] = __('Upload directory is not writable.');
            return $result;
        }
        $safeBase = preg_replace('/[^A-Za-z0-9_-]/', '', pathinfo($originalName, PATHINFO_FILENAME));
        if($safeBase === '')
        {
            $safeBase = 'payment_slip';
        }
        $fileName = date('YmdHis') . '_' . mt_rand(1000, 9999) . '_' . $safeBase . '.' . $extension;
        $targetFile = $targetDir . DS . $fileName;
        $moved = @move_uploaded_file($tmpName, $targetFile);
        if(!$moved)
        {
            $moved = @rename($tmpName, $targetFile);
        }
        if(!$moved)
        {
            $moved = @copy($tmpName, $targetFile);
            if($moved)
            {
                @unlink($tmpName);
            }
        }
        if(!$moved)
        {
            $result['message'] = __('Unable to upload payment slip. Please try again.');
            return $result;
        }
        $mime = '';
        if(function_exists('mime_content_type'))
        {
            $detected = @mime_content_type($targetFile);
            if($detected !== false)
            {
                $mime = (string)$detected;
            }
        }
        $isImage = in_array($extension, array('jpg','jpeg','png','gif','webp')) ? 1 : 0;
        $result['ok'] = true;
        $result['message'] = __('Uploaded successfully.');
        $result['file'] = $fileName;
        $result['mime'] = $mime;
        $result['is_image'] = $isImage;
        return $result;
    }

    protected function _deleteDealPaymentSlipFile($fileName, $dirName = 'paymentslips')
    {
        $fileName = trim((string)$fileName);
        $dirName = trim((string)$dirName);
        if($fileName === '')
        {
            return;
        }
        if($dirName === '')
        {
            $dirName = 'paymentslips';
        }
        $this->CustomFunction->fileDelete($fileName, $dirName, false);
    }

    protected function _deleteDealPaymentIncomeVouchers($paymentRow)
    {
        $payment = !empty($paymentRow['DealsPayment']) ? (array)$paymentRow['DealsPayment'] : (array)$paymentRow;
        $paymentId = !empty($payment['id']) ? (int)$payment['id'] : 0;
        if($paymentId <= 0)
        {
            return false;
        }
        $this->loadModel('Admin.Ardtransaction');
        $this->loadModel('Admin.Ardjournalentry');
        $this->loadModel('Property');

        $projectId = 0;
        if(!empty($payment['property_id']))
        {
            $projectId = (int)$this->Property->field('project_id', array('Property.id' => (int)$payment['property_id']));
        }
        $receiptNo = trim((string)(isset($payment['receipt_no']) ? $payment['receipt_no'] : ''));
        $linkedIds = array();
        if(!empty($payment['ard_transaction_id']))
        {
            $linkedIds[(int)$payment['ard_transaction_id']] = (int)$payment['ard_transaction_id'];
        }
        foreach($this->_ardParseComponentTransactionIds(isset($payment['ard_component_transaction_ids']) ? $payment['ard_component_transaction_ids'] : '') as $componentId)
        {
            $linkedIds[(int)$componentId] = (int)$componentId;
        }

        $orConditions = array(
            array(
                'Ardtransaction.source_type' => array('deal_receipt','deal_receipt_tax'),
                'Ardtransaction.source_id' => $paymentId
            )
        );
        if(!empty($linkedIds))
        {
            $orConditions[] = array('Ardtransaction.id' => array_values($linkedIds));
        }
        if($receiptNo !== '')
        {
            // Legacy auto vouchers predate source_id linkage. The exact receipt
            // plus generated description prevents manual income from matching.
            $orConditions[] = array(
                'Ardtransaction.type' => 'income',
                'Ardtransaction.ref_no' => $receiptNo,
                'Ardtransaction.description LIKE' => 'Deal receipt %'
            );
            $orConditions[] = array(
                'Ardtransaction.type' => 'income',
                'Ardtransaction.ref_no' => $receiptNo.'-TAX',
                'Ardtransaction.description LIKE' => 'Tax payable for deal receipt %'
            );
        }
        $conditions = array('OR' => $orConditions);
        if($projectId > 0)
        {
            $conditions['Ardtransaction.project_id'] = $projectId;
        }
        $voucherRows = (array)$this->Ardtransaction->find('all', array(
            'fields' => array('Ardtransaction.id','Ardtransaction.ledger_id','Ardtransaction.income_ledger_id','Ardtransaction.expense_ledger_id'),
            'conditions' => $conditions,
            'recursive' => -1
        ));
        $ledgerIds = array();
        foreach($voucherRows as $voucherRow)
        {
            if(empty($voucherRow['Ardtransaction']['id']))
            {
                continue;
            }
            $voucher = (array)$voucherRow['Ardtransaction'];
            $voucherId = (int)$voucher['id'];
            foreach(array('ledger_id','income_ledger_id','expense_ledger_id') as $ledgerField)
            {
                if(!empty($voucher[$ledgerField]))
                {
                    $ledgerIds[(int)$voucher[$ledgerField]] = (int)$voucher[$ledgerField];
                }
            }
            try
            {
                $this->Ardjournalentry->deleteAll(array('Ardjournalentry.transaction_id' => $voucherId), false);
            }
            catch(Exception $e)
            {
                return false;
            }
            if(!$this->Ardtransaction->delete($voucherId))
            {
                return false;
            }
        }
        foreach($ledgerIds as $ledgerId)
        {
            $this->_recalculateArdLedgerBalance((int)$ledgerId);
        }
        return true;
    }

    protected function _syncDealCancellationVoucherAfterPaymentDelete($dealId)
    {
        $dealId = (int)$dealId;
        if($dealId <= 0)
        {
            return true;
        }
        $this->loadModel('Deal');
        $this->loadModel('Property');
        $this->loadModel('Admin.Ardtransaction');
        $this->loadModel('Admin.Ardjournalentry');
        $dealRow = $this->Deal->find('first', array(
            'fields' => array('Deal.id','Deal.property_id','Deal.deal_status'),
            'conditions' => array('Deal.id' => $dealId),
            'recursive' => -1
        ));
        if(empty($dealRow['Deal']))
        {
            return true;
        }
        $status = trim((string)$dealRow['Deal']['deal_status']);
        if(!in_array($status, array('Permanent Blocked','Pemanent Blocked'), true))
        {
            return true;
        }
        $projectId = (int)$this->Property->field('project_id', array('Property.id' => (int)$dealRow['Deal']['property_id']));
        if($projectId <= 0)
        {
            return false;
        }
        $voucherRows = (array)$this->Ardtransaction->find('all', array(
            'conditions' => array(
                'Ardtransaction.project_id' => $projectId,
                'Ardtransaction.source_type' => 'deal_cancellation',
                'Ardtransaction.source_id' => $dealId
            ),
            'order' => array('Ardtransaction.id' => 'desc'),
            'recursive' => -1
        ));
        if(empty($voucherRows))
        {
            return true;
        }
        $paidRow = $this->DealsPayment->find('first', array(
            'fields' => array('SUM(DealsPayment.amount) AS paid_total'),
            'conditions' => array('DealsPayment.deal_id' => $dealId),
            'recursive' => -1
        ));
        $paidTotal = isset($paidRow[0]['paid_total']) ? round((float)$paidRow[0]['paid_total'], 2) : 0.0;
        if($paidTotal <= 0)
        {
            $ledgerIds = array();
            foreach($voucherRows as $voucherRow)
            {
                $voucher = (array)$voucherRow['Ardtransaction'];
                foreach(array('ledger_id','income_ledger_id','expense_ledger_id') as $ledgerField)
                {
                    if(!empty($voucher[$ledgerField])){$ledgerIds[(int)$voucher[$ledgerField]] = (int)$voucher[$ledgerField];}
                }
                $voucherId = (int)$voucher['id'];
                $this->Ardjournalentry->deleteAll(array('Ardjournalentry.transaction_id' => $voucherId), false);
                if(!$this->Ardtransaction->delete($voucherId))
                {
                    return false;
                }
            }
            foreach($ledgerIds as $ledgerId){$this->_recalculateArdLedgerBalance((int)$ledgerId);}
            return true;
        }

        $existing = (array)$voucherRows[0]['Ardtransaction'];
        $updatedId = $this->_ardPostAutoVoucher(
            $projectId,
            (int)$existing['ledger_id'],
            'income',
            $paidTotal,
            'CANCEL-'.$dealId,
            !empty($existing['entry_date']) ? $existing['entry_date'] : date('Y-m-d'),
            'Cancellation / forfeiture for deal #'.$dealId,
            array(
                'existing_id' => (int)$existing['id'],
                'income_settings_field' => 'cancellation_forfeiture_income_ledger_id',
                'income_fallback' => 'Cancellation / Forfeiture Income',
                'source_type' => 'deal_cancellation',
                'source_id' => $dealId,
                'status' => 'Posted'
            )
        );
        return $updatedId > 0;
    }

    protected function _ensurePaymentSlipMediaLibraryTables()
    {
        try
        {
            $Schema = ClassRegistry::init('Configuration');
            if(!$Schema)
            {
                return false;
            }
            $Schema->query("CREATE TABLE IF NOT EXISTS `media_folders` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `parent_id` int(11) DEFAULT NULL,
                `project_id` int(11) NOT NULL DEFAULT 0,
                `name` varchar(150) NOT NULL,
                `slug` varchar(160) DEFAULT '',
                `system_key` varchar(120) DEFAULT '',
                `description` text,
                `visibility` varchar(20) NOT NULL DEFAULT 'backend',
                `status` varchar(20) NOT NULL DEFAULT 'Active',
                `sort_order` int(11) NOT NULL DEFAULT 0,
                `created_by` int(11) NOT NULL DEFAULT 0,
                `modified_by` int(11) NOT NULL DEFAULT 0,
                `created` datetime DEFAULT NULL,
                `modified` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `idx_parent_id` (`parent_id`),
                KEY `idx_project_id` (`project_id`),
                KEY `idx_system_key` (`system_key`),
                KEY `idx_status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            $Schema->query("CREATE TABLE IF NOT EXISTS `media_files` (
                `id` bigint(20) NOT NULL AUTO_INCREMENT,
                `folder_id` int(11) NOT NULL DEFAULT 0,
                `project_id` int(11) NOT NULL DEFAULT 0,
                `source_type` varchar(80) NOT NULL DEFAULT 'library',
                `source_id` bigint(20) NOT NULL DEFAULT 0,
                `title` varchar(255) DEFAULT '',
                `alt_text` varchar(255) DEFAULT '',
                `file_name` varchar(255) NOT NULL,
                `original_name` varchar(255) DEFAULT '',
                `dir` varchar(255) DEFAULT '',
                `file_path` varchar(500) DEFAULT '',
                `url_path` varchar(500) DEFAULT '',
                `mime_type` varchar(150) DEFAULT '',
                `file_type` varchar(30) DEFAULT 'other',
                `extension` varchar(20) DEFAULT '',
                `file_size` bigint(20) NOT NULL DEFAULT 0,
                `visibility` varchar(20) NOT NULL DEFAULT 'backend',
                `status` varchar(20) NOT NULL DEFAULT 'Active',
                `created_by` int(11) NOT NULL DEFAULT 0,
                `modified_by` int(11) NOT NULL DEFAULT 0,
                `created` datetime DEFAULT NULL,
                `modified` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `idx_folder_id` (`folder_id`),
                KEY `idx_project_id` (`project_id`),
                KEY `idx_source` (`source_type`, `source_id`),
                KEY `idx_type_status` (`file_type`, `status`),
                KEY `idx_visibility` (`visibility`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            return true;
        }
        catch(Exception $e)
        {
            return false;
        }
    }

    protected function _ensurePaymentSlipsMediaFolder()
    {
        if(!$this->_ensurePaymentSlipMediaLibraryTables())
        {
            return 0;
        }
        $this->loadModel('MediaFolder');
        try
        {
            $this->MediaFolder->schema(true);
        }
        catch(Exception $e) {}
        $row = $this->MediaFolder->find('first', array(
            'conditions' => array('MediaFolder.system_key' => 'payment_slips'),
            'recursive' => -1
        ));
        if(empty($row['MediaFolder']['id']))
        {
            $row = $this->MediaFolder->find('first', array(
                'conditions' => array('LOWER(MediaFolder.name)' => 'payment slips'),
                'recursive' => -1
            ));
        }
        if(!empty($row['MediaFolder']['id']))
        {
            $folderId = (int)$row['MediaFolder']['id'];
            $this->MediaFolder->save(array('MediaFolder' => array(
                'id' => $folderId,
                'system_key' => 'payment_slips',
                'name' => 'Payment Slips',
                'slug' => 'payment-slips',
                'visibility' => 'backend',
                'status' => 'Active',
                'modified_by' => (int)$this->luserId,
                'modified' => date('Y-m-d H:i:s')
            )), array('validate' => false));
            return $folderId;
        }
        $this->MediaFolder->create();
        $this->MediaFolder->save(array('MediaFolder' => array(
            'parent_id' => null,
            'project_id' => 0,
            'name' => 'Payment Slips',
            'slug' => 'payment-slips',
            'system_key' => 'payment_slips',
            'description' => '',
            'visibility' => 'backend',
            'status' => 'Active',
            'sort_order' => 9,
            'created_by' => (int)$this->luserId,
            'modified_by' => (int)$this->luserId,
            'created' => date('Y-m-d H:i:s'),
            'modified' => date('Y-m-d H:i:s')
        )), array('validate' => false));
        return (int)$this->MediaFolder->id;
    }

    protected function _indexDealPaymentSlipInMediaGallery($paymentId, $slipMeta, $projectId = 0, $receiptNo = '')
    {
        $paymentId = (int)$paymentId;
        $slipMeta = (array)$slipMeta;
        $fileName = !empty($slipMeta['file']) ? basename((string)$slipMeta['file']) : '';
        if($paymentId <= 0 || $fileName === '')
        {
            return false;
        }
        $dir = !empty($slipMeta['dir']) ? trim(str_replace('\\', '/', (string)$slipMeta['dir']), '/') : 'paymentslips';
        if($dir === '' || strpos($dir, '..') !== false)
        {
            $dir = 'paymentslips';
        }
        $absPath = WWW_ROOT . 'img' . DS . str_replace('/', DS, $dir) . DS . $fileName;
        if(!is_file($absPath))
        {
            return false;
        }
        $folderId = $this->_ensurePaymentSlipsMediaFolder();
        if($folderId <= 0)
        {
            return false;
        }
        $this->loadModel('MediaFile');
        try
        {
            $this->MediaFile->schema(true);
        }
        catch(Exception $e) {}

        $existing = $this->MediaFile->find('first', array(
            'conditions' => array(
                'MediaFile.source_type' => 'deal_payment_slip',
                'MediaFile.source_id' => $paymentId
            ),
            'recursive' => -1
        ));
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $fileType = $this->_paymentSlipGalleryFileType($extension);
        $mime = !empty($slipMeta['mime']) ? trim((string)$slipMeta['mime']) : '';
        if($mime === '' && function_exists('mime_content_type'))
        {
            $detected = @mime_content_type($absPath);
            if($detected !== false)
            {
                $mime = (string)$detected;
            }
        }
        $receiptNo = trim((string)$receiptNo);
        if($receiptNo === '')
        {
            $paymentRow = $this->DealsPayment->find('first', array(
                'fields' => array('DealsPayment.receipt_no'),
                'conditions' => array('DealsPayment.id' => $paymentId),
                'recursive' => -1
            ));
            if(!empty($paymentRow['DealsPayment']['receipt_no']))
            {
                $receiptNo = trim((string)$paymentRow['DealsPayment']['receipt_no']);
            }
        }
        $title = $receiptNo !== '' ? 'Payment Slip - '.$receiptNo : 'Payment Slip #'.$paymentId;
        $now = date('Y-m-d H:i:s');
        $save = array(
            'folder_id' => $folderId,
            'project_id' => (int)$projectId,
            'source_type' => 'deal_payment_slip',
            'source_id' => $paymentId,
            'title' => $title,
            'alt_text' => $title,
            'file_name' => $fileName,
            'original_name' => $fileName,
            'dir' => $dir,
            'file_path' => $dir.'/'.$fileName,
            'url_path' => $dir.'/'.$fileName,
            'mime_type' => $mime,
            'file_type' => $fileType,
            'extension' => $extension,
            'file_size' => (int)@filesize($absPath),
            'visibility' => 'backend',
            'status' => 'Active',
            'modified_by' => (int)$this->luserId,
            'modified' => $now
        );
        if(!empty($existing['MediaFile']['id']))
        {
            $save['id'] = (int)$existing['MediaFile']['id'];
        }
        else
        {
            $save['created_by'] = (int)$this->luserId;
            $save['created'] = $now;
        }
        $this->MediaFile->create();
        return (bool)$this->MediaFile->save(array('MediaFile' => $save), array('validate' => false));
    }

    protected function _paymentSlipGalleryFileType($extension)
    {
        $extension = strtolower(trim((string)$extension));
        if(in_array($extension, array('jpg','jpeg','png','gif','webp'), true))
        {
            return 'image';
        }
        if(in_array($extension, array('pdf','doc','docx','xls','xlsx','csv','txt'), true))
        {
            return 'document';
        }
        return 'other';
    }

    protected function _isOnlineTransferPaymentType($paymentTypeName)
    {
        $name = strtolower(trim((string)$paymentTypeName));
        if($name === '')
        {
            return false;
        }
        if(strpos($name, 'cash') !== false)
        {
            return false;
        }
        return (strpos($name, 'online') !== false ||
            strpos($name, 'bank transfer') !== false ||
            strpos($name, 'transfer') !== false ||
            strpos($name, 'ubl') !== false);
    }

    protected function _isCashPaymentType($paymentTypeName)
    {
        $name = strtolower(trim((string)$paymentTypeName));
        return ($name !== '' && strpos($name, 'cash') !== false);
    }

    protected function _getOrCreateReceiptLedger($projectId, $ledgerName)
    {
        $this->loadModel('Admin.Ardledger');
        $projectId = (int)$projectId;
        $ledgerName = trim((string)$ledgerName);
        if($projectId <= 0 || $ledgerName === '')
        {
            return 0;
        }
        $row = $this->Ardledger->find('first', array(
            'fields' => array('Ardledger.id'),
            'conditions' => array(
                'Ardledger.project_id' => $projectId,
                'Ardledger.name' => $ledgerName,
                'Ardledger.is_trashed' => 0
            ),
            'recursive' => -1
        ));
        if(!empty($row['Ardledger']['id']))
        {
            return (int)$row['Ardledger']['id'];
        }
        $this->Ardledger->create();
        if($this->Ardledger->save(array('Ardledger' => array(
            'project_id' => $projectId,
            'name' => $ledgerName,
            'type' => 'bank',
            'category' => 'Auto Receipt Account',
            'balance' => 0,
            'is_trashed' => 0
        )), false))
        {
            return (int)$this->Ardledger->id;
        }
        return 0;
    }

    protected function _syncArdJournalFromTransaction($transactionId, $sourceType = 'deal_receipt', $sourceId = 0)
    {
        $this->loadModel('Admin.Ardtransaction');
        $this->loadModel('Admin.Ardledger');
        $transactionId = (int)$transactionId;
        if($transactionId <= 0)
        {
            return false;
        }
        $txRow = $this->Ardtransaction->find('first', array(
            'conditions' => array('Ardtransaction.id' => $transactionId),
            'recursive' => -1
        ));
        if(empty($txRow['Ardtransaction']))
        {
            return false;
        }
        $tx = $txRow['Ardtransaction'];
        $ledgerName = 'Ledger #' . (int)$tx['ledger_id'];
        if((int)$tx['ledger_id'] > 0)
        {
            $ledger = $this->Ardledger->find('first', array(
                'fields' => array('Ardledger.name'),
                'conditions' => array('Ardledger.id' => (int)$tx['ledger_id']),
                'recursive' => -1
            ));
            if(!empty($ledger['Ardledger']['name']))
            {
                $ledgerName = trim((string)$ledger['Ardledger']['name']);
            }
        }

        $incomeLedgerName = '';
        $incomeLedgerId = isset($tx['income_ledger_id']) ? (int)$tx['income_ledger_id'] : 0;
        if($incomeLedgerId > 0)
        {
            $incomeLedger = $this->Ardledger->find('first', array(
                'fields' => array('Ardledger.name'),
                'conditions' => array('Ardledger.id' => $incomeLedgerId),
                'recursive' => -1
            ));
            if(!empty($incomeLedger['Ardledger']['name']))
            {
                $incomeLedgerName = trim((string)$incomeLedger['Ardledger']['name']);
            }
        }

        $type = strtolower(trim((string)$tx['type']));
        $debitAccount = ($type === 'income') ? $ledgerName : 'Expense Control';
        $creditAccount = ($type === 'income') ? ($incomeLedgerName !== '' ? $incomeLedgerName : 'Income Control') : $ledgerName;

        $data = array(
            'project_id' => (int)$tx['project_id'],
            'transaction_id' => $transactionId,
            'source_type' => substr(trim((string)$sourceType), 0, 40),
            'source_id' => (int)$sourceId,
            'ref_no' => trim((string)$tx['ref_no']),
            'entry_date' => trim((string)$tx['entry_date']),
            'debit_account' => substr($debitAccount, 0, 160),
            'credit_account' => substr($creditAccount, 0, 160),
            'amount' => round((float)$tx['amount'], 2),
            'description' => trim((string)$tx['description'])
        );
        $db = $this->Ardledger->getDataSource();
        try
        {
            $this->Ardledger->query("CREATE TABLE IF NOT EXISTS `ardaccounts_journal_entries` (
                `id` bigint(20) NOT NULL AUTO_INCREMENT,
                `project_id` int(11) NOT NULL DEFAULT 0,
                `transaction_id` bigint(20) NOT NULL DEFAULT 0,
                `source_type` varchar(40) NOT NULL DEFAULT 'voucher',
                `source_id` bigint(20) NOT NULL DEFAULT 0,
                `ref_no` varchar(80) DEFAULT '',
                `entry_date` date NOT NULL,
                `debit_account` varchar(160) NOT NULL,
                `credit_account` varchar(160) NOT NULL,
                `amount` decimal(15,2) NOT NULL DEFAULT 0.00,
                `description` text,
                `created` datetime DEFAULT NULL,
                `modified` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uniq_transaction` (`transaction_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3");

            $existing = $this->Ardledger->query("SELECT `id` FROM `ardaccounts_journal_entries` WHERE `transaction_id`=".(int)$transactionId." LIMIT 1");
            $existingId = 0;
            if(!empty($existing))
            {
                if(isset($existing[0]['ardaccounts_journal_entries']['id']))
                {
                    $existingId = (int)$existing[0]['ardaccounts_journal_entries']['id'];
                }
                elseif(isset($existing[0][0]['id']))
                {
                    $existingId = (int)$existing[0][0]['id'];
                }
            }

            $projectId = (int)$data['project_id'];
            $sourceTypeQ = $db->value($data['source_type'], 'string');
            $sourceIdQ = (int)$data['source_id'];
            $refNoQ = $db->value($data['ref_no'], 'string');
            $entryDateQ = $db->value($data['entry_date'], 'string');
            $debitQ = $db->value($data['debit_account'], 'string');
            $creditQ = $db->value($data['credit_account'], 'string');
            $amountQ = (float)$data['amount'];
            $descriptionQ = $db->value($data['description'], 'string');

            if($existingId > 0)
            {
                $sql = "UPDATE `ardaccounts_journal_entries` SET
                    `project_id`={$projectId},
                    `source_type`={$sourceTypeQ},
                    `source_id`={$sourceIdQ},
                    `ref_no`={$refNoQ},
                    `entry_date`={$entryDateQ},
                    `debit_account`={$debitQ},
                    `credit_account`={$creditQ},
                    `amount`={$amountQ},
                    `description`={$descriptionQ},
                    `modified`=NOW()
                    WHERE `id`=".(int)$existingId;
            }
            else
            {
                $sql = "INSERT INTO `ardaccounts_journal_entries`
                    (`project_id`,`transaction_id`,`source_type`,`source_id`,`ref_no`,`entry_date`,`debit_account`,`credit_account`,`amount`,`description`,`created`,`modified`)
                    VALUES
                    ({$projectId},".(int)$transactionId.",{$sourceTypeQ},{$sourceIdQ},{$refNoQ},{$entryDateQ},{$debitQ},{$creditQ},{$amountQ},{$descriptionQ},NOW(),NOW())";
            }
            $this->Ardledger->query($sql);
            return true;
        }
        catch (Exception $e)
        {
            return false;
        }
    }

    protected function _deleteArdJournalByTransaction($transactionId)
    {
        $this->loadModel('Admin.Ardledger');
        $transactionId = (int)$transactionId;
        if($transactionId <= 0)
        {
            return;
        }
        try
        {
            $this->Ardledger->query("DELETE FROM `ardaccounts_journal_entries` WHERE `transaction_id`=".(int)$transactionId);
        }
        catch (Exception $e)
        {
            // Keep delete flow running even if journal table is unavailable.
        }
    }
    protected function _recalculateArdLedgerBalance($ledgerId)
    {
        return $this->_ardRecalculateLedgerBalanceCompat($ledgerId);
    }
    protected function _nextRecurringStep($recurring)
    {
        if($recurring==='Q')
        {
            return '+3 month';
        }
        if($recurring==='H')
        {
            return '+6 month';
        }
        if($recurring==='Y')
        {
            return '+1 year';
        }
        return '+1 month';
    }
    protected function _nextInstallmentName($plans=array(), $isOverdue=false)
    {
        $maxInstallmentNo = 0;
        foreach($plans as $planRow)
        {
            $planName = isset($planRow['name']) ? trim($planRow['name']) : '';
            if(preg_match('/^(?:(?:Monthly\s+)?(\d+)\s+Installment|Monthly\s+Installment\s+(\d+)|Overdue\s+Plan\s+(\d+))$/i', $planName, $match))
            {
                $serial = !empty($match[1]) ? (int)$match[1] : (!empty($match[2]) ? (int)$match[2] : (int)$match[3]);
                if($serial > $maxInstallmentNo)
                {
                    $maxInstallmentNo = $serial;
                }
            }
        }
        if($maxInstallmentNo <= 0)
        {
            $maxInstallmentNo = max(1, count($plans) - 1);
        }
        $nextNo = $maxInstallmentNo + 1;
        if($isOverdue)
        {
            return 'Overdue Plan ' . $nextNo;
        }
        return $nextNo . ' Installment';
    }
    protected function _getDealPaidEmiSum($dealId)
    {
        $row = $this->DealsPayment->find('first',array(
            'fields'=>array('SUM(DealsPayment.emi) AS paid_emi_total'),
            'conditions'=>array('DealsPayment.deal_id'=>$dealId),
            'recursive'=>-1
        ));
        $paidTotal = 0;
        if(isset($row[0]['paid_emi_total']) && $row[0]['paid_emi_total']!==null)
        {
            $paidTotal = (float)$row[0]['paid_emi_total'];
        }
        return round($paidTotal,2);
    }
    protected function _getPlanRowsWithPaid($dealId)
    {
        $this->loadModel('PlansPayment');
        $planRowsRaw = $this->PlansPayment->find('all',array(
            'fields'=>array('PlansPayment.id','PlansPayment.name','PlansPayment.amount','PlansPayment.date'),
            'conditions'=>array('PlansPayment.deal_id'=>$dealId),
            'order'=>array('PlansPayment.id'=>'asc'),
            'recursive'=>-1
        ));
        $paidRowsRaw = $this->DealsPayment->find('all',array(
            'fields'=>array('DealsPayment.plans_payment_id','SUM(DealsPayment.emi) AS paid_total'),
            'conditions'=>array('DealsPayment.deal_id'=>$dealId),
            'group'=>array('DealsPayment.plans_payment_id'),
            'recursive'=>-1
        ));
        $paidMap = array();
        foreach($paidRowsRaw as $paidRow)
        {
            $planId = isset($paidRow['DealsPayment']['plans_payment_id']) ? (int)$paidRow['DealsPayment']['plans_payment_id'] : 0;
            if($planId<=0)
            {
                continue;
            }
            $paidMap[$planId] = isset($paidRow[0]['paid_total']) ? round((float)$paidRow[0]['paid_total'],2) : 0;
        }
        $plans = array();
        foreach($planRowsRaw as $row)
        {
            $planId = (int)$row['PlansPayment']['id'];
            $planAmount = round((float)$row['PlansPayment']['amount'],2);
            $paidBefore = isset($paidMap[$planId]) ? $paidMap[$planId] : 0;
            $pending = round($planAmount-$paidBefore,2);
            if($pending<0)$pending=0;
            $plans[] = array(
                'id'=>$planId,
                'name'=>$row['PlansPayment']['name'],
                'amount'=>$planAmount,
                'date'=>$row['PlansPayment']['date'],
                'paid_before'=>$paidBefore,
                'pending'=>$pending
            );
        }
        return $plans;
    }
    protected function _findNextPendingInstallment($dealId)
    {
        $plans = $this->_getPlanRowsWithPaid($dealId);
        foreach($plans as $plan)
        {
            if($plan['pending']>0)
            {
                return array('plan'=>$plan,'plans'=>$plans);
            }
        }
        return array('plan'=>array(),'plans'=>$plans);
    }
    protected function _getDealScheduleContext($dealId)
    {
        $this->loadModel('Deal');
        $this->loadModel('Plan');
        $this->loadModel('PlansPayment');
        $deal = $this->Deal->find('first', array(
            'fields' => array('Deal.id', 'Deal.plan_id', 'Deal.total_amount', 'Deal.recurring'),
            'conditions' => array('Deal.id' => $dealId),
            'recursive' => -1
        ));
        if(!$deal)
        {
            return array('schedule_last_date' => null, 'is_schedule_over' => false, 'expected_installments' => 0);
        }
        $planId = (int)$deal['Deal']['plan_id'];
        $expectedInstallments = 0;
        if($planId > 0)
        {
            $p = $this->Plan->find('first', array(
                'fields' => array('Plan.id', 'Plan.installment', 'Plan.name'),
                'conditions' => array('Plan.id' => $planId),
                'recursive' => -1
            ));
            if($p)
            {
                $expectedInstallments = (int)$p['Plan']['installment'];
                if($expectedInstallments <= 0 && preg_match('/^(\d+)\s+Installment/i', (string)$p['Plan']['name'], $m))
                {
                    $expectedInstallments = (int)$m[1];
                }
            }
        }
        $planRows = $this->PlansPayment->find('all', array(
            'fields' => array('PlansPayment.id', 'PlansPayment.name', 'PlansPayment.date'),
            'conditions' => array(
                'PlansPayment.deal_id' => $dealId,
                'OR' => array(
                    'PlansPayment.is_deal_charge' => 0,
                    'PlansPayment.is_deal_charge IS NULL'
                )
            ),
            'order' => array('PlansPayment.id' => 'asc'),
            'recursive' => -1
        ));
        $scheduleLastDate = null;
        $originalPlanCount = 0;
        if($expectedInstallments > 0)
        {
            foreach($planRows as $row)
            {
                $name = trim((string)$row['PlansPayment']['name']);
                if(preg_match('/^(?:(?:Monthly\s+)?(\d+)\s+Installment|Monthly\s+Installment\s+(\d+)|Overdue\s+Plan\s+(\d+))/i', $name, $m))
                {
                    $num = !empty($m[1]) ? (int)$m[1] : (!empty($m[2]) ? (int)$m[2] : (int)$m[3]);
                    if($num > $expectedInstallments)
                    {
                        continue;
                    }
                }
                $originalPlanCount++;
                $d = $row['PlansPayment']['date'];
                if($d && ($scheduleLastDate === null || $d > $scheduleLastDate))
                {
                    $scheduleLastDate = $d;
                }
            }
        }
        else
        {
            foreach($planRows as $row)
            {
                $d = $row['PlansPayment']['date'];
                if($d && ($scheduleLastDate === null || $d > $scheduleLastDate))
                {
                    $scheduleLastDate = $d;
                }
            }
            $originalPlanCount = count($planRows);
        }
        $today = !empty($this->currentDate) ? $this->currentDate : date('Y-m-d');
        $isScheduleOver = ($scheduleLastDate !== null && $scheduleLastDate <= $today);
        return array(
            'schedule_last_date' => $scheduleLastDate,
            'is_schedule_over' => $isScheduleOver,
            'expected_installments' => $expectedInstallments,
            'original_plan_count' => $originalPlanCount
        );
    }
    protected function _createCarryForwardInstallment($dealId,$carryAmount,$recurring,$plans=array())
    {
        $this->loadModel('PlansPayment');
        $carryAmount = round((float)$carryAmount,2);
        if($carryAmount<=0)
        {
            return array();
        }
        $scheduleContext = $this->_getDealScheduleContext($dealId);
        $lastPlan = end($plans);
        $baseDate = isset($lastPlan['date']) && strlen($lastPlan['date'])>0 ? $lastPlan['date'] : $this->currentDate;
        
        $isScheduleOver = !empty($scheduleContext['is_schedule_over']);
        // If the agreed payment schedule is over, lock due date to schedule last payment date
        if($isScheduleOver && !empty($scheduleContext['schedule_last_date']))
        {
            $nextDate = $scheduleContext['schedule_last_date'];
        }
        else
        {
            $nextDate = date('Y-m-d',strtotime($baseDate.' '.$this->_nextRecurringStep($recurring)));
        }
        $nextName = $this->_nextInstallmentName($plans, $isScheduleOver);
        $this->PlansPayment->create();
        $saveOk = $this->PlansPayment->save(array('PlansPayment'=>array(
            'deal_id'=>$dealId,
            'name'=>$nextName,
            'amount'=>$carryAmount,
            'date'=>$nextDate
        )),false);
        if(!$saveOk)
        {
            return array();
        }
        return array(
            'id'=>$this->PlansPayment->id,
            'name'=>$nextName,
            'amount'=>$carryAmount,
            'date'=>$nextDate
        );
    }
    protected function _applyCarryForwardAllocation($dealId,$startPlanId,$paymentAmount,$recurring)
    {
        $plans = $this->_getPlanRowsWithPaid($dealId);
        $paymentAmount = round((float)$paymentAmount,2);
        if($paymentAmount<=0)
        {
            return array('success'=>false,'message'=>__('Payment amount should be greater than zero.'),'allocations'=>array());
        }
        if(empty($plans))
        {
            return array('success'=>false,'message'=>__('Payment plan not found.'),'allocations'=>array());
        }
        $startIndex = null;
        foreach($plans as $idx=>$plan)
        {
            if((int)$plan['id']===(int)$startPlanId)
            {
                $startIndex = $idx;
            }
        }
        if($startIndex===null)
        {
            return array('success'=>false,'message'=>__('Installment not found for allocation.'),'allocations'=>array());
        }
        $remaining = $paymentAmount;
        $allocations = array();
        for($i=$startIndex;$i<count($plans) && $remaining>0;$i++)
        {
            $planAmount = round((float)$plans[$i]['amount'],2);
            $paidBefore = isset($plans[$i]['paid_before']) ? round((float)$plans[$i]['paid_before'],2) : 0;
            $effectiveDue = round($planAmount-$paidBefore,2);
            if($effectiveDue<=0)
            {
                continue;
            }
            $allocated = $remaining>=$effectiveDue ? $effectiveDue : $remaining;
            $allocated = round((float)$allocated,2);
            if($allocated<=0)
            {
                continue;
            }
            $allocations[] = array(
                'plan_id'=>$plans[$i]['id'],
                'plan_name'=>$plans[$i]['name'],
                'amount'=>$allocated
            );
            $remaining = round($remaining-$allocated,2);
            if($allocated<$effectiveDue)
            {
                $carryForward = round($effectiveDue-$allocated,2);
                $newCurrentAmount = round($paidBefore+$allocated,2);
                $this->PlansPayment->id = $plans[$i]['id'];
                if(!$this->PlansPayment->saveField('amount',$newCurrentAmount,false))
                {
                    return array('success'=>false,'message'=>__('Unable to update installment amount.'),'allocations'=>array());
                }
                $plans[$i]['amount']=$newCurrentAmount;
                $plans[$i]['paid_before']=$newCurrentAmount;
                $plans[$i]['pending']=0;
                if(isset($plans[$i+1]))
                {
                    $nextAmount = round((float)$plans[$i+1]['amount']+$carryForward,2);
                    $this->PlansPayment->id = $plans[$i+1]['id'];
                    if(!$this->PlansPayment->saveField('amount',$nextAmount,false))
                    {
                        return array('success'=>false,'message'=>__('Unable to carry forward installment amount.'),'allocations'=>array());
                    }
                    $plans[$i+1]['amount']=$nextAmount;
                }
                else
                {
                    $newPlan = $this->_createCarryForwardInstallment($dealId,$carryForward,$recurring,$plans);
                    if(empty($newPlan))
                    {
                        return array('success'=>false,'message'=>__('Unable to create carry forward installment.'),'allocations'=>array());
                    }
                    $plans[] = $newPlan;
                }
                break;
            }
            if($remaining>0 && $i===count($plans)-1)
            {
                $newPlan = $this->_createCarryForwardInstallment($dealId,$remaining,$recurring,$plans);
                if(empty($newPlan))
                {
                    return array('success'=>false,'message'=>__('Unable to create additional installment.'),'allocations'=>array());
                }
                $allocations[] = array(
                    'plan_id'=>$newPlan['id'],
                    'plan_name'=>$newPlan['name'],
                    'amount'=>$remaining
                );
                $remaining = 0;
                break;
            }
        }
        if(empty($allocations))
        {
            return array('success'=>false,'message'=>__('Unable to allocate payment amount.'),'allocations'=>array());
        }
        return array('success'=>true,'message'=>'','allocations'=>$allocations,'remaining'=>$remaining);
    }
    protected function _generateReceiptNo($invoiceNo, $projectId = 0)
    {
        $prefixedReceiptNo = $this->_nextDealPaymentReceiptNo($projectId);
        if($prefixedReceiptNo !== '')
        {
            return $prefixedReceiptNo;
        }
        $lastRecord=$this->DealsPayment->find('first',array('fields' => array ('id'),'order' => array('id'=>'DESC')));
        if($lastRecord)
        {
            return $invoiceNo.'-'.str_pad($lastRecord['DealsPayment']['id']+1,4,"0",STR_PAD_LEFT);
        }
        return $invoiceNo.'-0001';
    }

    protected function _previewDealPaymentReceiptNo($invoiceNo, $projectId = 0)
    {
        $projectId = (int)$projectId;
        if($projectId>0)
        {
            $this->loadModel('Admin.Ardsetting');
            $this->_ensureDealPaymentReceiptPrefixColumns();
            try
            {
                $rows = $this->Ardsetting->query(
                    "SELECT `deal_payment_receipt_prefix`,`deal_payment_receipt_next_no`
                     FROM `ardaccounts_settings`
                     WHERE `project_id`=".$projectId."
                     ORDER BY `id` ASC
                     LIMIT 1"
                );
                $raw = array();
                if(!empty($rows[0]['ardaccounts_settings']) && is_array($rows[0]['ardaccounts_settings']))
                {
                    $raw = $rows[0]['ardaccounts_settings'];
                }
                elseif(!empty($rows[0][0]) && is_array($rows[0][0]))
                {
                    $raw = $rows[0][0];
                }
                $prefix = 'DPR-'.date('Y').'-';
                $nextNo = 1;
                if(!empty($raw))
                {
                    if(!empty($raw['deal_payment_receipt_prefix']))
                    {
                        $prefix = trim((string)$raw['deal_payment_receipt_prefix']);
                    }
                    if(!empty($raw['deal_payment_receipt_next_no']))
                    {
                        $nextNo = max(1,(int)$raw['deal_payment_receipt_next_no']);
                    }
                }
                $existingCounter = $this->_existingDealPaymentReceiptCounter($projectId, $prefix);
                if(!empty($existingCounter))
                {
                    $prefix = $existingCounter['prefix'];
                    $nextNo = max($nextNo, (int)$existingCounter['next_no']);
                }
                return substr($prefix,0,20).$nextNo;
            }
            catch(Exception $e)
            {
                // Fall back to the legacy invoice-based preview below.
            }
        }
        $lastRecord=$this->DealsPayment->find('first',array('fields'=>array('id'),'order'=>array('id'=>'DESC')));
        $nextId = !empty($lastRecord['DealsPayment']['id']) ? ((int)$lastRecord['DealsPayment']['id']+1) : 1;
        return $invoiceNo.'-'.str_pad($nextId,4,'0',STR_PAD_LEFT);
    }

    /**
     * Reconcile the configured counter with receipts already issued for a project.
     * This prevents a new deal's first payment from restarting the receipt series.
     */
    protected function _existingDealPaymentReceiptCounter($projectId, $preferredPrefix = '')
    {
        $projectId = (int)$projectId;
        $preferredPrefix = substr(trim((string)$preferredPrefix), 0, 20);
        if($projectId <= 0)
        {
            return array();
        }

        try
        {
            $rows = $this->DealsPayment->query(
                "SELECT `DealsPayment`.`receipt_no`
                 FROM `deals_payments` AS `DealsPayment`
                 INNER JOIN `properties` AS `Property`
                    ON `Property`.`id`=`DealsPayment`.`property_id`
                 WHERE `Property`.`project_id`=".$projectId."
                   AND `DealsPayment`.`receipt_no` IS NOT NULL
                   AND TRIM(`DealsPayment`.`receipt_no`)<>''
                 ORDER BY `DealsPayment`.`id` DESC
                 LIMIT 5000"
            );
        }
        catch(Exception $e)
        {
            return array();
        }

        $series = array();
        foreach((array)$rows as $row)
        {
            $receiptNo = '';
            if(isset($row['DealsPayment']['receipt_no']))
            {
                $receiptNo = trim((string)$row['DealsPayment']['receipt_no']);
            }
            elseif(isset($row[0]['receipt_no']))
            {
                $receiptNo = trim((string)$row[0]['receipt_no']);
            }
            if($receiptNo === '' || !preg_match('/^(.*?)(\d+)$/', $receiptNo, $matches))
            {
                continue;
            }

            $receiptPrefix = substr(trim((string)$matches[1]), 0, 20);
            $receiptNumber = (int)$matches[2];
            if($receiptPrefix === '' || $receiptNumber <= 0)
            {
                continue;
            }
            if(!isset($series[$receiptPrefix]))
            {
                $series[$receiptPrefix] = array('prefix'=>$receiptPrefix, 'max_no'=>0, 'count'=>0);
            }
            $series[$receiptPrefix]['max_no'] = max($series[$receiptPrefix]['max_no'], $receiptNumber);
            $series[$receiptPrefix]['count']++;
        }

        if(empty($series))
        {
            return array();
        }

        if($preferredPrefix !== '' && isset($series[$preferredPrefix]))
        {
            $selected = $series[$preferredPrefix];
        }
        else
        {
            $selected = reset($series);
            foreach($series as $candidate)
            {
                if($candidate['count'] > $selected['count'])
                {
                    $selected = $candidate;
                }
            }
        }

        return array(
            'prefix'=>$selected['prefix'],
            'next_no'=>((int)$selected['max_no'] + 1)
        );
    }

    protected function _dealPaymentAmountInWords($amount, $currencyLabel = 'Rs')
    {
        $amount = round(abs((float)$amount),2);
        $whole = (int)floor($amount);
        $fraction = (int)round(($amount-$whole)*100);
        if($fraction>=100)
        {
            $whole++;
            $fraction = 0;
        }
        $currency = strtoupper(trim(strip_tags((string)$currencyLabel)));
        if(in_array($currency,array('$','USD'),true))
        {
            $currencyWord = 'Dollars';
        }
        elseif(in_array($currency,array('AED','DH','DIRHAM','DIRHAMS'),true))
        {
            $currencyWord = 'Dirhams';
        }
        else
        {
            $currencyWord = 'Rupees';
        }
        $words = $this->_dealPaymentIntegerInWords($whole).' '.$currencyWord;
        if($fraction>0)
        {
            $words .= ' And '.$this->_dealPaymentIntegerInWords($fraction).' Paisa';
        }
        return trim($words).' Only';
    }

    protected function _dealPaymentIntegerInWords($number)
    {
        $number = (int)$number;
        if($number===0)
        {
            return 'Zero';
        }
        $labels = array('','Thousand','Lakh','Crore','Arab','Kharab');
        $groups = array(array($number%1000,''));
        $number = (int)floor($number/1000);
        $labelIndex = 1;
        while($number>0)
        {
            $groups[] = array($number%100,isset($labels[$labelIndex]) ? $labels[$labelIndex] : '');
            $number = (int)floor($number/100);
            $labelIndex++;
        }
        $parts = array();
        for($index=count($groups)-1;$index>=0;$index--)
        {
            $value = (int)$groups[$index][0];
            if($value<=0)
            {
                continue;
            }
            $part = $index===0 ? $this->_dealPaymentUnderThousandInWords($value) : $this->_dealPaymentUnderHundredInWords($value);
            if($groups[$index][1]!=='')
            {
                $part .= ' '.$groups[$index][1];
            }
            $parts[] = $part;
        }
        return implode(' ',$parts);
    }

    protected function _dealPaymentUnderThousandInWords($number)
    {
        $ones = array('','One','Two','Three','Four','Five','Six','Seven','Eight','Nine');
        $parts = array();
        $number = (int)$number;
        if($number>=100)
        {
            $parts[] = $ones[(int)floor($number/100)].' Hundred';
            $number %= 100;
        }
        if($number>0)
        {
            $parts[] = $this->_dealPaymentUnderHundredInWords($number);
        }
        return implode(' ',$parts);
    }

    protected function _dealPaymentUnderHundredInWords($number)
    {
        $small = array('Zero','One','Two','Three','Four','Five','Six','Seven','Eight','Nine','Ten','Eleven','Twelve','Thirteen','Fourteen','Fifteen','Sixteen','Seventeen','Eighteen','Nineteen');
        $tens = array(2=>'Twenty',3=>'Thirty',4=>'Forty',5=>'Fifty',6=>'Sixty',7=>'Seventy',8=>'Eighty',9=>'Ninety');
        $number = (int)$number;
        if($number<20)
        {
            return $small[$number];
        }
        $words = $tens[(int)floor($number/10)];
        if($number%10>0)
        {
            $words .= ' '.$small[$number%10];
        }
        return $words;
    }

    protected function _nextDealPaymentReceiptNo($projectId = 0)
    {
        $projectId = (int)$projectId;
        if($projectId <= 0)
        {
            return '';
        }
        $this->loadModel('Admin.Ardsetting');
        $this->_ensureDealPaymentReceiptPrefixColumns();
        $defaultPrefix = 'DPR-' . date('Y') . '-';
        $prefix = $defaultPrefix;
        $nextNo = 1;
        $db = $this->Ardsetting->getDataSource();
        try
        {
            $rows = $this->Ardsetting->query(
                "SELECT `id`,`deal_payment_receipt_prefix`,`deal_payment_receipt_next_no`
                 FROM `ardaccounts_settings`
                 WHERE `project_id`=" . $projectId . "
                 ORDER BY `id` ASC
                 LIMIT 1
                 FOR UPDATE"
            );
            $raw = array();
            if(!empty($rows[0]['ardaccounts_settings']) && is_array($rows[0]['ardaccounts_settings']))
            {
                $raw = $rows[0]['ardaccounts_settings'];
            }
            elseif(!empty($rows[0][0]) && is_array($rows[0][0]))
            {
                $raw = $rows[0][0];
            }
            if(!empty($raw))
            {
                $savedPrefix = isset($raw['deal_payment_receipt_prefix']) ? trim((string)$raw['deal_payment_receipt_prefix']) : '';
                if($savedPrefix !== '')
                {
                    $prefix = $savedPrefix;
                }
                $nextNo = isset($raw['deal_payment_receipt_next_no']) ? (int)$raw['deal_payment_receipt_next_no'] : 1;
                if($nextNo <= 0)
                {
                    $nextNo = 1;
                }
                $existingCounter = $this->_existingDealPaymentReceiptCounter($projectId, $prefix);
                if(!empty($existingCounter))
                {
                    $prefix = $existingCounter['prefix'];
                    $nextNo = max($nextNo, (int)$existingCounter['next_no']);
                }
                if(strlen($prefix) > 20)
                {
                    $prefix = substr($prefix, 0, 20);
                }
                if($prefix === '')
                {
                    $prefix = $defaultPrefix;
                    if(strlen($prefix) > 20)
                    {
                        $prefix = substr($prefix, 0, 20);
                    }
                }
                $prefixQ = $db->value($prefix, 'string');
                $this->Ardsetting->query(
                    "UPDATE `ardaccounts_settings`
                     SET `deal_payment_receipt_prefix`=" . $prefixQ . ",
                         `deal_payment_receipt_next_no`=" . ($nextNo + 1) . ",
                         `modified`=NOW()
                     WHERE `id`=" . (int)$raw['id'] . "
                     LIMIT 1"
                );
                return $prefix . $nextNo;
            }

            $existingCounter = $this->_existingDealPaymentReceiptCounter($projectId);
            if(!empty($existingCounter))
            {
                $prefix = $existingCounter['prefix'];
                $nextNo = max(1, (int)$existingCounter['next_no']);
            }
            if(strlen($prefix) > 20)
            {
                $prefix = substr($prefix, 0, 20);
            }
            $prefixQ = $db->value($prefix, 'string');
            $this->Ardsetting->query(
                "INSERT INTO `ardaccounts_settings`
                (`project_id`,`deal_payment_receipt_prefix`,`deal_payment_receipt_next_no`,`created`,`modified`)
                VALUES
                (" . $projectId . "," . $prefixQ . "," . ($nextNo + 1) . ",NOW(),NOW())
                ON DUPLICATE KEY UPDATE
                    `deal_payment_receipt_prefix`=VALUES(`deal_payment_receipt_prefix`),
                    `deal_payment_receipt_next_no`=IF(`deal_payment_receipt_next_no`<VALUES(`deal_payment_receipt_next_no`),VALUES(`deal_payment_receipt_next_no`),`deal_payment_receipt_next_no`),
                    `modified`=NOW()"
            );
            return $prefix . $nextNo;
        }
        catch (Exception $e)
        {
            return '';
        }
    }

    protected function _resolveReceiptLedgerId($projectId, $paymentTypeId, $selectedLedgerId = 0, $allowCreate = true)
    {
        $this->loadModel('Admin.Ardledger');
        $this->loadModel('Paymenttype');
        $projectId = (int)$projectId;
        $selectedLedgerId = (int)$selectedLedgerId;
        if($projectId <= 0)
        {
            return 0;
        }

        if($selectedLedgerId > 0)
        {
            $selected = $this->Ardledger->find('first', array(
                'fields' => array('Ardledger.id'),
                'conditions' => array(
                    'Ardledger.id' => $selectedLedgerId,
                    'Ardledger.project_id' => $projectId,
                    'Ardledger.is_trashed' => 0,
                    'Ardledger.type' => array('asset', 'bank')
                ),
                'recursive' => -1
            ));
            if(!empty($selected['Ardledger']['id']))
            {
                return (int)$selected['Ardledger']['id'];
            }
        }

        $settingsDefaults = $this->_getReceiptLedgerDefaultsFromSettings($projectId);
        $defaultOnlineLedgerId = isset($settingsDefaults['online_ledger_id']) ? (int)$settingsDefaults['online_ledger_id'] : 0;
        $defaultCashLedgerId = isset($settingsDefaults['cash_ledger_id']) ? (int)$settingsDefaults['cash_ledger_id'] : 0;

        $paymentTypeId = (int)$paymentTypeId;
        $paymentTypeName = '';
        if($paymentTypeId > 0)
        {
            $paymentType = $this->Paymenttype->find('first', array(
                'fields' => array('Paymenttype.name'),
                'conditions' => array('Paymenttype.id' => $paymentTypeId),
                'recursive' => -1
            ));
            if(!empty($paymentType['Paymenttype']['name']))
            {
                $paymentTypeName = $paymentType['Paymenttype']['name'];
            }
        }

        if($this->_isOnlineTransferPaymentType($paymentTypeName))
        {
            if($defaultOnlineLedgerId > 0)
            {
                return $defaultOnlineLedgerId;
            }
            $ledgerId = $this->_findReceiptLedgerByName($projectId, array('UBL', 'Online Transfer', 'Bank Transfer'));
            if($ledgerId > 0)
            {
                return $ledgerId;
            }
            $ledgerId = $this->_findReceiptLedgerByLike($projectId, array('%bank%', '%online%', '%transfer%', '%ubl%'));
            if($ledgerId > 0)
            {
                return $ledgerId;
            }
            return $this->_findFirstReceiptLedger($projectId, array('asset', 'bank'));
        }
        if($this->_isCashPaymentType($paymentTypeName))
        {
            if($defaultCashLedgerId > 0)
            {
                return $defaultCashLedgerId;
            }
            $ledgerId = $this->_findReceiptLedgerByName($projectId, array('Cash-inhand', 'Cash in hand', 'Cash'));
            if($ledgerId > 0)
            {
                return $ledgerId;
            }
            $ledgerId = $this->_findReceiptLedgerByLike($projectId, array('%cash%', '%inhand%', '%in hand%'));
            if($ledgerId > 0)
            {
                return $ledgerId;
            }
            return $this->_findFirstReceiptLedger($projectId, array('asset', 'bank'));
        }

        $ledgerId = $this->_findReceiptLedgerByName($projectId, array('General Receipt Account'));
        if($ledgerId > 0)
        {
            return $ledgerId;
        }
        return $this->_findFirstReceiptLedger($projectId, array('asset', 'bank'));
    }

    protected function _syncDealPaymentToArd($dealPaymentId, $paymentTypeId = 0, $selectedLedgerId = 0, $allowCreate = true, $reuseByReference = true)
    {
        $this->loadModel('Admin.Ardtransaction');
        $this->loadModel('Admin.Ardledger');
        $this->loadModel('DealsPayment');
        $this->loadModel('Deal');
        $this->loadModel('Property');
        $this->loadModel('PlansPayment');

        $dealPaymentId = (int)$dealPaymentId;
        if($dealPaymentId <= 0)
        {
            return false;
        }

        $payment = $this->DealsPayment->find('first', array(
            'conditions' => array('DealsPayment.id' => $dealPaymentId),
            'recursive' => -1
        ));
        if(empty($payment['DealsPayment']))
        {
            return false;
        }
        $payment = $payment['DealsPayment'];

        $deal = $this->Deal->find('first', array(
            'fields' => array('Deal.id', 'Deal.property_id', 'Deal.invoice_no'),
            'conditions' => array('Deal.id' => (int)$payment['deal_id']),
            'recursive' => -1
        ));
        if(empty($deal['Deal']))
        {
            return false;
        }

        $property = $this->Property->find('first', array(
            'fields' => array('Property.id', 'Property.project_id'),
            'conditions' => array('Property.id' => (int)$deal['Deal']['property_id']),
            'recursive' => -1
        ));
        $projectId = !empty($property['Property']['project_id']) ? (int)$property['Property']['project_id'] : 0;
        if($projectId <= 0)
        {
            return false;
        }

        $existingPaymentTypeId = isset($payment['paymenttype_id']) ? (int)$payment['paymenttype_id'] : 0;
        $existingArdLedgerId = isset($payment['ard_ledger_id']) ? (int)$payment['ard_ledger_id'] : 0;
        $resolvedPaymentTypeId = (int)$paymentTypeId > 0 ? (int)$paymentTypeId : $existingPaymentTypeId;
        $resolvedLedgerId = $this->_resolveReceiptLedgerId($projectId, $resolvedPaymentTypeId, $selectedLedgerId > 0 ? $selectedLedgerId : $existingArdLedgerId, $allowCreate);
        if($resolvedLedgerId <= 0)
        {
            return false;
        }

        $amount = isset($payment['amount']) ? round((float)$payment['amount'], 2) : 0;
        $taxAmount = isset($payment['tax_amount']) ? round((float)$payment['tax_amount'], 2) : 0;
        $receiptNo = trim((string)(isset($payment['receipt_no']) ? $payment['receipt_no'] : ''));
        if($receiptNo !== '')
        {
            $receiptTotalRow = $this->DealsPayment->find('first', array(
                'fields' => array('SUM(DealsPayment.amount) AS receipt_total', 'SUM(DealsPayment.tax_amount) AS receipt_tax_total'),
                'conditions' => array(
                    'DealsPayment.deal_id' => (int)$payment['deal_id'],
                    'DealsPayment.receipt_no' => $receiptNo
                ),
                'recursive' => -1
            ));
            if(isset($receiptTotalRow[0]['receipt_total']) && $receiptTotalRow[0]['receipt_total'] !== null)
            {
                $receiptAmount = round((float)$receiptTotalRow[0]['receipt_total'], 2);
                if($receiptAmount > 0)
                {
                    $amount = $receiptAmount;
                }
            }
            if(isset($receiptTotalRow[0]['receipt_tax_total']) && $receiptTotalRow[0]['receipt_tax_total'] !== null)
            {
                $taxAmount = round((float)$receiptTotalRow[0]['receipt_tax_total'], 2);
            }
        }
        if($amount <= 0)
        {
            $amount = isset($payment['total_amount']) ? round((float)$payment['total_amount'], 2) : 0;
        }
        $grossReceiptAmount = $amount;
        if($taxAmount < 0)
        {
            $taxAmount = 0;
        }
        if($taxAmount > $grossReceiptAmount)
        {
            $taxAmount = $grossReceiptAmount;
        }
        $amount = round($grossReceiptAmount - $taxAmount, 2);
        if($amount <= 0 && $taxAmount <= 0)
        {
            return false;
        }

        $referenceNo = $receiptNo;
        if($referenceNo === '')
        {
            $referenceNo = 'RCPT-' . $dealPaymentId;
        }

        $description = 'Deal receipt ' . $referenceNo;
        if(!empty($deal['Deal']['invoice_no']))
        {
            $description .= ' [' . $deal['Deal']['invoice_no'] . ']';
        }
        $entryDate = !empty($payment['payment_date']) ? trim((string)$payment['payment_date']) : '';
        if($entryDate === '')
        {
            $entryDate = date('Y-m-d');
        }
        else
        {
            $parsed = strtotime($entryDate);
            if($parsed !== false)
            {
                $entryDate = date('Y-m-d', $parsed);
            }
        }

        $incomeSettingsField = 'installment_income_ledger_id';
        $incomeFallback = 'Installment Income';
        try
        {
            $planId = isset($payment['plans_payment_id']) ? (int)$payment['plans_payment_id'] : 0;
            if($planId > 0)
            {
                $planRow = $this->PlansPayment->find('first', array(
                    'fields' => array('PlansPayment.id', 'PlansPayment.name', 'PlansPayment.is_deal_charge'),
                    'conditions' => array('PlansPayment.id' => $planId),
                    'recursive' => -1
                ));
                $planName = !empty($planRow['PlansPayment']['name']) ? strtolower(trim((string)$planRow['PlansPayment']['name'])) : '';
                if(!empty($planRow['PlansPayment']['is_deal_charge']))
                {
                    $incomeSettingsField = 'deal_charges_income_ledger_id';
                    $incomeFallback = 'Deal Charges Income';
                }
                elseif(strpos($planName, 'booking') !== false)
                {
                    $incomeSettingsField = 'booking_income_ledger_id';
                    $incomeFallback = 'Booking Income';
                }
            }
            $extraPayment = isset($payment['extra_payment']) ? round((float)$payment['extra_payment'], 2) : 0;
            $principalAmount = isset($payment['emi']) ? round((float)$payment['emi'], 2) : 0;
            if($extraPayment > 0 && $principalAmount <= 0)
            {
                $incomeSettingsField = 'late_fee_income_ledger_id';
                $incomeFallback = 'Late Fee Income';
            }
        }
        catch(Exception $e)
        {
            $incomeSettingsField = 'installment_income_ledger_id';
            $incomeFallback = 'Installment Income';
        }
        if($this->_isRentAdjustmentPaymentType($resolvedPaymentTypeId))
        {
            $incomeSettingsField = 'rental_adjustment_income_ledger_id';
            $incomeFallback = 'Rental Adjustment as Installment';
            $incomeLedgerId = $this->_ardEnsureRentalAdjustmentIncomeLedger($projectId);
        }
        else
        {
            $incomeLedgerId = $this->_ardDefaultLedgerId($projectId, $incomeSettingsField, $incomeFallback, 'income');
        }

        if($resolvedLedgerId === $incomeLedgerId || $resolvedLedgerId <= 0)
        {
            $fallbackReceiptId = $this->_findFirstReceiptLedger($projectId, array('asset', 'bank'));
            if($fallbackReceiptId > 0 && $fallbackReceiptId !== $incomeLedgerId)
            {
                $resolvedLedgerId = $fallbackReceiptId;
            }
        }

        $txData = array(
            'project_id' => $projectId,
            'ledger_id' => $resolvedLedgerId,
            'expense_ledger_id' => 0,
            'income_ledger_id' => $incomeLedgerId,
            'type' => 'income',
            'status' => 'Posted',
            'amount' => $amount,
            'description' => $description,
            'ref_no' => $referenceNo,
            'entry_date' => $entryDate,
            'construction_site_id' => 0,
            'source_type' => 'deal_receipt',
            'source_id' => $dealPaymentId,
            'auto_posted' => 1,
            'posted_by' => (int)$this->luserId,
            'posted_at' => date('Y-m-d H:i:s'),
            'modified_by' => (int)$this->luserId
        );

        $oldLedgerId = 0;
        $oldIncomeLedgerId = 0;
        $txId = isset($payment['ard_transaction_id']) ? (int)$payment['ard_transaction_id'] : 0;
        if($txId > 0)
        {
            $existingTx = $this->Ardtransaction->findById($txId);
            if(!empty($existingTx['Ardtransaction']))
            {
                $txData['id'] = $txId;
                $oldLedgerId = (int)$existingTx['Ardtransaction']['ledger_id'];
                $oldIncomeLedgerId = isset($existingTx['Ardtransaction']['income_ledger_id']) ? (int)$existingTx['Ardtransaction']['income_ledger_id'] : 0;
            }
        }
        if($txId <= 0 && $reuseByReference)
        {
            $existingByRef = $this->Ardtransaction->find('first', array(
                'fields' => array('Ardtransaction.id', 'Ardtransaction.ledger_id', 'Ardtransaction.income_ledger_id', 'Ardtransaction.status', 'Ardtransaction.entry_date', 'Ardtransaction.project_id'),
                'conditions' => array(
                    'Ardtransaction.project_id' => $projectId,
                    'Ardtransaction.type' => 'income',
                    'Ardtransaction.ref_no' => $referenceNo
                ),
                'order' => array('Ardtransaction.id' => 'desc'),
                'recursive' => -1
            ));
            if(!empty($existingByRef['Ardtransaction']['id']))
            {
                $txData['id'] = (int)$existingByRef['Ardtransaction']['id'];
                $oldLedgerId = (int)$existingByRef['Ardtransaction']['ledger_id'];
                $oldIncomeLedgerId = isset($existingByRef['Ardtransaction']['income_ledger_id']) ? (int)$existingByRef['Ardtransaction']['income_ledger_id'] : 0;
            }
        }
        if(empty($txData['id']))
        {
            $txData['created_by'] = (int)$this->luserId;
        }

        $this->Ardtransaction->create();
        if(!$this->Ardtransaction->save(array('Ardtransaction' => $txData), false))
        {
            return false;
        }
        $savedTxId = (int)$this->Ardtransaction->id;
        if($savedTxId <= 0)
        {
            return false;
        }

        $this->DealsPayment->id = $dealPaymentId;
        $linked = false;
        try
        {
            $this->DealsPayment->query("UPDATE `deals_payments` SET `ard_transaction_id`=".(int)$savedTxId.", `ard_ledger_id`=".(int)$resolvedLedgerId." WHERE `id`=".(int)$dealPaymentId);
            $linked = true;
        }
        catch (Exception $e)
        {
            $linked = false;
        }
        if(!$linked)
        {
            if($this->DealsPayment->hasField('ard_transaction_id'))
            {
                $this->DealsPayment->saveField('ard_transaction_id', $savedTxId, false);
            }
            if($this->DealsPayment->hasField('ard_ledger_id'))
            {
                $this->DealsPayment->saveField('ard_ledger_id', $resolvedLedgerId, false);
            }
        }

        try
        {
            $this->_syncArdJournalFromTransaction($savedTxId, 'deal_receipt', $dealPaymentId);
        }
        catch (Exception $e)
        {
            // Journal failure should not block receipt posting in ARD.
        }
        $this->_recalculateArdLedgerBalance($resolvedLedgerId);
        if($oldLedgerId > 0 && $oldLedgerId !== $resolvedLedgerId)
        {
            $this->_recalculateArdLedgerBalance($oldLedgerId);
        }
        if($incomeLedgerId > 0)
        {
            $this->_recalculateArdLedgerBalance($incomeLedgerId);
        }
        if($oldIncomeLedgerId > 0 && $oldIncomeLedgerId !== $incomeLedgerId)
        {
            $this->_recalculateArdLedgerBalance($oldIncomeLedgerId);
        }
        $componentTransactionIds = array();
        if($taxAmount > 0)
        {
            $taxLedgerId = $this->_ardDefaultLedgerId($projectId, 'tax_payable_ledger_id', 'Tax Payable', 'liability');
            if($taxLedgerId > 0)
            {
                $taxTxId = $this->_ardPostAutoVoucher(
                    $projectId,
                    $resolvedLedgerId,
                    'income',
                    $taxAmount,
                    $referenceNo.'-TAX',
                    $entryDate,
                    'Tax payable for deal receipt '.$referenceNo,
                    array(
                        'income_ledger_id' => $taxLedgerId,
                        'source_type' => 'deal_receipt_tax',
                        'source_id' => $dealPaymentId,
                        'status' => 'Posted'
                    )
                );
                if($taxTxId > 0)
                {
                    $componentTransactionIds['tax'] = (int)$taxTxId;
                }
            }
        }
        else
        {
            $this->_ardVoidAutoVoucherBySource($projectId, 'deal_receipt_tax', $dealPaymentId, 'Receipt tax removed');
        }
        try
        {
            $componentJson = !empty($componentTransactionIds) ? json_encode($componentTransactionIds) : '';
            $this->DealsPayment->query("UPDATE `deals_payments` SET `ard_component_transaction_ids`='".addslashes($componentJson)."' WHERE `id`=".(int)$dealPaymentId);
        }
        catch(Exception $e)
        {
            // Component links are for accounting lock checks only; keep the receipt save safe.
        }
        return true;
    }

    protected function _normalizePaymentDate($rawDate)
    {
        $rawDate = trim((string)$rawDate);
        if($rawDate === '')
        {
            return date('Y-m-d');
        }
        $parsed = strtotime($rawDate);
        if($parsed !== false)
        {
            return date('Y-m-d', $parsed);
        }
        if(preg_match('/^(\d{2})-(\d{2})-(\d{4})$/', $rawDate, $matches))
        {
            return $matches[3].'-'.$matches[2].'-'.$matches[1];
        }
        return $rawDate;
    }

    protected function _syncEditedPaymentVoucherDates($paymentIds)
    {
        $this->loadModel('DealsPayment');
        $this->loadModel('Admin.Ardtransaction');
        $cleanIds = array();
        foreach((array)$paymentIds as $paymentId)
        {
            $paymentId = (int)$paymentId;
            if($paymentId > 0)
            {
                $cleanIds[$paymentId] = $paymentId;
            }
        }
        if(empty($cleanIds))
        {
            return true;
        }

        $rows = $this->DealsPayment->find('all', array(
            'fields' => array('DealsPayment.id', 'DealsPayment.payment_date', 'DealsPayment.ard_transaction_id', 'DealsPayment.ard_component_transaction_ids'),
            'conditions' => array('DealsPayment.id' => array_values($cleanIds)),
            'order' => array('DealsPayment.id' => 'asc'),
            'recursive' => -1
        ));
        $transactionDates = array();
        foreach((array)$rows as $row)
        {
            $payment = (array)$row['DealsPayment'];
            $entryDate = $this->_normalizePaymentDate(isset($payment['payment_date']) ? $payment['payment_date'] : '');
            if(!preg_match('/^\d{4}-\d{2}-\d{2}$/', $entryDate))
            {
                return false;
            }
            $transactionIds = array();
            if(!empty($payment['ard_transaction_id']))
            {
                $transactionIds[(int)$payment['ard_transaction_id']] = (int)$payment['ard_transaction_id'];
            }
            foreach($this->_ardParseComponentTransactionIds(isset($payment['ard_component_transaction_ids']) ? $payment['ard_component_transaction_ids'] : '') as $componentTransactionId)
            {
                $transactionIds[(int)$componentTransactionId] = (int)$componentTransactionId;
            }
            foreach($transactionIds as $transactionId)
            {
                if($transactionId > 0 && !isset($transactionDates[$transactionId]))
                {
                    $transactionDates[$transactionId] = $entryDate;
                }
            }
        }

        foreach($transactionDates as $transactionId => $entryDate)
        {
            $transaction = $this->Ardtransaction->find('first', array(
                'fields' => array('Ardtransaction.id', 'Ardtransaction.source_type', 'Ardtransaction.source_id'),
                'conditions' => array('Ardtransaction.id' => (int)$transactionId),
                'recursive' => -1
            ));
            if(empty($transaction['Ardtransaction']['id']))
            {
                return false;
            }
            $this->Ardtransaction->create();
            if(!$this->Ardtransaction->save(array('Ardtransaction' => array(
                'id' => (int)$transactionId,
                'entry_date' => $entryDate,
                'modified_by' => (int)$this->luserId,
                'modified' => date('Y-m-d H:i:s')
            )), false))
            {
                return false;
            }
            $sourceType = !empty($transaction['Ardtransaction']['source_type']) ? $transaction['Ardtransaction']['source_type'] : 'deal_receipt';
            $sourceId = !empty($transaction['Ardtransaction']['source_id']) ? (int)$transaction['Ardtransaction']['source_id'] : 0;
            if(!$this->_syncArdJournalFromTransaction((int)$transactionId, $sourceType, $sourceId))
            {
                return false;
            }
        }
        return true;
    }

    protected function _syncExistingReceiptsToArd($dealId = null)
    {
        $this->loadModel('DealsPayment');
        $fields = array('DealsPayment.id','DealsPayment.receipt_no','DealsPayment.remarks','DealsPayment.paymenttype_id','DealsPayment.ard_ledger_id','DealsPayment.ard_transaction_id');

        $conditions = array();
        if((int)$dealId > 0)
        {
            $conditions['DealsPayment.deal_id'] = (int)$dealId;
        }

        $synced = 0;
        $processed = 0;
        $failed = 0;
        $errors = array();
        $lastId = 0;
        $batchSize = 300;

        do
        {
            $batchConditions = $conditions;
            $batchConditions['DealsPayment.id >'] = $lastId;
            $rows = $this->DealsPayment->find('all', array(
                'fields' => $fields,
                'conditions' => $batchConditions,
                'order' => array('DealsPayment.id' => 'asc'),
                'limit' => $batchSize,
                'recursive' => -1
            ));
            if(empty($rows))
            {
                break;
            }

            foreach($rows as $row)
            {
                $pid = isset($row['DealsPayment']['id']) ? (int)$row['DealsPayment']['id'] : 0;
                if($pid <= 0)
                {
                    continue;
                }
                $lastId = $pid;
                $processed++;

                try
                {
                    $txId = isset($row['DealsPayment']['ard_transaction_id']) ? (int)$row['DealsPayment']['ard_transaction_id'] : 0;
                    if($txId > 0)
                    {
                        continue;
                    }
                    if($this->_isInternalDealTransferPayment($row['DealsPayment']))
                    {
                        continue;
                    }
                    $paymentTypeId = isset($row['DealsPayment']['paymenttype_id']) ? (int)$row['DealsPayment']['paymenttype_id'] : 0;
                    $ledgerId = isset($row['DealsPayment']['ard_ledger_id']) ? (int)$row['DealsPayment']['ard_ledger_id'] : 0;
                    if($this->_syncDealPaymentToArd($pid, $paymentTypeId, $ledgerId, true))
                    {
                        $synced++;
                    }
                    else
                    {
                        $failed++;
                    }
                }
                catch (Exception $e)
                {
                    $failed++;
                    if(count($errors) < 5)
                    {
                        $errors[] = 'Payment ID '.$pid.': '.$e->getMessage();
                    }
                }
            }
        }
        while(count($rows) === $batchSize);

        $this->_lastReceiptSyncSummary = array(
            'processed' => $processed,
            'synced' => $synced,
            'failed' => $failed,
            'errors' => $errors
        );
        return $synced;
    }

    protected function _isInternalDealTransferPayment($payment)
    {
        $payment = (array)$payment;
        $receiptNo = isset($payment['receipt_no']) ? strtoupper(trim((string)$payment['receipt_no'])) : '';
        $remarks = isset($payment['remarks']) ? strtolower(trim((string)$payment['remarks'])) : '';
        if(strpos($receiptNo, 'DMG-') === 0 || strpos($receiptNo, 'DTR-MRG-') === 0)
        {
            return true;
        }
        return (strpos($remarks, 'internal merge credit') !== false);
    }

    protected function _paymentRowsLockedMessage($rows)
    {
        $dealIds = array();
        $transactionIds = array();
        foreach((array)$rows as $row)
        {
            $payment = isset($row['DealsPayment']) ? (array)$row['DealsPayment'] : array();
            if(empty($payment)) { continue; }
            if($this->_isInternalDealTransferPayment($payment))
            {
                return __('This is an internal merge payment entry and cannot be edited or deleted. Reverse the deal merge if it was created by mistake.');
            }
            if(!empty($payment['deal_id']))
            {
                $dealIds[(int)$payment['deal_id']] = (int)$payment['deal_id'];
            }
            if(!empty($payment['ard_transaction_id']))
            {
                $transactionIds[(int)$payment['ard_transaction_id']] = (int)$payment['ard_transaction_id'];
            }
            if(!empty($payment['ard_component_transaction_ids']))
            {
                foreach($this->_ardParseComponentTransactionIds($payment['ard_component_transaction_ids']) as $componentTransactionId)
                {
                    $transactionIds[(int)$componentTransactionId] = (int)$componentTransactionId;
                }
            }
        }
        foreach($transactionIds as $transactionId)
        {
            $message = $this->_ardTransactionLockedMessage($transactionId);
            if($message !== '')
            {
                return $message;
            }
        }
        foreach($dealIds as $dealId)
        {
            $message = $this->_dealPaymentLockedMessage($dealId);
            if($message !== '')
            {
                return $message;
            }
        }
        return '';
    }

    protected function _dealPaymentLockedMessage($dealId)
    {
        $this->loadModel('Deal');
        $dealId = (int)$dealId;
        if($dealId <= 0 || !$this->Deal->hasField('deal_status'))
        {
            return '';
        }
        $dealControl = $this->Deal->find('first',array(
            'fields'=>array('Deal.id','Deal.deal_status','Deal.deal_status_reason'),
            'conditions'=>array('Deal.id'=>$dealId),
            'recursive'=>-1
        ));
        if(empty($dealControl['Deal']))
        {
            return '';
        }
        $dealStatus = isset($dealControl['Deal']['deal_status']) && strlen($dealControl['Deal']['deal_status'])>0 ? $dealControl['Deal']['deal_status'] : 'Active';
        if(!in_array($dealStatus,$this->_dealPaymentBlockedStatuses))
        {
            return '';
        }
        $message = __('Payment changes are blocked for this deal because status is').' '.$dealStatus.'.';
        $reason = isset($dealControl['Deal']['deal_status_reason']) ? trim((string)$dealControl['Deal']['deal_status_reason']) : '';
        if($reason !== '')
        {
            $message .= ' '.__('Reason').': '.$reason;
        }
        return $message;
    }

    protected function _getLastReceiptSyncSummary()
    {
        if(empty($this->_lastReceiptSyncSummary))
        {
            return array(
                'processed' => 0,
                'synced' => 0,
                'failed' => 0,
                'errors' => array()
            );
        }
        return $this->_lastReceiptSyncSummary;
    }

    protected function _buildReceiptAccountUiData($projectId, $paymentTypeList = array())
    {
        $this->loadModel('Admin.Ardledger');
        $projectId = (int)$projectId;
        $ublLedgerId = 0;
        $cashLedgerId = 0;

        $ledgerList = array();
        if($projectId > 0)
        {
            $ledgerList = $this->Ardledger->find('list', array(
                'fields' => array('Ardledger.id', 'Ardledger.name'),
                'conditions' => array(
                    'Ardledger.project_id' => $projectId,
                    'Ardledger.is_trashed' => 0,
                    'Ardledger.type !=' => 'expense'
                ),
                'order' => array('Ardledger.name' => 'asc')
            ));
            $settingsDefaults = $this->_getReceiptLedgerDefaultsFromSettings($projectId);
            $ublLedgerId = isset($settingsDefaults['online_ledger_id']) ? (int)$settingsDefaults['online_ledger_id'] : 0;
            $cashLedgerId = isset($settingsDefaults['cash_ledger_id']) ? (int)$settingsDefaults['cash_ledger_id'] : 0;
            if($ublLedgerId <= 0)
            {
                $ublLedgerId = $this->_findReceiptLedgerByName($projectId, array('UBL', 'Online Transfer', 'Bank Transfer'));
            }
            if($cashLedgerId <= 0)
            {
                $cashLedgerId = $this->_findReceiptLedgerByName($projectId, array('Cash-inhand', 'Cash in hand', 'Cash'));
            }
            if($ublLedgerId > 0 && !isset($ledgerList[$ublLedgerId]))
            {
                $ublLedgerId = 0;
            }
            if($cashLedgerId > 0 && !isset($ledgerList[$cashLedgerId]))
            {
                $cashLedgerId = 0;
            }
        }

        $defaultMap = array();
        foreach((array)$paymentTypeList as $ptypeId => $ptypeName)
        {
            $ptypeId = (int)$ptypeId;
            if($ptypeId <= 0)
            {
                continue;
            }
            if($this->_isOnlineTransferPaymentType($ptypeName))
            {
                $defaultMap[$ptypeId] = $ublLedgerId;
            }
            elseif($this->_isCashPaymentType($ptypeName))
            {
                $defaultMap[$ptypeId] = $cashLedgerId;
            }
            else
            {
                $defaultMap[$ptypeId] = 0;
            }
        }

        return array(
            'ledger_list' => $ledgerList,
            'default_map' => $defaultMap,
            'ubl_ledger_id' => $ublLedgerId,
            'cash_ledger_id' => $cashLedgerId
        );
    }
    protected function _getReceiptSummaryData($dealId,$dealTotalAmount=0)
    {
        $dealTotalAmount = round((float)$dealTotalAmount,2);
        $paidRow = $this->DealsPayment->find('first',array(
            'fields'=>array('SUM(DealsPayment.emi) AS paid_total'),
            'conditions'=>array('DealsPayment.deal_id'=>$dealId),
            'recursive'=>-1
        ));
        $totalPaid = 0;
        if(isset($paidRow[0]['paid_total']) && $paidRow[0]['paid_total']!==null)
        {
            $totalPaid = round((float)$paidRow[0]['paid_total'],2);
        }
        $dueAmount = round($dealTotalAmount-$totalPaid,2);
        if($dueAmount<0)
        {
            $dueAmount = 0;
        }
        $this->loadModel('PlansPayment');
        $planRows = $this->PlansPayment->find('all',array(
            'fields'=>array('PlansPayment.id','PlansPayment.amount','PlansPayment.date'),
            'conditions'=>array('PlansPayment.deal_id'=>$dealId),
            'order'=>array('PlansPayment.id'=>'asc'),
            'recursive'=>-1
        ));
        $planPaidRows = $this->DealsPayment->find('all',array(
            'fields'=>array('DealsPayment.plans_payment_id','SUM(DealsPayment.emi) AS paid_total'),
            'conditions'=>array('DealsPayment.deal_id'=>$dealId),
            'group'=>array('DealsPayment.plans_payment_id'),
            'recursive'=>-1
        ));
        $paidMap = array();
        foreach($planPaidRows as $paidPlanRow)
        {
            $planId = isset($paidPlanRow['DealsPayment']['plans_payment_id']) ? (int)$paidPlanRow['DealsPayment']['plans_payment_id'] : 0;
            if($planId<=0)
            {
                continue;
            }
            $paidMap[$planId] = isset($paidPlanRow[0]['paid_total']) ? (float)$paidPlanRow[0]['paid_total'] : 0;
        }
        $overdueAmount = 0;
        foreach($planRows as $planRow)
        {
            $planId = (int)$planRow['PlansPayment']['id'];
            $planAmount = isset($planRow['PlansPayment']['amount']) ? (float)$planRow['PlansPayment']['amount'] : 0;
            $paidAmount = isset($paidMap[$planId]) ? (float)$paidMap[$planId] : 0;
            $pendingAmount = round($planAmount-$paidAmount,2);
            $dueDate = isset($planRow['PlansPayment']['date']) ? $planRow['PlansPayment']['date'] : null;
            if($pendingAmount>0 && $dueDate && $dueDate<=$this->currentDate)
            {
                $overdueAmount += $pendingAmount;
            }
        }
        return array(
            'deal_due_amount'=>round($dueAmount,2),
            'deal_paid_amount'=>round($totalPaid,2),
            'deal_overdue_amount'=>round($overdueAmount,2)
        );
    }
    protected function _dealChargesTableReady()
    {
        try
        {
            $rows = $this->DealsPayment->query("SHOW TABLES LIKE 'deal_charges'");
            return !empty($rows);
        }
        catch(Exception $e)
        {
            return false;
        }
    }
    protected function _dealChargeSummaryForDeal($dealId, $dealRow = array())
    {
        $summary = array(
            'charge_count' => 0,
            'charge_total' => 0,
            'base_amount' => 0,
            'grand_total' => 0,
            'charge_names' => ''
        );
        $dealData = isset($dealRow['Deal']) ? (array)$dealRow['Deal'] : array();
        $dealAmount = isset($dealData['amount']) ? (float)$dealData['amount'] : 0;
        $dealDiscount = isset($dealData['discount']) ? (float)$dealData['discount'] : 0;
        $dealGrandTotal = isset($dealData['total_amount']) ? (float)$dealData['total_amount'] : 0;
        if($dealAmount > 0)
        {
            $summary['base_amount'] = round($dealAmount - $dealDiscount, 2);
            if($summary['base_amount'] < 0)
            {
                $summary['base_amount'] = 0;
            }
        }
        if($this->_dealChargesTableReady())
        {
            try
            {
                $sumRows = $this->DealsPayment->query(
                    "SELECT
                        COUNT(*) AS charge_count,
                        COALESCE(SUM(`total_amount`),0) AS charge_total,
                        GROUP_CONCAT(DISTINCT TRIM(`charge_name`) ORDER BY TRIM(`charge_name`) SEPARATOR ', ') AS charge_names
                     FROM `deal_charges`
                     WHERE `deal_id`=".(int)$dealId." AND `status`=1"
                );
                if(!empty($sumRows[0][0]))
                {
                    if(isset($sumRows[0][0]['charge_count']))
                    {
                        $summary['charge_count'] = (int)$sumRows[0][0]['charge_count'];
                    }
                    if(isset($sumRows[0][0]['charge_total']) && $sumRows[0][0]['charge_total'] !== null)
                    {
                        $summary['charge_total'] = round((float)$sumRows[0][0]['charge_total'], 2);
                    }
                    if(isset($sumRows[0][0]['charge_names']) && $sumRows[0][0]['charge_names'] !== null)
                    {
                        $summary['charge_names'] = trim((string)$sumRows[0][0]['charge_names']);
                    }
                }
            }
            catch(Exception $e)
            {
                // Keep ledger page available if charge summary query fails.
            }
        }
        if($summary['base_amount'] <= 0 && $dealGrandTotal > 0)
        {
            $summary['base_amount'] = round($dealGrandTotal - (float)$summary['charge_total'], 2);
            if($summary['base_amount'] < 0)
            {
                $summary['base_amount'] = $dealGrandTotal;
            }
        }
        $summary['grand_total'] = $dealGrandTotal > 0 ? round($dealGrandTotal, 2) : round((float)$summary['base_amount'] + (float)$summary['charge_total'], 2);
        return $summary;
    }
    public function index($id=null)
    {
        if(!$id)
        {
            $this->Session->setFlash(__('Invalid Post'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('controller'=>'Deals','action' => 'index'));            
        }
        if ($this->Session->check('frontUser') && !$this->Session->check('User')) {
            return $this->redirect(array('plugin' => false, 'admin' => false, 'controller' => 'MyPayments', 'action' => 'index', $id));
        }
        if(!$this->_canAccessDeal($id))
        {
            $this->Session->setFlash(__('Access Denied. You do not have permission to view payments for this deal.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('controller'=>'Deals','action' => 'index'));
        }
        $this->_syncExistingReceiptsToArd($id);
        $post = $this->DealsPayment->find('all',array('joins'=>array(array('table'=>'deals','alias'=>'Deal','type'=>'LEFT','conditions'=>array('DealsPayment.deal_id=Deal.id')),
                                                                       array('table'=>'clients','alias'=>'Client','type'=>'LEFT','conditions'=>array('Deal.client_id=Client.id')),
                                                                       array('table'=>'properties','alias'=>'Property','type'=>'LEFT','conditions'=>array('Deal.property_id=Property.id')),
                                                                       array('table'=>'types','alias'=>'Type','type'=>'LEFT','conditions'=>array('Property.type_id=Type.id')),
                                                                       array('table'=>'contracts','alias'=>'Contract','type'=>'LEFT','conditions'=>array('Property.contract_id=Contract.id')),
                                                                       array('table'=>'projects','alias'=>'Project','type'=>'LEFT','conditions'=>array('Property.project_id=Project.id')),
                                                                       array('table'=>'units','alias'=>'Unit','type'=>'LEFT','conditions'=>array('Property.unit_id=Unit.id')),
                                                                       array('table'=>'plans','alias'=>'Plan','type'=>'LEFT','conditions'=>array('Deal.plan_id=Plan.id')),
                                                                       array('table'=>'plans_payments','alias'=>'PlansPayment','type'=>'LEFT','conditions'=>array('DealsPayment.plans_payment_id=PlansPayment.id')),
                                                                       array('table'=>'paymenttypes','alias'=>'Paymenttype','type'=>'LEFT','conditions'=>array('DealsPayment.paymenttype_id=Paymenttype.id'))),
                                                      'fields'=>array('DealsPayment.*','Deal.*','Client.*','Property.*','Type.*','Contract.*','Project.*','Unit.*','Plan.*','PlansPayment.*','Paymenttype.*'),
                                                      'conditions'=>array('Deal.id'=>$id),
                                                      'order'=>array('DealsPayment.id'=>'asc'),
                                                      'recursive'=>-1)
                                          );
        $dealHeaderRow = !empty($post[0]) ? $post[0] : array();
        if(empty($dealHeaderRow))
        {
            // Keep ledger page accessible even when no receipt exists for this deal.
            $this->loadModel('Deal');
            $dealHeaderRow = $this->Deal->find('first', array(
                'joins' => array(
                    array('table' => 'clients', 'alias' => 'Client', 'type' => 'LEFT', 'conditions' => array('Deal.client_id=Client.id')),
                    array('table' => 'properties', 'alias' => 'Property', 'type' => 'LEFT', 'conditions' => array('Deal.property_id=Property.id')),
                    array('table' => 'types', 'alias' => 'Type', 'type' => 'LEFT', 'conditions' => array('Property.type_id=Type.id')),
                    array('table' => 'contracts', 'alias' => 'Contract', 'type' => 'LEFT', 'conditions' => array('Property.contract_id=Contract.id')),
                    array('table' => 'projects', 'alias' => 'Project', 'type' => 'LEFT', 'conditions' => array('Property.project_id=Project.id')),
                    array('table' => 'units', 'alias' => 'Unit', 'type' => 'LEFT', 'conditions' => array('Property.unit_id=Unit.id')),
                    array('table' => 'plans', 'alias' => 'Plan', 'type' => 'LEFT', 'conditions' => array('Deal.plan_id=Plan.id'))
                ),
                'fields' => array('Deal.*', 'Client.*', 'Property.*', 'Type.*', 'Contract.*', 'Project.*', 'Unit.*', 'Plan.*'),
                'conditions' => array('Deal.id' => $id),
                'recursive' => -1
            ));
            if(empty($dealHeaderRow['Deal']['id']))
            {
                $this->Session->setFlash(__('Invalid Post'),'flash',array('alert'=>'danger'));
                return $this->redirect(array('controller'=>'Deals','action' => 'index'));
            }
        }
        $this->loadModel('PlansPayment');
        $planRows = $this->PlansPayment->find('all',array(
            'fields'=>array('PlansPayment.id','PlansPayment.amount','PlansPayment.date'),
            'conditions'=>array('PlansPayment.deal_id'=>$id),
            'order'=>array('PlansPayment.id'=>'asc'),
            'recursive'=>-1
        ));
        $paidRows = $this->DealsPayment->find('all',array(
            'fields'=>array('DealsPayment.plans_payment_id','SUM(DealsPayment.emi) AS paid_total'),
            'conditions'=>array('DealsPayment.deal_id'=>$id),
            'group'=>array('DealsPayment.plans_payment_id'),
            'recursive'=>-1
        ));
        $paidMap = array();
        foreach($paidRows as $paidRow)
        {
            $planId = isset($paidRow['DealsPayment']['plans_payment_id']) ? (int)$paidRow['DealsPayment']['plans_payment_id'] : 0;
            if($planId<=0)
            {
                continue;
            }
            $paidMap[$planId] = isset($paidRow[0]['paid_total']) ? (float)$paidRow[0]['paid_total'] : 0;
        }
        $overdueAmount = 0;
        $overdueCount = 0;
        foreach($planRows as $planRow)
        {
            $planId = (int)$planRow['PlansPayment']['id'];
            $planAmount = isset($planRow['PlansPayment']['amount']) ? (float)$planRow['PlansPayment']['amount'] : 0;
            $paidAmount = isset($paidMap[$planId]) ? (float)$paidMap[$planId] : 0;
            $pendingAmount = round($planAmount - $paidAmount,2);
            $dueDate = isset($planRow['PlansPayment']['date']) ? $planRow['PlansPayment']['date'] : null;
            if($pendingAmount>0 && $dueDate && $dueDate<=$this->currentDate)
            {
                $overdueAmount += $pendingAmount;
                $overdueCount++;
            }
        }
        $overdueAmount = round($overdueAmount,2);
        $allottedUnitNumber = '';
        try
        {
            $this->loadModel('BallotingAllotment');
            $allotmentRow = $this->BallotingAllotment->find('first', array(
                'fields' => array('BallotingAllotment.allotment_no'),
                'conditions' => array(
                    'BallotingAllotment.deal_id' => (int)$id,
                    'BallotingAllotment.status' => 'Allotted'
                ),
                'order' => array('BallotingAllotment.id' => 'desc'),
                'recursive' => -1
            ));
            if (!empty($allotmentRow['BallotingAllotment']['allotment_no']))
            {
                $allottedUnitNumber = trim((string)$allotmentRow['BallotingAllotment']['allotment_no']);
            }
        }
        catch (Exception $e)
        {
            $allottedUnitNumber = '';
        }
        $agencyLogoFile = '';
        $projectId = !empty($dealHeaderRow['Project']['id']) ? (int)$dealHeaderRow['Project']['id'] : 0;
        if($projectId > 0)
        {
            $this->loadModel('User');
            $userFields = array('User.id');
            if($this->User->hasField('photo'))
            {
                $userFields[] = 'User.photo';
            }
            $baseConditions = array(
                'User.project_id' => $projectId,
                'User.photo <>' => ''
            );
            $agencyUserRow = array();
            if($this->User->hasField('ugroup_id'))
            {
                $agencyUserRow = $this->User->find('first', array(
                    'fields' => $userFields,
                    'conditions' => array_merge($baseConditions, array('User.ugroup_id' => 4)),
                    'order' => array('User.id' => 'asc'),
                    'recursive' => -1
                ));
            }
            if(empty($agencyUserRow))
            {
                $agencyUserRow = $this->User->find('first', array(
                    'fields' => $userFields,
                    'conditions' => $baseConditions,
                    'order' => array('User.id' => 'asc'),
                    'recursive' => -1
                ));
            }
            if(!empty($agencyUserRow['User']['photo']))
            {
                $agencyLogoFile = trim((string)$agencyUserRow['User']['photo']);
            }
        }
        $dealChargeSummary = $this->_dealChargeSummaryForDeal($id, $dealHeaderRow);
        $clientLedgerPreview = (!empty($this->request->query['client_preview']) && $this->Session->check('Impersonation.front'));
        $statusAnalysis = $this->_computeDealStatusAnalysisData($id);
        $this->set('dealPayment',$post);
        $this->set('dealId',$id);
        $this->set('overdueAmount',$overdueAmount);
        $this->set('overdueCount',$overdueCount);
        $this->set('allottedUnitNumber',$allottedUnitNumber);
        $this->set('agencyLogoFile',$agencyLogoFile);
        $this->set('agency_logo',$agencyLogoFile);
        $this->set('dealHeaderRow',$dealHeaderRow);
        $this->set('dealChargeSummary',$dealChargeSummary);
        $this->set('clientLedgerPreview',$clientLedgerPreview);
        $this->set('statusAnalysis',$statusAnalysis);

        $this->loadModel('Configuration');
        $cfg = $this->Configuration->findById(1);
        $dealVaultEnabled = !isset($cfg['Configuration']['deal_vault_enabled']) || (int)$cfg['Configuration']['deal_vault_enabled'] === 1;
        $dealAdminOnly = !isset($cfg['Configuration']['deal_features_admin_only']) || (int)$cfg['Configuration']['deal_features_admin_only'] === 1;
        $canManageDocs = true;
        if($dealAdminOnly && !$this->_isSuperAdminSession((array)$this->adminValue) && $this->userType !== 'admin' && !$this->_isCeoRoleContext((array)$this->adminValue))
        {
            $canManageDocs = false;
        }

        $dealDocuments = array();
        if($dealVaultEnabled)
        {
            $this->loadModel('Admin.DealDocument');
            try {
                $dealDocuments = (array)$this->DealDocument->find('all', array(
                    'conditions' => array('DealDocument.deal_id' => (int)$id),
                    'order' => array('DealDocument.id' => 'desc'),
                    'recursive' => -1
                ));
            } catch(Exception $e) {
                $dealDocuments = array();
            }
        }
        $this->set('dealVaultEnabled', $dealVaultEnabled);
        $this->set('canManageDocs', $canManageDocs);
        $this->set('dealDocuments', $dealDocuments);
    }

    protected function _computeDealStatusAnalysisData($dealId)
    {
        $this->loadModel('PlansPayment');
        $this->loadModel('DealsPayment');

        $planRows = $this->PlansPayment->find('all', array(
            'fields' => array('PlansPayment.id', 'PlansPayment.deal_id', 'PlansPayment.name', 'PlansPayment.amount', 'PlansPayment.date'),
            'conditions' => array('PlansPayment.deal_id' => $dealId),
            'order' => array('PlansPayment.date' => 'asc', 'PlansPayment.id' => 'asc'),
            'recursive' => -1
        ));

        $paymentRows = $this->DealsPayment->find('all', array(
            'joins' => array(
                array('table' => 'paymenttypes', 'alias' => 'Paymenttype', 'type' => 'LEFT', 'conditions' => array('DealsPayment.paymenttype_id=Paymenttype.id'))
            ),
            'fields' => array('DealsPayment.*', 'Paymenttype.name'),
            'conditions' => array('DealsPayment.deal_id' => $dealId),
            'order' => array('DealsPayment.payment_date' => 'asc', 'DealsPayment.id' => 'asc'),
            'recursive' => -1
        ));

        $paymentsByPlan = array();
        $unlinkedPayments = array();
        $totalPaidSum = 0;
        $earlyCount = 0;
        $ontimeCount = 0;
        $lateCount = 0;
        $advanceCount = 0;

        foreach($paymentRows as $pRow)
        {
            $p = $pRow['DealsPayment'];
            $pTypeName = !empty($pRow['Paymenttype']['name']) ? $pRow['Paymenttype']['name'] : '';
            $p['payment_type_name'] = $pTypeName;
            $planId = !empty($p['plans_payment_id']) ? (int)$p['plans_payment_id'] : 0;
            $amt = isset($p['emi']) && (float)$p['emi'] > 0 ? (float)$p['emi'] : (isset($p['amount']) ? (float)$p['amount'] : 0);
            $totalPaidSum += $amt;

            if($planId > 0)
            {
                if(!isset($paymentsByPlan[$planId]))
                {
                    $paymentsByPlan[$planId] = array();
                }
                $paymentsByPlan[$planId][] = $p;
            }
            else
            {
                $unlinkedPayments[] = $p;
            }
        }

        $nowDate = date('Y-m-d');
        $nowTs = strtotime($nowDate);

        $schedules = array();
        $totalPlanAmount = 0;
        $totalOverdueBalance = 0;
        $overdueCount = 0;
        $maxDelayDays = 0;

        foreach($planRows as $idx => $plRow)
        {
            $pl = $plRow['PlansPayment'];
            $planId = (int)$pl['id'];
            $planAmount = isset($pl['amount']) ? (float)$pl['amount'] : 0;
            $totalPlanAmount += $planAmount;
            $dueDate = !empty($pl['date']) ? date('Y-m-d', strtotime($pl['date'])) : '';
            $dueTs = !empty($dueDate) ? strtotime($dueDate) : 0;

            $linked = isset($paymentsByPlan[$planId]) ? $paymentsByPlan[$planId] : array();
            $planPaidAmount = 0;
            $linkedReceipts = array();

            foreach($linked as $lp)
            {
                $pAmt = isset($lp['emi']) && (float)$lp['emi'] > 0 ? (float)$lp['emi'] : (isset($lp['amount']) ? (float)$lp['amount'] : 0);
                $planPaidAmount += $pAmt;
                $pDate = !empty($lp['payment_date']) ? date('Y-m-d', strtotime($lp['payment_date'])) : '';
                $pTs = !empty($pDate) ? strtotime($pDate) : 0;

                $timingStatus = 'advance';
                $timingDiffDays = 0;

                if($pTs > 0 && $dueTs > 0)
                {
                    $diff = (int)round(($pTs - $dueTs) / 86400);
                    if($diff < 0)
                    {
                        $timingStatus = 'early';
                        $timingDiffDays = abs($diff);
                        $earlyCount++;
                    }
                    elseif($diff == 0)
                    {
                        $timingStatus = 'ontime';
                        $timingDiffDays = 0;
                        $ontimeCount++;
                    }
                    else
                    {
                        $timingStatus = 'late';
                        $timingDiffDays = $diff;
                        $lateCount++;
                    }
                }
                else
                {
                    $advanceCount++;
                }

                $linkedReceipts[] = array(
                    'id' => (int)$lp['id'],
                    'receipt_no' => !empty($lp['receipt_no']) ? $lp['receipt_no'] : '-',
                    'payment_date' => $pDate,
                    'amount' => $pAmt,
                    'payment_type' => !empty($lp['payment_type_name']) ? $lp['payment_type_name'] : '-',
                    'timing_status' => $timingStatus,
                    'diff_days' => $timingDiffDays,
                    'remarks' => !empty($lp['remarks']) ? $lp['remarks'] : ''
                );
            }

            $pendingBalance = round(max(0, $planAmount - $planPaidAmount), 2);
            $scheduleStatus = 'paid';
            $delayDurationDays = 0;

            if($pendingBalance > 0)
            {
                if($dueTs > 0 && $dueTs < $nowTs)
                {
                    $scheduleStatus = 'overdue';
                    $delayDurationDays = (int)round(($nowTs - $dueTs) / 86400);
                    $totalOverdueBalance += $pendingBalance;
                    $overdueCount++;
                    if($delayDurationDays > $maxDelayDays)
                    {
                        $maxDelayDays = $delayDurationDays;
                    }
                }
                elseif($dueTs > 0 && $dueTs == $nowTs)
                {
                    $scheduleStatus = 'due_today';
                    $delayDurationDays = 0;
                }
                else
                {
                    $scheduleStatus = 'upcoming';
                    $delayDurationDays = $dueTs > 0 ? (int)round(($dueTs - $nowTs) / 86400) : 0;
                }
            }
            elseif($planPaidAmount > 0)
            {
                $scheduleStatus = 'paid';
            }

            $schedules[] = array(
                'id' => $planId,
                'name' => !empty($pl['name']) ? $pl['name'] : __('Installment'),
                'due_date' => $dueDate,
                'amount' => $planAmount,
                'paid_amount' => $planPaidAmount,
                'balance_amount' => $pendingBalance,
                'status' => $scheduleStatus,
                'delay_days' => $delayDurationDays,
                'receipts' => $linkedReceipts
            );
        }

        $unlinkedList = array();
        foreach($unlinkedPayments as $up)
        {
            $pAmt = isset($up['emi']) && (float)$up['emi'] > 0 ? (float)$up['emi'] : (isset($up['amount']) ? (float)$up['amount'] : 0);
            $pDate = !empty($up['payment_date']) ? date('Y-m-d', strtotime($up['payment_date'])) : '';
            $advanceCount++;
            $unlinkedList[] = array(
                'id' => (int)$up['id'],
                'receipt_no' => !empty($up['receipt_no']) ? $up['receipt_no'] : '-',
                'payment_date' => $pDate,
                'amount' => $pAmt,
                'payment_type' => !empty($up['payment_type_name']) ? $up['payment_type_name'] : '-',
                'timing_status' => 'advance',
                'diff_days' => 0,
                'remarks' => !empty($up['remarks']) ? $up['remarks'] : ''
            );
        }

        $totalEvaluatedPaid = $earlyCount + $ontimeCount + $lateCount;
        $complianceScore = 100;
        if($totalEvaluatedPaid > 0)
        {
            $complianceScore = round((($earlyCount + $ontimeCount) / $totalEvaluatedPaid) * 100);
        }

        return array(
            'schedules' => $schedules,
            'unlinked_receipts' => $unlinkedList,
            'kpis' => array(
                'overdue_count' => $overdueCount,
                'overdue_balance' => $totalOverdueBalance,
                'max_delay_days' => $maxDelayDays,
                'early_count' => $earlyCount,
                'ontime_count' => $ontimeCount,
                'late_count' => $lateCount,
                'advance_count' => $advanceCount,
                'total_receipts' => count($paymentRows),
                'compliance_score' => $complianceScore,
                'total_plan_amount' => $totalPlanAmount,
                'total_paid_amount' => $totalPaidSum,
                'total_balance_amount' => round(max(0, $totalPlanAmount - $totalPaidSum), 2)
            )
        );
    }    

    public function letters($dealId=null)
    {
        if(!$dealId)
        {
            $this->Session->setFlash(__('Invalid Post'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('controller'=>'Deals','action' => 'index'));
        }
        if(!$this->_canAccessDeal($dealId))
        {
            $this->Session->setFlash(__('Access Denied.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('controller'=>'Deals','action' => 'index'));
        }
        $this->loadModel('Deal');
        $this->loadModel('Admin.Realestateletter');

        $dealRow = $this->Deal->find('first',array(
            'joins'=>array(
                array('table'=>'clients','alias'=>'Client','type'=>'LEFT','conditions'=>array('Deal.client_id=Client.id')),
                array('table'=>'properties','alias'=>'Property','type'=>'LEFT','conditions'=>array('Deal.property_id=Property.id')),
                array('table'=>'projects','alias'=>'Project','type'=>'LEFT','conditions'=>array('Property.project_id=Project.id'))
            ),
            'fields'=>array(
                'Deal.id','Deal.user_id','Deal.invoice_no','Deal.property_id','Deal.client_id',
                'Client.id','Client.name','Client.phone','Client.mobile',
                'Property.id','Property.name','Property.project_id',
                'Project.id','Project.name'
            ),
            'conditions'=>array('Deal.id'=>$dealId),
            'recursive'=>-1
        ));
        if(empty($dealRow['Deal']['id']))
        {
            $this->Session->setFlash(__('Invalid Post'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('controller'=>'Deals','action' => 'index'));
        }

        $projectId = !empty($dealRow['Property']['project_id']) ? (int)$dealRow['Property']['project_id'] : 0;
        if($this->userType==='AGY' && $projectId>0 && (int)$this->agencyId!==(int)$projectId)
        {
            $this->Session->setFlash(__('Invalid Post'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('controller'=>'Deals','action' => 'index'));
        }
        if($this->userType==='AGT' && !empty($dealRow['Deal']['user_id']) && (int)$dealRow['Deal']['user_id']!==(int)$this->luserId)
        {
            $this->Session->setFlash(__('Invalid Post'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('controller'=>'Deals','action' => 'index'));
        }

        if($this->request->is('ajax'))
        {
            $this->layout = null;
        }

        $letterTypeArr = array(
            'RLT_WELCOME' => __('Welcome Letter'),
            'RLT_BOOKING' => __('Booking Letter'),
            'RLT_CHARGE' => __('Deal Charges Notice'),
            'RLT_DEMARCATION' => __('Demarcation Letter'),
            'RLT_DESIGN_MAP_APPROVAL' => __('Design / Map Approval Letter'),
            'RLT_NOC' => __('NOC Letter'),
            'RLT_ALLOT' => __('Allotment Letter'),
            'RLT_DEMAND' => __('Payment Demand Notice'),
            'RLT_RECEIPT' => __('Payment Acknowledgement'),
            'RLT_POSSESSION' => __('Possession Letter'),
            'RLT_TRANSFER' => __('Transfer / Mutation Letter'),
            'RLT_CANCEL' => __('Cancellation Letter')
        );
        $autoLetterTypeArr = array(
            'RLT_WELCOME' => 1,
            'RLT_BOOKING' => 1,
            'RLT_CHARGE' => 1
        );

        $letters = array();
        try
        {
            $letters = $this->Realestateletter->find('all',array(
                'fields'=>array('Realestateletter.*','Template.name'),
                'joins'=>array(
                    array('table'=>'templates','alias'=>'Template','type'=>'LEFT','conditions'=>array('Template.id=Realestateletter.template_id'))
                ),
                'conditions'=>array('Realestateletter.deal_id'=>(int)$dealId),
                'order'=>array('Realestateletter.id'=>'desc'),
                'recursive'=>-1
            ));
        }
        catch (Exception $e)
        {
            $letters = array();
        }

        $this->set('dealId',(int)$dealId);
        $this->set('dealMeta',$dealRow);
        $this->set('dealLetters',$letters);
        $this->set('letterTypeArr',$letterTypeArr);
        $this->set('autoLetterTypeArr',$autoLetterTypeArr);
    }

    public function communication($dealId=null)
    {
        if(!$dealId)
        {
            $this->Session->setFlash(__('Invalid Post'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('controller'=>'Deals','action' => 'index'));
        }
        if(!$this->_canAccessDeal($dealId))
        {
            $this->Session->setFlash(__('Access Denied.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('controller'=>'Deals','action' => 'index'));
        }
        $this->loadModel('Deal');
        $this->loadModel('Client');
        $this->loadModel('Property');
        $this->loadModel('Project');
        $this->loadModel('Admin.DealCommunication');

        $dealRow = $this->Deal->find('first',array(
            'joins'=>array(
                array('table'=>'clients','alias'=>'Client','type'=>'LEFT','conditions'=>array('Deal.client_id=Client.id')),
                array('table'=>'properties','alias'=>'Property','type'=>'LEFT','conditions'=>array('Deal.property_id=Property.id')),
                array('table'=>'projects','alias'=>'Project','type'=>'LEFT','conditions'=>array('Property.project_id=Project.id'))
            ),
            'fields'=>array(
                'Deal.id','Deal.invoice_no','Deal.property_id','Deal.client_id',
                'Client.id','Client.name','Client.phone','Client.email',
                'Property.id','Property.name','Property.project_id',
                'Project.id','Project.name'
            ),
            'conditions'=>array('Deal.id'=>$dealId),
            'recursive'=>-1
        ));
        if(empty($dealRow['Deal']['id']))
        {
            $this->Session->setFlash(__('Invalid Post'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('controller'=>'Deals','action' => 'index'));
        }

        $projectId = !empty($dealRow['Property']['project_id']) ? (int)$dealRow['Property']['project_id'] : 0;
        $clientId = !empty($dealRow['Client']['id']) ? (int)$dealRow['Client']['id'] : 0;
        $contactNo = !empty($dealRow['Client']['phone']) ? trim($dealRow['Client']['phone']) : '';

        if($this->request->is('post'))
        {
            $entryType = strtoupper(trim((string)Hash::get($this->request->data,'DealCommunication.entry_type','')));
            $subject = trim((string)Hash::get($this->request->data,'DealCommunication.subject',''));
            $message = trim((string)Hash::get($this->request->data,'DealCommunication.message',''));
            $callStatus = trim((string)Hash::get($this->request->data,'DealCommunication.call_status',''));
            $followupRaw = trim((string)Hash::get($this->request->data,'DealCommunication.followup_date',''));
            $followupDate = null;
            if($followupRaw !== '')
            {
                $ts = strtotime($followupRaw);
                if($ts !== false)
                {
                    $followupDate = date('Y-m-d',$ts);
                }
            }
            if($entryType !== 'SMS' && $entryType !== 'CALL')
            {
                $entryType = 'CALL';
            }
            if($entryType === 'SMS')
            {
                if($message === '')
                {
                    $this->Session->setFlash(__('Please enter SMS message.'),'flash',array('alert'=>'danger'));
                    return $this->redirect(array('action'=>'communication',$dealId));
                }
                $smsStatus = __('Not Sent');
                if($contactNo !== '')
                {
                    if($this->smsNotification)
                    {
                        try
                        {
                            $this->CustomFunction->sendSms($contactNo,$message,$this->smsSettingArr);
                            $smsStatus = __('Sent');
                        }
                        catch(Exception $e)
                        {
                            $smsStatus = __('Send Failed');
                        }
                    }
                    else
                    {
                        $smsStatus = __('SMS Disabled');
                    }
                }
                else
                {
                    $smsStatus = __('No Contact Number');
                }
                if($subject === '')
                {
                    $subject = __('Custom SMS');
                }
                $callStatus = $smsStatus;
            }
            else
            {
                if($message === '')
                {
                    $this->Session->setFlash(__('Please enter call details/notes.'),'flash',array('alert'=>'danger'));
                    return $this->redirect(array('action'=>'communication',$dealId));
                }
                if($subject === '')
                {
                    $subject = __('Recovery Call');
                }
            }
            $saveData = array(
                'deal_id'=>$dealId,
                'project_id'=>$projectId,
                'client_id'=>$clientId,
                'contact_no'=>$contactNo,
                'entry_type'=>$entryType,
                'subject'=>$subject,
                'message'=>$message,
                'call_status'=>$callStatus,
                'followup_date'=>$followupDate,
                'created_by'=>(int)$this->luserId
            );
            $this->DealCommunication->create();
            if($this->DealCommunication->save(array('DealCommunication'=>$saveData)))
            {
                $this->Session->setFlash(__('Communication entry saved successfully.'),'flash',array('alert'=>'success'));
            }
            else
            {
                $this->Session->setFlash(__('Unable to save communication entry.'),'flash',array('alert'=>'danger'));
            }
            return $this->redirect(array('action'=>'communication',$dealId));
        }

        $history = $this->DealCommunication->find('all',array(
            'conditions'=>array('DealCommunication.deal_id'=>$dealId),
            'order'=>array('DealCommunication.id'=>'desc'),
            'recursive'=>-1
        ));
        $this->set('dealId',$dealId);
        $this->set('dealMeta',$dealRow);
        $this->set('communicationHistory',$history);
    }

    public function documents($dealId=null)
    {
        if(!$dealId)
        {
            $this->Session->setFlash(__('Invalid Post'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('controller'=>'Deals','action' => 'index'));
        }
        if(!$this->_canAccessDeal($dealId))
        {
            $this->Session->setFlash(__('Access Denied.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('controller'=>'Deals','action' => 'index'));
        }
        $this->_ensureDealDocumentsTable();
        $this->loadModel('Deal');
        $this->loadModel('Admin.DealDocument');
        $this->DealDocument->cacheSources = false;
        $dealRow = $this->Deal->find('first',array(
            'joins'=>array(
                array('table'=>'clients','alias'=>'Client','type'=>'LEFT','conditions'=>array('Deal.client_id=Client.id')),
                array('table'=>'properties','alias'=>'Property','type'=>'LEFT','conditions'=>array('Deal.property_id=Property.id')),
                array('table'=>'projects','alias'=>'Project','type'=>'LEFT','conditions'=>array('Property.project_id=Project.id'))
            ),
            'fields'=>array(
                'Deal.id','Deal.user_id','Deal.invoice_no','Deal.property_id','Deal.client_id',
                'Client.id','Client.name','Client.phone',
                'Property.id','Property.name','Property.project_id',
                'Project.id','Project.name'
            ),
            'conditions'=>array('Deal.id'=>$dealId),
            'recursive'=>-1
        ));
        if(empty($dealRow['Deal']['id']))
        {
            $this->Session->setFlash(__('Invalid Post'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('controller'=>'Deals','action' => 'index'));
        }
        $projectId = !empty($dealRow['Property']['project_id']) ? (int)$dealRow['Property']['project_id'] : 0;
        if($this->userType==='AGY' && $projectId>0 && (int)$this->agencyId!==$projectId)
        {
            $this->Session->setFlash(__('Invalid Post'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('controller'=>'Deals','action' => 'index'));
        }
        if($this->userType==='AGT' && !empty($dealRow['Deal']['user_id']) && (int)$dealRow['Deal']['user_id']!==(int)$this->luserId)
        {
            $this->Session->setFlash(__('Invalid Post'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('controller'=>'Deals','action' => 'index'));
        }

        $this->loadModel('Configuration');
        $cfg = $this->Configuration->findById(1);
        $dealVaultEnabled = !isset($cfg['Configuration']['deal_vault_enabled']) || (int)$cfg['Configuration']['deal_vault_enabled'] === 1;
        $dealAdminOnly = !isset($cfg['Configuration']['deal_features_admin_only']) || (int)$cfg['Configuration']['deal_features_admin_only'] === 1;
        if(!$dealVaultEnabled)
        {
            $this->Session->setFlash(__('Deal Document Vault is disabled in system configurations.'),'flash',array('alert'=>'warning'));
            return $this->redirect(array('action'=>'index',$dealId));
        }
        $isAdmin = ($this->_isSuperAdminSession((array)$this->adminValue) || $this->userType === 'admin' || $this->_isCeoRoleContext((array)$this->adminValue));
        if($dealAdminOnly && !$isAdmin)
        {
            $this->Session->setFlash(__('Document Vault access is restricted to Administrators.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action'=>'index',$dealId));
        }

        if($this->request->is('ajax'))
        {
            $this->layout = null;
        }

        $documentTypeOptions = array(
            'CNIC' => __('CNIC / Passport'),
            'Booking Form' => __('Signed Booking Form'),
            'Deposit Slip' => __('Bank Deposit Slip / Cheque'),
            'Possession Certificate' => __('Possession Certificate'),
            'Allotment Letter' => __('Allotment Letter'),
            'Client Photo' => __('Client Photo'),
            'Deal Document' => __('Deal Document'),
            'Agreement' => __('Agreement Deed'),
            'Nominee' => __('Nominee Document'),
            'Others' => __('Others')
        );

        if($this->request->is('post'))
        {
            $docType = trim((string)Hash::get($this->request->data, 'DealDocument.document_type', ''));
            $docDate = $this->_normalizeDealDocumentDate(Hash::get($this->request->data, 'DealDocument.document_date', ''));
            $upload = Hash::get($this->request->data, 'DealDocument.upload_file', array());
            if($docType === '' || !isset($documentTypeOptions[$docType]))
            {
                $docType = 'Others';
            }
            if($docDate === null)
            {
                $docDate = date('Y-m-d');
            }
            $uploadResult = $this->_saveDealDocumentUpload($upload, 'dealsdocuments');
            if(!$uploadResult['ok'])
            {
                $this->Session->setFlash($uploadResult['message'],'flash',array('alert'=>'danger'));
            }
            else
            {
                $saveData = array(
                    'deal_id' => (int)$dealId,
                    'project_id' => (int)$projectId,
                    'client_id' => !empty($dealRow['Client']['id']) ? (int)$dealRow['Client']['id'] : 0,
                    'document_type' => $docType,
                    'document_date' => $docDate,
                    'photo' => $uploadResult['file'],
                    'dir' => 'dealsdocuments',
                    'mime_type' => $uploadResult['mime'],
                    'is_image' => (int)$uploadResult['is_image'],
                    'created_by' => (int)$this->luserId
                );
                $this->DealDocument->create();
                if($this->DealDocument->save(array('DealDocument'=>$saveData)))
                {
                    $this->Session->setFlash(__('Document has been uploaded successfully.'),'flash',array('alert'=>'success'));
                }
                else
                {
                    if(!empty($uploadResult['file']))
                    {
                        $this->CustomFunction->fileDelete($uploadResult['file'],'dealsdocuments',false);
                    }
                    $this->Session->setFlash(__('Unable to save document record.'),'flash',array('alert'=>'danger'));
                }
            }
            if(!$this->request->is('ajax'))
            {
                return $this->redirect(array('action'=>'documents',$dealId));
            }
        }

        $documents = array();
        try
        {
            $documents = $this->DealDocument->find('all',array(
                'conditions'=>array('DealDocument.deal_id'=>(int)$dealId),
                'order'=>array('DealDocument.id'=>'desc'),
                'recursive'=>-1
            ));
        }
        catch (Exception $e)
        {
            $this->_ensureDealDocumentsTable();
            try
            {
                if(function_exists('clearstatcache'))
                {
                    clearstatcache();
                }
                $documents = $this->DealDocument->find('all',array(
                    'conditions'=>array('DealDocument.deal_id'=>(int)$dealId),
                    'order'=>array('DealDocument.id'=>'desc'),
                    'recursive'=>-1
                ));
            }
            catch (Exception $inner)
            {
                $this->Session->setFlash(__('Deal documents table is not available. Please check DB create permission.'),'flash',array('alert'=>'danger'));
                $documents = array();
            }
        }

        $this->set('dealId',(int)$dealId);
        $this->set('dealMeta',$dealRow);
        $this->set('dealDocuments',$documents);
        $this->set('documentTypeOptions',$documentTypeOptions);
    }

    public function deletedocument($dealId=null, $id=null)
    {
        $dealId = (int)$dealId;
        $id = (int)$id;
        $isAjax = $this->request->is('ajax');
        if($isAjax)
        {
            $this->autoRender = false;
            $this->response->type('json');
        }
        if($dealId<=0 || $id<=0 || !$this->_canAccessDeal($dealId))
        {
            if($isAjax)
            {
                $this->response->body(json_encode(array('ok'=>false,'message'=>__('Invalid request or access denied.'))));
                return;
            }
            $this->Session->setFlash(__('Access Denied'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('controller'=>'Deals','action' => 'index'));
        }
        $this->loadModel('Deal');
        $this->loadModel('Admin.DealDocument');
        $dealRow = $this->Deal->find('first',array(
            'joins'=>array(
                array('table'=>'properties','alias'=>'Property','type'=>'LEFT','conditions'=>array('Deal.property_id=Property.id'))
            ),
            'fields'=>array('Deal.id','Deal.user_id','Property.project_id'),
            'conditions'=>array('Deal.id'=>$dealId),
            'recursive'=>-1
        ));
        if(empty($dealRow['Deal']['id']))
        {
            if($isAjax)
            {
                $this->response->body(json_encode(array('ok'=>false,'message'=>__('Invalid deal.'))));
                return;
            }
            $this->Session->setFlash(__('Invalid Post'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('controller'=>'Deals','action' => 'index'));
        }
        $projectId = !empty($dealRow['Property']['project_id']) ? (int)$dealRow['Property']['project_id'] : 0;
        if($this->userType==='AGY' && $projectId>0 && (int)$this->agencyId!==$projectId)
        {
            if($isAjax)
            {
                $this->response->body(json_encode(array('ok'=>false,'message'=>__('Access denied.'))));
                return;
            }
            $this->Session->setFlash(__('Invalid Post'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('controller'=>'Deals','action' => 'index'));
        }
        if($this->userType==='AGT' && !empty($dealRow['Deal']['user_id']) && (int)$dealRow['Deal']['user_id']!==(int)$this->luserId)
        {
            if($isAjax)
            {
                $this->response->body(json_encode(array('ok'=>false,'message'=>__('Access denied.'))));
                return;
            }
            $this->Session->setFlash(__('Invalid Post'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('controller'=>'Deals','action' => 'index'));
        }
        $docRow = $this->DealDocument->find('first',array(
            'conditions'=>array(
                'DealDocument.id'=>$id,
                'DealDocument.deal_id'=>$dealId
            ),
            'recursive'=>-1
        ));
        if(empty($docRow['DealDocument']['id']))
        {
            if($isAjax)
            {
                $this->response->body(json_encode(array('ok'=>false,'message'=>__('Document not found.'))));
                return;
            }
            $this->Session->setFlash(__('Document not found.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action'=>'documents',$dealId));
        }
        $fileName = !empty($docRow['DealDocument']['photo']) ? $docRow['DealDocument']['photo'] : '';
        $dirName = !empty($docRow['DealDocument']['dir']) ? $docRow['DealDocument']['dir'] : 'dealsdocuments';
        if($this->DealDocument->delete($id))
        {
            if($fileName!=='')
            {
                $this->CustomFunction->fileDelete($fileName,$dirName,false);
            }
            if($isAjax)
            {
                $this->response->body(json_encode(array('ok'=>true,'message'=>__('Document deleted successfully.'))));
                return;
            }
            $this->Session->setFlash(__('Document deleted successfully.'),'flash',array('alert'=>'success'));
        }
        else
        {
            if($isAjax)
            {
                $this->response->body(json_encode(array('ok'=>false,'message'=>__('Unable to delete document.'))));
                return;
            }
            $this->Session->setFlash(__('Unable to delete document.'),'flash',array('alert'=>'danger'));
        }
        return $this->redirect(array('action'=>'documents',$dealId));
    }

    protected function _ensureRentAdjustmentRentalSchema()
    {
        $this->loadModel('Admin.DealRentalReceipt');
        try
        {
            $columns = $this->DealRentalReceipt->query("SHOW COLUMNS FROM `deal_rental_receipts` LIKE 'deal_payment_receipt_no'");
            if(empty($columns))
            {
                $this->DealRentalReceipt->query("ALTER TABLE `deal_rental_receipts` ADD COLUMN `deal_payment_receipt_no` varchar(60) DEFAULT NULL AFTER `payment_mode`");
            }
        }
        catch(Exception $e)
        {
            // Column already exists.
        }
        try
        {
            $indexes = $this->DealRentalReceipt->query("SHOW INDEX FROM `deal_rental_receipts` WHERE `Key_name`='deal_payment_rental_adjustment'");
            if(empty($indexes))
            {
                $this->DealRentalReceipt->query("ALTER TABLE `deal_rental_receipts` ADD UNIQUE KEY `deal_payment_rental_adjustment` (`deal_id`,`deal_payment_receipt_no`)");
            }
        }
        catch(Exception $e)
        {
            // Index already exists.
        }
        try
        {
            $this->DealRentalReceipt->cacheSources = false;
            $this->DealRentalReceipt->schema(true);
        }
        catch(Exception $e)
        {
        }
        return $this->DealRentalReceipt->hasField('deal_payment_receipt_no');
    }

    protected function _isRentAdjustmentPaymentType($paymentTypeId)
    {
        $this->loadModel('Paymenttype');
        $name = (string)$this->Paymenttype->field('name', array('Paymenttype.id' => (int)$paymentTypeId));
        return strtolower(trim($name)) === 'rent adjustment';
    }

    protected function _nextRentAdjustmentReceiptNo($projectId)
    {
        $projectId = (int)$projectId;
        if($projectId <= 0)
        {
            return '';
        }
        $this->loadModel('Admin.Ardsetting');
        $db = $this->Ardsetting->getDataSource();
        try
        {
            $this->Ardsetting->query("INSERT IGNORE INTO `ardaccounts_settings` (`project_id`,`rental_receipt_prefix`,`rental_receipt_next_no`,`rental_agreement_prefix`,`rental_agreement_next_no`,`created`,`modified`) VALUES (".$projectId.",'RR-',1,'RA-',1,NOW(),NOW())");
            $rows = $this->Ardsetting->query("SELECT `id`,`rental_receipt_prefix`,`rental_receipt_next_no` FROM `ardaccounts_settings` WHERE `project_id`=".$projectId." LIMIT 1 FOR UPDATE");
            $setting = !empty($rows[0]['ardaccounts_settings']) ? $rows[0]['ardaccounts_settings'] : (!empty($rows[0][0]) ? $rows[0][0] : array());
            if(empty($setting['id']))
            {
                return '';
            }
            $prefix = trim((string)$setting['rental_receipt_prefix']);
            $prefix = $prefix !== '' ? substr($prefix, 0, 20) : 'RR-';
            $nextNo = max(1, (int)$setting['rental_receipt_next_no']);
            $receiptNo = $prefix.str_pad((string)$nextNo, 5, '0', STR_PAD_LEFT);
            while($this->DealRentalReceipt->find('count', array('conditions' => array('DealRentalReceipt.receipt_no' => $receiptNo), 'recursive' => -1)) > 0)
            {
                $nextNo++;
                $receiptNo = $prefix.str_pad((string)$nextNo, 5, '0', STR_PAD_LEFT);
            }
            $this->Ardsetting->query("UPDATE `ardaccounts_settings` SET `rental_receipt_prefix`=".$db->value($prefix, 'string').",`rental_receipt_next_no`=".($nextNo + 1).",`modified`=NOW() WHERE `id`=".(int)$setting['id']." LIMIT 1");
            return $receiptNo;
        }
        catch(Exception $e)
        {
            CakeLog::write('error', 'Rent adjustment receipt numbering failed: '.$e->getMessage());
            return '';
        }
    }

    protected function _deleteLinkedRentAdjustmentReceipt($receipt)
    {
        if(empty($receipt['DealRentalReceipt']['id']))
        {
            return true;
        }
        $row = $receipt['DealRentalReceipt'];
        $receiptId = (int)$row['id'];
        $rentalId = (int)$row['deal_rental_id'];
        $this->loadModel('Admin.DealRentalReceiptAllocation');
        if(!empty($row['voucher_transaction_id']) && !$this->_ardVoidAutoVoucherBySource((int)$row['project_id'], 'rental_payment', $receiptId, 'Deal rent adjustment reversed', true))
        {
            return false;
        }
        $this->DealRentalReceiptAllocation->deleteAll(array('DealRentalReceiptAllocation.receipt_id' => $receiptId), false);
        if(!$this->DealRentalReceipt->delete($receiptId, false))
        {
            return false;
        }
        return $this->_rebuildRentAdjustmentAllocations($rentalId);
    }

    protected function _syncRentAdjustmentForDealReceipt($dealId, $dealReceiptNo)
    {
        $dealId = (int)$dealId;
        $dealReceiptNo = trim((string)$dealReceiptNo);
        if($dealId <= 0 || $dealReceiptNo === '' || !$this->_ensureRentAdjustmentRentalSchema())
        {
            return false;
        }
        $this->loadModel('Admin.DealRental');
        $this->loadModel('Admin.DealRentalReceipt');
        $this->loadModel('Admin.DealRentalSchedule');
        $this->loadModel('Admin.Ardledger');
        $groupRows = $this->DealsPayment->find('all', array(
            'conditions' => array('DealsPayment.deal_id' => $dealId, 'DealsPayment.receipt_no' => $dealReceiptNo),
            'order' => array('DealsPayment.id' => 'asc'),
            'recursive' => -1
        ));
        $linkedReceipt = $this->DealRentalReceipt->find('first', array(
            'conditions' => array('DealRentalReceipt.deal_id' => $dealId, 'DealRentalReceipt.deal_payment_receipt_no' => $dealReceiptNo),
            'recursive' => -1
        ));
        if(empty($groupRows))
        {
            return $this->_deleteLinkedRentAdjustmentReceipt($linkedReceipt);
        }
        $primary = $groupRows[0]['DealsPayment'];
        if(!$this->_isRentAdjustmentPaymentType((int)$primary['paymenttype_id']))
        {
            return $this->_deleteLinkedRentAdjustmentReceipt($linkedReceipt);
        }
        $rental = $this->DealRental->find('first', array(
            'conditions' => array('DealRental.deal_id' => $dealId, 'DealRental.status' => 'Active'),
            'order' => array('DealRental.id' => 'desc'),
            'recursive' => -1
        ));
        if(empty($rental['DealRental']['id']))
        {
            return $this->_deleteLinkedRentAdjustmentReceipt($linkedReceipt);
        }
        $r = $rental['DealRental'];
        $rentAmount = 0.0;
        $sourceLedgerId = 0;
        foreach($groupRows as $groupRow)
        {
            $payment = $groupRow['DealsPayment'];
            $rentAmount += isset($payment['emi']) ? (float)$payment['emi'] : 0;
            if($sourceLedgerId <= 0 && !empty($payment['ard_ledger_id']))
            {
                $sourceLedgerId = (int)$payment['ard_ledger_id'];
            }
        }
        $rentAmount = round($rentAmount, 2);
        if($rentAmount <= 0)
        {
            return false;
        }
        $sourceLedger = $this->Ardledger->find('first', array(
            'conditions' => array('Ardledger.id' => $sourceLedgerId, 'Ardledger.project_id' => (int)$r['project_id'], 'Ardledger.type' => 'bank', 'Ardledger.is_trashed' => 0),
            'recursive' => -1
        ));
        if(empty($sourceLedger['Ardledger']['id']))
        {
            return false;
        }
        $scheduleConditions = array('DealRentalSchedule.deal_rental_id' => (int)$r['id']);
        if(!empty($linkedReceipt['DealRentalReceipt']['rent_month']))
        {
            $scheduleConditions['DealRentalSchedule.rent_month'] = $linkedReceipt['DealRentalReceipt']['rent_month'];
        }
        else
        {
            $scheduleConditions['DealRentalSchedule.balance_amount >'] = 0.009;
        }
        $schedule = $this->DealRentalSchedule->find('first', array(
            'conditions' => $scheduleConditions,
            'order' => array('DealRentalSchedule.due_date' => 'asc', 'DealRentalSchedule.id' => 'asc'),
            'recursive' => -1
        ));
        if(empty($schedule['DealRentalSchedule']['id']))
        {
            $schedule = $this->DealRentalSchedule->find('first', array(
                'conditions' => array('DealRentalSchedule.deal_rental_id' => (int)$r['id']),
                'order' => array('DealRentalSchedule.due_date' => 'asc', 'DealRentalSchedule.id' => 'asc'),
                'recursive' => -1
            ));
        }
        if(empty($schedule['DealRentalSchedule']['id']))
        {
            return false;
        }
        $receiptNo = !empty($linkedReceipt['DealRentalReceipt']['receipt_no']) ? $linkedReceipt['DealRentalReceipt']['receipt_no'] : $this->_nextRentAdjustmentReceiptNo((int)$r['project_id']);
        if($receiptNo === '')
        {
            return false;
        }
        $receiptId = !empty($linkedReceipt['DealRentalReceipt']['id']) ? (int)$linkedReceipt['DealRentalReceipt']['id'] : 0;
        $receiptDate = !empty($primary['payment_date']) ? date('Y-m-d', strtotime($primary['payment_date'])) : date('Y-m-d');
        $receiptData = array('DealRentalReceipt' => array(
            'id' => $receiptId > 0 ? $receiptId : null,
            'deal_rental_id' => (int)$r['id'],
            'deal_id' => $dealId,
            'client_id' => (int)$r['client_id'],
            'project_id' => (int)$r['project_id'],
            'receipt_no' => $receiptNo,
            'rent_month' => $schedule['DealRentalSchedule']['rent_month'],
            'receipt_date' => $receiptDate,
            'rent_amount' => $rentAmount,
            'expense_amount' => 0,
            'net_amount' => $rentAmount,
            'payment_mode' => substr((string)$sourceLedger['Ardledger']['name'], 0, 40),
            'deal_payment_receipt_no' => $dealReceiptNo,
            'source_ledger_id' => $sourceLedgerId,
            'voucher_transaction_id' => !empty($linkedReceipt['DealRentalReceipt']['voucher_transaction_id']) ? (int)$linkedReceipt['DealRentalReceipt']['voucher_transaction_id'] : 0,
            'remarks' => 'Auto rental payment from deal Rent Adjustment '.$dealReceiptNo,
            'created_by' => (int)$this->luserId
        ));
        if($receiptId <= 0)
        {
            $this->DealRentalReceipt->create();
        }
        if(!$this->DealRentalReceipt->save($receiptData, false))
        {
            return false;
        }
        $receiptId = (int)$this->DealRentalReceipt->id;
        $expenseLedgerId = $this->_ardEnsureRentalPaymentLedger((int)$r['project_id']);
        $voucherId = $this->_ardPostAutoVoucher(
            (int)$r['project_id'],
            $sourceLedgerId,
            'expense',
            $rentAmount,
            $receiptNo,
            $receiptDate,
            'Rental payment adjustment against deal receipt '.$dealReceiptNo,
            array(
                'expense_ledger_id' => $expenseLedgerId,
                'source_type' => 'rental_payment',
                'source_id' => $receiptId,
                'existing_id' => !empty($receiptData['DealRentalReceipt']['voucher_transaction_id']) ? (int)$receiptData['DealRentalReceipt']['voucher_transaction_id'] : 0,
                'status' => 'Posted',
                'allow_source_update' => true,
                'skip_schema_bootstrap' => true
            )
        );
        if($voucherId <= 0 || !$this->DealRentalReceipt->updateAll(array('DealRentalReceipt.voucher_transaction_id' => $voucherId), array('DealRentalReceipt.id' => $receiptId)))
        {
            return false;
        }
        return $this->_rebuildRentAdjustmentAllocations((int)$r['id']);
    }

    protected function _rebuildRentAdjustmentAllocations($rentalId)
    {
        $rentalId = (int)$rentalId;
        if($rentalId <= 0)
        {
            return true;
        }
        $this->loadModel('Admin.DealRentalSchedule');
        $this->loadModel('Admin.DealRentalReceipt');
        $this->loadModel('Admin.DealRentalReceiptAllocation');
        $schedules = $this->DealRentalSchedule->find('all', array('conditions' => array('DealRentalSchedule.deal_rental_id' => $rentalId), 'order' => array('DealRentalSchedule.due_date' => 'asc', 'DealRentalSchedule.id' => 'asc'), 'recursive' => -1));
        if(empty($schedules))
        {
            return false;
        }
        $receipts = $this->DealRentalReceipt->find('all', array('conditions' => array('DealRentalReceipt.deal_rental_id' => $rentalId), 'order' => array('DealRentalReceipt.receipt_date' => 'asc', 'DealRentalReceipt.id' => 'asc'), 'recursive' => -1));
        $this->DealRentalReceiptAllocation->deleteAll(array('DealRentalReceiptAllocation.deal_rental_id' => $rentalId), false);
        $state = array();
        foreach($schedules as $scheduleRow)
        {
            $schedule = $scheduleRow['DealRentalSchedule'];
            $schedule['paid_amount'] = 0.0;
            $state[] = $schedule;
        }
        foreach($receipts as $receiptRow)
        {
            $receipt = $receiptRow['DealRentalReceipt'];
            $remaining = round(max(0, (float)$receipt['rent_amount']), 2);
            $startIndex = 0;
            $anchorMonth = !empty($receipt['rent_month']) ? substr((string)$receipt['rent_month'], 0, 7) : '';
            if($anchorMonth !== '')
            {
                foreach($state as $index => $schedule)
                {
                    if($schedule['rent_month'] === $anchorMonth)
                    {
                        $startIndex = $index;
                        break;
                    }
                }
            }
            for($index = $startIndex; $index < count($state) && $remaining > 0.009; $index++)
            {
                $balance = round(max(0, (float)$state[$index]['rent_amount'] - (float)$state[$index]['paid_amount']), 2);
                if($balance <= 0.009)
                {
                    continue;
                }
                $allocated = round(min($remaining, $balance), 2);
                $this->DealRentalReceiptAllocation->create();
                if(!$this->DealRentalReceiptAllocation->save(array('DealRentalReceiptAllocation' => array('receipt_id' => (int)$receipt['id'], 'schedule_id' => (int)$state[$index]['id'], 'deal_rental_id' => $rentalId, 'allocated_amount' => $allocated)), false))
                {
                    return false;
                }
                $state[$index]['paid_amount'] = round((float)$state[$index]['paid_amount'] + $allocated, 2);
                $remaining = round($remaining - $allocated, 2);
            }
        }
        $today = date('Y-m-d');
        foreach($state as $schedule)
        {
            $rentAmount = round((float)$schedule['rent_amount'], 2);
            $paidAmount = round(min($rentAmount, (float)$schedule['paid_amount']), 2);
            $balanceAmount = round(max(0, $rentAmount - $paidAmount), 2);
            if($balanceAmount <= 0.009) { $status = 'Paid'; }
            elseif($paidAmount > 0.009) { $status = 'Partially Paid'; }
            elseif(!empty($schedule['due_date']) && $schedule['due_date'] < $today) { $status = 'Overdue'; }
            elseif(!empty($schedule['due_date']) && $schedule['due_date'] === $today) { $status = 'Due'; }
            else { $status = 'Upcoming'; }
            if(!$this->DealRentalSchedule->save(array('DealRentalSchedule' => array('id' => (int)$schedule['id'], 'paid_amount' => $paidAmount, 'balance_amount' => $balanceAmount, 'status' => $status)), false))
            {
                return false;
            }
        }
        return true;
    }

    public function add($dealId=null)
    {
        if($dealId && !$this->_canAccessDeal($dealId))
        {
            $this->Session->setFlash(__('Access Denied. You do not have permission to add payments for this deal.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('controller'=>'Deals','action' => 'index'));
        }
        $this->loadModel('Property');
        $this->loadModel('Paymenttype');
        $this->loadModel('Deal');
        $this->loadModel('Client');
        $this->loadModel('Tax');
        if($dealId && $this->Deal->hasField('deal_status'))
        {
            $dealControl = $this->Deal->find('first',array(
                'fields'=>array('Deal.id','Deal.deal_status','Deal.deal_status_reason'),
                'conditions'=>array('Deal.id'=>$dealId)
            ));
            if(!empty($dealControl))
            {
                $dealStatus = isset($dealControl['Deal']['deal_status']) && strlen($dealControl['Deal']['deal_status'])>0 ? $dealControl['Deal']['deal_status'] : 'Active';
                if(in_array($dealStatus,$this->_dealPaymentBlockedStatuses))
                {
                    $reason = '';
                    if(isset($dealControl['Deal']['deal_status_reason']) && strlen(trim($dealControl['Deal']['deal_status_reason']))>0)
                    {
                        $reason = trim($dealControl['Deal']['deal_status_reason']);
                    }
                    $message = __('New payment is blocked for this deal because status is').' '.$dealStatus.'.';
                    if(strlen($reason)>0)
                    {
                        $message .= ' '.__('Reason').': '.$reason;
                    }
                    $this->Session->setFlash($message,'flash',array('alert'=>'danger'));
                    return $this->redirect(array('controller'=>'deals_payments','action' => 'index',$dealId));
                }
            }
        }
        $this->set('tax',$this->Tax->find('list',array('fields'=>array('id','percent','name',))));
        $paymentTypeList = $this->Paymenttype->find('list',array('fields'=>array('id','name')));
        $this->set('paymentType',$paymentTypeList);
        $this->set('date',CakeTime::format($this->dtFormat,CakeTime::convert(time(),$this->siteTimezone)));
        $paymentArr=$this->Deal->find('first',array(
            'fields'=>array('Deal.id','Deal.client_id','Deal.property_id','Deal.total_amount','Deal.user_id','Deal.invoice_no','Deal.recurring'),
            'conditions'=>array('Deal.id'=>$dealId),
            'recursive'=>-1
        ));
        if(!$paymentArr)
        {
            $this->Session->setFlash(__('Invalid Deal'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('controller'=>'Deals','action' => 'index'));
        }
        if($this->request->is('post') || !empty($this->request->query['sync_existing']))
        {
            $this->_syncExistingReceiptsToArd($dealId);
        }
        $invoiceNo=$paymentArr['Deal']['invoice_no'];
        $propertyId=$paymentArr['Deal']['property_id'];
        $clientId = !empty($paymentArr['Deal']['client_id']) ? (int)$paymentArr['Deal']['client_id'] : 0;
        $projectId = 0;
        $payment=$this->Property->find('first',array(
            'fields'=>array('Property.id','Property.name','Property.project_id','Property.commission_type','Property.agent_commission'),
            'conditions'=>array('Property.id'=>$propertyId),
            'recursive'=>-1
        ));
        if(!empty($payment['Property']['project_id']))
        {
            $projectId = (int)$payment['Property']['project_id'];
        }
        $receiptAccountUi = $this->_buildReceiptAccountUiData($projectId, $paymentTypeList);
        $selectedArdLedgerId = 0;
        if(isset($this->request->data['DealsPayment']['ard_ledger_id']))
        {
            $selectedArdLedgerId = (int)$this->request->data['DealsPayment']['ard_ledger_id'];
        }
        if($selectedArdLedgerId<=0 && isset($this->request->data['DealsPayment']['paymenttype_id']))
        {
            $postedPaymentType = (int)$this->request->data['DealsPayment']['paymenttype_id'];
            if(isset($receiptAccountUi['default_map'][$postedPaymentType]))
            {
                $selectedArdLedgerId = (int)$receiptAccountUi['default_map'][$postedPaymentType];
            }
        }
        $dealTotalAmount = round((float)$paymentArr['Deal']['total_amount'],2);
        $dealPaidAmount = $this->_getDealPaidEmiSum($dealId);
        $dealOutstanding = round($dealTotalAmount-$dealPaidAmount,2);
        if($dealOutstanding<0)$dealOutstanding=0;
        $nextInstallmentData = $this->_findNextPendingInstallment($dealId);
        $nextInstallment = isset($nextInstallmentData['plan']) ? $nextInstallmentData['plan'] : array();
        $allPlans = isset($nextInstallmentData['plans']) ? $nextInstallmentData['plans'] : array();
        if(empty($nextInstallment))
        {
            if($dealOutstanding<=0)
            {
                $this->Session->setFlash('All Payments Paid','flash',array('alert'=>'success'));
                return $this->redirect(array('action' => "index/$dealId"));
            }
            $autoPlan = $this->_createCarryForwardInstallment($dealId,$dealOutstanding,$paymentArr['Deal']['recurring'],$allPlans);
            if(empty($autoPlan))
            {
                $this->Session->setFlash(__('Unable to create next payment milestone. Please add milestone manually from Payment Plan.'),'flash',array('alert'=>'danger'));
                return $this->redirect(array('controller'=>'plans_payments','action' => 'index',$dealId));
            }
            $nextInstallment = array(
                'id'=>$autoPlan['id'],
                'name'=>$autoPlan['name'],
                'amount'=>(float)$autoPlan['amount'],
                'date'=>$autoPlan['date'],
                'paid_before'=>0,
                'pending'=>(float)$autoPlan['amount']
            );
        }
        $plansPaymentId=$nextInstallment['id'];
        $currentInstallmentAmount = round((float)$nextInstallment['pending'],2);
        $emi=$currentInstallmentAmount;
        $dueDate=$nextInstallment['date'];
        $paymentName=$nextInstallment['name'];
        if(isset($nextInstallment['pending']) && isset($nextInstallment['amount']) && (float)$nextInstallment['pending']<(float)$nextInstallment['amount'])
        {
            $paymentName .= ' '.__('pending').' '.CakeNumber::format($nextInstallment['pending']);
        }
        $dealPropertyName = '-';
        if(!empty($payment['Property']['name']))
        {
            $dealPropertyName = $payment['Property']['name'];
        }
        elseif($this->Property->hasField('property_name') && !empty($payment['Property']['property_name']))
        {
            $dealPropertyName = $payment['Property']['property_name'];
        }
        elseif($propertyId>0)
        {
            $dealPropertyName = __('Property').' #'.$propertyId;
        }
        $dealClientName = '-';
        if($clientId>0)
        {
            $clientRow = $this->Client->find('first',array(
                'fields'=>array('Client.name'),
                'conditions'=>array('Client.id'=>$clientId),
                'recursive'=>-1
            ));
            if(!empty($clientRow['Client']['name']))
            {
                $dealClientName = $clientRow['Client']['name'];
            }
        }
        $commissionType=$payment['Property']['commission_type'];
        $commission=$payment['Property']['agent_commission'];
        
        $isPopup = !empty($this->request->query['popup']) || !empty($this->request->data['popup']);
        $this->set('isPopup', $isPopup);
        if ($this->request->is('post'))
        {
            $dealId=$this->data['dealId'];
            $dataSource = $this->DealsPayment->getDataSource();
            $transactionActive = false;
            $dataSource->begin();
            $transactionActive = true;
            try
            {
                $dueDays=$this->dueDays;
                $enteredPaidAmount=isset($this->request->data['DealsPayment']['emi']) ? (float)$this->request->data['DealsPayment']['emi'] : 0;
                $enteredPaidAmount=round($enteredPaidAmount,2);
                $extraPayment=(float)$this->request->data['DealsPayment']['extra_payment'];
                $extraPayment=round($extraPayment,2);
                $totalAmount=round($enteredPaidAmount+$extraPayment,2);
                if($enteredPaidAmount<=0)
                {
                    throw new Exception(__('Paid amount should be greater than zero.'));
                }
                if($totalAmount<=0)
                {
                    throw new Exception(__('Payment amount should be greater than zero.'));
                }
                $taxId=isset($this->request->data['DealsPayment']['tax_id']) ? $this->request->data['DealsPayment']['tax_id'] : null;
                $taxPost=$this->Tax->findById($taxId);
                $dealTax=0;
                if($taxPost)
                $dealTax=$taxPost['Tax']['percent'];
                $taxAmount=round(($totalAmount*$dealTax)/100);
                $amount=$totalAmount+$taxAmount;
                $recurring = isset($paymentArr['Deal']['recurring']) ? $paymentArr['Deal']['recurring'] : null;
                // Extra/Late amount is recorded separately and must not be allocated against deal principal.
                $allocationResult = $this->_applyCarryForwardAllocation($dealId,$plansPaymentId,$enteredPaidAmount,$recurring);
                if(!$allocationResult['success'])
                {
                    throw new Exception($allocationResult['message']);
                }
                $paymentTypeId = isset($this->request->data['DealsPayment']['paymenttype_id']) ? $this->request->data['DealsPayment']['paymenttype_id'] : null;
                $selectedArdLedgerId = isset($this->request->data['DealsPayment']['ard_ledger_id']) ? (int)$this->request->data['DealsPayment']['ard_ledger_id'] : 0;
                $paymentDate = isset($this->request->data['DealsPayment']['payment_date']) ? $this->request->data['DealsPayment']['payment_date'] : null;
                $paymentDate = $this->_normalizePaymentDate($paymentDate);
                $remarks = isset($this->request->data['DealsPayment']['remarks']) ? $this->request->data['DealsPayment']['remarks'] : null;
                $slipMeta = array(
                    'file' => '',
                    'dir' => 'paymentslips',
                    'mime' => '',
                    'is_image' => 0
                );
                if(isset($this->request->data['DealsPayment']['payment_slip']) && is_array($this->request->data['DealsPayment']['payment_slip']))
                {
                    $slipUpload = $this->request->data['DealsPayment']['payment_slip'];
                    $hasSlipUpload = false;
                    if(!empty($slipUpload['tmp_name']))
                    {
                        $hasSlipUpload = true;
                    }
                    elseif(!empty($slipUpload['name']) && isset($slipUpload['error']) && (int)$slipUpload['error'] === 0)
                    {
                        $hasSlipUpload = true;
                    }
                    if($hasSlipUpload)
                    {
                        $savedSlip = $this->_saveDealPaymentSlipUpload($slipUpload, 'paymentslips');
                        if(empty($savedSlip['ok']))
                        {
                            throw new Exception(!empty($savedSlip['message']) ? $savedSlip['message'] : __('Unable to upload payment slip.'));
                        }
                        $slipMeta['file'] = !empty($savedSlip['file']) ? $savedSlip['file'] : '';
                        $slipMeta['dir'] = !empty($savedSlip['dir']) ? $savedSlip['dir'] : 'paymentslips';
                        $slipMeta['mime'] = !empty($savedSlip['mime']) ? $savedSlip['mime'] : '';
                        $slipMeta['is_image'] = !empty($savedSlip['is_image']) ? 1 : 0;
                    }
                }
                $primaryPaymentId = null;
                $primaryArdTransactionId = 0;
                $receiptNo = $this->_generateReceiptNo($invoiceNo, $projectId);
                $savedPaymentIds = array();
                foreach($allocationResult['allocations'] as $allocIndex=>$allocation)
                {
                    $allocatedAmount = round((float)$allocation['amount'],2);
                    $rowTaxAmount = $allocIndex==0 ? $taxAmount : 0;
                    $rowTaxId = $allocIndex==0 ? $taxId : null;
                    $rowExtraPayment = 0;
                    if($allocIndex==0)
                    {
                        $rowExtraPayment = $extraPayment;
                    }
                    $paymentData = array(
                        'property_id'=>$propertyId,
                        'deal_id'=>$dealId,
                        'emi'=>$allocatedAmount,
                        'extra_payment'=>$rowExtraPayment,
                        'total_amount'=>$allocatedAmount,
                        'amount'=>$allocatedAmount+$rowExtraPayment+$rowTaxAmount,
                        'plans_payment_id'=>$allocation['plan_id'],
                        'receipt_no'=>$receiptNo,
                        'tax_id'=>$rowTaxId,
                        'tax_amount'=>$rowTaxAmount,
                        'paymenttype_id'=>$paymentTypeId,
                        'ard_ledger_id'=>$selectedArdLedgerId,
                        'payment_date'=>$paymentDate,
                        'remarks'=>$remarks,
                        'payment_slip'=>$slipMeta['file'],
                        'payment_slip_dir'=>$slipMeta['dir'],
                        'payment_slip_mime'=>$slipMeta['mime'],
                        'payment_slip_is_image'=>$slipMeta['is_image']
                    );
                    if($allocIndex>0 && $primaryArdTransactionId>0)
                    {
                        $paymentData['ard_transaction_id'] = $primaryArdTransactionId;
                    }
                    $this->DealsPayment->create();
                    if(!$this->DealsPayment->save(array('DealsPayment'=>$paymentData)))
                    {
                        throw new Exception(__('Unable to save payment allocation.'));
                    }
                    $savedDealPaymentId = (int)$this->DealsPayment->id;
                    if($savedDealPaymentId > 0)
                    {
                        $savedPaymentIds[] = $savedDealPaymentId;
                    }
                    if($allocIndex===0)
                    {
                        if(!$this->_syncDealPaymentToArd($savedDealPaymentId, (int)$paymentTypeId, $selectedArdLedgerId, true, false))
                        {
                            throw new Exception(__('Unable to auto-post this receipt into Accounts. Please check ARD ledgers and payment type mapping.'));
                        }
                        $primarySaved = $this->DealsPayment->find('first',array(
                            'fields'=>array('DealsPayment.ard_transaction_id'),
                            'conditions'=>array('DealsPayment.id'=>$savedDealPaymentId),
                            'recursive'=>-1
                        ));
                        if(!empty($primarySaved['DealsPayment']['ard_transaction_id']))
                        {
                            $primaryArdTransactionId = (int)$primarySaved['DealsPayment']['ard_transaction_id'];
                        }
                    }
                    if($primaryPaymentId===null)
                    {
                        $primaryPaymentId = $savedDealPaymentId;
                    }
                }
                if($receiptNo !== '' && !empty($savedPaymentIds))
                {
                    try
                    {
                        $this->DealsPayment->updateAll(
                            array('DealsPayment.receipt_no' => "'" . addslashes($receiptNo) . "'"),
                            array('DealsPayment.id' => $savedPaymentIds)
                        );
                    }
                    catch(Exception $e)
                    {
                        // keep flow running even if receipt update fails
                    }
                }
                // Re-sync once after all allocation rows are saved so ARD keeps one consolidated receipt transaction amount.
                if($primaryPaymentId!==null)
                {
                    if(!$this->_syncDealPaymentToArd((int)$primaryPaymentId, (int)$paymentTypeId, $selectedArdLedgerId, true, false))
                    {
                        throw new Exception(__('Unable to finalize consolidated receipt in Accounts.'));
                    }
                    if(!$this->_syncRentAdjustmentForDealReceipt((int)$dealId, $receiptNo))
                    {
                        throw new Exception(__('Unable to create the linked rental payment and Rental-Payments expense voucher.'));
                    }
                }
                $agentId=$paymentArr['Deal']['user_id'];
                $agentId=(int)$agentId;
                $dealCommissionAmount=0.0;
                if($commissionType=="Percentage" && $commission!=null && $agentId>0)
                {
                    $dealBaseAmount = isset($paymentArr['Deal']['total_amount']) ? (float)$paymentArr['Deal']['total_amount'] : 0.0;
                    $dealCommissionAmount=round(((float)$commission*$dealBaseAmount)/100,2);
                }
                elseif($commissionType=="Value" && $commission!=null && $agentId>0)
                {
                    $dealCommissionAmount=round((float)$commission,2);
                }
                try
                {
                    $dataSource->commit();
                }
                catch(Exception $commitException)
                {
                    if(stripos($commitException->getMessage(), 'no active transaction') === false)
                    {
                        throw $commitException;
                    }
                }
                $transactionActive = false;

                // Automated WhatsApp & In-Portal Payment Receipt Delivery
                if ($primaryPaymentId !== null) {
                    try {
                        App::uses('WhatsAppAutomationComponent', 'Controller/Component');
                        $waReceipt = new WhatsAppAutomationComponent(new ComponentCollection());
                        $waReceipt->sendPaymentReceiptAlert((int)$primaryPaymentId);
                    } catch (Exception $eWaPay) {}
                }

                if($primaryPaymentId!==null && !empty($slipMeta['file']))
                {
                    try
                    {
                        $this->_indexDealPaymentSlipInMediaGallery((int)$primaryPaymentId, $slipMeta, (int)$projectId, $receiptNo);
                    }
                    catch(Exception $galleryEx)
                    {
                        // Gallery indexing should never change the payment save flow.
                    }
                }
                $mailWarning = '';
                try
                {
                    $this->loadModel('Deal');
                    $this->loadModel('Client');
                    $dealArr=$this->Deal->findById($dealId);
                    $clientArr=$this->Client->findById($dealArr['Deal']['client_id']);
                    $email=$clientArr['Client']['email'];$clientName=$clientArr['Client']['name'];
                      $mobileNo=$clientArr['Client']['phone'];$date=$paymentDate;
                    $amountNumber=(float)$amount;
                    $amountWords=$this->_dealPaymentAmountInWords($amountNumber,$this->currencyFull);
                    $paidAmountWords=$amountWords;
                    $amount=CakeNumber::format($amountNumber);$currency=$this->currencyFull;
                    $siteName=$this->siteName;$contactNo=$this->contact;$url=$this->siteDomain;
                    if($email)
                    {
                        if($this->emailNotification)
                        {
                            /* Send Email */
                            $this->loadModel('Emailtemplate');
                            $emailSettingArr=$this->Emailtemplate->findByType('PAY');
                            if($emailSettingArr['Emailtemplate']['status']=="Published")
                            {
                                $message=$this->_renderTemplateString((string)$emailSettingArr['Emailtemplate']['description'], get_defined_vars());
                                $serverName = isset($_SERVER['SERVER_NAME']) ? strtolower(trim($_SERVER['SERVER_NAME'])) : '';
                                $siteDomain = strtolower((string)$this->siteDomain);
                                $isLocalServer = in_array($serverName,array('localhost','127.0.0.1','::1')) || strpos($siteDomain,'localhost')!==false || strpos($siteDomain,'127.0.0.1')!==false;
                                $isMailTransport = (strtolower((string)$this->emailSettype)==='mail');
                                if($isLocalServer && $isMailTransport)
                                {
                                    $mailWarning = __('Email skipped on localhost. Configure SMTP in Email Settings to send emails locally.');
                                }
                                else
                                {
                                    try
                                    {
                                        $Email = new CakeEmail();
                                        $Email->transport($this->emailSettype);
                                        if($this->emailSettype=="Smtp")
                                        $Email->config(array('host' => $this->emailHost,'port' =>  $this->emailPort,'username' => $this->emailUsername,'password' => $this->emailPassword));
                                        $Email->from(array($this->siteEmail =>$this->siteName));
                                        $Email->to($email);
                                        $Email->template('default');
                                        $Email->emailFormat('html');
                                        $Email->subject($emailSettingArr['Emailtemplate']['name']);
                                        $Email->send($message);
                                    }
                                    catch (Exception $mailEx)
                                    {
                                        $mailWarning = __('Payment saved, but email could not be sent. Please check SMTP/Email settings.');
                                    }
                                }
                                /* End Email */
                            }
                        }
                    }
                    if($mobileNo)
                    {
                        if($this->smsNotification)
                        {
                            /* Send Sms */
                            $this->loadModel('Smstemplate');
                            $smsTemplateArr=$this->Smstemplate->findByType('PAY');
                            if($smsTemplateArr['Smstemplate']['status']=="Published")
                            {
                                $url="$this->siteDomain";
                                $currency=$this->currencyType;
                                $message=$this->_renderTemplateString((string)$smsTemplateArr['Smstemplate']['description'], get_defined_vars());
                                try
                                {
                                    $this->CustomFunction->sendSms($mobileNo,$message,$this->smsSettingArr);
                                }
                                catch(Exception $smsEx)
                                {
                                    $mailWarning = trim($mailWarning.' '.__('Payment saved, but SMS could not be sent. Please check SMS settings.'));
                                }
                            }
                            /* End Sms */
                        }
                    }
                }
                catch(Exception $notifyEx)
                {
                    $mailWarning = trim($mailWarning.' '.__('Payment saved, but notification could not be sent. Please check SMS/Email settings.'));
                }
                $shortOrExtra = round($enteredPaidAmount-(float)$currentInstallmentAmount,2);
                if($shortOrExtra<0)
                {
                    $carryAmt = $this->currency.CakeNumber::format(abs($shortOrExtra));
                    $successText = __('Short payment saved. Remaining ').$carryAmt.__(' is carried forward to next installment.');
                }
                elseif($shortOrExtra>0)
                {
                    $extraAmt = $this->currency.CakeNumber::format($shortOrExtra);
                    $successText = __('Over payment saved. Extra ').$extraAmt.__(' is adjusted in upcoming installments.');
                }
                else
                {
                    $successText = __('Installment payment saved successfully.');
                }
                if(strlen($mailWarning)>0)
                {
                    $successText .= ' '.$mailWarning;
                }
                  $this->Session->setFlash($successText,'flash',array('alert'=>'success'));
                  if($isPopup || (!empty($savedPaymentIds) && $this->_paidNotificationPopupEnabledForDeal($dealId, $projectId)))
                  {
                      return $this->_dealPaymentAddSuccessResponse($dealId, $projectId, $savedPaymentIds, $successText);
                  }
                  return $this->redirect(array('controller'=>'deals_payments','action' => 'index',$dealId));
            }
            catch (Exception $e)
            {
                if(!empty($transactionActive))
                {
                    try
                    {
                        $dataSource->rollback();
                    }
                    catch(Exception $rollbackException)
                    {
                        // Payment flow should show the original error, not a nested transaction-state error.
                    }
                }
                else
                {
                    $errorMessage = trim($e->getMessage());
                    $successText = __('Installment payment saved successfully.');
                    if(strlen($errorMessage)>0)
                    {
                        $successText .= ' '.__('Post-save notification warning').': '.$errorMessage;
                    }
                    $this->Session->setFlash($successText,'flash',array('alert'=>'success'));
                    $popupPaymentIds = isset($savedPaymentIds) ? $savedPaymentIds : array();
                    if($isPopup || (!empty($popupPaymentIds) && $this->_paidNotificationPopupEnabledForDeal($dealId, $projectId)))
                    {
                        return $this->_dealPaymentAddSuccessResponse($dealId, $projectId, $popupPaymentIds, $successText);
                    }
                    return $this->redirect(array('controller'=>'deals_payments','action' => 'index',$dealId));
                }
                $errorMessage = trim($e->getMessage());
                if(strlen($errorMessage)<=0)
                {
                    $errorMessage = __('Invalid Post');
                }
                $this->Session->setFlash($errorMessage,'flash',array('alert'=>'danger'));
            }            
        }
        $dueDays=$this->dueDays;
        $targetDueDate=date('Y-m-d',strtotime($dueDate."+$dueDays days"));
        if($targetDueDate<$this->currentDate)
        {
            $lateDate=floor((strtotime($this->currentDate)-strtotime($dueDate))/(60*60*24));
            $extraPayment=round((($emi*$this->lateFees)/100)*$lateDate/365);
        }
        else
        $extraPayment=0;
        $this->set('dealId',$dealId);
        $this->set('dealClientName',$dealClientName);
        $this->set('dealPropertyName',$dealPropertyName);
        $this->set('dealMembershipNo',$invoiceNo);
        $this->set('dealReceiptNoPreview',$this->_previewDealPaymentReceiptNo($invoiceNo,$projectId));
        $this->set('paymentName',$paymentName);
        $this->set('emi',$emi);
        $this->set('currentInstallmentAmount',$currentInstallmentAmount);
        $this->set('totalAmount',$emi);
        $this->set('extraPayment',$extraPayment);
        $this->set('dueDays',$dueDays);
        $this->set('dueDate',$dueDate);
        $this->set('lateFees',$this->lateFees);
        $this->set('ardLedgerList', isset($receiptAccountUi['ledger_list']) ? $receiptAccountUi['ledger_list'] : array());
        $this->set('ardDefaultMap', isset($receiptAccountUi['default_map']) ? $receiptAccountUi['default_map'] : array());
        $this->set('ardSelectedLedgerId', (int)$selectedArdLedgerId);
        $this->set('ardUblLedgerId', isset($receiptAccountUi['ubl_ledger_id']) ? (int)$receiptAccountUi['ubl_ledger_id'] : 0);
        $this->set('ardCashLedgerId', isset($receiptAccountUi['cash_ledger_id']) ? (int)$receiptAccountUi['cash_ledger_id'] : 0);
    }

    protected function _dealPaymentAddSuccessResponse($dealId, $projectId = 0, $savedPaymentIds = array(), $successText = '')
    {
        $this->autoRender = false;
        $this->response->type('html');
        $targetUrl = Router::url(array('controller'=>'deals_payments','action'=>'index',$dealId), true);
        $savedPaymentIds = array_values(array_filter(array_map('intval', (array)$savedPaymentIds)));

        // Automated WhatsApp & SMS Gateway confirmation dispatch (respects toggles)
        try {
            $this->_dispatchLeadbotDealPaymentNotification($dealId, $projectId, $savedPaymentIds);
        } catch (Exception $eGateway) {
            // Non-blocking fail-safe
        }

        if(!empty($savedPaymentIds) && $this->_paidNotificationPopupEnabledForDeal($dealId, $projectId))
        {
            echo $this->_buildPaidNotificationPopupHtml($dealId, $savedPaymentIds, $targetUrl, $successText);
            return $this->response;
        }
        echo '<script>(function(){var targetUrl=' . json_encode($targetUrl) . ';try{if(window.opener&&!window.opener.closed){window.opener.location.href=targetUrl;window.close();return;}}catch(e){}window.location.href=targetUrl;})();</script>';
        return $this->response;
    }

    /**
     * Dispatch automated WhatsApp / SMS receipt confirmation for deal payment
     */
    protected function _dispatchLeadbotDealPaymentNotification($dealId, $projectId = 0, array $savedPaymentIds = array())
    {
        $dealId = (int)$dealId;
        if ($dealId <= 0 || empty($savedPaymentIds)) return false;

        $this->loadModel('Deal');
        $this->loadModel('DealsPayment');

        $deal = $this->Deal->find('first', array(
            'conditions' => array('Deal.id' => $dealId),
            'contain'    => array('Client', 'Property'),
            'recursive'  => 0
        ));
        if (empty($deal)) return false;

        $effectiveProjectId = (int)$projectId > 0 ? (int)$projectId : (!empty($deal['Property']['project_id']) ? (int)$deal['Property']['project_id'] : 0);
        $assignedUserId     = !empty($deal['Deal']['user_id']) ? (int)$deal['Deal']['user_id'] : 0;

        // Sum paid amounts
        $payments = $this->DealsPayment->find('all', array(
            'conditions' => array('DealsPayment.id' => $savedPaymentIds),
            'fields'     => array('DealsPayment.id', 'DealsPayment.amount', 'DealsPayment.payment_date', 'DealsPayment.receipt_no'),
            'recursive'  => -1
        ));

        $totalPaid = 0;
        $receiptNo = '';
        foreach ($payments as $p) {
            $totalPaid += (float)$p['DealsPayment']['amount'];
            if ($receiptNo === '' && !empty($p['DealsPayment']['receipt_no'])) {
                $receiptNo = $p['DealsPayment']['receipt_no'];
            }
        }

        $clientName  = !empty($deal['Client']['name']) ? $deal['Client']['name'] : 'Valued Client';
        $clientPhone = !empty($deal['Client']['mobile']) ? $deal['Client']['mobile'] : (!empty($deal['Client']['phone']) ? $deal['Client']['phone'] : '');

        if (empty($clientPhone)) return false;

        App::uses('LeadbotGatewayComponent', 'Controller/Component');
        $collection = new ComponentCollection();
        $leadbotComp = new LeadbotGatewayComponent($collection);
        $leadbotComp->initialize($this);

        return $leadbotComp->dispatch('payment_posted', array(
            'client_name'   => $clientName,
            'client_mobile' => $clientPhone,
            'phone'         => $clientPhone,
            'amount'        => number_format($totalPaid, 0),
            'total_amount'  => number_format($totalPaid, 0),
            'deal_invoice'  => !empty($deal['Deal']['invoice_no']) ? $deal['Deal']['invoice_no'] : ('Deal #' . $dealId),
            'receipt_no'    => $receiptNo !== '' ? $receiptNo : ('RCPT-' . $savedPaymentIds[0]),
            'property_name' => !empty($deal['Property']['name']) ? $deal['Property']['name'] : 'Plot/Property',
            'project_name'  => 'White Villas',
            'user_id'       => $assignedUserId,
            'project_id'    => $effectiveProjectId
        ), $effectiveProjectId, $assignedUserId);
    }

    protected function _paidNotificationPopupEnabledForDeal($dealId, $projectId = 0)
    {
        $projectId = (int)$projectId;
        if($projectId <= 0)
        {
            $projectId = $this->_projectIdForDealPaymentPopup($dealId);
        }
        if($projectId <= 0)
        {
            return true;
        }
        $this->loadModel('Admin.Ardsetting');
        $this->_ensureDealPaymentReceiptPrefixColumns();
        try
        {
            $rows = $this->Ardsetting->query(
                "SELECT `paid_notification_popup_enabled`
                 FROM `ardaccounts_settings`
                 WHERE `project_id`=".(int)$projectId."
                 ORDER BY `id` DESC
                 LIMIT 1"
            );
            if(empty($rows))
            {
                return true;
            }
            $raw = array();
            if(!empty($rows[0]['ardaccounts_settings']) && is_array($rows[0]['ardaccounts_settings']))
            {
                $raw = $rows[0]['ardaccounts_settings'];
            }
            elseif(!empty($rows[0][0]) && is_array($rows[0][0]))
            {
                $raw = $rows[0][0];
            }
            return !isset($raw['paid_notification_popup_enabled']) || (int)$raw['paid_notification_popup_enabled'] === 1;
        }
        catch(Exception $e)
        {
            return true;
        }
    }

    protected function _projectIdForDealPaymentPopup($dealId)
    {
        $dealId = (int)$dealId;
        if($dealId <= 0)
        {
            return 0;
        }
        $this->loadModel('Deal');
        $this->loadModel('Property');
        $deal = $this->Deal->find('first', array(
            'fields'=>array('Deal.property_id'),
            'conditions'=>array('Deal.id'=>$dealId),
            'recursive'=>-1
        ));
        $propertyId = !empty($deal['Deal']['property_id']) ? (int)$deal['Deal']['property_id'] : 0;
        if($propertyId <= 0)
        {
            return 0;
        }
        $property = $this->Property->find('first', array(
            'fields'=>array('Property.project_id'),
            'conditions'=>array('Property.id'=>$propertyId),
            'recursive'=>-1
        ));
        return !empty($property['Property']['project_id']) ? (int)$property['Property']['project_id'] : 0;
    }

    protected function _buildPaidNotificationPopupHtml($dealId, $savedPaymentIds, $targetUrl, $successText)
    {
        $ids = implode(',', array_values(array_filter(array_map('intval', (array)$savedPaymentIds))));
        $whatsappUrl = Router::url(array('controller'=>'Invpaids','action'=>'whatsapp',$ids,'?'=>array('notification_popup'=>1)), true);
        $emailUrl = Router::url(array('controller'=>'Invpaids','action'=>'sendemail',$ids,'?'=>array('notification_popup'=>1)), true);
        $receiptUrl = Router::url(array('controller'=>'deals_payments','action'=>'printreceipt',$ids), true);
        $successText = trim((string)$successText);
        if($successText === '')
        {
            $successText = __('Installment payment saved successfully.');
        }
        $title = __('Payment Saved');
        $subtitle = __('Send paid notification now using the paid invoice template.');
        $whatsappLabel = __('Send WhatsApp');
        $emailLabel = __('Send Email');
        $printReceiptLabel = __('Print Receipt');
        $doneLabel = __('Done');
        $openingLabel = __('Opening WhatsApp...');
        $sendingLabel = __('Sending email...');
        $errorLabel = __('Unable to send notification. Please try again from Paid Invoices.');
        $refreshedLabel = __('Payment saved. Choose a notification or press Done.');
        return '<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>'.htmlspecialchars($title, ENT_QUOTES, 'UTF-8').'</title>
<style>
body{margin:0;background:#f5f7fb;font-family:Arial,Helvetica,sans-serif;color:#172033;}
.paid-notify-wrap{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;box-sizing:border-box;}
.paid-notify-box{width:100%;max-width:460px;background:#fff;border:1px solid #dfe6ef;border-radius:8px;box-shadow:0 18px 45px rgba(23,32,51,.14);padding:24px;box-sizing:border-box;}
.paid-notify-icon{width:44px;height:44px;border-radius:50%;background:#e8f7ef;color:#16894d;display:flex;align-items:center;justify-content:center;font-size:26px;font-weight:bold;margin-bottom:12px;}
h1{font-size:22px;margin:0 0 8px;line-height:1.25;}
p{font-size:14px;line-height:1.5;margin:0 0 10px;color:#536072;}
.paid-notify-message{border:1px solid #d8ecd7;background:#f1fbf3;color:#1d6333;border-radius:6px;padding:10px 12px;margin:14px 0;}
.paid-notify-actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:18px;}
button{border:0;border-radius:6px;font-size:14px;font-weight:bold;padding:11px 14px;cursor:pointer;min-width:130px;}
button:disabled{cursor:wait;opacity:.7;}
.btn-wa{background:#1fa855;color:#fff;}
.btn-mail{background:#2563eb;color:#fff;}
.btn-print{background:#0f5f99;color:#fff;}
.btn-done{background:#eef2f7;color:#172033;}
#paidNotifyStatus{min-height:22px;margin-top:14px;font-size:13px;color:#536072;}
#paidNotifyStatus.ok{color:#16894d;}
#paidNotifyStatus.fail{color:#b42318;}
</style>
</head>
<body>
<div class="paid-notify-wrap">
<div class="paid-notify-box">
<div class="paid-notify-icon">&#10003;</div>
<h1>'.htmlspecialchars($title, ENT_QUOTES, 'UTF-8').'</h1>
<p>'.htmlspecialchars($subtitle, ENT_QUOTES, 'UTF-8').'</p>
<div class="paid-notify-message">'.htmlspecialchars($successText, ENT_QUOTES, 'UTF-8').'</div>
<div class="paid-notify-actions">
<button type="button" class="btn-wa" id="paidNotifyWhatsapp">'.htmlspecialchars($whatsappLabel, ENT_QUOTES, 'UTF-8').'</button>
<button type="button" class="btn-mail" id="paidNotifyEmail">'.htmlspecialchars($emailLabel, ENT_QUOTES, 'UTF-8').'</button>
<button type="button" class="btn-print" id="paidNotifyPrintReceipt">'.htmlspecialchars($printReceiptLabel, ENT_QUOTES, 'UTF-8').'</button>
<button type="button" class="btn-done" id="paidNotifyDone">'.htmlspecialchars($doneLabel, ENT_QUOTES, 'UTF-8').'</button>
</div>
<div id="paidNotifyStatus">'.htmlspecialchars($refreshedLabel, ENT_QUOTES, 'UTF-8').'</div>
</div>
</div>
<script>
(function(){
var targetUrl='.json_encode($targetUrl).';
var whatsappUrl='.json_encode($whatsappUrl).';
var emailUrl='.json_encode($emailUrl).';
var receiptUrl='.json_encode($receiptUrl).';
var openingText='.json_encode($openingLabel).';
var sendingText='.json_encode($sendingLabel).';
var errorText='.json_encode($errorLabel).';
var statusEl=document.getElementById("paidNotifyStatus");
function refreshParent(){
    try{
        if(window.opener && !window.opener.closed){
            window.opener.location.href=targetUrl;
            return true;
        }
    }catch(e){}
    return false;
}
function setStatus(message, ok){
    statusEl.className=ok===true?"ok":(ok===false?"fail":"");
    statusEl.innerHTML=message;
}
function requestJson(url, busyText, button, callback){
    button.disabled=true;
    setStatus(busyText, null);
    fetch(url,{credentials:"same-origin",headers:{"X-Requested-With":"XMLHttpRequest"}})
        .then(function(response){return response.json();})
        .then(function(data){
            button.disabled=false;
            if(data && data.success){
                setStatus(data.message || "", true);
                if(callback){callback(data);}
            }else{
                setStatus((data && data.message) ? data.message : errorText, false);
            }
        })
        .catch(function(){
            button.disabled=false;
            setStatus(errorText, false);
        });
}
document.getElementById("paidNotifyWhatsapp").onclick=function(){
    var button=this;
    requestJson(whatsappUrl, openingText, button, function(data){
        if(data.url){ window.open(data.url, "_blank"); }
    });
};
document.getElementById("paidNotifyEmail").onclick=function(){
    requestJson(emailUrl, sendingText, this, null);
};
document.getElementById("paidNotifyPrintReceipt").onclick=function(){
    window.open(receiptUrl, "_blank");
};
document.getElementById("paidNotifyDone").onclick=function(){
    if(refreshParent()){
        window.close();
    }else{
        window.location.href=targetUrl;
    }
};
refreshParent();
})();
</script>
</body>
</html>';
    }

    public function edit($id = null)
    {
        $this->loadModel('Paymenttype');
        $this->loadModel('Deal');
        $this->loadModel('Property');
        $paymentTypeList = $this->Paymenttype->find('list',array('fields'=>array('id','name')));
        $this->set('paymentType',$paymentTypeList);
        if (!$id)
        {
            throw new NotFoundException(__('Invalid post'));
        }
        $ids=explode(",",$id);
        $post=array();
        foreach($ids as $paymentId)
        {
            $row = $this->DealsPayment->findById($paymentId);
            if(!empty($row))
            {
                $post[] = $row;
            }
        }
        if (empty($post))
        {
            throw new NotFoundException(__('Invalid post'));
        }
        $this->set('DealsPayment',$post);
        $editPaymentIds = array();
        foreach($post as $editPaymentRow)
        {
            if(!empty($editPaymentRow['DealsPayment']['id']))
            {
                $editPaymentIds[] = (int)$editPaymentRow['DealsPayment']['id'];
            }
        }
        $editPaymentIds = implode(',', array_unique($editPaymentIds));
        $this->set('editPaymentIds',$editPaymentIds);
        $this->set('editPaymentCount',count($post));
        $dealId=(int)$post[0]['DealsPayment']['deal_id'];
        if(!$this->_canAccessDeal($dealId))
        {
            $this->Session->setFlash(__('Access Denied. You do not have permission to edit payments for this deal.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('controller'=>'Deals','action' => 'index'));
        }
        $lockedMessage = $this->_paymentRowsLockedMessage($post);
        if($lockedMessage !== '')
        {
            $this->Session->setFlash($lockedMessage,'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => "index/$dealId"));
        }
        $this->_syncExistingReceiptsToArd($dealId);
        $projectId = 0;
        $dealMeta = $this->Deal->find('first',array(
            'fields'=>array('Deal.property_id'),
            'conditions'=>array('Deal.id'=>$dealId),
            'recursive'=>-1
        ));
        if(!empty($dealMeta['Deal']['property_id']))
        {
            $propertyMeta = $this->Property->find('first',array(
                'fields'=>array('Property.project_id'),
                'conditions'=>array('Property.id'=>(int)$dealMeta['Deal']['property_id']),
                'recursive'=>-1
            ));
            if(!empty($propertyMeta['Property']['project_id']))
            {
                $projectId = (int)$propertyMeta['Property']['project_id'];
            }
        }
        $receiptAccountUi = $this->_buildReceiptAccountUiData($projectId, $paymentTypeList);
        $this->set('ardLedgerList', isset($receiptAccountUi['ledger_list']) ? $receiptAccountUi['ledger_list'] : array());
        $this->set('ardDefaultMap', isset($receiptAccountUi['default_map']) ? $receiptAccountUi['default_map'] : array());
        if ($this->request->is(array('post', 'put')))
        {
            $dealId=$this->data['dealId'];
            unset($this->request->data['dealId']);
            $dataSource = $this->DealsPayment->getDataSource();
            $transactionActive = false;
            $dataSource->begin();
            $transactionActive = true;
            try
            {
                $paymentSlipMediaRows = array();
                $existingById = array();
                foreach((array)$post as $existingRow)
                {
                    if(!empty($existingRow['DealsPayment']['id']))
                    {
                        $existingById[(int)$existingRow['DealsPayment']['id']] = $existingRow['DealsPayment'];
                    }
                }
                if(isset($this->request->data['DealsPaymentBulk']) && is_array($this->request->data['DealsPaymentBulk']))
                {
                    $bulkData = $this->request->data['DealsPaymentBulk'];
                    $bulkIds = array();
                    $rawBulkIds = !empty($bulkData['payment_ids']) ? explode(',', (string)$bulkData['payment_ids']) : array_keys($existingById);
                    foreach($rawBulkIds as $rawBulkId)
                    {
                        $bulkPaymentId = (int)$rawBulkId;
                        if($bulkPaymentId>0 && isset($existingById[$bulkPaymentId]) && !in_array($bulkPaymentId, $bulkIds, true))
                        {
                            $bulkIds[] = $bulkPaymentId;
                        }
                    }
                    if(empty($bulkIds))
                    {
                        throw new Exception(__('Invalid post'));
                    }
                    $bulkSavedSlip = array();
                    if(isset($bulkData['payment_slip']) && is_array($bulkData['payment_slip']))
                    {
                        $bulkTmpName = isset($bulkData['payment_slip']['tmp_name']) ? trim((string)$bulkData['payment_slip']['tmp_name']) : '';
                        $bulkUploadName = isset($bulkData['payment_slip']['name']) ? trim((string)$bulkData['payment_slip']['name']) : '';
                        $bulkUploadError = isset($bulkData['payment_slip']['error']) ? (int)$bulkData['payment_slip']['error'] : 0;
                        if($bulkTmpName !== '' || ($bulkUploadName !== '' && $bulkUploadError === 0))
                        {
                            $bulkSavedSlip = $this->_saveDealPaymentSlipUpload($bulkData['payment_slip'], 'paymentslips');
                            if(empty($bulkSavedSlip['ok']))
                            {
                                throw new Exception(!empty($bulkSavedSlip['message']) ? $bulkSavedSlip['message'] : __('Unable to upload payment slip.'));
                            }
                        }
                    }
                    $expandedData = array();
                    foreach($bulkIds as $bulkIndex => $bulkPaymentId)
                    {
                        $existingPayment = $existingById[$bulkPaymentId];
                        $expandedData[$bulkIndex] = array(
                            'DealsPayment' => array(
                                'id' => $bulkPaymentId,
                                'paymenttype_id' => isset($bulkData['paymenttype_id']) ? $bulkData['paymenttype_id'] : (isset($existingPayment['paymenttype_id']) ? $existingPayment['paymenttype_id'] : ''),
                                'ard_ledger_id' => isset($bulkData['ard_ledger_id']) ? $bulkData['ard_ledger_id'] : (isset($existingPayment['ard_ledger_id']) ? $existingPayment['ard_ledger_id'] : ''),
                                'payment_date' => isset($bulkData['payment_date']) ? $bulkData['payment_date'] : (isset($existingPayment['payment_date']) ? $existingPayment['payment_date'] : ''),
                                'remarks' => isset($bulkData['remarks']) ? $bulkData['remarks'] : (isset($existingPayment['remarks']) ? $existingPayment['remarks'] : ''),
                                'current_payment_slip' => isset($existingPayment['payment_slip']) ? $existingPayment['payment_slip'] : '',
                                'current_payment_slip_dir' => !empty($existingPayment['payment_slip_dir']) ? $existingPayment['payment_slip_dir'] : 'paymentslips',
                                'current_payment_slip_mime' => isset($existingPayment['payment_slip_mime']) ? $existingPayment['payment_slip_mime'] : '',
                                'current_payment_slip_is_image' => !empty($existingPayment['payment_slip_is_image']) ? 1 : 0
                            )
                        );
                        if(!empty($bulkSavedSlip))
                        {
                            $expandedData[$bulkIndex]['DealsPayment']['_saved_payment_slip'] = $bulkSavedSlip;
                        }
                    }
                    $this->request->data = $expandedData;
                }
                foreach((array)$this->request->data as $rowIndex => $rowWrap)
                {
                    if(empty($rowWrap['DealsPayment']) || !is_array($rowWrap['DealsPayment']))
                    {
                        continue;
                    }
                    $rowData = $rowWrap['DealsPayment'];
                    $paymentId = isset($rowData['id']) ? (int)$rowData['id'] : 0;
                    if($paymentId <= 0)
                    {
                        continue;
                    }
                    $existingSlipFile = isset($rowData['current_payment_slip']) ? trim((string)$rowData['current_payment_slip']) : '';
                    $existingSlipDir = isset($rowData['current_payment_slip_dir']) ? trim((string)$rowData['current_payment_slip_dir']) : 'paymentslips';
                    $existingSlipMime = isset($rowData['current_payment_slip_mime']) ? trim((string)$rowData['current_payment_slip_mime']) : '';
                    $existingSlipImage = isset($rowData['current_payment_slip_is_image']) ? (int)$rowData['current_payment_slip_is_image'] : 0;
                    if(isset($existingById[$paymentId]))
                    {
                        if($existingSlipFile === '' && !empty($existingById[$paymentId]['payment_slip']))
                        {
                            $existingSlipFile = (string)$existingById[$paymentId]['payment_slip'];
                        }
                        if(($existingSlipDir === '' || $existingSlipDir === 'paymentslips') && !empty($existingById[$paymentId]['payment_slip_dir']))
                        {
                            $existingSlipDir = (string)$existingById[$paymentId]['payment_slip_dir'];
                        }
                        if($existingSlipMime === '' && !empty($existingById[$paymentId]['payment_slip_mime']))
                        {
                            $existingSlipMime = (string)$existingById[$paymentId]['payment_slip_mime'];
                        }
                        if($existingSlipImage === 0 && !empty($existingById[$paymentId]['payment_slip_is_image']))
                        {
                            $existingSlipImage = (int)$existingById[$paymentId]['payment_slip_is_image'];
                        }
                    }
                    $hasNewSlip = false;
                    $savedSlipOverride = array();
                    if(isset($rowData['_saved_payment_slip']) && is_array($rowData['_saved_payment_slip']))
                    {
                        $savedSlipOverride = $rowData['_saved_payment_slip'];
                        if(!empty($savedSlipOverride['ok']))
                        {
                            $hasNewSlip = true;
                        }
                    }
                    if(isset($rowData['payment_slip']) && is_array($rowData['payment_slip']))
                    {
                        $tmpName = isset($rowData['payment_slip']['tmp_name']) ? trim((string)$rowData['payment_slip']['tmp_name']) : '';
                        $uploadName = isset($rowData['payment_slip']['name']) ? trim((string)$rowData['payment_slip']['name']) : '';
                        $uploadError = isset($rowData['payment_slip']['error']) ? (int)$rowData['payment_slip']['error'] : 0;
                        if($tmpName !== '' || ($uploadName !== '' && $uploadError === 0))
                        {
                            $hasNewSlip = true;
                        }
                    }
                    if($hasNewSlip)
                    {
                        $savedSlip = !empty($savedSlipOverride) ? $savedSlipOverride : $this->_saveDealPaymentSlipUpload($rowData['payment_slip'], 'paymentslips');
                        if(empty($savedSlip['ok']))
                        {
                            throw new Exception(!empty($savedSlip['message']) ? $savedSlip['message'] : __('Unable to upload payment slip.'));
                        }
                        $newSlipFile = !empty($savedSlip['file']) ? (string)$savedSlip['file'] : '';
                        $newSlipDir = !empty($savedSlip['dir']) ? (string)$savedSlip['dir'] : 'paymentslips';
                        if($existingSlipFile !== '' && ($existingSlipFile !== $newSlipFile || $existingSlipDir !== $newSlipDir))
                        {
                            $this->_deleteDealPaymentSlipFile($existingSlipFile, $existingSlipDir);
                        }
                        $this->request->data[$rowIndex]['DealsPayment']['payment_slip'] = $newSlipFile;
                        $this->request->data[$rowIndex]['DealsPayment']['payment_slip_dir'] = $newSlipDir;
                        $this->request->data[$rowIndex]['DealsPayment']['payment_slip_mime'] = !empty($savedSlip['mime']) ? (string)$savedSlip['mime'] : '';
                        $this->request->data[$rowIndex]['DealsPayment']['payment_slip_is_image'] = !empty($savedSlip['is_image']) ? 1 : 0;
                        $receiptNoForGallery = '';
                        if(isset($rowData['receipt_no']))
                        {
                            $receiptNoForGallery = trim((string)$rowData['receipt_no']);
                        }
                        elseif(!empty($existingById[$paymentId]['receipt_no']))
                        {
                            $receiptNoForGallery = trim((string)$existingById[$paymentId]['receipt_no']);
                        }
                        $paymentSlipMediaRows[] = array(
                            'payment_id' => $paymentId,
                            'file' => $newSlipFile,
                            'dir' => $newSlipDir,
                            'mime' => !empty($savedSlip['mime']) ? (string)$savedSlip['mime'] : '',
                            'is_image' => !empty($savedSlip['is_image']) ? 1 : 0,
                            'receipt_no' => $receiptNoForGallery
                        );
                    }
                    else
                    {
                        $this->request->data[$rowIndex]['DealsPayment']['payment_slip'] = $existingSlipFile;
                        $this->request->data[$rowIndex]['DealsPayment']['payment_slip_dir'] = $existingSlipDir;
                        $this->request->data[$rowIndex]['DealsPayment']['payment_slip_mime'] = $existingSlipMime;
                        $this->request->data[$rowIndex]['DealsPayment']['payment_slip_is_image'] = $existingSlipImage;
                    }
                    unset($this->request->data[$rowIndex]['DealsPayment']['current_payment_slip']);
                    unset($this->request->data[$rowIndex]['DealsPayment']['current_payment_slip_dir']);
                    unset($this->request->data[$rowIndex]['DealsPayment']['current_payment_slip_mime']);
                    unset($this->request->data[$rowIndex]['DealsPayment']['current_payment_slip_is_image']);
                    unset($this->request->data[$rowIndex]['DealsPayment']['_saved_payment_slip']);
                }
                if (!$this->DealsPayment->saveAll($this->request->data))
                {
                    throw new Exception(__('Unable to update payment.'));
                }
                $syncedPaymentIds = array();
                foreach((array)$this->request->data as $row)
                {
                    if(!isset($row['DealsPayment']['id']))
                    {
                        continue;
                    }
                    $paymentId = (int)$row['DealsPayment']['id'];
                    if($paymentId<=0)
                    {
                        continue;
                    }
                    $paymentTypeId = isset($row['DealsPayment']['paymenttype_id']) ? (int)$row['DealsPayment']['paymenttype_id'] : 0;
                    $ledgerId = isset($row['DealsPayment']['ard_ledger_id']) ? (int)$row['DealsPayment']['ard_ledger_id'] : 0;
                    if(!$this->_syncDealPaymentToArd($paymentId,$paymentTypeId,$ledgerId,true))
                    {
                        throw new Exception(__('Unable to sync updated payment into Accounts.'));
                    }
                    $syncedPaymentIds[$paymentId] = $paymentId;
                }
                if(!$this->_syncEditedPaymentVoucherDates(array_values($syncedPaymentIds)))
                {
                    throw new Exception(__('Unable to update the linked Accounts voucher date.'));
                }
                $rentAdjustmentGroups = array();
                foreach(array_values($syncedPaymentIds) as $syncedPaymentId)
                {
                    $syncedRow = $this->DealsPayment->find('first', array(
                        'fields' => array('DealsPayment.deal_id', 'DealsPayment.receipt_no'),
                        'conditions' => array('DealsPayment.id' => (int)$syncedPaymentId),
                        'recursive' => -1
                    ));
                    if(!empty($syncedRow['DealsPayment']['deal_id']) && !empty($syncedRow['DealsPayment']['receipt_no']))
                    {
                        $groupKey = (int)$syncedRow['DealsPayment']['deal_id'].'|'.trim((string)$syncedRow['DealsPayment']['receipt_no']);
                        $rentAdjustmentGroups[$groupKey] = array((int)$syncedRow['DealsPayment']['deal_id'], trim((string)$syncedRow['DealsPayment']['receipt_no']));
                    }
                }
                foreach($rentAdjustmentGroups as $rentAdjustmentGroup)
                {
                    if(!$this->_syncRentAdjustmentForDealReceipt($rentAdjustmentGroup[0], $rentAdjustmentGroup[1]))
                    {
                        throw new Exception(__('Unable to update the linked rental payment and Rental-Payments expense voucher.'));
                    }
                }
                try
                {
                    $dataSource->commit();
                }
                catch(Exception $commitException)
                {
                    if(stripos($commitException->getMessage(), 'no active transaction') === false)
                    {
                        throw $commitException;
                    }
                }
                $transactionActive = false;
                if(!empty($paymentSlipMediaRows))
                {
                    foreach($paymentSlipMediaRows as $gallerySlipRow)
                    {
                        try
                        {
                            $receiptNoForGallery = !empty($gallerySlipRow['receipt_no']) ? $gallerySlipRow['receipt_no'] : '';
                            unset($gallerySlipRow['receipt_no']);
                            $this->_indexDealPaymentSlipInMediaGallery((int)$gallerySlipRow['payment_id'], $gallerySlipRow, (int)$projectId, $receiptNoForGallery);
                        }
                        catch(Exception $galleryEx)
                        {
                            // Gallery indexing should never change the payment edit flow.
                        }
                    }
                }
                $this->Session->setFlash(__('Your Payment has been saved.'),'flash',array('alert'=>'success'));
                return $this->redirect(array('action' => "index/$dealId"));
            }
            catch (Exception $e)
            {
                if(!empty($transactionActive))
                {
                    try
                    {
                        $dataSource->rollback();
                    }
                    catch(Exception $rollbackException)
                    {
                        // Keep the original edit error instead of replacing it with a transaction-state warning.
                    }
                }
                $errorMessage = trim($e->getMessage());
                if(strlen($errorMessage)<=0)
                {
                    $errorMessage = __('Invalid Post.');
                }
                $this->Session->setFlash($errorMessage,'flash',array('alert'=>'danger'));
            }
            $this->set('isError',true);
        }
        else
        {
            $this->layout = null;
            $this->set('isError',false);
        }
        if (!$this->request->data)
        {
            $this->request->data = $post;
        }
        $this->set('dealId',$dealId);
    }
    public function deleteall()
    {
        $dealId=$this->data['dealId'];
        if(!$this->_canAccessDeal($dealId))
        {
            $this->Session->setFlash(__('Access Denied. You do not have permission to delete payments for this deal.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('controller'=>'Deals','action' => 'index'));
        }
        $dataSource = null;
        try
        {
            if ($this->request->is('post'))
            {
                $this->loadModel('AwalletHistory');
                $this->loadModel('Admin.Ardtransaction');
                $dataSource = $this->DealsPayment->getDataSource();
                $dataSource->begin();
                $deleteIds = array();
                $rentAdjustmentGroups = array();
                $cancellationDealIds = array();
                foreach((array)$this->data['DealsPayment']['id'] as $key => $rawValue)
                {
                    foreach(explode(',',(string)$rawValue) as $tokenId)
                    {
                        $parsedId = (int)trim($tokenId);
                        if($parsedId>0)
                        {
                            $deleteIds[$parsedId] = $parsedId;
                        }
                    }
                }
                foreach($deleteIds as $value)
                {
                    if($value>0)
                    {
                        $paymentRow = $this->DealsPayment->find('first',array(
                            'fields'=>array('DealsPayment.id','DealsPayment.deal_id','DealsPayment.property_id','DealsPayment.receipt_no','DealsPayment.remarks','DealsPayment.ard_transaction_id','DealsPayment.ard_ledger_id','DealsPayment.ard_component_transaction_ids'),
                            'conditions'=>array('DealsPayment.id'=>$value),
                            'recursive'=>-1
                        ));
                        if(!empty($paymentRow['DealsPayment']['deal_id']) && !empty($paymentRow['DealsPayment']['receipt_no']))
                        {
                            $groupKey = (int)$paymentRow['DealsPayment']['deal_id'].'|'.trim((string)$paymentRow['DealsPayment']['receipt_no']);
                            $rentAdjustmentGroups[$groupKey] = array((int)$paymentRow['DealsPayment']['deal_id'], trim((string)$paymentRow['DealsPayment']['receipt_no']));
                        }
                        if(!empty($paymentRow['DealsPayment']['deal_id']))
                        {
                            $cancellationDealIds[(int)$paymentRow['DealsPayment']['deal_id']] = (int)$paymentRow['DealsPayment']['deal_id'];
                        }
                        $lockedMessage = $this->_paymentRowsLockedMessage(array($paymentRow));
                        if($lockedMessage !== '')
                        {
                            throw new Exception($lockedMessage);
                        }
                        $txId = !empty($paymentRow['DealsPayment']['ard_transaction_id']) ? (int)$paymentRow['DealsPayment']['ard_transaction_id'] : 0;
                        $ledgerId = !empty($paymentRow['DealsPayment']['ard_ledger_id']) ? (int)$paymentRow['DealsPayment']['ard_ledger_id'] : 0;
                        $incomeLedgerId = 0;
                        $componentLedgerIds = array();
                        $post=$this->AwalletHistory->findByDealsPaymentId($value);
                        if($post)
                        {
                            $agentId=$post['AwalletHistory']['user_id'];
                            $commissionAmount=$post['AwalletHistory']['amount'];
                            $this->CustomFunction->awalletDelete($agentId,$commissionAmount);
                            $this->AwalletHistory->delete($post['AwalletHistory']['id']);
                        }
                        if(!$this->_deleteDealPaymentIncomeVouchers($paymentRow))
                        {
                            throw new Exception(__('Unable to permanently delete the linked income voucher.'));
                        }
                        // The guarded helper removes the main and component
                        // vouchers, including legacy receipt-number links.
                        $txId = 0;
                        $paymentRow['DealsPayment']['ard_component_transaction_ids'] = '';
                        if($txId>0)
                        {
                            $txRow = $this->Ardtransaction->find('first', array(
                                'fields' => array('Ardtransaction.income_ledger_id'),
                                'conditions' => array('Ardtransaction.id' => $txId),
                                'recursive' => -1
                            ));
                            if(!empty($txRow['Ardtransaction']['income_ledger_id']))
                            {
                                $incomeLedgerId = (int)$txRow['Ardtransaction']['income_ledger_id'];
                            }
                            $this->_deleteArdJournalByTransaction($txId);
                            $this->Ardtransaction->delete($txId);
                        }
                        foreach($this->_ardParseComponentTransactionIds(isset($paymentRow['DealsPayment']['ard_component_transaction_ids']) ? $paymentRow['DealsPayment']['ard_component_transaction_ids'] : '') as $componentTxId)
                        {
                            $componentTx = $this->Ardtransaction->find('first', array(
                                'fields' => array('Ardtransaction.ledger_id','Ardtransaction.income_ledger_id','Ardtransaction.expense_ledger_id'),
                                'conditions' => array('Ardtransaction.id' => (int)$componentTxId),
                                'recursive' => -1
                            ));
                            if(!empty($componentTx['Ardtransaction']))
                            {
                                foreach(array('ledger_id','income_ledger_id','expense_ledger_id') as $ledgerField)
                                {
                                    if(!empty($componentTx['Ardtransaction'][$ledgerField]))
                                    {
                                        $componentLedgerIds[(int)$componentTx['Ardtransaction'][$ledgerField]] = (int)$componentTx['Ardtransaction'][$ledgerField];
                                    }
                                }
                                $this->_deleteArdJournalByTransaction((int)$componentTxId);
                                $this->Ardtransaction->delete((int)$componentTxId);
                            }
                        }
                        if(!$this->DealsPayment->delete($value))
                        {
                            throw new Exception(__('Unable to delete selected payment.'));
                        }
                        if($ledgerId>0)
                        {
                            $this->_recalculateArdLedgerBalance($ledgerId);
                        }
                        if($incomeLedgerId>0)
                        {
                            $this->_recalculateArdLedgerBalance($incomeLedgerId);
                        }
                        foreach($componentLedgerIds as $componentLedgerId)
                        {
                            if($componentLedgerId > 0)
                            {
                                $this->_recalculateArdLedgerBalance($componentLedgerId);
                            }
                        }
                    }                    
                }
                foreach($rentAdjustmentGroups as $rentAdjustmentGroup)
                {
                    if(!$this->_syncRentAdjustmentForDealReceipt($rentAdjustmentGroup[0], $rentAdjustmentGroup[1]))
                    {
                        throw new Exception(__('Unable to reverse or update the linked rental payment expense voucher.'));
                    }
                }
                foreach($cancellationDealIds as $cancellationDealId)
                {
                    if(!$this->_syncDealCancellationVoucherAfterPaymentDelete((int)$cancellationDealId))
                    {
                        throw new Exception(__('Unable to update the linked cancellation accounting voucher.'));
                    }
                }
                $dataSource->commit();
                $this->Session->setFlash(__('Your Payment has been deleted.'),'flash',array('alert'=>'success'));
            }
            return $this->redirect(array('action' => "index/$dealId"));
        }
        catch (Exception $e)
        {
            if($dataSource)
            {
                $dataSource->rollback();
            }
            $errorMessage = trim($e->getMessage());
            if($errorMessage === '') { $errorMessage = __('Invalid Post.'); }
            $this->Session->setFlash($errorMessage,'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => "index/$dealId"));
        }
    }
    public function backfill_all_receipts()
    {
        if(!$this->request->is('post'))
        {
            throw new MethodNotAllowedException(__('Invalid request method.'));
        }
        $projectId = 0;
        if(isset($this->request->query['project_id']))
        {
            $projectId = (int)$this->request->query['project_id'];
        }
        if($this->userType !== 'ADR')
        {
            $this->Session->setFlash(__('Permission denied.'),'flash',array('alert'=>'danger'));
            if($projectId>0)
            {
                return $this->redirect(array('controller'=>'Ardaccounts','action'=>'dashboard',$projectId));
            }
            return $this->redirect(array('controller'=>'Ardaccounts','action'=>'dashboard'));
        }
        try
        {
            @set_time_limit(0);
            $syncedCount = (int)$this->_syncExistingReceiptsToArd(null);
            $summary = $this->_getLastReceiptSyncSummary();
            $failedCount = isset($summary['failed']) ? (int)$summary['failed'] : 0;
            if($syncedCount>0 || $failedCount>0)
            {
                $msg = sprintf(__('Backfill completed. Synced: %d, Failed: %d.'),$syncedCount,$failedCount);
                if($failedCount>0 && !empty($summary['errors'][0]))
                {
                    $msg .= ' '.__('First error').': '.$summary['errors'][0];
                }
                $this->Session->setFlash($msg,'flash',array('alert'=>$failedCount>0 ? 'warning' : 'success'));
            }
            else
            {
                $this->Session->setFlash(__('Backfill completed. All receipts are already synced.'),'flash',array('alert'=>'success'));
            }
        }
        catch(Exception $e)
        {
            $this->Session->setFlash(__('Backfill failed. ').$e->getMessage(),'flash',array('alert'=>'danger'));
        }
        if($projectId>0)
        {
            return $this->redirect(array('controller'=>'Ardaccounts','action'=>'dashboard',$projectId));
        }
        return $this->redirect(array('controller'=>'Ardaccounts','action'=>'dashboard'));
    }
   public function print_invoice($id = null) {
    if (!$id || !$this->_canAccessDeal($id)) {
        throw new NotFoundException(__('Deal not found'));
    }

    $deal = $this->Deal->find('first', [
        'conditions' => ['Deal.id' => $id],
        'contain' => ['Customer', 'PlansPayment', 'DealsPayment']
    ]);

    if (!$deal) {
        throw new NotFoundException(__('Deal not found'));
    }

    // Match each scheduled installment to actual payment
    foreach ($deal['PlansPayment'] as &$plan) {
        $matched = false;
        foreach ($deal['DealsPayment'] as $payment) {
            if (
                $payment['due_date'] === $plan['due_date'] &&
                (float)$payment['amount'] === (float)$plan['amount']
            ) {
                $matched = true;
                break;
            }
        }
        $plan['status'] = $matched ? 'Paid' : 'Unpaid';
    }

    // Calculate total outstanding (unpaid only)
    $totalOutstanding = 0;
    foreach ($deal['PlansPayment'] as $plan) {
        if ($plan['status'] === 'Unpaid') {
            $totalOutstanding += (float)$plan['amount'];
        }
    }

    $this->set(compact('deal', 'totalOutstanding'));
}

    public function viewpaymentslip($id = null)
    {
        $this->layout = null;
        if(!$id)
        {
            $this->Session->setFlash(__('Invalid Post.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'index'));
        }

        $joins = array(
            array('table'=>'deals','alias'=>'Deal','type'=>'LEFT','conditions'=>array('DealsPayment.deal_id=Deal.id')),
            array('table'=>'clients','alias'=>'Client','type'=>'LEFT','conditions'=>array('Deal.client_id=Client.id')),
            array('table'=>'properties','alias'=>'Property','type'=>'LEFT','conditions'=>array('Deal.property_id=Property.id')),
            array('table'=>'plans_payments','alias'=>'PlansPayment','type'=>'LEFT','conditions'=>array('DealsPayment.plans_payment_id=PlansPayment.id')),
            array('table'=>'paymenttypes','alias'=>'Paymenttype','type'=>'LEFT','conditions'=>array('DealsPayment.paymenttype_id=Paymenttype.id'))
        );
        $fields = array('DealsPayment.*','Deal.*','Client.*','Property.*','PlansPayment.*','Paymenttype.*');
        if($this->DealsPayment->hasField('tax_id'))
        {
            $joins[] = array('table'=>'taxes','alias'=>'Tax','type'=>'LEFT','conditions'=>array('DealsPayment.tax_id=Tax.id'));
            $fields[] = 'Tax.*';
        }
        if($this->DealsPayment->hasField('ard_ledger_id'))
        {
            $joins[] = array('table'=>'ardaccounts_ledgers','alias'=>'Ardledger','type'=>'LEFT','conditions'=>array('DealsPayment.ard_ledger_id=Ardledger.id'));
            $fields[] = 'Ardledger.*';
        }

        $post = $this->DealsPayment->find('first',array(
            'joins'=>$joins,
            'fields'=>$fields,
            'conditions'=>array('DealsPayment.id'=>$id),
            'recursive'=>-1
        ));
        if(!$post || !$this->_canAccessDeal(!empty($post['Deal']['id']) ? $post['Deal']['id'] : 0))
        {
            $this->Session->setFlash(__('Invalid Post or Access Denied.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('controller'=>'Deals','action' => 'index'));
        }

        $slipFile = !empty($post['DealsPayment']['payment_slip']) ? trim((string)$post['DealsPayment']['payment_slip']) : '';
        $slipDir = !empty($post['DealsPayment']['payment_slip_dir']) ? trim((string)$post['DealsPayment']['payment_slip_dir']) : 'paymentslips';
        $slipRelPath = '';
        $slipMime = !empty($post['DealsPayment']['payment_slip_mime']) ? trim((string)$post['DealsPayment']['payment_slip_mime']) : '';
        $slipIsImage = !empty($post['DealsPayment']['payment_slip_is_image']) ? true : false;
        $slipMessage = __('Payment slip not uploaded.');

        if($slipFile !== '')
        {
            $slipMessage = __('Payment slip file was not found. Please upload the correct payment slip again.');
            $safeSlipDir = trim(str_replace('\\', '/', $slipDir), '/');
            if($safeSlipDir === '')
            {
                $safeSlipDir = 'paymentslips';
            }
            $slipAbsPath = WWW_ROOT . 'img' . DS . str_replace('/', DS, $safeSlipDir) . DS . $slipFile;
            if(file_exists($slipAbsPath))
            {
                $slipMessage = '';
                $slipRelPath = $safeSlipDir . '/' . $slipFile;
                if(!$slipIsImage)
                {
                    $slipExt = strtolower(pathinfo($slipFile, PATHINFO_EXTENSION));
                    $slipIsImage = in_array($slipExt, array('jpg','jpeg','png','gif','webp'));
                }
            }
        }

        $this->set('post', $post);
        $this->set('slipRelPath', $slipRelPath);
        $this->set('slipIsImage', $slipIsImage);
        $this->set('slipMime', $slipMime);
        $this->set('slipMessage', $slipMessage);
    }

    public function printreceipt($id = null)
    {
        $this->layout = false;
        $this->loadModel('PlansPayment');
        if (!$id)
        {
            $this->Session->setFlash(__('Invalid Post.'),'flash',array('alert'=>'danger'));
            $this->redirect(array('action' => 'index'));
        }
        $idList = array();
        foreach(explode(',',(string)$id) as $tokenId)
        {
            $parsedId = (int)trim($tokenId);
            if($parsedId>0)
            {
                $idList[$parsedId] = $parsedId;
            }
        }
        if(empty($idList))
        {
            $this->Session->setFlash(__('Invalid Post.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        $primaryId = reset($idList);
        $joins = array(array('table'=>'deals','alias'=>'Deal','type'=>'LEFT','conditions'=>array('DealsPayment.deal_id=Deal.id')),
                                                                       array('table'=>'clients','alias'=>'Client','type'=>'LEFT','conditions'=>array('Deal.client_id=Client.id')),
                                                                       array('table'=>'properties','alias'=>'Property','type'=>'LEFT','conditions'=>array('Deal.property_id=Property.id')),
                                                                       array('table'=>'types','alias'=>'Type','type'=>'LEFT','conditions'=>array('Property.type_id=Type.id')),
                                                                       array('table'=>'projects','alias'=>'Project','type'=>'LEFT','conditions'=>array('Property.project_id=Project.id')),
                                                                       array('table'=>'units','alias'=>'Unit','type'=>'LEFT','conditions'=>array('Property.unit_id=Unit.id')),
                                                                       array('table'=>'plans','alias'=>'Plan','type'=>'LEFT','conditions'=>array('Deal.plan_id=Plan.id')),
                                                                       array('table'=>'plans_payments','alias'=>'PlansPayment','type'=>'LEFT','conditions'=>array('DealsPayment.plans_payment_id=PlansPayment.id')),
                                                                       array('table'=>'paymenttypes','alias'=>'Paymenttype','type'=>'LEFT','conditions'=>array('DealsPayment.paymenttype_id=Paymenttype.id')));
        $fields = array('DealsPayment.*','Deal.*','Client.*','Property.*','Type.*','Project.*','Unit.*','Plan.*','PlansPayment.*','Paymenttype.*');
        $post = $this->DealsPayment->find('first',array('joins'=>$joins,
                                                      'fields'=>$fields,
                                                      'conditions'=>array('DealsPayment.id'=>$primaryId),
                                                      'recursive'=>-1));
        if(!$post)
        {
            $this->Session->setFlash(__('Invalid Post.'),'flash',array('alert'=>'danger'));
            $this->redirect(array('action' => 'index'));
        }
        $dealId = $post['Deal']['id'];
        if(!$this->_canAccessDeal($dealId))
        {
            $this->Session->setFlash(__('Access Denied.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('controller'=>'Deals','action' => 'index'));
        }
        $currentPlanId = !empty($post['PlansPayment']['id']) ? (int)$post['PlansPayment']['id'] : 0;
        $receiptNo = !empty($post['DealsPayment']['receipt_no']) ? trim((string)$post['DealsPayment']['receipt_no']) : '';
        $receiptRows = array();
        if(!empty($idList))
        {
            $receiptRows = $this->DealsPayment->find('all',array(
                'joins'=>$joins,
                'fields'=>$fields,
                'conditions'=>array('DealsPayment.id'=>array_values($idList)),
                'order'=>array('DealsPayment.id'=>'asc'),
                'recursive'=>-1
            ));
        }
        elseif($receiptNo!=='')
        {
            $receiptRows = $this->DealsPayment->find('all',array(
                'joins'=>$joins,
                'fields'=>$fields,
                'conditions'=>array(
                    'DealsPayment.deal_id'=>$dealId,
                    'DealsPayment.receipt_no'=>$receiptNo
                ),
                'order'=>array('DealsPayment.id'=>'asc'),
                'recursive'=>-1
            ));
        }
        if(!empty($receiptRows) && count($receiptRows)>1)
        {
            $sumEmi = 0;
            $sumExtra = 0;
            $sumTotal = 0;
            $sumAmount = 0;
            $sumTax = 0;
            $planNames = array();
            $firstRow = $receiptRows[0];
            foreach($receiptRows as $receiptRow)
            {
                $sumEmi += isset($receiptRow['DealsPayment']['emi']) ? (float)$receiptRow['DealsPayment']['emi'] : 0;
                $sumExtra += isset($receiptRow['DealsPayment']['extra_payment']) ? (float)$receiptRow['DealsPayment']['extra_payment'] : 0;
                $sumTotal += isset($receiptRow['DealsPayment']['total_amount']) ? (float)$receiptRow['DealsPayment']['total_amount'] : 0;
                $sumAmount += isset($receiptRow['DealsPayment']['amount']) ? (float)$receiptRow['DealsPayment']['amount'] : 0;
                $sumTax += isset($receiptRow['DealsPayment']['tax_amount']) ? (float)$receiptRow['DealsPayment']['tax_amount'] : 0;
                $planName = !empty($receiptRow['PlansPayment']['name']) ? trim((string)$receiptRow['PlansPayment']['name']) : '';
                if($planName !== '' && !in_array($planName,$planNames,true))
                {
                    $planNames[] = $planName;
                }
                $planId = !empty($receiptRow['PlansPayment']['id']) ? (int)$receiptRow['PlansPayment']['id'] : 0;
                if($planId > $currentPlanId)
                {
                    $currentPlanId = $planId;
                }
            }
            $post = $firstRow;
            $post['DealsPayment']['emi'] = round($sumEmi,2);
            $post['DealsPayment']['extra_payment'] = round($sumExtra,2);
            $post['DealsPayment']['total_amount'] = round($sumTotal,2);
            $post['DealsPayment']['amount'] = round($sumAmount,2);
            $post['DealsPayment']['tax_amount'] = round($sumTax,2);
            if(!empty($planNames))
            {
                $post['PlansPayment']['name'] = implode(', ', $planNames);
            }
        }
        $dealTotal = isset($post['Deal']['total_amount']) ? (float)$post['Deal']['total_amount'] : 0;
        $summaryData = $this->_getReceiptSummaryData($dealId,$dealTotal);
        $dealBalance = isset($summaryData['deal_due_amount']) ? (float)$summaryData['deal_due_amount'] : 0;
        $scheduleContext = $this->_getDealScheduleContext($dealId);
        $isNextDueOverdue = false;
        $todayDate = !empty($this->currentDate) ? $this->currentDate : date('Y-m-d');

        if($dealBalance <= 0)
        {
            $dueDate = null;
            $isNextDueOverdue = false;
        }
        else
        {
            $targetNextPlan = null;
            if($currentPlanId > 0)
            {
                $currentPlanRow = $this->PlansPayment->find('first', array(
                    'conditions' => array('PlansPayment.id' => $currentPlanId),
                    'recursive' => -1
                ));
                if(!empty($currentPlanRow))
                {
                    $currentPaidRow = $this->DealsPayment->find('first', array(
                        'fields' => array('SUM(DealsPayment.emi) AS paid_total'),
                        'conditions' => array('DealsPayment.plans_payment_id' => $currentPlanId),
                        'recursive' => -1
                    ));
                    $currentPaid = !empty($currentPaidRow[0]['paid_total']) ? (float)$currentPaidRow[0]['paid_total'] : 0;
                    $currentPlanAmount = !empty($currentPlanRow['PlansPayment']['amount']) ? (float)$currentPlanRow['PlansPayment']['amount'] : 0;
                    if(round($currentPlanAmount - $currentPaid, 2) > 0)
                    {
                        $targetNextPlan = $currentPlanRow;
                    }
                }
            }

            if(empty($targetNextPlan))
            {
                $dueDateConditions = array('PlansPayment.deal_id' => $dealId);
                if($currentPlanId > 0)
                {
                    $dueDateConditions['PlansPayment.id >'] = $currentPlanId;
                }
                $targetNextPlan = $this->PlansPayment->find('first', array(
                    'fields' => array('PlansPayment.*'),
                    'conditions' => $dueDateConditions,
                    'order' => array('PlansPayment.id' => 'asc'),
                    'recursive' => -1
                ));
            }

            if(!empty($targetNextPlan))
            {
                $dueDate = !empty($targetNextPlan['PlansPayment']['date']) ? $targetNextPlan['PlansPayment']['date'] : null;

                $nextPlanId = (int)$targetNextPlan['PlansPayment']['id'];
                $nextPlanAmount = (float)$targetNextPlan['PlansPayment']['amount'];
                $nextPaidRow = $this->DealsPayment->find('first', array(
                    'fields' => array('SUM(DealsPayment.emi) AS paid_total'),
                    'conditions' => array('DealsPayment.plans_payment_id' => $nextPlanId),
                    'recursive' => -1
                ));
                $nextPaidAmount = !empty($nextPaidRow[0]['paid_total']) ? (float)$nextPaidRow[0]['paid_total'] : 0;
                $isTargetUnsettled = round($nextPlanAmount - $nextPaidAmount, 2) > 0;

                if(!empty($scheduleContext['is_schedule_over']) && $isTargetUnsettled)
                {
                    if(!empty($scheduleContext['schedule_last_date']))
                    {
                        $dueDate = $scheduleContext['schedule_last_date'];
                    }
                    $isNextDueOverdue = true;
                }
                else
                {
                    $isNextDueOverdue = false;
                }
            }
            else
            {
                $dueDate = !empty($scheduleContext['schedule_last_date']) ? $scheduleContext['schedule_last_date'] : null;
                $isNextDueOverdue = !empty($scheduleContext['is_schedule_over']);
            }
        }
        $agencyLogoFile = '';
        $projectId = !empty($post['Project']['id']) ? (int)$post['Project']['id'] : (!empty($post['Property']['project_id']) ? (int)$post['Property']['project_id'] : 0);
        if($projectId > 0)
        {
            $this->loadModel('User');
            $userFields = array('User.id');
            if($this->User->hasField('photo'))
            {
                $userFields[] = 'User.photo';
            }
            $baseConditions = array(
                'User.project_id' => $projectId,
                'User.photo <>' => ''
            );
            $agencyUserRow = array();
            if($this->User->hasField('ugroup_id'))
            {
                $agencyUserRow = $this->User->find('first', array(
                    'fields' => $userFields,
                    'conditions' => array_merge($baseConditions, array('User.ugroup_id' => 4)),
                    'order' => array('User.id' => 'asc'),
                    'recursive' => -1
                ));
            }
            if(empty($agencyUserRow))
            {
                $agencyUserRow = $this->User->find('first', array(
                    'fields' => $userFields,
                    'conditions' => $baseConditions,
                    'order' => array('User.id' => 'asc'),
                    'recursive' => -1
                ));
            }
            if(!empty($agencyUserRow['User']['photo']))
            {
                $agencyLogoFile = trim((string)$agencyUserRow['User']['photo']);
            }
        }
        $showReceiptPrintDate = isset($this->showReceiptPrintDate) ? (int)$this->showReceiptPrintDate : 1;
        try
        {
            $this->loadModel('Configuration');
            if($this->Configuration && $this->Configuration->hasField('show_receipt_print_date'))
            {
                $cfg = (array)$this->Configuration->find('first', array(
                    'fields' => array('Configuration.show_receipt_print_date'),
                    'conditions' => array('Configuration.id' => 1),
                    'recursive' => -1
                ));
                if(isset($cfg['Configuration']['show_receipt_print_date']))
                {
                    $showReceiptPrintDate = (int)$cfg['Configuration']['show_receipt_print_date'];
                }
            }
        }
        catch(Exception $e)
        {
            $showReceiptPrintDate = isset($this->showReceiptPrintDate) ? (int)$this->showReceiptPrintDate : 1;
        }

        $this->set('dueDate',$dueDate);
        $this->set('isNextDueOverdue',$isNextDueOverdue);
        $this->set('receiptSummary',$summaryData);
        $this->set('post',$post);
        $this->set('agencyLogoFile',$agencyLogoFile);
        $this->set('agency_logo',$agencyLogoFile);
        $this->set('showReceiptPrintDate',$showReceiptPrintDate);
    }
}
