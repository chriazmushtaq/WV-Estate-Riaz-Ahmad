<?php
App::uses('AdminAppController', 'Admin.Controller');
App::uses('ConnectionManager', 'Model');
App::uses('CakeLog', 'Log');

class BusinessfeaturesController extends AdminAppController
{
    public $uses = array('Project');
    protected $_featureTablesReady = true;

    protected $_featureMap = array(
        'lead_sla' => array('route' => 'leadsla', 'name' => 'Lead SLA Automation', 'icon' => 'fa-clock-o'),
        'source_roi' => array('route' => 'sourceroi', 'name' => 'Source ROI Dashboard', 'icon' => 'fa-pie-chart'),
        'client_journey' => array('route' => 'clientjourney', 'name' => 'Client Journey Timeline', 'icon' => 'fa-road'),
        'property_explorer' => array('route' => 'propertyexplorer', 'name' => 'Property Availability Explorer', 'icon' => 'fa-map-marker'),
        'payment_overdue' => array('route' => 'paymentoverdue', 'name' => 'Payment Overdue Center', 'icon' => 'fa-bell-o'),
        'letter_workflow' => array('route' => 'letterworkflow', 'name' => 'Letter Workflow Engine', 'icon' => 'fa-envelope-o'),
        'document_vault' => array('route' => 'documentvault', 'name' => 'Document Vault', 'icon' => 'fa-folder-open-o'),
        'agent_scorecard' => array('route' => 'agentscorecard', 'name' => 'Agent Scorecard', 'icon' => 'fa-line-chart'),
        'portal_actions' => array('route' => 'portalactions', 'name' => 'Client Self-Service Actions', 'icon' => 'fa-user-circle-o'),
        'website_conversion' => array('route' => 'websiteconversion', 'name' => 'Website Conversion Upgrades', 'icon' => 'fa-bullseye')
    );

    public function beforeFilter()
    {
        parent::beforeFilter();
        if($this->_shouldRunControllerBootstrap('businessfeatures.schema', '2026-04-09.1'))
        {
            $this->_featureTablesReady = $this->_ensureFeatureTables();
            // Avoid stale Cake table-source cache only when schema check runs.
            $this->_clearFeatureSchemaCache();
        }
        else
        {
            $this->_featureTablesReady = true;
        }
        if(!$this->_featureTablesReady)
        {
            $this->Session->setFlash(
                __('Unable to initialize Growth Features tables. Please check database permissions.'),
                'flash',
                array('alert' => 'danger')
            );
            return $this->redirect(array('plugin' => 'admin', 'controller' => 'Dashboards', 'action' => 'index'));
        }
        $this->loadModel('Admin.Businessfeature');
        $this->loadModel('Admin.Businessfeaturesetting');
        $this->set('featureMap', $this->_featureMap);
        $this->set('canUseAgencyFilters', $this->_canUseAgencyFilters());
        $this->set('projectName', $this->_projectList());
    }

    protected function _canUseAgencyFilters()
    {
        return $this->_isSuperAdminSession();
    }

    protected function _projectList()
    {
        return (array)$this->CustomFunction->agencyWiseList($this->userType, $this->agencyId);
    }

    protected function _selectedProjectId($fallback = 0)
    {
        $projectId = (int)$fallback;
        if($this->request->is('post') && isset($this->request->data['Businessfeature']['project_id']))
        {
            $projectId = (int)$this->request->data['Businessfeature']['project_id'];
        }
        elseif($this->request->is('post') && isset($this->request->data['Businessfeaturesetting']['project_id']))
        {
            $projectId = (int)$this->request->data['Businessfeaturesetting']['project_id'];
        }
        elseif(isset($this->request->query['project_id']))
        {
            $projectId = (int)$this->request->query['project_id'];
        }
        if(!$this->_canUseAgencyFilters())
        {
            return (int)$this->agencyId;
        }
        return max(0, (int)$projectId);
    }

    protected function _isAllowedProject($projectId)
    {
        $projectId = (int)$projectId;
        if($projectId <= 0)
        {
            return $this->_canUseAgencyFilters();
        }
        if($this->_canUseAgencyFilters())
        {
            return true;
        }
        return ((int)$this->agencyId === $projectId);
    }

