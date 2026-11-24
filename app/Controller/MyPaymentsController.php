<?php
class MyPaymentsController extends AppController
{
    public $helpers = array('Html', 'Form', 'Session', 'Time', 'Number', 'NumToWord');
    protected $_portalVisibleDealStatuses = array('Active','Blocked');
    public function beforeFilter()
    {
        parent::beforeFilter();
        $action = !empty($this->request->params['action']) ? strtolower($this->request->params['action']) : '';
        if ($action === 'statement') {
            if ($this->Session->check('Client')) {
                $this->authenticate();
                $this->userId = $this->userValue['Client']['id'];
            }
            return;
        }
        $this->authenticate();
        $this->userId = $this->userValue['Client']['id'];
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
        $this->loadModel('PlansPayment');
        $selectedDealId = (int)$dealId;
        $paymentConditions = array('Deal.client_id'=>$this->userId,$this->_dealVisibilityCondition('Deal'));
        if($selectedDealId > 0)
        {
            $paymentConditions['Deal.id'] = $selectedDealId;
        }
        $postArr=$this->MyPayment->find('all',array(
            'joins'=>array(
                array('table'=>'deals','alias'=>'Deal','type'=>'LEFT','conditions'=>array('MyPayment.deal_id=Deal.id')),
                array('table'=>'properties','alias'=>'Property','type'=>'LEFT','conditions'=>array('Deal.property_id=Property.id')),
                array('table'=>'units','alias'=>'Unit','type'=>'LEFT','conditions'=>array('Property.unit_id=Unit.id')),
                array('table'=>'paymenttypes','alias'=>'Paymenttype','type'=>'LEFT','conditions'=>array('MyPayment.paymenttype_id=Paymenttype.id')),
                array('table'=>'plans_payments','alias'=>'PlansPayment','type'=>'LEFT','conditions'=>array('MyPayment.plans_payment_id=PlansPayment.id'))
            ),
            'fields'=>array('MyPayment.*','Paymenttype.*','Deal.*','Property.*','Unit.*','PlansPayment.*'),
            'conditions'=>$paymentConditions,
            'order'=>array('Deal.id'=>'desc','MyPayment.id'=>'desc'),
            'recursive'=>-1
        ));

        $paymentsByDeal = array();
        $overallStats = array(
            'total_paid' => 0,
            'receipts_count' => 0,
            'early_count' => 0,
            'ontime_count' => 0,
            'late_count' => 0,
            'advance_count' => 0
        );

        foreach((array)$postArr as $row)
        {
            if(empty($row['Deal']['id']))
            {
                continue;
            }
            $dealId = (int)$row['Deal']['id'];
            if(!isset($paymentsByDeal[$dealId]))
            {
                $paymentsByDeal[$dealId] = array(
                    'Deal' => isset($row['Deal']) ? $row['Deal'] : array(),
                    'Property' => isset($row['Property']) ? $row['Property'] : array(),
                    'Unit' => isset($row['Unit']) ? $row['Unit'] : array(),
                    'rows' => array(),
                    'total_paid' => 0,
                    'receipt_count' => 0,
                    'early_count' => 0,
                    'ontime_count' => 0,
                    'late_count' => 0,
                    'advance_count' => 0
                );
            }

            $payDate = !empty($row['MyPayment']['payment_date']) ? date('Y-m-d', strtotime($row['MyPayment']['payment_date'])) : (!empty($row['MyPayment']['date']) ? date('Y-m-d', strtotime($row['MyPayment']['date'])) : '');
            $dueDate = !empty($row['PlansPayment']['date']) ? date('Y-m-d', strtotime($row['PlansPayment']['date'])) : '';

            $timingStatus = 'advance';
            $diffDays = 0;

            if(!empty($payDate) && !empty($dueDate))
            {
                $diff = (int)round((strtotime($payDate) - strtotime($dueDate)) / 86400);
                if($diff < 0)
                {
                    $timingStatus = 'early';
                    $diffDays = abs($diff);
                    $paymentsByDeal[$dealId]['early_count']++;
                    $overallStats['early_count']++;
                }
                elseif($diff == 0)
                {
                    $timingStatus = 'ontime';
                    $diffDays = 0;
                    $paymentsByDeal[$dealId]['ontime_count']++;
                    $overallStats['ontime_count']++;
                }
                else
                {
                    $timingStatus = 'late';
                    $diffDays = $diff;
                    $paymentsByDeal[$dealId]['late_count']++;
                    $overallStats['late_count']++;
                }
            }
            else
            {
                $paymentsByDeal[$dealId]['advance_count']++;
                $overallStats['advance_count']++;
            }

            $row['TimingAnalysis'] = array(
                'status' => $timingStatus,
                'diff_days' => $diffDays,
                'due_date' => $dueDate,
                'payment_date' => $payDate,
                'installment_name' => !empty($row['PlansPayment']['name']) ? $row['PlansPayment']['name'] : ''
            );

            $amt = isset($row['MyPayment']['amount']) ? (float)$row['MyPayment']['amount'] : 0;
            $paymentsByDeal[$dealId]['rows'][] = $row;
            $paymentsByDeal[$dealId]['total_paid'] += $amt;
            $paymentsByDeal[$dealId]['receipt_count']++;
            $overallStats['total_paid'] += $amt;
            $overallStats['receipts_count']++;
        }

        foreach($paymentsByDeal as $dealId => $dealData)
        {
            $paymentsByDeal[$dealId]['total_paid'] = round((float)$dealData['total_paid'],2);
        }

        $dealIds = array_keys($paymentsByDeal);
        $planPaidById = array();
        if(!empty($dealIds))
        {
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
                $planPaidById[$planId] = isset($planPaidRow[0]['paid_total']) ? (float)$planPaidRow[0]['paid_total'] : 0;
            }
        }

