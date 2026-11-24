<?php
App::uses('BillingManagerAppController', 'BillingManager.Controller');

class BillingAuditLogsController extends BillingManagerAppController
{
    public $uses = array(
        'BillingManager.BillingAuditLog',
        'BillingManager.BillingTrash',
        'BillingManager.BillingBill',
        'BillingManager.BillingReading',
        'BillingManager.BillingMeter'
    );

    public function index()
    {
        $societyId = $this->_getActiveSocietyId();
        $logs = $this->BillingAuditLog->find('all', array(
            'conditions' => array('BillingAuditLog.society_id' => $societyId),
            'order' => array('BillingAuditLog.id' => 'DESC'),
            'limit' => 50
        ));

        $trashItems = $this->BillingTrash->find('all', array(
            'conditions' => array('BillingTrash.society_id' => $societyId),
            'order' => array('BillingTrash.id' => 'DESC'),
            'limit' => 50
        ));

        $this->set(compact('logs', 'trashItems'));
    }

    public function rollback($id = null)
    {
        $trash = $this->BillingTrash->findById((int)$id);
        if(!empty($trash))
        {
            $itemType = strtolower(trim($trash['BillingTrash']['item_type']));
            $itemData = @unserialize($trash['BillingTrash']['item_data']);
            if(empty($itemData) && !empty($trash['BillingTrash']['item_data'])) {
                $itemData = json_decode($trash['BillingTrash']['item_data'], true);
            }

            if(!empty($itemData) && is_array($itemData))
            {
                if($itemType === 'bill') {
                    $this->BillingBill->create();
                    $this->BillingBill->save(array('BillingBill' => $itemData));
                } elseif($itemType === 'reading') {
                    $this->BillingReading->create();
                    $this->BillingReading->save(array('BillingReading' => $itemData));
                } elseif($itemType === 'meter') {
                    $this->BillingMeter->create();
                    $this->BillingMeter->save(array('BillingMeter' => $itemData));
                }

                $this->BillingTrash->delete((int)$id);
                $this->Session->setFlash(__('Record successfully restored from trash!'), 'flash', array('alert' => 'success'));
            } else {
                $this->Session->setFlash(__('Unable to parse record snapshot for rollback.'), 'flash', array('alert' => 'danger'));
            }
        }
        return $this->redirect(array('action' => 'index'));
    }
}
