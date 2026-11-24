<?php
App::uses('Shell', 'Console');
App::uses('ClassRegistry', 'Utility');
App::uses('CakeEmail', 'Network/Email');
App::uses('CakeNumber', 'Utility');
App::uses('HttpSocket', 'Network/Http');
App::uses('CakeLog', 'Log');

class PaymentReminderShell extends Shell
{
    protected $_PaymentReminder = null;
    protected $_PaymentReminderSetting = null;
    protected $_PlansPayment = null;
    protected $_Smssetting = null;
    protected $_Emailsetting = null;
    protected $_Configuration = null;
    protected $_Currency = null;
    protected $_Deal = null;
    protected $_User = null;

    protected $_siteName = 'White Villas';
    protected $_siteEmail = 'info@whitevillas.com.pk';
    protected $_contact = '';
    protected $_timezone = 'Asia/Karachi';
    protected $_currency = 'Rs ';
    protected $_smsNotification = true;
    protected $_emailNotification = true;
    protected $_emailSettings = array();
    protected $_smsSettings = array();
    protected $_blockedDealStatuses = array('Transferd', 'Transferred', 'Permanent Blocked', 'Pemanent Blocked', 'Refund', 'Refund-Running', 'Merged', 'Reversed');
    protected $_agencyUserInfoCache = array();

    public function getOptionParser()
    {
        $parser = parent::getOptionParser();
        $parser->description('Send automatic payment reminders by SMS, email, and WhatsApp API.');
        $parser->addOption('limit', array(
            'short' => 'l',
            'help' => 'Max reminder attempts for this run',
            'default' => null
        ));
        return $parser;
    }

    public function main()
    {
        return $this->run();
    }

    public function run()
    {
        if(!$this->_bootstrapModels())
        {
            $this->err('Unable to load required models.');
            return 1;
        }
        $this->_loadAppSettings();
        $this->_PaymentReminder->ensureSchema();
        $settings = (array)$this->_PaymentReminderSetting->currentSettings();
        if(empty($settings['auto_enabled']))
        {
            $this->out('Auto payment reminders are disabled.');
            return 0;
        }
        $channels = $this->_activeChannels($settings);
        if(empty($channels))
        {
            $this->out('No auto reminder channel is enabled.');
            return 0;
        }
        if(isset($this->params['limit']) && $this->params['limit'] !== null && $this->params['limit'] !== '')
        {
            $settings['daily_limit'] = (int)$this->params['limit'];
        }
        $limit = !empty($settings['daily_limit']) ? (int)$settings['daily_limit'] : 100;
        if($limit <= 0)
        {
            $limit = 100;
        }

        $rows = $this->_autoReminderRows($settings);
        $sent = 0;
        $failed = 0;
        $skipped = 0;
        $attempts = 0;

        foreach($rows as $row)
        {
            foreach($channels as $channel)
            {
                if($attempts >= $limit)
                {
                    $skipped++;
                    continue 2;
                }
                if($this->_recentReminderAlreadySent($row, $channel, $settings))
                {
                    $skipped++;
                    continue;
                }
                $attempts++;
                try
                {
                    if($channel === 'sms')
                    {
                        $this->_sendSmsReminder($row);
                    }
                    elseif($channel === 'email')
                    {
                        $this->_sendEmailReminder($row);
                    }
                    else
                    {
                        $this->_sendWhatsappReminder($row, $settings);
                    }
                    $sent++;
                }
                catch(Exception $e)
                {
                    CakeLog::write('error', 'Payment reminder auto failed: '.$e->getMessage());
                    $failed++;
                }
            }
        }

        $this->out('Payment reminder run complete. Sent: '.$sent.' | Failed: '.$failed.' | Skipped: '.$skipped);
        return 0;
    }

    protected function _bootstrapModels()
    {
        $this->_PaymentReminder = ClassRegistry::init('Admin.PaymentReminder');
        $this->_PaymentReminderSetting = ClassRegistry::init('Admin.PaymentReminderSetting');
        $this->_PlansPayment = ClassRegistry::init('Admin.PlansPayment');
        $this->_Smssetting = ClassRegistry::init('Admin.Smssetting');
        $this->_Emailsetting = ClassRegistry::init('Admin.Emailsetting');
        $this->_Configuration = ClassRegistry::init('Admin.Configuration');
        $this->_Currency = ClassRegistry::init('Admin.Currency');
        $this->_Deal = ClassRegistry::init('Admin.Deal');
        $this->_User = ClassRegistry::init('Admin.User');
        return ($this->_PaymentReminder && $this->_PaymentReminderSetting && $this->_PlansPayment && $this->_Smssetting && $this->_Emailsetting && $this->_Configuration);
    }

