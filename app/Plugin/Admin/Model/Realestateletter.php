<?php
class Realestateletter extends AppModel
{
    public $validationDomain = 'validation';
    public $useTable = 'real_estate_letters';
    public $actsAs = array('search-master.Searchable');
    public $filterArgs = array(
        'keyword' => array('type' => 'like', 'field' => 'Realestateletter.title')
    );
    public $belongsTo = array(
        'Deal' => array('className' => 'Deal', 'foreignKey' => 'deal_id'),
        'Template' => array('className' => 'Template', 'foreignKey' => 'template_id')
    );
    public $validate = array(
        'deal_id' => array(
            'rule' => 'numeric',
            'required' => true,
            'allowEmpty' => false,
            'message' => 'Please select a valid deal'
        ),
        'letter_type' => array(
            'rule' => 'notBlank',
            'required' => true,
            'allowEmpty' => false,
            'message' => 'Please select letter type'
        ),
        'title' => array(
            'rule' => 'notBlank',
            'required' => true,
            'allowEmpty' => false,
            'message' => 'Please enter letter title'
        ),
        'description' => array(
            'rule' => 'notBlank',
            'required' => true,
            'allowEmpty' => false,
            'message' => 'Please enter letter content'
        )
    );

    public function beforeValidate($options = array())
    {
        if (isset($this->data['Realestateletter']['letter_date']) && strlen(trim((string)$this->data['Realestateletter']['letter_date'])) > 0) {
            $this->data['Realestateletter']['letter_date'] = $this->dateFormatBeforeSave($this->data['Realestateletter']['letter_date']);
        }
        return true;
    }
}
?>
