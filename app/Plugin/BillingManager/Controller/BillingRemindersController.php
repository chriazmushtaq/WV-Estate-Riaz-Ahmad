<?php
App::uses('BillingManagerAppController', 'BillingManager.Controller');
App::uses('LeadbotGatewayClient', 'Lib');

class BillingRemindersController extends BillingManagerAppController
{
    public $uses = array(
        'BillingManager.BillingReminderLog',
        'BillingManager.BillingBill',
        'BillingManager.BillingClient',
        'BillingManager.BillingSetting'
    );

    public function index()
    {
        $societyId = $this->_getActiveSocietyId();
        $logs = $this->BillingReminderLog->find('all', array(
            'conditions' => array('BillingReminderLog.society_id' => $societyId),
            'order' => array('BillingReminderLog.id' => 'DESC'),
            'limit' => 50
        ));
        $this->set('logs', $logs);
    }

    public function send_reminder()
    {
        if($this->request->is('post'))
        {
            $type = isset($this->request->data['Reminder']['type']) ? $this->request->data['Reminder']['type'] : 'due_date';
            $channel = isset($this->request->data['Reminder']['channel']) ? strtolower($this->request->data['Reminder']['channel']) : 'sms';
            $societyId = $this->_getActiveSocietyId();

            // Load Society SMS / Gateway Settings
            $useSystemGateway = (int)$this->BillingSetting->getSetting('subm_use_system_sms_gateway', 1, $societyId);
            $customUrl = $this->BillingSetting->getSetting('subm_sms_gateway_url', 'https://sms.alriazdevelopers.com/services/send.php', $societyId);
            $customToken = $this->BillingSetting->getSetting('subm_sms_gateway_token', '3bfccea8319461dd7f1d97d60f1fead9b3ade365', $societyId);
            $customSender = $this->BillingSetting->getSetting('subm_sms_gateway_sender_id', 'WhiteVillas', $societyId);
            $customMethod = $this->BillingSetting->getSetting('subm_sms_gateway_method', 'GET', $societyId);
            $defaultCc = $this->BillingSetting->getSetting('subm_reminder_default_cc', '+92', $societyId);

            // Load System SMS Settings if in System Mode
            $systemSms = null;
            if($useSystemGateway)
            {
                try {
                    $db = ConnectionManager::getDataSource('default');
                    $sysRows = $db->fetchAll("SELECT * FROM smssettings LIMIT 1");
                    if(!empty($sysRows[0]))
                    {
                        $systemSms = isset($sysRows[0]['smssettings']) ? $sysRows[0]['smssettings'] : $sysRows[0];
                    }
                } catch(Exception $e) {}
            }

            // Find all unpaid or partial bills
            $unpaid = $this->BillingBill->find('all', array(
                'conditions' => array('BillingBill.society_id' => $societyId, 'BillingBill.payment_status' => array('unpaid', 'partial')),
                'contain' => array('BillingClient')
            ));

            $dispatchedCount = 0;
            foreach($unpaid as $ub)
            {
                $b = $ub['BillingBill'];
                $c = $ub['BillingClient'];
                if(empty($c['phone'])) continue;

                $phone = trim($c['phone']);
                if(substr($phone, 0, 1) === '0') {
                    $phone = $defaultCc . substr($phone, 1);
                } elseif(substr($phone, 0, 1) !== '+') {
                    $phone = $defaultCc . $phone;
                }

                $msg = "Dear {$c['name']}, your White Villas bill #{$b['bill_number']} for amount PKR " . number_format((float)$b['total_amount'], 2) . " is due on {$b['due_date']}. Please clear to avoid late fee.";

                // Dispatch SMS
                $smsStatus = 'sent';
                $apiResponse = '';
                if($channel === 'sms')
                {
                    if($useSystemGateway && !empty($systemSms))
                    {
                        // Use Central System SMS Gateway
                        $apiUrl = !empty($systemSms['api']) ? $systemSms['api'] : 'http://sms.alriazdevelopers.com/services/send.php';
                        $params = array(
                            !empty($systemSms['husername']) ? $systemSms['husername'] : 'option' => !empty($systemSms['username']) ? $systemSms['username'] : '',
                            !empty($systemSms['hpassword']) ? $systemSms['hpassword'] : 'key' => !empty($systemSms['password']) ? $systemSms['password'] : '',
                            !empty($systemSms['hsenderid']) ? $systemSms['hsenderid'] : 'type' => !empty($systemSms['senderid']) ? $systemSms['senderid'] : 'sms',
                            !empty($systemSms['hmobile']) ? $systemSms['hmobile'] : 'number' => $phone,
                            !empty($systemSms['hmessage']) ? $systemSms['hmessage'] : 'message' => $msg
                        );

                        if(strtolower($systemSms['type']) === 'post') {
                            $ch = curl_init($apiUrl);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($ch, CURLOPT_POST, true);
                            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
                            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                            $apiResponse = curl_exec($ch);
                            curl_close($ch);
                        } else {
                            $fullUrl = $apiUrl . '?' . http_build_query($params);
                            @$apiResponse = file_get_contents($fullUrl);
                        }
                    }
                    else
                    {
                        // Use Custom Isolated Gateway
                        $params = array(
                            'token' => $customToken,
                            'to' => $phone,
                            'message' => $msg,
                            'sender' => $customSender
                        );

                        if(strtoupper($customMethod) === 'POST') {
                            $ch = curl_init($customUrl);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($ch, CURLOPT_POST, true);
                            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
                            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                            $apiResponse = curl_exec($ch);
                            curl_close($ch);
                        } else {
                            $fullUrl = $customUrl . '?' . http_build_query($params);
                            @$apiResponse = file_get_contents($fullUrl);
                        }
                    }
                }
                elseif($channel === 'whatsapp')
                {
                    $GatewayModel = ClassRegistry::init('Admin.LeadbotGatewaySetting');
                    $settings = $GatewayModel ? $GatewayModel->getSettings(0, 0, true) : array();
                    $gatewayUrl = !empty($settings['gateway_url']) ? $settings['gateway_url'] : LeadbotGatewayClient::DEFAULT_LOCAL_URL;
                    $apiKey     = !empty($settings['api_key']) ? $settings['api_key'] : '';
                    $waSession  = !empty($settings['selected_wa_session']) ? $settings['selected_wa_session'] : '';
                    $agencyId   = !empty($settings['agency_id']) ? $settings['agency_id'] : 51;

                    if(!empty($apiKey)) {
                        $res = LeadbotGatewayClient::sendWhatsApp($gatewayUrl, $apiKey, array(
                            'recipient' => $phone,
                            'message'   => $msg,
                            'session'   => $waSession,
                            'agency_id' => $agencyId
                        ));
                        $isSuccess = !empty($res['success']);
                        $smsStatus = $isSuccess ? 'sent' : 'failed';
                        $apiResponse = $isSuccess ? (isset($res['message_id']) ? ('Message ID: ' . $res['message_id']) : 'Sent') : (!empty($res['error']) ? $res['error'] : 'Failed');
                    } else {
                        $smsStatus = 'failed';
                        $apiResponse = 'LeadBot API Key not configured';
                    }
                }

                $this->BillingReminderLog->create();
                $this->BillingReminderLog->save(array('BillingReminderLog' => array(
                    'society_id' => $societyId,
                    'bill_id' => $b['id'],
                    'billing_client_id' => $b['billing_client_id'],
                    'channel' => $channel,
                    'recipient' => $phone,
                    'message' => $msg,
                    'status' => $smsStatus,
                    'response' => is_string($apiResponse) ? substr($apiResponse, 0, 255) : '',
                    'sent_at' => date('Y-m-d H:i:s')
                )));
                $dispatchedCount++;
            }

            $modeText = ($channel === 'whatsapp') ? 'LeadBot WhatsApp Engine' : ($useSystemGateway ? 'Central System Gateway' : 'Custom Isolated Gateway');
            $this->Session->setFlash(sprintf(__('Dispatched %d notifications via %s using %s.'), $dispatchedCount, strtoupper($channel), $modeText), 'flash', array('alert' => 'success'));
            return $this->redirect(array('action' => 'index'));
        }
    }

