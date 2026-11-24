<?php
App::uses('AppController', 'Controller');

class MyRefundrequestsController extends AppController
{
    public $helpers = array('Html','Form','Session','Time','Number','Paginator');
    public $components = array('Session','Paginator');
    public $uses = array('MyDeal','MyPayment','Admin.Dealrefundrequest');
    public $paginate = array('Dealrefundrequest' => array('page' => 1, 'order' => array('Dealrefundrequest.id' => 'desc')));
    protected $_portalVisibleDealStatuses = array('Active','Blocked');
    protected $_reapplyAllowedRequestStatuses = array('Rejected','Cancelled');

    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->authenticate();
        $this->userId = $this->userValue['Client']['id'];
        $this->_ensureRefundRequestSchema();
        $this->_ensureClientRefundDealColumns();
        $this->_ensureRefundRequestSettingsColumns();
    }

    protected function _dealVisibilityCondition($alias = 'Deal')
    {
        if(!$this->MyDeal->hasField('deal_status'))
        {
            return array();
        }
        return array('OR' => array(
            array($alias.'.deal_status' => null),
            array($alias.'.deal_status' => ''),
            array($alias.'.deal_status' => $this->_portalVisibleDealStatuses)
        ));
    }

    protected function _refundAllowedCondition($alias = 'Deal')
    {
        if($this->MyDeal->hasField('client_refund_allowed'))
        {
            return array($alias.'.client_refund_allowed' => 1);
        }
        return array($alias.'.id' => 0);
    }

    public function index()
    {
        $conditions = array('Dealrefundrequest.client_id' => (int)$this->userId);
        $status = isset($this->request->query['status']) ? trim((string)$this->request->query['status']) : '';
        if($status !== '')
        {
            $conditions['Dealrefundrequest.status'] = $status;
        }

        $this->Paginator->settings = array(
            'Dealrefundrequest' => array(
                'fields' => array(
                    'Dealrefundrequest.*',
                    'Deal.invoice_no','Deal.total_amount','Deal.date',
                    'Property.name','Property.area',
                    'Project.name',
                    'Dealrefund.id','Dealrefund.status'
                ),
                'joins' => $this->_requestJoins(),
                'conditions' => $conditions,
                'order' => array('Dealrefundrequest.id' => 'desc'),
                'limit' => 20,
                'recursive' => -1
            )
        );
        $requests = $this->Paginator->paginate('Dealrefundrequest');
        $stats = $this->_clientRequestStats();
        $statusOptions = $this->_statusOptions();
        $dealOptions = $this->_clientDealOptions();
        $this->set(compact('requests','stats','statusOptions','status','dealOptions'));
    }

    public function apply($dealId = null)
    {
        $dealId = (int)$dealId;
        if($this->request->is(array('post','put')))
        {
            $row = isset($this->request->data['Dealrefundrequest']) ? (array)$this->request->data['Dealrefundrequest'] : array();
            $dealId = !empty($row['deal_id']) ? (int)$row['deal_id'] : $dealId;
            $deal = $this->_clientDealRow($dealId);
            if(empty($deal['MyDeal']['id']))
            {
                $this->Session->setFlash(__('Please select a valid deal.'), 'flash', array('alert' => 'danger'));
                return $this->redirect(array('action' => 'apply'));
            }
            $blockingRequest = $this->_blockingRefundRequest($dealId);
            if(!empty($blockingRequest['Dealrefundrequest']['id']))
            {
                $this->Session->setFlash(__('A refund request is already in process for this deal. You can apply again only if the current request is rejected or cancelled.'), 'flash', array('alert' => 'warning'));
                return $this->redirect(array('action' => 'index'));
            }
            $rejectedRequest = $this->_rejectedRefundRequestForDeal($dealId);
            if(!empty($rejectedRequest['Dealrefundrequest']['id']))
            {
                $this->Session->setFlash(__('Your previous refund request was rejected. Please appeal and resubmit that application instead of creating a new one.'), 'flash', array('alert' => 'warning'));
                return $this->redirect(array('action' => 'appeal', (int)$rejectedRequest['Dealrefundrequest']['id']));
            }

            $totalPaid = $this->_dealTotalPaid($dealId);
            $requestedAmount = max(0, (float)$totalPaid);
            $reason = trim((string)(isset($row['reason']) ? $row['reason'] : ''));
            if($reason === '')
            {
                $this->Session->setFlash(__('Please enter refund reason.'), 'flash', array('alert' => 'danger'));
                return $this->redirect(array('action' => 'apply', $dealId));
            }

            $client = !empty($deal['Client']) ? (array)$deal['Client'] : array();
            $property = !empty($deal['Property']) ? (array)$deal['Property'] : array();
            $clientCnic = isset($row['cnic']) && trim((string)$row['cnic']) !== '' ? trim((string)$row['cnic']) : (isset($client['pincode']) ? $client['pincode'] : '');
            $cnicFrontNumber = isset($row['cnic_front_number']) ? trim((string)$row['cnic_front_number']) : '';
            if($this->_normalizeCnicNumber($clientCnic) === '' || $this->_normalizeCnicNumber($cnicFrontNumber) === '' || $this->_normalizeCnicNumber($clientCnic) !== $this->_normalizeCnicNumber($cnicFrontNumber))
            {
                $this->Session->setFlash(__('CNIC front image number must match applicant CNIC before submitting.'), 'flash', array('alert' => 'danger'));
                return $this->redirect(array('action' => 'apply', $dealId));
            }

            $upload = $this->_handleRequiredDocumentUploads();
            if(!empty($upload['error']))
            {
                $this->Session->setFlash($upload['error'], 'flash', array('alert' => 'danger'));
                return $this->redirect(array('action' => 'apply', $dealId));
            }

            $projectId = isset($property['project_id']) ? (int)$property['project_id'] : 0;
            $requestNo = $this->_nextRefundRequestNumber($projectId);
            $mobile = isset($row['mobile_no']) && trim((string)$row['mobile_no']) !== '' ? trim((string)$row['mobile_no']) : (!empty($client['mobile']) ? $client['mobile'] : (isset($client['phone']) ? $client['phone'] : ''));
            $save = array(
                'deal_id' => $dealId,
                'client_id' => (int)$this->userId,
                'project_id' => $projectId,
                'property_id' => isset($property['id']) ? (int)$property['id'] : 0,
                'request_no' => $requestNo['request_no'],
                'request_prefix' => $requestNo['request_prefix'],
                'request_seq' => $requestNo['request_seq'],
                'requested_amount' => round($requestedAmount, 2),
                'approved_amount' => 0,
                'total_paid' => round($totalPaid, 2),
                'deduction_percent' => 0,
                'preferred_method' => isset($row['preferred_method']) ? trim((string)$row['preferred_method']) : 'Bank Transfer',
                'reason' => $reason,
                'client_notes' => isset($row['client_notes']) ? trim((string)$row['client_notes']) : '',
                'admin_notes' => '',
                'applicant_name' => isset($row['applicant_name']) && trim((string)$row['applicant_name']) !== '' ? trim((string)$row['applicant_name']) : (isset($client['name']) ? $client['name'] : ''),
                'father_name' => isset($row['father_name']) ? trim((string)$row['father_name']) : '',
                'cnic' => $clientCnic,
                'cnic_front_number' => $cnicFrontNumber,
                'cnic_match_status' => 'Matched',
                'passport_no' => isset($row['passport_no']) ? trim((string)$row['passport_no']) : '',
                'address' => isset($row['address']) && trim((string)$row['address']) !== '' ? trim((string)$row['address']) : (isset($client['address']) ? $client['address'] : ''),
                'mobile_no' => $mobile,
                'email' => isset($row['email']) && trim((string)$row['email']) !== '' ? trim((string)$row['email']) : (isset($client['email']) ? $client['email'] : ''),
                'account_title' => isset($row['account_title']) ? trim((string)$row['account_title']) : '',
                'account_no' => isset($row['account_no']) ? trim((string)$row['account_no']) : '',
                'bank_name' => isset($row['bank_name']) ? trim((string)$row['bank_name']) : '',
                'bank_branch_city' => isset($row['bank_branch_city']) ? trim((string)$row['bank_branch_city']) : '',
                'iban' => isset($row['iban']) ? trim((string)$row['iban']) : '',
                'wallet_name' => isset($row['wallet_name']) ? trim((string)$row['wallet_name']) : '',
                'wallet_no' => isset($row['wallet_no']) ? trim((string)$row['wallet_no']) : '',
                'status' => 'Pending',
                'status_updated_at' => date('Y-m-d H:i:s'),
                'reviewed_by' => 0,
                'converted_refund_id' => 0,
                'created_by_client' => 1
            );
            if(!empty($upload['data']))
            {
                $save = array_merge($save, $upload['data']);
            }

            $this->Dealrefundrequest->create();
            if($this->Dealrefundrequest->save(array('Dealrefundrequest' => $save), array('validate' => false)))
            {
                $this->Session->setFlash(__('Refund request submitted successfully.'), 'flash', array('alert' => 'success'));
                return $this->redirect(array('action' => 'view', (int)$this->Dealrefundrequest->id));
            }
            $this->Session->setFlash(__('Unable to submit refund request.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'apply', $dealId));
        }

        $deal = array();
        if($dealId > 0)
        {
            $deal = $this->_clientDealRow($dealId);
            if(empty($deal['MyDeal']['id']))
            {
                $this->Session->setFlash(__('Selected deal is not available.'), 'flash', array('alert' => 'danger'));
                return $this->redirect(array('action' => 'index'));
            }
            $blockingRequest = $this->_blockingRefundRequest($dealId);
            if(!empty($blockingRequest['Dealrefundrequest']['id']))
            {
                $this->Session->setFlash(__('A refund request is already in process for this deal. You can apply again only if the current request is rejected or cancelled.'), 'flash', array('alert' => 'warning'));
                return $this->redirect(array('action' => 'index'));
            }
            $rejectedRequest = $this->_rejectedRefundRequestForDeal($dealId);
            if(!empty($rejectedRequest['Dealrefundrequest']['id']))
            {
                $this->Session->setFlash(__('Your previous refund request was rejected. Please appeal and resubmit that application.'), 'flash', array('alert' => 'warning'));
                return $this->redirect(array('action' => 'appeal', (int)$rejectedRequest['Dealrefundrequest']['id']));
            }
            $this->request->data['Dealrefundrequest'] = $this->_prefillFromDeal($deal);
        }
        $dealOptions = $this->_clientDealOptions();
        $methodOptions = $this->_methodOptions();
        $totalPaid = $dealId > 0 ? $this->_dealTotalPaid($dealId) : 0;
        $this->set(compact('dealId','deal','dealOptions','methodOptions','totalPaid'));
    }

    public function appeal($id = null)
    {
        $row = $this->_clientRequestRow($id);
        if(empty($row['Dealrefundrequest']['id']))
        {
            $this->Session->setFlash(__('Refund request not found.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        $request = (array)$row['Dealrefundrequest'];
        if((string)$request['status'] !== 'Rejected')
        {
            $this->Session->setFlash(__('Only rejected refund requests can be appealed.'), 'flash', array('alert' => 'warning'));
            return $this->redirect(array('action' => 'view', (int)$request['id']));
        }
        $dealId = !empty($request['deal_id']) ? (int)$request['deal_id'] : 0;
        $deal = $this->_clientDealRow($dealId);
        if(empty($deal['MyDeal']['id']))
        {
            $this->Session->setFlash(__('This deal is not available for refund appeal.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'view', (int)$request['id']));
        }

        if($this->request->is(array('post','put')))
        {
            $form = isset($this->request->data['Dealrefundrequest']) ? (array)$this->request->data['Dealrefundrequest'] : array();
            $blockingRequest = $this->_blockingRefundRequest($dealId);
            if(!empty($blockingRequest['Dealrefundrequest']['id']))
            {
                $this->Session->setFlash(__('A refund request is already in process for this deal.'), 'flash', array('alert' => 'warning'));
                return $this->redirect(array('action' => 'index'));
            }

            $totalPaid = $this->_dealTotalPaid($dealId);
            $requestedAmount = max(0, (float)$totalPaid);
            $reason = trim((string)(isset($form['reason']) ? $form['reason'] : ''));
            if($reason === '')
            {
                $this->Session->setFlash(__('Please enter refund reason.'), 'flash', array('alert' => 'danger'));
                return $this->redirect(array('action' => 'appeal', (int)$request['id']));
            }

            $client = !empty($deal['Client']) ? (array)$deal['Client'] : array();
            $property = !empty($deal['Property']) ? (array)$deal['Property'] : array();
            $clientCnic = isset($form['cnic']) && trim((string)$form['cnic']) !== '' ? trim((string)$form['cnic']) : (isset($client['pincode']) ? $client['pincode'] : '');
            $cnicFrontNumber = isset($form['cnic_front_number']) ? trim((string)$form['cnic_front_number']) : '';
            if($this->_normalizeCnicNumber($clientCnic) === '' || $this->_normalizeCnicNumber($cnicFrontNumber) === '' || $this->_normalizeCnicNumber($clientCnic) !== $this->_normalizeCnicNumber($cnicFrontNumber))
            {
                $this->Session->setFlash(__('CNIC front image number must match applicant CNIC before submitting.'), 'flash', array('alert' => 'danger'));
                return $this->redirect(array('action' => 'appeal', (int)$request['id']));
            }

            $upload = $this->_handleRequiredDocumentUploads($request);
            if(!empty($upload['error']))
            {
                $this->Session->setFlash($upload['error'], 'flash', array('alert' => 'danger'));
                return $this->redirect(array('action' => 'appeal', (int)$request['id']));
            }

            $mobile = isset($form['mobile_no']) && trim((string)$form['mobile_no']) !== '' ? trim((string)$form['mobile_no']) : (!empty($client['mobile']) ? $client['mobile'] : (isset($client['phone']) ? $client['phone'] : ''));
            $save = array(
                'id' => (int)$request['id'],
                'deal_id' => $dealId,
                'client_id' => (int)$this->userId,
                'project_id' => isset($property['project_id']) ? (int)$property['project_id'] : (int)$request['project_id'],
                'property_id' => isset($property['id']) ? (int)$property['id'] : (int)$request['property_id'],
                'requested_amount' => round($requestedAmount, 2),
                'approved_amount' => 0,
                'total_paid' => round($totalPaid, 2),
                'deduction_percent' => 0,
                'preferred_method' => isset($form['preferred_method']) ? trim((string)$form['preferred_method']) : 'Bank Transfer',
                'reason' => $reason,
                'client_notes' => isset($form['client_notes']) ? trim((string)$form['client_notes']) : '',
                'applicant_name' => isset($form['applicant_name']) && trim((string)$form['applicant_name']) !== '' ? trim((string)$form['applicant_name']) : (isset($client['name']) ? $client['name'] : ''),
                'father_name' => isset($form['father_name']) ? trim((string)$form['father_name']) : '',
                'cnic' => $clientCnic,
                'cnic_front_number' => $cnicFrontNumber,
                'cnic_match_status' => 'Matched',
                'passport_no' => isset($form['passport_no']) ? trim((string)$form['passport_no']) : '',
                'address' => isset($form['address']) && trim((string)$form['address']) !== '' ? trim((string)$form['address']) : (isset($client['address']) ? $client['address'] : ''),
                'mobile_no' => $mobile,
                'email' => isset($form['email']) && trim((string)$form['email']) !== '' ? trim((string)$form['email']) : (isset($client['email']) ? $client['email'] : ''),
                'account_title' => isset($form['account_title']) ? trim((string)$form['account_title']) : '',
                'account_no' => isset($form['account_no']) ? trim((string)$form['account_no']) : '',
                'bank_name' => isset($form['bank_name']) ? trim((string)$form['bank_name']) : '',
                'bank_branch_city' => isset($form['bank_branch_city']) ? trim((string)$form['bank_branch_city']) : '',
                'iban' => isset($form['iban']) ? trim((string)$form['iban']) : '',
                'wallet_name' => isset($form['wallet_name']) ? trim((string)$form['wallet_name']) : '',
                'wallet_no' => isset($form['wallet_no']) ? trim((string)$form['wallet_no']) : '',
                'status' => 'Pending',
                'status_updated_at' => date('Y-m-d H:i:s'),
                'reviewed_by' => 0,
                'reviewed_at' => null,
                'converted_refund_id' => 0,
                'documents_verified' => 1,
                'created_by_client' => 1
            );
            if(!empty($upload['data']))
            {
                $save = array_merge($save, $upload['data']);
            }

            if($this->Dealrefundrequest->save(array('Dealrefundrequest' => $save), array('validate' => false)))
            {
                $this->Session->setFlash(__('Refund appeal submitted successfully.'), 'flash', array('alert' => 'success'));
                return $this->redirect(array('action' => 'view', (int)$request['id']));
            }
            $this->Session->setFlash(__('Unable to submit refund appeal.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'appeal', (int)$request['id']));
        }

        $this->request->data['Dealrefundrequest'] = $request;
        $dealOptions = $this->_clientDealOptions();
        if(!isset($dealOptions[$dealId]))
        {
            $dealOptions[$dealId] = !empty($row['Deal']['invoice_no']) ? $row['Deal']['invoice_no'] : 'Deal #'.$dealId;
        }
        $methodOptions = $this->_methodOptions();
        $totalPaid = $dealId > 0 ? $this->_dealTotalPaid($dealId) : (float)$request['total_paid'];
        $appealMode = true;
        $appealRequestId = (int)$request['id'];
        $appealRequestNo = !empty($request['request_no']) ? $request['request_no'] : '#'.$appealRequestId;
        $existingDocs = $this->_existingRefundDocuments($request);
        $this->set(compact('dealId','deal','dealOptions','methodOptions','totalPaid','appealMode','appealRequestId','appealRequestNo','existingDocs'));
        return $this->render('apply');
    }
    public function view($id = null)
    {
        $row = $this->_clientRequestRow($id);
        if(empty($row['Dealrefundrequest']['id']))
        {
            $this->Session->setFlash(__('Refund request not found.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        $convertedRefund = array();
        $refundScheduleRows = array();
        $refundProgress = array();
        if(!empty($row['Dealrefundrequest']['converted_refund_id']))
        {
            $convertedRefund = $this->_clientConvertedRefund((int)$row['Dealrefundrequest']['converted_refund_id']);
            if(!empty($convertedRefund['Dealrefund']['id']))
            {
                $refundScheduleRows = $this->_clientRefundSchedules((int)$convertedRefund['Dealrefund']['id']);
                $refundProgress = $this->_clientRefundProgress($convertedRefund['Dealrefund'], $refundScheduleRows);
            }
        }
        $this->set(compact('row','convertedRefund','refundScheduleRows','refundProgress'));
    }

    public function cancel($id = null)
    {
        $row = $this->_clientRequestRow($id);
        if(empty($row['Dealrefundrequest']['id']))
        {
            $this->Session->setFlash(__('Refund request not found.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        if(!in_array($row['Dealrefundrequest']['status'], array('Pending','Under Review'), true))
        {
            $this->Session->setFlash(__('Only pending requests can be cancelled.'), 'flash', array('alert' => 'warning'));
            return $this->redirect(array('action' => 'view', (int)$row['Dealrefundrequest']['id']));
        }
        $this->Dealrefundrequest->id = (int)$row['Dealrefundrequest']['id'];
        $this->Dealrefundrequest->save(array('Dealrefundrequest' => array('status' => 'Cancelled', 'status_updated_at' => date('Y-m-d H:i:s'))), array('validate' => false));
        $this->Session->setFlash(__('Refund request cancelled.'), 'flash', array('alert' => 'success'));
        return $this->redirect(array('action' => 'index'));
    }

    public function download($id = null, $docKey = null)
    {
        $row = $this->_clientRequestRow($id);
        if(empty($row['Dealrefundrequest']['id']))
        {
            $this->Session->setFlash(__('Refund request not found.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        return $this->_serveAttachment($row, $docKey);
    }

    protected function _requestJoins()
    {
        return array(
            array('table' => 'deals', 'alias' => 'Deal', 'type' => 'LEFT', 'conditions' => array('Deal.id=Dealrefundrequest.deal_id')),
            array('table' => 'properties', 'alias' => 'Property', 'type' => 'LEFT', 'conditions' => array('Property.id=Dealrefundrequest.property_id')),
            array('table' => 'projects', 'alias' => 'Project', 'type' => 'LEFT', 'conditions' => array('Project.id=Dealrefundrequest.project_id')),
            array('table' => 'deal_refunds', 'alias' => 'Dealrefund', 'type' => 'LEFT', 'conditions' => array('Dealrefund.id=Dealrefundrequest.converted_refund_id'))
        );
    }

    protected function _clientRequestRow($id)
    {
        $id = (int)$id;
        if($id <= 0)
        {
            return array();
        }
        return (array)$this->Dealrefundrequest->find('first', array(
            'fields' => array('Dealrefundrequest.*','Deal.invoice_no','Deal.total_amount','Property.name','Property.area','Project.name','Dealrefund.id','Dealrefund.status'),
            'joins' => $this->_requestJoins(),
            'conditions' => array('Dealrefundrequest.id' => $id, 'Dealrefundrequest.client_id' => (int)$this->userId),
            'recursive' => -1
        ));
    }

    protected function _clientConvertedRefund($refundId)
    {
        $refundId = (int)$refundId;
        if($refundId <= 0)
        {
            return array();
        }
        try
        {
            $rows = (array)$this->MyDeal->query("SELECT * FROM `deal_refunds` WHERE `id`=".(int)$refundId." AND `client_id`=".(int)$this->userId." LIMIT 1");
            if(!empty($rows[0]['deal_refunds']))
            {
                return array('Dealrefund' => (array)$rows[0]['deal_refunds']);
            }
            if(!empty($rows[0][0]))
            {
                return array('Dealrefund' => (array)$rows[0][0]);
            }
        }
        catch(Exception $e)
        {
        }
        return array();
    }

    protected function _clientRefundSchedules($refundId)
    {
        $refundId = (int)$refundId;
        if($refundId <= 0)
        {
            return array();
        }
        try
        {
            $rows = (array)$this->MyDeal->query("SELECT * FROM `deal_refund_schedules` WHERE `refund_id`=".(int)$refundId." AND `client_id`=".(int)$this->userId." ORDER BY `cycle_no` ASC, `id` ASC");
            $scheduleRows = array();
            foreach($rows as $row)
            {
                if(!empty($row['deal_refund_schedules']))
                {
                    $scheduleRows[] = array('Dealrefundschedule' => (array)$row['deal_refund_schedules']);
                }
                elseif(!empty($row[0]))
                {
                    $scheduleRows[] = array('Dealrefundschedule' => (array)$row[0]);
                }
            }
            return $scheduleRows;
        }
        catch(Exception $e)
        {
        }
        return array();
    }

    protected function _clientRefundProgress($refund, $scheduleRows)
    {
        $netRefund = !empty($refund['net_refund']) ? (float)$refund['net_refund'] : 0.0;
        $scheduledTotal = 0.0;
        $paidTotal = 0.0;
        $pendingTotal = 0.0;
        $paidCount = 0;
        $pendingCount = 0;
        foreach((array)$scheduleRows as $row)
        {
            $schedule = !empty($row['Dealrefundschedule']) ? (array)$row['Dealrefundschedule'] : array();
            $amount = !empty($schedule['amount']) ? (float)$schedule['amount'] : 0.0;
            $paidAmount = !empty($schedule['paid_amount']) ? (float)$schedule['paid_amount'] : 0.0;
            $scheduledTotal += $amount;
            if(!empty($schedule['status']) && $schedule['status'] === 'Paid')
            {
                $paidTotal += $paidAmount > 0 ? $paidAmount : $amount;
                $paidCount++;
            }
            else
            {
                $pendingTotal += max(0, $amount - $paidAmount);
                $pendingCount++;
            }
        }
        $balance = max(0, ($netRefund > 0 ? $netRefund : $scheduledTotal) - $paidTotal);
        $progressStatus = ($balance <= 0 && ($netRefund > 0 || $scheduledTotal > 0)) ? 'Completed' : 'Pending';
        return array(
            'scheduled_total' => round($scheduledTotal, 2),
            'paid_total' => round($paidTotal, 2),
            'pending_total' => round($pendingTotal, 2),
            'balance' => round($balance, 2),
            'paid_count' => $paidCount,
            'pending_count' => $pendingCount,
            'progress_status' => $progressStatus
        );
    }

    protected function _clientDealRow($dealId)
    {
        $dealId = (int)$dealId;
        if($dealId <= 0)
        {
            return array();
        }
        return (array)$this->MyDeal->find('first', array(
            'fields' => array('MyDeal.*','Client.id','Client.name','Client.pincode','Client.phone','Client.mobile','Client.email','Client.address','Property.id','Property.name','Property.project_id','Property.area','Project.name'),
            'joins' => array(
                array('table' => 'clients', 'alias' => 'Client', 'type' => 'LEFT', 'conditions' => array('Client.id=MyDeal.client_id')),
                array('table' => 'properties', 'alias' => 'Property', 'type' => 'LEFT', 'conditions' => array('Property.id=MyDeal.property_id')),
                array('table' => 'projects', 'alias' => 'Project', 'type' => 'LEFT', 'conditions' => array('Project.id=Property.project_id'))
            ),
            'conditions' => array('MyDeal.id' => $dealId, 'MyDeal.client_id' => (int)$this->userId, $this->_dealVisibilityCondition('MyDeal'), $this->_refundAllowedCondition('MyDeal')),
            'recursive' => -1
        ));
    }

    protected function _clientDealOptions()
    {
        $rows = (array)$this->MyDeal->find('all', array(
            'fields' => array('MyDeal.id','MyDeal.invoice_no','Property.name'),
            'joins' => array(
                array('table' => 'properties', 'alias' => 'Property', 'type' => 'LEFT', 'conditions' => array('Property.id=MyDeal.property_id'))
            ),
            'conditions' => array('MyDeal.client_id' => (int)$this->userId, $this->_dealVisibilityCondition('MyDeal'), $this->_refundAllowedCondition('MyDeal')),
            'order' => array('MyDeal.id' => 'desc'),
            'recursive' => -1
        ));
        $dealIds = array();
        foreach($rows as $row)
        {
            if(!empty($row['MyDeal']['id']))
            {
                $dealIds[] = (int)$row['MyDeal']['id'];
            }
        }
        $blockedDeals = $this->_blockingRefundRequestsByDeal($dealIds);
        $options = array();
        foreach($rows as $row)
        {
            $id = (int)$row['MyDeal']['id'];
            if(isset($blockedDeals[$id]))
            {
                continue;
            }
            $label = !empty($row['MyDeal']['invoice_no']) ? $row['MyDeal']['invoice_no'] : 'Deal #'.$id;
            if(!empty($row['Property']['name']))
            {
                $label .= ' - '.$row['Property']['name'];
            }
            $options[$id] = $label;
        }
        return $options;
    }

    protected function _prefillFromDeal($deal)
    {
        $client = !empty($deal['Client']) ? (array)$deal['Client'] : array();
        $mobile = !empty($client['mobile']) ? $client['mobile'] : (isset($client['phone']) ? $client['phone'] : '');
        return array(
            'deal_id' => (int)$deal['MyDeal']['id'],
            'requested_amount' => $this->_dealTotalPaid((int)$deal['MyDeal']['id']),
            'applicant_name' => isset($client['name']) ? $client['name'] : '',
            'cnic' => isset($client['pincode']) ? $client['pincode'] : '',
            'mobile_no' => $mobile,
            'email' => isset($client['email']) ? $client['email'] : '',
            'address' => isset($client['address']) ? $client['address'] : '',
            'preferred_method' => 'Bank Transfer'
        );
    }

    protected function _activeRequestExists($dealId)
    {
        return !empty($this->_blockingRefundRequest($dealId));
    }

    protected function _blockingRefundRequest($dealId)
    {
        $rows = $this->_blockingRefundRequestsByDeal(array($dealId));
        return isset($rows[(int)$dealId]) ? array('Dealrefundrequest' => $rows[(int)$dealId]) : array();
    }

    protected function _blockingRefundRequestsByDeal($dealIds)
    {
        $dealIds = array_values(array_unique(array_filter(array_map('intval', (array)$dealIds))));
        if(empty($dealIds))
        {
            return array();
        }
        $conditions = array(
            'Dealrefundrequest.deal_id' => $dealIds,
            'Dealrefundrequest.client_id' => (int)$this->userId
        );
        if(!empty($this->_reapplyAllowedRequestStatuses))
        {
            $conditions['NOT'] = array('Dealrefundrequest.status' => $this->_reapplyAllowedRequestStatuses);
        }
        $rows = (array)$this->Dealrefundrequest->find('all', array(
            'fields' => array('Dealrefundrequest.id','Dealrefundrequest.deal_id','Dealrefundrequest.request_no','Dealrefundrequest.status'),
            'conditions' => $conditions,
            'order' => array('Dealrefundrequest.id' => 'desc'),
            'recursive' => -1
        ));
        $blocked = array();
        foreach($rows as $row)
        {
            $request = isset($row['Dealrefundrequest']) ? (array)$row['Dealrefundrequest'] : array();
            $requestDealId = !empty($request['deal_id']) ? (int)$request['deal_id'] : 0;
            if($requestDealId > 0 && !isset($blocked[$requestDealId]))
            {
                $blocked[$requestDealId] = $request;
            }
        }
        return $blocked;
    }

    protected function _rejectedRefundRequestForDeal($dealId)
    {
        $dealId = (int)$dealId;
        if($dealId <= 0)
        {
            return array();
        }
        return (array)$this->Dealrefundrequest->find('first', array(
            'fields' => array('Dealrefundrequest.id','Dealrefundrequest.deal_id','Dealrefundrequest.request_no','Dealrefundrequest.status'),
            'conditions' => array(
                'Dealrefundrequest.deal_id' => $dealId,
                'Dealrefundrequest.client_id' => (int)$this->userId,
                'Dealrefundrequest.status' => 'Rejected'
            ),
            'order' => array('Dealrefundrequest.id' => 'desc'),
            'recursive' => -1
        ));
    }

    protected function _existingRefundDocuments($request)
    {
        $request = isset($request['Dealrefundrequest']) ? (array)$request['Dealrefundrequest'] : (array)$request;
        $docs = $this->_requiredRefundDocuments();
        $existing = array();
        foreach($docs as $field => $meta)
        {
            $file = !empty($request[$field]) ? basename($request[$field]) : '';
            $existing[$field] = array(
                'label' => $meta['label'],
                'file' => $file,
                'mime' => !empty($request[$meta['mime']]) ? $request[$meta['mime']] : ''
            );
        }
        return $existing;
    }

    protected function _clientRequestStats()
    {
        $stats = array('total' => 0, 'pending' => 0, 'approved' => 0, 'converted' => 0);
        $rows = (array)$this->Dealrefundrequest->find('all', array(
            'fields' => array('Dealrefundrequest.status','COUNT(Dealrefundrequest.id) AS total'),
            'conditions' => array('Dealrefundrequest.client_id' => (int)$this->userId),
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

    protected function _dealTotalPaid($dealId)
    {
        $dealId = (int)$dealId;
        if($dealId <= 0)
        {
            return 0.0;
        }
        try
        {
            $row = $this->MyPayment->find('first', array(
                'fields' => array('SUM(MyPayment.emi) AS paid_total'),
                'conditions' => array('MyPayment.deal_id' => $dealId),
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
            $row = $this->MyPayment->find('first', array(
                'fields' => array('SUM(MyPayment.amount) AS paid_total'),
                'conditions' => array('MyPayment.deal_id' => $dealId),
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

    protected function _methodOptions()
    {
        return array(
            'Bank Transfer' => __('Bank Transfer'),
            'Cheque' => __('Cheque'),
            'Cash' => __('Cash'),
            'Wallet' => __('Wallet')
        );
    }

    protected function _requiredRefundDocuments()
    {
        return array(
            'client_photo' => array('label' => __('Client Photo'), 'file' => 'client_photo_file', 'mime' => 'client_photo_mime', 'image_only' => true),
            'cnic_front' => array('label' => __('CNIC Front'), 'file' => 'cnic_front_file', 'mime' => 'cnic_front_mime', 'image_only' => true),
            'cnic_back' => array('label' => __('CNIC Back'), 'file' => 'cnic_back_file', 'mime' => 'cnic_back_mime', 'image_only' => true),
            'file_front' => array('label' => __('File Front'), 'file' => 'file_front_file', 'mime' => 'file_front_mime', 'image_only' => false),
            'file_back' => array('label' => __('File Back'), 'file' => 'file_back_file', 'mime' => 'file_back_mime', 'image_only' => false),
            'client_signature' => array('label' => __('Client Signature'), 'file' => 'client_signature_file', 'mime' => 'client_signature_mime', 'image_only' => true)
        );
    }

    protected function _handleRequiredDocumentUploads($existing = array())
    {
        $save = array();
        $existing = isset($existing['Dealrefundrequest']) ? (array)$existing['Dealrefundrequest'] : (array)$existing;
        $docs = $this->_requiredRefundDocuments();
        foreach($docs as $field => $meta)
        {
            $fileKey = $meta['file'];
            $file = isset($this->request->data['Dealrefundrequest'][$fileKey]) ? $this->request->data['Dealrefundrequest'][$fileKey] : array();
            if(empty($file) || !is_array($file) || empty($file['name']))
            {
                if(!empty($existing[$field]))
                {
                    continue;
                }
                return array('error' => sprintf(__('%s is required.'), $meta['label']));
            }
            $result = $this->_saveRefundUpload($file, $field, $meta);
            if(!empty($result['error']))
            {
                return $result;
            }
            $save[$field] = $result['file'];
            $save[$meta['mime']] = $result['mime'];
            if($field === 'cnic_front')
            {
                $save['attachment'] = $result['file'];
                $save['attachment_dir'] = 'refundrequests';
                $save['attachment_mime'] = $result['mime'];
            }
        }
        $save['documents_verified'] = 1;
        return array('data' => $save);
    }

    protected function _saveRefundUpload($file, $field, $meta)
    {
        if(empty($file) || !is_array($file) || empty($file['name']))
        {
            return array('error' => sprintf(__('%s is required.'), $meta['label']));
        }
        if(!empty($file['error']) && (int)$file['error'] !== UPLOAD_ERR_OK)
        {
            return array('error' => sprintf(__('Unable to upload %s.'), $meta['label']));
        }
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = !empty($meta['image_only']) ? array('jpg','jpeg','png','webp') : array('pdf','jpg','jpeg','png','webp');
        if(!in_array($ext, $allowed, true))
        {
            return array('error' => sprintf(__('%s must be %s.'), $meta['label'], !empty($meta['image_only']) ? __('JPG, PNG, or WEBP image') : __('PDF, JPG, PNG, or WEBP')));
        }
        if(!empty($file['size']) && (int)$file['size'] > 8 * 1024 * 1024)
        {
            return array('error' => sprintf(__('%s size must be 8 MB or less.'), $meta['label']));
        }
        $dir = APP.'RefundRequests'.DS;
        if(!is_dir($dir))
        {
            @mkdir($dir, 0775, true);
        }
        if(!is_dir($dir) || !is_writable($dir))
        {
            return array('error' => __('Refund attachment folder is not writable.'));
        }
        $safeName = 'refund_request_'.$this->userId.'_'.preg_replace('/[^a-z0-9_]+/i', '_', $field).'_'.time().'_'.mt_rand(1000, 9999).'.'.$ext;
        $target = $dir.$safeName;
        if(!move_uploaded_file($file['tmp_name'], $target))
        {
            return array('error' => sprintf(__('Unable to save %s.'), $meta['label']));
        }
        return array(
            'file' => $safeName,
            'mime' => isset($file['type']) ? $file['type'] : ''
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

    protected function _normalizeCnicNumber($value)
    {
        return preg_replace('/\D+/', '', (string)$value);
    }

    protected function _nextRefundRequestNumber($projectId)
    {
        $projectId = (int)$projectId;
        $prefix = 'RFR-'.($projectId > 0 ? $projectId.'-' : '');
        $nextNo = 1;
        $settingId = 0;
        try
        {
            $this->_ensureRefundRequestSettingsColumns();
            $rows = (array)$this->MyDeal->query("SELECT `id`, `refund_request_prefix`, `refund_request_next_no` FROM `ardaccounts_settings` WHERE `project_id`=".(int)$projectId." ORDER BY `id` DESC LIMIT 1");
            if(!empty($rows[0]))
            {
                $row = (array)$rows[0];
                if(isset($row['ardaccounts_settings']) && is_array($row['ardaccounts_settings']))
                {
                    $row = (array)$row['ardaccounts_settings'];
                }
                elseif(isset($row['Ardsetting']) && is_array($row['Ardsetting']))
                {
                    $row = (array)$row['Ardsetting'];
                }
                elseif(isset($row[0]) && is_array($row[0]))
                {
                    $row = (array)$row[0];
                }
                if(!empty($row['id']))
                {
                    $settingId = (int)$row['id'];
                }
                if(isset($row['refund_request_prefix']) && trim((string)$row['refund_request_prefix']) !== '')
                {
                    $prefix = substr(trim((string)$row['refund_request_prefix']), 0, 30);
                }
                if(!empty($row['refund_request_next_no']))
                {
                    $nextNo = max(1, (int)$row['refund_request_next_no']);
                }
            }
            $requestNo = $prefix.str_pad($nextNo, 5, '0', STR_PAD_LEFT);
            $prefixQ = $this->MyDeal->getDataSource()->value($prefix, 'string');
            if($settingId > 0)
            {
                $this->MyDeal->query("UPDATE `ardaccounts_settings` SET `refund_request_prefix`={$prefixQ}, `refund_request_next_no`=".(int)($nextNo + 1)." WHERE `id`=".(int)$settingId." LIMIT 1");
            }
            else
            {
                $this->MyDeal->query("INSERT INTO `ardaccounts_settings` (`project_id`, `refund_request_prefix`, `refund_request_next_no`) VALUES (".(int)$projectId.", {$prefixQ}, ".(int)($nextNo + 1).")");
            }
            return array('request_no' => $requestNo, 'request_prefix' => $prefix, 'request_seq' => $nextNo);
        }
        catch(Exception $e)
        {
        }
        try
        {
            $row = $this->Dealrefundrequest->find('first', array(
                'fields' => array('MAX(Dealrefundrequest.request_seq) AS max_seq'),
                'conditions' => array('Dealrefundrequest.project_id' => $projectId),
                'recursive' => -1
            ));
            $nextNo = !empty($row[0]['max_seq']) ? ((int)$row[0]['max_seq'] + 1) : 1;
        }
        catch(Exception $e)
        {
            $nextNo = (int)date('His');
        }
        return array('request_no' => $prefix.str_pad($nextNo, 5, '0', STR_PAD_LEFT), 'request_prefix' => $prefix, 'request_seq' => $nextNo);
    }

    protected function _ensureRefundRequestSchema()
    {
        $SchemaModel = $this->MyDeal;
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
            // Keep the portal available if schema bootstrap is blocked.
        }
        $this->_ensureRefundRequestColumns();
        $this->Dealrefundrequest->cacheSources = false;
        try { $this->Dealrefundrequest->schema(true); } catch(Exception $e) {}
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

    protected function _ensureRefundRequestColumns()
    {
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
                $exists = (array)$this->MyDeal->query("SHOW COLUMNS FROM `deal_refund_requests` LIKE '".addslashes($column)."'");
                if(empty($exists))
                {
                    $this->MyDeal->query($sql);
                }
            }
            catch(Exception $e)
            {
            }
        }
    }

    protected function _ensureRefundRequestSettingsColumns()
    {
        $columns = array(
            'refund_request_prefix' => "ALTER TABLE `ardaccounts_settings` ADD COLUMN `refund_request_prefix` varchar(30) DEFAULT 'RFR-'",
            'refund_request_next_no' => "ALTER TABLE `ardaccounts_settings` ADD COLUMN `refund_request_next_no` int(11) NOT NULL DEFAULT 1"
        );
        foreach($columns as $column => $sql)
        {
            try
            {
                $exists = (array)$this->MyDeal->query("SHOW COLUMNS FROM `ardaccounts_settings` LIKE '".addslashes($column)."'");
                if(empty($exists))
                {
                    $this->MyDeal->query($sql);
                }
            }
            catch(Exception $e)
            {
            }
        }
    }
}
?>
