<?php
App::uses('BillingManagerAppModel', 'BillingManager.Model');

class BillingBill extends BillingManagerAppModel
{
    public $useTable = 'bills';
    public $belongsTo = array(
        'BillingProperty' => array(
            'className' => 'BillingManager.BillingProperty',
            'foreignKey' => 'billing_property_id'
        ),
        'BillingClient' => array(
            'className' => 'BillingManager.BillingClient',
            'foreignKey' => 'billing_client_id'
        ),
        'BillingTemplate' => array(
            'className' => 'BillingManager.BillingTemplate',
            'foreignKey' => 'template_id'
        )
    );
}
