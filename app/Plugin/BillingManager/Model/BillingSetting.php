<?php
App::uses('BillingManagerAppModel', 'BillingManager.Model');

class BillingSetting extends BillingManagerAppModel
{
    public $useTable = 'settings';

    public function getSetting($key, $default = null, $societyId = 1)
    {
        $row = $this->find('first', array(
            'conditions' => array(
                'BillingSetting.society_id' => $societyId,
                'BillingSetting.setting_key' => $key
            )
        ));
        if(!empty($row) && isset($row['BillingSetting']['setting_value']) && $row['BillingSetting']['setting_value'] !== '') {
            return $row['BillingSetting']['setting_value'];
        }
        if($societyId != 1) {
            $defaultRow = $this->find('first', array(
                'conditions' => array(
                    'BillingSetting.society_id' => 1,
                    'BillingSetting.setting_key' => $key
                )
            ));
            if(!empty($defaultRow) && isset($defaultRow['BillingSetting']['setting_value']) && $defaultRow['BillingSetting']['setting_value'] !== '') {
                return $defaultRow['BillingSetting']['setting_value'];
            }
        }
        return $default;
    }

    public function setSetting($key, $value, $societyId = 1)
    {
        $existing = $this->find('first', array(
            'conditions' => array(
                'BillingSetting.society_id' => $societyId,
                'BillingSetting.setting_key' => $key
            )
        ));
        $data = array(
            'society_id' => $societyId,
            'setting_key' => $key,
            'setting_value' => (string)$value
        );
        if(!empty($existing['BillingSetting']['id']))
        {
            $data['id'] = $existing['BillingSetting']['id'];
        }
        else
        {
            $this->create();
        }
        return $this->save(array('BillingSetting' => $data));
    }
}
