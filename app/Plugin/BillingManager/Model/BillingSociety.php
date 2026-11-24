<?php
App::uses('BillingManagerAppModel', 'BillingManager.Model');

class BillingSociety extends BillingManagerAppModel
{
    public $useTable = 'societies';
    public $hasMany = array(
        'BillingClient' => array(
            'className' => 'BillingManager.BillingClient',
            'foreignKey' => 'society_id'
        ),
        'BillingProperty' => array(
            'className' => 'BillingManager.BillingProperty',
            'foreignKey' => 'society_id'
        )
    );
}
