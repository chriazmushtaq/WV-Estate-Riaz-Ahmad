<?php
App::uses('BillingManagerAppController', 'BillingManager.Controller');

class BillingPropertiesController extends BillingManagerAppController
{
    public $uses = array(
        'BillingManager.BillingProperty',
        'BillingManager.BillingClient',
        'BillingManager.BillingMeter',
        'BillingManager.BillingBill',
        'BillingManager.BillingSociety',
        'BillingManager.BillingSetting',
        'BillingManager.BillingFeeder'
    );

    public function beforeFilter()
    {
        parent::beforeFilter();
        if(isset($this->Auth))
        {
            $this->Auth->allow(array('ndc_clearance'));
        }
    }

    public function index()
    {
        $this->_syncProjectsWithSocieties();

        $conditions = array();
        if(!empty($this->request->query['society_id']))
        {
            $conditions['BillingProperty.society_id'] = (int)$this->request->query['society_id'];
        }
        if(!empty($this->request->query['q']))
        {
            $q = trim($this->request->query['q']);
            $conditions['OR'] = array(
                'BillingProperty.title LIKE' => "%{$q}%",
                'BillingProperty.property_no LIKE' => "%{$q}%",
                'BillingClient.name LIKE' => "%{$q}%"
            );
        }

        $this->paginate = array(
            'conditions' => $conditions,
            'order' => array('BillingProperty.id' => 'ASC'),
            'limit' => 40,
            'contain' => array('BillingClient', 'BillingMeter', 'BillingSociety')
        );

        $properties = $this->paginate('BillingProperty');
        $clients = $this->BillingClient->find('list', array('fields' => array('id', 'name')));
        $societies = $this->BillingSociety->find('list', array('fields' => array('id', 'name')));

        $this->set(compact('properties', 'clients', 'societies'));
    }

    public function add()
    {
        $projectSocMap = $this->_syncProjectsWithSocieties();

        if($this->request->is('post'))
        {
            if(empty($this->request->data['BillingProperty']['society_id']))
            {
                $this->request->data['BillingProperty']['society_id'] = 1;
            }

            // If app_client_id was selected from deal or contacts but not yet in billing_clients, auto-create
            if(!empty($this->request->data['BillingProperty']['deal_client_id']) && empty($this->request->data['BillingProperty']['billing_client_id']))
            {
                $dealClientId = (int)$this->request->data['BillingProperty']['deal_client_id'];
                $socId = (int)$this->request->data['BillingProperty']['society_id'];
                $bClientId = $this->_ensureBillingClient($dealClientId, $socId);
                if($bClientId)
                {
                    $this->request->data['BillingProperty']['billing_client_id'] = $bClientId;
                }
            }

            $this->BillingProperty->create();
            if($this->BillingProperty->save($this->request->data))
            {
                $this->Session->setFlash(__('Property unit created and linked successfully.'), 'flash', array('alert' => 'success'));
                return $this->redirect(array('action' => 'index'));
            }
        }

        $clients = $this->BillingClient->find('list', array('fields' => array('id', 'name'), 'order' => array('name' => 'ASC')));
        $societies = $this->BillingSociety->find('list', array('fields' => array('id', 'name')));
        $backendProperties = $this->_getBackendPropertiesFromDeals($projectSocMap);
        $agencyContacts = $this->_getAgencyContactsFromDeals($projectSocMap);

        $feeders = $this->BillingFeeder->find('list', array('fields' => array('id', 'name'), 'order' => array('id' => 'ASC')));
        $this->set(compact('clients', 'societies', 'backendProperties', 'agencyContacts', 'feeders'));
    }

