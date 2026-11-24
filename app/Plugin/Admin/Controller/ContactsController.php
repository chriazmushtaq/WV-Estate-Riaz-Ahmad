<?php
App::uses('SimplePasswordHasher', 'Controller/Component/Auth');
App::uses('CakeEmail', 'Network/Email');
App::uses('CakeSession', 'Model/Datasource');
@ini_set('memory_limit', '512M');
@ini_set('max_execution_time', 900);
class ContactsController extends AdminAppController {
    public $uses = array('Admin.Contact');
    public $helpers = array('Html', 'Form','Session','Paginator','Js'=> array('Jquery'), 'MenuBuilder.MenuBuilder');
    public $components = array('Session','Paginator','search-master.Prg');
    public $presetVars = true;
    var $paginate = array('page'=>1,'order'=>array('Contact.id'=>'desc'));
    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->loadModel('Admin.Contact');
        $this->loadModel('Admin.Addresshierarchy');
        $this->Addresshierarchy->ensureSchema();
        $this->Addresshierarchy->ensureTargetColumns('clients');
        try { $this->Contact->schema(true); } catch(Throwable $e) {}
        if($this->_shouldRunControllerBootstrap('contacts.schema', '2026-11-21.1'))
        {
            $this->_ensureContactMediaColumns();
        }
        $this->_ensureContactUploadDirectories();
    }
    protected function _setAddressHierarchyVars()
    {
        $this->loadModel('Admin.Addresshierarchy');
        $this->Addresshierarchy->ensureSchema();
        $this->set('addressHierarchyTree', $this->Addresshierarchy->treePayload());
        $this->set('addressCountryOptions', $this->Addresshierarchy->levelOptions('country', 0));
        $this->set('addressLevelOrder', $this->Addresshierarchy->levelOrder);
    }
    protected function _normalizeContactAddressHierarchy(&$contactData)
    {
        $this->loadModel('Admin.Addresshierarchy');
        $this->Addresshierarchy->ensureSchema();
        $this->Addresshierarchy->normalizeHierarchyData($contactData);
    }
    protected function _ensureContactMediaColumns()
    {
        $columns = array(
            'fathername' => "varchar(250) DEFAULT NULL",
            'relation_type' => "varchar(50) DEFAULT 'Father'",
            'gender' => "varchar(20) DEFAULT NULL",
            'photo' => "varchar(255) DEFAULT NULL",
            'dirt' => "varchar(255) DEFAULT NULL",
            'id_front' => "varchar(255) DEFAULT NULL",
            'id_front_dir' => "varchar(255) DEFAULT NULL",
            'id_back' => "varchar(255) DEFAULT NULL",
            'id_back_dir' => "varchar(255) DEFAULT NULL",
            'side_photo' => "varchar(255) DEFAULT NULL",
            'side_photo_dir' => "varchar(255) DEFAULT NULL",
            'app_login_status' => "tinyint(1) DEFAULT 0",
            'app_last_login' => "datetime DEFAULT NULL"
        );
        foreach($columns as $columnName => $columnSql)
        {
            try
            {
                $exists = $this->Contact->query("SHOW COLUMNS FROM `clients` LIKE '".addslashes($columnName)."'");
                if(empty($exists))
                {
                    $this->Contact->query("ALTER TABLE `clients` ADD `{$columnName}` {$columnSql}");
                }
            }
            catch (Exception $e)
            {
                // Keep flow running even if ALTER fails in restrictive DB permissions.
            }
        }
    }
    protected function _setContactImageDirs(&$contactData)
    {
        if(!is_array($contactData))
        {
            return;
        }
        $contactData['dirt'] = 'contact';
        $contactData['id_front_dir'] = 'contact';
        $contactData['id_back_dir'] = 'contact';
        $contactData['side_photo_dir'] = 'contact';
    }
    protected function _ensureContactUploadDirectories()
    {
        $dirs = array(
            WWW_ROOT . 'img' . DS . 'contact',
            WWW_ROOT . 'img' . DS . 'contact_thumb',
            TMP . 'uploads'
        );
        foreach ($dirs as $dir) {
            if (!is_dir($dir)) {
                @mkdir($dir, 0777, true);
                @chmod($dir, 0777);
            }
        }
    }
    protected function _linkedLeadRowsForContact($contactId)
    {
        $contactId = (int)$contactId;
        if($contactId <= 0)
        {
            return array();
        }
        $this->loadModel('Admin.Lead');
        $conditions = array('Lead.client_id' => $contactId);
        if($this->userType == 'AGT')
        {
            $conditions['Lead.user_id'] = (int)$this->luserId;
        }
        elseif($this->userType == 'AGY')
        {
            $conditions['Lead.project_id'] = (int)$this->agencyId;
        }
        return (array)$this->Lead->find('all', array(
            'fields' => array('Lead.id','Lead.lead_name','Lead.lead_mobile','Lead.project_id','Lead.user_id','Lead.client_id','Lead.status_id','Lead.follow_up','Status.name'),
            'conditions' => $conditions,
            'joins' => array(
                array('table' => 'statuses', 'alias' => 'Status', 'type' => 'LEFT', 'conditions' => array('Status.id = Lead.status_id'))
            ),
            'order' => array('Lead.id' => 'desc'),
            'recursive' => -1
        ));
    }
    protected function _linkedLeadMapForContacts($contacts)
    {
        $linkedLeadMap = array();
        foreach((array)$contacts as $contactRow)
        {
            $contactId = !empty($contactRow['Contact']['id']) ? (int)$contactRow['Contact']['id'] : 0;
            if($contactId > 0)
            {
                $linkedLeadMap[$contactId] = $this->_linkedLeadRowsForContact($contactId);
            }
        }
        return $linkedLeadMap;
    }
    protected function _dealCountMapForContacts($contacts)
    {
        $contactIds = array();
        foreach((array)$contacts as $contactRow)
        {
            if(!empty($contactRow['Contact']['id']))
            {
                $contactIds[] = (int)$contactRow['Contact']['id'];
            }
        }
        $contactIds = array_values(array_unique(array_filter($contactIds)));
        $dealCountMap = array();
        foreach($contactIds as $contactId)
        {
            $dealCountMap[$contactId] = 0;
        }
        if(empty($contactIds))
        {
            return $dealCountMap;
        }
        $this->loadModel('Admin.Deal');
        $conditions = array('Deal.client_id' => $contactIds);
        $dealRows = (array)$this->Deal->find('all', array(
            'fields' => array('Deal.client_id', 'COUNT(Deal.id) AS deal_count'),
            'conditions' => $conditions,
            'group' => array('Deal.client_id'),
            'recursive' => -1
        ));
        foreach($dealRows as $dealRow)
        {
            $clientId = !empty($dealRow['Deal']['client_id']) ? (int)$dealRow['Deal']['client_id'] : 0;
            $dealCount = isset($dealRow[0]['deal_count']) ? (int)$dealRow[0]['deal_count'] : 0;
            if($clientId > 0)
            {
                $dealCountMap[$clientId] = $dealCount;
            }
        }
        return $dealCountMap;
    }
    protected function _contactHasLinkedDeal($contactId)
    {
        $contactId = (int)$contactId;
        if($contactId <= 0)
        {
            return false;
        }
        $this->loadModel('Admin.Deal');
        return ((int)$this->Deal->find('count', array(
            'conditions' => array('Deal.client_id' => $contactId),
            'recursive' => -1
        )) > 0);
    }
    protected function _defaultLeadStatusIdForContact()
    {
        $this->loadModel('Admin.Status');
        $row = $this->Status->find('first', array(
            'fields' => array('Status.id'),
            'order' => array('Status.id' => 'asc'),
            'recursive' => -1
        ));
        return !empty($row['Status']['id']) ? (int)$row['Status']['id'] : 1;
    }
    protected function _createLeadFromContact($contactId)
    {
        $contactId = (int)$contactId;
        if($contactId <= 0 || $this->_contactHasLinkedDeal($contactId))
        {
            return 0;
        }
        $contactRow = $this->Contact->find('first', array(
            'conditions' => array('Contact.id' => $contactId),
            'recursive' => -1
        ));
        if(empty($contactRow['Contact']['id']))
        {
            return 0;
        }
        $this->loadModel('Admin.Lead');
        $existingLeadCount = (int)$this->Lead->find('count', array(
            'conditions' => array('Lead.client_id' => $contactId),
            'recursive' => -1
        ));
        if($existingLeadCount > 0)
        {
            return 0;
        }
        $contact = (array)$contactRow['Contact'];
        $projectId = !empty($contact['project_id']) ? (int)$contact['project_id'] : (int)$this->agencyId;
        $userId = !empty($contact['user_id']) ? (int)$contact['user_id'] : (int)$this->luserId;
        $leadMobile = trim((string)(!empty($contact['mobile']) ? $contact['mobile'] : (!empty($contact['phone']) ? $contact['phone'] : '')));
        $leadData = array(
            'project_id' => $projectId,
            'user_id' => $userId,
            'client_id' => null,
            'status_id' => $this->_defaultLeadStatusIdForContact(),
            'lead_name' => trim((string)(isset($contact['name']) ? $contact['name'] : '')),
            'lead_mobile' => $leadMobile,
            'lead_email' => trim((string)(isset($contact['email']) ? $contact['email'] : '')),
            'lead_address' => trim((string)(isset($contact['address']) ? $contact['address'] : '')),
            'lead_city' => trim((string)(isset($contact['district']) ? $contact['district'] : '')),
            'lead_source' => __('Changed Back From Contact'),
            'remarks' => __('Created from customer contact').' #'.$contactId
        );
        if($leadData['lead_name'] === '' && $leadData['lead_mobile'] === '')
        {
            return 0;
        }
        $this->Lead->create();
        if($this->Lead->save(array('Lead' => $leadData), array('validate' => false)))
        {
            $leadId = (int)$this->Lead->id;
            $this->_logLeadContactCreatedFromContact($leadId, $contactRow);
            return $leadId;
        }
        return 0;
    }
    protected function _removeContactAfterLeadMove($contactId)
    {
        $contactId = (int)$contactId;
        if($contactId <= 0 || $this->_contactHasLinkedDeal($contactId))
        {
            return false;
        }
        $this->loadModel('Admin.Lead');
        $remainingLeadCount = (int)$this->Lead->find('count', array(
            'conditions' => array('Lead.client_id' => $contactId),
            'recursive' => -1
        ));
        if($remainingLeadCount > 0)
        {
            return false;
        }
        try
        {
            return (bool)$this->Contact->delete($contactId, false);
        }
        catch(Exception $e)
        {
            CakeLog::write('error', 'Contact remove after lead move failed for #'.$contactId.': '.$e->getMessage());
        }
        return false;
    }
    protected function _logLeadContactCreatedFromContact($leadId, $contactRow)
    {
        $leadId = (int)$leadId;
        if($leadId <= 0 || empty($contactRow['Contact']))
        {
            return;
        }
        try
        {
            $this->loadModel('LeadHistory');
            if(method_exists($this->LeadHistory, 'ensureSchema'))
            {
                $this->LeadHistory->ensureSchema();
            }
            if(!method_exists($this->LeadHistory, 'createEntry'))
            {
                return;
            }
            $contact = (array)$contactRow['Contact'];
            $this->LeadHistory->createEntry(array_merge($this->_contactLeadHistoryActorContext(), array(
                'lead_id' => $leadId,
                'project_id' => !empty($contact['project_id']) ? (int)$contact['project_id'] : 0,
                'lead_user_id' => !empty($contact['user_id']) ? (int)$contact['user_id'] : 0,
                'entry_type' => 'create',
                'title' => __('Changed Back To Lead Contact'),
                'message_body' => __('Lead contact created from customer contact').' #'.(int)$contact['id'],
                'meta_json' => array('source_action' => 'Contacts/edit', 'contact_id' => (int)$contact['id'])
            )));
        }
        catch(Exception $e)
        {
            CakeLog::write('error', 'Contact to lead history failed: '.$e->getMessage());
        }
    }
    protected function _contactLeadHistoryActorContext()
    {
        $sessionUser = (array)$this->Session->read('User');
        $actorUserId = !empty($this->adminValue['User']['id']) ? (int)$this->adminValue['User']['id'] : (!empty($sessionUser['User']['id']) ? (int)$sessionUser['User']['id'] : 0);
        $actorName = !empty($this->adminValue['User']['name']) ? trim((string)$this->adminValue['User']['name']) : trim((string)(!empty($sessionUser['User']['name']) ? $sessionUser['User']['name'] : ''));
        if($actorName === '')
        {
            $actorName = !empty($this->adminValue['User']['username']) ? trim((string)$this->adminValue['User']['username']) : trim((string)(!empty($sessionUser['User']['username']) ? $sessionUser['User']['username'] : 'System'));
        }
        $actorRole = !empty($this->adminValue['Ugroup']['name']) ? trim((string)$this->adminValue['Ugroup']['name']) : trim((string)(!empty($sessionUser['Ugroup']['name']) ? $sessionUser['Ugroup']['name'] : ''));
        return array(
            'actor_user_id' => $actorUserId,
            'actor_name' => $actorName,
            'actor_role' => $actorRole
        );
    }
    protected function _logLeadContactUnlink($leadRow, $contactId, $contactName)
    {
        if(empty($leadRow['Lead']['id']))
        {
            return;
        }
        try
        {
            $this->loadModel('LeadHistory');
            if(method_exists($this->LeadHistory, 'ensureSchema'))
            {
                $this->LeadHistory->ensureSchema();
            }
            if(!method_exists($this->LeadHistory, 'createEntry'))
            {
                return;
            }
            $oldValue = trim((string)$contactName);
            if($oldValue === '')
            {
                $oldValue = __('Contact');
            }
            $oldValue .= ' #'.(int)$contactId;
            $this->LeadHistory->createEntry(array_merge($this->_contactLeadHistoryActorContext(), array(
                'lead_id' => (int)$leadRow['Lead']['id'],
                'project_id' => !empty($leadRow['Lead']['project_id']) ? (int)$leadRow['Lead']['project_id'] : 0,
                'lead_user_id' => !empty($leadRow['Lead']['user_id']) ? (int)$leadRow['Lead']['user_id'] : 0,
                'entry_type' => 'change',
                'title' => __('Changed Back To Lead Contact'),
                'field_name' => 'client_id',
                'field_label' => __('Client'),
                'old_value' => $oldValue,
                'new_value' => __('Lead Contact'),
                'message_body' => __('Customer contact link was removed from this lead.'),
                'meta_json' => array('source_action' => 'Contacts/edit', 'contact_id' => (int)$contactId)
            )));
        }
        catch(Exception $e)
        {
            CakeLog::write('error', 'Contact lead unlink history failed: '.$e->getMessage());
        }
    }
    protected function _unlinkContactLinkedLeads($contactId, $selectedLeadIds = array())
    {
        $contactId = (int)$contactId;
        if($contactId <= 0)
        {
            return 0;
        }
        if($this->_contactHasLinkedDeal($contactId))
        {
            return 0;
        }
        $leadRows = $this->_linkedLeadRowsForContact($contactId);
        if(empty($leadRows))
        {
            return 0;
        }
        $selectedLeadIds = array_filter(array_map('intval', (array)$selectedLeadIds));
        if(!empty($selectedLeadIds))
        {
            $selectedLeadIdMap = array_flip($selectedLeadIds);
            $filteredRows = array();
            foreach($leadRows as $leadRow)
            {
                $leadId = !empty($leadRow['Lead']['id']) ? (int)$leadRow['Lead']['id'] : 0;
                if($leadId > 0 && isset($selectedLeadIdMap[$leadId]))
                {
                    $filteredRows[] = $leadRow;
                }
            }
            $leadRows = $filteredRows;
            if(empty($leadRows))
            {
                return 0;
            }
        }
        $leadIds = array();
        foreach($leadRows as $leadRow)
        {
            if(!empty($leadRow['Lead']['id']))
            {
                $leadIds[] = (int)$leadRow['Lead']['id'];
            }
        }
        $leadIds = array_values(array_unique($leadIds));
        if(empty($leadIds))
        {
            return 0;
        }
        $contactName = (string)$this->Contact->field('name', array('Contact.id' => $contactId));
        $this->Lead->updateAll(array('Lead.client_id' => 'NULL'), array('Lead.id' => $leadIds));
        foreach($leadRows as $leadRow)
        {
            $this->_logLeadContactUnlink($leadRow, $contactId, $contactName);
        }
        return count($leadIds);
    }
    protected function _eligibleContactIds()
    {
        $this->loadModel('Admin.Deal');
        $dealClientIds = (array)$this->Deal->find('list', array(
            'fields' => array('Deal.client_id', 'Deal.client_id'),
            'conditions' => array(
                'Deal.client_id >' => 0,
                'OR' => array(
                    'Deal.deal_status IS NULL',
                    'Deal.deal_status !=' => 'Cancelled'
                )
            ),
            'recursive' => -1
        ));
        $dealClientIds = array_values(array_unique(array_filter(array_map('intval', $dealClientIds))));

        $billingClientIds = array();
        try {
            $billingRows = (array)$this->Contact->query("SELECT DISTINCT app_client_id FROM billing_clients WHERE app_client_id > 0");
            foreach ($billingRows as $bRow) {
                $cid = 0;
                if (!empty($bRow['billing_clients']['app_client_id'])) {
                    $cid = (int)$bRow['billing_clients']['app_client_id'];
                } elseif (!empty($bRow[0]['app_client_id'])) {
                    $cid = (int)$bRow[0]['app_client_id'];
                }
                if ($cid > 0) {
                    $billingClientIds[] = $cid;
                }
            }
        } catch (Exception $e) {
            // Ignore if billing table is missing in some environments
        }

        return array_values(array_unique(array_merge($dealClientIds, $billingClientIds)));
    }

    protected function _contactListConditions($id = null)
    {
        $conditions = array(
            'NOT' => array('Contact.reg_status' => 'Lead'),
            'EXISTS (SELECT 1 FROM deals AS Deal WHERE Deal.client_id = Contact.id)'
        );

        $agencyIdFilter = 0;
        if($id !== null && is_numeric($id) && (int)$id > 0)
        {
            $agencyIdFilter = (int)$id;
        }

        $isSuperAdmin = ($this->userType === 'ADR' || $this->_isSuperAdminSession((array)$this->adminValue));

        if($isSuperAdmin)
        {
            // Super Admin can view all contacts, optionally filtered by agency
            if($agencyIdFilter > 0)
            {
                $conditions['Contact.project_id'] = $agencyIdFilter;
            }
        }
        elseif($this->userType === 'AGY' || $this->_isAgencyAdminSession() || $this->_isAgencySystemManagerRoleContext((array)$this->adminValue))
        {
            // Agency Admin sees all contacts belonging to their agency or created by them
            if(!empty($this->agencyId))
            {
                $conditions['OR'] = array(
                    'Contact.project_id' => (int)$this->agencyId,
                    'Contact.user_id' => (int)$this->luserId
                );
            }
        }
        elseif($this->userType === 'AGT')
        {
            // Agent sees contacts of their agency where they created the contact or have a deal
            if(!empty($this->agencyId))
            {
                $conditions['Contact.project_id'] = (int)$this->agencyId;
            }
            $this->loadModel('Admin.Deal');
            $dealClientIds = (array)$this->Deal->find('list', array(
                'fields' => array('Deal.client_id', 'Deal.client_id'),
                'conditions' => array(
                    'Deal.user_id' => (int)$this->luserId,
                    'Deal.client_id >' => 0,
                    'OR' => array(
                        'Deal.deal_status IS NULL',
                        'Deal.deal_status !=' => 'Cancelled'
                    )
                ),
                'recursive' => -1
            ));
            $dealClientIds = array_values(array_unique(array_filter(array_map('intval', $dealClientIds))));
            $conditions['OR'] = array(
                'Contact.user_id' => (int)$this->luserId,
                'Contact.id' => !empty($dealClientIds) ? $dealClientIds : array(0)
            );
        }
        else
        {
            // Other staff (Manager, CEO, CRM, etc.)
            if($agencyIdFilter > 0)
            {
                $conditions['Contact.project_id'] = $agencyIdFilter;
            }
            elseif(!empty($this->agencyId))
            {
                $conditions['Contact.project_id'] = (int)$this->agencyId;
            }
        }

        return $conditions;
    }

    protected function _isContactAccessAllowed($contact)
    {
        if(empty($contact['Contact']['id']))
        {
            return false;
        }
        $isSuperAdmin = ($this->userType === 'ADR' || $this->_isSuperAdminSession((array)$this->adminValue));
        if($isSuperAdmin)
        {
            return true;
        }

        $contactProjectId = !empty($contact['Contact']['project_id']) ? (int)$contact['Contact']['project_id'] : 0;
        $contactUserId = !empty($contact['Contact']['user_id']) ? (int)$contact['Contact']['user_id'] : 0;

        if($this->userType === 'AGY' || $this->_isAgencyAdminSession() || $this->_isAgencySystemManagerRoleContext((array)$this->adminValue))
        {
            if($contactProjectId > 0 && !empty($this->agencyId) && $contactProjectId === (int)$this->agencyId)
            {
                return true;
            }
            if($contactUserId > 0 && $contactUserId === (int)$this->luserId)
            {
                return true;
            }
            if($contactProjectId <= 0)
            {
                return true;
            }
            return false;
        }

        if($this->userType === 'AGT')
        {
            if(!empty($this->agencyId) && $contactProjectId > 0 && $contactProjectId !== (int)$this->agencyId)
            {
                return false;
            }
            if($contactUserId === (int)$this->luserId)
            {
                return true;
            }
            $this->loadModel('Admin.Deal');
            return ((int)$this->Deal->find('count', array(
                'conditions' => array('Deal.user_id' => (int)$this->luserId, 'Deal.client_id' => (int)$contact['Contact']['id']),
                'recursive' => -1
            )) > 0);
        }

        // Other roles (Manager, CRM, CEO, etc.):
        if(!empty($this->agencyId) && $contactProjectId > 0 && $contactProjectId !== (int)$this->agencyId)
        {
            return false;
        }
        return true;
    }

    public function index($id=null)
    {
        $condition = $this->_contactListConditions($id);
        $this->loadModel('Category');
        $this->set('category',$this->Category->find('list'));
        $this->Prg->commonProcess();
        $this->Paginator->settings = $this->paginate;
        $this->Paginator->settings['conditions'] = array($this->Contact->parseCriteria($this->Prg->parsedParams()),$condition);
        $contactPageLimit = $this->_contactPaginatorLimit();
        $this->Paginator->settings['limit']=$contactPageLimit;
        $this->Paginator->settings['maxLimit']=max((int)$this->maxLimit,$contactPageLimit,500);
        $this->set('Contact', $this->Paginator->paginate());  
        $this->set('luserId',$this->luserId);
        if ($this->request->is('ajax'))
        {
            $this->render('index','ajax'); // View, Layout
        }
    }
    protected function _contactPaginatorLimit()
    {
        $limit = 0;
        if(isset($this->request->query['limit']))
        {
            $limit = (int)$this->request->query['limit'];
        }
        elseif(isset($this->request->params['named']['limit']))
        {
            $limit = (int)$this->request->params['named']['limit'];
        }
        elseif(isset($this->passedArgs['limit']))
        {
            $limit = (int)$this->passedArgs['limit'];
        }
        elseif(isset($this->request->data['limit']))
        {
            $limit = (int)$this->request->data['limit'];
        }
        elseif(isset($this->request->data['Contact']['limit']))
        {
            $limit = (int)$this->request->data['Contact']['limit'];
        }
        if($limit<=0)
        {
            $limit = (int)$this->pageLimit;
        }
        $limit = max(1,min($limit,500));
        $this->request->params['named']['limit'] = $limit;
        $this->passedArgs['limit'] = $limit;
        return $limit;
    }
    public function add()
    {
        $this->loadModel('Category');
        $this->set('category',$this->Category->find('list',array('conditions'=>array('type'=>NULL))));
        $this->set('agency',$this->CustomFunction->agencyWiseList($this->userType,$this->agencyId));
        $this->_setAddressHierarchyVars();
        if ($this->request->is('post'))
        {
            $this->Contact->create();
            try
            {
                $password = rand();
                $this->request->data['Contact']['password'] = $this->_hashPassword($password);
		if($this->userType=='AGT' || $this->userType=='AGY'){
		    $this->request->data['Contact']['project_id']=$this->agencyId;		    
		}
		$this->request->data['Contact']['user_id']=$this->luserId;
                $this->_ensureContactUploadDirectories();
                // Support cropped images or base64 data for profile photo and CNIC images
                $uploadFields = array('photo', 'id_front', 'id_back', 'side_photo');
                foreach ($uploadFields as $uField) {
                    $dataKey = $uField . '_data';
                    if (!empty($this->request->data['Contact'][$dataKey])) {
                        $base64Data = (string)$this->request->data['Contact'][$dataKey];
                        if (preg_match('/^data:image\/(\w+);base64,(.+)$/', $base64Data, $bMatches)) {
                            $imgExt = strtolower($bMatches[1]);
                            if ($imgExt === 'jpeg') $imgExt = 'jpg';
                            $decoded = base64_decode($bMatches[2]);
                            if ($decoded !== false && strlen($decoded) > 0) {
                                $tmpDir = TMP . 'uploads' . DS;
                                if (!is_dir($tmpDir)) {
                                    @mkdir($tmpDir, 0777, true);
                                }
                                $tmpFileName = 'crop_' . $uField . '_' . time() . '_' . mt_rand(1000, 9999) . '.' . $imgExt;
                                $tmpFilePath = $tmpDir . $tmpFileName;
                                file_put_contents($tmpFilePath, $decoded);
                                $this->request->data['Contact'][$uField] = array(
                                    'name' => $tmpFileName,
                                    'type' => 'image/' . ($imgExt === 'jpg' ? 'jpeg' : $imgExt),
                                    'tmp_name' => $tmpFilePath,
                                    'error' => 0,
                                    'size' => filesize($tmpFilePath)
                                );
                            }
                        }
                        unset($this->request->data['Contact'][$dataKey]);
                    }
                }
                $this->_setContactImageDirs($this->request->data['Contact']);
                $this->_normalizeContactAddressHierarchy($this->request->data['Contact']);
                if ($this->Contact->save($this->request->data))
                {
                    // Automated WhatsApp, SMS, and Email Welcome with Login Credentials according to Agency Branding
                    $this->_dispatchContactRegistrationWelcome($this->Contact->id, $password, $this->request->data['Contact']);

                    $this->Session->setFlash(__('Your Contact Manager has been saved and registration credentials have been dispatched.'),'flash',array('alert'=>'success'));
                    return $this->redirect(array('controller'=>'Contacts','action'=>'index'));
                }
                $validationError='';
                foreach((array)$this->Contact->validationErrors as $fieldErrors)
                {
                    if(is_array($fieldErrors) && !empty($fieldErrors[0]))
                    {
                        $validationError=(string)$fieldErrors[0];
                        break;
                    }
                    if(is_string($fieldErrors) && trim($fieldErrors)!=='')
                    {
                        $validationError=trim($fieldErrors);
                        break;
                    }
                }
                if($validationError==='')
                {
                    $validationError=__('Contact could not be saved. Please check image type/size and required fields.');
                }
                $this->Session->setFlash($validationError,'flash',array('alert'=>'danger'));
            }
            catch (Throwable $e)
            {
                CakeLog::write('error', 'Contact add error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
                $this->Session->setFlash(__('Error saving contact: ') . $e->getMessage(), 'flash', array('alert' => 'danger'));
                return $this->redirect(array('action' => 'index'));
            }
        }
    }
    public function edit($id = null)
    {
        if (!$id)
        {
            throw new NotFoundException(__('Invalid post'));
        }
        $ids=explode(",",$id);
        $post=array();
        foreach($ids as $k=>$id)
        {$k++;
            $post[$k]=$this->Contact->findByid($id);
	    if(!$this->_isContactAccessAllowed($post[$k])) {
		$this->Session->setFlash(__('Invalid Post'),'flash',array('alert'=>'danger'));
		return $this->redirect(array('action' => 'index'));
	    }
        }
        $this->set('Contact',$post);
        if (!$post)
        {
            throw new NotFoundException(__('Invalid post'));
        }
        $this->loadModel('Category');
        $this->set('category',$this->Category->find('list'));
        $this->set('agency',$this->CustomFunction->agencyWiseList($this->userType,$this->agencyId));
        $this->set('linkedLeadMap',$this->_linkedLeadMapForContacts($post));
        $this->set('linkedDealCountMap',$this->_dealCountMapForContacts($post));
        $this->_setAddressHierarchyVars();
        if ($this->request->is(array('post', 'put')))
        {
            try
            {
                $unlinkContactLeadMap = array();
                $convertContactIds = array();
                $blockedDealContactCount = 0;
                foreach($this->request->data as $key=>$value)
                {
                    if(isset($this->request->data[$key]['Contact']))
                    {
                        $contactIdForUnlink = !empty($this->request->data[$key]['Contact']['id']) ? (int)$this->request->data[$key]['Contact']['id'] : 0;
                        $hasUnlinkRequest = !empty($this->request->data[$key]['Contact']['unlink_lead_ids']) || !empty($this->request->data[$key]['Contact']['unlink_leads']) || !empty($this->request->data[$key]['Contact']['convert_to_lead']);
                        if($contactIdForUnlink > 0 && $hasUnlinkRequest && $this->_contactHasLinkedDeal($contactIdForUnlink))
                        {
                            $blockedDealContactCount++;
                        }
                        elseif($contactIdForUnlink > 0 && !empty($this->request->data[$key]['Contact']['unlink_lead_ids']) && is_array($this->request->data[$key]['Contact']['unlink_lead_ids']))
                        {
                            $unlinkContactLeadMap[$contactIdForUnlink] = array_filter(array_map('intval', $this->request->data[$key]['Contact']['unlink_lead_ids']));
                        }
                        elseif($contactIdForUnlink > 0 && !empty($this->request->data[$key]['Contact']['unlink_leads']))
                        {
                            $unlinkContactLeadMap[$contactIdForUnlink] = array();
                        }
                        elseif($contactIdForUnlink > 0 && !empty($this->request->data[$key]['Contact']['convert_to_lead']))
                        {
                            $convertContactIds[] = $contactIdForUnlink;
                        }
                        if(isset($this->request->data[$key]['Contact']['convert_to_lead']))
                        {
                            unset($this->request->data[$key]['Contact']['convert_to_lead']);
                        }
                        if(isset($this->request->data[$key]['Contact']['unlink_lead_ids']))
                        {
                            unset($this->request->data[$key]['Contact']['unlink_lead_ids']);
                        }
                        if(isset($this->request->data[$key]['Contact']['unlink_leads']))
                        {
                            unset($this->request->data[$key]['Contact']['unlink_leads']);
                        }
                        // Support changing/removing profile photo and CNIC images directly or via base64 crop
                        $uploadFields = array('photo', 'id_front', 'id_back', 'side_photo');
                        foreach ($uploadFields as $uField) {
                            $dataKey = $uField . '_data';
                            $removeKey = 'remove_' . $uField;

                            if (!empty($this->request->data[$key]['Contact'][$removeKey])) {
                                $this->request->data[$key]['Contact'][$uField] = array('remove' => true);
                                unset($this->request->data[$key]['Contact'][$removeKey]);
                            } elseif (!empty($this->request->data[$key]['Contact'][$dataKey])) {
                                $base64Data = (string)$this->request->data[$key]['Contact'][$dataKey];
                                if (preg_match('/^data:image\/(\w+);base64,(.+)$/', $base64Data, $bMatches)) {
                                    $imgExt = strtolower($bMatches[1]);
                                    if ($imgExt === 'jpeg') $imgExt = 'jpg';
                                    $decoded = base64_decode($bMatches[2]);
                                    if ($decoded !== false && strlen($decoded) > 0) {
                                        $tmpDir = TMP . 'uploads' . DS;
                                        if (!is_dir($tmpDir)) {
                                            @mkdir($tmpDir, 0777, true);
                                        }
                                        $tmpFileName = 'crop_' . $uField . '_' . time() . '_' . mt_rand(1000, 9999) . '.' . $imgExt;
                                        $tmpFilePath = $tmpDir . $tmpFileName;
                                        file_put_contents($tmpFilePath, $decoded);
                                        $this->request->data[$key]['Contact'][$uField] = array(
                                            'name' => $tmpFileName,
                                            'type' => 'image/' . ($imgExt === 'jpg' ? 'jpeg' : $imgExt),
                                            'tmp_name' => $tmpFilePath,
                                            'error' => 0,
                                            'size' => filesize($tmpFilePath)
                                        );
                                    }
                                }
                                unset($this->request->data[$key]['Contact'][$dataKey]);
                            }
                        }
                        $this->_setContactImageDirs($this->request->data[$key]['Contact']);
                        $this->_normalizeContactAddressHierarchy($this->request->data[$key]['Contact']);
                    }
                }
                if ($this->Contact->saveAll($this->request->data))
                {
                    $unlinkedLeadCount = 0;
                    $removedContactCount = 0;
                    foreach($unlinkContactLeadMap as $unlinkContactId => $unlinkLeadIds)
                    {
                        $currentUnlinkedLeadCount = $this->_unlinkContactLinkedLeads($unlinkContactId, $unlinkLeadIds);
                        $unlinkedLeadCount += $currentUnlinkedLeadCount;
                        if($currentUnlinkedLeadCount > 0 && $this->_removeContactAfterLeadMove($unlinkContactId))
                        {
                            $removedContactCount++;
                        }
                    }
                    $createdLeadCount = 0;
                    foreach(array_unique($convertContactIds) as $convertContactId)
                    {
                        if($this->_createLeadFromContact($convertContactId) > 0)
                        {
                            $createdLeadCount++;
                            if($this->_removeContactAfterLeadMove($convertContactId))
                            {
                                $removedContactCount++;
                            }
                        }
                    }
                    if($unlinkedLeadCount > 0 || $createdLeadCount > 0 || $removedContactCount > 0)
                    {
                        $flashMessage = __('Your Contact Manager has been Updated. ');
                        if($unlinkedLeadCount > 0)
                        {
                            $flashMessage .= $unlinkedLeadCount.__(' linked lead(s) changed back to Lead Contact. ');
                        }
                        if($createdLeadCount > 0)
                        {
                            $flashMessage .= $createdLeadCount.__(' lead contact(s) created from contact. ');
                        }
                        if($removedContactCount > 0)
                        {
                            $flashMessage .= $removedContactCount.__(' contact(s) removed from contact list. ');
                        }
                        if($blockedDealContactCount > 0)
                        {
                            $flashMessage .= ' '.__('Some contact(s) have linked deals and cannot be moved back to Lead Contact.');
                        }
                        $this->Session->setFlash($flashMessage,'flash',array('alert'=>'success'));
                    }
                    elseif($blockedDealContactCount > 0)
                    {
                        $this->Session->setFlash(__('Your Contact Manager has been Updated. Contact has linked deal and cannot be moved back to Lead Contact.'),'flash',array('alert'=>'warning'));
                    }
                    else
                    {
                        $this->Session->setFlash(__('Your Contact Manager has been Updated.'),'flash',array('alert'=>'success'));
                    }
                    return $this->redirect(array('action' => 'index'));
                }
                $validationError='';
                foreach((array)$this->Contact->validationErrors as $fieldErrors)
                {
                    if(is_array($fieldErrors) && !empty($fieldErrors[0]))
                    {
                        $validationError=(string)$fieldErrors[0];
                        break;
                    }
                    if(is_string($fieldErrors) && trim($fieldErrors)!=='')
                    {
                        $validationError=trim($fieldErrors);
                        break;
                    }
                }
                if($validationError==='')
                {
                    $validationError=__('Contact could not be updated. Please check image type/size and required fields.');
                }
                $this->Session->setFlash($validationError,'flash',array('alert'=>'danger'));
            }
            catch (Throwable $e)
            {
                CakeLog::write('error', 'Contact edit error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
                $this->Session->setFlash(__('Error updating contact: ') . $e->getMessage(), 'flash', array('alert' => 'danger'));
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
    public function delete($id = null)
    {
        if ($this->request->is('post') || $this->request->is('delete') || $this->request->is('get'))
        {
            $id = (int)$id;
            if ($id > 0)
            {
                $this->request->data['Contact']['id'] = array($id);
                return $this->deleteall();
            }
        }
        return $this->redirect(array('action' => 'index'));
    }
    public function deleteall()
    {
        try
        {
            if ($this->request->is('post'))
            {
                foreach($this->data['Contact']['id'] as $key => $value)
                {
		    if($value > 0) {
			$prop[$key]=$this->Contact->findByid($value);
			if(!$this->_isContactAccessAllowed($prop[$key])) {
			    $this->Session->setFlash(__('Invalid Post'),'flash',array('alert'=>'danger'));
			    return $this->redirect(array('action' => 'index'));
			}
                        $this->Contact->delete($value);
		    }
                }
                $this->Session->setFlash(__('Your Contact Manager has been deleted.'),'flash',array('alert'=>'success'));
            }        
            $this->redirect(array('action' => 'index'));
        }
        catch (Exception $e)
        {
            $this->Session->setFlash(__('Please Delete Deal first.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'index'));
        }
    }
    public function view($id = null)
    {
        $this->layout = null;
        if (!$id)
        {
            $this->Session->setFlash(__('Invalid Post.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        $post = $this->Contact->findById($id);
        if (!$post || !$this->_isContactAccessAllowed($post))
        {
            $this->Session->setFlash(__('Invalid Post.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        $this->set('post',$post);
    }
    public function printclient($id = null)
    {
        $this->layout = null;
        if (!$id)
        {
            $this->Session->setFlash(__('Invalid Post.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        $post = $this->Contact->findById($id);
        if (!$post || !$this->_isContactAccessAllowed($post))
        {
            $this->Session->setFlash(__('Invalid Post.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        $this->_setAddressHierarchyVars();
        $this->set('post',$post);
    }
    protected function _addressPrintSelectedIds()
    {
        $ids = array();
        if(!empty($this->request->data['Contact']['id']) && is_array($this->request->data['Contact']['id']))
        {
            $ids = $this->request->data['Contact']['id'];
        }
        elseif(!empty($this->request->query['ids']))
        {
            $ids = explode(',', (string)$this->request->query['ids']);
        }
        $ids = array_values(array_unique(array_filter(array_map('intval', (array)$ids))));
        return $ids;
    }
    protected function _addressPrintAgencyHeader($projectId=0)
    {
        $projectId = (int)$projectId;
        $header = array(
            'name' => isset($this->siteOrganization) ? (string)$this->siteOrganization : (isset($this->siteName) ? (string)$this->siteName : ''),
            'address' => isset($this->siteAddress) ? (string)$this->siteAddress : '',
            'phone' => isset($this->contact) ? (string)$this->contact : '',
            'mobile' => '',
            'email' => isset($this->siteEmail) ? (string)$this->siteEmail : '',
            'logo' => isset($this->viewVars['frontLogo']) ? (string)$this->viewVars['frontLogo'] : ''
        );
        if($projectId <= 0)
        {
            $projectId = (int)$this->agencyId;
        }
        if($projectId > 0)
        {
            try
            {
                $this->loadModel('Agency');
                $agencyFields = array('Agency.id');
                foreach(array('name','address','phone','mobile','email','photo') as $fieldName)
                {
                    if($this->Agency->hasField($fieldName))
                    {
                        $agencyFields[] = 'Agency.'.$fieldName;
                    }
                }
                $agencyRow = $this->Agency->find('first', array(
                    'fields' => $agencyFields,
                    'conditions' => array('Agency.id' => $projectId),
                    'recursive' => -1
                ));
                if(!empty($agencyRow['Agency']))
                {
                    foreach(array('name','address','phone','mobile','email') as $fieldName)
                    {
                        if(!empty($agencyRow['Agency'][$fieldName]))
                        {
                            $header[$fieldName] = $agencyRow['Agency'][$fieldName];
                        }
                    }
                    if(!empty($agencyRow['Agency']['photo']))
                    {
                        $header['logo'] = 'agency_thumb/'.basename((string)$agencyRow['Agency']['photo']);
                    }
                }
            }
            catch(Exception $e)
            {
                // Keep site defaults if agency lookup is unavailable.
            }
            try
            {
                $this->loadModel('User');
                $userFields = array('User.id');
                foreach(array('name','address','phone','mobile','email','photo') as $fieldName)
                {
                    if($this->User->hasField($fieldName))
                    {
                        $userFields[] = 'User.'.$fieldName;
                    }
                }
                $agencyUser = $this->User->find('first', array(
                    'fields' => $userFields,
                    'joins' => array(
                        array(
                            'table' => 'ugroups',
                            'alias' => 'Ugroup',
                            'type' => 'INNER',
                            'conditions' => array('User.ugroup_id = Ugroup.id')
                        )
                    ),
                    'conditions' => array('User.project_id' => $projectId, 'Ugroup.type' => 'AGY'),
                    'order' => array('User.id' => 'asc'),
                    'recursive' => -1
                ));
                if(empty($agencyUser['User']))
                {
                    $agencyUser = $this->User->find('first', array(
                        'fields' => $userFields,
                        'joins' => array(
                            array(
                                'table' => 'ugroups',
                                'alias' => 'Ugroup',
                                'type' => 'INNER',
                                'conditions' => array('User.ugroup_id = Ugroup.id')
                            )
                        ),
                        'conditions' => array('User.project_id' => $projectId, 'Ugroup.name' => 'Agency'),
                        'order' => array('User.id' => 'asc'),
                        'recursive' => -1
                    ));
                }
                if(!empty($agencyUser['User']))
                {
                    foreach(array('name','address','phone','mobile','email') as $fieldName)
                    {
                        if(!empty($agencyUser['User'][$fieldName]))
                        {
                            $header[$fieldName] = $agencyUser['User'][$fieldName];
                        }
                    }
                    if(!empty($agencyUser['User']['photo']))
                    {
                        $header['logo'] = 'user_thumb/'.basename((string)$agencyUser['User']['photo']);
                    }
                }
            }
            catch(Exception $e)
            {
                // Keep the agency/site details already resolved above.
            }
        }
        if(empty($header['logo']) && !empty($this->viewVars['frontLogo']))
        {
            $header['logo'] = (string)$this->viewVars['frontLogo'];
        }
        return $header;
    }
    public function addressprint()
    {
        $this->layout = null;
        $ids = $this->_addressPrintSelectedIds();
        if(empty($ids))
        {
            $this->set('contacts', array());
            $this->set('agencyHeaders', array());
            return;
        }
        $conditions = array('Contact.id' => $ids);
        if($this->userType == 'AGT')
        {
            if(!empty($this->agencyId))
            {
                $conditions['Contact.project_id'] = (int)$this->agencyId;
            }
            $this->loadModel('Admin.Deal');
            $dealClientIds = (array)$this->Deal->find('list', array(
                'fields' => array('Deal.client_id', 'Deal.client_id'),
                'conditions' => array('Deal.user_id' => (int)$this->luserId, 'Deal.client_id >' => 0),
                'recursive' => -1
            ));
            $dealClientIds = array_values(array_unique(array_filter($dealClientIds)));
            if(!empty($dealClientIds))
            {
                $conditions['OR'] = array(
                    'Contact.user_id' => (int)$this->luserId,
                    'Contact.id' => $dealClientIds
                );
            }
            else
            {
                $conditions['Contact.user_id'] = (int)$this->luserId;
            }
        }
        elseif($this->userType == 'AGY')
        {
            $conditions['Contact.project_id'] = (int)$this->agencyId;
        }
        $contacts = $this->Contact->find('all', array(
            'conditions' => $conditions,
            'order' => array('FIELD(Contact.id,'.implode(',', $ids).')'),
            'recursive' => -1
        ));
        $agencyHeaders = array();
        foreach((array)$contacts as $contactRow)
        {
            $projectId = !empty($contactRow['Contact']['project_id']) ? (int)$contactRow['Contact']['project_id'] : (int)$this->agencyId;
            if(!isset($agencyHeaders[$projectId]))
            {
                $agencyHeaders[$projectId] = $this->_addressPrintAgencyHeader($projectId);
            }
        }
        $this->set('contacts', $contacts);
        $this->set('agencyHeaders', $agencyHeaders);
    }
    public function changepass($id = null)
    {
        $this->layout = null;
        if (!$id)
        {
            $this->Session->setFlash(__('Invalid Post.'),'flash',array('alert'=>'danger'));
            $this->redirect(array('action' => 'index'));
        }
        $post = $this->Contact->findById($id);
        if (!$post)
        {
            $this->Session->setFlash(__('Invalid Post.'),'flash',array('alert'=>'danger'));
            $this->redirect(array('action' => 'index'));
        }
	if(!$this->_isContactAccessAllowed($post)) {
	    $this->Session->setFlash(__('Invalid Post'),'flash',array('alert'=>'danger'));
	    return $this->redirect(array('action' => 'index'));
	}
        if ($this->request->is(array('post', 'put')))
        {
	    try
	    {
		$this->Contact->id = $id;
		$this->request->data['Contact']['password'] = (string)$this->request->data['Contact']['password'];
		if ($this->Contact->save($this->request->data,array('validate'=>false)))
		{
		    $this->Session->setFlash(__('Password Changed Successfully'),'flash',array('alert'=>'success'));
		    $this->redirect(array('action' => 'index'));
		}
		$this->Session->setFlash(__('Invalid Post.'),'flash',array('alert'=>'danger'));
		$this->redirect(array('action' => 'index'));
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
    public function loginas($id = null)
    {
        if(!$id)
        {
            $this->Session->setFlash(__('Invalid Post'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        $currentUser = (array)$this->Session->read('User');
        if(!$this->_isSuperAdminSession($currentUser))
        {
            $this->Session->setFlash(__('Only Super Admin can use Login as Contact User.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        $contact = $this->Contact->find('first', array(
            'conditions' => array('Contact.id' => (int)$id, 'Contact.status' => 'Active'),
            'recursive' => -1
        ));
        if(empty($contact['Contact']['id']))
        {
            $this->Session->setFlash(__('Target contact not found or inactive.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        $frontLoginTime = date('Y-m-d H:i:s');
        try
        {
            $this->Contact->id = (int)$contact['Contact']['id'];
            $this->Contact->save(array('Contact'=>array('id'=>(int)$contact['Contact']['id'],'last_login'=>$frontLoginTime)),array('validate'=>false,'callbacks'=>false));
            $contact['Contact']['last_login'] = $frontLoginTime;
        }
        catch (Exception $e)
        {
            CakeLog::write('error','Contact login as last_login update failed: '.$e->getMessage());
        }
        $frontUser = array('Client' => (array)$contact['Contact']);
        $impMeta = array(
            'started_at' => time(),
            'target_contact_id' => (int)$contact['Contact']['id'],
            'target_contact_name' => (string)$contact['Contact']['name'],
            'target_contact_role' => 'Contact'
        );
        $existingFront = $this->Session->read('frontUser');
        if(!empty($existingFront))
        {
            $impMeta['original_front_user'] = $existingFront;
        }
        $this->Session->write('Impersonation.front', $impMeta);
        $this->Session->write('frontUser', $frontUser);
        CakeSession::renew();
        $this->Session->setFlash(sprintf(__('Front login started as %s.'),(string)$contact['Contact']['name']),'flash',array('alert'=>'success'));
        return $this->redirect(array('plugin'=>false,'admin'=>false,'controller' => 'Dashboards', 'action' => 'index'));
    }
    public function exportExcel($id = null)
    {
        $this->autoRender = false;
        $this->layout = null;

        $condition = $this->_contactListConditions($id);
        $contacts = $this->Contact->find('all', array(
            'conditions' => $condition,
            'order' => array('Contact.id' => 'desc')
        ));

        $filename = "contacts_export_" . date("Y-m-d_H-i-s") . ".csv";

        header("Content-Type: text/csv; charset=utf-8");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Pragma: no-cache");
        header("Expires: 0");

        $output = fopen("php://output", "w");
        fputcsv($output, array('ID', 'Name', 'Mobile', 'CNIC', 'KYC Status', 'Address', 'District', 'Email'));

        foreach ($contacts as $contact) {
            $hasCnicNo  = !empty(trim((string)$contact['Contact']['pincode']));
            $hasPhoto   = !empty(trim((string)$contact['Contact']['photo']));
            $hasIdFront = !empty(trim((string)$contact['Contact']['id_front']));
            $hasIdBack  = !empty(trim((string)$contact['Contact']['id_back']));
            $docScore   = ($hasCnicNo ? 1 : 0) + ($hasPhoto ? 1 : 0) + ($hasIdFront ? 1 : 0) + ($hasIdBack ? 1 : 0);
            $kycText    = ($docScore === 4) ? 'Verified' : (($docScore > 0) ? "Partial ($docScore/4)" : 'Pending');

            fputcsv($output, array(
                $contact['Contact']['id'],
                $contact['Contact']['name'],
                $contact['Contact']['mobile'],
                $contact['Contact']['pincode'],
                $kycText,
                $contact['Contact']['address'],
                $contact['Contact']['district'],
                $contact['Contact']['email']
            ));
        }

        fclose($output);
        exit();
    }

    /**
     * Re-send or manually dispatch portal login credentials to client via WhatsApp, SMS, and Email
     */
    public function sendcredentials($id = null)
    {
        $id = (int)$id;
        if ($id <= 0) {
            $this->Session->setFlash(__('Invalid contact.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }

        $contact = $this->Contact->findById($id);
        if (empty($contact) || !$this->_isContactAccessAllowed($contact)) {
            $this->Session->setFlash(__('Contact record was not found or access denied.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }

        $newPass = (string)rand(100000, 999999);
        $this->Contact->id = $id;
        $this->Contact->saveField('password', $this->_hashPassword($newPass), array('validate' => false));

        $res = $this->_dispatchContactRegistrationWelcome($id, $newPass, $contact['Contact']);

        $channels = array();
        if (!empty($res['whatsapp'])) $channels[] = 'WhatsApp';
        if (!empty($res['sms']))      $channels[] = 'SMS';
        if (!empty($res['email']))    $channels[] = 'Email';

        $channelStr = !empty($channels) ? implode(', ', $channels) : __('configured gateway channels');
        $this->Session->setFlash(sprintf(__('Portal credentials dispatched to %s via %s (Password: %s).'), h($contact['Contact']['name']), $channelStr, $newPass), 'flash', array('alert' => 'success'));
        return $this->redirect(array('action' => 'index'));
    }

    /**
     * Toggle or update login status (Active vs Block) for a Contact / Client
     * When Block, client cannot login to Client Portal or Flutter App
     */
    public function change_status($id = null, $status = null)
    {
        $id = (int)$id;
        if ($id <= 0) {
            if ($this->request->is('ajax')) {
                return new CakeResponse(array('type' => 'json', 'body' => json_encode(array('status' => false, 'message' => __('Invalid contact ID.')))));
            }
            $this->Session->setFlash(__('Invalid contact.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }

        $contact = $this->Contact->findById($id);
        if (empty($contact) || !$this->_isContactAccessAllowed($contact)) {
            if ($this->request->is('ajax')) {
                return new CakeResponse(array('type' => 'json', 'body' => json_encode(array('status' => false, 'message' => __('Contact record was not found or access denied.')))));
            }
            $this->Session->setFlash(__('Contact record was not found or access denied.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }

        $currentStatus = !empty($contact['Contact']['status']) ? trim((string)$contact['Contact']['status']) : 'Active';

        if ($status === null || trim((string)$status) === '') {
            $newStatus = (strcasecmp($currentStatus, 'Active') === 0) ? 'Block' : 'Active';
        } else {
            $status = trim((string)$status);
            $newStatus = (strcasecmp($status, 'Block') === 0 || strcasecmp($status, 'Blocked') === 0) ? 'Block' : 'Active';
        }

        $this->Contact->id = $id;
        $saveData = array('status' => $newStatus);
        if ($newStatus === 'Block') {
            $saveData['app_login_status'] = 0;
        }
        $this->Contact->save($saveData, array('validate' => false, 'callbacks' => false));

        $isBlocked = ($newStatus === 'Block');
        $msg = $isBlocked 
            ? sprintf(__('Login access for %s has been BLOCKED. This client cannot login to client portal or Flutter mobile app.'), h($contact['Contact']['name']))
            : sprintf(__('Login access for %s has been ACTIVATED.'), h($contact['Contact']['name']));

        if ($this->request->is('ajax')) {
            return new CakeResponse(array(
                'type' => 'json',
                'body' => json_encode(array(
                    'status' => true,
                    'new_status' => $newStatus,
                    'is_blocked' => $isBlocked,
                    'label' => $isBlocked ? __('Blocked') : __('Active'),
                    'message' => $msg
                ))
            ));
        }

        $this->Session->setFlash($msg, 'flash', array('alert' => $isBlocked ? 'danger' : 'success'));
        return $this->redirect($this->referer(array('action' => 'index')));
    }

    public function toggle_status($id = null)
    {
        return $this->change_status($id, null);
    }

    public function change_login_status($id = null, $status = null)
    {
        return $this->change_status($id, $status);
    }

}
