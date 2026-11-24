<?php
App::uses('AdminAppModel', 'Admin.Model');
App::uses('LeadbotGatewayClient', 'Lib');

class LeadbotGatewaySetting extends AdminAppModel
{
    public $useTable = 'leadbot_gateway_settings';

    /**
     * Helper to return standard empty settings structure
     */
    protected function _defaultSettings($projectId = 0, $userId = 0, $scope = 'default')
    {
        return array(
            'id'                       => 0,
            'project_id'               => (int)$projectId,
            'user_id'                  => (int)$userId,
            'gateway_url'              => LeadbotGatewayClient::DEFAULT_LOCAL_URL,
            'api_key'                  => '',
            'credential_mode'          => 'agency',
            'personal_api_key'         => '',
            'selected_wa_session'      => '',
            'selected_wa_profile_name' => '',
            'selected_sms_device_id'   => null,
            'selected_sms_sim_slot'    => 1,
            'selected_sms_device_name' => '',
            'auto_send_whatsapp'       => 1,
            'auto_send_sms'            => 1,
            'notify_agent'             => 1,
            'notify_client'            => 0,
            'admin_mobile'             => '',
            'status'                   => 'active',
            'is_default_sms'           => 0,
            'is_default_whatsapp'      => 1,
            'last_sync_at'             => null,
            'last_telemetry'           => null,
            'resolved_scope'           => $scope,
            'is_agent_specific'        => ($scope === 'agent'),
            'is_agency_specific'       => ($scope === 'agency'),
            'is_global'                => ($scope === 'global')
        );
    }

    /**
     * Get gateway configuration with multi-tier resolution:
     * 1. Agent-specific settings (user_id > 0)
     * 2. Agency-specific settings (project_id > 0, user_id = 0)
     * 3. Global Super Admin settings (project_id = 0, user_id = 0)
     *
     * @param int $projectId Agency / Project ID
     * @param int $userId Specific Agent / User ID
     * @param bool $allowFallback Fallback to agency/global if agent/agency key not configured
     * @return array Resolved gateway settings
     */
    public function getSettings($projectId = 0, $userId = 0, $allowFallback = true)
    {
        $projectId = (int)$projectId;
        $userId    = (int)$userId;
        $row       = null;
        $scope     = 'none';

        // 1. Try Agent-Specific Settings (if userId provided)
        if ($userId > 0) {
            $conds = array('LeadbotGatewaySetting.user_id' => $userId);
            if ($projectId > 0) {
                $conds['LeadbotGatewaySetting.project_id'] = $projectId;
            }
            $row = $this->find('first', array(
                'conditions' => $conds,
                'recursive'  => -1
            ));

            if (empty($row)) {
                $row = $this->find('first', array(
                    'conditions' => array('LeadbotGatewaySetting.user_id' => $userId),
                    'recursive'  => -1
                ));
            }

            if (!empty($row['LeadbotGatewaySetting'])) {
                $isIsolatedMode = (isset($row['LeadbotGatewaySetting']['credential_mode']) && $row['LeadbotGatewaySetting']['credential_mode'] === 'isolated');
                $isInheritMode  = (isset($row['LeadbotGatewaySetting']['credential_mode']) && $row['LeadbotGatewaySetting']['credential_mode'] === 'agency');

                if ($isInheritMode) {
                    $row = null; // Explicitly set to inherit agency default -> fall through to agency/global
                } elseif (!empty($row['LeadbotGatewaySetting']['api_key']) || $isIsolatedMode) {
                    $scope = 'agent';
                } elseif (!$allowFallback) {
                    $scope = 'agent';
                } else {
                    $row = null; // Empty personal key with fallback allowed -> fall through to agency/global
                }
            } elseif (!$allowFallback) {
                return $this->_defaultSettings($projectId, $userId, 'agent');
            }
        }

        // 2. Try Agency-Specific Settings (if projectId provided)
        if (empty($row) && $projectId > 0) {
            $row = $this->find('first', array(
                'conditions' => array(
                    'LeadbotGatewaySetting.project_id' => $projectId,
                    'LeadbotGatewaySetting.user_id'    => 0
                ),
                'recursive' => -1
            ));

            if (!empty($row['LeadbotGatewaySetting'])) {
                if (!empty($row['LeadbotGatewaySetting']['api_key'])) {
                    $scope = 'agency';
                } elseif (!$allowFallback && $userId === 0) {
                    $scope = 'agency';
                } elseif ($allowFallback) {
                    $scope = 'agency';
                } else {
                    $row = null; // Fall through to global
                }
            } elseif (!$allowFallback && $userId === 0) {
                return $this->_defaultSettings($projectId, 0, 'agency');
            }
        }

        // 3. Try Global Super Admin Settings (projectId = 0, userId = 0)
        if (empty($row)) {
            $row = $this->find('first', array(
                'conditions' => array(
                    'LeadbotGatewaySetting.project_id' => 0,
                    'LeadbotGatewaySetting.user_id'    => 0
                ),
                'recursive' => -1
            ));
            if (!empty($row['LeadbotGatewaySetting'])) {
                $scope = 'global';
            }
        }

        if (!empty($row['LeadbotGatewaySetting'])) {
            $data = $row['LeadbotGatewaySetting'];
            if (empty($data['gateway_url'])) {
                $data['gateway_url'] = LeadbotGatewayClient::DEFAULT_LOCAL_URL;
            }
            $data['resolved_scope']     = $scope;
            $data['is_agent_specific']  = ($scope === 'agent');
            $data['is_agency_specific'] = ($scope === 'agency');
            $data['is_global']          = ($scope === 'global');
            return $data;
        }

        return $this->_defaultSettings($projectId, $userId, 'default');
    }

