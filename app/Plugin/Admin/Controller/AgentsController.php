<?php
class AgentsController extends AdminAppController {
    public $helpers = array('Html', 'Form','Session','Paginator','Js'=> array('Jquery'));
    public $components = array('Session','Paginator','search-master.Prg');
    public $presetVars = true;
    var $paginate = array('page'=>1,'order'=>array('Agent.id'=>'desc'));
    protected function _walletBalanceMapForAgents($agentIds)
    {
        $agentIds = array_values(array_unique(array_filter(array_map('intval', (array)$agentIds))));
        if(empty($agentIds))
        {
            return array();
        }
        $idList = implode(',', $agentIds);
        $balanceMap = array();
        try
        {
            $walletRows = (array)$this->Agent->query(
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
                $agentId = !empty($walletRow['aw']['user_id']) ? (int)$walletRow['aw']['user_id'] : (!empty($walletRow[0]['user_id']) ? (int)$walletRow[0]['user_id'] : 0);
                if($agentId <= 0)
                {
                    continue;
                }
                $balance = isset($walletRow['aw']['balance']) ? (float)$walletRow['aw']['balance'] : (isset($walletRow[0]['balance']) ? (float)$walletRow[0]['balance'] : 0.0);
                $balanceMap[$agentId] = $balance;
            }
        }
        catch(Exception $e)
        {
        }
        try
        {
            $historyRows = (array)$this->Agent->query(
                "SELECT user_id,
                        SUM(CASE WHEN UPPER(TRIM(COALESCE(type,'')))='CR' THEN amount ELSE (-1 * amount) END) AS hist_balance
                 FROM awallet_histories
                 WHERE user_id IN (".$idList.")
                 GROUP BY user_id"
            );
            foreach($historyRows as $historyRow)
            {
                $agentId = !empty($historyRow['awallet_histories']['user_id']) ? (int)$historyRow['awallet_histories']['user_id'] : (!empty($historyRow[0]['user_id']) ? (int)$historyRow[0]['user_id'] : 0);
                if($agentId <= 0 || isset($balanceMap[$agentId]))
                {
                    continue;
                }
                $balance = isset($historyRow[0]['hist_balance']) ? (float)$historyRow[0]['hist_balance'] : (isset($historyRow['awallet_histories']['hist_balance']) ? (float)$historyRow['awallet_histories']['hist_balance'] : 0.0);
                $balanceMap[$agentId] = $balance;
            }
        }
        catch(Exception $e)
        {
        }
        return $balanceMap;
    }
    protected function _ensureFrontViewColumn()
    {
        if($this->Agent->hasField('front_view'))
        {
            return true;
        }
        try
        {
            $this->Agent->query("ALTER TABLE `users` ADD `front_view` VARCHAR(3) NOT NULL DEFAULT 'On' AFTER `status`");
            $this->Agent->schema(true);
        }
        catch(Exception $e)
        {
            $this->Agent->schema(true);
        }
        return $this->Agent->hasField('front_view');
    }
    protected function _ensureSortIdColumn()
    {
        $this->Agent->schema(true);
        if($this->Agent->hasField('sort_id'))
        {
            return true;
        }
        $afterColumn = $this->_ensureFrontViewColumn() ? 'front_view' : 'status';
        try
        {
            $this->Agent->query("ALTER TABLE `users` ADD `sort_id` INT(11) NOT NULL DEFAULT 0 AFTER `".$afterColumn."`");
            $this->Agent->schema(true);
        }
        catch(Exception $e)
        {
            $this->Agent->schema(true);
        }
        return $this->Agent->hasField('sort_id');
    }
    protected function _ensureLoginStatusColumn()
    {
        $this->Agent->schema(true);
        if($this->Agent->hasField('login_status'))
        {
            return true;
        }
        try
        {
            $this->Agent->query("ALTER TABLE `users` ADD `login_status` VARCHAR(20) NOT NULL DEFAULT 'Active' AFTER `status`");
            $this->Agent->schema(true);
        }
        catch(Exception $e)
        {
            $this->Agent->schema(true);
        }
        return $this->Agent->hasField('login_status');
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
    protected function _agentSortOrder($alias='Agent')
    {
        $alias = preg_replace('/[^A-Za-z0-9_]/', '', (string)$alias);
        if($alias === '')
        {
            $alias = 'Agent';
        }
        return array(
            '(CASE WHEN '.$alias.'.sort_id > 0 THEN 0 ELSE 1 END)' => 'asc',
            $alias.'.sort_id' => 'asc',
            $alias.'.name' => 'asc',
            $alias.'.id' => 'asc'
        );
    }
    protected function _agentRoleIds()
    {
        $this->loadModel('Ugroup');
        try
        {
            $columns = (array)$this->Ugroup->query("SHOW COLUMNS FROM `ugroups` LIKE 'role_category'");
            $conditions = empty($columns)
                ? array('Ugroup.type' => 'AGT')
                : array('OR' => array('Ugroup.type' => 'AGT', 'Ugroup.role_category' => 'agent'));
            $roles = (array)$this->Ugroup->find('list', array(
                'fields' => array('Ugroup.id','Ugroup.id'),
                'conditions' => $conditions,
                'recursive' => -1
            ));
            $roleIds = array_values(array_unique(array_filter(array_map('intval', array_keys($roles)))));
            return !empty($roleIds) ? $roleIds : array(2);
        }
        catch(Exception $e)
        {
            return array(2);
        }
    }
    public function index($id=null)
    {
        $hasSortIdColumn = $this->_ensureSortIdColumn();
        $this->set('hasSortIdColumn',$hasSortIdColumn);
        $hasLoginStatusColumn = $this->_ensureLoginStatusColumn();
        $this->set('hasLoginStatusColumn',$hasLoginStatusColumn);
        $conditions=array('Agent.ugroup_id'=>$this->_agentRoleIds());
        $this->loadModel('Admin.User');
        $this->loadModel('Project');
        $selectedAgencyId = 0;
        if(strtoupper(trim((string)$this->userType)) === 'ADR')
        {
            $selectedAgencyId = (int)$id;
        }
        else
        {
            $selectedAgencyId = (int)$this->agencyId;
        }
        if($selectedAgencyId > 0)
        {
            $conditions[] = $this->User->projectMembershipCondition($selectedAgencyId, 'Agent');
        }
        $this->Prg->commonProcess();
        $this->Paginator->settings = $this->paginate;
        if($hasSortIdColumn)
        {
            $this->Paginator->settings['order']=$this->_agentSortOrder('Agent');
        }
        $this->Paginator->settings['conditions'] = array($this->Agent->parseCriteria($this->Prg->parsedParams()),$conditions);
        $this->Paginator->settings['limit']=$this->pageLimit;
        $this->Paginator->settings['maxLimit']=$this->maxLimit;
        $agents = $this->Paginator->paginate();
        $agentIds = array();
        foreach((array)$agents as $agentRow)
        {
            if(!empty($agentRow['Agent']['id']))
            {
                $agentIds[] = (int)$agentRow['Agent']['id'];
            }
        }
        $agentAgencyNames = array();
        if(!empty($agentIds) && method_exists($this->User,'assignedProjectNameMap'))
        {
            $agentAgencyNames = (array)$this->User->assignedProjectNameMap($agentIds);
        }
        $projectNameMap = array();
        try
        {
            $projectNameMap = (array)$this->Project->find('list', array(
                'fields' => array('Project.id','Project.name'),
                'recursive' => -1
            ));
        }
        catch(Exception $e)
        {
            $projectNameMap = array();
        }
        foreach((array)$agents as $agentIndex => $agentRow)
        {
            $agentId = !empty($agentRow['Agent']['id']) ? (int)$agentRow['Agent']['id'] : 0;
            $primaryProjectId = !empty($agentRow['Agent']['project_id']) ? (int)$agentRow['Agent']['project_id'] : 0;
            if($hasSortIdColumn && !isset($agents[$agentIndex]['Agent']['sort_id']))
            {
                $agents[$agentIndex]['Agent']['sort_id'] = 0;
            }
            if(!isset($agents[$agentIndex]['Agent']['login_status']) || trim((string)$agents[$agentIndex]['Agent']['login_status']) === '')
            {
                $agents[$agentIndex]['Agent']['login_status'] = 'Active';
            }
            if($agentId <= 0)
            {
                continue;
            }
            if((empty($agentAgencyNames[$agentId]) || trim((string)$agentAgencyNames[$agentId]) === '') && $primaryProjectId > 0 && !empty($projectNameMap[$primaryProjectId]))
            {
                $agentAgencyNames[$agentId] = $projectNameMap[$primaryProjectId];
            }
        }
        $this->set('agentWalletBalances', $this->_walletBalanceMapForAgents($agentIds));
	$this->set('agentAgencyNames', $agentAgencyNames);
	$this->set('Agent', $agents);  
	if ($this->request->is('ajax'))
        {
            $this->render('index','ajax'); // View, Layout
        }
    }    
    public function add()
    {
        $hasFrontViewColumn = $this->_ensureFrontViewColumn();
        $hasSortIdColumn = $this->_ensureSortIdColumn();
        $hasLoginStatusColumn = $this->_ensureLoginStatusColumn();
        $this->set('hasFrontViewColumn',$hasFrontViewColumn);
        $this->set('hasSortIdColumn',$hasSortIdColumn);
        $this->set('hasLoginStatusColumn',$hasLoginStatusColumn);
        $this->set('frontViewOptions',array('On'=>__('On'),'Off'=>__('Off')));
        $this->set('loginStatusOptions',array('Active'=>__('Active'),'Blocked'=>__('Blocked')));
        if ($this->request->is('post'))
        {
            $this->Agent->create();
            $this->Agent->unbindValidation('remove', array('amount','action','remarks'), true);
            try
            {
                if($hasFrontViewColumn)
                {
                    $frontView = isset($this->request->data['Agent']['front_view']) ? $this->request->data['Agent']['front_view'] : 'On';
                    $this->request->data['Agent']['front_view']=$this->_normalizeFrontViewValue($frontView);
                }
                else
                {
                    unset($this->request->data['Agent']['front_view']);
                }
                if($hasSortIdColumn)
                {
                    $sortId = isset($this->request->data['Agent']['sort_id']) ? $this->request->data['Agent']['sort_id'] : 0;
                    $this->request->data['Agent']['sort_id']=$this->_normalizeSortIdValue($sortId);
                }
                else
                {
                    unset($this->request->data['Agent']['sort_id']);
                }
                if($hasLoginStatusColumn)
                {
                    $loginStatus = isset($this->request->data['Agent']['login_status']) ? $this->request->data['Agent']['login_status'] : 'Active';
                    $this->request->data['Agent']['login_status']=$this->_normalizeLoginStatusValue($loginStatus);
                }
                else
                {
                    unset($this->request->data['Agent']['login_status']);
                }
                if ($this->Agent->save($this->request->data))
                {
                    $this->Session->setFlash(__('Agent has been saved.'),'flash',array('alert'=>'success'));
                    return $this->redirect(array('action' => 'add'));
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
            $this->request->data['Agent']['front_view']='On';
        }
        if(!$this->request->is('post') && $hasSortIdColumn)
        {
            $this->request->data['Agent']['sort_id']=0;
        }
        if(!$this->request->is('post') && $hasLoginStatusColumn)
        {
            $this->request->data['Agent']['login_status']='Active';
        }
    }
    public function edit($id = null)
    {
        $hasFrontViewColumn = $this->_ensureFrontViewColumn();
        $hasSortIdColumn = $this->_ensureSortIdColumn();
        $hasLoginStatusColumn = $this->_ensureLoginStatusColumn();
        $this->set('hasFrontViewColumn',$hasFrontViewColumn);
        $this->set('hasSortIdColumn',$hasSortIdColumn);
        $this->set('hasLoginStatusColumn',$hasLoginStatusColumn);
        $this->set('frontViewOptions',array('On'=>__('On'),'Off'=>__('Off')));
        $this->set('loginStatusOptions',array('Active'=>__('Active'),'Blocked'=>__('Blocked')));
        if (!$id)
        {
            throw new NotFoundException(__('Invalid post'));
        }
        $ids=explode(",",$id);
        $post=array();
        foreach($ids as $k=>$id)
        {
	    $k++;
            $post[$k]=$this->Agent->findByid($id);
            if($hasFrontViewColumn && (!isset($post[$k]['Agent']['front_view']) || strlen(trim((string)$post[$k]['Agent']['front_view']))<=0))
            {
                $post[$k]['Agent']['front_view']='On';
            }
            if($hasSortIdColumn && (!isset($post[$k]['Agent']['sort_id']) || strlen(trim((string)$post[$k]['Agent']['sort_id']))<=0))
            {
                $post[$k]['Agent']['sort_id']=0;
            }
            if($hasLoginStatusColumn && (!isset($post[$k]['Agent']['login_status']) || strlen(trim((string)$post[$k]['Agent']['login_status']))<=0))
            {
                $post[$k]['Agent']['login_status']='Active';
            }
        }
        $this->set('Agent',$post);
        if (!$post)
        {
            throw new NotFoundException(__('Invalid post'));
        }
        $this->Agent->unbindValidation('remove', array('amount','action','remarks'), true);
        if ($this->request->is(array('post', 'put')))
        {
            try
            {
                if($hasFrontViewColumn)
                {
                    foreach($this->request->data as $key=>$value)
                    {
                        $frontView = isset($this->request->data[$key]['Agent']['front_view']) ? $this->request->data[$key]['Agent']['front_view'] : 'On';
                        $this->request->data[$key]['Agent']['front_view']=$this->_normalizeFrontViewValue($frontView);
                    }
                }
                else
                {
                    foreach($this->request->data as $key=>$value)
                    {
                        unset($this->request->data[$key]['Agent']['front_view']);
                    }
                }
                if($hasSortIdColumn)
                {
                    foreach($this->request->data as $key=>$value)
                    {
                        $sortId = isset($this->request->data[$key]['Agent']['sort_id']) ? $this->request->data[$key]['Agent']['sort_id'] : 0;
                        $this->request->data[$key]['Agent']['sort_id']=$this->_normalizeSortIdValue($sortId);
                    }
                }
                else
                {
                    foreach($this->request->data as $key=>$value)
                    {
                        unset($this->request->data[$key]['Agent']['sort_id']);
                    }
                }
                if($hasLoginStatusColumn)
                {
                    foreach($this->request->data as $key=>$value)
                    {
                        if(isset($this->request->data[$key]['Agent']['login_status']))
                        {
                            $this->request->data[$key]['Agent']['login_status']=$this->_normalizeLoginStatusValue($this->request->data[$key]['Agent']['login_status']);
                        }
                    }
                }
                else
                {
                    foreach($this->request->data as $key=>$value)
                    {
                        unset($this->request->data[$key]['Agent']['login_status']);
                    }
                }
                if ($this->Agent->saveAll($this->request->data))
                {
                    $this->Session->setFlash(__('Agent has been saved.'),'flash',array('alert'=>'success'));
                    return $this->redirect(array('action' => 'index'));
                }
            }
            catch (Exception $e)
            {
                $this->Session->setFlash(__('Invalid Post.'),'flash',array('alert'=>'danger'));
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
    public function deleteall()
    {
        try
        {
            if ($this->request->is('post'))
            {
                foreach($this->data['Agent']['id'] as $key => $value)
                {
                    $this->Agent->delete($value);
                }
                $this->Session->setFlash(__('Agent has been deleted.'),'flash',array('alert'=>'danger'));
            }        
            $this->redirect(array('action' => 'index'));
        }
        catch (Exception $e)
        {
            $this->Session->setFlash(__('Please delete related record first.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'index'));
        }
    }
    public function wallet($id = null)
    {
        $this->layout = null;
        $ids=explode(",",$id);
        $post=array();
        foreach($ids as $id)
        {
            $post[]=$this->Agent->findById($id);
        }
        $this->set('Agent',$post);
        if (!$post)
        {
            throw new NotFoundException(__('Invalid post'));
        }
        if ($this->request->is(array('post', 'put')))
        {
            try
            {
                $this->Agent->unbindValidation('keep', array('amount','action','remarks'), true);
                foreach($this->request->data as $value)
                {
                    if($this->CustomFunction->awalletInsert($value['Agent']['id'],$value['Agent']['amount'],$value['Agent']['action'],$value['Agent']['remarks']))
                    {
                        $this->Session->setFlash(__('Agent Wallet has been updated.'),'flash',array('alert'=>'success'));
                    }
                    else
                    {
                        $this->Session->setFlash(__('Invalid Amount.'),'flash',array('alert'=>'danger'));
                    }
                }                
                return $this->redirect(array('action' => 'index'));
            }
            catch (Exception $e)
            {
                $this->Session->setFlash(__('Agent record not found.'),'flash',array('alert'=>'danger'));
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
    public function view($id = null)
    {
        $this->layout = null;
        if (!$id)
        {
            $this->Session->setFlash(__('Invalid Post.'),'flash',array('alert'=>'danger'));
            $this->redirect(array('action' => 'index'));
        }
        $post = $this->Agent->findById($id);
        if(strlen($post['Agent']['photo'])>0)
        $std_img='user_thumb/'.$post['Agent']['photo'];
        else
        $std_img='User.png';
        $this->set('std_img', $std_img);
        $this->set('post',$post);
    }
    public function change_login_status($id = null, $status = null)
    {
        $this->_ensureLoginStatusColumn();
        $id = (int)$id;
        if($id <= 0)
        {
            $this->Session->setFlash(__('Invalid Agent.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }

        // Protection: Super Admin (ID 1) or currently logged in user cannot be blocked
        if($id === 1)
        {
            $this->Session->setFlash(__('Super Administrator login status cannot be modified.'), 'flash', array('alert' => 'warning'));
            return $this->redirect(array('action' => 'index'));
        }
        if($id === (int)$this->luserId && strtolower(trim((string)$status)) === 'blocked')
        {
            $this->Session->setFlash(__('You cannot block your own account login.'), 'flash', array('alert' => 'warning'));
            return $this->redirect(array('action' => 'index'));
        }

        $agent = $this->Agent->find('first', array(
            'conditions' => array('Agent.id' => $id, 'Agent.deleted IS NULL'),
            'recursive' => -1
        ));

        if(empty($agent['Agent']['id']))
        {
            $this->Session->setFlash(__('Agent not found.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }

        if(strtoupper(trim((string)$this->userType)) !== 'ADR' && empty($this->isSuperAdminSession))
        {
            $this->loadModel('Admin.User');
            $agencyId = (int)$this->agencyId;
            $hasAccess = false;
            if($agencyId > 0 && method_exists($this->User, 'projectMembershipCondition'))
            {
                $check = $this->Agent->find('first', array(
                    'conditions' => array(
                        'Agent.id' => $id,
                        $this->User->projectMembershipCondition($agencyId, 'Agent')
                    ),
                    'recursive' => -1
                ));
                if(!empty($check['Agent']['id']))
                {
                    $hasAccess = true;
                }
            }
            if(!$hasAccess)
            {
                $this->Session->setFlash(__('You do not have permission to modify this agent.'), 'flash', array('alert' => 'danger'));
                return $this->redirect(array('action' => 'index'));
            }
        }

        $newStatus = $this->_normalizeLoginStatusValue($status);
        $this->Agent->id = $id;
        if($this->Agent->saveField('login_status', $newStatus, array('validate' => false, 'callbacks' => false)))
        {
            if($newStatus === 'Blocked')
            {
                $this->Session->setFlash(__('Agent login has been blocked. This user cannot login to their account.'), 'flash', array('alert' => 'danger'));
            }
            else
            {
                $this->Session->setFlash(__('Agent login has been activated.'), 'flash', array('alert' => 'success'));
            }

            try
            {
                $this->_writeAuditEntry(array(
                    'user_id' => $this->luserId,
                    'ugroup_id' => isset($this->adminValue['User']['ugroup_id']) ? (int)$this->adminValue['User']['ugroup_id'] : null,
                    'project_id' => isset($this->adminValue['User']['project_id']) ? (int)$this->adminValue['User']['project_id'] : null,
                    'controller' => 'agents',
                    'action' => 'change_login_status',
                    'activity' => ($newStatus === 'Blocked') ? 'agent_login_blocked' : 'agent_login_activated',
                    'method' => $this->request->method(),
                    'url' => $this->request->here(false),
                    'ip_address' => $this->request->clientIp(),
                    'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? substr((string)$_SERVER['HTTP_USER_AGENT'], 0, 250) : '',
                    'request_data' => json_encode(array('agent_id' => $id, 'login_status' => $newStatus)),
                    'created' => date('Y-m-d H:i:s')
                ));
            }
            catch(Exception $e)
            {
            }
        }
        else
        {
            $this->Session->setFlash(__('Unable to update login status.'), 'flash', array('alert' => 'danger'));
        }

        return $this->redirect($this->referer(array('action' => 'index')));
    }
    public function loginas($id = null)
    {
        if(!$id)
        {
            $this->Session->setFlash(__('Invalid Post'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'index'));
        }

        $currentUser = (array)$this->Session->read('User');
        $isSuperAdmin = $this->_isSuperAdminSession($currentUser);
        $isAgencyAdmin = $this->_isAgencyAdminSession($currentUser);

        if(!$isSuperAdmin && !$isAgencyAdmin)
        {
            $this->Session->setFlash(__('You do not have permission to log in as an agent.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'index'));
        }

        $agent = $this->Agent->findById((int)$id);
        if(empty($agent['Agent']['id']))
        {
            $this->Session->setFlash(__('Agent not found or inactive.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'index'));
        }

        if(!empty($agent['Agent']['login_status']) && strcasecmp(trim((string)$agent['Agent']['login_status']), 'Blocked') === 0)
        {
            $this->Session->setFlash(__('Login is blocked for this agent.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'index'));
        }

        return $this->redirect(array('controller' => 'Users', 'action' => 'loginas', (int)$id));
    }
}
