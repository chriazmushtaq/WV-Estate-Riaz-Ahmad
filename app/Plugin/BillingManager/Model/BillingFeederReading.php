<?php
App::uses('BillingManagerAppModel', 'BillingManager.Model');

class BillingFeederReading extends BillingManagerAppModel
{
    public $useTable = 'feeder_readings';

    public $belongsTo = array(
        'BillingFeeder' => array(
            'className' => 'BillingManager.BillingFeeder',
            'foreignKey' => 'feeder_id'
        ),
        'BillingSociety' => array(
            'className' => 'BillingManager.BillingSociety',
            'foreignKey' => 'society_id'
        )
    );
}
