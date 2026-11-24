<?php
App::uses('AdminAppController', 'Admin.Controller');
App::uses('CakeEmail', 'Network/Email');

class FacebookIntegrationsController extends AdminAppController
{
    public $helpers = array('Html', 'Form', 'Session', 'Paginator', 'Time');
    public $uses = array(
        'FacebookAccount',
        'FacebookSetting',
        'FacebookAgentAssignment',
        'FacebookFormMapping',
        'FacebookLeadLog',
        'FacebookNotificationLog',
        'WhatsappSetting',
        'WhatsappLog',
        'WhatsappProjectGateway',
        'Lead',
        'Client',
        'User',
        'Project',
        'Status'
    );

    public function beforeFilter()
    {
        $this->_ensureBackendTimezone();
        $this->_ensureFacebookIntegrationSchema();
        $currAction = strtolower((string)$this->request->params['action']);
        $cleanAction = preg_replace('/^admin_/', '', $currAction);

        if(in_array($cleanAction, array('webhook', 'cron_autofetch', 'test_connection', 'test_account_token', 'save_account_ajax', 'connect_page_ajax', 'get_app_config_ajax'), true) || ($this->request->is('ajax') && in_array($cleanAction, array('send_notification', 'bulk_notifications', 'resend_notification', 'resend_all_failed'), true)))
        {
            $this->autoRender = false;
            $this->layout = false;
            if(isset($this->Security))
            {
                $this->Security->validatePost = false;
                $this->Security->csrfCheck = false;
            }
            return;
        }

        parent::beforeFilter();
        $this->plugin = 'Admin';
        $this->layout = 'Admin.default';
    }

    public function beforeRender()
    {
        $currAction = strtolower((string)$this->request->params['action']);
        $cleanAction = preg_replace('/^admin_/', '', $currAction);
        if(in_array($cleanAction, array('webhook', 'cron_autofetch', 'test_connection', 'test_account_token', 'save_account_ajax', 'connect_page_ajax', 'get_app_config_ajax'), true) || ($this->request->is('ajax') && in_array($cleanAction, array('send_notification', 'bulk_notifications', 'resend_notification', 'resend_all_failed'), true)))
        {
            return;
        }
        parent::beforeRender();
        $this->plugin = 'Admin';
        $this->layout = 'Admin.default';
    }




    public function admin_whatsapp()
    {
        return $this->whatsapp();
    }

    public function whatsapp()
    {
        $waSettings = $this->WhatsappSetting->findById(1);
        if(empty($waSettings))
        {
            $this->WhatsappSetting->create();
            $this->WhatsappSetting->save(array(
                'WhatsappSetting' => array(
                    'id' => 1,
                    'is_active' => 1,
                    'api_version' => 'v19.0',
                    'notify_fb_agent' => 1,
                    'notify_fb_client' => 1,
                    'notify_deals' => 1,
                    'notify_letters' => 1,
                    'notify_followups' => 1,
                    'created' => date('Y-m-d H:i:s'),
                    'modified' => date('Y-m-d H:i:s')
                )
            ));
            $waSettings = $this->WhatsappSetting->findById(1);
        }

        // Calculate KPIs
        $totalSent = !empty($waSettings['WhatsappSetting']['total_sent']) ? (int)$waSettings['WhatsappSetting']['total_sent'] : 0;
        $totalFailed = !empty($waSettings['WhatsappSetting']['total_failed']) ? (int)$waSettings['WhatsappSetting']['total_failed'] : 0;
        
        $todayStart = date('Y-m-d 00:00:00');
        $todaySent = $this->WhatsappLog->find('count', array(
            'conditions' => array('WhatsappLog.created >=' => $todayStart, 'WhatsappLog.status' => 'Sent')
        ));
        
        $agentAlertsCount = $this->WhatsappLog->find('count', array(
            'conditions' => array('WhatsappLog.message_category' => 'FB_LEAD_AGENT')
        ));
        
        $clientWelcomeCount = $this->WhatsappLog->find('count', array(
            'conditions' => array('WhatsappLog.message_category' => 'FB_LEAD_CLIENT')
        ));

        $dealsCount = $this->WhatsappLog->find('count', array(
            'conditions' => array('WhatsappLog.message_category' => 'DEAL_MILESTONE')
        ));

        $lettersCount = $this->WhatsappLog->find('count', array(
            'conditions' => array('WhatsappLog.message_category' => 'OFFICIAL_LETTER')
        ));

        $recentLogs = $this->WhatsappLog->find('all', array(
            'limit' => 35,
            'order' => array('WhatsappLog.id' => 'DESC'),
            'recursive' => -1
        ));

        $cfg = ClassRegistry::init('Configuration')->findById(1);
        $accounts = $this->FacebookAccount->find('all', array('conditions' => array('FacebookAccount.is_active' => 1), 'recursive' => -1));

        $projectGateways = $this->WhatsappProjectGateway->find('all', array('order' => array('WhatsappProjectGateway.id' => 'ASC')));
        $projects = $this->Project->find('all', array('fields' => array('Project.id', 'Project.name', 'Project.whatsapp_gateway_id'), 'order' => array('Project.name' => 'ASC')));
        $this->set(compact('waSettings', 'totalSent', 'totalFailed', 'todaySent', 'agentAlertsCount', 'clientWelcomeCount', 'dealsCount', 'lettersCount', 'recentLogs', 'cfg', 'accounts', 'projectGateways', 'projects'));
    }

    public function admin_save_whatsapp()
    {
        return $this->save_whatsapp();
    }

    public function save_whatsapp()
    {
        if($this->request->is(array('post', 'put')))
        {
            $data = $this->request->data;
            $savePayload = array(
                'id' => 1,
                'is_active' => !empty($data['is_active']) ? 1 : 0,
                'phone_number_id' => isset($data['phone_number_id']) ? trim($data['phone_number_id']) : '',
                'waba_id' => isset($data['waba_id']) ? trim($data['waba_id']) : '',
                'access_token' => isset($data['access_token']) ? trim($data['access_token']) : '',
                'api_version' => !empty($data['api_version']) ? trim($data['api_version']) : 'v19.0',
                'notify_fb_agent' => !empty($data['notify_fb_agent']) ? 1 : 0,
                'notify_fb_client' => !empty($data['notify_fb_client']) ? 1 : 0,
                'notify_deals' => !empty($data['notify_deals']) ? 1 : 0,
                'notify_letters' => !empty($data['notify_letters']) ? 1 : 0,
                'notify_followups' => !empty($data['notify_followups']) ? 1 : 0,
                'template_fb_agent' => isset($data['template_fb_agent']) ? trim($data['template_fb_agent']) : '',
                'template_fb_client' => isset($data['template_fb_client']) ? trim($data['template_fb_client']) : '',
                'template_deals' => isset($data['template_deals']) ? trim($data['template_deals']) : '',
                'template_letters' => isset($data['template_letters']) ? trim($data['template_letters']) : '',
                'template_followups' => isset($data['template_followups']) ? trim($data['template_followups']) : '',
                'modified' => date('Y-m-d H:i:s')
            );

            $this->WhatsappSetting->save($savePayload);
            $this->Session->setFlash(__('Meta WhatsApp Cloud API Settings & Notification Rules saved successfully!'), 'flash', array('alert' => 'success'));
            return $this->redirect(array('action' => 'whatsapp'));
        }
        return $this->redirect(array('action' => 'whatsapp'));
    }


    public function admin_save_project_gateway()
    {
        return $this->save_project_gateway();
    }

    public function save_project_gateway()
    {
        if($this->request->is(array('post', 'put')))
        {
            $data = $this->request->data['WhatsappProjectGateway'];
            $id = !empty($data['id']) ? (int)$data['id'] : null;

            $saveData = array(
                'gateway_name' => !empty($data['gateway_name']) ? trim($data['gateway_name']) : 'Project WhatsApp Gateway',
                'project_id' => !empty($data['project_id']) ? (int)$data['project_id'] : null,
                'facebook_account_id' => !empty($data['facebook_account_id']) ? (int)$data['facebook_account_id'] : null,
                'sender_phone_number' => !empty($data['sender_phone_number']) ? trim($data['sender_phone_number']) : '',
                'phone_number_id' => !empty($data['phone_number_id']) ? trim($data['phone_number_id']) : '',
                'waba_id' => !empty($data['waba_id']) ? trim($data['waba_id']) : '',
                'access_token' => !empty($data['access_token']) ? trim($data['access_token']) : '',
                'api_version' => !empty($data['api_version']) ? trim($data['api_version']) : 'v19.0',
                'is_active' => isset($data['is_active']) ? (int)$data['is_active'] : 1,
                'modified' => date('Y-m-d H:i:s')
            );

            if($id)
            {
                $this->WhatsappProjectGateway->id = $id;
            }
            else
            {
                $this->WhatsappProjectGateway->create();
                $saveData['created'] = date('Y-m-d H:i:s');
            }

            if($this->WhatsappProjectGateway->save($saveData))
            {
                $this->Session->setFlash(__('Project/Agency WhatsApp Gateway saved successfully!'), 'flash', array('alert' => 'success'));
            }
            else
            {
                $this->Session->setFlash(__('Could not save Project WhatsApp Gateway.'), 'flash', array('alert' => 'danger'));
            }
        }
        return $this->redirect(array('action' => 'whatsapp'));
    }

    public function admin_delete_project_gateway($id = null)
    {
        return $this->delete_project_gateway($id);
    }

    public function delete_project_gateway($id = null)
    {
        if($id)
        {
            $this->WhatsappProjectGateway->delete($id);
            $this->Session->setFlash(__('Project WhatsApp Gateway deleted successfully!'), 'flash', array('alert' => 'success'));
        }
        return $this->redirect(array('action' => 'whatsapp'));
    }

    public function admin_save_gateway_mapping()
    {
        return $this->save_gateway_mapping();
    }

    public function save_gateway_mapping()
    {
        if($this->request->is(array('post', 'put')))
        {
            // Update projects gateway mapping
            if(!empty($this->request->data['ProjectGatewayMapping']))
            {
                foreach($this->request->data['ProjectGatewayMapping'] as $pId => $gwId)
                {
                    $gwVal = ($gwId !== '' && (int)$gwId > 0) ? (int)$gwId : null;
                    $this->Project->id = (int)$pId;
                    $this->Project->saveField('whatsapp_gateway_id', $gwVal);
                }
            }

            // Update facebook accounts gateway mapping
            if(!empty($this->request->data['AccountGatewayMapping']))
            {
                foreach($this->request->data['AccountGatewayMapping'] as $accId => $gwId)
                {
                    $gwVal = ($gwId !== '' && (int)$gwId > 0) ? (int)$gwId : null;
                    $this->FacebookAccount->id = (int)$accId;
                    $this->FacebookAccount->saveField('whatsapp_gateway_id', $gwVal);
                }
            }

            $this->Session->setFlash(__('Project & Page WhatsApp Gateway routing mappings updated!'), 'flash', array('alert' => 'success'));
        }
        return $this->redirect(array('action' => 'whatsapp'));
    }
    public function send_test_whatsapp()
    {
        $this->autoRender = false;
        header('Content-Type: application/json');

        $toMobile = !empty($this->request->data['phone']) ? trim($this->request->data['phone']) : '';
        $customMsg = !empty($this->request->data['message']) ? trim($this->request->data['message']) : 'Hello! This is a test WhatsApp message from White Villas CRM Meta Cloud API.';

        if($toMobile === '')
        {
            echo json_encode(array('success' => false, 'message' => 'Please enter a valid recipient mobile number.'));
            exit();
        }

        $res = $this->_sendMetaWhatsAppMessage($toMobile, $customMsg, 'TEST', 'Test Recipient', 'Admin', 'White Villas Portal', 'System Test');
        echo json_encode($res);
        exit();
    }