    protected function _loadAppSettings()
    {
        $config = (array)$this->_Configuration->findById(1);
        $row = !empty($config['Configuration']) ? (array)$config['Configuration'] : array();
        if(!empty($row['name']))
        {
            $this->_siteName = (string)$row['name'];
        }
        if(!empty($row['email']))
        {
            $this->_siteEmail = (string)$row['email'];
        }
        if(!empty($row['contact']))
        {
            $this->_contact = (string)$row['contact'];
        }
        $this->_smsNotification = !empty($row['sms_notification']);
        $this->_emailNotification = !empty($row['email_notification']);
        if(!empty($row['timezone']))
        {
            $this->_timezone = (string)$row['timezone'];
        }
        date_default_timezone_set($this->_timezone);
        if(!empty($row['currency']))
        {
            $currency = (array)$this->_Currency->findById((int)$row['currency']);
            if(!empty($currency['Currency']['symbol']))
            {
                $this->_currency = trim((string)$currency['Currency']['symbol']).' ';
            }
            elseif(!empty($currency['Currency']['short']))
            {
                $this->_currency = trim((string)$currency['Currency']['short']).' ';
            }
        }
        $this->_emailSettings = (array)$this->_Emailsetting->findById(1);
        $this->_smsSettings = (array)$this->_Smssetting->findById(1);
    }

    protected function _activeChannels($settings)
    {
        $channels = array();
        if(!empty($settings['send_sms']))
        {
            $channels[] = 'sms';
        }
        if(!empty($settings['send_email']))
        {
            $channels[] = 'email';
        }
        if(!empty($settings['send_whatsapp']))
        {
            $channels[] = 'whatsapp';
        }
        return $channels;
    }

    protected function _autoReminderRows($settings)
    {
        $today = date('Y-m-d');
        $daysBefore = !empty($settings['days_before']) ? (int)$settings['days_before'] : 0;
        if($daysBefore < 0)
        {
            $daysBefore = 0;
        }
        $endDate = date('Y-m-d', strtotime($today.' + '.$daysBefore.' days'));
        $dateConditions = array(array('PlansPayment.date >=' => $today, 'PlansPayment.date <=' => $endDate));
        if(!empty($settings['include_overdue']))
        {
            $dateConditions[] = array('PlansPayment.date <' => $today);
        }
        $conditions = array('DealsPayment.id IS NULL');
        $conditions[] = count($dateConditions) > 1 ? array('OR' => $dateConditions) : $dateConditions[0];
        $dealStatusCondition = $this->_dealStatusCondition();
        if(!empty($dealStatusCondition))
        {
            $conditions[] = $dealStatusCondition;
        }
        $rows = (array)$this->_PlansPayment->find('all', array(
            'fields' => $this->_installmentFields(),
            'joins' => $this->_installmentJoins(),
            'conditions' => $conditions,
            'order' => array('PlansPayment.date' => 'asc', 'PlansPayment.id' => 'asc'),
            'limit' => 1000,
            'recursive' => -1
        ));
        return $this->_groupOverdueRowsByDeal($rows, $today);
    }

    protected function _installmentJoins()
    {
        return array(
            array('table' => 'deals', 'alias' => 'Deal', 'type' => 'LEFT', 'conditions' => array('PlansPayment.deal_id=Deal.id')),
            array('table' => 'deals_payments', 'alias' => 'DealsPayment', 'type' => 'LEFT', 'conditions' => array('PlansPayment.id=DealsPayment.plans_payment_id')),
            array('table' => 'clients', 'alias' => 'Client', 'type' => 'LEFT', 'conditions' => array('Deal.client_id=Client.id')),
            array('table' => 'properties', 'alias' => 'Property', 'type' => 'LEFT', 'conditions' => array('Deal.property_id=Property.id')),
            array('table' => 'projects', 'alias' => 'Project', 'type' => 'LEFT', 'conditions' => array('Property.project_id=Project.id'))
        );
    }

