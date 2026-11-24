<?php
App::uses('AppController', 'Controller');

class BillingPortalsController extends AppController
{
    public $layout = 'default';
    public $helpers = array('Html', 'Form', 'Session');
    public $uses = array(
        'BillingManager.BillingSociety',
        'BillingManager.BillingClient',
        'BillingManager.BillingProperty',
        'BillingManager.BillingMeter',
        'BillingManager.BillingReading',
        'BillingManager.BillingBill',
        'BillingManager.BillingPaymentRef',
        'BillingManager.BillingTemplate',
        'BillingManager.BillingSetting'
    );

    public function beforeFilter()
    {
        parent::beforeFilter();
        if(isset($this->Auth))
        {
            $this->Auth->allow(array('check_bill', 'history', 'client_portal', 'meter_reader', 'view_bill', 'print_bill', 'submit_payment', 'payment_webhook'));
        }
    }

    public function print_bill($id = null)
    {
        return $this->view_bill($id);
    }

    public function check_bill()
    {
        $bill = null;
        $history = array();
        $reading = null;
        $meter = null;
        $meterImageUrl = '';
        $latestPaymentReq = null;

        $query = isset($this->request->query['q']) ? trim($this->request->query['q']) : '';
        $activeTab = isset($this->request->query['tab']) ? trim($this->request->query['tab']) : 'bill';

        if($query !== '')
        {
            $cleanDigits = preg_replace('/[^0-9]/', '', $query);
            $searchId = (int)$cleanDigits;

            $conds = array(
                'OR' => array(
                    'BillingBill.bill_number' => $query,
                    'BillingBill.bill_number LIKE' => "%{$query}%",
                    'BillingProperty.property_no' => $query,
                    'BillingProperty.property_no LIKE' => "%{$query}%",
                    'BillingProperty.title LIKE' => "%{$query}%",
                    'BillingClient.name LIKE' => "%{$query}%"
                )
            );

            if($cleanDigits !== '')
            {
                $conds['OR']['BillingClient.phone LIKE'] = "%{$cleanDigits}%";
                $conds['OR']['BillingBill.id'] = $searchId;
                $conds['OR']['BillingClient.id'] = $searchId;
                if(strlen($cleanDigits) >= 4 && substr($cleanDigits, 0, 2) === '70')
                {
                    $shortClientId = (int)substr($cleanDigits, 2);
                    $conds['OR']['BillingClient.id'] = $shortClientId;
                }
            }

            $bill = $this->BillingBill->find('first', array(
                'conditions' => $conds,
                'order' => array('BillingBill.id' => 'DESC'),
                'contain' => array('BillingProperty', 'BillingClient')
            ));

            if(!empty($bill)) {
                $this->loadModel('BillingManager.BillingReading');
                $this->loadModel('BillingManager.BillingMeter');

                if(!empty($bill['BillingBill']['reading_id'])) {
                    $reading = $this->BillingReading->findById((int)$bill['BillingBill']['reading_id']);
                } elseif(!empty($bill['BillingBill']['billing_property_id'])) {
                    $reading = $this->BillingReading->find('first', array(
                        'conditions' => array('BillingReading.billing_property_id' => (int)$bill['BillingBill']['billing_property_id']),
                        'order' => array('BillingReading.id' => 'DESC')
                    ));
                }

                if(!empty($bill['BillingBill']['meter_id'])) {
                    $meter = $this->BillingMeter->findById((int)$bill['BillingBill']['meter_id']);
                }

                if(!empty($reading)) {
                    if(!empty($reading['BillingReading']['photo_url'])) {
                        $meterImageUrl = $reading['BillingReading']['photo_url'];
                    } elseif(!empty($reading['BillingReading']['reading_image'])) {
                        $meterImageUrl = $reading['BillingReading']['reading_image'];
                    } elseif(!empty($reading['BillingReading']['meter_photo'])) {
                        $meterImageUrl = $reading['BillingReading']['meter_photo'];
                    } else {
                        try {
                            $db = ConnectionManager::getDataSource('default');
                            $mf = $db->fetchAll("SELECT url_path, file_name FROM media_files WHERE source_type = 'meter_reading' ORDER BY id DESC LIMIT 1");
                            if(!empty($mf[0]['media_files']['url_path'])) {
                                $meterImageUrl = '/img/' . ltrim($mf[0]['media_files']['url_path'], '/');
                            } elseif(!empty($mf[0]['media_files']['file_name'])) {
                                $meterImageUrl = '/img/Reading/' . $mf[0]['media_files']['file_name'];
                            }
                        } catch(Exception $e) {}
                    }
                }

                if(!empty($bill['BillingBill']['id'])) {
                    $this->loadModel('BillingManager.BillingPaymentRef');
                    $latestPaymentReq = $this->BillingPaymentRef->find('first', array(
                        'conditions' => array('BillingPaymentRef.bill_id' => (int)$bill['BillingBill']['id']),
                        'order' => array('BillingPaymentRef.id' => 'DESC')
                    ));
                }
            }

            if(!empty($bill['BillingBill']['billing_client_id']))
            {
                $history = $this->BillingBill->find('all', array(
                    'conditions' => array('BillingBill.billing_client_id' => (int)$bill['BillingBill']['billing_client_id']),
                    'order' => array('BillingBill.id' => 'DESC'),
                    'limit' => 12
                ));
            }
        }

        $society = $this->BillingSociety->findById(1);
        $settings = $this->BillingSetting->find('list', array(
            'fields' => array('setting_key', 'setting_value'),
            'conditions' => array('BillingSetting.society_id' => 1)
        ));

        $this->set(compact('bill', 'reading', 'meter', 'meterImageUrl', 'history', 'query', 'society', 'settings', 'activeTab', 'latestPaymentReq'));
    }

