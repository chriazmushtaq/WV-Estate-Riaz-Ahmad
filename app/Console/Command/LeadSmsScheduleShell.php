<?php
App::uses('Shell', 'Console');
App::uses('ClassRegistry', 'Utility');
App::uses('HttpSocket', 'Network/Http');
App::uses('CakeLog', 'Log');

class LeadSmsScheduleShell extends Shell
{
    protected $_LeadSmsSchedule = null;
    protected $_Lead = null;
    protected $_CrmIntegration = null;
    protected $_Smssetting = null;
    protected $_LeadHistory = null;
    protected $_HttpSocket = null;

    public function getOptionParser()
    {
        $parser = parent::getOptionParser();
        $parser->description('Process due scheduled lead SMS queue.');
        $parser->addOption('limit', array(
            'short' => 'l',
            'help' => 'Max queue rows to process per run',
            'default' => 200
        ));
        return $parser;
    }

    public function main()
    {
        return $this->run();
    }

    public function run()
    {
        $limit = !empty($this->params['limit']) ? (int)$this->params['limit'] : 200;
        if($limit <= 0)
        {
            $limit = 200;
        }

        if(!$this->_bootstrapModels())
        {
            $this->err('Unable to load required models.');
            return 1;
        }

        $this->_LeadSmsSchedule->ensureSchema();
        $this->_LeadSmsSchedule->resetStuckProcessing(30);
        $dueRows = (array)$this->_LeadSmsSchedule->dueRows($limit);
        if(empty($dueRows))
        {
            $this->out('No scheduled lead SMS due at '.date('Y-m-d H:i:s'));
            return 0;
        }

        $leadIds = array();
        foreach($dueRows as $dueRow)
        {
            if(!empty($dueRow['LeadSmsSchedule']['lead_id']))
            {
                $leadId = (int)$dueRow['LeadSmsSchedule']['lead_id'];
                $leadIds[$leadId] = $leadId;
            }
        }
        $leadMap = $this->_loadLeadMap(array_values($leadIds));
        $globalSmsSettings = (array)$this->_Smssetting->findById(1);

        $sent = 0;
        $failed = 0;
        $skipped = 0;

        foreach($dueRows as $dueRow)
        {
            $schedule = isset($dueRow['LeadSmsSchedule']) ? (array)$dueRow['LeadSmsSchedule'] : array();
            $scheduleId = !empty($schedule['id']) ? (int)$schedule['id'] : 0;
            if($scheduleId <= 0)
            {
                continue;
            }

            if(!$this->_LeadSmsSchedule->markProcessing($scheduleId))
            {
                continue;
            }

            $leadId = !empty($schedule['lead_id']) ? (int)$schedule['lead_id'] : 0;
            $leadRow = ($leadId > 0 && isset($leadMap[$leadId])) ? (array)$leadMap[$leadId] : array();
            if(empty($leadRow))
            {
                $this->_LeadSmsSchedule->markFailed($scheduleId, 'Lead record not found.');
                $failed++;
                continue;
            }

            $toMobile = trim((string)(!empty($schedule['mobile']) ? $schedule['mobile'] : $this->_leadMobileForMessage($leadRow)));
            if($toMobile === '')
            {
                $this->_LeadSmsSchedule->markFailed($scheduleId, 'No mobile number found.');
                $this->_logLeadHistory($leadRow, 'failed', '', '', 'Scheduled SMS', 'No mobile number found.');
                $skipped++;
                continue;
            }

            $scheduledByUserId = !empty($schedule['scheduled_by_user_id']) ? (int)$schedule['scheduled_by_user_id'] : 0;
            $smsBundle = (array)$this->_CrmIntegration->effectiveSmsGatewayForUser($scheduledByUserId, $globalSmsSettings);
            $smsSettings = isset($smsBundle['settings']) ? (array)$smsBundle['settings'] : $globalSmsSettings;
            $gatewayLabel = !empty($smsBundle['use_global']) ? 'Global SMS Settings' : 'My CRM SMS Settings';
            if(!$this->_smsGatewayLooksConfigured($smsSettings))
            {
                $this->_LeadSmsSchedule->markFailed($scheduleId, 'SMS gateway is not configured.');
                $this->_logLeadHistory($leadRow, 'failed', $toMobile, '', $gatewayLabel, 'SMS gateway is not configured.');
                $failed++;
                continue;
            }

            $messageTemplate = trim((string)(isset($schedule['message_template']) ? $schedule['message_template'] : ''));
            if($messageTemplate === '')
            {
                $messageTemplate = trim((string)(isset($schedule['message_rendered']) ? $schedule['message_rendered'] : ''));
            }
            if($messageTemplate === '')
            {
                $this->_LeadSmsSchedule->markFailed($scheduleId, 'Message template is empty.');
                $this->_logLeadHistory($leadRow, 'failed', $toMobile, '', $gatewayLabel, 'Message template is empty.');
                $failed++;
                continue;
            }

            $messageBody = $this->_leadTemplateRender($messageTemplate, $leadRow);
            try
            {
                $this->_sendSms($toMobile, $messageBody, $smsSettings);
                $this->_LeadSmsSchedule->markSent($scheduleId, $messageBody);
                $this->_logLeadHistory($leadRow, 'sent', $toMobile, $messageBody, $gatewayLabel, '');
                $sent++;
            }
            catch(Exception $e)
            {
                $errorText = trim((string)$e->getMessage());
                $this->_LeadSmsSchedule->markFailed($scheduleId, $errorText);
                $this->_logLeadHistory($leadRow, 'failed', $toMobile, $messageBody, $gatewayLabel, $errorText);
                CakeLog::write('error', 'Lead SMS scheduler failed for queue #'.$scheduleId.': '.$errorText);
                $failed++;
            }
        }

        $this->out('Lead SMS scheduler complete. Sent: '.$sent.' | Failed: '.$failed.' | Skipped: '.$skipped);
        return 0;
    }