    protected function _installmentFields()
    {
        return array(
            'PlansPayment.id', 'PlansPayment.deal_id', 'PlansPayment.name', 'PlansPayment.amount', 'PlansPayment.date',
            'Deal.id', 'Deal.invoice_no', 'Deal.total_amount', 'Deal.deal_status', 'Deal.user_id',
            'Client.id', 'Client.name', 'Client.phone', 'Client.mobile', 'Client.email',
            'Property.id', 'Property.name', 'Property.address', 'Property.project_id',
            'Project.id', 'Project.name', 'Project.phone', 'Project.mobile', 'Project.email'
        );
    }

    protected function _groupOverdueRowsByDeal($rows, $today)
    {
        $output = array();
        $overdueGroups = array();
        foreach((array)$rows as $row)
        {
            $dueDate = !empty($row['PlansPayment']['date']) ? $row['PlansPayment']['date'] : '';
            $dealId = !empty($row['Deal']['id']) ? (int)$row['Deal']['id'] : 0;
            if($dueDate !== '' && $dueDate < $today && $dealId > 0)
            {
                if(empty($overdueGroups[$dealId]))
                {
                    $row['_ReminderAggregate'] = array('is_overdue_group' => true, 'overdue_count' => 0, 'overdue_amount' => 0, 'plan_ids' => array());
                    $overdueGroups[$dealId] = $row;
                }
                $overdueGroups[$dealId]['_ReminderAggregate']['overdue_count']++;
                $overdueGroups[$dealId]['_ReminderAggregate']['overdue_amount'] += !empty($row['PlansPayment']['amount']) ? (float)$row['PlansPayment']['amount'] : 0;
                if(!empty($row['PlansPayment']['id']))
                {
                    $overdueGroups[$dealId]['_ReminderAggregate']['plan_ids'][] = (int)$row['PlansPayment']['id'];
                }
                continue;
            }
            $output[] = $row;
        }
        foreach($overdueGroups as $row)
        {
            $count = !empty($row['_ReminderAggregate']['overdue_count']) ? (int)$row['_ReminderAggregate']['overdue_count'] : 1;
            $amount = !empty($row['_ReminderAggregate']['overdue_amount']) ? (float)$row['_ReminderAggregate']['overdue_amount'] : 0;
            $row['PlansPayment']['name'] = 'Overdue Installments ('.$count.')';
            $row['PlansPayment']['amount'] = $amount;
            $output[] = $row;
        }
        usort($output, array($this, '_sortReminderRowsByDueDate'));
        return $output;
    }

    protected function _sortReminderRowsByDueDate($a, $b)
    {
        $dateA = !empty($a['PlansPayment']['date']) ? strtotime($a['PlansPayment']['date']) : PHP_INT_MAX;
        $dateB = !empty($b['PlansPayment']['date']) ? strtotime($b['PlansPayment']['date']) : PHP_INT_MAX;
        if($dateA === $dateB)
        {
            return (int)$a['PlansPayment']['id'] - (int)$b['PlansPayment']['id'];
        }
        return $dateA < $dateB ? -1 : 1;
    }

    protected function _recentReminderAlreadySent($row, $channel, $settings)
    {
        $repeatDays = isset($settings['repeat_after_days']) ? (int)$settings['repeat_after_days'] : 0;
        if($repeatDays <= 0)
        {
            return false;
        }
        $conditions = array(
            'PaymentReminder.channel' => $channel,
            'PaymentReminder.reminder_type' => $this->_reminderType($row),
            'PaymentReminder.status' => array('sent', 'opened', 'prepared'),
            'PaymentReminder.created >=' => date('Y-m-d H:i:s', strtotime('-'.$repeatDays.' days'))
        );
        if($this->_reminderType($row) === 'overdue' && !empty($row['Deal']['id']))
        {
            $conditions['PaymentReminder.deal_id'] = (int)$row['Deal']['id'];
        }
        else
        {
            $conditions['PaymentReminder.plans_payment_id'] = !empty($row['PlansPayment']['id']) ? (int)$row['PlansPayment']['id'] : 0;
        }
        return ((int)$this->_PaymentReminder->find('count', array('conditions' => $conditions, 'recursive' => -1)) > 0);
    }

