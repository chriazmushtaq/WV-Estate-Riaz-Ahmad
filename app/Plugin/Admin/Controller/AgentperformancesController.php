<?php
class AgentperformancesController extends AdminAppController
{
    public $helpers = array('Html','Form','Session','Number');
    public $components = array('Session','CustomFunction');
    public $uses = array('Admin.User','Property','Admin.Lead','Admin.Deal','Admin.PropertyLeadClick','Admin.Agency');

    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->_ensurePropertyLeadClicksTable();
    }

    public function index()
    {
        $dateFrom = isset($this->request->query['date_from']) && trim((string)$this->request->query['date_from']) !== '' ? trim((string)$this->request->query['date_from']) : date('Y-m-01');
        $dateTo = isset($this->request->query['date_to']) && trim((string)$this->request->query['date_to']) !== '' ? trim((string)$this->request->query['date_to']) : date('Y-m-d');
        $projectId = isset($this->request->query['project_id']) ? (int)$this->request->query['project_id'] : 0;
        $agentId = isset($this->request->query['agent_id']) ? (int)$this->request->query['agent_id'] : 0;
        if(strtoupper(trim((string)$this->userType)) === 'AGY')
        {
            $projectId = (int)$this->agencyId;
        }
        if(strtoupper(trim((string)$this->userType)) === 'AGT')
        {
            $agentId = (int)$this->luserId;
            $projectId = (int)$this->agencyId;
        }

        $agencyOptions = $this->CustomFunction->agencyWiseList($this->userType, $this->agencyId);
        $agentOptions = $this->_agentOptions($projectId);
        $agents = $this->_agentRows($projectId, $agentId);
        $agentRows = array();
        foreach($agents as $agent)
        {
            $user = $agent['User'];
            $agentRows[] = $this->_agentPerformanceRow((int)$user['id'], $user, $dateFrom, $dateTo);
        }
        $agencyRows = $this->_agencyPerformanceRows($agencyOptions, $dateFrom, $dateTo);
        $totals = array('leads'=>0,'properties'=>0,'views'=>0,'whatsapp'=>0,'calls'=>0,'emails'=>0,'deals'=>0);
        foreach($agentRows as $row)
        {
            foreach($totals as $key => $value)
            {
                $totals[$key] += isset($row[$key]) ? (int)$row[$key] : 0;
            }
        }
        $totals['conversion_rate'] = $totals['leads'] > 0 ? round(($totals['deals'] / $totals['leads']) * 100, 1) : 0;
        $this->set(compact('dateFrom','dateTo','projectId','agentId','agencyOptions','agentOptions','agentRows','agencyRows','totals'));
    }

    protected function _agentOptions($projectId)
    {
        $conditions = array($this->User->assignableScopeConditions((int)$projectId, 'User'));
        if(strtoupper(trim((string)$this->userType)) === 'AGT')
        {
            $conditions['User.id'] = (int)$this->luserId;
        }
        return (array)$this->User->find('list', array(
            'fields'=>array('User.id','User.name'),
            'conditions'=>$conditions,
            'order'=>array('User.name'=>'asc'),
            'recursive'=>-1
        ));
    }

    protected function _agentRows($projectId, $agentId)
    {
        $conditions = array($this->User->assignableScopeConditions((int)$projectId, 'User'));
        if((int)$agentId > 0)
        {
            $conditions['User.id'] = (int)$agentId;
        }
        return (array)$this->User->find('all', array(
            'fields'=>array('User.id','User.name','User.mobile','User.phone','User.email','User.project_id'),
            'conditions'=>$conditions,
            'order'=>array('User.name'=>'asc'),
            'limit'=>100,
            'recursive'=>-1
        ));
    }

    protected function _dateConditions($alias, $field, $dateFrom, $dateTo)
    {
        $conditions = array();
        if($dateFrom !== '')
        {
            $conditions[$alias.'.'.$field.' >='] = $dateFrom.' 00:00:00';
        }
        if($dateTo !== '')
        {
            $conditions[$alias.'.'.$field.' <='] = $dateTo.' 23:59:59';
        }
        return $conditions;
    }

    protected function _modelDateConditions($modelAlias, $preferredFields, $dateFrom, $dateTo)
    {
        $preferredFields = (array)$preferredFields;
        if(empty($this->{$modelAlias}) || !is_object($this->{$modelAlias}))
        {
            return array();
        }
        foreach($preferredFields as $field)
        {
            $field = trim((string)$field);
            if($field === '' || !$this->{$modelAlias}->hasField($field))
            {
                continue;
            }
            $fieldType = strtolower((string)$this->{$modelAlias}->getColumnType($field));
            if($fieldType === 'date')
            {
                $conditions = array();
                if($dateFrom !== '')
                {
                    $conditions[$modelAlias.'.'.$field.' >='] = $dateFrom;
                }
                if($dateTo !== '')
                {
                    $conditions[$modelAlias.'.'.$field.' <='] = $dateTo;
                }
                return $conditions;
            }
            return $this->_dateConditions($modelAlias, $field, $dateFrom, $dateTo);
        }
        return array();
    }

    protected function _agentPerformanceRow($agentId, $user, $dateFrom, $dateTo)
    {
        $propertyCount = (int)$this->Property->find('count', array('conditions'=>array('Property.user_id'=>$agentId),'recursive'=>-1));
        $viewsRow = $this->Property->find('first', array('fields'=>array('SUM(Property.views) AS total_views'),'conditions'=>array('Property.user_id'=>$agentId),'recursive'=>-1));
        $views = !empty($viewsRow[0]['total_views']) ? (int)$viewsRow[0]['total_views'] : 0;
        $leadConditions = array('Lead.user_id'=>$agentId) + $this->_modelDateConditions('Lead', array('created','follow_up','last_contact_date'), $dateFrom, $dateTo);
        $leads = (int)$this->Lead->find('count', array('conditions'=>$leadConditions,'recursive'=>-1));
        $dealConditions = array('Deal.user_id'=>$agentId);
        if($dateFrom !== ''){$dealConditions['Deal.date >='] = $dateFrom;}
        if($dateTo !== ''){$dealConditions['Deal.date <='] = $dateTo;}
        $deals = (int)$this->Deal->find('count', array('conditions'=>$dealConditions,'recursive'=>-1));
        $clicks = $this->_clickCounts(array('PropertyLeadClick.agent_id'=>$agentId) + $this->_dateConditions('PropertyLeadClick','created',$dateFrom,$dateTo));
        return array(
            'agent_id'=>$agentId,
            'agent_name'=>!empty($user['name']) ? $user['name'] : __('Agent #').$agentId,
            'mobile'=>!empty($user['mobile']) ? $user['mobile'] : (!empty($user['phone']) ? $user['phone'] : ''),
            'properties'=>$propertyCount,
            'views'=>$views,
            'leads'=>$leads,
            'whatsapp'=>isset($clicks['whatsapp']) ? (int)$clicks['whatsapp'] : 0,
            'calls'=>isset($clicks['call']) ? (int)$clicks['call'] : 0,
            'emails'=>isset($clicks['email']) ? (int)$clicks['email'] : 0,
            'deals'=>$deals,
            'conversion_rate'=>$leads > 0 ? round(($deals / $leads) * 100, 1) : 0
        );
    }

    protected function _agencyPerformanceRows($agencyOptions, $dateFrom, $dateTo)
    {
        $rows = array();
        foreach((array)$agencyOptions as $projectId => $agencyName)
        {
            $projectId = (int)$projectId;
            if($projectId <= 0)
            {
                continue;
            }
            $leadConditions = array('Lead.project_id'=>$projectId) + $this->_modelDateConditions('Lead', array('created','follow_up','last_contact_date'), $dateFrom, $dateTo);
            $dealConditions = array('Property.project_id'=>$projectId);
            if($dateFrom !== ''){$dealConditions['Deal.date >='] = $dateFrom;}
            if($dateTo !== ''){$dealConditions['Deal.date <='] = $dateTo;}
            $leads = (int)$this->Lead->find('count', array('conditions'=>$leadConditions,'recursive'=>-1));
            $properties = (int)$this->Property->find('count', array('conditions'=>array('Property.project_id'=>$projectId),'recursive'=>-1));
            $viewsRow = $this->Property->find('first', array('fields'=>array('SUM(Property.views) AS total_views'),'conditions'=>array('Property.project_id'=>$projectId),'recursive'=>-1));
            $views = !empty($viewsRow[0]['total_views']) ? (int)$viewsRow[0]['total_views'] : 0;
            $deals = (int)$this->Deal->find('count', array(
                'joins'=>array(array('table'=>'properties','alias'=>'Property','type'=>'LEFT','conditions'=>array('Property.id=Deal.property_id'))),
                'conditions'=>$dealConditions,
                'recursive'=>-1
            ));
            $clicks = $this->_clickCounts(array('PropertyLeadClick.project_id'=>$projectId) + $this->_dateConditions('PropertyLeadClick','created',$dateFrom,$dateTo));
            $rows[] = array(
                'project_id'=>$projectId,
                'agency_name'=>$agencyName,
                'properties'=>$properties,
                'views'=>$views,
                'leads'=>$leads,
                'whatsapp'=>isset($clicks['whatsapp']) ? (int)$clicks['whatsapp'] : 0,
                'calls'=>isset($clicks['call']) ? (int)$clicks['call'] : 0,
                'emails'=>isset($clicks['email']) ? (int)$clicks['email'] : 0,
                'deals'=>$deals,
                'conversion_rate'=>$leads > 0 ? round(($deals / $leads) * 100, 1) : 0
            );
        }
        return $rows;
    }

    protected function _clickCounts($conditions)
    {
        $map = array();
        try
        {
            $rows = (array)$this->PropertyLeadClick->find('all', array(
                'fields'=>array('PropertyLeadClick.click_type','COUNT(PropertyLeadClick.id) AS total'),
                'conditions'=>$conditions,
                'group'=>array('PropertyLeadClick.click_type'),
                'recursive'=>-1
            ));
            foreach($rows as $row)
            {
                $type = !empty($row['PropertyLeadClick']['click_type']) ? strtolower($row['PropertyLeadClick']['click_type']) : '';
                if($type !== '')
                {
                    $map[$type] = !empty($row[0]['total']) ? (int)$row[0]['total'] : 0;
                }
            }
        }
        catch(Exception $e)
        {
        }
        return $map;
    }

    protected function _ensurePropertyLeadClicksTable()
    {
        try
        {
            $this->PropertyLeadClick->query("CREATE TABLE IF NOT EXISTS `property_lead_clicks` (
                `id` bigint(20) NOT NULL AUTO_INCREMENT,
                `property_id` int(11) NOT NULL DEFAULT 0,
                `project_id` int(11) DEFAULT NULL,
                `agent_id` int(11) DEFAULT NULL,
                `client_id` int(11) DEFAULT NULL,
                `click_type` varchar(40) NOT NULL DEFAULT 'call',
                `visitor_ip` varchar(64) DEFAULT '',
                `user_agent` varchar(255) DEFAULT '',
                `referrer` varchar(255) DEFAULT '',
                `created` datetime DEFAULT NULL,
                `modified` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `property_id` (`property_id`),
                KEY `project_id` (`project_id`),
                KEY `agent_id` (`agent_id`),
                KEY `click_type` (`click_type`),
                KEY `created` (`created`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8");
        }
        catch(Exception $e)
        {
        }
    }
}
?>
