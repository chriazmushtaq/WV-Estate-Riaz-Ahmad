<?php
class BallotingUnit extends AppModel
{
    public $useTable = 'balloting_units';
    public $validationDomain = 'validation';

    public $belongsTo = array(
        'Project' => array('className' => 'Project', 'foreignKey' => 'project_id'),
        'Category' => array('className' => 'Category', 'foreignKey' => 'category_id'),
        'Type' => array('className' => 'Type', 'foreignKey' => 'property_type_id'),
        'Unit' => array('className' => 'Unit', 'foreignKey' => 'unit_id')
    );

    public $validate = array(
        'project_id' => array(
            'numeric' => array(
                'rule' => 'numeric',
                'required' => true,
                'allowEmpty' => false,
                'message' => 'Please select a valid agency'
            )
        ),
        'allotment_no' => array(
            'notBlank' => array(
                'rule' => 'notBlank',
                'required' => true,
                'allowEmpty' => false,
                'message' => 'Please enter allotment number'
            )
        ),
        'street_address' => array(
            'notBlank' => array(
                'rule' => 'notBlank',
                'required' => true,
                'allowEmpty' => false,
                'message' => 'Please enter street address'
            )
        )
    );
}
