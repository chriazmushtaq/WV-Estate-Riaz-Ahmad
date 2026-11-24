<?php
App::uses('CakeNumber', 'Utility');
App::uses('Router', 'Routing');
App::uses('Security', 'Utility');
class MyDealsController extends AppController
{
    public $helpers = array('Html','Form','Session','Paginator','Js','Time','Number','NumToWord');
    protected $_portalVisibleDealStatuses = array('Active','Blocked');
    protected $_portalAgencyLogoUrlCache = array();
    protected $_portalAgencyUserInfoCache = array();
    protected $_portalLetterTypeArr = array(
        'RLT_WELCOME' => 'Welcome Letter',
        'RLT_BOOKING' => 'Booking Letter',
        'RLT_CHARGE' => 'Deal Charges Notice',
        'RLT_DEMARCATION' => 'Demarcation Letter',
        'RLT_DESIGN_MAP_APPROVAL' => 'Design / Map Approval Letter',
        'RLT_NOC' => 'NOC Letter',
        'RLT_ALLOT' => 'Allotment Letter',
        'RLT_DEMAND' => 'Payment Demand Notice',
        'RLT_RECEIPT' => 'Payment Acknowledgement',
        'RLT_POSSESSION' => 'Possession Letter',
        'RLT_TRANSFER' => 'Transfer / Mutation Letter',
        'RLT_CANCEL' => 'Cancellation Letter',
        'RLT_REFUND_CLEARANCE' => 'Refund Clearance / No Claim'
    );
    protected $_portalAutoLetterTypeArr = array(
        'RLT_WELCOME' => 1,
        'RLT_BOOKING' => 1,
        'RLT_CHARGE' => 1,
        'RLT_REFUND_CLEARANCE' => 1
    );
    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->authenticate();
        $this->userId=$this->userValue['Client']['id'];
        $this->_ensureClientRefundDealColumns();
    }
    protected function _dealVisibilityCondition($alias='Deal')
    {
        $this->loadModel('Deal');
        if(!$this->Deal->hasField('deal_status'))
        {
            return array();
        }
        return array(
            'OR'=>array(
                array($alias.'.deal_status'=>null),
                array($alias.'.deal_status'=>''),
                array($alias.'.deal_status'=>$this->_portalVisibleDealStatuses)
            )
        );
    }
    protected function _clientRefundAllowed($deal)
    {
        $deal = (array)$deal;
        return (!empty($deal['client_refund_allowed']));
    }
    protected function _ensureClientRefundDealColumns()
    {
        try
        {
            $exists = (array)$this->MyDeal->query("SHOW COLUMNS FROM `deals` LIKE 'client_refund_allowed'");
            if(empty($exists))
            {
                $this->MyDeal->query("ALTER TABLE `deals` ADD COLUMN `client_refund_allowed` tinyint(1) NOT NULL DEFAULT 0");
            }
        }
        catch(Exception $e)
        {
        }
        try
        {
            $exists = (array)$this->MyDeal->query("SHOW COLUMNS FROM `deals` LIKE 'client_refund_allowed_by'");
            if(empty($exists))
            {
                $this->MyDeal->query("ALTER TABLE `deals` ADD COLUMN `client_refund_allowed_by` int(11) NOT NULL DEFAULT 0");
            }
        }
        catch(Exception $e)
        {
        }
        try
        {
            $exists = (array)$this->MyDeal->query("SHOW COLUMNS FROM `deals` LIKE 'client_refund_allowed_at'");
            if(empty($exists))
            {
                $this->MyDeal->query("ALTER TABLE `deals` ADD COLUMN `client_refund_allowed_at` datetime DEFAULT NULL");
            }
        }
        catch(Exception $e)
        {
        }
        $this->MyDeal->cacheSources = false;
        try { $this->MyDeal->schema(true); } catch(Exception $e) {}
    }
    protected function _dealRefundRequestsTableReady()
    {
        try
        {
            $rows = (array)$this->MyDeal->query("SHOW TABLES LIKE 'deal_refund_requests'");
            return !empty($rows);
        }
        catch(Exception $e)
        {
            return false;
        }
    }
    protected function _refundRequestBlocksForDeals($dealIds)
    {
        $dealIds = array_values(array_unique(array_filter(array_map('intval', (array)$dealIds))));
        if(empty($dealIds) || !$this->_dealRefundRequestsTableReady())
        {
            return array();
        }
        try
        {
            $this->loadModel('Admin.Dealrefundrequest');
            $rows = (array)$this->Dealrefundrequest->find('all', array(
                'fields' => array('Dealrefundrequest.id','Dealrefundrequest.deal_id','Dealrefundrequest.request_no','Dealrefundrequest.status','Dealrefundrequest.converted_refund_id'),
                'conditions' => array(
                    'Dealrefundrequest.deal_id' => $dealIds,
                    'Dealrefundrequest.client_id' => (int)$this->userId
                ),
                'order' => array('Dealrefundrequest.id' => 'desc'),
                'recursive' => -1
            ));
        }
        catch(Exception $e)
        {
            return array();
        }
        $blocked = array();
        foreach($rows as $row)
        {
            $request = isset($row['Dealrefundrequest']) ? (array)$row['Dealrefundrequest'] : array();
            $dealId = !empty($request['deal_id']) ? (int)$request['deal_id'] : 0;
            if($dealId > 0 && !isset($blocked[$dealId]))
            {
                $blocked[$dealId] = $request;
            }
        }
        return $blocked;
    }
    protected function _refundRequestBlockForDeal($dealId)
    {
        $blocked = $this->_refundRequestBlocksForDeals(array($dealId));
        return isset($blocked[(int)$dealId]) ? $blocked[(int)$dealId] : array();
    }
    public function index()
    {
        $postArr=$this->MyDeal->find('all',array('conditions'=>array('MyDeal.client_id'=>$this->userId,$this->_dealVisibilityCondition('MyDeal')),
                                                 'order'=>array('MyDeal.id'=>'desc')));
        $dealPaymentSummary = array();
        $dealIds = array();
        foreach((array)$postArr as $post)
        {
            if(!empty($post['MyDeal']['id']))
            {
                $dealIds[] = (int)$post['MyDeal']['id'];
            }
        }
        if(!empty($dealIds))
        {
            $this->loadModel('MyPayment');
            $paymentRows = $this->MyPayment->find('all',array(
                'fields'=>array('MyPayment.deal_id','SUM(MyPayment.amount) AS paid_amount','COUNT(MyPayment.id) AS receipt_count'),
                'conditions'=>array('MyPayment.deal_id'=>$dealIds),
                'group'=>array('MyPayment.deal_id'),
                'recursive'=>-1
            ));
            foreach((array)$paymentRows as $paymentRow)
            {
                $dealId = isset($paymentRow['MyPayment']['deal_id']) ? (int)$paymentRow['MyPayment']['deal_id'] : 0;
                if($dealId<=0)
                {
                    continue;
                }
                $dealPaymentSummary[$dealId] = array(
                    'paid_amount'=>isset($paymentRow[0]['paid_amount']) ? round((float)$paymentRow[0]['paid_amount'],2) : 0,
                    'receipt_count'=>isset($paymentRow[0]['receipt_count']) ? (int)$paymentRow[0]['receipt_count'] : 0
                );
            }
        }
        $refundRequestBlocks = $this->_refundRequestBlocksForDeals($dealIds);
        $runningRefundNotice = $this->_runningRefundNoticeForClient((int)$this->userId);
        $dealsBillingMap = $this->_getDealsBillingMeterMap($postArr, (int)$this->userId);
        $this->set('postArr',$postArr);
        $this->set('dealPaymentSummary',$dealPaymentSummary);
        $this->set('refundRequestBlocks',$refundRequestBlocks);
        $this->set('runningRefundNotice',$runningRefundNotice);
        $this->set('dealsBillingMap',$dealsBillingMap);
    }

    protected function _getDealsBillingMeterMap($dealRows, $clientId)
    {
        $clientId = (int)$clientId;
        $map = array();
        if(empty($dealRows))
        {
            return $map;
        }

        $dealIds = array();
        $propertyIds = array();
        foreach((array)$dealRows as $row)
        {
            if(!empty($row['MyDeal']['id']))
            {
                $dId = (int)$row['MyDeal']['id'];
                $dealIds[] = $dId;
                $map[$dId] = array(
                    'has_billing' => false,
                    'meter_count' => 0,
                    'bill_count' => 0,
                    'billing_property_id' => 0,
                    'meter_numbers' => array()
                );
            }
            if(!empty($row['MyDeal']['property_id']))
            {
                $propertyIds[] = (int)$row['MyDeal']['property_id'];
            }
        }
        $dealIds = array_values(array_unique(array_filter($dealIds)));
        $propertyIds = array_values(array_unique(array_filter($propertyIds)));

        try
        {
            $db = ConnectionManager::getDataSource('default');
            
            // 1. Find billing_client_id(s) for this client
            $bClientIds = array();
            $clientData = $this->Session->read('frontUser.Client');
            $condPhone = array();
            if(!empty($clientData['phone'])) { $condPhone[] = "'" . addslashes(trim($clientData['phone'])) . "'"; }
            if(!empty($clientData['mobile'])) { $condPhone[] = "'" . addslashes(trim($clientData['mobile'])) . "'"; }

            $bClientSql = "SELECT id FROM billing_clients WHERE app_client_id = {$clientId}";
            if(!empty($condPhone))
            {
                $bClientSql .= " OR phone IN (" . implode(',', $condPhone) . ")";
            }
            $bClientRows = (array)$db->query($bClientSql);
            foreach($bClientRows as $bcr)
            {
                if(!empty($bcr['billing_clients']['id']))
                {
                    $bClientIds[] = (int)$bcr['billing_clients']['id'];
                }
            }
            $bClientIds = array_values(array_unique(array_filter($bClientIds)));

            // 2. Query billing_properties linked by app_deal_id, app_property_id, or billing_client_id
            $whereParts = array();
            if(!empty($dealIds))
            {
                $whereParts[] = "bp.app_deal_id IN (" . implode(',', $dealIds) . ")";
            }
            if(!empty($propertyIds))
            {
                $whereParts[] = "bp.app_property_id IN (" . implode(',', $propertyIds) . ")";
            }
            if(!empty($bClientIds))
            {
                $whereParts[] = "bp.billing_client_id IN (" . implode(',', $bClientIds) . ")";
            }

            if(!empty($whereParts))
            {
                $sql = "SELECT bp.id AS billing_prop_id, bp.app_deal_id, bp.app_property_id, bp.billing_client_id, " .
                       "bm.id AS meter_id, bm.meter_number, bm.utility_type, " .
                       "COUNT(DISTINCT bb.id) AS bill_count " .
                       "FROM billing_properties bp " .
                       "LEFT JOIN billing_meters bm ON bm.billing_property_id = bp.id " .
                       "LEFT JOIN billing_bills bb ON bb.billing_property_id = bp.id " .
                       "WHERE (" . implode(' OR ', $whereParts) . ") " .
                       "GROUP BY bp.id, bp.app_deal_id, bp.app_property_id, bp.billing_client_id, bm.id, bm.meter_number, bm.utility_type";

                $rows = (array)$db->query($sql);
                foreach($rows as $r)
                {
                    $flat = array_merge(
                        isset($r['bp']) ? $r['bp'] : array(),
                        isset($r['bm']) ? $r['bm'] : array(),
                        isset($r[0]) ? $r[0] : array()
                    );

                    $appDealId = !empty($flat['app_deal_id']) ? (int)$flat['app_deal_id'] : 0;
                    $appPropId = !empty($flat['app_property_id']) ? (int)$flat['app_property_id'] : 0;
                    $meterId = !empty($flat['meter_id']) ? (int)$flat['meter_id'] : 0;
                    $meterNum = !empty($flat['meter_number']) ? trim($flat['meter_number']) : '';
                    $billCount = !empty($flat['bill_count']) ? (int)$flat['bill_count'] : 0;
                    $billingPropId = !empty($flat['billing_prop_id']) ? (int)$flat['billing_prop_id'] : 0;
                    $billingClientId = !empty($flat['billing_client_id']) ? (int)$flat['billing_client_id'] : 0;

                    foreach($dealRows as $dRow)
                    {
                        $dId = (int)$dRow['MyDeal']['id'];
                        $dPropId = !empty($dRow['MyDeal']['property_id']) ? (int)$dRow['MyDeal']['property_id'] : 0;

                        $matched = false;
                        if($appDealId > 0 && $appDealId === $dId)
                        {
                            $matched = true;
                        }
                        elseif($appPropId > 0 && $appPropId === $dPropId)
                        {
                            $matched = true;
                        }
                        elseif(!empty($bClientIds) && in_array($billingClientId, $bClientIds, true))
                        {
                            if($appPropId > 0 && $appPropId === $dPropId)
                            {
                                $matched = true;
                            }
                            elseif($appPropId <= 0 && count($dealRows) === 1)
                            {
                                $matched = true;
                            }
                        }

                        if($matched && ($meterId > 0 || $billCount > 0 || $billingPropId > 0))
                        {
                            $map[$dId]['has_billing'] = true;
                            $map[$dId]['billing_property_id'] = $billingPropId;
                            if($meterId > 0)
                            {
                                $map[$dId]['meter_count']++;
                                if($meterNum !== '' && !in_array($meterNum, $map[$dId]['meter_numbers']))
                                {
                                    $map[$dId]['meter_numbers'][] = $meterNum;
                                }
                            }
                            $map[$dId]['bill_count'] += $billCount;
                        }
                    }
                }
            }
        }
        catch(Exception $e)
        {
        }

        return $map;
    }

    protected function _runningRefundNoticeForClient($clientId)
    {
        $clientId = (int)$clientId;
        if($clientId <= 0)
        {
            return array();
        }
        try
        {
            $rows = (array)$this->MyDeal->query(
                "SELECT r.`id`, r.`net_refund`, r.`status`, rr.`id` AS request_id, rr.`request_no`, " .
                "COALESCE(SUM(CASE WHEN s.`status`='Paid' THEN IF(s.`paid_amount`>0,s.`paid_amount`,s.`amount`) ELSE 0 END),0) AS paid_total, " .
                "COALESCE(SUM(CASE WHEN s.`id` IS NOT NULL AND (s.`status`<>'Paid' OR s.`status` IS NULL) THEN GREATEST(s.`amount`-s.`paid_amount`,0) ELSE 0 END),0) AS pending_total " .
                "FROM `deal_refunds` r " .
                "LEFT JOIN `deal_refund_schedules` s ON s.`refund_id`=r.`id` " .
                "LEFT JOIN `deal_refund_requests` rr ON rr.`converted_refund_id`=r.`id` " .
                "WHERE r.`client_id`=".$clientId." " .
                "GROUP BY r.`id`, r.`net_refund`, r.`status`, rr.`id`, rr.`request_no` " .
                "HAVING pending_total > 0 OR paid_total < COALESCE(r.`net_refund`,0) " .
                "ORDER BY r.`id` DESC LIMIT 1"
            );
            if(empty($rows[0]))
            {
                return array();
            }
            $row = $this->_flattenRunningRefundRow($rows[0]);
            $requestId = !empty($row['request_id']) ? (int)$row['request_id'] : 0;
            if($requestId <= 0)
            {
                return array();
            }
            return array(
                'request_id' => $requestId,
                'request_no' => !empty($row['request_no']) ? $row['request_no'] : '',
                'refund_id' => !empty($row['id']) ? (int)$row['id'] : 0,
                'net_refund' => isset($row['net_refund']) ? (float)$row['net_refund'] : 0,
                'paid_total' => isset($row['paid_total']) ? (float)$row['paid_total'] : 0,
                'pending_total' => isset($row['pending_total']) ? (float)$row['pending_total'] : 0,
                'status' => !empty($row['status']) ? $row['status'] : ''
            );
        }
        catch(Exception $e)
        {
        }
        return array();
    }

    protected function _flattenRunningRefundRow($rawRow)
    {
        $flat = array();
        foreach((array)$rawRow as $part)
        {
            if(is_array($part))
            {
                foreach($part as $key => $value)
                {
                    if(!isset($flat[$key]))
                    {
                        $flat[$key] = $value;
                    }
                }
            }
        }
        return $flat;
    }
    protected function _dealChargesTableReady()
    {
        try
        {
            $this->loadModel('Admin.DealsPayment');
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
                // Keep the client ledger available if charge lookup is unavailable.
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
    protected function _prepareClientLedgerView($dealId)
    {
        $dealId = (int)$dealId;
        if($dealId <= 0)
        {
            return false;
        }
        $this->loadModel('Admin.DealsPayment');
        $this->loadModel('Admin.Deal');
        $this->loadModel('Admin.PlansPayment');

        $dealConditions = array('Deal.id'=>$dealId,'Deal.client_id'=>$this->userId,$this->_dealVisibilityCondition('Deal'));
        $post = $this->DealsPayment->find('all',array(
            'joins'=>array(
                array('table'=>'deals','alias'=>'Deal','type'=>'LEFT','conditions'=>array('DealsPayment.deal_id=Deal.id')),
                array('table'=>'clients','alias'=>'Client','type'=>'LEFT','conditions'=>array('Deal.client_id=Client.id')),
                array('table'=>'properties','alias'=>'Property','type'=>'LEFT','conditions'=>array('Deal.property_id=Property.id')),
                array('table'=>'types','alias'=>'Type','type'=>'LEFT','conditions'=>array('Property.type_id=Type.id')),
                array('table'=>'contracts','alias'=>'Contract','type'=>'LEFT','conditions'=>array('Property.contract_id=Contract.id')),
                array('table'=>'projects','alias'=>'Project','type'=>'LEFT','conditions'=>array('Property.project_id=Project.id')),
                array('table'=>'units','alias'=>'Unit','type'=>'LEFT','conditions'=>array('Property.unit_id=Unit.id')),
                array('table'=>'plans','alias'=>'Plan','type'=>'LEFT','conditions'=>array('Deal.plan_id=Plan.id')),
                array('table'=>'plans_payments','alias'=>'PlansPayment','type'=>'LEFT','conditions'=>array('DealsPayment.plans_payment_id=PlansPayment.id')),
                array('table'=>'paymenttypes','alias'=>'Paymenttype','type'=>'LEFT','conditions'=>array('DealsPayment.paymenttype_id=Paymenttype.id'))
            ),
            'fields'=>array('DealsPayment.*','Deal.*','Client.*','Property.*','Type.*','Contract.*','Project.*','Unit.*','Plan.*','PlansPayment.*','Paymenttype.*'),
            'conditions'=>$dealConditions,
            'order'=>array('DealsPayment.id'=>'asc'),
            'recursive'=>-1
        ));

        $dealHeaderRow = !empty($post[0]) ? $post[0] : array();
        if(empty($dealHeaderRow))
        {
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
                'conditions' => $dealConditions,
                'recursive' => -1
            ));
            if(empty($dealHeaderRow['Deal']['id']))
            {
                return false;
            }
        }

        $planRows = $this->PlansPayment->find('all',array(
            'fields'=>array('PlansPayment.id','PlansPayment.amount','PlansPayment.date'),
            'conditions'=>array('PlansPayment.deal_id'=>$dealId),
            'order'=>array('PlansPayment.id'=>'asc'),
            'recursive'=>-1
        ));
        $paidRows = $this->DealsPayment->find('all',array(
            'fields'=>array('DealsPayment.plans_payment_id','SUM(DealsPayment.emi) AS paid_total'),
            'conditions'=>array('DealsPayment.deal_id'=>$dealId),
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
        $overdueAmount = 0;
        $overdueCount = 0;
        foreach((array)$planRows as $planRow)
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
            $this->loadModel('Admin.BallotingAllotment');
            $allotmentRow = $this->BallotingAllotment->find('first', array(
                'fields' => array('BallotingAllotment.allotment_no'),
                'conditions' => array(
                    'BallotingAllotment.deal_id' => $dealId,
                    'BallotingAllotment.status' => 'Allotted'
                ),
                'order' => array('BallotingAllotment.id' => 'desc'),
                'recursive' => -1
            ));
            if(!empty($allotmentRow['BallotingAllotment']['allotment_no']))
            {
                $allottedUnitNumber = trim((string)$allotmentRow['BallotingAllotment']['allotment_no']);
            }
        }
        catch(Exception $e)
        {
            $allottedUnitNumber = '';
        }

        $agencyLogoFile = '';
        $projectId = !empty($dealHeaderRow['Project']['id']) ? (int)$dealHeaderRow['Project']['id'] : 0;
        if($projectId > 0)
        {
            try
            {
                $this->loadModel('Admin.User');
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
            catch(Exception $e)
            {
                $agencyLogoFile = '';
            }
        }

        $this->set('dealPayment',$post);
        $this->set('dealId',$dealId);
        $this->set('overdueAmount',$overdueAmount);
        $this->set('overdueCount',$overdueCount);
        $this->set('allottedUnitNumber',$allottedUnitNumber);
        $this->set('agencyLogoFile',$agencyLogoFile);
        $this->set('agency_logo',$agencyLogoFile);
        $this->set('dealHeaderRow',$dealHeaderRow);
        $this->set('dealChargeSummary',$this->_dealChargeSummaryForDeal($dealId, $dealHeaderRow));
        $this->set('clientLedgerPreview',true);
        return true;
    }
    public function ledgerpdf($id = null)
    {
        if(!$this->_prepareClientLedgerView($id))
        {
            $this->Session->setFlash(__('Invalid Post.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        $this->set('ledgerAutoPdf',true);
        $this->render('Admin.DealsPayments/index');
    }
    public function view($id = null)
    {
        $this->layout = null;        
        if (!$id)
        {
            $this->Session->setFlash(__('Invalid Post.'),'flash',array('alert'=>'danger'));
            $this->redirect(array('action' => 'index'));
        }
        $post = $this->MyDeal->find('first',array('conditions'=>array('MyDeal.id'=>$id,'MyDeal.client_id'=>$this->userId,$this->_dealVisibilityCondition('MyDeal'))));
        if(!$post)
        {
            $this->Session->setFlash(__('Invalid Post.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        $contractTypeName = '-';
        $contractId = !empty($post['Property']['contract_id']) ? (int)$post['Property']['contract_id'] : 0;
        if($contractId>0)
        {
            $contractRow = $this->MyDeal->query("SELECT `Contract`.`name` FROM `contracts` AS `Contract` WHERE `Contract`.`id` = ".$contractId." LIMIT 1");
            if(!empty($contractRow[0]['Contract']['name']))
            {
                $contractTypeName = $contractRow[0]['Contract']['name'];
            }
        }
        $this->set('contractTypeName',$contractTypeName);
        $this->set('refundRequestBlock',$this->_refundRequestBlockForDeal((int)$id));
        $this->set('post',$post);
    }
    public function printinvoice($id = null)
    {
        $this->layout = null;
        $id = (int)$id;
        if (!$id)
        {
            $this->Session->setFlash(__('Invalid Post.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        $this->loadModel('Admin.Deal');
        $this->loadModel('Admin.PlansPayment');
        $this->loadModel('Admin.DealsPayment');
        $this->loadModel('Admin.Project');

        $post = $this->Deal->find('first', array(
            'conditions' => array(
                'Deal.id' => $id,
                'Deal.client_id' => $this->userId,
                $this->_dealVisibilityCondition('Deal')
            )
        ));
        if(empty($post))
        {
            $post = $this->MyDeal->find('first', array(
                'conditions' => array(
                    'MyDeal.id' => $id,
                    'MyDeal.client_id' => $this->userId,
                    $this->_dealVisibilityCondition('MyDeal')
                )
            ));
            if(!empty($post['MyDeal']) && empty($post['Deal']))
            {
                $post['Deal'] = $post['MyDeal'];
            }
        }
        if(empty($post) || (empty($post['Deal']['id']) && empty($post['MyDeal']['id'])))
        {
            $this->Session->setFlash(__('Invalid Post.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'index'));
        }

        if(empty($post['Deal']) && !empty($post['MyDeal']))
        {
            $post['Deal'] = $post['MyDeal'];
        }

        $plansPaymentArr = $this->PlansPayment->find('all',array(
            'conditions'=>array('PlansPayment.deal_id'=>$id),
            'order'=>array('PlansPayment.id'=>'asc'),
            'recursive'=>-1
        ));

        $planStatusMap = array();
        if(!empty($plansPaymentArr))
        {
            $planIds = array();
            foreach($plansPaymentArr as $planRow)
            {
                if(isset($planRow['PlansPayment']['id']))
                {
                    $planIds[] = (int)$planRow['PlansPayment']['id'];
                }
            }
            $planIds = array_values(array_unique(array_filter($planIds)));
            if(!empty($planIds))
            {
                $paidRows = $this->DealsPayment->find('all',array(
                    'fields'=>array('DealsPayment.plans_payment_id','SUM(DealsPayment.emi) AS paid_total'),
                    'conditions'=>array(
                        'DealsPayment.deal_id'=>$id,
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
                foreach($plansPaymentArr as $planRow)
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
        $this->set('plansPaymentArr',$plansPaymentArr);
        $this->set('planStatusMap',$planStatusMap);

        $dealChargeRows = array();
        $dealChargeTotal = 0;
        $dealChargeNames = array();
        try
        {
            $this->loadModel('Admin.DealCharge');
            $dealChargeRows = $this->DealCharge->find('all', array(
                'conditions' => array(
                    'DealCharge.deal_id' => (int)$id,
                    'DealCharge.status' => 1
                ),
                'order' => array('DealCharge.id' => 'asc'),
                'recursive' => -1
            ));
            foreach((array)$dealChargeRows as $dealChargeRow)
            {
                if(empty($dealChargeRow['DealCharge']))
                {
                    continue;
                }
                $dealCharge = $dealChargeRow['DealCharge'];
                $dealChargeTotal += isset($dealCharge['total_amount']) ? (float)$dealCharge['total_amount'] : 0;
                $chargeName = isset($dealCharge['charge_name']) ? trim((string)$dealCharge['charge_name']) : '';
                if($chargeName !== '')
                {
                    $dealChargeNames[$chargeName] = $chargeName;
                }
            }
        }
        catch(Exception $e)
        {
            $dealChargeRows = array();
            $dealChargeTotal = 0;
            $dealChargeNames = array();
        }
        $this->set('dealChargeRows',$dealChargeRows);
        $this->set('dealChargeTotal',round((float)$dealChargeTotal, 2));
        $this->set('dealChargeNames',array_values($dealChargeNames));

        $agencyProject = array();
        $agencyLogoFile = '';
        $projectId = 0;
        if(!empty($post['Property']['project_id']))
        {
            $projectId = (int)$post['Property']['project_id'];
        }
        if($projectId > 0)
        {
            $agencyProjectRow = $this->Project->find('first', array(
                'fields' => array('Project.*'),
                'conditions' => array('Project.id' => $projectId),
                'recursive' => -1
            ));
            if(!empty($agencyProjectRow['Project']))
            {
                $agencyProject = $agencyProjectRow['Project'];
            }
            $this->loadModel('Admin.User');
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
        $this->set('agencyProject',$agencyProject);
        $this->set('agencyLogoFile',$agencyLogoFile);
        $this->set('agency_logo',$agencyLogoFile);
        $this->set('post',$post);
    }

    public function letters($dealId = null)
    {
        $dealId = (int)$dealId;
        if($dealId <= 0)
        {
            $this->Session->setFlash(__('Invalid Post.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        $deal = $this->MyDeal->find('first',array(
            'conditions'=>array('MyDeal.id'=>$dealId,'MyDeal.client_id'=>$this->userId,$this->_dealVisibilityCondition('MyDeal')),
            'recursive'=>0
        ));
        if(empty($deal['MyDeal']['id']))
        {
            $this->Session->setFlash(__('Invalid Post.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'index'));
        }

        $this->loadModel('Admin.Realestateletter');
        $letters = array();
        try
        {
            $letters = $this->Realestateletter->find('all',array(
                'fields'=>array('Realestateletter.*','Template.name'),
                'joins'=>array(
                    array('table'=>'templates','alias'=>'Template','type'=>'LEFT','conditions'=>array('Template.id=Realestateletter.template_id'))
                ),
                'conditions'=>array('Realestateletter.deal_id'=>$dealId,'Realestateletter.status'=>'Published'),
                'order'=>array('Realestateletter.id'=>'desc'),
                'recursive'=>-1
            ));
        }
        catch (Exception $e)
        {
            $letters = array();
        }

        $this->set('deal',$deal);
        $this->set('dealId',$dealId);
        $this->set('letters',$letters);
        $this->set('portalLetterTypeArr',$this->_portalLetterTypeArr);
        $this->set('portalAutoLetterTypeArr',$this->_portalAutoLetterTypeArr);
    }

    public function viewletter($dealId = null, $letterId = null)
    {
        $dealId = (int)$dealId;
        $letterId = (int)$letterId;
        if($dealId <= 0 || $letterId <= 0)
        {
            $this->Session->setFlash(__('Invalid Post.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        $post = $this->_portalLetterDetails($dealId, $letterId);
        if(empty($post['Realestateletter']['id']))
        {
            $this->Session->setFlash(__('Invalid Post.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'letters', $dealId));
        }
        $renderedDescription = $this->_portalRenderLetterBody($post);
        $letterType = isset($post['Realestateletter']['letter_type']) ? trim((string)$post['Realestateletter']['letter_type']) : '';
        $letterTypeLabel = isset($this->_portalLetterTypeArr[$letterType]) ? $this->_portalLetterTypeArr[$letterType] : $letterType;
        $this->set('dealId',$dealId);
        $this->set('post',$post);
        $this->set('renderedDescription',$renderedDescription);
        $this->set('isFullHtmlTemplate',$this->_portalIsFullHtmlDocument($renderedDescription));
        $this->set('letterTypeLabel',$letterTypeLabel);
        $this->set('autoPrint',false);
        $this->layout = null;
        $this->render('letterprint');
    }

    public function printletter($dealId = null, $letterId = null)
    {
        $this->layout = null;
        $dealId = (int)$dealId;
        $letterId = (int)$letterId;
        if($dealId <= 0 || $letterId <= 0)
        {
            $this->Session->setFlash(__('Invalid Post.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        $post = $this->_portalLetterDetails($dealId, $letterId);
        if(empty($post['Realestateletter']['id']))
        {
            $this->Session->setFlash(__('Invalid Post.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'letters', $dealId));
        }
        $renderedDescription = $this->_portalRenderLetterBody($post);
        $letterType = isset($post['Realestateletter']['letter_type']) ? trim((string)$post['Realestateletter']['letter_type']) : '';
        $letterTypeLabel = isset($this->_portalLetterTypeArr[$letterType]) ? $this->_portalLetterTypeArr[$letterType] : $letterType;
        $this->set('dealId',$dealId);
        $this->set('post',$post);
        $this->set('renderedDescription',$renderedDescription);
        $this->set('isFullHtmlTemplate',$this->_portalIsFullHtmlDocument($renderedDescription));
        $this->set('letterTypeLabel',$letterTypeLabel);
        $this->set('autoPrint',true);
        $this->render('letterprint');
    }

    protected function _portalLetterDetails($dealId, $letterId)
    {
        $dealId = (int)$dealId;
        $letterId = (int)$letterId;
        if($dealId <= 0 || $letterId <= 0)
        {
            return array();
        }
        $this->loadModel('Admin.Realestateletter');
        try
        {
            return (array)$this->Realestateletter->find('first', array(
                'fields' => array(
                    'Realestateletter.*',
                    'Deal.*',
                    'Client.*',
                    'Property.*',
                    'Type.name','Contract.name','Unit.name',
                    'Project.*',
                    'Template.*'
                ),
                'joins' => array(
                    array('table' => 'deals', 'alias' => 'Deal', 'type' => 'LEFT', 'conditions' => array('Deal.id=Realestateletter.deal_id')),
                    array('table' => 'clients', 'alias' => 'Client', 'type' => 'LEFT', 'conditions' => array('Client.id=Deal.client_id')),
                    array('table' => 'properties', 'alias' => 'Property', 'type' => 'LEFT', 'conditions' => array('Property.id=Deal.property_id')),
                    array('table' => 'types', 'alias' => 'Type', 'type' => 'LEFT', 'conditions' => array('Type.id=Property.type_id')),
                    array('table' => 'contracts', 'alias' => 'Contract', 'type' => 'LEFT', 'conditions' => array('Contract.id=Property.contract_id')),
                    array('table' => 'units', 'alias' => 'Unit', 'type' => 'LEFT', 'conditions' => array('Unit.id=Property.unit_id')),
                    array('table' => 'projects', 'alias' => 'Project', 'type' => 'LEFT', 'conditions' => array('Project.id=Property.project_id')),
                    array('table' => 'templates', 'alias' => 'Template', 'type' => 'LEFT', 'conditions' => array('Template.id=Realestateletter.template_id'))
                ),
                'conditions' => array(
                    'Realestateletter.id' => $letterId,
                    'Realestateletter.deal_id' => $dealId,
                    'Realestateletter.status' => 'Published',
                    'Deal.client_id' => $this->userId,
                    $this->_dealVisibilityCondition('Deal')
                ),
                'recursive' => -1
            ));
        }
        catch (Exception $e)
        {
            return array();
        }
    }

    protected function _portalImageAssetUrl($asset)
    {
        $asset = trim(str_replace('\\', '/', (string)$asset), '/');
        if ($asset === '')
        {
            return '';
        }
        $base = rtrim((string)Router::url('/', false), '/');
        return $base . '/img/' . $asset;
    }

    protected function _portalResolveImageFromCandidates($candidates)
    {
        foreach ((array)$candidates as $candidate)
        {
            if (!is_string($candidate))
            {
                continue;
            }
            $candidate = trim(str_replace('\\', '/', $candidate), '/');
            if ($candidate === '')
            {
                continue;
            }
            $filePath = WWW_ROOT . 'img' . DS . str_replace('/', DS, $candidate);
            if (@file_exists($filePath))
            {
                return $this->_portalImageAssetUrl($candidate);
            }
        }
        return '';
    }

    protected function _portalResolveClientPhotoUrl($client)
    {
        $photo = isset($client['photo']) ? trim((string)$client['photo']) : '';
        if ($photo === '' && !empty($client['image']))
        {
            $photo = trim((string)$client['image']);
        }
        if ($photo === '')
        {
            return $this->_portalResolveImageFromCandidates(array('User.png'));
        }
        $dir = isset($client['dir']) ? trim((string)$client['dir']) : '';
        $dirt = isset($client['dirt']) ? trim((string)$client['dirt']) : '';
        $candidates = array(
            ($dir !== '' ? trim($dir, '/\\') . '/' . $photo : ''),
            ($dirt !== '' ? trim($dirt, '/\\') . '/' . $photo : ''),
            'client/' . $photo,
            'contact/' . $photo,
            'user/' . $photo,
            ($dir !== '' ? trim($dir, '/\\') . '_thumb/' . $photo : ''),
            ($dirt !== '' ? trim($dirt, '/\\') . '_thumb/' . $photo : ''),
            'client_thumb/' . $photo,
            'contact_thumb/' . $photo,
            'user_thumb/' . $photo,
            $photo
        );
        $resolved = $this->_portalResolveImageFromCandidates($candidates);
        if ($resolved !== '')
        {
            return $resolved;
        }
        return $this->_portalResolveImageFromCandidates(array('User.png'));
    }

    protected function _portalResolveClientDocUrl($fileName, $dirName, $fallbackDir)
    {
        $fileName = trim((string)$fileName);
        if ($fileName === '')
        {
            return '';
        }
        $dirName = trim((string)$dirName);
        $fallbackDir = trim((string)$fallbackDir);
        $candidates = array(
            ($dirName !== '' ? trim($dirName, '/\\') . '_thumb/' . $fileName : ''),
            ($dirName !== '' ? trim($dirName, '/\\') . '/' . $fileName : ''),
            ($fallbackDir !== '' ? trim($fallbackDir, '/\\') . '_thumb/' . $fileName : ''),
            ($fallbackDir !== '' ? trim($fallbackDir, '/\\') . '/' . $fileName : ''),
            'contact_thumb/' . $fileName,
            'contact/' . $fileName,
            $fileName
        );
        return $this->_portalResolveImageFromCandidates($candidates);
    }

    protected function _portalContactImageTag($url, $altText)
    {
        $url = trim((string)$url);
        if ($url === '')
        {
            return '';
        }
        $safeUrl = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
        $safeAlt = htmlspecialchars((string)$altText, ENT_QUOTES, 'UTF-8');
        return '<img src="' . $safeUrl . '" alt="' . $safeAlt . '" style="max-width:180px;max-height:180px;" />';
    }

    protected function _portalAgencyUserInfo($projectId)
    {
        $projectId = (int)$projectId;
        if ($projectId <= 0)
        {
            return array();
        }
        if (isset($this->_portalAgencyUserInfoCache[$projectId]))
        {
            return (array)$this->_portalAgencyUserInfoCache[$projectId];
        }
        $info = array();
        try
        {
            $User = ClassRegistry::init('Admin.User');
            $fields = array('User.id', 'User.name');
            foreach (array('address', 'phone', 'mobile', 'email', 'photo') as $field)
            {
                if ($User->hasField($field))
                {
                    $fields[] = 'User.' . $field;
                }
            }
            $conditions = array('User.project_id' => $projectId);
            $row = array();
            if ($User->hasField('ugroup_id'))
            {
                $row = (array)$User->find('first', array(
                    'fields' => $fields,
                    'conditions' => array_merge($conditions, array('User.ugroup_id' => 4)),
                    'order' => array('User.id' => 'asc'),
                    'recursive' => -1
                ));
            }
            if (empty($row['User']))
            {
                $row = (array)$User->find('first', array(
                    'fields' => $fields,
                    'conditions' => $conditions,
                    'order' => array('User.id' => 'asc'),
                    'recursive' => -1
                ));
            }
            if (!empty($row['User']))
            {
                $info = (array)$row['User'];
            }
        }
        catch (Exception $e)
        {
            $info = array();
        }
        $this->_portalAgencyUserInfoCache[$projectId] = $info;
        return $info;
    }

    protected function _portalResolveAgencyLogoUrl($project)
    {
        $project = (array)$project;
        $projectId = isset($project['id']) ? (int)$project['id'] : 0;
        $cacheKey = ($projectId > 0) ? (string)$projectId : md5(serialize($project));
        if (isset($this->_portalAgencyLogoUrlCache[$cacheKey]))
        {
            return (string)$this->_portalAgencyLogoUrlCache[$cacheKey];
        }

        $agencyLogoFile = '';
        if ($projectId > 0)
        {
            $agencyUserInfo = $this->_portalAgencyUserInfo($projectId);
            if (!empty($agencyUserInfo['photo']))
            {
                $agencyLogoFile = trim((string)$agencyUserInfo['photo']);
            }
        }

        $logoUrl = $this->_portalResolveImageFromCandidates(array(
            ($agencyLogoFile !== '' ? 'user_thumb/' . $agencyLogoFile : ''),
            ($agencyLogoFile !== '' ? 'user/' . $agencyLogoFile : ''),
            ($agencyLogoFile !== '' ? $agencyLogoFile : ''),
            (!empty($project['logo']) ? 'agency_thumb/' . $project['logo'] : ''),
            (!empty($project['logo']) ? 'agency/' . $project['logo'] : ''),
            (!empty($project['logo']) ? $project['logo'] : ''),
            (!empty($project['image']) ? 'agency_thumb/' . $project['image'] : ''),
            (!empty($project['image']) ? 'agency/' . $project['image'] : ''),
            (!empty($project['image']) ? $project['image'] : ''),
            (!empty($project['photo']) ? 'agency_thumb/' . $project['photo'] : ''),
            (!empty($project['photo']) ? 'agency/' . $project['photo'] : ''),
            (!empty($project['photo']) ? $project['photo'] : '')
        ));
        $this->_portalAgencyLogoUrlCache[$cacheKey] = (string)$logoUrl;
        return (string)$logoUrl;
    }

    protected function _portalAgencyLogoImageTag($url, $altText)
    {
        $url = trim((string)$url);
        if ($url === '')
        {
            return '';
        }
        $safeUrl = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
        $safeAlt = htmlspecialchars((string)$altText, ENT_QUOTES, 'UTF-8');
        return '<img src="' . $safeUrl . '" alt="' . $safeAlt . '" style="display:block;max-width:100%;max-height:100%;width:auto;height:auto;object-fit:contain;margin:auto;" />';
    }

    protected function _portalTemplateSignatureUrl($template, $fileField, $dirField)
    {
        $fileName = isset($template[$fileField]) ? trim((string)$template[$fileField]) : '';
        if ($fileName === '')
        {
            return '';
        }
        $dirName = isset($template[$dirField]) ? trim((string)$template[$dirField]) : 'letter_signatures';
        if ($dirName === '')
        {
            $dirName = 'letter_signatures';
        }
        return $this->_portalResolveImageFromCandidates(array(
            trim($dirName, '/\\') . '/' . $fileName,
            trim($dirName, '/\\') . '_thumb/' . $fileName,
            'letter_signatures/' . $fileName,
            $fileName
        ));
    }

    protected function _portalSignatureImageTag($url, $altText, $fallbackTitle)
    {
        $url = trim((string)$url);
        if ($url !== '')
        {
            $safeUrl = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
            $safeAlt = htmlspecialchars((string)$altText, ENT_QUOTES, 'UTF-8');
            return '<img class="sig-image" src="' . $safeUrl . '" alt="' . $safeAlt . '" style="max-height:72px;max-width:270px;display:inline-block;" />';
        }
        return '<div class="digital-stamp-mock">Securely Signed<br><strong>' . htmlspecialchars((string)$fallbackTitle, ENT_QUOTES, 'UTF-8') . '</strong><br>Verification Ref</div>';
    }

    protected function _portalActiveDealChargeRows($dealId)
    {
        $dealId = (int)$dealId;
        if ($dealId <= 0)
        {
            return array();
        }
        try
        {
            $this->loadModel('Admin.Realestateletter');
            $rows = (array)$this->Realestateletter->query(
                "SELECT `id`,`charge_name`,`cycle_type`,`cycle_count`,`cycle_amount`,`total_amount`,`start_date`
                 FROM `deal_charges`
                 WHERE `deal_id`=" . $dealId . " AND `status`=1
                 ORDER BY `id` ASC"
            );
        }
        catch (Exception $e)
        {
            return array();
        }
        $charges = array();
        foreach ($rows as $row)
        {
            $charge = array();
            if (isset($row['deal_charges']) && is_array($row['deal_charges']))
            {
                $charge = $row['deal_charges'];
            }
            elseif (isset($row[0]) && is_array($row[0]))
            {
                $charge = $row[0];
            }
            else
            {
                foreach ((array)$row as $part)
                {
                    if (is_array($part) && isset($part['charge_name']))
                    {
                        $charge = $part;
                        break;
                    }
                }
            }
            if (!empty($charge))
            {
                $charges[] = $charge;
            }
        }
        return $charges;
    }

    protected function _portalDealChargesTableHtml($rows)
    {
        $rows = (array)$rows;
        if (empty($rows))
        {
            return 'N/A';
        }
        $cycleLabels = array(
            'M' => __('Monthly'),
            'Q' => __('Quarterly'),
            'H' => __('Biannual'),
            'Y' => __('Yearly'),
            'O' => __('One Time')
        );
        $html = '<table class="table table-bordered" style="width:100%;border-collapse:collapse;font-size:12px;">';
        $html .= '<thead><tr>';
        $html .= '<th style="border:1px solid #ccc;padding:5px;">' . __('Charge') . '</th>';
        $html .= '<th style="border:1px solid #ccc;padding:5px;">' . __('Cycle') . '</th>';
        $html .= '<th style="border:1px solid #ccc;padding:5px;">' . __('Count') . '</th>';
        $html .= '<th style="border:1px solid #ccc;padding:5px;">' . __('Amount') . '</th>';
        $html .= '<th style="border:1px solid #ccc;padding:5px;">' . __('Total') . '</th>';
        $html .= '</tr></thead><tbody>';
        foreach ($rows as $row)
        {
            $cycleType = isset($row['cycle_type']) ? strtoupper(trim((string)$row['cycle_type'])) : '';
            $cycleLabel = isset($cycleLabels[$cycleType]) ? $cycleLabels[$cycleType] : $cycleType;
            $html .= '<tr>';
            $html .= '<td style="border:1px solid #ccc;padding:5px;">' . h(isset($row['charge_name']) ? $row['charge_name'] : '') . '</td>';
            $html .= '<td style="border:1px solid #ccc;padding:5px;">' . h($cycleLabel) . '</td>';
            $html .= '<td style="border:1px solid #ccc;padding:5px;text-align:center;">' . h(isset($row['cycle_count']) ? $row['cycle_count'] : '') . '</td>';
            $html .= '<td style="border:1px solid #ccc;padding:5px;text-align:right;">' . h($this->currency . CakeNumber::format(isset($row['cycle_amount']) ? (float)$row['cycle_amount'] : 0)) . '</td>';
            $html .= '<td style="border:1px solid #ccc;padding:5px;text-align:right;">' . h($this->currency . CakeNumber::format(isset($row['total_amount']) ? (float)$row['total_amount'] : 0)) . '</td>';
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';
        return $html;
    }

    protected function _portalJoinLetterList($values)
    {
        $clean = array();
        foreach ((array)$values as $value)
        {
            $value = trim((string)$value);
            if ($value !== '')
            {
                $clean[] = $value;
            }
        }
        if (empty($clean))
        {
            return '-';
        }
        return implode(', ', $clean);
    }

    protected function _portalDealOverdueSummaryForLetter($dealId, $asOfDate = null)
    {
        $dealId = (int)$dealId;
        $empty = array(
            'count' => 0,
            'label' => '-',
            'amount' => 0,
            'amount_text' => $this->currency . CakeNumber::format(0),
            'amount_words' => $this->_portalAmountInWords(0)
        );
        if ($dealId <= 0)
        {
            return $empty;
        }
        $asOfDate = trim((string)$asOfDate);
        if ($asOfDate === '' || strtotime($asOfDate) === false)
        {
            $asOfDate = date('Y-m-d');
        }
        else
        {
            $asOfDate = date('Y-m-d', strtotime($asOfDate));
        }
        try
        {
            $this->loadModel('Admin.Realestateletter');
            $rows = (array)$this->Realestateletter->query(
                "SELECT PP.id, PP.name, PP.amount, PP.date
                 FROM plans_payments PP
                 LEFT JOIN deals_payments DP ON DP.plans_payment_id=PP.id
                 WHERE PP.deal_id=" . $dealId . "
                   AND (PP.is_deal_charge=0 OR PP.is_deal_charge IS NULL)
                   AND PP.date<='" . $asOfDate . "'
                   AND DP.id IS NULL
                 ORDER BY PP.date ASC, PP.id ASC"
            );
        }
        catch (Exception $e)
        {
            $rows = array();
        }
        $names = array();
        $amount = 0;
        foreach ($rows as $row)
        {
            $data = isset($row['PP']) ? (array)$row['PP'] : (isset($row['plans_payments']) ? (array)$row['plans_payments'] : array());
            $name = isset($data['name']) ? trim((string)$data['name']) : '';
            if ($name !== '')
            {
                $names[] = $name;
            }
            $amount += isset($data['amount']) ? (float)$data['amount'] : 0;
        }
        $count = count($rows);
        if ($count <= 0)
        {
            return $empty;
        }
        $label = ($count > 3) ? __('Overdue Installments') . ' (' . $count . ')' : $this->_portalJoinLetterList($names);
        return array(
            'count' => $count,
            'label' => $label,
            'amount' => $amount,
            'amount_text' => $this->currency . CakeNumber::format((float)$amount),
            'amount_words' => $this->_portalAmountInWords($amount)
        );
    }

    protected function _portalFirstDealPaymentForLetter($dealId)
    {
        $dealId = (int)$dealId;
        $empty = array(
            'receipt_no' => 'N/A',
            'payment_method' => 'Un-Paid',
            'remarks' => '',
            'payment_date' => '',
            'amount' => 0,
            'payment_names' => ''
        );
        if ($dealId <= 0)
        {
            return $empty;
        }
        try
        {
            $this->loadModel('Admin.Realestateletter');
            $rows = (array)$this->Realestateletter->query(
                "SELECT DP.id, DP.receipt_no, DP.paymenttype_id, DP.remarks, DP.payment_date, DP.total_amount, DP.amount,
                        PT.name AS payment_method
                 FROM deals_payments DP
                 LEFT JOIN paymenttypes PT ON PT.id=DP.paymenttype_id
                 WHERE DP.deal_id=" . $dealId . "
                 ORDER BY DP.id ASC
                 LIMIT 1"
            );
        }
        catch (Exception $e)
        {
            return $empty;
        }
        if (empty($rows[0]))
        {
            return $empty;
        }
        $dp = isset($rows[0]['DP']) ? (array)$rows[0]['DP'] : (isset($rows[0]['deals_payments']) ? (array)$rows[0]['deals_payments'] : array());
        $pt = isset($rows[0]['PT']) ? (array)$rows[0]['PT'] : (isset($rows[0]['paymenttypes']) ? (array)$rows[0]['paymenttypes'] : array());
        $flat = isset($rows[0][0]) ? (array)$rows[0][0] : array();
        $receiptNo = isset($dp['receipt_no']) ? trim((string)$dp['receipt_no']) : '';
        if ($receiptNo === '')
        {
            $receiptNo = 'N/A';
        }
        $paymentMethod = isset($flat['payment_method']) ? trim((string)$flat['payment_method']) : '';
        if ($paymentMethod === '' && isset($pt['name']))
        {
            $paymentMethod = trim((string)$pt['name']);
        }
        if ($paymentMethod === '')
        {
            $paymentMethod = 'Un-Paid';
        }
        $amount = 0;
        if (isset($dp['total_amount']) && (float)$dp['total_amount'] > 0)
        {
            $amount = (float)$dp['total_amount'];
        }
        elseif (isset($dp['amount']))
        {
            $amount = (float)$dp['amount'];
        }
        $paymentNames = '';
        if ($receiptNo !== 'N/A')
        {
            try
            {
                $db = $this->Realestateletter->getDataSource();
                $receiptRows = (array)$this->Realestateletter->query(
                    "SELECT
                        COALESCE(SUM(CASE WHEN DP.amount IS NOT NULL AND DP.amount > 0 THEN DP.amount ELSE DP.total_amount END),0) AS receipt_paid_total,
                        GROUP_CONCAT(DISTINCT PP.name ORDER BY PP.id ASC SEPARATOR ', ') AS payment_names
                     FROM deals_payments DP
                     LEFT JOIN plans_payments PP ON PP.id=DP.plans_payment_id
                     WHERE DP.deal_id=" . $dealId . "
                       AND DP.receipt_no=" . $db->value($receiptNo, 'string')
                );
                if (isset($receiptRows[0][0]['receipt_paid_total']) && (float)$receiptRows[0][0]['receipt_paid_total'] > 0)
                {
                    $amount = (float)$receiptRows[0][0]['receipt_paid_total'];
                }
                if (!empty($receiptRows[0][0]['payment_names']))
                {
                    $paymentNames = trim((string)$receiptRows[0][0]['payment_names']);
                }
            }
            catch (Exception $e)
            {
                $paymentNames = '';
            }
        }
        return array(
            'receipt_no' => $receiptNo,
            'payment_method' => $paymentMethod,
            'remarks' => isset($dp['remarks']) ? trim((string)$dp['remarks']) : '',
            'payment_date' => !empty($dp['payment_date']) ? $this->_portalFormatDateValue($dp['payment_date'], '') : '',
            'amount' => $amount,
            'payment_names' => $paymentNames
        );
    }

    protected function _portalDealTotalPaidForLetter($dealId)
    {
        $dealId = (int)$dealId;
        if ($dealId <= 0)
        {
            return 0;
        }
        try
        {
            $this->loadModel('Admin.Realestateletter');
            $rows = (array)$this->Realestateletter->query(
                "SELECT COALESCE(SUM(CASE WHEN DP.amount IS NOT NULL AND DP.amount > 0 THEN DP.amount ELSE DP.total_amount END),0) AS paid_total
                 FROM deals_payments DP
                 WHERE DP.deal_id=" . $dealId
            );
        }
        catch (Exception $e)
        {
            return 0;
        }
        if (!empty($rows[0][0]['paid_total']))
        {
            return (float)$rows[0][0]['paid_total'];
        }
        return 0;
    }

    protected function _portalLetterDealQrUrl($projectName, $clientName, $membershipNo, $paymentLabel, $totalPaid, $cacheKey = '', $dealQrData = array())
    {
        $dealQrData = (array)$dealQrData;
        if (!empty($dealQrData['verification_url']))
        {
            $qrText = trim((string)$dealQrData['verification_url']);
        }
        elseif (!empty($dealQrData))
        {
            $mobile = isset($dealQrData['agency_mobile']) ? trim((string)$dealQrData['agency_mobile']) : '';
            $phone = isset($dealQrData['agency_phone']) ? trim((string)$dealQrData['agency_phone']) : '';
            $contactParts = array();
            if ($mobile !== '')
            {
                $contactParts[] = __('Mobile') . ': ' . $mobile;
            }
            if ($phone !== '')
            {
                $contactParts[] = __('Phone') . ': ' . $phone;
            }
            $contactLine = implode(' | ', $contactParts);
            if ($contactLine === '')
            {
                $contactLine = '-';
            }
            $qrText = implode("\n", array(
                strtoupper(__('Agency Details')),
                trim((string)$projectName),
                __('Contact') . ': ' . $contactLine,
                '',
                strtoupper(__('Deal Details')),
                __('Membership No') . ': ' . trim((string)$membershipNo),
                __('Client Name') . ': ' . trim((string)$clientName),
                __('CNIC') . ': ' . (isset($dealQrData['client_cnic']) && trim((string)$dealQrData['client_cnic']) !== '' ? trim((string)$dealQrData['client_cnic']) : '-'),
                __('Deal Status') . ': ' . (isset($dealQrData['deal_status']) && trim((string)$dealQrData['deal_status']) !== '' ? trim((string)$dealQrData['deal_status']) : '-'),
                '',
                strtoupper(__('Property Details')),
                __('Property File #') . ': ' . (isset($dealQrData['property_file']) && trim((string)$dealQrData['property_file']) !== '' ? trim((string)$dealQrData['property_file']) : '-'),
                __('Property Area') . ': ' . (isset($dealQrData['property_area']) && trim((string)$dealQrData['property_area']) !== '' ? trim((string)$dealQrData['property_area']) : '-'),
                __('Property Type') . ': ' . (isset($dealQrData['property_type']) && trim((string)$dealQrData['property_type']) !== '' ? trim((string)$dealQrData['property_type']) : '-'),
                __('Contract Type') . ': ' . (isset($dealQrData['contract_type']) && trim((string)$dealQrData['contract_type']) !== '' ? trim((string)$dealQrData['contract_type']) : '-')
            ));
        }
        else
        {
            $qrText = implode("\n", array(
                'Project: ' . trim((string)$projectName),
                'Client: ' . trim((string)$clientName),
                'Deal: ' . trim((string)$membershipNo),
                'Payment: ' . trim((string)$paymentLabel),
                'Total Paid: ' . $this->currency . CakeNumber::format((float)$totalPaid)
            ));
        }
        return 'https://api.qrserver.com/v1/create-qr-code/?size=600x600&ecc=L&qzone=2&margin=8&format=png&charset-source=UTF-8&charset-target=UTF-8&data='
            . rawurlencode($qrText)
            . '&v=' . rawurlencode((string)$cacheKey)
            . '&cb=' . rawurlencode(date('YmdHis'));
    }

    protected function _portalLetterVerificationToken($letterId, $dealId, $clientId)
    {
        return substr(Security::hash((int)$letterId.'|'.(int)$dealId.'|'.(int)$clientId.'|whitevillas-letter-verify', 'sha256', true), 0, 24);
    }

    protected function _portalLetterVerificationUrl($letter)
    {
        $letter = (array)$letter;
        $letterId = !empty($letter['id']) ? (int)$letter['id'] : 0;
        if($letterId <= 0)
        {
            return '';
        }
        $dealId = !empty($letter['deal_id']) ? (int)$letter['deal_id'] : 0;
        $clientId = !empty($letter['client_id']) ? (int)$letter['client_id'] : 0;
        $token = $this->_portalLetterVerificationToken($letterId, $dealId, $clientId);
        return Router::url(array('plugin'=>false,'admin'=>false,'controller'=>'Letterverifications','action'=>'verify',$letterId,$token), true);
    }

    protected function _portalRefundCompletionQrText($refundId, $clientName, $amountText, $dateText)
    {
        $refundId = (int)$refundId;
        $refundLabel = $refundId > 0 ? 'RFD-' . $refundId : '-';
        return implode("\n", array(
            'Deal Refund ID: ' . $refundLabel,
            'Client Name: ' . trim((string)$clientName),
            'Refund Total Deal Amount: ' . trim((string)$amountText),
            'Date: ' . trim((string)$dateText)
        ));
    }

    protected function _portalRefundCompletionQrUrl($refundId, $clientName, $amountText, $dateText, $cacheKey = '')
    {
        $qrText = $this->_portalRefundCompletionQrText($refundId, $clientName, $amountText, $dateText);
        return 'https://api.qrserver.com/v1/create-qr-code/?size=600x600&ecc=L&qzone=2&margin=8&format=png&charset-source=UTF-8&charset-target=UTF-8&data='
            . rawurlencode($qrText)
            . '&v=' . rawurlencode((string)$cacheKey)
            . '&cb=' . rawurlencode(date('YmdHis'));
    }
    protected function _portalPrimaryPaymentBankAccountForLetter($projectId)
    {
        $projectId = (int)$projectId;
        if ($projectId <= 0)
        {
            return array();
        }
        try
        {
            $this->loadModel('Admin.Ardpaymentbankaccount');
            $rows = $this->Ardpaymentbankaccount->activeRowsForProject($projectId);
            return (array)$this->Ardpaymentbankaccount->primaryRow($rows);
        }
        catch (Exception $e)
        {
            return array();
        }
    }

    protected function _portalAmountInWords($amount, $currencyWord = 'Rupees')
    {
        $amount = trim(str_replace(',', '', (string)$amount));
        if ($amount === '' || !is_numeric($amount))
        {
            return '';
        }
        $negative = ((float)$amount < 0);
        $amount = round(abs((float)$amount), 2);
        $whole = (int)floor($amount);
        $fraction = (int)round(($amount - $whole) * 100);
        if ($fraction === 100)
        {
            $whole++;
            $fraction = 0;
        }
        $words = $this->_portalIntegerToWords($whole);
        if (trim((string)$currencyWord) !== '')
        {
            $words .= ' ' . trim((string)$currencyWord);
        }
        if ($fraction > 0)
        {
            $words .= ' And ' . $this->_portalIntegerToWords($fraction) . ' Paisa';
        }
        if ($negative)
        {
            $words = 'Minus ' . $words;
        }
        return trim($words) . ' Only';
    }

    protected function _portalRomanTextToUrdu($text)
    {
        $text = trim((string)$text);
        if ($text === '')
        {
            return '';
        }
        if (preg_match('/[\x{0600}-\x{06FF}]/u', $text))
        {
            return $text;
        }
        $text = preg_replace('/\bS\s*\/\s*O\b/i', 'ولد', $text);
        $text = preg_replace('/\bD\s*\/\s*O\b/i', 'دختر', $text);
        $text = preg_replace('/\bW\s*\/\s*O\b/i', 'زوجہ', $text);
        $dictionary = $this->_portalRomanUrduDictionary();
        return preg_replace_callback('/[A-Za-z]+/', function($matches) use ($dictionary) {
            $word = (string)$matches[0];
            if (strlen($word) <= 1)
            {
                return $word;
            }
            $key = strtolower($word);
            if (isset($dictionary[$key]))
            {
                return $dictionary[$key];
            }
            return $this->_portalRomanWordToUrdu($word);
        }, $text);
    }

    protected function _portalRefundClientNameParts($name, $fatherName)
    {
        $name = trim((string)$name);
        $fatherName = trim((string)$fatherName);
        if (preg_match('/^(.*?)\s*(?:S\s*\/\s*O|D\s*\/\s*O|W\s*\/\s*O|SON\s+OF|DAUGHTER\s+OF|WIFE\s+OF)\s+(.+)$/i', $name, $matches))
        {
            $baseName = trim((string)$matches[1]);
            $relationName = trim((string)$matches[2]);
            if ($baseName !== '')
            {
                $name = $baseName;
            }
            if ($fatherName === '' && $relationName !== '')
            {
                $fatherName = $relationName;
            }
        }
        return array('name' => $name, 'fathername' => $fatherName);
    }

    protected function _portalRomanWordToUrdu($word)
    {
        $word = strtolower(trim((string)$word));
        if ($word === '')
        {
            return '';
        }
        $chunks = array(
            'sch' => 'سک', 'sh' => 'ش', 'ch' => 'چ', 'kh' => 'خ', 'gh' => 'غ',
            'ph' => 'ف', 'th' => 'تھ', 'dh' => 'دھ', 'bh' => 'بھ', 'jh' => 'جھ',
            'zh' => 'ژ', 'aa' => 'ا', 'ee' => 'ی', 'oo' => 'و', 'ou' => 'و',
            'ai' => 'ے', 'ay' => 'ے', 'ei' => 'ی', 'ie' => 'ی', 'qu' => 'ق',
            'ck' => 'ک', 'ng' => 'نگ'
        );
        $chars = array(
            'a' => 'ا', 'b' => 'ب', 'c' => 'ک', 'd' => 'د', 'e' => 'ی',
            'f' => 'ف', 'g' => 'گ', 'h' => 'ہ', 'i' => 'ی', 'j' => 'ج',
            'k' => 'ک', 'l' => 'ل', 'm' => 'م', 'n' => 'ن', 'o' => 'و',
            'p' => 'پ', 'q' => 'ق', 'r' => 'ر', 's' => 'س', 't' => 'ت',
            'u' => 'و', 'v' => 'و', 'w' => 'و', 'x' => 'کس', 'y' => 'ی',
            'z' => 'ز'
        );
        $out = '';
        $len = strlen($word);
        for ($i = 0; $i < $len;)
        {
            $matched = false;
            foreach ($chunks as $roman => $urdu)
            {
                $partLen = strlen($roman);
                if (substr($word, $i, $partLen) === $roman)
                {
                    $out .= $urdu;
                    $i += $partLen;
                    $matched = true;
                    break;
                }
            }
            if ($matched)
            {
                continue;
            }
            $char = substr($word, $i, 1);
            $out .= isset($chars[$char]) ? $chars[$char] : $char;
            $i++;
        }
        return $out;
    }

    protected function _portalRomanUrduDictionary()
    {
        return array(
            'muhammad' => 'محمد', 'mohammad' => 'محمد', 'mohammed' => 'محمد', 'mohd' => 'محمد',
            'ahmad' => 'احمد', 'ahmed' => 'احمد', 'ali' => 'علی', 'hassan' => 'حسن', 'hasan' => 'حسن',
            'hussain' => 'حسین', 'hussein' => 'حسین', 'qadeer' => 'قدیر', 'qadir' => 'قادر',
            'abdul' => 'عبدال', 'rehman' => 'رحمان', 'rahman' => 'رحمان', 'rasheed' => 'رشید', 'rashid' => 'رشید',
            'naseer' => 'نصیر', 'nasir' => 'ناصر', 'naveed' => 'نوید', 'waheed' => 'وحید', 'saeed' => 'سعید',
            'shahid' => 'شاہد', 'shahzad' => 'شہزاد', 'tariq' => 'طارق', 'nadeem' => 'ندیم', 'naeem' => 'نعیم',
            'saleem' => 'سلیم', 'jameel' => 'جمیل', 'shakeel' => 'شکیل', 'khalid' => 'خالد', 'aslam' => 'اسلم',
            'akram' => 'اکرم', 'imran' => 'عمران', 'usman' => 'عثمان', 'umar' => 'عمر', 'umer' => 'عمر',
            'farhan' => 'فرحان', 'adnan' => 'عدنان', 'rizwan' => 'رضوان', 'irfan' => 'عرفان', 'sultan' => 'سلطان',
            'nazir' => 'نذیر', 'nazeer' => 'نذیر', 'ameer' => 'امیر', 'amir' => 'عامر', 'sajjad' => 'سجاد',
            'abid' => 'عابد', 'iftikhar' => 'افتخار', 'zubair' => 'زبیر', 'fatima' => 'فاطمہ', 'sadia' => 'سعدیہ',
            'saira' => 'سائرہ', 'iqra' => 'اقرا', 'ayesha' => 'عائشہ', 'aisha' => 'عائشہ',
            'house' => 'مکان', 'home' => 'مکان', 'flat' => 'فلیٹ', 'plot' => 'پلاٹ', 'street' => 'گلی',
            'st' => 'گلی', 'road' => 'روڈ', 'rd' => 'روڈ', 'block' => 'بلاک', 'sector' => 'سیکٹر',
            'phase' => 'فیز', 'town' => 'ٹاؤن', 'colony' => 'کالونی', 'mohalla' => 'محلہ', 'mohala' => 'محلہ', 'mohlah' => 'محلہ',
            'mohalha' => 'محلہ', 'village' => 'گاؤں', 'tehsil' => 'تحصیل', 'district' => 'ضلع',
            'lahore' => 'لاہور', 'kasur' => 'قصور', 'chunian' => 'چونیاں', 'chuniyan' => 'چونیاں',
            'karachi' => 'کراچی', 'islamabad' => 'اسلام آباد', 'rawalpindi' => 'راولپنڈی', 'multan' => 'ملتان',
            'faisalabad' => 'فیصل آباد', 'gujranwala' => 'گوجرانوالہ', 'sialkot' => 'سیالکوٹ',
            'pakistan' => 'پاکستان', 'canada' => 'کینیڈا'
        );
    }

    protected function _portalIntegerToWords($number)
    {
        $number = (int)$number;
        if ($number === 0)
        {
            return 'Zero';
        }
        $scaleNames = array('', 'Thousand', 'Lakh', 'Crore', 'Arab', 'Kharab');
        $groups = array($number % 1000);
        $number = (int)floor($number / 1000);
        while ($number > 0)
        {
            $groups[] = $number % 100;
            $number = (int)floor($number / 100);
        }
        $parts = array();
        for ($i = count($groups) - 1; $i >= 0; $i--)
        {
            $groupValue = (int)$groups[$i];
            if ($groupValue === 0)
            {
                continue;
            }
            $groupWords = ($i === 0) ? $this->_portalUnderThousandToWords($groupValue) : $this->_portalUnderHundredToWords($groupValue);
            if (!empty($scaleNames[$i]))
            {
                $groupWords .= ' ' . $scaleNames[$i];
            }
            $parts[] = $groupWords;
        }
        return implode(' ', $parts);
    }

    protected function _portalUnderThousandToWords($number)
    {
        $number = (int)$number;
        $parts = array();
        if ($number >= 100)
        {
            $parts[] = $this->_portalUnderTwentyWords((int)floor($number / 100)) . ' Hundred';
            $number = $number % 100;
        }
        if ($number > 0)
        {
            $parts[] = $this->_portalUnderHundredToWords($number);
        }
        return implode(' ', $parts);
    }

    protected function _portalUnderHundredToWords($number)
    {
        $number = (int)$number;
        if ($number < 20)
        {
            return $this->_portalUnderTwentyWords($number);
        }
        $tens = array(2 => 'Twenty', 3 => 'Thirty', 4 => 'Forty', 5 => 'Fifty', 6 => 'Sixty', 7 => 'Seventy', 8 => 'Eighty', 9 => 'Ninety');
        $ten = (int)floor($number / 10);
        $unit = $number % 10;
        $words = isset($tens[$ten]) ? $tens[$ten] : '';
        if ($unit > 0)
        {
            $words .= ' ' . $this->_portalUnderTwentyWords($unit);
        }
        return trim($words);
    }

    protected function _portalUnderTwentyWords($number)
    {
        $ones = array(0 => '', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four', 5 => 'Five', 6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine', 10 => 'Ten', 11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen', 16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen', 19 => 'Nineteen');
        $number = (int)$number;
        return isset($ones[$number]) ? $ones[$number] : '';
    }

    protected function _wrapEnInline($value)
    {
        $val = trim((string)$value);
        if ($val === '' || $val === 'N/A' || $val === '-') {
            return $val;
        }
        if (strpos($val, 'class="en-inline"') !== false) {
            return $val;
        }
        return '<span dir="ltr" class="en-inline">' . $val . '</span>';
    }

    protected function _portalFormatDateValue($dateValue, $fallback = '')
    {
        $value = trim((string)$dateValue);
        if ($value === '')
        {
            return $fallback;
        }
        $timestamp = strtotime($value);
        if ($timestamp === false)
        {
            return $fallback;
        }
        return date($this->dtFormat, $timestamp);
    }

    protected function _portalBallotingSnapshotByDeal($dealId)
    {
        $dealId = (int)$dealId;
        if ($dealId <= 0)
        {
            return array();
        }
        $this->loadModel('Admin.Realestateletter');
        try
        {
            $rows = (array)$this->Realestateletter->query("SHOW TABLES LIKE 'balloting_allotments'");
            if (empty($rows))
            {
                return array();
            }
        }
        catch (Exception $e)
        {
            return array();
        }
        try
        {
            $row = (array)$this->Realestateletter->query(
                "SELECT BA.*, U.permanent_type as unit_permanent_type, U.unit_area as unit_unit_area, U.area_unit as unit_area_unit,
                        T.name as type_name, UN.name as unit_name, C.name as category_name
                 FROM balloting_allotments BA
                 LEFT JOIN balloting_units U ON U.id=BA.balloting_unit_id
                 LEFT JOIN types T ON T.id=BA.property_type_id
                 LEFT JOIN units UN ON UN.id=BA.unit_id
                 LEFT JOIN categories C ON C.id=BA.category_id
                 WHERE BA.deal_id=".$dealId." AND BA.status='Allotted'
                 ORDER BY BA.id DESC
                 LIMIT 1"
            );
        }
        catch (Exception $e)
        {
            return array();
        }
        if (empty($row[0]))
        {
            return array();
        }
        $r = $row[0];
        $ba = isset($r['BA']) ? (array)$r['BA'] : $r;
        return array(
            'allotment_no' => isset($ba['allotment_no']) ? (string)$ba['allotment_no'] : '',
            'permanent_type' => isset($ba['permanent_type']) && trim((string)$ba['permanent_type']) !== '' ? (string)$ba['permanent_type'] : (isset($r['unit_permanent_type']) ? (string)$r['unit_permanent_type'] : ''),
            'property_no' => isset($ba['property_no']) ? (string)$ba['property_no'] : '',
            'street_no' => isset($ba['street_no']) ? (string)$ba['street_no'] : '',
            'block_name' => isset($ba['block_name']) ? (string)$ba['block_name'] : '',
            'street_address' => isset($ba['street_address']) ? (string)$ba['street_address'] : '',
            'unit_area' => isset($ba['unit_area']) ? (string)$ba['unit_area'] : (isset($r['unit_unit_area']) ? (string)$r['unit_unit_area'] : ''),
            'area_unit' => isset($ba['area_unit']) ? (string)$ba['area_unit'] : (isset($r['unit_area_unit']) ? (string)$r['unit_area_unit'] : ''),
            'property_type' => isset($r['type_name']) ? (string)$r['type_name'] : '',
            'unit_name' => isset($r['unit_name']) ? (string)$r['unit_name'] : '',
            'category_name' => isset($r['category_name']) ? (string)$r['category_name'] : '',
            'allotment_date' => isset($ba['allotment_date']) ? (string)$ba['allotment_date'] : ''
        );
    }

    protected function _portalPlaceholderMap($letterRow)
    {
        $letter = isset($letterRow['Realestateletter']) ? (array)$letterRow['Realestateletter'] : array();
        $deal = isset($letterRow['Deal']) ? (array)$letterRow['Deal'] : array();
        $client = isset($letterRow['Client']) ? (array)$letterRow['Client'] : array();
        $property = isset($letterRow['Property']) ? (array)$letterRow['Property'] : array();
        $unit = isset($letterRow['Unit']) ? (array)$letterRow['Unit'] : array();
        $type = isset($letterRow['Type']) ? (array)$letterRow['Type'] : array();
        $contract = isset($letterRow['Contract']) ? (array)$letterRow['Contract'] : array();
        $project = isset($letterRow['Project']) ? (array)$letterRow['Project'] : array();
        $template = isset($letterRow['Template']) ? (array)$letterRow['Template'] : array();
        if (empty($project['id']) && !empty($property['project_id']))
        {
            $project['id'] = (int)$property['project_id'];
        }
        $portalPropertyName = isset($property['name']) && trim((string)$property['name']) !== '' ? trim((string)$property['name']) : '';
        if ($portalPropertyName === '' && !empty($ballot['property_no'])) {
            $portalPropertyName = trim((string)$ballot['property_no']);
        }
        if ($portalPropertyName === '' && !empty($property['uniq_id'])) {
            $portalPropertyName = trim((string)$property['uniq_id']);
        }
        if ($portalPropertyName === '' && !empty($deal['invoice_no'])) {
            $portalPropertyName = trim((string)$deal['invoice_no']);
        }

        $portalBlockName = isset($ballot['block_name']) ? trim((string)$ballot['block_name']) : '';
        if ($portalBlockName === '' || strtoupper($portalBlockName) === 'N/A') {
            $projNameCandidate = isset($project['name']) ? (string)$project['name'] : '';
            if (preg_match('/([A-Za-z0-9_-]+\s+Block)/i', $projNameCandidate, $matchesBlock)) {
                $portalBlockName = trim($matchesBlock[1]);
            }
        }
        $projectIdForAgency = isset($project['id']) ? (int)$project['id'] : 0;
        $agencyUserInfo = $this->_portalAgencyUserInfo($projectIdForAgency);
        $agencyLogoUrl = $this->_portalResolveAgencyLogoUrl($project);
        $agencyName = isset($project['name']) && trim((string)$project['name']) !== '' ? (string)$project['name'] : '';
        if ($agencyName === '' && !empty($agencyUserInfo['name']))
        {
            $agencyName = (string)$agencyUserInfo['name'];
        }
        if ($agencyName === '')
        {
            $agencyName = (string)$this->siteOrganization;
        }
        $agencyAddress = isset($project['address']) && trim((string)$project['address']) !== '' ? (string)$project['address'] : '';
        if ($agencyAddress === '' && !empty($agencyUserInfo['address']))
        {
            $agencyAddress = (string)$agencyUserInfo['address'];
        }
        if ($agencyAddress === '')
        {
            $agencyAddress = (string)$this->siteAddress;
        }
        $agencyPhone = '';
        foreach (array(
            isset($project['phone']) ? $project['phone'] : '',
            isset($project['mobile']) ? $project['mobile'] : '',
            isset($agencyUserInfo['phone']) ? $agencyUserInfo['phone'] : '',
            isset($agencyUserInfo['mobile']) ? $agencyUserInfo['mobile'] : ''
        ) as $phoneCandidate)
        {
            $phoneCandidate = trim((string)$phoneCandidate);
            if ($phoneCandidate !== '')
            {
                $agencyPhone = $phoneCandidate;
                break;
            }
        }
        if ($agencyPhone === '')
        {
            $agencyPhone = (string)$this->contact;
        }
        $agencyEmail = isset($project['email']) && trim((string)$project['email']) !== '' ? (string)$project['email'] : '';
        if ($agencyEmail === '' && !empty($agencyUserInfo['email']))
        {
            $agencyEmail = (string)$agencyUserInfo['email'];
        }
        if ($agencyEmail === '')
        {
            $agencyEmail = (string)$this->siteEmail;
        }
        $letterTypeCode = isset($letter['letter_type']) ? trim((string)$letter['letter_type']) : '';
        $letterTypeLabel = isset($this->_portalLetterTypeArr[$letterTypeCode]) ? (string)$this->_portalLetterTypeArr[$letterTypeCode] : $letterTypeCode;
        if (trim((string)$letterTypeLabel) === '')
        {
            $letterTypeLabel = '-';
        }
        $letterText = function($field, $fallback = '') use ($letter) {
            $value = isset($letter[$field]) ? trim((string)$letter[$field]) : '';
            return ($value !== '') ? $value : $fallback;
        };

        $letterDate = !empty($letter['letter_date']) ? $this->_portalFormatDateValue($letter['letter_date'], date($this->dtFormat)) : date($this->dtFormat);
        $propertyArea = '';
        if (!empty($property['area']))
        {
            $propertyArea = trim((string)$property['area']);
            if (!empty($unit['name']))
            {
                $propertyArea .= ' '.trim((string)$unit['name']);
            }
        }

        $profileUrl = $this->_portalResolveClientPhotoUrl($client);
        $idFrontDir = isset($client['id_front_dir']) ? trim((string)$client['id_front_dir']) : 'contact';
        if ($idFrontDir === '') { $idFrontDir = 'contact'; }
        $idBackDir = isset($client['id_back_dir']) ? trim((string)$client['id_back_dir']) : 'contact';
        if ($idBackDir === '') { $idBackDir = 'contact'; }
        $idFrontUrl = $this->_portalResolveClientDocUrl(isset($client['id_front']) ? $client['id_front'] : '', $idFrontDir, 'contact_id_front');
        $idBackUrl = $this->_portalResolveClientDocUrl(isset($client['id_back']) ? $client['id_back'] : '', $idBackDir, 'contact_id_back');

        $ballot = $this->_portalBallotingSnapshotByDeal(isset($deal['id']) ? (int)$deal['id'] : 0);
        $ballotArea = isset($ballot['unit_area']) ? trim((string)$ballot['unit_area']) : '';
        if ($ballotArea !== '' && !empty($ballot['area_unit']))
        {
            $ballotArea .= ' '.trim((string)$ballot['area_unit']);
        }
        $ballotDate = !empty($ballot['allotment_date']) ? $this->_portalFormatDateValue($ballot['allotment_date'], '') : '';
        $dealChargeCycleTypes = array(
            'M' => 'Monthly',
            'Q' => 'Quarterly',
            'H' => 'Biannual',
            'Y' => 'Yearly'
        );
        $dealChargeCount = 0;
        $dealChargeTotal = 0;
        $dealChargeTypes = '';
        $latestChargeType = '';
        $latestChargeCycleType = '';
        $latestChargeCycleCount = 0;
        $latestChargeCycleAmount = 0;
        $latestChargeTotalAmount = 0;
        $latestChargeStartDate = '';
        $dealChargeRows = array();
        $dealId = isset($deal['id']) ? (int)$deal['id'] : 0;
        if ($dealId > 0)
        {
            try
            {
                $sumRows = (array)$this->Realestateletter->query(
                    "SELECT
                        COUNT(*) AS charge_count,
                        COALESCE(SUM(`total_amount`),0) AS charge_total,
                        GROUP_CONCAT(DISTINCT TRIM(`charge_name`) ORDER BY TRIM(`charge_name`) SEPARATOR ', ') AS charge_types
                     FROM `deal_charges`
                     WHERE `deal_id`=".(int)$dealId." AND `status`=1"
                );
                if (!empty($sumRows[0][0]))
                {
                    if (isset($sumRows[0][0]['charge_count']))
                    {
                        $dealChargeCount = (int)$sumRows[0][0]['charge_count'];
                    }
                    if (isset($sumRows[0][0]['charge_total']) && $sumRows[0][0]['charge_total'] !== null)
                    {
                        $dealChargeTotal = round((float)$sumRows[0][0]['charge_total'], 2);
                    }
                    if (isset($sumRows[0][0]['charge_types']) && $sumRows[0][0]['charge_types'] !== null)
                    {
                        $dealChargeTypes = trim((string)$sumRows[0][0]['charge_types']);
                    }
                }
                $latestRows = (array)$this->Realestateletter->query(
                    "SELECT `charge_name`,`cycle_type`,`cycle_count`,`cycle_amount`,`total_amount`,`start_date`
                     FROM `deal_charges`
                     WHERE `deal_id`=".(int)$dealId." AND `status`=1
                     ORDER BY `id` DESC
                     LIMIT 1"
                );
                if (!empty($latestRows[0][0]))
                {
                    $latest = (array)$latestRows[0][0];
                    $latestChargeType = isset($latest['charge_name']) ? trim((string)$latest['charge_name']) : '';
                    $latestChargeCycleType = isset($latest['cycle_type']) ? strtoupper(trim((string)$latest['cycle_type'])) : '';
                    $latestChargeCycleCount = isset($latest['cycle_count']) ? (int)$latest['cycle_count'] : 0;
                    $latestChargeCycleAmount = isset($latest['cycle_amount']) ? round((float)$latest['cycle_amount'], 2) : 0;
                    $latestChargeTotalAmount = isset($latest['total_amount']) ? round((float)$latest['total_amount'], 2) : 0;
                    $latestChargeStartDate = isset($latest['start_date']) ? (string)$latest['start_date'] : '';
                }
            }
            catch (Exception $e)
            {
                // Keep portal letter rendering available if deal charges table is missing.
            }
            $dealChargeRows = $this->_portalActiveDealChargeRows($dealId);
            if (!empty($dealChargeRows))
            {
                $dealChargeCount = count($dealChargeRows);
                $dealChargeTotal = 0;
                $chargeNameSet = array();
                foreach ($dealChargeRows as $chargeRow)
                {
                    $rowChargeName = isset($chargeRow['charge_name']) ? trim((string)$chargeRow['charge_name']) : '';
                    $rowTotal = isset($chargeRow['total_amount']) ? (float)$chargeRow['total_amount'] : 0;
                    $dealChargeTotal += $rowTotal;
                    if ($rowChargeName !== '')
                    {
                        $chargeNameSet[$rowChargeName] = $rowChargeName;
                    }
                }
                $dealChargeTotal = round($dealChargeTotal, 2);
                $dealChargeTypes = !empty($chargeNameSet) ? implode(', ', array_values($chargeNameSet)) : $dealChargeTypes;
                $latest = end($dealChargeRows);
                if (is_array($latest))
                {
                    $latestChargeType = isset($latest['charge_name']) ? trim((string)$latest['charge_name']) : $latestChargeType;
                    $latestChargeCycleType = isset($latest['cycle_type']) ? strtoupper(trim((string)$latest['cycle_type'])) : $latestChargeCycleType;
                    $latestChargeCycleCount = isset($latest['cycle_count']) ? (int)$latest['cycle_count'] : $latestChargeCycleCount;
                    $latestChargeCycleAmount = isset($latest['cycle_amount']) ? round((float)$latest['cycle_amount'], 2) : $latestChargeCycleAmount;
                    $latestChargeTotalAmount = isset($latest['total_amount']) ? round((float)$latest['total_amount'], 2) : $latestChargeTotalAmount;
                    $latestChargeStartDate = isset($latest['start_date']) ? (string)$latest['start_date'] : $latestChargeStartDate;
                }
            }
        }
        if ($dealChargeTypes === '')
        {
            $dealChargeTypes = $latestChargeType;
        }
        if ($dealChargeTypes === '')
        {
            $dealChargeTypes = '-';
        }
        if ($latestChargeType === '')
        {
            $latestChargeType = '-';
        }
        $latestChargeCycleTypeLabel = isset($dealChargeCycleTypes[$latestChargeCycleType]) ? $dealChargeCycleTypes[$latestChargeCycleType] : $latestChargeCycleType;
        if (trim((string)$latestChargeCycleTypeLabel) === '')
        {
            $latestChargeCycleTypeLabel = '-';
        }
        $latestChargeStartDateLabel = ($latestChargeStartDate !== '') ? $this->_portalFormatDateValue($latestChargeStartDate, '') : '';
        $dealTotalAmount = isset($deal['total_amount']) ? (float)$deal['total_amount'] : 0;
        $dealBookingAmount = isset($deal['booking_amount']) ? (float)$deal['booking_amount'] : 0;
        $dealBalanceAmount = max(0, $dealTotalAmount - $dealBookingAmount);
        $dealTotalWithCharges = $dealTotalAmount + (float)$dealChargeTotal;
        $dealStatus = '';
        foreach (array(
            isset($deal['deal_status']) ? $deal['deal_status'] : '',
            isset($deal['status']) ? $deal['status'] : ''
        ) as $statusCandidate)
        {
            $statusCandidate = trim((string)$statusCandidate);
            if ($statusCandidate !== '')
            {
                $dealStatus = $statusCandidate;
                break;
            }
        }
        if ($dealStatus === '')
        {
            $dealStatus = '-';
        }
        $overdueSummary = $this->_portalDealOverdueSummaryForLetter($dealId, !empty($letter['letter_date']) ? (string)$letter['letter_date'] : date('Y-m-d'));
        $refundData = array();
        $refundId = 0;
        $refundPaidTotal = 0.0;
        $refundLastPaidDate = '';
        $refundScheduleTable = '';
        if ($dealId > 0)
        {
            try
            {
                $this->loadModel('Admin.Dealrefund');
                $this->loadModel('Admin.Dealrefundschedule');
                $refundId = isset($letter['refund_id']) ? (int)$letter['refund_id'] : 0;
                $refundRow = array();
                if ($refundId > 0)
                {
                    $refundRow = (array)$this->Dealrefund->find('first', array(
                        'conditions' => array('Dealrefund.id' => $refundId),
                        'recursive' => -1
                    ));
                }
                if (empty($refundRow['Dealrefund']['id']))
                {
                    $refundRow = (array)$this->Dealrefund->find('first', array(
                        'conditions' => array('Dealrefund.deal_id' => $dealId),
                        'order' => array('Dealrefund.id' => 'desc'),
                        'recursive' => -1
                    ));
                }
                if (!empty($refundRow['Dealrefund']['id']))
                {
                    $refundData = (array)$refundRow['Dealrefund'];
                    $refundId = (int)$refundData['id'];
                    $scheduleRows = (array)$this->Dealrefundschedule->find('all', array(
                        'conditions' => array('Dealrefundschedule.refund_id' => $refundId),
                        'order' => array('Dealrefundschedule.cycle_no' => 'asc'),
                        'recursive' => -1
                    ));
                    if (!empty($scheduleRows))
                    {
                        $scheduleHtml = '<div class="schedule-table-container"><table><thead><tr><th>Cycle</th><th>Tentative Date</th><th>Amount (PKR)</th><th>Status</th></tr></thead><tbody>';
                        foreach ($scheduleRows as $schedRow)
                        {
                            $s = isset($schedRow['Dealrefundschedule']) ? $schedRow['Dealrefundschedule'] : array();
                            $cycleNo = isset($s['cycle_no']) ? (int)$s['cycle_no'] : 0;
                            $schedDate = isset($s['scheduled_date']) ? (string)$s['scheduled_date'] : '';
                            $schedLabel = $schedDate !== '' ? $this->_portalFormatDateValue($schedDate, $schedDate) : '';
                            $schedAmount = isset($s['amount']) ? (float)$s['amount'] : 0;
                            $schedStatus = isset($s['status']) ? (string)$s['status'] : '';
                            if (strtolower(trim($schedStatus)) === 'paid')
                            {
                                $paidAmount = isset($s['paid_amount']) ? round((float)$s['paid_amount'], 2) : 0;
                                if ($paidAmount <= 0)
                                {
                                    $paidAmount = $schedAmount;
                                }
                                $refundPaidTotal = round($refundPaidTotal + $paidAmount, 2);
                                $paidDate = isset($s['paid_date']) ? trim((string)$s['paid_date']) : '';
                                if ($paidDate !== '' && $paidDate !== '0000-00-00' && ($refundLastPaidDate === '' || strcmp($paidDate, $refundLastPaidDate) > 0))
                                {
                                    $refundLastPaidDate = $paidDate;
                                }
                            }
                            $scheduleHtml .= '<tr><td>Cycle '.(int)$cycleNo.'</td><td>'.h($schedLabel).'</td><td>'.h(number_format($schedAmount, 2)).'</td><td>'.h($schedStatus).'</td></tr>';
                        }
                        $scheduleHtml .= '</tbody></table></div>';
                        $refundScheduleTable = $scheduleHtml;
                    }
                }
            }
            catch (Exception $e)
            {
                $refundData = array();
                $refundScheduleTable = '';
            }
        }
        $refundRequestDate = isset($refundData['request_date']) ? (string)$refundData['request_date'] : '';
        if ($refundRequestDate !== '' && $refundRequestDate !== '0000-00-00')
        {
            $refundRequestDate = $this->_portalFormatDateValue($refundRequestDate, date($this->dtFormat));
        }
        $refundTotalPaid = isset($refundData['total_paid']) ? round((float)$refundData['total_paid'], 2) : 0;
        $refundDeductionPercent = isset($refundData['deduction_percent']) ? round((float)$refundData['deduction_percent'], 2) : 0;
        $refundDeductionAmount = isset($refundData['deduction_amount']) ? round((float)$refundData['deduction_amount'], 2) : 0;
        $refundNet = isset($refundData['net_refund']) ? round((float)$refundData['net_refund'], 2) : 0;
        $refundCycleCount = isset($refundData['cycle_count']) ? (int)$refundData['cycle_count'] : 0;
        $refundCycleFrequency = isset($refundData['cycle_frequency']) ? (string)$refundData['cycle_frequency'] : '';
        $refundCycleAmount = ($refundCycleCount > 0) ? round($refundNet / $refundCycleCount, 2) : 0;
        if (!empty($refundData['per_cycle_amount']))
        {
            $refundCycleAmount = (float)$refundData['per_cycle_amount'];
        }
        $refundCompletionDate = $letterDate;
        if ($refundLastPaidDate !== '' && $refundLastPaidDate !== '0000-00-00')
        {
            $refundCompletionDate = $this->_portalFormatDateValue($refundLastPaidDate, $letterDate);
        }
        $refundDisplayId = $refundId > 0 ? 'RFD-' . $refundId : '';
        $refundQrAmountText = $this->currency . CakeNumber::format((float)$refundNet);
        $refundCompletionQrText = $this->_portalRefundCompletionQrText($refundId, isset($client['name']) ? (string)$client['name'] : '', $refundQrAmountText, $refundCompletionDate);
        $refundCompletionQrUrl = $this->_portalRefundCompletionQrUrl($refundId, isset($client['name']) ? (string)$client['name'] : '', $refundQrAmountText, $refundCompletionDate, ($refundId > 0 ? 'refund-' . $refundId : 'letter-' . (isset($letter['id']) ? (int)$letter['id'] : 0)));
        $refundCompletionQrImg = '<img src="' . htmlspecialchars($refundCompletionQrUrl, ENT_QUOTES, 'UTF-8') . '" alt="Deal Refund QR Code" style="display:block;width:100%;height:100%;object-fit:contain;" />';
        $firstDealPayment = $this->_portalFirstDealPaymentForLetter($dealId);
        $firstDealPaymentAmount = isset($firstDealPayment['amount']) ? (float)$firstDealPayment['amount'] : 0;
        $firstDealPaymentNames = isset($firstDealPayment['payment_names']) && trim((string)$firstDealPayment['payment_names']) !== '' ? (string)$firstDealPayment['payment_names'] : 'Booking Amount';
        $paymentLabelForQr = isset($firstDealPayment['payment_method']) ? trim((string)$firstDealPayment['payment_method']) : '';
        if ($paymentLabelForQr === '')
        {
            $paymentLabelForQr = 'Un-Paid';
        }
        if ($firstDealPaymentAmount > 0)
        {
            $paymentLabelForQr .= ' - ' . $this->currency . CakeNumber::format($firstDealPaymentAmount);
        }
        $agencyMobileForQr = '';
        foreach (array(
            isset($project['mobile']) ? $project['mobile'] : '',
            isset($agencyUserInfo['mobile']) ? $agencyUserInfo['mobile'] : ''
        ) as $mobileCandidate)
        {
            $mobileCandidate = trim((string)$mobileCandidate);
            if ($mobileCandidate !== '')
            {
                $agencyMobileForQr = $mobileCandidate;
                break;
            }
        }
        $agencyPhoneForQr = '';
        foreach (array(
            isset($project['phone']) ? $project['phone'] : '',
            isset($agencyUserInfo['phone']) ? $agencyUserInfo['phone'] : '',
            $this->contact
        ) as $phoneCandidate)
        {
            $phoneCandidate = trim((string)$phoneCandidate);
            if ($phoneCandidate !== '')
            {
                $agencyPhoneForQr = $phoneCandidate;
                break;
            }
        }
        $letterVerificationUrl = $this->_portalLetterVerificationUrl($letter);
        $letterDealQrUrl = $this->_portalLetterDealQrUrl(
            $agencyName,
            isset($client['name']) && trim((string)$client['name']) !== '' ? (string)$client['name'] : '-',
            isset($deal['invoice_no']) && trim((string)$deal['invoice_no']) !== '' ? (string)$deal['invoice_no'] : '-',
            $paymentLabelForQr,
            $this->_portalDealTotalPaidForLetter($dealId),
            ($dealId > 0 ? 'deal-' . $dealId : 'letter-' . (isset($letter['id']) ? (int)$letter['id'] : 0)),
            array(
                'agency_mobile' => $agencyMobileForQr,
                'agency_phone' => $agencyPhoneForQr,
                'client_cnic' => isset($client['pincode']) ? (string)$client['pincode'] : '-',
                'deal_status' => $dealStatus,
                'property_file' => isset($property['name']) ? (string)$property['name'] : '-',
                'property_area' => $propertyArea,
                'property_type' => isset($type['name']) ? (string)$type['name'] : '-',
                'contract_type' => isset($contract['name']) ? (string)$contract['name'] : '-',
                'verification_url' => $letterVerificationUrl
            )
        );
        $letterDealQrImg = '<img src="' . htmlspecialchars($letterDealQrUrl, ENT_QUOTES, 'UTF-8') . '" alt="Deal QR Code" style="display:block;width:100%;height:100%;object-fit:contain;" />';
        $primaryBank = $this->_portalPrimaryPaymentBankAccountForLetter($projectIdForAgency);
        $ceoSignatureUrl = $this->_portalTemplateSignatureUrl($template, 'ceo_signature', 'ceo_signature_dir');
        $chairmanSignatureUrl = $this->_portalTemplateSignatureUrl($template, 'chairman_signature', 'chairman_signature_dir');
        $chargeWorkDetails = $letterText('charge_work_details', $dealChargeTypes);
        $chargeWorkStatus = $letterText('charge_work_status', 'In Progress');
        $chargePaymentMethod = $letterText('charge_payment_method', 'Bank Transfer / Cash Receipt');
        $chargePaymentDeadline = $letterText('charge_payment_deadline', 'As per agreed schedule');
        $agreementPlace = $letterText('agreement_place', $agencyAddress !== '' ? $agencyAddress : $agencyName);
        $agencyRepresentativeName = $letterText('agency_representative_name', $agencyName);
        $agencyRepresentativeFather = $letterText('agency_representative_father', '________________');
        $agencyRepresentativeCnic = $letterText('agency_representative_cnic', '________________');
        $agencyRepresentativeDesignation = $letterText('agency_representative_designation', 'Authorized Officer');
        $clientNameDisplay = isset($client['name']) ? (string)$client['name'] : '';
        $clientFatherNameDisplay = isset($client['fathername']) ? (string)$client['fathername'] : '';
        $clientAddressDisplay = isset($client['address']) ? (string)$client['address'] : '';
        if ($letterTypeCode === 'RLT_REFUND_CLEARANCE')
        {
            $clientNameParts = $this->_portalRefundClientNameParts($clientNameDisplay, $clientFatherNameDisplay);
            $clientNameDisplay = $clientNameParts['name'];
            $clientFatherNameDisplay = $clientNameParts['fathername'];
            $clientNameDisplay = $this->_portalRomanTextToUrdu($clientNameDisplay);
            $clientFatherNameDisplay = $this->_portalRomanTextToUrdu($clientFatherNameDisplay);
            $clientAddressDisplay = $this->_portalRomanTextToUrdu($clientAddressDisplay);
        }

        $isBookingOrWelcome = in_array($letterTypeCode, array('RLT_BOOKING', 'RLT_WELCOME'), true);
        $en = function($v) use ($isBookingOrWelcome) {
            return $isBookingOrWelcome ? $v : $this->_wrapEnInline($v);
        };

        return array(
            '{{site_name}}' => (string)$this->siteName,
            '{{site_organization}}' => (string)$this->siteOrganization,
            '{{site_address}}' => (string)$this->siteAddress,
            '{{site_contact}}' => (string)$this->contact,
            '{{site_email}}' => (string)$this->siteEmail,
            '{{agency_logo_url}}' => $agencyLogoUrl,
            '{{agency_logo}}' => $this->_portalAgencyLogoImageTag($agencyLogoUrl, $agencyName),
            '{{agency_name}}' => $agencyName,
            '{{agency_address}}' => $agencyAddress,
            '{{agency_phone}}' => $agencyPhone,
            '{{agency_email}}' => $agencyEmail,
            '{{today}}' => $en(date($this->dtFormat)),
            '{{letter_date}}' => $en($letterDate),
            '{{letter_ref_no}}' => $en(isset($letter['reference_no']) ? (string)$letter['reference_no'] : ''),
            '{{letter_title}}' => isset($letter['title']) ? (string)$letter['title'] : '',
            '{{letter_type_label}}' => $letterTypeLabel,
            '{{client_name}}' => $clientNameDisplay,
            '{{client_father_name}}' => $clientFatherNameDisplay,
            '{{client_cnic}}' => $en(isset($client['pincode']) ? (string)$client['pincode'] : ''),
            '{{client_phone}}' => $en(isset($client['phone']) ? (string)$client['phone'] : ''),
            '{{client_mobile}}' => $en(isset($client['mobile']) ? (string)$client['mobile'] : ''),
            '{{client_email}}' => isset($client['email']) ? (string)$client['email'] : '',
            '{{client_address}}' => $clientAddressDisplay,
            '{{client_profile_photo_url}}' => $profileUrl,
            '{{client_id_front_image_url}}' => $idFrontUrl,
            '{{client_id_back_image_url}}' => $idBackUrl,
            '{{client_profile_photo}}' => $this->_portalContactImageTag($profileUrl, 'Client Profile Photo'),
            '{{client_id_front_image}}' => $this->_portalContactImageTag($idFrontUrl, 'Client ID Front'),
            '{{client_id_back_image}}' => $this->_portalContactImageTag($idBackUrl, 'Client ID Back'),
            '{{applicant_photo_url}}' => $profileUrl,
            '{{applicant_photo}}' => $this->_portalContactImageTag($profileUrl, 'Applicant Photo'),
            '{{nominee_name}}' => isset($deal['nominee_name']) ? (string)$deal['nominee_name'] : '',
            '{{nominee_cnic}}' => $en(isset($deal['nominee_cnic']) ? (string)$deal['nominee_cnic'] : ''),
            '{{nominee_mobile}}' => $en(isset($deal['nominee_mobile']) ? (string)$deal['nominee_mobile'] : ''),
            '{{nominee_phone}}' => $en(isset($deal['nominee_mobile']) ? (string)$deal['nominee_mobile'] : ''),
            '{{nominee_relation}}' => isset($deal['nominee_relation']) ? (string)$deal['nominee_relation'] : '',
            '{{deal_invoice_no}}' => $en(isset($deal['invoice_no']) ? (string)$deal['invoice_no'] : ''),
            '{{deal_date}}' => $en(!empty($deal['date']) ? $this->_portalFormatDateValue($deal['date'], '') : ''),
            '{{deal_status}}' => $dealStatus,
            '{{deal_total_amount}}' => $en(isset($deal['total_amount']) ? $this->currency.CakeNumber::format((float)$deal['total_amount']) : ''),
            '{{deal_total_amount_words}}' => $this->_portalAmountInWords($dealTotalAmount),
            '{{deal_total_amount_words_urdu}}' => $this->_portalAmountInWords($dealTotalAmount),
            '{{deal_total_with_charges}}' => $en($this->currency.CakeNumber::format((float)$dealTotalWithCharges)),
            '{{deal_total_with_charges_words_urdu}}' => $this->_portalAmountInWords($dealTotalWithCharges),
            '{{deal_discount}}' => $en(isset($deal['discount']) ? $this->currency.CakeNumber::format((float)$deal['discount']) : ''),
            '{{deal_booking_amount}}' => $en(isset($deal['booking_amount']) ? $this->currency.CakeNumber::format((float)$deal['booking_amount']) : ''),
            '{{deal_balance_amount}}' => $en($this->currency.CakeNumber::format((float)$dealBalanceAmount)),
            '{{deal_balance_amount_words_urdu}}' => $this->_portalAmountInWords($dealBalanceAmount),
            '{{deal_booking_receipt_no}}' => $en(isset($firstDealPayment['receipt_no']) ? (string)$firstDealPayment['receipt_no'] : ''),
            '{{deal_booking_payment_method}}' => isset($firstDealPayment['payment_method']) ? (string)$firstDealPayment['payment_method'] : '',
            '{{deal_booking_payment_remarks}}' => isset($firstDealPayment['remarks']) ? (string)$firstDealPayment['remarks'] : '',
            '{{deal_booking_payment_date}}' => $en(isset($firstDealPayment['payment_date']) ? (string)$firstDealPayment['payment_date'] : ''),
            '{{deal_booking_payment_amount}}' => $en($this->currency.CakeNumber::format($firstDealPaymentAmount)),
            '{{deal_booking_payment_amount_words}}' => $this->_portalAmountInWords($firstDealPaymentAmount),
            '{{deal_booking_payment_amount_words_urdu}}' => $this->_portalAmountInWords($firstDealPaymentAmount),
            '{{deal_booking_payment_against}}' => $firstDealPaymentNames,
            '{{first_deal_payment_receipt_no}}' => $en(isset($firstDealPayment['receipt_no']) ? (string)$firstDealPayment['receipt_no'] : ''),
            '{{first_deal_payment_method}}' => isset($firstDealPayment['payment_method']) ? (string)$firstDealPayment['payment_method'] : '',
            '{{first_deal_payment_amount}}' => $en($this->currency.CakeNumber::format($firstDealPaymentAmount)),
            '{{first_deal_payment_amount_words}}' => $this->_portalAmountInWords($firstDealPaymentAmount),
            '{{first_deal_payment_amount_words_urdu}}' => $this->_portalAmountInWords($firstDealPaymentAmount),
            '{{first_deal_payment_against}}' => $firstDealPaymentNames,
            '{{letter_deal_qr_url}}' => $letterDealQrUrl,
            '{{deal_letter_qr_url}}' => $letterDealQrUrl,
            '{{letter_deal_qr}}' => $letterDealQrImg,
            '{{deal_letter_qr}}' => $letterDealQrImg,
            '{{letter_verification_url}}' => $letterVerificationUrl,
            '{{letter_verification_qr_url}}' => $letterDealQrUrl,
            '{{letter_verification_qr}}' => $letterDealQrImg,
            '{{overdue_installments_label}}' => isset($overdueSummary['label']) ? (string)$overdueSummary['label'] : '-',
            '{{overdue_installments_count}}' => $en(isset($overdueSummary['count']) ? (string)$overdueSummary['count'] : '0'),
            '{{overdue_amount}}' => $en(isset($overdueSummary['amount_text']) ? (string)$overdueSummary['amount_text'] : $this->currency.CakeNumber::format(0)),
            '{{overdue_amount_words}}' => isset($overdueSummary['amount_words']) ? (string)$overdueSummary['amount_words'] : $this->_portalAmountInWords(0),
            '{{final_notice_deadline_days}}' => '7',
            '{{bank_name}}' => isset($primaryBank['bank_name']) ? (string)$primaryBank['bank_name'] : '',
            '{{bank_account_title}}' => isset($primaryBank['account_title']) ? (string)$primaryBank['account_title'] : '',
            '{{bank_account_no}}' => $en(isset($primaryBank['account_no']) ? (string)$primaryBank['account_no'] : ''),
            '{{bank_iban}}' => $en(isset($primaryBank['iban']) ? (string)$primaryBank['iban'] : ''),
            '{{bank_branch}}' => isset($primaryBank['branch']) ? (string)$primaryBank['branch'] : '',
            '{{bank_swift_code}}' => isset($primaryBank['swift_code']) ? (string)$primaryBank['swift_code'] : '',
            '{{agency_bank_name}}' => isset($primaryBank['bank_name']) ? (string)$primaryBank['bank_name'] : '',
            '{{agency_account_title}}' => isset($primaryBank['account_title']) ? (string)$primaryBank['account_title'] : '',
            '{{agency_account_no}}' => $en(isset($primaryBank['account_no']) ? (string)$primaryBank['account_no'] : ''),
            '{{agency_iban}}' => $en(isset($primaryBank['iban']) ? (string)$primaryBank['iban'] : ''),
            '{{ceo_signature_url}}' => $ceoSignatureUrl,
            '{{chairman_signature_url}}' => $chairmanSignatureUrl,
            '{{ceo_signature}}' => $this->_portalSignatureImageTag($ceoSignatureUrl, 'CEO Signature', 'CEO Office'),
            '{{chairman_signature}}' => $this->_portalSignatureImageTag($chairmanSignatureUrl, 'Chairman Signature', 'Board Chairman'),
            '{{deal_charges_total}}' => $en($this->currency.CakeNumber::format((float)$dealChargeTotal)),
            '{{deal_charges_total_words}}' => $this->_portalAmountInWords($dealChargeTotal),
            '{{deal_charges_total_words_urdu}}' => $this->_portalAmountInWords($dealChargeTotal),
            '{{deal_charges_count}}' => $en((string)$dealChargeCount),
            '{{deal_charges_types}}' => $dealChargeTypes,
            '{{deal_charges_types_urdu}}' => $dealChargeTypes,
            '{{deal_charges_table}}' => $this->_portalDealChargesTableHtml($dealChargeRows),
            '{{deal_charge_type}}' => $latestChargeType,
            '{{deal_charge_type_urdu}}' => $latestChargeType,
            '{{deal_charge_cycle_type}}' => $latestChargeCycleTypeLabel,
            '{{deal_charge_cycle_count}}' => $en((string)$latestChargeCycleCount),
            '{{deal_charge_cycle_amount}}' => $en($this->currency.CakeNumber::format((float)$latestChargeCycleAmount)),
            '{{deal_charge_total_amount}}' => $en($this->currency.CakeNumber::format((float)$latestChargeTotalAmount)),
            '{{deal_charge_total_amount_words_urdu}}' => $this->_portalAmountInWords($latestChargeTotalAmount),
            '{{deal_charge_start_date}}' => $en($latestChargeStartDateLabel),
            '{{charge_work_details}}' => $chargeWorkDetails,
            '{{charge_work_status}}' => $chargeWorkStatus,
            '{{charge_work_status_urdu}}' => $chargeWorkStatus,
            '{{charge_payment_method}}' => $chargePaymentMethod,
            '{{charge_payment_deadline}}' => $chargePaymentDeadline,
            '{{agreement_place}}' => $agreementPlace,
            '{{agency_representative_name}}' => $agencyRepresentativeName,
            '{{agency_representative_father}}' => $agencyRepresentativeFather,
            '{{agency_representative_cnic}}' => $en($agencyRepresentativeCnic),
            '{{agency_representative_designation}}' => $agencyRepresentativeDesignation,
            '{{agreement_payment_plan}}' => $letterText('agreement_payment_plan', 'As per approved payment plan'),
            '{{agreement_penalty_rate}}' => $letterText('agreement_penalty_rate', 'As per policy'),
            '{{agreement_possession_months}}' => $letterText('agreement_possession_months', 'As per project schedule'),
            '{{agreement_extra_work_amount}}' => $en($letterText('agreement_extra_work_amount', $this->currency.CakeNumber::format((float)$dealChargeTotal))),
            '{{agreement_extra_work_schedule}}' => $letterText('agreement_extra_work_schedule', 'As per agreed schedule'),
            '{{agreement_cancellation_deduction_rate}}' => $letterText('agreement_cancellation_deduction_rate', 'As per policy'),
            '{{agreement_refund_period}}' => $letterText('agreement_refund_period', 'As per policy'),
            '{{agreement_court_jurisdiction}}' => $letterText('agreement_court_jurisdiction', 'Lahore'),
            '{{agreement_property_block}}' => $letterText('agreement_property_block', isset($ballot['block_name']) ? (string)$ballot['block_name'] : ''),
            '{{agreement_property_unit_no}}' => $letterText('agreement_property_unit_no', isset($ballot['property_no']) ? (string)$ballot['property_no'] : ''),
            '{{agreement_property_floor}}' => $letterText('agreement_property_floor', ''),
            '{{agreement_property_covered_area}}' => $letterText('agreement_property_covered_area', $ballotArea !== '' ? $ballotArea : $propertyArea),
            '{{agreement_property_location}}' => $letterText('agreement_property_location', $agencyAddress),
            '{{property_agreement_category}}' => $letterText('property_agreement_category', isset($ballot['category_name']) ? (string)$ballot['category_name'] : ''),
            '{{property_agreement_terms}}' => $letterText('property_agreement_terms', 'As per company policy'),
            '{{witness_one_name}}' => $letterText('witness_one_name', '________________'),
            '{{witness_one_cnic}}' => $letterText('witness_one_cnic', '________________'),
            '{{witness_one_contact}}' => $letterText('witness_one_contact', '________________'),
            '{{witness_one_address}}' => $letterText('witness_one_address', '________________'),
            '{{witness_two_name}}' => $letterText('witness_two_name', '________________'),
            '{{witness_two_cnic}}' => $letterText('witness_two_cnic', '________________'),
            '{{witness_two_contact}}' => $letterText('witness_two_contact', '________________'),
            '{{witness_two_address}}' => $letterText('witness_two_address', '________________'),
            '{{refund_id}}' => $refundId > 0 ? (string)$refundId : '',
            '{{deal_refund_id}}' => $refundDisplayId,
            '{{refund_request_date}}' => $refundRequestDate,
            '{{refund_total_paid}}' => $this->currency.CakeNumber::format((float)$refundTotalPaid),
            '{{refund_deduction_percent}}' => number_format((float)$refundDeductionPercent, 2) . '%',
            '{{refund_deduction_amount}}' => $this->currency.CakeNumber::format((float)$refundDeductionAmount),
            '{{refund_net_refund}}' => $this->currency.CakeNumber::format((float)$refundNet),
            '{{refund_net_refund_words}}' => $this->_portalAmountInWords($refundNet),
            '{{refund_net_refund_words_urdu}}' => $this->_portalAmountInWords($refundNet),
            '{{refund_paid_total}}' => $this->currency.CakeNumber::format((float)$refundPaidTotal),
            '{{refund_completion_date}}' => $refundCompletionDate,
            '{{refund_cycle_count}}' => (string)$refundCycleCount,
            '{{refund_cycle_frequency}}' => $refundCycleFrequency,
            '{{refund_cycle_amount}}' => $this->currency.CakeNumber::format((float)$refundCycleAmount),
            '{{refund_account_title}}' => isset($refundData['account_title']) ? (string)$refundData['account_title'] : '',
            '{{refund_account_no}}' => isset($refundData['account_no']) ? (string)$refundData['account_no'] : '',
            '{{refund_bank_name}}' => isset($refundData['bank_name']) ? (string)$refundData['bank_name'] : '',
            '{{refund_bank_branch}}' => isset($refundData['bank_branch_city']) ? (string)$refundData['bank_branch_city'] : '',
            '{{refund_reason}}' => isset($refundData['reason']) ? (string)$refundData['reason'] : '',
            '{{refund_schedule_table}}' => $refundScheduleTable,
            '{{refund_passport_no}}' => isset($refundData['passport_no']) ? (string)$refundData['passport_no'] : '',
            '{{refund_completion_qr_url}}' => $refundCompletionQrUrl,
            '{{refund_completion_qr}}' => $refundCompletionQrImg,
            '{{refund_completion_qr_text}}' => $refundCompletionQrText,
            '{{refund_completion_qr_text_json}}' => json_encode($refundCompletionQrText),
            '{{property_name}}' => $portalPropertyName,
            '{{Property_Name}}' => $portalPropertyName,
            '{{PROPERTY_NAME}}' => $portalPropertyName,
            '{{PropertyName}}' => $portalPropertyName,
            '{{property_title}}' => $portalPropertyName,
            '{{property_file}}' => $portalPropertyName,
            '{{property_area}}' => $propertyArea,
            '{{property_unit}}' => isset($unit['name']) ? (string)$unit['name'] : '',
            '{{property_price}}' => isset($property['price']) ? $this->currency.CakeNumber::format((float)$property['price']) : '',
            '{{property_type}}' => isset($type['name']) ? (string)$type['name'] : '',
            '{{property_contract}}' => isset($contract['name']) ? (string)$contract['name'] : '',
            '{{project_name}}' => isset($project['name']) ? (string)$project['name'] : '',
            '{{ballot_allotment_no}}' => isset($ballot['allotment_no']) ? (string)$ballot['allotment_no'] : '',
            '{{ballot_permanent_type}}' => isset($ballot['permanent_type']) ? (string)$ballot['permanent_type'] : '',
            '{{ballot_property_no}}' => isset($ballot['property_no']) ? (string)$ballot['property_no'] : '',
            '{{ballot_street_no}}' => isset($ballot['street_no']) ? (string)$ballot['street_no'] : '',
            '{{ballot_block_name}}' => $portalBlockName !== '' ? $portalBlockName : 'N/A',
            '{{block_name}}' => $portalBlockName !== '' ? $portalBlockName : 'N/A',
            '{{Block_Name}}' => $portalBlockName !== '' ? $portalBlockName : 'N/A',
            '{{BLOCK_NAME}}' => $portalBlockName !== '' ? $portalBlockName : 'N/A',
            '{{ballot_street_address}}' => isset($ballot['street_address']) ? (string)$ballot['street_address'] : '',
            '{{ballot_unit_area}}' => isset($ballot['unit_area']) ? (string)$ballot['unit_area'] : '',
            '{{ballot_area_unit}}' => isset($ballot['area_unit']) ? (string)$ballot['area_unit'] : '',
            '{{ballot_area_full}}' => $ballotArea,
            '{{ballot_property_type}}' => isset($ballot['property_type']) ? (string)$ballot['property_type'] : '',
            '{{ballot_unit_name}}' => isset($ballot['unit_name']) ? (string)$ballot['unit_name'] : '',
            '{{ballot_category_name}}' => isset($ballot['category_name']) ? (string)$ballot['category_name'] : '',
            '{{ballot_allotment_date}}' => $ballotDate,
            '{{allotment_no}}' => isset($ballot['allotment_no']) ? (string)$ballot['allotment_no'] : '',
            '{{street_address}}' => isset($ballot['street_address']) ? (string)$ballot['street_address'] : '',
            '{{client_id}}' => isset($deal['client_id']) ? (string)$deal['client_id'] : (isset($client['id']) ? (string)$client['id'] : '')
        );
    }

    protected function _portalRenderLetterBody($letterRow)
    {
        $description = '';
        if (isset($letterRow['Template']['description']) && !$this->_portalLetterBodyIsBlank($letterRow['Template']['description']))
        {
            $description = (string)$letterRow['Template']['description'];
        }
        elseif (isset($letterRow['Realestateletter']['description']))
        {
            $description = (string)$letterRow['Realestateletter']['description'];
        }
        $rendered = strtr($description, $this->_portalPlaceholderMap($letterRow));
        $rendered = preg_replace('/\{\{\s*[^}]+\s*\}\}/', 'N/A', $rendered);
        // Ensure applicant/client profile photo displays in full without aggressive cropping
        $rendered = preg_replace_callback('/<img\b([^>]*\b(?:id=["\']client-photo["\']|alt=["\'](?:Applicant|Client)\s+Photo["\'])[^>]*)>/i', function($m) {
            $attrs = $m[1];
            $attrs = preg_replace('/\bobject-cover\b/i', 'object-contain bg-white', $attrs);
            if (stripos($attrs, 'object-fit') === false) {
                if (stripos($attrs, 'style=') !== false) {
                    $attrs = preg_replace('/style=["\']([^"\']*)["\']/i', 'style="$1;object-fit:contain !important;object-position:center !important;"', $attrs);
                } else {
                    $attrs .= ' style="object-fit:contain !important;object-position:center !important;"';
                }
            }
            return '<img' . $attrs . '>';
        }, $rendered);
        // Strip obsolete centered "Booking Form" title between Property Name and ID No if present
        $rendered = preg_replace('/<div[^>]*class=["\'][^"\']*text-center[^"\']*["\'][^>]*style=["\'][^"\']*font-size:\s*17px[^"\']*["\'][^>]*>\s*Booking Form\s*<\/div>\s*/i', '', $rendered);
        $letterType = isset($letterRow['Realestateletter']['letter_type']) ? trim((string)$letterRow['Realestateletter']['letter_type']) : '';
        if ($letterType === 'RLT_DEAL_AGREEMENT')
        {
            $rendered = $this->_portalPrepareDealAgreementPrintHtml($rendered);
        }
        elseif ($letterType === 'RLT_REFUND_CLEARANCE')
        {
            $rendered = $this->_portalPrepareRefundCompletionPrintHtml($rendered);
        }
        return $this->_portalPreserveFullHtmlPreviewPrint($rendered);
    }

    protected function _portalLetterBodyIsBlank($html)
    {
        $text = (string)$html;
        $text = preg_replace('/<br\s*\/?>/i', '', $text);
        $text = str_ireplace(array('&nbsp;', '&#160;'), ' ', $text);
        $text = trim(strip_tags($text));
        return $text === '';
    }

    protected function _portalPrepareDealAgreementPrintHtml($html)
    {
        $html = (string)$html;
        if (!$this->_portalIsFullHtmlDocument($html))
        {
            return $html;
        }
        if (strpos($html, 'wv-deal-agreement-print-fix') !== false)
        {
            return $html;
        }
        $fixCss = '<style id="wv-deal-agreement-print-fix">'
            . '@font-face { font-family: "Jameel Noori Nastaleeq"; src: url("/fonts/JameelNooriNastaleeq.ttf") format("truetype"), url("/whitevillas/fonts/JameelNooriNastaleeq.ttf") format("truetype"), url("../fonts/JameelNooriNastaleeq.ttf") format("truetype"); font-weight: normal; font-style: normal; font-display: swap; }'
            . '@font-face { font-family: "Jameel Noori Nastaleeq Kasheeda"; src: url("/fonts/JameelNooriNastaleeqKasheeda.ttf") format("truetype"), url("/whitevillas/fonts/JameelNooriNastaleeqKasheeda.ttf") format("truetype"), url("../fonts/JameelNooriNastaleeqKasheeda.ttf") format("truetype"); font-weight: normal; font-style: normal; font-display: swap; }'
            . '*, *:before, *:after { box-sizing: border-box !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; color-adjust: exact !important; }'
            . '@page { size: Legal portrait; margin: 12mm 14mm !important; }'
            . '.page { width: 100% !important; max-width: 100% !important; height: auto !important; min-height: 0 !important; margin: 0 auto !important; padding: 0 !important; background: #fff !important; border: none !important; border-radius: 0 !important; overflow: visible !important; display: block !important; }'
            . '.page, .page * { word-spacing: 0 !important; letter-spacing: 0 !important; }'
            . 'body, .page, p, .clause, .note-box, .amount-box, .sign-box { font-family: "Jameel Noori Nastaleeq", "Noto Nastaliq Urdu", sans-serif !important; }'
            . 'p { font-size: 16px !important; line-height: 1.65 !important; margin: 0 0 6px !important; text-align: justify !important; text-justify: inter-word !important; }'
            . '.clause { font-size: 16px !important; line-height: 1.65 !important; margin: 0 0 5px !important; text-align: justify !important; text-justify: inter-word !important; }'
            . '.section-title { font-family: "Jameel Noori Nastaleeq Kasheeda", "Jameel Noori Nastaleeq", "Noto Nastaliq Urdu", serif !important; font-size: 17px !important; line-height: 1.35 !important; margin: 10px 0 5px !important; font-weight: bold !important; text-align: center !important; }'
            . '.en-inline { display: inline-block !important; direction: ltr !important; unicode-bidi: embed !important; font-family: Arial, Helvetica, sans-serif !important; font-size: 13.5px !important; line-height: 1 !important; border-bottom: 1px solid #000000 !important; padding: 0 4px 1px 4px !important; margin: 0 2px !important; text-align: center !important; vertical-align: baseline !important; font-weight: bold !important; color: #000000 !important; }'
            . '.field { padding: 0 2px !important; line-height: 1.2 !important; word-spacing: 0 !important; letter-spacing: 0 !important; }'
            . '.amount-box { font-size: 16px !important; line-height: 1.65 !important; margin: 8px 0 !important; padding: 6px 12px !important; border: 1px solid #000 !important; }'
            . '.note-box { font-size: 15px !important; line-height: 1.6 !important; margin: 6px 0 !important; padding: 6px 10px !important; text-align: justify !important; text-justify: inter-word !important; border: 1px solid #000 !important; }'
            . '.sign-area, .witness-area { gap: 14px !important; margin-top: 14px !important; page-break-inside: avoid !important; break-inside: avoid !important; }'
            . '.sign-box { font-size: 14px !important; line-height: 1.5 !important; min-height: 110px !important; padding: 8px 10px !important; border: 1px solid #000 !important; page-break-inside: avoid !important; break-inside: avoid !important; }'
            . '.thumb-box { height: 42px !important; margin-top: 6px !important; font-size: 12px !important; border: 1px dashed #000 !important; }'
            . '.page-body { flex: 1 1 auto !important; padding-bottom: 8mm !important; }'
            . '.footer { position: relative !important; left: auto !important; right: auto !important; bottom: auto !important; margin-top: auto !important; }'
            . '@media print { @page { size: Legal portrait; margin: 12mm 14mm !important; }'
            . 'html, body { width: auto !important; margin: 0 !important; padding: 0 !important; background: #fff !important; }'
            . '.page { width: 100% !important; height: auto !important; min-height: 0 !important; margin: 0 auto !important; padding: 0 !important; background: #fff !important; overflow: visible !important; page-break-after: always !important; page-break-inside: auto !important; box-shadow: none !important; border: none !important; border-radius: 0 !important; display: block !important; }'
            . '.page:last-of-type { page-break-after: auto !important; }'
            . '.watermark { color: rgba(0,0,0,.02) !important; border-color: rgba(0,0,0,.02) !important; } }'
            . '</style>';
        if (stripos($html, '</head>') !== false)
        {
            return str_ireplace('</head>', $fixCss . '</head>', $html);
        }
        return $fixCss . $html;
    }

    protected function _portalPrepareRefundCompletionPrintHtml($html)
    {
        $html = (string)$html;
        if (!$this->_portalIsFullHtmlDocument($html))
        {
            return $html;
        }
        if (strpos($html, 'wv-refund-clearance-print-fix') !== false)
        {
            return $html;
        }
        $fontStack = '"Jameel Noori Nastaleeq", "Noto Nastaliq Urdu", sans-serif';
        $fixCss = '<style id="wv-refund-clearance-print-fix">'
            . '@font-face { font-family: "Jameel Noori Nastaleeq"; src: url("/fonts/JameelNooriNastaleeq.ttf") format("truetype"), url("/whitevillas/fonts/JameelNooriNastaleeq.ttf") format("truetype"), url("../fonts/JameelNooriNastaleeq.ttf") format("truetype"); font-weight: normal; font-style: normal; font-display: swap; }'
            . '@font-face { font-family: "Jameel Noori Nastaleeq Kasheeda"; src: url("/fonts/JameelNooriNastaleeqKasheeda.ttf") format("truetype"), url("/whitevillas/fonts/JameelNooriNastaleeqKasheeda.ttf") format("truetype"), url("../fonts/JameelNooriNastaleeqKasheeda.ttf") format("truetype"); font-weight: normal; font-style: normal; font-display: swap; }'
            . '*, *:before, *:after { box-sizing: border-box !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; color-adjust: exact !important; }'
            . '@page { size: Legal portrait; margin: 12mm 14mm !important; }'
            . 'html, body { background: #fff !important; color: #000 !important; font-family: ' . $fontStack . ' !important; font-size: 16px !important; line-height: 1.65 !important; direction: rtl !important; }'
            . '.legal-page-canvas { width: 100% !important; max-width: 100% !important; min-height: 0 !important; height: auto !important; overflow: visible !important; background: #fff !important; color: #000 !important; box-sizing: border-box !important; padding: 0 !important; }'
            . '.stamp-top-gap { height: 3.65in !important; min-height: 0 !important; flex: 0 0 auto !important; }'
            . '.legal-frame { border-right: 3px double #000000 !important; border-left: 1px solid #cccccc !important; padding-right: 1.45rem !important; padding-left: .85rem !important; }'
            . '.urdu-legal, .legal-frame, .signature-block { font-family: ' . $fontStack . ' !important; letter-spacing: 0 !important; }'
            . '.urdu-legal, #urdu-paragraphs p { font-size: 16px !important; line-height: 1.65 !important; word-spacing: 0 !important; text-align: justify !important; text-justify: inter-word !important; margin-top: 6px !important; margin-bottom: 0 !important; }'
            . '.en-inline { display: inline-block !important; direction: ltr !important; unicode-bidi: embed !important; font-family: Arial, Helvetica, sans-serif !important; font-size: 13.5px !important; line-height: 1 !important; border-bottom: 1px solid #000000 !important; padding: 0 4px 1px 4px !important; margin: 0 2px !important; text-align: center !important; vertical-align: baseline !important; font-weight: bold !important; color: #000000 !important; }'
            . '.signature-block { display: block !important; margin-top: 14px !important; padding-top: 10px !important; border-top: 1px solid #000000 !important; page-break-inside: avoid !important; break-inside: avoid !important; color: #000000 !important; }'
            . '.signature-block .grid { display: grid !important; }'
            . '.signature-block .grid-cols-2 { grid-template-columns: repeat(2, minmax(0, 1fr)) !important; }'
            . '.signature-block .grid-cols-3 { grid-template-columns: repeat(3, minmax(0, 1fr)) !important; }'
            . '.signature-block .gap-8 { gap: 1.15rem !important; }'
            . '.signature-block .gap-2 { gap: .35rem !important; }'
            . '.signature-block .text-center { text-align: center !important; }'
            . '.signature-block .h-10 { height: 34px !important; }'
            . '.signature-block .h-8 { height: 27px !important; }'
            . '.signature-block .border-b { border-bottom: 1px dashed #000000 !important; }'
            . '.signature-block .border-t { border-top: 1px solid #000000 !important; }'
            . '.signature-block .w-3\/4 { width: 75% !important; }'
            . '.signature-block .w-11\/12 { width: 91.666667% !important; }'
            . '.signature-block .mx-auto { margin-left: auto !important; margin-right: auto !important; }'
            . '.signature-block .flex { display: flex !important; }'
            . '.signature-block .flex-col { flex-direction: column !important; }'
            . '.signature-block .items-center { align-items: center !important; }'
            . '.signature-block .justify-center { justify-content: center !important; }'
            . '.signature-block .justify-end { justify-content: flex-end !important; }'
            . '.signature-block p { margin: 2px 0 !important; line-height: 1.4 !important; font-size: 14px !important; }'
            . '.qr-container-fixed { width: 1.55in !important; height: 1.55in !important; display: flex !important; align-items: center !important; justify-content: center !important; background: #fff !important; padding: .06in !important; box-sizing: border-box !important; border: 1px solid #000000 !important; }'
            . '#ashtam-qrcode { width: 100% !important; height: 100% !important; display: flex !important; align-items: center !important; justify-content: center !important; }'
            . '#ashtam-qrcode img { display: block !important; width: 100% !important; height: 100% !important; max-width: 100% !important; max-height: 100% !important; object-fit: contain !important; }'
            . '@media print { @page { size: Legal portrait; margin: 12mm 14mm !important; }'
            . 'html, body { width: auto !important; min-height: 0 !important; margin: 0 !important; padding: 0 !important; background: #fff !important; color: #000 !important; }'
            . '.no-print { display: none !important; }'
            . '.print-container, main { width: 100% !important; max-width: 100% !important; margin: 0 !important; padding: 0 !important; background: #fff !important; box-shadow: none !important; overflow: visible !important; }'
            . '.legal-page-canvas { width: 100% !important; min-height: 0 !important; height: auto !important; margin: 0 !important; padding: 0 !important; box-shadow: none !important; border: 0 !important; border-radius: 0 !important; }'
            . '.stamp-top-gap { height: 3.65in !important; }'
            . '.signature-block { page-break-inside: avoid !important; break-inside: avoid !important; }'
            . '* { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; color-adjust: exact !important; } }'
            . '</style>';
        if (stripos($html, '</head>') !== false)
        {
            return str_ireplace('</head>', $fixCss . '</head>', $html);
        }
        return $fixCss . $html;
    }

    protected function _portalPreserveFullHtmlPreviewPrint($html)
    {
        $html = (string)$html;
        if (!$this->_portalIsFullHtmlDocument($html))
        {
            return $html;
        }
        if (strpos($html, 'wv-letter-preview-print-match') !== false)
        {
            return $html;
        }
        $fixCss = '<style id="wv-letter-preview-print-match">'
            . '@media print{'
            . '*{-webkit-print-color-adjust:exact!important;print-color-adjust:exact!important;}'
            . '.page{border:1px solid #e2e8f0!important;border-radius:4px!important;background:#fff!important;box-shadow:none!important;}'
            . '.container{width:198mm!important;max-width:100%!important;margin:0 auto!important;padding:15px 18px!important;box-sizing:border-box!important;border:1px solid #e5e5e5!important;border-top:6px solid #8c1d1d!important;background:#fff!important;box-shadow:none!important;}'
            . '.ashtam-page{border:1px solid #111!important;background:#fff!important;box-shadow:none!important;}'
            . '.form-container{box-shadow:none!important;}'
            . '}'
            . '</style>';
        if (stripos($html, '</head>') !== false)
        {
            return str_ireplace('</head>', $fixCss . '</head>', $html);
        }
        return $fixCss . $html;
    }

    protected function _portalIsFullHtmlDocument($html)
    {
        $html = trim((string)$html);
        if ($html === '')
        {
            return false;
        }
        $prefix = strtolower(substr($html, 0, 600));
        if (strpos($prefix, '<!doctype html') !== false || strpos($prefix, '<html') !== false)
        {
            return true;
        }
        if (strpos($prefix, '<head') !== false && strpos($prefix, '<body') !== false)
        {
            return true;
        }
        return false;
    }

    public function calendar_ics($id = null)
    {
        $this->autoRender = false;
        $id = (int)$id;
        if ($id <= 0) {
            throw new NotFoundException(__('Invalid Deal'));
        }

        $this->loadModel('Admin.Deal');
        $this->loadModel('Admin.PlansPayment');
        $this->loadModel('Admin.DealsPayment');
        $this->loadModel('Admin.InstallmentSyncSetting');

        $deal = $this->Deal->find('first', array(
            'joins' => array(
                array('table' => 'properties', 'alias' => 'Property', 'type' => 'LEFT', 'conditions' => array('Deal.property_id=Property.id')),
                array('table' => 'projects', 'alias' => 'Project', 'type' => 'LEFT', 'conditions' => array('Property.project_id=Project.id')),
                array('table' => 'units', 'alias' => 'Unit', 'type' => 'LEFT', 'conditions' => array('Property.unit_id=Unit.id')),
                array('table' => 'clients', 'alias' => 'Client', 'type' => 'LEFT', 'conditions' => array('Deal.client_id=Client.id'))
            ),
            'fields' => array('Deal.*', 'Property.*', 'Project.*', 'Unit.*', 'Client.*'),
            'conditions' => array(
                'Deal.id' => $id,
                'Deal.client_id' => $this->userId,
                $this->_dealVisibilityCondition('Deal')
            ),
            'recursive' => -1
        ));

        if (empty($deal)) {
            throw new NotFoundException(__('Deal not found or access denied.'));
        }

        $projectId = !empty($deal['Project']['id']) ? (int)$deal['Project']['id'] : 0;
        $settings = $this->InstallmentSyncSetting->getSettings($projectId);

        if (isset($settings['calendar_sync_enabled']) && !(int)$settings['calendar_sync_enabled']) {
            $this->Session->setFlash(__('Calendar Sync is currently disabled by administrator.'), 'flash', array('alert' => 'warning'));
            return $this->redirect(array('action' => 'index'));
        }

        $planRows = $this->PlansPayment->find('all', array(
            'conditions' => array('PlansPayment.deal_id' => $id),
            'order' => array('PlansPayment.date' => 'ASC', 'PlansPayment.id' => 'ASC'),
            'recursive' => -1
        ));

        $paidRows = $this->DealsPayment->find('all', array(
            'fields' => array('DealsPayment.plans_payment_id', 'SUM(DealsPayment.emi) AS paid_total'),
            'conditions' => array('DealsPayment.deal_id' => $id, 'DealsPayment.plans_payment_id >' => 0),
            'group' => array('DealsPayment.plans_payment_id'),
            'recursive' => -1
        ));
        $paidMap = array();
        foreach ((array)$paidRows as $pr) {
            $pid = (int)$pr['DealsPayment']['plans_payment_id'];
            $paidMap[$pid] = isset($pr[0]['paid_total']) ? (float)$pr[0]['paid_total'] : 0;
        }

        $agencyName = !empty($deal['Project']['name']) ? $deal['Project']['name'] : $this->siteOrganization;
        $propName = !empty($deal['Property']['name']) ? $deal['Property']['name'] : 'White Villas Property';
        $invoiceNo = !empty($deal['Deal']['invoice_no']) ? $deal['Deal']['invoice_no'] : 'WV-'.$id;
        $clientName = !empty($deal['Client']['name']) ? $deal['Client']['name'] : 'Valued Resident';

        $alarmDays = array(7, 3, 1);
        if (!empty($settings['calendar_reminder_days'])) {
            $parsedAlarms = array_map('intval', explode(',', $settings['calendar_reminder_days']));
            $alarmDays = array_values(array_filter($parsedAlarms, function($d) { return $d > 0; }));
        }

        $alarmTime = !empty($settings['calendar_alarm_time']) ? trim($settings['calendar_alarm_time']) : '09:00';
        $timeParts = explode(':', $alarmTime);
        $alarmHour = isset($timeParts[0]) ? str_pad(trim($timeParts[0]), 2, '0', STR_PAD_LEFT) : '09';
        $alarmMin = isset($timeParts[1]) ? str_pad(trim($timeParts[1]), 2, '0', STR_PAD_LEFT) : '00';

        $eol = "\r\n";
        $ics = "BEGIN:VCALENDAR" . $eol;
        $ics .= "VERSION:2.0" . $eol;
        $ics .= "PRODID:-//White Villas//Installment Schedule//EN" . $eol;
        $ics .= "CALSCALE:GREGORIAN" . $eol;
        $ics .= "METHOD:PUBLISH" . $eol;
        $ics .= "X-WR-CALNAME:" . $this->_escapeIcs("Installments: {$propName} ({$invoiceNo})") . $eol;
        $ics .= "X-WR-TIMEZONE:Asia/Karachi" . $eol;

        $dtStamp = gmdate('Ymd\THis\Z');

        foreach ($planRows as $row) {
            $pId = (int)$row['PlansPayment']['id'];
            $pName = !empty($row['PlansPayment']['name']) ? $row['PlansPayment']['name'] : 'Installment';
            $pAmt = isset($row['PlansPayment']['amount']) ? (float)$row['PlansPayment']['amount'] : 0;
            $pPaid = isset($paidMap[$pId]) ? (float)$paidMap[$pId] : 0;
            $pPending = max(0, round($pAmt - $pPaid, 2));
            $dueDateStr = !empty($row['PlansPayment']['date']) ? $row['PlansPayment']['date'] : null;

            if (empty($dueDateStr) || $pPending <= 0) {
                continue;
            }

            $dueDateTime = strtotime($dueDateStr . ' ' . $alarmHour . ':' . $alarmMin . ':00');
            $dtStart = date('Ymd\T', $dueDateTime) . $alarmHour . $alarmMin . '00';
            $dtEnd = date('Ymd\T', $dueDateTime + 3600) . $alarmHour . $alarmMin . '00';
            $uid = "wv-plan-{$pId}-deal-{$id}-" . date('Ymd', $dueDateTime) . "@whitevillas.com.pk";

            $summary = "Installment Due: {$pName} - Rs. " . number_format($pPending) . " ({$propName})";
            $description = "Installment: {$pName}\\n"
                . "Property: {$propName}\\n"
                . "Deal / Membership: {$invoiceNo}\\n"
                . "Client: {$clientName}\\n"
                . "Amount Due: Rs. " . number_format($pPending) . "\\n"
                . "Scheduled Due Date: " . date('d-M-Y', $dueDateTime) . "\\n"
                . "Project: {$agencyName}\\n\\n"
                . "Please make payment before due date to avoid late surcharges.";

            $location = $agencyName;

            $ics .= "BEGIN:VEVENT" . $eol;
            $ics .= "UID:{$uid}" . $eol;
            $ics .= "DTSTAMP:{$dtStamp}" . $eol;
            $ics .= "DTSTART:{$dtStart}" . $eol;
            $ics .= "DTEND:{$dtEnd}" . $eol;
            $ics .= "SUMMARY:" . $this->_escapeIcs($summary) . $eol;
            $ics .= "DESCRIPTION:" . $this->_escapeIcs($description) . $eol;
            $ics .= "LOCATION:" . $this->_escapeIcs($location) . $eol;
            $ics .= "STATUS:CONFIRMED" . $eol;

            foreach ($alarmDays as $daysBefore) {
                $ics .= "BEGIN:VALARM" . $eol;
                $ics .= "TRIGGER:-P{$daysBefore}D" . $eol;
                $ics .= "ACTION:DISPLAY" . $eol;
                $ics .= "DESCRIPTION:" . $this->_escapeIcs("Reminder: {$pName} Due in {$daysBefore} day(s) for {$propName}") . $eol;
                $ics .= "END:VALARM" . $eol;
            }

            $ics .= "END:VEVENT" . $eol;
        }

        $ics .= "END:VCALENDAR" . $eol;

        $cleanFileName = 'installments_' . preg_replace('/[^a-zA-Z0-9_\-]/', '_', $invoiceNo) . '.ics';
        $this->response->type('text/calendar');
        $this->response->header('Content-Disposition', 'attachment; filename="' . $cleanFileName . '"');
        $this->response->body($ics);
        return $this->response;
    }

    public function early_discount_data($id = null)
    {
        $this->autoRender = false;
        $this->response->type('json');
        $id = (int)$id;

        if ($id <= 0) {
            $this->response->body(json_encode(array('status' => 'error', 'message' => __('Invalid Deal ID.'))));
            return $this->response;
        }

        $this->loadModel('Admin.Deal');
        $this->loadModel('Admin.PlansPayment');
        $this->loadModel('Admin.DealsPayment');
        $this->loadModel('Admin.InstallmentSyncSetting');

        $deal = $this->Deal->find('first', array(
            'joins' => array(
                array('table' => 'properties', 'alias' => 'Property', 'type' => 'LEFT', 'conditions' => array('Deal.property_id=Property.id')),
                array('table' => 'projects', 'alias' => 'Project', 'type' => 'LEFT', 'conditions' => array('Property.project_id=Project.id')),
                array('table' => 'units', 'alias' => 'Unit', 'type' => 'LEFT', 'conditions' => array('Property.unit_id=Unit.id'))
            ),
            'fields' => array('Deal.*', 'Property.*', 'Project.*', 'Unit.*'),
            'conditions' => array(
                'Deal.id' => $id,
                'Deal.client_id' => $this->userId,
                $this->_dealVisibilityCondition('Deal')
            ),
            'recursive' => -1
        ));

        if (empty($deal)) {
            $this->response->body(json_encode(array('status' => 'error', 'message' => __('Deal not found or access denied.'))));
            return $this->response;
        }

        $projectId = !empty($deal['Project']['id']) ? (int)$deal['Project']['id'] : 0;
        $settings = $this->InstallmentSyncSetting->getSettings($projectId);

        $planRows = $this->PlansPayment->find('all', array(
            'conditions' => array('PlansPayment.deal_id' => $id),
            'order' => array('PlansPayment.date' => 'ASC', 'PlansPayment.id' => 'ASC'),
            'recursive' => -1
        ));

        $paidRows = $this->DealsPayment->find('all', array(
            'fields' => array('DealsPayment.plans_payment_id', 'SUM(DealsPayment.emi) AS paid_total'),
            'conditions' => array('DealsPayment.deal_id' => $id, 'DealsPayment.plans_payment_id >' => 0),
            'group' => array('DealsPayment.plans_payment_id'),
            'recursive' => -1
        ));
        $paidMap = array();
        foreach ((array)$paidRows as $pr) {
            $pid = (int)$pr['DealsPayment']['plans_payment_id'];
            $paidMap[$pid] = isset($pr[0]['paid_total']) ? (float)$pr[0]['paid_total'] : 0;
        }

        $pendingInstallments = array();
        $totalPendingBalance = 0;
        $totalOverdueAmount = 0;
        $totalOverdueCount = 0;
        $totalUpcomingAmount = 0;
        $totalUpcomingCount = 0;
        $today = date('Y-m-d');

        foreach ($planRows as $row) {
            $pId = (int)$row['PlansPayment']['id'];
            $pName = !empty($row['PlansPayment']['name']) ? $row['PlansPayment']['name'] : 'Installment';
            $pAmt = isset($row['PlansPayment']['amount']) ? (float)$row['PlansPayment']['amount'] : 0;
            $pPaid = isset($paidMap[$pId]) ? (float)$paidMap[$pId] : 0;
            $pPending = max(0, round($pAmt - $pPaid, 2));
            $dueDateStr = !empty($row['PlansPayment']['date']) ? $row['PlansPayment']['date'] : null;

            if ($pPending <= 0) {
                continue;
            }

            $totalPendingBalance += $pPending;
            $isOverdue = false;
            $overdueDays = 0;
            $monthsAhead = 0;

            if (!empty($dueDateStr)) {
                $d1 = new DateTime($today);
                $d2 = new DateTime($dueDateStr);
                if ($d2 < $d1) {
                    $isOverdue = true;
                    $diff = $d2->diff($d1);
                    $overdueDays = (int)$diff->days;
                    $totalOverdueAmount += $pPending;
                    $totalOverdueCount++;
                } else {
                    $diff = $d1->diff($d2);
                    $monthsAhead = ($diff->y * 12) + $diff->m + ($diff->d > 10 ? 1 : 0);
                    $totalUpcomingAmount += $pPending;
                    $totalUpcomingCount++;
                }
            } else {
                $totalUpcomingAmount += $pPending;
                $totalUpcomingCount++;
            }

            $pendingInstallments[] = array(
                'id' => $pId,
                'name' => $pName,
                'total_amount' => $pAmt,
                'paid_amount' => $pPaid,
                'pending_amount' => $pPending,
                'due_date' => $dueDateStr,
                'due_date_formatted' => !empty($dueDateStr) ? date('d-M-Y', strtotime($dueDateStr)) : 'N/A',
                'is_overdue' => $isOverdue,
                'overdue_days' => $overdueDays,
                'months_ahead' => max(0, $monthsAhead)
            );
        }

        $payload = array(
            'status' => 'success',
            'deal' => array(
                'id' => $id,
                'invoice_no' => !empty($deal['Deal']['invoice_no']) ? $deal['Deal']['invoice_no'] : 'WV-'.$id,
                'property_name' => !empty($deal['Property']['name']) ? $deal['Property']['name'] : 'Property',
                'total_amount' => isset($deal['Deal']['total_amount']) ? (float)$deal['Deal']['total_amount'] : 0,
                'total_pending_balance' => round($totalPendingBalance, 2),
                'total_overdue_amount' => round($totalOverdueAmount, 2),
                'total_overdue_count' => $totalOverdueCount,
                'total_upcoming_amount' => round($totalUpcomingAmount, 2),
                'total_upcoming_count' => $totalUpcomingCount
            ),
            'settings' => array(
                'calculator_enabled' => !empty($settings['early_payment_calculator_enabled']) ? 1 : 0,
                'discount_mode' => !empty($settings['early_payment_discount_mode']) ? $settings['early_payment_discount_mode'] : 'percentage',
                'discount_percent' => isset($settings['early_payment_discount_percent']) ? (float)$settings['early_payment_discount_percent'] : 5.00,
                'annual_rate' => isset($settings['early_payment_annual_rate']) ? (float)$settings['early_payment_annual_rate'] : 8.00,
                'min_installments' => isset($settings['early_payment_min_installments']) ? (int)$settings['early_payment_min_installments'] : 3,
                'require_all_overdue' => isset($settings['require_all_overdue']) ? (int)$settings['require_all_overdue'] : 1,
                'allow_overdue_discount_package' => isset($settings['allow_overdue_discount_package']) ? (int)$settings['allow_overdue_discount_package'] : 1,
                'overdue_special_discount_percent' => isset($settings['overdue_special_discount_percent']) ? (float)$settings['overdue_special_discount_percent'] : 0.00,
                'terms_en' => !empty($settings['early_payment_terms_en']) ? $settings['early_payment_terms_en'] : '',
                'terms_ur' => !empty($settings['early_payment_terms_ur']) ? $settings['early_payment_terms_ur'] : ''
            ),
            'installments' => $pendingInstallments
        );

        $this->response->body(json_encode($payload));
        return $this->response;
    }

    protected function _escapeIcs($text)
    {
        $text = str_replace(array("\\", ";", ","), array("\\\\", "\\;", "\\,"), (string)$text);
        return $text;
    }
}

