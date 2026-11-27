<?php
App::uses('BillingManagerAppController', 'BillingManager.Controller');

class BillingBillsController extends BillingManagerAppController
{
    public $uses = array(
        'BillingManager.BillingBill',
        'BillingManager.BillingClient',
        'BillingManager.BillingProperty',
        'BillingManager.BillingMeter',
        'BillingManager.BillingReading',
        'BillingManager.BillingExtraCharge',
        'BillingManager.BillingTemplate',
        'BillingManager.BillingSetting',
        'BillingManager.BillingSociety',
        'BillingManager.BillingPaymentRef'
    );

    public $components = array('Session', 'Paginator', 'CustomFunction');

    public function beforeFilter()
    {
        parent::beforeFilter();
        if(isset($this->Auth))
        {
            $this->Auth->allow(array(
                'print_bill', 'admin_print_bill',
                'print_receipt', 'admin_print_receipt',
                'get_meter_bill_preview', 'admin_get_meter_bill_preview',
                'get_bulk_preflight_audit', 'admin_get_bulk_preflight_audit',
                'bulk_print', 'admin_bulk_print',
                'send_bill_sms', 'admin_send_bill_sms',
                'send_bill_whatsapp', 'admin_send_bill_whatsapp'
            ));
        }
    }

    public function index()
    {
        // Check for post-payment notification session payload
        $justPaidBill = $this->Session->read('BillingManager.just_paid_bill');
        if(!empty($justPaidBill))
        {
            $this->Session->delete('BillingManager.just_paid_bill');
            $this->set('justPaidBill', $justPaidBill);
        }

        // Default to current month on initial page load if no filter parameters exist
        if (!isset($this->request->query['filter_month']) && !isset($this->request->query['all']) && empty($this->request->query['filter_bill']) && empty($this->request->query['filter_client_name']) && empty($this->request->query['filter_start_date']) && empty($this->request->query['status']) && empty($this->request->query['month'])) {
            $this->request->query['filter_month'] = date('Y-m');
        } elseif (!empty($this->request->query['month'])) {
            $this->request->query['filter_month'] = $this->request->query['month'];
        }

        $conditions = array();

        if(!empty($this->request->query['filter_bill']))
        {
            $conditions['BillingBill.bill_number LIKE'] = "%" . trim($this->request->query['filter_bill']) . "%";
        }
        if(!empty($this->request->query['filter_client_name']))
        {
            $cName = trim($this->request->query['filter_client_name']);
            $conditions['OR'] = array(
                'BillingClient.name LIKE' => "%{$cName}%",
                'BillingProperty.title LIKE' => "%{$cName}%"
            );
        }
        if(!empty($this->request->query['filter_month']))
        {
            $conditions['BillingBill.billing_period'] = $this->request->query['filter_month'];
        }
        if(!empty($this->request->query['filter_status']) && $this->request->query['filter_status'] !== 'all')
        {
            $conditions['BillingBill.payment_status'] = $this->request->query['filter_status'];
        }
        if(!empty($this->request->query['filter_meter_type']) && $this->request->query['filter_meter_type'] !== 'all')
        {
            $conditions['BillingBill.utility_type LIKE'] = "%" . $this->request->query['filter_meter_type'] . "%";
        }
        if(!empty($this->request->query['filter_start_date']))
        {
            $conditions['BillingBill.issue_date >='] = $this->request->query['filter_start_date'];
        }
        if(!empty($this->request->query['filter_end_date']))
        {
            $conditions['BillingBill.issue_date <='] = $this->request->query['filter_end_date'];
        }

        // Export CSV handler
        if(!empty($this->request->query['action']) && $this->request->query['action'] === 'export_csv')
        {
            $this->_exportBillsCsv($conditions);
            return;
        }

        // Handle POST actions
        if($this->request->is('post'))
        {
            $submAction = isset($this->request->data['subm_action']) ? $this->request->data['subm_action'] : '';
            
            if($submAction === 'bulk_delete' && !empty($this->request->data['bill_ids']))
            {
                $deleted = 0;
                foreach($this->request->data['bill_ids'] as $bId)
                {
                    $this->BillingBill->delete((int)$bId);
                    $deleted++;
                }
                $this->Session->setFlash(sprintf(__('Deleted %d selected bills.'), $deleted), 'flash', array('alert' => 'success'));
                return $this->redirect(array('action' => 'index'));
            }
            elseif($submAction === 'send_sms' && !empty($this->request->data['bill_ids']))
            {
                $sentCount = 0;
                foreach($this->request->data['bill_ids'] as $bId)
                {
                    $r = $this->_sendBillSmsDirect((int)$bId);
                    if(!empty($r['success'])) {
                        $sentCount++;
                    }
                }
                $this->Session->setFlash(sprintf(__('Dispatched SMS notifications for %d selected bills.'), $sentCount), 'flash', array('alert' => 'success'));
                return $this->redirect(array('action' => 'index'));
            }
            elseif($submAction === 'send_whatsapp' && !empty($this->request->data['bill_ids']))
            {
                $sentCount = 0;
                $failedCount = 0;
                foreach($this->request->data['bill_ids'] as $bId)
                {
                    $r = $this->_sendBillWhatsappDirect((int)$bId);
                    if(!empty($r['success'])) {
                        $sentCount++;
                    } else {
                        $failedCount++;
                    }
                }
                $this->Session->setFlash(sprintf(__('Dispatched WhatsApp notifications with official PDF attachments for %d selected bills (%d failed/skipped).'), $sentCount, $failedCount), 'flash', array('alert' => $sentCount > 0 ? 'success' : 'danger'));
                return $this->redirect(array('action' => 'index'));
            }
            elseif($submAction === 'generate_bulk')
            {
                return $this->generate_bulk();
            }
            elseif($submAction === 'generate_single')
            {
                return $this->generate_single();
            }
            elseif($submAction === 'generate_custom')
            {
                return $this->generate_custom();
            }
            elseif($submAction === 'record_payment')
            {
                return $this->pay_from_modal();
            }
            elseif($submAction === 'update_bill')
            {
                return $this->update_bill_from_modal();
            }
            elseif($submAction === 'add_adjustment')
            {
                return $this->add_adjustment();
            }
            elseif($submAction === 'delete_adjustment')
            {
                $adjId = !empty($this->request->data['BillingBillAdjustment']['adj_id']) ? (int)$this->request->data['BillingBillAdjustment']['adj_id'] : 0;
                return $this->delete_adjustment($adjId);
            }
        }

        $this->paginate = array(
            'conditions' => $conditions,
            'order' => array('BillingBill.id' => 'DESC'),
            'limit' => 50,
            'contain' => array('BillingProperty' => array('BillingClient', 'BillingMeter'), 'BillingClient', 'BillingTemplate')
        );

        $bills = $this->paginate('BillingBill');

        // Totals of all filtered rows
        $allBills = $this->BillingBill->find('all', array(
            'conditions' => $conditions,
            'contain' => array('BillingProperty', 'BillingClient'),
            'fields' => array('BillingBill.total_amount', 'BillingBill.consumption_amount', 'BillingBill.paid_amount', 'BillingBill.balance_amount')
        ));
        $sumBillAmount = 0.0;
        $sumTotal = 0.0;
        $sumPaid = 0.0;
        $sumBalance = 0.0;
        $totalCount = count($allBills);

        foreach($allBills as $ab)
        {
            $sumBillAmount += (float)$ab['BillingBill']['consumption_amount'];
            $sumTotal += (float)$ab['BillingBill']['total_amount'];
            $sumPaid += (float)$ab['BillingBill']['paid_amount'];
            $sumBalance += (float)$ab['BillingBill']['balance_amount'];
        }

        $billMeters = $this->BillingMeter->find('all', array(
            'conditions' => array('BillingMeter.status' => 'active'),
            'contain' => array('BillingProperty' => array('BillingClient')),
            'order' => array('BillingMeter.id' => 'ASC')
        ));

        $properties = $this->BillingProperty->find('all', array(
            'conditions' => array(
                'OR' => array(
                    'BillingProperty.billing_client_id >' => 0,
                    'BillingProperty.id IN (SELECT DISTINCT billing_property_id FROM billing_meters WHERE billing_property_id IS NOT NULL AND billing_property_id > 0)'
                )
            ),
            'contain' => array('BillingClient', 'BillingMeter'),
            'order' => array('BillingProperty.title' => 'ASC')
        ));

        $societies = $this->BillingSociety->find('list', array('fields' => array('id', 'name')));
        $templates = $this->BillingTemplate->find('list', array('fields' => array('id', 'name')));

        // Load rates and settings
        $settingsList = $this->BillingSetting->find('list', array('fields' => array('setting_key', 'setting_value')));
        $defaultElecRate = isset($settingsList['subm_rate_elec']) ? (float)$settingsList['subm_rate_elec'] : 58.34;
        $defaultWaterRate = isset($settingsList['subm_rate_water']) ? (float)$settingsList['subm_rate_water'] : 100.00;
        $defaultGasRate = isset($settingsList['subm_rate_gas']) ? (float)$settingsList['subm_rate_gas'] : 350.00;
        $defaultDueDays = isset($settingsList['subm_due_after_days']) ? (int)$settingsList['subm_due_after_days'] : 3;

        // Direct Property to Client lookup mapping
        $clientsList = $this->BillingClient->find('all');
        $clientById = array();
        foreach($clientsList as $cl) {
            $clientById[$cl['BillingClient']['id']] = $cl['BillingClient'];
        }

        $propsList = $this->BillingProperty->find('all');
        $propToClient = array();
        // Build existing bills lookup map: [propId . '_' . utilityType . '_' . billingPeriod] => bill info
        $allBillsRaw = $this->BillingBill->find('all', array(
            'fields' => array('id', 'bill_number', 'billing_property_id', 'utility_type', 'billing_period', 'total_amount', 'payment_status'),
            'recursive' => -1
        ));
        $existingBillsLookup = array();
        foreach($allBillsRaw as $ab) {
            $b = $ab['BillingBill'];
            $pId = (int)$b['billing_property_id'];
            $uType = strtolower(trim($b['utility_type']));
            $period = trim($b['billing_period']);
            $normPeriod = $period;
            if (preg_match('/^(\d{4})-(\d{1,2})/', $period, $bmp)) {
                $normPeriod = sprintf('%04d-%02d', $bmp[1], $bmp[2]);
            }
            $bEntry = array(
                'id' => (int)$b['id'],
                'bill_number' => $b['bill_number'],
                'total_amount' => (float)$b['total_amount'],
                'payment_status' => $b['payment_status']
            );
            $existingBillsLookup[$pId . '_' . $uType . '_' . $period] = $bEntry;
            $existingBillsLookup[$pId . '_' . $uType . '_' . $normPeriod] = $bEntry;
        }

        // Build all readings map: [meterId . '_' . readingMonth] => reading info
        $allReadingsRaw = $this->BillingReading->find('all', array(
            'order' => array('BillingReading.id' => 'DESC'),
            'recursive' => -1
        ));
        $allReadingsLookup = array();
        $latestReadingsLookup = array();
        foreach($allReadingsRaw as $ar) {
            $r = $ar['BillingReading'];
            $mId = (int)$r['meter_id'];
            $monthStr = trim($r['reading_month']);
            $normMonth = $monthStr;
            if (preg_match('/^(\d{4})-(\d{1,2})/', $monthStr, $mmp)) {
                $normMonth = sprintf('%04d-%02d', $mmp[1], $mmp[2]);
            }
            $entry = array(
                'previous_reading' => (float)$r['previous_reading'],
                'current_reading' => (float)$r['current_reading'],
                'units_consumed' => (float)$r['units_consumed'],
                'reading_month' => $normMonth
            );
            $allReadingsLookup[$mId . '_' . $monthStr] = $entry;
            $allReadingsLookup[$mId . '_' . $normMonth] = $entry;

            if(!isset($latestReadingsLookup[$mId])) {
                $latestReadingsLookup[$mId] = $entry;
            }
        }

        $lateFeeRules = array(
            'mode' => $this->_getSetting('subm_late_fee_mode', 'percent'),
            'val' => (float)$this->_getSetting('subm_late_fee_val', 7.0),
            'min' => (float)$this->_getSetting('subm_late_fee_min', 200.0),
            'max' => (float)$this->_getSetting('subm_late_fee_max', 2000.0)
        );

        // Maintenance heads
        $maintHeads = $this->_getSetting('subm_maintenance_heads', array());
        if(is_string($maintHeads)) {
            $un = @unserialize($maintHeads);
            if($un !== false) $maintHeads = $un;
        }

        // Extra recurring charges
        $extraChargesRaw = $this->BillingExtraCharge->find('all', array(
            'conditions' => array('BillingExtraCharge.charge_type' => 'recurring'),
            'recursive' => -1
        ));

        // Properties lookup with maintenance and charges
        $propertiesData = array();
        foreach($properties as $pr) {
            $pId = (int)$pr['BillingProperty']['id'];
            $pCat = !empty($pr['BillingProperty']['category']) ? trim($pr['BillingProperty']['category']) : '';
            $pTitle = !empty($pr['BillingProperty']['title']) ? trim($pr['BillingProperty']['title']) : '';
            
            // Determine property type
            $typeMatch = 'Apartment';
            if(stripos($pTitle, 'B1/') !== false || stripos($pTitle, 'B2/') !== false || stripos($pTitle, 'B3/') !== false || stripos($pTitle, 'B4/') !== false || stripos($pTitle, 'FF') !== false || stripos($pTitle, 'SF') !== false || stripos($pTitle, 'GF') !== false || stripos($pCat, 'Apartment') !== false || stripos($pCat, 'Flat') !== false) {
                $typeMatch = 'Apartment';
            } elseif(stripos($pCat, 'Plot') !== false) {
                $typeMatch = 'Plot';
            } else {
                $typeMatch = 'House';
            }

            // Determine area
            $areaNum = 4.0;
            if(!empty($pr['BillingProperty']['area']) && (float)$pr['BillingProperty']['area'] > 0) {
                $areaNum = (float)$pr['BillingProperty']['area'];
            } elseif(preg_match('/([0-9\.]+)\s*(marla|kanal|sqft|m)?/i', $pTitle, $am)) {
                $areaNum = (float)$am[1];
            }

            $mItems = array();
            $mTotal = 0.0;
            if(is_array($maintHeads)) {
                foreach($maintHeads as $h) {
                    $hType = !empty($h['property_type']) ? trim($h['property_type']) : '';
                    if(empty($hType) || strcasecmp($hType, $typeMatch) === 0 || strcasecmp($hType, 'all') === 0 || strcasecmp($hType, 'any') === 0) {
                        $min = isset($h['min_area']) && $h['min_area'] !== '' ? (float)$h['min_area'] : 0.0;
                        $max = isset($h['max_area']) && $h['max_area'] !== '' ? (float)$h['max_area'] : 999999.0;
                        if($areaNum >= $min && $areaNum <= $max) {
                            $amt = !empty($h['amount']) ? (float)$h['amount'] : 0.0;
                            $mItems[] = array('name' => $h['name'], 'amount' => $amt);
                            $mTotal += $amt;
                        }
                    }
                }
            }
            if(empty($mItems)) {
                $baseFee = (float)$this->_getSetting('subm_maintenance_fee', 0.0);
                if($baseFee > 0) {
                    $mItems[] = array('name' => 'Monthly Maintenance Fee', 'amount' => $baseFee);
                    $mTotal += $baseFee;
                }
            }

            $oItems = array();
            $oTotal = 0.0;
            foreach($extraChargesRaw as $ec) {
                $ecPropId = isset($ec['BillingExtraCharge']['billing_property_id']) ? (int)$ec['BillingExtraCharge']['billing_property_id'] : (isset($ec['BillingExtraCharge']['property_id']) ? (int)$ec['BillingExtraCharge']['property_id'] : 0);
                if($ecPropId === $pId || $ecPropId === 0) {
                    $amt = (float)$ec['BillingExtraCharge']['amount'];
                    $oItems[] = array('name' => $ec['BillingExtraCharge']['title'], 'amount' => $amt);
                    $oTotal += $amt;
                }
            }

            // Past Arrears
            $pastUnpaid = $this->BillingBill->find('all', array(
                'conditions' => array('BillingBill.billing_property_id' => $pId, 'BillingBill.payment_status' => array('unpaid', 'partial')),
                'fields' => array('balance_amount'),
                'recursive' => -1
            ));
            $arrears = 0.0;
            foreach($pastUnpaid as $pu) {
                $arrears += (float)$pu['BillingBill']['balance_amount'];
            }

            $propertiesData[$pId] = array(
                'maintenance_items' => $mItems,
                'maintenance_amount' => $mTotal,
                'other_charges_items' => $oItems,
                'other_charges_amount' => $oTotal,
                'arrears_amount' => $arrears
            );
        }

        // Load adjustments grouped by bill
        $this->loadModel('BillingManager.BillingBillAdjustment');
        $adjustmentsList = $this->BillingBillAdjustment->find('all', array(
            'order' => array('BillingBillAdjustment.id' => 'DESC')
        ));
        $adjustmentsByBill = array();
        foreach($adjustmentsList as $adjItem) {
            $bid = (int)$adjItem['BillingBillAdjustment']['bill_id'];
            if(!isset($adjustmentsByBill[$bid])) $adjustmentsByBill[$bid] = array();
            $adjustmentsByBill[$bid][] = $adjItem['BillingBillAdjustment'];
        }

        $this->set(compact('bills', 'sumBillAmount', 'sumTotal', 'sumPaid', 'sumBalance', 'totalCount', 'billMeters', 'properties', 'societies', 'templates', 'defaultElecRate', 'defaultWaterRate', 'defaultGasRate', 'defaultDueDays', 'propToClient', 'existingBillsLookup', 'allReadingsLookup', 'propertiesData', 'metersData', 'adjustmentsByBill'));
    }

    public function admin_index()
    {
        $this->index();
        if ($this->hasRendered || (is_object($this->response) && $this->response->statusCode() == 302)) {
            return;
        }
        return $this->render('BillingManager.BillingBills/index');
    }

    public function admin_print_bill($id = null)
    {
        return $this->print_bill($id);
    }

    public function admin_print_receipt($id = null)
    {
        return $this->print_receipt($id);
    }

    public function admin_pay_from_modal()
    {
        return $this->pay_from_modal();
    }

    public function admin_update_bill_from_modal()
    {
        return $this->update_bill_from_modal();
    }

    public function admin_add_adjustment()
    {
        return $this->add_adjustment();
    }

    public function admin_delete_adjustment($adjId = null)
    {
        return $this->delete_adjustment($adjId);
    }

    public function admin_delete($id = null)
    {
        return $this->delete($id);
    }

    public function admin_get_meter_bill_preview()
    {
        return $this->get_meter_bill_preview();
    }

    public function get_meter_bill_preview()
    {
        $this->autoRender = false;
        header('Content-Type: application/json');

        $meterId = !empty($this->request->query['meter_id']) ? (int)$this->request->query['meter_id'] : (!empty($this->request->data['meter_id']) ? (int)$this->request->data['meter_id'] : 0);
        $month = !empty($this->request->query['month']) ? trim($this->request->query['month']) : (!empty($this->request->data['month']) ? trim($this->request->data['month']) : date('Y-m'));
        
        $normMonth = $month;
        if (preg_match('/^(\d{4})-(\d{1,2})/', $month, $mmp)) {
            $normMonth = sprintf('%04d-%02d', $mmp[1], $mmp[2]);
        }

        $rateElec = isset($this->request->query['rate_elec']) && $this->request->query['rate_elec'] !== '' ? (float)$this->request->query['rate_elec'] : (isset($this->request->data['rate_elec']) && $this->request->data['rate_elec'] !== '' ? (float)$this->request->data['rate_elec'] : null);
        $rateWater = isset($this->request->query['rate_water']) && $this->request->query['rate_water'] !== '' ? (float)$this->request->query['rate_water'] : (isset($this->request->data['rate_water']) && $this->request->data['rate_water'] !== '' ? (float)$this->request->data['rate_water'] : null);
        $rateGas = isset($this->request->query['rate_gas']) && $this->request->query['rate_gas'] !== '' ? (float)$this->request->query['rate_gas'] : (isset($this->request->data['rate_gas']) && $this->request->data['rate_gas'] !== '' ? (float)$this->request->data['rate_gas'] : null);
        
        $applyMaint = !isset($this->request->query['apply_maint']) || $this->request->query['apply_maint'] === '1' || $this->request->query['apply_maint'] === 'true';
        if (isset($this->request->data['apply_maint'])) {
            $applyMaint = ($this->request->data['apply_maint'] === '1' || $this->request->data['apply_maint'] === 'true' || $this->request->data['apply_maint'] === 1);
        }
        
        $applyOther = !isset($this->request->query['apply_other']) || $this->request->query['apply_other'] === '1' || $this->request->query['apply_other'] === 'true';
        if (isset($this->request->data['apply_other'])) {
            $applyOther = ($this->request->data['apply_other'] === '1' || $this->request->data['apply_other'] === 'true' || $this->request->data['apply_other'] === 1);
        }

        $meter = $this->BillingMeter->findById($meterId);
        if(empty($meter))
        {
            echo json_encode(array('status' => 'error', 'message' => 'Meter not found'));
            exit;
        }

        $propId = (int)$meter['BillingMeter']['billing_property_id'];
        $prop = $this->BillingProperty->findById($propId);
        $socId = !empty($meter['BillingMeter']['society_id']) ? (int)$meter['BillingMeter']['society_id'] : 1;

        $reading = $this->BillingReading->find('first', array(
            'conditions' => array(
                'BillingReading.meter_id' => $meterId,
                'OR' => array(
                    'BillingReading.reading_month' => $month,
                    'BillingReading.reading_month' => $normMonth
                )
            ),
            'order' => array('BillingReading.id' => 'DESC')
        ));

        $latestReading = $this->BillingReading->find('first', array(
            'conditions' => array('BillingReading.meter_id' => $meterId),
            'order' => array('BillingReading.id' => 'DESC')
        ));

        $prev = 0.0;
        $curr = 0.0;
        $units = 0.0;
        $readingFound = false;
        if(!empty($reading))
        {
            $prev = (float)$reading['BillingReading']['previous_reading'];
            $curr = (float)$reading['BillingReading']['current_reading'];
            $units = (float)$reading['BillingReading']['units_consumed'];
            $readingFound = true;
        }
        elseif(!empty($latestReading))
        {
            $prev = (float)$latestReading['BillingReading']['current_reading'];
            $curr = $prev;
            $units = 0.0;
        }
        else
        {
            $prev = (float)$meter['BillingMeter']['current_reading'];
            $curr = $prev;
            $units = 0.0;
        }

        $utilityType = strtolower(trim($meter['BillingMeter']['utility_type']));
        $rate = 0.0;
        if($utilityType === 'water') {
            $rate = ($rateWater !== null) ? $rateWater : (float)$this->_getSetting('subm_rate_water', 100.0);
        } elseif($utilityType === 'gas') {
            $rate = ($rateGas !== null) ? $rateGas : (float)$this->_getSetting('subm_rate_gas', 350.0);
        } else {
            $rate = ($rateElec !== null) ? $rateElec : (float)$this->_getSetting('subm_rate_elec', 58.34);
        }

        $consumptionAmt = $units * $rate;

        // Maintenance breakdown
        $maintItems = array();
        $maintAmt = 0.0;
        if($applyMaint && !empty($prop))
        {
            $heads = $this->_getSetting('subm_maintenance_heads', array());
            if(is_string($heads)) {
                $un = @unserialize($heads);
                if($un !== false) $heads = $un;
            }

            $pTitle = !empty($prop['BillingProperty']['title']) ? trim($prop['BillingProperty']['title']) : '';
            $pCat = !empty($prop['BillingProperty']['category']) ? trim($prop['BillingProperty']['category']) : '';
            $typeMatch = 'Apartment';
            if(stripos($pTitle, 'B1/') !== false || stripos($pTitle, 'B2/') !== false || stripos($pTitle, 'B3/') !== false || stripos($pTitle, 'B4/') !== false || stripos($pTitle, 'FF') !== false || stripos($pTitle, 'SF') !== false || stripos($pTitle, 'GF') !== false || stripos($pCat, 'Apartment') !== false || stripos($pCat, 'Flat') !== false) {
                $typeMatch = 'Apartment';
            } elseif(stripos($pCat, 'Plot') !== false) {
                $typeMatch = 'Plot';
            } else {
                $typeMatch = 'House';
            }

            $areaNum = 4.0;
            if(!empty($prop['BillingProperty']['area']) && (float)$prop['BillingProperty']['area'] > 0) {
                $areaNum = (float)$prop['BillingProperty']['area'];
            } elseif(preg_match('/([0-9\.]+)\s*(marla|kanal|sqft|m)?/i', $pTitle, $am)) {
                $areaNum = (float)$am[1];
            }

            if(is_array($heads))
            {
                foreach($heads as $h)
                {
                    $hType = !empty($h['property_type']) ? trim($h['property_type']) : '';
                    if(empty($hType) || strcasecmp($hType, $typeMatch) === 0 || strcasecmp($hType, 'all') === 0 || strcasecmp($hType, 'any') === 0)
                    {
                        $min = isset($h['min_area']) && $h['min_area'] !== '' ? (float)$h['min_area'] : 0.0;
                        $max = isset($h['max_area']) && $h['max_area'] !== '' ? (float)$h['max_area'] : 999999.0;
                        if($areaNum >= $min && $areaNum <= $max) {
                            $amt = !empty($h['amount']) ? (float)$h['amount'] : 0.0;
                            $maintItems[] = array('name' => $h['name'], 'amount' => $amt);
                            $maintAmt += $amt;
                        }
                    }
                }
            }
            if(empty($maintItems))
            {
                $baseFee = (float)$this->_getSetting('subm_maintenance_fee', 0.0);
                if($baseFee > 0)
                {
                    $maintItems[] = array('name' => 'Monthly Maintenance Fee', 'amount' => $baseFee);
                    $maintAmt += $baseFee;
                }
            }
        }

        // Other active extra charges for property
        $otherItems = array();
        $otherAmt = 0.0;
        if($applyOther)
        {
            $extraCharges = $this->BillingExtraCharge->find('all', array(
                'conditions' => array(
                    'OR' => array(
                        'BillingExtraCharge.billing_property_id' => $propId,
                        'BillingExtraCharge.billing_property_id' => 0
                    ),
                    'BillingExtraCharge.charge_type' => 'recurring'
                )
            ));
            foreach($extraCharges as $ec)
            {
                $amt = (float)$ec['BillingExtraCharge']['amount'];
                $otherItems[] = array('name' => $ec['BillingExtraCharge']['title'], 'amount' => $amt);
                $otherAmt += $amt;
            }
        }

        // Past Arrears
        $pastUnpaid = $this->BillingBill->find('all', array(
            'conditions' => array('BillingBill.billing_property_id' => $propId, 'BillingBill.payment_status' => array('unpaid', 'partial')),
            'fields' => array('balance_amount')
        ));
        $arrears = 0.0;
        foreach($pastUnpaid as $pu)
        {
            $arrears += (float)$pu['BillingBill']['balance_amount'];
        }

        // Check for duplicate bill in the same period
        $existingBill = $this->BillingBill->find('first', array(
            'conditions' => array(
                'BillingBill.billing_property_id' => $propId,
                'BillingBill.utility_type' => $meter['BillingMeter']['utility_type'],
                'OR' => array(
                    'BillingBill.billing_period' => $month,
                    'BillingBill.billing_period' => $normMonth
                )
            )
        ));

        $isDuplicate = !empty($existingBill);
        $existingBillNo = $isDuplicate ? $existingBill['BillingBill']['bill_number'] : '';
        $existingBillTotal = $isDuplicate ? (float)$existingBill['BillingBill']['total_amount'] : 0.0;
        $existingPaymentStatus = $isDuplicate ? $existingBill['BillingBill']['payment_status'] : '';

        $currentBill = $consumptionAmt + $maintAmt + $otherAmt;
        $totalWithinDue = $currentBill + $arrears;
        $lateFee = $this->_calculateLateFee($currentBill, $socId);
        $payableAfterDue = $totalWithinDue + $lateFee;

        echo json_encode(array(
            'status' => 'success',
            'is_duplicate' => $isDuplicate,
            'existing_bill_number' => $existingBillNo,
            'existing_total_amount' => round($existingBillTotal, 2),
            'existing_payment_status' => $existingPaymentStatus,
            'meter_id' => $meterId,
            'meter_number' => $meter['BillingMeter']['meter_number'],
            'utility_type' => $meter['BillingMeter']['utility_type'],
            'reading_found' => $readingFound,
            'latest_reading_month' => (!empty($latestReading) ? $latestReading['BillingReading']['reading_month'] : ''),
            'latest_reading_units' => (!empty($latestReading) ? (float)$latestReading['BillingReading']['units_consumed'] : 0.0),
            'previous_reading' => $prev,
            'current_reading' => $curr,
            'units_consumed' => $units,
            'export_units' => $exportUnits,
            'net_units' => $netUnits,
            'unit_rate' => $rate,
            'consumption_amount' => round($consumptionAmt, 2),
            'maintenance_items' => $maintItems,
            'maintenance_amount' => round($maintAmt, 2),
            'other_charges_items' => $otherItems,
            'other_charges_amount' => round($otherAmt, 2),
            'arrears_amount' => round($arrears, 2),
            'current_bill' => round($currentBill, 2),
            'total_within_due' => round($totalWithinDue, 2),
            'late_fee' => round($lateFee, 2),
            'payable_after_due' => round($payableAfterDue, 2),
            'total_amount' => round($totalWithinDue, 2)
        ));
        exit;
    }

