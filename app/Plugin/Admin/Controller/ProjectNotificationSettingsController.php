<?php
App::uses('AdminAppController', 'Admin.Controller');

class ProjectNotificationSettingsController extends AdminAppController
{
    public $helpers = array('Html', 'Form', 'Session');
    public $uses = array(
        'Admin.ProjectNotificationSetting',
        'Admin.Project',
        'Admin.WhatsappProjectGateway',
        'Admin.WhatsappSetting',
        'Admin.WhatsappLog'
    );

    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->_ensureAdminPermission();
    }

    protected function _ensureAdminPermission()
    {
        $sessionUser = (array)$this->adminValue;
        $isSuper = $this->_isSuperAdminSession($sessionUser);
        $isCeo = $this->_isCeoRoleContext($sessionUser);

        if (!$isSuper && !$isCeo) {
            $this->Session->setFlash(__('Access denied. Only Super Admin and CEO can manage Project Notification & WhatsApp Settings.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('controller' => 'Dashboards', 'action' => 'index'));
        }
    }

    public function index()
    {
        $this->ProjectNotificationSetting->ensureSchema();
        $projectId = 0;
        if (!empty($this->request->query['project_id'])) {
            $projectId = (int)$this->request->query['project_id'];
        }

        $projects = array('0' => __('Global / All Projects (Default)')) + (array)$this->Project->find('list', array(
            'fields' => array('Project.id', 'Project.name'),
            'order' => array('Project.name' => 'ASC'),
            'recursive' => -1
        ));

        // Available gateways
        $gateways = array('0' => __('Master Agency Gateway (Global)'));
        $gatewayRows = $this->WhatsappProjectGateway->find('all', array(
            'conditions' => array('WhatsappProjectGateway.is_active' => 1),
            'order' => array('WhatsappProjectGateway.gateway_name' => 'ASC'),
            'recursive' => -1
        ));
        foreach($gatewayRows as $gw) {
            $gwId = $gw['WhatsappProjectGateway']['id'];
            $gwName = $gw['WhatsappProjectGateway']['gateway_name'] . (!empty($gw['WhatsappProjectGateway']['sender_phone_number']) ? ' (' . $gw['WhatsappProjectGateway']['sender_phone_number'] . ')' : '');
            $gateways[$gwId] = $gwName;
        }

        if ($this->request->is(array('post', 'put'))) {
            $data = $this->request->data;

            // Check if this is a test message dispatch
            if (!empty($data['TestWhatsapp']['send_test'])) {
                $testMobile = !empty($data['TestWhatsapp']['mobile']) ? trim($data['TestWhatsapp']['mobile']) : '';
                $testMsg = !empty($data['TestWhatsapp']['message']) ? trim($data['TestWhatsapp']['message']) : 'This is a test notification from White Villas Portal.';

                if (empty($testMobile)) {
                    $this->Session->setFlash(__('Please enter a valid mobile number for test message.'), 'flash', array('alert' => 'warning'));
                } else {
                    $testRes = $this->_dispatchProjectWhatsapp($projectId, $testMobile, $testMsg, 'PORTAL_TEST', 'Test User');
                    if (!empty($testRes['success'])) {
                        $this->Session->setFlash(__('Test WhatsApp message sent successfully! Meta Message ID: ' . (isset($testRes['meta_id']) ? $testRes['meta_id'] : 'OK')), 'flash', array('alert' => 'success'));
                    } else {
                        $this->Session->setFlash(__('Failed to send test WhatsApp message: ' . (isset($testRes['message']) ? $testRes['message'] : 'Unknown error')), 'flash', array('alert' => 'danger'));
                    }
                }
                return $this->redirect(array('action' => 'index', '?' => array('project_id' => $projectId)));
            }

            // Normal settings save
            if (isset($data['ProjectNotificationSetting'])) {
                $saveData = $data['ProjectNotificationSetting'];
                $saveData['project_id'] = (int)$projectId;

                // Toggles
                $saveData['in_portal_notifications'] = !empty($saveData['in_portal_notifications']) ? 1 : 0;
                $saveData['whatsapp_reminders'] = !empty($saveData['whatsapp_reminders']) ? 1 : 0;
                $saveData['whatsapp_receipts'] = !empty($saveData['whatsapp_receipts']) ? 1 : 0;
                $saveData['whatsapp_letters'] = !empty($saveData['whatsapp_letters']) ? 1 : 0;
                $saveData['whatsapp_bot'] = !empty($saveData['whatsapp_bot']) ? 1 : 0;
                $saveData['whatsapp_gateway_id'] = !empty($saveData['whatsapp_gateway_id']) ? (int)$saveData['whatsapp_gateway_id'] : 0;
                $saveData['reminder_days_before'] = !empty($saveData['reminder_days_before']) ? trim($saveData['reminder_days_before']) : '7,1';

                $existing = $this->ProjectNotificationSetting->find('first', array(
                    'conditions' => array('ProjectNotificationSetting.project_id' => $saveData['project_id']),
                    'recursive' => -1
                ));

                if (!empty($existing['ProjectNotificationSetting']['id'])) {
                    $saveData['id'] = (int)$existing['ProjectNotificationSetting']['id'];
                } else {
                    $this->ProjectNotificationSetting->create();
                }

                if ($this->ProjectNotificationSetting->save($saveData)) {
                    $this->Session->setFlash(__('Notification & WhatsApp settings for ' . (isset($projects[$projectId]) ? $projects[$projectId] : 'Project') . ' saved successfully!'), 'flash', array('alert' => 'success'));
                    return $this->redirect(array('action' => 'index', '?' => array('project_id' => $projectId)));
                } else {
                    $this->Session->setFlash(__('Unable to save settings. Please verify input values.'), 'flash', array('alert' => 'danger'));
                }
            }
        }

        $settings = $this->ProjectNotificationSetting->getSettings($projectId);
        $this->request->data['ProjectNotificationSetting'] = $settings;

        // Webhook URL
        App::uses('Router', 'Routing');
        $webhookUrl = Router::url(array('plugin' => false, 'admin' => false, 'controller' => 'Crmwebhooks', 'action' => 'whatsapp_bot'), true);

        $this->set(compact('settings', 'projects', 'projectId', 'gateways', 'webhookUrl'));
    }

    protected function _dispatchProjectWhatsapp($projectId, $toMobile, $messageBody, $category = 'PORTAL_TEST', $recipientName = '')
    {
        try {
            $wa = $this->WhatsappSetting->findById(1);
            if (empty($wa['WhatsappSetting']) || empty($wa['WhatsappSetting']['is_active'])) {
                return array('success' => false, 'message' => 'WhatsApp API Gateway is disabled globally in Settings.');
            }

            $s = $wa['WhatsappSetting'];
            $phoneId = trim((string)$s['phone_number_id']);
            $token = trim((string)$s['access_token']);
            $apiVersion = !empty($s['api_version']) ? trim($s['api_version']) : 'v19.0';
            $gatewayNameUsed = 'Master Agency Gateway';

            // Check project setting
            $pSetting = $this->ProjectNotificationSetting->getSettings($projectId);
            if (!empty($pSetting['whatsapp_gateway_id'])) {
                $gw = $this->WhatsappProjectGateway->findById((int)$pSetting['whatsapp_gateway_id']);
                if (!empty($gw['WhatsappProjectGateway']['phone_number_id']) && !empty($gw['WhatsappProjectGateway']['access_token'])) {
                    $phoneId = trim((string)$gw['WhatsappProjectGateway']['phone_number_id']);
                    $token = trim((string)$gw['WhatsappProjectGateway']['access_token']);
                    if (!empty($gw['WhatsappProjectGateway']['api_version'])) {
                        $apiVersion = trim((string)$gw['WhatsappProjectGateway']['api_version']);
                    }
                    $gatewayNameUsed = $gw['WhatsappProjectGateway']['gateway_name'];
                }
            }

            if ($phoneId === '' || $token === '') {
                return array('success' => false, 'message' => 'Phone Number ID or Access Token is missing in WhatsApp Settings.');
            }

            $cleanMobile = preg_replace('/[^0-9]/', '', (string)$toMobile);
            if (substr($cleanMobile, 0, 1) === '0') {
                $cleanMobile = '92' . substr($cleanMobile, 1);
            }

            $endpoint = "https://graph.facebook.com/" . urlencode($apiVersion) . "/" . urlencode($phoneId) . "/messages";
            $payload = array(
                'messaging_product' => 'whatsapp',
                'recipient_type' => 'individual',
                'to' => $cleanMobile,
                'type' => 'text',
                'text' => array('preview_url' => false, 'body' => $messageBody)
            );

            $ch = curl_init($endpoint);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $token, 'Content-Type: application/json'));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);

            $resp = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $json = @json_decode($resp, true);
            if ($httpCode >= 200 && $httpCode < 300 && !empty($json['messages'][0]['id'])) {
                $metaMsgId = $json['messages'][0]['id'];
                $this->WhatsappLog->create();
                $this->WhatsappLog->save(array('WhatsappLog' => array(
                    'recipient_mobile' => $cleanMobile,
                    'recipient_name' => $recipientName,
                    'recipient_type' => 'Client',
                    'message_category' => $category,
                    'project_name' => 'Project #' . $projectId,
                    'message_body' => $messageBody,
                    'gateway_name' => $gatewayNameUsed,
                    'meta_message_id' => $metaMsgId,
                    'status' => 'Sent',
                    'created' => date('Y-m-d H:i:s')
                )));
                return array('success' => true, 'meta_id' => $metaMsgId);
            } else {
                $errMsg = !empty($json['error']['message']) ? $json['error']['message'] : ('HTTP ' . $httpCode . ': ' . $resp);
                return array('success' => false, 'message' => $errMsg);
            }
        } catch(Exception $e) {
            return array('success' => false, 'message' => $e->getMessage());
        }
    }
}
