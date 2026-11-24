<?php
class DealRental extends AppModel
{
    public $useTable = 'deal_rentals';
    public $cacheSources = false;
    public $belongsTo = array(
        'Deal' => array('className' => 'Admin.Deal', 'foreignKey' => 'deal_id'),
        'Client' => array('className' => 'Client', 'foreignKey' => 'client_id'),
        'Agency' => array('className' => 'Project', 'foreignKey' => 'project_id')
    );
    public $hasMany = array(
        'DealRentalReceipt' => array('className' => 'Admin.DealRentalReceipt', 'foreignKey' => 'deal_rental_id', 'dependent' => false),
        'DealRentalMonthlyExpense' => array('className' => 'Admin.DealRentalMonthlyExpense', 'foreignKey' => 'deal_rental_id', 'dependent' => false),
        'DealRentalSchedule' => array('className' => 'Admin.DealRentalSchedule', 'foreignKey' => 'deal_rental_id', 'dependent' => false),
        'DealRentalTenantCollection' => array('className' => 'Admin.DealRentalTenantCollection', 'foreignKey' => 'deal_rental_id', 'dependent' => false),
        'DealRentalMaintenance' => array('className' => 'Admin.DealRentalMaintenance', 'foreignKey' => 'deal_rental_id', 'dependent' => false),
        'DealRentalInspection' => array('className' => 'Admin.DealRentalInspection', 'foreignKey' => 'deal_rental_id', 'dependent' => false),
        'DealRentalIncrement' => array('className' => 'Admin.DealRentalIncrement', 'foreignKey' => 'deal_rental_id', 'dependent' => true)
    );
}
?>
