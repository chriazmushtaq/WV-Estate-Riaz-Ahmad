<?php
App::uses('CakeTime', 'Utility');
App::uses('CakeNumber', 'Utility');
class DealrefundsController extends AdminAppController
{
    public $helpers = array('Html', 'Form','Session','Paginator','Js'=> array('Jquery'));
    public $components = array('Session','Paginator');
    public $paginate = array('page' => 1, 'order' => array('Dealrefund.id' => 'desc'));

    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->loadModel('Admin.Dealrefund');
        $this->loadModel('Admin.Dealrefundschedule');
        if($this->_isSuperAdminSession((array)$this->adminValue))
        {
            $this->userPermissionArr = $this->_defaultFullPermission();
            $this->set('userPermissionArr', $this->userPermissionArr);
            $this->set('allowAddAction', true);
            $this->set('allowEditAction', true);
            $this->set('allowDeleteAction', true);
            $this->set('allowViewAction', true);
        }
        $needsSchema = !$this->_refundTablesReady();
        if($needsSchema || $this->_shouldRunControllerBootstrap('dealrefunds.schema', '2026-06-02.1'))
        {
            $this->_ensureRefundSchema();
            $this->_ensureRefundSettingsColumn();
            $this->_ensureBusinessAccountingSchema();
            try
            {
                $this->Dealrefund->schema(true);
                $this->Dealrefundschedule->schema(true);
            }
            catch(Exception $e)
            {
                // ignore schema refresh issues
            }
        }
    }

    protected function _refundTablesReady()
    {
        try
        {
            $rows = $this->Dealrefund->query("SHOW TABLES LIKE 'deal_refunds'");
            return !empty($rows);
        }
        catch(Exception $e)
        {
            return false;
        }
    }

    protected function _ensureRefundSchema()
    {
        $this->loadModel('Admin.Dealrefund');
        $sql = array(
            "CREATE TABLE IF NOT EXISTS `deal_refunds` (
                `id` bigint(20) NOT NULL AUTO_INCREMENT,
                `deal_id` bigint(20) NOT NULL,
                `client_id` bigint(20) NOT NULL,
                `project_id` int(11) NOT NULL DEFAULT 0,
                `status` varchar(30) NOT NULL DEFAULT 'Draft',
                `request_date` date DEFAULT NULL,
                `total_paid` decimal(15,2) NOT NULL DEFAULT 0.00,
                `deduction_percent` decimal(8,2) NOT NULL DEFAULT 0.00,
                `deduction_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
                `net_refund` decimal(15,2) NOT NULL DEFAULT 0.00,
                `cycle_count` int(11) NOT NULL DEFAULT 1,
                `cycle_frequency` varchar(20) NOT NULL DEFAULT 'Monthly',
                `cycle_start_date` date DEFAULT NULL,
                `per_cycle_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
                `reason` text,
                `applicant_name` varchar(160) DEFAULT '',
                `father_name` varchar(160) DEFAULT '',
                `cnic` varchar(40) DEFAULT '',
                `passport_no` varchar(40) DEFAULT '',
                `address` text,
                `mobile_no` varchar(40) DEFAULT '',
                `email` varchar(140) DEFAULT '',
                `account_title` varchar(160) DEFAULT '',
                `account_no` varchar(120) DEFAULT '',
                `bank_name` varchar(120) DEFAULT '',
                `bank_branch_city` varchar(160) DEFAULT '',
                `created_by` int(11) NOT NULL DEFAULT 0,
                `created` datetime DEFAULT NULL,
                `modified` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `deal_id` (`deal_id`),
                KEY `client_id` (`client_id`),
                KEY `project_id` (`project_id`),
                KEY `status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3",
            "CREATE TABLE IF NOT EXISTS `deal_refund_schedules` (
                `id` bigint(20) NOT NULL AUTO_INCREMENT,
                `refund_id` bigint(20) NOT NULL,
                `deal_id` bigint(20) NOT NULL,
                `client_id` bigint(20) NOT NULL,
                `project_id` int(11) NOT NULL DEFAULT 0,
                `cycle_no` int(11) NOT NULL DEFAULT 1,
                `scheduled_date` date DEFAULT NULL,
                `amount` decimal(15,2) NOT NULL DEFAULT 0.00,
                `status` varchar(20) NOT NULL DEFAULT 'Unpaid',
                `paid_date` date DEFAULT NULL,
                `paid_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
                `paid_ledger_id` bigint(20) NOT NULL DEFAULT 0,
                `voucher_transaction_id` bigint(20) NOT NULL DEFAULT 0,
                `remarks` text,
                `created` datetime DEFAULT NULL,
                `modified` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `refund_id` (`refund_id`),
                KEY `deal_id` (`deal_id`),
                KEY `client_id` (`client_id`),
                KEY `project_id` (`project_id`),
                KEY `status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3"
        );
        foreach($sql as $q)
        {
            try
            {
                $this->Dealrefund->query($q);
            }
            catch(Exception $e)
            {
                // keep flow safe
            }
        }
        $alterSql = array(
            "ALTER TABLE `deal_refunds` ADD COLUMN `relation_type` varchar(50) DEFAULT '' AFTER `applicant_name`",
            "ALTER TABLE `deal_refunds` ADD COLUMN `payable_transaction_id` bigint(20) NOT NULL DEFAULT 0 AFTER `net_refund`",
            "ALTER TABLE `deal_refunds` ADD COLUMN `forfeiture_transaction_id` bigint(20) NOT NULL DEFAULT 0 AFTER `payable_transaction_id`",
            "ALTER TABLE `deal_refunds` ADD KEY `payable_transaction_id` (`payable_transaction_id`)",
            "ALTER TABLE `deal_refunds` ADD KEY `forfeiture_transaction_id` (`forfeiture_transaction_id`)"
        );
        foreach($alterSql as $q)
        {
            try { $this->Dealrefund->query($q); } catch(Exception $e) {}
        }
    }

    protected function _ensureRefundSettingsColumn()
    {
        $this->loadModel('Admin.Ardsetting');
        try
        {
            $this->Ardsetting->query("ALTER TABLE `ardaccounts_settings` ADD COLUMN `refund_deduction_percent` decimal(8,2) NOT NULL DEFAULT 0.00 AFTER `deal_charge_types`");
        }
        catch(Exception $e)
        {
            // Ignore if exists.
        }
        try
        {
            $this->Ardsetting->schema(true);
        }
        catch(Exception $e)
        {
            // Ignore schema refresh issues.
        }
    }

    public function index($dealId = null)
    {
        $dealId = (int)$dealId;
        $query = (array)$this->request->query;
        $conditions = array();

        if(!$this->_isSuperAdminSession((array)$this->adminValue))
        {
            $conditions['Dealrefund.project_id'] = (int)$this->agencyId;
        }
        if($dealId > 0)
        {
            $conditions['Dealrefund.deal_id'] = $dealId;
        }
        if(!empty($query['status']))
        {
            $conditions['Dealrefund.status'] = trim((string)$query['status']);
        }

        $this->Paginator->settings = $this->paginate;
        $this->Paginator->settings['conditions'] = $conditions;
        $this->Paginator->settings['limit'] = $this->pageLimit;
        $this->Paginator->settings['maxLimit'] = $this->maxLimit;
        $this->Paginator->settings['recursive'] = 0;
        $refundRows = $this->Paginator->paginate('Dealrefund');
        $refundProgressSummary = array();
        $refundHasPaidSchedule = array();
        foreach((array)$refundRows as $refundRow)
        {
            if(!empty($refundRow['Dealrefund']['id']))
            {
                $rid = (int)$refundRow['Dealrefund']['id'];
                $this->_syncDeletedRefundVoucherSchedules($rid);
                $this->_reconcileRefundScheduleBalance($refundRow['Dealrefund']);
                $refundProgressSummary[$rid] = $this->_refundProgressSummary($refundRow['Dealrefund']);
                $refundHasPaidSchedule[$rid] = ((int)$this->Dealrefundschedule->find('count', array(
                    'conditions' => array(
                        'Dealrefundschedule.refund_id' => $rid,
                        'Dealrefundschedule.status' => 'Paid'
                    ),
                    'recursive' => -1
                )) > 0);
            }
        }

        $dealOptions = $this->_dealOptionsForRefund();
        $statusOptions = array('Draft' => 'Draft', 'Finalized' => 'Finalized');
        $defaultDeduction = $this->_defaultRefundDeductionPercent();

        $this->set(compact('refundRows','dealOptions','statusOptions','defaultDeduction','dealId','refundProgressSummary','refundHasPaidSchedule'));
    }

    public function create()
    {
        if(!$this->request->is(array('post','put')))
        {
            return $this->redirect(array('action' => 'index'));
        }
        $row = isset($this->request->data['Dealrefund']) ? $this->request->data['Dealrefund'] : array();
        $dealId = isset($row['deal_id']) ? (int)$row['deal_id'] : 0;
        if($dealId <= 0)
        {
            $this->Session->setFlash(__('Please select a deal first.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'index'));
        }

        $dealData = $this->_fetchDealDetails($dealId);
        if(empty($dealData['Deal']['id']))
        {
            $this->Session->setFlash(__('Invalid deal selected.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'index'));
        }

        $projectId = (int)$dealData['Property']['project_id'];
        if(!$this->_isAllowedProject($projectId))
        {
            $this->Session->setFlash(__('No Permission!'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'index'));
        }

        $totalPaid = $this->_dealTotalPaid($dealId);
        $deductionPercent = isset($row['deduction_percent']) ? (float)str_replace(array(',', '%'), '', $row['deduction_percent']) : $this->_defaultRefundDeductionPercent($projectId);
        $deductionAmount = round(($totalPaid * $deductionPercent) / 100, 2);
        $netRefund = round(max(0, $totalPaid - $deductionAmount), 2);
        $cycleCount = max(1, (int)(isset($row['cycle_count']) ? $row['cycle_count'] : 1));
        $perCycleAmount = ($cycleCount > 0) ? round($netRefund / $cycleCount, 2) : 0.0;
        $cycleFrequency = trim((string)(isset($row['cycle_frequency']) ? $row['cycle_frequency'] : 'Monthly'));
        if($cycleFrequency === ''){ $cycleFrequency = 'Monthly'; }
        $cycleStartDate = $this->_normalizeDate(isset($row['cycle_start_date']) ? $row['cycle_start_date'] : '');
        $requestDate = $this->_normalizeDate(isset($row['request_date']) ? $row['request_date'] : '');

        $applicantName = isset($row['applicant_name']) && trim((string)$row['applicant_name']) !== '' ? trim((string)$row['applicant_name']) : (isset($dealData['Client']['name']) ? $dealData['Client']['name'] : '');
        $relationType = isset($row['relation_type']) && trim((string)$row['relation_type']) !== '' ? trim((string)$row['relation_type']) : (isset($dealData['Client']['relation_type']) ? $dealData['Client']['relation_type'] : 'Father');
        $fatherName = isset($row['father_name']) && trim((string)$row['father_name']) !== '' ? trim((string)$row['father_name']) : (isset($dealData['Client']['fathername']) ? $dealData['Client']['fathername'] : '');

        $refundData = array(
            'deal_id' => $dealId,
            'client_id' => (int)$dealData['Deal']['client_id'],
            'project_id' => $projectId,
            'status' => 'Draft',
            'request_date' => $requestDate,
            'total_paid' => $totalPaid,
            'deduction_percent' => $deductionPercent,
            'deduction_amount' => $deductionAmount,
            'net_refund' => $netRefund,
            'cycle_count' => $cycleCount,
            'cycle_frequency' => $cycleFrequency,
            'cycle_start_date' => $cycleStartDate,
            'per_cycle_amount' => $perCycleAmount,
            'reason' => isset($row['reason']) ? $row['reason'] : '',
            'applicant_name' => $applicantName,
            'relation_type' => $relationType,
            'father_name' => $fatherName,
            'cnic' => isset($row['cnic']) && trim((string)$row['cnic']) !== '' ? trim((string)$row['cnic']) : (isset($dealData['Client']['pincode']) ? $dealData['Client']['pincode'] : ''),
            'passport_no' => isset($row['passport_no']) ? $row['passport_no'] : '',
            'address' => isset($row['address']) && trim((string)$row['address']) !== '' ? trim((string)$row['address']) : (isset($dealData['Client']['address']) ? $dealData['Client']['address'] : ''),
            'mobile_no' => isset($row['mobile_no']) && trim((string)$row['mobile_no']) !== '' ? trim((string)$row['mobile_no']) : (isset($dealData['Client']['phone']) ? $dealData['Client']['phone'] : ''),
            'email' => isset($row['email']) && trim((string)$row['email']) !== '' ? trim((string)$row['email']) : (isset($dealData['Client']['email']) ? $dealData['Client']['email'] : ''),
            'account_title' => isset($row['account_title']) ? $row['account_title'] : '',
            'account_no' => isset($row['account_no']) ? $row['account_no'] : '',
            'bank_name' => isset($row['bank_name']) ? $row['bank_name'] : '',
            'bank_branch_city' => isset($row['bank_branch_city']) ? $row['bank_branch_city'] : '',
            'created_by' => (int)$this->luserId
        );

        $this->Dealrefund->create();
        if(!$this->Dealrefund->save(array('Dealrefund' => $refundData), array('validate' => false)))
        {
            $this->Session->setFlash(__('Unable to save refund request.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        $refundId = (int)$this->Dealrefund->id;
        $finalize = !empty($row['finalize_now']) ? 1 : 0;
        if($finalize)
        {
            $this->_finalizeRefund($refundId);
        }
        $this->Session->setFlash(__('Refund request saved successfully.'),'flash',array('alert'=>'success'));
        return $this->redirect(array('action' => 'view', $refundId));
    }

    public function view($refundId = null)
    {
        $refundId = (int)$refundId;
        if($refundId <= 0)
        {
            return $this->redirect(array('action' => 'index'));
        }
        $refund = $this->Dealrefund->find('first', array(
            'conditions' => array('Dealrefund.id' => $refundId),
            'recursive' => 0
        ));
        if(empty($refund['Dealrefund']['id']))
        {
            $this->Session->setFlash(__('Invalid refund record.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        if(!$this->_isAllowedProject((int)$refund['Dealrefund']['project_id']))
        {
            $this->Session->setFlash(__('No Permission!'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'index'));
        }

        $this->_syncDeletedRefundVoucherSchedules($refundId);
        $this->_reconcileRefundScheduleBalance($refund['Dealrefund']);
        $dealData = $this->_fetchDealDetails((int)$refund['Dealrefund']['deal_id']);
        $scheduleRows = $this->Dealrefundschedule->find('all', array(
            'conditions' => array('Dealrefundschedule.refund_id' => $refundId),
            'order' => array('Dealrefundschedule.cycle_no' => 'asc'),
            'recursive' => -1
        ));
        $lastPaidScheduleId = 0;
        foreach((array)$scheduleRows as $scheduleRow)
        {
            $schedule = isset($scheduleRow['Dealrefundschedule']) ? $scheduleRow['Dealrefundschedule'] : array();
            if(!empty($schedule['id']) && isset($schedule['status']) && $schedule['status'] === 'Paid' && !empty($schedule['voucher_transaction_id']))
            {
                $lastPaidScheduleId = (int)$schedule['id'];
            }
        }
        $refundProgress = $this->_refundProgressSummary($refund['Dealrefund'], $scheduleRows);
        $refundClearanceLetterId = 0;
        if(!empty($refundProgress['progress_status']) && $refundProgress['progress_status'] === 'Completed')
        {
            try
            {
                $refundClearanceLetterId = (int)$this->_ensureRefundCompletionLetterIfCompleted($refundId);
            }
            catch(Exception $ignored)
            {
                // Keep the refund view available if auto letter creation is blocked.
            }
        }
        $expenseLedgerId = $this->_ardDefaultLedgerId((int)$refund['Dealrefund']['project_id'], 'refund_payable_ledger_id', 'Refund Payable', 'liability');
        $assetLedgerOptions = $this->_assetLedgerOptions((int)$refund['Dealrefund']['project_id']);

        $this->set(compact('refund','dealData','scheduleRows','refundProgress','refundClearanceLetterId','expenseLedgerId','assetLedgerOptions','lastPaidScheduleId'));
    }

    public function edit($refundId = null)
    {
        $refundId = (int)$refundId;
        if($refundId <= 0)
        {
            return $this->redirect(array('action' => 'index'));
        }
        $refundRow = $this->Dealrefund->find('first', array(
            'conditions' => array('Dealrefund.id' => $refundId),
            'recursive' => -1
        ));
        if(empty($refundRow['Dealrefund']['id']))
        {
            $this->Session->setFlash(__('Invalid refund record.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        if(!$this->_isAllowedProject((int)$refundRow['Dealrefund']['project_id']))
        {
            $this->Session->setFlash(__('No Permission!'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        if($refundRow['Dealrefund']['status'] !== 'Draft')
        {
            $this->Session->setFlash(__('Only Draft refunds can be edited.'),'flash',array('alert'=>'warning'));
            return $this->redirect(array('action' => 'view', $refundId));
        }

        if($this->request->is(array('post','put')))
        {
            $row = isset($this->request->data['Dealrefund']) ? $this->request->data['Dealrefund'] : array();
            $dealId = (int)$refundRow['Dealrefund']['deal_id'];
            $dealData = $this->_fetchDealDetails($dealId);
            $totalPaid = $this->_dealTotalPaid($dealId);
            $deductionPercent = isset($row['deduction_percent']) ? (float)str_replace(array(',', '%'), '', $row['deduction_percent']) : (float)$refundRow['Dealrefund']['deduction_percent'];
            $deductionAmount = round(($totalPaid * $deductionPercent) / 100, 2);
            $netRefund = round(max(0, $totalPaid - $deductionAmount), 2);
            $cycleCount = max(1, (int)(isset($row['cycle_count']) ? $row['cycle_count'] : $refundRow['Dealrefund']['cycle_count']));
            $cycleFrequency = trim((string)(isset($row['cycle_frequency']) ? $row['cycle_frequency'] : $refundRow['Dealrefund']['cycle_frequency']));
            if($cycleFrequency === ''){ $cycleFrequency = 'Monthly'; }
            $cycleStartDate = $this->_normalizeDate(isset($row['cycle_start_date']) ? $row['cycle_start_date'] : $refundRow['Dealrefund']['cycle_start_date']);
            $requestDate = $this->_normalizeDate(isset($row['request_date']) ? $row['request_date'] : $refundRow['Dealrefund']['request_date']);
            $perCycleAmount = ($cycleCount > 0) ? round($netRefund / $cycleCount, 2) : 0.0;

            $save = array(
                'id' => $refundId,
                'request_date' => $requestDate,
                'total_paid' => $totalPaid,
                'deduction_percent' => $deductionPercent,
                'deduction_amount' => $deductionAmount,
                'net_refund' => $netRefund,
                'cycle_count' => $cycleCount,
                'cycle_frequency' => $cycleFrequency,
                'cycle_start_date' => $cycleStartDate,
                'per_cycle_amount' => $perCycleAmount,
                'reason' => isset($row['reason']) ? $row['reason'] : $refundRow['Dealrefund']['reason'],
                'applicant_name' => isset($row['applicant_name']) ? $row['applicant_name'] : $refundRow['Dealrefund']['applicant_name'],
                'relation_type' => isset($row['relation_type']) ? $row['relation_type'] : (isset($refundRow['Dealrefund']['relation_type']) ? $refundRow['Dealrefund']['relation_type'] : ''),
                'father_name' => isset($row['father_name']) ? $row['father_name'] : $refundRow['Dealrefund']['father_name'],
                'cnic' => isset($row['cnic']) ? $row['cnic'] : $refundRow['Dealrefund']['cnic'],
                'passport_no' => isset($row['passport_no']) ? $row['passport_no'] : $refundRow['Dealrefund']['passport_no'],
                'address' => isset($row['address']) ? $row['address'] : $refundRow['Dealrefund']['address'],
                'mobile_no' => isset($row['mobile_no']) ? $row['mobile_no'] : $refundRow['Dealrefund']['mobile_no'],
                'email' => isset($row['email']) ? $row['email'] : $refundRow['Dealrefund']['email'],
                'account_title' => isset($row['account_title']) ? $row['account_title'] : $refundRow['Dealrefund']['account_title'],
                'account_no' => isset($row['account_no']) ? $row['account_no'] : $refundRow['Dealrefund']['account_no'],
                'bank_name' => isset($row['bank_name']) ? $row['bank_name'] : $refundRow['Dealrefund']['bank_name'],
                'bank_branch_city' => isset($row['bank_branch_city']) ? $row['bank_branch_city'] : $refundRow['Dealrefund']['bank_branch_city']
            );
            $this->Dealrefund->save(array('Dealrefund' => $save), array('validate' => false));
            $this->Session->setFlash(__('Refund updated successfully.'),'flash',array('alert'=>'success'));
            return $this->redirect(array('action' => 'view', $refundId));
        }

        $dealOptions = $this->_dealOptionsForRefund();
        $defaultDeduction = $this->_defaultRefundDeductionPercent((int)$refundRow['Dealrefund']['project_id']);
        $this->set(compact('refundRow','dealOptions','defaultDeduction'));
    }

    public function delete($refundId = null)
    {
        $refundId = (int)$refundId;
        if($refundId <= 0)
        {
            return $this->redirect(array('action' => 'index'));
        }
        if(!$this->_isSuperAdminSession((array)$this->adminValue))
        {
            $this->Session->setFlash(__('Only Super Admin can delete refund records.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        $refundRow = $this->Dealrefund->find('first', array(
            'conditions' => array('Dealrefund.id' => $refundId),
            'recursive' => -1
        ));
        if(empty($refundRow['Dealrefund']['id']))
        {
            $this->Session->setFlash(__('Invalid refund record.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        if(!$this->_isAllowedProject((int)$refundRow['Dealrefund']['project_id']))
        {
            $this->Session->setFlash(__('No Permission!'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        $paidCount = (int)$this->Dealrefundschedule->find('count', array(
            'conditions' => array(
                'Dealrefundschedule.refund_id' => $refundId,
                'Dealrefundschedule.status' => 'Paid'
            ),
            'recursive' => -1
        ));
        if($paidCount > 0)
        {
            $this->Session->setFlash(__('Refund cannot be deleted because payments have been recorded.'),'flash',array('alert'=>'warning'));
            return $this->redirect(array('action' => 'view', $refundId));
        }
        $dealId = (int)$refundRow['Dealrefund']['deal_id'];
        $this->Dealrefund->delete($refundId);
        if($dealId > 0)
        {
            $this->loadModel('Admin.Deal');
            if($this->Deal->hasField('deal_status'))
            {
                $this->Deal->id = $dealId;
                $this->Deal->save(array('Deal' => array(
                    'id' => $dealId,
                    'deal_status' => 'Active',
                    'deal_status_changed' => date('Y-m-d H:i:s')
                )), false, array('deal_status','deal_status_changed'));
            }
        }
        $this->Session->setFlash(__('Refund deleted successfully.'),'flash',array('alert'=>'success'));
        return $this->redirect(array('action' => 'index'));
    }

    public function finalize($refundId = null)
    {
        $refundId = (int)$refundId;
        if($refundId <= 0)
        {
            return $this->redirect(array('action' => 'index'));
        }
        $this->_finalizeRefund($refundId, true);
    }

    public function pay($scheduleId = null)
    {
        $scheduleId = (int)$scheduleId;
        if($scheduleId <= 0)
        {
            return $this->redirect(array('action' => 'index'));
        }
        $row = $this->Dealrefundschedule->find('first', array(
            'conditions' => array('Dealrefundschedule.id' => $scheduleId),
            'recursive' => -1
        ));
        if(empty($row['Dealrefundschedule']['id']))
        {
            $this->Session->setFlash(__('Invalid schedule.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        if(!$this->_isAllowedProject((int)$row['Dealrefundschedule']['project_id']))
        {
            $this->Session->setFlash(__('No Permission!'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        if($row['Dealrefundschedule']['status'] !== 'Unpaid')
        {
            $this->Session->setFlash(__('This schedule is already closed.'),'flash',array('alert'=>'warning'));
            return $this->redirect(array('action' => 'view', (int)$row['Dealrefundschedule']['refund_id']));
        }
        if(!$this->request->is(array('post','put')))
        {
            return $this->redirect(array('action' => 'view', (int)$row['Dealrefundschedule']['refund_id']));
        }
        $payData = isset($this->request->data['Dealrefundschedule']) ? $this->request->data['Dealrefundschedule'] : array();
        $ledgerId = isset($payData['paid_ledger_id']) ? (int)$payData['paid_ledger_id'] : 0;
        $paidDate = $this->_normalizeDate(isset($payData['paid_date']) ? $payData['paid_date'] : '');
        $paidAmount = isset($payData['paid_amount']) ? round((float)$payData['paid_amount'], 2) : round((float)$row['Dealrefundschedule']['amount'], 2);
        if($paidAmount <= 0){ $paidAmount = round((float)$row['Dealrefundschedule']['amount'], 2); }
        if($paidDate === ''){ $paidDate = date('Y-m-d'); }
        $adjustableCheck = $this->_validateRefundPaymentAdjustment($row['Dealrefundschedule'], $paidAmount);
        if(empty($adjustableCheck['ok']))
        {
            $this->Session->setFlash($adjustableCheck['message'],'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'view', (int)$row['Dealrefundschedule']['refund_id']));
        }

        $expenseLedgerId = $this->_ardDefaultLedgerId((int)$row['Dealrefundschedule']['project_id'], 'refund_payable_ledger_id', 'Refund Payable', 'liability');
        if($ledgerId <= 0 || $expenseLedgerId <= 0)
        {
            $this->Session->setFlash(__('Please select a valid payment account.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'view', (int)$row['Dealrefundschedule']['refund_id']));
        }

        $completionLetterId = 0;
        $ds = $this->Dealrefundschedule->getDataSource();
        $ds->begin();
        try
        {
            $refundClientName = $this->_refundScheduleClientName($row['Dealrefundschedule']);
            $voucherDescription = 'Refund payment for ';
            if($refundClientName !== '')
            {
                $voucherDescription .= $refundClientName.' - ';
            }
            $voucherDescription .= 'Deal #'.(int)$row['Dealrefundschedule']['deal_id'];
            $voucherDescription .= ' (Cycle '.$row['Dealrefundschedule']['cycle_no'].')';
            $txId = $this->_createRefundExpenseVoucher(
                (int)$row['Dealrefundschedule']['project_id'],
                $ledgerId,
                $expenseLedgerId,
                $paidAmount,
                $paidDate,
                $voucherDescription
            );
            if($txId <= 0)
            {
                throw new Exception(__('Unable to record refund payment.'));
            }

            $adjustmentNote = $this->_applyRefundPaymentAdjustment($row['Dealrefundschedule'], $paidAmount);
            $remarks = isset($payData['remarks']) ? trim((string)$payData['remarks']) : '';
            if($adjustmentNote !== '')
            {
                $remarks = $this->_appendRefundScheduleNote($remarks, $adjustmentNote);
            }

            $this->Dealrefundschedule->id = (int)$scheduleId;
            $saved = $this->Dealrefundschedule->save(array('Dealrefundschedule' => array(
                'id' => (int)$scheduleId,
                'amount' => $paidAmount,
                'status' => 'Paid',
                'paid_date' => $paidDate,
                'paid_amount' => $paidAmount,
                'paid_ledger_id' => $ledgerId,
                'voucher_transaction_id' => $txId,
                'remarks' => $remarks
            )), false, array('amount','status','paid_date','paid_amount','paid_ledger_id','voucher_transaction_id','remarks'));
            if(!$saved)
            {
                throw new Exception(__('Unable to update refund schedule.'));
            }

            $this->_recalculateLedgerBalance($ledgerId);
            $this->_recalculateLedgerBalance($expenseLedgerId);
            $ds->commit();
            try
            {
                $completionLetterId = $this->_ensureRefundCompletionLetterIfCompleted((int)$row['Dealrefundschedule']['refund_id']);
            }
            catch(Exception $ignored)
            {
                $completionLetterId = 0;
            }
        }
        catch(Exception $e)
        {
            $ds->rollback();
            $this->Session->setFlash($e->getMessage(),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'view', (int)$row['Dealrefundschedule']['refund_id']));
        }

        $successMessage = __('Refund payment recorded and voucher created.');
        if($completionLetterId > 0)
        {
            $successMessage .= ' '.__('Refund clearance letter auto-generated.');
        }
        $this->Session->setFlash($successMessage,'flash',array('alert'=>'success'));
        return $this->redirect(array('action' => 'view', (int)$row['Dealrefundschedule']['refund_id']));
    }

    public function editpayment($scheduleId = null)
    {
        $scheduleId = (int)$scheduleId;
        if($scheduleId <= 0)
        {
            return $this->redirect(array('action' => 'index'));
        }
        if(!$this->request->is(array('post','put')))
        {
            return $this->redirect(array('action' => 'index'));
        }
        $row = $this->Dealrefundschedule->find('first', array(
            'conditions' => array('Dealrefundschedule.id' => $scheduleId),
            'recursive' => -1
        ));
        if(empty($row['Dealrefundschedule']['id']))
        {
            $this->Session->setFlash(__('Invalid schedule.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        $schedule = $row['Dealrefundschedule'];
        $refundId = (int)$schedule['refund_id'];
        if(!$this->_isAllowedProject((int)$schedule['project_id']))
        {
            $this->Session->setFlash(__('No Permission!'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'view', $refundId));
        }
        if($schedule['status'] !== 'Paid' || (int)$schedule['voucher_transaction_id'] <= 0)
        {
            $this->Session->setFlash(__('Only paid refund entries can be edited.'),'flash',array('alert'=>'warning'));
            return $this->redirect(array('action' => 'view', $refundId));
        }
        if(!$this->_isLastPaidRefundSchedule($schedule))
        {
            $this->Session->setFlash(__('Only the last paid refund entry can be edited.'),'flash',array('alert'=>'warning'));
            return $this->redirect(array('action' => 'view', $refundId));
        }

        $payData = isset($this->request->data['Dealrefundschedule']) ? $this->request->data['Dealrefundschedule'] : array();
        $ledgerId = isset($payData['paid_ledger_id']) ? (int)$payData['paid_ledger_id'] : 0;
        $paidDate = $this->_normalizeDate(isset($payData['paid_date']) ? $payData['paid_date'] : '');
        $paidAmount = isset($payData['paid_amount']) ? round((float)$payData['paid_amount'], 2) : round((float)$schedule['paid_amount'], 2);
        if($paidAmount <= 0)
        {
            $this->Session->setFlash(__('Paid amount should be greater than zero.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'view', $refundId));
        }
        if($paidDate === ''){ $paidDate = date('Y-m-d'); }
        $expenseLedgerId = $this->_ardDefaultLedgerId((int)$schedule['project_id'], 'refund_payable_ledger_id', 'Refund Payable', 'liability');
        if($ledgerId <= 0 || $expenseLedgerId <= 0)
        {
            $this->Session->setFlash(__('Please select a valid payment account.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'view', $refundId));
        }

        $refund = $this->Dealrefund->find('first', array(
            'conditions' => array('Dealrefund.id' => $refundId),
            'recursive' => -1
        ));
        if(empty($refund['Dealrefund']['id']))
        {
            $this->Session->setFlash(__('Invalid refund record.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        $maximumPayable = $this->_refundRemainingAmountForScheduleEdit($refund['Dealrefund'], $scheduleId);
        if($paidAmount > ($maximumPayable + 0.01))
        {
            $this->Session->setFlash(__('Paid amount cannot be greater than the remaining refund balance.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'view', $refundId));
        }

        $ds = $this->Dealrefundschedule->getDataSource();
        $ds->begin();
        try
        {
            $oldTxId = (int)$schedule['voucher_transaction_id'];
            $oldLedgerIds = $this->_deleteRefundVoucherTransaction($oldTxId);
            $refundClientName = $this->_refundScheduleClientName($schedule);
            $voucherDescription = 'Refund payment edited for ';
            if($refundClientName !== '')
            {
                $voucherDescription .= $refundClientName.' - ';
            }
            $voucherDescription .= 'Deal #'.(int)$schedule['deal_id'];
            $voucherDescription .= ' (Cycle '.$schedule['cycle_no'].')';
            $txId = $this->_createRefundExpenseVoucher(
                (int)$schedule['project_id'],
                $ledgerId,
                $expenseLedgerId,
                $paidAmount,
                $paidDate,
                $voucherDescription
            );
            if($txId <= 0)
            {
                throw new Exception(__('Unable to record updated refund payment.'));
            }
            $remarks = isset($payData['remarks']) ? trim((string)$payData['remarks']) : '';
            $remarks = $this->_appendRefundScheduleNote($remarks, __('Payment edited from voucher #%s.', $oldTxId));
            $this->Dealrefundschedule->id = $scheduleId;
            $saved = $this->Dealrefundschedule->save(array('Dealrefundschedule' => array(
                'id' => $scheduleId,
                'amount' => $paidAmount,
                'status' => 'Paid',
                'paid_date' => $paidDate,
                'paid_amount' => $paidAmount,
                'paid_ledger_id' => $ledgerId,
                'voucher_transaction_id' => $txId,
                'remarks' => $remarks
            )), false, array('amount','status','paid_date','paid_amount','paid_ledger_id','voucher_transaction_id','remarks'));
            if(!$saved)
            {
                throw new Exception(__('Unable to update refund schedule.'));
            }
            $this->_rebalanceRefundSchedulesAfterPaidChange($refund['Dealrefund']);
            $ledgerIds = array($ledgerId => $ledgerId, $expenseLedgerId => $expenseLedgerId);
            foreach((array)$oldLedgerIds as $oldLedgerId)
            {
                if((int)$oldLedgerId > 0)
                {
                    $ledgerIds[(int)$oldLedgerId] = (int)$oldLedgerId;
                }
            }
            foreach($ledgerIds as $recalcLedgerId)
            {
                $this->_recalculateLedgerBalance($recalcLedgerId);
            }
            $ds->commit();
        }
        catch(Exception $e)
        {
            $ds->rollback();
            $this->Session->setFlash($e->getMessage(),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'view', $refundId));
        }

        $this->Session->setFlash(__('Refund payment updated successfully.'),'flash',array('alert'=>'success'));
        return $this->redirect(array('action' => 'view', $refundId));
    }

    public function deletepayment($scheduleId = null)
    {
        $scheduleId = (int)$scheduleId;
        if($scheduleId <= 0)
        {
            return $this->redirect(array('action' => 'index'));
        }
        if(!$this->request->is('post'))
        {
            throw new MethodNotAllowedException(__('Invalid request method.'));
        }
        $row = $this->Dealrefundschedule->find('first', array(
            'conditions' => array('Dealrefundschedule.id' => $scheduleId),
            'recursive' => -1
        ));
        if(empty($row['Dealrefundschedule']['id']))
        {
            $this->Session->setFlash(__('Invalid schedule.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        $schedule = $row['Dealrefundschedule'];
        $refundId = (int)$schedule['refund_id'];
        if(!$this->_isAllowedProject((int)$schedule['project_id']))
        {
            $this->Session->setFlash(__('No Permission!'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'view', $refundId));
        }
        if($schedule['status'] !== 'Paid' || (int)$schedule['voucher_transaction_id'] <= 0)
        {
            $this->Session->setFlash(__('Only paid refund entries can be deleted.'),'flash',array('alert'=>'warning'));
            return $this->redirect(array('action' => 'view', $refundId));
        }
        if(!$this->_isLastPaidRefundSchedule($schedule))
        {
            $this->Session->setFlash(__('Only the last paid refund entry can be deleted.'),'flash',array('alert'=>'warning'));
            return $this->redirect(array('action' => 'view', $refundId));
        }
        $refund = $this->Dealrefund->find('first', array(
            'conditions' => array('Dealrefund.id' => $refundId),
            'recursive' => -1
        ));
        if(empty($refund['Dealrefund']['id']))
        {
            $this->Session->setFlash(__('Invalid refund record.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'index'));
        }

        $ds = $this->Dealrefundschedule->getDataSource();
        $ds->begin();
        try
        {
            $oldLedgerIds = $this->_deleteRefundVoucherTransaction((int)$schedule['voucher_transaction_id']);
            $remarks = $this->_appendRefundScheduleNote(isset($schedule['remarks']) ? $schedule['remarks'] : '', __('Payment voucher #%s deleted. Schedule reopened.', (int)$schedule['voucher_transaction_id']));
            $reopenAmount = !empty($schedule['paid_amount']) ? round((float)$schedule['paid_amount'], 2) : round((float)$schedule['amount'], 2);
            $this->Dealrefundschedule->id = $scheduleId;
            $saved = $this->Dealrefundschedule->save(array('Dealrefundschedule' => array(
                'id' => $scheduleId,
                'amount' => $reopenAmount,
                'status' => 'Unpaid',
                'paid_date' => null,
                'paid_amount' => 0,
                'paid_ledger_id' => 0,
                'voucher_transaction_id' => 0,
                'remarks' => $remarks
            )), false, array('amount','status','paid_date','paid_amount','paid_ledger_id','voucher_transaction_id','remarks'));
            if(!$saved)
            {
                throw new Exception(__('Unable to reopen refund schedule.'));
            }
            $this->_rebalanceRefundSchedulesAfterPaidChange($refund['Dealrefund']);
            foreach((array)$oldLedgerIds as $oldLedgerId)
            {
                $this->_recalculateLedgerBalance((int)$oldLedgerId);
            }
            $ds->commit();
        }
        catch(Exception $e)
        {
            $ds->rollback();
            $this->Session->setFlash($e->getMessage(),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'view', $refundId));
        }

        $this->Session->setFlash(__('Last refund payment deleted and schedule reopened.'),'flash',array('alert'=>'success'));
        return $this->redirect(array('action' => 'view', $refundId));
    }

    protected function _isLastPaidRefundSchedule($schedule)
    {
        $schedule = (array)$schedule;
        $refundId = isset($schedule['refund_id']) ? (int)$schedule['refund_id'] : 0;
        $scheduleId = isset($schedule['id']) ? (int)$schedule['id'] : 0;
        if($refundId <= 0 || $scheduleId <= 0)
        {
            return false;
        }
        $last = $this->Dealrefundschedule->find('first', array(
            'fields' => array('Dealrefundschedule.id'),
            'conditions' => array(
                'Dealrefundschedule.refund_id' => $refundId,
                'Dealrefundschedule.status' => 'Paid',
                'Dealrefundschedule.voucher_transaction_id >' => 0
            ),
            'order' => array('Dealrefundschedule.cycle_no' => 'desc', 'Dealrefundschedule.id' => 'desc'),
            'recursive' => -1
        ));
        return (!empty($last['Dealrefundschedule']['id']) && (int)$last['Dealrefundschedule']['id'] === $scheduleId);
    }

    protected function _refundRemainingAmountForScheduleEdit($refundRow, $editingScheduleId)
    {
        $refundRow = (array)$refundRow;
        $refundId = isset($refundRow['id']) ? (int)$refundRow['id'] : 0;
        $editingScheduleId = (int)$editingScheduleId;
        $netRefund = isset($refundRow['net_refund']) ? round((float)$refundRow['net_refund'], 2) : 0.0;
        if($refundId <= 0)
        {
            return 0.0;
        }
        $paidRows = $this->Dealrefundschedule->find('all', array(
            'fields' => array('Dealrefundschedule.id','Dealrefundschedule.paid_amount','Dealrefundschedule.amount'),
            'conditions' => array(
                'Dealrefundschedule.refund_id' => $refundId,
                'Dealrefundschedule.status' => 'Paid',
                'Dealrefundschedule.voucher_transaction_id >' => 0,
                'Dealrefundschedule.id !=' => $editingScheduleId
            ),
            'recursive' => -1
        ));
        $otherPaidTotal = 0.0;
        foreach((array)$paidRows as $paidRow)
        {
            $schedule = isset($paidRow['Dealrefundschedule']) ? $paidRow['Dealrefundschedule'] : array();
            $amount = isset($schedule['paid_amount']) ? round((float)$schedule['paid_amount'], 2) : 0.0;
            if($amount <= 0)
            {
                $amount = isset($schedule['amount']) ? round((float)$schedule['amount'], 2) : 0.0;
            }
            $otherPaidTotal = round($otherPaidTotal + $amount, 2);
        }
        return round(max(0, $netRefund - $otherPaidTotal), 2);
    }

    protected function _deleteRefundVoucherTransaction($transactionId)
    {
        $transactionId = (int)$transactionId;
        if($transactionId <= 0)
        {
            return array();
        }
        $this->loadModel('Admin.Ardtransaction');
        $this->loadModel('Admin.Ardjournalentry');
        $ledgerIds = array();
        $txRow = $this->Ardtransaction->find('first', array(
            'fields' => array('Ardtransaction.ledger_id','Ardtransaction.expense_ledger_id','Ardtransaction.income_ledger_id'),
            'conditions' => array('Ardtransaction.id' => $transactionId),
            'recursive' => -1
        ));
        if(!empty($txRow['Ardtransaction']))
        {
            foreach(array('ledger_id','expense_ledger_id','income_ledger_id') as $field)
            {
                if(!empty($txRow['Ardtransaction'][$field]))
                {
                    $ledgerIds[(int)$txRow['Ardtransaction'][$field]] = (int)$txRow['Ardtransaction'][$field];
                }
            }
        }
        try
        {
            $this->Ardjournalentry->deleteAll(array('Ardjournalentry.transaction_id' => $transactionId), false);
        }
        catch(Exception $e)
        {
            // Older accounts installs may not have journal rows for refund vouchers.
        }
        if(!$this->Ardtransaction->delete($transactionId))
        {
            throw new Exception(__('Unable to delete old refund voucher.'));
        }
        return array_values($ledgerIds);
    }

    protected function _rebalanceRefundSchedulesAfterPaidChange($refundRow)
    {
        $refundRow = (array)$refundRow;
        $refundId = isset($refundRow['id']) ? (int)$refundRow['id'] : 0;
        if($refundId <= 0)
        {
            return false;
        }
        $this->_reconcileRefundScheduleBalance($refundRow);
        $progress = $this->_refundProgressSummary($refundRow);
        $balance = isset($progress['balance']) ? round((float)$progress['balance'], 2) : 0.0;
        $unpaidTotal = isset($progress['unpaid_schedule_total']) ? round((float)$progress['unpaid_schedule_total'], 2) : 0.0;
        if($balance <= 0.009 || $unpaidTotal > 0.009)
        {
            return true;
        }
        $targetRow = $this->Dealrefundschedule->find('first', array(
            'conditions' => array(
                'Dealrefundschedule.refund_id' => $refundId,
                'Dealrefundschedule.status !=' => 'Paid'
            ),
            'order' => array('Dealrefundschedule.cycle_no' => 'asc', 'Dealrefundschedule.id' => 'asc'),
            'recursive' => -1
        ));
        if(!empty($targetRow['Dealrefundschedule']['id']))
        {
            $target = $targetRow['Dealrefundschedule'];
            $remarks = $this->_appendRefundScheduleNote(isset($target['remarks']) ? $target['remarks'] : '', __('Schedule reopened after refund payment correction: %s', number_format($balance, 2)));
            $this->Dealrefundschedule->id = (int)$target['id'];
            return (bool)$this->Dealrefundschedule->save(array('Dealrefundschedule' => array(
                'id' => (int)$target['id'],
                'amount' => $balance,
                'status' => 'Unpaid',
                'remarks' => $remarks
            )), false, array('amount','status','remarks'));
        }

        $last = $this->Dealrefundschedule->find('first', array(
            'conditions' => array('Dealrefundschedule.refund_id' => $refundId),
            'order' => array('Dealrefundschedule.cycle_no' => 'desc', 'Dealrefundschedule.id' => 'desc'),
            'recursive' => -1
        ));
        if(!empty($last['Dealrefundschedule']))
        {
            $this->_createCarryForwardRefundSchedule($last['Dealrefundschedule'], $balance);
        }
        return true;
    }

    protected function _refundScheduleClientName($schedule)
    {
        $schedule = (array)$schedule;
        $name = '';
        $clientId = isset($schedule['client_id']) ? (int)$schedule['client_id'] : 0;
        if($clientId > 0)
        {
            try
            {
                $clientRow = $this->Dealrefundschedule->query("SELECT `name` FROM `clients` WHERE `id`=".$clientId." LIMIT 1");
                if(!empty($clientRow[0]['clients']['name']))
                {
                    $name = trim((string)$clientRow[0]['clients']['name']);
                }
                elseif(!empty($clientRow[0]['clients']['Name']))
                {
                    $name = trim((string)$clientRow[0]['clients']['Name']);
                }
                elseif(!empty($clientRow[0]['name']))
                {
                    $name = trim((string)$clientRow[0]['name']);
                }
            }
            catch(Exception $e)
            {
                $name = '';
            }
        }
        if($name === '' && !empty($schedule['refund_id']))
        {
            $refund = $this->Dealrefund->find('first', array(
                'fields' => array('Dealrefund.applicant_name'),
                'conditions' => array('Dealrefund.id' => (int)$schedule['refund_id']),
                'recursive' => -1
            ));
            if(!empty($refund['Dealrefund']['applicant_name']))
            {
                $name = trim((string)$refund['Dealrefund']['applicant_name']);
            }
        }
        return $name;
    }

    protected function _validateRefundPaymentAdjustment($schedule, $paidAmount)
    {
        $schedule = (array)$schedule;
        $paidAmount = round((float)$paidAmount, 2);
        $scheduleAmount = round((float)(isset($schedule['amount']) ? $schedule['amount'] : 0), 2);
        if($paidAmount <= 0)
        {
            return array('ok' => false, 'message' => __('Paid amount should be greater than zero.'));
        }
        if($scheduleAmount <= 0)
        {
            return array('ok' => false, 'message' => __('This schedule has no payable amount.'));
        }
        $difference = round($scheduleAmount - $paidAmount, 2);
        if(abs($difference) < 0.01)
        {
            return array('ok' => true, 'message' => '');
        }

        $futureConditions = array(
            'Dealrefundschedule.refund_id' => (int)$schedule['refund_id'],
            'Dealrefundschedule.id !=' => (int)$schedule['id'],
            'Dealrefundschedule.cycle_no >' => (int)$schedule['cycle_no'],
            'Dealrefundschedule.status' => 'Unpaid',
            'Dealrefundschedule.amount >' => 0
        );
        if($difference > 0)
        {
            return array('ok' => true, 'message' => '');
        }

        $futureRows = $this->Dealrefundschedule->find('all', array(
            'fields' => array('Dealrefundschedule.amount'),
            'conditions' => $futureConditions,
            'recursive' => -1
        ));
        $futureTotal = 0.0;
        foreach((array)$futureRows as $futureRow)
        {
            $futureTotal += round((float)$futureRow['Dealrefundschedule']['amount'], 2);
        }
        $maximumPayable = round($scheduleAmount + $futureTotal, 2);
        if($paidAmount > ($maximumPayable + 0.01))
        {
            return array('ok' => false, 'message' => __('Paid amount cannot be greater than the remaining refund schedule balance.'));
        }
        return array('ok' => true, 'message' => '');
    }

    protected function _applyRefundPaymentAdjustment($schedule, $paidAmount)
    {
        $schedule = (array)$schedule;
        $paidAmount = round((float)$paidAmount, 2);
        $scheduleAmount = round((float)(isset($schedule['amount']) ? $schedule['amount'] : 0), 2);
        $difference = round($scheduleAmount - $paidAmount, 2);
        if(abs($difference) < 0.01)
        {
            return '';
        }

        if($difference > 0)
        {
            return $this->_carryShortRefundToNextSchedule($schedule, $difference);
        }
        return $this->_adjustExtraRefundFromUpcomingSchedules($schedule, abs($difference));
    }

    protected function _carryShortRefundToNextSchedule($schedule, $shortAmount)
    {
        $shortAmount = round((float)$shortAmount, 2);
        if($shortAmount <= 0)
        {
            return '';
        }
        $next = $this->Dealrefundschedule->find('first', array(
            'conditions' => array(
                'Dealrefundschedule.refund_id' => (int)$schedule['refund_id'],
                'Dealrefundschedule.id !=' => (int)$schedule['id'],
                'Dealrefundschedule.cycle_no >' => (int)$schedule['cycle_no'],
                'Dealrefundschedule.status' => 'Unpaid',
                'Dealrefundschedule.amount >' => 0
            ),
            'order' => array('Dealrefundschedule.cycle_no' => 'asc', 'Dealrefundschedule.id' => 'asc'),
            'recursive' => -1
        ));
        if(empty($next['Dealrefundschedule']['id']))
        {
            return $this->_createCarryForwardRefundSchedule($schedule, $shortAmount);
        }

        $nextSchedule = $next['Dealrefundschedule'];
        $newAmount = round((float)$nextSchedule['amount'] + $shortAmount, 2);
        $note = __('Carry forward from cycle %s: %s', (int)$schedule['cycle_no'], number_format($shortAmount, 2));
        $nextRemarks = $this->_appendRefundScheduleNote(isset($nextSchedule['remarks']) ? $nextSchedule['remarks'] : '', $note);
        $this->Dealrefundschedule->id = (int)$nextSchedule['id'];
        $saved = $this->Dealrefundschedule->save(array('Dealrefundschedule' => array(
            'id' => (int)$nextSchedule['id'],
            'amount' => $newAmount,
            'remarks' => $nextRemarks
        )), false, array('amount','remarks'));
        if(!$saved)
        {
            throw new Exception(__('Unable to adjust next refund schedule.'));
        }
        return __('Short payment %s carried forward to cycle %s.', number_format($shortAmount, 2), (int)$nextSchedule['cycle_no']);
    }

    protected function _createCarryForwardRefundSchedule($schedule, $shortAmount)
    {
        $schedule = (array)$schedule;
        $shortAmount = round((float)$shortAmount, 2);
        if($shortAmount <= 0)
        {
            return '';
        }
        $refundId = isset($schedule['refund_id']) ? (int)$schedule['refund_id'] : 0;
        if($refundId <= 0)
        {
            throw new Exception(__('Unable to create carry forward refund schedule.'));
        }

        $last = $this->Dealrefundschedule->find('first', array(
            'fields' => array('Dealrefundschedule.cycle_no', 'Dealrefundschedule.scheduled_date'),
            'conditions' => array('Dealrefundschedule.refund_id' => $refundId),
            'order' => array('Dealrefundschedule.cycle_no' => 'desc', 'Dealrefundschedule.id' => 'desc'),
            'recursive' => -1
        ));
        $lastCycleNo = !empty($last['Dealrefundschedule']['cycle_no']) ? (int)$last['Dealrefundschedule']['cycle_no'] : (int)(isset($schedule['cycle_no']) ? $schedule['cycle_no'] : 0);
        $lastDate = !empty($last['Dealrefundschedule']['scheduled_date']) ? $last['Dealrefundschedule']['scheduled_date'] : (isset($schedule['scheduled_date']) ? $schedule['scheduled_date'] : '');
        $nextCycleNo = $lastCycleNo + 1;
        $nextDate = $this->_nextRefundScheduleDate($refundId, $lastDate);
        $note = __('Auto created carry forward from cycle %s: %s', (int)$schedule['cycle_no'], number_format($shortAmount, 2));

        $newRow = array(
            'refund_id' => $refundId,
            'deal_id' => isset($schedule['deal_id']) ? (int)$schedule['deal_id'] : 0,
            'client_id' => isset($schedule['client_id']) ? (int)$schedule['client_id'] : 0,
            'project_id' => isset($schedule['project_id']) ? (int)$schedule['project_id'] : 0,
            'cycle_no' => $nextCycleNo,
            'scheduled_date' => $nextDate,
            'amount' => $shortAmount,
            'status' => 'Unpaid',
            'paid_amount' => 0,
            'paid_ledger_id' => 0,
            'voucher_transaction_id' => 0,
            'remarks' => $this->_appendRefundScheduleNote('', $note)
        );
        $this->Dealrefundschedule->create();
        if(!$this->Dealrefundschedule->save(array('Dealrefundschedule' => $newRow), array('validate' => false)))
        {
            throw new Exception(__('Unable to create carry forward refund schedule.'));
        }

        $refundCycle = $this->Dealrefund->find('first', array(
            'fields' => array('Dealrefund.cycle_count'),
            'conditions' => array('Dealrefund.id' => $refundId),
            'recursive' => -1
        ));
        $savedCycleCount = !empty($refundCycle['Dealrefund']['cycle_count']) ? (int)$refundCycle['Dealrefund']['cycle_count'] : 0;
        $this->Dealrefund->id = $refundId;
        $this->Dealrefund->saveField('cycle_count', max($savedCycleCount, $nextCycleNo), array('validate' => false));

        return __('Short payment %s carried forward to new cycle %s.', number_format($shortAmount, 2), $nextCycleNo);
    }

    protected function _nextRefundScheduleDate($refundId, $baseDate)
    {
        $refundId = (int)$refundId;
        $baseDate = trim((string)$baseDate);
        if($baseDate === '' || $baseDate === '0000-00-00')
        {
            $baseDate = date('Y-m-d');
        }
        $monthStep = 1;
        if($refundId > 0)
        {
            $refund = $this->Dealrefund->find('first', array(
                'fields' => array('Dealrefund.cycle_frequency'),
                'conditions' => array('Dealrefund.id' => $refundId),
                'recursive' => -1
            ));
            $frequency = !empty($refund['Dealrefund']['cycle_frequency']) ? strtolower(trim((string)$refund['Dealrefund']['cycle_frequency'])) : 'monthly';
            if($frequency === 'quarterly'){$monthStep = 3;}
            elseif($frequency === 'biannually' || $frequency === 'bi-annual' || $frequency === 'bianuly'){$monthStep = 6;}
            elseif($frequency === 'yearly'){$monthStep = 12;}
        }
        $ts = strtotime('+'.$monthStep.' months', strtotime($baseDate));
        if($ts === false)
        {
            $ts = strtotime('+'.$monthStep.' months');
        }
        return date('Y-m-d', $ts);
    }

    protected function _adjustExtraRefundFromUpcomingSchedules($schedule, $extraAmount)
    {
        $extraAmount = round((float)$extraAmount, 2);
        if($extraAmount <= 0)
        {
            return '';
        }
        $futureRows = $this->Dealrefundschedule->find('all', array(
            'conditions' => array(
                'Dealrefundschedule.refund_id' => (int)$schedule['refund_id'],
                'Dealrefundschedule.id !=' => (int)$schedule['id'],
                'Dealrefundschedule.cycle_no >' => (int)$schedule['cycle_no'],
                'Dealrefundschedule.status' => 'Unpaid',
                'Dealrefundschedule.amount >' => 0
            ),
            'order' => array('Dealrefundschedule.cycle_no' => 'asc', 'Dealrefundschedule.id' => 'asc'),
            'recursive' => -1
        ));
        if(empty($futureRows))
        {
            throw new Exception(__('Extra refund payment needs an upcoming unpaid schedule for adjustment.'));
        }

        $remaining = $extraAmount;
        $touchedCycles = array();
        foreach((array)$futureRows as $futureRow)
        {
            if($remaining <= 0.009)
            {
                break;
            }
            $future = $futureRow['Dealrefundschedule'];
            $futureAmount = round((float)$future['amount'], 2);
            if($futureAmount <= 0)
            {
                continue;
            }
            $adjusted = min($futureAmount, $remaining);
            $newAmount = round($futureAmount - $adjusted, 2);
            $remaining = round($remaining - $adjusted, 2);
            $futureStatus = ($newAmount <= 0.009) ? 'Adjusted' : 'Unpaid';
            if($futureStatus === 'Adjusted')
            {
                $newAmount = 0.0;
            }
            $note = __('Adjusted by extra payment from cycle %s: %s', (int)$schedule['cycle_no'], number_format($adjusted, 2));
            $futureRemarks = $this->_appendRefundScheduleNote(isset($future['remarks']) ? $future['remarks'] : '', $note);
            $this->Dealrefundschedule->id = (int)$future['id'];
            $saved = $this->Dealrefundschedule->save(array('Dealrefundschedule' => array(
                'id' => (int)$future['id'],
                'amount' => $newAmount,
                'status' => $futureStatus,
                'remarks' => $futureRemarks
            )), false, array('amount','status','remarks'));
            if(!$saved)
            {
                throw new Exception(__('Unable to adjust upcoming refund schedule.'));
            }
            $touchedCycles[] = (int)$future['cycle_no'];
        }
        if($remaining > 0.009)
        {
            throw new Exception(__('Paid amount cannot be greater than the remaining refund schedule balance.'));
        }
        $cycleText = !empty($touchedCycles) ? implode(', ', $touchedCycles) : '';
        return __('Extra payment %s adjusted with upcoming cycle(s): %s.', number_format($extraAmount, 2), $cycleText);
    }

    protected function _appendRefundScheduleNote($existing, $note)
    {
        $existing = trim((string)$existing);
        $note = trim((string)$note);
        if($note === '')
        {
            return $existing;
        }
        $line = '['.date('Y-m-d H:i').'] '.$note;
        if($existing === '')
        {
            return $line;
        }
        return $existing."\n".$line;
    }

    protected function _syncDeletedRefundVoucherSchedules($refundId = 0)
    {
        $refundId = (int)$refundId;
        $conditions = array(
            'Dealrefundschedule.status' => 'Paid'
        );
        if($refundId > 0)
        {
            $conditions['Dealrefundschedule.refund_id'] = $refundId;
        }
        $paidRows = $this->Dealrefundschedule->find('all', array(
            'conditions' => $conditions,
            'recursive' => -1
        ));
        if(empty($paidRows))
        {
            return 0;
        }

        $this->loadModel('Admin.Ardtransaction');
        $resetCount = 0;
        foreach((array)$paidRows as $paidRow)
        {
            $schedule = isset($paidRow['Dealrefundschedule']) ? $paidRow['Dealrefundschedule'] : array();
            $txId = isset($schedule['voucher_transaction_id']) ? (int)$schedule['voucher_transaction_id'] : 0;
            $exists = 0;
            if($txId > 0)
            {
                $exists = (int)$this->Ardtransaction->find('count', array(
                    'conditions' => array('Ardtransaction.id' => $txId),
                    'recursive' => -1
                ));
            }
            if($txId > 0 && $exists > 0)
            {
                continue;
            }
            $note = $txId > 0 ? __('Voucher #%s was deleted or missing. Schedule reset to unpaid so refund balance is recalculated.', $txId) : __('Paid refund schedule had no voucher. Schedule reset to unpaid so refund balance is recalculated.');
            $remarks = $this->_appendRefundScheduleNote(isset($schedule['remarks']) ? $schedule['remarks'] : '', $note);
            $this->Dealrefundschedule->id = (int)$schedule['id'];
            if($this->Dealrefundschedule->save(array('Dealrefundschedule' => array(
                'id' => (int)$schedule['id'],
                'status' => 'Unpaid',
                'paid_date' => null,
                'paid_amount' => 0,
                'paid_ledger_id' => 0,
                'voucher_transaction_id' => 0,
                'remarks' => $remarks
            )), false, array('status','paid_date','paid_amount','paid_ledger_id','voucher_transaction_id','remarks')))
            {
                $resetCount++;
            }
        }
        return $resetCount;
    }

    protected function _refundProgressSummary($refundRow, $scheduleRows = null)
    {
        $refundRow = (array)$refundRow;
        $refundId = isset($refundRow['id']) ? (int)$refundRow['id'] : 0;
        if($scheduleRows === null && $refundId > 0)
        {
            $scheduleRows = $this->Dealrefundschedule->find('all', array(
                'conditions' => array('Dealrefundschedule.refund_id' => $refundId),
                'order' => array('Dealrefundschedule.cycle_no' => 'asc'),
                'recursive' => -1
            ));
        }
        $netRefund = isset($refundRow['net_refund']) ? round((float)$refundRow['net_refund'], 2) : 0.0;
        $lifecycleStatus = isset($refundRow['status']) ? trim((string)$refundRow['status']) : '';
        $paidTotal = 0.0;
        $unpaidScheduleTotal = 0.0;
        $paidCount = 0;
        $unpaidCount = 0;
        $scheduleCount = 0;
        foreach((array)$scheduleRows as $row)
        {
            $schedule = isset($row['Dealrefundschedule']) ? $row['Dealrefundschedule'] : array();
            if(empty($schedule))
            {
                continue;
            }
            $scheduleCount++;
            $status = isset($schedule['status']) ? trim((string)$schedule['status']) : '';
            $amount = isset($schedule['amount']) ? round((float)$schedule['amount'], 2) : 0.0;
            if($status === 'Paid')
            {
                $txId = isset($schedule['voucher_transaction_id']) ? (int)$schedule['voucher_transaction_id'] : 0;
                if($txId <= 0)
                {
                    continue;
                }
                $paidAmount = isset($schedule['paid_amount']) ? round((float)$schedule['paid_amount'], 2) : 0.0;
                if($paidAmount <= 0)
                {
                    $paidAmount = $amount;
                }
                $paidTotal = round($paidTotal + $paidAmount, 2);
                $paidCount++;
            }
            elseif($status === 'Unpaid')
            {
                $unpaidScheduleTotal = round($unpaidScheduleTotal + max(0, $amount), 2);
                if($amount > 0)
                {
                    $unpaidCount++;
                }
            }
        }
        $balance = round(max(0, $netRefund - $paidTotal), 2);
        if(strtolower($lifecycleStatus) === 'draft' && $scheduleCount <= 0)
        {
            $progressStatus = 'Draft';
        }
        elseif($balance <= 0.009 && $scheduleCount > 0)
        {
            $progressStatus = 'Completed';
        }
        elseif($paidTotal > 0)
        {
            $progressStatus = 'Partial Pending';
        }
        else
        {
            $progressStatus = 'Pending';
        }
        return array(
            'lifecycle_status' => $lifecycleStatus,
            'progress_status' => $progressStatus,
            'net_refund' => $netRefund,
            'paid_total' => $paidTotal,
            'balance' => $balance,
            'unpaid_schedule_total' => $unpaidScheduleTotal,
            'paid_count' => $paidCount,
            'unpaid_count' => $unpaidCount,
            'schedule_count' => $scheduleCount
        );
    }

    protected function _reconcileRefundScheduleBalance($refundRow)
    {
        $refundRow = (array)$refundRow;
        $refundId = isset($refundRow['id']) ? (int)$refundRow['id'] : 0;
        if($refundId <= 0)
        {
            return false;
        }
        $scheduleRows = $this->Dealrefundschedule->find('all', array(
            'conditions' => array('Dealrefundschedule.refund_id' => $refundId),
            'order' => array('Dealrefundschedule.cycle_no' => 'asc', 'Dealrefundschedule.id' => 'asc'),
            'recursive' => -1
        ));
        if(empty($scheduleRows))
        {
            return false;
        }
        $netRefund = isset($refundRow['net_refund']) ? round((float)$refundRow['net_refund'], 2) : 0.0;
        $paidTotal = 0.0;
        $unpaidTotal = 0.0;
        $unpaidRows = array();
        foreach((array)$scheduleRows as $row)
        {
            $schedule = isset($row['Dealrefundschedule']) ? $row['Dealrefundschedule'] : array();
            $status = isset($schedule['status']) ? trim((string)$schedule['status']) : '';
            $amount = isset($schedule['amount']) ? round((float)$schedule['amount'], 2) : 0.0;
            if($status === 'Paid')
            {
                $txId = isset($schedule['voucher_transaction_id']) ? (int)$schedule['voucher_transaction_id'] : 0;
                if($txId <= 0)
                {
                    continue;
                }
                $paidAmount = isset($schedule['paid_amount']) ? round((float)$schedule['paid_amount'], 2) : 0.0;
                if($paidAmount <= 0)
                {
                    $paidAmount = $amount;
                }
                $paidTotal = round($paidTotal + $paidAmount, 2);
            }
            elseif($status === 'Unpaid' && $amount > 0)
            {
                $unpaidTotal = round($unpaidTotal + $amount, 2);
                $unpaidRows[] = $schedule;
            }
        }
        if(empty($unpaidRows))
        {
            return false;
        }
        $balance = round(max(0, $netRefund - $paidTotal), 2);
        $difference = round($balance - $unpaidTotal, 2);
        if(abs($difference) < 0.01)
        {
            return false;
        }

        if($difference > 0)
        {
            $target = $unpaidRows[0];
            $newAmount = round((float)$target['amount'] + $difference, 2);
            $note = __('Schedule reconciled with refund balance. Carry forward added: %s', number_format($difference, 2));
            $remarks = $this->_appendRefundScheduleNote(isset($target['remarks']) ? $target['remarks'] : '', $note);
            $this->Dealrefundschedule->id = (int)$target['id'];
            return (bool)$this->Dealrefundschedule->save(array('Dealrefundschedule' => array(
                'id' => (int)$target['id'],
                'amount' => $newAmount,
                'remarks' => $remarks
            )), false, array('amount','remarks'));
        }

        $remainingReduction = abs($difference);
        $changed = false;
        foreach($unpaidRows as $target)
        {
            if($remainingReduction <= 0.009)
            {
                break;
            }
            $amount = round((float)$target['amount'], 2);
            if($amount <= 0)
            {
                continue;
            }
            $reduceBy = min($amount, $remainingReduction);
            $newAmount = round($amount - $reduceBy, 2);
            $remainingReduction = round($remainingReduction - $reduceBy, 2);
            $newStatus = ($newAmount <= 0.009) ? 'Adjusted' : 'Unpaid';
            if($newStatus === 'Adjusted')
            {
                $newAmount = 0.0;
            }
            $note = __('Schedule reconciled with refund balance. Upcoming amount reduced: %s', number_format($reduceBy, 2));
            $remarks = $this->_appendRefundScheduleNote(isset($target['remarks']) ? $target['remarks'] : '', $note);
            $this->Dealrefundschedule->id = (int)$target['id'];
            if($this->Dealrefundschedule->save(array('Dealrefundschedule' => array(
                'id' => (int)$target['id'],
                'amount' => $newAmount,
                'status' => $newStatus,
                'remarks' => $remarks
            )), false, array('amount','status','remarks')))
            {
                $changed = true;
            }
        }
        return $changed;
    }

    public function agreement($refundId = null)
    {
        $refundId = (int)$refundId;
        $this->autoRender = false;
        $this->layout = null;
        if($refundId <= 0)
        {
            throw new NotFoundException(__('Invalid refund.'));
        }
        $refund = $this->Dealrefund->find('first', array(
            'conditions' => array('Dealrefund.id' => $refundId),
            'recursive' => 0
        ));
        if(empty($refund['Dealrefund']['id']))
        {
            throw new NotFoundException(__('Invalid refund.'));
        }
        if(!$this->_isAllowedProject((int)$refund['Dealrefund']['project_id']))
        {
            throw new NotFoundException(__('No Permission.'));
        }
        $letterId = $this->_ensureRefundAgreementLetter($refund['Dealrefund']);
        if ($letterId > 0)
        {
            return $this->redirect(array('controller' => 'Realestateletters', 'action' => 'printletter', (int)$letterId));
        }
        throw new NotFoundException(__('Refund agreement template not found.'));
    }

    public function clearance($refundId = null)
    {
        $refundId = (int)$refundId;
        $this->autoRender = false;
        $this->layout = null;
        if($refundId <= 0)
        {
            throw new NotFoundException(__('Invalid refund.'));
        }
        $refund = $this->Dealrefund->find('first', array(
            'conditions' => array('Dealrefund.id' => $refundId),
            'recursive' => -1
        ));
        if(empty($refund['Dealrefund']['id']))
        {
            throw new NotFoundException(__('Invalid refund.'));
        }
        if(!$this->_isAllowedProject((int)$refund['Dealrefund']['project_id']))
        {
            throw new NotFoundException(__('No Permission.'));
        }
        $letterId = $this->_ensureRefundCompletionLetterIfCompleted($refundId);
        if($letterId > 0)
        {
            return $this->redirect(array('controller' => 'Realestateletters', 'action' => 'printletter', (int)$letterId));
        }
        $this->Session->setFlash(__('Refund clearance letter can be printed after the refund schedule is completed.'),'flash',array('alert'=>'warning'));
        return $this->redirect(array('action' => 'view', $refundId));
    }

    public function dealmeta($dealId = null)
    {
        $this->autoRender = false;
        $this->layout = null;
        $dealId = (int)$dealId;
        $metaRow = $this->_fetchDealMetaRow($dealId);
        if(empty($metaRow['Deal']['id']))
        {
            return $this->_json(array('ok' => 0, 'message' => 'Invalid deal'));
        }
        $projectId = isset($metaRow['Property']['project_id']) ? (int)$metaRow['Property']['project_id'] : 0;
        if($projectId > 0 && !$this->_isAllowedProject($projectId))
        {
            return $this->_json(array('ok' => 0, 'message' => 'No permission'));
        }
        $totalPaid = $this->_dealTotalPaid($dealId);
        $meta = array(
            'ok' => 1,
            'deal_id' => (int)$dealId,
            'client_name' => isset($metaRow['Client']['name']) ? $metaRow['Client']['name'] : '',
            'client_relation_type' => isset($metaRow['Client']['relation_type']) && trim((string)$metaRow['Client']['relation_type']) !== '' ? trim((string)$metaRow['Client']['relation_type']) : 'Father',
            'client_father_name' => isset($metaRow['Client']['fathername']) ? $metaRow['Client']['fathername'] : '',
            'client_cnic' => isset($metaRow['Client']['pincode']) ? $metaRow['Client']['pincode'] : '',
            'client_phone' => isset($metaRow['Client']['phone']) ? $metaRow['Client']['phone'] : (isset($metaRow['Client']['mobile']) ? $metaRow['Client']['mobile'] : ''),
            'client_email' => isset($metaRow['Client']['email']) ? $metaRow['Client']['email'] : '',
            'client_address' => isset($metaRow['Client']['address']) ? $metaRow['Client']['address'] : '',
            'total_paid' => $totalPaid
        );
        return $this->_json($meta);
    }

    protected function _json($payload)
    {
        header('Content-Type: application/json');
        echo json_encode($payload);
        return;
    }

    protected function _finalizeRefund($refundId, $redirectAfter = false)
    {
        $refund = $this->Dealrefund->find('first', array(
            'conditions' => array('Dealrefund.id' => (int)$refundId),
            'recursive' => -1
        ));
        if(empty($refund['Dealrefund']['id']))
        {
            return $this->redirect(array('action' => 'index'));
        }
        if(!$this->_isAllowedProject((int)$refund['Dealrefund']['project_id']))
        {
            $this->Session->setFlash(__('No Permission!'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        if((string)$refund['Dealrefund']['status'] !== 'Finalized')
        {
            $this->Dealrefund->id = (int)$refundId;
            $this->Dealrefund->saveField('status', 'Finalized', array('validate' => false));
        }
        $this->_generateRefundSchedules($refund['Dealrefund']);
        $this->_markDealRefundRunning((int)$refund['Dealrefund']['deal_id']);
        $this->_ensureRefundAgreementLetter($refund['Dealrefund']);
        $this->_syncRefundAccountingVouchers($refund['Dealrefund']);
        if($redirectAfter)
        {
            $this->Session->setFlash(__('Refund finalized. Schedule generated and deal moved to Refund-Running.'),'flash',array('alert'=>'success'));
            return $this->redirect(array('action' => 'view', $refundId));
        }
        return null;
    }

    protected function _syncRefundAccountingVouchers($refundRow)
    {
        $refund = (array)$refundRow;
        $refundId = isset($refund['id']) ? (int)$refund['id'] : 0;
        $projectId = isset($refund['project_id']) ? (int)$refund['project_id'] : 0;
        if($refundId <= 0 || $projectId <= 0)
        {
            return false;
        }
        $requestDate = !empty($refund['request_date']) ? $refund['request_date'] : date('Y-m-d');
        $netRefund = isset($refund['net_refund']) ? round((float)$refund['net_refund'], 2) : 0;
        $deductionAmount = isset($refund['deduction_amount']) ? round((float)$refund['deduction_amount'], 2) : 0;
        $payableTxId = !empty($refund['payable_transaction_id']) ? (int)$refund['payable_transaction_id'] : 0;
        $forfeitureTxId = !empty($refund['forfeiture_transaction_id']) ? (int)$refund['forfeiture_transaction_id'] : 0;
        $saveFields = array('id' => $refundId);

        if($netRefund > 0)
        {
            $refundExpenseLedgerId = $this->_ardDefaultLedgerId($projectId, 'discount_waiver_ledger_id', 'Refund Expense', 'expense');
            $refundPayableLedgerId = $this->_ardDefaultLedgerId($projectId, 'refund_payable_ledger_id', 'Refund Payable', 'liability');
            if($refundExpenseLedgerId > 0 && $refundPayableLedgerId > 0)
            {
                $txId = $this->_ardPostAutoVoucher(
                    $projectId,
                    $refundPayableLedgerId,
                    'expense',
                    $netRefund,
                    'RFD-PAY-'.$refundId,
                    $requestDate,
                    'Refund payable created for refund #'.$refundId,
                    array(
                        'existing_id' => $payableTxId,
                        'expense_ledger_id' => $refundExpenseLedgerId,
                        'source_type' => 'deal_refund_payable',
                        'source_id' => $refundId,
                        'status' => 'Posted'
                    )
                );
                if($txId > 0)
                {
                    $saveFields['payable_transaction_id'] = (int)$txId;
                }
            }
        }
        else
        {
            $this->_ardVoidAutoVoucherBySource($projectId, 'deal_refund_payable', $refundId, 'Refund payable removed');
            $saveFields['payable_transaction_id'] = 0;
        }

        if($deductionAmount > 0)
        {
            $refundPayableLedgerId = $this->_ardDefaultLedgerId($projectId, 'refund_payable_ledger_id', 'Refund Payable', 'liability');
            $forfeitureLedgerId = $this->_ardDefaultLedgerId($projectId, 'cancellation_forfeiture_income_ledger_id', 'Cancellation / Forfeiture Income', 'income');
            if($refundPayableLedgerId > 0 && $forfeitureLedgerId > 0)
            {
                $txId = $this->_ardPostAutoVoucher(
                    $projectId,
                    $refundPayableLedgerId,
                    'income',
                    $deductionAmount,
                    'RFD-FORF-'.$refundId,
                    $requestDate,
                    'Refund deduction / forfeiture for refund #'.$refundId,
                    array(
                        'existing_id' => $forfeitureTxId,
                        'income_ledger_id' => $forfeitureLedgerId,
                        'source_type' => 'deal_refund_forfeiture',
                        'source_id' => $refundId,
                        'status' => 'Posted'
                    )
                );
                if($txId > 0)
                {
                    $saveFields['forfeiture_transaction_id'] = (int)$txId;
                }
            }
        }
        else
        {
            $this->_ardVoidAutoVoucherBySource($projectId, 'deal_refund_forfeiture', $refundId, 'Refund deduction removed');
            $saveFields['forfeiture_transaction_id'] = 0;
        }

        if(count($saveFields) > 1)
        {
            try
            {
                $this->Dealrefund->save(array('Dealrefund' => $saveFields), false);
            }
            catch(Exception $e) {}
        }
        return true;
    }

    protected function _ensureRefundAgreementLetter($refundRow)
    {
        $refundRow = (array)$refundRow;
        $refundId = isset($refundRow['id']) ? (int)$refundRow['id'] : 0;
        if ($refundId <= 0)
        {
            return 0;
        }
        $dealId = isset($refundRow['deal_id']) ? (int)$refundRow['deal_id'] : 0;
        $clientId = isset($refundRow['client_id']) ? (int)$refundRow['client_id'] : 0;
        $projectId = isset($refundRow['project_id']) ? (int)$refundRow['project_id'] : 0;
        if ($dealId <= 0)
        {
            return 0;
        }
        $this->loadModel('Admin.Realestateletter');
        $this->loadModel('Template');

        $existingLetter = $this->Realestateletter->find('first', array(
            'fields' => array('Realestateletter.id'),
            'conditions' => array(
                'Realestateletter.refund_id' => $refundId,
                'Realestateletter.letter_type' => 'RLT_REFUND'
            ),
            'order' => array('Realestateletter.id' => 'asc'),
            'recursive' => -1
        ));
        if (!empty($existingLetter['Realestateletter']['id']))
        {
            return (int)$existingLetter['Realestateletter']['id'];
        }

        $templateId = $this->_refundTemplateId();
        if ($templateId <= 0)
        {
            return 0;
        }
        $template = $this->Template->find('first', array(
            'conditions' => array('Template.id' => $templateId),
            'fields' => array('Template.id', 'Template.description'),
            'recursive' => -1
        ));
        $templateBody = isset($template['Template']['description']) ? (string)$template['Template']['description'] : '';

        $referenceNo = $this->_nextRefundReferenceNo($projectId);
        $dealRow = $this->_fetchDealDetails($dealId);
        $title = 'Refund Agreement';
        if (!empty($dealRow['Deal']['invoice_no']))
        {
            $title .= ' - ' . $dealRow['Deal']['invoice_no'];
        }

        $this->Realestateletter->create();
        if ($this->Realestateletter->save(array('Realestateletter' => array(
            'deal_id' => $dealId,
            'client_id' => $clientId > 0 ? $clientId : null,
            'template_id' => (int)$templateId,
            'user_id' => (int)$this->luserId,
            'refund_id' => $refundId,
            'letter_type' => 'RLT_REFUND',
            'reference_no' => $referenceNo,
            'title' => $title,
            'letter_date' => date('Y-m-d'),
            'description' => $templateBody,
            'status' => 'Published'
        )), array('validate' => false)))
        {
            return (int)$this->Realestateletter->id;
        }
        return 0;
    }

    protected function _refundCompletionTemplateBody()
    {
        $file = APP . 'Lib' . DS . 'LetterTemplates' . DS . 'refund_completion_clearance.html';
        if (is_readable($file))
        {
            $body = file_get_contents($file);
            if (trim((string)$body) !== '')
            {
                return (string)$body;
            }
        }
        return '<p><strong>Date:</strong> {{refund_completion_date}}<br><strong>Reference:</strong> {{letter_ref_no}}</p><p>Refund clearance has been issued for <strong>{{client_name}}</strong>, deal <strong>{{deal_invoice_no}}</strong>.</p><p><strong>Deal Refund ID:</strong> {{deal_refund_id}}<br><strong>Refund Amount:</strong> {{refund_net_refund}}</p><div style="width:150px;height:150px;">{{refund_completion_qr}}</div>';
    }

    protected function _refundCompletionTemplateId()
    {
        $this->loadModel('Template');
        $typeCode = 'RLT_REFUND_CLEARANCE';
        $rows = (array)$this->Template->find('all', array(
            'fields' => array('Template.id', 'Template.status', 'Template.is_new'),
            'conditions' => array('Template.type' => $typeCode),
            'order' => array('Template.id' => 'asc'),
            'recursive' => -1
        ));
        $fallbackId = 0;
        $publishedId = 0;
        foreach ($rows as $row)
        {
            $id = isset($row['Template']['id']) ? (int)$row['Template']['id'] : 0;
            if ($id <= 0)
            {
                continue;
            }
            if ($fallbackId <= 0)
            {
                $fallbackId = $id;
            }
            $status = isset($row['Template']['status']) ? trim((string)$row['Template']['status']) : '';
            $isNew = isset($row['Template']['is_new']) ? strtolower(trim((string)$row['Template']['is_new'])) : '';
            if ($status === 'Published' && $publishedId <= 0)
            {
                $publishedId = $id;
            }
            if ($status === 'Published' && $isNew !== 'yes')
            {
                return $id;
            }
        }
        if ($publishedId > 0)
        {
            return $publishedId;
        }
        if ($fallbackId > 0)
        {
            return $fallbackId;
        }

        $templateData = array(
            'name' => 'Refund Clearance / No Claim',
            'description' => $this->_refundCompletionTemplateBody(),
            'type' => $typeCode,
            'status' => 'Published',
            'is_new' => 'No'
        );
        $this->Template->create();
        if ($this->Template->save(array('Template' => $templateData), false))
        {
            return (int)$this->Template->id;
        }
        return 0;
    }

    protected function _ensureRefundCompletionLetterIfCompleted($refundId)
    {
        $refundId = (int)$refundId;
        if ($refundId <= 0)
        {
            return 0;
        }
        $refund = $this->Dealrefund->find('first', array(
            'conditions' => array('Dealrefund.id' => $refundId),
            'recursive' => -1
        ));
        if (empty($refund['Dealrefund']['id']))
        {
            return 0;
        }
        if (!$this->_isAllowedProject((int)$refund['Dealrefund']['project_id']))
        {
            return 0;
        }
        $scheduleRows = $this->Dealrefundschedule->find('all', array(
            'conditions' => array('Dealrefundschedule.refund_id' => $refundId),
            'order' => array('Dealrefundschedule.cycle_no' => 'asc'),
            'recursive' => -1
        ));
        $progress = $this->_refundProgressSummary($refund['Dealrefund'], $scheduleRows);
        if (empty($progress['progress_status']) || $progress['progress_status'] !== 'Completed')
        {
            return 0;
        }

        $this->loadModel('Admin.Realestateletter');
        $existingLetter = $this->Realestateletter->find('first', array(
            'fields' => array('Realestateletter.id'),
            'conditions' => array(
                'Realestateletter.refund_id' => $refundId,
                'Realestateletter.letter_type' => 'RLT_REFUND_CLEARANCE'
            ),
            'order' => array('Realestateletter.id' => 'asc'),
            'recursive' => -1
        ));
        if (!empty($existingLetter['Realestateletter']['id']))
        {
            return (int)$existingLetter['Realestateletter']['id'];
        }

        $templateId = $this->_refundCompletionTemplateId();
        if ($templateId <= 0)
        {
            return 0;
        }
        $this->loadModel('Template');
        $template = $this->Template->find('first', array(
            'conditions' => array('Template.id' => $templateId),
            'fields' => array('Template.id', 'Template.description'),
            'recursive' => -1
        ));
        $templateBody = isset($template['Template']['description']) ? (string)$template['Template']['description'] : $this->_refundCompletionTemplateBody();

        $dealId = isset($refund['Dealrefund']['deal_id']) ? (int)$refund['Dealrefund']['deal_id'] : 0;
        $clientId = isset($refund['Dealrefund']['client_id']) ? (int)$refund['Dealrefund']['client_id'] : 0;
        if ($dealId <= 0)
        {
            return 0;
        }
        $dealRow = $this->_fetchDealDetails($dealId);
        $title = 'Refund Clearance / No Claim';
        if (!empty($dealRow['Deal']['invoice_no']))
        {
            $title .= ' - ' . $dealRow['Deal']['invoice_no'];
        }

        $this->Realestateletter->create();
        if ($this->Realestateletter->save(array('Realestateletter' => array(
            'deal_id' => $dealId,
            'client_id' => $clientId > 0 ? $clientId : null,
            'template_id' => (int)$templateId,
            'user_id' => (int)$this->luserId,
            'refund_id' => $refundId,
            'letter_type' => 'RLT_REFUND_CLEARANCE',
            'reference_no' => $this->_nextRefundReferenceNo((int)$refund['Dealrefund']['project_id']),
            'title' => $title,
            'letter_date' => date('Y-m-d'),
            'description' => $templateBody,
            'status' => 'Published'
        )), array('validate' => false)))
        {
            return (int)$this->Realestateletter->id;
        }
        return 0;
    }

    protected function _refundTemplateId()
    {
        $this->loadModel('Template');
        $rows = (array)$this->Template->find('all', array(
            'fields' => array('Template.id', 'Template.status', 'Template.is_new'),
            'conditions' => array('Template.type' => 'RLT_REFUND'),
            'order' => array('Template.id' => 'asc'),
            'recursive' => -1
        ));
        if (empty($rows))
        {
            return 0;
        }
        $fallbackId = 0;
        $publishedId = 0;
        foreach ($rows as $row)
        {
            $id = isset($row['Template']['id']) ? (int)$row['Template']['id'] : 0;
            if ($id <= 0)
            {
                continue;
            }
            if ($fallbackId <= 0)
            {
                $fallbackId = $id;
            }
            $status = isset($row['Template']['status']) ? trim((string)$row['Template']['status']) : '';
            $isNew = isset($row['Template']['is_new']) ? strtolower(trim((string)$row['Template']['is_new'])) : '';
            if ($status === 'Published' && $publishedId <= 0)
            {
                $publishedId = $id;
            }
            if ($status === 'Published' && $isNew !== 'yes')
            {
                return $id;
            }
        }
        if ($publishedId > 0)
        {
            return $publishedId;
        }
        return $fallbackId;
    }

    protected function _nextRefundReferenceNo($projectId)
    {
        $projectId = (int)$projectId;
        $this->loadModel('Admin.Ardsetting');
        $settings = $this->Ardsetting->find('first', array(
            'conditions' => array('Ardsetting.project_id' => $projectId),
            'recursive' => -1
        ));
        $row = !empty($settings['Ardsetting']) ? $settings['Ardsetting'] : array('project_id' => $projectId);
        $prefix = isset($row['refund_letter_prefix']) ? trim((string)$row['refund_letter_prefix']) : '';
        if ($prefix === '')
        {
            $prefix = isset($row['letters_prefix']) ? trim((string)$row['letters_prefix']) : '';
        }
        if ($prefix === '')
        {
            $prefix = 'RFD-' . date('Y') . '-';
        }
        if (strlen($prefix) > 20)
        {
            $prefix = substr($prefix, 0, 20);
        }
        $nextNo = isset($row['refund_letter_next_no']) ? (int)$row['refund_letter_next_no'] : 1;
        if ($nextNo <= 0)
        {
            $nextNo = 1;
        }
        $this->loadModel('Admin.Realestateletter');
        $candidate = '';
        $guard = 0;
        while (true)
        {
            $candidate = $prefix . $nextNo;
            $exists = (int)$this->Realestateletter->find('count', array(
                'conditions' => array('Realestateletter.reference_no' => $candidate),
                'recursive' => -1
            ));
            if ($exists <= 0)
            {
                break;
            }
            $nextNo++;
            $guard++;
            if ($guard > 100000)
            {
                break;
            }
        }
        $rowSave = array('project_id' => $projectId, 'refund_letter_prefix' => $prefix, 'refund_letter_next_no' => $nextNo + 1);
        if (!empty($settings['Ardsetting']['id']))
        {
            $rowSave['id'] = (int)$settings['Ardsetting']['id'];
        }
        $this->Ardsetting->save(array('Ardsetting' => $rowSave), array('validate' => false));
        return $candidate;
    }

    protected function _dealOptionsForRefund()
    {
        $this->loadModel('Admin.Deal');
        $conditions = array();
        if(!$this->_isSuperAdminSession((array)$this->adminValue))
        {
            $conditions['Property.project_id'] = (int)$this->agencyId;
        }
        $rows = $this->Deal->find('all', array(
            'fields' => array('Deal.id','Deal.invoice_no','Client.name','Property.name','Property.project_id'),
            'joins' => array(
                array(
                    'table' => 'clients',
                    'alias' => 'Client',
                    'type' => 'LEFT',
                    'conditions' => array('Client.id = Deal.client_id')
                ),
                array(
                    'table' => 'properties',
                    'alias' => 'Property',
                    'type' => 'LEFT',
                    'conditions' => array('Property.id = Deal.property_id')
                )
            ),
            'conditions' => $conditions,
            'order' => array('Deal.id' => 'desc'),
            'recursive' => -1
        ));
        $options = array();
        foreach($rows as $row)
        {
            $dealId = (int)$row['Deal']['id'];
            $label = trim((string)$row['Deal']['invoice_no']);
            if($label === '')
            {
                $label = 'Deal #'.$dealId;
            }
            $clientName = isset($row['Client']['name']) ? $row['Client']['name'] : '';
            $propertyName = isset($row['Property']['name']) ? $row['Property']['name'] : '';
            $labelParts = array();
            if($clientName !== ''){ $labelParts[] = $clientName; }
            $labelParts[] = $label;
            $labelParts[] = '';
            if($propertyName !== ''){ $labelParts[] = $propertyName; }
            $options[$dealId] = implode(' | ', $labelParts);
        }
        return $options;
    }

    protected function _defaultRefundDeductionPercent($projectId = null)
    {
        $this->loadModel('Admin.Ardsetting');
        $projectId = (int)$projectId;
        if($projectId <= 0)
        {
            $projectId = (int)$this->agencyId;
        }
        $row = $this->Ardsetting->find('first', array(
            'conditions' => array('Ardsetting.project_id' => $projectId),
            'recursive' => -1
        ));
        if(!empty($row['Ardsetting']['refund_deduction_percent']))
        {
            return (float)$row['Ardsetting']['refund_deduction_percent'];
        }
        return 10.0;
    }

    protected function _fetchDealDetails($dealId)
    {
        $this->loadModel('Admin.Deal');
        $dealId = (int)$dealId;
        if($dealId <= 0)
        {
            return array();
        }
        $dealData = $this->Deal->find('first', array(
            'fields' => array(
                'Deal.id',
                'Deal.invoice_no',
                'Deal.client_id',
                'Deal.property_id',
                'Deal.date',
                'Client.id',
                'Client.name',
                'Client.relation_type',
                'Client.fathername',
                'Client.pincode',
                'Client.phone',
                'Client.mobile',
                'Client.email',
                'Client.address',
                'Property.id',
                'Property.name',
                'Property.project_id',
                'Property.price',
                'Property.area'
            ),
            'joins' => array(
                array(
                    'table' => 'clients',
                    'alias' => 'Client',
                    'type' => 'LEFT',
                    'conditions' => array('Client.id = Deal.client_id')
                ),
                array(
                    'table' => 'properties',
                    'alias' => 'Property',
                    'type' => 'LEFT',
                    'conditions' => array('Property.id = Deal.property_id')
                )
            ),
            'conditions' => array('Deal.id' => $dealId),
            'recursive' => -1
        ));
        return $dealData;
    }

    protected function _fetchDealMetaRow($dealId)
    {
        $this->loadModel('Admin.Deal');
        $dealId = (int)$dealId;
        if($dealId <= 0)
        {
            return array();
        }
        $rows = $this->Deal->find('first', array(
            'fields' => array(
                'Deal.id',
                'Deal.client_id',
                'Deal.property_id',
                'Client.name',
                'Client.relation_type',
                'Client.fathername',
                'Client.pincode',
                'Client.phone',
                'Client.mobile',
                'Client.email',
                'Client.address',
                'Property.project_id'
            ),
            'joins' => array(
                array(
                    'table' => 'clients',
                    'alias' => 'Client',
                    'type' => 'LEFT',
                    'conditions' => array('Client.id = Deal.client_id')
                ),
                array(
                    'table' => 'properties',
                    'alias' => 'Property',
                    'type' => 'LEFT',
                    'conditions' => array('Property.id = Deal.property_id')
                )
            ),
            'conditions' => array('Deal.id' => $dealId),
            'recursive' => -1
        ));
        return is_array($rows) ? $rows : array();
    }

    protected function _dealTotalPaid($dealId)
    {
        $this->loadModel('Admin.DealsPayment');
        $dealId = (int)$dealId;
        if($dealId <= 0)
        {
            return 0.0;
        }
        $row = $this->DealsPayment->find('first', array(
            'fields' => array('SUM(DealsPayment.emi) AS paid_total'),
            'conditions' => array('DealsPayment.deal_id' => $dealId),
            'recursive' => -1
        ));
        if(isset($row[0]['paid_total']))
        {
            return (float)$row[0]['paid_total'];
        }
        return 0.0;
    }

    protected function _normalizeDate($raw)
    {
        $raw = trim((string)$raw);
        if($raw === '')
        {
            return '';
        }
        $ts = strtotime($raw);
        if($ts === false)
        {
            return '';
        }
        return date('Y-m-d', $ts);
    }

    protected function _generateRefundSchedules($refund)
    {
        $refundId = (int)$refund['id'];
        if($refundId <= 0)
        {
            return;
        }
        $existingCount = $this->Dealrefundschedule->find('count', array(
            'conditions' => array('Dealrefundschedule.refund_id' => $refundId)
        ));
        if($existingCount > 0)
        {
            return;
        }
        $cycleCount = max(1, (int)$refund['cycle_count']);
        $netRefund = (float)$refund['net_refund'];
        $perCycle = ($cycleCount > 0) ? round($netRefund / $cycleCount, 2) : 0.0;
        if($perCycle <= 0)
        {
            $perCycle = (float)$refund['per_cycle_amount'];
        }
        $startDate = $refund['cycle_start_date'];
        if($startDate === '' || $startDate === null)
        {
            $startDate = date('Y-m-d');
        }
        $frequency = strtolower(trim((string)$refund['cycle_frequency']));
        $monthStep = 1;
        if($frequency === 'quarterly'){$monthStep = 3;}
        elseif($frequency === 'biannually' || $frequency === 'bi-annual' || $frequency === 'bianuly'){$monthStep = 6;}
        elseif($frequency === 'yearly'){$monthStep = 12;}

        $rows = array();
        $remaining = $netRefund;
        for($i=1; $i<=$cycleCount; $i++)
        {
            $amount = ($i === $cycleCount) ? round($remaining, 2) : $perCycle;
            if($amount < 0){ $amount = 0; }
            $remaining = round($remaining - $amount, 2);
            $scheduleDate = date('Y-m-d', strtotime('+'.(($i-1) * $monthStep).' months', strtotime($startDate)));
            $rows[] = array(
                'refund_id' => $refundId,
                'deal_id' => (int)$refund['deal_id'],
                'client_id' => (int)$refund['client_id'],
                'project_id' => (int)$refund['project_id'],
                'cycle_no' => $i,
                'scheduled_date' => $scheduleDate,
                'amount' => $amount,
                'status' => 'Unpaid'
            );
        }
        if(!empty($rows))
        {
            $this->Dealrefundschedule->saveAll($rows);
        }
    }

    protected function _markDealRefundRunning($dealId)
    {
        $this->loadModel('Admin.Deal');
        $dealId = (int)$dealId;
        if($dealId <= 0)
        {
            return;
        }
        if(!$this->Deal->hasField('deal_status'))
        {
            return;
        }
        $this->Deal->id = $dealId;
        $this->Deal->save(array('Deal' => array(
            'id' => $dealId,
            'deal_status' => 'Refund-Running',
            'deal_status_changed' => date('Y-m-d H:i:s')
        )), false, array('deal_status','deal_status_changed'));
    }

    protected function _assetLedgerOptions($projectId)
    {
        $this->loadModel('Admin.Ardledger');
        $projectId = (int)$projectId;
        $options = array(0 => __('Please Select'));
        if($projectId <= 0)
        {
            return $options;
        }
        $rows = $this->Ardledger->find('list', array(
            'fields' => array('Ardledger.id', 'Ardledger.name'),
            'conditions' => array(
                'Ardledger.project_id' => $projectId,
                'Ardledger.is_trashed' => 0,
                'Ardledger.type !=' => 'expense'
            ),
            'order' => array('Ardledger.name' => 'asc'),
            'recursive' => -1
        ));
        return $options + (array)$rows;
    }

    protected function _ensureRefundExpenseLedger($projectId)
    {
        $this->loadModel('Admin.Ardledger');
        $projectId = (int)$projectId;
        if($projectId <= 0)
        {
            return 0;
        }
        $row = $this->Ardledger->find('first', array(
            'fields' => array('Ardledger.id'),
            'conditions' => array(
                'Ardledger.project_id' => $projectId,
                'Ardledger.type' => 'expense',
                'Ardledger.name' => 'Refund-Deals',
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
            'name' => 'Refund-Deals',
            'type' => 'expense',
            'category' => 'Refund',
            'balance' => 0
        )), array('validate' => false)))
        {
            return (int)$this->Ardledger->id;
        }
        return 0;
    }

    protected function _createRefundExpenseVoucher($projectId, $ledgerId, $expenseLedgerId, $amount, $entryDate, $description)
    {
        $this->loadModel('Admin.Ardtransaction');
        $projectId = (int)$projectId;
        $ledgerId = (int)$ledgerId;
        $expenseLedgerId = (int)$expenseLedgerId;
        $amount = (float)$amount;
        if($projectId <= 0 || $ledgerId <= 0 || $expenseLedgerId <= 0 || $amount <= 0)
        {
            return 0;
        }
        $refNo = $this->_nextVoucherReferenceNo($projectId, 'expense');
        $tx = array(
            'project_id' => $projectId,
            'ledger_id' => $ledgerId,
            'expense_ledger_id' => $expenseLedgerId,
            'type' => 'expense',
            'status' => 'Posted',
            'amount' => $amount,
            'description' => $description,
            'ref_no' => $refNo,
            'entry_date' => $entryDate,
            'source_type' => 'deal_refund',
            'source_id' => 0,
            'auto_posted' => 1,
            'posted_by' => (int)$this->luserId,
            'posted_at' => date('Y-m-d H:i:s'),
            'modified_by' => (int)$this->luserId,
            'created_by' => (int)$this->luserId
        );
        $this->Ardtransaction->create();
        if($this->Ardtransaction->save(array('Ardtransaction' => $tx), array('validate' => false)))
        {
            return (int)$this->Ardtransaction->id;
        }
        return 0;
    }

    protected function _nextVoucherReferenceNo($projectId, $type = 'expense')
    {
        $txType = (strtolower(trim((string)$type)) === 'income') ? 'income' : 'expense';
        return $this->_nextReferenceNo((int)$projectId, $txType, ($txType === 'income' ? 'INC-' : 'EXP-'));
    }

    protected function _nextReferenceNo($projectId, $sequenceKey, $fallbackPrefix = 'REF-')
    {
        $this->loadModel('Admin.Ardsetting');
        $projectId = (int)$projectId;
        $sequenceMap = array(
            'income' => array('prefix_field' => 'income_voucher_prefix', 'next_no_field' => 'income_voucher_next_no'),
            'expense' => array('prefix_field' => 'expense_voucher_prefix', 'next_no_field' => 'expense_voucher_next_no')
        );
        $sequenceKey = strtolower(trim((string)$sequenceKey));
        if(!isset($sequenceMap[$sequenceKey]))
        {
            $fallbackPrefix = trim((string)$fallbackPrefix);
            if($fallbackPrefix === '')
            {
                $fallbackPrefix = 'REF-';
            }
            return $fallbackPrefix . date('YmdHis');
        }
        $prefixField = $sequenceMap[$sequenceKey]['prefix_field'];
        $nextNoField = $sequenceMap[$sequenceKey]['next_no_field'];
        $defaults = array(
            'income_voucher_prefix' => 'INC-',
            'income_voucher_next_no' => 1,
            'expense_voucher_prefix' => 'EXP-',
            'expense_voucher_next_no' => 1
        );
        $settingRow = $this->Ardsetting->find('first', array(
            'conditions' => array('Ardsetting.project_id' => $projectId),
            'recursive' => -1
        ));
        $settingData = $defaults;
        if(!empty($settingRow['Ardsetting']))
        {
            $settingData = array_merge($settingData, $settingRow['Ardsetting']);
        }
        $prefix = trim((string)(isset($settingData[$prefixField]) ? $settingData[$prefixField] : ''));
        if($prefix === '')
        {
            $prefix = $defaults[$prefixField];
        }
        if(strlen($prefix) > 20)
        {
            $prefix = substr($prefix, 0, 20);
        }
        $nextNo = max(1, (int)(isset($settingData[$nextNoField]) ? $settingData[$nextNoField] : 1));
        $refNo = $prefix . $nextNo;
        $settingData[$prefixField] = $prefix;
        $settingData[$nextNoField] = $nextNo + 1;
        $settingData['project_id'] = $projectId;
        if(!empty($settingRow['Ardsetting']['id']))
        {
            $settingData['id'] = (int)$settingRow['Ardsetting']['id'];
        }
        $this->Ardsetting->create();
        $this->Ardsetting->save(array('Ardsetting' => $settingData), false);
        return $refNo;
    }

    protected function _recalculateLedgerBalance($ledgerId)
    {
        return $this->_ardRecalculateLedgerBalanceCompat($ledgerId);
    }

    protected function _isAllowedProject($projectId)
    {
        $projectId = (int)$projectId;
        if($projectId <= 0)
        {
            return $this->_isSuperAdminSession((array)$this->adminValue);
        }
        if($this->_isSuperAdminSession((array)$this->adminValue))
        {
            return true;
        }
        return ((int)$this->agencyId === $projectId);
    }
}
?>
