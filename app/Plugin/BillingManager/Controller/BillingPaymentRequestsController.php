<?php
App::uses('BillingManagerAppController', 'BillingManager.Controller');
App::uses('CakeEmail', 'Network/Email');

class BillingPaymentRequestsController extends BillingManagerAppController
{
    public $uses = array(
        'BillingManager.BillingPaymentRef',
        'BillingManager.BillingBill',
        'BillingManager.BillingClient',
        'BillingManager.BillingProperty',
        'BillingManager.BillingSetting',
        'BillingManager.BillingSociety'
    );

    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->layout = 'Admin.default';
    }

    public function index()
    {
        $statusFilter = isset($this->request->query['status']) ? trim($this->request->query['status']) : 'all';
        $conditions = array();

        if($statusFilter !== 'all' && in_array($statusFilter, array('pending', 'confirmed', 'rejected', 'cancelled'))) {
            $conditions['BillingPaymentRef.status'] = $statusFilter;
        }

        if(!empty($this->request->query['q'])) {
            $q = trim($this->request->query['q']);
            $conditions['OR'] = array(
                'BillingPaymentRef.transaction_ref LIKE' => "%{$q}%",
                'BillingBill.bill_number LIKE' => "%{$q}%",
                'BillingClient.name LIKE' => "%{$q}%"
            );
        }

        $this->paginate = array(
            'conditions' => $conditions,
            'order' => array('BillingPaymentRef.id' => 'DESC'),
            'limit' => 20,
            'contain' => array('BillingBill', 'BillingClient')
        );

        $requests = $this->paginate('BillingPaymentRef');

        // Populate Property Titles for Bills
        foreach($requests as &$r) {
            if(!empty($r['BillingBill']['billing_property_id'])) {
                $prop = $this->BillingProperty->findById((int)$r['BillingBill']['billing_property_id']);
                $r['BillingProperty'] = !empty($prop['BillingProperty']) ? $prop['BillingProperty'] : array();
            }
        }

        // KPI Counts
        $kpis = array(
            'total' => $this->BillingPaymentRef->find('count'),
            'pending' => $this->BillingPaymentRef->find('count', array('conditions' => array('BillingPaymentRef.status' => 'pending'))),
            'confirmed' => $this->BillingPaymentRef->find('count', array('conditions' => array('BillingPaymentRef.status' => 'confirmed'))),
            'rejected' => $this->BillingPaymentRef->find('count', array('conditions' => array('BillingPaymentRef.status' => 'rejected')))
        );

        $this->set(compact('requests', 'statusFilter', 'kpis'));
    }

    public function admin_index()
    {
        return $this->index();
    }

    public function approve($id = null)
    {
        $this->request->allowMethod('post', 'get');
        $id = (int)$id;
        $req = $this->BillingPaymentRef->findById($id);

        if(empty($req)) {
            $this->Session->setFlash(__('Payment request not found.'), 'default', array('class' => 'alert alert-danger'));
            return $this->redirect(array('action' => 'index'));
        }

        if($req['BillingPaymentRef']['status'] === 'confirmed') {
            $this->Session->setFlash(__('This payment request is already approved and confirmed.'), 'default', array('class' => 'alert alert-warning'));
            return $this->redirect(array('action' => 'index'));
        }

        $billId = (int)$req['BillingPaymentRef']['bill_id'];
        $bill = $this->BillingBill->findById($billId);

        if(empty($bill)) {
            $this->Session->setFlash(__('Associated bill not found.'), 'default', array('class' => 'alert alert-danger'));
            return $this->redirect(array('action' => 'index'));
        }

        $paidAmount = (float)$req['BillingPaymentRef']['amount'];
        $currentPaid = (float)$bill['BillingBill']['paid_amount'];
        $totalAmount = (float)$bill['BillingBill']['total_amount'];
        $newPaid = $currentPaid + $paidAmount;

        $newStatus = ($newPaid >= $totalAmount) ? 'paid' : 'partial';

        // Update Bill
        $billData = array(
            'id' => $billId,
            'paid_amount' => $newPaid,
            'payment_status' => $newStatus,
            'payment_date' => date('Y-m-d H:i:s')
        );

        if($newStatus === 'paid') {
            $billData['due_late_fee'] = 0.00;
        }

        $this->BillingBill->save($billData);

        // Auto-post Income Ledger Transaction into ARD Accounts
        $this->_syncBillPaidIncomeLedgerTransaction($billId);

        // Update Payment Reference
        $adminUserId = $this->_getAdminUserId();
        $this->BillingPaymentRef->id = $id;
        $this->BillingPaymentRef->save(array(
            'status' => 'confirmed',
            'approved_by' => $adminUserId,
            'approved_at' => date('Y-m-d H:i:s')
        ));

        // Dispatch Instant Notifications (SMS, Email, WhatsApp)
        $this->_dispatchStatusNotification($req, 'approved');

        $this->Session->setFlash(__('Payment Request #%s approved! PKR %s posted to Bill #%s.', $id, number_format($paidAmount, 2), $bill['BillingBill']['bill_number']), 'default', array('class' => 'alert alert-success'));
        return $this->redirect(array('action' => 'index'));
    }

    public function admin_approve($id = null)
    {
        return $this->approve($id);
    }

    public function reject($id = null)
    {
        $this->request->allowMethod('post', 'get');
        $id = (int)$id;
        $req = $this->BillingPaymentRef->findById($id);

        if(empty($req)) {
            $this->Session->setFlash(__('Payment request not found.'), 'default', array('class' => 'alert alert-danger'));
            return $this->redirect(array('action' => 'index'));
        }

        $reason = 'Transaction verification failed.';
        if(!empty($this->request->data['rejection_reason'])) {
            $reason = trim($this->request->data['rejection_reason']);
        } elseif(!empty($this->request->data['BillingPaymentRef']['rejection_reason'])) {
            $reason = trim($this->request->data['BillingPaymentRef']['rejection_reason']);
        } elseif(!empty($this->request->query['reason'])) {
            $reason = trim($this->request->query['reason']);
        }

        $adminUserId = $this->_getAdminUserId();

        $this->BillingPaymentRef->id = $id;
        $this->BillingPaymentRef->save(array(
            'status' => 'rejected',
            'rejection_reason' => $reason,
            'approved_by' => $adminUserId,
            'approved_at' => date('Y-m-d H:i:s')
        ));

        // Dispatch Rejection Notifications (SMS, Email, WhatsApp)
        $this->_dispatchStatusNotification($req, 'rejected', $reason);

        $this->Session->setFlash(__('Payment Request #%s marked as Rejected.', $id), 'default', array('class' => 'alert alert-info'));
        return $this->redirect(array('action' => 'index'));
    }

    public function admin_reject($id = null)
    {
        return $this->reject($id);
    }

    public function cancel($id = null)
    {
        $this->request->allowMethod('post', 'get');
        $id = (int)$id;
        $req = $this->BillingPaymentRef->findById($id);

        if(!empty($req)) {
            $this->BillingPaymentRef->id = $id;
            $this->BillingPaymentRef->saveField('status', 'cancelled');
            $this->Session->setFlash(__('Payment request cancelled.'), 'default', array('class' => 'alert alert-warning'));
        }

        return $this->redirect(array('action' => 'index'));
    }

    public function admin_cancel($id = null)
    {
        return $this->cancel($id);
    }

    protected function _getAdminUserId()
    {
        $user = $this->Session->read('User');
        if(!empty($user['User']['id'])) {
            return (int)$user['User']['id'];
        }
        if(!empty($this->adminValue['User']['id'])) {
            return (int)$this->adminValue['User']['id'];
        }
        return 1;
    }

    protected function _dispatchStatusNotification($req, $statusType, $reason = '')
    {
        try {
            $billId = (int)$req['BillingPaymentRef']['bill_id'];
            $bill = $this->BillingBill->findById($billId);
            $client = !empty($req['BillingClient']) ? $req['BillingClient'] : (!empty($bill['BillingClient']) ? $bill['BillingClient'] : array());

            $phone = !empty($client['phone']) ? preg_replace('/[^0-9]/', '', $client['phone']) : '';
            $email = !empty($client['email']) ? trim($client['email']) : '';
            $clientName = !empty($client['name']) ? $client['name'] : 'Resident';
            $billNo = !empty($bill['BillingBill']['bill_number']) ? $bill['BillingBill']['bill_number'] : 'BILL';
            $amount = number_format((float)$req['BillingPaymentRef']['amount'], 2);
            $tid = !empty($req['BillingPaymentRef']['transaction_ref']) ? $req['BillingPaymentRef']['transaction_ref'] : '-';

            $society = $this->BillingSociety->findById(1);
            $societyName = !empty($society['BillingSociety']['name']) ? $society['BillingSociety']['name'] : 'White Villas';

            if($statusType === 'approved') {
                $smsMsg = "Dear {$clientName}, your online payment of PKR {$amount} for Bill #{$billNo} (TID: {$tid}) has been CONFIRMED. Thank you! - {$societyName}";
                $subject = "Payment Confirmed - Bill #{$billNo} - {$societyName}";
            } else {
                $smsMsg = "Dear {$clientName}, your payment request for Bill #{$billNo} (TID: {$tid}) was REJECTED. Reason: {$reason}. Please re-submit proof on portal. - {$societyName}";
                $subject = "Payment Verification Alert - Bill #{$billNo} - {$societyName}";
            }

            // 1. SMS Dispatch
            if(!empty($phone)) {
                $settings = $this->BillingSetting->find('list', array(
                    'fields' => array('setting_key', 'setting_value'),
                    'conditions' => array('BillingSetting.society_id' => 1)
                ));
                $smsApiUrl = !empty($settings['sms_api_url']) ? $settings['sms_api_url'] : '';
                if(!empty($smsApiUrl)) {
                    $url = str_replace(array('{{phone}}', '{{message}}'), array(urlencode($phone), urlencode($smsMsg)), $smsApiUrl);
                    @file_get_contents($url);
                }
            }

            // 2. Email Dispatch
            if(!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                try {
                    $Email = new CakeEmail('default');
                    $Email->to($email)
                          ->subject($subject)
                          ->emailFormat('html')
                          ->send("<div style='font-family:sans-serif;max-width:600px;padding:20px;border:1px solid #e2e8f0;border-radius:10px;'>"
                               . "<h3 style='color:#0284c7;'>{$societyName} - Payment Status Update</h3>"
                               . "<p>{$smsMsg}</p>"
                               . "<hr/><p style='font-size:12px;color:#64748b;'>This is an automated notification from White Villas Billing Portal.</p>"
                               . "</div>");
                } catch(Exception $e) {}
            }

        } catch(Exception $ex) {}
    }
}
