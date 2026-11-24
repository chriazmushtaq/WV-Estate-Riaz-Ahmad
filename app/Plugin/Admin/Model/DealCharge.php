<?php
class DealCharge extends AppModel
{
    public $useTable = 'deal_charges';
    public $belongsTo = array('Deal');
}
?>
