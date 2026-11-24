<?php
class DealRentalReceiptAllocation extends AppModel
{
    public $useTable = 'deal_rental_receipt_allocations';
    public $cacheSources = false;
    public $belongsTo = array(
        'DealRentalReceipt' => array('className' => 'Admin.DealRentalReceipt', 'foreignKey' => 'receipt_id'),
        'DealRentalSchedule' => array('className' => 'Admin.DealRentalSchedule', 'foreignKey' => 'schedule_id'),
        'DealRental' => array('className' => 'Admin.DealRental', 'foreignKey' => 'deal_rental_id')
    );
}
?>
