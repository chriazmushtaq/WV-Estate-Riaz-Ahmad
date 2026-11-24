<?php
class Ardhremployee extends AppModel
{
    public $useTable = 'ardaccounts_hr_employees';
    public $actsAs = array(
        'Upload.Upload' => array(
            'profile_photo' => array(
                'fields' => array('dir' => 'profile_photo_dir'),
                'pathMethod' => 'flat',
                'thumbnailSizes' => array('' => '180x180'),
                'thumbnailMethod' => 'php',
                'thumbnailPrefixStyle' => false,
                'thumbnailType' => true
            ),
            'cnic_front' => array(
                'fields' => array('dir' => 'cnic_front_dir'),
                'pathMethod' => 'flat',
                'thumbnailSizes' => array('' => '360x220'),
                'thumbnailMethod' => 'php',
                'thumbnailPrefixStyle' => false,
                'thumbnailType' => true
            ),
            'cnic_back' => array(
                'fields' => array('dir' => 'cnic_back_dir'),
                'pathMethod' => 'flat',
                'thumbnailSizes' => array('' => '360x220'),
                'thumbnailMethod' => 'php',
                'thumbnailPrefixStyle' => false,
                'thumbnailType' => true
            )
        )
    );

    public $validate = array(
        'profile_photo' => array(
            'isValidExtension' => array(
                'rule' => array('isValidExtension', array('jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'jfif', 'JPG', 'JPEG', 'PNG', 'GIF', 'BMP', 'WEBP', 'JFIF'), false),
                'allowEmpty' => true,
                'message' => 'File does not have a valid extension'
            ),
            'isValidMimeType' => array(
                'rule' => array('isValidMimeType', array('image/jpeg', 'image/jpg', 'image/png', 'image/bmp', 'image/gif', 'image/webp'), false),
                'allowEmpty' => true,
                'message' => 'You must supply a valid image file.'
            )
        ),
        'cnic_front' => array(
            'isValidExtension' => array(
                'rule' => array('isValidExtension', array('jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'jfif', 'JPG', 'JPEG', 'PNG', 'GIF', 'BMP', 'WEBP', 'JFIF'), false),
                'allowEmpty' => true,
                'message' => 'File does not have a valid extension'
            ),
            'isValidMimeType' => array(
                'rule' => array('isValidMimeType', array('image/jpeg', 'image/jpg', 'image/png', 'image/bmp', 'image/gif', 'image/webp'), false),
                'allowEmpty' => true,
                'message' => 'You must supply a valid image file.'
            )
        ),
        'cnic_back' => array(
            'isValidExtension' => array(
                'rule' => array('isValidExtension', array('jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'jfif', 'JPG', 'JPEG', 'PNG', 'GIF', 'BMP', 'WEBP', 'JFIF'), false),
                'allowEmpty' => true,
                'message' => 'File does not have a valid extension'
            ),
            'isValidMimeType' => array(
                'rule' => array('isValidMimeType', array('image/jpeg', 'image/jpg', 'image/png', 'image/bmp', 'image/gif', 'image/webp'), false),
                'allowEmpty' => true,
                'message' => 'You must supply a valid image file.'
            )
        )
    );

    public function beforeSave($options = array())
    {
        $row = isset($this->data[$this->alias]) ? $this->data[$this->alias] : array();
        $map = array(
            'profile_photo' => 'profile_photo_dir',
            'cnic_front' => 'cnic_front_dir',
            'cnic_back' => 'cnic_back_dir'
        );
        $dirDefaults = array(
            'profile_photo_dir' => 'hr_employee',
            'cnic_front_dir' => 'hr_employee_cnic_front',
            'cnic_back_dir' => 'hr_employee_cnic_back'
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
}

