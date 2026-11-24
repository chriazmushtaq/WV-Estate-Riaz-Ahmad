<?php
App::uses('BillingManagerAppController', 'BillingManager.Controller');

class BillingMetersController extends BillingManagerAppController
{
    public $uses = array(
        'BillingManager.BillingMeter',
        'BillingManager.BillingProperty',
        'BillingManager.BillingReading',
        'BillingManager.BillingSociety'
    );

    public function index()
    {
        $this->paginate = array(
            'order' => array('BillingMeter.id' => 'ASC'),
            'limit' => 30,
            'contain' => array('BillingProperty' => array('BillingClient'))
        );

        $meters = $this->paginate('BillingMeter');
        $properties = $this->_getPropertyList();

        // Direct Property to Client lookup mapping
        $this->loadModel('BillingManager.BillingClient');
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

        $this->set(compact('meters', 'properties', 'propToClient'));
    }

    public function add()
    {
        if($this->request->is('post'))
        {
            if(empty($this->request->data['BillingMeter']['society_id']))
            {
                $this->request->data['BillingMeter']['society_id'] = 1;
            }
            $this->BillingMeter->create();
            if($this->BillingMeter->save($this->request->data))
            {
                $this->Session->setFlash(__('Meter successfully created.'), 'flash', array('alert' => 'success'));
                return $this->redirect(array('action' => 'index'));
            }
            else
            {
                $this->Session->setFlash(__('Failed to save meter.'), 'flash', array('alert' => 'danger'));
            }
        }
        $properties = $this->_getPropertyList();
        $societies = $this->BillingSociety->find('list', array('fields' => array('id', 'name')));
        $this->set(compact('properties', 'societies'));
    }

    public function edit($id = null)
    {
        $this->BillingMeter->id = (int)$id;
        if($this->request->is(array('post', 'put')))
        {
            if($this->BillingMeter->save($this->request->data))
            {
                $this->Session->setFlash(__('Meter updated successfully.'), 'flash', array('alert' => 'success'));
                return $this->redirect(array('action' => 'index'));
            }
        }
        else
        {
            $this->request->data = $this->BillingMeter->read();
        }
        $properties = $this->_getPropertyList();
        $societies = $this->BillingSociety->find('list', array('fields' => array('id', 'name')));
        $this->set(compact('properties', 'societies'));
    }

    public function delete($id = null)
    {
        $this->BillingMeter->delete((int)$id);
        $this->Session->setFlash(__('Meter deleted.'), 'flash', array('alert' => 'success'));
        return $this->redirect(array('action' => 'index'));
    }

