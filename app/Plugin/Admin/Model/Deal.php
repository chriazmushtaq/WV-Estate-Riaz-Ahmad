<?php
class Deal extends AppModel
{
  public $validationDomain = 'validation';
  public $actsAs = array('search-master.Searchable','Containable'=>array('Unit.name'));
  public $filterArgs = array('keyword' => array('type' => 'like','field'=>'Client.name'));
  public $belongsTo=array('Client',
                          'Property'=>array('className'=>'Property'),
                          'Plan',
                          'User',
                          'Unit'=>array('foreignKey'=>false,'conditions'=> array('Property.unit_id=Unit.id')),
                          'Type'=>array('foreignKey'=>false,'conditions'=> array('Property.type_id=Type.id')),
                          'Contract'=>array('foreignKey'=>false,'conditions'=> array('Property.contract_id=Contract.id')));
  public $validate = array('invoice_no' => array('alphaNumeric' => array('rule' => 'alphaNumericCustom','required' => true,'allowEmpty' => false,'message' => 'Only letters and numbers allowed'),
                                                 'isUnique'=>array('rule' => 'isUnique','message' => 'This Invoice No. has already been taken.')),
                           'date' => array('numeric'=>array('rule'=>'date','message'=>'Only Date')),
                           'discount' =>array('numeric'=>array('rule'=>'numeric','message'=>'Only Number','allowEmpty' => true)),
                           'plan_id' => array('rule' => 'numeric','message' => 'Please Select an item in the list'),
                           'user_id' => array('rule' => 'numeric','message' => 'Please Select an item in the list','allowEmpty' => true),
                           'nominee_name' => array('alphaNumeric' => array('rule' => 'alphaNumericCustom','required' => false,'allowEmpty' => true,'message' => 'Only letters and numbers allowed')),
                           'nominee_cnic' => array('cnicFormat' => array('rule' => array('custom', '/^\d{5}-\d{7}-\d{1}$/'),'required' => false,'allowEmpty' => true,'message' => 'Enter valid CNIC like 35301-1657035-3')),
                           'nominee_mobile' => array('alphaNumeric' => array('rule' => 'alphaNumericCustom','required' => false,'allowEmpty' => true,'message' => 'Only numbers allowed')),
                           'nominee_relation' => array('alphaNumeric' => array('rule' => 'alphaNumericCustom','required' => false,'allowEmpty' => true,'message' => 'Only letters allowed')),
                           'occupation' => array('maxLength' => array('rule' => array('maxLength', 150),'required' => false,'allowEmpty' => true,'message' => 'Maximum 150 characters allowed')),
                           'remarks' => array('alphaNumeric' => array('rule' => 'alphaNumericCustom','required' => true,'allowEmpty' => true,'message' => 'Only letters and numbers allowed')),
                           );
  public function beforeValidate($options = array())
  {
      if (!empty($this->data['Deal']['date'])) {
      $this->data['Deal']['date'] = $this->dateFormatBeforeSave($this->data['Deal']['date']);
      }
      if (!empty($this->data['Deal']['evocate_date'])) {
      $this->data['Deal']['evocate_date'] = $this->dateFormatBeforeSave($this->data['Deal']['evocate_date']);
      }
      if(isset($this->data['Deal']['nominee_cnic']) && strlen(trim((string)$this->data['Deal']['nominee_cnic']))>0){
      $digits = preg_replace('/\D+/', '', $this->data['Deal']['nominee_cnic']);
      if(strlen($digits)===13){
      $this->data['Deal']['nominee_cnic'] = substr($digits,0,5).'-'.substr($digits,5,7).'-'.substr($digits,12,1);
      }else{
      $this->data['Deal']['nominee_cnic'] = trim($this->data['Deal']['nominee_cnic']);
      }
      }
      return true;
  }
  public function afterFind($results, $primary = false)
  {
      return $this->_refreshLinkedPropertyNames($results, 'Deal');
  }
}
?>