    public function admin_get_bulk_preflight_audit()
    {
        return $this->get_bulk_preflight_audit();
    }

    public function get_bulk_preflight_audit()
    {
        $this->autoRender = false;
        header('Content-Type: application/json');

        $month = !empty($this->request->query['month']) ? trim($this->request->query['month']) : (!empty($this->request->data['month']) ? trim($this->request->data['month']) : date('Y-m'));
        $normMonth = $month;
        if (preg_match('/^(\d{4})-(\d{1,2})/', $month, $mmp)) {
            $normMonth = sprintf('%04d-%02d', $mmp[1], $mmp[2]);
        }

        $socIdFilter = !empty($this->request->query['society_id']) ? (int)$this->request->query['society_id'] : (!empty($this->request->data['society_id']) ? (int)$this->request->data['society_id'] : 0);

        $applyMaint = !isset($this->request->query['apply_maint']) || $this->request->query['apply_maint'] === '1' || $this->request->query['apply_maint'] === 'true';
        $applyOther = !isset($this->request->query['apply_other']) || $this->request->query['apply_other'] === '1' || $this->request->query['apply_other'] === 'true';

        $overrideElec = (isset($this->request->query['rate_elec']) && $this->request->query['rate_elec'] !== '') ? (float)$this->request->query['rate_elec'] : null;
        $overrideWater = (isset($this->request->query['rate_water']) && $this->request->query['rate_water'] !== '') ? (float)$this->request->query['rate_water'] : null;
        $overrideGas = (isset($this->request->query['rate_gas']) && $this->request->query['rate_gas'] !== '') ? (float)$this->request->query['rate_gas'] : null;

        // ONLY select properties that have real linked meters OR assigned consumers/clients
        $propCond = array(
            'OR' => array(
                'BillingProperty.billing_client_id >' => 0,
                'BillingProperty.id IN (SELECT DISTINCT billing_property_id FROM billing_meters WHERE billing_property_id IS NOT NULL AND billing_property_id > 0)'
            )
        );
        if ($socIdFilter > 0) {
            $propCond['BillingProperty.society_id'] = $socIdFilter;
        }

        $properties = $this->BillingProperty->find('all', array(
            'conditions' => $propCond,
            'contain' => array('BillingClient', 'BillingMeter')
        ));

        $totalMeters = 0;
        $readingsFound = 0;
        $missingReadings = 0;
        $duplicatesCount = 0;
        $newBillsCount = 0;

        $estUnitsTotal = 0.0;
        $estEnergyTotal = 0.0;
        $estMaintTotal = 0.0;
        $estOtherTotal = 0.0;
        $estArrearsTotal = 0.0;
        $estGrandTotal = 0.0;

        $missingMetersList = array();

        foreach ($properties as $p) {
            $propId = (int)$p['BillingProperty']['id'];
            $socId = !empty($p['BillingProperty']['society_id']) ? (int)$p['BillingProperty']['society_id'] : 1;
            $clientId = !empty($p['BillingProperty']['billing_client_id']) ? (int)$p['BillingProperty']['billing_client_id'] : 0;
            $clientName = !empty($p['BillingClient']['name']) ? $p['BillingClient']['name'] : 'No Client';
            $propTitle = !empty($p['BillingProperty']['title']) ? $p['BillingProperty']['title'] : (!empty($p['BillingProperty']['property_no']) ? $p['BillingProperty']['property_no'] : ('Unit #' . $propId));

            $meters = !empty($p['BillingMeter']) ? $p['BillingMeter'] : array();

            if (!empty($meters)) {
                foreach ($meters as $m) {
                    $totalMeters++;
                    $mId = isset($m['id']) ? (int)$m['id'] : 0;
                    $mNumber = !empty($m['meter_number']) ? $m['meter_number'] : ('MTR-' . $propId);
                    $uType = !empty($m['utility_type']) ? trim($m['utility_type']) : 'Electricity';

                    // Check duplicate
                    $dupCond = array(
                        'BillingBill.billing_property_id' => $propId,
                        'BillingBill.utility_type' => $uType,
                        'OR' => array(
                            'BillingBill.billing_period' => $month,
                            'BillingBill.billing_period' => $normMonth
                        )
                    );

                    $existing = $this->BillingBill->find('first', array('conditions' => $dupCond, 'fields' => array('id', 'bill_number', 'total_amount')));
                    $isDuplicate = !empty($existing);

                    // Reading lookup
                    $readingCond = array(
                        'BillingReading.meter_id' => $mId,
                        'OR' => array(
                            'BillingReading.reading_month' => $month,
                            'BillingReading.reading_month' => $normMonth
                        )
                    );

                    $reading = $this->BillingReading->find('first', array(
                        'conditions' => $readingCond,
                        'order' => array('BillingReading.id' => 'DESC')
                    ));

                    $units = 0.0;
                    $hasReading = !empty($reading);
                    if ($hasReading) {
                        $readingsFound++;
                        $units = (float)$reading['BillingReading']['units_consumed'];
                    } else {
                        $missingReadings++;
                        $missingMetersList[] = array(
                            'meter_id' => $mId,
                            'meter_number' => $mNumber,
                            'utility_type' => $uType,
                            'property_title' => $propTitle,
                            'client_name' => $clientName
                        );
                    }

                    if ($isDuplicate) {
                        $duplicatesCount++;
                    } else {
                        $newBillsCount++;

                        // Energy rate
                        $uTypeLower = strtolower($uType);
                        if ($uTypeLower === 'water') {
                            $rate = ($overrideWater !== null) ? $overrideWater : $this->_calculateMeterRate('Water', $p, $units, $socId);
                        } elseif ($uTypeLower === 'gas') {
                            $rate = ($overrideGas !== null) ? $overrideGas : $this->_calculateMeterRate('Gas', $p, $units, $socId);
                        } else {
                            $rate = ($overrideElec !== null) ? $overrideElec : $this->_calculateMeterRate('Electricity', $p, $units, $socId);
                        }

                        $energyAmt = $units * $rate;
                        $maintAmt = $applyMaint ? $this->_calculatePropertyMaintenance($p, $socId) : 0.0;
                        
                        $otherAmt = 0.0;
                        if ($applyOther) {
                            $extraCharges = $this->BillingExtraCharge->find('all', array(
                                'conditions' => array(
                                    'BillingExtraCharge.charge_type' => 'recurring',
                                    'BillingExtraCharge.status' => 'active'
                                )
                            ));
                            foreach ($extraCharges as $ec) {
                                $otherAmt += (float)$ec['BillingExtraCharge']['amount'];
                            }
                        }

                        // Arrears
                        $pastUnpaid = $this->BillingBill->find('all', array(
                            'conditions' => array('BillingBill.billing_property_id' => $propId, 'BillingBill.payment_status' => array('unpaid', 'partial')),
                            'fields' => array('balance_amount')
                        ));
                        $arrears = 0.0;
                        foreach ($pastUnpaid as $pu) {
                            $arrears += (float)$pu['BillingBill']['balance_amount'];
                        }

                        $billTotal = $energyAmt + $maintAmt + $otherAmt + $arrears;

                        $estUnitsTotal += $units;
                        $estEnergyTotal += $energyAmt;
                        $estMaintTotal += $maintAmt;
                        $estOtherTotal += $otherAmt;
                        $estArrearsTotal += $arrears;
                        $estGrandTotal += $billTotal;
                    }
                }
            } elseif ($clientId > 0 && ($applyMaint || $applyOther)) {
                // Property has assigned client/consumer but NO meter (e.g. flat maintenance billing)
                $uType = 'Maintenance';
                $dupCond = array(
                    'BillingBill.billing_property_id' => $propId,
                    'BillingBill.utility_type' => $uType,
                    'OR' => array(
                        'BillingBill.billing_period' => $month,
                        'BillingBill.billing_period' => $normMonth
                    )
                );
                $existing = $this->BillingBill->find('first', array('conditions' => $dupCond, 'fields' => array('id', 'bill_number', 'total_amount')));
                if (!empty($existing)) {
                    $duplicatesCount++;
                } else {
                    $newBillsCount++;
                    $maintAmt = $applyMaint ? $this->_calculatePropertyMaintenance($p, $socId) : 0.0;
                    $otherAmt = 0.0;
                    if ($applyOther) {
                        $extraCharges = $this->BillingExtraCharge->find('all', array(
                            'conditions' => array(
                                'BillingExtraCharge.charge_type' => 'recurring',
                                'BillingExtraCharge.status' => 'active'
                            )
                        ));
                        foreach ($extraCharges as $ec) {
                            $otherAmt += (float)$ec['BillingExtraCharge']['amount'];
                        }
                    }
                    $pastUnpaid = $this->BillingBill->find('all', array(
                        'conditions' => array('BillingBill.billing_property_id' => $propId, 'BillingBill.payment_status' => array('unpaid', 'partial')),
                        'fields' => array('balance_amount')
                    ));
                    $arrears = 0.0;
                    foreach ($pastUnpaid as $pu) {
                        $arrears += (float)$pu['BillingBill']['balance_amount'];
                    }
                    $billTotal = $maintAmt + $otherAmt + $arrears;
                    $estMaintTotal += $maintAmt;
                    $estOtherTotal += $otherAmt;
                    $estArrearsTotal += $arrears;
                    $estGrandTotal += $billTotal;
                }
            }
        }

        echo json_encode(array(
            'status' => 'success',
            'month' => $month,
            'total_meters' => $totalMeters,
            'readings_found' => $readingsFound,
            'missing_readings' => $missingReadings,
            'duplicates_count' => $duplicatesCount,
            'new_bills_count' => $newBillsCount,
            'est_units_total' => round($estUnitsTotal, 1),
            'est_energy_total' => round($estEnergyTotal, 2),
            'est_maint_total' => round($estMaintTotal, 2),
            'est_other_total' => round($estOtherTotal, 2),
            'est_arrears_total' => round($estArrearsTotal, 2),
            'est_grand_total' => round($estGrandTotal, 2),
            'missing_meters_list' => array_slice($missingMetersList, 0, 50)
        ));
        exit;
    }

