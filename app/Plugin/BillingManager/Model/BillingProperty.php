<?php
App::uses('BillingManagerAppModel', 'BillingManager.Model');

class BillingProperty extends BillingManagerAppModel
{
    public $useTable = 'properties';
    public $belongsTo = array(
        'Property' => array(
            'className' => 'Property',
            'foreignKey' => 'app_property_id'
        ),
        'BillingClient' => array(
            'className' => 'BillingManager.BillingClient',
            'foreignKey' => 'billing_client_id'
        ),
        'BillingSociety' => array(
            'className' => 'BillingManager.BillingSociety',
            'foreignKey' => 'society_id'
        )
    );
    public $hasMany = array(
        'BillingMeter' => array(
            'className' => 'BillingManager.BillingMeter',
            'foreignKey' => 'billing_property_id'
        ),
        'BillingBill' => array(
            'className' => 'BillingManager.BillingBill',
            'foreignKey' => 'billing_property_id'
        )
    );
}