    public function replace_meter($id = null)
    {
        if ($this->request->is(array('post', 'put'))) {
            $oldMeterId = (int)$this->request->data['BillingMeter']['old_meter_id'];
            $oldMeter = $this->BillingMeter->findById($oldMeterId);

            if (empty($oldMeter)) {
                $this->Session->setFlash(__('Old meter not found.'), 'flash', array('alert' => 'danger'));
                return $this->redirect(array('action' => 'index'));
            }

            $socId = !empty($oldMeter['BillingMeter']['society_id']) ? (int)$oldMeter['BillingMeter']['society_id'] : 1;
            $propId = (int)$oldMeter['BillingMeter']['billing_property_id'];
            $utilityType = !empty($oldMeter['BillingMeter']['utility_type']) ? $oldMeter['BillingMeter']['utility_type'] : 'Electricity';

            $oldFinalReading = (float)$this->request->data['BillingMeter']['old_final_reading'];
            $newMeterNumber = trim($this->request->data['BillingMeter']['new_meter_number']);
            $newInitialReading = isset($this->request->data['BillingMeter']['new_initial_reading']) ? (float)$this->request->data['BillingMeter']['new_initial_reading'] : 0.0;
            $reason = !empty($this->request->data['BillingMeter']['replacement_reason']) ? trim($this->request->data['BillingMeter']['replacement_reason']) : 'Dead / Faulty Meter Replacement';

            $replacementFee = isset($this->request->data['BillingMeter']['replacement_fee']) ? (float)$this->request->data['BillingMeter']['replacement_fee'] : 0.0;
            $feeTitle = !empty($this->request->data['BillingMeter']['replacement_fee_title']) ? trim($this->request->data['BillingMeter']['replacement_fee_title']) : 'Dead Meter Replacement & Penalty Charge';

            // Step 1: Update old meter status to replaced / inactive with final reading
            $this->BillingMeter->id = $oldMeterId;
            $this->BillingMeter->save(array(
                'status' => 'replaced',
                'current_reading' => $oldFinalReading
            ));

            // Step 2: Record final reading entry for old meter if old_final_reading > previous reading
            $lastReading = $this->BillingReading->find('first', array(
                'conditions' => array('BillingReading.meter_id' => $oldMeterId),
                'order' => array('BillingReading.id' => 'DESC')
            ));
            $prevOfOld = !empty($lastReading) ? (float)$lastReading['BillingReading']['current_reading'] : (float)$oldMeter['BillingMeter']['initial_reading'];

            if ($oldFinalReading >= $prevOfOld) {
                $this->BillingReading->create();
                $this->BillingReading->save(array('BillingReading' => array(
                    'society_id' => $socId,
                    'meter_id' => $oldMeterId,
                    'billing_property_id' => $propId,
                    'reading_date' => date('Y-m-d'),
                    'reading_month' => date('Y-m'),
                    'previous_reading' => $prevOfOld,
                    'current_reading' => $oldFinalReading,
                    'units_consumed' => max(0.0, $oldFinalReading - $prevOfOld),
                    'reader_name' => 'Admin (Meter Replacement)',
                    'is_billed' => 0
                )));
            }

            // Step 3: Create the NEW active meter assigned to the property unit starting at initial_reading
            $this->BillingMeter->create();
            $newMeterData = array(
                'society_id' => $socId,
                'billing_property_id' => $propId,
                'meter_number' => $newMeterNumber,
                'utility_type' => $utilityType,
                'multiplier' => 1.0,
                'initial_reading' => $newInitialReading,
                'current_reading' => $newInitialReading,
                'status' => 'active'
            );
            $this->BillingMeter->save(array('BillingMeter' => $newMeterData));

            // Step 4: If replacement fee / penalty > 0, create an extra charge item for the client's property
            if ($replacementFee > 0) {
                $this->loadModel('BillingManager.BillingExtraCharge');
                $this->BillingExtraCharge->create();
                $this->BillingExtraCharge->save(array('BillingExtraCharge' => array(
                    'society_id' => $socId,
                    'title' => $feeTitle . ' (Old Meter #' . $oldMeter['BillingMeter']['meter_number'] . ' replaced by #' . $newMeterNumber . ')',
                    'amount' => $replacementFee,
                    'charge_type' => 'one_time',
                    'apply_to' => 'property',
                    'status' => 'active'
                )));
            }

            $this->Session->setFlash(sprintf(__('Old Meter #%s archived (final reading %s). New Meter #%s activated starting at %s.'), h($oldMeter['BillingMeter']['meter_number']), number_format($oldFinalReading, 2), h($newMeterNumber), number_format($newInitialReading, 2)), 'flash', array('alert' => 'success'));
            return $this->redirect(array('action' => 'index'));
        }

        return $this->redirect(array('action' => 'index'));
    }

