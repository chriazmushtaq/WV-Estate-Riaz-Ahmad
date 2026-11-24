<?php
class BallotingRun extends AppModel
{
    public $useTable = 'balloting_runs';
    public $validationDomain = 'validation';

    public $validate = array(
        'project_id' => array(
            'numeric' => array(
                'rule' => 'numeric',
                'required' => true,
                'allowEmpty' => false,
                'message' => 'Please select a valid agency'
            )
        ),
        'title' => array(
            'notBlank' => array(
                'rule' => 'notBlank',
                'required' => true,
                'allowEmpty' => false,
                'message' => 'Please enter run title'
            )
        ),
        'run_date' => array(
            'date' => array(
                'rule' => 'date',
                'required' => true,
                'allowEmpty' => false,
                'message' => 'Please enter run date'
            )
        )
    );

    public function beforeValidate($options = array())
    {
        if (isset($this->data[$this->alias]['run_date']) && strlen(trim((string)$this->data[$this->alias]['run_date'])) > 0) {
            $this->data[$this->alias]['run_date'] = $this->dateFormatBeforeSave($this->data[$this->alias]['run_date']);
        }
        return true;
    }
}

