<?php
App::uses('BillingManagerAppModel', 'BillingManager.Model');

class BillingFeeder extends BillingManagerAppModel
{
    public $useTable = 'feeders';

    public $belongsTo = array(
        'BillingSociety' => array(
            'className' => 'BillingManager.BillingSociety',
            'foreignKey' => 'society_id'
        )
    );

    public $hasMany = array(
        'BillingFeederReading' => array(
            'className' => 'BillingManager.BillingFeederReading',
            'foreignKey' => 'feeder_id',
            'dependent' => true
        ),
        'BillingProperty' => array(
            'className' => 'BillingManager.BillingProperty',
            'foreignKey' => 'feeder_id'
        )
    );
}