    public function run_drip_reminders()
    {
        $societyId = $this->_getActiveSocietyId();
        $enabled = (int)$this->BillingSetting->getSetting('subm_upgrade_drip_reminders_enabled', 1, $societyId);
        if(!$enabled)
        {
            $this->Session->setFlash(__('Multi-Stage Drip WhatsApp Reminders are switched OFF in Upgrades & Feature Settings.'), 'flash', array('alert' => 'warning'));
            return $this->redirect(array('action' => 'index'));
        }

        $preDueDays = (int)$this->BillingSetting->getSetting('subm_upgrade_drip_pre_due_days', 3, $societyId);
        $overdue1Days = (int)$this->BillingSetting->getSetting('subm_upgrade_drip_overdue_1_days', 2, $societyId);
        $overdue2Days = (int)$this->BillingSetting->getSetting('subm_upgrade_drip_overdue_2_days', 7, $societyId);

        $today = date('Y-m-d');
        $preDueDate = date('Y-m-d', strtotime("+{$preDueDays} days"));
        $overdue1Date = date('Y-m-d', strtotime("-{$overdue1Days} days"));
        $overdue2Date = date('Y-m-d', strtotime("-{$overdue2Days} days"));

        $targets = array(
            'pre_due' => array('date' => $preDueDate, 'title' => "Pre-Due Courtesy Notice ({$preDueDays} days before due date)"),
            'due_today' => array('date' => $today, 'title' => "Due Date Alert (Today is the due date)"),
            'overdue_1' => array('date' => $overdue1Date, 'title' => "Overdue Follow-up ({$overdue1Days} days overdue)"),
            'overdue_2' => array('date' => $overdue2Date, 'title' => "Final Overdue Warning ({$overdue2Days} days overdue)")
        );

        $totalSent = 0;
        foreach($targets as $stageKey => $stageInfo)
        {
            $bills = $this->BillingBill->find('all', array(
                'conditions' => array(
                    'BillingBill.society_id' => $societyId,
                    'BillingBill.payment_status' => array('unpaid', 'partial'),
                    'BillingBill.due_date' => $stageInfo['date']
                ),
                'contain' => array('BillingClient')
            ));

            foreach($bills as $b)
            {
                $c = $b['BillingClient'];
                if(empty($c['phone'])) continue;
                $phone = preg_replace('/[^0-9]/', '', (string)$c['phone']);
                if(substr($phone, 0, 1) === '0') $phone = '92' . substr($phone, 1);

                $billNo = $b['BillingBill']['bill_number'];
                $amount = number_format((float)$b['BillingBill']['total_amount'], 2);
                $dueDate = date('d-M-Y', strtotime($b['BillingBill']['due_date']));

                $msg = "🏛️ *WHITE VILLAS LAHORE*\n"
                     . "🔔 *{$stageInfo['title']}*\n"
                     . "━━━━━━━━━━━━━━━━━━━━━\n"
                     . "👤 *Resident:* " . strtoupper($c['name']) . "\n"
                     . "📋 *Bill #:* #" . $billNo . "\n"
                     . "💵 *Payable Amount:* *PKR {$amount}*\n"
                     . "🔴 *Due Date:* *{$dueDate}*\n"
                     . "━━━━━━━━━━━━━━━━━━━━━\n"
                     . "🏦 *Payment Methods:* Meezan / JazzCash (0339-1101156)\n"
                     . "📞 *Office:* +92 339 1101156";

                $GatewayModel = ClassRegistry::init('Admin.LeadbotGatewaySetting');
                $settings = $GatewayModel ? $GatewayModel->getSettings(0, 0, true) : array();
                $gatewayUrl = !empty($settings['gateway_url']) ? $settings['gateway_url'] : LeadbotGatewayClient::DEFAULT_LOCAL_URL;
                $apiKey     = !empty($settings['api_key']) ? $settings['api_key'] : '';
                $waSession  = !empty($settings['selected_wa_session']) ? $settings['selected_wa_session'] : '';
                $agencyId   = !empty($settings['agency_id']) ? $settings['agency_id'] : 51;

                if(!empty($apiKey)) {
                    $res = LeadbotGatewayClient::sendWhatsApp($gatewayUrl, $apiKey, array(
                        'recipient' => $phone,
                        'message'   => $msg,
                        'session'   => $waSession,
                        'agency_id' => $agencyId
                    ));
                    $isSuccess = !empty($res['success']);
                    $this->BillingReminderLog->create();
                    $this->BillingReminderLog->save(array('BillingReminderLog' => array(
                        'society_id' => $societyId,
                        'bill_id' => $b['BillingBill']['id'],
                        'billing_client_id' => $b['BillingBill']['billing_client_id'],
                        'channel' => 'whatsapp',
                        'recipient' => $phone,
                        'message' => $msg,
                        'status' => $isSuccess ? 'sent' : 'failed',
                        'response' => $isSuccess ? 'Drip Reminder Sent' : (!empty($res['error']) ? $res['error'] : 'Failed'),
                        'sent_at' => date('Y-m-d H:i:s')
                    )));
                    if($isSuccess) $totalSent++;
                }
            }
        }

        $this->Session->setFlash(sprintf(__('Automated Multi-Stage Drip sequence evaluated. Dispatched %d WhatsApp reminders based on configured days.'), $totalSent), 'flash', array('alert' => 'success'));
        return $this->redirect(array('action' => 'index'));
    }

