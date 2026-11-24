<?php
App::uses('AdminAppController', 'Admin.Controller');

class CrmreportsController extends AdminAppController
{
    public $name = 'Crmreports';

    public function daily()
    {
        $this->loadModel('Lead');
        $this->loadModel('LeadHistory');
        $this->loadModel('Admin.Contact');

        $today = date('Y-m-d');
        $leadConditions = array();
        $contactConditions = array();

        if(!$this->_isSuperAdminSession())
        {
            $leadPermission = $this->CustomFunction->userWisePermission(null, $this->luserId, $this->userType, $this->agencyId, 'Lead.project_id', 'Lead.user_id');
            if(!empty($leadPermission))
            {
                $leadConditions[] = $leadPermission;
            }
            $contactPermission = $this->CustomFunction->userWisePermission(null, $this->luserId, $this->userType, $this->agencyId, 'Contact.project_id', 'Contact.user_id');
            if(!empty($contactPermission))
            {
                $contactConditions[] = $contactPermission;
            }
        }

        if($this->Lead->hasField('created'))
        {
            $leadsToday = (int)$this->Lead->find('count', array(
                'conditions' => array_merge($leadConditions, array('DATE(Lead.created)' => $today))
            ));
            $recentLeads = (array)$this->Lead->find('all', array(
                'fields' => array('Lead.id','Lead.lead_name','Lead.lead_mobile','Lead.lead_email','Lead.created'),
                'conditions' => array_merge($leadConditions, array('DATE(Lead.created)' => $today)),
                'order' => array('Lead.id' => 'desc'),
                'limit' => 15,
                'recursive' => -1
            ));
        }
        else
        {
            $historyLeadConditions = array();
            if(!empty($leadConditions))
            {
                foreach($leadConditions as $leadCondition)
                {
                    if(is_array($leadCondition))
                    {
                        $historyLeadConditions[] = $leadCondition;
                    }
                }
            }
            $leadCountRows = (array)$this->Lead->find('all', array(
                'fields' => array('Lead.id'),
                'joins' => array(array(
                    'table' => 'lead_histories',
                    'alias' => 'LeadHistory',
                    'type' => 'INNER',
                    'conditions' => array('LeadHistory.lead_id=Lead.id')
                )),
                'conditions' => array_merge($historyLeadConditions, array('DATE(LeadHistory.created)' => $today)),
                'group' => array('Lead.id'),
                'recursive' => -1
            ));
            $leadsToday = count($leadCountRows);
            $recentLeads = (array)$this->Lead->find('all', array(
                'fields' => array('Lead.id','Lead.lead_name','Lead.lead_mobile','Lead.lead_email','MIN(LeadHistory.created) AS lead_created'),
                'joins' => array(array(
                    'table' => 'lead_histories',
                    'alias' => 'LeadHistory',
                    'type' => 'INNER',
                    'conditions' => array('LeadHistory.lead_id=Lead.id')
                )),
                'conditions' => array_merge($historyLeadConditions, array('DATE(LeadHistory.created)' => $today)),
                'group' => array('Lead.id','Lead.lead_name','Lead.lead_mobile','Lead.lead_email'),
                'order' => array('Lead.id' => 'desc'),
                'limit' => 15,
                'recursive' => -1
            ));
            foreach($recentLeads as $k=>$leadRow)
            {
                $recentLeads[$k]['Lead']['created'] = !empty($leadRow[0]['lead_created']) ? $leadRow[0]['lead_created'] : null;
            }
        }
        $contactsToday = (int)$this->Contact->find('count', array(
            'conditions' => array_merge($contactConditions, array('DATE(Contact.created)' => $today))
        ));
        $contactedToday = (int)$this->Lead->find('count', array(
            'conditions' => array_merge($leadConditions, array('Lead.last_contact_date' => $today))
        ));
        $followUpsToday = (int)$this->Lead->find('count', array(
            'conditions' => array_merge($leadConditions, array('DATE(Lead.follow_up)' => $today))
        ));

        $recentContacts = (array)$this->Contact->find('all', array(
            'fields' => array('Contact.id','Contact.name','Contact.mobile','Contact.email','Contact.created'),
            'conditions' => array_merge($contactConditions, array('DATE(Contact.created)' => $today)),
            'order' => array('Contact.id' => 'desc'),
            'limit' => 15,
            'recursive' => -1
        ));

        $scopeLabel = $this->_isSuperAdminSession() ? __('All Users') : __('My Records');

        $this->set('today', $today);
        $this->set('scopeLabel', $scopeLabel);
        $this->set('leadsToday', $leadsToday);
        $this->set('contactsToday', $contactsToday);
        $this->set('contactedToday', $contactedToday);
        $this->set('followUpsToday', $followUpsToday);
        $this->set('recentLeads', $recentLeads);
        $this->set('recentContacts', $recentContacts);
    }
}
