<?php
class MyPayment extends AppModel
{
  public $validationDomain = 'validation';
  public $useTable = "deals_payments";
  public $belongsTo = array(
    'Paymenttype',
    'Deal' => array('foreignKey' => 'deal_id'),
    'Property' => array('foreignKey' => false, 'type' => 'LEFT', 'conditions' => array('Deal.property_id = Property.id'))
  );
}
?>