    public function send_executive_digest()
    {
        $societyId = $this->_getActiveSocietyId();
        $enabled = (int)$this->BillingSetting->getSetting('subm_upgrade_daily_digest_enabled', 1, $societyId);
        if(!$enabled)
        {
            $this->Session->setFlash(__('Daily Executive WhatsApp Digest is switched OFF in Upgrades & Feature Settings.'), 'flash', array('alert' => 'warning'));
            return $this->redirect(array('action' => 'index'));
        }

        $adminPhones = $this->BillingSetting->getSetting('subm_upgrade_daily_digest_phones', '03391101156', $societyId);
        $phoneList = array_filter(array_map('trim', explode(',', (string)$adminPhones)));

        if(empty($phoneList))
        {
            $this->Session->setFlash(__('No administrator mobile numbers configured for daily digest.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }

        $today = date('Y-m-d');
        // Calculate collections today
        $collectedRows = $this->BillingBill->find('all', array(
            'conditions' => array(
                'BillingBill.society_id' => $societyId,
                'BillingBill.payment_date' => $today
            ),
            'fields' => array('SUM(BillingBill.paid_amount) AS total_today', 'COUNT(BillingBill.id) AS count_today')
        ));
        $todayCollection = !empty($collectedRows[0][0]['total_today']) ? (float)$collectedRows[0][0]['total_today'] : 0.0;
        $todayCount = !empty($collectedRows[0][0]['count_today']) ? (int)$collectedRows[0][0]['count_today'] : 0;

        // Calculate total outstanding unpaid
        $unpaidRows = $this->BillingBill->find('all', array(
            'conditions' => array(
                'BillingBill.society_id' => $societyId,
                'BillingBill.payment_status' => array('unpaid', 'partial')
            ),
            'fields' => array('SUM(BillingBill.balance_amount) AS total_pending', 'COUNT(BillingBill.id) AS count_pending')
        ));
        $totalPending = !empty($unpaidRows[0][0]['total_pending']) ? (float)$unpaidRows[0][0]['total_pending'] : 0.0;
        $countPending = !empty($unpaidRows[0][0]['count_pending']) ? (int)$unpaidRows[0][0]['count_pending'] : 0;

        // Top 5 Defaulters
        $includeDefaulters = (int)$this->BillingSetting->getSetting('subm_upgrade_daily_digest_include_defaulters', 1, $societyId);
        $defaultersList = "";
        if($includeDefaulters)
        {
            $defaulters = $this->BillingBill->find('all', array(
                'conditions' => array(
                    'BillingBill.society_id' => $societyId,
                    'BillingBill.payment_status' => array('unpaid', 'partial'),
                    'BillingBill.due_date <' => $today
                ),
                'contain' => array('BillingClient'),
                'order' => array('BillingBill.balance_amount' => 'DESC'),
                'limit' => 5
            ));
            if(!empty($defaulters))
            {
                $defaultersList = "\n⚠️ *TOP OUTSTANDING DEFAULTERS:*\n";
                $i = 1;
                foreach($defaulters as $df)
                {
                    $cName = !empty($df['BillingClient']['name']) ? $df['BillingClient']['name'] : 'Resident';
                    $bal = number_format((float)$df['BillingBill']['balance_amount'], 2);
                    $defaultersList .= "{$i}. {$cName}: *PKR {$bal}*\n";
                    $i++;
                }
            }
        }

        $digestMsg = "🏛️ *WHITE VILLAS LAHORE — EXECUTIVE DAILY DIGEST*\n"
                   . "📅 *Date:* " . date('d-M-Y') . " (Evening Report)\n"
                   . "━━━━━━━━━━━━━━━━━━━━━\n"
                   . "💵 *Collected Today:* *PKR " . number_format($todayCollection, 2) . "* (" . $todayCount . " bills cleared)\n"
                   . "⏳ *Total Outstanding Arrears:* *PKR " . number_format($totalPending, 2) . "* (" . $countPending . " unpaid bills)\n"
                   . "━━━━━━━━━━━━━━━━━━━━━"
                   . $defaultersList
                   . "\n📊 *Status:* System operational & gateways active.\n"
                   . "🏢 *White Villas Automated Billing Core*";

        $GatewayModel = ClassRegistry::init('Admin.LeadbotGatewaySetting');
        $settings = $GatewayModel ? $GatewayModel->getSettings(0, 0, true) : array();
        $gatewayUrl = !empty($settings['gateway_url']) ? $settings['gateway_url'] : LeadbotGatewayClient::DEFAULT_LOCAL_URL;
        $apiKey     = !empty($settings['api_key']) ? $settings['api_key'] : '';
        $waSession  = !empty($settings['selected_wa_session']) ? $settings['selected_wa_session'] : '';
        $agencyId   = !empty($settings['agency_id']) ? $settings['agency_id'] : 51;

        $sentCount = 0;
        foreach($phoneList as $phone)
        {
            $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
            if(substr($cleanPhone, 0, 1) === '0') $cleanPhone = '92' . substr($cleanPhone, 1);

            if(!empty($apiKey)) {
                $res = LeadbotGatewayClient::sendWhatsApp($gatewayUrl, $apiKey, array(
                    'recipient' => $cleanPhone,
                    'message'   => $digestMsg,
                    'session'   => $waSession,
                    'agency_id' => $agencyId
                ));
                if(!empty($res['success'])) $sentCount++;
            }
        }

        $this->Session->setFlash(sprintf(__('Executive Daily Digest dispatched to %d management phone numbers via WhatsApp!'), $sentCount), 'flash', array('alert' => 'success'));
        return $this->redirect(array('action' => 'index'));
    }
}
