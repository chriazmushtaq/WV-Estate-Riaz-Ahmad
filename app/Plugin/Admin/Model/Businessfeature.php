<?php
class Businessfeature extends AppModel
{
    public $useTable = 'business_features';
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
        'title' => array(
            'rule' => 'notBlank',
            'required' => true,
            'allowEmpty' => false,
            'message' => 'Please enter title'
        )
    );
}