        $overdueByDeal = array();
        $overdueInstallmentsByDeal = array();
        if(!empty($dealIds))
        {
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
                $planPaid = isset($planPaidById[$planId]) ? (float)$planPaidById[$planId] : 0;
                $pending = round($planAmount - $planPaid, 2);
                $dueDate = !empty($planRow['PlansPayment']['date']) ? $planRow['PlansPayment']['date'] : null;
                if($pending > 0 && $dueDate && $dueDate <= $this->currentDate)
                {
                    if(!isset($overdueByDeal[$dealId]))
                    {
                        $overdueByDeal[$dealId] = 0;
                    }
                    if(!isset($overdueInstallmentsByDeal[$dealId]))
                    {
                        $overdueInstallmentsByDeal[$dealId] = 0;
                    }
                    $overdueByDeal[$dealId] += $pending;
                    $overdueInstallmentsByDeal[$dealId]++;
                }
            }
        }

        foreach($paymentsByDeal as $dealId => $dealData)
        {
            $dealTotal = isset($dealData['Deal']['total_amount']) ? (float)$dealData['Deal']['total_amount'] : 0;
            $dealPaid = isset($dealData['total_paid']) ? (float)$dealData['total_paid'] : 0;
            $dueAmount = round($dealTotal - $dealPaid, 2);
            if($dueAmount < 0)
            {
                $dueAmount = 0;
            }
            $paymentsByDeal[$dealId]['due_amount'] = $dueAmount;
            $paymentsByDeal[$dealId]['overdue_amount'] = round(isset($overdueByDeal[$dealId]) ? (float)$overdueByDeal[$dealId] : 0, 2);
            $paymentsByDeal[$dealId]['overdue_installments'] = isset($overdueInstallmentsByDeal[$dealId]) ? (int)$overdueInstallmentsByDeal[$dealId] : 0;
        }

        $this->set('postArr',$postArr);
        $this->set('paymentsByDeal',$paymentsByDeal);
        $this->set('selectedDealId',$selectedDealId);
        $this->set('overallStats',$overallStats);
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
        $this->layout = null;
        $this->loadModel('Admin.PlansPayment');
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
        $this->layout = null;
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
        if ($this->Session->check('Client')) {
            $clientId = (int)$this->Session->read('Client.id');
            if ($clientId > 0 && $clientId === (int)$deal['Deal']['client_id']) {
                $hasSessionAuth = true;
            }
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

