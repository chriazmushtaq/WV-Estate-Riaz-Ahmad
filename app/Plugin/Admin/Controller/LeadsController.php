<?php
App::uses('CakeTime', 'Utility');
App::uses('SimplePasswordHasher', 'Controller/Component/Auth');
App::uses('CakeEmail', 'Network/Email');
class LeadsController extends AdminAppController {
    public $helpers = array('Html', 'Form','Session','Paginator','Js'=> array('Jquery'));
    public $components = array('Session','Paginator','search-master.Prg');
    public $presetVars = true;
    var $paginate = array('page'=>1,'order'=>array('Lead.id'=>'desc'));

    public function beforeFilter()
    {
        $currAction = strtolower((string)$this->request->params['action']);
        if($currAction === 'runscheduledsmscron')
        {
            $this->autoRender = false;
            $this->layout = false;
            $this->_ensureLeadCrmColumns();
            $this->_ensureLeadHistorySchema();
            return;
        }
        $cleanAction = preg_replace('/^admin_/', '', $currAction);
        if($this->request->is('ajax') && in_array($cleanAction, array('send_notification', 'bulk_notifications', 'quick_leadbot_dispatch', 'leadbot_templates_ajax'), true))
        {
            $this->autoRender = false;
            $this->layout = false;
            if(isset($this->Security))
            {
                $this->Security->validatePost = false;
                $this->Security->csrfCheck = false;
            }
        }
        parent::beforeFilter();
        $this->layout = 'Admin.default';
        if($this->_shouldRunControllerBootstrap('leads.schema', '2026-09-02.2'))
        {
            $this->_ensureLeadCrmColumns();
            $this->_ensureLeadKanbanIndexes();
            $this->_ensureLeadHistorySchema();
            $this->_ensureLeadSmsScheduleSchema();
            $this->_ensureLeadsCrmMenu();
        }
    }

    public function beforeRender()
    {
        parent::beforeRender();
        $this->plugin = 'Admin';
        if ($this->layout === 'default' || empty($this->layout) || $this->layout === 'Admin.default') {
            $this->layout = 'Admin.default';
        }
    }

    protected function _ensureLeadCrmColumns()
    {
        $columns = array(
            'lead_name' => "varchar(255) DEFAULT NULL",
            'lead_mobile' => "varchar(60) DEFAULT NULL",
            'lead_email' => "varchar(255) DEFAULT NULL",
            'lead_address' => "text NULL",
            'lead_city' => "varchar(150) DEFAULT NULL",
            'lead_budget' => "decimal(15,2) DEFAULT NULL",
            'lead_language' => "varchar(80) DEFAULT NULL",
            'lead_source' => "varchar(150) DEFAULT NULL",
            'source_form_id' => "varchar(120) DEFAULT NULL",
            'source_ad_id' => "varchar(120) DEFAULT NULL",
            'utm_source' => "varchar(191) DEFAULT NULL",
            'utm_campaign' => "varchar(191) DEFAULT NULL",
            'utm_medium' => "varchar(191) DEFAULT NULL",
            'utm_term' => "varchar(191) DEFAULT NULL",
            'utm_content' => "varchar(191) DEFAULT NULL",
            'last_contact_date' => "date DEFAULT NULL",
            'lead_temperature' => "varchar(12) DEFAULT 'warm'",
            'kanban_position' => "int(11) DEFAULT 0"
        );
        foreach($columns as $columnName => $columnSql)
        {
            try
            {
                $exists = $this->Lead->query("SHOW COLUMNS FROM `leads` LIKE '".addslashes($columnName)."'");
                if(empty($exists))
                {
                    $this->Lead->query("ALTER TABLE `leads` ADD `{$columnName}` {$columnSql}");
                }
            }
            catch(Exception $e)
            {
                // Keep request running even if schema update fails.
            }
        }
        try
        {
            $this->Lead->query("ALTER TABLE `leads` MODIFY COLUMN `user_id` INT(11) NOT NULL DEFAULT 1");
        }
        catch(Exception $e) {}
        try
        {
            $this->Lead->query("ALTER TABLE `leads` MODIFY COLUMN `status_id` INT(11) NOT NULL DEFAULT 1");
        }
        catch(Exception $e) {}
    }

    protected function _ensureLeadKanbanIndexes()
    {
        $indexSqlMap = array(
            'idx_leads_status_position' => "ALTER TABLE `leads` ADD INDEX `idx_leads_status_position` (`status_id`,`kanban_position`,`id`)",
            'idx_leads_scope_status' => "ALTER TABLE `leads` ADD INDEX `idx_leads_scope_status` (`project_id`,`user_id`,`status_id`)",
            'idx_leads_follow_up' => "ALTER TABLE `leads` ADD INDEX `idx_leads_follow_up` (`follow_up`)",
            'idx_leads_client_id' => "ALTER TABLE `leads` ADD INDEX `idx_leads_client_id` (`client_id`)",
            'idx_leads_manage_scope_followup' => "ALTER TABLE `leads` ADD INDEX `idx_leads_manage_scope_followup` (`project_id`,`user_id`,`follow_up`,`id`)",
            'idx_leads_manage_user_followup' => "ALTER TABLE `leads` ADD INDEX `idx_leads_manage_user_followup` (`user_id`,`follow_up`,`id`)",
            'idx_leads_kanban_scope_order' => "ALTER TABLE `leads` ADD INDEX `idx_leads_kanban_scope_order` (`project_id`,`user_id`,`status_id`,`kanban_position`,`follow_up`,`id`)",
            'idx_leads_kanban_user_order' => "ALTER TABLE `leads` ADD INDEX `idx_leads_kanban_user_order` (`user_id`,`status_id`,`kanban_position`,`follow_up`,`id`)",
            'idx_leads_kanban_scope_followup' => "ALTER TABLE `leads` ADD INDEX `idx_leads_kanban_scope_followup` (`project_id`,`user_id`,`status_id`,`follow_up`,`kanban_position`,`id`)",
            'idx_leads_kanban_user_followup' => "ALTER TABLE `leads` ADD INDEX `idx_leads_kanban_user_followup` (`user_id`,`status_id`,`follow_up`,`kanban_position`,`id`)"
        );
        foreach($indexSqlMap as $indexName => $sql)
        {
            try
            {
                $exists = (array)$this->Lead->query("SHOW INDEX FROM `leads` WHERE `Key_name` = '".addslashes($indexName)."'");
                if(empty($exists))
                {
                    $this->Lead->query($sql);
                }
            }
            catch(Exception $e)
            {
                // Keep request flow safe if index create is blocked.
            }
        }
    }

    protected function _ensureLeadClientNullable()
    {
        static $isEnsured = null;
        if($isEnsured !== null)
        {
            return $isEnsured;
        }
        $isEnsured = false;
        try
        {
            $clientColumnRows = (array)$this->Lead->query("SHOW COLUMNS FROM `leads` LIKE 'client_id'");
            if(empty($clientColumnRows))
            {
                return false;
            }
            $columnMeta = reset($clientColumnRows);
            $columnInfo = array();
            if(isset($columnMeta['COLUMNS']))
            {
                $columnInfo = (array)$columnMeta['COLUMNS'];
            }
            elseif(isset($columnMeta[0]))
            {
                $columnInfo = (array)$columnMeta[0];
            }
            $isNullable = !empty($columnInfo['Null']) && strtoupper((string)$columnInfo['Null']) === 'YES';
            if($isNullable)
            {
                $isEnsured = true;
                return true;
            }

            $constraintName = '';
            $constraintRows = (array)$this->Lead->query(
                "SELECT `CONSTRAINT_NAME` FROM `information_schema`.`KEY_COLUMN_USAGE` ".
                "WHERE `TABLE_SCHEMA` = DATABASE() AND `TABLE_NAME` = 'leads' AND `COLUMN_NAME` = 'client_id' ".
                "AND `REFERENCED_TABLE_NAME` = 'clients' LIMIT 1"
            );
            if(!empty($constraintRows[0]['KEY_COLUMN_USAGE']['CONSTRAINT_NAME']))
            {
                $constraintName = (string)$constraintRows[0]['KEY_COLUMN_USAGE']['CONSTRAINT_NAME'];
            }
            elseif(!empty($constraintRows[0][0]['CONSTRAINT_NAME']))
            {
                $constraintName = (string)$constraintRows[0][0]['CONSTRAINT_NAME'];
            }

            if($constraintName !== '')
            {
                $this->Lead->query("ALTER TABLE `leads` DROP FOREIGN KEY `".addslashes($constraintName)."`");
            }
            $this->Lead->query("ALTER TABLE `leads` MODIFY `client_id` int(11) DEFAULT NULL");
            $this->Lead->query(
                "ALTER TABLE `leads` ADD CONSTRAINT `leads_ibfk_5` FOREIGN KEY (`client_id`) ".
                "REFERENCES `clients` (`id`) ON DELETE CASCADE ON UPDATE CASCADE"
            );
            $isEnsured = true;
        }
        catch(Exception $e)
        {
            $isEnsured = false;
        }
        return $isEnsured;
    }

    protected function _ensureLeadsCrmMenu()
    {
        try
        {
            $Page = ClassRegistry::init('Page');
            $PageRight = ClassRegistry::init('PageRight');
            $Ugroup = ClassRegistry::init('Ugroup');
            $User = ClassRegistry::init('User');
            $superAdminGroupId = 0;
            try
            {
                $superAdminGroupId = (int)$User->field('ugroup_id', array('User.id' => 1));
            }
            catch(Exception $e)
            {
                $superAdminGroupId = 0;
            }

            $root = $Page->find('first', array(
                'fields' => array('Page.id','Page.action_name'),
                'conditions' => array(
                    'LOWER(Page.controller_name)' => 'leads',
                    'Page.parent_id' => 0
                ),
                'order' => array('Page.id' => 'asc'),
                'recursive' => -1
            ));

            if(empty($root['Page']['id']))
            {
                $Page->create();
                $Page->save(array('Page' => array(
                    'model_name' => 'Leads',
                    'page_name' => 'Leads CRM',
                    'controller_name' => 'Leads',
                    'action_name' => 'dashboard',
                    'icon' => 'fa fa-bullhorn',
                    'parent_id' => 0,
                    'ordering' => 6,
                    'published' => 'Yes',
                    'sel_name' => ''
                )));
                $rootId = (int)$Page->id;
            }
            else
            {
                $rootId = (int)$root['Page']['id'];
                if($rootId > 0)
                {
                    $Page->save(array('Page' => array(
                        'id' => $rootId,
                        'model_name' => 'Leads',
                        'page_name' => 'Leads CRM',
                        'controller_name' => 'Leads',
                        'action_name' => 'dashboard',
                        'icon' => 'fa fa-bullhorn',
                        'parent_id' => 0,
                        'ordering' => 6,
                        'published' => 'Yes',
                        'sel_name' => ''
                    )));
                }
            }

            if($rootId <= 0)
            {
                return;
            }

            $menuPageIds = array($rootId => $rootId);
            $children = array(
                array('name' => 'CRM Dashboard', 'action' => 'dashboard', 'icon' => 'fa fa-dashboard', 'ordering' => 1),
                array('name' => 'Manage Leads', 'action' => 'manage', 'icon' => 'fa fa-list', 'ordering' => 2),
                array('name' => 'Brochure & PDF Leads', 'action' => 'brochureleads', 'icon' => 'fa fa-file-pdf-o', 'ordering' => 3),
                array('name' => 'Kanban Board', 'action' => 'kanban', 'icon' => 'fa fa-columns', 'ordering' => 4),
                array('name' => 'Add Lead', 'action' => 'leadadd', 'icon' => 'fa fa-plus-circle', 'ordering' => 4),
                array('name' => 'Import Leads', 'action' => 'import', 'icon' => 'fa fa-upload', 'ordering' => 5),
                array('name' => 'Progress', 'action' => 'dailyprogress', 'icon' => 'fa fa-line-chart', 'ordering' => 6),
                array('name' => 'Schedule SMS', 'action' => 'schedulesms', 'icon' => 'fa fa-clock-o', 'ordering' => 7)
            );

            foreach($children as $child)
            {
                $existing = $Page->find('first', array(
                    'fields' => array('Page.id'),
                    'conditions' => array(
                        'LOWER(Page.controller_name)' => 'leads',
                        'LOWER(Page.action_name)' => strtolower($child['action']),
                        'Page.parent_id' => $rootId
                    ),
                    'recursive' => -1
                ));
                $data = array(
                    'model_name' => 'Leads',
                    'page_name' => $child['name'],
                    'controller_name' => 'Leads',
                    'action_name' => $child['action'],
                    'icon' => $child['icon'],
                    'parent_id' => $rootId,
                    'ordering' => $child['ordering'],
                    'published' => 'Yes',
                    'sel_name' => ''
                );
                if(empty($existing['Page']['id']))
                {
                    $Page->create();
                    $Page->save(array('Page' => $data));
                    if((int)$Page->id > 0)
                    {
                        $menuPageIds[(int)$Page->id] = (int)$Page->id;
                    }
                }
                else
                {
                    $data['id'] = (int)$existing['Page']['id'];
                    $Page->save(array('Page' => $data));
                    $menuPageIds[(int)$existing['Page']['id']] = (int)$existing['Page']['id'];
                }
            }

            // Hide legacy menu item that points to index (now dashboard redirect).
            $legacyIndexRows = $Page->find('all', array(
                'fields' => array('Page.id'),
                'conditions' => array(
                    'LOWER(Page.controller_name)' => 'leads',
                    'LOWER(Page.action_name)' => 'index',
                    'Page.parent_id' => $rootId
                ),
                'recursive' => -1
            ));
            foreach((array)$legacyIndexRows as $legacyIndexRow)
            {
                $legacyPageId = isset($legacyIndexRow['Page']['id']) ? (int)$legacyIndexRow['Page']['id'] : 0;
                if($legacyPageId <= 0)
                {
                    continue;
                }
                $Page->id = $legacyPageId;
                $Page->saveField('published','No');
            }

            $groups = $Ugroup->find('all', array(
                'fields' => array('Ugroup.id', 'Ugroup.type'),
                'recursive' => -1
            ));
            foreach((array)$groups as $groupRow)
            {
                $groupId = !empty($groupRow['Ugroup']['id']) ? (int)$groupRow['Ugroup']['id'] : 0;
                $groupType = !empty($groupRow['Ugroup']['type']) ? strtoupper(trim((string)$groupRow['Ugroup']['type'])) : '';
                $isSuperRole = ($groupId > 0 && ($groupId === $superAdminGroupId || $groupId === 1 || in_array($groupType, array('ADR','ADMIN','SUP','SAD'), true)));
                if($groupId <= 0)
                {
                    continue;
                }
                foreach($menuPageIds as $pageId)
                {
                    $existingRight = $PageRight->find('first', array(
                        'fields' => array('PageRight.id','PageRight.view_right','PageRight.save_right','PageRight.update_right','PageRight.search_right'),
                        'conditions' => array(
                            'PageRight.page_id' => (int)$pageId,
                            'PageRight.ugroup_id' => $groupId
                        ),
                        'recursive' => -1
                    ));
                    if(empty($existingRight['PageRight']['id']))
                    {
                        $saveRight = array(
                            'page_id' => (int)$pageId,
                            'ugroup_id' => $groupId,
                            'view_right' => 1
                        );
                        if($isSuperRole)
                        {
                            $saveRight['save_right'] = 1;
                            $saveRight['update_right'] = 1;
                            $saveRight['search_right'] = 1;
                        }
                        $PageRight->create();
                        $PageRight->save(array('PageRight' => $saveRight));
                        continue;
                    }

                    $rightId = (int)$existingRight['PageRight']['id'];
                    if($isSuperRole)
                    {
                        if(
                            empty($existingRight['PageRight']['view_right']) ||
                            empty($existingRight['PageRight']['save_right']) ||
                            empty($existingRight['PageRight']['update_right']) ||
                            empty($existingRight['PageRight']['search_right'])
                        )
                        {
                            $PageRight->id = $rightId;
                            $PageRight->save(array('PageRight' => array(
                                'id' => $rightId,
                                'view_right' => 1,
                                'save_right' => 1,
                                'update_right' => 1,
                                'search_right' => 1
                            )));
                        }
                    }
                    elseif(empty($existingRight['PageRight']['view_right']))
                    {
                        $PageRight->id = $rightId;
                        $PageRight->save(array('PageRight' => array(
                            'id' => $rightId,
                            'view_right' => 1
                        )));
                    }
                }
            }
        }
        catch(Exception $e)
        {
            // Ignore menu sync failures.
        }
    }

