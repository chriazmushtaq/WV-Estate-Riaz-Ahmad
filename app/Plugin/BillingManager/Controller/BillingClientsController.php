<?php
App::uses('BillingManagerAppController', 'BillingManager.Controller');

class BillingClientsController extends BillingManagerAppController
{
    public $uses = array(
        'BillingManager.BillingClient',
        'BillingManager.BillingProperty',
        'BillingManager.BillingBill',
        'Client'
    );

    public function index()
    {
        $societyId = $this->_getActiveSocietyId();
        $conditions = array('BillingClient.society_id' => $societyId);

        if(!empty($this->request->query['q']))
        {
            $q = trim($this->request->query['q']);
            $conditions['OR'] = array(
                'BillingClient.name LIKE' => "%{$q}%",
                'BillingClient.phone LIKE' => "%{$q}%",
                'BillingClient.email LIKE' => "%{$q}%",
                'BillingClient.cnic LIKE' => "%{$q}%"
            );
        }

        $this->paginate = array(
            'conditions' => $conditions,
            'order' => array('BillingClient.id' => 'ASC'),
            'limit' => 30,
            'contain' => array('Client', 'BillingProperty')
        );

        $clients = $this->paginate('BillingClient');
        $this->set('clients', $clients);
    }

    public function add()
    {
        $societyId = $this->_getActiveSocietyId();
        if($this->request->is('post'))
        {
            $this->request->data['BillingClient']['society_id'] = $societyId;
            $this->BillingClient->create();
            if($this->BillingClient->save($this->request->data))
            {
                $this->Session->setFlash(__('Billing consumer profile created successfully.'), 'flash', array('alert' => 'success'));
                return $this->redirect(array('action' => 'index'));
            }
        }
        $appClients = $this->Client->find('list', array('fields' => array('id', 'name')));
        $this->set('appClients', $appClients);
    }

    public function edit($id = null)
    {
        $this->BillingClient->id = (int)$id;
        if($this->request->is(array('post', 'put')))
        {
            if($this->BillingClient->save($this->request->data))
            {
                $this->Session->setFlash(__('Consumer profile updated successfully.'), 'flash', array('alert' => 'success'));
                return $this->redirect(array('action' => 'index'));
            }
        }
        else
        {
            $this->request->data = $this->BillingClient->read();
        }
        $appClients = $this->Client->find('list', array('fields' => array('id', 'name')));
        $this->set('appClients', $appClients);
    }

    public function ledger($id = null)
    {
        $client = $this->BillingClient->find('first', array(
            'conditions' => array('BillingClient.id' => (int)$id),
            'contain' => array('BillingProperty', 'BillingBill' => array('order' => array('BillingBill.id' => 'DESC')))
        ));
        if(empty($client))
        {
            $this->Session->setFlash(__('Client not found.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        $this->set('client', $client);
    }
}
