<?php
class DealrefundrequestsController extends AdminAppController
{
    public $helpers = array('Html','Form','Session','Paginator','Time','Number');
    public $components = array('Session','Paginator');
    public $uses = array('Admin.Dealrefundrequest','Admin.Dealrefund','Admin.Deal','Admin.DealsPayment');
    public $paginate = array('Dealrefundrequest' => array('page' => 1, 'order' => array('Dealrefundrequest.id' => 'desc')));

    public function beforeFilter()
    {
        parent::beforeFilter();
        if($this->_isSuperAdminSession((array)$this->adminValue))
        {
            $this->userPermissionArr = $this->_defaultFullPermission();
            $this->set('userPermissionArr', $this->userPermissionArr);
            $this->set('allowAddAction', true);
            $this->set('allowEditAction', true);
            $this->set('allowDeleteAction', true);
            $this->set('allowViewAction', true);
        }
        $this->_ensureRefundRequestSchema();
        $this->_ensureRefundCoreTables();
        $this->_ensureClientRefundDealColumns();
        $this->_ensureRefundRequestSettingsColumns();
    }

    public function index()
    {
        $conditions = array();
        if(!$this->_isSuperAdminSession((array)$this->adminValue))
        {
            $conditions['Dealrefundrequest.project_id'] = (int)$this->agencyId;
        }
        $status = isset($this->request->query['status']) ? trim((string)$this->request->query['status']) : '';
        $keyword = isset($this->request->query['keyword']) ? trim((string)$this->request->query['keyword']) : '';
        if($status !== '')
        {
            $conditions['Dealrefundrequest.status'] = $status;
        }
        if($keyword !== '')
        {
            $conditions[] = array('OR' => array(
                'Dealrefundrequest.request_no LIKE' => '%'.$keyword.'%',
                'Deal.invoice_no LIKE' => '%'.$keyword.'%',
                'Client.name LIKE' => '%'.$keyword.'%',
                'Dealrefundrequest.applicant_name LIKE' => '%'.$keyword.'%',
                'Dealrefundrequest.mobile_no LIKE' => '%'.$keyword.'%',
                'Dealrefundrequest.cnic LIKE' => '%'.$keyword.'%',
                'Property.name LIKE' => '%'.$keyword.'%'
            ));
        }

        $this->Paginator->settings = array(
            'Dealrefundrequest' => array(
                'fields' => $this->_requestFields(),
                'joins' => $this->_requestJoins(),
                'conditions' => $conditions,
                'order' => array('Dealrefundrequest.id' => 'desc'),
                'limit' => $this->pageLimit,
                'maxLimit' => $this->maxLimit,
                'recursive' => -1
            )
        );
        try
        {
            $requests = $this->Paginator->paginate('Dealrefundrequest');
        }
        catch(Exception $e)
        {
            $this->_ensureRefundRequestSchema();
            $this->Dealrefundrequest->cacheSources = false;
            $this->Dealrefundrequest->schema(true);
            $requests = $this->Paginator->paginate('Dealrefundrequest');
        }
        $stats = $this->_requestStats();
        $statusOptions = $this->_statusOptions();
        $dealOptions = $this->_clientRefundDealOptions();
        $eligibleDeals = $this->_clientRefundEligibleDeals();
        $prefixProjectId = (int)$this->agencyId;
        if($this->_isSuperAdminSession((array)$this->adminValue) && !empty($this->request->query['prefix_project_id']))
        {
            $prefixProjectId = (int)$this->request->query['prefix_project_id'];
        }
        $prefixSetting = $this->_refundRequestSequenceState($prefixProjectId);
        $prefixProjectOptions = $this->_refundPrefixProjectOptions();
        $this->set(compact('requests','stats','statusOptions','status','keyword','dealOptions','eligibleDeals','prefixSetting','prefixProjectOptions'));
    }

