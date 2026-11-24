<?php
class DealRentalMonthlyExpense extends AppModel
{
    public $useTable = 'deal_rental_monthly_expenses';
    public $belongsTo = array(
        'DealRental' => array('className' => 'Admin.DealRental', 'foreignKey' => 'deal_rental_id'),
        'Deal' => array('className' => 'Admin.Deal', 'foreignKey' => 'deal_id'),
        'Client' => array('className' => 'Client', 'foreignKey' => 'client_id'),
        'Agency' => array('className' => 'Project', 'foreignKey' => 'project_id')
    );
}
?>
