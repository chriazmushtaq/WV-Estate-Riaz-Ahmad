<?php
App::uses('BillingManagerAppModel', 'BillingManager.Model');

class BillingPaymentRef extends BillingManagerAppModel
{
    public $useTable = 'payment_refs';

    public $belongsTo = array(
        'BillingBill' => array(
            'className' => 'BillingManager.BillingBill',
            'foreignKey' => 'bill_id'
        ),
        'BillingClient' => array(
            'className' => 'BillingManager.BillingClient',
            'foreignKey' => 'billing_client_id'
        )
    );
}
