<?php
class BallotingSetting extends AppModel
{
    public $useTable = 'balloting_settings';
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
        'min_paid_percent' => array(
            'numeric' => array(
                'rule' => 'numeric',
                'required' => true,
                'allowEmpty' => false,
                'message' => 'Please enter minimum paid percent'
            )
        ),
        'max_overdue_installments' => array(
            'numeric' => array(
                'rule' => 'numeric',
                'required' => true,
                'allowEmpty' => false,
                'message' => 'Please enter overdue installment limit'
            )
        ),
        'max_overdue_days' => array(
            'numeric' => array(
                'rule' => 'numeric',
                'required' => true,
                'allowEmpty' => false,
                'message' => 'Please enter overdue days limit'
            )
        )
    );

    public function beforeValidate($options = array())
    {
        if (isset($this->data[$this->alias]['booking_date_from']) && strlen(trim((string)$this->data[$this->alias]['booking_date_from'])) > 0) {
            $this->data[$this->alias]['booking_date_from'] = $this->dateFormatBeforeSave($this->data[$this->alias]['booking_date_from']);
        }
        if (isset($this->data[$this->alias]['booking_date_to']) && strlen(trim((string)$this->data[$this->alias]['booking_date_to'])) > 0) {
            $this->data[$this->alias]['booking_date_to'] = $this->dateFormatBeforeSave($this->data[$this->alias]['booking_date_to']);
        }
        return true;
    }
}