    public function history()
    {
        $this->redirect(array('action' => 'check_bill', '?' => array('q' => isset($this->request->query['q']) ? $this->request->query['q'] : '', 'tab' => 'history')));
    }

    public function client_portal()
    {
        $sessionPhone = $this->Session->read('BillingPortal.phone');
        $client = null;
        $unpaidBills = array();
        $allBills = array();

        if($this->request->is('post') && !empty($this->request->data['Portal']['phone']))
        {
            $phone = trim($this->request->data['Portal']['phone']);
            $cleanDigits = preg_replace('/[^0-9]/', '', $phone);
            $found = $this->BillingClient->find('first', array(
                'conditions' => array('BillingClient.phone LIKE' => "%{$cleanDigits}%")
            ));
            if(!empty($found))
            {
                $this->Session->write('BillingPortal.phone', $phone);
                $this->Session->write('BillingPortal.client_id', $found['BillingClient']['id']);
                return $this->redirect(array('action' => 'client_portal'));
            }
            else
            {
                $this->Session->setFlash(__('Consumer record not found for this phone number.'), 'flash', array('alert' => 'danger'));
            }
        }

        $clientId = $this->Session->read('BillingPortal.client_id');
        if($clientId)
        {
            $client = $this->BillingClient->find('first', array(
                'conditions' => array('BillingClient.id' => (int)$clientId),
                'contain' => array('BillingProperty')
            ));
            if(!empty($client))
            {
                $unpaidBills = $this->BillingBill->find('all', array(
                    'conditions' => array('BillingBill.billing_client_id' => (int)$clientId, 'BillingBill.payment_status' => array('unpaid', 'partial')),
                    'order' => array('BillingBill.id' => 'DESC')
                ));
                $allBills = $this->BillingBill->find('all', array(
                    'conditions' => array('BillingBill.billing_client_id' => (int)$clientId),
                    'order' => array('BillingBill.id' => 'DESC')
                ));
            }
        }

        $society = $this->BillingSociety->findById(1);
        $this->set(compact('client', 'unpaidBills', 'allBills', 'society'));
    }