    /**
     * Backward-compatible agency settings lookup
     */
    public function getAgencySettings($projectId = 0, $allowGlobalFallback = true)
    {
        return $this->getSettings($projectId, 0, $allowGlobalFallback);
    }

    /**
     * Get specific agent's gateway settings
     */
    public function getAgentSettings($userId, $projectId = 0, $allowFallback = true)
    {
        return $this->getSettings($projectId, $userId, $allowFallback);
    }

    /**
     * Save or update gateway settings for an Agency (user_id = 0) or specific Agent (user_id > 0)
     */
    public function saveSettings($projectId, $userId = 0, array $data = array())
    {
        $projectId = (int)$projectId;
        $userId    = (int)$userId;

        $existing = $this->find('first', array(
            'conditions' => array(
                'LeadbotGatewaySetting.project_id' => $projectId,
                'LeadbotGatewaySetting.user_id'    => $userId
            ),
            'fields' => array('LeadbotGatewaySetting.id'),
            'recursive' => -1
        ));

        // If not found with project_id, also try by user_id if user_id > 0
        if (empty($existing) && $userId > 0) {
            $existing = $this->find('first', array(
                'conditions' => array('LeadbotGatewaySetting.user_id' => $userId),
                'fields' => array('LeadbotGatewaySetting.id'),
                'recursive' => -1
            ));
        }

        $saveData = array(
            'project_id'               => $projectId,
            'user_id'                  => $userId,
            'gateway_url'              => LeadbotGatewayClient::normalizeUrl(isset($data['gateway_url']) ? $data['gateway_url'] : ''),
            'api_key'                  => trim((string)(isset($data['api_key']) ? $data['api_key'] : '')),
            'credential_mode'          => isset($data['credential_mode']) ? $data['credential_mode'] : (!empty($data['api_key']) ? 'isolated' : 'agency'),
            'personal_api_key'         => isset($data['personal_api_key']) ? trim((string)$data['personal_api_key']) : (isset($data['api_key']) ? trim((string)$data['api_key']) : ''),
            'selected_wa_session'      => trim((string)(isset($data['selected_wa_session']) ? $data['selected_wa_session'] : '')),
            'selected_wa_profile_name' => trim((string)(isset($data['selected_wa_profile_name']) ? $data['selected_wa_profile_name'] : '')),
            'selected_sms_device_id'   => !empty($data['selected_sms_device_id']) ? (int)$data['selected_sms_device_id'] : null,
            'selected_sms_sim_slot'    => !empty($data['selected_sms_sim_slot']) ? (int)$data['selected_sms_sim_slot'] : 1,
            'selected_sms_device_name' => trim((string)(isset($data['selected_sms_device_name']) ? $data['selected_sms_device_name'] : '')),
            'auto_send_whatsapp'       => isset($data['auto_send_whatsapp']) ? (int)$data['auto_send_whatsapp'] : 0,
            'auto_send_sms'            => isset($data['auto_send_sms']) ? (int)$data['auto_send_sms'] : 0,
            'notify_agent'             => isset($data['notify_agent']) ? (int)$data['notify_agent'] : 0,
            'notify_client'            => isset($data['notify_client']) ? (int)$data['notify_client'] : 0,
            'admin_mobile'             => trim((string)(isset($data['admin_mobile']) ? $data['admin_mobile'] : '')),
            'status'                   => !empty($data['status']) ? $data['status'] : 'active',
            'modified'                 => date('Y-m-d H:i:s')
        );

        $this->create();
        if (!empty($existing['LeadbotGatewaySetting']['id'])) {
            $existingId = (int)$existing['LeadbotGatewaySetting']['id'];
            $saveData['id'] = $existingId;
            $this->id = $existingId;
        } else {
            $saveData['created'] = date('Y-m-d H:i:s');
            $this->id = null;
        }

        return (bool)$this->save($saveData, array('validate' => false));
    }

