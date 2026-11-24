<?php
App::uses('BillingManagerAppModel', 'BillingManager.Model');

class BillingReading extends BillingManagerAppModel
{
    public $useTable = 'readings';
    public $belongsTo = array(
        'BillingMeter' => array(
            'className' => 'BillingManager.BillingMeter',
            'foreignKey' => 'meter_id'
        ),
        'BillingProperty' => array(
            'className' => 'BillingManager.BillingProperty',
            'foreignKey' => 'billing_property_id'
        )
    );
}
