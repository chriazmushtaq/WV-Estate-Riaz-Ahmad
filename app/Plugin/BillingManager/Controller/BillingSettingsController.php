<?php
App::uses('BillingManagerAppController', 'BillingManager.Controller');

class BillingSettingsController extends BillingManagerAppController
{
    public $uses = array(
        'BillingManager.BillingSetting',
        'BillingManager.BillingSociety',
        'BillingManager.BillingExtraCharge',
        'BillingManager.BillingBill',
        'BillingManager.BillingReading',
        'BillingManager.BillingMeter',
        'BillingManager.BillingClient',
        'BillingManager.BillingProperty',
        'BillingManager.BillingLateFeeLog',
        'BillingManager.BillingTemplate'
    );

    public function index()
    {
        // 1. Determine active society_id
        $societyId = 1;
        if(!empty($this->request->query['society_id']))
        {
            $societyId = (int)$this->request->query['society_id'];
        }
        elseif(!empty($this->request->data['society_id']))
        {
            $societyId = (int)$this->request->data['society_id'];
        }
        else
        {
            $societyId = $this->_getActiveSocietyId();
        }

        $activeTab = !empty($this->request->query['tab']) ? trim($this->request->query['tab']) : 'general';

        // 2. Handle Save
        if($this->request->is(array('post', 'put')))
        {
            $settingsData = isset($this->request->data['BillingSetting']) ? $this->request->data['BillingSetting'] : array();

            // Handle Bank Rows
            if(isset($this->request->data['bank_accounts']) && is_array($this->request->data['bank_accounts']))
            {
                $bankRows = array();
                foreach($this->request->data['bank_accounts'] as $b)
                {
                    if(!empty($b['title']) || !empty($b['details']) || !empty($b['qr']))
                    {
                        $bankRows[] = array(
                            'title' => trim($b['title']),
                            'details' => trim($b['details']),
                            'qr' => trim($b['qr'])
                        );
                    }
                }
                $settingsData['subm_bank_accounts'] = serialize($bankRows);
            }

            // Handle Maintenance Breakdown Heads (subm_maintenance_heads)
            if(isset($this->request->data['m_head_name']) && is_array($this->request->data['m_head_name']))
            {
                $mHeads = array();
                foreach($this->request->data['m_head_name'] as $k => $name)
                {
                    $name = trim($name);
                    if($name !== '')
                    {
                        $mHeads[] = array(
                            'name' => $name,
                            'property_type' => isset($this->request->data['m_head_property_type'][$k]) ? trim($this->request->data['m_head_property_type'][$k]) : 'House',
                            'amount' => isset($this->request->data['m_head_amount'][$k]) ? (float)$this->request->data['m_head_amount'][$k] : 0,
                            'min_area' => isset($this->request->data['m_head_min_area'][$k]) ? trim($this->request->data['m_head_min_area'][$k]) : '',
                            'max_area' => isset($this->request->data['m_head_max_area'][$k]) ? trim($this->request->data['m_head_max_area'][$k]) : '',
                            'unit' => isset($this->request->data['m_head_unit'][$k]) ? trim($this->request->data['m_head_unit'][$k]) : 'Marla'
                        );
                    }
                }
                $settingsData['subm_maintenance_heads'] = serialize($mHeads);
            }

            // Handle Area Slabs (subm_area_slabs)
            if(isset($this->request->data['slab_meter_type']) && is_array($this->request->data['slab_meter_type']))
            {
                $slabs = array();
                foreach($this->request->data['slab_meter_type'] as $k => $mType)
                {
                    if(!empty($mType))
                    {
                        $slabs[] = array(
                            'meter_type' => trim($mType),
                            'property_type' => isset($this->request->data['slab_property_type'][$k]) ? trim($this->request->data['slab_property_type'][$k]) : '',
                            'area_unit' => isset($this->request->data['slab_area_unit'][$k]) ? trim($this->request->data['slab_area_unit'][$k]) : '',
                            'area_min' => isset($this->request->data['slab_area_min'][$k]) ? trim($this->request->data['slab_area_min'][$k]) : '',
                            'area_max' => isset($this->request->data['slab_area_max'][$k]) ? trim($this->request->data['slab_area_max'][$k]) : '',
                            'min_units' => isset($this->request->data['slab_min_units'][$k]) ? (float)$this->request->data['slab_min_units'][$k] : 0,
                            'max_units' => isset($this->request->data['slab_max_units'][$k]) ? trim($this->request->data['slab_max_units'][$k]) : '',
                            'rate' => isset($this->request->data['slab_rate'][$k]) ? (float)$this->request->data['slab_rate'][$k] : 0
                        );
                    }
                }
                $settingsData['subm_area_slabs'] = serialize($slabs);
            }

            // Handle Template Routing Rules (subm_template_routing_rules)
            if(isset($this->request->data['routing_rules']) && is_array($this->request->data['routing_rules']))
            {
                $settingsData['subm_template_routing_rules'] = serialize($this->request->data['routing_rules']);
            }

            // Save all submitted settings for this isolated society
            foreach($settingsData as $k => $v)
            {
                if(is_array($v))
                {
                    $v = serialize($v);
                }
                $this->BillingSetting->setSetting($k, $v, $societyId);
            }

            $this->Session->setFlash(__('Settings saved successfully for the selected society.'), 'flash', array('alert' => 'success'));
            return $this->redirect(array('action' => 'index', '?' => array('society_id' => $societyId, 'tab' => $activeTab)));
        }

        // 3. Load all societies
        $societies = $this->BillingSociety->find('all', array('order' => array('BillingSociety.id' => 'ASC')));
        if(empty($societies))
        {
            $societies = array(array('BillingSociety' => array('id' => 1, 'name' => 'White Villas Lahore', 'code' => 'WHIT')));
        }

        $activeSociety = null;
        foreach($societies as $soc)
        {
            if($soc['BillingSociety']['id'] == $societyId)
            {
                $activeSociety = $soc['BillingSociety'];
                break;
            }
        }
        if(!$activeSociety && !empty($societies[0]))
        {
            $activeSociety = $societies[0]['BillingSociety'];
            $societyId = (int)$activeSociety['id'];
        }

        // 4. Load settings for this society
        $allRows = $this->BillingSetting->find('all', array('conditions' => array('BillingSetting.society_id' => $societyId)));
        
        if(empty($allRows) && $societyId != 1)
        {
            $allRows = $this->BillingSetting->find('all', array('conditions' => array('BillingSetting.society_id' => 1)));
        }

        $settings = array();
        foreach($allRows as $r)
        {
            $val = $r['BillingSetting']['setting_value'];
            if(is_string($val) && (substr($val, 0, 2) === 'a:' || substr($val, 0, 2) === 'O:'))
            {
                $unserialized = @unserialize($val);
                if($unserialized !== false)
                {
                    $val = $unserialized;
                }
            }
            $settings[$r['BillingSetting']['setting_key']] = $val;
        }

        // Bank Accounts array
        $bankAccounts = array();
        if(!empty($settings['subm_bank_accounts']) && is_array($settings['subm_bank_accounts']))
        {
            $bankAccounts = $settings['subm_bank_accounts'];
        }
        else
        {
            $bankAccounts = array(
                array(
                    'title' => 'United Bank Limited UBL',
                    'details' => '<p style="margin:0;font-size:10px;text-align:center"><strong>Account Title</strong></p><p style="margin:2px 0 3px 0;font-size:14px;text-align:center"><strong>' . strtoupper($activeSociety['name']) . '</strong></p><p style="margin:0;font-size:10px;text-align:center"><strong>Account No</strong></p><p style="margin:2px 0;font-size:14px;letter-spacing:2px;text-align:center"><strong>0773310485346</strong></p>',
                    'qr' => 'https://whitevillas.com.pk/wp-content/uploads/2026/02/WHITE-VILLAS-BANK-ACCOUNT-QR-CODE.jpeg'
                ),
                array(
                    'title' => 'JAZZ CASH TILL ID',
                    'details' => '<p style="margin:0;font-size:10px;text-align:center"><strong>Account Title</strong></p><p style="margin:2px 0 3px 0;font-size:14px;text-align:center"><strong>' . strtoupper($activeSociety['name']) . '</strong></p><p style="margin:0;font-size:10px;text-align:center"><strong>TILL ID</strong></p><p style="margin:2px 0;font-size:14px;letter-spacing:2px;text-align:center"><strong>981452707</strong></p>',
                    'qr' => 'https://whitevillas.com.pk/wp-content/uploads/2026/02/white-villas-lahore-jazzcash-till-id-e1771858709333.jpeg'
                )
            );
        }

        // Maintenance Heads array
        $maintenanceHeads = array();
        if(!empty($settings['subm_maintenance_heads']) && is_array($settings['subm_maintenance_heads']))
        {
            $maintenanceHeads = $settings['subm_maintenance_heads'];
        }
        else
        {
            // Seed exact maintenance breakdown heads from WP plugin
            $maintenanceHeads = array(
                array('name' => 'Services Chargers', 'property_type' => 'Apartment', 'amount' => 500, 'min_area' => 3, 'max_area' => 4, 'unit' => 'Marla'),
                array('name' => 'Water Bill', 'property_type' => 'Apartment', 'amount' => 1101, 'min_area' => 3, 'max_area' => 4, 'unit' => 'Marla'),
                array('name' => 'Garbage Collection', 'property_type' => 'Apartment', 'amount' => 350, 'min_area' => 2, 'max_area' => 5, 'unit' => 'Marla'),
                array('name' => 'Services Chargers', 'property_type' => 'House', 'amount' => 500, 'min_area' => 3, 'max_area' => 5, 'unit' => 'Marla'),
                array('name' => 'Garbage Collection', 'property_type' => 'House', 'amount' => 350, 'min_area' => 3, 'max_area' => 10, 'unit' => 'Marla'),
                array('name' => 'Security', 'property_type' => 'House', 'amount' => 200, 'min_area' => 3, 'max_area' => 3, 'unit' => 'Marla'),
                array('name' => 'Security', 'property_type' => 'House', 'amount' => 250, 'min_area' => 4, 'max_area' => 4, 'unit' => 'Marla'),
                array('name' => 'Security', 'property_type' => 'House', 'amount' => 300, 'min_area' => 5, 'max_area' => 5, 'unit' => 'Any'),
                array('name' => 'Services Chargers', 'property_type' => 'House', 'amount' => 700, 'min_area' => 6, 'max_area' => 10, 'unit' => 'Any'),
                array('name' => 'Garbage Collection', 'property_type' => 'Plot', 'amount' => 350, 'min_area' => 1, 'max_area' => 5, 'unit' => 'Any'),
                array('name' => 'Security', 'property_type' => 'Plot', 'amount' => 400, 'min_area' => 1, 'max_area' => 10, 'unit' => 'Any')
            );
        }

        // Area Slabs array
        $areaSlabs = array();
        if(!empty($settings['subm_area_slabs']) && is_array($settings['subm_area_slabs']))
        {
            $areaSlabs = $settings['subm_area_slabs'];
        }

        // Templates & Routing Rules
        $templates = $this->BillingTemplate->find('all', array(
            'conditions' => array('BillingTemplate.society_id' => array(1, $societyId)),
            'order' => array('BillingTemplate.is_default' => 'DESC', 'BillingTemplate.id' => 'ASC')
        ));

        $routingRules = !empty($settings['subm_template_routing_rules']) ? $settings['subm_template_routing_rules'] : array();

        // Property Types & Area Units
        $propertyTypes = array('Apartment', 'House', 'Plot', 'Villa', 'Building', 'Shop');
        $areaUnits = array('Marla', 'Kanal', 'SqFt', 'Any');

        // Extra Charges
        $extraCharges = $this->BillingExtraCharge->find('all', array(
            'conditions' => array('BillingExtraCharge.society_id' => $societyId),
            'order' => array('BillingExtraCharge.id' => 'ASC')
        ));

        // Late fee logs for this society
        $lateFeeLogs = array();
        try {
            $db = ConnectionManager::getDataSource('default');
            $lateFeeLogs = $db->fetchAll("SELECT l.* FROM billing_late_fee_logs l 
                                          INNER JOIN billing_bills b ON l.bill_id = b.id 
                                          WHERE b.society_id = {$societyId} 
                                          ORDER BY l.id DESC LIMIT 20");
        } catch(Exception $e) {}

        // System SMS Gateway Settings
        $systemSmsSettings = null;
        try {
            $db = ConnectionManager::getDataSource('default');
            $sysSmsRows = $db->fetchAll("SELECT * FROM smssettings LIMIT 1");
            if(!empty($sysSmsRows[0]))
            {
                $systemSmsSettings = isset($sysSmsRows[0]['smssettings']) ? $sysSmsRows[0]['smssettings'] : $sysSmsRows[0];
            }
        } catch(Exception $e) {}

        $stats = array(
            'bills' => $this->BillingBill->find('count'),
            'readings' => $this->BillingReading->find('count'),
            'meters' => $this->BillingMeter->find('count'),
            'clients' => $this->BillingClient->find('count'),
            'properties' => $this->BillingProperty->find('count')
        );

        $this->set(compact('settings', 'bankAccounts', 'maintenanceHeads', 'areaSlabs', 'propertyTypes', 'areaUnits', 'societies', 'societyId', 'activeSociety', 'extraCharges', 'lateFeeLogs', 'activeTab', 'templates', 'routingRules', 'systemSmsSettings', 'stats'));
    }

    public function set_primary_template($id = null, $societyId = 1)
    {
        $template = $this->BillingTemplate->findById($id);
        if(!empty($template))
        {
            $tType = $template['BillingTemplate']['template_type'];
            $this->BillingTemplate->updateAll(
                array('BillingTemplate.is_default' => 0),
                array('BillingTemplate.template_type' => $tType, 'BillingTemplate.society_id' => $societyId)
            );
            $this->BillingTemplate->id = (int)$id;
            $this->BillingTemplate->saveField('is_default', 1);
            $this->Session->setFlash(sprintf(__('Template "%s" set as primary for %s.'), h($template['BillingTemplate']['name']), h($tType)), 'flash', array('alert' => 'success'));
        }
        return $this->redirect(array('action' => 'index', '?' => array('society_id' => $societyId, 'tab' => 'templates')));
    }

    public function delete_template($id = null, $societyId = 1)
    {
        $template = $this->BillingTemplate->findById($id);
        if(!empty($template))
        {
            if(!empty($template['BillingTemplate']['is_default']))
            {
                $this->Session->setFlash(__('Cannot delete a primary template. Set another template as primary first.'), 'flash', array('alert' => 'danger'));
            }
            else
            {
                $this->BillingTemplate->delete((int)$id);
                $this->Session->setFlash(__('Template deleted successfully.'), 'flash', array('alert' => 'success'));
            }
        }
        return $this->redirect(array('action' => 'index', '?' => array('society_id' => $societyId, 'tab' => 'templates')));
    }

    public function save_template()
    {
        if($this->request->is(array('post', 'put')))
        {
            $socId = !empty($this->request->data['BillingTemplate']['society_id']) ? (int)$this->request->data['BillingTemplate']['society_id'] : 1;
            $id = !empty($this->request->data['BillingTemplate']['id']) ? (int)$this->request->data['BillingTemplate']['id'] : null;
            
            if($id) {
                $this->BillingTemplate->id = $id;
            } else {
                $this->BillingTemplate->create();
            }

            if($this->BillingTemplate->save($this->request->data))
            {
                $this->Session->setFlash(__('Template saved successfully.'), 'flash', array('alert' => 'success'));
            }
            else
            {
                $this->Session->setFlash(__('Could not save template.'), 'flash', array('alert' => 'danger'));
            }
            return $this->redirect(array('action' => 'index', '?' => array('society_id' => $socId, 'tab' => 'templates')));
        }
        return $this->redirect(array('action' => 'index'));
    }

    public function run_late_fee_now()
    {
        $societyId = !empty($this->request->query['society_id']) ? (int)$this->request->query['society_id'] : $this->_getActiveSocietyId();
        
        $enabled = $this->BillingSetting->getSetting('subm_upgrade_auto_late_fee_enabled', $this->BillingSetting->getSetting('subm_feat_auto_late_fee', '1', $societyId), $societyId);
        if($enabled === '0' || $enabled === 0)
        {
            $this->Session->setFlash(__('Automated Late-Fee Engine is switched OFF for this society in Upgrades & Feature Settings.'), 'flash', array('alert' => 'warning'));
            return $this->redirect(array('action' => 'index', '?' => array('society_id' => $societyId, 'tab' => 'upgrades')));
        }

        $percent = (float)$this->BillingSetting->getSetting('subm_upgrade_late_fee_rate', $this->BillingSetting->getSetting('subm_late_fee_percent', 7.0, $societyId), $societyId);
        $graceDays = (int)$this->BillingSetting->getSetting('subm_upgrade_late_fee_grace_days', $this->BillingSetting->getSetting('subm_late_fee_grace_days', 0, $societyId), $societyId);
        $minFee = (float)$this->BillingSetting->getSetting('subm_late_fee_min', 200.0, $societyId);
        $maxFee = (float)$this->BillingSetting->getSetting('subm_late_fee_max', 2000.0, $societyId);
        $cutoffDate = date('Y-m-d', strtotime("-{$graceDays} days"));
        
        $overdueBills = $this->BillingBill->find('all', array(
            'conditions' => array(
                'BillingBill.society_id' => $societyId,
                'BillingBill.due_date <' => $cutoffDate,
                'BillingBill.payment_status' => array('unpaid', 'partial'),
                'BillingBill.late_fee_applied' => 0
            )
        ));

        $appliedCount = 0;
        $totalSurcharge = 0.0;
        foreach($overdueBills as $b)
        {
            $billId = (int)$b['BillingBill']['id'];
            $pending = (float)$b['BillingBill']['balance_amount'];
            if($pending <= 0) {
                $pending = (float)$b['BillingBill']['total_amount'] - (float)$b['BillingBill']['paid_amount'];
            }

            if($pending > 0)
            {
                $surcharge = round($pending * ($percent / 100), 2);
                if($minFee > 0 && $surcharge < $minFee) $surcharge = $minFee;
                if($maxFee > 0 && $surcharge > $maxFee) $surcharge = $maxFee;

                $prevLateFee = (float)$b['BillingBill']['late_fee'];
                $newLateFee = $prevLateFee + $surcharge;
                $newTotal = (float)$b['BillingBill']['total_amount'] + $surcharge;
                $newBalance = (float)$b['BillingBill']['balance_amount'] + $surcharge;

                $this->BillingBill->id = $billId;
                $this->BillingBill->save(array(
                    'late_fee' => $newLateFee,
                    'late_fee_applied' => 1,
                    'total_amount' => $newTotal,
                    'balance_amount' => $newBalance
                ));

                try {
                    $this->loadModel('BillingManager.BillingLateFeeLog');
                    $this->BillingLateFeeLog->create();
                    $this->BillingLateFeeLog->save(array(
                        'society_id' => $societyId,
                        'bill_id' => $billId,
                        'previous_late_fee' => $prevLateFee,
                        'new_late_fee' => $newLateFee,
                        'applied_at' => date('Y-m-d H:i:s')
                    ));
                } catch(Exception $e) {}

                $appliedCount++;
                $totalSurcharge += $surcharge;
            }
        }

        $this->Session->setFlash(sprintf(__('Automated Late-Fee Engine executed! Applied %s%% surcharge to %d overdue bills (Total surcharges: PKR %s).'), number_format($percent, 1), $appliedCount, number_format($totalSurcharge, 2)), 'flash', array('alert' => 'success'));
        return $this->redirect(array('action' => 'index', '?' => array('society_id' => $societyId, 'tab' => 'billing-rules')));
    }

    public function test_sms()
    {
        $this->autoRender = false;
        header('Content-Type: application/json');

        if(!$this->request->is('post'))
        {
            echo json_encode(array('status' => 'error', 'message' => 'Invalid request method.'));
            exit;
        }

        $societyId = !empty($this->request->data['society_id']) ? (int)$this->request->data['society_id'] : 1;
        $phone = !empty($this->request->data['test_phone']) ? trim($this->request->data['test_phone']) : '';
        $msg = !empty($this->request->data['test_message']) ? trim($this->request->data['test_message']) : 'Test SMS from White Villas Billing System: Gateway connection is verified and operational.';
        
        $useSystem = !empty($this->request->data['use_system_gateway']) ? 1 : 0;
        $customUrl = !empty($this->request->data['custom_url']) ? trim($this->request->data['custom_url']) : '';
        $customToken = !empty($this->request->data['custom_token']) ? trim($this->request->data['custom_token']) : '';
        $customSender = !empty($this->request->data['custom_sender']) ? trim($this->request->data['custom_sender']) : 'WhiteVillas';
        $customMethod = !empty($this->request->data['custom_method']) ? strtoupper(trim($this->request->data['custom_method'])) : 'GET';
        $defaultCc = !empty($this->request->data['default_cc']) ? trim($this->request->data['default_cc']) : '+92';

        if(empty($phone))
        {
            echo json_encode(array('status' => 'error', 'message' => 'Please enter a valid recipient mobile number.'));
            exit;
        }

        // Format mobile number
        if(substr($phone, 0, 1) === '0') {
            $phone = $defaultCc . substr($phone, 1);
        } elseif(substr($phone, 0, 1) !== '+') {
            $phone = $defaultCc . $phone;
        }

        $responseOutput = '';
        $modeUsed = '';

        try {
            if($useSystem)
            {
                $modeUsed = 'Central System SMS Gateway';
                $db = ConnectionManager::getDataSource('default');
                $sysRows = $db->fetchAll("SELECT * FROM smssettings LIMIT 1");
                if(empty($sysRows[0]))
                {
                    echo json_encode(array('status' => 'error', 'message' => 'Central System SMS settings not configured in database.'));
                    exit;
                }
                $sys = isset($sysRows[0]['smssettings']) ? $sysRows[0]['smssettings'] : $sysRows[0];
                $apiUrl = !empty($sys['api']) ? $sys['api'] : 'http://sms.alriazdevelopers.com/services/send.php';
                
                $params = array(
                    !empty($sys['husername']) ? $sys['husername'] : 'option' => !empty($sys['username']) ? $sys['username'] : '',
                    !empty($sys['hpassword']) ? $sys['hpassword'] : 'key' => !empty($sys['password']) ? $sys['password'] : '',
                    !empty($sys['hsenderid']) ? $sys['hsenderid'] : 'type' => !empty($sys['senderid']) ? $sys['senderid'] : 'sms',
                    !empty($sys['hmobile']) ? $sys['hmobile'] : 'number' => $phone,
                    !empty($sys['hmessage']) ? $sys['hmessage'] : 'message' => $msg
                );

                if(strtolower($sys['type']) === 'post') {
                    $ch = curl_init($apiUrl);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
                    curl_setopt($ch, CURLOPT_TIMEOUT, 12);
                    $responseOutput = curl_exec($ch);
                    curl_close($ch);
                } else {
                    $fullUrl = $apiUrl . '?' . http_build_query($params);
                    $responseOutput = @file_get_contents($fullUrl);
                }
            }
            else
            {
                $modeUsed = 'Custom Isolated Gateway';
                if(empty($customUrl))
                {
                    echo json_encode(array('status' => 'error', 'message' => 'Custom SMS Gateway URL is required.'));
                    exit;
                }

                $params = array(
                    'token' => $customToken,
                    'to' => $phone,
                    'message' => $msg,
                    'sender' => $customSender
                );

                if($customMethod === 'POST') {
                    $ch = curl_init($customUrl);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
                    curl_setopt($ch, CURLOPT_TIMEOUT, 12);
                    $responseOutput = curl_exec($ch);
                    curl_close($ch);
                } else {
                    $fullUrl = $customUrl . '?' . http_build_query($params);
                    $responseOutput = @file_get_contents($fullUrl);
                }
            }

            echo json_encode(array(
                'status' => 'success',
                'mode' => $modeUsed,
                'recipient' => $phone,
                'response' => (string)$responseOutput,
                'message' => "Test SMS dispatched to {$phone} via {$modeUsed}!"
            ));
            exit;

        } catch(Exception $e) {
            echo json_encode(array(
                'status' => 'error',
                'message' => 'SMS Sending Failed: ' . $e->getMessage()
            ));
            exit;
        }
    }

    public function save_maintenance_rules()
    {
        if ($this->request->is(array('post', 'put'))) {
            $socId = !empty($this->request->data['society_id']) ? (int)$this->request->data['society_id'] : (!empty($this->request->data['BillingSetting']['society_id']) ? (int)$this->request->data['BillingSetting']['society_id'] : $this->_getActiveSocietyId());

            $mHeads = array();
            $dataSrc = !empty($this->request->data['BillingSetting']) ? $this->request->data['BillingSetting'] : $this->request->data;
            if (!empty($dataSrc['m_head_name'])) {
                $names = $dataSrc['m_head_name'];
                $pTypes = isset($dataSrc['m_head_property_type']) ? $dataSrc['m_head_property_type'] : array();
                $minAreas = isset($dataSrc['m_head_min_area']) ? $dataSrc['m_head_min_area'] : array();
                $maxAreas = isset($dataSrc['m_head_max_area']) ? $dataSrc['m_head_max_area'] : array();
                $units = isset($dataSrc['m_head_unit']) ? $dataSrc['m_head_unit'] : array();
                $amounts = isset($dataSrc['m_head_amount']) ? $dataSrc['m_head_amount'] : array();

                foreach ($names as $idx => $name) {
                    $nameStr = trim($name);
                    if ($nameStr !== '') {
                        $mHeads[] = array(
                            'name' => $nameStr,
                            'property_type' => isset($pTypes[$idx]) ? trim($pTypes[$idx]) : 'Apartment',
                            'min_area' => isset($minAreas[$idx]) ? trim($minAreas[$idx]) : '',
                            'max_area' => isset($maxAreas[$idx]) ? trim($maxAreas[$idx]) : '',
                            'unit' => isset($units[$idx]) ? trim($units[$idx]) : 'Marla',
                            'amount' => isset($amounts[$idx]) ? (float)$amounts[$idx] : 0.0
                        );
                    }
                }
            }

            $this->BillingSetting->saveSetting('subm_maintenance_heads', serialize($mHeads), $socId);

            $this->Session->setFlash(__('Society Maintenance Breakdown Rules saved separately for this society.'), 'flash', array('alert' => 'success'));
            return $this->redirect(array('controller' => 'billing_extra_charges', 'action' => 'index', '?' => array('society_id' => $socId)));
        }
        return $this->redirect(array('controller' => 'billing_extra_charges', 'action' => 'index'));
    }
}
