<?php
App::uses('AppModel', 'Model');

class Ardvoucher extends AppModel
{
    public $useTable = 'ardaccounts_vouchers';
    public $hasMany = array(
        'Ardvoucheritem' => array(
            'className' => 'Admin.Ardvoucheritem',
            'foreignKey' => 'voucher_id',
            'dependent' => true
        )
    );
}
