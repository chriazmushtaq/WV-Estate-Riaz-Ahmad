<?php
App::uses('AppController', 'Controller');
App::uses('CakeTime', 'Utility');
App::uses('CakeNumber', 'Utility');
App::uses('BillingClient', 'BillingManager.Model');
App::uses('BillingProperty', 'BillingManager.Model');
App::uses('BillingMeter', 'BillingManager.Model');
App::uses('BillingReading', 'BillingManager.Model');
App::uses('BillingBill', 'BillingManager.Model');
App::uses('BillingPaymentRef', 'BillingManager.Model');
App::uses('BillingTemplate', 'BillingManager.Model');
App::uses('BillingSetting', 'BillingManager.Model');

class UtilityBillsController extends AppController
{
    public $helpers = array('Html', 'Form', 'Session', 'Number', 'Time');
    public $components = array('Session');
    protected $billingAccess = null;

    protected function _getSettingVal($key, $default = null)
    {
        if(isset($this->BillingSetting) && method_exists($this->BillingSetting, 'getSetting'))
        {
            return $this->BillingSetting->getSetting($key, $default);
        }
        try {
            $row = $this->BillingSetting->find('first', array(
                'conditions' => array('BillingSetting.setting_key' => $key),
                'recursive' => -1
            ));
            if(isset($row['BillingSetting']['setting_value']) && $row['BillingSetting']['setting_value'] !== '')
            {
                return $row['BillingSetting']['setting_value'];
            }
        } catch (Exception $e) {}
        return $default;
    }

    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->authenticate();
        
