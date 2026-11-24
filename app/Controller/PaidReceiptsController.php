<?php
App::uses('AppController', 'Controller');

class PaidReceiptsController extends AppController
{
    public $helpers = array('Html', 'Form', 'Session', 'Number', 'Time', 'NumToWord');
    public $uses = array();

    public function view($paymentIds = null, $token = null)
    {
        $this->layout = null;
        $ids = $this->_receiptPaymentIds($paymentIds);
        if(empty($ids))
        {
            throw new NotFoundException(__('Invalid receipt link.'));
        }

        $data = $this->_receiptData($ids);
        if(empty($data['post']))
        {
            throw new NotFoundException(__('Receipt was not found.'));
        }

        $expectedToken = $this->_receiptShareToken($ids, $data['post']);
        if(!$this->_secureTokenEquals($expectedToken, (string)$token))
        {
            throw new ForbiddenException(__('Invalid receipt link.'));
        }

        $this->set('dueDate', $data['dueDate']);
        $this->set('receiptSummary', $data['receiptSummary']);
        $this->set('post', $data['post']);
        $this->set('agencyLogoFile', $data['agencyLogoFile']);
        $this->set('agency_logo', $data['agencyLogoFile']);
        $this->set('showReceiptPrintDate', $data['showReceiptPrintDate']);
    }

    protected function _receiptPaymentIds($paymentIds)
    {
        $ids = array();
        foreach(preg_split('/[^0-9]+/', (string)$paymentIds) as $id)
        {
            $id = (int)$id;
            if($id > 0)
            {
                $ids[$id] = $id;
            }
        }
        ksort($ids);
        return array_values($ids);
    }

    protected function _receiptData($ids)
    {
        $this->loadModel('Admin.DealsPayment');
        $this->loadModel('Admin.PlansPayment');
        $joins = $this->_receiptJoins();
        $fields = array('DealsPayment.*','Deal.*','Client.*','Property.*','Project.*','Unit.*','Plan.*','PlansPayment.*','Paymenttype.*');
        $receiptRows = $this->DealsPayment->find('all', array(
            'joins' => $joins,
            'fields' => $fields,
            'conditions' => array('DealsPayment.id' => $ids),
            'order' => array('DealsPayment.id' => 'asc'),
            'recursive' => -1
        ));
        if(empty($receiptRows))
        {
            return array();
        }

        $post = $this->_mergedReceiptPost($receiptRows);
        $dealId = !empty($post['Deal']['id']) ? (int)$post['Deal']['id'] : 0;
        $currentPlanId = !empty($post['PlansPayment']['id']) ? (int)$post['PlansPayment']['id'] : 0;
        foreach($receiptRows as $receiptRow)
        {
            $planId = !empty($receiptRow['PlansPayment']['id']) ? (int)$receiptRow['PlansPayment']['id'] : 0;
            if($planId > $currentPlanId)
            {
                $currentPlanId = $planId;
            }
        }

        $dueDateConditions = array('PlansPayment.deal_id' => $dealId);
        if($currentPlanId > 0)
        {
            $dueDateConditions['PlansPayment.id >'] = $currentPlanId;
        }
        $dueDateRow = $this->PlansPayment->find('first', array(
            'fields' => array('PlansPayment.date'),
            'conditions' => $dueDateConditions,
            'order' => array('PlansPayment.id' => 'asc'),
            'recursive' => -1
        ));

        return array(
            'post' => $post,
            'dueDate' => !empty($dueDateRow['PlansPayment']['date']) ? $dueDateRow['PlansPayment']['date'] : null,
            'receiptSummary' => $this->_receiptSummaryData($dealId, isset($post['Deal']['total_amount']) ? $post['Deal']['total_amount'] : 0),
            'agencyLogoFile' => $this->_receiptAgencyLogoFile($post),
            'showReceiptPrintDate' => $this->_receiptPrintDateSetting()
        );
    }

