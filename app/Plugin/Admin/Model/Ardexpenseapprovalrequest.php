<?php
class Ardexpenseapprovalrequest extends AppModel
{
    public $useTable = 'ardaccounts_expense_approval_requests';
    public $cacheSources = false;
    public $belongsTo = array(
        'Agency' => array('className' => 'Project', 'foreignKey' => 'project_id'),
        'SourceLedger' => array('className' => 'Admin.Ardledger', 'foreignKey' => 'ledger_id'),
        'ExpenseLedger' => array('className' => 'Admin.Ardledger', 'foreignKey' => 'expense_ledger_id'),
        'Creator' => array('className' => 'User', 'foreignKey' => 'created_by'),
        'ManagerVerifier' => array('className' => 'User', 'foreignKey' => 'manager_verified_by'),
        'CeoApprover' => array('className' => 'User', 'foreignKey' => 'ceo_approved_by'),
        'PostedTransaction' => array('className' => 'Admin.Ardtransaction', 'foreignKey' => 'transaction_id')
    );
}
