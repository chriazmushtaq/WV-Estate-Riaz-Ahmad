<?php
App::uses('BillingManagerAppController', 'BillingManager.Controller');

class BillingBankReconciliationsController extends BillingManagerAppController
{
    public $uses = array(
        'BillingManager.BillingBankRow',
        'BillingManager.BillingPaymentRef',
        'BillingManager.BillingBill'
    );

    public function index()
    {
        $societyId = $this->_getActiveSocietyId();
        $bankRows = $this->BillingBankRow->find('all', array(
            'conditions' => array('BillingBankRow.society_id' => $societyId),
            'order' => array('BillingBankRow.id' => 'DESC'),
            'limit' => 50
        ));
        $pendingPayments = $this->BillingPaymentRef->find('all', array(
            'conditions' => array('BillingPaymentRef.society_id' => $societyId, 'BillingPaymentRef.status' => 'pending'),
            'order' => array('BillingPaymentRef.id' => 'DESC')
        ));

        $this->set(compact('bankRows', 'pendingPayments'));
    }

    public function approve_payment($id = null)
    {
        $ref = $this->BillingPaymentRef->findById((int)$id);
        if(!empty($ref))
        {
            $billId = (int)$ref['BillingPaymentRef']['bill_id'];
            $amt = (float)$ref['BillingPaymentRef']['amount'];
            $bill = $this->BillingBill->findById($billId);
            if(!empty($bill))
            {
                $total = (float)$bill['BillingBill']['total_amount'];
                $paid = (float)$bill['BillingBill']['paid_amount'] + $amt;
                $balance = max(0.0, $total - $paid);
                $status = ($paid >= $total) ? 'paid' : 'partial';

                $this->BillingBill->id = $billId;
                $this->BillingBill->save(array('BillingBill' => array(
                    'paid_amount' => $paid,
                    'balance_amount' => $balance,
                    'payment_status' => $status,
                    'payment_date' => date('Y-m-d'),
                    'payment_method' => !empty($ref['BillingPaymentRef']['bank_name']) ? $ref['BillingPaymentRef']['bank_name'] : 'Bank Transfer',
                    'transaction_ref' => $ref['BillingPaymentRef']['transaction_ref']
                )));

                $this->BillingPaymentRef->id = (int)$id;
                $this->BillingPaymentRef->saveField('status', 'approved');

                $this->Session->setFlash(__('Payment verified and posted to bill successfully!'), 'flash', array('alert' => 'success'));
            }
        }
        return $this->redirect(array('action' => 'index'));
    }
}
