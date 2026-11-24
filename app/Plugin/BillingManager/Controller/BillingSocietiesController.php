<?php
App::uses('BillingManagerAppController', 'BillingManager.Controller');

class BillingSocietiesController extends BillingManagerAppController
{
    public $uses = array(
        'BillingManager.BillingSociety',
        'BillingManager.BillingProperty',
        'BillingManager.BillingMeter',
        'BillingManager.BillingClient',
        'BillingManager.BillingBill',
        'Project'
    );

    public function index()
    {
        $societies = $this->BillingSociety->find('all', array('order' => array('BillingSociety.id' => 'ASC')));
        
        $socData = array();
        foreach($societies as $s)
        {
            $id = $s['BillingSociety']['id'];
            $propsCount = $this->BillingProperty->find('count', array('conditions' => array('BillingProperty.society_id' => $id)));
            $metersCount = $this->BillingMeter->find('count', array('conditions' => array('BillingMeter.society_id' => $id)));
            $billsCount = $this->BillingBill->find('count', array('conditions' => array('BillingBill.society_id' => $id)));
            
            $socData[] = array(
                'society' => $s['BillingSociety'],
                'props_count' => $propsCount,
                'meters_count' => $metersCount,
                'bills_count' => $billsCount
            );
        }

        $this->set('socData', $socData);
    }

    public function add()
    {
        if($this->request->is('post'))
        {
            $this->BillingSociety->create();
            if($this->BillingSociety->save($this->request->data))
            {
                $societyId = $this->BillingSociety->id;
                $societyName = !empty($this->request->data['BillingSociety']['name']) ? $this->request->data['BillingSociety']['name'] : 'New Society';
                $this->_ensureSocietyBillingIncomeLedger($societyId, $societyName);

                $this->Session->setFlash(__('Society created successfully and Billing Income Ledger bootstrapped.'), 'flash', array('alert' => 'success'));
                return $this->redirect(array('action' => 'index'));
            }
            else
            {
                $this->Session->setFlash(__('Failed to save society.'), 'flash', array('alert' => 'danger'));
            }
        }
        $projects = $this->_getProjectDropdownList();
        $this->set('projects', $projects);
        $this->set('agencies', $projects);
    }

    public function edit($id = null)
    {
        $this->BillingSociety->id = (int)$id;
        if($this->request->is(array('post', 'put')))
        {
            if($this->BillingSociety->save($this->request->data))
            {
                $societyId = $this->BillingSociety->id;
                $societyName = !empty($this->request->data['BillingSociety']['name']) ? $this->request->data['BillingSociety']['name'] : 'Society ' . $societyId;
                $this->_ensureSocietyBillingIncomeLedger($societyId, $societyName);

                $this->Session->setFlash(__('Society profile updated successfully.'), 'flash', array('alert' => 'success'));
                return $this->redirect(array('action' => 'index'));
            }
        }
        else
        {
            $this->request->data = $this->BillingSociety->read();
        }
        $projects = $this->_getProjectDropdownList();
        $this->set('projects', $projects);
        $this->set('agencies', $projects);
    }

    public function delete($id = null)
    {
        $this->BillingSociety->delete((int)$id);
        $this->Session->setFlash(__('Society deleted.'), 'flash', array('alert' => 'success'));
        return $this->redirect(array('action' => 'index'));
    }

    protected function _getProjectDropdownList()
    {
        $list = array();
        
        // Show ONLY backend Projects (no agents)
        $projects = $this->Project->find('all', array('order' => array('Project.name' => 'ASC')));
        foreach($projects as $p)
        {
            $name = trim($p['Project']['name']);
            if(empty($name)) continue;

            $phone = !empty($p['Project']['phone']) ? $p['Project']['phone'] : (!empty($p['Project']['mobile']) ? $p['Project']['mobile'] : '');
            $email = !empty($p['Project']['email']) ? $p['Project']['email'] : '';
            
            // Check if society already exists with this name to pull address/code if available
            $existing = $this->BillingSociety->find('first', array(
                'conditions' => array('BillingSociety.name' => $name)
            ));

            $address = !empty($existing['BillingSociety']['address']) 
                ? $existing['BillingSociety']['address'] 
                : ($name . ', Bahria Town Lahore, Pakistan');

            $code = !empty($existing['BillingSociety']['code']) 
                ? $existing['BillingSociety']['code'] 
                : strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $name), 0, 4));

            $logoUrl = !empty($existing['BillingSociety']['logo_url']) 
                ? $existing['BillingSociety']['logo_url'] 
                : '/img/site-logo-20260824135207-8538.png';

            $list[$name] = array(
                'label' => $name,
                'name' => $name,
                'code' => $code,
                'phone' => !empty($phone) ? $phone : (!empty($existing['BillingSociety']['phone']) ? $existing['BillingSociety']['phone'] : ''),
                'email' => !empty($email) ? $email : (!empty($existing['BillingSociety']['email']) ? $existing['BillingSociety']['email'] : ''),
                'address' => $address,
                'logo_url' => $logoUrl
            );
        }

        return $list;
    }

    protected function _getAgencyDropdownList()
    {
        return $this->_getProjectDropdownList();
    }
}
