<?php
App::uses('AppController', 'Controller');
App::uses('Folder', 'Utility');
App::uses('Cache', 'Cache');
App::uses('Inflector', 'Utility');

class AdminAppController extends AppController
{
    public $helpers = array('Html', 'Form', 'Session', 'Paginator', 'Time', 'Number', 'NumToWord', 'Js' => array('Jquery'), 'MenuBuilder.MenuBuilder');
    public $components = array('Session','Paginator');
    protected $_userMenuCache = array();
    protected $_menuBootstrapVersion = '2026-11-23.transactions.2';
    protected $_menuCacheVersion = '2026-11-23.transactions.2';
    protected $_controllerBootstrapCache = array();
    protected $_assignedAgencyProjectListCache = null;

    protected function _isCeoRoleContext($sessionUser = null)
    {
        $sessionUser = $sessionUser === null ? (array)$this->adminValue : (array)$sessionUser;
        $groupName = strtolower(trim((string)(isset($sessionUser['Ugroup']['name']) ? $sessionUser['Ugroup']['name'] : '')));
        $groupType = strtoupper(trim((string)(isset($sessionUser['Ugroup']['type']) ? $sessionUser['Ugroup']['type'] : '')));
        return ($groupName === 'ceo' || $groupType === 'CEO');
    }

    protected function _ensureCeoPermissionCoverage($sessionUser = null)
    {
        static $isEnsured = false;
        if($isEnsured)
        {
            return true;
        }
        $sessionUser = $sessionUser === null ? (array)$this->adminValue : (array)$sessionUser;
        if(!$this->_isCeoRoleContext($sessionUser) && !$this->_isSuperAdminSession($sessionUser))
        {
            return true;
        }
        try
        {
            $Ugroup = ClassRegistry::init('Ugroup');
            if(!$Ugroup)
            {
                return false;
            }
            $ceoRoleIds = (array)$Ugroup->find('list', array(
                'fields' => array('Ugroup.id','Ugroup.id'),
                'conditions' => array('OR' => array(
                    'LOWER(Ugroup.name)' => 'ceo',
                    'UPPER(Ugroup.type)' => 'CEO'
                )),
                'recursive' => -1
            ));
            foreach(array_keys($ceoRoleIds) as $ceoRoleId)
            {
                $ceoRoleId = (int)$ceoRoleId;
                if($ceoRoleId <= 0 || $ceoRoleId === 1)
                {
                    continue;
                }
                $missingRows = (array)$Ugroup->query(
                    "SELECT COUNT(*) AS missing_count FROM `page_rights` admin_rights ".
                    "LEFT JOIN `page_rights` ceo_rights ON ceo_rights.`page_id`=admin_rights.`page_id` AND ceo_rights.`ugroup_id`=".$ceoRoleId." ".
                    "WHERE admin_rights.`ugroup_id`=1 AND ceo_rights.`id` IS NULL"
                );
                $missingCount = !empty($missingRows[0][0]['missing_count']) ? (int)$missingRows[0][0]['missing_count'] : 0;
                if($missingCount <= 0)
                {
                    continue;
                }
                $Ugroup->query(
                    "INSERT IGNORE INTO `page_rights` (`page_id`,`ugroup_id`,`save_right`,`update_right`,`view_right`,`search_right`) ".
                    "SELECT DISTINCT admin_rights.`page_id`,".$ceoRoleId.",admin_rights.`save_right`,admin_rights.`update_right`,admin_rights.`view_right`,admin_rights.`search_right` ".
                    "FROM `page_rights` admin_rights ".
                    "LEFT JOIN `page_rights` ceo_rights ON ceo_rights.`page_id`=admin_rights.`page_id` AND ceo_rights.`ugroup_id`=".$ceoRoleId." ".
                    "WHERE admin_rights.`ugroup_id`=1 AND ceo_rights.`id` IS NULL"
                );
                $this->_bumpMenuPermissionVersion($ceoRoleId);
            }
            $isEnsured = true;
            return true;
        }
        catch(Throwable $e)
        {
            CakeLog::write('error', 'CEO permission coverage sync failed: '.$e->getMessage());
            return false;
        }
    }

    protected function _ensureLeadDashboardPermissions($sessionUser = null)
    {
        static $isEnsured = false;
        if($isEnsured)
        {
            return true;
        }
        try
        {
            $PageRight = ClassRegistry::init('PageRight');
            $Page = ClassRegistry::init('Page');
            $Ugroup = ClassRegistry::init('Ugroup');
            if(!$PageRight || !$Page || !$Ugroup)
            {
                return false;
            }

            // Target user groups: Agent (ugroup_id = 2) and CRM (ugroup_id = 8)
            $targetGroups = $Ugroup->find('list', array(
                'fields' => array('Ugroup.id', 'Ugroup.name'),
                'conditions' => array(
                    'OR' => array(
                        'Ugroup.id' => array(2, 8),
                        'LOWER(Ugroup.name)' => array('agent', 'crm'),
                        'UPPER(Ugroup.type)' => array('AGT', 'CRM')
                    )
                ),
                'recursive' => -1
            ));

            if(empty($targetGroups))
            {
                $targetGroups = array(2 => 'Agent', 8 => 'CRM');
            }

            // Target lead pages: Page 5 (parent Leads CRM), 125 (CRM Dashboard), 126 (Kanban Board), 127 (Add Lead), 130 (Manage Leads)
            $leadPages = $Page->find('list', array(
                'fields' => array('Page.id', 'Page.id'),
                'conditions' => array(
                    'OR' => array(
                        'Page.id' => array(5, 125, 126, 127, 130),
                        array(
                            'LOWER(Page.controller_name)' => 'leads',
                            'LOWER(Page.action_name)' => array('dashboard', 'kanban', 'leadadd', 'manage')
                        )
                    )
                ),
                'recursive' => -1
            ));

            if(empty($leadPages))
            {
                $leadPages = array(5 => 5, 125 => 125, 126 => 126, 127 => 127, 130 => 130);
            }

            foreach(array_keys($targetGroups) as $ugroupId)
            {
                $ugroupId = (int)$ugroupId;
                if($ugroupId <= 0)
                {
                    continue;
                }
                $bumpNeeded = false;
                foreach(array_keys($leadPages) as $pageId)
                {
                    $pageId = (int)$pageId;
                    if($pageId <= 0)
                    {
                        continue;
                    }
                    $existingRight = $PageRight->find('first', array(
                        'fields' => array('PageRight.id', 'PageRight.view_right', 'PageRight.save_right', 'PageRight.update_right', 'PageRight.search_right'),
                        'conditions' => array(
                            'PageRight.page_id' => $pageId,
                            'PageRight.ugroup_id' => $ugroupId
                        ),
                        'recursive' => -1
                    ));

                    if(!empty($existingRight['PageRight']['id']))
                    {
                        $pr = $existingRight['PageRight'];
                        if(empty($pr['view_right']) || empty($pr['save_right']) || empty($pr['update_right']))
                        {
                            $PageRight->id = (int)$pr['id'];
                            $PageRight->save(array('PageRight' => array(
                                'id' => (int)$pr['id'],
                                'view_right' => 1,
                                'save_right' => 1,
                                'update_right' => 1,
                                'search_right' => ($ugroupId === 8 ? 1 : (isset($pr['search_right']) ? (int)$pr['search_right'] : 0))
                            )), array('validate' => false));
                            $bumpNeeded = true;
                        }
                    }
                    else
                    {
                        $PageRight->create();
                        $PageRight->save(array('PageRight' => array(
                            'page_id' => $pageId,
                            'ugroup_id' => $ugroupId,
                            'view_right' => 1,
                            'save_right' => 1,
                            'update_right' => 1,
                            'search_right' => ($ugroupId === 8 ? 1 : 0)
                        )), array('validate' => false));
                        $bumpNeeded = true;
                    }
                }
                if($bumpNeeded)
                {
                    $this->_bumpMenuPermissionVersion($ugroupId);
                }
            }
            $isEnsured = true;
            return true;
        }
        catch(Throwable $e)
        {
            CakeLog::write('error', 'Ensure Lead dashboard permissions failed: '.$e->getMessage());
            return false;
        }
    }

    public function _ensureNotificationSchema()
    {
        static $schemaDone = false;
        if($schemaDone)
        {
            return true;
        }
        try
        {
            App::uses('ConnectionManager', 'Model');
            $db = ConnectionManager::getDataSource('default');
            if(!$db)
            {
                return false;
            }

            // 1. user_notifications
            $db->rawQuery("CREATE TABLE IF NOT EXISTS `user_notifications` (
              `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
              `user_id` int(11) NOT NULL,
              `notif_key` varchar(120) DEFAULT NULL,
              `title` varchar(255) NOT NULL,
              `message` text NOT NULL,
              `type` varchar(50) NOT NULL DEFAULT 'general',
              `priority` enum('low','normal','high','urgent') NOT NULL DEFAULT 'normal',
              `icon` varchar(80) NOT NULL DEFAULT 'fa-bell',
              `action_url` varchar(255) DEFAULT NULL,
              `is_read` tinyint(1) NOT NULL DEFAULT '0',
              `read_at` datetime DEFAULT NULL,
              `created` datetime NOT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `uniq_user_key` (`user_id`,`notif_key`),
              KEY `idx_user_read_id` (`user_id`,`is_read`,`id`),
              KEY `idx_type_created` (`type`,`created`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

            // 2. notification_broadcasts
            $db->rawQuery("CREATE TABLE IF NOT EXISTS `notification_broadcasts` (
              `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
              `sender_id` int(11) NOT NULL,
              `target_type` enum('all','roles','users') NOT NULL DEFAULT 'all',
              `target_payload` text,
              `title` varchar(255) NOT NULL,
              `message` text NOT NULL,
              `type` varchar(50) NOT NULL DEFAULT 'broadcast',
              `priority` enum('low','normal','high','urgent') NOT NULL DEFAULT 'normal',
              `icon` varchar(80) NOT NULL DEFAULT 'fa-bullhorn',
              `action_url` varchar(255) DEFAULT NULL,
              `total_recipients` int(11) NOT NULL DEFAULT '0',
              `created` datetime NOT NULL,
              PRIMARY KEY (`id`),
              KEY `idx_sender_created` (`sender_id`,`created`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

            // 3. notification_settings
            $db->rawQuery("CREATE TABLE IF NOT EXISTS `notification_settings` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `setting_key` varchar(80) NOT NULL,
              `setting_value` text,
              `created` datetime DEFAULT NULL,
              `modified` datetime DEFAULT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `uniq_setting_key` (`setting_key`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

            // 4. Seed default settings
            $now = date('Y-m-d H:i:s');
            $db->rawQuery("INSERT IGNORE INTO `notification_settings` (`setting_key`, `setting_value`, `created`, `modified`) VALUES
            ('enable_lead_assignment', '1', '{$now}', '{$now}'),
            ('enable_facebook_sync', '1', '{$now}', '{$now}'),
            ('enable_followup_reminder', '1', '{$now}', '{$now}'),
            ('followup_reminder_minutes', '30', '{$now}', '{$now}'),
            ('enable_overdue_alert', '1', '{$now}', '{$now}'),
            ('enable_deal_closed', '1', '{$now}', '{$now}'),
            ('enable_expense_approval', '1', '{$now}', '{$now}'),
            ('enable_payment_received', '1', '{$now}', '{$now}'),
            ('whatsapp_demand_pdf_attachment', '1', '{$now}', '{$now}'),
            ('enable_admin_broadcast', '1', '{$now}', '{$now}'),
            ('enable_sound_chime', '1', '{$now}', '{$now}'),
            ('enable_browser_push', '1', '{$now}', '{$now}');");

            $schemaDone = true;
            return true;
        }
        catch(Throwable $e)
        {
            return false;
        }
    }

    public function _ensureNotificationsMenu()
    {
        try
        {
            $Page = ClassRegistry::init('Page');
            $PageRight = ClassRegistry::init('PageRight');
            $Ugroup = ClassRegistry::init('Ugroup');
            if(!$Page || !$PageRight || !$Ugroup)
            {
                return;
            }

            // 1. Locate or create root 'Notifications' menu with parent_id = 0
            $root = (array)$Page->find('first', array(
                'fields' => array('Page.id', 'Page.page_name'),
                'conditions' => array(
                    'Page.parent_id' => 0,
                    'OR' => array(
                        array('LOWER(Page.page_name)' => 'notifications'),
                        array(
                            'LOWER(Page.controller_name)' => 'notifications',
                            'Page.parent_id' => 0
                        )
                    )
                ),
                'order' => array('Page.id' => 'asc'),
                'recursive' => -1
            ));

            $rootData = array(
                'model_name' => 'Notifications',
                'page_name' => 'Notifications',
                'controller_name' => '',
                'action_name' => '',
                'icon' => 'fa fa-bell',
                'parent_id' => 0,
                'ordering' => 38,
                'published' => 'Yes',
                'sel_name' => 'notifications'
            );
            if(!empty($root['Page']['id']))
            {
                $rootData['id'] = (int)$root['Page']['id'];
            }
            $Page->create();
            $Page->save(array('Page' => $rootData), array('validate' => false));
            $rootId = !empty($rootData['id']) ? (int)$rootData['id'] : (int)$Page->id;
            if($rootId <= 0)
            {
                return;
            }

            // 2. Child pages under root Notifications
            $childPages = array(
                array(
                    'page_name' => 'Notifications Inbox',
                    'action_name' => 'index',
                    'icon' => 'fa fa-inbox',
                    'ordering' => 1,
                    'published' => 'Yes',
                    'grant_all' => true
                ),
                array(
                    'page_name' => 'Send Custom Broadcast',
                    'action_name' => 'broadcast',
                    'icon' => 'fa fa-bullhorn',
                    'ordering' => 2,
                    'published' => 'Yes',
                    'grant_all' => false
                ),
                array(
                    'page_name' => 'Broadcast Logs',
                    'action_name' => 'broadcast_history',
                    'icon' => 'fa fa-history',
                    'ordering' => 3,
                    'published' => 'Yes',
                    'grant_all' => false
                ),
                array(
                    'page_name' => 'Notification Settings',
                    'action_name' => 'settings',
                    'icon' => 'fa fa-sliders',
                    'ordering' => 4,
                    'published' => 'Yes',
                    'grant_all' => false
                ),
                array('page_name' => 'Poll Notifications', 'action_name' => 'poll', 'icon' => 'fa fa-refresh', 'ordering' => 5, 'published' => 'No', 'grant_all' => true),
                array('page_name' => 'Mark Read Notification', 'action_name' => 'mark_read', 'icon' => 'fa fa-check', 'ordering' => 6, 'published' => 'No', 'grant_all' => true),
                array('page_name' => 'Mark All Read Notifications', 'action_name' => 'mark_all_read', 'icon' => 'fa fa-check-circle', 'ordering' => 7, 'published' => 'No', 'grant_all' => true),
                array('page_name' => 'Cron Notifications', 'action_name' => 'cron_reminders', 'icon' => 'fa fa-clock-o', 'ordering' => 8, 'published' => 'No', 'grant_all' => true)
            );

            $pageIdsByAction = array();

            foreach($childPages as $cp)
            {
                $actLower = strtolower($cp['action_name']);
                $existingPage = (array)$Page->find('first', array(
                    'fields' => array('Page.id'),
                    'conditions' => array(
                        'LOWER(Page.controller_name)' => 'notifications',
                        'LOWER(Page.action_name)' => $actLower,
                        'Page.id !=' => $rootId
                    ),
                    'order' => array('Page.id' => 'asc'),
                    'recursive' => -1
                ));

                $saveChild = array(
                    'model_name' => 'Notifications',
                    'page_name' => $cp['page_name'],
                    'controller_name' => 'Notifications',
                    'action_name' => $cp['action_name'],
                    'icon' => $cp['icon'],
                    'parent_id' => $rootId,
                    'ordering' => $cp['ordering'],
                    'published' => $cp['published'],
                    'sel_name' => 'notifications'
                );
                if(!empty($existingPage['Page']['id']))
                {
                    $saveChild['id'] = (int)$existingPage['Page']['id'];
                }
                $Page->create();
                $Page->save(array('Page' => $saveChild), array('validate' => false));
                $pageId = !empty($saveChild['id']) ? (int)$saveChild['id'] : (int)$Page->id;
                if($pageId > 0)
                {
                    $pageIdsByAction[$actLower] = array('id' => $pageId, 'grant_all' => $cp['grant_all']);
                }
            }

            // 3. Child page under Configurations (parent_id = 7)
            $configRoot = (array)$Page->find('first', array(
                'fields' => array('Page.id'),
                'conditions' => array(
                    'Page.parent_id' => 0,
                    'OR' => array(
                        array('Page.id' => 7),
                        array('LOWER(Page.page_name)' => 'configurations'),
                        array('LOWER(Page.model_name)' => 'configuration'),
                        array('LOWER(Page.controller_name)' => 'configurations')
                    )
                ),
                'order' => array('Page.id' => 'asc'),
                'recursive' => -1
            ));
            $configRootId = !empty($configRoot['Page']['id']) ? (int)$configRoot['Page']['id'] : 7;
            if($configRootId > 0)
            {
                $this->_ensureSimpleChildAdminPage(array(
                    'parent_id' => $configRootId,
                    'parent_page_name' => 'Configurations',
                    'model_name' => 'Notification Settings',
                    'page_name' => 'Notification Settings',
                    'controller_name' => 'Notifications',
                    'action_name' => 'settings',
                    'icon' => 'fa fa-bell',
                    'ordering' => 19,
                    'published' => 'Yes',
                    'sel_name' => 'notifications,configurations',
                    'grant_group_ids' => array(1)
                ));
            }

            // 3b. Child pages for LeadBot WhatsApp & SMS Gateway under root Notifications
            $this->_ensureSimpleChildAdminPage(array(
                'parent_id' => $rootId,
                'parent_page_name' => 'Notifications',
                'model_name' => 'LeadbotGateways',
                'page_name' => 'WhatsApp & SMS Gateway',
                'controller_name' => 'LeadbotGateways',
                'action_name' => 'index',
                'icon' => 'fa fa-whatsapp',
                'ordering' => 5,
                'published' => 'Yes',
                'sel_name' => 'notifications,leadbot_gateways',
                'grant_group_ids' => array(1, 2, 4)
            ));
            $this->_ensureSimpleChildAdminPage(array(
                'parent_id' => $rootId,
                'parent_page_name' => 'Notifications',
                'model_name' => 'LeadbotGateways',
                'page_name' => 'Gateway Logs',
                'controller_name' => 'LeadbotGateways',
                'action_name' => 'logs',
                'icon' => 'fa fa-history',
                'ordering' => 6,
                'published' => 'Yes',
                'sel_name' => 'notifications,leadbot_gateways',
                'grant_group_ids' => array(1, 2, 4)
            ));

            // 4. Seed page rights across all user groups
            $groups = (array)$Ugroup->find('all', array(
                'fields' => array('Ugroup.id', 'Ugroup.type', 'Ugroup.name'),
                'recursive' => -1
            ));

            foreach($groups as $groupRow)
            {
                $group = !empty($groupRow['Ugroup']) ? (array)$groupRow['Ugroup'] : array();
                $groupId = !empty($group['id']) ? (int)$group['id'] : 0;
                if($groupId <= 0) continue;

                $type = strtoupper(trim((string)(isset($group['type']) ? $group['type'] : '')));
                $name = strtoupper(trim((string)(isset($group['name']) ? $group['name'] : '')));
                $isSuperOrAdmin = ($groupId === 1 || in_array($type, array('ADR', 'AGY', 'ADMIN', 'SUP', 'SAD'), true) || preg_match('/SUPER\s*ADMIN|ADMINISTRATOR|\bADMIN\b|\bCEO\b|\bDIRECTOR\b/i', $name));
                $isManager = ($isSuperOrAdmin || preg_match('/\b(MANAGER)\b/i', $name));

                // Root menu rights: Everyone gets view_right = 1
                $PageRight->query("INSERT INTO `page_rights` (`page_id`, `ugroup_id`, `save_right`, `update_right`, `view_right`, `search_right`) 
                                   VALUES (".(int)$rootId.", ".(int)$groupId.", 1, 1, 1, 1) 
                                   ON DUPLICATE KEY UPDATE `view_right` = 1");

                foreach($pageIdsByAction as $actKey => $info)
                {
                    $pId = (int)$info['id'];
                    $grantAll = !empty($info['grant_all']);

                    if($isSuperOrAdmin) {
                        $s = 1; $u = 1; $v = 1; $d = 1;
                    } elseif($isManager && in_array($actKey, array('index', 'broadcast', 'broadcast_history', 'poll', 'mark_read', 'mark_all_read'), true)) {
                        $s = 1; $u = 1; $v = 1; $d = 1;
                    } elseif($grantAll) {
                        $s = 1; $u = 1; $v = 1; $d = 0;
                    } else {
                        $s = 0; $u = 0; $v = 0; $d = 0;
                    }

                    $PageRight->query("INSERT INTO `page_rights` (`page_id`, `ugroup_id`, `save_right`, `update_right`, `view_right`, `search_right`) 
                                       VALUES (".$pId.", ".(int)$groupId.", ".$s.", ".$u.", ".$v.", ".$d.") 
                                       ON DUPLICATE KEY UPDATE `save_right` = ".$s.", `update_right` = ".$u.", `view_right` = ".$v.", `search_right` = ".$d);
                }
            }

            // Invalidate menu caches
            $this->_bumpMenuPermissionVersion(1);
        }
        catch(Throwable $e)
        {
            // Silent fallback
        }
    }

    protected function _ensureNotificationPermissions($sessionUser = null)
    {
        static $isEnsured = false;
        if($isEnsured)
        {
            return true;
        }
        $this->_ensureNotificationSchema();
        $this->_ensureNotificationsMenu();
        $isEnsured = true;
        return true;
    }

    public function sendNotification($userId, $title, $message, $type = 'general', $actionUrl = '', $icon = 'fa-bell', $priority = 'normal', $dedupKey = null)
    {
        $userId = (int)$userId;
        if($userId <= 0 || trim((string)$title) === '')
        {
            return false;
        }

        try
        {
            $this->loadModel('Admin.NotificationSetting');
            $toggleMap = array(
                'lead_assignment' => 'enable_lead_assignment',
                'lead_assigned' => 'enable_lead_assignment',
                'facebook_sync' => 'enable_facebook_sync',
                'followup_due' => 'enable_followup_reminder',
                'followup_overdue' => 'enable_overdue_alert',
                'deal_closed' => 'enable_deal_closed',
                'expense_approval' => 'enable_expense_approval',
                'payment_received' => 'enable_payment_received',
                'broadcast' => 'enable_admin_broadcast'
            );
            $normalizedType = strtolower(trim((string)$type));
            if(isset($toggleMap[$normalizedType]))
            {
                $settingKey = $toggleMap[$normalizedType];
                if(!$this->NotificationSetting->isEnabled($settingKey, true))
                {
                    return false; // Suppressed by Admin toggle
                }
            }
        }
        catch(Exception $toggleEx)
        {
            // Proceed safely if setting model fails
        }

        try
        {
            $this->loadModel('Admin.UserNotification');
            $this->UserNotification->create();
            $saved = (bool)$this->UserNotification->save(array(
                'user_id' => $userId,
                'notif_key' => $dedupKey,
                'title' => trim($title),
                'message' => trim($message),
                'type' => !empty($normalizedType) ? $normalizedType : 'general',
                'priority' => in_array($priority, array('low', 'normal', 'high', 'urgent'), true) ? $priority : 'normal',
                'icon' => !empty($icon) ? $icon : 'fa-bell',
                'action_url' => $actionUrl,
                'is_read' => 0,
                'created' => date('Y-m-d H:i:s')
            ));

            if ($saved)
            {
                try
                {
                    $this->_dispatchLeadbotNotification($userId, $title, $message, $normalizedType);
                }
                catch(Exception $eGateway)
                {
                    // Fail-safe: Gateway delivery error must never break in-app notification flow
                }
            }

            return $saved;
        }
        catch(Throwable $e)
        {
            return false;
        }
    }

    /**
     * Automated WhatsApp & Android SMS Gateway Dispatch Hook for White Villas CRM Events
     */
    protected function _dispatchLeadbotNotification($userId, $title, $message, $type = 'general')
    {
        $userId = (int)$userId;
        if($userId <= 0) return false;

        App::uses('LeadbotGatewayClient', 'Lib');
        $User = ClassRegistry::init('User');
        $u = (array)$User->find('first', array(
            'conditions' => array('User.id' => $userId),
            'fields' => array('User.id', 'User.name', 'User.phone', 'User.mobile', 'User.project_id'),
            'recursive' => -1
        ));

        $projectId = !empty($u['User']['project_id']) ? (int)$u['User']['project_id'] : (!empty($this->agencyId) ? (int)$this->agencyId : 0);
        $agentName = !empty($u['User']['name']) ? $u['User']['name'] : 'Agent';
        $agentMobile = !empty($u['User']['mobile']) ? $u['User']['mobile'] : (!empty($u['User']['phone']) ? $u['User']['phone'] : '');

        $eventMap = array(
            'lead_assignment'        => 'lead_assignment',
            'lead_assigned'          => 'lead_assignment',
            'facebook_sync'          => 'facebook_sync',
            'followup_due'           => 'followup_reminder',
            'followup_reminder'      => 'followup_reminder',
            'followup_overdue'       => 'overdue_alert',
            'deal_closed'            => 'deal_closed',
            'expense_approval'       => 'expense_approval',
            'payment_received'       => 'payment_received',
            'payment_posted'         => 'payment_posted',
            'installment_reminder'   => 'installment_reminder',
            'installment_overdue'    => 'installment_overdue',
            'site_visit_scheduled'   => 'site_visit_scheduled',
            'deal_transfer'          => 'deal_transfer',
            'balloting_result'       => 'balloting_result',
            'letter_issued'          => 'letter_issued',
            'construction_milestone' => 'construction_milestone',
            'refund_processed'       => 'refund_processed',
            'contact_registered'     => 'contact_registered',
            'client_registration'    => 'contact_registered',
            'client_login_credentials' => 'contact_registered',
            'broadcast'              => 'broadcast'
        );
        $eventKey = isset($eventMap[$type]) ? $eventMap[$type] : $type;

        $GatewayModel = ClassRegistry::init('Admin.LeadbotGatewaySetting');
        $EventModel   = ClassRegistry::init('Admin.LeadbotEventSetting');
        $LogModel     = ClassRegistry::init('Admin.LeadbotNotificationLog');

        // Multi-tier resolution: Agent personal settings -> Agency settings -> Global
        $settings = $GatewayModel->getSettings($projectId, $userId, true);
        if(empty($settings['api_key'])) return false;

        $eventConfig = $EventModel->getEventConfig($eventKey, $projectId, $userId);
        if(empty($eventConfig)) return false;

        $waActive  = !empty($settings['auto_send_whatsapp']) && !empty($eventConfig['whatsapp_enabled']);
        $smsActive = !empty($settings['auto_send_sms']) && !empty($eventConfig['sms_enabled']);
        if(!$waActive && !$smsActive) return false;

        $gatewayUrl = !empty($settings['gateway_url']) ? $settings['gateway_url'] : LeadbotGatewayClient::DEFAULT_LOCAL_URL;
        $apiKey     = $settings['api_key'];
        $waSession  = !empty($settings['selected_wa_session']) ? $settings['selected_wa_session'] : '';
        $smsDevId   = !empty($settings['selected_sms_device_id']) ? (int)$settings['selected_sms_device_id'] : null;
        $smsSimSlot = !empty($settings['selected_sms_sim_slot']) ? (int)$settings['selected_sms_sim_slot'] : 1;

        $contextData = array(
            'agent_name'   => $agentName,
            'phone'        => $agentMobile,
            'client_name'  => 'Valued Client',
            'title'        => $title,
            'message'      => $message,
            'remarks'      => $message,
            'project_name' => 'White Villas'
        );

        $targetPhone = !empty($agentMobile) ? LeadbotGatewayClient::cleanPhone($agentMobile) : (!empty($settings['admin_mobile']) ? LeadbotGatewayClient::cleanPhone($settings['admin_mobile']) : '');
        if(empty($targetPhone)) return false;

        $waDesc = !empty($waSession) ? $waSession : 'Default WhatsApp Profile';
        $smsDesc = !empty($smsDevId) ? ('Device #' . $smsDevId . ' (SIM ' . $smsSimSlot . ')') : 'Default SMS Device';
        if(!empty($settings['is_agent_specific'])) {
            $waDesc = 'Agent Key - ' . $waDesc;
            $smsDesc = 'Agent Device - ' . $smsDesc;
        }

        if($waActive)
        {
            $waTpl = !empty($eventConfig['whatsapp_template']) ? $eventConfig['whatsapp_template'] : $message;
            $compiledWa = LeadbotGatewayClient::compileTemplate($waTpl, $contextData);
            $resWa = LeadbotGatewayClient::sendWhatsApp($gatewayUrl, $apiKey, array(
                'recipient' => $targetPhone,
                'message'   => $compiledWa,
                'session'   => $waSession,
                'agency_id' => $projectId
            ));

            $LogModel->recordLog(array(
                'project_id'                => $projectId,
                'user_id'                   => $userId,
                'channel'                   => 'whatsapp',
                'event_key'                 => $eventKey,
                'recipient_phone'           => $targetPhone,
                'recipient_name'            => $agentName,
                'message'                   => $compiledWa,
                'gateway_profile_or_device' => $waDesc,
                'status'                    => !empty($resWa['success']) ? 'sent' : 'failed',
                'response_code'             => isset($resWa['latency_ms']) ? $resWa['latency_ms'] : 0,
                'response_payload'          => isset($resWa['raw_response']) ? $resWa['raw_response'] : '',
                'error_message'             => !empty($resWa['error']) ? $resWa['error'] : null,
                'is_test'                   => 0
            ));
        }

        if($smsActive)
        {
            $smsTpl = !empty($eventConfig['sms_template']) ? $eventConfig['sms_template'] : $message;
            $compiledSms = LeadbotGatewayClient::compileTemplate($smsTpl, $contextData);
            $resSms = LeadbotGatewayClient::sendSms($gatewayUrl, $apiKey, array(
                'recipient' => $targetPhone,
                'message'   => $compiledSms,
                'device_id' => $smsDevId,
                'sim_slot'  => $smsSimSlot,
                'agency_id' => $projectId
            ));

            $LogModel->recordLog(array(
                'project_id'                => $projectId,
                'user_id'                   => $userId,
                'channel'                   => 'sms',
                'event_key'                 => $eventKey,
                'recipient_phone'           => $targetPhone,
                'recipient_name'            => $agentName,
                'message'                   => $compiledSms,
                'gateway_profile_or_device' => $smsDesc,
                'status'                    => !empty($resSms['success']) ? 'queued' : 'failed',
                'response_code'             => isset($resSms['latency_ms']) ? $resSms['latency_ms'] : 0,
                'response_payload'          => isset($resSms['raw_response']) ? $resSms['raw_response'] : '',
                'error_message'             => !empty($resSms['error']) ? $resSms['error'] : null,
                'is_test'                   => 0
            ));
        }

        return true;
    }

    /**
     * Dispatch Contact / Client Registration Welcome Notification
     * Automatically dispatches WhatsApp, SMS, and HTML Email according to Agency Branding and event toggles.
     *
     * @param int $contactId
     * @param string $plainPassword
     * @param array $contactData
     * @return array Array of delivery statuses: array('whatsapp' => bool, 'sms' => bool, 'email' => bool)
     */
    public function _dispatchContactRegistrationWelcome($contactId, $plainPassword, $contactData)
    {
        $contactId = (int)$contactId;
        $plainPassword = (string)$plainPassword;
        if ($contactId <= 0 || $plainPassword === '') {
            return array('whatsapp' => false, 'sms' => false, 'email' => false);
        }

        $clientName = !empty($contactData['name']) ? trim((string)$contactData['name']) : 'Valued Client';
        $email      = !empty($contactData['email']) ? trim((string)$contactData['email']) : '';
        $mobileNo   = !empty($contactData['phone']) ? trim((string)$contactData['phone']) : (!empty($contactData['mobile']) ? trim((string)$contactData['mobile']) : '');
        $mobileNo   = preg_replace('/\s+/', '', (string)$mobileNo);

        $projectId = !empty($contactData['project_id']) ? (int)$contactData['project_id'] : (!empty($this->agencyId) ? (int)$this->agencyId : 0);
        $userId    = !empty($contactData['user_id']) ? (int)$contactData['user_id'] : (!empty($this->luserId) ? (int)$this->luserId : 0);

        // 1. Resolve Agency / Project Details
        $this->loadModel('Project');
        $project = $this->Project->find('first', array(
            'conditions' => array('Project.id' => $projectId),
            'recursive'  => -1
        ));
        $agencyName  = !empty($project['Project']['name']) ? $project['Project']['name'] : (!empty($this->siteName) ? $this->siteName : 'White Villas');
        $agencyPhone = !empty($project['Project']['mobile']) ? $project['Project']['mobile'] : (!empty($project['Project']['phone']) ? $project['Project']['phone'] : (isset($this->contact) ? $this->contact : ''));
        $agencyEmail = !empty($project['Project']['email']) ? $project['Project']['email'] : (isset($this->siteEmail) ? $this->siteEmail : '');

        // 2. Resolve Agent Details
        $agentUser = ClassRegistry::init('User')->find('first', array(
            'conditions' => array('User.id' => $userId),
            'fields'     => array('User.id', 'User.name', 'User.mobile', 'User.phone'),
            'recursive'  => -1
        ));
        $agentName   = !empty($agentUser['User']['name']) ? $agentUser['User']['name'] : 'Property Advisor';
        $agentMobile = !empty($agentUser['User']['mobile']) ? $agentUser['User']['mobile'] : (!empty($agentUser['User']['phone']) ? $agentUser['User']['phone'] : '');

        // 3. Client Portal Login URL
        $portalLoginUrl = Router::url(array('plugin' => false, 'controller' => 'Clients', 'action' => 'login'), true);

        // 4. Compile Context Data
        $context = array(
            'client_name'    => $clientName,
            'name'           => $clientName,
            'email'          => $email,
            'password'       => $plainPassword,
            'plain_password' => $plainPassword,
            'login_url'      => $portalLoginUrl,
            'url'            => $portalLoginUrl,
            'phone'          => $mobileNo,
            'mobile'         => $mobileNo,
            'client_mobile'  => $mobileNo,
            'agency_name'    => $agencyName,
            'project_name'   => $agencyName,
            'site_name'      => $agencyName,
            'agency_phone'   => $agencyPhone,
            'agency_email'   => $agencyEmail,
            'agent_name'     => $agentName,
            'agent_mobile'   => $agentMobile,
            'user_id'        => $userId,
            'project_id'     => $projectId
        );

        $results = array(
            'whatsapp' => false,
            'sms'      => false,
            'email'    => false
        );

        // 5. Automated WhatsApp & SMS Dispatch via LeadBot Gateway
        try {
            App::uses('LeadbotGatewayComponent', 'Controller/Component');
            $collection = new ComponentCollection();
            $leadbotComp = new LeadbotGatewayComponent($collection);
            $leadbotComp->initialize($this);

            $dispatchRes = $leadbotComp->dispatch('contact_registered', $context, $projectId, $userId);
            if (!empty($dispatchRes['success'])) {
                $results['whatsapp'] = !empty($dispatchRes['dispatched']['whatsapp']);
                $results['sms']      = !empty($dispatchRes['dispatched']['sms']);
            }
        } catch (Exception $eGateway) {
            CakeLog::write('error', 'LeadBot Gateway dispatch failed for contact #' . $contactId . ': ' . $eGateway->getMessage());
        }

        // Fallback for legacy SMS if LeadBot SMS was not dispatched and legacy is enabled
        if (empty($results['sms']) && $mobileNo !== '' && !empty($this->smsNotification) && !empty($this->smsSettingArr['Smssetting']['api'])) {
            try {
                $legacyMsg = sprintf(__('Dear %s, your %s customer account is active. Email: %s Password: %s Login at: %s'), $clientName, $agencyName, $email, $plainPassword, $portalLoginUrl);
                if (isset($this->CustomFunction) && method_exists($this->CustomFunction, 'sendSms')) {
                    $this->CustomFunction->sendSms($mobileNo, $legacyMsg, $this->smsSettingArr);
                    $results['sms'] = true;
                }
            } catch (Exception $eSms) {}
        }

        // 6. Automated Professional HTML Email Dispatch according to Agency Branding
        if ($email !== '' && !empty($this->emailNotification)) {
            try {
                App::uses('CakeEmail', 'Network/Email');
                $emailSubject = sprintf(__('Welcome to %s — Your Client Portal Login Credentials'), $agencyName);
                $htmlBody     = $this->_buildProfessionalContactEmailHtml($context);

                $Email = new CakeEmail();
                if (!empty($this->emailSettype)) {
                    $Email->transport($this->emailSettype);
                }
                if (isset($this->emailSettype) && strtolower((string)$this->emailSettype) === 'smtp') {
                    $Email->config(array(
                        'host'     => $this->emailHost,
                        'port'     => $this->emailPort,
                        'username' => $this->emailUsername,
                        'password' => $this->emailPassword,
                        'timeout'  => 90
                    ));
                }
                $fromEmail = !empty($agencyEmail) ? $agencyEmail : (!empty($this->siteEmail) ? $this->siteEmail : 'no-reply@leadbotcrm.com');
                $Email->from(array($fromEmail => $agencyName));
                $Email->to($email);
                $Email->emailFormat('html');
                $Email->subject($emailSubject);
                $results['email'] = (bool)$Email->send($htmlBody);
            } catch (Exception $emailException) {
                CakeLog::write('error', 'Contact welcome email send failed: ' . $emailException->getMessage());
            }
        }

        return $results;
    }

    /**
     * Build an ultra-professional, responsive HTML welcome email matching the agency name & branding
     */
    public function _buildProfessionalContactEmailHtml(array $context)
    {
        $agencyName   = htmlspecialchars(isset($context['agency_name']) ? $context['agency_name'] : 'White Villas', ENT_QUOTES, 'UTF-8');
        $clientName   = htmlspecialchars(isset($context['client_name']) ? $context['client_name'] : 'Valued Client', ENT_QUOTES, 'UTF-8');
        $email        = htmlspecialchars(isset($context['email']) ? $context['email'] : '', ENT_QUOTES, 'UTF-8');
        $password     = htmlspecialchars(isset($context['password']) ? $context['password'] : '', ENT_QUOTES, 'UTF-8');
        $loginUrl     = htmlspecialchars(isset($context['login_url']) ? $context['login_url'] : '', ENT_QUOTES, 'UTF-8');
        $agencyPhone  = htmlspecialchars(isset($context['agency_phone']) ? $context['agency_phone'] : '', ENT_QUOTES, 'UTF-8');
        $agencyEmail  = htmlspecialchars(isset($context['agency_email']) ? $context['agency_email'] : '', ENT_QUOTES, 'UTF-8');
        $agentName    = htmlspecialchars(isset($context['agent_name']) ? $context['agent_name'] : 'Property Advisor', ENT_QUOTES, 'UTF-8');
        $agentMobile  = htmlspecialchars(isset($context['agent_mobile']) ? $context['agent_mobile'] : '', ENT_QUOTES, 'UTF-8');
        $year         = date('Y');

        return '<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Welcome to ' . $agencyName . '</title>
</head>
<body style="margin:0;padding:0;background-color:#f1f5f9;font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif;">
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color:#f1f5f9;padding:32px 16px;">
  <tr>
    <td align="center">
      <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width:600px;background-color:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 10px 25px rgba(15,23,42,0.08);border:1px solid #e2e8f0;">
        <!-- Header -->
        <tr>
          <td style="background:linear-gradient(135deg, #0f172a 0%, #1e293b 100%);padding:36px 30px;text-align:center;">
            <div style="font-size:24px;font-weight:800;color:#ffffff;letter-spacing:-0.5px;text-transform:uppercase;margin-bottom:6px;">
              ' . $agencyName . '
            </div>
            <div style="font-size:12px;color:#94a3b8;font-weight:700;letter-spacing:1px;text-transform:uppercase;">
              Official Client Portal Registration
            </div>
          </td>
        </tr>
        <!-- Content -->
        <tr>
          <td style="padding:32px 28px;color:#334155;line-height:1.6;">
            <h2 style="margin:0 0 16px 0;font-size:20px;font-weight:800;color:#0f172a;">
              Welcome, ' . $clientName . '!
            </h2>
            <p style="margin:0 0 18px 0;font-size:14.5px;color:#475569;">
              Congratulations! Your customer account with <strong>' . $agencyName . '</strong> has been activated. You can now access your property documents, review installment payment plans, and download official receipts online anytime.
            </p>

            <!-- Credentials Box -->
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color:#f8fafc;border:2px solid #e2e8f0;border-radius:10px;margin:22px 0;padding:18px;">
              <tr>
                <td colspan="2" style="padding-bottom:12px;">
                  <strong style="font-size:12px;font-weight:800;color:#0284c7;text-transform:uppercase;letter-spacing:0.5px;">
                    🔑 Your Client Portal Credentials
                  </strong>
                </td>
              </tr>
              <tr>
                <td style="padding:8px 0;font-size:13.5px;color:#64748b;width:140px;border-bottom:1px solid #e2e8f0;">
                  <strong>Portal URL:</strong>
                </td>
                <td style="padding:8px 0;font-size:13.5px;border-bottom:1px solid #e2e8f0;">
                  <a href="' . $loginUrl . '" target="_blank" style="color:#0284c7;font-weight:700;text-decoration:none;">' . $loginUrl . '</a>
                </td>
              </tr>
              <tr>
                <td style="padding:8px 0;font-size:13.5px;color:#64748b;border-bottom:1px solid #e2e8f0;">
                  <strong>Username / Email:</strong>
                </td>
                <td style="padding:8px 0;font-size:13.5px;border-bottom:1px solid #e2e8f0;">
                  <code style="background:#e0e7ff;color:#3730a3;padding:3px 8px;border-radius:4px;font-weight:700;font-size:13.5px;">' . $email . '</code>
                </td>
              </tr>
              <tr>
                <td style="padding:8px 0;font-size:13.5px;color:#64748b;">
                  <strong>Initial Password:</strong>
                </td>
                <td style="padding:8px 0;font-size:13.5px;">
                  <code style="background:#fef3c7;color:#92400e;padding:3px 8px;border-radius:4px;font-weight:800;font-size:14px;letter-spacing:1px;">' . $password . '</code>
                </td>
              </tr>
            </table>

            <!-- Button -->
            <div style="text-align:center;margin:28px 0 20px 0;">
              <a href="' . $loginUrl . '" target="_blank" style="display:inline-block;background:#0284c7;color:#ffffff;text-decoration:none;font-size:15px;font-weight:800;padding:12px 32px;border-radius:8px;box-shadow:0 4px 12px rgba(2,132,199,0.35);">
                Log In to Client Portal &rarr;
              </a>
            </div>

            <p style="font-size:12px;color:#64748b;margin:16px 0 0 0;text-align:center;">
              🔒 <em>Security Tip: Please change your password upon your first login for security.</em>
            </p>
          </td>
        </tr>
        <!-- Agency Footer -->
        <tr>
          <td style="background-color:#f8fafc;padding:22px 28px;border-top:1px solid #e2e8f0;font-size:12px;color:#64748b;line-height:1.6;">
            <strong style="color:#0f172a;font-size:13px;display:block;margin-bottom:4px;">' . $agencyName . '</strong>
            <div><strong>Phone:</strong> ' . $agencyPhone . ' &nbsp;|&nbsp; <strong>Email:</strong> ' . $agencyEmail . '</div>
            <div><strong>Property Advisor:</strong> ' . $agentName . ' ' . ($agentMobile ? '(' . $agentMobile . ')' : '') . '</div>
            <div style="margin-top:8px;color:#94a3b8;font-size:11px;">
              &copy; ' . $year . ' ' . $agencyName . '. All rights reserved.
            </div>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
</body>
</html>';
    }

    protected function _isAgencySystemManagerRoleContext($sessionUser = null)
    {
        $sessionUser = $sessionUser === null ? (array)$this->adminValue : (array)$sessionUser;
        $groupId = !empty($sessionUser['User']['ugroup_id']) ? (int)$sessionUser['User']['ugroup_id'] : 0;
        $groupType = strtoupper(trim((string)(isset($sessionUser['Ugroup']['type']) ? $sessionUser['Ugroup']['type'] : '')));
        if($groupId <= 0 || in_array($groupId, array(1, 2, 4, 9), true))
        {
            return false;
        }
        return !in_array($groupType, array('ADR', 'ADMIN', 'SUP', 'SAD', 'AGY', 'AGT', 'RCO'), true);
    }

    public function _isManagerRoleSession($sessionUser = null)
    {
        $sessionUser = $sessionUser === null ? (array)$this->adminValue : (array)$sessionUser;
        if(empty($sessionUser['User']['id']) && isset($this->Session))
        {
            $sessionUser = (array)$this->Session->read('User');
        }
        $groupId = !empty($sessionUser['User']['ugroup_id']) ? (int)$sessionUser['User']['ugroup_id'] : 0;
        $groupType = strtoupper(trim((string)(isset($sessionUser['Ugroup']['type']) ? $sessionUser['Ugroup']['type'] : '')));
        $userType = strtoupper(trim((string)(isset($sessionUser['User']['type']) ? $sessionUser['User']['type'] : '')));
        return ($groupId === 3 || $groupType === 'MAR' || $userType === 'MAR' || $this->_isAgencySystemManagerRoleContext($sessionUser));
    }

    public function _isAgencyAdminSession($sessionUser = null)
    {
        $sessionUser = $sessionUser === null ? (array)$this->adminValue : (array)$sessionUser;
        if(empty($sessionUser['User']['id']) && isset($this->Session))
        {
            $sessionUser = (array)$this->Session->read('User');
        }
        $groupId = !empty($sessionUser['User']['ugroup_id']) ? (int)$sessionUser['User']['ugroup_id'] : 0;
        $groupType = strtoupper(trim((string)(isset($sessionUser['Ugroup']['type']) ? $sessionUser['Ugroup']['type'] : '')));
        $groupName = strtoupper(trim((string)(isset($sessionUser['Ugroup']['name']) ? $sessionUser['Ugroup']['name'] : '')));
        $userRole = strtoupper(trim((string)(isset($sessionUser['User']['role']) ? $sessionUser['User']['role'] : '')));
        $userTypeField = strtoupper(trim((string)(isset($sessionUser['User']['type']) ? $sessionUser['User']['type'] : '')));
        $userTypeAlt = strtoupper(trim((string)(isset($sessionUser['User']['user_type']) ? $sessionUser['User']['user_type'] : '')));
        if($groupId === 4 || in_array($groupType, array('AGY', 'AGENCY'), true) || strpos($groupName, 'AGENCY') !== false || in_array($userRole, array('AGY', 'AGENCY'), true) || in_array($userTypeField, array('AGY', 'AGENCY'), true) || in_array($userTypeAlt, array('AGY', 'AGENCY'), true))
        {
            return true;
        }
        return $this->_isAgencySystemManagerRoleContext($sessionUser);
    }

    public function _agentRoleIds()
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
        catch(Throwable $e)
        {
            return array(2);
        }
    }

    public function _isSuperAdminOnlyController($controllerName)
    {
        $controllerName = strtolower(trim((string)$controllerName));
        $superAdminOnly = array(
            'audittrails',
            'systemupdates',
            'configurations',
            'currencies',
            'paymenttypes',
            'taxes',
            'modules',
            'eldialogs'
        );
        return in_array($controllerName, $superAdminOnly, true);
    }

    protected function _assignedAgencyProjectList($userId = 0)
    {
        $userId = (int)$userId;
        if($userId <= 0)
        {
            $userId = !empty($this->adminValue['User']['id']) ? (int)$this->adminValue['User']['id'] : 0;
        }
        if($userId <= 0)
        {
            return array();
        }
        if($this->_assignedAgencyProjectListCache !== null)
        {
            return (array)$this->_assignedAgencyProjectListCache;
        }
        $projectIds = array();
        try
        {
            $AdminUser = ClassRegistry::init('Admin.User');
            if($AdminUser && method_exists($AdminUser, 'assignedProjectIds'))
            {
                $projectIds = (array)$AdminUser->assignedProjectIds($userId, true);
            }
        }
        catch(Throwable $e)
        {
            $projectIds = array();
        }
        try
        {
            $ProjectMembership = ClassRegistry::init('Project');
            $membershipRows = (array)$ProjectMembership->query("SELECT `project_id` FROM `user_projects` WHERE `user_id`=".$userId);
            foreach($membershipRows as $membershipRow)
            {
                $membershipProjectId = !empty($membershipRow['user_projects']['project_id']) ? (int)$membershipRow['user_projects']['project_id'] : (!empty($membershipRow[0]['project_id']) ? (int)$membershipRow[0]['project_id'] : 0);
                if($membershipProjectId > 0)
                {
                    $projectIds[] = $membershipProjectId;
                }
            }
        }
        catch(Throwable $e)
        {
            // Primary agency below remains the safe fallback.
        }
        $primaryProjectId = !empty($this->adminValue['User']['project_id']) ? (int)$this->adminValue['User']['project_id'] : 0;
        if($primaryProjectId > 0)
        {
            $projectIds[] = $primaryProjectId;
        }
        $projectIds = array_values(array_unique(array_filter(array_map('intval', $projectIds))));
        $projectList = array();
        if(!empty($projectIds))
        {
            try
            {
                $Project = ClassRegistry::init('Project');
                $projectList = (array)$Project->find('list', array(
                    'fields' => array('Project.id', 'Project.name'),
                    'conditions' => array('Project.id' => $projectIds),
                    'order' => array('Project.name' => 'asc'),
                    'recursive' => -1
                ));
            }
            catch(Throwable $e)
            {
                $projectList = array();
            }
        }
        $this->_assignedAgencyProjectListCache = $projectList;
        return $projectList;
    }

    protected function _resolveSystemManagerActiveAgency()
    {
        if(!$this->_isAgencySystemManagerRoleContext((array)$this->adminValue))
        {
            return (int)$this->agencyId;
        }
        $projectList = $this->_assignedAgencyProjectList((int)$this->luserId);
        if(empty($projectList))
        {
            return (int)$this->agencyId;
        }
        $requestedProjectId = isset($this->request->query['project_id']) ? (int)$this->request->query['project_id'] : 0;
        if($requestedProjectId <= 0 && isset($this->request->params['named']['project_id']))
        {
            $requestedProjectId = (int)$this->request->params['named']['project_id'];
        }
        $sessionKey = 'Admin.SystemManagerActiveAgency.'.(int)$this->luserId;
        $sessionProjectId = (int)$this->Session->read($sessionKey);
        $primaryProjectId = !empty($this->adminValue['User']['project_id']) ? (int)$this->adminValue['User']['project_id'] : 0;
        if($requestedProjectId > 0 && isset($projectList[$requestedProjectId]))
        {
            $activeProjectId = $requestedProjectId;
        }
        elseif($sessionProjectId > 0 && isset($projectList[$sessionProjectId]))
        {
            $activeProjectId = $sessionProjectId;
        }
        elseif($primaryProjectId > 0 && isset($projectList[$primaryProjectId]))
        {
            $activeProjectId = $primaryProjectId;
        }
        else
        {
            $activeProjectIds = array_keys($projectList);
            $activeProjectId = (int)reset($activeProjectIds);
        }
        $this->Session->write($sessionKey, $activeProjectId);
        return $activeProjectId;
    }
    protected function _isSuperAdminSession($sessionUser = null)
    {
        if($sessionUser === null)
        {
            $sessionUser = (array)$this->Session->read('User');
        }
        $userId = isset($sessionUser['User']['id']) ? (int)$sessionUser['User']['id'] : 0;
        $userGroupId = isset($sessionUser['User']['ugroup_id']) ? (int)$sessionUser['User']['ugroup_id'] : 0;
        $userType = isset($sessionUser['Ugroup']['type']) ? strtoupper(trim((string)$sessionUser['Ugroup']['type'])) : '';
        $userGroupName = isset($sessionUser['Ugroup']['name']) ? strtoupper(trim((string)$sessionUser['Ugroup']['name'])) : '';
        $userRole = isset($sessionUser['User']['role']) ? strtoupper(trim((string)$sessionUser['User']['role'])) : '';
        $userTypeField = isset($sessionUser['User']['type']) ? strtoupper(trim((string)$sessionUser['User']['type'])) : '';
        $userUserType = isset($sessionUser['User']['user_type']) ? strtoupper(trim((string)$sessionUser['User']['user_type'])) : '';
        $userUsertypeAlt = isset($sessionUser['User']['usertype']) ? strtoupper(trim((string)$sessionUser['User']['usertype'])) : '';
        $userName = isset($sessionUser['User']['username']) ? strtoupper(trim((string)$sessionUser['User']['username'])) : '';
        $superTypes = array('ADR', 'ADMIN', 'SUP', 'SAD', 'SUPERADMIN', 'SUPER ADMIN');
        $isSuperType = in_array($userType, $superTypes, true);
        $isSuperByName = (in_array($userGroupName, array('ADMIN','ADMINISTRATOR','SUPERADMIN','SUPER ADMIN','CEO','DIRECTOR'), true) || ($userGroupName !== '' && preg_match('/SUPER\s*ADMIN|ADMINISTRATOR|\bADMIN\b|\bCEO\b|\bDIRECTOR\b/i', $userGroupName)));
        $isSuperByUserType = in_array($userRole, $superTypes, true) || in_array($userTypeField, $superTypes, true) || in_array($userUserType, $superTypes, true) || in_array($userUsertypeAlt, $superTypes, true);
        $isSuperByUsername = ($userName === 'ADMIN' || $userName === 'SUPERADMIN' || $userName === 'SUPER_ADMIN');
        $isSuperByFlag = (!empty($sessionUser['User']['is_super_admin']) || !empty($sessionUser['User']['super_admin']) || !empty($sessionUser['User']['is_admin']));
        if($userId === 1 || $userGroupId === 1 || $isSuperType || $isSuperByName || $isSuperByUserType || $isSuperByUsername || $isSuperByFlag)
        {
            return true;
        }
        if($userGroupId > 0)
        {
            static $groupSuperCache = array();
            if(array_key_exists($userGroupId, $groupSuperCache))
            {
                return !empty($groupSuperCache[$userGroupId]);
            }
            $isSuperFromDb = false;
            try
            {
                $Ugroup = ClassRegistry::init('Ugroup');
                if($Ugroup)
                {
                    $ugroupRow = (array)$Ugroup->find('first', array(
                        'fields' => array('Ugroup.id', 'Ugroup.type', 'Ugroup.name'),
                        'conditions' => array('Ugroup.id' => $userGroupId),
                        'recursive' => -1
                    ));
                    if(!empty($ugroupRow['Ugroup']))
                    {
                        $dbType = strtoupper(trim((string)$ugroupRow['Ugroup']['type']));
                        $dbName = strtoupper(trim((string)$ugroupRow['Ugroup']['name']));
                        $isSuperFromDb = ((int)$ugroupRow['Ugroup']['id'] === 1 || in_array($dbType, $superTypes, true) || in_array($dbName, array('ADMIN','ADMINISTRATOR','SUPERADMIN','SUPER ADMIN','CEO','DIRECTOR'), true) || ($dbName !== '' && preg_match('/SUPER\s*ADMIN|ADMINISTRATOR|\bADMIN\b|\bCEO\b|\bDIRECTOR\b/i', $dbName)));
                    }
                }
            }
            catch(Throwable $e)
            {
                $isSuperFromDb = false;
            }
            $groupSuperCache[$userGroupId] = $isSuperFromDb ? 1 : 0;
            return $isSuperFromDb;
        }
        return false;
    }
    protected function _normalizeSuperAdminSessionUser()
    {
        $sessionUser = (array)$this->Session->read('User');
        if(empty($sessionUser['User']['id']) || !$this->_isSuperAdminSession($sessionUser))
        {
            return $sessionUser;
        }
        if(!isset($sessionUser['Ugroup']) || !is_array($sessionUser['Ugroup']))
        {
            $sessionUser['Ugroup'] = array();
        }
        if(strtoupper(trim((string)(isset($sessionUser['Ugroup']['type']) ? $sessionUser['Ugroup']['type'] : ''))) !== 'ADR')
        {
            $sessionUser['Ugroup']['type'] = 'ADR';
            $this->Session->write('User', $sessionUser);
        }
        return $sessionUser;
    }
    protected function _reloadCurrentAdminSessionUser()
    {
        $sessionUser = (array)$this->Session->read('User');
        $userId = !empty($sessionUser['User']['id']) ? (int)$sessionUser['User']['id'] : 0;
        if($userId <= 0)
        {
            return $sessionUser;
        }
        try
        {
            $User = ClassRegistry::init('User');
            if(!$User)
            {
                return $sessionUser;
            }
            $freshUser = (array)$User->find('first', array(
                'conditions' => array(
                    'User.id' => $userId,
                    'User.deleted IS NULL'
                ),
                'recursive' => 0
            ));
            if(empty($freshUser['User']['id']))
            {
                $this->Session->destroy();
                $this->redirect(array('plugin' => 'admin', 'controller' => 'users', 'action' => 'login_form'));
                exit();
            }
            if(isset($freshUser['User']['status']) && (string)$freshUser['User']['status'] !== 'Active')
            {
                $this->Session->destroy();
                $this->redirect(array('plugin' => 'admin', 'controller' => 'users', 'action' => 'login_form'));
                exit();
            }
            if(isset($freshUser['User']['login_status']) && strcasecmp(trim((string)$freshUser['User']['login_status']), 'Blocked') === 0)
            {
                $this->Session->destroy();
                $this->Session->setFlash(__('Login is blocked. Please contact administrator.'), 'flash', array('alert' => 'danger'));
                $this->redirect(array('plugin' => 'admin', 'controller' => 'users', 'action' => 'login_form'));
                exit();
            }
            $this->Session->write('User', $freshUser);
            return $freshUser;
        }
        catch(Throwable $e)
        {
            return $sessionUser;
        }
    }
    protected function _resolveAdminImpersonationSessionUser($sessionUser = array())
    {
        $sessionUser = is_array($sessionUser) ? $sessionUser : array();
        $impMeta = (array)$this->Session->read('Impersonation.admin');
        $targetUserId = !empty($impMeta['target_user_id']) ? (int)$impMeta['target_user_id'] : 0;
        if($targetUserId <= 0)
        {
            return $sessionUser;
        }
        $currentUserId = !empty($sessionUser['User']['id']) ? (int)$sessionUser['User']['id'] : 0;
        if($currentUserId === $targetUserId)
        {
            return $sessionUser;
        }
        try
        {
            $User = ClassRegistry::init('User');
            if(!$User)
            {
                return $sessionUser;
            }
            $targetUser = (array)$User->find('first', array(
                'conditions' => array(
                    'User.id' => $targetUserId,
                    'User.status' => 'Active',
                    'User.deleted IS NULL'
                ),
                'recursive' => 0
            ));
            if(empty($targetUser['User']['id']))
            {
                if(!empty($impMeta['original_user']['User']['id']))
                {
                    $this->Session->write('User', $impMeta['original_user']);
                }
                $this->Session->delete('Impersonation.admin');
                return !empty($impMeta['original_user']['User']['id']) ? $impMeta['original_user'] : $sessionUser;
            }
            $this->Session->write('User', $targetUser);
            return $targetUser;
        }
        catch(Throwable $e)
        {
            return $sessionUser;
        }
    }
    protected function _impersonationTtlSeconds()
    {
        return 0; // no auto-expiry
    }
    protected function _runAdminMenuBootstrap()
    {
        $this->_removeMiscoldMenu();
        $this->_ensureNotificationSchema();
        $this->_ensureNotificationsMenu();
        $this->_ensureArdaccountsMenu();
        $this->_ensureTransactionsAndExpenseMenus();
        $this->_ensureLattesMenu();
        $this->_ensureBallotingMenu();
        $this->_ensureBusinessfeaturesMenu();
        $this->_ensureCrmautomationsMenu();
        $this->_ensureCommunicationAutomationMenu();
        $this->_ensureReportsMenu();
        $this->_ensureAgentWalletHistoryMainMenu();
        $this->_ensureDealRefundMenu();
        $this->_ensureDealRefundRequestMenu();
        $this->_ensureDealRentalMenu();
        $this->_ensureDealTransferMenu();
        $this->_ensureDealsAllMenu();
        $this->_ensureAuditTrailMenu();
        $this->_ensureThemeSettingsMenu();
        $this->_ensureAgencyPaymentPlanSettingsMenu();
        $this->_ensureMediaGalleryMenu();
        $this->_ensurePaymentReminderMenu();
        $this->_ensureRecoveryMenu();
        $this->_ensureDocumentVaultMenu();
        $this->_ensurePropertyEnhancementsMenu();
        $this->_ensureAddressHierarchyMenu();
        $this->_ensureLetterApprovalMenu();
        $this->_ensureSeoAssistantMenu();
        $this->_ensureLeadDuplicateMenu();
        $this->_ensureAgentPerformanceMenu();
        $this->_ensureAppManagementMenu();
        $this->_ensureModulesMenu();
        $this->_ensureModuleThemeSettingManagerMenu();
        $this->_ensureBillingReportsMenu();
        $this->_ensureInstallmentSyncSettingsMenu();
        $this->_ensureSystemUpdatesMenu();
        $this->_ensureDealFinancialRiskSettingsMenu();
        $this->_ensureUserLevelPageRightsCoverage();
        $this->_ensureRecoveryOfficerRoleRestrictions();
    }

    protected function _ensureDealFinancialRiskSettingsMenu()
    {
        // 1. Under Configurations (Root ID 7)
        $this->_ensureSimpleChildAdminPage(array(
            'parent_id' => 7,
            'parent_page_name' => 'Configurations',
            'model_name' => 'Configuration',
            'page_name' => 'Deals & Financial Risk Management Settings',
            'controller_name' => 'Configurations',
            'action_name' => 'dealsettings',
            'icon' => 'fa fa-shield',
            'ordering' => 6,
            'published' => 'Yes',
            'sel_name' => 'dealsettings,deal_settings',
            'grant_group_ids' => array(1)
        ));

        // 2. Under Deals (Root ID 4) - Cash Flow Forecast
        $this->_ensureSimpleChildAdminPage(array(
            'parent_id' => 4,
            'parent_page_name' => 'Deals',
            'model_name' => 'Deal',
            'page_name' => 'Cash Flow Forecast',
            'controller_name' => 'Deals',
            'action_name' => 'cashflow',
            'icon' => 'fa fa-line-chart',
            'ordering' => 2,
            'published' => 'Yes',
            'sel_name' => 'cashflow',
            'grant_group_types' => array('ADR', 'AGY')
        ));

        // 3. Under Deals (Root ID 4) - Deal & Risk Settings
        $this->_ensureSimpleChildAdminPage(array(
            'parent_id' => 4,
            'parent_page_name' => 'Deals',
            'model_name' => 'Deal',
            'page_name' => 'Deal & Risk Settings',
            'controller_name' => 'Configurations',
            'action_name' => 'dealsettings',
            'icon' => 'fa fa-shield',
            'ordering' => 9,
            'published' => 'Yes',
            'sel_name' => 'dealsettings,deal_settings',
            'grant_group_ids' => array(1)
        ));
    }

    protected function _ensureSystemUpdatesMenu()
    {
        $this->_ensureSimpleChildAdminPage(array(
            'parent_id' => 7,
            'parent_page_name' => 'Configurations',
            'model_name' => 'System Updates',
            'page_name' => 'System Updates & Patches',
            'controller_name' => 'Systemupdates',
            'action_name' => 'index',
            'icon' => 'fa fa-cloud-download',
            'ordering' => 18,
            'published' => 'Yes',
            'sel_name' => 'system_updates,configurations,system_modules',
            'grant_group_ids' => array(1)
        ));
    }

    protected function _ensureBillingReportsMenu()
    {
        $this->_ensureSimpleChildAdminPage(array(
            'parent_controller' => 'BillingDashboards',
            'parent_page_name' => 'Billing System',
            'model_name' => 'Reports & Recovery Hub',
            'page_name' => 'Reports & Recovery Hub',
            'controller_name' => 'BillingReports',
            'action_name' => 'index',
            'icon' => 'fa fa-bar-chart',
            'ordering' => 3,
            'published' => '1',
            'sel_name' => 'billingreports',
            'grant_group_types' => array('ADR', 'AGY')
        ));
    }

    protected function _ensureInstallmentSyncSettingsMenu()
    {
        try
        {
            $Page = ClassRegistry::init('Page');
            $PageRight = ClassRegistry::init('PageRight');
            $Ugroup = ClassRegistry::init('Ugroup');
            if(!$Page || !$PageRight || !$Ugroup)
            {
                return;
            }

            $root = (array)$Page->find('first', array(
                'fields' => array('Page.id'),
                'conditions' => array(
                    'Page.parent_id' => 0,
                    'OR' => array(
                        array('Page.id' => 7),
                        array('LOWER(Page.page_name)' => 'configurations'),
                        array('LOWER(Page.model_name)' => 'configuration'),
                        array('LOWER(Page.controller_name)' => 'configurations')
                    )
                ),
                'order' => array('Page.id' => 'asc'),
                'recursive' => -1
            ));
            $rootId = !empty($root['Page']['id']) ? (int)$root['Page']['id'] : 7;

            $existing = (array)$Page->find('first', array(
                'fields' => array('Page.id'),
                'conditions' => array(
                    'OR' => array(
                        array(
                            'LOWER(Page.controller_name)' => array('installmentsyncsettings', 'installment_sync_settings'),
                            'LOWER(Page.action_name)' => 'index'
                        )
                    )
                ),
                'order' => array('Page.id' => 'asc'),
                'recursive' => -1
            ));

            $pageData = array(
                'model_name' => 'Configuration',
                'page_name' => 'Installment Sync & Discount',
                'controller_name' => 'InstallmentSyncSettings',
                'action_name' => 'index',
                'icon' => 'fa fa-calendar-check-o',
                'parent_id' => $rootId,
                'ordering' => 5,
                'published' => 'Yes',
                'sel_name' => 'installmentsyncsettings'
            );
            if(!empty($existing['Page']['id']))
            {
                $pageData['id'] = (int)$existing['Page']['id'];
            }
            $Page->create();
            $Page->save(array('Page' => $pageData), array('validate' => false));
            $pageId = !empty($pageData['id']) ? (int)$pageData['id'] : (int)$Page->id;
            if($pageId <= 0)
            {
                return;
            }

            $groups = (array)$Ugroup->find('all', array(
                'fields' => array('Ugroup.id', 'Ugroup.type'),
                'recursive' => -1
            ));
            foreach($groups as $groupRow)
            {
                $groupId = !empty($groupRow['Ugroup']['id']) ? (int)$groupRow['Ugroup']['id'] : 0;
                $groupType = !empty($groupRow['Ugroup']['type']) ? strtoupper(trim((string)$groupRow['Ugroup']['type'])) : '';
                if($groupId <= 0)
                {
                    continue;
                }
                $grantFullRights = ($groupId === 1 || $groupType === 'SUPER ADMIN' || $groupType === 'CEO' || $groupType === 'ADMINISTRATOR');
                $existingRight = (array)$PageRight->find('first', array(
                    'fields' => array('PageRight.id'),
                    'conditions' => array(
                        'PageRight.page_id' => $pageId,
                        'PageRight.ugroup_id' => $groupId
                    ),
                    'recursive' => -1
                ));
                if(!empty($existingRight['PageRight']['id']) && !$grantFullRights)
                {
                    continue;
                }
                $rightData = array(
                    'page_id' => $pageId,
                    'ugroup_id' => $groupId,
                    'view_right' => $grantFullRights ? 1 : 0,
                    'save_right' => $grantFullRights ? 1 : 0,
                    'update_right' => $grantFullRights ? 1 : 0,
                    'search_right' => $grantFullRights ? 1 : 0
                );
                if(!empty($existingRight['PageRight']['id']))
                {
                    $rightData['id'] = (int)$existingRight['PageRight']['id'];
                }
                $PageRight->create();
                $PageRight->save(array('PageRight' => $rightData), array('validate' => false));
            }
        }
        catch(Throwable $e)
        {
            // Ignore
        }
    }

    protected function _ensureAppManagementMenu()
    {
        try
        {
            $Page = ClassRegistry::init('Page');
            $PageRight = ClassRegistry::init('PageRight');
            $Ugroup = ClassRegistry::init('Ugroup');
            if(!$Page || !$PageRight || !$Ugroup)
            {
                return;
            }
            $root = (array)$Page->find('first', array(
                'fields' => array('Page.id'),
                'conditions' => array(
                    'LOWER(Page.controller_name)' => 'appmanagements',
                    'LOWER(Page.action_name)' => 'index',
                    'Page.parent_id' => 0
                ),
                'recursive' => -1
            ));
            $rootData = array(
                'model_name' => 'Appmanagement',
                'page_name' => 'App Management',
                'controller_name' => 'Appmanagements',
                'action_name' => 'index',
                'icon' => 'fa fa-mobile',
                'parent_id' => 0,
                'ordering' => 90,
                'published' => 'Yes',
                'sel_name' => 'app_management'
            );
            if(!empty($root['Page']['id']))
            {
                $rootData['id'] = (int)$root['Page']['id'];
            }
            $Page->create();
            $Page->save(array('Page' => $rootData), array('validate' => false));
            $rootId = !empty($rootData['id']) ? (int)$rootData['id'] : (int)$Page->id;
            if($rootId <= 0)
            {
                return;
            }
            $pages = array(
                array('page_name' => 'Overview & Controls', 'action_name' => 'index', 'icon' => 'fa fa-dashboard', 'ordering' => 1, 'published' => 'Yes'),
                array('page_name' => 'Version & APK Update', 'action_name' => 'version_control', 'icon' => 'fa fa-cloud-upload', 'ordering' => 2, 'published' => 'Yes'),
                array('page_name' => 'Send In-App Notification', 'action_name' => 'send_notification', 'icon' => 'fa fa-paper-plane', 'ordering' => 3, 'published' => 'Yes'),
                array('page_name' => 'Broadcast History', 'action_name' => 'notifications_history', 'icon' => 'fa fa-history', 'ordering' => 4, 'published' => 'Yes'),
                array('page_name' => 'App Client Users', 'action_name' => 'client_activity', 'icon' => 'fa fa-users', 'ordering' => 5, 'published' => 'Yes'),
                array('page_name' => 'Delete Broadcast Notification', 'action_name' => 'delete_broadcast', 'icon' => 'fa fa-trash', 'ordering' => 6, 'published' => 'No')
            );
            $pageIds = array($rootId => $rootId);
            foreach($pages as $pageRow)
            {
                $existing = (array)$Page->find('first', array(
                    'fields' => array('Page.id'),
                    'conditions' => array(
                        'LOWER(Page.controller_name)' => 'appmanagements',
                        'LOWER(Page.action_name)' => strtolower($pageRow['action_name'])
                    ),
                    'recursive' => -1
                ));
                $saveData = array(
                    'model_name' => 'Appmanagement',
                    'page_name' => $pageRow['page_name'],
                    'controller_name' => 'Appmanagements',
                    'action_name' => $pageRow['action_name'],
                    'icon' => $pageRow['icon'],
                    'parent_id' => $rootId,
                    'ordering' => $pageRow['ordering'],
                    'published' => $pageRow['published'],
                    'sel_name' => 'app_management'
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
            $groups = (array)$Ugroup->find('all', array(
                'fields' => array('Ugroup.id', 'Ugroup.type', 'Ugroup.name'),
                'recursive' => -1
            ));
            foreach($groups as $groupRow)
            {
                $group = !empty($groupRow['Ugroup']) ? (array)$groupRow['Ugroup'] : array();
                $groupId = !empty($group['id']) ? (int)$group['id'] : 0;
                if($groupId <= 0)
                {
                    continue;
                }
                $type = strtoupper(trim((string)(isset($group['type']) ? $group['type'] : '')));
                $name = strtoupper(trim((string)(isset($group['name']) ? $group['name'] : '')));
                $grant = ($groupId === 1 || in_array($type, array('ADR', 'AGY'), true) || preg_match('/\b(SUPER|ADMIN|DIRECTOR|CEO|MANAGER)\b/i', $name));
                foreach($pageIds as $pageId)
                {
                    $existingRight = (array)$PageRight->find('first', array(
                        'fields' => array('PageRight.id'),
                        'conditions' => array(
                            'PageRight.page_id' => (int)$pageId,
                            'PageRight.ugroup_id' => $groupId
                        ),
                        'recursive' => -1
                    ));
                    $rightData = array(
                        'page_id' => (int)$pageId,
                        'ugroup_id' => $groupId,
                        'save_right' => $grant ? 1 : 0,
                        'update_right' => $grant ? 1 : 0,
                        'view_right' => $grant ? 1 : 0,
                        'search_right' => $grant ? 1 : 0
                    );
                    if(!empty($existingRight['PageRight']['id']))
                    {
                        $rightData['id'] = (int)$existingRight['PageRight']['id'];
                    }
                    $PageRight->create();
                    $PageRight->save(array('PageRight' => $rightData), array('validate' => false));
                }
            }
        }
        catch(Throwable $e)
        {
            // keep app flow safe
        }
    }

    protected function _ensureModulesMenu()
    {
        try
        {
            $Page = ClassRegistry::init('Page');
            $PageRight = ClassRegistry::init('PageRight');
            $Ugroup = ClassRegistry::init('Ugroup');
            if(!$Page || !$PageRight || !$Ugroup)
            {
                return;
            }

            // 1. Locate the Configurations parent menu (id=7 or parent_id=0 with Configurations)
            $configRoot = (array)$Page->find('first', array(
                'fields' => array('Page.id'),
                'conditions' => array(
                    'Page.parent_id' => 0,
                    'OR' => array(
                        array('LOWER(Page.page_name)' => 'configurations'),
                        array('LOWER(Page.model_name)' => 'configuration'),
                        array('LOWER(Page.controller_name)' => 'configurations')
                    )
                ),
                'order' => array('Page.id' => 'asc'),
                'recursive' => -1
            ));
            $configRootId = !empty($configRoot['Page']['id']) ? (int)$configRoot['Page']['id'] : 7;

            // 2. Remove any old standalone root page (parent_id = 0 for Modules)
            $oldRootPages = (array)$Page->find('all', array(
                'fields' => array('Page.id'),
                'conditions' => array(
                    'LOWER(Page.controller_name)' => 'modules',
                    'Page.parent_id' => 0
                ),
                'recursive' => -1
            ));
            foreach($oldRootPages as $oldPage) {
                $oldPid = (int)$oldPage['Page']['id'];
                if ($oldPid > 0 && $oldPid !== $configRootId) {
                    $PageRight->deleteAll(array('PageRight.page_id' => $oldPid), false);
                    $Page->delete($oldPid, false);
                }
            }

            // 3. Register Module Manager and Module Builder under Configurations
            $pages = array(
                array(
                    'page_name' => 'Module Manager',
                    'controller_name' => 'Modules',
                    'action_name' => 'index',
                    'icon' => 'fa fa-puzzle-piece',
                    'ordering' => 14,
                    'published' => 'Yes'
                ),
                array(
                    'page_name' => 'Module Builder',
                    'controller_name' => 'Modules',
                    'action_name' => 'builder',
                    'icon' => 'fa fa-magic',
                    'ordering' => 15,
                    'published' => 'Yes'
                ),
                array(
                    'page_name' => 'Install Module Package',
                    'controller_name' => 'Modules',
                    'action_name' => 'install',
                    'icon' => 'fa fa-cloud-upload',
                    'ordering' => 16,
                    'published' => 'No'
                ),
                array(
                    'page_name' => 'Toggle Module Status',
                    'controller_name' => 'Modules',
                    'action_name' => 'toggle_status',
                    'icon' => 'fa fa-toggle-on',
                    'ordering' => 17,
                    'published' => 'No'
                ),
                array(
                    'page_name' => 'Export Module',
                    'controller_name' => 'Modules',
                    'action_name' => 'export',
                    'icon' => 'fa fa-download',
                    'ordering' => 18,
                    'published' => 'No'
                ),
                array(
                    'page_name' => 'Uninstall Module',
                    'controller_name' => 'Modules',
                    'action_name' => 'uninstall',
                    'icon' => 'fa fa-trash',
                    'ordering' => 19,
                    'published' => 'No'
                )
            );

            $pageIds = array();
            foreach($pages as $pageRow)
            {
                $existing = (array)$Page->find('first', array(
                    'fields' => array('Page.id'),
                    'conditions' => array(
                        'LOWER(Page.controller_name)' => 'modules',
                        'LOWER(Page.action_name)' => strtolower($pageRow['action_name'])
                    ),
                    'recursive' => -1
                ));

                $saveData = array(
                    'model_name' => 'Configuration',
                    'page_name' => $pageRow['page_name'],
                    'controller_name' => 'Modules',
                    'action_name' => $pageRow['action_name'],
                    'icon' => $pageRow['icon'],
                    'parent_id' => $configRootId,
                    'ordering' => (int)$pageRow['ordering'],
                    'published' => $pageRow['published'],
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

            // 4. Grant full permissions to Super Admin (ugroup_id=1) and admin roles
            $ugroups = (array)$Ugroup->find('all', array(
                'fields' => array('Ugroup.id', 'Ugroup.name', 'Ugroup.type'),
                'recursive' => -1
            ));

            foreach($ugroups as $ug) {
                $ugId = (int)$ug['Ugroup']['id'];
                $name = strtoupper(trim((string)$ug['Ugroup']['name']));
                $type = strtoupper(trim((string)$ug['Ugroup']['type']));
                $isSuperAdminRole = ($ugId === 1 || in_array($type, array('ADR', 'SUP', 'SAD'), true) || preg_match('/\b(SUPER|ADMIN|DIRECTOR|CEO)\b/i', $name));

                if (!$isSuperAdminRole) {
                    continue;
                }

                foreach($pageIds as $pageId)
                {
                    $existingRight = (array)$PageRight->find('first', array(
                        'fields' => array('PageRight.id'),
                        'conditions' => array(
                            'PageRight.page_id' => $pageId,
                            'PageRight.ugroup_id' => $ugId
                        ),
                        'recursive' => -1
                    ));
                    $rightData = array(
                        'page_id' => $pageId,
                        'ugroup_id' => $ugId,
                        'save_right' => 1,
                        'update_right' => 1,
                        'view_right' => 1,
                        'search_right' => 1
                    );
                    if(!empty($existingRight['PageRight']['id']))
                    {
                        $rightData['id'] = (int)$existingRight['PageRight']['id'];
                    }
                    $PageRight->create();
                    $PageRight->save(array('PageRight' => $rightData), array('validate' => false));
                }
            }
        }
        catch(Throwable $e)
        {
            CakeLog::write('error', 'Modules menu bootstrap failed: '.$e->getMessage());
        }
    }

    protected function _ensureModuleThemeSettingManagerMenu()
    {
        try
        {
            $Page = ClassRegistry::init('Page');
            $PageRight = ClassRegistry::init('PageRight');
            $Ugroup = ClassRegistry::init('Ugroup');
            $SystemModule = ClassRegistry::init('Admin.SystemModule');
            if(!$Page || !$PageRight || !$Ugroup)
            {
                return;
            }

            // Check if RealEstateTheme plugin directory exists and is active in system_modules
            $isThemeActive = false;
            $pluginDir = APP . 'Plugin' . DS . 'RealEstateTheme';
            if (is_dir($pluginDir) && $SystemModule) {
                $modRecord = $SystemModule->find('first', array(
                    'conditions' => array(
                        'OR' => array(
                            array('LOWER(SystemModule.slug)' => 'realestatetheme'),
                            array('LOWER(SystemModule.slug)' => 'real_estate_theme')
                        )
                    ),
                    'recursive' => -1
                ));
                if (!empty($modRecord) && !empty($modRecord['SystemModule']['status'])) {
                    $isThemeActive = true;
                }
            }

            $publishedValue = $isThemeActive ? 'Yes' : 'No';

            // 1. Root Menu at the end of sidebar (ordering = 999)
            $root = (array)$Page->find('first', array(
                'conditions' => array(
                    'Page.parent_id' => 0,
                    'OR' => array(
                        array('LOWER(Page.controller_name)' => 'realestatethemes'),
                        array('LOWER(Page.controller_name)' => 'real_estate_themes'),
                        array('Page.page_name' => 'Theme Setting Manager'),
                        array('Page.page_name' => 'Theme Settings Manager')
                    )
                ),
                'order' => array('Page.id' => 'asc'),
                'recursive' => -1
            ));

            $rootData = array(
                'model_name' => 'RealEstateTheme',
                'page_name' => 'Theme Setting Manager',
                'controller_name' => 'RealEstateThemes',
                'action_name' => 'settings',
                'icon' => 'fa fa-paint-brush',
                'parent_id' => 0,
                'ordering' => 999,
                'published' => $publishedValue,
                'sel_name' => 'realestatethemes,real_estate_themes,modules'
            );
            if (!empty($root['Page']['id'])) {
                $rootData['id'] = (int)$root['Page']['id'];
            }
            $Page->create();
            $Page->save(array('Page' => $rootData), array('validate' => false));
            $rootId = !empty($rootData['id']) ? (int)$rootData['id'] : (int)$Page->id;

            if ($rootId <= 0) {
                return;
            }

            // 2. Child Menus under this root
            $children = array(
                array(
                    'page_name' => 'Theme Customizer',
                    'controller_name' => 'RealEstateThemes',
                    'action_name' => 'settings',
                    'icon' => 'fa fa-sliders',
                    'ordering' => 1,
                    'published' => $publishedValue
                ),
                array(
                    'page_name' => 'Theme Overview',
                    'controller_name' => 'RealEstateThemes',
                    'action_name' => 'index',
                    'icon' => 'fa fa-desktop',
                    'ordering' => 2,
                    'published' => $publishedValue
                ),
                array(
                    'page_name' => 'Module Manager',
                    'controller_name' => 'Modules',
                    'action_name' => 'index',
                    'icon' => 'fa fa-puzzle-piece',
                    'ordering' => 3,
                    'published' => $publishedValue
                ),
                array(
                    'page_name' => 'Module Builder',
                    'controller_name' => 'Modules',
                    'action_name' => 'builder',
                    'icon' => 'fa fa-magic',
                    'ordering' => 4,
                    'published' => $publishedValue
                )
            );

            $menuPageIds = array($rootId => $rootId);
            foreach ($children as $c) {
                $existChild = (array)$Page->find('first', array(
                    'conditions' => array(
                        'Page.parent_id' => $rootId,
                        'LOWER(Page.controller_name)' => strtolower($c['controller_name']),
                        'LOWER(Page.action_name)' => strtolower($c['action_name'])
                    ),
                    'recursive' => -1
                ));

                $childData = $c;
                $childData['model_name'] = ($c['controller_name'] === 'RealEstateThemes') ? 'RealEstateTheme' : 'SystemModule';
                $childData['parent_id'] = $rootId;
                $childData['sel_name'] = '';
                if (!empty($existChild['Page']['id'])) {
                    $childData['id'] = (int)$existChild['Page']['id'];
                }
                $Page->create();
                $Page->save(array('Page' => $childData), array('validate' => false));
                $savedChildId = !empty($childData['id']) ? (int)$childData['id'] : (int)$Page->id;
                if ($savedChildId > 0) {
                    $menuPageIds[$savedChildId] = $savedChildId;
                }
            }

            if (!$isThemeActive) {
                // Hide Theme Setting Manager root and RealEstateThemes pages
                $Page->updateAll(
                    array('Page.published' => "'No'"),
                    array(
                        'OR' => array(
                            array('Page.id' => $rootId),
                            array('LOWER(Page.controller_name)' => 'realestatethemes'),
                            array('LOWER(Page.controller_name)' => 'real_estate_themes')
                        )
                    )
                );
            } else {
                // Ensure published
                $Page->updateAll(
                    array('Page.published' => "'Yes'"),
                    array(
                        'OR' => array(
                            array('Page.id' => $rootId),
                            array('LOWER(Page.controller_name)' => 'realestatethemes'),
                            array('LOWER(Page.controller_name)' => 'real_estate_themes')
                        )
                    )
                );
            }

            // Grant Super Admin and Admin Full Rights
            $superAdmins = (array)$Ugroup->find('list', array(
                'fields' => array('Ugroup.id', 'Ugroup.id'),
                'conditions' => array(
                    'OR' => array(
                        array('Ugroup.id' => 1),
                        array('LOWER(Ugroup.name) LIKE' => '%super%'),
                        array('LOWER(Ugroup.name) LIKE' => '%admin%')
                    )
                ),
                'recursive' => -1
            ));
            if (empty($superAdmins)) {
                $superAdmins = array(1 => 1);
            }

            foreach ($menuPageIds as $grantPageId) {
                foreach ($superAdmins as $groupId) {
                    $existRight = (array)$PageRight->find('first', array(
                        'conditions' => array(
                            'PageRight.page_id' => $grantPageId,
                            'PageRight.ugroup_id' => (int)$groupId
                        ),
                        'recursive' => -1
                    ));
                    if (empty($existRight['PageRight']['id'])) {
                        $PageRight->create();
                        $PageRight->save(array('PageRight' => array(
                            'page_id' => $grantPageId,
                            'ugroup_id' => (int)$groupId,
                            'ugroup_name' => ($groupId == 1) ? 'Super Admin' : 'Admin',
                            'save_right' => 1,
                            'view_right' => 1,
                            'update_right' => 1,
                            'delete_right' => 1,
                            'search_right' => 1
                        )), array('validate' => false));
                    }
                }
            }
        }
        catch(Throwable $e)
        {
            CakeLog::write('error', 'ModuleThemeSettingManager menu bootstrap failed: '.$e->getMessage());
        }
    }

    protected function _ensurePaymentReminderMenu()
    {
        $this->_ensureSimpleRootAdminPage(array(
            'model_name' => 'Payment Reminder',
            'page_name' => 'Payment Reminders',
            'controller_name' => 'Paymentreminders',
            'action_name' => 'index',
            'icon' => 'fa fa-bell',
            'ordering' => 15,
            'sel_name' => 'payment_reminders,plans_payments',
            'grant_group_types' => array('ADR', 'AGY')
        ));
        $this->_ensureSimpleRootAdminPage(array(
            'model_name' => 'Payment Reminder Settings',
            'page_name' => 'Payment Reminder Settings',
            'controller_name' => 'payment_reminder_settings',
            'action_name' => 'settings',
            'icon' => 'fa fa-cog',
            'ordering' => 15,
            'published' => 'No',
            'sel_name' => 'payment_reminders,payment_reminder_settings',
            'grant_group_types' => array('ADR', 'AGY')
        ));
    }

    protected function _ensureCommunicationAutomationMenu()
    {
        try
        {
            $Page = ClassRegistry::init('Page');
            $PageRight = ClassRegistry::init('PageRight');
            $Ugroup = ClassRegistry::init('Ugroup');
            if(!$Page || !$PageRight || !$Ugroup)
            {
                return;
            }
            $root = (array)$Page->find('first', array(
                'fields' => array('Page.id'),
                'conditions' => array(
                    'LOWER(Page.controller_name)' => 'communicationautomations',
                    'LOWER(Page.action_name)' => 'index',
                    'Page.parent_id' => 0
                ),
                'recursive' => -1
            ));
            $rootData = array(
                'model_name' => 'Communication Automation',
                'page_name' => 'Communication Automation',
                'controller_name' => 'Communicationautomations',
                'action_name' => 'index',
                'icon' => 'fa fa-comments',
                'parent_id' => isset($pageData['parent_id']) ? (int)$pageData['parent_id'] : 0,
                'ordering' => 18,
                'published' => 'Yes',
                'sel_name' => 'communication_templates,communication_rules,communication_queue,communication_logs'
            );
            if(!empty($root['Page']['id']))
            {
                $rootData['id'] = (int)$root['Page']['id'];
            }
            $Page->create();
            $Page->save(array('Page' => $rootData), array('validate' => false));
            $rootId = !empty($rootData['id']) ? (int)$rootData['id'] : (int)$Page->id;
            if($rootId <= 0)
            {
                return;
            }
            $pages = array(
                array('page_name' => 'Automation Rules', 'action_name' => 'rules', 'icon' => 'fa fa-random', 'ordering' => 1, 'published' => 'Yes'),
                array('page_name' => 'Message Templates', 'action_name' => 'templates', 'icon' => 'fa fa-file-text-o', 'ordering' => 2, 'published' => 'Yes'),
                array('page_name' => 'Bulk Send Center', 'action_name' => 'bulk', 'icon' => 'fa fa-send', 'ordering' => 3, 'published' => 'Yes'),
                array('page_name' => 'Message Queue', 'action_name' => 'queue', 'icon' => 'fa fa-tasks', 'ordering' => 4, 'published' => 'Yes'),
                array('page_name' => 'Communication Logs', 'action_name' => 'logs', 'icon' => 'fa fa-history', 'ordering' => 5, 'published' => 'Yes'),
                array('page_name' => 'Communication Timeline', 'action_name' => 'timeline', 'icon' => 'fa fa-list-alt', 'ordering' => 6, 'published' => 'Yes'),
                array('page_name' => 'Add Communication Template', 'action_name' => 'templateadd', 'icon' => 'fa fa-plus', 'ordering' => 20, 'published' => 'No'),
                array('page_name' => 'Edit Communication Template', 'action_name' => 'templateedit', 'icon' => 'fa fa-edit', 'ordering' => 21, 'published' => 'No'),
                array('page_name' => 'Preview Communication Template', 'action_name' => 'templatepreview', 'icon' => 'fa fa-eye', 'ordering' => 22, 'published' => 'No'),
                array('page_name' => 'Change Communication Template Status', 'action_name' => 'templatestatus', 'icon' => 'fa fa-toggle-on', 'ordering' => 23, 'published' => 'No'),
                array('page_name' => 'Add Automation Rule', 'action_name' => 'ruleadd', 'icon' => 'fa fa-plus', 'ordering' => 24, 'published' => 'No'),
                array('page_name' => 'Edit Automation Rule', 'action_name' => 'ruleedit', 'icon' => 'fa fa-edit', 'ordering' => 25, 'published' => 'No'),
                array('page_name' => 'Change Automation Rule Status', 'action_name' => 'rulestatus', 'icon' => 'fa fa-toggle-on', 'ordering' => 26, 'published' => 'No'),
                array('page_name' => 'Run Communication Queue', 'action_name' => 'runqueue', 'icon' => 'fa fa-play', 'ordering' => 27, 'published' => 'No'),
                array('page_name' => 'Retry Communication Queue', 'action_name' => 'retry', 'icon' => 'fa fa-refresh', 'ordering' => 28, 'published' => 'No')
            );
            $pageIds = array($rootId);
            foreach($pages as $pageRow)
            {
                $existing = (array)$Page->find('first', array(
                    'fields' => array('Page.id'),
                    'conditions' => array(
                        'LOWER(Page.controller_name)' => 'communicationautomations',
                        'LOWER(Page.action_name)' => strtolower($pageRow['action_name'])
                    ),
                    'recursive' => -1
                ));
                $saveData = array(
                    'model_name' => 'Communication Automation',
                    'page_name' => $pageRow['page_name'],
                    'controller_name' => 'Communicationautomations',
                    'action_name' => $pageRow['action_name'],
                    'icon' => $pageRow['icon'],
                    'parent_id' => $rootId,
                    'ordering' => $pageRow['ordering'],
                    'published' => $pageRow['published'],
                    'sel_name' => 'communication_templates,communication_rules,communication_queue,communication_logs'
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
            $groups = (array)$Ugroup->find('all', array(
                'fields' => array('Ugroup.id', 'Ugroup.type', 'Ugroup.name'),
                'recursive' => -1
            ));
            foreach($groups as $groupRow)
            {
                $group = !empty($groupRow['Ugroup']) ? (array)$groupRow['Ugroup'] : array();
                $groupId = !empty($group['id']) ? (int)$group['id'] : 0;
                if($groupId <= 0)
                {
                    continue;
                }
                $type = strtoupper(trim((string)(isset($group['type']) ? $group['type'] : '')));
                $name = strtoupper(trim((string)(isset($group['name']) ? $group['name'] : '')));
                $grant = ($groupId === 1 || in_array($type, array('ADR', 'AGY'), true) || preg_match('/\bSUPER\b.*\bADMIN\b/', $name));
                foreach($pageIds as $pageId)
                {
                    $existingRight = (array)$PageRight->find('first', array(
                        'fields' => array('PageRight.id'),
                        'conditions' => array(
                            'PageRight.page_id' => (int)$pageId,
                            'PageRight.ugroup_id' => $groupId
                        ),
                        'recursive' => -1
                    ));
                    $right = array(
                        'page_id' => (int)$pageId,
                        'ugroup_id' => $groupId,
                        'view_right' => $grant ? 1 : 0,
                        'save_right' => $grant ? 1 : 0,
                        'update_right' => $grant ? 1 : 0,
                        'search_right' => $grant ? 1 : 0
                    );
                    if(!empty($existingRight['PageRight']['id']))
                    {
                        $right['id'] = (int)$existingRight['PageRight']['id'];
                    }
                    $PageRight->create();
                    $PageRight->save(array('PageRight' => $right), array('validate' => false));
                }
            }
        }
        catch(Throwable $e)
        {
            // Menu registration must not block the admin panel.
        }
    }

    protected function _ensureRecoveryMenu()
    {
        $common = array(
            'grant_group_types' => array('ADR', 'AGY')
        );
        $this->_ensureSimpleRootAdminPage(array_merge($common, array(
            'model_name' => 'Payment Recovery',
            'page_name' => 'Payment Recovery',
            'controller_name' => 'Recoveries',
            'action_name' => 'index',
            'icon' => 'fa fa-phone',
            'ordering' => 16,
            'sel_name' => 'recovery_cases,recovery_followups,recovery_officers,plans_payments'
        )));
        $this->_ensureSimpleRootAdminPage(array_merge($common, array(
            'model_name' => 'Recovery Officers',
            'page_name' => 'Recovery Officers',
            'controller_name' => 'Recoveries',
            'action_name' => 'officers',
            'icon' => 'fa fa-users',
            'ordering' => 16,
            'published' => 'No',
            'sel_name' => 'recovery_officers'
        )));
        $this->_ensureSimpleRootAdminPage(array_merge($common, array(
            'model_name' => 'Recovery Settings',
            'page_name' => 'Recovery Settings',
            'controller_name' => 'Recoveries',
            'action_name' => 'settings',
            'icon' => 'fa fa-cog',
            'ordering' => 16,
            'published' => 'No',
            'sel_name' => 'recovery_settings'
        )));
        $this->_ensureSimpleRootAdminPage(array_merge($common, array(
            'model_name' => 'Recovery Assign',
            'page_name' => 'Recovery Assign',
            'controller_name' => 'Recoveries',
            'action_name' => 'assign',
            'icon' => 'fa fa-user-plus',
            'ordering' => 16,
            'published' => 'No',
            'sel_name' => 'recovery_cases'
        )));
        $this->_ensureSimpleRootAdminPage(array_merge($common, array(
            'model_name' => 'Recovery Follow-up',
            'page_name' => 'Recovery Follow-up',
            'controller_name' => 'Recoveries',
            'action_name' => 'followup',
            'icon' => 'fa fa-phone-square',
            'ordering' => 16,
            'published' => 'No',
            'sel_name' => 'recovery_followups,recovery_cases'
        )));
        $this->_ensureSimpleRootAdminPage(array_merge($common, array(
            'model_name' => 'Delete Recovery Officer',
            'page_name' => 'Delete Recovery Officer',
            'controller_name' => 'Recoveries',
            'action_name' => 'deleteofficer',
            'icon' => 'fa fa-trash',
            'ordering' => 16,
            'published' => 'No',
            'sel_name' => 'recovery_officers'
        )));
    }

    protected function _ensureDocumentVaultMenu()
    {
        $this->_ensureSimpleRootAdminPage(array(
            'model_name' => 'Document Vault',
            'page_name' => 'Document Vault',
            'controller_name' => 'Documentvaults',
            'action_name' => 'index',
            'icon' => 'fa fa-lock',
            'ordering' => 16,
            'sel_name' => 'deal_documents'
        ));
    }

    protected function _ensureLetterApprovalMenu()
    {
        $this->_ensureSimpleRootAdminPage(array(
            'model_name' => 'Letters',
            'page_name' => 'Letter Approvals',
            'controller_name' => 'Realestateletters',
            'action_name' => 'approvals',
            'icon' => 'fa fa-check-square-o',
            'ordering' => 18,
            'sel_name' => 'real_estate_letters',
            'grant_group_types' => array('ADR', 'AGY')
        ));
        foreach(array('draftletter','approveletter','issueletter') as $approvalAction)
        {
            $this->_ensureSimpleRootAdminPage(array(
                'model_name' => 'Letters',
                'page_name' => Inflector::humanize($approvalAction),
                'controller_name' => 'Realestateletters',
                'action_name' => $approvalAction,
                'icon' => 'fa fa-check',
                'ordering' => 18,
                'published' => 'No',
                'sel_name' => 'real_estate_letters',
                'grant_group_types' => array('ADR', 'AGY')
            ));
        }
    }

    protected function _ensureSeoAssistantMenu()
    {
        $this->_ensureSimpleRootAdminPage(array(
            'model_name' => 'SEO',
            'page_name' => 'SEO Auto Suggestions',
            'controller_name' => 'Seoassistants',
            'action_name' => 'index',
            'icon' => 'fa fa-search',
            'ordering' => 19,
            'sel_name' => 'properties,projects,property_seos,agency_seos',
            'grant_group_types' => array('ADR', 'AGY')
        ));
    }

    protected function _ensureLeadDuplicateMenu()
    {
        $this->_ensureSimpleRootAdminPage(array(
            'model_name' => 'Leads',
            'page_name' => 'Duplicate Lead Detector',
            'controller_name' => 'Leadduplicates',
            'action_name' => 'index',
            'icon' => 'fa fa-clone',
            'ordering' => 20,
            'sel_name' => 'leads,clients',
            'grant_group_types' => array('ADR', 'AGY')
        ));
    }

    protected function _ensureAgentPerformanceMenu()
    {
        $this->_ensureSimpleRootAdminPage(array(
            'model_name' => 'Reports',
            'page_name' => 'Agent Performance',
            'controller_name' => 'Agentperformances',
            'action_name' => 'index',
            'icon' => 'fa fa-line-chart',
            'ordering' => 21,
            'sel_name' => 'leads,properties,deals,property_lead_clicks',
            'grant_group_types' => array('ADR', 'AGY')
        ));
    }

    protected function _ensureAddressHierarchyMenu()
    {
        $this->_ensureSimpleRootAdminPage(array(
            'model_name' => 'Address Hierarchy',
            'page_name' => 'Address Hierarchy',
            'controller_name' => 'Addresshierarchies',
            'action_name' => 'index',
            'icon' => 'fa fa-map-marker',
            'ordering' => 89,
            'sel_name' => 'address_hierarchies,configurations,clients,properties',
            'grant_group_types' => array('ADR', 'AGY')
        ));
    }

    protected function _ensureSimpleRootAdminPage($pageData)
    {
        try
        {
            $Page = ClassRegistry::init('Page');
            $PageRight = ClassRegistry::init('PageRight');
            $Ugroup = ClassRegistry::init('Ugroup');
            if(!$Page || !$PageRight || !$Ugroup || empty($pageData['controller_name']) || empty($pageData['action_name']))
            {
                return;
            }
            $existing = (array)$Page->find('first', array(
                'fields' => array('Page.id'),
                'conditions' => array(
                    'Page.parent_id' => 0,
                    'LOWER(Page.controller_name)' => strtolower((string)$pageData['controller_name']),
                    'LOWER(Page.action_name)' => strtolower((string)$pageData['action_name'])
                ),
                'order' => array('Page.id' => 'asc'),
                'recursive' => -1
            ));
            $data = array(
                'model_name' => !empty($pageData['model_name']) ? $pageData['model_name'] : $pageData['page_name'],
                'page_name' => !empty($pageData['page_name']) ? $pageData['page_name'] : $pageData['controller_name'],
                'controller_name' => $pageData['controller_name'],
                'action_name' => $pageData['action_name'],
                'icon' => !empty($pageData['icon']) ? $pageData['icon'] : 'fa fa-circle',
                'parent_id' => 0,
                'ordering' => !empty($pageData['ordering']) ? (int)$pageData['ordering'] : 99,
                'published' => !empty($pageData['published']) ? $pageData['published'] : 'Yes',
                'sel_name' => !empty($pageData['sel_name']) ? $pageData['sel_name'] : ''
            );
            if(!empty($existing['Page']['id']))
            {
                $data['id'] = (int)$existing['Page']['id'];
            }
            else
            {
                $maxRes = $Page->query("SELECT MAX(id) as max_id FROM pages");
                $maxId = !empty($maxRes[0][0]['max_id']) ? (int)$maxRes[0][0]['max_id'] : 1300;
                $data['id'] = max(1300, $maxId + 1);
            }
            $Page->create();
            $Page->save(array('Page' => $data), array('validate' => false));
            $pageId = !empty($data['id']) ? (int)$data['id'] : (int)$Page->id;
            if($pageId <= 0)
            {
                return;
            }
            $grantGroupTypes = !empty($pageData['grant_group_types']) ? (array)$pageData['grant_group_types'] : array();
            $grantGroupTypes = array_map('strtoupper', array_map('trim', $grantGroupTypes));
            $grantGroupIds = !empty($pageData['grant_group_ids']) ? (array)$pageData['grant_group_ids'] : array();
            $grantGroupIds = array_map('intval', $grantGroupIds);
            $grantAllGroups = !empty($pageData['grant_all_groups']);

            $groups = (array)$Ugroup->find('all', array(
                'fields' => array('Ugroup.id', 'Ugroup.type'),
                'recursive' => -1
            ));
            foreach($groups as $groupRow)
            {
                $groupId = !empty($groupRow['Ugroup']['id']) ? (int)$groupRow['Ugroup']['id'] : 0;
                $groupType = !empty($groupRow['Ugroup']['type']) ? strtoupper(trim((string)$groupRow['Ugroup']['type'])) : '';
                if($groupId <= 0)
                {
                    continue;
                }
                $grantFullRights = ($groupId === 1 || in_array($groupId, $grantGroupIds, true) || ($groupType !== '' && in_array($groupType, $grantGroupTypes, true)) || ($grantAllGroups && !in_array($groupType, array('AGT', 'RCO'), true)));
                $existingRight = (array)$PageRight->find('first', array(
                    'fields' => array('PageRight.id'),
                    'conditions' => array(
                        'PageRight.page_id' => $pageId,
                        'PageRight.ugroup_id' => $groupId
                    ),
                    'recursive' => -1
                ));
                if(!empty($existingRight['PageRight']['id']))
                {
                    if($groupId !== 1)
                    {
                        continue;
                    }
                }
                $rightData = array(
                    'page_id' => $pageId,
                    'ugroup_id' => $groupId,
                    'view_right' => $grantFullRights ? 1 : 0,
                    'save_right' => $grantFullRights ? 1 : 0,
                    'update_right' => $grantFullRights ? 1 : 0,
                    'search_right' => $grantFullRights ? 1 : 0
                );
                if(!empty($existingRight['PageRight']['id']))
                {
                    $rightData['id'] = (int)$existingRight['PageRight']['id'];
                }
                $PageRight->create();
                $PageRight->save(array('PageRight' => $rightData), array('validate' => false));
            }
        }
        catch(Throwable $e)
        {
            // Menu registration must not block the admin panel.
        }
    }

    protected function _ensureSimpleChildAdminPage($pageData)
    {
        try
        {
            $Page = ClassRegistry::init('Page');
            $PageRight = ClassRegistry::init('PageRight');
            $Ugroup = ClassRegistry::init('Ugroup');
            if(!$Page || !$PageRight || !$Ugroup || empty($pageData['controller_name']) || empty($pageData['action_name']))
            {
                return;
            }

            $parentId = 0;
            if(!empty($pageData['parent_id']))
            {
                $parentId = (int)$pageData['parent_id'];
            }
            elseif(!empty($pageData['parent_controller']))
            {
                $parentConds = array(
                    'LOWER(Page.controller_name)' => strtolower((string)$pageData['parent_controller'])
                );
                if(isset($pageData['parent_action']))
                {
                    $parentConds['LOWER(Page.action_name)'] = strtolower((string)$pageData['parent_action']);
                }
                $parentConds['Page.parent_id'] = 0;
                $parentPage = $Page->find('first', array(
                    'fields' => array('Page.id'),
                    'conditions' => $parentConds,
                    'recursive' => -1
                ));
                if(!empty($parentPage['Page']['id']))
                {
                    $parentId = (int)$parentPage['Page']['id'];
                }
            }

            $existingConds = array(
                'LOWER(Page.controller_name)' => strtolower((string)$pageData['controller_name']),
                'LOWER(Page.action_name)' => strtolower((string)$pageData['action_name'])
            );
            if ($parentId > 0) {
                $existingConds['Page.parent_id'] = $parentId;
            }
            $existing = (array)$Page->find('first', array(
                'fields' => array('Page.id'),
                'conditions' => $existingConds,
                'order' => array('Page.id' => 'asc'),
                'recursive' => -1
            ));

            $published = isset($pageData['published']) ? $pageData['published'] : 'Yes';
            if ($published === '1' || $published === 1) $published = 'Yes';
            if ($published === '0' || $published === 0) $published = 'No';

            $data = array(
                'model_name' => !empty($pageData['model_name']) ? $pageData['model_name'] : $pageData['page_name'],
                'page_name' => !empty($pageData['page_name']) ? $pageData['page_name'] : $pageData['controller_name'],
                'controller_name' => $pageData['controller_name'],
                'action_name' => $pageData['action_name'],
                'icon' => !empty($pageData['icon']) ? $pageData['icon'] : 'fa fa-circle',
                'parent_id' => $parentId,
                'ordering' => !empty($pageData['ordering']) ? (int)$pageData['ordering'] : 99,
                'published' => $published,
                'sel_name' => !empty($pageData['sel_name']) ? $pageData['sel_name'] : ''
            );
            if(!empty($existing['Page']['id']))
            {
                $data['id'] = (int)$existing['Page']['id'];
            }
            else
            {
                $maxRes = $Page->query("SELECT MAX(id) as max_id FROM pages");
                $maxId = !empty($maxRes[0][0]['max_id']) ? (int)$maxRes[0][0]['max_id'] : 1300;
                $data['id'] = max(1300, $maxId + 1);
            }
            $Page->create();
            $Page->save(array('Page' => $data), array('validate' => false));
            $pageId = !empty($data['id']) ? (int)$data['id'] : (int)$Page->id;
            if($pageId <= 0)
            {
                return;
            }

            $grantGroupTypes = !empty($pageData['grant_group_types']) ? (array)$pageData['grant_group_types'] : array();
            $grantGroupTypes = array_map('strtoupper', array_map('trim', $grantGroupTypes));
            $grantGroupIds = !empty($pageData['grant_group_ids']) ? (array)$pageData['grant_group_ids'] : array();
            $grantGroupIds = array_map('intval', $grantGroupIds);
            $grantAllGroups = !empty($pageData['grant_all_groups']);

            $groups = (array)$Ugroup->find('all', array(
                'fields' => array('Ugroup.id', 'Ugroup.type'),
                'recursive' => -1
            ));
            foreach($groups as $groupRow)
            {
                $groupId = !empty($groupRow['Ugroup']['id']) ? (int)$groupRow['Ugroup']['id'] : 0;
                $groupType = !empty($groupRow['Ugroup']['type']) ? strtoupper(trim((string)$groupRow['Ugroup']['type'])) : '';
                if($groupId <= 0)
                {
                    continue;
                }
                $grantFullRights = ($groupId === 1 || in_array($groupId, $grantGroupIds, true) || ($groupType !== '' && in_array($groupType, $grantGroupTypes, true)) || ($grantAllGroups && !in_array($groupType, array('AGT', 'RCO'), true)));
                $existingRight = (array)$PageRight->find('first', array(
                    'fields' => array('PageRight.id'),
                    'conditions' => array(
                        'PageRight.page_id' => $pageId,
                        'PageRight.ugroup_id' => $groupId
                    ),
                    'recursive' => -1
                ));
                if(!empty($existingRight['PageRight']['id']))
                {
                    if($groupId !== 1)
                    {
                        continue;
                    }
                }
                $rightData = array(
                    'page_id' => $pageId,
                    'ugroup_id' => $groupId,
                    'view_right' => $grantFullRights ? 1 : 0,
                    'save_right' => $grantFullRights ? 1 : 0,
                    'update_right' => $grantFullRights ? 1 : 0,
                    'search_right' => $grantFullRights ? 1 : 0
                );
                if(!empty($existingRight['PageRight']['id']))
                {
                    $rightData['id'] = (int)$existingRight['PageRight']['id'];
                }
                $PageRight->create();
                $PageRight->save(array('PageRight' => $rightData), array('validate' => false));
            }
        }
        catch(Throwable $e)
        {
            // Menu registration must not block the admin panel.
        }
    }

    protected function _ensureMediaGalleryMenu()
    {
        try
        {
            $Page = ClassRegistry::init('Page');
            $PageRight = ClassRegistry::init('PageRight');
            $Ugroup = ClassRegistry::init('Ugroup');
            if(!$Page || !$PageRight || !$Ugroup)
            {
                return;
            }

            $existing = (array)$Page->find('first', array(
                'fields' => array('Page.id'),
                'conditions' => array(
                    'LOWER(Page.controller_name)' => array('media_galleries', 'mediagalleries'),
                    'LOWER(Page.action_name)' => 'index'
                ),
                'order' => array('Page.id' => 'asc'),
                'recursive' => -1
            ));

            $pageData = array(
                'model_name' => 'Media',
                'page_name' => 'Media Gallery',
                'controller_name' => 'media_galleries',
                'action_name' => 'index',
                'icon' => 'fa fa-picture-o fa-fw',
                'parent_id' => 0,
                'ordering' => 14,
                'published' => 'Yes',
                'sel_name' => 'media_files,media_folders'
            );
            if(!empty($existing['Page']['id']))
            {
                $pageData['id'] = (int)$existing['Page']['id'];
            }
            $Page->create();
            $Page->save(array('Page' => $pageData), array('validate' => false));
            $pageId = !empty($pageData['id']) ? (int)$pageData['id'] : (int)$Page->id;
            if($pageId <= 0)
            {
                return;
            }

            $groups = (array)$Ugroup->find('list', array(
                'fields' => array('Ugroup.id', 'Ugroup.id'),
                'recursive' => -1
            ));
            foreach($groups as $groupId)
            {
                $groupId = (int)$groupId;
                if($groupId <= 0)
                {
                    continue;
                }
                $existingRight = (array)$PageRight->find('first', array(
                    'fields' => array('PageRight.id'),
                    'conditions' => array(
                        'PageRight.page_id' => $pageId,
                        'PageRight.ugroup_id' => $groupId
                    ),
                    'recursive' => -1
                ));
                $rightData = array(
                    'page_id' => $pageId,
                    'ugroup_id' => $groupId,
                    'view_right' => 1,
                    'save_right' => 1,
                    'update_right' => 1,
                    'search_right' => 1
                );
                if(!empty($existingRight['PageRight']['id']))
                {
                    $rightData['id'] = (int)$existingRight['PageRight']['id'];
                }
                $PageRight->create();
                $PageRight->save(array('PageRight' => $rightData), array('validate' => false));
            }
        }
        catch(Throwable $e)
        {
            // Menu bootstrap must not block the admin panel if the media page cannot be registered.
        }
    }

    protected function _ensurePropertyEnhancementsMenu()
    {
        $this->_ensureSimpleRootAdminPage(array(
            'model_name' => 'Property Enhancements',
            'page_name' => 'Property Enhancements',
            'controller_name' => 'Propertyenhancements',
            'action_name' => 'index',
            'icon' => 'fa fa-magic',
            'ordering' => 14,
            'sel_name' => 'properties,property_seos,property_lead_clicks,property_saved_searches,property_price_histories,property_quality_checks',
            'grant_group_types' => array('ADR', 'AGY')
        ));
    }

    protected function _ensureThemeSettingsMenu()
    {
        try
        {
            $Page = ClassRegistry::init('Page');
            $PageRight = ClassRegistry::init('PageRight');
            $Ugroup = ClassRegistry::init('Ugroup');
            if(!$Page || !$PageRight || !$Ugroup)
            {
                return;
            }

            $root = (array)$Page->find('first', array(
                'fields' => array('Page.id'),
                'conditions' => array(
                    'Page.parent_id' => 0,
                    'OR' => array(
                        array('LOWER(Page.page_name)' => 'configurations'),
                        array('LOWER(Page.model_name)' => 'configuration'),
                        array('LOWER(Page.controller_name)' => 'configurations')
                    )
                ),
                'order' => array('Page.id' => 'asc'),
                'recursive' => -1
            ));
            $rootId = !empty($root['Page']['id']) ? (int)$root['Page']['id'] : 0;
            if($rootId <= 0)
            {
                $Page->create();
                $Page->save(array('Page' => array(
                    'model_name' => 'Configuration',
                    'page_name' => 'Configurations',
                    'controller_name' => 'Configurations',
                    'action_name' => 'index',
                    'icon' => 'fa fa-cogs fa-fw',
                    'parent_id' => 0,
                    'ordering' => 88,
                    'published' => 'Yes',
                    'sel_name' => ''
                )), array('validate' => false));
                $rootId = (int)$Page->id;
            }
            if($rootId <= 0)
            {
                return;
            }

            $existing = (array)$Page->find('first', array(
                'fields' => array('Page.id'),
                'conditions' => array(
                    'LOWER(Page.controller_name)' => 'themesettings',
                    'LOWER(Page.action_name)' => 'index'
                ),
                'order' => array('Page.id' => 'asc'),
                'recursive' => -1
            ));
            $pageData = array(
                'model_name' => 'Configuration',
                'page_name' => 'Theme Settings',
                'controller_name' => 'Themesettings',
                'action_name' => 'index',
                'icon' => 'fa fa-paint-brush',
                'parent_id' => $rootId,
                'ordering' => 3,
                'published' => 'Yes',
                'sel_name' => ''
            );
            if(!empty($existing['Page']['id']))
            {
                $pageData['id'] = (int)$existing['Page']['id'];
            }
            $Page->create();
            $Page->save(array('Page' => $pageData), array('validate' => false));
            $pageId = !empty($pageData['id']) ? (int)$pageData['id'] : (int)$Page->id;
            if($pageId <= 0)
            {
                return;
            }

            $sourceRights = array();
            $generalPage = (array)$Page->find('first', array(
                'fields' => array('Page.id'),
                'conditions' => array(
                    'LOWER(Page.controller_name)' => 'configurations',
                    'LOWER(Page.action_name)' => 'index'
                ),
                'order' => array('Page.id' => 'asc'),
                'recursive' => -1
            ));
            if(!empty($generalPage['Page']['id']))
            {
                $sourceRights = (array)$PageRight->find('all', array(
                    'conditions' => array('PageRight.page_id' => (int)$generalPage['Page']['id']),
                    'fields' => array('PageRight.ugroup_id','PageRight.view_right','PageRight.save_right','PageRight.update_right','PageRight.search_right'),
                    'recursive' => -1
                ));
            }
            if(empty($sourceRights))
            {
                $groups = (array)$Ugroup->find('list', array('fields' => array('Ugroup.id','Ugroup.id'), 'recursive' => -1));
                foreach($groups as $groupId)
                {
                    $isSuperGroup = ((int)$groupId === 1);
                    $sourceRights[] = array('PageRight' => array(
                        'ugroup_id' => (int)$groupId,
                        'view_right' => $isSuperGroup ? 1 : 0,
                        'save_right' => $isSuperGroup ? 1 : 0,
                        'update_right' => $isSuperGroup ? 1 : 0,
                        'search_right' => $isSuperGroup ? 1 : 0
                    ));
                }
            }
            foreach($sourceRights as $rightRow)
            {
                $right = !empty($rightRow['PageRight']) ? (array)$rightRow['PageRight'] : (array)$rightRow;
                $groupId = !empty($right['ugroup_id']) ? (int)$right['ugroup_id'] : 0;
                if($groupId <= 0)
                {
                    continue;
                }
                $existingRight = (array)$PageRight->find('first', array(
                    'fields' => array('PageRight.id'),
                    'conditions' => array('PageRight.page_id' => $pageId, 'PageRight.ugroup_id' => $groupId),
                    'recursive' => -1
                ));
                $saveRight = array(
                    'page_id' => $pageId,
                    'ugroup_id' => $groupId,
                    'view_right' => !empty($right['view_right']) ? 1 : 0,
                    'save_right' => !empty($right['save_right']) ? 1 : 0,
                    'update_right' => !empty($right['update_right']) ? 1 : 0,
                    'search_right' => !empty($right['search_right']) ? 1 : 0
                );
                if(!empty($existingRight['PageRight']['id']))
                {
                    $saveRight['id'] = (int)$existingRight['PageRight']['id'];
                }
                $PageRight->create();
                $PageRight->save(array('PageRight' => $saveRight), array('validate' => false));
            }
            $adminRight = (array)$PageRight->find('first', array(
                'fields' => array('PageRight.id'),
                'conditions' => array('PageRight.page_id' => $pageId, 'PageRight.ugroup_id' => 1),
                'recursive' => -1
            ));
            $adminSave = array(
                'page_id' => $pageId,
                'ugroup_id' => 1,
                'view_right' => 1,
                'save_right' => 1,
                'update_right' => 1,
                'search_right' => 1
            );
            if(!empty($adminRight['PageRight']['id']))
            {
                $adminSave['id'] = (int)$adminRight['PageRight']['id'];
            }
            $PageRight->create();
            $PageRight->save(array('PageRight' => $adminSave), array('validate' => false));
        }
        catch(Throwable $e)
        {
            // Keep normal admin flow safe if theme menu bootstrap fails.
        }
    }
    protected function _ensureAgencyPaymentPlanSettingsMenu()
    {
        try
        {
            $Page = ClassRegistry::init('Page');
            $PageRight = ClassRegistry::init('PageRight');
            $Ugroup = ClassRegistry::init('Ugroup');
            if(!$Page || !$PageRight || !$Ugroup)
            {
                return;
            }

            $root = (array)$Page->find('first', array(
                'fields' => array('Page.id'),
                'conditions' => array(
                    'Page.parent_id' => 0,
                    'OR' => array(
                        array('LOWER(Page.page_name)' => 'configurations'),
                        array('LOWER(Page.model_name)' => 'configuration'),
                        array('LOWER(Page.controller_name)' => 'configurations')
                    )
                ),
                'order' => array('Page.id' => 'asc'),
                'recursive' => -1
            ));
            $rootId = !empty($root['Page']['id']) ? (int)$root['Page']['id'] : 0;
            if($rootId <= 0)
            {
                $Page->create();
                $Page->save(array('Page' => array(
                    'model_name' => 'Configuration',
                    'page_name' => 'Configurations',
                    'controller_name' => 'Configurations',
                    'action_name' => 'index',
                    'icon' => 'fa fa-cogs fa-fw',
                    'parent_id' => 0,
                    'ordering' => 88,
                    'published' => 'Yes',
                    'sel_name' => ''
                )), array('validate' => false));
                $rootId = (int)$Page->id;
            }
            if($rootId <= 0)
            {
                return;
            }

            $existing = (array)$Page->find('first', array(
                'fields' => array('Page.id'),
                'conditions' => array(
                    'OR' => array(
                        array(
                            'LOWER(Page.controller_name)' => array('paymentplansettings', 'payment_plan_settings'),
                            'LOWER(Page.action_name)' => 'index'
                        ),
                        array(
                            'LOWER(Page.controller_name)' => 'configurations',
                            'LOWER(Page.action_name)' => 'paymentplans'
                        )
                    )
                ),
                'order' => array('Page.id' => 'asc'),
                'recursive' => -1
            ));
            $pageData = array(
                'model_name' => 'Configuration',
                'page_name' => 'Payment Plan Settings',
                'controller_name' => 'PaymentPlanSettings',
                'action_name' => 'index',
                'icon' => 'fa fa-list-alt',
                'parent_id' => $rootId,
                'ordering' => 4,
                'published' => 'Yes',
                'sel_name' => ''
            );
            if(!empty($existing['Page']['id']))
            {
                $pageData['id'] = (int)$existing['Page']['id'];
            }
            $Page->create();
            $Page->save(array('Page' => $pageData), array('validate' => false));
            $pageId = !empty($pageData['id']) ? (int)$pageData['id'] : (int)$Page->id;
            if($pageId <= 0)
            {
                return;
            }

            $sourceRights = array();
            $generalPage = (array)$Page->find('first', array(
                'fields' => array('Page.id'),
                'conditions' => array(
                    'LOWER(Page.controller_name)' => 'configurations',
                    'LOWER(Page.action_name)' => 'index'
                ),
                'order' => array('Page.id' => 'asc'),
                'recursive' => -1
            ));
            if(!empty($generalPage['Page']['id']))
            {
                $sourceRights = (array)$PageRight->find('all', array(
                    'conditions' => array('PageRight.page_id' => (int)$generalPage['Page']['id']),
                    'fields' => array('PageRight.ugroup_id','PageRight.view_right','PageRight.save_right','PageRight.update_right','PageRight.search_right'),
                    'recursive' => -1
                ));
            }
            if(empty($sourceRights))
            {
                $groups = (array)$Ugroup->find('list', array('fields' => array('Ugroup.id','Ugroup.id'), 'recursive' => -1));
                foreach($groups as $groupId)
                {
                    $isSuperGroup = ((int)$groupId === 1);
                    $sourceRights[] = array('PageRight' => array(
                        'ugroup_id' => (int)$groupId,
                        'view_right' => $isSuperGroup ? 1 : 0,
                        'save_right' => $isSuperGroup ? 1 : 0,
                        'update_right' => $isSuperGroup ? 1 : 0,
                        'search_right' => $isSuperGroup ? 1 : 0
                    ));
                }
            }
            foreach($sourceRights as $rightRow)
            {
                $right = !empty($rightRow['PageRight']) ? (array)$rightRow['PageRight'] : (array)$rightRow;
                $groupId = !empty($right['ugroup_id']) ? (int)$right['ugroup_id'] : 0;
                if($groupId <= 0)
                {
                    continue;
                }
                $existingRight = (array)$PageRight->find('first', array(
                    'fields' => array('PageRight.id'),
                    'conditions' => array('PageRight.page_id' => $pageId, 'PageRight.ugroup_id' => $groupId),
                    'recursive' => -1
                ));
                $saveRight = array(
                    'page_id' => $pageId,
                    'ugroup_id' => $groupId,
                    'view_right' => !empty($right['view_right']) ? 1 : 0,
                    'save_right' => !empty($right['save_right']) ? 1 : 0,
                    'update_right' => !empty($right['update_right']) ? 1 : 0,
                    'search_right' => !empty($right['search_right']) ? 1 : 0
                );
                if(!empty($existingRight['PageRight']['id']))
                {
                    $saveRight['id'] = (int)$existingRight['PageRight']['id'];
                }
                $PageRight->create();
                $PageRight->save(array('PageRight' => $saveRight), array('validate' => false));
            }
            $adminRight = (array)$PageRight->find('first', array(
                'fields' => array('PageRight.id'),
                'conditions' => array('PageRight.page_id' => $pageId, 'PageRight.ugroup_id' => 1),
                'recursive' => -1
            ));
            $adminSave = array(
                'page_id' => $pageId,
                'ugroup_id' => 1,
                'view_right' => 1,
                'save_right' => 1,
                'update_right' => 1,
                'search_right' => 1
            );
            if(!empty($adminRight['PageRight']['id']))
            {
                $adminSave['id'] = (int)$adminRight['PageRight']['id'];
            }
            $PageRight->create();
            $PageRight->save(array('PageRight' => $adminSave), array('validate' => false));
        }
        catch(Throwable $e)
        {
            // Keep normal admin flow safe if payment-plan menu registration fails.
        }
    }
    protected function _ensureUserLevelPageRightsCoverage()
    {
        try
        {
            $PageRight = ClassRegistry::init('PageRight');
            if(!$PageRight)
            {
                return;
            }
            $PageRight->query(
                "INSERT IGNORE INTO `page_rights` (`page_id`,`ugroup_id`,`view_right`,`save_right`,`update_right`,`search_right`)
                 SELECT DISTINCT `Page`.`id`, `Ugroup`.`id`, 0, 0, 0, 0
                 FROM `pages` `Page`
                 INNER JOIN `ugroups` `Ugroup` ON `Ugroup`.`id` > 1
                 LEFT JOIN `page_rights` `PageRight`
                    ON `PageRight`.`page_id` = `Page`.`id`
                   AND `PageRight`.`ugroup_id` = `Ugroup`.`id`
                 WHERE `Page`.`page_name` IS NOT NULL
                   AND TRIM(`Page`.`page_name`) <> ''
                   AND `PageRight`.`id` IS NULL"
            );
            // Super admin (ugroup_id=1) always gets full rights on all pages.
            $PageRight->query(
                "INSERT IGNORE INTO `page_rights` (`page_id`,`ugroup_id`,`view_right`,`save_right`,`update_right`,`search_right`)
                 SELECT DISTINCT `Page`.`id`, 1, 1, 1, 1, 1
                 FROM `pages` `Page`
                 LEFT JOIN `page_rights` `PageRight`
                    ON `PageRight`.`page_id` = `Page`.`id`
                   AND `PageRight`.`ugroup_id` = 1
                 WHERE `Page`.`page_name` IS NOT NULL
                   AND TRIM(`Page`.`page_name`) <> ''
                   AND `PageRight`.`id` IS NULL"
            );
            $PageRight->query(
                "UPDATE `page_rights`
                 SET `view_right`=1, `save_right`=1, `update_right`=1, `search_right`=1
                 WHERE `ugroup_id`=1"
            );
        }
        catch(Throwable $e)
        {
            // Keep normal flow even if rights sync fails.
        }
    }
    protected function _ensureRecoveryOfficerRole()
    {
        try
        {
            $Ugroup = ClassRegistry::init('Ugroup');
            if(!$Ugroup)
            {
                return 0;
            }
            $row = (array)$Ugroup->find('first', array(
                'conditions' => array('OR' => array(
                    'Ugroup.type' => 'RCO',
                    'Ugroup.name' => 'Recovery Officer'
                )),
                'recursive' => -1
            ));
            if(!empty($row['Ugroup']['id']))
            {
                return (int)$row['Ugroup']['id'];
            }
            $Ugroup->create();
            if($Ugroup->save(array('Ugroup' => array(
                'name' => 'Recovery Officer',
                'type' => 'RCO',
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            )), array('validate' => false)))
            {
                $groupId = (int)$Ugroup->id;
                $this->_ensureUserLevelPageRightsCoverage();
                $this->_ensureRecoveryOfficerRoleRestrictions();
                return $groupId;
            }
        }
        catch(Throwable $e)
        {
        }
        return 0;
    }
    protected function _ensureRecoveryOfficerRoleRestrictions()
    {
        try
        {
            $Ugroup = ClassRegistry::init('Ugroup');
            $Page = ClassRegistry::init('Page');
            $PageRight = ClassRegistry::init('PageRight');
            if(!$Ugroup || !$Page || !$PageRight)
            {
                return;
            }
            $groups = (array)$Ugroup->find('all', array(
                'fields' => array('Ugroup.id'),
                'conditions' => array('OR' => array(
                    'Ugroup.type' => 'RCO',
                    'Ugroup.name' => 'Recovery Officer'
                )),
                'recursive' => -1
            ));
            if(empty($groups))
            {
                return;
            }
            $groupIds = array();
            foreach($groups as $group)
            {
                if(!empty($group['Ugroup']['id']))
                {
                    $groupIds[] = (int)$group['Ugroup']['id'];
                }
            }
            $groupIds = array_values(array_unique(array_filter($groupIds)));
            if(empty($groupIds))
            {
                return;
            }

            $this->_ensureRecoveryOfficerHiddenPages($groupIds);
            $this->_ensureUserLevelPageRightsCoverage();

            foreach($groupIds as $groupId)
            {
                $PageRight->updateAll(
                    array(
                        'PageRight.view_right' => 0,
                        'PageRight.save_right' => 0,
                        'PageRight.update_right' => 0,
                        'PageRight.search_right' => 0
                    ),
                    array('PageRight.ugroup_id' => $groupId)
                );

                $allowedRows = (array)$Page->find('all', array(
                    'fields' => array('Page.id', 'Page.controller_name', 'Page.action_name'),
                    'conditions' => array('OR' => array(
                        array('LOWER(Page.controller_name)' => 'dashboards', 'LOWER(Page.action_name)' => 'index'),
                        array('LOWER(Page.controller_name)' => 'recoveries', 'LOWER(Page.action_name)' => array('index', 'assign', 'followup')),
                        array('LOWER(Page.controller_name)' => 'paymentreminders', 'LOWER(Page.action_name)' => array('sendsms', 'whatsapp', 'sendemail'))
                    )),
                    'recursive' => -1
                ));
                foreach($allowedRows as $pageRow)
                {
                    $pageId = !empty($pageRow['Page']['id']) ? (int)$pageRow['Page']['id'] : 0;
                    if($pageId <= 0)
                    {
                        continue;
                    }
                    $existingRight = (array)$PageRight->find('first', array(
                        'fields' => array('PageRight.id'),
                        'conditions' => array('PageRight.page_id' => $pageId, 'PageRight.ugroup_id' => $groupId),
                        'recursive' => -1
                    ));
                    $rightData = array(
                        'page_id' => $pageId,
                        'ugroup_id' => $groupId,
                        'view_right' => 1,
                        'save_right' => 1,
                        'update_right' => 1,
                        'search_right' => 0
                    );
                    if(!empty($existingRight['PageRight']['id']))
                    {
                        $rightData['id'] = (int)$existingRight['PageRight']['id'];
                    }
                    $PageRight->create();
                    $PageRight->save(array('PageRight' => $rightData), array('validate' => false));
                }
                $this->_bumpMenuPermissionVersion($groupId);
            }
        }
        catch(Throwable $e)
        {
        }
    }
    protected function _ensureRecoveryOfficerHiddenPages($groupIds = array())
    {
        $groupIds = array_values(array_unique(array_map('intval', (array)$groupIds)));
        $hiddenPages = array(
            array('page_name' => 'Send Recovery SMS', 'controller_name' => 'Paymentreminders', 'action_name' => 'sendsms', 'icon' => 'fa fa-comment', 'sel_name' => 'payment_reminders'),
            array('page_name' => 'Send Recovery WhatsApp', 'controller_name' => 'Paymentreminders', 'action_name' => 'whatsapp', 'icon' => 'fa fa-whatsapp', 'sel_name' => 'payment_reminders'),
            array('page_name' => 'Send Recovery Email', 'controller_name' => 'Paymentreminders', 'action_name' => 'sendemail', 'icon' => 'fa fa-envelope', 'sel_name' => 'payment_reminders')
        );
        foreach($hiddenPages as $hiddenPage)
        {
            $this->_ensureSimpleRootAdminPage(array(
                'model_name' => 'Payment Recovery',
                'page_name' => $hiddenPage['page_name'],
                'controller_name' => $hiddenPage['controller_name'],
                'action_name' => $hiddenPage['action_name'],
                'icon' => $hiddenPage['icon'],
                'ordering' => 99,
                'published' => 'No',
                'sel_name' => $hiddenPage['sel_name'],
                'grant_group_ids' => $groupIds
            ));
        }
    }
    protected function _removeMiscoldMenu()
    {
        try
        {
            $Page = ClassRegistry::init('Page');
            $PageRight = ClassRegistry::init('PageRight');
            if(!$Page || !$PageRight)
            {
                return;
            }
            $legacyPageIds = array();
            $legacyRootRows = (array)$Page->find('all', array(
                'fields' => array('Page.id'),
                'conditions' => array(
                    'OR' => array(
                        array('LOWER(Page.page_name)' => 'misc/old'),
                        array('LOWER(Page.page_name)' => 'miscold'),
                        array('LOWER(Page.model_name)' => 'misc/old'),
                        array('LOWER(Page.model_name)' => 'miscold')
                    )
                ),
                'recursive' => -1
            ));
            $legacyRootIds = array();
            foreach((array)$legacyRootRows as $legacyRootRow)
            {
                $legacyRootId = isset($legacyRootRow['Page']['id']) ? (int)$legacyRootRow['Page']['id'] : 0;
                if($legacyRootId > 0)
                {
                    $legacyRootIds[$legacyRootId] = $legacyRootId;
                    $legacyPageIds[$legacyRootId] = $legacyRootId;
                }
            }
            if(!empty($legacyRootIds))
            {
                $legacyChildRows = (array)$Page->find('all', array(
                    'fields' => array('Page.id'),
                    'conditions' => array('Page.parent_id' => array_values($legacyRootIds)),
                    'recursive' => -1
                ));
                foreach((array)$legacyChildRows as $legacyChildRow)
                {
                    $legacyChildId = isset($legacyChildRow['Page']['id']) ? (int)$legacyChildRow['Page']['id'] : 0;
                    if($legacyChildId > 0)
                    {
                        $legacyPageIds[$legacyChildId] = $legacyChildId;
                    }
                }
            }
            $legacyControllerRows = (array)$Page->find('all', array(
                'fields' => array('Page.id'),
                'conditions' => array(
                    'LOWER(Page.controller_name)' => array(
                        'expense_categories',
                        'vendors',
                        'expenses',
                        'expenses_payments',
                        'inventories',
                        'inventories_balances'
                    )
                ),
                'recursive' => -1
            ));
            foreach((array)$legacyControllerRows as $legacyControllerRow)
            {
                $legacyControllerId = isset($legacyControllerRow['Page']['id']) ? (int)$legacyControllerRow['Page']['id'] : 0;
                if($legacyControllerId > 0)
                {
                    $legacyPageIds[$legacyControllerId] = $legacyControllerId;
                }
            }
            $legacyCategoryRows = (array)$Page->find('all', array(
                'fields' => array('Page.id'),
                'conditions' => array(
                    'LOWER(Page.controller_name)' => 'categories',
                    'LOWER(Page.page_name)' => 'category',
                    'LOWER(Page.model_name)' => 'configuration'
                ),
                'recursive' => -1
            ));
            foreach((array)$legacyCategoryRows as $legacyCategoryRow)
            {
                $legacyCategoryId = isset($legacyCategoryRow['Page']['id']) ? (int)$legacyCategoryRow['Page']['id'] : 0;
                if($legacyCategoryId > 0)
                {
                    $legacyPageIds[$legacyCategoryId] = $legacyCategoryId;
                }
            }
            if(empty($legacyPageIds))
            {
                return;
            }
            $legacyPageIds = array_values(array_unique(array_map('intval', (array)$legacyPageIds)));
            $PageRight->deleteAll(array('PageRight.page_id' => $legacyPageIds), false);
            $Page->deleteAll(array('Page.id' => $legacyPageIds), false);
        }
        catch(Throwable $e)
        {
            // keep app flow safe if menu cleanup fails
        }
    }
    protected function _maybeRunAdminMenuBootstrap($sessionUser = array())
    {
        $sessionUser = is_array($sessionUser) ? $sessionUser : array();
        if(empty($sessionUser['User']['id']))
        {
            return;
        }
        $bootstrapKey = 'Admin.menu_bootstrap_version';
        $storedVersion = (string)$this->Session->read($bootstrapKey);
        $forceBootstrap = !empty($this->request->query['refresh_menu']);
        if($forceBootstrap || $storedVersion !== (string)$this->_menuBootstrapVersion)
        {
            $this->_runAdminMenuBootstrap();
            $this->Session->write($bootstrapKey, (string)$this->_menuBootstrapVersion);
        }
    }
    protected function _adminMenuCacheSessionKey($sessionUser = array())
    {
        $sessionUser = is_array($sessionUser) ? $sessionUser : array();
        $userId = !empty($sessionUser['User']['id']) ? (int)$sessionUser['User']['id'] : 0;
        $ugroupId = !empty($sessionUser['User']['ugroup_id']) ? (int)$sessionUser['User']['ugroup_id'] : 0;
        $lang = strtolower(trim((string)Configure::read('Config.language')));
        if($lang === '')
        {
            $lang = 'default';
        }
        $permissionVersion = $this->_menuPermissionVersion($sessionUser);
        return 'Admin.menu_cache.'.$userId.'.'.$ugroupId.'.'.$lang.'.'.$this->_menuBootstrapVersion.'.'.$this->_menuCacheVersion.'.'.$permissionVersion;
    }
    protected function _menuPermissionVersion($sessionUser = array())
    {
        $sessionUser = is_array($sessionUser) ? $sessionUser : array();
        $ugroupId = !empty($sessionUser['User']['ugroup_id']) ? (int)$sessionUser['User']['ugroup_id'] : 0;
        if($ugroupId <= 0)
        {
            return 'p0';
        }
        $cacheKey = 'admin_menu_perm_ver_u'.$ugroupId;
        $version = Cache::read($cacheKey, '_cake_core_');
        if($version === false || $version === null || trim((string)$version) === '')
        {
            $version = '1';
            Cache::write($cacheKey, $version, '_cake_core_');
        }
        return 'p'.preg_replace('/[^A-Za-z0-9_\-\.]/', '', (string)$version);
    }
    protected function _bumpMenuPermissionVersion($ugroupId = 0)
    {
        $ugroupId = (int)$ugroupId;
        if($ugroupId <= 0)
        {
            return;
        }
        $cacheKey = 'admin_menu_perm_ver_u'.$ugroupId;
        $version = str_replace(' ', '', sprintf('%.6f', microtime(true))).'_'.mt_rand(1000, 9999);
        Cache::write($cacheKey, $version, '_cake_core_');
    }
    protected function _buildAdminMenuTree()
    {
        $menu = array();
        $mainMenu = $this->userMenu('0');
        if($mainMenu)
        {
            $renderedItems = array();
            foreach($mainMenu as $value)
            {
                $parentId = (int)$value['Page']['id'];
                $childControllers = array();
                $menuPost = $this->userMenu($parentId);
                $subMenu = array();
                if($menuPost)
                {
                    foreach($menuPost as $menuValue)
                    {
                        $ctrl = trim((string)$menuValue['Page']['controller_name']);
                        $act = trim((string)$menuValue['Page']['action_name']);
                        if ($ctrl !== '') {
                            $childControllers[] = strtolower($ctrl);
                            $childControllers[] = Inflector::underscore($ctrl);
                        }
                        $childUrl = array('controller' => $ctrl, 'action' => $act, 'admin' => true);
                        if (strtolower($ctrl) === 'realestatethemes' || strtolower($ctrl) === 'real_estate_themes') {
                            $childUrl['plugin'] = 'RealEstateTheme';
                        } elseif (strpos(strtolower($ctrl), 'billing') === 0) {
                            $childUrl['plugin'] = 'BillingManager';
                        } elseif (strtolower($ctrl) === 'modules') {
                            $childUrl['plugin'] = 'admin';
                        } else {
                            $childUrl['plugin'] = 'admin';
                        }
                        $subMenu[] = array(array(
                            'title' => '<i class="'.$menuValue['Page']['icon'].'"></i><span>'.__($menuValue['Page']['page_name']).'</span>',
                            'url' => $childUrl,
                            'selName' => !empty($menuValue['Page']['sel_name']) ? $menuValue['Page']['sel_name'] : ''
                        ));
                    }
                    $subMenu = call_user_func_array('array_merge', $subMenu);
                }

                $rootCtrl = trim((string)$value['Page']['controller_name']);
                $rootAct = trim((string)$value['Page']['action_name']);

                // Determine if this page is a parent container with children in the DB
                $hasChildrenInDb = false;
                try {
                    $hasChildrenInDb = ($this->Page->find('count', array(
                        'conditions' => array('Page.parent_id' => $parentId, 'Page.published' => 'Yes'),
                        'recursive' => -1
                    )) > 0);
                } catch(Exception $e) {}

                // If this is a container module with children in DB, but user has 0 accessible children:
                // DO NOT RENDER THIS MODULE IN SIDEBAR!
                if ($hasChildrenInDb && empty($subMenu)) {
                    continue;
                }

                // If not a container, but has no controller to link to, DO NOT RENDER!
                if (!$hasChildrenInDb && $rootCtrl === '') {
                    continue;
                }

                if ($rootCtrl === '' || !empty($subMenu)) {
                    $rootUrl = null;
                } else {
                    $rootUrl = array('controller' => $rootCtrl, 'action' => $rootAct, 'admin' => true);
                    if (strtolower($rootCtrl) === 'realestatethemes' || strtolower($rootCtrl) === 'real_estate_themes') {
                        $rootUrl['plugin'] = 'RealEstateTheme';
                    } elseif (strpos(strtolower($rootCtrl), 'billing') === 0) {
                        $rootUrl['plugin'] = 'BillingManager';
                    } elseif (strtolower($rootCtrl) === 'modules') {
                        $rootUrl['plugin'] = 'admin';
                    } else {
                        $rootUrl['plugin'] = 'admin';
                    }
                }

                $parentSel = array();
                if (!empty($value['Page']['sel_name'])) {
                    $parentSel = array_map('trim', explode(',', (string)$value['Page']['sel_name']));
                }
                if (!empty($childControllers)) {
                    $parentSel = array_merge($parentSel, $childControllers);
                }
                $finalSelName = implode(',', array_unique(array_filter($parentSel)));

                $renderedItems[] = array(array(
                    'title' => '<i class="'.$value['Page']['icon'].'"></i><span>'.__($value['Page']['page_name']).'</span>',
                    'url' => $rootUrl,
                    'children' => !empty($subMenu) ? $subMenu : null,
                    'selName' => $finalSelName
                ));
            }
            if(!empty($renderedItems))
            {
                $menu = array('slide-out' => call_user_func_array('array_merge', $renderedItems));
            }
            else
            {
                $menu = array('slide-out' => array());
            }
        }
        return $menu;
    }
    protected function _loadAdminMenuTree($sessionUser = array())
    {
        $sessionUser = is_array($sessionUser) ? $sessionUser : array();
        if(empty($sessionUser['User']['id']))
        {
            return array();
        }
        $cacheKey = $this->_adminMenuCacheSessionKey($sessionUser);
        $forceRefresh = !empty($this->request->query['refresh_menu']) || !empty($this->request->query['refresh_nav']);
        if($forceRefresh)
        {
            $this->Session->delete($cacheKey);
        }
        $cachedMenu = $this->Session->read($cacheKey);
        if(is_array($cachedMenu) && !empty($cachedMenu['slide-out']))
        {
            return $cachedMenu;
        }
        $menu = $this->_buildAdminMenuTree();
        $this->Session->write($cacheKey, $menu);
        return $menu;
    }
    protected function _shouldRunControllerBootstrap($bootstrapKey, $bootstrapVersion = '1', $sessionUser = array())
    {
        $bootstrapKey = strtolower(trim((string)$bootstrapKey));
        $bootstrapVersion = trim((string)$bootstrapVersion);
        if($bootstrapKey === '')
        {
            return true;
        }
        if($bootstrapVersion === '')
        {
            $bootstrapVersion = '1';
        }
        $sessionUser = is_array($sessionUser) ? $sessionUser : (array)$this->Session->read('User');
        $userId = !empty($sessionUser['User']['id']) ? (int)$sessionUser['User']['id'] : 0;
        $sessionKey = 'Admin.bootstrap.'.$bootstrapKey.'.u'.$userId;
        if(isset($this->_controllerBootstrapCache[$sessionKey]))
        {
            return !empty($this->_controllerBootstrapCache[$sessionKey]);
        }
        $forceRefresh = !empty($this->request->query['refresh_schema']) || !empty($this->request->query['refresh_menu']);
        $storedVersion = (string)$this->Session->read($sessionKey);
        $shouldRun = ($forceRefresh || $storedVersion !== $bootstrapVersion);
        if($shouldRun)
        {
            $this->Session->write($sessionKey, $bootstrapVersion);
        }
        $this->_controllerBootstrapCache[$sessionKey] = $shouldRun ? 1 : 0;
        return $shouldRun;
    }
    protected function _ensureArdaccountsMenu()
    {
        try
        {
            $Page = ClassRegistry::init('Page');
            $PageRight = ClassRegistry::init('PageRight');
            $Ugroup = ClassRegistry::init('Ugroup');
            $rootConditions = array(
                'LOWER(Page.controller_name)' => 'ardaccounts',
                'LOWER(Page.action_name)' => 'dashboard',
                'Page.parent_id' => 0
            );
            $existing = $Page->find('first',array(
                'fields'=>array('Page.id','Page.parent_id','Page.action_name'),
                'conditions'=>$rootConditions,
                'order'=>array('Page.id'=>'asc')
            ));
            $rootPageData = array(
                'model_name' => 'Accounts',
                'page_name' => 'Accounts',
                'controller_name' => 'Ardaccounts',
                'action_name' => 'dashboard',
                'icon' => 'fa fa-calculator',
                'parent_id' => 0,
                'ordering' => 7,
                'published' => 'Yes',
                'sel_name' => ''
            );
            if(empty($existing['Page']['id']))
            {
                $Page->create();
                if(!$Page->save(array('Page'=>$rootPageData)))
                {
                    return;
                }
                $pageId = (int)$Page->id;
            }
            else
            {
                $pageId = (int)$existing['Page']['id'];
                $rootPageData['id'] = $pageId;
                $Page->save(array('Page'=>$rootPageData));
            }
            if($pageId<=0)
            {
                return;
            }

            $configRootPageId = 0;
            $configRootPage = $Page->find('first', array(
                'fields' => array('Page.id'),
                'conditions' => array(
                    'Page.parent_id' => 0,
                    'OR' => array(
                        array('LOWER(Page.controller_name)' => 'configurations'),
                        array('LOWER(Page.page_name)' => 'configurations')
                    )
                ),
                'order' => array('Page.id' => 'asc'),
                'recursive' => -1
            ));
            if(!empty($configRootPage['Page']['id']))
            {
                $configRootPageId = (int)$configRootPage['Page']['id'];
            }
            $keepBackupUnderAccounts = ($configRootPageId <= 0);

            $childMenus = array(
                array('name'=>'Dashboard','action'=>'dashboard','icon'=>'fa fa-dashboard','ordering'=>1),
                array('name'=>'Transactions','action'=>'transactions','icon'=>'fa fa-exchange','ordering'=>2),
                array('name'=>'Income Transactions','action'=>'incometransactions','icon'=>'fa fa-arrow-circle-down','ordering'=>3),
                array('name'=>'Expense Transactions','action'=>'expensetransactions','icon'=>'fa fa-credit-card','ordering'=>4),
                array('name'=>'Expense Approvals','action'=>'expenseapprovals','icon'=>'fa fa-check-square-o','ordering'=>5),
                array('name'=>'Ledgers','action'=>'ledgers','icon'=>'fa fa-book','ordering'=>6),
                array('name'=>'Reports','action'=>'reports','icon'=>'fa fa-line-chart','ordering'=>7),
                array('name'=>'Accounting','action'=>'accounting','icon'=>'fa fa-calculator','ordering'=>8),
                array('name'=>'Journal Voucher','action'=>'transfer','icon'=>'fa fa-exchange','ordering'=>9),
                array('name'=>'Vendors','action'=>'vendors','icon'=>'fa fa-users','ordering'=>10),
                array('name'=>'Construction','action'=>'construction','icon'=>'fa fa-building','ordering'=>11),
                array('name'=>'HR Payroll','action'=>'hrpayroll','icon'=>'fa fa-users','ordering'=>12),
                array('name'=>'Labour Control','action'=>'labourcontrol','icon'=>'fa fa-users','ordering'=>13),
                array('name'=>'Cash Closing','action'=>'cashclosing','icon'=>'fa fa-calculator','ordering'=>14),
                array('name'=>'Sites','action'=>'sites','icon'=>'fa fa-map-marker','ordering'=>15),
                array('name'=>'Stock Stores','action'=>'stockstores','icon'=>'fa fa-archive','ordering'=>16),
                array('name'=>'Stock Store','action'=>'stockstore','icon'=>'fa fa-archive','ordering'=>16),
                array('name'=>'Stock','action'=>'stock','icon'=>'fa fa-cubes','ordering'=>17),
                array('name'=>'Stock Out','action'=>'stockout','icon'=>'fa fa-sign-out','ordering'=>18),
                array('name'=>'Purchase Orders','action'=>'purchaseorders','icon'=>'fa fa-list-alt','ordering'=>19),
                array('name'=>'GRN','action'=>'grn','icon'=>'fa fa-truck','ordering'=>20),
                array('name'=>'Purchase Invoices','action'=>'purchaseinvoices','icon'=>'fa fa-file-o','ordering'=>21),
                array('name'=>'Purchase Payments','action'=>'purchasepayments','icon'=>'fa fa-money','ordering'=>22),
                array('name'=>'Reorder Rules','action'=>'reorderrules','icon'=>'fa fa-repeat','ordering'=>23),
                array('name'=>'Construction Projects','action'=>'constructionprojects','icon'=>'fa fa-building-o','ordering'=>24),
                array('name'=>'HR Payroll Entries','action'=>'hrpayrollentries','icon'=>'fa fa-list','ordering'=>25),
                array('name'=>'HR Payments','action'=>'hrpayments','icon'=>'fa fa-credit-card','ordering'=>26),
                array('name'=>'HR Employees','action'=>'hremployees','icon'=>'fa fa-id-badge','ordering'=>27),
                array('name'=>'Recurring','action'=>'recurring','icon'=>'fa fa-refresh','ordering'=>28),
                array('name'=>'Budgets','action'=>'budgets','icon'=>'fa fa-pie-chart','ordering'=>29),
                array('name'=>'AR/AP','action'=>'arap','icon'=>'fa fa-balance-scale','ordering'=>30),
                array('name'=>'Templates','action'=>'templates','icon'=>'fa fa-files-o','ordering'=>31),
                array('name'=>'Normalize Receipt Accounts','action'=>'normalizereceiptaccounts','icon'=>'fa fa-wrench','ordering'=>32),
                array('name'=>'Receipt Merge Entries','action'=>'receiptmergeentries','icon'=>'fa fa-random','ordering'=>33),
                array('name'=>'Manage','action'=>'index','icon'=>'fa fa-list','ordering'=>34)
            );
            $nextChildOrdering = 1;
            foreach((array)$childMenus as $childMenuRow)
            {
                $nextChildOrdering = max($nextChildOrdering, (int)$childMenuRow['ordering']);
            }
            if($keepBackupUnderAccounts)
            {
                $nextChildOrdering++;
                $childMenus[] = array('name'=>'Backups','action'=>'backups','icon'=>'fa fa-database','ordering'=>$nextChildOrdering);
            }
            $nextChildOrdering++;
            $childMenus[] = array('name'=>'Settings','action'=>'settings','icon'=>'fa fa-cog','ordering'=>$nextChildOrdering);
            $canonicalActionPageIds = array();
            $newChildActionIds = array();
            foreach($childMenus as $childMenu)
            {
                $childActionKey = strtolower((string)$childMenu['action']);
                $childConditions = array(
                    'LOWER(Page.controller_name)' => 'ardaccounts',
                    'LOWER(Page.action_name)' => $childActionKey,
                    'Page.parent_id' => $pageId
                );
                $childExisting = $Page->find('first',array(
                    'fields'=>array('Page.id'),
                    'conditions'=>$childConditions,
                    'order'=>array('Page.id'=>'asc'),
                    'recursive'=>-1
                ));
                $childData = array(
                    'model_name'=>'Accounts',
                    'page_name'=>$childMenu['name'],
                    'controller_name'=>'Ardaccounts',
                    'action_name'=>$childMenu['action'],
                    'icon'=>$childMenu['icon'],
                    'parent_id'=>$pageId,
                    'ordering'=>$childMenu['ordering'],
                    'published'=>'Yes',
                    'sel_name'=>''
                );
                if(empty($childExisting['Page']['id']))
                {
                    $Page->create();
                    if($Page->save(array('Page'=>$childData)))
                    {
                        $savedChildId = (int)$Page->id;
                        if($savedChildId > 0)
                        {
                            $canonicalActionPageIds[$childActionKey] = $savedChildId;
                            $newChildActionIds[$childActionKey] = $savedChildId;
                        }
                    }
                }
                else
                {
                    $savedChildId = (int)$childExisting['Page']['id'];
                    $childData['id'] = $savedChildId;
                    $Page->save(array('Page'=>$childData));
                    if($savedChildId > 0)
                    {
                        $canonicalActionPageIds[$childActionKey] = $savedChildId;
                    }
                }
            }
            // De-duplicate Accounts child pages by action to keep assign-rights clean and deterministic.
            $existingArdChildren = (array)$Page->find('all', array(
                'fields' => array('Page.id', 'Page.action_name'),
                'conditions' => array(
                    'LOWER(Page.controller_name)' => 'ardaccounts',
                    'Page.parent_id' => $pageId,
                    'Page.id <>' => $pageId
                ),
                'order' => array('LOWER(Page.action_name)' => 'asc', 'Page.id' => 'asc'),
                'recursive' => -1
            ));
            $seenChildAction = array();
            foreach((array)$existingArdChildren as $existingArdChild)
            {
                $existingArdChildId = isset($existingArdChild['Page']['id']) ? (int)$existingArdChild['Page']['id'] : 0;
                $existingArdAction = strtolower(trim((string)(isset($existingArdChild['Page']['action_name']) ? $existingArdChild['Page']['action_name'] : '')));
                if($existingArdChildId <= 0 || $existingArdAction === '')
                {
                    continue;
                }
                $canonicalActionId = isset($canonicalActionPageIds[$existingArdAction]) ? (int)$canonicalActionPageIds[$existingArdAction] : 0;
                if($canonicalActionId <= 0 && !isset($seenChildAction[$existingArdAction]))
                {
                    $canonicalActionId = $existingArdChildId;
                    $canonicalActionPageIds[$existingArdAction] = $existingArdChildId;
                }
                if($canonicalActionId > 0 && $existingArdChildId === $canonicalActionId)
                {
                    $seenChildAction[$existingArdAction] = $existingArdChildId;
                    continue;
                }
                if(isset($seenChildAction[$existingArdAction]) && (int)$seenChildAction[$existingArdAction] === $existingArdChildId)
                {
                    continue;
                }
                $PageRight->deleteAll(array('PageRight.page_id' => $existingArdChildId), false);
                $Page->delete($existingArdChildId, false);
            }

            $configBackupPageId = 0;
            if($configRootPageId > 0)
            {
                $existingBackupPage = $Page->find('first', array(
                    'fields' => array('Page.id'),
                    'conditions' => array(
                        'LOWER(Page.controller_name)' => 'ardaccounts',
                        'LOWER(Page.action_name)' => 'backups'
                    ),
                    'order' => array('Page.id' => 'asc'),
                    'recursive' => -1
                ));
                $backupData = array(
                    'model_name' => 'Configurations',
                    'page_name' => 'Backups',
                    'controller_name' => 'Ardaccounts',
                    'action_name' => 'backups',
                    'icon' => 'fa fa-database',
                    'parent_id' => $configRootPageId,
                    'ordering' => 99,
                    'published' => 'Yes',
                    'sel_name' => ''
                );
                if(!empty($existingBackupPage['Page']['id']))
                {
                    $configBackupPageId = (int)$existingBackupPage['Page']['id'];
                    $backupData['id'] = $configBackupPageId;
                    $Page->save(array('Page' => $backupData));
                }
                else
                {
                    $Page->create();
                    if($Page->save(array('Page' => $backupData)))
                    {
                        $configBackupPageId = (int)$Page->id;
                    }
                }
            }

            $systemUpdatePageId = 0;
            if($configRootPageId > 0)
            {
                $existingSysUpdate = $Page->find('first', array(
                    'fields' => array('Page.id'),
                    'conditions' => array(
                        'LOWER(Page.controller_name)' => 'systemupdates'
                    ),
                    'order' => array('Page.id' => 'asc'),
                    'recursive' => -1
                ));
                $sysUpdateData = array(
                    'model_name' => 'Configurations',
                    'page_name' => 'System Updates & Patches',
                    'controller_name' => 'SystemUpdates',
                    'action_name' => 'index',
                    'icon' => 'fa fa-cloud-upload',
                    'parent_id' => $configRootPageId,
                    'ordering' => 100,
                    'published' => 'Yes',
                    'sel_name' => 'systemupdates,system_updates'
                );
                if(!empty($existingSysUpdate['Page']['id']))
                {
                    $systemUpdatePageId = (int)$existingSysUpdate['Page']['id'];
                    $sysUpdateData['id'] = $systemUpdatePageId;
                    $Page->save(array('Page' => $sysUpdateData));
                }
                else
                {
                    $Page->create();
                    if($Page->save(array('Page' => $sysUpdateData)))
                    {
                        $systemUpdatePageId = (int)$Page->id;
                    }
                }
                if($systemUpdatePageId > 0)
                {
                    $rightCheck = $PageRight->find('first', array(
                        'conditions' => array('PageRight.page_id' => $systemUpdatePageId, 'PageRight.ugroup_id' => 1)
                    ));
                    if(empty($rightCheck['PageRight']['id']))
                    {
                        $PageRight->create();
                        $PageRight->save(array('PageRight' => array(
                            'page_id' => $systemUpdatePageId,
                            'ugroup_id' => 1,
                            'save_right' => 1,
                            'update_right' => 1,
                            'view_right' => 1,
                            'search_right' => 1
                        )));
                    }
                }
            }

            $trashRootPageId = 0;
            $trashRootConditions = array(
                'LOWER(Page.controller_name)' => 'ardaccounts',
                'LOWER(Page.action_name)' => 'trash',
                'Page.parent_id' => 0
            );
            $trashRootExisting = $Page->find('first', array(
                'fields' => array('Page.id'),
                'conditions' => $trashRootConditions,
                'recursive' => -1
            ));
            $trashRootData = array(
                'model_name' => 'Trash',
                'page_name' => 'Trash',
                'controller_name' => 'Ardaccounts',
                'action_name' => 'trash',
                'icon' => 'fa fa-trash',
                'parent_id' => 0,
                'ordering' => 13,
                'published' => 'Yes',
                'sel_name' => ''
            );
            if(empty($trashRootExisting['Page']['id']))
            {
                $Page->create();
                if($Page->save(array('Page' => $trashRootData)))
                {
                    $trashRootPageId = (int)$Page->id;
                }
            }
            else
            {
                $trashRootPageId = (int)$trashRootExisting['Page']['id'];
                $trashRootData['id'] = $trashRootPageId;
                $Page->save(array('Page' => $trashRootData));
            }

            // Keep sidebar compact: only show high-level modules, keep detailed pages accessible via module screens.
            $visibleActions = array('dashboard','transactions','incometransactions','expensetransactions','expenseapprovals','ledgers','reports','accounting','transfer','vendors','construction','hrpayroll','labourcontrol','cashclosing','settings');
            if($keepBackupUnderAccounts)
            {
                $visibleActions[] = 'backups';
            }
            $allArdPages = $Page->find('all', array(
                'fields' => array('Page.id','Page.action_name','Page.parent_id','Page.ordering'),
                'conditions' => array(
                    'LOWER(Page.controller_name)' => 'ardaccounts',
                    'Page.parent_id' => $pageId,
                    'Page.id <>' => $pageId
                ),
                'order' => array(
                    'Page.ordering' => 'asc',
                    'Page.id' => 'asc'
                ),
                'recursive' => -1
            ));
            $seenVisibleActions = array();
            foreach($allArdPages as $ardPage)
            {
                $ardPageId = (int)$ardPage['Page']['id'];
                if($ardPageId <= 0)
                {
                    continue;
                }
                if($trashRootPageId > 0 && $ardPageId === $trashRootPageId)
                {
                    continue;
                }
                if($configBackupPageId > 0 && $ardPageId === $configBackupPageId)
                {
                    continue;
                }
                $ardAction = strtolower((string)$ardPage['Page']['action_name']);
                if(in_array($ardAction, $visibleActions))
                {
                    $canonicalId = isset($canonicalActionPageIds[$ardAction]) ? (int)$canonicalActionPageIds[$ardAction] : 0;
                    if($canonicalId <= 0 && empty($seenVisibleActions[$ardAction]))
                    {
                        $canonicalId = $ardPageId;
                        $canonicalActionPageIds[$ardAction] = $ardPageId;
                    }
                    $isCanonical = ($canonicalId > 0 && $ardPageId === $canonicalId);
                    if($isCanonical)
                    {
                        $seenVisibleActions[$ardAction] = 1;
                        $Page->id = $ardPageId;
                        $Page->save(array('Page' => array(
                            'id' => $ardPageId,
                            'parent_id' => $pageId,
                            'published' => 'Yes'
                        )));
                    }
                    else
                    {
                        $Page->id = $ardPageId;
                        $Page->saveField('published', 'No');
                    }
                }
                else
                {
                    $Page->id = $ardPageId;
                    $Page->saveField('published', 'No');
                }
            }

            $menuPageIds = array($pageId => $pageId);
            $childPageIds = $Page->find('list',array(
                'fields'=>array('Page.id','Page.id'),
                'conditions'=>array('Page.parent_id'=>$pageId)
            ));
            foreach($childPageIds as $childPageId)
            {
                $menuPageIds[(int)$childPageId] = (int)$childPageId;
            }
            if($trashRootPageId > 0)
            {
                $menuPageIds[$trashRootPageId] = $trashRootPageId;
            }
            if($configRootPageId > 0)
            {
                $menuPageIds[$configRootPageId] = $configRootPageId;
            }
            if($configBackupPageId > 0)
            {
                $menuPageIds[$configBackupPageId] = $configBackupPageId;
            }

            $groups = $Ugroup->find('list',array('fields'=>array('id','id')));
            if(!empty($newChildActionIds['expenseapprovals']) && !empty($canonicalActionPageIds['dashboard']))
            {
                $dashboardRights = (array)$PageRight->find('all', array(
                    'conditions' => array('PageRight.page_id' => (int)$canonicalActionPageIds['dashboard']),
                    'recursive' => -1
                ));
                foreach($dashboardRights as $dashboardRightRow)
                {
                    $dashboardRight = !empty($dashboardRightRow['PageRight']) ? $dashboardRightRow['PageRight'] : array();
                    if(empty($dashboardRight['ugroup_id']))
                    {
                        continue;
                    }
                    unset($dashboardRight['id']);
                    $dashboardRight['page_id'] = (int)$newChildActionIds['expenseapprovals'];
                    $PageRight->create();
                    $PageRight->save(array('PageRight' => $dashboardRight), array('validate' => false));
                }
            }
            foreach($groups as $ugroupId)
            {
                if((int)$ugroupId===1)
                {
                    continue;
                }
                foreach($menuPageIds as $grantPageId)
                {
                    $exists = $PageRight->find('count',array(
                        'conditions'=>array(
                            'PageRight.page_id'=>(int)$grantPageId,
                            'PageRight.ugroup_id'=>(int)$ugroupId
                        )
                    ));
                    if($exists>0)
                    {
                        continue;
                    }
                    $PageRight->create();
                    $PageRight->save(array('PageRight'=>array(
                        'page_id'=>(int)$grantPageId,
                        'ugroup_id'=>(int)$ugroupId,
                        'view_right'=>0,
                        'save_right'=>0,
                        'update_right'=>0,
                        'search_right'=>0
                    )));
                }
            }
        }
        catch(Throwable $e)
        {
            // Keep normal flow even if menu bootstrap fails.
        }
    }
    protected function _ensureTransactionsAndExpenseMenus()
    {
        try
        {
            $Page = ClassRegistry::init('Page');
            $PageRight = ClassRegistry::init('PageRight');
            $Ugroup = ClassRegistry::init('Ugroup');
            if(!$Page || !$PageRight || !$Ugroup)
            {
                return;
            }

            // 1. Shift non-Ardaccounts root pages with ordering <= 4 so Dashboard is 1, Transactions is 2, Income Transactions is 3, Expense Transactions is 4
            $shiftPages = $Page->find('all', array(
                'fields' => array('Page.id', 'Page.ordering'),
                'conditions' => array(
                    'Page.parent_id' => 0,
                    'Page.id <>' => 1,
                    'LOWER(Page.controller_name) <>' => 'ardaccounts',
                    'Page.ordering <=' => 4
                ),
                'recursive' => -1
            ));
            foreach((array)$shiftPages as $sp)
            {
                $spId = (int)$sp['Page']['id'];
                $spOrd = (int)$sp['Page']['ordering'];
                $Page->updateAll(
                    array('Page.ordering' => $spOrd + 3),
                    array('Page.id' => $spId)
                );
            }

            // 2. Ensure root "Transactions" page (parent_id = 0, ordering = 2)
            $this->_ensureSimpleRootAdminPage(array(
                'model_name' => 'Transactions',
                'page_name' => 'Transactions',
                'controller_name' => 'Ardaccounts',
                'action_name' => 'transactions',
                'icon' => 'fa fa-exchange',
                'ordering' => 2,
                'published' => 'Yes',
                'sel_name' => 'transactions,ardaccounts_transactions',
                'grant_all_groups' => true
            ));

            // 3. Ensure root "Income Transactions" page (parent_id = 0, ordering = 3)
            $this->_ensureSimpleRootAdminPage(array(
                'model_name' => 'Income Transactions',
                'page_name' => 'Income Transactions',
                'controller_name' => 'Ardaccounts',
                'action_name' => 'incometransactions',
                'icon' => 'fa fa-arrow-circle-down',
                'ordering' => 3,
                'published' => 'Yes',
                'sel_name' => 'incometransactions,ardaccounts_incometransactions,income_transactions',
                'grant_all_groups' => true
            ));

            // 4. Ensure root "Expense Transactions" page (parent_id = 0, ordering = 4)
            $this->_ensureSimpleRootAdminPage(array(
                'model_name' => 'Expense Transactions',
                'page_name' => 'Expense Transactions',
                'controller_name' => 'Ardaccounts',
                'action_name' => 'expensetransactions',
                'icon' => 'fa fa-credit-card',
                'ordering' => 4,
                'published' => 'Yes',
                'sel_name' => 'expensetransactions,ardaccounts_expensetransactions,expense_transactions',
                'grant_all_groups' => true
            ));
        }
        catch(Throwable $e)
        {
        }
    }

    protected function _ensureLattesMenu()
    {
        try
        {
            $Page = ClassRegistry::init('Page');
            $PageRight = ClassRegistry::init('PageRight');
            $Ugroup = ClassRegistry::init('Ugroup');
            $rootConditions = array(
                'LOWER(Page.controller_name)' => 'realestateletters',
                'LOWER(Page.action_name)' => 'dashboard',
                'Page.parent_id' => 0
            );
            $existing = $Page->find('first', array(
                'fields' => array('Page.id'),
                'conditions' => $rootConditions,
                'order' => array('Page.id' => 'asc'),
                'recursive' => -1
            ));
            $rootData = array(
                'model_name' => 'Letters',
                'page_name' => 'Letters',
                'controller_name' => 'Realestateletters',
                'action_name' => 'dashboard',
                'icon' => 'fa fa-file-text-o',
                'parent_id' => 0,
                'ordering' => 7,
                'published' => 'Yes',
                'sel_name' => ''
            );
            if(empty($existing['Page']['id']))
            {
                $Page->create();
                if(!$Page->save(array('Page' => $rootData)))
                {
                    return;
                }
                $rootId = (int)$Page->id;
            }
            else
            {
                $rootId = (int)$existing['Page']['id'];
                $rootData['id'] = $rootId;
                $Page->save(array('Page' => $rootData));
            }
            if($rootId <= 0)
            {
                return;
            }

            // De-duplicate root rows and move any children to the canonical root.
            $allRoots = $Page->find('all', array(
                'fields' => array('Page.id'),
                'conditions' => $rootConditions,
                'order' => array('Page.id' => 'asc'),
                'recursive' => -1
            ));
            foreach($allRoots as $rootRow)
            {
                $extraRootId = (int)$rootRow['Page']['id'];
                if($extraRootId <= 0 || $extraRootId === $rootId)
                {
                    continue;
                }
                $Page->updateAll(
                    array('Page.parent_id' => $rootId),
                    array('Page.parent_id' => $extraRootId)
                );
                $PageRight->deleteAll(array('PageRight.page_id' => $extraRootId), false);
                $Page->delete($extraRootId, false);
            }

            $menuPageIds = array($rootId => $rootId);
            $children = array(
                array('name' => 'Dashboard', 'action' => 'dashboard', 'icon' => 'fa fa-dashboard', 'ordering' => 1),
                array('name' => 'Manage Letters', 'action' => 'index', 'icon' => 'fa fa-file-text-o', 'ordering' => 2),
                array('name' => 'Manage Templates', 'action' => 'templates', 'icon' => 'fa fa-files-o', 'ordering' => 3)
            );
            foreach($children as $child)
            {
                $childConditions = array(
                    'LOWER(Page.controller_name)' => 'realestateletters',
                    'LOWER(Page.action_name)' => strtolower($child['action']),
                    'Page.id <>' => $rootId
                );
                $childRows = $Page->find('all', array(
                    'fields' => array('Page.id', 'Page.parent_id'),
                    'conditions' => $childConditions,
                    'order' => array('Page.id' => 'asc'),
                    'recursive' => -1
                ));
                $childId = 0;
                $duplicateIds = array();
                foreach($childRows as $childRow)
                {
                    $candidateId = (int)$childRow['Page']['id'];
                    $candidateParent = (int)$childRow['Page']['parent_id'];
                    if($candidateId <= 0)
                    {
                        continue;
                    }
                    if($candidateParent === $rootId)
                    {
                        $childId = $candidateId;
                        break;
                    }
                }
                if($childId <= 0 && !empty($childRows[0]['Page']['id']))
                {
                    $childId = (int)$childRows[0]['Page']['id'];
                }
                foreach($childRows as $childRow)
                {
                    $candidateId = (int)$childRow['Page']['id'];
                    if($candidateId > 0 && $candidateId !== $childId)
                    {
                        $duplicateIds[$candidateId] = $candidateId;
                    }
                }
                $childData = array(
                    'model_name' => 'Letters',
                    'page_name' => $child['name'],
                    'controller_name' => 'Realestateletters',
                    'action_name' => $child['action'],
                    'icon' => $child['icon'],
                    'parent_id' => $rootId,
                    'ordering' => $child['ordering'],
                    'published' => 'Yes',
                    'sel_name' => ''
                );
                if($childId <= 0)
                {
                    $Page->create();
                    if($Page->save(array('Page' => $childData)))
                    {
                        $savedChildId = (int)$Page->id;
                        $menuPageIds[$savedChildId] = $savedChildId;
                    }
                }
                else
                {
                    $childData['id'] = $childId;
                    $Page->save(array('Page' => $childData));
                    $menuPageIds[$childId] = $childId;
                }
                foreach($duplicateIds as $duplicateId)
                {
                    $PageRight->deleteAll(array('PageRight.page_id' => (int)$duplicateId), false);
                    $Page->delete((int)$duplicateId, false);
                }
            }

            $groups = $Ugroup->find('list', array('fields' => array('id', 'id')));
            foreach($groups as $groupId)
            {
                $groupId = (int)$groupId;
                if($groupId <= 0)
                {
                    continue;
                }
                foreach($menuPageIds as $grantPageId)
                {
                    $exists = $PageRight->find('count', array(
                        'conditions' => array(
                            'PageRight.page_id' => (int)$grantPageId,
                            'PageRight.ugroup_id' => $groupId
                        ),
                        'recursive' => -1
                    ));
                    if($exists > 0)
                    {
                        continue;
                    }
                    $PageRight->create();
                    $PageRight->save(array('PageRight' => array(
                        'page_id' => (int)$grantPageId,
                        'ugroup_id' => $groupId,
                        'view_right' => 0,
                        'save_right' => 0,
                        'update_right' => 0,
                        'search_right' => 0
                    )));
                }
            }
        }
        catch(Throwable $e)
        {
            // Keep normal flow even if menu bootstrap fails.
        }
    }
    protected function _ensureBallotingMenu()
    {
        try
        {
            $Page = ClassRegistry::init('Page');
            $PageRight = ClassRegistry::init('PageRight');
            $Ugroup = ClassRegistry::init('Ugroup');
            $rootConditions = array(
                'LOWER(Page.controller_name)' => 'balloting',
                'LOWER(Page.action_name)' => 'dashboard',
                'Page.parent_id' => 0
            );
            $existingRoot = $Page->find('first', array(
                'fields' => array('Page.id'),
                'conditions' => $rootConditions,
                'order' => array('Page.id' => 'asc'),
                'recursive' => -1
            ));
            $rootData = array(
                'model_name' => 'Balloting',
                'page_name' => 'Balloting',
                'controller_name' => 'Balloting',
                'action_name' => 'dashboard',
                'icon' => 'fa fa-random',
                'parent_id' => 0,
                'ordering' => 7,
                'published' => 'Yes',
                'sel_name' => ''
            );
            if (empty($existingRoot['Page']['id']))
            {
                $Page->create();
                if (!$Page->save(array('Page' => $rootData)))
                {
                    return;
                }
                $rootId = (int)$Page->id;
            }
            else
            {
                $rootId = (int)$existingRoot['Page']['id'];
                $rootData['id'] = $rootId;
                $Page->save(array('Page' => $rootData));
            }
            if ($rootId <= 0)
            {
                return;
            }

            // De-duplicate root rows and move any children to the canonical root.
            $allRoots = $Page->find('all', array(
                'fields' => array('Page.id'),
                'conditions' => $rootConditions,
                'order' => array('Page.id' => 'asc'),
                'recursive' => -1
            ));
            foreach ($allRoots as $rootRow)
            {
                $extraRootId = (int)$rootRow['Page']['id'];
                if ($extraRootId <= 0 || $extraRootId === $rootId)
                {
                    continue;
                }
                $Page->updateAll(
                    array('Page.parent_id' => $rootId),
                    array('Page.parent_id' => $extraRootId)
                );
                $PageRight->deleteAll(array('PageRight.page_id' => $extraRootId), false);
                $Page->delete($extraRootId, false);
            }

            $menuPageIds = array($rootId => $rootId);
            $children = array(
                array('name' => 'Dashboard', 'action' => 'dashboard', 'icon' => 'fa fa-dashboard', 'ordering' => 1),
                array('name' => 'Manage Units', 'action' => 'units', 'icon' => 'fa fa-list', 'ordering' => 2),
                array('name' => 'Allotments', 'action' => 'allotments', 'icon' => 'fa fa-link', 'ordering' => 3),
                array('name' => 'Manual Allotment', 'action' => 'manualallotment', 'icon' => 'fa fa-hand-paper-o', 'ordering' => 4),
                array('name' => 'Run Balloting', 'action' => 'run', 'icon' => 'fa fa-play-circle', 'ordering' => 5),
                array('name' => 'Settings', 'action' => 'settings', 'icon' => 'fa fa-cog', 'ordering' => 6),
                array('name' => 'Import Units', 'action' => 'import', 'icon' => 'fa fa-upload', 'ordering' => 7)
            );

            foreach ($children as $child)
            {
                $childRows = $Page->find('all', array(
                    'fields' => array('Page.id', 'Page.parent_id'),
                    'conditions' => array(
                        'LOWER(Page.controller_name)' => 'balloting',
                        'LOWER(Page.action_name)' => strtolower($child['action']),
                        'Page.id <>' => $rootId
                    ),
                    'order' => array('Page.id' => 'asc'),
                    'recursive' => -1
                ));
                $childId = 0;
                $duplicateIds = array();
                foreach ($childRows as $childRow)
                {
                    $candidateId = (int)$childRow['Page']['id'];
                    $candidateParent = (int)$childRow['Page']['parent_id'];
                    if ($candidateId <= 0)
                    {
                        continue;
                    }
                    if ($candidateParent === $rootId)
                    {
                        $childId = $candidateId;
                        break;
                    }
                }
                if ($childId <= 0 && !empty($childRows[0]['Page']['id']))
                {
                    $childId = (int)$childRows[0]['Page']['id'];
                }
                foreach ($childRows as $childRow)
                {
                    $candidateId = (int)$childRow['Page']['id'];
                    if ($candidateId > 0 && $candidateId !== $childId)
                    {
                        $duplicateIds[$candidateId] = $candidateId;
                    }
                }
                $childData = array(
                    'model_name' => 'Balloting',
                    'page_name' => $child['name'],
                    'controller_name' => 'Balloting',
                    'action_name' => $child['action'],
                    'icon' => $child['icon'],
                    'parent_id' => $rootId,
                    'ordering' => $child['ordering'],
                    'published' => 'Yes',
                    'sel_name' => ''
                );
                if ($childId <= 0)
                {
                    $Page->create();
                    if ($Page->save(array('Page' => $childData)))
                    {
                        $menuPageIds[(int)$Page->id] = (int)$Page->id;
                    }
                }
                else
                {
                    $childData['id'] = $childId;
                    $Page->save(array('Page' => $childData));
                    $menuPageIds[$childId] = $childId;
                }
                foreach ($duplicateIds as $duplicateId)
                {
                    $PageRight->deleteAll(array('PageRight.page_id' => (int)$duplicateId), false);
                    $Page->delete((int)$duplicateId, false);
                }
            }

            $groups = $Ugroup->find('list', array('fields' => array('id', 'id')));
            foreach ($groups as $groupId)
            {
                $groupId = (int)$groupId;
                if ($groupId <= 0)
                {
                    continue;
                }
                foreach ($menuPageIds as $grantPageId)
                {
                    $exists = $PageRight->find('count', array(
                        'conditions' => array(
                            'PageRight.page_id' => (int)$grantPageId,
                            'PageRight.ugroup_id' => $groupId
                        ),
                        'recursive' => -1
                    ));
                    if ($exists > 0)
                    {
                        continue;
                    }
                    $PageRight->create();
                    $PageRight->save(array('PageRight' => array(
                        'page_id' => (int)$grantPageId,
                        'ugroup_id' => $groupId,
                        'view_right' => 0,
                        'save_right' => 0,
                        'update_right' => 0,
                        'search_right' => 0
                    )));
                }
            }
        }
        catch (Exception $e)
        {
            // Keep normal flow even if menu bootstrap fails.
        }
    }
    protected function _ensureBusinessfeaturesMenu()
    {
        try
        {
            $Page = ClassRegistry::init('Page');
            $PageRight = ClassRegistry::init('PageRight');
            $Ugroup = ClassRegistry::init('Ugroup');
            $User = ClassRegistry::init('User');
            $autoFullRoleTypes = array('ADR');
            $superAdminGroupId = 0;
            try
            {
                $superAdminGroupId = (int)$User->field('ugroup_id', array('User.id' => 1));
            }
            catch (Exception $e)
            {
                $superAdminGroupId = 0;
            }
            $rootConditions = array(
                'LOWER(Page.controller_name)' => 'businessfeatures',
                'LOWER(Page.action_name)' => 'dashboard',
                'Page.parent_id' => 0
            );
            $existingRoot = $Page->find('first', array(
                'fields' => array('Page.id'),
                'conditions' => $rootConditions,
                'order' => array('Page.id' => 'asc'),
                'recursive' => -1
            ));
            $rootData = array(
                'model_name' => 'Growth Features',
                'page_name' => 'Growth Features',
                'controller_name' => 'Businessfeatures',
                'action_name' => 'dashboard',
                'icon' => 'fa fa-rocket',
                'parent_id' => 0,
                'ordering' => 8,
                'published' => 'Yes',
                'sel_name' => ''
            );
            if(empty($existingRoot['Page']['id']))
            {
                $Page->create();
                if(!$Page->save(array('Page' => $rootData)))
                {
                    return;
                }
                $rootId = (int)$Page->id;
            }
            else
            {
                $rootId = (int)$existingRoot['Page']['id'];
                $rootData['id'] = $rootId;
                $Page->save(array('Page' => $rootData));
            }
            if($rootId <= 0)
            {
                return;
            }

            $menuPageIds = array($rootId => $rootId);
            $children = array(
                array('name' => 'Dashboard', 'action' => 'dashboard', 'icon' => 'fa fa-dashboard', 'ordering' => 1),
                array('name' => 'Lead SLA', 'action' => 'leadsla', 'icon' => 'fa fa-clock-o', 'ordering' => 2),
                array('name' => 'Source ROI', 'action' => 'sourceroi', 'icon' => 'fa fa-pie-chart', 'ordering' => 3),
                array('name' => 'Client Journey', 'action' => 'clientjourney', 'icon' => 'fa fa-road', 'ordering' => 4),
                array('name' => 'Property Explorer', 'action' => 'propertyexplorer', 'icon' => 'fa fa-map-marker', 'ordering' => 5),
                array('name' => 'Payment Overdue', 'action' => 'paymentoverdue', 'icon' => 'fa fa-bell-o', 'ordering' => 6),
                array('name' => 'Letter Workflow', 'action' => 'letterworkflow', 'icon' => 'fa fa-envelope-o', 'ordering' => 7),
                array('name' => 'Document Vault', 'action' => 'documentvault', 'icon' => 'fa fa-folder-open-o', 'ordering' => 8),
                array('name' => 'Agent Scorecard', 'action' => 'agentscorecard', 'icon' => 'fa fa-line-chart', 'ordering' => 9),
                array('name' => 'Portal Actions', 'action' => 'portalactions', 'icon' => 'fa fa-user-circle-o', 'ordering' => 10),
                array('name' => 'Website Conversion', 'action' => 'websiteconversion', 'icon' => 'fa fa-bullseye', 'ordering' => 11)
            );

            foreach($children as $child)
            {
                $childConditions = array(
                    'LOWER(Page.controller_name)' => 'businessfeatures',
                    'LOWER(Page.action_name)' => strtolower($child['action']),
                    'Page.parent_id' => $rootId
                );
                $existingChild = $Page->find('first', array(
                    'fields' => array('Page.id'),
                    'conditions' => $childConditions,
                    'order' => array('Page.id' => 'asc'),
                    'recursive' => -1
                ));
                $childData = array(
                    'model_name' => 'Growth Features',
                    'page_name' => $child['name'],
                    'controller_name' => 'Businessfeatures',
                    'action_name' => $child['action'],
                    'icon' => $child['icon'],
                    'parent_id' => $rootId,
                    'ordering' => (int)$child['ordering'],
                    'published' => 'Yes',
                    'sel_name' => ''
                );
                if(empty($existingChild['Page']['id']))
                {
                    $Page->create();
                    if($Page->save(array('Page' => $childData)))
                    {
                        $menuPageIds[(int)$Page->id] = (int)$Page->id;
                    }
                }
                else
                {
                    $childData['id'] = (int)$existingChild['Page']['id'];
                    $Page->save(array('Page' => $childData));
                    $menuPageIds[(int)$existingChild['Page']['id']] = (int)$existingChild['Page']['id'];
                }
            }

            $groups = $Ugroup->find('all', array(
                'fields' => array('Ugroup.id', 'Ugroup.type'),
                'recursive' => -1
            ));
            foreach((array)$groups as $groupRow)
            {
                $groupId = !empty($groupRow['Ugroup']['id']) ? (int)$groupRow['Ugroup']['id'] : 0;
                $groupType = !empty($groupRow['Ugroup']['type']) ? strtoupper(trim((string)$groupRow['Ugroup']['type'])) : '';
                $autoFullRights = in_array($groupType, $autoFullRoleTypes, true) || ($groupId > 0 && $groupId === (int)$superAdminGroupId);
                if($groupId <= 0)
                {
                    continue;
                }
                foreach($menuPageIds as $grantPageId)
                {
                    $existingRight = $PageRight->find('first', array(
                        'fields' => array('PageRight.id', 'PageRight.view_right', 'PageRight.save_right', 'PageRight.update_right', 'PageRight.search_right'),
                        'conditions' => array(
                            'PageRight.page_id' => (int)$grantPageId,
                            'PageRight.ugroup_id' => (int)$groupId
                        ),
                        'order' => array('PageRight.id' => 'asc'),
                        'recursive' => -1
                    ));

                    if(empty($existingRight['PageRight']['id']))
                    {
                        $saveRight = array(
                            'page_id' => (int)$grantPageId,
                            'ugroup_id' => (int)$groupId,
                            'view_right' => ($autoFullRights ? 1 : 0),
                            'save_right' => 0,
                            'update_right' => 0,
                            'search_right' => 0
                        );
                        if($autoFullRights)
                        {
                            $saveRight['save_right'] = 1;
                            $saveRight['update_right'] = 1;
                            $saveRight['search_right'] = 1;
                        }
                        $PageRight->create();
                        $PageRight->save(array('PageRight' => $saveRight));
                        continue;
                    }
                    if($autoFullRights)
                    {
                        $rightId = (int)$existingRight['PageRight']['id'];
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
                }
            }
        }
        catch (Exception $e)
        {
            // Keep normal flow even if menu bootstrap fails.
        }
    }
    protected function _ensureCrmautomationsMenu()
    {
        try
        {
            $Page = ClassRegistry::init('Page');
            $PageRight = ClassRegistry::init('PageRight');
            if(!$Page || !$PageRight)
            {
                return;
            }

            $rootRow = (array)$Page->find('first', array(
                'fields' => array('Page.id'),
                'conditions' => array(
                    'LOWER(Page.controller_name)' => 'leads',
                    'LOWER(Page.action_name)' => 'dashboard',
                    'Page.parent_id' => 0
                ),
                'order' => array('Page.id' => 'asc'),
                'recursive' => -1
            ));
            $rootId = !empty($rootRow['Page']['id']) ? (int)$rootRow['Page']['id'] : 0;
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

            if(empty($pageIds))
            {
                return;
            }

            $rightsSourcePageId = 0;
            $manageRow = (array)$Page->find('first', array(
                'fields' => array('Page.id'),
                'conditions' => array(
                    'Page.parent_id' => $rootId,
                    'LOWER(Page.controller_name)' => 'leads',
                    'LOWER(Page.action_name)' => 'manage',
                    'Page.published' => 'Yes'
                ),
                'order' => array('Page.id' => 'asc'),
                'recursive' => -1
            ));
            if(!empty($manageRow['Page']['id']))
            {
                $rightsSourcePageId = (int)$manageRow['Page']['id'];
            }
            if($rightsSourcePageId <= 0)
            {
                $rightsSourcePageId = $rootId;
            }

            $rightsRows = (array)$PageRight->find('all', array(
                'conditions' => array('PageRight.page_id' => $rightsSourcePageId),
                'fields' => array('PageRight.ugroup_id','PageRight.view_right','PageRight.save_right','PageRight.update_right','PageRight.search_right'),
                'recursive' => -1
            ));

            if(empty($rightsRows))
            {
                $grantGroupIds = array(1, 4);
                foreach($grantGroupIds as $groupId)
                {
                    $rightsRows[] = array('PageRight' => array(
                        'ugroup_id' => (int)$groupId,
                        'view_right' => 1,
                        'save_right' => 1,
                        'update_right' => 1,
                        'search_right' => 1
                    ));
                }
            }

            foreach($pageIds as $pageId)
            {
                foreach($rightsRows as $rightsRow)
                {
                    $rightMeta = !empty($rightsRow['PageRight']) ? (array)$rightsRow['PageRight'] : (array)$rightsRow;
                    $groupId = !empty($rightMeta['ugroup_id']) ? (int)$rightMeta['ugroup_id'] : 0;
                    if($groupId <= 0)
                    {
                        continue;
                    }
                    $existingRight = (array)$PageRight->find('first', array(
                        'fields' => array('PageRight.id'),
                        'conditions' => array(
                            'PageRight.page_id' => (int)$pageId,
                            'PageRight.ugroup_id' => $groupId
                        ),
                        'recursive' => -1
                    ));
                    $saveRight = array(
                        'page_id' => (int)$pageId,
                        'ugroup_id' => $groupId,
                        'view_right' => !empty($rightMeta['view_right']) ? 1 : 0,
                        'save_right' => !empty($rightMeta['save_right']) ? 1 : 0,
                        'update_right' => !empty($rightMeta['update_right']) ? 1 : 0,
                        'search_right' => !empty($rightMeta['search_right']) ? 1 : 0
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
        catch(Throwable $e)
        {
        }
    }
    protected function _ensureReportsMenu()
    {
        try
        {
            $Page = ClassRegistry::init('Page');
            $PageRight = ClassRegistry::init('PageRight');
            $Ugroup = ClassRegistry::init('Ugroup');

            $rootConditions = array(
                'LOWER(Page.page_name)' => 'reports',
                'Page.parent_id' => 0
            );
            $existingRoot = $Page->find('first', array(
                'fields' => array('Page.id'),
                'conditions' => $rootConditions,
                'order' => array('Page.id' => 'asc'),
                'recursive' => -1
            ));
            if (empty($existingRoot['Page']['id']))
            {
                $existingRoot = $Page->find('first', array(
                    'fields' => array('Page.id'),
                    'conditions' => array(
                        'Page.parent_id' => 0,
                        'OR' => array(
                            array(
                                'LOWER(Page.controller_name)' => 'salesreports',
                                'LOWER(Page.action_name)' => 'index'
                            ),
                            array(
                                'LOWER(Page.controller_name)' => 'ardaccounts',
                                'LOWER(Page.action_name)' => 'reports'
                            )
                        )
                    ),
                    'order' => array('Page.id' => 'asc'),
                    'recursive' => -1
                ));
            }
            $rootData = array(
                'model_name' => 'Reports',
                'page_name' => 'Reports',
                'controller_name' => 'Salesreports',
                'action_name' => 'index',
                'icon' => 'fa fa-table',
                'parent_id' => 0,
                'ordering' => 10,
                'published' => 'Yes',
                'sel_name' => ''
            );
            if (empty($existingRoot['Page']['id']))
            {
                $Page->create();
                if (!$Page->save(array('Page' => $rootData)))
                {
                    return;
                }
                $rootId = (int)$Page->id;
            }
            else
            {
                $rootId = (int)$existingRoot['Page']['id'];
                $rootData['id'] = $rootId;
                $Page->save(array('Page' => $rootData));
            }
            if ($rootId <= 0)
            {
                return;
            }

            $menuPageIds = array($rootId => $rootId);
            $children = array(
                array('name' => 'Deals Report', 'controller' => 'Deals', 'action' => 'index', 'icon' => 'fa fa-briefcase', 'ordering' => 1),
                array('name' => 'Paid Invoices', 'controller' => 'Invpaids', 'action' => 'index', 'icon' => 'fa fa-money', 'ordering' => 2),
                array('name' => 'Demand Notices', 'controller' => 'Invpastdues', 'action' => 'index', 'icon' => 'fa fa-bell', 'ordering' => 3),
                array('name' => 'Leads CRM Report', 'controller' => 'Leads', 'action' => 'manage', 'icon' => 'fa fa-bullhorn', 'ordering' => 4),
                array('name' => 'Letters Report', 'controller' => 'Realestateletters', 'action' => 'index', 'icon' => 'fa fa-file-text-o', 'ordering' => 5),
                array('name' => 'Balloting Report', 'controller' => 'Balloting', 'action' => 'allotments', 'icon' => 'fa fa-random', 'ordering' => 6),
                array('name' => 'Accounts Report', 'controller' => 'Ardaccounts', 'action' => 'reports', 'icon' => 'fa fa-calculator', 'ordering' => 7),
                array('name' => 'Sales Report', 'controller' => 'Salesreports', 'action' => 'index', 'icon' => 'fa fa-line-chart', 'ordering' => 8),
                array('name' => 'Purchase Report', 'controller' => 'Purchasereports', 'action' => 'index', 'icon' => 'fa fa-shopping-cart', 'ordering' => 9),
                array('name' => 'Expense Report', 'controller' => 'Expensereports', 'action' => 'index', 'icon' => 'fa fa-credit-card', 'ordering' => 10),
                array('name' => 'Profit & Loss', 'controller' => 'Profitreports', 'action' => 'index', 'icon' => 'fa fa-balance-scale', 'ordering' => 11),
                array('name' => 'CRM Daily Progress', 'controller' => 'Crmreports', 'action' => 'daily', 'icon' => 'fa fa-line-chart', 'ordering' => 12)
            );

            foreach ($children as $child)
            {
                $childConditions = array(
                    'LOWER(Page.controller_name)' => strtolower((string)$child['controller']),
                    'LOWER(Page.action_name)' => strtolower((string)$child['action']),
                    'Page.parent_id' => $rootId
                );
                $existingChild = $Page->find('first', array(
                    'fields' => array('Page.id'),
                    'conditions' => $childConditions,
                    'order' => array('Page.id' => 'asc'),
                    'recursive' => -1
                ));
                $childData = array(
                    'model_name' => 'Reports',
                    'page_name' => $child['name'],
                    'controller_name' => $child['controller'],
                    'action_name' => $child['action'],
                    'icon' => $child['icon'],
                    'parent_id' => $rootId,
                    'ordering' => (int)$child['ordering'],
                    'published' => 'Yes',
                    'sel_name' => ''
                );
                if (empty($existingChild['Page']['id']))
                {
                    $Page->create();
                    if ($Page->save(array('Page' => $childData)))
                    {
                        $menuPageIds[(int)$Page->id] = (int)$Page->id;
                    }
                }
                else
                {
                    $childId = (int)$existingChild['Page']['id'];
                    $childData['id'] = $childId;
                    $Page->save(array('Page' => $childData));
                    $menuPageIds[$childId] = $childId;
                }
            }

            $groups = $Ugroup->find('list', array('fields' => array('id', 'id')));
            foreach ((array)$groups as $groupId)
            {
                $groupId = (int)$groupId;
                if ($groupId <= 0)
                {
                    continue;
                }
                foreach ($menuPageIds as $grantPageId)
                {
                    $existingRight = $PageRight->find('first', array(
                        'fields' => array('PageRight.id', 'PageRight.view_right', 'PageRight.save_right', 'PageRight.update_right', 'PageRight.search_right'),
                        'conditions' => array(
                            'PageRight.page_id' => (int)$grantPageId,
                            'PageRight.ugroup_id' => $groupId
                        ),
                        'order' => array('PageRight.id' => 'asc'),
                        'recursive' => -1
                    ));
                    if (empty($existingRight['PageRight']['id']))
                    {
                        $PageRight->create();
                        $PageRight->save(array('PageRight' => array(
                            'page_id' => (int)$grantPageId,
                            'ugroup_id' => $groupId,
                            'view_right' => ($groupId === 1 ? 1 : 0),
                            'save_right' => ($groupId === 1 ? 1 : 0),
                            'update_right' => ($groupId === 1 ? 1 : 0),
                            'search_right' => ($groupId === 1 ? 1 : 0)
                        )));
                    }
                    elseif ($groupId === 1)
                    {
                        $PageRight->updateAll(
                            array(
                                'PageRight.view_right' => 1,
                                'PageRight.save_right' => 1,
                                'PageRight.update_right' => 1,
                                'PageRight.search_right' => 1
                            ),
                            array(
                                'PageRight.page_id' => (int)$grantPageId,
                                'PageRight.ugroup_id' => $groupId
                            )
                        );
                    }
                }
            }
        }
        catch (Exception $e)
        {
            // Keep normal flow even if menu bootstrap fails.
        }
    }
    protected function _ensureAgentWalletHistoryMainMenu()
    {
        try
        {
            $Page = ClassRegistry::init('Page');
            $PageRight = ClassRegistry::init('PageRight');
            $Ugroup = ClassRegistry::init('Ugroup');
            if(!$Page || !$PageRight || !$Ugroup)
            {
                return;
            }

            $walletRows = (array)$Page->find('all', array(
                'fields' => array('Page.id', 'Page.parent_id'),
                'conditions' => array(
                    'LOWER(Page.controller_name)' => 'awallet_histories',
                    'LOWER(Page.action_name)' => 'index'
                ),
                'order' => array('Page.id' => 'asc'),
                'recursive' => -1
            ));

            $walletPageId = 0;
            if(!empty($walletRows[0]['Page']['id']))
            {
                $walletPageId = (int)$walletRows[0]['Page']['id'];
            }

            $walletData = array(
                'model_name' => 'User',
                'page_name' => 'Agent Wallet History',
                'controller_name' => 'awallet_histories',
                'action_name' => 'index',
                'icon' => 'fa fa-briefcase',
                'parent_id' => 0,
                'ordering' => 11,
                'published' => 'Yes',
                'sel_name' => ''
            );

            if($walletPageId > 0)
            {
                $walletData['id'] = $walletPageId;
                $Page->save(array('Page' => $walletData), array('validate' => false));
            }
            else
            {
                $Page->create();
                if($Page->save(array('Page' => $walletData), array('validate' => false)))
                {
                    $walletPageId = (int)$Page->id;
                }
            }

            if($walletPageId <= 0)
            {
                return;
            }

            // Hide any duplicate Wallet History menu rows, keep one canonical root menu.
            foreach($walletRows as $idx => $walletRow)
            {
                $rowId = (int)$walletRow['Page']['id'];
                if($rowId <= 0 || $rowId === $walletPageId)
                {
                    continue;
                }
                $Page->id = $rowId;
                $Page->save(array('Page' => array(
                    'id' => $rowId,
                    'published' => 'No'
                )), array('validate' => false));
            }

            // Ensure rights row exists for all user levels; do not force access changes.
            $groups = (array)$Ugroup->find('list', array('fields' => array('id', 'id')));
            foreach($groups as $groupId)
            {
                $groupId = (int)$groupId;
                if($groupId <= 0)
                {
                    continue;
                }
                $exists = (int)$PageRight->find('count', array(
                    'conditions' => array(
                        'PageRight.page_id' => $walletPageId,
                        'PageRight.ugroup_id' => $groupId
                    ),
                    'recursive' => -1
                ));
                if($exists > 0)
                {
                    continue;
                }
                $PageRight->create();
                $PageRight->save(array('PageRight' => array(
                    'page_id' => $walletPageId,
                    'ugroup_id' => $groupId,
                    'view_right' => 0,
                    'save_right' => 0,
                    'update_right' => 0,
                    'search_right' => 0
                )), array('validate' => false));
            }
        }
        catch(Throwable $e)
        {
            // Keep normal flow even if menu bootstrap fails.
        }
    }
    protected function _ensureAuditTrailMenu()
    {
        try
        {
            $Page = ClassRegistry::init('Page');
            $PageRight = ClassRegistry::init('PageRight');
            $Ugroup = ClassRegistry::init('Ugroup');
            if(!$Page || !$PageRight || !$Ugroup)
            {
                return;
            }

            $existingRows = (array)$Page->find('all', array(
                'fields' => array('Page.id', 'Page.parent_id'),
                'conditions' => array(
                    'LOWER(Page.controller_name)' => 'audittrails',
                    'LOWER(Page.action_name)' => 'index'
                ),
                'order' => array('Page.id' => 'asc'),
                'recursive' => -1
            ));

            $auditPageId = 0;
            if(!empty($existingRows[0]['Page']['id']))
            {
                $auditPageId = (int)$existingRows[0]['Page']['id'];
            }
            $auditData = array(
                'model_name' => 'Audit Trail',
                'page_name' => 'Audit Trail',
                'controller_name' => 'audittrails',
                'action_name' => 'index',
                'icon' => 'fa fa-history',
                'parent_id' => 0,
                'ordering' => 14,
                'published' => 'Yes',
                'sel_name' => ''
            );

            if($auditPageId > 0)
            {
                $auditData['id'] = $auditPageId;
                $Page->save(array('Page' => $auditData), array('validate' => false));
            }
            else
            {
                $Page->create();
                if($Page->save(array('Page' => $auditData), array('validate' => false)))
                {
                    $auditPageId = (int)$Page->id;
                }
            }

            if($auditPageId <= 0)
            {
                return;
            }

            foreach($existingRows as $row)
            {
                $rowId = (int)$row['Page']['id'];
                if($rowId <= 0 || $rowId === $auditPageId)
                {
                    continue;
                }
                $Page->id = $rowId;
                $Page->save(array('Page' => array(
                    'id' => $rowId,
                    'published' => 'No'
                )), array('validate' => false));
            }

            $groups = (array)$Ugroup->find('list', array('fields' => array('id', 'id')));
            foreach($groups as $groupId)
            {
                $groupId = (int)$groupId;
                if($groupId <= 0)
                {
                    continue;
                }
                $exists = (int)$PageRight->find('count', array(
                    'conditions' => array(
                        'PageRight.page_id' => $auditPageId,
                        'PageRight.ugroup_id' => $groupId
                    ),
                    'recursive' => -1
                ));
                if($exists > 0)
                {
                    if($groupId === 1)
                    {
                        $PageRight->updateAll(
                            array(
                                'PageRight.view_right' => 1,
                                'PageRight.save_right' => 1,
                                'PageRight.update_right' => 1,
                                'PageRight.search_right' => 1
                            ),
                            array(
                                'PageRight.page_id' => $auditPageId,
                                'PageRight.ugroup_id' => $groupId
                            )
                        );
                    }
                    continue;
                }
                $PageRight->create();
                $PageRight->save(array('PageRight' => array(
                    'page_id' => $auditPageId,
                    'ugroup_id' => $groupId,
                    'view_right' => ($groupId === 1 ? 1 : 0),
                    'save_right' => ($groupId === 1 ? 1 : 0),
                    'update_right' => ($groupId === 1 ? 1 : 0),
                    'search_right' => ($groupId === 1 ? 1 : 0)
                )), array('validate' => false));
            }
        }
        catch(Throwable $e)
        {
            // Keep normal flow even if menu bootstrap fails.
        }
    }
    protected function _ensureDealRefundMenu()
    {
        try
        {
            $Page = ClassRegistry::init('Page');
            $PageRight = ClassRegistry::init('PageRight');
            $Ugroup = ClassRegistry::init('Ugroup');
            if(!$Page || !$PageRight || !$Ugroup)
            {
                return;
            }

            $dealRootId = 0;
            $dealRoot = $Page->find('first', array(
                'fields' => array('Page.id'),
                'conditions' => array(
                    'LOWER(Page.controller_name)' => 'deals',
                    'LOWER(Page.action_name)' => 'index',
                    'Page.parent_id' => 0
                ),
                'order' => array('Page.id' => 'asc'),
                'recursive' => -1
            ));
            if(!empty($dealRoot['Page']['id']))
            {
                $dealRootId = (int)$dealRoot['Page']['id'];
            }

            $existingRows = (array)$Page->find('all', array(
                'fields' => array('Page.id', 'Page.parent_id'),
                'conditions' => array(
                    'LOWER(Page.controller_name)' => 'dealrefunds',
                    'LOWER(Page.action_name)' => 'index'
                ),
                'order' => array('Page.id' => 'asc'),
                'recursive' => -1
            ));

            $refundPageId = 0;
            if(!empty($existingRows[0]['Page']['id']))
            {
                $refundPageId = (int)$existingRows[0]['Page']['id'];
            }

            $refundData = array(
                'model_name' => 'Deal Refunds',
                'page_name' => 'Deal Refunds',
                'controller_name' => 'dealrefunds',
                'action_name' => 'index',
                'icon' => 'fa fa-undo',
                'parent_id' => $dealRootId,
                'ordering' => 7,
                'published' => 'Yes',
                'sel_name' => ''
            );

            if($refundPageId > 0)
            {
                $refundData['id'] = $refundPageId;
                $Page->save(array('Page' => $refundData), array('validate' => false));
            }
            else
            {
                $Page->create();
                if($Page->save(array('Page' => $refundData), array('validate' => false)))
                {
                    $refundPageId = (int)$Page->id;
                }
            }

            if($refundPageId <= 0)
            {
                return;
            }

            foreach($existingRows as $row)
            {
                $rowId = (int)$row['Page']['id'];
                if($rowId <= 0 || $rowId === $refundPageId)
                {
                    continue;
                }
                $Page->id = $rowId;
                $Page->save(array('Page' => array(
                    'id' => $rowId,
                    'published' => 'No'
                )), array('validate' => false));
            }

            $groups = (array)$Ugroup->find('list', array('fields' => array('id', 'id')));
            foreach($groups as $groupId)
            {
                $groupId = (int)$groupId;
                if($groupId <= 0)
                {
                    continue;
                }
                $exists = (int)$PageRight->find('count', array(
                    'conditions' => array(
                        'PageRight.page_id' => $refundPageId,
                        'PageRight.ugroup_id' => $groupId
                    ),
                    'recursive' => -1
                ));
                if($exists > 0)
                {
                    if($groupId === 1)
                    {
                        $PageRight->updateAll(
                            array(
                                'PageRight.view_right' => 1,
                                'PageRight.save_right' => 1,
                                'PageRight.update_right' => 1,
                                'PageRight.search_right' => 1
                            ),
                            array(
                                'PageRight.page_id' => $refundPageId,
                                'PageRight.ugroup_id' => $groupId
                            )
                        );
                    }
                    continue;
                }
                $PageRight->create();
                $PageRight->save(array('PageRight' => array(
                    'page_id' => $refundPageId,
                    'ugroup_id' => $groupId,
                    'view_right' => ($groupId === 1 ? 1 : 0),
                    'save_right' => ($groupId === 1 ? 1 : 0),
                    'update_right' => ($groupId === 1 ? 1 : 0),
                    'search_right' => ($groupId === 1 ? 1 : 0)
                )), array('validate' => false));
            }
        }
        catch(Throwable $e)
        {
            // Keep normal flow even if menu bootstrap fails.
        }
    }
    protected function _ensureDealRefundRequestMenu()
    {
        $dealRootId = 0;
        try
        {
            $Page = ClassRegistry::init('Page');
            if($Page)
            {
                $dealRoot = (array)$Page->find('first', array(
                    'fields' => array('Page.id'),
                    'conditions' => array(
                        'LOWER(Page.controller_name)' => 'deals',
                        'LOWER(Page.action_name)' => 'index',
                        'Page.parent_id' => 0
                    ),
                    'order' => array('Page.id' => 'asc'),
                    'recursive' => -1
                ));
                if(!empty($dealRoot['Page']['id']))
                {
                    $dealRootId = (int)$dealRoot['Page']['id'];
                }
            }
        }
        catch(Throwable $e)
        {
            $dealRootId = 0;
        }
        $this->_ensureSimpleRootAdminPage(array(
            'model_name' => 'Deal Refunds',
            'page_name' => 'Refund Applications',
            'controller_name' => 'Dealrefundrequests',
            'action_name' => 'index',
            'icon' => 'fa fa-inbox',
            'parent_id' => $dealRootId,
            'ordering' => 8,
            'sel_name' => 'deal_refund_requests,deal_refunds',
            'grant_group_types' => array('ADR', 'AGY')
        ));
        foreach(array('view','review','approve','reject','convert','download','allowdeal','disallowdeal','saveprefix') as $requestAction)
        {
            $this->_ensureSimpleRootAdminPage(array(
                'model_name' => 'Deal Refunds',
                'page_name' => 'Refund Application '.Inflector::humanize($requestAction),
                'controller_name' => 'Dealrefundrequests',
                'action_name' => $requestAction,
                'icon' => 'fa fa-inbox',
                'parent_id' => $dealRootId,
                'ordering' => 8,
                'published' => 'No',
                'sel_name' => 'deal_refund_requests,deal_refunds',
                'grant_group_types' => array('ADR', 'AGY')
            ));
        }
    }
    protected function _ensureDealRentalMenu()
    {
        try
        {
            $this->loadModel('Page');
            $this->loadModel('PageRight');
            $this->loadModel('Ugroup');
            $root = (array)$this->Page->find('first', array(
                'fields' => array('Page.id'),
                'conditions' => array(
                    'LOWER(Page.page_name)' => 'rent manager',
                    'Page.parent_id' => 0
                ),
                'recursive' => -1
            ));
            $rootData = array(
                'model_name' => 'Rent Manager',
                'page_name' => 'Rent Manager',
                'controller_name' => 'Dealrentals',
                'action_name' => 'dashboard',
                'icon' => 'fa fa-key',
                'parent_id' => 0,
                'ordering' => 7,
                'published' => 'Yes'
            );
            if(!empty($root['Page']['id']))
            {
                $rootData['id'] = (int)$root['Page']['id'];
                $this->Page->id = (int)$root['Page']['id'];
                $this->Page->save(array('Page' => $rootData), array('validate' => false));
                $parentId = (int)$root['Page']['id'];
            }
            else
            {
                $this->Page->create();
                $this->Page->save(array('Page' => $rootData), array('validate' => false));
                $parentId = (int)$this->Page->id;
            }
            $pages = array(
                array('page_name' => 'Rental Income', 'action_name' => 'index', 'icon' => 'fa fa-home', 'ordering' => 1, 'published' => 'Yes'),
                array('page_name' => 'Rental Reports', 'action_name' => 'reports', 'icon' => 'fa fa-bar-chart', 'ordering' => 2, 'published' => 'Yes'),
                array('page_name' => 'Rental Settings', 'action_name' => 'settings', 'icon' => 'fa fa-cogs', 'ordering' => 3, 'published' => 'Yes'),
                array('page_name' => 'Add Rental Agreement', 'action_name' => 'add', 'icon' => 'fa fa-plus', 'ordering' => 71, 'published' => 'No'),
                array('page_name' => 'Edit Rental Agreement', 'action_name' => 'edit', 'icon' => 'fa fa-edit', 'ordering' => 72, 'published' => 'No'),
                array('page_name' => 'View Rental Agreement', 'action_name' => 'view', 'icon' => 'fa fa-eye', 'ordering' => 73, 'published' => 'No'),
                array('page_name' => 'Rental Receipt', 'action_name' => 'receipt', 'icon' => 'fa fa-money', 'ordering' => 74, 'published' => 'No'),
                array('page_name' => 'Edit Rental Receipt', 'action_name' => 'editreceipt', 'icon' => 'fa fa-edit', 'ordering' => 75, 'published' => 'No'),
                array('page_name' => 'Delete Rental Receipt', 'action_name' => 'deletereceipt', 'icon' => 'fa fa-trash', 'ordering' => 76, 'published' => 'No'),
                array('page_name' => 'Print Rental Agreement', 'action_name' => 'agreement', 'icon' => 'fa fa-print', 'ordering' => 77, 'published' => 'No'),
                array('page_name' => 'Delete Rental Agreement', 'action_name' => 'delete', 'icon' => 'fa fa-trash', 'ordering' => 79, 'published' => 'No')
            );
            $ugroups = (array)$this->Ugroup->find('list', array('fields' => array('Ugroup.id','Ugroup.id'), 'recursive' => -1));
            foreach($ugroups as $ugroupId)
            {
                $right = (array)$this->PageRight->find('first', array('conditions' => array('PageRight.page_id' => $parentId, 'PageRight.ugroup_id' => (int)$ugroupId), 'recursive' => -1));
                if(!empty($right['PageRight']['id']))
                {
                    if((int)$ugroupId !== 1)
                    {
                        continue;
                    }
                    $rightData = array('id' => (int)$right['PageRight']['id'], 'page_id' => $parentId, 'ugroup_id' => 1, 'view_right' => 1, 'save_right' => 1, 'update_right' => 1, 'search_right' => 1);
                    $this->PageRight->id = (int)$right['PageRight']['id'];
                    $this->PageRight->save(array('PageRight' => $rightData), array('validate' => false));
                }
                else
                {
                    $grant = ((int)$ugroupId === 1 ? 1 : 0);
                    $rightData = array('page_id' => $parentId, 'ugroup_id' => (int)$ugroupId, 'view_right' => $grant, 'save_right' => $grant, 'update_right' => $grant, 'search_right' => $grant);
                    $this->PageRight->create();
                    $this->PageRight->save(array('PageRight' => $rightData), array('validate' => false));
                }
            }
            foreach($pages as $pageRow)
            {
                $existing = (array)$this->Page->find('first', array(
                    'conditions' => array(
                        'LOWER(Page.controller_name)' => 'dealrentals',
                        'LOWER(Page.action_name)' => strtolower($pageRow['action_name']),
                        'Page.parent_id' => $parentId
                    ),
                    'recursive' => -1
                ));
                if(empty($existing['Page']['id']))
                {
                    $existing = (array)$this->Page->find('first', array(
                        'conditions' => array(
                            'LOWER(Page.controller_name)' => 'dealrentals',
                            'LOWER(Page.action_name)' => strtolower($pageRow['action_name']),
                            'Page.page_name !=' => 'Rent Manager'
                        ),
                        'recursive' => -1
                    ));
                }
                $data = array(
                    'model_name' => 'Rent Manager',
                    'page_name' => $pageRow['page_name'],
                    'controller_name' => 'Dealrentals',
                    'action_name' => $pageRow['action_name'],
                    'icon' => $pageRow['icon'],
                    'parent_id' => $parentId,
                    'ordering' => $pageRow['ordering'],
                    'published' => $pageRow['published']
                );
                if(!empty($existing['Page']['id']))
                {
                    $data['id'] = (int)$existing['Page']['id'];
                    $this->Page->id = (int)$existing['Page']['id'];
                    $this->Page->save(array('Page' => $data), array('validate' => false));
                    $pageId = (int)$existing['Page']['id'];
                }
                else
                {
                    $this->Page->create();
                    $this->Page->save(array('Page' => $data), array('validate' => false));
                    $pageId = (int)$this->Page->id;
                }
                if($pageId > 0)
                {
                    $ugroups = (array)$this->Ugroup->find('list', array('fields' => array('Ugroup.id','Ugroup.id'), 'recursive' => -1));
                    foreach($ugroups as $ugroupId)
                    {
                        $right = (array)$this->PageRight->find('first', array('conditions' => array('PageRight.page_id' => $pageId, 'PageRight.ugroup_id' => (int)$ugroupId), 'recursive' => -1));
                        if(!empty($right['PageRight']['id']))
                        {
                            if((int)$ugroupId !== 1)
                            {
                                continue;
                            }
                            $rightData = array('id' => (int)$right['PageRight']['id'], 'page_id' => $pageId, 'ugroup_id' => 1, 'view_right' => 1, 'save_right' => 1, 'update_right' => 1, 'search_right' => 1);
                            $this->PageRight->id = (int)$right['PageRight']['id'];
                            $this->PageRight->save(array('PageRight' => $rightData), array('validate' => false));
                        }
                        else
                        {
                            $grant = ((int)$ugroupId === 1 ? 1 : 0);
                            $rightData = array('page_id' => $pageId, 'ugroup_id' => (int)$ugroupId, 'view_right' => $grant, 'save_right' => $grant, 'update_right' => $grant, 'search_right' => $grant);
                            $this->PageRight->create();
                            $this->PageRight->save(array('PageRight' => $rightData), array('validate' => false));
                        }
                    }
                }
            }
        }
        catch(Throwable $e)
        {
            // Keep admin loading if menu bootstrap cannot run on an older database.
        }
    }

protected function _ensureDealTransferMenu()
    {
        try
        {
            $Page = ClassRegistry::init('Page');
            $PageRight = ClassRegistry::init('PageRight');
            $Ugroup = ClassRegistry::init('Ugroup');
            if(!$Page || !$PageRight || !$Ugroup)
            {
                return;
            }

            $dealRootId = 0;
            $dealRoot = $Page->find('first', array(
                'fields' => array('Page.id'),
                'conditions' => array(
                    'LOWER(Page.controller_name)' => 'deals',
                    'LOWER(Page.action_name)' => 'index',
                    'Page.parent_id' => 0
                ),
                'order' => array('Page.id' => 'asc'),
                'recursive' => -1
            ));
            if(!empty($dealRoot['Page']['id']))
            {
                $dealRootId = (int)$dealRoot['Page']['id'];
            }

            $existingRows = (array)$Page->find('all', array(
                'fields' => array('Page.id', 'Page.parent_id'),
                'conditions' => array(
                    'LOWER(Page.controller_name)' => 'dealtransfers',
                    'LOWER(Page.action_name)' => 'index'
                ),
                'order' => array('Page.id' => 'asc'),
                'recursive' => -1
            ));

            $transferPageId = 0;
            if(!empty($existingRows[0]['Page']['id']))
            {
                $transferPageId = (int)$existingRows[0]['Page']['id'];
            }

            $transferData = array(
                'model_name' => 'Deal Transfer',
                'page_name' => 'Deal Transfer',
                'controller_name' => 'dealtransfers',
                'action_name' => 'index',
                'icon' => 'fa fa-exchange',
                'parent_id' => $dealRootId,
                'ordering' => 8,
                'published' => 'Yes',
                'sel_name' => ''
            );

            if($transferPageId > 0)
            {
                $transferData['id'] = $transferPageId;
                $Page->save(array('Page' => $transferData), array('validate' => false));
            }
            else
            {
                $Page->create();
                if($Page->save(array('Page' => $transferData), array('validate' => false)))
                {
                    $transferPageId = (int)$Page->id;
                }
            }

            if($transferPageId <= 0)
            {
                return;
            }

            foreach($existingRows as $row)
            {
                $rowId = (int)$row['Page']['id'];
                if($rowId <= 0 || $rowId === $transferPageId)
                {
                    continue;
                }
                $Page->id = $rowId;
                $Page->save(array('Page' => array('id' => $rowId, 'published' => 'No')), array('validate' => false));
            }

            $groups = (array)$Ugroup->find('list', array('fields' => array('id', 'id')));
            foreach($groups as $groupId)
            {
                $groupId = (int)$groupId;
                if($groupId <= 0)
                {
                    continue;
                }
                $exists = (int)$PageRight->find('count', array(
                    'conditions' => array(
                        'PageRight.page_id' => $transferPageId,
                        'PageRight.ugroup_id' => $groupId
                    ),
                    'recursive' => -1
                ));
                if($exists > 0)
                {
                    if($groupId === 1)
                    {
                        $PageRight->updateAll(
                            array(
                                'PageRight.view_right' => 1,
                                'PageRight.save_right' => 1,
                                'PageRight.update_right' => 1,
                                'PageRight.search_right' => 1
                            ),
                            array(
                                'PageRight.page_id' => $transferPageId,
                                'PageRight.ugroup_id' => $groupId
                            )
                        );
                    }
                    continue;
                }
                $PageRight->create();
                $PageRight->save(array('PageRight' => array(
                    'page_id' => $transferPageId,
                    'ugroup_id' => $groupId,
                    'view_right' => ($groupId === 1 ? 1 : 0),
                    'save_right' => ($groupId === 1 ? 1 : 0),
                    'update_right' => ($groupId === 1 ? 1 : 0),
                    'search_right' => ($groupId === 1 ? 1 : 0)
                )), array('validate' => false));
            }
        }
        catch(Throwable $e)
        {
            // Keep normal flow even if menu bootstrap fails.
        }
    }
    protected function _ensureDealsAllMenu()
    {
        try
        {
            $Page = ClassRegistry::init('Page');
            $PageRight = ClassRegistry::init('PageRight');
            $Ugroup = ClassRegistry::init('Ugroup');
            if(!$Page || !$PageRight || !$Ugroup)
            {
                return;
            }

            $dealRootId = 0;
            $dealRoot = $Page->find('first', array(
                'fields' => array('Page.id'),
                'conditions' => array(
                    'LOWER(Page.controller_name)' => 'deals',
                    'LOWER(Page.action_name)' => 'index',
                    'Page.parent_id' => 0
                ),
                'order' => array('Page.id' => 'asc'),
                'recursive' => -1
            ));
            if(!empty($dealRoot['Page']['id']))
            {
                $dealRootId = (int)$dealRoot['Page']['id'];
            }
            if($dealRootId <= 0)
            {
                return;
            }

            $existingRows = (array)$Page->find('all', array(
                'fields' => array('Page.id', 'Page.parent_id'),
                'conditions' => array(
                    'LOWER(Page.controller_name)' => 'deals',
                    'LOWER(Page.action_name)' => 'index',
                    'Page.parent_id' => $dealRootId
                ),
                'order' => array('Page.id' => 'asc'),
                'recursive' => -1
            ));

            $allPageId = 0;
            if(!empty($existingRows[0]['Page']['id']))
            {
                $allPageId = (int)$existingRows[0]['Page']['id'];
            }

            $allData = array(
                'model_name' => 'All Deals',
                'page_name' => 'All Deals',
                'controller_name' => 'Deals',
                'action_name' => 'index',
                'icon' => 'fa fa-briefcase',
                'parent_id' => $dealRootId,
                'ordering' => 1,
                'published' => 'Yes',
                'sel_name' => ''
            );

            if($allPageId > 0)
            {
                $allData['id'] = $allPageId;
                $Page->save(array('Page' => $allData), array('validate' => false));
            }
            else
            {
                $Page->create();
                if($Page->save(array('Page' => $allData), array('validate' => false)))
                {
                    $allPageId = (int)$Page->id;
                }
            }

            if($allPageId <= 0)
            {
                return;
            }

            $groups = (array)$Ugroup->find('list', array('fields' => array('id', 'id')));
            foreach($groups as $groupId)
            {
                $groupId = (int)$groupId;
                if($groupId <= 0)
                {
                    continue;
                }
                $exists = (int)$PageRight->find('count', array(
                    'conditions' => array(
                        'PageRight.page_id' => $allPageId,
                        'PageRight.ugroup_id' => $groupId
                    ),
                    'recursive' => -1
                ));
                if($exists > 0)
                {
                    if($groupId === 1)
                    {
                        $PageRight->updateAll(
                            array(
                                'PageRight.view_right' => 1,
                                'PageRight.save_right' => 1,
                                'PageRight.update_right' => 1,
                                'PageRight.search_right' => 1
                            ),
                            array(
                                'PageRight.page_id' => $allPageId,
                                'PageRight.ugroup_id' => $groupId
                            )
                        );
                    }
                    continue;
                }
                $PageRight->create();
                $PageRight->save(array('PageRight' => array(
                    'page_id' => $allPageId,
                    'ugroup_id' => $groupId,
                    'view_right' => ($groupId === 1 ? 1 : 0),
                    'save_right' => ($groupId === 1 ? 1 : 0),
                    'update_right' => ($groupId === 1 ? 1 : 0),
                    'search_right' => ($groupId === 1 ? 1 : 0)
                )), array('validate' => false));
            }
        }
        catch(Throwable $e)
        {
            // Keep normal flow even if menu bootstrap fails.
        }
    }
    public function dateTimeFormatBeforeSave($dateString)
    {
        $dateString = trim((string)$dateString);
        if($dateString === '' || $dateString === '0000-00-00' || $dateString === '0000-00-00 00:00:00')
        {
            return null;
        }
        $ts = strtotime($dateString);
        if($ts === false)
        {
            $dateClean = str_replace('/', '-', $dateString);
            $ts = strtotime($dateClean);
        }
        if($ts === false || $ts <= 0)
        {
            return null;
        }
        return date('Y-m-d H:i:s', $ts);
    }

    public function dateFormatBeforeSave($dateString)
    {
        $dateString = trim((string)$dateString);
        if($dateString === '' || $dateString === '0000-00-00')
        {
            return null;
        }
        $ts = strtotime($dateString);
        if($ts === false)
        {
            $dateClean = str_replace('/', '-', $dateString);
            $ts = strtotime($dateClean);
        }
        if($ts === false || $ts <= 0)
        {
            return null;
        }
        return date('Y-m-d', $ts);
    }

    function authenticate()
    {
        // Check if the session variable User exists, redirect to loginform if not
        if(!$this->Session->check('User'))
        {
            $this->redirect(array('plugin' => 'admin', 'controller' => 'users', 'action' => 'login_form'));
            exit();
        }
        $sessionUser = (array)$this->Session->read('User');
        if(!empty($sessionUser['User']['login_status']) && strcasecmp(trim((string)$sessionUser['User']['login_status']), 'Blocked') === 0)
        {
            $this->Session->destroy();
            $this->Session->setFlash(__('Login is blocked. Please contact administrator.'), 'flash', array('alert' => 'danger'));
            $this->redirect(array('plugin' => 'admin', 'controller' => 'users', 'action' => 'login_form'));
            exit();
        }
    } 
    protected function _requiredPermissionFieldByAction($actionName)
    {
        $actionName = strtolower(trim((string)$actionName));
        if($actionName === '')
        {
            return 'view_right';
        }
        if(strpos($actionName, 'delete') !== false || strpos($actionName, 'remove') !== false || strpos($actionName, 'destroy') !== false || strpos($actionName, 'trash') !== false)
        {
            // Legacy schema does not have delete_right; search_right is used as delete-right.
            return 'search_right';
        }
        if(strpos($actionName, 'edit') !== false || strpos($actionName, 'update') !== false)
        {
            return 'update_right';
        }
        if(strpos($actionName, 'setting') !== false)
        {
            return 'update_right';
        }
        if(strpos($actionName, 'add') !== false || strpos($actionName, 'create') !== false || strpos($actionName, 'import') !== false || strpos($actionName, 'upload') !== false)
        {
            return 'save_right';
        }
        return 'view_right';
    }
    protected function _normalizePageRightRow($pageRight)
    {
        $pageRight = is_array($pageRight) ? $pageRight : array();
        $canView = !empty($pageRight['view_right']) ? 1 : 0;
        $canAdd = !empty($pageRight['save_right']) ? 1 : 0;
        $canEdit = !empty($pageRight['update_right']) ? 1 : 0;
        $canDelete = !empty($pageRight['search_right']) ? 1 : 0;
        return array(
            'save_right' => $canAdd,
            'update_right' => $canEdit,
            'view_right' => $canView,
            'search_right' => $canDelete
        );
    }
    protected function _pushPermissionActionCandidate(&$candidates, $candidate)
    {
        $candidate = strtolower(trim((string)$candidate));
        if($candidate === '' || in_array($candidate, $candidates, true))
        {
            return;
        }
        $candidates[] = $candidate;
    }
    protected function _permissionActionCandidates($controllerName, $actionName)
    {
        $controllerName = strtolower(trim((string)$controllerName));
        $actionName = strtolower(trim((string)$actionName));
        $requiredField = $this->_requiredPermissionFieldByAction($actionName);
        $candidates = array();

        $this->_pushPermissionActionCandidate($candidates, $actionName);

        if(strpos($actionName, 'transaction') !== false)
        {
            $this->_pushPermissionActionCandidate($candidates, 'transactions');
        }
        if(strpos($actionName, 'template') !== false)
        {
            $this->_pushPermissionActionCandidate($candidates, 'templates');
        }
        if(strpos($actionName, 'setting') !== false)
        {
            $this->_pushPermissionActionCandidate($candidates, 'settings');
        }
        if(strpos($actionName, 'expenseapproval') !== false)
        {
            $this->_pushPermissionActionCandidate($candidates, 'expenseapprovals');
        }
        if(strpos($actionName, 'report') !== false)
        {
            $this->_pushPermissionActionCandidate($candidates, 'reports');
        }
        if(strpos($actionName, 'cashclosing') !== false)
        {
            $this->_pushPermissionActionCandidate($candidates, 'cashclosing');
        }
        if(strpos($actionName, 'unit') !== false)
        {
            $this->_pushPermissionActionCandidate($candidates, 'units');
        }
        if(strpos($actionName, 'allotment') !== false)
        {
            $this->_pushPermissionActionCandidate($candidates, 'allotments');
        }

        if($requiredField !== 'save_right')
        {
            $this->_pushPermissionActionCandidate($candidates, 'manage');
        }
        $this->_pushPermissionActionCandidate($candidates, 'index');

        return $candidates;
    }
    protected function _findPermissionPageRow($ugroupId, $controllerName, $actionName)
    {
        $ugroupId = (int)$ugroupId;
        $controllerName = strtolower(trim((string)$controllerName));
        $actionName = strtolower(trim((string)$actionName));
        if($ugroupId<=0 || $controllerName==='')
        {
            return array();
        }
        $this->loadModel('Page');
        $controllerClean = str_replace('_', '', $controllerName);
        $base = array(
            'PageRight.ugroup_id' => $ugroupId,
            'OR' => array(
                array('LOWER(Page.controller_name)' => $controllerName),
                array('LOWER(Page.controller_name)' => $controllerClean),
                array('LOWER(REPLACE(Page.controller_name, "_", ""))' => $controllerClean)
            )
        );
        $actionCandidates = $this->_permissionActionCandidates($controllerName, $actionName);
        foreach($actionCandidates as $candidateAction)
        {
            $row = $this->Page->find('first', array(
                'joins' => array(
                    array(
                        'table' => 'page_rights',
                        'alias' => 'PageRight',
                        'type' => 'Inner',
                        'conditions' => array('Page.id=PageRight.page_id')
                    )
                ),
                'conditions' => array_merge($base, array('LOWER(Page.action_name)' => $candidateAction)),
                'fields' => array('Page.*','PageRight.*'),
                'order' => array('Page.parent_id' => 'asc', 'Page.ordering' => 'asc', 'Page.id' => 'asc'),
                'recursive' => -1
            ));
            if(!empty($row))
            {
                return $row;
            }
        }

        // Fallback: Check if there is a controller-level page right where action_name is empty or null
        $fallbackRow = $this->Page->find('first', array(
            'joins' => array(
                array(
                    'table' => 'page_rights',
                    'alias' => 'PageRight',
                    'type' => 'Inner',
                    'conditions' => array('Page.id=PageRight.page_id')
                )
            ),
            'conditions' => array_merge($base, array(
                'OR' => array(
                    array('Page.action_name IS NULL'),
                    array('Page.action_name' => '')
                )
            )),
            'fields' => array('Page.*','PageRight.*'),
            'order' => array('Page.parent_id' => 'asc', 'Page.ordering' => 'asc', 'Page.id' => 'asc'),
            'recursive' => -1
        ));
        if(!empty($fallbackRow))
        {
            return $fallbackRow;
        }
        return array();
    }
    protected function _defaultFullPermission()
    {
        return array('save_right' => 1, 'update_right' => 1, 'view_right' => 1, 'search_right' => 1);
    }
    protected function _adminThemeCookieName($userId = 0)
    {
        $userId = (int)$userId;
        if($userId <= 0)
        {
            return 'wv_admin_theme_guest';
        }
        return 'wv_admin_theme_' . $userId;
    }

    protected function _resolveAdminThemeContext($sessionUser = array())
    {
        $sessionUser = is_array($sessionUser) ? $sessionUser : array();
        $userId = !empty($sessionUser['User']['id']) ? (int)$sessionUser['User']['id'] : 0;
        $defaultTheme = 'theme-light';
        $themeOptions = array(
            'theme-light' => __('Professional Light'),
            'theme-dark' => __('Professional Dark'),
            'theme-gold' => __('Royal Gold'),
            'theme-green' => __('Emerald Green')
        );
        try
        {
            $ThemeSetting = ClassRegistry::init('ThemeSetting');
            if($ThemeSetting)
            {
                $settings = (array)$ThemeSetting->currentSettings();
                $themeOptions = (array)$ThemeSetting->adminThemeOptions();
                $defaultTheme = $ThemeSetting->normalizeThemeKey(isset($settings['admin_default_theme']) ? $settings['admin_default_theme'] : 'theme-light');
            }
        }
        catch(Throwable $e)
        {
            $defaultTheme = 'theme-light';
        }

        $cookieName = $this->_adminThemeCookieName($userId);
        $resolvedTheme = $defaultTheme;
        $sessionTheme = (string)$this->Session->read('AdminTheme.active');
        if($sessionTheme !== '' && isset($themeOptions[$sessionTheme]))
        {
            $resolvedTheme = $sessionTheme;
        }
        if(!empty($_COOKIE[$cookieName]))
        {
            $cookieTheme = trim((string)$_COOKIE[$cookieName]);
            if(isset($themeOptions[$cookieTheme]))
            {
                $resolvedTheme = $cookieTheme;
            }
        }
        $this->Session->write('AdminTheme.active', $resolvedTheme);
        return array(
            'active' => $resolvedTheme,
            'default' => $defaultTheme,
            'cookie_name' => $cookieName,
            'options' => $themeOptions
        );
    }

    public function beforeFilter()
    {
        parent::beforeFilter();
        if ($this->layout === 'default' || empty($this->layout)) {
            $this->layout = 'Admin.default';
        }
        $sessionUser = (array)$this->Session->read('User');
        $currAction=strtolower($this->action);
        $currController=strtolower($this->params['controller']);
        if($currController=="admin")
        {
            $this->redirect(array('controller' => 'dashboards', 'action' => 'index'));
            exit();
        }
        $cleanAction = preg_replace('/^admin_/', '', $currAction);
        $isPublicCronOrWebhook = (
            (in_array($currController, array('facebook_integrations', 'facebookintegrations'), true) &&
            in_array($cleanAction, array('cron_autofetch', 'webhook'), true)) ||
            ($currController === 'notifications' && in_array($cleanAction, array('cron_reminders'), true)) ||
            ($currController === 'invpastdues' && in_array($cleanAction, array('downloadletterpdf', 'printletter'), true)) ||
            ($currController === 'deals_payments' && in_array($cleanAction, array('printreceipt'), true))
        );

        if($currAction!='login_form' && $currController!='forgots' && !$isPublicCronOrWebhook)
        {
            $this->authenticate();            
            $sessionUser = $this->_reloadCurrentAdminSessionUser();
            $this->adminValue = $sessionUser;
        }
        if(!$isPublicCronOrWebhook)
        {
            $sessionUser = $this->_resolveAdminImpersonationSessionUser($sessionUser);
            $sessionUser = $this->_normalizeSuperAdminSessionUser();
            $this->adminValue = $sessionUser;
            $this->_maybeRunAdminMenuBootstrap($sessionUser);
            $this->_ensureCeoPermissionCoverage($sessionUser);
            $this->_ensureLeadDashboardPermissions($sessionUser);
            $this->_ensureNotificationPermissions($sessionUser);
            $this->_ensureDealPaymentTablesAutoIncrement();
        }

        // Initialize user identity, role, and agency scope BEFORE checking permissions
        $this->luserId = isset($this->adminValue['User']['id']) ? $this->adminValue['User']['id'] : null;
        $this->agencyId = isset($this->adminValue['User']['project_id']) ? $this->adminValue['User']['project_id'] : null;
        $this->userType = isset($this->adminValue['Ugroup']['type']) ? $this->adminValue['Ugroup']['type'] : '';
        $runtimeGroupName = strtolower(trim((string)(isset($this->adminValue['Ugroup']['name']) ? $this->adminValue['Ugroup']['name'] : '')));
        if(trim((string)$this->userType) === '' && $runtimeGroupName === 'ceo')
        {
            $this->userType = 'CEO';
        }
        $isResolvedSuperAdmin = ($this->_isSuperAdminSession((array)$this->adminValue) || $this->_isSuperAdminSession((array)$sessionUser) || (isset($this->adminValue['User']['ugroup_id']) && (int)$this->adminValue['User']['ugroup_id'] === 1) || (isset($sessionUser['User']['ugroup_id']) && (int)$sessionUser['User']['ugroup_id'] === 1) || (isset($sessionUser['Ugroup']['id']) && (int)$sessionUser['Ugroup']['id'] === 1));
        if($isResolvedSuperAdmin)
        {
            $this->userType = 'ADR';
            if(!isset($this->adminValue['Ugroup']) || !is_array($this->adminValue['Ugroup']))
            {
                $this->adminValue['Ugroup'] = array();
            }
            $this->adminValue['Ugroup']['type'] = 'ADR';
        }
        elseif($this->_isAgencySystemManagerRoleContext((array)$this->adminValue))
        {
            $this->userType = 'AGY';
            $this->agencyId = $this->_resolveSystemManagerActiveAgency();
        }
        elseif($this->_isAgencyAdminSession((array)$this->adminValue) || $this->_isAgencyAdminSession((array)$sessionUser))
        {
            $this->userType = 'AGY';
        }

        $isAgencyAdmin = ($this->_isAgencyAdminSession((array)$this->adminValue) || $this->_isAgencyAdminSession((array)$sessionUser) || $this->userType === 'AGY' || (isset($sessionUser['User']['ugroup_id']) && (int)$sessionUser['User']['ugroup_id'] === 4) || (isset($this->adminValue['User']['ugroup_id']) && (int)$this->adminValue['User']['ugroup_id'] === 4));
        $isSuperAdminOnly = $this->_isSuperAdminOnlyController($currController);

        $isNotificationPollAction = ($currController === 'notifications' && in_array($currAction, array('poll', 'mark_read', 'mark_all_read'), true));
        if($currAction!='login_form' && $currAction!='myprofile' && $currAction!='editprofile' && $currController!='eldialogs' && $currAction!='changepass' && $currAction!='logout' && $currAction!='stopimpersonation' && $currController!='forgots' && !$isPublicCronOrWebhook && !$isNotificationPollAction)
        {
            $userPermissionMeta = $this->userPermission();
            $this->userPermissionArr = !empty($userPermissionMeta['PageRight']) ? $userPermissionMeta['PageRight'] : $this->_defaultFullPermission();
            $this->set('userPermissionArr',$this->userPermissionArr);
        }
        else
        {
            $this->userPermissionArr = $this->_defaultFullPermission();
            $this->set('userPermissionArr',$this->userPermissionArr);
        }

        if($isResolvedSuperAdmin)
        {
            $this->userPermissionArr = $this->_defaultFullPermission();
            $this->set('userPermissionArr', $this->userPermissionArr);
            $this->set('allowAddAction', true);
            $this->set('allowEditAction', true);
            $this->set('allowDeleteAction', true);
            $this->set('allowViewAction', true);
            $this->set('isSuperAdminSession', true);
            $this->set('isAgencyAdminSession', false);
        }
        elseif($isAgencyAdmin && !$isSuperAdminOnly)
        {
            $this->userPermissionArr = $this->_defaultFullPermission();
            $this->set('userPermissionArr', $this->userPermissionArr);
            $this->set('allowAddAction', true);
            $this->set('allowEditAction', true);
            $this->set('allowDeleteAction', true);
            $this->set('allowViewAction', true);
            $this->set('isSuperAdminSession', false);
            $this->set('isAgencyAdminSession', true);
        }
        else
        {
            $this->set('allowAddAction', !empty($this->userPermissionArr['save_right']));
            $this->set('allowEditAction', !empty($this->userPermissionArr['update_right']));
            $this->set('allowDeleteAction', !empty($this->userPermissionArr['search_right']));
            $this->set('allowViewAction', !empty($this->userPermissionArr['view_right']));
            $this->set('isSuperAdminSession', false);
            $this->set('isAgencyAdminSession', false);
        }
        $menu = $this->_loadAdminMenuTree($sessionUser);
        $this->set('menu', $menu);
        if(!empty($menu['slide-out']))
        {
            $this->set('slide-out', $menu['slide-out']);
        }
        if(!in_array('MenuBuilder.MenuBuilder', $this->helpers, true) && !array_key_exists('MenuBuilder.MenuBuilder', $this->helpers))
        {
            $this->helpers[] = 'MenuBuilder.MenuBuilder';
        }
        $this->set('userType',$this->userType);
        $this->set('activeAgencyId',(int)$this->agencyId);
        $this->set('isAgencySystemManager',$this->_isAgencySystemManagerRoleContext((array)$this->adminValue));
        $this->set('assignedAgencyProjectList',$this->_isAgencySystemManagerRoleContext((array)$this->adminValue) ? $this->_assignedAgencyProjectList((int)$this->luserId) : array());
        $this->set('isSuperAdminSession',$isResolvedSuperAdmin);
        $this->set('isAgencyAdminSession',$isAgencyAdmin);
        $this->set('adminImpersonationMeta',(array)$this->Session->read('Impersonation.admin'));
        $adminThemeContext = $this->_resolveAdminThemeContext($sessionUser);
        $this->set('adminThemeClass', $adminThemeContext['active']);
        $this->set('adminDefaultTheme', $adminThemeContext['default']);
        $this->set('adminThemeCookieName', $adminThemeContext['cookie_name']);
        $this->set('adminThemeOptions', $adminThemeContext['options']);
        if(!empty($sessionUser['User']['id']))
        {
            $this->_autoPatchMissingLettersForDeals();
        }
    }
    public function beforeRender()
    {
        parent::beforeRender();
        $this->plugin = 'Admin';
        $currAction = strtolower((string)$this->action);
        if ($this->layout === false || $this->layout === null || $this->layout === '' || strpos($currAction, 'print') !== false) {
            $this->layout = false;
            return;
        }
        if ($this->layout === 'default' || $this->layout === 'Admin.default') {
            $this->layout = 'Admin.default';
        }
    }
    public function afterFilter()
    {
        parent::afterFilter();
        $this->_logAuditTrail();
    }
    protected function _logAuditTrail()
    {
        try
        {
            $sessionUser = (array)$this->Session->read('User');
            if(empty($sessionUser['User']['id']))
            {
                return;
            }
            $controller = strtolower((string)$this->params['controller']);
            $action = strtolower((string)$this->action);
            $method = strtoupper((string)$this->request->method());
            if($controller === 'audittrails')
            {
                return;
            }
            if($controller === 'admin')
            {
                return;
            }
            if(in_array($controller, array('eldialogs', 'forgots'), true))
            {
                return;
            }
            if($controller === 'users' && in_array($action, array('login_form', 'logout', 'stopimpersonation', 'changepass', 'editprofile', 'myprofile'), true))
            {
                return;
            }
            if($this->request->is('ajax') && $method === 'GET')
            {
                return;
            }

            $auditData = array(
                'user_id' => isset($sessionUser['User']['id']) ? (int)$sessionUser['User']['id'] : null,
                'ugroup_id' => isset($sessionUser['User']['ugroup_id']) ? (int)$sessionUser['User']['ugroup_id'] : null,
                'project_id' => $this->_isAgencySystemManagerRoleContext($sessionUser) && (int)$this->agencyId > 0 ? (int)$this->agencyId : (isset($sessionUser['User']['project_id']) ? (int)$sessionUser['User']['project_id'] : null),
                'controller' => $controller,
                'action' => $action,
                'activity' => $this->_inferAuditActivity($controller, $action, $method),
                'method' => $method,
                'url' => $this->request->here(false),
                'ip_address' => $this->_resolveAuditClientIp(),
                'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? substr((string)$_SERVER['HTTP_USER_AGENT'], 0, 250) : '',
                'request_data' => $this->_serializeAuditPayload(array(
                    'query' => (array)$this->request->query,
                    'data' => (array)$this->request->data
                )),
                'created' => date('Y-m-d H:i:s')
            );

            $this->_writeAuditEntry($auditData);
        }
        catch(Throwable $e)
        {
            // Do not block user actions if audit log fails.
        }
    }
    protected function _writeAuditEntry($auditData)
    {
        $Audittrail = ClassRegistry::init('Admin.Audittrail');
        if(!$Audittrail)
        {
            $Audittrail = ClassRegistry::init('Audittrail');
        }
        if(!$Audittrail)
        {
            return;
        }
        $tableExists = false;
        try
        {
            $existsRows = $Audittrail->query("SHOW TABLES LIKE 'audit_trails'");
            $tableExists = !empty($existsRows);
        }
        catch(Throwable $e)
        {
            $tableExists = false;
        }
        if(!$tableExists)
        {
            $Audittrail->query("CREATE TABLE IF NOT EXISTS `audit_trails` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `user_id` int(11) DEFAULT NULL,
                `ugroup_id` int(11) DEFAULT NULL,
                `project_id` int(11) DEFAULT NULL,
                `controller` varchar(100) DEFAULT NULL,
                `action` varchar(100) DEFAULT NULL,
                `activity` varchar(50) DEFAULT NULL,
                `method` varchar(10) DEFAULT NULL,
                `url` text,
                `ip_address` varchar(45) DEFAULT NULL,
                `user_agent` varchar(255) DEFAULT NULL,
                `request_data` longtext,
                `created` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `idx_user_id` (`user_id`),
                KEY `idx_project_id` (`project_id`),
                KEY `idx_created` (`created`),
                KEY `idx_controller` (`controller`),
                KEY `idx_activity` (`activity`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
        }
        $Audittrail->create();
        try
        {
            $Audittrail->save(array('Audittrail' => $auditData), array('validate' => false, 'callbacks' => false));
        }
        catch(Throwable $e)
        {
            // Silently handle audit trail log failures so logout and primary operations never crash
        }
    }
    protected function _inferAuditActivity($controller, $action, $method)
    {
        $action = strtolower(trim((string)$action));
        $method = strtoupper(trim((string)$method));
        if($action === '')
        {
            return 'other';
        }
        if(strpos($action, 'delete') !== false || strpos($action, 'remove') !== false || strpos($action, 'trash') !== false)
        {
            return 'delete';
        }
        if(strpos($action, 'edit') !== false || strpos($action, 'update') !== false)
        {
            return 'edit';
        }
        if(strpos($action, 'add') !== false || strpos($action, 'create') !== false || $method === 'POST')
        {
            return 'add';
        }
        if($method === 'GET')
        {
            return 'view';
        }
        return 'other';
    }
    protected function _resolveAuditClientIp()
    {
        $normalizeIp = function($value)
        {
            $value = trim((string)$value);
            if($value === '')
            {
                return '';
            }
            if(strpos($value, '::ffff:') === 0)
            {
                $mappedIp = substr($value, 7);
                if(filter_var($mappedIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4))
                {
                    return $mappedIp;
                }
            }
            if($value === '::1')
            {
                return '127.0.0.1';
            }
            return filter_var($value, FILTER_VALIDATE_IP) ? $value : '';
        };

        $remoteIp = $normalizeIp(isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '');
        $trustProxyHeaders = (bool)Configure::read('AuditTrail.trustProxyHeaders');
        if(!$trustProxyHeaders && $remoteIp !== '')
        {
            $isPublicRemote = filter_var($remoteIp, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
            $trustProxyHeaders = ($isPublicRemote === false);
        }
        if($trustProxyHeaders)
        {
            $forwardedHeaders = array(
                'HTTP_CF_CONNECTING_IP',
                'HTTP_TRUE_CLIENT_IP',
                'HTTP_X_REAL_IP',
                'HTTP_X_FORWARDED_FOR'
            );
            foreach($forwardedHeaders as $headerName)
            {
                if(empty($_SERVER[$headerName]))
                {
                    continue;
                }
                foreach(explode(',', (string)$_SERVER[$headerName]) as $candidate)
                {
                    $candidateIp = $normalizeIp($candidate);
                    if($candidateIp !== '')
                    {
                        return $candidateIp;
                    }
                }
            }
        }
        return $remoteIp !== '' ? $remoteIp : 'Unknown';
    }
    protected function _serializeAuditPayload($payload)
    {
        $payload = $this->_sanitizeAuditPayload($payload);
        $json = json_encode($payload);
        if($json === false)
        {
            return '';
        }
        if(strlen($json) > 12000)
        {
            $json = substr($json, 0, 12000).'...';
        }
        return $json;
    }
    protected function _sanitizeAuditPayload($payload)
    {
        $redactKeys = array(
            'password', 'pass', 'new_password', 'old_password', 'confirm_password',
            'password_confirmation', 'token', 'api_key', 'apikey', 'access_token',
            'refresh_token', 'authorization', 'cookie', 'session', 'csrf', '_token',
            'secret', 'client_secret', 'tmp_name'
        );
        if(is_array($payload))
        {
            $sanitized = array();
            foreach($payload as $key => $value)
            {
                $keyLower = strtolower((string)$key);
                if(in_array($keyLower, $redactKeys, true))
                {
                    $sanitized[$key] = '***';
                    continue;
                }
                $sanitized[$key] = $this->_sanitizeAuditPayload($value);
            }
            return $sanitized;
        }
        return $payload;
    }

    protected function _ardBusinessMappingFields()
    {
        return array(
            'booking_income_ledger_id' => array('label' => 'Booking Income', 'type' => 'income', 'fallback' => 'Booking Income'),
            'installment_income_ledger_id' => array('label' => 'Installment Income', 'type' => 'income', 'fallback' => 'Installment Income'),
            'rental_adjustment_income_ledger_id' => array('label' => 'Rental Adjustment as Installment', 'type' => 'income', 'fallback' => 'Rental Adjustment as Installment'),
            'late_fee_income_ledger_id' => array('label' => 'Late Fee Income', 'type' => 'income', 'fallback' => 'Late Fee Income'),
            'deal_charges_income_ledger_id' => array('label' => 'Deal Charges Income', 'type' => 'income', 'fallback' => 'Deal Charges Income'),
            'discount_waiver_ledger_id' => array('label' => 'Discount / Waiver', 'type' => 'expense', 'fallback' => 'Discount / Waiver'),
            'refund_payable_ledger_id' => array('label' => 'Refund Payable', 'type' => 'liability', 'fallback' => 'Refund Payable'),
            'cancellation_forfeiture_income_ledger_id' => array('label' => 'Cancellation / Forfeiture', 'type' => 'income', 'fallback' => 'Cancellation / Forfeiture Income'),
            'transfer_fee_income_ledger_id' => array('label' => 'Transfer Fee Income', 'type' => 'income', 'fallback' => 'Transfer Fee Income'),
            'agent_commission_expense_ledger_id' => array('label' => 'Agent Commission', 'type' => 'expense', 'fallback' => 'Agent Commission Expense'),
            'tax_payable_ledger_id' => array('label' => 'Tax Payable', 'type' => 'liability', 'fallback' => 'Tax Payable'),
            'vendor_payable_ledger_id' => array('label' => 'Vendor Payable', 'type' => 'liability', 'fallback' => 'Vendor Payable'),
            'construction_wip_ledger_id' => array('label' => 'Construction / WIP', 'type' => 'asset', 'fallback' => 'Construction WIP'),
            'cash_closing_cash_ledger_id' => array('label' => 'Daily Closing Cash', 'type' => 'asset', 'fallback' => 'Cash In Hand')
        );
    }

    protected function _ensureBusinessAccountingSchema()
    {
        try
        {
            $Model = ClassRegistry::init('Admin.Ardtransaction');
            $Model->query("CREATE TABLE IF NOT EXISTS `ardaccounts_transactions` (
                `id` bigint(20) NOT NULL AUTO_INCREMENT,
                `project_id` int(11) NOT NULL DEFAULT 0,
                `ledger_id` bigint(20) NOT NULL DEFAULT 0,
                `expense_ledger_id` bigint(20) NOT NULL DEFAULT 0,
                `type` varchar(20) NOT NULL DEFAULT 'expense',
                `amount` decimal(15,2) NOT NULL DEFAULT 0.00,
                `description` text,
                `ref_no` varchar(80) DEFAULT '',
                `entry_date` date NOT NULL,
                `construction_site_id` bigint(20) DEFAULT 0,
                `created` datetime DEFAULT NULL,
                `modified` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `project_id` (`project_id`),
                KEY `ledger_id` (`ledger_id`),
                KEY `entry_date` (`entry_date`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3");
            $Model->query("CREATE TABLE IF NOT EXISTS `ardaccounts_settings` (
                `id` bigint(20) NOT NULL AUTO_INCREMENT,
                `project_id` int(11) NOT NULL DEFAULT 0,
                `company_name` varchar(150) DEFAULT '',
                PRIMARY KEY (`id`),
                UNIQUE KEY `project_id` (`project_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3");

            $transactionAlters = array(
                "ALTER TABLE `ardaccounts_transactions` ADD COLUMN `income_ledger_id` bigint(20) NOT NULL DEFAULT 0 AFTER `expense_ledger_id`",
                "ALTER TABLE `ardaccounts_transactions` ADD COLUMN `status` varchar(20) NOT NULL DEFAULT 'Posted' AFTER `type`",
                "ALTER TABLE `ardaccounts_transactions` ADD COLUMN `source_type` varchar(60) DEFAULT '' AFTER `construction_site_id`",
                "ALTER TABLE `ardaccounts_transactions` ADD COLUMN `source_id` bigint(20) NOT NULL DEFAULT 0 AFTER `source_type`",
                "ALTER TABLE `ardaccounts_transactions` ADD COLUMN `auto_posted` tinyint(1) NOT NULL DEFAULT 0 AFTER `source_id`",
                "ALTER TABLE `ardaccounts_transactions` ADD COLUMN `approved_by` int(11) NOT NULL DEFAULT 0 AFTER `auto_posted`",
                "ALTER TABLE `ardaccounts_transactions` ADD COLUMN `approved_at` datetime DEFAULT NULL AFTER `approved_by`",
                "ALTER TABLE `ardaccounts_transactions` ADD COLUMN `posted_by` int(11) NOT NULL DEFAULT 0 AFTER `approved_at`",
                "ALTER TABLE `ardaccounts_transactions` ADD COLUMN `posted_at` datetime DEFAULT NULL AFTER `posted_by`",
                "ALTER TABLE `ardaccounts_transactions` ADD COLUMN `voided_by` int(11) NOT NULL DEFAULT 0 AFTER `posted_at`",
                "ALTER TABLE `ardaccounts_transactions` ADD COLUMN `voided_at` datetime DEFAULT NULL AFTER `voided_by`",
                "ALTER TABLE `ardaccounts_transactions` ADD COLUMN `lock_note` varchar(255) DEFAULT '' AFTER `voided_at`",
                "ALTER TABLE `ardaccounts_transactions` ADD COLUMN `created_by` int(11) NOT NULL DEFAULT 0 AFTER `lock_note`",
                "ALTER TABLE `ardaccounts_transactions` ADD COLUMN `modified_by` int(11) NOT NULL DEFAULT 0 AFTER `created_by`",
                "ALTER TABLE `ardaccounts_transactions` ADD KEY `income_ledger_id` (`income_ledger_id`)",
                "ALTER TABLE `ardaccounts_transactions` ADD KEY `status` (`status`)",
                "ALTER TABLE `ardaccounts_transactions` ADD KEY `source_lookup` (`source_type`,`source_id`)"
            );
            foreach($transactionAlters as $sql)
            {
                try { $Model->query($sql); } catch(Exception $e) {}
            }

            $afterField = 'payroll_payment_expense_ledger_id';
            foreach($this->_ardBusinessMappingFields() as $field => $meta)
            {
                $safeField = preg_replace('/[^a-z0-9_]/i', '', $field);
                if($safeField === '')
                {
                    continue;
                }
                try
                {
                    $Model->query("ALTER TABLE `ardaccounts_settings` ADD COLUMN `".$safeField."` bigint(20) NOT NULL DEFAULT 0 AFTER `".$afterField."`");
                }
                catch(Throwable $e)
                {
                    // Column already exists or previous after-field is unavailable.
                    try { $Model->query("ALTER TABLE `ardaccounts_settings` ADD COLUMN `".$safeField."` bigint(20) NOT NULL DEFAULT 0"); } catch(Exception $ignored) {}
                }
                $afterField = $safeField;
            }

            $Model->query("CREATE TABLE IF NOT EXISTS `ardaccounts_cash_closings` (
                `id` bigint(20) NOT NULL AUTO_INCREMENT,
                `project_id` int(11) NOT NULL DEFAULT 0,
                `closing_date` date NOT NULL,
                `cash_ledger_id` bigint(20) NOT NULL DEFAULT 0,
                `opening_cash` decimal(15,2) NOT NULL DEFAULT 0.00,
                `received_cash` decimal(15,2) NOT NULL DEFAULT 0.00,
                `expense_cash` decimal(15,2) NOT NULL DEFAULT 0.00,
                `bank_deposit` decimal(15,2) NOT NULL DEFAULT 0.00,
                `adjustment_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
                `closing_cash` decimal(15,2) NOT NULL DEFAULT 0.00,
                `counted_cash` decimal(15,2) NOT NULL DEFAULT 0.00,
                `difference_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
                `cashier_user_id` int(11) NOT NULL DEFAULT 0,
                `status` varchar(20) NOT NULL DEFAULT 'Draft',
                `note` text,
                `created_by` int(11) NOT NULL DEFAULT 0,
                `modified_by` int(11) NOT NULL DEFAULT 0,
                `created` datetime DEFAULT NULL,
                `modified` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `project_date_ledger` (`project_id`,`closing_date`,`cash_ledger_id`),
                KEY `project_id` (`project_id`),
                KEY `closing_date` (`closing_date`),
                KEY `cash_ledger_id` (`cash_ledger_id`),
                KEY `status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3");

            try { $Model->query("ALTER TABLE `deal_charges` ADD COLUMN `voucher_transaction_id` bigint(20) NOT NULL DEFAULT 0 AFTER `status`"); } catch(Exception $e) {}
            try { $Model->query("ALTER TABLE `deal_charges` ADD KEY `voucher_transaction_id` (`voucher_transaction_id`)"); } catch(Exception $e) {}
            try { $Model->query("ALTER TABLE `deals_payments` ADD COLUMN `ard_component_transaction_ids` text AFTER `ard_transaction_id`"); } catch(Exception $e) {}
            try { $Model->query("ALTER TABLE `deals` ADD COLUMN `ard_discount_transaction_id` bigint(20) NOT NULL DEFAULT 0 AFTER `discount`"); } catch(Exception $e) {}
            try { $Model->query("ALTER TABLE `deals` ADD KEY `ard_discount_transaction_id` (`ard_discount_transaction_id`)"); } catch(Exception $e) {}
            try { $Model->query("ALTER TABLE `ardaccounts_construction_purchase_invoices` ADD COLUMN `voucher_transaction_id` bigint(20) NOT NULL DEFAULT 0 AFTER `status`"); } catch(Exception $e) {}
            try { $Model->query("ALTER TABLE `ardaccounts_construction_purchase_invoices` ADD KEY `voucher_transaction_id` (`voucher_transaction_id`)"); } catch(Exception $e) {}
            try { $Model->query("ALTER TABLE `deal_refunds` ADD COLUMN `payable_transaction_id` bigint(20) NOT NULL DEFAULT 0 AFTER `net_refund`"); } catch(Exception $e) {}
            try { $Model->query("ALTER TABLE `deal_refunds` ADD COLUMN `forfeiture_transaction_id` bigint(20) NOT NULL DEFAULT 0 AFTER `payable_transaction_id`"); } catch(Exception $e) {}
            try { $Model->query("ALTER TABLE `deal_refunds` ADD KEY `payable_transaction_id` (`payable_transaction_id`)"); } catch(Exception $e) {}
            try { $Model->query("ALTER TABLE `deal_refunds` ADD KEY `forfeiture_transaction_id` (`forfeiture_transaction_id`)"); } catch(Exception $e) {}

            // Enterprise Real Estate Chart of Accounts & Multi-Line GL Tables
            try
            {
                $Model->query("CREATE TABLE IF NOT EXISTS `ardaccounts_chart_of_accounts` (
                  `id` bigint(20) NOT NULL AUTO_INCREMENT,
                  `project_id` int(11) NOT NULL DEFAULT 0,
                  `account_code` varchar(30) NOT NULL,
                  `account_name` varchar(150) NOT NULL,
                  `parent_code` varchar(30) DEFAULT '',
                  `account_type` varchar(20) NOT NULL,
                  `normal_balance` varchar(10) NOT NULL DEFAULT 'debit',
                  `category` varchar(80) DEFAULT '',
                  `is_header` tinyint(1) NOT NULL DEFAULT 0,
                  `is_system_default` tinyint(1) NOT NULL DEFAULT 1,
                  `is_active` tinyint(1) NOT NULL DEFAULT 1,
                  `created` datetime DEFAULT NULL,
                  `modified` datetime DEFAULT NULL,
                  PRIMARY KEY (`id`),
                  UNIQUE KEY `uniq_project_code` (`project_id`, `account_code`),
                  KEY `idx_project_type` (`project_id`, `account_type`),
                  KEY `idx_parent_code` (`parent_code`),
                  KEY `idx_is_active` (`is_active`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

                $Model->query("CREATE TABLE IF NOT EXISTS `ardaccounts_vouchers` (
                  `id` bigint(20) NOT NULL AUTO_INCREMENT,
                  `project_id` int(11) NOT NULL DEFAULT 0,
                  `voucher_no` varchar(80) NOT NULL,
                  `voucher_type` varchar(20) NOT NULL,
                  `voucher_date` date NOT NULL,
                  `narration` text DEFAULT NULL,
                  `total_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
                  `source_module` varchar(60) DEFAULT 'manual',
                  `source_id` bigint(20) NOT NULL DEFAULT 0,
                  `status` varchar(20) NOT NULL DEFAULT 'Posted',
                  `created_by` int(11) NOT NULL DEFAULT 0,
                  `approved_by` int(11) DEFAULT NULL,
                  `approved_at` datetime DEFAULT NULL,
                  `posted_by` int(11) DEFAULT NULL,
                  `posted_at` datetime DEFAULT NULL,
                  `created` datetime DEFAULT NULL,
                  `modified` datetime DEFAULT NULL,
                  PRIMARY KEY (`id`),
                  UNIQUE KEY `uniq_project_voucher_no` (`project_id`, `voucher_no`),
                  KEY `idx_project_date` (`project_id`, `voucher_date`),
                  KEY `idx_source` (`source_module`, `source_id`),
                  KEY `idx_status` (`status`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

                $Model->query("CREATE TABLE IF NOT EXISTS `ardaccounts_voucher_items` (
                  `id` bigint(20) NOT NULL AUTO_INCREMENT,
                  `voucher_id` bigint(20) NOT NULL,
                  `project_id` int(11) NOT NULL DEFAULT 0,
                  `ledger_id` bigint(20) NOT NULL DEFAULT 0,
                  `coa_id` bigint(20) NOT NULL DEFAULT 0,
                  `account_code` varchar(30) DEFAULT '',
                  `debit` decimal(15,2) NOT NULL DEFAULT 0.00,
                  `credit` decimal(15,2) NOT NULL DEFAULT 0.00,
                  `cost_center_site_id` bigint(20) DEFAULT 0,
                  `property_id` int(11) DEFAULT 0,
                  `deal_id` int(11) DEFAULT 0,
                  `party_type` varchar(40) DEFAULT '',
                  `party_id` bigint(20) DEFAULT 0,
                  `narration` varchar(255) DEFAULT '',
                  `created` datetime DEFAULT NULL,
                  PRIMARY KEY (`id`),
                  KEY `idx_voucher_id` (`voucher_id`),
                  KEY `idx_project_ledger` (`project_id`, `ledger_id`),
                  KEY `idx_coa_id` (`coa_id`),
                  KEY `idx_deal_prop` (`deal_id`, `property_id`),
                  KEY `idx_party` (`party_type`, `party_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

                $ledgerAlters = array(
                    "ALTER TABLE `ardaccounts_ledgers` ADD COLUMN `account_code` varchar(30) DEFAULT '' AFTER `category`",
                    "ALTER TABLE `ardaccounts_ledgers` ADD KEY `idx_account_code` (`account_code`)",
                    "ALTER TABLE `ardaccounts_ledgers` ADD COLUMN `account_type` varchar(20) DEFAULT '' AFTER `account_code`",
                    "ALTER TABLE `ardaccounts_ledgers` ADD KEY `idx_account_type` (`account_type`)",
                    "ALTER TABLE `ardaccounts_ledgers` ADD COLUMN `normal_balance` varchar(10) DEFAULT '' AFTER `account_type`",
                    "ALTER TABLE `ardaccounts_ledgers` ADD COLUMN `parent_id` bigint(20) DEFAULT 0 AFTER `normal_balance`",
                    "ALTER TABLE `ardaccounts_ledgers` ADD COLUMN `coa_id` bigint(20) DEFAULT 0 AFTER `parent_id`",
                    "ALTER TABLE `ardaccounts_ledgers` ADD KEY `idx_coa_id` (`coa_id`)",
                    "ALTER TABLE `ardaccounts_ledgers` MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT",
                    "ALTER TABLE `ardaccounts_settings` MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT",
                    "ALTER TABLE `ardaccounts_transactions` MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT",
                    "ALTER TABLE `ardaccounts_journal_entries` MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT",
                    "ALTER TABLE `projects` ADD PRIMARY KEY (`id`)",
                    "ALTER TABLE `projects` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT"
                );
                foreach($ledgerAlters as $sql)
                {
                    try { $Model->query($sql); } catch(Exception $ignored) {}
                }
            }
            catch(Throwable $e) {}

            try { Cache::clear(false, '_cake_model_'); } catch(Exception $e) {}
        }
        catch(Throwable $e)
        {
            // Keep legacy pages alive if schema bootstrap cannot run on restricted hosting.
        }
    }

    protected function _ardIsAccountingAdmin()
    {
        return $this->_isSuperAdminSession((array)$this->adminValue) || $this->_isSuperAdminSession((array)$this->Session->read('User'));
    }

    protected function _ardFetchSettingsRowRaw($projectId)
    {
        $projectId = (int)$projectId;
        if($projectId <= 0)
        {
            return array();
        }
        try
        {
            $Setting = ClassRegistry::init('Admin.Ardsetting');
            $row = (array)$Setting->find('first', array(
                'conditions' => array('Ardsetting.project_id' => $projectId),
                'recursive' => -1
            ));
            return !empty($row['Ardsetting']) ? (array)$row['Ardsetting'] : array();
        }
        catch(Throwable $e)
        {
            return array();
        }
    }

    protected function _ardValidLedgerId($projectId, $ledgerId, $ledgerType = 'any')
    {
        $projectId = (int)$projectId;
        $ledgerId = (int)$ledgerId;
        if($projectId <= 0 || $ledgerId <= 0)
        {
            return 0;
        }
        try
        {
            $Ledger = ClassRegistry::init('Admin.Ardledger');
            $conditions = array(
                'Ardledger.id' => $ledgerId,
                'Ardledger.project_id' => $projectId,
                'Ardledger.is_trashed' => 0
            );
            $ledgerType = strtolower(trim((string)$ledgerType));
            if($ledgerType === 'expense')
            {
                $conditions['Ardledger.type'] = 'expense';
            }
            elseif($ledgerType === 'income')
            {
                $conditions['Ardledger.type'] = 'income';
            }
            elseif($ledgerType === 'liability')
            {
                $conditions['Ardledger.type'] = 'liability';
            }
            elseif($ledgerType === 'asset' || $ledgerType === 'source')
            {
                $conditions['Ardledger.type'] = array('asset', 'bank');
            }
            $row = (array)$Ledger->find('first', array('fields' => array('Ardledger.id'), 'conditions' => $conditions, 'recursive' => -1));
            return !empty($row['Ardledger']['id']) ? (int)$row['Ardledger']['id'] : 0;
        }
        catch(Throwable $e)
        {
            return 0;
        }
    }

    protected function _ardGetOrCreateLedger($projectId, $name, $type = 'income', $category = 'Auto Business Account')
    {
        $projectId = (int)$projectId;
        $name = trim((string)$name);
        $type = strtolower(trim((string)$type));
        if($projectId <= 0 || $name === '')
        {
            return 0;
        }
        if($type === '')
        {
            $type = 'income';
        }
        try
        {
            $Ledger = ClassRegistry::init('Admin.Ardledger');
            $row = (array)$Ledger->find('first', array(
                'fields' => array('Ardledger.id', 'Ardledger.is_trashed'),
                'conditions' => array('Ardledger.project_id' => $projectId, 'Ardledger.name' => $name),
                'recursive' => -1
            ));
            if(!empty($row['Ardledger']['id']))
            {
                if(!empty($row['Ardledger']['is_trashed']))
                {
                    $Ledger->id = (int)$row['Ardledger']['id'];
                    $Ledger->saveField('is_trashed', 0, false);
                }
                return (int)$row['Ardledger']['id'];
            }
            $Ledger->create();
            if($Ledger->save(array('Ardledger' => array(
                'project_id' => $projectId,
                'name' => $name,
                'type' => $type,
                'category' => trim((string)$category),
                'balance' => 0,
                'is_trashed' => 0
            )), false))
            {
                return (int)$Ledger->id;
            }
        }
        catch(Throwable $e)
        {
            return 0;
        }
        return 0;
    }

    protected function _ardEnsureRentalPaymentLedger($projectId)
    {
        return $this->_ardGetOrCreateLedger(
            (int)$projectId,
            'Rental-Payments',
            'expense',
            'Auto Rental Expense'
        );
    }

    protected function _ardEnsureRentalIncomeLedger($projectId)
    {
        return $this->_ardGetOrCreateLedger(
            (int)$projectId,
            'Rental-Income',
            'income',
            'Auto Rental Income'
        );
    }

    protected function _ardEnsureRentalAdjustmentIncomeLedger($projectId)
    {
        $projectId = (int)$projectId;
        $ledgerId = $this->_ardGetOrCreateLedger(
            $projectId,
            'Rental Adjustment as Installment',
            'income',
            'Auto Rental Adjustment Income'
        );
        if($projectId > 0 && $ledgerId > 0)
        {
            try
            {
                $Setting = ClassRegistry::init('Admin.Ardsetting');
                $Setting->query("INSERT IGNORE INTO `ardaccounts_settings` (`project_id`,`created`,`modified`) VALUES (".$projectId.",NOW(),NOW())");
                $Setting->updateAll(
                    array(
                        'Ardsetting.rental_adjustment_income_ledger_id' => $ledgerId,
                        'Ardsetting.modified' => "'".date('Y-m-d H:i:s')."'"
                    ),
                    array('Ardsetting.project_id' => $projectId)
                );
            }
            catch(Throwable $e)
            {
                CakeLog::write('error', 'Rental adjustment ledger mapping failed for agency '.$projectId.': '.$e->getMessage());
            }
        }
        return $ledgerId;
    }

    protected function _ardProvisionAgencyAccountingLedgers($projectId)
    {
        $projectId = (int)$projectId;
        if($projectId <= 0)
        {
            return false;
        }
        $this->_ensureBusinessAccountingSchema();
        try
        {
            $this->_ardProvisionRealEstateChartOfAccounts($projectId);

            $Setting = ClassRegistry::init('Admin.Ardsetting');
            $Setting->query("INSERT IGNORE INTO `ardaccounts_settings` (`project_id`,`created`,`modified`) VALUES (".$projectId.",NOW(),NOW())");
            $settings = $this->_ardFetchSettingsRowRaw($projectId);
            $updates = array();
            foreach($this->_ardBusinessMappingFields() as $field => $meta)
            {
                $type = strtolower(trim((string)$meta['type']));
                if(!in_array($type, array('income', 'expense'), true) || $field === 'discount_waiver_ledger_id')
                {
                    continue;
                }
                $existingId = !empty($settings[$field]) ? (int)$settings[$field] : 0;
                if($this->_ardValidLedgerId($projectId, $existingId, $type) > 0)
                {
                    continue;
                }
                $ledgerId = ($field === 'rental_adjustment_income_ledger_id')
                    ? $this->_ardEnsureRentalAdjustmentIncomeLedger($projectId)
                    : $this->_ardGetOrCreateLedger($projectId, $meta['fallback'], $type, 'Auto Business Default');
                if($ledgerId > 0)
                {
                    $updates['Ardsetting.'.$field] = $ledgerId;
                }
            }
            $this->_ardEnsureRentalPaymentLedger($projectId);
            $this->_ardEnsureRentalIncomeLedger($projectId);
            if(!empty($updates))
            {
                $updates['Ardsetting.modified'] = "'".date('Y-m-d H:i:s')."'";
                $Setting->updateAll($updates, array('Ardsetting.project_id' => $projectId));
            }
            return true;
        }
        catch(Throwable $e)
        {
            CakeLog::write('error', 'Agency accounting ledger provisioning failed for agency '.$projectId.': '.$e->getMessage());
            return false;
        }
    }

    public function _ardRealEstateCoaCatalog()
    {
        return array(
            // 10000 ASSETS
            array('code'=>'10000', 'name'=>'ASSETS', 'parent'=>'', 'type'=>'asset', 'normal'=>'debit', 'cat'=>'Assets', 'header'=>1, 'ledger_type'=>'asset'),
            array('code'=>'11000', 'name'=>'Current Assets', 'parent'=>'10000', 'type'=>'asset', 'normal'=>'debit', 'cat'=>'Current Assets', 'header'=>1, 'ledger_type'=>'asset'),
            array('code'=>'11100', 'name'=>'Cash & Bank Balances', 'parent'=>'11000', 'type'=>'asset', 'normal'=>'debit', 'cat'=>'Cash & Bank', 'header'=>1, 'ledger_type'=>'bank'),
            array('code'=>'11110', 'name'=>'Cash in Hand (Main Safe)', 'parent'=>'11100', 'type'=>'asset', 'normal'=>'debit', 'cat'=>'Cash', 'header'=>0, 'ledger_type'=>'bank', 'field'=>'cash_closing_cash_ledger_id', 'alias'=>array('Cash-inhand', 'Cash In Hand')),
            array('code'=>'11120', 'name'=>'Petty Cash (Site Office)', 'parent'=>'11100', 'type'=>'asset', 'normal'=>'debit', 'cat'=>'Cash', 'header'=>0, 'ledger_type'=>'bank', 'alias'=>array('Paty Cash', 'Petty Cash')),
            array('code'=>'11130', 'name'=>'Primary Bank Account', 'parent'=>'11100', 'type'=>'asset', 'normal'=>'debit', 'cat'=>'Bank', 'header'=>0, 'ledger_type'=>'bank', 'field'=>'deal_receipt_online_ledger_id', 'alias'=>array('UBL Account Official', 'UBL old for houses', 'General Receipt Account')),
            array('code'=>'11140', 'name'=>'Secondary Bank Account', 'parent'=>'11100', 'type'=>'asset', 'normal'=>'debit', 'cat'=>'Bank', 'header'=>0, 'ledger_type'=>'bank'),
            array('code'=>'11200', 'name'=>'Accounts & Installments Receivable', 'parent'=>'11000', 'type'=>'asset', 'normal'=>'debit', 'cat'=>'Receivables', 'header'=>1, 'ledger_type'=>'asset'),
            array('code'=>'11210', 'name'=>'Plot / Property Installments Receivable', 'parent'=>'11200', 'type'=>'asset', 'normal'=>'debit', 'cat'=>'Receivables', 'header'=>0, 'ledger_type'=>'asset'),
            array('code'=>'11220', 'name'=>'Tenant Rental Receivables', 'parent'=>'11200', 'type'=>'asset', 'normal'=>'debit', 'cat'=>'Receivables', 'header'=>0, 'ledger_type'=>'asset'),
            array('code'=>'11230', 'name'=>'Society Maintenance & Utility Receivables', 'parent'=>'11200', 'type'=>'asset', 'normal'=>'debit', 'cat'=>'Receivables', 'header'=>0, 'ledger_type'=>'asset'),
            array('code'=>'11300', 'name'=>'Advances & Prepayments', 'parent'=>'11000', 'type'=>'asset', 'normal'=>'debit', 'cat'=>'Advances', 'header'=>1, 'ledger_type'=>'asset'),
            array('code'=>'11310', 'name'=>'Advance to Material Vendors', 'parent'=>'11300', 'type'=>'asset', 'normal'=>'debit', 'cat'=>'Advances', 'header'=>0, 'ledger_type'=>'asset'),
            array('code'=>'11320', 'name'=>'Advance to Subcontractors & Labour', 'parent'=>'11300', 'type'=>'asset', 'normal'=>'debit', 'cat'=>'Advances', 'header'=>0, 'ledger_type'=>'asset'),
            array('code'=>'11330', 'name'=>'Advance Income Tax / WHT Deducted', 'parent'=>'11300', 'type'=>'asset', 'normal'=>'debit', 'cat'=>'Tax', 'header'=>0, 'ledger_type'=>'asset'),
            array('code'=>'11400', 'name'=>'Project Inventory & Work in Progress', 'parent'=>'11000', 'type'=>'asset', 'normal'=>'debit', 'cat'=>'Inventory', 'header'=>1, 'ledger_type'=>'asset'),
            array('code'=>'11410', 'name'=>'Land Held for Development', 'parent'=>'11400', 'type'=>'asset', 'normal'=>'debit', 'cat'=>'Land Inventory', 'header'=>0, 'ledger_type'=>'asset'),
            array('code'=>'11420', 'name'=>'Construction Work-in-Progress (CWIP)', 'parent'=>'11400', 'type'=>'asset', 'normal'=>'debit', 'cat'=>'Construction WIP', 'header'=>0, 'ledger_type'=>'asset', 'field'=>'construction_wip_ledger_id', 'alias'=>array('Construction WIP')),
            array('code'=>'11430', 'name'=>'Construction Material Stock (Stores)', 'parent'=>'11400', 'type'=>'asset', 'normal'=>'debit', 'cat'=>'Stock Inventory', 'header'=>0, 'ledger_type'=>'asset'),
            array('code'=>'11440', 'name'=>'Finished Units Available for Sale', 'parent'=>'11400', 'type'=>'asset', 'normal'=>'debit', 'cat'=>'Finished Units', 'header'=>0, 'ledger_type'=>'asset'),
            array('code'=>'12000', 'name'=>'Non-Current & Fixed Assets', 'parent'=>'10000', 'type'=>'asset', 'normal'=>'debit', 'cat'=>'Fixed Assets', 'header'=>1, 'ledger_type'=>'asset'),
            array('code'=>'12100', 'name'=>'Head Office Premises & Buildings', 'parent'=>'12000', 'type'=>'asset', 'normal'=>'debit', 'cat'=>'Fixed Asset', 'header'=>0, 'ledger_type'=>'asset'),
            array('code'=>'12200', 'name'=>'Site Machinery & Heavy Equipment', 'parent'=>'12000', 'type'=>'asset', 'normal'=>'debit', 'cat'=>'Fixed Asset', 'header'=>0, 'ledger_type'=>'asset'),
            array('code'=>'12300', 'name'=>'Company Vehicles', 'parent'=>'12000', 'type'=>'asset', 'normal'=>'debit', 'cat'=>'Fixed Asset', 'header'=>0, 'ledger_type'=>'asset'),
            array('code'=>'12400', 'name'=>'Office Furniture, Computers & IT', 'parent'=>'12000', 'type'=>'asset', 'normal'=>'debit', 'cat'=>'Fixed Asset', 'header'=>0, 'ledger_type'=>'asset'),
            array('code'=>'12500', 'name'=>'Accumulated Depreciation', 'parent'=>'12000', 'type'=>'asset', 'normal'=>'credit', 'cat'=>'Contra Asset', 'header'=>0, 'ledger_type'=>'asset'),

            // 20000 LIABILITIES
            array('code'=>'20000', 'name'=>'LIABILITIES', 'parent'=>'', 'type'=>'liability', 'normal'=>'credit', 'cat'=>'Liabilities', 'header'=>1, 'ledger_type'=>'liability'),
            array('code'=>'21000', 'name'=>'Current Liabilities', 'parent'=>'20000', 'type'=>'liability', 'normal'=>'credit', 'cat'=>'Current Liabilities', 'header'=>1, 'ledger_type'=>'liability'),
            array('code'=>'21100', 'name'=>'Accounts Payable (Trade Creditors)', 'parent'=>'21000', 'type'=>'liability', 'normal'=>'credit', 'cat'=>'Payables', 'header'=>1, 'ledger_type'=>'liability'),
            array('code'=>'21110', 'name'=>'Material Vendors Accounts Payable', 'parent'=>'21100', 'type'=>'liability', 'normal'=>'credit', 'cat'=>'Payables', 'header'=>0, 'ledger_type'=>'liability', 'field'=>'vendor_payable_ledger_id', 'alias'=>array('Vendor Payable')),
            array('code'=>'21120', 'name'=>'Subcontractor & Civil Labour Payable', 'parent'=>'21100', 'type'=>'liability', 'normal'=>'credit', 'cat'=>'Payables', 'header'=>0, 'ledger_type'=>'liability'),
            array('code'=>'21130', 'name'=>'Retention Money Payable (Contractors)', 'parent'=>'21100', 'type'=>'liability', 'normal'=>'credit', 'cat'=>'Payables', 'header'=>0, 'ledger_type'=>'liability'),
            array('code'=>'21200', 'name'=>'Customer Advances & Unearned Revenue', 'parent'=>'21000', 'type'=>'liability', 'normal'=>'credit', 'cat'=>'Customer Advances', 'header'=>1, 'ledger_type'=>'liability'),
            array('code'=>'21210', 'name'=>'Customer Booking Advances (Unearned)', 'parent'=>'21200', 'type'=>'liability', 'normal'=>'credit', 'cat'=>'Customer Advances', 'header'=>0, 'ledger_type'=>'liability'),
            array('code'=>'21220', 'name'=>'Installments Received in Advance', 'parent'=>'21200', 'type'=>'liability', 'normal'=>'credit', 'cat'=>'Customer Advances', 'header'=>0, 'ledger_type'=>'liability'),
            array('code'=>'21230', 'name'=>'Customer Refund Payable (Cancelled Deals)', 'parent'=>'21200', 'type'=>'liability', 'normal'=>'credit', 'cat'=>'Refunds', 'header'=>0, 'ledger_type'=>'liability', 'field'=>'refund_payable_ledger_id', 'alias'=>array('Refund Payable')),
            array('code'=>'21300', 'name'=>'Accrued & Other Liabilities', 'parent'=>'21000', 'type'=>'liability', 'normal'=>'credit', 'cat'=>'Accrued Liabilities', 'header'=>1, 'ledger_type'=>'liability'),
            array('code'=>'21310', 'name'=>'Sales Agent & Broker Commission Payable', 'parent'=>'21300', 'type'=>'liability', 'normal'=>'credit', 'cat'=>'Commission Payable', 'header'=>0, 'ledger_type'=>'liability', 'alias'=>array('Agent Commission Payable')),
            array('code'=>'21320', 'name'=>'Accrued Salaries & Wages Payable', 'parent'=>'21300', 'type'=>'liability', 'normal'=>'credit', 'cat'=>'Payroll Payable', 'header'=>0, 'ledger_type'=>'liability'),
            array('code'=>'21330', 'name'=>'Tenant Security Deposits (Refundable)', 'parent'=>'21300', 'type'=>'liability', 'normal'=>'credit', 'cat'=>'Rental Deposits', 'header'=>0, 'ledger_type'=>'liability'),
            array('code'=>'21340', 'name'=>'Utility & Maintenance Charges Payable', 'parent'=>'21300', 'type'=>'liability', 'normal'=>'credit', 'cat'=>'Utilities Payable', 'header'=>0, 'ledger_type'=>'liability'),
            array('code'=>'21400', 'name'=>'Statutory & Tax Liabilities', 'parent'=>'21000', 'type'=>'liability', 'normal'=>'credit', 'cat'=>'Tax Liabilities', 'header'=>1, 'ledger_type'=>'liability'),
            array('code'=>'21410', 'name'=>'Withholding Tax Payable', 'parent'=>'21400', 'type'=>'liability', 'normal'=>'credit', 'cat'=>'Tax', 'header'=>0, 'ledger_type'=>'liability', 'field'=>'tax_payable_ledger_id', 'alias'=>array('Tax Payable')),
            array('code'=>'21420', 'name'=>'Sales Tax / Provincial Tax Payable', 'parent'=>'21400', 'type'=>'liability', 'normal'=>'credit', 'cat'=>'Tax', 'header'=>0, 'ledger_type'=>'liability'),
            array('code'=>'22000', 'name'=>'Long-Term Liabilities', 'parent'=>'20000', 'type'=>'liability', 'normal'=>'credit', 'cat'=>'Long Term Liabilities', 'header'=>1, 'ledger_type'=>'liability'),
            array('code'=>'22100', 'name'=>'Bank Project Financing & Mortgages', 'parent'=>'22000', 'type'=>'liability', 'normal'=>'credit', 'cat'=>'Bank Loans', 'header'=>0, 'ledger_type'=>'liability'),
            array('code'=>'22200', 'name'=>'Investor Long-Term Capital Loans', 'parent'=>'22000', 'type'=>'liability', 'normal'=>'credit', 'cat'=>'Investor Financing', 'header'=>0, 'ledger_type'=>'liability'),

            // 30000 EQUITY / CAPITAL
            array('code'=>'30000', 'name'=>'EQUITY & CAPITAL', 'parent'=>'', 'type'=>'equity', 'normal'=>'credit', 'cat'=>'Equity', 'header'=>1, 'ledger_type'=>'equity'),
            array('code'=>'31000', 'name'=>'Owner / Partner Capital', 'parent'=>'30000', 'type'=>'equity', 'normal'=>'credit', 'cat'=>'Capital', 'header'=>1, 'ledger_type'=>'equity'),
            array('code'=>'31100', 'name'=>'Owner / Partner Capital Account', 'parent'=>'31000', 'type'=>'equity', 'normal'=>'credit', 'cat'=>'Capital', 'header'=>0, 'ledger_type'=>'equity', 'alias'=>array('Owner', 'Sajjad Sabir')),
            array('code'=>'31200', 'name'=>'Partner Drawings & Personal Withdrawals', 'parent'=>'31000', 'type'=>'equity', 'normal'=>'debit', 'cat'=>'Drawings', 'header'=>0, 'ledger_type'=>'equity'),
            array('code'=>'32000', 'name'=>'Reserves & Retained Earnings', 'parent'=>'30000', 'type'=>'equity', 'normal'=>'credit', 'cat'=>'Retained Earnings', 'header'=>1, 'ledger_type'=>'equity'),
            array('code'=>'32100', 'name'=>'Retained Earnings (Prior Years Profit/Loss)', 'parent'=>'32000', 'type'=>'equity', 'normal'=>'credit', 'cat'=>'Retained Earnings', 'header'=>0, 'ledger_type'=>'equity'),
            array('code'=>'32200', 'name'=>'Current Year Profit / (Loss)', 'parent'=>'32000', 'type'=>'equity', 'normal'=>'credit', 'cat'=>'Current Earnings', 'header'=>0, 'ledger_type'=>'equity'),

            // 40000 REVENUE / INCOME
            array('code'=>'40000', 'name'=>'REVENUE & OPERATING INCOME', 'parent'=>'', 'type'=>'income', 'normal'=>'credit', 'cat'=>'Revenue', 'header'=>1, 'ledger_type'=>'income'),
            array('code'=>'41000', 'name'=>'Property Sales & Recognized Revenue', 'parent'=>'40000', 'type'=>'income', 'normal'=>'credit', 'cat'=>'Sales Revenue', 'header'=>1, 'ledger_type'=>'income'),
            array('code'=>'41100', 'name'=>'Plot / Property Sales Revenue', 'parent'=>'41000', 'type'=>'income', 'normal'=>'credit', 'cat'=>'Sales Revenue', 'header'=>0, 'ledger_type'=>'income'),
            array('code'=>'41200', 'name'=>'Booking Income', 'parent'=>'41000', 'type'=>'income', 'normal'=>'credit', 'cat'=>'Sales Revenue', 'header'=>0, 'ledger_type'=>'income', 'field'=>'booking_income_ledger_id', 'alias'=>array('Booking Income')),
            array('code'=>'41300', 'name'=>'Installment Income', 'parent'=>'41000', 'type'=>'income', 'normal'=>'credit', 'cat'=>'Sales Revenue', 'header'=>0, 'ledger_type'=>'income', 'field'=>'installment_income_ledger_id', 'alias'=>array('Installment Income')),
            array('code'=>'41400', 'name'=>'Deal Additional Charges Income', 'parent'=>'41000', 'type'=>'income', 'normal'=>'credit', 'cat'=>'Sales Revenue', 'header'=>0, 'ledger_type'=>'income', 'field'=>'deal_charges_income_ledger_id', 'alias'=>array('Deal Charges Income')),
            array('code'=>'42000', 'name'=>'Real Estate Service & Operational Income', 'parent'=>'40000', 'type'=>'income', 'normal'=>'credit', 'cat'=>'Operational Income', 'header'=>1, 'ledger_type'=>'income'),
            array('code'=>'42100', 'name'=>'Plot Ownership Transfer Fee Income', 'parent'=>'42000', 'type'=>'income', 'normal'=>'credit', 'cat'=>'Transfer Fees', 'header'=>0, 'ledger_type'=>'income', 'field'=>'transfer_fee_income_ledger_id', 'alias'=>array('Transfer Fee Income')),
            array('code'=>'42200', 'name'=>'Demarcation, Map & Possession Fee Income', 'parent'=>'42000', 'type'=>'income', 'normal'=>'credit', 'cat'=>'Documentation Fees', 'header'=>0, 'ledger_type'=>'income'),
            array('code'=>'42300', 'name'=>'Cancellation & Forfeiture Fee Income', 'parent'=>'42000', 'type'=>'income', 'normal'=>'credit', 'cat'=>'Cancellation Income', 'header'=>0, 'ledger_type'=>'income', 'field'=>'cancellation_forfeiture_income_ledger_id', 'alias'=>array('Cancellation / Forfeiture Income')),
            array('code'=>'42400', 'name'=>'Late Payment Surcharge / Penalty Income', 'parent'=>'42000', 'type'=>'income', 'normal'=>'credit', 'cat'=>'Surcharge Income', 'header'=>0, 'ledger_type'=>'income', 'field'=>'late_fee_income_ledger_id', 'alias'=>array('Late Fee Income')),
            array('code'=>'42500', 'name'=>'Rental Property Income', 'parent'=>'42000', 'type'=>'income', 'normal'=>'credit', 'cat'=>'Rental Income', 'header'=>0, 'ledger_type'=>'income', 'alias'=>array('Rental-Income')),
            array('code'=>'42600', 'name'=>'Rental Adjustment as Installment', 'parent'=>'42000', 'type'=>'income', 'normal'=>'credit', 'cat'=>'Rental Adjustment', 'header'=>0, 'ledger_type'=>'income', 'field'=>'rental_adjustment_income_ledger_id', 'alias'=>array('Rental Adjustment as Installment')),
            array('code'=>'42700', 'name'=>'Agency & Brokerage Commission Income', 'parent'=>'42000', 'type'=>'income', 'normal'=>'credit', 'cat'=>'Brokerage Income', 'header'=>0, 'ledger_type'=>'income'),
            array('code'=>'42800', 'name'=>'Utility & Maintenance Billing Revenue', 'parent'=>'42000', 'type'=>'income', 'normal'=>'credit', 'cat'=>'Utility Revenue', 'header'=>0, 'ledger_type'=>'income', 'field'=>'billing_income_ledger_id', 'alias'=>array('Billing Utility Revenue')),
            array('code'=>'42900', 'name'=>'Miscellaneous Receipts & Other Income', 'parent'=>'42000', 'type'=>'income', 'normal'=>'credit', 'cat'=>'Other Income', 'header'=>0, 'ledger_type'=>'income'),

            // 50000 COST OF SALES / DIRECT DEVELOPMENT
            array('code'=>'50000', 'name'=>'COST OF SALES & DEVELOPMENT', 'parent'=>'', 'type'=>'expense', 'normal'=>'debit', 'cat'=>'Cost of Sales', 'header'=>1, 'ledger_type'=>'expense'),
            array('code'=>'51000', 'name'=>'Direct Property Development Cost', 'parent'=>'50000', 'type'=>'expense', 'normal'=>'debit', 'cat'=>'Cost of Sales', 'header'=>1, 'ledger_type'=>'expense'),
            array('code'=>'51100', 'name'=>'Cost of Plots / Units Sold (COGS from CWIP)', 'parent'=>'51000', 'type'=>'expense', 'normal'=>'debit', 'cat'=>'Cost of Sales', 'header'=>0, 'ledger_type'=>'expense'),
            array('code'=>'51200', 'name'=>'Direct Land Development Cost Allocated', 'parent'=>'51000', 'type'=>'expense', 'normal'=>'debit', 'cat'=>'Cost of Sales', 'header'=>0, 'ledger_type'=>'expense'),
            array('code'=>'51300', 'name'=>'Direct Site Infrastructure & Roads Allocated', 'parent'=>'51000', 'type'=>'expense', 'normal'=>'debit', 'cat'=>'Cost of Sales', 'header'=>0, 'ledger_type'=>'expense'),

            // 60000 OPERATING & ADMINISTRATIVE EXPENSES
            array('code'=>'60000', 'name'=>'OPERATING & ADMINISTRATIVE EXPENSES', 'parent'=>'', 'type'=>'expense', 'normal'=>'debit', 'cat'=>'Operating Expenses', 'header'=>1, 'ledger_type'=>'expense'),
            array('code'=>'61000', 'name'=>'Staff Salaries & Payroll Expenses', 'parent'=>'60000', 'type'=>'expense', 'normal'=>'debit', 'cat'=>'Payroll', 'header'=>1, 'ledger_type'=>'expense'),
            array('code'=>'61100', 'name'=>'Staff Salaries & Allowances', 'parent'=>'61000', 'type'=>'expense', 'normal'=>'debit', 'cat'=>'Payroll', 'header'=>0, 'ledger_type'=>'expense', 'field'=>'payroll_payment_expense_ledger_id', 'alias'=>array('HR Payroll', 'Staff Salary')),
            array('code'=>'61200', 'name'=>'Staff Overtime & Bonuses', 'parent'=>'61000', 'type'=>'expense', 'normal'=>'debit', 'cat'=>'Payroll', 'header'=>0, 'ledger_type'=>'expense'),
            array('code'=>'61300', 'name'=>'Employee Welfare, Medical & Tea', 'parent'=>'61000', 'type'=>'expense', 'normal'=>'debit', 'cat'=>'Office', 'header'=>0, 'ledger_type'=>'expense', 'alias'=>array('Tea', 'Grocery')),
            array('code'=>'62000', 'name'=>'Sales, Marketing & Agency Commissions', 'parent'=>'60000', 'type'=>'expense', 'normal'=>'debit', 'cat'=>'Marketing', 'header'=>1, 'ledger_type'=>'expense'),
            array('code'=>'62100', 'name'=>'Sales Agent & Broker Commissions Expense', 'parent'=>'62000', 'type'=>'expense', 'normal'=>'debit', 'cat'=>'Commission Expense', 'header'=>0, 'ledger_type'=>'expense', 'field'=>'agent_commission_expense_ledger_id', 'alias'=>array('Agent Commission Expense', 'Paid Agents Commission')),
            array('code'=>'62200', 'name'=>'Digital Marketing, Social Media & Ads', 'parent'=>'62000', 'type'=>'expense', 'normal'=>'debit', 'cat'=>'Marketing', 'header'=>0, 'ledger_type'=>'expense', 'alias'=>array('Marketing')),
            array('code'=>'62300', 'name'=>'Billboards, Signage & Print Media', 'parent'=>'62000', 'type'=>'expense', 'normal'=>'debit', 'cat'=>'Marketing', 'header'=>0, 'ledger_type'=>'expense'),
            array('code'=>'62400', 'name'=>'Client Hospitality & Expo Events', 'parent'=>'62000', 'type'=>'expense', 'normal'=>'debit', 'cat'=>'Marketing', 'header'=>0, 'ledger_type'=>'expense'),
            array('code'=>'62500', 'name'=>'Discounts & Waivers Allowed', 'parent'=>'62000', 'type'=>'expense', 'normal'=>'debit', 'cat'=>'Discounts', 'header'=>0, 'ledger_type'=>'expense', 'field'=>'discount_waiver_ledger_id', 'alias'=>array('Discount / Waiver')),
            array('code'=>'63000', 'name'=>'General Office & Administrative Expenses', 'parent'=>'60000', 'type'=>'expense', 'normal'=>'debit', 'cat'=>'Office Expenses', 'header'=>1, 'ledger_type'=>'expense'),
            array('code'=>'63100', 'name'=>'Office Rent', 'parent'=>'63000', 'type'=>'expense', 'normal'=>'debit', 'cat'=>'Rent', 'header'=>0, 'ledger_type'=>'expense', 'alias'=>array('Rental-Payments', 'Office Rent')),
            array('code'=>'63200', 'name'=>'Office Electricity, Gas & Water Bills', 'parent'=>'63000', 'type'=>'expense', 'normal'=>'debit', 'cat'=>'Utilities', 'header'=>0, 'ledger_type'=>'expense', 'alias'=>array('Electricity Bill')),
            array('code'=>'63300', 'name'=>'Internet, Wifi & Telephone Bills', 'parent'=>'63000', 'type'=>'expense', 'normal'=>'debit', 'cat'=>'Office', 'header'=>0, 'ledger_type'=>'expense', 'alias'=>array('Wifi Bill', 'Mobile Packges')),
            array('code'=>'63400', 'name'=>'Office Stationery, Printing & Postage', 'parent'=>'63000', 'type'=>'expense', 'normal'=>'debit', 'cat'=>'Office', 'header'=>0, 'ledger_type'=>'expense', 'alias'=>array('Office')),
            array('code'=>'63500', 'name'=>'Vehicle Fuel, Bike & Travel Expense', 'parent'=>'63000', 'type'=>'expense', 'normal'=>'debit', 'cat'=>'Vehicle', 'header'=>0, 'ledger_type'=>'expense', 'alias'=>array('Car Expanse', 'Bike')),
            array('code'=>'63600', 'name'=>'Legal, Regulatory & Government Fees', 'parent'=>'63000', 'type'=>'expense', 'normal'=>'debit', 'cat'=>'Legal', 'header'=>0, 'ledger_type'=>'expense'),
            array('code'=>'63700', 'name'=>'Depreciation Expense', 'parent'=>'63000', 'type'=>'expense', 'normal'=>'debit', 'cat'=>'Depreciation', 'header'=>0, 'ledger_type'=>'expense'),
            array('code'=>'63800', 'name'=>'Bank Charges & Financial Deductions', 'parent'=>'63000', 'type'=>'expense', 'normal'=>'debit', 'cat'=>'Bank Charges', 'header'=>0, 'ledger_type'=>'expense'),
            array('code'=>'63900', 'name'=>'General Office Maintenance & Repairs', 'parent'=>'63000', 'type'=>'expense', 'normal'=>'debit', 'cat'=>'General Expense', 'header'=>0, 'ledger_type'=>'expense', 'field'=>'purchase_payment_expense_ledger_id', 'alias'=>array('General Expense Account', 'Garbage', 'Chairty', 'Others'))
        );
    }

    public function _ardProvisionRealEstateChartOfAccounts($projectId)
    {
        $projectId = (int)$projectId;
        if($projectId <= 0)
        {
            return false;
        }
        $this->_ensureBusinessAccountingSchema();
        try
        {
            $ChartOfAccount = ClassRegistry::init('Admin.Ardchartofaccount');
            $Ledger = ClassRegistry::init('Admin.Ardledger');
            $Setting = ClassRegistry::init('Admin.Ardsetting');
            $catalog = $this->_ardRealEstateCoaCatalog();

            // 1. Seed or update chart of accounts table
            foreach($catalog as $item)
            {
                $code = addslashes($item['code']);
                $name = addslashes($item['name']);
                $parent = addslashes($item['parent']);
                $type = addslashes($item['type']);
                $normal = addslashes($item['normal']);
                $cat = addslashes($item['cat']);
                $header = (int)$item['header'];

                $ChartOfAccount->query("INSERT INTO `ardaccounts_chart_of_accounts` 
                    (`project_id`, `account_code`, `account_name`, `parent_code`, `account_type`, `normal_balance`, `category`, `is_header`, `is_system_default`, `is_active`, `created`, `modified`)
                    VALUES (
                        {$projectId},
                        '{$code}',
                        '{$name}',
                        '{$parent}',
                        '{$type}',
                        '{$normal}',
                        '{$cat}',
                        {$header},
                        1, 1, NOW(), NOW()
                    )
                    ON DUPLICATE KEY UPDATE 
                    `account_name`='{$name}',
                    `parent_code`='{$parent}',
                    `account_type`='{$type}',
                    `normal_balance`='{$normal}',
                    `category`='{$cat}',
                    `is_header`={$header},
                    `modified`=NOW()");
            }

            // 2. Fetch all COA rows for this project into code => id map
            $coaRows = (array)$ChartOfAccount->query("SELECT `id`, `account_code` FROM `ardaccounts_chart_of_accounts` WHERE `project_id` = {$projectId}");
            $coaIdMap = array();
            foreach($coaRows as $r)
            {
                $row = isset($r['ardaccounts_chart_of_accounts']) ? $r['ardaccounts_chart_of_accounts'] : (isset($r[0]) ? $r[0] : $r);
                if(!empty($row['account_code']))
                {
                    $coaIdMap[$row['account_code']] = (int)$row['id'];
                }
            }

            // 3. Fetch existing ledgers for this project
            $existingLedgersRaw = (array)$Ledger->query("SELECT `id`, `name`, `type`, `category`, `account_code`, `coa_id` FROM `ardaccounts_ledgers` WHERE `project_id` = {$projectId} AND `is_trashed` = 0");
            $existingLedgers = array();
            foreach($existingLedgersRaw as $el)
            {
                $existingLedgers[] = isset($el['ardaccounts_ledgers']) ? $el['ardaccounts_ledgers'] : (isset($el[0]) ? $el[0] : $el);
            }

            $matchedLedgerIds = array();
            $settingsFieldMap = array();

            foreach($catalog as $item)
            {
                if(!empty($item['header']))
                {
                    continue;
                }
                $code = $item['code'];
                $coaId = isset($coaIdMap[$code]) ? (int)$coaIdMap[$code] : 0;
                $matchedId = 0;

                foreach($existingLedgers as $el)
                {
                    $elId = (int)$el['id'];
                    if(in_array($elId, $matchedLedgerIds, true))
                    {
                        continue;
                    }
                    if(!empty($el['account_code']) && $el['account_code'] === $code)
                    {
                        $matchedId = $elId;
                        break;
                    }
                    if(strcasecmp(trim((string)$el['name']), trim((string)$item['name'])) === 0)
                    {
                        $matchedId = $elId;
                        break;
                    }
                    if(!empty($item['alias']))
                    {
                        foreach($item['alias'] as $alias)
                        {
                            if(stripos(trim((string)$el['name']), $alias) !== false)
                            {
                                $matchedId = $elId;
                                break 2;
                            }
                        }
                    }
                }

                $codeEsc = addslashes($code);
                $typeEsc = addslashes($item['type']);
                $normalEsc = addslashes($item['normal']);
                $nameEsc = addslashes($item['name']);
                $ledgerTypeEsc = addslashes($item['ledger_type']);
                $catEsc = addslashes($item['cat']);

                if($matchedId > 0)
                {
                    $matchedLedgerIds[] = $matchedId;
                    $Ledger->query("UPDATE `ardaccounts_ledgers` SET 
                        `account_code` = '{$codeEsc}',
                        `account_type` = '{$typeEsc}',
                        `normal_balance` = '{$normalEsc}',
                        `coa_id` = {$coaId},
                        `modified` = NOW()
                        WHERE `id` = {$matchedId}");

                    if(!empty($item['field']))
                    {
                        $settingsFieldMap[$item['field']] = $matchedId;
                    }
                }
                else
                {
                    $Ledger->query("INSERT INTO `ardaccounts_ledgers` 
                        (`project_id`, `name`, `type`, `category`, `account_code`, `account_type`, `normal_balance`, `coa_id`, `balance`, `is_trashed`, `created`, `modified`)
                        VALUES (
                            {$projectId},
                            '{$nameEsc}',
                            '{$ledgerTypeEsc}',
                            '{$catEsc}',
                            '{$codeEsc}',
                            '{$typeEsc}',
                            '{$normalEsc}',
                            {$coaId},
                            0.00, 0, NOW(), NOW()
                        )");
                    $resId = $Ledger->query("SELECT LAST_INSERT_ID() as new_id");
                    $newLedgerId = !empty($resId[0][0]['new_id']) ? (int)$resId[0][0]['new_id'] : (!empty($resId[0]['new_id']) ? (int)$resId[0]['new_id'] : 0);
                    if(!empty($item['field']) && $newLedgerId > 0)
                    {
                        $settingsFieldMap[$item['field']] = $newLedgerId;
                    }
                }
            }

            // Sync settings
            if(!empty($settingsFieldMap))
            {
                $Setting->query("INSERT IGNORE INTO `ardaccounts_settings` (`project_id`,`created`,`modified`) VALUES ({$projectId},NOW(),NOW())");
                $setParts = array();
                foreach($settingsFieldMap as $f => $val)
                {
                    $setParts['Ardsetting.'.$f] = (int)$val;
                }
                $setParts['Ardsetting.modified'] = "'".date('Y-m-d H:i:s')."'";
                $Setting->updateAll($setParts, array('Ardsetting.project_id' => $projectId));
            }

            return true;
        }
        catch(Throwable $e)
        {
            CakeLog::write('error', 'COA provisioning failed for agency '.$projectId.': '.$e->getMessage());
            return false;
        }
    }

    protected function _ardDefaultLedgerId($projectId, $settingsField, $fallbackName, $ledgerType = 'income')
    {
        $projectId = (int)$projectId;
        if($projectId <= 0)
        {
            return 0;
        }
        $settingsField = preg_replace('/[^a-z0-9_]/i', '', (string)$settingsField);
        $ledgerType = strtolower(trim((string)$ledgerType));
        $settings = $this->_ardFetchSettingsRowRaw($projectId);
        $ledgerId = (!empty($settings[$settingsField])) ? (int)$settings[$settingsField] : 0;
        $valid = $this->_ardValidLedgerId($projectId, $ledgerId, $ledgerType);
        if($valid > 0)
        {
            return $valid;
        }
        if($fallbackName === '')
        {
            return 0;
        }
        $createType = ($ledgerType === 'expense') ? 'expense' : $ledgerType;
        if($createType === 'source' || $createType === 'any')
        {
            $createType = 'bank';
        }
        return $this->_ardGetOrCreateLedger($projectId, $fallbackName, $createType, 'Auto Business Default');
    }

    protected function _ardDefaultSourceLedgerId($projectId, $settingsField = '')
    {
        $projectId = (int)$projectId;
        if($projectId <= 0)
        {
            return 0;
        }
        if(trim((string)$settingsField) !== '')
        {
            $settings = $this->_ardFetchSettingsRowRaw($projectId);
            $ledgerId = !empty($settings[$settingsField]) ? (int)$settings[$settingsField] : 0;
            $valid = $this->_ardValidLedgerId($projectId, $ledgerId, 'source');
            if($valid > 0)
            {
                return $valid;
            }
        }
        try
        {
            $Ledger = ClassRegistry::init('Admin.Ardledger');
            $row = (array)$Ledger->find('first', array(
                'fields' => array('Ardledger.id'),
                'conditions' => array(
                    'Ardledger.project_id' => $projectId,
                    'Ardledger.is_trashed' => 0,
                    'Ardledger.type !=' => 'expense',
                    'OR' => array(
                        array('Ardledger.name LIKE' => '%Cash%'),
                        array('Ardledger.name LIKE' => '%Bank%')
                    )
                ),
                'order' => array('Ardledger.name' => 'asc'),
                'recursive' => -1
            ));
            if(!empty($row['Ardledger']['id']))
            {
                return (int)$row['Ardledger']['id'];
            }
        }
        catch(Throwable $e) {}
        return $this->_ardGetOrCreateLedger($projectId, 'Cash In Hand', 'bank', 'Auto Receipt Account');
    }

    protected function _ardNormalizeVoucherStatus($status, $default = 'Posted')
    {
        $status = strtolower(trim((string)$status));
        $map = array(
            'draft' => 'Draft',
            'approved' => 'Approved',
            'posted' => 'Posted',
            'void' => 'Void',
            'voided' => 'Void',
            'reversed' => 'Reversed'
        );
        $default = isset($map[strtolower((string)$default)]) ? $map[strtolower((string)$default)] : 'Posted';
        return isset($map[$status]) ? $map[$status] : $default;
    }

    protected function _ardTransactionLockedMessage($transactionRowOrId, $projectId = 0)
    {
        if($this->_ardIsAccountingAdmin())
        {
            return '';
        }
        $tx = array();
        if(is_array($transactionRowOrId))
        {
            $tx = !empty($transactionRowOrId['Ardtransaction']) ? (array)$transactionRowOrId['Ardtransaction'] : (array)$transactionRowOrId;
        }
        else
        {
            $transactionId = (int)$transactionRowOrId;
            if($transactionId > 0)
            {
                try
                {
                    $Transaction = ClassRegistry::init('Admin.Ardtransaction');
                    $row = (array)$Transaction->find('first', array('conditions' => array('Ardtransaction.id' => $transactionId), 'recursive' => -1));
                    if(!empty($row['Ardtransaction']))
                    {
                        $tx = (array)$row['Ardtransaction'];
                    }
                }
                catch(Throwable $e) {}
            }
        }
        if(empty($tx))
        {
            return '';
        }
        $status = $this->_ardNormalizeVoucherStatus(isset($tx['status']) ? $tx['status'] : 'Posted', 'Posted');
        if(in_array($status, array('Approved', 'Posted', 'Void', 'Reversed'), true))
        {
            return __('This voucher is').' '.$status.'. '.__('Only admin can edit/delete approved or posted vouchers.');
        }
        $entryDate = trim((string)(isset($tx['entry_date']) ? $tx['entry_date'] : ''));
        $projectId = (int)$projectId > 0 ? (int)$projectId : (int)(isset($tx['project_id']) ? $tx['project_id'] : 0);
        if($projectId > 0 && $entryDate !== '' && $entryDate !== '0000-00-00')
        {
            $settings = $this->_ardFetchSettingsRowRaw($projectId);
            $lockDate = trim((string)(isset($settings['fiscal_lock_date']) ? $settings['fiscal_lock_date'] : ''));
            if($lockDate !== '' && $lockDate !== '0000-00-00' && $entryDate <= $lockDate)
            {
                return __('This voucher date is inside the fiscal lock period. Only admin can change it.');
            }
        }
        return '';
    }

    protected function _ardDecorateTransactionPayload($payload, $options = array())
    {
        $payload = is_array($payload) ? $payload : array();
        $options = is_array($options) ? $options : array();
        $isUpdate = !empty($payload['id']);
        $status = $this->_ardNormalizeVoucherStatus(isset($payload['status']) ? $payload['status'] : (isset($options['status']) ? $options['status'] : 'Posted'), 'Posted');
        $payload['status'] = $status;
        $payload['source_type'] = isset($options['source_type']) ? substr(trim((string)$options['source_type']), 0, 60) : (isset($payload['source_type']) ? substr(trim((string)$payload['source_type']), 0, 60) : '');
        $payload['source_id'] = isset($options['source_id']) ? (int)$options['source_id'] : (isset($payload['source_id']) ? (int)$payload['source_id'] : 0);
        $payload['auto_posted'] = !empty($options['auto_posted']) ? 1 : (isset($payload['auto_posted']) ? (int)$payload['auto_posted'] : 0);
        $payload['modified_by'] = isset($this->luserId) ? (int)$this->luserId : 0;
        if(!$isUpdate)
        {
            $payload['created_by'] = isset($this->luserId) ? (int)$this->luserId : 0;
        }
        if($status === 'Approved' && empty($payload['approved_by']))
        {
            $payload['approved_by'] = isset($this->luserId) ? (int)$this->luserId : 0;
            $payload['approved_at'] = date('Y-m-d H:i:s');
        }
        if($status === 'Posted' && empty($payload['posted_by']))
        {
            $payload['posted_by'] = isset($this->luserId) ? (int)$this->luserId : 0;
            $payload['posted_at'] = date('Y-m-d H:i:s');
        }
        if($status === 'Void' && empty($payload['voided_by']))
        {
            $payload['voided_by'] = isset($this->luserId) ? (int)$this->luserId : 0;
            $payload['voided_at'] = date('Y-m-d H:i:s');
        }
        return $payload;
    }

    protected function _ardSyncJournalForTransaction($transactionId, $sourceType = 'voucher', $sourceId = 0)
    {
        if(method_exists($this, '_syncJournalForTransaction'))
        {
            return $this->_syncJournalForTransaction($transactionId, $sourceType, $sourceId);
        }
        if(method_exists($this, '_syncArdJournalFromTransaction'))
        {
            return $this->_syncArdJournalFromTransaction($transactionId, $sourceType, $sourceId);
        }
        $transactionId = (int)$transactionId;
        if($transactionId <= 0)
        {
            return false;
        }
        try
        {
            $Transaction = ClassRegistry::init('Admin.Ardtransaction');
            $Ledger = ClassRegistry::init('Admin.Ardledger');
            $JournalEntry = ClassRegistry::init('Admin.Ardjournalentry');
            $txRow = $Transaction->find('first', array(
                'conditions' => array('Ardtransaction.id' => $transactionId),
                'recursive' => -1
            ));
            if(empty($txRow['Ardtransaction']))
            {
                return false;
            }
            $tx = $txRow['Ardtransaction'];
            $ledgerName = 'Ledger #' . (int)$tx['ledger_id'];
            if((int)$tx['ledger_id'] > 0)
            {
                $ledger = $Ledger->find('first', array(
                    'fields' => array('Ardledger.name'),
                    'conditions' => array('Ardledger.id' => (int)$tx['ledger_id']),
                    'recursive' => -1
                ));
                if(!empty($ledger['Ardledger']['name']))
                {
                    $ledgerName = trim((string)$ledger['Ardledger']['name']);
                }
            }
            $expenseLedgerName = '';
            $expenseLedgerId = isset($tx['expense_ledger_id']) ? (int)$tx['expense_ledger_id'] : 0;
            if($expenseLedgerId > 0)
            {
                $expenseLedger = $Ledger->find('first', array(
                    'fields' => array('Ardledger.name'),
                    'conditions' => array('Ardledger.id' => $expenseLedgerId),
                    'recursive' => -1
                ));
                if(!empty($expenseLedger['Ardledger']['name']))
                {
                    $expenseLedgerName = trim((string)$expenseLedger['Ardledger']['name']);
                }
            }
            $incomeLedgerName = '';
            $incomeLedgerId = isset($tx['income_ledger_id']) ? (int)$tx['income_ledger_id'] : 0;
            if($incomeLedgerId > 0)
            {
                $incomeLedger = $Ledger->find('first', array(
                    'fields' => array('Ardledger.name'),
                    'conditions' => array('Ardledger.id' => $incomeLedgerId),
                    'recursive' => -1
                ));
                if(!empty($incomeLedger['Ardledger']['name']))
                {
                    $incomeLedgerName = trim((string)$incomeLedger['Ardledger']['name']);
                }
            }
            $txType = strtolower(trim((string)$tx['type']));
            if($txType === 'income')
            {
                $debitAccount = $ledgerName;
                $creditAccount = ($incomeLedgerName !== '') ? $incomeLedgerName : 'Income Control';
                if($debitAccount === $creditAccount)
                {
                    $debitAccount = 'General Receipt Account';
                }
            }
            else
            {
                $debitAccount = ($expenseLedgerName !== '') ? $expenseLedgerName : 'Expense Control';
                $creditAccount = $ledgerName;
                if($debitAccount === $creditAccount)
                {
                    $creditAccount = 'Cash-inhand';
                }
            }
            $payload = array(
                'project_id' => (int)$tx['project_id'],
                'transaction_id' => $transactionId,
                'source_type' => substr(trim((string)$sourceType), 0, 40),
                'source_id' => (int)$sourceId,
                'ref_no' => trim((string)$tx['ref_no']),
                'entry_date' => trim((string)$tx['entry_date']),
                'debit_account' => substr($debitAccount, 0, 160),
                'credit_account' => substr($creditAccount, 0, 160),
                'amount' => round((float)$tx['amount'], 2),
                'description' => trim((string)$tx['description'])
            );
            $exists = $JournalEntry->find('first', array(
                'fields' => array('Ardjournalentry.id'),
                'conditions' => array('Ardjournalentry.transaction_id' => $transactionId),
                'recursive' => -1
            ));
            if(!empty($exists['Ardjournalentry']['id']))
            {
                $payload['id'] = (int)$exists['Ardjournalentry']['id'];
            }
            $JournalEntry->create();
            return (bool)$JournalEntry->save(array('Ardjournalentry' => $payload), false);
        }
        catch(Throwable $e)
        {
            return false;
        }
    }

    public function _generateCode128BarcodeSvg($text, $height = 36, $barWidth = 2)
    {
        $text = trim((string)$text);
        if($text === '')
        {
            $text = 'VOUCHER';
        }

        $patterns = array(
            '212222', '222122', '222221', '121223', '121322', '131222', '122213', '122312', '132212', '221213',
            '221312', '231212', '112232', '122132', '122231', '113222', '123122', '123221', '223211', '221132',
            '221231', '213212', '223112', '312131', '311222', '321122', '321221', '312212', '322112', '322211',
            '212123', '212321', '232121', '111323', '131123', '131321', '112313', '132113', '132311', '211313',
            '231113', '231311', '112133', '112331', '132131', '113123', '113321', '133121', '313121', '211331',
            '231131', '213113', '213311', '213131', '311123', '311321', '331121', '312113', '312311', '332111',
            '314111', '221411', '431111', '111224', '111422', '121124', '121421', '141122', '141221', '112214',
            '112412', '122114', '122411', '142112', '142211', '241211', '221114', '413111', '241112', '134111',
            '111242', '121142', '121241', '114212', '124112', '124211', '411212', '421112', '421211', '212141',
            '214121', '412121', '111143', '111341', '131141', '114113', '114311', '411113', '411311', '113141',
            '114131', '311141', '411131', '211412', '211214', '211232', '2331112'
        );

        $startB = 104;
        $stop = 106;

        $encodedIndices = array($startB);
        $checkSum = $startB;

        $len = strlen($text);
        for($i = 0; $i < $len; $i++)
        {
            $ascii = ord($text[$i]);
            if($ascii >= 32 && $ascii <= 126)
            {
                $code = $ascii - 32;
            }
            else
            {
                $code = 0;
            }
            $encodedIndices[] = $code;
            $checkSum += ($code * ($i + 1));
        }

        $checkDigit = $checkSum % 103;
        $encodedIndices[] = $checkDigit;
        $encodedIndices[] = $stop;

        $rects = array();
        $currentX = 12; // 12px quiet zone

        foreach($encodedIndices as $idx)
        {
            if(!isset($patterns[$idx]))
            {
                continue;
            }
            $pattern = $patterns[$idx];
            $pLen = strlen($pattern);
            for($p = 0; $p < $pLen; $p++)
            {
                $w = (int)$pattern[$p] * $barWidth;
                if($p % 2 === 0)
                {
                    $rects[] = '<rect x="' . $currentX . '" y="0" width="' . $w . '" height="' . $height . '" fill="#000000"/>';
                }
                $currentX += $w;
            }
        }
        $totalWidth = $currentX + 12;

        return '<svg class="h-9 w-auto max-w-[360px]" viewBox="0 0 ' . $totalWidth . ' ' . $height . '" preserveAspectRatio="xMidYMid meet" xmlns="http://www.w3.org/2000/svg" style="display:block;margin:0 auto;">' .
            implode('', $rects) .
            '</svg>';
    }

    public function _ardRecalculateLedgerBalanceCompat($ledgerId)
    {
        $ledgerId = (int)$ledgerId;
        if($ledgerId <= 0)
        {
            return 0.0;
        }
        try
        {
            $Transaction = ClassRegistry::init('Admin.Ardtransaction');
            $Ledger = ClassRegistry::init('Admin.Ardledger');
            $ledger = (array)$Ledger->find('first', array(
                'conditions' => array('Ardledger.id' => $ledgerId),
                'fields' => array('Ardledger.id', 'Ardledger.type', 'Ardledger.account_type', 'Ardledger.normal_balance'),
                'recursive' => -1
            ));
            if(empty($ledger['Ardledger']['id']))
            {
                return 0.0;
            }
            $accType = !empty($ledger['Ardledger']['account_type']) ? strtolower(trim((string)$ledger['Ardledger']['account_type'])) : (!empty($ledger['Ardledger']['type']) ? strtolower(trim((string)$ledger['Ardledger']['type'])) : 'asset');
            $legacyType = !empty($ledger['Ardledger']['type']) ? strtolower(trim((string)$ledger['Ardledger']['type'])) : '';
            $isExpenseLedger = ($legacyType === 'expense' || $accType === 'expense');

            $rows = (array)$Transaction->find('all', array(
                'fields' => array('Ardtransaction.type', 'Ardtransaction.status', 'Ardtransaction.amount', 'Ardtransaction.ledger_id', 'Ardtransaction.expense_ledger_id', 'Ardtransaction.income_ledger_id'),
                'conditions' => array(
                    'NOT' => array('Ardtransaction.status' => array('Void', 'Reversed')),
                    'OR' => array(
                        'Ardtransaction.ledger_id' => $ledgerId,
                        array('Ardtransaction.type' => 'expense', 'Ardtransaction.expense_ledger_id' => $ledgerId),
                        array('Ardtransaction.type' => 'income', 'Ardtransaction.income_ledger_id' => $ledgerId),
                        array('Ardtransaction.type' => 'transfer', 'Ardtransaction.expense_ledger_id' => $ledgerId)
                    )
                ),
                'recursive' => -1
            ));

            $balance = 0.0;
            foreach($rows as $row)
            {
                $tx = !empty($row['Ardtransaction']) ? (array)$row['Ardtransaction'] : array();
                $amount = (float)(isset($tx['amount']) ? $tx['amount'] : 0);
                $type = strtolower(trim((string)(isset($tx['type']) ? $tx['type'] : '')));
                $sourceLedgerId = (int)(isset($tx['ledger_id']) ? $tx['ledger_id'] : 0);
                $expenseLedgerId = (int)(isset($tx['expense_ledger_id']) ? $tx['expense_ledger_id'] : 0);
                $incomeLedgerId = (int)(isset($tx['income_ledger_id']) ? $tx['income_ledger_id'] : 0);

                $isSource = ($sourceLedgerId === $ledgerId);
                $isExpense = ($type === 'expense' && $expenseLedgerId === $ledgerId && !$isSource);
                $isIncome = ($type === 'income' && $incomeLedgerId === $ledgerId && !$isSource);
                $isTransferIn = ($type === 'transfer' && $expenseLedgerId === $ledgerId && !$isSource);

                if($isSource)
                {
                    if($type === 'income' || $type === 'transfer_in')
                    {
                        $balance += $amount;
                    }
                    else
                    {
                        $balance -= $amount;
                    }
                }
                elseif($isExpense)
                {
                    if($isExpenseLedger)
                    {
                        // Incurring expenses is positive normal debit balance
                        $balance += $amount;
                    }
                    else
                    {
                        // Bank or liability paying out reduces balance
                        $balance -= $amount;
                    }
                }
                elseif($isIncome)
                {
                    $balance += $amount;
                }
                elseif($isTransferIn)
                {
                    $balance += $amount;
                }
            }

            $Ledger->id = $ledgerId;
            $Ledger->saveField('balance', $balance, false);
            return $balance;
        }
        catch(Throwable $e)
        {
            return 0.0;
        }
    }

    protected function _ardPostAutoVoucher($projectId, $sourceLedgerId, $type, $amount, $refNo, $entryDate, $description, $options = array())
    {
        $options = is_array($options) ? $options : array();
        if(empty($options['skip_schema_bootstrap']))
        {
            $this->_ensureBusinessAccountingSchema();
        }
        $projectId = (int)$projectId;
        $sourceLedgerId = (int)$sourceLedgerId;
        $amount = round((float)$amount, 2);
        if($projectId <= 0 || $sourceLedgerId <= 0 || $amount <= 0)
        {
            return 0;
        }
        $type = strtolower(trim((string)$type)) === 'expense' ? 'expense' : 'income';
        $entryDate = trim((string)$entryDate);
        $parsedDate = strtotime($entryDate);
        $entryDate = ($parsedDate !== false) ? date('Y-m-d', $parsedDate) : date('Y-m-d');
        $refNo = trim((string)$refNo);
        if($refNo === '')
        {
            $refNo = strtoupper($type).'-AUTO-'.time();
        }

        $incomeLedgerId = 0;
        $expenseLedgerId = 0;
        if($type === 'income')
        {
            $incomeLedgerId = isset($options['income_ledger_id']) ? (int)$options['income_ledger_id'] : 0;
            if($incomeLedgerId <= 0 && !empty($options['income_settings_field']))
            {
                $incomeLedgerId = $this->_ardDefaultLedgerId($projectId, $options['income_settings_field'], isset($options['income_fallback']) ? $options['income_fallback'] : 'General Income', 'income');
            }
            if($incomeLedgerId <= 0)
            {
                $incomeLedgerId = $this->_ardDefaultLedgerId($projectId, 'installment_income_ledger_id', 'General Income', 'income');
            }
        }
        else
        {
            $expenseLedgerId = isset($options['expense_ledger_id']) ? (int)$options['expense_ledger_id'] : 0;
            if($expenseLedgerId <= 0 && !empty($options['expense_settings_field']))
            {
                $expenseLedgerId = $this->_ardDefaultLedgerId($projectId, $options['expense_settings_field'], isset($options['expense_fallback']) ? $options['expense_fallback'] : 'General Expense Account', 'expense');
            }
            if($expenseLedgerId <= 0)
            {
                $expenseLedgerId = $this->_ardDefaultLedgerId($projectId, 'discount_waiver_ledger_id', 'General Expense Account', 'expense');
            }
        }

        try
        {
            $Transaction = ClassRegistry::init('Admin.Ardtransaction');
            $existingId = !empty($options['existing_id']) ? (int)$options['existing_id'] : 0;
            $oldTx = array();
            if($existingId > 0)
            {
                $oldRow = (array)$Transaction->find('first', array('conditions' => array('Ardtransaction.id' => $existingId), 'recursive' => -1));
                if(!empty($oldRow['Ardtransaction']))
                {
                    $oldTx = (array)$oldRow['Ardtransaction'];
                }
            }
            if(empty($oldTx) && !empty($options['source_type']) && !empty($options['source_id']))
            {
                $oldRow = (array)$Transaction->find('first', array(
                    'conditions' => array(
                        'Ardtransaction.project_id' => $projectId,
                        'Ardtransaction.source_type' => substr(trim((string)$options['source_type']), 0, 60),
                        'Ardtransaction.source_id' => (int)$options['source_id']
                    ),
                    'order' => array('Ardtransaction.id' => 'desc'),
                    'recursive' => -1
                ));
                if(!empty($oldRow['Ardtransaction']))
                {
                    $oldTx = (array)$oldRow['Ardtransaction'];
                }
            }
            if(!empty($oldTx) && empty($options['allow_source_update']))
            {
                $lockMessage = $this->_ardTransactionLockedMessage($oldTx, $projectId);
                if($lockMessage !== '')
                {
                    return (int)$oldTx['id'];
                }
            }
            $payload = array(
                'project_id' => $projectId,
                'ledger_id' => $sourceLedgerId,
                'expense_ledger_id' => $expenseLedgerId,
                'income_ledger_id' => $incomeLedgerId,
                'type' => $type,
                'amount' => $amount,
                'description' => trim((string)$description),
                'ref_no' => $refNo,
                'entry_date' => $entryDate,
                'construction_site_id' => isset($options['construction_site_id']) ? (int)$options['construction_site_id'] : 0
            );
            if(!empty($oldTx['id']))
            {
                $payload['id'] = (int)$oldTx['id'];
            }
            $payload = $this->_ardDecorateTransactionPayload($payload, array(
                'status' => isset($options['status']) ? $options['status'] : 'Posted',
                'source_type' => isset($options['source_type']) ? $options['source_type'] : 'auto',
                'source_id' => isset($options['source_id']) ? $options['source_id'] : 0,
                'auto_posted' => 1
            ));
            $Transaction->create();
            if(!$Transaction->save(array('Ardtransaction' => $payload), false))
            {
                return 0;
            }
            $txId = (int)$Transaction->id;
            $ledgerIds = array($sourceLedgerId, $incomeLedgerId, $expenseLedgerId);
            if(!empty($oldTx['ledger_id'])) { $ledgerIds[] = (int)$oldTx['ledger_id']; }
            if(!empty($oldTx['income_ledger_id'])) { $ledgerIds[] = (int)$oldTx['income_ledger_id']; }
            if(!empty($oldTx['expense_ledger_id'])) { $ledgerIds[] = (int)$oldTx['expense_ledger_id']; }
            foreach(array_unique(array_filter($ledgerIds)) as $ledgerId)
            {
                $this->_ardRecalculateLedgerBalanceCompat((int)$ledgerId);
            }
            $this->_ardSyncJournalForTransaction($txId, isset($options['source_type']) ? $options['source_type'] : 'auto', isset($options['source_id']) ? (int)$options['source_id'] : $txId);
            return $txId;
        }
        catch(Throwable $e)
        {
            return 0;
        }
    }

    protected function _ardParseComponentTransactionIds($value)
    {
        $ids = array();
        if(is_array($value))
        {
            $rawItems = $value;
        }
        else
        {
            $value = trim((string)$value);
            if($value === '')
            {
                return array();
            }
            $decoded = json_decode($value, true);
            if(is_array($decoded))
            {
                $rawItems = $decoded;
            }
            else
            {
                $rawItems = preg_split('/[^0-9]+/', $value);
            }
        }
        foreach((array)$rawItems as $key => $item)
        {
            if(is_array($item))
            {
                foreach($this->_ardParseComponentTransactionIds($item) as $id)
                {
                    $ids[$id] = $id;
                }
                continue;
            }
            $id = (int)$item;
            if($id <= 0 && !is_numeric($key))
            {
                $id = (int)$key;
            }
            if($id > 0)
            {
                $ids[$id] = $id;
            }
        }
        return array_values($ids);
    }

    protected function _ardVoidAutoVoucherBySource($projectId, $sourceType, $sourceId, $lockNote = '', $allowSourceVoid = false)
    {
        $projectId = (int)$projectId;
        $sourceType = substr(trim((string)$sourceType), 0, 60);
        $sourceId = (int)$sourceId;
        if($projectId <= 0 || $sourceType === '' || $sourceId <= 0)
        {
            return true;
        }
        try
        {
            $Transaction = ClassRegistry::init('Admin.Ardtransaction');
            $Journal = ClassRegistry::init('Admin.Ardjournalentry');
            $rows = (array)$Transaction->find('all', array(
                'conditions' => array(
                    'Ardtransaction.project_id' => $projectId,
                    'Ardtransaction.source_type' => $sourceType,
                    'Ardtransaction.source_id' => $sourceId,
                    'NOT' => array('Ardtransaction.status' => array('Void', 'Reversed'))
                ),
                'recursive' => -1
            ));
            foreach($rows as $row)
            {
                if(empty($row['Ardtransaction']['id']))
                {
                    continue;
                }
                $tx = (array)$row['Ardtransaction'];
                $lockMessage = $this->_ardTransactionLockedMessage($tx, $projectId);
                if(!$allowSourceVoid && $lockMessage !== '')
                {
                    return false;
                }
                $txId = (int)$tx['id'];
                $ledgerIds = array(
                    (int)(isset($tx['ledger_id']) ? $tx['ledger_id'] : 0),
                    (int)(isset($tx['income_ledger_id']) ? $tx['income_ledger_id'] : 0),
                    (int)(isset($tx['expense_ledger_id']) ? $tx['expense_ledger_id'] : 0)
                );
                $Transaction->id = $txId;
                $Transaction->save(array('Ardtransaction' => array(
                    'id' => $txId,
                    'status' => 'Void',
                    'voided_by' => isset($this->luserId) ? (int)$this->luserId : 0,
                    'voided_at' => date('Y-m-d H:i:s'),
                    'lock_note' => substr(trim((string)$lockNote), 0, 255),
                    'modified_by' => isset($this->luserId) ? (int)$this->luserId : 0
                )), false);
                try { $Journal->deleteAll(array('Ardjournalentry.transaction_id' => $txId), false); } catch(Exception $e) {}
                foreach(array_unique(array_filter($ledgerIds)) as $ledgerId)
                {
                    $this->_ardRecalculateLedgerBalanceCompat((int)$ledgerId);
                }
            }
        }
        catch(Throwable $e)
        {
            return false;
        }
        return true;
    }

    protected function _ardPostAgentCommissionAccrual($projectId, $dealId, $agentId, $amount, $entryDate = null)
    {
        $projectId = (int)$projectId;
        $dealId = (int)$dealId;
        $agentId = (int)$agentId;
        $amount = round((float)$amount, 2);
        if($projectId <= 0 || $dealId <= 0 || $agentId <= 0 || $amount <= 0)
        {
            return 0;
        }
        $payableLedgerId = $this->_ardGetOrCreateLedger($projectId, 'Agent Commission Payable', 'liability', 'Auto Business Payable');
        $expenseLedgerId = $this->_ardDefaultLedgerId($projectId, 'agent_commission_expense_ledger_id', 'Agent Commission Expense', 'expense');
        if($payableLedgerId <= 0 || $expenseLedgerId <= 0)
        {
            return 0;
        }
        $agentLabel = 'Agent #'.$agentId;
        try
        {
            $User = ClassRegistry::init('User');
            $agentName = trim((string)$User->field('name', array('User.id' => $agentId)));
            if($agentName !== '')
            {
                $agentLabel = $agentName;
            }
        }
        catch(Throwable $e) {}
        $entryDate = trim((string)$entryDate);
        if($entryDate === '')
        {
            $entryDate = date('Y-m-d');
        }
        return $this->_ardPostAutoVoucher(
            $projectId,
            $payableLedgerId,
            'expense',
            $amount,
            'ACOM-'.$dealId,
            $entryDate,
            'Agent commission accrued for '.$agentLabel.' | Deal #'.$dealId,
            array(
                'expense_ledger_id' => $expenseLedgerId,
                'source_type' => 'agent_commission_accrual',
                'source_id' => $dealId,
                'status' => 'Posted'
            )
        );
    }
    public function userPermission()
    {
        $UserArr=$this->Session->read('User');
        if(empty($UserArr['User']['id']))
        {
            return array('PageRight' => $this->_defaultFullPermission());
        }
        $isSuperAdmin = ($this->_isSuperAdminSession((array)$UserArr) || $this->_isSuperAdminSession((array)$this->adminValue) || (isset($this->adminValue['User']['ugroup_id']) && (int)$this->adminValue['User']['ugroup_id'] === 1) || (isset($UserArr['User']['ugroup_id']) && (int)$UserArr['User']['ugroup_id'] === 1));
        if($isSuperAdmin)
        {
            return array('PageRight' => $this->_defaultFullPermission());
        }
        $currController = strtolower((string)$this->params['controller']);
        $currAction = strtolower((string)$this->action);

        // Agency Admin has guaranteed full operational rights across all agency modules (isolated by active agency)
        $ugroupId = !empty($UserArr['User']['ugroup_id']) ? (int)$UserArr['User']['ugroup_id'] : 0;
        $isAgencyAdmin = ($this->_isAgencyAdminSession((array)$UserArr) || $this->_isAgencyAdminSession((array)$this->adminValue) || $ugroupId === 4 || $this->userType === 'AGY' || $this->_isAgencySystemManagerRoleContext((array)$this->adminValue));
        if($isAgencyAdmin && !$this->_isSuperAdminOnlyController($currController))
        {
            return array('PageRight' => $this->_defaultFullPermission());
        }
        if($isAgencyAdmin && $this->_isSuperAdminOnlyController($currController))
        {
            $this->Session->setFlash(__('Access restricted to Super Administrator.'),'flash',array('alert'=>'danger'));
            $this->redirect(array('controller'=>'Dashboards','action' => 'index'));
            exit();
        }
        $requiredField = $this->_requiredPermissionFieldByAction($currAction);
        $permissionRow = $this->_findPermissionPageRow((int)$UserArr['User']['ugroup_id'], $currController, $currAction);
        if(empty($permissionRow['PageRight']))
        {
            $this->Session->setFlash(__('No Permission!'),'flash',array('alert'=>'danger'));
            $this->redirect(array('controller'=>'Dashboards','action' => 'index'));
            exit();
        }
        $normalizedRight = $this->_normalizePageRightRow($permissionRow['PageRight']);
        if(empty($normalizedRight['view_right']) || empty($normalizedRight[$requiredField]))
        {
            $this->Session->setFlash(__('No Permission!'),'flash',array('alert'=>'danger'));
            $this->redirect(array('controller'=>'Dashboards','action' => 'index'));
            exit();
        }
        return array(
            'Page' => isset($permissionRow['Page']) ? $permissionRow['Page'] : array(),
            'PageRight' => $normalizedRight
        );
    }
    public function userMenu($id)
    {
        $id = (int)$id;
        if(isset($this->_userMenuCache[$id]))
        {
            return $this->_userMenuCache[$id];
        }
        $UserArr=$this->Session->read('User');
        $this->loadModel('Page');
        if($this->_isSuperAdminSession((array)$UserArr))
        {
            $menuArr=$this->Page->find('all',array('conditions'=>array('parent_id'=>$id,'published'=>'Yes'),'order'=>array('ordering'=>'asc')));
        }
        else
        {
            $ugroupId = !empty($UserArr['User']['ugroup_id']) ? (int)$UserArr['User']['ugroup_id'] : 0;
            if($id === 0)
            {
                $menuArr = $this->Page->find('all', array(
                    'conditions' => array(
                        'Page.parent_id' => 0,
                        'Page.published' => 'Yes',
                        'OR' => array(
                            'EXISTS (SELECT 1 FROM page_rights pr WHERE pr.page_id = Page.id AND pr.ugroup_id = ' . $ugroupId . ' AND pr.view_right = 1)',
                            'EXISTS (SELECT 1 FROM pages cp JOIN page_rights cpr ON cp.id = cpr.page_id WHERE cp.parent_id = Page.id AND cp.published = "Yes" AND cpr.ugroup_id = ' . $ugroupId . ' AND cpr.view_right = 1)'
                        )
                    ),
                    'order' => array('Page.ordering' => 'asc')
                ));
            }
            else
            {
                $menuArr=$this->Page->find('all',array('joins'=>array(array
                                                                       ('table'=>'page_rights','alias'=>'PageRight','type'=>'Inner',
                                                                         'conditions'=>array('Page.id=PageRight.page_id'))),
                                                        'conditions'=>array('PageRight.ugroup_id'=>$ugroupId,'PageRight.view_right'=>1,'parent_id'=>$id,'published'=>'Yes'),
                                                        'order'=>array('Page.ordering'=>'asc')));
            }
        }
        if(!empty($menuArr))
        {
            $dedupedMenuArr = array();
            $seenMenuKeys = array();
            foreach((array)$menuArr as $menuRow)
            {
                $pageRow = !empty($menuRow['Page']) ? (array)$menuRow['Page'] : array();
                $menuKey = implode('|', array(
                    (int)(isset($pageRow['parent_id']) ? $pageRow['parent_id'] : 0),
                    strtolower(trim((string)(isset($pageRow['controller_name']) ? $pageRow['controller_name'] : ''))),
                    strtolower(trim((string)(isset($pageRow['action_name']) ? $pageRow['action_name'] : ''))),
                    strtolower(trim((string)(isset($pageRow['page_name']) ? $pageRow['page_name'] : '')))
                ));
                if($menuKey === '0|||')
                {
                    $menuKey = 'page-id|'.(int)(isset($pageRow['id']) ? $pageRow['id'] : 0);
                }
                if(isset($seenMenuKeys[$menuKey]))
                {
                    continue;
                }
                $seenMenuKeys[$menuKey] = true;
                $dedupedMenuArr[] = $menuRow;
            }
            $menuArr = $dedupedMenuArr;
        }
        $this->_userMenuCache[$id] = $menuArr;
        return $menuArr;
    }

    public function _recordUploadInMediaGallery($sourceType, $sourceId, $fileName, $dir = '', $options = array())
    {
        $fileName = trim((string)$fileName);
        if($fileName === '')
        {
            return false;
        }
        try
        {
            $MediaFile = ClassRegistry::init('MediaFile');
            $MediaFolder = ClassRegistry::init('MediaFolder');
            if(!$MediaFile || !$MediaFolder)
            {
                return false;
            }
            
            $folderMap = array(
                'property_photo' => 'property_photos',
                'property_document' => 'property_documents',
                'property_floorplan' => 'property_floorplans',
                'payment_plan_pdf' => 'property_documents',
                'deal_document' => 'deal_documents',
                'client_photo' => 'client_media',
                'client_id_front' => 'client_media',
                'client_id_back' => 'client_media',
                'client_side_photo' => 'client_media',
                'user_photo' => 'user_media',
                'slide' => 'website_media',
                'site_logo' => 'website_media',
                'news_post' => 'website_media'
            );
            
            $folderKey = isset($folderMap[$sourceType]) ? $folderMap[$sourceType] : 'website_media';
            $folderRow = (array)$MediaFolder->find('first', array(
                'fields' => array('MediaFolder.id'),
                'conditions' => array('MediaFolder.system_key' => $folderKey),
                'recursive' => -1
            ));
            $folderId = !empty($folderRow['MediaFolder']['id']) ? (int)$folderRow['MediaFolder']['id'] : 0;
            
            $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $imageExts = array('jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg', 'ico', 'tiff', 'avif');
            $videoExts = array('mp4', 'webm', 'mov', 'mkv', 'avi', 'wmv', 'm4v', 'flv', '3gp', 'ogv', 'ts');
            $docExts = array('pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'rtf', 'csv', 'ppt', 'pptx');
            $fileType = in_array($ext, $imageExts, true) ? 'image' : (in_array($ext, $videoExts, true) ? 'video' : (in_array($ext, $docExts, true) ? 'document' : 'other'));
            
            $filePath = ($dir !== '' ? rtrim($dir, '/\\') . '/' : '') . $fileName;
            $fullDiskPath = WWW_ROOT . ($dir !== '' ? ('img' . DS . str_replace('/', DS, $dir) . DS) : ('img' . DS)) . $fileName;
            if(!file_exists($fullDiskPath) && strpos($dir, 'files') !== false)
            {
                $fullDiskPath = WWW_ROOT . str_replace('/', DS, $dir) . DS . $fileName;
            }
            $fileSize = file_exists($fullDiskPath) ? (int)filesize($fullDiskPath) : 0;
            
            $mimeMap = array(
                'png' => 'image/png', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg',
                'gif' => 'image/gif', 'webp' => 'image/webp', 'svg' => 'image/svg+xml',
                'pdf' => 'application/pdf', 'mp4' => 'video/mp4', 'webm' => 'video/webm',
                'mov' => 'video/quicktime', 'mkv' => 'video/x-matroska', 'avi' => 'video/x-msvideo',
                'doc' => 'application/msword', 'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'xls' => 'application/vnd.ms-excel', 'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            );
            $mimeType = isset($mimeMap[$ext]) ? $mimeMap[$ext] : 'application/octet-stream';
            
            $title = !empty($options['title']) ? trim((string)$options['title']) : ucwords(str_replace(array('_', '-'), ' ', $sourceType)) . ' - ' . $fileName;
            $projectId = !empty($options['project_id']) ? (int)$options['project_id'] : 0;
            $visibility = !empty($options['visibility']) ? $options['visibility'] : 'both';
            
            $existing = (array)$MediaFile->find('first', array(
                'fields' => array('MediaFile.id'),
                'conditions' => array(
                    'MediaFile.source_type' => $sourceType,
                    'MediaFile.source_id' => (int)$sourceId,
                    'MediaFile.file_name' => $fileName
                ),
                'recursive' => -1
            ));
            
            $saveRecord = array(
                'folder_id' => $folderId,
                'project_id' => $projectId,
                'source_type' => $sourceType,
                'source_id' => (int)$sourceId,
                'title' => $title,
                'file_name' => $fileName,
                'original_name' => !empty($options['original_name']) ? $options['original_name'] : $fileName,
                'dir' => $dir,
                'file_path' => $filePath,
                'url_path' => $filePath,
                'mime_type' => $mimeType,
                'file_type' => $fileType,
                'extension' => $ext,
                'file_size' => $fileSize,
                'visibility' => $visibility,
                'status' => 'Active',
                'created_by' => !empty($this->luserId) ? (int)$this->luserId : 0,
                'modified_by' => !empty($this->luserId) ? (int)$this->luserId : 0,
            );
            
            if(!empty($existing['MediaFile']['id']))
            {
                $saveRecord['id'] = (int)$existing['MediaFile']['id'];
                $saveRecord['modified'] = date('Y-m-d H:i:s');
            }
            else
            {
                $saveRecord['created'] = date('Y-m-d H:i:s');
                $saveRecord['modified'] = date('Y-m-d H:i:s');
                $MediaFile->create();
            }
            
            return (bool)$MediaFile->save(array('MediaFile' => $saveRecord), array('validate' => false));
        }
        catch(Throwable $e)
        {
            return false;
        }
    }

    public function admin_clearcache()
    {
        return $this->clearcache();
    }

    public function clearcache()
    {
        $cleared = 0;
        try {
            @Cache::clear(false, '_cake_core_');
            @Cache::clear(false, '_cake_model_');
        } catch (Exception $e) {}

        $cacheDirs = array(
            TMP . 'cache' . DS . 'models',
            TMP . 'cache' . DS . 'persistent',
            TMP . 'cache' . DS . 'views'
        );

        foreach ($cacheDirs as $dir) {
            if (is_dir($dir)) {
                $files = @glob($dir . DS . '*');
                if (is_array($files)) {
                    foreach ($files as $f) {
                        if (is_file($f) && basename($f) !== 'empty') {
                            if (@unlink($f)) {
                                $cleared++;
                            }
                        }
                    }
                }
            }
        }

        if ($this->request->is('ajax')) {
            $this->autoRender = false;
            $this->response->type('json');
            $this->response->body(json_encode(array('status' => 'success', 'message' => sprintf(__('Cache cleared successfully! (%d files purged)'), $cleared))));
            return $this->response;
        }

        $this->Session->setFlash(sprintf(__('System cache cleared successfully! (%d files purged).'), $cleared), 'flash', array('alert' => 'success'));
        return $this->redirect($this->referer(array('action' => 'index'), true));
    }

    protected function _ensureDealPaymentTablesAutoIncrement()
    {
        static $hasRun = false;
        if($hasRun)
        {
            return true;
        }
        $hasRun = true;
        try
        {
            $this->loadModel('Admin.Deal');
            $tables = array('deals', 'deals_payments', 'plans_payments', 'deal_transfers', 'deal_charges');
            foreach($tables as $tbl)
            {
                try
                {
                    $cols = $this->Deal->query("SHOW FULL COLUMNS FROM `{$tbl}` LIKE 'id'");
                    if(!empty($cols[0]['COLUMNS']))
                    {
                        $col = $cols[0]['COLUMNS'];
                        $extra = strtolower(isset($col['Extra']) ? $col['Extra'] : '');
                        $key = strtoupper(isset($col['Key']) ? $col['Key'] : '');
                        $type = isset($col['Type']) ? $col['Type'] : 'int(11)';
                        if(strpos($extra, 'auto_increment') === false)
                        {
                            if($key === 'PRI')
                            {
                                $this->Deal->query("ALTER TABLE `{$tbl}` MODIFY `id` {$type} NOT NULL AUTO_INCREMENT");
                            }
                            else
                            {
                                $this->Deal->query("ALTER TABLE `{$tbl}` MODIFY `id` {$type} NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (`id`)");
                            }
                        }
                    }
                    $maxRow = $this->Deal->query("SELECT MAX(`id`) AS max_id FROM `{$tbl}`");
                    $maxId = 0;
                    if(!empty($maxRow[0][0]['max_id'])) { $maxId = (int)$maxRow[0][0]['max_id']; }
                    elseif(!empty($maxRow[0][$tbl]['max_id'])) { $maxId = (int)$maxRow[0][$tbl]['max_id']; }
                    if($maxId > 0)
                    {
                        $st = $this->Deal->query("SHOW TABLE STATUS LIKE '{$tbl}'");
                        $curAi = 0;
                        if(!empty($st[0]['TABLES']['Auto_increment'])) { $curAi = (int)$st[0]['TABLES']['Auto_increment']; }
                        elseif(!empty($st[0][0]['Auto_increment'])) { $curAi = (int)$st[0][0]['Auto_increment']; }
                        if($curAi <= $maxId)
                        {
                            $this->Deal->query("ALTER TABLE `{$tbl}` AUTO_INCREMENT = " . ($maxId + 1));
                        }
                    }
                }
                catch(Exception $tblEx) {}
            }
        }
        catch(Throwable $e) {}
        return true;
    }

    public function _autoGenerateDealScheduleIfMissing($dealId)
    {
        $dealId = (int)$dealId;
        if($dealId <= 0)
        {
            return false;
        }
        try
        {
            $this->loadModel('Admin.Deal');
            $this->loadModel('Admin.PlansPayment');
            $this->loadModel('Admin.Property');

            $deal = $this->Deal->find('first', array(
                'conditions' => array('Deal.id' => $dealId),
                'recursive' => -1
            ));
            if(empty($deal['Deal']['id']) || empty($deal['Deal']['plan_id']))
            {
                return false;
            }
            $planId = (int)$deal['Deal']['plan_id'];
            if($planId <= 0)
            {
                return false;
            }

            $existingCount = (int)$this->PlansPayment->find('count', array(
                'conditions' => array(
                    'PlansPayment.deal_id' => $dealId,
                    'OR' => array('PlansPayment.is_deal_charge' => 0, 'PlansPayment.is_deal_charge IS NULL')
                ),
                'recursive' => -1
            ));
            if($existingCount > 0)
            {
                return true;
            }

            $propId = !empty($deal['Deal']['property_id']) ? (int)$deal['Deal']['property_id'] : 0;
            $flatArr = $this->Property->find('first', array(
                'fields' => array('Property.id', 'Property.price', 'Property.status'),
                'conditions' => array('Property.id' => $propId),
                'recursive' => -1
            ));
            $grossDealAmount = !empty($flatArr['Property']['price']) ? (float)$flatArr['Property']['price'] : (float)$deal['Deal']['total_amount'];
            $totalAmount = (float)$deal['Deal']['total_amount'];
            $dealDate = !empty($deal['Deal']['date']) ? $deal['Deal']['date'] : date('Y-m-d');

            $template = $this->_dealPlanTemplate($planId, $totalAmount, $grossDealAmount);
            if(!empty($template['enabled']) && !empty($template['rows']))
            {
                $created = $this->_createDealScheduleFromTemplate($dealId, $template, $dealDate, false);
                if($created)
                {
                    if(!empty($flatArr['Property']['id']) && (empty($flatArr['Property']['status']) || strtolower($flatArr['Property']['status']) !== 'sold'))
                    {
                        $this->Property->id = (int)$flatArr['Property']['id'];
                        $this->Property->saveField('status', 'Sold');
                    }
                    return true;
                }
            }
        }
        catch(Throwable $e) {}
        return false;
    }

    protected function _dealPlanRecord($planId)
    {
        $planId = (int)$planId;
        if($planId<=0) return array();
        $this->loadModel('Admin.Deal');
        $rows = $this->Deal->query(
            "SELECT `id`,`name`,`installment`,`total_amount`,`status` FROM `plans` WHERE `id`=".$planId." LIMIT 1"
        );
        if(empty($rows)) return array();
        $row = reset($rows);
        if(isset($row['plans'])) return (array)$row['plans'];
        if(isset($row['Plan'])) return (array)$row['Plan'];
        if(isset($row[0])) return (array)$row[0];
        return array();
    }

    protected function _dealScheduleDateByMonthOffset($baseTimestamp, $monthOffset)
    {
        $monthOffset = max(0,(int)$monthOffset);
        $baseDay = (int)date('j',$baseTimestamp);
        $targetMonthStart = strtotime(date('Y-m-01',$baseTimestamp).' +'.$monthOffset.' month');
        $targetDay = min($baseDay,(int)date('t',$targetMonthStart));
        return date('Y-m-', $targetMonthStart).str_pad($targetDay,2,'0',STR_PAD_LEFT);
    }

    protected function _dealPlanTemplate($planId, $dealTotal, $grossDealAmount = null)
    {
        $planId = (int)$planId;
        $dealTotal = round((float)$dealTotal,2);
        if($planId <= 0)
        {
            return array('enabled'=>false,'rows'=>array(),'booking_amount'=>0);
        }
        $this->loadModel('Admin.PlanScheduleRow');
        $plan = $this->_dealPlanRecord($planId);
        $rows = $this->PlanScheduleRow->find('all',array(
            'conditions'=>array('PlanScheduleRow.plan_id'=>$planId),
            'order'=>array('PlanScheduleRow.sort_order'=>'ASC','PlanScheduleRow.id'=>'ASC'),
            'recursive'=>-1
        ));
        if(empty($rows))
        {
            return array('enabled'=>false,'rows'=>array(),'booking_amount'=>0);
        }
        $planTotal = !empty($plan['total_amount']) ? round((float)$plan['total_amount'],2) : 0;
        $grossDealAmount = $grossDealAmount === null ? $dealTotal : round((float)$grossDealAmount,2);
        if($planTotal <= 0)
        {
            throw new Exception(__('The selected payment plan must have a total amount greater than zero.'));
        }
        if($dealTotal <= 0)
        {
            throw new Exception(__('The discounted deal total must be greater than zero.'));
        }
        $normalizedRows = array();
        $scheduleTotal = 0.0;
        foreach($rows as $row)
        {
            $item = $row['PlanScheduleRow'];
            $count = max(1,(int)$item['repeat_count']);
            $value = (float)$item['amount_value'];
            $unitAmount = $item['amount_mode']==='percentage' ? round(($planTotal*$value)/100,2) : round($value,2);
            $scheduleTotal += ($unitAmount*$count);
            $item['unit_amount'] = $unitAmount;
            $item['unit_amounts'] = array_fill(0,$count,$unitAmount);
            $item['repeat_count'] = $count;
            $normalizedRows[] = $item;
        }
        $scheduleTotal = round($scheduleTotal,2);
        if(abs($scheduleTotal-$planTotal)>0.01)
        {
            throw new Exception(sprintf(__('Selected payment schedule totals %s and does not equal the payment-plan total %s.'),CakeNumber::format($scheduleTotal),CakeNumber::format($planTotal)));
        }

        $reductionRemaining = max(0,round($planTotal-$dealTotal,2));
        for($rowIndex=count($normalizedRows)-1;$rowIndex>=0 && $reductionRemaining>0.001;$rowIndex--)
        {
            for($paymentIndex=count($normalizedRows[$rowIndex]['unit_amounts'])-1;$paymentIndex>=0 && $reductionRemaining>0.001;$paymentIndex--)
            {
                $available = round((float)$normalizedRows[$rowIndex]['unit_amounts'][$paymentIndex],2);
                $deduction = min($available,$reductionRemaining);
                $normalizedRows[$rowIndex]['unit_amounts'][$paymentIndex] = round($available-$deduction,2);
                $reductionRemaining = round($reductionRemaining-$deduction,2);
            }
        }
        if($reductionRemaining>0.01)
        {
            throw new Exception(__('The deal total could not be fitted to the selected payment schedule.'));
        }

        $extensionRemaining = max(0,round($dealTotal-$planTotal,2));
        if($extensionRemaining>0.001)
        {
            $extensionRowIndex = null;
            foreach(array('monthly','quarterly','biannual') as $preferredType)
            {
                foreach($normalizedRows as $candidateIndex=>$candidateRow)
                {
                    if(strtolower((string)$candidateRow['payment_type'])===$preferredType && !empty($candidateRow['unit_amounts']))
                    {
                        $extensionRowIndex = $candidateIndex;
                        break 2;
                    }
                }
            }
            if($extensionRowIndex===null)
            {
                foreach($normalizedRows as $candidateIndex=>$candidateRow)
                {
                    if(strtolower((string)$candidateRow['payment_type'])==='custom' && !empty($candidateRow['unit_amounts']))
                    {
                        $extensionRowIndex = $candidateIndex;
                        break;
                    }
                }
            }
            if($extensionRowIndex!==null)
            {
                $referenceAmounts = $normalizedRows[$extensionRowIndex]['unit_amounts'];
                $referenceAmount = round((float)reset($referenceAmounts),2);
                if($referenceAmount<=0)
                {
                    throw new Exception(__('The recurring payment amount must be greater than zero to extend this plan.'));
                }
                while($extensionRemaining>0.001)
                {
                    $nextAmount = min($referenceAmount,$extensionRemaining);
                    $normalizedRows[$extensionRowIndex]['unit_amounts'][] = round($nextAmount,2);
                    $extensionRemaining = round($extensionRemaining-$nextAmount,2);
                }
                $normalizedRows[$extensionRowIndex]['repeat_count'] = count($normalizedRows[$extensionRowIndex]['unit_amounts']);
            }
            else
            {
                $insertAt = count($normalizedRows);
                foreach($normalizedRows as $candidateIndex=>$candidateRow)
                {
                    if(in_array(strtolower((string)$candidateRow['payment_type']),array('possession','final'),true))
                    {
                        $insertAt = $candidateIndex;
                        break;
                    }
                }
                $extensionRow = array(
                    'payment_type'=>'custom',
                    'title'=>__('Additional Balance Payment'),
                    'amount_mode'=>'fixed',
                    'amount_value'=>$extensionRemaining,
                    'repeat_count'=>1,
                    'interval_months'=>1,
                    'sort_order'=>$insertAt+1,
                    'unit_amount'=>$extensionRemaining,
                    'unit_amounts'=>array($extensionRemaining)
                );
                array_splice($normalizedRows,$insertAt,0,array($extensionRow));
                $extensionRemaining = 0;
            }
        }

        $adjustedTotal = 0.0;
        $bookingAmount = 0.0;
        $adjustedRows = array();
        foreach($normalizedRows as $rowIndex=>$item)
        {
            $item['unit_amounts'] = array_values(array_filter($item['unit_amounts'],function($amount){ return round((float)$amount,2)>0; }));
            if(empty($item['unit_amounts'])) continue;
            $item['repeat_count'] = count($item['unit_amounts']);
            $rowTotal = round(array_sum($item['unit_amounts']),2);
            $adjustedTotal += $rowTotal;
            if($item['payment_type']==='booking') $bookingAmount += $rowTotal;
            $item['unit_amount'] = (float)$item['unit_amounts'][0];
            $adjustedRows[] = $item;
        }
        $adjustedTotal = round($adjustedTotal,2);
        if(abs($adjustedTotal-$dealTotal)>0.01)
        {
            throw new Exception(sprintf(__('Adjusted schedule total is %s and must equal the discounted deal total %s.'),CakeNumber::format($adjustedTotal),CakeNumber::format($dealTotal)));
        }
        return array(
            'enabled'=>true,
            'rows'=>$adjustedRows,
            'booking_amount'=>round($bookingAmount,2),
            'plan_total'=>$planTotal,
            'discount'=>max(0,round($grossDealAmount-$dealTotal,2)),
            'schedule_total'=>$adjustedTotal,
            'adjustment_amount'=>round($dealTotal-$planTotal,2)
        );
    }

    protected function _createDealScheduleFromTemplate($dealId, $template, $dealDate, $atomic = true)
    {
        $dealId = (int)$dealId;
        if($dealId <= 0 || empty($template['enabled']) || empty($template['rows']))
        {
            return false;
        }
        $this->loadModel('Admin.PlansPayment');
        $baseTimestamp = strtotime((string)$dealDate);
        if(!$baseTimestamp) $baseTimestamp = time();
        $recurringTypes = array('monthly','quarterly','biannual');
        $terminalMonth = 0;
        foreach($template['rows'] as $row)
        {
            $type = strtolower(trim((string)$row['payment_type']));
            $count = max(1,(int)$row['repeat_count']);
            $intervalMonths = max(0,(int)$row['interval_months']);
            if(in_array($type,$recurringTypes,true) || ($type==='custom' && $count>1))
            {
                $terminalMonth = max($terminalMonth,$intervalMonths*$count);
            }
            elseif($type==='confirmation')
            {
                $terminalMonth = max($terminalMonth,$intervalMonths);
            }
        }
        $terminalCursor = $terminalMonth;
        $saveRows = array();
        foreach($template['rows'] as $sortIndex=>$row)
        {
            $count = max(1,(int)$row['repeat_count']);
            $intervalMonths = max(0,(int)$row['interval_months']);
            $type = strtolower(trim((string)$row['payment_type']));
            $unitAmounts = !empty($row['unit_amounts']) ? array_values($row['unit_amounts']) : array_fill(0,$count,(float)$row['unit_amount']);
            for($index=1;$index<=$count;$index++)
            {
                $dueMonth = 0;
                if(in_array($type,$recurringTypes,true) || ($type==='custom' && $count>1))
                {
                    $dueMonth = $intervalMonths*$index;
                }
                elseif($type==='confirmation')
                {
                    $dueMonth = $intervalMonths;
                }
                elseif(in_array($type,array('possession','final'),true))
                {
                    $terminalCursor += max(1,$intervalMonths);
                    $dueMonth = $terminalCursor;
                }
                elseif($type==='custom')
                {
                    $dueMonth = $intervalMonths;
                }
                $title = trim((string)$row['title']);
                if($count>1) $title .= ' '.$index;
                $dueDate = $this->_dealScheduleDateByMonthOffset($baseTimestamp,$dueMonth);
                $dueTimestamp = strtotime($dueDate);
                $saveRows[] = array(
                    '_due_timestamp'=>$dueTimestamp,
                    '_sort_index'=>(int)$sortIndex,
                    '_payment_index'=>(int)$index,
                    'PlansPayment'=>array(
                        'deal_id'=>$dealId,
                        'name'=>$title,
                        'amount'=>round((float)(isset($unitAmounts[$index-1]) ? $unitAmounts[$index-1] : $row['unit_amount']),2),
                        'date'=>$dueDate,
                        'is_deal_charge'=>0,
                        'deal_charge_id'=>0
                    )
                );
            }
        }
        if(empty($saveRows)) return false;
        usort($saveRows,function($left,$right){
            if($left['_due_timestamp']===$right['_due_timestamp'])
            {
                if($left['_sort_index']===$right['_sort_index']) return $left['_payment_index']-$right['_payment_index'];
                return $left['_sort_index']-$right['_sort_index'];
            }
            return $left['_due_timestamp']<$right['_due_timestamp'] ? -1 : 1;
        });

        // Clean up un-paid regular rows to avoid partial leftovers
        try
        {
            $this->PlansPayment->deleteAll(array(
                'PlansPayment.deal_id' => $dealId,
                'OR' => array('PlansPayment.is_deal_charge' => 0, 'PlansPayment.is_deal_charge IS NULL')
            ), false);
        }
        catch(Exception $delEx) {}

        // Fetch MAX(id) and assign strictly ascending IDs to prevent duplicate key collisions
        $maxIdRow = $this->PlansPayment->query("SELECT MAX(`id`) AS max_id FROM `plans_payments`");
        $currentMaxId = 0;
        if(!empty($maxIdRow[0][0]['max_id'])) { $currentMaxId = (int)$maxIdRow[0][0]['max_id']; }
        elseif(!empty($maxIdRow[0]['plans_payments']['max_id'])) { $currentMaxId = (int)$maxIdRow[0]['plans_payments']['max_id']; }
        $nextPlanId = $currentMaxId + 1;

        foreach($saveRows as $key=>$saveRow)
        {
            unset($saveRows[$key]['_due_timestamp'],$saveRows[$key]['_sort_index'],$saveRows[$key]['_payment_index']);
            $saveRows[$key]['PlansPayment']['id'] = $nextPlanId++;
        }

        $savedOk = false;
        try
        {
            $results = $this->PlansPayment->saveAll($saveRows,array('atomic'=>(bool)$atomic,'validate'=>false));
            $savedOk = $atomic ? (bool)$results : (is_array($results) && !in_array(false,$results,true));
        }
        catch(Exception $saveEx)
        {
            $savedOk = false;
        }

        if(!$savedOk)
        {
            $savedOk = true;
            foreach($saveRows as $sRow)
            {
                $rec = isset($sRow['PlansPayment']) ? $sRow['PlansPayment'] : $sRow;
                $curMax = $this->PlansPayment->query("SELECT MAX(`id`) AS max_id FROM `plans_payments`");
                $freshMax = !empty($curMax[0][0]['max_id']) ? (int)$curMax[0][0]['max_id'] : (int)$nextPlanId;
                $rec['id'] = $freshMax + 1;
                $this->PlansPayment->create();
                if(!$this->PlansPayment->save(array('PlansPayment' => $rec), array('validate' => false, 'callbacks' => false)))
                {
                    $savedOk = false;
                    break;
                }
            }
        }

        try
        {
            $finMax = $this->PlansPayment->query("SELECT MAX(`id`) AS max_id FROM `plans_payments`");
            $finalMaxId = !empty($finMax[0][0]['max_id']) ? (int)$finMax[0][0]['max_id'] : 0;
            if($finalMaxId > 0)
            {
                $this->PlansPayment->query("ALTER TABLE `plans_payments` AUTO_INCREMENT = " . ($finalMaxId + 1));
            }
        }
        catch(Exception $aiEx) {}

        return $savedOk;
    }

    protected function _resolveDealProjectId($dealId)
    {
        $dealId = (int)$dealId;
        if($dealId <= 0)
        {
            return 0;
        }
        $this->loadModel('Deal');
        $dealRow = $this->Deal->find('first', array(
            'fields' => array('Deal.id', 'Property.project_id'),
            'joins' => array(
                array('table' => 'properties', 'alias' => 'Property', 'type' => 'LEFT', 'conditions' => array('Deal.property_id=Property.id'))
            ),
            'conditions' => array('Deal.id' => $dealId),
            'recursive' => -1
        ));
        if(!empty($dealRow['Property']['project_id']))
        {
            return (int)$dealRow['Property']['project_id'];
        }
        return 0;
    }

    protected function _autoLetterTypeConfig($letterType = '')
    {
        $letterType = trim((string)$letterType);
        $year = date('Y');
        $config = array(
            'prefix_field' => 'letters_prefix',
            'next_field' => 'letters_next_no',
            'default_prefix' => 'REL-' . $year . '-',
            'template_name' => 'Real Estate Letter',
            'title' => 'Real Estate Letter',
            'description' => '<p><strong>Date:</strong> {{letter_date}}<br><strong>Reference:</strong> {{letter_ref_no}}</p><p>Dear {{client_name}},</p><p>This is a real estate letter for your membership <strong>{{deal_invoice_no}}</strong>.</p><p>Regards,<br>{{site_organization}}</p>'
        );
        if($letterType === 'RLT_WELCOME')
        {
            $config = array(
                'prefix_field' => 'welcome_letter_prefix',
                'next_field' => 'welcome_letter_next_no',
                'default_prefix' => 'WEL-' . $year . '-',
                'template_name' => 'Welcome Letter',
                'title' => 'Welcome Letter',
                'description' => '<p><strong>Date:</strong> {{letter_date}}<br><strong>Reference:</strong> {{letter_ref_no}}</p><p>Dear {{client_name}},</p><p>Welcome to {{site_organization}}. We are pleased to confirm your new booking in <strong>{{project_name}}</strong>.</p><p><strong>Membership No:</strong> {{deal_invoice_no}}<br><strong>Property:</strong> {{property_name}}<br><strong>Type:</strong> {{property_type}}<br><strong>Area:</strong> {{property_area}}</p><p>Thank you for trusting us.</p><p>Regards,<br>{{site_organization}}</p>'
            );
        }
        elseif($letterType === 'RLT_BOOKING')
        {
            $config = array(
                'prefix_field' => 'booking_letter_prefix',
                'next_field' => 'booking_letter_next_no',
                'default_prefix' => 'BKG-' . $year . '-',
                'template_name' => 'Booking Letter',
                'title' => 'Booking Letter',
                'description' => '<p><strong>Date:</strong> {{letter_date}}<br><strong>Reference:</strong> {{letter_ref_no}}</p><p>Dear {{client_name}},</p><p>This is to confirm your booking of <strong>{{property_name}}</strong> in {{project_name}}.</p><p><strong>Membership No:</strong> {{deal_invoice_no}}<br><strong>Booking Date:</strong> {{deal_date}}<br><strong>Total Amount:</strong> {{deal_total_amount}}<br><strong>Booking Amount:</strong> {{deal_booking_amount}}<br><strong>Property Type:</strong> {{property_type}}<br><strong>Area:</strong> {{property_area}}<br><strong>Allotment No:</strong> {{ballot_allotment_no}}</p><p>Regards,<br>{{site_organization}}</p>'
            );
        }
        elseif($letterType === 'RLT_CHARGE')
        {
            $config = array(
                'prefix_field' => 'charge_letter_prefix',
                'next_field' => 'charge_letter_next_no',
                'default_prefix' => 'CHG-' . $year . '-',
                'template_name' => 'Deal Charges Agreement',
                'title' => 'Deal Charges Agreement',
                'description' => '<div dir="rtl" style="font-family:Jameel Noori Nastaleeq,Noto Nastaliq Urdu,serif;line-height:2;"><h2 style="text-align:center;">معاہدہ برائے اضافی کام / اضافی چارجز</h2><p><strong>تاریخ:</strong> {{letter_date}} &nbsp; <strong>حوالہ:</strong> {{letter_ref_no}}</p><p>یہ معاہدہ فریق اوّل <strong>{{client_name}}</strong> اور فریق دوم <strong>{{agency_name}}</strong> کے درمیان پراپرٹی / فائل <strong>{{property_file}}</strong>، ممبر شپ نمبر <strong>{{deal_invoice_no}}</strong> کے اضافی کام / اضافی چارجز کے لیے باہمی رضامندی سے طے پایا۔</p><p><strong>اضافی کام:</strong> {{charge_work_details}}<br><strong>کام کی حالت:</strong> {{charge_work_status_urdu}}<br><strong>کل اضافی چارجز:</strong> {{deal_charges_total}} ({{deal_charges_total_words_urdu}})</p></div>'
            );
        }
        elseif($letterType === 'RLT_DEMARCATION')
        {
            $config = array(
                'prefix_field' => 'demarcation_letter_prefix',
                'next_field' => 'demarcation_letter_next_no',
                'default_prefix' => 'DEM-' . $year . '-',
                'template_name' => 'Demarcation Letter',
                'title' => 'Demarcation Letter',
                'description' => '<p><strong>Date:</strong> {{letter_date}}<br><strong>Reference:</strong> {{letter_ref_no}}</p><p>Dear {{client_name}},</p><p>This letter confirms that demarcation has been completed for your allotted unit.</p><p><strong>Membership No:</strong> {{deal_invoice_no}}<br><strong>Allotment No:</strong> {{ballot_allotment_no}}<br><strong>Property Number:</strong> {{ballot_property_no}}<br><strong>Block:</strong> {{ballot_block_name}}<br><strong>Street Address:</strong> {{ballot_street_address}}<br><strong>Area:</strong> {{ballot_area_full}}</p><p>Regards,<br>{{site_organization}}</p>'
            );
        }
        elseif($letterType === 'RLT_DESIGN_MAP_APPROVAL')
        {
            $config = array(
                'prefix_field' => 'design_map_approval_prefix',
                'next_field' => 'design_map_approval_next_no',
                'default_prefix' => 'DMA-' . $year . '-',
                'template_name' => 'Design / Map Approval Letter',
                'title' => 'Design / Map Approval Letter',
                'description' => '<p><strong>Date:</strong> {{letter_date}}<br><strong>Reference:</strong> {{letter_ref_no}}</p><p>Dear {{client_name}},</p><p>Your design/map submission for <strong>{{property_name}}</strong> has been approved subject to applicable by-laws and authority rules.</p><p><strong>Membership No:</strong> {{deal_invoice_no}}<br><strong>Project:</strong> {{project_name}}<br><strong>Property Type:</strong> {{property_type}}<br><strong>Allotment No:</strong> {{ballot_allotment_no}}<br><strong>Block:</strong> {{ballot_block_name}}<br><strong>Street Address:</strong> {{ballot_street_address}}<br><strong>Area:</strong> {{ballot_area_full}}</p><p>Regards,<br>{{site_organization}}</p>'
            );
        }
        elseif($letterType === 'RLT_NOC')
        {
            $config = array(
                'prefix_field' => 'noc_letter_prefix',
                'next_field' => 'noc_letter_next_no',
                'default_prefix' => 'NOC-' . $year . '-',
                'template_name' => 'NOC Letter',
                'title' => 'NOC Letter',
                'description' => '<p><strong>Date:</strong> {{letter_date}}<br><strong>Reference:</strong> {{letter_ref_no}}</p><p>Dear {{client_name}},</p><p>No Objection Certificate is hereby issued for <strong>{{property_name}}</strong> (Membership {{deal_invoice_no}}), subject to company policy and record verification.</p><p><strong>Project:</strong> {{project_name}}<br><strong>Property Type:</strong> {{property_type}}<br><strong>Allotment No:</strong> {{ballot_allotment_no}}<br><strong>Property Number:</strong> {{ballot_property_no}}<br><strong>Block:</strong> {{ballot_block_name}}<br><strong>Street Address:</strong> {{ballot_street_address}}</p><p>Regards,<br>{{site_organization}}</p>'
            );
        }
        elseif($letterType === 'RLT_ALLOT')
        {
            $config = array(
                'prefix_field' => 'allot_letter_prefix',
                'next_field' => 'allot_letter_next_no',
                'default_prefix' => 'ALT-' . $year . '-',
                'template_name' => 'Allotment Letter',
                'title' => 'Allotment Letter',
                'description' => '<p><strong>Date:</strong> {{letter_date}}<br><strong>Reference:</strong> {{letter_ref_no}}</p><p>Dear {{client_name}},</p><p>This letter confirms allotment of <strong>{{property_name}}</strong> in {{project_name}}.</p><p><strong>Membership No:</strong> {{deal_invoice_no}}<br><strong>Allotment No:</strong> {{ballot_allotment_no}}<br><strong>Property Number:</strong> {{ballot_property_no}}<br><strong>Block:</strong> {{ballot_block_name}}<br><strong>Street Address:</strong> {{ballot_street_address}}<br><strong>Area:</strong> {{ballot_area_full}}</p><p>Regards,<br>{{site_organization}}</p>'
            );
        }
        elseif($letterType === 'RLT_DEMAND')
        {
            $config = array(
                'prefix_field' => 'demand_letter_prefix',
                'next_field' => 'demand_letter_next_no',
                'default_prefix' => 'DMN-' . $year . '-',
                'template_name' => 'Payment Demand Notice',
                'title' => 'Payment Demand Notice',
                'description' => '<p><strong>Date:</strong> {{letter_date}}<br><strong>Reference:</strong> {{letter_ref_no}}</p><p>To,<br>{{client_name}}<br>{{client_address}}</p><p>This is to remind you that payment for <strong>{{property_name}}</strong> (Membership {{deal_invoice_no}}) is due as per agreed schedule.</p><p><strong>Property Type:</strong> {{property_type}}<br><strong>Area:</strong> {{property_area}}</p><p>Regards,<br>{{site_organization}}</p>'
            );
        }
        elseif($letterType === 'RLT_RECEIPT')
        {
            $config = array(
                'prefix_field' => 'receipt_letter_prefix',
                'next_field' => 'receipt_letter_next_no',
                'default_prefix' => 'RCP-' . $year . '-',
                'template_name' => 'Payment Acknowledgement',
                'title' => 'Payment Acknowledgement',
                'description' => '<p><strong>Date:</strong> {{letter_date}}<br><strong>Reference:</strong> {{letter_ref_no}}</p><p>Dear {{client_name}},</p><p>We acknowledge receipt of payment for <strong>{{property_name}}</strong> (Membership {{deal_invoice_no}}).</p><p><strong>Project:</strong> {{project_name}}<br><strong>Property Type:</strong> {{property_type}}</p><p>Regards,<br>{{site_organization}}</p>'
            );
        }
        elseif($letterType === 'RLT_POSSESSION')
        {
            $config = array(
                'prefix_field' => 'possession_letter_prefix',
                'next_field' => 'possession_letter_next_no',
                'default_prefix' => 'POS-' . $year . '-',
                'template_name' => 'Possession Letter',
                'title' => 'Possession Letter',
                'description' => '<p><strong>Date:</strong> {{letter_date}}<br><strong>Reference:</strong> {{letter_ref_no}}</p><p>Dear {{client_name}},</p><p>This letter confirms possession handover for your unit.</p><p><strong>Membership No:</strong> {{deal_invoice_no}}<br><strong>Property:</strong> {{property_name}}<br><strong>Allotment No:</strong> {{ballot_allotment_no}}<br><strong>Street Address:</strong> {{ballot_street_address}}<br><strong>Area:</strong> {{ballot_area_full}}</p><p>Regards,<br>{{site_organization}}</p>'
            );
        }
        elseif($letterType === 'RLT_TRANSFER')
        {
            $config = array(
                'prefix_field' => 'transfer_letter_prefix',
                'next_field' => 'transfer_letter_next_no',
                'default_prefix' => 'TRF-' . $year . '-',
                'template_name' => 'Transfer / Mutation Letter',
                'title' => 'Transfer / Mutation Letter',
                'description' => '<p><strong>Date:</strong> {{letter_date}}<br><strong>Reference:</strong> {{letter_ref_no}}</p><p>Dear {{client_name}},</p><p>This is to certify that transfer / mutation request for <strong>{{property_name}}</strong> has been processed as per company policy.</p><p><strong>Membership No:</strong> {{deal_invoice_no}}<br><strong>Allotment No:</strong> {{ballot_allotment_no}}</p><p>Regards,<br>{{site_organization}}</p>'
            );
        }
        elseif($letterType === 'RLT_CANCEL')
        {
            $config = array(
                'prefix_field' => 'cancel_letter_prefix',
                'next_field' => 'cancel_letter_next_no',
                'default_prefix' => 'CNL-' . $year . '-',
                'template_name' => 'Cancellation Letter',
                'title' => 'Cancellation Letter',
                'description' => '<p><strong>Date:</strong> {{letter_date}}<br><strong>Reference:</strong> {{letter_ref_no}}</p><p>Dear {{client_name}},</p><p>This is to confirm cancellation of booking for <strong>{{property_name}}</strong> (Membership {{deal_invoice_no}}) as per request / policy.</p><p><strong>Project:</strong> {{project_name}}<br><strong>Property Type:</strong> {{property_type}}</p><p>Regards,<br>{{site_organization}}</p>'
            );
        }
        elseif($letterType === 'RLT_REFUND')
        {
            $config = array(
                'prefix_field' => 'refund_letter_prefix',
                'next_field' => 'refund_letter_next_no',
                'default_prefix' => 'RFD-' . $year . '-',
                'template_name' => 'Refund Agreement',
                'title' => 'Refund Agreement',
                'description' => '<p><strong>Date:</strong> {{letter_date}}<br><strong>Reference:</strong> {{letter_ref_no}}</p><p>Dear {{client_name}},</p><p>Your refund agreement has been issued for <strong>{{property_name}}</strong> (Membership {{deal_invoice_no}}).</p><p><strong>Total Paid:</strong> {{refund_total_paid}}<br><strong>Net Refundable:</strong> {{refund_net_refund}}</p><p>Regards,<br>{{site_organization}}</p>'
            );
        }
        return $config;
    }

    protected function _autoLetterDefaults()
    {
        $year = date('Y');
        return array(
            'letters_prefix' => 'REL-' . $year . '-',
            'letters_next_no' => 1,
            'welcome_letter_prefix' => 'WEL-' . $year . '-',
            'welcome_letter_next_no' => 1,
            'booking_letter_prefix' => 'BKG-' . $year . '-',
            'booking_letter_next_no' => 1,
            'charge_letter_prefix' => 'CHG-' . $year . '-',
            'charge_letter_next_no' => 1,
            'demarcation_letter_prefix' => 'DEM-' . $year . '-',
            'demarcation_letter_next_no' => 1,
            'design_map_approval_prefix' => 'DMA-' . $year . '-',
            'design_map_approval_next_no' => 1,
            'noc_letter_prefix' => 'NOC-' . $year . '-',
            'noc_letter_next_no' => 1,
            'allot_letter_prefix' => 'ALT-' . $year . '-',
            'allot_letter_next_no' => 1,
            'demand_letter_prefix' => 'DMN-' . $year . '-',
            'demand_letter_next_no' => 1,
            'receipt_letter_prefix' => 'RCP-' . $year . '-',
            'receipt_letter_next_no' => 1,
            'possession_letter_prefix' => 'POS-' . $year . '-',
            'possession_letter_next_no' => 1,
            'transfer_letter_prefix' => 'TRF-' . $year . '-',
            'transfer_letter_next_no' => 1,
            'cancel_letter_prefix' => 'CNL-' . $year . '-',
            'cancel_letter_next_no' => 1,
            'refund_letter_prefix' => 'RFD-' . $year . '-',
            'refund_letter_next_no' => 1
        );
    }

    protected function _ensureAutoLetterReferenceSettingsColumns()
    {
        try
        {
            $this->loadModel('Admin.Ardsetting');
        }
        catch(Throwable $e)
        {
            return;
        }
        $sqlList = array(
            "ALTER TABLE `ardaccounts_settings` ADD COLUMN `letters_prefix` varchar(20) DEFAULT 'REL-' AFTER `deal_invoice_next_no`",
            "ALTER TABLE `ardaccounts_settings` ADD COLUMN `letters_next_no` int(11) NOT NULL DEFAULT 1 AFTER `letters_prefix`",
            "ALTER TABLE `ardaccounts_settings` ADD COLUMN `welcome_letter_prefix` varchar(20) DEFAULT 'WEL-' AFTER `letters_next_no`",
            "ALTER TABLE `ardaccounts_settings` ADD COLUMN `welcome_letter_next_no` int(11) NOT NULL DEFAULT 1 AFTER `welcome_letter_prefix`",
            "ALTER TABLE `ardaccounts_settings` ADD COLUMN `booking_letter_prefix` varchar(20) DEFAULT 'BKG-' AFTER `welcome_letter_next_no`",
            "ALTER TABLE `ardaccounts_settings` ADD COLUMN `booking_letter_next_no` int(11) NOT NULL DEFAULT 1 AFTER `booking_letter_prefix`",
            "ALTER TABLE `ardaccounts_settings` ADD COLUMN `charge_letter_prefix` varchar(20) DEFAULT 'CHG-' AFTER `booking_letter_next_no`",
            "ALTER TABLE `ardaccounts_settings` ADD COLUMN `charge_letter_next_no` int(11) NOT NULL DEFAULT 1 AFTER `charge_letter_prefix`",
            "ALTER TABLE `ardaccounts_settings` ADD COLUMN `demarcation_letter_prefix` varchar(20) DEFAULT 'DEM-' AFTER `booking_letter_next_no`",
            "ALTER TABLE `ardaccounts_settings` ADD COLUMN `demarcation_letter_next_no` int(11) NOT NULL DEFAULT 1 AFTER `demarcation_letter_prefix`",
            "ALTER TABLE `ardaccounts_settings` ADD COLUMN `design_map_approval_prefix` varchar(20) DEFAULT 'DMA-' AFTER `demarcation_letter_next_no`",
            "ALTER TABLE `ardaccounts_settings` ADD COLUMN `design_map_approval_next_no` int(11) NOT NULL DEFAULT 1 AFTER `design_map_approval_prefix`",
            "ALTER TABLE `ardaccounts_settings` ADD COLUMN `noc_letter_prefix` varchar(20) DEFAULT 'NOC-' AFTER `design_map_approval_next_no`",
            "ALTER TABLE `ardaccounts_settings` ADD COLUMN `noc_letter_next_no` int(11) NOT NULL DEFAULT 1 AFTER `noc_letter_prefix`",
            "ALTER TABLE `ardaccounts_settings` ADD COLUMN `allot_letter_prefix` varchar(20) DEFAULT 'ALT-' AFTER `noc_letter_next_no`",
            "ALTER TABLE `ardaccounts_settings` ADD COLUMN `allot_letter_next_no` int(11) NOT NULL DEFAULT 1 AFTER `allot_letter_prefix`",
            "ALTER TABLE `ardaccounts_settings` ADD COLUMN `demand_letter_prefix` varchar(20) DEFAULT 'DMN-' AFTER `allot_letter_next_no`",
            "ALTER TABLE `ardaccounts_settings` ADD COLUMN `demand_letter_next_no` int(11) NOT NULL DEFAULT 1 AFTER `demand_letter_prefix`",
            "ALTER TABLE `ardaccounts_settings` ADD COLUMN `receipt_letter_prefix` varchar(20) DEFAULT 'RCP-' AFTER `demand_letter_next_no`",
            "ALTER TABLE `ardaccounts_settings` ADD COLUMN `receipt_letter_next_no` int(11) NOT NULL DEFAULT 1 AFTER `receipt_letter_prefix`",
            "ALTER TABLE `ardaccounts_settings` ADD COLUMN `possession_letter_prefix` varchar(20) DEFAULT 'POS-' AFTER `receipt_letter_next_no`",
            "ALTER TABLE `ardaccounts_settings` ADD COLUMN `possession_letter_next_no` int(11) NOT NULL DEFAULT 1 AFTER `possession_letter_prefix`",
            "ALTER TABLE `ardaccounts_settings` ADD COLUMN `transfer_letter_prefix` varchar(20) DEFAULT 'TRF-' AFTER `possession_letter_next_no`",
            "ALTER TABLE `ardaccounts_settings` ADD COLUMN `transfer_letter_next_no` int(11) NOT NULL DEFAULT 1 AFTER `transfer_letter_prefix`",
            "ALTER TABLE `ardaccounts_settings` ADD COLUMN `cancel_letter_prefix` varchar(20) DEFAULT 'CNL-' AFTER `transfer_letter_next_no`",
            "ALTER TABLE `ardaccounts_settings` ADD COLUMN `cancel_letter_next_no` int(11) NOT NULL DEFAULT 1 AFTER `cancel_letter_prefix`",
            "ALTER TABLE `ardaccounts_settings` ADD COLUMN `refund_letter_prefix` varchar(20) DEFAULT 'RFD-' AFTER `cancel_letter_next_no`",
            "ALTER TABLE `ardaccounts_settings` ADD COLUMN `refund_letter_next_no` int(11) NOT NULL DEFAULT 1 AFTER `refund_letter_prefix`"
        );
        foreach($sqlList as $sql)
        {
            try
            {
                $this->Ardsetting->query($sql);
            }
            catch(Throwable $e)
            {
                // Ignore if column already exists.
            }
        }
    }

    protected function _autoLetterSettingsRow($projectId = 0)
    {
        $projectId = (int)$projectId;
        $defaults = $this->_autoLetterDefaults();
        if($projectId <= 0)
        {
            return array_merge($defaults, array('project_id' => 0));
        }
        $this->_ensureAutoLetterReferenceSettingsColumns();
        try
        {
            $this->loadModel('Admin.Ardsetting');
            $row = $this->Ardsetting->find('first', array(
                'conditions' => array('Ardsetting.project_id' => $projectId),
                'order' => array('Ardsetting.id' => 'desc'),
                'recursive' => -1
            ));
        }
        catch(Throwable $e)
        {
            $row = array();
        }
        $merged = array_merge($defaults, array('project_id' => $projectId));
        if(!empty($row['Ardsetting']))
        {
            $merged = array_merge($merged, $row['Ardsetting']);
        }
        if(!empty($row['Ardsetting']['id']))
        {
            $merged['id'] = (int)$row['Ardsetting']['id'];
        }
        return $merged;
    }

    protected function _ensureRealestateLettersStorage()
    {
        $this->loadModel('Admin.Realestateletter');
        $tablesToCheck = array('real_estate_letters', 'real_estate_letters_trash', 'client_notifications');
        foreach ($tablesToCheck as $tName) {
            $exists = array();
            try {
                $exists = $this->Realestateletter->query("SHOW TABLES LIKE '{$tName}'");
            } catch (Exception $e) {
                $exists = array();
            }
            if (empty($exists)) {
                if ($tName === 'real_estate_letters') {
                    $tableSql = "CREATE TABLE IF NOT EXISTS `{$tName}` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        `deal_id` int(11) NOT NULL,
                        `client_id` int(11) DEFAULT NULL,
                        `template_id` int(11) DEFAULT NULL,
                        `user_id` int(11) DEFAULT NULL,
                        `letter_type` varchar(30) DEFAULT NULL,
                        `reference_no` varchar(60) DEFAULT NULL,
                        `title` varchar(255) DEFAULT NULL,
                        `letter_date` date DEFAULT NULL,
                        `description` longtext,
                        `status` varchar(15) NOT NULL DEFAULT 'Published',
                        `created` datetime DEFAULT NULL,
                        `modified` datetime DEFAULT NULL,
                        PRIMARY KEY (`id`),
                        KEY `deal_id` (`deal_id`),
                        KEY `client_id` (`client_id`),
                        KEY `template_id` (`template_id`),
                        KEY `user_id` (`user_id`),
                        KEY `letter_type` (`letter_type`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
                    try {
                        $this->Realestateletter->query($tableSql);
                    } catch (Exception $e) {}
                }
            } else {
                try {
                    $cols = $this->Realestateletter->query("SHOW FULL COLUMNS FROM `{$tName}` LIKE 'id'");
                    if (!empty($cols[0]['COLUMNS'])) {
                        $col = $cols[0]['COLUMNS'];
                        $extra = strtolower(isset($col['Extra']) ? $col['Extra'] : '');
                        $key = strtoupper(isset($col['Key']) ? $col['Key'] : '');
                        $type = isset($col['Type']) ? $col['Type'] : ($tName === 'client_notifications' ? 'bigint(20) unsigned' : 'int(11)');
                        if (strpos($extra, 'auto_increment') === false) {
                            if ($key === 'PRI') {
                                $this->Realestateletter->query("ALTER TABLE `{$tName}` MODIFY `id` {$type} NOT NULL AUTO_INCREMENT");
                            } else {
                                $this->Realestateletter->query("ALTER TABLE `{$tName}` MODIFY `id` {$type} NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (`id`)");
                            }
                        }
                    }
                    $maxRow = $this->Realestateletter->query("SELECT MAX(`id`) AS max_id FROM `{$tName}`");
                    $maxId = 0;
                    if (!empty($maxRow[0][0]['max_id'])) { $maxId = (int)$maxRow[0][0]['max_id']; }
                    elseif (!empty($maxRow[0][$tName]['max_id'])) { $maxId = (int)$maxRow[0][$tName]['max_id']; }
                    if ($maxId > 0) {
                        $st = $this->Realestateletter->query("SHOW TABLE STATUS LIKE '{$tName}'");
                        $curAi = 0;
                        if (!empty($st[0]['TABLES']['Auto_increment'])) { $curAi = (int)$st[0]['TABLES']['Auto_increment']; }
                        elseif (!empty($st[0][0]['Auto_increment'])) { $curAi = (int)$st[0][0]['Auto_increment']; }
                        if ($curAi <= $maxId) {
                            $this->Realestateletter->query("ALTER TABLE `{$tName}` AUTO_INCREMENT = " . ($maxId + 1));
                        }
                    }
                } catch (Exception $e) {}
            }
        }
        try {
            $this->Realestateletter->schema(true);
        } catch (Exception $e) {}
        return true;
    }

    protected function _autoLetterReferenceExists($referenceNo, $projectId = 0)
    {
        $referenceNo = trim((string)$referenceNo);
        if($referenceNo === '')
        {
            return false;
        }
        $this->loadModel('Admin.Realestateletter');
        $conditions = array('Realestateletter.reference_no' => $referenceNo);
        $query = array(
            'conditions' => $conditions,
            'recursive' => -1
        );
        $projectId = (int)$projectId;
        if($projectId > 0)
        {
            $query['joins'] = array(
                array('table' => 'deals', 'alias' => 'Deal', 'type' => 'LEFT', 'conditions' => array('Deal.id=Realestateletter.deal_id')),
                array('table' => 'properties', 'alias' => 'Property', 'type' => 'LEFT', 'conditions' => array('Property.id=Deal.property_id'))
            );
            $conditions[] = array('Property.project_id' => $projectId);
            $query['conditions'] = $conditions;
        }
        try
        {
            return ((int)$this->Realestateletter->find('count', $query) > 0);
        }
        catch(Throwable $e)
        {
            return false;
        }
    }

    protected function _nextAutoLetterReferenceNo($projectId = 0, $letterType = '')
    {
        $projectId = (int)$projectId;
        $config = $this->_autoLetterTypeConfig($letterType);
        $prefixField = isset($config['prefix_field']) ? (string)$config['prefix_field'] : 'letters_prefix';
        $nextField = isset($config['next_field']) ? (string)$config['next_field'] : 'letters_next_no';
        $defaultPrefix = isset($config['default_prefix']) ? (string)$config['default_prefix'] : 'REL-'.date('Y').'-';
        $settings = $this->_autoLetterSettingsRow($projectId);
        $defaults = $this->_autoLetterDefaults();

        $prefix = trim((string)(isset($settings[$prefixField]) ? $settings[$prefixField] : ''));
        if($prefix === '' && $prefixField !== 'letters_prefix')
        {
            $prefix = trim((string)(isset($settings['letters_prefix']) ? $settings['letters_prefix'] : ''));
        }
        if($prefix === '')
        {
            $prefix = trim((string)(isset($defaults[$prefixField]) ? $defaults[$prefixField] : ''));
        }
        if($prefix === '')
        {
            $prefix = $defaultPrefix;
        }
        if($prefix === '')
        {
            $prefix = 'REL-'.date('Y').'-';
        }
        if(strlen($prefix) > 20)
        {
            $prefix = substr($prefix, 0, 20);
        }

        $nextNo = max(1, (int)(isset($settings[$nextField]) ? $settings[$nextField] : 1));
        if($nextNo <= 0 && $nextField !== 'letters_next_no')
        {
            $nextNo = max(1, (int)(isset($settings['letters_next_no']) ? $settings['letters_next_no'] : 1));
        }
        $candidate = '';
        $guard = 0;
        while(true)
        {
            $candidate = $prefix . $nextNo;
            if(!$this->_autoLetterReferenceExists($candidate, $projectId))
            {
                break;
            }
            $nextNo++;
            $guard++;
            if($guard > 100000)
            {
                break;
            }
        }

        if($projectId > 0)
        {
            try
            {
                $this->loadModel('Admin.Ardsetting');
                $saveRow = array(
                    'project_id' => $projectId,
                    $prefixField => $prefix,
                    $nextField => $nextNo + 1
                );
                if(!empty($settings['id']))
                {
                    $saveRow['id'] = (int)$settings['id'];
                    $this->Ardsetting->id = (int)$settings['id'];
                }
                else
                {
                    $this->Ardsetting->create();
                }
                $this->Ardsetting->save(array('Ardsetting' => $saveRow), false);
            }
            catch(Throwable $e)
            {
                // Keep flow alive even if counter update fails.
            }
        }
        return $candidate;
    }

    protected function _ensureAutoLetterTemplate($letterType)
    {
        $letterType = trim((string)$letterType);
        if($letterType === '')
        {
            return array();
        }
        $config = $this->_autoLetterTypeConfig($letterType);
        $templateName = isset($config['template_name']) ? (string)$config['template_name'] : 'Real Estate Letter';
        $templateDescription = isset($config['description']) ? (string)$config['description'] : '';
        $this->loadModel('Template');
        $template = $this->Template->find('first', array(
            'fields' => array('Template.id', 'Template.description', 'Template.name', 'Template.status', 'Template.is_new'),
            'conditions' => array('Template.type' => $letterType, 'Template.status' => 'Published'),
            'order' => array('Template.is_new' => 'asc', 'Template.id' => 'asc'),
            'recursive' => -1
        ));
        if(!empty($template['Template']['id']))
        {
            if(trim((string)$template['Template']['description']) === '' && $templateDescription !== '')
            {
                $this->Template->id = (int)$template['Template']['id'];
                $this->Template->save(array('Template' => array(
                    'id' => (int)$template['Template']['id'],
                    'description' => $templateDescription
                )), false);
                $template['Template']['description'] = $templateDescription;
            }
            return $template['Template'];
        }

        $template = $this->Template->find('first', array(
            'fields' => array('Template.id', 'Template.description', 'Template.name', 'Template.status', 'Template.is_new'),
            'conditions' => array('Template.type' => $letterType, 'Template.name' => $templateName),
            'order' => array('Template.id' => 'asc'),
            'recursive' => -1
        ));
        if(!empty($template['Template']['id']))
        {
            $saveRow = array(
                'id' => (int)$template['Template']['id'],
                'status' => 'Published',
                'is_new' => 'No'
            );
            if(trim((string)$template['Template']['description']) === '' && $templateDescription !== '')
            {
                $saveRow['description'] = $templateDescription;
                $template['Template']['description'] = $templateDescription;
            }
            $this->Template->id = (int)$template['Template']['id'];
            $this->Template->save(array('Template' => $saveRow), false);
            $template['Template']['status'] = 'Published';
            $template['Template']['is_new'] = 'No';
            return $template['Template'];
        }

        $this->Template->create();
        $this->Template->save(array('Template' => array(
            'name' => $templateName,
            'description' => $templateDescription,
            'type' => $letterType,
            'status' => 'Published',
            'is_new' => 'No'
        )), false);
        $id = (int)$this->Template->id;
        if($id <= 0)
        {
            return array();
        }
        return array(
            'id' => $id,
            'description' => $templateDescription,
            'name' => $templateName
        );
    }

    protected function _autoLetterNotificationTypeMap()
    {
        return array(
            'RLT_WELCOME' => 'LWL',
            'RLT_BOOKING' => 'LBK'
        );
    }

    protected function _autoLetterNotificationFallbackType()
    {
        return 'LIS';
    }

    protected function _autoLetterNotificationTemplateDefaults()
    {
        return array(
            'LWL' => array(
                'sms_name' => 'Letter Issued Welcome Letter',
                'sms_body' => 'Dear $clientName, your Welcome Letter has been issued. Ref: $referenceNo, File: $invoiceNo, Property: $propertyName. $url',
                'email_name' => 'Letter Issued Welcome Letter',
                'email_body' => '<p>Dear $clientName,</p><p>Your <strong>Welcome Letter</strong> has been issued.</p><p><strong>Reference:</strong> $referenceNo<br><strong>Membership:</strong> $invoiceNo<br><strong>Property:</strong> $propertyName<br><strong>Project:</strong> $projectName</p><p><a href="$url" target="_blank">View Letter</a></p><p>Regards,<br>$siteName<br>$contactNo</p>'
            ),
            'LBK' => array(
                'sms_name' => 'Letter Issued Booking Letter',
                'sms_body' => 'Dear $clientName, your Booking Letter has been issued. Ref: $referenceNo, File: $invoiceNo, Property: $propertyName. $url',
                'email_name' => 'Letter Issued Booking Letter',
                'email_body' => '<p>Dear $clientName,</p><p>Your <strong>Booking Letter</strong> has been issued.</p><p><strong>Reference:</strong> $referenceNo<br><strong>Membership:</strong> $invoiceNo<br><strong>Property:</strong> $propertyName<br><strong>Project:</strong> $projectName</p><p><a href="$url" target="_blank">View Letter</a></p><p>Regards,<br>$siteName<br>$contactNo</p>'
            ),
            'LIS' => array(
                'sms_name' => 'Letter Issued Generic',
                'sms_body' => 'Dear $clientName, your $letterType has been issued. Ref: $referenceNo, File: $invoiceNo. $url',
                'email_name' => 'Letter Issued Generic',
                'email_body' => '<p>Dear $clientName,</p><p>Your <strong>$letterType</strong> has been issued.</p><p><strong>Reference:</strong> $referenceNo<br><strong>Membership:</strong> $invoiceNo<br><strong>Property:</strong> $propertyName</p><p><a href="$url" target="_blank">View Letter</a></p><p>Regards,<br>$siteName<br>$contactNo</p>'
            )
        );
    }

    protected function _ensureAutoLetterNotificationTemplates()
    {
        try
        {
            $this->loadModel('Smstemplate');
            $this->loadModel('Emailtemplate');
        }
        catch(Throwable $e)
        {
            return;
        }
        foreach((array)$this->_autoLetterNotificationTemplateDefaults() as $typeCode => $row)
        {
            $typeCode = strtoupper(substr(trim((string)$typeCode), 0, 3));
            if($typeCode === '')
            {
                continue;
            }
            try
            {
                $smsExists = $this->Smstemplate->find('first', array(
                    'fields' => array('Smstemplate.id'),
                    'conditions' => array('Smstemplate.type' => $typeCode),
                    'recursive' => -1
                ));
                if(empty($smsExists['Smstemplate']['id']))
                {
                    $this->Smstemplate->create();
                    $this->Smstemplate->save(array('Smstemplate' => array(
                        'name' => isset($row['sms_name']) ? (string)$row['sms_name'] : 'Letter SMS',
                        'description' => isset($row['sms_body']) ? (string)$row['sms_body'] : '',
                        'status' => 'Published',
                        'type' => $typeCode
                    )), array('validate' => false, 'callbacks' => false));
                }
            }
            catch(Throwable $e)
            {
                // Notification template ensure should never block flow.
            }
            try
            {
                $emailExists = $this->Emailtemplate->find('first', array(
                    'fields' => array('Emailtemplate.id'),
                    'conditions' => array('Emailtemplate.type' => $typeCode),
                    'recursive' => -1
                ));
                if(empty($emailExists['Emailtemplate']['id']))
                {
                    $this->Emailtemplate->create();
                    $this->Emailtemplate->save(array('Emailtemplate' => array(
                        'name' => isset($row['email_name']) ? (string)$row['email_name'] : 'Letter Email',
                        'description' => isset($row['email_body']) ? (string)$row['email_body'] : '',
                        'status' => 'Published',
                        'type' => $typeCode
                    )), array('validate' => false, 'callbacks' => false));
                }
            }
            catch(Throwable $e)
            {
                // Notification template ensure should never block flow.
            }
        }
    }

    protected function _autoLetterNotificationTypeCode($letterType)
    {
        $letterType = trim((string)$letterType);
        $map = (array)$this->_autoLetterNotificationTypeMap();
        if(isset($map[$letterType]))
        {
            return $map[$letterType];
        }
        return $this->_autoLetterNotificationFallbackType();
    }

    protected function _autoLetterNotificationRow($letterId)
    {
        $letterId = (int)$letterId;
        if($letterId <= 0)
        {
            return array();
        }
        $this->loadModel('Admin.Realestateletter');
        return (array)$this->Realestateletter->find('first', array(
            'fields' => array(
                'Realestateletter.id','Realestateletter.letter_type','Realestateletter.reference_no','Realestateletter.title','Realestateletter.status',
                'Deal.id','Deal.invoice_no',
                'Client.name','Client.mobile','Client.phone','Client.email',
                'Property.name',
                'Project.name'
            ),
            'joins' => array(
                array('table' => 'deals', 'alias' => 'Deal', 'type' => 'LEFT', 'conditions' => array('Deal.id=Realestateletter.deal_id')),
                array('table' => 'clients', 'alias' => 'Client', 'type' => 'LEFT', 'conditions' => array('Client.id=Realestateletter.client_id')),
                array('table' => 'properties', 'alias' => 'Property', 'type' => 'LEFT', 'conditions' => array('Property.id=Deal.property_id')),
                array('table' => 'projects', 'alias' => 'Project', 'type' => 'LEFT', 'conditions' => array('Project.id=Property.project_id'))
            ),
            'conditions' => array('Realestateletter.id' => $letterId),
            'recursive' => -1
        ));
    }

    protected function _autoLetterNotificationTokens($row)
    {
        $letter = isset($row['Realestateletter']) ? (array)$row['Realestateletter'] : array();
        $deal = isset($row['Deal']) ? (array)$row['Deal'] : array();
        $client = isset($row['Client']) ? (array)$row['Client'] : array();
        $property = isset($row['Property']) ? (array)$row['Property'] : array();
        $project = isset($row['Project']) ? (array)$row['Project'] : array();
        $letterTypeCode = isset($letter['letter_type']) ? trim((string)$letter['letter_type']) : '';
        $letterTypeLabel = $letterTypeCode;
        if($letterTypeCode === 'RLT_WELCOME')
        {
            $letterTypeLabel = 'Welcome Letter';
        }
        elseif($letterTypeCode === 'RLT_BOOKING')
        {
            $letterTypeLabel = 'Booking Letter';
        }
        $letterId = isset($letter['id']) ? (int)$letter['id'] : 0;
        $url = rtrim((string)$this->siteDomain, '/');
        if($letterId > 0)
        {
            $url .= '/admin/Realestateletters/printletter/'.$letterId;
        }
        return array(
            '$siteName' => (string)$this->siteName,
            '$contactNo' => (string)$this->contact,
            '$url' => $url,
            '$clientName' => isset($client['name']) ? (string)$client['name'] : '',
            '$mobileNo' => isset($client['mobile']) && trim((string)$client['mobile']) !== '' ? trim((string)$client['mobile']) : trim((string)(isset($client['phone']) ? $client['phone'] : '')),
            '$email' => isset($client['email']) ? (string)$client['email'] : '',
            '$referenceNo' => isset($letter['reference_no']) ? (string)$letter['reference_no'] : '',
            '$letterNo' => isset($letter['reference_no']) ? (string)$letter['reference_no'] : '',
            '$invoiceNo' => isset($deal['invoice_no']) ? (string)$deal['invoice_no'] : '',
            '$propertyName' => isset($property['name']) ? (string)$property['name'] : '',
            '$projectName' => isset($project['name']) ? (string)$project['name'] : '',
            '$letterType' => $letterTypeLabel,
            '$letterTitle' => isset($letter['title']) ? (string)$letter['title'] : $letterTypeLabel
        );
    }

    protected function _renderAutoLetterNotificationMessage($body, $row)
    {
        return strtr((string)$body, $this->_autoLetterNotificationTokens($row));
    }

    protected function _sendAutoLetterSmsNotification($row)
    {
        if(empty($this->smsNotification) || empty($this->smsSettingArr['Smssetting']['api']))
        {
            return;
        }
        $mobile = trim((string)(isset($row['Client']['mobile']) ? $row['Client']['mobile'] : ''));
        if($mobile === '')
        {
            $mobile = trim((string)(isset($row['Client']['phone']) ? $row['Client']['phone'] : ''));
        }
        $mobile = preg_replace('/\s+/', '', $mobile);
        if($mobile === '')
        {
            return;
        }
        $letterType = isset($row['Realestateletter']['letter_type']) ? $row['Realestateletter']['letter_type'] : '';
        $typeCode = $this->_autoLetterNotificationTypeCode($letterType);
        $template = $this->Smstemplate->find('first', array(
            'fields' => array('Smstemplate.id','Smstemplate.description'),
            'conditions' => array('Smstemplate.type' => $typeCode, 'Smstemplate.status' => 'Published'),
            'recursive' => -1
        ));
        if(empty($template['Smstemplate']['id']) && $typeCode !== $this->_autoLetterNotificationFallbackType())
        {
            $template = $this->Smstemplate->find('first', array(
                'fields' => array('Smstemplate.id','Smstemplate.description'),
                'conditions' => array('Smstemplate.type' => $this->_autoLetterNotificationFallbackType(), 'Smstemplate.status' => 'Published'),
                'recursive' => -1
            ));
        }
        if(empty($template['Smstemplate']['id']))
        {
            return;
        }
        $message = $this->_renderAutoLetterNotificationMessage($template['Smstemplate']['description'], $row);
        if(trim($message) === '')
        {
            return;
        }
        try
        {
            $this->CustomFunction->sendSms($mobile, $message, $this->smsSettingArr);
        }
        catch(Throwable $e)
        {
            // Keep flow running.
        }
    }

    protected function _sendAutoLetterEmailNotification($row)
    {
        if(empty($this->emailNotification))
        {
            return;
        }
        $email = trim((string)(isset($row['Client']['email']) ? $row['Client']['email'] : ''));
        if($email === '')
        {
            return;
        }
        $letterType = isset($row['Realestateletter']['letter_type']) ? $row['Realestateletter']['letter_type'] : '';
        $typeCode = $this->_autoLetterNotificationTypeCode($letterType);
        $template = $this->Emailtemplate->find('first', array(
            'fields' => array('Emailtemplate.id','Emailtemplate.name','Emailtemplate.description'),
            'conditions' => array('Emailtemplate.type' => $typeCode, 'Emailtemplate.status' => 'Published'),
            'recursive' => -1
        ));
        if(empty($template['Emailtemplate']['id']) && $typeCode !== $this->_autoLetterNotificationFallbackType())
        {
            $template = $this->Emailtemplate->find('first', array(
                'fields' => array('Emailtemplate.id','Emailtemplate.name','Emailtemplate.description'),
                'conditions' => array('Emailtemplate.type' => $this->_autoLetterNotificationFallbackType(), 'Emailtemplate.status' => 'Published'),
                'recursive' => -1
            ));
        }
        if(empty($template['Emailtemplate']['id']))
        {
            return;
        }
        $message = $this->_renderAutoLetterNotificationMessage($template['Emailtemplate']['description'], $row);
        if(trim($message) === '')
        {
            return;
        }
        $subject = trim((string)(isset($template['Emailtemplate']['name']) ? $template['Emailtemplate']['name'] : 'Letter Notification'));
        if($subject === '')
        {
            $subject = 'Letter Notification';
        }
        try
        {
            App::uses('CakeEmail', 'Network/Email');
            $Email = new CakeEmail();
            $Email->transport($this->emailSettype);
            if($this->emailSettype=="Smtp" || $this->emailSettype=="SMTP")
            {
                $Email->config(array('host' => $this->emailHost,'port' =>  $this->emailPort,'username' => $this->emailUsername,'password' => $this->emailPassword));
            }
            $Email->from(array($this->siteEmail =>$this->siteName));
            $Email->to($email);
            $Email->template('default');
            $Email->emailFormat('html');
            $Email->subject($subject);
            $Email->send($message);
        }
        catch(Throwable $e)
        {
            // Keep flow running.
        }
    }

    protected function _dispatchAutoLetterNotificationsById($letterId)
    {
        $letterId = (int)$letterId;
        if($letterId <= 0)
        {
            return;
        }
        try
        {
            $this->_ensureAutoLetterNotificationTemplates();
            $this->loadModel('Smstemplate');
            $this->loadModel('Emailtemplate');
            $row = $this->_autoLetterNotificationRow($letterId);
            if(empty($row['Realestateletter']['id']))
            {
                return;
            }
            $status = isset($row['Realestateletter']['status']) ? trim((string)$row['Realestateletter']['status']) : '';
            if($status !== 'Published')
            {
                return;
            }
            $this->_sendAutoLetterEmailNotification($row);
            $this->_sendAutoLetterSmsNotification($row);
        }
        catch(Throwable $e)
        {
            // Keep flow running.
        }
    }

    public function _createAutoDealLetter($dealId, $projectId, $userId, $letterType, $dispatchNotifications = true)
    {
        $dealId = (int)$dealId;
        $projectId = (int)$projectId;
        $userId = (int)$userId;
        $letterType = trim((string)$letterType);
        if($dealId <= 0 || $letterType === '')
        {
            return false;
        }

        $this->loadModel('Deal');
        $this->loadModel('Admin.Realestateletter');

        $deal = $this->Deal->find('first', array(
            'fields' => array('Deal.id', 'Deal.client_id', 'Deal.invoice_no'),
            'conditions' => array('Deal.id' => $dealId),
            'recursive' => -1
        ));
        if(empty($deal['Deal']['id']))
        {
            return false;
        }

        $template = $this->_ensureAutoLetterTemplate($letterType);
        $config = $this->_autoLetterTypeConfig($letterType);
        $titleBase = isset($config['title']) ? trim((string)$config['title']) : 'Real Estate Letter';
        if($titleBase === '')
        {
            $titleBase = 'Real Estate Letter';
        }
        $invoiceNo = isset($deal['Deal']['invoice_no']) ? trim((string)$deal['Deal']['invoice_no']) : '';
        $title = $titleBase;
        if($invoiceNo !== '')
        {
            $title .= ' - ' . $invoiceNo;
        }

        $existing = $this->Realestateletter->find('first', array(
            'fields' => array('Realestateletter.id', 'Realestateletter.client_id', 'Realestateletter.title'),
            'conditions' => array(
                'Realestateletter.deal_id' => $dealId,
                'Realestateletter.letter_type' => $letterType
            ),
            'recursive' => -1
        ));
        if(!empty($existing['Realestateletter']['id']))
        {
            $needsUpdate = false;
            $updateRow = array('id' => (int)$existing['Realestateletter']['id']);
            if((int)$existing['Realestateletter']['client_id'] !== (int)$deal['Deal']['client_id'])
            {
                $updateRow['client_id'] = (int)$deal['Deal']['client_id'];
                $needsUpdate = true;
            }
            if(trim((string)$existing['Realestateletter']['title']) !== $title)
            {
                $updateRow['title'] = $title;
                $needsUpdate = true;
            }
            if($needsUpdate)
            {
                $this->Realestateletter->save(array('Realestateletter' => $updateRow), false);
            }
            return true;
        }
        $description = isset($template['description']) ? (string)$template['description'] : '';
        if(trim($description) === '')
        {
            $description = isset($config['description']) ? (string)$config['description'] : '';
        }
        $referenceNo = $this->_nextAutoLetterReferenceNo($projectId, $letterType);

        $saveRow = array(
            'deal_id' => (int)$deal['Deal']['id'],
            'client_id' => isset($deal['Deal']['client_id']) ? (int)$deal['Deal']['client_id'] : null,
            'template_id' => !empty($template['id']) ? (int)$template['id'] : null,
            'user_id' => $userId > 0 ? $userId : 1,
            'letter_type' => $letterType,
            'reference_no' => $referenceNo,
            'title' => $title,
            'letter_date' => date('Y-m-d'),
            'description' => $description,
            'status' => 'Published'
        );
        $this->Realestateletter->create();
        $saved = false;
        $saveFieldList = array(
            'id',
            'deal_id',
            'client_id',
            'template_id',
            'user_id',
            'letter_type',
            'reference_no',
            'title',
            'letter_date',
            'description',
            'status'
        );
        try
        {
            $saved = (bool)$this->Realestateletter->save(array('Realestateletter' => $saveRow), array(
                'validate' => false,
                'fieldList' => $saveFieldList
            ));
        }
        catch (Throwable $eLetterSave)
        {
            try
            {
                $maxRow = $this->Realestateletter->find('first', array(
                    'fields' => array('MAX(Realestateletter.id) AS max_id'),
                    'recursive' => -1
                ));
                $nextId = (!empty($maxRow[0]['max_id']) ? (int)$maxRow[0]['max_id'] : 0) + 1;
                $saveRow['id'] = $nextId;
                $this->Realestateletter->create();
                $saved = (bool)$this->Realestateletter->save(array('Realestateletter' => $saveRow), array(
                    'validate' => false,
                    'fieldList' => $saveFieldList
                ));
            }
            catch (Throwable $eRetry)
            {
                CakeLog::write('error', 'Auto real estate letter retry failed for deal '.$dealId.' type '.$letterType.': '.$eRetry->getMessage());
            }
        }
        if($saved)
        {
            $savedLetterId = (int)$this->Realestateletter->id;
            if($savedLetterId > 0 && $dispatchNotifications)
            {
                $this->_dispatchAutoLetterNotificationsById($savedLetterId);
            }
        }
        else
        {
            CakeLog::write('error', 'Auto real estate letter save failed for deal '.$dealId.' type '.$letterType.': '.json_encode($this->Realestateletter->validationErrors));
        }
        return $saved;
    }

    public function _autoCreateLettersForNewDeal($dealId, $projectId, $userId)
    {
        $dealId = (int)$dealId;
        if($dealId <= 0)
        {
            return;
        }
        try
        {
            if(!$this->_ensureRealestateLettersStorage())
            {
                return;
            }
            $this->_ensureAutoLetterReferenceSettingsColumns();
            $this->_ensureAutoLetterNotificationTemplates();
            $this->_createAutoDealLetter($dealId, $projectId, $userId, 'RLT_WELCOME', true);
            $this->_createAutoDealLetter($dealId, $projectId, $userId, 'RLT_BOOKING', true);
        }
        catch(Throwable $e)
        {
            // Auto letters should never block deal flow.
        }
    }

    public function _autoPatchMissingLettersForDeals($limit = 50)
    {
        static $hasRun = false;
        if($hasRun)
        {
            return;
        }
        $hasRun = true;
        $lastChecked = (int)$this->Session->read('LetterPatch.last_checked');
        $now = time();
        if ($lastChecked > 0 && ($now - $lastChecked) < 1800)
        {
            return;
        }
        $this->Session->write('LetterPatch.last_checked', $now);
        try
        {
            if(!$this->_ensureRealestateLettersStorage())
            {
                return;
            }
            $this->loadModel('Deal');
            $this->loadModel('Admin.Realestateletter');

            $deals = $this->Deal->find('all', array(
                'fields' => array('Deal.id', 'Deal.user_id', 'Deal.client_id', 'Property.project_id'),
                'joins' => array(
                    array(
                        'table' => 'properties',
                        'alias' => 'Property',
                        'type' => 'LEFT',
                        'conditions' => array('Property.id = Deal.property_id')
                    )
                ),
                'order' => array('Deal.id' => 'DESC'),
                'limit' => (int)$limit,
                'recursive' => -1
            ));

            if(empty($deals))
            {
                return;
            }

            $dealIds = Hash::extract($deals, '{n}.Deal.id');
            $existingLetters = $this->Realestateletter->find('all', array(
                'fields' => array('Realestateletter.deal_id', 'Realestateletter.letter_type'),
                'conditions' => array(
                    'Realestateletter.deal_id' => $dealIds,
                    'Realestateletter.letter_type' => array('RLT_WELCOME', 'RLT_BOOKING')
                ),
                'recursive' => -1
            ));

            $letterMap = array();
            foreach($existingLetters as $el)
            {
                $dId = (int)$el['Realestateletter']['deal_id'];
                $lType = $el['Realestateletter']['letter_type'];
                $letterMap[$dId][$lType] = true;
            }

            foreach($deals as $d)
            {
                try
                {
                    $dId = (int)$d['Deal']['id'];
                    $pId = !empty($d['Property']['project_id']) ? (int)$d['Property']['project_id'] : 0;
                    $uId = !empty($d['Deal']['user_id']) ? (int)$d['Deal']['user_id'] : (!empty($this->luserId) ? (int)$this->luserId : 1);

                    $hasWelcome = !empty($letterMap[$dId]['RLT_WELCOME']);
                    $hasBooking = !empty($letterMap[$dId]['RLT_BOOKING']);

                    if(!$hasWelcome || !$hasBooking)
                    {
                        $this->_ensureAutoLetterReferenceSettingsColumns();
                        $this->_ensureAutoLetterNotificationTemplates();
                        if(!$hasWelcome)
                        {
                            $this->_createAutoDealLetter($dId, $pId, $uId, 'RLT_WELCOME', false);
                        }
                        if(!$hasBooking)
                        {
                            $this->_createAutoDealLetter($dId, $pId, $uId, 'RLT_BOOKING', false);
                        }
                    }
                }
                catch(Throwable $eDeal)
                {
                    // Single deal failure in loop must never stop the sweep.
                }
            }
        }
        catch(Throwable $e)
        {
            // Auto letters patch should never block any request.
        }
    }
}



