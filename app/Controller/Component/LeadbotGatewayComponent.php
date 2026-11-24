<?php
App::uses('Component', 'Controller');
App::uses('LeadbotGatewayClient', 'Lib');
App::uses('ClassRegistry', 'Utility');

class LeadbotGatewayComponent extends Component
{
    protected $_controller = null;
    protected $_gatewayModel = null;
    protected $_eventModel = null;
    protected $_logModel = null;
    protected $_userModel = null;

    public function initialize(Controller $controller)
    {
        $this->_controller = $controller;
    }

    protected function _getGatewayModel()
    {
        if ($this->_gatewayModel === null) {
            $this->_gatewayModel = ClassRegistry::init('Admin.LeadbotGatewaySetting');
        }
        return $this->_gatewayModel;
    }

    protected function _getEventModel()
    {
        if ($this->_eventModel === null) {
            $this->_eventModel = ClassRegistry::init('Admin.LeadbotEventSetting');
        }
        return $this->_eventModel;
    }

    protected function _getLogModel()
    {
        if ($this->_logModel === null) {
            $this->_logModel = ClassRegistry::init('Admin.LeadbotNotificationLog');
        }
        return $this->_logModel;
    }

    protected function _getUserModel()
    {
        if ($this->_userModel === null) {
            $this->_userModel = ClassRegistry::init('User');
        }
        return $this->_userModel;
    }

