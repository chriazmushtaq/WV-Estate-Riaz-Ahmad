<?php
class DealRentalIncrement extends AppModel
{
    public $useTable = 'deal_rental_increments';
    public $cacheSources = false;
    public $belongsTo = array(
        'DealRental' => array('className' => 'Admin.DealRental', 'foreignKey' => 'deal_rental_id')
    );
}
?>
