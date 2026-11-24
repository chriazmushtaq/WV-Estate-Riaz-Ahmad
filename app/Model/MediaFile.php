<?php
class MediaFile extends AppModel
{
    public $validationDomain = 'validation';
    public $belongsTo = array(
        'MediaFolder' => array(
            'className' => 'MediaFolder',
            'foreignKey' => 'folder_id'
        )
    );
}
?>