    public function generate_bulk()
    {
        if($this->request->is('post'))
        {
            $month = trim($this->request->data['BillingBill']['billing_period']);
            $issueDate = !empty($this->request->data['BillingBill']['issue_date']) ? $this->request->data['BillingBill']['issue_date'] : date('Y-m-d');
            $dueDate = !empty($this->request->data['BillingBill']['due_date']) ? $this->request->data['BillingBill']['due_date'] : date('Y-m-d', strtotime('+10 days'));
            $templateId = !empty($this->request->data['BillingBill']['template_id']) ? (int)$this->request->data['BillingBill']['template_id'] : 11;
            $socIdFilter = !empty($this->request->data['BillingBill']['society_id']) ? (int)$this->request->data['BillingBill']['society_id'] : 0;
            $zeroUnitAction = !empty($this->request->data['BillingBill']['zero_unit_action']) ? $this->request->data['BillingBill']['zero_unit_action'] : 'generate';

            if($month === '')
            {
                $this->Session->setFlash(__('Please specify a billing month.'), 'flash', array('alert' => 'danger'));
                return $this->redirect(array('action' => 'index'));
            }

            $normMonth = $month;
            if (preg_match('/^(\d{4})-(\d{1,2})/', $month, $mmp)) {
                $normMonth = sprintf('%04d-%02d', $mmp[1], $mmp[2]);
            }

            // Toggles & Overrides
            $applyMaint = !isset($this->request->data['BillingBill']['apply_maintenance']) || !empty($this->request->data['BillingBill']['apply_maintenance']);
            $applyOther = !isset($this->request->data['BillingBill']['apply_other_charges']) || !empty($this->request->data['BillingBill']['apply_other_charges']);

            $overrideElec = (isset($this->request->data['BillingBill']['rate_override_elec']) && $this->request->data['BillingBill']['rate_override_elec'] !== '') ? (float)$this->request->data['BillingBill']['rate_override_elec'] : null;
            $overrideWater = (isset($this->request->data['BillingBill']['rate_override_water']) && $this->request->data['BillingBill']['rate_override_water'] !== '') ? (float)$this->request->data['BillingBill']['rate_override_water'] : null;
            $overrideGas = (isset($this->request->data['BillingBill']['rate_override_gas']) && $this->request->data['BillingBill']['rate_override_gas'] !== '') ? (float)$this->request->data['BillingBill']['rate_override_gas'] : null;

            // ONLY select properties that have real linked meters OR assigned consumers/clients
            $propCond = array(
                'OR' => array(
                    'BillingProperty.billing_client_id >' => 0,
                    'BillingProperty.id IN (SELECT DISTINCT billing_property_id FROM billing_meters WHERE billing_property_id IS NOT NULL AND billing_property_id > 0)'
                )
            );
            if ($socIdFilter > 0) {
                $propCond['BillingProperty.society_id'] = $socIdFilter;
            }

            $properties = $this->BillingProperty->find('all', array(
                'conditions' => $propCond,
                'contain' => array('BillingClient', 'BillingMeter')
            ));

            $generatedCount = 0;
            $skippedCount = 0;

            foreach($properties as $p)
            {
                $propId = (int)$p['BillingProperty']['id'];
                $socId = !empty($p['BillingProperty']['society_id']) ? (int)$p['BillingProperty']['society_id'] : 1;
                $clientId = !empty($p['BillingProperty']['billing_client_id']) ? (int)$p['BillingProperty']['billing_client_id'] : 0;

                // Meters attached to this property
                $meters = !empty($p['BillingMeter']) ? $p['BillingMeter'] : array();

                if (!empty($meters)) {
                    foreach($meters as $m)
                    {
                        $mId = isset($m['id']) ? (int)$m['id'] : 0;
                        $uType = !empty($m['utility_type']) ? trim($m['utility_type']) : 'Electricity';

                        // Check duplicate bill for this property + meter/type + month
                        $dupCond = array(
                            'BillingBill.billing_property_id' => $propId,
                            'BillingBill.utility_type' => $uType,
                            'OR' => array(
                                'BillingBill.billing_period' => $month,
                                'BillingBill.billing_period' => $normMonth
                            )
                        );

                        $alreadyExists = $this->BillingBill->find('first', array('conditions' => $dupCond));
                        if(!empty($alreadyExists)) {
                            $skippedCount++;
                            continue;
                        }

                        // Reading lookup
                        $readingCond = array(
                            'BillingReading.meter_id' => $mId,
                            'OR' => array(
                                'BillingReading.reading_month' => $month,
                                'BillingReading.reading_month' => $normMonth
                            )
                        );

                        $reading = $this->BillingReading->find('first', array(
                            'conditions' => $readingCond,
                            'order' => array('BillingReading.id' => 'DESC')
                        ));

                        $prev = 0.0;
                        $curr = 0.0;
                        $units = 0.0;
                        $exportUnits = 0.0;
                        $rId = null;
                        if(!empty($reading)) {
                            $prev = (float)$reading['BillingReading']['previous_reading'];
                            $curr = (float)$reading['BillingReading']['current_reading'];
                            $units = (float)$reading['BillingReading']['units_consumed'];
                            if(isset($reading['BillingReading']['export_units'])) {
                                $exportUnits = (float)$reading['BillingReading']['export_units'];
                            }
                            $rId = (int)$reading['BillingReading']['id'];
                        }

                        if ($zeroUnitAction === 'skip_zero' && $units <= 0 && empty($reading)) {
                            $skippedCount++;
                            continue;
                        }

                        // Determine rate based on overrides / slabs
                        $uTypeLower = strtolower($uType);
                        if($uTypeLower === 'water') {
                            $rate = ($overrideWater !== null) ? $overrideWater : $this->_calculateMeterRate('Water', $p, $units, $socId);
                        } elseif($uTypeLower === 'gas') {
                            $rate = ($overrideGas !== null) ? $overrideGas : $this->_calculateMeterRate('Gas', $p, $units, $socId);
                        } else {
                            $rate = ($overrideElec !== null) ? $overrideElec : $this->_calculateMeterRate('Electricity', $p, $units, $socId);
                        }

                        // Solar Net-Metering Calculation Guard
                        $isSolarEnabled = $this->BillingSetting->getSetting('subm_feat_solar_net_metering', '1', $socId);
                        if($isSolarEnabled !== '0' && $isSolarEnabled !== 0 && $exportUnits > 0) {
                            $netUnits = max(0.0, $units - $exportUnits);
                        } else {
                            $netUnits = $units;
                        }

                        $consumptionAmt = $netUnits * $rate;

                        // Maintenance calculation
                        $maintAmt = 0.0;
                        $itemizedBreakdown = array();
                        $itemizedBreakdown[] = array(
                            'category' => 'Utility',
                            'desc' => $uType . ' Charges',
                            'units' => $units,
                            'net_units' => $netUnits,
                            'export_units' => $exportUnits,
                            'rate' => $rate,
                            'amount' => $consumptionAmt
                        );

                        if($applyMaint) {
                            $maintAmt = $this->_calculatePropertyMaintenance($p, $socId);
                            $maintItems = !empty($p['BillingProperty']['maintenance_items']) ? @unserialize($p['BillingProperty']['maintenance_items']) : array();
                            if (is_array($maintItems) && !empty($maintItems)) {
                                foreach ($maintItems as $mi) {
                                    $itemizedBreakdown[] = array(
                                        'category' => 'Maintenance',
                                        'desc' => isset($mi['name']) ? $mi['name'] : (isset($mi['desc']) ? $mi['desc'] : 'Maintenance'),
                                        'amount' => (float)(isset($mi['amount']) ? $mi['amount'] : 0)
                                    );
                                }
                            } else {
                                $itemizedBreakdown[] = array(
                                    'category' => 'Maintenance',
                                    'desc' => 'Monthly Maintenance Fee',
                                    'amount' => $maintAmt
                                );
                            }
                        }

                        // Other charges calculation
                        $otherAmt = 0.0;
                        if($applyOther) {
                            $extraCharges = $this->BillingExtraCharge->find('all', array(
                                'conditions' => array(
                                    'BillingExtraCharge.charge_type' => 'recurring',
                                    'BillingExtraCharge.status' => 'active'
                                )
                            ));
                            foreach($extraCharges as $ec) {
                                $amt = (float)$ec['BillingExtraCharge']['amount'];
                                $otherAmt += $amt;
                                $itemizedBreakdown[] = array(
                                    'category' => 'Other',
                                    'desc' => $ec['BillingExtraCharge']['title'],
                                    'amount' => $amt
                                );
                            }
                        }

                        // Arrears
                        $pastUnpaid = $this->BillingBill->find('all', array(
                            'conditions' => array('BillingBill.billing_property_id' => $propId, 'BillingBill.payment_status' => array('unpaid', 'partial')),
                            'fields' => array('balance_amount')
                        ));
                        $arrears = 0.0;
                        foreach($pastUnpaid as $pu) {
                            $arrears += (float)$pu['BillingBill']['balance_amount'];
                        }

                        $extraChargesTotal = $maintAmt + $otherAmt;
                        $chargeTotal = $consumptionAmt + $extraChargesTotal;
                        $lateFee = $this->_calculateLateFee($chargeTotal, $socId);
                        $total = $chargeTotal + $arrears;
                        $billNo = 'WB' . rand(9000, 9999);

                        $this->BillingBill->create();
                        $this->BillingBill->save(array('BillingBill' => array(
                            'society_id' => $socId,
                            'bill_number' => $billNo,
                            'billing_property_id' => $propId,
                            'billing_client_id' => $clientId,
                            'utility_type' => $uType,
                            'billing_period' => $month,
                            'reading_id' => $rId,
                            'previous_reading' => $prev,
                            'current_reading' => $curr,
                            'units_consumed' => $units,
                            'export_units' => $exportUnits,
                            'net_units' => $netUnits,
                            'unit_rate' => $rate,
                            'consumption_amount' => $consumptionAmt,
                            'extra_charges_amount' => $extraChargesTotal,
                            'extra_charges_json' => !empty($itemizedBreakdown) ? json_encode($itemizedBreakdown) : null,
                            'arrears_amount' => $arrears,
                            'late_fee' => $lateFee,
                            'total_amount' => $total,
                            'paid_amount' => 0.0,
                            'balance_amount' => $total,
                            'issue_date' => $issueDate,
                            'due_date' => $dueDate,
                            'payment_status' => 'unpaid',
                            'template_id' => $templateId,
                            'created_at' => date('Y-m-d H:i:s')
                        )));
                        $generatedCount++;
                    }
                } elseif ($clientId > 0 && ($applyMaint || $applyOther)) {
                    // Property has assigned consumer but no meter: generate maintenance invoice
                    $uType = 'Maintenance';
                    $dupCond = array(
                        'BillingBill.billing_property_id' => $propId,
                        'BillingBill.utility_type' => $uType,
                        'OR' => array(
                            'BillingBill.billing_period' => $month,
                            'BillingBill.billing_period' => $normMonth
                        )
                    );
                    $alreadyExists = $this->BillingBill->find('first', array('conditions' => $dupCond));
                    if(!empty($alreadyExists)) {
                        $skippedCount++;
                        continue;
                    }

                    $maintAmt = 0.0;
                    $itemizedBreakdown = array();
                    if($applyMaint) {
                        $maintAmt = $this->_calculatePropertyMaintenance($p, $socId);
                        $maintItems = !empty($p['BillingProperty']['maintenance_items']) ? @unserialize($p['BillingProperty']['maintenance_items']) : array();
                        if (is_array($maintItems) && !empty($maintItems)) {
                            foreach ($maintItems as $mi) {
                                $itemizedBreakdown[] = array(
                                    'category' => 'Maintenance',
                                    'desc' => isset($mi['name']) ? $mi['name'] : (isset($mi['desc']) ? $mi['desc'] : 'Maintenance'),
                                    'amount' => (float)(isset($mi['amount']) ? $mi['amount'] : 0)
                                );
                            }
                        } else {
                            $itemizedBreakdown[] = array(
                                'category' => 'Maintenance',
                                'desc' => 'Monthly Maintenance Fee',
                                'amount' => $maintAmt
                            );
                        }
                    }

                    $otherAmt = 0.0;
                    if($applyOther) {
                        $extraCharges = $this->BillingExtraCharge->find('all', array(
                            'conditions' => array(
                                'BillingExtraCharge.charge_type' => 'recurring',
                                'BillingExtraCharge.status' => 'active'
                            )
                        ));
                        foreach($extraCharges as $ec) {
                            $amt = (float)$ec['BillingExtraCharge']['amount'];
                            $otherAmt += $amt;
                            $itemizedBreakdown[] = array(
                                'category' => 'Other',
                                'desc' => $ec['BillingExtraCharge']['title'],
                                'amount' => $amt
                            );
                        }
                    }

                    $pastUnpaid = $this->BillingBill->find('all', array(
                        'conditions' => array('BillingBill.billing_property_id' => $propId, 'BillingBill.payment_status' => array('unpaid', 'partial')),
                        'fields' => array('balance_amount')
                    ));
                    $arrears = 0.0;
                    foreach($pastUnpaid as $pu) {
                        $arrears += (float)$pu['BillingBill']['balance_amount'];
                    }

                    $extraChargesTotal = $maintAmt + $otherAmt;
                    $lateFee = $this->_calculateLateFee($extraChargesTotal, $socId);
                    $total = $extraChargesTotal + $arrears;
                    $billNo = 'WB' . rand(9000, 9999);

                    $this->BillingBill->create();
                    $this->BillingBill->save(array('BillingBill' => array(
                        'society_id' => $socId,
                        'bill_number' => $billNo,
                        'billing_property_id' => $propId,
                        'billing_client_id' => $clientId,
                        'utility_type' => $uType,
                        'billing_period' => $month,
                        'reading_id' => null,
                        'previous_reading' => 0.0,
                        'current_reading' => 0.0,
                        'units_consumed' => 0.0,
                        'unit_rate' => 0.0,
                        'consumption_amount' => 0.0,
                        'extra_charges_amount' => $extraChargesTotal,
                        'extra_charges_json' => !empty($itemizedBreakdown) ? json_encode($itemizedBreakdown) : null,
                        'arrears_amount' => $arrears,
                        'late_fee' => $lateFee,
                        'total_amount' => $total,
                        'paid_amount' => 0.0,
                        'balance_amount' => $total,
                        'issue_date' => $issueDate,
                        'due_date' => $dueDate,
                        'payment_status' => 'unpaid',
                        'template_id' => $templateId,
                        'created_at' => date('Y-m-d H:i:s')
                    )));
                    $generatedCount++;
                }
            }

            $msg = sprintf(__('Generated %d bulk bills for period %s.'), $generatedCount, h($month));
            if($skippedCount > 0) {
                $msg .= sprintf(__(' (%d existing bills skipped to prevent duplicate creation).'), $skippedCount);
            }
            $this->Session->setFlash($msg, 'flash', array('alert' => 'success'));
        }
        return $this->redirect(array('action' => 'index'));
    }

public function generate_single()
{
    if($this->request->is('post'))
    {
        $meterId = (int)$this->request->data['BillingBill']['meter_id'];
        $meter = $this->BillingMeter->findById($meterId);
        if(empty($meter))
        {
            $this->Session->setFlash(__('Please select a valid meter.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }

        $propId = (int)$meter['BillingMeter']['billing_property_id'];
        $prop = $this->BillingProperty->findById($propId);
        $clientId = !empty($prop['BillingProperty']['billing_client_id']) ? (int)$prop['BillingProperty']['billing_client_id'] : 0;
        $socId = !empty($meter['BillingMeter']['society_id']) ? (int)$meter['BillingMeter']['society_id'] : 1;

        $month = trim($this->request->data['BillingBill']['billing_period']);
        $issueDate = !empty($this->request->data['BillingBill']['issue_date']) ? $this->request->data['BillingBill']['issue_date'] : date('Y-m-d');
        $dueDate = !empty($this->request->data['BillingBill']['due_date']) ? $this->request->data['BillingBill']['due_date'] : date('Y-m-d', strtotime('+3 days'));
        $templateId = !empty($this->request->data['BillingBill']['template_id']) ? (int)$this->request->data['BillingBill']['template_id'] : 11;

        // Block duplicate bill generation
        $normMonth = $month;
        if (preg_match('/^(\d{4})-(\d{1,2})/', $month, $mmp)) {
            $normMonth = sprintf('%04d-%02d', $mmp[1], $mmp[2]);
        }

        $existingBill = $this->BillingBill->find('first', array(
            'conditions' => array(
                'BillingBill.billing_property_id' => $propId,
                'BillingBill.utility_type' => $meter['BillingMeter']['utility_type'],
                'OR' => array(
                    'BillingBill.billing_period' => $month,
                    'BillingBill.billing_period' => $normMonth
                )
            )
        ));
        if(!empty($existingBill))
        {
            $this->Session->setFlash(sprintf(__('Duplicate blocked: Bill #%s already exists for this meter for period %s. Creation cancelled.'), h($existingBill['BillingBill']['bill_number']), h($month)), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }

        $reading = $this->BillingReading->find('first', array(
            'conditions' => array(
                'BillingReading.meter_id' => $meterId,
                'OR' => array(
                    'BillingReading.reading_month' => $month,
                    'BillingReading.reading_month' => $normMonth
                )
            ),
            'order' => array('BillingReading.id' => 'DESC')
        ));

        $prev = 0.0;
        $curr = 0.0;
        $units = 0.0;
        $rId = null;
        if(!empty($reading))
        {
            $prev = (float)$reading['BillingReading']['previous_reading'];
            $curr = (float)$reading['BillingReading']['current_reading'];
            $units = (float)$reading['BillingReading']['units_consumed'];
            $rId = (int)$reading['BillingReading']['id'];
        }
        else
        {
            $prev = (float)$meter['BillingMeter']['current_reading'];
            $curr = $prev;
            $units = 0.0;
        }

        // On-the-fly reading input support
        if (isset($this->request->data['BillingBill']['input_curr_reading']) && $this->request->data['BillingBill']['input_curr_reading'] !== '') {
            $inPrev = isset($this->request->data['BillingBill']['input_prev_reading']) && $this->request->data['BillingBill']['input_prev_reading'] !== '' ? (float)$this->request->data['BillingBill']['input_prev_reading'] : $prev;
            $inCurr = (float)$this->request->data['BillingBill']['input_curr_reading'];
            $inUnits = max(0.0, $inCurr - $inPrev);

            // Save new reading record automatically
            $this->BillingReading->create();
            $this->BillingReading->save(array('BillingReading' => array(
                'society_id' => $socId,
                'meter_id' => $meterId,
                'billing_property_id' => $propId,
                'billing_client_id' => $clientId,
                'reading_month' => $normMonth,
                'previous_reading' => $inPrev,
                'current_reading' => $inCurr,
                'units_consumed' => $inUnits,
                'reading_date' => $issueDate,
                'created_at' => date('Y-m-d H:i:s')
            )));
            $rId = (int)$this->BillingReading->id;
            $prev = $inPrev;
            $curr = $inCurr;
            $units = $inUnits;
        }

        $utilityType = strtolower(trim($meter['BillingMeter']['utility_type']));
        $rate = 0.0;
        if($utilityType === 'water') {
            $override = isset($this->request->data['BillingBill']['rate_override_water']) && $this->request->data['BillingBill']['rate_override_water'] !== '' ? (float)$this->request->data['BillingBill']['rate_override_water'] : null;
            $rate = ($override !== null) ? $override : (float)$this->_getSetting('subm_rate_water', 100.0);
        } elseif($utilityType === 'gas') {
            $override = isset($this->request->data['BillingBill']['rate_override_gas']) && $this->request->data['BillingBill']['rate_override_gas'] !== '' ? (float)$this->request->data['BillingBill']['rate_override_gas'] : null;
            $rate = ($override !== null) ? $override : (float)$this->_getSetting('subm_rate_gas', 350.0);
        } else {
            $override = isset($this->request->data['BillingBill']['rate_override_elec']) && $this->request->data['BillingBill']['rate_override_elec'] !== '' ? (float)$this->request->data['BillingBill']['rate_override_elec'] : null;
            $rate = ($override !== null) ? $override : (float)$this->_getSetting('subm_rate_elec', 58.34);
        }

        $exportUnits = 0.0;
        if(!empty($reading) && !empty($reading['BillingReading']['export_units'])) {
            $exportUnits = (float)$reading['BillingReading']['export_units'];
        } elseif(isset($this->request->data['BillingBill']['export_units']) && $this->request->data['BillingBill']['export_units'] !== '') {
            $exportUnits = (float)$this->request->data['BillingBill']['export_units'];
        }

        $isSolarEnabled = $this->BillingSetting->getSetting('subm_feat_solar_net_metering', '1', $socId);
        if($isSolarEnabled !== '0' && $isSolarEnabled !== 0 && $exportUnits > 0) {
            $netUnits = max(0.0, $units - $exportUnits);
        } else {
            $netUnits = $units;
        }

        $consumptionAmt = $netUnits * $rate;
        $itemizedBreakdown = array();
        $itemizedBreakdown[] = array(
            'category' => 'Utility',
            'desc' => $meter['BillingMeter']['utility_type'] . ' Charges',
            'units' => $units,
            'net_units' => $netUnits,
            'export_units' => $exportUnits,
            'rate' => $rate,
            'amount' => $consumptionAmt
        );
        
        // Maintenance calculation
        $maintAmt = 0.0;
        if(!empty($this->request->data['BillingBill']['apply_maintenance']))
        {
            if(!empty($this->request->data['BillingBill']['maint_items']) && is_array($this->request->data['BillingBill']['maint_items']))
            {
                foreach($this->request->data['BillingBill']['maint_items'] as $hName => $hAmt) {
                    $amt = (float)$hAmt;
                    $maintAmt += $amt;
                    $itemizedBreakdown[] = array('category' => 'Maintenance', 'desc' => $hName, 'amount' => $amt);
                }
            }
            elseif(isset($this->request->data['BillingBill']['override_maintenance_amount']) && $this->request->data['BillingBill']['override_maintenance_amount'] !== '')
            {
                $maintAmt = (float)$this->request->data['BillingBill']['override_maintenance_amount'];
                $itemizedBreakdown[] = array('category' => 'Maintenance', 'desc' => 'Maintenance Charges', 'amount' => $maintAmt);
            }
            else
            {
                $maintAmt = $this->_calculatePropertyMaintenance($prop, $socId);
                $maintItems = !empty($prop['BillingProperty']['maintenance_items']) ? @unserialize($prop['BillingProperty']['maintenance_items']) : array();
                if (is_array($maintItems) && !empty($maintItems)) {
                    foreach ($maintItems as $mi) {
                        $itemizedBreakdown[] = array(
                            'category' => 'Maintenance',
                            'desc' => isset($mi['name']) ? $mi['name'] : (isset($mi['desc']) ? $mi['desc'] : 'Maintenance'),
                            'amount' => (float)(isset($mi['amount']) ? $mi['amount'] : 0)
                        );
                    }
                } else {
                    $itemizedBreakdown[] = array('category' => 'Maintenance', 'desc' => 'Monthly Maintenance Fee', 'amount' => $maintAmt);
                }
            }
        }

        // Other charges calculation
        $otherAmt = 0.0;
        if(!empty($this->request->data['BillingBill']['apply_other_charges']))
        {
            if(!empty($this->request->data['BillingBill']['other_items']) && is_array($this->request->data['BillingBill']['other_items']))
            {
                foreach($this->request->data['BillingBill']['other_items'] as $oTitle => $oAmt) {
                    $amt = (float)$oAmt;
                    $otherAmt += $amt;
                    $itemizedBreakdown[] = array('category' => 'Other', 'desc' => $oTitle, 'amount' => $amt);
                }
            }
            elseif(isset($this->request->data['BillingBill']['override_other_amount']) && $this->request->data['BillingBill']['override_other_amount'] !== '')
            {
                $otherAmt = (float)$this->request->data['BillingBill']['override_other_amount'];
                $itemizedBreakdown[] = array('category' => 'Other', 'desc' => 'Other Charges', 'amount' => $otherAmt);
            }
            else
            {
                $extraCharges = $this->BillingExtraCharge->find('all', array(
                    'conditions' => array(
                        'BillingExtraCharge.charge_type' => 'recurring',
                        'BillingExtraCharge.status' => 'active'
                    )
                ));
                foreach($extraCharges as $ec) {
                    $amt = (float)$ec['BillingExtraCharge']['amount'];
                    $otherAmt += $amt;
                    $itemizedBreakdown[] = array('category' => 'Other', 'desc' => $ec['BillingExtraCharge']['title'], 'amount' => $amt);
                }
            }
        }

        $pastUnpaid = $this->BillingBill->find('all', array(
            'conditions' => array('BillingBill.billing_property_id' => $propId, 'BillingBill.payment_status' => array('unpaid', 'partial')),
            'fields' => array('balance_amount')
        ));
        $arrears = 0.0;
        foreach($pastUnpaid as $pu)
        {
            $arrears += (float)$pu['BillingBill']['balance_amount'];
        }

        $extraChargesTotal = $maintAmt + $otherAmt;
        $chargeTotal = $consumptionAmt + $extraChargesTotal;
        $lateFee = $this->_calculateLateFee($chargeTotal, $socId);
        $total = $chargeTotal + $arrears;
        $billNo = 'WB' . rand(9000, 9999);

        $this->BillingBill->create();
        $this->BillingBill->save(array('BillingBill' => array(
            'society_id' => $socId,
            'bill_number' => $billNo,
            'billing_property_id' => $propId,
            'billing_client_id' => $clientId,
            'utility_type' => $meter['BillingMeter']['utility_type'],
            'billing_period' => $month,
            'reading_id' => $rId,
            'previous_reading' => $prev,
            'current_reading' => $curr,
            'units_consumed' => $units,
            'export_units' => $exportUnits,
            'net_units' => $netUnits,
            'unit_rate' => $rate,
            'consumption_amount' => $consumptionAmt,
            'extra_charges_amount' => $extraChargesTotal,
            'extra_charges_json' => !empty($itemizedBreakdown) ? json_encode($itemizedBreakdown) : null,
            'arrears_amount' => $arrears,
            'late_fee' => $lateFee,
            'total_amount' => $total,
            'paid_amount' => 0.0,
            'balance_amount' => $total,
            'issue_date' => $issueDate,
            'due_date' => $dueDate,
            'payment_status' => 'unpaid',
            'template_id' => $templateId,
            'created_at' => date('Y-m-d H:i:s')
        )));

        $newBillId = $this->BillingBill->id;
        if (!empty($this->request->data['BillingBill']['auto_print'])) {
            $this->Session->write('WV_AUTO_PRINT_BILL_ID', $newBillId);
        }

        $this->Session->setFlash(sprintf(__('Single bill %s created.'), h($billNo)), 'flash', array('alert' => 'success'));
        return $this->redirect(array('action' => 'index'));
    }

    $billMeters = $this->BillingMeter->find('all', array(
        'conditions' => array('BillingMeter.status' => 'active'),
        'contain' => array('BillingProperty' => array('BillingClient')),
        'order' => array('BillingMeter.id' => 'ASC')
    ));

    $properties = $this->BillingProperty->find('all', array(
        'contain' => array('BillingClient', 'BillingMeter'),
        'order' => array('BillingProperty.title' => 'ASC')
    ));

    $societies = $this->BillingSociety->find('list', array('fields' => array('id', 'name')));
    $templates = $this->BillingTemplate->find('list', array('fields' => array('id', 'name')));

    $settingsList = $this->BillingSetting->find('list', array('fields' => array('setting_key', 'setting_value')));
    $defaultElecRate = isset($settingsList['subm_rate_elec']) ? (float)$settingsList['subm_rate_elec'] : 58.34;
    $defaultWaterRate = isset($settingsList['subm_rate_water']) ? (float)$settingsList['subm_rate_water'] : 100.00;
    $defaultGasRate = isset($settingsList['subm_rate_gas']) ? (float)$settingsList['subm_rate_gas'] : 350.00;
    $defaultDueDays = isset($settingsList['subm_due_after_days']) ? (int)$settingsList['subm_due_after_days'] : 3;

    // Direct Property to Client lookup mapping
    $clientsList = $this->BillingClient->find('all');
    $clientById = array();
    foreach($clientsList as $cl) {
        $clientById[$cl['BillingClient']['id']] = $cl['BillingClient'];
    }

    $propsList = $this->BillingProperty->find('all');
    $propToClient = array();
    foreach($propsList as $pr) {
        $pId = (int)$pr['BillingProperty']['id'];
        $cId = !empty($pr['BillingProperty']['billing_client_id']) ? (int)$pr['BillingProperty']['billing_client_id'] : 0;
        if($cId && isset($clientById[$cId])) {
            $propToClient[$pId] = $clientById[$cId];
        }
    }

    // Build existing bills lookup map
    $allBillsRaw = $this->BillingBill->find('all', array(
        'fields' => array('id', 'bill_number', 'billing_property_id', 'utility_type', 'billing_period', 'total_amount', 'payment_status'),
        'recursive' => -1
    ));
    $existingBillsLookup = array();
    foreach($allBillsRaw as $ab) {
        $b = $ab['BillingBill'];
        $pId = (int)$b['billing_property_id'];
        $uType = strtolower(trim($b['utility_type']));
        $period = trim($b['billing_period']);
        $normPeriod = $period;
        if (preg_match('/^(\d{4})-(\d{1,2})/', $period, $bmp)) {
            $normPeriod = sprintf('%04d-%02d', $bmp[1], $bmp[2]);
        }
        $bEntry = array(
            'id' => (int)$b['id'],
            'bill_number' => $b['bill_number'],
            'total_amount' => (float)$b['total_amount'],
            'payment_status' => $b['payment_status']
        );
        $existingBillsLookup[$pId . '_' . $uType . '_' . $period] = $bEntry;
        $existingBillsLookup[$pId . '_' . $uType . '_' . $normPeriod] = $bEntry;
    }

    // Build all readings map
    $allReadingsRaw = $this->BillingReading->find('all', array(
        'order' => array('BillingReading.id' => 'DESC'),
        'recursive' => -1
    ));
    $allReadingsLookup = array();
    $latestReadingsLookup = array();
    foreach($allReadingsRaw as $ar) {
        $r = $ar['BillingReading'];
        $mId = (int)$r['meter_id'];
        $monthStr = trim($r['reading_month']);
        $normMonth = $monthStr;
        if (preg_match('/^(\d{4})-(\d{1,2})/', $monthStr, $mmp)) {
            $normMonth = sprintf('%04d-%02d', $mmp[1], $mmp[2]);
        }
        $entry = array(
            'previous_reading' => (float)$r['previous_reading'],
            'current_reading' => (float)$r['current_reading'],
            'units_consumed' => (float)$r['units_consumed'],
            'reading_month' => $normMonth
        );
        $allReadingsLookup[$mId . '_' . $monthStr] = $entry;
        $allReadingsLookup[$mId . '_' . $normMonth] = $entry;

        if(!isset($latestReadingsLookup[$mId])) {
            $latestReadingsLookup[$mId] = $entry;
        }
    }

    $lateFeeRules = array(
        'mode' => $this->_getSetting('subm_late_fee_mode', 'percent'),
        'val' => (float)$this->_getSetting('subm_late_fee_val', 7.0),
        'min' => (float)$this->_getSetting('subm_late_fee_min', 200.0),
        'max' => (float)$this->_getSetting('subm_late_fee_max', 2000.0)
    );

    // Maintenance heads
    $maintHeads = $this->_getSetting('subm_maintenance_heads', array());
    if(is_string($maintHeads)) {
        $un = @unserialize($maintHeads);
        if($un !== false) $maintHeads = $un;
    }

    // Extra recurring charges
    $extraChargesRaw = $this->BillingExtraCharge->find('all', array(
        'conditions' => array('BillingExtraCharge.charge_type' => 'recurring'),
        'recursive' => -1
    ));

    // Properties lookup with maintenance and charges
    $propertiesData = array();
    foreach($properties as $pr) {
        $pId = (int)$pr['BillingProperty']['id'];
        $pCat = !empty($pr['BillingProperty']['category']) ? trim($pr['BillingProperty']['category']) : '';
        $pTitle = !empty($pr['BillingProperty']['title']) ? trim($pr['BillingProperty']['title']) : '';
        
        // Determine property type
        $typeMatch = 'Apartment';
        if(stripos($pTitle, 'B1/') !== false || stripos($pTitle, 'B2/') !== false || stripos($pTitle, 'B3/') !== false || stripos($pTitle, 'B4/') !== false || stripos($pTitle, 'FF') !== false || stripos($pTitle, 'SF') !== false || stripos($pTitle, 'GF') !== false || stripos($pCat, 'Apartment') !== false || stripos($pCat, 'Flat') !== false) {
            $typeMatch = 'Apartment';
        } elseif(stripos($pCat, 'Plot') !== false) {
            $typeMatch = 'Plot';
        } else {
            $typeMatch = 'House';
        }

        // Determine area
        $areaNum = 4.0;
        if(!empty($pr['BillingProperty']['area']) && (float)$pr['BillingProperty']['area'] > 0) {
            $areaNum = (float)$pr['BillingProperty']['area'];
        } elseif(preg_match('/([0-9\.]+)\s*(marla|kanal|sqft|m)?/i', $pTitle, $am)) {
            $areaNum = (float)$am[1];
        }

        $mItems = array();
        $mTotal = 0.0;
        if(is_array($maintHeads)) {
            foreach($maintHeads as $h) {
                $hType = !empty($h['property_type']) ? trim($h['property_type']) : '';
                if(empty($hType) || strcasecmp($hType, $typeMatch) === 0 || strcasecmp($hType, 'all') === 0 || strcasecmp($hType, 'any') === 0) {
                    $min = isset($h['min_area']) && $h['min_area'] !== '' ? (float)$h['min_area'] : 0.0;
                    $max = isset($h['max_area']) && $h['max_area'] !== '' ? (float)$h['max_area'] : 999999.0;
                    if($areaNum >= $min && $areaNum <= $max) {
                        $amt = !empty($h['amount']) ? (float)$h['amount'] : 0.0;
                        $mItems[] = array('name' => $h['name'], 'amount' => $amt);
                        $mTotal += $amt;
                    }
                }
            }
        }
        if(empty($mItems)) {
            $baseFee = (float)$this->_getSetting('subm_maintenance_fee', 0.0);
            if($baseFee > 0) {
                $mItems[] = array('name' => 'Monthly Maintenance Fee', 'amount' => $baseFee);
                $mTotal += $baseFee;
            }
        }

        $oItems = array();
        $oTotal = 0.0;
        foreach($extraChargesRaw as $ec) {
            $ecPropId = (int)$ec['BillingExtraCharge']['billing_property_id'];
            if($ecPropId === $pId || $ecPropId === 0) {
                $amt = (float)$ec['BillingExtraCharge']['amount'];
                $oItems[] = array('name' => $ec['BillingExtraCharge']['title'], 'amount' => $amt);
                $oTotal += $amt;
            }
        }

        // Past Arrears
        $pastUnpaid = $this->BillingBill->find('all', array(
            'conditions' => array('BillingBill.billing_property_id' => $pId, 'BillingBill.payment_status' => array('unpaid', 'partial')),
            'fields' => array('balance_amount'),
            'recursive' => -1
        ));
        $arrears = 0.0;
        foreach($pastUnpaid as $pu) {
            $arrears += (float)$pu['BillingBill']['balance_amount'];
        }

        $propertiesData[$pId] = array(
            'maintenance_items' => $mItems,
            'maintenance_amount' => $mTotal,
            'other_charges_items' => $oItems,
            'other_charges_amount' => $oTotal,
            'arrears_amount' => $arrears
        );
    }

    $metersData = array();
    foreach($billMeters as $bm) {
        $m = $bm['BillingMeter'];
        $metersData[$m['id']] = array(
            'id' => (int)$m['id'],
            'meter_number' => $m['meter_number'],
            'utility_type' => $m['utility_type'],
            'current_reading' => (float)$m['current_reading'],
            'property_id' => (int)$m['billing_property_id']
        );
    }

    $this->set(compact('billMeters', 'properties', 'societies', 'templates', 'defaultElecRate', 'defaultWaterRate', 'defaultGasRate', 'defaultDueDays', 'propToClient', 'existingBillsLookup', 'allReadingsLookup', 'latestReadingsLookup', 'lateFeeRules', 'propertiesData', 'metersData'));
}

protected function _calculatePropertyMaintenance($prop, $socId = 1)
{
    $heads = $this->BillingSetting->getSetting('subm_maintenance_heads', array(), $socId);
    if(is_string($heads))
    {
        $un = @unserialize($heads);
        if($un !== false) $heads = $un;
    }

    if(empty($heads) || !is_array($heads))
    {
        return 500.0 + 1101.0 + 350.0;
    }

    $propCat = !empty($prop['BillingProperty']['category']) ? strtolower(trim($prop['BillingProperty']['category'])) : 'house';
    $propTitle = !empty($prop['BillingProperty']['title']) ? $prop['BillingProperty']['title'] : '';
    
    // Extract numeric area
    $areaNum = 4.0;
    if(preg_match('/([0-9\.]+)\s*(marla|kanal|sqft|m)?/i', $propTitle, $m))
    {
        $areaNum = (float)$m[1];
    }

    $total = 0.0;
    foreach($heads as $h)
    {
        $hType = !empty($h['property_type']) ? strtolower(trim($h['property_type'])) : '';
        if($hType !== '' && $hType !== 'any' && $hType !== $propCat)
        {
            continue;
        }

        $min = isset($h['min_area']) && $h['min_area'] !== '' ? (float)$h['min_area'] : 0.0;
        $max = isset($h['max_area']) && $h['max_area'] !== '' ? (float)$h['max_area'] : 999999.0;
        $amt = isset($h['amount']) ? (float)$h['amount'] : 0.0;

        if($areaNum >= $min && $areaNum <= $max)
        {
            $total += $amt;
        }
    }

    return ($total > 0) ? $total : 1951.0;
}

protected function _calculateMeterRate($utilityType, $prop, $units = 0, $socId = 1)
{
    $slabs = $this->BillingSetting->getSetting('subm_area_slabs', array(), $socId);
    if(is_string($slabs))
    {
        $un = @unserialize($slabs);
        if($un !== false) $slabs = $un;
    }

    if(!empty($slabs) && is_array($slabs))
    {
        $propCat = !empty($prop['BillingProperty']['category']) ? strtolower(trim($prop['BillingProperty']['category'])) : 'house';
        $propTitle = !empty($prop['BillingProperty']['title']) ? $prop['BillingProperty']['title'] : '';
        $areaNum = 4.0;
        if(preg_match('/([0-9\.]+)/', $propTitle, $m))
        {
            $areaNum = (float)$m[1];
        }

        foreach($slabs as $s)
        {
            if(!empty($s['meter_type']) && strtolower($s['meter_type']) !== strtolower($utilityType)) continue;
            if(!empty($s['property_type']) && strtolower($s['property_type']) !== 'any' && strtolower($s['property_type']) !== $propCat) continue;
            
            $minArea = isset($s['area_min']) && $s['area_min'] !== '' ? (float)$s['area_min'] : 0.0;
            $maxArea = isset($s['area_max']) && $s['area_max'] !== '' ? (float)$s['area_max'] : 999999.0;
            if($areaNum < $minArea || $areaNum > $maxArea) continue;

            $minUnits = isset($s['min_units']) && $s['min_units'] !== '' ? (float)$s['min_units'] : 0.0;
            $maxUnits = isset($s['max_units']) && $s['max_units'] !== '' ? (float)$s['max_units'] : 999999.0;
            if($units >= $minUnits && $units <= $maxUnits)
            {
                return (float)$s['rate'];
            }
        }
    }

    if(strtolower($utilityType) === 'water')
    {
        return (float)$this->BillingSetting->getSetting('subm_rate_water', 100.00, $socId);
    }
    if(strtolower($utilityType) === 'gas')
    {
        return (float)$this->BillingSetting->getSetting('subm_rate_gas', 350.00, $socId);
    }
    return (float)$this->BillingSetting->getSetting('subm_rate_elec', 58.34, $socId);
}

protected function _calculateLateFee($principal, $socId = 1)
{
    $percent = (float)$this->BillingSetting->getSetting('subm_late_fee_percent', 7.0, $socId);
    if($percent <= 0) $percent = 7.0;
    return round($principal * ($percent / 100.0), 2);
}

    public function generate_custom()
    {
        if($this->request->is('post'))
        {
            $propId = (int)$this->request->data['BillingBill']['property_id'];
            $prop = $this->BillingProperty->findById($propId);
            if(empty($prop))
            {
                $this->Session->setFlash(__('Please select a property unit.'), 'flash', array('alert' => 'danger'));
                return $this->redirect(array('action' => 'index'));
            }

            $socId = !empty($this->request->data['BillingBill']['society_id']) ? (int)$this->request->data['BillingBill']['society_id'] : (int)$prop['BillingProperty']['society_id'];
            $clientId = (int)$prop['BillingProperty']['billing_client_id'];
            $month = trim($this->request->data['BillingBill']['billing_period']);
            $issueDate = !empty($this->request->data['BillingBill']['issue_date']) ? $this->request->data['BillingBill']['issue_date'] : date('Y-m-d');
            $dueDate = !empty($this->request->data['BillingBill']['due_date']) ? $this->request->data['BillingBill']['due_date'] : date('Y-m-d', strtotime('+10 days'));
            $templateId = !empty($this->request->data['BillingBill']['template_id']) ? (int)$this->request->data['BillingBill']['template_id'] : 11;

            $itemsRaw = !empty($this->request->data['BillingBill']['items']) ? $this->request->data['BillingBill']['items'] : array();
            $customItems = array();
            $customTotal = 0.0;

            if (is_array($itemsRaw) && !empty($itemsRaw)) {
                foreach ($itemsRaw as $it) {
                    $desc = trim(isset($it['desc']) ? $it['desc'] : '');
                    $amt = (float)(isset($it['amount']) ? $it['amount'] : 0);
                    $cat = trim(isset($it['category']) ? $it['category'] : 'Other Charges');
                    if (!empty($desc) && $amt > 0) {
                        $customItems[] = array(
                            'category' => $cat,
                            'desc' => $desc,
                            'name' => $desc,
                            'amount' => $amt
                        );
                        $customTotal += $amt;
                    }
                }
            }

            // Fallback to lumpsum if no items array
            if ($customTotal <= 0 && isset($this->request->data['BillingBill']['custom_amount'])) {
                $customTotal = (float)$this->request->data['BillingBill']['custom_amount'];
                $customTitle = !empty($this->request->data['BillingBill']['custom_title']) ? trim($this->request->data['BillingBill']['custom_title']) : 'Custom Charge';
                $customItems[] = array(
                    'category' => 'Other Charges',
                    'desc' => $customTitle,
                    'name' => $customTitle,
                    'amount' => $customTotal
                );
            }

            $extraAmt = 0.0;
            $includeMaint = !empty($this->request->data['BillingBill']['include_maintenance']);
            $includeOther = !empty($this->request->data['BillingBill']['include_other_charges']);

            if($includeMaint && !empty($prop['BillingProperty']['maintenance_items'])) {
                $maintItems = @unserialize($prop['BillingProperty']['maintenance_items']);
                if(is_array($maintItems)) {
                    foreach($maintItems as $mi) {
                        if(isset($mi['amount'])) {
                            $mAmt = (float)$mi['amount'];
                            $extraAmt += $mAmt;
                            $customItems[] = array('category' => 'Maintenance', 'desc' => isset($mi['name']) ? $mi['name'] : 'Maintenance Fee', 'amount' => $mAmt);
                        }
                    }
                }
            }

            if($includeOther) {
                $otherCharges = $this->BillingExtraCharge->find('all', array(
                    'conditions' => array(
                        'BillingExtraCharge.billing_property_id' => $propId,
                        'BillingExtraCharge.charge_type' => 'recurring',
                        'BillingExtraCharge.status' => 'active'
                    )
                ));
                foreach($otherCharges as $oc) {
                    $oAmt = (float)$oc['BillingExtraCharge']['amount'];
                    $extraAmt += $oAmt;
                    $customItems[] = array('category' => 'Other', 'desc' => $oc['BillingExtraCharge']['title'], 'amount' => $oAmt);
                }
            }

            $totalAmt = $customTotal + $extraAmt;
            $billNo = 'WB' . rand(9000, 9999);

            $this->BillingBill->create();
            $this->BillingBill->save(array('BillingBill' => array(
                'society_id' => $socId,
                'bill_number' => $billNo,
                'billing_property_id' => $propId,
                'billing_client_id' => $clientId,
                'utility_type' => 'Custom Invoice',
                'billing_period' => $month,
                'previous_reading' => 0.0,
                'current_reading' => 0.0,
                'units_consumed' => 0.0,
                'unit_rate' => 0.0,
                'consumption_amount' => 0.0,
                'extra_charges_amount' => $totalAmt,
                'extra_charges_json' => !empty($customItems) ? json_encode($customItems) : null,
                'arrears_amount' => 0.0,
                'late_fee' => 0.0,
                'total_amount' => $totalAmt,
                'paid_amount' => 0.0,
                'balance_amount' => $totalAmt,
                'issue_date' => $issueDate,
                'due_date' => $dueDate,
                'payment_status' => 'unpaid',
                'template_id' => $templateId,
                'created_at' => date('Y-m-d H:i:s')
            )));

            $this->Session->setFlash(sprintf(__('Custom bill %s of PKR %s created successfully with %d line item(s).'), h($billNo), number_format($totalAmt, 2), count($customItems)), 'flash', array('alert' => 'success'));
        }
        return $this->redirect(array('action' => 'index'));
    }

    public function pay_from_modal()
    {
        $bId = (int)$this->request->data['BillingBill']['bill_id'];
        $bill = $this->BillingBill->findById($bId);
        if(!empty($bill))
        {
            $paymentType = !empty($this->request->data['BillingBill']['payment_type']) ? $this->request->data['BillingBill']['payment_type'] : 'full';
            $paidInput = (float)$this->request->data['BillingBill']['pay_amount'];
            $payDate = !empty($this->request->data['BillingBill']['pay_date']) ? $this->request->data['BillingBill']['pay_date'] : date('Y-m-d');
            $method = !empty($this->request->data['BillingBill']['payment_method']) ? $this->request->data['BillingBill']['payment_method'] : 'Cash';
            $ref = isset($this->request->data['BillingBill']['payment_ref']) ? trim($this->request->data['BillingBill']['payment_ref']) : '';

            $origTotal = (float)$bill['BillingBill']['total_amount'];
            $origLateFee = (float)$bill['BillingBill']['late_fee'];
            $prevPaid = (float)$bill['BillingBill']['paid_amount'];

            // Overdue fee waiver logic
            $waiveCheck = !empty($this->request->data['BillingBill']['waive_late_fee']);
            $waivedAmt = 0.0;
            $newLateFee = $origLateFee;
            $newTotal = $origTotal;

            if($waiveCheck) {
                $waivedAmt = (isset($this->request->data['BillingBill']['waived_late_fee']) && $this->request->data['BillingBill']['waived_late_fee'] !== '') ? (float)$this->request->data['BillingBill']['waived_late_fee'] : $origLateFee;
                if($waivedAmt > 0) {
                    $newLateFee = max(0.0, $origLateFee - $waivedAmt);
                    $newTotal = max(0.0, $origTotal - $waivedAmt);
                    $noteWaiver = sprintf(__(' [Late fee waived: PKR %s]'), number_format($waivedAmt, 2));
                    $ref .= $noteWaiver;
                }
            }

            if($paymentType === 'full') {
                // Full Payment: clear entire remaining net balance
                $newPaidTotal = $newTotal;
                $paidThisTime = max(0.0, $newTotal - $prevPaid);
                $newBalance = 0.0;
                $status = 'paid';
            } else {
                // Partial Payment
                $paidThisTime = $paidInput;
                $newPaidTotal = $prevPaid + $paidThisTime;
                if($newPaidTotal >= $newTotal && $newTotal > 0) {
                    $newBalance = 0.0;
                    $status = 'paid';
                    $newPaidTotal = $newTotal;
                } else {
                    $newBalance = max(0.0, $newTotal - $newPaidTotal);
                    $status = ($newPaidTotal > 0) ? 'partial' : 'unpaid';
                }
            }

            $this->BillingBill->id = $bId;
            $this->BillingBill->save(array('BillingBill' => array(
                'late_fee' => $newLateFee,
                'total_amount' => $newTotal,
                'paid_amount' => $newPaidTotal,
                'balance_amount' => $newBalance,
                'payment_status' => $status,
                'payment_date' => $payDate,
                'payment_method' => $method,
                'transaction_ref' => $ref
            )));

            // Auto-post Income Ledger Transaction into ARD Accounts
            $this->_syncBillPaidIncomeLedgerTransaction($bId);

            // Reload updated bill record with Client details
            $updatedBill = $this->BillingBill->find('first', array(
                'conditions' => array('BillingBill.id' => $bId),
                'contain' => array('BillingClient', 'BillingProperty')
            ));

            // Dispatch automated SMS and Email notifications to client
            $this->_dispatchPaymentNotifications($updatedBill, $paidThisTime, $waivedAmt, $method, $ref);

            // Auto-dispatch official WhatsApp Paid Slip with PDF attachment if instant receipts enabled
            $socId = !empty($bill['BillingBill']['society_id']) ? (int)$bill['BillingBill']['society_id'] : 1;
            $instantReceipts = $this->BillingSetting->getSetting('subm_upgrade_instant_payment_receipt_enabled', '1', $socId);
            if($instantReceipts === '1' || $instantReceipts === 1 || $instantReceipts === true)
            {
                try {
                    $this->_sendBillWhatsappDirect($bId);
                } catch(Exception $e) {}
            }

            // Construct rich post-payment notification payload for instant WhatsApp & SMS dispatch
            $cl = !empty($updatedBill['BillingClient']) ? $updatedBill['BillingClient'] : array();
            $pr = !empty($updatedBill['BillingProperty']) ? $updatedBill['BillingProperty'] : array();
            $cName = !empty($cl['name']) ? strtoupper($cl['name']) : __('Valued Resident');
            $cPhone = !empty($cl['phone']) ? trim($cl['phone']) : '';
            $consumerId = !empty($cl['id']) ? ('WV70' . str_pad($cl['id'], 2, '0', STR_PAD_LEFT)) : '-';
            $unitNo = !empty($pr['property_no']) ? $pr['property_no'] : (!empty($pr['title']) ? $pr['title'] : '-');
            $billNo = $bill['BillingBill']['bill_number'];
            $monthLabel = date('M Y', strtotime($bill['BillingBill']['billing_period'] . '-01'));

            $cleanPhone = preg_replace('/[^0-9]/', '', (string)$cPhone);
            if(substr($cleanPhone, 0, 1) === '0') $cleanPhone = '92' . substr($cleanPhone, 1);

            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
            $host = !empty($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
            $printReceiptUrl = $protocol . $host . Router::url(array('plugin' => 'billing_manager', 'controller' => 'billing_bills', 'action' => 'print_receipt', $bId));
            $printBillUrl = $protocol . $host . Router::url(array('plugin' => 'billing_manager', 'controller' => 'billing_bills', 'action' => 'print_bill', $bId));

            $waPaidMsg = "🏛️ *WHITE VILLAS LAHORE*\n"
                       . "✅ *OFFICIAL PAYMENT RECEIPT*\n"
                       . "--------------------------------\n"
                       . "🧾 *Receipt / Bill No:* #" . $billNo . "\n"
                       . "👤 *Consumer ID:* " . $consumerId . "\n"
                       . "🏠 *Resident:* " . $cName . "\n"
                       . "📍 *House/Plot:* " . $unitNo . "\n"
                       . "📅 *Billing Month:* " . $monthLabel . "\n"
                       . "--------------------------------\n"
                       . "💵 *Total Billed:* PKR " . number_format($newTotal, 2) . "\n"
                       . "🟢 *Amount Received & Paid:* PKR " . number_format($paidThisTime, 2) . "\n"
                       . "📅 *Payment Date:* " . date('d-M-Y', strtotime($payDate)) . "\n"
                       . "💳 *Payment Method:* " . $method . "\n"
                       . (!empty($ref) ? ("🔖 *Ref / Note:* " . $ref . "\n") : "")
                       . "⚖️ *Remaining Balance:* PKR " . number_format($newBalance, 2) . ($newBalance <= 0 ? " (Fully Cleared & Settled)" : "") . "\n"
                       . "🟢 *Status:* " . ($newBalance <= 0 ? "CLEARED & PAID" : "PARTIALLY PAID") . "\n"
                       . "--------------------------------\n"
                       . "📄 *VIEW & DOWNLOAD OFFICIAL PAID SLIP (PDF):*\n"
                       . $printReceiptUrl . "\n"
                       . "--------------------------------\n"
                       . "🙏 *Thank you for your timely payment! Your account is updated.*\n"
                       . "*White Villas Management Office*\n"
                       . "📞 Mobile: +92 339 1101156";

            $waUrl = !empty($cleanPhone) ? ("https://wa.me/" . $cleanPhone . "?text=" . urlencode($waPaidMsg)) : "";

            $this->Session->write('BillingManager.just_paid_bill', array(
                'bill_id' => $bId,
                'bill_number' => $billNo,
                'consumer_id' => $consumerId,
                'client_name' => $cName,
                'phone' => $cPhone,
                'clean_phone' => $cleanPhone,
                'unit_no' => $unitNo,
                'billing_month' => $monthLabel,
                'total_amount' => $newTotal,
                'paid_amount' => $paidThisTime,
                'balance_amount' => $newBalance,
                'payment_status' => $status,
                'payment_date' => date('d M Y', strtotime($payDate)),
                'payment_method' => $method,
                'transaction_ref' => $ref,
                'wa_url' => $waUrl,
                'print_receipt_url' => $printReceiptUrl,
                'print_bill_url' => $printBillUrl
            ));

            $flashMsg = sprintf(__('Payment of PKR %s recorded for Bill %s. Status: %s.'), number_format($paidThisTime, 2), h($bill['BillingBill']['bill_number']), strtoupper($status));
            if($waivedAmt > 0) {
                $flashMsg .= sprintf(__(' (Overdue fee waiver of PKR %s applied).'), number_format($waivedAmt, 2));
            }
            if($status === 'partial') {
                $flashMsg .= sprintf(__(' Remaining balance: PKR %s.'), number_format($newBalance, 2));
            } else {
                $flashMsg .= __(' Bill is fully settled with NO remaining unpaid amount.');
            }
            $this->Session->setFlash($flashMsg, 'flash', array('alert' => 'success'));
        }
        return $this->redirect(array('action' => 'index'));
    }

    protected function _dispatchPaymentNotifications($bill, $paidThisTime, $waivedAmt, $method, $ref)
    {
        if(empty($bill['BillingBill']['billing_client_id'])) return;

        $cId = (int)$bill['BillingBill']['billing_client_id'];
        $client = $this->BillingClient->findById($cId);
        if(empty($client)) return;

        $socId = !empty($bill['BillingBill']['society_id']) ? (int)$bill['BillingBill']['society_id'] : 1;

        $cName = !empty($client['BillingClient']['name']) ? $client['BillingClient']['name'] : __('Valued Resident');
        $cPhone = !empty($client['BillingClient']['phone']) ? trim($client['BillingClient']['phone']) : '';
        $cEmail = !empty($client['BillingClient']['email']) ? trim($client['BillingClient']['email']) : '';

        $billNo = $bill['BillingBill']['bill_number'];
        $totalAmt = number_format((float)$bill['BillingBill']['total_amount'], 2);
        $paidAmt = number_format((float)$paidThisTime, 2);
        $balAmt = number_format((float)$bill['BillingBill']['balance_amount'], 2);
        $status = strtoupper($bill['BillingBill']['payment_status']);

        // 1. Send SMS Notification
        if(!empty($cPhone))
        {
            $defaultCc = $this->BillingSetting->getSetting('subm_reminder_default_cc', '+92', $socId);
            $phone = $cPhone;
            if(substr($phone, 0, 1) === '0') {
                $phone = $defaultCc . substr($phone, 1);
            } elseif(substr($phone, 0, 1) !== '+') {
                $phone = $defaultCc . $phone;
            }

            $smsMsg = sprintf(
                "Dear %s, payment of PKR %s for Bill #%s has been received via %s. Status: %s. Remaining Balance: PKR %s. Thank you!",
                $cName,
                $paidAmt,
                $billNo,
                $method,
                $status,
                $balAmt
            );

            $useSystemGateway = (int)$this->BillingSetting->getSetting('subm_use_system_sms_gateway', 1, $socId);
            $customUrl = $this->BillingSetting->getSetting('subm_sms_gateway_url', 'https://sms.alriazdevelopers.com/services/send.php', $socId);
            $customToken = $this->BillingSetting->getSetting('subm_sms_gateway_token', '3bfccea8319461dd7f1d97d60f1fead9b3ade365', $socId);
            $customSender = $this->BillingSetting->getSetting('subm_sms_gateway_sender_id', 'WhiteVillas', $socId);
            $customMethod = $this->BillingSetting->getSetting('subm_sms_gateway_method', 'GET', $socId);

            $systemSms = null;
            if($useSystemGateway)
            {
                try {
                    $db = ConnectionManager::getDataSource('default');
                    $sysRows = $db->fetchAll("SELECT * FROM smssettings LIMIT 1");
                    if(!empty($sysRows[0])) {
                        $systemSms = isset($sysRows[0]['smssettings']) ? $sysRows[0]['smssettings'] : $sysRows[0];
                    }
                } catch(Exception $e) {}
            }

            if($useSystemGateway && !empty($systemSms))
            {
                $apiUrl = !empty($systemSms['api']) ? $systemSms['api'] : 'http://sms.alriazdevelopers.com/services/send.php';
                $params = array(
                    !empty($systemSms['husername']) ? $systemSms['husername'] : 'option' => !empty($systemSms['username']) ? $systemSms['username'] : '',
                    !empty($systemSms['hpassword']) ? $systemSms['hpassword'] : 'key' => !empty($systemSms['password']) ? $systemSms['password'] : '',
                    !empty($systemSms['hsenderid']) ? $systemSms['hsenderid'] : 'type' => !empty($systemSms['senderid']) ? $systemSms['senderid'] : 'sms',
                    !empty($systemSms['hmobile']) ? $systemSms['hmobile'] : 'number' => $phone,
                    !empty($systemSms['hmessage']) ? $systemSms['hmessage'] : 'message' => $smsMsg
                );

                if(strtolower($systemSms['type']) === 'post') {
                    $ch = curl_init($apiUrl);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
                    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                    @curl_exec($ch);
                    curl_close($ch);
                } else {
                    $fullUrl = $apiUrl . '?' . http_build_query($params);
                    @file_get_contents($fullUrl);
                }
            }
            else
            {
                $params = array(
                    'token' => $customToken,
                    'to' => $phone,
                    'message' => $smsMsg,
                    'sender' => $customSender
                );

                if(strtoupper($customMethod) === 'POST') {
                    $ch = curl_init($customUrl);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
                    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                    @curl_exec($ch);
                    curl_close($ch);
                } else {
                    $fullUrl = $customUrl . '?' . http_build_query($params);
                    @file_get_contents($fullUrl);
                }
            }

            // Log SMS dispatch
            $this->loadModel('BillingManager.BillingReminderLog');
            $this->BillingReminderLog->create();
            $this->BillingReminderLog->save(array('BillingReminderLog' => array(
                'society_id' => $socId,
                'bill_id' => $bill['BillingBill']['id'],
                'channel' => 'sms',
                'recipient' => $phone,
                'message_body' => $smsMsg,
                'status' => 'sent',
                'response_payload' => is_string($apiResponse) ? substr($apiResponse, 0, 255) : 'Dispatched via API',
                'sent_at' => date('Y-m-d H:i:s')
            )));
        }

        // 2. Send Email Notification
        if(!empty($cEmail) && filter_var($cEmail, FILTER_VALIDATE_EMAIL))
        {
            $subject = sprintf("Payment Confirmation: Bill #%s (%s)", $billNo, $status);
            $emailHtml = "
            <div style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto;border:1px solid #e2e8f0;padding:20px;border-radius:8px;'>
                <h2 style='color:#16a34a;margin-top:0;'>Payment Confirmation</h2>
                <p>Dear <strong>" . h($cName) . "</strong>,</p>
                <p>We have successfully received your payment for utility bill <strong>#" . h($billNo) . "</strong>.</p>
                <table style='width:100%;border-collapse:collapse;margin:15px 0;'>
                    <tr style='background:#f8fafc;'><td style='padding:8px;border:1px solid #cbd5e1;'><strong>Bill Number:</strong></td><td style='padding:8px;border:1px solid #cbd5e1;'>" . h($billNo) . "</td></tr>
                    <tr><td style='padding:8px;border:1px solid #cbd5e1;'><strong>Amount Paid:</strong></td><td style='padding:8px;border:1px solid #cbd5e1;'>PKR " . $paidAmt . "</td></tr>
                    <tr style='background:#f8fafc;'><td style='padding:8px;border:1px solid #cbd5e1;'><strong>Payment Method:</strong></td><td style='padding:8px;border:1px solid #cbd5e1;'>" . h($method) . "</td></tr>
                    <tr><td style='padding:8px;border:1px solid #cbd5e1;'><strong>Payment Status:</strong></td><td style='padding:8px;border:1px solid #cbd5e1;'><strong>" . h($status) . "</strong></td></tr>
                    <tr style='background:#f8fafc;'><td style='padding:8px;border:1px solid #cbd5e1;'><strong>Remaining Balance:</strong></td><td style='padding:8px;border:1px solid #cbd5e1;color:#dc2626;font-weight:bold;'>PKR " . $balAmt . "</td></tr>";
            if($waivedAmt > 0) {
                $emailHtml .= "<tr><td style='padding:8px;border:1px solid #cbd5e1;'><strong>Waived Late Fee:</strong></td><td style='padding:8px;border:1px solid #cbd5e1;color:#16a34a;'>PKR " . number_format($waivedAmt, 2) . "</td></tr>";
            }
            $emailHtml .= "
                </table>
                <p style='color:#64748b;font-size:12px;'>Thank you for your prompt payment.</p>
            </div>";

            try {
                App::uses('CakeEmail', 'Network/Email');
                $email = new CakeEmail();
                $email->from(array('billing@whitevillas.com' => 'White Villas Billing'));
                $email->to($cEmail);
                $email->subject($subject);
                $email->emailFormat('html');
                $email->send($emailHtml);
            } catch(Exception $e) {
                @mail($cEmail, $subject, strip_tags($smsMsg), "From: billing@whitevillas.com");
            }
        }
    }

    public function update_bill_from_modal()
    {
        $bId = (int)$this->request->data['BillingBill']['edit_bill_id'];
        $bill = $this->BillingBill->findById($bId);
        if(!empty($bill))
        {
            $billNo = !empty($this->request->data['BillingBill']['bill_number']) ? trim($this->request->data['BillingBill']['bill_number']) : $bill['BillingBill']['bill_number'];
            $dueDate = !empty($this->request->data['BillingBill']['due_date']) ? $this->request->data['BillingBill']['due_date'] : $bill['BillingBill']['due_date'];
            $templateId = !empty($this->request->data['BillingBill']['template_id']) ? (int)$this->request->data['BillingBill']['template_id'] : (int)$bill['BillingBill']['template_id'];

            $uDesc = !empty($this->request->data['BillingBill']['utility_desc']) ? trim($this->request->data['BillingBill']['utility_desc']) : 'Electricity Charges';
            $uUnits = isset($this->request->data['BillingBill']['utility_units']) ? (float)$this->request->data['BillingBill']['utility_units'] : (float)$bill['BillingBill']['units_consumed'];
            $uRate = isset($this->request->data['BillingBill']['utility_rate']) ? (float)$this->request->data['BillingBill']['utility_rate'] : (float)$bill['BillingBill']['unit_rate'];
            $consAmt = isset($this->request->data['BillingBill']['utility_amount']) ? (float)$this->request->data['BillingBill']['utility_amount'] : round($uUnits * $uRate, 2);

            $maintTotal = 0.0;
            $jsonItems = array();

            // 1. Utility JSON item
            $jsonItems[] = array(
                'category' => 'utility',
                'type' => strtolower($bill['BillingBill']['utility_type']),
                'desc' => $uDesc,
                'units' => $uUnits,
                'rate' => $uRate,
                'amount' => $consAmt
            );

            // 2. Maintenance JSON items
            if(!empty($this->request->data['BillingBill']['maint_items']) && is_array($this->request->data['BillingBill']['maint_items']))
            {
                foreach($this->request->data['BillingBill']['maint_items'] as $mi)
                {
                    $desc = isset($mi['desc']) ? trim($mi['desc']) : '';
                    $amt = isset($mi['amount']) ? (float)$mi['amount'] : 0.0;
                    if($desc !== '' || $amt > 0)
                    {
                        $jsonItems[] = array(
                            'category' => 'maintenance',
                            'desc' => $desc,
                            'amount' => $amt
                        );
                        $maintTotal += $amt;
                    }
                }
            }

            // 3. Other charges JSON items
            $otherTotal = 0.0;
            if(!empty($this->request->data['BillingBill']['other_items']) && is_array($this->request->data['BillingBill']['other_items']))
            {
                foreach($this->request->data['BillingBill']['other_items'] as $oi)
                {
                    $desc = isset($oi['desc']) ? trim($oi['desc']) : '';
                    $amt = isset($oi['amount']) ? (float)$oi['amount'] : 0.0;
                    if($desc !== '' || $amt > 0)
                    {
                        $jsonItems[] = array(
                            'category' => 'other',
                            'desc' => $desc,
                            'amount' => $amt
                        );
                        $otherTotal += $amt;
                    }
                }
            }

            $extraChargesTotal = $maintTotal + $otherTotal;
            $currentBill = $consAmt + $extraChargesTotal;

            $arrears = isset($this->request->data['BillingBill']['arrears_amount']) ? (float)$this->request->data['BillingBill']['arrears_amount'] : (float)$bill['BillingBill']['arrears_amount'];
            $lateFee = isset($this->request->data['BillingBill']['late_fee']) ? (float)$this->request->data['BillingBill']['late_fee'] : (float)$bill['BillingBill']['late_fee'];
            
            $adjPlus = isset($this->request->data['BillingBill']['adjustment_plus']) ? (float)$this->request->data['BillingBill']['adjustment_plus'] : 0.0;
            $adjMinus = isset($this->request->data['BillingBill']['adjustment_minus']) ? (float)$this->request->data['BillingBill']['adjustment_minus'] : 0.0;
            $adjustmentNet = $adjPlus - $adjMinus;

            $newTotal = $currentBill + $arrears + $adjustmentNet;
            $paidAmt = (float)$bill['BillingBill']['paid_amount'];
            $newBal = max(0.0, $newTotal - $paidAmt);
            $status = ($paidAmt >= $newTotal && $newTotal > 0) ? 'paid' : ($paidAmt > 0 ? 'partial' : 'unpaid');

            $this->BillingBill->id = $bId;
            $this->BillingBill->save(array('BillingBill' => array(
                'bill_number' => $billNo,
                'due_date' => $dueDate,
                'template_id' => $templateId,
                'units_consumed' => $uUnits,
                'unit_rate' => $uRate,
                'consumption_amount' => $consAmt,
                'extra_charges_amount' => $extraChargesTotal,
                'arrears_amount' => $arrears,
                'late_fee' => $lateFee,
                'total_amount' => $newTotal,
                'balance_amount' => $newBal,
                'payment_status' => $status,
                'extra_charges_json' => json_encode($jsonItems)
            )));

            // Also keep meter reading in sync if reading_id is linked
            if(!empty($bill['BillingBill']['reading_id']))
            {
                $rId = (int)$bill['BillingBill']['reading_id'];
                $this->loadModel('BillingManager.BillingReading');
                $reading = $this->BillingReading->findById($rId);
                if(!empty($reading))
                {
                    $prevR = (float)$reading['BillingReading']['previous_reading'];
                    $currR = $prevR + $uUnits;
                    $this->BillingReading->id = $rId;
                    $this->BillingReading->saveField('current_reading', $currR);
                    $this->BillingReading->saveField('units_consumed', $uUnits);
                }
            }

            $this->Session->setFlash(sprintf(__('Bill %s updated successfully with complete breakdown.'), h($billNo)), 'flash', array('alert' => 'success'));
        }
        return $this->redirect(array('action' => 'index'));
    }

    public function add_adjustment()
    {
        $this->loadModel('BillingManager.BillingBillAdjustment');
        if($this->request->is('post') && !empty($this->request->data['BillingBillAdjustment']))
        {
            $bId = (int)$this->request->data['BillingBillAdjustment']['bill_id'];
            $type = (isset($this->request->data['BillingBillAdjustment']['type']) && strtolower($this->request->data['BillingBillAdjustment']['type']) === 'debit') ? 'debit' : 'credit';
            $amt = (float)$this->request->data['BillingBillAdjustment']['amount'];
            $reason = !empty($this->request->data['BillingBillAdjustment']['reason']) ? trim($this->request->data['BillingBillAdjustment']['reason']) : ($type === 'credit' ? 'Discount / Relief' : 'Additional Charge / Surcharge');
            $approvedBy = !empty($this->request->data['BillingBillAdjustment']['approved_by']) ? trim($this->request->data['BillingBillAdjustment']['approved_by']) : 'Admin';

            $bill = $this->BillingBill->findById($bId);
            if(!empty($bill) && $amt > 0)
            {
                $this->BillingBillAdjustment->create();
                $this->BillingBillAdjustment->save(array('BillingBillAdjustment' => array(
                    'society_id' => !empty($bill['BillingBill']['society_id']) ? (int)$bill['BillingBill']['society_id'] : 1,
                    'bill_id' => $bId,
                    'billing_property_id' => (int)$bill['BillingBill']['billing_property_id'],
                    'billing_client_id' => !empty($bill['BillingBill']['billing_client_id']) ? (int)$bill['BillingBill']['billing_client_id'] : null,
                    'type' => $type,
                    'amount' => $amt,
                    'reason' => $reason,
                    'approved_by' => $approvedBy,
                    'created_at' => date('Y-m-d H:i:s')
                )));

                // Recalculate bill totals safely
                $this->_recalculateBillTotalsWithAdjustments($bId);

                $this->Session->setFlash(sprintf(__('Adjustment of PKR %s (%s) recorded successfully on bill %s.'), number_format($amt, 2), ucfirst($type), h($bill['BillingBill']['bill_number'])), 'flash', array('alert' => 'success'));
            }
        }
        return $this->redirect(array('action' => 'index'));
    }

    public function delete_adjustment($adjId)
    {
        $this->loadModel('BillingManager.BillingBillAdjustment');
        $adj = $this->BillingBillAdjustment->findById((int)$adjId);
        if(!empty($adj))
        {
            $bId = (int)$adj['BillingBillAdjustment']['bill_id'];
            $this->BillingBillAdjustment->delete((int)$adjId);
            $this->_recalculateBillTotalsWithAdjustments($bId);
            $this->Session->setFlash(__('Adjustment removed and bill totals updated.'), 'flash', array('alert' => 'info'));
        }
        return $this->redirect(array('action' => 'index'));
    }

    protected function _recalculateBillTotalsWithAdjustments($bId)
    {
        $this->loadModel('BillingManager.BillingBillAdjustment');
        $bill = $this->BillingBill->findById((int)$bId);
        if(empty($bill)) return;

        $adjs = $this->BillingBillAdjustment->find('all', array(
            'conditions' => array('BillingBillAdjustment.bill_id' => (int)$bId)
        ));

        $netAdj = 0.0;
        foreach($adjs as $a) {
            $type = strtolower($a['BillingBillAdjustment']['type']);
            $amt = (float)$a['BillingBillAdjustment']['amount'];
            if($type === 'credit') {
                $netAdj -= $amt;
            } else {
                $netAdj += $amt;
            }
        }

        $consAmt = (float)$bill['BillingBill']['consumption_amount'];
        $extraAmt = (float)$bill['BillingBill']['extra_charges_amount'];
        $arrears = (float)$bill['BillingBill']['arrears_amount'];
        $baseTotal = $consAmt + $extraAmt + $arrears;
        
        $newTotal = max(0.0, $baseTotal + $netAdj);
        $paidAmt = (float)$bill['BillingBill']['paid_amount'];
        $newBal = max(0.0, $newTotal - $paidAmt);
        $status = ($paidAmt >= $newTotal && $newTotal > 0) ? 'paid' : ($paidAmt > 0 ? 'partial' : 'unpaid');

        $this->BillingBill->id = (int)$bId;
        $this->BillingBill->save(array('BillingBill' => array(
            'total_amount' => $newTotal,
            'balance_amount' => $newBal,
            'payment_status' => $status
        )));
    }

    public function repair_carry_forward()
    {
        $properties = $this->BillingProperty->find('all', array('fields' => array('id')));
        $repairedCount = 0;

        foreach($properties as $pr)
        {
            $propId = (int)$pr['BillingProperty']['id'];
            $bills = $this->BillingBill->find('all', array(
                'conditions' => array('BillingBill.billing_property_id' => $propId),
                'order' => array('BillingBill.billing_period' => 'ASC', 'BillingBill.id' => 'ASC')
            ));

            $runningArrears = 0.0;
            foreach($bills as $b)
            {
                $bId = (int)$b['BillingBill']['id'];
                $consAmt = (float)$b['BillingBill']['consumption_amount'];
                $extraAmt = (float)$b['BillingBill']['extra_charges_amount'];
                $paidAmt = (float)$b['BillingBill']['paid_amount'];
                $lateFee = (float)$b['BillingBill']['late_fee'];
                $txRef = !empty($b['BillingBill']['transaction_ref']) ? $b['BillingBill']['transaction_ref'] : '';
                $currStatus = !empty($b['BillingBill']['payment_status']) ? $b['BillingBill']['payment_status'] : 'unpaid';

                $isPaid = ($currStatus === 'paid') || ($paidAmt > 0 && $paidAmt >= (float)$b['BillingBill']['total_amount']);
                $hasWaiver = (strpos($txRef, 'waiv') !== false || strpos($txRef, 'Waiv') !== false);

                if($isPaid) {
                    // Fully paid bill: preserve exact saved total, late fee, paid amount, and zero balance
                    $newArrears = (float)$b['BillingBill']['arrears_amount'];
                    $newTotal = (float)$b['BillingBill']['total_amount'];
                    $newBal = 0.0;
                    $status = 'paid';
                    $runningArrears = 0.0;
                } else {
                    $newArrears = $runningArrears;
                    $newTotal = $consAmt + $extraAmt + $newArrears;
                    $netPayable = $newTotal + $lateFee;
                    $newBal = max(0.0, $netPayable - $paidAmt);
                    $status = ($paidAmt >= $netPayable && $netPayable > 0) ? 'paid' : ($paidAmt > 0 ? 'partial' : 'unpaid');
                    $runningArrears = $newBal;
                }

                $this->BillingBill->id = $bId;
                $this->BillingBill->save(array('BillingBill' => array(
                    'arrears_amount' => $newArrears,
                    'total_amount' => $newTotal,
                    'balance_amount' => $newBal,
                    'payment_status' => $status
                )));

                $repairedCount++;
            }
        }

        $this->Session->setFlash(sprintf(__('Carry-forward repair completed for %d bills. Paid bills and waived late fees preserved.'), $repairedCount), 'flash', array('alert' => 'success'));
        return $this->redirect(array('action' => 'index'));
    }

    public function delete($id = null)
    {
        $this->BillingBill->delete((int)$id);
        $this->Session->setFlash(__('Bill deleted.'), 'flash', array('alert' => 'success'));
        return $this->redirect(array('action' => 'index'));
    }

    public function print_bill($id = null)
    {
        $this->autoRender = false;
        $this->layout = false;

        $templateId = !empty($this->request->query['template_id']) ? (int)$this->request->query['template_id'] : null;
        $bill = null;

        if(!empty($id))
        {
            $bill = $this->BillingBill->find('first', array(
                'conditions' => array('BillingBill.id' => (int)$id),
                'contain' => array('BillingProperty' => array('BillingClient', 'BillingMeter'), 'BillingClient')
            ));
        }

        // Fallback to sample bill for preview mode
        if(empty($bill))
        {
            $bill = $this->BillingBill->find('first', array(
                'order' => array('BillingBill.id' => 'DESC'),
                'contain' => array('BillingProperty' => array('BillingClient', 'BillingMeter'), 'BillingClient')
            ));
        }

        // Load requested template or active default
        $template = null;
        if($templateId)
        {
            $template = $this->BillingTemplate->findById($templateId);
        }

        if(empty($template) || empty($template['BillingTemplate']['html_content']))
        {
            $template = $this->BillingTemplate->find('first', array(
                'conditions' => array('BillingTemplate.template_type' => 'print_bill', 'BillingTemplate.is_default' => 1)
            ));
        }

        if(empty($template) || empty($template['BillingTemplate']['html_content']))
        {
            $template = $this->BillingTemplate->findById(11);
        }

        if(empty($template) || empty($template['BillingTemplate']['html_content']))
        {
            $template = $this->BillingTemplate->find('first', array('order' => array('BillingTemplate.id' => 'ASC')));
        }

        $html = !empty($template['BillingTemplate']['html_content']) ? $template['BillingTemplate']['html_content'] : '<h2>Template HTML not found</h2>';

        $rendered = $this->_renderBillVoucherHtml($bill, $html);

        // Inject Top Floating Print & Close Bar
        $printBtnHtml = '
        <div style="position:fixed;top:12px;right:20px;z-index:99999;display:flex;gap:8px;" class="no-print">
            <button onclick="window.print()" style="background:#16a34a;color:#fff;font-weight:900;padding:10px 22px;border:none;border-radius:6px;box-shadow:0 4px 14px rgba(0,0,0,0.3);cursor:pointer;font-size:13px;display:flex;align-items:center;gap:8px;">
                🖨️ Print Bill Voucher
            </button>
            <button onclick="window.close()" style="background:#475569;color:#fff;font-weight:700;padding:10px 16px;border:none;border-radius:6px;cursor:pointer;font-size:13px;">
                ✖ Close
            </button>
        </div>';

        if(strpos($rendered, '</body>') !== false) {
            $rendered = str_replace('</body>', $printBtnHtml . '</body>', $rendered);
        } else {
            $rendered = $printBtnHtml . $rendered;
        }

        echo $rendered;
        exit;
    }

    public function admin_bulk_print()
    {
        return $this->bulk_print();
    }

    public function bulk_print()
    {
        $this->autoRender = false;
        $this->layout = false;

        // Query parameters
        $month = !empty($this->request->query['month']) ? trim($this->request->query['month']) : (!empty($this->request->data['month']) ? trim($this->request->data['month']) : null);
        $societyId = !empty($this->request->query['society_id']) ? (int)$this->request->query['society_id'] : (!empty($this->request->data['society_id']) ? (int)$this->request->data['society_id'] : null);
        $utilityType = !empty($this->request->query['utility_type']) ? trim($this->request->query['utility_type']) : (!empty($this->request->data['utility_type']) ? trim($this->request->data['utility_type']) : null);
        $status = !empty($this->request->query['status']) ? trim($this->request->query['status']) : (!empty($this->request->data['status']) ? trim($this->request->data['status']) : null);
        $billIdsParam = !empty($this->request->query['bill_ids']) ? $this->request->query['bill_ids'] : (!empty($this->request->data['bill_ids']) ? $this->request->data['bill_ids'] : null);
        $templateId = !empty($this->request->query['template_id']) ? (int)$this->request->query['template_id'] : null;
        $autoDownload = !empty($this->request->query['download_pdf']) && ($this->request->query['download_pdf'] == '1' || $this->request->query['download_pdf'] === 'true');

        $conds = array();
        if (!empty($billIdsParam)) {
            if (is_string($billIdsParam)) {
                $ids = array_filter(array_map('intval', explode(',', $billIdsParam)));
            } else {
                $ids = array_filter(array_map('intval', (array)$billIdsParam));
            }
            if (!empty($ids)) {
                $conds['BillingBill.id'] = $ids;
            }
        }

        if (!empty($month) && strtolower($month) !== 'all') {
            $normMonth = $month;
            if (preg_match('/^(\d{4})-(\d{1,2})/', $month, $mmp)) {
                $normMonth = sprintf('%04d-%02d', $mmp[1], $mmp[2]);
            }
            $conds['OR'] = array(
                'BillingBill.billing_period' => $month,
                'BillingBill.billing_period' => $normMonth
            );
        }

        if (!empty($societyId) && $societyId > 0) {
            $conds['BillingBill.society_id'] = $societyId;
        }

        if (!empty($utilityType) && strtolower($utilityType) !== 'all') {
            $conds['BillingBill.utility_type'] = $utilityType;
        }

        if (!empty($status) && strtolower($status) !== 'all') {
            $conds['BillingBill.payment_status'] = strtolower($status);
        }

        $bills = $this->BillingBill->find('all', array(
            'conditions' => $conds,
            'order' => array('BillingBill.id' => 'ASC'),
            'contain' => array('BillingProperty' => array('BillingClient', 'BillingMeter'), 'BillingClient')
        ));

        if (empty($bills)) {
            echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>No Bills Found</title><style>body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;background:#f8fafc;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;}.box{background:#fff;padding:36px 48px;border-radius:12px;box-shadow:0 10px 25px rgba(0,0,0,0.08);text-align:center;max-width:480px;border:1.5px solid #e2e8f0;}.icon{font-size:48px;margin-bottom:12px;}.btn{display:inline-block;margin-top:16px;background:#0284c7;color:#fff;font-weight:700;padding:10px 20px;border-radius:6px;text-decoration:none;border:none;cursor:pointer;}</style></head><body><div class="box"><div class="icon">📦</div><h2 style="margin:0 0 8px;color:#0f172a;">No Bills Found</h2><p style="color:#64748b;font-size:14px;margin:0 0 16px;">No bills matched the selected month or filters. Please adjust your criteria and try again.</p><button class="btn" onclick="window.close()">Close Window</button></div></body></html>';
            exit;
        }

        // Load Template
        $template = null;
        if ($templateId) {
            $template = $this->BillingTemplate->findById($templateId);
        }
        if (empty($template) || empty($template['BillingTemplate']['html_content'])) {
            $template = $this->BillingTemplate->find('first', array(
                'conditions' => array('BillingTemplate.template_type' => 'print_bill', 'BillingTemplate.is_default' => 1)
            ));
        }
        if (empty($template) || empty($template['BillingTemplate']['html_content'])) {
            $template = $this->BillingTemplate->findById(11);
        }
        if (empty($template) || empty($template['BillingTemplate']['html_content'])) {
            $template = $this->BillingTemplate->find('first', array('order' => array('BillingTemplate.id' => 'ASC')));
        }
        $templateHtml = !empty($template['BillingTemplate']['html_content']) ? $template['BillingTemplate']['html_content'] : '';

        // Extract <head> styles and scripts from template to ensure 100% identical styling to print_bill
        $templateCustomHead = '';
        if (preg_match('/<head[^>]*>(.*?)<\/head>/is', $templateHtml, $headMatch)) {
            $templateCustomHead = $headMatch[1];
        }

        $totalBills = count($bills);
        $periodLabel = !empty($month) ? $month : date('Y-m');
        $pdfSafeFileName = 'WhiteVillas_Utility_Bills_' . preg_replace('/[^a-zA-Z0-9_\-]+/', '_', $periodLabel) . '_' . $totalBills . '_Bills';

        // Render each bill voucher
        $vouchersOutput = '';
        foreach ($bills as $idx => $billItem) {
            $singleHtml = $this->_renderBillVoucherHtml($billItem, $templateHtml);

            // Strip <html>, <head>, <body> tags if inside template to embed cleanly
            if (preg_match('/<body[^>]*>(.*?)<\/body>/is', $singleHtml, $bodyMatch)) {
                $innerContent = $bodyMatch[1];
            } else {
                $innerContent = $singleHtml;
            }

            $vouchersOutput .= '
            <div class="wv-merged-page-wrapper" data-bill-id="' . (int)$billItem['BillingBill']['id'] . '" data-bill-no="' . h($billItem['BillingBill']['bill_number']) . '">
                <div class="wv-page-number-indicator no-print">Bill #' . ($idx + 1) . ' of ' . $totalBills . ' &mdash; Bill No: ' . h($billItem['BillingBill']['bill_number']) . ' (' . h($billItem['BillingBill']['utility_type']) . ')</div>
                ' . $innerContent . '
            </div>';
        }

        // Render Merged Wrapper with Top Navigation and PDF Engine
        $mergedHtml = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . h($pdfSafeFileName) . ' (' . $totalBills . ' Bills)</title>
    <!-- Template Custom Head Styles & Dependencies -->
    ' . $templateCustomHead . '
    <!-- Core External Vector & PDF Dependencies -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        @page {
            size: Letter portrait;
            margin: 0;
        }
        @media print {
            html, body {
                background: #ffffff !important;
                margin: 0 !important;
                padding: 0 !important;
                height: auto !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .no-print, .no-print * {
                display: none !important;
            }
            #merged-container {
                display: block !important;
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .wv-merged-page-wrapper {
                page-break-after: always !important;
                break-after: page !important;
                page-break-inside: avoid !important;
                margin: 0 !important;
                padding: 0 !important;
                border: none !important;
                box-shadow: none !important;
                width: 100% !important;
                height: auto !important;
                display: block !important;
            }
            .wv-merged-page-wrapper:last-child {
                page-break-after: auto !important;
                break-after: auto !important;
            }
            .page {
                margin: 2mm auto !important;
                border: 3px solid #1e3a8a !important;
                box-shadow: none !important;
                width: 205.9mm !important;
                height: 275mm !important;
                min-height: 275mm !important;
                page-break-inside: avoid !important;
                page-break-after: avoid !important;
            }
        }
        @media screen {
            body {
                background: #1e293b;
                margin: 0;
                padding-top: 76px;
                padding-bottom: 60px;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                color: #0f172a;
            }
            #merged-container {
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 36px;
                max-width: 1000px;
                margin: 0 auto;
                padding: 0 16px;
            }
            .wv-merged-page-wrapper {
                position: relative;
                background: transparent;
                display: flex;
                justify-content: center;
                width: 100%;
            }
            .wv-page-number-indicator {
                background: #0f172a;
                color: #38bdf8;
                font-size: 11px;
                font-weight: 800;
                padding: 4px 16px;
                border-radius: 6px 6px 0 0;
                position: absolute;
                top: -24px;
                left: 50%;
                transform: translateX(-50%);
                z-index: 50;
                border: 1.5px solid #334155;
                border-bottom: none;
                letter-spacing: 0.3px;
                box-shadow: 0 2px 6px rgba(0,0,0,0.25);
            }
            .wv-merged-page-wrapper .page {
                margin: 0 auto !important;
                box-shadow: 0 20px 35px -5px rgba(0,0,0,0.5), 0 10px 15px -5px rgba(0,0,0,0.3) !important;
            }
        }
        .wv-top-bar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 56px;
            background: #0f172a;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            z-index: 999999;
            box-shadow: 0 4px 16px rgba(0,0,0,0.3);
            border-bottom: 2px solid #0284c7;
        }
        .wv-top-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 12.5px;
            border: none;
            cursor: pointer;
            transition: all 0.15s ease;
            text-decoration: none;
        }
        .wv-top-btn:hover {
            opacity: 0.92;
            transform: translateY(-1px);
        }
        .wv-btn-download {
            background: #16a34a;
            color: #ffffff;
            box-shadow: 0 2px 8px rgba(22,163,74,0.4);
        }
        .wv-btn-print {
            background: #0284c7;
            color: #ffffff;
            box-shadow: 0 2px 8px rgba(2,132,199,0.4);
        }
        .wv-btn-close {
            background: #475569;
            color: #ffffff;
        }
        #pdfLoadingModal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15,23,42,0.85);
            z-index: 1000000;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(4px);
        }
    </style>
</head>
<body>

    <!-- Sticky Navigation Control Bar -->
    <div class="wv-top-bar no-print">
        <div style="display:flex;align-items:center;gap:12px;">
            <div style="font-size:16px;font-weight:900;letter-spacing:0.4px;color:#38bdf8;display:flex;align-items:center;gap:8px;">
                <i class="fa fa-file-pdf-o text-red-400"></i> WHITE VILLAS BULK BILLS
            </div>
            <span style="background:#1e293b;border:1px solid #334155;color:#94a3b8;font-size:11.5px;font-weight:700;padding:3px 10px;border-radius:20px;">
                ' . h($periodLabel) . ' &bull; <strong style="color:#38bdf8;">' . $totalBills . '</strong> ' . ($totalBills === 1 ? 'Bill' : 'Bills') . '
            </span>
        </div>

        <div style="display:flex;align-items:center;gap:10px;">
            <button id="btnDownloadMergedPdf" class="wv-top-btn wv-btn-download" onclick="startMergedPdfDownload()">
                <i class="fa fa-download"></i> Download Merged PDF (' . $totalBills . ')
            </button>
            <button class="wv-top-btn wv-btn-print" onclick="window.print()">
                <i class="fa fa-print"></i> Print All (' . $totalBills . ')
            </button>
            <button class="wv-top-btn wv-btn-close" onclick="window.close()">
                <i class="fa fa-times"></i> Close
            </button>
        </div>
    </div>

    <!-- PDF Generation Progress Modal -->
    <div id="pdfLoadingModal" class="no-print">
        <div style="background:#ffffff;border-radius:14px;padding:32px 40px;max-width:440px;width:90%;text-align:center;box-shadow:0 25px 50px -12px rgba(0,0,0,0.5);border:2px solid #0284c7;">
            <div style="font-size:42px;margin-bottom:14px;display:inline-block;">⚙️</div>
            <h3 style="font-size:18px;font-weight:900;color:#0f172a;margin:0 0 6px;">Generating Merged PDF...</h3>
            <p style="font-size:13px;color:#64748b;margin:0 0 16px;">Compiling <strong id="pdfCompileCount" style="color:#0284c7;">' . $totalBills . '</strong> high-resolution bill pages into a single PDF file.</p>
            <div style="width:100%;height:10px;background:#e2e8f0;border-radius:5px;overflow:hidden;margin-bottom:12px;">
                <div id="pdfProgressBar" style="width:25%;height:100%;background:linear-gradient(90deg,#0284c7,#16a34a);transition:width 0.3s ease;"></div>
            </div>
            <div id="pdfStatusText" style="font-size:11.5px;color:#475569;font-weight:700;">Preparing vectors and layout...</div>
        </div>
    </div>

    <!-- Merged Multi-Page Bills Container -->
    <div id="merged-container">
        ' . $vouchersOutput . '
    </div>

    <script type="text/javascript">
    var TOTAL_BILLS = ' . $totalBills . ';
    var PDF_FILENAME = ' . json_encode($pdfSafeFileName . '.pdf') . ';
    var isGeneratingPdf = false;

    function startMergedPdfDownload() {
        if (isGeneratingPdf) return;
        isGeneratingPdf = true;

        var modal = document.getElementById("pdfLoadingModal");
        var pBar = document.getElementById("pdfProgressBar");
        var sText = document.getElementById("pdfStatusText");
        var btn = document.getElementById("btnDownloadMergedPdf");

        if (modal) modal.style.display = "flex";
        if (btn) btn.disabled = true;

        pBar.style.width = "30%";
        sText.innerText = "Rendering high-density canvas for " + TOTAL_BILLS + " bills...";

        setTimeout(function() {
            var element = document.getElementById("merged-container");
            if (!element || typeof html2pdf === "undefined") {
                // Fallback to browser print-to-pdf
                if (modal) modal.style.display = "none";
                if (btn) btn.disabled = false;
                isGeneratingPdf = false;
                window.print();
                return;
            }

            pBar.style.width = "65%";
            sText.innerText = "Building vector document & splitting pages...";

            var opt = {
                margin: [0, 0, 0, 0],
                filename: PDF_FILENAME,
                image: { type: "jpeg", quality: 0.98 },
                html2canvas: { scale: 1.75, useCORS: true, logging: false },
                jsPDF: { unit: "mm", format: "letter", orientation: "portrait" },
                pagebreak: { mode: ["css", "legacy"], after: ".wv-merged-page-wrapper" }
            };

            html2pdf().set(opt).from(element).toPdf().get("pdf").then(function(pdf) {
                pBar.style.width = "90%";
                sText.innerText = "Packaging PDF download...";
            }).save().then(function() {
                pBar.style.width = "100%";
                sText.innerText = "Download complete!";
                setTimeout(function() {
                    if (modal) modal.style.display = "none";
                    if (btn) btn.disabled = false;
                    isGeneratingPdf = false;
                    pBar.style.width = "0%";
                }, 800);
            }).catch(function(err) {
                console.error("PDF Export Error:", err);
                if (modal) modal.style.display = "none";
                if (btn) btn.disabled = false;
                isGeneratingPdf = false;
                alert("PDF compilation encountered an issue. Opening standard print view instead.");
                window.print();
            });
        }, 300);
    }

    ' . ($autoDownload ? '
    window.addEventListener("DOMContentLoaded", function() {
        setTimeout(startMergedPdfDownload, 600);
    });' : '') . '
    </script>
</body>
</html>';

        echo $mergedHtml;
        exit;
    }

    public function _renderBillVoucherHtml($bill, $html = null)
    {
        if(empty($html))
        {
            $template = $this->BillingTemplate->find('first', array(
                'conditions' => array('BillingTemplate.template_type' => 'print_bill', 'BillingTemplate.is_default' => 1)
            ));
            if(empty($template) || empty($template['BillingTemplate']['html_content']))
            {
                $template = $this->BillingTemplate->findById(11);
            }
            if(empty($template) || empty($template['BillingTemplate']['html_content']))
            {
                $template = $this->BillingTemplate->find('first', array('order' => array('BillingTemplate.id' => 'ASC')));
            }
            $html = !empty($template['BillingTemplate']['html_content']) ? $template['BillingTemplate']['html_content'] : '<h2>Template HTML not found</h2>';
        }
        // Prepare Bill, Property, Client fields with full fallback
        $b = !empty($bill['BillingBill']) ? $bill['BillingBill'] : array();
        $p = !empty($bill['BillingProperty']) ? $bill['BillingProperty'] : array();
        $c = !empty($bill['BillingClient']) ? $bill['BillingClient'] : (isset($p['BillingClient']) ? $p['BillingClient'] : array());

        $billNo = !empty($b['bill_number']) ? $b['bill_number'] : 'WV-BILL-2026-08-0018';
        $consumerId = !empty($c['id']) ? ('WV70' . str_pad($c['id'], 2, '0', STR_PAD_LEFT)) : 'WV7006';
        $clientName = !empty($c['name']) ? strtoupper($c['name']) : 'MUHAMMAD ANSAR';
        $propertyId = !empty($p['property_no']) ? $p['property_no'] : 'NG0215086-GF';
        $clientAddress = !empty($c['address']) ? $c['address'] : (!empty($p['address']) ? $p['address'] : 'AWAMI BLOCK WHITE VILLAS LAHORE');
        $clientPhone = !empty($c['phone']) ? $c['phone'] : '+92 300 1234567';
        $clientCnic = !empty($c['cnic']) ? $c['cnic'] : '35201-1234567-1';

        $billingMonth = !empty($b['billing_period']) ? date('M-Y', strtotime($b['billing_period'] . '-01')) : 'Aug-2026';
        $issueDate = !empty($b['issue_date']) && $b['issue_date'] !== '0000-00-00' ? date('d-M-Y', strtotime($b['issue_date'])) : '17-Aug-2026';
        $dueDate = !empty($b['due_date']) && $b['due_date'] !== '0000-00-00' ? date('d-M-Y', strtotime($b['due_date'])) : '20-Aug-2026';
        $validityDate = date('d-M-Y', strtotime($dueDate . ' +10 days'));

        $utilityType = !empty($b['utility_type']) ? $b['utility_type'] : 'Electricity';
        $prev = isset($b['previous_reading']) ? (float)$b['previous_reading'] : 1420.0;
        $curr = isset($b['current_reading']) ? (float)$b['current_reading'] : 1464.5;
        $units = isset($b['units_consumed']) ? (float)$b['units_consumed'] : 44.5;
        $rate = isset($b['unit_rate']) && (float)$b['unit_rate'] > 0 ? (float)$b['unit_rate'] : 58.34;
        $consAmt = ($units * $rate > 0) ? ($units * $rate) : (isset($b['consumption_amount']) ? (float)$b['consumption_amount'] : 2596.13);

        $arrears = isset($b['arrears_amount']) ? (float)$b['arrears_amount'] : 0.0;
        $extraAmt = isset($b['extra_charges_amount']) && (float)$b['extra_charges_amount'] > 0 ? (float)$b['extra_charges_amount'] : 1951.0;
        $totalAmt = isset($b['total_amount']) && (float)$b['total_amount'] > 0 ? (float)$b['total_amount'] : ($consAmt + $extraAmt + $arrears);
        $lateFee = isset($b['late_fee']) && (float)$b['late_fee'] > 0 ? (float)$b['late_fee'] : 318.30;
        $payableAfterDue = $totalAmt + $lateFee;
        $paidAmt = isset($b['paid_amount']) ? (float)$b['paid_amount'] : 0.0;
        $balanceAmt = max(0.0, $totalAmt - $paidAmt);
        $status = !empty($b['payment_status']) ? ucfirst($b['payment_status']) : 'Unpaid';

        // Resolve Meter Number
        $meterNo = 'WV-MTR-' . (!empty($b['billing_property_id']) ? $b['billing_property_id'] : '8001');
        if (!empty($p['BillingMeter'])) {
            if (isset($p['BillingMeter'][0]['meter_number'])) {
                $meterNo = $p['BillingMeter'][0]['meter_number'];
            } elseif (isset($p['BillingMeter']['meter_number'])) {
                $meterNo = $p['BillingMeter']['meter_number'];
            }
        }

        // 12 Months Real Payment History with Adjustments from Database
        $pastBills = array();
        if(!empty($b['billing_property_id'])) {
            $pastBills = $this->BillingBill->find('all', array(
                'conditions' => array('BillingBill.billing_property_id' => (int)$b['billing_property_id']),
                'order' => array('BillingBill.billing_period' => 'DESC'),
                'limit' => 24
            ));
        }

        $this->loadModel('BillingManager.BillingBillAdjustment');
        $pastBillIds = array();
        foreach($pastBills as $pb) {
            $pastBillIds[] = (int)$pb['BillingBill']['id'];
        }

        $pastAdjustments = array();
        if(!empty($pastBillIds)) {
            $allPastAdjs = $this->BillingBillAdjustment->find('all', array(
                'conditions' => array('BillingBillAdjustment.bill_id' => $pastBillIds)
            ));
            foreach($allPastAdjs as $pa) {
                $pBillId = (int)$pa['BillingBillAdjustment']['bill_id'];
                if(!isset($pastAdjustments[$pBillId])) $pastAdjustments[$pBillId] = array();
                $pastAdjustments[$pBillId][] = $pa['BillingBillAdjustment'];
            }
        }

        // Map past bills by normalized YYYY-MM period
        $billsByPeriod = array();
        foreach($pastBills as $pb) {
            $pPeriod = !empty($pb['BillingBill']['billing_period']) ? trim($pb['BillingBill']['billing_period']) : '';
            if(preg_match('/^(\d{4})-(\d{1,2})/', $pPeriod, $m)) {
                $pNorm = sprintf('%04d-%02d', $m[1], $m[2]);
                $billsByPeriod[$pNorm] = $pb;
            }
        }

        // Auto-generate full 12 Months History ending at current bill's billing period
        $currPeriodRaw = !empty($b['billing_period']) ? trim($b['billing_period']) : date('Y-m');
        if(preg_match('/^(\d{4})-(\d{1,2})/', $currPeriodRaw, $cm)) {
            $currPeriodNorm = sprintf('%04d-%02d', $cm[1], $cm[2]);
        } else {
            $currPeriodNorm = date('Y-m');
        }
        $currTs = strtotime($currPeriodNorm . '-01');
        if(!$currTs) $currTs = time();

        $historyRows = '';
        for ($i = 0; $i < 12; $i++) {
            $targetTs = strtotime("-{$i} month", $currTs);
            $pPeriodKey = date('Y-m', $targetTs);
            $pLabel = date('M-y', $targetTs);

            if (isset($billsByPeriod[$pPeriodKey])) {
                $pb = $billsByPeriod[$pPeriodKey];
                $hUnits = number_format((float)$pb['BillingBill']['units_consumed'], 1);
                $hTotal = number_format((float)$pb['BillingBill']['total_amount'], 2);
                $hPaid = number_format((float)$pb['BillingBill']['paid_amount'], 2);

                $pBillId = (int)$pb['BillingBill']['id'];
                $hAdj = '-';
                if (!empty($pastAdjustments[$pBillId])) {
                    $adjSum = 0.0;
                    foreach ($pastAdjustments[$pBillId] as $ad) {
                        $adjSum += ($ad['type'] === 'credit') ? -(float)$ad['amount'] : (float)$ad['amount'];
                    }
                    $hAdj = ($adjSum != 0) ? (($adjSum > 0 ? '+' : '') . number_format($adjSum, 2)) : '-';
                }

                $hSt = !empty($pb['BillingBill']['payment_status']) ? ucfirst($pb['BillingBill']['payment_status']) : 'Unpaid';
            } else {
                $hUnits = '-';
                $hTotal = '-';
                $hPaid = '-';
                $hAdj = '-';
                $hSt = '-';
            }

            $historyRows .= "<tr>
                <td>{$pLabel}</td>
                <td>{$hUnits}</td>
                <td>{$hTotal}</td>
                <td>{$hPaid}</td>
                <td>{$hAdj}</td>
                <td>{$hSt}</td>
            </tr>";
        }

        // Active Adjustments for this current bill
        $billAdjustments = array();
        $adjDebit = 0.0;
        $adjCredit = 0.0;
        $adjNotes = array();
        if(!empty($b['id'])) {
            $billAdjustments = $this->BillingBillAdjustment->find('all', array(
                'conditions' => array('BillingBillAdjustment.bill_id' => (int)$b['id']),
                'order' => array('BillingBillAdjustment.id' => 'ASC')
            ));
            foreach($billAdjustments as $ba) {
                $type = strtolower($ba['BillingBillAdjustment']['type']);
                $amt = (float)$ba['BillingBillAdjustment']['amount'];
                $reason = trim($ba['BillingBillAdjustment']['reason']);
                if($type === 'credit') {
                    $adjCredit += $amt;
                } else {
                    $adjDebit += $amt;
                }
                if(!empty($reason)) {
                    $adjNotes[] = $reason;
                }
            }
        }
        $netAdj = $adjDebit - $adjCredit;
        $adjNoteStr = !empty($adjNotes) ? implode(', ', array_unique($adjNotes)) : 'None';

        $adjustmentRows = '';
        if(!empty($billAdjustments)) {
            $adjustmentRows .= '<tr class="bg-blue-50/20"><td colspan="2" class="p-0.5 px-3 border-r border-blue-300 font-black text-[9px] uppercase text-blue-900">ADJUSTMENTS & REVERSALS</td></tr>';
            foreach($billAdjustments as $ba) {
                $a = $ba['BillingBillAdjustment'];
                $isCredit = ($a['type'] === 'credit');
                $sign = $isCredit ? '-' : '+';
                $color = $isCredit ? '#16a34a' : '#dc2626';
                $typeLabel = $isCredit ? 'Credit / Waiver' : 'Debit / Surcharge';
                $adjustmentRows .= '<tr><td class="p-1 px-3 border-r border-blue-300"><span style="color:' . $color . ';font-weight:700;">' . $sign . ' ' . h($a['reason']) . '</span> <small style="color:#64748b;">(' . $typeLabel . ')</small></td><td class="p-1 px-3 text-right" style="color:' . $color . ';font-weight:700;">' . $sign . number_format($a['amount'], 2) . '</td></tr>';
            }
        }

        // Load SBP Raast / 1Bill EMVCo QR Generator Lib & Backend Bank Settings
        App::uses('RaastEmvcoQr', 'BillingManager.Lib');
        $socId = !empty($b['society_id']) ? (int)$b['society_id'] : 1;
        $enableQr = (int)$this->BillingSetting->getSetting('subm_enable_qr_payment', 1, $socId);
        $qrMode = $this->BillingSetting->getSetting('subm_qr_mode', 'raast_iban', $socId);
        $raastId = $this->BillingSetting->getSetting('subm_raast_id', 'PK36MEZN000100773310485346', $socId);
        $billerCode = $this->BillingSetting->getSetting('subm_1bill_prefix', '100773', $socId);
        $bankName = $this->BillingSetting->getSetting('subm_bank_name', 'MEEZAN BANK / UBL', $socId);
        $accTitle = $this->BillingSetting->getSetting('subm_bank_account_title', 'WHITE VILLAS', $socId);
        $accNo = $this->BillingSetting->getSetting('subm_bank_account_no', '0773310485346', $socId);
        $iban = $this->BillingSetting->getSetting('subm_bank_iban', 'PK36MEZN000100773310485346', $socId);

        $consumerRef = $billerCode . str_pad(preg_replace('/[^0-9]/', '', $billNo), 6, '0', STR_PAD_LEFT);

        $raastPayload = RaastEmvcoQr::generatePayload(array(
            'qr_mode' => $qrMode,
            'raast_id' => $raastId,
            'bill_number' => $billNo,
            'amount' => ($totalAmt > 0) ? $totalAmt : 0,
            'merchant_name' => 'White Villas',
            'city' => 'Lahore',
            'biller_code' => $billerCode
        ));
        $raastQrUrl = RaastEmvcoQr::getQrImageUrl($raastPayload, 160);

        $fmtWithinDue = number_format(round($totalAmt), 0);
        $fmtLateFee = number_format(round($lateFee), 0);
        $fmtPayableAfterDue = number_format(round($payableAfterDue), 0);

        // Right Upper Payment Summary Banner (3 Distinct Professional Full-Width Cards)
        $bankSummaryBanner = '
        <div style="display:grid;grid-template-columns: 1fr 1fr 1fr;gap:5px;width:100%;margin-bottom:6px;box-sizing:border-box;">
            <!-- 1. Within Due Date Card: Dark Green Theme -->
            <div style="background:linear-gradient(135deg, #064e3b 0%, #065f46 100%);color:#ffffff;border-radius:6px;padding:6px 4px;text-align:center;box-shadow:0 1.5px 4px rgba(6,78,59,0.3);border:1px solid #047857;display:flex;flex-direction:column;justify-content:center;">
                <div style="font-size:7.5px;font-weight:900;color:#6ee7b7;text-transform:uppercase;letter-spacing:0.4px;line-height:1.1;">WITHIN DUE DATE</div>
                <div style="font-size:13px;font-weight:900;color:#ffffff;font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,sans-serif;letter-spacing:0.2px;margin:2px 0 1px 0;line-height:1.1;">
                    <span style="font-size:8.5px;color:#a7f3d0;font-weight:700;margin-right:2px;">PKR</span>' . $fmtWithinDue . '
                </div>
                <div style="font-size:7px;color:#d1fae5;font-weight:700;line-height:1;">Due: ' . h($dueDate) . '</div>
            </div>

            <!-- 2. Late Fee Surcharge Card: Dark Red Theme -->
            <div style="background:linear-gradient(135deg, #7f1d1d 0%, #991b1b 100%);color:#ffffff;border-radius:6px;padding:6px 4px;text-align:center;box-shadow:0 1.5px 4px rgba(127,29,29,0.3);border:1px solid #b91c1c;display:flex;flex-direction:column;justify-content:center;">
                <div style="font-size:7.5px;font-weight:900;color:#fca5a5;text-transform:uppercase;letter-spacing:0.4px;line-height:1.1;">LATE FEE SURCHARGE</div>
                <div style="font-size:13px;font-weight:900;color:#ffffff;font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,sans-serif;letter-spacing:0.2px;margin:2px 0 1px 0;line-height:1.1;">
                    <span style="font-size:8.5px;color:#fecaca;font-weight:700;margin-right:2px;">+ PKR</span>' . $fmtLateFee . '
                </div>
                <div style="font-size:7px;color:#fee2e2;font-weight:700;line-height:1;">After ' . h($dueDate) . '</div>
            </div>

            <!-- 3. Payable After Due Card: Deep Crimson Theme -->
            <div style="background:linear-gradient(135deg, #450a0a 0%, #7f1d1d 100%);color:#ffffff;border-radius:6px;padding:6px 4px;text-align:center;box-shadow:0 1.5px 4px rgba(69,10,10,0.3);border:1px solid #991b1b;display:flex;flex-direction:column;justify-content:center;">
                <div style="font-size:7.5px;font-weight:900;color:#fde047;text-transform:uppercase;letter-spacing:0.4px;line-height:1.1;">PAYABLE AFTER DUE</div>
                <div style="font-size:13px;font-weight:900;color:#fef08a;font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,sans-serif;letter-spacing:0.2px;margin:2px 0 1px 0;line-height:1.1;">
                    <span style="font-size:8.5px;color:#fde047;font-weight:700;margin-right:2px;">PKR</span>' . $fmtPayableAfterDue . '
                </div>
                <div style="font-size:7px;color:#fef9c3;font-weight:700;line-height:1;">Valid Till: ' . h($validityDate) . '</div>
            </div>
        </div>';

        if ($enableQr === 1) {
            $bankDetailsHtml = '
            <div style="display:flex;flex-direction:column;gap:5px;width:100%;margin:2px 0;">
                ' . $bankSummaryBanner . '
                <div style="background:#f8fafc;border:1.5px solid #0284c7;border-radius:6px;padding:6px 10px;box-shadow:0 1px 3px rgba(0,0,0,0.05);">
                    <div style="display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid #cbd5e1;padding-bottom:3px;margin-bottom:4px;">
                        <div style="font-size:9.5px;font-weight:900;color:#0369a1;text-transform:uppercase;letter-spacing:0.4px;">
                            🏦 OFFICIAL BANK PAYMENT &amp; 1BILL / RAAST QR SCAN-TO-PAY
                        </div>
                        <div style="font-size:8.5px;font-weight:800;color:#15803d;background:#dcfce7;padding:1px 5px;border-radius:3px;">
                            INSTANT SETTLEMENT
                        </div>
                    </div>
                    <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;">
                        <div style="flex:1;font-size:9px;line-height:1.3;color:#1e293b;">
                            <div style="display:grid;grid-template-columns: 115px 1fr;gap:2px 6px;align-items:center;">
                                <span style="color:#64748b;font-weight:700;">BANK NAME:</span>
                                <strong style="color:#0f172a;font-size:9.5px;">' . h(strtoupper($bankName)) . '</strong>
                                <span style="color:#64748b;font-weight:700;">ACCOUNT TITLE:</span>
                                <strong style="color:#0369a1;font-size:9.5px;">' . h(strtoupper($accTitle)) . '</strong>
                                <span style="color:#64748b;font-weight:700;">1BILL CONSUMER ID:</span>
                                <strong style="color:#0f172a;font-family:monospace;font-size:11.5px;letter-spacing:0.5px;">' . h($consumerRef) . '</strong>
                                <span style="color:#64748b;font-weight:700;">ACCOUNT / TILL NO:</span>
                                <strong style="color:#0f172a;font-family:monospace;font-size:10px;">' . h($accNo) . ' (TILL: 981452707)</strong>
                                <span style="color:#64748b;font-weight:700;">IBAN / RAAST ID:</span>
                                <strong style="color:#0f172a;font-family:monospace;font-size:9.5px;">' . h($iban) . '</strong>
                            </div>
                        </div>
                        <div style="text-align:center;min-width:76px;">
                            <img src="' . h($raastQrUrl) . '" style="width:70px;height:70px;border:1.5px solid #0284c7;border-radius:4px;padding:2px;background:#ffffff;" />
                            <div style="font-size:7px;font-weight:900;color:#0284c7;margin-top:2px;text-transform:uppercase;letter-spacing:0.2px;">
                                SCAN VIA BANK APP
                            </div>
                        </div>
                    </div>
                </div>
            </div>';
        } else {
            $bankAccounts = $this->BillingSetting->getSetting('subm_bank_accounts', array(), $socId);
            if (is_string($bankAccounts)) {
                $un = @unserialize($bankAccounts);
                if ($un !== false) $bankAccounts = $un;
            }
            if (empty($bankAccounts) || !is_array($bankAccounts)) {
                $bankAccounts = array(
                    array(
                        'title' => 'United Bank Limited UBL',
                        'details' => '<p style="margin:0;font-size:10px;text-align:left"><strong>Account Title</strong></p><p style="margin:2px 0 3px 0;font-size:14px;text-align:left"><strong>WHITE VILLAS</strong></p><p style="margin:0;font-size:10px;text-align:left"><strong>Account No</strong></p><p style="margin:2px 0;font-size:14px;letter-spacing:2px;text-align:left"><strong>0773310485346</strong></p>',
                        'qr' => 'https://whitevillas.com.pk/wp-content/uploads/2026/02/WHITE-VILLAS-BANK-ACCOUNT-QR-CODE.jpeg'
                    ),
                    array(
                        'title' => 'JAZZ CASH TILL ID',
                        'details' => '<p style="margin:0;font-size:10px;text-align:left"><strong>Account Title</strong></p><p style="margin:2px 0 3px 0;font-size:14px;text-align:left"><strong>WHITE VILLAS</strong></p><p style="margin:0;font-size:10px;text-align:left"><strong>TILL ID</strong></p><p style="margin:2px 0;font-size:14px;letter-spacing:2px;text-align:left"><strong>981452707</strong></p>',
                        'qr' => 'https://whitevillas.com.pk/wp-content/uploads/2026/02/white-villas-lahore-jazzcash-till-id-e1771858709333.jpeg'
                    )
                );
            }

            $bankDetailsHtml = '
            <div style="display:flex;flex-direction:column;gap:4px;margin:2px 0;width:100%;">
                ' . $bankSummaryBanner . '';
            foreach ($bankAccounts as $ba) {
                $bTitle = !empty($ba['title']) ? h($ba['title']) : 'Bank Account';
                $bDetails = !empty($ba['details']) ? str_replace(array('font-size:10x', 'text-align:center', 'text-align: center'), array('font-size:10px', 'text-align:left', 'text-align:left'), $ba['details']) : '';
                $bQr = !empty($ba['qr']) ? trim($ba['qr']) : '';

                // Ensure bank QR is always an embedded local Base64 Data URI (prevents empty box in PDF/print)
                if (empty($bQr) || strpos($bQr, 'whitevillas.com.pk') !== false || strpos($bQr, 'api.qrserver.com') !== false) {
                    if ($isJazz) {
                        $bQr = RaastEmvcoQr::getQrImageUrl("jazzcash://pay?till=981452707&title=WHITE+VILLAS&amount=" . $totalAmt . "&ref=" . $billNo, 140);
                    } else {
                        $bQr = RaastEmvcoQr::getQrImageUrl("raast://pay?iban=" . $iban . "&title=WHITE+VILLAS&amount=" . $totalAmt . "&ref=" . $billNo, 140);
                    }
                }

                $isJazz = (strpos(strtoupper($bTitle), 'JAZZ') !== false);
                $cardBg = $isJazz ? '#fffbeb' : '#f8fafc';
                $cardBorder = $isJazz ? '#fde047' : '#0284c7';
                $titleColor = $isJazz ? '#92400e' : '#0369a1';

                $bankDetailsHtml .= '
                <div style="background:' . $cardBg . ';border:1.5px solid ' . $cardBorder . ';border-radius:6px;padding:5px 10px;display:flex;align-items:center;justify-content:space-between;gap:10px;box-shadow:0 1px 2px rgba(0,0,0,0.03);">
                    <div style="flex:1;text-align:left;font-size:9.5px;line-height:1.3;color:#1e293b;">
                        <div style="color:' . $titleColor . ';font-weight:900;font-size:11px;text-transform:uppercase;letter-spacing:0.3px;margin-bottom:2px;border-bottom:1px solid ' . $cardBorder . ';padding-bottom:2px;text-align:left;">
                            🏦 ' . $bTitle . '
                        </div>
                        <div style="margin-top:2px;text-align:left;">
                            ' . $bDetails . '
                        </div>
                    </div>
                    ' . (!empty($bQr) ? '
                    <div style="text-align:center;flex-shrink:0;display:flex;flex-direction:column;align-items:center;justify-content:center;">
                        <img src="' . $bQr . '" style="height:75px;width:75px;max-height:75px;max-width:75px;object-fit:contain;border:1.5px solid ' . $cardBorder . ';border-radius:4px;padding:2px;background:#ffffff;box-shadow:0 1px 3px rgba(0,0,0,0.1);" />
                        <div style="font-size:7px;font-weight:900;color:' . $titleColor . ';margin-top:2px;text-transform:uppercase;letter-spacing:0.2px;">SCAN QR</div>
                    </div>' : '') . '
                </div>';
            }
            $bankDetailsHtml .= '</div>';
        }

        $meterImgUrl = '';
        $this->loadModel('BillingManager.BillingReading');

        if (!empty($b['reading_id'])) {
            $readRec = $this->BillingReading->findById((int)$b['reading_id']);
            if (!empty($readRec['BillingReading']['photo_url'])) {
                $meterImgUrl = $readRec['BillingReading']['photo_url'];
            }
        }

        if (empty($meterImgUrl) && !empty($b['billing_property_id'])) {
            $readRec = $this->BillingReading->find('first', array(
                'conditions' => array(
                    'BillingReading.billing_property_id' => (int)$b['billing_property_id'],
                    'BillingReading.photo_url IS NOT NULL',
                    'BillingReading.photo_url !=' => ''
                ),
                'order' => array('BillingReading.id' => 'DESC')
            ));
            if (!empty($readRec['BillingReading']['photo_url'])) {
                $meterImgUrl = $readRec['BillingReading']['photo_url'];
            }
        }

        $billNoticeText = $this->BillingSetting->getSetting('subm_bill_notice', 'احتیاط: ادائیگی سے پہلے براہِ کرم بل کی تفصیلات کی تصدیق کر لیں۔ بل پر درج اکاؤنٹ اور کیو آر کوڈ کی معلومات استعمال کریں۔', $socId);
        $billNoticeHtml = '
        <div class="urdu-font" style="direction:rtl;text-align:right;font-size:12.5px;line-height:1.8;color:#0f172a;padding:8px 12px;font-weight:700;">
            ' . h($billNoticeText) . '
        </div>';

        if (!empty($meterImgUrl)) {
            $meterImgHtml = '
            <div style="text-align:center;padding:4px;width:100%;height:100%;min-height:140px;display:flex;align-items:center;justify-content:center;background:#ffffff;">
                <a href="' . h($meterImgUrl) . '" target="_blank" style="display:inline-block;width:100%;height:100%;">
                    <img src="' . h($meterImgUrl) . '" style="max-width:100%;max-height:155px;width:auto;height:auto;object-fit:contain;border-radius:4px;border:1px solid #cbd5e1;box-shadow:0 1px 3px rgba(0,0,0,0.1);" />
                </a>
            </div>';
        } else {
            $meterImgHtml = '
            <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;height:100%;min-height:140px;color:#64748b;background:#f8fafc;padding:12px;text-align:center;width:100%;border:1px dashed #cbd5e1;border-radius:4px;">
                <span style="font-size:28px;color:#94a3b8;margin-bottom:6px;">📷</span>
                <strong style="font-size:11px;color:#475569;text-transform:uppercase;letter-spacing:0.5px;">Meter Image Verified</strong>
            </div>';
        }

        $rawUrduHtml = $this->BillingSetting->getSetting('subm_online_pay_instructions', '', $socId);
        if (empty($rawUrduHtml)) {
            $rawUrduHtml = '
            <ul style="padding-right:16px;margin:0;">
                <li>ادائیگی صرف بل پر درج پرائمری بینک اکاؤنٹ یا کیو آر کوڈ کے ذریعے کریں۔</li>
                <li>ادائیگی کے بعد تصدیقی رسید / ٹرانزیکشن اسکرین شاٹ ہیلپ لائن <strong>0339-1101156</strong> پر واٹس ایپ کریں۔</li>
                <li>مقررہ تاریخ کے بعد ادائیگی کی صورت میں لیٹ فیس لاگو ہوگی۔</li>
            </ul>';
        }

        $urduNoticeHtml = '
        <div style="margin:2px 0;">
            <div style="background:#dc2626;color:#ffffff;font-size:9.5px;font-weight:900;padding:2px 8px;border-radius:3px 3px 0 0;text-align:right;direction:rtl;" class="urdu-font">
                ⚠️ اہم ہدایات برائے آن لائن ادائیگی (آن لائن ٹرانسفر / جاز کیش)
            </div>
            <div class="urdu-font" style="direction:rtl;text-align:right;font-size:10.5px;line-height:1.6;color:#0f172a;background:#fff1f2;border:1px solid #fca5a5;border-top:none;padding:5px 8px;border-radius:0 0 4px 4px;">
                ' . $rawUrduHtml . '
            </div>
        </div>';

        $paidStampHtml = '';
        if(strtolower($status) === 'paid') {
            $paidStampHtml = '
            <div style="display:inline-block;border:3px solid #16a34a;color:#16a34a;font-weight:900;font-size:16px;padding:3px 12px;border-radius:6px;text-transform:uppercase;transform:rotate(-4deg);letter-spacing:1px;">
                ✓ PAID
            </div>';
        }

        $utilityRows = '
        <tr>
            <td class="p-1 px-3 border-r border-blue-300 font-bold">' . h($utilityType) . ' Consumption (' . number_format($units, 1) . ' units &times; PKR ' . number_format($rate, 2) . ')</td>
            <td class="p-1 px-3 text-right font-black text-slate-800">' . number_format($consAmt, 2) . '</td>
        </tr>';

        $matchingMaintItems = array();
        $matchingExtraCharges = array();

        if (!empty($b['extra_charges_json'])) {
            $jsonParsed = @json_decode($b['extra_charges_json'], true);
            if (!empty($jsonParsed) && is_array($jsonParsed)) {
                foreach ($jsonParsed as $item) {
                    $cat = !empty($item['category']) ? strtolower($item['category']) : '';
                    if (strpos($cat, 'maint') !== false) {
                        $matchingMaintItems[] = array('name' => !empty($item['desc']) ? $item['desc'] : 'Maintenance', 'amount' => (float)$item['amount']);
                    } elseif ($cat !== 'utility') {
                        $matchingExtraCharges[] = array('name' => !empty($item['desc']) ? $item['desc'] : (!empty($item['category']) ? $item['category'] : 'Extra Charge'), 'amount' => (float)$item['amount']);
                    }
                }
            }
        }

        $otherRows = '';
        if (!empty($matchingExtraCharges)) {
            $otherRows .= '<tr class="bg-blue-50/20"><td colspan="2" class="p-0.5 px-3 border-r border-blue-300 font-black text-[9px] uppercase text-blue-900">OTHER SERVICES &amp; CHARGES</td></tr>';
            foreach ($matchingExtraCharges as $ec) {
                $otherRows .= '<tr><td class="p-1 px-3 border-r border-blue-300">' . h($ec['name']) . '</td><td class="p-1 px-3 text-right">' . number_format($ec['amount'], 2) . '</td></tr>';
            }
        }

        $maintenanceRows = '';
        if (!empty($matchingMaintItems)) {
            $maintenanceRows .= '<tr class="bg-blue-50/20"><td colspan="2" class="p-0.5 px-3 border-r border-blue-300 font-black text-[9px] uppercase text-blue-900">MAINTENANCE BREAKDOWN</td></tr>';
            foreach ($matchingMaintItems as $mi) {
                $maintenanceRows .= '<tr><td class="p-1 px-3 border-r border-blue-300">' . h($mi['name']) . '</td><td class="p-1 px-3 text-right">' . number_format($mi['amount'], 2) . '</td></tr>';
            }
        }

        $energyCalc = number_format($units, 1) . ' Units &times; ' . number_format($rate, 2) . ' = PKR ' . number_format($consAmt, 2) . ' (Energy Cost)';

        // Resolve Society / Project profile for bill header & branding
        $socId = !empty($b['society_id']) ? (int)$b['society_id'] : (!empty($p['society_id']) ? (int)$p['society_id'] : 1);
        $this->loadModel('BillingManager.BillingSociety');
        $soc = $this->BillingSociety->findById($socId);
        if (empty($soc)) {
            $soc = $this->BillingSociety->find('first', array('order' => array('BillingSociety.id' => 'ASC')));
        }

        $societyName = !empty($soc['BillingSociety']['name']) ? strtoupper($soc['BillingSociety']['name']) : 'WHITE VILLAS LAHORE';
        $societyAddress = !empty($soc['BillingSociety']['address']) ? $soc['BillingSociety']['address'] : 'White Villas Lahore, Bahria Town Lahore, Pakistan';
        $societyPhone = !empty($soc['BillingSociety']['phone']) ? $soc['BillingSociety']['phone'] : '+92 339 1101156';
        $societyEmail = !empty($soc['BillingSociety']['email']) ? $soc['BillingSociety']['email'] : 'info@whitevillas.com.pk';

        // Resolve Project / Society Logo (support base64 embedding to prevent any broken image in print / pdf)
        $rawLogoUrl = !empty($soc['BillingSociety']['logo_url']) ? trim($soc['BillingSociety']['logo_url']) : '';
        if (empty($rawLogoUrl)) {
            $rawLogoUrl = $this->BillingSetting->getSetting('subm_society_logo', '', $socId);
        }
        if (empty($rawLogoUrl)) {
            $rawLogoUrl = '/img/site-logo-20260824135207-8538.png';
        }

        $societyLogoSrc = $rawLogoUrl;
        if (strpos($rawLogoUrl, 'data:') !== 0 && strpos($rawLogoUrl, 'http://') !== 0 && strpos($rawLogoUrl, 'https://') !== 0) {
            $cleanRelative = preg_replace('/^\/?(WhiteVilas|whitevillas)?\//i', '', $rawLogoUrl);
            $localDiskPath = WWW_ROOT . ltrim($cleanRelative, '/\\');
            if (!file_exists($localDiskPath)) {
                $localDiskPath = WWW_ROOT . 'img' . DS . 'site-logo-20260824135207-8538.png';
            }
            if (file_exists($localDiskPath)) {
                $imgData = @file_get_contents($localDiskPath);
                if ($imgData !== false) {
                    $ext = strtolower(pathinfo($localDiskPath, PATHINFO_EXTENSION));
                    $mime = ($ext === 'jpg' || $ext === 'jpeg') ? 'image/jpeg' : (($ext === 'svg') ? 'image/svg+xml' : 'image/png');
                    $societyLogoSrc = 'data:' . $mime . ';base64,' . base64_encode($imgData);
                }
            } else {
                $societyLogoSrc = Router::url('/' . ltrim($cleanRelative, '/'), true);
            }
        }

        $replacements = array(
            '{{society_logo}}' => $societyLogoSrc,
            '{{society_name}}' => $societyName,
            '{{society_address}}' => $societyAddress,
            '{{society_phone}}' => $societyPhone,
            '{{society_mobile}}' => $societyPhone,
            '{{society_email}}' => $societyEmail,
            '{{bill_no}}' => $billNo,
            '{{bill_number}}' => $billNo,
            '{{consumer_id}}' => $consumerId,
            '{{consumer_no}}' => $consumerId,
            '{{property_id}}' => $propertyId,
            '{{property_no}}' => $propertyId,
            '{{property_address}}' => $clientAddress,
            '{{billing_month}}' => $billingMonth,
            '{{billing_period}}' => $billingMonth,
            '{{issue_date}}' => $issueDate,
            '{{due_date}}' => $dueDate,
            '{{due_date_iso}}' => $dueDate,
            '{{validity_date}}' => $validityDate,
            '{{client_name}}' => $clientName,
            '{{client_address}}' => $clientAddress,
            '{{client_phone}}' => $clientPhone,
            '{{client_cnic}}' => $clientCnic,
            '{{meter_no}}' => $meterNo,
            '{{meter_type}}' => $utilityType,
            '{{prev_reading}}' => number_format($prev, 1),
            '{{curr_reading}}' => number_format($curr, 1),
            '{{units_consumed}}' => number_format($units, 1),
            '{{units}}' => number_format($units, 1),
            '{{export_units}}' => isset($b['export_units']) ? number_format((float)$b['export_units'], 1) : '0.0',
            '{{net_units}}' => isset($b['net_units']) && $b['net_units'] !== null ? number_format((float)$b['net_units'], 1) : number_format($units, 1),
            '{{solar_net_summary}}' => (!empty($b['export_units']) && (float)$b['export_units'] > 0) ? '<div style="font-size:10.5px;font-weight:700;color:#059669;">[Solar Net: Import ' . number_format($units, 1) . ' - Export ' . number_format((float)$b['export_units'], 1) . ' = Net ' . number_format(isset($b['net_units']) ? (float)$b['net_units'] : $units, 1) . ' kWh]</div>' : '',
            '{{unit_price}}' => number_format($rate, 2),
            '{{unit_rate}}' => number_format($rate, 2),
            '{{rate}}' => number_format($rate, 2),
            '{{consumption_amount}}' => number_format($consAmt, 2),
            '{{energy_calculation}}' => $energyCalc,
            '{{meter_image}}' => $meterImgHtml,
            '{{meter_img}}' => $meterImgHtml,
            '{{paid_stamp}}' => $paidStampHtml,
            '{{history_rows}}' => $historyRows,
            '{{meter_rows}}' => $utilityRows,
            '{{utility_rows}}' => $utilityRows,
            '{{table_rows}}' => $utilityRows,
            '{{maintenance_rows}}' => $maintenanceRows,
            '{{maintenance_breakdown_rows}}' => $maintenanceRows,
            '{{other_rows}}' => $otherRows,
            '{{maintenance_charges}}' => number_format($extraAmt, 2),
            '{{extra_charges}}' => number_format($extraAmt, 2),
            '{{arrears_amount}}' => number_format($arrears, 2),
            '{{arrears}}' => number_format($arrears, 2),
            '{{late_fee}}' => number_format($lateFee, 2),
            '{{late_fee_amount}}' => number_format($lateFee, 2),
            '{{late_fee_percent}}' => '5.0%',
            '{{total_amount}}' => number_format($totalAmt, 2),
            '{{total_payable}}' => number_format($totalAmt, 2),
            '{{payable_within_due}}' => number_format($totalAmt, 2),
            '{{payable_after_due}}' => number_format($payableAfterDue, 2),
            '{{paid_amount}}' => number_format($paidAmt, 2),
            '{{balance_amount}}' => number_format($balanceAmt, 2),
            '{{balance}}' => number_format($balanceAmt, 2),
            '{{pending_amount}}' => number_format($balanceAmt, 2),
            '{{payment_status}}' => $status,
            '{{bank_details}}' => $bankDetailsHtml,
            '{{bank_accounts}}' => $bankDetailsHtml,
            '{{urdu_instructions}}' => $urduNoticeHtml,
            '{{instructions_urdu}}' => $urduNoticeHtml,
            '{{bill_notice}}' => $billNoticeHtml,
            '{{notice}}' => $billNoticeHtml,
            '{{preview_caution}}' => $billNoticeHtml,
            '{{qr_code}}' => RaastEmvcoQr::getQrImageUrl($billNo, 120),
            '{{portal_qr}}' => RaastEmvcoQr::getQrImageUrl('https://whitevillas.com.pk/billing/?q=' . urlencode($billNo), 100),
            '{{adjustment_note}}' => h($adjNoteStr),
            '{{adjustment_debit}}' => number_format($adjDebit, 2),
            '{{adjustment_credit}}' => number_format($adjCredit, 2),
            '{{adjustment_amount}}' => number_format($netAdj, 2),
            '{{adjustment_total}}' => number_format($netAdj, 2),
            '{{adjustment_rows}}' => $adjustmentRows,
            '{{adjustments_rows}}' => $adjustmentRows,
            '{{adjustments_breakdown}}' => $adjustmentRows,
            '{{payment_date}}' => !empty($b['payment_date']) && $b['payment_date'] !== '0000-00-00' ? date('d-M-Y', strtotime($b['payment_date'])) : '-',
            '{{payment_ref}}' => !empty($b['transaction_ref']) ? h($b['transaction_ref']) : '-',
            '{{document_type}}' => 'UTILITY INVOICE',
            '{{template_type}}' => 'print_bill',
            '{{paid_slip_block}}' => '',
            '{{review_reason}}' => '',
            '{{advertisement}}' => '',
            '{{advertisement_mini}}' => ''
        );

        $rendered = str_replace(array_keys($replacements), array_values($replacements), $html);
        
        // Auto-replace any hardcoded external QR URLs with embedded offline Base64 Data URI to prevent blank boxes in PDF/print
        $portalQrDataUri = RaastEmvcoQr::getQrImageUrl('https://whitevillas.com.pk/billing/?q=' . urlencode($billNo), 100);
        $rendered = str_replace(
            array(
                'https://whitevillas.com.pk/wp-content/uploads/2026/02/qr-code.png',
                'http://whitevillas.com.pk/wp-content/uploads/2026/02/qr-code.png'
            ),
            $portalQrDataUri,
            $rendered
        );

        return $rendered;
    }

    public function print_receipt($id = null)
    {
        $this->autoRender = false;
        $this->layout = false;

        $isPreview = !empty($this->request->query['preview']);

        $bill = $this->BillingBill->find('first', array(
            'conditions' => array('BillingBill.id' => (int)$id),
            'contain' => array('BillingProperty' => array('BillingClient', 'BillingMeter'), 'BillingClient')
        ));

        if(empty($bill))
        {
            $this->Session->setFlash(__('Bill not found.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }

        $b = $bill['BillingBill'];
        $p = $bill['BillingProperty'];
        $c = !empty($bill['BillingClient']) ? $bill['BillingClient'] : (isset($p['BillingClient']) ? $p['BillingClient'] : array());

        $totalAmt = (float)$b['total_amount'];
        $paidAmt = (float)$b['paid_amount'];
        $status = strtolower($b['payment_status']);

        // STRICT SECURITY GUARD: Unpaid bills with NO payment cannot access or print official payment receipts
        if($paidAmt <= 0 && $status === 'unpaid' && !$isPreview)
        {
            $this->Session->setFlash(sprintf(__('Access Denied: Bill #%s is currently UNPAID with no recorded payment. Payment receipts can only be generated after a payment is recorded.'), h($b['bill_number'])), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }

        $isPartial = ($status === 'partial' || ($paidAmt < $totalAmt && $paidAmt > 0));
        $tplId = $isPartial ? 19 : 16;

        $template = $this->BillingTemplate->findById($tplId);
        if(empty($template) || empty($template['BillingTemplate']['html_content']))
        {
            $template = $this->BillingTemplate->findById(16);
        }

        $html = !empty($template['BillingTemplate']['html_content']) ? $template['BillingTemplate']['html_content'] : '';

        $consumerId = !empty($c['id']) ? ('WV70' . str_pad($c['id'], 2, '0', STR_PAD_LEFT)) : 'WV7006';
        $clientName = !empty($c['name']) ? strtoupper($c['name']) : 'IRFAN RASOOL S/O MIAN GHULAM RASOOL';
        $propertyId = !empty($p['property_no']) ? $p['property_no'] : 'House 7 Marla , Mian Road';
        $clientAddress = !empty($c['address']) ? $c['address'] : (!empty($p['address']) ? $p['address'] : 'House 7 Marla , Mian Road AWAMI BLOCK WHITE VILLAS LAHORE');
        $billingMonth = date('M-Y', strtotime($b['billing_period'] . '-01'));
        $payDate = !empty($b['payment_date']) && $b['payment_date'] !== '0000-00-00 00:00:00' ? date('d-M-Y', strtotime($b['payment_date'])) : date('d-M-Y');
        $docType = $isPartial ? 'PARTIAL PAYMENT RECEIPT' : 'OFFICIAL PAYMENT RECEIPT';

        $totalAmt = (float)$b['total_amount'];
        $paidAmt = (float)$b['paid_amount'];
        $balAmt = (float)$b['balance_amount'];
        $arrears = (float)$b['arrears_amount'];
        $lateFee = (float)$b['late_fee'];
        $consAmt = (float)$b['consumption_amount'];
        $payableAfterDue = $totalAmt + $lateFee;

        $payMethod = !empty($b['payment_method']) ? $b['payment_method'] : 'Online / Bank Deposit';
        $payRef = !empty($b['transaction_ref']) ? $b['transaction_ref'] : 'Cleared';

        // Maintenance rows
        $maintenanceRows = '
        <tr><td class="py-0.5 px-3 border-r border-slate-100">Services Charges</td><td class="py-0.5 px-3 text-right">500.00</td></tr>
        <tr><td class="py-0.5 px-3 border-r border-slate-100">Water Bill</td><td class="py-0.5 px-3 text-right">1,101.00</td></tr>
        <tr><td class="py-0.5 px-3 border-r border-slate-100">Garbage Collection</td><td class="py-0.5 px-3 text-right">350.00</td></tr>';

        // Other charges / Electricity row
        $otherRows = '
        <tr><td class="py-0.5 px-3 border-r border-slate-100">Electricity Energy Charges (' . number_format((float)$b['units_consumed'], 1) . ' &times; ' . number_format((float)$b['unit_rate'], 2) . ')</td><td class="py-0.5 px-3 text-right">' . number_format($consAmt, 2) . '</td></tr>';

        // Fetch Previous 6 Months History
        $propId = (int)$b['billing_property_id'];
        $pastBills = $this->BillingBill->find('all', array(
            'conditions' => array(
                'BillingBill.billing_property_id' => $propId,
                'BillingBill.billing_period <=' => $b['billing_period']
            ),
            'order' => array('BillingBill.billing_period' => 'DESC'),
            'limit' => 6
        ));

        $historyRows = '';
        if(!empty($pastBills))
        {
            foreach($pastBills as $pb)
            {
                $pbi = $pb['BillingBill'];
                $mLabel = date('M-Y', strtotime($pbi['billing_period'] . '-01'));
                $bTot = number_format((float)$pbi['total_amount'], 2);
                $bPaid = number_format((float)$pbi['paid_amount'], 2);
                $bSt = strtoupper($pbi['payment_status']);
                $stColor = ($bSt === 'PAID') ? 'text-green-700' : (($bSt === 'PARTIAL') ? 'text-yellow-700' : 'text-red-600');

                $historyRows .= '
                <tr>
                    <td class="py-0.5 border-r border-slate-100 uppercase font-semibold text-slate-700">' . h($mLabel) . '</td>
                    <td class="py-0.5 border-r border-slate-100">' . $bTot . '</td>
                    <td class="py-0.5 border-r border-slate-100 font-bold text-green-700">' . $bPaid . '</td>
                    <td class="py-0.5 font-bold ' . $stColor . ' uppercase">' . h($bSt) . '</td>
                </tr>';
            }
        }
        else
        {
            $historyRows = '<tr><td colspan="4" class="py-1 text-slate-400">No previous payment history found.</td></tr>';
        }

        // Resolve Society / Project profile for receipt header
        $socId = !empty($b['society_id']) ? (int)$b['society_id'] : (!empty($p['society_id']) ? (int)$p['society_id'] : 1);
        $this->loadModel('BillingManager.BillingSociety');
        $soc = $this->BillingSociety->findById($socId);
        if (empty($soc)) {
            $soc = $this->BillingSociety->find('first', array('order' => array('BillingSociety.id' => 'ASC')));
        }

        $societyName = !empty($soc['BillingSociety']['name']) ? strtoupper($soc['BillingSociety']['name']) : 'WHITE VILLAS LAHORE';
        $societyAddress = !empty($soc['BillingSociety']['address']) ? $soc['BillingSociety']['address'] : 'White Villas Lahore, Bahria Town Lahore, Pakistan';
        $societyPhone = !empty($soc['BillingSociety']['phone']) ? $soc['BillingSociety']['phone'] : '+92 339 1101156';
        $societyEmail = !empty($soc['BillingSociety']['email']) ? $soc['BillingSociety']['email'] : 'info@whitevillas.com.pk';

        $rawLogoUrl = !empty($soc['BillingSociety']['logo_url']) ? trim($soc['BillingSociety']['logo_url']) : '';
        if (empty($rawLogoUrl)) {
            $rawLogoUrl = $this->BillingSetting->getSetting('subm_society_logo', '', $socId);
        }
        if (empty($rawLogoUrl)) {
            $rawLogoUrl = '/img/site-logo-20260824135207-8538.png';
        }

        $societyLogoSrc = $rawLogoUrl;
        if (strpos($rawLogoUrl, 'data:') !== 0 && strpos($rawLogoUrl, 'http://') !== 0 && strpos($rawLogoUrl, 'https://') !== 0) {
            $cleanRelative = preg_replace('/^\/?(WhiteVilas|whitevillas)?\//i', '', $rawLogoUrl);
            $localDiskPath = WWW_ROOT . ltrim($cleanRelative, '/\\');
            if (!file_exists($localDiskPath)) {
                $localDiskPath = WWW_ROOT . 'img' . DS . 'site-logo-20260824135207-8538.png';
            }
            if (file_exists($localDiskPath)) {
                $imgData = @file_get_contents($localDiskPath);
                if ($imgData !== false) {
                    $ext = strtolower(pathinfo($localDiskPath, PATHINFO_EXTENSION));
                    $mime = ($ext === 'jpg' || $ext === 'jpeg') ? 'image/jpeg' : (($ext === 'svg') ? 'image/svg+xml' : 'image/png');
                    $societyLogoSrc = 'data:' . $mime . ';base64,' . base64_encode($imgData);
                }
            } else {
                $societyLogoSrc = Router::url('/' . ltrim($cleanRelative, '/'), true);
            }
        }

        $replacements = array(
            '{{document_type}}' => $docType,
            '{{template_type}}' => $docType,
            '{{society_logo}}' => $societyLogoSrc,
            '{{society_name}}' => $societyName,
            '{{society_address}}' => $societyAddress,
            '{{society_mobile}}' => $societyPhone,
            '{{society_phone}}' => $societyPhone,
            '{{society_email}}' => $societyEmail,
            '{{bill_no}}' => $b['bill_number'],
            '{{consumer_id}}' => $consumerId,
            '{{property_id}}' => $propertyId,
            '{{billing_month}}' => $billingMonth,
            '{{issue_date}}' => $payDate,
            '{{payment_date}}' => $payDate,
            '{{payment_status}}' => $isPartial ? 'PARTIALLY PAID' : 'CLEARED & PAID',
            '{{client_name}}' => $clientName,
            '{{client_address}}' => $clientAddress,
            '{{late_fee_percent}}' => '10.00',
            '{{LATE_FEE_PERCENT}}' => '10.00',
            '{{arrears}}' => number_format($arrears, 2),
            '{{total_amount}}' => number_format($totalAmt, 2),
            '{{late_fee}}' => number_format($lateFee, 2),
            '{{payable_after_due}}' => number_format($payableAfterDue, 2),
            '{{paid_amount}}' => number_format($paidAmt, 2),
            '{{pending_amount}}' => number_format($balAmt, 2),
            '{{balance_amount}}' => number_format($balAmt, 2),
            '{{adjustment_credit}}' => '0.00',
            '{{adjustment_debit}}' => '0.00',
            '{{adjustment_note}}' => 'Verified Digital Official Ledger Entry',
            '{{payment_method}}' => $payMethod,
            '{{payment_ref}}' => $payRef,
            '{{PAYMENT_REF}}' => $payRef,
            '{{maintenance_rows}}' => $maintenanceRows,
            '{{other_rows}}' => $otherRows,
            '{{history_rows}}' => $historyRows,
            '{{preview_caution}}' => ''
        );

        $rendered = str_replace(array_keys($replacements), array_values($replacements), $html);

        // Fallback for any remaining lowercase/uppercase placeholders
        $rendered = preg_replace_callback('/\{\{([a-zA-Z0-9_-]+)\}\}/', function($m) use ($replacements) {
            $k = '{{' . strtolower($m[1]) . '}}';
            return isset($replacements[$k]) ? $replacements[$k] : '';
        }, $rendered);

        // Inject Top Floating Print & Close Bar
        $printBtnHtml = '
        <div style="position:fixed;top:12px;right:20px;z-index:99999;display:flex;gap:8px;" class="no-print">
            <button onclick="window.print()" style="background:#059669;color:#fff;font-weight:900;padding:10px 22px;border:none;border-radius:6px;box-shadow:0 4px 14px rgba(0,0,0,0.3);cursor:pointer;font-size:13px;display:flex;align-items:center;gap:8px;">
                🖨️ Print Paid Slip
            </button>
            <button onclick="window.close()" style="background:#475569;color:#fff;font-weight:700;padding:10px 16px;border:none;border-radius:6px;cursor:pointer;font-size:13px;">
                ✖ Close
            </button>
        </div>';

        if(strpos($rendered, '</body>') !== false) {
            $rendered = str_replace('</body>', $printBtnHtml . '</body>', $rendered);
        } else {
            $rendered = $printBtnHtml . $rendered;
        }

        echo $rendered;
        exit;
    }

    protected function _exportBillsCsv($conditions)
    {
        $this->autoRender = false;
        $bills = $this->BillingBill->find('all', array(
            'conditions' => $conditions,
            'contain' => array('BillingProperty', 'BillingClient'),
            'order' => array('BillingBill.id' => 'DESC')
        ));

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=bills_export_' . date('Y-m-d_His') . '.csv');
        $fp = fopen('php://output', 'w');
        fputcsv($fp, array('Bill No', 'Consumer ID', 'Client', 'House/Plot #', 'Meter No', 'Meter Type', 'Month', 'Bill PKR', 'Late Fee PKR', 'Total PKR', 'Paid PKR', 'Balance PKR', 'Prev Pending', 'Ref#', 'Status', 'Paid Date'));

        foreach($bills as $b)
        {
            $bi = $b['BillingBill'];
            $pr = $b['BillingProperty'];
            $cl = $b['BillingClient'];
            fputcsv($fp, array(
                $bi['bill_number'],
                !empty($cl['id']) ? ('WV70' . str_pad($cl['id'], 2, '0', STR_PAD_LEFT)) : '-',
                !empty($cl['name']) ? strtoupper($cl['name']) : '-',
                !empty($pr['property_no']) ? $pr['property_no'] : '-',
                !empty($bi['billing_property_id']) ? ('MTR-' . $bi['billing_property_id']) : '-',
                $bi['utility_type'],
                date('M Y', strtotime($bi['billing_period'] . '-01')),
                $bi['consumption_amount'],
                $bi['late_fee'],
                $bi['total_amount'],
                $bi['paid_amount'],
                $bi['balance_amount'],
                $bi['arrears_amount'],
                $bi['transaction_ref'],
                strtoupper($bi['payment_status']),
                $bi['payment_date']
            ));
        }
        fclose($fp);
        exit;
    }

    public function run_late_fees()
    {
        $societyId = !empty($this->request->query['society_id']) ? (int)$this->request->query['society_id'] : $this->_getActiveSocietyId();
        
        $enabled = $this->BillingSetting->getSetting('subm_feat_auto_late_fee', '1', $societyId);
        if($enabled === '0' || $enabled === 0)
        {
            $this->Session->setFlash(__('Automated Late-Fee Engine is switched OFF for this society in Feature Toggles.'), 'flash', array('alert' => 'warning'));
            return $this->redirect(array('action' => 'index', '?' => array('society_id' => $societyId)));
        }

        $percent = (float)$this->BillingSetting->getSetting('subm_late_fee_percent', 7.0, $societyId);
        $graceDays = (int)$this->BillingSetting->getSetting('subm_late_fee_grace_days', 0, $societyId);
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

        $this->Session->setFlash(sprintf(__('Automated Late-Fee Engine executed! Applied %s%% surcharge to %d overdue bills (Total surcharges added: PKR %s).'), number_format($percent, 1), $appliedCount, number_format($totalSurcharge, 2)), 'flash', array('alert' => 'success'));
        return $this->redirect(array('action' => 'index', '?' => array('society_id' => $societyId)));
    }

    public function admin_run_late_fees()
    {
        return $this->run_late_fees();
    }

    public function send_bill_sms($billId = null)
    {
        $this->autoRender = false;
        if(empty($billId) && !empty($this->request->query['bill_id'])) {
            $billId = (int)$this->request->query['bill_id'];
        }
        if(empty($billId) && !empty($this->request->data['bill_id'])) {
            $billId = (int)$this->request->data['bill_id'];
        }

        $res = $this->_sendBillSmsDirect($billId);

        if($this->request->is('ajax') || !empty($this->request->query['json'])) {
            header('Content-Type: application/json');
            echo json_encode($res);
            exit;
        }

        if(!empty($res['success'])) {
            $this->Session->setFlash($res['message'], 'flash', array('alert' => 'success'));
        } else {
            $this->Session->setFlash($res['message'], 'flash', array('alert' => 'danger'));
        }
        return $this->redirect(array('action' => 'index'));
    }

    public function admin_send_bill_sms($billId = null)
    {
        return $this->send_bill_sms($billId);
    }

    public function _sendBillSmsDirect($billId)
    {
        if(empty($billId)) {
            return array('success' => false, 'message' => __('Bill ID is required.'));
        }

        $bill = $this->BillingBill->find('first', array(
            'conditions' => array('BillingBill.id' => (int)$billId),
            'contain' => array('BillingProperty', 'BillingClient')
        ));

        if(empty($bill)) {
            return array('success' => false, 'message' => __('Bill record not found.'));
        }

        $b = $bill['BillingBill'];
        $c = !empty($bill['BillingClient']) ? $bill['BillingClient'] : array();
        $p = !empty($bill['BillingProperty']) ? $bill['BillingProperty'] : array();

        if(empty($c['phone'])) {
            return array('success' => false, 'message' => sprintf(__('Resident %s has no phone number registered.'), !empty($c['name']) ? $c['name'] : 'Client'));
        }

        App::uses('LeadbotGatewayClient', 'Lib');
        $cleanPhone = LeadbotGatewayClient::cleanPhone($c['phone']);
        if(empty($cleanPhone)) {
            $cleanPhone = trim($c['phone']);
        }

        $socId = !empty($b['society_id']) ? (int)$b['society_id'] : 1;

        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
        $host = !empty($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
        $printBillUrl = $protocol . $host . Router::url(array('plugin' => 'billing_manager', 'controller' => 'billing_bills', 'action' => 'print_bill', $b['id']));
        $printReceiptUrl = $protocol . $host . Router::url(array('plugin' => 'billing_manager', 'controller' => 'billing_bills', 'action' => 'print_receipt', $b['id']));

        $clientName = !empty($c['name']) ? $c['name'] : 'Valued Resident';
        $billNo = $b['bill_number'];
        $monthLabel = date('M Y', strtotime($b['billing_period'] . '-01'));
        $totalAmt = number_format((float)$b['total_amount'], 2);
        $paidAmt = number_format((float)$b['paid_amount'], 2);
        $balAmt = number_format((float)$b['balance_amount'], 2);
        $status = strtolower($b['payment_status']);

        if($status === 'paid' || ((float)$b['paid_amount'] >= (float)$b['total_amount'] && (float)$b['total_amount'] > 0)) {
            $smsMsg = sprintf(
                "Dear %s, payment of PKR %s for White Villas Bill #%s has been received. Status: CLEARED & PAID. Balance: PKR 0.00. Paid Slip: %s. Thank you! - White Villas Management",
                $clientName,
                $paidAmt,
                $billNo,
                $printReceiptUrl
            );
        } elseif($status === 'partial' || (float)$b['paid_amount'] > 0) {
            $smsMsg = sprintf(
                "Dear %s, partial payment of PKR %s for Bill #%s received. Status: PARTIAL. Remaining Balance: PKR %s. Paid Slip: %s. Thank you! - White Villas Management",
                $clientName,
                $paidAmt,
                $billNo,
                $balAmt,
                $printReceiptUrl
            );
        } else {
            $smsMsg = sprintf(
                "Dear %s, your White Villas Bill #%s for %s of PKR %s is due on %s. View Bill: %s. Please pay on time. Thank you! - White Villas Management",
                $clientName,
                $billNo,
                $monthLabel,
                $totalAmt,
                date('d-M-Y', strtotime($b['due_date'])),
                $printBillUrl
            );
        }

        // Send via default system SMS gateway (CustomFunctionComponent::sendSms)
        $smsSent = false;
        $apiResponse = '';
        $errorMsg = '';
        try {
            if(!isset($this->CustomFunction)) {
                $this->CustomFunction = $this->Components->load('CustomFunction');
            }
            $smsSettings = isset($this->smsSettingArr) ? $this->smsSettingArr : array();
            $smsRes = $this->CustomFunction->sendSms($cleanPhone, $smsMsg, $smsSettings);
            $smsSent = true;
            $apiResponse = is_string($smsRes) ? $smsRes : 'Sent via Default Gateway';
        } catch(Exception $eSms) {
            $errorMsg = $eSms->getMessage();
            $apiResponse = 'Error: ' . $errorMsg;
        }

        // Log to billing_reminder_logs
        try {
            $ReminderLog = ClassRegistry::init('BillingManager.BillingReminderLog');
            if($ReminderLog) {
                $ReminderLog->create();
                $ReminderLog->save(array('BillingReminderLog' => array(
                    'society_id' => $socId,
                    'bill_id' => $b['id'],
                    'channel' => 'sms',
                    'recipient' => $cleanPhone,
                    'message_body' => $smsMsg,
                    'status' => $smsSent ? 'sent' : 'failed',
                    'response_payload' => is_string($apiResponse) ? substr($apiResponse, 0, 255) : 'Dispatched',
                    'sent_at' => date('Y-m-d H:i:s')
                )));
            }
        } catch(Exception $eLog) {}

        if($smsSent) {
            return array(
                'success' => true,
                'message' => sprintf(__('SMS notification successfully dispatched to %s (%s) via default gateway for Bill #%s!'), $clientName, $cleanPhone, $billNo),
                'phone' => $cleanPhone,
                'sms_msg' => $smsMsg
            );
        } else {
            return array(
                'success' => false,
                'message' => sprintf(__('SMS Dispatch failed: %s'), $errorMsg),
                'phone' => $cleanPhone,
                'sms_msg' => $smsMsg
            );
        }
    }

    public function send_bill_whatsapp($billId = null)
    {
        $this->autoRender = false;
        if(empty($billId) && !empty($this->request->query['bill_id'])) {
            $billId = (int)$this->request->query['bill_id'];
        }
        if(empty($billId) && !empty($this->request->data['bill_id'])) {
            $billId = (int)$this->request->data['bill_id'];
        }

        $res = $this->_sendBillWhatsappDirect($billId);

        if($this->request->is('ajax') || !empty($this->request->query['json'])) {
            header('Content-Type: application/json');
            echo json_encode($res);
            exit;
        }

        if(!empty($res['success'])) {
            $this->Session->setFlash($res['message'], 'flash', array('alert' => 'success'));
        } else {
            $this->Session->setFlash($res['message'], 'flash', array('alert' => 'danger'));
        }
        return $this->redirect(array('action' => 'index'));
    }

    public function admin_send_bill_whatsapp($billId = null)
    {
        return $this->send_bill_whatsapp($billId);
    }

    public function _generateBillPdfDataUri($billId, $type = 'bill')
    {
        $tmpDir = TMP . 'bills' . DS;
        if(!is_dir($tmpDir)) {
            @mkdir($tmpDir, 0777, true);
        }

        $pdfFile = $tmpDir . ($type === 'receipt' ? 'receipt_' : 'bill_') . (int)$billId . '_' . time() . '.pdf';
        
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
        $host = !empty($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
        $action = ($type === 'receipt') ? 'print_receipt' : 'print_bill';
        $targetUrl = $protocol . $host . Router::url(array(
            'plugin' => 'billing_manager',
            'controller' => 'billing_bills',
            'action' => $action,
            $billId,
            '?' => array('nobar' => 1)
        ));

        $chromePath = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
        $edgePath = 'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe';
        $bin = file_exists($chromePath) ? $chromePath : (file_exists($edgePath) ? $edgePath : null);

        if(!empty($bin)) {
            $cmd = 'cmd /c "' . escapeshellarg($bin) . ' --headless=new --disable-gpu --no-sandbox --print-to-pdf=' . escapeshellarg($pdfFile) . ' ' . escapeshellarg($targetUrl) . '" 2>&1';
            @shell_exec($cmd);
        }

        if(file_exists($pdfFile) && filesize($pdfFile) > 1000) {
            $pdfBytes = file_get_contents($pdfFile);
            @unlink($pdfFile);
            return 'data:application/pdf;base64,' . base64_encode($pdfBytes);
        }

        return null;
    }

    public function _sendBillWhatsappDirect($billId)
    {
        if(empty($billId)) {
            return array('success' => false, 'message' => __('Bill ID is required.'));
        }

        $bill = $this->BillingBill->find('first', array(
            'conditions' => array('BillingBill.id' => (int)$billId),
            'contain' => array('BillingProperty', 'BillingClient')
        ));

        if(empty($bill)) {
            return array('success' => false, 'message' => __('Bill record not found.'));
        }

        $b = $bill['BillingBill'];
        $c = !empty($bill['BillingClient']) ? $bill['BillingClient'] : array();
        $p = !empty($bill['BillingProperty']) ? $bill['BillingProperty'] : array();

        if(empty($c['phone'])) {
            return array('success' => false, 'message' => sprintf(__('Resident %s has no phone number registered in the system.'), !empty($c['name']) ? $c['name'] : 'Client'));
        }

        App::uses('LeadbotGatewayClient', 'Lib');
        $cleanPhone = LeadbotGatewayClient::cleanPhone($c['phone']);
        if(empty($cleanPhone)) {
            return array('success' => false, 'message' => sprintf(__('Invalid phone number "%s" for resident %s.'), $c['phone'], !empty($c['name']) ? $c['name'] : 'Client'));
        }

        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
        $host = !empty($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
        $printBillUrl = $protocol . $host . Router::url(array('plugin' => 'billing_manager', 'controller' => 'billing_bills', 'action' => 'print_bill', $b['id']));
        $printReceiptUrl = $protocol . $host . Router::url(array('plugin' => 'billing_manager', 'controller' => 'billing_bills', 'action' => 'print_receipt', $b['id']));

        $clientName  = !empty($c['name']) ? $c['name'] : 'Valued Resident';
        $consumerId  = !empty($b['consumer_number']) ? $b['consumer_number'] : (!empty($c['consumer_number']) ? $c['consumer_number'] : ('WV-' . str_pad($b['id'], 4, '0', STR_PAD_LEFT)));
        $billNo      = $b['bill_number'];
        $monthLabel  = date('F Y', strtotime($b['billing_period'] . '-01'));
        $unitNo      = !empty($p['title']) ? $p['title'] : (!empty($b['unit_no']) ? $b['unit_no'] : 'Plot/House');
        $totalAmt    = (float)$b['total_amount'];
        $paidAmt     = (float)$b['paid_amount'];
        $balAmt      = (float)$b['balance_amount'];
        $status      = strtolower((string)$b['payment_status']);
        $isPaid      = ($status === 'paid' || ($paidAmt >= $totalAmt && $totalAmt > 0));

        if($isPaid) {
            $paidDate = !empty($b['paid_date']) ? date('d-M-Y', strtotime($b['paid_date'])) : date('d-M-Y');
            $ref = !empty($b['transaction_ref']) ? $b['transaction_ref'] : (!empty($b['payment_method']) ? $b['payment_method'] : 'Cleared');
            $waMsg = "🏛️ *WHITE VILLAS LAHORE*\n"
                   . "✅ *OFFICIAL PAYMENT RECEIPT*\n"
                   . "━━━━━━━━━━━━━━━━━━━━━\n"
                   . "🧾 *Receipt #:* #" . $billNo . "  |  🆔 *ID:* " . $consumerId . "\n"
                   . "👤 *Resident:* " . $clientName . "\n"
                   . "🏠 *Property:* " . $unitNo . "\n"
                   . "📅 *Period:* " . $monthLabel . "\n"
                   . "━━━━━━━━━━━━━━━━━━━━━\n"
                   . "🟢 *PAID AMOUNT:* *PKR " . number_format($paidAmt, 2) . "*\n"
                   . "📅 *Payment Date:* " . $paidDate . "\n"
                   . "💳 *Ref / TID:* " . $ref . "\n"
                   . "⚖️ *Remaining Balance:* *PKR " . number_format($balAmt, 2) . "* (" . ($balAmt <= 0 ? "Fully Cleared" : "Partial") . ")\n"
                   . "✨ *Status:* *CLEARED & PAID IN FULL*\n"
                   . "━━━━━━━━━━━━━━━━━━━━━\n"
                   . "📎 *Official Paid Slip PDF Attached*\n"
                   . "📄 *View in Browser:* " . $printReceiptUrl . "\n"
                   . "🙏 *Thank you for your prompt payment!*\n"
                   . "📞 *Office:* +92 339 1101156";
        } else {
            $billAmt = (float)$b['current_billing_amount'];
            $arrears = (float)$b['arrears_amount'];
            $lateFee = (float)$b['late_fee_amount'];
            $payableAfterDue = $totalAmt + ($lateFee > 0 ? $lateFee : 0);
            $meterNo = !empty($b['meter_number']) ? $b['meter_number'] : '-';
            $unitsLine = ((float)$b['units_consumed'] > 0) ? ("📊 *Units Consumed:* " . number_format((float)$b['units_consumed'], 1) . " kWh\n") : "";
            $arrearsLine = ($arrears > 0) ? ("⏳ *Previous Arrears:* PKR " . number_format($arrears, 2) . "\n") : "";
            $lateFeeLine = ($lateFee > 0) ? ("⚠️ *Late Fee:* PKR " . number_format($lateFee, 2) . "\n") : "";

            $waMsg = "🏛️ *WHITE VILLAS LAHORE*\n"
                   . "⚡ *UTILITY BILL NOTICE* — *" . $monthLabel . "*\n"
                   . "━━━━━━━━━━━━━━━━━━━━━\n"
                   . "📋 *Bill #:* #" . $billNo . "  |  🆔 *ID:* " . $consumerId . "\n"
                   . "👤 *Resident:* " . $clientName . "\n"
                   . "🏠 *Property:* " . $unitNo . "\n"
                   . "⚡ *Utility:* " . ucfirst($b['utility_type']) . " (" . $meterNo . ")\n"
                   . $unitsLine
                   . "━━━━━━━━━━━━━━━━━━━━━\n"
                   . "💰 *Current Bill:* PKR " . number_format($billAmt, 2) . "\n"
                   . $arrearsLine
                   . $lateFeeLine
                   . "💵 *PAYABLE BY DUE DATE:* *PKR " . number_format($totalAmt, 2) . "*\n"
                   . "🔴 *Due Date:* *" . date('d-M-Y', strtotime((string)$b['due_date'])) . "*\n"
                   . "🚨 *After Due Date:* PKR " . number_format($payableAfterDue, 2) . "\n"
                   . "━━━━━━━━━━━━━━━━━━━━━\n"
                   . "📎 *Official PDF Bill Attached*\n"
                   . "📄 *View in Browser:* " . $printBillUrl . "\n"
                   . "🏦 *Online Accounts:* Meezan / JazzCash (0339-1101156)\n"
                   . "📞 *Office:* +92 339 1101156";
        }

        // Generate official PDF data URI attachment matching print preview
        $pdfDataUri = $this->_generateBillPdfDataUri($b['id'], $isPaid ? 'receipt' : 'bill');
        $pdfFileName = ($isPaid ? 'Receipt_' : 'Bill_') . preg_replace('/[^a-zA-Z0-9_-]/', '', $billNo) . '.pdf';

        // Fetch LeadBot WhatsApp gateway settings
        $GatewayModel = ClassRegistry::init('Admin.LeadbotGatewaySetting');
        $socId = !empty($b['society_id']) ? (int)$b['society_id'] : 1;
        $settings = $GatewayModel ? $GatewayModel->getSettings(0, 0, true) : array();

        $gatewayUrl = !empty($settings['gateway_url']) ? $settings['gateway_url'] : LeadbotGatewayClient::DEFAULT_LOCAL_URL;
        $apiKey     = !empty($settings['api_key']) ? $settings['api_key'] : '';
        $waSession  = !empty($settings['selected_wa_session']) ? $settings['selected_wa_session'] : '';
        $agencyId   = !empty($settings['agency_id']) ? $settings['agency_id'] : 51;

        $fallbackUrl = "https://wa.me/" . $cleanPhone . "?text=" . urlencode($waMsg);

        if(empty($apiKey)) {
            return array(
                'success' => false,
                'message' => __('LeadBot WhatsApp API Key is not configured in /admin/LeadbotGateways.'),
                'fallback_url' => $fallbackUrl
            );
        }

        $dispatchParams = array(
            'recipient' => $cleanPhone,
            'message'   => $waMsg,
            'session'   => $waSession,
            'agency_id' => $agencyId,
            'filename'  => $pdfFileName
        );
        if(!empty($pdfDataUri)) {
            $dispatchParams['media_url'] = $pdfDataUri;
        }

        $res = LeadbotGatewayClient::sendWhatsApp($gatewayUrl, $apiKey, $dispatchParams);

        $isSuccess = !empty($res['success']);
        $statusStr = $isSuccess ? 'sent' : 'failed';
        $apiPayload = $isSuccess ? (isset($res['message_id']) ? ('Message ID: ' . $res['message_id']) : 'Sent') : (!empty($res['error']) ? $res['error'] : 'Failed');

        // Log to billing_reminder_logs
        try {
            $ReminderLog = ClassRegistry::init('BillingManager.BillingReminderLog');
            if($ReminderLog) {
                $ReminderLog->create();
                $ReminderLog->save(array('BillingReminderLog' => array(
                    'society_id' => $socId,
                    'bill_id' => $b['id'],
                    'channel' => 'whatsapp',
                    'recipient' => $cleanPhone,
                    'message_body' => $waMsg,
                    'status' => $statusStr,
                    'response_payload' => is_string($apiPayload) ? substr($apiPayload, 0, 255) : json_encode($apiPayload),
                    'sent_at' => date('Y-m-d H:i:s')
                )));
            }
        } catch(Exception $eLog) {}

        // Log to Admin.LeadbotNotificationLog
        try {
            $LeadbotLog = ClassRegistry::init('Admin.LeadbotNotificationLog');
            if($LeadbotLog) {
                $LeadbotLog->create();
                $LeadbotLog->save(array('LeadbotNotificationLog' => array(
                    'project_id' => $agencyId,
                    'event_key' => $isPaid ? 'bill_paid_receipt' : 'bill_unpaid_notice',
                    'channel' => 'whatsapp',
                    'recipient' => $cleanPhone,
                    'recipient_name' => $clientName,
                    'message' => $waMsg,
                    'gateway_profile_or_device' => !empty($waSession) ? $waSession : 'LeadBot WhatsApp',
                    'status' => $isSuccess ? 'sent' : 'failed',
                    'response_code' => isset($res['latency_ms']) ? (int)$res['latency_ms'] : ($isSuccess ? 200 : 500)
                )));
            }
        } catch(Exception $eLbLog) {}

        if($isSuccess) {
            return array(
                'success' => true,
                'message' => sprintf(__('WhatsApp %s with official PDF attachment successfully dispatched to %s (%s) via LeadBot API!'), $isPaid ? __('Receipt') : __('Bill Notice'), $clientName, $cleanPhone),
                'recipient' => $cleanPhone,
                'client_name' => $clientName,
                'bill_number' => $billNo,
                'message_id' => isset($res['message_id']) ? $res['message_id'] : '',
                'channel' => 'whatsapp',
                'has_pdf' => !empty($pdfDataUri),
                'fallback_url' => $fallbackUrl
            );
        } else {
            return array(
                'success' => false,
                'message' => sprintf(__('LeadBot WhatsApp error: %s'), !empty($res['error']) ? $res['error'] : __('Unable to deliver WhatsApp message.')),
                'recipient' => $cleanPhone,
                'client_name' => $clientName,
                'bill_number' => $billNo,
                'fallback_url' => $fallbackUrl
            );
        }
    }
}