    protected function _sendSmsReminder($row)
    {
        $mobileNo = $this->_clientMobile($row);
        $message = $this->_buildReminderMessage($row, 'sms');
        if($mobileNo === '')
        {
            $this->_saveReminderLog($row, 'sms', 'failed', $message, 'Client mobile number is missing.');
            throw new Exception('Client mobile number is missing.');
        }
        if(!$this->_smsNotification)
        {
            $this->_saveReminderLog($row, 'sms', 'failed', $message, 'SMS notification is disabled.');
            throw new Exception('SMS notification is disabled.');
        }
        try
        {
            $this->_sendSmsGateway($mobileNo, $message, $this->_smsSettings);
            $this->_saveReminderLog($row, 'sms', 'sent', $message, '');
        }
        catch(Exception $e)
        {
            $this->_saveReminderLog($row, 'sms', 'failed', $message, $e->getMessage());
            throw $e;
        }
    }

    protected function _sendEmailReminder($row)
    {
        $email = !empty($row['Client']['email']) ? trim((string)$row['Client']['email']) : '';
        $message = $this->_buildReminderMessage($row, 'email');
        if($email === '')
        {
            $this->_saveReminderLog($row, 'email', 'failed', $message, 'Client email is missing.');
            throw new Exception('Client email is missing.');
        }
        if(!$this->_emailNotification)
        {
            $this->_saveReminderLog($row, 'email', 'failed', $message, 'Email notification is disabled.');
            throw new Exception('Email notification is disabled.');
        }
        $emailRow = !empty($this->_emailSettings['Emailsetting']) ? (array)$this->_emailSettings['Emailsetting'] : array();
        $emailType = !empty($emailRow['type']) ? (string)$emailRow['type'] : 'Mail';
        try
        {
            $Email = new CakeEmail();
            $Email->transport($emailType);
            if(strtolower($emailType) === 'smtp')
            {
                $Email->config(array(
                    'host' => isset($emailRow['host']) ? $emailRow['host'] : '',
                    'port' => isset($emailRow['port']) ? $emailRow['port'] : '',
                    'username' => isset($emailRow['username']) ? $emailRow['username'] : '',
                    'password' => isset($emailRow['password']) ? $emailRow['password'] : '',
                    'timeout' => 90
                ));
            }
            $Email->from(array($this->_siteEmail => $this->_siteName));
            $Email->to($email);
            $Email->subject($this->_buildReminderSubject($row));
            $Email->emailFormat('text');
            $Email->send($message);
            $this->_saveReminderLog($row, 'email', 'sent', $message, '');
        }
        catch(Exception $e)
        {
            $this->_saveReminderLog($row, 'email', 'failed', $message, $e->getMessage());
            throw $e;
        }
    }

    protected function _sendWhatsappReminder($row, $settings)
    {
        $mobileNo = $this->_whatsappNumber($this->_clientMobile($row));
        $message = $this->_buildReminderMessage($row, 'whatsapp');
        if($mobileNo === '')
        {
            $this->_saveReminderLog($row, 'whatsapp', 'failed', $message, 'Client WhatsApp number is missing.');
            throw new Exception('Client WhatsApp number is missing.');
        }
        if(empty($settings['whatsapp_mode']) || $settings['whatsapp_mode'] !== 'api')
        {
            $this->_saveReminderLog($row, 'whatsapp', 'failed', $message, 'WhatsApp auto send needs API gateway settings.');
            throw new Exception('WhatsApp auto send needs API gateway settings.');
        }
        try
        {
            $this->_sendWhatsappGateway($mobileNo, $message, $settings);
            $this->_saveReminderLog($row, 'whatsapp', 'sent', $message, '');
        }
        catch(Exception $e)
        {
            $this->_saveReminderLog($row, 'whatsapp', 'failed', $message, $e->getMessage());
            throw $e;
        }
    }

    protected function _buildReminderSubject($row)
    {
        $invoiceNo = !empty($row['Deal']['invoice_no']) ? $row['Deal']['invoice_no'] : '-';
        return ($this->_reminderType($row) === 'overdue' ? 'Overdue Payment Reminder' : 'Installment Payment Reminder').' - '.$invoiceNo;
    }

