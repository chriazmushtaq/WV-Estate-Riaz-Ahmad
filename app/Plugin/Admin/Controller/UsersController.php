<?php
App::uses('AdminAppController', 'Admin.Controller');
App::uses('AppController', 'Controller');
App::uses('SimplePasswordHasher', 'Controller/Component/Auth');
App::uses('CakeEmail', 'Network/Email');
App::uses('CakeSession', 'Model/Datasource');
App::uses('Security', 'Utility');
class UsersController extends AdminAppController {
    public $uses = array('Admin.User');
    public $helpers = array('Html', 'Form','Session','Paginator','Js'=> array('Jquery'));
    public $components = array('Session','Paginator','search-master.Prg');
    public $presetVars = true;
    var $paginate = array('page'=>1,'order'=>array('User.name'=>'asc'));
    protected function _ensureFrontViewColumn()
    {
        if($this->User->hasField('front_view'))
        {
            return true;
        }
        try
        {
            $this->User->query("ALTER TABLE `users` ADD `front_view` VARCHAR(3) NOT NULL DEFAULT 'On' AFTER `status`");
            $this->User->schema(true);
        }
        catch (Exception $e)
        {
            $this->User->schema(true);
        }
        return $this->User->hasField('front_view');
    }
    protected function _ensureSortIdColumn()
    {
        $this->User->schema(true);
        if($this->User->hasField('sort_id'))
        {
            return true;
        }
        $afterColumn = $this->_ensureFrontViewColumn() ? 'front_view' : 'status';
        try
        {
            $this->User->query("ALTER TABLE `users` ADD `sort_id` INT(11) NOT NULL DEFAULT 0 AFTER `".$afterColumn."`");
            $this->User->schema(true);
        }
        catch (Exception $e)
        {
            $this->User->schema(true);
        }
        return $this->User->hasField('sort_id');
    }
    protected function _ensureLoginStatusColumn()
    {
        $this->User->schema(true);
        if($this->User->hasField('login_status'))
        {
            return true;
        }
        try
        {
            $this->User->query("ALTER TABLE `users` ADD `login_status` VARCHAR(20) NOT NULL DEFAULT 'Active' AFTER `status`");
            $this->User->schema(true);
        }
        catch (Exception $e)
        {
            $this->User->schema(true);
        }
        return $this->User->hasField('login_status');
    }
    protected function _normalizeLoginStatusValue($value)
    {
        $value = strtolower(trim((string)$value));
        if(in_array($value, array('blocked', 'block', 'suspend', 'suspended', 'inactive', '0', 'false', 'off'), true))
        {
            return 'Blocked';
        }
        return 'Active';
    }
    protected function _normalizeFrontViewValue($value)
    {
        $value = strtolower(trim((string)$value));
        if(in_array($value,array('off','0','false','no'),true))
        {
            return 'Off';
        }
        return 'On';
    }
    protected function _normalizeSortIdValue($value)
    {
        $value = trim((string)$value);
        if($value === '')
        {
            return 0;
        }
        return max(0,(int)$value);
    }
    protected function _systemUserRoleTypeCondition($alias = 'Ugroup')
    {
        $alias = preg_replace('/[^A-Za-z0-9_]/', '', (string)$alias);
        if($alias === '')
        {
            $alias = 'Ugroup';
        }
        return "(COALESCE(".$alias.".type,'') NOT IN ('AGY','AGT'))";
    }
    protected function _canManageAllUsers()
    {
        return ($this->_isSuperAdminSession((array)$this->adminValue) || $this->_isSuperAdminSession((array)$this->Session->read('User')));
    }
    protected function _canAccessManagedUser($userRow)
    {
        if($this->_canManageAllUsers())
        {
            return true;
        }
        $userId = !empty($userRow['User']['id']) ? (int)$userRow['User']['id'] : 0;
        $userGroupId = !empty($userRow['User']['ugroup_id']) ? (int)$userRow['User']['ugroup_id'] : 0;
        if($userId === 1 || $userGroupId === 1)
        {
            return false;
        }
        return ($userId > 0 && (int)$this->agencyId > 0 && $this->User->userHasProjectAccess($userId, (int)$this->agencyId));
    }
    protected function _normalizeManagedProjectSelection($userData, $fallbackUgroupId = 0)
    {
        $userData = (array)$userData;
        $ugroupId = !empty($userData['ugroup_id']) ? (int)$userData['ugroup_id'] : (int)$fallbackUgroupId;
        $primaryProjectId = !empty($userData['project_id']) ? (int)$userData['project_id'] : 0;
        $projectIds = $this->User->normalizeProjectIdList(
            isset($userData['project_ids']) ? $userData['project_ids'] : array(),
            $primaryProjectId
        );
        if($primaryProjectId <= 0 && !empty($projectIds))
        {
            $primaryProjectId = (int)reset($projectIds);
        }
        if($primaryProjectId > 0)
        {
            $projectIds[$primaryProjectId] = $primaryProjectId;
        }
        $projectIds = array_values(array_unique(array_map('intval', (array)$projectIds)));
        if($ugroupId === 1)
        {
            $primaryProjectId = 0;
            $projectIds = array();
        }
        elseif($this->_userRoleCategoryForGroup($ugroupId) === 'agency')
        {
            $projectIds = ($primaryProjectId > 0) ? array($primaryProjectId) : array();
        }
        return array(
            'ugroup_id' => $ugroupId,
            'project_id' => $primaryProjectId,
            'project_ids' => $projectIds
        );
    }
    protected function _restrictManagedProjectSelectionForCurrentAdmin($selection, $existingUserRow = array())
    {
        $selection = (array)$selection;
        if($this->_canManageAllUsers())
        {
            return $selection;
        }
        $ugroupId = !empty($selection['ugroup_id']) ? (int)$selection['ugroup_id'] : 0;
        if($ugroupId > 0 && $ugroupId !== 1 && $this->_userRoleCategoryForGroup($ugroupId) !== 'agency')
        {
            if(!empty($existingUserRow['User']['id']))
            {
                $existingUserId = (int)$existingUserRow['User']['id'];
                $existingPrimaryProjectId = !empty($existingUserRow['User']['project_id']) ? (int)$existingUserRow['User']['project_id'] : 0;
                $existingProjectIds = method_exists($this->User,'assignedProjectIds') ? (array)$this->User->assignedProjectIds($existingUserId, true) : array();
                if(empty($existingProjectIds) && $existingPrimaryProjectId > 0)
                {
                    $existingProjectIds = array($existingPrimaryProjectId);
                }
                $selection['project_id'] = $existingPrimaryProjectId;
                $selection['project_ids'] = $existingProjectIds;
            }
            else
            {
                $agencyId = (int)$this->agencyId;
                $selection['project_id'] = $agencyId;
                $selection['project_ids'] = ($agencyId > 0) ? array($agencyId) : array();
            }
        }
        return $selection;
    }
    protected function _managedUserRedirectTarget($groupTypeMap, $savedRows)
    {
        $types = array();
        foreach((array)$savedRows as $row)
        {
            $groupId = !empty($row['ugroup_id']) ? (int)$row['ugroup_id'] : 0;
            if($groupId <= 0)
            {
                continue;
            }
            $types[] = !empty($groupTypeMap[$groupId]) ? strtoupper(trim((string)$groupTypeMap[$groupId])) : '';
        }
        $types = array_values(array_unique($types));
        if(count($types) === 1 && $types[0] === 'AGY')
        {
            return array(
                'message' => __('Agency has been updated.'),
                'redirect' => array('controller' => 'Agencies', 'action' => 'index')
            );
        }
        if(count($types) === 1 && $types[0] === 'AGT')
        {
            return array(
                'message' => __('Agent has been updated.'),
                'redirect' => array('controller' => 'Agents', 'action' => 'index')
            );
        }
        return array(
            'message' => __('Users has been updated.'),
            'redirect' => array('controller' => 'Users', 'action' => 'index')
        );
    }
    protected function _walletBalanceMapForUsers($userIds)
    {
        $userIds = array_values(array_unique(array_filter(array_map('intval', (array)$userIds))));
        if(empty($userIds))
        {
            return array();
        }
        $idList = implode(',', $userIds);
        $balanceMap = array();
        try
        {
            $walletRows = (array)$this->User->query(
                "SELECT aw.user_id, aw.balance
                 FROM awallets aw
                 INNER JOIN (
                    SELECT user_id, MAX(id) AS max_id
                    FROM awallets
                    WHERE user_id IN (".$idList.")
                    GROUP BY user_id
                 ) latest_aw ON latest_aw.max_id = aw.id"
            );
            foreach($walletRows as $walletRow)
            {
                $userId = !empty($walletRow['aw']['user_id']) ? (int)$walletRow['aw']['user_id'] : (!empty($walletRow[0]['user_id']) ? (int)$walletRow[0]['user_id'] : 0);
                if($userId <= 0)
                {
                    continue;
                }
                $balance = isset($walletRow['aw']['balance']) ? (float)$walletRow['aw']['balance'] : (isset($walletRow[0]['balance']) ? (float)$walletRow[0]['balance'] : 0.0);
                $balanceMap[$userId] = $balance;
            }
        }
        catch(Exception $e)
        {
        }
        try
        {
            $historyRows = (array)$this->User->query(
                "SELECT user_id,
                        SUM(CASE WHEN UPPER(TRIM(COALESCE(type,'')))='CR' THEN amount ELSE (-1 * amount) END) AS hist_balance
                 FROM awallet_histories
                 WHERE user_id IN (".$idList.")
                 GROUP BY user_id"
            );
            foreach($historyRows as $historyRow)
            {
                $userId = !empty($historyRow['awallet_histories']['user_id']) ? (int)$historyRow['awallet_histories']['user_id'] : (!empty($historyRow[0]['user_id']) ? (int)$historyRow[0]['user_id'] : 0);
                if($userId <= 0 || isset($balanceMap[$userId]))
                {
                    continue;
                }
                $balance = isset($historyRow[0]['hist_balance']) ? (float)$historyRow[0]['hist_balance'] : (isset($historyRow['awallet_histories']['hist_balance']) ? (float)$historyRow['awallet_histories']['hist_balance'] : 0.0);
                $balanceMap[$userId] = $balance;
            }
        }
        catch(Exception $e)
        {
        }
        return $balanceMap;
    }
    public function index()
    {
        $this->Prg->commonProcess();
        $this->Paginator->settings = $this->paginate;
        $conditions = array(
            'User.deleted IS NULL',
            $this->User->parseCriteria($this->Prg->parsedParams())
        );
        if(!$this->_canManageAllUsers())
        {
            $conditions[] = $this->_systemUserRoleTypeCondition('Ugroup');
            if((int)$this->agencyId > 0)
            {
                $conditions[] = $this->User->projectMembershipCondition((int)$this->agencyId, 'User');
            }
        }
        $this->Paginator->settings['conditions'] = $conditions;
	$this->Paginator->settings['limit']=$this->pageLimit;
        $this->Paginator->settings['maxLimit']=$this->maxLimit;
        $users = $this->Paginator->paginate();
        $userIds = array();
        foreach((array)$users as $userRow)
        {
            if(!empty($userRow['User']['id']))
            {
                $userIds[] = (int)$userRow['User']['id'];
            }
        }
        $this->set('userAgencyNames', $this->User->assignedProjectNameMap($userIds));
        $this->set('userWalletBalances', $this->_walletBalanceMapForUsers($userIds));
        $this->set('User', $users);  
	if ($this->request->is('ajax'))
        {
            $this->render('index','ajax'); // View, Layout
        }
    }
    public function add($type=null)
    {
	$hasFrontViewColumn = $this->_ensureFrontViewColumn();
	$hasSortIdColumn = $this->_ensureSortIdColumn();
	$this->set('hasFrontViewColumn',$hasFrontViewColumn);
	$this->set('hasSortIdColumn',$hasSortIdColumn);
	$this->set('frontViewOptions',array('On'=>__('On'),'Off'=>__('Off')));
	if(!$type && $this->userType!='ADR'){
	    $this->Session->setFlash(__('Invalid Post'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'index'));
	}
	elseif($type!='AY' && $type!='AT' && $this->userType!='ADR'){
	    $this->Session->setFlash(__('Invalid Post'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'index'));
	}
        elseif($type=='AY' && $this->userType!='ADR'){
	    $this->Session->setFlash(__('Invalid Post'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'index'));
	}
        elseif($type=='AY'){
	    $this->set('type',$type);
	}
        elseif($type=='AT'){
	    $this->set('type',$type);
	    $this->set('pId',$this->agencyId);
        }
	$this->set('type',$type);
        $this->loadModel('Ugroup');
        $this->_ensureUserRoleCategorySchema();
        $this->set('ugroup',$this->_userRoleOptionsByCategory('system_user'));
        $this->set('agencyRoleOptions',$this->_userRoleOptionsByCategory('agency'));
        $this->set('agentRoleOptions',$this->_userRoleOptionsByCategory('agent'));
        $this->loadModel('Location');
        $this->set('location',$this->Location->find('list'));
        $this->loadModel('Project');
	$cond=array();
	if($this->userType!='ADR')
	{
	    $cond=array('Project.id'=>$this->agencyId);
	}
        $this->set('project',$this->Project->find('list',array('conditions'=>$cond)));
        if($this->request->is('post'))
        {
            $this->User->create();
            try
            {
                $password=$this->request->data['User']['password'];
                $saveData = $this->request->data;
		if($hasFrontViewColumn)
		{
		    $frontView = isset($saveData['User']['front_view']) ? $saveData['User']['front_view'] : 'On';
		    $saveData['User']['front_view']=$this->_normalizeFrontViewValue($frontView);
		}
		else
		{
		    unset($saveData['User']['front_view']);
		}
		if($hasSortIdColumn && isset($saveData['User']['sort_id']))
		{
		    $saveData['User']['sort_id']=$this->_normalizeSortIdValue($saveData['User']['sort_id']);
		}
		elseif(!$hasSortIdColumn)
		{
		    unset($saveData['User']['sort_id']);
		}
		$selectedGroupId = !empty($saveData['User']['ugroup_id']) ? (int)$saveData['User']['ugroup_id'] : 0;
                $selectedRoleCategory = $this->_userRoleCategoryForGroup($selectedGroupId);
                $requiredRoleCategory = $type === 'AY' ? 'agency' : ($type === 'AT' ? 'agent' : 'system_user');
                if($selectedGroupId <= 0 || $selectedRoleCategory !== $requiredRoleCategory)
                {
                    $this->Session->setFlash(__('Please select a valid user level for this user category.'),'flash',array('alert'=>'danger'));
                    $this->request->data = $saveData;
                    return;
                }
		if($selectedRoleCategory === 'agency'){
		    $projectRecord=array('name'=>$saveData['User']['name'],'email'=>$saveData['User']['email'],'phone'=>$saveData['User']['phone'],'mobile'=>$saveData['User']['mobile']);
		    $this->Project->save($projectRecord);
		    $lastId=$this->Project->id;
		    $this->_ardProvisionAgencyAccountingLedgers((int)$lastId);
		    $saveData['User']['project_id']=$lastId;
		}
		if($this->userType=='AGY' && $type === 'AT'){
		    $saveData['User']['project_id']=$this->agencyId;
		}
		$selection = $this->_normalizeManagedProjectSelection(isset($saveData['User']) ? $saveData['User'] : array(), isset($saveData['User']['ugroup_id']) ? (int)$saveData['User']['ugroup_id'] : 0);
		$selection = $this->_restrictManagedProjectSelectionForCurrentAdmin($selection);
		$currentUserLevel = (int)$selection['ugroup_id'];
		$currentAgencyId = (int)$selection['project_id'];
		$saveData['User']['ugroup_id']=$currentUserLevel;
		$saveData['User']['project_id']=$currentAgencyId;
		unset($saveData['User']['project_ids']);
		if($currentUserLevel>0 && $currentUserLevel!==1 && $selectedRoleCategory!=='agency' && $currentAgencyId<=0)
		{
		    $this->Session->setFlash(__('Please select agency for this user.'),'flash',array('alert'=>'danger'));
                    $this->request->data = $saveData;
                    $this->request->data['User']['project_id']=$currentAgencyId;
                    $this->request->data['User']['project_ids']=$selection['project_ids'];
		    return;
		}
		if ($this->User->save($saveData))
		{		    
                    $savedUserId = (int)$this->User->id;
                    $this->User->syncProjectAssignments($savedUserId, $selection['project_ids'], $currentAgencyId);
		    $email=$saveData['User']['email'];$name=$saveData['User']['name'];$mobileNo=$saveData['User']['mobile'];
		    $userName=$saveData['User']['username'];
		    $siteName=$this->siteName;$siteEmailContact=$this->siteEmailContact;$url=$this->siteDomain.'/admin';
		    if($email)
		    {
			if($this->emailNotification)
			{
			    /* Send Email */
			    $this->loadModel('Emailtemplate');
			    $emailSettingArr=$this->Emailtemplate->findByType('ULC');
			    if($emailSettingArr['Emailtemplate']['status']=="Published")
			    {
				$message=$this->_renderTemplateString((string)$emailSettingArr['Emailtemplate']['description'], get_defined_vars());
				$Email = new CakeEmail();
				$Email = new CakeEmail();
				$Email->transport($this->emailSettype);
				if($this->emailSettype=="Smtp")
				$Email->config(array('host' => $this->emailHost,'port' =>  $this->emailPort,'username' => $this->emailUsername,'password' => $this->emailPassword));
				$Email->from(array($this->siteEmail =>$this->siteName));
				$Email->to($email);
				$Email->template('default');
				$Email->emailFormat('html');
				$Email->subject($emailSettingArr['Emailtemplate']['name']);
				$Email->send($message);
				/* End Email */
			    }
			}
		    }
		    if($mobileNo)
		    {
			if($this->smsNotification)
			{
			    /* Send Sms */
			    $this->loadModel('Smstemplate');
			    $smsTemplateArr=$this->Smstemplate->findByType('ULC');
			    if($smsTemplateArr['Smstemplate']['status']=="Published")
			    {
				$url="$this->siteDomain";
				$message=$this->_renderTemplateString((string)$smsTemplateArr['Smstemplate']['description'], get_defined_vars());
				$this->CustomFunction->sendSms($mobileNo,$message,$this->smsSettingArr);
			    }
			    /* End Sms */
			}
		    }
		    if($type=='AY'){
			$this->Session->setFlash(__('Agency has been saved.'),'flash',array('alert'=>'success'));
			return $this->redirect(array('controller'=>'Agencies','action' => 'Index'));
		    }
		    elseif($type=='AT'){
			$this->Session->setFlash(__('Agent has been saved.'),'flash',array('alert'=>'success'));
			return $this->redirect(array('controller'=>'Agents','action' => 'Index'));
		    }
		    else{
			$this->Session->setFlash(__('User has been saved.'),'flash',array('alert'=>'success'));
			return $this->redirect(array('action' => 'add'));
		    }
		}		
            }
            catch (Exception $e)
            {
                $this->Session->setFlash(__('Invalid Post'),'flash',array('alert'=>'danger'));
                return $this->redirect(array('action' => 'index'));
            }
        }
	if(!$this->request->is('post') && $hasFrontViewColumn)
	{
	    $this->request->data['User']['front_view']='On';
	}
	if(!$this->request->is('post') && $hasSortIdColumn && $type==='AT')
	{
	    $this->request->data['User']['sort_id']=0;
	}
    }
    public function edit($id = null)
    {
	$hasFrontViewColumn = $this->_ensureFrontViewColumn();
	$hasSortIdColumn = $this->_ensureSortIdColumn();
	$this->set('hasFrontViewColumn',$hasFrontViewColumn);
	$this->set('hasSortIdColumn',$hasSortIdColumn);
	$this->set('frontViewOptions',array('On'=>__('On'),'Off'=>__('Off')));
	$this->set('luserId',$this->adminValue['User']['project_id']);
        $this->loadModel('Ugroup');
        $this->set('ugroup',$this->Ugroup->find('list'));
        $this->loadModel('Project');
        $projectConditions=array();
        if($this->userType!='ADR')
        {
            $projectConditions=array('Project.id'=>$this->agencyId);
        }
        $this->set('project',$this->Project->find('list',array('conditions'=>$projectConditions)));
        $this->loadModel('Location');
        $this->set('location',$this->Location->find('list'));
        $ugroupTypeMap = $this->Ugroup->find('list',array('fields'=>array('Ugroup.id','Ugroup.type')));
        if (!$id)
        {
            throw new NotFoundException(__('Invalid post'));
        }
        $ids=explode(",",$id);
        $post=array();
        foreach($ids as $k=>$id)
        {
	    $k++;
            $post[$k]=$this->User->findById($id);
	    if(empty($post[$k]['User']['id']) || !$this->_canAccessManagedUser($post[$k]))
	    {
		$this->Session->setFlash(__('Invalid post'),'flash',array('alert'=>'danger'));
		return $this->redirect(array('action' => 'index'));
	    }
            $post[$k]['User']['project_ids']=$this->User->assignedProjectIds($id, false);
	    if($hasFrontViewColumn && (!isset($post[$k]['User']['front_view']) || strlen(trim((string)$post[$k]['User']['front_view']))<=0))
	    {
		$post[$k]['User']['front_view']='On';
	    }
	    if($hasSortIdColumn && isset($post[$k]['User']['ugroup_id']) && $this->_userRoleCategoryForGroup((int)$post[$k]['User']['ugroup_id'])==='agent' && (!isset($post[$k]['User']['sort_id']) || strlen(trim((string)$post[$k]['User']['sort_id']))<=0))
	    {
		$post[$k]['User']['sort_id']=0;
	    }
        }
        $this->set('User',$post);
        if (!$post)
        {
            throw new NotFoundException(__('Invalid post'));
        }
        if ($this->request->is(array('post', 'put')))
        {
	    $this->User->id = $id;
            try
            {
                $saveData = $this->request->data;
		$projectRecord=array();
		$projectRecordMap=array();
                $projectAssignments = array();
                $savedRows = array();
		foreach($saveData as $key => $value)
                {
                    if($hasFrontViewColumn)
                    {
                        $frontView = isset($saveData[$key]['User']['front_view']) ? $saveData[$key]['User']['front_view'] : 'On';
                        $saveData[$key]['User']['front_view'] = $this->_normalizeFrontViewValue($frontView);
                    }
                    else
                    {
                        unset($saveData[$key]['User']['front_view']);
                    }
                    if($hasSortIdColumn && isset($saveData[$key]['User']['sort_id']))
                    {
                        $saveData[$key]['User']['sort_id'] = $this->_normalizeSortIdValue($saveData[$key]['User']['sort_id']);
                    }
                    elseif(!$hasSortIdColumn)
                    {
                        unset($saveData[$key]['User']['sort_id']);
                    }
                    $rowUserGroupId = 0;
                    if(isset($saveData[$key]['User']['ugroup_id']))
                    {
                        $rowUserGroupId = (int)$saveData[$key]['User']['ugroup_id'];
                    }
                    elseif(isset($post[$key]['User']['ugroup_id']))
                    {
                        $rowUserGroupId = (int)$post[$key]['User']['ugroup_id'];
                    }
                    $selection = $this->_normalizeManagedProjectSelection(isset($saveData[$key]['User']) ? $saveData[$key]['User'] : array(), $rowUserGroupId);
                    $selection = $this->_restrictManagedProjectSelectionForCurrentAdmin($selection, isset($post[$key]) ? $post[$key] : array());
                    $rowUserGroupId = (int)$selection['ugroup_id'];
                    $rowRoleCategory = $this->_userRoleCategoryForGroup($rowUserGroupId);
                    if($rowUserGroupId>0 && $rowUserGroupId!==1 && $rowRoleCategory!=='agency' && (int)$selection['project_id']<=0)
                    {
			$this->Session->setFlash(__('Please select agency for this user.'),'flash',array('alert'=>'danger'));
			$this->set('isError',true);
                        $saveData[$key]['User']['project_ids']=$selection['project_ids'];
                        $this->request->data = $saveData;
			return;
		    }
                    $saveData[$key]['User']['ugroup_id']=$rowUserGroupId;
                    $saveData[$key]['User']['project_id']=(int)$selection['project_id'];
                    unset($saveData[$key]['User']['project_ids']);
                    if($rowRoleCategory==='agency' && (int)$selection['project_id']>0)
                    {
			$projectId=(int)$selection['project_id'];
                        if($projectId>0)
                        {
			    $projectRecordMap[$projectId]=array('id'=>$projectId,'name'=>$saveData[$key]['User']['name'],'email'=>$saveData[$key]['User']['email'],'phone'=>$saveData[$key]['User']['phone'],'mobile'=>$saveData[$key]['User']['mobile']);
                        }
		    }
                    $savedUserId = !empty($saveData[$key]['User']['id']) ? (int)$saveData[$key]['User']['id'] : 0;
                    if($savedUserId>0)
                    {
                        $projectAssignments[] = array(
                            'user_id' => $savedUserId,
                            'project_id' => (int)$selection['project_id'],
                            'project_ids' => $selection['project_ids']
                        );
                        $savedRows[] = array(
                            'user_id' => $savedUserId,
                            'ugroup_id' => $rowUserGroupId
                        );
                    }
                }
                if(!empty($projectRecordMap))
                {
                    $projectRecord=array_values($projectRecordMap);
                }
                if ($this->User->saveAll($saveData))
                {
                    if(!empty($projectRecord))
                    {
                        $this->Project->saveAll($projectRecord);
                        foreach($projectRecord as $savedProject)
                        {
                            if(!empty($savedProject['id']))
                            {
                                $this->_ardProvisionAgencyAccountingLedgers((int)$savedProject['id']);
                                $this->_ardEnsureRentalPaymentLedger((int)$savedProject['id']);
                                $this->_ardEnsureRentalIncomeLedger((int)$savedProject['id']);
                            }
                        }
                    }
                    foreach($projectAssignments as $assignment)
                    {
                        $this->User->syncProjectAssignments(
                            (int)$assignment['user_id'],
                            $assignment['project_ids'],
                            (int)$assignment['project_id']
                        );
                    }
                    $redirectMeta = $this->_managedUserRedirectTarget($ugroupTypeMap, $savedRows);
		    $this->Session->setFlash($redirectMeta['message'],'flash',array('alert'=>'success'));
		    return $this->redirect($redirectMeta['redirect']);
                }
            }
            catch (Exception $e)
            {
                $this->Session->setFlash($e.__('Invalid Post'),'flash',array('alert'=>'danger'));
                return $this->redirect(array('action' => 'index'));
            }
            $this->set('isError',true);
        }
        else
        {
            $this->layout = null;
            $this->set('isError',false);
        }
        if (!$this->request->data)
        {
            $this->request->data = $post;
        }
    }
    public function editprofile()
    {
	$this->loadModel('Location');
	$id=$this->luserId;
        $post=$this->User->findById($id);
	$this->set('location',$this->Location->find('list',array('order'=>array('name'=>'asc'))));
        if ($this->request->is(array('post', 'put')))
        {
	    try
            {
		$this->request->data['User']['id']=$id;
                if ($this->User->save($this->request->data['User']))
                {
		    $this->Session->setFlash(__('User has been updated.'),'flash',array('alert'=>'success'));
		    return $this->redirect(array('controller'=>'Users','action' => 'myProfile'));
                }
            }
            catch (Exception $e)
            {
                $this->Session->setFlash(__('Invalid Post'),'flash',array('alert'=>'danger'));
                return $this->redirect(array('action' => 'myProfile'));
            }
        }
	$this->set('post',$post);
        if (!$this->request->data)
        {
            $this->request->data = $post;
        }
    }
    public function deleteall()
    {
        $this->loadModel('User');
        if ($this->request->is('post'))
        {
            $rawIds = array();
            if (!empty($this->data['User']['id'])) {
                $rawIds = is_array($this->data['User']['id']) ? $this->data['User']['id'] : array($this->data['User']['id']);
            } elseif (!empty($this->request->data['User']['id'])) {
                $rawIds = is_array($this->request->data['User']['id']) ? $this->request->data['User']['id'] : array($this->request->data['User']['id']);
            }

            $deletedCount = 0;
            foreach ($rawIds as $value)
            {
                $value = (int)$value;
                if ($value > 1)
                {
                    $userRow = $this->User->findById($value);
                    if (!empty($userRow['User']['id']))
                    {
                        if (!$this->_canAccessManagedUser($userRow))
                        {
                            $this->Session->setFlash(__('You do not have permission to delete one or more selected users.'), 'flash', array('alert' => 'danger'));
                            return $this->redirect(array('action' => 'index'));
                        }
                        $this->_cleanupDeletedUser($value);
                        $this->User->delete($value);
                        $deletedCount++;
                    }
                }
            }

            if ($deletedCount > 0) {
                $this->Session->setFlash(__('Selected user(s) have been deleted.'), 'flash', array('alert' => 'success'));
            } else {
                $this->Session->setFlash(__('Please select at least one valid user to delete.'), 'flash', array('alert' => 'warning'));
            }
        }        
        return $this->redirect(array('action' => 'index'));
    }

