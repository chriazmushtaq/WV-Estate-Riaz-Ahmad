<?php
App::uses('BillingManagerAppModel', 'BillingManager.Model');

class BillingBillAdjustment extends BillingManagerAppModel
{
    public $tablePrefix = '';
    public $useTable = 'billing_bill_adjustments';

    public $belongsTo = array(
        'BillingBill' => array(
            'className' => 'BillingManager.BillingBill',
            'foreignKey' => 'bill_id'
        ),
        'BillingProperty' => array(
            'className' => 'BillingManager.BillingProperty',
            'foreignKey' => 'billing_property_id'
        ),
        'BillingClient' => array(
            'className' => 'BillingManager.BillingClient',
            'foreignKey' => 'billing_client_id'
        )
    );
}