    public function meter_reader()
    {
        $societyId = 1;
        $month = !empty($this->request->query['month']) ? trim($this->request->query['month']) : date('Y-m');

        if($this->request->is('post'))
        {
            $meterId = (int)$this->request->data['Reading']['meter_id'];
            $propId = (int)$this->request->data['Reading']['property_id'];
            $curr = (float)$this->request->data['Reading']['current_reading'];
            $prev = (float)$this->request->data['Reading']['previous_reading'];
            $readerName = !empty($this->request->data['Reading']['reader_name']) ? trim($this->request->data['Reading']['reader_name']) : 'Mobile Reader';
            $units = max(0.0, $curr - $prev);

            $this->BillingReading->create();
            $this->BillingReading->save(array('BillingReading' => array(
                'society_id' => $societyId,
                'meter_id' => $meterId,
                'billing_property_id' => $propId,
                'reading_date' => date('Y-m-d'),
                'reading_month' => $month,
                'previous_reading' => $prev,
                'current_reading' => $curr,
                'units_consumed' => $units,
                'reader_name' => $readerName,
                'is_billed' => 0
            )));

            $this->BillingMeter->id = $meterId;
            $this->BillingMeter->saveField('current_reading', $curr);

            $this->Session->setFlash(__('Meter reading recorded successfully!'), 'flash', array('alert' => 'success'));
            return $this->redirect(array('action' => 'meter_reader', '?' => array('month' => $month)));
        }

        $meters = $this->BillingMeter->find('all', array(
            'conditions' => array('BillingMeter.society_id' => $societyId, 'BillingMeter.status' => 'active'),
            'contain' => array('BillingProperty' => array('BillingClient')),
            'order' => array('BillingProperty.property_no' => 'ASC')
        ));

        $this->set(compact('meters', 'month'));
    }

    public function view_bill($billNo = null)
    {
        $this->layout = false;
        $cond = array();
        if(is_numeric($billNo))
        {
            $cond = array(
                'OR' => array(
                    'BillingBill.id' => (int)$billNo,
                    'BillingBill.bill_number' => $billNo
                )
            );
        }
        else
        {
            $cond = array('BillingBill.bill_number' => $billNo);
        }

        $bill = $this->BillingBill->find('first', array(
            'conditions' => $cond,
            'contain' => array('BillingProperty', 'BillingClient')
        ));

        if(empty($bill))
        {
            die("Bill not found.");
        }

        $template = $this->BillingTemplate->find('first', array('order' => array('is_default' => 'DESC', 'id' => 'ASC')));
        $html = !empty($template['BillingTemplate']['html_content']) ? $template['BillingTemplate']['html_content'] : '';
        $css = !empty($template['BillingTemplate']['css_content']) ? $template['BillingTemplate']['css_content'] : '';

        $b = $bill['BillingBill'];
        $p = $bill['BillingProperty'];
        $c = $bill['BillingClient'];

        $replacements = array(
            '{{society_name}}' => 'White Villas Housing Complex',
            '{{bill_no}}' => $b['bill_number'],
            '{{client_name}}' => !empty($c['name']) ? $c['name'] : 'Valued Consumer',
            '{{property_id}}' => !empty($p['property_no']) ? $p['property_no'] : ('Unit #' . $b['billing_property_id']),
            '{{billing_month}}' => date('M-Y', strtotime($b['billing_period'] . '-01')),
            '{{due_date}}' => !empty($b['due_date']) ? date('d-M-Y', strtotime($b['due_date'])) : 'TBA',
            '{{arrears}}' => 'PKR ' . number_format((float)$b['arrears_amount'], 2),
            '{{late_fee}}' => 'PKR ' . number_format((float)$b['late_fee'], 2),
            '{{total_amount}}' => 'PKR ' . number_format((float)$b['total_amount'], 2),
            '{{paid_amount}}' => 'PKR ' . number_format((float)$b['paid_amount'], 2),
            '{{bank_details}}' => 'Bank: UBL / Meezan Bank<br>Title: WHITE VILLAS<br>Account #: 0773310485346',
            '{{qr_code}}' => '<div style="background:#0f172a;color:#fff;padding:8px;display:inline-block;font-size:11px;font-weight:bold;">SCAN & PAY<br>' . $b['bill_number'] . '</div>',
            '{{table_rows}}' => '<tr><td>Electricity Consumption (' . number_format((float)$b['units_consumed'], 1) . ' Units @ PKR ' . number_format((float)$b['unit_rate'], 2) . ')</td><td align="right">PKR ' . number_format((float)$b['consumption_amount'], 2) . '</td></tr>'
                              . '<tr><td>Maintenance & Society Services</td><td align="right">PKR ' . number_format((float)$b['extra_charges_amount'], 2) . '</td></tr>'
        );

        $rendered = str_replace(array_keys($replacements), array_values($replacements), $html);
        $this->set(compact('rendered', 'css', 'bill'));
    }

