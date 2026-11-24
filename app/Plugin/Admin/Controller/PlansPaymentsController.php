<?php
class PlansPaymentsController extends AdminAppController {
    public $helpers = array('Html', 'Form','Session','Paginator','Js'=> array('Jquery'));
    public $components = array('Session','Paginator','search-master.Prg');
    public $presetVars = true;
    var $paginate = array('page'=>1,'order'=>array('PlansPayment.id'=>'asc'));
    public function beforeFilter()
    {
        parent::beforeFilter();
        if ($this->_isSuperAdminSession() || $this->_isAgencyAdminSession())
        {
            $this->set('allowAddAction', true);
            $this->set('allowEditAction', true);
            $this->set('allowDeleteAction', true);
            $this->set('allowViewAction', true);
        }
        if($this->_shouldRunControllerBootstrap('planspayments.schema', '2026-06-02.1'))
        {
            $this->_ensureDealChargeSchema();
            $this->_ensureBusinessAccountingSchema();
        }
    }
    protected function _ensureDealChargeSchema()
    {
        try
        {
            $this->PlansPayment->query("CREATE TABLE IF NOT EXISTS `deal_charges` (
                `id` bigint(20) NOT NULL AUTO_INCREMENT,
                `deal_id` bigint(20) NOT NULL DEFAULT 0,
                `project_id` int(11) NOT NULL DEFAULT 0,
                `charge_name` varchar(160) NOT NULL DEFAULT '',
                `cycle_type` char(1) NOT NULL DEFAULT 'M',
                `cycle_count` int(11) NOT NULL DEFAULT 1,
                `cycle_amount` decimal(14,2) NOT NULL DEFAULT 0.00,
                `total_amount` decimal(14,2) NOT NULL DEFAULT 0.00,
                `start_date` date DEFAULT NULL,
                `status` tinyint(1) NOT NULL DEFAULT 1,
                `created_by` int(11) NOT NULL DEFAULT 0,
                `created` datetime DEFAULT NULL,
                `modified` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `deal_id` (`deal_id`),
                KEY `project_id` (`project_id`),
                KEY `status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3");
        }
        catch(Exception $e)
        {
            // Keep page available even if table create fails.
        }
        try
        {
            $this->PlansPayment->query("ALTER TABLE `plans_payments` ADD COLUMN `is_deal_charge` tinyint(1) NOT NULL DEFAULT 0 AFTER `date`");
        }
        catch(Exception $e)
        {
            // Ignore if column already exists.
        }
        try
        {
            $this->PlansPayment->query("ALTER TABLE `plans_payments` ADD COLUMN `deal_charge_id` bigint(20) NOT NULL DEFAULT 0 AFTER `is_deal_charge`");
        }
        catch(Exception $e)
        {
            // Ignore if column already exists.
        }
        try
        {
            $this->PlansPayment->query("ALTER TABLE `plans_payments` ADD KEY `is_deal_charge` (`is_deal_charge`)");
        }
        catch(Exception $e)
        {
            // Ignore if index already exists.
        }
        try
        {
            $this->PlansPayment->query("ALTER TABLE `plans_payments` ADD KEY `deal_charge_id` (`deal_charge_id`)");
        }
        catch(Exception $e)
        {
            // Ignore if index already exists.
        }
        try
        {
            $this->PlansPayment->schema(true);
        }
        catch(Exception $e)
        {
            // Ignore schema refresh issues.
        }
    }
    protected function _dealChargeTableReady()
    {
        try
        {
            $rows = $this->PlansPayment->query("SHOW TABLES LIKE 'deal_charges'");
            return !empty($rows);
        }
        catch(Exception $e)
        {
            return false;
        }
    }
    protected function _dealChargeCycleOptions()
    {
        return array(
            'M' => __('Monthly'),
            'Q' => __('Quarterly'),
            'H' => __('Biannual'),
            'Y' => __('Yearly')
        );
    }
    protected function _cycleStepByType($cycleType)
    {
        $cycleType = strtoupper(trim((string)$cycleType));
        if($cycleType === 'Q')return 3;
        if($cycleType === 'H')return 6;
        if($cycleType === 'Y')return 12;
        return 1;
    }
    protected function _normalizePostedDate($rawDate)
    {
        $rawDate = trim((string)$rawDate);
        if($rawDate === '')
        {
            return '';
        }
        $time = strtotime($rawDate);
        if($time === false)
        {
            return '';
        }
        return date('Y-m-d', $time);
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
    protected function _ensureRealestateLettersStorage()
    {
        $this->loadModel('Realestateletter');
        $exists = false;
        try
        {
            $existsRows = $this->Realestateletter->query("SHOW TABLES LIKE 'real_estate_letters'");
            if(!empty($existsRows))
            {
                $exists = true;
            }
        }
        catch(Exception $e)
        {
            // Continue and try create.
        }
        if(!$exists)
        {
            try
            {
                $this->Realestateletter->query("CREATE TABLE IF NOT EXISTS `real_estate_letters` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `deal_id` int(11) NOT NULL,
                    `client_id` int(11) DEFAULT NULL,
                    `template_id` int(11) DEFAULT NULL,
                    `user_id` int(11) DEFAULT NULL,
                    `letter_type` varchar(30) DEFAULT NULL,
                    `reference_no` varchar(60) DEFAULT NULL,
                    `title` varchar(255) DEFAULT NULL,
                    `letter_date` date DEFAULT NULL,
                    `description` longtext,
                    `status` varchar(15) NOT NULL DEFAULT 'Published',
                    `created` datetime DEFAULT NULL,
                    `modified` datetime DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `deal_id` (`deal_id`),
                    KEY `client_id` (`client_id`),
                    KEY `template_id` (`template_id`),
                    KEY `user_id` (`user_id`),
                    KEY `letter_type` (`letter_type`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
                $exists = true;
            }
            catch(Exception $e)
            {
                return false;
            }
        }
        $columns = array(
            "ALTER TABLE `real_estate_letters` ADD COLUMN `charge_work_details` longtext NULL AFTER `description`",
            "ALTER TABLE `real_estate_letters` ADD COLUMN `charge_work_status` varchar(60) DEFAULT 'In Progress' AFTER `charge_work_details`",
            "ALTER TABLE `real_estate_letters` ADD COLUMN `charge_payment_method` varchar(120) DEFAULT '' AFTER `charge_work_status`",
            "ALTER TABLE `real_estate_letters` ADD COLUMN `charge_payment_deadline` varchar(120) DEFAULT '' AFTER `charge_payment_method`",
            "ALTER TABLE `real_estate_letters` ADD COLUMN `agreement_place` varchar(191) DEFAULT '' AFTER `charge_payment_deadline`",
            "ALTER TABLE `real_estate_letters` ADD COLUMN `agency_representative_name` varchar(191) DEFAULT '' AFTER `agreement_place`",
            "ALTER TABLE `real_estate_letters` ADD COLUMN `agency_representative_designation` varchar(120) DEFAULT '' AFTER `agency_representative_name`"
        );
        foreach($columns as $sql)
        {
            try
            {
                $this->Realestateletter->query($sql);
            }
            catch(Exception $e)
            {
                // Ignore existing columns.
            }
        }
        try
        {
            $this->Realestateletter->schema(true);
        }
        catch(Exception $e)
        {
            // Ignore schema refresh issues.
        }
        return $exists;
    }
    protected function _ensureDealChargeLetterReferenceColumns()
    {
        try
        {
            $this->loadModel('Ardsetting');
        }
        catch(Exception $e)
        {
            return;
        }
        try
        {
            $this->Ardsetting->query("ALTER TABLE `ardaccounts_settings` ADD COLUMN `charge_letter_prefix` varchar(20) DEFAULT 'CHG-' AFTER `booking_letter_next_no`");
        }
        catch(Exception $e)
        {
            try
            {
                $this->Ardsetting->query("ALTER TABLE `ardaccounts_settings` ADD COLUMN `charge_letter_prefix` varchar(20) DEFAULT 'CHG-'");
            }
            catch(Exception $ignored)
            {
                // Ignore if column already exists.
            }
        }
        try
        {
            $this->Ardsetting->query("ALTER TABLE `ardaccounts_settings` ADD COLUMN `charge_letter_next_no` int(11) NOT NULL DEFAULT 1 AFTER `charge_letter_prefix`");
        }
        catch(Exception $e)
        {
            try
            {
                $this->Ardsetting->query("ALTER TABLE `ardaccounts_settings` ADD COLUMN `charge_letter_next_no` int(11) NOT NULL DEFAULT 1");
            }
            catch(Exception $ignored)
            {
                // Ignore if column already exists.
            }
        }
    }
    protected function _dealChargeLetterTemplateBody()
    {
        return '<div dir="rtl" style="font-family:Jameel Noori Nastaleeq,Noto Nastaliq Urdu,serif;line-height:2;"><h2 style="text-align:center;">معاہدہ برائے اضافی کام / اضافی چارجز</h2><p><strong>تاریخ:</strong> {{letter_date}} &nbsp; <strong>حوالہ:</strong> {{letter_ref_no}}</p><p>یہ معاہدہ فریق اوّل <strong>{{client_name}}</strong> اور فریق دوم <strong>{{agency_name}}</strong> کے درمیان پراپرٹی / فائل <strong>{{property_file}}</strong>، ممبر شپ نمبر <strong>{{deal_invoice_no}}</strong> کے اضافی کام / اضافی چارجز کے لیے باہمی رضامندی سے طے پایا۔</p><p><strong>اضافی کام:</strong> {{charge_work_details}}<br><strong>کام کی حالت:</strong> {{charge_work_status_urdu}}<br><strong>کل اضافی چارجز:</strong> {{deal_charges_total}} ({{deal_charges_total_words_urdu}})<br><strong>ادائیگی طریقہ:</strong> {{charge_payment_method}}<br><strong>ادائیگی شیڈول:</strong> {{charge_payment_deadline}}</p>{{deal_charges_table}}<p>فریقین نے تمام شرائط پڑھ / سن کر، سمجھ کر، اپنی آزاد مرضی سے قبول کیں۔</p></div>';
    }
    protected function _ensureDealChargeLetterTemplate()
    {
        $this->loadModel('Template');
        $typeCode = 'RLT_CHARGE';
        $template = $this->Template->find('first', array(
            'fields' => array('Template.id','Template.name','Template.description'),
            'conditions' => array('Template.type' => $typeCode),
            'order' => array('Template.id' => 'DESC'),
            'recursive' => -1
        ));
        if(!empty($template['Template']['id']))
        {
            return $template['Template'];
        }
        $templateData = array(
            'name' => 'Deal Charges Agreement',
            'description' => $this->_dealChargeLetterTemplateBody(),
            'type' => $typeCode,
            'status' => 'Published',
            'is_new' => 'No'
        );
        $this->Template->create();
        $saved = $this->Template->save(array('Template' => $templateData), false);
        if(!$saved)
        {
            return array();
        }
        return array(
            'id' => (int)$this->Template->id,
            'name' => $templateData['name'],
            'description' => $templateData['description']
        );
    }
    protected function _dealChargeLetterReferenceExists($referenceNo, $projectId = 0)
    {
        $referenceNo = trim((string)$referenceNo);
        if($referenceNo === '')
        {
            return false;
        }
        $this->loadModel('Realestateletter');
        $conditions = array('Realestateletter.reference_no' => $referenceNo);
        $query = array(
            'conditions' => $conditions,
            'recursive' => -1
        );
        $projectId = (int)$projectId;
        if($projectId > 0)
        {
            $query['joins'] = array(
                array('table' => 'deals', 'alias' => 'Deal', 'type' => 'LEFT', 'conditions' => array('Deal.id=Realestateletter.deal_id')),
                array('table' => 'properties', 'alias' => 'Property', 'type' => 'LEFT', 'conditions' => array('Property.id=Deal.property_id'))
            );
            $conditions[] = array('Property.project_id' => $projectId);
            $query['conditions'] = $conditions;
        }
        try
        {
            return ((int)$this->Realestateletter->find('count', $query) > 0);
        }
        catch(Exception $e)
        {
            return false;
        }
    }
    protected function _nextDealChargeLetterReferenceNo($projectId = 0)
    {
        $projectId = (int)$projectId;
        $prefixDefault = 'CHG-' . date('Y') . '-';
        if($projectId <= 0)
        {
            $this->loadModel('Realestateletter');
            $lastId = 0;
            try
            {
                $last = $this->Realestateletter->find('first', array(
                    'fields' => array('Realestateletter.id'),
                    'order' => array('Realestateletter.id' => 'DESC'),
                    'recursive' => -1
                ));
                if(!empty($last['Realestateletter']['id']))
                {
                    $lastId = (int)$last['Realestateletter']['id'];
                }
            }
            catch(Exception $e)
            {
                $lastId = 0;
            }
            return $prefixDefault . str_pad((string)($lastId + 1), 4, '0', STR_PAD_LEFT);
        }

        $this->_ensureDealChargeLetterReferenceColumns();
        $this->loadModel('Ardsetting');
        $settings = array();
        try
        {
            $settingsRow = $this->Ardsetting->find('first', array(
                'conditions' => array('Ardsetting.project_id' => $projectId),
                'order' => array('Ardsetting.id' => 'DESC'),
                'recursive' => -1
            ));
            if(!empty($settingsRow['Ardsetting']))
            {
                $settings = (array)$settingsRow['Ardsetting'];
            }
        }
        catch(Exception $e)
        {
            $settings = array();
        }
        $prefix = isset($settings['charge_letter_prefix']) ? trim((string)$settings['charge_letter_prefix']) : '';
        if($prefix === '')
        {
            $prefix = $prefixDefault;
        }
        if(strlen($prefix) > 20)
        {
            $prefix = substr($prefix, 0, 20);
        }
        $nextNo = isset($settings['charge_letter_next_no']) ? (int)$settings['charge_letter_next_no'] : 1;
        if($nextNo <= 0)
        {
            $nextNo = 1;
        }
        $guard = 0;
        while(true)
        {
            $candidate = $prefix . $nextNo;
            if(!$this->_dealChargeLetterReferenceExists($candidate, $projectId))
            {
                break;
            }
            $nextNo++;
            $guard++;
            if($guard > 100000)
            {
                break;
            }
        }
        try
        {
            $saveRow = array(
                'project_id' => $projectId,
                'charge_letter_prefix' => $prefix,
                'charge_letter_next_no' => $nextNo + 1
            );
            if(!empty($settings['id']))
            {
                $saveRow['id'] = (int)$settings['id'];
            }
            $this->Ardsetting->create();
            $this->Ardsetting->save(array('Ardsetting' => $saveRow), false);
        }
        catch(Exception $e)
        {
            // Keep charge flow available if counter update fails.
        }
        return $candidate;
    }
    protected function _refreshDealChargeAutoLetter($dealId, $projectId = 0, $userId = 0, $chargeName = '')
    {
        $dealId = (int)$dealId;
        if($dealId <= 0)
        {
            return false;
        }
        if(!$this->_ensureRealestateLettersStorage())
        {
            return false;
        }
        $this->loadModel('Deal');
        $this->loadModel('Realestateletter');
        $dealRow = $this->Deal->find('first', array(
            'fields' => array('Deal.id','Deal.client_id','Deal.invoice_no','Property.project_id'),
            'joins' => array(
                array('table' => 'properties', 'alias' => 'Property', 'type' => 'LEFT', 'conditions' => array('Deal.property_id=Property.id'))
            ),
            'conditions' => array('Deal.id' => $dealId),
            'recursive' => -1
        ));
        if(empty($dealRow['Deal']['id']))
        {
            return false;
        }
        $projectId = (int)$projectId;
        if($projectId <= 0 && !empty($dealRow['Property']['project_id']))
        {
            $projectId = (int)$dealRow['Property']['project_id'];
        }
        $template = $this->_ensureDealChargeLetterTemplate();
        if(empty($template['id']))
        {
            return false;
        }

        $typeCode = 'RLT_CHARGE';
        $invoiceNo = !empty($dealRow['Deal']['invoice_no']) ? trim((string)$dealRow['Deal']['invoice_no']) : '';
        $chargeName = trim((string)$chargeName);
        $titleParts = array('Deal Charges Agreement');
        if($chargeName !== '')
        {
            $titleParts[] = $chargeName;
        }
        if($invoiceNo !== '')
        {
            $titleParts[] = $invoiceNo;
        }
        $title = implode(' - ', $titleParts);
        $existing = $this->Realestateletter->find('first', array(
            'conditions' => array(
                'Realestateletter.deal_id' => $dealId,
                'Realestateletter.letter_type' => $typeCode,
                'Realestateletter.title' => $title
            ),
            'order' => array('Realestateletter.id' => 'DESC'),
            'recursive' => -1
        ));
        $description = isset($template['description']) ? (string)$template['description'] : $this->_dealChargeLetterTemplateBody();
        if(!empty($existing['Realestateletter']['id']))
        {
            $existingTitle = isset($existing['Realestateletter']['title']) ? trim((string)$existing['Realestateletter']['title']) : '';
            $existingDescription = isset($existing['Realestateletter']['description']) ? trim((string)$existing['Realestateletter']['description']) : '';
            $existingReferenceNo = isset($existing['Realestateletter']['reference_no']) ? trim((string)$existing['Realestateletter']['reference_no']) : '';
            $existingLetterDate = isset($existing['Realestateletter']['letter_date']) ? trim((string)$existing['Realestateletter']['letter_date']) : '';
            $updateRow = array(
                'id' => (int)$existing['Realestateletter']['id'],
                'template_id' => (int)$template['id'],
                'status' => 'Published'
            );
            if($existingTitle === '')
            {
                $updateRow['title'] = $title;
            }
            if($existingDescription === '')
            {
                $updateRow['description'] = $description;
            }
            if($existingReferenceNo === '')
            {
                $updateRow['reference_no'] = $this->_nextDealChargeLetterReferenceNo($projectId);
            }
            if($existingLetterDate === '')
            {
                $updateRow['letter_date'] = date('Y-m-d');
            }
            if((int)$userId > 0)
            {
                $updateRow['user_id'] = (int)$userId;
            }
            $this->Realestateletter->create();
            return (bool)$this->Realestateletter->save(array('Realestateletter' => $updateRow), false);
        }

        $saveRow = array(
            'deal_id' => $dealId,
            'client_id' => !empty($dealRow['Deal']['client_id']) ? (int)$dealRow['Deal']['client_id'] : null,
            'template_id' => (int)$template['id'],
            'user_id' => (int)$userId,
            'letter_type' => $typeCode,
            'reference_no' => $this->_nextDealChargeLetterReferenceNo($projectId),
            'title' => $title,
            'letter_date' => date('Y-m-d'),
            'description' => $description,
            'charge_work_details' => $chargeName,
            'charge_work_status' => 'In Progress',
            'charge_payment_method' => 'باہمی طے شدہ طریقہ کار / بینک ٹرانسفر / نقد رسید',
            'charge_payment_deadline' => 'باہمی طے شدہ شیڈول / آخری تاریخ کے مطابق',
            'agency_representative_designation' => 'مجاز افسر',
            'status' => 'Published'
        );
        $this->Realestateletter->create();
        return (bool)$this->Realestateletter->save(array('Realestateletter' => $saveRow), false);
    }
    protected function _dealChargeTypesForProject($projectId)
    {
        $options = array(
            'Development' => 'Development',
            'Extra Work' => 'Extra Work'
        );
        try
        {
            $this->loadModel('Ardsetting');
            $settingsRow = $this->Ardsetting->find('first', array(
                'fields' => array('Ardsetting.deal_charge_types'),
                'conditions' => array('Ardsetting.project_id' => (int)$projectId),
                'order' => array('Ardsetting.id' => 'DESC'),
                'recursive' => -1
            ));
            if(empty($settingsRow) && (int)$projectId > 0)
            {
                $settingsRow = $this->Ardsetting->find('first', array(
                    'fields' => array('Ardsetting.deal_charge_types'),
                    'conditions' => array('Ardsetting.project_id' => 0),
                    'order' => array('Ardsetting.id' => 'DESC'),
                    'recursive' => -1
                ));
            }
            $raw = '';
            if(!empty($settingsRow['Ardsetting']['deal_charge_types']))
            {
                $raw = (string)$settingsRow['Ardsetting']['deal_charge_types'];
            }
            $rows = preg_split('/[\r\n,]+/', $raw);
            if(is_array($rows))
            {
                $parsed = array();
                foreach($rows as $row)
                {
                    $row = trim((string)$row);
                    if($row === '')
                    {
                        continue;
                    }
                    $parsed[$row] = $row;
                }
                if(!empty($parsed))
                {
                    $options = $parsed;
                }
            }
        }
        catch(Exception $e)
        {
            // Use defaults if settings table/column is not ready yet.
        }
        return $options;
    }
    protected function _dealChargeRows($dealId)
    {
        if(!$this->_dealChargeTableReady())
        {
            return array();
        }
        $this->loadModel('DealCharge');
        try
        {
            return (array)$this->DealCharge->find('all', array(
                'conditions' => $this->_dealChargeConditions($dealId),
                'order' => array('DealCharge.id' => 'DESC'),
                'recursive' => -1
            ));
        }
        catch(Exception $e)
        {
            return array();
        }
    }

    protected function _dealChargeConditions($dealId)
    {
        $dealId = (int)$dealId;
        $conditions = array('DealCharge.deal_id' => $dealId, 'DealCharge.status' => 1);
        $dealCreated = $this->_dealCreatedAt($dealId);
        if($dealCreated !== '')
        {
            $conditions[] = array('OR' => array(
                'DealCharge.created IS NULL',
                'DealCharge.created >=' => $dealCreated
            ));
        }
        return $conditions;
    }

    protected function _dealCreatedAt($dealId)
    {
        $dealId = (int)$dealId;
        if($dealId <= 0)
        {
            return '';
        }
        $this->loadModel('Deal');
        try
        {
            $dealRow = $this->Deal->find('first', array(
                'fields' => array('Deal.id', 'Deal.created'),
                'conditions' => array('Deal.id' => $dealId),
                'recursive' => -1
            ));
        }
        catch(Exception $e)
        {
            return '';
        }
        return !empty($dealRow['Deal']['created']) ? (string)$dealRow['Deal']['created'] : '';
    }

    protected function _dealChargeCycleRowsWithPaid($dealId)
    {
        if(!$this->_dealChargeTableReady())
        {
            return array();
        }
        $cycleRowsRaw = $this->PlansPayment->find('all', array(
            'fields' => array('PlansPayment.id', 'PlansPayment.name', 'PlansPayment.amount', 'PlansPayment.date', 'PlansPayment.deal_charge_id'),
            'conditions' => array('PlansPayment.deal_id' => (int)$dealId, 'PlansPayment.is_deal_charge' => 1),
            'order' => array('PlansPayment.date' => 'ASC', 'PlansPayment.id' => 'ASC'),
            'recursive' => -1
        ));
        $planIds = array();
        foreach($cycleRowsRaw as $cycleRow)
        {
            if(!empty($cycleRow['PlansPayment']['id']))
            {
                $planIds[] = (int)$cycleRow['PlansPayment']['id'];
            }
        }
        $paidMap = array();
        if(!empty($planIds))
        {
            $this->loadModel('DealsPayment');
            $paidRows = $this->DealsPayment->find('all', array(
                'fields' => array('DealsPayment.plans_payment_id', 'SUM(DealsPayment.emi) AS paid_total'),
                'conditions' => array('DealsPayment.deal_id' => (int)$dealId, 'DealsPayment.plans_payment_id' => $planIds),
                'group' => array('DealsPayment.plans_payment_id'),
                'recursive' => -1
            ));
            foreach((array)$paidRows as $paidRow)
            {
                $planId = !empty($paidRow['DealsPayment']['plans_payment_id']) ? (int)$paidRow['DealsPayment']['plans_payment_id'] : 0;
                if($planId > 0)
                {
                    $paidMap[$planId] = isset($paidRow[0]['paid_total']) ? round((float)$paidRow[0]['paid_total'], 2) : 0;
                }
            }
        }
        $today = !empty($this->currentDate) ? $this->currentDate : date('Y-m-d');
        $rows = array();
        foreach($cycleRowsRaw as $cycleRow)
        {
            $planId = !empty($cycleRow['PlansPayment']['id']) ? (int)$cycleRow['PlansPayment']['id'] : 0;
            if($planId <= 0)
            {
                continue;
            }
            $amount = isset($cycleRow['PlansPayment']['amount']) ? round((float)$cycleRow['PlansPayment']['amount'], 2) : 0;
            $paid = isset($paidMap[$planId]) ? round((float)$paidMap[$planId], 2) : 0;
            $pending = round($amount - $paid, 2);
            if($pending < 0)
            {
                $pending = 0;
            }
            $dueDate = !empty($cycleRow['PlansPayment']['date']) ? (string)$cycleRow['PlansPayment']['date'] : '';
            $status = 'Un-Paid';
            if($pending <= 0)
            {
                $status = 'Paid';
            }
            elseif($dueDate !== '' && $dueDate <= $today)
            {
                $status = 'Overdue';
            }
            $rows[] = array(
                'id' => $planId,
                'deal_charge_id' => !empty($cycleRow['PlansPayment']['deal_charge_id']) ? (int)$cycleRow['PlansPayment']['deal_charge_id'] : 0,
                'name' => isset($cycleRow['PlansPayment']['name']) ? $cycleRow['PlansPayment']['name'] : '',
                'amount' => $amount,
                'date' => $dueDate,
                'paid' => $paid,
                'pending' => $pending,
                'status' => $status
            );
        }
        return $rows;
    }
    protected function _dealChargeSummaryMap($dealChargeRows, $dealChargeCycles)
    {
        $summary = array();
        foreach((array)$dealChargeRows as $chargeRow)
        {
            $chargeId = !empty($chargeRow['DealCharge']['id']) ? (int)$chargeRow['DealCharge']['id'] : 0;
            if($chargeId <= 0)
            {
                continue;
            }
            $summary[$chargeId] = array(
                'row' => $chargeRow['DealCharge'],
                'paid' => 0,
                'pending' => isset($chargeRow['DealCharge']['total_amount']) ? round((float)$chargeRow['DealCharge']['total_amount'], 2) : 0,
                'cycle_rows' => array()
            );
        }
        foreach((array)$dealChargeCycles as $cycleRow)
        {
            $chargeId = !empty($cycleRow['deal_charge_id']) ? (int)$cycleRow['deal_charge_id'] : 0;
            if($chargeId <= 0 || !isset($summary[$chargeId]))
            {
                continue;
            }
            $summary[$chargeId]['paid'] = round((float)$summary[$chargeId]['paid'] + (float)$cycleRow['paid'], 2);
            $summary[$chargeId]['pending'] = round((float)$summary[$chargeId]['pending'] - (float)$cycleRow['paid'], 2);
            if($summary[$chargeId]['pending'] < 0)
            {
                $summary[$chargeId]['pending'] = 0;
            }
            $summary[$chargeId]['cycle_rows'][] = $cycleRow;
        }
        return $summary;
    }
    protected function _dealChargePlanRowsWithPaymentMeta($dealId, $chargeId)
    {
        $dealId = (int)$dealId;
        $chargeId = (int)$chargeId;
        if($dealId <= 0 || $chargeId <= 0)
        {
            return array();
        }
        try
        {
            $rows = $this->PlansPayment->query(
                "SELECT
                    `PlansPayment`.`id`,
                    `PlansPayment`.`name`,
                    `PlansPayment`.`amount`,
                    `PlansPayment`.`date`,
                    COALESCE(SUM(`DealsPayment`.`emi`),0) AS `paid_total`,
                    COUNT(`DealsPayment`.`id`) AS `receipt_count`
                 FROM `plans_payments` AS `PlansPayment`
                 LEFT JOIN `deals_payments` AS `DealsPayment` ON `DealsPayment`.`plans_payment_id`=`PlansPayment`.`id`
                 WHERE `PlansPayment`.`deal_id`=".$dealId."
                   AND `PlansPayment`.`deal_charge_id`=".$chargeId."
                   AND `PlansPayment`.`is_deal_charge`=1
                 GROUP BY `PlansPayment`.`id`,`PlansPayment`.`name`,`PlansPayment`.`amount`,`PlansPayment`.`date`
                 ORDER BY `PlansPayment`.`date` ASC, `PlansPayment`.`id` ASC"
            );
        }
        catch(Exception $e)
        {
            return array();
        }
        $out = array();
        foreach((array)$rows as $row)
        {
            $chunk = array();
            if(isset($row['PlansPayment']) && is_array($row['PlansPayment']))
            {
                $chunk = $row['PlansPayment'];
            }
            elseif(isset($row[0]) && is_array($row[0]))
            {
                $chunk = $row[0];
            }
            if(empty($chunk['id']))
            {
                continue;
            }
            $paidTotal = 0;
            $receiptCount = 0;
            if(isset($row[0]['paid_total']))
            {
                $paidTotal = (float)$row[0]['paid_total'];
            }
            elseif(isset($row['0']['paid_total']))
            {
                $paidTotal = (float)$row['0']['paid_total'];
            }
            if(isset($row[0]['receipt_count']))
            {
                $receiptCount = (int)$row[0]['receipt_count'];
            }
            elseif(isset($row['0']['receipt_count']))
            {
                $receiptCount = (int)$row['0']['receipt_count'];
            }
            $out[] = array(
                'id' => (int)$chunk['id'],
                'name' => isset($chunk['name']) ? (string)$chunk['name'] : '',
                'amount' => isset($chunk['amount']) ? round((float)$chunk['amount'],2) : 0,
                'date' => isset($chunk['date']) ? (string)$chunk['date'] : '',
                'paid_total' => round((float)$paidTotal,2),
                'receipt_count' => (int)$receiptCount
            );
        }
        return $out;
    }
    protected function _dealChargeTotalForDeal($dealId)
    {
        if(!$this->_dealChargeTableReady())
        {
            return 0;
        }
        $this->loadModel('DealCharge');
        try
        {
            $sumRow = $this->DealCharge->find('first', array(
                'fields' => array('SUM(DealCharge.total_amount) AS total_charge_amount'),
                'conditions' => $this->_dealChargeConditions($dealId),
                'recursive' => -1
            ));
        }
        catch(Exception $e)
        {
            return 0;
        }
        if(isset($sumRow[0]['total_charge_amount']) && $sumRow[0]['total_charge_amount'] !== null)
        {
            return round((float)$sumRow[0]['total_charge_amount'], 2);
        }
        return 0;
    }
    protected function _recalculateDealTotalFromBaseAndCharges($dealId)
    {
        $dealId = (int)$dealId;
        if($dealId <= 0)
        {
            return false;
        }
        $this->loadModel('Deal');
        $dealRow = $this->Deal->find('first', array(
            'fields' => array('Deal.id', 'Deal.amount', 'Deal.discount', 'Deal.total_amount'),
            'conditions' => array('Deal.id' => $dealId),
            'recursive' => -1
        ));
        if(empty($dealRow['Deal']['id']))
        {
            return false;
        }
        $baseAmount = isset($dealRow['Deal']['amount']) ? (float)$dealRow['Deal']['amount'] : 0;
        $discount = isset($dealRow['Deal']['discount']) ? (float)$dealRow['Deal']['discount'] : 0;
        $netBaseAmount = round($baseAmount - $discount, 2);
        if($netBaseAmount < 0)
        {
            $netBaseAmount = 0;
        }
        $chargeTotal = $this->_dealChargeTotalForDeal($dealId);
        $grandTotal = round($netBaseAmount + $chargeTotal, 2);
        $this->Deal->id = $dealId;
        return (bool)$this->Deal->saveField('total_amount', $grandTotal, false);
    }
    protected function _applyDealTotalDelta($dealId, $deltaAmount)
    {
        $dealId = (int)$dealId;
        $deltaAmount = round((float)$deltaAmount, 2);
        if($dealId <= 0 || abs($deltaAmount) <= 0)
        {
            return true;
        }
        $this->loadModel('Deal');
        $dealRow = $this->Deal->find('first', array(
            'fields' => array('Deal.id', 'Deal.total_amount'),
            'conditions' => array('Deal.id' => $dealId),
            'recursive' => -1
        ));
        if(empty($dealRow['Deal']['id']))
        {
            return false;
        }
        $currentTotal = isset($dealRow['Deal']['total_amount']) ? (float)$dealRow['Deal']['total_amount'] : 0;
        $updatedTotal = round($currentTotal + $deltaAmount, 2);
        if($updatedTotal < 0)
        {
            $updatedTotal = 0;
        }
        $this->Deal->id = $dealId;
        return (bool)$this->Deal->saveField('total_amount', $updatedTotal, false);
    }
    protected function _nextRecurringStep($recurring)
    {
        if($recurring==='Q')return '+3 month';
        if($recurring==='H')return '+6 month';
        if($recurring==='Y')return '+1 year';
        return '+1 month';
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
        $isScheduleOver = false;
        if($scheduleLastDate !== null && $scheduleLastDate <= $today)
        {
            $isScheduleOver = true;
        }
        if($expectedInstallments > 0 && count($planRows) >= $expectedInstallments)
        {
            $isScheduleOver = true;
        }
        return array(
            'schedule_last_date' => $scheduleLastDate,
            'is_schedule_over' => $isScheduleOver,
            'expected_installments' => $expectedInstallments,
            'original_plan_count' => $originalPlanCount
        );
    }
    protected function _nextInstallmentName($plans=array(), $isOverdue=false)
    {
        $maxInstallmentNo = 0;
        foreach($plans as $planRow)
        {
            $name = isset($planRow['name']) ? trim($planRow['name']) : '';
            if(preg_match('/^(?:(?:Monthly\s+)?(\d+)\s+Installment|Monthly\s+Installment\s+(\d+)|Overdue\s+Plan\s+(\d+))$/i', $name, $m))
            {
                $no = !empty($m[1]) ? (int)$m[1] : (!empty($m[2]) ? (int)$m[2] : (int)$m[3]);
                if($no > $maxInstallmentNo) $maxInstallmentNo = $no;
            }
        }
        if($maxInstallmentNo <= 0) $maxInstallmentNo = max(1, count($plans) - 1);
        $nextNo = $maxInstallmentNo + 1;
        if($isOverdue)
        {
            return 'Overdue Plan ' . $nextNo;
        }
        return $nextNo . ' Installment';
    }
    public function index($id=null)
    {
        if(!$id)
        {
            $this->Session->setFlash(__('Invalid deal.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('controller'=>'Deals','action'=>'index'));
        }
        $this->Prg->commonProcess();
        $this->Paginator->settings = $this->paginate;
        $dealId = (int)$id;
        $cond=" 1=1 AND PlansPayment.deal_id=".$dealId." AND (PlansPayment.is_deal_charge=0 OR PlansPayment.is_deal_charge IS NULL)";
        $this->Paginator->settings['conditions'] =array($this->PlansPayment->parseCriteria($this->Prg->parsedParams()),$cond);
        $requestedLimit = !empty($this->request->params['named']['limit']) ? (int)$this->request->params['named']['limit'] : (!empty($this->request->query['limit']) ? (int)$this->request->query['limit'] : 0);
        $planLimit = $requestedLimit > 0 ? $requestedLimit : 5000;
        $this->Paginator->settings['limit'] = $planLimit;
        $this->Paginator->settings['maxLimit'] = max(5000, $planLimit);
        $plans = $this->Paginator->paginate();
        $this->set('Plan', $plans);
        $planStatusMap = array();
        if(!empty($plans))
        {
            $planIds = array();
            foreach($plans as $planRow)
            {
                if(isset($planRow['PlansPayment']['id']))
                {
                    $planIds[] = (int)$planRow['PlansPayment']['id'];
                }
            }
            if(!empty($planIds))
            {
                $this->loadModel('DealsPayment');
                $paidRows = $this->DealsPayment->find('all',array(
                    'fields'=>array('DealsPayment.plans_payment_id','SUM(DealsPayment.emi) AS paid_total'),
                    'conditions'=>array(
                        'DealsPayment.deal_id'=>$dealId,
                        'DealsPayment.plans_payment_id'=>$planIds
                    ),
                    'group'=>array('DealsPayment.plans_payment_id'),
                    'recursive'=>-1
                ));
                $paidMap = array();
                foreach((array)$paidRows as $paidRow)
                {
                    $planId = isset($paidRow['DealsPayment']['plans_payment_id']) ? (int)$paidRow['DealsPayment']['plans_payment_id'] : 0;
                    if($planId<=0)
                    {
                        continue;
                    }
                    $paidMap[$planId] = isset($paidRow[0]['paid_total']) ? (float)$paidRow[0]['paid_total'] : 0;
                }
                $today = !empty($this->currentDate) ? $this->currentDate : date('Y-m-d');
                foreach($plans as $planRow)
                {
                    $planId = isset($planRow['PlansPayment']['id']) ? (int)$planRow['PlansPayment']['id'] : 0;
                    if($planId<=0)
                    {
                        continue;
                    }
                    $planAmount = isset($planRow['PlansPayment']['amount']) ? (float)$planRow['PlansPayment']['amount'] : 0;
                    $paidAmount = isset($paidMap[$planId]) ? (float)$paidMap[$planId] : 0;
                    $pendingAmount = round($planAmount-$paidAmount,2);
                    if($paidAmount <= 0)
                    {
                        $dueDate = isset($planRow['PlansPayment']['date']) ? $planRow['PlansPayment']['date'] : '';
                        if(strlen($dueDate)>0 && $dueDate<=$today)
                        {
                            $status = 'Overdue';
                        }
                        else
                        {
                            $status = 'Un-Paid';
                        }
                    }
                    elseif($pendingAmount <= 0)
                    {
                        $status = 'Paid';
                    }
                    else
                    {
                        $status = 'Partial';
                    }
                    $planStatusMap[$planId] = array(
                        'status'=>$status,
                        'paid'=>$paidAmount,
                        'pending'=>$pendingAmount
                    );
                }
            }
        }
        $dealProjectId = $this->_resolveDealProjectId($dealId);
        $dealChargeTypes = $this->_dealChargeTypesForProject($dealProjectId);
        $dealChargeRows = $this->_dealChargeRows($dealId);
        $dealChargeCycleRows = $this->_dealChargeCycleRowsWithPaid($dealId);
        $dealChargeSummaryMap = $this->_dealChargeSummaryMap($dealChargeRows, $dealChargeCycleRows);
        $this->set('dealChargeTypes', $dealChargeTypes);
        $this->set('dealChargeCycleTypeOptions', $this->_dealChargeCycleOptions());
        $this->set('dealChargeRows', $dealChargeRows);
        $this->set('dealChargeCycleRows', $dealChargeCycleRows);
        $this->set('dealChargeSummaryMap', $dealChargeSummaryMap);
        $this->set('planStatusMap',$planStatusMap);
        $this->set('id', $id);
		 if ($this->request->is('ajax'))
        {
            $this->render('index','ajax'); // View, Layout
        }
    }    
    public function add($id=null)
    {
        $this->loadModel('Deal');
        $this->set('id', $id);
        $dealArr=$this->Deal->find('first',array('fields'=>array('Plan.installment','Deal.total_amount','Deal.recurring','Deal.date','Deal.booking_amount'),
                                                         'joins'=>array(array('table'=>'plans','alias'=>'Plan','type'=>'Left','conditions'=>array('Deal.plan_id=Plan.id'))),
                                                         'conditions'=>array('Deal.id'=>$id)));
        
        $dealAmount=$dealArr['Deal']['total_amount'];
        $dealChargeTotal = $this->_dealChargeTotalForDeal($id);
        $regularDealAmount = round((float)$dealAmount - (float)$dealChargeTotal,2);
        if($regularDealAmount<0)$regularDealAmount=0;
        $bookingAmount=$dealArr['Deal']['booking_amount'];
        $installment=$dealArr['Plan']['installment'];
        $installmentAmount = 0;
        if((int)$installment > 0)
        {
            $installmentAmount=round(($regularDealAmount-$bookingAmount)/$installment);
        }
        $installment++;
        $this->set('date',CakeTime::format($this->dtFormat,$dealArr['Deal']['date']));
        $this->set('installment',$installment);
        $this->set('installmentAmount',$installmentAmount);
        $this->set('bookingAmount',round($bookingAmount));
        $this->set('recurring',$dealArr['Deal']['recurring']);
        if ($this->request->is('post'))
        {
            $this->PlansPayment->create();
            try
            {
                $totalAmount=0;
                foreach($this->request->data as $k=> $value)
                {
                    $totalAmount=$totalAmount+ $this->request->data[$k]['PlansPayment']['amount'];
                    $this->request->data[$k]['PlansPayment']['deal_id']=$id;
                }                
                if(round((float)$regularDealAmount,2)==round((float)$totalAmount,2))
                {
                    if ($this->PlansPayment->saveAll($this->request->data))
                    {
                        $this->Session->setFlash(__('Plans Payment has been added!.'),'flash',array('alert'=>'success'));
                        $this->Session->setFlash(__('Plans Payment has been added!.'),'flash',array('alert'=>'success'));
                        return $this->redirect(array('controller'=>'deals_payments','action' => 'add',$id));
                    }
                }
                else
                {
                    $this->Session->setFlash(__('Deal Amount is not equal to installment amount'),'flash',array('alert'=>'danger'));
                }
            }
            catch (Exception $e)
            {
                $this->Session->setFlash(__('Invalid Post'),'flash',array('alert'=>'danger'));
                return $this->redirect(array('action' => 'add',$id));
            }
        }
    }
    public function edit($id = null)
    {
        $this->set('id',$id);
        if (!$id)
        {
            throw new NotFoundException(__('Invalid post'));
        }
        $this->loadModel('Deal');

        // Resolve Deal ID: $id could be a Deal ID or comma-separated installment IDs
        $dealId = 0;
        $rawParts = explode(",", (string)$id);
        $ids = array_values(array_filter(array_map('intval', $rawParts)));

        if (count($ids) === 1 && $this->Deal->exists($ids[0])) {
            $dealId = $ids[0];
        }

        if ($dealId <= 0 && !empty($ids)) {
            $firstPayment = $this->PlansPayment->find('first', array(
                'fields' => array('PlansPayment.deal_id'),
                'conditions' => array('PlansPayment.id' => $ids),
                'recursive' => -1
            ));
            if (!empty($firstPayment['PlansPayment']['deal_id'])) {
                $dealId = (int)$firstPayment['PlansPayment']['deal_id'];
            }
        }

        if ($dealId <= 0 && is_numeric($id)) {
            $anyPayment = $this->PlansPayment->find('first', array(
                'fields' => array('PlansPayment.deal_id'),
                'conditions' => array('PlansPayment.deal_id' => (int)$id),
                'recursive' => -1
            ));
            if (!empty($anyPayment['PlansPayment']['deal_id'])) {
                $dealId = (int)$anyPayment['PlansPayment']['deal_id'];
            }
        }

        if ($dealId <= 0) {
            throw new NotFoundException(__('Invalid deal or installment plan'));
        }
        $dealProjectId = $this->_resolveDealProjectId($dealId);
        if (!$this->_isSuperAdminSession() && (int)$this->agencyId > 0 && $dealProjectId > 0 && (int)$this->agencyId !== $dealProjectId) {
            $this->Session->setFlash(__('Access Denied. You do not have permission to view payment plans from other agencies.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('controller' => 'Deals', 'action' => 'index'));
        }

        // Always load ALL regular installments for this deal in order
        $post = $this->PlansPayment->find('all', array(
            'conditions' => array(
                'PlansPayment.deal_id' => $dealId,
                array(
                    'OR' => array(
                        'PlansPayment.is_deal_charge' => 0,
                        'PlansPayment.is_deal_charge IS NULL'
                    )
                )
            ),
            'order' => array('PlansPayment.id' => 'ASC'),
            'recursive' => -1
        ));

        if (empty($post)) {
            $this->Session->setFlash(__('No payment plan installments found for this deal. Please create installments schedule.'), 'flash', array('alert' => 'warning'));
            return $this->redirect(array('controller' => 'plans_payments', 'action' => 'add', $dealId));
        }

        $this->set('PlansPayment', $post);
        $this->set('id', $dealId);

        $dealArr = $this->Deal->find('first', array(
            'fields' => array('Deal.total_amount'),
            'conditions' => array('Deal.id' => $dealId),
            'recursive' => -1
        ));
        $dealAmount = 0;
        if (!empty($dealArr['Deal']['total_amount'])) {
            $dealAmount = (float)$dealArr['Deal']['total_amount'];
        }
        $dealChargeTotal = $this->_dealChargeTotalForDeal($dealId);
        $dealRegularAmount = round((float)$dealAmount - (float)$dealChargeTotal, 2);
        if ($dealRegularAmount < 0) $dealRegularAmount = 0;
        $this->set('dealAmountForEdit', $dealRegularAmount);
        $this->set('dealChargeTotalForEdit', $dealChargeTotal);

        if ($this->request->is(array('post', 'put')))
        {
            try
            {
                $totalAmount = 0;
                $normalizedData = array();
                $deleteIds = array();
                foreach ((array)$this->request->data as $k => $value)
                {
                    if (empty($value['PlansPayment'])) {
                        continue;
                    }
                    $row = $value;
                    $rowId = !empty($row['PlansPayment']['id']) ? (int)$row['PlansPayment']['id'] : 0;
                    $deleteFlag = !empty($row['PlansPayment']['_delete']) && (int)$row['PlansPayment']['_delete'] === 1;
                    if ($deleteFlag) {
                        if ($rowId > 0) {
                            $deleteIds[] = $rowId;
                        }
                        continue;
                    }
                    $name = isset($row['PlansPayment']['name']) ? trim($row['PlansPayment']['name']) : '';
                    $amountRaw = isset($row['PlansPayment']['amount']) ? trim((string)$row['PlansPayment']['amount']) : '';
                    $dateRaw = isset($row['PlansPayment']['date']) ? trim((string)$row['PlansPayment']['date']) : '';
                    if ($name === '' && $amountRaw === '' && $dateRaw === '') {
                        continue;
                    }
                    $cleanAmount = str_replace(array(',', ' '), '', $amountRaw);
                    if ($cleanAmount === '') $cleanAmount = '0';
                    $row['PlansPayment']['amount'] = (float)$cleanAmount;
                    $row['PlansPayment']['deal_id'] = $dealId;
                    
                    // Normalize date format if entered in dd-mm-yyyy format
                    if (!empty($dateRaw)) {
                        $parsedTime = strtotime($dateRaw);
                        if ($parsedTime !== false) {
                            $row['PlansPayment']['date'] = date('Y-m-d', $parsedTime);
                        }
                    }

                    unset($row['PlansPayment']['_delete']);
                    $totalAmount = $totalAmount + (float)$row['PlansPayment']['amount'];
                    $normalizedData[] = $row;
                }
                $this->request->data = $normalizedData;
                $dealRegularAmount = (float)$dealRegularAmount;
                
                // Allow tolerance of 1.0 for floating-point rounding
                if (abs(round($dealRegularAmount, 2) - round($totalAmount, 2)) < 1.0)
                {
                    if (isset($this->PlansPayment->validate['deal_id']['isUnique'])) {
                        unset($this->PlansPayment->validate['deal_id']['isUnique']);
                    }
                    $deleteIds = array_values(array_unique($deleteIds));
                    $db = $this->PlansPayment->getDataSource();
                    $db->begin();
                    $savedOk = true;
                    if (!empty($this->request->data)) {
                        $savedOk = $this->PlansPayment->saveAll($this->request->data, array('validate' => 'first', 'deep' => false));
                    }
                    if ($savedOk && !empty($deleteIds)) {
                        foreach ($deleteIds as $deleteId) {
                            if (!$this->PlansPayment->hasAny(array('PlansPayment.id' => $deleteId, 'PlansPayment.deal_id' => $dealId))) {
                                continue;
                            }
                            if (!$this->PlansPayment->delete($deleteId, false)) {
                                $savedOk = false;
                                break;
                            }
                        }
                    }
                    if ($savedOk) {
                        $db->commit();
                        $this->Session->setFlash(__('Plans Payment has been saved!.'), 'flash', array('alert' => 'success'));
                        return $this->redirect(array('action' => 'index', $dealId));
                    }
                    $db->rollback();
                    $this->Session->setFlash(__('Unable to save updated schedule. Please check installment values and try again.'), 'flash', array('alert' => 'danger'));
                }
                else
                {
                    $this->Session->setFlash(__('Deal Amount (%s) is not equal to installment total amount (%s).', number_format($dealRegularAmount, 2), number_format($totalAmount, 2)), 'flash', array('alert' => 'danger'));
                }
            }
            catch (Exception $e)
            {
                $this->Session->setFlash(__('Invalid Post: ' . $e->getMessage()), 'flash', array('alert' => 'danger'));
                return $this->redirect(array('action' => 'index', $dealId));
            }
            $this->set('isError', true);
        }
        else
        {
            $this->layout = null;
            $this->set('isError', false);
        }
        if (!$this->request->data)
        {
            $this->request->data = $post;
        }
    }
    public function deleteall($id=null)
    {
        try
        {
            if ($this->request->is('post'))
            {
                foreach($this->data['PlansPayment']['id'] as $key => $value)
                {
                    $this->PlansPayment->delete($value);
                }
                $this->Session->setFlash(__('Payments has been deleted.'),'flash',array('alert'=>'danger'));
            }        
            $this->redirect(array('controller'=>'PlansPayments','action' => 'index',$id));
        }
        catch (Exception $e)
        {
            $this->Session->setFlash(__('Delete releted options first'),'flash',array('alert'=>'danger'));
            $this->redirect(array('controller'=>'PlansPayments','action' => 'index',$id));
        }
    }
    public function milestone($id=null)
    {
        $this->loadModel('Deal');
        $this->loadModel('DealsPayment');
        if(!$id)
        {
            $this->Session->setFlash(__('Invalid deal.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('controller'=>'Deals','action'=>'index'));
        }
        $deal = $this->Deal->find('first',array(
            'fields'=>array('Deal.id','Deal.total_amount','Deal.recurring'),
            'conditions'=>array('Deal.id'=>$id),
            'recursive'=>-1
        ));
        if(!$deal)
        {
            $this->Session->setFlash(__('Invalid deal.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('controller'=>'Deals','action'=>'index'));
        }
        $planRowsRaw = $this->PlansPayment->find('all',array(
            'fields'=>array('PlansPayment.id','PlansPayment.name','PlansPayment.amount','PlansPayment.date'),
            'conditions'=>array('PlansPayment.deal_id'=>$id),
            'order'=>array('PlansPayment.id'=>'asc'),
            'recursive'=>-1
        ));
        $plans = array();
        foreach($planRowsRaw as $row)
        {
            $plans[] = array(
                'id'=>$row['PlansPayment']['id'],
                'name'=>$row['PlansPayment']['name'],
                'amount'=>$row['PlansPayment']['amount'],
                'date'=>$row['PlansPayment']['date']
            );
        }
        $paidRow = $this->DealsPayment->find('first',array(
            'fields'=>array('SUM(DealsPayment.emi) AS paid_total'),
            'conditions'=>array('DealsPayment.deal_id'=>$id),
            'recursive'=>-1
        ));
        $paidTotal = 0;
        if(isset($paidRow[0]['paid_total']) && $paidRow[0]['paid_total']!==null)
        {
            $paidTotal = (float)$paidRow[0]['paid_total'];
        }
        $outstanding = round((float)$deal['Deal']['total_amount']-$paidTotal,2);
        if($outstanding<0)$outstanding=0;
        $scheduleContext = $this->_getDealScheduleContext($id);
        $isScheduleOver = !empty($scheduleContext['is_schedule_over']);
        $defaultName = $this->_nextInstallmentName($plans, $isScheduleOver);
        $baseDate = $this->currentDate;
        if(!empty($plans))
        {
            $lastPlan = end($plans);
            if(isset($lastPlan['date']) && strlen($lastPlan['date'])>0)
            {
                $baseDate = $lastPlan['date'];
            }
        }
        if($isScheduleOver && !empty($scheduleContext['schedule_last_date']))
        {
            $defaultDate = date($this->dtFormat, strtotime($scheduleContext['schedule_last_date']));
        }
        else
        {
            $defaultDate = date($this->dtFormat,strtotime($baseDate.' '.$this->_nextRecurringStep($deal['Deal']['recurring'])));
        }
        if($this->request->is('post'))
        {
            $name = isset($this->request->data['PlansPayment']['name']) ? trim($this->request->data['PlansPayment']['name']) : '';
            $amount = isset($this->request->data['PlansPayment']['amount']) ? round((float)$this->request->data['PlansPayment']['amount'],2) : 0;
            $date = isset($this->request->data['PlansPayment']['date']) ? $this->request->data['PlansPayment']['date'] : null;
            if(strlen($name)<=0)$name=$defaultName;
            if($amount<=0)
            {
                $this->Session->setFlash(__('Please enter valid milestone amount.'),'flash',array('alert'=>'danger'));
            }
            else
            {
                $this->PlansPayment->create();
                if($this->PlansPayment->save(array('PlansPayment'=>array(
                    'deal_id'=>$id,
                    'name'=>$name,
                    'amount'=>$amount,
                    'date'=>$date
                )),false))
                {
                    $this->Session->setFlash(__('Milestone installment added successfully.'),'flash',array('alert'=>'success'));
                    return $this->redirect(array('controller'=>'plans_payments','action'=>'index',$id));
                }
                $this->Session->setFlash(__('Unable to add milestone installment.'),'flash',array('alert'=>'danger'));
            }
        }
        if(empty($this->request->data))
        {
            $this->request->data['PlansPayment']['name'] = $defaultName;
            $this->request->data['PlansPayment']['amount'] = ($outstanding>0 ? $outstanding : null);
            $this->request->data['PlansPayment']['date'] = $defaultDate;
        }
        $this->set('id',$id);
        $this->set('outstanding',$outstanding);
    }
    public function adddealcharge($id=null)
    {
        $dealId = (int)$id;
        if($dealId<=0)
        {
            $this->Session->setFlash(__('Invalid deal.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('controller'=>'Deals','action'=>'index'));
        }
        if(!$this->request->is('post'))
        {
            return $this->redirect(array('action'=>'index',$dealId));
        }
        if(!$this->_dealChargeTableReady())
        {
            $this->Session->setFlash(__('Deal charges table is not ready yet. Please open Accounts Settings once and retry.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action'=>'index',$dealId));
        }
        $dealProjectId = $this->_resolveDealProjectId($dealId);
        if($this->userType==='AGY' && $dealProjectId>0 && (int)$this->agencyId!==$dealProjectId)
        {
            $this->Session->setFlash(__('Invalid deal scope.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action'=>'index',$dealId));
        }
        $payload = isset($this->request->data['DealCharge']) ? (array)$this->request->data['DealCharge'] : array();
        $chargeName = isset($payload['charge_name']) ? trim((string)$payload['charge_name']) : '';
        $cycleType = isset($payload['cycle_type']) ? strtoupper(trim((string)$payload['cycle_type'])) : 'M';
        $cycleCount = isset($payload['cycle_count']) ? (int)$payload['cycle_count'] : 0;
        $cycleAmountRaw = isset($payload['cycle_amount']) ? (string)$payload['cycle_amount'] : '0';
        $cycleAmount = round((float)str_replace(array(',', ' '), '', $cycleAmountRaw), 2);
        $startDate = $this->_normalizePostedDate(isset($payload['start_date']) ? $payload['start_date'] : '');
        if($chargeName === '')
        {
            $this->Session->setFlash(__('Please enter charge type/name.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action'=>'index',$dealId));
        }
        if(!array_key_exists($cycleType, $this->_dealChargeCycleOptions()))
        {
            $cycleType = 'M';
        }
        if($cycleCount<=0)
        {
            $cycleCount=1;
        }
        if($cycleAmount<=0)
        {
            $this->Session->setFlash(__('Please enter valid charge amount per cycle.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action'=>'index',$dealId));
        }
        if($startDate==='')
        {
            $this->Session->setFlash(__('Please select first cycle start date.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action'=>'index',$dealId));
        }
        $totalAmount = round($cycleAmount * $cycleCount, 2);
        $this->loadModel('DealCharge');
        $db = $this->DealCharge->getDataSource();
        $db->begin();
        try
        {
            $this->DealCharge->create();
            $saveChargeOk = $this->DealCharge->save(array('DealCharge'=>array(
                'deal_id' => $dealId,
                'project_id' => (int)$dealProjectId,
                'charge_name' => $chargeName,
                'cycle_type' => $cycleType,
                'cycle_count' => (int)$cycleCount,
                'cycle_amount' => $cycleAmount,
                'total_amount' => $totalAmount,
                'start_date' => $startDate,
                'status' => 1,
                'created_by' => (int)$this->luserId
            )), false);
            if(!$saveChargeOk)
            {
                throw new Exception('Unable to save charge record.');
            }
            $dealChargeId = (int)$this->DealCharge->id;
            $stepMonths = $this->_cycleStepByType($cycleType);
            $planRows = array();
            for($i=1;$i<=$cycleCount;$i++)
            {
                $offsetMonths = ($i-1) * $stepMonths;
                $dueDate = ($offsetMonths<=0) ? $startDate : date('Y-m-d', strtotime($startDate.' +'.$offsetMonths.' month'));
                $planRows[] = array(
                    'PlansPayment' => array(
                        'deal_id' => $dealId,
                        'name' => $chargeName.' Charge '.$i.'/'.$cycleCount,
                        'amount' => $cycleAmount,
                        'date' => $dueDate,
                        'is_deal_charge' => 1,
                        'deal_charge_id' => $dealChargeId
                    )
                );
            }
            if(empty($planRows) || !$this->PlansPayment->saveAll($planRows, array('validate'=>false,'deep'=>false,'atomic'=>false)))
            {
                throw new Exception('Unable to save charge cycle rows.');
            }
            if(!$this->_applyDealTotalDelta($dealId, $totalAmount))
            {
                throw new Exception('Unable to recalculate deal total.');
            }
            $db->commit();
            $this->_syncDealChargeVoucher($dealId, $dealChargeId, (int)$dealProjectId, $totalAmount, $chargeName, $startDate);
            try
            {
                $this->_refreshDealChargeAutoLetter($dealId, (int)$dealProjectId, (int)$this->luserId, $chargeName);
            }
            catch(Exception $e)
            {
                // Do not block charge save if letter auto-generation fails.
            }
            $this->Session->setFlash(__('Deal charge added and linked to payment plan successfully.'),'flash',array('alert'=>'success'));
        }
        catch(Exception $e)
        {
            try
            {
                $db->rollback();
            }
            catch(Exception $rollbackException)
            {
                // Keep the original save error visible.
            }
            $this->Session->setFlash(__('Unable to add deal charge. Please try again.'),'flash',array('alert'=>'danger'));
        }
        return $this->redirect(array('action'=>'index',$dealId));
    }
    public function deletedealcharge($dealId=null,$chargeId=null)
    {
        $dealId = (int)$dealId;
        $chargeId = (int)$chargeId;
        if($dealId<=0 || $chargeId<=0)
        {
            $this->Session->setFlash(__('Invalid deal charge record.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action'=>'index',$dealId));
        }
        if(!$this->request->is(array('post','delete')))
        {
            return $this->redirect(array('action'=>'index',$dealId));
        }
        if(!$this->_dealChargeTableReady())
        {
            $this->Session->setFlash(__('Deal charges table is not ready yet.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action'=>'index',$dealId));
        }
        $this->loadModel('DealCharge');
        $chargeRow = $this->DealCharge->find('first', array(
            'conditions' => array('DealCharge.id' => $chargeId, 'DealCharge.deal_id' => $dealId, 'DealCharge.status' => 1),
            'recursive' => -1
        ));
        if(empty($chargeRow['DealCharge']['id']))
        {
            $this->Session->setFlash(__('Deal charge record not found.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action'=>'index',$dealId));
        }
        $paidCountRows = $this->PlansPayment->query(
            "SELECT COUNT(*) AS paid_count
             FROM `deals_payments` AS `DealsPayment`
             INNER JOIN `plans_payments` AS `PlansPayment` ON `PlansPayment`.`id`=`DealsPayment`.`plans_payment_id`
             WHERE `PlansPayment`.`deal_id`=".(int)$dealId."
               AND `PlansPayment`.`deal_charge_id`=".(int)$chargeId
        );
        $paidCount = 0;
        if(!empty($paidCountRows[0][0]['paid_count']))
        {
            $paidCount = (int)$paidCountRows[0][0]['paid_count'];
        }
        if($paidCount>0)
        {
            $this->Session->setFlash(__('This charge already has received payments. Please delete related receipts first.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action'=>'index',$dealId));
        }
        $chargeTotalAmount = isset($chargeRow['DealCharge']['total_amount']) ? (float)$chargeRow['DealCharge']['total_amount'] : 0;
        try
        {
            $chargePlanSumRows = $this->PlansPayment->query(
                "SELECT COALESCE(SUM(`amount`),0) AS plan_total
                 FROM `plans_payments`
                 WHERE `deal_id`=".(int)$dealId."
                   AND `deal_charge_id`=".(int)$chargeId."
                   AND `is_deal_charge`=1"
            );
            if(!empty($chargePlanSumRows[0][0]['plan_total']))
            {
                $chargeTotalAmount = (float)$chargePlanSumRows[0][0]['plan_total'];
            }
        }
        catch(Exception $e)
        {
            // Fallback to stored charge total.
        }
        $db = $this->DealCharge->getDataSource();
        $db->begin();
        try
        {
            $this->PlansPayment->deleteAll(array(
                'PlansPayment.deal_id' => $dealId,
                'PlansPayment.deal_charge_id' => $chargeId,
                'PlansPayment.is_deal_charge' => 1
            ), false, false);
            if(!$this->DealCharge->delete($chargeId, false))
            {
                throw new Exception('Unable to delete charge record.');
            }
            if(!$this->_applyDealTotalDelta($dealId, -1 * $chargeTotalAmount))
            {
                throw new Exception('Unable to recalculate deal total.');
            }
            $this->_voidDealChargeVoucher($chargeRow['DealCharge']);
            $db->commit();
            $this->Session->setFlash(__('Deal charge removed successfully.'),'flash',array('alert'=>'success'));
        }
        catch(Exception $e)
        {
            try
            {
                $db->rollback();
            }
            catch(Exception $rollbackException)
            {
                // Keep the original delete error visible.
            }
            $this->Session->setFlash(__('Unable to remove deal charge.'),'flash',array('alert'=>'danger'));
        }
        return $this->redirect(array('action'=>'index',$dealId));
    }
    public function editdealcharge($dealId=null,$chargeId=null)
    {
        $dealId = (int)$dealId;
        if($dealId<=0)
        {
            $this->Session->setFlash(__('Invalid deal.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('controller'=>'Deals','action'=>'index'));
        }
        if(!$this->request->is(array('post','put')))
        {
            return $this->redirect(array('action'=>'index',$dealId));
        }
        if(!$this->_dealChargeTableReady())
        {
            $this->Session->setFlash(__('Deal charges table is not ready yet.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action'=>'index',$dealId));
        }
        $payload = isset($this->request->data['DealCharge']) ? (array)$this->request->data['DealCharge'] : array();
        if((int)$chargeId <= 0 && !empty($payload['id']))
        {
            $chargeId = (int)$payload['id'];
        }
        $chargeId = (int)$chargeId;
        if($chargeId<=0)
        {
            $this->Session->setFlash(__('Invalid deal charge record.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action'=>'index',$dealId));
        }
        $this->loadModel('DealCharge');
        $chargeRow = $this->DealCharge->find('first', array(
            'conditions' => array('DealCharge.id' => $chargeId, 'DealCharge.deal_id' => $dealId, 'DealCharge.status' => 1),
            'recursive' => -1
        ));
        if(empty($chargeRow['DealCharge']['id']))
        {
            $this->Session->setFlash(__('Deal charge record not found.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action'=>'index',$dealId));
        }
        $dealProjectId = $this->_resolveDealProjectId($dealId);
        if($this->userType==='AGY' && $dealProjectId>0 && (int)$this->agencyId!==$dealProjectId)
        {
            $this->Session->setFlash(__('Invalid deal scope.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action'=>'index',$dealId));
        }
        if(!empty($chargeRow['DealCharge']['voucher_transaction_id']))
        {
            $lockMessage = $this->_ardTransactionLockedMessage((int)$chargeRow['DealCharge']['voucher_transaction_id'], (int)$dealProjectId);
            if($lockMessage !== '')
            {
                $this->Session->setFlash($lockMessage,'flash',array('alert'=>'danger'));
                return $this->redirect(array('action'=>'index',$dealId));
            }
        }

        $chargeName = isset($payload['charge_name']) ? trim((string)$payload['charge_name']) : '';
        $cycleType = isset($payload['cycle_type']) ? strtoupper(trim((string)$payload['cycle_type'])) : 'M';
        $cycleCount = isset($payload['cycle_count']) ? (int)$payload['cycle_count'] : 0;
        $cycleAmountRaw = isset($payload['cycle_amount']) ? (string)$payload['cycle_amount'] : '0';
        $cycleAmount = round((float)str_replace(array(',', ' '), '', $cycleAmountRaw), 2);
        $startDate = $this->_normalizePostedDate(isset($payload['start_date']) ? $payload['start_date'] : '');
        if($chargeName === '')
        {
            $this->Session->setFlash(__('Please enter charge type/name.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action'=>'index',$dealId));
        }
        if(!array_key_exists($cycleType, $this->_dealChargeCycleOptions()))
        {
            $cycleType = 'M';
        }
        if($cycleCount<=0)
        {
            $cycleCount=1;
        }
        if($cycleAmount<=0)
        {
            $this->Session->setFlash(__('Please enter valid charge amount per cycle.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action'=>'index',$dealId));
        }
        if($startDate === '')
        {
            $this->Session->setFlash(__('Please select first cycle start date.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action'=>'index',$dealId));
        }

        $planRows = $this->_dealChargePlanRowsWithPaymentMeta($dealId, $chargeId);
        $oldTotalAmount = 0;
        $lockedRows = array();
        $unlockedRows = array();
        $lockedAmountTotal = 0;
        $lockedMaxDate = '';
        foreach($planRows as $planRow)
        {
            $oldTotalAmount += isset($planRow['amount']) ? (float)$planRow['amount'] : 0;
            $isLocked = ((int)$planRow['receipt_count'] > 0 || (float)$planRow['paid_total'] > 0);
            if($isLocked)
            {
                $lockedRows[] = $planRow;
                $lockedAmountTotal += isset($planRow['amount']) ? (float)$planRow['amount'] : 0;
                if(!empty($planRow['date']) && ($lockedMaxDate === '' || $planRow['date'] > $lockedMaxDate))
                {
                    $lockedMaxDate = $planRow['date'];
                }
            }
            else
            {
                $unlockedRows[] = $planRow;
            }
        }
        $oldTotalAmount = round((float)$oldTotalAmount, 2);
        if($oldTotalAmount <= 0 && isset($chargeRow['DealCharge']['total_amount']))
        {
            $oldTotalAmount = round((float)$chargeRow['DealCharge']['total_amount'], 2);
        }
        $lockedAmountTotal = round((float)$lockedAmountTotal, 2);
        $lockedCount = count($lockedRows);
        $requestedCycleCount = $cycleCount;
        if($cycleCount < $lockedCount)
        {
            $cycleCount = $lockedCount;
        }
        $remainingCount = max(0, $cycleCount - $lockedCount);
        $stepMonths = $this->_cycleStepByType($cycleType);
        $newRows = array();
        if($remainingCount > 0)
        {
            $firstDate = $startDate;
            if($lockedCount > 0)
            {
                $baseDate = $lockedMaxDate !== '' ? $lockedMaxDate : $startDate;
                $firstDate = date('Y-m-d', strtotime($baseDate.' +'.$stepMonths.' month'));
            }
            for($i = 1; $i <= $remainingCount; $i++)
            {
                $offsetMonths = ($i - 1) * $stepMonths;
                $dueDate = ($offsetMonths<=0) ? $firstDate : date('Y-m-d', strtotime($firstDate.' +'.$offsetMonths.' month'));
                $serial = $lockedCount + $i;
                $newRows[] = array(
                    'PlansPayment' => array(
                        'deal_id' => $dealId,
                        'name' => $chargeName.' Charge '.$serial.'/'.$cycleCount,
                        'amount' => $cycleAmount,
                        'date' => $dueDate,
                        'is_deal_charge' => 1,
                        'deal_charge_id' => $chargeId
                    )
                );
            }
        }
        $newTotalAmount = round($lockedAmountTotal + ($remainingCount * $cycleAmount), 2);
        $deltaAmount = round($newTotalAmount - $oldTotalAmount, 2);
        $deleteIds = array();
        foreach($unlockedRows as $unlockedRow)
        {
            if(!empty($unlockedRow['id']))
            {
                $deleteIds[] = (int)$unlockedRow['id'];
            }
        }

        $db = $this->DealCharge->getDataSource();
        $db->begin();
        try
        {
            if(!empty($deleteIds))
            {
                $this->PlansPayment->deleteAll(array(
                    'PlansPayment.id' => $deleteIds,
                    'PlansPayment.deal_id' => $dealId,
                    'PlansPayment.deal_charge_id' => $chargeId,
                    'PlansPayment.is_deal_charge' => 1
                ), false, false);
            }
            if(!empty($newRows))
            {
                if(!$this->PlansPayment->saveAll($newRows, array('validate'=>false,'deep'=>false,'atomic'=>false)))
                {
                    throw new Exception('Unable to regenerate unpaid charge cycles.');
                }
            }
            $this->DealCharge->id = $chargeId;
            $saveChargeOk = $this->DealCharge->save(array('DealCharge'=>array(
                'id' => $chargeId,
                'charge_name' => $chargeName,
                'cycle_type' => $cycleType,
                'cycle_count' => (int)$cycleCount,
                'cycle_amount' => $cycleAmount,
                'total_amount' => $newTotalAmount,
                'start_date' => $startDate
            )), false);
            if(!$saveChargeOk)
            {
                throw new Exception('Unable to update charge master row.');
            }
            if(abs((float)$deltaAmount) > 0 && !$this->_applyDealTotalDelta($dealId, $deltaAmount))
            {
                throw new Exception('Unable to update deal total amount.');
            }
            $db->commit();
            $this->_syncDealChargeVoucher($dealId, $chargeId, (int)$dealProjectId, $newTotalAmount, $chargeName, $startDate);
            try
            {
                $this->_refreshDealChargeAutoLetter($dealId, (int)$dealProjectId, (int)$this->luserId, $chargeName);
            }
            catch(Exception $e)
            {
                // Do not block charge update if letter auto-generation fails.
            }
            $msg = __('Deal charge updated successfully. Paid cycles were preserved, and unpaid cycles were regenerated.');
            if($requestedCycleCount < $lockedCount)
            {
                $msg .= ' '.__('Cycle count was auto-adjusted to keep already paid cycles safe.');
            }
            $this->Session->setFlash($msg,'flash',array('alert'=>'success'));
        }
        catch(Exception $e)
        {
            try
            {
                $db->rollback();
            }
            catch(Exception $rollbackException)
            {
                // Keep the original update error visible.
            }
            $this->Session->setFlash(__('Unable to update deal charge. Please try again.'),'flash',array('alert'=>'danger'));
        }
        return $this->redirect(array('action'=>'index',$dealId));
    }

    protected function _syncDealChargeVoucher($dealId, $chargeId, $projectId, $amount, $chargeName, $entryDate)
    {
        $dealId = (int)$dealId;
        $chargeId = (int)$chargeId;
        $projectId = (int)$projectId;
        $amount = round((float)$amount, 2);
        if($dealId <= 0 || $chargeId <= 0 || $projectId <= 0 || $amount <= 0)
        {
            return 0;
        }
        $this->loadModel('Admin.DealCharge');
        $existingId = 0;
        $chargeRow = $this->DealCharge->find('first', array(
            'fields' => array('DealCharge.id', 'DealCharge.voucher_transaction_id'),
            'conditions' => array('DealCharge.id' => $chargeId),
            'recursive' => -1
        ));
        if(!empty($chargeRow['DealCharge']['voucher_transaction_id']))
        {
            $existingId = (int)$chargeRow['DealCharge']['voucher_transaction_id'];
        }
        $sourceLedgerId = $this->_ardGetOrCreateLedger($projectId, 'Deal Receivables', 'asset', 'Auto Deal Receivable');
        if($sourceLedgerId <= 0)
        {
            $sourceLedgerId = $this->_ardDefaultSourceLedgerId($projectId);
        }
        $refNo = 'CHG-'.$dealId.'-'.$chargeId;
        $txId = $this->_ardPostAutoVoucher(
            $projectId,
            $sourceLedgerId,
            'income',
            $amount,
            $refNo,
            $entryDate,
            'Deal charge: '.$chargeName.' | Deal #'.$dealId,
            array(
                'existing_id' => $existingId,
                'income_settings_field' => 'deal_charges_income_ledger_id',
                'income_fallback' => 'Deal Charges Income',
                'source_type' => 'deal_charge',
                'source_id' => $chargeId,
                'status' => 'Posted'
            )
        );
        if($txId > 0 && $this->DealCharge->hasField('voucher_transaction_id'))
        {
            $this->DealCharge->id = $chargeId;
            $this->DealCharge->saveField('voucher_transaction_id', $txId, false);
        }
        return $txId;
    }

    protected function _voidDealChargeVoucher($chargeRow)
    {
        $chargeRow = is_array($chargeRow) ? $chargeRow : array();
        $txId = !empty($chargeRow['voucher_transaction_id']) ? (int)$chargeRow['voucher_transaction_id'] : 0;
        if($txId <= 0)
        {
            return true;
        }
        $this->loadModel('Admin.Ardtransaction');
        $txRow = $this->Ardtransaction->find('first', array('conditions' => array('Ardtransaction.id' => $txId), 'recursive' => -1));
        if(empty($txRow['Ardtransaction']))
        {
            return true;
        }
        $lockMessage = $this->_ardTransactionLockedMessage($txRow['Ardtransaction'], (int)$txRow['Ardtransaction']['project_id']);
        if($lockMessage !== '')
        {
            throw new Exception($lockMessage);
        }
        $ledgerIds = array(
            (int)$txRow['Ardtransaction']['ledger_id'],
            (int)(isset($txRow['Ardtransaction']['income_ledger_id']) ? $txRow['Ardtransaction']['income_ledger_id'] : 0),
            (int)(isset($txRow['Ardtransaction']['expense_ledger_id']) ? $txRow['Ardtransaction']['expense_ledger_id'] : 0)
        );
        $this->Ardtransaction->id = $txId;
        $this->Ardtransaction->save(array('Ardtransaction' => $this->_ardDecorateTransactionPayload(array(
            'id' => $txId,
            'status' => 'Void',
            'lock_note' => 'Voided because linked deal charge was removed'
        ), array('status' => 'Void'))), false);
        foreach(array_unique(array_filter($ledgerIds)) as $ledgerId)
        {
            $this->_ardRecalculateLedgerBalanceCompat((int)$ledgerId);
        }
        return true;
    }
}
