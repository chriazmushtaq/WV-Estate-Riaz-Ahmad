<?php
class PropertyPriceHistory extends AppModel
{
    public $validationDomain = 'validation';
    public $belongsTo = array('Property');
}
?>
