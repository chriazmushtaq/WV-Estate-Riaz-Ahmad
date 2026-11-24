<?php
class BallotingAllotment extends AppModel
{
    public $useTable = 'balloting_allotments';
    public $validationDomain = 'validation';

    public $belongsTo = array(
        'BallotingUnit' => array('className' => 'BallotingUnit', 'foreignKey' => 'balloting_unit_id'),
        'Deal' => array('className' => 'Deal', 'foreignKey' => 'deal_id'),
        'Client' => array('className' => 'Contact', 'foreignKey' => 'client_id'),
        'Project' => array('className' => 'Project', 'foreignKey' => 'project_id')
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
        'deal_id' => array(
            'numeric' => array(
                'rule' => 'numeric',
                'required' => true,
                'allowEmpty' => false,
                'message' => 'Please select a valid deal'
            )
        ),
        'balloting_unit_id' => array(
            'numeric' => array(
                'rule' => 'numeric',
                'required' => true,
                'allowEmpty' => false,
                'message' => 'Please select a valid balloting unit'
            )
        ),
        'allotment_date' => array(
            'date' => array(
                'rule' => 'date',
                'required' => true,
                'allowEmpty' => false,
                'message' => 'Please enter a valid allotment date'
            )
        )
    );

    public function beforeValidate($options = array())
    {
        if (isset($this->data[$this->alias]['allotment_date']) && strlen(trim((string)$this->data[$this->alias]['allotment_date'])) > 0) {
            $this->data[$this->alias]['allotment_date'] = $this->dateFormatBeforeSave($this->data[$this->alias]['allotment_date']);
        }
        return true;
    }
}