    protected function _buildReminderMessage($row, $channel = 'sms')
    {
        $context = $this->_reminderContext($row);
        if($channel === 'email')
        {
            $lines = array(
                (!empty($context['is_overdue']) ? 'Overdue Payment Reminder' : 'Installment Payment Reminder'),
                '',
                'Dear '.$context['client_name'].',',
                '',
                'Membership No: '.$context['invoice_no'],
                'Property/File: '.$context['property_name'],
                'Installment: '.$context['installment'],
                'Due Date: '.$context['due_date'],
                'Installment Amount: '.$context['amount']
            );
            if(!empty($context['overdue_count']))
            {
                $lines[] = '';
                $lines[] = 'Overdue Summary';
                $lines[] = 'Total Overdue Installments: '.$context['overdue_count'];
                $lines[] = 'Total Overdue Amount: '.$context['overdue_amount'];
                $lines[] = 'Oldest Overdue Age: '.$context['overdue_age'].' days';
            }
            $lines[] = '';
            $lines[] = 'Please clear the pending dues as soon as possible. If you have already paid, kindly ignore this message or contact our office for confirmation.';
            $lines[] = '';
            $lines[] = 'Regards,';
            $lines[] = $context['agency_name'];
            $lines[] = 'Contact: '.$context['agency_phone'];
            return implode("\n", $lines);
        }
        if($channel === 'whatsapp')
        {
            $lines = array(
                (!empty($context['is_overdue']) ? '*Overdue Payment Reminder*' : '*Installment Payment Reminder*'),
                '',
                'Dear *'.$context['client_name'].'*,',
                '',
                (!empty($context['is_overdue']) ? 'Your installment payment is overdue. Please clear it as soon as possible.' : 'Your installment payment is coming due. Please arrange payment before the due date.'),
                '',
                '*Membership No:* '.$context['invoice_no'],
                '*Property/File:* '.$context['property_name'],
                '*Installment:* '.$context['installment'],
                '*Due Date:* '.$context['due_date'],
                '*Installment Amount:* '.$context['amount']
            );
            if(!empty($context['overdue_count']))
            {
                $lines[] = '';
                $lines[] = '*Overdue Summary*';
                $lines[] = 'Total overdue installments: *'.$context['overdue_count'].'*';
                $lines[] = 'Total overdue amount: *'.$context['overdue_amount'].'*';
                $lines[] = 'Oldest overdue age: *'.$context['overdue_age'].' days*';
            }
            $lines[] = '';
            $lines[] = 'If you have already paid, please ignore this message or share payment proof for confirmation.';
            $lines[] = '*Contact:* '.$context['agency_phone'];
            $lines[] = '';
            $lines[] = 'Regards,';
            $lines[] = '*'.$context['agency_name'].'*';
            return implode("\n", $lines);
        }

        $message = 'Dear '.$context['client_name'].', ';
        if(!empty($context['is_overdue']))
        {
            $message .= 'your installment '.$context['installment'].' for membership '.$context['invoice_no'].' is overdue. Due Date: '.$context['due_date'].'. ';
        }
        else
        {
            $message .= 'your installment '.$context['installment'].' for membership '.$context['invoice_no'].' is due on '.$context['due_date'].'. ';
        }
        $message .= 'Amount: '.$context['amount'].'. ';
        if(!empty($context['overdue_count']))
        {
            $message .= 'Total overdue installments: '.$context['overdue_count'].'. Total overdue amount: '.$context['overdue_amount'].'. Oldest overdue: '.$context['overdue_age'].' days. ';
        }
        $message .= 'Please clear dues or contact '.$context['agency_name'].' '.$context['agency_phone'].'.';
        return $message;
    }

    protected function _reminderContext($row)
    {
        $overdueSummary = $this->_dealOverdueSummary($row);
        $isOverdue = ($this->_reminderType($row) === 'overdue');
        $installmentName = !empty($row['PlansPayment']['name']) ? trim((string)$row['PlansPayment']['name']) : 'Installment';
        $installmentAmount = !empty($row['PlansPayment']['amount']) ? (float)$row['PlansPayment']['amount'] : 0;
        if($isOverdue && !empty($overdueSummary['count']))
        {
            $installmentName = 'Overdue Installments ('.(int)$overdueSummary['count'].')';
            $installmentAmount = (float)$overdueSummary['amount'];
        }
        $messageDueDateRaw = date('Y-m-d', strtotime(date('Y-m-d').' +5 days'));
        return array(
            'client_name' => !empty($row['Client']['name']) ? trim((string)$row['Client']['name']) : 'Customer',
            'invoice_no' => !empty($row['Deal']['invoice_no']) ? trim((string)$row['Deal']['invoice_no']) : '-',
            'property_name' => !empty($row['Property']['name']) ? trim((string)$row['Property']['name']) : '-',
            'installment' => $installmentName,
            'amount' => $this->_currency.CakeNumber::format($installmentAmount),
            'due_date' => date('d-M-Y', strtotime($messageDueDateRaw)),
            'agency_name' => !empty($row['Project']['name']) ? trim((string)$row['Project']['name']) : $this->_siteName,
            'agency_phone' => $this->_agencyReminderPhone($row),
            'is_overdue' => $isOverdue,
            'overdue_count' => !empty($overdueSummary['count']) ? (int)$overdueSummary['count'] : 0,
            'overdue_amount' => $this->_currency.CakeNumber::format(!empty($overdueSummary['amount']) ? (float)$overdueSummary['amount'] : 0),
            'overdue_age' => !empty($overdueSummary['age']) ? (int)$overdueSummary['age'] : 0
        );
    }