    protected function _bootstrapModels()
    {
        $this->_LeadSmsSchedule = ClassRegistry::init('LeadSmsSchedule');
        $this->_Lead = ClassRegistry::init('Admin.Lead');
        $this->_CrmIntegration = ClassRegistry::init('CrmIntegration');
        $this->_Smssetting = ClassRegistry::init('Admin.Smssetting');
        $this->_LeadHistory = ClassRegistry::init('LeadHistory');
        if(!$this->_LeadSmsSchedule || !$this->_Lead || !$this->_CrmIntegration || !$this->_Smssetting || !$this->_LeadHistory)
        {
            return false;
        }
        $this->_CrmIntegration->ensureSchema();
        $this->_LeadHistory->ensureSchema();
        return true;
    }

    protected function _loadLeadMap($leadIds = array())
    {
        $leadIds = array_values(array_unique(array_map('intval', (array)$leadIds)));
        if(empty($leadIds))
        {
            return array();
        }
        $rows = (array)$this->_Lead->find('all', array(
            'conditions' => array('Lead.id' => $leadIds),
            'order' => array('Lead.id' => 'asc'),
            'recursive' => 0
        ));
        $map = array();
        foreach((array)$rows as $row)
        {
            $leadId = !empty($row['Lead']['id']) ? (int)$row['Lead']['id'] : 0;
            if($leadId > 0)
            {
                $map[$leadId] = $row;
            }
        }
        return $map;
    }

    protected function _leadMobileForMessage($leadRow)
    {
        $leadMobile = isset($leadRow['Lead']['lead_mobile']) ? trim((string)$leadRow['Lead']['lead_mobile']) : '';
        if($leadMobile !== '')
        {
            return $leadMobile;
        }
        $clientMobile = isset($leadRow['Client']['mobile']) ? trim((string)$leadRow['Client']['mobile']) : '';
        if($clientMobile !== '')
        {
            return $clientMobile;
        }
        return isset($leadRow['Client']['phone']) ? trim((string)$leadRow['Client']['phone']) : '';
    }

    protected function _leadEmailForMessage($leadRow)
    {
        $leadEmail = isset($leadRow['Lead']['lead_email']) ? trim((string)$leadRow['Lead']['lead_email']) : '';
        if($leadEmail !== '')
        {
            return $leadEmail;
        }
        return isset($leadRow['Client']['email']) ? trim((string)$leadRow['Client']['email']) : '';
    }