    protected function _featureByRoute($routeAction)
    {
        $routeAction = strtolower(trim((string)$routeAction));
        foreach($this->_featureMap as $featureKey => $featureCfg)
        {
            if(strtolower((string)$featureCfg['route']) === $routeAction)
            {
                return (string)$featureKey;
            }
        }
        return '';
    }

    protected function _featureByKey($featureKey)
    {
        $featureKey = strtolower(trim((string)$featureKey));
        if($featureKey === '' || !isset($this->_featureMap[$featureKey]))
        {
            return array();
        }
        return $this->_featureMap[$featureKey];
    }

    protected function _featureIndexUrl($featureKey, $projectId = null)
    {
        $cfg = $this->_featureByKey($featureKey);
        if(empty($cfg['route']))
        {
            return array('action' => 'dashboard');
        }
        $url = array('action' => (string)$cfg['route']);
        if($projectId !== null)
        {
            $url['?'] = array('project_id' => (int)$projectId);
        }
        return $url;
    }

    protected function _normalizeDate($value)
    {
        $value = trim((string)$value);
        if($value === '')
        {
            return null;
        }
        if(preg_match('/^\d{4}-\d{2}-\d{2}$/', $value))
        {
            return $value;
        }
        $ts = strtotime($value);
        if($ts === false)
        {
            return null;
        }
        return date('Y-m-d', $ts);
    }

    protected function _sanitizeRuleJson($value)
    {
        $value = trim((string)$value);
        if($value === '')
        {
            return '';
        }
        $decoded = json_decode($value, true);
        if(json_last_error() !== JSON_ERROR_NONE)
        {
            return '';
        }
        return json_encode($decoded);
    }

    protected function _featureItemConditions($featureKey, $projectId = 0)
    {
        $conditions = array('Businessfeature.feature_key' => (string)$featureKey);
        if((int)$projectId > 0)
        {
            $conditions['Businessfeature.project_id'] = (int)$projectId;
        }
        elseif(!$this->_canUseAgencyFilters())
        {
            $conditions['Businessfeature.project_id'] = (int)$this->agencyId;
        }
        return $conditions;
    }

    protected function _featureSettingConditions($featureKey, $projectId = 0)
    {
        $conditions = array('Businessfeaturesetting.feature_key' => (string)$featureKey);
        if((int)$projectId > 0)
        {
            $conditions['Businessfeaturesetting.project_id'] = (int)$projectId;
        }
        elseif(!$this->_canUseAgencyFilters())
        {
            $conditions['Businessfeaturesetting.project_id'] = (int)$this->agencyId;
        }
        return $conditions;
    }

