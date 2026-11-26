<?php
App::uses('Component', 'Controller');
App::uses('Router', 'Routing');

class WhatsAppAutomationComponent extends Component {
    protected $controller = null;

    public function initialize(Controller $controller) {
        $this->controller = $controller;
    }

    public function sendProjectMessage($projectId, $toMobile, $messageBody, $category = 'NOTIFICATION', $recipientName = '') {
        try {
            $waSettingModel = ClassRegistry::init('Admin.WhatsappSetting');
            $waLogModel = ClassRegistry::init('Admin.WhatsappLog');
            $pSettingModel = ClassRegistry::init('Admin.ProjectNotificationSetting');
            $gwModel = ClassRegistry::init('Admin.WhatsappProjectGateway');
            $projectModel = ClassRegistry::init('Admin.Project');

            $globalWa = $waSettingModel->findById(1);
            if (empty($globalWa['WhatsappSetting']) || empty($globalWa['WhatsappSetting']['is_active'])) {
                return array('success' => false, 'message' => 'WhatsApp Gateway disabled in settings.');
            }

            $phoneId = trim((string)$globalWa['WhatsappSetting']['phone_number_id']);
            $token = trim((string)$globalWa['WhatsappSetting']['access_token']);
            $apiVersion = !empty($globalWa['WhatsappSetting']['api_version']) ? trim($globalWa['WhatsappSetting']['api_version']) : 'v19.0';
            $gatewayNameUsed = 'Master Agency Gateway';

            // Check project-specific gateway
            $pSetting = $pSettingModel->getSettings($projectId);
            if (!empty($pSetting['whatsapp_gateway_id'])) {
                $gw = $gwModel->findById((int)$pSetting['whatsapp_gateway_id']);
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
                return array('success' => false, 'message' => 'WhatsApp credentials not configured.');
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
                $waLogModel->create();
                $waLogModel->save(array('WhatsappLog' => array(
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
                $waSettingModel->updateAll(
                    array('WhatsappSetting.total_sent' => 'WhatsappSetting.total_sent + 1'),
                    array('WhatsappSetting.id' => 1)
                );
                return array('success' => true, 'meta_id' => $metaMsgId);
            } else {
                $errMsg = !empty($json['error']['message']) ? $json['error']['message'] : ('HTTP ' . $httpCode . ': ' . $resp);
                $waLogModel->create();
                $waLogModel->save(array('WhatsappLog' => array(
                    'recipient_mobile' => $cleanMobile,
                    'recipient_name' => $recipientName,
                    'recipient_type' => 'Client',
                    'message_category' => $category,
                    'project_name' => 'Project #' . $projectId,
                    'message_body' => $messageBody,
                    'gateway_name' => $gatewayNameUsed,
                    'status' => 'Failed',
                    'error_message' => $errMsg,
                    'created' => date('Y-m-d H:i:s')
                )));
                return array('success' => false, 'message' => $errMsg);
            }
        } catch(Exception $e) {
            return array('success' => false, 'message' => $e->getMessage());
        }
    }

    public function sendPaymentReceiptAlert($paymentId) {
        $paymentModel = ClassRegistry::init('MyPayment');
        $notifModel = ClassRegistry::init('ClientNotification');
        $pSettingModel = ClassRegistry::init('Admin.ProjectNotificationSetting');

        $payment = $paymentModel->find('first', array(
            'joins' => array(
                array('table' => 'deals', 'alias' => 'Deal', 'type' => 'LEFT', 'conditions' => array('MyPayment.deal_id=Deal.id')),
                array('table' => 'properties', 'alias' => 'Property', 'type' => 'LEFT', 'conditions' => array('Deal.property_id=Property.id')),
                array('table' => 'projects', 'alias' => 'Project', 'type' => 'LEFT', 'conditions' => array('Property.project_id=Project.id')),
                array('table' => 'clients', 'alias' => 'Client', 'type' => 'LEFT', 'conditions' => array('Deal.client_id=Client.id'))
            ),
            'fields' => array('MyPayment.*', 'Deal.*', 'Property.*', 'Project.*', 'Client.*'),
            'conditions' => array('MyPayment.id' => (int)$paymentId),
            'recursive' => -1
        ));

        if (empty($payment['MyPayment']['id']) || empty($payment['Client']['id'])) {
            return false;
        }

        $clientId = (int)$payment['Client']['id'];
        $projectId = !empty($payment['Property']['project_id']) ? (int)$payment['Property']['project_id'] : 0;
        $clientMobile = !empty($payment['Client']['mobile']) ? $payment['Client']['mobile'] : (!empty($payment['Client']['phone']) ? $payment['Client']['phone'] : '');
        $clientName = !empty($payment['Client']['name']) ? $payment['Client']['name'] : 'Valued Client';
        $propName = !empty($payment['Property']['name']) ? $payment['Property']['name'] : 'Plot/Villa';
        $projectName = !empty($payment['Project']['name']) ? $payment['Project']['name'] : 'White Villas';
        $recNo = !empty($payment['MyPayment']['receipt_no']) ? $payment['MyPayment']['receipt_no'] : 'R-' . $paymentId;
        $amount = isset($payment['MyPayment']['total_amount']) ? (float)$payment['MyPayment']['total_amount'] : (isset($payment['MyPayment']['amount']) ? (float)$payment['MyPayment']['amount'] : 0);
        $paidDate = !empty($payment['MyPayment']['payment_date']) ? date('d-M-Y', strtotime($payment['MyPayment']['payment_date'])) : date('d-M-Y');

        // Check project settings
        $pSettings = $pSettingModel->getSettings($projectId);

        // 1. In-Portal Notification Bell Entry
        if (!empty($pSettings['in_portal_notifications'])) {
            $notifKey = 'receipt_' . $paymentId;
            $existingNotif = $notifModel->find('first', array(
                'conditions' => array('ClientNotification.client_id' => $clientId, 'ClientNotification.notif_key' => $notifKey),
                'recursive' => -1
            ));
            if (empty($existingNotif)) {
                $notifModel->create();
                $notifModel->save(array('ClientNotification' => array(
                    'client_id' => $clientId,
                    'notif_key' => $notifKey,
                    'title' => 'Payment Receipt Issued (' . $recNo . ')',
                    'type' => 'payment_received',
                    'message' => 'Payment of Rs ' . number_format($amount) . ' for ' . $propName . ' verified on ' . $paidDate . '.',
                    'is_read' => 0,
                    'action_route' => 'MyPayments/printreceipt/' . $paymentId,
                    'action_url' => Router::url(array('controller' => 'MyPayments', 'action' => 'printreceipt', $paymentId), true),
                    'created' => date('Y-m-d H:i:s')
                )));
            }
        }

        // 2. WhatsApp Instant Alert
        if (!empty($pSettings['whatsapp_receipts']) && !empty($clientMobile)) {
            $receiptUrl = Router::url(array('controller' => 'PaidReceipts', 'action' => 'view', $paymentId), true);
            $tpl = !empty($pSettings['template_receipt']) ? $pSettings['template_receipt'] : "✅ *Payment Received - {project_name}*\nReceipt: {receipt_no}\nAmount: Rs {amount}\nProperty: {property_name}\nDate: {paid_date}\nView: {receipt_link}";
            $body = str_replace(
                array('{client_name}', '{property_name}', '{amount}', '{receipt_no}', '{paid_date}', '{due_amount}', '{receipt_link}', '{project_name}'),
                array($clientName, $propName, number_format($amount), $recNo, $paidDate, '0', $receiptUrl, $projectName),
                $tpl
            );
            $this->sendProjectMessage($projectId, $clientMobile, $body, 'PAYMENT_RECEIPT', $clientName);
        }

        return true;
    }

    public function sendLetterIssuedAlert($letterId) {
        $letterModel = ClassRegistry::init('Admin.Realestateletter');
        $notifModel = ClassRegistry::init('ClientNotification');
        $pSettingModel = ClassRegistry::init('Admin.ProjectNotificationSetting');

        $letter = $letterModel->find('first', array(
            'joins' => array(
                array('table' => 'deals', 'alias' => 'Deal', 'type' => 'LEFT', 'conditions' => array('Realestateletter.deal_id=Deal.id')),
                array('table' => 'properties', 'alias' => 'Property', 'type' => 'LEFT', 'conditions' => array('Deal.property_id=Property.id')),
                array('table' => 'projects', 'alias' => 'Project', 'type' => 'LEFT', 'conditions' => array('Property.project_id=Project.id')),
                array('table' => 'clients', 'alias' => 'Client', 'type' => 'LEFT', 'conditions' => array('Deal.client_id=Client.id'))
            ),
            'fields' => array('Realestateletter.*', 'Deal.*', 'Property.*', 'Project.*', 'Client.*'),
            'conditions' => array('Realestateletter.id' => (int)$letterId),
            'recursive' => -1
        ));

        if (empty($letter['Realestateletter']['id']) || empty($letter['Client']['id'])) {
            return false;
        }

        $clientId = (int)$letter['Client']['id'];
        $projectId = !empty($letter['Property']['project_id']) ? (int)$letter['Property']['project_id'] : 0;
        $clientMobile = !empty($letter['Client']['mobile']) ? $letter['Client']['mobile'] : (!empty($letter['Client']['phone']) ? $letter['Client']['phone'] : '');
        $clientName = !empty($letter['Client']['name']) ? $letter['Client']['name'] : 'Valued Client';
        $propName = !empty($letter['Property']['name']) ? $letter['Property']['name'] : 'Plot/Villa';
        $projectName = !empty($letter['Project']['name']) ? $letter['Project']['name'] : 'White Villas';
        $letterType = !empty($letter['Realestateletter']['letter_type']) ? $letter['Realestateletter']['letter_type'] : 'Official Document';
        $refNo = !empty($letter['Realestateletter']['reference_no']) ? $letter['Realestateletter']['reference_no'] : 'WV-DOC-' . $letterId;
        $issueDate = !empty($letter['Realestateletter']['created']) ? date('d-M-Y', strtotime($letter['Realestateletter']['created'])) : date('d-M-Y');

        $pSettings = $pSettingModel->getSettings($projectId);

        // 1. In-Portal Notification Bell Entry
        if (!empty($pSettings['in_portal_notifications'])) {
            $notifKey = 'letter_' . $letterId;
            $existingNotif = $notifModel->find('first', array(
                'conditions' => array('ClientNotification.client_id' => $clientId, 'ClientNotification.notif_key' => $notifKey),
                'recursive' => -1
            ));
            if (empty($existingNotif)) {
                $notifModel->create();
                $notifModel->save(array('ClientNotification' => array(
                    'client_id' => $clientId,
                    'notif_key' => $notifKey,
                    'title' => 'Official Letter Issued: ' . $letterType,
                    'type' => 'letter_issued',
                    'message' => 'An official ' . $letterType . ' (Ref: ' . $refNo . ') has been issued for your property ' . $propName . '.',
                    'is_read' => 0,
                    'action_route' => 'MyDeals/letterview/' . $letterId,
                    'action_url' => Router::url(array('controller' => 'MyDeals', 'action' => 'letterview', $letterId), true),
                    'created' => date('Y-m-d H:i:s')
                )));
            }
        }

        // 2. WhatsApp Alert
        if (!empty($pSettings['whatsapp_letters']) && !empty($clientMobile)) {
            $letterUrl = Router::url(array('controller' => 'MyDeals', 'action' => 'letterview', $letterId), true);
            $tpl = !empty($pSettings['template_letter']) ? $pSettings['template_letter'] : "📜 *Official Document Issued - {project_name}*\nDear {client_name},\nDocument: {letter_type}\nRef: {ref_no}\nView: {letter_link}";
            $body = str_replace(
                array('{client_name}', '{property_name}', '{letter_type}', '{ref_no}', '{issue_date}', '{letter_link}', '{project_name}'),
                array($clientName, $propName, $letterType, $refNo, $issueDate, $letterUrl, $projectName),
                $tpl
            );
            $this->sendProjectMessage($projectId, $clientMobile, $body, 'LETTER_ISSUED', $clientName);
        }

        return true;
    }

    public function processCronReminders() {
        $planModel = ClassRegistry::init('PlansPayment');
        $pSettingModel = ClassRegistry::init('Admin.ProjectNotificationSetting');
        $notifModel = ClassRegistry::init('ClientNotification');

        $now = date('Y-m-d');
        $in7days = date('Y-m-d', strtotime('+7 days'));
        $in1day = date('Y-m-d', strtotime('+1 day'));

        // Target dates
        $targetDates = array(
            7 => $in7days,
            1 => $in1day
        );

        $results = array('sent_7d' => 0, 'sent_1d' => 0, 'in_portal_created' => 0);

        foreach ($targetDates as $days => $targetDate) {
            $plans = $planModel->find('all', array(
                'joins' => array(
                    array('table' => 'deals', 'alias' => 'Deal', 'type' => 'INNER', 'conditions' => array('PlansPayment.deal_id=Deal.id')),
                    array('table' => 'properties', 'alias' => 'Property', 'type' => 'LEFT', 'conditions' => array('Deal.property_id=Property.id')),
                    array('table' => 'projects', 'alias' => 'Project', 'type' => 'LEFT', 'conditions' => array('Property.project_id=Project.id')),
                    array('table' => 'clients', 'alias' => 'Client', 'type' => 'INNER', 'conditions' => array('Deal.client_id=Client.id'))
                ),
                'fields' => array('PlansPayment.*', 'Deal.*', 'Property.*', 'Project.*', 'Client.*'),
                'conditions' => array(
                    'PlansPayment.date' => $targetDate,
                    'Deal.deal_status' => array('Active', 'Blocked', null, '')
                ),
                'recursive' => -1
            ));

            foreach ($plans as $row) {
                $planId = (int)$row['PlansPayment']['id'];
                $dealId = (int)$row['Deal']['id'];
                $clientId = (int)$row['Client']['id'];
                $projectId = !empty($row['Property']['project_id']) ? (int)$row['Property']['project_id'] : 0;
                $clientMobile = !empty($row['Client']['mobile']) ? $row['Client']['mobile'] : (!empty($row['Client']['phone']) ? $row['Client']['phone'] : '');
                $clientName = !empty($row['Client']['name']) ? $row['Client']['name'] : 'Valued Client';
                $propName = !empty($row['Property']['name']) ? $row['Property']['name'] : 'Plot/Villa';
                $projectName = !empty($row['Project']['name']) ? $row['Project']['name'] : 'White Villas';
                $amount = !empty($row['PlansPayment']['amount']) ? (float)$row['PlansPayment']['amount'] : 0;
                $dueDate = date('d-M-Y', strtotime($targetDate));

                $pSettings = $pSettingModel->getSettings($projectId);

                // In-Portal Notification
                if (!empty($pSettings['in_portal_notifications'])) {
                    $notifKey = 'reminder_' . $days . 'd_' . $planId;
                    $exists = $notifModel->find('first', array(
                        'conditions' => array('ClientNotification.client_id' => $clientId, 'ClientNotification.notif_key' => $notifKey),
                        'recursive' => -1
                    ));
                    if (empty($exists)) {
                        $notifModel->create();
                        $notifModel->save(array('ClientNotification' => array(
                            'client_id' => $clientId,
                            'notif_key' => $notifKey,
                            'title' => 'Installment Due in ' . $days . ' Day' . ($days > 1 ? 's' : '') . ' - ' . $propName,
                            'type' => 'installment_reminder',
                            'message' => 'Your installment of Rs ' . number_format($amount) . ' is due on ' . $dueDate . '. Click to view statement & payment instructions.',
                            'is_read' => 0,
                            'action_route' => 'MyPayments/statement/' . $dealId,
                            'action_url' => Router::url(array('controller' => 'MyPayments', 'action' => 'statement', $dealId), true),
                            'created' => date('Y-m-d H:i:s')
                        )));
                        $results['in_portal_created']++;
                    }
                }

                // WhatsApp Reminder
                if (!empty($pSettings['whatsapp_reminders']) && !empty($clientMobile)) {
                    $statementUrl = Router::url(array('controller' => 'MyPayments', 'action' => 'statement', $dealId), true);
                    $template = ($days === 1) ? $pSettings['template_reminder_1d'] : $pSettings['template_reminder_7d'];
                    $body = str_replace(
                        array('{client_name}', '{property_name}', '{due_amount}', '{due_date}', '{pay_link}', '{project_name}', '{project_phone}'),
                        array($clientName, $propName, number_format($amount), $dueDate, $statementUrl, $projectName, !empty($row['Project']['phone']) ? $row['Project']['phone'] : '+92 339 1101156'),
                        $template
                    );

                    $this->sendProjectMessage($projectId, $clientMobile, $body, 'INSTALLMENT_REMINDER_' . $days . 'D', $clientName);
                    if ($days === 1) {
                        $results['sent_1d']++;
                    } else {
                        $results['sent_7d']++;
                    }
                }
            }
        }

        return $results;
    }

    public function handleBotQuery($senderMobile, $messageText) {
        $clientModel = ClassRegistry::init('Client');
        $dealModel = ClassRegistry::init('MyDeal');
        $paymentModel = ClassRegistry::init('MyPayment');
        $planModel = ClassRegistry::init('PlansPayment');
        $allotmentModel = ClassRegistry::init('Admin.BallotingAllotment');
        $pSettingModel = ClassRegistry::init('Admin.ProjectNotificationSetting');

        $cleanMobile = preg_replace('/[^0-9]/', '', (string)$senderMobile);
        $last9Digits = substr($cleanMobile, -9);

        // Find client by mobile or phone
        $client = $clientModel->find('first', array(
            'conditions' => array(
                'OR' => array(
                    'Client.mobile LIKE' => '%' . $last9Digits,
                    'Client.phone LIKE' => '%' . $last9Digits
                )
            ),
            'recursive' => -1
        ));

        if (empty($client['Client']['id'])) {
            return false; // Not a registered client, let CRM lead processor handle it
        }

        $clientId = (int)$client['Client']['id'];
        $clientName = !empty($client['Client']['name']) ? $client['Client']['name'] : 'Client';

        // Find client's deals to resolve project
        $deals = $dealModel->find('all', array(
            'joins' => array(
                array('table' => 'properties', 'alias' => 'Property', 'type' => 'LEFT', 'conditions' => array('MyDeal.property_id=Property.id')),
                array('table' => 'projects', 'alias' => 'Project', 'type' => 'LEFT', 'conditions' => array('Property.project_id=Project.id'))
            ),
            'fields' => array('MyDeal.*', 'Property.*', 'Project.*'),
            'conditions' => array('MyDeal.client_id' => $clientId),
            'order' => array('MyDeal.id' => 'DESC'),
            'recursive' => -1
        ));

        $projectId = 0;
        $projectName = 'White Villas';
        if (!empty($deals[0]['Property']['project_id'])) {
            $projectId = (int)$deals[0]['Property']['project_id'];
            $projectName = !empty($deals[0]['Project']['name']) ? $deals[0]['Project']['name'] : 'White Villas';
        }

        // Check project bot toggle
        $pSettings = $pSettingModel->getSettings($projectId);
        if (empty($pSettings['whatsapp_bot'])) {
            return false;
        }

        $cmd = strtoupper(trim((string)$messageText));
        $reply = '';

        if (strpos($cmd, 'LEDGER') !== false || strpos($cmd, 'BALANCE') !== false) {
            $totalAmount = 0;
            $dealIds = array();
            foreach ($deals as $d) {
                $totalAmount += !empty($d['MyDeal']['total_amount']) ? (float)$d['MyDeal']['total_amount'] : 0;
                $dealIds[] = (int)$d['MyDeal']['id'];
            }
            $paidTotal = 0;
            if (!empty($dealIds)) {
                $paidRow = $paymentModel->find('first', array(
                    'fields' => array('SUM(MyPayment.total_amount) AS paid_total'),
                    'conditions' => array('MyPayment.deal_id' => $dealIds),
                    'recursive' => -1
                ));
                $paidTotal = !empty($paidRow[0]['paid_total']) ? (float)$paidRow[0]['paid_total'] : 0;
            }
            $dueAmount = max(0, $totalAmount - $paidTotal);
            $portalUrl = Router::url(array('controller' => 'Dashboards', 'action' => 'index'), true);

            $reply = "📊 *Account Ledger Summary - {$projectName}*\n\n" .
                     "Client: *{$clientName}*\n" .
                     "Total Deals Booked: *" . count($deals) . "*\n" .
                     "Total Booked Value: *Rs " . number_format($totalAmount) . "*\n" .
                     "Total Paid to Date: *Rs " . number_format($paidTotal) . "*\n" .
                     "Outstanding Balance: *Rs " . number_format($dueAmount) . "*\n\n" .
                     "📄 *Complete Online Ledger:* {$portalUrl}";

        } elseif (strpos($cmd, 'NEXT DUE') !== false || strpos($cmd, 'DUE') !== false) {
            $dealIds = array();
            foreach ($deals as $d) { $dealIds[] = (int)$d['MyDeal']['id']; }

            $nextPlan = null;
            if (!empty($dealIds)) {
                $nextPlan = $planModel->find('first', array(
                    'conditions' => array(
                        'PlansPayment.deal_id' => $dealIds,
                        'PlansPayment.date >=' => date('Y-m-d')
                    ),
                    'order' => array('PlansPayment.date' => 'ASC'),
                    'recursive' => -1
                ));
            }

            if (!empty($nextPlan['PlansPayment'])) {
                $p = $nextPlan['PlansPayment'];
                $portalUrl = Router::url(array('controller' => 'MyPayments', 'action' => 'statement', $p['deal_id']), true);
                $reply = "📅 *Next Upcoming Installment - {$projectName}*\n\n" .
                         "Installment: *" . (!empty($p['name']) ? $p['name'] : 'Monthly Installment') . "*\n" .
                         "Amount Due: *Rs " . number_format($p['amount']) . "*\n" .
                         "Due Date: *" . date('d-M-Y', strtotime($p['date'])) . "*\n\n" .
                         "💳 *View Statement & Pay:* {$portalUrl}";
            } else {
                $reply = "✅ *All Installments Up to Date!*\n\nDear {$clientName}, you have no upcoming dues scheduled at this moment. Thank you for your on-time payments!";
            }

        } elseif (strpos($cmd, 'STATEMENT') !== false) {
            $dealId = !empty($deals[0]['MyDeal']['id']) ? (int)$deals[0]['MyDeal']['id'] : 0;
            $statementUrl = Router::url(array('controller' => 'MyPayments', 'action' => 'statement', $dealId), true);
            $reply = "📄 *Official Statement of Account*\n\nDear {$clientName},\n\nYou can view and download your full signed Statement of Account here:\n👉 {$statementUrl}\n\nFor queries, contact {$projectName} Customer Care.";

        } elseif (strpos($cmd, 'BALLOT') !== false || strpos($cmd, 'ALLOT') !== false) {
            $allotment = $allotmentModel->find('first', array(
                'conditions' => array('BallotingAllotment.client_id' => $clientId, 'BallotingAllotment.status' => 'Allotted'),
                'order' => array('BallotingAllotment.id' => 'DESC'),
                'recursive' => -1
            ));

            if (!empty($allotment['BallotingAllotment'])) {
                $a = $allotment['BallotingAllotment'];
                $certUrl = Router::url(array('controller' => 'BallotingResults', 'action' => 'certificate', $a['id']), true);
                $reply = "🎉 *Congratulations! e-Balloting Allotment Details*\n\n" .
                         "Project: *{$projectName}*\n" .
                         "Allotment No: *" . (!empty($a['allotment_no']) ? $a['allotment_no'] : 'WV-' . $a['id']) . "*\n" .
                         "Plot / Unit No: *" . (!empty($a['property_no']) ? $a['property_no'] : 'Assigned') . "*\n" .
                         "Block / Sector: *" . (!empty($a['block_name']) ? $a['block_name'] : 'Main Block') . "*\n" .
                         "Street No: *" . (!empty($a['street_no']) ? $a['street_no'] : 'Main') . "*\n" .
                         "Size: *" . $a['unit_area'] . " " . (!empty($a['area_unit']) ? $a['area_unit'] : 'Marla') . "*\n" .
                         "Allotment Date: *" . date('d-M-Y', strtotime($a['allotment_date'])) . "*\n\n" .
                         "📜 *Download Official Allocation Certificate:* {$certUrl}";
            } else {
                $ballotSearchUrl = Router::url(array('controller' => 'BallotingResults', 'action' => 'index'), true);
                $reply = "ℹ️ *e-Balloting Status*\n\nDear {$clientName}, your plot is currently queued for the upcoming balloting phase. Please ensure your installments are up to date.\n\n🔍 Check live status: {$ballotSearchUrl}";
            }

        } elseif (in_array($cmd, array('HELP', 'MENU', 'HI', 'HELLO', 'ASSALAM', 'ASSALAMUALAIKUM', 'INFO'))) {
            $tpl = !empty($pSettings['template_bot_welcome']) ? $pSettings['template_bot_welcome'] : "👋 *Welcome to {project_name} Assistant!*\nReply with:\n👉 *LEDGER*\n👉 *NEXT DUE*\n👉 *STATEMENT*\n👉 *BALLOT*\n👉 *HELP*";
            $reply = str_replace(
                array('{client_name}', '{project_name}'),
                array($clientName, $projectName),
                $tpl
            );
        }

        if ($reply !== '') {
            $this->sendProjectMessage($projectId, $cleanMobile, $reply, 'BOT_AUTO_REPLY', $clientName);
            return true;
        }

        return false;
    }
}
