<?php
class Currency extends AppModel
{
  public $validationDomain = 'validation';
  public $actsAs = array('search-master.Searchable');
 public $validate = array('name' => array('alphaNumeric' => array('rule' => '/^[a-z0-9 .-_ ()]*$/i','required' => true,'allowEmpty'=>false,'message' => 'Only letters and numbers allowed'),
                                          'isUnique'=>array('rule' => 'isUnique','message' => 'Currency Name already exist.')),
                          'symbol' => array('notBlank' => array('rule' => 'notBlank','required' => true,'allowEmpty'=>false,'message' => 'Currency Symbol is required'),
                                            'maxLength' => array('rule' => array('maxLength',20),'message' => 'Currency Symbol should be less than or equal to 20 characters')),
                          );
  public $filterArgs = array('keyword' => array('type' => 'like','field'=>'name'));
}
?>