    public function admin_delete($id = null)
    {
        return $this->delete($id);
    }

    public function delete($id = null)
    {
        $this->loadModel('User');
        $id = (int)$id;
        if ($id <= 1)
        {
            $this->Session->setFlash(__('Invalid user selected.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }

        $userRow = $this->User->findById($id);
        if (empty($userRow['User']['id']) || !$this->_canAccessManagedUser($userRow))
        {
            $this->Session->setFlash(__('User not found or access denied.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }

        $this->_cleanupDeletedUser($id);
        if ($this->User->delete($id))
        {
            $this->Session->setFlash(__('User has been deleted.'), 'flash', array('alert' => 'success'));
        }
        else
        {
            $this->Session->setFlash(__('Failed to delete user.'), 'flash', array('alert' => 'danger'));
        }
        return $this->redirect(array('action' => 'index'));
    }

    protected function _cleanupDeletedUser($userId)
    {
        $userId = (int)$userId;
        if ($userId <= 0) {
            return;
        }
        try {
            $this->loadModel('FacebookAgentAssignment');
            $this->FacebookAgentAssignment->deleteAll(array('FacebookAgentAssignment.user_id' => $userId), false);
        } catch (Exception $e) {}

        try {
            $this->loadModel('UserProject');
            $this->UserProject->deleteAll(array('UserProject.user_id' => $userId), false);
        } catch (Exception $e) {}
    }
    public function myProfile()
    {
        $userValue=$this->Session->read('User');
        $post = $this->User->findById($userValue['User']['id']);        
	if(strlen($post['User']['photo'])>0)
        $std_img='user_thumb/'.$post['User']['photo'];
        else
        $std_img='User.png';
        $this->set('std_img', $std_img);
	$this->set('post',$post);
    }
    public function assignrights()
    {
	if(!$this->_canManageAllUsers()){
	    return $this->redirect(array('action' => 'index'));
	}
        $this->loadModel('Ugroup');
        $this->_ensureUserRoleCategorySchema();
        $Ugroup=$this->Ugroup->find('all',array('conditions'=>array('id >'=>1),'order'=>'name asc'));
        $this->set('Ugroup',$Ugroup);
        $this->set('roleCategoryOptions',$this->_userRoleCategoryOptions());
    }
    public function assignrightsedit($id=null)
    {
	if(!$this->_canManageAllUsers()){
	    return $this->redirect(array('action' => 'index'));
	}
        $this->layout = null;
        $this->loadModel('Ugroup');
        $this->loadModel('PageRight');
        if (!$id)
        {
            throw new NotFoundException(__('Invalid post'));
        }
        $isPost=$this->Ugroup->find('count',array('conditions'=>array('id'=>$id)));
        if($isPost==0)
        {
            throw new NotFoundException(__('Invalid post'));
        }
        $post=$this->User->assingPages($id);
        $this->set('PageRight',$post);
        if ($this->request->is(array('post', 'put')))
        {
            try
            {
                $posted = isset($this->request->data['PageRight']) ? (array)$this->request->data['PageRight'] : array();
                $pageIds = isset($posted['page_id']) ? (array)$posted['page_id'] : array();
                $viewRights = isset($posted['view_right']) ? (array)$posted['view_right'] : array();
                $saveRights = isset($posted['save_right']) ? (array)$posted['save_right'] : array();
                $updateRights = isset($posted['update_right']) ? (array)$posted['update_right'] : array();
                $deleteRights = isset($posted['delete_right']) ? (array)$posted['delete_right'] : array();

                $this->PageRight->deleteAll(array('ugroup_id'=>$id), false);
                foreach($pageIds as $pageValue)
                {
                    $pageId = (int)$pageValue;
                    if($pageId<=0)
                    {
                        continue;
                    }
                    $canView = !empty($viewRights[$pageId]) ? 1 : 0;
                    $canAdd = !empty($saveRights[$pageId]) ? 1 : 0;
                    $canEdit = !empty($updateRights[$pageId]) ? 1 : 0;
                    $canDelete = !empty($deleteRights[$pageId]) ? 1 : 0;

                    if($canAdd || $canEdit || $canDelete)
                    {
                        $canView = 1;
                    }
                    $this->PageRight->create();
                    $this->PageRight->save(array(
                        'page_id'=>$pageId,
                        'ugroup_id'=>$id,
                        'view_right'=>$canView,
                        'save_right'=>$canAdd,
                        'update_right'=>$canEdit,
                        // Keep DB compatibility: search_right is used as delete-right slot.
                        'search_right'=>$canDelete
                    ));
                }
                $this->_bumpMenuPermissionVersion((int)$id);
                $this->Session->setFlash(__('Permission update successfully'),'flash',array('alert'=>'success'));
                return $this->redirect(array('action' => 'assignrights'));
            }
            catch (Exception $e)
            {
                $this->Session->setFlash(__('Error in updation'),'flash',array('alert'=>'danger'));
                return $this->redirect(array('action' => 'assignrights'));
            }
        }
        $this->set('id',$id);
        if (!$this->request->data)
        {
            $this->request->data = $post;
        }
    }
    protected function _adminLoginToken()
    {
        $token = (string)$this->Session->read('AdminLogin.token');
        if(strlen($token) < 32)
        {
            $token = $this->_buildAdminLoginToken();
            $this->Session->write('AdminLogin.token', $token);
        }
        return $token;
    }
    protected function _buildAdminLoginToken()
    {
        $issuedAt = time();
        $nonce = Security::hash(uniqid('', true).mt_rand().microtime(true), 'sha256', true);
        $signature = $this->_adminLoginTokenSignature($issuedAt, $nonce);
        return $issuedAt.'.'.$nonce.'.'.$signature;
    }
    protected function _adminLoginTokenSignature($issuedAt, $nonce)
    {
        $payload = (int)$issuedAt.'|'.(string)$nonce.'|'.(isset($_SERVER['HTTP_USER_AGENT']) ? substr((string)$_SERVER['HTTP_USER_AGENT'],0,180) : '');
        $secret = (string)Configure::read('Security.salt').'|white-villas-admin-login';
        if(function_exists('hash_hmac'))
        {
            return hash_hmac('sha256', $payload, $secret);
        }
        return Security::hash($payload.'|'.$secret, 'sha256', true);
    }
    protected function _validStatelessAdminLoginToken($postedToken)
    {
        $postedToken = (string)$postedToken;
        $parts = explode('.', $postedToken);
        if(count($parts) !== 3)
        {
            return false;
        }
        $issuedAt = (int)$parts[0];
        $nonce = (string)$parts[1];
        $signature = (string)$parts[2];
        if($issuedAt <= 0 || strlen($nonce) < 32 || strlen($signature) < 32)
        {
            return false;
        }
        if($issuedAt < (time() - 7200) || $issuedAt > (time() + 300))
        {
            return false;
        }
        $expected = $this->_adminLoginTokenSignature($issuedAt, $nonce);
        if(function_exists('hash_equals'))
        {
            return hash_equals($expected, $signature);
        }
        return $expected === $signature;
    }
    protected function _validAdminLoginToken($postedToken)
    {
        $sessionToken = (string)$this->Session->read('AdminLogin.token');
        $postedToken = (string)$postedToken;
        if($postedToken === '')
        {
            return false;
        }
        if($sessionToken !== '')
        {
            if(function_exists('hash_equals'))
            {
                if(hash_equals($sessionToken, $postedToken))
                {
                    return true;
                }
            }
            elseif($sessionToken === $postedToken)
            {
                return true;
            }
        }
        return $this->_validStatelessAdminLoginToken($postedToken);
    }
    protected function _adminLoginThrottleKey($username)
    {
        $username = strtolower(trim((string)$username));
        $ipAddress = (string)$this->request->clientIp();
        return 'admin_login_'.sha1($ipAddress.'|'.$username);
    }
    protected function _adminLoginThrottleState($username)
    {
        $state = false;
        try
        {
            $state = Cache::read($this->_adminLoginThrottleKey($username));
        }
        catch(Exception $e)
        {
            $state = false;
        }
        if(!is_array($state))
        {
            $state = array('failures'=>array(),'locked_until'=>0);
        }
        $now = time();
        $recentFailures = array();
        foreach((array)$state['failures'] as $failureTime)
        {
            $failureTime = (int)$failureTime;
            if($failureTime >= ($now - 900))
            {
                $recentFailures[] = $failureTime;
            }
        }
        $state['failures'] = $recentFailures;
        if(!empty($state['locked_until']) && (int)$state['locked_until'] <= $now)
        {
            $state['locked_until'] = 0;
        }
        return $state;
    }
    protected function _writeAdminLoginThrottleState($username, $state)
    {
        try
        {
            Cache::write($this->_adminLoginThrottleKey($username), $state);
        }
        catch(Exception $e)
        {
            // Login should continue even if the cache backend is temporarily unavailable.
        }
    }
    protected function _adminLoginLockSeconds($username)
    {
        $state = $this->_adminLoginThrottleState($username);
        if(!empty($state['locked_until']) && (int)$state['locked_until'] > time())
        {
            return (int)$state['locked_until'] - time();
        }
        return 0;
    }
    protected function _recordAdminLoginFailure($username)
    {
        $state = $this->_adminLoginThrottleState($username);
        $state['failures'][] = time();
        if(count($state['failures']) >= 5)
        {
            $state['locked_until'] = time() + 300;
            $state['failures'] = array();
        }
        $this->_writeAdminLoginThrottleState($username, $state);
    }
    protected function _clearAdminLoginThrottle($username)
    {
        try
        {
            Cache::delete($this->_adminLoginThrottleKey($username));
        }
        catch(Exception $e)
        {
            // Nothing to clear.
        }
    }
    function login_form()
    {
        $this->set('loginCsrfToken', $this->_adminLoginToken());
        if ($this->request->is('post'))
        {
            $username = isset($this->request->data['User']['username']) ? trim((string)$this->request->data['User']['username']) : '';
            $loginIdentity = strtolower($username);
            $plainPassword = isset($this->request->data['User']['password']) ? (string)$this->request->data['User']['password'] : '';
            $postedToken = isset($this->request->data['User']['login_token']) ? (string)$this->request->data['User']['login_token'] : '';
            $honeypot = isset($this->request->data['User']['company_site']) ? trim((string)$this->request->data['User']['company_site']) : '';
            if(!$this->_validAdminLoginToken($postedToken))
            {
                $this->Session->setFlash(__('Your login session expired. Please try again.'),'flash', array('alert'=> 'danger'));
                $this->redirect(array('action' => 'login_form'));
                exit();
            }
            if($honeypot !== '')
            {
                $this->_recordAdminLoginFailure($loginIdentity);
                $this->Session->setFlash(__('Unable to login. Please check your credentials and try again.'),'flash', array('alert'=> 'danger'));
                $this->redirect(array('action' => 'login_form'));
                exit();
            }
            $lockedSeconds = $this->_adminLoginLockSeconds($loginIdentity);
            if($lockedSeconds > 0)
            {
                $this->Session->setFlash(sprintf(__('Too many failed attempts. Please try again in %s minutes.'),ceil($lockedSeconds/60)),'flash', array('alert'=> 'danger'));
                $this->redirect(array('action' => 'login_form'));
                exit();
            }
            if($username === '' || $plainPassword === '')
            {
                $this->_recordAdminLoginFailure($loginIdentity);
                $this->Session->setFlash(__('Unable to login. Please check your credentials and try again.'),'flash', array('alert'=> 'danger'));
                $this->redirect(array('action' => 'login_form'));
                exit();
            }
            $user = $this->User->find('first', array(
                'conditions' => array(
                    'OR' => array(
                        'LOWER(User.username)' => $loginIdentity,
                        'LOWER(User.email)' => $loginIdentity
                    ),
                    'User.deleted IS NULL'
                ),
                'recursive' => 0
            ));
            if($user != false)
            {
                $userLoginStatus = !empty($user['User']['login_status']) ? trim((string)$user['User']['login_status']) : 'Active';
                if(strcasecmp($userLoginStatus, 'Blocked') === 0)
                {
                    $this->_recordAdminLoginFailure($loginIdentity);
                    $this->Session->setFlash(__('Login is blocked. Please contact administrator.'),'flash', array('alert'=> 'danger'));
                    $this->redirect(array('action' => 'login_form'));
                    exit();
                }
                if(isset($user['User']['status']) && (string)$user['User']['status'] !== 'Active')
                {
                    $this->_recordAdminLoginFailure($loginIdentity);
                    $this->Session->setFlash(__('Account is suspended. Please contact administrator.'),'flash', array('alert'=> 'danger'));
                    $this->redirect(array('action' => 'login_form'));
                    exit();
                }
            }
            if($user != false && !empty($user['User']['password']) && $this->_verifyPassword($plainPassword, $user['User']['password']))
            {   
                if($this->_passwordNeedsRefresh($user['User']['password']))
                {
                    $this->_rehashPasswordForModel($this->User, (int)$user['User']['id'], $plainPassword);
                }
                $this->_clearAdminLoginThrottle($loginIdentity);
                if(!empty($user['User']['username']))
                {
                    $this->_clearAdminLoginThrottle(strtolower((string)$user['User']['username']));
                }
                if(!empty($user['User']['email']))
                {
                    $this->_clearAdminLoginThrottle(strtolower((string)$user['User']['email']));
                }
                if($this->User->hasField('last_login'))
                {
                    try
                    {
                        $this->User->id = (int)$user['User']['id'];
                        $this->User->save(array('User'=>array('id'=>(int)$user['User']['id'],'last_login'=>date('Y-m-d H:i:s'))),array('validate'=>false,'callbacks'=>false));
                    }
                    catch(Exception $e)
                    {
                        // Last-login is helpful but should never block access.
                    }
                }
                CakeSession::renew();
                $this->Session->delete('AdminLogin.token');
                $this->Session->setFlash(__('Thank you for logging in!'),'flash', array('alert'=> 'success'));
                $this->Session->write('User', $user);                
                $this->_writeAuditEntry(array(
                    'user_id' => isset($user['User']['id']) ? (int)$user['User']['id'] : null,
                    'ugroup_id' => isset($user['User']['ugroup_id']) ? (int)$user['User']['ugroup_id'] : null,
                    'project_id' => isset($user['User']['project_id']) ? (int)$user['User']['project_id'] : null,
                    'controller' => 'users',
                    'action' => 'login_form',
                    'activity' => 'login',
                    'method' => 'POST',
                    'url' => $this->request->here(false),
                    'ip_address' => $this->request->clientIp(),
                    'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? substr((string)$_SERVER['HTTP_USER_AGENT'], 0, 250) : '',
                    'request_data' => $this->_serializeAuditPayload(array('query' => (array)$this->request->query)),
                    'created' => date('Y-m-d H:i:s')
                ));
                $loginGroupType = !empty($user['Ugroup']['type']) ? strtoupper(trim((string)$user['Ugroup']['type'])) : '';
                $loginGroupName = !empty($user['Ugroup']['name']) ? strtolower(trim((string)$user['Ugroup']['name'])) : '';
                if($loginGroupType === 'RCO' || $loginGroupName === 'recovery officer')
                {
                    $this->Redirect(array('controller' => 'Recoveries', 'action' => 'index'));
                    exit();
                }
                $this->Redirect(array('controller' => 'Dashboards', 'action' => 'index'));
                exit();
            }
            else
            {
                $this->_recordAdminLoginFailure($loginIdentity);
                usleep(250000);
                $this -> Session -> setFlash(__('Unable to login. Please check your credentials and try again.'),'flash', array('alert'=> 'danger'));
                $this -> Redirect(array('action' => 'login_form'));
                exit();
            }
        } 
    }
      function logout() {
          try
          {
              $sessionUser = (array)$this->Session->read('User');
              if(!empty($sessionUser['User']['id']))
              {
                $this->_writeAuditEntry(array(
                    'user_id' => isset($sessionUser['User']['id']) ? (int)$sessionUser['User']['id'] : null,
                    'ugroup_id' => isset($sessionUser['User']['ugroup_id']) ? (int)$sessionUser['User']['ugroup_id'] : null,
                    'project_id' => isset($sessionUser['User']['project_id']) ? (int)$sessionUser['User']['project_id'] : null,
                    'controller' => 'users',
                    'action' => 'logout',
                    'activity' => 'logout',
                    'method' => strtoupper((string)$this->request->method()),
                    'url' => $this->request->here(false),
                    'ip_address' => $this->request->clientIp(),
                    'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? substr((string)$_SERVER['HTTP_USER_AGENT'], 0, 250) : '',
                    'request_data' => $this->_serializeAuditPayload(array('query' => (array)$this->request->query)),
                    'created' => date('Y-m-d H:i:s')
                ));
              }
          }
          catch (Exception $e)
          {
              // Ignore audit logging failures on logout
          }

          $this->Session->delete('Impersonation.admin');
          $this->Session->delete('User');
          $this->Session->destroy();
          return $this->redirect(array('plugin' => 'admin', 'controller' => 'users', 'action' => 'login_form'));
      }
    public function loginas($id = null)
    {
        if(!$id)
        {
            $this->Session->setFlash(__('Invalid Post'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'index'));
        }

        // Rule 1: Prevent nested / chained impersonation
        $impMeta = (array)$this->Session->read('Impersonation.admin');
        if(!empty($impMeta['is_impersonating']) || !empty($impMeta['original_user']['User']['id']))
        {
            $this->Session->setFlash(__('You are already in an active impersonation session. Please exit the current impersonation before switching accounts.'), 'flash', array('alert' => 'warning'));
            return $this->redirect(array('controller' => 'Dashboards', 'action' => 'index'));
        }

        // Rule 2: Caller authentication & authorization check
        $currentUser = (array)$this->Session->read('User');
        $currentUserId = !empty($currentUser['User']['id']) ? (int)$currentUser['User']['id'] : 0;
        if($currentUserId <= 0)
        {
            $this->Session->setFlash(__('You must be logged in to perform this action.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('controller' => 'Users', 'action' => 'login_form'));
        }

        $isSuperAdmin = $this->_isSuperAdminSession($currentUser);
        $isAgencyAdmin = $this->_isAgencyAdminSession($currentUser);

        if(!$isSuperAdmin && !$isAgencyAdmin)
        {
            $this->Session->setFlash(__('You do not have permission to log in as another user.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('controller' => 'Dashboards', 'action' => 'index'));
        }

        // Rule 3: Protected accounts
        if((int)$id === 1)
        {
            $this->Session->setFlash(__('Super Administrator cannot be impersonated.'), 'flash', array('alert' => 'warning'));
            return $this->redirect($isAgencyAdmin ? array('controller' => 'Agents', 'action' => 'index') : array('controller' => 'Dashboards', 'action' => 'index'));
        }

        if((int)$id === $currentUserId)
        {
            $this->Session->setFlash(__('You are already logged in as this user.'), 'flash', array('alert' => 'warning'));
            return $this->redirect($isAgencyAdmin ? array('controller' => 'Agents', 'action' => 'index') : array('controller' => 'Dashboards', 'action' => 'index'));
        }

        // Rule 4: Target existence and account status
        $this->loadModel('Admin.User');
        $targetUser = $this->User->find('first', array(
            'conditions' => array(
                'User.id' => (int)$id,
                'User.status' => 'Active',
                'User.deleted IS NULL'
            ),
            'recursive' => 0
        ));

        if(empty($targetUser['User']['id']))
        {
            $this->Session->setFlash(__('Target user not found or inactive.'), 'flash', array('alert' => 'danger'));
            return $this->redirect($isAgencyAdmin ? array('controller' => 'Agents', 'action' => 'index') : array('action' => 'index'));
        }

        if(!empty($targetUser['User']['login_status']) && strcasecmp(trim((string)$targetUser['User']['login_status']), 'Blocked') === 0)
        {
            $this->Session->setFlash(__('Login is blocked for this user/agent.'), 'flash', array('alert' => 'danger'));
            return $this->redirect($isAgencyAdmin ? array('controller' => 'Agents', 'action' => 'index') : array('action' => 'index'));
        }

        // Rule 5: Strict validation for Agency Admins (Scope and Boundary Enforcement)
        if(!$isSuperAdmin && $isAgencyAdmin)
        {
            $agentRoleIds = $this->_agentRoleIds();
            $targetGroupId = !empty($targetUser['User']['ugroup_id']) ? (int)$targetUser['User']['ugroup_id'] : 0;
            $targetGroupType = !empty($targetUser['Ugroup']['type']) ? strtoupper(trim((string)$targetUser['Ugroup']['type'])) : '';
            $targetGroupName = !empty($targetUser['Ugroup']['name']) ? strtolower(trim((string)$targetUser['Ugroup']['name'])) : '';
            $targetRoleCategory = !empty($targetUser['Ugroup']['role_category']) ? strtolower(trim((string)$targetUser['Ugroup']['role_category'])) : '';

            $isAgentRole = (
                in_array($targetGroupId, $agentRoleIds, true) ||
                $targetGroupType === 'AGT' ||
                $targetRoleCategory === 'agent' ||
                strpos($targetGroupName, 'agent') !== false
            );

            // Block privilege escalation: Agency Admin CANNOT impersonate Super Admin or another Agency Admin or non-agent
            if(!$isAgentRole || $this->_isSuperAdminSession($targetUser) || $this->_isAgencyAdminSession($targetUser) || $targetGroupType === 'ADR')
            {
                try
                {
                    $this->_writeAuditEntry(array(
                        'user_id' => $currentUserId,
                        'ugroup_id' => !empty($currentUser['User']['ugroup_id']) ? (int)$currentUser['User']['ugroup_id'] : 4,
                        'project_id' => !empty($currentUser['User']['project_id']) ? (int)$currentUser['User']['project_id'] : null,
                        'controller' => 'users',
                        'action' => 'loginas',
                        'activity' => 'impersonate_denied_role',
                        'method' => 'GET',
                        'url' => $this->request->here(false),
                        'ip_address' => $this->request->clientIp(),
                        'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? substr((string)$_SERVER['HTTP_USER_AGENT'], 0, 250) : '',
                        'request_data' => $this->_serializeAuditPayload(array(
                            'agency_admin_id' => $currentUserId,
                            'target_user_id' => (int)$targetUser['User']['id'],
                            'target_role' => $targetGroupType,
                            'reason' => 'Agency Admin attempted to impersonate non-agent user'
                        )),
                        'created' => date('Y-m-d H:i:s')
                    ));
                }
                catch(Exception $e) {}

                $this->Session->setFlash(__('Agency administrators are only permitted to log in as agents.'), 'flash', array('alert' => 'danger'));
                return $this->redirect(array('controller' => 'Agents', 'action' => 'index'));
            }

            // Agency Boundary Enforcement: Agent must belong to the Agency Admin's agency
            $adminAgencyId = !empty($currentUser['User']['project_id']) ? (int)$currentUser['User']['project_id'] : (int)$this->agencyId;
            $adminProjectIds = array();
            if(method_exists($this->User, 'assignedProjectIds'))
            {
                $adminProjectIds = (array)$this->User->assignedProjectIds($currentUserId, true);
            }
            if($adminAgencyId > 0 && !in_array($adminAgencyId, $adminProjectIds, true))
            {
                $adminProjectIds[] = $adminAgencyId;
            }

            $agentProjectIds = array();
            if(method_exists($this->User, 'assignedProjectIds'))
            {
                $agentProjectIds = (array)$this->User->assignedProjectIds((int)$targetUser['User']['id'], true);
            }
            $agentPrimaryProject = !empty($targetUser['User']['project_id']) ? (int)$targetUser['User']['project_id'] : 0;
            if($agentPrimaryProject > 0 && !in_array($agentPrimaryProject, $agentProjectIds, true))
            {
                $agentProjectIds[] = $agentPrimaryProject;
            }

            $hasSharedAgency = (count(array_intersect($adminProjectIds, $agentProjectIds)) > 0);
            if(!$hasSharedAgency && $adminAgencyId > 0 && method_exists($this->User, 'userHasProjectAccess'))
            {
                $hasSharedAgency = $this->User->userHasProjectAccess((int)$targetUser['User']['id'], $adminAgencyId);
            }

            if(!$hasSharedAgency)
            {
                try
                {
                    $this->_writeAuditEntry(array(
                        'user_id' => $currentUserId,
                        'ugroup_id' => !empty($currentUser['User']['ugroup_id']) ? (int)$currentUser['User']['ugroup_id'] : 4,
                        'project_id' => $adminAgencyId > 0 ? $adminAgencyId : null,
                        'controller' => 'users',
                        'action' => 'loginas',
                        'activity' => 'impersonate_denied_cross_agency',
                        'method' => 'GET',
                        'url' => $this->request->here(false),
                        'ip_address' => $this->request->clientIp(),
                        'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? substr((string)$_SERVER['HTTP_USER_AGENT'], 0, 250) : '',
                        'request_data' => $this->_serializeAuditPayload(array(
                            'agency_admin_id' => $currentUserId,
                            'admin_agencies' => array_values($adminProjectIds),
                            'target_agent_id' => (int)$targetUser['User']['id'],
                            'agent_agencies' => array_values($agentProjectIds),
                            'reason' => 'Target agent does not belong to administrator agency'
                        )),
                        'created' => date('Y-m-d H:i:s')
                    ));
                }
                catch(Exception $e) {}

                $this->Session->setFlash(__('You can only log in as agents belonging to your agency.'), 'flash', array('alert' => 'danger'));
                return $this->redirect(array('controller' => 'Agents', 'action' => 'index'));
            }
        }

        // Rule 6: Initialize Impersonation Session
        $originalAdminId = (int)$currentUser['User']['id'];
        $originalAdminName = !empty($currentUser['User']['name']) ? (string)$currentUser['User']['name'] : 'Administrator';
        $originalAdminRole = $isSuperAdmin ? 'Super Administrator' : 'Agency Administrator';
        $originalAdminType = $isSuperAdmin ? 'ADR' : 'AGY';
        $targetRole = !empty($targetUser['Ugroup']['name']) ? (string)$targetUser['Ugroup']['name'] : (!empty($targetUser['Ugroup']['type']) ? (string)$targetUser['Ugroup']['type'] : 'Agent');

        $impMeta = array(
            'is_impersonating' => true,
            'started_at' => time(),
            'original_user' => $currentUser,
            'impersonator_id' => $originalAdminId,
            'impersonator_name' => $originalAdminName,
            'impersonator_role' => $originalAdminRole,
            'impersonator_type' => $originalAdminType,
            'impersonator_agency_id' => !empty($currentUser['User']['project_id']) ? (int)$currentUser['User']['project_id'] : (int)$this->agencyId,
            'target_user_id' => (int)$targetUser['User']['id'],
            'target_user_name' => (string)$targetUser['User']['name'],
            'target_user_role' => $targetRole,
            'target_agency_id' => !empty($targetUser['User']['project_id']) ? (int)$targetUser['User']['project_id'] : 0,
            'ip_address' => $this->request->clientIp(),
            'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? substr((string)$_SERVER['HTTP_USER_AGENT'], 0, 250) : ''
        );

        $this->Session->write('Impersonation.admin', $impMeta);
        $this->Session->write('User', $targetUser);

        $targetGroupId = !empty($targetUser['User']['ugroup_id']) ? (int)$targetUser['User']['ugroup_id'] : 0;
        $this->_bumpMenuPermissionVersion($targetGroupId);
        $this->Session->delete('Admin.menu_cache');
        $this->Session->delete('Admin.menu_bootstrap_version');
        CakeSession::renew();

        // Rule 7: Audit Trail
        try
        {
            $this->_writeAuditEntry(array(
                'user_id' => $originalAdminId,
                'ugroup_id' => !empty($currentUser['User']['ugroup_id']) ? (int)$currentUser['User']['ugroup_id'] : ($isSuperAdmin ? 1 : 4),
                'project_id' => !empty($currentUser['User']['project_id']) ? (int)$currentUser['User']['project_id'] : ($this->agencyId > 0 ? (int)$this->agencyId : null),
                'controller' => 'users',
                'action' => 'loginas',
                'activity' => 'impersonate_start',
                'method' => 'GET',
                'url' => $this->request->here(false),
                'ip_address' => $this->request->clientIp(),
                'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? substr((string)$_SERVER['HTTP_USER_AGENT'], 0, 250) : '',
                'request_data' => $this->_serializeAuditPayload(array(
                    'impersonator_id' => $originalAdminId,
                    'impersonator_name' => $originalAdminName,
                    'impersonator_type' => $originalAdminType,
                    'impersonator_role' => $originalAdminRole,
                    'target_user_id' => (int)$targetUser['User']['id'],
                    'target_user_name' => (string)$targetUser['User']['name'],
                    'target_user_role' => $targetRole,
                    'target_agency_id' => (int)$targetUser['User']['project_id']
                )),
                'created' => date('Y-m-d H:i:s')
            ));
        }
        catch(Exception $e) {}

        $loginGroupType = !empty($targetUser['Ugroup']['type']) ? strtoupper(trim((string)$targetUser['Ugroup']['type'])) : '';
        $loginGroupName = !empty($targetUser['Ugroup']['name']) ? strtolower(trim((string)$targetUser['Ugroup']['name'])) : '';
        $this->Session->setFlash(sprintf(__('You are now safely logged in as %s (%s).'), (string)$targetUser['User']['name'], $targetRole),'flash',array('alert'=>'success'));

        if($loginGroupType === 'RCO' || $loginGroupName === 'recovery officer')
        {
            return $this->redirect(array('controller' => 'Recoveries', 'action' => 'index'));
        }
        return $this->redirect(array('controller' => 'Dashboards', 'action' => 'index'));
    }
    public function stopimpersonation()
    {
        $impMeta = (array)$this->Session->read('Impersonation.admin');
        if(empty($impMeta['original_user']['User']['id']))
        {
            $this->Session->setFlash(__('No active impersonation session found.'),'flash',array('alert'=>'warning'));
            return $this->redirect(array('controller' => 'Dashboards', 'action' => 'index'));
        }
        $originalUser = $impMeta['original_user'];
        $originalUserId = (int)$originalUser['User']['id'];
        $targetUserId = !empty($impMeta['target_user_id']) ? (int)$impMeta['target_user_id'] : 0;
        $originalGroupId = !empty($originalUser['User']['ugroup_id']) ? (int)$originalUser['User']['ugroup_id'] : 1;
        $isOriginalAgencyAdmin = $this->_isAgencyAdminSession($originalUser);
        $roleName = !empty($impMeta['impersonator_role']) ? $impMeta['impersonator_role'] : ($isOriginalAgencyAdmin ? __('Agency Administrator') : __('Administrator'));

        $this->Session->write('User', $originalUser);
        $this->Session->delete('Impersonation.admin');
        $this->_bumpMenuPermissionVersion($originalGroupId);
        $this->Session->delete('Admin.menu_cache');
        $this->Session->delete('Admin.menu_bootstrap_version');
        CakeSession::renew();
        try
        {
            $this->_writeAuditEntry(array(
                'user_id' => $originalUserId,
                'ugroup_id' => $originalGroupId,
                'project_id' => !empty($originalUser['User']['project_id']) ? (int)$originalUser['User']['project_id'] : null,
                'controller' => 'users',
                'action' => 'stopimpersonation',
                'activity' => 'impersonate_stop',
                'method' => $this->request->method(),
                'url' => $this->request->here(false),
                'ip_address' => $this->request->clientIp(),
                'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? substr((string)$_SERVER['HTTP_USER_AGENT'], 0, 250) : '',
                'request_data' => $this->_serializeAuditPayload(array(
                    'reverted_from_user_id' => $targetUserId,
                    'reverted_to_user_id' => $originalUserId,
                    'reverted_to_role' => $roleName
                )),
                'created' => date('Y-m-d H:i:s')
            ));
        }
        catch(Exception $e) {}

        $this->Session->setFlash(sprintf(__('Impersonation ended safely. You are back in %s mode.'), $roleName),'flash',array('alert'=>'success'));
        if($isOriginalAgencyAdmin)
        {
            return $this->redirect(array('controller' => 'Agents', 'action' => 'index'));
        }
        return $this->redirect(array('controller' => 'Dashboards', 'action' => 'index'));
    }
    public function changePass()
    {
        if ($this->Session->check('Impersonation.admin'))
        {
            $this->Session->setFlash(__('Password changes are disabled while impersonating another account. Please stop impersonation first.'), 'flash', array('alert' => 'warning'));
            return $this->redirect(array('controller' => 'Dashboards', 'action' => 'index'));
        }
        if ($this->request->is(array('post', 'put')))
        {
            $userValue=$this->Session->read('User');
            $post = $this->User->findById($userValue['User']['id']);
            if(!empty($post['User']['password']) && $this->_verifyPassword((string)$this->request->data['User']['oldPassword'], $post['User']['password']))
            {
                $this->User->id = $userValue['User']['id'];
                $this->User->unbindValidation('remove', array('name','username','address','phone','mobile','email','skype','twitter','facebook','linkedin','description','photo'), true);
                $saveData = $this->request->data;
                if(isset($saveData['User']['password']))
                {
                    $saveData['User']['password'] = (string)$saveData['User']['password'];
                }
                if ($this->User->save($saveData))
                {
                    $this->Session->setFlash(__('Password Changed Successfully'),'flash',array('alert'=>'success'));
                }                
            }
            else
            {
                $this->Session->setFlash(__('Invalid Password.'),'flash',array('alert'=>'danger'));
            }
            $this->redirect(array('action' => 'changePass'));
        }
    }

    protected function _ensureUserRoleCategorySchema()
    {
        $this->loadModel('Ugroup');
        try
        {
            $columns = (array)$this->Ugroup->query("SHOW COLUMNS FROM `ugroups` LIKE 'role_category'");
            if(empty($columns))
            {
                $this->Ugroup->query("ALTER TABLE `ugroups` ADD COLUMN `role_category` varchar(30) DEFAULT NULL AFTER `type`");
            }
            $this->Ugroup->query("UPDATE `ugroups` SET `role_category`=CASE WHEN `type`='ADR' THEN 'administrator' WHEN `type`='AGY' THEN 'agency' WHEN `type`='AGT' THEN 'agent' ELSE 'system_user' END WHERE `role_category` IS NULL OR TRIM(`role_category`)='' OR (`type`='RCO' AND `role_category`='recovery')");
            $this->Ugroup->schema(true);
            return true;
        }
        catch(Exception $e)
        {
            CakeLog::write('error', 'User role category schema failed: '.$e->getMessage());
            return false;
        }
    }

    protected function _userRoleCategoryOptions()
    {
        return array(
            'system_user' => __('System User'),
            'agency' => __('Agency'),
            'agent' => __('Agent')
        );
    }

    protected function _userRoleCategoryMeta($category)
    {
        $meta = array(
            'system_user' => array('type' => null, 'template_group_id' => 3),
            'agency' => array('type' => 'AGY', 'template_group_id' => 4),
            'agent' => array('type' => 'AGT', 'template_group_id' => 2)
        );
        return isset($meta[$category]) ? $meta[$category] : array();
    }

    protected function _userRoleOptionsByCategory($category)
    {
        $this->_ensureUserRoleCategorySchema();
        $this->loadModel('Ugroup');
        $category = strtolower(trim((string)$category));
        return (array)$this->Ugroup->find('list', array(
            'fields' => array('Ugroup.id','Ugroup.name'),
            'conditions' => array('Ugroup.role_category' => $category, 'Ugroup.id !=' => 1),
            'order' => array('Ugroup.name' => 'asc'),
            'recursive' => -1
        ));
    }

    protected function _userRoleCategoryForGroup($groupId)
    {
        $groupId = (int)$groupId;
        if($groupId <= 0)
        {
            return '';
        }
        $this->_ensureUserRoleCategorySchema();
        $this->loadModel('Ugroup');
        return strtolower(trim((string)$this->Ugroup->field('role_category', array('Ugroup.id' => $groupId))));
    }

    protected function _seedUserRolePermissions($groupId, $templateGroupId)
    {
        $groupId = (int)$groupId;
        $templateGroupId = (int)$templateGroupId;
        if($groupId <= 0 || $templateGroupId <= 0)
        {
            return false;
        }
        $this->loadModel('PageRight');
        $this->PageRight->query(
            "INSERT IGNORE INTO `page_rights` (`page_id`,`ugroup_id`,`save_right`,`update_right`,`view_right`,`search_right`) ".
            "SELECT `page_id`,".$groupId.",MAX(COALESCE(`save_right`,0)),MAX(COALESCE(`update_right`,0)),MAX(COALESCE(`view_right`,0)),MAX(COALESCE(`search_right`,0)) ".
            "FROM `page_rights` WHERE `ugroup_id`=".$templateGroupId." GROUP BY `page_id`"
        );
        return ((int)$this->PageRight->find('count', array('conditions' => array('PageRight.ugroup_id' => $groupId), 'recursive' => -1)) > 0);
    }

    public function leveladd()
    {
	if(!$this->_canManageAllUsers()){
	    return $this->redirect(array('action' => 'index'));
	}
        $this->loadModel('Ugroup');
        if(!$this->_ensureUserRoleCategorySchema())
        {
            $this->Session->setFlash(__('Role category setup could not be initialized.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'assignrights'));
        }
        $roleCategoryOptions = $this->_userRoleCategoryOptions();
        $this->set('roleCategoryOptions', $roleCategoryOptions);
        if ($this->request->is('post'))
        {
            $postedUser = isset($this->request->data['User']) ? (array)$this->request->data['User'] : array();
            $category = strtolower(trim((string)(isset($postedUser['role_category']) ? $postedUser['role_category'] : '')));
            $roleName = trim((string)(isset($postedUser['name']) ? $postedUser['name'] : ''));
            $categoryMeta = $this->_userRoleCategoryMeta($category);
            if($roleName === '' || empty($categoryMeta) || !isset($roleCategoryOptions[$category]))
            {
                $this->Session->setFlash(__('Please select a user category and enter the level name.'),'flash',array('alert'=>'danger'));
                return;
            }
            if($this->Ugroup->find('count', array('conditions' => array('LOWER(Ugroup.name)' => strtolower($roleName)), 'recursive' => -1)) > 0)
            {
                $this->Session->setFlash(__('This level name already exists.'),'flash',array('alert'=>'danger'));
                return;
            }
            $db = $this->Ugroup->getDataSource();
            $db->begin();
            try
            {
                $this->Ugroup->create();
		$this->Ugroup->unbindValidation('keep', array('name'), true);
                if(!$this->Ugroup->save(array('Ugroup' => array(
                    'name' => $roleName,
                    'type' => $categoryMeta['type'],
                    'role_category' => $category
                )), array('validate' => true)))
                {
                    throw new Exception(__('Level User could not be saved.'));
                }
                $groupId = (int)$this->Ugroup->id;
                if(!$this->_seedUserRolePermissions($groupId, (int)$categoryMeta['template_group_id']))
                {
                    throw new Exception(__('Related category permissions could not be created.'));
                }
                $db->commit();
                $this->_bumpMenuPermissionVersion($groupId);
                $this->Session->setFlash(__('Level User has been saved with related category permissions. You can now customize them.'),'flash',array('alert'=>'success'));
                return $this->redirect(array('action' => 'assignrights'));
            }
            catch (Exception $e)
	    {
		$db->rollback();
		$this->Session->setFlash(__($e->getMessage()),'flash',array('alert'=>'danger'));
		return;
	    }
        }
    }
    public function deletelevel()
    {
	if(!$this->_canManageAllUsers()){
	    return $this->redirect(array('action' => 'index'));
	}
        $this->loadModel('Ugroup');
        if ($this->request->is('post'))
        {
            $this->loadModel('PageRight');
            $protectedGroupIds = array(1,2,3,4,9);
            $blockedNames = array();
            $deletedCount = 0;
            $db = $this->Ugroup->getDataSource();
            $db->begin();
            try
            {
                $selectedIds = !empty($this->data['Ugroup']['id']) ? (array)$this->data['Ugroup']['id'] : array();
                foreach($selectedIds as $value)
                {
                    $groupId = (int)$value;
                    if($groupId <= 0 || in_array($groupId, $protectedGroupIds, true))
                    {
                        continue;
                    }
                    $groupRow = (array)$this->Ugroup->find('first', array('fields'=>array('Ugroup.id','Ugroup.name'),'conditions'=>array('Ugroup.id'=>$groupId),'recursive'=>-1));
                    if(empty($groupRow['Ugroup']['id']))
                    {
                        continue;
                    }
                    if($this->User->find('count', array('conditions'=>array('User.ugroup_id'=>$groupId,'User.deleted IS NULL'),'recursive'=>-1)) > 0)
                    {
                        $blockedNames[] = (string)$groupRow['Ugroup']['name'];
                        continue;
                    }
                    $this->PageRight->deleteAll(array('PageRight.ugroup_id'=>$groupId), false);
                    if($this->Ugroup->delete($groupId, false))
                    {
                        $deletedCount++;
                    }
                }
		$db->commit();
                if(!empty($blockedNames))
                {
                    $this->Session->setFlash(__('Some levels were not deleted because users are assigned: ').implode(', ',array_unique($blockedNames)),'flash',array('alert'=>'warning'));
                }
                elseif($deletedCount > 0)
                {
		    $this->Session->setFlash(__('Level user has been deleted'),'flash',array('alert'=>'success'));
                }
            }
            catch (Exception $e)
	    {
		$db->rollback();
		$this->Session->setFlash(__('Can not delete level user'),'flash',array('alert'=>'danger'));
		return $this->redirect(array('action' => 'assignrights'));
	    }
        }        
        $this->redirect(array('action' => 'assignrights'));
    }
    public function view($id = null)
    {
        $this->layout = null;
        if (!$id)
        {
            $this->Session->setFlash(__('Invalid Post.'),'flash',array('alert'=>'danger'));
            $this->redirect(array('action' => 'index'));
        }
        $post = $this->User->findById($id);
        if(empty($post['User']['id']) || !$this->_canAccessManagedUser($post))
        {
            $this->Session->setFlash(__('Invalid Post.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        if(strlen($post['User']['photo'])>0)
        $std_img='user_thumb/'.$post['User']['photo'];
        else
        $std_img='User.png';
        $this->set('std_img', $std_img);
        $this->set('post',$post);
    }
}
