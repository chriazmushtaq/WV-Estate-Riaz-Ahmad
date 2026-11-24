<?php
App::uses('AdminAppController', 'Admin.Controller');
App::uses('LeadbotGatewayClient', 'Lib');

class SmssettingsController extends AdminAppController {
    public $helpers = array('Html', 'Form','Session');
    public $components = array('Session','search-master.Prg');

    public function index()
    {
        $id = 1;        
        $post = $this->Smssetting->findById($id); 
        if ($this->request->is('post'))
        {
            $this->Smssetting->id = $id;
            try
            {
                if ($this->Smssetting->save($this->request->data))
                {
                    // Sync LeadbotGatewaySetting is_default_sms
                    $isLeadbot = !empty($this->request->data['Smssetting']['gateway_type']) && $this->request->data['Smssetting']['gateway_type'] === 'leadbot';
                    try {
                        $LbModel = ClassRegistry::init('Admin.LeadbotGatewaySetting');
                        if ($LbModel) {
                            $LbModel->updateAll(
                                array(
                                    'LeadbotGatewaySetting.is_default_sms' => $isLeadbot ? 1 : 0,
                                    'LeadbotGatewaySetting.is_default_whatsapp' => 1
                                ),
                                array('LeadbotGatewaySetting.project_id' => 0, 'LeadbotGatewaySetting.user_id' => 0)
                            );
                        }
                    } catch (Exception $eSync) {}

                    $this->Session->setFlash(__('Your SMS Settings have been saved successfully.'),'flash',array('alert'=>'success'));
                    return $this->redirect(array('action' => 'index'));
                }
            }
            catch (Exception $e)
            {
                $this->Session->setFlash(__('Setting Problem: ' . $e->getMessage()),'flash',array('alert'=>'danger'));
                return $this->redirect(array('action' => 'index'));
            }
        }
        if (!$this->request->data)
        {
            $this->request->data = $post;
        }

        // Load LeadBot settings & telemetry for interactive switcher
        $leadbotSettings   = array();
        $leadbotTelemetry  = array();
        $isLeadbotConnected = false;
        try {
            $LbModel = ClassRegistry::init('Admin.LeadbotGatewaySetting');
            if ($LbModel) {
                $leadbotSettings = $LbModel->getSettings(0, 0, true);
                $gwUrl  = !empty($leadbotSettings['gateway_url']) ? $leadbotSettings['gateway_url'] : LeadbotGatewayClient::DEFAULT_LOCAL_URL;
                $apiKey = !empty($leadbotSettings['api_key']) ? $leadbotSettings['api_key'] : '';
                if ($apiKey !== '') {
                    $ping = LeadbotGatewayClient::ping($gwUrl, $apiKey);
                    $isLeadbotConnected = !empty($ping['connected']);
                    $leadbotTelemetry   = LeadbotGatewayClient::getGateways($gwUrl, $apiKey);
                }
            }
        } catch (Exception $eLb) {}

        $isLeadbotDefault = (!empty($post['Smssetting']['gateway_type']) && strtolower((string)$post['Smssetting']['gateway_type']) === 'leadbot') || !empty($post['Smssetting']['is_leadbot_default']);

        $this->set(compact('leadbotSettings', 'leadbotTelemetry', 'isLeadbotConnected', 'isLeadbotDefault'));
    }

    /**
     * AJAX 1-Click Toggle for Default Gateway (LeadBot CRM vs Custom HTTP API)
     */
    public function ajax_toggle_default()
    {
        $this->autoRender = false;
        $this->layout = false;
        $this->response->type('json');

        if (!$this->request->is('post')) {
            return $this->response->body(json_encode(array('success' => false, 'message' => __('Invalid request method.'))));
        }

        $target = isset($this->request->data['gateway_type']) ? trim((string)$this->request->data['gateway_type']) : '';
        $id = 1;
        $current = $this->Smssetting->findById($id);

        if ($target === '') {
            $currentType = !empty($current['Smssetting']['gateway_type']) ? $current['Smssetting']['gateway_type'] : 'custom';
            $target = ($currentType === 'leadbot') ? 'custom' : 'leadbot';
        }

        $isLeadbot = ($target === 'leadbot');

        try {
            $this->Smssetting->id = $id;
            $this->Smssetting->saveField('gateway_type', $target);
            $this->Smssetting->saveField('is_leadbot_default', $isLeadbot ? 1 : 0);

            // Synchronize with LeadbotGatewaySetting
            $LbModel = ClassRegistry::init('Admin.LeadbotGatewaySetting');
            if ($LbModel) {
                $LbModel->updateAll(
                    array(
                        'LeadbotGatewaySetting.is_default_sms' => $isLeadbot ? 1 : 0,
                        'LeadbotGatewaySetting.is_default_whatsapp' => 1
                    ),
                    array('LeadbotGatewaySetting.project_id' => 0, 'LeadbotGatewaySetting.user_id' => 0)
                );
            }

            // Invalidate cache
            $this->smsSettings();

            return $this->response->body(json_encode(array(
                'success'            => true,
                'gateway_type'       => $target,
                'is_leadbot_default' => $isLeadbot,
                'message'            => $isLeadbot 
                    ? __('LeadBot CRM is now active as the DEFAULT gateway for all SMS & WhatsApp messages!') 
                    : __('Custom HTTP SMS Gateway is now set as the active provider.')
            )));
        } catch (Exception $e) {
            return $this->response->body(json_encode(array(
                'success' => false,
                'message' => __('Database update error: ') . $e->getMessage()
            )));
        }
    }

    public function test_sms()
    {
        $this->autoRender = false;
        $response = array('status' => 'error', 'message' => __('Invalid request'));

        if ($this->request->is('post'))
        {
            $mobile = isset($this->request->data['test_mobile']) ? trim($this->request->data['test_mobile']) : '';
            $msg = isset($this->request->data['test_message']) ? trim($this->request->data['test_message']) : 'White Villas SMS Gateway Test';

            if (empty($mobile))
            {
                $response = array('status' => 'error', 'message' => __('Please provide a valid recipient phone number.'));
            }
            else
            {
                try
                {
                    $sent = $this->sendSystemSms($mobile, $msg);
                    if ($sent)
                    {
                        // Check if it was sent via LeadBot
                        $curr = $this->Smssetting->findById(1);
                        $isLb = !empty($curr['Smssetting']['gateway_type']) && $curr['Smssetting']['gateway_type'] === 'leadbot';
                        $engineLabel = $isLb ? __('LeadBot CRM Android Gateway') : __('Custom SMS Gateway');
                        $response = array(
                            'status' => 'success', 
                            'message' => sprintf(__('Test SMS was successfully queued via %s to %s!'), $engineLabel, h($mobile))
                        );
                    }
                    else
                    {
                        $response = array('status' => 'error', 'message' => __('SMS could not be delivered. Please check gateway URL, API token, and phone number.'));
                    }
                }
                catch (Throwable $e)
                {
                    $response = array('status' => 'error', 'message' => __('Gateway Error: ') . $e->getMessage());
                }
            }
        }

        if ($this->request->is('ajax'))
        {
            $this->response->type('json');
            $this->response->body(json_encode($response));
            return $this->response;
        }

        $this->Session->setFlash($response['message'], 'flash', array('alert' => $response['status'] === 'success' ? 'success' : 'danger'));
        return $this->redirect(array('action' => 'index'));
    }
}
?>