    /**
     * Dispatch automated WhatsApp and/or SMS notifications for any CRM or system event.
     * Automatically prioritizes Agent-specific API key/session/device, then Agency, then Super Admin.
     *
     * @param string $eventKey (e.g. 'lead_assignment', 'facebook_sync', 'followup_reminder', 'deal_closed', etc.)
     * @param array $data Contextual information (client_name, agent_name, phone, amount, user_id, project_id, etc.)
     * @param int|null $projectId Specific agency project ID (auto-resolved if null)
     * @param int|null $userId Specific agent user ID (auto-resolved if null)
     * @return array Dispatch summary
     */
    public function dispatch($eventKey, array $data = array(), $projectId = null, $userId = null)
    {
        $eventKey = trim((string)$eventKey);
        if ($eventKey === '') {
            return array('success' => false, 'error' => 'Event key is required');
        }

        // 1. Resolve User / Agent ID
        if ($userId === null) {
            if (!empty($data['user_id'])) {
                $userId = (int)$data['user_id'];
            } elseif (!empty($data['agent_id'])) {
                $userId = (int)$data['agent_id'];
            } elseif (!empty($this->_controller->luserId)) {
                $userId = (int)$this->_controller->luserId;
            } else {
                $userId = 0;
            }
        }
        $userId = (int)$userId;

        // 2. Resolve Project / Agency ID
        if ($projectId === null) {
            if (!empty($data['project_id'])) {
                $projectId = (int)$data['project_id'];
            } elseif (!empty($this->_controller->agencyId)) {
                $projectId = (int)$this->_controller->agencyId;
            } elseif ($userId > 0) {
                $userModel = $this->_getUserModel();
                $uRow = $userModel->find('first', array(
                    'conditions' => array('User.id' => $userId),
                    'fields' => array('User.project_id'),
                    'recursive' => -1
                ));
                if (!empty($uRow['User']['project_id'])) {
                    $projectId = (int)$uRow['User']['project_id'];
                } else {
                    $projectId = 0;
                }
            } else {
                $projectId = 0;
            }
        }
        $projectId = (int)$projectId;

        // 3. Load Gateway Settings (Multi-tier: Agent -> Agency -> Global Super Admin)
        $gatewayModel = $this->_getGatewayModel();
        $settings = $gatewayModel->getSettings($projectId, $userId, true);

        if (empty($settings) || empty($settings['api_key'])) {
            return array('success' => false, 'error' => 'LeadBot CRM API Key not configured for this agent or agency');
        }

        $gatewayUrl = !empty($settings['gateway_url']) ? $settings['gateway_url'] : LeadbotGatewayClient::DEFAULT_LOCAL_URL;
        $apiKey     = $settings['api_key'];
        $waSession  = !empty($settings['selected_wa_session']) ? $settings['selected_wa_session'] : '';
        $smsDevId   = !empty($settings['selected_sms_device_id']) ? (int)$settings['selected_sms_device_id'] : null;
        $smsSimSlot = !empty($settings['selected_sms_sim_slot']) ? (int)$settings['selected_sms_sim_slot'] : 1;

        // Check Master Channel Toggles
        $channelWaActive  = !empty($settings['auto_send_whatsapp']);
        $channelSmsActive = !empty($settings['auto_send_sms']);

        if (!$channelWaActive && !$channelSmsActive) {
            return array('success' => false, 'error' => 'Both WhatsApp and SMS notifications are toggled OFF');
        }

        // 4. Load Event Configuration (Multi-tier: Agent -> Agency -> Global)
        $eventModel = $this->_getEventModel();
        $eventConfig = $eventModel->getEventConfig($eventKey, $projectId, $userId);
        if (empty($eventConfig)) {
            return array('success' => false, 'error' => 'No event configuration found for ' . $eventKey);
        }

        $isWaEnabled  = $channelWaActive && !empty($eventConfig['whatsapp_enabled']);
        $isSmsEnabled = $channelSmsActive && !empty($eventConfig['sms_enabled']);

        if (!$isWaEnabled && !$isSmsEnabled) {
            return array('success' => false, 'error' => 'Event toggles for WhatsApp & SMS are disabled for ' . $eventKey);
        }

        // 5. Resolve Target Recipients
        $recipients = array();

        // Target: Agent
        if (!empty($eventConfig['notify_agent']) || !empty($settings['notify_agent'])) {
            $agentPhone = '';
            $agentName  = isset($data['agent_name']) ? $data['agent_name'] : '';
            if (!empty($data['agent_mobile'])) {
                $agentPhone = $data['agent_mobile'];
            } elseif (!empty($data['agent_phone'])) {
                $agentPhone = $data['agent_phone'];
            } elseif ($userId > 0) {
                $userModel = $this->_getUserModel();
                $userRow = $userModel->find('first', array(
                    'conditions' => array('User.id' => $userId),
                    'fields' => array('User.id', 'User.name', 'User.mobile', 'User.phone'),
                    'recursive' => -1
                ));
                if (!empty($userRow['User'])) {
                    $agentPhone = !empty($userRow['User']['mobile']) ? $userRow['User']['mobile'] : (!empty($userRow['User']['phone']) ? $userRow['User']['phone'] : '');
                    if ($agentName === '') {
                        $agentName = $userRow['User']['name'];
                    }
                }
            }
            if ($agentPhone !== '') {
                $recipients[] = array(
                    'type'  => 'agent',
                    'name'  => $agentName !== '' ? $agentName : 'Agent',
                    'phone' => LeadbotGatewayClient::cleanPhone($agentPhone)
                );
            }
        }

        // Target: Client
        if (!empty($eventConfig['notify_client']) || !empty($settings['notify_client'])) {
            $clientPhone = '';
            $clientName  = isset($data['client_name']) ? $data['client_name'] : (isset($data['name']) ? $data['name'] : '');
            if (!empty($data['client_mobile'])) {
                $clientPhone = $data['client_mobile'];
            } elseif (!empty($data['lead_mobile'])) {
                $clientPhone = $data['lead_mobile'];
            } elseif (!empty($data['phone'])) {
                $clientPhone = $data['phone'];
            } elseif (!empty($data['mobile'])) {
                $clientPhone = $data['mobile'];
            }
            if ($clientPhone !== '') {
                $recipients[] = array(
                    'type'  => 'client',
                    'name'  => $clientName !== '' ? $clientName : 'Client',
                    'phone' => LeadbotGatewayClient::cleanPhone($clientPhone)
                );
            }
        }

        // Target: Direct recipient passed in data
        if (empty($recipients)) {
            $directPhone = !empty($data['phone']) ? $data['phone'] : (!empty($data['mobile']) ? $data['mobile'] : '');
            if ($directPhone !== '') {
                $recipients[] = array(
                    'type'  => 'recipient',
                    'name'  => !empty($data['client_name']) ? $data['client_name'] : 'Client',
                    'phone' => LeadbotGatewayClient::cleanPhone($directPhone)
                );
            }
        }

        // Target: Admin CC
        if (!empty($settings['admin_mobile'])) {
            $recipients[] = array(
                'type'  => 'admin',
                'name'  => 'Administration',
                'phone' => LeadbotGatewayClient::cleanPhone($settings['admin_mobile'])
            );
        }

        if (empty($recipients)) {
            return array('success' => false, 'error' => 'No recipient phone numbers could be resolved for this notification');
        }

        // 6. Compile Message Texts
        $waTemplate  = !empty($eventConfig['whatsapp_template']) ? $eventConfig['whatsapp_template'] : (!empty($data['message']) ? $data['message'] : 'Notification alert from White Villas');
        $smsTemplate = !empty($eventConfig['sms_template']) ? $eventConfig['sms_template'] : (!empty($data['message']) ? $data['message'] : 'Notification alert from White Villas');

        $compiledWaText  = LeadbotGatewayClient::compileTemplate($waTemplate, $data);
        $compiledSmsText = LeadbotGatewayClient::compileTemplate($smsTemplate, $data);

        $logModel = $this->_getLogModel();
        $dispatched = array(
            'whatsapp' => array(),
            'sms'      => array()
        );

        $seenPhones = array();

        foreach ($recipients as $rec) {
            $phone = $rec['phone'];
            if ($phone === '' || isset($seenPhones[$phone])) {
                continue;
            }
            $seenPhones[$phone] = true;

            // Gateway Device / Profile Description
            $waDesc  = !empty($waSession) ? $waSession : 'Default WhatsApp Profile';
            $smsDesc = !empty($smsDevId) ? ('Device #' . $smsDevId . ' (SIM ' . $smsSimSlot . ')') : 'Default SMS Device';
            if (!empty($settings['is_agent_specific'])) {
                $waDesc  = 'Agent Key - ' . $waDesc;
                $smsDesc = 'Agent Device - ' . $smsDesc;
            }

            // Dispatch WhatsApp
            if ($isWaEnabled) {
                $waResult = LeadbotGatewayClient::sendWhatsApp($gatewayUrl, $apiKey, array(
                    'recipient' => $phone,
                    'message'   => $compiledWaText,
                    'session'   => $waSession,
                    'agency_id' => $projectId
                ));

                $logModel->recordLog(array(
                    'project_id'                => $projectId,
                    'user_id'                   => $userId > 0 ? $userId : null,
                    'channel'                   => 'whatsapp',
                    'event_key'                 => $eventKey,
                    'recipient_phone'           => $phone,
                    'recipient_name'            => $rec['name'],
                    'message'                   => $compiledWaText,
                    'gateway_profile_or_device' => $waDesc,
                    'status'                    => !empty($waResult['success']) ? 'sent' : 'failed',
                    'response_code'             => isset($waResult['latency_ms']) ? $waResult['latency_ms'] : 0,
                    'response_payload'          => isset($waResult['raw_response']) ? $waResult['raw_response'] : '',
                    'error_message'             => !empty($waResult['error']) ? $waResult['error'] : null,
                    'is_test'                   => 0
                ));

                $dispatched['whatsapp'][] = array(
                    'phone'   => $phone,
                    'success' => !empty($waResult['success']),
                    'result'  => $waResult
                );
            }

            // Dispatch SMS
            if ($isSmsEnabled) {
                $smsResult = LeadbotGatewayClient::sendSms($gatewayUrl, $apiKey, array(
                    'recipient' => $phone,
                    'message'   => $compiledSmsText,
                    'device_id' => $smsDevId,
                    'sim_slot'  => $smsSimSlot,
                    'agency_id' => $projectId
                ));

                $logModel->recordLog(array(
                    'project_id'                => $projectId,
                    'user_id'                   => $userId > 0 ? $userId : null,
                    'channel'                   => 'sms',
                    'event_key'                 => $eventKey,
                    'recipient_phone'           => $phone,
                    'recipient_name'            => $rec['name'],
                    'message'                   => $compiledSmsText,
                    'gateway_profile_or_device' => $smsDesc,
                    'status'                    => !empty($smsResult['success']) ? 'queued' : 'failed',
                    'response_code'             => isset($smsResult['latency_ms']) ? $smsResult['latency_ms'] : 0,
                    'response_payload'          => isset($smsResult['raw_response']) ? $smsResult['raw_response'] : '',
                    'error_message'             => !empty($smsResult['error']) ? $smsResult['error'] : null,
                    'is_test'                   => 0
                ));

                $dispatched['sms'][] = array(
                    'phone'   => $phone,
                    'success' => !empty($smsResult['success']),
                    'result'  => $smsResult
                );
            }
        }

        return array(
            'success'    => true,
            'event'      => $eventKey,
            'project_id' => $projectId,
            'user_id'    => $userId,
            'scope'      => isset($settings['resolved_scope']) ? $settings['resolved_scope'] : 'agency',
            'dispatched' => $dispatched
        );
    }