    protected function _ensureLeadHistorySchema()
    {
        try
        {
            $this->Lead->query(
                "CREATE TABLE IF NOT EXISTS `lead_histories` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `lead_id` int(11) NOT NULL,
                    `project_id` int(11) DEFAULT NULL,
                    `lead_user_id` int(11) DEFAULT NULL,
                    `actor_user_id` int(11) DEFAULT NULL,
                    `actor_name` varchar(191) DEFAULT NULL,
                    `actor_role` varchar(191) DEFAULT NULL,
                    `entry_type` varchar(50) NOT NULL DEFAULT 'change',
                    `title` varchar(255) DEFAULT NULL,
                    `field_name` varchar(100) DEFAULT NULL,
                    `field_label` varchar(191) DEFAULT NULL,
                    `old_value` text,
                    `new_value` text,
                    `recipient` varchar(255) DEFAULT NULL,
                    `message_body` longtext,
                    `meta_json` longtext,
                    `created` datetime DEFAULT NULL,
                    `modified` datetime DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `lead_histories_lead_created` (`lead_id`,`created`),
                    KEY `lead_histories_project` (`project_id`),
                    KEY `lead_histories_actor` (`actor_user_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8"
            );
            $requiredColumns = array(
                'project_id' => "ALTER TABLE `lead_histories` ADD `project_id` int(11) DEFAULT NULL",
                'lead_user_id' => "ALTER TABLE `lead_histories` ADD `lead_user_id` int(11) DEFAULT NULL",
                'actor_user_id' => "ALTER TABLE `lead_histories` ADD `actor_user_id` int(11) DEFAULT NULL",
                'actor_name' => "ALTER TABLE `lead_histories` ADD `actor_name` varchar(191) DEFAULT NULL",
                'actor_role' => "ALTER TABLE `lead_histories` ADD `actor_role` varchar(191) DEFAULT NULL",
                'entry_type' => "ALTER TABLE `lead_histories` ADD `entry_type` varchar(50) NOT NULL DEFAULT 'change'",
                'title' => "ALTER TABLE `lead_histories` ADD `title` varchar(255) DEFAULT NULL",
                'field_name' => "ALTER TABLE `lead_histories` ADD `field_name` varchar(100) DEFAULT NULL",
                'field_label' => "ALTER TABLE `lead_histories` ADD `field_label` varchar(191) DEFAULT NULL",
                'old_value' => "ALTER TABLE `lead_histories` ADD `old_value` text NULL",
                'new_value' => "ALTER TABLE `lead_histories` ADD `new_value` text NULL",
                'recipient' => "ALTER TABLE `lead_histories` ADD `recipient` varchar(255) DEFAULT NULL",
                'message_body' => "ALTER TABLE `lead_histories` ADD `message_body` longtext NULL",
                'meta_json' => "ALTER TABLE `lead_histories` ADD `meta_json` longtext NULL"
            );
            foreach($requiredColumns as $columnName => $sql)
            {
                $exists = (array)$this->Lead->query("SHOW COLUMNS FROM `lead_histories` LIKE '".addslashes($columnName)."'");
                if(empty($exists))
                {
                    $this->Lead->query($sql);
                }
            }

            $LeadHistory = ClassRegistry::init('LeadHistory');
            if($LeadHistory)
            {
                $LeadHistory->cacheSources = false;
            }
        }
        catch(Exception $e)
        {
        }
    }

    protected function _ensureLeadSmsScheduleSchema()
    {
        try
        {
            $LeadSmsSchedule = ClassRegistry::init('LeadSmsSchedule');
            if($LeadSmsSchedule && method_exists($LeadSmsSchedule, 'ensureSchema'))
            {
                $LeadSmsSchedule->ensureSchema();
            }
        }
        catch(Exception $e)
        {
        }
    }

    protected function _isGlobalAdminUser()
    {
        return $this->_isSuperAdminSession();
    }

    protected function _leadPermissionCondition($id = null)
    {
        $userTypeKey = strtoupper(trim((string)$this->userType));
        $ugroupId = isset($this->adminValue['User']['ugroup_id']) ? (int)$this->adminValue['User']['ugroup_id'] : 0;
        $ugroupName = isset($this->adminValue['Ugroup']['name']) ? strtolower(trim((string)$this->adminValue['Ugroup']['name'])) : '';
        if($userTypeKey === 'AGT' || $ugroupId === 2 || $ugroupName === 'agent')
        {
            $uid = (int)$this->luserId;
            if($id !== null && (int)$id > 0)
            {
                return array(
                    'Lead.id' => (int)$id,
                    'Lead.user_id' => $uid
                );
            }
            return array(
                'Lead.user_id' => $uid
            );
        }
        if($ugroupId === 8 || $ugroupName === 'crm')
        {
            if($id !== null && (int)$id > 0)
            {
                return array('Lead.id' => (int)$id);
            }
            if((int)$this->agencyId > 0 && !$this->_isGlobalAdminUser())
            {
                return array('Lead.project_id' => (int)$this->agencyId);
            }
            return array();
        }
        return $this->CustomFunction->userWisePermission($id,$this->luserId,$this->userType,$this->agencyId,'Lead.project_id','Lead.user_id');
    }

    protected function _resolveProjectIdFromInput($requestedProjectId)
    {
        $requestedProjectId = (int)$requestedProjectId;
        if(!$this->_isGlobalAdminUser())
        {
            return (int)$this->agencyId;
        }
        return $requestedProjectId > 0 ? $requestedProjectId : 0;
    }

    protected function _resolveUserIdFromInput($requestedUserId, $projectId, $allowedUgroupId = 0)
    {
        $this->loadModel('User');
        if($this->userType=='AGT')
        {
            $currUser = $this->User->find('first', array(
                'fields' => array('User.id', 'User.login_status'),
                'conditions' => array('User.id' => (int)$this->luserId),
                'recursive' => -1
            ));
            if(!empty($currUser['User']['login_status']) && strcasecmp(trim((string)$currUser['User']['login_status']), 'Blocked') === 0)
            {
                return 0;
            }
            return (int)$this->luserId;
        }
        $requestedUserId = (int)$requestedUserId;
        $scopeProjectId = $this->_isGlobalAdminUser() ? 0 : (int)$projectId;
        $lookupConditions = array($this->User->assignableScopeConditions($scopeProjectId,'User'));
        $allowedUgroupId = (int)$allowedUgroupId;
        if($allowedUgroupId > 0)
        {
            $lookupConditions[] = array('User.ugroup_id' => $allowedUgroupId);
        }
        if($requestedUserId > 0)
        {
            $selectedUserConditions = $lookupConditions;
            $selectedUserConditions[] = array('User.id' => $requestedUserId);
            $selectedUser = $this->User->find('first', array(
                'fields' => array('User.id', 'User.login_status'),
                'conditions' => $selectedUserConditions,
                'recursive' => -1
            ));
            if(!empty($selectedUser['User']['id']) && (empty($selectedUser['User']['login_status']) || strcasecmp(trim((string)$selectedUser['User']['login_status']), 'Blocked') !== 0))
            {
                return (int)$selectedUser['User']['id'];
            }
        }
        $agent = $this->User->find('first', array(
            'fields' => array('User.id', 'User.login_status'),
            'conditions' => $lookupConditions,
            'order' => array('User.id' => 'asc'),
            'recursive' => -1
        ));
        if(!empty($agent['User']['id']) && (empty($agent['User']['login_status']) || strcasecmp(trim((string)$agent['User']['login_status']), 'Blocked') !== 0))
        {
            return (int)$agent['User']['id'];
        }
        $luserCheck = $this->User->find('first', array(
            'fields' => array('User.id', 'User.login_status'),
            'conditions' => array('User.id' => (int)$this->luserId),
            'recursive' => -1
        ));
        if(!empty($luserCheck['User']['login_status']) && strcasecmp(trim((string)$luserCheck['User']['login_status']), 'Blocked') === 0)
        {
            return 1;
        }
        return (int)$this->luserId;
    }

    protected function _currentLeadImportUserLevelId()
    {
        if(!empty($this->adminValue['User']['ugroup_id']))
        {
            return (int)$this->adminValue['User']['ugroup_id'];
        }
        $sessionUser = (array)$this->Session->read('User');
        if(!empty($sessionUser['User']['ugroup_id']))
        {
            return (int)$sessionUser['User']['ugroup_id'];
        }
        return 0;
    }

    protected function _leadImportUserLevelOptions()
    {
        $currentUserLevelId = $this->_currentLeadImportUserLevelId();
        $this->loadModel('Ugroup');
        if(!$this->_isGlobalAdminUser())
        {
            if($currentUserLevelId <= 0)
            {
                return array();
            }
            return (array)$this->Ugroup->find('list', array(
                'fields' => array('Ugroup.id', 'Ugroup.name'),
                'conditions' => array('Ugroup.id' => $currentUserLevelId),
                'order' => array('Ugroup.name' => 'asc'),
                'recursive' => -1
            ));
        }
        $this->loadModel('User');
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

    protected function _normalizeLeadImportUserLevelId($requestedUgroupId)
    {
        $requestedValue = trim((string)$requestedUgroupId);
        if($requestedValue === '')
        {
            return 0;
        }
        $this->loadModel('User');
        $allowedGroupIds = array_values(array_unique(array_map('intval', (array)$this->User->assignableGroupIds())));
        if(ctype_digit($requestedValue))
        {
            $requestedId = (int)$requestedValue;
            return in_array($requestedId, $allowedGroupIds, true) ? $requestedId : 0;
        }
        $this->loadModel('Ugroup');
        $groupRow = $this->Ugroup->find('first', array(
            'fields' => array('Ugroup.id'),
            'conditions' => array(
                'OR' => array(
                    'LOWER(Ugroup.name)' => strtolower($requestedValue),
                    'LOWER(Ugroup.type)' => strtolower($requestedValue)
                )
            ),
            'recursive' => -1
        ));
        $requestedId = !empty($groupRow['Ugroup']['id']) ? (int)$groupRow['Ugroup']['id'] : 0;
        return ($requestedId > 0 && in_array($requestedId, $allowedGroupIds, true)) ? $requestedId : 0;
    }

    protected function _leadImportAgentOptions($projectId = 0, $ugroupId = 0)
    {
        $projectId = (int)$projectId;
        $ugroupId = (int)$ugroupId;
        $this->loadModel('User');
        if(!$this->_isGlobalAdminUser())
        {
            return (array)$this->User->find('list', array(
                'fields' => array('User.id','User.name'),
                'conditions' => array(
                    'User.id' => (int)$this->luserId,
                    'User.deleted IS NULL'
                ),
                'order' => array('User.name' => 'asc'),
                'recursive' => -1
            ));
        }
        $conditions = array($this->User->assignableScopeConditions($projectId,'User'));
        if($ugroupId > 0)
        {
            $conditions[] = array('User.ugroup_id' => $ugroupId);
        }
        if($this->userType === 'AGT')
        {
            $conditions[] = array('User.id' => (int)$this->luserId);
        }
        return (array)$this->User->find('list', array(
            'fields' => array('User.id','User.name'),
            'conditions' => $conditions,
            'order' => array('User.name' => 'asc')
        ));
    }

    protected function _defaultLeadStatusId()
    {
        $this->loadModel('Status');
        $row = $this->Status->find('first', array(
            'fields' => array('Status.id'),
            'order' => array('Status.id' => 'asc'),
            'recursive' => -1
        ));
        return !empty($row['Status']['id']) ? (int)$row['Status']['id'] : 1;
    }

    protected function _resolveLeadStatusId($statusValue)
    {
        $statusValue = trim((string)$statusValue);
        if($statusValue === '')
        {
            return $this->_defaultLeadStatusId();
        }
        if(ctype_digit($statusValue) && (int)$statusValue > 0)
        {
            return (int)$statusValue;
        }
        $this->loadModel('Status');
        $row = $this->Status->find('first', array(
            'fields' => array('Status.id'),
            'conditions' => array('LOWER(Status.name)' => strtolower($statusValue)),
            'recursive' => -1
        ));
        if(!empty($row['Status']['id']))
        {
            return (int)$row['Status']['id'];
        }
        return $this->_defaultLeadStatusId();
    }

    protected function _normalizeLeadTemperature($temperatureValue)
    {
        $temperatureValue = strtolower(trim((string)$temperatureValue));
        if(in_array($temperatureValue, array('hot','warm','cold'), true))
        {
            return $temperatureValue;
        }
        return 'warm';
    }

    protected function _resolveLeadPropertyId($projectId, $propertyValue)
    {
        $propertyValue = trim((string)$propertyValue);
        if($propertyValue === '')
        {
            return 0;
        }
        if(ctype_digit($propertyValue) && (int)$propertyValue > 0)
        {
            return (int)$propertyValue;
        }
        $this->loadModel('Property');
        $conditions = array('Property.name LIKE' => $propertyValue.'%');
        if((int)$projectId > 0)
        {
            $conditions['Property.project_id'] = (int)$projectId;
        }
        $row = $this->Property->find('first', array(
            'fields' => array('Property.id'),
            'conditions' => $conditions,
            'recursive' => -1
        ));
        if(!empty($row['Property']['id']))
        {
            return (int)$row['Property']['id'];
        }
        return 0;
    }

    protected function _sanitizeMobile($mobileValue)
    {
        $mobileValue = trim((string)$mobileValue);
        $mobileValue = preg_replace('/\s+/', '', $mobileValue);
        return $mobileValue;
    }

    protected function _mobileVariants($mobileValue)
    {
        $clean = preg_replace('/[^0-9]/', '', (string)$mobileValue);
        if($clean === '')
        {
            return array();
        }
        $variants = array($clean);

        // If standard 10 digit (e.g. 3001234567)
        if(strlen($clean) === 10 && substr($clean, 0, 1) === '3')
        {
            $variants[] = '0' . $clean;
            $variants[] = '92' . $clean;
            $variants[] = '+92' . $clean;
            $variants[] = '03' . substr($clean, 1);
        }
        // If 11 digit starting with 0 (e.g. 03001234567)
        elseif(strlen($clean) === 11 && substr($clean, 0, 1) === '0')
        {
            $withoutZero = substr($clean, 1);
            $variants[] = $withoutZero;
            $variants[] = '92' . $withoutZero;
            $variants[] = '+92' . $withoutZero;
        }
        // If 12 digit starting with 92 (e.g. 923001234567)
        elseif(strlen($clean) === 12 && substr($clean, 0, 2) === '92')
        {
            $without92 = substr($clean, 2);
            $variants[] = $without92;
            $variants[] = '0' . $without92;
            $variants[] = '+92' . $without92;
        }

        // Also include original string if it had dashes or spaces
        $orig = trim((string)$mobileValue);
        if($orig !== '')
        {
            $variants[] = $orig;
            $variants[] = str_replace(' ', '', $orig);
            $variants[] = str_replace('-', '', $orig);
        }

        return array_values(array_unique(array_filter($variants)));
    }

    public function detectPhoneRegion($phone)
    {
        $raw = trim((string)$phone);
        if ($raw === '') {
            return array('region' => 'unknown', 'country' => 'Unknown', 'flag' => '', 'badge' => '', 'is_overseas' => false);
        }
        
        $digits = preg_replace('/[^0-9]/', '', $raw);
        if ($digits === '') {
            return array('region' => 'unknown', 'country' => 'Unknown', 'flag' => '', 'badge' => '', 'is_overseas' => false);
        }
        
        // Strip leading 00 (international prefix)
        if (substr($digits, 0, 2) === '00') {
            $digits = substr($digits, 2);
        }
        
        // Handle 092... (common PK prefix)
        if (substr($digits, 0, 3) === '092') {
            $digits = substr($digits, 1);
        }
        
        // Pakistan local format
        if (preg_match('/^92[0-9]{10}$/', $digits) ||
            preg_match('/^03[0-9]{9}$/', $digits) ||
            preg_match('/^3[0-9]{9}$/', $digits) ||
            preg_match('/^0[2-9][0-9]{8,9}$/', $digits)) {
            return array('region' => 'local', 'country' => 'Pakistan', 'flag' => '🇵🇰', 'badge' => '🇵🇰 Pakistan', 'is_overseas' => false);
        }
        
        if (substr($digits, 0, 1) === '0' && strlen($digits) >= 11) {
            $digits = substr($digits, 1);
        }
        
        // International Country Match
        if (substr($digits, 0, 3) === '971') return array('region' => 'overseas', 'country' => 'UAE (Dubai)', 'flag' => '🇦🇪', 'badge' => '🇦🇪 UAE', 'is_overseas' => true);
        if (substr($digits, 0, 3) === '966') return array('region' => 'overseas', 'country' => 'Saudi Arabia', 'flag' => '🇸🇦', 'badge' => '🇸🇦 KSA', 'is_overseas' => true);
        if (substr($digits, 0, 2) === '44') return array('region' => 'overseas', 'country' => 'United Kingdom', 'flag' => '🇬🇧', 'badge' => '🇬🇧 UK', 'is_overseas' => true);
        if (substr($digits, 0, 1) === '1' && strlen($digits) === 11) return array('region' => 'overseas', 'country' => 'USA / Canada', 'flag' => '🇺🇸', 'badge' => '🇺🇸 USA', 'is_overseas' => true);
        if (substr($digits, 0, 3) === '968') return array('region' => 'overseas', 'country' => 'Oman', 'flag' => '🇴🇲', 'badge' => '🇴🇲 Oman', 'is_overseas' => true);
        if (substr($digits, 0, 3) === '974') return array('region' => 'overseas', 'country' => 'Qatar', 'flag' => '🇶🇦', 'badge' => '🇶🇦 Qatar', 'is_overseas' => true);
        if (substr($digits, 0, 3) === '973') return array('region' => 'overseas', 'country' => 'Bahrain', 'flag' => '🇧🇭', 'badge' => '🇧🇭 Bahrain', 'is_overseas' => true);
        if (substr($digits, 0, 3) === '965') return array('region' => 'overseas', 'country' => 'Kuwait', 'flag' => '🇰🇼', 'badge' => '🇰🇼 Kuwait', 'is_overseas' => true);
        if (substr($digits, 0, 2) === '61') return array('region' => 'overseas', 'country' => 'Australia', 'flag' => '🇦🇺', 'badge' => '🇦🇺 Australia', 'is_overseas' => true);
        if (substr($digits, 0, 2) === '39') return array('region' => 'overseas', 'country' => 'Italy', 'flag' => '🇮🇹', 'badge' => '🇮🇹 Italy', 'is_overseas' => true);
        if (substr($digits, 0, 2) === '49') return array('region' => 'overseas', 'country' => 'Germany', 'flag' => '🇩🇪', 'badge' => '🇩🇪 Germany', 'is_overseas' => true);
        if (substr($digits, 0, 2) === '34') return array('region' => 'overseas', 'country' => 'Spain', 'flag' => '🇪🇸', 'badge' => '🇪🇸 Spain', 'is_overseas' => true);
        if (substr($digits, 0, 2) === '33') return array('region' => 'overseas', 'country' => 'France', 'flag' => '🇫🇷', 'badge' => '🇫🇷 France', 'is_overseas' => true);

        return array('region' => 'overseas', 'country' => 'Overseas', 'flag' => '🌍', 'badge' => '🌍 Overseas', 'is_overseas' => true);
    }

    protected function _leadPhoneRegionCondition($phoneRegion = 'all')
    {
        $phoneRegion = strtolower(trim((string)$phoneRegion));
        if ($phoneRegion === 'overseas') {
            return "(Lead.lead_mobile IS NOT NULL AND TRIM(Lead.lead_mobile) != '' AND NOT (REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(Lead.lead_mobile, '+', ''), ' ', ''), '-', ''), '(', ''), ')', '') REGEXP '^(092|0092|92|03|3|042|051|021)[0-9]'))";
        }
        if ($phoneRegion === 'local') {
            return "(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(Lead.lead_mobile, '+', ''), ' ', ''), '-', ''), '(', ''), ')', '') REGEXP '^(092|0092|92|03|3|042|051|021)[0-9]')";
        }
        return null;
    }

    protected function _normalizeEmail($emailValue)
    {
        return strtolower(trim((string)$emailValue));
    }

    protected function _leadDuplicateRows($mobileNo, $emailAddress, $excludeLeadId = 0, $limit = 10)
    {
        $variants = $this->_mobileVariants($mobileNo);
        $emailAddress = $this->_normalizeEmail($emailAddress);
        $orConditions = array();
        if(!empty($variants))
        {
            $orConditions[] = array('Lead.lead_mobile' => $variants);
            $orConditions[] = array('Client.mobile' => $variants);
            $orConditions[] = array('Client.phone' => $variants);
        }
        if($emailAddress !== '')
        {
            $orConditions[] = array('LOWER(Lead.lead_email)' => $emailAddress);
            $orConditions[] = array('LOWER(Client.email)' => $emailAddress);
        }
        if(empty($orConditions))
        {
            return array();
        }
        $conditions = array('OR' => $orConditions);
        $excludeLeadId = (int)$excludeLeadId;
        if($excludeLeadId > 0)
        {
            $conditions['Lead.id <>'] = $excludeLeadId;
        }
        try
        {
            return (array)$this->Lead->find('all', array(
                'fields' => array(
                    'Lead.id','Lead.project_id','Lead.user_id','Lead.status_id','Lead.lead_name','Lead.lead_mobile','Lead.lead_email','Lead.follow_up','Lead.last_contact_date','Lead.remarks','Lead.created',
                    'Client.name','Client.mobile','Client.phone','Client.email',
                    'Project.name','AssignedUser.id','AssignedUser.name','Status.name','Status.color'
                ),
                'joins' => array(
                    array('table'=>'clients','alias'=>'Client','type'=>'LEFT','conditions'=>array('Client.id=Lead.client_id')),
                    array('table'=>'projects','alias'=>'Project','type'=>'LEFT','conditions'=>array('Project.id=Lead.project_id')),
                    array('table'=>'users','alias'=>'AssignedUser','type'=>'LEFT','conditions'=>array('AssignedUser.id=Lead.user_id')),
                    array('table'=>'statuses','alias'=>'Status','type'=>'LEFT','conditions'=>array('Status.id=Lead.status_id'))
                ),
                'conditions' => $conditions,
                'order' => array('Lead.id' => 'desc'),
                'limit' => max(1, (int)$limit),
                'recursive' => -1
            ));
        }
        catch(Exception $e)
        {
            return array();
        }
    }

    protected function _findContactByLeadData($projectId, $mobileNo, $emailAddress, $providedClientId = 0)
    {
        $this->loadModel('Admin.Contact');
        $providedClientId = (int)$providedClientId;
        if($providedClientId > 0)
        {
            $conditions = array('Contact.id' => $providedClientId);
            if((int)$projectId > 0)
            {
                $conditions['Contact.project_id'] = (int)$projectId;
            }
            $row = $this->Contact->find('first', array(
                'fields' => array('Contact.id'),
                'conditions' => $conditions,
                'recursive' => -1
            ));
            if(!empty($row['Contact']['id']))
            {
                return (int)$row['Contact']['id'];
            }
        }

        $orConditions = array();
        if(strlen(trim($mobileNo)) > 0)
        {
            $orConditions[] = array('Contact.mobile' => $mobileNo);
            $orConditions[] = array('Contact.phone' => $mobileNo);
        }
        if(strlen(trim($emailAddress)) > 0)
        {
            $orConditions[] = array('LOWER(Contact.email)' => strtolower($emailAddress));
        }
        if(empty($orConditions))
        {
            return 0;
        }

        $conditions = array('OR' => $orConditions);
        if((int)$projectId > 0)
        {
            $conditions['Contact.project_id'] = (int)$projectId;
        }
        $row = $this->Contact->find('first', array(
            'fields' => array('Contact.id'),
            'conditions' => $conditions,
            'recursive' => -1
        ));
        return !empty($row['Contact']['id']) ? (int)$row['Contact']['id'] : 0;
    }

    protected function _createContactFromLeadData($projectId, $userId, $leadName, $mobileNo, $emailAddress, $leadAddress = '')
    {
        $this->loadModel('Admin.Contact');
        $this->loadModel('Category');

        $category = $this->Category->find('first', array(
            'fields' => array('Category.id'),
            'conditions' => array('Category.type' => NULL),
            'order' => array('Category.id' => 'asc'),
            'recursive' => -1
        ));
        $categoryId = !empty($category['Category']['id']) ? (int)$category['Category']['id'] : 1;
        if($categoryId <= 0)
        {
            $categoryId = 1;
        }

        $plainPassword = (string)rand(100000,999999);
        $data = array(
            'Contact' => array(
                'category_id' => $categoryId,
                'user_id' => (int)$userId,
                'project_id' => (int)$projectId,
                'name' => trim((string)$leadName),
                'mobile' => trim((string)$mobileNo),
                'phone' => trim((string)$mobileNo),
                'email' => trim((string)$emailAddress),
                'address' => trim((string)$leadAddress),
                'password' => $this->_hashPassword($plainPassword),
                'status' => 'Active',
                'reg_status' => 'Live'
            )
        );
        $this->Contact->create();
        if($this->Contact->save($data, array('validate' => false)))
        {
            $newContactId = (int)$this->Contact->id;
            try {
                $this->_dispatchContactRegistrationWelcome($newContactId, $plainPassword, $data['Contact']);
            } catch (Exception $eWelcome) {
                CakeLog::write('error', 'Auto welcome failed for lead-converted contact #' . $newContactId . ': ' . $eWelcome->getMessage());
            }
            return $newContactId;
        }
        return 0;
    }

    protected function _safeFollowUpDateTime($value)
    {
        $value = trim((string)$value);
        if($value === '')
        {
            return null;
        }
        $knownFormats = array(
            'Y-m-d H:i:s',
            'Y-m-d h:i:s A',
            'Y-m-d h:i:s a',
            'Y-m-d H:i',
            'Y/m/d H:i',
            'd-m-Y H:i',
            'd/m/Y H:i',
            'd.m.Y H:i',
            'm/d/Y H:i',
            'm-d-Y H:i',
            'd-m-Y h:i A',
            'd-m-Y h:i a',
            'd/m/Y h:i A',
            'd/m/Y h:i a',
            'm/d/Y h:i A',
            'm/d/Y h:i a',
            'm-d-Y h:i A',
            'm-d-Y h:i a',
            'Y-m-d h:i A',
            'Y-m-d h:i a',
            'Y/m/d h:i A',
            'Y/m/d h:i a'
        );
        foreach($knownFormats as $knownFormat)
        {
            $dateObj = DateTime::createFromFormat($knownFormat, $value);
            if($dateObj instanceof DateTime)
            {
                $errors = DateTime::getLastErrors();
                if((int)$errors['warning_count'] === 0 && (int)$errors['error_count'] === 0)
                {
                    return $dateObj->format('Y-m-d H:i:s');
                }
            }
        }
        $timestamp = strtotime($value);
        if($timestamp === false)
        {
            return null;
        }
        return date('Y-m-d H:i:s',$timestamp);
    }

    protected function _parseCsvRows($filePath)
    {
        $rows = array();
        $handle = fopen($filePath,'r');
        if($handle === false)
        {
            return $rows;
        }
        while(($data = fgetcsv($handle)) !== false)
        {
            if(!is_array($data))
            {
                continue;
            }
            $rows[] = $data;
        }
        fclose($handle);
        return $rows;
    }

    protected function _xlsxColumnToIndex($letters)
    {
        $letters = strtoupper(trim((string)$letters));
        $index = 0;
        $len = strlen($letters);
        for($i=0;$i<$len;$i++)
        {
            $index = ($index * 26) + (ord($letters[$i]) - 64);
        }
        return max(0,$index - 1);
    }

    protected function _parseXlsxRows($filePath)
    {
        $rows = array();
        if(!class_exists('ZipArchive'))
        {
            return $rows;
        }
        $zip = new ZipArchive();
        if($zip->open($filePath) !== true)
        {
            return $rows;
        }

        $sharedStrings = array();
        $sharedXml = $zip->getFromName('xl/sharedStrings.xml');
        if($sharedXml !== false)
        {
            $domShared = new DOMDocument();
            if(@$domShared->loadXML($sharedXml))
            {
                $xpathShared = new DOMXPath($domShared);
                $xpathShared->registerNamespace('a','http://schemas.openxmlformats.org/spreadsheetml/2006/main');
                $siNodes = $xpathShared->query('//a:si');
                foreach($siNodes as $siNode)
                {
                    $parts = $xpathShared->query('.//a:t',$siNode);
                    $text = '';
                    foreach($parts as $partNode)
                    {
                        $text .= (string)$partNode->nodeValue;
                    }
                    $sharedStrings[] = $text;
                }
            }
        }

        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        if($sheetXml === false)
        {
            $zip->close();
            return $rows;
        }
        $zip->close();

        $domSheet = new DOMDocument();
        if(!@$domSheet->loadXML($sheetXml))
        {
            return $rows;
        }
        $xpathSheet = new DOMXPath($domSheet);
        $xpathSheet->registerNamespace('a','http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        $rowNodes = $xpathSheet->query('//a:sheetData/a:row');
        foreach($rowNodes as $rowNode)
        {
            $row = array();
            $currentIndex = 0;
            $cellNodes = $xpathSheet->query('a:c',$rowNode);
            foreach($cellNodes as $cellNode)
            {
                $ref = (string)$cellNode->getAttribute('r');
                $letters = preg_replace('/[^A-Z]/i','',$ref);
                $targetIndex = $this->_xlsxColumnToIndex($letters);
                while($currentIndex < $targetIndex)
                {
                    $row[] = '';
                    $currentIndex++;
                }

                $cellType = (string)$cellNode->getAttribute('t');
                $cellValue = '';
                if($cellType === 's')
                {
                    $valueNode = $xpathSheet->query('a:v',$cellNode)->item(0);
                    $sharedIndex = $valueNode ? (int)$valueNode->nodeValue : -1;
                    $cellValue = isset($sharedStrings[$sharedIndex]) ? $sharedStrings[$sharedIndex] : '';
                }
                elseif($cellType === 'inlineStr')
                {
                    $textNode = $xpathSheet->query('a:is/a:t',$cellNode)->item(0);
                    $cellValue = $textNode ? (string)$textNode->nodeValue : '';
                }
                else
                {
                    $valueNode = $xpathSheet->query('a:v',$cellNode)->item(0);
                    $cellValue = $valueNode ? (string)$valueNode->nodeValue : '';
                }

                $row[] = $cellValue;
                $currentIndex++;
            }
            $rows[] = $row;
        }
        return $rows;
    }

    protected function _parseImportRows($filePath, $originalName)
    {
        $extension = strtolower(pathinfo((string)$originalName, PATHINFO_EXTENSION));
        if($extension === 'xlsx')
        {
            return $this->_parseXlsxRows($filePath);
        }
        return $this->_parseCsvRows($filePath);
    }

    protected function _mapImportHeaders($headers)
    {
        $aliasMap = array(
            'lead_name' => 'lead_name',
            'name' => 'lead_name',
            'client_name' => 'lead_name',
            'contact_name' => 'lead_name',
            'lead_mobile' => 'lead_mobile',
            'mobile' => 'lead_mobile',
            'phone' => 'lead_mobile',
            'lead_phone' => 'lead_mobile',
            'lead_email' => 'lead_email',
            'email' => 'lead_email',
            'lead_address' => 'lead_address',
            'address' => 'lead_address',
            'lead_city' => 'lead_city',
            'city' => 'lead_city',
            'lead_budget' => 'lead_budget',
            'budget' => 'lead_budget',
            'lead_language' => 'lead_language',
            'language' => 'lead_language',
            'lead_source' => 'lead_source',
            'source' => 'lead_source',
            'source_form_id' => 'source_form_id',
            'form_id' => 'source_form_id',
            'source_ad_id' => 'source_ad_id',
            'ad_id' => 'source_ad_id',
            'utm_source' => 'utm_source',
            'utm_campaign' => 'utm_campaign',
            'utm_medium' => 'utm_medium',
            'utm_term' => 'utm_term',
            'utm_content' => 'utm_content',
            'last_contact_date' => 'last_contact_date',
            'last_contact' => 'last_contact_date',
            'contact_date' => 'last_contact_date',
            'lead_temperature' => 'lead_temperature',
            'temperature' => 'lead_temperature',
            'priority' => 'lead_temperature',
            'badge' => 'lead_temperature',
            'status' => 'status',
            'status_name' => 'status',
            'status_id' => 'status',
            'follow_up' => 'follow_up',
            'followup' => 'follow_up',
            'next_follow_up' => 'follow_up',
            'remarks' => 'remarks',
            'remark' => 'remarks',
            'notes' => 'remarks',
            'property' => 'property',
            'property_name' => 'property',
            'property_id' => 'property',
            'user_level' => 'ugroup_id',
            'user_role' => 'ugroup_id',
            'role' => 'ugroup_id',
            'level' => 'ugroup_id',
            'ugroup_id' => 'ugroup_id',
            'client_id' => 'client_id',
            'project_id' => 'project_id',
            'agency_id' => 'project_id',
            'user_id' => 'user_id',
            'agent_id' => 'user_id'
        );

        $map = array();
        foreach((array)$headers as $index => $rawHeader)
        {
            $normalized = strtolower(trim((string)$rawHeader));
            $normalized = preg_replace('/[^a-z0-9]+/','_',$normalized);
            $normalized = trim($normalized,'_');
            if($normalized === '')
            {
                continue;
            }
            if(isset($aliasMap[$normalized]))
            {
                $map[$index] = $aliasMap[$normalized];
            }
        }
        return $map;
    }

    protected function _safeLeadBudgetValue($value)
    {
        if($value === null)
        {
            return null;
        }
        $text = trim((string)$value);
        if($text === '')
        {
            return null;
        }
        $text = preg_replace('/[^0-9\.\-]/', '', $text);
        if($text === '' || !is_numeric($text))
        {
            return null;
        }
        return round((float)$text, 2);
    }

    protected function _buildLeadPayload($formData, $projectId, $userId, $defaultStatusId = null)
    {
        if($defaultStatusId === null)
        {
            $defaultStatusId = $this->_defaultLeadStatusId();
        }
        $leadName = trim((string)$formData['lead_name']);
        $leadMobile = $this->_sanitizeMobile($formData['lead_mobile']);
        $leadEmail = $this->_normalizeEmail($formData['lead_email']);
        $leadAddress = trim((string)$formData['lead_address']);
        $leadCity = trim((string)(isset($formData['lead_city']) ? $formData['lead_city'] : (isset($formData['city']) ? $formData['city'] : '')));
        $leadBudget = $this->_safeLeadBudgetValue(isset($formData['lead_budget']) ? $formData['lead_budget'] : (isset($formData['budget']) ? $formData['budget'] : null));
        $leadLanguage = trim((string)(isset($formData['lead_language']) ? $formData['lead_language'] : (isset($formData['language']) ? $formData['language'] : '')));
        $leadSource = trim((string)$formData['lead_source']);
        $sourceFormId = trim((string)(isset($formData['source_form_id']) ? $formData['source_form_id'] : (isset($formData['form_id']) ? $formData['form_id'] : '')));
        $sourceAdId = trim((string)(isset($formData['source_ad_id']) ? $formData['source_ad_id'] : (isset($formData['ad_id']) ? $formData['ad_id'] : '')));
        $utmSource = trim((string)(isset($formData['utm_source']) ? $formData['utm_source'] : ''));
        $utmCampaign = trim((string)(isset($formData['utm_campaign']) ? $formData['utm_campaign'] : ''));
        $utmMedium = trim((string)(isset($formData['utm_medium']) ? $formData['utm_medium'] : ''));
        $utmTerm = trim((string)(isset($formData['utm_term']) ? $formData['utm_term'] : ''));
        $utmContent = trim((string)(isset($formData['utm_content']) ? $formData['utm_content'] : ''));
        $statusId = $this->_resolveLeadStatusId(isset($formData['status_id']) ? $formData['status_id'] : $defaultStatusId);
        $lastContactDate = $this->_leadNormalizeDateInput(isset($formData['last_contact_date']) ? $formData['last_contact_date'] : '');
        $followUp = $this->_safeFollowUpDateTime(isset($formData['follow_up']) ? $formData['follow_up'] : '');
        $leadTemperature = $this->_normalizeLeadTemperature(isset($formData['lead_temperature']) ? $formData['lead_temperature'] : 'warm');
        $propertyId = $this->_resolveLeadPropertyId($projectId, isset($formData['property_id']) ? $formData['property_id'] : '');
        $remarks = trim((string)$formData['remarks']);
        $createContactFlagProvided = array_key_exists('create_contact', (array)$formData);
        $autoCreateContact = !empty($formData['create_contact']) ? 1 : 0;
        $allowContactLinking = (!$createContactFlagProvided || $autoCreateContact === 1);
        $clientId = 0;
        if($allowContactLinking)
        {
            $clientId = $this->_findContactByLeadData($projectId, $leadMobile, $leadEmail, isset($formData['client_id']) ? $formData['client_id'] : 0);
            if($clientId <= 0 && $leadName!=='')
            {
                $createdClientId = $this->_createContactFromLeadData($projectId, $userId, $leadName, $leadMobile, $leadEmail, $leadAddress);
                if($createdClientId > 0)
                {
                    $clientId = $createdClientId;
                }
            }
        }

        $payload = array(
            'project_id' => (int)$projectId,
            'user_id' => (int)$userId,
            'client_id' => ((int)$clientId > 0 ? (int)$clientId : null),
            'property_id' => ((int)$propertyId > 0 ? (int)$propertyId : null),
            'status_id' => (int)$statusId,
            'last_contact_date' => ($lastContactDate !== '' ? $lastContactDate : null),
            'follow_up' => $followUp,
            'remarks' => $remarks,
            'lead_name' => $leadName,
            'lead_mobile' => $leadMobile,
            'lead_email' => $leadEmail,
            'lead_address' => $leadAddress,
            'lead_city' => ($leadCity !== '' ? $leadCity : null),
            'lead_budget' => $leadBudget,
            'lead_language' => ($leadLanguage !== '' ? $leadLanguage : null),
            'lead_source' => $leadSource,
            'source_form_id' => ($sourceFormId !== '' ? $sourceFormId : null),
            'source_ad_id' => ($sourceAdId !== '' ? $sourceAdId : null),
            'utm_source' => ($utmSource !== '' ? $utmSource : null),
            'utm_campaign' => ($utmCampaign !== '' ? $utmCampaign : null),
            'utm_medium' => ($utmMedium !== '' ? $utmMedium : null),
            'utm_term' => ($utmTerm !== '' ? $utmTerm : null),
            'utm_content' => ($utmContent !== '' ? $utmContent : null),
            'lead_temperature' => $leadTemperature
        );
        return $payload;
    }

    protected function _applyCrmAutomationToLeadPayload($payload = array(), $scope = 'manual', $excludeLeadId = 0)
    {
        $payload = (array)$payload;
        try
        {
            $CrmAutomationSetting = ClassRegistry::init('CrmAutomationSetting');
            if($CrmAutomationSetting)
            {
                $CrmAutomationSetting->ensureSchema();
                return (array)$CrmAutomationSetting->applyToLeadPayload($payload, array(
                    'scope' => $scope,
                    'exclude_lead_id' => (int)$excludeLeadId
                ));
            }
        }
        catch(Exception $e)
        {
        }
        return array(
            'success' => true,
            'payload' => $payload,
            'duplicate_lead_id' => 0
        );
    }

    protected function _syncLeadGoogleCalendarEvent($leadId = 0)
    {
        $leadId = (int)$leadId;
        if($leadId <= 0)
        {
            return;
        }
        try
        {
            $CrmGoogleCalendar = ClassRegistry::init('CrmGoogleCalendar');
            if($CrmGoogleCalendar)
            {
                $CrmGoogleCalendar->ensureSchema();
                $CrmGoogleCalendar->syncLeadFollowUpEventByLeadId($leadId);
            }
        }
        catch(Exception $e)
        {
        }
    }

    protected function _syncLeadGoogleCalendarEvents($leadIds = array())
    {
        foreach((array)$leadIds as $leadId)
        {
            $this->_syncLeadGoogleCalendarEvent((int)$leadId);
        }
    }

    protected function _removeLeadGoogleCalendarEvent($leadId = 0)
    {
        $leadId = (int)$leadId;
        if($leadId <= 0)
        {
            return;
        }
        try
        {
            $CrmGoogleCalendar = ClassRegistry::init('CrmGoogleCalendar');
            if($CrmGoogleCalendar)
            {
                $CrmGoogleCalendar->ensureSchema();
                $CrmGoogleCalendar->removeLeadFollowUpEventByLeadId($leadId);
            }
        }
        catch(Exception $e)
        {
        }
    }

    protected function _leadHistoryModel()
    {
        try
        {
            $this->_ensureLeadHistorySchema();
            $LeadHistory = ClassRegistry::init('LeadHistory');
            if($LeadHistory)
            {
                $LeadHistory->ensureSchema();
                return $LeadHistory;
            }
        }
        catch(Exception $e)
        {
        }
        return null;
    }

    protected function _leadHistoryActorContext()
    {
        $sessionUser = (array)$this->Session->read('User');
        $actorUserId = !empty($this->adminValue['User']['id']) ? (int)$this->adminValue['User']['id'] : (!empty($sessionUser['User']['id']) ? (int)$sessionUser['User']['id'] : 0);
        $actorName = !empty($this->adminValue['User']['name']) ? trim((string)$this->adminValue['User']['name']) : trim((string)(!empty($sessionUser['User']['name']) ? $sessionUser['User']['name'] : ''));
        $actorRole = !empty($this->adminValue['Ugroup']['name']) ? trim((string)$this->adminValue['Ugroup']['name']) : trim((string)(!empty($sessionUser['Ugroup']['name']) ? $sessionUser['Ugroup']['name'] : ''));

        if(($actorName === '' || $actorRole === '') && $actorUserId > 0)
        {
            try
            {
                $this->loadModel('User');
                $actorRow = (array)$this->User->find('first', array(
                    'fields' => array('User.id','User.name','Ugroup.name'),
                    'conditions' => array('User.id' => $actorUserId),
                    'recursive' => 0
                ));
                if($actorName === '' && !empty($actorRow['User']['name']))
                {
                    $actorName = trim((string)$actorRow['User']['name']);
                }
                if($actorRole === '' && !empty($actorRow['Ugroup']['name']))
                {
                    $actorRole = trim((string)$actorRow['Ugroup']['name']);
                }
            }
            catch(Exception $e)
            {
            }
        }

        if($actorName === '')
        {
            $actorName = 'System';
        }

        return array(
            'actor_user_id' => $actorUserId,
            'actor_name' => $actorName,
            'actor_role' => $actorRole
        );
    }

    protected function _leadHistoryComparableValue($fieldName, $value)
    {
        if($value === null)
        {
            return '';
        }
        if(is_string($value))
        {
            $value = trim($value);
        }
        if($value === '' || $value === '0000-00-00' || $value === '0000-00-00 00:00:00')
        {
            return '';
        }
        switch((string)$fieldName)
        {
            case 'project_id':
            case 'user_id':
            case 'client_id':
            case 'property_id':
            case 'status_id':
            case 'type_id':
                return (string)(int)$value;
            case 'lead_budget':
                return is_numeric($value) ? number_format((float)$value, 2, '.', '') : trim((string)$value);
            case 'last_contact_date':
                $ts = strtotime((string)$value);
                return $ts ? date('Y-m-d', $ts) : trim((string)$value);
            case 'follow_up':
                $ts = strtotime((string)$value);
                return $ts ? date('Y-m-d H:i:s', $ts) : trim((string)$value);
            default:
                return is_scalar($value) ? trim((string)$value) : json_encode($value);
        }
    }

    protected function _leadHistoryLeadRow($leadId = 0)
    {
        $leadId = (int)$leadId;
        if($leadId <= 0)
        {
            return array();
        }
        return (array)$this->Lead->find('first', array(
            'conditions' => array('Lead.id' => $leadId),
            'recursive' => -1
        ));
    }

    protected function _logLeadHistoryCreate($leadId = 0, $title = 'Lead Created')
    {
        $leadId = (int)$leadId;
        if($leadId <= 0)
        {
            return;
        }
        $LeadHistory = $this->_leadHistoryModel();
        if(!$LeadHistory)
        {
            return;
        }
        $leadRow = $this->_leadHistoryLeadRow($leadId);
        if(empty($leadRow['Lead']['id']))
        {
            return;
        }
        $actor = $this->_leadHistoryActorContext();
        $LeadHistory->createEntry(array_merge($actor, array(
            'lead_id' => $leadId,
            'project_id' => !empty($leadRow['Lead']['project_id']) ? (int)$leadRow['Lead']['project_id'] : 0,
            'lead_user_id' => !empty($leadRow['Lead']['user_id']) ? (int)$leadRow['Lead']['user_id'] : 0,
            'entry_type' => 'create',
            'title' => $title,
            'message_body' => $LeadHistory->buildCreateSummary($leadRow),
            'meta_json' => array('source_action' => (string)$this->request->action)
        )));

        // Real-time in-app notification dispatch for assigned agent
        $assignedUserId = !empty($leadRow['Lead']['user_id']) ? (int)$leadRow['Lead']['user_id'] : 0;
        if($assignedUserId > 0)
        {
            $leadTitle = !empty($leadRow['Lead']['lead_name']) ? $leadRow['Lead']['lead_name'] : (!empty($leadRow['Client']['name']) ? $leadRow['Client']['name'] : ('Lead #' . $leadId));
            $phone = !empty($leadRow['Lead']['lead_mobile']) ? $leadRow['Lead']['lead_mobile'] : (!empty($leadRow['Client']['mobile']) ? $leadRow['Client']['mobile'] : '');
            $msg = sprintf(__('New lead assigned to you: %s (%s)'), $leadTitle, $phone);
            $actionUrl = '/admin/Leads/leadview/' . $leadId;
            $this->sendNotification($assignedUserId, 'New Lead Assigned: ' . $leadTitle, $msg, 'lead_assigned', $actionUrl, 'fa-user-plus', 'high', 'lead_assigned_' . $leadId . '_' . $assignedUserId);
        }
    }

    protected function _logLeadHistoryChanges($leadId = 0, $beforeLeadRow = array(), $afterLeadData = array())
    {
        $leadId = (int)$leadId;
        if($leadId <= 0)
        {
            return;
        }
        $LeadHistory = $this->_leadHistoryModel();
        if(!$LeadHistory)
        {
            return;
        }
        $afterLeadRow = $this->_leadHistoryLeadRow($leadId);
        if(empty($afterLeadRow['Lead']['id']))
        {
            return;
        }

        $beforeLead = isset($beforeLeadRow['Lead']) ? (array)$beforeLeadRow['Lead'] : (array)$beforeLeadRow;
        $afterLead = isset($afterLeadRow['Lead']) ? (array)$afterLeadRow['Lead'] : array();
        $afterLeadData = isset($afterLeadData['Lead']) ? (array)$afterLeadData['Lead'] : (array)$afterLeadData;
        $ignoredFields = (array)$LeadHistory->ignoredFields();
        $compareFields = array();
        foreach(array_keys($afterLeadData) as $fieldName)
        {
            $fieldName = trim((string)$fieldName);
            if($fieldName === '' || in_array($fieldName, $ignoredFields, true))
            {
                continue;
            }
            $compareFields[$fieldName] = $fieldName;
        }
        if(empty($compareFields))
        {
            return;
        }

        $actor = $this->_leadHistoryActorContext();
        $changedItems = array();
        foreach($compareFields as $fieldName)
        {
            $oldComparable = $this->_leadHistoryComparableValue($fieldName, isset($beforeLead[$fieldName]) ? $beforeLead[$fieldName] : null);
            $newComparable = $this->_leadHistoryComparableValue($fieldName, isset($afterLead[$fieldName]) ? $afterLead[$fieldName] : null);
            if($oldComparable === $newComparable)
            {
                continue;
            }
            $fieldLabel = $LeadHistory->fieldLabel($fieldName);
            $changedItems[] = array(
                'field_name' => $fieldName,
                'field_label' => $fieldLabel,
                'old_value' => $LeadHistory->displayValue($fieldName, isset($beforeLead[$fieldName]) ? $beforeLead[$fieldName] : null),
                'new_value' => $LeadHistory->displayValue($fieldName, isset($afterLead[$fieldName]) ? $afterLead[$fieldName] : null)
            );
        }

        if(empty($changedItems))
        {
            return;
        }

        $detailLines = array();
        foreach($changedItems as $changedItem)
        {
            $oldText = trim((string)$changedItem['old_value']) !== '' ? trim((string)$changedItem['old_value']) : '-';
            $newText = trim((string)$changedItem['new_value']) !== '' ? trim((string)$changedItem['new_value']) : '-';
            if($oldText === '-' && $newText !== '-')
            {
                $detailLines[] = $changedItem['field_label'].': '.$newText;
            }
            else
            {
                $detailLines[] = $changedItem['field_label'].': '.$oldText.' -> '.$newText;
            }
        }

        $singleItem = count($changedItems) === 1 ? $changedItems[0] : array();
        $LeadHistory->createEntry(array_merge($actor, array(
            'lead_id' => $leadId,
            'project_id' => !empty($afterLead['project_id']) ? (int)$afterLead['project_id'] : 0,
            'lead_user_id' => !empty($afterLead['user_id']) ? (int)$afterLead['user_id'] : 0,
            'entry_type' => 'change',
            'title' => !empty($singleItem['field_label']) ? $singleItem['field_label'].' Updated' : 'Lead Updated',
            'field_name' => !empty($singleItem['field_name']) ? $singleItem['field_name'] : 'multiple',
            'field_label' => !empty($singleItem['field_label']) ? $singleItem['field_label'] : 'Multiple Changes',
            'old_value' => !empty($singleItem) ? $singleItem['old_value'] : null,
            'new_value' => !empty($singleItem) ? $singleItem['new_value'] : null,
            'message_body' => implode("\n", $detailLines),
            'meta_json' => array(
                'source_action' => (string)$this->request->action,
                'change_count' => count($changedItems)
            )
        )));

        // Real-time notification dispatches
        $assignedUserId = !empty($afterLead['user_id']) ? (int)$afterLead['user_id'] : 0;
        $leadTitle = !empty($afterLead['lead_name']) ? $afterLead['lead_name'] : ('Lead #' . $leadId);
        $actionUrl = '/admin/Leads/leadview/' . $leadId;

        // 1. Reassignment to agent
        if(!empty($afterLead['user_id']))
        {
            $oldUserId = !empty($beforeLead['user_id']) ? (int)$beforeLead['user_id'] : 0;
            $newUserId = (int)$afterLead['user_id'];
            if($newUserId > 0 && $oldUserId !== $newUserId)
            {
                $msg = sprintf(__('Lead #%d (%s) has been assigned to you.'), $leadId, $leadTitle);
                $this->sendNotification($newUserId, 'Lead Assigned: ' . $leadTitle, $msg, 'lead_assigned', $actionUrl, 'fa-user-plus', 'high', 'lead_reassigned_' . $leadId . '_' . $newUserId . '_' . date('YmdH'));
            }
        }

        // 2. Status change or Deal Won
        if(isset($afterLead['lead_status_id']) && isset($beforeLead['lead_status_id']) && (int)$afterLead['lead_status_id'] !== (int)$beforeLead['lead_status_id'])
        {
            if($assignedUserId > 0)
            {
                $oldStatusText = '';
                $newStatusText = '';
                foreach($changedItems as $ci) {
                    if(!empty($ci['field_name']) && $ci['field_name'] === 'lead_status_id') {
                        $oldStatusText = !empty($ci['old_value']) ? $ci['old_value'] : '';
                        $newStatusText = !empty($ci['new_value']) ? $ci['new_value'] : '';
                        break;
                    }
                }
                $isWon = (stripos($newStatusText, 'won') !== false || stripos($newStatusText, 'deal') !== false || stripos($newStatusText, 'book') !== false);
                $notifType = $isWon ? 'deal_won' : 'status_change';
                $priority = $isWon ? 'urgent' : 'normal';
                $icon = $isWon ? 'fa-trophy' : 'fa-exchange';
                $notifTitle = $isWon ? ('Deal Won! ' . $leadTitle) : ('Status Updated: ' . $leadTitle);
                $notifMsg = sprintf(__('Status changed from "%s" to "%s" for %s.'), $oldStatusText ?: 'previous', $newStatusText ?: 'new', $leadTitle);
                $this->sendNotification($assignedUserId, $notifTitle, $notifMsg, $notifType, $actionUrl, $icon, $priority);
            }
        }
    }

    protected function _leadHistoryMetaArray($metaJson = null)
    {
        if(is_array($metaJson))
        {
            return $metaJson;
        }
        $metaJson = trim((string)$metaJson);
        if($metaJson === '')
        {
            return array();
        }
        $decoded = json_decode($metaJson, true);
        return is_array($decoded) ? $decoded : array();
    }

    protected function _leadHistoryChangeLine($entry = array())
    {
        $fieldLabel = trim((string)(isset($entry['field_label']) ? $entry['field_label'] : ''));
        if($fieldLabel === '')
        {
            $fieldLabel = 'Change';
        }
        $oldText = trim((string)(isset($entry['old_value']) ? $entry['old_value'] : ''));
        $newText = trim((string)(isset($entry['new_value']) ? $entry['new_value'] : ''));
        if($oldText === '' && $newText === '')
        {
            return '';
        }
        if($oldText === '')
        {
            return $fieldLabel.': '.$newText;
        }
        return $fieldLabel.': '.$oldText.' -> '.($newText !== '' ? $newText : '-');
    }

    protected function _leadHistoryDetailText($entry = array())
    {
        $entry = is_array($entry) ? $entry : array();
        $parts = array();
        $recipient = trim((string)(isset($entry['recipient']) ? $entry['recipient'] : ''));
        if($recipient !== '')
        {
            $parts[] = 'Recipient: '.$recipient;
        }
        $messageBody = trim((string)(isset($entry['message_body']) ? $entry['message_body'] : ''));
        if($messageBody !== '')
        {
            $parts[] = $messageBody;
        }
        if(empty($parts))
        {
            $changeLine = $this->_leadHistoryChangeLine($entry);
            if($changeLine !== '')
            {
                $parts[] = $changeLine;
            }
        }
        $meta = $this->_leadHistoryMetaArray(isset($entry['meta_json']) ? $entry['meta_json'] : null);
        if(!empty($meta['gateway_source']))
        {
            $parts[] = 'Gateway: '.trim((string)$meta['gateway_source']);
        }
        return implode("\n\n", $parts);
    }

    protected function _leadHistoryDisplayRows($historyRows = array())
    {
        $displayRows = array();
        $changeGroupMap = array();

        foreach((array)$historyRows as $historyRow)
        {
            $entry = isset($historyRow['LeadHistory']) ? (array)$historyRow['LeadHistory'] : array();
            if(empty($entry))
            {
                continue;
            }
            $entryType = strtolower(trim((string)(isset($entry['entry_type']) ? $entry['entry_type'] : 'change')));
            if($entryType === 'change')
            {
                $meta = $this->_leadHistoryMetaArray(isset($entry['meta_json']) ? $entry['meta_json'] : null);
                $groupKey = implode('|', array(
                    (int)(isset($entry['lead_id']) ? $entry['lead_id'] : 0),
                    trim((string)(isset($entry['created']) ? $entry['created'] : '')),
                    (int)(isset($entry['actor_user_id']) ? $entry['actor_user_id'] : 0),
                    trim((string)(isset($meta['source_action']) ? $meta['source_action'] : ''))
                ));
                $line = $this->_leadHistoryChangeLine($entry);
                if(isset($changeGroupMap[$groupKey]))
                {
                    $groupIndex = (int)$changeGroupMap[$groupKey];
                    if($line !== '')
                    {
                        $displayRows[$groupIndex]['details_lines'][] = $line;
                    }
                    continue;
                }

                $displayRows[] = array(
                    'entry' => $entry,
                    'details_lines' => ($line !== '' ? array($line) : array())
                );
                $changeGroupMap[$groupKey] = count($displayRows) - 1;
                continue;
            }

            $displayRows[] = array(
                'entry' => $entry,
                'details_text' => $this->_leadHistoryDetailText($entry)
            );
        }

        foreach($displayRows as $index => $displayRow)
        {
            if(isset($displayRow['details_lines']))
            {
                $detailsLines = array_values(array_unique(array_filter((array)$displayRow['details_lines'], 'strlen')));
                $displayRows[$index]['details_text'] = implode("\n", $detailsLines);
                if(count($detailsLines) > 1)
                {
                    $displayRows[$index]['entry']['title'] = 'Lead Updated';
                    $displayRows[$index]['entry']['field_label'] = 'Multiple Changes';
                }
            }
        }

        return $displayRows;
    }

    protected function _logLeadHistoryMessage($leadRow = array(), $mode = 'sms', $sendStatus = 'sent', $recipient = '', $subject = '', $messageBody = '', $gatewaySourceLabel = '', $errorText = '')
    {
        $leadData = isset($leadRow['Lead']) ? (array)$leadRow['Lead'] : array();
        $leadId = !empty($leadData['id']) ? (int)$leadData['id'] : 0;
        if($leadId <= 0)
        {
            return;
        }
        $LeadHistory = $this->_leadHistoryModel();
        if(!$LeadHistory)
        {
            return;
        }
        $mode = strtolower(trim((string)$mode));
        $sendStatus = strtolower(trim((string)$sendStatus));
        $modeLabel = ($mode === 'email') ? 'Email' : 'SMS';
        $statusLabel = 'Sent';
        if($sendStatus === 'failed')
        {
            $statusLabel = 'Failed';
        }
        elseif($sendStatus === 'skipped')
        {
            $statusLabel = 'Skipped';
        }

        $details = trim((string)$messageBody);
        if($mode === 'email' && trim((string)$subject) !== '')
        {
            $details = "Subject: ".trim((string)$subject).($details !== '' ? "\n\n".$details : '');
        }
        if(trim((string)$errorText) !== '')
        {
            $details .= ($details !== '' ? "\n\n" : '').'Note: '.trim((string)$errorText);
        }

        $actor = $this->_leadHistoryActorContext();
        $LeadHistory->createEntry(array_merge($actor, array(
            'lead_id' => $leadId,
            'project_id' => !empty($leadData['project_id']) ? (int)$leadData['project_id'] : 0,
            'lead_user_id' => !empty($leadData['user_id']) ? (int)$leadData['user_id'] : 0,
            'entry_type' => $mode,
            'title' => $modeLabel.' '.$statusLabel,
            'recipient' => trim((string)$recipient),
            'message_body' => $details,
            'meta_json' => array(
                'source_action' => (string)$this->request->action,
                'gateway_source' => trim((string)$gatewaySourceLabel),
                'status' => $sendStatus
            )
        )));
    }

    protected function _collectLeadMessageIds(&$normalizedIds, $rawValue)
    {
        if(is_array($rawValue))
        {
            foreach($rawValue as $nestedRawValue)
            {
                $this->_collectLeadMessageIds($normalizedIds, $nestedRawValue);
            }
            return;
        }
        $rawText = trim((string)$rawValue);
        if($rawText === '')
        {
            return;
        }
        if(strpos($rawText, ',') !== false)
        {
            $parts = explode(',', $rawText);
            foreach($parts as $part)
            {
                $this->_collectLeadMessageIds($normalizedIds, $part);
            }
            return;
        }
        $leadId = (int)$rawText;
        if($leadId > 0)
        {
            $normalizedIds[$leadId] = $leadId;
        }
    }

    protected function _leadMessageIdsFromRequest($fallbackIds = null)
    {
        $rawCandidates = array();

        if($fallbackIds !== null && $fallbackIds !== '')
        {
            $rawCandidates[] = $fallbackIds;
        }

        if(isset($this->request->data['Lead']['id']))
        {
            $rawCandidates[] = $this->request->data['Lead']['id'];
        }
        if(isset($this->request->data['Lead']['ids']))
        {
            $rawCandidates[] = $this->request->data['Lead']['ids'];
        }
        if(isset($this->request->data['id']))
        {
            $rawCandidates[] = $this->request->data['id'];
        }
        if(isset($this->request->data['ids']))
        {
            $rawCandidates[] = $this->request->data['ids'];
        }
        if(isset($this->request->params['form']['Lead']['id']))
        {
            $rawCandidates[] = $this->request->params['form']['Lead']['id'];
        }
        if(isset($this->request->params['form']['Lead']['ids']))
        {
            $rawCandidates[] = $this->request->params['form']['Lead']['ids'];
        }
        if(isset($this->request->params['form']['id']))
        {
            $rawCandidates[] = $this->request->params['form']['id'];
        }
        if(isset($this->request->params['form']['ids']))
        {
            $rawCandidates[] = $this->request->params['form']['ids'];
        }

        if(isset($this->request->query['ids']))
        {
            $rawCandidates[] = $this->request->query['ids'];
        }
        if(isset($this->request->query['id']))
        {
            $rawCandidates[] = $this->request->query['id'];
        }
        if(isset($this->request->params['named']['ids']))
        {
            $rawCandidates[] = $this->request->params['named']['ids'];
        }
        if(isset($this->request->params['named']['id']))
        {
            $rawCandidates[] = $this->request->params['named']['id'];
        }
        if(isset($this->request->params['pass'][1]))
        {
            $rawCandidates[] = $this->request->params['pass'][1];
        }
        elseif(isset($this->request->params['pass'][0]))
        {
            $passZero = trim((string)$this->request->params['pass'][0]);
            if($passZero !== '' && !in_array(strtolower($passZero), array('sms','email'), true))
            {
                $rawCandidates[] = $passZero;
            }
        }

        if(isset($_REQUEST['ids']))
        {
            $rawCandidates[] = $_REQUEST['ids'];
        }
        if(isset($_REQUEST['id']))
        {
            $rawCandidates[] = $_REQUEST['id'];
        }

        $normalized = array();
        foreach($rawCandidates as $rawCandidate)
        {
            $this->_collectLeadMessageIds($normalized, $rawCandidate);
        }
        return array_values($normalized);
    }

    protected function _leadRowsForMessaging($idList)
    {
        $idList = (array)$idList;
        if(empty($idList))
        {
            return array();
        }
        $permissionCondition = $this->_leadPermissionCondition(null);
        return (array)$this->Lead->find('all', array(
            'conditions' => array('Lead.id' => $idList, $permissionCondition),
            'order' => array('Lead.id' => 'desc')
        ));
    }

    protected function _leadCanUseAgencyFilters()
    {
        $ugroupId = isset($this->adminValue['User']['ugroup_id']) ? (int)$this->adminValue['User']['ugroup_id'] : 0;
        $ugroupName = isset($this->adminValue['Ugroup']['name']) ? strtolower(trim((string)$this->adminValue['Ugroup']['name'])) : '';
        return $this->_isGlobalAdminUser() || $ugroupId === 8 || $ugroupName === 'crm';
    }

    protected function _leadCanUseAgentFilters()
    {
        $ugroupId = isset($this->adminValue['User']['ugroup_id']) ? (int)$this->adminValue['User']['ugroup_id'] : 0;
        $ugroupName = isset($this->adminValue['Ugroup']['name']) ? strtolower(trim((string)$this->adminValue['Ugroup']['name'])) : '';
        return $this->_isGlobalAdminUser() || strtoupper(trim((string)$this->userType)) === 'AGY' || $ugroupId === 8 || $ugroupName === 'crm';
    }

    protected function _leadCanUseUserLevelFilters()
    {
        return $this->_isGlobalAdminUser();
    }

    protected function _leadNormalizeDateInput($rawValue)
    {
        $rawValue = trim((string)$rawValue);
        if($rawValue === '')
        {
            return '';
        }
        $knownFormats = array(
            'Y-m-d',
            'Y/m/d',
            'd-m-Y',
            'd/m/Y',
            'd.m.Y',
            'm/d/Y',
            'm-d-Y',
            'Y-m-d H:i:s',
            'Y-m-d H:i',
            'Y/m/d H:i:s',
            'Y/m/d H:i',
            'd-m-Y H:i:s',
            'd-m-Y H:i',
            'd/m/Y H:i:s',
            'd/m/Y H:i',
            'm/d/Y H:i:s',
            'm/d/Y H:i',
            'm-d-Y H:i:s',
            'm-d-Y H:i',
            'd-m-Y h:i A',
            'd-m-Y h:i a',
            'd/m/Y h:i A',
            'd/m/Y h:i a',
            'm/d/Y h:i A',
            'm/d/Y h:i a',
            'm-d-Y h:i A',
            'm-d-Y h:i a',
            'Y-m-d h:i A',
            'Y-m-d h:i a',
            'Y/m/d h:i A',
            'Y/m/d h:i a'
        );
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

    protected function _leadPeriodOptions()
    {
        return array(
            '' => __('All Time'),
            'today' => __('Today'),
            'week' => __('This Week'),
            'month' => __('This Month'),
            'year' => __('This Year'),
            'custom' => __('Custom Date Range')
        );
    }

    protected function _leadPeriodLabel($period)
    {
        $period = strtolower(trim((string)$period));
        $options = $this->_leadPeriodOptions();
        if(isset($options[$period]))
        {
            return (string)$options[$period];
        }
        return (string)$options[''];
    }

    protected function _leadKanbanPeriodOptions()
    {
        return array(
            'day' => __('Day'),
            'week' => __('Week'),
            'month' => __('Month'),
            'year' => __('Year'),
            'lifetime' => __('Lifetime'),
            'custom' => __('Custom Date Range')
        );
    }

    protected function _leadKanbanPeriodLabel($period)
    {
        $period = strtolower(trim((string)$period));
        $options = $this->_leadKanbanPeriodOptions();
        if(isset($options[$period]))
        {
            return (string)$options[$period];
        }
        return (string)$options['lifetime'];
    }

    protected function _leadSortStatusRows($statusRows)
    {
        $regularRows = array();
        $cancelledRows = array();
        foreach((array)$statusRows as $statusRow)
        {
            $statusName = '';
            if(!empty($statusRow['Status']['name']))
            {
                $statusName = strtolower(trim((string)$statusRow['Status']['name']));
            }
            if(in_array($statusName, array('cancelled', 'canceled'), true))
            {
                $cancelledRows[] = $statusRow;
                continue;
            }
            $regularRows[] = $statusRow;
        }
        return array_merge($regularRows, $cancelledRows);
    }

    protected function _leadAgentFilterOptions($selectedProjectId = 0, $selectedUserLevelId = 0)
    {
        $selectedProjectId = (int)$selectedProjectId;
        $selectedUserLevelId = (int)$selectedUserLevelId;
        $this->loadModel('User');
        $conditions = array();
        if(!$this->_leadCanUseAgentFilters())
        {
            $conditions[] = array('User.id' => (int)$this->luserId);
        }
        elseif($this->_leadCanUseAgencyFilters())
        {
            $conditions[] = $this->User->assignableScopeConditions($selectedProjectId > 0 ? $selectedProjectId : 0,'User');
        }
        else
        {
            $conditions[] = $this->User->assignableScopeConditions((int)$this->agencyId,'User');
        }
        if($this->_leadCanUseUserLevelFilters() && $selectedUserLevelId > 0)
        {
            $conditions[] = array('User.ugroup_id' => $selectedUserLevelId);
        }
        return (array)$this->User->find('list', array(
            'fields' => array('User.id','User.name'),
            'conditions' => $conditions,
            'order' => array('User.name' => 'asc')
        ));
    }

    protected function _leadResolveReportAgency($selectedProjectId = 0)
    {
        $selectedProjectId = (int)$selectedProjectId;
        $reportAgency = array();
        $reportAgencyId = 0;
        if($selectedProjectId > 0)
        {
            $reportAgencyId = $selectedProjectId;
        }
        elseif(!$this->_leadCanUseAgencyFilters() && (int)$this->agencyId > 0)
        {
            $reportAgencyId = (int)$this->agencyId;
        }
        if($reportAgencyId <= 0)
        {
            return $reportAgency;
        }

        $this->loadModel('Agency');
        $agencyFields = array('Agency.id');
        foreach(array('name','photo','address','phone','mobile','email') as $agencyHeaderField)
        {
            if($this->Agency->hasField($agencyHeaderField))
            {
                $agencyFields[] = 'Agency.'.$agencyHeaderField;
            }
        }
        $agencyRow = $this->Agency->find('first', array(
            'fields' => $agencyFields,
            'conditions' => array('Agency.id' => $reportAgencyId),
            'recursive' => -1
        ));
        if(empty($agencyRow['Agency']))
        {
            return $reportAgency;
        }
        $reportAgency = (array)$agencyRow['Agency'];
        if(empty($reportAgency['address']) || empty($reportAgency['phone']) || empty($reportAgency['mobile']) || empty($reportAgency['email']))
        {
            $this->loadModel('User');
            $userFields = array('User.id');
            foreach(array('address','phone','mobile','email') as $userHeaderField)
            {
                if($this->User->hasField($userHeaderField))
                {
                    $userFields[] = 'User.'.$userHeaderField;
                }
            }
            $agencyUser = $this->User->find('first', array(
                'fields' => $userFields,
                'conditions' => array('User.project_id' => $reportAgencyId),
                'order' => array('User.id' => 'asc'),
                'recursive' => -1
            ));
            if(!empty($agencyUser['User']))
            {
                if(empty($reportAgency['address']) && !empty($agencyUser['User']['address']))$reportAgency['address'] = $agencyUser['User']['address'];
                if(empty($reportAgency['phone']) && !empty($agencyUser['User']['phone']))$reportAgency['phone'] = $agencyUser['User']['phone'];
                if(empty($reportAgency['mobile']) && !empty($agencyUser['User']['mobile']))$reportAgency['mobile'] = $agencyUser['User']['mobile'];
                if(empty($reportAgency['email']) && !empty($agencyUser['User']['email']))$reportAgency['email'] = $agencyUser['User']['email'];
            }
        }
        return $reportAgency;
    }

    protected function _extractLeadManageFilters($rawParams = array())
    {
        $rawParams = (array)$rawParams;
        $canUseAgencyFilters = $this->_leadCanUseAgencyFilters();
        $canUseAgentFilters = $this->_leadCanUseAgentFilters();
        $selectedProjectFilter = 0;
        if(isset($rawParams['project_id']))
        {
            $selectedProjectFilter = (int)$rawParams['project_id'];
        }
        if(!$canUseAgencyFilters)
        {
            $selectedProjectFilter = (int)$this->agencyId;
        }
        if($selectedProjectFilter < 0)
        {
            $selectedProjectFilter = 0;
        }

        $selectedAgentFilter = 0;
        if($canUseAgentFilters && isset($rawParams['user_id']))
        {
            $selectedAgentFilter = (int)$rawParams['user_id'];
        }
        if(!$canUseAgentFilters)
        {
            $selectedAgentFilter = (int)$this->luserId;
        }
        if($selectedAgentFilter < 0)
        {
            $selectedAgentFilter = 0;
        }
        if($canUseAgentFilters && $selectedAgentFilter > 0)
        {
            $allowedUsers = $this->_leadAgentFilterOptions($selectedProjectFilter);
            if(!isset($allowedUsers[$selectedAgentFilter]))
            {
                $selectedAgentFilter = 0;
            }
        }

        $selectedPeriod = '';
        if(array_key_exists('period', $rawParams))
        {
            $selectedPeriod = strtolower(trim((string)$rawParams['period']));
        }
        if(!in_array($selectedPeriod, array('','today','week','month','year','custom'), true))
        {
            $selectedPeriod = '';
        }

        $startDateInput = isset($rawParams['start_date']) ? trim((string)$rawParams['start_date']) : '';
        $endDateInput = isset($rawParams['end_date']) ? trim((string)$rawParams['end_date']) : '';
        $startDate = '';
        $endDate = '';
        $today = date('Y-m-d');
        if($selectedPeriod === 'today')
        {
            $startDate = $today;
            $endDate = $today;
        }
        elseif($selectedPeriod === 'week')
        {
            $startDate = date('Y-m-d', strtotime('monday this week'));
            $endDate = date('Y-m-d', strtotime('sunday this week'));
        }
        elseif($selectedPeriod === 'month')
        {
            $startDate = date('Y-m-01');
            $endDate = date('Y-m-t');
        }
        elseif($selectedPeriod === 'year')
        {
            $startDate = date('Y-01-01');
            $endDate = date('Y-12-31');
        }
        else
        {
            $startDate = $this->_leadNormalizeDateInput($startDateInput);
            $endDate = $this->_leadNormalizeDateInput($endDateInput);
        }

        $conditions = array();
        $isAgentUser = (strtoupper(trim((string)$this->userType)) === 'AGT');
        if($canUseAgencyFilters && $selectedProjectFilter > 0)
        {
            $conditions[] = array('Lead.project_id' => $selectedProjectFilter);
        }
        elseif(!$canUseAgencyFilters && !$isAgentUser && (int)$this->agencyId > 0)
        {
            $this->loadModel('User');
            $agencyAgentRows = (array)$this->User->find('all', array(
                'fields' => array('User.id'),
                'conditions' => array('User.project_id' => (int)$this->agencyId),
                'recursive' => -1
            ));
            $agencyAgentIds = !empty($agencyAgentRows) ? Hash::extract($agencyAgentRows, '{n}.User.id') : array();
            if(!empty($agencyAgentIds))
            {
                $conditions[] = array(
                    'OR' => array(
                        'Lead.project_id' => (int)$this->agencyId,
                        'Lead.user_id' => $agencyAgentIds
                    )
                );
            }
            else
            {
                $conditions[] = array('Lead.project_id' => (int)$this->agencyId);
            }
        }
        if($selectedAgentFilter > 0)
        {
            $conditions[] = array('Lead.user_id' => $selectedAgentFilter);
        }
        if($startDate !== '' && $endDate !== '')
        {
            $conditions[] = array(
                'OR' => array(
                    array('Lead.follow_up >=' => $startDate.' 00:00:00', 'Lead.follow_up <=' => $endDate.' 23:59:59'),
                    array('Lead.last_contact_date >=' => $startDate, 'Lead.last_contact_date <=' => $endDate),
                    array('Lead.created >=' => $startDate.' 00:00:00', 'Lead.created <=' => $endDate.' 23:59:59'),
                    array('Lead.follow_up' => null)
                )
            );
        }
        elseif($startDate !== '')
        {
            $conditions[] = array(
                'OR' => array(
                    array('Lead.follow_up >=' => $startDate.' 00:00:00'),
                    array('Lead.last_contact_date >=' => $startDate),
                    array('Lead.created >=' => $startDate.' 00:00:00'),
                    array('Lead.follow_up' => null)
                )
            );
        }
        elseif($endDate !== '')
        {
            $conditions[] = array(
                'OR' => array(
                    array('Lead.follow_up <=' => $endDate.' 23:59:59'),
                    array('Lead.last_contact_date <=' => $endDate),
                    array('Lead.created <=' => $endDate.' 23:59:59')
                )
            );
        }

        $selectedPhoneRegion = 'all';
        if(isset($rawParams['phone_region']))
        {
            $selectedPhoneRegion = strtolower(trim((string)$rawParams['phone_region']));
        }
        if(!in_array($selectedPhoneRegion, array('all', 'overseas', 'local'), true))
        {
            $selectedPhoneRegion = 'all';
        }
        if($selectedPhoneRegion !== 'all')
        {
            $regionCond = $this->_leadPhoneRegionCondition($selectedPhoneRegion);
            if($regionCond)
            {
                $conditions[] = $regionCond;
            }
        }

        $selectedLeadSource = '';
        if(isset($rawParams['lead_source']))
        {
            $selectedLeadSource = trim((string)$rawParams['lead_source']);
        }
        if($selectedLeadSource === 'brochure' || $selectedLeadSource === 'pdf' || $selectedLeadSource === 'Payment Plan PDF Download')
        {
            $conditions[] = array(
                'OR' => array(
                    'Lead.lead_source LIKE' => '%PDF%',
                    'Lead.lead_source LIKE' => '%Brochure%',
                    'Lead.lead_source LIKE' => '%Payment Plan%',
                    'Lead.lead_source LIKE' => '%Download%',
                    'Lead.remarks LIKE' => '%download%',
                    'Lead.remarks LIKE' => '%brochure%',
                    'Lead.remarks LIKE' => '%payment plan%'
                )
            );
        }
        elseif(in_array(strtolower($selectedLeadSource), array('facebook', 'fb', 'facebook lead ads'), true))
        {
            $conditions[] = array(
                'OR' => array(
                    'Lead.lead_source LIKE' => '%Facebook%',
                    'Lead.lead_source LIKE' => '%FB%',
                    'Lead.remarks LIKE' => '%Facebook%',
                    'Lead.remarks LIKE' => '%FB%'
                )
            );
        }
        elseif($selectedLeadSource !== '')
        {
            $conditions[] = array('Lead.lead_source' => $selectedLeadSource);
        }

        return array(
            'conditions' => $conditions,
            'project_id' => $selectedProjectFilter,
            'user_id' => $selectedAgentFilter,
            'lead_source' => $selectedLeadSource,
            'phone_region' => $selectedPhoneRegion,
            'period' => $selectedPeriod,
            'period_label' => $this->_leadPeriodLabel($selectedPeriod),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'start_date_input' => $startDateInput,
            'end_date_input' => $endDateInput
        );
    }

    protected function _leadKanbanRawValue($rawParams, $key, $fallback = '')
    {
        $rawParams = (array)$rawParams;
        $key = trim((string)$key);
        if($key === '')
        {
            return $fallback;
        }
        if(isset($rawParams[$key]))
        {
            return $rawParams[$key];
        }
        if(isset($rawParams['Lead']) && is_array($rawParams['Lead']) && array_key_exists($key, $rawParams['Lead']))
        {
            return $rawParams['Lead'][$key];
        }
        if(isset($rawParams['data']) && is_array($rawParams['data']))
        {
            if(array_key_exists($key, $rawParams['data']))
            {
                return $rawParams['data'][$key];
            }
            if(isset($rawParams['data']['Lead']) && is_array($rawParams['data']['Lead']) && array_key_exists($key, $rawParams['data']['Lead']))
            {
                return $rawParams['data']['Lead'][$key];
            }
        }
        return $fallback;
    }

    protected function _extractLeadKanbanFilters($rawParams = array())
    {
        $rawParams = (array)$rawParams;
        $canUseAgencyFilters = $this->_leadCanUseAgencyFilters();
        $canUseAgentFilters = $this->_leadCanUseAgentFilters();
        $canUseUserLevelFilters = $this->_leadCanUseUserLevelFilters();

        $selectedProjectFilter = 0;
        $projectFilterRaw = $this->_leadKanbanRawValue($rawParams, 'project_id', $this->_leadKanbanRawValue($rawParams, 'kanban_project_id', 0));
        if($canUseAgencyFilters && $projectFilterRaw !== '')
        {
            $selectedProjectFilter = (int)$projectFilterRaw;
        }
        elseif(!$canUseAgencyFilters && (int)$this->agencyId > 0)
        {
            $selectedProjectFilter = (int)$this->agencyId;
        }
        if($selectedProjectFilter < 0)
        {
            $selectedProjectFilter = 0;
        }

        $selectedUserLevelFilter = 0;
        $userLevelRaw = $this->_leadKanbanRawValue($rawParams, 'ugroup_id', $this->_leadKanbanRawValue($rawParams, 'kanban_ugroup_id', 0));
        if($canUseUserLevelFilters && $userLevelRaw !== '')
        {
            $selectedUserLevelFilter = $this->_normalizeLeadImportUserLevelId($userLevelRaw);
        }

        $userOptions = $this->_leadAgentFilterOptions($selectedProjectFilter, $selectedUserLevelFilter);

        $selectedUserFilter = 0;
        if(!$canUseAgentFilters)
        {
            $selectedUserFilter = (int)$this->luserId;
        }
        else
        {
            $requestedUserId = (int)$this->_leadKanbanRawValue($rawParams, 'user_id', $this->_leadKanbanRawValue($rawParams, 'kanban_user_id', 0));
            if($requestedUserId > 0 && isset($userOptions[$requestedUserId]))
            {
                $selectedUserFilter = $requestedUserId;
            }
        }

        $selectedPeriod = 'week';
        $periodRaw = $this->_leadKanbanRawValue($rawParams, 'period', $this->_leadKanbanRawValue($rawParams, 'kanban_period', 'week'));
        if($periodRaw !== '')
        {
            $selectedPeriod = strtolower(trim((string)$periodRaw));
        }
        if(in_array($selectedPeriod, array('', 'all', 'alltime'), true))
        {
            $selectedPeriod = 'lifetime';
        }
        if($selectedPeriod === 'today')
        {
            $selectedPeriod = 'day';
        }
        if(!in_array($selectedPeriod, array('day','week','month','year','lifetime','custom'), true))
        {
            $selectedPeriod = 'lifetime';
        }

        $startDateInput = trim((string)$this->_leadKanbanRawValue($rawParams, 'start_date', ''));
        $endDateInput = trim((string)$this->_leadKanbanRawValue($rawParams, 'end_date', ''));
        $startDate = '';
        $endDate = '';
        $today = date('Y-m-d');
        if($selectedPeriod === 'day')
        {
            $startDate = $today;
            $endDate = $today;
        }
        elseif($selectedPeriod === 'week')
        {
            $startDate = date('Y-m-d', strtotime('monday this week'));
            $endDate = date('Y-m-d', strtotime('sunday this week'));
        }
        elseif($selectedPeriod === 'month')
        {
            $startDate = date('Y-m-01');
            $endDate = date('Y-m-t');
        }
        elseif($selectedPeriod === 'year')
        {
            $startDate = date('Y-01-01');
            $endDate = date('Y-12-31');
        }
        elseif($selectedPeriod === 'custom')
        {
            $startDate = $this->_leadNormalizeDateInput($startDateInput);
            $endDate = $this->_leadNormalizeDateInput($endDateInput);
        }

        $conditions = array();
        $isAgentUser = (strtoupper(trim((string)$this->userType)) === 'AGT');
        if($canUseAgencyFilters && $selectedProjectFilter > 0)
        {
            $conditions[] = array('Lead.project_id' => $selectedProjectFilter);
        }
        elseif(!$canUseAgencyFilters && !$isAgentUser && (int)$this->agencyId > 0)
        {
            $this->loadModel('User');
            $agencyAgentRows = (array)$this->User->find('all', array(
                'fields' => array('User.id'),
                'conditions' => array('User.project_id' => (int)$this->agencyId),
                'recursive' => -1
            ));
            $agencyAgentIds = !empty($agencyAgentRows) ? Hash::extract($agencyAgentRows, '{n}.User.id') : array();
            if(!empty($agencyAgentIds))
            {
                $conditions[] = array(
                    'OR' => array(
                        'Lead.project_id' => (int)$this->agencyId,
                        'Lead.user_id' => $agencyAgentIds
                    )
                );
            }
            else
            {
                $conditions[] = array('Lead.project_id' => (int)$this->agencyId);
            }
        }

        if($selectedUserFilter > 0)
        {
            $conditions[] = array('Lead.user_id' => $selectedUserFilter);
        }
        elseif($canUseUserLevelFilters && $selectedUserLevelFilter > 0)
        {
            $userIds = array_values(array_unique(array_filter(array_map('intval', array_keys((array)$userOptions)))));
            if(empty($userIds))
            {
                $conditions[] = array('Lead.user_id' => 0);
            }
            else
            {
                $conditions[] = array('Lead.user_id' => $userIds);
            }
        }

        if($startDate !== '' && $endDate !== '')
        {
            $conditions[] = array(
                'OR' => array(
                    array('Lead.follow_up >=' => $startDate.' 00:00:00', 'Lead.follow_up <=' => $endDate.' 23:59:59'),
                    array('Lead.last_contact_date >=' => $startDate, 'Lead.last_contact_date <=' => $endDate),
                    array('Lead.created >=' => $startDate.' 00:00:00', 'Lead.created <=' => $endDate.' 23:59:59'),
                    array('Lead.follow_up' => null)
                )
            );
        }
        elseif($startDate !== '')
        {
            $conditions[] = array(
                'OR' => array(
                    array('Lead.follow_up >=' => $startDate.' 00:00:00'),
                    array('Lead.last_contact_date >=' => $startDate),
                    array('Lead.created >=' => $startDate.' 00:00:00'),
                    array('Lead.follow_up' => null)
                )
            );
        }
        elseif($endDate !== '')
        {
            $conditions[] = array(
                'OR' => array(
                    array('Lead.follow_up <=' => $endDate.' 23:59:59'),
                    array('Lead.last_contact_date <=' => $endDate),
                    array('Lead.created <=' => $endDate.' 23:59:59')
                )
            );
        }

        $selectedPhoneRegion = strtolower(trim((string)$this->_leadKanbanRawValue($rawParams, 'phone_region', $this->_leadKanbanRawValue($rawParams, 'kanban_phone_region', 'all'))));
        if(!in_array($selectedPhoneRegion, array('all', 'overseas', 'local'), true))
        {
            $selectedPhoneRegion = 'all';
        }
        if($selectedPhoneRegion !== 'all')
        {
            $regionCond = $this->_leadPhoneRegionCondition($selectedPhoneRegion);
            if($regionCond)
            {
                $conditions[] = $regionCond;
            }
        }

        return array(
            'conditions' => $conditions,
            'project_id' => $selectedProjectFilter,
            'ugroup_id' => $selectedUserLevelFilter,
            'user_id' => $selectedUserFilter,
            'phone_region' => $selectedPhoneRegion,
            'period' => $selectedPeriod,
            'period_label' => $this->_leadKanbanPeriodLabel($selectedPeriod),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'start_date_input' => $startDateInput,
            'end_date_input' => $endDateInput,
            'user_options' => $userOptions
        );
    }

    protected function _leadManageConditions($id = null, $parsedParams = array(), $rawParams = array())
    {
        $condition = $this->_leadPermissionCondition($id);
        $filters = $this->_extractLeadManageFilters($rawParams);
        $conditions = array();
        if(!empty($condition))
        {
            $conditions[] = $condition;
        }
        $criteria = $this->Lead->parseCriteria((array)$parsedParams);
        if(!empty($criteria))
        {
            $conditions[] = $criteria;
        }
        foreach((array)$filters['conditions'] as $filterCondition)
        {
            $conditions[] = $filterCondition;
        }
        return array('conditions' => $conditions, 'filters' => $filters);
    }

    protected function _leadMobileForMessage($leadRow)
    {
        $leadMobile = isset($leadRow['Lead']['lead_mobile']) ? trim((string)$leadRow['Lead']['lead_mobile']) : '';
        if($leadMobile !== '')
        {
            return $leadMobile;
        }
        $clientMobile = isset($leadRow['Client']['mobile']) ? trim((string)$leadRow['Client']['mobile']) : '';
        if($clientMobile !== '')
        {
            return $clientMobile;
        }
        return isset($leadRow['Client']['phone']) ? trim((string)$leadRow['Client']['phone']) : '';
    }

    protected function _leadEmailForMessage($leadRow)
    {
        $leadEmail = isset($leadRow['Lead']['lead_email']) ? trim((string)$leadRow['Lead']['lead_email']) : '';
        if($leadEmail !== '')
        {
            return $leadEmail;
        }
        return isset($leadRow['Client']['email']) ? trim((string)$leadRow['Client']['email']) : '';
    }

    protected function _leadTemplateRender($templateText, $leadRow)
    {
        $leadName = trim((string)(isset($leadRow['Lead']['lead_name']) ? $leadRow['Lead']['lead_name'] : ''));
        if($leadName === '')
        {
            $leadName = trim((string)(isset($leadRow['Client']['name']) ? $leadRow['Client']['name'] : ''));
        }
        $lastContactDate = isset($leadRow['Lead']['last_contact_date']) ? trim((string)$leadRow['Lead']['last_contact_date']) : '';
        if($lastContactDate !== '')
        {
            $lastContactTimestamp = strtotime($lastContactDate);
            if($lastContactTimestamp !== false)
            {
                $lastContactDate = date('d-M-Y', $lastContactTimestamp);
            }
        }
        $followUp = isset($leadRow['Lead']['follow_up']) ? trim((string)$leadRow['Lead']['follow_up']) : '';
        if($followUp !== '')
        {
            $timestamp = strtotime($followUp);
            if($timestamp !== false)
            {
                $followUp = date('d-M-Y H:i', $timestamp);
            }
        }
        $tokens = array(
            '{{lead_name}}' => $leadName,
            '{{client_name}}' => trim((string)(isset($leadRow['Client']['name']) ? $leadRow['Client']['name'] : '')),
            '{{lead_mobile}}' => $this->_leadMobileForMessage($leadRow),
            '{{lead_email}}' => $this->_leadEmailForMessage($leadRow),
            '{{lead_source}}' => trim((string)(isset($leadRow['Lead']['lead_source']) ? $leadRow['Lead']['lead_source'] : '')),
            '{{remarks}}' => trim((string)(isset($leadRow['Lead']['remarks']) ? $leadRow['Lead']['remarks'] : '')),
            '{{last_contact_date}}' => $lastContactDate,
            '{{follow_up}}' => $followUp,
            '{{property_name}}' => trim((string)(isset($leadRow['Property']['name']) ? $leadRow['Property']['name'] : ''))
        );
        return str_replace(array_keys($tokens), array_values($tokens), (string)$templateText);
    }

    protected function _leadNormalizeDateTimeInput($rawValue)
    {
        $rawValue = trim((string)$rawValue);
        if($rawValue === '')
        {
            return '';
        }
        $knownFormats = array(
            'Y-m-d H:i:s',
            'Y-m-d H:i',
            'Y/m/d H:i:s',
            'Y/m/d H:i',
            'd-m-Y H:i:s',
            'd-m-Y H:i',
            'd/m/Y H:i:s',
            'd/m/Y H:i',
            'm/d/Y H:i:s',
            'm/d/Y H:i',
            'm-d-Y H:i:s',
            'm-d-Y H:i',
            'd-m-Y h:i A',
            'd-m-Y h:i a',
            'd/m/Y h:i A',
            'd/m/Y h:i a',
            'm/d/Y h:i A',
            'm/d/Y h:i a',
            'm-d-Y h:i A',
            'm-d-Y h:i a',
            'Y-m-d h:i A',
            'Y-m-d h:i a',
            'Y/m/d h:i A',
            'Y/m/d h:i a'
        );
        foreach($knownFormats as $knownFormat)
        {
            $dateObj = DateTime::createFromFormat($knownFormat, $rawValue);
            if($dateObj instanceof DateTime)
            {
                $errors = DateTime::getLastErrors();
                if((int)$errors['warning_count'] === 0 && (int)$errors['error_count'] === 0)
                {
                    return $dateObj->format('Y-m-d H:i:s');
                }
            }
        }
        $timestamp = strtotime($rawValue);
        if($timestamp === false)
        {
            return '';
        }
        return date('Y-m-d H:i:s', $timestamp);
    }

    protected function _leadCanSendMessages()
    {
        $canSend = !empty($this->userPermissionArr['save_right']);
        if($canSend)
        {
            return true;
        }
        $sessionUser = (array)$this->Session->read('User');
        $groupId = !empty($sessionUser['User']['ugroup_id']) ? (int)$sessionUser['User']['ugroup_id'] : 0;
        if($groupId <= 0)
        {
            return false;
        }
        $managePermission = $this->_findPermissionPageRow($groupId, 'leads', 'manage');
        if(empty($managePermission['PageRight']))
        {
            return false;
        }
        $normalizedManagePermission = $this->_normalizePageRightRow((array)$managePermission['PageRight']);
        return !empty($normalizedManagePermission['save_right']);
    }

    protected function _effectiveSmsGatewayBundleForUser($userId = 0)
    {
        $globalSmsGateway = isset($this->smsSettingArr) ? (array)$this->smsSettingArr : array();
        $globalApi = '';
        if(!empty($globalSmsGateway['Smssetting']) && is_array($globalSmsGateway['Smssetting']))
        {
            $globalApi = trim((string)(isset($globalSmsGateway['Smssetting']['api']) ? $globalSmsGateway['Smssetting']['api'] : ''));
        }
        if($globalApi === '')
        {
            try
            {
                $Smssetting = ClassRegistry::init('Admin.Smssetting');
                if($Smssetting)
                {
                    $globalSmsGateway = (array)$Smssetting->findById(1);
                }
            }
            catch(Exception $e)
            {
            }
        }
        $bundle = array('use_global' => 1, 'settings' => $globalSmsGateway);
        $userId = (int)$userId;
        if($userId <= 0)
        {
            return $bundle;
        }
        try
        {
            $CrmIntegration = ClassRegistry::init('CrmIntegration');
            if($CrmIntegration)
            {
                $CrmIntegration->ensureSchema();
                $bundle = (array)$CrmIntegration->effectiveSmsGatewayForUser($userId, $globalSmsGateway);
            }
        }
        catch(Exception $e)
        {
            $bundle = array('use_global' => 1, 'settings' => $globalSmsGateway);
        }
        return $bundle;
    }

    protected function _smsGatewayLooksConfigured($smsGatewaySettings = array())
    {
        $smsGatewaySettings = (array)$smsGatewaySettings;
        $smsGatewayRow = !empty($smsGatewaySettings['Smssetting']) ? (array)$smsGatewaySettings['Smssetting'] : array();
        return (trim((string)(isset($smsGatewayRow['api']) ? $smsGatewayRow['api'] : '')) !== '');
    }

    protected function _queueLeadSmsRows($leadRows = array(), $currentUserId = 0, $templateId = 0, $templateMap = array(), $messageText = '', $scheduledAt = '', $gatewaySourceMode = 'global', $gatewaySourceLabel = '')
    {
        $result = array('queued' => 0, 'skipped' => 0, 'failed' => 0, 'error' => '');
        $LeadSmsSchedule = ClassRegistry::init('LeadSmsSchedule');
        if(!$LeadSmsSchedule)
        {
            $result['error'] = 'LeadSmsSchedule model not available.';
            return $result;
        }
        if(!$LeadSmsSchedule->ensureSchema())
        {
            $result['error'] = 'Lead SMS schedule table is not ready.';
            return $result;
        }

        $templateId = (int)$templateId;
        $templateName = '';
        if($templateId > 0 && !empty($templateMap[$templateId]['name']))
        {
            $templateName = trim((string)$templateMap[$templateId]['name']);
        }

        foreach((array)$leadRows as $leadRow)
        {
            $leadData = isset($leadRow['Lead']) ? (array)$leadRow['Lead'] : array();
            $leadId = !empty($leadData['id']) ? (int)$leadData['id'] : 0;
            if($leadId <= 0)
            {
                continue;
            }
            $toMobile = trim((string)$this->_leadMobileForMessage($leadRow));
            if($toMobile === '')
            {
                $result['skipped']++;
                continue;
            }
            $renderedText = $this->_leadTemplateRender($messageText, $leadRow);
            $saveData = array(
                'lead_id' => $leadId,
                'project_id' => !empty($leadData['project_id']) ? (int)$leadData['project_id'] : 0,
                'lead_user_id' => !empty($leadData['user_id']) ? (int)$leadData['user_id'] : 0,
                'scheduled_by_user_id' => (int)$currentUserId,
                'template_id' => $templateId,
                'template_name' => $templateName,
                'mobile' => $toMobile,
                'message_template' => (string)$messageText,
                'message_rendered' => (string)$renderedText,
                'gateway_mode' => (string)$gatewaySourceMode,
                'gateway_label' => (string)$gatewaySourceLabel,
                'scheduled_at' => (string)$scheduledAt,
                'status' => 'queued',
                'attempt_count' => 0,
                'last_error' => '',
                'meta_json' => json_encode(array(
                    'source_action' => (string)$this->request->action,
                    'template_id' => $templateId
                ))
            );
            $LeadSmsSchedule->create();
            if($LeadSmsSchedule->save(array('LeadSmsSchedule' => $saveData), array('validate' => false)))
            {
                $result['queued']++;
            }
            else
            {
                $result['failed']++;
                if($result['error'] === '')
                {
                    $lastError = '';
                    try
                    {
                        $db = $LeadSmsSchedule->getDataSource();
                        if($db && property_exists($db, 'lastError'))
                        {
                            $lastError = trim((string)$db->lastError);
                        }
                    }
                    catch(Exception $e)
                    {
                        $lastError = '';
                    }
                    if($lastError === '' && !empty($LeadSmsSchedule->validationErrors))
                    {
                        $lastError = trim((string)json_encode($LeadSmsSchedule->validationErrors));
                    }
                    $result['error'] = ($lastError !== '' ? $lastError : 'Unable to save schedule row.');
                }
            }
        }
        return $result;
    }

    protected function _processDueLeadSmsSchedules($limit = 200)
    {
        $summary = array('due' => 0, 'sent' => 0, 'failed' => 0, 'skipped' => 0);
        $LeadSmsSchedule = ClassRegistry::init('LeadSmsSchedule');
        if(!$LeadSmsSchedule)
        {
            return $summary;
        }
        $LeadSmsSchedule->ensureSchema();
        $LeadSmsSchedule->resetStuckProcessing(30);
        $dueRows = (array)$LeadSmsSchedule->dueRows((int)$limit);
        $summary['due'] = count($dueRows);
        if(empty($dueRows))
        {
            return $summary;
        }

        $leadIds = array();
        foreach((array)$dueRows as $dueRow)
        {
            $dueLeadId = !empty($dueRow['LeadSmsSchedule']['lead_id']) ? (int)$dueRow['LeadSmsSchedule']['lead_id'] : 0;
            if($dueLeadId > 0)
            {
                $leadIds[$dueLeadId] = $dueLeadId;
            }
        }
        $leadRows = (array)$this->Lead->find('all', array(
            'conditions' => array('Lead.id' => array_values($leadIds)),
            'recursive' => 0
        ));
        $leadMap = array();
        foreach((array)$leadRows as $leadRow)
        {
            $leadId = !empty($leadRow['Lead']['id']) ? (int)$leadRow['Lead']['id'] : 0;
            if($leadId > 0)
            {
                $leadMap[$leadId] = $leadRow;
            }
        }

        foreach((array)$dueRows as $dueRow)
        {
            $schedule = isset($dueRow['LeadSmsSchedule']) ? (array)$dueRow['LeadSmsSchedule'] : array();
            $scheduleId = !empty($schedule['id']) ? (int)$schedule['id'] : 0;
            if($scheduleId <= 0)
            {
                continue;
            }
            if(!$LeadSmsSchedule->markProcessing($scheduleId))
            {
                continue;
            }

            $leadId = !empty($schedule['lead_id']) ? (int)$schedule['lead_id'] : 0;
            if($leadId <= 0 || empty($leadMap[$leadId]))
            {
                $LeadSmsSchedule->markFailed($scheduleId, 'Lead record not found.');
                $summary['failed']++;
                continue;
            }

            $leadRow = (array)$leadMap[$leadId];
            $toMobile = trim((string)(!empty($schedule['mobile']) ? $schedule['mobile'] : $this->_leadMobileForMessage($leadRow)));
            if($toMobile === '')
            {
                $LeadSmsSchedule->markFailed($scheduleId, 'No mobile number found.');
                $this->_logLeadHistoryMessage($leadRow, 'sms', 'skipped', '', '', '', 'Scheduled SMS', 'No mobile number found.');
                $summary['skipped']++;
                continue;
            }

            $scheduledByUserId = !empty($schedule['scheduled_by_user_id']) ? (int)$schedule['scheduled_by_user_id'] : 0;
            $smsGatewayBundle = $this->_effectiveSmsGatewayBundleForUser($scheduledByUserId);
            $smsGatewaySettings = isset($smsGatewayBundle['settings']) ? (array)$smsGatewayBundle['settings'] : array();
            $gatewaySourceLabel = !empty($smsGatewayBundle['use_global']) ? __('Global SMS Settings') : __('My CRM SMS Settings');
            if(!$this->_smsGatewayLooksConfigured($smsGatewaySettings))
            {
                $LeadSmsSchedule->markFailed($scheduleId, 'SMS gateway is not configured.');
                $this->_logLeadHistoryMessage($leadRow, 'sms', 'failed', $toMobile, '', '', $gatewaySourceLabel, 'SMS gateway is not configured.');
                $summary['failed']++;
                continue;
            }

            $messageTemplate = trim((string)(isset($schedule['message_template']) ? $schedule['message_template'] : ''));
            if($messageTemplate === '')
            {
                $messageTemplate = trim((string)(isset($schedule['message_rendered']) ? $schedule['message_rendered'] : ''));
            }
            if($messageTemplate === '')
            {
                $LeadSmsSchedule->markFailed($scheduleId, 'Message template is empty.');
                $this->_logLeadHistoryMessage($leadRow, 'sms', 'failed', $toMobile, '', '', $gatewaySourceLabel, 'Message template is empty.');
                $summary['failed']++;
                continue;
            }

            $smsBody = $this->_leadTemplateRender($messageTemplate, $leadRow);
            try
            {
                $this->CustomFunction->sendSms($toMobile, $smsBody, $smsGatewaySettings);
                $LeadSmsSchedule->markSent($scheduleId, $smsBody);
                $this->_logLeadHistoryMessage($leadRow, 'sms', 'sent', $toMobile, '', $smsBody, $gatewaySourceLabel);
                $summary['sent']++;
            }
            catch(Exception $e)
            {
                $errorText = trim((string)$e->getMessage());
                $LeadSmsSchedule->markFailed($scheduleId, $errorText);
                $this->_logLeadHistoryMessage($leadRow, 'sms', 'failed', $toMobile, '', $smsBody, $gatewaySourceLabel, $errorText);
                $summary['failed']++;
            }
        }

        return $summary;
    }

    public function runscheduledsmscron()
    {
        $this->autoRender = false;
        $this->layout = false;
        $expectedToken = sha1((string)Configure::read('Security.salt').'|lead_sms_schedule');
        $providedToken = '';
        if(isset($this->request->query['token']))
        {
            $providedToken = trim((string)$this->request->query['token']);
        }
        elseif(isset($this->request->params['named']['token']))
        {
            $providedToken = trim((string)$this->request->params['named']['token']);
        }

        $isTokenValid = false;
        if($providedToken !== '')
        {
            if(function_exists('hash_equals'))
            {
                $isTokenValid = hash_equals($expectedToken, $providedToken);
            }
            else
            {
                $isTokenValid = ($expectedToken === $providedToken);
            }
        }
        if(!$isTokenValid)
        {
            $this->response->statusCode(403);
            $this->response->type('application/json');
            $this->response->body(json_encode(array('success' => false, 'message' => 'Invalid token')));
            return $this->response;
        }

        $limit = isset($this->request->query['limit']) ? (int)$this->request->query['limit'] : 200;
        if($limit <= 0)
        {
            $limit = 200;
        }
        $summary = $this->_processDueLeadSmsSchedules($limit);
        $payload = array(
            'success' => true,
            'processed_at' => date('Y-m-d H:i:s'),
            'summary' => $summary
        );
        $this->response->type('application/json');
        $this->response->body(json_encode($payload));
        return $this->response;
    }

    protected function _ensureLeadMessageTemplates()
    {
        $SmsTemplate = ClassRegistry::init('Admin.Smstemplate');
        $EmailTemplate = ClassRegistry::init('Admin.Emailtemplate');
        if(!$SmsTemplate || !$EmailTemplate)
        {
            return;
        }
        $SmsTemplate->ensureOwnershipSchema();
        $EmailTemplate->ensureOwnershipSchema();

        $smsDefault = $SmsTemplate->find('first', array(
            'conditions' => array('Smstemplate.type' => 'LED'),
            'recursive' => -1
        ));
        if(empty($smsDefault['Smstemplate']['id']))
        {
            $SmsTemplate->create();
            $SmsTemplate->save(array('Smstemplate' => array(
                'name' => 'Lead Default SMS (LED)',
                'description' => 'Dear {{lead_name}}, this is a follow-up from '.$this->siteName.'. Source: {{lead_source}}. Follow-up: {{follow_up}}. Remarks: {{remarks}}',
                'status' => 'Published',
                'type' => 'LED'
            )), array('validate' => false));
        }

        $emailDefault = $EmailTemplate->find('first', array(
            'conditions' => array('Emailtemplate.type' => 'LED'),
            'recursive' => -1
        ));
        if(empty($emailDefault['Emailtemplate']['id']))
        {
            $EmailTemplate->create();
            $EmailTemplate->save(array('Emailtemplate' => array(
                'name' => 'Lead Default Email (LED)',
                'description' => '<p>Dear {{lead_name}},</p><p>Thank you for your interest in our project.</p><p><strong>Source:</strong> {{lead_source}}<br><strong>Follow-up:</strong> {{follow_up}}<br><strong>Remarks:</strong> {{remarks}}</p><p>Regards,<br>'.$this->siteName.'</p>',
                'status' => 'Published',
                'type' => 'LED'
            )), array('validate' => false));
        }
    }

    protected function _leadTemplateBundle($mode = 'sms')
    {
        $mode = strtolower(trim((string)$mode));
        $isEmail = ($mode === 'email');
        $modelName = $isEmail ? 'Emailtemplate' : 'Smstemplate';
        $defaultTypes = $isEmail ? array('LED','LDE') : array('LED','LDS');
        $isGlobalAdmin = $this->_isGlobalAdminUser();
        $sessionUser = (array)$this->Session->read('User');
        $currentUserId = !empty($sessionUser['User']['id']) ? (int)$sessionUser['User']['id'] : (int)$this->luserId;
        $Model = ClassRegistry::init('Admin.'.$modelName);
        if(!$Model)
        {
            return array('options' => array(), 'map' => array(), 'default_id' => 0);
        }
        if(method_exists($Model, 'ensureOwnershipSchema'))
        {
            $Model->ensureOwnershipSchema();
        }

        $customConditions = method_exists($Model, 'scopedCustomConditions')
            ? $Model->scopedCustomConditions($currentUserId, $isGlobalAdmin, $modelName)
            : array(
                'OR' => array(
                    array($modelName.'.type' => null),
                    array($modelName.'.type' => '')
                )
            );
        $orConditions = array(
            array($modelName.'.type' => $defaultTypes),
            $customConditions
        );

        $rows = (array)$Model->find('all', array(
            'conditions' => array(
                $modelName.'.status' => 'Published',
                'OR' => $orConditions
            ),
            'fields' => array($modelName.'.id', $modelName.'.name', $modelName.'.description', $modelName.'.type', $modelName.'.user_id'),
            'order' => array($modelName.'.name' => 'asc'),
            'recursive' => -1
        ));

        $options = array();
        $existing = array();
        $defaults = array();
        $map = array();
        $defaultId = 0;
        foreach($rows as $row)
        {
            if(empty($row[$modelName]['id']))
            {
                continue;
            }
            $templateId = (int)$row[$modelName]['id'];
            $templateName = (string)$row[$modelName]['name'];
            $templateType = strtoupper(trim((string)$row[$modelName]['type']));
            $templateDescription = (string)$row[$modelName]['description'];
            $map[$templateId] = array(
                'name' => $templateName,
                'description' => $templateDescription,
                'type' => $templateType
            );
            if(in_array($templateType, $defaultTypes, true))
            {
                $defaults[$templateId] = $templateName.' ('.$templateType.')';
                if($defaultId <= 0 || $templateType === 'LED')
                {
                    $defaultId = $templateId;
                }
            }
            else
            {
                $existing[$templateId] = $templateName;
            }
        }
        if(!empty($existing))
        {
            $options[$isGlobalAdmin ? __('Custom Templates') : __('My Templates')] = $existing;
        }
        if(!empty($defaults))
        {
            $options[__('Lead Default Template')] = $defaults;
        }
        if($defaultId <= 0 && !empty($existing))
        {
            $keys = array_keys($existing);
            $defaultId = (int)$keys[0];
        }
        return array('options' => $options, 'map' => $map, 'default_id' => $defaultId);
    }

    protected function _normalizeLeadTemplateOptionsForMode($templateOptions = array(), $templateMap = array(), $mode = 'sms')
    {
        $templateOptions = (array)$templateOptions;
        if(!empty($templateOptions))
        {
            return $templateOptions;
        }

        $templateMap = (array)$templateMap;
        if(empty($templateMap))
        {
            return array();
        }

        $mode = strtolower(trim((string)$mode));
        $defaultTypes = ($mode === 'email') ? array('LED', 'LDE') : array('LED', 'LDS');
        $customRows = array();
        $defaultRows = array();

        foreach($templateMap as $templateId => $templateRow)
        {
            $templateId = (int)$templateId;
            if($templateId <= 0)
            {
                continue;
            }
            $templateRow = (array)$templateRow;
            $templateName = trim((string)(isset($templateRow['name']) ? $templateRow['name'] : ('#'.$templateId)));
            if($templateName === '')
            {
                $templateName = '#'.$templateId;
            }
            $templateType = strtoupper(trim((string)(isset($templateRow['type']) ? $templateRow['type'] : '')));
            if(in_array($templateType, $defaultTypes, true))
            {
                $defaultRows[$templateId] = $templateName.' ('.$templateType.')';
            }
            else
            {
                $customRows[$templateId] = $templateName;
            }
        }

        $normalizedOptions = array();
        if(!empty($customRows))
        {
            $normalizedOptions[__('My Templates')] = $customRows;
        }
        if(!empty($defaultRows))
        {
            $normalizedOptions[__('Lead Default Template')] = $defaultRows;
        }
        return $normalizedOptions;
    }

    public function schedulesms($ids = null)
    {
        $canSend = $this->_leadCanSendMessages();
        if(!$canSend)
        {
            $this->Session->setFlash(__('No Permission!'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'manage'));
        }

        $selectedLeadIds = $this->_leadMessageIdsFromRequest($ids);
        $leadRows = array();
        $allowedLeadIds = array();
        if(!empty($selectedLeadIds))
        {
            $leadRows = $this->_leadRowsForMessaging($selectedLeadIds);
            foreach($leadRows as $row)
            {
                if(!empty($row['Lead']['id']))
                {
                    $allowedLeadIds[] = (int)$row['Lead']['id'];
                }
            }
            if(empty($allowedLeadIds))
            {
                $this->Session->setFlash(__('No allowed leads found for selected records.'),'flash',array('alert'=>'danger'));
            }
        }

        $this->_ensureLeadMessageTemplates();
        $templateBundle = $this->_leadTemplateBundle('sms');
        $templateOptions = isset($templateBundle['options']) ? (array)$templateBundle['options'] : array();
        $templateMap = isset($templateBundle['map']) ? (array)$templateBundle['map'] : array();
        $templateOptions = $this->_normalizeLeadTemplateOptionsForMode($templateOptions, $templateMap, 'sms');
        $defaultTemplateId = isset($templateBundle['default_id']) ? (int)$templateBundle['default_id'] : 0;

        $sessionUser = (array)$this->Session->read('User');
        $currentUserId = !empty($sessionUser['User']['id']) ? (int)$sessionUser['User']['id'] : 0;
        try
        {
            $CrmIntegration = ClassRegistry::init('CrmIntegration');
            if($CrmIntegration)
            {
                $CrmIntegration->ensureSchema();
                $preferredTemplateId = (int)$CrmIntegration->defaultSmsTemplateIdForUser($currentUserId);
                if($preferredTemplateId > 0 && isset($templateMap[$preferredTemplateId]))
                {
                    $defaultTemplateId = $preferredTemplateId;
                }
            }
        }
        catch(Exception $e)
        {
        }

        $smsGatewayBundle = $this->_effectiveSmsGatewayBundleForUser($currentUserId);
        $smsGatewaySettings = isset($smsGatewayBundle['settings']) ? (array)$smsGatewayBundle['settings'] : array();
        $smsGatewayRow = !empty($smsGatewaySettings['Smssetting']) ? (array)$smsGatewaySettings['Smssetting'] : array();
        $gatewaySourceMode = !empty($smsGatewayBundle['use_global']) ? 'global' : 'user';
        $gatewaySourceLabel = !empty($smsGatewayBundle['use_global']) ? __('Global SMS Settings') : __('My CRM SMS Settings');
        $gatewayMetaLabel = __('Request Type').': '.(isset($smsGatewayRow['type']) ? (string)$smsGatewayRow['type'] : 'Get');
        $gatewayConfigured = $this->_smsGatewayLooksConfigured($smsGatewaySettings);

        $selectedTemplateId = isset($this->request->data['Lead']['template_id']) ? (int)$this->request->data['Lead']['template_id'] : $defaultTemplateId;
        if($selectedTemplateId <= 0 || !isset($templateMap[$selectedTemplateId]))
        {
            $selectedTemplateId = $defaultTemplateId;
        }
        $defaultMessage = '';
        if($selectedTemplateId > 0 && isset($templateMap[$selectedTemplateId]['description']))
        {
            $defaultMessage = (string)$templateMap[$selectedTemplateId]['description'];
        }
        $defaultScheduleAt = date('d-M-Y h:i A', strtotime('+10 minutes'));

        if($this->request->is('post'))
        {
            $postData = isset($this->request->data['Lead']) ? (array)$this->request->data['Lead'] : array();
            $submitAction = strtolower(trim((string)(isset($this->request->data['submit_action']) ? $this->request->data['submit_action'] : '')));
            $selectedTemplateId = isset($postData['template_id']) ? (int)$postData['template_id'] : $selectedTemplateId;
            if($selectedTemplateId <= 0 || !isset($templateMap[$selectedTemplateId]))
            {
                $selectedTemplateId = $defaultTemplateId;
            }
            $messageText = isset($postData['message']) ? trim((string)$postData['message']) : '';
            if($messageText === '' && $selectedTemplateId > 0 && isset($templateMap[$selectedTemplateId]['description']))
            {
                $messageText = trim((string)$templateMap[$selectedTemplateId]['description']);
            }
            $scheduleAtInput = isset($postData['schedule_at']) ? trim((string)$postData['schedule_at']) : '';

            if($submitAction === 'save_default')
            {
                if($selectedTemplateId <= 0 || !isset($templateMap[$selectedTemplateId]))
                {
                    $this->Session->setFlash(__('Please select a valid template first.'),'flash',array('alert'=>'danger'));
                }
                else
                {
                    try
                    {
                        $CrmIntegration = ClassRegistry::init('CrmIntegration');
                        if(!$CrmIntegration)
                        {
                            throw new Exception(__('CRM Settings are not available right now.'));
                        }
                        $CrmIntegration->ensureSchema();
                        $saveOk = (bool)$CrmIntegration->saveUserSmsRow($currentUserId, array('default_sms_template_id' => $selectedTemplateId));
                        if(!$saveOk)
                        {
                            throw new Exception(__('Unable to save your default template right now.'));
                        }
                        $this->Session->setFlash(__('Your default template has been saved.'),'flash',array('alert'=>'success'));
                        return $this->redirect(array('action' => 'schedulesms', implode(',', $allowedLeadIds)));
                    }
                    catch(Exception $e)
                    {
                        $this->Session->setFlash($e->getMessage(),'flash',array('alert'=>'danger'));
                    }
                }
            }
            elseif($submitAction === 'schedule' && empty($allowedLeadIds))
            {
                $this->Session->setFlash(__('Please select at least one lead from Manage Leads first.'),'flash',array('alert'=>'danger'));
            }
            elseif($submitAction === 'schedule' && $messageText === '')
            {
                $this->Session->setFlash(__('Message is required.'),'flash',array('alert'=>'danger'));
            }
            elseif($submitAction === 'schedule')
            {
                $scheduledAt = $this->_leadNormalizeDateTimeInput($scheduleAtInput);
                if($scheduledAt === '')
                {
                    $this->Session->setFlash(__('Please select a valid schedule date and time.'),'flash',array('alert'=>'danger'));
                }
                else
                {
                    $scheduledTimestamp = strtotime($scheduledAt);
                    if($scheduledTimestamp !== false && $scheduledTimestamp < (time() - 60))
                    {
                        $this->Session->setFlash(__('Schedule time cannot be in the past.'),'flash',array('alert'=>'danger'));
                    }
                    else
                    {
                        $queueResult = $this->_queueLeadSmsRows(
                            $leadRows,
                            $currentUserId,
                            $selectedTemplateId,
                            $templateMap,
                            $messageText,
                            $scheduledAt,
                            $gatewaySourceMode,
                            $gatewaySourceLabel
                        );
                        $queued = (int)$queueResult['queued'];
                        $skipped = (int)$queueResult['skipped'];
                        $failed = (int)$queueResult['failed'];
                        $queueError = trim((string)(isset($queueResult['error']) ? $queueResult['error'] : ''));
                        $statusText = __('Scheduled SMS queued: ').$queued;
                        if($skipped > 0)
                        {
                            $statusText .= ' | '.__('Skipped: ').$skipped;
                        }
                        if($failed > 0)
                        {
                            $statusText .= ' | '.__('Failed: ').$failed;
                        }
                        if($queueError !== '')
                        {
                            $statusText .= ' | '.__('Error: ').$queueError;
                        }
                        if($queued <= 0 && $skipped <= 0 && $failed <= 0 && $queueError === '')
                        {
                            $statusText .= ' | '.__('No rows were queued. Please re-check selected leads and schedule table schema.');
                        }
                        if($queued > 0)
                        {
                            $this->Session->setFlash($statusText,'flash',array('alert'=>'success'));
                            return $this->redirect(array('action' => 'manage'));
                        }
                        $this->Session->setFlash($statusText,'flash',array('alert'=>'danger'));
                    }
                }
            }
            else
            {
                // Initial open from Manage Leads posts selected ids without submit_action.
                // Keep page in edit/select state; do not schedule until user explicitly clicks Schedule SMS.
            }
        }

        if(!$this->request->is('post'))
        {
            if(!isset($this->request->data['Lead']) || !is_array($this->request->data['Lead']))
            {
                $this->request->data['Lead'] = array();
            }
            $this->request->data['Lead']['template_id'] = $selectedTemplateId;
            $this->request->data['Lead']['message'] = $defaultMessage;
            $this->request->data['Lead']['schedule_at'] = $defaultScheduleAt;
        }

        $recentRows = array();
        $leadNameMap = array();
        try
        {
            $LeadSmsSchedule = ClassRegistry::init('LeadSmsSchedule');
            if($LeadSmsSchedule)
            {
                if(!$LeadSmsSchedule->ensureSchema())
                {
                    throw new Exception('Schedule table is not ready.');
                }
                $recentConditions = array();
                if(!$this->_isGlobalAdminUser())
                {
                    $recentConditions['LeadSmsSchedule.scheduled_by_user_id'] = $currentUserId;
                }
                $recentRows = (array)$LeadSmsSchedule->find('all', array(
                    'conditions' => $recentConditions,
                    'order' => array('LeadSmsSchedule.id' => 'desc'),
                    'limit' => 40,
                    'recursive' => -1
                ));
                $recentLeadIds = array();
                foreach((array)$recentRows as $recentRow)
                {
                    $recentLeadId = !empty($recentRow['LeadSmsSchedule']['lead_id']) ? (int)$recentRow['LeadSmsSchedule']['lead_id'] : 0;
                    if($recentLeadId > 0)
                    {
                        $recentLeadIds[$recentLeadId] = $recentLeadId;
                    }
                }
                if(!empty($recentLeadIds))
                {
                    $recentLeadRows = (array)$this->Lead->find('all', array(
                        'fields' => array('Lead.id','Lead.lead_name','Client.name'),
                        'conditions' => array('Lead.id' => array_values($recentLeadIds)),
                        'recursive' => 0
                    ));
                    foreach((array)$recentLeadRows as $recentLeadRow)
                    {
                        $recentLeadId = !empty($recentLeadRow['Lead']['id']) ? (int)$recentLeadRow['Lead']['id'] : 0;
                        if($recentLeadId <= 0)
                        {
                            continue;
                        }
                        $recentLeadName = trim((string)(isset($recentLeadRow['Lead']['lead_name']) ? $recentLeadRow['Lead']['lead_name'] : ''));
                        if($recentLeadName === '')
                        {
                            $recentLeadName = trim((string)(isset($recentLeadRow['Client']['name']) ? $recentLeadRow['Client']['name'] : ''));
                        }
                        $leadNameMap[$recentLeadId] = ($recentLeadName !== '' ? $recentLeadName : ('#'.$recentLeadId));
                    }
                }
            }
        }
        catch(Exception $e)
        {
            $recentRows = array();
            $leadNameMap = array();
        }

        $cakeConsolePath = str_replace('\\', '/', ROOT.DS.'lib'.DS.'Cake'.DS.'Console'.DS.'cake.php');
        $appPath = rtrim(str_replace('\\', '/', APP), '/');
        $cronCommand = 'php '.$cakeConsolePath.' LeadSmsSchedule run -app '.$appPath;
        $cronSample = '*/5 * * * * '.$cronCommand.' >/dev/null 2>&1';
        $cronToken = sha1((string)Configure::read('Security.salt').'|lead_sms_schedule');
        $cronUrl = Router::url(array(
            'controller' => 'Leads',
            'action' => 'runscheduledsmscron',
            '?' => array('token' => $cronToken, 'limit' => 200)
        ), true);
        $cronUrlCommand = 'wget -q -O /dev/null "'.$cronUrl.'"';
        $cronUrlSample = '*/5 * * * * '.$cronUrlCommand;

        $this->set('leadRows',$leadRows);
        $this->set('selectedLeadIds',$allowedLeadIds);
        $this->set('templateOptions',$templateOptions);
        $this->set('templateMap',$templateMap);
        $this->set('selectedTemplateId',$selectedTemplateId);
        $this->set('defaultMessage',$defaultMessage);
        $this->set('gatewaySourceMode',$gatewaySourceMode);
        $this->set('gatewaySourceLabel',$gatewaySourceLabel);
        $this->set('gatewayMetaLabel',$gatewayMetaLabel);
        $this->set('gatewayConfigured',$gatewayConfigured);
        $this->set('recentRows',$recentRows);
        $this->set('leadNameMap',$leadNameMap);
        $this->set('cronCommand',$cronCommand);
        $this->set('cronSample',$cronSample);
        $this->set('cronUrl',$cronUrl);
        $this->set('cronUrlSample',$cronUrlSample);
    }

    public function leadbulkmessage($mode = 'sms', $ids = null)
    {
        $mode = strtolower(trim((string)$mode));
        if(!in_array($mode, array('sms', 'email'), true))
        {
            $mode = 'sms';
        }
        $canSend = $this->_leadCanSendMessages();
        if(!$canSend)
        {
            $this->Session->setFlash(__('No Permission!'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'manage'));
        }

        $selectedLeadIds = $this->_leadMessageIdsFromRequest($ids);
        if(empty($selectedLeadIds))
        {
            $this->Session->setFlash(__('Please select at least one lead.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'manage'));
        }

        $leadRows = $this->_leadRowsForMessaging($selectedLeadIds);
        if(empty($leadRows))
        {
            $this->Session->setFlash(__('No allowed leads found for selected records.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'manage'));
        }
        $allowedLeadIds = array();
        foreach($leadRows as $row)
        {
            if(!empty($row['Lead']['id']))
            {
                $allowedLeadIds[] = (int)$row['Lead']['id'];
            }
        }

        $this->_ensureLeadMessageTemplates();
        $templateBundle = $this->_leadTemplateBundle($mode);
        $templateOptions = isset($templateBundle['options']) ? (array)$templateBundle['options'] : array();
        $templateMap = isset($templateBundle['map']) ? (array)$templateBundle['map'] : array();
        $templateOptions = $this->_normalizeLeadTemplateOptionsForMode($templateOptions, $templateMap, $mode);
        $defaultTemplateId = isset($templateBundle['default_id']) ? (int)$templateBundle['default_id'] : 0;

        $sessionUser = (array)$this->Session->read('User');
        $currentUserId = !empty($sessionUser['User']['id']) ? (int)$sessionUser['User']['id'] : 0;
        $isSuperAdmin = $this->_isSuperAdminSession();
        $globalEmailGateway = array(
            'Emailsetting' => array(
                'type' => isset($this->emailSettype) ? (string)$this->emailSettype : 'Mail',
                'host' => isset($this->emailHost) ? (string)$this->emailHost : '',
                'port' => isset($this->emailPort) ? (string)$this->emailPort : '',
                'username' => isset($this->emailUsername) ? (string)$this->emailUsername : '',
                'password' => isset($this->emailPassword) ? (string)$this->emailPassword : ''
            )
        );
        $globalSmsGateway = isset($this->smsSettingArr) ? (array)$this->smsSettingArr : array();
        $smsGatewayBundle = array('use_global' => 1, 'settings' => $globalSmsGateway);
        $emailGatewayBundle = array('use_global' => 1, 'settings' => $globalEmailGateway);
        try
        {
            $CrmIntegration = ClassRegistry::init('CrmIntegration');
            if($CrmIntegration)
            {
                $CrmIntegration->ensureSchema();
                $smsGatewayBundle = (array)$CrmIntegration->effectiveSmsGatewayForUser($currentUserId, $globalSmsGateway);
                $emailGatewayBundle = (array)$CrmIntegration->effectiveEmailGatewayForUser($currentUserId, $globalEmailGateway);
                if($mode === 'email')
                {
                    $preferredTemplateId = (int)$CrmIntegration->defaultEmailTemplateIdForUser($currentUserId);
                    if($preferredTemplateId > 0 && isset($templateMap[$preferredTemplateId]))
                    {
                        $defaultTemplateId = $preferredTemplateId;
                    }
                }
                else
                {
                    $preferredTemplateId = (int)$CrmIntegration->defaultSmsTemplateIdForUser($currentUserId);
                    if($preferredTemplateId > 0 && isset($templateMap[$preferredTemplateId]))
                    {
                        $defaultTemplateId = $preferredTemplateId;
                    }
                }
            }
        }
        catch(Exception $e)
        {
            $smsGatewayBundle = array('use_global' => 1, 'settings' => $globalSmsGateway);
            $emailGatewayBundle = array('use_global' => 1, 'settings' => $globalEmailGateway);
        }
        $resolvedGlobalSmsBundle = (array)$this->_effectiveSmsGatewayBundleForUser(0);
        $resolvedGlobalSmsSettings = isset($resolvedGlobalSmsBundle['settings']) ? (array)$resolvedGlobalSmsBundle['settings'] : (array)$globalSmsGateway;
        if(!empty($resolvedGlobalSmsSettings))
        {
            $globalSmsGateway = $resolvedGlobalSmsSettings;
        }
        $selectedTemplateId = isset($this->request->data['Lead']['template_id']) ? (int)$this->request->data['Lead']['template_id'] : $defaultTemplateId;
        if($selectedTemplateId <= 0 || !isset($templateMap[$selectedTemplateId]))
        {
            $selectedTemplateId = $defaultTemplateId;
        }

        $defaultSubject = 'Lead Follow Up - '.$this->siteName;
        $defaultMessage = '';
        if($selectedTemplateId > 0 && isset($templateMap[$selectedTemplateId]['description']))
        {
            $defaultMessage = (string)$templateMap[$selectedTemplateId]['description'];
            if($mode === 'email' && isset($templateMap[$selectedTemplateId]['name']))
            {
                $defaultSubject = (string)$templateMap[$selectedTemplateId]['name'];
            }
        }
        $smsGatewaySettings = isset($smsGatewayBundle['settings']) ? (array)$smsGatewayBundle['settings'] : $globalSmsGateway;
        $emailGatewaySettings = isset($emailGatewayBundle['settings']) ? (array)$emailGatewayBundle['settings'] : $globalEmailGateway;
        $emailGatewayRow = !empty($emailGatewaySettings['Emailsetting']) ? (array)$emailGatewaySettings['Emailsetting'] : array();
        $emailGatewayType = isset($emailGatewayRow['type']) ? (string)$emailGatewayRow['type'] : 'Mail';
        $gatewaySourceMode = 'global';
        $gatewaySourceLabel = ($mode === 'email') ? __('Global Email Settings') : __('Global SMS Settings');
        $gatewayMetaLabel = '';
        $gatewayConfigured = true;
        $smsGatewayMode = ($mode === 'sms' && $isSuperAdmin) ? 'assigned_user' : 'default';
        if($mode === 'sms' && $isSuperAdmin && isset($this->request->data['Lead']['sms_gateway_mode']))
        {
            $postedSmsGatewayMode = strtolower(trim((string)$this->request->data['Lead']['sms_gateway_mode']));
            if(in_array($postedSmsGatewayMode, array('assigned_user', 'global', 'default'), true))
            {
                $smsGatewayMode = $postedSmsGatewayMode;
            }
        }
        if($mode === 'email')
        {
            $gatewaySourceMode = !empty($emailGatewayBundle['use_global']) ? 'global' : 'user';
            $gatewaySourceLabel = !empty($emailGatewayBundle['use_global']) ? __('Global Email Settings') : __('My CRM Email Settings');
            $gatewayMetaLabel = __('Transport').': '.($emailGatewayType === 'Smtp' ? 'SMTP' : 'Mail');
            if($emailGatewayType === 'Smtp')
            {
                $gatewayConfigured = (
                    trim((string)(isset($emailGatewayRow['host']) ? $emailGatewayRow['host'] : '')) !== '' &&
                    trim((string)(isset($emailGatewayRow['port']) ? $emailGatewayRow['port'] : '')) !== ''
                );
            }
        }
        else
        {
            $smsGatewayRow = !empty($smsGatewaySettings['Smssetting']) ? (array)$smsGatewaySettings['Smssetting'] : array();
            if($isSuperAdmin && $smsGatewayMode === 'global')
            {
                $gatewaySourceMode = 'global';
                $gatewaySourceLabel = __('Global SMS Settings (Forced)');
                $gatewayMetaLabel = __('Request Type').': '.(isset($globalSmsGateway['Smssetting']['type']) ? (string)$globalSmsGateway['Smssetting']['type'] : 'Get');
                $gatewayConfigured = $this->_smsGatewayLooksConfigured($globalSmsGateway);
            }
            elseif($isSuperAdmin && $smsGatewayMode === 'assigned_user')
            {
                $gatewaySourceMode = 'assigned_user';
                $gatewaySourceLabel = __('Assigned User SMS Settings (Per Lead)');
                $gatewayMetaLabel = __('Each lead uses its assigned user CRM SMS mode (My Own or Global).');
                $gatewayConfigured = true;
            }
            else
            {
                $gatewaySourceMode = !empty($smsGatewayBundle['use_global']) ? 'global' : 'user';
                $gatewaySourceLabel = !empty($smsGatewayBundle['use_global']) ? __('Global SMS Settings') : __('My CRM SMS Settings');
                $gatewayMetaLabel = __('Request Type').': '.(isset($smsGatewayRow['type']) ? (string)$smsGatewayRow['type'] : 'Get');
                $gatewayConfigured = (trim((string)(isset($smsGatewayRow['api']) ? $smsGatewayRow['api'] : '')) !== '');
            }
        }

        if($this->request->is('post'))
        {
            $postData = isset($this->request->data['Lead']) ? (array)$this->request->data['Lead'] : array();
            $submitAction = strtolower(trim((string)(isset($this->request->data['submit_action']) ? $this->request->data['submit_action'] : '')));
            $selectedTemplateId = isset($postData['template_id']) ? (int)$postData['template_id'] : $selectedTemplateId;
            $subjectText = isset($postData['subject']) ? trim((string)$postData['subject']) : $defaultSubject;
            $messageText = isset($postData['message']) ? trim((string)$postData['message']) : '';
            if($messageText === '' && $selectedTemplateId > 0 && isset($templateMap[$selectedTemplateId]['description']))
            {
                $messageText = trim((string)$templateMap[$selectedTemplateId]['description']);
            }
            if($subjectText === '' && $selectedTemplateId > 0 && isset($templateMap[$selectedTemplateId]['name']))
            {
                $subjectText = trim((string)$templateMap[$selectedTemplateId]['name']);
            }

            if($submitAction === 'save_default')
            {
                if($selectedTemplateId <= 0 || !isset($templateMap[$selectedTemplateId]))
                {
                    $this->Session->setFlash(__('Please select a valid template first.'),'flash',array('alert'=>'danger'));
                }
                else
                {
                    try
                    {
                        $CrmIntegration = ClassRegistry::init('CrmIntegration');
                        if(!$CrmIntegration)
                        {
                            throw new Exception(__('CRM Settings are not available right now.'));
                        }
                        $CrmIntegration->ensureSchema();
                        $saveOk = false;
                        if($mode === 'email')
                        {
                            $saveOk = (bool)$CrmIntegration->saveUserEmailRow($currentUserId, array('default_email_template_id' => $selectedTemplateId));
                        }
                        else
                        {
                            $saveOk = (bool)$CrmIntegration->saveUserSmsRow($currentUserId, array('default_sms_template_id' => $selectedTemplateId));
                        }
                        if(!$saveOk)
                        {
                            throw new Exception(__('Unable to save your default template right now.'));
                        }
                        $this->Session->setFlash(__('Your default template has been saved.'),'flash',array('alert'=>'success'));
                        return $this->redirect(array('action' => 'leadbulkmessage', $mode, implode(',', $allowedLeadIds)));
                    }
                    catch(Exception $e)
                    {
                        $this->Session->setFlash($e->getMessage(),'flash',array('alert'=>'danger'));
                    }
                }
            }
            elseif($submitAction === 'send' && $messageText === '')
            {
                $this->Session->setFlash(__('Message is required.'),'flash',array('alert'=>'danger'));
            }
            elseif($submitAction === 'send' && $mode === 'email' && $subjectText === '')
            {
                $this->Session->setFlash(__('Subject is required for email.'),'flash',array('alert'=>'danger'));
            }
            elseif($submitAction === 'send')
            {
                $sent = 0;
                $skipped = 0;
                $failed = 0;
                $assignedGatewayCache = array();
                foreach($leadRows as $leadRow)
                {
                    if($mode === 'sms')
                    {
                        $toMobile = $this->_leadMobileForMessage($leadRow);
                        $smsBody = $this->_leadTemplateRender($messageText, $leadRow);
                        $smsSendSettings = $smsGatewaySettings;
                        $smsSendGatewayLabel = $gatewaySourceLabel;
                        if($isSuperAdmin && $smsGatewayMode === 'global')
                        {
                            $smsSendSettings = $globalSmsGateway;
                            $smsSendGatewayLabel = __('Global SMS Settings (Forced)');
                        }
                        elseif($isSuperAdmin && $smsGatewayMode === 'assigned_user')
                        {
                            $assignedUserId = !empty($leadRow['Lead']['user_id']) ? (int)$leadRow['Lead']['user_id'] : 0;
                            if($assignedUserId > 0)
                            {
                                if(!isset($assignedGatewayCache[$assignedUserId]))
                                {
                                    $assignedGatewayCache[$assignedUserId] = (array)$this->_effectiveSmsGatewayBundleForUser($assignedUserId);
                                }
                                $assignedBundle = (array)$assignedGatewayCache[$assignedUserId];
                            }
                            else
                            {
                                $assignedBundle = (array)$resolvedGlobalSmsBundle;
                            }
                            $smsSendSettings = isset($assignedBundle['settings']) ? (array)$assignedBundle['settings'] : (array)$smsGatewaySettings;
                            $assignedUserName = trim((string)(isset($leadRow['User']['name']) ? $leadRow['User']['name'] : ''));
                            if(!empty($assignedBundle['use_global']))
                            {
                                $smsSendGatewayLabel = __('Assigned User via Global SMS').($assignedUserName !== '' ? ' - '.$assignedUserName : '');
                            }
                            else
                            {
                                $smsSendGatewayLabel = __('Assigned User Own SMS').($assignedUserName !== '' ? ' - '.$assignedUserName : '');
                            }
                        }
                        if($toMobile === '')
                        {
                            $this->_logLeadHistoryMessage($leadRow, 'sms', 'skipped', '', '', $smsBody, $smsSendGatewayLabel, 'No mobile number found.');
                            $skipped++;
                            continue;
                        }
                        if(!$this->_smsGatewayLooksConfigured($smsSendSettings))
                        {
                            $this->_logLeadHistoryMessage($leadRow, 'sms', 'failed', $toMobile, '', $smsBody, $smsSendGatewayLabel, 'SMS gateway is not configured.');
                            $failed++;
                            continue;
                        }
                        try
                        {
                            $this->CustomFunction->sendSms($toMobile, $smsBody, $smsSendSettings);
                            $this->_logLeadHistoryMessage($leadRow, 'sms', 'sent', $toMobile, '', $smsBody, $smsSendGatewayLabel);
                            $sent++;
                        }
                        catch(Exception $e)
                        {
                            $this->_logLeadHistoryMessage($leadRow, 'sms', 'failed', $toMobile, '', $smsBody, $smsSendGatewayLabel, trim((string)$e->getMessage()));
                            $failed++;
                        }
                    }
                    else
                    {
                        $toEmail = $this->_leadEmailForMessage($leadRow);
                        $emailSubject = $this->_leadTemplateRender($subjectText, $leadRow);
                        $emailBody = $this->_leadTemplateRender($messageText, $leadRow);
                        if($toEmail === '')
                        {
                            $this->_logLeadHistoryMessage($leadRow, 'email', 'skipped', '', $emailSubject, $emailBody, $gatewaySourceLabel, 'No email address found.');
                            $skipped++;
                            continue;
                        }
                        try
                        {
                            $Email = new CakeEmail();
                            $Email->transport($emailGatewayType);
                            if($emailGatewayType=="Smtp")
                            {
                                $Email->config(array(
                                    'host' => isset($emailGatewayRow['host']) ? $emailGatewayRow['host'] : '',
                                    'port' => isset($emailGatewayRow['port']) ? $emailGatewayRow['port'] : '',
                                    'username' => isset($emailGatewayRow['username']) ? $emailGatewayRow['username'] : '',
                                    'password' => isset($emailGatewayRow['password']) ? $emailGatewayRow['password'] : ''
                                ));
                            }
                            $Email->from(array($this->siteEmail => $this->siteName));
                            $Email->to($toEmail);
                            $Email->template('default');
                            $Email->emailFormat('html');
                            $Email->subject($emailSubject);
                            $Email->send($emailBody);
                            $this->_logLeadHistoryMessage($leadRow, 'email', 'sent', $toEmail, $emailSubject, $emailBody, $gatewaySourceLabel);
                            $sent++;
                        }
                        catch(Exception $e)
                        {
                            $this->_logLeadHistoryMessage($leadRow, 'email', 'failed', $toEmail, $emailSubject, $emailBody, $gatewaySourceLabel, trim((string)$e->getMessage()));
                            $failed++;
                        }
                    }
                }
                $statusText = ucfirst($mode).' '.__('sent: ').$sent;
                if($skipped > 0)
                {
                    $statusText .= ' | '.__('Skipped: ').$skipped;
                }
                if($failed > 0)
                {
                    $statusText .= ' | '.__('Failed: ').$failed;
                }
                if($sent > 0)
                {
                    $this->Session->setFlash($statusText,'flash',array('alert'=>'success'));
                }
                else
                {
                    $this->Session->setFlash($statusText,'flash',array('alert'=>'danger'));
                }
                return $this->redirect(array('action' => 'manage'));
            }
        }

        if(!$this->request->is('post'))
        {
            if(!isset($this->request->data['Lead']) || !is_array($this->request->data['Lead']))
            {
                $this->request->data['Lead'] = array();
            }
            $this->request->data['Lead']['template_id'] = $selectedTemplateId;
            $this->request->data['Lead']['message'] = $defaultMessage;
            if($mode === 'sms' && $isSuperAdmin)
            {
                $this->request->data['Lead']['sms_gateway_mode'] = $smsGatewayMode;
            }
            if($mode === 'email')
            {
                $this->request->data['Lead']['subject'] = $defaultSubject;
            }
        }

        $this->set('mode',$mode);
        $this->set('leadRows',$leadRows);
        $this->set('selectedLeadIds',$allowedLeadIds);
        $this->set('templateOptions',$templateOptions);
        $this->set('templateMap',$templateMap);
        $this->set('selectedTemplateId',$selectedTemplateId);
        $this->set('defaultSubject',$defaultSubject);
        $this->set('defaultMessage',$defaultMessage);
        $this->set('gatewaySourceMode',$gatewaySourceMode);
        $this->set('gatewaySourceLabel',$gatewaySourceLabel);
        $this->set('gatewayMetaLabel',$gatewayMetaLabel);
        $this->set('gatewayConfigured',$gatewayConfigured);
        $this->set('isSuperAdmin',$isSuperAdmin);
        $this->set('smsGatewayMode',$smsGatewayMode);
    }
    public function index($id=null)
    {
        if($id!==null && $id!=='')
        {
            return $this->redirect(array('action' => 'dashboard', (int)$id));
        }
        return $this->redirect(array('action' => 'dashboard'));
    }

    public function brochureleads()
    {
        $this->request->params['named']['lead_source'] = 'brochure';
        $this->request->params['named']['period'] = 'all';
        $this->set('isBrochureLeadsView', true);
        $this->set('customPageTitle', __('Brochure & PDF Download Leads'));
        $this->setAction('manage');
    }

    protected function _autoSyncFacebookLeadsToCrm()
    {
        try
        {
            $missingInLogs = $this->Lead->query("SELECT l.id FROM leads l LEFT JOIN facebook_lead_logs log ON log.lead_id = l.id WHERE log.id IS NULL AND (l.lead_source LIKE '%Facebook%' OR l.lead_source LIKE '%FB%') LIMIT 1");
            $unlinked = $this->Lead->query("SELECT id FROM facebook_lead_logs WHERE lead_id IS NULL OR lead_id = 0 LIMIT 1");
            if(!empty($unlinked) || !empty($missingInLogs))
            {
                App::uses('FacebookIntegrationsController', 'Admin.Controller');
                $fbCtrl = new FacebookIntegrationsController();
                $fbCtrl->constructClasses();
                $fbCtrl->_ensureAllFacebookLogsInCrmLeads();
            }
        }
        catch(Exception $e)
        {
        }
    }

    public function manage($id=null)
    {
        $this->_autoSyncFacebookLeadsToCrm();
        $this->Prg->commonProcess();
        $parsedParams = (array)$this->Prg->parsedParams();
        $namedParams = isset($this->request->params['named']) ? (array)$this->request->params['named'] : array();
        $rawParams = array_merge($namedParams, $parsedParams);
        if(!empty($this->request->data['Lead']) && is_array($this->request->data['Lead']))
        {
            $rawParams = array_merge($rawParams, (array)$this->request->data['Lead']);
        }
        $conditionBundle = $this->_leadManageConditions($id, $parsedParams, $rawParams);
        $filters = (array)$conditionBundle['filters'];
        $todayStart = date('Y-m-d 00:00:00');

        $this->Lead->unbindModel(array('belongsTo' => array('Property','Contract','Type')), false);
        $this->Paginator->settings = $this->paginate;
        $this->Paginator->settings['recursive'] = -1;
        $this->Paginator->settings['joins'] = array(
            array(
                'table' => 'clients',
                'alias' => 'Client',
                'type' => 'LEFT',
                'conditions' => array('Client.id = Lead.client_id')
            ),
            array(
                'table' => 'statuses',
                'alias' => 'Status',
                'type' => 'LEFT',
                'conditions' => array('Status.id = Lead.status_id')
            )
        );
        $this->Paginator->settings['fields'] = array(
            'Lead.id','Lead.client_id','Lead.project_id','Lead.user_id','Lead.status_id',
            'Lead.last_contact_date','Lead.follow_up','Lead.remarks','Lead.lead_name',
            'Lead.lead_mobile','Lead.lead_email','Lead.lead_source','Lead.created',
            'Client.id','Client.name','Client.mobile','Client.phone','Client.email','Client.user_id',
            'Status.id','Status.name','Status.color'
        );
        $this->Paginator->settings['conditions'] = (array)$conditionBundle['conditions'];
	$this->Paginator->settings['limit']=$this->pageLimit;
        $this->Paginator->settings['maxLimit']=$this->maxLimit;
        $this->Paginator->settings['order'] = array('Lead.id' => 'DESC');
        $leadPageRows = (array)$this->Paginator->paginate();
        $this->set('Lead', $leadPageRows);

        $showAssignedAgentColumn = (strtoupper(trim((string)$this->userType)) !== 'AGT');
        $assignedAgentMap = array();
        $assignedAgentRoleMap = array();
        if($showAssignedAgentColumn)
        {
            $leadAgentIds = array();
            foreach($leadPageRows as $leadPageRow)
            {
                $leadAgentId = !empty($leadPageRow['Lead']['user_id']) ? (int)$leadPageRow['Lead']['user_id'] : (!empty($leadPageRow['Client']['user_id']) ? (int)$leadPageRow['Client']['user_id'] : 0);
                if($leadAgentId > 0)
                {
                    $leadAgentIds[$leadAgentId] = $leadAgentId;
                }
            }
            if(!empty($leadAgentIds))
            {
                $this->loadModel('User');
                $assignedAgentRows = (array)$this->User->find('all', array(
                    'fields' => array('User.id','User.name','Ugroup.name'),
                    'conditions' => array('User.id' => array_values($leadAgentIds)),
                    'recursive' => 0
                ));
                foreach($assignedAgentRows as $assignedAgentRow)
                {
                    $assignedAgentId = !empty($assignedAgentRow['User']['id']) ? (int)$assignedAgentRow['User']['id'] : 0;
                    if($assignedAgentId <= 0)
                    {
                        continue;
                    }
                    $assignedAgentMap[$assignedAgentId] = !empty($assignedAgentRow['User']['name']) ? (string)$assignedAgentRow['User']['name'] : '';
                    $assignedAgentRoleMap[$assignedAgentId] = !empty($assignedAgentRow['Ugroup']['name']) ? (string)$assignedAgentRow['Ugroup']['name'] : '';
                }
                if(empty($assignedAgentMap))
                {
                    $assignedAgentMap = (array)$this->User->find('list', array(
                        'fields' => array('User.id','User.name'),
                        'conditions' => array('User.id' => array_values($leadAgentIds)),
                        'recursive' => -1
                    ));
                }
                if(empty($assignedAgentRoleMap))
                {
                    foreach($assignedAgentMap as $assignedAgentId => $assignedAgentName)
                    {
                        $assignedAgentRoleMap[(int)$assignedAgentId] = '';
                    }
                }
            }
            else
            {
                // Initial open from Manage Leads posts selected ids without submit_action.
                // Keep page in template selection/edit mode; do not send until user clicks Send.
            }
        }

        $canUseAgencyFilters = $this->_leadCanUseAgencyFilters();
        $canUseAgentFilters = $this->_leadCanUseAgentFilters();
        $canUseUserLevelFilters = $this->_leadCanUseUserLevelFilters();
        $projectOptions = array();
        if($canUseAgencyFilters)
        {
            $this->loadModel('Project');
            $projectOptions = (array)$this->Project->find('list', array('fields' => array('Project.id','Project.name'),'order' => array('Project.name' => 'asc')));
        }
        $agentFilterOptions = $this->_leadAgentFilterOptions((int)$filters['project_id']);
        $canUseAgentFilters = $this->_leadCanUseAgentFilters();
        $selectedAgentName = __('All Agents');
        if((int)$filters['user_id'] > 0)
        {
            if(isset($agentFilterOptions[(int)$filters['user_id']]))
            {
                $selectedAgentName = (string)$agentFilterOptions[(int)$filters['user_id']];
            }
            else
            {
                $this->loadModel('User');
                $selectedAgentName = (string)$this->User->field('name', array('User.id' => (int)$filters['user_id']));
            }
            if(trim((string)$selectedAgentName) === '')
            {
                $selectedAgentName = __('All Agents');
            }
        }

        $selectedAgencyName = __('All Agency');
        if(!$canUseAgencyFilters)
        {
            $selectedAgencyName = $this->siteOrganization;
        }
        elseif((int)$filters['project_id'] > 0 && isset($projectOptions[(int)$filters['project_id']]))
        {
            $selectedAgencyName = (string)$projectOptions[(int)$filters['project_id']];
        }

        $selectedDateRangeLabel = __('All Dates');
        if(trim((string)$filters['start_date']) !== '' || trim((string)$filters['end_date']) !== '')
        {
            $startLabel = trim((string)$filters['start_date']) !== '' ? date('d-M-Y', strtotime($filters['start_date'])) : __('N/A');
            $endLabel = trim((string)$filters['end_date']) !== '' ? date('d-M-Y', strtotime($filters['end_date'])) : __('N/A');
            $selectedDateRangeLabel = $startLabel.' '.__('to').' '.$endLabel;
        }
        $selectedKeyword = '';
        if(isset($parsedParams['keyword']))
        {
            $selectedKeyword = trim((string)$parsedParams['keyword']);
        }
        elseif(isset($namedParams['keyword']))
        {
            $selectedKeyword = trim((string)$namedParams['keyword']);
        }

        $exportNamedParams = array();
        if($selectedKeyword !== '')$exportNamedParams['keyword'] = $selectedKeyword;
        if($canUseAgencyFilters && (int)$filters['project_id'] > 0)$exportNamedParams['project_id'] = (int)$filters['project_id'];
        if((int)$filters['user_id'] > 0)$exportNamedParams['user_id'] = (int)$filters['user_id'];
        if(trim((string)$filters['period']) !== '')$exportNamedParams['period'] = (string)$filters['period'];
        if(trim((string)$filters['start_date_input']) !== '')$exportNamedParams['start_date'] = (string)$filters['start_date_input'];
        if(trim((string)$filters['end_date_input']) !== '')$exportNamedParams['end_date'] = (string)$filters['end_date_input'];
        $exportCsvUrl = array_merge(array('controller' => 'Leads', 'action' => 'exportcsv'), $exportNamedParams);

        $selectedFilterSummaryParts = array();
        $selectedFilterSummaryParts[] = __('Period').': '.$filters['period_label'];
        $selectedFilterSummaryParts[] = __('Date Range').': '.$selectedDateRangeLabel;
        if($selectedKeyword !== '')
        {
            $selectedFilterSummaryParts[] = __('Search').': '.$selectedKeyword;
        }

        $this->loadModel('Status');
        $statusRows = (array)$this->Status->find('all', array(
            'fields' => array('Status.id','Status.name','Status.color'),
            'order' => array('Status.name' => 'asc'),
            'recursive' => -1
        ));
        $statusOptions = array();
        $statusMetaMap = array();
        foreach($statusRows as $statusRow)
        {
            $statusId = !empty($statusRow['Status']['id']) ? (int)$statusRow['Status']['id'] : 0;
            if($statusId <= 0)
            {
                continue;
            }
            $statusName = !empty($statusRow['Status']['name']) ? (string)$statusRow['Status']['name'] : '';
            $statusColor = !empty($statusRow['Status']['color']) ? (string)$statusRow['Status']['color'] : '#337ab7';
            $statusOptions[$statusId] = $statusName;
            $statusMetaMap[$statusId] = array(
                'name' => $statusName,
                'color' => $statusColor
            );
        }

        $this->set('reportAgency', $this->_leadResolveReportAgency((int)$filters['project_id']));
        $this->set('projectName', $projectOptions);
        $this->set('agentFilterOptions', $agentFilterOptions);
        $this->set('periodOptions', $this->_leadPeriodOptions());
        $this->set('canUseAgencyFilters', $canUseAgencyFilters);
        $this->set('canUseAgentFilters', $canUseAgentFilters);
        $this->set('selectedProjectFilter', (int)$filters['project_id']);
        $this->set('selectedAgentFilter', (int)$filters['user_id']);
        $this->set('selectedPeriodFilter', (string)$filters['period']);
        $this->set('selectedPeriodLabel', (string)$filters['period_label']);
        $this->set('selectedLeadSource', (string)$filters['lead_source']);
        $this->set('selectedStartDateInput', (string)$filters['start_date_input']);
        $this->set('selectedEndDateInput', (string)$filters['end_date_input']);
        $this->set('selectedDateRangeLabel', $selectedDateRangeLabel);
        $this->set('selectedAgentName', $selectedAgentName);
        $this->set('selectedAgencyName', $selectedAgencyName);
        $this->set('selectedKeyword', $selectedKeyword);
        $this->set('selectedFilterSummary', implode(' | ', $selectedFilterSummaryParts));
        $this->set('exportCsvUrl', $exportCsvUrl);
        $this->set('showAssignedAgentColumn', $showAssignedAgentColumn);
        $this->set('assignedAgentMap', $assignedAgentMap);
        $this->set('assignedAgentRoleMap', $assignedAgentRoleMap);
        $this->set('statusOptions', $statusOptions);
        $this->set('statusMetaMap', $statusMetaMap);
	if ($this->request->is('ajax'))
        {
            $this->render('index','ajax'); // View, Layout
            return;
        }
        $this->render('index');
    }

    public function dailyprogress()
    {
        $this->loadModel('LeadHistory');
        $this->loadModel('Project');
        $this->loadModel('User');
        $this->LeadHistory->ensureSchema();

        $canUseAgencyFilters = $this->_leadCanUseAgencyFilters();
        $canUseAgentFilters = $this->_leadCanUseAgentFilters();
        $projectOptions = $this->CustomFunction->agencyWiseList($this->userType,$this->agencyId);

        $selectedProjectId = 0;
        if($canUseAgencyFilters && isset($this->request->query['project_id']))
        {
            $selectedProjectId = max(0,(int)$this->request->query['project_id']);
        }
        elseif(!$canUseAgencyFilters && (int)$this->agencyId > 0 && strtoupper(trim((string)$this->userType)) !== 'AGT')
        {
            $selectedProjectId = (int)$this->agencyId;
        }

        $agentOptions = $this->_leadAgentFilterOptions($selectedProjectId);
        $selectedAgentId = 0;
        if($canUseAgentFilters && isset($this->request->query['user_id']))
        {
            $requestedAgentId = (int)$this->request->query['user_id'];
            if($requestedAgentId > 0 && isset($agentOptions[$requestedAgentId]))
            {
                $selectedAgentId = $requestedAgentId;
            }
        }
        if(!$canUseAgentFilters)
        {
            $selectedAgentId = (int)$this->luserId;
        }

        $periodOptions = array(
            'today' => __('Today'),
            'week' => __('Week'),
            'month' => __('Month'),
            'all' => __('All Time'),
            'custom' => __('Custom Date Range')
        );
        $selectedPeriod = isset($this->request->query['period']) ? strtolower(trim((string)$this->request->query['period'])) : 'today';
        if(!isset($periodOptions[$selectedPeriod]))
        {
            $selectedPeriod = 'today';
        }
        $selectedStartDateInput = isset($this->request->query['start_date']) ? trim((string)$this->request->query['start_date']) : '';
        $selectedEndDateInput = isset($this->request->query['end_date']) ? trim((string)$this->request->query['end_date']) : '';
        $startDate = '';
        $endDate = '';
        if($selectedPeriod === 'today')
        {
            $startDate = date('Y-m-d');
            $endDate = date('Y-m-d');
        }
        elseif($selectedPeriod === 'week')
        {
            $startDate = date('Y-m-d', strtotime('monday this week'));
            $endDate = date('Y-m-d', strtotime('sunday this week'));
        }
        elseif($selectedPeriod === 'month')
        {
            $startDate = date('Y-m-01');
            $endDate = date('Y-m-t');
        }
        elseif($selectedPeriod === 'custom')
        {
            $startDate = $this->_leadNormalizeDateInput($selectedStartDateInput);
            $endDate = $this->_leadNormalizeDateInput($selectedEndDateInput);
        }

        $conditions = array();
        if($selectedProjectId > 0)
        {
            $conditions[] = array('OR' => array('LeadHistory.project_id' => $selectedProjectId, 'Lead.project_id' => $selectedProjectId));
        }
        if($selectedAgentId > 0)
        {
            $conditions[] = array('OR' => array('LeadHistory.lead_user_id' => $selectedAgentId, 'Lead.user_id' => $selectedAgentId));
        }
        if($startDate !== '' && $endDate !== '')
        {
            $conditions[] = array('LeadHistory.created >=' => $startDate.' 00:00:00', 'LeadHistory.created <=' => $endDate.' 23:59:59');
        }
        elseif($startDate !== '')
        {
            $conditions[] = array('LeadHistory.created >=' => $startDate.' 00:00:00');
        }
        elseif($endDate !== '')
        {
            $conditions[] = array('LeadHistory.created <=' => $endDate.' 23:59:59');
        }

        $joins = array(
            array('table'=>'leads','alias'=>'Lead','type'=>'LEFT','conditions'=>array('Lead.id=LeadHistory.lead_id')),
            array('table'=>'clients','alias'=>'Client','type'=>'LEFT','conditions'=>array('Client.id=Lead.client_id')),
            array('table'=>'projects','alias'=>'Project','type'=>'LEFT','conditions'=>array('Project.id=COALESCE(LeadHistory.project_id,Lead.project_id)')),
            array('table'=>'users','alias'=>'AssignedUser','type'=>'LEFT','conditions'=>array('AssignedUser.id=COALESCE(LeadHistory.lead_user_id,Lead.user_id)'))
        );
        $historyRows = (array)$this->LeadHistory->find('all', array(
            'joins' => $joins,
            'fields' => array('LeadHistory.*','Lead.id','Lead.lead_name','Lead.lead_mobile','Lead.follow_up','Lead.remarks','Client.mobile','Client.phone','Project.name','AssignedUser.name'),
            'conditions' => $conditions,
            'order' => array('LeadHistory.created' => 'desc', 'LeadHistory.id' => 'desc'),
            'limit' => 500,
            'recursive' => -1
        ));

        $changeStats = array();
        $statusStats = array();
        $agentStats = array();
        $agentDateStats = array();
        foreach($historyRows as $historyRow)
        {
            $history = isset($historyRow['LeadHistory']) ? (array)$historyRow['LeadHistory'] : array();
            $fieldLabel = !empty($history['field_label']) ? (string)$history['field_label'] : (!empty($history['title']) ? (string)$history['title'] : __('Other'));
            if(!isset($changeStats[$fieldLabel]))$changeStats[$fieldLabel] = 0;
            $changeStats[$fieldLabel]++;
            if(!empty($history['field_name']) && $history['field_name'] === 'status_id')
            {
                $statusName = trim((string)(isset($history['new_value']) ? $history['new_value'] : ''));
                if($statusName === '')$statusName = __('Unknown');
                if(!isset($statusStats[$statusName]))$statusStats[$statusName] = 0;
                $statusStats[$statusName]++;
            }
            $agentName = !empty($historyRow['AssignedUser']['name']) ? (string)$historyRow['AssignedUser']['name'] : __('Unassigned');
            if(!isset($agentStats[$agentName]))$agentStats[$agentName] = 0;
            $agentStats[$agentName]++;
            $changeDate = !empty($history['created']) ? date('Y-m-d', strtotime($history['created'])) : __('Unknown');
            if(!isset($agentDateStats[$agentName]))
            {
                $agentDateStats[$agentName] = array();
            }
            if(!isset($agentDateStats[$agentName][$changeDate]))
            {
                $agentDateStats[$agentName][$changeDate] = array(
                    'total' => 0,
                    'status' => 0,
                    'follow_up' => 0,
                    'remarks' => 0,
                    'contact' => 0,
                    'other' => 0
                );
            }
            $agentDateStats[$agentName][$changeDate]['total']++;
            $fieldName = isset($history['field_name']) ? trim((string)$history['field_name']) : '';
            if($fieldName === 'status_id')
            {
                $agentDateStats[$agentName][$changeDate]['status']++;
            }
            elseif($fieldName === 'follow_up' || $fieldName === 'last_contact_date')
            {
                $agentDateStats[$agentName][$changeDate]['follow_up']++;
            }
            elseif($fieldName === 'remarks')
            {
                $agentDateStats[$agentName][$changeDate]['remarks']++;
            }
            elseif(in_array($fieldName, array('lead_mobile','lead_email','lead_name','client_id'), true))
            {
                $agentDateStats[$agentName][$changeDate]['contact']++;
            }
            else
            {
                $agentDateStats[$agentName][$changeDate]['other']++;
            }
        }
        arsort($changeStats);
        arsort($statusStats);
        arsort($agentStats);
        foreach($agentDateStats as $agentName => $dateRows)
        {
            krsort($dateRows);
            $agentDateStats[$agentName] = $dateRows;
        }

        $leadProgressBaseConditions = array($this->_leadPermissionCondition(null));
        if($selectedProjectId > 0)
        {
            $leadProgressBaseConditions[] = array('Lead.project_id' => $selectedProjectId);
        }
        if($selectedAgentId > 0)
        {
            $leadProgressBaseConditions[] = array('Lead.user_id' => $selectedAgentId);
        }
        $agentLeadTotals = array();
        $agentDueLeadIds = array();
        $isLifetimeProgress = ($startDate === '' && $endDate === '');
        if($isLifetimeProgress)
        {
            $leadCountRows = (array)$this->Lead->find('all', array(
                'fields' => array('Lead.user_id','COUNT(Lead.id) AS total_leads'),
                'conditions' => $leadProgressBaseConditions,
                'group' => array('Lead.user_id'),
                'recursive' => -1
            ));
            foreach($leadCountRows as $leadCountRow)
            {
                $agentIdForCount = !empty($leadCountRow['Lead']['user_id']) ? (int)$leadCountRow['Lead']['user_id'] : 0;
                if($agentIdForCount > 0)
                {
                    $agentLeadTotals[$agentIdForCount] = isset($leadCountRow[0]['total_leads']) ? (int)$leadCountRow[0]['total_leads'] : 0;
                }
            }
        }
        else
        {
            $currentDueConditions = $leadProgressBaseConditions;
            if($startDate !== '' && $endDate !== '')
            {
                $currentDueConditions[] = array('Lead.follow_up >=' => $startDate.' 00:00:00', 'Lead.follow_up <=' => $endDate.' 23:59:59');
            }
            elseif($startDate !== '')
            {
                $currentDueConditions[] = array('Lead.follow_up >=' => $startDate.' 00:00:00');
            }
            elseif($endDate !== '')
            {
                $currentDueConditions[] = array('Lead.follow_up <=' => $endDate.' 23:59:59');
            }
            $currentDueRows = (array)$this->Lead->find('all', array(
                'fields' => array('Lead.id','Lead.user_id'),
                'conditions' => $currentDueConditions,
                'recursive' => -1
            ));
            foreach($currentDueRows as $currentDueRow)
            {
                $agentIdForDue = !empty($currentDueRow['Lead']['user_id']) ? (int)$currentDueRow['Lead']['user_id'] : 0;
                $leadIdForDue = !empty($currentDueRow['Lead']['id']) ? (int)$currentDueRow['Lead']['id'] : 0;
                if($agentIdForDue > 0 && $leadIdForDue > 0)
                {
                    if(!isset($agentDueLeadIds[$agentIdForDue]))$agentDueLeadIds[$agentIdForDue] = array();
                    $agentDueLeadIds[$agentIdForDue][$leadIdForDue] = $leadIdForDue;
                }
            }
        }

        $followupProgressConditions = array($this->_leadPermissionCondition(null), 'LeadHistory.field_name' => 'follow_up');
        if($selectedProjectId > 0)
        {
            $followupProgressConditions[] = array('OR' => array('LeadHistory.project_id' => $selectedProjectId, 'Lead.project_id' => $selectedProjectId));
        }
        if($selectedAgentId > 0)
        {
            $followupProgressConditions[] = array('OR' => array('LeadHistory.lead_user_id' => $selectedAgentId, 'Lead.user_id' => $selectedAgentId));
        }
        $followupProgressRows = (array)$this->LeadHistory->find('all', array(
            'joins' => array(array('table'=>'leads','alias'=>'Lead','type'=>'LEFT','conditions'=>array('Lead.id=LeadHistory.lead_id'))),
            'fields' => array('LeadHistory.lead_id','LeadHistory.old_value','LeadHistory.new_value','COALESCE(LeadHistory.lead_user_id,Lead.user_id) AS agent_id'),
            'conditions' => $followupProgressConditions,
            'recursive' => -1
        ));
        $agentProgressLeadIds = array();
        foreach($followupProgressRows as $followupProgressRow)
        {
            $agentIdForProgress = !empty($followupProgressRow[0]['agent_id']) ? (int)$followupProgressRow[0]['agent_id'] : 0;
            $leadIdForProgress = !empty($followupProgressRow['LeadHistory']['lead_id']) ? (int)$followupProgressRow['LeadHistory']['lead_id'] : 0;
            if($agentIdForProgress <= 0 || $leadIdForProgress <= 0)
            {
                continue;
            }
            $oldFollowupDate = $this->_leadNormalizeDateInput(isset($followupProgressRow['LeadHistory']['old_value']) ? $followupProgressRow['LeadHistory']['old_value'] : '');
            if(!$isLifetimeProgress)
            {
                if($oldFollowupDate === '')
                {
                    continue;
                }
                if($startDate !== '' && $oldFollowupDate < $startDate)
                {
                    continue;
                }
                if($endDate !== '' && $oldFollowupDate > $endDate)
                {
                    continue;
                }
                if(!isset($agentDueLeadIds[$agentIdForProgress]))$agentDueLeadIds[$agentIdForProgress] = array();
                $agentDueLeadIds[$agentIdForProgress][$leadIdForProgress] = $leadIdForProgress;
            }
            if(!isset($agentProgressLeadIds[$agentIdForProgress]))$agentProgressLeadIds[$agentIdForProgress] = array();
            $agentProgressLeadIds[$agentIdForProgress][$leadIdForProgress] = $leadIdForProgress;
        }
        if(!$isLifetimeProgress)
        {
            foreach($agentDueLeadIds as $agentIdForDue => $leadIdsForDue)
            {
                $agentLeadTotals[$agentIdForDue] = count((array)$leadIdsForDue);
            }
        }
        $agentProgressRows = array();
        $agentIdsForProgress = array_unique(array_merge(array_keys($agentLeadTotals), array_keys($agentProgressLeadIds)));
        foreach($agentIdsForProgress as $agentIdForProgress)
        {
            $agentIdForProgress = (int)$agentIdForProgress;
            if($agentIdForProgress <= 0)
            {
                continue;
            }
            $totalLeadsForAgent = isset($agentLeadTotals[$agentIdForProgress]) ? (int)$agentLeadTotals[$agentIdForProgress] : 0;
            $progressedLeadsForAgent = isset($agentProgressLeadIds[$agentIdForProgress]) ? count((array)$agentProgressLeadIds[$agentIdForProgress]) : 0;
            if($progressedLeadsForAgent > $totalLeadsForAgent && $totalLeadsForAgent > 0)
            {
                $progressedLeadsForAgent = $totalLeadsForAgent;
            }
            $progressPercent = $totalLeadsForAgent > 0 ? round(($progressedLeadsForAgent / $totalLeadsForAgent) * 100, 2) : 0;
            $agentProgressRows[] = array(
                'agent_id' => $agentIdForProgress,
                'agent_name' => isset($agentOptions[$agentIdForProgress]) ? (string)$agentOptions[$agentIdForProgress] : (string)$this->User->field('name', array('User.id' => $agentIdForProgress)),
                'total_leads' => $totalLeadsForAgent,
                'progressed_leads' => $progressedLeadsForAgent,
                'percent' => $progressPercent
            );
        }
        usort($agentProgressRows, function($a, $b){
            if((float)$a['percent'] == (float)$b['percent'])
            {
                return strcmp((string)$a['agent_name'], (string)$b['agent_name']);
            }
            return ((float)$a['percent'] > (float)$b['percent']) ? -1 : 1;
        });

        $selectedAgencyName = __('All Agencies');
        if($selectedProjectId > 0)
        {
            $selectedAgencyName = (string)$this->Project->field('name', array('Project.id' => $selectedProjectId));
        }
        $selectedAgentName = __('All Agents');
        if($selectedAgentId > 0 && isset($agentOptions[$selectedAgentId]))
        {
            $selectedAgentName = (string)$agentOptions[$selectedAgentId];
        }
        $dateRangeLabel = ($startDate !== '' || $endDate !== '') ? trim($startDate.' - '.$endDate, ' -') : __('All Time');
        $reportAgency = $this->_leadResolveReportAgency($selectedProjectId);
        $showAgencyAgentColumns = $this->_isGlobalAdminUser();

        $this->set(compact('historyRows','changeStats','statusStats','agentStats','agentProgressRows','agentDateStats','projectOptions','agentOptions','periodOptions','canUseAgencyFilters','canUseAgentFilters','selectedProjectId','selectedAgentId','selectedPeriod','selectedStartDateInput','selectedEndDateInput','selectedAgencyName','selectedAgentName','dateRangeLabel','reportAgency','showAgencyAgentColumns','isLifetimeProgress'));
    }

    public function exportcsv($id = null)
    {
        $this->autoRender = false;
        $namedParams = isset($this->request->params['named']) ? (array)$this->request->params['named'] : array();
        $queryParams = isset($this->request->query) ? (array)$this->request->query : array();
        $rawParams = array_merge($namedParams, $queryParams);
        $parsedForSearch = array();
        if(isset($rawParams['keyword']))
        {
            $parsedForSearch['keyword'] = $rawParams['keyword'];
        }
        $conditionBundle = $this->_leadManageConditions($id, $parsedForSearch, $rawParams);
        $leadRows = (array)$this->Lead->find('all', array(
            'conditions' => (array)$conditionBundle['conditions'],
            'order' => array('Lead.follow_up' => 'asc', 'Lead.id' => 'desc')
        ));

        $projectIds = array();
        $userIds = array();
        foreach($leadRows as $leadRow)
        {
            $projectId = !empty($leadRow['Lead']['project_id']) ? (int)$leadRow['Lead']['project_id'] : 0;
            if($projectId > 0)$projectIds[$projectId] = $projectId;
            $userId = !empty($leadRow['Lead']['user_id']) ? (int)$leadRow['Lead']['user_id'] : 0;
            if($userId > 0)$userIds[$userId] = $userId;
        }
        $projectMap = array();
        if(!empty($projectIds))
        {
            $this->loadModel('Project');
            $projectMap = (array)$this->Project->find('list', array(
                'fields' => array('Project.id', 'Project.name'),
                'conditions' => array('Project.id' => array_values($projectIds)),
                'recursive' => -1
            ));
        }
        $agentMap = array();
        if(!empty($userIds))
        {
            $this->loadModel('User');
            $agentMap = (array)$this->User->find('list', array(
                'fields' => array('User.id', 'User.name'),
                'conditions' => array('User.id' => array_values($userIds)),
                'recursive' => -1
            ));
        }

        $filename = 'leads_'.date('Ymd_His').'.csv';
        header("Content-Type: text/csv; charset=utf-8");
        header("Content-Disposition: attachment; filename=\"".$filename."\"");
        header("Pragma: no-cache");
        header("Expires: 0");
        $out = fopen('php://output','w');
        fputcsv($out, array('#','Lead Name','SMS Mobile #','Email','Agency','Agent','Last Contact Date','Follow Up','Status','Remarks'));
        $sr = 1;
        foreach($leadRows as $leadRow)
        {
            $projectId = !empty($leadRow['Lead']['project_id']) ? (int)$leadRow['Lead']['project_id'] : 0;
            $agentId = !empty($leadRow['Lead']['user_id']) ? (int)$leadRow['Lead']['user_id'] : 0;
            $leadName = trim((string)$leadRow['Lead']['lead_name']);
            if($leadName === '')
            {
                $leadName = !empty($leadRow['Client']['name']) ? (string)$leadRow['Client']['name'] : '';
            }
            $leadMobile = trim((string)$leadRow['Lead']['lead_mobile']);
            if($leadMobile === '')
            {
                $leadMobile = !empty($leadRow['Client']['mobile']) ? (string)$leadRow['Client']['mobile'] : '';
            }
            if($leadMobile === '')
            {
                $leadMobile = !empty($leadRow['Client']['phone']) ? (string)$leadRow['Client']['phone'] : '';
            }
            $leadEmail = trim((string)$leadRow['Lead']['lead_email']);
            if($leadEmail === '')
            {
                $leadEmail = !empty($leadRow['Client']['email']) ? (string)$leadRow['Client']['email'] : '';
            }
            $followUpText = '';
            if(!empty($leadRow['Lead']['follow_up']) && $leadRow['Lead']['follow_up'] !== '0000-00-00 00:00:00')
            {
                $followUpText = date('d-M-Y H:i', strtotime($leadRow['Lead']['follow_up']));
            }
            $lastContactText = '';
            if(!empty($leadRow['Lead']['last_contact_date']) && $leadRow['Lead']['last_contact_date'] !== '0000-00-00')
            {
                $lastContactText = date('d-M-Y', strtotime($leadRow['Lead']['last_contact_date']));
            }
            elseif($followUpText !== '')
            {
                $lastContactText = date('d-M-Y', strtotime($leadRow['Lead']['follow_up']));
            }
            fputcsv($out, array(
                $sr++,
                $leadName,
                $leadMobile,
                $leadEmail,
                isset($projectMap[$projectId]) ? $projectMap[$projectId] : '',
                isset($agentMap[$agentId]) ? $agentMap[$agentId] : '',
                $lastContactText,
                $followUpText,
                !empty($leadRow['Status']['name']) ? $leadRow['Status']['name'] : '',
                trim((string)$leadRow['Lead']['remarks'])
            ));
        }
        fclose($out);
        exit();
    }
    public function propertysearch()
    {
        $this->autoRender = false;
	//$this->request->onlyAllow('ajax');
        // get the search term from URL
        $this->loadModel('Property');
        $term = $this->request->query['q'];
        $cond=array();
        if($this->request->query['q1'])
        {
            $type=$this->request->query['q1'];
            $cond[]=array('Property.type_id' => $type);
        }
	if(isset($this->request->query['q2']) && strlen($this->request->query['q2'])>0)
        {
            $cond[]=array('Property.project_id' => $this->request->query['q2']);
        }
        $condition=$this->CustomFunction->agencyWisePermission(NULL,$this->userType,$this->agencyId,'Property.project_id');
        $users = $this->Property->find('all',array('conditions' => array('Property.name LIKE' => '%'.$term.'%','Property.status'=>'Available',$cond,$condition)));
        // Format the result for select2
        $result = array();
        foreach($users as $key => $user)
        {
            $result[$key]['id'] = (int) $user['Property']['id'];
            $result[$key]['text'] = $user['Property']['name'];
        }
        $users = $result;        
        echo json_encode($users);
    }
    public function clientsearch()
    {
        $this->autoRender = false;
        $this->request->onlyAllow('ajax');
        // get the search term from URL
        $this->loadModel('Client');
        $term = $this->request->query['q'];
        $condition=$this->CustomFunction->agencyWisePermission(NULL,$this->userType,$this->agencyId,'Client.project_id');
        $users = $this->Client->find('all',array('fields'=>array('Client.id','Client.name'),'conditions' => array('Client.name LIKE' => '%'.$term.'%',$condition)));
        // Format the result for select2
        $result = array();
        foreach($users as $key => $user)
        {
            $result[$key]['id'] = (int) $user['Client']['id'];
            $result[$key]['text'] = $user['Client']['name'];
        }
        $users = $result;        
        echo json_encode($users);
    }
    public function add()
    {
        $this->loadModel('Property');
        $this->loadModel('Project');
        $this->loadModel('Type');
        $this->loadModel('Status');
        $this->loadModel('User');
        //$this->set('propertyId',$this->Property->find('list',array('fields'=>array('id','name'))));
	$this->set('projectName',$this->CustomFunction->agencyWiseList($this->userType,$this->agencyId));
        $this->set('type',$this->Type->find('list'));
        $this->set('status',$this->Status->find('list',array('order'=>array('Status.name'=>'asc'))));
	$this->set('agencyId',$this->agencyId);
	$this->set('luserId',$this->luserId);
	$projectId=null;
	if($this->userType=='AGY'){
	    $this->set('agentList',$this->User->find('list',array('conditions'=>array($this->User->assignableScopeConditions((int)$this->agencyId,'User')),'order'=>array('User.name'=>'asc'))));		    
	}
	if ($this->request->is('post'))
        {
            $this->Lead->create();
            if($this->userType=='AGT' || $this->userType=='AGY'){
		$this->request->data['Lead']['project_id']=$this->agencyId;		    
	    }
	    if($this->userType=='AGT')
	    $this->request->data['Lead']['user_id']=$this->luserId;
        if(!empty($this->request->data['Lead']['user_id']))
        {
            $checkUser = $this->User->find('first', array(
                'fields' => array('User.id', 'User.login_status'),
                'conditions' => array('User.id' => (int)$this->request->data['Lead']['user_id']),
                'recursive' => -1
            ));
            if(!empty($checkUser['User']['login_status']) && strcasecmp(trim((string)$checkUser['User']['login_status']), 'Blocked') === 0)
            {
                $this->Session->setFlash(__('Cannot assign lead: the selected agent login is blocked.'), 'flash', array('alert' => 'danger'));
                $projectId = isset($this->request->data['Lead']['project_id']) ? $this->request->data['Lead']['project_id'] : $this->agencyId;
                $this->set('agentName', $this->User->find('list', array('conditions' => array($this->User->assignableScopeConditions((int)$projectId, 'User')), 'order' => array('User.name' => 'asc'))));
                return;
            }
        }
	    try
            {
                if ($this->Lead->save($this->request->data))
                {
                    $this->_logLeadHistoryCreate((int)$this->Lead->id);
                    $this->Session->setFlash(__('Lead has been saved.'),'flash',array('alert'=>'success'));
                    return $this->redirect(array('action' => 'add'));
                }
            }
            catch (Exception $e)
            {
                $this->Session->setFlash(__('Please Select Property Name'),'flash',array('alert'=>'danger'));
            }
	    $projectId=$this->request->data['Lead']['project_id'];
        }
	$this->set('agentName',$this->User->find('list',array('conditions'=>array($this->User->assignableScopeConditions((int)$projectId,'User')),'order'=>array('User.name'=>'asc'))));
    }
    public function edit($id = null)
    {
        if (!$id)
        {
            throw new NotFoundException(__('Invalid post'));
        }
        $ids=explode(",",$id);
        $post=array();
	$this->loadModel('User');
        $this->set('agencyId',$this->agencyId);
	foreach($ids as $k=>$id)
        {
            $k++;
            $post[$k]=$this->Lead->findByid($id);
            $this->set("agentName$k", $this->User->find('list',array('fields'=>array('id','name'),'conditions'=>array($this->User->assignableScopeConditions((int)$post[$k]['Lead']['project_id'],'User')),'order'=>array('User.name'=>'asc'))));
        }
        $this->set('Lead',$post);
        if($this->userType=='AGY'){
	    $this->set('agentList',$this->User->find('list',array('conditions'=>array($this->User->assignableScopeConditions((int)$this->agencyId,'User')),'order'=>array('User.name'=>'asc'))));		    
	}
	if (!$post)
        {
            throw new NotFoundException(__('Invalid post'));
        }
        $this->loadModel('Type');
        $this->loadModel('Project');
        $this->loadModel('Status');
        $this->set('type',$this->Type->find('list'));
        $this->set('status',$this->Status->find('list'));
        $this->set('agency',$this->CustomFunction->agencyWiseList($this->userType,$this->agencyId));
        if ($this->request->is(array('post', 'put')))
        {
            $this->Lead->id = $id;
            try
            {
                foreach((array)$this->request->data as $leadCheckRow)
                {
                    if(!empty($leadCheckRow['Lead']['user_id']))
                    {
                        $checkUser = $this->User->find('first', array(
                            'fields' => array('User.id', 'User.login_status'),
                            'conditions' => array('User.id' => (int)$leadCheckRow['Lead']['user_id']),
                            'recursive' => -1
                        ));
                        if(!empty($checkUser['User']['login_status']) && strcasecmp(trim((string)$checkUser['User']['login_status']), 'Blocked') === 0)
                        {
                            $this->Session->setFlash(__('Cannot assign lead: the selected agent login is blocked.'), 'flash', array('alert' => 'danger'));
                            $this->set('isError', true);
                            return;
                        }
                    }
                }
                if ($this->Lead->saveAll($this->request->data))
                {
                    $updatedLeadIds = array();
                    $beforeLeadMap = array();
                    foreach((array)$post as $leadBeforeRow)
                    {
                        if(!empty($leadBeforeRow['Lead']['id']))
                        {
                            $beforeLeadMap[(int)$leadBeforeRow['Lead']['id']] = $leadBeforeRow;
                        }
                    }
                    foreach((array)$this->request->data as $leadSaveRow)
                    {
                        if(!empty($leadSaveRow['Lead']['id']))
                        {
                            $savedLeadId = (int)$leadSaveRow['Lead']['id'];
                            $updatedLeadIds[] = $savedLeadId;
                            $this->_logLeadHistoryChanges($savedLeadId, isset($beforeLeadMap[$savedLeadId]) ? $beforeLeadMap[$savedLeadId] : array(), (array)$leadSaveRow['Lead']);
                        }
                    }
                    $this->_syncLeadGoogleCalendarEvents($updatedLeadIds);
                    $this->Session->setFlash(__('Lead has been updated.'),'flash',array('alert'=>'success'));
                    return $this->redirect(array('action' => 'manage'));
                }
            }
            catch (Exception $e)
            {
                $this->Session->setFlash(__('Please Select Client Name & Property Name.'),'flash',array('alert'=>'danger'));
                return $this->redirect(array('action' => 'manage'));
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
                $selectedIds = array();
                if(!empty($this->request->data['Lead']['id']) && is_array($this->request->data['Lead']['id']))
                {
                    foreach($this->request->data['Lead']['id'] as $rawId)
                    {
                        $leadId = (int)$rawId;
                        if($leadId > 0)
                        {
                            $selectedIds[$leadId] = $leadId;
                        }
                    }
                }
                if(empty($selectedIds))
                {
                    $this->Session->setFlash(__('Please Select Any Record'),'flash',array('alert'=>'danger'));
                    return $this->redirect(array('action' => 'manage'));
                }

                $permissionCondition = $this->_leadPermissionCondition(null);
                $deletedCount = 0;
                $blockedCount = 0;
                foreach(array_values($selectedIds) as $leadId)
                {
                    $allowedLead = $this->Lead->find('first', array(
                        'fields' => array('Lead.id'),
                        'conditions' => array('Lead.id' => $leadId, $permissionCondition),
                        'recursive' => -1
                    ));
                    if(empty($allowedLead['Lead']['id']))
                    {
                        $blockedCount++;
                        continue;
                    }
                    if($this->Lead->delete($leadId))
                    {
                        $deletedCount++;
                    }
                }

                if($deletedCount > 0)
                {
                    $msg = __('Lead has been deleted.');
                    if($blockedCount > 0)
                    {
                        $msg .= ' '.__('Some selected records were skipped due to permissions.');
                    }
                    $this->Session->setFlash($msg,'flash',array('alert'=>'success'));
                }
                else
                {
                    $this->Session->setFlash(__('No record deleted. Please check permissions or selected records.'),'flash',array('alert'=>'danger'));
                }
            }
            return $this->redirect(array('action' => 'manage'));
        }
        catch (Exception $e)
        {
            $this->Session->setFlash(__('Unable to delete selected records.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'manage'));
        }
    }

    public function send_notification($id = null, $type = 'all')
    {
        App::uses('FacebookIntegrationsController', 'Admin.Controller');
        $fbCtrl = new FacebookIntegrationsController($this->request, $this->response);
        $fbCtrl->constructClasses();
        return $fbCtrl->send_notification($id, $type);
    }
    public function admin_send_notification($id = null, $type = 'all')
    {
        return $this->send_notification($id, $type);
    }

    public function bulk_notifications($type = 'all')
    {
        App::uses('FacebookIntegrationsController', 'Admin.Controller');
        $fbCtrl = new FacebookIntegrationsController($this->request, $this->response);
        $fbCtrl->constructClasses();
        return $fbCtrl->bulk_notifications($type);
    }
    public function admin_bulk_notifications($type = 'all')
    {
        return $this->bulk_notifications($type);
    }

    public function resend_notification($logId = null)
    {
        App::uses('FacebookIntegrationsController', 'Admin.Controller');
        $fbCtrl = new FacebookIntegrationsController($this->request, $this->response);
        $fbCtrl->constructClasses();
        return $fbCtrl->resend_notification($logId);
    }
    public function admin_resend_notification($logId = null)
    {
        return $this->resend_notification($logId);
    }

    public function view($id = null)
    {
        $this->layout = null;
        if (!$id)
        {
            $this->Session->setFlash(__('Invalid Post.'),'flash',array('alert'=>'danger'));
            $this->redirect(array('action' => 'manage'));
        }
        $post = $this->Lead->findById($id);
        $this->set('post',$post);
    }

    protected function _leadDashboardDbCount($conditions)
    {
        $row = $this->Lead->find('first', array(
            'fields' => array('COUNT(DISTINCT Lead.id) AS total'),
            'conditions' => (array)$conditions,
            'recursive' => -1
        ));
        if(isset($row[0]['total']))
        {
            return (int)$row[0]['total'];
        }
        if(isset($row['Lead']['total']))
        {
            return (int)$row['Lead']['total'];
        }
        return 0;
    }
    public function dashboard($id = null)
    {
        $this->loadModel('Status');
        $this->loadModel('User');
        $namedParams = isset($this->request->params['named']) ? (array)$this->request->params['named'] : array();
        $queryParams = isset($this->request->query) ? (array)$this->request->query : array();
        $rawParams = array_merge($namedParams, $queryParams);
        if(!empty($this->request->data['Lead']) && is_array($this->request->data['Lead']))
        {
            $rawParams = array_merge($rawParams, (array)$this->request->data['Lead']);
        }
        $conditionBundle = $this->_leadManageConditions($id, array(), $rawParams);
        $dashboardFilters = isset($conditionBundle['filters']) ? (array)$conditionBundle['filters'] : array();
        $selectedScope = isset($this->request->query['scope']) ? strtolower(trim((string)$this->request->query['scope'])) : 'all';
        if($selectedScope === '')
        {
            $selectedScope = 'all';
        }
        $selectedFollowupPeriod = isset($this->request->query['followup_period']) ? strtolower(trim((string)$this->request->query['followup_period'])) : 'today';
        if(!in_array($selectedFollowupPeriod, array('today','week','month','overdue','all','custom'), true))
        {
            $selectedFollowupPeriod = 'today';
        }
        $followupStartInput = isset($this->request->query['followup_start']) ? trim((string)$this->request->query['followup_start']) : '';
        $followupEndInput = isset($this->request->query['followup_end']) ? trim((string)$this->request->query['followup_end']) : '';
        $followupStartDate = '';
        $followupEndDate = '';
        $followupPeriodLabel = __('Today');
        if($selectedFollowupPeriod === 'today')
        {
            $followupStartDate = date('Y-m-d');
            $followupEndDate = date('Y-m-d');
        }
        elseif($selectedFollowupPeriod === 'week')
        {
            $followupStartDate = date('Y-m-d', strtotime('monday this week'));
            $followupEndDate = date('Y-m-d', strtotime('sunday this week'));
            $followupPeriodLabel = __('This Week');
        }
        elseif($selectedFollowupPeriod === 'month')
        {
            $followupStartDate = date('Y-m-01');
            $followupEndDate = date('Y-m-t');
            $followupPeriodLabel = __('This Month');
        }
        elseif($selectedFollowupPeriod === 'overdue')
        {
            $followupPeriodLabel = __('Overdue Follow Ups');
        }
        elseif($selectedFollowupPeriod === 'custom')
        {
            $followupStartDate = $this->_leadNormalizeDateInput($followupStartInput);
            $followupEndDate = $this->_leadNormalizeDateInput($followupEndInput);
            $followupPeriodLabel = __('Selected Date');
        }
        else
        {
            $followupPeriodLabel = __('All');
        }

        $selectedProjectId = isset($dashboardFilters['project_id']) ? (int)$dashboardFilters['project_id'] : 0;
        if($selectedProjectId < 0)
        {
            $selectedProjectId = 0;
        }

        $canUseAgentFilters = $this->_leadCanUseAgentFilters();
        $selectedAgentId = isset($dashboardFilters['user_id']) ? (int)$dashboardFilters['user_id'] : 0;
        if($selectedAgentId < 0)
        {
            $selectedAgentId = 0;
        }
        if($canUseAgentFilters && $selectedAgentId > 0)
        {
            $allowedDashboardUsers = $this->_leadAgentFilterOptions($selectedProjectId);
            if(!isset($allowedDashboardUsers[$selectedAgentId]))
            {
                $selectedAgentId = 0;
            }
        }
        $selectedAgentName = __('All Agents');
        if($selectedAgentId > 0)
        {
            $selectedAgentName = (string)$this->User->field('name', array('User.id' => $selectedAgentId));
            if(trim((string)$selectedAgentName) === '')
            {
                $selectedAgentName = __('All Agents');
            }
        }

        $dashboardBaseConditions = isset($conditionBundle['conditions']) ? (array)$conditionBundle['conditions'] : array();

        $statusRows = (array)$this->Status->find('all', array(
            'fields' => array('Status.id','Status.name','Status.color'),
            'order' => array('Status.name' => 'asc'),
            'recursive' => -1
        ));
        $statusRows = $this->_leadSortStatusRows($statusRows);
        $statusCounts = $this->Lead->find('all', array(
            'fields' => array('Lead.status_id','COUNT(DISTINCT Lead.id) AS total'),
            'conditions' => $dashboardBaseConditions,
            'group' => array('Lead.status_id'),
            'recursive' => -1
        ));
        $statusMap = array();
        foreach($statusCounts as $row)
        {
            $statusMap[(int)$row['Lead']['status_id']] = (int)$row[0]['total'];
        }

        $totalLeads = $this->_leadDashboardDbCount($dashboardBaseConditions);
        $todayStart = date('Y-m-d 00:00:00');
        $todayEnd = date('Y-m-d 23:59:59');
        $followUpTodayCount = $this->_leadDashboardDbCount(array_merge($dashboardBaseConditions, array(
            'Lead.follow_up IS NOT NULL',
            'Lead.follow_up !=' => '0000-00-00 00:00:00',
            'Lead.follow_up >=' => $todayStart,
            'Lead.follow_up <=' => $todayEnd
        )));
        $followUpOverdueCount = $this->_leadDashboardDbCount(array_merge($dashboardBaseConditions, array(
            'Lead.follow_up IS NOT NULL',
            'Lead.follow_up !=' => '0000-00-00 00:00:00',
            'Lead.follow_up <' => $todayStart
        )));
        $upcomingCount = $this->_leadDashboardDbCount(array_merge($dashboardBaseConditions, array(
            'Lead.follow_up IS NOT NULL',
            'Lead.follow_up !=' => '0000-00-00 00:00:00',
            'Lead.follow_up >=' => $this->currentDateTime
        )));
        $overseasCond = $this->_leadPhoneRegionCondition('overseas');
        $localCond = $this->_leadPhoneRegionCondition('local');
        $overseasCount = $overseasCond ? $this->_leadDashboardDbCount(array_merge($dashboardBaseConditions, array($overseasCond))) : 0;
        $localCount = $localCond ? $this->_leadDashboardDbCount(array_merge($dashboardBaseConditions, array($localCond))) : 0;

        $recentConditions = $dashboardBaseConditions;
        $upcomingConditions = array_merge($dashboardBaseConditions, array('Lead.follow_up >=' => $this->currentDateTime));
        $scopeLabel = __('All Leads');
        if($selectedScope === 'today')
        {
            $recentConditions[] = array('DATE(Lead.follow_up)' => date('Y-m-d'));
            $upcomingConditions = array_merge($dashboardBaseConditions, array('DATE(Lead.follow_up)' => date('Y-m-d')));
            $scopeLabel = __('Follow Ups Today');
        }
        elseif($selectedScope === 'overdue')
        {
            $recentConditions[] = array(
                'Lead.follow_up IS NOT NULL',
                'Lead.follow_up !=' => '0000-00-00 00:00:00',
                'Lead.follow_up <' => $todayStart
            );
            $upcomingConditions = array_merge($dashboardBaseConditions, array(
                'Lead.follow_up IS NOT NULL',
                'Lead.follow_up !=' => '0000-00-00 00:00:00',
                'Lead.follow_up <' => $todayStart
            ));
            $scopeLabel = __('Overdue Follow Ups');
        }
        elseif($selectedScope === 'upcoming')
        {
            $recentConditions[] = array('Lead.follow_up >=' => $this->currentDateTime);
            $upcomingConditions = array_merge($dashboardBaseConditions, array('Lead.follow_up >=' => $this->currentDateTime));
            $scopeLabel = __('Upcoming Follow Ups');
        }
        elseif($selectedScope === 'overseas')
        {
            if($overseasCond)
            {
                $recentConditions[] = $overseasCond;
                $upcomingConditions[] = $overseasCond;
            }
            $scopeLabel = __('🌍 Overseas Leads');
        }
        elseif($selectedScope === 'local')
        {
            if($localCond)
            {
                $recentConditions[] = $localCond;
                $upcomingConditions[] = $localCond;
            }
            $scopeLabel = __('🇵🇰 Local Leads');
        }
        elseif(preg_match('/^status_(\d+)$/', $selectedScope, $scopeMatches))
        {
            $selectedStatusId = (int)$scopeMatches[1];
            if($selectedStatusId > 0)
            {
                $recentConditions[] = array('Lead.status_id' => $selectedStatusId);
                $upcomingConditions[] = array('Lead.status_id' => $selectedStatusId);
                foreach($statusRows as $scopeStatusRow)
                {
                    if((int)$scopeStatusRow['Status']['id'] === $selectedStatusId)
                    {
                        $scopeLabel = (string)$scopeStatusRow['Status']['name'];
                        break;
                    }
                }
            }
        }
        elseif($selectedScope !== 'all')
        {
            $selectedScope = 'all';
        }

        $dashboardBaseQuery = array();
        if($selectedProjectId > 0)
        {
            $dashboardBaseQuery['project_id'] = $selectedProjectId;
        }
        if($selectedAgentId > 0)
        {
            $dashboardBaseQuery['user_id'] = $selectedAgentId;
        }

        $followupTableConditions = array_merge($dashboardBaseConditions, array('Lead.follow_up IS NOT NULL', 'Lead.follow_up !=' => '0000-00-00 00:00:00'));
        if($selectedFollowupPeriod === 'overdue')
        {
            $followupTableConditions[] = array('Lead.follow_up <' => $todayStart);
        }
        elseif($followupStartDate !== '' && $followupEndDate !== '')
        {
            $followupTableConditions[] = array('Lead.follow_up >=' => $followupStartDate.' 00:00:00', 'Lead.follow_up <=' => $followupEndDate.' 23:59:59');
        }
        elseif($followupStartDate !== '')
        {
            $followupTableConditions[] = array('Lead.follow_up >=' => $followupStartDate.' 00:00:00');
        }
        elseif($followupEndDate !== '')
        {
            $followupTableConditions[] = array('Lead.follow_up <=' => $followupEndDate.' 23:59:59');
        }
        $upcomingPageLimit = 15;
        $upcomingPage = isset($this->request->query['followup_page']) ? (int)$this->request->query['followup_page'] : 1;
        if($upcomingPage < 1)
        {
            $upcomingPage = 1;
        }
        $followupTableCount = $this->_leadDashboardDbCount($followupTableConditions);
        $followupTotalPages = max(1, (int)ceil($followupTableCount / $upcomingPageLimit));
        if($upcomingPage > $followupTotalPages)
        {
            $upcomingPage = $followupTotalPages;
        }
        $this->Lead->unbindModel(array('belongsTo' => array('Property','Contract','Type')), false);
        $upcomingLeads = $this->Lead->find('all', array(
            'fields' => array(
                'Lead.id','Lead.client_id','Lead.status_id','Lead.follow_up','Lead.lead_name','Lead.lead_mobile','Lead.lead_email',
                'Lead.lead_temperature','Lead.remarks','Lead.last_contact_date','Lead.lead_source',
                'Client.id','Client.name','Client.mobile','Client.phone','Client.email',
                'Status.id','Status.name','Status.color'
            ),
            'conditions' => $followupTableConditions,
            'order' => array('Lead.follow_up' => 'asc', 'Lead.id' => 'desc'),
            'limit' => $upcomingPageLimit,
            'offset' => (($upcomingPage - 1) * $upcomingPageLimit),
            'recursive' => 0
        ));
        $recentLeads = $this->Lead->find('all', array(
            'fields' => array(
                'Lead.id','Lead.client_id','Lead.status_id','Lead.follow_up','Lead.lead_name','Lead.lead_mobile','Lead.lead_email',
                'Lead.lead_temperature','Lead.remarks','Lead.last_contact_date','Lead.lead_source',
                'Client.id','Client.name','Client.mobile','Client.phone','Client.email',
                'Status.id','Status.name','Status.color'
            ),
            'conditions' => $recentConditions,
            'order' => array('Lead.id' => 'desc'),
            'limit' => 10,
            'recursive' => 0
        ));

        $wonStatusIds = (array)$this->Status->find('list', array(
            'fields' => array('Status.id'),
            'conditions' => array('LOWER(Status.name) REGEXP' => 'booked|sold|won|converted|completed|deal closed'),
            'recursive' => -1
        ));
        $lostStatusIds = (array)$this->Status->find('list', array(
            'fields' => array('Status.id'),
            'conditions' => array('LOWER(Status.name) REGEXP' => 'cancel|lost|dead|not interested'),
            'recursive' => -1
        ));

        $wonCount = !empty($wonStatusIds) ? $this->_leadDashboardDbCount(array_merge($dashboardBaseConditions, array('Lead.status_id' => $wonStatusIds))) : 0;
        $lostCount = !empty($lostStatusIds) ? $this->_leadDashboardDbCount(array_merge($dashboardBaseConditions, array('Lead.status_id' => $lostStatusIds))) : 0;
        $inProgressCount = max(0, $totalLeads - $wonCount - $lostCount);
        $winRate = $totalLeads > 0 ? round(($wonCount / $totalLeads) * 100, 1) : 0;
        $lostRate = $totalLeads > 0 ? round(($lostCount / $totalLeads) * 100, 1) : 0;

        $lostReasonRows = (array)$this->Lead->find('all', array(
            'fields' => array('Lead.lost_reason', 'COUNT(Lead.id) AS total'),
            'conditions' => array_merge($dashboardBaseConditions, array(
                'Lead.lost_reason IS NOT NULL',
                'TRIM(Lead.lost_reason) !=' => ''
            )),
            'group' => array('Lead.lost_reason'),
            'order' => array('total' => 'desc'),
            'recursive' => -1
        ));
        $lostReasonsMap = array();
        foreach($lostReasonRows as $lr)
        {
            $rName = trim((string)$lr['Lead']['lost_reason']);
            if($rName !== '')
            {
                $lostReasonsMap[$rName] = (int)$lr[0]['total'];
            }
        }

        $this->set('statusRows',$statusRows);
        $this->set('statusMap',$statusMap);
        $this->set('totalLeads',$totalLeads);
        $this->set('followUpTodayCount',$followUpTodayCount);
        $this->set('followUpOverdueCount',$followUpOverdueCount);
        $this->set('upcomingCount',$upcomingCount);
        $this->set('overseasCount',$overseasCount);
        $this->set('localCount',$localCount);
        $this->set('wonCount',$wonCount);
        $this->set('lostCount',$lostCount);
        $this->set('inProgressCount',$inProgressCount);
        $this->set('winRate',$winRate);
        $this->set('lostRate',$lostRate);
        $this->set('lostReasonsMap',$lostReasonsMap);
        $this->set('selectedScope',$selectedScope);
        $this->set('selectedPhoneRegion',isset($dashboardFilters['phone_region']) ? $dashboardFilters['phone_region'] : 'all');
        $this->set('scopeLabel',$scopeLabel);
        $this->set('selectedAgentName',$selectedAgentName);
        $this->set('reportAgency',$this->_leadResolveReportAgency($selectedProjectId));
        $this->set('dashboardBaseQuery',$dashboardBaseQuery);
        $this->set('upcomingLeads',$upcomingLeads);
        $this->set('selectedFollowupPeriod',$selectedFollowupPeriod);
        $this->set('followupPeriodLabel',$followupPeriodLabel);
        $this->set('followupStartInput',$followupStartInput);
        $this->set('followupEndInput',$followupEndInput);
        $this->set('followupTableCount',$followupTableCount);
        $this->set('followupCurrentPage',$upcomingPage);
        $this->set('followupTotalPages',$followupTotalPages);
        $this->set('recentLeads',$recentLeads);
    }

    public function quicklog()
    {
        $this->autoRender = false;
        $this->response->type('json');
        if(!$this->request->is('post'))
        {
            return $this->response->body(json_encode(array('status' => 'error', 'message' => __('Invalid request method.'))));
        }
        try
        {
            $leadId = isset($this->request->data['id']) ? (int)$this->request->data['id'] : 0;
            if($leadId <= 0)
            {
                return $this->response->body(json_encode(array('status' => 'error', 'message' => __('Lead ID missing.'))));
            }
            $lead = $this->Lead->findById($leadId);
            if(empty($lead['Lead']['id']))
            {
                return $this->response->body(json_encode(array('status' => 'error', 'message' => __('Lead not found.'))));
            }

            $updateData = array('id' => $leadId);
            if(!empty($this->request->data['status_id']))
            {
                $updateData['status_id'] = (int)$this->request->data['status_id'];
            }
            if(!empty($this->request->data['lead_temperature']))
            {
                $updateData['lead_temperature'] = trim((string)$this->request->data['lead_temperature']);
            }
            if(isset($this->request->data['follow_up']))
            {
                $rawFollowup = trim((string)$this->request->data['follow_up']);
                $updateData['follow_up'] = ($rawFollowup !== '') ? $this->dateTimeFormatBeforeSave($rawFollowup) : null;
            }
            if(!empty($this->request->data['remarks']))
            {
                $newRemark = trim((string)$this->request->data['remarks']);
                $existingRemarks = trim((string)$lead['Lead']['remarks']);
                $userSession = (array)$this->Session->read('User');
                $userName = !empty($userSession['User']['name']) ? $userSession['User']['name'] : (!empty($userSession['name']) ? $userSession['name'] : 'Agent');
                $logEntry = '[' . date('Y-m-d H:i') . ' by ' . $userName . ']: ' . $newRemark;
                $updateData['remarks'] = ($existingRemarks !== '') ? $existingRemarks . "\n" . $logEntry : $logEntry;
            }
            $updateData['last_contact_date'] = date('Y-m-d');

            $this->Lead->id = $leadId;
            if($this->Lead->save($updateData, false))
            {
                try
                {
                    $LeadHistory = $this->_leadHistoryModel();
                    if($LeadHistory)
                    {
                        $actor = $this->_leadHistoryActorContext();
                        $noteText = !empty($this->request->data['remarks']) ? trim((string)$this->request->data['remarks']) : '';
                        $newFollowup = !empty($updateData['follow_up']) ? date('d-M-Y h:i A', strtotime($updateData['follow_up'])) : '';
                        
                        $msgLines = array();
                        if($noteText !== '')
                        {
                            $msgLines[] = "Remark / Note: " . $noteText;
                        }
                        if($newFollowup !== '')
                        {
                            $msgLines[] = "Next Follow-up: " . $newFollowup;
                        }
                        if(!empty($updateData['status_id']) && (int)$updateData['status_id'] !== (int)$lead['Lead']['status_id'])
                        {
                            $this->loadModel('Status');
                            $newStatusName = $this->Status->field('name', array('Status.id' => $updateData['status_id']));
                            $msgLines[] = "Status: " . $newStatusName;
                        }

                        $LeadHistory->createEntry(array_merge($actor, array(
                            'lead_id' => $leadId,
                            'project_id' => !empty($lead['Lead']['project_id']) ? (int)$lead['Lead']['project_id'] : 0,
                            'lead_user_id' => !empty($lead['Lead']['user_id']) ? (int)$lead['Lead']['user_id'] : 0,
                            'entry_type' => 'comment',
                            'title' => 'Conversation & Follow-up Logged',
                            'field_name' => 'remarks',
                            'field_label' => 'Conversation Note',
                            'new_value' => $noteText,
                            'message_body' => implode("\n", $msgLines),
                            'meta_json' => array(
                                'source_action' => 'quicklog',
                                'follow_up' => isset($updateData['follow_up']) ? $updateData['follow_up'] : null,
                                'status_id' => isset($updateData['status_id']) ? $updateData['status_id'] : null
                            )
                        )));
                    }
                }
                catch(Exception $e) {}

                return $this->response->body(json_encode(array('status' => 'success', 'message' => __('Lead updated successfully.'))));
            }
            return $this->response->body(json_encode(array('status' => 'error', 'message' => __('Failed to save lead update.'))));
        }
        catch(Exception $ex)
        {
            return $this->response->body(json_encode(array('status' => 'error', 'message' => $ex->getMessage())));
        }
    }

    public function bulkreschedule()
    {
        $this->autoRender = false;
        $this->response->type('json');
        if(!$this->request->is('post'))
        {
            return $this->response->body(json_encode(array('status' => 'error', 'message' => __('Invalid request method.'))));
        }
        $targetDate = isset($this->request->data['target_date']) ? trim((string)$this->request->data['target_date']) : date('Y-m-d 11:00:00');
        $projectId = isset($this->request->data['project_id']) ? (int)$this->request->data['project_id'] : 0;
        $userId = isset($this->request->data['user_id']) ? (int)$this->request->data['user_id'] : 0;

        $conditions = array(
            'Lead.follow_up IS NOT NULL',
            'Lead.follow_up !=' => '0000-00-00 00:00:00',
            'Lead.follow_up <' => date('Y-m-d 00:00:00')
        );
        if($projectId > 0)
        {
            $conditions['Lead.project_id'] = $projectId;
        }
        if($userId > 0)
        {
            $conditions['Lead.user_id'] = $userId;
        }

        $formattedTarget = $this->dateTimeFormatBeforeSave($targetDate);
        $affected = $this->Lead->updateAll(
            array('Lead.follow_up' => "'".$formattedTarget."'", 'Lead.modified' => "'".date('Y-m-d H:i:s')."'"),
            $conditions
        );

        if($affected)
        {
            return $this->response->body(json_encode(array('status' => 'success', 'message' => __('Overdue follow-ups rescheduled successfully.'))));
        }
        return $this->response->body(json_encode(array('status' => 'error', 'message' => __('No overdue follow-ups found to reschedule.'))));
    }

    public function kanban($id = null)
    {
        $namedParams = isset($this->request->params['named']) ? (array)$this->request->params['named'] : array();
        $queryParams = isset($this->request->query) ? (array)$this->request->query : array();
        $rawParams = array_merge($namedParams, $queryParams);
        $condition = $this->_leadPermissionCondition($id);
        $filters = $this->_extractLeadKanbanFilters($rawParams);
        $kanbanConditions = array($condition);
        foreach((array)$filters['conditions'] as $filterCondition)
        {
            $kanbanConditions[] = $filterCondition;
        }

        $this->loadModel('Status');
        $statusRows = (array)$this->Status->find('all', array(
            'fields' => array('Status.id','Status.name','Status.color'),
            'order' => array('Status.name' => 'asc'),
            'recursive' => -1
        ));
        $statusRows = $this->_leadSortStatusRows($statusRows);
        $this->Lead->unbindModel(array('belongsTo' => array('Property','Contract','Type')), false);
        $leadRows = $this->Lead->find('all', array(
            'fields' => array(
                'Lead.id',
                'Lead.client_id',
                'Lead.status_id',
                'Lead.kanban_position',
                'Lead.lead_temperature',
                'Lead.follow_up',
                'Lead.remarks',
                'Lead.lead_source',
                'Lead.lead_name',
                'Lead.lead_mobile',
                'Lead.lead_email',
                'Client.id',
                'Client.name',
                'Client.mobile',
                'Client.email'
            ),
            'joins' => array(
                array(
                    'table' => 'clients',
                    'alias' => 'Client',
                    'type' => 'LEFT',
                    'conditions' => array('Client.id = Lead.client_id')
                )
            ),
            'conditions' => $kanbanConditions,
            'order' => array('Lead.status_id' => 'asc', 'Lead.follow_up' => 'asc', 'Lead.kanban_position' => 'asc', 'Lead.id' => 'desc'),
            'recursive' => -1
        ));

        $canUseAgencyFilters = $this->_leadCanUseAgencyFilters();
        $canUseAgentFilters = $this->_leadCanUseAgentFilters();
        $canUseUserLevelFilters = $this->_leadCanUseUserLevelFilters();
        $projectOptions = array();
        if($canUseAgencyFilters)
        {
            $this->loadModel('Project');
            $projectOptions = (array)$this->Project->find('list', array(
                'fields' => array('Project.id','Project.name'),
                'order' => array('Project.name' => 'asc')
            ));
        }
        $userLevelOptions = $this->_leadImportUserLevelOptions();
        $agentFilterOptions = isset($filters['user_options']) ? (array)$filters['user_options'] : $this->_leadAgentFilterOptions((int)$filters['project_id'], (int)$filters['ugroup_id']);

        $selectedAgencyName = __('All Agency');
        if(!$canUseAgencyFilters)
        {
            $selectedAgencyName = $this->siteOrganization;
        }
        elseif((int)$filters['project_id'] > 0 && isset($projectOptions[(int)$filters['project_id']]))
        {
            $selectedAgencyName = (string)$projectOptions[(int)$filters['project_id']];
        }

        $selectedUserLevelName = __('All User Levels');
        if((int)$filters['ugroup_id'] > 0 && isset($userLevelOptions[(int)$filters['ugroup_id']]))
        {
            $selectedUserLevelName = (string)$userLevelOptions[(int)$filters['ugroup_id']];
        }

        $selectedUserName = __('All Users');
        if((int)$filters['user_id'] > 0)
        {
            if(isset($agentFilterOptions[(int)$filters['user_id']]))
            {
                $selectedUserName = (string)$agentFilterOptions[(int)$filters['user_id']];
            }
            else
            {
                $this->loadModel('User');
                $selectedUserName = (string)$this->User->field('name', array('User.id' => (int)$filters['user_id']));
            }
            if(trim((string)$selectedUserName) === '')
            {
                $selectedUserName = __('All Users');
            }
        }

        $selectedDateRangeLabel = __('All Dates');
        if(trim((string)$filters['start_date']) !== '' || trim((string)$filters['end_date']) !== '')
        {
            $startLabel = trim((string)$filters['start_date']) !== '' ? date('d-M-Y', strtotime($filters['start_date'])) : __('N/A');
            $endLabel = trim((string)$filters['end_date']) !== '' ? date('d-M-Y', strtotime($filters['end_date'])) : __('N/A');
            $selectedDateRangeLabel = $startLabel.' '.__('to').' '.$endLabel;
        }

        $selectedFilterSummaryParts = array();
        $selectedFilterSummaryParts[] = __('Period').': '.$filters['period_label'];
        $selectedFilterSummaryParts[] = __('Date Range').': '.$selectedDateRangeLabel;
        if($canUseAgencyFilters)
        {
            $selectedFilterSummaryParts[] = __('Agency').': '.$selectedAgencyName;
        }
        if($canUseUserLevelFilters)
        {
            $selectedFilterSummaryParts[] = __('User Level').': '.$selectedUserLevelName;
        }
        if($canUseAgentFilters)
        {
            $selectedFilterSummaryParts[] = __('User').': '.$selectedUserName;
        }

        $kanbanRows = array();
        foreach($statusRows as $statusRow)
        {
            $kanbanRows[(int)$statusRow['Status']['id']] = array();
        }
        foreach($leadRows as $leadRow)
        {
            $sid = (int)$leadRow['Lead']['status_id'];
            if(!isset($kanbanRows[$sid]))
            {
                $kanbanRows[$sid] = array();
            }
            $leadRow['Lead']['lead_temperature'] = $this->_normalizeLeadTemperature(isset($leadRow['Lead']['lead_temperature']) ? $leadRow['Lead']['lead_temperature'] : 'warm');
            $leadRow['Lead']['kanban_position'] = isset($leadRow['Lead']['kanban_position']) ? (int)$leadRow['Lead']['kanban_position'] : 0;
            $kanbanRows[$sid][] = $leadRow;
        }
        // Keep DB order as-is (status_id, follow_up, kanban_position, id).
        // Avoid expensive PHP-side resort for large boards.
        $this->set('statusRows',$statusRows);
        $this->set('kanbanRows',$kanbanRows);
        $this->set('leadTotal',count($leadRows));
        $this->set('projectOptions', $projectOptions);
        $this->set('userLevelOptions', $userLevelOptions);
        $this->set('agentFilterOptions', $agentFilterOptions);
        $this->set('canUseAgencyFilters', $canUseAgencyFilters);
        $this->set('canUseAgentFilters', $canUseAgentFilters);
        $this->set('canUseUserLevelFilters', $canUseUserLevelFilters);
        $this->set('kanbanPeriodOptions', $this->_leadKanbanPeriodOptions());
        $this->set('selectedProjectFilter', (int)$filters['project_id']);
        $this->set('selectedUserLevelFilter', (int)$filters['ugroup_id']);
        $this->set('selectedUserFilter', (int)$filters['user_id']);
        $this->set('selectedPhoneRegion', (string)$filters['phone_region']);
        $this->set('selectedPeriodFilter', (string)$filters['period']);
        $this->set('selectedPeriodLabel', (string)$filters['period_label']);
        $this->set('selectedStartDateInput', (string)$filters['start_date_input']);
        $this->set('selectedEndDateInput', (string)$filters['end_date_input']);
        $this->set('selectedAgencyName', $selectedAgencyName);
        $this->set('selectedUserLevelName', $selectedUserLevelName);
        $this->set('selectedUserName', $selectedUserName);
        $this->set('selectedDateRangeLabel', $selectedDateRangeLabel);
        $this->set('selectedFilterSummary', implode(' | ', $selectedFilterSummaryParts));
    }

    public function updatestatus()
    {
        $this->autoRender = false;
        if(!$this->request->is(array('post','ajax')))
        {
            echo json_encode(array('success' => false, 'message' => 'Invalid request'));
            return;
        }
        $requestPayload = array();
        if(!empty($this->request->data) && is_array($this->request->data))
        {
            $requestPayload = (array)$this->request->data;
        }
        if(isset($requestPayload['Lead']) && is_array($requestPayload['Lead']))
        {
            $requestPayload = array_merge($requestPayload, (array)$requestPayload['Lead']);
        }
        $condition = $this->_leadPermissionCondition(null);
        $cardsPayload = isset($requestPayload['cards']) ? $requestPayload['cards'] : $this->request->data('cards');
        if(!empty($cardsPayload))
        {
            if(is_string($cardsPayload))
            {
                $decodedCards = json_decode($cardsPayload, true);
            }
            else
            {
                $decodedCards = $cardsPayload;
            }
            $decodedCards = is_array($decodedCards) ? $decodedCards : array();

            $cardMap = array();
            $leadIds = array();
            foreach($decodedCards as $index => $cardItem)
            {
                if(!is_array($cardItem))
                {
                    continue;
                }
                $cardLeadId = isset($cardItem['id']) ? (int)$cardItem['id'] : 0;
                $cardStatusId = isset($cardItem['status_id']) ? (int)$cardItem['status_id'] : 0;
                if($cardLeadId <= 0 || $cardStatusId <= 0)
                {
                    continue;
                }
                $leadIds[$cardLeadId] = $cardLeadId;
                $cardMap[$cardLeadId] = array(
                    'status_id' => $cardStatusId,
                    'position' => (isset($cardItem['position']) ? (int)$cardItem['position'] : ((int)$index + 1)),
                    'badge' => $this->_normalizeLeadTemperature(isset($cardItem['badge']) ? $cardItem['badge'] : 'warm')
                );
            }
            if(empty($cardMap))
            {
                echo json_encode(array('success' => false, 'message' => 'Invalid payload'));
                return;
            }
            $allowedRows = $this->Lead->find('all', array(
                'fields' => array('Lead.id','Lead.project_id','Lead.user_id','Lead.status_id','Lead.lead_temperature','Lead.kanban_position'),
                'conditions' => array($condition, 'Lead.id' => array_values($leadIds)),
                'recursive' => -1
            ));
            $allowedLeadMap = array();
            foreach((array)$allowedRows as $allowedRow)
            {
                $allowedLeadId = !empty($allowedRow['Lead']['id']) ? (int)$allowedRow['Lead']['id'] : 0;
                if($allowedLeadId > 0)
                {
                    $allowedLeadMap[$allowedLeadId] = $allowedRow;
                }
            }
            $updatedCount = 0;
            $changedCount = 0;
            foreach($allowedLeadMap as $allowedId => $beforeLeadRow)
            {
                $allowedId = (int)$allowedId;
                if(!isset($cardMap[$allowedId]))
                {
                    continue;
                }
                $updatePayload = array(
                    'id' => $allowedId,
                    'status_id' => (int)$cardMap[$allowedId]['status_id'],
                    'kanban_position' => ((int)$cardMap[$allowedId]['position'] > 0 ? (int)$cardMap[$allowedId]['position'] : ($updatedCount + 1)),
                    'lead_temperature' => $cardMap[$allowedId]['badge']
                );
                $beforeStatusId = !empty($beforeLeadRow['Lead']['status_id']) ? (int)$beforeLeadRow['Lead']['status_id'] : 0;
                $beforePosition = isset($beforeLeadRow['Lead']['kanban_position']) ? (int)$beforeLeadRow['Lead']['kanban_position'] : 0;
                $beforeBadge = $this->_normalizeLeadTemperature(isset($beforeLeadRow['Lead']['lead_temperature']) ? $beforeLeadRow['Lead']['lead_temperature'] : 'warm');
                $afterStatusId = (int)$updatePayload['status_id'];
                $afterPosition = (int)$updatePayload['kanban_position'];
                $afterBadge = (string)$updatePayload['lead_temperature'];
                if(
                    $beforeStatusId === $afterStatusId &&
                    $beforePosition === $afterPosition &&
                    $beforeBadge === $afterBadge
                )
                {
                    continue;
                }
                $changedCount++;
                if($this->Lead->save(array('Lead' => $updatePayload), array('validate' => false, 'callbacks' => false)))
                {
                    // Position-only reorder can touch many rows; skip history spam for faster autosave.
                    if($beforeStatusId !== $afterStatusId || $beforeBadge !== $afterBadge)
                    {
                        $this->_logLeadHistoryChanges($allowedId, $beforeLeadRow, $updatePayload);
                    }
                    $updatedCount++;
                }
            }
            if($changedCount === 0)
            {
                echo json_encode(array('success' => true, 'message' => 'Kanban already up to date', 'updated' => 0));
                return;
            }
            if($updatedCount > 0)
            {
                echo json_encode(array('success' => true, 'message' => 'Kanban updated', 'updated' => $updatedCount));
                return;
            }
            echo json_encode(array('success' => false, 'message' => 'Unable to update kanban'));
            return;
        }

        $leadId = isset($requestPayload['id']) ? (int)$requestPayload['id'] : (int)$this->request->data('id');
        if($leadId <= 0)
        {
            echo json_encode(array('success' => false, 'message' => 'Invalid payload'));
            return;
        }
        $leadRow = $this->Lead->find('first', array(
            'fields' => array('Lead.id','Lead.status_id','Lead.lead_temperature','Lead.follow_up','Lead.last_contact_date','Lead.remarks'),
            'conditions' => array('Lead.id' => $leadId, $condition),
            'recursive' => -1
        ));
        if(empty($leadRow['Lead']['id']))
        {
            echo json_encode(array('success' => false, 'message' => 'Lead not found'));
            return;
        }

        $hasStatusInput = array_key_exists('status_id', $requestPayload) && trim((string)$requestPayload['status_id']) !== '';
        $hasBadgeInput = array_key_exists('badge', $requestPayload) && trim((string)$requestPayload['badge']) !== '';
        $hasFollowUpInput = array_key_exists('follow_up', $requestPayload);
        $hasLastContactInput = array_key_exists('last_contact_date', $requestPayload);
        $hasRemarksInput = array_key_exists('remarks', $requestPayload);

        $statusId = !empty($leadRow['Lead']['status_id']) ? (int)$leadRow['Lead']['status_id'] : 0;
        if($hasStatusInput)
        {
            $statusId = (int)$requestPayload['status_id'];
            if($statusId <= 0)
            {
                echo json_encode(array('success' => false, 'message' => 'Invalid status'));
                return;
            }
        }

        $badge = $this->_normalizeLeadTemperature(isset($leadRow['Lead']['lead_temperature']) ? $leadRow['Lead']['lead_temperature'] : 'warm');
        if($hasBadgeInput)
        {
            $badge = $this->_normalizeLeadTemperature($requestPayload['badge']);
        }

        $updateLeadData = array('id' => $leadId);
        $hasUpdates = false;

        if($hasStatusInput || $hasBadgeInput)
        {
            $updateLeadData['status_id'] = $statusId;
            $updateLeadData['lead_temperature'] = $badge;
            $hasUpdates = true;
        }
        if(isset($requestPayload['position']))
        {
            $updateLeadData['kanban_position'] = (int)$requestPayload['position'];
            $hasUpdates = true;
        }

        if($hasFollowUpInput)
        {
            $followUpInput = trim((string)$requestPayload['follow_up']);
            if($followUpInput === '')
            {
                $updateLeadData['follow_up'] = null;
            }
            else
            {
                $followUpValue = $this->_safeFollowUpDateTime($followUpInput);
                if($followUpValue === '' || $followUpValue === null)
                {
                    echo json_encode(array('success' => false, 'message' => 'Invalid follow up date/time'));
                    return;
                }
                $updateLeadData['follow_up'] = $followUpValue;
            }
            $hasUpdates = true;
        }

        if($hasLastContactInput)
        {
            $lastContactInput = trim((string)$requestPayload['last_contact_date']);
            if($lastContactInput === '')
            {
                $updateLeadData['last_contact_date'] = null;
            }
            else
            {
                $lastContactDateTime = $this->_safeFollowUpDateTime($lastContactInput);
                if($lastContactDateTime !== '' && $lastContactDateTime !== null)
                {
                    $lastContactValue = date('Y-m-d', strtotime($lastContactDateTime));
                }
                else
                {
                    $lastContactValue = $this->_leadNormalizeDateInput($lastContactInput);
                }
                if($lastContactValue === '')
                {
                    echo json_encode(array('success' => false, 'message' => 'Invalid last contact date'));
                    return;
                }
                $updateLeadData['last_contact_date'] = $lastContactValue;
            }
            $hasUpdates = true;
        }

        if(($hasStatusInput || $hasFollowUpInput) && !$hasLastContactInput)
        {
            $updateLeadData['last_contact_date'] = date('Y-m-d');
            $hasUpdates = true;
        }

        if($hasRemarksInput)
        {
            $updateLeadData['remarks'] = trim((string)$requestPayload['remarks']);
            $hasUpdates = true;
        }

        $hasUserInput = (array_key_exists('user_id', $requestPayload) && (int)$requestPayload['user_id'] > 0);
        if($hasUserInput && ($this->_isGlobalAdminUser() || strtoupper(trim((string)$this->userType)) === 'AGY'))
        {
            $newAgentUserId = (int)$requestPayload['user_id'];
            $this->loadModel('User');
            $newAgentUser = $this->User->find('first', array(
                'fields' => array('User.id', 'User.project_id', 'User.login_status'),
                'conditions' => array('User.id' => $newAgentUserId),
                'recursive' => -1
            ));
            if(!empty($newAgentUser['User']['login_status']) && strcasecmp(trim((string)$newAgentUser['User']['login_status']), 'Blocked') === 0)
            {
                echo json_encode(array('success' => false, 'message' => 'Cannot assign lead: this agent login is blocked.'));
                return;
            }
            $updateLeadData['user_id'] = $newAgentUserId;
            if(!empty($newAgentUser['User']['project_id']))
            {
                $updateLeadData['project_id'] = (int)$newAgentUser['User']['project_id'];
            }
            $hasUpdates = true;
        }

        if(!$hasUpdates)
        {
            echo json_encode(array('success' => false, 'message' => 'No changes supplied'));
            return;
        }

        if($this->Lead->save(array('Lead' => $updateLeadData), array('validate' => false)))
        {
            if($hasLastContactInput)
            {
                $lastContactSqlValue = 'NULL';
                if(array_key_exists('last_contact_date', $updateLeadData) && $updateLeadData['last_contact_date'] !== null && trim((string)$updateLeadData['last_contact_date']) !== '')
                {
                    $lastContactSqlValue = "'".addslashes(trim((string)$updateLeadData['last_contact_date']))."'";
                }
                try
                {
                    $this->Lead->updateAll(
                        array('Lead.last_contact_date' => $lastContactSqlValue),
                        array('Lead.id' => $leadId)
                    );
                }
                catch(Exception $e)
                {
                }
            }
            $this->_syncLeadGoogleCalendarEvent($leadId);
            $this->_logLeadHistoryChanges($leadId, $leadRow, $updateLeadData);
            $freshLeadRow = $this->Lead->find('first', array(
                'fields' => array('Lead.id','Lead.status_id','Lead.lead_temperature','Lead.follow_up','Lead.last_contact_date','Lead.remarks'),
                'conditions' => array('Lead.id' => $leadId),
                'recursive' => -1
            ));
            $freshLead = isset($freshLeadRow['Lead']) ? (array)$freshLeadRow['Lead'] : array();
            $responseLead = array(
                'id' => $leadId,
                'status_id' => isset($freshLead['status_id']) ? (int)$freshLead['status_id'] : ($hasStatusInput ? (int)$statusId : (!empty($leadRow['Lead']['status_id']) ? (int)$leadRow['Lead']['status_id'] : 0)),
                'lead_temperature' => isset($freshLead['lead_temperature']) ? $this->_normalizeLeadTemperature($freshLead['lead_temperature']) : ($hasBadgeInput ? $badge : $this->_normalizeLeadTemperature(isset($leadRow['Lead']['lead_temperature']) ? $leadRow['Lead']['lead_temperature'] : 'warm')),
                'follow_up' => array_key_exists('follow_up', $freshLead) ? $freshLead['follow_up'] : ($hasFollowUpInput ? (isset($updateLeadData['follow_up']) ? $updateLeadData['follow_up'] : null) : (isset($leadRow['Lead']['follow_up']) ? $leadRow['Lead']['follow_up'] : null)),
                'last_contact_date' => array_key_exists('last_contact_date', $freshLead) ? $freshLead['last_contact_date'] : ((isset($updateLeadData['last_contact_date'])) ? $updateLeadData['last_contact_date'] : (isset($leadRow['Lead']['last_contact_date']) ? $leadRow['Lead']['last_contact_date'] : null)),
                'remarks' => array_key_exists('remarks', $freshLead) ? (string)$freshLead['remarks'] : ($hasRemarksInput ? (string)$updateLeadData['remarks'] : (isset($leadRow['Lead']['remarks']) ? (string)$leadRow['Lead']['remarks'] : ''))
            );
            echo json_encode(array('success' => true, 'message' => 'Lead updated', 'lead' => $responseLead));
            return;
        }
        echo json_encode(array('success' => false, 'message' => 'Unable to update lead'));
    }

    public function leadadd()
    {
        $this->loadModel('Type');
        $this->loadModel('Status');
        $this->loadModel('User');
        $this->_ensureLeadClientNullable();
        $duplicateLeadRows = array();

        $projectOptions = $this->CustomFunction->agencyWiseList($this->userType,$this->agencyId);
        $selectedProjectId = $this->_resolveProjectIdFromInput(isset($this->request->data['Lead']['project_id']) ? $this->request->data['Lead']['project_id'] : $this->agencyId);
        $agentConditions = array($this->User->assignableScopeConditions($selectedProjectId,'User'));
        $agentOptions = $this->User->find('list', array(
            'fields' => array('User.id','User.name'),
            'conditions' => $agentConditions,
            'order' => array('User.name' => 'asc')
        ));
        $statusOptions = $this->Status->find('list', array(
            'fields' => array('Status.id','Status.name'),
            'order' => array('Status.name' => 'asc')
        ));
        $typeOptions = $this->Type->find('list');
        $defaultStatusId = $this->_defaultLeadStatusId();
        $selectedAgentId = $this->_resolveUserIdFromInput(isset($this->request->data['Lead']['user_id']) ? $this->request->data['Lead']['user_id'] : 0, $selectedProjectId);

        if ($this->request->is('post'))
        {
            $form = isset($this->request->data['Lead']) ? (array)$this->request->data['Lead'] : array();
            if(empty($form['create_contact']) && empty($form['client_id']))
            {
                $form['create_contact'] = 0;
            }
            $projectId = $this->_resolveProjectIdFromInput(isset($form['project_id']) ? $form['project_id'] : 0);
            if($projectId <= 0)
            {
                $this->Session->setFlash(__('Please select agency first.'),'flash',array('alert'=>'danger'));
            }
            else
            {
                $userId = $this->_resolveUserIdFromInput(isset($form['user_id']) ? $form['user_id'] : 0, $projectId);
                $checkMobile = $this->_sanitizeMobile(isset($form['lead_mobile']) ? $form['lead_mobile'] : '');
                $checkEmail = $this->_normalizeEmail(isset($form['lead_email']) ? $form['lead_email'] : '');
                $duplicateLeadRows = $this->_leadDuplicateRows($checkMobile, $checkEmail, 0, 8);
                if(!empty($duplicateLeadRows) && empty($form['allow_duplicate']))
                {
                    $this->Session->setFlash(__('Possible duplicate lead found. Review the history below, then tick Allow duplicate if this is really a new inquiry.'),'flash',array('alert'=>'warning'));
                }
                else
                {
                    $payload = $this->_buildLeadPayload($form, $projectId, $userId, $defaultStatusId);
                    $automationResult = $this->_applyCrmAutomationToLeadPayload($payload, 'manual', 0);
                    $payload = isset($automationResult['payload']) ? (array)$automationResult['payload'] : $payload;
                    if(trim((string)$payload['lead_name'])==='')
                    {
                        $this->Session->setFlash(__('Please enter Lead Name.'),'flash',array('alert'=>'danger'));
                    }
                    elseif(trim((string)$payload['lead_mobile'])==='')
                    {
                        $this->Session->setFlash(__('Please enter Mobile.'),'flash',array('alert'=>'danger'));
                    }
                    elseif(empty($automationResult['success']) && empty($form['allow_duplicate']))
                    {
                        $this->Session->setFlash(!empty($automationResult['message']) ? $automationResult['message'] : __('Duplicate lead already exists.'),'flash',array('alert'=>'danger'));
                    }
                    else
                    {
                        $this->Lead->create();
                        $saveOk = false;
                        $saveError = '';
                        try
                        {
                            $saveOk = (bool)$this->Lead->save(array('Lead' => $payload), array('validate' => false));
                            if(!$saveOk)
                            {
                                try
                                {
                                    $saveError = trim((string)$this->Lead->getDataSource()->lastError());
                                }
                                catch(Exception $e2)
                                {
                                    $saveError = '';
                                }
                            }
                        }
                        catch(Exception $e)
                        {
                            $saveOk = false;
                            $saveError = trim((string)$e->getMessage());
                        }
                        if($saveOk)
                        {
                            $savedLeadId = (int)$this->Lead->id;
                            $this->_logLeadHistoryCreate($savedLeadId);
                            $this->_syncLeadGoogleCalendarEvent($savedLeadId);
                            $this->Session->setFlash(__('Lead has been saved.'),'flash',array('alert'=>'success'));
                            return $this->redirect(array('action' => 'leadview', $savedLeadId));
                        }
                        $msg = __('Unable to save lead. Please check required values.');
                        if($saveError !== '')
                        {
                            $msg .= ' '.__('Error:').' '.$saveError;
                        }
                        $this->Session->setFlash($msg,'flash',array('alert'=>'danger'));
                    }
                }
                $selectedAgentId = (int)$userId;
            }
            $selectedProjectId = (int)$projectId;
        }

        $this->set('projectOptions',$projectOptions);
        $this->set('agentOptions',$agentOptions);
        $this->set('statusOptions',$statusOptions);
        $this->set('typeOptions',$typeOptions);
        $this->set('selectedProjectId',$selectedProjectId);
        $this->set('selectedAgentId',$selectedAgentId);
        $this->set('defaultStatusId',$defaultStatusId);
        $this->set('duplicateLeadRows',$duplicateLeadRows);
    }

    public function leadedit($id = null)
    {
        $id = (int)$id;
        if($id <= 0)
        {
            $this->Session->setFlash(__('Invalid Lead.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'dashboard'));
        }
        $condition = $this->_leadPermissionCondition(null);
        $leadRow = $this->Lead->find('first', array(
            'conditions' => array('Lead.id' => $id, $condition)
        ));
        if(empty($leadRow['Lead']['id']))
        {
            $this->Session->setFlash(__('Invalid Lead.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'dashboard'));
        }

        $this->loadModel('Type');
        $this->loadModel('Status');
        $this->loadModel('User');

        $projectOptions = $this->CustomFunction->agencyWiseList($this->userType,$this->agencyId);
        $selectedProjectId = $this->_resolveProjectIdFromInput($leadRow['Lead']['project_id']);
        $agentConditions = array($this->User->assignableScopeConditions($selectedProjectId,'User'));
        $agentOptions = $this->User->find('list', array(
            'fields' => array('User.id','User.name'),
            'conditions' => $agentConditions,
            'order' => array('User.name' => 'asc')
        ));
        $statusOptions = $this->Status->find('list', array(
            'fields' => array('Status.id','Status.name'),
            'order' => array('Status.name' => 'asc')
        ));
        $typeOptions = $this->Type->find('list');

        if ($this->request->is(array('post','put')))
        {
            $form = isset($this->request->data['Lead']) ? (array)$this->request->data['Lead'] : array();
            $projectId = $this->_resolveProjectIdFromInput(isset($form['project_id']) ? $form['project_id'] : $leadRow['Lead']['project_id']);
            $userId = $this->_resolveUserIdFromInput(isset($form['user_id']) ? $form['user_id'] : $leadRow['Lead']['user_id'], $projectId);
            if($userId > 0)
            {
                $assignedUser = $this->User->find('first', array(
                    'fields' => array('User.id', 'User.project_id'),
                    'conditions' => array('User.id' => $userId),
                    'recursive' => -1
                ));
                if(!empty($assignedUser['User']['project_id']))
                {
                    $assignedProject = (int)$assignedUser['User']['project_id'];
                    if($projectId <= 0 || ($this->_isGlobalAdminUser() && empty($form['project_id'])))
                    {
                        $projectId = $assignedProject;
                    }
                }
            }
            if($projectId <= 0)
            {
                $this->Session->setFlash(__('Please select agency first.'),'flash',array('alert'=>'danger'));
            }
            else
            {
                $payload = $this->_buildLeadPayload($form, $projectId, $userId, $leadRow['Lead']['status_id']);
                $payload['id'] = $id;
                $automationResult = $this->_applyCrmAutomationToLeadPayload($payload, 'manual', $id);
                $payload = isset($automationResult['payload']) ? (array)$automationResult['payload'] : $payload;
                $payload['id'] = $id;
                if(empty($automationResult['success']))
                {
                    $this->Session->setFlash(!empty($automationResult['message']) ? $automationResult['message'] : __('Duplicate lead already exists.'),'flash',array('alert'=>'danger'));
                }
                elseif((int)$payload['client_id']<=0)
                {
                    $this->Session->setFlash(__('Unable to link customer contact. Please select Existing Contact or provide Lead Name and mobile/email.'),'flash',array('alert'=>'danger'));
                }
                else
                {
                    $saveOk = false;
                    $saveError = '';
                    try
                    {
                        $saveOk = (bool)$this->Lead->save(array('Lead' => $payload), array('validate' => false));
                        if(!$saveOk)
                        {
                            try
                            {
                                $saveError = trim((string)$this->Lead->getDataSource()->lastError());
                            }
                            catch(Exception $e2)
                            {
                                $saveError = '';
                            }
                        }
                    }
                    catch(Exception $e)
                    {
                        $saveOk = false;
                        $saveError = trim((string)$e->getMessage());
                    }
                    if($saveOk)
                    {
                        $this->_logLeadHistoryChanges($id, $leadRow, $payload);
                        $this->_syncLeadGoogleCalendarEvent($id);
                        $this->Session->setFlash(__('Lead has been updated.'),'flash',array('alert'=>'success'));
                        return $this->redirect(array('action' => 'leadview', $id));
                    }
                    $msg = __('Unable to update lead.');
                    if($saveError !== '')
                    {
                        $msg .= ' '.__('Error:').' '.$saveError;
                    }
                    $this->Session->setFlash($msg,'flash',array('alert'=>'danger'));
                }
            }
        }

        if (!$this->request->data)
        {
            $this->request->data = $leadRow;
        }

        $this->set('leadRow',$leadRow);
        $this->set('projectOptions',$projectOptions);
        $this->set('agentOptions',$agentOptions);
        $this->set('statusOptions',$statusOptions);
        $this->set('typeOptions',$typeOptions);
        $this->set('selectedProjectId',$selectedProjectId);
    }

    public function leadview($id = null)
    {
        $id = (int)$id;
        if($id <= 0)
        {
            $this->Session->setFlash(__('Invalid Lead.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'dashboard'));
        }
        $condition = $this->_leadPermissionCondition(null);
        $leadRow = $this->Lead->find('first', array(
            'conditions' => array('Lead.id' => $id, $condition)
        ));
        if(empty($leadRow['Lead']['id']))
        {
            $this->Session->setFlash(__('Invalid Lead.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'dashboard'));
        }

        // Lead model does not always eager-load Agency(Project) and Agent(User),
        // so resolve them explicitly for stable view output.
        if(empty($leadRow['Project']['name']))
        {
            $projectId = !empty($leadRow['Lead']['project_id']) ? (int)$leadRow['Lead']['project_id'] : 0;
            if($projectId > 0)
            {
                $this->loadModel('Project');
                $projectRow = $this->Project->find('first', array(
                    'fields' => array('Project.id','Project.name'),
                    'conditions' => array('Project.id' => $projectId),
                    'recursive' => -1
                ));
                if(!empty($projectRow['Project']['id']))
                {
                    $leadRow['Project'] = $projectRow['Project'];
                }
            }
        }

        if(empty($leadRow['User']['name']))
        {
            $agentId = !empty($leadRow['Lead']['user_id']) ? (int)$leadRow['Lead']['user_id'] : 0;
            if($agentId > 0)
            {
                $this->loadModel('User');
                $agentRow = $this->User->find('first', array(
                    'fields' => array('User.id','User.name'),
                    'conditions' => array('User.id' => $agentId),
                    'recursive' => -1
                ));
                if(!empty($agentRow['User']['id']))
                {
                    $leadRow['User'] = $agentRow['User'];
                }
            }
        }

        $LeadHistory = $this->_leadHistoryModel();
        $historyRows = array();
        if($LeadHistory)
        {
            $historyRows = (array)$LeadHistory->find('all', array(
                'conditions' => array('LeadHistory.lead_id' => $id),
                'order' => array('LeadHistory.created' => 'desc', 'LeadHistory.id' => 'desc'),
                'recursive' => -1
            ));
        }

        $this->loadModel('Status');
        $statusOptions = (array)$this->Status->find('list', array(
            'fields' => array('Status.id', 'Status.name'),
            'order' => array('Status.name' => 'asc')
        ));

        $this->set('leadRow',$leadRow);
        $this->set('historyRows',$historyRows);
        $this->set('statusOptions',$statusOptions);
    }

    public function history($id = null)
    {
        $id = (int)$id;
        if($id <= 0)
        {
            $this->Session->setFlash(__('Invalid Lead.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'manage'));
        }

        $condition = $this->_leadPermissionCondition(null);
        $leadRow = $this->Lead->find('first', array(
            'conditions' => array('Lead.id' => $id, $condition)
        ));
        if(empty($leadRow['Lead']['id']))
        {
            $this->Session->setFlash(__('Invalid Lead.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'manage'));
        }

        if(empty($leadRow['Project']['name']))
        {
            $projectId = !empty($leadRow['Lead']['project_id']) ? (int)$leadRow['Lead']['project_id'] : 0;
            if($projectId > 0)
            {
                $this->loadModel('Project');
                $projectRow = $this->Project->find('first', array(
                    'fields' => array('Project.id','Project.name'),
                    'conditions' => array('Project.id' => $projectId),
                    'recursive' => -1
                ));
                if(!empty($projectRow['Project']['id']))
                {
                    $leadRow['Project'] = $projectRow['Project'];
                }
            }
        }

        if(empty($leadRow['User']['name']))
        {
            $agentId = !empty($leadRow['Lead']['user_id']) ? (int)$leadRow['Lead']['user_id'] : 0;
            if($agentId > 0)
            {
                $this->loadModel('User');
                $agentRow = $this->User->find('first', array(
                    'fields' => array('User.id','User.name','Ugroup.name'),
                    'conditions' => array('User.id' => $agentId),
                    'recursive' => 0
                ));
                if(!empty($agentRow['User']['id']))
                {
                    $leadRow['User'] = $agentRow['User'];
                }
                if(!empty($agentRow['Ugroup']['name']))
                {
                    $leadRow['LeadOwnerRole'] = array('name' => (string)$agentRow['Ugroup']['name']);
                }
            }
        }

        $LeadHistory = $this->_leadHistoryModel();
        $historyRows = array();
        if($LeadHistory)
        {
            $historyRows = (array)$LeadHistory->find('all', array(
                'conditions' => array('LeadHistory.lead_id' => $id),
                'order' => array('LeadHistory.created' => 'desc', 'LeadHistory.id' => 'desc'),
                'recursive' => -1
            ));
        }

        $this->set('leadRow',$leadRow);
        $this->set('historyRows',$historyRows);
        $this->set('displayHistoryRows',$this->_leadHistoryDisplayRows($historyRows));
    }

    public function import()
    {
        $this->loadModel('User');
        $this->_ensureLeadClientNullable();
        $isGlobalAdmin = $this->_isGlobalAdminUser();
        $showContactLinkOption = ($isGlobalAdmin || strtoupper(trim((string)$this->userType)) === 'AGY');
        $currentUserLevelId = $this->_currentLeadImportUserLevelId();
        $projectOptions = $this->CustomFunction->agencyWiseList($this->userType,$this->agencyId);
        $selectedProjectId = $this->_resolveProjectIdFromInput($this->agencyId);
        $selectedUserLevelId = $isGlobalAdmin ? 0 : $currentUserLevelId;
        $selectedUserId = $isGlobalAdmin ? 0 : (int)$this->luserId;
        if($isGlobalAdmin)
        {
            if(isset($this->request->query['project_id']))
            {
                $selectedProjectId = $this->_resolveProjectIdFromInput($this->request->query['project_id']);
            }
        }
        elseif((int)$this->agencyId > 0)
        {
            $selectedProjectId = (int)$this->agencyId;
        }
        if($isGlobalAdmin && isset($this->request->query['ugroup_id']))
        {
            $selectedUserLevelId = $this->_normalizeLeadImportUserLevelId($this->request->query['ugroup_id']);
        }
        if($isGlobalAdmin && isset($this->request->query['user_id']))
        {
            $selectedUserId = (int)$this->request->query['user_id'];
        }

        if($this->request->is('post'))
        {
            $form = isset($this->request->data['Lead']) ? (array)$this->request->data['Lead'] : array();
            if($isGlobalAdmin)
            {
                $projectId = $this->_resolveProjectIdFromInput(isset($form['project_id']) ? $form['project_id'] : $selectedProjectId);
                $userLevelId = $this->_normalizeLeadImportUserLevelId(isset($form['ugroup_id']) ? $form['ugroup_id'] : $selectedUserLevelId);
                $selectedUserLevelId = (int)$userLevelId;
                $selectedUserId = isset($form['user_id']) ? (int)$form['user_id'] : 0;
                $userId = $this->_resolveUserIdFromInput(isset($form['user_id']) ? $form['user_id'] : 0, $projectId, $userLevelId);
            }
            else
            {
                $projectId = (int)$this->agencyId;
                $userLevelId = (int)$currentUserLevelId;
                $selectedUserLevelId = (int)$userLevelId;
                $selectedUserId = (int)$this->luserId;
                $userId = (int)$this->luserId;
            }
            $autoCreateContact = ($showContactLinkOption && !empty($form['create_contact'])) ? 1 : 0;

            if($projectId <= 0)
            {
                $this->Session->setFlash(__('Please select agency first.'),'flash',array('alert'=>'danger'));
            }
            elseif(empty($this->request->data['Lead']['import_file']['tmp_name']))
            {
                $this->Session->setFlash(__('Please upload import file.'),'flash',array('alert'=>'danger'));
            }
            else
            {
                $tmpFile = $this->request->data['Lead']['import_file']['tmp_name'];
                $fileName = $this->request->data['Lead']['import_file']['name'];
                $rows = $this->_parseImportRows($tmpFile, $fileName);
                if(empty($rows) || count($rows) < 2)
                {
                    $this->Session->setFlash(__('Import file is empty or invalid. Use sample format.'),'flash',array('alert'=>'danger'));
                }
                else
                {
                    $headerMap = $this->_mapImportHeaders($rows[0]);
                    if(empty($headerMap))
                    {
                        $this->Session->setFlash(__('Header row is invalid. Please use sample file headers.'),'flash',array('alert'=>'danger'));
                    }
                    else
                    {
                        $imported = 0;
                        $skippedDuplicates = 0;
                        $failed = 0;
                        $duplicateReport = array();
                        $defaultStatusId = $this->_defaultLeadStatusId();
                        for($i=1;$i<count($rows);$i++)
                        {
                            $line = (array)$rows[$i];
                            $lineData = array();
                            foreach($headerMap as $colIndex => $fieldKey)
                            {
                                $lineData[$fieldKey] = isset($line[$colIndex]) ? trim((string)$line[$colIndex]) : '';
                            }

                            $lineProjectId = $projectId;
                            if($isGlobalAdmin && !empty($lineData['project_id']) && (int)$lineData['project_id'] > 0)
                            {
                                $lineProjectId = (int)$lineData['project_id'];
                            }
                            $lineUserLevelId = $userLevelId;
                            if($isGlobalAdmin && !empty($lineData['ugroup_id']))
                            {
                                $lineUserLevelId = $this->_normalizeLeadImportUserLevelId($lineData['ugroup_id']);
                            }
                            $lineUserId = $userId;
                            if($isGlobalAdmin && !empty($lineData['user_id']) && (int)$lineData['user_id'] > 0)
                            {
                                $lineUserId = (int)$lineData['user_id'];
                            }
                            if($isGlobalAdmin)
                            {
                                $lineUserId = $this->_resolveUserIdFromInput($lineUserId, $lineProjectId, $lineUserLevelId);
                            }
                            else
                            {
                                $lineUserId = (int)$this->luserId;
                            }
                            if($lineUserId > 0)
                            {
                                $checkLineUser = $this->User->find('first', array(
                                    'fields' => array('User.id', 'User.login_status'),
                                    'conditions' => array('User.id' => $lineUserId),
                                    'recursive' => -1
                                ));
                                if(!empty($checkLineUser['User']['login_status']) && strcasecmp(trim((string)$checkLineUser['User']['login_status']), 'Blocked') === 0)
                                {
                                    $fallbackAgent = $this->User->find('first', array(
                                        'fields' => array('User.id'),
                                        'conditions' => array($this->User->assignableScopeConditions($lineProjectId, 'User')),
                                        'order' => array('User.id' => 'asc'),
                                        'recursive' => -1
                                    ));
                                    $lineUserId = !empty($fallbackAgent['User']['id']) ? (int)$fallbackAgent['User']['id'] : 1;
                                }
                            }

                            $leadPayloadInput = array(
                                'lead_name' => isset($lineData['lead_name']) ? $lineData['lead_name'] : '',
                                'lead_mobile' => isset($lineData['lead_mobile']) ? $lineData['lead_mobile'] : '',
                                'lead_email' => isset($lineData['lead_email']) ? $lineData['lead_email'] : '',
                                'lead_address' => isset($lineData['lead_address']) ? $lineData['lead_address'] : '',
                                'lead_source' => isset($lineData['lead_source']) ? $lineData['lead_source'] : '',
                                'last_contact_date' => isset($lineData['last_contact_date']) ? $lineData['last_contact_date'] : '',
                                'status_id' => isset($lineData['status']) ? $lineData['status'] : $defaultStatusId,
                                'follow_up' => isset($lineData['follow_up']) ? $lineData['follow_up'] : '',
                                'remarks' => isset($lineData['remarks']) ? $lineData['remarks'] : '',
                                'property_id' => isset($lineData['property']) ? $lineData['property'] : '',
                                'client_id' => isset($lineData['client_id']) ? $lineData['client_id'] : 0,
                                'create_contact' => $autoCreateContact
                            );

                            if(trim((string)$leadPayloadInput['lead_name'])==='' && trim((string)$leadPayloadInput['lead_mobile'])==='' && trim((string)$leadPayloadInput['lead_email'])==='' && (int)$leadPayloadInput['client_id']<=0)
                            {
                                continue;
                            }

                            // DEDUPLICATION: Check if this mobile number or email is already assigned to any agent
                            $existingDupes = $this->_leadDuplicateRows($leadPayloadInput['lead_mobile'], $leadPayloadInput['lead_email'], 0, 1);
                            if(!empty($existingDupes))
                            {
                                $existing = $existingDupes[0];
                                $existingLeadId = (int)$existing['Lead']['id'];
                                $existingAgentName = !empty($existing['AssignedUser']['name']) ? $existing['AssignedUser']['name'] : __('Unassigned / System');
                                $existingStatus = !empty($existing['Status']['name']) ? $existing['Status']['name'] : '-';
                                $existingAgency = !empty($existing['Project']['name']) ? $existing['Project']['name'] : '-';
                                $existingCreated = (!empty($existing['Lead']['created']) && $existing['Lead']['created'] !== '0000-00-00 00:00:00') ? date('d-M-Y', strtotime($existing['Lead']['created'])) : '-';

                                $duplicateReport[] = array(
                                    'row_num' => $i + 1,
                                    'lead_name' => trim((string)$leadPayloadInput['lead_name']) !== '' ? $leadPayloadInput['lead_name'] : (!empty($existing['Lead']['lead_name']) ? $existing['Lead']['lead_name'] : '-'),
                                    'mobile' => $leadPayloadInput['lead_mobile'],
                                    'email' => $leadPayloadInput['lead_email'],
                                    'existing_lead_id' => $existingLeadId,
                                    'assigned_agent' => $existingAgentName,
                                    'agency' => $existingAgency,
                                    'status' => $existingStatus,
                                    'created_date' => $existingCreated,
                                    'remarks' => $leadPayloadInput['remarks']
                                );

                                // Log note in existing lead history so agent is aware of duplicate inquiry
                                if($existingLeadId > 0)
                                {
                                    try
                                    {
                                        $LeadHistory = $this->_leadHistoryModel();
                                        if($LeadHistory)
                                        {
                                            $actor = $this->_leadHistoryActorContext();
                                            $noteMsg = "⚠️ Duplicate Lead inquiry skipped during CSV Import (" . $fileName . ").\n";
                                            if(trim((string)$leadPayloadInput['remarks']) !== '')
                                            {
                                                $noteMsg .= "Imported Remark: " . trim((string)$leadPayloadInput['remarks']) . "\n";
                                            }
                                            $noteMsg .= "Source in file: " . (!empty($leadPayloadInput['lead_source']) ? $leadPayloadInput['lead_source'] : 'CSV Import');

                                            $LeadHistory->createEntry(array_merge($actor, array(
                                                'lead_id' => $existingLeadId,
                                                'project_id' => !empty($existing['Lead']['project_id']) ? (int)$existing['Lead']['project_id'] : 0,
                                                'lead_user_id' => !empty($existing['Lead']['user_id']) ? (int)$existing['Lead']['user_id'] : 0,
                                                'entry_type' => 'comment',
                                                'title' => 'Duplicate Inquiry Skipped During Import',
                                                'field_name' => 'duplicate_import',
                                                'field_label' => 'Import Deduplication',
                                                'new_value' => 'Duplicate Skipped',
                                                'message_body' => $noteMsg,
                                                'meta_json' => array('file_name' => $fileName, 'row_num' => $i + 1)
                                            )));
                                        }
                                    }
                                    catch(Exception $e) {}
                                }

                                $skippedDuplicates++;
                                continue; // DO NOT RE-ADD IN LEADS!
                            }

                            $payload = $this->_buildLeadPayload($leadPayloadInput, $lineProjectId, $lineUserId, $defaultStatusId);
                            $automationResult = $this->_applyCrmAutomationToLeadPayload($payload, 'import', 0);
                            $payload = isset($automationResult['payload']) ? (array)$automationResult['payload'] : $payload;
                            if(trim((string)$payload['lead_name'])==='' && trim((string)$payload['lead_mobile'])==='' && trim((string)$payload['lead_email'])==='' && (int)$payload['client_id']<=0)
                            {
                                $failed++;
                                continue;
                            }
                            if(empty($automationResult['success']))
                            {
                                $failed++;
                                continue;
                            }
                            if($autoCreateContact === 1 && (int)$payload['client_id']<=0)
                            {
                                $failed++;
                                continue;
                            }

                            $this->Lead->create();
                            $saveOk = false;
                            try
                            {
                                $saveOk = (bool)$this->Lead->save(array('Lead' => $payload), array('validate' => false));
                            }
                            catch(Exception $e)
                            {
                                $saveOk = false;
                            }
                            if($saveOk)
                            {
                                $this->_logLeadHistoryCreate((int)$this->Lead->id, 'Lead Imported');
                                $imported++;
                            }
                            else
                            {
                                $failed++;
                            }
                        }

                        if(!empty($duplicateReport))
                        {
                            $this->Session->write('LeadImportDuplicateReport', $duplicateReport);
                        }

                        if($imported > 0 || $skippedDuplicates > 0)
                        {
                            $msg = __('Leads import processed. Successfully imported: %s new lead(s).', $imported);
                            if($skippedDuplicates > 0)
                            {
                                $msg .= ' | ' . __('⚠️ %s duplicate lead(s) skipped (already assigned to existing agents - see details below).', $skippedDuplicates);
                            }
                            if($failed > 0)
                            {
                                $msg .= ' | ' . __('Failed rows: %s', $failed);
                            }
                            $flashType = ($imported > 0) ? 'success' : 'warning';
                            $this->Session->setFlash($msg, 'flash', array('alert' => $flashType));
                            return $this->redirect(array('action' => 'import'));
                        }
                        $this->Session->setFlash(__('No leads imported. Please check your file values.'),'flash',array('alert'=>'danger'));
                    }
                }
            }
            $selectedProjectId = (int)$projectId;
            $selectedUserId = (int)$userId;
        }

        $duplicateReport = array();
        if($this->Session->check('LeadImportDuplicateReport'))
        {
            $duplicateReport = (array)$this->Session->read('LeadImportDuplicateReport');
            $this->Session->delete('LeadImportDuplicateReport');
        }

        $userLevelOptions = $this->_leadImportUserLevelOptions();
        $agentOptions = $this->_leadImportAgentOptions($selectedProjectId, $selectedUserLevelId);
        if($selectedUserId > 0 && !isset($agentOptions[$selectedUserId]))
        {
            $selectedUserId = 0;
        }
        $currentImportAgencyName = '';
        if((int)$selectedProjectId > 0 && !empty($projectOptions[(int)$selectedProjectId]))
        {
            $currentImportAgencyName = (string)$projectOptions[(int)$selectedProjectId];
        }
        $this->set('projectOptions',$projectOptions);
        $this->set('userLevelOptions',$userLevelOptions);
        $this->set('agentOptions',$agentOptions);
        $this->set('selectedProjectId',$selectedProjectId);
        $this->set('selectedUserLevelId',$selectedUserLevelId);
        $this->set('selectedUserId',$selectedUserId);
        $this->set('duplicateReport',$duplicateReport);
        $this->set('importIsolatedToCurrentUser', !$isGlobalAdmin);
        $this->set('showImportCreateContactOption', $showContactLinkOption);
        $this->set('currentImportUserName', !empty($this->adminValue['User']['name']) ? (string)$this->adminValue['User']['name'] : '');
        $this->set('currentImportUserLevelName', !empty($this->adminValue['Ugroup']['name']) ? (string)$this->adminValue['Ugroup']['name'] : '');
        $this->set('currentImportAgencyName', $currentImportAgencyName);
    }

    public function samplecsv()
    {
        $this->autoRender = false;
        $filename = 'lead_import_sample_'.date('Ymd_His').'.csv';
        header("Content-Type: text/csv; charset=utf-8");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Pragma: no-cache");
        header("Expires: 0");
        $out = fopen('php://output','w');
        fputcsv($out,array('lead_name','lead_mobile','lead_email','lead_address','lead_city','lead_budget','lead_language','lead_source','source_form_id','source_ad_id','utm_campaign','last_contact_date','status','follow_up','remarks','property'));
        fputcsv($out,array('Ahmad Raza','923001234567','ahmad@example.com','Johar Town Lahore','Lahore','3500000','Urdu','Facebook','123456789','987654321','Ramzan Offer','2026-04-01','New','2026-04-02 03:00 PM','Interested in 3 marla file','1 Marla Plot'));
        fputcsv($out,array('Sara Khan','923009876543','sara@example.com','Bahria Town Lahore','Lahore','5200000','English','Referral','','','Summer Walk-In','2026-04-01','Contacted','2026-04-05 11:30 AM','Asked for payment plan',''));
        fclose($out);
        exit();
    }

    protected function _vcardEscape($value)
    {
        $value = trim((string)$value);
        if($value === '')
        {
            return '';
        }
        $value = str_replace('\\','\\\\',$value);
        $value = str_replace(';','\;',$value);
        $value = str_replace(',','\,',$value);
        $value = preg_replace("/\r\n|\r|\n/", "\\n", $value);
        return $value;
    }

    public function phonecontact($id = null)
    {
        $this->autoRender = false;
        $id = (int)$id;
        if($id <= 0)
        {
            throw new NotFoundException(__('Invalid Lead.'));
        }

        $condition = $this->_leadPermissionCondition(null);
        $leadRow = $this->Lead->find('first', array(
            'conditions' => array('Lead.id' => $id, $condition)
        ));
        if(empty($leadRow['Lead']['id']))
        {
            throw new NotFoundException(__('Lead not found.'));
        }

        $leadName = trim((string)$leadRow['Lead']['lead_name']);
        if($leadName === '' && !empty($leadRow['Client']['name']))
        {
            $leadName = trim((string)$leadRow['Client']['name']);
        }
        if($leadName === '')
        {
            $leadName = 'Lead '.$id;
        }

        $leadMobile = $this->_sanitizeMobile($leadRow['Lead']['lead_mobile']);
        if($leadMobile === '' && !empty($leadRow['Client']['mobile']))
        {
            $leadMobile = $this->_sanitizeMobile($leadRow['Client']['mobile']);
        }
        if($leadMobile === '' && !empty($leadRow['Client']['phone']))
        {
            $leadMobile = $this->_sanitizeMobile($leadRow['Client']['phone']);
        }

        $leadEmail = $this->_normalizeEmail($leadRow['Lead']['lead_email']);
        if($leadEmail === '' && !empty($leadRow['Client']['email']))
        {
            $leadEmail = $this->_normalizeEmail($leadRow['Client']['email']);
        }

        $leadAddress = trim((string)$leadRow['Lead']['lead_address']);
        if($leadAddress === '' && !empty($leadRow['Client']['address']))
        {
            $leadAddress = trim((string)$leadRow['Client']['address']);
        }

        $noteParts = array();
        if(!empty($leadRow['Lead']['lead_source']))
        {
            $noteParts[] = 'Source: '.trim((string)$leadRow['Lead']['lead_source']);
        }
        if(!empty($leadRow['Lead']['remarks']))
        {
            $noteParts[] = 'Remarks: '.trim((string)$leadRow['Lead']['remarks']);
        }
        if(!empty($leadRow['Project']['name']))
        {
            $noteParts[] = 'Agency: '.trim((string)$leadRow['Project']['name']);
        }

        $vcardLines = array();
        $vcardLines[] = 'BEGIN:VCARD';
        $vcardLines[] = 'VERSION:3.0';
        $vcardLines[] = 'FN:'.$this->_vcardEscape($leadName);
        $vcardLines[] = 'N:'.$this->_vcardEscape($leadName).';;;;';
        if($leadMobile !== '')
        {
            $vcardLines[] = 'TEL;TYPE=CELL:'.$this->_vcardEscape($leadMobile);
        }
        if($leadEmail !== '')
        {
            $vcardLines[] = 'EMAIL;TYPE=INTERNET:'.$this->_vcardEscape($leadEmail);
        }
        if($leadAddress !== '')
        {
            $vcardLines[] = 'ADR;TYPE=HOME:;;'.$this->_vcardEscape($leadAddress).';;;;';
        }
        if(!empty($noteParts))
        {
            $vcardLines[] = 'NOTE:'.$this->_vcardEscape(implode(' | ', $noteParts));
        }
        $vcardLines[] = 'END:VCARD';
        $vcardContent = implode("\r\n", $vcardLines)."\r\n";

        $filename = 'lead_contact_'.$id.'.vcf';
        $this->response->header(array(
            'Content-Type' => 'text/vcard; charset=UTF-8',
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache'
        ));
        $this->response->body($vcardContent);
        return $this->response;
    }

    public function convertcontact($id = null)
    {
        $id = (int)$id;
        if((int)$this->request->query('phone') === 1)
        {
            return $this->phonecontact($id);
        }
        if($id <= 0)
        {
            $this->Session->setFlash(__('Invalid Lead.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'dashboard'));
        }
        $condition = $this->_leadPermissionCondition(null);
        $leadRow = $this->Lead->find('first', array(
            'conditions' => array('Lead.id' => $id, $condition)
        ));
        if(empty($leadRow['Lead']['id']))
        {
            $this->Session->setFlash(__('Lead not found.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'dashboard'));
        }

        $existingClientId = (int)$leadRow['Lead']['client_id'];
        if($existingClientId > 0)
        {
            $this->loadModel('Admin.Contact');
            $existingClient = $this->Contact->find('first', array(
                'fields' => array('Contact.id','Contact.name'),
                'conditions' => array('Contact.id' => $existingClientId),
                'recursive' => -1
            ));
            if(!empty($existingClient['Contact']['id']))
            {
                $this->Session->setFlash(__('This lead is already linked with customer contact: ').$existingClient['Contact']['name'],'flash',array('alert'=>'info'));
                return $this->redirect($this->referer(array('action' => 'leadview', $id), true));
            }
        }

        $leadName = trim((string)$leadRow['Lead']['lead_name']);
        if($leadName === '' && !empty($leadRow['Client']['name']))
        {
            $leadName = trim((string)$leadRow['Client']['name']);
        }
        $leadMobile = $this->_sanitizeMobile($leadRow['Lead']['lead_mobile']);
        if($leadMobile === '' && !empty($leadRow['Client']['mobile']))
        {
            $leadMobile = $this->_sanitizeMobile($leadRow['Client']['mobile']);
        }
        $leadEmail = $this->_normalizeEmail($leadRow['Lead']['lead_email']);
        if($leadEmail === '' && !empty($leadRow['Client']['email']))
        {
            $leadEmail = $this->_normalizeEmail($leadRow['Client']['email']);
        }
        $leadAddress = trim((string)$leadRow['Lead']['lead_address']);
        if($leadAddress === '' && !empty($leadRow['Client']['address']))
        {
            $leadAddress = trim((string)$leadRow['Client']['address']);
        }

        if($leadName === '')
        {
            $this->Session->setFlash(__('Lead Name is required before converting to customer contact.'),'flash',array('alert'=>'danger'));
            return $this->redirect($this->referer(array('action' => 'leadview', $id), true));
        }

        $projectId = (int)$leadRow['Lead']['project_id'];
        $userId = (int)$leadRow['Lead']['user_id'];
        $clientId = $this->_findContactByLeadData($projectId, $leadMobile, $leadEmail, 0);
        if($clientId <= 0)
        {
            $clientId = $this->_createContactFromLeadData($projectId, $userId, $leadName, $leadMobile, $leadEmail, $leadAddress);
        }
        if($clientId <= 0)
        {
            $this->Session->setFlash(__('Unable to convert lead to customer contact.'),'flash',array('alert'=>'danger'));
            return $this->redirect($this->referer(array('action' => 'leadview', $id), true));
        }

        $beforeLeadRow = $this->Lead->find('first', array(
            'fields' => array('Lead.id','Lead.project_id','Lead.user_id','Lead.client_id'),
            'conditions' => array('Lead.id' => $id),
            'recursive' => -1
        ));
        $this->Lead->id = $id;
        $this->Lead->save(array('Lead' => array('id' => $id, 'client_id' => $clientId)), array('validate' => false));
        $this->_logLeadHistoryChanges($id, $beforeLeadRow, array('client_id' => $clientId));
        $this->Session->setFlash(__('Lead converted to customer contact successfully.'),'flash',array('alert'=>'success'));
        return $this->redirect($this->referer(array('action' => 'leadview', $id), true));
    }

    public function leaderboard()
    {
        $this->loadModel('User');
        $this->loadModel('Status');
        $this->loadModel('Project');
        $this->loadModel('LeadHistory');

        $canUseAgencyFilters = $this->_leadCanUseAgencyFilters();
        $selectedProjectId = 0;
        if($canUseAgencyFilters && isset($this->request->query['project_id']))
        {
            $selectedProjectId = (int)$this->request->query['project_id'];
        }
        elseif(!$canUseAgencyFilters && (int)$this->agencyId > 0)
        {
            $selectedProjectId = (int)$this->agencyId;
        }

        $selectedPeriod = isset($this->request->query['period']) ? strtolower(trim((string)$this->request->query['period'])) : 'month';
        if(!in_array($selectedPeriod, array('today','week','month','year','all','custom'), true))
        {
            $selectedPeriod = 'month';
        }
        $startDateInput = isset($this->request->query['start_date']) ? trim((string)$this->request->query['start_date']) : '';
        $endDateInput = isset($this->request->query['end_date']) ? trim((string)$this->request->query['end_date']) : '';

        $startDate = '';
        $endDate = '';
        if($selectedPeriod === 'today') { $startDate = date('Y-m-d'); $endDate = date('Y-m-d'); }
        elseif($selectedPeriod === 'week') { $startDate = date('Y-m-d', strtotime('monday this week')); $endDate = date('Y-m-d', strtotime('sunday this week')); }
        elseif($selectedPeriod === 'month') { $startDate = date('Y-m-01'); $endDate = date('Y-m-t'); }
        elseif($selectedPeriod === 'year') { $startDate = date('Y-01-01'); $endDate = date('Y-12-31'); }
        elseif($selectedPeriod === 'custom') { $startDate = $this->_leadNormalizeDateInput($startDateInput); $endDate = $this->_leadNormalizeDateInput($endDateInput); }

        $userConditions = array();
        if($selectedProjectId > 0)
        {
            $userConditions[] = array('User.project_id' => $selectedProjectId);
        }
        elseif(!$canUseAgencyFilters && (int)$this->agencyId > 0)
        {
            $userConditions[] = array('User.project_id' => (int)$this->agencyId);
        }
        $agents = (array)$this->User->find('all', array(
            'fields' => array('User.id','User.name','User.email','User.mobile','User.ugroup_id','User.project_id'),
            'conditions' => $userConditions,
            'recursive' => -1
        ));

        $wonStatusRows = (array)$this->Status->find('all', array(
            'fields' => array('Status.id','Status.name'),
            'conditions' => array('LOWER(Status.name) REGEXP' => 'booked|sold|won|converted|completed|deal closed'),
            'recursive' => -1
        ));
        $wonStatusIds = array();
        foreach($wonStatusRows as $wsr) { $wonStatusIds[] = (int)$wsr['Status']['id']; }

        $lostStatusRows = (array)$this->Status->find('all', array(
            'fields' => array('Status.id','Status.name'),
            'conditions' => array('LOWER(Status.name) REGEXP' => 'cancel|lost|dead|not interested'),
            'recursive' => -1
        ));
        $lostStatusIds = array();
        foreach($lostStatusRows as $lsr) { $lostStatusIds[] = (int)$lsr['Status']['id']; }

        $todayStart = date('Y-m-d 00:00:00');
        $leaderboardData = array();

        foreach($agents as $agent)
        {
            $uid = (int)$agent['User']['id'];
            $baseCond = array('Lead.user_id' => $uid);
            if($startDate !== '' && $endDate !== '')
            {
                $baseCond['Lead.created >='] = $startDate.' 00:00:00';
                $baseCond['Lead.created <='] = $endDate.' 23:59:59';
            }

            $totalAssigned = (int)$this->Lead->find('count', array('conditions' => $baseCond));
            $wonCount = 0;
            if(!empty($wonStatusIds) && $totalAssigned > 0)
            {
                $wonCount = (int)$this->Lead->find('count', array('conditions' => array_merge($baseCond, array('Lead.status_id' => $wonStatusIds))));
            }
            $lostCount = 0;
            if(!empty($lostStatusIds) && $totalAssigned > 0)
            {
                $lostCount = (int)$this->Lead->find('count', array('conditions' => array_merge($baseCond, array('Lead.status_id' => $lostStatusIds))));
            }
            $overdueCount = (int)$this->Lead->find('count', array('conditions' => array_merge($baseCond, array('Lead.follow_up IS NOT NULL', 'Lead.follow_up !=' => '0000-00-00 00:00:00', 'Lead.follow_up <' => $todayStart))));

            $activityCond = array('LeadHistory.lead_user_id' => $uid);
            if($startDate !== '' && $endDate !== '')
            {
                $activityCond['LeadHistory.created >='] = $startDate.' 00:00:00';
                $activityCond['LeadHistory.created <='] = $endDate.' 23:59:59';
            }
            $activitiesLogged = (int)$this->LeadHistory->find('count', array('conditions' => $activityCond));

            $conversionRate = $totalAssigned > 0 ? round(($wonCount / $totalAssigned) * 100, 1) : 0;
            $followupHealth = $totalAssigned > 0 ? max(0, min(100, round(100 - (($overdueCount / $totalAssigned) * 100)))) : 100;

            $score = ($wonCount * 50) + ($activitiesLogged * 2) + ($conversionRate * 10) - ($overdueCount * 5);

            $leaderboardData[] = array(
                'user_id' => $uid,
                'name' => $agent['User']['name'],
                'email' => $agent['User']['email'],
                'mobile' => $agent['User']['mobile'],
                'project_id' => (int)$agent['User']['project_id'],
                'total_assigned' => $totalAssigned,
                'won_count' => $wonCount,
                'lost_count' => $lostCount,
                'overdue_count' => $overdueCount,
                'conversion_rate' => $conversionRate,
                'followup_health' => $followupHealth,
                'activities_logged' => $activitiesLogged,
                'score' => $score
            );
        }

        usort($leaderboardData, function($a, $b){
            if($b['won_count'] !== $a['won_count']) return $b['won_count'] - $a['won_count'];
            if($b['conversion_rate'] != $a['conversion_rate']) return ($b['conversion_rate'] > $a['conversion_rate']) ? 1 : -1;
            return $b['activities_logged'] - $a['activities_logged'];
        });

        $projectOptions = array();
        if($canUseAgencyFilters)
        {
            $projectOptions = (array)$this->Project->find('list', array('order' => array('Project.name' => 'asc')));
        }

        $this->set('leaderboardData', $leaderboardData);
        $this->set('projectOptions', $projectOptions);
        $this->set('selectedProjectId', $selectedProjectId);
        $this->set('selectedPeriod', $selectedPeriod);
        $this->set('selectedStartDateInput', $startDateInput);
        $this->set('selectedEndDateInput', $endDateInput);
        $this->set('canUseAgencyFilters', $canUseAgencyFilters);
    }

    public function quotation($id = null)
    {
        $id = (int)$id;
        if($id <= 0)
        {
            throw new NotFoundException(__('Invalid Lead'));
        }
        $permissionCondition = $this->_leadPermissionCondition($id);
        $lead = $this->Lead->find('first', array(
            'conditions' => array('Lead.id' => $id, $permissionCondition),
            'recursive' => 0
        ));
        if(empty($lead['Lead']['id']))
        {
            throw new NotFoundException(__('Lead not found'));
        }

        $this->loadModel('Property');
        $this->loadModel('Project');
        $this->loadModel('CrmAutomationSetting');

        $settingsRow = $this->CrmAutomationSetting->findProjectRow((int)$lead['Lead']['project_id']);
        $settings = isset($settingsRow['CrmAutomationSetting']) ? $settingsRow['CrmAutomationSetting'] : array();

        if($this->request->is('post'))
        {
            $quotTotal = !empty($this->request->data['quotation_total']) ? (float)$this->request->data['quotation_total'] : 0;
            $quotPlanJson = !empty($this->request->data['quotation_plan']) ? (string)$this->request->data['quotation_plan'] : '';

            $this->Lead->id = $id;
            $this->Lead->saveField('quotation_total', $quotTotal);
            $this->Lead->saveField('quotation_plan', $quotPlanJson);

            $actor = $this->_leadHistoryActorContext();
            $this->loadModel('LeadHistory');
            $this->LeadHistory->createEntry(array_merge($actor, array(
                'lead_id' => $id,
                'project_id' => (int)$lead['Lead']['project_id'],
                'lead_user_id' => (int)$lead['Lead']['user_id'],
                'entry_type' => 'quotation',
                'title' => 'Digital Quotation Generated',
                'recipient' => !empty($lead['Lead']['lead_mobile']) ? $lead['Lead']['lead_mobile'] : '',
                'message_body' => "Quotation Generated: Total Price Rs. " . number_format($quotTotal),
                'meta_json' => array('total' => $quotTotal)
            )));

            $this->Session->setFlash(__('Quotation has been saved to lead history.'), 'flash', array('alert' => 'success'));
            return $this->redirect(array('action' => 'quotation', $id));
        }

        $propertyList = (array)$this->Property->find('list', array(
            'conditions' => array('Property.project_id' => (int)$lead['Lead']['project_id']),
            'order' => array('Property.name' => 'asc')
        ));

        $this->set('lead', $lead);
        $this->set('propertyList', $propertyList);
        $this->set('settings', $settings);
        $this->set('reportAgency', $this->_leadResolveReportAgency((int)$lead['Lead']['project_id']));
    }

    public function due_followups_check()
    {
        $this->autoRender = false;
        $this->response->type('json');

        $userId = (int)$this->luserId;
        $isGlobalAdmin = $this->_isGlobalAdminUser();

        $conditions = array(
            'Lead.follow_up IS NOT NULL',
            'Lead.follow_up !=' => '0000-00-00 00:00:00'
        );
        if(!$isGlobalAdmin)
        {
            $conditions['Lead.user_id'] = $userId;
        }

        $windowStart = date('Y-m-d H:i:s', strtotime('-15 minutes'));
        $windowEnd = date('Y-m-d H:i:s', strtotime('+15 minutes'));

        $dueConditions = array_merge($conditions, array(
            'Lead.follow_up >=' => $windowStart,
            'Lead.follow_up <=' => $windowEnd
        ));

        $dueLeads = (array)$this->Lead->find('all', array(
            'fields' => array('Lead.id', 'Lead.lead_name', 'Lead.lead_mobile', 'Lead.follow_up', 'Lead.lead_temperature', 'Client.name', 'Client.mobile'),
            'joins' => array(array(
                'table' => 'clients',
                'alias' => 'Client',
                'type' => 'LEFT',
                'conditions' => array('Client.id = Lead.client_id')
            )),
            'conditions' => $dueConditions,
            'order' => array('Lead.follow_up' => 'asc'),
            'limit' => 5,
            'recursive' => -1
        ));

        $leadsPayload = array();
        foreach($dueLeads as $row)
        {
            $name = trim((string)$row['Lead']['lead_name']) !== '' ? $row['Lead']['lead_name'] : (isset($row['Client']['name']) ? $row['Client']['name'] : '');
            $mobile = trim((string)$row['Lead']['lead_mobile']) !== '' ? $row['Lead']['lead_mobile'] : (isset($row['Client']['mobile']) ? $row['Client']['mobile'] : '');
            $fu = !empty($row['Lead']['follow_up']) ? date('h:i A', strtotime($row['Lead']['follow_up'])) : '';
            $diffMins = round((strtotime($row['Lead']['follow_up']) - time()) / 60);

            $leadsPayload[] = array(
                'id' => (int)$row['Lead']['id'],
                'name' => $name !== '' ? $name : 'Lead #'.$row['Lead']['id'],
                'mobile' => $mobile,
                'follow_up_time' => $fu,
                'minutes_away' => $diffMins
            );
        }

        echo json_encode(array(
            'has_due' => !empty($leadsPayload),
            'due_count' => count($leadsPayload),
            'leads' => $leadsPayload
        ));
    }

    public function daily_agenda()
    {
        $this->autoRender = false;
        $this->response->type('json');

        $userId = (int)$this->luserId;
        $isGlobalAdmin = $this->_isGlobalAdminUser();
        $baseCond = array();
        if(!$isGlobalAdmin)
        {
            $baseCond['Lead.user_id'] = $userId;
        }

        $todayStart = date('Y-m-d 00:00:00');
        $todayEnd = date('Y-m-d 23:59:59');

        $todayCount = (int)$this->Lead->find('count', array(
            'conditions' => array_merge($baseCond, array(
                'Lead.follow_up IS NOT NULL',
                'Lead.follow_up !=' => '0000-00-00 00:00:00',
                'Lead.follow_up >=' => $todayStart,
                'Lead.follow_up <=' => $todayEnd
            ))
        ));

        $overdueCount = (int)$this->Lead->find('count', array(
            'conditions' => array_merge($baseCond, array(
                'Lead.follow_up IS NOT NULL',
                'Lead.follow_up !=' => '0000-00-00 00:00:00',
                'Lead.follow_up <' => $todayStart
            ))
        ));

        $overseasCond = $this->_leadPhoneRegionCondition('overseas');
        $overseasCount = $overseasCond ? (int)$this->Lead->find('count', array('conditions' => array_merge($baseCond, array($overseasCond)))) : 0;
        $totalActive = (int)$this->Lead->find('count', array('conditions' => $baseCond));

        $topToday = (array)$this->Lead->find('all', array(
            'fields' => array('Lead.id', 'Lead.lead_name', 'Lead.lead_mobile', 'Lead.follow_up', 'Lead.lead_temperature', 'Client.name', 'Client.mobile'),
            'joins' => array(array(
                'table' => 'clients',
                'alias' => 'Client',
                'type' => 'LEFT',
                'conditions' => array('Client.id = Lead.client_id')
            )),
            'conditions' => array_merge($baseCond, array(
                'Lead.follow_up IS NOT NULL',
                'Lead.follow_up !=' => '0000-00-00 00:00:00',
                'Lead.follow_up >=' => $todayStart,
                'Lead.follow_up <=' => $todayEnd
            )),
            'order' => array('Lead.follow_up' => 'asc'),
            'limit' => 5,
            'recursive' => -1
        ));

        $topList = array();
        foreach($topToday as $t)
        {
            $name = trim((string)$t['Lead']['lead_name']) !== '' ? $t['Lead']['lead_name'] : (isset($t['Client']['name']) ? $t['Client']['name'] : '');
            $mobile = trim((string)$t['Lead']['lead_mobile']) !== '' ? $t['Lead']['lead_mobile'] : (isset($t['Client']['mobile']) ? $t['Client']['mobile'] : '');
            $topList[] = array(
                'id' => (int)$t['Lead']['id'],
                'name' => $name,
                'mobile' => $mobile,
                'follow_up' => date('h:i A', strtotime($t['Lead']['follow_up'])),
                'temp' => isset($t['Lead']['lead_temperature']) ? $t['Lead']['lead_temperature'] : 'warm'
            );
        }

        echo json_encode(array(
            'agent_name' => $this->Session->read('User.name'),
            'today_count' => $todayCount,
            'overdue_count' => $overdueCount,
            'overseas_count' => $overseasCount,
            'total_active' => $totalActive,
            'today_leads' => $topList
        ));
    }

    /**
     * AJAX endpoint to fetch available LeadBot templates & toggle statuses for Leads UI
     */
    public function leadbot_templates_ajax()
    {
        $this->autoRender = false;
        $this->layout = null;
        $this->response->type('json');

        $projectId = !empty($this->agencyId) ? (int)$this->agencyId : 0;
        $userId    = !empty($this->luserId) ? (int)$this->luserId : 0;

        App::uses('LeadbotGatewayClient', 'Lib');
        $GatewayModel = ClassRegistry::init('Admin.LeadbotGatewaySetting');
        $EventModel   = ClassRegistry::init('Admin.LeadbotEventSetting');

        $settings = $GatewayModel->getSettings($projectId, $userId, true);
        $events   = $EventModel->getAgencyEvents($projectId, $userId);

        $templates = array();
        foreach ($events as $k => $ev) {
            $templates[$k] = array(
                'key'               => $k,
                'label'             => $ev['event_label'],
                'whatsapp_enabled'  => !empty($ev['whatsapp_enabled']),
                'sms_enabled'       => !empty($ev['sms_enabled']),
                'whatsapp_template' => !empty($ev['whatsapp_template']) ? $ev['whatsapp_template'] : '',
                'sms_template'      => !empty($ev['sms_template']) ? $ev['sms_template'] : ''
            );
        }

        echo json_encode(array(
            'success'            => true,
            'has_api_key'        => !empty($settings['api_key']),
            'auto_send_whatsapp' => !empty($settings['auto_send_whatsapp']),
            'auto_send_sms'      => !empty($settings['auto_send_sms']),
            'selected_wa_session'=> !empty($settings['selected_wa_session']) ? $settings['selected_wa_session'] : '',
            'selected_sms_device'=> !empty($settings['selected_sms_device_id']) ? ('Device #' . $settings['selected_sms_device_id'] . ' (SIM ' . $settings['selected_sms_sim_slot'] . ')') : '',
            'is_agent_specific'  => !empty($settings['is_agent_specific']),
            'resolved_scope'     => isset($settings['resolved_scope']) ? $settings['resolved_scope'] : 'agency',
            'templates'          => $templates
        ));
    }

    /**
     * 1-Click WhatsApp & Android SMS Dispatch for Leads (respects Agent API key & ON/OFF toggles)
     */
    public function quick_leadbot_dispatch()
    {
        $this->autoRender = false;
        $this->layout = null;
        $this->response->type('json');

        $leadId = !empty($this->request->data['lead_id']) ? (int)$this->request->data['lead_id'] : (!empty($this->request->query['lead_id']) ? (int)$this->request->query['lead_id'] : 0);
        $channel = !empty($this->request->data['channel']) ? strtolower(trim((string)$this->request->data['channel'])) : (!empty($this->request->query['channel']) ? strtolower(trim((string)$this->request->query['channel'])) : 'whatsapp');
        $eventKey = !empty($this->request->data['event_key']) ? trim((string)$this->request->data['event_key']) : (!empty($this->request->query['event_key']) ? trim((string)$this->request->query['event_key']) : 'client_welcome');
        $customMessage = isset($this->request->data['message']) ? trim((string)$this->request->data['message']) : '';

        if ($leadId <= 0) {
            echo json_encode(array('success' => false, 'message' => __('Invalid lead ID.')));
            return;
        }

        $lead = $this->Lead->find('first', array(
            'conditions' => array('Lead.id' => $leadId),
            'contain'    => array('Client', 'Status'),
            'recursive'  => 0
        ));

        if (empty($lead)) {
            echo json_encode(array('success' => false, 'message' => __('Lead not found.')));
            return;
        }

        $leadName = !empty($lead['Lead']['lead_name']) ? $lead['Lead']['lead_name'] : (!empty($lead['Client']['name']) ? $lead['Client']['name'] : 'Lead Client');
        $leadMobile = !empty($lead['Lead']['lead_mobile']) ? $lead['Lead']['lead_mobile'] : (!empty($lead['Client']['mobile']) ? $lead['Client']['mobile'] : '');
        $leadPhoneClean = LeadbotGatewayClient::cleanPhone($leadMobile);

        if ($leadPhoneClean === '') {
            echo json_encode(array('success' => false, 'message' => __('Lead mobile number is missing or invalid.')));
            return;
        }

        $projectId = !empty($lead['Lead']['project_id']) ? (int)$lead['Lead']['project_id'] : (!empty($this->agencyId) ? (int)$this->agencyId : 0);
        $assignedUserId = !empty($lead['Lead']['user_id']) ? (int)$lead['Lead']['user_id'] : (!empty($this->luserId) ? (int)$this->luserId : 0);

        App::uses('LeadbotGatewayClient', 'Lib');
        $GatewayModel = ClassRegistry::init('Admin.LeadbotGatewaySetting');
        $EventModel   = ClassRegistry::init('Admin.LeadbotEventSetting');
        $LogModel     = ClassRegistry::init('Admin.LeadbotNotificationLog');

        // Multi-tier resolution: Agent personal credentials -> Agency -> Global
        $settings = $GatewayModel->getSettings($projectId, $assignedUserId, true);
        if (empty($settings['api_key'])) {
            echo json_encode(array('success' => false, 'message' => __('LeadBot CRM API Key not configured for this agent or agency. Please configure in Gateway Hub.')));
            return;
        }

        // 1. Channel Master Toggle Check
        if ($channel === 'whatsapp' && empty($settings['auto_send_whatsapp'])) {
            echo json_encode(array('success' => false, 'message' => __('WhatsApp messaging is toggled OFF in your LeadBot Gateway settings.')));
            return;
        }
        if ($channel === 'sms' && empty($settings['auto_send_sms'])) {
            echo json_encode(array('success' => false, 'message' => __('SMS messaging is toggled OFF in your LeadBot Gateway settings.')));
            return;
        }

        // 2. Event Toggle Check (if an event key was specified)
        $eventConfig = $EventModel->getEventConfig($eventKey, $projectId, $assignedUserId);
        if (!empty($eventConfig)) {
            if ($channel === 'whatsapp' && empty($eventConfig['whatsapp_enabled']) && $customMessage === '') {
                echo json_encode(array('success' => false, 'message' => __('WhatsApp notification toggle is OFF for "%s" in your Gateway settings.', $eventConfig['event_label'])));
                return;
            }
            if ($channel === 'sms' && empty($eventConfig['sms_enabled']) && $customMessage === '') {
                echo json_encode(array('success' => false, 'message' => __('SMS notification toggle is OFF for "%s" in your Gateway settings.', $eventConfig['event_label'])));
                return;
            }
        }

        // 3. Compile Message
        $agentUser = ClassRegistry::init('User')->find('first', array(
            'conditions' => array('User.id' => $assignedUserId),
            'fields'     => array('User.id', 'User.name', 'User.mobile', 'User.phone'),
            'recursive'  => -1
        ));
        $agentName = !empty($agentUser['User']['name']) ? $agentUser['User']['name'] : 'Agent';
        $agentMobile = !empty($agentUser['User']['mobile']) ? $agentUser['User']['mobile'] : (!empty($agentUser['User']['phone']) ? $agentUser['User']['phone'] : '');

        $contextData = array(
            'client_name'   => $leadName,
            'lead_name'     => $leadName,
            'phone'         => $leadPhoneClean,
            'lead_mobile'   => $leadPhoneClean,
            'agent_name'    => $agentName,
            'agent_mobile'  => $agentMobile,
            'project_name'  => 'White Villas',
            'status'        => !empty($lead['Status']['name']) ? $lead['Status']['name'] : 'Inquiry'
        );

        if ($customMessage !== '') {
            $compiledText = LeadbotGatewayClient::compileTemplate($customMessage, $contextData);
        } elseif (!empty($eventConfig)) {
            $template = ($channel === 'whatsapp') ? (!empty($eventConfig['whatsapp_template']) ? $eventConfig['whatsapp_template'] : 'Notification alert') : (!empty($eventConfig['sms_template']) ? $eventConfig['sms_template'] : 'Notification alert');
            $compiledText = LeadbotGatewayClient::compileTemplate($template, $contextData);
        } else {
            $compiledText = __('Hello %s, thank you for your interest in White Villas. An agent will assist you shortly.', $leadName);
        }

        $gatewayUrl = !empty($settings['gateway_url']) ? $settings['gateway_url'] : LeadbotGatewayClient::DEFAULT_LOCAL_URL;
        $apiKey     = $settings['api_key'];

        if ($channel === 'whatsapp') {
            $waSession = !empty($settings['selected_wa_session']) ? $settings['selected_wa_session'] : '';
            $desc      = !empty($waSession) ? $waSession : 'Default WhatsApp Profile';
            if (!empty($settings['is_agent_specific'])) $desc = 'Agent Key - ' . $desc;

            $result = LeadbotGatewayClient::sendWhatsApp($gatewayUrl, $apiKey, array(
                'recipient' => $leadPhoneClean,
                'message'   => $compiledText,
                'session'   => $waSession,
                'agency_id' => $projectId
            ));

            $isSuccess = !empty($result['success']);
            $errStr    = !empty($result['error']) ? $result['error'] : '';

            $LogModel->recordLog(array(
                'project_id'                => $projectId,
                'user_id'                   => $assignedUserId > 0 ? $assignedUserId : null,
                'channel'                   => 'whatsapp',
                'event_key'                 => $eventKey,
                'recipient_phone'           => $leadPhoneClean,
                'recipient_name'            => $leadName,
                'message'                   => $compiledText,
                'gateway_profile_or_device' => $desc,
                'status'                    => $isSuccess ? 'sent' : 'failed',
                'response_code'             => isset($result['latency_ms']) ? $result['latency_ms'] : 0,
                'response_payload'          => isset($result['raw_response']) ? $result['raw_response'] : '',
                'error_message'             => $errStr,
                'is_test'                   => 0
            ));

            echo json_encode(array(
                'success' => $isSuccess,
                'channel' => 'whatsapp',
                'message' => $isSuccess ? __('WhatsApp sent to %s via profile (%s).', $leadName, $desc) : ($errStr !== '' ? $errStr : __('WhatsApp delivery failed.')),
                'url'     => 'https://wa.me/' . $leadPhoneClean . '?text=' . rawurlencode($compiledText)
            ));
        } else {
            $smsDevId   = !empty($settings['selected_sms_device_id']) ? (int)$settings['selected_sms_device_id'] : null;
            $smsSimSlot = !empty($settings['selected_sms_sim_slot']) ? (int)$settings['selected_sms_sim_slot'] : 1;
            $desc       = !empty($smsDevId) ? ('Device #' . $smsDevId . ' (SIM ' . $smsSimSlot . ')') : 'Default SMS Device';
            if (!empty($settings['is_agent_specific'])) $desc = 'Agent Device - ' . $desc;

            $result = LeadbotGatewayClient::sendSms($gatewayUrl, $apiKey, array(
                'recipient' => $leadPhoneClean,
                'message'   => $compiledText,
                'device_id' => $smsDevId,
                'sim_slot'  => $smsSimSlot,
                'agency_id' => $projectId
            ));

            $isSuccess = !empty($result['success']);
            $errStr    = !empty($result['error']) ? $result['error'] : '';

            $LogModel->recordLog(array(
                'project_id'                => $projectId,
                'user_id'                   => $assignedUserId > 0 ? $assignedUserId : null,
                'channel'                   => 'sms',
                'event_key'                 => $eventKey,
                'recipient_phone'           => $leadPhoneClean,
                'recipient_name'            => $leadName,
                'message'                   => $compiledText,
                'gateway_profile_or_device' => $desc,
                'status'                    => $isSuccess ? 'queued' : 'failed',
                'response_code'             => isset($result['latency_ms']) ? $result['latency_ms'] : 0,
                'response_payload'          => isset($result['raw_response']) ? $result['raw_response'] : '',
                'error_message'             => $errStr,
                'is_test'                   => 0
            ));

            echo json_encode(array(
                'success' => $isSuccess,
                'channel' => 'sms',
                'message' => $isSuccess ? __('SMS queued on Android Gateway (%s) for %s.', $desc, $leadName) : ($errStr !== '' ? $errStr : __('SMS dispatch failed.'))
            ));
        }
    }
}
