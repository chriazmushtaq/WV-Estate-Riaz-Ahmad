<?php
class Property extends AppModel
{
  public $validationDomain = 'validation';
  public $hasAndBelongsToMany=array('Feature'=>array('className'=>'Feature',
                                                     'joinTable' => 'properties_features',
                                                     'foreignKey' => 'property_id',
                                                     'associationForeignKey' => 'feature_id'));
  public $hasMany=array(
    'PropertiesPhoto'=>array('order'=>array('PropertiesPhoto.sort_order'=>'ASC','PropertiesPhoto.id'=>'ASC')),
    'PropertiesDocument'=>array('order'=>array('PropertiesDocument.sort_order'=>'ASC','PropertiesDocument.id'=>'ASC')),
    'PropertiesFloorplan'=>array('order'=>array('PropertiesFloorplan.sort_order'=>'ASC','PropertiesFloorplan.id'=>'ASC'))
  );
  public $belongsTo=array('Project','Unit','Location','Type','Contract','User');
  public $actsAs = array('search-master.Searchable');
   public $filterArgs = array('keyword' => array('type' => 'like','field'=>'Property.name'),
                             'skeyword' => array('field'=>'Property.status'),
                             'location' => array('type' => 'like','field'=>'Location.name'),
                             'general' => array('type'=>'query','method'=>'CreationDateRangeCondition'),
                             'publish' => array('type'=>'query','method'=>'CreationDateRangeCondition'),
                             'contract' => array('type'=>'query','method'=>'CreationDateRangeCondition'),
                             'type' => array('type'=>'query','method'=>'CreationDateRangeCondition'),
                             'contract' => array('type'=>'query','method'=>'CreationDateRangeCondition'),
                             'location' => array('type'=>'query','method'=>'CreationDateRangeCondition'),
                             'keyword' => array('type'=>'query','method'=>'CreationDateRangeCondition'),
                             'start_price' => array('type'=>'query','method'=>'CreationDateRangeCondition'),
                             'end_price' => array('type'=>'query','method'=>'CreationDateRangeCondition'),
                             'bedroom' => array('type'=>'query','method'=>'CreationDateRangeCondition'),
                             'bathroom' => array('type'=>'query','method'=>'CreationDateRangeCondition'),
                             'uniq_id' => array('type'=>'query','method'=>'CreationDateRangeCondition'),
                             'agent' => array('type'=>'query','method'=>'CreationDateRangeCondition'),
                             'area' => array('type'=>'query','method'=>'CreationDateRangeCondition'),
                              );
  public $validate = array('location_id' =>array('numeric'=>array('rule'=>'numeric','message'=>'Please select any location','required' => true,'allowEmpty' => false)),
                           'type_id' =>array('numeric'=>array('rule'=>'numeric','message'=>'Please select any type','required' => true,'allowEmpty' => false)),
                           'contract_id' =>array('numeric'=>array('rule'=>'numeric','message'=>'Please select any contract','required' => true,'allowEmpty' => false)),
                           'name' => array('notEmpty' => array('rule' => 'notEmpty','required' => true,'allowEmpty' => false,'message' => 'Please enter property title')),
                           'area' =>array('numeric'=>array('rule'=>'numeric','message'=>'Only Number','required' => true,'allowEmpty' => false)),
                           'price' =>array('numeric'=>array('rule'=>'numeric','message'=>'Only Number','required' => true,'allowEmpty' => false)),
                           'bedroom' =>array('numeric'=>array('rule'=>'numeric','message'=>'Only Number','required' => false,'allowEmpty' => true)),
                           'bathroom' =>array('numeric'=>array('rule'=>'numeric','message'=>'Only Number','required' => false,'allowEmpty' => true)),
                           'address' => array('notEmpty' => array('rule' => 'notEmpty','required' => false,'allowEmpty' => true,'message' => 'Please enter a valid address.')),
                           'commission_type' => array('notEmpty' => array('rule' => 'notEmpty','required' => false,'allowEmpty' => true,'message' => 'Please select commission type.')),
                           'agent_commission' =>array('numeric'=>array('rule'=>'numeric','message'=>'Only Number','required' => false,'allowEmpty' => true)),
                           );
}
?>