    public function edit($id = null)
    {
        $projectSocMap = $this->_syncProjectsWithSocieties();
        $this->BillingProperty->id = (int)$id;

        if($this->request->is(array('post', 'put')))
        {
            if(!empty($this->request->data['BillingProperty']['deal_client_id']) && empty($this->request->data['BillingProperty']['billing_client_id']))
            {
                $dealClientId = (int)$this->request->data['BillingProperty']['deal_client_id'];
                $socId = !empty($this->request->data['BillingProperty']['society_id']) ? (int)$this->request->data['BillingProperty']['society_id'] : 1;
                $bClientId = $this->_ensureBillingClient($dealClientId, $socId);
                if($bClientId)
                {
                    $this->request->data['BillingProperty']['billing_client_id'] = $bClientId;
                }
            }

            if($this->BillingProperty->save($this->request->data))
            {
                $this->Session->setFlash(__('Property unit updated successfully.'), 'flash', array('alert' => 'success'));
                return $this->redirect(array('action' => 'index'));
            }
        }
        else
        {
            $this->request->data = $this->BillingProperty->read();
        }

        $clients = $this->BillingClient->find('list', array('fields' => array('id', 'name'), 'order' => array('name' => 'ASC')));
        $societies = $this->BillingSociety->find('list', array('fields' => array('id', 'name')));
        $backendProperties = $this->_getBackendPropertiesFromDeals($projectSocMap);
        $agencyContacts = $this->_getAgencyContactsFromDeals($projectSocMap);
        $feeders = $this->BillingFeeder->find('list', array('fields' => array('id', 'name'), 'order' => array('id' => 'ASC')));

        $this->set(compact('clients', 'societies', 'backendProperties', 'agencyContacts', 'feeders'));
    }

    public function delete($id = null)
    {
        $this->BillingProperty->delete((int)$id);
        $this->Session->setFlash(__('Property unit deleted.'), 'flash', array('alert' => 'success'));
        return $this->redirect(array('action' => 'index'));
    }

