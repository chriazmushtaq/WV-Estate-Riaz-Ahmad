<?php
App::uses('BillingManagerAppController', 'BillingManager.Controller');

class BillingExtraChargesController extends BillingManagerAppController
{
    public $uses = array(
        'BillingManager.BillingExtraCharge',
        'BillingManager.BillingSociety',
        'BillingManager.BillingProperty'
    );

    public function index()
    {
        $activeSocId = $this->_getActiveSocietyId();
        $selectedSocId = isset($this->request->query['society_id']) ? $this->request->query['society_id'] : $activeSocId;

        $conditions = array();
        if ($selectedSocId !== 'all' && !empty($selectedSocId)) {
            $conditions['BillingExtraCharge.society_id'] = (int)$selectedSocId;
        }

        $charges = $this->BillingExtraCharge->find('all', array(
            'conditions' => $conditions,
            'order' => array('BillingExtraCharge.id' => 'ASC')
        ));

        $targetSocId = ($selectedSocId === 'all' || empty($selectedSocId)) ? $activeSocId : (int)$selectedSocId;

        $this->loadModel('BillingManager.BillingSetting');
        $maintenanceHeadsRaw = $this->BillingSetting->getSetting('subm_maintenance_heads', array(), $targetSocId);
        if (is_string($maintenanceHeadsRaw)) {
            $un = @unserialize($maintenanceHeadsRaw);
            if ($un !== false) $maintenanceHeadsRaw = $un;
        }
        $maintenanceHeads = is_array($maintenanceHeadsRaw) ? $maintenanceHeadsRaw : array();

        $areaSlabsRaw = $this->BillingSetting->getSetting('subm_area_slabs', array(), $targetSocId);
        if (is_string($areaSlabsRaw)) {
            $un = @unserialize($areaSlabsRaw);
            if ($un !== false) $areaSlabsRaw = $un;
        }
        $areaSlabs = is_array($areaSlabsRaw) ? $areaSlabsRaw : array();

        // Fetch Live Real-Time Property Types directly from `types` Database Table (/admin/Types/index/)
        $dbTypes = array();
        try {
            $db = ConnectionManager::getDataSource('default');
            $typesRows = $db->fetchAll("SELECT DISTINCT name FROM types WHERE name IS NOT NULL AND name != '' ORDER BY name ASC");
            foreach ($typesRows as $tr) {
                $tName = trim(isset($tr['types']['name']) ? $tr['types']['name'] : (isset($tr[0]['name']) ? $tr[0]['name'] : ''));
                if ($tName !== '') {
                    $dbTypes[] = $tName;
                }
            }
        } catch (Exception $e) {}

        // Preserve any previously saved custom types in maintenance heads
        $savedTypes = array();
        if (!empty($maintenanceHeads)) {
            foreach ($maintenanceHeads as $mh) {
                if (!empty($mh['property_type'])) {
                    $savedTypes[] = trim($mh['property_type']);
                }
            }
        }

        $propertyTypes = array_values(array_unique(array_merge($dbTypes, $savedTypes, array('Apartment', 'House', 'Plot', 'Commercial'))));

        // Fetch Live Real-Time Area Units directly from `units` Database Table
        $dbAreaUnits = array();
        try {
            $db = ConnectionManager::getDataSource('default');
            $unitRows = $db->fetchAll("SELECT DISTINCT name FROM units WHERE name IS NOT NULL AND name != '' ORDER BY name ASC");
            foreach ($unitRows as $ur) {
                $uName = trim(isset($ur['units']['name']) ? $ur['units']['name'] : (isset($ur[0]['name']) ? $ur[0]['name'] : ''));
                if ($uName !== '') {
                    $dbAreaUnits[] = $uName;
                }
            }
        } catch (Exception $e) {}

        // Preserve any previously saved area units in maintenance heads
        $savedUnits = array();
        if (!empty($maintenanceHeads)) {
            foreach ($maintenanceHeads as $mh) {
                if (!empty($mh['unit'])) {
                    $savedUnits[] = trim($mh['unit']);
                }
            }
        }

        $areaUnits = array_values(array_unique(array_merge($dbAreaUnits, $savedUnits, array('Marla', 'Kanal', 'Sqft', 'Sq. Ft', 'Sq. mtr', 'Sq.Yard'))));

        $societies = $this->BillingSociety->find('list', array('fields' => array('id', 'name')));
        $properties = $this->BillingProperty->find('list', array('fields' => array('id', 'title')));

        $this->set(compact('charges', 'societies', 'properties', 'selectedSocId', 'activeSocId', 'maintenanceHeads', 'areaSlabs', 'propertyTypes', 'areaUnits'));
    }