    protected function _dealOverdueSummary($row)
    {
        $summary = array('count' => 0, 'amount' => 0, 'age' => 0);
        if(empty($row['Deal']['id']))
        {
            return $summary;
        }
        $today = date('Y-m-d');
        $conditions = array(
            'PlansPayment.deal_id' => (int)$row['Deal']['id'],
            'PlansPayment.date <' => $today,
            'DealsPayment.id IS NULL'
        );
        $dealStatusCondition = $this->_dealStatusCondition();
        if(!empty($dealStatusCondition))
        {
            $conditions[] = $dealStatusCondition;
        }
        $rows = (array)$this->_PlansPayment->find('all', array(
            'fields' => array('PlansPayment.id', 'PlansPayment.name', 'PlansPayment.amount', 'PlansPayment.date', 'Deal.id'),
            'joins' => array(
                array('table' => 'deals', 'alias' => 'Deal', 'type' => 'LEFT', 'conditions' => array('PlansPayment.deal_id=Deal.id')),
                array('table' => 'deals_payments', 'alias' => 'DealsPayment', 'type' => 'LEFT', 'conditions' => array('PlansPayment.id=DealsPayment.plans_payment_id'))
            ),
            'conditions' => $conditions,
            'order' => array('PlansPayment.date' => 'asc'),
            'recursive' => -1
        ));
        $oldestDate = '';
        foreach($rows as $overdueRow)
        {
            $summary['count']++;
            $summary['amount'] += !empty($overdueRow['PlansPayment']['amount']) ? (float)$overdueRow['PlansPayment']['amount'] : 0;
            if($oldestDate === '' || (!empty($overdueRow['PlansPayment']['date']) && $overdueRow['PlansPayment']['date'] < $oldestDate))
            {
                $oldestDate = !empty($overdueRow['PlansPayment']['date']) ? $overdueRow['PlansPayment']['date'] : '';
            }
        }
        if($oldestDate !== '')
        {
            $summary['age'] = max(0, (int)floor((strtotime($today) - strtotime($oldestDate)) / 86400));
        }
        return $summary;
    }

    protected function _agencyReminderPhone($row)
    {
        $projectId = !empty($row['Project']['id']) ? (int)$row['Project']['id'] : 0;
        $agencyUserInfo = $this->_agencyUserInfo($projectId);
        $candidates = array(
            isset($agencyUserInfo['mobile']) ? $agencyUserInfo['mobile'] : '',
            isset($agencyUserInfo['phone']) ? $agencyUserInfo['phone'] : '',
            !empty($row['Project']['mobile']) ? $row['Project']['mobile'] : '',
            !empty($row['Project']['phone']) ? $row['Project']['phone'] : '',
            $this->_contact
        );
        $fallback = '';
        foreach($candidates as $candidate)
        {
            $candidate = trim((string)$candidate);
            if($candidate === '')
            {
                continue;
            }
            if($fallback === '')
            {
                $fallback = $candidate;
            }
            if(!$this->_isOldReminderContact($candidate))
            {
                return $candidate;
            }
        }
        return $fallback;
    }

    protected function _agencyUserInfo($projectId)
    {
        $projectId = (int)$projectId;
        if($projectId <= 0 || !$this->_User)
        {
            return array();
        }
        if(isset($this->_agencyUserInfoCache[$projectId]))
        {
            return $this->_agencyUserInfoCache[$projectId];
        }
        $row = (array)$this->_User->find('first', array(
            'fields' => array('User.id', 'User.name', 'User.phone', 'User.mobile', 'User.email'),
            'conditions' => array('User.project_id' => $projectId, 'User.ugroup_id' => 4, 'User.status' => 'Active'),
            'order' => array('User.id' => 'asc'),
            'recursive' => -1
        ));
        $this->_agencyUserInfoCache[$projectId] = !empty($row['User']) ? (array)$row['User'] : array();
        return $this->_agencyUserInfoCache[$projectId];
    }

