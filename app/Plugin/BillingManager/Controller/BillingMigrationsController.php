<?php
App::uses('BillingManagerAppController', 'BillingManager.Controller');

class BillingMigrationsController extends BillingManagerAppController
{
    public $uses = array(
        'BillingManager.BillingSociety',
        'BillingManager.BillingClient',
        'BillingManager.BillingProperty',
        'BillingManager.BillingMeter',
        'BillingManager.BillingReading',
        'BillingManager.BillingBill',
        'BillingManager.BillingExtraCharge',
        'BillingManager.BillingTemplate',
        'BillingManager.BillingSetting'
    );

    public function index()
    {
        $societyId = $this->_getActiveSocietyId();

        $stats = array(
            'clients' => $this->BillingClient->find('count', array('conditions' => array('BillingClient.society_id' => $societyId))),
            'properties' => $this->BillingProperty->find('count', array('conditions' => array('BillingProperty.society_id' => $societyId))),
            'meters' => $this->BillingMeter->find('count', array('conditions' => array('BillingMeter.society_id' => $societyId))),
            'readings' => $this->BillingReading->find('count', array('conditions' => array('BillingReading.society_id' => $societyId))),
            'bills' => $this->BillingBill->find('count', array('conditions' => array('BillingBill.society_id' => $societyId))),
            'extra_charges' => $this->BillingExtraCharge->find('count', array('conditions' => array('BillingExtraCharge.society_id' => $societyId))),
            'templates' => $this->BillingTemplate->find('count', array('conditions' => array('BillingTemplate.society_id' => $societyId)))
        );

        $this->set('stats', $stats);
    }

    public function export_json()
    {
        $societyId = $this->_getActiveSocietyId();
        $export = array(
            'meta' => array(
                'plugin' => 'WhiteVillas Billing Manager',
                'version' => '20.1',
                'exported_at' => date('Y-m-d H:i:s')
            ),
            'tables' => array(
                'societies' => $this->BillingSociety->find('all', array('conditions' => array('BillingSociety.id' => $societyId))),
                'clients' => $this->BillingClient->find('all', array('conditions' => array('BillingClient.society_id' => $societyId))),
                'properties' => $this->BillingProperty->find('all', array('conditions' => array('BillingProperty.society_id' => $societyId))),
                'meters' => $this->BillingMeter->find('all', array('conditions' => array('BillingMeter.society_id' => $societyId))),
                'readings' => $this->BillingReading->find('all', array('conditions' => array('BillingReading.society_id' => $societyId))),
                'bills' => $this->BillingBill->find('all', array('conditions' => array('BillingBill.society_id' => $societyId))),
                'extra_charges' => $this->BillingExtraCharge->find('all', array('conditions' => array('BillingExtraCharge.society_id' => $societyId))),
                'templates' => $this->BillingTemplate->find('all', array('conditions' => array('BillingTemplate.society_id' => $societyId))),
                'settings' => $this->BillingSetting->find('all', array('conditions' => array('BillingSetting.society_id' => $societyId)))
            )
        );

        $json = json_encode($export, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $filename = 'whitevillas_billing_backup_' . date('Ymd_His') . '.json';

        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($json));
        echo $json;
        exit;
    }

    public function import_json()
    {
        if($this->request->is('post') && !empty($_FILES['backup_file']['tmp_name']))
        {
            $jsonContent = file_get_contents($_FILES['backup_file']['tmp_name']);
            $data = json_decode($jsonContent, true);

            if(!empty($data) && !empty($data['tables']) && is_array($data['tables']))
            {
                $importedCount = 0;
                $tables = $data['tables'];

                if(!empty($tables['clients']) && is_array($tables['clients'])) {
                    foreach($tables['clients'] as $row) {
                        if(!empty($row['BillingClient'])) {
                            $this->BillingClient->create();
                            $this->BillingClient->save($row['BillingClient']);
                            $importedCount++;
                        }
                    }
                }

                if(!empty($tables['properties']) && is_array($tables['properties'])) {
                    foreach($tables['properties'] as $row) {
                        if(!empty($row['BillingProperty'])) {
                            $this->BillingProperty->create();
                            $this->BillingProperty->save($row['BillingProperty']);
                            $importedCount++;
                        }
                    }
                }

                if(!empty($tables['meters']) && is_array($tables['meters'])) {
                    foreach($tables['meters'] as $row) {
                        if(!empty($row['BillingMeter'])) {
                            $this->BillingMeter->create();
                            $this->BillingMeter->save($row['BillingMeter']);
                            $importedCount++;
                        }
                    }
                }

                if(!empty($tables['readings']) && is_array($tables['readings'])) {
                    foreach($tables['readings'] as $row) {
                        if(!empty($row['BillingReading'])) {
                            $this->BillingReading->create();
                            $this->BillingReading->save($row['BillingReading']);
                            $importedCount++;
                        }
                    }
                }

                if(!empty($tables['bills']) && is_array($tables['bills'])) {
                    foreach($tables['bills'] as $row) {
                        if(!empty($row['BillingBill'])) {
                            $this->BillingBill->create();
                            $this->BillingBill->save($row['BillingBill']);
                            $importedCount++;
                        }
                    }
                }

                $this->Session->setFlash(sprintf(__('Successfully imported %d records from backup file.'), $importedCount), 'flash', array('alert' => 'success'));
            } else {
                $this->Session->setFlash(__('Invalid or corrupted backup JSON file.'), 'flash', array('alert' => 'danger'));
            }
        }
        return $this->redirect(array('action' => 'index'));
    }
}