    protected function _receiptJoins()
    {
        return array(
            array('table'=>'deals','alias'=>'Deal','type'=>'LEFT','conditions'=>array('DealsPayment.deal_id=Deal.id')),
            array('table'=>'clients','alias'=>'Client','type'=>'LEFT','conditions'=>array('Deal.client_id=Client.id')),
            array('table'=>'properties','alias'=>'Property','type'=>'LEFT','conditions'=>array('Deal.property_id=Property.id')),
            array('table'=>'projects','alias'=>'Project','type'=>'LEFT','conditions'=>array('Property.project_id=Project.id')),
            array('table'=>'units','alias'=>'Unit','type'=>'LEFT','conditions'=>array('Property.unit_id=Unit.id')),
            array('table'=>'plans','alias'=>'Plan','type'=>'LEFT','conditions'=>array('Deal.plan_id=Plan.id')),
            array('table'=>'plans_payments','alias'=>'PlansPayment','type'=>'LEFT','conditions'=>array('DealsPayment.plans_payment_id=PlansPayment.id')),
            array('table'=>'paymenttypes','alias'=>'Paymenttype','type'=>'LEFT','conditions'=>array('DealsPayment.paymenttype_id=Paymenttype.id'))
        );
    }

    protected function _mergedReceiptPost($receiptRows)
    {
        $post = $receiptRows[0];
        if(count($receiptRows) <= 1)
        {
            return $post;
        }

        $sumEmi = $sumExtra = $sumTotal = $sumAmount = $sumTax = 0;
        $planNames = array();
        foreach($receiptRows as $receiptRow)
        {
            $sumEmi += isset($receiptRow['DealsPayment']['emi']) ? (float)$receiptRow['DealsPayment']['emi'] : 0;
            $sumExtra += isset($receiptRow['DealsPayment']['extra_payment']) ? (float)$receiptRow['DealsPayment']['extra_payment'] : 0;
            $sumTotal += isset($receiptRow['DealsPayment']['total_amount']) ? (float)$receiptRow['DealsPayment']['total_amount'] : 0;
            $sumAmount += isset($receiptRow['DealsPayment']['amount']) ? (float)$receiptRow['DealsPayment']['amount'] : 0;
            $sumTax += isset($receiptRow['DealsPayment']['tax_amount']) ? (float)$receiptRow['DealsPayment']['tax_amount'] : 0;
            $planName = !empty($receiptRow['PlansPayment']['name']) ? trim((string)$receiptRow['PlansPayment']['name']) : '';
            if($planName !== '' && !in_array($planName, $planNames, true))
            {
                $planNames[] = $planName;
            }
        }
        $post['DealsPayment']['emi'] = round($sumEmi, 2);
        $post['DealsPayment']['extra_payment'] = round($sumExtra, 2);
        $post['DealsPayment']['total_amount'] = round($sumTotal, 2);
        $post['DealsPayment']['amount'] = round($sumAmount, 2);
        $post['DealsPayment']['tax_amount'] = round($sumTax, 2);
        if(!empty($planNames))
        {
            $post['PlansPayment']['name'] = implode(', ', $planNames);
        }
        return $post;
    }

