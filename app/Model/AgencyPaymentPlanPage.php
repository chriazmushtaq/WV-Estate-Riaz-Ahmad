<?php
class AgencyPaymentPlanPage extends AppModel
{
    public $useTable = 'agency_payment_plan_pages';
    public $belongsTo = array(
        'Agency' => array(
            'className' => 'Agency',
            'foreignKey' => 'project_id'
        )
    );

    public function paymentPlanTableName()
    {
        try {
            $db = $this->getDataSource();
            $table = method_exists($db, 'fullTableName') ? $db->fullTableName($this, false, false) : $this->useTable;
            $table = trim((string)$table, '`');
            return $table !== '' ? $table : 'agency_payment_plan_pages';
        } catch (Exception $e) {
            return 'agency_payment_plan_pages';
        }
    }

    public function tableExists()
    {
        try {
            $db = $this->getDataSource();
            $table = $this->paymentPlanTableName();
            $connection = method_exists($db, 'getConnection') ? $db->getConnection() : null;
            if(is_object($connection) && method_exists($connection, 'query'))
            {
                $check = $connection->query("SHOW TABLES LIKE ".$connection->quote($table));
                return ($check && $check->fetch()) ? true : false;
            }
            $rows = $this->query("SHOW TABLES LIKE '".$table."'");
            return !empty($rows);
        } catch (Exception $e) {
            return false;
        }
    }