    public function submit_payment()
    {
        if($this->request->is('post'))
        {
            $billId = !empty($this->request->data['PaymentRef']['bill_id']) ? (int)$this->request->data['PaymentRef']['bill_id'] : 0;
            $clientId = !empty($this->request->data['PaymentRef']['billing_client_id']) ? (int)$this->request->data['PaymentRef']['billing_client_id'] : 0;
            $amount = !empty($this->request->data['PaymentRef']['amount']) ? (float)$this->request->data['PaymentRef']['amount'] : 0.0;
            $paymentMethod = !empty($this->request->data['PaymentRef']['payment_method']) ? trim($this->request->data['PaymentRef']['payment_method']) : 'JazzCash';
            $bank = !empty($this->request->data['PaymentRef']['bank_name']) ? trim($this->request->data['PaymentRef']['bank_name']) : $paymentMethod;
            $txnRef = !empty($this->request->data['PaymentRef']['transaction_ref']) ? trim($this->request->data['PaymentRef']['transaction_ref']) : '';
            $payerName = !empty($this->request->data['PaymentRef']['payer_name']) ? trim($this->request->data['PaymentRef']['payer_name']) : '';
            $payerPhone = !empty($this->request->data['PaymentRef']['payer_phone']) ? trim($this->request->data['PaymentRef']['payer_phone']) : '';

            $proofFile = '';
            if(!empty($this->request->data['PaymentRef']['proof_file']['name']))
            {
                $uploadDir = WWW_ROOT . 'img' . DS . 'billing_payments' . DS;
                if(!is_dir($uploadDir))
                {
                    mkdir($uploadDir, 0777, true);
                }
                $ext = pathinfo($this->request->data['PaymentRef']['proof_file']['name'], PATHINFO_EXTENSION);
                $proofFile = 'pay_' . time() . '_' . rand(100,999) . '.' . $ext;
                move_uploaded_file($this->request->data['PaymentRef']['proof_file']['tmp_name'], $uploadDir . $proofFile);
            }

            $this->BillingPaymentRef->create();
            $this->BillingPaymentRef->save(array('BillingPaymentRef' => array(
                'bill_id' => $billId,
                'billing_client_id' => $clientId,
                'amount' => $amount,
                'payment_method' => $paymentMethod,
                'bank_name' => $bank,
                'transaction_ref' => $txnRef,
                'payer_name' => $payerName,
                'payer_phone' => $payerPhone,
                'proof_file' => $proofFile,
                'status' => 'pending',
                'submission_date' => date('Y-m-d H:i:s')
            )));

            $this->Session->setFlash(__('Payment submission received! It will be verified by management shortly.'), 'flash', array('alert' => 'success'));
            return $this->redirect(array('action' => 'check_bill', '?' => array('q' => $billId)));
        }
    }

