<?php
class CrmautomationsController extends AdminAppController
{
    public $helpers = array('Html', 'Form', 'Session', 'Paginator');
    public $components = array('Session', 'Paginator');

    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->loadModel('CrmAutomationSetting');
        $this->loadModel('CrmAssignmentRule');
        if($this->_shouldRunControllerBootstrap('crmautomations.schema', '2026-04-09.2'))
        {
            $this->CrmAutomationSetting->ensureSchema();
            $this->CrmAssignmentRule->ensureSchema();
            $this->_ensureCrmAutomationMenu();
        }
    }

    protected function _normalizePositiveId($value)
    {
        $value = (int)$value;
        return ($value > 0) ? $value : 0;
    }

    protected function _projectOptions()
    {
        return (array)$this->CustomFunction->agencyWiseList($this->userType, $this->agencyId);
    }

    protected function _userLevelOptions()
    {
        $this->loadModel('User');
        $this->loadModel('Ugroup');
        $groupIds = array_values(array_unique(array_map('intval', (array)$this->User->assignableGroupIds())));
        if(empty($groupIds))
        {
            return array();
        }
        return (array)$this->Ugroup->find('list', array(
            'fields' => array('Ugroup.id', 'Ugroup.name'),
            'conditions' => array('Ugroup.id' => $groupIds),
            'order' => array('Ugroup.name' => 'asc'),
            'recursive' => -1
        ));
    }

    protected function _userOptions($projectId = 0, $ugroupId = 0)
    {
        $this->loadModel('User');
        $projectId = (int)$projectId;
        $ugroupId = (int)$ugroupId;
        $conditions = array($this->User->assignableScopeConditions($projectId, 'User'));
        if($ugroupId > 0)
        {
            $conditions[] = array('User.ugroup_id' => $ugroupId);
        }
        if($this->userType === 'AGT')
        {
            $conditions[] = array('User.id' => (int)$this->luserId);
        }
        return (array)$this->User->find('list', array(
            'fields' => array('User.id', 'User.name'),
            'conditions' => $conditions,
            'order' => array('User.name' => 'asc'),
            'recursive' => -1
        ));
    }

    protected function _superAdminGroupIds()
    {
        $this->loadModel('Ugroup');
        $rows = (array)$this->Ugroup->find('all', array(
            'fields' => array('Ugroup.id', 'Ugroup.type', 'Ugroup.name'),
            'recursive' => -1
        ));
        $groupIds = array();
        foreach($rows as $row)
        {
            $groupId = !empty($row['Ugroup']['id']) ? (int)$row['Ugroup']['id'] : 0;
            $type = !empty($row['Ugroup']['type']) ? strtoupper(trim((string)$row['Ugroup']['type'])) : '';
            $name = !empty($row['Ugroup']['name']) ? strtoupper(trim((string)$row['Ugroup']['name'])) : '';
            if($groupId > 0 && ($groupId === 1 || $type === 'ADR' || preg_match('/\bSUPER\b.*\bADMIN\b/', $name)))
            {
                $groupIds[$groupId] = $groupId;
            }
        }
        if(empty($groupIds))
        {
            $groupIds[1] = 1;
        }
        return array_values($groupIds);
    }

    protected function _agencyAdminGroupIds()
    {
        $this->loadModel('Ugroup');
        $rows = (array)$this->Ugroup->find('all', array(
            'fields' => array('Ugroup.id', 'Ugroup.type', 'Ugroup.name'),
            'recursive' => -1
        ));
        $groupIds = array();
        foreach($rows as $row)
        {
            $groupId = !empty($row['Ugroup']['id']) ? (int)$row['Ugroup']['id'] : 0;
            $type = !empty($row['Ugroup']['type']) ? strtoupper(trim((string)$row['Ugroup']['type'])) : '';
            $name = !empty($row['Ugroup']['name']) ? strtoupper(trim((string)$row['Ugroup']['name'])) : '';
            if($groupId > 0 && ($type === 'AGY' || $name === 'AGENCY'))
            {
                $groupIds[$groupId] = $groupId;
            }
        }
        return array_values($groupIds);
    }

    protected function _leadPermissionCondition($id = null)
    {
        return $this->CustomFunction->userWisePermission($id, $this->luserId, $this->userType, $this->agencyId, 'Lead.project_id', 'Lead.user_id');
    }

    protected function _resolveProjectIdFromInput($requestedProjectId)
    {
        $requestedProjectId = (int)$requestedProjectId;
        if(!$this->_isSuperAdminSession())
        {
            return (int)$this->agencyId;
        }
        return ($requestedProjectId > 0) ? $requestedProjectId : 0;
    }

    protected function _crmCanUseAgentFilters()
    {
        return $this->_isSuperAdminSession() || strtoupper(trim((string)$this->userType)) === 'AGY';
    }

    protected function _crmCanUseUserLevelFilters()
    {
        return $this->_isSuperAdminSession();
    }

    protected function _queueTypeOptions()
    {
        return array(
            'overdue' => __('Overdue'),
            'today' => __('Today'),
            'upcoming' => __('Upcoming'),
            'no_followup' => __('No Follow Up'),
            'all' => __('All')
        );
    }

    protected function _normalizeDateInput($rawValue)
    {
        $rawValue = trim((string)$rawValue);
        if($rawValue === '')
        {
            return '';
        }
        $knownFormats = array('Y-m-d', 'd-m-Y', 'd/m/Y', 'm/d/Y', 'Y/m/d');
        foreach($knownFormats as $knownFormat)
        {
            $dateObj = DateTime::createFromFormat($knownFormat, $rawValue);
            if($dateObj instanceof DateTime)
            {
                $errors = DateTime::getLastErrors();
                if((int)$errors['warning_count'] === 0 && (int)$errors['error_count'] === 0)
                {
                    return $dateObj->format('Y-m-d');
                }
            }
        }
        $timestamp = strtotime($rawValue);
        if($timestamp === false)
        {
            return '';
        }
        return date('Y-m-d', $timestamp);
    }

    protected function _sourceRoiGroupParts($leadRow = array())
    {
        $lead = !empty($leadRow['Lead']) ? (array)$leadRow['Lead'] : (array)$leadRow;
        $remarks = trim((string)(isset($lead['remarks']) ? $lead['remarks'] : ''));
        $sourceName = trim((string)(isset($lead['lead_source']) ? $lead['lead_source'] : ''));
        if($sourceName === '')
        {
            $sourceName = __('Unknown');
        }
        $formId = trim((string)(isset($lead['source_form_id']) ? $lead['source_form_id'] : ''));
        if($formId === '' && $remarks !== '' && preg_match('/form_id\s*:\s*([A-Za-z0-9\-_]+)/i', $remarks, $matches) && !empty($matches[1]))
        {
            $formId = trim((string)$matches[1]);
        }
        $adId = trim((string)(isset($lead['source_ad_id']) ? $lead['source_ad_id'] : ''));
        if($adId === '' && $remarks !== '' && preg_match('/ad_id\s*:\s*([A-Za-z0-9\-_]+)/i', $remarks, $matches) && !empty($matches[1]))
        {
            $adId = trim((string)$matches[1]);
        }
        $utmCampaign = trim((string)(isset($lead['utm_campaign']) ? $lead['utm_campaign'] : ''));
        $groupKey = strtolower($sourceName.'|'.$formId.'|'.$adId.'|'.$utmCampaign);
        return array(
            'group_key' => $groupKey,
            'source_name' => $sourceName,
            'form_id' => $formId,
            'ad_id' => $adId,
            'utm_campaign' => $utmCampaign
        );
    }

    protected function _sourceRoiStatusLooksLikeVisit($statusName)
    {
        $statusName = strtolower(trim((string)$statusName));
        if($statusName === '')
        {
            return false;
        }
        foreach(array('visit', 'site visit', 'meeting', 'demo') as $keyword)
        {
            if(strpos($statusName, $keyword) !== false)
            {
                return true;
            }
        }
        return false;
    }

    protected function _selectedLeadIdsFromPost()
    {
        $selected = array();
        $rawValues = array();
        if(isset($this->request->data['Lead']['id']))
        {
            $rawValues[] = $this->request->data['Lead']['id'];
        }
        if(isset($this->request->data['Lead']['ids']))
        {
            $rawValues[] = $this->request->data['Lead']['ids'];
        }
        foreach($rawValues as $rawValue)
        {
            if(is_array($rawValue))
            {
                foreach($rawValue as $item)
                {
                    $leadId = (int)$item;
                    if($leadId > 0)
                    {
                        $selected[$leadId] = $leadId;
                    }
                }
            }
            else
            {
                foreach(explode(',', (string)$rawValue) as $item)
                {
                    $leadId = (int)$item;
                    if($leadId > 0)
                    {
                        $selected[$leadId] = $leadId;
                    }
                }
            }
        }
        return array_values($selected);
    }

    protected function _queueLeadRows($filters = array(), $paginate = true)
    {
        $filters = (array)$filters;
        $queueType = strtolower(trim((string)(isset($filters['queue_type']) ? $filters['queue_type'] : 'overdue')));
        if(!isset($this->_queueTypeOptions()[$queueType]))
        {
            $queueType = 'overdue';
        }
        $projectId = $this->_resolveProjectIdFromInput(isset($filters['project_id']) ? $filters['project_id'] : 0);
        $ugroupId = $this->_normalizePositiveId(isset($filters['ugroup_id']) ? $filters['ugroup_id'] : 0);
        $userId = $this->_normalizePositiveId(isset($filters['user_id']) ? $filters['user_id'] : 0);
        $statusId = $this->_normalizePositiveId(isset($filters['status_id']) ? $filters['status_id'] : 0);

        $conditions = array($this->_leadPermissionCondition(null));
        if($projectId > 0)
        {
            $conditions[] = array('Lead.project_id' => $projectId);
        }
        if($userId > 0)
        {
            $conditions[] = array('Lead.user_id' => $userId);
        }
        if($statusId > 0)
        {
            $conditions[] = array('Lead.status_id' => $statusId);
        }

        $joins = array(
            array(
                'table' => 'users',
                'alias' => 'User',
                'type' => 'LEFT',
                'conditions' => array('Lead.user_id = User.id')
            ),
            array(
                'table' => 'projects',
                'alias' => 'Project',
                'type' => 'LEFT',
                'conditions' => array('Lead.project_id = Project.id')
            ),
            array(
                'table' => 'statuses',
                'alias' => 'Status',
                'type' => 'LEFT',
                'conditions' => array('Lead.status_id = Status.id')
            ),
            array(
                'table' => 'clients',
                'alias' => 'Client',
                'type' => 'LEFT',
                'conditions' => array('Lead.client_id = Client.id')
            )
        );
        if($ugroupId > 0)
        {
            $conditions[] = array('User.ugroup_id' => $ugroupId);
        }

        $todayStart = date('Y-m-d 00:00:00');
        $todayEnd = date('Y-m-d 23:59:59');
        if($queueType === 'overdue')
        {
            $conditions[] = array('Lead.follow_up <' => date('Y-m-d H:i:s'));
            $conditions[] = array('Lead.follow_up IS NOT NULL');
            $conditions[] = array('Lead.follow_up !=' => '0000-00-00 00:00:00');
        }
        elseif($queueType === 'today')
        {
            $conditions[] = array('Lead.follow_up >=' => $todayStart, 'Lead.follow_up <=' => $todayEnd);
        }
        elseif($queueType === 'upcoming')
        {
            $conditions[] = array('Lead.follow_up >' => $todayEnd);
        }
        elseif($queueType === 'no_followup')
        {
            $conditions[] = array(
                'OR' => array(
                    array('Lead.follow_up' => null),
                    array('Lead.follow_up' => '0000-00-00 00:00:00'),
                    array('Lead.follow_up' => '')
                )
            );
        }

        $query = array(
            'fields' => array(
                'Lead.id','Lead.project_id','Lead.user_id','Lead.client_id','Lead.lead_name','Lead.lead_mobile','Lead.lead_email',
                'Lead.last_contact_date','Lead.follow_up','Lead.remarks',
                'User.id','User.name',
                'Project.id','Project.name',
                'Status.id','Status.name',
                'Client.id','Client.name','Client.mobile','Client.email'
            ),
            'joins' => $joins,
            'conditions' => $conditions,
            'order' => array('Lead.follow_up' => 'asc', 'Lead.id' => 'desc'),
            'recursive' => -1
        );
        if($paginate)
        {
            $this->Paginator->settings = array(
                'Lead' => array_merge($query, array('limit' => 50))
            );
            return (array)$this->Paginator->paginate('Lead');
        }
        return (array)$this->Lead->find('all', $query);
    }

    protected function _ensureCrmAutomationMenu()
    {
        try
        {
            $Page = ClassRegistry::init('Page');
            $PageRight = ClassRegistry::init('PageRight');
            $root = (array)$Page->find('first', array(
                'fields' => array('Page.id'),
                'conditions' => array(
                    'LOWER(Page.controller_name)' => 'leads',
                    'Page.parent_id' => 0
                ),
                'order' => array('Page.id' => 'asc'),
                'recursive' => -1
            ));
            $rootId = !empty($root['Page']['id']) ? (int)$root['Page']['id'] : 0;
            if($rootId <= 0)
            {
                return;
            }

            $pages = array(
                array('page_name' => 'CRM Follow-up Queue', 'controller_name' => 'Crmautomations', 'action_name' => 'queue', 'icon' => 'fa fa-clock-o', 'ordering' => 8),
                array('page_name' => 'CRM Reactivation', 'controller_name' => 'Crmautomations', 'action_name' => 'reactivation', 'icon' => 'fa fa-bullseye', 'ordering' => 9),
                array('page_name' => 'CRM Source ROI', 'controller_name' => 'Crmautomations', 'action_name' => 'sourceroi', 'icon' => 'fa fa-pie-chart', 'ordering' => 10),
                array('page_name' => 'CRM Assignment Rules', 'controller_name' => 'Crmautomations', 'action_name' => 'assignmentrules', 'icon' => 'fa fa-random', 'ordering' => 14),
                array('page_name' => 'CRM Automation Settings', 'controller_name' => 'Crmautomations', 'action_name' => 'index', 'icon' => 'fa fa-cogs', 'ordering' => 15)
            );
            $pageIds = array();
            foreach($pages as $pageRow)
            {
                $existing = (array)$Page->find('first', array(
                    'fields' => array('Page.id'),
                    'conditions' => array(
                        'Page.parent_id' => $rootId,
                        'LOWER(Page.controller_name)' => strtolower($pageRow['controller_name']),
                        'LOWER(Page.action_name)' => strtolower($pageRow['action_name'])
                    ),
                    'recursive' => -1
                ));
                $saveData = array(
                    'model_name' => 'Leads',
                    'page_name' => $pageRow['page_name'],
                    'controller_name' => $pageRow['controller_name'],
                    'action_name' => $pageRow['action_name'],
                    'icon' => $pageRow['icon'],
                    'parent_id' => $rootId,
                    'ordering' => $pageRow['ordering'],
                    'published' => 'Yes',
                    'sel_name' => ''
                );
                if(!empty($existing['Page']['id']))
                {
                    $saveData['id'] = (int)$existing['Page']['id'];
                }
                $Page->create();
                $Page->save(array('Page' => $saveData), array('validate' => false));
                $pageId = !empty($saveData['id']) ? (int)$saveData['id'] : (int)$Page->id;
                if($pageId > 0)
                {
                    $pageIds[$pageId] = $pageId;
                }
            }

            $grantGroupIds = array_values(array_unique(array_merge($this->_superAdminGroupIds(), $this->_agencyAdminGroupIds())));
            foreach($pageIds as $pageId)
            {
                foreach($grantGroupIds as $groupId)
                {
                    $existingRight = (array)$PageRight->find('first', array(
                        'fields' => array('PageRight.id'),
                        'conditions' => array(
                            'PageRight.page_id' => (int)$pageId,
                            'PageRight.ugroup_id' => (int)$groupId
                        ),
                        'recursive' => -1
                    ));
                    $saveRight = array(
                        'page_id' => (int)$pageId,
                        'ugroup_id' => (int)$groupId,
                        'view_right' => 1,
                        'save_right' => 1,
                        'update_right' => 1,
                        'search_right' => 1
                    );
                    if(!empty($existingRight['PageRight']['id']))
                    {
                        $saveRight['id'] = (int)$existingRight['PageRight']['id'];
                    }
                    $PageRight->create();
                    $PageRight->save(array('PageRight' => $saveRight), array('validate' => false));
                }
            }
        }
        catch(Exception $e)
        {
        }
    }

    public function index()
    {
        $projectOptions = $this->_projectOptions();
        $selectedProjectId = $this->_resolveProjectIdFromInput(isset($this->request->query['project_id']) ? $this->request->query['project_id'] : $this->agencyId);
        if($this->request->is('post'))
        {
            if(empty($this->userPermissionArr['save_right']))
            {
                $this->Session->setFlash(__('No Permission!'), 'flash', array('alert' => 'danger'));
                return $this->redirect(array('action' => 'index'));
            }
            $form = isset($this->request->data['CrmAutomationSetting']) ? (array)$this->request->data['CrmAutomationSetting'] : array();
            $selectedProjectId = $this->_resolveProjectIdFromInput(isset($form['project_id']) ? $form['project_id'] : $selectedProjectId);
            if($selectedProjectId <= 0)
            {
                $this->Session->setFlash(__('Please select agency first.'), 'flash', array('alert' => 'danger'));
            }
            elseif($this->CrmAutomationSetting->saveProjectRow($selectedProjectId, $form))
            {
                $this->Session->setFlash(__('CRM automation settings have been saved.'), 'flash', array('alert' => 'success'));
                return $this->redirect(array('action' => 'index', '?' => array('project_id' => $selectedProjectId)));
            }
            else
            {
                $this->Session->setFlash(__('Unable to save CRM automation settings.'), 'flash', array('alert' => 'danger'));
            }
        }
        if(!$this->_isSuperAdminSession() && (int)$this->agencyId > 0)
        {
            $selectedProjectId = (int)$this->agencyId;
        }
        $settingsRow = $this->CrmAutomationSetting->findProjectRow($selectedProjectId);
        $currentAgencyName = ((int)$selectedProjectId > 0 && !empty($projectOptions[$selectedProjectId])) ? (string)$projectOptions[$selectedProjectId] : '';
        $isSuperAdmin = $this->_isSuperAdminSession();
        $this->set(compact('projectOptions', 'selectedProjectId', 'settingsRow', 'currentAgencyName', 'isSuperAdmin'));
    }

    public function queue()
    {
        $projectOptions = $this->_projectOptions();
        $userLevelOptions = $this->_userLevelOptions();
        $isSuperAdmin = $this->_isSuperAdminSession();
        $canUseAgentFilters = $this->_crmCanUseAgentFilters();
        $canUseUserLevelFilters = $this->_crmCanUseUserLevelFilters();
        $this->loadModel('Status');
        $statusOptions = (array)$this->Status->find('list', array(
            'fields' => array('Status.id', 'Status.name'),
            'order' => array('Status.name' => 'asc'),
            'recursive' => -1
        ));

        $filters = array(
            'queue_type' => isset($this->request->query['queue_type']) ? $this->request->query['queue_type'] : 'overdue',
            'project_id' => isset($this->request->query['project_id']) ? $this->request->query['project_id'] : $this->agencyId,
            'ugroup_id' => isset($this->request->query['ugroup_id']) ? $this->request->query['ugroup_id'] : 0,
            'user_id' => isset($this->request->query['user_id']) ? $this->request->query['user_id'] : 0,
            'status_id' => isset($this->request->query['status_id']) ? $this->request->query['status_id'] : 0
        );
        if(!$this->_isSuperAdminSession() && (int)$this->agencyId > 0)
        {
            $filters['project_id'] = (int)$this->agencyId;
        }
        $filters['project_id'] = $this->_resolveProjectIdFromInput($filters['project_id']);
        $filters['ugroup_id'] = $canUseUserLevelFilters ? $this->_normalizePositiveId($filters['ugroup_id']) : 0;
        $filters['user_id'] = $canUseAgentFilters ? $this->_normalizePositiveId($filters['user_id']) : (int)$this->luserId;
        $filters['status_id'] = $this->_normalizePositiveId($filters['status_id']);

        $userOptions = $canUseAgentFilters ? $this->_userOptions($filters['project_id'], $filters['ugroup_id']) : array();
        if($canUseAgentFilters && (int)$filters['user_id'] > 0 && !isset($userOptions[(int)$filters['user_id']]))
        {
            $filters['user_id'] = 0;
        }

        $this->loadModel('Lead');
        $leadRows = $this->_queueLeadRows($filters, true);
        $queueTypeOptions = $this->_queueTypeOptions();

        $this->set(compact('projectOptions', 'userLevelOptions', 'userOptions', 'statusOptions', 'leadRows', 'filters', 'queueTypeOptions', 'isSuperAdmin', 'canUseAgentFilters', 'canUseUserLevelFilters'));
    }

    public function assignmentrules()
    {
        $projectOptions = $this->_projectOptions();
        $selectedProjectId = $this->_resolveProjectIdFromInput(isset($this->request->query['project_id']) ? $this->request->query['project_id'] : $this->agencyId);
        if(!$this->_isSuperAdminSession() && (int)$this->agencyId > 0)
        {
            $selectedProjectId = (int)$this->agencyId;
        }

        $this->loadModel('User');
        if($this->request->is('post'))
        {
            if(empty($this->userPermissionArr['save_right']))
            {
                $this->Session->setFlash(__('No Permission!'), 'flash', array('alert' => 'danger'));
                return $this->redirect(array('action' => 'assignmentrules'));
            }

            $task = strtolower(trim((string)(isset($this->request->data['CrmAssignmentRule']['task']) ? $this->request->data['CrmAssignmentRule']['task'] : 'save_rule')));
            if($task === 'save_settings')
            {
                $settingsForm = isset($this->request->data['CrmAutomationSetting']) ? (array)$this->request->data['CrmAutomationSetting'] : array();
                $selectedProjectId = $this->_resolveProjectIdFromInput(isset($settingsForm['project_id']) ? $settingsForm['project_id'] : $selectedProjectId);
                if($selectedProjectId <= 0)
                {
                    $this->Session->setFlash(__('Please select agency first.'), 'flash', array('alert' => 'danger'));
                }
                elseif($this->CrmAutomationSetting->saveProjectAssignmentSettings($selectedProjectId, $settingsForm))
                {
                    $this->Session->setFlash(__('CRM assignment settings have been saved.'), 'flash', array('alert' => 'success'));
                    return $this->redirect(array('action' => 'assignmentrules', '?' => array('project_id' => $selectedProjectId)));
                }
                else
                {
                    $this->Session->setFlash(__('Unable to save CRM assignment settings.'), 'flash', array('alert' => 'danger'));
                }
            }
            elseif($task === 'delete_rule')
            {
                $ruleId = (int)(isset($this->request->data['CrmAssignmentRule']['id']) ? $this->request->data['CrmAssignmentRule']['id'] : 0);
                $ruleRow = (array)$this->CrmAssignmentRule->find('first', array(
                    'conditions' => array('CrmAssignmentRule.id' => $ruleId),
                    'recursive' => -1
                ));
                if(empty($ruleRow['CrmAssignmentRule']['id']))
                {
                    $this->Session->setFlash(__('Invalid assignment rule.'), 'flash', array('alert' => 'danger'));
                }
                else
                {
                    $ruleProjectId = (int)$ruleRow['CrmAssignmentRule']['project_id'];
                    $selectedProjectId = $this->_resolveProjectIdFromInput($ruleProjectId);
                    if(!$this->_isSuperAdminSession() && $ruleProjectId !== (int)$this->agencyId)
                    {
                        $this->Session->setFlash(__('Permission denied for selected rule.'), 'flash', array('alert' => 'danger'));
                    }
                    elseif($this->CrmAssignmentRule->delete($ruleId))
                    {
                        $this->Session->setFlash(__('CRM assignment rule deleted successfully.'), 'flash', array('alert' => 'success'));
                    }
                    else
                    {
                        $this->Session->setFlash(__('Unable to delete CRM assignment rule.'), 'flash', array('alert' => 'danger'));
                    }
                }
                return $this->redirect(array('action' => 'assignmentrules', '?' => array('project_id' => $selectedProjectId)));
            }
            else
            {
                $form = isset($this->request->data['CrmAssignmentRule']) ? (array)$this->request->data['CrmAssignmentRule'] : array();
                $selectedProjectId = $this->_resolveProjectIdFromInput(isset($form['project_id']) ? $form['project_id'] : $selectedProjectId);
                $saveData = $this->CrmAssignmentRule->normalizeRuleData($form, $selectedProjectId);
                $ruleId = !empty($form['id']) ? (int)$form['id'] : 0;
                $validationErrors = array();
                if($selectedProjectId <= 0)
                {
                    $validationErrors[] = __('Please select agency first.');
                }
                if(trim((string)$saveData['title']) === '')
                {
                    $validationErrors[] = __('Please enter rule title.');
                }
                $targetUserId = (int)$saveData['target_user_id'];
                if($targetUserId <= 0)
                {
                    $validationErrors[] = __('Please select assigned user.');
                }
                else
                {
                    $userRow = (array)$this->User->find('first', array(
                        'fields' => array('User.id', 'User.ugroup_id'),
                        'conditions' => array_merge(
                            array($this->User->assignableScopeConditions($selectedProjectId, 'User')),
                            array('User.id' => $targetUserId)
                        ),
                        'recursive' => -1
                    ));
                    if(empty($userRow['User']['id']))
                    {
                        $validationErrors[] = __('Selected user does not belong to selected agency/user scope.');
                    }
                    else
                    {
                        $saveData['target_ugroup_id'] = (int)$userRow['User']['ugroup_id'];
                    }
                }
                if(empty($validationErrors))
                {
                    if($ruleId > 0)
                    {
                        $existingRow = (array)$this->CrmAssignmentRule->find('first', array(
                            'conditions' => array('CrmAssignmentRule.id' => $ruleId),
                            'recursive' => -1
                        ));
                        if(empty($existingRow['CrmAssignmentRule']['id']))
                        {
                            $validationErrors[] = __('Invalid assignment rule.');
                        }
                        else
                        {
                            $saveData['id'] = $ruleId;
                            $saveData['created_by'] = (int)$existingRow['CrmAssignmentRule']['created_by'];
                        }
                    }
                }
                if(!empty($validationErrors))
                {
                    $this->Session->setFlash(implode('<br/>', $validationErrors), 'flash', array('alert' => 'danger'));
                }
                else
                {
                    if(empty($saveData['created_by']))
                    {
                        $saveData['created_by'] = (int)$this->luserId;
                    }
                    $saveData['modified_by'] = (int)$this->luserId;
                    $this->CrmAssignmentRule->create();
                    if($this->CrmAssignmentRule->save(array('CrmAssignmentRule' => $saveData), array('validate' => false)))
                    {
                        $this->Session->setFlash(__('CRM assignment rule has been saved.'), 'flash', array('alert' => 'success'));
                        return $this->redirect(array('action' => 'assignmentrules', '?' => array('project_id' => $selectedProjectId)));
                    }
                    $this->Session->setFlash(__('Unable to save CRM assignment rule.'), 'flash', array('alert' => 'danger'));
                }
            }
        }

        $settingsRow = $this->CrmAutomationSetting->findProjectRow($selectedProjectId);
        $ruleRows = array();
        if((int)$selectedProjectId > 0)
        {
            $ruleRows = (array)$this->CrmAssignmentRule->find('all', array(
                'conditions' => array('CrmAssignmentRule.project_id' => (int)$selectedProjectId),
                'joins' => array(
                    array('table' => 'users', 'alias' => 'User', 'type' => 'LEFT', 'conditions' => array('User.id = CrmAssignmentRule.target_user_id')),
                    array('table' => 'ugroups', 'alias' => 'Ugroup', 'type' => 'LEFT', 'conditions' => array('Ugroup.id = CrmAssignmentRule.target_ugroup_id'))
                ),
                'fields' => array('CrmAssignmentRule.*', 'User.id', 'User.name', 'Ugroup.id', 'Ugroup.name'),
                'order' => array('CrmAssignmentRule.priority' => 'desc', 'CrmAssignmentRule.id' => 'desc'),
                'recursive' => -1
            ));
        }

        $editId = isset($this->request->query['edit']) ? (int)$this->request->query['edit'] : 0;
        $formRow = $this->CrmAssignmentRule->defaultRuleRow($selectedProjectId);
        if($editId > 0)
        {
            foreach($ruleRows as $ruleRow)
            {
                if((int)$ruleRow['CrmAssignmentRule']['id'] === $editId)
                {
                    $formRow = array('CrmAssignmentRule' => (array)$ruleRow['CrmAssignmentRule']);
                    break;
                }
            }
        }
        $userOptions = ((int)$selectedProjectId > 0) ? $this->_userOptions($selectedProjectId, 0) : array();
        $currentAgencyName = ((int)$selectedProjectId > 0 && !empty($projectOptions[$selectedProjectId])) ? (string)$projectOptions[$selectedProjectId] : '';
        $isSuperAdmin = $this->_isSuperAdminSession();

        $this->set(compact('projectOptions', 'selectedProjectId', 'settingsRow', 'ruleRows', 'formRow', 'userOptions', 'currentAgencyName', 'isSuperAdmin'));
    }

    public function sourceroi()
    {
        $projectOptions = $this->_projectOptions();
        $userLevelOptions = $this->_userLevelOptions();
        $isSuperAdmin = $this->_isSuperAdminSession();
        $canUseAgentFilters = $this->_crmCanUseAgentFilters();
        $canUseUserLevelFilters = $this->_crmCanUseUserLevelFilters();
        $filters = array(
            'project_id' => isset($this->request->query['project_id']) ? $this->request->query['project_id'] : $this->agencyId,
            'ugroup_id' => isset($this->request->query['ugroup_id']) ? $this->request->query['ugroup_id'] : 0,
            'user_id' => isset($this->request->query['user_id']) ? $this->request->query['user_id'] : 0,
            'start_date' => isset($this->request->query['start_date']) ? $this->request->query['start_date'] : '',
            'end_date' => isset($this->request->query['end_date']) ? $this->request->query['end_date'] : ''
        );
        if(!$this->_isSuperAdminSession() && (int)$this->agencyId > 0)
        {
            $filters['project_id'] = (int)$this->agencyId;
        }
        $filters['project_id'] = $this->_resolveProjectIdFromInput($filters['project_id']);
        $filters['ugroup_id'] = $canUseUserLevelFilters ? $this->_normalizePositiveId($filters['ugroup_id']) : 0;
        $filters['user_id'] = $canUseAgentFilters ? $this->_normalizePositiveId($filters['user_id']) : (int)$this->luserId;
        $filters['start_date'] = $this->_normalizeDateInput($filters['start_date']);
        $filters['end_date'] = $this->_normalizeDateInput($filters['end_date']);

        $userOptions = ($canUseAgentFilters && (int)$filters['project_id'] > 0) ? $this->_userOptions($filters['project_id'], $filters['ugroup_id']) : array();
        if($canUseAgentFilters && (int)$filters['user_id'] > 0 && !isset($userOptions[(int)$filters['user_id']]))
        {
            $filters['user_id'] = 0;
        }

        $this->loadModel('Lead');
        $this->loadModel('Deal');
        $this->loadModel('DealsPayment');

        $leadBaseConditions = array($this->_leadPermissionCondition(null));
        if((int)$filters['project_id'] > 0)
        {
            $leadBaseConditions[] = array('Lead.project_id' => (int)$filters['project_id']);
        }
        if((int)$filters['user_id'] > 0)
        {
            $leadBaseConditions[] = array('Lead.user_id' => (int)$filters['user_id']);
        }
        $leadJoins = array(
            array('table' => 'users', 'alias' => 'User', 'type' => 'LEFT', 'conditions' => array('Lead.user_id = User.id')),
            array('table' => 'statuses', 'alias' => 'Status', 'type' => 'LEFT', 'conditions' => array('Lead.status_id = Status.id'))
        );
        if($canUseUserLevelFilters && (int)$filters['ugroup_id'] > 0)
        {
            $leadBaseConditions[] = array('User.ugroup_id' => (int)$filters['ugroup_id']);
        }

        $leadFields = array(
            'Lead.id','Lead.project_id','Lead.user_id','Lead.client_id','Lead.lead_source','Lead.source_form_id',
            'Lead.source_ad_id','Lead.utm_campaign','Lead.last_contact_date','Lead.follow_up','Lead.status_id',
            'Lead.remarks',
            'User.id','User.name','User.ugroup_id',
            'Status.id','Status.name'
        );
        $leadAttributionRows = (array)$this->Lead->find('all', array(
            'fields' => $leadFields,
            'joins' => $leadJoins,
            'conditions' => $leadBaseConditions,
            'order' => array('Lead.last_contact_date' => 'desc', 'Lead.follow_up' => 'desc', 'Lead.id' => 'desc'),
            'recursive' => -1
        ));

        $leadMetricConditions = $leadBaseConditions;
        if($filters['start_date'] !== '')
        {
            $leadMetricConditions[] = array(
                'OR' => array(
                    array('Lead.last_contact_date >=' => $filters['start_date']),
                    array('DATE(Lead.follow_up) >=' => $filters['start_date'])
                )
            );
        }
        if($filters['end_date'] !== '')
        {
            $leadMetricConditions[] = array(
                'OR' => array(
                    array('Lead.last_contact_date <=' => $filters['end_date']),
                    array('DATE(Lead.follow_up) <=' => $filters['end_date'])
                )
            );
        }
        $leadMetricRows = (array)$this->Lead->find('all', array(
            'fields' => $leadFields,
            'joins' => $leadJoins,
            'conditions' => $leadMetricConditions,
            'order' => array('Lead.last_contact_date' => 'desc', 'Lead.follow_up' => 'desc', 'Lead.id' => 'desc'),
            'recursive' => -1
        ));

        $attributionMap = array();
        $reportRows = array();
        foreach($leadAttributionRows as $leadRow)
        {
            $lead = (array)$leadRow['Lead'];
            $groupParts = $this->_sourceRoiGroupParts($leadRow);
            $groupKey = (string)$groupParts['group_key'];
            $projectId = !empty($lead['project_id']) ? (int)$lead['project_id'] : 0;
            $clientId = !empty($lead['client_id']) ? (int)$lead['client_id'] : 0;
            if($projectId > 0 && $clientId > 0 && !isset($attributionMap[$projectId][$clientId]))
            {
                $attributionMap[$projectId][$clientId] = array(
                    'group_key' => $groupKey,
                    'source_name' => $groupParts['source_name'],
                    'form_id' => $groupParts['form_id'],
                    'ad_id' => $groupParts['ad_id'],
                    'utm_campaign' => $groupParts['utm_campaign'],
                    'user_name' => !empty($leadRow['User']['name']) ? (string)$leadRow['User']['name'] : ''
                );
            }
        }

        foreach($leadMetricRows as $leadRow)
        {
            $lead = (array)$leadRow['Lead'];
            $groupParts = $this->_sourceRoiGroupParts($leadRow);
            $groupKey = (string)$groupParts['group_key'];
            if(!isset($reportRows[$groupKey]))
            {
                $reportRows[$groupKey] = array(
                    'group_key' => $groupKey,
                    'source_name' => $groupParts['source_name'],
                    'form_id' => $groupParts['form_id'],
                    'ad_id' => $groupParts['ad_id'],
                    'utm_campaign' => $groupParts['utm_campaign'],
                    'lead_count' => 0,
                    'contacted_count' => 0,
                    'visit_count' => 0,
                    'booking_count' => 0,
                    'sales_amount' => 0.0,
                    'collection_amount' => 0.0,
                    'users' => array()
                );
            }
            $reportRows[$groupKey]['lead_count']++;
            if(!empty($lead['last_contact_date']) && $lead['last_contact_date'] !== '0000-00-00')
            {
                $reportRows[$groupKey]['contacted_count']++;
            }
            if($this->_sourceRoiStatusLooksLikeVisit(isset($leadRow['Status']['name']) ? $leadRow['Status']['name'] : ''))
            {
                $reportRows[$groupKey]['visit_count']++;
            }
            if(!empty($leadRow['User']['name']))
            {
                $reportRows[$groupKey]['users'][(int)$lead['user_id']] = (string)$leadRow['User']['name'];
            }
        }

        $dealConditions = array();
        if((int)$filters['project_id'] > 0)
        {
            $dealConditions[] = array('Property.project_id' => (int)$filters['project_id']);
        }
        if($filters['start_date'] !== '')
        {
            $dealConditions[] = array('Deal.date >=' => $filters['start_date']);
        }
        if($filters['end_date'] !== '')
        {
            $dealConditions[] = array('Deal.date <=' => $filters['end_date']);
        }
        $dealRows = (array)$this->Deal->find('all', array(
            'fields' => array('Deal.id', 'Deal.client_id', 'Deal.total_amount', 'Deal.date', 'Property.project_id'),
            'joins' => array(
                array('table' => 'properties', 'alias' => 'Property', 'type' => 'INNER', 'conditions' => array('Property.id = Deal.property_id'))
            ),
            'conditions' => $dealConditions,
            'recursive' => -1
        ));

        $paymentConditions = array();
        if((int)$filters['project_id'] > 0)
        {
            $paymentConditions[] = array('Property.project_id' => (int)$filters['project_id']);
        }
        if($filters['start_date'] !== '')
        {
            $paymentConditions[] = array('DealsPayment.payment_date >=' => $filters['start_date']);
        }
        if($filters['end_date'] !== '')
        {
            $paymentConditions[] = array('DealsPayment.payment_date <=' => $filters['end_date']);
        }
        $paymentRows = (array)$this->DealsPayment->find('all', array(
            'fields' => array('DealsPayment.deal_id', 'SUM(DealsPayment.total_amount) AS collection_total'),
            'joins' => array(
                array('table' => 'deals', 'alias' => 'Deal', 'type' => 'INNER', 'conditions' => array('Deal.id = DealsPayment.deal_id')),
                array('table' => 'properties', 'alias' => 'Property', 'type' => 'INNER', 'conditions' => array('Property.id = Deal.property_id'))
            ),
            'conditions' => $paymentConditions,
            'group' => array('DealsPayment.deal_id'),
            'recursive' => -1
        ));
        $paymentByDeal = array();
        foreach($paymentRows as $paymentRow)
        {
            $dealId = !empty($paymentRow['DealsPayment']['deal_id']) ? (int)$paymentRow['DealsPayment']['deal_id'] : 0;
            if($dealId > 0)
            {
                $paymentByDeal[$dealId] = !empty($paymentRow[0]['collection_total']) ? (float)$paymentRow[0]['collection_total'] : 0.0;
            }
        }

        foreach($dealRows as $dealRow)
        {
            $deal = (array)$dealRow['Deal'];
            $projectId = !empty($dealRow['Property']['project_id']) ? (int)$dealRow['Property']['project_id'] : 0;
            $clientId = !empty($deal['client_id']) ? (int)$deal['client_id'] : 0;
            if($projectId <= 0 || $clientId <= 0 || empty($attributionMap[$projectId][$clientId]))
            {
                continue;
            }
            $groupInfo = (array)$attributionMap[$projectId][$clientId];
            $groupKey = (string)$groupInfo['group_key'];
            if(!isset($reportRows[$groupKey]))
            {
                $reportRows[$groupKey] = array(
                    'group_key' => $groupKey,
                    'source_name' => isset($groupInfo['source_name']) ? $groupInfo['source_name'] : __('Unknown'),
                    'form_id' => isset($groupInfo['form_id']) ? $groupInfo['form_id'] : '',
                    'ad_id' => isset($groupInfo['ad_id']) ? $groupInfo['ad_id'] : '',
                    'utm_campaign' => isset($groupInfo['utm_campaign']) ? $groupInfo['utm_campaign'] : '',
                    'lead_count' => 0,
                    'contacted_count' => 0,
                    'visit_count' => 0,
                    'booking_count' => 0,
                    'sales_amount' => 0.0,
                    'collection_amount' => 0.0,
                    'users' => array()
                );
            }
            $reportRows[$groupKey]['booking_count']++;
            $reportRows[$groupKey]['sales_amount'] += (float)(isset($deal['total_amount']) ? $deal['total_amount'] : 0);
            $dealId = !empty($deal['id']) ? (int)$deal['id'] : 0;
            if($dealId > 0 && isset($paymentByDeal[$dealId]))
            {
                $reportRows[$groupKey]['collection_amount'] += (float)$paymentByDeal[$dealId];
            }
            if(!empty($groupInfo['user_name']))
            {
                $reportRows[$groupKey]['users'][md5((string)$groupInfo['user_name'])] = (string)$groupInfo['user_name'];
            }
        }

        $summaryCards = array(
            'total_leads' => 0,
            'total_contacted' => 0,
            'total_visits' => 0,
            'total_bookings' => 0,
            'total_sales' => 0.0,
            'total_collections' => 0.0
        );
        foreach($reportRows as $groupKey => $row)
        {
            $reportRows[$groupKey]['sales_amount'] = round((float)$row['sales_amount'], 2);
            $reportRows[$groupKey]['collection_amount'] = round((float)$row['collection_amount'], 2);
            $reportRows[$groupKey]['users_text'] = implode(', ', array_values((array)$row['users']));
            $summaryCards['total_leads'] += (int)$row['lead_count'];
            $summaryCards['total_contacted'] += (int)$row['contacted_count'];
            $summaryCards['total_visits'] += (int)$row['visit_count'];
            $summaryCards['total_bookings'] += (int)$row['booking_count'];
            $summaryCards['total_sales'] += (float)$row['sales_amount'];
            $summaryCards['total_collections'] += (float)$row['collection_amount'];
        }
        usort($reportRows, function($rowA, $rowB){
            $salesA = isset($rowA['sales_amount']) ? (float)$rowA['sales_amount'] : 0.0;
            $salesB = isset($rowB['sales_amount']) ? (float)$rowB['sales_amount'] : 0.0;
            if($salesA !== $salesB)
            {
                return ($salesA < $salesB) ? 1 : -1;
            }
            $leadsA = isset($rowA['lead_count']) ? (int)$rowA['lead_count'] : 0;
            $leadsB = isset($rowB['lead_count']) ? (int)$rowB['lead_count'] : 0;
            if($leadsA !== $leadsB)
            {
                return ($leadsA < $leadsB) ? 1 : -1;
            }
            return strcasecmp(isset($rowA['source_name']) ? $rowA['source_name'] : '', isset($rowB['source_name']) ? $rowB['source_name'] : '');
        });
        $summaryCards['total_sales'] = round((float)$summaryCards['total_sales'], 2);
        $summaryCards['total_collections'] = round((float)$summaryCards['total_collections'], 2);
        $currentAgencyName = ((int)$filters['project_id'] > 0 && !empty($projectOptions[(int)$filters['project_id']])) ? (string)$projectOptions[(int)$filters['project_id']] : '';

        $this->set(compact('projectOptions', 'userLevelOptions', 'userOptions', 'filters', 'reportRows', 'summaryCards', 'isSuperAdmin', 'currentAgencyName', 'canUseAgentFilters', 'canUseUserLevelFilters'));
    }

    public function reactivation()
    {
        $projectOptions = $this->_projectOptions();
        $userLevelOptions = $this->_userLevelOptions();
        $isSuperAdmin = $this->_isSuperAdminSession();
        $canUseAgentFilters = $this->_crmCanUseAgentFilters();
        $canUseUserLevelFilters = $this->_crmCanUseUserLevelFilters();
        $this->loadModel('Status');
        $statusOptions = (array)$this->Status->find('list', array(
            'fields' => array('Status.id', 'Status.name'),
            'order' => array('Status.name' => 'asc'),
            'recursive' => -1
        ));

        $filters = array(
            'project_id' => isset($this->request->query['project_id']) ? $this->request->query['project_id'] : $this->agencyId,
            'ugroup_id' => isset($this->request->query['ugroup_id']) ? $this->request->query['ugroup_id'] : 0,
            'user_id' => isset($this->request->query['user_id']) ? $this->request->query['user_id'] : 0,
            'status_id' => isset($this->request->query['status_id']) ? $this->request->query['status_id'] : 0,
            'older_than_days' => isset($this->request->query['older_than_days']) ? (int)$this->request->query['older_than_days'] : 30
        );
        if($filters['older_than_days'] <= 0)
        {
            $filters['older_than_days'] = 30;
        }
        if(!$this->_isSuperAdminSession() && (int)$this->agencyId > 0)
        {
            $filters['project_id'] = (int)$this->agencyId;
        }
        $filters['project_id'] = $this->_resolveProjectIdFromInput($filters['project_id']);
        $filters['ugroup_id'] = $canUseUserLevelFilters ? $this->_normalizePositiveId($filters['ugroup_id']) : 0;
        $filters['user_id'] = $canUseAgentFilters ? $this->_normalizePositiveId($filters['user_id']) : (int)$this->luserId;
        $filters['status_id'] = $this->_normalizePositiveId($filters['status_id']);

        if($this->request->is('post'))
        {
            $mode = strtolower(trim((string)(isset($this->request->data['send_mode']) ? $this->request->data['send_mode'] : '')));
            $selectedLeadIds = $this->_selectedLeadIdsFromPost();
            if(in_array($mode, array('sms', 'email'), true) && !empty($selectedLeadIds))
            {
                return $this->redirect(array('controller' => 'Leads', 'action' => 'leadbulkmessage', $mode, '?' => array('ids' => implode(',', $selectedLeadIds))));
            }
            $this->Session->setFlash(__('Please select at least one lead first.'), 'flash', array('alert' => 'danger'));
        }

        $userOptions = ($canUseAgentFilters && (int)$filters['project_id'] > 0) ? $this->_userOptions($filters['project_id'], $filters['ugroup_id']) : array();
        if($canUseAgentFilters && (int)$filters['user_id'] > 0 && !isset($userOptions[(int)$filters['user_id']]))
        {
            $filters['user_id'] = 0;
        }

        $this->loadModel('Lead');
        $conditions = array($this->_leadPermissionCondition(null));
        if((int)$filters['project_id'] > 0)
        {
            $conditions[] = array('Lead.project_id' => (int)$filters['project_id']);
        }
        if((int)$filters['user_id'] > 0)
        {
            $conditions[] = array('Lead.user_id' => (int)$filters['user_id']);
        }
        if((int)$filters['status_id'] > 0)
        {
            $conditions[] = array('Lead.status_id' => (int)$filters['status_id']);
        }
        $joins = array(
            array('table' => 'users', 'alias' => 'User', 'type' => 'LEFT', 'conditions' => array('Lead.user_id = User.id')),
            array('table' => 'projects', 'alias' => 'Project', 'type' => 'LEFT', 'conditions' => array('Lead.project_id = Project.id')),
            array('table' => 'statuses', 'alias' => 'Status', 'type' => 'LEFT', 'conditions' => array('Lead.status_id = Status.id')),
            array('table' => 'clients', 'alias' => 'Client', 'type' => 'LEFT', 'conditions' => array('Lead.client_id = Client.id'))
        );
        if($canUseUserLevelFilters && (int)$filters['ugroup_id'] > 0)
        {
            $conditions[] = array('User.ugroup_id' => (int)$filters['ugroup_id']);
        }
        $cutoffDate = date('Y-m-d', strtotime('-'.(int)$filters['older_than_days'].' day'));
        $cutoffDateTime = date('Y-m-d H:i:s', strtotime('-'.(int)$filters['older_than_days'].' day'));
        $conditions[] = array(
            'OR' => array(
                array('Lead.last_contact_date <=' => $cutoffDate),
                array('Lead.follow_up <=' => $cutoffDateTime),
                array(
                    'Lead.last_contact_date' => null,
                    'OR' => array(
                        array('Lead.follow_up' => null),
                        array('Lead.follow_up' => '0000-00-00 00:00:00')
                    )
                )
            )
        );
        $this->Paginator->settings = array(
            'Lead' => array(
                'fields' => array(
                    'Lead.id','Lead.project_id','Lead.user_id','Lead.client_id','Lead.lead_name','Lead.lead_mobile','Lead.lead_email',
                    'Lead.last_contact_date','Lead.follow_up','Lead.remarks',
                    'User.id','User.name',
                    'Project.id','Project.name',
                    'Status.id','Status.name',
                    'Client.id','Client.name','Client.mobile','Client.email'
                ),
                'joins' => $joins,
                'conditions' => $conditions,
                'order' => array('Lead.last_contact_date' => 'asc', 'Lead.follow_up' => 'asc', 'Lead.id' => 'desc'),
                'recursive' => -1,
                'limit' => 50
            )
        );
        $leadRows = (array)$this->Paginator->paginate('Lead');
        $this->set(compact('projectOptions', 'userLevelOptions', 'userOptions', 'statusOptions', 'leadRows', 'filters', 'isSuperAdmin', 'canUseAgentFilters', 'canUseUserLevelFilters'));
    }
}