    public function _sendMetaWhatsAppMessage($toMobile, $messageBody, $category = 'FB_LEAD', $recipientName = '', $recipientType = 'Client', $pageName = '', $projectName = '', $projectId = null, $accountId = null)
    {
        try
        {
            $wa = $this->WhatsappSetting->findById(1);
            if(empty($wa['WhatsappSetting']) || empty($wa['WhatsappSetting']['is_active']))
            {
                return array('success' => false, 'message' => 'WhatsApp API Gateway is disabled in settings.');
            }

            $s = $wa['WhatsappSetting'];
            $phoneId = trim((string)$s['phone_number_id']);
            $token = trim((string)$s['access_token']);
            $apiVersion = !empty($s['api_version']) ? trim($s['api_version']) : 'v19.0';
            $gatewayNameUsed = 'Master Agency Gateway';

            // 1. Check if Project or Account has an Isolated Dedicated WhatsApp Gateway
            $matchedGateway = null;

            if(!empty($projectId))
            {
                $proj = $this->Project->findById((int)$projectId);
                if(!empty($proj['Project']['whatsapp_gateway_id']))
                {
                    $matchedGateway = $this->WhatsappProjectGateway->findById((int)$proj['Project']['whatsapp_gateway_id']);
                }
                if(empty($matchedGateway))
                {
                    $matchedGateway = $this->WhatsappProjectGateway->find('first', array(
                        'conditions' => array('WhatsappProjectGateway.project_id' => (int)$projectId, 'WhatsappProjectGateway.is_active' => 1)
                    ));
                }
            }

            if(empty($matchedGateway) && !empty($accountId))
            {
                $acc = $this->FacebookAccount->findById((int)$accountId);
                if(!empty($acc['FacebookAccount']['whatsapp_gateway_id']))
                {
                    $matchedGateway = $this->WhatsappProjectGateway->findById((int)$acc['FacebookAccount']['whatsapp_gateway_id']);
                }
                if(empty($matchedGateway))
                {
                    $matchedGateway = $this->WhatsappProjectGateway->find('first', array(
                        'conditions' => array('WhatsappProjectGateway.facebook_account_id' => (int)$accountId, 'WhatsappProjectGateway.is_active' => 1)
                    ));
                }
            }

            if(empty($matchedGateway) && !empty($pageName))
            {
                $matchedGateway = $this->WhatsappProjectGateway->find('first', array(
                    'joins' => array(
                        array(
                            'table' => 'projects',
                            'alias' => 'Project',
                            'type' => 'LEFT',
                            'conditions' => array('Project.id = WhatsappProjectGateway.project_id')
                        )
                    ),
                    'conditions' => array(
                        'WhatsappProjectGateway.is_active' => 1,
                        'OR' => array(
                            'WhatsappProjectGateway.gateway_name LIKE' => "%$pageName%",
                            'Project.name LIKE' => "%$pageName%"
                        )
                    ),
                    'recursive' => -1
                ));
            }

            // If an isolated gateway was resolved, override credentials
            if(!empty($matchedGateway['WhatsappProjectGateway']['phone_number_id']) && !empty($matchedGateway['WhatsappProjectGateway']['access_token']))
            {
                $gw = $matchedGateway['WhatsappProjectGateway'];
                $phoneId = trim((string)$gw['phone_number_id']);
                $token = trim((string)$gw['access_token']);
                if(!empty($gw['api_version'])) $apiVersion = trim((string)$gw['api_version']);
                $gatewayNameUsed = $gw['gateway_name'] . (!empty($gw['sender_phone_number']) ? ' (' . $gw['sender_phone_number'] . ')' : '');
            }

            if($phoneId === '' || $token === '')
            {
                // Fallback / simulated logger if credentials not yet entered
                $this->WhatsappLog->create();
                $this->WhatsappLog->save(array('WhatsappLog' => array(
                    'recipient_mobile' => $toMobile,
                    'recipient_name' => $recipientName,
                    'recipient_type' => $recipientType,
                    'message_category' => $category,
                    'page_name' => $pageName,
                    'project_name' => $projectName,
                    'message_body' => $messageBody,
                    'gateway_name' => $gatewayNameUsed,
                    'status' => 'Pending Setup',
                    'error_message' => 'Phone Number ID or Access Token is missing in WhatsApp Settings.',
                    'created' => date('Y-m-d H:i:s')
                )));

                return array('success' => false, 'message' => 'Please configure Phone Number ID and Access Token in WhatsApp Settings.');
            }

            // Clean mobile number to international format without + or dashes
            $cleanMobile = preg_replace('/[^0-9]/', '', (string)$toMobile);
            if(substr($cleanMobile, 0, 1) === '0')
            {
                $cleanMobile = '92' . substr($cleanMobile, 1);
            }

            $endpoint = "https://graph.facebook.com/" . urlencode($apiVersion) . "/" . urlencode($phoneId) . "/messages";

            $payload = array(
                'messaging_product' => 'whatsapp',
                'recipient_type' => 'individual',
                'to' => $cleanMobile,
                'type' => 'text',
                'text' => array(
                    'preview_url' => false,
                    'body' => $messageBody
                )
            );

            $ch = curl_init($endpoint);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Authorization: Bearer ' . $token,
                'Content-Type: application/json'
            ));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);

            $resp = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $json = @json_decode($resp, true);

            if($httpCode >= 200 && $httpCode < 300 && !empty($json['messages'][0]['id']))
            {
                $metaMsgId = $json['messages'][0]['id'];
                
                // Log Success
                $this->WhatsappLog->create();
                $this->WhatsappLog->save(array('WhatsappLog' => array(
                    'recipient_mobile' => $cleanMobile,
                    'recipient_name' => $recipientName,
                    'recipient_type' => $recipientType,
                    'message_category' => $category,
                    'page_name' => $pageName,
                    'project_name' => $projectName,
                    'message_body' => $messageBody,
                    'gateway_name' => $gatewayNameUsed,
                    'meta_message_id' => $metaMsgId,
                    'status' => 'Sent',
                    'error_message' => null,
                    'created' => date('Y-m-d H:i:s')
                )));

                // Increment total_sent
                $this->WhatsappSetting->updateAll(
                    array('WhatsappSetting.total_sent' => 'WhatsappSetting.total_sent + 1'),
                    array('WhatsappSetting.id' => 1)
                );

                return array('success' => true, 'message' => 'WhatsApp message sent successfully!', 'meta_id' => $metaMsgId);
            }
            else
            {
                $errMsg = !empty($json['error']['message']) ? $json['error']['message'] : ('HTTP Error ' . $httpCode . ': ' . $resp);
                
                // Log Failure
                $this->WhatsappLog->create();
                $this->WhatsappLog->save(array('WhatsappLog' => array(
                    'recipient_mobile' => $cleanMobile,
                    'recipient_name' => $recipientName,
                    'recipient_type' => $recipientType,
                    'message_category' => $category,
                    'page_name' => $pageName,
                    'project_name' => $projectName,
                    'message_body' => $messageBody,
                    'gateway_name' => $gatewayNameUsed,
                    'meta_message_id' => null,
                    'status' => 'Failed',
                    'error_message' => $errMsg,
                    'created' => date('Y-m-d H:i:s')
                )));

                // Increment total_failed
                $this->WhatsappSetting->updateAll(
                    array('WhatsappSetting.total_failed' => 'WhatsappSetting.total_failed + 1'),
                    array('WhatsappSetting.id' => 1)
                );

                return array('success' => false, 'message' => $errMsg);
            }
        }
        catch(Exception $e)
        {
            CakeLog::write('error', 'Meta WhatsApp Cloud API Exception: ' . $e->getMessage());
            return array('success' => false, 'message' => $e->getMessage());
        }
    }
    public function admin_notifications()
    {
        return $this->notifications();
    }

    public function notifications()
    {
        $fbSettings = $this->_ensureFacebookSettings();
        $cfg = ClassRegistry::init('Configuration')->findById(1);
        $defaultAgentTemplate = $this->_getDefaultAgentEmailTemplate();
        $defaultClientTemplate = $this->_getDefaultClientEmailTemplate();
        $this->set(compact('fbSettings', 'cfg', 'defaultAgentTemplate', 'defaultClientTemplate'));
    }

    public function admin_docs()
    {
        return $this->docs();
    }

    public function docs()
    {
        $webhookUrl = Router::url(array('plugin' => 'admin', 'controller' => 'facebook_integrations', 'action' => 'webhook'), true);
        $cronUrl = Router::url(array('plugin' => 'admin', 'controller' => 'facebook_integrations', 'action' => 'cron_autofetch'), true);
        $this->set(compact('webhookUrl', 'cronUrl'));
    }

    public function admin_index()
    {
        return $this->index();
    }

    public function index()
    {
        $this->_ensureAllFacebookLogsInCrmLeads();

        $accounts = $this->FacebookAccount->find('all', array(
            'order' => array('FacebookAccount.id' => 'ASC'),
            'recursive' => 0
        ));
        
        $totalFbLeads = $this->Lead->find('count', array(
            'conditions' => array(
                'OR' => array(
                    array('Lead.lead_source LIKE' => '%Facebook%'),
                    array('Lead.lead_source LIKE' => '%FB%')
                )
            ),
            'recursive' => -1
        ));

        $todayFbLeads = $this->Lead->find('count', array(
            'conditions' => array(
                'Lead.created >=' => date('Y-m-d 00:00:00'),
                'OR' => array(
                    array('Lead.lead_source LIKE' => '%Facebook%'),
                    array('Lead.lead_source LIKE' => '%FB%')
                )
            ),
            'recursive' => -1
        ));

        $activeAgentsCount = $this->FacebookAgentAssignment->find('count', array(
            'conditions' => array('FacebookAgentAssignment.is_active' => 1)
        ));

        $activeAccountsCount = $this->FacebookAccount->find('count', array(
            'conditions' => array('FacebookAccount.is_active' => 1)
        ));

        $isAgentUser = (strtoupper(trim((string)$this->userType)) === 'AGT');
        $selectedAgentFilter = 0;
        if($isAgentUser && (int)$this->luserId > 0)
        {
            $selectedAgentFilter = (int)$this->luserId;
        }
        elseif(isset($this->request->query['agent_id']) && $this->request->query['agent_id'] !== 'all')
        {
            $selectedAgentFilter = (int)$this->request->query['agent_id'];
        }
        elseif(isset($this->request->params['named']['agent_id']) && $this->request->params['named']['agent_id'] !== 'all')
        {
            $selectedAgentFilter = (int)$this->request->params['named']['agent_id'];
        }

        $logConditions = array('FacebookLeadLog.lead_id >' => 0);
        if($selectedAgentFilter > 0)
        {
            $logConditions['FacebookLeadLog.assigned_user_id'] = $selectedAgentFilter;
        }

        $recentLogs = $this->FacebookLeadLog->find('all', array(
            'conditions' => $logConditions,
            'order' => array('FacebookLeadLog.id' => 'DESC'),
            'recursive' => 0
        ));

        // Get per-agent lead counts for overview filter pills & statistics
        $agentCountsRaw = $this->FacebookLeadLog->find('all', array(
            'conditions' => array('FacebookLeadLog.lead_id >' => 0),
            'fields' => array('FacebookLeadLog.assigned_user_id', 'COUNT(FacebookLeadLog.id) as count'),
            'group' => array('FacebookLeadLog.assigned_user_id'),
            'recursive' => -1
        ));
        $agentLeadCounts = array();
        foreach($agentCountsRaw as $acr)
        {
            $aId = !empty($acr['FacebookLeadLog']['assigned_user_id']) ? (int)$acr['FacebookLeadLog']['assigned_user_id'] : 0;
            $cnt = !empty($acr[0]['count']) ? (int)$acr[0]['count'] : 0;
            $agentLeadCounts[$aId] = $cnt;
        }

        $projects = $this->Project->find('list', array('fields' => array('Project.id', 'Project.name')));
        $statuses = $this->Status->find('list', array('fields' => array('Status.id', 'Status.name')));
        
        $allAgents = $this->_getFormattedAgentList();

        $this->set(compact('accounts', 'totalFbLeads', 'todayFbLeads', 'activeAgentsCount', 'activeAccountsCount', 'recentLogs', 'projects', 'statuses', 'allAgents', 'agentLeadCounts', 'selectedAgentFilter', 'isAgentUser'));
    }

    public function admin_settings()
    {
        return $this->settings();
    }

    public function settings()
    {
        $accounts = $this->FacebookAccount->find('all', array(
            'order' => array('FacebookAccount.id' => 'ASC'),
            'recursive' => 0
        ));

        // Dynamically calculate and sync accurate synced leads count for each account
        $totalAccounts = count($accounts);
        $totalAllFbLeadsInCrm = (int)$this->Lead->find('count', array(
            'conditions' => array(
                'OR' => array(
                    array('Lead.lead_source LIKE' => '%Facebook%'),
                    array('Lead.lead_source LIKE' => '%FB%')
                )
            ),
            'recursive' => -1
        ));

        foreach($accounts as &$acc)
        {
            $accId = (int)$acc['FacebookAccount']['id'];
            $pageId = trim((string)$acc['FacebookAccount']['page_id']);
            $projId = !empty($acc['FacebookAccount']['project_id']) ? (int)$acc['FacebookAccount']['project_id'] : 0;
            
            // 1. First check distinct leads in FacebookLeadLog by page_id
            $leadsCount = 0;
            if($pageId !== '')
            {
                $leadsCount = (int)$this->FacebookLeadLog->find('count', array(
                    'conditions' => array('FacebookLeadLog.page_id' => $pageId),
                    'recursive' => -1
                ));
            }

            // 2. If no logs by page_id or fewer than project leads, check Lead by project_id
            if($projId > 0)
            {
                $projLeadsCount = (int)$this->Lead->find('count', array(
                    'conditions' => array(
                        'Lead.project_id' => $projId,
                        'OR' => array(
                            array('Lead.lead_source LIKE' => '%Facebook%'),
                            array('Lead.lead_source LIKE' => '%FB%')
                        )
                    ),
                    'recursive' => -1
                ));
                if($projLeadsCount > $leadsCount)
                {
                    $leadsCount = $projLeadsCount;
                }
            }

            // 3. If there is only 1 account, match all Facebook leads in CRM (same as Dashboard)
            if($totalAccounts === 1 && $totalAllFbLeadsInCrm > $leadsCount)
            {
                $leadsCount = $totalAllFbLeadsInCrm;
            }

            // Take highest between computed count and stored total_leads_synced
            $finalCount = max($leadsCount, (int)$acc['FacebookAccount']['total_leads_synced']);

            if($finalCount !== (int)$acc['FacebookAccount']['total_leads_synced'])
            {
                $this->FacebookAccount->updateAll(
                    array('FacebookAccount.total_leads_synced' => $finalCount),
                    array('FacebookAccount.id' => $accId)
                );
            }

            $acc['FacebookAccount']['total_leads_synced'] = $finalCount;
        }
        unset($acc);

        $projects = $this->Project->find('list', array('fields' => array('Project.id', 'Project.name'), 'order' => array('Project.name' => 'ASC')));
        $statuses = $this->Status->find('list', array('fields' => array('Status.id', 'Status.name'), 'order' => array('Status.name' => 'ASC')));

        $webhookUrl = Router::url(array('plugin' => 'admin', 'controller' => 'facebook_integrations', 'action' => 'webhook'), true);
        $cronUrl = Router::url(array('plugin' => 'admin', 'controller' => 'facebook_integrations', 'action' => 'cron_autofetch'), true);

        $fbSettings = $this->_ensureFacebookSettings();
        $this->set(compact('accounts', 'projects', 'statuses', 'webhookUrl', 'cronUrl', 'fbSettings'));
    }

    public function admin_save_account()
    {
        return $this->save_account();
    }

    public function save_account()
    {
        $this->autoRender = false;
        if($this->request->is(array('post', 'put')))
        {
            $data = $this->request->data['FacebookAccount'];
            $accountId = !empty($data['id']) ? (int)$data['id'] : null;
            $rawToken = !empty($data['page_access_token']) ? trim($data['page_access_token']) : '';
            $pageId = !empty($data['page_id']) ? trim($data['page_id']) : '';

            // Automatically resolve User Token to dedicated Page Access Token
            if($rawToken !== '' && $pageId !== '' && $pageId !== 'me')
            {
                $resolvedToken = $this->_resolvePageAccessToken($pageId, $rawToken);
                $data['page_access_token'] = $resolvedToken;

                // Auto-fetch verified Page Name from Meta
                $infoUrl = "https://graph.facebook.com/v19.0/" . urlencode($pageId) . "?fields=name,id&access_token=" . urlencode($resolvedToken);
                $ch = curl_init($infoUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                $infoResp = curl_exec($ch);
                curl_close($ch);
                $infoJson = @json_decode($infoResp, true);
                if(!empty($infoJson['name']))
                {
                    $data['page_name'] = $infoJson['name'];
                    if(empty($data['account_name']))
                    {
                        $data['account_name'] = $infoJson['name'];
                    }
                }

                // Automatically subscribe the page to Leadgen Webhooks
                $subUrl = "https://graph.facebook.com/v19.0/" . urlencode($pageId) . "/subscribed_apps?subscribed_fields=leadgen&access_token=" . urlencode($resolvedToken);
                $ch = curl_init($subUrl);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                curl_exec($ch);
                curl_close($ch);
            }

            if(!isset($data['is_active']) || $data['is_active'] === '')
            {
                $data['is_active'] = 1;
            }

            if($accountId)
            {
                $this->FacebookAccount->id = $accountId;
                $data['modified'] = date('Y-m-d H:i:s');
            }
            else
            {
                $this->FacebookAccount->create();
                $data['created'] = date('Y-m-d H:i:s');
                $data['modified'] = date('Y-m-d H:i:s');
                if(empty($data['webhook_verify_token']))
                {
                    $data['webhook_verify_token'] = 'whitevillas_fb_verify_token_2026';
                }
            }

            if($this->FacebookAccount->save($data))
            {
                $this->Session->setFlash(__('Facebook Page saved & auto-configured with Page Access Token & Webhooks!'), 'flash', array('alert' => 'success'));
            }
            else
            {
                $this->Session->setFlash(__('Could not save Facebook account. Please check required fields.'), 'flash', array('alert' => 'danger'));
            }
        }
        return $this->redirect(array('action' => 'settings'));
    }

    
    public function delete_account($id = null)
    {
        return $this->admin_delete_account($id);
    }
    public function admin_delete_account($id = null)
    {
        $this->autoRender = false;
        if(!$id)
        {
            $this->Session->setFlash(__('Invalid account ID.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'settings'));
        }
        $this->FacebookAccount->id = (int)$id;
        if($this->FacebookAccount->delete())
        {
            $this->Session->setFlash(__('Facebook account deleted successfully.'), 'flash', array('alert' => 'success'));
        }
        else
        {
            $this->Session->setFlash(__('Unable to delete account.'), 'flash', array('alert' => 'danger'));
        }
        return $this->redirect(array('action' => 'settings'));
    }

    
    public function toggle_account($id = null)
    {
        return $this->admin_toggle_account($id);
    }
    public function admin_toggle_account($id = null)
    {
        $this->autoRender = false;
        if(!$id)
        {
            return $this->redirect(array('action' => 'settings'));
        }
        $account = $this->FacebookAccount->findById((int)$id);
        if(!empty($account))
        {
            $newStatus = !empty($account['FacebookAccount']['is_active']) ? 0 : 1;
            $this->FacebookAccount->id = (int)$id;
            $this->FacebookAccount->saveField('is_active', $newStatus);
            $this->Session->setFlash(__('Account status updated successfully.'), 'flash', array('alert' => 'success'));
        }
        return $this->redirect(array('action' => 'settings'));
    }

    public function test_account_token()
    {
        $this->autoRender = false;
        header('Content-Type: application/json');

        $rawToken = !empty($this->request->data['page_access_token']) ? trim($this->request->data['page_access_token']) : '';
        $pageId = !empty($this->request->data['page_id']) ? trim($this->request->data['page_id']) : 'me';

        if($rawToken === '')
        {
            echo json_encode(array('success' => false, 'message' => 'Please provide a token to test.'));
            exit();
        }

        // Auto-resolve User Token to Page Token
        $token = ($pageId !== '' && $pageId !== 'me') ? $this->_resolvePageAccessToken($pageId, $rawToken) : $rawToken;

        $url = "https://graph.facebook.com/v19.0/" . urlencode($pageId) . "?fields=id,name,category,link&access_token=" . urlencode($token);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $json = @json_decode($response, true);
        if($httpCode == 200 && !empty($json['id']))
        {
            $pageName = !empty($json['name']) ? $json['name'] : 'Active Page';
            
            // Check debug token permissions
            $debugUrl = "https://graph.facebook.com/v19.0/debug_token?input_token=" . urlencode($token) . "&access_token=" . urlencode($token);
            $ch = curl_init($debugUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $debugResp = curl_exec($ch);
            curl_close($ch);
            $debugJson = @json_decode($debugResp, true);
            
            $scopes = !empty($debugJson['data']['scopes']) ? (array)$debugJson['data']['scopes'] : array();
            $hasLeadsRetrieval = in_array('leads_retrieval', $scopes, true);
            $hasPageAds = in_array('pages_manage_ads', $scopes, true);

            $permWarning = "";
            if(!$hasLeadsRetrieval || !$hasPageAds)
            {
                $permWarning = " (Note: Add 'leads_retrieval' and 'pages_manage_ads' permissions in Meta Graph API Explorer for full lead downloads).";
            }

            echo json_encode(array(
                'success' => true,
                'resolved_token' => $token,
                'page_name' => $pageName,
                'page_id' => $json['id'],
                'message' => "✓ Connected to Meta Page: {$pageName} (ID: {$json['id']}){$permWarning}"
            ));
        }
        else
        {
            $err = !empty($json['error']['message']) ? $json['error']['message'] : "HTTP Error {$httpCode}";
            echo json_encode(array('success' => false, 'message' => $err));
        }
        exit();
    }

    public function admin_agents()
    {
        return $this->agents();
    }

    public function admin_resequence_agents()
    {
        return $this->resequence_agents();
    }

    public function resequence_agents()
    {
        $this->_resequenceAssignments();
        $this->Session->setFlash(__('Queue orders have been re-sequenced successfully (1, 2, 3...)!'), 'flash', array('alert' => 'success'));
        return $this->redirect(array('action' => 'agents'));
    }

    public function admin_delete_agent($id = null)
    {
        return $this->delete_agent($id);
    }

    public function delete_agent($id = null)
    {
        $id = (int)$id;
        if ($id > 0)
        {
            $this->FacebookAgentAssignment->delete($id);
            $this->Session->setFlash(__('Agent removed from looping queue successfully!'), 'flash', array('alert' => 'success'));
        }
        return $this->redirect(array('action' => 'agents'));
    }

    protected function _resequenceAssignments()
    {
        $assignments = $this->FacebookAgentAssignment->find('all', array(
            'order' => array('FacebookAgentAssignment.sort_order' => 'ASC', 'FacebookAgentAssignment.id' => 'ASC'),
            'recursive' => -1
        ));
        $num = 1;
        foreach ($assignments as $a)
        {
            $this->FacebookAgentAssignment->id = (int)$a['FacebookAgentAssignment']['id'];
            $this->FacebookAgentAssignment->saveField('sort_order', $num);
            $num++;
        }
    }

    public function agents()
    {
        // Auto-prune orphan assignments where user_id does not exist or is not active
        try {
            $this->FacebookAgentAssignment->query("DELETE FROM facebook_agent_assignments WHERE user_id NOT IN (SELECT id FROM users WHERE status = 'Active')");
        } catch (Exception $e) {}

        if($this->request->is('post'))
        {
            if(!empty($this->request->data['AgentToggle']))
            {
                $assignmentId = (int)$this->request->data['AgentToggle']['id'];
                $isActive = (int)$this->request->data['AgentToggle']['is_active'];
                $this->FacebookAgentAssignment->id = $assignmentId;
                $this->FacebookAgentAssignment->saveField('is_active', $isActive);
                $this->Session->setFlash(__('Agent status updated!'), 'flash', array('alert' => 'success'));
                return $this->redirect(array('action' => 'agents'));
            }

            if(!empty($this->request->data['EditAgent']['id']))
            {
                $assignmentId = (int)$this->request->data['EditAgent']['id'];
                $projectId = !empty($this->request->data['EditAgent']['project_id']) ? (int)$this->request->data['EditAgent']['project_id'] : null;
                $sortOrder = isset($this->request->data['EditAgent']['sort_order']) ? (int)$this->request->data['EditAgent']['sort_order'] : 1;
                $isActive = isset($this->request->data['EditAgent']['is_active']) ? (int)$this->request->data['EditAgent']['is_active'] : 1;

                $this->FacebookAgentAssignment->id = $assignmentId;
                $this->FacebookAgentAssignment->save(array(
                    'project_id' => $projectId,
                    'sort_order' => $sortOrder,
                    'is_active' => $isActive,
                    'modified' => date('Y-m-d H:i:s')
                ));
                $this->Session->setFlash(__('Agent loop settings updated successfully!'), 'flash', array('alert' => 'success'));
                return $this->redirect(array('action' => 'agents'));
            }

            if(!empty($this->request->data['DeleteAgent']['id']))
            {
                $assignmentId = (int)$this->request->data['DeleteAgent']['id'];
                $this->FacebookAgentAssignment->delete($assignmentId);
                $this->Session->setFlash(__('Agent removed from looping queue!'), 'flash', array('alert' => 'success'));
                return $this->redirect(array('action' => 'agents'));
            }

            if(!empty($this->request->data['Resequence']))
            {
                $this->_resequenceAssignments();
                $this->Session->setFlash(__('Queue orders have been re-sequenced successfully (1, 2, 3...)!'), 'flash', array('alert' => 'success'));
                return $this->redirect(array('action' => 'agents'));
            }

            if(!empty($this->request->data['AddAgent']['user_id']))
            {
                $userId = (int)$this->request->data['AddAgent']['user_id'];
                $projectId = !empty($this->request->data['AddAgent']['project_id']) ? (int)$this->request->data['AddAgent']['project_id'] : null;
                $sortOrder = !empty($this->request->data['AddAgent']['sort_order']) ? (int)$this->request->data['AddAgent']['sort_order'] : 0;

                $existing = $this->FacebookAgentAssignment->find('first', array(
                    'conditions' => array('FacebookAgentAssignment.user_id' => $userId)
                ));

                if(!empty($existing))
                {
                    $this->FacebookAgentAssignment->id = (int)$existing['FacebookAgentAssignment']['id'];
                    $this->FacebookAgentAssignment->save(array(
                        'is_active' => 1,
                        'project_id' => $projectId,
                        'sort_order' => $sortOrder,
                        'modified' => date('Y-m-d H:i:s')
                    ));
                }
                else
                {
                    $this->FacebookAgentAssignment->create();
                    $this->FacebookAgentAssignment->save(array(
                        'user_id' => $userId,
                        'project_id' => $projectId,
                        'is_active' => 1,
                        'sort_order' => $sortOrder,
                        'total_assigned_count' => 0,
                        'created' => date('Y-m-d H:i:s'),
                        'modified' => date('Y-m-d H:i:s')
                    ));
                }

                $this->Session->setFlash(__('Agent added to round-robin looping pool!'), 'flash', array('alert' => 'success'));
                return $this->redirect(array('action' => 'agents'));
            }
        }

        $assignments = $this->FacebookAgentAssignment->find('all', array(
            'conditions' => array(
                'User.id IS NOT NULL',
                'User.status' => 'Active'
            ),
            'order' => array('FacebookAgentAssignment.sort_order' => 'ASC', 'FacebookAgentAssignment.id' => 'ASC'),
            'recursive' => 0
        ));

        $allUsers = $this->_getFormattedAgentList();
        $projects = $this->Project->find('list', array('fields' => array('Project.id', 'Project.name')));

        $userAgencyMap = array();
        foreach($assignments as $a)
        {
            $aId = (int)$a['FacebookAgentAssignment']['id'];
            $uProj = !empty($a['User']['project_id']) ? (int)$a['User']['project_id'] : 0;
            $userAgencyMap[$aId] = !empty($projects[$uProj]) ? $projects[$uProj] : 'White Villas';
        }

        $this->set(compact('assignments', 'allUsers', 'projects', 'userAgencyMap'));
    }

    public function admin_forms()
    {
        return $this->forms();
    }

    public function forms()
    {
        if($this->request->is('post'))
        {
            if(!empty($this->request->data['FacebookFormMapping']))
            {
                $data = $this->request->data['FacebookFormMapping'];
                $formId = !empty($data['facebook_form_id']) ? trim($data['facebook_form_id']) : '';

                if($formId === '' && !empty($data['custom_form_id']))
                {
                    $formId = trim($data['custom_form_id']);
                    $data['facebook_form_id'] = $formId;
                }

                if($formId !== '')
                {
                    $existing = $this->FacebookFormMapping->find('first', array(
                        'conditions' => array('FacebookFormMapping.facebook_form_id' => $formId)
                    ));

                    if(!empty($existing['FacebookFormMapping']['id']))
                    {
                        $this->FacebookFormMapping->id = (int)$existing['FacebookFormMapping']['id'];
                        $data['modified'] = date('Y-m-d H:i:s');
                    }
                    else
                    {
                        $this->FacebookFormMapping->create();
                        $data['created'] = date('Y-m-d H:i:s');
                        $data['modified'] = date('Y-m-d H:i:s');
                        $data['is_active'] = 1;
                    }

                    if($this->FacebookFormMapping->save($data))
                    {
                        $this->Session->setFlash(__('Facebook form mapped successfully!'), 'flash', array('alert' => 'success'));
                        return $this->redirect(array('action' => 'forms'));
                    }
                }
                else
                {
                    $this->Session->setFlash(__('Please select or enter a valid Facebook Form ID.'), 'flash', array('alert' => 'danger'));
                }
            }
        }

        // Fetch all active forms from all linked Facebook pages
        $activeAccounts = $this->FacebookAccount->find('all', array(
            'conditions' => array('FacebookAccount.is_active' => 1),
            'recursive' => -1
        ));

        $availableForms = array();
        $formsMetaMap = array();

        // 1. Live Meta API Discovery
        foreach($activeAccounts as $acc)
        {
            $pageId = !empty($acc['FacebookAccount']['page_id']) ? $acc['FacebookAccount']['page_id'] : '';
            $pageName = !empty($acc['FacebookAccount']['account_name']) ? $acc['FacebookAccount']['account_name'] : 'Facebook Page';
            $token = !empty($acc['FacebookAccount']['page_access_token']) ? $acc['FacebookAccount']['page_access_token'] : '';

            if($pageId !== '' && $token !== '')
            {
                $resolvedToken = $this->_resolvePageAccessToken($pageId, $token);
                $formsUrl = "https://graph.facebook.com/v19.0/" . urlencode($pageId) . "/leadgen_forms?fields=id,name,status,leads_count&access_token=" . urlencode($resolvedToken);
                $ch = curl_init($formsUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_TIMEOUT, 8);
                $resp = curl_exec($ch);
                curl_close($ch);
                $json = @json_decode($resp, true);

                if(!empty($json['data']) && is_array($json['data']))
                {
                    foreach($json['data'] as $f)
                    {
                        if(!empty($f['id']))
                        {
                            $fName = !empty($f['name']) ? $f['name'] : 'Lead Form #' . $f['id'];
                            $availableForms[$pageName][$f['id']] = $fName . " (ID: " . $f['id'] . ")";
                            $formsMetaMap[$f['id']] = array(
                                'name' => $fName,
                                'page_id' => $pageId,
                                'page_name' => $pageName
                            );
                        }
                    }
                }
            }
        }

        // 2. Discover from Facebook Lead Logs (Always reliable)
        $knownLogs = $this->FacebookLeadLog->find('all', array(
            'fields' => array('DISTINCT FacebookLeadLog.form_id', 'FacebookLeadLog.form_name', 'FacebookLeadLog.page_id'),
            'conditions' => array('FacebookLeadLog.form_id IS NOT NULL', 'FacebookLeadLog.form_id !=' => ''),
            'recursive' => -1
        ));
        foreach($knownLogs as $kl)
        {
            $fId = $kl['FacebookLeadLog']['form_id'];
            $fName = !empty($kl['FacebookLeadLog']['form_name']) ? $kl['FacebookLeadLog']['form_name'] : 'Lead Form #' . $fId;
            $pgName = 'White Alpine Lodges';
            if(stripos($fName, 'ICE') !== false || stripos($fName, 'Cottage') !== false) {
                $pgName = 'White ICE Cottages';
            }
            if(!isset($formsMetaMap[$fId]))
            {
                $availableForms[$pgName][$fId] = $fName . " (ID: " . $fId . ")";
                $formsMetaMap[$fId] = array(
                    'name' => $fName,
                    'page_id' => $kl['FacebookLeadLog']['page_id'],
                    'page_name' => $pgName
                );
            }
        }

        // 3. Preloaded Catalog of Discovered Live Forms (Guaranteed Always Available)
        $catalogForms = array(
            'White ICE Cottages' => array(
                '419864743398043' => 'Plot or Cottage Booking Form',
                '1415106342586333' => 'Rent Offer',
                '881336549225622' => 'White ICE 3.5 Marla',
                '671367367203657' => '6Marla New Cottage Compaing',
                '568310655456652' => 'White ice 20/05/2023',
                '466766848360572' => 'Karachi Add',
                '399681851598922' => 'Book Cottage'
            ),
            'White Alpine Lodges' => array(
                '2038407180131077' => "White Alpine Lodges's form",
                '1648793206760172' => 'White Alpine Lodges Naran A'
            )
        );

        foreach($catalogForms as $pg => $fList)
        {
            foreach($fList as $fId => $fName)
            {
                if(!isset($formsMetaMap[$fId]))
                {
                    $availableForms[$pg][$fId] = $fName . " (ID: " . $fId . ")";
                    $formsMetaMap[$fId] = array(
                        'name' => $fName,
                        'page_id' => '',
                        'page_name' => $pg
                    );
                }
            }
        }

        $mappings = $this->FacebookFormMapping->find('all', array(
            'order' => array('FacebookFormMapping.id' => 'DESC'),
            'recursive' => 0
        ));

        $projects = $this->Project->find('list', array('fields' => array('Project.id', 'Project.name')));
        $statuses = $this->Status->find('list', array('fields' => array('Status.id', 'Status.name')));
        $agents = $this->User->find('list', array('conditions' => array('User.status' => 'Active'), 'fields' => array('User.id', 'User.name')));

        $this->set(compact('mappings', 'projects', 'statuses', 'agents', 'availableForms', 'formsMetaMap'));
    }

    public function delete_form($id = null)
    {
        return $this->admin_delete_form($id);
    }
    public function admin_delete_form($id = null)
    {
        $this->autoRender = false;
        if($id)
        {
            $this->FacebookFormMapping->id = (int)$id;
            $this->FacebookFormMapping->delete();
            $this->Session->setFlash(__('Form mapping removed successfully.'), 'flash', array('alert' => 'success'));
        }
        return $this->redirect(array('action' => 'forms'));
    }

    public function admin_logs()
    {
        return $this->logs();
    }

    public function logs()
    {
        $this->Paginator->settings = array(
            'limit' => 25,
            'order' => array('FacebookLeadLog.id' => 'DESC'),
            'recursive' => 0
        );
        $logs = $this->Paginator->paginate('FacebookLeadLog');
        $allAgents = $this->_getFormattedAgentList();
        $this->set(compact('logs', 'allAgents'));
    }

    public function admin_notification_logs()
    {
        return $this->notification_logs();
    }

    public function notification_logs()
    {
        $conditions = array();

        $channel = !empty($this->request->query['channel']) ? strtolower(trim($this->request->query['channel'])) : 'all';
        if($channel !== 'all' && in_array($channel, array('email', 'sms'), true))
        {
            $conditions['FacebookNotificationLog.channel'] = $channel;
        }

        $status = !empty($this->request->query['status']) ? strtolower(trim($this->request->query['status'])) : 'all';
        if($status !== 'all' && in_array($status, array('success', 'failed'), true))
        {
            $conditions['FacebookNotificationLog.status'] = $status;
        }

        $recipientType = !empty($this->request->query['recipient_type']) ? strtolower(trim($this->request->query['recipient_type'])) : 'all';
        if($recipientType !== 'all')
        {
            $conditions['FacebookNotificationLog.recipient_type'] = $recipientType;
        }

        $q = !empty($this->request->query['q']) ? trim($this->request->query['q']) : '';
        if($q !== '')
        {
            $conditions['OR'] = array(
                'FacebookNotificationLog.lead_name LIKE' => "%{$q}%",
                'FacebookNotificationLog.lead_phone LIKE' => "%{$q}%",
                'FacebookNotificationLog.lead_email LIKE' => "%{$q}%",
                'FacebookNotificationLog.recipient_name LIKE' => "%{$q}%",
                'FacebookNotificationLog.recipient_target LIKE' => "%{$q}%",
                'FacebookNotificationLog.subject LIKE' => "%{$q}%"
            );
        }

        $this->Paginator->settings = array(
            'conditions' => $conditions,
            'limit' => 25,
            'order' => array('FacebookNotificationLog.id' => 'DESC'),
            'recursive' => -1
        );
        $logs = $this->Paginator->paginate('FacebookNotificationLog');

        // Calculate KPIs
        $totalLogs = $this->FacebookNotificationLog->find('count');
        $totalSuccessEmails = $this->FacebookNotificationLog->find('count', array(
            'conditions' => array('FacebookNotificationLog.channel' => 'email', 'FacebookNotificationLog.status' => 'success')
        ));
        $totalSuccessSms = $this->FacebookNotificationLog->find('count', array(
            'conditions' => array('FacebookNotificationLog.channel' => 'sms', 'FacebookNotificationLog.status' => 'success')
        ));
        $totalFailed = $this->FacebookNotificationLog->find('count', array(
            'conditions' => array('FacebookNotificationLog.status' => 'failed')
        ));

        $kpis = array(
            'total' => $totalLogs,
            'emails' => $totalSuccessEmails,
            'sms' => $totalSuccessSms,
            'failed' => $totalFailed
        );

        $this->set(compact('logs', 'kpis', 'channel', 'status', 'recipientType', 'q'));
    }

    public function admin_resend_notification($logId = null)
    {
        return $this->resend_notification($logId);
    }

    public function resend_notification($logId = null)
    {
        $logId = (int)$logId;
        if($logId <= 0)
        {
            if($this->request->is('ajax'))
            {
                $this->autoRender = false;
                echo json_encode(array('success' => false, 'message' => __('Invalid log ID.')));
                exit();
            }
            $this->Session->setFlash(__('Invalid log ID.'), 'flash', array('alert' => 'danger'));
            return $this->redirect($this->referer(array('action' => 'notification_logs')));
        }

        $log = $this->FacebookNotificationLog->findById($logId);
        if(empty($log['FacebookNotificationLog']))
        {
            if($this->request->is('ajax'))
            {
                $this->autoRender = false;
                echo json_encode(array('success' => false, 'message' => __('Notification log record not found.')));
                exit();
            }
            $this->Session->setFlash(__('Notification log record not found.'), 'flash', array('alert' => 'danger'));
            return $this->redirect($this->referer(array('action' => 'notification_logs')));
        }

        $entry = $log['FacebookNotificationLog'];
        $leadId = !empty($entry['lead_id']) ? (int)$entry['lead_id'] : 0;
        $channel = strtolower(trim((string)$entry['channel']));
        $target = trim((string)$entry['recipient_target']);
        $leadName = !empty($entry['lead_name']) ? $entry['lead_name'] : 'Lead';

        $resendSuccess = false;
        $resendMsg = '';

        $fbSettings = $this->FacebookSetting->findById(1);
        $s = !empty($fbSettings['FacebookSetting']) ? $fbSettings['FacebookSetting'] : array();
        $cfg = ClassRegistry::init('Configuration')->findById(1);
        $agencyEmail = !empty($cfg['Configuration']['email']) ? trim($cfg['Configuration']['email']) : 'info@whitevillas.com.pk';
        $agencyName = !empty($cfg['Configuration']['name']) ? trim($cfg['Configuration']['name']) : 'White Villas';

        if($channel === 'email')
        {
            if(empty($target) || !filter_var($target, FILTER_VALIDATE_EMAIL))
            {
                if($leadId > 0)
                {
                    $lead = $this->Lead->findById($leadId);
                    if(!empty($lead['Lead']['user_id']))
                    {
                        $u = $this->User->findById((int)$lead['Lead']['user_id']);
                        if(!empty($u['User']['email']) && filter_var(trim($u['User']['email']), FILTER_VALIDATE_EMAIL))
                        {
                            $target = trim($u['User']['email']);
                        }
                    }
                }
                if(empty($target) || !filter_var($target, FILTER_VALIDATE_EMAIL))
                {
                    $target = !empty($s['admin_notification_email']) ? trim($s['admin_notification_email']) : $agencyEmail;
                }
            }

            if(!empty($target) && filter_var($target, FILTER_VALIDATE_EMAIL))
            {
                if($leadId > 0)
                {
                    $dRes = $this->_dispatchFacebookLeadNotifications($leadId, null, null, null, null, null, 'email', 'admin_resend');
                    $resendSuccess = !empty($dRes['emails_sent']) && $dRes['emails_sent'] > 0;
                    $resendMsg = $resendSuccess ? sprintf(__('Notification email resent successfully to %s.'), $target) : __('Failed sending notification email.');
                }
                else
                {
                    $subj = !empty($entry['subject']) ? $entry['subject'] : "[Notification Resend] New Lead Alert: {$leadName}";
                    $body = !empty($entry['message_preview']) ? nl2br(h($entry['message_preview'])) : "Resent notification for lead: {$leadName}";
                    $resendSuccess = (bool)$this->_sendDirectHtmlEmail($target, $subj, $body, $agencyEmail, $agencyName);
                    $resendMsg = $resendSuccess ? sprintf(__('Direct email resent successfully to %s.'), $target) : __('Direct email failed.');
                }
            }
            else
            {
                $resendSuccess = false;
                $resendMsg = __('No valid recipient email address could be resolved.');
            }
        }
        elseif($channel === 'sms')
        {
            if(empty($target) && $leadId > 0)
            {
                $lead = $this->Lead->findById($leadId);
                if(!empty($lead['Lead']['user_id']))
                {
                    $u = $this->User->findById((int)$lead['Lead']['user_id']);
                    if(!empty($u['User']['mobile'])) $target = trim($u['User']['mobile']);
                    elseif(!empty($u['User']['phone'])) $target = trim($u['User']['phone']);
                }
            }

            if(!empty($target))
            {
                if($leadId > 0)
                {
                    $dRes = $this->_dispatchFacebookLeadNotifications($leadId, null, null, null, null, null, 'sms', 'admin_resend');
                    $resendSuccess = !empty($dRes['sms_sent']) && $dRes['sms_sent'] > 0;
                    $resendMsg = $resendSuccess ? sprintf(__('SMS notification resent successfully to %s.'), $target) : __('SMS transmission failed.');
                }
                else
                {
                    $smsText = !empty($entry['message_preview']) ? $entry['message_preview'] : "Alert: Lead {$leadName}";
                    $resendSuccess = (bool)$this->_sendDirectSms($target, $smsText);
                    $resendMsg = $resendSuccess ? sprintf(__('SMS resent successfully to %s.'), $target) : __('SMS transmission failed.');
                }
            }
            else
            {
                $resendSuccess = false;
                $resendMsg = __('No valid recipient phone number available.');
            }
        }

        // Update the log entry
        $newCount = (int)$entry['resend_count'] + 1;
        $now = date('Y-m-d H:i:s');
        $this->FacebookNotificationLog->id = $logId;
        $this->FacebookNotificationLog->save(array(
            'FacebookNotificationLog' => array(
                'status' => $resendSuccess ? 'success' : 'failed',
                'recipient_target' => $target,
                'resend_count' => $newCount,
                'last_resend_at' => $now,
                'error_message' => $resendSuccess ? "Resent successfully on " . date('d-M-Y h:i A') : $resendMsg,
                'modified' => $now
            )
        ));

        if($this->request->is('ajax'))
        {
            $this->autoRender = false;
            echo json_encode(array(
                'success' => $resendSuccess,
                'message' => $resendMsg,
                'status' => $resendSuccess ? 'success' : 'failed',
                'resend_count' => $newCount,
                'last_resend_at' => date('d-M-Y h:i A')
            ));
            exit();
        }

        $this->Session->setFlash($resendMsg, 'flash', array('alert' => ($resendSuccess ? 'success' : 'danger')));
        return $this->redirect($this->referer(array('action' => 'notification_logs')));
    }

    public function admin_resend_all_failed()
    {
        return $this->resend_all_failed();
    }

    public function resend_all_failed()
    {
        @set_time_limit(300);
        $failedLogs = $this->FacebookNotificationLog->find('all', array(
            'conditions' => array('FacebookNotificationLog.status' => 'failed'),
            'limit' => 50,
            'order' => array('FacebookNotificationLog.id' => 'DESC')
        ));

        if(empty($failedLogs))
        {
            $msg = __('No failed notifications found to resend.');
            if($this->request->is('ajax'))
            {
                $this->autoRender = false;
                echo json_encode(array('success' => true, 'message' => $msg, 'count' => 0));
                exit();
            }
            $this->Session->setFlash($msg, 'flash', array('alert' => 'info'));
            return $this->redirect($this->referer(array('action' => 'notification_logs')));
        }

        $successCount = 0;
        $failCount = 0;

        foreach($failedLogs as $fl)
        {
            $lid = (int)$fl['FacebookNotificationLog']['id'];
            $entry = $fl['FacebookNotificationLog'];
            $leadId = !empty($entry['lead_id']) ? (int)$entry['lead_id'] : 0;
            $channel = strtolower(trim((string)$entry['channel']));

            $ok = false;
            if($leadId > 0)
            {
                $dRes = $this->_dispatchFacebookLeadNotifications($leadId, null, null, null, null, null, $channel, 'admin_resend');
                $ok = ($channel === 'email') ? (!empty($dRes['emails_sent']) && $dRes['emails_sent'] > 0) : (!empty($dRes['sms_sent']) && $dRes['sms_sent'] > 0);
            }

            $newCount = (int)$entry['resend_count'] + 1;
            $this->FacebookNotificationLog->id = $lid;
            $this->FacebookNotificationLog->save(array(
                'FacebookNotificationLog' => array(
                    'status' => $ok ? 'success' : 'failed',
                    'resend_count' => $newCount,
                    'last_resend_at' => date('Y-m-d H:i:s'),
                    'error_message' => $ok ? 'Bulk retry success on ' . date('d-M-Y h:i A') : 'Retry failed',
                    'modified' => date('Y-m-d H:i:s')
                )
            ));

            if($ok) $successCount++;
            else $failCount++;
        }

        $msg = sprintf(__('Bulk Resend Completed: %d succeeded, %d failed out of %d attempted.'), $successCount, $failCount, count($failedLogs));
        if($this->request->is('ajax'))
        {
            $this->autoRender = false;
            echo json_encode(array('success' => ($successCount > 0), 'message' => $msg, 'succeeded' => $successCount, 'failed' => $failCount));
            exit();
        }

        $this->Session->setFlash($msg, 'flash', array('alert' => ($successCount > 0 ? 'success' : 'warning')));
        return $this->redirect($this->referer(array('action' => 'notification_logs')));
    }

    public function admin_sync_now()
    {
        return $this->sync_now();
    }

    public function sync_now()
    {
        $this->autoRender = false;
        $result = $this->_fetchFacebookLeadsMultiAccounts();
        $this->_ensureAllFacebookLogsInCrmLeads();
        if($result['success'])
        {
            $this->Session->setFlash(sprintf(__('Multi-Account sync complete! Ingested %d new lead(s). %s'), $result['count'], $result['message']), 'flash', array('alert' => 'success'));
        }
        else
        {
            $this->Session->setFlash(__('Sync status: ') . $result['message'], 'flash', array('alert' => 'warning'));
        }
        return $this->redirect(array('action' => 'settings'));
    }

    
    public function sync_account($id = null)
    {
        return $this->admin_sync_account($id);
    }
    public function admin_sync_account($id = null)
    {
        $this->autoRender = false;
        if(!$id)
        {
            return $this->redirect(array('action' => 'settings'));
        }
        try
        {
            $account = $this->FacebookAccount->findById((int)$id);
            if(empty($account))
            {
                $this->Session->setFlash(__('Account not found.'), 'flash', array('alert' => 'danger'));
                return $this->redirect(array('action' => 'settings'));
            }

            $result = $this->_fetchLeadsForAccount($account['FacebookAccount']);
            $this->_ensureAllFacebookLogsInCrmLeads();
            if(!empty($result['success']))
            {
                $this->Session->setFlash(sprintf(__('Sync complete for %s! Ingested %d new lead(s).'), h($account['FacebookAccount']['account_name']), (int)$result['count']), 'flash', array('alert' => 'success'));
            }
            else
            {
                $msg = !empty($result['message']) ? $result['message'] : 'Could not fetch leads from Meta API.';
                $this->Session->setFlash(__('Sync status: ') . $msg, 'flash', array('alert' => 'warning'));
            }
        }
        catch(Exception $e)
        {
            $this->Session->setFlash(__('Sync error: ') . $e->getMessage(), 'flash', array('alert' => 'danger'));
        }
        return $this->redirect(array('action' => 'settings'));
    }




        protected function _getFormattedAgentList()
    {
        $this->loadModel('User');
        $this->loadModel('Project');
        $users = $this->User->find('all', array(
            'conditions' => array(
                'User.status' => 'Active',
                'OR' => array(
                    'User.login_status IS NULL',
                    'User.login_status !=' => 'Blocked'
                )
            ),
            'fields' => array('User.id', 'User.name', 'User.username', 'User.project_id'),
            'order' => array('User.name' => 'ASC'),
            'recursive' => -1
        ));
        $projects = $this->Project->find('list', array('fields' => array('Project.id', 'Project.name')));
        $list = array();
        foreach($users as $u)
        {
            $uId = (int)$u['User']['id'];
            $uName = trim((string)$u['User']['name']);
            if($uName === '') {
                $uName = trim((string)$u['User']['username']);
            }
            $projId = !empty($u['User']['project_id']) ? (int)$u['User']['project_id'] : 0;
            $agency = !empty($projects[$projId]) ? $projects[$projId] : 'White Villas';
            $list[$uId] = $uName . ' (Agency: ' . $agency . ')';
        }
        return $list;
    }

    public function admin_save_notifications()
    {
        return $this->save_notifications();
    }
    public function save_notifications()
    {
        $this->autoRender = false;
        $this->_ensureFacebookSettings();

        if ($this->request->is('post') || $this->request->is('put'))
        {
            $data = $this->request->data;
            
            // Build settings payload
            $updateData = array(
                'id' => 1,
                'notify_agent_sms' => !empty($data['notify_agent_sms']) ? 1 : 0,
                'notify_agent_email' => !empty($data['notify_agent_email']) ? 1 : 0,
                'notify_all_agents_email' => !empty($data['notify_all_agents_email']) ? 1 : 0,
                'notify_admin_email' => !empty($data['notify_admin_email']) ? 1 : 0,
                'admin_notification_email' => isset($data['admin_notification_email']) ? trim($data['admin_notification_email']) : '',
                'notify_client_email' => !empty($data['notify_client_email']) ? 1 : 0,
                'notify_client_sms' => !empty($data['notify_client_sms']) ? 1 : 0,
                'agent_sms_template' => isset($data['agent_sms_template']) ? trim($data['agent_sms_template']) : '',
                'agent_email_subject' => isset($data['agent_email_subject']) ? trim($data['agent_email_subject']) : '',
                'agent_email_template' => isset($data['agent_email_template']) ? trim($data['agent_email_template']) : '',
                'client_email_subject' => isset($data['client_email_subject']) ? trim($data['client_email_subject']) : '',
                'client_email_template' => isset($data['client_email_template']) ? trim($data['client_email_template']) : '',
                'client_sms_template' => isset($data['client_sms_template']) ? trim($data['client_sms_template']) : '',
                'modified' => date('Y-m-d H:i:s')
            );

            $this->FacebookSetting->save($updateData);
            $this->Session->setFlash(__('Facebook lead notification settings & templates saved successfully!'), 'flash', array('alert' => 'success'));
            $referer = (string)$this->referer();
            if(strpos($referer, 'notifications') !== false)
            {
                return $this->redirect(array('action' => 'notifications'));
            }
            return $this->redirect(array('action' => 'settings', '#' => 'notifications-tab'));
        }
        return $this->redirect(array('action' => 'notifications'));
    }

    public function send_test_notification()
    {
        $this->autoRender = false;
        header('Content-Type: application/json');

        $type = !empty($this->request->data['type']) ? $this->request->data['type'] : 'agent_email';
        $testEmail = !empty($this->request->data['email']) ? trim($this->request->data['email']) : '';
        $testPhone = !empty($this->request->data['phone']) ? trim($this->request->data['phone']) : '';

        // Load settings
        $fbSettings = $this->FacebookSetting->findById(1);
        $cfg = ClassRegistry::init('Configuration')->findById(1);

        $dummyLead = array(
            'id' => 9999,
            'lead_name' => 'Muhammad Ali (Test Lead)',
            'lead_mobile' => '+923001234567',
            'lead_email' => $testEmail !== '' ? $testEmail : 'test@example.com',
            'lead_city' => 'Lahore',
            'remarks' => 'Interested in 5 Marla Luxury Cottage - Test Submission',
            'created' => date('Y-m-d H:i:s')
        );

        $res = $this->_sendFacebookLeadNotificationDirect($type, $dummyLead, $testEmail, $testPhone, 'Test Form (Facebook Ads)', 'White Villas Lahore', 1);

        echo json_encode($res);
        exit();
    }

    protected function _sendFacebookLeadNotificationDirect($type, $leadData, $toEmail, $toPhone, $formName, $projectName, $assignedUserId = 1)
    {
        try
        {
            $fbSettings = $this->FacebookSetting->findById(1);
            $s = !empty($fbSettings['FacebookSetting']) ? $fbSettings['FacebookSetting'] : array();
            $cfg = ClassRegistry::init('Configuration')->findById(1);

            $agent = $this->User->findById($assignedUserId);
            $agentName = !empty($agent['User']['name']) ? $agent['User']['name'] : 'Ch Riaz Ahmad';
            $agentEmail = !empty($agent['User']['email']) ? trim($agent['User']['email']) : 'chriazmushtaq@gmail.com';
            $agentMobile = !empty($agent['User']['mobile']) ? trim($agent['User']['mobile']) : '+92 311 4432729';

            $defaultOrgName = !empty($cfg['Configuration']['name']) ? $cfg['Configuration']['name'] : 'White Villas';
            $agencyName = !empty($projectName) ? $projectName : $defaultOrgName;
            $agencyEmail = !empty($cfg['Configuration']['email']) ? $cfg['Configuration']['email'] : 'info@whitevillas.com.pk';
            $agencyPhone = !empty($cfg['Configuration']['contact']) ? $cfg['Configuration']['contact'] : '+923391101156';
            $agencyAddress = !empty($cfg['Configuration']['address']) ? $cfg['Configuration']['address'] : 'Lahore, Pakistan';
            $agencyDomain = !empty($cfg['Configuration']['domain_name']) ? $cfg['Configuration']['domain_name'] : 'https://portal.whitevillas.com.pk';

            $agencyLogo = !empty($cfg['Configuration']['photo']) ? $cfg['Configuration']['photo'] : '';
            $agencyLogoUrl = Router::url('/', true) . 'img/' . $agencyLogo;
            $agencyLogoHtml = $agencyLogo !== '' ? '<img src="' . $agencyLogoUrl . '" alt="' . h($agencyName) . '" style="max-height:55px;margin-bottom:8px;">' : '';

            $crmLink = Router::url(array('plugin' => 'admin', 'controller' => 'leads', 'action' => 'leadview', !empty($leadData['id']) ? $leadData['id'] : 1), true);
            $leadMobileClean = preg_replace('/[^0-9]/', '', (string)$leadData['lead_mobile']);
            $agentMobileClean = preg_replace('/[^0-9]/', '', (string)$agentMobile);

            $tags = array(
                '{lead_name}' => $leadData['lead_name'],
                '{lead_mobile}' => $leadData['lead_mobile'],
                '{lead_email}' => !empty($leadData['lead_email']) ? $leadData['lead_email'] : 'N/A',
                '{lead_city}' => !empty($leadData['lead_city']) ? $leadData['lead_city'] : 'Lahore',
                '{lead_remarks}' => !empty($leadData['remarks']) ? $leadData['remarks'] : 'None',
                '{form_name}' => $formName,
                '{project_name}' => $projectName,
                '{agent_name}' => $agentName,
                '{agent_mobile}' => $agentMobile,
                '{agent_email}' => $agentEmail,
                '{agent_email_row}' => '<div style="margin-top:6px;font-size:12px;color:#64748b;"><strong>Email:</strong> <a href="mailto:' . $agentEmail . '" style="color:#0284c7;text-decoration:none;font-weight:600;">' . $agentEmail . '</a></div>',
                '{agent_whatsapp_link}' => 'https://wa.me/' . ($agentMobileClean !== '' ? $agentMobileClean : $leadMobileClean),
                '{whatsapp_link}' => 'https://wa.me/' . $leadMobileClean,
                '{agency_name}' => $agencyName,
                '{agency_phone}' => $agencyPhone,
                '{agency_email}' => $agencyEmail,
                '{agency_address}' => $agencyAddress,
                '{agency_domain}' => $agencyDomain,
                '{agency_logo_html}' => $agencyLogoHtml,
                '{crm_link}' => $crmLink,
                '{inquiry_date}' => !empty($leadCreatedTime) ? date('d-M-Y h:i A', strtotime($leadCreatedTime)) : date('d-M-Y h:i A')
            );

            if($type === 'agent_email')
            {
                $target = !empty($toEmail) ? $toEmail : $agentEmail;
                $subj = !empty($s['agent_email_subject']) ? str_replace(array_keys($tags), array_values($tags), $s['agent_email_subject']) : "[New Facebook Lead] {$leadData['lead_name']} - {$projectName} ({$agencyName})";
                $rawBody = !empty($s['agent_email_template']) ? $s['agent_email_template'] : $this->_getDefaultAgentEmailTemplate();
                $body = str_replace(array_keys($tags), array_values($tags), $rawBody);
                $ok = $this->_sendDirectHtmlEmail($target, $subj, $body, $agencyEmail, $agencyName);
                return array('success' => (bool)$ok, 'message' => $ok ? "Test agent email sent successfully to {$target}" : "Failed sending test email to {$target}");
            }
            elseif($type === 'admin_email')
            {
                $target = !empty($toEmail) ? $toEmail : (!empty($s['admin_notification_email']) ? $s['admin_notification_email'] : $agencyEmail);
                $subj = "[Admin Alert] New Facebook Lead Assigned: {$leadData['lead_name']} - {$projectName} ({$agencyName})";
                $rawBody = !empty($s['agent_email_template']) ? $s['agent_email_template'] : $this->_getDefaultAgentEmailTemplate();
                $body = str_replace(array_keys($tags), array_values($tags), $rawBody);
                $ok = $this->_sendDirectHtmlEmail($target, $subj, $body, $agencyEmail, $agencyName);
                return array('success' => (bool)$ok, 'message' => $ok ? "Test agency admin email sent successfully to {$target}" : "Failed sending test email to {$target}");
            }
            elseif($type === 'client_email')
            {
                $target = !empty($toEmail) ? $toEmail : $leadData['lead_email'];
                $subj = !empty($s['client_email_subject']) ? str_replace(array_keys($tags), array_values($tags), $s['client_email_subject']) : "Thank you for your inquiry - {$agencyName}";
                $rawBody = !empty($s['client_email_template']) ? $s['client_email_template'] : "";
                $body = str_replace(array_keys($tags), array_values($tags), $rawBody);
                $ok = $this->_sendDirectHtmlEmail($target, $subj, $body, $agencyEmail, $agencyName);
                return array('success' => (bool)$ok, 'message' => $ok ? "Test client email sent successfully to {$target}" : "Failed sending test email to {$target}");
            }
            elseif($type === 'agent_sms')
            {
                $target = !empty($toPhone) ? $toPhone : $agentMobile;
                $sms = !empty($s['agent_sms_template']) ? str_replace(array_keys($tags), array_values($tags), $s['agent_sms_template']) : "New FB Lead: {$leadData['lead_name']}";
                $ok = $this->_sendDirectSms($target, $sms);
                return array('success' => (bool)$ok, 'message' => $ok ? "Test SMS sent successfully to {$target}" : "Failed sending test SMS to {$target}");
            }
            return array('success' => false, 'message' => 'Invalid notification test type.');
        }
        catch(Throwable $e)
        {
            return array('success' => false, 'message' => $e->getMessage());
        }
    }

    public function admin_send_notification($id = null, $type = 'all')
    {
        return $this->send_notification($id, $type);
    }
    public function send_notification($id = null, $type = 'all')
    {
        $id = (int)$id;
        if($id <= 0)
        {
            if($this->request->is('ajax'))
            {
                $this->autoRender = false;
                echo json_encode(array('success' => false, 'message' => __('Invalid lead ID.')));
                exit();
            }
            $this->Session->setFlash(__('Invalid lead ID.'), 'flash', array('alert' => 'danger'));
            return $this->redirect($this->referer(array('action' => 'logs')));
        }

        $res = $this->_dispatchFacebookLeadNotifications($id, null, null, null, null, null, $type, 'manual_single');

        if($this->request->is('ajax'))
        {
            $this->autoRender = false;
            echo json_encode($res);
            exit();
        }

        if(!empty($res['success']))
        {
            $this->Session->setFlash(__('Notifications dispatched for Lead #%s: %s Email(s), %s SMS sent.', $id, $res['emails_sent'], $res['sms_sent']), 'flash', array('alert' => 'success'));
        }
        else
        {
            $this->Session->setFlash(!empty($res['message']) ? $res['message'] : __('Failed to dispatch notification.'), 'flash', array('alert' => 'danger'));
        }
        return $this->redirect($this->referer(array('action' => 'logs')));
    }

    public function admin_bulk_notifications($type = 'all')
    {
        return $this->bulk_notifications($type);
    }
    public function bulk_notifications($type = 'all')
    {
        $type = strtolower(trim((string)$type));
        if($type === '') $type = 'all';

        $selectedIds = array();
        if(!empty($this->request->data['Lead']['id']) && is_array($this->request->data['Lead']['id']))
        {
            foreach($this->request->data['Lead']['id'] as $rawId)
            {
                $lid = (int)$rawId;
                if($lid > 0) $selectedIds[$lid] = $lid;
            }
        }
        elseif(!empty($this->request->data['selected_ids']))
        {
            $rawArr = is_array($this->request->data['selected_ids']) ? $this->request->data['selected_ids'] : explode(',', (string)$this->request->data['selected_ids']);
            foreach($rawArr as $rawId)
            {
                $lid = (int)trim($rawId);
                if($lid > 0) $selectedIds[$lid] = $lid;
            }
        }

        if(empty($selectedIds))
        {
            if($this->request->is('ajax'))
            {
                $this->autoRender = false;
                echo json_encode(array('success' => false, 'message' => __('Please select at least one lead.')));
                exit();
            }
            $this->Session->setFlash(__('Please select at least one lead.'), 'flash', array('alert' => 'warning'));
            return $this->redirect($this->referer(array('action' => 'logs')));
        }

        // Safety controls
        @set_time_limit(300);
        @ini_set('max_execution_time', 300);
        @ini_set('memory_limit', '512M');

        // Cap batch size to 100 for safety
        $leadIdsList = array_slice(array_values($selectedIds), 0, 100);

        $processedCount = 0;
        $totalEmails = 0;
        $totalSms = 0;
        $failedCount = 0;

        foreach($leadIdsList as $leadId)
        {
            $res = $this->_dispatchFacebookLeadNotifications($leadId, null, null, null, null, null, $type, 'bulk_action');
            if(!empty($res['success']))
            {
                $processedCount++;
                $totalEmails += (int)$res['emails_sent'];
                $totalSms += (int)$res['sms_sent'];
            }
            else
            {
                $failedCount++;
            }
        }

        $summaryMsg = __('Bulk notifications complete: %s lead(s) processed. (Sent: %s Emails, %s SMS).', $processedCount, $totalEmails, $totalSms);
        if($failedCount > 0)
        {
            $summaryMsg .= ' ' . __('Failed: %s lead(s).', $failedCount);
        }

        if($this->request->is('ajax'))
        {
            $this->autoRender = false;
            echo json_encode(array(
                'success' => true,
                'message' => $summaryMsg,
                'processed_count' => $processedCount,
                'emails_sent' => $totalEmails,
                'sms_sent' => $totalSms,
                'failed_count' => $failedCount
            ));
            exit();
        }

        $this->Session->setFlash($summaryMsg, 'flash', array('alert' => ($processedCount > 0 ? 'success' : 'danger')));
        return $this->redirect($this->referer(array('action' => 'logs')));
    }

    protected function _recordNotificationLog($logData)
    {
        try {
            $this->FacebookNotificationLog->create();
            $now = date('Y-m-d H:i:s');
            $default = array(
                'lead_id' => null,
                'lead_name' => '',
                'lead_phone' => '',
                'lead_email' => '',
                'form_name' => '',
                'project_name' => '',
                'channel' => 'email',
                'recipient_type' => 'agent',
                'recipient_name' => '',
                'recipient_target' => '',
                'subject' => null,
                'message_preview' => '',
                'trigger_type' => 'auto_sync',
                'status' => 'success',
                'error_message' => null,
                'resend_count' => 0,
                'created' => $now,
                'modified' => $now
            );
            $merged = array_merge($default, (array)$logData);
            return $this->FacebookNotificationLog->save($merged);
        } catch(Exception $e) {
            CakeLog::write('error', 'Facebook Notification Log Save Error: ' . $e->getMessage());
            return false;
        }
    }

    public function dispatchLeadNotification($createdLeadId, $channel = 'all', $parsed = null, $formName = null, $projectName = null, $assignedUserId = null, $leadCreatedTime = null)
    {
        return $this->_dispatchFacebookLeadNotifications($createdLeadId, $parsed, $formName, $projectName, $assignedUserId, $leadCreatedTime, $channel, 'auto_sync');
    }

    protected function _dispatchFacebookLeadNotifications($createdLeadId, $parsed = null, $formName = null, $projectName = null, $assignedUserId = null, $leadCreatedTime = null, $channel = 'all', $triggerType = 'auto_sync')
    {
        $createdLeadId = (int)$createdLeadId;
        $channel = strtolower(trim((string)$channel));
        if($channel === '') $channel = 'all';
        $triggerType = !empty($triggerType) ? trim((string)$triggerType) : 'auto_sync';

        $totalEmailsSent = 0;
        $totalSmsSent = 0;
        $details = array();

        try
        {
            $fbSettings = $this->FacebookSetting->findById(1);
            if(empty($fbSettings['FacebookSetting']))
            {
                return array('success' => false, 'message' => 'Facebook settings not found', 'emails_sent' => 0, 'sms_sent' => 0);
            }
            $s = $fbSettings['FacebookSetting'];

            // If lead details were not passed, load directly from Lead model!
            if($createdLeadId > 0 && empty($parsed))
            {
                $lead = $this->Lead->find('first', array(
                    'conditions' => array('Lead.id' => $createdLeadId),
                    'recursive' => 1
                ));
                if(empty($lead['Lead']))
                {
                    return array('success' => false, 'message' => "Lead #{$createdLeadId} not found", 'emails_sent' => 0, 'sms_sent' => 0);
                }

                $parsed = array(
                    'name' => !empty($lead['Lead']['lead_name']) ? $lead['Lead']['lead_name'] : (!empty($lead['Client']['name']) ? $lead['Client']['name'] : 'Lead Contact'),
                    'phone' => !empty($lead['Lead']['lead_mobile']) ? $lead['Lead']['lead_mobile'] : (!empty($lead['Client']['mobile']) ? $lead['Client']['mobile'] : (!empty($lead['Client']['phone']) ? $lead['Client']['phone'] : '')),
                    'email' => !empty($lead['Lead']['lead_email']) ? $lead['Lead']['lead_email'] : (!empty($lead['Client']['email']) ? $lead['Client']['email'] : ''),
                    'city' => !empty($lead['Lead']['lead_city']) ? $lead['Lead']['lead_city'] : (!empty($lead['Client']['city']) ? $lead['Client']['city'] : ''),
                    'remarks' => !empty($lead['Lead']['remarks']) ? $lead['Lead']['remarks'] : ''
                );

                if(empty($formName))
                {
                    $formName = !empty($lead['Lead']['lead_source']) ? $lead['Lead']['lead_source'] : 'Facebook Lead Ads';
                }
                if(empty($projectName))
                {
                    $projectName = !empty($lead['Project']['name']) ? $lead['Project']['name'] : '';
                }
                if($assignedUserId === null || $assignedUserId <= 0)
                {
                    $assignedUserId = !empty($lead['Lead']['user_id']) ? (int)$lead['Lead']['user_id'] : (!empty($lead['Client']['user_id']) ? (int)$lead['Client']['user_id'] : 0);
                }
                if(empty($leadCreatedTime))
                {
                    $leadCreatedTime = !empty($lead['Lead']['created']) ? $lead['Lead']['created'] : null;
                }
            }

            // Fallback agency admin email/mobile & config
            $cfg = ClassRegistry::init('Configuration')->findById(1);
            $defaultOrgName = !empty($cfg['Configuration']['name']) ? $cfg['Configuration']['name'] : 'White Villas';
            
            // Dynamically resolve exact Agency Name
            if(empty($projectName))
            {
                $projectName = $defaultOrgName;
            }
            $agencyName = $projectName;
            $agencyEmail = !empty($cfg['Configuration']['email']) ? $cfg['Configuration']['email'] : 'info@whitevillas.com.pk';
            $agencyPhone = !empty($cfg['Configuration']['contact']) ? $cfg['Configuration']['contact'] : '+923391101156';
            $agencyAddress = !empty($cfg['Configuration']['address']) ? $cfg['Configuration']['address'] : 'Lahore, Pakistan';
            $agencyDomain = !empty($cfg['Configuration']['domain_name']) ? $cfg['Configuration']['domain_name'] : 'https://portal.whitevillas.com.pk';
            
            $agencyLogo = !empty($cfg['Configuration']['photo']) ? $cfg['Configuration']['photo'] : '';
            $agencyLogoUrl = Router::url('/', true) . 'img/' . $agencyLogo;
            $agencyLogoHtml = $agencyLogo !== '' ? '<img src="' . $agencyLogoUrl . '" alt="' . h($agencyName) . '" style="max-height:55px;margin-bottom:8px;">' : '';

            // 1. Resolve Assigned Agent Details
            $agentName = 'Sales Team';
            $agentEmail = '';
            $agentMobile = '';

            if($assignedUserId > 0)
            {
                $agent = $this->User->findById($assignedUserId);
                if(!empty($agent['User']))
                {
                    $agentName = !empty($agent['User']['name']) ? $agent['User']['name'] : 'Agent';
                    $agentEmail = !empty($agent['User']['email']) ? trim($agent['User']['email']) : '';
                    $agentMobile = !empty($agent['User']['mobile']) ? trim($agent['User']['mobile']) : (!empty($agent['User']['phone']) ? trim($agent['User']['phone']) : '');
                }
            }

            $crmLink = Router::url(array('plugin' => 'admin', 'controller' => 'leads', 'action' => 'leadview', $createdLeadId), true);
            $leadMobileClean = preg_replace('/[^0-9]/', '', (string)$parsed['phone']);
            $agentMobileClean = preg_replace('/[^0-9]/', '', (string)$agentMobile);

            // Real-time in-app notification to assigned agent
            if($assignedUserId > 0)
            {
                $notifTitle = 'New Facebook Lead: ' . (!empty($parsed['name']) ? $parsed['name'] : 'Lead #' . $createdLeadId);
                $notifMsg = sprintf(__('Facebook lead %s (%s) was assigned to you from form "%s".'), !empty($parsed['name']) ? $parsed['name'] : 'Lead', !empty($parsed['phone']) ? $parsed['phone'] : '', $formName);
                $actionUrl = '/admin/Leads/leadview/' . $createdLeadId;
                $this->sendNotification($assignedUserId, $notifTitle, $notifMsg, 'lead_assigned', $actionUrl, 'fa-facebook-square', 'high', 'fb_lead_' . $createdLeadId . '_' . $assignedUserId);
            }

            $tags = array(
                '{lead_name}' => $parsed['name'],
                '{lead_mobile}' => $parsed['phone'],
                '{lead_email}' => !empty($parsed['email']) ? $parsed['email'] : 'N/A',
                '{lead_city}' => !empty($parsed['city']) ? $parsed['city'] : 'N/A',
                '{lead_remarks}' => !empty($parsed['remarks']) ? $parsed['remarks'] : 'None',
                '{form_name}' => $formName,
                '{project_name}' => $projectName,
                '{agent_name}' => $agentName,
                '{agent_mobile}' => $agentMobile !== '' ? $agentMobile : $agencyPhone,
                '{agent_email}' => $agentEmail !== '' ? $agentEmail : $agencyEmail,
                '{agent_email_row}' => $agentEmail !== '' ? '<div style="margin-top:6px;font-size:12px;color:#64748b;"><strong>Email:</strong> <a href="mailto:' . $agentEmail . '" style="color:#0284c7;text-decoration:none;font-weight:600;">' . $agentEmail . '</a></div>' : '',
                '{agent_whatsapp_link}' => 'https://wa.me/' . ($agentMobileClean !== '' ? $agentMobileClean : $leadMobileClean),
                '{whatsapp_link}' => 'https://wa.me/' . $leadMobileClean,
                '{agency_name}' => $agencyName,
                '{agency_phone}' => $agencyPhone,
                '{agency_email}' => $agencyEmail,
                '{agency_address}' => $agencyAddress,
                '{agency_domain}' => $agencyDomain,
                '{agency_logo_html}' => $agencyLogoHtml,
                '{crm_link}' => $crmLink,
                '{inquiry_date}' => !empty($leadCreatedTime) ? date('d-M-Y h:i A', strtotime($leadCreatedTime)) : date('d-M-Y h:i A')
            );

            // 1. Notify Assigned Agent (and optionally all active agents in looper) via Light Modern Email
            if(in_array($channel, array('all', 'email', 'agent_email'), true) && !empty($s['notify_agent_email']))
            {
                $targetEmails = array();

                // If admin enabled "Notify All Active Agents"
                if(!empty($s['notify_all_agents_email']))
                {
                    $activeAssignments = $this->FacebookAgentAssignment->find('all', array(
                        'conditions' => array('FacebookAgentAssignment.is_active' => 1),
                        'recursive' => -1
                    ));
                    if(!empty($activeAssignments))
                    {
                        $uIds = Set::extract('/FacebookAgentAssignment/user_id', $activeAssignments);
                        $activeUsers = $this->User->find('all', array(
                            'conditions' => array('User.id' => $uIds, 'User.status' => 'Active'),
                            'fields' => array('User.id', 'User.email'),
                            'recursive' => -1
                        ));
                        foreach($activeUsers as $au)
                        {
                            $em = trim((string)$au['User']['email']);
                            if($em !== '' && filter_var($em, FILTER_VALIDATE_EMAIL))
                            {
                                $targetEmails[strtolower($em)] = $em;
                            }
                        }
                    }
                }

                // Always include assigned agent email if present and valid
                if($agentEmail !== '' && filter_var($agentEmail, FILTER_VALIDATE_EMAIL))
                {
                    $targetEmails[strtolower($agentEmail)] = $agentEmail;
                }

                // If agent has NO email configured, fallback to agency admin email so alert is never lost!
                if(empty($targetEmails))
                {
                    $fallbackAdmin = !empty($s['admin_notification_email']) ? trim($s['admin_notification_email']) : (!empty($cfg['Configuration']['email']) ? trim($cfg['Configuration']['email']) : '');
                    if($fallbackAdmin !== '' && filter_var($fallbackAdmin, FILTER_VALIDATE_EMAIL))
                    {
                        $targetEmails[strtolower($fallbackAdmin)] = $fallbackAdmin;
                        CakeLog::write('warning', "Facebook Lead Notification: Assigned agent '{$agentName}' has no email. Falling back to admin email '{$fallbackAdmin}'.");
                    }
                    else
                    {
                        $this->_recordNotificationLog(array(
                            'lead_id' => $createdLeadId,
                            'lead_name' => $parsed['name'],
                            'lead_phone' => $parsed['phone'],
                            'lead_email' => $parsed['email'],
                            'form_name' => $formName,
                            'project_name' => $projectName,
                            'channel' => 'email',
                            'recipient_type' => 'agent',
                            'recipient_name' => $agentName,
                            'recipient_target' => 'Not Configured',
                            'subject' => "[New Facebook Lead] {$parsed['name']} - {$projectName} ({$agencyName})",
                            'message_preview' => "Assigned agent '{$agentName}' has no email address configured in Users profile, and no agency fallback was found.",
                            'trigger_type' => $triggerType,
                            'status' => 'failed',
                            'error_message' => 'Agent profile has blank email in Users table.'
                        ));
                    }
                }

                $subj = !empty($s['agent_email_subject']) ? str_replace(array_keys($tags), array_values($tags), $s['agent_email_subject']) : "[New Facebook Lead] {$parsed['name']} - {$projectName} ({$agencyName})";
                $rawBody = !empty($s['agent_email_template']) ? $s['agent_email_template'] : $this->_getDefaultAgentEmailTemplate();
                $body = str_replace(array_keys($tags), array_values($tags), $rawBody);
                if($body !== '')
                {
                    foreach($targetEmails as $tEmail)
                    {
                        $ok = $this->_sendDirectHtmlEmail($tEmail, $subj, $body, $agencyEmail, $agencyName);
                        if($ok)
                        {
                            $totalEmailsSent++;
                            $details['agent_email'][] = $tEmail;
                        }
                        $this->_recordNotificationLog(array(
                            'lead_id' => $createdLeadId,
                            'lead_name' => $parsed['name'],
                            'lead_phone' => $parsed['phone'],
                            'lead_email' => $parsed['email'],
                            'form_name' => $formName,
                            'project_name' => $projectName,
                            'channel' => 'email',
                            'recipient_type' => (!empty($s['notify_all_agents_email']) && count($targetEmails) > 1) ? 'all_agents' : 'agent',
                            'recipient_name' => $agentName,
                            'recipient_target' => $tEmail,
                            'subject' => $subj,
                            'message_preview' => mb_substr(strip_tags($body), 0, 300),
                            'trigger_type' => $triggerType,
                            'status' => $ok ? 'success' : 'failed',
                            'error_message' => $ok ? 'Agent email delivered successfully.' : 'Agent email delivery failed across all transports.'
                        ));
                    }
                }
            }

            // 1b. Notify Agency Admin via Email
            $adminNotifyEnabled = isset($s['notify_admin_email']) ? !empty($s['notify_admin_email']) : (!empty($s['notify_agent_email']));
            if(in_array($channel, array('all', 'email', 'admin_email'), true) && $adminNotifyEnabled)
            {
                $targetAdminEmail = !empty($s['admin_notification_email']) ? trim($s['admin_notification_email']) : $agencyEmail;
                if(empty($targetAdminEmail))
                {
                    $targetAdminEmail = !empty($cfg['Configuration']['email']) ? trim($cfg['Configuration']['email']) : '';
                }
                if(!empty($targetAdminEmail) && filter_var($targetAdminEmail, FILTER_VALIDATE_EMAIL))
                {
                    if(!empty($s['agent_email_subject']))
                    {
                        $cleanAgentSubj = str_replace(array_keys($tags), array_values($tags), $s['agent_email_subject']);
                        $cleanAgentSubj = preg_replace('/[\x{1F300}-\x{1F6FF}\x{1F900}-\x{1F9FF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}]/u', '', $cleanAgentSubj);
                        $adminSubj = "[Admin Alert] " . trim($cleanAgentSubj);
                    }
                    else
                    {
                        $adminSubj = "[Admin Alert] New Facebook Lead Assigned to {$agentName}: {$parsed['name']} - {$projectName} ({$agencyName})";
                    }
                    
                    $rawAdminBody = !empty($s['agent_email_template']) ? $s['agent_email_template'] : $this->_getDefaultAgentEmailTemplate();
                    $adminBody = str_replace(array_keys($tags), array_values($tags), $rawAdminBody);
                    if($adminBody !== '')
                    {
                        // Avoid duplicate send if admin was already notified as sole target in step 1
                        if(empty($details['agent_email']) || !in_array($targetAdminEmail, $details['agent_email'], true))
                        {
                            $ok = $this->_sendDirectHtmlEmail($targetAdminEmail, $adminSubj, $adminBody, $agencyEmail, $agencyName);
                            if($ok)
                            {
                                $totalEmailsSent++;
                                $details['admin_email'] = $targetAdminEmail;
                            }
                            $this->_recordNotificationLog(array(
                                'lead_id' => $createdLeadId,
                                'lead_name' => $parsed['name'],
                                'lead_phone' => $parsed['phone'],
                                'lead_email' => $parsed['email'],
                                'form_name' => $formName,
                                'project_name' => $projectName,
                                'channel' => 'email',
                                'recipient_type' => 'admin',
                                'recipient_name' => 'Agency Admin',
                                'recipient_target' => $targetAdminEmail,
                                'subject' => $adminSubj,
                                'message_preview' => mb_substr(strip_tags($adminBody), 0, 300),
                                'trigger_type' => $triggerType,
                                'status' => $ok ? 'success' : 'failed',
                                'error_message' => $ok ? 'Agency Admin alert email delivered successfully.' : 'Agency Admin email delivery failed.'
                            ));
                        }
                    }
                }
            }

            // 2. Notify Agent via SMS
            if(in_array($channel, array('all', 'sms', 'agent_sms'), true) && !empty($s['notify_agent_sms']) && $agentMobile !== '')
            {
                $smsText = !empty($s['agent_sms_template']) ? str_replace(array_keys($tags), array_values($tags), $s['agent_sms_template']) : "New FB Lead: {$parsed['name']}, Phone: {$parsed['phone']}, Form: {$formName}. - {$agencyName}";
                $ok = $this->_sendDirectSms($agentMobile, $smsText);
                if($ok)
                {
                    $totalSmsSent++;
                    $details['agent_sms'] = $agentMobile;
                }
                $this->_recordNotificationLog(array(
                    'lead_id' => $createdLeadId,
                    'lead_name' => $parsed['name'],
                    'lead_phone' => $parsed['phone'],
                    'lead_email' => $parsed['email'],
                    'form_name' => $formName,
                    'project_name' => $projectName,
                    'channel' => 'sms',
                    'recipient_type' => 'agent',
                    'recipient_name' => $agentName,
                    'recipient_target' => $agentMobile,
                    'subject' => null,
                    'message_preview' => mb_substr($smsText, 0, 300),
                    'trigger_type' => $triggerType,
                    'status' => $ok ? 'success' : 'failed',
                    'error_message' => $ok ? 'Agent SMS delivered successfully.' : 'SMS gateway rejected or failed.'
                ));
            }

            // 3. Notify Client via Professional Welcome Email
            if(in_array($channel, array('all', 'email', 'client_email'), true) && !empty($s['notify_client_email']) && !empty($parsed['email']) && filter_var($parsed['email'], FILTER_VALIDATE_EMAIL))
            {
                $cSubj = !empty($s['client_email_subject']) ? str_replace(array_keys($tags), array_values($tags), $s['client_email_subject']) : "Thank You for Your Inquiry with {$agencyName} - {$projectName}";
                $cBody = !empty($s['client_email_template']) ? str_replace(array_keys($tags), array_values($tags), $s['client_email_template']) : "";
                if($cBody !== '')
                {
                    $ok = $this->_sendDirectHtmlEmail($parsed['email'], $cSubj, $cBody, $agencyEmail, $agencyName);
                    if($ok)
                    {
                        $totalEmailsSent++;
                        $details['client_email'] = $parsed['email'];
                    }
                    $this->_recordNotificationLog(array(
                        'lead_id' => $createdLeadId,
                        'lead_name' => $parsed['name'],
                        'lead_phone' => $parsed['phone'],
                        'lead_email' => $parsed['email'],
                        'form_name' => $formName,
                        'project_name' => $projectName,
                        'channel' => 'email',
                        'recipient_type' => 'client',
                        'recipient_name' => $parsed['name'],
                        'recipient_target' => $parsed['email'],
                        'subject' => $cSubj,
                        'message_preview' => mb_substr(strip_tags($cBody), 0, 300),
                        'trigger_type' => $triggerType,
                        'status' => $ok ? 'success' : 'failed',
                        'error_message' => $ok ? 'Client welcome email delivered successfully.' : 'Client email delivery failed.'
                    ));
                }
            }

            // 4. Notify Client via Welcome SMS
            if(in_array($channel, array('all', 'sms', 'client_sms'), true) && !empty($s['notify_client_sms']) && !empty($parsed['phone']))
            {
                $cSms = !empty($s['client_sms_template']) ? str_replace(array_keys($tags), array_values($tags), $s['client_sms_template']) : "Dear {$parsed['name']}, thank you for inquiring about {$projectName} on Facebook! Your assigned advisor is {$agentName} ({$agentMobile}). - {$agencyName}";
                $ok = $this->_sendDirectSms($parsed['phone'], $cSms);
                if($ok)
                {
                    $totalSmsSent++;
                    $details['client_sms'] = $parsed['phone'];
                }
                $this->_recordNotificationLog(array(
                    'lead_id' => $createdLeadId,
                    'lead_name' => $parsed['name'],
                    'lead_phone' => $parsed['phone'],
                    'lead_email' => $parsed['email'],
                    'form_name' => $formName,
                    'project_name' => $projectName,
                    'channel' => 'sms',
                    'recipient_type' => 'client',
                    'recipient_name' => $parsed['name'],
                    'recipient_target' => $parsed['phone'],
                    'subject' => null,
                    'message_preview' => mb_substr($cSms, 0, 300),
                    'trigger_type' => $triggerType,
                    'status' => $ok ? 'success' : 'failed',
                    'error_message' => $ok ? 'Client welcome SMS delivered successfully.' : 'SMS gateway rejected phone number.'
                ));
            }

            // 5. WhatsApp Auto-Notifications via Meta Cloud API (only for 'all' channel)
            if($channel === 'all')
            {
                $waSettings = $this->WhatsappSetting->findById(1);
                if(!empty($waSettings['WhatsappSetting']['is_active']))
                {
                    $w = $waSettings['WhatsappSetting'];
                    
                    // 5a. WhatsApp to Assigned Agent
                    if(!empty($w['notify_fb_agent']) && $agentMobile !== '')
                    {
                        $agentWaBody = !empty($w['template_fb_agent']) ? str_replace(array_keys($tags), array_values($tags), $w['template_fb_agent']) : "🔔 *WV CRM: New Facebook Lead Assigned!*\n\n👤 *Client:* {$parsed['name']}\n📞 *Mobile:* {$parsed['phone']}\n🏡 *Project:* {$projectName}\n📋 *Form:* {$formName}\n💬 *Inquiry:* {$parsed['remarks']}\n\n🔗 *Open CRM:* {$crmLink}";
                        $this->_sendMetaWhatsAppMessage($agentMobile, $agentWaBody, 'FB_LEAD_AGENT', $agentName, 'Agent', $formName, $projectName);
                    }

                    // 5b. WhatsApp to Lead Client
                    if(!empty($w['notify_fb_client']) && !empty($parsed['phone']))
                    {
                        $clientWaBody = !empty($w['template_fb_client']) ? str_replace(array_keys($tags), array_values($tags), $w['template_fb_client']) : "✨ *Welcome to {$agencyName}!* ✨\n\nDear *{$parsed['name']}*,\n\nThank you for reaching out to us on Facebook regarding *{$projectName}*. Your dedicated property advisor is *{$agentName}* ({$agentMobile}). We will contact you shortly.\n\n📍 {$agencyName}";
                        $this->_sendMetaWhatsAppMessage($parsed['phone'], $clientWaBody, 'FB_LEAD_CLIENT', $parsed['name'], 'Client', $formName, $projectName);
                    }
                }
            }

            return array(
                'success' => true,
                'lead_id' => $createdLeadId,
                'emails_sent' => $totalEmailsSent,
                'sms_sent' => $totalSmsSent,
                'details' => $details,
                'message' => "Notifications dispatched: {$totalEmailsSent} Email(s), {$totalSmsSent} SMS"
            );
        }
        catch(Exception $e)
        {
            CakeLog::write('error', 'Facebook Lead Notification Error: ' . $e->getMessage());
            return array(
                'success' => false,
                'message' => $e->getMessage(),
                'emails_sent' => $totalEmailsSent,
                'sms_sent' => $totalSmsSent
            );
        }
    }

    protected function _sendDirectHtmlEmail($toEmail, $subject, $htmlBody, $fromEmail, $fromName)
    {
        try
        {
            $toEmail = trim((string)$toEmail);
            if(empty($toEmail) || !filter_var($toEmail, FILTER_VALIDATE_EMAIL))
            {
                CakeLog::write('warning', "Facebook Email Skipped: Invalid or empty recipient email '{$toEmail}'");
                return false;
            }

            $emailSetting = ClassRegistry::init('Emailsetting')->findById(1);
            $transportType = (!empty($emailSetting['Emailsetting']['type'])) ? $emailSetting['Emailsetting']['type'] : 'Smtp';

            $cfg = ClassRegistry::init('Configuration')->findById(1);
            if(empty($fromEmail))
            {
                $fromEmail = !empty($cfg['Configuration']['email']) ? trim($cfg['Configuration']['email']) : (!empty($emailSetting['Emailsetting']['username']) ? trim($emailSetting['Emailsetting']['username']) : 'info@whitevillas.com.pk');
            }
            if(empty($fromName))
            {
                $fromName = !empty($cfg['Configuration']['name']) ? trim($cfg['Configuration']['name']) : 'White Villas';
            }

            $smtpSender = !empty($emailSetting['Emailsetting']['username']) ? trim($emailSetting['Emailsetting']['username']) : $fromEmail;
            $actualFrom = ($transportType === 'Smtp' && !empty($smtpSender)) ? $smtpSender : $fromEmail;

            // Clean any residual emojis from subject to prevent mail transfer encoding issues
            $cleanSubj = preg_replace('/[\x{1F300}-\x{1F6FF}\x{1F900}-\x{1F9FF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}]/u', '', (string)$subject);
            $cleanSubj = trim(preg_replace('/\s+/', ' ', $cleanSubj));

            // Layer 1: Configured Transport (Smtp)
            try
            {
                $Email = new CakeEmail();
                $Email->transport($transportType);

                if($transportType === 'Smtp' && !empty($emailSetting['Emailsetting']))
                {
                    $smtpPort = !empty($emailSetting['Emailsetting']['port']) ? (int)$emailSetting['Emailsetting']['port'] : 465;
                    $smtpConfig = array(
                        'host' => !empty($emailSetting['Emailsetting']['host']) ? $emailSetting['Emailsetting']['host'] : 'localhost',
                        'port' => $smtpPort,
                        'username' => !empty($emailSetting['Emailsetting']['username']) ? $emailSetting['Emailsetting']['username'] : '',
                        'password' => !empty($emailSetting['Emailsetting']['password']) ? $emailSetting['Emailsetting']['password'] : '',
                        'timeout' => 15
                    );
                    if($smtpPort === 465)
                    {
                        $smtpConfig['protocol'] = 'ssl';
                        $smtpConfig['ssl_allow_self_signed'] = true;
                        $smtpConfig['context'] = array(
                            'ssl' => array(
                                'verify_peer' => false,
                                'verify_peer_name' => false,
                                'allow_self_signed' => true
                            )
                        );
                    }
                    elseif($smtpPort === 587)
                    {
                        $smtpConfig['tls'] = true;
                    }
                    $Email->config($smtpConfig);
                }

                $Email->charset('utf-8');
                $Email->from(array($actualFrom => $fromName));
                if(!empty($fromEmail) && strtolower($fromEmail) !== strtolower($actualFrom))
                {
                    $Email->replyTo($fromEmail, $fromName);
                }
                $Email->to($toEmail);
                $Email->template('default');
                $Email->emailFormat('html');
                $Email->subject($cleanSubj);
                $res = $Email->send($htmlBody);
                CakeLog::write('info', "Facebook Email Delivery SUCCESS to {$toEmail} via {$transportType} | Subject: {$cleanSubj}");
                return true;
            }
            catch(Throwable $e1)
            {
                CakeLog::write('warning', "Facebook Email Delivery Failed via {$transportType} to {$toEmail} (" . $e1->getMessage() . "). Attempting fallback to CakeEmail Mail transport...");
                
                // Layer 2: CakeEmail Mail transport
                try
                {
                    $fallbackEmail = new CakeEmail();
                    $fallbackEmail->transport('Mail');
                    $fallbackEmail->charset('utf-8');
                    $fallbackEmail->from(array($fromEmail => $fromName));
                    if(!empty($fromEmail))
                    {
                        $fallbackEmail->replyTo($fromEmail, $fromName);
                    }
                    $fallbackEmail->to($toEmail);
                    $fallbackEmail->template('default');
                    $fallbackEmail->emailFormat('html');
                    $fallbackEmail->subject($cleanSubj);
                    $fallbackEmail->send($htmlBody);
                    CakeLog::write('info', "Facebook Email Delivery SUCCESS via Fallback Mail to {$toEmail} | Subject: {$cleanSubj}");
                    return true;
                }
                catch(Throwable $e2)
                {
                    CakeLog::write('warning', "Facebook Email Fallback Mail transport failed to {$toEmail} (" . $e2->getMessage() . "). Attempting native PHP mail()...");
                    
                    // Layer 3: Direct PHP native mail()
                    $headers = "MIME-Version: 1.0\r\n";
                    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
                    $headers .= "From: =?UTF-8?B?" . base64_encode($fromName) . "?= <{$fromEmail}>\r\n";
                    $headers .= "Reply-To: {$fromEmail}\r\n";
                    $headers .= "X-Mailer: PHP/" . phpversion();
                    $encodedSubj = "=?UTF-8?B?" . base64_encode($cleanSubj) . "?=";
                    $nativeSent = @mail($toEmail, $encodedSubj, $htmlBody, $headers);
                    if($nativeSent)
                    {
                        CakeLog::write('info', "Facebook Email Delivery SUCCESS via Native mail() to {$toEmail}");
                        return true;
                    }
                    CakeLog::write('error', "Facebook Email Delivery FAILED completely to {$toEmail}");
                    return false;
                }
            }
        }
        catch(Throwable $e)
        {
            CakeLog::write('error', 'Facebook Email Exception: ' . $e->getMessage());
            return false;
        }
    }

    protected function _sendDirectSms($toMobile, $smsMessage)
    {
        try
        {
            if(method_exists($this, 'sendSystemSms'))
            {
                return $this->sendSystemSms($toMobile, $smsMessage);
            }
            $smsSetting = ClassRegistry::init('Smssetting')->findById(1);
            if(!empty($smsSetting['Smssetting']))
            {
                if(!isset($this->CustomFunction))
                {
                    App::uses('ComponentCollection', 'Controller');
                    App::uses('CustomFunctionComponent', 'Controller/Component');
                    $this->CustomFunction = new CustomFunctionComponent(new ComponentCollection());
                }
                $this->CustomFunction->sendSms($toMobile, $smsMessage, $smsSetting['Smssetting']);
                return true;
            }
        }
        catch(Exception $e)
        {
            CakeLog::write('error', 'Facebook SMS Delivery Failed to ' . $toMobile . ': ' . $e->getMessage());
            return false;
        }
        return false;
    }

    public function _getDefaultAgentEmailTemplate()
    {
        return '<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>New Lead Assigned - {agency_name}</title>
</head>
<body style="margin:0;padding:0;background-color:#f1f5f9;font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif;-webkit-font-smoothing:antialiased;color:#334155;">
    <div style="background-color:#f1f5f9;padding:24px 10px;">
        <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width:620px;background-color:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 18px rgba(0,0,0,0.06);border:1px solid #e2e8f0;border-collapse:separate;mso-table-lspace:0pt;mso-table-rspace:0pt;">
            <tr>
                <td style="height:4px;background-color:#0284c7;line-height:4px;font-size:4px;">&nbsp;</td>
            </tr>
            <tr>
                <td align="center" style="padding:26px 30px 20px 30px;background-color:#ffffff;border-bottom:1px solid #f1f5f9;">
                    {agency_logo_html}
                    <h2 style="margin:6px 0 0 0;font-size:20px;font-weight:800;color:#0f172a;letter-spacing:-0.4px;">{agency_name}</h2>
                    <p style="margin:4px 0 10px 0;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.8px;">Facebook Lead Ingestion &bull; Sales CRM</p>
                    <div style="display:inline-block;background-color:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;font-size:11px;font-weight:700;padding:4px 14px;border-radius:20px;text-transform:uppercase;letter-spacing:0.5px;">
                        New Lead Assigned
                    </div>
                </td>
            </tr>
            <tr>
                <td style="padding:26px 30px 20px 30px;">
                    <div style="background-color:#f8fafc;border-left:4px solid #0284c7;border-radius:0 8px 8px 0;padding:14px 18px;margin-bottom:20px;font-size:13.5px;color:#334155;line-height:1.6;">
                        Hello <strong>{agent_name}</strong>,<br/>
                        A new verified lead from Facebook Lead Ads for <strong>{project_name}</strong> has been assigned to you. Prompt contact within <strong>15 minutes</strong> gives the highest conversion rate!
                    </div>

                    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color:#ffffff;border:1px solid #e2e8f0;border-radius:10px;margin-bottom:22px;border-collapse:separate;overflow:hidden;">
                        <tr style="background-color:#f8fafc;">
                            <td colspan="2" style="padding:11px 16px;font-size:12px;font-weight:800;color:#0f172a;text-transform:uppercase;letter-spacing:0.5px;border-bottom:1px solid #e2e8f0;">
                                Verified Lead Information
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:10px 16px;font-size:13px;color:#64748b;width:130px;border-bottom:1px solid #f1f5f9;font-weight:600;">Client Name:</td>
                            <td style="padding:10px 16px;font-size:14px;color:#0f172a;font-weight:700;border-bottom:1px solid #f1f5f9;">{lead_name}</td>
                        </tr>
                        <tr>
                            <td style="padding:10px 16px;font-size:13px;color:#64748b;border-bottom:1px solid #f1f5f9;font-weight:600;">Mobile / Phone:</td>
                            <td style="padding:10px 16px;font-size:14px;border-bottom:1px solid #f1f5f9;">
                                <a href="tel:{lead_mobile}" style="color:#0284c7;font-weight:700;text-decoration:none;">{lead_mobile}</a>
                                &nbsp;&nbsp;
                                <a href="{whatsapp_link}" target="_blank" style="display:inline-block;background-color:#16a34a;color:#ffffff;font-size:11px;font-weight:700;padding:3px 10px;border-radius:4px;text-decoration:none;vertical-align:middle;">WhatsApp</a>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:10px 16px;font-size:13px;color:#64748b;border-bottom:1px solid #f1f5f9;font-weight:600;">Email Address:</td>
                            <td style="padding:10px 16px;font-size:13px;color:#0f172a;border-bottom:1px solid #f1f5f9;">
                                <a href="mailto:{lead_email}" style="color:#0284c7;text-decoration:none;">{lead_email}</a>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:10px 16px;font-size:13px;color:#64748b;border-bottom:1px solid #f1f5f9;font-weight:600;">City / Location:</td>
                            <td style="padding:10px 16px;font-size:13px;color:#0f172a;border-bottom:1px solid #f1f5f9;">{lead_city}</td>
                        </tr>
                        <tr>
                            <td style="padding:10px 16px;font-size:13px;color:#64748b;border-bottom:1px solid #f1f5f9;font-weight:600;">Target Project:</td>
                            <td style="padding:10px 16px;font-size:14px;color:#0f172a;font-weight:700;border-bottom:1px solid #f1f5f9;">{project_name}</td>
                        </tr>
                        <tr>
                            <td style="padding:10px 16px;font-size:13px;color:#64748b;border-bottom:1px solid #f1f5f9;font-weight:600;">Facebook Form:</td>
                            <td style="padding:10px 16px;font-size:12px;border-bottom:1px solid #f1f5f9;">
                                <span style="display:inline-block;background-color:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;padding:2px 8px;border-radius:4px;font-weight:600;">{form_name}</span>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:10px 16px;font-size:13px;color:#64748b;border-bottom:1px solid #f1f5f9;font-weight:600;vertical-align:top;">Form Answers:</td>
                            <td style="padding:10px 16px;font-size:13px;color:#334155;border-bottom:1px solid #f1f5f9;line-height:1.5;">
                                <div style="background-color:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;padding:8px 12px;">{lead_remarks}</div>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:10px 16px;font-size:13px;color:#64748b;font-weight:600;">Assignment Time:</td>
                            <td style="padding:10px 16px;font-size:12px;color:#64748b;">{inquiry_date}</td>
                        </tr>
                    </table>

                    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin:20px 0 10px 0;">
                        <tr>
                            <td align="center">
                                <a href="{crm_link}" target="_blank" style="display:inline-block;background-color:#0f172a;color:#ffffff;font-size:13.5px;font-weight:bold;text-decoration:none;padding:12px 22px;border-radius:6px;margin-right:8px;margin-bottom:8px;">
                                    Open Lead Profile in CRM &rarr;
                                </a>
                                <a href="{whatsapp_link}" target="_blank" style="display:inline-block;background-color:#16a34a;color:#ffffff;font-size:13.5px;font-weight:bold;text-decoration:none;padding:12px 20px;border-radius:6px;margin-bottom:8px;">
                                    Chat on WhatsApp &rarr;
                                </a>
                            </td>
                        </tr>
                    </table>

                    <div style="background-color:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:12px 16px;margin-top:16px;font-size:12px;color:#64748b;">
                        <span style="font-weight:700;color:#0f172a;">Assigned Advisor:</span> {agent_name} &bull; <a href="tel:{agent_mobile}" style="color:#0284c7;text-decoration:none;font-weight:600;">{agent_mobile}</a> {agent_email_row}
                    </div>
                </td>
            </tr>

            <tr>
                <td style="background-color:#f8fafc;padding:22px 30px;text-align:center;border-top:1px solid #e2e8f0;font-size:12px;color:#64748b;line-height:1.6;">
                    <div style="font-size:13px;font-weight:700;color:#0f172a;margin-bottom:4px;">{agency_name}</div>
                    <div style="color:#64748b;"><strong>Address:</strong> {agency_address}</div>
                    <div style="margin-top:6px;color:#64748b;">
                        <strong>Phone:</strong> <a href="tel:{agency_phone}" style="color:#0284c7;text-decoration:none;font-weight:600;">{agency_phone}</a> &bull; <strong>Email:</strong> <a href="mailto:{agency_email}" style="color:#0284c7;text-decoration:none;font-weight:600;">{agency_email}</a>
                    </div>
                    <div style="margin-top:6px;">
                        <strong>Website:</strong> <a href="{agency_domain}" target="_blank" style="color:#0284c7;text-decoration:none;font-weight:600;">{agency_domain}</a>
                    </div>
                    <div style="border-top:1px solid #e2e8f0;margin-top:14px;padding-top:10px;font-size:11px;color:#94a3b8;">
                        Automated Facebook Lead Ingestion &bull; White Villas Real Estate CRM<br/>
                        &copy; 2026 {agency_name}. All rights reserved.
                    </div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>';
    }

    public function _getDefaultClientEmailTemplate()
    {
        return '<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Thank You For Your Inquiry - {agency_name}</title>
</head>
<body style="margin:0;padding:0;background-color:#f1f5f9;font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif;-webkit-font-smoothing:antialiased;color:#334155;">
    <div style="background-color:#f1f5f9;padding:24px 10px;">
        <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width:620px;background-color:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 18px rgba(0,0,0,0.06);border:1px solid #e2e8f0;border-collapse:separate;mso-table-lspace:0pt;mso-table-rspace:0pt;">
            <tr>
                <td style="height:4px;background-color:#0284c7;line-height:4px;font-size:4px;">&nbsp;</td>
            </tr>
            <tr>
                <td align="center" style="padding:28px 30px 22px 30px;background-color:#ffffff;border-bottom:1px solid #f1f5f9;">
                    {agency_logo_html}
                    <h2 style="margin:6px 0 0 0;font-size:22px;font-weight:800;color:#0f172a;letter-spacing:-0.3px;">{agency_name}</h2>
                    <p style="margin:4px 0 0 0;font-size:12px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:1px;">Premier Real Estate &amp; Luxury Living</p>
                </td>
            </tr>
            <tr>
                <td style="background-color:#eff6ff;padding:12px 24px;border-bottom:1px solid #dbeafe;text-align:center;">
                    <span style="font-size:13px;font-weight:700;color:#1d4ed8;">
                        We Have Successfully Received Your Facebook Inquiry
                    </span>
                </td>
            </tr>
            <tr>
                <td style="padding:28px 30px 20px 30px;">
                    <p style="margin:0 0 16px 0;font-size:15px;line-height:1.6;color:#0f172a;">
                        Dear <strong>{lead_name}</strong>,
                    </p>
                    <p style="margin:0 0 20px 0;font-size:14px;line-height:1.7;color:#334155;">
                        Thank you for reaching out to us regarding <strong>{project_name}</strong>. We are delighted to assist you with complete project details, flexible installment options, and site location information.
                    </p>
                    <div style="background-color:#f8fafc;border:1px solid #e2e8f0;border-left:4px solid #0284c7;border-radius:8px;padding:18px;margin:22px 0;">
                        <div style="font-size:11px;font-weight:800;text-transform:uppercase;color:#0284c7;letter-spacing:0.8px;margin-bottom:4px;">
                            Dedicated Property Advisor
                        </div>
                        <div style="font-size:17px;font-weight:800;color:#0f172a;margin-bottom:2px;">
                            {agent_name}
                        </div>
                        <div style="font-size:12px;font-weight:600;color:#64748b;margin-bottom:14px;">
                            Property Advisor &bull; {agency_name}
                        </div>
                        <table border="0" cellpadding="0" cellspacing="0" width="100%">
                            <tr>
                                <td style="padding:0 0 8px 0;">
                                    <a href="tel:{agent_mobile}" style="display:inline-block;background-color:#0284c7;color:#ffffff;font-size:13px;font-weight:700;text-decoration:none;padding:10px 18px;border-radius:6px;margin-right:8px;margin-bottom:6px;">
                                        Direct Call: {agent_mobile}
                                    </a>
                                    <a href="{agent_whatsapp_link}" target="_blank" style="display:inline-block;background-color:#16a34a;color:#ffffff;font-size:13px;font-weight:700;text-decoration:none;padding:10px 18px;border-radius:6px;margin-bottom:6px;">
                                        WhatsApp Chat &rarr;
                                    </a>
                                </td>
                            </tr>
                        </table>
                        {agent_email_row}
                    </div>
                    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color:#ffffff;border:1px solid #e2e8f0;border-radius:8px;margin:20px 0;border-collapse:separate;overflow:hidden;">
                        <tr>
                            <td colspan="2" style="background-color:#f8fafc;padding:11px 16px;font-size:12px;font-weight:800;color:#0f172a;text-transform:uppercase;letter-spacing:0.5px;border-bottom:1px solid #e2e8f0;">
                                Inquiry Summary
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:10px 16px;font-size:13px;color:#64748b;width:130px;border-bottom:1px solid #f1f5f9;font-weight:600;">Project / Offer:</td>
                            <td style="padding:10px 16px;font-size:13px;color:#0f172a;font-weight:700;border-bottom:1px solid #f1f5f9;">{project_name} ({form_name})</td>
                        </tr>
                        <tr>
                            <td style="padding:10px 16px;font-size:13px;color:#64748b;border-bottom:1px solid #f1f5f9;font-weight:600;">Registered Phone:</td>
                            <td style="padding:10px 16px;font-size:13px;color:#0284c7;font-weight:700;border-bottom:1px solid #f1f5f9;">{lead_mobile}</td>
                        </tr>
                        <tr>
                            <td style="padding:10px 16px;font-size:13px;color:#64748b;font-weight:600;">Inquiry Date:</td>
                            <td style="padding:10px 16px;font-size:13px;color:#475569;">{inquiry_date}</td>
                        </tr>
                    </table>
                    <p style="margin:20px 0 0 0;font-size:14px;line-height:1.7;color:#334155;">
                        Your dedicated advisor <strong>{agent_name}</strong> will connect with you shortly with complete payment plans, layouts, and priority booking options.
                    </p>
                    <p style="margin:22px 0 0 0;font-size:14px;line-height:1.6;color:#334155;">
                        Warm regards,<br/>
                        <strong style="color:#0f172a;">Customer Relations Team</strong><br/>
                        <span style="color:#0284c7;font-weight:700;">{agency_name}</span>
                    </p>
                </td>
            </tr>
            <tr>
                <td style="background-color:#f8fafc;padding:22px 30px;text-align:center;border-top:1px solid #e2e8f0;font-size:12px;color:#64748b;line-height:1.6;">
                    <div style="font-size:13px;font-weight:700;color:#0f172a;margin-bottom:4px;">{agency_name}</div>
                    <div style="color:#64748b;"><strong>Address:</strong> {agency_address}</div>
                    <div style="margin-top:6px;color:#64748b;">
                        <strong>Phone:</strong> <a href="tel:{agency_phone}" style="color:#0284c7;text-decoration:none;font-weight:600;">{agency_phone}</a> &bull; <strong>Email:</strong> <a href="mailto:{agency_email}" style="color:#0284c7;text-decoration:none;font-weight:600;">{agency_email}</a>
                    </div>
                    <div style="margin-top:6px;">
                        <strong>Website:</strong> <a href="{agency_domain}" target="_blank" style="color:#0284c7;text-decoration:none;font-weight:600;">{agency_domain}</a>
                    </div>
                    <div style="border-top:1px solid #e2e8f0;margin-top:14px;padding-top:10px;font-size:11px;color:#94a3b8;">
                        You received this email because you submitted an inquiry via our official Facebook advertising campaign.<br/>
                        &copy; 2026 {agency_name}. All rights reserved.
                    </div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>';
    }

    protected function _ensureFacebookSettings()
    {
        try {
            $defaultAgentTemplate = $this->_getDefaultAgentEmailTemplate();
            $defaultClientTemplate = $this->_getDefaultClientEmailTemplate();
            $settings = $this->FacebookSetting->findById(1);

            if(empty($settings) || empty($settings['FacebookSetting']))
            {
                $this->FacebookSetting->create();
                $this->FacebookSetting->save(array(
                    'FacebookSetting' => array(
                        'id' => 1,
                        'webhook_verify_token' => 'whitevillas_fb_verify_token_2026',
                        'auto_fetch_enabled' => 1,
                        'auto_assign_enabled' => 1,
                        'notify_agent_sms' => 1,
                        'notify_agent_email' => 1,
                        'notify_admin_email' => 1,
                        'notify_client_email' => 1,
                        'notify_client_sms' => 0,
                        'agent_email_subject' => '[New Facebook Lead] {lead_name} - {project_name} ({agency_name})',
                        'agent_email_template' => $defaultAgentTemplate,
                        'agent_sms_template' => 'WV CRM: New FB Lead Assigned! Name: {lead_name}, Phone: {lead_mobile}, Form: {form_name}, Project: {project_name}. Open CRM: {crm_link} - {agency_name}',
                        'client_email_subject' => 'Thank you for your interest in {project_name} | {agency_name}',
                        'client_email_template' => $defaultClientTemplate,
                        'client_sms_template' => 'Dear {lead_name}, thank you for inquiring about {project_name} on Facebook! Your assigned property advisor is {agent_name} ({agent_mobile}). We will connect with you shortly. - {agency_name}',
                        'created' => date('Y-m-d H:i:s'),
                        'modified' => date('Y-m-d H:i:s')
                    )
                ));
                $settings = $this->FacebookSetting->findById(1);
            }
            else
            {
                // Auto-upgrade database templates if they contain corrupting emojis or are empty
                $row = $settings['FacebookSetting'];
                $needsUpdate = false;
                $updatePayload = array();

                if(empty($row['agent_email_template']) || strlen($row['agent_email_template']) < 150 || preg_match('/[\x{1F300}-\x{1F6FF}]/u', $row['agent_email_template']))
                {
                    $updatePayload['agent_email_template'] = $defaultAgentTemplate;
                    $needsUpdate = true;
                }
                if(empty($row['client_email_template']) || strlen($row['client_email_template']) < 150 || preg_match('/[\x{1F300}-\x{1F6FF}]/u', $row['client_email_template']))
                {
                    $updatePayload['client_email_template'] = $defaultClientTemplate;
                    $needsUpdate = true;
                }
                if(empty($row['agent_email_subject']) || preg_match('/[\x{1F300}-\x{1F6FF}]/u', $row['agent_email_subject']))
                {
                    $updatePayload['agent_email_subject'] = '[New Facebook Lead] {lead_name} - {project_name} ({agency_name})';
                    $needsUpdate = true;
                }

                if($needsUpdate)
                {
                    $updatePayload['id'] = 1;
                    $this->FacebookSetting->save($updatePayload, false);
                    $settings = $this->FacebookSetting->findById(1);
                }
            }
            return $settings;
        } catch(Exception $e) {
            CakeLog::write('error', 'FacebookSettings ensure failed: ' . $e->getMessage());
            return array();
        }
    }

    public function reassign_agent_ajax()
    {
        $this->autoRender = false;
        header('Content-Type: application/json');

        if(!$this->request->is('post'))
        {
            echo json_encode(array('success' => false, 'message' => 'Invalid request method.'));
            exit();
        }

        $logId = !empty($this->request->data['log_id']) ? (int)$this->request->data['log_id'] : 0;
        $leadId = !empty($this->request->data['lead_id']) ? (int)$this->request->data['lead_id'] : 0;
        $newUserId = isset($this->request->data['user_id']) ? (int)$this->request->data['user_id'] : 0;

        $updated = false;
        $newAgentName = 'Unassigned';

        if($newUserId > 0)
        {
            $user = $this->User->findById($newUserId);
            if(!empty($user['User']['login_status']) && strcasecmp(trim((string)$user['User']['login_status']), 'Blocked') === 0)
            {
                echo json_encode(array('success' => false, 'message' => 'Cannot assign lead: this agent login is blocked.'));
                exit();
            }
            if(!empty($user['User']['name']))
            {
                $newAgentName = $user['User']['name'];
            }
        }

        // If logId provided, find matching lead_id
        if($logId > 0)
        {
            $log = $this->FacebookLeadLog->findById($logId);
            if(!empty($log))
            {
                $this->FacebookLeadLog->id = $logId;
                $this->FacebookLeadLog->saveField('assigned_user_id', $newUserId);
                if(!empty($log['FacebookLeadLog']['lead_id']))
                {
                    $leadId = (int)$log['FacebookLeadLog']['lead_id'];
                }
                else
                {
                    $this->_ensureAllFacebookLogsInCrmLeads();
                    $freshLog = $this->FacebookLeadLog->findById($logId);
                    if(!empty($freshLog['FacebookLeadLog']['lead_id']))
                    {
                        $leadId = (int)$freshLog['FacebookLeadLog']['lead_id'];
                    }
                }
                $updated = true;
            }
        }

        // Update Lead record and sync project_id
        if($leadId > 0)
        {
            $leadBefore = $this->Lead->findById($leadId);
            $this->Lead->id = $leadId;
            $leadSaveData = array(
                'user_id' => $newUserId,
                'modified' => date('Y-m-d H:i:s')
            );
            if(!empty($user['User']['project_id']))
            {
                $leadSaveData['project_id'] = (int)$user['User']['project_id'];
            }
            $this->Lead->save($leadSaveData, array('validate' => false));
            $updated = true;

            // Also update Client record and project
            if(!empty($leadBefore['Lead']['client_id']))
            {
                $clientId = (int)$leadBefore['Lead']['client_id'];
                $clientSaveData = array(
                    'user_id' => $newUserId,
                    'modified' => date('Y-m-d H:i:s')
                );
                if(!empty($user['User']['project_id']))
                {
                    $clientSaveData['project_id'] = (int)$user['User']['project_id'];
                }
                $this->Client->id = $clientId;
                $this->Client->save($clientSaveData, array('validate' => false));
            }

            // Ensure facebook_lead_logs reflects this assignment
            $this->FacebookLeadLog->updateAll(
                array('FacebookLeadLog.assigned_user_id' => $newUserId),
                array('FacebookLeadLog.lead_id' => $leadId)
            );

            // Audit transfer in LeadHistory
            try {
                $LeadHistory = ClassRegistry::init('LeadHistory');
                if($LeadHistory) {
                    $LeadHistory->ensureSchema();
                    $oldUserId = !empty($leadBefore['Lead']['user_id']) ? (int)$leadBefore['Lead']['user_id'] : 0;
                    $oldUser = $oldUserId > 0 ? $this->User->findById($oldUserId) : array();
                    $oldAgentName = !empty($oldUser['User']['name']) ? $oldUser['User']['name'] : 'Unassigned';
                    $LeadHistory->create();
                    $LeadHistory->save(array('LeadHistory' => array(
                        'lead_id' => $leadId,
                        'project_id' => !empty($leadSaveData['project_id']) ? $leadSaveData['project_id'] : (!empty($leadBefore['Lead']['project_id']) ? $leadBefore['Lead']['project_id'] : null),
                        'field_name' => 'user_id',
                        'old_value' => $oldAgentName,
                        'new_value' => $newAgentName,
                        'remarks' => "Lead reassigned from {$oldAgentName} to {$newAgentName}",
                        'created' => date('Y-m-d H:i:s')
                    )));
                }
            } catch(Exception $e) {}
        }

        if($updated)
        {
            echo json_encode(array(
                'success' => true,
                'user_id' => $newUserId,
                'agent_name' => $newAgentName,
                'message' => "Assigned agent updated to {$newAgentName}!"
            ));
        }
        else
        {
            echo json_encode(array('success' => false, 'message' => 'Lead record not found.'));
        }
        exit();
    }
    public function get_app_config_ajax()
    {
        $this->autoRender = false;
        header('Content-Type: application/json');

        // Check if any app_id is configured
        $account = $this->FacebookAccount->find('first', array(
            'conditions' => array('FacebookAccount.app_id IS NOT NULL', 'FacebookAccount.app_id !=' => ''),
            'recursive' => -1
        ));

        $appId = !empty($account['FacebookAccount']['app_id']) ? $account['FacebookAccount']['app_id'] : '';
        echo json_encode(array('success' => true, 'app_id' => $appId));
        exit();
    }

    public function connect_page_ajax()
    {
        $this->autoRender = false;
        header('Content-Type: application/json');

        $pageId = !empty($this->request->data['page_id']) ? trim($this->request->data['page_id']) : '';
        $pageName = !empty($this->request->data['page_name']) ? trim($this->request->data['page_name']) : 'Facebook Page';
        $accessToken = !empty($this->request->data['access_token']) ? trim($this->request->data['access_token']) : '';
        $appId = !empty($this->request->data['app_id']) ? trim($this->request->data['app_id']) : '';
        $projectId = !empty($this->request->data['project_id']) ? (int)$this->request->data['project_id'] : null;

        if($pageId === '' || $accessToken === '')
        {
            echo json_encode(array('success' => false, 'message' => 'Missing Page ID or Access Token.'));
            exit();
        }

        // Check if page already exists in accounts
        $existing = $this->FacebookAccount->find('first', array(
            'conditions' => array('FacebookAccount.page_id' => $pageId),
            'recursive' => -1
        ));

        $record = array(
            'account_name' => $pageName,
            'page_name' => $pageName,
            'page_id' => $pageId,
            'page_access_token' => $accessToken,
            'app_id' => $appId,
            'project_id' => $projectId,
            'default_status_id' => 1,
            'is_active' => 1,
            'webhook_verify_token' => 'whitevillas_fb_verify_token_2026',
            'modified' => date('Y-m-d H:i:s')
        );

        if(!empty($existing['FacebookAccount']['id']))
        {
            $this->FacebookAccount->id = (int)$existing['FacebookAccount']['id'];
            $this->FacebookAccount->save($record);
            $accountId = (int)$existing['FacebookAccount']['id'];
        }
        else
        {
            $this->FacebookAccount->create();
            $record['created'] = date('Y-m-d H:i:s');
            $record['total_leads_synced'] = 0;
            $this->FacebookAccount->save($record);
            $accountId = (int)$this->FacebookAccount->id;
        }

        // Attempt automatic Webhook Leadgen subscription on Facebook
        $subscribeUrl = "https://graph.facebook.com/v19.0/" . urlencode($pageId) . "/subscribed_apps?subscribed_fields=leadgen&access_token=" . urlencode($accessToken);
        $ch = curl_init($subscribeUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        $subResp = curl_exec($ch);
        curl_close($ch);

        $subJson = @json_decode($subResp, true);
        $subscribedMsg = (!empty($subJson['success'])) ? " (Webhook subscribed automatically)" : "";

        echo json_encode(array(
            'success' => true,
            'account_id' => $accountId,
            'message' => "Successfully connected \"{$pageName}\" for auto-syncing leads!{$subscribedMsg}"
        ));
        exit();
    }
    public function webhook()
    {
        $this->autoRender = false;

        // 1. Handle Facebook Webhook Verification Challenge (GET)
        if($this->request->is('get'))
        {
            $hubMode = isset($_GET['hub_mode']) ? $_GET['hub_mode'] : (isset($_GET['hub']['mode']) ? $_GET['hub']['mode'] : '');
            $hubToken = isset($_GET['hub_verify_token']) ? $_GET['hub_verify_token'] : (isset($_GET['hub']['verify_token']) ? $_GET['hub']['verify_token'] : '');
            $hubChallenge = isset($_GET['hub_challenge']) ? $_GET['hub_challenge'] : (isset($_GET['hub']['challenge']) ? $_GET['hub']['challenge'] : '');

            // Verify against all active accounts or default verify token
            $matched = ($hubToken === 'whitevillas_fb_verify_token_2026');
            if(!$matched && $hubToken !== '')
            {
                $check = $this->FacebookAccount->find('first', array(
                    'conditions' => array('FacebookAccount.webhook_verify_token' => $hubToken, 'FacebookAccount.is_active' => 1)
                ));
                if(!empty($check))
                {
                    $matched = true;
                }
            }

            if($hubMode === 'subscribe' && $matched)
            {
                echo $hubChallenge;
                exit();
            }
            else
            {
                http_response_code(403);
                echo 'Verification token mismatch.';
                exit();
            }
        }

        // 2. Handle Incoming Leadgen Notification (POST)
        if($this->request->is('post'))
        {
            $rawPayload = file_get_contents('php://input');
            $data = @json_decode($rawPayload, true);

            if(!empty($data['entry']))
            {
                foreach($data['entry'] as $entry)
                {
                    if(!empty($entry['changes']))
                    {
                        foreach($entry['changes'] as $change)
                        {
                            if(!empty($change['value']['leadgen_id']))
                            {
                                $leadgenId = trim((string)$change['value']['leadgen_id']);
                                $formId = !empty($change['value']['form_id']) ? trim((string)$change['value']['form_id']) : '';
                                $pageId = !empty($change['value']['page_id']) ? trim((string)$change['value']['page_id']) : '';

                                try
                                {
                                    $this->_processSingleFacebookLead($leadgenId, $formId, $pageId, $rawPayload);
                                }
                                catch(Exception $whEx)
                                {
                                    // Silently continue for duplicate or handled leads
                                }
                            }
                        }
                    }
                }
            }

            http_response_code(200);
            echo 'EVENT_RECEIVED';
            exit();
        }
    }

    public function admin_cron_autofetch()
    {
        $this->cron_autofetch();
    }

    public function cron_autofetch()
    {
        $this->autoRender = false;
        $result = $this->_fetchFacebookLeadsMultiAccounts();
        $this->_ensureAllFacebookLogsInCrmLeads();
        echo json_encode($result);
        exit();
    }

    /**
     * Parse Facebook Lead field_data into structured client details.
     * Guarantees that real person name is chosen over company/business name.
     * Accurately extracts real city name from form questions or address text.
     */
    protected function _parseFacebookLeadData($fieldData, $leadgenId = '')
    {
        $parsed = array(
            'name' => '',
            'first_name' => '',
            'last_name' => '',
            'company_name' => '',
            'phone' => '',
            'email' => '',
            'city' => '',
            'budget' => '',
            'gender' => '',
            'remarks' => ''
        );

        $customAnswers = array();

        // Comprehensive list of Pakistani & International cities for auto-detection
        $knownCities = array(
            'Lahore', 'Karachi', 'Islamabad', 'Rawalpindi', 'Faisalabad', 'Multan',
            'Peshawar', 'Gujranwala', 'Sialkot', 'Quetta', 'Sargodha', 'Bahawalpur',
            'Sahiwal', 'Okara', 'Gujrat', 'Kasur', 'Sheikhupura', 'Jhelum', 'Mardan',
            'Abbottabad', 'Wah Cantt', 'Murree', 'Naran', 'Kaghan', 'Mansehra',
            'Attock', 'Taxila', 'Chakwal', 'Rahim Yar Khan', 'Dera Ghazi Khan',
            'Mirpur', 'Muzaffarabad', 'Swat', 'Gilgit', 'Hunza', 'Gwadar',
            'Pattoki', 'Burewala', 'Vehari', 'Kamoke', 'Hafizabad', 'Chiniot',
            'Jhang', 'Khanewal', 'Mandi Bahauddin', 'Daska', 'Gojra', 'Bahawalnagar',
            'Samundri', 'Jaranwala', 'Chishtian', 'Wazirabad', 'Layyah', 'Khushab',
            'Mianwali', 'Lodhran', 'Hasilpur', 'Bhakkar', 'Arif Wala', 'Narowal',
            'Bhalwal', 'Haripur', 'Nowshera', 'Swabi', 'Kohat', 'Dera Ismail Khan',
            'Sukkur', 'Larkana', 'Hyderabad', 'Nawabshah', 'Mirpur Khas', 'Jacobabad',
            'Shikarpur', 'Khairpur', 'Dadu', 'Tando Adam', 'Tando Allahyar', 'Badin',
            'Ghotki', 'Turbat', 'Khuzdar', 'Hub', 'Chaman',
            'Dubai', 'Abu Dhabi', 'Sharjah', 'Doha', 'Riyadh', 'Jeddah', 'Muscat',
            'Kuwait', 'London', 'Birmingham', 'Manchester', 'New York', 'Toronto'
        );

        if(is_array($fieldData))
        {
            foreach($fieldData as $field)
            {
                $rawName = isset($field['name']) ? trim((string)$field['name']) : '';
                $fname = strtolower($rawName);
                $fval = isset($field['values'][0]) ? trim((string)$field['values'][0]) : '';
                if($fval === '') continue;

                // 1. Phone / Mobile
                if(strpos($fname, 'phone') !== false || strpos($fname, 'mobile') !== false || strpos($fname, 'contact_no') !== false) {
                    if(empty($parsed['phone'])) {
                        $parsed['phone'] = $fval;
                    }
                }
                // 2. Email
                elseif(strpos($fname, 'email') !== false) {
                    if(empty($parsed['email'])) {
                        $parsed['email'] = $fval;
                    }
                }
                // 3. Client Real Name (Strict Priority: Person Name > Company Name)
                elseif($fname === 'full_name' || $fname === 'fullname' || $fname === 'your_name' || $fname === 'client_name' || $fname === 'customer_name' || $fname === 'applicant_name') {
                    $parsed['name'] = $fval;
                }
                elseif($fname === 'first_name' || $fname === 'fname') {
                    $parsed['first_name'] = $fval;
                }
                elseif($fname === 'last_name' || $fname === 'lname') {
                    $parsed['last_name'] = $fval;
                }
                elseif($fname === 'name') {
                    // Exact match on 'name' only (not company_name or page_name)
                    if(empty($parsed['name'])) {
                        $parsed['name'] = $fval;
                    }
                }
                // 4. Company Name / Business Name (Keep separate from client person name!)
                elseif(strpos($fname, 'company') !== false || strpos($fname, 'business') !== false || strpos($fname, 'organization') !== false) {
                    $parsed['company_name'] = $fval;
                    $customAnswers[] = 'Company: ' . $fval;
                }
                // 5. City Name
                elseif(strpos($fname, 'city') !== false || strpos($fname, 'user_city') !== false || strpos($fname, 'location') !== false || strpos($fname, 'town') !== false || strpos($fname, 'district') !== false) {
                    $parsed['city'] = ucwords(strtolower($fval));
                }
                // 6. Budget / Price
                elseif(strpos($fname, 'budget') !== false || strpos($fname, 'price') !== false) {
                    $parsed['budget'] = $fval;
                    $customAnswers[] = 'Budget: ' . $fval;
                }
                // 7. Gender
                elseif(strpos($fname, 'gender') !== false) {
                    $parsed['gender'] = $fval;
                    $customAnswers[] = 'Gender: ' . $fval;
                }
                // 8. Custom questions / other data
                else {
                    $customAnswers[] = $rawName . ': ' . $fval;
                }
            }
        }

        // Combine first_name and last_name if full_name wasn't explicitly provided
        if(empty($parsed['name'])) {
            $combined = trim($parsed['first_name'] . ' ' . $parsed['last_name']);
            if($combined !== '') {
                $parsed['name'] = $combined;
            }
        }

        // Fallback for name: if person name is still empty, use company_name or Facebook Lead #ID
        if(empty($parsed['name'])) {
            if(!empty($parsed['company_name'])) {
                $parsed['name'] = $parsed['company_name'];
            } else {
                $parsed['name'] = 'Facebook Lead #' . substr((string)$leadgenId, -6);
            }
        }

        // Auto-detect city from address/company or custom text if city is still empty
        if(empty($parsed['city'])) {
            $textToSearch = $parsed['company_name'] . ' ' . implode(' ', $customAnswers);
            foreach($knownCities as $kc) {
                if(preg_match('/\b' . preg_quote($kc, '/') . '\b/i', $textToSearch)) {
                    $parsed['city'] = $kc;
                    break;
                }
            }
        }

        if(!empty($customAnswers)) {
            $parsed['remarks'] = implode(" | ", $customAnswers);
        }

        return $parsed;
    }


    protected function _ensureBackendTimezone()
    {
        if(!empty($this->siteTimezone))
        {
            date_default_timezone_set($this->siteTimezone);
            Configure::write('Config.timezone', $this->siteTimezone);
            return $this->siteTimezone;
        }
        try {
            $ConfigModel = ClassRegistry::init('Configuration');
            $cfg = $ConfigModel->find('first', array('recursive' => -1));
            $tz = !empty($cfg['Configuration']['timezone']) ? trim($cfg['Configuration']['timezone']) : 'Asia/Karachi';
            $this->siteTimezone = $tz;
            date_default_timezone_set($tz);
            Configure::write('Config.timezone', $tz);
            return $tz;
        } catch(Exception $e) {
            $tz = 'Asia/Karachi';
            $this->siteTimezone = $tz;
            date_default_timezone_set($tz);
            Configure::write('Config.timezone', $tz);
            return $tz;
        }
    }

    protected function _ensureFacebookIntegrationSchema()
    {
        try {
            $this->FacebookAgentAssignment->ensureSchema();
            $this->FacebookAgentAssignment->autoSeedAgentsIfEmpty();
        } catch(Exception $e) {}

        try {
            $this->FacebookSetting->query("ALTER TABLE `facebook_settings` ADD COLUMN `notify_all_agents_email` TINYINT(1) NOT NULL DEFAULT 0 AFTER `notify_agent_email`");
        } catch(Exception $e) {}

        try {
            $this->Lead->query("ALTER TABLE `leads` MODIFY COLUMN `user_id` INT(11) NOT NULL DEFAULT 1");
        } catch(Exception $e) {}

        try {
            $this->Lead->query("ALTER TABLE `leads` MODIFY COLUMN `status_id` INT(11) NOT NULL DEFAULT 1");
        } catch(Exception $e) {}

        try {
            $this->FacebookNotificationLog->query("CREATE TABLE IF NOT EXISTS `facebook_notification_logs` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `lead_id` int(11) DEFAULT NULL,
              `lead_name` varchar(150) DEFAULT NULL,
              `lead_phone` varchar(50) DEFAULT NULL,
              `lead_email` varchar(150) DEFAULT NULL,
              `form_name` varchar(150) DEFAULT NULL,
              `project_name` varchar(150) DEFAULT NULL,
              `channel` varchar(20) NOT NULL DEFAULT 'email',
              `recipient_type` varchar(30) NOT NULL DEFAULT 'agent',
              `recipient_name` varchar(150) DEFAULT NULL,
              `recipient_target` varchar(150) DEFAULT NULL,
              `subject` varchar(255) DEFAULT NULL,
              `message_preview` text DEFAULT NULL,
              `trigger_type` varchar(30) NOT NULL DEFAULT 'auto_sync',
              `status` varchar(20) NOT NULL DEFAULT 'success',
              `error_message` text DEFAULT NULL,
              `resend_count` int(11) NOT NULL DEFAULT 0,
              `last_resend_at` datetime DEFAULT NULL,
              `created` datetime DEFAULT NULL,
              `modified` datetime DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `lead_id` (`lead_id`),
              KEY `channel` (`channel`),
              KEY `status` (`status`),
              KEY `recipient_type` (`recipient_type`),
              KEY `created` (`created`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        } catch(Exception $e) {}
    }

    protected function _formatToBackendDateTime($metaCreatedTime = null)
    {
        $backendTzName = $this->_ensureBackendTimezone();
        try {
            $targetTz = new DateTimeZone($backendTzName);
            if(!empty($metaCreatedTime))
            {
                if(is_numeric($metaCreatedTime))
                {
                    $dt = new DateTime('@' . (int)$metaCreatedTime);
                }
                else
                {
                    $dt = new DateTime((string)$metaCreatedTime);
                }
                $dt->setTimezone($targetTz);
                return $dt->format('Y-m-d H:i:s');
            }
            $now = new DateTime('now', $targetTz);
            return $now->format('Y-m-d H:i:s');
        } catch(Exception $e) {
            return date('Y-m-d H:i:s');
        }
    }

    protected function _processSingleFacebookLead($leadgenId, $formId = '', $pageId = '', $rawPayload = '')
    {
        $backendTz = $this->_ensureBackendTimezone();
        $leadgenId = trim((string)$leadgenId);
        if($leadgenId === '')
        {
            return array('success' => false, 'status' => 'Error', 'message' => 'Empty leadgen_id');
        }

        // Check 1: Check if lead was already ingested into leads table with this source_form_id
        $existingLeadByGen = $this->Lead->find('first', array(
            'conditions' => array('Lead.source_form_id' => $leadgenId),
            'recursive' => -1
        ));
        if(!empty($existingLeadByGen))
        {
            return array('success' => true, 'duplicate' => true, 'status' => 'Duplicate', 'lead_id' => $existingLeadByGen['Lead']['id'], 'message' => 'Lead already exists in CRM');
        }

        // Check 2: Check if leadgen_id was already logged in facebook_lead_logs (any status)
        $existingLog = $this->FacebookLeadLog->find('first', array(
            'conditions' => array(
                'FacebookLeadLog.facebook_leadgen_id' => $leadgenId
            ),
            'recursive' => -1
        ));
        if(!empty($existingLog))
        {
            $existingLeadId = !empty($existingLog['FacebookLeadLog']['lead_id']) ? (int)$existingLog['FacebookLeadLog']['lead_id'] : 0;
            return array(
                'success' => true,
                'duplicate' => true,
                'status' => 'Duplicate',
                'lead_id' => $existingLeadId,
                'message' => 'Facebook Lead ' . $leadgenId . ' was already processed'
            );
        }

        // Find matching Facebook Account by page_id
        $account = null;
        if($pageId !== '')
        {
            $account = $this->FacebookAccount->find('first', array(
                'conditions' => array('FacebookAccount.page_id' => $pageId, 'FacebookAccount.is_active' => 1),
                'recursive' => -1
            ));
        }
        if(empty($account))
        {
            $account = $this->FacebookAccount->find('first', array(
                'conditions' => array('FacebookAccount.is_active' => 1),
                'order' => array('FacebookAccount.id' => 'ASC'),
                'recursive' => -1
            ));
        }

        $token = !empty($account['FacebookAccount']['page_access_token']) ? trim($account['FacebookAccount']['page_access_token']) : '';

        // Check if payload already contains field_data
        $leadDetails = is_array($rawPayload) ? $rawPayload : @json_decode($rawPayload, true);
        if(empty($leadDetails['field_data']) && $token !== '')
        {
            $url = "https://graph.facebook.com/v19.0/" . urlencode($leadgenId) . "?access_token=" . urlencode($token);
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 20);
            $resp = curl_exec($ch);
            curl_close($ch);
            $leadDetails = @json_decode($resp, true);
        }

        $fieldData = !empty($leadDetails['field_data']) ? $leadDetails['field_data'] : array();
        $parsed = $this->_parseFacebookLeadData($fieldData, $leadgenId);

        $metaCreated = !empty($leadDetails['created_time']) ? $leadDetails['created_time'] : null;
        $backendCreatedTime = $this->_formatToBackendDateTime($metaCreated);
        $backendNowObj = new DateTime('now', new DateTimeZone($this->_ensureBackendTimezone()));
        $backendNowStr = $backendNowObj->format('Y-m-d H:i:s');
        $backendLastContact = $backendNowObj->format('Y-m-d');
        $backendFollowUp = (clone $backendNowObj)->modify('+1 day')->format('Y-m-d H:i:s');

        // Form mapping lookup
        $formMapping = null;
        if($formId !== '')
        {
            $formMapping = $this->FacebookFormMapping->find('first', array(
                'conditions' => array('FacebookFormMapping.facebook_form_id' => $formId, 'FacebookFormMapping.is_active' => 1),
                'recursive' => -1
            ));
        }

        $projectId = !empty($formMapping['FacebookFormMapping']['project_id']) ? (int)$formMapping['FacebookFormMapping']['project_id'] : (!empty($account['FacebookAccount']['project_id']) ? (int)$account['FacebookAccount']['project_id'] : null);
        $statusId = !empty($formMapping['FacebookFormMapping']['default_status_id']) ? (int)$formMapping['FacebookFormMapping']['default_status_id'] : (!empty($account['FacebookAccount']['default_status_id']) ? (int)$account['FacebookAccount']['default_status_id'] : 1);

        // Check 3: Normalized Phone Deduplication
        $cleanPhone = preg_replace('/[^0-9]/', '', $parsed['phone']);
        $corePhone = preg_replace('/^(0092|92|0)/', '', $cleanPhone);

        $existingLead = null;
        if(!empty($corePhone) && strlen($corePhone) >= 7)
        {
            $phoneConditions = array(
                'OR' => array(
                    'Lead.lead_mobile' => $parsed['phone'],
                    'Lead.lead_mobile LIKE' => '%' . $corePhone
                )
            );
            if(!empty($projectId) && (int)$projectId > 0)
            {
                $phoneConditions['Lead.project_id'] = (int)$projectId;
            }

            $existingLead = $this->Lead->find('first', array(
                'conditions' => $phoneConditions,
                'order' => array('Lead.id' => 'DESC'),
                'recursive' => -1
            ));
        }

        if(!empty($existingLead))
        {
            $existingLeadId = (int)$existingLead['Lead']['id'];

            // Enrich existing lead if missing real name or city
            $leadUpdates = array();
            if(!empty($parsed['name']) && (empty($existingLead['Lead']['lead_name']) || strpos($existingLead['Lead']['lead_name'], 'Facebook Lead') !== false || $existingLead['Lead']['lead_name'] === 'No self' || $existingLead['Lead']['lead_name'] === 'Own Businiess'))
            {
                $leadUpdates['lead_name'] = $parsed['name'];
            }
            if(!empty($parsed['city']) && empty($existingLead['Lead']['lead_city']))
            {
                $leadUpdates['lead_city'] = $parsed['city'];
            }
            if(!empty($parsed['remarks']) && empty($existingLead['Lead']['remarks']))
            {
                $leadUpdates['remarks'] = $parsed['remarks'];
            }
            if(!empty($leadUpdates))
            {
                $this->Lead->id = $existingLeadId;
                $this->Lead->save($leadUpdates);
            }

            // Record as Duplicate in log to preserve FB audit trail without creating duplicate CRM leads
            try
            {
                $existingLogCheck = $this->FacebookLeadLog->find('first', array(
                    'conditions' => array('FacebookLeadLog.facebook_leadgen_id' => $leadgenId),
                    'recursive' => -1
                ));
                if(empty($existingLogCheck))
                {
                    $this->FacebookLeadLog->create();
                    $this->FacebookLeadLog->save(array('FacebookLeadLog' => array(
                        'facebook_leadgen_id' => $leadgenId,
                        'form_id' => $formId,
                        'form_name' => !empty($formMapping['FacebookFormMapping']['form_name']) ? $formMapping['FacebookFormMapping']['form_name'] : (!empty($account['FacebookAccount']['account_name']) ? $account['FacebookAccount']['account_name'] : 'Lead Form'),
                        'page_id' => $pageId,
                        'lead_id' => $existingLeadId,
                        'assigned_user_id' => (int)$existingLead['Lead']['user_id'],
                        'lead_name' => $parsed['name'],
                        'lead_mobile' => $parsed['phone'],
                        'lead_email' => $parsed['email'],
                        'lead_city' => $parsed['city'],
                        'raw_payload' => !empty($leadDetails) ? json_encode($leadDetails) : (is_string($rawPayload) ? $rawPayload : json_encode($rawPayload)),
                        'status' => 'Duplicate',
                        'error_message' => 'Duplicate phone number already exists in CRM (Lead #' . $existingLeadId . ')',
                        'created' => $backendCreatedTime
                    )));
                }
                else
                {
                    $this->FacebookLeadLog->id = $existingLogCheck['FacebookLeadLog']['id'];
                    $this->FacebookLeadLog->saveField('lead_id', $existingLeadId);
                    $this->FacebookLeadLog->saveField('status', 'Duplicate');
                }
            }
            catch(Exception $e)
            {
                // Catch any duplicate constraint violation safely
            }

            return array('success' => true, 'duplicate' => true, 'status' => 'Duplicate', 'lead_id' => $existingLeadId, 'message' => 'Phone number already exists in CRM');
        }

        // 1-by-1 Round-Robin Agent Assignment
        $assignedUserId = 0;
        if(!empty($formMapping['FacebookFormMapping']['default_agent_id']))
        {
            $candAgentId = (int)$formMapping['FacebookFormMapping']['default_agent_id'];
            $candUser = $this->User->find('first', array(
                'fields' => array('User.id', 'User.login_status', 'User.status'),
                'conditions' => array('User.id' => $candAgentId, 'User.deleted IS NULL'),
                'recursive' => -1
            ));
            if(!empty($candUser['User']['id']) && (string)$candUser['User']['status'] === 'Active' && (empty($candUser['User']['login_status']) || strcasecmp(trim((string)$candUser['User']['login_status']), 'Blocked') !== 0))
            {
                $assignedUserId = $candAgentId;
            }
            else
            {
                $assignedUserId = (int)$this->FacebookAgentAssignment->getNextAssignedAgentId($projectId);
            }
        }
        else
        {
            $assignedUserId = (int)$this->FacebookAgentAssignment->getNextAssignedAgentId($projectId);
        }

        // On fresh cPanel installs or when agent pool is empty, guarantee assignedUserId is never 0
        if($assignedUserId <= 0)
        {
            $fallbackAgent = $this->User->find('first', array(
                'conditions' => array(
                    'User.status' => 'Active',
                    'User.ugroup_id' => array(2, 3, 4),
                    'OR' => array(
                        'User.login_status IS NULL',
                        'User.login_status !=' => 'Blocked'
                    )
                ),
                'order' => array('User.id' => 'ASC'),
                'recursive' => -1
            ));
            if(!empty($fallbackAgent['User']['id']))
            {
                $assignedUserId = (int)$fallbackAgent['User']['id'];
            }
            else
            {
                $firstUser = $this->User->find('first', array('conditions' => array('User.status' => 'Active'), 'order' => array('User.id' => 'ASC'), 'recursive' => -1));
                $assignedUserId = !empty($firstUser['User']['id']) ? (int)$firstUser['User']['id'] : 1;
            }
        }

        // If project_id was not explicitly resolved from mapping/account, derive it from the assigned agent!
        if((empty($projectId) || (int)$projectId <= 0) && $assignedUserId > 0)
        {
            $assignedAgentRow = $this->User->find('first', array(
                'fields' => array('User.id', 'User.project_id'),
                'conditions' => array('User.id' => $assignedUserId),
                'recursive' => -1
            ));
            if(!empty($assignedAgentRow['User']['project_id']))
            {
                $projectId = (int)$assignedAgentRow['User']['project_id'];
            }
        }

        if(empty($projectId) || (int)$projectId <= 0)
        {
            $defaultProj = $this->Project->find('first', array('order' => array('Project.id' => 'ASC'), 'recursive' => -1));
            $projectId = !empty($defaultProj['Project']['id']) ? (int)$defaultProj['Project']['id'] : 1;
        }

        if(empty($statusId) || (int)$statusId <= 0)
        {
            $statusId = 1;
        }

        // Link or create Client if phone/email matches
        $clientId = null;
        if(!empty($parsed['phone']) || !empty($parsed['email']))
        {
            $clientCond = array();
            if(!empty($parsed['phone'])) $clientCond['Client.mobile'] = $parsed['phone'];
            elseif(!empty($parsed['email'])) $clientCond['Client.email'] = $parsed['email'];

            $client = $this->Client->find('first', array('conditions' => $clientCond, 'recursive' => -1));
            if(!empty($client['Client']['id']))
            {
                $clientId = (int)$client['Client']['id'];
                $clientUpdate = array();
                if(!empty($parsed['city']) && empty($client['Client']['city']))
                {
                    $clientUpdate['city'] = $parsed['city'];
                }
                if(!empty($projectId) && empty($client['Client']['project_id']))
                {
                    $clientUpdate['project_id'] = $projectId;
                }
                if(!empty($assignedUserId) && empty($client['Client']['user_id']))
                {
                    $clientUpdate['user_id'] = $assignedUserId;
                }
                if(!empty($clientUpdate))
                {
                    $this->Client->id = $clientId;
                    $this->Client->save($clientUpdate, array('validate' => false));
                }
            }
            else
            {
                // Create Client record so it connects seamlessly to CRM contact management
                $clientSaveData = array(
                    'name' => !empty($parsed['name']) ? $parsed['name'] : 'Facebook Lead Contact',
                    'mobile' => !empty($parsed['phone']) ? $parsed['phone'] : null,
                    'phone' => !empty($parsed['phone']) ? $parsed['phone'] : null,
                    'email' => !empty($parsed['email']) ? $parsed['email'] : null,
                    'city' => !empty($parsed['city']) ? $parsed['city'] : null,
                    'project_id' => $projectId,
                    'user_id' => $assignedUserId,
                    'status' => 'Active',
                    'reg_status' => 'Live',
                    'created' => $backendCreatedTime,
                    'modified' => $backendNowStr
                );
                $this->Client->create();
                if($this->Client->save($clientSaveData, array('validate' => false)))
                {
                    $clientId = (int)$this->Client->id;
                }
            }
        }

        // Create new unique Lead
        $leadRecord = array(
            'client_id' => $clientId,
            'lead_name' => $parsed['name'],
            'lead_mobile' => $parsed['phone'],
            'lead_email' => $parsed['email'],
            'lead_city' => $parsed['city'],
            'lead_source' => 'Facebook Lead Ads',
            'source_form_id' => $leadgenId,
            'user_id' => $assignedUserId,
            'project_id' => $projectId,
            'status_id' => $statusId,
            'remarks' => $parsed['remarks'] !== '' ? $parsed['remarks'] : 'Ingested from Facebook Lead Ads (Form ID: ' . $formId . ')',
            'last_contact_date' => $backendLastContact,
            'follow_up' => $backendFollowUp,
            'created' => $backendCreatedTime,
            'modified' => $backendNowStr
        );

        $this->Lead->create();
        $this->Lead->save(array('Lead' => $leadRecord), array('validate' => false));
        $createdLeadId = (int)$this->Lead->id;

        // Record initial assignment history in LeadHistory
        try {
            $LeadHistory = ClassRegistry::init('LeadHistory');
            if($LeadHistory && $createdLeadId > 0) {
                $LeadHistory->ensureSchema();
                $agentName = 'Unassigned';
                if($assignedUserId > 0) {
                    $assignedUserRow = $this->User->findById($assignedUserId);
                    $agentName = !empty($assignedUserRow['User']['name']) ? $assignedUserRow['User']['name'] : "User #{$assignedUserId}";
                }
                $LeadHistory->create();
                $LeadHistory->save(array('LeadHistory' => array(
                    'lead_id' => $createdLeadId,
                    'project_id' => $projectId,
                    'field_name' => 'lead_source',
                    'old_value' => '',
                    'new_value' => 'Facebook Lead Ads',
                    'remarks' => "Lead created via Facebook Lead Ads and assigned to {$agentName}",
                    'created' => $backendCreatedTime
                )));
            }
        } catch(Exception $e) {}

        // Auto Dispatch SMS & Email Notifications to Agent & Lead Client
        $projectNameStr = !empty($formMapping['FacebookFormMapping']['form_name']) ? $formMapping['FacebookFormMapping']['form_name'] : (!empty($account['FacebookAccount']['account_name']) ? $account['FacebookAccount']['account_name'] : 'White Villas Project');
        $formNameStr = !empty($formMapping['FacebookFormMapping']['form_name']) ? $formMapping['FacebookFormMapping']['form_name'] : 'Facebook Lead Ads';
        $this->_dispatchFacebookLeadNotifications($createdLeadId, $parsed, $formNameStr, $projectNameStr, $assignedUserId, $backendCreatedTime);

        // Log Facebook Lead Ingestion
        $logRecord = array(
            'facebook_leadgen_id' => $leadgenId,
            'form_id' => $formId,
            'form_name' => !empty($formMapping['FacebookFormMapping']['form_name']) ? $formMapping['FacebookFormMapping']['form_name'] : (!empty($account['FacebookAccount']['account_name']) ? $account['FacebookAccount']['account_name'] : 'Lead Form'),
            'page_id' => $pageId,
            'lead_id' => $createdLeadId,
            'assigned_user_id' => $assignedUserId,
            'lead_name' => $parsed['name'],
            'lead_mobile' => $parsed['phone'],
            'lead_email' => $parsed['email'],
            'lead_city' => $parsed['city'],
            'raw_payload' => !empty($leadDetails) ? json_encode($leadDetails) : (is_string($rawPayload) ? $rawPayload : json_encode($rawPayload)),
            'status' => 'Success',
            'created' => $backendCreatedTime
        );

        try
        {
            $existingLogCheck = $this->FacebookLeadLog->find('first', array(
                'conditions' => array('FacebookLeadLog.facebook_leadgen_id' => $leadgenId),
                'recursive' => -1
            ));
            if(!empty($existingLogCheck))
            {
                $this->FacebookLeadLog->id = $existingLogCheck['FacebookLeadLog']['id'];
                $this->FacebookLeadLog->save($logRecord);
            }
            else
            {
                $this->FacebookLeadLog->create();
                $this->FacebookLeadLog->save(array('FacebookLeadLog' => $logRecord));
            }
        }
        catch(Exception $logEx)
        {
            // Safely catch 1062 duplicate entry or DB constraint violation
        }

        // Update account counter
        if(!empty($account['FacebookAccount']['id']))
        {
            $accId = (int)$account['FacebookAccount']['id'];
            $this->FacebookAccount->updateAll(
                array(
                    'FacebookAccount.total_leads_synced' => 'FacebookAccount.total_leads_synced + 1',
                    'FacebookAccount.last_sync_at' => "'" . date('Y-m-d H:i:s') . "'"
                ),
                array('FacebookAccount.id' => $accId)
            );
        }

        return array('success' => true, 'lead_id' => $createdLeadId, 'assigned_to' => $assignedUserId);
    }

    /**
     * Ensures all records in facebook_lead_logs are synced into the main CRM leads table
     * If any Facebook lead log is unlinked (lead_id = 0, NULL, or missing from leads table),
     * this automatically creates the Client and Lead record, assigns the agent, links the ID,
     * and sets status to 'Success'.
     */
    public function _ensureAllFacebookLogsInCrmLeads()
    {
        try {
            $syncedCount = 0;
            $backendNowStr = date('Y-m-d H:i:s');

            // 1. Two-way sync: Copy any Facebook leads from leads table into facebook_lead_logs if missing
            $missingInLogsSql = "INSERT IGNORE INTO facebook_lead_logs (facebook_leadgen_id, form_id, form_name, page_id, lead_id, assigned_user_id, lead_name, lead_mobile, lead_email, lead_city, status, created)
                SELECT 
                    CONCAT('crm_', l.id),
                    IF(l.source_form_id IS NOT NULL AND l.source_form_id != '', l.source_form_id, 'facebook_lead'),
                    'Facebook Lead Ads',
                    'me',
                    l.id,
                    IF(l.user_id > 0, l.user_id, IF(c.user_id > 0, c.user_id, 1)),
                    IF(l.lead_name IS NOT NULL AND l.lead_name != '', l.lead_name, IF(c.name IS NOT NULL AND c.name != '', c.name, 'Facebook Lead')),
                    IF(l.lead_mobile IS NOT NULL AND l.lead_mobile != '', l.lead_mobile, IF(c.mobile IS NOT NULL AND c.mobile != '', c.mobile, '')),
                    IF(l.lead_email IS NOT NULL AND l.lead_email != '', l.lead_email, IF(c.email IS NOT NULL AND c.email != '', c.email, '')),
                    IF(l.lead_city IS NOT NULL AND l.lead_city != '', l.lead_city, ''),
                    'Success',
                    CASE WHEN l.created IS NOT NULL AND l.created > '2000-01-01 00:00:00' THEN l.created ELSE NOW() END
                FROM leads l
                LEFT JOIN clients c ON l.client_id = c.id
                LEFT JOIN facebook_lead_logs log ON log.lead_id = l.id
                WHERE log.id IS NULL 
                  AND (l.lead_source LIKE '%Facebook%' OR l.lead_source LIKE '%FB%' OR (l.source_form_id IS NOT NULL AND l.source_form_id != '' AND l.source_form_id != '0'))";
            $this->FacebookLeadLog->query($missingInLogsSql);

            // 2. Automated Deduplication & Cleanup of duplicate/orphaned lead logs
            // a. If unlinked logs (lead_id = 0) have phone numbers matching a CRM Facebook lead that already has a log, delete the unlinked duplicate log
            $this->FacebookLeadLog->query("DELETE f FROM facebook_lead_logs f
                INNER JOIN leads l ON l.lead_mobile = f.lead_mobile AND f.lead_mobile != ''
                INNER JOIN facebook_lead_logs existing ON existing.lead_id = l.id
                WHERE (f.lead_id IS NULL OR f.lead_id = 0)
                AND (l.lead_source LIKE '%Facebook%' OR l.lead_source LIKE '%FB%')
                AND f.id != existing.id");

            // b. If unlinked logs match a CRM lead without a log, link it
            $this->FacebookLeadLog->query("UPDATE facebook_lead_logs f
                INNER JOIN leads l ON l.lead_mobile = f.lead_mobile AND f.lead_mobile != ''
                SET f.lead_id = l.id, f.assigned_user_id = IF(l.user_id > 0, l.user_id, f.assigned_user_id), f.status = 'Success'
                WHERE (f.lead_id IS NULL OR f.lead_id = 0)
                AND (l.lead_source LIKE '%Facebook%' OR l.lead_source LIKE '%FB%')");

            // c. Delete orphaned logs where lead does not exist in CRM
            $this->FacebookLeadLog->query("DELETE f FROM facebook_lead_logs f
                LEFT JOIN leads l ON f.lead_id = l.id
                WHERE f.lead_id IS NULL OR f.lead_id = 0 OR l.id IS NULL");

            // d. Deduplicate multiple logs pointing to the SAME lead_id: prefer authentic Meta leadgen_id over synthetic crm_%
            $this->FacebookLeadLog->query("DELETE f1 FROM facebook_lead_logs f1
                INNER JOIN facebook_lead_logs f2 ON f1.lead_id = f2.lead_id AND f1.id != f2.id
                WHERE f1.facebook_leadgen_id LIKE 'crm_%' AND f2.facebook_leadgen_id NOT LIKE 'crm_%'");

            // e. If multiple duplicate logs still remain on the same lead_id, keep the newest log (highest id)
            $this->FacebookLeadLog->query("DELETE f1 FROM facebook_lead_logs f1
                INNER JOIN facebook_lead_logs f2 ON f1.lead_id = f2.lead_id AND f1.id < f2.id");

            // 3. Synchronize assigned agent between facebook_lead_logs and leads/clients
            // If lead has assigned user, ensure facebook_lead_logs reflects it
            $this->FacebookLeadLog->query("UPDATE facebook_lead_logs log
                INNER JOIN leads l ON log.lead_id = l.id
                SET log.assigned_user_id = l.user_id
                WHERE l.user_id > 0 AND (log.assigned_user_id IS NULL OR log.assigned_user_id = 0 OR log.assigned_user_id != l.user_id)");

            // If log has assigned user, ensure leads & clients reflect it
            $this->FacebookLeadLog->query("UPDATE leads l
                INNER JOIN facebook_lead_logs log ON log.lead_id = l.id
                SET l.user_id = log.assigned_user_id
                WHERE log.assigned_user_id > 0 AND (l.user_id IS NULL OR l.user_id = 0)");

            $this->FacebookLeadLog->query("UPDATE clients c
                INNER JOIN leads l ON l.client_id = c.id
                INNER JOIN facebook_lead_logs log ON log.lead_id = l.id
                SET c.user_id = log.assigned_user_id
                WHERE log.assigned_user_id > 0 AND (c.user_id IS NULL OR c.user_id = 0)");

            // Align leads.project_id with assigned agent's agency project if missing
            $this->Lead->query("UPDATE leads l
                INNER JOIN users u ON l.user_id = u.id
                SET l.project_id = u.project_id
                WHERE (l.project_id IS NULL OR l.project_id = 0) AND l.user_id > 0 AND u.project_id > 0");

            // Update form names from form mappings where available
            $this->FacebookLeadLog->query("UPDATE facebook_lead_logs log
                INNER JOIN facebook_form_mappings ffm ON ffm.facebook_form_id = log.form_id
                SET log.form_name = ffm.form_name
                WHERE ffm.form_name IS NOT NULL AND ffm.form_name != '' AND (log.form_name = '' OR log.form_name = 'Facebook Lead Ads' OR log.form_name IS NULL)");

            // 3. Find all unlinked logs (logs that have lead_id = 0 or missing in leads)
            $sql = "SELECT log.* FROM facebook_lead_logs log " .
                   "LEFT JOIN leads l ON l.id = log.lead_id " .
                   "WHERE log.lead_id IS NULL OR log.lead_id = 0 OR l.id IS NULL " .
                   "ORDER BY log.id ASC LIMIT 100";
            $unlinkedLogs = $this->FacebookLeadLog->query($sql);

            if(empty($unlinkedLogs))
            {
                return $syncedCount;
            }

            // Find fallback project and status once
            $defaultProject = $this->Project->find('first', array(
                'fields' => array('Project.id'),
                'order' => array('Project.id' => 'ASC'),
                'recursive' => -1
            ));
            $fallbackProjectId = !empty($defaultProject['Project']['id']) ? (int)$defaultProject['Project']['id'] : 1;

            $defaultStatus = $this->Status->find('first', array(
                'fields' => array('Status.id'),
                'order' => array('Status.id' => 'ASC'),
                'recursive' => -1
            ));
            $fallbackStatusId = !empty($defaultStatus['Status']['id']) ? (int)$defaultStatus['Status']['id'] : 1;

            foreach($unlinkedLogs as $row)
            {
                $log = isset($row['log']) ? $row['log'] : (isset($row['FacebookLeadLog']) ? $row['FacebookLeadLog'] : (isset($row['facebook_lead_logs']) ? $row['facebook_lead_logs'] : $row));
                $logId = !empty($log['id']) ? (int)$log['id'] : 0;
                if($logId <= 0) continue;

                $leadgenId = !empty($log['facebook_leadgen_id']) ? trim((string)$log['facebook_leadgen_id']) : '';
                $formId = !empty($log['form_id']) ? trim((string)$log['form_id']) : '';
                $name = !empty($log['lead_name']) ? trim((string)$log['lead_name']) : 'Facebook Lead';
                $mobile = !empty($log['lead_mobile']) ? trim((string)$log['lead_mobile']) : '';
                $email = !empty($log['lead_email']) ? trim((string)$log['lead_email']) : '';
                $city = !empty($log['lead_city']) ? trim((string)$log['lead_city']) : '';
                $assignedUserId = !empty($log['assigned_user_id']) ? (int)$log['assigned_user_id'] : 0;
                $logCreated = !empty($log['created']) ? $log['created'] : $backendNowStr;

                // Check if a CRM lead already exists with this source_form_id ($leadgenId)
                $existingLead = null;
                if($leadgenId !== '')
                {
                    $existingLead = $this->Lead->find('first', array(
                        'conditions' => array('Lead.source_form_id' => $leadgenId),
                        'recursive' => -1
                    ));
                }

                // If not found by leadgenId, check by mobile
                if(empty($existingLead['Lead']['id']) && $mobile !== '')
                {
                    $mobileClean = preg_replace('/[^0-9]/', '', $mobile);
                    $subMobile = strlen($mobileClean) > 7 ? substr($mobileClean, -7) : $mobileClean;
                    if(strlen($subMobile) >= 7)
                    {
                        $existingLead = $this->Lead->find('first', array(
                            'conditions' => array(
                                'Lead.lead_mobile LIKE' => "%{$subMobile}%",
                                'OR' => array(
                                    array('Lead.lead_source LIKE' => '%Facebook%'),
                                    array('Lead.lead_source LIKE' => '%FB%')
                                )
                            ),
                            'recursive' => -1
                        ));
                    }
                }

                if(!empty($existingLead['Lead']['id']))
                {
                    $foundLeadId = (int)$existingLead['Lead']['id'];
                    $leadAgentId = !empty($existingLead['Lead']['user_id']) ? (int)$existingLead['Lead']['user_id'] : $assignedUserId;
                    $this->FacebookLeadLog->query("UPDATE facebook_lead_logs SET lead_id = {$foundLeadId}, assigned_user_id = {$leadAgentId}, status = 'Success' WHERE id = {$logId}");
                    $syncedCount++;
                    continue;
                }

                // Resolve project_id from form mapping
                $projectId = 0;
                $leadStatusId = $fallbackStatusId;
                if($formId !== '')
                {
                    $mapping = $this->FacebookFormMapping->find('first', array(
                        'conditions' => array('FacebookFormMapping.facebook_form_id' => $formId),
                        'recursive' => -1
                    ));
                    if(!empty($mapping['FacebookFormMapping']['project_id']))
                    {
                        $projectId = (int)$mapping['FacebookFormMapping']['project_id'];
                    }
                    if(!empty($mapping['FacebookFormMapping']['default_status_id']))
                    {
                        $leadStatusId = (int)$mapping['FacebookFormMapping']['default_status_id'];
                    }
                    if(!empty($mapping['FacebookFormMapping']['default_agent_id']) && $assignedUserId <= 0)
                    {
                        $candAgentId = (int)$mapping['FacebookFormMapping']['default_agent_id'];
                        $candUser = $this->User->find('first', array(
                            'fields' => array('User.id', 'User.login_status', 'User.status'),
                            'conditions' => array('User.id' => $candAgentId, 'User.deleted IS NULL'),
                            'recursive' => -1
                        ));
                        if(!empty($candUser['User']['id']) && (string)$candUser['User']['status'] === 'Active' && (empty($candUser['User']['login_status']) || strcasecmp(trim((string)$candUser['User']['login_status']), 'Blocked') !== 0))
                        {
                            $assignedUserId = $candAgentId;
                        }
                    }
                }
                if($projectId <= 0)
                {
                    $projectId = $fallbackProjectId;
                }

                // Resolve assigned user
                if($assignedUserId <= 0)
                {
                    $assignedUserId = (int)$this->FacebookAgentAssignment->getNextAssignedAgentId($projectId);
                }
                if($assignedUserId <= 0)
                {
                    $assignedUserId = 1;
                }

                // Resolve client
                $clientId = 0;
                $clientCond = array();
                if($mobile !== '') $clientCond['Client.mobile'] = $mobile;
                elseif($email !== '') $clientCond['Client.email'] = $email;

                if(!empty($clientCond))
                {
                    $client = $this->Client->find('first', array('conditions' => $clientCond, 'recursive' => -1));
                    if(!empty($client['Client']['id']))
                    {
                        $clientId = (int)$client['Client']['id'];
                    }
                }

                if($clientId <= 0)
                {
                    $clientData = array(
                        'name' => $name,
                        'mobile' => $mobile !== '' ? $mobile : null,
                        'phone' => $mobile !== '' ? $mobile : null,
                        'email' => $email !== '' ? $email : null,
                        'project_id' => $projectId,
                        'user_id' => $assignedUserId,
                        'status' => 'Active',
                        'reg_status' => 'Live',
                        'created' => $logCreated,
                        'modified' => $backendNowStr
                    );
                    $this->Client->create();
                    if($this->Client->save($clientData, array('validate' => false)))
                    {
                        $clientId = (int)$this->Client->id;
                    }
                }

                // Create Lead record
                $leadRecord = array(
                    'client_id' => $clientId,
                    'lead_name' => $name,
                    'lead_mobile' => $mobile,
                    'lead_email' => $email,
                    'lead_city' => $city,
                    'lead_source' => 'Facebook Lead Ads',
                    'source_form_id' => $leadgenId,
                    'user_id' => $assignedUserId,
                    'project_id' => $projectId,
                    'status_id' => $leadStatusId,
                    'remarks' => 'Ingested from Facebook Lead Ads (Form ID: ' . $formId . ')',
                    'last_contact_date' => date('Y-m-d', strtotime($logCreated)),
                    'follow_up' => date('Y-m-d H:i:s', strtotime($logCreated . ' +1 day')),
                    'created' => $logCreated,
                    'modified' => $backendNowStr
                );

                $this->Lead->create();
                if($this->Lead->save(array('Lead' => $leadRecord), array('validate' => false)))
                {
                    $newLeadId = (int)$this->Lead->id;
                    $this->FacebookLeadLog->query("UPDATE facebook_lead_logs SET lead_id = {$newLeadId}, assigned_user_id = {$assignedUserId}, status = 'Success' WHERE id = {$logId}");
                    $syncedCount++;
                }
            }

            return $syncedCount;
        } catch(Exception $e) {
            return 0;
        }
    }

    /**
     * Automatically resolves and guarantees a Page Access Token
     * If a User Token was provided, this exchanges it with Meta for the Page's dedicated Page Access Token.
     */
    protected function _resolvePageAccessToken($pageId, $token)
    {
        $token = trim((string)$token);
        $pageId = trim((string)$pageId);
        if($token === '' || $pageId === '' || $pageId === 'me')
        {
            return $token;
        }

        // 1. First check if token is already a PAGE token
        $debugUrl = "https://graph.facebook.com/v19.0/debug_token?input_token=" . urlencode($token) . "&access_token=" . urlencode($token);
        $ch = curl_init($debugUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $debugResp = curl_exec($ch);
        curl_close($ch);
        $debugJson = @json_decode($debugResp, true);

        if(!empty($debugJson['data']['type']) && strtoupper($debugJson['data']['type']) === 'PAGE')
        {
            return $token; // It's already a Page Access Token!
        }

        // 2. If it's a USER token, query /{page_id}?fields=access_token to get the Page token
        $exchangeUrl = "https://graph.facebook.com/v19.0/" . urlencode($pageId) . "?fields=access_token,name&access_token=" . urlencode($token);
        $ch = curl_init($exchangeUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 12);
        $exchangeResp = curl_exec($ch);
        curl_close($ch);
        $exchangeJson = @json_decode($exchangeResp, true);

        if(!empty($exchangeJson['access_token']))
        {
            // Update saved account with the resolved Page Access Token
            $this->FacebookAccount->updateAll(
                array('FacebookAccount.page_access_token' => "'" . addslashes($exchangeJson['access_token']) . "'"),
                array('FacebookAccount.page_id' => $pageId)
            );
            return $exchangeJson['access_token'];
        }

        // 3. Fallback: query /me/accounts
        $meAccountsUrl = "https://graph.facebook.com/v19.0/me/accounts?fields=id,name,access_token&access_token=" . urlencode($token);
        $ch = curl_init($meAccountsUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 12);
        $meAccountsResp = curl_exec($ch);
        curl_close($ch);
        $meAccountsJson = @json_decode($meAccountsResp, true);

        if(!empty($meAccountsJson['data']) && is_array($meAccountsJson['data']))
        {
            foreach($meAccountsJson['data'] as $p)
            {
                if(!empty($p['id']) && (string)$p['id'] === (string)$pageId && !empty($p['access_token']))
                {
                    $this->FacebookAccount->updateAll(
                        array('FacebookAccount.page_access_token' => "'" . addslashes($p['access_token']) . "'"),
                        array('FacebookAccount.page_id' => $pageId)
                    );
                    return $p['access_token'];
                }
            }
        }

        return $token;
    }
    protected function _fetchFacebookLeadsMultiAccounts()
    {
        // Mutex lock to prevent duplicate concurrent executions across multiple open tabs/crons
        $lockFile = TMP . 'fb_sync.lock';
        $lockFp = @fopen($lockFile, 'c+');
        if($lockFp)
        {
            if(!flock($lockFp, LOCK_EX | LOCK_NB))
            {
                @fclose($lockFp);
                return array('success' => true, 'count' => 0, 'message' => 'Sync is already in progress in another process.');
            }
        }

        try
        {
            $accounts = $this->FacebookAccount->getActiveAccounts();
            if(empty($accounts))
            {
                if($lockFp) { flock($lockFp, LOCK_UN); @fclose($lockFp); }
                return array('success' => false, 'count' => 0, 'message' => 'No active Facebook accounts configured in Settings.');
            }

            $totalIngested = 0;
            $details = array();

            foreach($accounts as $acc)
            {
                $res = $this->_fetchLeadsForAccount($acc['FacebookAccount']);
                if(!empty($res['success']))
                {
                    $totalIngested += $res['count'];
                    $details[] = $acc['FacebookAccount']['account_name'] . " (" . $res['count'] . " leads)";
                }
            }

            if($lockFp) { flock($lockFp, LOCK_UN); @fclose($lockFp); }

            return array(
                'success' => true,
                'count' => $totalIngested,
                'message' => "Scanned " . count($accounts) . " active page(s). " . implode(", ", $details)
            );
        }
        catch(Exception $e)
        {
            if($lockFp) { flock($lockFp, LOCK_UN); @fclose($lockFp); }
            return array('success' => false, 'count' => 0, 'message' => 'Sync error: ' . $e->getMessage());
        }
    }

    protected function _fetchLeadsForAccount($account)
    {
        $pageId = !empty($account['page_id']) ? trim($account['page_id']) : 'me';
        $rawToken = !empty($account['page_access_token']) ? trim($account['page_access_token']) : '';
        $token = $this->_resolvePageAccessToken($pageId, $rawToken);

        if($token === '')
        {
            return array('success' => false, 'count' => 0, 'message' => 'Page Access Token missing.');
        }

        // 1. Fetch Lead Forms on the Page
        $formsUrl = "https://graph.facebook.com/v19.0/" . urlencode($pageId) . "/leadgen_forms?access_token=" . urlencode($token);
        $ch = curl_init($formsUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        $formsResp = curl_exec($ch);
        curl_close($ch);

        $formsData = @json_decode($formsResp, true);
        if(!empty($formsData['error']['message']))
        {
            $errMsg = $formsData['error']['message'];
            if(strpos($errMsg, 'pages_manage_ads') !== false || strpos($errMsg, 'leads_retrieval') !== false || $formsData['error']['code'] == 200)
            {
                $errMsg .= " -> Solution: Add 'pages_manage_ads' and 'leads_retrieval' permissions in Meta Graph API Explorer and regenerate token.";
            }
            return array('success' => false, 'count' => 0, 'message' => "Meta API Error: " . $errMsg);
        }

        if(empty($formsData['data']) || !is_array($formsData['data']))
        {
            // Fallback: check if user configured specific forms in Form Mapping
            $manualForms = $this->FacebookFormMapping->find('all', array(
                'conditions' => array('FacebookFormMapping.is_active' => 1),
                'recursive' => -1
            ));
            if(!empty($manualForms))
            {
                $formsData['data'] = array();
                foreach($manualForms as $mf)
                {
                    $formsData['data'][] = array(
                        'id' => $mf['FacebookFormMapping']['facebook_form_id'],
                        'name' => $mf['FacebookFormMapping']['form_name']
                    );
                }
            }
            else
            {
                return array('success' => false, 'count' => 0, 'message' => 'No active lead forms found on this Facebook Page. If your form is active in Meta Ads Manager, please verify the token has "pages_manage_ads" and "leads_retrieval" permissions.');
            }
        }

        $ingestedCount = 0;
        foreach($formsData['data'] as $form)
        {
            $formId = !empty($form['id']) ? $form['id'] : '';
            if($formId === '') continue;

            $nextUrl = "https://graph.facebook.com/v19.0/" . urlencode($formId) . "/leads?fields=id,created_time,field_data&limit=500&access_token=" . urlencode($token);
            $allFormLeads = array();
            
            while(!empty($nextUrl))
            {
                $ch = curl_init($nextUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                $leadsResp = curl_exec($ch);
                curl_close($ch);

                $leadsData = @json_decode($leadsResp, true);
                if(!empty($leadsData['error']['message']))
                {
                    $leadErr = $leadsData['error']['message'];
                    if(strpos($leadErr, 'leads_retrieval') !== false || $leadsData['error']['code'] == 200)
                    {
                        return array('success' => false, 'count' => 0, 'message' => "Meta Error: (#200) Missing 'leads_retrieval' permission. Please add 'leads_retrieval' in Graph API Explorer and regenerate token.");
                    }
                    break;
                }
                if(!empty($leadsData['data']) && is_array($leadsData['data']))
                {
                    foreach($leadsData['data'] as $leadItem)
                    {
                        $allFormLeads[] = $leadItem;
                    }
                }
                $nextUrl = (!empty($leadsData['paging']['next']) && count($allFormLeads) < 1000) ? $leadsData['paging']['next'] : null;
            }

            // Process in reverse (oldest first so newest ends up at top with highest ID)
            $allFormLeadsChronological = array_reverse($allFormLeads);
            foreach($allFormLeadsChronological as $leadItem)
            {
                $leadgenId = !empty($leadItem['id']) ? $leadItem['id'] : '';
                if($leadgenId === '') continue;

                try
                {
                    $result = $this->_processSingleFacebookLead($leadgenId, $formId, $pageId, json_encode($leadItem));
                    if(!empty($result['success']) && empty($result['duplicate']))
                    {
                        $ingestedCount++;
                    }
                }
                catch(Exception $leadEx)
                {
                    // If a duplicate entry constraint violation occurs (e.g. 1062 on idx_unique_leadgen_id),
                    // safely skip and continue processing remaining leads in the batch!
                    continue;
                }
            }
        }

        $accId = (int)$account['id'];
        $pageId = !empty($account['page_id']) ? trim($account['page_id']) : '';
        $realCount = 0;
        if($pageId !== '')
        {
            $realCount = (int)$this->FacebookLeadLog->find('count', array(
                'conditions' => array('FacebookLeadLog.page_id' => $pageId),
                'recursive' => -1
            ));
        }
        if($realCount <= 0)
        {
            $realCount = (int)$this->Lead->find('count', array(
                'conditions' => array(
                    'OR' => array(
                        array('Lead.lead_source LIKE' => '%Facebook%'),
                        array('Lead.lead_source LIKE' => '%FB%')
                    )
                ),
                'recursive' => -1
            ));
        }
        $finalCount = max($realCount, (int)$account['total_leads_synced'] + $ingestedCount);
        $this->FacebookAccount->updateAll(
            array(
                'FacebookAccount.total_leads_synced' => $finalCount,
                'FacebookAccount.last_sync_at' => "'" . date('Y-m-d H:i:s') . "'"
            ),
            array('FacebookAccount.id' => $accId)
        );

        return array('success' => true, 'count' => $ingestedCount, 'message' => "Scanned " . count($formsData['data']) . " form(s).");
    }
}