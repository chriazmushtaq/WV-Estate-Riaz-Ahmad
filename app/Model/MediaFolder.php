<?php
class MediaFolder extends AppModel
{
    public $validationDomain = 'validation';
    public $hasMany = array(
        'MediaFile' => array(
            'className' => 'MediaFile',
            'foreignKey' => 'folder_id'
        )
    );
}
?>