    public function save_maintenance_rules()
    {
        if ($this->request->is(array('post', 'put'))) {
            $socId = !empty($this->request->data['society_id']) ? (int)$this->request->data['society_id'] : $this->_getActiveSocietyId();
            $this->loadModel('BillingManager.BillingSetting');

            // 1. Process Maintenance Breakdown (Conditional by Area)
            $mHeads = array();
            if (!empty($this->request->data['m_head_name'])) {
                $names = $this->request->data['m_head_name'];
                $pTypes = isset($this->request->data['m_head_property_type']) ? $this->request->data['m_head_property_type'] : array();
                $minAreas = isset($this->request->data['m_head_min_area']) ? $this->request->data['m_head_min_area'] : array();
                $maxAreas = isset($this->request->data['m_head_max_area']) ? $this->request->data['m_head_max_area'] : array();
                $units = isset($this->request->data['m_head_unit']) ? $this->request->data['m_head_unit'] : array();
                $amounts = isset($this->request->data['m_head_amount']) ? $this->request->data['m_head_amount'] : array();

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

            // 2. Process Area Slabs
            $slabs = array();
            if (!empty($this->request->data['slab_meter_type'])) {
                $mTypes = $this->request->data['slab_meter_type'];
                $pTypes = isset($this->request->data['slab_property_type']) ? $this->request->data['slab_property_type'] : array();
                $aMins = isset($this->request->data['slab_area_min']) ? $this->request->data['slab_area_min'] : array();
                $aMaxs = isset($this->request->data['slab_area_max']) ? $this->request->data['slab_area_max'] : array();
                $uMins = isset($this->request->data['slab_min_units']) ? $this->request->data['slab_min_units'] : array();
                $uMaxs = isset($this->request->data['slab_max_units']) ? $this->request->data['slab_max_units'] : array();
                $rates = isset($this->request->data['slab_rate']) ? $this->request->data['slab_rate'] : array();

                foreach ($mTypes as $idx => $mType) {
                    $slabs[] = array(
                        'meter_type' => trim($mType),
                        'property_type' => isset($pTypes[$idx]) ? trim($pTypes[$idx]) : '',
                        'area_min' => isset($aMins[$idx]) ? trim($aMins[$idx]) : '',
                        'area_max' => isset($aMaxs[$idx]) ? trim($aMaxs[$idx]) : '',
                        'min_units' => isset($uMins[$idx]) ? trim($uMins[$idx]) : '0',
                        'max_units' => isset($uMaxs[$idx]) ? trim($uMaxs[$idx]) : '',
                        'rate' => isset($rates[$idx]) ? (float)$rates[$idx] : 0.0
                    );
                }
            }

            $this->BillingSetting->saveSetting('subm_maintenance_heads', serialize($mHeads), $socId);
            $this->BillingSetting->saveSetting('subm_area_slabs', serialize($slabs), $socId);

            $this->Session->setFlash(__('Society Maintenance Breakdown Rules saved separately for this society.'), 'flash', array('alert' => 'success'));
            return $this->redirect(array('action' => 'index', '?' => array('society_id' => $socId)));
        }
        return $this->redirect(array('action' => 'index'));
    }

    public function copy_maintenance_rules()
    {
        if ($this->request->is(array('post', 'put'))) {
            $fromSocId = (int)$this->request->data['from_society_id'];
            $toSocId = (int)$this->request->data['to_society_id'];

            if ($fromSocId > 0 && $toSocId > 0 && $fromSocId !== $toSocId) {
                $this->loadModel('BillingManager.BillingSetting');
                $sourceHeads = $this->BillingSetting->getSetting('subm_maintenance_heads', '', $fromSocId);
                $sourceSlabs = $this->BillingSetting->getSetting('subm_area_slabs', '', $fromSocId);

                if (!empty($sourceHeads)) {
                    $this->BillingSetting->saveSetting('subm_maintenance_heads', $sourceHeads, $toSocId);
                }
                if (!empty($sourceSlabs)) {
                    $this->BillingSetting->saveSetting('subm_area_slabs', $sourceSlabs, $toSocId);
                }

                $this->Session->setFlash(__('Maintenance breakdown rules copied successfully to target society.'), 'flash', array('alert' => 'success'));
                return $this->redirect(array('action' => 'index', '?' => array('society_id' => $toSocId)));
            }
        }
        return $this->redirect(array('action' => 'index'));
    }

    public function add()
    {
        $activeSocId = $this->_getActiveSocietyId();
        if($this->request->is('post'))
        {
            if(empty($this->request->data['BillingExtraCharge']['society_id']))
            {
                $this->request->data['BillingExtraCharge']['society_id'] = $activeSocId;
            }
            $this->BillingExtraCharge->create();
            if($this->BillingExtraCharge->save($this->request->data))
            {
                $this->Session->setFlash(__('Charge head created successfully.'), 'flash', array('alert' => 'success'));
                return $this->redirect(array('action' => 'index'));
            }
            else
            {
                $this->Session->setFlash(__('Failed to save charge head.'), 'flash', array('alert' => 'danger'));
            }
        }
        $societies = $this->BillingSociety->find('list', array('fields' => array('id', 'name')));
        $properties = $this->BillingProperty->find('list', array('fields' => array('id', 'title'), 'order' => array('title' => 'ASC')));
        $this->set(compact('societies', 'properties', 'activeSocId'));
    }

    public function edit($id = null)
    {
        $this->BillingExtraCharge->id = (int)$id;
        if($this->request->is(array('post', 'put')))
        {
            if($this->BillingExtraCharge->save($this->request->data))
            {
                $this->Session->setFlash(__('Charge head updated successfully.'), 'flash', array('alert' => 'success'));
                return $this->redirect(array('action' => 'index'));
            }
            else
            {
                $this->Session->setFlash(__('Failed to update charge head.'), 'flash', array('alert' => 'danger'));
            }
        }
        else
        {
            $this->request->data = $this->BillingExtraCharge->read();
        }
        $societies = $this->BillingSociety->find('list', array('fields' => array('id', 'name')));
        $properties = $this->BillingProperty->find('list', array('fields' => array('id', 'title'), 'order' => array('title' => 'ASC')));
        $this->set(compact('societies', 'properties'));
    }

    public function delete($id = null)
    {
        $this->BillingExtraCharge->delete((int)$id);
        $this->Session->setFlash(__('Charge head deleted.'), 'flash', array('alert' => 'success'));
        return $this->redirect(array('action' => 'index'));
    }
}
