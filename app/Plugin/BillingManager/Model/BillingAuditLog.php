<?php
App::uses('BillingManagerAppModel', 'BillingManager.Model');

class BillingAuditLog extends BillingManagerAppModel
{
    public $useTable = 'audit_logs';
}
