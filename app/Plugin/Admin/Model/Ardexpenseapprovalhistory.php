<?php
class Ardexpenseapprovalhistory extends AppModel
{
    public $useTable = 'ardaccounts_expense_approval_history';
    public $cacheSources = false;
    public $belongsTo = array(
        'Request' => array('className' => 'Admin.Ardexpenseapprovalrequest', 'foreignKey' => 'request_id'),
        'Actor' => array('className' => 'User', 'foreignKey' => 'actor_user_id')
    );
}
