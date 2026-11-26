<?php
App::uses('CakeTime', 'Utility');
App::uses('CakeNumber', 'Utility');
class DashboardsController extends AppController
{
    protected $_portalVisibleDealStatuses = array('Active','Blocked');
    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->authenticate();
        $this->_syncPortalClientSession();
        if(empty($this->userValue['Client']['id']))
        {
            $this->Session->setFlash(__('Please login again to view your dashboard.'),'flash',array('alert'=>'danger'));
            $this->redirect(array('controller'=>'Clients','action'=>'login'));
            exit();
        }
        $this->userId=$this->userValue['Client']['id'];
    }
    protected function _syncPortalClientSession()
    {
        $frontUser = (array)$this->Session->read('frontUser');
        if(empty($frontUser['Client']) && !empty($frontUser['Contact']) && is_array($frontUser['Contact']))
        {
            $frontUser['Client'] = $frontUser['Contact'];
        }
        if(empty($frontUser['Client']['id']))
        {
            $frontImpersonation = (array)$this->Session->read('Impersonation.front');
            if(!empty($frontImpersonation['target_contact_id']))
            {
                $frontUser['Client']['id'] = (int)$frontImpersonation['target_contact_id'];
            }
        }
        if(empty($frontUser['Client']['id']))
        {
            $this->userValue = $frontUser;
            return;
        }
        $this->loadModel('MyProfile');
        $clientRow = $this->MyProfile->find('first',array(
            'conditions'=>array('MyProfile.id'=>(int)$frontUser['Client']['id']),
            'recursive'=>-1
        ));
        if(!empty($clientRow['MyProfile']['id']))
        {
            $frontUser['Client'] = array_merge((array)$frontUser['Client'], (array)$clientRow['MyProfile']);
            $this->Session->write('frontUser', $frontUser);
        }
        $this->userValue = $frontUser;
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
    public function index()
    {
        $this->loadModel('MyDeal');
        $this->loadModel('MyPayment');
        $this->loadModel('PlansPayment');
        $this->loadModel('DealsPayment');
        $currentDateTime=CakeTime::format('Y-m-d',CakeTime::convert(time(),$this->siteTimezone));
        $this->set('portalClient', isset($this->userValue['Client']) ? (array)$this->userValue['Client'] : array());
        $this->set('frontClientPhotoUrl', $this->_frontResolveClientPhotoUrl(isset($this->userValue['Client']) ? (array)$this->userValue['Client'] : array()));
        $this->set('isFrontImpersonation', $this->Session->check('Impersonation.front'));

        $dashboardSummary = array(
            'deal_count' => 0,
            'paid_amount' => 0,
            'payment_count' => 0,
            'due_amount' => 0,
            'due_deal_count' => 0,
            'overdue_amount' => 0,
            'overdue_deal_count' => 0,
            'overdue_installment_count' => 0
        );

        $dealRows = $this->MyDeal->find('all', array(
            'fields' => array('MyDeal.id', 'MyDeal.total_amount'),
            'conditions' => array('MyDeal.client_id' => $this->userId, $this->_dealVisibilityCondition('MyDeal')),
            'recursive' => -1
        ));

        $dealTotals = array();
        foreach((array)$dealRows as $dealRow)
        {
            if(empty($dealRow['MyDeal']['id']))
            {
                continue;
            }
            $dealId = (int)$dealRow['MyDeal']['id'];
            $dealTotals[$dealId] = isset($dealRow['MyDeal']['total_amount']) ? (float)$dealRow['MyDeal']['total_amount'] : 0;
        }
        $dealIds = array_keys($dealTotals);
        $dashboardSummary['deal_count'] = count($dealIds);

        if(!empty($dealIds))
        {
            $paidByDeal = array();
            $paidRows = $this->MyPayment->find('all', array(
                'fields' => array(
                    'MyPayment.deal_id',
                    'SUM(MyPayment.total_amount) AS paid_total',
                    'COUNT(MyPayment.id) AS payment_count'
                ),
                'conditions' => array('MyPayment.deal_id' => $dealIds),
                'group' => array('MyPayment.deal_id'),
                'recursive' => -1
            ));
            foreach((array)$paidRows as $paidRow)
            {
                $dealId = isset($paidRow['MyPayment']['deal_id']) ? (int)$paidRow['MyPayment']['deal_id'] : 0;
                if($dealId <= 0)
                {
                    continue;
                }
                $paidTotal = isset($paidRow[0]['paid_total']) ? (float)$paidRow[0]['paid_total'] : 0;
                $paymentCount = isset($paidRow[0]['payment_count']) ? (int)$paidRow[0]['payment_count'] : 0;
                $paidByDeal[$dealId] = $paidTotal;
                $dashboardSummary['paid_amount'] += $paidTotal;
                $dashboardSummary['payment_count'] += $paymentCount;
            }

            foreach($dealTotals as $dealId => $dealTotalAmount)
            {
                $paidAmount = isset($paidByDeal[$dealId]) ? (float)$paidByDeal[$dealId] : 0;
                $pendingAmount = round((float)$dealTotalAmount - (float)$paidAmount, 2);
                if($pendingAmount > 0)
                {
                    $dashboardSummary['due_amount'] += $pendingAmount;
                    $dashboardSummary['due_deal_count']++;
                }
            }

            $paidByPlan = array();
            $planPaidRows = $this->MyPayment->find('all', array(
                'fields' => array('MyPayment.plans_payment_id', 'SUM(MyPayment.emi) AS paid_total'),
                'conditions' => array(
                    'MyPayment.deal_id' => $dealIds,
                    'MyPayment.plans_payment_id >' => 0
                ),
                'group' => array('MyPayment.plans_payment_id'),
                'recursive' => -1
            ));
            foreach((array)$planPaidRows as $planPaidRow)
            {
                $planId = isset($planPaidRow['MyPayment']['plans_payment_id']) ? (int)$planPaidRow['MyPayment']['plans_payment_id'] : 0;
                if($planId <= 0)
                {
                    continue;
                }
                $paidByPlan[$planId] = isset($planPaidRow[0]['paid_total']) ? (float)$planPaidRow[0]['paid_total'] : 0;
            }

            $overdueDealTracker = array();
            $planRows = $this->PlansPayment->find('all', array(
                'fields' => array('PlansPayment.id', 'PlansPayment.deal_id', 'PlansPayment.amount', 'PlansPayment.date'),
                'conditions' => array('PlansPayment.deal_id' => $dealIds),
                'order' => array('PlansPayment.id' => 'asc'),
                'recursive' => -1
            ));
            foreach((array)$planRows as $planRow)
            {
                if(empty($planRow['PlansPayment']['id']) || empty($planRow['PlansPayment']['deal_id']))
                {
                    continue;
                }
                $planId = (int)$planRow['PlansPayment']['id'];
                $dealId = (int)$planRow['PlansPayment']['deal_id'];
                $planAmount = isset($planRow['PlansPayment']['amount']) ? (float)$planRow['PlansPayment']['amount'] : 0;
                $paidAmount = isset($paidByPlan[$planId]) ? (float)$paidByPlan[$planId] : 0;
                $pendingAmount = round($planAmount - $paidAmount, 2);
                $dueDate = !empty($planRow['PlansPayment']['date']) ? $planRow['PlansPayment']['date'] : null;
                if($pendingAmount > 0 && $dueDate && $dueDate <= $currentDateTime)
                {
                    $dashboardSummary['overdue_amount'] += $pendingAmount;
                    $dashboardSummary['overdue_installment_count']++;
                    $overdueDealTracker[$dealId] = 1;
                }
            }
            $dashboardSummary['overdue_deal_count'] = count($overdueDealTracker);
        }

        $this->set('dashboardSummary', $dashboardSummary);
        $this->set('rentalDashboardSummary', $this->_rentalDashboardForClient((int)$this->userId, $currentDateTime));
        $runningRefundNotice = $this->_runningRefundNoticeForClient((int)$this->userId);
        $this->set('runningRefundNotice', $runningRefundNotice);

        $utilityDashboardSummary = array('has_bills' => false);
        $frontBillingAccess = $this->_getFrontClientBillingAccess((int)$this->userId, isset($this->userValue['Client']) ? $this->userValue['Client'] : array());
        if(!empty($frontBillingAccess['has_access']))
        {
            try
            {
                $bClientId = (int)$frontBillingAccess['billing_client_id'];
                $BillingBill = ClassRegistry::init('BillingManager.BillingBill');
                $bills = (array)$BillingBill->find('all', array(
                    'conditions' => array('BillingBill.billing_client_id' => $bClientId),
                    'order' => array('BillingBill.id' => 'DESC'),
                    'recursive' => -1
                ));
                $totBilled = 0.0; $totPaid = 0.0; $totBal = 0.0; $unpaidCount = 0;
                $latestBill = !empty($bills[0]['BillingBill']) ? $bills[0]['BillingBill'] : null;
                foreach($bills as $bRow)
                {
                    $b = $bRow['BillingBill'];
                    $totBilled += (float)$b['total_amount'];
                    $totPaid += (float)$b['paid_amount'];
                    $totBal += (float)$b['balance_amount'];
                    if(strtolower(trim((string)$b['payment_status'])) !== 'paid')
                    {
                        $unpaidCount++;
                    }
                }
                $utilityDashboardSummary = array(
                    'has_bills' => true,
                    'bills_count' => count($bills),
                    'unpaid_count' => $unpaidCount,
                    'total_billed' => $totBilled,
                    'total_paid' => $totPaid,
                    'balance_outstanding' => $totBal,
                    'latest_bill' => $latestBill
                );
            }
            catch(Exception $e)
            {
            }
        }
        $this->set('utilityDashboardSummary', $utilityDashboardSummary);
        
        $dealsPayment = array();
        $dealsPaymentSections = array();
        if(!empty($dealIds))
        {
            $dealsPayment=$this->DealsPayment->find('all',array(
                'fields'=>array(
                    'DealsPayment.*',
                    'Paymenttype.name',
                    'PlansPayment.name',
                    'Deal.id',
                    'Deal.invoice_no',
                    'Deal.total_amount',
                    'Property.name'
                ),
                'joins'=>array(
                    array('table'=>'paymenttypes','alias'=>'Paymenttype','type'=>'LEFT','conditions'=>array('DealsPayment.paymenttype_id=Paymenttype.id')),
                    array('table'=>'plans_payments','alias'=>'PlansPayment','type'=>'LEFT','conditions'=>array('DealsPayment.plans_payment_id=PlansPayment.id')),
                    array('table'=>'deals','alias'=>'Deal','type'=>'INNER','conditions'=>array('DealsPayment.deal_id=Deal.id')),
                    array('table'=>'properties','alias'=>'Property','type'=>'LEFT','conditions'=>array('Deal.property_id=Property.id'))
                ),
                'conditions'=>array('Deal.client_id'=>$this->userId,$this->_dealVisibilityCondition('Deal')),
                'order'=>array('Deal.id'=>'desc','DealsPayment.payment_date'=>'desc','DealsPayment.id'=>'desc')
            ));
            foreach((array)$dealsPayment as $paymentRow)
            {
                $dealId = !empty($paymentRow['DealsPayment']['deal_id']) ? (int)$paymentRow['DealsPayment']['deal_id'] : 0;
                if($dealId<=0)
                {
                    continue;
                }
                if(empty($dealsPaymentSections[$dealId]))
                {
                    $dealsPaymentSections[$dealId] = array(
                        'Deal'=>array(
                            'id'=>$dealId,
                            'invoice_no'=>!empty($paymentRow['Deal']['invoice_no']) ? $paymentRow['Deal']['invoice_no'] : '',
                            'total_amount'=>isset($paymentRow['Deal']['total_amount']) ? $paymentRow['Deal']['total_amount'] : 0
                        ),
                        'Property'=>array(
                            'name'=>!empty($paymentRow['Property']['name']) ? $paymentRow['Property']['name'] : ''
                        ),
                        'receipt_count'=>0,
                        'received_total'=>0,
                        'payments'=>array()
                    );
                }
                $rowId = !empty($paymentRow['DealsPayment']['id']) ? (int)$paymentRow['DealsPayment']['id'] : 0;
                $receiptNoKey = !empty($paymentRow['DealsPayment']['receipt_no']) ? trim((string)$paymentRow['DealsPayment']['receipt_no']) : '';
                if($receiptNoKey === '')
                {
                    $receiptNoKey = 'ROW-'.$rowId;
                }
                if(empty($dealsPaymentSections[$dealId]['payments'][$receiptNoKey]))
                {
                    $dealsPaymentSections[$dealId]['payments'][$receiptNoKey] = array(
                        'receipt_no'=>$receiptNoKey,
                        'first_row'=>$paymentRow,
                        'first_id'=>$rowId,
                        'ids'=>array(),
                        'paid_amount'=>0,
                        'late_fees'=>0,
                        'installment_late_amount'=>0,
                        'payment_names'=>array()
                    );
                    $dealsPaymentSections[$dealId]['receipt_count']++;
                }
                $rowPaidAmount = isset($paymentRow['DealsPayment']['emi']) ? (float)$paymentRow['DealsPayment']['emi'] : (isset($paymentRow['DealsPayment']['amount']) ? (float)$paymentRow['DealsPayment']['amount'] : 0);
                $rowLateFees = isset($paymentRow['DealsPayment']['extra_payment']) ? (float)$paymentRow['DealsPayment']['extra_payment'] : 0;
                $dealsPaymentSections[$dealId]['payments'][$receiptNoKey]['ids'][] = $rowId;
                $dealsPaymentSections[$dealId]['payments'][$receiptNoKey]['paid_amount'] += $rowPaidAmount;
                $dealsPaymentSections[$dealId]['payments'][$receiptNoKey]['late_fees'] += $rowLateFees;
                $dealsPaymentSections[$dealId]['received_total'] += $rowPaidAmount;
                $rowPaymentName = !empty($paymentRow['PlansPayment']['name']) ? trim((string)$paymentRow['PlansPayment']['name']) : '';
                if($rowPaymentName === '' && !empty($paymentRow['DealsPayment']['name']))
                {
                    $rowPaymentName = trim((string)$paymentRow['DealsPayment']['name']);
                }
                if($rowPaymentName !== '' && !in_array($rowPaymentName, $dealsPaymentSections[$dealId]['payments'][$receiptNoKey]['payment_names'], true))
                {
                    $dealsPaymentSections[$dealId]['payments'][$receiptNoKey]['payment_names'][] = $rowPaymentName;
                }
            }
            foreach($dealsPaymentSections as $sectionDealId=>$sectionData)
            {
                foreach((array)$sectionData['payments'] as $receiptKey=>$receiptGroup)
                {
                    $paidAmount = isset($receiptGroup['paid_amount']) ? (float)$receiptGroup['paid_amount'] : 0;
                    $lateFees = isset($receiptGroup['late_fees']) ? (float)$receiptGroup['late_fees'] : 0;
                    $dealsPaymentSections[$sectionDealId]['payments'][$receiptKey]['paid_amount'] = round($paidAmount,2);
                    $dealsPaymentSections[$sectionDealId]['payments'][$receiptKey]['late_fees'] = round($lateFees,2);
                    $dealsPaymentSections[$sectionDealId]['payments'][$receiptKey]['installment_late_amount'] = round($paidAmount+$lateFees,2);
                }
                $dealsPaymentSections[$sectionDealId]['payments'] = array_values((array)$dealsPaymentSections[$sectionDealId]['payments']);
                $dealsPaymentSections[$sectionDealId]['received_total'] = round((float)$dealsPaymentSections[$sectionDealId]['received_total'],2);
            }
        }
        $this->set('dealsPayment',$dealsPayment);
        $this->set('dealsPaymentSections',$dealsPaymentSections);
        $this->set('title_for_layout', __('Dashboard'));
        $this->set('isClientPortal', true);
    }

    protected function _rentalDashboardForClient($clientId, $currentDate)
    {
        $clientId = (int)$clientId;
        if($clientId <= 0)
        {
            return array();
        }

        try
        {
            $this->loadModel('Admin.DealRental');
            $this->loadModel('Admin.DealRentalSchedule');
            $this->loadModel('Admin.DealRentalMaintenance');
            $rentals = (array)$this->DealRental->find('all', array(
                'fields' => array('DealRental.id', 'DealRental.start_date', 'DealRental.end_date'),
                'conditions' => array('DealRental.client_id' => $clientId, 'DealRental.client_portal_access' => 'Active'),
                'order' => array('DealRental.start_date' => 'asc', 'DealRental.id' => 'asc'),
                'recursive' => -1
            ));
            if(empty($rentals))
            {
                return array();
            }

            $rentalIds = array();
            $summary = array(
                'rental_count' => 0,
                'start_date' => '',
                'end_date' => '',
                'scheduled' => 0,
                'paid' => 0,
                'upcoming' => 0,
                'overdue' => 0,
                'owner_charges' => 0,
                'net_received' => 0
            );
            foreach($rentals as $rentalRow)
            {
                $rental = !empty($rentalRow['DealRental']) ? (array)$rentalRow['DealRental'] : array();
                if(empty($rental['id']))
                {
                    continue;
                }
                $rentalIds[] = (int)$rental['id'];
                if(!empty($rental['start_date']) && ($summary['start_date'] === '' || $rental['start_date'] < $summary['start_date']))
                {
                    $summary['start_date'] = $rental['start_date'];
                }
                if(!empty($rental['end_date']) && ($summary['end_date'] === '' || $rental['end_date'] > $summary['end_date']))
                {
                    $summary['end_date'] = $rental['end_date'];
                }
            }
            if(empty($rentalIds))
            {
                return array();
            }
            $summary['rental_count'] = count($rentalIds);

            $schedules = (array)$this->DealRentalSchedule->find('all', array(
                'fields' => array('DealRentalSchedule.rent_amount', 'DealRentalSchedule.paid_amount', 'DealRentalSchedule.balance_amount', 'DealRentalSchedule.due_date'),
                'conditions' => array(
                    'DealRentalSchedule.deal_rental_id' => $rentalIds,
                    'DealRentalSchedule.client_id' => $clientId
                ),
                'recursive' => -1
            ));
            foreach($schedules as $scheduleRow)
            {
                $schedule = !empty($scheduleRow['DealRentalSchedule']) ? (array)$scheduleRow['DealRentalSchedule'] : array();
                $paid = isset($schedule['paid_amount']) ? (float)$schedule['paid_amount'] : 0;
                $balance = isset($schedule['balance_amount']) ? (float)$schedule['balance_amount'] : 0;
                $summary['scheduled'] += isset($schedule['rent_amount']) ? (float)$schedule['rent_amount'] : 0;
                $summary['paid'] += $paid;
                if($balance > 0.009 && !empty($schedule['due_date']) && $schedule['due_date'] < $currentDate)
                {
                    $summary['overdue'] += $balance;
                }
                elseif($balance > 0.009)
                {
                    $summary['upcoming'] += $balance;
                }
            }

            $maintenanceRows = (array)$this->DealRentalMaintenance->find('all', array(
                'fields' => array('DealRentalMaintenance.owner_charge_amount'),
                'conditions' => array(
                    'DealRentalMaintenance.deal_rental_id' => $rentalIds,
                    'DealRentalMaintenance.client_id' => $clientId
                ),
                'recursive' => -1
            ));
            foreach($maintenanceRows as $maintenanceRow)
            {
                if(isset($maintenanceRow['DealRentalMaintenance']['owner_charge_amount']))
                {
                    $summary['owner_charges'] += (float)$maintenanceRow['DealRentalMaintenance']['owner_charge_amount'];
                }
            }
            $summary['net_received'] = $summary['paid'] - $summary['owner_charges'];
            foreach(array('scheduled', 'paid', 'upcoming', 'overdue', 'owner_charges', 'net_received') as $amountKey)
            {
                $summary[$amountKey] = round((float)$summary[$amountKey], 2);
            }
            return $summary;
        }
        catch(Exception $e)
        {
            return array();
        }
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
}
