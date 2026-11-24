<?php
App::uses('AppModel', 'Model');

class BillingManagerAppModel extends AppModel
{
    public $tablePrefix = 'billing_';
    public $actsAs = array('Containable');
    public $recursive = 2;
}
