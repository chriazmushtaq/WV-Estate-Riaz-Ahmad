<?php
class MyPaymentsController extends AppController
{
    public $helpers = array('Html', 'Form', 'Session', 'Time', 'Number', 'NumToWord', 'Js', 'Paginator');
    public $uses = array('MyPayment', 'Admin.DealsPayment', 'Admin.PlansPayment');
    protected $_portalVisibleDealStatuses = array('Active','Blocked');
    public function beforeFilter()
    {
        parent::beforeFilter();
        $action = !empty($this->request->params['action']) ? strtolower($this->request->params['action']) : '';
        if ($action === 'statement') {
            if ($this->Session->check('frontUser') || $this->Session->check('Client')) {
                $this->authenticate();
                $this->userId = !empty($this->userValue['Client']['id']) ? (int)$this->userValue['Client']['id'] : 0;
            }
            return;
        }
        $this->authenticate();
        $this->userId = !empty($this->userValue['Client']['id']) ? (int)$this->userValue['Client']['id'] : 0;
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
    protected function _getReceiptSummaryData($dealId,$dealTotalAmount=0)
    {
        $dealTotalAmount = round((float)$dealTotalAmount,2);
        $paidRow = $this->MyPayment->find('first',array(
            'fields'=>array('SUM(MyPayment.total_amount) AS paid_total'),
            'conditions'=>array('MyPayment.deal_id'=>$dealId),
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
        $planPaidRows = $this->MyPayment->find('all',array(
            'fields'=>array('MyPayment.plans_payment_id','SUM(MyPayment.emi) AS paid_total'),
            'conditions'=>array('MyPayment.deal_id'=>$dealId),
            'group'=>array('MyPayment.plans_payment_id'),
            'recursive'=>-1
        ));
        $paidMap = array();
        foreach($planPaidRows as $paidPlanRow)
        {
            $planId = isset($paidPlanRow['MyPayment']['plans_payment_id']) ? (int)$paidPlanRow['MyPayment']['plans_payment_id'] : 0;
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
    public function index($dealId = null)
    {
        $this->loadModel('Deal');
        $this->loadModel('Admin.PlansPayment');
        $this->loadModel('Admin.DealsPayment');

        $selectedDealId = (int)$dealId;

        // 1. Fetch all deals for this authenticated client
        $clientDeals = $this->Deal->find('all', array(
            'joins' => array(
                array('table' => 'properties', 'alias' => 'Property', 'type' => 'LEFT', 'conditions' => array('Deal.property_id=Property.id')),
                array('table' => 'units', 'alias' => 'Unit', 'type' => 'LEFT', 'conditions' => array('Property.unit_id=Unit.id')),
                array('table' => 'plans', 'alias' => 'Plan', 'type' => 'LEFT', 'conditions' => array('Deal.plan_id=Plan.id')),
                array('table' => 'projects', 'alias' => 'Project', 'type' => 'LEFT', 'conditions' => array('Property.project_id=Project.id'))
            ),
            'fields' => array(
                'Deal.*',
                'Property.id', 'Property.name', 'Property.area', 'Property.price',
                'Unit.id', 'Unit.name',
                'Plan.id', 'Plan.name',
                'Project.id', 'Project.name'
            ),
            'conditions' => array(
                'Deal.client_id' => $this->userId,
                $this->_dealVisibilityCondition('Deal')
            ),
            'order' => array('Deal.id' => 'desc'),
            'recursive' => -1
        ));

        $dealMap = array();
        $dealIds = array();
        foreach ((array)$clientDeals as $cd) {
            if (!empty($cd['Deal']['id'])) {
                $dId = (int)$cd['Deal']['id'];
                $dealIds[] = $dId;
                $dealMap[$dId] = $cd;
            }
        }

        if (empty($dealIds)) {
            $this->set('clientDeals', array());
            $this->set('dealsWithPayments', array());
            $this->set('allPaymentsList', array());
            $this->set('selectedDealId', 0);
            $this->set('overallStats', array(
                'total_paid' => 0,
                'receipts_count' => 0,
                'deals_count' => 0,
                'total_due' => 0
            ));
            $this->set('isClientPortal', true);
            $this->set('title_for_layout', __('My Payment Receipts'));
            return;
        }

        // 2. Fetch all payment receipts for all client deals from deals_payments
        // Order by DealsPayment.payment_date ASC, DealsPayment.id ASC (by their paid date)
        $paymentRows = $this->DealsPayment->find('all', array(
            'joins' => array(
                array('table' => 'deals', 'alias' => 'Deal', 'type' => 'LEFT', 'conditions' => array('DealsPayment.deal_id=Deal.id')),
                array('table' => 'properties', 'alias' => 'Property', 'type' => 'LEFT', 'conditions' => array('Deal.property_id=Property.id')),
                array('table' => 'units', 'alias' => 'Unit', 'type' => 'LEFT', 'conditions' => array('Property.unit_id=Unit.id')),
                array('table' => 'plans_payments', 'alias' => 'PlansPayment', 'type' => 'LEFT', 'conditions' => array('DealsPayment.plans_payment_id=PlansPayment.id')),
                array('table' => 'paymenttypes', 'alias' => 'Paymenttype', 'type' => 'LEFT', 'conditions' => array('DealsPayment.paymenttype_id=Paymenttype.id'))
            ),
            'fields' => array(
                'DealsPayment.*',
                'Deal.id', 'Deal.invoice_no', 'Deal.total_amount', 'Deal.date', 'Deal.deal_status',
                'Property.id', 'Property.name', 'Property.area',
                'Unit.id', 'Unit.name',
                'PlansPayment.id', 'PlansPayment.name', 'PlansPayment.date',
                'Paymenttype.id', 'Paymenttype.name'
            ),
            'conditions' => array(
                'DealsPayment.deal_id' => $dealIds
            ),
            'order' => array('DealsPayment.payment_date' => 'asc', 'DealsPayment.id' => 'asc'),
            'recursive' => -1
        ));

        // 3. Group payments by deal_id and group individual payments by receipt_no
        $dealReceiptGroups = array();
        $allPaymentsList = array();
        $totalPaidAllDeals = 0;
        $totalReceiptsCount = 0;

        foreach ($paymentRows as $paymentRow) {
            $dId = (int)$paymentRow['DealsPayment']['deal_id'];
            $rowId = (int)$paymentRow['DealsPayment']['id'];
            if ($dId <= 0 || $rowId <= 0) {
                continue;
            }

            if (!isset($dealReceiptGroups[$dId])) {
                $dealReceiptGroups[$dId] = array();
            }

            $receiptNoKey = isset($paymentRow['DealsPayment']['receipt_no']) ? trim((string)$paymentRow['DealsPayment']['receipt_no']) : '';
            if ($receiptNoKey === '') {
                $receiptNoKey = 'ROW-' . $rowId;
            }

            if (!isset($dealReceiptGroups[$dId][$receiptNoKey])) {
                $slipFile = !empty($paymentRow['DealsPayment']['file']) ? trim((string)$paymentRow['DealsPayment']['file']) : '';
                $slipDir = !empty($paymentRow['DealsPayment']['dir']) ? trim((string)$paymentRow['DealsPayment']['dir']) : 'paymentslips';

                $dealReceiptGroups[$dId][$receiptNoKey] = array(
                    'receipt_key' => $receiptNoKey,
                    'receipt_no' => !empty($paymentRow['DealsPayment']['receipt_no']) ? $paymentRow['DealsPayment']['receipt_no'] : ('RCP-' . $rowId),
                    'deal_id' => $dId,
                    'first_row' => $paymentRow,
                    'first_id' => $rowId,
                    'ids' => array(),
                    'paid_amount' => 0,
                    'late_fees' => 0,
                    'installment_late_amount' => 0,
                    'payment_names' => array(),
                    'payment_date' => !empty($paymentRow['DealsPayment']['payment_date']) ? $paymentRow['DealsPayment']['payment_date'] : '',
                    'remarks' => !empty($paymentRow['DealsPayment']['remarks']) ? $paymentRow['DealsPayment']['remarks'] : '',
                    'payment_type' => !empty($paymentRow['Paymenttype']['name']) ? $paymentRow['Paymenttype']['name'] : '-',
                    'slip_row_id' => $rowId,
                    'slip_file' => $slipFile,
                    'slip_dir' => $slipDir,
                    'slip_is_image' => 0
                );
            }

            $dealReceiptGroups[$dId][$receiptNoKey]['ids'][] = $rowId;
            $rowPaidAmount = isset($paymentRow['DealsPayment']['emi']) ? (float)$paymentRow['DealsPayment']['emi'] : (isset($paymentRow['DealsPayment']['amount']) ? (float)$paymentRow['DealsPayment']['amount'] : 0);
            $rowLateFees = isset($paymentRow['DealsPayment']['extra_payment']) ? (float)$paymentRow['DealsPayment']['extra_payment'] : 0;

            $dealReceiptGroups[$dId][$receiptNoKey]['paid_amount'] += $rowPaidAmount;
            $dealReceiptGroups[$dId][$receiptNoKey]['late_fees'] += $rowLateFees;
            $dealReceiptGroups[$dId][$receiptNoKey]['installment_late_amount'] += ($rowPaidAmount + $rowLateFees);

            $rowPaymentName = !empty($paymentRow['PlansPayment']['name']) ? trim((string)$paymentRow['PlansPayment']['name']) : '';
            if ($rowPaymentName === '' && !empty($paymentRow['DealsPayment']['name'])) {
                $rowPaymentName = trim((string)$paymentRow['DealsPayment']['name']);
            }
            if ($rowPaymentName !== '' && !in_array($rowPaymentName, $dealReceiptGroups[$dId][$receiptNoKey]['payment_names'], true)) {
                $dealReceiptGroups[$dId][$receiptNoKey]['payment_names'][] = $rowPaymentName;
            }

            if (empty($dealReceiptGroups[$dId][$receiptNoKey]['slip_file']) && !empty($paymentRow['DealsPayment']['file'])) {
                $dealReceiptGroups[$dId][$receiptNoKey]['slip_file'] = trim((string)$paymentRow['DealsPayment']['file']);
                $dealReceiptGroups[$dId][$receiptNoKey]['slip_row_id'] = $rowId;
                if (!empty($paymentRow['DealsPayment']['dir'])) {
                    $dealReceiptGroups[$dId][$receiptNoKey]['slip_dir'] = trim((string)$paymentRow['DealsPayment']['dir']);
                }
            }
        }

        // 4. Build final structure for each deal
        $dealsWithPayments = array();
        $totalBalanceAllDeals = 0;

        foreach ($dealMap as $dId => $cd) {
            $receipts = isset($dealReceiptGroups[$dId]) ? array_values($dealReceiptGroups[$dId]) : array();

            $dealPaidTotal = 0;
            $dealLateFeesTotal = 0;
            foreach ($receipts as &$rcp) {
                $rcp['id_list'] = implode(',', $rcp['ids']);
                if (!empty($rcp['slip_file'])) {
                    $ext = strtolower(pathinfo($rcp['slip_file'], PATHINFO_EXTENSION));
                    $rcp['slip_is_image'] = in_array($ext, array('jpg', 'jpeg', 'png', 'gif', 'webp'), true);
                }
                $dealPaidTotal += $rcp['paid_amount'];
                $dealLateFeesTotal += $rcp['late_fees'];

                $rcpWithDeal = $rcp;
                $rcpWithDeal['deal_invoice'] = !empty($cd['Deal']['invoice_no']) ? $cd['Deal']['invoice_no'] : ('#' . $dId);
                $rcpWithDeal['property_name'] = !empty($cd['Property']['name']) ? $cd['Property']['name'] : __('Property');
                $rcpWithDeal['unit_name'] = !empty($cd['Unit']['name']) ? $cd['Unit']['name'] : '';
                $allPaymentsList[] = $rcpWithDeal;
            }
            unset($rcp);

            $dealTotalAmount = isset($cd['Deal']['total_amount']) ? (float)$cd['Deal']['total_amount'] : 0;
            $dealBalanceDue = round(max(0, $dealTotalAmount - $dealPaidTotal), 2);

            $totalPaidAllDeals += $dealPaidTotal;
            $totalBalanceAllDeals += $dealBalanceDue;
            $totalReceiptsCount += count($receipts);

            $dealsWithPayments[$dId] = array(
                'deal' => $cd['Deal'],
                'property' => isset($cd['Property']) ? $cd['Property'] : array(),
                'unit' => isset($cd['Unit']) ? $cd['Unit'] : array(),
                'plan' => isset($cd['Plan']) ? $cd['Plan'] : array(),
                'project' => isset($cd['Project']) ? $cd['Project'] : array(),
                'receipts' => $receipts,
                'total_paid' => round($dealPaidTotal, 2),
                'total_late_fees' => round($dealLateFeesTotal, 2),
                'total_amount' => round($dealTotalAmount, 2),
                'balance_due' => $dealBalanceDue,
                'receipt_count' => count($receipts)
            );
        }

        // Sort allPaymentsList by payment_date DESC, first_id DESC (by paid date)
        usort($allPaymentsList, function($a, $b) {
            $tA = !empty($a['payment_date']) ? strtotime($a['payment_date']) : 0;
            $tB = !empty($b['payment_date']) ? strtotime($b['payment_date']) : 0;
            if ($tA === $tB) {
                return ($a['first_id'] > $b['first_id']) ? -1 : 1;
            }
            return ($tA > $tB) ? -1 : 1;
        });

        $filteredPaymentsList = $allPaymentsList;
        if ($selectedDealId > 0) {
            $filteredPaymentsList = array_values(array_filter($allPaymentsList, function($item) use ($selectedDealId) {
                return ((int)$item['deal_id'] === $selectedDealId);
            }));
        }

        $overallStats = array(
            'total_paid' => round($totalPaidAllDeals, 2),
            'receipts_count' => $totalReceiptsCount,
            'deals_count' => count($dealMap),
            'total_due' => round($totalBalanceAllDeals, 2)
        );

        $this->set('clientDeals', $clientDeals);
        $this->set('dealsWithPayments', $dealsWithPayments);
        $this->set('allPaymentsList', $allPaymentsList);
        $this->set('filteredPaymentsList', $filteredPaymentsList);
        $this->set('selectedDealId', $selectedDealId);
        $this->set('overallStats', $overallStats);
        $this->set('isClientPortal', true);
        $this->set('title_for_layout', __('My Payment Receipts'));
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
        $this->loadModel('Admin.DealsPayment');
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
        catch(Exception $e) {}
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

    protected function _computeDealStatusAnalysisData($dealId)
    {
        $this->loadModel('Admin.PlansPayment');
        $this->loadModel('Admin.DealsPayment');

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
                    'receipt_no' => !empty($lp['receipt_no']) ? $lp['receipt_no'] : '',
                    'payment_date' => $pDate,
                    'amount' => $pAmt,
                    'payment_type' => !empty($lp['payment_type_name']) ? $lp['payment_type_name'] : '',
                    'timing_status' => $timingStatus,
                    'timing_diff_days' => $timingDiffDays
                );
            }

            $balance = round($planAmount - $planPaidAmount, 2);
            $isPaid = ($balance <= 0);
            $isPartial = ($planPaidAmount > 0 && $balance > 0);
            $isOverdue = (!$isPaid && $dueTs > 0 && $dueTs < $nowTs);

            if($isOverdue)
            {
                $totalOverdueBalance += $balance;
                $overdueCount++;
                $delayDays = (int)round(($nowTs - $dueTs) / 86400);
                if($delayDays > $maxDelayDays)
                {
                    $maxDelayDays = $delayDays;
                }
            }

            $schedules[] = array(
                'index' => $idx + 1,
                'plan_id' => $planId,
                'name' => !empty($pl['name']) ? $pl['name'] : __('Installment') . ' ' . ($idx + 1),
                'amount' => $planAmount,
                'due_date' => $dueDate,
                'paid_amount' => $planPaidAmount,
                'balance' => $balance > 0 ? $balance : 0,
                'is_paid' => $isPaid,
                'is_partial' => $isPartial,
                'is_overdue' => $isOverdue,
                'receipts' => $linkedReceipts
            );
        }

        $totalEvaluated = $earlyCount + $ontimeCount + $lateCount;
        $compliancePercent = 100;
        if($totalEvaluated > 0)
        {
            $compliancePercent = round((($earlyCount + $ontimeCount) / $totalEvaluated) * 100);
        }

        return array(
            'total_plan_amount' => $totalPlanAmount,
            'total_paid_amount' => $totalPaidSum,
            'total_overdue_balance' => $totalOverdueBalance,
            'overdue_count' => $overdueCount,
            'max_delay_days' => $maxDelayDays,
            'early_count' => $earlyCount,
            'ontime_count' => $ontimeCount,
            'late_count' => $lateCount,
            'advance_count' => $advanceCount,
            'compliance_percent' => $compliancePercent,
            'schedules' => $schedules,
            'unlinked_payments' => $unlinkedPayments
        );
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

        $this->loadModel('Admin.DealsPayment');
        $post = $this->DealsPayment->find('first', array(
            'joins' => $joins,
            'fields' => $fields,
            'conditions' => array(
                'DealsPayment.id' => (int)$id,
                'Deal.client_id' => $this->userId
            ),
            'recursive' => -1
        ));

        if(!$post)
        {
            $this->Session->setFlash(__('Payment slip not found or access denied.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'index'));
        }

        $slipFile = !empty($post['DealsPayment']['payment_slip']) ? trim((string)$post['DealsPayment']['payment_slip']) : '';
        $slipDir = !empty($post['DealsPayment']['payment_slip_dir']) ? trim((string)$post['DealsPayment']['payment_slip_dir']) : 'paymentslips';
        $slipRelPath = '';
        $slipMime = !empty($post['DealsPayment']['payment_slip_mime']) ? trim((string)$post['DealsPayment']['payment_slip_mime']) : '';
        $slipIsImage = !empty($post['DealsPayment']['payment_slip_is_image']) ? true : false;
        $slipMessage = __('Payment slip not uploaded.');

        if($slipFile !== '')
        {
            $slipMessage = __('Payment slip file was not found.');
            $safeSlipDir = trim(str_replace('\\', '/', $slipDir), '/');
            if($safeSlipDir === '')
            {
                $safeSlipDir = 'paymentslips';
            }
            $slipAbsPath = WWW_ROOT . 'img' . DS . str_replace('/', DS, $safeSlipDir) . DS . $slipFile;
            if(file_exists($slipAbsPath))
            {
                $slipRelPath = $safeSlipDir . '/' . $slipFile;
                $slipMessage = '';
                if(!$slipIsImage)
                {
                    $ext = strtolower(pathinfo($slipFile, PATHINFO_EXTENSION));
                    $slipIsImage = in_array($ext, array('jpg','jpeg','png','gif','webp'));
                }
            }
        }

        $this->set('post', $post);
        $this->set('slipRelPath', $slipRelPath);
        $this->set('slipMime', $slipMime);
        $this->set('slipIsImage', $slipIsImage);
        $this->set('slipMessage', $slipMessage);
        $this->render('/../Plugin/Admin/View/DealsPayments/viewpaymentslip');
    }

    protected function _getDealScheduleContext($dealId)
    {
        $this->loadModel('Deal');
        $this->loadModel('Plan');
        $this->loadModel('Admin.PlansPayment');
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

    public function printreceipt($id = null)
    {
        $this->layout = false;
        $this->loadModel('Admin.PlansPayment');
        $this->loadModel('Admin.DealsPayment');
        if (!$id)
        {
            $this->Session->setFlash(__('Invalid Post.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        $idList = array();
        foreach(explode(',',(string)$id) as $tokenId)
        {
            $parsedId = (int)trim($tokenId);
            if($parsedId > 0)
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
        $joins = array(
            array('table'=>'deals','alias'=>'Deal','type'=>'LEFT','conditions'=>array('MyPayment.deal_id=Deal.id')),
            array('table'=>'clients','alias'=>'Client','type'=>'LEFT','conditions'=>array('Deal.client_id=Client.id')),
            array('table'=>'properties','alias'=>'Property','type'=>'LEFT','conditions'=>array('Deal.property_id=Property.id')),
            array('table'=>'types','alias'=>'Type','type'=>'LEFT','conditions'=>array('Property.type_id=Type.id')),
            array('table'=>'projects','alias'=>'Project','type'=>'LEFT','conditions'=>array('Property.project_id=Project.id')),
            array('table'=>'units','alias'=>'Unit','type'=>'LEFT','conditions'=>array('Property.unit_id=Unit.id')),
            array('table'=>'plans','alias'=>'Plan','type'=>'LEFT','conditions'=>array('Deal.plan_id=Plan.id')),
            array('table'=>'plans_payments','alias'=>'PlansPayment','type'=>'LEFT','conditions'=>array('MyPayment.plans_payment_id=PlansPayment.id')),
            array('table'=>'paymenttypes','alias'=>'Paymenttype','type'=>'LEFT','conditions'=>array('MyPayment.paymenttype_id=Paymenttype.id'))
        );
        $fields = array('MyPayment.*','Deal.*','Client.*','Property.*','Type.*','Project.*','Unit.*','Plan.*','PlansPayment.*','Paymenttype.*');
        
        $conditions = array(
            'MyPayment.id' => $primaryId,
            'Deal.client_id' => $this->userId,
            $this->_dealVisibilityCondition('Deal')
        );

        $post = $this->MyPayment->find('first', array(
            'joins' => $joins,
            'fields' => $fields,
            'conditions' => $conditions,
            'recursive' => -1
        ));

        if(!$post)
        {
            $this->Session->setFlash(__('Invalid Post.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'index'));
        }

        // Map DealsPayment alias if needed for view compatibility
        if(empty($post['DealsPayment']) && !empty($post['MyPayment']))
        {
            $post['DealsPayment'] = $post['MyPayment'];
        }

        $dealId = (int)$post['Deal']['id'];
        $currentPlanId = !empty($post['PlansPayment']['id']) ? (int)$post['PlansPayment']['id'] : 0;
        $receiptNo = !empty($post['MyPayment']['receipt_no']) ? trim((string)$post['MyPayment']['receipt_no']) : '';
        $receiptRows = array();
        
        if(!empty($idList))
        {
            $receiptRows = $this->MyPayment->find('all', array(
                'joins' => $joins,
                'fields' => $fields,
                'conditions' => array(
                    'MyPayment.id' => array_values($idList),
                    'Deal.client_id' => $this->userId,
                    $this->_dealVisibilityCondition('Deal')
                ),
                'order' => array('MyPayment.id' => 'asc'),
                'recursive' => -1
            ));
        }

        if(!empty($receiptRows) && count($receiptRows) > 1)
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
                $sumEmi += isset($receiptRow['MyPayment']['emi']) ? (float)$receiptRow['MyPayment']['emi'] : 0;
                $sumExtra += isset($receiptRow['MyPayment']['extra_payment']) ? (float)$receiptRow['MyPayment']['extra_payment'] : 0;
                $sumTotal += isset($receiptRow['MyPayment']['total_amount']) ? (float)$receiptRow['MyPayment']['total_amount'] : 0;
                $sumAmount += isset($receiptRow['MyPayment']['amount']) ? (float)$receiptRow['MyPayment']['amount'] : 0;
                $sumTax += isset($receiptRow['MyPayment']['tax_amount']) ? (float)$receiptRow['MyPayment']['tax_amount'] : 0;
                $planName = !empty($receiptRow['PlansPayment']['name']) ? trim((string)$receiptRow['PlansPayment']['name']) : '';
                if($planName !== '' && !in_array($planName, $planNames, true))
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
            $post['MyPayment']['emi'] = round($sumEmi, 2);
            $post['MyPayment']['extra_payment'] = round($sumExtra, 2);
            $post['MyPayment']['total_amount'] = round($sumTotal, 2);
            $post['MyPayment']['amount'] = round($sumAmount, 2);
            $post['MyPayment']['tax_amount'] = round($sumTax, 2);
            $post['DealsPayment'] = $post['MyPayment'];
            if(!empty($planNames))
            {
                $post['PlansPayment']['name'] = implode(', ', $planNames);
            }
        }

        $dealTotal = isset($post['Deal']['total_amount']) ? (float)$post['Deal']['total_amount'] : 0;
        $summaryData = $this->_getReceiptSummaryData($dealId, $dealTotal);
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
                    $currentPaidRow = $this->MyPayment->find('first', array(
                        'fields' => array('SUM(MyPayment.emi) AS paid_total'),
                        'conditions' => array('MyPayment.plans_payment_id' => $currentPlanId),
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
                $nextPaidRow = $this->MyPayment->find('first', array(
                    'fields' => array('SUM(MyPayment.emi) AS paid_total'),
                    'conditions' => array('MyPayment.plans_payment_id' => $nextPlanId),
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

        $showReceiptPrintDate = 1;
        try
        {
            $this->loadModel('Admin.Configuration');
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
            $showReceiptPrintDate = 1;
        }

        $this->set('dueDate', $dueDate);
        $this->set('isNextDueOverdue', $isNextDueOverdue);
        $this->set('receiptSummary', $summaryData);
        $this->set('post', $post);
        $this->set('agencyLogoFile', $agencyLogoFile);
        $this->set('agency_logo', $agencyLogoFile);
        $this->set('showReceiptPrintDate', $showReceiptPrintDate);
    }

    public function statement($dealId = null, $token = null)
    {
        $this->layout = false;
        $this->loadModel('Deal');
        $this->loadModel('PlansPayment');
        $this->loadModel('Project');

        $dealId = (int)$dealId;
        if ($dealId <= 0) {
            throw new NotFoundException(__('Invalid Deal ID for Statement of Account.'));
        }

        $deal = $this->Deal->find('first', array(
            'joins' => array(
                array('table' => 'clients', 'alias' => 'Client', 'type' => 'LEFT', 'conditions' => array('Deal.client_id=Client.id')),
                array('table' => 'properties', 'alias' => 'Property', 'type' => 'LEFT', 'conditions' => array('Deal.property_id=Property.id')),
                array('table' => 'projects', 'alias' => 'Project', 'type' => 'LEFT', 'conditions' => array('Property.project_id=Project.id')),
                array('table' => 'units', 'alias' => 'Unit', 'type' => 'LEFT', 'conditions' => array('Property.unit_id=Unit.id')),
                array('table' => 'plans', 'alias' => 'Plan', 'type' => 'LEFT', 'conditions' => array('Deal.plan_id=Plan.id'))
            ),
            'fields' => array('Deal.*', 'Client.*', 'Property.*', 'Project.*', 'Unit.*', 'Plan.*'),
            'conditions' => array('Deal.id' => $dealId),
            'recursive' => -1
        ));

        if (empty($deal['Deal']['id'])) {
            throw new NotFoundException(__('Deal not found.'));
        }

        $expectedToken = $this->_statementSecurityToken($dealId, $deal);
        $providedToken = !empty($token) ? trim((string)$token) : (!empty($this->request->query['token']) ? trim((string)$this->request->query['token']) : '');

        $hasSessionAuth = false;
        $clientId = 0;
        if (!empty($this->userId)) {
            $clientId = (int)$this->userId;
        } elseif (!empty($this->userValue['Client']['id'])) {
            $clientId = (int)$this->userValue['Client']['id'];
        } elseif ($this->Session->check('frontUser.Client.id')) {
            $clientId = (int)$this->Session->read('frontUser.Client.id');
        } elseif ($this->Session->check('Client.id')) {
            $clientId = (int)$this->Session->read('Client.id');
        }

        if ($clientId > 0 && $clientId === (int)$deal['Deal']['client_id']) {
            $hasSessionAuth = true;
        } elseif ($this->Session->check('User')) {
            $hasSessionAuth = true;
        }

        if (!$hasSessionAuth && ($providedToken === '' || !hash_equals($expectedToken, $providedToken))) {
            throw new ForbiddenException(__('Access denied or invalid statement verification token.'));
        }

        // Fetch all installment plans for this deal
        $planRows = $this->PlansPayment->find('all', array(
            'conditions' => array('PlansPayment.deal_id' => $dealId),
            'order' => array('PlansPayment.date' => 'ASC', 'PlansPayment.id' => 'ASC'),
            'recursive' => -1
        ));

        // Fetch all payment receipts recorded for this deal
        $payments = $this->MyPayment->find('all', array(
            'joins' => array(
                array('table' => 'paymenttypes', 'alias' => 'Paymenttype', 'type' => 'LEFT', 'conditions' => array('MyPayment.paymenttype_id=Paymenttype.id'))
            ),
            'fields' => array('MyPayment.*', 'Paymenttype.*'),
            'conditions' => array('MyPayment.deal_id' => $dealId),
            'order' => array('MyPayment.paid_date' => 'ASC', 'MyPayment.id' => 'ASC'),
            'recursive' => -1
        ));

        $totalDealAmount = isset($deal['Deal']['total_amount']) ? (float)$deal['Deal']['total_amount'] : 0;
        $totalPaidAmount = 0;
        $paidByPlan = array();

        foreach ($payments as $p) {
            $amt = isset($p['MyPayment']['total_amount']) ? (float)$p['MyPayment']['total_amount'] : (isset($p['MyPayment']['amount']) ? (float)$p['MyPayment']['amount'] : 0);
            $totalPaidAmount += $amt;
            $pid = !empty($p['MyPayment']['plans_payment_id']) ? (int)$p['MyPayment']['plans_payment_id'] : 0;
            if ($pid > 0) {
                if (!isset($paidByPlan[$pid])) $paidByPlan[$pid] = 0;
                $paidByPlan[$pid] += (isset($p['MyPayment']['emi']) && (float)$p['MyPayment']['emi'] > 0) ? (float)$p['MyPayment']['emi'] : $amt;
            }
        }

        $balanceAmount = max(0, round($totalDealAmount - $totalPaidAmount, 2));
        $overdueAmount = 0;
        $overdueCount = 0;
        $scheduleItems = array();
        $runningBalance = $totalDealAmount;

        foreach ($planRows as $pr) {
            $pId = (int)$pr['PlansPayment']['id'];
            $pAmt = isset($pr['PlansPayment']['amount']) ? (float)$pr['PlansPayment']['amount'] : 0;
            $pPaid = isset($paidByPlan[$pId]) ? (float)$paidByPlan[$pId] : 0;
            $pPending = max(0, round($pAmt - $pPaid, 2));
            $dueDate = !empty($pr['PlansPayment']['date']) ? $pr['PlansPayment']['date'] : null;

            $isOverdue = ($pPending > 0 && !empty($dueDate) && $dueDate <= date('Y-m-d'));
            if ($isOverdue) {
                $overdueAmount += $pPending;
                $overdueCount++;
            }

            $status = 'Pending';
            if ($pPending == 0 && $pAmt > 0) {
                $status = 'Paid';
            } elseif ($pPaid > 0 && $pPending > 0) {
                $status = 'Partial';
            } elseif ($isOverdue) {
                $status = 'Overdue';
            }

            $scheduleItems[] = array(
                'id' => $pId,
                'name' => !empty($pr['PlansPayment']['name']) ? $pr['PlansPayment']['name'] : 'Installment',
                'amount' => $pAmt,
                'paid_amount' => $pPaid,
                'pending_amount' => $pPending,
                'due_date' => $dueDate,
                'status' => $status
            );
        }

        $paidPercentage = ($totalDealAmount > 0) ? min(100, round(($totalPaidAmount / $totalDealAmount) * 100, 1)) : 0;
        $createdYear = !empty($deal['Deal']['created']) ? date('Y', strtotime($deal['Deal']['created'])) : date('Y');
        $statementNo = 'WV-SOA-' . $createdYear . '-' . str_pad($dealId, 5, '0', STR_PAD_LEFT);
        $statementDate = date('d-M-Y');

        $agencyName = !empty($deal['Project']['name']) ? (string)$deal['Project']['name'] : (string)$this->siteOrganization;
        $propName = !empty($deal['Property']['name']) ? $deal['Property']['name'] : 'White Villas Executive';
        $clientName = !empty($deal['Client']['name']) ? $deal['Client']['name'] : 'Valued Client';
        $invoiceNo = !empty($deal['Deal']['invoice_no']) ? $deal['Deal']['invoice_no'] : (!empty($deal['Deal']['code']) ? $deal['Deal']['code'] : 'WV-DEAL-'.$dealId);

        $verifyUrl = Router::url(array('controller' => 'my_payments', 'action' => 'statement', $dealId, '?' => array('token' => $expectedToken)), true);
        $qrText = "WHITE VILLAS OFFICIAL STATEMENT\nStatement No: {$statementNo}\nClient: {$clientName}\nDeal: {$invoiceNo}\nProperty: {$propName}\nTotal Value: Rs " . number_format($totalDealAmount) . "\nTotal Paid: Rs " . number_format($totalPaidAmount) . "\nBalance: Rs " . number_format($balanceAmount) . "\nVerified: {$statementDate}\nURL: {$verifyUrl}";
        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=420x420&ecc=M&qzone=2&margin=1&format=png&data=' . rawurlencode($qrText);

        $this->set(compact(
            'deal', 'planRows', 'payments', 'scheduleItems',
            'totalDealAmount', 'totalPaidAmount', 'balanceAmount',
            'overdueAmount', 'overdueCount', 'paidPercentage',
            'statementNo', 'statementDate', 'expectedToken', 'qrUrl', 'verifyUrl'
        ));
    }

    protected function _statementSecurityToken($dealId, $dealData)
    {
        $clientId = !empty($dealData['Client']['id']) ? (int)$dealData['Client']['id'] : 0;
        $invoiceNo = !empty($dealData['Deal']['invoice_no']) ? (string)$dealData['Deal']['invoice_no'] : '';
        $totalAmt = isset($dealData['Deal']['total_amount']) ? (float)$dealData['Deal']['total_amount'] : 0;
        $salt = Configure::read('Security.salt');
        return substr(hash('sha256', "WV_STATEMENT_{$dealId}_{$clientId}_{$invoiceNo}_{$totalAmt}_{$salt}"), 0, 32);
    }
}

