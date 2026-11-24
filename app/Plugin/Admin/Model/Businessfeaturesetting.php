<?php
class Businessfeaturesetting extends AppModel
{
    public $useTable = 'business_feature_settings';
    public $cacheSources = false;
    public $validationDomain = 'validation';

    public $belongsTo = array(
        'Project' => array('className' => 'Project', 'foreignKey' => 'project_id')
    );

    public $validate = array(
        'project_id' => array(
            'rule' => 'numeric',
            'required' => true,
            'allowEmpty' => false,
            'message' => 'Please select a valid agency'
        ),
        'feature_key' => array(
            'rule' => 'notBlank',
            'required' => true,
            'allowEmpty' => false,
            'message' => 'Feature key is required'
        ),
        'setting_key' => array(
            'rule' => 'notBlank',
            'required' => true,
            'allowEmpty' => false,
            'message' => 'Please enter setting key'
        )
    );
}
