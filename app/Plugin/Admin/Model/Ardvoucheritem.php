<?php
App::uses('AppModel', 'Model');

class Ardvoucheritem extends AppModel
{
    public $useTable = 'ardaccounts_voucher_items';
    public $belongsTo = array(
        'Ardvoucher' => array(
            'className' => 'Admin.Ardvoucher',
            'foreignKey' => 'voucher_id'
        ),
        'Ardledger' => array(
            'className' => 'Admin.Ardledger',
            'foreignKey' => 'ledger_id'
        )
    );
}
