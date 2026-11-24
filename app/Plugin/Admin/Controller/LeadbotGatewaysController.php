<?php
App::uses('AdminAppController', 'Admin.Controller');
App::uses('LeadbotGatewayClient', 'Lib');

class LeadbotGatewaysController extends AdminAppController
{
    public $uses = array(
        'Admin.LeadbotGatewaySetting',
        'Admin.LeadbotEventSetting',
        'Admin.LeadbotNotificationLog',
        'Project',
        'User'
    );

    public $components = array('Paginator', 'Session', 'RequestHandler');

    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->_ensureLeadbotSchema();
    }

    protected function _isSuperAdmin()
    {
        if (!empty($this->isSuperAdminSession)) {
            return true;
        }
        $sessionUser = (array)$this->Session->read('User');
        if (method_exists($this, '_isSuperAdminSession') && $this->_isSuperAdminSession($sessionUser)) {
            return true;
        }
        if (!empty($this->adminValue) && method_exists($this, '_isSuperAdminSession') && $this->_isSuperAdminSession($this->adminValue)) {
            return true;
        }
        $ugroupId = !empty($sessionUser['User']['ugroup_id']) ? (int)$sessionUser['User']['ugroup_id'] : (!empty($this->adminValue['User']['ugroup_id']) ? (int)$this->adminValue['User']['ugroup_id'] : 0);
        return ($ugroupId === 1);
    }

    protected function _isAgencyAdmin()
    {
        if ($this->_isSuperAdmin()) {
            return true;
        }
        if ($this->userType === 'AGY' || $this->userType === 'ADR') {
            return true;
        }
        if (method_exists($this, '_isAgencyAdminSession') && $this->_isAgencyAdminSession((array)$this->adminValue)) {
            return true;
        }
        $sessionUser = (array)$this->Session->read('User');
        $ugroupId = !empty($sessionUser['User']['ugroup_id']) ? (int)$sessionUser['User']['ugroup_id'] : (!empty($this->adminValue['User']['ugroup_id']) ? (int)$this->adminValue['User']['ugroup_id'] : 0);
        return ($ugroupId === 4 || $ugroupId === 2);
    }

    protected function _canManageQueue()
    {
        return ($this->_isSuperAdmin() || $this->_isAgencyAdmin());
    }

    /**
     * Resolve target agency/project ID considering role isolation
     */
    protected function _resolveTargetAgencyId()
    {
        $isSuper = $this->_isSuperAdmin();

        if ($isSuper) {
            // Check POST data first
            if (isset($this->request->data['project_id']) && $this->request->data['project_id'] !== '') {
                return (int)$this->request->data['project_id'];
            }
            if (isset($this->request->data['LeadbotGatewaySetting']['project_id']) && $this->request->data['LeadbotGatewaySetting']['project_id'] !== '') {
                return (int)$this->request->data['LeadbotGatewaySetting']['project_id'];
            }
            if (isset($this->request->query['project_id']) && $this->request->query['project_id'] !== '') {
                return (int)$this->request->query['project_id'];
            }
            return 0; // Super Admin default: Global Platform
        }

        $agencyId = !empty($this->agencyId) ? (int)$this->agencyId : 0;
        if ($agencyId <= 0 && !empty($this->adminValue['User']['project_id'])) {
            $agencyId = (int)$this->adminValue['User']['project_id'];
        }
        return $agencyId;
    }

    /**
     * Resolve target agent/user ID (0 = agency-wide, >0 = specific agent)
     */
    protected function _resolveTargetUserId()
    {
        $isSuper = $this->_isSuperAdmin();
        $isAgencyManager = (!$isSuper && in_array((string)$this->userType, array('AGY', 'ADR'), true));

        // Normal agents only manage their own personal gateway
        if (!$isSuper && !$isAgencyManager) {
            return (int)$this->luserId;
        }

        $targetUserId = 0;
        // Check POST data first
        if (isset($this->request->data['user_id']) && $this->request->data['user_id'] !== '') {
            $targetUserId = (int)$this->request->data['user_id'];
        } elseif (isset($this->request->data['LeadbotGatewaySetting']['user_id']) && $this->request->data['LeadbotGatewaySetting']['user_id'] !== '') {
            $targetUserId = (int)$this->request->data['LeadbotGatewaySetting']['user_id'];
        } elseif (isset($this->request->query['user_id']) && $this->request->query['user_id'] !== '') {
            $targetUserId = (int)$this->request->query['user_id'];
        }

        // Agency Manager can only configure agents inside their agency
        if ($isAgencyManager && $targetUserId > 0) {
            $agencyId = !empty($this->agencyId) ? (int)$this->agencyId : (int)$this->adminValue['User']['project_id'];
            $chk = $this->User->find('first', array(
                'conditions' => array('User.id' => $targetUserId),
                'fields'     => array('User.id', 'User.project_id'),
                'recursive'  => -1
            ));
            if (empty($chk['User']['project_id']) || (int)$chk['User']['project_id'] !== $agencyId) {
                $targetUserId = 0;
            }
        }

        return $targetUserId;
    }

    /**
     * Ensure database tables & agent columns exist automatically
     */
    protected function _ensureLeadbotSchema()
    {
        static $done = false;
        if ($done) return true;
        $done = true;

        try {
            $db = ConnectionManager::getDataSource('default');
            if (!$db) return false;

            $db->query("
                CREATE TABLE IF NOT EXISTS `leadbot_gateway_settings` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `project_id` int(11) NOT NULL DEFAULT '0',
                    `user_id` int(11) NOT NULL DEFAULT '0',
                    `gateway_url` varchar(255) NOT NULL DEFAULT 'http://localhost/whatsapp',
                    `api_key` varchar(255) NOT NULL DEFAULT '',
                    `selected_wa_session` varchar(100) DEFAULT NULL,
                    `selected_wa_profile_name` varchar(150) DEFAULT NULL,
                    `selected_sms_device_id` int(11) DEFAULT NULL,
                    `selected_sms_sim_slot` tinyint(2) NOT NULL DEFAULT '1',
                    `selected_sms_device_name` varchar(150) DEFAULT NULL,
                    `auto_send_whatsapp` tinyint(1) NOT NULL DEFAULT '1',
                    `auto_send_sms` tinyint(1) NOT NULL DEFAULT '1',
                    `notify_agent` tinyint(1) NOT NULL DEFAULT '1',
                    `notify_client` tinyint(1) NOT NULL DEFAULT '0',
                    `admin_mobile` varchar(50) DEFAULT NULL,
                    `status` varchar(30) NOT NULL DEFAULT 'active',
                    `last_sync_at` datetime DEFAULT NULL,
                    `last_telemetry` longtext DEFAULT NULL,
                    `created` datetime DEFAULT NULL,
                    `modified` datetime DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `uniq_project_user` (`project_id`, `user_id`),
                    KEY `idx_user_id` (`user_id`),
                    KEY `idx_status` (`status`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");

            $db->query("
                CREATE TABLE IF NOT EXISTS `leadbot_event_settings` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `project_id` int(11) NOT NULL DEFAULT '0',
                    `user_id` int(11) NOT NULL DEFAULT '0',
                    `event_key` varchar(80) NOT NULL,
                    `event_label` varchar(150) NOT NULL,
                    `event_description` varchar(255) DEFAULT NULL,
                    `whatsapp_enabled` tinyint(1) NOT NULL DEFAULT '1',
                    `sms_enabled` tinyint(1) NOT NULL DEFAULT '0',
                    `whatsapp_template` text DEFAULT NULL,
                    `sms_template` text DEFAULT NULL,
                    `notify_agent` tinyint(1) NOT NULL DEFAULT '1',
                    `notify_client` tinyint(1) NOT NULL DEFAULT '0',
                    `notify_admin` tinyint(1) NOT NULL DEFAULT '0',
                    `created` datetime DEFAULT NULL,
                    `modified` datetime DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `uniq_project_user_event` (`project_id`, `user_id`, `event_key`),
                    KEY `idx_project_id` (`project_id`),
                    KEY `idx_user_id` (`user_id`),
                    KEY `idx_event_key` (`event_key`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");

            $db->query("
                CREATE TABLE IF NOT EXISTS `leadbot_notification_logs` (
                    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    `project_id` int(11) NOT NULL DEFAULT '0',
                    `user_id` int(11) DEFAULT NULL,
                    `channel` varchar(20) NOT NULL,
                    `event_key` varchar(80) NOT NULL,
                    `recipient_phone` varchar(50) NOT NULL,
                    `recipient_name` varchar(100) DEFAULT NULL,
                    `message` text NOT NULL,
                    `gateway_profile_or_device` varchar(100) DEFAULT NULL,
                    `status` varchar(30) NOT NULL DEFAULT 'queued',
                    `response_code` int(11) DEFAULT NULL,
                    `response_payload` text DEFAULT NULL,
                    `error_message` text DEFAULT NULL,
                    `is_test` tinyint(1) NOT NULL DEFAULT '0',
                    `created` datetime DEFAULT NULL,
                    `modified` datetime DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `idx_project_id` (`project_id`),
                    KEY `idx_user_id` (`user_id`),
                    KEY `idx_channel` (`channel`),
                    KEY `idx_event_key` (`event_key`),
                    KEY `idx_status` (`status`),
                    KEY `idx_created` (`created`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");

            try {
                $db->query("ALTER TABLE `leadbot_notification_logs` ADD COLUMN `modified` datetime DEFAULT NULL AFTER `created`");
            } catch (Exception $eMod) {}

            try {
                $db->query("ALTER TABLE `smssettings` ADD COLUMN `gateway_type` varchar(20) NOT NULL DEFAULT 'custom'");
            } catch (Exception $eCol1) {}

            try {
                $db->query("ALTER TABLE `smssettings` ADD COLUMN `is_leadbot_default` tinyint(1) NOT NULL DEFAULT 0");
            } catch (Exception $eCol2) {}

            try {
                $db->query("ALTER TABLE `leadbot_gateway_settings` ADD COLUMN `is_default_sms` tinyint(1) NOT NULL DEFAULT 0");
            } catch (Exception $eCol3) {}

            try {
                $db->query("ALTER TABLE `leadbot_gateway_settings` ADD COLUMN `is_default_whatsapp` tinyint(1) NOT NULL DEFAULT 1");
            } catch (Exception $eCol4) {}

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Main Gateway Settings & Live Telemetry Dashboard
     */
    public function index()
    {
        $isSuperAdmin = $this->_isSuperAdmin();
        $targetAgencyId = $this->_resolveTargetAgencyId();
        $targetUserId = $this->_resolveTargetUserId();

        // 1. Load Scope-specific Settings (Agent personal key if targetUserId > 0, else Agency default)
        $currentSettings = $this->LeadbotGatewaySetting->getSettings($targetAgencyId, $targetUserId, false);

        // Effective settings with fallback for live ping & discovery
        $effectiveSettings = $this->LeadbotGatewaySetting->getSettings($targetAgencyId, $targetUserId, true);
        $effectiveApiKey = !empty($effectiveSettings['api_key']) ? $effectiveSettings['api_key'] : '';
        $gatewayUrl = !empty($effectiveSettings['gateway_url']) ? $effectiveSettings['gateway_url'] : LeadbotGatewayClient::DEFAULT_LOCAL_URL;

        // 2. Fetch Live Telemetry from Gateway API
        $pingStatus = LeadbotGatewayClient::ping($gatewayUrl, $effectiveApiKey);
        $telemetry  = LeadbotGatewayClient::getGateways($gatewayUrl, $effectiveApiKey, $targetAgencyId);

        // Cache latest telemetry in database for offline resilience
        if (!empty($telemetry['success']) && !empty($currentSettings['id'])) {
            $this->LeadbotGatewaySetting->updateAll(
                array(
                    'LeadbotGatewaySetting.last_sync_at'   => "'" . date('Y-m-d H:i:s') . "'",
                    'LeadbotGatewaySetting.last_telemetry' => "'" . addslashes(json_encode($telemetry)) . "'"
                ),
                array('LeadbotGatewaySetting.id' => (int)$currentSettings['id'])
            );
        } elseif (!empty($currentSettings['last_telemetry'])) {
            $cached = json_decode($currentSettings['last_telemetry'], true);
            if (is_array($cached)) {
                $telemetry = $cached;
                $telemetry['is_cached'] = true;
            }
        }

        // 3. Load Event Matrix Settings
        $events = $this->LeadbotEventSetting->getAgencyEvents($targetAgencyId, $targetUserId);

        // 4. Project List for Super Admin Switcher
        $projectList = array();
        if ($isSuperAdmin) {
            try {
                $projectList = (array)$this->Project->find('list', array(
                    'fields' => array('Project.id', 'Project.name'),
                    'order'  => array('Project.name' => 'ASC'),
                    'recursive' => -1
                ));
            } catch (Exception $e) {
                $projectList = array();
            }
        }

        // 5. Agents in this Agency (for Agent Gateway Management)
        $agencyAgents = array();
        try {
            $agencyAgents = $this->LeadbotGatewaySetting->getAgencyAgentsWithSettings($targetAgencyId);
        } catch (Exception $e) {}

        // Target Agent User record (if viewing an agent)
        $targetAgentUser = null;
        if ($targetUserId > 0) {
            $uRow = $this->User->find('first', array(
                'conditions' => array('User.id' => $targetUserId),
                'fields'     => array('User.id', 'User.name', 'User.email', 'User.mobile', 'User.phone', 'User.project_id'),
                'recursive'  => -1
            ));
            if (!empty($uRow['User'])) {
                $targetAgentUser = $uRow['User'];
            }
        }

        // 6. Recent Logs
        $recentLogs = array();
        try {
            $logConditions = array();
            if ($targetUserId > 0) {
                $logConditions['LeadbotNotificationLog.user_id'] = $targetUserId;
            } elseif (!$isSuperAdmin || $targetAgencyId > 0) {
                $logConditions['LeadbotNotificationLog.project_id'] = $targetAgencyId;
            }
            $recentLogs = (array)$this->LeadbotNotificationLog->find('all', array(
                'conditions' => $logConditions,
                'order'      => array('LeadbotNotificationLog.id' => 'DESC'),
                'limit'      => 15,
                'recursive'  => -1
            ));
        } catch (Exception $e) {}

        // 7. Outbox & Message Queue (Allowed for Super Admin and Agency Admin ONLY)
        $canManageQueue = $this->_canManageQueue();
        $queueCounts = array('total' => 0, 'whatsapp' => 0, 'sms' => 0, 'cancelled' => 0, 'failed' => 0);
        $queueItems = array();

        if ($canManageQueue) {
            try {
                $queueCounts = $this->LeadbotNotificationLog->getQueueCounts($targetAgencyId, $isSuperAdmin);
                $queueItems  = $this->LeadbotNotificationLog->getQueueItems($targetAgencyId, $isSuperAdmin, array(), 150);
            } catch (Exception $eQueue) {}
        }

        $activeTab = isset($this->request->query['tab']) ? strtolower(trim((string)$this->request->query['tab'])) : 'overview';
        if ($activeTab === 'queue' && !$canManageQueue) {
            $activeTab = 'overview';
        }

        // 8. Check if LeadBot is currently set as system default for SMS & WhatsApp
        $isLeadbotDefault = false;
        try {
            $SmsModel = ClassRegistry::init('Admin.Smssetting');
            if ($SmsModel) {
                $sRow = $SmsModel->findById(1);
                if (!empty($sRow['Smssetting']['gateway_type']) && strtolower((string)$sRow['Smssetting']['gateway_type']) === 'leadbot') {
                    $isLeadbotDefault = true;
                } elseif (!empty($sRow['Smssetting']['is_leadbot_default'])) {
                    $isLeadbotDefault = true;
                }
            }
        } catch (Exception $eSmsChk) {}
        if (!empty($effectiveSettings['is_default_sms'])) {
            $isLeadbotDefault = true;
        }

        $this->set(compact(
            'currentSettings',
            'effectiveSettings',
            'events',
            'telemetry',
            'pingStatus',
            'targetAgencyId',
            'targetUserId',
            'targetAgentUser',
            'isSuperAdmin',
            'projectList',
            'agencyAgents',
            'recentLogs',
            'activeTab',
            'gatewayUrl',
            'effectiveApiKey',
            'canManageQueue',
            'queueCounts',
            'queueItems',
            'isLeadbotDefault'
        ));
    }

    /**
     * AJAX 1-Click Action to Set / Toggle LeadBot CRM as Default Gateway for System SMS & WhatsApp
     */
    public function ajax_set_default()
    {
        $this->autoRender = false;
        $this->layout = false;
        $this->response->type('json');

        if (!$this->request->is('post')) {
            return $this->response->body(json_encode(array('success' => false, 'message' => __('Invalid request method.'))));
        }

        $input = $this->request->data;
        $targetAgencyId = $this->_resolveTargetAgencyId();
        $targetUserId   = $this->_resolveTargetUserId();

        // Check user authorization (Super Admin or Agency Admin)
        if (!$this->_isSuperAdmin() && !$this->_isAgencyAdmin()) {
            return $this->response->body(json_encode(array(
                'success' => false,
                'message' => __('You do not have permission to change system-wide default gateways.')
            )));
        }

        // Determine target state
        $SmsModel = ClassRegistry::init('Admin.Smssetting');
        $currentSms = $SmsModel ? $SmsModel->findById(1) : array();
        $isCurrentlyLeadbot = (!empty($currentSms['Smssetting']['gateway_type']) && strtolower((string)$currentSms['Smssetting']['gateway_type']) === 'leadbot') || !empty($currentSms['Smssetting']['is_leadbot_default']);

        $targetState = isset($input['state']) ? (!empty($input['state']) ? 1 : 0) : ($isCurrentlyLeadbot ? 0 : 1);
        $gatewayType = ($targetState === 1) ? 'leadbot' : 'custom';

        try {
            // 1. Update Smssetting
            if ($SmsModel) {
                $SmsModel->id = 1;
                $SmsModel->saveField('gateway_type', $gatewayType);
                $SmsModel->saveField('is_leadbot_default', $targetState);
            }

            // 2. Update LeadbotGatewaySetting
            $this->LeadbotGatewaySetting->updateAll(
                array(
                    'LeadbotGatewaySetting.is_default_sms'      => $targetState,
                    'LeadbotGatewaySetting.is_default_whatsapp' => 1
                ),
                array('LeadbotGatewaySetting.project_id' => $targetAgencyId, 'LeadbotGatewaySetting.user_id' => $targetUserId)
            );

            // Also ensure global setting is updated
            if ($targetAgencyId > 0 || $targetUserId > 0) {
                $this->LeadbotGatewaySetting->updateAll(
                    array(
                        'LeadbotGatewaySetting.is_default_sms'      => $targetState,
                        'LeadbotGatewaySetting.is_default_whatsapp' => 1
                    ),
                    array('LeadbotGatewaySetting.project_id' => 0, 'LeadbotGatewaySetting.user_id' => 0)
                );
            }

            // If activating LeadBot and currently selected WhatsApp session is disconnected, auto-select connected session
            if ($targetState === 1) {
                $curr = $this->LeadbotGatewaySetting->getSettings($targetAgencyId, $targetUserId, true);
                if (empty($curr['selected_wa_session']) || $curr['selected_wa_session'] === 'wa-923391101156') {
                    $gwUrl = !empty($curr['gateway_url']) ? $curr['gateway_url'] : LeadbotGatewayClient::DEFAULT_LOCAL_URL;
                    $apiKey = !empty($curr['api_key']) ? $curr['api_key'] : '';
                    $tel = LeadbotGatewayClient::getGateways($gwUrl, $apiKey);
                    if (!empty($tel['whatsapp_sessions'])) {
                        foreach ($tel['whatsapp_sessions'] as $s) {
                            if (!empty($s['is_connected']) || !empty($s['is_online']) || $s['status'] === 'CONNECTED') {
                                $this->LeadbotGatewaySetting->updateAll(
                                    array('LeadbotGatewaySetting.selected_wa_session' => "'" . addslashes($s['session_id']) . "'"),
                                    array('LeadbotGatewaySetting.project_id' => $targetAgencyId, 'LeadbotGatewaySetting.user_id' => $targetUserId)
                                );
                                break;
                            }
                        }
                    }
                }
            }

            return $this->response->body(json_encode(array(
                'success'            => true,
                'is_leadbot_default' => ($targetState === 1),
                'gateway_type'       => $gatewayType,
                'message'            => ($targetState === 1)
                    ? __('LeadBot CRM is now set as the DEFAULT gateway for SMS & WhatsApp across the system!')
                    : __('Switched to Custom SMS Gateway (/admin/Smssettings) for transactional SMS.')
            )));
        } catch (Exception $e) {
            return $this->response->body(json_encode(array(
                'success' => false,
                'message' => __('Failed to update default gateway: ') . $e->getMessage()
            )));
        }
    }

    /**
     * Save Gateway Credentials & Profile / Android Device Selection (Agency or Agent)
     */
    public function save_settings()
    {
        if (!$this->request->is('post')) {
            return $this->redirect(array('action' => 'index'));
        }

        $targetAgencyId = $this->_resolveTargetAgencyId();
        $targetUserId   = $this->_resolveTargetUserId();
        $postData       = isset($this->request->data['LeadbotGatewaySetting']) ? $this->request->data['LeadbotGatewaySetting'] : $this->request->data;

        $waSession  = trim((string)(isset($postData['selected_wa_session']) ? $postData['selected_wa_session'] : ''));
        $waName     = trim((string)(isset($postData['selected_wa_profile_name']) ? $postData['selected_wa_profile_name'] : $waSession));
        $smsDevId   = !empty($postData['selected_sms_device_id']) ? (int)$postData['selected_sms_device_id'] : null;
        $smsDevName = trim((string)(isset($postData['selected_sms_device_name']) ? $postData['selected_sms_device_name'] : ''));

        $savePayload = array(
            'gateway_url'              => LeadbotGatewayClient::normalizeUrl(isset($postData['gateway_url']) ? $postData['gateway_url'] : ''),
            'api_key'                  => trim((string)(isset($postData['api_key']) ? $postData['api_key'] : '')),
            'selected_wa_session'      => $waSession,
            'selected_wa_profile_name' => $waName,
            'selected_sms_device_id'   => $smsDevId,
            'selected_sms_sim_slot'    => !empty($postData['selected_sms_sim_slot']) ? (int)$postData['selected_sms_sim_slot'] : 1,
            'selected_sms_device_name' => $smsDevName,
            'auto_send_whatsapp'       => !empty($postData['auto_send_whatsapp']) ? 1 : 0,
            'auto_send_sms'            => !empty($postData['auto_send_sms']) ? 1 : 0,
            'notify_agent'             => !empty($postData['notify_agent']) ? 1 : 0,
            'notify_client'            => !empty($postData['notify_client']) ? 1 : 0,
            'admin_mobile'             => trim((string)(isset($postData['admin_mobile']) ? $postData['admin_mobile'] : '')),
            'status'                   => 'active'
        );

        $saved = $this->LeadbotGatewaySetting->saveSettings($targetAgencyId, $targetUserId, $savePayload);

        $label = ($targetUserId > 0) ? __('Agent personal gateway settings') : __('Agency gateway configuration');
        if ($saved) {
            $this->Session->setFlash(sprintf(__('%s updated successfully.'), $label), 'flash', array('alert' => 'success'));
        } else {
            $this->Session->setFlash(__('Failed to save gateway configuration. Please check your inputs.'), 'flash', array('alert' => 'danger'));
        }

        $redirectQuery = array('project_id' => $targetAgencyId, 'tab' => 'settings');
        if ($targetUserId > 0) {
            $redirectQuery['user_id'] = $targetUserId;
        }

        return $this->redirect(array('action' => 'index', '?' => $redirectQuery));
    }

    /**
     * Save Event Notification Toggles & Custom Templates
     */
    public function save_events()
    {
        if (!$this->request->is('post')) {
            return $this->redirect(array('action' => 'index'));
        }

        $targetAgencyId = $this->_resolveTargetAgencyId();
        $targetUserId   = $this->_resolveTargetUserId();
        $eventsData     = isset($this->request->data['LeadbotEvent']) ? $this->request->data['LeadbotEvent'] : array();

        $saved = $this->LeadbotEventSetting->saveAgencyEvents($targetAgencyId, $eventsData, $targetUserId);

        if ($saved) {
            $this->Session->setFlash(__('Notification event toggles and templates saved successfully.'), 'flash', array('alert' => 'success'));
        } else {
            $this->Session->setFlash(__('Failed to save event preferences.'), 'flash', array('alert' => 'danger'));
        }

        $redirectQuery = array('project_id' => $targetAgencyId, 'tab' => 'events');
        if ($targetUserId > 0) {
            $redirectQuery['user_id'] = $targetUserId;
        }

        return $this->redirect(array('action' => 'index', '?' => $redirectQuery));
    }

    /**
     * AJAX 1-Click Interactive Toggle for Event Matrix (WhatsApp, SMS, Notify Agent, Notify Client)
     */
    public function ajax_toggle_event()
    {
        $this->autoRender = false;
        $this->layout = false;
        $this->response->type('json');

        $input = $this->request->is('post') ? $this->request->data : $this->request->query;
        $projectId = isset($input['project_id']) ? (int)$input['project_id'] : $this->_resolveTargetAgencyId();
        $userId    = isset($input['user_id']) ? (int)$input['user_id'] : $this->_resolveTargetUserId();
        $eventKey  = isset($input['event_key']) ? trim((string)$input['event_key']) : '';
        $field     = isset($input['field']) ? trim((string)$input['field']) : '';
        $state     = !empty($input['state']) ? 1 : 0;

        if ($eventKey === '' || $field === '') {
            return $this->response->body(json_encode(array('success' => false, 'error' => 'Invalid parameters')));
        }

        $ok = $this->LeadbotEventSetting->updateSingleToggle($projectId, $userId, $eventKey, $field, $state);
        return $this->response->body(json_encode(array(
            'success'   => $ok,
            'event_key' => $eventKey,
            'field'     => $field,
            'state'     => $state,
            'message'   => $ok ? __('Toggle saved successfully.') : __('Failed to save toggle.')
        )));
    }

    /**
     * AJAX 1-Click Interactive Master Channel Toggle (WhatsApp Master ON/OFF, SMS Master ON/OFF)
     */
    public function ajax_toggle_master()
    {
        $this->autoRender = false;
        $this->layout = false;
        $this->response->type('json');

        $input = $this->request->is('post') ? $this->request->data : $this->request->query;
        $projectId = isset($input['project_id']) ? (int)$input['project_id'] : $this->_resolveTargetAgencyId();
        $userId    = isset($input['user_id']) ? (int)$input['user_id'] : $this->_resolveTargetUserId();
        $channel   = isset($input['channel']) ? strtolower(trim((string)$input['channel'])) : 'whatsapp';
        $state     = !empty($input['state']) ? 1 : 0;

        $field = ($channel === 'sms') ? 'auto_send_sms' : 'auto_send_whatsapp';

        // Role authorization check
        $isSuper = $this->_isSuperAdmin();
        $isAgencyAdmin = $this->_isAgencyAdmin();

        if (!$isSuper) {
            $myAgencyId = $this->_resolveTargetAgencyId();
            if ($isAgencyAdmin) {
                // Agency Admin can toggle agency settings (user_id = 0) or agents within their agency
                if ($userId > 0) {
                    $agentChk = $this->User->find('first', array(
                        'conditions' => array('User.id' => $userId),
                        'fields'     => array('User.id', 'User.project_id'),
                        'recursive'  => -1
                    ));
                    if (empty($agentChk['User']['project_id']) || (int)$agentChk['User']['project_id'] !== $myAgencyId) {
                        return $this->response->body(json_encode(array(
                            'success' => false,
                            'message' => __('Unauthorized agent target.')
                        )));
                    }
                }
                $projectId = $myAgencyId;
            } else {
                // Regular agent can only toggle their own
                $userId = (int)$this->luserId;
                $projectId = $myAgencyId;
            }
        }

        $conditions = array('LeadbotGatewaySetting.user_id' => $userId);
        if ($projectId > 0) {
            $conditions['LeadbotGatewaySetting.project_id'] = $projectId;
        }
        $existing = $this->LeadbotGatewaySetting->find('first', array(
            'conditions' => $conditions,
            'recursive'  => -1
        ));

        if (empty($existing) && $userId > 0) {
            $existing = $this->LeadbotGatewaySetting->find('first', array(
                'conditions' => array('LeadbotGatewaySetting.user_id' => $userId),
                'recursive'  => -1
            ));
        }

        if (!empty($existing['LeadbotGatewaySetting']['id'])) {
            $this->LeadbotGatewaySetting->id = (int)$existing['LeadbotGatewaySetting']['id'];
            $ok = (bool)$this->LeadbotGatewaySetting->saveField($field, $state, array('validate' => false));
        } else {
            // Inherit from agency or create
            $global = $this->LeadbotGatewaySetting->getSettings($projectId, 0, true);
            $global['project_id'] = $projectId;
            $global['user_id']    = $userId;
            $global[$field]       = $state;
            $ok = (bool)$this->LeadbotGatewaySetting->saveSettings($projectId, $userId, $global);
        }

        $agentName = '';
        if ($userId > 0) {
            $uRow = $this->User->find('first', array(
                'conditions' => array('User.id' => $userId),
                'fields'     => array('User.name'),
                'recursive'  => -1
            ));
            if (!empty($uRow['User']['name'])) {
                $agentName = $uRow['User']['name'];
            }
        }

        $channelLabel = ($channel === 'sms') ? 'SMS Auto' : 'WhatsApp Auto';
        $msg = $agentName 
            ? sprintf(__('%s for %s is now %s.'), $channelLabel, $agentName, $state ? 'ON' : 'OFF')
            : sprintf(__('%s master toggle is now %s.'), $channelLabel, $state ? 'ON' : 'OFF');

        return $this->response->body(json_encode(array(
            'success'    => $ok,
            'channel'    => $channel,
            'user_id'    => $userId,
            'project_id' => $projectId,
            'state'      => $state,
            'message'    => $msg
        )));
    }

    /**
     * AJAX 1-Click Interactive Credential Mode Switch (Inherit Agency Default vs Agent Own Isolated)
     */
    public function ajax_set_credential_mode()
    {
        $this->autoRender = false;
        $this->layout = false;
        $this->response->type('json');

        $input = $this->request->is('post') ? $this->request->data : $this->request->query;
        $userId = isset($input['user_id']) ? (int)$input['user_id'] : 0;
        $mode = isset($input['mode']) ? strtolower(trim((string)$input['mode'])) : 'agency';
        $apiKey = isset($input['api_key']) ? trim((string)$input['api_key']) : null;
        $projectId = isset($input['project_id']) ? (int)$input['project_id'] : $this->_resolveTargetAgencyId();

        if ($userId <= 0) {
            return $this->response->body(json_encode(array(
                'success' => false,
                'message' => __('Agent User ID is required.')
            )));
        }

        // Authorization check
        $isSuper = $this->_isSuperAdmin();
        $isAgencyAdmin = $this->_isAgencyAdmin();

        if (!$isSuper) {
            $myAgencyId = $this->_resolveTargetAgencyId();
            if ($isAgencyAdmin) {
                $agentChk = $this->User->find('first', array(
                    'conditions' => array('User.id' => $userId),
                    'fields'     => array('User.id', 'User.project_id', 'User.name'),
                    'recursive'  => -1
                ));
                if (empty($agentChk['User']['project_id']) || (int)$agentChk['User']['project_id'] !== $myAgencyId) {
                    return $this->response->body(json_encode(array(
                        'success' => false,
                        'message' => __('Unauthorized agent target.')
                    )));
                }
                $projectId = $myAgencyId;
            } else {
                if ($userId !== (int)$this->luserId) {
                    return $this->response->body(json_encode(array(
                        'success' => false,
                        'message' => __('You can only manage your own credentials.')
                    )));
                }
                $projectId = $myAgencyId;
            }
        }

        // Fetch Agent Name
        $agentName = '';
        $uRow = $this->User->find('first', array(
            'conditions' => array('User.id' => $userId),
            'fields'     => array('User.id', 'User.name', 'User.project_id'),
            'recursive'  => -1
        ));
        if (!empty($uRow['User']['name'])) {
            $agentName = $uRow['User']['name'];
        }
        if (empty($projectId) && !empty($uRow['User']['project_id'])) {
            $projectId = (int)$uRow['User']['project_id'];
        }

        $conditions = array('LeadbotGatewaySetting.user_id' => $userId);
        if ($projectId > 0) {
            $conditions['LeadbotGatewaySetting.project_id'] = $projectId;
        }
        $existing = $this->LeadbotGatewaySetting->find('first', array(
            'conditions' => $conditions,
            'recursive'  => -1
        ));

        if (empty($existing)) {
            $existing = $this->LeadbotGatewaySetting->find('first', array(
                'conditions' => array('LeadbotGatewaySetting.user_id' => $userId),
                'recursive'  => -1
            ));
        }

        $isIsolated = ($mode === 'isolated');
        $savedApiKey = '';

        if (!empty($existing['LeadbotGatewaySetting']['id'])) {
            $this->LeadbotGatewaySetting->id = (int)$existing['LeadbotGatewaySetting']['id'];
            $stRow = $existing['LeadbotGatewaySetting'];

            if ($isIsolated) {
                if ($apiKey !== null && $apiKey !== '') {
                    $savedApiKey = $apiKey;
                } elseif (!empty($stRow['api_key'])) {
                    $savedApiKey = $stRow['api_key'];
                } elseif (!empty($stRow['personal_api_key'])) {
                    $savedApiKey = $stRow['personal_api_key'];
                }

                $fieldsToSave = array(
                    'credential_mode'  => 'isolated',
                    'api_key'          => $savedApiKey,
                    'personal_api_key' => ($savedApiKey !== '') ? $savedApiKey : (isset($stRow['personal_api_key']) ? $stRow['personal_api_key'] : ''),
                    'modified'         => date('Y-m-d H:i:s')
                );
            } else {
                $rememberKey = !empty($stRow['api_key']) ? $stRow['api_key'] : (!empty($stRow['personal_api_key']) ? $stRow['personal_api_key'] : '');
                $fieldsToSave = array(
                    'credential_mode'  => 'agency',
                    'api_key'          => '',
                    'personal_api_key' => $rememberKey,
                    'modified'         => date('Y-m-d H:i:s')
                );
            }

            $ok = (bool)$this->LeadbotGatewaySetting->save($fieldsToSave, array('validate' => false));
        } else {
            $global = $this->LeadbotGatewaySetting->getSettings($projectId, 0, true);
            $global['project_id'] = $projectId;
            $global['user_id']    = $userId;

            if ($isIsolated) {
                $savedApiKey = ($apiKey !== null) ? $apiKey : '';
                $global['credential_mode']  = 'isolated';
                $global['api_key']          = $savedApiKey;
                $global['personal_api_key'] = $savedApiKey;
            } else {
                $global['credential_mode']  = 'agency';
                $global['api_key']          = '';
                $global['personal_api_key'] = '';
            }

            $ok = (bool)$this->LeadbotGatewaySetting->saveSettings($projectId, $userId, $global);
        }

        $hasKeyNow = $isIsolated && ($savedApiKey !== '');

        if ($isIsolated) {
            $msg = $agentName
                ? sprintf(__('Gateway credentials for %s are now set to Agent Own (Isolated).'), $agentName)
                : __('Gateway credentials set to Agent Own (Isolated).');
        } else {
            $msg = $agentName
                ? sprintf(__('Gateway credentials for %s are now set to Inheriting Agency Default.'), $agentName)
                : __('Gateway credentials set to Inheriting Agency Default.');
        }

        return $this->response->body(json_encode(array(
            'success'   => $ok,
            'user_id'   => $userId,
            'mode'      => $isIsolated ? 'isolated' : 'agency',
            'has_key'   => $hasKeyNow,
            'message'   => $msg
        )));
    }

    /**
     * AJAX Bulk Toggle for all events or a specific sorted category
     */
    public function ajax_bulk_toggle_events()
    {
        $this->autoRender = false;
        $this->response->type('json');

        $input = $this->request->is('post') ? $this->request->data : $this->request->query;
        $projectId = isset($input['project_id']) ? (int)$input['project_id'] : $this->_resolveTargetAgencyId();
        $userId    = isset($input['user_id']) ? (int)$input['user_id'] : $this->_resolveTargetUserId();
        $field     = isset($input['field']) ? trim((string)$input['field']) : '';
        $state     = !empty($input['state']) ? 1 : 0;
        $category  = isset($input['category']) ? trim((string)$input['category']) : 'all';

        $updatedCount = $this->LeadbotEventSetting->updateBulkChannelToggles($projectId, $userId, $field, $state, $category);

        $channelLabel = ($field === 'whatsapp_enabled') ? 'WhatsApp' : (($field === 'sms_enabled') ? 'SMS' : 'Notification');
        $stateLabel   = $state ? 'ENABLED' : 'DISABLED';

        $scopeLabel = ($projectId === 0 && $userId === 0) ? 'LeadBot CRM Global Master' : 'Agency #' . $projectId;
        if ($userId > 0) {
            $scopeLabel = 'Agent #' . $userId;
        }

        $this->response->body(json_encode(array(
            'success'       => true,
            'field'         => $field,
            'state'         => $state,
            'category'      => $category,
            'updated_count' => $updatedCount,
            'scope'         => $scopeLabel,
            'message'       => sprintf(__('%d %s event toggles %s for %s.'), $updatedCount, $channelLabel, $stateLabel, $scopeLabel)
        )));
        return $this->response;
    }

    /**
     * AJAX Live WhatsApp Dispatch Test
     */
    public function test_whatsapp()
    {
        $this->autoRender = false;
        $this->layout = false;
        $this->response->type('json');

        $input = $this->request->is('post') ? $this->request->data : $this->request->query;
        $targetAgencyId = $this->_resolveTargetAgencyId();
        $targetUserId   = $this->_resolveTargetUserId();

        $phone   = LeadbotGatewayClient::cleanPhone(isset($input['phone']) ? $input['phone'] : '');
        $message = trim((string)(isset($input['message']) ? $input['message'] : ''));
        $session = trim((string)(isset($input['session']) ? $input['session'] : ''));

        if ($phone === '') {
            return $this->response->body(json_encode(array(
                'success' => false,
                'error'   => __('Please enter a valid recipient phone number.')
            )));
        }

        if ($message === '') {
            $message = "Test message from White Villas Gateway at " . date('Y-m-d H:i:s') . ". WhatsApp API connected!";
        }

        $settings = $this->LeadbotGatewaySetting->getSettings($targetAgencyId, $targetUserId, true);
        $gatewayUrl = !empty($settings['gateway_url']) ? $settings['gateway_url'] : LeadbotGatewayClient::DEFAULT_LOCAL_URL;
        $apiKey     = !empty($settings['api_key']) ? $settings['api_key'] : '';

        if ($session === '' && !empty($settings['selected_wa_session'])) {
            $session = $settings['selected_wa_session'];
        }

        $result = LeadbotGatewayClient::sendWhatsApp($gatewayUrl, $apiKey, array(
            'recipient' => $phone,
            'message'   => $message,
            'session'   => $session,
            'agency_id' => $targetAgencyId
        ));

        $desc = !empty($session) ? $session : 'Default WhatsApp Profile';
        if (!empty($settings['is_agent_specific'])) $desc = 'Agent Key - ' . $desc;

        $this->LeadbotNotificationLog->recordLog(array(
            'project_id'                => $targetAgencyId,
            'user_id'                   => $targetUserId > 0 ? $targetUserId : (int)$this->luserId,
            'channel'                   => 'whatsapp',
            'event_key'                 => 'manual_test',
            'recipient_phone'           => $phone,
            'recipient_name'            => 'Test Recipient',
            'message'                   => $message,
            'gateway_profile_or_device' => $desc,
            'status'                    => !empty($result['success']) ? 'sent' : 'failed',
            'response_code'             => isset($result['latency_ms']) ? $result['latency_ms'] : null,
            'response_payload'          => isset($result['raw_response']) ? $result['raw_response'] : '',
            'error_message'             => !empty($result['error']) ? $result['error'] : null,
            'is_test'                   => 1
        ));

        return $this->response->body(json_encode($result));
    }

    /**
     * AJAX Live SMS Dispatch Test
     */
    public function test_sms()
    {
        $this->autoRender = false;
        $this->layout = false;
        $this->response->type('json');

        $input = $this->request->is('post') ? $this->request->data : $this->request->query;
        $targetAgencyId = $this->_resolveTargetAgencyId();
        $targetUserId   = $this->_resolveTargetUserId();

        $phone    = LeadbotGatewayClient::cleanPhone(isset($input['phone']) ? $input['phone'] : '');
        $message  = trim((string)(isset($input['message']) ? $input['message'] : ''));
        $deviceId = !empty($input['device_id']) ? (int)$input['device_id'] : null;
        $simSlot  = !empty($input['sim_slot']) ? (int)$input['sim_slot'] : 1;

        if ($phone === '') {
            return $this->response->body(json_encode(array(
                'success' => false,
                'error'   => __('Please enter a valid recipient mobile number.')
            )));
        }

        if ($message === '') {
            $message = "Test SMS from White Villas at " . date('h:i A') . ". Android SMS Gateway is Online!";
        }

        $settings = $this->LeadbotGatewaySetting->getSettings($targetAgencyId, $targetUserId, true);
        $gatewayUrl = !empty($settings['gateway_url']) ? $settings['gateway_url'] : LeadbotGatewayClient::DEFAULT_LOCAL_URL;
        $apiKey     = !empty($settings['api_key']) ? $settings['api_key'] : '';

        if ($deviceId === null && !empty($settings['selected_sms_device_id'])) {
            $deviceId = (int)$settings['selected_sms_device_id'];
            $simSlot  = (int)$settings['selected_sms_sim_slot'];
        }

        $result = LeadbotGatewayClient::sendSms($gatewayUrl, $apiKey, array(
            'recipient' => $phone,
            'message'   => $message,
            'device_id' => $deviceId,
            'sim_slot'  => $simSlot,
            'agency_id' => $targetAgencyId
        ));

        $desc = !empty($deviceId) ? ('Device #' . $deviceId . ' (SIM ' . $simSlot . ')') : 'Default SMS Device';
        if (!empty($settings['is_agent_specific'])) $desc = 'Agent Device - ' . $desc;

        $this->LeadbotNotificationLog->recordLog(array(
            'project_id'                => $targetAgencyId,
            'user_id'                   => $targetUserId > 0 ? $targetUserId : (int)$this->luserId,
            'channel'                   => 'sms',
            'event_key'                 => 'manual_test',
            'recipient_phone'           => $phone,
            'recipient_name'            => 'Test Recipient',
            'message'                   => $message,
            'gateway_profile_or_device' => $desc,
            'status'                    => !empty($result['success']) ? 'queued' : 'failed',
            'response_code'             => isset($result['latency_ms']) ? $result['latency_ms'] : null,
            'response_payload'          => isset($result['raw_response']) ? $result['raw_response'] : '',
            'error_message'             => !empty($result['error']) ? $result['error'] : null,
            'is_test'                   => 1
        ));

        return $this->response->body(json_encode($result));
    }

    /**
     * AJAX Live Telemetry Poll
     */
    public function fetch_telemetry()
    {
        $this->autoRender = false;
        $this->layout = false;
        $this->response->type('json');

        $targetAgencyId = $this->_resolveTargetAgencyId();
        $targetUserId   = $this->_resolveTargetUserId();
        $settings       = $this->LeadbotGatewaySetting->getSettings($targetAgencyId, $targetUserId, true);

        $gatewayUrl = !empty($settings['gateway_url']) ? $settings['gateway_url'] : LeadbotGatewayClient::DEFAULT_LOCAL_URL;
        $apiKey     = !empty($settings['api_key']) ? $settings['api_key'] : '';

        $telemetry = LeadbotGatewayClient::getGateways($gatewayUrl, $apiKey, $targetAgencyId);
        $ping      = LeadbotGatewayClient::ping($gatewayUrl, $apiKey);

        return $this->response->body(json_encode(array(
            'success'   => !empty($telemetry['success']),
            'ping'      => $ping,
            'telemetry' => $telemetry
        )));
    }

    /**
     * Notification Dispatch Logs
     */
    public function logs()
    {
        $isSuperAdmin = $this->_isSuperAdmin();
        $targetAgencyId = $this->_resolveTargetAgencyId();
        $targetUserId   = $this->_resolveTargetUserId();

        $conditions = array();
        if ($targetUserId > 0) {
            $conditions['LeadbotNotificationLog.user_id'] = $targetUserId;
        } elseif (!$isSuperAdmin || $targetAgencyId > 0) {
            $conditions['LeadbotNotificationLog.project_id'] = $targetAgencyId;
        }

        if (!empty($this->request->query['channel'])) {
            $conditions['LeadbotNotificationLog.channel'] = strtolower(trim((string)$this->request->query['channel']));
        }
        if (!empty($this->request->query['status'])) {
            $conditions['LeadbotNotificationLog.status'] = strtolower(trim((string)$this->request->query['status']));
        }
        if (!empty($this->request->query['q'])) {
            $q = trim((string)$this->request->query['q']);
            $conditions['OR'] = array(
                'LeadbotNotificationLog.recipient_phone LIKE' => '%' . $q . '%',
                'LeadbotNotificationLog.recipient_name LIKE'  => '%' . $q . '%',
                'LeadbotNotificationLog.message LIKE'         => '%' . $q . '%'
            );
        }

        $this->Paginator->settings = array(
            'conditions' => $conditions,
            'order'      => array('LeadbotNotificationLog.id' => 'DESC'),
            'limit'      => 30,
            'recursive'  => -1
        );

        $logs = $this->Paginator->paginate('LeadbotNotificationLog');
        $this->set(compact('logs', 'isSuperAdmin', 'targetAgencyId', 'targetUserId'));
    }

    /**
     * Cancel a single queued notification
     * Only Super Admin and Agency Admin allowed
     */
    public function ajax_cancel_queue($id = null)
    {
        $this->autoRender = false;
        $this->response->type('json');

        if (!$this->_canManageQueue()) {
            $this->response->statusCode(403);
            return $this->response->body(json_encode(array(
                'success' => false,
                'error'   => __('Access Denied. Only Super Admin and Agency Admin can cancel queued notifications.')
            )));
        }

        $input = $this->request->is('post') ? $this->request->data : $this->request->query;
        $id = !empty($id) ? (int)$id : (isset($input['id']) ? (int)$input['id'] : 0);
        if ($id <= 0) {
            return $this->response->body(json_encode(array('success' => false, 'error' => __('Invalid queue ID.'))));
        }

        $targetAgencyId = $this->_resolveTargetAgencyId();
        $isSuperAdmin   = $this->_isSuperAdmin();
        $adminName      = !empty($this->adminValue['User']['name']) ? $this->adminValue['User']['name'] : 'Admin';

        $ok = $this->LeadbotNotificationLog->cancelQueueItem($id, $targetAgencyId, $isSuperAdmin, $adminName);

        if ($ok) {
            return $this->response->body(json_encode(array(
                'success' => true,
                'id'      => $id,
                'status'  => 'cancelled',
                'message' => sprintf(__('Notification #%d has been cancelled from the queue.'), $id)
            )));
        }

        return $this->response->body(json_encode(array(
            'success' => false,
            'error'   => __('Unable to cancel item: Record not found or belongs to another agency.')
        )));
    }

    /**
     * Bulk Cancel Selected Queue Items
     * Only Super Admin and Agency Admin allowed
     */
    public function ajax_bulk_cancel_queue()
    {
        $this->autoRender = false;
        $this->response->type('json');

        if (!$this->_canManageQueue()) {
            $this->response->statusCode(403);
            return $this->response->body(json_encode(array(
                'success' => false,
                'error'   => __('Access Denied. Only Super Admin and Agency Admin can cancel queued notifications.')
            )));
        }

        $input = $this->request->is('post') ? $this->request->data : $this->request->query;
        $ids = !empty($input['ids']) ? (array)$input['ids'] : array();
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));

        if (empty($ids)) {
            return $this->response->body(json_encode(array(
                'success' => false,
                'error'   => __('Please select at least one queue notification to cancel.')
            )));
        }

        $targetAgencyId = $this->_resolveTargetAgencyId();
        $isSuperAdmin   = $this->_isSuperAdmin();
        $adminName      = !empty($this->adminValue['User']['name']) ? $this->adminValue['User']['name'] : 'Admin';

        $cancelledCount = $this->LeadbotNotificationLog->bulkCancelQueue($ids, $targetAgencyId, $isSuperAdmin, $adminName);

        return $this->response->body(json_encode(array(
            'success'         => true,
            'cancelled_count' => count($ids),
            'ids'             => $ids,
            'message'         => sprintf(__('%d queued notification(s) successfully cancelled.'), count($ids))
        )));
    }

    /**
     * Delete a single notification from queue / outbox
     * Only Super Admin and Agency Admin allowed
     */
    public function ajax_delete_queue($id = null)
    {
        $this->autoRender = false;
        $this->response->type('json');

        if (!$this->_canManageQueue()) {
            $this->response->statusCode(403);
            return $this->response->body(json_encode(array(
                'success' => false,
                'error'   => __('Access Denied. Only Super Admin and Agency Admin can delete queued notifications.')
            )));
        }

        $input = $this->request->is('post') ? $this->request->data : $this->request->query;
        $id = !empty($id) ? (int)$id : (isset($input['id']) ? (int)$input['id'] : 0);
        if ($id <= 0) {
            return $this->response->body(json_encode(array('success' => false, 'error' => __('Invalid queue ID.'))));
        }

        $targetAgencyId = $this->_resolveTargetAgencyId();
        $isSuperAdmin   = $this->_isSuperAdmin();

        $deleted = $this->LeadbotNotificationLog->deleteQueueItem($id, $targetAgencyId, $isSuperAdmin);

        if ($deleted) {
            return $this->response->body(json_encode(array(
                'success' => true,
                'id'      => $id,
                'message' => sprintf(__('Notification #%d permanently deleted from queue.'), $id)
            )));
        }

        return $this->response->body(json_encode(array(
            'success' => false,
            'error'   => __('Unable to delete: Item not found or access restricted to your agency.')
        )));
    }

    /**
     * Bulk Delete Selected Queue Items
     * Only Super Admin and Agency Admin allowed
     */
    public function ajax_bulk_delete_queue()
    {
        $this->autoRender = false;
        $this->response->type('json');

        if (!$this->_canManageQueue()) {
            $this->response->statusCode(403);
            return $this->response->body(json_encode(array(
                'success' => false,
                'error'   => __('Access Denied. Only Super Admin and Agency Admin can delete queued notifications.')
            )));
        }

        $input = $this->request->is('post') ? $this->request->data : $this->request->query;
        $ids = !empty($input['ids']) ? (array)$input['ids'] : array();
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));

        if (empty($ids)) {
            return $this->response->body(json_encode(array(
                'success' => false,
                'error'   => __('Please select at least one queue notification to delete.')
            )));
        }

        $targetAgencyId = $this->_resolveTargetAgencyId();
        $isSuperAdmin   = $this->_isSuperAdmin();

        $deleted = $this->LeadbotNotificationLog->bulkDeleteQueue($ids, $targetAgencyId, $isSuperAdmin);

        return $this->response->body(json_encode(array(
            'success'       => (bool)$deleted,
            'deleted_count' => count($ids),
            'ids'           => $ids,
            'message'       => sprintf(__('%d notification(s) permanently deleted from queue.'), count($ids))
        )));
    }

    /**
     * Re-dispatch / Retry a queued or failed message
     */
    public function ajax_retry_queue($id = null)
    {
        $this->autoRender = false;
        $this->response->type('json');

        if (!$this->_canManageQueue()) {
            $this->response->statusCode(403);
            return $this->response->body(json_encode(array(
                'success' => false,
                'error'   => __('Access Denied. Only Super Admin and Agency Admin can retry queue notifications.')
            )));
        }

        $input = $this->request->is('post') ? $this->request->data : $this->request->query;
        $id = !empty($id) ? (int)$id : (isset($input['id']) ? (int)$input['id'] : 0);
        if ($id <= 0) {
            return $this->response->body(json_encode(array('success' => false, 'error' => __('Invalid queue ID.'))));
        }

        $targetAgencyId = $this->_resolveTargetAgencyId();
        $conditions = array('LeadbotNotificationLog.id' => $id);
        if (!$this->_isSuperAdmin()) {
            $conditions['LeadbotNotificationLog.project_id'] = (int)$targetAgencyId;
        }

        $item = $this->LeadbotNotificationLog->find('first', array(
            'conditions' => $conditions,
            'recursive'  => -1
        ));

        if (empty($item)) {
            return $this->response->body(json_encode(array('success' => false, 'error' => __('Queue item not found.'))));
        }

        $log = $item['LeadbotNotificationLog'];
        $projectId = (int)$log['project_id'];
        $userId    = !empty($log['user_id']) ? (int)$log['user_id'] : 0;
        $channel   = strtolower(trim((string)$log['channel']));
        $phone     = $log['recipient_phone'];
        $message   = $log['message'];

        $settings = $this->LeadbotGatewaySetting->getSettings($projectId, $userId, true);
        $gatewayUrl = !empty($settings['gateway_url']) ? $settings['gateway_url'] : LeadbotGatewayClient::DEFAULT_LOCAL_URL;
        $apiKey     = !empty($settings['api_key']) ? $settings['api_key'] : '';

        $dispatchResult = array('success' => false, 'error' => 'Unsupported channel');
        if ($channel === 'whatsapp') {
            $session = !empty($settings['selected_wa_session']) ? $settings['selected_wa_session'] : '';
            $dispatchResult = LeadbotGatewayClient::sendWhatsApp($gatewayUrl, $apiKey, array(
                'recipient' => $phone,
                'message'   => $message,
                'session'   => $session,
                'agency_id' => $projectId
            ));
        } elseif ($channel === 'sms') {
            $devId = !empty($settings['selected_sms_device_id']) ? (int)$settings['selected_sms_device_id'] : null;
            $slot  = !empty($settings['selected_sms_sim_slot']) ? (int)$settings['selected_sms_sim_slot'] : 1;
            $dispatchResult = LeadbotGatewayClient::sendSms($gatewayUrl, $apiKey, array(
                'recipient' => $phone,
                'message'   => $message,
                'device_id' => $devId,
                'sim_slot'  => $slot,
                'agency_id' => $projectId
            ));
        }

        $newStatus = !empty($dispatchResult['success']) ? ($channel === 'sms' ? 'queued' : 'sent') : 'failed';
        $this->LeadbotNotificationLog->id = $id;
        $this->LeadbotNotificationLog->save(array(
            'status'           => $newStatus,
            'response_code'    => isset($dispatchResult['latency_ms']) ? (int)$dispatchResult['latency_ms'] : null,
            'response_payload' => isset($dispatchResult['raw_response']) ? (string)$dispatchResult['raw_response'] : '',
            'error_message'    => !empty($dispatchResult['error']) ? (string)$dispatchResult['error'] : null,
            'modified'         => date('Y-m-d H:i:s')
        ), false);

        return $this->response->body(json_encode(array(
            'success'   => !empty($dispatchResult['success']),
            'id'        => $id,
            'status'    => $newStatus,
            'error'     => !empty($dispatchResult['error']) ? $dispatchResult['error'] : null,
            'message'   => !empty($dispatchResult['success']) ? sprintf(__('Message #%d re-dispatched successfully (%s).'), $id, strtoupper($newStatus)) : __('Failed to re-dispatch message.')
        )));
    }
}
