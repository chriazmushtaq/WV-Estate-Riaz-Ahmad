<?php
App::uses('CakeTime', 'Utility');
App::uses('CakeNumber', 'Utility');
App::uses('Hash', 'Utility');
App::uses('SimplePasswordHasher', 'Controller/Component/Auth');

class RecoveriesController extends AdminAppController
{
    public $helpers = array('Html', 'Form', 'Session', 'Paginator', 'Js' => array('Jquery'));
    public $components = array('Session', 'Paginator');
    public $uses = array(
        'Admin.RecoveryOfficer',
        'Admin.RecoveryCase',
        'Admin.RecoveryFollowup',
        'Admin.RecoverySetting',
        'Admin.PlansPayment'
    );

    protected $_blockedDealStatuses = array('Transferd', 'Transferred', 'Permanent Blocked', 'Pemanent Blocked', 'Refund', 'Refund-Running', 'Merged', 'Reversed');

    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->_ensureRecoverySchema();
        $this->_repairRecoveryOfficerLoginPasswords();
        if($this->_isSuperAdminSession((array)$this->adminValue))
        {
            $this->userPermissionArr = $this->_defaultFullPermission();
            $this->set('userPermissionArr', $this->userPermissionArr);
            $this->set('allowAddAction', true);
            $this->set('allowEditAction', true);
            $this->set('allowDeleteAction', true);
            $this->set('allowViewAction', true);
        }
    }

    public function index()
    {
        $today = CakeTime::format('Y-m-d', CakeTime::convert(time(), $this->siteTimezone));
        $query = (array)$this->request->query;
        $selectedAgencyId = $this->_selectedAgencyId($query);
        $status = !empty($query['status']) ? strtolower(trim((string)$query['status'])) : 'overdue';
        if(!in_array($status, array('all', 'due_soon', 'overdue'), true))
        {
            $status = 'overdue';
        }
        $daysBefore = isset($query['days_before']) ? (int)$query['days_before'] : 7;
        $daysBefore = max(0, min(90, $daysBefore));
        $keyword = isset($query['keyword']) ? trim((string)$query['keyword']) : '';
        $officerFilter = isset($query['officer_id']) ? (int)$query['officer_id'] : 0;
        $caseStatusFilter = !empty($query['case_status']) ? trim((string)$query['case_status']) : '';

        $conditions = array('DealsPayment.id IS NULL');
        if($status === 'due_soon')
        {
            $conditions[] = array('PlansPayment.date >=' => $today, 'PlansPayment.date <=' => date('Y-m-d', strtotime($today.' + '.$daysBefore.' days')));
        }
        elseif($status === 'overdue')
        {
            $conditions[] = array('PlansPayment.date <' => $today);
        }
        else
        {
            $conditions[] = array('PlansPayment.date <=' => date('Y-m-d', strtotime($today.' + '.$daysBefore.' days')));
        }
        if($keyword !== '')
        {
            $conditions[] = array('OR' => array(
                'Client.name LIKE' => '%'.$keyword.'%',
                'Client.phone LIKE' => '%'.$keyword.'%',
                'Client.mobile LIKE' => '%'.$keyword.'%',
                'Deal.invoice_no LIKE' => '%'.$keyword.'%',
                'Property.name LIKE' => '%'.$keyword.'%',
                'Project.name LIKE' => '%'.$keyword.'%'
            ));
        }
        $permission = $this->_recoveryPermissionCondition($selectedAgencyId);
        if(!empty($permission))
        {
            $conditions[] = $permission;
        }
        $dealStatusCondition = $this->_dealStatusCondition();
        if(!empty($dealStatusCondition))
        {
            $conditions[] = $dealStatusCondition;
        }

        $rawRows = array();
        try
        {
            $rawRows = (array)$this->PlansPayment->find('all', array(
                'fields' => $this->_installmentFields(),
                'joins' => $this->_installmentJoins(),
                'conditions' => $conditions,
                'order' => array('PlansPayment.date' => 'asc', 'PlansPayment.id' => 'asc'),
                'recursive' => -1
            ));
        }
        catch(Exception $e)
        {
            $rawRows = array();
        }

        $rows = $this->_groupOverdueRowsByDeal($rawRows, $today);
        $dealIds = $this->_dealIdsFromRows($rows);
        $caseMap = $this->_caseMap($dealIds);
        $lastFollowupMap = $this->_lastFollowupMap($dealIds);
        $rows = $this->_attachRecoveryMeta($rows, $caseMap, $lastFollowupMap, $officerFilter, $caseStatusFilter);
        $summary = $this->_dashboardSummary($rows, $today);
        $pagination = $this->_paginateRows($rows);
        $rows = $pagination['rows'];
        unset($pagination['rows']);

        $agencyOptions = $this->_agencyOptions();
        $officerOptions = $this->_officerOptions($selectedAgencyId, true);
        $caseStatuses = $this->_caseStatuses();
        $actionTypes = $this->_actionTypes();
        $priorityOptions = $this->_priorityOptions();

        $this->set(compact('rows', 'summary', 'pagination', 'query', 'selectedAgencyId', 'agencyOptions', 'officerOptions', 'status', 'daysBefore', 'keyword', 'officerFilter', 'caseStatusFilter', 'caseStatuses', 'actionTypes', 'priorityOptions', 'today'));
    }

    public function assign()
    {
        if(!$this->request->is('post'))
        {
            throw new MethodNotAllowedException(__('Invalid request method.'));
        }
        $dealId = (int)Hash::get($this->request->data, 'Recovery.deal_id', 0);
        $officerId = (int)Hash::get($this->request->data, 'Recovery.recovery_officer_id', 0);
        $status = trim((string)Hash::get($this->request->data, 'Recovery.status', 'New'));
        $priority = trim((string)Hash::get($this->request->data, 'Recovery.priority', 'Normal'));
        $deal = $this->_dealMeta($dealId);
        if(empty($deal['Deal']['id']))
        {
            $this->Session->setFlash(__('Invalid recovery deal.'), 'flash', array('alert' => 'danger'));
            return $this->redirect($this->referer(array('action' => 'index'), true));
        }
        if(!$this->_officerAllowedForDeal($officerId, (int)$deal['Property']['project_id']))
        {
            $this->Session->setFlash(__('Selected recovery officer is not available for this agency.'), 'flash', array('alert' => 'danger'));
            return $this->redirect($this->referer(array('action' => 'index'), true));
        }
        $this->_upsertRecoveryCase($deal, array(
            'recovery_officer_id' => $officerId,
            'status' => $this->_cleanCaseStatus($status),
            'priority' => $this->_cleanPriority($priority)
        ));
        $this->Session->setFlash(__('Recovery officer assignment saved.'), 'flash', array('alert' => 'success'));
        return $this->redirect($this->referer(array('action' => 'index'), true));
    }

    public function followup()
    {
        if(!$this->request->is('post'))
        {
            throw new MethodNotAllowedException(__('Invalid request method.'));
        }
        $data = (array)Hash::get($this->request->data, 'Recovery', array());
        $dealId = !empty($data['deal_id']) ? (int)$data['deal_id'] : 0;
        $deal = $this->_dealMeta($dealId);
        if(empty($deal['Deal']['id']))
        {
            $this->Session->setFlash(__('Invalid recovery deal.'), 'flash', array('alert' => 'danger'));
            return $this->redirect($this->referer(array('action' => 'index'), true));
        }

        $projectId = (int)$deal['Property']['project_id'];
        $officerId = !empty($data['recovery_officer_id']) ? (int)$data['recovery_officer_id'] : 0;
        if(!$this->_officerAllowedForDeal($officerId, $projectId))
        {
            $this->Session->setFlash(__('Please select a valid recovery officer for this agency.'), 'flash', array('alert' => 'danger'));
            return $this->redirect($this->referer(array('action' => 'index'), true));
        }

        $note = trim((string)Hash::get($data, 'note', ''));
        if($note === '')
        {
            $this->Session->setFlash(__('Please enter recovery notes.'), 'flash', array('alert' => 'danger'));
            return $this->redirect($this->referer(array('action' => 'index'), true));
        }

        $promiseDate = $this->_normalizeDate(Hash::get($data, 'promise_date', ''));
        $nextActionDate = $this->_normalizeDate(Hash::get($data, 'next_action_date', ''));
        if($promiseDate === false || $nextActionDate === false)
        {
            $this->Session->setFlash(__('Please enter valid promise/next action dates.'), 'flash', array('alert' => 'danger'));
            return $this->redirect($this->referer(array('action' => 'index'), true));
        }
        $status = $this->_cleanCaseStatus(Hash::get($data, 'status', 'Contacted'));
        $actionType = $this->_cleanActionType(Hash::get($data, 'action_type', 'Call'));
        $priority = $this->_cleanPriority(Hash::get($data, 'priority', 'Normal'));

        $caseId = $this->_upsertRecoveryCase($deal, array(
            'recovery_officer_id' => $officerId,
            'status' => $status,
            'priority' => $priority,
            'promise_date' => $promiseDate,
            'next_action_date' => $nextActionDate,
            'last_contact_at' => date('Y-m-d H:i:s'),
            'last_note' => $note
        ));

        $contactNo = trim((string)(!empty($deal['Client']['mobile']) ? $deal['Client']['mobile'] : $deal['Client']['phone']));
        $planId = !empty($data['plans_payment_id']) ? (int)$data['plans_payment_id'] : 0;
        $this->RecoveryFollowup->create();
        $this->RecoveryFollowup->save(array('RecoveryFollowup' => array(
            'project_id' => $projectId,
            'recovery_case_id' => $caseId,
            'deal_id' => (int)$deal['Deal']['id'],
            'plans_payment_id' => $planId,
            'client_id' => (int)$deal['Client']['id'],
            'recovery_officer_id' => $officerId,
            'action_type' => $actionType,
            'status' => $status,
            'contact_no' => $contactNo,
            'promise_date' => $promiseDate,
            'next_action_date' => $nextActionDate,
            'note' => $note,
            'created_by' => (int)$this->luserId
        )), array('validate' => false));

        if($planId > 0 && $promiseDate !== '' && $this->PlansPayment->hasField('recovery_promise_date'))
        {
            $db = $this->PlansPayment->getDataSource();
            $this->PlansPayment->updateAll(array('PlansPayment.recovery_promise_date' => $db->value($promiseDate, 'date')), array('PlansPayment.id' => $planId));
        }

        $this->Session->setFlash(__('Recovery follow-up saved.'), 'flash', array('alert' => 'success'));
        return $this->redirect($this->referer(array('action' => 'index'), true));
    }

    public function officers()
    {
        $query = (array)$this->request->query;
        $selectedAgencyId = $this->_selectedAgencyId($query);
        if($this->request->is('post') || $this->request->is('put'))
        {
            $data = (array)Hash::get($this->request->data, 'RecoveryOfficer', array());
            $id = !empty($data['id']) ? (int)$data['id'] : 0;
            $projectIds = $this->_allowedOfficerProjectIds(Hash::get($data, 'project_ids', array()), !empty($data['project_id']) ? (int)$data['project_id'] : $selectedAgencyId);
            if(empty($projectIds))
            {
                $this->Session->setFlash(__('Please select at least one allowed agency.'), 'flash', array('alert' => 'danger'));
                return $this->redirect(array('action' => 'officers'));
            }
            $projectId = (int)$projectIds[0];
            $userId = !empty($data['user_id']) ? (int)$data['user_id'] : 0;
            $name = trim((string)Hash::get($data, 'name', ''));
            $mobile = trim((string)Hash::get($data, 'mobile', ''));
            $email = trim((string)Hash::get($data, 'email', ''));
            $username = trim((string)Hash::get($data, 'username', ''));
            $password = trim((string)Hash::get($data, 'password', ''));
            $createLogin = (!empty($data['create_login']) || $userId > 0 || $username !== '' || $password !== '');
            if($userId > 0)
            {
                $user = $this->_userRow($userId);
                if(!empty($user['User']))
                {
                    if($name === '') $name = $user['User']['name'];
                    if($mobile === '') $mobile = !empty($user['User']['mobile']) ? $user['User']['mobile'] : $user['User']['phone'];
                    if($email === '') $email = $user['User']['email'];
                    if($username === '') $username = $user['User']['username'];
                }
            }
            if($name === '')
            {
                $this->Session->setFlash(__('Please enter recovery officer name.'), 'flash', array('alert' => 'danger'));
                return $this->redirect($this->referer(array('action' => 'officers'), true));
            }
            if($createLogin)
            {
                $roleId = (int)$this->_ensureRecoveryOfficerRole();
                if($roleId <= 0)
                {
                    $this->Session->setFlash(__('Unable to prepare Recovery Officer login role.'), 'flash', array('alert' => 'danger'));
                    return $this->redirect($this->referer(array('action' => 'officers'), true));
                }
                if($username === '')
                {
                    $this->Session->setFlash(__('Please enter login username for recovery officer.'), 'flash', array('alert' => 'danger'));
                    return $this->redirect($this->referer(array('action' => 'officers'), true));
                }
                if($userId <= 0 && $password === '')
                {
                    $this->Session->setFlash(__('Please enter password for new recovery officer login.'), 'flash', array('alert' => 'danger'));
                    return $this->redirect($this->referer(array('action' => 'officers'), true));
                }
                $userResult = $this->_saveRecoveryOfficerUser(array(
                    'user_id' => $userId,
                    'ugroup_id' => $roleId,
                    'project_id' => $projectId,
                    'project_ids' => $projectIds,
                    'name' => $name,
                    'username' => $username,
                    'password' => $password,
                    'mobile' => $mobile,
                    'email' => $email,
                    'status' => Hash::get($data, 'status', 'Active') === 'Inactive' ? 'Suspend' : 'Active'
                ));
                if(empty($userResult['success']))
                {
                    $this->Session->setFlash($userResult['message'], 'flash', array('alert' => 'danger'));
                    return $this->redirect($this->referer(array('action' => 'officers'), true));
                }
                $userId = (int)$userResult['user_id'];
            }
            $save = array(
                'project_id' => $projectId,
                'user_id' => $userId,
                'name' => $name,
                'mobile' => $mobile,
                'email' => $email,
                'status' => in_array(Hash::get($data, 'status', 'Active'), array('Active', 'Inactive'), true) ? Hash::get($data, 'status', 'Active') : 'Active',
                'created_by' => (int)$this->luserId
            );
            if($id > 0)
            {
                $save['id'] = $id;
            }
            $this->RecoveryOfficer->create();
            if($this->RecoveryOfficer->save(array('RecoveryOfficer' => $save), array('validate' => false)))
            {
                $this->Session->setFlash(__('Recovery officer saved.'), 'flash', array('alert' => 'success'));
            }
            else
            {
                $this->Session->setFlash(__('Unable to save recovery officer.'), 'flash', array('alert' => 'danger'));
            }
            return $this->redirect(array('action' => 'officers', '?' => array('project_id' => $projectId)));
        }

        $conditions = array();
            if($selectedAgencyId > 0)
            {
                $conditions[] = $this->_officerAgencyCondition($selectedAgencyId);
            }
            elseif(!$this->_isSuperAdminSession((array)$this->adminValue) && (int)$this->agencyId > 0)
            {
                $allowedProjectIds = $this->_sessionAllowedProjectIds();
                $conditions[] = $this->_officerAgencyCondition(!empty($allowedProjectIds) ? $allowedProjectIds : array((int)$this->agencyId));
            }
        $officers = (array)$this->RecoveryOfficer->find('all', array(
            'conditions' => $conditions,
            'order' => array('RecoveryOfficer.project_id' => 'asc', 'RecoveryOfficer.name' => 'asc'),
            'recursive' => -1
        ));
        $agencyOptions = $this->_agencyOptions();
        $userMeta = $this->_recoveryOfficerUserMeta($officers);
        $officerProjectMap = $this->_officerProjectIdsMap($officers);
        $this->set(compact('officers', 'agencyOptions', 'selectedAgencyId', 'userMeta', 'officerProjectMap'));
    }

    protected function _saveRecoveryOfficerUser($data)
    {
        $this->loadModel('Admin.User');
        $userId = !empty($data['user_id']) ? (int)$data['user_id'] : 0;
        $username = preg_replace('/[^A-Za-z0-9]/', '', (string)Hash::get($data, 'username', ''));
        if($username === '')
        {
            return array('success' => false, 'message' => __('Username can use letters and numbers only.'));
        }
        $duplicateConditions = array('User.username' => $username, 'User.deleted IS NULL');
        if($userId > 0)
        {
            $duplicateConditions['User.id <>'] = $userId;
        }
        $duplicate = (int)$this->User->find('count', array('conditions' => $duplicateConditions, 'recursive' => -1));
        if($duplicate > 0)
        {
            return array('success' => false, 'message' => __('This recovery officer username is already used.'));
        }
        if($userId > 0)
        {
            $existing = (array)$this->User->find('first', array(
                'conditions' => array('User.id' => $userId, 'User.deleted IS NULL'),
                'recursive' => -1
            ));
            if(empty($existing['User']['id']))
            {
                return array('success' => false, 'message' => __('Linked login user was not found.'));
            }
        }
        $save = array(
            'ugroup_id' => (int)Hash::get($data, 'ugroup_id', 0),
            'project_id' => (int)Hash::get($data, 'project_id', 0),
            'name' => trim((string)Hash::get($data, 'name', '')),
            'username' => $username,
            'phone' => trim((string)Hash::get($data, 'mobile', '')),
            'mobile' => trim((string)Hash::get($data, 'mobile', '')),
            'email' => trim((string)Hash::get($data, 'email', '')),
            'address' => 'Recovery Officer',
            'description' => 'Payment recovery officer login',
            'status' => Hash::get($data, 'status', 'Active') === 'Suspend' ? 'Suspend' : 'Active',
            'front_view' => 'Off'
        );
        $password = trim((string)Hash::get($data, 'password', ''));
        if($password !== '')
        {
            $save['password'] = $this->_hashPassword($password);
        }
        if($userId > 0)
        {
            $save['id'] = $userId;
        }
        else
        {
            $this->User->create();
        }
        if(!$this->User->save(array('User' => $save), array('validate' => false, 'callbacks' => false)))
        {
            return array('success' => false, 'message' => __('Unable to save recovery officer login user.'));
        }
        $savedUserId = $userId > 0 ? $userId : (int)$this->User->id;
        if(method_exists($this->User, 'syncProjectAssignments'))
        {
            $this->User->syncProjectAssignments($savedUserId, Hash::get($data, 'project_ids', array((int)$save['project_id'])), (int)$save['project_id']);
        }
        $roleId = (int)Hash::get($data, 'ugroup_id', 0);
        if($roleId > 0)
        {
            $this->_bumpMenuPermissionVersion($roleId);
        }
        return array('success' => true, 'user_id' => $savedUserId);
    }

    protected function _repairRecoveryOfficerLoginPasswords()
    {
        static $done = false;
        if($done)
        {
            return;
        }
        $done = true;
        try
        {
            $roleId = (int)$this->_ensureRecoveryOfficerRole();
            if($roleId <= 0)
            {
                return;
            }
            $this->_ensureRecoveryOfficerRoleRestrictions();
            $this->loadModel('Admin.User');
            $users = (array)$this->User->find('all', array(
                'fields' => array('User.id', 'User.password'),
                'conditions' => array('User.ugroup_id' => $roleId, 'User.deleted IS NULL'),
                'recursive' => -1
            ));
            foreach($users as $user)
            {
                $userId = !empty($user['User']['id']) ? (int)$user['User']['id'] : 0;
                $password = isset($user['User']['password']) ? trim((string)$user['User']['password']) : '';
                if($userId <= 0 || $password === '' || $this->_isModernPasswordHash($password))
                {
                    continue;
                }
                $this->User->id = $userId;
                $this->User->save(array('User' => array(
                    'id' => $userId,
                    'password' => $this->_hashPassword($password)
                )), array('validate' => false, 'callbacks' => false, 'fieldList' => array('password')));
            }
        }
        catch(Exception $e)
        {
            // Login repair must never block the recovery pages.
        }
    }

    protected function _recoveryOfficerUserMeta($officers)
    {
        $meta = array();
        $userIds = array();
        foreach((array)$officers as $row)
        {
            $userId = !empty($row['RecoveryOfficer']['user_id']) ? (int)$row['RecoveryOfficer']['user_id'] : 0;
            if($userId > 0)
            {
                $userIds[$userId] = $userId;
            }
        }
        if(empty($userIds))
        {
            return $meta;
        }
        $this->loadModel('Admin.User');
        $users = (array)$this->User->find('all', array(
            'fields' => array('User.id', 'User.username', 'User.status', 'User.project_id', 'Ugroup.name', 'Ugroup.type'),
            'conditions' => array('User.id' => array_values($userIds)),
            'recursive' => 0
        ));
        $agencyNamesByUser = method_exists($this->User, 'assignedProjectNameMap') ? (array)$this->User->assignedProjectNameMap(array_values($userIds)) : array();
        foreach($users as $user)
        {
            if(empty($user['User']['id']))
            {
                continue;
            }
            $roleName = !empty($user['Ugroup']['name']) ? $user['Ugroup']['name'] : (!empty($user['Ugroup']['type']) ? $user['Ugroup']['type'] : '');
            $meta[(int)$user['User']['id']] = array(
                'username' => $user['User']['username'],
                'status' => $user['User']['status'],
                'role' => $roleName,
                'project_ids' => method_exists($this->User, 'assignedProjectIds') ? (array)$this->User->assignedProjectIds((int)$user['User']['id'], true) : array((int)$user['User']['project_id']),
                'agency_names' => isset($agencyNamesByUser[(int)$user['User']['id']]) ? (string)$agencyNamesByUser[(int)$user['User']['id']] : ''
            );
        }
        return $meta;
    }

    public function deleteofficer($id = null)
    {
        if(!$this->request->is('post'))
        {
            throw new MethodNotAllowedException(__('Invalid request method.'));
        }
        $id = (int)$id;
        $row = (array)$this->RecoveryOfficer->findById($id);
        if(empty($row['RecoveryOfficer']['id']) || !$this->_projectAllowed((int)$row['RecoveryOfficer']['project_id']))
        {
            $this->Session->setFlash(__('Invalid recovery officer.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'officers'));
        }
        $this->RecoveryOfficer->delete($id, false);
        $this->Session->setFlash(__('Recovery officer removed.'), 'flash', array('alert' => 'success'));
        return $this->redirect($this->referer(array('action' => 'officers'), true));
    }

    public function settings()
    {
        $query = (array)$this->request->query;
        $selectedAgencyId = $this->_selectedAgencyId($query);
        if($selectedAgencyId <= 0 && (int)$this->agencyId > 0)
        {
            $selectedAgencyId = (int)$this->agencyId;
        }
        if($this->request->is('post') || $this->request->is('put'))
        {
            $data = (array)Hash::get($this->request->data, 'RecoverySetting', array());
            $projectId = !empty($data['project_id']) ? (int)$data['project_id'] : $selectedAgencyId;
            if(!$this->_projectAllowed($projectId) || $projectId <= 0)
            {
                $this->Session->setFlash(__('Please select a valid agency.'), 'flash', array('alert' => 'danger'));
                return $this->redirect(array('action' => 'settings'));
            }
            $existing = $this->_settingRow($projectId);
            $save = array(
                'project_id' => $projectId,
                'default_next_action_days' => max(0, min(30, (int)Hash::get($data, 'default_next_action_days', 1))),
                'escalation_days' => max(1, min(365, (int)Hash::get($data, 'escalation_days', 30))),
                'high_priority_amount' => max(0, (float)Hash::get($data, 'high_priority_amount', 0)),
                'auto_assign_enabled' => !empty($data['auto_assign_enabled']) ? 1 : 0
            );
            if(!empty($existing['RecoverySetting']['id']))
            {
                $save['id'] = (int)$existing['RecoverySetting']['id'];
            }
            $this->RecoverySetting->create();
            $this->RecoverySetting->save(array('RecoverySetting' => $save), array('validate' => false));
            $this->Session->setFlash(__('Recovery settings saved.'), 'flash', array('alert' => 'success'));
            return $this->redirect(array('action' => 'settings', '?' => array('project_id' => $projectId)));
        }
        $setting = $this->_settingRow($selectedAgencyId);
        if(empty($setting['RecoverySetting']))
        {
            $setting = array('RecoverySetting' => $this->_defaultSettings($selectedAgencyId));
        }
        $this->request->data = $setting;
        $agencyOptions = $this->_agencyOptions();
        $this->set(compact('agencyOptions', 'selectedAgencyId', 'setting'));
    }

    protected function _ensureRecoverySchema()
    {
        foreach(array('RecoveryOfficer', 'RecoveryCase', 'RecoveryFollowup', 'RecoverySetting') as $modelName)
        {
            if(isset($this->{$modelName}) && method_exists($this->{$modelName}, 'ensureSchema'))
            {
                try { $this->{$modelName}->ensureSchema(); } catch(Exception $e) {}
            }
        }
        $this->_ensurePaymentReminderPromiseColumn();
        try
        {
            $this->loadModel('Admin.User');
            if(method_exists($this->User, 'ensureUserProjectsTable'))
            {
                $this->User->ensureUserProjectsTable();
            }
        }
        catch(Exception $e)
        {
        }
    }

    protected function _ensurePaymentReminderPromiseColumn()
    {
        try
        {
            if(!$this->PlansPayment->hasField('recovery_promise_date'))
            {
                $this->PlansPayment->query("ALTER TABLE `plans_payments` ADD `recovery_promise_date` DATE NULL DEFAULT NULL");
                $this->PlansPayment->schema(true);
            }
        }
        catch(Exception $e)
        {
        }
    }

    protected function _installmentJoins()
    {
        return array(
            array('table' => 'deals', 'alias' => 'Deal', 'type' => 'LEFT', 'conditions' => array('PlansPayment.deal_id=Deal.id')),
            array('table' => 'deals_payments', 'alias' => 'DealsPayment', 'type' => 'LEFT', 'conditions' => array('PlansPayment.id=DealsPayment.plans_payment_id')),
            array('table' => 'clients', 'alias' => 'Client', 'type' => 'LEFT', 'conditions' => array('Deal.client_id=Client.id')),
            array('table' => 'properties', 'alias' => 'Property', 'type' => 'LEFT', 'conditions' => array('Deal.property_id=Property.id')),
            array('table' => 'projects', 'alias' => 'Project', 'type' => 'LEFT', 'conditions' => array('Property.project_id=Project.id'))
        );
    }

    protected function _installmentFields()
    {
        $fields = array(
            'PlansPayment.id', 'PlansPayment.deal_id', 'PlansPayment.name', 'PlansPayment.amount', 'PlansPayment.date',
            'Deal.id', 'Deal.invoice_no', 'Deal.total_amount', 'Deal.deal_status', 'Deal.user_id',
            'Client.id', 'Client.name', 'Client.phone', 'Client.mobile', 'Client.email',
            'Property.id', 'Property.name', 'Property.address', 'Property.project_id',
            'Project.id', 'Project.name'
        );
        if($this->PlansPayment->hasField('recovery_promise_date'))
        {
            $fields[] = 'PlansPayment.recovery_promise_date';
        }
        return $fields;
    }

    protected function _groupOverdueRowsByDeal($rows, $today)
    {
        $output = array();
        $overdueGroups = array();
        foreach((array)$rows as $row)
        {
            $dueDate = !empty($row['PlansPayment']['date']) ? $row['PlansPayment']['date'] : '';
            $dealId = !empty($row['Deal']['id']) ? (int)$row['Deal']['id'] : 0;
            if($dueDate !== '' && $dueDate < $today && $dealId > 0)
            {
                if(empty($overdueGroups[$dealId]))
                {
                    $row['_RecoveryAggregate'] = array('overdue_count' => 0, 'overdue_amount' => 0, 'plan_ids' => array(), 'oldest_due_date' => $dueDate);
                    $overdueGroups[$dealId] = $row;
                }
                $overdueGroups[$dealId]['_RecoveryAggregate']['overdue_count']++;
                $overdueGroups[$dealId]['_RecoveryAggregate']['overdue_amount'] += !empty($row['PlansPayment']['amount']) ? (float)$row['PlansPayment']['amount'] : 0;
                $overdueGroups[$dealId]['_RecoveryAggregate']['plan_ids'][] = !empty($row['PlansPayment']['id']) ? (int)$row['PlansPayment']['id'] : 0;
                if($dueDate < $overdueGroups[$dealId]['_RecoveryAggregate']['oldest_due_date'])
                {
                    $overdueGroups[$dealId]['_RecoveryAggregate']['oldest_due_date'] = $dueDate;
                }
                continue;
            }
            $row['_RecoveryAggregate'] = array('overdue_count' => 0, 'overdue_amount' => !empty($row['PlansPayment']['amount']) ? (float)$row['PlansPayment']['amount'] : 0, 'plan_ids' => array(!empty($row['PlansPayment']['id']) ? (int)$row['PlansPayment']['id'] : 0), 'oldest_due_date' => $dueDate);
            $output[] = $row;
        }
        foreach($overdueGroups as $dealId => $row)
        {
            $row['PlansPayment']['name'] = sprintf(__('Overdue Installments (%s)'), (int)$row['_RecoveryAggregate']['overdue_count']);
            $row['PlansPayment']['amount'] = (float)$row['_RecoveryAggregate']['overdue_amount'];
            $row['PlansPayment']['date'] = $row['_RecoveryAggregate']['oldest_due_date'];
            $output[] = $row;
        }
        usort($output, array($this, '_sortRowsByDueDate'));
        return $output;
    }

    protected function _sortRowsByDueDate($a, $b)
    {
        $dateA = !empty($a['PlansPayment']['date']) ? strtotime($a['PlansPayment']['date']) : PHP_INT_MAX;
        $dateB = !empty($b['PlansPayment']['date']) ? strtotime($b['PlansPayment']['date']) : PHP_INT_MAX;
        if($dateA === $dateB)
        {
            return (int)Hash::get($a, 'PlansPayment.id', 0) - (int)Hash::get($b, 'PlansPayment.id', 0);
        }
        return $dateA < $dateB ? -1 : 1;
    }

    protected function _dealStatusCondition()
    {
        return array('OR' => array(
            'Deal.deal_status IS NULL',
            'Deal.deal_status' => '',
            'Deal.deal_status NOT' => $this->_blockedDealStatuses
        ));
    }

    protected function _recoveryPermissionCondition($selectedAgencyId = 0)
    {
        $selectedAgencyId = (int)$selectedAgencyId;
        if($selectedAgencyId > 0)
        {
            return array('Property.project_id' => $selectedAgencyId);
        }
        if($this->_isSuperAdminSession((array)$this->adminValue) || strtoupper(trim((string)$this->userType)) === 'ADR')
        {
            return array();
        }
        if(strtoupper(trim((string)$this->userType)) === 'RCO')
        {
            $allowedProjectIds = $this->_sessionAllowedProjectIds();
            if(!empty($allowedProjectIds))
            {
                return array('Property.project_id' => $allowedProjectIds);
            }
        }
        if((int)$this->agencyId > 0)
        {
            return array('Property.project_id' => (int)$this->agencyId);
        }
        if((int)$this->luserId > 0)
        {
            return array('Property.user_id' => (int)$this->luserId);
        }
        return array();
    }

    protected function _dealIdsFromRows($rows)
    {
        $ids = array();
        foreach((array)$rows as $row)
        {
            $dealId = (int)Hash::get($row, 'Deal.id', 0);
            if($dealId > 0)
            {
                $ids[$dealId] = $dealId;
            }
        }
        return array_values($ids);
    }

    protected function _caseMap($dealIds)
    {
        if(empty($dealIds))
        {
            return array();
        }
        $rows = (array)$this->RecoveryCase->find('all', array(
            'conditions' => array('RecoveryCase.deal_id' => $dealIds),
            'recursive' => -1
        ));
        $map = array();
        foreach($rows as $row)
        {
            $map[(int)$row['RecoveryCase']['deal_id']] = $row['RecoveryCase'];
        }
        return $map;
    }

    protected function _lastFollowupMap($dealIds)
    {
        if(empty($dealIds))
        {
            return array();
        }
        $rows = (array)$this->RecoveryFollowup->find('all', array(
            'conditions' => array('RecoveryFollowup.deal_id' => $dealIds),
            'order' => array('RecoveryFollowup.id' => 'desc'),
            'recursive' => -1
        ));
        $map = array();
        foreach($rows as $row)
        {
            $dealId = (int)$row['RecoveryFollowup']['deal_id'];
            if($dealId > 0 && empty($map[$dealId]))
            {
                $map[$dealId] = $row['RecoveryFollowup'];
            }
        }
        return $map;
    }

    protected function _attachRecoveryMeta($rows, $caseMap, $lastFollowupMap, $officerFilter = 0, $caseStatusFilter = '')
    {
        $filtered = array();
        foreach((array)$rows as $row)
        {
            $dealId = (int)Hash::get($row, 'Deal.id', 0);
            $case = isset($caseMap[$dealId]) ? $caseMap[$dealId] : array('status' => 'New', 'priority' => 'Normal', 'recovery_officer_id' => 0, 'promise_date' => '', 'next_action_date' => '', 'last_note' => '');
            if($officerFilter > 0 && (int)$case['recovery_officer_id'] !== (int)$officerFilter)
            {
                continue;
            }
            if($caseStatusFilter !== '' && strcasecmp((string)$case['status'], $caseStatusFilter) !== 0)
            {
                continue;
            }
            $row['RecoveryCase'] = $case;
            $row['RecoveryFollowup'] = isset($lastFollowupMap[$dealId]) ? $lastFollowupMap[$dealId] : array();
            $filtered[] = $row;
        }
        return $filtered;
    }

    protected function _dashboardSummary($rows, $today)
    {
        $summary = array('total' => 0, 'unassigned' => 0, 'promise_today' => 0, 'missed_promise' => 0, 'high_priority' => 0);
        foreach((array)$rows as $row)
        {
            $summary['total']++;
            $case = isset($row['RecoveryCase']) ? $row['RecoveryCase'] : array();
            if(empty($case['recovery_officer_id']))
            {
                $summary['unassigned']++;
            }
            $promiseDate = !empty($case['promise_date']) ? $case['promise_date'] : '';
            if($promiseDate === $today)
            {
                $summary['promise_today']++;
            }
            elseif($promiseDate !== '' && $promiseDate < $today)
            {
                $summary['missed_promise']++;
            }
            if(!empty($case['priority']) && $case['priority'] === 'High')
            {
                $summary['high_priority']++;
            }
        }
        return $summary;
    }

    protected function _paginateRows($rows)
    {
        $limit = isset($this->request->query['limit']) ? (int)$this->request->query['limit'] : (!empty($this->pageLimit) ? (int)$this->pageLimit : 30);
        $limit = max(1, min($limit, max((int)$this->maxLimit, 500)));
        $page = isset($this->request->query['page']) ? (int)$this->request->query['page'] : 1;
        $page = max(1, $page);
        $count = count($rows);
        $pageCount = $count > 0 ? (int)ceil($count / $limit) : 1;
        if($page > $pageCount) $page = $pageCount;
        return array(
            'rows' => array_slice($rows, ($page - 1) * $limit, $limit),
            'page' => $page,
            'limit' => $limit,
            'count' => $count,
            'pageCount' => $pageCount,
            'start' => $count > 0 ? (($page - 1) * $limit) + 1 : 0,
            'end' => min($count, $page * $limit)
        );
    }

    protected function _dealMeta($dealId)
    {
        $dealId = (int)$dealId;
        if($dealId <= 0)
        {
            return array();
        }
        $this->loadModel('Admin.Deal');
        $conditions = array('Deal.id' => $dealId);
        $permission = $this->_recoveryPermissionCondition(0);
        if(!empty($permission))
        {
            $conditions[] = $permission;
        }
        return (array)$this->Deal->find('first', array(
            'joins' => array(
                array('table' => 'clients', 'alias' => 'Client', 'type' => 'LEFT', 'conditions' => array('Deal.client_id=Client.id')),
                array('table' => 'properties', 'alias' => 'Property', 'type' => 'LEFT', 'conditions' => array('Deal.property_id=Property.id')),
                array('table' => 'projects', 'alias' => 'Project', 'type' => 'LEFT', 'conditions' => array('Property.project_id=Project.id'))
            ),
            'fields' => array('Deal.id', 'Deal.invoice_no', 'Deal.client_id', 'Deal.property_id', 'Client.id', 'Client.name', 'Client.phone', 'Client.mobile', 'Property.id', 'Property.name', 'Property.project_id', 'Project.id', 'Project.name'),
            'conditions' => $conditions,
            'recursive' => -1
        ));
    }

    protected function _upsertRecoveryCase($deal, $data)
    {
        $dealId = (int)$deal['Deal']['id'];
        $existing = (array)$this->RecoveryCase->find('first', array('conditions' => array('RecoveryCase.deal_id' => $dealId), 'recursive' => -1));
        $save = array_merge(array(
            'project_id' => (int)$deal['Property']['project_id'],
            'deal_id' => $dealId,
            'client_id' => (int)$deal['Client']['id'],
            'status' => 'New',
            'priority' => 'Normal',
            'modified_by' => (int)$this->luserId
        ), (array)$data);
        if(!empty($existing['RecoveryCase']['id']))
        {
            $save['id'] = (int)$existing['RecoveryCase']['id'];
        }
        else
        {
            $save['created_by'] = (int)$this->luserId;
        }
        if(!empty($save['recovery_officer_id']))
        {
            $officer = (array)$this->RecoveryOfficer->findById((int)$save['recovery_officer_id']);
            $save['assigned_user_id'] = !empty($officer['RecoveryOfficer']['user_id']) ? (int)$officer['RecoveryOfficer']['user_id'] : 0;
        }
        $this->RecoveryCase->create();
        $this->RecoveryCase->save(array('RecoveryCase' => $save), array('validate' => false));
        return !empty($save['id']) ? (int)$save['id'] : (int)$this->RecoveryCase->id;
    }

    protected function _selectedAgencyId($query = array())
    {
        $projectId = isset($query['project_id']) ? (int)$query['project_id'] : 0;
        if($this->_isSuperAdminSession((array)$this->adminValue) || strtoupper(trim((string)$this->userType)) === 'ADR')
        {
            return $projectId > 0 ? $projectId : 0;
        }
        if($projectId > 0 && $this->_projectAllowed($projectId))
        {
            return $projectId;
        }
        if(strtoupper(trim((string)$this->userType)) === 'RCO')
        {
            return 0;
        }
        return (int)$this->agencyId > 0 ? (int)$this->agencyId : 0;
    }

    protected function _agencyOptions()
    {
        $this->loadModel('Admin.Agency');
        $conditions = array();
        if(!$this->_isSuperAdminSession((array)$this->adminValue) && strtoupper(trim((string)$this->userType)) !== 'ADR')
        {
            $allowedProjectIds = $this->_sessionAllowedProjectIds();
            $conditions['Agency.id'] = !empty($allowedProjectIds) ? $allowedProjectIds : array((int)$this->agencyId);
        }
        return (array)$this->Agency->find('list', array('fields' => array('Agency.id', 'Agency.name'), 'conditions' => $conditions, 'order' => array('Agency.name' => 'asc'), 'recursive' => -1));
    }

    protected function _officerOptions($projectId = 0, $includeInactive = false)
    {
        $conditions = array();
        if((int)$projectId > 0)
        {
            $conditions[] = $this->_officerAgencyCondition((int)$projectId);
        }
        elseif(!$this->_isSuperAdminSession((array)$this->adminValue) && (int)$this->agencyId > 0)
        {
            $allowedProjectIds = $this->_sessionAllowedProjectIds();
            $conditions[] = $this->_officerAgencyCondition(!empty($allowedProjectIds) ? $allowedProjectIds : array((int)$this->agencyId));
        }
        if(!$includeInactive)
        {
            $conditions['RecoveryOfficer.status'] = 'Active';
        }
        return (array)$this->RecoveryOfficer->find('list', array('fields' => array('RecoveryOfficer.id', 'RecoveryOfficer.name'), 'conditions' => $conditions, 'order' => array('RecoveryOfficer.name' => 'asc'), 'recursive' => -1));
    }

    protected function _userOptions($projectId = 0)
    {
        $this->loadModel('Admin.User');
        $conditions = array('User.status' => 'Active');
        if((int)$projectId > 0)
        {
            $conditions['User.project_id'] = (int)$projectId;
        }
        elseif(!$this->_isSuperAdminSession((array)$this->adminValue) && (int)$this->agencyId > 0)
        {
            $conditions['User.project_id'] = (int)$this->agencyId;
        }
        return (array)$this->User->find('list', array('fields' => array('User.id', 'User.name'), 'conditions' => $conditions, 'order' => array('User.name' => 'asc'), 'recursive' => -1));
    }

    protected function _userRow($userId)
    {
        $this->loadModel('Admin.User');
        return (array)$this->User->find('first', array('conditions' => array('User.id' => (int)$userId), 'recursive' => -1));
    }

    protected function _sessionAllowedProjectIds()
    {
        if($this->_isSuperAdminSession((array)$this->adminValue) || strtoupper(trim((string)$this->userType)) === 'ADR')
        {
            return array();
        }
        $ids = array();
        if((int)$this->agencyId > 0)
        {
            $ids[(int)$this->agencyId] = (int)$this->agencyId;
        }
        try
        {
            $this->loadModel('Admin.User');
            if((int)$this->luserId > 0 && method_exists($this->User, 'assignedProjectIds'))
            {
                foreach((array)$this->User->assignedProjectIds((int)$this->luserId, true) as $projectId)
                {
                    $projectId = (int)$projectId;
                    if($projectId > 0)
                    {
                        $ids[$projectId] = $projectId;
                    }
                }
            }
        }
        catch(Exception $e)
        {
        }
        return array_values($ids);
    }

    protected function _allowedOfficerProjectIds($rawProjectIds, $fallbackProjectId = 0)
    {
        try
        {
            $this->loadModel('Admin.User');
            $projectIds = method_exists($this->User, 'normalizeProjectIdList') ? (array)$this->User->normalizeProjectIdList($rawProjectIds, $fallbackProjectId) : array((int)$fallbackProjectId);
        }
        catch(Exception $e)
        {
            $projectIds = array((int)$fallbackProjectId);
        }
        $allowed = array();
        foreach((array)$projectIds as $projectId)
        {
            $projectId = (int)$projectId;
            if($projectId > 0 && $this->_projectAllowed($projectId))
            {
                $allowed[$projectId] = $projectId;
            }
        }
        return array_values($allowed);
    }

    protected function _officerAgencyCondition($projectIds)
    {
        $ids = array();
        foreach((array)$projectIds as $projectId)
        {
            $projectId = (int)$projectId;
            if($projectId > 0)
            {
                $ids[$projectId] = $projectId;
            }
        }
        $ids = array_values($ids);
        if(empty($ids))
        {
            return array();
        }
        if(count($ids) === 1)
        {
            $id = (int)$ids[0];
            return array('OR' => array(
                'RecoveryOfficer.project_id' => $id,
                "EXISTS (SELECT 1 FROM `user_projects` `RecoveryOfficerProject` WHERE `RecoveryOfficerProject`.`user_id`=`RecoveryOfficer`.`user_id` AND `RecoveryOfficerProject`.`project_id`=".$id.")"
            ));
        }
        $idList = implode(',', array_map('intval', $ids));
        return array('OR' => array(
            'RecoveryOfficer.project_id' => $ids,
            "EXISTS (SELECT 1 FROM `user_projects` `RecoveryOfficerProject` WHERE `RecoveryOfficerProject`.`user_id`=`RecoveryOfficer`.`user_id` AND `RecoveryOfficerProject`.`project_id` IN (".$idList."))"
        ));
    }

    protected function _officerProjectIds($officer)
    {
        $officer = isset($officer['RecoveryOfficer']) ? (array)$officer['RecoveryOfficer'] : (array)$officer;
        $ids = array();
        if(!empty($officer['project_id']))
        {
            $ids[(int)$officer['project_id']] = (int)$officer['project_id'];
        }
        $userId = !empty($officer['user_id']) ? (int)$officer['user_id'] : 0;
        if($userId > 0)
        {
            try
            {
                $this->loadModel('Admin.User');
                if(method_exists($this->User, 'assignedProjectIds'))
                {
                    foreach((array)$this->User->assignedProjectIds($userId, true) as $projectId)
                    {
                        $projectId = (int)$projectId;
                        if($projectId > 0)
                        {
                            $ids[$projectId] = $projectId;
                        }
                    }
                }
            }
            catch(Exception $e)
            {
            }
        }
        return array_values($ids);
    }

    protected function _officerProjectIdsMap($officers)
    {
        $map = array();
        foreach((array)$officers as $row)
        {
            $officerId = !empty($row['RecoveryOfficer']['id']) ? (int)$row['RecoveryOfficer']['id'] : 0;
            if($officerId > 0)
            {
                $map[$officerId] = $this->_officerProjectIds($row);
            }
        }
        return $map;
    }

    protected function _projectAllowed($projectId)
    {
        $projectId = (int)$projectId;
        if($projectId <= 0)
        {
            return false;
        }
        if($this->_isSuperAdminSession((array)$this->adminValue) || strtoupper(trim((string)$this->userType)) === 'ADR')
        {
            return true;
        }
        if((int)$this->agencyId > 0 && (int)$this->agencyId === $projectId)
        {
            return true;
        }
        try
        {
            $this->loadModel('Admin.User');
            if((int)$this->luserId > 0 && method_exists($this->User, 'userHasProjectAccess'))
            {
                return (bool)$this->User->userHasProjectAccess((int)$this->luserId, $projectId);
            }
        }
        catch(Exception $e)
        {
        }
        return false;
    }

    protected function _officerAllowedForDeal($officerId, $projectId)
    {
        $officerId = (int)$officerId;
        if($officerId <= 0)
        {
            return true;
        }
        $officer = (array)$this->RecoveryOfficer->findById($officerId);
        if(empty($officer['RecoveryOfficer']['id']) || $officer['RecoveryOfficer']['status'] !== 'Active')
        {
            return false;
        }
        return in_array((int)$projectId, $this->_officerProjectIds($officer), true);
    }

    protected function _settingRow($projectId)
    {
        $projectId = (int)$projectId;
        if($projectId <= 0)
        {
            return array();
        }
        return (array)$this->RecoverySetting->find('first', array('conditions' => array('RecoverySetting.project_id' => $projectId), 'recursive' => -1));
    }

    protected function _defaultSettings($projectId)
    {
        return array('project_id' => (int)$projectId, 'default_next_action_days' => 1, 'escalation_days' => 30, 'high_priority_amount' => 0, 'auto_assign_enabled' => 0);
    }

    protected function _normalizeDate($value)
    {
        $value = trim((string)$value);
        if($value === '')
        {
            return '';
        }
        if(preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $value, $m))
        {
            return checkdate((int)$m[2], (int)$m[3], (int)$m[1]) ? sprintf('%04d-%02d-%02d', (int)$m[1], (int)$m[2], (int)$m[3]) : false;
        }
        if(preg_match('/^(\d{1,2})[\/\-.](\d{1,2})[\/\-.](\d{2}|\d{4})$/', $value, $m))
        {
            $year = (int)$m[3];
            if($year < 100) $year += 2000;
            return checkdate((int)$m[2], (int)$m[1], $year) ? sprintf('%04d-%02d-%02d', $year, (int)$m[2], (int)$m[1]) : false;
        }
        $ts = strtotime(str_replace('/', '-', $value));
        return $ts === false ? false : date('Y-m-d', $ts);
    }

    protected function _caseStatuses()
    {
        return array('New' => __('New'), 'Contacted' => __('Contacted'), 'Promise' => __('Promise'), 'No Answer' => __('No Answer'), 'Dispute' => __('Dispute'), 'Refused' => __('Refused'), 'Legal Notice' => __('Legal Notice'), 'Paid' => __('Paid'), 'Closed' => __('Closed'));
    }

    protected function _actionTypes()
    {
        return array('Call' => __('Call'), 'SMS' => __('SMS'), 'WhatsApp' => __('WhatsApp'), 'Email' => __('Email'), 'Office Visit' => __('Office Visit'), 'Field Visit' => __('Field Visit'));
    }

    protected function _priorityOptions()
    {
        return array('Normal' => __('Normal'), 'High' => __('High'), 'Urgent' => __('Urgent'));
    }

    protected function _cleanCaseStatus($status)
    {
        $statuses = array_keys($this->_caseStatuses());
        return in_array($status, $statuses, true) ? $status : 'New';
    }

    protected function _cleanActionType($actionType)
    {
        $types = array_keys($this->_actionTypes());
        return in_array($actionType, $types, true) ? $actionType : 'Call';
    }

    protected function _cleanPriority($priority)
    {
        $options = array_keys($this->_priorityOptions());
        return in_array($priority, $options, true) ? $priority : 'Normal';
    }
}
?>