        $frontUser = $this->_syncFrontClientSession();
        if(empty($frontUser['Client']['id']))
        {
            $this->Session->setFlash(__('Please login to view your utility bills.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('controller' => 'Clients', 'action' => 'login'));
        }

        $this->userId = (int)$frontUser['Client']['id'];
        $this->userValue = $frontUser;
        $this->set('UserArr', $frontUser);
        $this->set('userValue', $frontUser);
        $this->set('frontUser', $frontUser);

        $this->loadModel('BillingManager.BillingClient');
        $this->loadModel('BillingManager.BillingProperty');
        $this->loadModel('BillingManager.BillingMeter');
        $this->loadModel('BillingManager.BillingReading');
        $this->loadModel('BillingManager.BillingBill');
        $this->loadModel('BillingManager.BillingPaymentRef');
        $this->loadModel('BillingManager.BillingTemplate');
        $this->loadModel('BillingManager.BillingSetting');

        $this->billingAccess = $this->_getFrontClientBillingAccess($this->userId, isset($frontUser['Client']) ? $frontUser['Client'] : array());

        $this->set('portalClient', isset($frontUser['Client']) ? (array)$frontUser['Client'] : array());
        $this->set('frontClientPhotoUrl', $this->_frontResolveClientPhotoUrl(isset($frontUser['Client']) ? (array)$frontUser['Client'] : array()));
    }

    public function index()
    {
        $bClientId = !empty($this->billingAccess['billing_client_id']) ? (int)$this->billingAccess['billing_client_id'] : 0;
        $bClient = null;
        if($bClientId > 0)
        {
            $bClient = $this->BillingClient->findById($bClientId);
        }

        if(empty($bClient))
        {
            $frontUser = (array)$this->Session->read('frontUser');
            $cData = isset($frontUser['Client']) ? $frontUser['Client'] : array();
            $bClient = array(
                'BillingClient' => array(
                    'id' => 0,
                    'name' => !empty($cData['name']) ? $cData['name'] : __('Valued Consumer'),
                    'phone' => !empty($cData['phone']) ? $cData['phone'] : (!empty($cData['mobile']) ? $cData['mobile'] : ''),
                    'email' => !empty($cData['email']) ? $cData['email'] : '',
                    'address' => !empty($cData['address']) ? $cData['address'] : ''
                )
            );
        }

        $filterMeterId = isset($this->request->query['meter_id']) ? (int)$this->request->query['meter_id'] : 0;
        $filterStatus = isset($this->request->query['status']) ? trim((string)$this->request->query['status']) : '';
        $filterMonth = isset($this->request->query['month']) ? trim((string)$this->request->query['month']) : '';

        $properties = array();
        $meters = array();
        $metersMap = array();
        $propMeterMap = array();

        if($bClientId > 0)
        {
            $properties = $this->BillingProperty->find('all', array(
                'conditions' => array('BillingProperty.billing_client_id' => $bClientId),
                'order' => array('BillingProperty.id' => 'ASC'),
                'recursive' => -1
            ));

            $propIds = array();
            foreach($properties as $pRow)
            {
                $propIds[] = (int)$pRow['BillingProperty']['id'];
            }

            if(!empty($propIds))
            {
                $meters = $this->BillingMeter->find('all', array(
                    'conditions' => array('BillingMeter.billing_property_id' => $propIds),
                    'order' => array('BillingMeter.id' => 'ASC'),
                    'recursive' => -1
                ));

                foreach($meters as &$mRow)
                {
                    $mId = (int)$mRow['BillingMeter']['id'];
                    $pId = (int)$mRow['BillingMeter']['billing_property_id'];
                    $uType = strtolower(trim((string)$mRow['BillingMeter']['utility_type']));

                    // Fetch latest reading photo
                    $photoUrl = '';
                    $readingDate = '';
                    if(isset($this->BillingReading))
                    {
                        $lastReading = $this->BillingReading->find('first', array(
                            'conditions' => array('BillingReading.meter_id' => $mId),
                            'order' => array('BillingReading.id' => 'DESC'),
                            'recursive' => -1
                        ));
                        if(!empty($lastReading['BillingReading']['photo_url']))
                        {
                            $photoUrl = trim($lastReading['BillingReading']['photo_url']);
                            $readingDate = !empty($lastReading['BillingReading']['reading_date']) ? $lastReading['BillingReading']['reading_date'] : '';
                        }
                    }
                    $mRow['BillingMeter']['latest_photo_url'] = $photoUrl;
                    $mRow['BillingMeter']['latest_reading_date'] = $readingDate;

                    $lastBill = $this->BillingBill->find('first', array(
                        'conditions' => array(
                            'BillingBill.billing_property_id' => $pId,
                            'BillingBill.utility_type' => $uType
                        ),
                        'order' => array('BillingBill.id' => 'DESC'),
                        'recursive' => -1
                    ));
                    if(!empty($lastBill['BillingBill']))
                    {
                        $mRow['BillingMeter']['previous_reading'] = (float)$lastBill['BillingBill']['previous_reading'];
                        $mRow['BillingMeter']['current_reading'] = (float)$lastBill['BillingBill']['current_reading'];
                        $mRow['BillingMeter']['units_consumed'] = (float)$lastBill['BillingBill']['units_consumed'];
                    }
                    else
                    {
                        $mRow['BillingMeter']['previous_reading'] = isset($mRow['BillingMeter']['initial_reading']) ? (float)$mRow['BillingMeter']['initial_reading'] : 0.0;
                    }

                    $m = $mRow['BillingMeter'];
                    $metersMap[(int)$m['id']] = $m;
                    $propMeterMap[$pId . '_' . $uType] = $m;
                }
                unset($mRow);
            }
        }

        $conditions = array();
        if($bClientId > 0)
        {
            $conditions['BillingBill.billing_client_id'] = $bClientId;
        }
        else
        {
            $conditions['BillingBill.billing_client_id'] = -1;
        }

        if($filterMeterId > 0 && isset($metersMap[$filterMeterId]))
        {
            $targetMeter = $metersMap[$filterMeterId];
            $conditions['BillingBill.billing_property_id'] = (int)$targetMeter['billing_property_id'];
            $conditions['BillingBill.utility_type'] = $targetMeter['utility_type'];
        }

        if(!empty($filterStatus))
        {
            $conditions['BillingBill.payment_status'] = strtolower($filterStatus);
        }

        if(!empty($filterMonth))
        {
            $conditions['BillingBill.billing_period LIKE'] = $filterMonth . '%';
        }

        $bills = array();
        if($bClientId > 0)
        {
            $bills = $this->BillingBill->find('all', array(
                'conditions' => $conditions,
                'order' => array('BillingBill.due_date' => 'DESC', 'BillingBill.id' => 'DESC'),
                'recursive' => -1
            ));
        }

        $totalBilledAmount = 0;
        $totalPaidAmount = 0;
        $totalBalanceOutstanding = 0;
        $totalUnitsConsumed = 0;
        $totalBillsCount = count($bills);

        foreach($bills as $bRow)
        {
            $b = $bRow['BillingBill'];
            $totalBilledAmount += (float)$b['total_amount'];
            $totalPaidAmount += (float)$b['paid_amount'];
            $totalBalanceOutstanding += (float)$b['balance_amount'];
            $totalUnitsConsumed += (float)$b['units_consumed'];
        }

        $pendingProofMap = array();
        if(!empty($bClientId) && isset($this->BillingPaymentRef))
        {
            $pendingList = $this->BillingPaymentRef->find('all', array(
                'conditions' => array(
                    'BillingPaymentRef.billing_client_id' => $bClientId,
                    'BillingPaymentRef.status' => 'pending'
                ),
                'order' => array('BillingPaymentRef.id' => 'DESC'),
                'recursive' => -1
            ));
            foreach($pendingList as $pItem)
            {
                $pRow = $pItem['BillingPaymentRef'];
                $pendingProofMap[(int)$pRow['bill_id']] = $pRow;
            }
        }

        $bankDetails = $this->_getSettingVal('bank_details', "Bank: Meezan Bank Ltd\nAccount Title: White Villas Billing\nAccount No: 010101010101\nIBAN: PK12MEZN0000010101010101");
        $raastId = $this->_getSettingVal('raast_id', '03391101156');
        $lateFeePercent = (float)$this->_getSettingVal('late_fee_percent', 10);

        $this->set(compact(
            'bClient',
            'properties',
            'meters',
            'metersMap',
            'propMeterMap',
            'bills',
            'totalBillsCount',
            'totalBilledAmount',
            'totalPaidAmount',
            'totalBalanceOutstanding',
            'totalUnitsConsumed',
            'filterMeterId',
            'filterStatus',
            'filterMonth',
            'bankDetails',
            'raastId',
            'lateFeePercent',
            'pendingProofMap'
        ));
    }

    public function submit_payment()
    {
        $this->autoRender = false;
        if($this->request->is('post') && !empty($this->request->data['PaymentRef']))
        {
            $pData = $this->request->data['PaymentRef'];
            $billId = !empty($pData['bill_id']) ? (int)$pData['bill_id'] : 0;
            $clientId = !empty($pData['billing_client_id']) ? (int)$pData['billing_client_id'] : (!empty($this->billingAccess['billing_client_id']) ? (int)$this->billingAccess['billing_client_id'] : 0);
            $amount = !empty($pData['amount']) ? (float)$pData['amount'] : 0.0;
            $paymentMethod = !empty($pData['payment_method']) ? trim($pData['payment_method']) : 'JazzCash';
            $bankName = !empty($pData['bank_name']) ? trim($pData['bank_name']) : $paymentMethod;
            $txnRef = !empty($pData['transaction_ref']) ? trim($pData['transaction_ref']) : '';
            $notes = !empty($pData['notes']) ? trim($pData['notes']) : '';

            $slipUrl = '';
            if(!empty($_FILES['slip_file']['name']) && $_FILES['slip_file']['error'] === UPLOAD_ERR_OK)
            {
                $uploadDir = WWW_ROOT . 'img' . DS . 'billing_payments' . DS;
                if(!is_dir($uploadDir))
                {
                    @mkdir($uploadDir, 0777, true);
                }
                $ext = strtolower(pathinfo($_FILES['slip_file']['name'], PATHINFO_EXTENSION));
                if(in_array($ext, array('jpg', 'jpeg', 'png', 'webp', 'gif', 'pdf')))
                {
                    $fileName = 'proof_' . $billId . '_' . time() . '_' . rand(100, 999) . '.' . $ext;
                    if(@move_uploaded_file($_FILES['slip_file']['tmp_name'], $uploadDir . $fileName))
                    {
                        $slipUrl = '/img/billing_payments/' . $fileName;
                    }
                }
            }

            if(!isset($this->BillingPaymentRef))
            {
                $this->loadModel('BillingManager.BillingPaymentRef');
            }

            $this->BillingPaymentRef->create();
            $this->BillingPaymentRef->save(array('BillingPaymentRef' => array(
                'society_id' => 1,
                'bill_id' => $billId,
                'billing_client_id' => $clientId,
                'amount' => $amount,
                'bank_name' => $bankName,
                'payment_method' => $paymentMethod,
                'transaction_ref' => $txnRef,
                'slip_url' => $slipUrl,
                'submission_date' => date('Y-m-d H:i:s'),
                'status' => 'pending',
                'notes' => $notes
            )));

            $this->Session->setFlash(__('Your payment proof for Bill #' . $billId . ' has been submitted successfully! Management will verify and update your status.'), 'flash', array('alert' => 'success'));
            return $this->redirect(array('action' => 'index'));
        }
        return $this->redirect(array('action' => 'index'));
    }

    public function statement($meterId = null)
    {
        $this->layout = false;
        $bClientId = !empty($this->billingAccess['billing_client_id']) ? (int)$this->billingAccess['billing_client_id'] : 0;
        if($bClientId <= 0)
        {
            $this->Session->setFlash(__('No billing account statement available.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }

        $bClient = $this->BillingClient->findById($bClientId);
        if(empty($bClient))
        {
            $this->Session->setFlash(__('Billing client record not found.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }

        $properties = $this->BillingProperty->find('all', array(
            'conditions' => array('BillingProperty.billing_client_id' => $bClientId),
            'recursive' => -1
        ));

        $propIds = array();
        foreach($properties as $pRow)
        {
            $propIds[] = (int)$pRow['BillingProperty']['id'];
        }

        $meters = array();
        $metersMap = array();
        $propMeterMap = array();
        if(!empty($propIds))
        {
            $meters = $this->BillingMeter->find('all', array(
                'conditions' => array('BillingMeter.billing_property_id' => $propIds),
                'recursive' => -1
            ));
            foreach($meters as $mRow)
            {
                $m = $mRow['BillingMeter'];
                $metersMap[(int)$m['id']] = $m;
                $pId = (int)$m['billing_property_id'];
                $uType = strtolower(trim((string)$m['utility_type']));
                $propMeterMap[$pId . '_' . $uType] = $m;
            }
        }

        $conditions = array('BillingBill.billing_client_id' => $bClientId);
        if(!empty($meterId) && isset($metersMap[$meterId]))
        {
            $targetMeter = $metersMap[$meterId];
            $conditions['BillingBill.billing_property_id'] = (int)$targetMeter['billing_property_id'];
            $conditions['BillingBill.utility_type'] = $targetMeter['utility_type'];
        }

        $bills = $this->BillingBill->find('all', array(
            'conditions' => $conditions,
            'order' => array('BillingBill.billing_period' => 'ASC', 'BillingBill.id' => 'ASC'),
            'recursive' => -1
        ));

        $payments = array();
        if(!empty($bills))
        {
            $billIds = array();
            foreach($bills as $b)
            {
                $billIds[] = (int)$b['BillingBill']['id'];
            }
            $payments = $this->BillingPaymentRef->find('all', array(
                'conditions' => array('BillingPaymentRef.bill_id' => $billIds),
                'order' => array('BillingPaymentRef.payment_date' => 'ASC', 'BillingPaymentRef.id' => 'ASC'),
                'recursive' => -1
            ));
        }

        $societyName = $this->_getSettingVal('society_name', 'White Villas Housing Society');
        $societyPhone = $this->_getSettingVal('society_phone', '+92 339 1101156');
        $societyEmail = $this->_getSettingVal('society_email', 'info@whitevillas.com.pk');
        $societyAddress = $this->_getSettingVal('society_address', 'Bahria Town, Lahore, Pakistan');

        $this->set(compact(
            'bClient',
            'properties',
            'meters',
            'propMeterMap',
            'bills',
            'payments',
            'societyName',
            'societyPhone',
            'societyEmail',
            'societyAddress'
        ));
    }

    public function print_bill($id = null)
    {
        $id = (int)$id;
        if($id <= 0)
        {
            $this->Session->setFlash(__('Invalid bill ID.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }

        return $this->redirect(array(
            'plugin' => 'billing_manager',
            'controller' => 'billing_bills',
            'action' => 'print_bill',
            $id
        ));
    }

    public function print_receipt($id = null)
    {
        $id = (int)$id;
        if($id <= 0)
        {
            $this->Session->setFlash(__('Invalid bill ID.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }

        return $this->redirect(array(
            'plugin' => 'billing_manager',
            'controller' => 'billing_bills',
            'action' => 'print_receipt',
            $id
        ));
    }
}