    protected function _isOldReminderContact($phone)
    {
        $phone = preg_replace('/\D+/', '', (string)$phone);
        if(substr($phone, 0, 2) === '00')
        {
            $phone = substr($phone, 2);
        }
        if(substr($phone, 0, 1) === '0')
        {
            $phone = '92'.substr($phone, 1);
        }
        return ($phone === '923394080002');
    }

    protected function _saveReminderLog($row, $channel, $status, $message, $errorText = '')
    {
        $logAmount = $this->_reminderLogAmount($row);
        $data = array(
            'project_id' => !empty($row['Property']['project_id']) ? (int)$row['Property']['project_id'] : 0,
            'deal_id' => !empty($row['Deal']['id']) ? (int)$row['Deal']['id'] : 0,
            'plans_payment_id' => !empty($row['PlansPayment']['id']) ? (int)$row['PlansPayment']['id'] : 0,
            'channel' => $channel,
            'reminder_type' => $this->_reminderType($row),
            'recipient_name' => !empty($row['Client']['name']) ? $row['Client']['name'] : '',
            'recipient_mobile' => $this->_clientMobile($row),
            'recipient_email' => !empty($row['Client']['email']) ? $row['Client']['email'] : '',
            'due_date' => !empty($row['PlansPayment']['date']) ? $row['PlansPayment']['date'] : null,
            'amount' => $logAmount,
            'message' => $message,
            'status' => $status,
            'error_text' => $errorText,
            'created_by' => 0
        );
        if($status === 'sent' || $status === 'opened' || $status === 'prepared')
        {
            $data['sent_at'] = date('Y-m-d H:i:s');
        }
        else
        {
            $data['failed_at'] = date('Y-m-d H:i:s');
        }
        $this->_PaymentReminder->create();
        $this->_PaymentReminder->save(array('PaymentReminder' => $data), array('validate' => false));
    }

    protected function _reminderLogAmount($row)
    {
        $amount = !empty($row['PlansPayment']['amount']) ? (float)$row['PlansPayment']['amount'] : 0;
        if($this->_reminderType($row) !== 'overdue')
        {
            return $amount;
        }
        if(!empty($row['_ReminderAggregate']['overdue_amount']))
        {
            return (float)$row['_ReminderAggregate']['overdue_amount'];
        }
        $summary = $this->_dealOverdueSummary($row);
        if(!empty($summary['amount']))
        {
            return (float)$summary['amount'];
        }
        return $amount;
    }

    protected function _sendSmsGateway($mobileNo, $message, $smsSettings = array())
    {
        $smsRow = !empty($smsSettings['Smssetting']) ? (array)$smsSettings['Smssetting'] : array();
        $url = isset($smsRow['api']) ? trim((string)$smsRow['api']) : '';
        if($url === '')
        {
            throw new Exception('SMS API URL is missing.');
        }
        $this->_assertGatewayHostResolvable($url, 'SMS');
        $query = array(
            $this->_paramName($smsRow, 'husername', 'username') => isset($smsRow['username']) ? (string)$smsRow['username'] : '',
            $this->_paramName($smsRow, 'hpassword', 'password') => isset($smsRow['password']) ? (string)$smsRow['password'] : '',
            $this->_paramName($smsRow, 'hsenderid', 'sender') => isset($smsRow['senderid']) ? (string)$smsRow['senderid'] : '',
            $this->_paramName($smsRow, 'hmobile', 'mobile') => (string)$mobileNo,
            $this->_paramName($smsRow, 'hmessage', 'message') => (string)$message,
            'smstype' => 'TRANS'
        );
        $socket = new HttpSocket(array('timeout' => 30));
        if(strtolower(trim((string)(isset($smsRow['type']) ? $smsRow['type'] : 'Get'))) === 'get')
        {
            $response = $socket->get($url, $query);
        }
        else
        {
            $response = $socket->post($url, $query);
        }
        $this->_assertGatewayResponse($response, 'SMS');
    }

