<?php
App::uses('AdminAppController', 'Admin.Controller');
App::uses('CakeNumber', 'Utility');
App::uses('CakeEmail', 'Network/Email');
App::uses('ConnectionManager', 'Model');

class BallotingController extends AdminAppController
{
    public $helpers = array('Html', 'Form', 'Session', 'Paginator', 'Js' => array('Jquery'));
    public $components = array('Session', 'Paginator', 'search-master.Prg', 'CustomFunction');
    public $presetVars = true;
    public $uses = array(
        'BallotingSetting',
        'BallotingUnit',
        'BallotingAllotment',
        'BallotingRun',
        'Ardsetting',
        'Deal',
        'DealsPayment',
        'PlansPayment',
        'Property',
        'Project',
        'Type',
        'Unit',
        'Category',
        'Contact'
    );
    var $paginate = array('page' => 1, 'order' => array('BallotingUnit.id' => 'desc'));

    protected $_unitStatusArr = array(
        'Available' => 'Available',
        'Allotted' => 'Allotted',
        'Hold' => 'Hold',
        'Cancelled' => 'Cancelled'
    );
    protected $_allotmentStatusArr = array(
        'Allotted' => 'Allotted',
        'Cancelled' => 'Cancelled'
    );
    protected $_selectionOrderArr = array(
        'booking_oldest' => 'Booking Date (Oldest First)',
        'booking_latest' => 'Booking Date (Latest First)',
        'highest_paid' => 'Highest Paid % First',
        'random' => 'Random'
    );
    protected $_dealBlockedStatuses = array('Transferd', 'Transferred', 'Permanent Blocked', 'Pemanent Blocked', 'Refund', 'Refund-Running');

    public function beforeFilter()
    {
        parent::beforeFilter();
        if($this->_shouldRunControllerBootstrap('balloting.schema', '2026-04-09.1'))
        {
            $this->_ensureBallotingSchema();
        }
        $canUseAgencyFilters = $this->_canUseAgencyFilters();
        $this->set('unitStatusArr', $this->_unitStatusArr);
        $this->set('allotmentStatusArr', $this->_allotmentStatusArr);
        $this->set('selectionOrderArr', $this->_selectionOrderArr);
        $this->set('canUseAgencyFilters', $canUseAgencyFilters);
        $this->set('projectName', $this->_projectList());
        $this->set('propertyTypeOptions', $this->_propertyTypeList());
        $this->set('unitOptions', $this->_unitList());
        $this->set('categoryOptions', $this->_categoryList());
    }

    protected function _canUseAgencyFilters()
    {
        return $this->_isSuperAdminSession();
    }

    protected function _projectList()
    {
        if ($this->_canUseAgencyFilters()) {
            return (array)$this->CustomFunction->agencyWiseList('ADR', $this->agencyId);
        }
        return (array)$this->CustomFunction->agencyWiseList('AGY', $this->agencyId);
    }

    protected function _propertyTypeList()
    {
        return (array)$this->Type->find('list', array(
            'fields' => array('Type.id', 'Type.name'),
            'order' => array('Type.name' => 'asc'),
            'recursive' => -1
        ));
    }

    protected function _unitList()
    {
        return (array)$this->Unit->find('list', array(
            'fields' => array('Unit.id', 'Unit.name'),
            'order' => array('Unit.name' => 'asc'),
            'recursive' => -1
        ));
    }

    protected function _categoryList()
    {
        return (array)$this->Category->find('list', array(
            'fields' => array('Category.id', 'Category.name'),
            'order' => array('Category.name' => 'asc'),
            'recursive' => -1
        ));
    }

    protected function _lineTextToOptionList($text, $allowCommaSplit = true)
    {
        $text = (string)$text;
        if ($allowCommaSplit) {
            $rows = preg_split('/[\r\n,]+/', $text);
        } else {
            if (preg_match('/[\r\n]/', $text)) {
                $rows = preg_split('/[\r\n]+/', $text);
            } else {
                $rows = preg_split('/,+/', $text);
            }
        }
        $options = array();
        if (!is_array($rows)) {
            return $options;
        }
        foreach ($rows as $row)
        {
            $row = trim((string)$row);
            if ($row === '') {
                continue;
            }
            $row = preg_replace('/\s+/', ' ', $row);
            if (!isset($options[$row])) {
                $options[$row] = $row;
            }
        }
        return $options;
    }

    protected function _ballotingAddressOptions($projectId = 0)
    {
        $projectId = $this->_normalizeProjectId($projectId);
        $output = array('blockOptions' => array(), 'streetOptions' => array());
        if ((int)$projectId <= 0) {
            return $output;
        }
        $settings = array();
        try
        {
            $rows = $this->Ardsetting->query(
                "SELECT `balloting_block_types`,`balloting_street_address_types` FROM `ardaccounts_settings` AS `Ardsetting` WHERE `project_id`=".(int)$projectId." ORDER BY `id` DESC LIMIT 1"
            );
            if (!empty($rows[0]) && is_array($rows[0])) {
                if (!empty($rows[0]['Ardsetting']) && is_array($rows[0]['Ardsetting'])) {
                    $settings = (array)$rows[0]['Ardsetting'];
                } elseif (!empty($rows[0][0]) && is_array($rows[0][0])) {
                    $settings = (array)$rows[0][0];
                } else {
                    foreach ($rows[0] as $rowPart)
                    {
                        if (!is_array($rowPart)) {
                            continue;
                        }
                        if (array_key_exists('balloting_block_types', $rowPart) || array_key_exists('balloting_street_address_types', $rowPart)) {
                            $settings = (array)$rowPart;
                            break;
                        }
                    }
                    if (empty($settings) && (array_key_exists('balloting_block_types', $rows[0]) || array_key_exists('balloting_street_address_types', $rows[0]))) {
                        $settings = (array)$rows[0];
                    }
                }
            }
        }
        catch (Exception $e)
        {
            $settings = array();
        }
        if (empty($settings))
        {
            try
            {
                $settingsRow = $this->Ardsetting->find('first', array(
                    'conditions' => array('Ardsetting.project_id' => (int)$projectId),
                    'order' => array('Ardsetting.id' => 'desc'),
                    'recursive' => -1
                ));
            }
            catch (Exception $e)
            {
                $settingsRow = array();
            }
            $settings = !empty($settingsRow['Ardsetting']) ? (array)$settingsRow['Ardsetting'] : array();
        }
        $blockText = isset($settings['balloting_block_types']) ? (string)$settings['balloting_block_types'] : '';
        $streetText = isset($settings['balloting_street_address_types']) ? (string)$settings['balloting_street_address_types'] : '';
        // Support both real newlines and stored literal \n sequences.
        $blockText = str_replace(array("\\r\\n", "\\n", "\\r"), "\n", $blockText);
        $streetText = str_replace(array("\\r\\n", "\\n", "\\r"), "\n", $streetText);
        $blockText = str_replace(array("\r\n", "\r"), "\n", $blockText);
        $streetText = str_replace(array("\r\n", "\r"), "\n", $streetText);
        $output['blockOptions'] = $this->_lineTextToOptionList($blockText, false);
        $output['streetOptions'] = $this->_lineTextToOptionList($streetText, false);
        return $output;
    }

    protected function _firstAvailableProjectId()
    {
        $list = (array)$this->_projectList();
        foreach ($list as $projectId => $projectTitle)
        {
            if ((int)$projectId > 0) {
                return (int)$projectId;
            }
        }
        return 0;
    }

    protected function _addressOptionsByProject($selectedProjectId = 0)
    {
        $map = array();
        if ($this->_canUseAgencyFilters())
        {
            $projectList = (array)$this->_projectList();
            foreach ($projectList as $projectId => $projectTitle)
            {
                $projectId = (int)$projectId;
                if ($projectId <= 0) {
                    continue;
                }
                $map[$projectId] = $this->_ballotingAddressOptions($projectId);
            }
            return $map;
        }
        $projectId = $this->_normalizeProjectId($selectedProjectId);
        if ($projectId <= 0) {
            $projectId = (int)$this->agencyId;
        }
        if ($projectId > 0) {
            $map[$projectId] = $this->_ballotingAddressOptions($projectId);
        }
        return $map;
    }

