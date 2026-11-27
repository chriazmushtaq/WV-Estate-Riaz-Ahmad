<?php
class DealsPayment extends AppModel
{
  public $validationDomain = 'validation';
  public $belongsTo=array('Paymenttype');
  public $validate = array('payment_date' => array('numeric'=>array('rule'=>'date','message'=>'Only Date')),
                           'due_date' => array('numeric'=>array('rule'=>'date','message'=>'Only Date')),
                           'amount' =>array('numeric'=>array('rule'=>'numeric','message'=>'Only Number')),
                           'paymenttype_id' =>array('numeric'=>array('rule'=>'numeric','message'=>'Only Number')),
                           'remarks' => array('alphaNumeric' => array('rule' => 'alphaNumericCustom','required' => true,'allowEmpty' => true,'message' => 'Only letters and numbers allowed')));
  public function beforeValidate($options = array())
  {
      if (!empty($this->data['DealsPayment']['payment_date'])) {
      $this->data['DealsPayment']['payment_date'] = $this->dateFormatBeforeSave($this->data['DealsPayment']['payment_date']);
      }
      if (!empty($this->data['DealsPayment']['due_date'])) {
      $this->data['DealsPayment']['due_date'] = $this->dateFormatBeforeSave($this->data['DealsPayment']['due_date']);
      }
      return true;
  }

  public function beforeSave($options = array())
  {
      $alias = $this->alias;
      if (empty($this->id) && (empty($this->data[$alias]['id']) || (string)$this->data[$alias]['id'] === '0')) {
          try {
              $db = $this->getDataSource();
              if ($db && is_object($db)) {
                  $res = $db->query("SELECT MAX(`id`) AS max_id FROM `deals_payments`");
                  $maxId = !empty($res[0][0]['max_id']) ? (int)$res[0][0]['max_id'] : 0;
                  $this->data[$alias]['id'] = $maxId + 1;
              }
          } catch (Exception $e) {}
      }
      return parent::beforeSave($options);
  }
}
?>