<?php
App::uses('AppModel', 'Model');

class FacebookAccount extends AppModel
{
    public $useTable = 'facebook_accounts';
    public $primaryKey = 'id';

    public $belongsTo = array(
        'Project' => array(
            'className' => 'Project',
            'foreignKey' => 'project_id'
        ),
        'Status' => array(
            'className' => 'Status',
            'foreignKey' => 'default_status_id'
        )
    );

    public $validate = array(
        'account_name' => array(
            'rule' => 'notBlank',
            'message' => 'Please provide an account or page name.'
        ),
        'page_access_token' => array(
            'rule' => 'notBlank',
            'message' => 'Page Access Token is required.'
        )
    );

    public function getActiveAccounts()
    {
        return $this->find('all', array(
            'conditions' => array(
                'OR' => array(
                    'FacebookAccount.is_active' => 1,
                    'FacebookAccount.is_active IS NULL'
                )
            ),
            'order' => array('FacebookAccount.id' => 'ASC'),
            'recursive' => 0
        ));
    }
}