    protected function _syncProjectsWithSocieties()
    {
        $map = array(); // project_id => society_id
        try {
            $db = ConnectionManager::getDataSource('default');
            $allProjects = $db->fetchAll("SELECT id, name, phone, email FROM projects");
            $allSocieties = $db->fetchAll("SELECT id, name, code, phone, email FROM billing_societies");

            $nameToSocId = array();
            foreach($allSocieties as $sRow)
            {
                $s = isset($sRow['billing_societies']) ? $sRow['billing_societies'] : (isset($sRow['s']) ? $sRow['s'] : $sRow);
                $nameToSocId[strtolower(trim((string)$s['name']))] = (int)$s['id'];
            }

            foreach($allProjects as $pRow)
            {
                $pr = isset($pRow['projects']) ? $pRow['projects'] : (isset($pRow['p']) ? $pRow['p'] : $pRow);
                $pId = (int)$pr['id'];
                $pName = trim((string)$pr['name']);
                $pKey = strtolower($pName);

                if(isset($nameToSocId[$pKey]))
                {
                    $map[$pId] = $nameToSocId[$pKey];
                }
                else
                {
                    $code = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $pName), 0, 4));
                    if(empty($code)) $code = 'SOC' . $pId;
                    $phone = !empty($pr['phone']) ? $pr['phone'] : '';
                    $email = !empty($pr['email']) ? $pr['email'] : '';

                    $this->BillingSociety->create();
                    $saved = $this->BillingSociety->save(array('BillingSociety' => array(
                        'name' => $pName,
                        'code' => $code,
                        'phone' => $phone,
                        'email' => $email,
                        'status' => 'active'
                    )));
                    if($saved)
                    {
                        $newId = (int)$this->BillingSociety->id;
                        $nameToSocId[$pKey] = $newId;
                        $map[$pId] = $newId;
                    }
                }
            }
        } catch(Exception $e) {}
        return $map;
    }

    protected function _ensureBillingClient($appClientId, $socId = 1)
    {
        try {
            $db = ConnectionManager::getDataSource('default');
            $existing = $this->BillingClient->find('first', array(
                'conditions' => array('BillingClient.app_client_id' => $appClientId)
            ));
            if(!empty($existing))
            {
                return (int)$existing['BillingClient']['id'];
            }

            $cl = $db->fetchAll("SELECT * FROM clients WHERE id = " . (int)$appClientId);
            if(!empty($cl))
            {
                $row = isset($cl[0]['clients']) ? $cl[0]['clients'] : $cl[0];
                $this->BillingClient->create();
                $saved = $this->BillingClient->save(array('BillingClient' => array(
                    'app_client_id' => (int)$appClientId,
                    'society_id' => (int)$socId,
                    'name' => strtoupper(trim((string)$row['name'])),
                    'phone' => !empty($row['phone']) ? $row['phone'] : (!empty($row['mobile']) ? $row['mobile'] : ''),
                    'email' => !empty($row['email']) ? $row['email'] : '',
                    'address' => !empty($row['address']) ? $row['address'] : '',
                    'status' => 'active'
                )));
                if($saved)
                {
                    return (int)$this->BillingClient->id;
                }
            }
        } catch(Exception $e) {}
        return null;
    }

    protected function _getAgencyContactsFromDeals($projectSocMap = array())
    {
        try {
            $db = ConnectionManager::getDataSource('default');
            $sql = "SELECT c.id, c.project_id, c.name, c.phone, c.mobile, bc.id as billing_client_id,
                           GROUP_CONCAT(DISTINCT p.project_id) as deal_project_ids,
                           COUNT(d.id) as deal_count
                    FROM clients c
                    LEFT JOIN deals d ON d.client_id = c.id
                    LEFT JOIN properties p ON d.property_id = p.id
                    LEFT JOIN billing_clients bc ON (bc.app_client_id = c.id OR bc.name = c.name)
                    GROUP BY c.id
                    ORDER BY c.name ASC";

            $rows = $db->fetchAll($sql);
            $formatted = array();
            foreach($rows as $r)
            {
                $cl = isset($r['c']) ? $r['c'] : (isset($r['clients']) ? $r['clients'] : array());
                $bc = isset($r['bc']) ? $r['bc'] : (isset($r['billing_clients']) ? $r['billing_clients'] : array());
                $cnt = isset($r[0]['deal_count']) ? (int)$r[0]['deal_count'] : 0;
                $dealProjStr = isset($r[0]['deal_project_ids']) ? (string)$r[0]['deal_project_ids'] : '';
                
                $pId = (int)$cl['project_id'];
                $allProjIds = array_filter(array_unique(array_merge(array($pId), explode(',', $dealProjStr))));

                $socIds = array();
                foreach($allProjIds as $projId)
                {
                    $projId = (int)$projId;
                    if(isset($projectSocMap[$projId])) {
                        $socIds[] = $projectSocMap[$projId];
                    } else {
                        $socIds[] = ($projId == 22) ? 2 : 1;
                    }
                }
                if(empty($socIds)) {
                    $socIds = array(1, 2);
                }

                $bId = !empty($bc['billing_client_id']) ? (int)$bc['billing_client_id'] : (!empty($bc['id']) ? (int)$bc['id'] : null);

                $formatted[] = array(
                    'id' => (int)$cl['id'],
                    'project_id' => $pId,
                    'society_ids' => array_unique($socIds),
                    'name' => strtoupper(trim((string)$cl['name'])),
                    'phone' => !empty($cl['phone']) ? $cl['phone'] : (!empty($cl['mobile']) ? $cl['mobile'] : ''),
                    'billing_client_id' => $bId,
                    'is_undealed' => ($cnt === 0) ? 1 : 0
                );
            }
            return $formatted;
        } catch(Exception $e) {
            return array();
        }
    }

    protected function _getBackendPropertiesFromDeals($projectSocMap = array())
    {
        try {
            $db = ConnectionManager::getDataSource('default');
            $sql = "SELECT p.id, p.project_id, p.name, p.address, p.area, p.status, pr.name as project_name,
                           d.id as deal_id, d.invoice_no, d.client_id as deal_client_id, d.deal_status,
                           cl.name as deal_client_name, cl.phone as deal_client_phone, cl.mobile as deal_client_mobile,
                           bc.id as billing_client_id
                    FROM properties p
                    LEFT JOIN projects pr ON p.project_id = pr.id
                    LEFT JOIN deals d ON (d.property_id = p.id AND d.id = (
                        SELECT MAX(d2.id) FROM deals d2 WHERE d2.property_id = p.id
                    ))
                    LEFT JOIN clients cl ON d.client_id = cl.id
                    LEFT JOIN billing_clients bc ON (bc.app_client_id = cl.id OR bc.name = cl.name)
                    GROUP BY p.id
                    ORDER BY p.name ASC";

            $rows = $db->fetchAll($sql);
            $formatted = array();

            foreach($rows as $r)
            {
                $p = isset($r['p']) ? $r['p'] : (isset($r['properties']) ? $r['properties'] : array());
                $pr = isset($r['pr']) ? $r['pr'] : (isset($r['projects']) ? $r['projects'] : array());
                $d = isset($r['d']) ? $r['d'] : (isset($r['deals']) ? $r['deals'] : array());
                $cl = isset($r['cl']) ? $r['cl'] : (isset($r['clients']) ? $r['clients'] : array());
                $bc = isset($r['bc']) ? $r['bc'] : (isset($r['billing_clients']) ? $r['billing_clients'] : array());
                
                $pId = (int)$p['project_id'];
                $socId = isset($projectSocMap[$pId]) ? $projectSocMap[$pId] : (($pId == 22) ? 2 : 1);
                
                $dealId = !empty($d['deal_id']) ? (int)$d['deal_id'] : (!empty($d['id']) ? (int)$d['id'] : null);
                $dealClientId = !empty($d['deal_client_id']) ? (int)$d['deal_client_id'] : (!empty($d['client_id']) ? (int)$d['client_id'] : null);
                $dealClientName = !empty($cl['deal_client_name']) ? strtoupper(trim((string)$cl['deal_client_name'])) : (!empty($cl['name']) ? strtoupper(trim((string)$cl['name'])) : '');
                $dealClientPhone = !empty($cl['deal_client_phone']) ? trim((string)$cl['deal_client_phone']) : (!empty($cl['phone']) ? trim((string)$cl['phone']) : (!empty($cl['mobile']) ? trim((string)$cl['mobile']) : ''));
                $billingClientId = !empty($bc['billing_client_id']) ? (int)$bc['billing_client_id'] : (!empty($bc['id']) ? (int)$bc['id'] : null);
                $invoiceNo = !empty($d['invoice_no']) ? (string)$d['invoice_no'] : '';
                $hasDeal = ($dealId > 0 && !empty($dealClientName)) ? 1 : 0;

                $formatted[] = array(
                    'id' => (int)$p['id'],
                    'project_id' => $pId,
                    'society_id' => $socId,
                    'project_name' => !empty($pr['name']) ? $pr['name'] : 'Project ' . $pId,
                    'name' => trim((string)$p['name']),
                    'address' => trim((string)$p['address']),
                    'area' => !empty($p['area']) ? (string)$p['area'] : '',
                    'status' => !empty($p['status']) ? (string)$p['status'] : '',
                    'has_deal' => $hasDeal,
                    'deal_id' => $dealId,
                    'invoice_no' => $invoiceNo,
                    'deal_client_id' => $dealClientId,
                    'deal_client_name' => $dealClientName,
                    'deal_client_phone' => $dealClientPhone,
                    'billing_client_id' => $billingClientId
                );
            }
            return $formatted;
        } catch(Exception $e) {
            return array();
        }
    }

    public function ndc_clearance($propertyId = null)
    {
        $property = $this->BillingProperty->find('first', array(
            'conditions' => array('BillingProperty.id' => (int)$propertyId),
            'contain' => array('BillingClient', 'BillingMeter', 'BillingSociety')
        ));

        if(empty($property)) {
            $this->Session->setFlash(__('Property unit not found.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }

        $societyId = !empty($property['BillingProperty']['society_id']) ? (int)$property['BillingProperty']['society_id'] : 1;
        $enabled = $this->BillingSetting->getSetting('subm_feat_tenant_ndc', '1', $societyId);

        // Fetch all unpaid/partial bills for this property
        $unpaidBills = $this->BillingBill->find('all', array(
            'conditions' => array(
                'BillingBill.billing_property_id' => (int)$propertyId,
                'BillingBill.payment_status' => array('unpaid', 'partial')
            ),
            'order' => array('BillingBill.id' => 'DESC')
        ));

        $totalOutstanding = 0.0;
        foreach($unpaidBills as $ub) {
            $b = (float)$ub['BillingBill']['balance_amount'];
            if($b <= 0) $b = (float)$ub['BillingBill']['total_amount'] - (float)$ub['BillingBill']['paid_amount'];
            $totalOutstanding += max(0, $b);
        }

        // Fetch latest meter reading
        $this->loadModel('BillingManager.BillingReading');
        $latestReading = null;
        if(!empty($property['BillingMeter']['id'])) {
            $latestReading = $this->BillingReading->find('first', array(
                'conditions' => array('BillingReading.meter_id' => (int)$property['BillingMeter']['id']),
                'order' => array('BillingReading.id' => 'DESC')
            ));
        }

        // Handle POST form submission
        if($this->request->is(array('post', 'put'))) {
            $actionType = isset($this->request->data['action_type']) ? $this->request->data['action_type'] : 'update_info';
            
            $tenantName = isset($this->request->data['tenant_name']) ? trim($this->request->data['tenant_name']) : '';
            $tenantPhone = isset($this->request->data['tenant_phone']) ? trim($this->request->data['tenant_phone']) : '';
            $tenantCnic = isset($this->request->data['tenant_cnic']) ? trim($this->request->data['tenant_cnic']) : '';
            $exitReading = isset($this->request->data['departure_reading']) ? (float)$this->request->data['departure_reading'] : 0.0;

            $this->BillingProperty->id = (int)$propertyId;
            $saveData = array(
                'tenant_name' => $tenantName,
                'tenant_phone' => $tenantPhone,
                'tenant_cnic' => $tenantCnic
            );

            if($actionType === 'issue_ndc') {
                if($totalOutstanding > 0) {
                    $this->Session->setFlash(sprintf(__('Cannot issue NDC! Outstanding balance of PKR %s must be cleared first.'), number_format($totalOutstanding, 2)), 'flash', array('alert' => 'danger'));
                    return $this->redirect(array('action' => 'ndc_clearance', $propertyId));
                }

                $saveData['ndc_status'] = 'CLEARED';
                $saveData['ndc_date'] = date('Y-m-d H:i:s');

                if($exitReading > 0 && !empty($property['BillingMeter']['id'])) {
                    $prevRead = !empty($latestReading) ? (float)$latestReading['BillingReading']['current_reading'] : (float)$property['BillingMeter']['current_reading'];
                    $units = max(0, $exitReading - $prevRead);
                    $this->BillingReading->create();
                    $this->BillingReading->save(array('BillingReading' => array(
                        'society_id' => $societyId,
                        'meter_id' => (int)$property['BillingMeter']['id'],
                        'billing_property_id' => (int)$propertyId,
                        'reading_date' => date('Y-m-d'),
                        'reading_month' => date('Y-m'),
                        'previous_reading' => $prevRead,
                        'current_reading' => $exitReading,
                        'units_consumed' => $units,
                        'reader_name' => 'Move-Out Inspector',
                        'is_billed' => 1
                    )));
                }

                $this->BillingProperty->save($saveData);
                $this->Session->setFlash(__('Official Utility No Demand Certificate (NDC) issued and stamped successfully!'), 'flash', array('alert' => 'success'));
                return $this->redirect(array('action' => 'ndc_clearance', $propertyId, '?' => array('print' => 1)));
            } else {
                $this->BillingProperty->save($saveData);
                $this->Session->setFlash(__('Tenant information updated successfully.'), 'flash', array('alert' => 'success'));
                return $this->redirect(array('action' => 'ndc_clearance', $propertyId));
            }
        }

        $isPrint = !empty($this->request->query['print']);
        if($isPrint) {
            $this->layout = false;
        }

        $society = $this->BillingSociety->findById($societyId);
        $socData = !empty($society['BillingSociety']) ? $society['BillingSociety'] : array(
            'name' => 'WHITE VILLAS LAHORE',
            'address' => 'Awami Block White Villas Bahria Town Lahore, PAKISTAN',
            'phone' => '+92339 1101156',
            'email' => 'billing@whitevillas.com.pk'
        );

        $logoBase64 = '';
        $logoPath = WWW_ROOT . 'img' . DS . 'logo.png';
        if(file_exists($logoPath)) {
            $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
        }

        $this->set(compact('property', 'societyId', 'socData', 'unpaidBills', 'totalOutstanding', 'latestReading', 'enabled', 'isPrint', 'logoBase64'));
    }
}