    protected function _featureIndex($featureKey)
    {
        $featureCfg = $this->_featureByKey($featureKey);
        if(empty($featureCfg))
        {
            throw new NotFoundException(__('Invalid feature.'));
        }
        $projectId = $this->_selectedProjectId($this->_canUseAgencyFilters() ? 0 : (int)$this->agencyId);
        if(!$this->_isAllowedProject($projectId))
        {
            $this->Session->setFlash(__('You do not have permission for selected agency.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'dashboard'));
        }

        $conditions = $this->_featureItemConditions($featureKey, $projectId);
        $rows = $this->Businessfeature->find('all', array(
            'conditions' => $conditions,
            'joins' => array(
                array('table' => 'projects', 'alias' => 'Project', 'type' => 'LEFT', 'conditions' => array('Project.id=Businessfeature.project_id'))
            ),
            'fields' => array('Businessfeature.*', 'Project.name'),
            'order' => array('Businessfeature.priority' => 'desc', 'Businessfeature.id' => 'desc'),
            'recursive' => -1
        ));

        $this->set('selectedProjectId', (int)$projectId);
        $this->set('featureKey', (string)$featureKey);
        $this->set('featureCfg', $featureCfg);
        $this->set('rows', $rows);
        $this->render('/Features/business_index');
    }

    public function dashboard()
    {
        $projectId = $this->_selectedProjectId($this->_canUseAgencyFilters() ? 0 : (int)$this->agencyId);
        if(!$this->_isAllowedProject($projectId))
        {
            $this->Session->setFlash(__('You do not have permission for selected agency.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'dashboard'));
        }

        $cards = array();
        foreach($this->_featureMap as $featureKey => $featureCfg)
        {
            $count = (int)$this->Businessfeature->find('count', array(
                'conditions' => $this->_featureItemConditions($featureKey, $projectId),
                'recursive' => -1
            ));
            $cards[] = array(
                'feature_key' => $featureKey,
                'title' => $featureCfg['name'],
                'icon' => $featureCfg['icon'],
                'count' => $count,
                'url' => $this->_featureIndexUrl($featureKey, $projectId)
            );
        }
        $this->set('selectedProjectId', (int)$projectId);
        $this->set('cards', $cards);
        $this->render('/Features/business_dashboard');
    }

    public function index()
    {
        return $this->redirect(array('action' => 'dashboard'));
    }

    public function add($feature = null)
    {
        $featureKey = strtolower(trim((string)$feature));
        $featureCfg = $this->_featureByKey($featureKey);
        if(empty($featureCfg))
        {
            throw new NotFoundException(__('Invalid feature.'));
        }
        $projectId = $this->_selectedProjectId($this->_canUseAgencyFilters() ? 0 : (int)$this->agencyId);
        if(!$this->_isAllowedProject($projectId))
        {
            $this->Session->setFlash(__('You do not have permission for selected agency.'), 'flash', array('alert' => 'danger'));
            return $this->redirect($this->_featureIndexUrl($featureKey));
        }

        if($this->request->is('post'))
        {
            $saveData = isset($this->request->data['Businessfeature']) ? (array)$this->request->data['Businessfeature'] : array();
            $saveData['feature_key'] = $featureKey;
            if(!$this->_canUseAgencyFilters())
            {
                $saveData['project_id'] = (int)$this->agencyId;
            }
            else
            {
                $saveData['project_id'] = (int)(isset($saveData['project_id']) ? $saveData['project_id'] : $projectId);
            }
            if(!$this->_isAllowedProject((int)$saveData['project_id']))
            {
                $this->Session->setFlash(__('Invalid agency selection.'), 'flash', array('alert' => 'danger'));
                return $this->redirect($this->_featureIndexUrl($featureKey));
            }
            $saveData['start_date'] = $this->_normalizeDate(isset($saveData['start_date']) ? $saveData['start_date'] : '');
            $saveData['end_date'] = $this->_normalizeDate(isset($saveData['end_date']) ? $saveData['end_date'] : '');
            $saveData['rule_json'] = $this->_sanitizeRuleJson(isset($saveData['rule_json']) ? $saveData['rule_json'] : '');
            $saveData['priority'] = (int)(isset($saveData['priority']) ? $saveData['priority'] : 0);
            $saveData['status'] = (!empty($saveData['status']) && $saveData['status'] === 'Inactive') ? 'Inactive' : 'Active';
            $saveData['created_by'] = (int)$this->luserId;
            $saveData['modified_by'] = (int)$this->luserId;
            $this->Businessfeature->create();
            if($this->Businessfeature->save(array('Businessfeature' => $saveData)))
            {
                $this->Session->setFlash(__('Feature record added successfully.'), 'flash', array('alert' => 'success'));
                return $this->redirect($this->_featureIndexUrl($featureKey, (int)$saveData['project_id']));
            }
            $this->Session->setFlash(__('Unable to save record.'), 'flash', array('alert' => 'danger'));
        }
        else
        {
            $this->request->data['Businessfeature'] = array(
                'project_id' => (int)$projectId,
                'status' => 'Active',
                'priority' => 0
            );
        }

        $this->set('selectedProjectId', (int)$projectId);
        $this->set('featureKey', (string)$featureKey);
        $this->set('featureCfg', $featureCfg);
        $this->set('isEdit', false);
        $this->render('/Features/business_form');
    }

    public function edit($id = null)
    {
        $id = (int)$id;
        if($id <= 0)
        {
            throw new NotFoundException(__('Invalid record.'));
        }
        $row = $this->Businessfeature->find('first', array(
            'conditions' => array('Businessfeature.id' => $id),
            'recursive' => -1
        ));
        if(empty($row['Businessfeature']['id']))
        {
            throw new NotFoundException(__('Record not found.'));
        }
        $featureKey = trim((string)$row['Businessfeature']['feature_key']);
        $featureCfg = $this->_featureByKey($featureKey);
        if(empty($featureCfg))
        {
            throw new NotFoundException(__('Invalid feature.'));
        }
        if(!$this->_isAllowedProject((int)$row['Businessfeature']['project_id']))
        {
            $this->Session->setFlash(__('Permission denied for this record.'), 'flash', array('alert' => 'danger'));
            return $this->redirect($this->_featureIndexUrl($featureKey));
        }

        if($this->request->is(array('post', 'put')))
        {
            $saveData = isset($this->request->data['Businessfeature']) ? (array)$this->request->data['Businessfeature'] : array();
            $saveData['id'] = $id;
            $saveData['feature_key'] = $featureKey;
            if(!$this->_canUseAgencyFilters())
            {
                $saveData['project_id'] = (int)$this->agencyId;
            }
            else
            {
                $saveData['project_id'] = (int)(isset($saveData['project_id']) ? $saveData['project_id'] : $row['Businessfeature']['project_id']);
            }
            if(!$this->_isAllowedProject((int)$saveData['project_id']))
            {
                $this->Session->setFlash(__('Invalid agency selection.'), 'flash', array('alert' => 'danger'));
                return $this->redirect($this->_featureIndexUrl($featureKey));
            }
            $saveData['start_date'] = $this->_normalizeDate(isset($saveData['start_date']) ? $saveData['start_date'] : '');
            $saveData['end_date'] = $this->_normalizeDate(isset($saveData['end_date']) ? $saveData['end_date'] : '');
            $saveData['rule_json'] = $this->_sanitizeRuleJson(isset($saveData['rule_json']) ? $saveData['rule_json'] : '');
            $saveData['priority'] = (int)(isset($saveData['priority']) ? $saveData['priority'] : 0);
            $saveData['status'] = (!empty($saveData['status']) && $saveData['status'] === 'Inactive') ? 'Inactive' : 'Active';
            $saveData['modified_by'] = (int)$this->luserId;
            if($this->Businessfeature->save(array('Businessfeature' => $saveData)))
            {
                $this->Session->setFlash(__('Feature record updated successfully.'), 'flash', array('alert' => 'success'));
                return $this->redirect($this->_featureIndexUrl($featureKey, (int)$saveData['project_id']));
            }
            $this->Session->setFlash(__('Unable to update record.'), 'flash', array('alert' => 'danger'));
        }
        else
        {
            $this->request->data['Businessfeature'] = $row['Businessfeature'];
        }

        $this->set('selectedProjectId', (int)$row['Businessfeature']['project_id']);
        $this->set('featureKey', (string)$featureKey);
        $this->set('featureCfg', $featureCfg);
        $this->set('isEdit', true);
        $this->set('rowId', $id);
        $this->render('/Features/business_form');
    }

    public function view($id = null)
    {
        $id = (int)$id;
        if($id <= 0)
        {
            throw new NotFoundException(__('Invalid record.'));
        }
        $row = $this->Businessfeature->find('first', array(
            'conditions' => array('Businessfeature.id' => $id),
            'joins' => array(
                array('table' => 'projects', 'alias' => 'Project', 'type' => 'LEFT', 'conditions' => array('Project.id=Businessfeature.project_id'))
            ),
            'fields' => array('Businessfeature.*', 'Project.name'),
            'recursive' => -1
        ));
        if(empty($row['Businessfeature']['id']))
        {
            throw new NotFoundException(__('Record not found.'));
        }
        if(!$this->_isAllowedProject((int)$row['Businessfeature']['project_id']))
        {
            $this->Session->setFlash(__('Permission denied for this record.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'dashboard'));
        }
        $featureKey = trim((string)$row['Businessfeature']['feature_key']);
        $featureCfg = $this->_featureByKey($featureKey);
        $this->set('featureKey', (string)$featureKey);
        $this->set('featureCfg', $featureCfg);
        $this->set('row', $row);
        $this->render('/Features/business_view');
    }

    public function delete($id = null)
    {
        $this->request->allowMethod('post');
        $id = (int)$id;
        if($id <= 0)
        {
            $this->Session->setFlash(__('Invalid record.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'dashboard'));
        }
        $row = $this->Businessfeature->find('first', array(
            'conditions' => array('Businessfeature.id' => $id),
            'recursive' => -1
        ));
        if(empty($row['Businessfeature']['id']))
        {
            $this->Session->setFlash(__('Record not found.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'dashboard'));
        }
        $featureKey = trim((string)$row['Businessfeature']['feature_key']);
        if(!$this->_isAllowedProject((int)$row['Businessfeature']['project_id']))
        {
            $this->Session->setFlash(__('Permission denied for this record.'), 'flash', array('alert' => 'danger'));
            return $this->redirect($this->_featureIndexUrl($featureKey));
        }
        if($this->Businessfeature->delete($id))
        {
            $this->Session->setFlash(__('Record deleted successfully.'), 'flash', array('alert' => 'success'));
        }
        else
        {
            $this->Session->setFlash(__('Unable to delete record.'), 'flash', array('alert' => 'danger'));
        }
        return $this->redirect($this->_featureIndexUrl($featureKey, (int)$row['Businessfeature']['project_id']));
    }

    public function settings($feature = null)
    {
        $featureKey = strtolower(trim((string)$feature));
        $featureCfg = $this->_featureByKey($featureKey);
        if(empty($featureCfg))
        {
            throw new NotFoundException(__('Invalid feature.'));
        }
        $projectId = $this->_selectedProjectId($this->_canUseAgencyFilters() ? 0 : (int)$this->agencyId);
        if(!$this->_isAllowedProject($projectId))
        {
            $this->Session->setFlash(__('You do not have permission for selected agency.'), 'flash', array('alert' => 'danger'));
            return $this->redirect($this->_featureIndexUrl($featureKey));
        }

        if($this->request->is('post'))
        {
            $posted = isset($this->request->data['Businessfeaturesetting']) ? (array)$this->request->data['Businessfeaturesetting'] : array();
            $task = strtolower(trim((string)(isset($posted['task']) ? $posted['task'] : 'save_setting')));
            if($task === 'delete_setting')
            {
                $deleteId = (int)(isset($posted['id']) ? $posted['id'] : 0);
                $row = $this->Businessfeaturesetting->find('first', array(
                    'conditions' => array('Businessfeaturesetting.id' => $deleteId),
                    'recursive' => -1
                ));
                if(!empty($row['Businessfeaturesetting']['id']) && $this->_isAllowedProject((int)$row['Businessfeaturesetting']['project_id']))
                {
                    $this->Businessfeaturesetting->delete((int)$row['Businessfeaturesetting']['id']);
                    $this->Session->setFlash(__('Setting deleted.'), 'flash', array('alert' => 'success'));
                }
                else
                {
                    $this->Session->setFlash(__('Invalid setting record.'), 'flash', array('alert' => 'danger'));
                }
                return $this->redirect(array('action' => 'settings', $featureKey, '?' => array('project_id' => (int)$projectId)));
            }

            $saveId = (int)(isset($posted['id']) ? $posted['id'] : 0);
            $saveData = array(
                'id' => ($saveId > 0 ? $saveId : null),
                'project_id' => ($this->_canUseAgencyFilters() ? (int)(isset($posted['project_id']) ? $posted['project_id'] : $projectId) : (int)$this->agencyId),
                'feature_key' => $featureKey,
                'setting_key' => trim((string)(isset($posted['setting_key']) ? $posted['setting_key'] : '')),
                'setting_value' => (string)(isset($posted['setting_value']) ? $posted['setting_value'] : ''),
                'status' => (!empty($posted['status']) && $posted['status'] === 'Inactive') ? 'Inactive' : 'Active'
            );
            if(!$this->_isAllowedProject((int)$saveData['project_id']))
            {
                $this->Session->setFlash(__('Invalid agency selection.'), 'flash', array('alert' => 'danger'));
                return $this->redirect(array('action' => 'settings', $featureKey, '?' => array('project_id' => (int)$projectId)));
            }
            if($saveData['setting_key'] === '')
            {
                $this->Session->setFlash(__('Setting key is required.'), 'flash', array('alert' => 'danger'));
                return $this->redirect(array('action' => 'settings', $featureKey, '?' => array('project_id' => (int)$projectId)));
            }
            if($saveId > 0)
            {
                $this->Businessfeaturesetting->id = $saveId;
            }
            else
            {
                $this->Businessfeaturesetting->create();
            }
            if($this->Businessfeaturesetting->save(array('Businessfeaturesetting' => $saveData)))
            {
                $this->Session->setFlash(__('Settings saved successfully.'), 'flash', array('alert' => 'success'));
            }
            else
            {
                $this->Session->setFlash(__('Unable to save setting.'), 'flash', array('alert' => 'danger'));
            }
            return $this->redirect(array('action' => 'settings', $featureKey, '?' => array('project_id' => (int)$saveData['project_id'])));
        }

        $conditions = $this->_featureSettingConditions($featureKey, $projectId);
        $rows = $this->Businessfeaturesetting->find('all', array(
            'conditions' => $conditions,
            'joins' => array(
                array('table' => 'projects', 'alias' => 'Project', 'type' => 'LEFT', 'conditions' => array('Project.id=Businessfeaturesetting.project_id'))
            ),
            'fields' => array('Businessfeaturesetting.*', 'Project.name'),
            'order' => array('Businessfeaturesetting.id' => 'desc'),
            'recursive' => -1
        ));

        $editId = isset($this->request->query['edit']) ? (int)$this->request->query['edit'] : 0;
        $formRow = array(
            'id' => 0,
            'project_id' => (int)$projectId,
            'setting_key' => '',
            'setting_value' => '',
            'status' => 'Active'
        );
        if($editId > 0)
        {
            foreach($rows as $row)
            {
                if((int)$row['Businessfeaturesetting']['id'] === $editId)
                {
                    $formRow = $row['Businessfeaturesetting'];
                    break;
                }
            }
        }

        $this->set('selectedProjectId', (int)$projectId);
        $this->set('featureKey', (string)$featureKey);
        $this->set('featureCfg', $featureCfg);
        $this->set('rows', $rows);
        $this->set('formRow', $formRow);
        $this->render('/Features/business_settings');
    }

    public function leadsla()
    {
        return $this->_featureIndex('lead_sla');
    }

    public function sourceroi()
    {
        return $this->_featureIndex('source_roi');
    }

    public function clientjourney()
    {
        return $this->_featureIndex('client_journey');
    }

    public function propertyexplorer()
    {
        return $this->_featureIndex('property_explorer');
    }

    public function paymentoverdue()
    {
        return $this->_featureIndex('payment_overdue');
    }

    public function letterworkflow()
    {
        return $this->_featureIndex('letter_workflow');
    }

    public function documentvault()
    {
        return $this->_featureIndex('document_vault');
    }

    public function agentscorecard()
    {
        return $this->_featureIndex('agent_scorecard');
    }

    public function portalactions()
    {
        return $this->_featureIndex('portal_actions');
    }

    public function websiteconversion()
    {
        return $this->_featureIndex('website_conversion');
    }

    protected function _tableExists($db, $tableName)
    {
        $tableName = trim((string)$tableName);
        if($tableName === '')
        {
            return false;
        }
        try
        {
            $rows = (array)$db->query("SHOW TABLES LIKE '".$tableName."'");
        }
        catch(Exception $e)
        {
            return false;
        }
        return !empty($rows);
    }

    protected function _clearFeatureSchemaCache()
    {
        foreach((array)glob(APP.'tmp'.DS.'cache'.DS.'models'.DS.'*businessfeature*') as $cacheFile)
        {
            @unlink($cacheFile);
        }
        foreach((array)glob(APP.'tmp'.DS.'cache'.DS.'models'.DS.'*business_feature*') as $cacheFile)
        {
            @unlink($cacheFile);
        }
        @unlink(APP.'tmp'.DS.'cache'.DS.'persistent'.DS.'myapp_cake_core_method_cache');
    }

    protected function _ensureFeatureTables()
    {
        $dbConfig = 'default';
        if(isset($this->Businessfeature) && !empty($this->Businessfeature->useDbConfig))
        {
            $dbConfig = (string)$this->Businessfeature->useDbConfig;
        }
        try
        {
            $db = ConnectionManager::getDataSource($dbConfig);
        }
        catch(Exception $e)
        {
            CakeLog::write('error', 'Businessfeatures datasource init failed: '.$e->getMessage());
            return false;
        }

        $schemaTouched = false;
        $tableSql = array(
            'business_features' => array(
                "CREATE TABLE IF NOT EXISTS `business_features` (
                    `id` bigint(20) NOT NULL AUTO_INCREMENT,
                    `project_id` int(11) NOT NULL DEFAULT 0,
                    `feature_key` varchar(60) NOT NULL,
                    `title` varchar(180) NOT NULL,
                    `subtitle` varchar(255) DEFAULT '',
                    `trigger_event` varchar(80) DEFAULT '',
                    `priority` int(11) NOT NULL DEFAULT 0,
                    `status` varchar(20) NOT NULL DEFAULT 'Active',
                    `start_date` date DEFAULT NULL,
                    `end_date` date DEFAULT NULL,
                    `rule_json` longtext,
                    `notes` text,
                    `created_by` int(11) NOT NULL DEFAULT 0,
                    `modified_by` int(11) NOT NULL DEFAULT 0,
                    `created` datetime DEFAULT NULL,
                    `modified` datetime DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `project_id` (`project_id`),
                    KEY `feature_key` (`feature_key`),
                    KEY `status` (`status`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",
                "CREATE TABLE IF NOT EXISTS `business_features` (
                    `id` bigint(20) NOT NULL AUTO_INCREMENT,
                    `project_id` int(11) NOT NULL DEFAULT 0,
                    `feature_key` varchar(60) NOT NULL,
                    `title` varchar(180) NOT NULL,
                    `subtitle` varchar(255) DEFAULT '',
                    `trigger_event` varchar(80) DEFAULT '',
                    `priority` int(11) NOT NULL DEFAULT 0,
                    `status` varchar(20) NOT NULL DEFAULT 'Active',
                    `start_date` date DEFAULT NULL,
                    `end_date` date DEFAULT NULL,
                    `rule_json` longtext,
                    `notes` text,
                    `created_by` int(11) NOT NULL DEFAULT 0,
                    `modified_by` int(11) NOT NULL DEFAULT 0,
                    `created` datetime DEFAULT NULL,
                    `modified` datetime DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `project_id` (`project_id`),
                    KEY `feature_key` (`feature_key`),
                    KEY `status` (`status`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8"
            ),
            'business_feature_settings' => array(
                "CREATE TABLE IF NOT EXISTS `business_feature_settings` (
                    `id` bigint(20) NOT NULL AUTO_INCREMENT,
                    `project_id` int(11) NOT NULL DEFAULT 0,
                    `feature_key` varchar(60) NOT NULL,
                    `setting_key` varchar(120) NOT NULL,
                    `setting_value` longtext,
                    `status` varchar(20) NOT NULL DEFAULT 'Active',
                    `created` datetime DEFAULT NULL,
                    `modified` datetime DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `uniq_feature_setting` (`project_id`,`feature_key`,`setting_key`),
                    KEY `feature_key` (`feature_key`),
                    KEY `status` (`status`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci",
                "CREATE TABLE IF NOT EXISTS `business_feature_settings` (
                    `id` bigint(20) NOT NULL AUTO_INCREMENT,
                    `project_id` int(11) NOT NULL DEFAULT 0,
                    `feature_key` varchar(60) NOT NULL,
                    `setting_key` varchar(120) NOT NULL,
                    `setting_value` longtext,
                    `status` varchar(20) NOT NULL DEFAULT 'Active',
                    `created` datetime DEFAULT NULL,
                    `modified` datetime DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `uniq_feature_setting` (`project_id`,`feature_key`,`setting_key`),
                    KEY `feature_key` (`feature_key`),
                    KEY `status` (`status`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8"
            )
        );

        foreach($tableSql as $tableName => $sqlList)
        {
            if($this->_tableExists($db, $tableName))
            {
                continue;
            }
            $schemaTouched = true;
            foreach($sqlList as $sql)
            {
                try
                {
                    $db->query($sql);
                }
                catch(Exception $e)
                {
                    CakeLog::write('error', 'Businessfeatures table create failed for '.$tableName.': '.$e->getMessage());
                }
                if($this->_tableExists($db, $tableName))
                {
                    break;
                }
            }
        }

        if($schemaTouched)
        {
            $this->_clearFeatureSchemaCache();
        }

        if(!$this->_tableExists($db, 'business_features') || !$this->_tableExists($db, 'business_feature_settings'))
        {
            CakeLog::write('error', 'Businessfeatures schema missing after ensure.');
            return false;
        }
        return true;
    }
}
