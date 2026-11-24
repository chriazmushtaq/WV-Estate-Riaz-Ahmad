<?php
App::uses('BillingManagerAppModel', 'BillingManager.Model');

class BillingLateFeeLog extends BillingManagerAppModel
{
    public $useTable = 'late_fee_logs';
}
