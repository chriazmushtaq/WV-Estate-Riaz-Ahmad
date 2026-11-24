<?php
App::uses('BillingManagerAppModel', 'BillingManager.Model');

class BillingClient extends BillingManagerAppModel
{
    public $useTable = 'clients';
    public $belongsTo = array(
        'Client' => array(
            'className' => 'Client',
            'foreignKey' => 'app_client_id'
        ),
        'BillingSociety' => array(
            'className' => 'BillingManager.BillingSociety',
            'foreignKey' => 'society_id'
        )
    );
    public $hasMany = array(
        'BillingProperty' => array(
            'className' => 'BillingManager.BillingProperty',
            'foreignKey' => 'billing_client_id'
        ),
        'BillingBill' => array(
            'className' => 'BillingManager.BillingBill',
            'foreignKey' => 'billing_client_id'
        )
    );
}