    protected function _sendWhatsappGateway($mobileNo, $message, $settings)
    {
        $url = !empty($settings['whatsapp_api']) ? trim((string)$settings['whatsapp_api']) : '';
        if($url === '')
        {
            throw new Exception('WhatsApp API URL is missing.');
        }
        $this->_assertGatewayHostResolvable($url, 'WhatsApp');
        $query = array(
            $this->_paramName($settings, 'whatsapp_mobile_param', 'number') => (string)$mobileNo,
            $this->_paramName($settings, 'whatsapp_message_param', 'message') => (string)$message
        );
        $query = array_merge($query, $this->_parseExtraParams(!empty($settings['whatsapp_extra_params']) ? $settings['whatsapp_extra_params'] : ''));
        $headers = array();
        $token = !empty($settings['whatsapp_token']) ? trim((string)$settings['whatsapp_token']) : '';
        $tokenHeader = !empty($settings['whatsapp_token_header']) ? trim((string)$settings['whatsapp_token_header']) : '';
        if($token !== '' && $tokenHeader !== '')
        {
            $headers[$tokenHeader] = $token;
        }
        elseif($token !== '')
        {
            $query['token'] = $token;
        }
        $socket = new HttpSocket(array('timeout' => 30));
        $request = !empty($headers) ? array('header' => $headers) : array();
        if(strtolower(trim((string)(isset($settings['whatsapp_type']) ? $settings['whatsapp_type'] : 'Post'))) === 'get')
        {
            $response = $socket->get($url, $query, $request);
        }
        else
        {
            $response = $socket->post($url, $query, $request);
        }
        $this->_assertGatewayResponse($response, 'WhatsApp');
    }

    protected function _assertGatewayHostResolvable($url, $label)
    {
        $parsedUrl = parse_url($url);
        $host = !empty($parsedUrl['host']) ? $parsedUrl['host'] : '';
        if($host === '')
        {
            throw new Exception($label.' API URL is invalid.');
        }
        if(!filter_var($host, FILTER_VALIDATE_IP) && gethostbyname($host) === $host)
        {
            throw new Exception($label.' gateway host could not be resolved: '.$host);
        }
    }

    protected function _assertGatewayResponse($response, $label)
    {
        if($response === false)
        {
            throw new Exception($label.' gateway did not return a response.');
        }
        if(isset($response->code) && (int)$response->code >= 400)
        {
            throw new Exception($label.' gateway returned HTTP '.(int)$response->code.'.');
        }
    }

    protected function _paramName($row, $key, $fallback)
    {
        $name = isset($row[$key]) ? trim((string)$row[$key]) : '';
        return $name !== '' ? $name : $fallback;
    }

    protected function _parseExtraParams($text)
    {
        $params = array();
        $lines = preg_split('/\r\n|\r|\n/', (string)$text);
        foreach((array)$lines as $line)
        {
            $line = trim((string)$line);
            if($line === '' || strpos($line, '=') === false)
            {
                continue;
            }
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            if($key !== '')
            {
                $params[$key] = trim($value);
            }
        }
        return $params;
    }

    protected function _clientMobile($row)
    {
        if(!empty($row['Client']['mobile']))
        {
            return trim((string)$row['Client']['mobile']);
        }
        if(!empty($row['Client']['phone']))
        {
            return trim((string)$row['Client']['phone']);
        }
        return '';
    }

    protected function _whatsappNumber($mobileNo)
    {
        $mobileNo = preg_replace('/\D+/', '', (string)$mobileNo);
        if($mobileNo === '')
        {
            return '';
        }
        if(substr($mobileNo, 0, 2) === '00')
        {
            $mobileNo = substr($mobileNo, 2);
        }
        if(substr($mobileNo, 0, 1) === '0')
        {
            $mobileNo = '92'.substr($mobileNo, 1);
        }
        return $mobileNo;
    }

    protected function _reminderType($row)
    {
        if(!empty($row['PlansPayment']['date']) && $row['PlansPayment']['date'] < date('Y-m-d'))
        {
            return 'overdue';
        }
        return 'before_due';
    }

    protected function _dealStatusCondition()
    {
        if(!$this->_Deal || !$this->_Deal->hasField('deal_status'))
        {
            return array();
        }
        return array('OR' => array(
            'Deal.deal_status IS NULL',
            'Deal.deal_status' => '',
            'NOT' => array('Deal.deal_status' => $this->_blockedDealStatuses)
        ));
    }
}
?>
