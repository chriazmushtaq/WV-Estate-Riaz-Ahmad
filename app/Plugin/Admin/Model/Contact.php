<?php
class Contact extends AppModel
{
  public $useTable="clients";
  public $validationDomain = 'validation';
  public $actsAs = array(
      'search-master.Searchable',
      'Upload.Upload' => array(
          'photo' => array(
              'fields' => array('dir' => 'dirt'),
              'pathMethod' => 'flat',
              'thumbnailSizes' => array('' => '150x150'),
              'thumbnailMethod' => 'php',
              'thumbnailPrefixStyle' => false,
              'thumbnailType' => true
          ),
          'id_front' => array(
              'fields' => array('dir' => 'id_front_dir'),
              'pathMethod' => 'flat',
              'thumbnailSizes' => array('' => '300x200'),
              'thumbnailMethod' => 'php',
              'thumbnailPrefixStyle' => false,
              'thumbnailType' => true
          ),
          'id_back' => array(
              'fields' => array('dir' => 'id_back_dir'),
              'pathMethod' => 'flat',
              'thumbnailSizes' => array('' => '300x200'),
              'thumbnailMethod' => 'php',
              'thumbnailPrefixStyle' => false,
              'thumbnailType' => true
          ),
          'side_photo' => array(
              'fields' => array('dir' => 'side_photo_dir'),
              'pathMethod' => 'flat',
              'thumbnailSizes' => array('' => '300x300'),
              'thumbnailMethod' => 'php',
              'thumbnailPrefixStyle' => false,
              'thumbnailType' => true
          )
      )
  );
  public $belongsTo = array('Category');
  
  
  public $filterArgs = array(
      'cid' => array('field'=>'Contact.category_id'),
      'cname' => array('type' => 'like', 'field'=>'Contact.name'),
      'kyc_status' => array('type' => 'query', 'method' => 'filterKycStatus'),
  );
  