    protected function _receiptSummaryData($dealId, $dealTotalAmount = 0)
    {
        $dealId = (int)$dealId;
        if($dealId <= 0)
        {
            return array('deal_due_amount'=>0, 'deal_paid_amount'=>0, 'deal_overdue_amount'=>0);
        }
        $dealTotalAmount = round((float)$dealTotalAmount, 2);
        $paidRow = $this->DealsPayment->find('first', array(
            'fields' => array('SUM(DealsPayment.emi) AS paid_total'),
            'conditions' => array('DealsPayment.deal_id' => $dealId),
            'recursive' => -1
        ));
        $totalPaid = isset($paidRow[0]['paid_total']) ? round((float)$paidRow[0]['paid_total'], 2) : 0;
        $dueAmount = max(0, round($dealTotalAmount - $totalPaid, 2));
        $planRows = $this->PlansPayment->find('all', array(
            'fields' => array('PlansPayment.id','PlansPayment.amount','PlansPayment.date'),
            'conditions' => array('PlansPayment.deal_id' => $dealId),
            'order' => array('PlansPayment.id' => 'asc'),
            'recursive' => -1
        ));
        $planPaidRows = $this->DealsPayment->find('all', array(
            'fields' => array('DealsPayment.plans_payment_id','SUM(DealsPayment.emi) AS paid_total'),
            'conditions' => array('DealsPayment.deal_id' => $dealId),
            'group' => array('DealsPayment.plans_payment_id'),
            'recursive' => -1
        ));
        $paidMap = array();
        foreach($planPaidRows as $paidPlanRow)
        {
            $planId = isset($paidPlanRow['DealsPayment']['plans_payment_id']) ? (int)$paidPlanRow['DealsPayment']['plans_payment_id'] : 0;
            if($planId > 0)
            {
                $paidMap[$planId] = isset($paidPlanRow[0]['paid_total']) ? (float)$paidPlanRow[0]['paid_total'] : 0;
            }
        }
        $overdueAmount = 0;
        foreach($planRows as $planRow)
        {
            $planId = (int)$planRow['PlansPayment']['id'];
            $pendingAmount = round((float)$planRow['PlansPayment']['amount'] - (isset($paidMap[$planId]) ? (float)$paidMap[$planId] : 0), 2);
            $dueDate = isset($planRow['PlansPayment']['date']) ? $planRow['PlansPayment']['date'] : null;
            if($pendingAmount > 0 && $dueDate && $dueDate <= $this->currentDate)
            {
                $overdueAmount += $pendingAmount;
            }
        }
        return array(
            'deal_due_amount' => $dueAmount,
            'deal_paid_amount' => $totalPaid,
            'deal_overdue_amount' => round($overdueAmount, 2)
        );
    }

    protected function _receiptAgencyLogoFile($post)
    {
        $projectId = !empty($post['Project']['id']) ? (int)$post['Project']['id'] : (!empty($post['Property']['project_id']) ? (int)$post['Property']['project_id'] : 0);
        if($projectId <= 0)
        {
            return '';
        }
        $this->loadModel('User');
        $fields = array('User.id');
        if($this->User->hasField('photo'))
        {
            $fields[] = 'User.photo';
        }
        $conditions = array('User.project_id' => $projectId, 'User.photo <>' => '');
        $row = array();
        if($this->User->hasField('ugroup_id'))
        {
            $row = $this->User->find('first', array(
                'fields' => $fields,
                'conditions' => array_merge($conditions, array('User.ugroup_id' => 4)),
                'order' => array('User.id' => 'asc'),
                'recursive' => -1
            ));
        }
        if(empty($row))
        {
            $row = $this->User->find('first', array(
                'fields' => $fields,
                'conditions' => $conditions,
                'order' => array('User.id' => 'asc'),
                'recursive' => -1
            ));
        }
        return !empty($row['User']['photo']) ? trim((string)$row['User']['photo']) : '';
    }

    protected function _receiptPrintDateSetting()
    {
        $show = isset($this->showReceiptPrintDate) ? (int)$this->showReceiptPrintDate : 1;
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
                    $show = (int)$cfg['Configuration']['show_receipt_print_date'];
                }
            }
        }
        catch(Exception $e)
        {
            $show = isset($this->showReceiptPrintDate) ? (int)$this->showReceiptPrintDate : 1;
        }
        return $show;
    }

    protected function _receiptShareToken($ids, $post)
    {
        $parts = array(
            implode(',', (array)$ids),
            !empty($post['DealsPayment']['receipt_no']) ? trim((string)$post['DealsPayment']['receipt_no']) : '',
            isset($post['DealsPayment']['amount']) ? number_format((float)$post['DealsPayment']['amount'], 2, '.', '') : '0.00',
            Configure::read('Security.salt')
        );
        return substr(hash('sha256', implode('|', $parts)), 0, 32);
    }

    protected function _secureTokenEquals($known, $user)
    {
        $known = (string)$known;
        $user = (string)$user;
        if(function_exists('hash_equals'))
        {
            return hash_equals($known, $user);
        }
        if(strlen($known) !== strlen($user))
        {
            return false;
        }
        $result = 0;
        for($i = 0; $i < strlen($known); $i++)
        {
            $result |= ord($known[$i]) ^ ord($user[$i]);
        }
        return $result === 0;
    }
}
