<?php
App::uses('BillingManagerAppModel', 'BillingManager.Model');

class BillingMeter extends BillingManagerAppModel
{
    public $useTable = 'meters';
    public $belongsTo = array(
        'BillingProperty' => array(
            'className' => 'BillingManager.BillingProperty',
            'foreignKey' => 'billing_property_id'
        )
    );
    public $hasMany = array(
        'BillingReading' => array(
            'className' => 'BillingManager.BillingReading',
            'foreignKey' => 'meter_id'
        )
    );
}
