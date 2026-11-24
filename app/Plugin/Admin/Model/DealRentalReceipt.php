<?php
class DealRentalReceipt extends AppModel
{
    public $useTable = 'deal_rental_receipts';
    public $belongsTo = array(
        'DealRental' => array('className' => 'Admin.DealRental', 'foreignKey' => 'deal_rental_id'),
        'Deal' => array('className' => 'Admin.Deal', 'foreignKey' => 'deal_id'),
        'Client' => array('className' => 'Client', 'foreignKey' => 'client_id'),
        'Agency' => array('className' => 'Project', 'foreignKey' => 'project_id')
    );
    public $hasMany = array(
        'DealRentalReceiptAllocation' => array('className' => 'Admin.DealRentalReceiptAllocation', 'foreignKey' => 'receipt_id', 'dependent' => true)
    );
}
?>
