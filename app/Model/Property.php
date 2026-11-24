<?php
class Property extends AppModel
{
    public $validationDomain = 'validation';
    public $belongsTo=array('Type','Project','Unit','Location','User','Contract');
    public $hasMany=array(
        'PropertiesPhoto'=>array('order'=>array('PropertiesPhoto.sort_order'=>'ASC','PropertiesPhoto.id'=>'ASC')),
        'PropertiesDocument'=>array('order'=>array('PropertiesDocument.sort_order'=>'ASC','PropertiesDocument.id'=>'ASC')),
        'PropertiesFloorplan'=>array('order'=>array('PropertiesFloorplan.sort_order'=>'ASC','PropertiesFloorplan.id'=>'ASC'))
    );
    public $actsAs = array('search-master.Searchable');
    public $filterArgs = array('type' => array('type'=>'query','method'=>'CreationDateRangeCondition'),
                               'contract' => array('type'=>'query','method'=>'CreationDateRangeCondition'),
                               'location' => array('type'=>'query','method'=>'CreationDateRangeCondition'),
                               'keyword' => array('type'=>'query','method'=>'CreationDateRangeCondition'),
                               'start_price' => array('type'=>'query','method'=>'CreationDateRangeCondition'),
                               'end_price' => array('type'=>'query','method'=>'CreationDateRangeCondition'),
                               'bedroom' => array('type'=>'query','method'=>'CreationDateRangeCondition'),
                               'bathroom' => array('type'=>'query','method'=>'CreationDateRangeCondition'),
                               'uniq_id' => array('type'=>'query','method'=>'CreationDateRangeCondition'),
                               'agent' => array('type'=>'query','method'=>'CreationDateRangeCondition'),
                               );
}