    /**
     * Backward-compatible agency save
     */
    public function saveAgencySettings($projectId, array $data)
    {
        return $this->saveSettings($projectId, 0, $data);
    }

    /**
     * Save agent personal settings
     */
    public function saveAgentSettings($userId, $projectId, array $data)
    {
        return $this->saveSettings($projectId, $userId, $data);
    }

    /**
     * Get list of agents in an agency with their personal gateway status
     */
    public function getAgencyAgentsWithSettings($projectId = 0)
    {
        $projectId = (int)$projectId;
        $User = ClassRegistry::init('User');

        $conds = array('User.status' => 'Active');
        if ($projectId > 0) {
            $conds['User.project_id'] = $projectId;
        }

        $users = (array)$User->find('all', array(
            'conditions' => $conds,
            'fields'     => array('User.id', 'User.name', 'User.email', 'User.mobile', 'User.phone', 'User.project_id', 'User.ugroup_id'),
            'order'      => array('User.name' => 'ASC'),
            'recursive'  => -1
        ));

        $userIds = Hash::extract($users, '{n}.User.id');
        $agentSettings = array();
        if (!empty($userIds)) {
            $settingsRows = (array)$this->find('all', array(
                'conditions' => array('LeadbotGatewaySetting.user_id' => $userIds),
                'recursive'  => -1
            ));
            foreach ($settingsRows as $sr) {
                $uId = (int)$sr['LeadbotGatewaySetting']['user_id'];
                $agentSettings[$uId] = $sr['LeadbotGatewaySetting'];
            }
        }

        $result = array();
        foreach ($users as $u) {
            $uId = (int)$u['User']['id'];
            $st  = isset($agentSettings[$uId]) ? $agentSettings[$uId] : null;
            $hasKey = !empty($st['api_key']);
            $mode = 'agency';
            if (!empty($st)) {
                if (isset($st['credential_mode']) && $st['credential_mode'] !== '') {
                    $mode = ($st['credential_mode'] === 'isolated') ? 'isolated' : 'agency';
                } else {
                    $mode = $hasKey ? 'isolated' : 'agency';
                }
            }

            $result[] = array(
                'User'            => $u['User'],
                'has_own_key'     => ($mode === 'isolated'),
                'credential_mode' => $mode,
                'gateway_setting' => $st
            );
        }

        return $result;
    }
}