    protected function _normalizeDate($rawDate)
    {
        $rawDate = trim((string)$rawDate);
        if ($rawDate === '') {
            return '';
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $rawDate)) {
            return $rawDate;
        }
        try
        {
            return $this->CustomFunction->dateFormatBeforeSave($rawDate);
        }
        catch (Exception $e)
        {
            return '';
        }
    }

    protected function _normalizeProjectId($projectId = null)
    {
        $projectId = (int)$projectId;
        if (!$this->_canUseAgencyFilters()) {
            return (int)$this->agencyId;
        }
        return max(0, $projectId);
    }

    protected function _selectedProjectId($default = 0)
    {
        $projectId = (int)$default;
        if (isset($this->request->query['project_id'])) {
            $projectId = (int)$this->request->query['project_id'];
        }
        if (isset($this->request->data['project_id'])) {
            $projectId = (int)$this->request->data['project_id'];
        }
        if (isset($this->request->data['Balloting']['project_id'])) {
            $projectId = (int)$this->request->data['Balloting']['project_id'];
        }
        if (isset($this->request->data['BallotingSetting']['project_id'])) {
            $projectId = (int)$this->request->data['BallotingSetting']['project_id'];
        }
        if (isset($this->request->data['BallotingUnit']['project_id'])) {
            $projectId = (int)$this->request->data['BallotingUnit']['project_id'];
        }
        if (isset($this->request->data['BallotingRun']['project_id'])) {
            $projectId = (int)$this->request->data['BallotingRun']['project_id'];
        }
        return $this->_normalizeProjectId($projectId);
    }

    protected function _defaultProjectId()
    {
        if (!$this->_canUseAgencyFilters()) {
            return (int)$this->agencyId;
        }
        return 0;
    }

    protected function _ensureBallotingSchema()
    {
        $dbConfig = isset($this->BallotingUnit->useDbConfig) ? $this->BallotingUnit->useDbConfig : 'default';
        try
        {
            $db = ConnectionManager::getDataSource($dbConfig);
        }
        catch (Exception $e)
        {
            return;
        }

        $tableList = array('balloting_settings', 'balloting_units', 'balloting_allotments', 'balloting_runs');
        $missingTables = array();
        foreach ($tableList as $tableName)
        {
            try
            {
                $exists = (array)$db->query("SHOW TABLES LIKE '".$tableName."'");
            }
            catch (Exception $e)
            {
                $exists = array();
            }
            if (empty($exists)) {
                $missingTables[] = $tableName;
            }
        }
        $schemaTouched = !empty($missingTables);

        $sqlList = array(
            "CREATE TABLE IF NOT EXISTS `balloting_settings` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `project_id` int(11) NOT NULL DEFAULT 0,
                `booking_date_from` date DEFAULT NULL,
                `booking_date_to` date DEFAULT NULL,
                `min_paid_percent` decimal(6,2) NOT NULL DEFAULT 0.00,
                `max_overdue_installments` int(11) NOT NULL DEFAULT 999,
                `max_overdue_days` int(11) NOT NULL DEFAULT 9999,
                `require_active_deal` tinyint(1) NOT NULL DEFAULT 1,
                `selection_order` varchar(30) NOT NULL DEFAULT 'booking_oldest',
                `status` varchar(15) NOT NULL DEFAULT 'Active',
                `created` datetime DEFAULT NULL,
                `modified` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uniq_project` (`project_id`),
                KEY `status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",
            "CREATE TABLE IF NOT EXISTS `balloting_settings` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `project_id` int(11) NOT NULL DEFAULT 0,
                `booking_date_from` date DEFAULT NULL,
                `booking_date_to` date DEFAULT NULL,
                `min_paid_percent` decimal(6,2) NOT NULL DEFAULT 0.00,
                `max_overdue_installments` int(11) NOT NULL DEFAULT 999,
                `max_overdue_days` int(11) NOT NULL DEFAULT 9999,
                `require_active_deal` tinyint(1) NOT NULL DEFAULT 1,
                `selection_order` varchar(30) NOT NULL DEFAULT 'booking_oldest',
                `status` varchar(15) NOT NULL DEFAULT 'Active',
                `created` datetime DEFAULT NULL,
                `modified` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uniq_project` (`project_id`),
                KEY `status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `balloting_units` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `project_id` int(11) NOT NULL DEFAULT 0,
                `category_id` int(11) DEFAULT NULL,
                `property_type_id` int(11) DEFAULT NULL,
                `unit_id` int(11) DEFAULT NULL,
                `permanent_type` varchar(40) NOT NULL DEFAULT '',
                `unit_area` decimal(10,2) DEFAULT 0.00,
                `area_unit` varchar(30) DEFAULT '',
                `allotment_no` varchar(80) NOT NULL,
                `property_no` varchar(80) DEFAULT '',
                `street_no` varchar(80) DEFAULT '',
                `block_name` varchar(80) DEFAULT '',
                `street_address` varchar(255) DEFAULT '',
                `status` varchar(20) NOT NULL DEFAULT 'Available',
                `notes` text,
                `imported_at` datetime DEFAULT NULL,
                `created_by` int(11) DEFAULT 0,
                `created` datetime DEFAULT NULL,
                `modified` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `project_id` (`project_id`),
                KEY `allotment_no` (`allotment_no`),
                KEY `status` (`status`),
                KEY `property_type_id` (`property_type_id`),
                KEY `unit_id` (`unit_id`),
                KEY `category_id` (`category_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",
            "CREATE TABLE IF NOT EXISTS `balloting_units` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `project_id` int(11) NOT NULL DEFAULT 0,
                `category_id` int(11) DEFAULT NULL,
                `property_type_id` int(11) DEFAULT NULL,
                `unit_id` int(11) DEFAULT NULL,
                `permanent_type` varchar(40) NOT NULL DEFAULT '',
                `unit_area` decimal(10,2) DEFAULT 0.00,
                `area_unit` varchar(30) DEFAULT '',
                `allotment_no` varchar(80) NOT NULL,
                `property_no` varchar(80) DEFAULT '',
                `street_no` varchar(80) DEFAULT '',
                `block_name` varchar(80) DEFAULT '',
                `street_address` varchar(255) DEFAULT '',
                `status` varchar(20) NOT NULL DEFAULT 'Available',
                `notes` text,
                `imported_at` datetime DEFAULT NULL,
                `created_by` int(11) DEFAULT 0,
                `created` datetime DEFAULT NULL,
                `modified` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `project_id` (`project_id`),
                KEY `allotment_no` (`allotment_no`),
                KEY `status` (`status`),
                KEY `property_type_id` (`property_type_id`),
                KEY `unit_id` (`unit_id`),
                KEY `category_id` (`category_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `balloting_allotments` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `project_id` int(11) NOT NULL DEFAULT 0,
                `deal_id` int(11) NOT NULL DEFAULT 0,
                `client_id` int(11) DEFAULT NULL,
                `balloting_unit_id` int(11) NOT NULL DEFAULT 0,
                `balloting_run_id` int(11) DEFAULT NULL,
                `allotment_no` varchar(80) DEFAULT '',
                `permanent_type` varchar(40) DEFAULT '',
                `property_type_id` int(11) DEFAULT NULL,
                `unit_id` int(11) DEFAULT NULL,
                `category_id` int(11) DEFAULT NULL,
                `unit_area` decimal(10,2) DEFAULT 0.00,
                `area_unit` varchar(30) DEFAULT '',
                `property_no` varchar(80) DEFAULT '',
                `street_no` varchar(80) DEFAULT '',
                `block_name` varchar(80) DEFAULT '',
                `street_address` varchar(255) DEFAULT '',
                `paid_percent` decimal(7,2) DEFAULT 0.00,
                `overdue_installments` int(11) DEFAULT 0,
                `overdue_days` int(11) DEFAULT 0,
                `allotment_date` date DEFAULT NULL,
                `status` varchar(20) NOT NULL DEFAULT 'Allotted',
                `notes` text,
                `created_by` int(11) DEFAULT 0,
                `created` datetime DEFAULT NULL,
                `modified` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `project_id` (`project_id`),
                KEY `deal_id` (`deal_id`),
                KEY `balloting_unit_id` (`balloting_unit_id`),
                KEY `status` (`status`),
                KEY `allotment_date` (`allotment_date`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",
            "CREATE TABLE IF NOT EXISTS `balloting_allotments` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `project_id` int(11) NOT NULL DEFAULT 0,
                `deal_id` int(11) NOT NULL DEFAULT 0,
                `client_id` int(11) DEFAULT NULL,
                `balloting_unit_id` int(11) NOT NULL DEFAULT 0,
                `balloting_run_id` int(11) DEFAULT NULL,
                `allotment_no` varchar(80) DEFAULT '',
                `permanent_type` varchar(40) DEFAULT '',
                `property_type_id` int(11) DEFAULT NULL,
                `unit_id` int(11) DEFAULT NULL,
                `category_id` int(11) DEFAULT NULL,
                `unit_area` decimal(10,2) DEFAULT 0.00,
                `area_unit` varchar(30) DEFAULT '',
                `property_no` varchar(80) DEFAULT '',
                `street_no` varchar(80) DEFAULT '',
                `block_name` varchar(80) DEFAULT '',
                `street_address` varchar(255) DEFAULT '',
                `paid_percent` decimal(7,2) DEFAULT 0.00,
                `overdue_installments` int(11) DEFAULT 0,
                `overdue_days` int(11) DEFAULT 0,
                `allotment_date` date DEFAULT NULL,
                `status` varchar(20) NOT NULL DEFAULT 'Allotted',
                `notes` text,
                `created_by` int(11) DEFAULT 0,
                `created` datetime DEFAULT NULL,
                `modified` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `project_id` (`project_id`),
                KEY `deal_id` (`deal_id`),
                KEY `balloting_unit_id` (`balloting_unit_id`),
                KEY `status` (`status`),
                KEY `allotment_date` (`allotment_date`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8",
            "CREATE TABLE IF NOT EXISTS `balloting_runs` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `project_id` int(11) NOT NULL DEFAULT 0,
                `title` varchar(160) NOT NULL,
                `filters_json` longtext,
                `total_eligible` int(11) NOT NULL DEFAULT 0,
                `total_allotted` int(11) NOT NULL DEFAULT 0,
                `total_skipped` int(11) NOT NULL DEFAULT 0,
                `run_date` date DEFAULT NULL,
                `notes` text,
                `created_by` int(11) DEFAULT 0,
                `created` datetime DEFAULT NULL,
                `modified` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `project_id` (`project_id`),
                KEY `run_date` (`run_date`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",
            "CREATE TABLE IF NOT EXISTS `balloting_runs` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `project_id` int(11) NOT NULL DEFAULT 0,
                `title` varchar(160) NOT NULL,
                `filters_json` longtext,
                `total_eligible` int(11) NOT NULL DEFAULT 0,
                `total_allotted` int(11) NOT NULL DEFAULT 0,
                `total_skipped` int(11) NOT NULL DEFAULT 0,
                `run_date` date DEFAULT NULL,
                `notes` text,
                `created_by` int(11) DEFAULT 0,
                `created` datetime DEFAULT NULL,
                `modified` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `project_id` (`project_id`),
                KEY `run_date` (`run_date`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8"
        );
        if ($schemaTouched)
        {
            foreach ($sqlList as $sql)
            {
                try
                {
                    $db->query($sql);
                }
                catch (Exception $e)
                {
                    // Try compatible fallback statements.
                }
            }
        }
        try
        {
            $db->query("ALTER TABLE `balloting_units` ADD COLUMN `imported_at` datetime DEFAULT NULL AFTER `notes`");
        }
        catch (Exception $e)
        {
            // Ignore if already exists.
        }

        if ($schemaTouched)
        {
            foreach ((array)glob(APP.'tmp'.DS.'cache'.DS.'models'.DS.'*balloting*') as $cacheFile)
            {
                @unlink($cacheFile);
            }
            @unlink(APP.'tmp'.DS.'cache'.DS.'persistent'.DS.'myapp_cake_core_method_cache');
        }
    }

    protected function _defaultSettings($projectId)
    {
        return array(
            'BallotingSetting' => array(
                'project_id' => (int)$projectId,
                'booking_date_from' => '',
                'booking_date_to' => '',
                'min_paid_percent' => '0.00',
                'max_overdue_installments' => 999,
                'max_overdue_days' => 9999,
                'require_active_deal' => 1,
                'selection_order' => 'booking_oldest',
                'status' => 'Active'
            )
        );
    }

    protected function _settingsByProject($projectId)
    {
        $projectId = (int)$projectId;
        if ($projectId <= 0) {
            return $this->_defaultSettings(0);
        }
        $row = $this->BallotingSetting->find('first', array(
            'conditions' => array('BallotingSetting.project_id' => $projectId),
            'recursive' => -1
        ));
        if (empty($row['BallotingSetting']['id'])) {
            return $this->_defaultSettings($projectId);
        }
        if (!empty($row['BallotingSetting']['booking_date_from'])) {
            $row['BallotingSetting']['booking_date_from'] = date($this->dtFormat, strtotime($row['BallotingSetting']['booking_date_from']));
        }
        if (!empty($row['BallotingSetting']['booking_date_to'])) {
            $row['BallotingSetting']['booking_date_to'] = date($this->dtFormat, strtotime($row['BallotingSetting']['booking_date_to']));
        }
        return $row;
    }

    protected function _paidAmountSql()
    {
        return "IFNULL((SELECT SUM(DP.emi) FROM deals_payments DP WHERE DP.deal_id=Deal.id),0)";
    }

    protected function _paidPercentSql()
    {
        $paidSql = $this->_paidAmountSql();
        return "IF(Deal.total_amount>0,ROUND(((".$paidSql.")/Deal.total_amount)*100,2),0)";
    }

    protected function _overdueInstallmentsSql()
    {
        return "(
            SELECT COUNT(*)
            FROM plans_payments PP
            WHERE PP.deal_id=Deal.id
              AND PP.date < CURDATE()
              AND NOT EXISTS (
                SELECT 1
                FROM deals_payments DPP
                WHERE DPP.deal_id=Deal.id
                  AND DPP.plans_payment_id=PP.id
              )
        )";
    }

    protected function _overdueDaysSql()
    {
        return "IFNULL((
            SELECT DATEDIFF(CURDATE(), MIN(PP.date))
            FROM plans_payments PP
            WHERE PP.deal_id=Deal.id
              AND PP.date < CURDATE()
              AND NOT EXISTS (
                SELECT 1
                FROM deals_payments DPP
                WHERE DPP.deal_id=Deal.id
                  AND DPP.plans_payment_id=PP.id
              )
        ),0)";
    }

    protected function _eligibleConditions($projectId, $settingRow, $filters = array(), $excludeAlreadyAllotted = true)
    {
        $setting = isset($settingRow['BallotingSetting']) ? (array)$settingRow['BallotingSetting'] : (array)$settingRow;
        $conditions = array();
        if ((int)$projectId > 0) {
            $conditions[] = array('Property.project_id' => (int)$projectId);
        }
        $bookingFrom = $this->_normalizeDate(isset($setting['booking_date_from']) ? $setting['booking_date_from'] : '');
        $bookingTo = $this->_normalizeDate(isset($setting['booking_date_to']) ? $setting['booking_date_to'] : '');
        if ($bookingFrom !== '') {
            $conditions[] = array('Deal.date >=' => $bookingFrom);
        }
        if ($bookingTo !== '') {
            $conditions[] = array('Deal.date <=' => $bookingTo);
        }
        if (!empty($setting['require_active_deal'])) {
            $conditions[] = array('NOT' => array('Deal.deal_status' => $this->_dealBlockedStatuses));
        }
        $minPaidPercent = (float)(isset($setting['min_paid_percent']) ? $setting['min_paid_percent'] : 0);
        $maxOverdueInstallments = (int)(isset($setting['max_overdue_installments']) ? $setting['max_overdue_installments'] : 999);
        $maxOverdueDays = (int)(isset($setting['max_overdue_days']) ? $setting['max_overdue_days'] : 9999);
        $conditions[] = "(".$this->_paidPercentSql()." >= ".max(0, $minPaidPercent).")";
        $conditions[] = "(".$this->_overdueInstallmentsSql()." <= ".max(0, $maxOverdueInstallments).")";
        $conditions[] = "(".$this->_overdueDaysSql()." <= ".max(0, $maxOverdueDays).")";

        if (!empty($filters['deal_id'])) {
            $conditions[] = array('Deal.id' => (int)$filters['deal_id']);
        }
        if (!empty($filters['property_type_id'])) {
            $conditions[] = array('Property.type_id' => (int)$filters['property_type_id']);
        }
        if (!empty($filters['unit_id'])) {
            $conditions[] = array('Property.unit_id' => (int)$filters['unit_id']);
        }
        if (!empty($filters['category_id'])) {
            $conditions[] = array('Client.category_id' => (int)$filters['category_id']);
        }
        if (isset($filters['area_min']) && trim((string)$filters['area_min']) !== '') {
            $conditions[] = array('Property.area >=' => (float)$filters['area_min']);
        }
        if (isset($filters['area_max']) && trim((string)$filters['area_max']) !== '') {
            $conditions[] = array('Property.area <=' => (float)$filters['area_max']);
        }
        if ($excludeAlreadyAllotted) {
            $conditions[] = "NOT EXISTS (
                SELECT 1 FROM balloting_allotments BA
                WHERE BA.deal_id=Deal.id
                  AND BA.status='Allotted'
            )";
        }
        return $conditions;
    }

    protected function _eligibleDeals($projectId, $settingRow, $filters = array(), $limit = 250, $excludeAlreadyAllotted = true)
    {
        $setting = isset($settingRow['BallotingSetting']) ? (array)$settingRow['BallotingSetting'] : (array)$settingRow;
        $order = array('Deal.date' => 'asc', 'Deal.id' => 'asc');
        $selectionOrder = isset($setting['selection_order']) ? trim((string)$setting['selection_order']) : 'booking_oldest';
        if ($selectionOrder === 'booking_latest') {
            $order = array('Deal.date' => 'desc', 'Deal.id' => 'desc');
        } elseif ($selectionOrder === 'highest_paid') {
            $order = array($this->_paidPercentSql().' DESC', 'Deal.id ASC');
        } elseif ($selectionOrder === 'random') {
            $order = array('RAND()');
        }
        return (array)$this->Deal->find('all', array(
            'fields' => array(
                'Deal.id', 'Deal.invoice_no', 'Deal.date', 'Deal.total_amount', 'Deal.deal_status',
                'Client.id', 'Client.name', 'Client.mobile', 'Client.category_id',
                'Category.id', 'Category.name',
                'Property.id', 'Property.name', 'Property.area', 'Property.project_id', 'Property.type_id', 'Property.unit_id',
                'Type.id', 'Type.name',
                'Unit.id', 'Unit.name',
                '('.$this->_paidAmountSql().') AS paid_amount',
                '('.$this->_paidPercentSql().') AS paid_percent',
                '('.$this->_overdueInstallmentsSql().') AS overdue_installments',
                '('.$this->_overdueDaysSql().') AS overdue_days'
            ),
            'joins' => array(
                array('table' => 'clients', 'alias' => 'Client', 'type' => 'INNER', 'conditions' => array('Client.id=Deal.client_id')),
                array('table' => 'categories', 'alias' => 'Category', 'type' => 'LEFT', 'conditions' => array('Category.id=Client.category_id')),
                array('table' => 'properties', 'alias' => 'Property', 'type' => 'INNER', 'conditions' => array('Property.id=Deal.property_id')),
                array('table' => 'types', 'alias' => 'Type', 'type' => 'LEFT', 'conditions' => array('Type.id=Property.type_id')),
                array('table' => 'units', 'alias' => 'Unit', 'type' => 'LEFT', 'conditions' => array('Unit.id=Property.unit_id'))
            ),
            'conditions' => $this->_eligibleConditions($projectId, $settingRow, $filters, $excludeAlreadyAllotted),
            'order' => $order,
            'limit' => max(1, (int)$limit),
            'recursive' => -1
        ));
    }

    protected function _eligibleDealCount($projectId, $settingRow, $filters = array(), $excludeAlreadyAllotted = true)
    {
        return (int)$this->Deal->find('count', array(
            'joins' => array(
                array('table' => 'clients', 'alias' => 'Client', 'type' => 'INNER', 'conditions' => array('Client.id=Deal.client_id')),
                array('table' => 'properties', 'alias' => 'Property', 'type' => 'INNER', 'conditions' => array('Property.id=Deal.property_id'))
            ),
            'conditions' => $this->_eligibleConditions($projectId, $settingRow, $filters, $excludeAlreadyAllotted),
            'recursive' => -1
        ));
    }

    protected function _availableUnits($projectId, $filters = array(), $limit = 5000)
    {
        $conditions = array('BallotingUnit.status' => 'Available');
        if ((int)$projectId > 0) {
            $conditions['BallotingUnit.project_id'] = (int)$projectId;
        }
        if (!empty($filters['property_type_id'])) {
            $conditions['BallotingUnit.property_type_id'] = (int)$filters['property_type_id'];
        }
        if (!empty($filters['unit_id'])) {
            $conditions['BallotingUnit.unit_id'] = (int)$filters['unit_id'];
        }
        if (!empty($filters['category_id'])) {
            $conditions['BallotingUnit.category_id'] = (int)$filters['category_id'];
        }
        if (isset($filters['area_min']) && trim((string)$filters['area_min']) !== '') {
            $conditions[] = array('BallotingUnit.unit_area >=' => (float)$filters['area_min']);
        }
        if (isset($filters['area_max']) && trim((string)$filters['area_max']) !== '') {
            $conditions[] = array('BallotingUnit.unit_area <=' => (float)$filters['area_max']);
        }
        return (array)$this->BallotingUnit->find('all', array(
            'conditions' => $conditions,
            'order' => array('BallotingUnit.id' => 'asc'),
            'limit' => max(1, (int)$limit),
            'recursive' => -1
        ));
    }

    protected function _dealEligibilitySummaryFromRow($dealRow)
    {
        return array(
            'paid_amount' => isset($dealRow[0]['paid_amount']) ? (float)$dealRow[0]['paid_amount'] : 0.0,
            'paid_percent' => isset($dealRow[0]['paid_percent']) ? (float)$dealRow[0]['paid_percent'] : 0.0,
            'overdue_installments' => isset($dealRow[0]['overdue_installments']) ? (int)$dealRow[0]['overdue_installments'] : 0,
            'overdue_days' => isset($dealRow[0]['overdue_days']) ? (int)$dealRow[0]['overdue_days'] : 0
        );
    }

    protected function _unitMatchesDeal($unitRow, $dealRow)
    {
        $unit = isset($unitRow['BallotingUnit']) ? (array)$unitRow['BallotingUnit'] : array();
        $dealProperty = isset($dealRow['Property']) ? (array)$dealRow['Property'] : array();
        $dealClient = isset($dealRow['Client']) ? (array)$dealRow['Client'] : array();

        $unitTypeId = isset($unit['property_type_id']) ? (int)$unit['property_type_id'] : 0;
        $dealTypeId = isset($dealProperty['type_id']) ? (int)$dealProperty['type_id'] : 0;
        if ($unitTypeId > 0 && $dealTypeId > 0 && $unitTypeId !== $dealTypeId) {
            return false;
        }
        $unitUnitId = isset($unit['unit_id']) ? (int)$unit['unit_id'] : 0;
        $dealUnitId = isset($dealProperty['unit_id']) ? (int)$dealProperty['unit_id'] : 0;
        if ($unitUnitId > 0 && $dealUnitId > 0 && $unitUnitId !== $dealUnitId) {
            return false;
        }
        $unitCategoryId = isset($unit['category_id']) ? (int)$unit['category_id'] : 0;
        $dealCategoryId = isset($dealClient['category_id']) ? (int)$dealClient['category_id'] : 0;
        if ($unitCategoryId > 0 && $dealCategoryId > 0 && $unitCategoryId !== $dealCategoryId) {
            return false;
        }
        $unitArea = isset($unit['unit_area']) ? (float)$unit['unit_area'] : 0;
        $dealArea = isset($dealProperty['area']) ? (float)$dealProperty['area'] : 0;
        if ($unitArea > 0 && $dealArea > 0 && abs($unitArea - $dealArea) > 0.01) {
            return false;
        }
        return true;
    }

    protected function _ballotingAllotmentNotificationType()
    {
        return 'LAT';
    }

    protected function _ballotingFallbackNotificationType()
    {
        return 'LIS';
    }

    protected function _ballotingNotificationTemplateDefaults()
    {
        return array(
            'LAT' => array(
                'sms_name' => 'Balloting Allotment Notification',
                'sms_body' => 'Dear $clientName, your unit has been allotted. Allotment No: $allotmentNo, File: $invoiceNo, Property: $propertyName, Address: $streetAddress.',
                'email_name' => 'Balloting Allotment Notification',
                'email_body' => '<p>Dear $clientName,</p><p>Your unit has been allotted successfully.</p><p><strong>Allotment No:</strong> $allotmentNo<br><strong>Membership No:</strong> $invoiceNo<br><strong>Property:</strong> $propertyName<br><strong>Project:</strong> $projectName<br><strong>Street Address:</strong> $streetAddress</p><p>Regards,<br>$siteName<br>$contactNo</p>'
            ),
            'LIS' => array(
                'sms_name' => 'Letter Issued Generic',
                'sms_body' => 'Dear $clientName, your $letterType has been issued. Ref: $referenceNo, File: $invoiceNo. $url',
                'email_name' => 'Letter Issued Generic',
                'email_body' => '<p>Dear $clientName,</p><p>Your <strong>$letterType</strong> has been issued.</p><p><strong>Reference:</strong> $referenceNo<br><strong>Membership:</strong> $invoiceNo<br><strong>Property:</strong> $propertyName</p><p><a href="$url" target="_blank">View</a></p><p>Regards,<br>$siteName<br>$contactNo</p>'
            )
        );
    }

    protected function _ensureBallotingAllotmentNotificationTemplates()
    {
        try
        {
            $this->loadModel('Smstemplate');
            $this->loadModel('Emailtemplate');
        }
        catch (Exception $e)
        {
            return;
        }
        foreach ((array)$this->_ballotingNotificationTemplateDefaults() as $typeCode => $row)
        {
            $typeCode = strtoupper(substr(trim((string)$typeCode), 0, 3));
            if ($typeCode === '')
            {
                continue;
            }
            try
            {
                $smsExists = $this->Smstemplate->find('first', array(
                    'fields' => array('Smstemplate.id'),
                    'conditions' => array('Smstemplate.type' => $typeCode),
                    'recursive' => -1
                ));
                if (empty($smsExists['Smstemplate']['id']))
                {
                    $this->Smstemplate->create();
                    $this->Smstemplate->save(array('Smstemplate' => array(
                        'name' => isset($row['sms_name']) ? (string)$row['sms_name'] : 'Balloting SMS',
                        'description' => isset($row['sms_body']) ? (string)$row['sms_body'] : '',
                        'status' => 'Published',
                        'type' => $typeCode
                    )), array('validate' => false, 'callbacks' => false));
                }
            }
            catch (Exception $e)
            {
                // Keep allotment flow running.
            }

            try
            {
                $emailExists = $this->Emailtemplate->find('first', array(
                    'fields' => array('Emailtemplate.id'),
                    'conditions' => array('Emailtemplate.type' => $typeCode),
                    'recursive' => -1
                ));
                if (empty($emailExists['Emailtemplate']['id']))
                {
                    $this->Emailtemplate->create();
                    $this->Emailtemplate->save(array('Emailtemplate' => array(
                        'name' => isset($row['email_name']) ? (string)$row['email_name'] : 'Balloting Email',
                        'description' => isset($row['email_body']) ? (string)$row['email_body'] : '',
                        'status' => 'Published',
                        'type' => $typeCode
                    )), array('validate' => false, 'callbacks' => false));
                }
            }
            catch (Exception $e)
            {
                // Keep allotment flow running.
            }
        }
    }

    protected function _ballotingAllotmentNotificationRow($allotmentId)
    {
        $allotmentId = (int)$allotmentId;
        if ($allotmentId <= 0)
        {
            return array();
        }
        return (array)$this->BallotingAllotment->find('first', array(
            'fields' => array(
                'BallotingAllotment.id',
                'BallotingAllotment.allotment_no',
                'BallotingAllotment.property_no',
                'BallotingAllotment.street_no',
                'BallotingAllotment.block_name',
                'BallotingAllotment.street_address',
                'BallotingAllotment.status',
                'Deal.id',
                'Deal.invoice_no',
                'Client.name',
                'Client.mobile',
                'Client.phone',
                'Client.email',
                'Property.name',
                'Project.name'
            ),
            'joins' => array(
                array('table' => 'deals', 'alias' => 'Deal', 'type' => 'LEFT', 'conditions' => array('Deal.id=BallotingAllotment.deal_id')),
                array('table' => 'clients', 'alias' => 'Client', 'type' => 'LEFT', 'conditions' => array('Client.id=BallotingAllotment.client_id')),
                array('table' => 'properties', 'alias' => 'Property', 'type' => 'LEFT', 'conditions' => array('Property.id=Deal.property_id')),
                array('table' => 'projects', 'alias' => 'Project', 'type' => 'LEFT', 'conditions' => array('Project.id=BallotingAllotment.project_id'))
            ),
            'conditions' => array('BallotingAllotment.id' => $allotmentId),
            'recursive' => -1
        ));
    }

    protected function _ballotingNotificationTokens($row)
    {
        $allotment = isset($row['BallotingAllotment']) ? (array)$row['BallotingAllotment'] : array();
        $deal = isset($row['Deal']) ? (array)$row['Deal'] : array();
        $client = isset($row['Client']) ? (array)$row['Client'] : array();
        $property = isset($row['Property']) ? (array)$row['Property'] : array();
        $project = isset($row['Project']) ? (array)$row['Project'] : array();
        return array(
            '$siteName' => (string)$this->siteName,
            '$contactNo' => (string)$this->contact,
            '$url' => rtrim((string)$this->siteDomain, '/').'/admin/Balloting/allotments',
            '$clientName' => isset($client['name']) ? (string)$client['name'] : '',
            '$mobileNo' => isset($client['mobile']) && trim((string)$client['mobile']) !== '' ? trim((string)$client['mobile']) : trim((string)(isset($client['phone']) ? $client['phone'] : '')),
            '$email' => isset($client['email']) ? (string)$client['email'] : '',
            '$invoiceNo' => isset($deal['invoice_no']) ? (string)$deal['invoice_no'] : '',
            '$propertyName' => isset($property['name']) ? (string)$property['name'] : '',
            '$projectName' => isset($project['name']) ? (string)$project['name'] : '',
            '$allotmentNo' => isset($allotment['allotment_no']) ? (string)$allotment['allotment_no'] : '',
            '$streetAddress' => isset($allotment['street_address']) ? (string)$allotment['street_address'] : '',
            '$referenceNo' => isset($allotment['allotment_no']) ? (string)$allotment['allotment_no'] : '',
            '$letterType' => 'Allotment'
        );
    }

    protected function _renderBallotingNotificationMessage($body, $row)
    {
        return strtr((string)$body, $this->_ballotingNotificationTokens($row));
    }

    protected function _sendBallotingAllotmentSms($row)
    {
        if (empty($this->smsNotification) || empty($this->smsSettingArr['Smssetting']['api']))
        {
            return;
        }
        $mobileNo = trim((string)(isset($row['Client']['mobile']) ? $row['Client']['mobile'] : ''));
        if ($mobileNo === '')
        {
            $mobileNo = trim((string)(isset($row['Client']['phone']) ? $row['Client']['phone'] : ''));
        }
        $mobileNo = preg_replace('/\s+/', '', $mobileNo);
        if ($mobileNo === '')
        {
            return;
        }
        $templateType = $this->_ballotingAllotmentNotificationType();
        $template = $this->Smstemplate->find('first', array(
            'fields' => array('Smstemplate.id', 'Smstemplate.description'),
            'conditions' => array('Smstemplate.type' => $templateType, 'Smstemplate.status' => 'Published'),
            'recursive' => -1
        ));
        if (empty($template['Smstemplate']['id']))
        {
            $template = $this->Smstemplate->find('first', array(
                'fields' => array('Smstemplate.id', 'Smstemplate.description'),
                'conditions' => array('Smstemplate.type' => $this->_ballotingFallbackNotificationType(), 'Smstemplate.status' => 'Published'),
                'recursive' => -1
            ));
        }
        if (empty($template['Smstemplate']['id']))
        {
            return;
        }
        $message = $this->_renderBallotingNotificationMessage($template['Smstemplate']['description'], $row);
        if (trim($message) === '')
        {
            return;
        }
        try
        {
            $this->CustomFunction->sendSms($mobileNo, $message, $this->smsSettingArr);
        }
        catch (Exception $e)
        {
            // Keep allotment flow running.
        }
    }

    protected function _sendBallotingAllotmentEmail($row)
    {
        if (empty($this->emailNotification))
        {
            return;
        }
        $email = trim((string)(isset($row['Client']['email']) ? $row['Client']['email'] : ''));
        if ($email === '')
        {
            return;
        }
        $templateType = $this->_ballotingAllotmentNotificationType();
        $template = $this->Emailtemplate->find('first', array(
            'fields' => array('Emailtemplate.id', 'Emailtemplate.name', 'Emailtemplate.description'),
            'conditions' => array('Emailtemplate.type' => $templateType, 'Emailtemplate.status' => 'Published'),
            'recursive' => -1
        ));
        if (empty($template['Emailtemplate']['id']))
        {
            $template = $this->Emailtemplate->find('first', array(
                'fields' => array('Emailtemplate.id', 'Emailtemplate.name', 'Emailtemplate.description'),
                'conditions' => array('Emailtemplate.type' => $this->_ballotingFallbackNotificationType(), 'Emailtemplate.status' => 'Published'),
                'recursive' => -1
            ));
        }
        if (empty($template['Emailtemplate']['id']))
        {
            return;
        }
        $message = $this->_renderBallotingNotificationMessage($template['Emailtemplate']['description'], $row);
        if (trim($message) === '')
        {
            return;
        }
        $subject = trim((string)(isset($template['Emailtemplate']['name']) ? $template['Emailtemplate']['name'] : 'Allotment Notification'));
        if ($subject === '')
        {
            $subject = 'Allotment Notification';
        }
        try
        {
            $Email = new CakeEmail();
            $Email->transport($this->emailSettype);
            if ($this->emailSettype === "Smtp" || $this->emailSettype === "SMTP")
            {
                $Email->config(array(
                    'host' => $this->emailHost,
                    'port' =>  $this->emailPort,
                    'username' => $this->emailUsername,
                    'password' => $this->emailPassword
                ));
            }
            $Email->from(array($this->siteEmail => $this->siteName));
            $Email->to($email);
            $Email->template('default');
            $Email->emailFormat('html');
            $Email->subject($subject);
            $Email->send($message);
        }
        catch (Exception $e)
        {
            // Keep allotment flow running.
        }
    }

    protected function _dispatchBallotingAllotmentNotifications($allotmentId)
    {
        $allotmentId = (int)$allotmentId;
        if ($allotmentId <= 0)
        {
            return;
        }
        try
        {
            $this->_ensureBallotingAllotmentNotificationTemplates();
            $this->loadModel('Smstemplate');
            $this->loadModel('Emailtemplate');
            $row = $this->_ballotingAllotmentNotificationRow($allotmentId);
            if (empty($row['BallotingAllotment']['id']))
            {
                return;
            }
            if (isset($row['BallotingAllotment']['status']) && trim((string)$row['BallotingAllotment']['status']) !== 'Allotted')
            {
                return;
            }
            $this->_sendBallotingAllotmentEmail($row);
            $this->_sendBallotingAllotmentSms($row);
        }
        catch (Exception $e)
        {
            // Keep allotment flow running.
        }
    }

    protected function _assignUnitToDeal($unitRow, $dealRow, $runId = 0, $notes = '')
    {
        $unit = isset($unitRow['BallotingUnit']) ? (array)$unitRow['BallotingUnit'] : array();
        $deal = isset($dealRow['Deal']) ? (array)$dealRow['Deal'] : array();
        $client = isset($dealRow['Client']) ? (array)$dealRow['Client'] : array();
        if (empty($unit['id']) || empty($deal['id'])) {
            return array('ok' => false, 'message' => __('Invalid deal/unit selection.'));
        }
        $existingDealAllotment = (int)$this->BallotingAllotment->find('count', array(
            'conditions' => array('BallotingAllotment.deal_id' => (int)$deal['id'], 'BallotingAllotment.status' => 'Allotted'),
            'recursive' => -1
        ));
        if ($existingDealAllotment > 0) {
            return array('ok' => false, 'message' => __('This deal is already allotted.'));
        }
        $unitLatest = $this->BallotingUnit->find('first', array(
            'conditions' => array('BallotingUnit.id' => (int)$unit['id']),
            'recursive' => -1
        ));
        if (empty($unitLatest['BallotingUnit']['id']) || $unitLatest['BallotingUnit']['status'] !== 'Available') {
            return array('ok' => false, 'message' => __('Selected unit is not available now.'));
        }
        $summary = $this->_dealEligibilitySummaryFromRow($dealRow);
        $allotmentData = array(
            'project_id' => isset($unitLatest['BallotingUnit']['project_id']) ? (int)$unitLatest['BallotingUnit']['project_id'] : 0,
            'deal_id' => (int)$deal['id'],
            'client_id' => isset($client['id']) ? (int)$client['id'] : 0,
            'balloting_unit_id' => (int)$unitLatest['BallotingUnit']['id'],
            'balloting_run_id' => (int)$runId > 0 ? (int)$runId : null,
            'allotment_no' => isset($unitLatest['BallotingUnit']['allotment_no']) ? (string)$unitLatest['BallotingUnit']['allotment_no'] : '',
            'property_type_id' => isset($unitLatest['BallotingUnit']['property_type_id']) ? (int)$unitLatest['BallotingUnit']['property_type_id'] : null,
            'unit_id' => isset($unitLatest['BallotingUnit']['unit_id']) ? (int)$unitLatest['BallotingUnit']['unit_id'] : null,
            'category_id' => isset($unitLatest['BallotingUnit']['category_id']) ? (int)$unitLatest['BallotingUnit']['category_id'] : null,
            'unit_area' => isset($unitLatest['BallotingUnit']['unit_area']) ? (float)$unitLatest['BallotingUnit']['unit_area'] : 0,
            'area_unit' => isset($unitLatest['BallotingUnit']['area_unit']) ? (string)$unitLatest['BallotingUnit']['area_unit'] : '',
            'property_no' => isset($unitLatest['BallotingUnit']['property_no']) ? (string)$unitLatest['BallotingUnit']['property_no'] : '',
            'street_no' => isset($unitLatest['BallotingUnit']['street_no']) ? (string)$unitLatest['BallotingUnit']['street_no'] : '',
            'block_name' => isset($unitLatest['BallotingUnit']['block_name']) ? (string)$unitLatest['BallotingUnit']['block_name'] : '',
            'street_address' => isset($unitLatest['BallotingUnit']['street_address']) ? (string)$unitLatest['BallotingUnit']['street_address'] : '',
            'paid_percent' => (float)$summary['paid_percent'],
            'overdue_installments' => (int)$summary['overdue_installments'],
            'overdue_days' => (int)$summary['overdue_days'],
            'allotment_date' => date('Y-m-d'),
            'status' => 'Allotted',
            'notes' => (string)$notes,
            'created_by' => (int)$this->luserId
        );
        $db = $this->BallotingAllotment->getDataSource();
        $db->begin();
        try
        {
            $this->BallotingAllotment->create();
            if (!$this->BallotingAllotment->save(array('BallotingAllotment' => $allotmentData))) {
                throw new Exception(__('Unable to save allotment.'));
            }
            $this->BallotingUnit->id = (int)$unitLatest['BallotingUnit']['id'];
            if (!$this->BallotingUnit->saveField('status', 'Allotted')) {
                throw new Exception(__('Unable to lock selected unit.'));
            }
            $savedAllotmentId = (int)$this->BallotingAllotment->id;
            $db->commit();
            if ($savedAllotmentId > 0)
            {
                $this->_dispatchBallotingAllotmentNotifications($savedAllotmentId);
            }
            return array('ok' => true, 'id' => $savedAllotmentId);
        }
        catch (Exception $e)
        {
            $db->rollback();
            return array('ok' => false, 'message' => $e->getMessage());
        }
    }

    protected function _manualDeals($projectId, $keyword = '', $limit = 500, $extraConditions = array())
    {
        $conditions = array();
        if ((int)$projectId > 0) {
            $conditions[] = array('Property.project_id' => (int)$projectId);
        }
        $conditions[] = "NOT EXISTS (
            SELECT 1 FROM balloting_allotments BA
            WHERE BA.deal_id=Deal.id
              AND BA.status='Allotted'
        )";
        $keyword = trim((string)$keyword);
        if ($keyword !== '')
        {
            $or = array(
                'Deal.invoice_no LIKE' => '%'.$keyword.'%',
                'Client.name LIKE' => '%'.$keyword.'%',
                'Client.mobile LIKE' => '%'.$keyword.'%',
                'Property.name LIKE' => '%'.$keyword.'%'
            );
            if (preg_match('/^\d+$/', $keyword)) {
                $or[] = array('Deal.id' => (int)$keyword);
            }
            $conditions[] = array('OR' => $or);
        }
        foreach ((array)$extraConditions as $extraCondition)
        {
            if (!empty($extraCondition)) {
                $conditions[] = $extraCondition;
            }
        }
        return (array)$this->Deal->find('all', array(
            'fields' => array(
                'Deal.id', 'Deal.invoice_no', 'Deal.date', 'Deal.total_amount', 'Deal.deal_status',
                'Client.id', 'Client.name', 'Client.mobile', 'Client.category_id',
                'Property.id', 'Property.name', 'Property.area', 'Property.project_id', 'Property.type_id', 'Property.unit_id',
                'Type.id', 'Type.name',
                'Unit.id', 'Unit.name',
                '('.$this->_paidAmountSql().') AS paid_amount',
                '('.$this->_paidPercentSql().') AS paid_percent',
                '('.$this->_overdueInstallmentsSql().') AS overdue_installments',
                '('.$this->_overdueDaysSql().') AS overdue_days'
            ),
            'joins' => array(
                array('table' => 'clients', 'alias' => 'Client', 'type' => 'INNER', 'conditions' => array('Client.id=Deal.client_id')),
                array('table' => 'properties', 'alias' => 'Property', 'type' => 'INNER', 'conditions' => array('Property.id=Deal.property_id')),
                array('table' => 'types', 'alias' => 'Type', 'type' => 'LEFT', 'conditions' => array('Type.id=Property.type_id')),
                array('table' => 'units', 'alias' => 'Unit', 'type' => 'LEFT', 'conditions' => array('Unit.id=Property.unit_id'))
            ),
            'conditions' => $conditions,
            'order' => array('Deal.id' => 'desc'),
            'limit' => max(1, (int)$limit),
            'recursive' => -1
        ));
    }

    protected function _manualDealRow($dealId, $projectId)
    {
        $rows = $this->_manualDeals($projectId, '', 2, array(array('Deal.id' => (int)$dealId)));
        if (!empty($rows[0]['Deal']['id'])) {
            return $rows[0];
        }
        return array();
    }

    protected function _manualAvailableUnits($projectId, $keyword = '', $limit = 2000)
    {
        $conditions = array('BallotingUnit.status' => 'Available');
        if ((int)$projectId > 0) {
            $conditions['BallotingUnit.project_id'] = (int)$projectId;
        }
        $keyword = trim((string)$keyword);
        if ($keyword !== '')
        {
            $conditions[] = array(
                'OR' => array(
                    'BallotingUnit.allotment_no LIKE' => '%'.$keyword.'%',
                    'BallotingUnit.property_no LIKE' => '%'.$keyword.'%',
                    'BallotingUnit.street_no LIKE' => '%'.$keyword.'%',
                    'BallotingUnit.block_name LIKE' => '%'.$keyword.'%',
                    'BallotingUnit.street_address LIKE' => '%'.$keyword.'%'
                )
            );
        }
        return (array)$this->BallotingUnit->find('all', array(
            'fields' => array('BallotingUnit.*', 'Type.name', 'Unit.name'),
            'joins' => array(
                array('table' => 'types', 'alias' => 'Type', 'type' => 'LEFT', 'conditions' => array('Type.id=BallotingUnit.property_type_id')),
                array('table' => 'units', 'alias' => 'Unit', 'type' => 'LEFT', 'conditions' => array('Unit.id=BallotingUnit.unit_id'))
            ),
            'conditions' => $conditions,
            'order' => array('BallotingUnit.id' => 'asc'),
            'limit' => max(1, (int)$limit),
            'recursive' => -1
        ));
    }

    public function index()
    {
        return $this->redirect(array('action' => 'dashboard'));
    }

    public function dashboard()
    {
        $projectId = $this->_selectedProjectId($this->_defaultProjectId());
        $unitConditions = array();
        $allotmentConditions = array();
        if ($projectId > 0) {
            $unitConditions['BallotingUnit.project_id'] = $projectId;
            $allotmentConditions['BallotingAllotment.project_id'] = $projectId;
        }
        $settings = $this->_settingsByProject($projectId);
        $availableUnits = (int)$this->BallotingUnit->find('count', array('conditions' => array_merge($unitConditions, array('BallotingUnit.status' => 'Available')), 'recursive' => -1));
        $allottedUnits = (int)$this->BallotingUnit->find('count', array('conditions' => array_merge($unitConditions, array('BallotingUnit.status' => 'Allotted')), 'recursive' => -1));
        $totalUnits = (int)$this->BallotingUnit->find('count', array('conditions' => $unitConditions, 'recursive' => -1));
        $activeAllotments = (int)$this->BallotingAllotment->find('count', array('conditions' => array_merge($allotmentConditions, array('BallotingAllotment.status' => 'Allotted')), 'recursive' => -1));
        $cancelledAllotments = (int)$this->BallotingAllotment->find('count', array('conditions' => array_merge($allotmentConditions, array('BallotingAllotment.status' => 'Cancelled')), 'recursive' => -1));
        $eligibleCount = ($projectId > 0) ? $this->_eligibleDealCount($projectId, $settings) : 0;

        $recentAllotments = $this->BallotingAllotment->find('all', array(
            'fields' => array(
                'BallotingAllotment.*',
                'Deal.invoice_no', 'Deal.date',
                'Client.name',
                'BallotingUnit.street_address'
            ),
            'joins' => array(
                array('table' => 'deals', 'alias' => 'Deal', 'type' => 'LEFT', 'conditions' => array('Deal.id=BallotingAllotment.deal_id')),
                array('table' => 'clients', 'alias' => 'Client', 'type' => 'LEFT', 'conditions' => array('Client.id=BallotingAllotment.client_id')),
                array('table' => 'balloting_units', 'alias' => 'BallotingUnit', 'type' => 'LEFT', 'conditions' => array('BallotingUnit.id=BallotingAllotment.balloting_unit_id'))
            ),
            'conditions' => $allotmentConditions,
            'order' => array('BallotingAllotment.id' => 'desc'),
            'limit' => 8,
            'recursive' => -1
        ));

        $recentRuns = $this->BallotingRun->find('all', array(
            'conditions' => ($projectId > 0 ? array('BallotingRun.project_id' => $projectId) : array()),
            'order' => array('BallotingRun.id' => 'desc'),
            'limit' => 6,
            'recursive' => -1
        ));

        $this->set(compact(
            'projectId',
            'settings',
            'availableUnits',
            'allottedUnits',
            'totalUnits',
            'activeAllotments',
            'cancelledAllotments',
            'eligibleCount',
            'recentAllotments',
            'recentRuns'
        ));
    }

    public function settings()
    {
        $projectId = $this->_selectedProjectId($this->_defaultProjectId());
        if ($this->_canUseAgencyFilters() && !$this->request->is(array('post', 'put')) && (int)$projectId <= 0)
        {
            $projectList = $this->_projectList();
            foreach ((array)$projectList as $projectListId => $projectTitle)
            {
                if ((int)$projectListId > 0)
                {
                    return $this->redirect(array('action' => 'settings', '?' => array('project_id' => (int)$projectListId)));
                }
            }
        }
        if ($this->request->is(array('post', 'put')))
        {
            $projectId = $this->_normalizeProjectId(isset($this->request->data['BallotingSetting']['project_id']) ? $this->request->data['BallotingSetting']['project_id'] : $projectId);
            if ($projectId <= 0) {
                $this->Session->setFlash(__('Please select an agency first.'), 'flash', array('alert' => 'danger'));
                return $this->redirect(array('action' => 'settings'));
            }
            $existing = $this->BallotingSetting->find('first', array(
                'conditions' => array('BallotingSetting.project_id' => $projectId),
                'recursive' => -1
            ));
            $saveData = (array)$this->request->data['BallotingSetting'];
            $saveData['project_id'] = (int)$projectId;
            $saveData['booking_date_from'] = $this->_normalizeDate(isset($saveData['booking_date_from']) ? $saveData['booking_date_from'] : '');
            $saveData['booking_date_to'] = $this->_normalizeDate(isset($saveData['booking_date_to']) ? $saveData['booking_date_to'] : '');
            $saveData['min_paid_percent'] = max(0, (float)$saveData['min_paid_percent']);
            $saveData['max_overdue_installments'] = max(0, (int)$saveData['max_overdue_installments']);
            $saveData['max_overdue_days'] = max(0, (int)$saveData['max_overdue_days']);
            $saveData['require_active_deal'] = !empty($saveData['require_active_deal']) ? 1 : 0;
            $saveData['selection_order'] = isset($this->_selectionOrderArr[$saveData['selection_order']]) ? $saveData['selection_order'] : 'booking_oldest';
            $saveData['status'] = (!empty($saveData['status']) && $saveData['status'] === 'Inactive') ? 'Inactive' : 'Active';
            if (!empty($existing['BallotingSetting']['id'])) {
                $saveData['id'] = (int)$existing['BallotingSetting']['id'];
            } else {
                $this->BallotingSetting->create();
            }
            if ($this->BallotingSetting->save(array('BallotingSetting' => $saveData))) {
                $this->Session->setFlash(__('Balloting settings saved successfully.'), 'flash', array('alert' => 'success'));
                return $this->redirect(array('action' => 'settings', '?' => array('project_id' => $projectId)));
            }
            $this->Session->setFlash(__('Unable to save settings, please check your input.'), 'flash', array('alert' => 'danger'));
        }
        if (!$this->request->data)
        {
            $this->request->data = $this->_settingsByProject($projectId);
        }
        $this->set('projectId', $projectId);
        $this->set('statusOptions', array('Active' => 'Active', 'Inactive' => 'Inactive'));
    }

    public function units()
    {
        $projectId = $this->_selectedProjectId($this->_defaultProjectId());
        $conditions = array();
        if ($projectId > 0) {
            $conditions[] = array('BallotingUnit.project_id' => $projectId);
        }
        $keyword = isset($this->request->query['keyword']) ? trim((string)$this->request->query['keyword']) : '';
        $status = isset($this->request->query['status']) ? trim((string)$this->request->query['status']) : '';
        $typeId = isset($this->request->query['property_type_id']) ? (int)$this->request->query['property_type_id'] : 0;
        $unitId = isset($this->request->query['unit_id']) ? (int)$this->request->query['unit_id'] : 0;
        $categoryId = isset($this->request->query['category_id']) ? (int)$this->request->query['category_id'] : 0;
        if ($keyword !== '') {
            $conditions[] = array(
                'OR' => array(
                    'BallotingUnit.allotment_no LIKE' => '%'.$keyword.'%',
                    'BallotingUnit.street_address LIKE' => '%'.$keyword.'%',
                    'BallotingUnit.property_no LIKE' => '%'.$keyword.'%',
                    'BallotingUnit.street_no LIKE' => '%'.$keyword.'%',
                    'BallotingUnit.block_name LIKE' => '%'.$keyword.'%'
                )
            );
        }
        if (isset($this->_unitStatusArr[$status])) {
            $conditions[] = array('BallotingUnit.status' => $status);
        } else {
            $status = '';
        }
        if ($typeId > 0) {
            $conditions[] = array('BallotingUnit.property_type_id' => $typeId);
        }
        if ($unitId > 0) {
            $conditions[] = array('BallotingUnit.unit_id' => $unitId);
        }
        if ($categoryId > 0) {
            $conditions[] = array('BallotingUnit.category_id' => $categoryId);
        }
        $this->Paginator->settings = array(
            'fields' => array('BallotingUnit.*', 'Project.name', 'Type.name', 'Unit.name', 'Category.name'),
            'joins' => array(
                array('table' => 'projects', 'alias' => 'Project', 'type' => 'LEFT', 'conditions' => array('Project.id=BallotingUnit.project_id')),
                array('table' => 'types', 'alias' => 'Type', 'type' => 'LEFT', 'conditions' => array('Type.id=BallotingUnit.property_type_id')),
                array('table' => 'units', 'alias' => 'Unit', 'type' => 'LEFT', 'conditions' => array('Unit.id=BallotingUnit.unit_id')),
                array('table' => 'categories', 'alias' => 'Category', 'type' => 'LEFT', 'conditions' => array('Category.id=BallotingUnit.category_id'))
            ),
            'conditions' => $conditions,
            'order' => array('BallotingUnit.id' => 'desc'),
            'limit' => $this->pageLimit,
            'maxLimit' => $this->maxLimit,
            'recursive' => -1
        );
        $rows = $this->Paginator->paginate('BallotingUnit');
        $this->set(compact('rows', 'projectId', 'keyword', 'status', 'typeId', 'unitId', 'categoryId'));
    }

    protected function _loadUnitRow($id)
    {
        $row = $this->BallotingUnit->find('first', array(
            'conditions' => array('BallotingUnit.id' => (int)$id),
            'recursive' => -1
        ));
        if (empty($row['BallotingUnit']['id'])) {
            return array();
        }
        if (!$this->_canUseAgencyFilters() && (int)$row['BallotingUnit']['project_id'] !== (int)$this->agencyId) {
            return array();
        }
        return $row;
    }

    public function unitadd()
    {
        $projectId = $this->_selectedProjectId($this->_defaultProjectId());
        if ($this->_canUseAgencyFilters() && (int)$projectId <= 0) {
            $projectId = $this->_firstAvailableProjectId();
        }
        $addressOptions = $this->_ballotingAddressOptions($projectId);
        if (!$this->request->is('post'))
        {
            $this->request->data = array(
                'BallotingUnit' => array(
                    'project_id' => $projectId,
                    'status' => 'Available'
                )
            );
        }
        if ($this->request->is('post'))
        {
            $save = (array)$this->request->data['BallotingUnit'];
            $save['project_id'] = $this->_normalizeProjectId(isset($save['project_id']) ? $save['project_id'] : $projectId);
            $projectId = (int)$save['project_id'];
            $addressOptions = $this->_ballotingAddressOptions($projectId);
            $save['created_by'] = (int)$this->luserId;
            $save['unit_area'] = (float)(isset($save['unit_area']) ? $save['unit_area'] : 0);
            $save['area_unit'] = '';
            $save['category_id'] = null;
            $save['block_name'] = trim((string)(isset($save['block_name']) ? $save['block_name'] : ''));
            $save['street_address'] = trim((string)(isset($save['street_address']) ? $save['street_address'] : ''));
            if (!isset($this->_unitStatusArr[$save['status']])) {
                $save['status'] = 'Available';
            }
            $this->BallotingUnit->create();
            if ($this->BallotingUnit->save(array('BallotingUnit' => $save))) {
                $this->Session->setFlash(__('Balloting unit added successfully.'), 'flash', array('alert' => 'success'));
                return $this->redirect(array('action' => 'units', '?' => array('project_id' => (int)$save['project_id'])));
            }
            $this->Session->setFlash(__('Unable to add balloting unit.'), 'flash', array('alert' => 'danger'));
        }
        $this->set('projectId', $projectId);
        $this->set('blockOptions', isset($addressOptions['blockOptions']) ? $addressOptions['blockOptions'] : array());
        $this->set('streetOptions', isset($addressOptions['streetOptions']) ? $addressOptions['streetOptions'] : array());
        $this->set('addressOptionsByProject', $this->_addressOptionsByProject($projectId));
    }

    public function addressoptions()
    {
        $this->autoRender = false;
        if (method_exists($this->response, 'type')) {
            $this->response->type('json');
        } else {
            $this->response->header('Content-Type', 'application/json');
        }
        $projectId = $this->_selectedProjectId($this->_defaultProjectId());
        if ($this->_canUseAgencyFilters() && (int)$projectId <= 0) {
            $projectId = $this->_firstAvailableProjectId();
        }
        $addressOptions = $this->_ballotingAddressOptions($projectId);
        $payload = array(
            'ok' => true,
            'project_id' => (int)$projectId,
            'blockOptions' => isset($addressOptions['blockOptions']) ? (array)$addressOptions['blockOptions'] : array(),
            'streetOptions' => isset($addressOptions['streetOptions']) ? (array)$addressOptions['streetOptions'] : array()
        );
        echo json_encode($payload);
    }

    public function unitedit($id = null)
    {
        $row = $this->_loadUnitRow($id);
        if (empty($row['BallotingUnit']['id'])) {
            $this->Session->setFlash(__('Invalid unit selected.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'units'));
        }
        if ($this->request->is(array('post', 'put')))
        {
            $save = (array)$this->request->data['BallotingUnit'];
            $save['id'] = (int)$row['BallotingUnit']['id'];
            $save['project_id'] = $this->_normalizeProjectId(isset($save['project_id']) ? $save['project_id'] : $row['BallotingUnit']['project_id']);
            $projectId = (int)$save['project_id'];
            $save['unit_area'] = (float)(isset($save['unit_area']) ? $save['unit_area'] : 0);
            $save['area_unit'] = '';
            $save['category_id'] = null;
            $save['block_name'] = trim((string)(isset($save['block_name']) ? $save['block_name'] : ''));
            $save['street_address'] = trim((string)(isset($save['street_address']) ? $save['street_address'] : ''));
            if (!isset($this->_unitStatusArr[$save['status']])) {
                $save['status'] = 'Available';
            }
            if ($this->BallotingUnit->save(array('BallotingUnit' => $save))) {
                $this->Session->setFlash(__('Balloting unit updated successfully.'), 'flash', array('alert' => 'success'));
                return $this->redirect(array('action' => 'units', '?' => array('project_id' => (int)$save['project_id'])));
            }
            $this->Session->setFlash(__('Unable to update unit.'), 'flash', array('alert' => 'danger'));
        }
        if (!$this->request->data) {
            $this->request->data = $row;
        }
        $requestedProjectId = $this->_selectedProjectId(isset($row['BallotingUnit']['project_id']) ? $row['BallotingUnit']['project_id'] : 0);
        $activeProjectId = isset($projectId) ? (int)$projectId : (int)$requestedProjectId;
        if ((int)$activeProjectId <= 0) {
            $activeProjectId = (int)$row['BallotingUnit']['project_id'];
        }
        if (!$this->request->is(array('post', 'put'))) {
            $this->request->data['BallotingUnit']['project_id'] = $activeProjectId;
        }
        $addressOptions = $this->_ballotingAddressOptions($activeProjectId);
        $this->set('projectId', $activeProjectId);
        $this->set('blockOptions', isset($addressOptions['blockOptions']) ? $addressOptions['blockOptions'] : array());
        $this->set('streetOptions', isset($addressOptions['streetOptions']) ? $addressOptions['streetOptions'] : array());
        $this->set('addressOptionsByProject', $this->_addressOptionsByProject($activeProjectId));
    }

    public function unitview($id = null)
    {
        $row = $this->_loadUnitRow($id);
        if (empty($row['BallotingUnit']['id'])) {
            $this->Session->setFlash(__('Invalid unit selected.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'units'));
        }
        $history = $this->BallotingAllotment->find('all', array(
            'fields' => array('BallotingAllotment.*', 'Deal.invoice_no', 'Client.name'),
            'joins' => array(
                array('table' => 'deals', 'alias' => 'Deal', 'type' => 'LEFT', 'conditions' => array('Deal.id=BallotingAllotment.deal_id')),
                array('table' => 'clients', 'alias' => 'Client', 'type' => 'LEFT', 'conditions' => array('Client.id=BallotingAllotment.client_id'))
            ),
            'conditions' => array('BallotingAllotment.balloting_unit_id' => (int)$row['BallotingUnit']['id']),
            'order' => array('BallotingAllotment.id' => 'desc'),
            'recursive' => -1
        ));
        $this->set('row', $row);
        $this->set('history', $history);
    }

    public function unitdelete($id = null)
    {
        $row = $this->_loadUnitRow($id);
        if (empty($row['BallotingUnit']['id'])) {
            $this->Session->setFlash(__('Invalid unit selected.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'units'));
        }
        $activeAllotments = (int)$this->BallotingAllotment->find('count', array(
            'conditions' => array(
                'BallotingAllotment.balloting_unit_id' => (int)$row['BallotingUnit']['id'],
                'BallotingAllotment.status' => 'Allotted'
            ),
            'recursive' => -1
        ));
        if ($activeAllotments > 0) {
            $this->Session->setFlash(__('This unit is in active allotment and cannot be deleted.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'units', '?' => array('project_id' => (int)$row['BallotingUnit']['project_id'])));
        }
        $this->BallotingUnit->delete((int)$row['BallotingUnit']['id']);
        $this->Session->setFlash(__('Balloting unit deleted successfully.'), 'flash', array('alert' => 'success'));
        return $this->redirect(array('action' => 'units', '?' => array('project_id' => (int)$row['BallotingUnit']['project_id'])));
    }

    protected function _headerKeyMap($headers)
    {
        $map = array();
        foreach ((array)$headers as $idx => $header)
        {
            $key = strtolower(trim((string)$header));
            $key = preg_replace('/[^a-z0-9]+/', '_', $key);
            $key = trim($key, '_');
            if ($key !== '') {
                $map[$key] = (int)$idx;
            }
        }
        return $map;
    }

    protected function _rowValueByKeys($row, $headerMap, $keys = array())
    {
        foreach ((array)$keys as $k) {
            if (isset($headerMap[$k])) {
                $idx = (int)$headerMap[$k];
                if (isset($row[$idx])) {
                    return trim((string)$row[$idx]);
                }
            }
        }
        return '';
    }

    protected function _nameIdLookup($rows)
    {
        $map = array();
        foreach ((array)$rows as $id => $name)
        {
            $nameKey = strtolower(trim((string)$name));
            if ($nameKey !== '') {
                $map[$nameKey] = (int)$id;
            }
        }
        return $map;
    }

    public function import()
    {
        $projectId = $this->_selectedProjectId($this->_defaultProjectId());
        $importSummary = array();
        if ($this->request->is('post'))
        {
            $postedProjectId = $projectId;
            if (isset($this->request->data['project_id'])) {
                $postedProjectId = $this->request->data['project_id'];
            }
            if (isset($this->request->data['Balloting']['project_id'])) {
                $postedProjectId = $this->request->data['Balloting']['project_id'];
            }
            $projectId = $this->_normalizeProjectId($postedProjectId);
            $upload = isset($this->request->data['Balloting']['csv_file']) ? $this->request->data['Balloting']['csv_file'] : array();
            if ($projectId <= 0) {
                $this->Session->setFlash(__('Please select agency before import.'), 'flash', array('alert' => 'danger'));
            } elseif (empty($upload['tmp_name'])) {
                $this->Session->setFlash(__('Please upload a CSV file.'), 'flash', array('alert' => 'danger'));
            } else {
                $handle = @fopen($upload['tmp_name'], 'r');
                if (!$handle) {
                    $this->Session->setFlash(__('Unable to read uploaded CSV file.'), 'flash', array('alert' => 'danger'));
                } else {
                    $headers = fgetcsv($handle);
                    $headerMap = $this->_headerKeyMap($headers);
                    $typeLookup = $this->_nameIdLookup($this->_propertyTypeList());
                    $unitLookup = $this->_nameIdLookup($this->_unitList());
                    $inserted = 0;
                    $updated = 0;
                    $skipped = 0;
                    while (($row = fgetcsv($handle)) !== false)
                    {
                        if (count(array_filter($row, function ($v) { return trim((string)$v) !== ''; })) === 0) {
                            continue;
                        }
                        $allotmentNo = $this->_rowValueByKeys($row, $headerMap, array('allotment_no', 'allotment_number', 'ballot_no', 'number'));
                        if ($allotmentNo === '') {
                            $skipped++;
                            continue;
                        }
                        $propertyTypeName = strtolower($this->_rowValueByKeys($row, $headerMap, array('property_type', 'type', 'property_type_name')));
                        $unitName = strtolower($this->_rowValueByKeys($row, $headerMap, array('unit', 'unit_name', 'size_unit')));
                        $unitAreaRaw = $this->_rowValueByKeys($row, $headerMap, array('area', 'unit_area', 'size'));
                        $saveData = array(
                            'project_id' => (int)$projectId,
                            'category_id' => null,
                            'property_type_id' => ($propertyTypeName !== '' && isset($typeLookup[$propertyTypeName])) ? (int)$typeLookup[$propertyTypeName] : null,
                            'unit_id' => ($unitName !== '' && isset($unitLookup[$unitName])) ? (int)$unitLookup[$unitName] : null,
                            'unit_area' => ($unitAreaRaw === '' ? 0 : (float)$unitAreaRaw),
                            'area_unit' => '',
                            'allotment_no' => $allotmentNo,
                            'property_no' => $this->_rowValueByKeys($row, $headerMap, array('property_no', 'plot_no', 'house_no', 'unit_no')),
                            'street_no' => $this->_rowValueByKeys($row, $headerMap, array('street_no', 'street_number')),
                            'block_name' => $this->_rowValueByKeys($row, $headerMap, array('block_name', 'block')),
                            'street_address' => $this->_rowValueByKeys($row, $headerMap, array('street_address', 'address', 'full_address')),
                            'status' => 'Available',
                            'notes' => $this->_rowValueByKeys($row, $headerMap, array('notes', 'remarks')),
                            'imported_at' => date('Y-m-d H:i:s'),
                            'created_by' => (int)$this->luserId
                        );
                        $existing = $this->BallotingUnit->find('first', array(
                            'conditions' => array(
                                'BallotingUnit.project_id' => (int)$projectId,
                                'BallotingUnit.allotment_no' => $allotmentNo
                            ),
                            'recursive' => -1
                        ));
                        if (!empty($existing['BallotingUnit']['id'])) {
                            $saveData['id'] = (int)$existing['BallotingUnit']['id'];
                        } else {
                            $this->BallotingUnit->create();
                        }
                        if ($this->BallotingUnit->save(array('BallotingUnit' => $saveData))) {
                            if (!empty($existing['BallotingUnit']['id'])) {
                                $updated++;
                            } else {
                                $inserted++;
                            }
                        } else {
                            $skipped++;
                        }
                    }
                    fclose($handle);
                    $importSummary = compact('inserted', 'updated', 'skipped');
                    $this->Session->setFlash(__('CSV import completed. Inserted: %s, Updated: %s, Skipped: %s', $inserted, $updated, $skipped), 'flash', array('alert' => 'success'));
                }
            }
        }
        $this->set(compact('projectId', 'importSummary'));
    }

    public function importsample()
    {
        $this->autoRender = false;
        $rows = array(
            array('allotment_no', 'property_type', 'unit', 'area', 'property_no', 'street_no', 'block_name', 'street_address', 'notes'),
            array('BLT-001', 'Plot', 'Marla', '5', 'P-12', '3', 'Block A', 'Street 3, Block A', 'Sample row 1'),
            array('BLT-002', 'Flat', 'Sq Ft', '1200', 'F-7', '10', 'Block B', 'Street 10, Block B', 'Sample row 2')
        );
        $stream = fopen('php://temp', 'r+');
        foreach ($rows as $row)
        {
            fputcsv($stream, $row);
        }
        rewind($stream);
        $csvBody = stream_get_contents($stream);
        fclose($stream);
        $fileName = 'balloting_units_sample_' . date('Ymd_His') . '.csv';
        $this->response->type('csv');
        $this->response->download($fileName);
        $this->response->body($csvBody);
        return $this->response;
    }

    protected function _resolveReportAgencyHeader($selectedProjectId = 0)
    {
        $reportAgency = array();
        $reportAgencyId = 0;
        if ((int)$selectedProjectId > 0) {
            $reportAgencyId = (int)$selectedProjectId;
        } elseif (!$this->_canUseAgencyFilters() && (int)$this->agencyId > 0) {
            $reportAgencyId = (int)$this->agencyId;
        }
        if ($reportAgencyId <= 0) {
            return $reportAgency;
        }

        $this->loadModel('Agency');
        $agencyFields = array('Agency.id');
        foreach (array('name', 'photo', 'address', 'phone', 'mobile', 'email') as $agencyHeaderField)
        {
            if ($this->Agency->hasField($agencyHeaderField)) {
                $agencyFields[] = 'Agency.' . $agencyHeaderField;
            }
        }
        if (count($agencyFields) <= 1) {
            $agencyFields[] = 'Agency.id';
        }
        $agencyRow = $this->Agency->find('first', array(
            'fields' => $agencyFields,
            'conditions' => array('Agency.id' => $reportAgencyId),
            'recursive' => -1
        ));
        if (empty($agencyRow['Agency'])) {
            return $reportAgency;
        }

        $reportAgency = (array)$agencyRow['Agency'];
        if (empty($reportAgency['address']) || empty($reportAgency['phone']) || empty($reportAgency['mobile']) || empty($reportAgency['email']))
        {
            $this->loadModel('User');
            $userFields = array('User.id');
            foreach (array('address', 'phone', 'mobile', 'email') as $userHeaderField)
            {
                if ($this->User->hasField($userHeaderField)) {
                    $userFields[] = 'User.' . $userHeaderField;
                }
            }
            $agencyUser = $this->User->find('first', array(
                'fields' => $userFields,
                'conditions' => array('User.project_id' => $reportAgencyId),
                'order' => array('User.id' => 'asc'),
                'recursive' => -1
            ));
            if (!empty($agencyUser['User']))
            {
                if (empty($reportAgency['address']) && !empty($agencyUser['User']['address']))$reportAgency['address'] = $agencyUser['User']['address'];
                if (empty($reportAgency['phone']) && !empty($agencyUser['User']['phone']))$reportAgency['phone'] = $agencyUser['User']['phone'];
                if (empty($reportAgency['mobile']) && !empty($agencyUser['User']['mobile']))$reportAgency['mobile'] = $agencyUser['User']['mobile'];
                if (empty($reportAgency['email']) && !empty($agencyUser['User']['email']))$reportAgency['email'] = $agencyUser['User']['email'];
            }
        }
        return $reportAgency;
    }

    protected function _exportAllotmentsCsv($queryOptions = array())
    {
        $queryOptions = (array)$queryOptions;
        if (!isset($queryOptions['recursive'])) {
            $queryOptions['recursive'] = -1;
        }
        unset($queryOptions['limit'], $queryOptions['maxLimit'], $queryOptions['page']);
        $rows = (array)$this->BallotingAllotment->find('all', $queryOptions);

        $stream = fopen('php://temp', 'r+');
        fputcsv($stream, array(
            'Allotment Number',
            'Allotment Date',
            'Agency',
            'Deal Invoice',
            'Deal ID',
            'Client',
            'Street Address',
            'Paid Percent',
            'Overdue Installments',
            'Overdue Days',
            'Status'
        ));
        foreach ($rows as $row)
        {
            $allotmentDate = '';
            if (!empty($row['BallotingAllotment']['allotment_date']) && $row['BallotingAllotment']['allotment_date'] !== '0000-00-00') {
                $allotmentDate = date('d-M-Y', strtotime($row['BallotingAllotment']['allotment_date']));
            }
            fputcsv($stream, array(
                isset($row['BallotingAllotment']['allotment_no']) ? $row['BallotingAllotment']['allotment_no'] : '',
                $allotmentDate,
                !empty($row['Project']['name']) ? $row['Project']['name'] : '',
                !empty($row['Deal']['invoice_no']) ? $row['Deal']['invoice_no'] : '',
                !empty($row['BallotingAllotment']['deal_id']) ? (int)$row['BallotingAllotment']['deal_id'] : '',
                !empty($row['Client']['name']) ? $row['Client']['name'] : '',
                !empty($row['BallotingAllotment']['street_address']) ? $row['BallotingAllotment']['street_address'] : '',
                isset($row['BallotingAllotment']['paid_percent']) ? $row['BallotingAllotment']['paid_percent'] : '',
                isset($row['BallotingAllotment']['overdue_installments']) ? (int)$row['BallotingAllotment']['overdue_installments'] : 0,
                isset($row['BallotingAllotment']['overdue_days']) ? (int)$row['BallotingAllotment']['overdue_days'] : 0,
                !empty($row['BallotingAllotment']['status']) ? $row['BallotingAllotment']['status'] : ''
            ));
        }
        rewind($stream);
        $csvBody = stream_get_contents($stream);
        fclose($stream);

        $fileName = 'balloting_allotments_' . date('Ymd_His') . '.csv';
        $this->response->type('csv');
        $this->response->download($fileName);
        $this->response->body($csvBody);
        return $this->response;
    }

    public function allotments($dealId = null)
    {
        $dealId = (int)$dealId;
        if ($dealId <= 0 && isset($this->request->query['deal_id'])) {
            $dealId = (int)$this->request->query['deal_id'];
        }
        $projectId = $this->_selectedProjectId($this->_defaultProjectId());
        $conditions = array();
        if ($projectId > 0) {
            $conditions[] = array('BallotingAllotment.project_id' => $projectId);
        }
        if ($dealId > 0) {
            $conditions[] = array('BallotingAllotment.deal_id' => $dealId);
        }
        $status = isset($this->request->query['status']) ? trim((string)$this->request->query['status']) : '';
        if (isset($this->_allotmentStatusArr[$status])) {
            $conditions[] = array('BallotingAllotment.status' => $status);
        } else {
            $status = '';
        }
        $startDateInput = isset($this->request->query['start_date']) ? trim((string)$this->request->query['start_date']) : '';
        $endDateInput = isset($this->request->query['end_date']) ? trim((string)$this->request->query['end_date']) : '';
        $startDate = $this->_normalizeDate($startDateInput);
        $endDate = $this->_normalizeDate($endDateInput);
        if ($startDate !== '') {
            $conditions[] = array('BallotingAllotment.allotment_date >=' => $startDate);
        }
        if ($endDate !== '') {
            $conditions[] = array('BallotingAllotment.allotment_date <=' => $endDate);
        }
        $keyword = isset($this->request->query['keyword']) ? trim((string)$this->request->query['keyword']) : '';
        if ($keyword !== '') {
            $conditions[] = array(
                'OR' => array(
                    'BallotingAllotment.allotment_no LIKE' => '%'.$keyword.'%',
                    'BallotingAllotment.street_address LIKE' => '%'.$keyword.'%',
                    'Deal.invoice_no LIKE' => '%'.$keyword.'%',
                    'Client.name LIKE' => '%'.$keyword.'%'
                )
            );
        }
        $queryOptions = array(
            'fields' => array(
                'BallotingAllotment.*',
                'Deal.invoice_no', 'Deal.date',
                'Client.name',
                'Project.name'
            ),
            'joins' => array(
                array('table' => 'deals', 'alias' => 'Deal', 'type' => 'LEFT', 'conditions' => array('Deal.id=BallotingAllotment.deal_id')),
                array('table' => 'clients', 'alias' => 'Client', 'type' => 'LEFT', 'conditions' => array('Client.id=BallotingAllotment.client_id')),
                array('table' => 'balloting_units', 'alias' => 'BallotingUnit', 'type' => 'LEFT', 'conditions' => array('BallotingUnit.id=BallotingAllotment.balloting_unit_id')),
                array('table' => 'projects', 'alias' => 'Project', 'type' => 'LEFT', 'conditions' => array('Project.id=BallotingAllotment.project_id'))
            ),
            'conditions' => $conditions,
            'order' => array('BallotingAllotment.id' => 'desc'),
            'recursive' => -1
        );
        $exportType = isset($this->request->query['export']) ? strtolower(trim((string)$this->request->query['export'])) : '';
        if ($exportType === 'csv') {
            return $this->_exportAllotmentsCsv($queryOptions);
        }
        $this->Paginator->settings = $queryOptions;
        $this->Paginator->settings['limit'] = $this->pageLimit;
        $this->Paginator->settings['maxLimit'] = $this->maxLimit;
        $rows = $this->Paginator->paginate('BallotingAllotment');
        $reportAgency = $this->_resolveReportAgencyHeader($projectId);
        $this->set(compact('rows', 'projectId', 'dealId', 'status', 'keyword', 'startDateInput', 'endDateInput', 'reportAgency'));
    }

    protected function _dealRowForAssign($dealId, $projectId)
    {
        $filters = array('deal_id' => (int)$dealId);
        $settings = $this->_settingsByProject($projectId);
        $rows = $this->_eligibleDeals($projectId, $settings, $filters, 2, false);
        if (!empty($rows[0]['Deal']['id'])) {
            return $rows[0];
        }
        return array();
    }

    public function assign($dealId = null)
    {
        $dealId = (int)$dealId;
        if ($dealId <= 0 && isset($this->request->data['BallotingAllotment']['deal_id'])) {
            $dealId = (int)$this->request->data['BallotingAllotment']['deal_id'];
        }
        if ($dealId <= 0 && isset($this->request->query['deal_id'])) {
            $dealId = (int)$this->request->query['deal_id'];
        }
        if ($dealId <= 0) {
            return $this->redirect(array('action' => 'manualallotment'));
        }
        $dealBase = $this->Deal->find('first', array(
            'fields' => array('Deal.id', 'Property.project_id'),
            'joins' => array(
                array('table' => 'properties', 'alias' => 'Property', 'type' => 'INNER', 'conditions' => array('Property.id=Deal.property_id'))
            ),
            'conditions' => array('Deal.id' => $dealId),
            'recursive' => -1
        ));
        $projectId = 0;
        if (!empty($dealBase['Property']['project_id'])) {
            $projectId = $this->_normalizeProjectId((int)$dealBase['Property']['project_id']);
        }
        return $this->redirect(array('action' => 'manualallotment', '?' => array('project_id' => (int)$projectId, 'deal_id' => (int)$dealId)));
    }

    public function cancelallotment($id = null)
    {
        $row = $this->BallotingAllotment->find('first', array(
            'conditions' => array('BallotingAllotment.id' => (int)$id),
            'recursive' => -1
        ));
        if (empty($row['BallotingAllotment']['id'])) {
            $this->Session->setFlash(__('Invalid allotment selected.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'allotments'));
        }
        if (!$this->_canUseAgencyFilters() && (int)$row['BallotingAllotment']['project_id'] !== (int)$this->agencyId) {
            $this->Session->setFlash(__('You do not have permission for this allotment.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'allotments'));
        }
        if ($row['BallotingAllotment']['status'] !== 'Allotted') {
            $this->Session->setFlash(__('This allotment is already cancelled.'), 'flash', array('alert' => 'warning'));
            return $this->redirect(array('action' => 'allotments'));
        }
        $db = $this->BallotingAllotment->getDataSource();
        $db->begin();
        try
        {
            $this->BallotingAllotment->id = (int)$row['BallotingAllotment']['id'];
            if (!$this->BallotingAllotment->saveField('status', 'Cancelled')) {
                throw new Exception(__('Unable to cancel allotment.'));
            }
            $unitId = (int)$row['BallotingAllotment']['balloting_unit_id'];
            if ($unitId > 0) {
                $otherAllottedCount = (int)$this->BallotingAllotment->find('count', array(
                    'conditions' => array(
                        'BallotingAllotment.balloting_unit_id' => $unitId,
                        'BallotingAllotment.status' => 'Allotted'
                    ),
                    'recursive' => -1
                ));
                if ($otherAllottedCount <= 0) {
                    $this->BallotingUnit->id = $unitId;
                    $this->BallotingUnit->saveField('status', 'Available');
                }
            }
            $db->commit();
            $this->Session->setFlash(__('Allotment cancelled and unit released.'), 'flash', array('alert' => 'success'));
        }
        catch (Exception $e)
        {
            $db->rollback();
            $this->Session->setFlash($e->getMessage(), 'flash', array('alert' => 'danger'));
        }
        return $this->redirect(array('action' => 'allotments'));
    }

    public function deletecancelledallotment($id = null)
    {
        $returnProjectId = $this->_selectedProjectId($this->_defaultProjectId());
        if (!$this->_canUseAgencyFilters())
        {
            $this->Session->setFlash(__('Only super admin can delete cancelled allotments.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'allotments', '?' => array('project_id' => (int)$returnProjectId)));
        }

        $row = $this->BallotingAllotment->find('first', array(
            'conditions' => array('BallotingAllotment.id' => (int)$id),
            'recursive' => -1
        ));
        if (empty($row['BallotingAllotment']['id']))
        {
            $this->Session->setFlash(__('Invalid allotment selected.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'allotments', '?' => array('project_id' => (int)$returnProjectId)));
        }
        if ((string)$row['BallotingAllotment']['status'] !== 'Cancelled')
        {
            $this->Session->setFlash(__('Only cancelled allotments can be deleted.'), 'flash', array('alert' => 'warning'));
            return $this->redirect(array('action' => 'allotments', '?' => array('project_id' => (int)$returnProjectId)));
        }

        $projectId = !empty($row['BallotingAllotment']['project_id']) ? (int)$row['BallotingAllotment']['project_id'] : (int)$returnProjectId;
        if ($this->BallotingAllotment->delete((int)$row['BallotingAllotment']['id']))
        {
            $this->Session->setFlash(__('Cancelled allotment deleted permanently.'), 'flash', array('alert' => 'success'));
        }
        else
        {
            $this->Session->setFlash(__('Unable to delete cancelled allotment.'), 'flash', array('alert' => 'danger'));
        }
        return $this->redirect(array('action' => 'allotments', '?' => array('project_id' => (int)$projectId)));
    }

    public function run()
    {
        $projectId = $this->_selectedProjectId($this->_defaultProjectId());
        $settings = $this->_settingsByProject($projectId);
        $runFilters = array(
            'property_type_id' => 0,
            'unit_id' => 0,
            'category_id' => 0,
            'area_min' => '',
            'area_max' => ''
        );
        $eligiblePreview = array();
        $availablePreview = array();
        $runSummary = array();
        if ($this->request->is(array('post', 'put')))
        {
            $projectId = $this->_normalizeProjectId(isset($this->request->data['BallotingRun']['project_id']) ? $this->request->data['BallotingRun']['project_id'] : $projectId);
            if ($projectId <= 0)
            {
                $this->Session->setFlash(__('Please select agency before running balloting.'), 'flash', array('alert' => 'danger'));
                $recentRuns = array();
                $this->set(compact('projectId', 'settings', 'runFilters', 'eligiblePreview', 'availablePreview', 'runSummary', 'recentRuns'));
                return;
            }
            $settings = $this->_settingsByProject($projectId);
            $runFilters['property_type_id'] = isset($this->request->data['BallotingRun']['property_type_id']) ? (int)$this->request->data['BallotingRun']['property_type_id'] : 0;
            $runFilters['unit_id'] = isset($this->request->data['BallotingRun']['unit_id']) ? (int)$this->request->data['BallotingRun']['unit_id'] : 0;
            $runFilters['category_id'] = isset($this->request->data['BallotingRun']['category_id']) ? (int)$this->request->data['BallotingRun']['category_id'] : 0;
            $runFilters['area_min'] = isset($this->request->data['BallotingRun']['area_min']) ? trim((string)$this->request->data['BallotingRun']['area_min']) : '';
            $runFilters['area_max'] = isset($this->request->data['BallotingRun']['area_max']) ? trim((string)$this->request->data['BallotingRun']['area_max']) : '';
            $runAction = isset($this->request->data['BallotingRun']['run_action']) ? trim((string)$this->request->data['BallotingRun']['run_action']) : 'preview';
            $eligiblePreview = $this->_eligibleDeals($projectId, $settings, $runFilters, 300, true);
            $availablePreview = $this->_availableUnits($projectId, $runFilters, 3000);
            if ($runAction === 'execute')
            {
                $title = isset($this->request->data['BallotingRun']['title']) ? trim((string)$this->request->data['BallotingRun']['title']) : '';
                if ($title === '') {
                    $title = 'Balloting Run '.date('d-M-Y H:i');
                }
                $this->BallotingRun->create();
                $runData = array(
                    'project_id' => (int)$projectId,
                    'title' => $title,
                    'filters_json' => json_encode($runFilters),
                    'total_eligible' => count($eligiblePreview),
                    'total_allotted' => 0,
                    'total_skipped' => 0,
                    'run_date' => date('Y-m-d'),
                    'notes' => isset($this->request->data['BallotingRun']['notes']) ? (string)$this->request->data['BallotingRun']['notes'] : '',
                    'created_by' => (int)$this->luserId
                );
                if (!$this->BallotingRun->save(array('BallotingRun' => $runData))) {
                    $this->Session->setFlash(__('Unable to start run record.'), 'flash', array('alert' => 'danger'));
                } else {
                    $runId = (int)$this->BallotingRun->id;
                    $pool = $availablePreview;
                    if (isset($settings['BallotingSetting']['selection_order']) && $settings['BallotingSetting']['selection_order'] === 'random') {
                        shuffle($pool);
                    }
                    $assigned = 0;
                    $skipped = 0;
                    foreach ($eligiblePreview as $dealRow)
                    {
                        $matchedIndex = -1;
                        for ($i = 0; $i < count($pool); $i++) {
                            if ($this->_unitMatchesDeal($pool[$i], $dealRow)) {
                                $matchedIndex = $i;
                                break;
                            }
                        }
                        if ($matchedIndex < 0) {
                            $skipped++;
                            continue;
                        }
                        $selectedUnitRow = $pool[$matchedIndex];
                        $result = $this->_assignUnitToDeal($selectedUnitRow, $dealRow, $runId, 'Auto assigned by run #'.$runId);
                        if (!empty($result['ok'])) {
                            $assigned++;
                            array_splice($pool, $matchedIndex, 1);
                        } else {
                            $skipped++;
                        }
                    }
                    $this->BallotingRun->id = $runId;
                    $this->BallotingRun->save(array('BallotingRun' => array(
                        'id' => $runId,
                        'total_allotted' => $assigned,
                        'total_skipped' => $skipped
                    )));
                    $runSummary = array(
                        'run_id' => $runId,
                        'eligible' => count($eligiblePreview),
                        'assigned' => $assigned,
                        'skipped' => $skipped
                    );
                    $this->Session->setFlash(__('Run completed. Eligible: %s, Assigned: %s, Skipped: %s', count($eligiblePreview), $assigned, $skipped), 'flash', array('alert' => 'success'));
                    $eligiblePreview = $this->_eligibleDeals($projectId, $settings, $runFilters, 300, true);
                    $availablePreview = $this->_availableUnits($projectId, $runFilters, 3000);
                }
            }
        }
        if (!$this->request->data)
        {
            $this->request->data['BallotingRun'] = array(
                'project_id' => $projectId,
                'run_action' => 'preview'
            );
        }
        $recentRuns = $this->BallotingRun->find('all', array(
            'conditions' => ($projectId > 0 ? array('BallotingRun.project_id' => $projectId) : array()),
            'order' => array('BallotingRun.id' => 'desc'),
            'limit' => 8,
            'recursive' => -1
        ));
        $this->set(compact('projectId', 'settings', 'runFilters', 'eligiblePreview', 'availablePreview', 'runSummary', 'recentRuns'));
    }

    public function manualallotment()
    {
        $projectId = $this->_selectedProjectId($this->_defaultProjectId());
        if ($this->_canUseAgencyFilters() && (int)$projectId <= 0) {
            $projectId = $this->_firstAvailableProjectId();
        }
        $preselectedDealId = isset($this->request->query['deal_id']) ? (int)$this->request->query['deal_id'] : 0;
        $dealKeyword = isset($this->request->query['deal_keyword']) ? trim((string)$this->request->query['deal_keyword']) : '';
        $unitKeyword = isset($this->request->query['unit_keyword']) ? trim((string)$this->request->query['unit_keyword']) : '';

        if ($this->request->is(array('post', 'put')))
        {
            $projectId = $this->_normalizeProjectId(isset($this->request->data['BallotingAllotment']['project_id']) ? $this->request->data['BallotingAllotment']['project_id'] : $projectId);
            $dealId = isset($this->request->data['BallotingAllotment']['deal_id']) ? (int)$this->request->data['BallotingAllotment']['deal_id'] : 0;
            $preselectedDealId = (int)$dealId;
            $unitId = isset($this->request->data['BallotingAllotment']['balloting_unit_id']) ? (int)$this->request->data['BallotingAllotment']['balloting_unit_id'] : 0;
            $notes = isset($this->request->data['BallotingAllotment']['notes']) ? (string)$this->request->data['BallotingAllotment']['notes'] : '';
            if ($projectId <= 0)
            {
                $this->Session->setFlash(__('Please select agency before manual allotment.'), 'flash', array('alert' => 'danger'));
            }
            else
            {
                $dealRow = $this->_manualDealRow($dealId, $projectId);
                if (empty($dealRow['Deal']['id']))
                {
                    $this->Session->setFlash(__('Please select a valid deal that has no active allotment.'), 'flash', array('alert' => 'danger'));
                }
                else
                {
                    $unitRow = $this->BallotingUnit->find('first', array(
                        'fields' => array('BallotingUnit.*', 'Type.name', 'Unit.name'),
                        'joins' => array(
                            array('table' => 'types', 'alias' => 'Type', 'type' => 'LEFT', 'conditions' => array('Type.id=BallotingUnit.property_type_id')),
                            array('table' => 'units', 'alias' => 'Unit', 'type' => 'LEFT', 'conditions' => array('Unit.id=BallotingUnit.unit_id'))
                        ),
                        'conditions' => array(
                            'BallotingUnit.id' => (int)$unitId,
                            'BallotingUnit.project_id' => (int)$projectId,
                            'BallotingUnit.status' => 'Available'
                        ),
                        'recursive' => -1
                    ));
                    if (empty($unitRow['BallotingUnit']['id']))
                    {
                        $this->Session->setFlash(__('Please select a valid available unit.'), 'flash', array('alert' => 'danger'));
                    }
                    else
                    {
                        $result = $this->_assignUnitToDeal($unitRow, $dealRow, 0, $notes);
                        if (!empty($result['ok']))
                        {
                            $this->Session->setFlash(__('Manual allotment saved successfully.'), 'flash', array('alert' => 'success'));
                            return $this->redirect(array('action' => 'allotments', '?' => array('project_id' => (int)$projectId, 'deal_id' => (int)$dealId)));
                        }
                        $this->Session->setFlash(isset($result['message']) ? $result['message'] : __('Unable to save manual allotment.'), 'flash', array('alert' => 'danger'));
                    }
                }
            }
        }

        $dealRows = ($projectId > 0) ? $this->_manualDeals($projectId, $dealKeyword, 1000) : array();
        $unitRows = ($projectId > 0) ? $this->_manualAvailableUnits($projectId, $unitKeyword, 3000) : array();
        $dealOptions = array();
        foreach ($dealRows as $dealRow)
        {
            if (empty($dealRow['Deal']['id'])) {
                continue;
            }
            $dealIdKey = (int)$dealRow['Deal']['id'];
            $dealOptions[$dealIdKey] =
                trim((string)$dealRow['Deal']['invoice_no']).' | '.
                trim((string)$dealRow['Client']['name']).' | '.
                trim((string)$dealRow['Property']['name']).' | '.
                __('Paid').': '.number_format((float)(isset($dealRow[0]['paid_percent']) ? $dealRow[0]['paid_percent'] : 0), 2).'%';
        }
        if ($preselectedDealId > 0 && !isset($dealOptions[$preselectedDealId]) && $projectId > 0)
        {
            $singleDeal = $this->_manualDealRow($preselectedDealId, $projectId);
            if (!empty($singleDeal['Deal']['id']))
            {
                $dealOptions[$preselectedDealId] =
                    trim((string)$singleDeal['Deal']['invoice_no']).' | '.
                    trim((string)$singleDeal['Client']['name']).' | '.
                    trim((string)$singleDeal['Property']['name']).' | '.
                    __('Paid').': '.number_format((float)(isset($singleDeal[0]['paid_percent']) ? $singleDeal[0]['paid_percent'] : 0), 2).'%';
            }
        }
        $unitOptions = array();
        foreach ($unitRows as $unitRow)
        {
            if (empty($unitRow['BallotingUnit']['id'])) {
                continue;
            }
            $u = $unitRow['BallotingUnit'];
            $unitOptions[(int)$u['id']] =
                trim((string)$u['allotment_no']).' | '.
                trim((string)$u['street_address']).' | '.
                trim((string)(isset($unitRow['Type']['name']) ? $unitRow['Type']['name'] : '')).'/'.trim((string)(isset($unitRow['Unit']['name']) ? $unitRow['Unit']['name'] : '')).' | '.
                __('Area').': '.(float)$u['unit_area'];
        }
        if (!$this->request->data)
        {
            $this->request->data = array(
                'BallotingAllotment' => array(
                    'project_id' => (int)$projectId,
                    'deal_id' => (int)$preselectedDealId,
                    'balloting_unit_id' => 0
                )
            );
        }
        $this->set(compact('projectId', 'dealKeyword', 'unitKeyword', 'dealOptions', 'unitOptions'));
    }
}
?>