    /**
     * Send direct single message (WhatsApp or SMS) to a phone number.
     * Respects agent's personal API key/profile/device first, then agency, then global.
     * Also checks channel toggle before sending.
     *
     * @param string $channel 'whatsapp' or 'sms'
     * @param string $recipientPhone Target mobile number
     * @param string $message Text message
     * @param array $options ['user_id', 'project_id', 'recipient_name', 'event_key', 'lead_id']
     * @return array
     */
    public function sendDirect($channel, $recipientPhone, $message, array $options = array())
    {
        $channel = strtolower(trim((string)$channel));
        if (!in_array($channel, array('whatsapp', 'sms'), true)) {
            return array('success' => false, 'error' => 'Invalid channel. Choose whatsapp or sms.');
        }

        $recipientPhone = LeadbotGatewayClient::cleanPhone($recipientPhone);
        if ($recipientPhone === '') {
            return array('success' => false, 'error' => 'Valid recipient phone number is required.');
        }

        $message = trim((string)$message);
        if ($message === '') {
            return array('success' => false, 'error' => 'Message text cannot be empty.');
        }

        $userId    = !empty($options['user_id']) ? (int)$options['user_id'] : (!empty($this->_controller->luserId) ? (int)$this->_controller->luserId : 0);
        $projectId = !empty($options['project_id']) ? (int)$options['project_id'] : (!empty($this->_controller->agencyId) ? (int)$this->_controller->agencyId : 0);
        $recName   = !empty($options['recipient_name']) ? trim((string)$options['recipient_name']) : 'Valued Client';
        $eventKey  = !empty($options['event_key']) ? trim((string)$options['event_key']) : 'direct_message';

        $gatewayModel = $this->_getGatewayModel();
        $settings = $gatewayModel->getSettings($projectId, $userId, true);

        if (empty($settings['api_key'])) {
            return array('success' => false, 'error' => 'LeadBot CRM API Key not configured. Please configure in Gateway Hub.');
        }

        // Toggle verification
        if ($channel === 'whatsapp' && empty($settings['auto_send_whatsapp'])) {
            return array('success' => false, 'error' => 'WhatsApp messaging is toggled OFF in your Gateway settings.');
        }
        if ($channel === 'sms' && empty($settings['auto_send_sms'])) {
            return array('success' => false, 'error' => 'SMS messaging is toggled OFF in your Gateway settings.');
        }

        $gatewayUrl = !empty($settings['gateway_url']) ? $settings['gateway_url'] : LeadbotGatewayClient::DEFAULT_LOCAL_URL;
        $apiKey     = $settings['api_key'];
        $logModel   = $this->_getLogModel();

        if ($channel === 'whatsapp') {
            $waSession = !empty($settings['selected_wa_session']) ? $settings['selected_wa_session'] : '';
            $desc = !empty($waSession) ? $waSession : 'Default WhatsApp Profile';
            if (!empty($settings['is_agent_specific'])) $desc = 'Agent Key - ' . $desc;

            $result = LeadbotGatewayClient::sendWhatsApp($gatewayUrl, $apiKey, array(
                'recipient' => $recipientPhone,
                'message'   => $message,
                'session'   => $waSession,
                'agency_id' => $projectId
            ));

            $logId = $logModel->recordLog(array(
                'project_id'                => $projectId,
                'user_id'                   => $userId > 0 ? $userId : null,
                'channel'                   => 'whatsapp',
                'event_key'                 => $eventKey,
                'recipient_phone'           => $recipientPhone,
                'recipient_name'            => $recName,
                'message'                   => $message,
                'gateway_profile_or_device' => $desc,
                'status'                    => !empty($result['success']) ? 'sent' : 'failed',
                'response_code'             => isset($result['latency_ms']) ? $result['latency_ms'] : 0,
                'response_payload'          => isset($result['raw_response']) ? $result['raw_response'] : '',
                'error_message'             => !empty($result['error']) ? $result['error'] : null,
                'is_test'                   => 0
            ));

            return array(
                'success'    => !empty($result['success']),
                'channel'    => 'whatsapp',
                'message'    => !empty($result['success']) ? __('WhatsApp message sent successfully via %s', $desc) : (!empty($result['error']) ? $result['error'] : __('WhatsApp delivery failed')),
                'log_id'     => $logId,
                'result'     => $result
            );
        } else {
            $smsDevId   = !empty($settings['selected_sms_device_id']) ? (int)$settings['selected_sms_device_id'] : null;
            $smsSimSlot = !empty($settings['selected_sms_sim_slot']) ? (int)$settings['selected_sms_sim_slot'] : 1;
            $desc = !empty($smsDevId) ? ('Device #' . $smsDevId . ' (SIM ' . $smsSimSlot . ')') : 'Default SMS Device';
            if (!empty($settings['is_agent_specific'])) $desc = 'Agent Device - ' . $desc;

            $result = LeadbotGatewayClient::sendSms($gatewayUrl, $apiKey, array(
                'recipient' => $recipientPhone,
                'message'   => $message,
                'device_id' => $smsDevId,
                'sim_slot'  => $smsSimSlot,
                'agency_id' => $projectId
            ));

            $logId = $logModel->recordLog(array(
                'project_id'                => $projectId,
                'user_id'                   => $userId > 0 ? $userId : null,
                'channel'                   => 'sms',
                'event_key'                 => $eventKey,
                'recipient_phone'           => $recipientPhone,
                'recipient_name'            => $recName,
                'message'                   => $message,
                'gateway_profile_or_device' => $desc,
                'status'                    => !empty($result['success']) ? 'queued' : 'failed',
                'response_code'             => isset($result['latency_ms']) ? $result['latency_ms'] : 0,
                'response_payload'          => isset($result['raw_response']) ? $result['raw_response'] : '',
                'error_message'             => !empty($result['error']) ? $result['error'] : null,
                'is_test'                   => 0
            ));

            return array(
                'success'    => !empty($result['success']),
                'channel'    => 'sms',
                'message'    => !empty($result['success']) ? __('SMS queued on Android Gateway (%s)', $desc) : (!empty($result['error']) ? $result['error'] : __('SMS dispatch failed')),
                'log_id'     => $logId,
                'result'     => $result
            );
        }
    }
}
