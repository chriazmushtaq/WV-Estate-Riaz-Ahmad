<?php
App::uses('AdminAppController', 'Admin.Controller');

class NotificationsController extends AdminAppController
{
    public $uses = array(
        'Admin.UserNotification',
        'Admin.NotificationBroadcast',
        'Admin.NotificationSetting',
        'User',
        'Ugroup'
    );

    public $components = array('Paginator', 'Session');

    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->_ensureNotificationSchema();
        $this->_ensureNotificationsMenu();
    }

    protected function _isSuperAdmin()
    {
        if (!empty($this->isSuperAdminSession)) {
            return true;
        }
        $sessionUser = (array)$this->Session->read('User');
        if ($this->_isSuperAdminSession($sessionUser)) {
            return true;
        }
        if (!empty($this->adminValue) && $this->_isSuperAdminSession($this->adminValue)) {
            return true;
        }
        $ugroupId = !empty($sessionUser['User']['ugroup_id']) ? (int)$sessionUser['User']['ugroup_id'] : (!empty($this->adminValue['User']['ugroup_id']) ? (int)$this->adminValue['User']['ugroup_id'] : 0);
        return ($ugroupId === 1);
    }

    protected function _requireSuperAdmin()
    {
        if (php_sapi_name() === 'cli' || defined('STDIN')) {
            return true;
        }
        if (!$this->_isSuperAdmin()) {
            $this->Session->setFlash(__('Access denied. Super Admin privileges required.'), 'flash', array('alert' => 'danger'));
            $this->redirect(array('controller' => 'Notifications', 'action' => 'index'), null, false);
            return false;
        }
        return true;
    }

    public function index()
    {
        $userId = (int)$this->luserId;
        if ($userId <= 0) {
            $this->Session->setFlash(__('Please sign in to view notifications.'), 'flash', array('alert' => 'warning'));
            $this->redirect(array('controller' => 'Users', 'action' => 'login'), null, false);
            return;
        }

        $tab = isset($this->request->query['tab']) ? strtolower(trim((string)$this->request->query['tab'])) : 'all';
        $conditions = array('UserNotification.user_id' => $userId);

        if ($tab === 'unread') {
            $conditions['UserNotification.is_read'] = 0;
        } elseif ($tab === 'leads') {
            $conditions['UserNotification.type LIKE'] = '%lead%';
        } elseif ($tab === 'followup') {
            $conditions['UserNotification.type LIKE'] = '%followup%';
        } elseif ($tab === 'finance') {
            $conditions['OR'] = array(
                array('UserNotification.type LIKE' => '%deal%'),
                array('UserNotification.type LIKE' => '%payment%'),
                array('UserNotification.type LIKE' => '%expense%')
            );
        } elseif ($tab === 'broadcast') {
            $conditions['UserNotification.type'] = 'broadcast';
        }

        $searchQuery = isset($this->request->query['q']) ? trim((string)$this->request->query['q']) : '';
        if ($searchQuery !== '') {
            $conditions['OR'] = array(
                'UserNotification.title LIKE' => '%' . $searchQuery . '%',
                'UserNotification.message LIKE' => '%' . $searchQuery . '%'
            );
        }

        $this->Paginator->settings = array(
            'conditions' => $conditions,
            'order' => array('UserNotification.id' => 'DESC'),
            'limit' => 25,
            'recursive' => -1
        );

        $notifications = array();
        $unreadCount = 0;
        $totalCount = 0;
        try {
            $notifications = $this->Paginator->paginate('UserNotification');
            $unreadCount = $this->UserNotification->getUnreadCount($userId);
            $totalCount = (int)$this->UserNotification->find('count', array('conditions' => array('UserNotification.user_id' => $userId), 'recursive' => -1));
        } catch (Exception $e) {
            $this->_ensureNotificationSchema();
            try {
                $notifications = $this->Paginator->paginate('UserNotification');
                $unreadCount = $this->UserNotification->getUnreadCount($userId);
                $totalCount = (int)$this->UserNotification->find('count', array('conditions' => array('UserNotification.user_id' => $userId), 'recursive' => -1));
            } catch (Exception $ex) {
                $notifications = array();
                $unreadCount = 0;
                $totalCount = 0;
            }
        }

        $this->set(compact('notifications', 'tab', 'searchQuery', 'unreadCount', 'totalCount'));
        $this->set('isSuperAdmin', $this->_isSuperAdmin());
    }

    public function poll()
    {
        $this->autoRender = false;
        $this->response->type('json');

        $userId = (int)$this->luserId;
        if ($userId <= 0) {
            echo json_encode(array('status' => 'unauthenticated', 'unread_count' => 0, 'new_items' => array()));
            return;
        }

        $lastId = isset($this->request->query['last_id']) ? (int)$this->request->query['last_id'] : 0;
        $limit = isset($this->request->query['limit']) ? min(30, max(1, (int)$this->request->query['limit'])) : 10;

        $unreadCount = 0;
        $rows = array();
        try {
            $unreadCount = $this->UserNotification->getUnreadCount($userId);

            $cond = array('UserNotification.user_id' => $userId);
            if ($lastId > 0) {
                $cond['UserNotification.id >'] = $lastId;
            }

            $rows = (array)$this->UserNotification->find('all', array(
                'conditions' => $cond,
                'order' => array('UserNotification.id' => 'DESC'),
                'limit' => $limit,
                'recursive' => -1
            ));
        } catch (Exception $e) {
            $this->_ensureNotificationSchema();
            try {
                $unreadCount = $this->UserNotification->getUnreadCount($userId);
                $cond = array('UserNotification.user_id' => $userId);
                if ($lastId > 0) {
                    $cond['UserNotification.id >'] = $lastId;
                }
                $rows = (array)$this->UserNotification->find('all', array(
                    'conditions' => $cond,
                    'order' => array('UserNotification.id' => 'DESC'),
                    'limit' => $limit,
                    'recursive' => -1
                ));
            } catch (Exception $ex) {
                $rows = array();
                $unreadCount = 0;
            }
        }

        $newItems = array();
        foreach ($rows as $row) {
            $item = $row['UserNotification'];
            $item['time_ago'] = $this->_timeAgo($item['created']);
            $item['url'] = !empty($item['action_url']) ? Router::url($item['action_url']) : 'javascript:void(0);';
            $newItems[] = $item;
        }

        $soundEnabled = $this->NotificationSetting->isEnabled('enable_sound_chime', true);
        $browserPushEnabled = $this->NotificationSetting->isEnabled('enable_browser_push', true);

        echo json_encode(array(
            'status' => 'success',
            'user_id' => $userId,
            'unread_count' => $unreadCount,
            'new_count' => count($newItems),
            'new_items' => $newItems,
            'sound_enabled' => $soundEnabled,
            'browser_push_enabled' => $browserPushEnabled
        ));
    }

    public function mark_read($id = null)
    {
        $this->autoRender = false;
        $this->response->type('json');

        $userId = (int)$this->luserId;
        $id = $id !== null ? (int)$id : (isset($this->request->data['id']) ? (int)$this->request->data['id'] : 0);

        if ($userId <= 0 || $id <= 0) {
            echo json_encode(array('status' => 'error', 'message' => __('Invalid request')));
            return;
        }

        $success = $this->UserNotification->markAsRead($id, $userId);
        $unreadCount = $this->UserNotification->getUnreadCount($userId);

        echo json_encode(array(
            'status' => $success ? 'success' : 'error',
            'unread_count' => $unreadCount
        ));
    }

    public function mark_all_read()
    {
        $userId = (int)$this->luserId;
        if ($userId > 0) {
            $this->UserNotification->markAllAsRead($userId);
        }

        if ($this->request->is('ajax')) {
            $this->autoRender = false;
            $this->response->type('json');
            echo json_encode(array('status' => 'success', 'unread_count' => 0));
            return;
        }

        $this->Session->setFlash(__('All notifications marked as read.'), 'flash', array('alert' => 'success'));
        $this->redirect(array('action' => 'index'), null, false);
    }

    public function broadcast()
    {
        if (!$this->_requireSuperAdmin()) {
            return;
        }

        if ($this->request->is('post')) {
            $targetType = isset($this->request->data['target_type']) ? trim((string)$this->request->data['target_type']) : 'all';
            $selectedRoles = isset($this->request->data['roles']) ? (array)$this->request->data['roles'] : array();
            $selectedUsers = isset($this->request->data['users']) ? (array)$this->request->data['users'] : array();
            $title = trim((string)(isset($this->request->data['title']) ? $this->request->data['title'] : ''));
            $message = trim((string)(isset($this->request->data['message']) ? $this->request->data['message'] : ''));
            $priority = isset($this->request->data['priority']) ? trim((string)$this->request->data['priority']) : 'normal';
            $icon = isset($this->request->data['icon']) && trim((string)$this->request->data['icon']) !== '' ? trim((string)$this->request->data['icon']) : 'fa-bullhorn';
            $actionUrl = isset($this->request->data['action_url']) ? trim((string)$this->request->data['action_url']) : '';

            if ($title === '' || $message === '') {
                $this->Session->setFlash(__('Please provide both a Title and Message for the broadcast.'), 'flash', array('alert' => 'danger'));
            } else {
                $recipientUserIds = array();

                if ($targetType === 'all') {
                    $recipientUserIds = (array)$this->User->find('list', array(
                        'fields' => array('User.id'),
                        'conditions' => array('User.status' => 'Active'),
                        'recursive' => -1
                    ));
                } elseif ($targetType === 'roles' && !empty($selectedRoles)) {
                    $selectedRoles = array_map('intval', $selectedRoles);
                    $recipientUserIds = (array)$this->User->find('list', array(
                        'fields' => array('User.id'),
                        'conditions' => array('User.ugroup_id' => $selectedRoles, 'User.status' => 'Active'),
                        'recursive' => -1
                    ));
                } elseif ($targetType === 'users' && !empty($selectedUsers)) {
                    $recipientUserIds = array_map('intval', $selectedUsers);
                }

                $recipientUserIds = array_values(array_unique(array_filter($recipientUserIds)));

                if (empty($recipientUserIds)) {
                    $this->Session->setFlash(__('No active recipients found for the selected target audience.'), 'flash', array('alert' => 'warning'));
                } else {
                    // Record broadcast
                    $this->NotificationBroadcast->create();
                    $targetPayload = ($targetType === 'roles') ? json_encode($selectedRoles) : (($targetType === 'users') ? json_encode($selectedUsers) : 'all');
                    $this->NotificationBroadcast->save(array(
                        'sender_id' => (int)$this->luserId,
                        'target_type' => $targetType,
                        'target_payload' => $targetPayload,
                        'title' => $title,
                        'message' => $message,
                        'type' => 'broadcast',
                        'priority' => $priority,
                        'icon' => $icon,
                        'action_url' => $actionUrl,
                        'total_recipients' => count($recipientUserIds),
                        'created' => date('Y-m-d H:i:s')
                    ));
                    $broadcastId = $this->NotificationBroadcast->id;

                    // Bulk insert notifications
                    $now = date('Y-m-d H:i:s');
                    $insertRows = array();
                    foreach ($recipientUserIds as $uid) {
                        $dedup = 'bcast_' . $broadcastId . '_' . $uid;
                        $insertRows[] = array(
                            'user_id' => $uid,
                            'notif_key' => $dedup,
                            'title' => $title,
                            'message' => $message,
                            'type' => 'broadcast',
                            'priority' => $priority,
                            'icon' => $icon,
                            'action_url' => $actionUrl,
                            'is_read' => 0,
                            'created' => $now
                        );
                    }

                    $this->UserNotification->saveMany($insertRows, array('validate' => false));

                    $this->Session->setFlash(sprintf(__('Custom broadcast successfully delivered to %d user(s).'), count($recipientUserIds)), 'flash', array('alert' => 'success'));
                    return $this->redirect(array('action' => 'broadcast_history'), null, false);
                }
            }
        }

        // Prepare lists for dropdowns
        $roles = (array)$this->Ugroup->find('list', array(
            'fields' => array('Ugroup.id', 'Ugroup.name'),
            'order' => array('Ugroup.name' => 'ASC'),
            'recursive' => -1
        ));

        $users = (array)$this->User->find('all', array(
            'fields' => array('User.id', 'User.name', 'Ugroup.name'),
            'joins' => array(
                array('table' => 'ugroups', 'alias' => 'Ugroup', 'type' => 'LEFT', 'conditions' => array('User.ugroup_id=Ugroup.id'))
            ),
            'conditions' => array('User.status' => 'Active'),
            'order' => array('User.name' => 'ASC'),
            'recursive' => -1
        ));
        $userOptions = array();
        foreach ($users as $u) {
            $gName = !empty($u['Ugroup']['name']) ? $u['Ugroup']['name'] : __('User');
            $userOptions[$u['User']['id']] = $u['User']['name'] . ' (' . $gName . ')';
        }

        $this->set(compact('roles', 'userOptions'));
    }

    public function broadcast_history()
    {
        if (!$this->_requireSuperAdmin()) {
            return;
        }

        $this->Paginator->settings = array(
            'order' => array('NotificationBroadcast.id' => 'DESC'),
            'limit' => 20,
            'recursive' => 1
        );
        $broadcasts = array();
        try {
            $broadcasts = $this->Paginator->paginate('NotificationBroadcast');
        } catch (Exception $e) {
            $this->_ensureNotificationSchema();
            try {
                $broadcasts = $this->Paginator->paginate('NotificationBroadcast');
            } catch (Exception $ex) {
                $broadcasts = array();
            }
        }
        $this->set(compact('broadcasts'));
    }

    public function settings()
    {
        if (!$this->_requireSuperAdmin()) {
            return;
        }

        $this->_ensureNotificationSchema();

        if ($this->request->is('post')) {
            $settingsData = isset($this->request->data['NotificationSetting']) ? (array)$this->request->data['NotificationSetting'] : array();

            // All toggle keys
            $toggleKeys = array(
                'enable_lead_assignment',
                'enable_facebook_sync',
                'enable_followup_reminder',
                'enable_overdue_alert',
                'enable_deal_closed',
                'enable_expense_approval',
                'enable_payment_received',
                'whatsapp_demand_pdf_attachment',
                'whatsapp_receipt_pdf_attachment',
                'enable_admin_broadcast',
                'enable_sound_chime',
                'enable_browser_push'
            );

            $payload = array();
            foreach ($toggleKeys as $tk) {
                // If checkbox was unchecked, it will not exist in POST, so default to '0'
                $payload[$tk] = !empty($settingsData[$tk]) ? '1' : '0';
            }

            if (isset($settingsData['followup_reminder_minutes'])) {
                $payload['followup_reminder_minutes'] = max(5, min(1440, (int)$settingsData['followup_reminder_minutes']));
            }

            // Sync WhatsApp demand PDF and receipt PDF toggles to configurations table as well
            try {
                $ConfigModel = ClassRegistry::init('Admin.Configuration');
                if ($ConfigModel) {
                    $configUpdate = array(
                        'Configuration.whatsapp_demand_pdf' => (int)$payload['whatsapp_demand_pdf_attachment']
                    );
                    if ($ConfigModel->hasField('whatsapp_receipt_pdf')) {
                        $configUpdate['Configuration.whatsapp_receipt_pdf'] = (int)$payload['whatsapp_receipt_pdf_attachment'];
                    }
                    $ConfigModel->updateAll(
                        $configUpdate,
                        array('1 = 1')
                    );
                }
            } catch (Exception $eConfig) {}

            try {
                $this->NotificationSetting->saveSettings($payload);
                $this->Session->setFlash(__('Notification system preferences have been updated successfully.'), 'flash', array('alert' => 'success'));
                return $this->redirect(array('action' => 'settings'), null, false);
            } catch (Exception $e) {
                $this->_ensureNotificationSchema();
                try {
                    $this->NotificationSetting->saveSettings($payload);
                    $this->Session->setFlash(__('Notification system preferences have been updated successfully.'), 'flash', array('alert' => 'success'));
                    return $this->redirect(array('action' => 'settings'), null, false);
                } catch (Exception $ex) {
                    $this->Session->setFlash(__('Error saving settings: ') . $ex->getMessage(), 'flash', array('alert' => 'danger'));
                }
            }
        }

        $allSettings = array();
        try {
            $allSettings = $this->NotificationSetting->getAllSettings(true);
        } catch (Exception $e) {
            $this->_ensureNotificationSchema();
            try {
                $allSettings = $this->NotificationSetting->getAllSettings(true);
            } catch (Exception $ex) {
                $allSettings = array();
            }
        }
        $this->set(compact('allSettings'));
    }

    public function cron_reminders()
    {
        $this->autoRender = false;
        $this->layout = false;
        $this->response->type('json');

        $now = date('Y-m-d H:i:s');
        $remindersCount = 0;
        $overdueCount = 0;

        // Check if followup reminders enabled
        if ($this->NotificationSetting->isEnabled('enable_followup_reminder', true)) {
            $this->loadModel('Lead');
            $this->loadModel('Admin.Status');

            // Find terminal status IDs (e.g. Won, Closed, Lost, Drop) to avoid reminding closed leads
            $closedStatuses = (array)$this->Status->find('list', array(
                'fields' => array('Status.id'),
                'conditions' => array(
                    'OR' => array(
                        'Status.name LIKE' => '%won%',
                        'Status.name LIKE' => '%closed%',
                        'Status.name LIKE' => '%lost%',
                        'Status.name LIKE' => '%drop%'
                    )
                ),
                'recursive' => -1
            ));
            $closedStatusIds = array_values(array_filter(array_map('intval', $closedStatuses)));

            // 1. Upcoming followups within next 35 minutes
            $windowStart = date('Y-m-d H:i:s', strtotime('-5 minutes'));
            $windowEnd = date('Y-m-d H:i:s', strtotime('+35 minutes'));

            $leadConds = array(
                'Lead.user_id >' => 0,
                'Lead.follow_up >=' => $windowStart,
                'Lead.follow_up <=' => $windowEnd,
            );
            if (!empty($closedStatusIds)) {
                $leadConds['NOT'] = array('Lead.lead_status_id' => $closedStatusIds);
            }

            $upcomingLeads = $this->Lead->find('all', array(
                'conditions' => $leadConds,
                'fields' => array('Lead.id', 'Lead.lead_name', 'Lead.lead_mobile', 'Lead.user_id', 'Lead.follow_up'),
                'recursive' => -1,
                'limit' => 100
            ));

            foreach ($upcomingLeads as $l) {
                $leadId = (int)$l['Lead']['id'];
                $uid = (int)$l['Lead']['user_id'];
                $followTime = $l['Lead']['follow_up'];
                $leadName = !empty($l['Lead']['lead_name']) ? $l['Lead']['lead_name'] : ('Lead #' . $leadId);
                $mobile = !empty($l['Lead']['lead_mobile']) ? $l['Lead']['lead_mobile'] : '';
                $formattedTime = date('h:i A', strtotime($followTime));

                $dedupKey = 'followup_30m_' . $leadId . '_' . date('Ymd_H', strtotime($followTime));
                $title = 'Upcoming Follow-up: ' . $leadName;
                $msg = sprintf(__('Reminder: Follow-up scheduled for %s at %s (%s).'), $leadName, $formattedTime, $mobile);
                $actionUrl = '/admin/Leads/leadview/' . $leadId;

                $sent = $this->sendNotification($uid, $title, $msg, 'followup_reminder', $actionUrl, 'fa-clock-o', 'high', $dedupKey);
                if ($sent) {
                    $remindersCount++;
                }
            }

            // 2. Overdue follow-up check (if enable_overdue_alert is enabled)
            if ($this->NotificationSetting->isEnabled('enable_overdue_alert', true)) {
                $todayStart = date('Y-m-d 00:00:00');
                $overdueWindowEnd = date('Y-m-d H:i:s', strtotime('-15 minutes'));

                $overdueConds = array(
                    'Lead.user_id >' => 0,
                    'Lead.follow_up >=' => $todayStart,
                    'Lead.follow_up <=' => $overdueWindowEnd,
                );
                if (!empty($closedStatusIds)) {
                    $overdueConds['NOT'] = array('Lead.lead_status_id' => $closedStatusIds);
                }

                $overdueLeads = $this->Lead->find('all', array(
                    'conditions' => $overdueConds,
                    'fields' => array('Lead.id', 'Lead.lead_name', 'Lead.lead_mobile', 'Lead.user_id', 'Lead.follow_up'),
                    'recursive' => -1,
                    'limit' => 50
                ));

                foreach ($overdueLeads as $ol) {
                    $leadId = (int)$ol['Lead']['id'];
                    $uid = (int)$ol['Lead']['user_id'];
                    $followTime = $ol['Lead']['follow_up'];
                    $leadName = !empty($ol['Lead']['lead_name']) ? $ol['Lead']['lead_name'] : ('Lead #' . $leadId);
                    $formattedTime = date('h:i A', strtotime($followTime));

                    $dedupKey = 'followup_overdue_' . $leadId . '_' . date('Ymd');
                    $title = 'Overdue Follow-up: ' . $leadName;
                    $msg = sprintf(__('Follow-up for %s was due at %s today and is still pending.'), $leadName, $formattedTime);
                    $actionUrl = '/admin/Leads/leadview/' . $leadId;

                    $sent = $this->sendNotification($uid, $title, $msg, 'followup_reminder', $actionUrl, 'fa-exclamation-triangle', 'urgent', $dedupKey);
                    if ($sent) {
                        $overdueCount++;
                    }
                }
            }
        }

        // 3. Purge notifications older than 60 days
        $purgedCount = $this->UserNotification->purgeOldNotifications(60);

        // 4. Automated WhatsApp & Client Portal Installment Reminders (7-day & 1-day)
        $waReminders = array('sent_7d' => 0, 'sent_1d' => 0, 'in_portal_created' => 0);
        try {
            App::uses('WhatsAppAutomationComponent', 'Controller/Component');
            $waAuto = new WhatsAppAutomationComponent(new ComponentCollection());
            $waReminders = $waAuto->processCronReminders();
        } catch (Exception $eWa) {}

        $out = array(
            'status' => 'success',
            'timestamp' => $now,
            'upcoming_reminders_sent' => $remindersCount,
            'overdue_alerts_sent' => $overdueCount,
            'whatsapp_reminders_7d' => isset($waReminders['sent_7d']) ? $waReminders['sent_7d'] : 0,
            'whatsapp_reminders_1d' => isset($waReminders['sent_1d']) ? $waReminders['sent_1d'] : 0,
            'in_portal_client_reminders' => isset($waReminders['in_portal_created']) ? $waReminders['in_portal_created'] : 0,
            'purged_old_notifications' => $purgedCount
        );
        echo json_encode($out);
        return;
    }

    public function sync_existing_events()
    {
        if (!$this->_requireSuperAdmin()) {
            return;
        }

        $this->_ensureNotificationSchema();

        $scope = isset($this->request->query['scope']) ? trim($this->request->query['scope']) : 'all';
        $limit = isset($this->request->query['limit']) ? min(1000, max(10, (int)$this->request->query['limit'])) : 200;

        App::uses('ConnectionManager', 'Model');
        $db = ConnectionManager::getDataSource('default');
        $now = date('Y-m-d H:i:s');

        $stats = array(
            'leads_synced' => 0,
            'followups_synced' => 0,
            'deals_synced' => 0,
            'expenses_synced' => 0,
            'total_processed' => 0
        );

        $tables = $db->listSources();

        // 1. Leads
        if (($scope === 'all' || $scope === 'leads') && in_array('leads', $tables)) {
            try {
                $leads = (array)$db->query("SELECT id, user_id, lead_name, lead_source, created FROM `leads` WHERE `user_id` > 0 ORDER BY id DESC LIMIT {$limit}");
                foreach ($leads as $l) {
                    $row = isset($l['leads']) ? $l['leads'] : (isset($l[0]) ? $l[0] : array_values($l)[0]);
                    $leadId = (int)$row['id'];
                    $uid = (int)$row['user_id'];
                    $name = !empty($row['lead_name']) ? trim($row['lead_name']) : ('Lead #' . $leadId);
                    $src = !empty($row['lead_source']) ? ' (' . trim($row['lead_source']) . ')' : '';
                    $key = 'hist_lead_' . $leadId . '_' . $uid;
                    $created = !empty($row['created']) && $row['created'] !== '0000-00-00 00:00:00' ? $row['created'] : $now;

                    $title = addslashes(__('New Lead Assigned') . ': ' . $name);
                    $msg = addslashes(__('Lead #') . $leadId . __(' was assigned to your pipeline') . $src);
                    $actionUrl = addslashes('/admin/Leads/leadview/' . $leadId);

                    $sql = "INSERT IGNORE INTO `user_notifications` 
                            (`user_id`, `notif_key`, `title`, `message`, `type`, `priority`, `icon`, `action_url`, `is_read`, `created`) 
                            VALUES ({$uid}, '{$key}', '{$title}', '{$msg}', 'lead_assigned', 'normal', 'fa-user-plus', '{$actionUrl}', 0, '{$created}')";
                    $db->rawQuery($sql);
                    $stats['leads_synced']++;
                }
            } catch (Exception $e) {}
        }

        // 2. Follow-ups
        if (($scope === 'all' || $scope === 'followups') && in_array('leads', $tables)) {
            try {
                $followups = (array)$db->query("SELECT id, user_id, lead_name, follow_up, created FROM `leads` WHERE `user_id` > 0 AND `follow_up` IS NOT NULL AND `follow_up` != '0000-00-00 00:00:00' ORDER BY follow_up DESC LIMIT {$limit}");
                foreach ($followups as $f) {
                    $row = isset($f['leads']) ? $f['leads'] : (isset($f[0]) ? $f[0] : array_values($f)[0]);
                    $leadId = (int)$row['id'];
                    $uid = (int)$row['user_id'];
                    $name = !empty($row['lead_name']) ? trim($row['lead_name']) : ('Lead #' . $leadId);
                    $fuTime = $row['follow_up'];
                    $isOverdue = (strtotime($fuTime) < strtotime($now));
                    $key = ($isOverdue ? 'hist_fu_overdue_' : 'hist_fu_up_') . $leadId . '_' . date('Ymd', strtotime($fuTime));

                    $title = addslashes(($isOverdue ? __('⚠️ Overdue Follow-up') : __('Upcoming Follow-up')) . ': ' . $name);
                    $msg = addslashes(__('Scheduled for: ') . date('d M Y, h:i A', strtotime($fuTime)));
                    $type = $isOverdue ? 'followup_overdue' : 'followup_reminder';
                    $priority = $isOverdue ? 'high' : 'normal';
                    $icon = $isOverdue ? 'fa-clock-o' : 'fa-calendar-check-o';
                    $actionUrl = addslashes('/admin/Leads/leadview/' . $leadId);

                    $sql = "INSERT IGNORE INTO `user_notifications` 
                            (`user_id`, `notif_key`, `title`, `message`, `type`, `priority`, `icon`, `action_url`, `is_read`, `created`) 
                            VALUES ({$uid}, '{$key}', '{$title}', '{$msg}', '{$type}', '{$priority}', '{$icon}', '{$actionUrl}', 0, '{$now}')";
                    $db->rawQuery($sql);
                    $stats['followups_synced']++;
                }
            } catch (Exception $e) {}
        }

        // 3. Deals
        if (($scope === 'all' || $scope === 'deals') && in_array('deals', $tables)) {
            try {
                $deals = (array)$db->query("SELECT id, user_id, total_amount, deal_status, created FROM `deals` WHERE `user_id` > 0 ORDER BY id DESC LIMIT {$limit}");
                foreach ($deals as $d) {
                    $row = isset($d['deals']) ? $d['deals'] : (isset($d[0]) ? $d[0] : array_values($d)[0]);
                    $dealId = (int)$row['id'];
                    $uid = (int)$row['user_id'];
                    $status = !empty($row['deal_status']) ? trim($row['deal_status']) : 'Active';
                    $amount = (float)$row['total_amount'];
                    $key = 'hist_deal_' . $dealId . '_' . $uid;
                    $created = !empty($row['created']) && $row['created'] !== '0000-00-00 00:00:00' ? $row['created'] : $now;

                    $title = addslashes(__('Deal Recorded') . ' #' . $dealId . ' (' . $status . ')');
                    $msg = addslashes(__('Booking total: Rs. ') . number_format($amount, 2));
                    $actionUrl = addslashes('/admin/Deals');

                    $sql = "INSERT IGNORE INTO `user_notifications` 
                            (`user_id`, `notif_key`, `title`, `message`, `type`, `priority`, `icon`, `action_url`, `is_read`, `created`) 
                            VALUES ({$uid}, '{$key}', '{$title}', '{$msg}', 'deal_closed', 'high', 'fa-trophy', '{$actionUrl}', 0, '{$created}')";
                    $db->rawQuery($sql);
                    $stats['deals_synced']++;
                }
            } catch (Exception $e) {}
        }

        // 4. Expenses
        if (($scope === 'all' || $scope === 'expenses') && in_array('expenses', $tables)) {
            try {
                $expenses = (array)$db->query("SELECT id, user_id, amount, created FROM `expenses` ORDER BY id DESC LIMIT 50");
                foreach ($expenses as $ex) {
                    $row = isset($ex['expenses']) ? $ex['expenses'] : (isset($ex[0]) ? $ex[0] : array_values($ex)[0]);
                    $expId = (int)$row['id'];
                    $uid = !empty($row['user_id']) ? (int)$row['user_id'] : (int)$this->luserId;
                    $amount = (float)$row['amount'];
                    $key = 'hist_exp_' . $expId;
                    $created = !empty($row['created']) && $row['created'] !== '0000-00-00 00:00:00' ? $row['created'] : $now;

                    $title = addslashes(__('Expense Voucher') . ' #' . $expId);
                    $msg = addslashes(__('Voucher amount: Rs. ') . number_format($amount, 2));
                    $actionUrl = addslashes('/admin/Ardaccounts/expenseapprovals');

                    $sql = "INSERT IGNORE INTO `user_notifications` 
                            (`user_id`, `notif_key`, `title`, `message`, `type`, `priority`, `icon`, `action_url`, `is_read`, `created`) 
                            VALUES ({$uid}, '{$key}', '{$title}', '{$msg}', 'expense_approval', 'normal', 'fa-money', '{$actionUrl}', 0, '{$created}')";
                    $db->rawQuery($sql);
                    $stats['expenses_synced']++;
                }
            } catch (Exception $e) {}
        }

        $stats['total_processed'] = $stats['leads_synced'] + $stats['followups_synced'] + $stats['deals_synced'] + $stats['expenses_synced'];

        if ($this->request->is('ajax') || !empty($this->request->query['ajax'])) {
            $this->autoRender = false;
            $this->response->type('json');
            echo json_encode(array(
                'status' => 'success',
                'message' => sprintf(__('Successfully auto-synced %d historical event notifications across your users with 0 duplicates!'), $stats['total_processed']),
                'stats' => $stats
            ));
            return;
        }

        $this->Session->setFlash(
            sprintf(__('Successfully auto-synced %d historical event notifications (%d Leads, %d Follow-ups, %d Deals, %d Expenses) with zero duplicates!'),
                $stats['total_processed'], $stats['leads_synced'], $stats['followups_synced'], $stats['deals_synced'], $stats['expenses_synced']
            ),
            'flash',
            array('alert' => 'success')
        );
        return $this->redirect(array('action' => 'settings'), null, false);
    }

    protected function _timeAgo($datetime)
    {
        $time = strtotime($datetime);
        $diff = time() - $time;
        if ($diff < 60) {
            return __('Just now');
        } elseif ($diff < 3600) {
            return sprintf(__('%dm ago'), round($diff / 60));
        } elseif ($diff < 86400) {
            return sprintf(__('%dh ago'), round($diff / 3600));
        } elseif ($diff < 604800) {
            return sprintf(__('%dd ago'), round($diff / 86400));
        } else {
            return date('d M Y', $time);
        }
    }
}