    public function saveprefix()
    {
        if(!$this->request->is(array('post','put')))
        {
            return $this->redirect(array('action' => 'index'));
        }
        $data = isset($this->request->data['Ardsetting']) ? (array)$this->request->data['Ardsetting'] : array();
        $projectId = !empty($data['project_id']) ? (int)$data['project_id'] : (int)$this->agencyId;
        if(!$this->_isAllowedProject($projectId))
        {
            $this->Session->setFlash(__('No Permission!'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        $prefix = isset($data['refund_request_prefix']) ? trim((string)$data['refund_request_prefix']) : '';
        if($prefix === '')
        {
            $prefix = 'RFR-'.($projectId > 0 ? $projectId.'-' : '');
        }
        $nextNo = !empty($data['refund_request_next_no']) ? (int)$data['refund_request_next_no'] : 1;
        $this->_saveRefundRequestSequenceState($projectId, $prefix, $nextNo);
        $this->Session->setFlash(__('Refund request prefix saved.'), 'flash', array('alert' => 'success'));
        if($this->_isSuperAdminSession((array)$this->adminValue))
        {
            return $this->redirect(array('action' => 'index', '?' => array('prefix_project_id' => $projectId)));
        }
        return $this->redirect(array('action' => 'index'));
    }

    public function allowdeal()
    {
        if(!$this->request->is(array('post','put')))
        {
            return $this->redirect(array('action' => 'index'));
        }
        $dealId = !empty($this->request->data['Deal']['deal_id']) ? (int)$this->request->data['Deal']['deal_id'] : 0;
        return $this->_setClientRefundDealFlag($dealId, 1);
    }

    public function disallowdeal($dealId = null)
    {
        if(!$this->request->is('post'))
        {
            return $this->redirect(array('action' => 'index'));
        }
        return $this->_setClientRefundDealFlag((int)$dealId, 0);
    }

    public function view($id = null)
    {
        $row = $this->_requestRow($id);
        if(empty($row['Dealrefundrequest']['id']))
        {
            $this->Session->setFlash(__('Refund request not found.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        if(!$this->_isAllowedProject((int)$row['Dealrefundrequest']['project_id']))
        {
            $this->Session->setFlash(__('No Permission!'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        $defaultDeduction = $this->_defaultRefundDeductionPercent((int)$row['Dealrefundrequest']['project_id']);
        $this->set(compact('row','defaultDeduction'));
    }

    public function reviewform($id = null)
    {
        return $this->_actionForm($id, 'review');
    }

    public function approveform($id = null)
    {
        return $this->_actionForm($id, 'approve');
    }

    public function rejectform($id = null)
    {
        return $this->_actionForm($id, 'reject');
    }

    public function convertform($id = null)
    {
        return $this->_actionForm($id, 'convert');
    }

    public function review($id = null)
    {
        if(!$this->request->is(array('post','put')))
        {
            return $this->redirect(array('action' => 'view', (int)$id));
        }
        $row = $this->_requestRow($id);
        if(empty($row['Dealrefundrequest']['id']) || !$this->_isAllowedProject((int)$row['Dealrefundrequest']['project_id']))
        {
            $this->Session->setFlash(__('Invalid refund request.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        if(in_array($row['Dealrefundrequest']['status'], array('Cancelled','Rejected','Converted To Refund'), true))
        {
            $this->Session->setFlash(__('This request cannot be moved to review.'), 'flash', array('alert' => 'warning'));
            return $this->redirect(array('action' => 'view', (int)$row['Dealrefundrequest']['id']));
        }
        $data = isset($this->request->data['Dealrefundrequest']) ? (array)$this->request->data['Dealrefundrequest'] : array();
        $this->_updateRequestStatus((int)$row['Dealrefundrequest']['id'], 'Under Review', array(
            'admin_notes' => isset($data['admin_notes']) ? trim((string)$data['admin_notes']) : $row['Dealrefundrequest']['admin_notes']
        ));
        $this->Session->setFlash(__('Refund request marked under review.'), 'flash', array('alert' => 'success'));
        return $this->redirect(array('action' => 'view', (int)$row['Dealrefundrequest']['id']));
    }

    public function approve($id = null)
    {
        if(!$this->request->is(array('post','put')))
        {
            return $this->redirect(array('action' => 'view', (int)$id));
        }
        $row = $this->_requestRow($id);
        if(empty($row['Dealrefundrequest']['id']) || !$this->_isAllowedProject((int)$row['Dealrefundrequest']['project_id']))
        {
            $this->Session->setFlash(__('Invalid refund request.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        if(in_array($row['Dealrefundrequest']['status'], array('Cancelled','Rejected','Converted To Refund'), true))
        {
            $this->Session->setFlash(__('This request cannot be approved.'), 'flash', array('alert' => 'warning'));
            return $this->redirect(array('action' => 'view', (int)$row['Dealrefundrequest']['id']));
        }
        $data = isset($this->request->data['Dealrefundrequest']) ? (array)$this->request->data['Dealrefundrequest'] : array();
        $totalPaid = $this->_dealTotalPaid((int)$row['Dealrefundrequest']['deal_id']);
        if($totalPaid <= 0)
        {
            $totalPaid = (float)$row['Dealrefundrequest']['total_paid'];
        }
        $deductionPercent = isset($data['deduction_percent']) ? (float)str_replace(',', '', $data['deduction_percent']) : (float)$row['Dealrefundrequest']['deduction_percent'];
        if($deductionPercent < 0){$deductionPercent = 0;}
        $approvedAmount = isset($data['approved_amount']) ? (float)str_replace(',', '', $data['approved_amount']) : 0;
        if($approvedAmount <= 0)
        {
            $approvedAmount = max(0, $totalPaid - round(($totalPaid * $deductionPercent) / 100, 2));
        }
        if($totalPaid > 0 && $approvedAmount > $totalPaid)
        {
            $approvedAmount = $totalPaid;
        }
        if($approvedAmount <= 0)
        {
            $this->Session->setFlash(__('Approved amount must be greater than zero.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'view', (int)$row['Dealrefundrequest']['id']));
        }

        $this->_updateRequestStatus((int)$row['Dealrefundrequest']['id'], 'Approved', array(
            'approved_amount' => round($approvedAmount, 2),
            'total_paid' => round($totalPaid, 2),
            'deduction_percent' => round($deductionPercent, 2),
            'admin_notes' => isset($data['admin_notes']) ? trim((string)$data['admin_notes']) : $row['Dealrefundrequest']['admin_notes']
        ));
        $this->Session->setFlash(__('Refund request approved.'), 'flash', array('alert' => 'success'));
        return $this->redirect(array('action' => 'view', (int)$row['Dealrefundrequest']['id']));
    }

    public function reject($id = null)
    {
        if(!$this->request->is(array('post','put')))
        {
            return $this->redirect(array('action' => 'view', (int)$id));
        }
        $row = $this->_requestRow($id);
        if(empty($row['Dealrefundrequest']['id']) || !$this->_isAllowedProject((int)$row['Dealrefundrequest']['project_id']))
        {
            $this->Session->setFlash(__('Invalid refund request.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        if($row['Dealrefundrequest']['status'] === 'Converted To Refund')
        {
            $this->Session->setFlash(__('Converted requests cannot be rejected.'), 'flash', array('alert' => 'warning'));
            return $this->redirect(array('action' => 'view', (int)$row['Dealrefundrequest']['id']));
        }
        $data = isset($this->request->data['Dealrefundrequest']) ? (array)$this->request->data['Dealrefundrequest'] : array();
        $this->_updateRequestStatus((int)$row['Dealrefundrequest']['id'], 'Rejected', array(
            'admin_notes' => isset($data['admin_notes']) ? trim((string)$data['admin_notes']) : $row['Dealrefundrequest']['admin_notes']
        ));
        $this->Session->setFlash(__('Refund request rejected.'), 'flash', array('alert' => 'success'));
        return $this->redirect(array('action' => 'view', (int)$row['Dealrefundrequest']['id']));
    }

    public function convert($id = null)
    {
        if(!$this->request->is(array('post','put')))
        {
            return $this->redirect(array('action' => 'view', (int)$id));
        }
        $row = $this->_requestRow($id);
        if(empty($row['Dealrefundrequest']['id']) || !$this->_isAllowedProject((int)$row['Dealrefundrequest']['project_id']))
        {
            $this->Session->setFlash(__('Invalid refund request.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        $request = (array)$row['Dealrefundrequest'];
        if(!empty($request['converted_refund_id']))
        {
            return $this->redirect(array('controller' => 'Dealrefunds', 'action' => 'view', (int)$request['converted_refund_id']));
        }
        if($request['status'] !== 'Approved')
        {
            $this->Session->setFlash(__('Only approved requests can be converted to refund.'), 'flash', array('alert' => 'warning'));
            return $this->redirect(array('action' => 'view', (int)$request['id']));
        }

        $totalPaid = $this->_dealTotalPaid((int)$request['deal_id']);
        if($totalPaid <= 0)
        {
            $totalPaid = (float)$request['total_paid'];
        }
        $data = isset($this->request->data['Dealrefundrequest']) ? (array)$this->request->data['Dealrefundrequest'] : array();
        if(empty($data) && !empty($this->request->data['Dealrefund']))
        {
            $data = (array)$this->request->data['Dealrefund'];
        }
        $deductionPercent = isset($data['deduction_percent']) ? (float)str_replace(array(',', '%'), '', $data['deduction_percent']) : (float)$request['deduction_percent'];
        $deductionAmount = round(($totalPaid * $deductionPercent) / 100, 2);
        $netRefund = round(max(0, $totalPaid - $deductionAmount), 2);
        if($netRefund <= 0 && !empty($request['approved_amount']) && empty($data['deduction_percent']))
        {
            $netRefund = (float)$request['approved_amount'];
            if($totalPaid > 0 && $netRefund > $totalPaid)
            {
                $netRefund = $totalPaid;
            }
            $deductionAmount = max(0, round($totalPaid - $netRefund, 2));
            $deductionPercent = $totalPaid > 0 ? round(($deductionAmount / $totalPaid) * 100, 2) : $deductionPercent;
        }
        if($netRefund <= 0)
        {
            $this->Session->setFlash(__('Net refund amount must be greater than zero.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'view', (int)$request['id']));
        }
        $cycleCount = max(1, (int)(isset($data['cycle_count']) ? $data['cycle_count'] : 1));
        $cycleFrequency = trim((string)(isset($data['cycle_frequency']) ? $data['cycle_frequency'] : 'Monthly'));
        if(!in_array($cycleFrequency, array('Monthly','Quarterly','Biannually','Yearly'), true))
        {
            $cycleFrequency = 'Monthly';
        }
        $cycleStartDate = $this->_normalizeDate(isset($data['cycle_start_date']) ? $data['cycle_start_date'] : date('Y-m-d'));
        if($cycleStartDate === ''){ $cycleStartDate = date('Y-m-d'); }
        $requestDate = $this->_normalizeDate(isset($data['request_date']) ? $data['request_date'] : date('Y-m-d'));
        if($requestDate === ''){ $requestDate = date('Y-m-d'); }
        $perCycleAmount = $cycleCount > 0 ? round($netRefund / $cycleCount, 2) : round($netRefund, 2);
        $refundData = array(
            'deal_id' => (int)$request['deal_id'],
            'client_id' => (int)$request['client_id'],
            'project_id' => (int)$request['project_id'],
            'status' => 'Draft',
            'request_date' => $requestDate,
            'total_paid' => round($totalPaid, 2),
            'deduction_percent' => $deductionPercent,
            'deduction_amount' => $deductionAmount,
            'net_refund' => round($netRefund, 2),
            'cycle_count' => $cycleCount,
            'cycle_frequency' => $cycleFrequency,
            'cycle_start_date' => $cycleStartDate,
            'per_cycle_amount' => $perCycleAmount,
            'reason' => $request['reason'],
            'applicant_name' => $request['applicant_name'],
            'relation_type' => isset($request['relation_type']) ? $request['relation_type'] : '',
            'father_name' => $request['father_name'],
            'cnic' => $request['cnic'],
            'passport_no' => $request['passport_no'],
            'address' => $request['address'],
            'mobile_no' => $request['mobile_no'],
            'email' => $request['email'],
            'account_title' => $request['account_title'],
            'account_no' => trim((string)$request['account_no'].(!empty($request['iban']) ? ' / IBAN: '.$request['iban'] : '')),
            'bank_name' => $request['bank_name'],
            'bank_branch_city' => $request['bank_branch_city'],
            'created_by' => (int)$this->luserId
        );
        $this->Dealrefund->create();
        if(!$this->Dealrefund->save(array('Dealrefund' => $refundData), array('validate' => false)))
        {
            $this->Session->setFlash(__('Unable to convert request into refund.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'view', (int)$request['id']));
        }
        $refundId = (int)$this->Dealrefund->id;
        $this->_updateRequestStatus((int)$request['id'], 'Converted To Refund', array(
            'converted_refund_id' => $refundId,
            'approved_amount' => round($netRefund, 2),
            'total_paid' => round($totalPaid, 2),
            'deduction_percent' => round($deductionPercent, 2)
        ));
        if(!empty($data['finalize_now']))
        {
            $this->Session->setFlash(__('Deal refund created. Schedule generation is starting.'), 'flash', array('alert' => 'success'));
            return $this->redirect(array('controller' => 'Dealrefunds', 'action' => 'finalize', $refundId));
        }
        $this->Session->setFlash(__('Refund request converted successfully. Open the refund to finalize the schedule.'), 'flash', array('alert' => 'success'));
        return $this->redirect(array('controller' => 'Dealrefunds', 'action' => 'view', $refundId));
    }

    public function download($id = null, $docKey = null)
    {
        $row = $this->_requestRow($id);
        if(empty($row['Dealrefundrequest']['id']) || !$this->_isAllowedProject((int)$row['Dealrefundrequest']['project_id']))
        {
            $this->Session->setFlash(__('Refund request not found.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        return $this->_serveAttachment($row, $docKey);
    }

    protected function _requestFields()
    {
        return array(
            'Dealrefundrequest.*',
            'Deal.invoice_no','Deal.total_amount','Deal.date',
            'Client.name','Client.phone','Client.mobile','Client.email',
            'Property.name','Property.area',
            'Project.name',
            'ReviewedBy.name',
            'Dealrefund.id','Dealrefund.status'
        );
    }

    protected function _requestJoins()
    {
        return array(
            array('table' => 'deals', 'alias' => 'Deal', 'type' => 'LEFT', 'conditions' => array('Deal.id=Dealrefundrequest.deal_id')),
            array('table' => 'clients', 'alias' => 'Client', 'type' => 'LEFT', 'conditions' => array('Client.id=Dealrefundrequest.client_id')),
            array('table' => 'properties', 'alias' => 'Property', 'type' => 'LEFT', 'conditions' => array('Property.id=Dealrefundrequest.property_id')),
            array('table' => 'projects', 'alias' => 'Project', 'type' => 'LEFT', 'conditions' => array('Project.id=Dealrefundrequest.project_id')),
            array('table' => 'users', 'alias' => 'ReviewedBy', 'type' => 'LEFT', 'conditions' => array('ReviewedBy.id=Dealrefundrequest.reviewed_by')),
            array('table' => 'deal_refunds', 'alias' => 'Dealrefund', 'type' => 'LEFT', 'conditions' => array('Dealrefund.id=Dealrefundrequest.converted_refund_id'))
        );
    }

    protected function _requestRow($id)
    {
        $id = (int)$id;
        if($id <= 0)
        {
            return array();
        }
        return (array)$this->Dealrefundrequest->find('first', array(
            'fields' => $this->_requestFields(),
            'joins' => $this->_requestJoins(),
            'conditions' => array('Dealrefundrequest.id' => $id),
            'recursive' => -1
        ));
    }

    protected function _requestStats()
    {
        $conditions = array();
        if(!$this->_isSuperAdminSession((array)$this->adminValue))
        {
            $conditions['Dealrefundrequest.project_id'] = (int)$this->agencyId;
        }
        $stats = array('total' => 0, 'pending' => 0, 'approved' => 0, 'converted' => 0);
        $rows = (array)$this->Dealrefundrequest->find('all', array(
            'fields' => array('Dealrefundrequest.status','COUNT(Dealrefundrequest.id) AS total'),
            'conditions' => $conditions,
            'group' => array('Dealrefundrequest.status'),
            'recursive' => -1
        ));
        foreach($rows as $row)
        {
            $status = isset($row['Dealrefundrequest']['status']) ? $row['Dealrefundrequest']['status'] : '';
            $count = isset($row[0]['total']) ? (int)$row[0]['total'] : 0;
            $stats['total'] += $count;
            if($status === 'Pending' || $status === 'Under Review'){$stats['pending'] += $count;}
            if($status === 'Approved'){$stats['approved'] += $count;}
            if($status === 'Converted To Refund'){$stats['converted'] += $count;}
        }
        return $stats;
    }

    protected function _updateRequestStatus($id, $status, $extra = array())
    {
        $now = date('Y-m-d H:i:s');
        $save = array_merge(array(
            'id' => (int)$id,
            'status' => $status,
            'reviewed_by' => (int)$this->luserId,
            'reviewed_at' => $now,
            'status_updated_at' => $now
        ), (array)$extra);
        $this->Dealrefundrequest->save(array('Dealrefundrequest' => $save), array('validate' => false));
    }

    protected function _actionForm($id, $actionType)
    {
        $row = $this->_requestRow($id);
        if(empty($row['Dealrefundrequest']['id']))
        {
            $this->Session->setFlash(__('Refund request not found.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        if(!$this->_isAllowedProject((int)$row['Dealrefundrequest']['project_id']))
        {
            $this->Session->setFlash(__('No Permission!'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        if(!$this->_actionFormAllowed($actionType, (array)$row['Dealrefundrequest']))
        {
            $this->Session->setFlash(__('This action is not available for the current application status.'), 'flash', array('alert' => 'warning'));
            return $this->redirect(array('action' => 'view', (int)$row['Dealrefundrequest']['id']));
        }
        $defaultDeduction = $this->_defaultRefundDeductionPercent((int)$row['Dealrefundrequest']['project_id']);
        $this->request->data['Dealrefundrequest'] = (array)$row['Dealrefundrequest'];
        $this->set(compact('row','defaultDeduction','actionType'));
        return $this->render('actionform');
    }

    protected function _actionFormAllowed($actionType, $request)
    {
        $status = !empty($request['status']) ? (string)$request['status'] : 'Pending';
        if($actionType === 'review' || $actionType === 'approve')
        {
            return !in_array($status, array('Cancelled','Rejected','Converted To Refund'), true);
        }
        if($actionType === 'reject')
        {
            return $status !== 'Converted To Refund';
        }
        if($actionType === 'convert')
        {
            return $status === 'Approved' && empty($request['converted_refund_id']);
        }
        return false;
    }

    protected function _dealTotalPaid($dealId)
    {
        $dealId = (int)$dealId;
        if($dealId <= 0)
        {
            return 0.0;
        }
        try
        {
            $row = $this->DealsPayment->find('first', array(
                'fields' => array('SUM(DealsPayment.emi) AS paid_total'),
                'conditions' => array('DealsPayment.deal_id' => $dealId),
                'recursive' => -1
            ));
            if(isset($row[0]['paid_total']) && $row[0]['paid_total'] !== null)
            {
                return (float)$row[0]['paid_total'];
            }
        }
        catch(Exception $e)
        {
        }
        try
        {
            $row = $this->DealsPayment->find('first', array(
                'fields' => array('SUM(DealsPayment.amount) AS paid_total'),
                'conditions' => array('DealsPayment.deal_id' => $dealId),
                'recursive' => -1
            ));
            if(isset($row[0]['paid_total']))
            {
                return (float)$row[0]['paid_total'];
            }
        }
        catch(Exception $e)
        {
        }
        return 0.0;
    }

    protected function _defaultRefundDeductionPercent($projectId = null)
    {
        $projectId = (int)$projectId;
        if($projectId <= 0)
        {
            $projectId = (int)$this->agencyId;
        }
        try
        {
            $this->loadModel('Admin.Ardsetting');
            $row = $this->Ardsetting->find('first', array(
                'conditions' => array('Ardsetting.project_id' => $projectId),
                'recursive' => -1
            ));
            if(!empty($row['Ardsetting']['refund_deduction_percent']))
            {
                return (float)$row['Ardsetting']['refund_deduction_percent'];
            }
        }
        catch(Exception $e)
        {
        }
        return 10.0;
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

    protected function _statusOptions()
    {
        return array(
            'Pending' => __('Pending'),
            'Under Review' => __('Under Review'),
            'Approved' => __('Approved'),
            'Rejected' => __('Rejected'),
            'Cancelled' => __('Cancelled'),
            'Converted To Refund' => __('Converted To Refund')
        );
    }

    protected function _isAllowedProject($projectId)
    {
        if($this->_isSuperAdminSession((array)$this->adminValue))
        {
            return true;
        }
        return ((int)$projectId === (int)$this->agencyId);
    }

    protected function _requiredRefundDocuments()
    {
        return array(
            'client_photo' => array('label' => __('Client Photo'), 'mime' => 'client_photo_mime'),
            'cnic_front' => array('label' => __('CNIC Front'), 'mime' => 'cnic_front_mime'),
            'cnic_back' => array('label' => __('CNIC Back'), 'mime' => 'cnic_back_mime'),
            'file_front' => array('label' => __('File Front'), 'mime' => 'file_front_mime'),
            'file_back' => array('label' => __('File Back'), 'mime' => 'file_back_mime'),
            'client_signature' => array('label' => __('Client Signature'), 'mime' => 'client_signature_mime')
        );
    }

    protected function _serveAttachment($row, $docKey = null)
    {
        $doc = (array)$row['Dealrefundrequest'];
        $docs = $this->_requiredRefundDocuments();
        $docKey = trim((string)$docKey);
        $label = __('Attachment');
        if($docKey !== '' && isset($docs[$docKey]))
        {
            $file = !empty($doc[$docKey]) ? basename($doc[$docKey]) : '';
            $label = $docs[$docKey]['label'];
        }
        else
        {
            $file = !empty($doc['attachment']) ? basename($doc['attachment']) : '';
        }
        if($file === '')
        {
            $this->Session->setFlash(sprintf(__('%s not found.'), $label), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'view', (int)$doc['id']));
        }
        $path = APP.'RefundRequests'.DS.$file;
        if(!file_exists($path))
        {
            $this->Session->setFlash(sprintf(__('%s file is missing.'), $label), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'view', (int)$doc['id']));
        }
        $downloadName = 'refund_request_'.$doc['id'].'_'.($docKey !== '' ? $docKey.'_' : '').basename($file);
        $this->response->file($path, array('download' => true, 'name' => $downloadName));
        return $this->response;
    }

    protected function _refundRequestSequenceState($projectId)
    {
        $projectId = (int)$projectId;
        $state = array(
            'project_id' => $projectId,
            'refund_request_prefix' => 'RFR-'.($projectId > 0 ? $projectId.'-' : ''),
            'refund_request_next_no' => 1
        );
        try
        {
            $this->loadModel('Admin.Ardsetting');
            $row = $this->Ardsetting->find('first', array(
                'conditions' => array('Ardsetting.project_id' => $projectId),
                'order' => array('Ardsetting.id' => 'desc'),
                'recursive' => -1
            ));
            if(!empty($row['Ardsetting']))
            {
                $rowData = (array)$row['Ardsetting'];
                if(trim((string)(isset($rowData['refund_request_prefix']) ? $rowData['refund_request_prefix'] : '')) !== '')
                {
                    $state['refund_request_prefix'] = $rowData['refund_request_prefix'];
                }
                if(!empty($rowData['refund_request_next_no']))
                {
                    $state['refund_request_next_no'] = max(1, (int)$rowData['refund_request_next_no']);
                }
                if(!empty($rowData['id']))
                {
                    $state['id'] = (int)$rowData['id'];
                }
            }
        }
        catch(Exception $e)
        {
        }
        return $state;
    }

    protected function _refundPrefixProjectOptions()
    {
        if(!$this->_isSuperAdminSession((array)$this->adminValue))
        {
            return array();
        }
        $options = array();
        try
        {
            $rows = (array)$this->_schemaModel()->query("SELECT `id`, `name` FROM `projects` ORDER BY `name` ASC");
            foreach($rows as $row)
            {
                if(isset($row['projects']) && is_array($row['projects']))
                {
                    $row = (array)$row['projects'];
                }
                elseif(isset($row['Project']) && is_array($row['Project']))
                {
                    $row = (array)$row['Project'];
                }
                elseif(isset($row[0]) && is_array($row[0]))
                {
                    $row = (array)$row[0];
                }
                $id = !empty($row['id']) ? (int)$row['id'] : 0;
                if($id > 0)
                {
                    $options[$id] = !empty($row['name']) ? $row['name'] : 'Agency #'.$id;
                }
            }
        }
        catch(Exception $e)
        {
        }
        return $options;
    }

    protected function _saveRefundRequestSequenceState($projectId, $prefix, $nextNo)
    {
        $projectId = (int)$projectId;
        $prefix = substr(trim((string)$prefix), 0, 30);
        if($prefix === '')
        {
            $prefix = 'RFR-'.($projectId > 0 ? $projectId.'-' : '');
        }
        $nextNo = max(1, (int)$nextNo);
        try
        {
            $this->_ensureRefundRequestSettingsColumns();
            $this->loadModel('Admin.Ardsetting');
            $row = $this->Ardsetting->find('first', array(
                'conditions' => array('Ardsetting.project_id' => $projectId),
                'order' => array('Ardsetting.id' => 'desc'),
                'recursive' => -1
            ));
            $save = array(
                'project_id' => $projectId,
                'refund_request_prefix' => $prefix,
                'refund_request_next_no' => $nextNo
            );
            if(!empty($row['Ardsetting']['id']))
            {
                $save['id'] = (int)$row['Ardsetting']['id'];
            }
            $this->Ardsetting->create();
            $this->Ardsetting->save(array('Ardsetting' => $save), false);
        }
        catch(Exception $e)
        {
        }
    }

    protected function _ensureRefundRequestSchema()
    {
        $SchemaModel = $this->_schemaModel();
        try
        {
            $SchemaModel->query("CREATE TABLE IF NOT EXISTS `deal_refund_requests` (
                `id` bigint(20) NOT NULL AUTO_INCREMENT,
                `deal_id` bigint(20) NOT NULL DEFAULT 0,
                `client_id` bigint(20) NOT NULL DEFAULT 0,
                `project_id` int(11) NOT NULL DEFAULT 0,
                `property_id` bigint(20) NOT NULL DEFAULT 0,
                `request_no` varchar(80) DEFAULT '',
                `request_prefix` varchar(30) DEFAULT '',
                `request_seq` int(11) NOT NULL DEFAULT 0,
                `requested_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
                `approved_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
                `total_paid` decimal(15,2) NOT NULL DEFAULT 0.00,
                `deduction_percent` decimal(8,2) NOT NULL DEFAULT 0.00,
                `preferred_method` varchar(40) DEFAULT 'Bank Transfer',
                `reason` text,
                `client_notes` text,
                `admin_notes` text,
                `applicant_name` varchar(160) DEFAULT '',
                `father_name` varchar(160) DEFAULT '',
                `cnic` varchar(40) DEFAULT '',
                `cnic_front_number` varchar(40) DEFAULT '',
                `cnic_match_status` varchar(30) DEFAULT '',
                `passport_no` varchar(40) DEFAULT '',
                `address` text,
                `mobile_no` varchar(40) DEFAULT '',
                `email` varchar(140) DEFAULT '',
                `account_title` varchar(160) DEFAULT '',
                `account_no` varchar(120) DEFAULT '',
                `bank_name` varchar(120) DEFAULT '',
                `bank_branch_city` varchar(160) DEFAULT '',
                `iban` varchar(80) DEFAULT '',
                `wallet_name` varchar(80) DEFAULT '',
                `wallet_no` varchar(80) DEFAULT '',
                `attachment` varchar(255) DEFAULT '',
                `attachment_dir` varchar(120) DEFAULT 'refundrequests',
                `attachment_mime` varchar(120) DEFAULT '',
                `client_photo` varchar(255) DEFAULT '',
                `client_photo_mime` varchar(120) DEFAULT '',
                `cnic_front` varchar(255) DEFAULT '',
                `cnic_front_mime` varchar(120) DEFAULT '',
                `cnic_back` varchar(255) DEFAULT '',
                `cnic_back_mime` varchar(120) DEFAULT '',
                `file_front` varchar(255) DEFAULT '',
                `file_front_mime` varchar(120) DEFAULT '',
                `file_back` varchar(255) DEFAULT '',
                `file_back_mime` varchar(120) DEFAULT '',
                `client_signature` varchar(255) DEFAULT '',
                `client_signature_mime` varchar(120) DEFAULT '',
                `documents_verified` tinyint(1) NOT NULL DEFAULT 0,
                `status` varchar(30) NOT NULL DEFAULT 'Pending',
                `status_updated_at` datetime DEFAULT NULL,
                `reviewed_by` int(11) NOT NULL DEFAULT 0,
                `reviewed_at` datetime DEFAULT NULL,
                `converted_refund_id` bigint(20) NOT NULL DEFAULT 0,
                `created_by_client` tinyint(1) NOT NULL DEFAULT 1,
                `created` datetime DEFAULT NULL,
                `modified` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `deal_id` (`deal_id`),
                KEY `client_id` (`client_id`),
                KEY `project_id` (`project_id`),
                KEY `request_no` (`request_no`),
                KEY `status` (`status`),
                KEY `converted_refund_id` (`converted_refund_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3");
        }
        catch(Exception $e)
        {
        }
        $this->_ensureRefundRequestColumns();
        $this->Dealrefundrequest->cacheSources = false;
        try { $this->Dealrefundrequest->schema(true); } catch(Exception $e) {}
    }

    protected function _ensureRefundRequestColumns()
    {
        $SchemaModel = $this->_schemaModel();
        $columns = array(
            'deal_id' => "ALTER TABLE `deal_refund_requests` ADD COLUMN `deal_id` bigint(20) NOT NULL DEFAULT 0",
            'client_id' => "ALTER TABLE `deal_refund_requests` ADD COLUMN `client_id` bigint(20) NOT NULL DEFAULT 0",
            'project_id' => "ALTER TABLE `deal_refund_requests` ADD COLUMN `project_id` int(11) NOT NULL DEFAULT 0",
            'property_id' => "ALTER TABLE `deal_refund_requests` ADD COLUMN `property_id` bigint(20) NOT NULL DEFAULT 0",
            'request_no' => "ALTER TABLE `deal_refund_requests` ADD COLUMN `request_no` varchar(80) DEFAULT ''",
            'request_prefix' => "ALTER TABLE `deal_refund_requests` ADD COLUMN `request_prefix` varchar(30) DEFAULT ''",
            'request_seq' => "ALTER TABLE `deal_refund_requests` ADD COLUMN `request_seq` int(11) NOT NULL DEFAULT 0",
            'requested_amount' => "ALTER TABLE `deal_refund_requests` ADD COLUMN `requested_amount` decimal(15,2) NOT NULL DEFAULT 0.00",
            'approved_amount' => "ALTER TABLE `deal_refund_requests` ADD COLUMN `approved_amount` decimal(15,2) NOT NULL DEFAULT 0.00",
            'total_paid' => "ALTER TABLE `deal_refund_requests` ADD COLUMN `total_paid` decimal(15,2) NOT NULL DEFAULT 0.00",
            'deduction_percent' => "ALTER TABLE `deal_refund_requests` ADD COLUMN `deduction_percent` decimal(8,2) NOT NULL DEFAULT 0.00",
            'preferred_method' => "ALTER TABLE `deal_refund_requests` ADD COLUMN `preferred_method` varchar(40) DEFAULT 'Bank Transfer'",
            'reason' => "ALTER TABLE `deal_refund_requests` ADD COLUMN `reason` text",
            'client_notes' => "ALTER TABLE `deal_refund_requests` ADD COLUMN `client_notes` text",
            'admin_notes' => "ALTER TABLE `deal_refund_requests` ADD COLUMN `admin_notes` text",
            'applicant_name' => "ALTER TABLE `deal_refund_requests` ADD COLUMN `applicant_name` varchar(160) DEFAULT ''",
            'father_name' => "ALTER TABLE `deal_refund_requests` ADD COLUMN `father_name` varchar(160) DEFAULT ''",
            'cnic' => "ALTER TABLE `deal_refund_requests` ADD COLUMN `cnic` varchar(40) DEFAULT ''",
            'cnic_front_number' => "ALTER TABLE `deal_refund_requests` ADD COLUMN `cnic_front_number` varchar(40) DEFAULT ''",
            'cnic_match_status' => "ALTER TABLE `deal_refund_requests` ADD COLUMN `cnic_match_status` varchar(30) DEFAULT ''",
            'passport_no' => "ALTER TABLE `deal_refund_requests` ADD COLUMN `passport_no` varchar(40) DEFAULT ''",
            'address' => "ALTER TABLE `deal_refund_requests` ADD COLUMN `address` text",
            'mobile_no' => "ALTER TABLE `deal_refund_requests` ADD COLUMN `mobile_no` varchar(40) DEFAULT ''",
            'email' => "ALTER TABLE `deal_refund_requests` ADD COLUMN `email` varchar(140) DEFAULT ''",
            'account_title' => "ALTER TABLE `deal_refund_requests` ADD COLUMN `account_title` varchar(160) DEFAULT ''",
            'account_no' => "ALTER TABLE `deal_refund_requests` ADD COLUMN `account_no` varchar(120) DEFAULT ''",
            'bank_name' => "ALTER TABLE `deal_refund_requests` ADD COLUMN `bank_name` varchar(120) DEFAULT ''",
            'bank_branch_city' => "ALTER TABLE `deal_refund_requests` ADD COLUMN `bank_branch_city` varchar(160) DEFAULT ''",
            'iban' => "ALTER TABLE `deal_refund_requests` ADD COLUMN `iban` varchar(80) DEFAULT ''",
            'wallet_name' => "ALTER TABLE `deal_refund_requests` ADD COLUMN `wallet_name` varchar(80) DEFAULT ''",
            'wallet_no' => "ALTER TABLE `deal_refund_requests` ADD COLUMN `wallet_no` varchar(80) DEFAULT ''",
            'attachment' => "ALTER TABLE `deal_refund_requests` ADD COLUMN `attachment` varchar(255) DEFAULT ''",
            'attachment_dir' => "ALTER TABLE `deal_refund_requests` ADD COLUMN `attachment_dir` varchar(120) DEFAULT 'refundrequests'",
            'attachment_mime' => "ALTER TABLE `deal_refund_requests` ADD COLUMN `attachment_mime` varchar(120) DEFAULT ''",
            'client_photo' => "ALTER TABLE `deal_refund_requests` ADD COLUMN `client_photo` varchar(255) DEFAULT ''",
            'client_photo_mime' => "ALTER TABLE `deal_refund_requests` ADD COLUMN `client_photo_mime` varchar(120) DEFAULT ''",
            'cnic_front' => "ALTER TABLE `deal_refund_requests` ADD COLUMN `cnic_front` varchar(255) DEFAULT ''",
            'cnic_front_mime' => "ALTER TABLE `deal_refund_requests` ADD COLUMN `cnic_front_mime` varchar(120) DEFAULT ''",
            'cnic_back' => "ALTER TABLE `deal_refund_requests` ADD COLUMN `cnic_back` varchar(255) DEFAULT ''",
            'cnic_back_mime' => "ALTER TABLE `deal_refund_requests` ADD COLUMN `cnic_back_mime` varchar(120) DEFAULT ''",
            'file_front' => "ALTER TABLE `deal_refund_requests` ADD COLUMN `file_front` varchar(255) DEFAULT ''",
            'file_front_mime' => "ALTER TABLE `deal_refund_requests` ADD COLUMN `file_front_mime` varchar(120) DEFAULT ''",
            'file_back' => "ALTER TABLE `deal_refund_requests` ADD COLUMN `file_back` varchar(255) DEFAULT ''",
            'file_back_mime' => "ALTER TABLE `deal_refund_requests` ADD COLUMN `file_back_mime` varchar(120) DEFAULT ''",
            'client_signature' => "ALTER TABLE `deal_refund_requests` ADD COLUMN `client_signature` varchar(255) DEFAULT ''",
            'client_signature_mime' => "ALTER TABLE `deal_refund_requests` ADD COLUMN `client_signature_mime` varchar(120) DEFAULT ''",
            'documents_verified' => "ALTER TABLE `deal_refund_requests` ADD COLUMN `documents_verified` tinyint(1) NOT NULL DEFAULT 0",
            'status' => "ALTER TABLE `deal_refund_requests` ADD COLUMN `status` varchar(30) NOT NULL DEFAULT 'Pending'",
            'status_updated_at' => "ALTER TABLE `deal_refund_requests` ADD COLUMN `status_updated_at` datetime DEFAULT NULL",
            'reviewed_by' => "ALTER TABLE `deal_refund_requests` ADD COLUMN `reviewed_by` int(11) NOT NULL DEFAULT 0",
            'reviewed_at' => "ALTER TABLE `deal_refund_requests` ADD COLUMN `reviewed_at` datetime DEFAULT NULL",
            'converted_refund_id' => "ALTER TABLE `deal_refund_requests` ADD COLUMN `converted_refund_id` bigint(20) NOT NULL DEFAULT 0",
            'created_by_client' => "ALTER TABLE `deal_refund_requests` ADD COLUMN `created_by_client` tinyint(1) NOT NULL DEFAULT 1",
            'created' => "ALTER TABLE `deal_refund_requests` ADD COLUMN `created` datetime DEFAULT NULL",
            'modified' => "ALTER TABLE `deal_refund_requests` ADD COLUMN `modified` datetime DEFAULT NULL"
        );
        foreach($columns as $column => $sql)
        {
            try
            {
                $exists = (array)$SchemaModel->query("SHOW COLUMNS FROM `deal_refund_requests` LIKE '".addslashes($column)."'");
                if(empty($exists))
                {
                    $SchemaModel->query($sql);
                }
            }
            catch(Exception $e)
            {
            }
        }
    }

    protected function _ensureRefundRequestSettingsColumns()
    {
        $SchemaModel = $this->_schemaModel();
        $columns = array(
            'refund_request_prefix' => "ALTER TABLE `ardaccounts_settings` ADD COLUMN `refund_request_prefix` varchar(30) DEFAULT 'RFR-'",
            'refund_request_next_no' => "ALTER TABLE `ardaccounts_settings` ADD COLUMN `refund_request_next_no` int(11) NOT NULL DEFAULT 1"
        );
        foreach($columns as $column => $sql)
        {
            try
            {
                $exists = (array)$SchemaModel->query("SHOW COLUMNS FROM `ardaccounts_settings` LIKE '".addslashes($column)."'");
                if(empty($exists))
                {
                    $SchemaModel->query($sql);
                }
            }
            catch(Exception $e)
            {
            }
        }
        try
        {
            $this->loadModel('Admin.Ardsetting');
            $this->Ardsetting->cacheSources = false;
            $this->Ardsetting->schema(true);
        }
        catch(Exception $e)
        {
        }
    }

    protected function _schemaModel()
    {
        return !empty($this->Deal) ? $this->Deal : $this->Dealrefundrequest;
    }

    protected function _ensureClientRefundDealColumns()
    {
        try
        {
            $exists = (array)$this->Deal->query("SHOW COLUMNS FROM `deals` LIKE 'client_refund_allowed'");
            if(empty($exists))
            {
                $this->Deal->query("ALTER TABLE `deals` ADD COLUMN `client_refund_allowed` tinyint(1) NOT NULL DEFAULT 0");
            }
        }
        catch(Exception $e)
        {
        }
        try
        {
            $exists = (array)$this->Deal->query("SHOW COLUMNS FROM `deals` LIKE 'client_refund_allowed_by'");
            if(empty($exists))
            {
                $this->Deal->query("ALTER TABLE `deals` ADD COLUMN `client_refund_allowed_by` int(11) NOT NULL DEFAULT 0");
            }
        }
        catch(Exception $e)
        {
        }
        try
        {
            $exists = (array)$this->Deal->query("SHOW COLUMNS FROM `deals` LIKE 'client_refund_allowed_at'");
            if(empty($exists))
            {
                $this->Deal->query("ALTER TABLE `deals` ADD COLUMN `client_refund_allowed_at` datetime DEFAULT NULL");
            }
        }
        catch(Exception $e)
        {
        }
        $this->Deal->cacheSources = false;
        try { $this->Deal->schema(true); } catch(Exception $e) {}
    }

    protected function _adminDealPermissionConditions()
    {
        if($this->_isSuperAdminSession((array)$this->adminValue))
        {
            return array();
        }
        return array('Property.project_id' => (int)$this->agencyId);
    }

    protected function _clientRefundDealOptions()
    {
        $conditions = $this->_adminDealPermissionConditions();
        $rows = (array)$this->Deal->find('all', array(
            'fields' => array('Deal.id','Deal.invoice_no','Client.name','Property.name','Property.project_id','Project.name'),
            'joins' => array(
                array('table' => 'clients', 'alias' => 'Client', 'type' => 'LEFT', 'conditions' => array('Client.id=Deal.client_id')),
                array('table' => 'properties', 'alias' => 'Property', 'type' => 'LEFT', 'conditions' => array('Property.id=Deal.property_id')),
                array('table' => 'projects', 'alias' => 'Project', 'type' => 'LEFT', 'conditions' => array('Project.id=Property.project_id'))
            ),
            'conditions' => $conditions,
            'order' => array('Deal.id' => 'desc'),
            'limit' => 300,
            'recursive' => -1
        ));
        $options = array();
        foreach($rows as $row)
        {
            $id = !empty($row['Deal']['id']) ? (int)$row['Deal']['id'] : 0;
            if($id <= 0)
            {
                continue;
            }
            $label = !empty($row['Deal']['invoice_no']) ? $row['Deal']['invoice_no'] : 'Deal #'.$id;
            if(!empty($row['Client']['name']))
            {
                $label .= ' - '.$row['Client']['name'];
            }
            if(!empty($row['Property']['name']))
            {
                $label .= ' - '.$row['Property']['name'];
            }
            if(!empty($row['Project']['name']))
            {
                $label .= ' ('.$row['Project']['name'].')';
            }
            $options[$id] = $label;
        }
        return $options;
    }

    protected function _clientRefundEligibleDeals()
    {
        $conditions = $this->_adminDealPermissionConditions();
        $conditions['Deal.client_refund_allowed'] = 1;
        return (array)$this->Deal->find('all', array(
            'fields' => array('Deal.id','Deal.invoice_no','Deal.client_refund_allowed_at','Client.name','Property.name','Project.name','AllowedBy.name'),
            'joins' => array(
                array('table' => 'clients', 'alias' => 'Client', 'type' => 'LEFT', 'conditions' => array('Client.id=Deal.client_id')),
                array('table' => 'properties', 'alias' => 'Property', 'type' => 'LEFT', 'conditions' => array('Property.id=Deal.property_id')),
                array('table' => 'projects', 'alias' => 'Project', 'type' => 'LEFT', 'conditions' => array('Project.id=Property.project_id')),
                array('table' => 'users', 'alias' => 'AllowedBy', 'type' => 'LEFT', 'conditions' => array('AllowedBy.id=Deal.client_refund_allowed_by'))
            ),
            'conditions' => $conditions,
            'order' => array('Deal.client_refund_allowed_at' => 'desc', 'Deal.id' => 'desc'),
            'limit' => 100,
            'recursive' => -1
        ));
    }

    protected function _setClientRefundDealFlag($dealId, $allowed)
    {
        $dealId = (int)$dealId;
        if($dealId <= 0)
        {
            $this->Session->setFlash(__('Please select a valid deal.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        $conditions = array('Deal.id' => $dealId);
        $permission = $this->_adminDealPermissionConditions();
        if(!empty($permission))
        {
            $conditions[] = $permission;
        }
        $deal = $this->Deal->find('first', array(
            'fields' => array('Deal.id'),
            'joins' => array(array('table' => 'properties', 'alias' => 'Property', 'type' => 'LEFT', 'conditions' => array('Property.id=Deal.property_id'))),
            'conditions' => $conditions,
            'recursive' => -1
        ));
        if(empty($deal['Deal']['id']))
        {
            $this->Session->setFlash(__('Deal not found or access denied.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        $this->_ensureClientRefundDealColumns();
        $this->Deal->id = $dealId;
        $save = array(
            'client_refund_allowed' => !empty($allowed) ? 1 : 0,
            'client_refund_allowed_by' => !empty($allowed) ? (int)$this->luserId : 0,
            'client_refund_allowed_at' => !empty($allowed) ? date('Y-m-d H:i:s') : null
        );
        if($this->Deal->save(array('Deal' => $save), array('validate' => false, 'callbacks' => false)))
        {
            $this->Session->setFlash(!empty($allowed) ? __('Deal enabled for client refund application.') : __('Deal removed from client refund application list.'), 'flash', array('alert' => 'success'));
        }
        else
        {
            $this->Session->setFlash(__('Unable to update deal refund permission.'), 'flash', array('alert' => 'danger'));
        }
        return $this->redirect(array('action' => 'index'));
    }

    protected function _ensureRefundCoreTables()
    {
        try
        {
            $this->Dealrefund->query("CREATE TABLE IF NOT EXISTS `deal_refunds` (
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3");
        }
        catch(Exception $e)
        {
        }
    }
}
?>
