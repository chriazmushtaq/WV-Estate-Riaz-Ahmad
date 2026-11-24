<?php
class PropertiesController extends AdminAppController {
    public $helpers = array('Html', 'Form','Session','Paginator','Js'=> array('Jquery'));
    public $components = array('Session','Paginator','search-master.Prg','CustomFunction');
    public $presetVars = true;
    var $paginate = array('page'=>1,'order'=>array('Property.id'=>'desc'));
    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->loadModel('Admin.Addresshierarchy');
        $this->Addresshierarchy->ensureSchema();
        $this->Addresshierarchy->ensureTargetColumns('properties');
        $this->_ensurePropertyNearbyColumn();
        $this->_ensurePropertyPlanPdfColumns();
        $this->_ensurePropertySurroundingsTable();
        try { $this->Property->schema(true); } catch(Exception $e) {}
    }
        protected function _ensurePropertyPlanPdfColumns()
    {
        static $ready = null;
        if($ready !== null)
        {
            return $ready;
        }
        $ready = false;
        try
        {
            $cols = $this->Property->query("SHOW COLUMNS FROM `properties` LIKE 'payment_plan_pdf'");
            if(empty($cols))
            {
                $this->Property->query("ALTER TABLE `properties` ADD `payment_plan_pdf` VARCHAR(255) NULL DEFAULT NULL AFTER `nearby_places_json`");
            }
            $cols2 = $this->Property->query("SHOW COLUMNS FROM `properties` LIKE 'assigned_agent_id'");
            if(empty($cols2))
            {
                $this->Property->query("ALTER TABLE `properties` ADD `assigned_agent_id` INT(11) NULL DEFAULT NULL AFTER `payment_plan_pdf`");
            }
            $this->Property->schema(true);
            $ready = true;
        }
        catch(Exception $e)
        {
            $ready = false;
        }
        return $ready;
    }
    protected function _handlePaymentPlanPdfUpload($fileArray, $existingFile = null, $remove = false)
    {
        if ($remove) {
            return '';
        }
        if (empty($fileArray) || !is_array($fileArray) || empty($fileArray['tmp_name']) || !is_uploaded_file($fileArray['tmp_name'])) {
            return $existingFile;
        }
        $ext = strtolower(pathinfo($fileArray['name'], PATHINFO_EXTENSION));
        if ($ext !== 'pdf') {
            return $existingFile;
        }
        $targetDir = WWW_ROOT . 'files' . DS . 'payment_plans';
        if (!is_dir($targetDir)) {
            @mkdir($targetDir, 0777, true);
        }
        $fileName = 'plan-' . date('YmdHis') . '-' . mt_rand(1000, 9999) . '.pdf';
        $targetPath = $targetDir . DS . $fileName;
        if (move_uploaded_file($fileArray['tmp_name'], $targetPath)) {
            return $fileName;
        }
        return $existingFile;
    }
    protected function _ensurePropertyNearbyColumn()
    {
        static $ready = null;
        if($ready !== null)
        {
            return $ready;
        }
        $ready = false;
        try
        {
            $exists = $this->Property->query("SHOW COLUMNS FROM `properties` LIKE 'nearby_places_json'");
            if(empty($exists))
            {
                $this->Property->query("ALTER TABLE `properties` ADD `nearby_places_json` longtext NULL");
            }
            $this->Property->schema(true);
            $ready = true;
        }
        catch(Exception $e)
        {
            $ready = false;
        }
        return $ready;
    }
    protected function _ensurePropertySurroundingsTable()
    {
        static $ready = null;
        if($ready !== null)
        {
            return $ready;
        }
        try
        {
            $this->Property->query("CREATE TABLE IF NOT EXISTS `property_surroundings` (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `property_id` bigint(20) NOT NULL,
                `name` varchar(191) NOT NULL,
                `category` varchar(80) DEFAULT NULL,
                `distance` int(11) NOT NULL DEFAULT 0,
                `sort_order` int(11) NOT NULL DEFAULT 0,
                `created` datetime DEFAULT NULL,
                `modified` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `property_id` (`property_id`),
                KEY `category` (`category`),
                KEY `sort_order` (`sort_order`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8");
            $ready = true;
        }
        catch(Exception $e)
        {
            $ready = false;
        }
        return $ready;
    }
    protected function _normalizePropertyNearbyPlaces(&$propertyData)
    {
        if(!is_array($propertyData))
        {
            return;
        }
        if(!$this->Property->hasField('nearby_places_json'))
        {
            return;
        }
        $raw = isset($propertyData['nearby_places_json']) ? trim((string)$propertyData['nearby_places_json']) : '';
        if($raw === '')
        {
            $propertyData['nearby_places_json'] = null;
            return;
        }
        $rows = json_decode($raw, true);
        if(!is_array($rows))
        {
            $propertyData['nearby_places_json'] = null;
            return;
        }
        $clean = array();
        foreach($rows as $row)
        {
            if(!is_array($row))
            {
                continue;
            }
            $name = isset($row['name']) ? trim((string)$row['name']) : '';
            if($name === '')
            {
                continue;
            }
            $category = isset($row['category']) ? trim((string)$row['category']) : __('Nearby');
            $distance = isset($row['distance']) ? (int)$row['distance'] : 0;
            $clean[] = array(
                'name' => substr($name, 0, 120),
                'category' => substr($category, 0, 50),
                'distance' => max(0, min(50000, $distance))
            );
            if(count($clean) >= 40)
            {
                break;
            }
        }
        $propertyData['nearby_places_json'] = !empty($clean) ? json_encode($clean) : null;
    }
    protected function _decodePropertyNearbyPlaces($nearbyJson)
    {
        $rows = json_decode((string)$nearbyJson, true);
        if(!is_array($rows))
        {
            return array();
        }
        $clean = array();
        foreach($rows as $row)
        {
            if(!is_array($row))
            {
                continue;
            }
            $name = isset($row['name']) ? trim((string)$row['name']) : '';
            if($name === '')
            {
                continue;
            }
            $clean[] = array(
                'name' => substr($name, 0, 120),
                'category' => substr(isset($row['category']) ? trim((string)$row['category']) : __('Nearby'), 0, 50),
                'distance' => max(0, min(50000, isset($row['distance']) ? (int)$row['distance'] : 0))
            );
            if(count($clean) >= 40)
            {
                break;
            }
        }
        return $clean;
    }
    protected function _savePropertySurroundingsRows($propertyId, $nearbyJson)
    {
        $propertyId = (int)$propertyId;
        if($propertyId<=0 || !$this->_ensurePropertySurroundingsTable())
        {
            return false;
        }
        $rows = $this->_decodePropertyNearbyPlaces($nearbyJson);
        $db = $this->Property->getDataSource();
        $this->Property->query("DELETE FROM `property_surroundings` WHERE `property_id`=".$propertyId);
        $now = date('Y-m-d H:i:s');
        foreach($rows as $idx=>$row)
        {
            $this->Property->query(
                "INSERT INTO `property_surroundings` (`property_id`,`name`,`category`,`distance`,`sort_order`,`created`,`modified`) VALUES (".
                $propertyId.",".
                $db->value($row['name'], 'string').",".
                $db->value($row['category'], 'string').",".
                (int)$row['distance'].",".
                (int)$idx.",".
                $db->value($now, 'string').",".
                $db->value($now, 'string').
                ")"
            );
        }
        return true;
    }
    protected function _propertySurroundingsJson($propertyId, $fallbackJson='')
    {
        $propertyId = (int)$propertyId;
        $rows = array();
        if($propertyId>0 && $this->_ensurePropertySurroundingsTable())
        {
            try
            {
                $surroundings = (array)$this->Property->query("SELECT `name`,`category`,`distance` FROM `property_surroundings` WHERE `property_id`=".$propertyId." ORDER BY `sort_order` ASC, `id` ASC");
                foreach($surroundings as $row)
                {
                    $src = !empty($row['property_surroundings']) ? $row['property_surroundings'] : (!empty($row[0]) ? $row[0] : array());
                    if(!empty($src['name']))
                    {
                        $rows[] = array(
                            'name'=>(string)$src['name'],
                            'category'=>!empty($src['category']) ? (string)$src['category'] : __('Nearby'),
                            'distance'=>isset($src['distance']) ? (int)$src['distance'] : 0
                        );
                    }
                }
            }
            catch(Exception $e)
            {
                $rows = array();
            }
        }
        if(empty($rows) && $fallbackJson !== '')
        {
            $rows = $this->_decodePropertyNearbyPlaces($fallbackJson);
        }
        return !empty($rows) ? json_encode($rows) : '[]';
    }
    protected function _savePropertyNearbyPlacesColumn($propertyId, $nearbyJson)
    {
        $propertyId = (int)$propertyId;
        if($propertyId<=0 || !$this->_ensurePropertyNearbyColumn())
        {
            return false;
        }
        $db = $this->Property->getDataSource();
        $value = ($nearbyJson === null || $nearbyJson === '') ? 'NULL' : $db->value($nearbyJson, 'text');
        return (bool)$this->Property->updateAll(
            array('Property.nearby_places_json'=>$value),
            array('Property.id'=>$propertyId)
        );
    }
    protected function _savePropertySurroundings($propertyId, $nearbyJson)
    {
        $this->_savePropertyNearbyPlacesColumn($propertyId, $nearbyJson);
        return $this->_savePropertySurroundingsRows($propertyId, $nearbyJson);
    }
    protected function _setAddressHierarchyVars()
    {
        $this->loadModel('Admin.Addresshierarchy');
        $this->Addresshierarchy->ensureSchema();
        $this->set('addressHierarchyTree', $this->Addresshierarchy->treePayload());
        $this->set('addressCountryOptions', $this->Addresshierarchy->levelOptions('country', 0));
        $this->set('addressLevelOrder', $this->Addresshierarchy->levelOrder);
    }
    protected function _normalizePropertyAddressHierarchy(&$propertyData)
    {
        $this->loadModel('Admin.Addresshierarchy');
        $this->Addresshierarchy->ensureSchema();
        $this->Addresshierarchy->normalizeHierarchyData($propertyData);
    }
    protected function _canAccessPropertyRecord($propertyRow)
    {
        if ($this->_isSuperAdminSession() || strtoupper(trim((string)$this->userType)) === 'ADR') {
            return true;
        }
        $propertyRow = (array)$propertyRow;
        $row = isset($propertyRow['Property']) ? (array)$propertyRow['Property'] : $propertyRow;
        if (empty($row['id'])) {
            return false;
        }
        if ($this->_isAgencyAdminSession() || strtoupper(trim((string)$this->userType)) === 'AGY') {
            return ((int)$row['project_id'] === (int)$this->agencyId);
        }
        return ((int)$row['user_id'] === (int)$this->luserId || (!empty($this->agencyId) && (int)$row['project_id'] === (int)$this->agencyId));
    }
    protected function _ensurePropertyHistoryTable()
    {
        static $ready = null;
        if($ready !== null)
        {
            return $ready;
        }
        $this->loadModel('PropertyHistory');
        try
        {
            $this->PropertyHistory->query("CREATE TABLE IF NOT EXISTS `property_histories` (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `property_id` bigint(20) NOT NULL,
                `deal_id` bigint(20) DEFAULT NULL,
                `client_id` bigint(20) DEFAULT NULL,
                `project_id` bigint(20) DEFAULT NULL,
                `actor_user_id` bigint(20) DEFAULT NULL,
                `event_type` varchar(50) NOT NULL DEFAULT 'change',
                `title` varchar(191) DEFAULT NULL,
                `old_value` text NULL,
                `new_value` text NULL,
                `amount_delta` decimal(14,2) NOT NULL DEFAULT 0.00,
                `meta_json` longtext NULL,
                `created` datetime DEFAULT NULL,
                `modified` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `property_id` (`property_id`),
                KEY `deal_id` (`deal_id`),
                KEY `client_id` (`client_id`),
                KEY `event_type` (`event_type`),
                KEY `created` (`created`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8");
            $this->PropertyHistory->schema(true);
            $ready = true;
            return $ready;
        }
        catch(Exception $e)
        {
            $ready = false;
            return $ready;
        }
    }
    protected function _recordPropertyHistory($propertyId, $eventType, $title, $oldValue='', $newValue='', $options=array())
    {
        if(!$this->_ensurePropertyHistoryTable())
        {
            return false;
        }
        $this->PropertyHistory->create();
        return (bool)$this->PropertyHistory->save(array('PropertyHistory'=>array(
            'property_id'=>(int)$propertyId,
            'deal_id'=>isset($options['deal_id']) ? (int)$options['deal_id'] : null,
            'client_id'=>isset($options['client_id']) ? (int)$options['client_id'] : null,
            'project_id'=>isset($options['project_id']) ? (int)$options['project_id'] : null,
            'actor_user_id'=>(int)$this->luserId,
            'event_type'=>$eventType,
            'title'=>$title,
            'old_value'=>(string)$oldValue,
            'new_value'=>(string)$newValue,
            'amount_delta'=>isset($options['amount_delta']) ? round((float)$options['amount_delta'],2) : 0,
            'meta_json'=>!empty($options['meta']) ? json_encode($options['meta']) : null
        )),false);
    }
    protected function _sumPaidByPlanIds($dealId, $planIds)
    {
        $paidMap = array();
        $planIds = array_values(array_unique(array_filter(array_map('intval',(array)$planIds))));
        if(empty($planIds))
        {
            return $paidMap;
        }
        $this->loadModel('DealsPayment');
        $paidRows = (array)$this->DealsPayment->find('all',array(
            'fields'=>array('DealsPayment.plans_payment_id','SUM(DealsPayment.emi) AS paid_total'),
            'conditions'=>array('DealsPayment.deal_id'=>(int)$dealId,'DealsPayment.plans_payment_id'=>$planIds),
            'group'=>array('DealsPayment.plans_payment_id'),
            'recursive'=>-1
        ));
        foreach($paidRows as $paidRow)
        {
            $planId = isset($paidRow['DealsPayment']['plans_payment_id']) ? (int)$paidRow['DealsPayment']['plans_payment_id'] : 0;
            if($planId>0)
            {
                $paidMap[$planId] = isset($paidRow[0]['paid_total']) ? round((float)$paidRow[0]['paid_total'],2) : 0;
            }
        }
        return $paidMap;
    }
    protected function _adjustLastUnpaidSchedule($dealId, $amountDelta)
    {
        $amountDelta = round((float)$amountDelta,2);
        if(abs($amountDelta)<0.01)
        {
            return true;
        }
        $this->loadModel('PlansPayment');
        $conditions = array('PlansPayment.deal_id'=>(int)$dealId);
        if($this->PlansPayment->hasField('is_deal_charge'))
        {
            $conditions[] = array('OR'=>array('PlansPayment.is_deal_charge'=>0,'PlansPayment.is_deal_charge'=>null));
        }
        $plans = (array)$this->PlansPayment->find('all',array(
            'fields'=>array('PlansPayment.id','PlansPayment.name','PlansPayment.amount','PlansPayment.date'),
            'conditions'=>$conditions,
            'order'=>array('PlansPayment.date'=>'asc','PlansPayment.id'=>'asc'),
            'recursive'=>-1
        ));
        $planIds = array();
        foreach($plans as $planRow)
        {
            if(!empty($planRow['PlansPayment']['id']))
            {
                $planIds[] = (int)$planRow['PlansPayment']['id'];
            }
        }
        $paidMap = $this->_sumPaidByPlanIds($dealId,$planIds);
        $unpaidPlans = array();
        $totalPending = 0.0;
        foreach($plans as $planRow)
        {
            $planId = (int)$planRow['PlansPayment']['id'];
            $amount = round((float)$planRow['PlansPayment']['amount'],2);
            $paid = isset($paidMap[$planId]) ? round((float)$paidMap[$planId],2) : 0;
            $pending = round($amount-$paid,2);
            if($pending>0)
            {
                $planRow['PlansPayment']['paid'] = $paid;
                $planRow['PlansPayment']['pending'] = $pending;
                $unpaidPlans[] = $planRow;
                $totalPending = round($totalPending+$pending,2);
            }
        }
        if($amountDelta>0)
        {
            if(empty($unpaidPlans))
            {
                $lastDate = !empty($plans) ? $plans[count($plans)-1]['PlansPayment']['date'] : date('Y-m-d');
                $this->PlansPayment->create();
                return (bool)$this->PlansPayment->save(array('PlansPayment'=>array(
                    'deal_id'=>(int)$dealId,
                    'name'=>'Price Adjustment',
                    'amount'=>$amountDelta,
                    'date'=>$lastDate
                )),false);
            }
            $target = $unpaidPlans[count($unpaidPlans)-1]['PlansPayment'];
            $this->PlansPayment->id = (int)$target['id'];
            return (bool)$this->PlansPayment->saveField('amount',round((float)$target['amount']+$amountDelta,2),false);
        }
        $reduceAmount = abs($amountDelta);
        if($reduceAmount>$totalPending)
        {
            throw new Exception(__('Property price decrease is greater than unpaid schedule balance. Paid receipts were not changed.'));
        }
        for($i=count($unpaidPlans)-1;$i>=0 && $reduceAmount>0.009;$i--)
        {
            $target = $unpaidPlans[$i]['PlansPayment'];
            $take = min($reduceAmount,(float)$target['pending']);
            $newAmount = round((float)$target['amount']-$take,2);
            if($newAmount < (float)$target['paid'])
            {
                throw new Exception(__('Cannot reduce a schedule below the amount already paid.'));
            }
            $this->PlansPayment->id = (int)$target['id'];
            if(!$this->PlansPayment->saveField('amount',$newAmount,false))
            {
                return false;
            }
            $reduceAmount = round($reduceAmount-$take,2);
        }
        return true;
    }
    protected function _activeDealChargeTotal($dealId)
    {
        $dealId = (int)$dealId;
        if($dealId<=0)
        {
            return 0.0;
        }
        try
        {
            $rows = (array)$this->Property->query("SHOW TABLES LIKE 'deal_charges'");
            if(empty($rows))
            {
                return 0.0;
            }
            $chargeRows = (array)$this->Property->query(
                "SELECT COALESCE(SUM(`total_amount`),0) AS charge_total FROM `deal_charges` WHERE `status`=1 AND `deal_id`=".$dealId
            );
            if(!empty($chargeRows[0][0]['charge_total']))
            {
                return round((float)$chargeRows[0][0]['charge_total'],2);
            }
        }
        catch(Exception $e)
        {
            return 0.0;
        }
        return 0.0;
    }
    protected function _saveDealPriceTotals($dealId, $propertyPrice, $totalAmount)
    {
        $dealId = (int)$dealId;
        if($dealId<=0)
        {
            throw new Exception(__('Unable to update linked deal total.'));
        }
        $propertyPrice = round((float)$propertyPrice,2);
        $totalAmount = round((float)$totalAmount,2);
        $this->loadModel('Deal');
        $hasDealAmountField = $this->Deal->hasField('amount');
        $dealSaveData = array('id'=>$dealId,'total_amount'=>$totalAmount);
        if($hasDealAmountField)
        {
            $dealSaveData['amount'] = $propertyPrice;
        }
        $this->Deal->id = $dealId;
        $saved = (bool)$this->Deal->save(array('Deal'=>$dealSaveData),false);
        $verifyFields = array('Deal.id','Deal.total_amount');
        if($hasDealAmountField)
        {
            $verifyFields[] = 'Deal.amount';
        }
        $verify = $this->Deal->find('first',array(
            'fields'=>$verifyFields,
            'conditions'=>array('Deal.id'=>$dealId),
            'recursive'=>-1
        ));
        $savedTotal = isset($verify['Deal']['total_amount']) ? round((float)$verify['Deal']['total_amount'],2) : null;
        $savedBase = ($hasDealAmountField && isset($verify['Deal']['amount'])) ? round((float)$verify['Deal']['amount'],2) : $propertyPrice;
        if(!$saved || $savedTotal===null || abs($savedTotal-$totalAmount)>=0.01 || ($hasDealAmountField && abs($savedBase-$propertyPrice)>=0.01))
        {
            $db = $this->Deal->getDataSource();
            $updateFields = array('Deal.total_amount'=>$db->value($totalAmount,'float'));
            if($hasDealAmountField)
            {
                $updateFields['Deal.amount'] = $db->value($propertyPrice,'float');
            }
            if(!$this->Deal->updateAll($updateFields,array('Deal.id'=>$dealId)))
            {
                throw new Exception(__('Unable to update linked deal total.'));
            }
            $verify = $this->Deal->find('first',array(
                'fields'=>$verifyFields,
                'conditions'=>array('Deal.id'=>$dealId),
                'recursive'=>-1
            ));
            $savedTotal = isset($verify['Deal']['total_amount']) ? round((float)$verify['Deal']['total_amount'],2) : null;
            $savedBase = ($hasDealAmountField && isset($verify['Deal']['amount'])) ? round((float)$verify['Deal']['amount'],2) : $propertyPrice;
            if($savedTotal===null || abs($savedTotal-$totalAmount)>=0.01 || ($hasDealAmountField && abs($savedBase-$propertyPrice)>=0.01))
            {
                throw new Exception(__('Unable to update linked deal total.'));
            }
        }
        return true;
    }
    protected function _recordOwnerDealPriceAdjustment($propertyId, $deal=array(), $oldTotal=0, $newTotal=0, $amountDelta=0, $meta=array())
    {
        $deal = (array)$deal;
        $dealId = !empty($deal['id']) ? (int)$deal['id'] : 0;
        if($dealId<=0)
        {
            return false;
        }
        return $this->_recordPropertyHistory($propertyId,'owner_deal_price_adjusted',__('Owner Deal Price Adjusted'),$oldTotal,$newTotal,array(
            'deal_id'=>$dealId,
            'client_id'=>isset($deal['client_id']) ? (int)$deal['client_id'] : null,
            'amount_delta'=>$amountDelta,
            'meta'=>$meta
        ));
    }
    protected function _adjustActiveDealsForPropertyPriceChange($propertyId, $oldPrice, $newPrice)
    {
        $this->loadModel('Deal');
        $conditions = array('Deal.property_id'=>(int)$propertyId);
        if($this->Deal->hasField('deal_status'))
        {
            $conditions[] = array('OR'=>array('Deal.deal_status'=>'Active','Deal.deal_status'=>null,'Deal.deal_status'=>''));
        }
        $fields = array('Deal.id','Deal.property_id','Deal.client_id','Deal.discount','Deal.total_amount');
        $hasDealAmountField = $this->Deal->hasField('amount');
        if($hasDealAmountField)$fields[] = 'Deal.amount';
        if($this->Deal->hasField('deal_status'))$fields[] = 'Deal.deal_status';
        $deals = (array)$this->Deal->find('all',array('fields'=>$fields,'conditions'=>$conditions,'recursive'=>-1));
        foreach($deals as $dealRow)
        {
            $deal = $dealRow['Deal'];
            $dealId = (int)$deal['id'];
            $oldDealBase = ($hasDealAmountField && isset($deal['amount'])) ? round((float)$deal['amount'],2) : round((float)$oldPrice,2);
            $discount = isset($deal['discount']) ? round((float)$deal['discount'],2) : 0;
            $currentTotal = isset($deal['total_amount']) ? round((float)$deal['total_amount'],2) : 0;
            $oldNetBase = max(0,round($oldDealBase-$discount,2));
            $preservedCharges = $hasDealAmountField ? max(0,round($currentTotal-$oldNetBase,2)) : $this->_activeDealChargeTotal($dealId);
            $newNetBase = max(0,round((float)$newPrice-$discount,2));
            $newTotal = round($newNetBase+$preservedCharges,2);
            $delta = round($newTotal-$currentTotal,2);
            $this->loadModel('DealsPayment');
            $paidTotalRow = $this->DealsPayment->find('first',array(
                'fields'=>array('SUM(DealsPayment.emi) AS paid_total'),
                'conditions'=>array('DealsPayment.deal_id'=>$dealId),
                'recursive'=>-1
            ));
            $paidTotal = !empty($paidTotalRow[0]['paid_total']) ? round((float)$paidTotalRow[0]['paid_total'],2) : 0;
            if($newTotal < $paidTotal)
            {
                throw new Exception(__('New deal total is less than already paid receipts. Paid receipts were not changed.'));
            }
            if(abs($delta)<0.01)
            {
                if($hasDealAmountField)
                {
                    $this->_saveDealPriceTotals($dealId,$newPrice,$currentTotal);
                }
                continue;
            }
            $this->_adjustLastUnpaidSchedule($dealId,$delta);
            $this->_saveDealPriceTotals($dealId,$newPrice,$newTotal);
            $this->_recordPropertyHistory($propertyId,'deal_price_adjusted',__('Deal Total Adjusted'),$currentTotal,$newTotal,array(
                'deal_id'=>$dealId,
                'client_id'=>isset($deal['client_id']) ? (int)$deal['client_id'] : null,
                'amount_delta'=>$delta,
                'meta'=>array('old_property_price'=>$oldPrice,'new_property_price'=>$newPrice,'discount'=>$discount)
            ));
            $this->_recordOwnerDealPriceAdjustment($propertyId,$deal,$currentTotal,$newTotal,$delta,array(
                'old_property_price'=>$oldPrice,
                'new_property_price'=>$newPrice,
                'discount'=>$discount,
                'source'=>'property_price_change'
            ));
        }
    }
    protected function _syncActiveDealsForCurrentPropertyPrice($propertyRow)
    {
        $property = isset($propertyRow['Property']) ? (array)$propertyRow['Property'] : (array)$propertyRow;
        $propertyId = !empty($property['id']) ? (int)$property['id'] : 0;
        if($propertyId<=0 || !isset($property['price']))
        {
            return;
        }
        $this->loadModel('Deal');
        $conditions = array('Deal.property_id'=>$propertyId);
        if($this->Deal->hasField('deal_status'))
        {
            $conditions[] = array('OR'=>array('Deal.deal_status'=>'Active','Deal.deal_status'=>null,'Deal.deal_status'=>''));
        }
        $fields = array('Deal.id','Deal.client_id','Deal.discount','Deal.total_amount');
        if($this->Deal->hasField('amount'))$fields[] = 'Deal.amount';
        $deals = (array)$this->Deal->find('all',array('fields'=>$fields,'conditions'=>$conditions,'recursive'=>-1));
        foreach($deals as $dealRow)
        {
            $deal = isset($dealRow['Deal']) ? (array)$dealRow['Deal'] : array();
            $dealId = !empty($deal['id']) ? (int)$deal['id'] : 0;
            if($dealId<=0)
            {
                continue;
            }
            $discount = isset($deal['discount']) ? round((float)$deal['discount'],2) : 0;
            $currentTotal = isset($deal['total_amount']) ? round((float)$deal['total_amount'],2) : 0;
            $chargeTotal = $this->_activeDealChargeTotal($dealId);
            $newPropertyPrice = round((float)$property['price'],2);
            $expectedTotal = round(max(0,$newPropertyPrice-$discount)+$chargeTotal,2);
            $delta = round($expectedTotal-$currentTotal,2);
            if(abs($delta)<0.01)
            {
                if($this->Deal->hasField('amount'))
                {
                    $this->_saveDealPriceTotals($dealId,$newPropertyPrice,$currentTotal);
                }
                continue;
            }
            $this->loadModel('DealsPayment');
            $paidTotalRow = $this->DealsPayment->find('first',array(
                'fields'=>array('SUM(DealsPayment.emi) AS paid_total'),
                'conditions'=>array('DealsPayment.deal_id'=>$dealId),
                'recursive'=>-1
            ));
            $paidTotal = !empty($paidTotalRow[0]['paid_total']) ? round((float)$paidTotalRow[0]['paid_total'],2) : 0;
            if($expectedTotal < $paidTotal)
            {
                throw new Exception(__('New deal total is less than already paid receipts. Paid receipts were not changed.'));
            }
            $this->_adjustLastUnpaidSchedule($dealId,$delta);
            $this->_saveDealPriceTotals($dealId,$newPropertyPrice,$expectedTotal);
            $this->_recordPropertyHistory($propertyId,'deal_price_adjusted',__('Deal Total Adjusted'),$currentTotal,$expectedTotal,array(
                'deal_id'=>$dealId,
                'client_id'=>isset($deal['client_id']) ? (int)$deal['client_id'] : null,
                'amount_delta'=>$delta,
                'meta'=>array('property_price'=>$newPropertyPrice,'discount'=>$discount,'charge_total'=>$chargeTotal,'source'=>'deal_sync')
            ));
            $this->_recordOwnerDealPriceAdjustment($propertyId,$deal,$currentTotal,$expectedTotal,$delta,array(
                'property_price'=>$newPropertyPrice,
                'discount'=>$discount,
                'charge_total'=>$chargeTotal,
                'source'=>'deal_sync'
            ));
        }
    }
    protected function _hasUploadedPropertyPhotoFile($file)
    {
        return is_array($file) && !empty($file['name']) && !empty($file['tmp_name']);
    }
    protected function _normalizePropertyPhotoFiles($files)
    {
        $normalized = array();
        if(!is_array($files))
        {
            return $normalized;
        }
        if(isset($files['tmp_name']) && is_array($files['tmp_name']))
        {
            foreach($files['tmp_name'] as $idx=>$tmpName)
            {
                $file = array(
                    'name'=>isset($files['name'][$idx]) ? $files['name'][$idx] : '',
                    'type'=>isset($files['type'][$idx]) ? $files['type'][$idx] : '',
                    'tmp_name'=>$tmpName,
                    'error'=>isset($files['error'][$idx]) ? $files['error'][$idx] : 0,
                    'size'=>isset($files['size'][$idx]) ? $files['size'][$idx] : 0
                );
                if($this->_hasUploadedPropertyPhotoFile($file))
                {
                    $normalized[] = $file;
                }
            }
            return $normalized;
        }
        if($this->_hasUploadedPropertyPhotoFile($files))
        {
            $normalized[] = $files;
            return $normalized;
        }
        foreach($files as $file)
        {
            if($this->_hasUploadedPropertyPhotoFile($file))
            {
                $normalized[] = $file;
            }
        }
        return $normalized;
    }
    protected function _setPropertyFrontPhoto($propertyId,$photoId)
    {
        $propertyId = (int)$propertyId;
        $photoId = (int)$photoId;
        if($propertyId<=0 || $photoId<=0)
        {
            return false;
        }
        $this->loadModel('PropertiesPhoto');
        $photoRow = $this->PropertiesPhoto->find('first',array(
            'conditions'=>array('PropertiesPhoto.id'=>$photoId,'PropertiesPhoto.property_id'=>$propertyId),
            'recursive'=>-1
        ));
        if(empty($photoRow['PropertiesPhoto']['id']))
        {
            return false;
        }
        $this->PropertiesPhoto->updateAll(array('PropertiesPhoto.home'=>"'No'"),array('PropertiesPhoto.property_id'=>$propertyId));
        $this->PropertiesPhoto->id = $photoId;
        return (bool)$this->PropertiesPhoto->saveField('home','Yes',array('validate'=>false));
    }
    protected function _propertyHasFrontPhoto($propertyId)
    {
        $this->loadModel('PropertiesPhoto');
        $count = $this->PropertiesPhoto->find('count',array(
            'conditions'=>array('PropertiesPhoto.property_id'=>(int)$propertyId,'PropertiesPhoto.home'=>'Yes'),
            'recursive'=>-1
        ));
        return $count>0;
    }
    protected function _recordPropertyPriceHistory($propertyId,$oldPrice,$newPrice,$source='manual',$note='')
    {
        $propertyId = (int)$propertyId;
        if($propertyId<=0)
        {
            return false;
        }
        try
        {
            $this->loadModel('PropertyPriceHistory');
            $this->PropertyPriceHistory->create();
            return (bool)$this->PropertyPriceHistory->save(array('PropertyPriceHistory'=>array(
                'property_id'=>$propertyId,
                'old_price'=>($oldPrice===null ? null : round((float)$oldPrice,2)),
                'new_price'=>round((float)$newPrice,2),
                'changed_by'=>(int)$this->luserId,
                'source'=>$source,
                'note'=>$note,
                'created'=>date('Y-m-d H:i:s')
            )),false);
        }
        catch(Exception $e)
        {
            return false;
        }
    }
    protected function _propertyQualityScoreForIndex($propertyId,$propertyRow=array())
    {
        $propertyId = (int)$propertyId;
        if($propertyId<=0)
        {
            return array('score'=>0,'missing'=>array(__('Invalid property')));
        }
        $property = !empty($propertyRow['Property']) ? $propertyRow : $this->Property->find('first',array('conditions'=>array('Property.id'=>$propertyId),'recursive'=>-1));
        $this->loadModel('PropertiesPhoto');
        $this->loadModel('PropertiesDocument');
        $this->loadModel('PropertiesFloorplan');
        $this->loadModel('PropertiesFeature');
        $this->loadModel('PropertySeo');
        $frontPhotoCount = (int)$this->PropertiesPhoto->find('count',array('conditions'=>array('PropertiesPhoto.property_id'=>$propertyId,'PropertiesPhoto.home'=>'Yes'),'recursive'=>-1));
        $featureCount = (int)$this->PropertiesFeature->find('count',array('conditions'=>array('PropertiesFeature.property_id'=>$propertyId),'recursive'=>-1));
        $documentConditions = array('PropertiesDocument.property_id'=>$propertyId);
        if($this->PropertiesDocument->hasField('public_visible'))
        {
            $documentConditions['PropertiesDocument.public_visible'] = 1;
        }
        $documentCount = (int)$this->PropertiesDocument->find('count',array('conditions'=>$documentConditions,'recursive'=>-1));
        $floorplanCount = (int)$this->PropertiesFloorplan->find('count',array('conditions'=>array('PropertiesFloorplan.property_id'=>$propertyId),'recursive'=>-1));
        $seo = $this->PropertySeo->find('first',array('conditions'=>array('PropertySeo.property_id'=>$propertyId),'recursive'=>-1));
        $lat = trim((string)(isset($property['Property']['latitude']) ? $property['Property']['latitude'] : ''));
        $lng = trim((string)(isset($property['Property']['longitude']) ? $property['Property']['longitude'] : ''));
        $checks = array(
            __('Front photo') => $frontPhotoCount>0,
            __('Price') => !empty($property['Property']['price']) && (float)$property['Property']['price']>0,
            __('Description') => strlen(trim(strip_tags((string)(isset($property['Property']['description']) ? $property['Property']['description'] : ''))))>=40,
            __('Map') => ($lat!=='' && $lng!=='' && $lat!=='0' && $lng!=='0'),
            __('Type') => !empty($property['Property']['type_id']),
            __('Contract') => !empty($property['Property']['contract_id']),
            __('Location') => !empty($property['Property']['location_id']),
            __('Agent') => !empty($property['Property']['user_id']),
            __('Amenities') => $featureCount>0,
            __('Public documents') => $documentCount>0,
            __('Floor plan') => $floorplanCount>0,
            __('SEO') => (!empty($seo['PropertySeo']['meta_title']) && !empty($seo['PropertySeo']['meta_description']))
        );
        $done = 0;
        $missing = array();
        foreach($checks as $label=>$passed)
        {
            if($passed)
            {
                $done++;
            }
            else
            {
                $missing[] = $label;
            }
        }
        return array('score'=>(int)round(($done/count($checks))*100),'missing'=>$missing);
    }
    protected function _savePropertyPhotoUploads($propertyId,$files,$makeFirstFront=false)
    {
        $propertyId = (int)$propertyId;
        if($propertyId<=0)
        {
            return array();
        }
        $this->loadModel('PropertiesPhoto');
        $savedRows = array();
        $dirName = 'propertiesphotos';
        $files = $this->_normalizePropertyPhotoFiles($files);
        foreach($files as $file)
        {
            $fileName = $this->CustomFunction->Upload($file['tmp_name'],$file['name'],$dirName,200,200);
            if(strlen($fileName)>0)
            {
                $savedRows[] = array(
                    'property_id'=>$propertyId,
                    'photo'=>$fileName,
                    'dir'=>$dirName,
                    'home'=>($makeFirstFront && empty($savedRows)) ? 'Yes' : 'No'
                );
            }
        }
        if(!empty($savedRows))
        {
            if($makeFirstFront)
            {
                $this->PropertiesPhoto->updateAll(array('PropertiesPhoto.home'=>"'No'"),array('PropertiesPhoto.property_id'=>$propertyId));
            }
            $this->PropertiesPhoto->create();
            $this->PropertiesPhoto->saveAll($savedRows,array('validate'=>false));
            foreach($savedRows as $savedRow)
            {
                $this->_recordUploadInMediaGallery('property_photo', $propertyId, $savedRow['photo'], $savedRow['dir'], array(
                    'title' => sprintf(__('Property Photo - #%s'), $propertyId)
                ));
            }
        }
        return $savedRows;
    }
    public function index($id=null)
    {
	$this->loadModel('Contract');
	$this->loadModel('Location');
	$this->loadModel('Type');
	$condition=array();
	if(isset($this->params['named']['type']) && strlen($this->params['named']['type'])>0)
	{
	    $condition[]=array('Property.type_id'=>$this->params['named']['type']);
	}
	if(isset($this->params['named']['contract']) && strlen($this->params['named']['contract'])>0)
	{
	    $condition[]=array('Property.contract_id'=>$this->params['named']['contract']);
	}
	if(isset($this->params['named']['location']) && strlen($this->params['named']['location'])>0)
	{
	    $condition[]=array('OR'=>array(array('Location.'.'name'.' LIKE'=>"%".$this->params['named']['location']."%"),
					   array('Property.'.'address'.' LIKE'=>"%".$this->params['named']['location']."%"),
					   ));
	}
	if(isset($this->params['named']['general']) && strlen($this->params['named']['general'])>0)
	{
	    $condition[]=array('OR'=>array(array('Property.'.'address'.' LIKE'=>"%".$this->params['named']['general']."%"),
					   array('Property.'.'name'.' LIKE'=>"%".$this->params['named']['general']."%"),
					   array('Property.uniq_id'=>$this->params['named']['general']),
					   array('Property.'.'description'.' LIKE'=>"%".$this->params['named']['general']."%")));
	}
	if(isset($this->params['named']['start_price']) && strlen($this->params['named']['start_price'])>0 && isset($this->params['named']['end_price']) && strlen($this->params['named']['end_price'])>0)
	{
	    $startDate=$this->params['named']['start_price'];
	    $endDate=$this->params['named']['end_price'];
	    $condition[]=array('Property.price BETWEEN ? AND ?'=>array($startDate,$endDate));
	}
	if(isset($this->params['named']['publish']) && strlen($this->params['named']['publish'])>0)
	{
	    $condition[]=array('published'=>$this->params['named']['publish']);
	}
	if(isset($this->params['named']['bedroom']) && strlen($this->params['named']['bedroom'])>0)
	{
	    $condition[]=array('bedroom >='=>$this->params['named']['bedroom']);
	}
	if(isset($this->params['named']['area']) && strlen($this->params['named']['area'])>0)
	{
	    $condition[]=array('area >='=>$this->params['named']['area']);
	}
	if(isset($this->params['named']['bathroom']) && strlen($this->params['named']['bathroom'])>0)
	{
	    $condition[]=array('bathroom >='=>$this->params['named']['bathroom']);
	}
	if(isset($this->params['named']['uniq_id']) && strlen($this->params['named']['uniq_id'])>0)
	{
	    $condition[]=array('uniq_id'=>$this->params['named']['uniq_id']);
	}
	if(isset($this->params['named']['agent']) && strlen($this->params['named']['agent'])>0)
	{
	    $condition[]=array('user_id'=>$this->params['named']['agent']);
	}
	if(isset($this->params['named']['agency']) && strlen($this->params['named']['agency'])>0)
	{
	    $condition[]=array('Property.project_id'=>$this->params['named']['agency']);
	}
	$this->set('contract',$this->Contract->find('list'));
	$this->set('location',$this->Location->find('list'));
	$this->set('type',$this->Type->find('list'));
        $condition[]=$this->CustomFunction->userWisePermission($id,$this->luserId,$this->userType,$this->agencyId,'Property.project_id','Property.user_id');
	$this->Prg->commonProcess();
        $this->Paginator->settings = $this->paginate;
        $this->Paginator->settings['conditions'] = array($this->Property->parseCriteria($this->Prg->parsedParams()),$condition);
        $propertyPageLimit = $this->_propertyPaginatorLimit();
        $this->Paginator->settings['limit']=$propertyPageLimit;
        $this->Paginator->settings['maxLimit']=max((int)$this->maxLimit,$propertyPageLimit,500);
        $propertyRows = $this->Paginator->paginate();
        $propertyQualityScores = array();
        foreach((array)$propertyRows as $propertyRow)
        {
            if(!empty($propertyRow['Property']['id']))
            {
                $propertyQualityScores[(int)$propertyRow['Property']['id']] = $this->_propertyQualityScoreForIndex((int)$propertyRow['Property']['id'],$propertyRow);
            }
        }
        $this->set('Property', $propertyRows);
        $this->set('propertyQualityScores', $propertyQualityScores);
	$this->set('luserId',$this->luserId);
	if ($this->request->is('ajax'))
        {
            $this->render('index','ajax'); // View, Layout
        }	
    }
    protected function _propertyPaginatorLimit()
    {
        $limit = 0;
        if(isset($this->request->query['limit']))
        {
            $limit = (int)$this->request->query['limit'];
        }
        elseif(isset($this->request->params['named']['limit']))
        {
            $limit = (int)$this->request->params['named']['limit'];
        }
        elseif(isset($this->passedArgs['limit']))
        {
            $limit = (int)$this->passedArgs['limit'];
        }
        elseif(isset($this->request->data['limit']))
        {
            $limit = (int)$this->request->data['limit'];
        }
        elseif(isset($this->request->data['Property']['limit']))
        {
            $limit = (int)$this->request->data['Property']['limit'];
        }
        if($limit<=0)
        {
            $limit = (int)$this->pageLimit;
        }
        $limit = max(1,min($limit,500));
        $this->request->params['named']['limit'] = $limit;
        $this->passedArgs['limit'] = $limit;
        return $limit;
    }
    public function add()
    {
        $this->_setAddressHierarchyVars();
        $this->loadModel('Feature');
        $this->set('feature',$this->Feature->find('list'));
        $this->loadModel('Type');
        $this->set('type',$this->Type->find('list'));
        $this->loadModel('Contract');
        $this->set('contract',$this->Contract->find('list'));
        $this->loadModel('Project');
        $this->set('agency',$this->CustomFunction->agencyWiseList($this->userType,$this->agencyId));
        $this->loadModel('Location');
        $this->set('location',$this->Location->find('list'));
        $this->loadModel('Unit');
        $this->set('unitName',$this->Unit->find('list'));
	$projectId=null;
        if ($this->request->is('post'))
        {
            $this->Property->create();
            try
            {
                $gId=$this->CustomFunction->generate_rand();
                $this->loadModel('PropertiesPhoto');
                $this->loadModel('PropertiesDocument');
                $this->loadModel('PropertiesFloorplan');
                $this->loadModel('PropertiesFeature');
                $this->request->data['Property']['uniq_id']=$this->siteShort.$gId;
		if(strtoupper(trim((string)$this->userType))!='ADR' && (int)$this->agencyId>0){
		    $this->request->data['Property']['project_id']=$this->agencyId;		    
		}
                $this->request->data['Property']['user_id']=$this->luserId;
                $this->_normalizePropertyAddressHierarchy($this->request->data['Property']);
                $this->_normalizePropertyNearbyPlaces($this->request->data['Property']);
                                // Handle payment plan PDF upload
                $pdfRaw = isset($_FILES['payment_plan_pdf_file']) ? $_FILES['payment_plan_pdf_file'] : null;
                $savedPdf = $this->_handlePaymentPlanPdfUpload($pdfRaw);
                if (!empty($savedPdf)) {
                    $this->request->data['Property']['payment_plan_pdf'] = $savedPdf;
                }
                if (!empty($this->request->data['Property']['assigned_agent_id'])) {
                    $this->request->data['Property']['user_id'] = (int)$this->request->data['Property']['assigned_agent_id'];
                }
                if ($this->Property->save($this->request->data))
                {
                    $savedPropertyId = (int)$this->Property->id;
                    $this->_savePropertySurroundings($savedPropertyId, isset($this->request->data['Property']['nearby_places_json']) ? $this->request->data['Property']['nearby_places_json'] : null);
                    $mainPhotoFiles = isset($this->request->data['Pr']['MainPhoto']) ? $this->request->data['Pr']['MainPhoto'] : array();
                    $galleryPhotoFiles = isset($this->request->data['Pr']['PropertiesPhoto']) ? $this->request->data['Pr']['PropertiesPhoto'] : array();
                    $mainPhotoRows = $this->_savePropertyPhotoUploads($this->Property->id,$mainPhotoFiles,true);
                    $galleryFrontFallback = empty($mainPhotoRows);
                    $this->_savePropertyPhotoUploads($this->Property->id,$galleryPhotoFiles,$galleryFrontFallback);
                    $prDocument=array();
                    $dirName="propertiesdocuments";
                    foreach($this->request->data['Pr']['PropertiesDocument'] as $propertyPhoto)
                    {
                        $fileName=$this->CustomFunction->Upload($propertyPhoto['tmp_name'],$propertyPhoto['name'],$dirName);
                        if(strlen($fileName)>0)
                        {
                            $prDocument[]=(array('property_id'=>$this->Property->id,'photo' => $fileName,'dir'=>$dirName));
                            $this->_recordUploadInMediaGallery('property_document', $this->Property->id, $fileName, $dirName, array(
                                'original_name' => $propertyPhoto['name'],
                                'title' => sprintf(__('Property Document - #%s'), $this->Property->id)
                            ));
                        }
                    }
                    $prFloorplan=array();
                    $dirName="propertiesfloorplans";
                    foreach($this->request->data['Pr']['PropertiesFloorplan'] as $propertyPhoto)
                    {
                        $fileName=$this->CustomFunction->Upload($propertyPhoto['tmp_name'],$propertyPhoto['name'],$dirName,200,200);
                        if(strlen($fileName)>0)
                        {
                            $prFloorplan[]=(array('property_id'=>$this->Property->id,'photo' => $fileName,'dir'=>$dirName));
                            $this->_recordUploadInMediaGallery('property_floorplan', $this->Property->id, $fileName, $dirName, array(
                                'original_name' => $propertyPhoto['name'],
                                'title' => sprintf(__('Floor Plan - #%s'), $this->Property->id)
                            ));
                        }
                    }
                    if (!empty($savedPdf)) {
                        $this->_recordUploadInMediaGallery('payment_plan_pdf', $this->Property->id, $savedPdf, 'files/payment_plans', array(
                            'title' => sprintf(__('Payment Plan PDF - #%s'), $this->Property->id)
                        ));
                    }
                    $prFeature=array();
                    foreach($this->request->data['PropertiesFeature']['feature'] as $featureId)
                    {
			if($featureId!="multiselect-all"){
			    $prFeature[]=(array('property_id'=>$this->Property->id,'feature_id'=>$featureId));
			}
                    }                    
                    $this->PropertiesDocument->saveAll($prDocument);                    
                    $this->PropertiesFloorplan->saveAll($prFloorplan);                    
                    $this->PropertiesFeature->saveAll($prFeature);                    
                    $this->_recordPropertyHistory($savedPropertyId,'created',__('Property Created'),'',isset($this->request->data['Property']['name']) ? $this->request->data['Property']['name'] : '',array(
                        'project_id'=>isset($this->request->data['Property']['project_id']) ? (int)$this->request->data['Property']['project_id'] : null,
                        'meta'=>array('price'=>isset($this->request->data['Property']['price']) ? $this->request->data['Property']['price'] : '')
                    ));
                    if(isset($this->request->data['Property']['price']))
                    {
                        $this->_recordPropertyPriceHistory($savedPropertyId,null,$this->request->data['Property']['price'],'created',__('Initial property price'));
                    }
                    $this->Session->setFlash(__('Property has been saved.'),'flash',array('alert'=>'success'));
                    return $this->redirect(array('controller'=>'Properties','action' => 'index'));
                }
            }
            catch (Exception $e)
            {
                $this->Session->setFlash($e,'flash',array('alert'=>'danger'));
                return $this->redirect(array('action' => 'index'));
            }
	    $projectId=$this->request->data['Property']['project_id'];
        }
	$this->loadModel('User');
        $this->set('agentName',$this->User->find('list',array('conditions'=>array($this->User->assignableScopeConditions((int)$projectId,'User')),'order'=>array('User.name'=>'asc'))));
    }
    public function edit($id = null)
    {
        if (!$id)
        {
            throw new NotFoundException(__('Invalid post'));
        }
        $ids=explode(",",$id);
        $post=array();
        $this->loadModel('User');
	foreach($ids as $k=>$id)
	{
	    $k++;
	    $post[$k]=$this->Property->findByid($id);
	    if(!empty($post[$k]['Property']['id']))
	    {
		$post[$k]['Property']['nearby_places_json'] = $this->_propertySurroundingsJson((int)$post[$k]['Property']['id'], !empty($post[$k]['Property']['nearby_places_json']) ? $post[$k]['Property']['nearby_places_json'] : '');
	    }
	    if(!$this->_canAccessPropertyRecord($post[$k])) {
		$this->Session->setFlash(__('Invalid Post'),'flash',array('alert'=>'danger'));
		return $this->redirect(array('action' => 'index'));
	    }
	}
        $this->set('Property',$post);
        if (!$post)
        {
            throw new NotFoundException(__('Invalid post'));
        }
	$propertiesFeature=null;
        $this->loadModel('Feature');
        $this->set('feature',$this->Feature->find('list'));
        $this->loadModel('Type');
        $this->set('type',$this->Type->find('list'));
        $this->loadModel('Contract');
        $this->set('contract',$this->Contract->find('list'));
        $this->loadModel('Project');
        $this->set('agency',$this->CustomFunction->agencyWiseList($this->userType,$this->agencyId));
        $this->loadModel('Location');
        $this->set('location',$this->Location->find('list'));
        $this->loadModel('Unit');
        $this->set('unitName',$this->Unit->find('list'));
        $this->loadModel('Feature');
        $this->set('featureId', $this->Feature->find('list',array('order'=>array('Feature.name'=>'asc'))));
        $this->loadModel('User');
        $this->set('agentName', $this->User->find('list', array('conditions' => array('User.status' => 'Active'), 'order' => array('User.name' => 'asc'))));
        $this->_setAddressHierarchyVars();
        if ($this->request->is(array('post', 'put')))
        {
	    if(isset($this->request->data['multiselect']))
	    {
		unset($this->request->data['multiselect']);
	    }
            $photoRequestData = $this->request->data;
            $saveData = array();
            foreach((array)$this->request->data as $rowKey=>$rowData)
            {
                if(empty($rowData['Property']) || !is_array($rowData['Property']) || empty($rowData['Property']['id']))
                {
                    continue;
                }
                $saveData[$rowKey] = $rowData;
                if(isset($saveData[$rowKey]['Property']) && is_array($saveData[$rowKey]['Property']))
                {
                    $this->_normalizePropertyAddressHierarchy($saveData[$rowKey]['Property']);
                    $this->_normalizePropertyNearbyPlaces($saveData[$rowKey]['Property']);
                }
                if(isset($saveData[$rowKey]['Pr']))
                {
                    unset($saveData[$rowKey]['Pr']);
                }
                if(isset($saveData[$rowKey]['PropertiesPhoto']))
                {
                    unset($saveData[$rowKey]['PropertiesPhoto']);
                }
            }
	    if(empty($saveData))
	    {
		$this->Session->setFlash(__('No valid property data was submitted.'),'flash',array('alert'=>'danger'));
		return $this->redirect(array('action'=>'index'));
	    }
	    try
            {
                $oldPropertyMap = array();
                foreach($post as $oldRow)
                {
                    if(!empty($oldRow['Property']['id']))
                    {
                        $oldPropertyMap[(int)$oldRow['Property']['id']] = $oldRow['Property'];
                    }
                }
                $this->_ensurePropertyHistoryTable();
                $isFeatureSave=false;
                $db = $this->Property->getDataSource();
                $transactionActive = false;
                try
                {
                    $db->begin();
                    $transactionActive = true;
                }
                catch (Exception $be)
                {
                    $transactionActive = false;
                }
                // Handle payment plan PDF upload in edit
                $pdfRaw = isset($_FILES['payment_plan_pdf_file']) ? $_FILES['payment_plan_pdf_file'] : null;
                foreach ($saveData as $k => $item) {
                    $propId = !empty($item['Property']['id']) ? (int)$item['Property']['id'] : 0;
                    $existingPdf = isset($oldPropertyMap[$propId]['payment_plan_pdf']) ? $oldPropertyMap[$propId]['payment_plan_pdf'] : null;
                    $removePdf = !empty($item['Property']['remove_payment_plan_pdf']);
                    $finalPdf = $this->_handlePaymentPlanPdfUpload($pdfRaw, $existingPdf, $removePdf);
                    if ($finalPdf !== null) {
                        $saveData[$k]['Property']['payment_plan_pdf'] = $finalPdf;
                    }
                    if (!empty($item['Property']['assigned_agent_id'])) {
                        $saveData[$k]['Property']['user_id'] = (int)$item['Property']['assigned_agent_id'];
                    }
                    if (!$this->_isSuperAdminSession() && (int)$this->agencyId > 0) {
                        $saveData[$k]['Property']['project_id'] = (int)$this->agencyId;
                    }
                }
                if ($this->Property->saveAll($saveData, array('atomic' => false)))
                {
                    $this->loadModel('PropertiesFeature');
                    foreach($saveData as $k=> $value)
                    {
			$propertyId=$value['Property']['id'];
			$this->_savePropertySurroundings($propertyId, isset($value['Property']['nearby_places_json']) ? $value['Property']['nearby_places_json'] : null);
			$oldProperty = isset($oldPropertyMap[(int)$propertyId]) ? $oldPropertyMap[(int)$propertyId] : array();
			$oldName = isset($oldProperty['name']) ? trim((string)$oldProperty['name']) : '';
			$newName = isset($value['Property']['name']) ? trim((string)$value['Property']['name']) : $oldName;
			if($newName !== $oldName)
			{
			    $this->_recordPropertyHistory($propertyId,'title_changed',__('Property Title Changed'),$oldName,$newName,array(
				'project_id'=>isset($value['Property']['project_id']) ? (int)$value['Property']['project_id'] : (isset($oldProperty['project_id']) ? (int)$oldProperty['project_id'] : null)
			    ));
			}
			$oldPrice = isset($oldProperty['price']) ? round((float)$oldProperty['price'],2) : 0;
			$newPrice = isset($value['Property']['price']) ? round((float)$value['Property']['price'],2) : $oldPrice;
			if(abs($newPrice-$oldPrice)>=0.01)
			{
			    $this->_recordPropertyHistory($propertyId,'price_changed',__('Property Price Changed'),$oldPrice,$newPrice,array(
				'project_id'=>isset($value['Property']['project_id']) ? (int)$value['Property']['project_id'] : (isset($oldProperty['project_id']) ? (int)$oldProperty['project_id'] : null),
				'amount_delta'=>round($newPrice-$oldPrice,2)
			    ));
			    $this->_recordPropertyPriceHistory($propertyId,$oldPrice,$newPrice,'edit',__('Property edit form price change'));
			    $this->_adjustActiveDealsForPropertyPriceChange($propertyId,$oldPrice,$newPrice);
			}
			$selectedFeatures = !empty($value['PropertyFeature']['feature']) && is_array($value['PropertyFeature']['feature']) ? $value['PropertyFeature']['feature'] : array();
			if(!empty($selectedFeatures))
			{
			    $isFeatureSave=true;
			    $this->PropertiesFeature->deleteAll(array('PropertiesFeature.property_id'=>$propertyId));
			    foreach($selectedFeatures as $featureId)
			    {
				if(is_numeric($featureId))
				$propertiesFeature[]=array('property_id'=>$propertyId,'feature_id'=>$featureId);
			    }
			}
			else
			{
			    $this->PropertiesFeature->deleteAll(array('PropertiesFeature.property_id'=>$propertyId));
			}
                        $selectedFrontPhotoId = !empty($photoRequestData[$k]['PropertiesPhoto']['front_photo_id']) ? (int)$photoRequestData[$k]['PropertiesPhoto']['front_photo_id'] : 0;
                        $newMainPhoto = isset($photoRequestData[$k]['Pr']['MainPhoto']) ? $photoRequestData[$k]['Pr']['MainPhoto'] : array();
                        $newGalleryPhotos = isset($photoRequestData[$k]['Pr']['PropertiesPhoto']) ? $photoRequestData[$k]['Pr']['PropertiesPhoto'] : array();
                        $mainPhotoRows = $this->_savePropertyPhotoUploads($propertyId,$newMainPhoto,true);
                        if(empty($mainPhotoRows) && $selectedFrontPhotoId>0)
                        {
                            $this->_setPropertyFrontPhoto($propertyId,$selectedFrontPhotoId);
                        }
                        $galleryFrontFallback = empty($mainPhotoRows) && $selectedFrontPhotoId<=0 && !$this->_propertyHasFrontPhoto($propertyId);
                        $this->_savePropertyPhotoUploads($propertyId,$newGalleryPhotos,$galleryFrontFallback);
                    }
		    if($isFeatureSave)
		    {
			$this->PropertiesFeature->create();
			$this->PropertiesFeature->saveAll($propertiesFeature, array('atomic' => false));
		    }
                    if($transactionActive && isset($db))
                    {
                        try
                        {
                            $db->commit();
                        }
                        catch (Exception $ce) {}
                        $transactionActive = false;
                    }
                    $this->Session->setFlash(__('Property has been updated.'),'flash',array('alert'=>'success'));
                    $this->set('propertyIndexUrl',rtrim((string)$this->request->webroot,'/').'/admin/Properties');
                    $this->layout = false;
                    return $this->render('edit_success');
                }
                $validationMessages = array();
                foreach((array)$this->Property->validationErrors as $fieldErrors)
                {
                    foreach((array)$fieldErrors as $fieldError)
                    {
                        if(trim((string)$fieldError)!=='') $validationMessages[] = trim((string)$fieldError);
                    }
                }
                throw new Exception(!empty($validationMessages) ? implode(' ',array_unique($validationMessages)) : __('Property could not be updated. Please check the entered values.'));
            }
            catch (Exception $e)
            {
                if(!empty($transactionActive) && isset($db))
                {
                    try
                    {
                        $db->rollback();
                    }
                    catch (Exception $re) {}
                    $transactionActive = false;
                }
                $this->Session->setFlash($e->getMessage(),'flash',array('alert'=>'danger'));
                return $this->redirect(array('action' => 'index'));
            }
            $this->set('isError',true);
        }
        else
        {
            $this->set('isError',false);
        }
        if (!$this->request->data)
        {
            $this->request->data = $post;
        }
    }
    public function savesurroundings($id = null)
    {
        $this->autoRender = false;
        $this->layout = null;
        $id = (int)$id;
        header('Content-Type: application/json');
        if(!$this->request->is('post') || $id<=0)
        {
            echo json_encode(array('success'=>false,'message'=>__('Invalid request.')));
            return;
        }
        $post = $this->Property->findById($id);
        if(!$post || !$this->_canAccessPropertyRecord($post))
        {
            echo json_encode(array('success'=>false,'message'=>__('Invalid Post')));
            return;
        }
        $propertyData = array('nearby_places_json'=>isset($this->request->data['nearby_places_json']) ? $this->request->data['nearby_places_json'] : '');
        $this->_normalizePropertyNearbyPlaces($propertyData);
        try
        {
            $this->_savePropertySurroundings($id, isset($propertyData['nearby_places_json']) ? $propertyData['nearby_places_json'] : null);
            echo json_encode(array(
                'success'=>true,
                'message'=>__('Saved'),
                'nearby_places_json'=>$this->_propertySurroundingsJson($id, '')
            ));
        }
        catch(Exception $e)
        {
            echo json_encode(array('success'=>false,'message'=>$e->getMessage()));
        }
        return;
    }
    public function deleteall()
    {
        try
        {
            if ($this->request->is('post'))
            {
                foreach($this->data['Property']['id'] as $key => $value)
                {
		    if($value > 0) {
			$prop[$key]=$this->Property->findByid($value);
			if(!$this->_canAccessPropertyRecord($prop[$key])) {
			    $this->Session->setFlash(__('Invalid Post'),'flash',array('alert'=>'danger'));
			    return $this->redirect(array('action' => 'index'));
			}
			$this->Property->delete($value);
		    }
                }
		unset($prop);
                $this->Session->setFlash(__('Property has been deleted'),'flash',array('alert'=>'success'));
            }        
            $this->redirect(array('action' => 'index'));
        }
        catch (Exception $e)
        {
            $this->Session->setFlash(__('Please Delete Deal First.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'index'));
        }
    }
    public function bulkupdate()
    {
        if(!$this->request->is('post'))
        {
            return $this->redirect(array('action'=>'index'));
        }
        $ids = !empty($this->request->data['Property']['id']) ? (array)$this->request->data['Property']['id'] : array();
        $bulkAction = !empty($this->request->data['Property']['bulk_action']) ? trim((string)$this->request->data['Property']['bulk_action']) : '';
        $ids = array_values(array_unique(array_filter(array_map('intval',$ids))));
        if(empty($ids) || $bulkAction==='')
        {
            $this->Session->setFlash(__('Please select properties and a bulk action.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action'=>'index'));
        }
        $allowed = array(
            'publish_yes'=>array('published'=>'Yes'),
            'publish_no'=>array('published'=>'No'),
            'featured_yes'=>array('featured'=>'Yes'),
            'featured_no'=>array('featured'=>'No'),
            'status_available'=>array('status'=>'Available'),
            'status_sold'=>array('status'=>'Sold')
        );
        if(empty($allowed[$bulkAction]))
        {
            $this->Session->setFlash(__('Invalid bulk action.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action'=>'index'));
        }
        $updated = 0;
        foreach($ids as $propertyId)
        {
            $post = $this->Property->findById($propertyId);
            if(!$post || !$this->_canAccessPropertyRecord($post))
            {
                continue;
            }
            $save = array_merge(array('id'=>$propertyId,'bathroom'=>$post['Property']['bathroom']),$allowed[$bulkAction]);
            $this->Property->unbindValidation('keep', array('bathroom'), true);
            if($this->Property->save(array('Property'=>$save),false))
            {
                $updated++;
            }
        }
        $this->Session->setFlash(sprintf(__('%s properties updated.'),(int)$updated),'flash',array('alert'=>'success'));
        return $this->redirect(array('action'=>'index'));
    }
    public function duplicate($id=null)
    {
        $id = (int)$id;
        if($id<=0)
        {
            $this->Session->setFlash(__('Invalid Post.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action'=>'index'));
        }
        $post = $this->Property->find('first',array('conditions'=>array('Property.id'=>$id),'recursive'=>1));
        if(!$post || !$this->_canAccessPropertyRecord($post))
        {
            $this->Session->setFlash(__('Invalid Post.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action'=>'index'));
        }
        try
        {
            $data = $post['Property'];
            unset($data['id'],$data['created'],$data['modified']);
            $data['uniq_id'] = $this->siteShort.$this->CustomFunction->generate_rand();
            $data['name'] = trim((string)$data['name']).' Copy';
            $data['published'] = 'No';
            $data['featured'] = 'No';
            $data['views'] = 0;
            $this->Property->create();
            if(!$this->Property->save(array('Property'=>$data),false))
            {
                throw new Exception(__('Property could not be duplicated.'));
            }
            $newId = (int)$this->Property->id;
            $this->loadModel('PropertiesFeature');
            $features = $this->PropertiesFeature->find('all',array('conditions'=>array('PropertiesFeature.property_id'=>$id),'recursive'=>-1));
            $featureRows = array();
            foreach((array)$features as $feature)
            {
                if(!empty($feature['PropertiesFeature']['feature_id']))
                {
                    $featureRows[] = array('property_id'=>$newId,'feature_id'=>(int)$feature['PropertiesFeature']['feature_id']);
                }
            }
            if(!empty($featureRows))
            {
                $this->PropertiesFeature->create();
                $this->PropertiesFeature->saveAll($featureRows,array('validate'=>false));
            }
            $this->_recordPropertyHistory($newId,'created',__('Property Duplicated'),'',$data['name'],array(
                'project_id'=>isset($data['project_id']) ? (int)$data['project_id'] : null,
                'meta'=>array('source_property_id'=>$id)
            ));
            if(isset($data['price']))
            {
                $this->_recordPropertyPriceHistory($newId,null,$data['price'],'duplicate',__('Duplicated from property #'.$id));
            }
            $this->Session->setFlash(__('Property duplicated as unpublished. Please review details and add media if needed.'),'flash',array('alert'=>'success'));
            return $this->redirect(array('action'=>'edit',$newId));
        }
        catch(Exception $e)
        {
            $this->Session->setFlash($e->getMessage(),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action'=>'index'));
        }
    }
    public function view($id = null)
    {
        $this->layout = null;
        if (!$id)
        {
            $this->Session->setFlash(__('Invalid Post.'),'flash',array('alert'=>'danger'));
            $this->redirect(array('action' => 'index'));
        }
        $post = $this->Property->findById($id);
        if(!$this->_canAccessPropertyRecord($post))
        {
            $this->Session->setFlash(__('Invalid Post.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        $this->set('post',$post);
    }
    public function history($id = null)
    {
        if (!$id)
        {
            $this->Session->setFlash(__('Invalid Post.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        $post = $this->Property->findById($id);
        if(!$post || !$this->_canAccessPropertyRecord($post))
        {
            $this->Session->setFlash(__('Invalid Post.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        $this->_ensurePropertyHistoryTable();
        $this->loadModel('Deal');
        $this->loadModel('Client');
        $historyRows = (array)$this->PropertyHistory->find('all',array(
            'conditions'=>array('PropertyHistory.property_id'=>(int)$id),
            'order'=>array('PropertyHistory.created'=>'asc','PropertyHistory.id'=>'asc'),
            'recursive'=>0
        ));
        $dealFields = array('Deal.id','Deal.invoice_no','Deal.client_id','Deal.date','Deal.discount','Deal.total_amount');
        if($this->Deal->hasField('amount'))$dealFields[]='Deal.amount';
        if($this->Deal->hasField('deal_status'))$dealFields[]='Deal.deal_status';
        if($this->Deal->hasField('deal_status_changed'))$dealFields[]='Deal.deal_status_changed';
        if($this->Deal->hasField('deal_status_reason'))$dealFields[]='Deal.deal_status_reason';
        $ownerRows = (array)$this->Deal->find('all',array(
            'fields'=>array_merge($dealFields,array('Client.id','Client.name','Client.phone','Client.mobile')),
            'joins'=>array(array('table'=>'clients','alias'=>'Client','type'=>'LEFT','conditions'=>array('Client.id=Deal.client_id'))),
            'conditions'=>array('Deal.property_id'=>(int)$id),
            'order'=>array('Deal.date'=>'asc','Deal.id'=>'asc'),
            'recursive'=>-1
        ));
        $this->set('post',$post);
        $this->set('historyRows',$historyRows);
        $this->set('ownerRows',$ownerRows);
    }
    public function featured($id=null,$mode=null)
    {
        if (!$id || !$mode)
        {
            $this->Session->setFlash(__('Invalid Post.'),'flash',array('alert'=>'danger'));
            $this->redirect(array('action' => 'index'));
        }
        $post = $this->Property->findById($id);
        if (!$post || !$this->_canAccessPropertyRecord($post))
        {
            $this->Session->setFlash(__('Invalid Post.'),'flash',array('alert'=>'danger'));
            $this->redirect(array('action' => 'index'));
        }
        $this->Property->id = $id;
        try
        {
            $published="";
            if($mode=="Yes")
            $published="No";
            else
            $published="Yes";
            $userArr=array('id'=>$id,'bathroom'=>$post['Property']['bathroom'],'featured'=>$published);
            $this->Property->unbindValidation('keep', array('bathroom'), true);
            if ($this->Property->save($userArr))
            {
                $this->Session->setFlash(__("Record updated successfully"),'flash',array('alert'=>'success'));
                return $this->redirect(array('action' => 'index'));
            }
            else
            {
                $this->Session->setFlash(__('Something wrong.'),'flash',array('alert'=>'danger'));
                return $this->redirect(array('action' => 'index'));            
            }
        }
        catch (Exception $e)
        {
            $this->Session->setFlash(__('Something wrong.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'index'));            
        }
    }
    public function published($id=null,$mode=null)
    {
        if (!$id || !$mode)
        {
            $this->Session->setFlash(__('Invalid Post.'),'flash',array('alert'=>'danger'));
            $this->redirect(array('action' => 'index'));
        }
        $post = $this->Property->findById($id);
        if (!$post || !$this->_canAccessPropertyRecord($post))
        {
            $this->Session->setFlash(__('Invalid Post.'),'flash',array('alert'=>'danger'));
            $this->redirect(array('action' => 'index'));
        }
        $this->Property->id = $id;
        try
        {
            $published="";
            if($mode=="Yes")
            $published="No";
            else
            $published="Yes";
            $userArr=array('id'=>$id,'bathroom'=>$post['Property']['bathroom'],'published'=>$published);
            $this->Property->unbindValidation('keep', array('bathroom'), true);
            if ($this->Property->save($userArr))
            {
                $this->Session->setFlash(__("Record updated successfully"),'flash',array('alert'=>'success'));
                return $this->redirect(array('action' => 'index'));
            }
            else
            {
                $this->Session->setFlash(__('Something wrong.'),'flash',array('alert'=>'danger'));
                return $this->redirect(array('action' => 'index'));            
            }
        }
        catch (Exception $e)
        {
            $this->Session->setFlash(__('Something wrong.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'index'));            
        }
    }
}