    public function payment_webhook()
    {
        $this->autoRender = false;
        header('Content-Type: application/json');

        // 1. Read input payload (JSON or POST)
        $rawPayload = file_get_contents('php://input');
        $data = json_decode($rawPayload, true);
        if(!is_array($data)) {
            $data = $this->request->data;
        }

        // 2. Extract reference identifiers
        $billNo = '';
        if(!empty($data['bill_number'])) $billNo = trim($data['bill_number']);
        elseif(!empty($data['bill_no'])) $billNo = trim($data['bill_no']);
        elseif(!empty($data['pp_BillReference'])) $billNo = trim($data['pp_BillReference']);
        elseif(!empty($data['order_id'])) $billNo = trim($data['order_id']);
        elseif(!empty($data['reference'])) $billNo = trim($data['reference']);
        elseif(!empty($data['bill_id'])) $billNo = trim($data['bill_id']);

        $amount = 0.0;
        if(isset($data['amount'])) $amount = (float)$data['amount'];
        elseif(isset($data['pp_Amount'])) $amount = (float)$data['pp_Amount'] / 100;
        elseif(isset($data['paid_amount'])) $amount = (float)$data['paid_amount'];
        elseif(isset($data['transaction_amount'])) $amount = (float)$data['transaction_amount'];

        $txnRef = '';
        if(!empty($data['transaction_ref'])) $txnRef = trim($data['transaction_ref']);
        elseif(!empty($data['pp_TxnRefNo'])) $txnRef = trim($data['pp_TxnRefNo']);
        elseif(!empty($data['txn_id'])) $txnRef = trim($data['txn_id']);
        elseif(!empty($data['reference_id'])) $txnRef = trim($data['reference_id']);
        else $txnRef = 'WH-' . strtoupper(substr(md5(uniqid()), 0, 10));

        $gateway = !empty($data['gateway']) ? trim($data['gateway']) : (!empty($data['payment_method']) ? trim($data['payment_method']) : 'Payment Gateway Webhook');

        if(empty($billNo) || $amount <= 0) {
            echo json_encode(array(
                'status' => 'error',
                'message' => 'Invalid webhook payload. Both bill identifier and positive amount are required.'
            ));
            return;
        }

        // 3. Locate bill
        $bill = null;
        if(is_numeric($billNo)) {
            $bill = $this->BillingBill->find('first', array(
                'conditions' => array(
                    'OR' => array(
                        'BillingBill.id' => (int)$billNo,
                        'BillingBill.bill_number' => $billNo
                    )
                ),
                'contain' => array('BillingProperty', 'BillingClient')
            ));
        } else {
            $bill = $this->BillingBill->find('first', array(
                'conditions' => array('BillingBill.bill_number' => $billNo),
                'contain' => array('BillingProperty', 'BillingClient')
            ));
        }

        if(empty($bill)) {
            echo json_encode(array('status' => 'error', 'message' => "Bill not found for reference: {$billNo}"));
            return;
        }

        $bId = (int)$bill['BillingBill']['id'];
        $socId = !empty($bill['BillingBill']['society_id']) ? (int)$bill['BillingBill']['society_id'] : 1;

        // 4. Feature Toggle Check
        $enabled = $this->BillingSetting->getSetting('subm_feat_auto_webhook', '1', $socId);
        if($enabled === '0' || $enabled === 0) {
            echo json_encode(array(
                'status' => 'disabled',
                'message' => 'Instant Payment Gateway Webhook is currently disabled in Feature Toggles for this society.'
            ));
            return;
        }

        // 5. Idempotency Check (prevent duplicate processing of same transaction ref)
        $existingPayment = $this->BillingPaymentRef->find('first', array(
            'conditions' => array(
                'BillingPaymentRef.bill_id' => $bId,
                'BillingPaymentRef.transaction_ref' => $txnRef
            )
        ));

        if(!empty($existingPayment)) {
            echo json_encode(array(
                'status' => 'duplicate',
                'message' => 'This transaction has already been recorded and settled.',
                'bill_id' => $bId,
                'bill_number' => $bill['BillingBill']['bill_number'],
                'current_status' => $bill['BillingBill']['payment_status']
            ));
            return;
        }

        // 6. Execute Settlement
        $prevPaid = (float)$bill['BillingBill']['paid_amount'];
        $totalAmt = (float)$bill['BillingBill']['total_amount'];
        $newPaid = $prevPaid + $amount;
        $newBalance = max(0.0, $totalAmt - $newPaid);
        $newStatus = ($newBalance <= 0) ? 'paid' : 'partial';

        $this->BillingBill->id = $bId;
        $this->BillingBill->save(array(
            'paid_amount' => $newPaid,
            'balance_amount' => $newBalance,
            'payment_status' => $newStatus,
            'payment_date' => date('Y-m-d H:i:s'),
            'payment_method' => $gateway,
            'transaction_ref' => $txnRef
        ));

        // 7. Record in billing_payment_refs audit log
        $this->BillingPaymentRef->create();
        $this->BillingPaymentRef->save(array('BillingPaymentRef' => array(
            'society_id' => $socId,
            'bill_id' => $bId,
            'billing_client_id' => !empty($bill['BillingBill']['billing_client_id']) ? (int)$bill['BillingBill']['billing_client_id'] : 0,
            'amount' => $amount,
            'bank_name' => $gateway,
            'payment_method' => $gateway,
            'transaction_ref' => $txnRef,
            'slip_url' => 'AUTO-WEBHOOK',
            'submission_date' => date('Y-m-d H:i:s'),
            'status' => 'approved',
            'approved_at' => date('Y-m-d H:i:s'),
            'notes' => 'Auto-verified and settled via payment gateway webhook listener'
        )));

        // 8. Return response
        echo json_encode(array(
            'status' => 'success',
            'message' => 'Payment settled successfully via webhook listener.',
            'bill_id' => $bId,
            'bill_number' => $bill['BillingBill']['bill_number'],
            'amount_received' => $amount,
            'new_balance' => $newBalance,
            'payment_status' => strtoupper($newStatus),
            'transaction_ref' => $txnRef,
            'settled_at' => date('Y-m-d H:i:s')
        ));
        return;
    }
}