    public function ledger($id = null)
    {
        $meter = $this->BillingMeter->find('first', array(
            'conditions' => array('BillingMeter.id' => (int)$id),
            'contain' => array('BillingProperty' => array('BillingClient'))
        ));

        if (empty($meter)) {
            $this->Session->setFlash(__('Meter not found.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }

        $propId = !empty($meter['BillingMeter']['billing_property_id']) ? (int)$meter['BillingMeter']['billing_property_id'] : 0;

        // Fetch all readings for this meter
        $readings = $this->BillingReading->find('all', array(
            'conditions' => array('BillingReading.meter_id' => (int)$id),
            'order' => array('BillingReading.id' => 'DESC')
        ));

        // Index readings by reading_month (e.g. '2026-08')
        $readingsByMonth = array();
        foreach($readings as $rd) {
            $rMonth = !empty($rd['BillingReading']['reading_month']) ? $rd['BillingReading']['reading_month'] : date('Y-m', strtotime($rd['BillingReading']['reading_date']));
            $readingsByMonth[$rMonth] = $rd['BillingReading'];
        }

        // Fetch all bills for this property unit
        $this->loadModel('BillingManager.BillingBill');
        $bills = array();
        $billsByMonth = array();
        if ($propId > 0) {
            $bills = $this->BillingBill->find('all', array(
                'conditions' => array('BillingBill.billing_property_id' => $propId),
                'order' => array('BillingBill.id' => 'DESC')
            ));
            foreach($bills as $bl) {
                $bMonth = !empty($bl['BillingBill']['billing_period']) ? $bl['BillingBill']['billing_period'] : date('Y-m', strtotime($bl['BillingBill']['created']));
                $billsByMonth[$bMonth] = $bl['BillingBill'];
            }
        }

        // Collect all distinct months
        $allMonths = array_unique(array_merge(array_keys($readingsByMonth), array_keys($billsByMonth)));
        rsort($allMonths);

        // Combined Financial Ledger Array
        $combinedLedger = array();
        $totalUnits = 0;
        $totalBilled = 0;
        $totalPaid = 0;
        $totalBalance = 0;

        foreach($allMonths as $mKey) {
            $r = isset($readingsByMonth[$mKey]) ? $readingsByMonth[$mKey] : null;
            $b = isset($billsByMonth[$mKey]) ? $billsByMonth[$mKey] : null;

            // Format month as 'Aug-2026'
            $formattedMonth = $mKey;
            if (preg_match('/^(\d{4})-(\d{2})$/', $mKey, $matches)) {
                $formattedMonth = date('M-Y', strtotime($mKey . '-01'));
            } elseif (strtotime($mKey)) {
                $formattedMonth = date('M-Y', strtotime($mKey));
            }

            $uConsumed = !empty($r['units_consumed']) ? (float)$r['units_consumed'] : 0;
            $bAmount = !empty($b['total_amount']) ? (float)$b['total_amount'] : 0;
            $pAmount = !empty($b['paid_amount']) ? (float)$b['paid_amount'] : 0;
            $balAmount = max(0, $bAmount - $pAmount);

            $totalUnits += $uConsumed;
            $totalBilled += $bAmount;
            $totalPaid += $pAmount;
            $totalBalance += $balAmount;

            $combinedLedger[] = array(
                'month' => $mKey,
                'formatted_month' => $formattedMonth,
                'reading' => $r,
                'bill' => $b,
                'units' => $uConsumed,
                'billed' => $bAmount,
                'paid' => $pAmount,
                'balance' => $balAmount
            );
        }

        $summary = compact('totalUnits', 'totalBilled', 'totalPaid', 'totalBalance');

        // Fetch society details & settings for print header/footer
        $socId = !empty($meter['BillingMeter']['society_id']) ? (int)$meter['BillingMeter']['society_id'] : 1;
        $this->loadModel('BillingManager.BillingSociety');
        $this->loadModel('BillingManager.BillingSetting');
        $societyRec = $this->BillingSociety->findById($socId);
        $societyData = !empty($societyRec['BillingSociety']) ? $societyRec['BillingSociety'] : array();

        $societyName = $this->BillingSetting->getSetting('subm_society_name', !empty($societyData['name']) ? $societyData['name'] : 'WHITE VILLAS HOUSING SOCIETY', $socId);
        $societyAddress = $this->BillingSetting->getSetting('subm_society_address', !empty($societyData['address']) ? $societyData['address'] : 'Awami Block White Villas Bahria Town Lahore, PAKISTAN', $socId);
        $societyPhone = $this->BillingSetting->getSetting('subm_society_phone', !empty($societyData['phone']) ? $societyData['phone'] : '+92339 1101156', $socId);
        $societyEmail = $this->BillingSetting->getSetting('subm_society_email', !empty($societyData['email']) ? $societyData['email'] : 'info@whitevillas.com.pk', $socId);

        $this->set(compact('meter', 'readings', 'bills', 'combinedLedger', 'summary', 'allMonths', 'societyName', 'societyAddress', 'societyPhone', 'societyEmail'));
    }

    protected function _getPropertyList()
    {
        $props = $this->BillingProperty->find('all', array(
            'order' => array('BillingProperty.title' => 'ASC'),
            'contain' => array('BillingClient')
        ));
        $list = array();
        foreach($props as $p)
        {
            $id = $p['BillingProperty']['id'];
            $title = !empty($p['BillingProperty']['title']) ? $p['BillingProperty']['title'] : ('Unit #' . $p['BillingProperty']['property_no']);
            $clientName = !empty($p['BillingClient']['name']) ? (' — ' . $p['BillingClient']['name']) : '';
            $list[$id] = $title . $clientName;
        }
        return $list;
    }
}