  public $validate = array(
      'name' => array(
          'notBlank' => array('rule' => 'notBlank', 'required' => true, 'allowEmpty' => false, 'message' => 'Name is required')
      ),
      'category_id' => array(
          'numeric' => array('rule' => 'numeric', 'message' => 'Only Number', 'allowEmpty' => true, 'required' => false)
      ),
      'address' => array(
          'alphaNumeric' => array('rule' => 'alphaNumericCustom', 'required' => false, 'allowEmpty' => true, 'message' => 'Only letters and numbers allowed')
      ),
      'district' => array(
          'alphaNumeric' => array('rule' => 'alphaNumericCustom', 'required' => false, 'allowEmpty' => true, 'message' => 'Only letters and numbers allowed')
      ),
      'state' => array(
          'alphaNumeric' => array('rule' => 'alphaNumericCustom', 'required' => false, 'allowEmpty' => true, 'message' => 'Only letters and numbers allowed')
      ),
      'gender' => array(
          'inList' => array('rule' => array('inList', array('Male','Female','Other')), 'allowEmpty' => true, 'message' => 'Please select valid gender')
      ),
      'pincode' => array(
          'cnicFormat' => array(
              'rule' => array('custom', '/^[0-9\-\s]{4,20}$/'),
              'message' => 'Enter valid CNIC or postal code',
              'allowEmpty' => true
          )
      ),
      'email' => array(
          'email' => array('rule' => 'email', 'message' => 'Enter a valid email', 'allowEmpty' => true),
          'isUnique' => array('rule' => 'isUnique', 'message' => 'This Email already exists! Try another one', 'allowEmpty' => true)
      ),
      'mobile' => array(
          'notBlank' => array('rule' => 'notBlank', 'message' => 'Cell Phone is required', 'allowEmpty' => false, 'required' => true)
      ),
      'phone' => array(
          'phoneFormat' => array('rule' => array('custom', '/^[0-9\+\-\s\(\)]{4,25}$/'), 'required' => false, 'allowEmpty' => true, 'message' => 'Please enter valid phone number')
      ),
      'photo' => array(
          'isValidExtension' => array('rule' => array('isValidExtension', array('jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'jfif', 'JPG', 'JPEG', 'PNG', 'GIF', 'BMP', 'WEBP', 'JFIF'), false), 'allowEmpty' => true, 'message' => 'File does not have a valid extension'),
          'isValidMimeType' => array('rule' => array('isValidMimeType', array('image/jpeg', 'image/jpg', 'image/png', 'image/x-png', 'image/pjpeg', 'image/bmp', 'image/x-ms-bmp', 'image/gif', 'image/webp'), false), 'allowEmpty' => true, 'message' => 'You must supply a valid image file.')
      ),
      'id_front' => array(
          'isValidExtension' => array('rule' => array('isValidExtension', array('jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'jfif', 'JPG', 'JPEG', 'PNG', 'GIF', 'BMP', 'WEBP', 'JFIF'), false), 'allowEmpty' => true, 'message' => 'File does not have a valid extension'),
          'isValidMimeType' => array('rule' => array('isValidMimeType', array('image/jpeg', 'image/jpg', 'image/png', 'image/x-png', 'image/pjpeg', 'image/bmp', 'image/x-ms-bmp', 'image/gif', 'image/webp'), false), 'allowEmpty' => true, 'message' => 'You must supply a valid image file.')
      ),
      'id_back' => array(
          'isValidExtension' => array('rule' => array('isValidExtension', array('jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'jfif', 'JPG', 'JPEG', 'PNG', 'GIF', 'BMP', 'WEBP', 'JFIF'), false), 'allowEmpty' => true, 'message' => 'File does not have a valid extension'),
          'isValidMimeType' => array('rule' => array('isValidMimeType', array('image/jpeg', 'image/jpg', 'image/png', 'image/x-png', 'image/pjpeg', 'image/bmp', 'image/x-ms-bmp', 'image/gif', 'image/webp'), false), 'allowEmpty' => true, 'message' => 'You must supply a valid image file.')
      ),
      'side_photo' => array(
          'isValidExtension' => array('rule' => array('isValidExtension', array('jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'jfif', 'JPG', 'JPEG', 'PNG', 'GIF', 'BMP', 'WEBP', 'JFIF'), false), 'allowEmpty' => true, 'message' => 'File does not have a valid extension'),
          'isValidMimeType' => array('rule' => array('isValidMimeType', array('image/jpeg', 'image/jpg', 'image/png', 'image/x-png', 'image/pjpeg', 'image/bmp', 'image/x-ms-bmp', 'image/gif', 'image/webp'), false), 'allowEmpty' => true, 'message' => 'You must supply a valid image file.')
      )
  );
  public function beforeSave($options = array())
  {
      $row = isset($this->data[$this->alias]) ? $this->data[$this->alias] : array();
      $map = array(
          'photo' => 'dirt',
          'id_front' => 'id_front_dir',
          'id_back' => 'id_back_dir',
          'side_photo' => 'side_photo_dir'
      );
      $dirDefaults = array(
          'dirt' => 'contact',
          'id_front_dir' => 'contact',
          'id_back_dir' => 'contact',
          'side_photo_dir' => 'contact'
      );
      foreach($map as $fileField => $dirField)
      {
          $hasFile = false;
          if(isset($row[$fileField]) && is_array($row[$fileField]) && !empty($row[$fileField]['name']))
          {
              $hasFile = true;
          }
          elseif(isset($row[$fileField]) && is_string($row[$fileField]) && trim($row[$fileField]) !== '')
          {
              $hasFile = true;
          }
          if($hasFile && (!isset($row[$dirField]) || trim((string)$row[$dirField]) === ''))
          {
              $this->data[$this->alias][$dirField] = $dirDefaults[$dirField];
          }
      }
      return parent::beforeSave($options);
  }

  public function afterSave($created, $options = array())
  {
      parent::afterSave($created, $options);
      if (!empty($this->id)) {
          $current = $this->find('first', array(
              'conditions' => array($this->alias . '.' . $this->primaryKey => $this->id),
              'recursive' => -1
          ));
          if (!empty($current[$this->alias])) {
              $row = $current[$this->alias];
              $map = array(
                  'photo' => 'dirt',
                  'id_front' => 'id_front_dir',
                  'id_back' => 'id_back_dir',
                  'side_photo' => 'side_photo_dir'
              );
              $updates = array();
              $db = $this->getDataSource();
              foreach ($map as $fileCol => $dirCol) {
                  if (!empty($row[$fileCol]) && (empty($row[$dirCol]) || trim((string)$row[$dirCol]) === '')) {
                      $updates[$dirCol] = $db->value('contact', 'string');
                  }
              }
              if (!empty($updates)) {
                  $this->updateAll($updates, array($this->alias . '.' . $this->primaryKey => $this->id));
              }
          }
      }
  }

  public function filterKycStatus($data = array())
  {
      if (empty($data['kyc_status'])) {
          return array();
      }
      $status = strtolower(trim((string)$data['kyc_status']));
      if ($status === 'verified') {
          return array(
              'Contact.pincode IS NOT NULL',
              "TRIM(Contact.pincode) != ''",
              'Contact.photo IS NOT NULL',
              "TRIM(Contact.photo) != ''",
              'Contact.id_front IS NOT NULL',
              "TRIM(Contact.id_front) != ''",
              'Contact.id_back IS NOT NULL',
              "TRIM(Contact.id_back) != ''"
          );
      } elseif ($status === 'pending') {
          return array(
              'OR' => array(
                  'Contact.pincode IS NULL',
                  "TRIM(Contact.pincode) = ''"
              ),
              'AND' => array(
                  array('OR' => array('Contact.photo IS NULL', "TRIM(Contact.photo) = ''")),
                  array('OR' => array('Contact.id_front IS NULL', "TRIM(Contact.id_front) = ''")),
                  array('OR' => array('Contact.id_back IS NULL', "TRIM(Contact.id_back) = ''"))
              )
          );
      } elseif ($status === 'partial') {
          return array(
              'OR' => array(
                  array('AND' => array('Contact.pincode IS NOT NULL', "TRIM(Contact.pincode) != ''")),
                  array('AND' => array('Contact.photo IS NOT NULL', "TRIM(Contact.photo) != ''")),
                  array('AND' => array('Contact.id_front IS NOT NULL', "TRIM(Contact.id_front) != ''")),
                  array('AND' => array('Contact.id_back IS NOT NULL', "TRIM(Contact.id_back) != ''"))
              ),
              'AND' => array(
                  'OR' => array(
                      array('Contact.pincode IS NULL'),
                      array("TRIM(Contact.pincode) = ''"),
                      array('Contact.photo IS NULL'),
                      array("TRIM(Contact.photo) = ''"),
                      array('Contact.id_front IS NULL'),
                      array("TRIM(Contact.id_front) = ''"),
                      array('Contact.id_back IS NULL'),
                      array("TRIM(Contact.id_back) = ''")
                  )
              )
          );
      }
      return array();
  }
}
?>
