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

class MyUtilityBillsController extends AppController
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

        // If no linked billing_client yet, synthesize a basic client structure from logged-in client
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

        // 1. Fetch all properties linked to this consumer
        $properties = array();
        $propertyIds = array();
        if($bClientId > 0)
        {
            $properties = $this->BillingProperty->find('all', array(
                'conditions' => array('BillingProperty.billing_client_id' => $bClientId),
                'order' => array('BillingProperty.id' => 'ASC'),
                'recursive' => -1
            ));
            foreach($properties as $p)
            {
                $propertyIds[] = (int)$p['BillingProperty']['id'];
            }
        }

        // 2. Fetch all meters linked to these properties
        $meters = array();
        $meterIds = array();
        $metersMap = array();
        $propMeterMap = array();

        if(!empty($propertyIds))
        {
            $meters = $this->BillingMeter->find('all', array(
                'conditions' => array('BillingMeter.billing_property_id' => $propertyIds),
                'order' => array('BillingMeter.id' => 'ASC'),
                'recursive' => -1
            ));

            foreach($meters as &$m)
            {
                $mRow = $m['BillingMeter'];
                $mId = (int)$mRow['id'];
                $pId = (int)$mRow['billing_property_id'];
                $uType = strtolower(trim((string)$mRow['utility_type']));

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
                $m['BillingMeter']['latest_photo_url'] = $photoUrl;
                $m['BillingMeter']['latest_reading_date'] = $readingDate;

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
                    $m['BillingMeter']['previous_reading'] = (float)$lastBill['BillingBill']['previous_reading'];
                    $m['BillingMeter']['current_reading'] = (float)$lastBill['BillingBill']['current_reading'];
                    $m['BillingMeter']['units_consumed'] = (float)$lastBill['BillingBill']['units_consumed'];
                }
                else
                {
                    $m['BillingMeter']['previous_reading'] = isset($mRow['initial_reading']) ? (float)$mRow['initial_reading'] : 0.0;
                }

                $mRow = $m['BillingMeter'];
                $meterIds[] = $mId;
                $metersMap[$mId] = $mRow;
                $propMeterMap[$pId . '_' . $uType] = $mRow;
            }
            unset($m);
        }

        // 3. Build Bill Query Conditions
        $allBills = array();
        $bills = array();

        if($bClientId > 0 || !empty($propertyIds))
        {
            $billBaseConds = array();
            if(!empty($propertyIds))
            {
                $billBaseConds = array(
                    'OR' => array(
                        'BillingBill.billing_client_id' => $bClientId,
                        'BillingBill.billing_property_id' => $propertyIds
                    )
                );
            }
            else
            {
                $billBaseConds = array(
                    'BillingBill.billing_client_id' => $bClientId
                );
            }

            $filterMeterId = !empty($this->request->query['meter_id']) ? (int)$this->request->query['meter_id'] : 0;
            $filterStatus = !empty($this->request->query['status']) ? strtolower(trim((string)$this->request->query['status'])) : '';
            $filterMonth = !empty($this->request->query['month']) ? trim((string)$this->request->query['month']) : '';

            $billQueryConds = $billBaseConds;
            if($filterMeterId > 0 && isset($metersMap[$filterMeterId]))
            {
                $targetMeter = $metersMap[$filterMeterId];
                $billQueryConds['BillingBill.billing_property_id'] = (int)$targetMeter['billing_property_id'];
                $billQueryConds['BillingBill.utility_type'] = $targetMeter['utility_type'];
            }

            if($filterStatus !== '' && in_array($filterStatus, array('paid', 'unpaid', 'partial'), true))
            {
                $billQueryConds['BillingBill.payment_status'] = $filterStatus;
            }

            if($filterMonth !== '')
            {
                $normMonth = $filterMonth;
                if(preg_match('/^(\d{4})-(\d{1,2})/', $filterMonth, $mm))
                {
                    $normMonth = sprintf('%04d-%02d', $mm[1], $mm[2]);
                }
                $billQueryConds['OR_MONTH'] = array(
                    'BillingBill.billing_period' => $filterMonth,
                    'BillingBill.billing_period' => $normMonth
                );
            }

            $allBills = $this->BillingBill->find('all', array(
                'conditions' => $billBaseConds,
                'order' => array('BillingBill.id' => 'DESC'),
                'recursive' => -1
            ));

            $bills = $this->BillingBill->find('all', array(
                'conditions' => $billQueryConds,
                'order' => array('BillingBill.id' => 'DESC'),
                'recursive' => -1
            ));
        }

        // 4. Calculate Metrics
        $totalBillsCount = count($allBills);
        $paidBillsCount = 0;
        $unpaidBillsCount = 0;
        $partialBillsCount = 0;
        $totalBilledAmount = 0.0;
        $totalPaidAmount = 0.0;
        $totalBalanceOutstanding = 0.0;
        $totalUnitsConsumed = 0.0;

        foreach($allBills as $bRow)
        {
            $b = $bRow['BillingBill'];
            $st = strtolower(trim((string)$b['payment_status']));
            if($st === 'paid')
            {
                $paidBillsCount++;
            }
            elseif($st === 'partial')
            {
                $partialBillsCount++;
            }
            else
            {
                $unpaidBillsCount++;
            }

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

        // 5. Bank & Payment Settings
        $bankDetails = $this->_getSettingVal('subm_bank_details', "Bank: Meezan Bank / UBL\nTitle: WHITE VILLAS\nAccount #: 0773310485346");
        $raastId = $this->_getSettingVal('subm_raast_id', '03001112233');

        $this->set(compact(
            'bClient',
            'properties',
            'meters',
            'metersMap',
            'propMeterMap',
            'bills',
            'totalBillsCount',
            'paidBillsCount',
            'unpaidBillsCount',
            'partialBillsCount',
            'totalBilledAmount',
            'totalPaidAmount',
            'totalBalanceOutstanding',
            'totalUnitsConsumed',
            'filterMeterId',
            'filterStatus',
            'filterMonth',
            'bankDetails',
            'raastId',
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

    public function print_bill($billId = null)
    {
        $this->layout = null;
        $billId = (int)$billId;
        if($billId <= 0)
        {
            $this->Session->setFlash(__('Invalid bill reference.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }

        $bClientId = !empty($this->billingAccess['billing_client_id']) ? (int)$this->billingAccess['billing_client_id'] : 0;
        $bill = $this->BillingBill->find('first', array(
            'conditions' => array('BillingBill.id' => $billId),
            'contain' => array('BillingProperty', 'BillingClient')
        ));

        if(empty($bill))
        {
            $this->Session->setFlash(__('Bill not found.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }

        // Security check: verify this bill belongs to the logged-in consumer
        $billClientId = !empty($bill['BillingBill']['billing_client_id']) ? (int)$bill['BillingBill']['billing_client_id'] : 0;
        if($bClientId > 0 && $billClientId !== $bClientId)
        {
            $this->Session->setFlash(__('Unauthorized access to requested bill.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }

        // Redirect to print template
        return $this->redirect(array('plugin' => 'billing_manager', 'controller' => 'billing_bills', 'action' => 'print_bill', $billId));
    }

    public function print_receipt($billId = null)
    {
        $this->layout = null;
        $billId = (int)$billId;
        if($billId <= 0)
        {
            $this->Session->setFlash(__('Invalid bill reference.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }

        return $this->redirect(array('plugin' => 'billing_manager', 'controller' => 'billing_bills', 'action' => 'print_receipt', $billId));
    }

    public function statement($meterId = null)
    {
        $this->layout = null;
        $bClientId = !empty($this->billingAccess['billing_client_id']) ? (int)$this->billingAccess['billing_client_id'] : 0;
        $bClient = ($bClientId > 0) ? $this->BillingClient->findById($bClientId) : null;

        if(empty($bClient))
        {
            $frontUser = (array)$this->Session->read('frontUser');
            $cData = isset($frontUser['Client']) ? $frontUser['Client'] : array();
            $bClient = array('BillingClient' => $cData);
        }

        $properties = array();
        $propertyIds = array();
        if($bClientId > 0)
        {
            $properties = $this->BillingProperty->find('all', array(
                'conditions' => array('BillingProperty.billing_client_id' => $bClientId),
                'recursive' => -1
            ));
            foreach($properties as $p) $propertyIds[] = (int)$p['BillingProperty']['id'];
        }

        $meterConds = !empty($propertyIds) ? array('BillingMeter.billing_property_id' => $propertyIds) : array('BillingMeter.id' => 0);
        $meters = $this->BillingMeter->find('all', array(
            'conditions' => $meterConds,
            'recursive' => -1
        ));

        $metersMap = array();
        $propMeterMap = array();
        foreach($meters as $m)
        {
            $mRow = $m['BillingMeter'];
            $mId = (int)$mRow['id'];
            $pId = (int)$mRow['billing_property_id'];
            $uType = strtolower(trim((string)$mRow['utility_type']));
            $metersMap[$mId] = $mRow;
            $propMeterMap[$pId . '_' . $uType] = $mRow;
        }

        $billConds = array();
        if(!empty($propertyIds))
        {
            $billConds = array(
                'OR' => array(
                    'BillingBill.billing_client_id' => $bClientId,
                    'BillingBill.billing_property_id' => $propertyIds
                )
            );
        }
        else
        {
            $billConds = array('BillingBill.billing_client_id' => $bClientId);
        }

        if(!empty($meterId) && isset($metersMap[(int)$meterId]))
        {
            $targetMeter = $metersMap[(int)$meterId];
            $billConds['BillingBill.billing_property_id'] = (int)$targetMeter['billing_property_id'];
            $billConds['BillingBill.utility_type'] = $targetMeter['utility_type'];
        }

        $bills = $this->BillingBill->find('all', array(
            'conditions' => $billConds,
            'order' => array('BillingBill.id' => 'ASC'),
            'recursive' => -1
        ));

        $this->set(compact('bClient', 'properties', 'meters', 'metersMap', 'propMeterMap', 'bills', 'meterId'));
    }
}