    protected function _leadTemplateRender($templateText, $leadRow)
    {
        $leadName = trim((string)(isset($leadRow['Lead']['lead_name']) ? $leadRow['Lead']['lead_name'] : ''));
        if($leadName === '')
        {
            $leadName = trim((string)(isset($leadRow['Client']['name']) ? $leadRow['Client']['name'] : ''));
        }
        $lastContactDate = isset($leadRow['Lead']['last_contact_date']) ? trim((string)$leadRow['Lead']['last_contact_date']) : '';
        if($lastContactDate !== '')
        {
            $lastContactTimestamp = strtotime($lastContactDate);
            if($lastContactTimestamp !== false)
            {
                $lastContactDate = date('d-M-Y', $lastContactTimestamp);
            }
        }
        $followUp = isset($leadRow['Lead']['follow_up']) ? trim((string)$leadRow['Lead']['follow_up']) : '';
        if($followUp !== '')
        {
            $timestamp = strtotime($followUp);
            if($timestamp !== false)
            {
                $followUp = date('d-M-Y H:i', $timestamp);
            }
        }
        $tokens = array(
            '{{lead_name}}' => $leadName,
            '{{client_name}}' => trim((string)(isset($leadRow['Client']['name']) ? $leadRow['Client']['name'] : '')),
            '{{lead_mobile}}' => $this->_leadMobileForMessage($leadRow),
            '{{lead_email}}' => $this->_leadEmailForMessage($leadRow),
            '{{lead_source}}' => trim((string)(isset($leadRow['Lead']['lead_source']) ? $leadRow['Lead']['lead_source'] : '')),
            '{{remarks}}' => trim((string)(isset($leadRow['Lead']['remarks']) ? $leadRow['Lead']['remarks'] : '')),
            '{{last_contact_date}}' => $lastContactDate,
            '{{follow_up}}' => $followUp,
            '{{property_name}}' => trim((string)(isset($leadRow['Property']['name']) ? $leadRow['Property']['name'] : ''))
        );
        return str_replace(array_keys($tokens), array_values($tokens), (string)$templateText);
    }

    protected function _smsGatewayLooksConfigured($smsSettings = array())
    {
        $smsRow = !empty($smsSettings['Smssetting']) ? (array)$smsSettings['Smssetting'] : array();
        return (trim((string)(isset($smsRow['api']) ? $smsRow['api'] : '')) !== '');
    }

    protected function _sendSms($mobileNo, $message, $smsSettings = array())
    {
        $smsRow = !empty($smsSettings['Smssetting']) ? (array)$smsSettings['Smssetting'] : array();
        $url = isset($smsRow['api']) ? trim((string)$smsRow['api']) : '';
        if($url === '')
        {
            throw new Exception('SMS API URL is missing.');
        }
        $query = array(
            isset($smsRow['husername']) ? (string)$smsRow['husername'] : 'username' => isset($smsRow['username']) ? (string)$smsRow['username'] : '',
            isset($smsRow['hpassword']) ? (string)$smsRow['hpassword'] : 'password' => isset($smsRow['password']) ? (string)$smsRow['password'] : '',
            isset($smsRow['hsenderid']) ? (string)$smsRow['hsenderid'] : 'sender' => isset($smsRow['senderid']) ? (string)$smsRow['senderid'] : '',
            isset($smsRow['hmobile']) ? (string)$smsRow['hmobile'] : 'mobile' => (string)$mobileNo,
            isset($smsRow['hmessage']) ? (string)$smsRow['hmessage'] : 'message' => (string)$message,
            'smstype' => 'TRANS'
        );
        if(!$this->_HttpSocket)
        {
            $this->_HttpSocket = new HttpSocket();
        }
        $requestType = isset($smsRow['type']) ? strtoupper(trim((string)$smsRow['type'])) : 'GET';
        if($requestType === 'POST')
        {
            $this->_HttpSocket->post($url, $query);
            return true;
        }
        $this->_HttpSocket->get($url, $query);
        return true;
    }

    protected function _logLeadHistory($leadRow = array(), $status = 'sent', $recipient = '', $messageBody = '', $gatewayLabel = '', $errorText = '')
    {
        $leadData = isset($leadRow['Lead']) ? (array)$leadRow['Lead'] : array();
        $leadId = !empty($leadData['id']) ? (int)$leadData['id'] : 0;
        if($leadId <= 0)
        {
            return;
        }
        $status = strtolower(trim((string)$status));
        $title = ($status === 'sent') ? 'SMS Sent (Scheduled)' : 'SMS Failed (Scheduled)';
        $details = trim((string)$messageBody);
        if(trim((string)$errorText) !== '')
        {
            $details .= ($details !== '' ? "\n\n" : '').'Note: '.trim((string)$errorText);
        }
        $this->_LeadHistory->createEntry(array(
            'lead_id' => $leadId,
            'project_id' => !empty($leadData['project_id']) ? (int)$leadData['project_id'] : 0,
            'lead_user_id' => !empty($leadData['user_id']) ? (int)$leadData['user_id'] : 0,
            'actor_user_id' => 0,
            'actor_name' => 'System Cron',
            'actor_role' => 'System',
            'entry_type' => 'sms',
            'title' => $title,
            'recipient' => trim((string)$recipient),
            'message_body' => $details,
            'meta_json' => array(
                'source_action' => 'LeadSmsScheduleShell::run',
                'gateway_source' => trim((string)$gatewayLabel),
                'status' => ($status === 'sent' ? 'sent' : 'failed')
            )
        ));
    }
}
?>