    public function createTableSql()
    {
        $table = $this->paymentPlanTableName();
        $quotedTable = '`'.str_replace('`', '``', $table).'`';
        return "CREATE TABLE IF NOT EXISTS ".$quotedTable." (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `project_id` int(11) NOT NULL,
                `enabled` tinyint(1) NOT NULL DEFAULT 1,
                `slug` varchar(160) NOT NULL,
                `page_title` varchar(180) NOT NULL DEFAULT '',
                `highlights_title` varchar(180) DEFAULT '',
                `hero_subtitle` varchar(255) DEFAULT '',
                `unit_label` varchar(120) DEFAULT '',
                `booking_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
                `confirmation_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
                `allotment_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
                `monthly_installment` decimal(15,2) NOT NULL DEFAULT 0.00,
                `quarterly_installment` decimal(15,2) NOT NULL DEFAULT 0.00,
                `possession_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
                `total_price` decimal(15,2) NOT NULL DEFAULT 0.00,
                `amount_plans_json` longtext,
                `summary_title` varchar(180) DEFAULT 'Main Amounts',
                `footer_title` varchar(180) DEFAULT '',
                `footer_text` text,
                `contact_heading` varchar(180) DEFAULT '',
                `cta_label` varchar(80) DEFAULT 'Contact Agency',
                `meta_title` varchar(180) DEFAULT '',
                `meta_desc` varchar(255) DEFAULT '',
                `updated_by` int(11) NOT NULL DEFAULT 0,
                `created` datetime DEFAULT NULL,
                `modified` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `project_id` (`project_id`),
                UNIQUE KEY `slug` (`slug`),
                KEY `enabled` (`enabled`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
    }

    protected function _executeSchemaSql($sql)
    {
        $db = $this->getDataSource();
        $connection = method_exists($db, 'getConnection') ? $db->getConnection() : null;
        if(is_object($connection) && method_exists($connection, 'exec'))
        {
            $connection->exec($sql);
        }
        else
        {
            $db->rawQuery($sql);
        }
    }

    protected function _columnExists($column)
    {
        try {
            $table = '`'.str_replace('`', '``', $this->paymentPlanTableName()).'`';
            $column = str_replace("'", "\\'", (string)$column);
            $rows = $this->query("SHOW COLUMNS FROM ".$table." LIKE '".$column."'");
            return !empty($rows);
        } catch (Exception $e) {
            $this->schema(true);
            return $this->hasField($column);
        }
    }

    protected function _ensureColumn($column, $sql)
    {
        try {
            if($this->_columnExists($column))
            {
                return true;
            }
            $this->_executeSchemaSql($sql);
            $this->schema(true);
            return $this->_columnExists($column);
        } catch (Exception $e) {
            $this->schema(true);
            return $this->_columnExists($column);
        }
    }

    protected function _ensureTitleColumns()
    {
        $table = '`'.str_replace('`', '``', $this->paymentPlanTableName()).'`';
        $columns = array(
            'highlights_title' => "ALTER TABLE ".$table." ADD `highlights_title` varchar(180) DEFAULT '' AFTER `page_title`",
            'amount_plans_json' => "ALTER TABLE ".$table." ADD `amount_plans_json` longtext AFTER `total_price`",
            'summary_title' => "ALTER TABLE ".$table." ADD `summary_title` varchar(180) DEFAULT 'Main Amounts' AFTER `total_price`",
            'footer_title' => "ALTER TABLE ".$table." ADD `footer_title` varchar(180) DEFAULT '' AFTER `summary_title`",
            'footer_text' => "ALTER TABLE ".$table." ADD `footer_text` text AFTER `footer_title`"
        );
        foreach($columns as $column => $sql)
        {
            $this->_ensureColumn($column, $sql);
        }
    }

    protected function _ensureAmountColumns()
    {
        $table = '`'.str_replace('`', '``', $this->paymentPlanTableName()).'`';
        $columns = array(
            'allotment_amount' => "ALTER TABLE ".$table." ADD `allotment_amount` decimal(15,2) NOT NULL DEFAULT 0.00 AFTER `confirmation_amount`"
        );
        foreach($columns as $column => $sql)
        {
            $this->_ensureColumn($column, $sql);
        }
    }

    public function hasSettingsColumns()
    {
        $columns = array('amount_plans_json','summary_title','footer_title','footer_text');
        foreach($columns as $column)
        {
            if(!$this->_columnExists($column))
            {
                return false;
            }
        }
        return true;
    }

    public function hasPaymentPlanColumn($column)
    {
        return $this->_columnExists($column);
    }

    public function saveSettingsColumns($id, $data)
    {
        $id = (int)$id;
        if($id <= 0)
        {
            return false;
        }
        $columns = array('highlights_title','amount_plans_json','summary_title','footer_title','footer_text');
        $sets = array();
        $db = $this->getDataSource();
        foreach($columns as $column)
        {
            if(array_key_exists($column, (array)$data) && $this->_columnExists($column))
            {
                $sets[] = '`'.str_replace('`', '``', $column).'` = '.$db->value($data[$column], 'string');
            }
        }
        if(empty($sets))
        {
            return true;
        }
        try {
            $table = '`'.str_replace('`', '``', $this->paymentPlanTableName()).'`';
            $this->_executeSchemaSql("UPDATE ".$table." SET ".implode(', ', $sets)." WHERE `id` = ".$id." LIMIT 1");
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function ensureSchema()
    {
        try {
            $db = $this->getDataSource();
            $table = $this->paymentPlanTableName();
            $sql = $this->createTableSql();

            $connection = method_exists($db, 'getConnection') ? $db->getConnection() : null;
            if(is_object($connection) && method_exists($connection, 'exec'))
            {
                $connection->exec($sql);
                $check = $connection->query("SHOW TABLES LIKE ".$connection->quote($table));
                if(!$check || !$check->fetch())
                {
                    return false;
                }
            }
            else
            {
                $db->rawQuery($sql);
            }
            $this->schema(true);
            if($this->tableExists())
            {
                $this->_ensureAmountColumns();
                $this->_ensureTitleColumns();
            }
        } catch (Exception $e) {
            return $this->tableExists() && $this->hasSettingsColumns();
        }
        return $this->tableExists() && $this->hasSettingsColumns();
    }

    public function defaultsForAgency($agency)
    {
        $agency = isset($agency['Agency']) ? $agency['Agency'] : (array)$agency;
        $name = !empty($agency['name']) ? $agency['name'] : __('Agency');
        $slug = $this->uniqueSlug($this->slugify($name), !empty($agency['id']) ? (int)$agency['id'] : 0);
        return array(
            'project_id' => !empty($agency['id']) ? (int)$agency['id'] : 0,
            'enabled' => 1,
            'slug' => $slug,
            'page_title' => $name.' '.__('Payment Plan'),
            'highlights_title' => '',
            'hero_subtitle' => __('Flexible booking and installment details'),
            'unit_label' => __('Apartment'),
            'booking_amount' => 0,
            'confirmation_amount' => 0,
            'allotment_amount' => 0,
            'monthly_installment' => 0,
            'quarterly_installment' => 0,
            'possession_amount' => 0,
            'total_price' => 0,
            'amount_plans_json' => json_encode(array()),
            'summary_title' => __('Main Amounts'),
            'footer_title' => __('Need updated availability?'),
            'footer_text' => __('Contact this agency for current inventory, booking confirmation, and latest price updates.'),
            'contact_heading' => __('Talk to ').$name,
            'cta_label' => __('Contact Agency'),
            'meta_title' => $name.' '.__('Payment Plan'),
            'meta_desc' => __('Agency-wise apartment payment plan and booking details.')
        );
    }

    public function normalizeAmountPlans($rows)
    {
        $out = array();
        foreach ((array)$rows as $row) {
            $planTitle = isset($row['plan_title']) ? trim((string)$row['plan_title']) : '';
            $unitLabel = isset($row['unit_label']) ? trim((string)$row['unit_label']) : '';
            $booking = isset($row['booking_amount']) ? trim((string)$row['booking_amount']) : '';
            $confirmation = isset($row['confirmation_amount']) ? trim((string)$row['confirmation_amount']) : '';
            $allotment = isset($row['allotment_amount']) ? trim((string)$row['allotment_amount']) : '';
            $monthly = isset($row['monthly_installment']) ? trim((string)$row['monthly_installment']) : '';
            $quarterly = isset($row['quarterly_installment']) ? trim((string)$row['quarterly_installment']) : '';
            $possession = isset($row['possession_amount']) ? trim((string)$row['possession_amount']) : '';
            $total = isset($row['total_price']) ? trim((string)$row['total_price']) : '';
            $note = isset($row['note']) ? trim((string)$row['note']) : '';
            $highlightsTitle = isset($row['highlights_title']) ? trim((string)$row['highlights_title']) : '';
            $summaryTitle = isset($row['summary_title']) ? trim((string)$row['summary_title']) : '';
            $footerTitle = isset($row['footer_title']) ? trim((string)$row['footer_title']) : '';
            $footerText = isset($row['footer_text']) ? trim((string)$row['footer_text']) : '';
            if ($planTitle === '' && $unitLabel === '' && $booking === '' && $confirmation === '' && $allotment === '' && $monthly === '' && $quarterly === '' && $possession === '' && $total === '' && $note === '' && $highlightsTitle === '' && $summaryTitle === '' && $footerTitle === '' && $footerText === '') {
                continue;
            }
            $out[] = array(
                'plan_title' => $planTitle,
                'unit_label' => $unitLabel,
                'booking_amount' => $booking,
                'confirmation_amount' => $confirmation,
                'allotment_amount' => $allotment,
                'monthly_installment' => $monthly,
                'quarterly_installment' => $quarterly,
                'possession_amount' => $possession,
                'total_price' => $total,
                'note' => $note,
                'highlights_title' => $highlightsTitle,
                'summary_title' => $summaryTitle,
                'footer_title' => $footerTitle,
                'footer_text' => $footerText
            );
        }
        return $out;
    }

    public function decodeJsonList($value)
    {
        $decoded = json_decode((string)$value, true);
        return is_array($decoded) ? $decoded : array();
    }

    public function slugify($value)
    {
        $value = strtolower(trim((string)$value));
        $value = preg_replace('/[^a-z0-9]+/', '-', $value);
        $value = trim($value, '-');
        return $value !== '' ? $value : 'payment-plan';
    }

    public function uniqueSlug($slug, $projectId = 0, $rowId = 0)
    {
        $slug = $this->slugify($slug);
        $base = $slug;
        $i = 2;
        while (true) {
            $conditions = array('AgencyPaymentPlanPage.slug' => $slug);
            if ((int)$rowId > 0) {
                $conditions['AgencyPaymentPlanPage.id !='] = (int)$rowId;
            }
            $exists = $this->find('count', array('conditions' => $conditions, 'recursive' => -1));
            if (!$exists) {
                return $slug;
            }
            if ((int)$projectId > 0) {
                $candidate = $base.'-'.$projectId;
                if ($candidate !== $slug) {
                    $slug = $candidate;
                    continue;
                }
            }
            $slug = $base.'-'.$i;
            $i++;
        }
    }
}
?>
