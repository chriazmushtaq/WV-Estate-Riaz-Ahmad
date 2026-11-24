<?php
class Emailtemplate extends AppModel
{
  public $cacheSources = false;
  public $validationDomain = 'validation';
  public $actsAs = array('search-master.Searchable');
  public $filterArgs = array('keyword' => array('type' => 'like','field'=>'Emailtemplate.name'));
  public $validate = array('name' => array('alphaNumeric' => array('rule' => 'alphaNumericCustom','required' => true,'allowEmpty' => false,'message' => 'Only letters and numbers allowed')),
                           );

  public function ensureOwnershipSchema()
  {
    $db = $this->getDataSource();
    $table = $db->fullTableName($this, false, false);
    $refreshSchema = false;
    $columns = array(
      'user_id' => "ALTER TABLE `{$table}` ADD `user_id` INT(11) NULL DEFAULT NULL AFTER `type`",
      'project_id' => "ALTER TABLE `{$table}` ADD `project_id` INT(11) NULL DEFAULT NULL AFTER `user_id`"
    );
    foreach($columns as $columnName => $sql)
    {
      $exists = $db->query("SHOW COLUMNS FROM `{$table}` LIKE '".$columnName."'");
      if(empty($exists))
      {
        $db->query($sql);
        $refreshSchema = true;
      }
    }
    if($refreshSchema)
    {
      $this->schema(true);
    }
  }

  public function scopedCustomConditions($userId = 0, $isGlobalAdmin = false, $alias = null)
  {
    $alias = $alias ? (string)$alias : $this->alias;
    $conditions = array(
      'OR' => array(
        array($alias.'.type' => null),
        array($alias.'.type' => '')
      )
    );
    if(!$isGlobalAdmin)
    {
      $conditions[$alias.'.user_id'] = (int)$userId;
    }
    return $conditions;
  }
}
?>
