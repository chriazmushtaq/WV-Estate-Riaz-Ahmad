<?php
App::uses('CakeTime', 'Utility');
App::uses('CakeNumber', 'Utility');
App::uses('CakeEmail', 'Network/Email');
App::uses('HttpSocket', 'Network/Http');
App::uses('Router', 'Routing');

class PaymentremindersController extends AdminAppController
{
    public $components = array('Session', 'Paginator');
    public $helpers = array('Paginator', 'Js' => array('Jquery'));
    public $uses = array('Admin.PaymentReminder', 'Admin.PaymentReminderSetting');

    protected $_blockedDealStatuses = array('Transferd', 'Transferred', 'Permanent Blocked', 'Pemanent Blocked', 'Refund', 'Refund-Running', 'Merged', 'Reversed');
    protected $_agencyUserInfoCache = array();
    protected $_reminderSiteConfigCache = null;

    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->_ensurePaymentRemindersTable();
        $this->_ensurePaymentReminderTemplates();
    }

    public function index()
    {
        $this->loadModel('Admin.PlansPayment');
        $today = CakeTime::format('Y-m-d', CakeTime::convert(time(), $this->siteTimezone));
        $status = isset($this->request->query['status']) ? trim((string)$this->request->query['status']) : 'all';
        if(!in_array($status, array('all', 'due_soon', 'overdue'), true))
        {
            $status = 'all';
        }
        $daysBefore = isset($this->request->query['days_before']) ? (int)$this->request->query['days_before'] : 7;
        if($daysBefore < 0)
        {
            $daysBefore = 0;
        }
        if($daysBefore > 90)
        {
            $daysBefore = 90;
        }
        $keyword = isset($this->request->query['keyword']) ? trim((string)$this->request->query['keyword']) : '';
        $promiseFilter = isset($this->request->query['promise_filter']) ? strtolower(trim((string)$this->request->query['promise_filter'])) : 'all';
        if(!in_array($promiseFilter, array('all', 'day', 'week', 'month', 'year', 'custom'), true))
        {
            $promiseFilter = 'all';
        }
        $promiseStartDate = isset($this->request->query['promise_start_date']) ? $this->_normalizeReminderDate($this->request->query['promise_start_date']) : '';
        if($promiseStartDate === false)
        {
            $promiseStartDate = '';
        }
        $promiseEndDate = isset($this->request->query['promise_end_date']) ? $this->_normalizeReminderDate($this->request->query['promise_end_date']) : '';
        if($promiseEndDate === false)
        {
            $promiseEndDate = '';
        }
        $endDate = date('Y-m-d', strtotime($today.' + '.$daysBefore.' days'));

        $conditions = array(
            'DealsPayment.id IS NULL'
        );
        if($status === 'due_soon')
        {
            $conditions[] = array('PlansPayment.date >=' => $today, 'PlansPayment.date <=' => $endDate);
        }
        elseif($status === 'overdue')
        {
            $conditions[] = array('PlansPayment.date <' => $today);
        }
        else
        {
            $conditions[] = array('PlansPayment.date <=' => $endDate);
        }
        if($keyword !== '')
        {
            $conditions[] = array('OR' => array(
                'Client.name LIKE' => '%'.$keyword.'%',
                'Client.phone LIKE' => '%'.$keyword.'%',
                'Client.mobile LIKE' => '%'.$keyword.'%',
                'Client.email LIKE' => '%'.$keyword.'%',
                'Deal.invoice_no LIKE' => '%'.$keyword.'%',
                'Property.name LIKE' => '%'.$keyword.'%',
                'Project.name LIKE' => '%'.$keyword.'%',
                'PlansPayment.name LIKE' => '%'.$keyword.'%'
            ));
        }
        $promiseDateCondition = $this->_promiseDateFilterCondition($promiseFilter, $promiseStartDate, $promiseEndDate, $today);
        if(!empty($promiseDateCondition))
        {
            $conditions[] = $promiseDateCondition;
        }
        $permission = $this->_reminderPermissionCondition();
        if(!empty($permission))
        {
            $conditions[] = $permission;
        }
        $dealStatusCondition = $this->_dealStatusCondition();
        if(!empty($dealStatusCondition))
        {
            $conditions[] = $dealStatusCondition;
        }

        $joins = $this->_installmentJoins();
        $fields = $this->_installmentFields();
        $rows = $this->_paginatedReminderRows($conditions, $joins, $fields, $today);
        $summary = $this->_buildReminderSummary($conditions, $joins, $today);
        $history = $this->_recentReminderHistory();
        $autoSettings = $this->_autoReminderSettings();
        $reminderDueDate = $this->_reminderSendDueDateRaw($today);
        $canClearReminderLogs = $this->_canClearReminderLogs();

        $this->set(compact('rows', 'summary', 'status', 'daysBefore', 'keyword', 'today', 'endDate', 'history', 'autoSettings', 'reminderDueDate', 'promiseFilter', 'promiseStartDate', 'promiseEndDate', 'canClearReminderLogs'));
    }

    public function clearlogs()
    {
        if(!$this->request->is('post'))
        {
            throw new MethodNotAllowedException(__('Invalid request method.'));
        }
        if(!$this->_canClearReminderLogs())
        {
            $this->Session->setFlash(__('Only admin can clear reminder logs.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }

        $conditions = $this->_reminderLogScopeConditions(true);
        try
        {
            $count = (int)$this->PaymentReminder->find('count', array(
                'conditions' => $conditions,
                'recursive' => -1
            ));
            if($count <= 0)
            {
                $this->Session->setFlash(__('No reminder logs found to clear.'), 'flash', array('alert' => 'info'));
                return $this->redirect($this->referer(array('action' => 'index'), true));
            }
            if($this->PaymentReminder->deleteAll($conditions, false, false))
            {
                $this->Session->setFlash(__('%s reminder log(s) cleared successfully.', $count), 'flash', array('alert' => 'success'));
            }
            else
            {
                $this->Session->setFlash(__('Unable to clear reminder logs. Please try again.'), 'flash', array('alert' => 'danger'));
            }
        }
        catch(Exception $e)
        {
            $this->Session->setFlash(__('Unable to clear reminder logs. Please try again.'), 'flash', array('alert' => 'danger'));
        }

        return $this->redirect($this->referer(array('action' => 'index'), true));
    }

    public function savepromise()
    {
        if(!$this->request->is('post'))
        {
            throw new MethodNotAllowedException(__('Invalid request method.'));
        }
        $this->autoRender = false;
        $this->loadModel('Admin.PlansPayment');

        $planId = !empty($this->request->data['plan_id']) ? (int)$this->request->data['plan_id'] : 0;
        $promiseDate = isset($this->request->data['promise_date']) ? $this->_normalizeReminderDate($this->request->data['promise_date']) : '';
        if($planId <= 0)
        {
            return $this->_jsonResponse(array('success' => false, 'message' => __('Invalid reminder row.')));
        }
        if($promiseDate === false)
        {
            return $this->_jsonResponse(array('success' => false, 'message' => __('Please select a valid promise date.')));
        }
        if(!$this->PlansPayment->hasField('recovery_promise_date'))
        {
            $this->_ensurePaymentReminderPromiseColumn();
        }
        if(!$this->PlansPayment->hasField('recovery_promise_date'))
        {
            return $this->_jsonResponse(array('success' => false, 'message' => __('Promise date column is missing. Please import/update database schema.')));
        }

        $row = $this->_getReminderInstallment($planId);
        if(empty($row))
        {
            return $this->_jsonResponse(array('success' => false, 'message' => __('Installment reminder record was not found or is already paid.')));
        }

        $db = $this->PlansPayment->getDataSource();
        $value = ($promiseDate === '') ? 'NULL' : $db->value($promiseDate, 'date');
        $saved = $this->PlansPayment->updateAll(
            array('PlansPayment.recovery_promise_date' => $value),
            array('PlansPayment.id' => $planId)
        );

        if(!$saved)
        {
            return $this->_jsonResponse(array('success' => false, 'message' => __('Promise date could not be saved.')));
        }

        return $this->_jsonResponse(array(
            'success' => true,
            'message' => __('Promise date saved.'),
            'date' => $promiseDate,
            'display' => $this->_formatReminderShortDate($promiseDate)
        ));
    }

    public function sendsms($planId = null)
    {
        if(!$this->request->is('post'))
        {
            throw new MethodNotAllowedException(__('Invalid request method.'));
        }
        $row = $this->_getReminderInstallment($planId);
        if(empty($row))
        {
            if($this->_isAjaxReminderRequest())
            {
                return $this->_jsonResponse(array('success' => false, 'message' => __('Installment reminder record was not found or is already paid.')));
            }
            $this->Session->setFlash(__('Installment reminder record was not found or is already paid.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        $result = $this->_sendSmsReminderRow($row);
        if($this->_isAjaxReminderRequest())
        {
            return $this->_jsonResponse($result);
        }
        $this->Session->setFlash($result['message'], 'flash', array('alert' => !empty($result['success']) ? 'success' : 'danger'));
        return $this->redirect(array('action' => 'index'));
    }

    public function sendemail($planId = null)
    {
        if(!$this->request->is('post'))
        {
            throw new MethodNotAllowedException(__('Invalid request method.'));
        }
        $row = $this->_getReminderInstallment($planId);
        if(empty($row))
        {
            if($this->_isAjaxReminderRequest())
            {
                return $this->_jsonResponse(array('success' => false, 'message' => __('Installment reminder record was not found or is already paid.')));
            }
            $this->Session->setFlash(__('Installment reminder record was not found or is already paid.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        $result = $this->_sendEmailReminderRow($row);
        if($this->_isAjaxReminderRequest())
        {
            return $this->_jsonResponse($result);
        }
        $this->Session->setFlash($result['message'], 'flash', array('alert' => !empty($result['success']) ? 'success' : 'danger'));
        return $this->redirect(array('action' => 'index'));
    }

    public function whatsapp($planId = null)
    {
        $row = $this->_getReminderInstallment($planId);
        if(empty($row))
        {
            if($this->_isAjaxReminderRequest())
            {
                return $this->_jsonResponse(array('success' => false, 'message' => __('Installment reminder record was not found or is already paid.')));
            }
            $this->Session->setFlash(__('Installment reminder record was not found or is already paid.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        $result = $this->_sendWhatsappReminderRow($row);
        if($this->_isAjaxReminderRequest())
        {
            return $this->_jsonResponse($result);
        }
        $this->Session->setFlash($result['message'], 'flash', array('alert' => !empty($result['success']) ? 'success' : 'danger'));
        if(!empty($result['url']) && empty($result['api_sent']))
        {
            return $this->redirect($result['url']);
        }
        return $this->redirect(array('action' => 'index'));
    }

    public function bulk()
    {
        if(!$this->request->is('post'))
        {
            throw new MethodNotAllowedException(__('Invalid request method.'));
        }

        $data = !empty($this->request->data['PaymentReminder']) ? (array)$this->request->data['PaymentReminder'] : array();
        $channel = !empty($data['channel']) ? strtolower(trim((string)$data['channel'])) : '';
        if(!in_array($channel, array('sms', 'whatsapp', 'email'), true))
        {
            $this->Session->setFlash(__('Please choose SMS, WhatsApp, or Email for bulk sending.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }

        $planIds = !empty($data['plan_ids']) ? (array)$data['plan_ids'] : array();
        $cleanPlanIds = array();
        foreach($planIds as $planId)
        {
            $planId = (int)$planId;
            if($planId > 0)
            {
                $cleanPlanIds[$planId] = $planId;
            }
        }
        if(empty($cleanPlanIds))
        {
            $this->Session->setFlash(__('Please select at least one installment reminder.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }

        $sent = 0;
        $failed = 0;
        $skipped = 0;
        $errors = array();
        $whatsappLinks = array();
        $whatsappSettings = $channel === 'whatsapp' ? $this->_autoReminderSettings() : array();
        $whatsappApiMode = ($channel === 'whatsapp' && !empty($whatsappSettings['whatsapp_mode']) && $whatsappSettings['whatsapp_mode'] === 'api');

        foreach($cleanPlanIds as $planId)
        {
            $row = $this->_getReminderInstallment($planId);
            if(empty($row))
            {
                $skipped++;
                continue;
            }

            if($channel === 'sms')
            {
                $result = $this->_sendSmsReminderRow($row);
            }
            elseif($channel === 'email')
            {
                $result = $this->_sendEmailReminderRow($row);
            }
            else
            {
                if($whatsappApiMode)
                {
                    $result = $this->_sendWhatsappAutoReminderRow($row, $whatsappSettings);
                }
                else
                {
                    $result = $this->_buildWhatsappReminderLink($row);
                    if(!empty($result['success']))
                    {
                        $this->_saveReminderLog($row, 'whatsapp', 'opened', $result['body'], '');
                        $whatsappLinks[] = $result;
                    }
                }
            }

            if(!empty($result['success']))
            {
                $sent++;
            }
            else
            {
                $failed++;
                if(!empty($result['message']))
                {
                    $errors[] = $result['message'];
                }
            }
        }

        if($channel === 'whatsapp' && !$whatsappApiMode)
        {
            if(empty($whatsappLinks))
            {
                $this->Session->setFlash(__('No WhatsApp links could be prepared for the selected reminders.'), 'flash', array('alert' => 'danger'));
                return $this->redirect(array('action' => 'index'));
            }
            $returnUrl = $this->referer(array('action' => 'index'), true);
            $this->set(compact('whatsappLinks', 'sent', 'failed', 'skipped', 'errors', 'returnUrl'));
            return $this->render('bulkwhatsapp');
        }

        $flashMessage = sprintf(__('%s reminders sent. %s failed. %s skipped.'), (int)$sent, (int)$failed, (int)$skipped);
        if($channel === 'whatsapp' && $whatsappApiMode)
        {
            $flashMessage = sprintf(__('%s WhatsApp reminders sent by API. %s failed. %s skipped.'), (int)$sent, (int)$failed, (int)$skipped);
        }
        if(!empty($errors))
        {
            $flashMessage .= ' ' . implode(' ', array_slice(array_unique($errors), 0, 3));
        }
        $this->Session->setFlash($flashMessage, 'flash', array('alert' => ($failed > 0 ? 'warning' : 'success')));
        return $this->redirect(array('action' => 'index'));
    }

    public function settings()
    {
        $settings = $this->_autoReminderSettings();
        if($this->request->is(array('post', 'put')))
        {
            $data = !empty($this->request->data['PaymentReminderSetting']) ? (array)$this->request->data['PaymentReminderSetting'] : array();
            $settings = $this->PaymentReminderSetting->normalizeSettings($data);
            $this->PaymentReminderSetting->create();
            if($this->PaymentReminderSetting->save(array('PaymentReminderSetting' => $settings), array('validate' => false)))
            {
                $this->Session->setFlash(__('Auto reminder settings have been saved.'), 'flash', array('alert' => 'success'));
                return $this->redirect(array('action' => 'settings'));
            }
            $this->Session->setFlash(__('Auto reminder settings could not be saved.'), 'flash', array('alert' => 'danger'));
        }
        if(empty($this->request->data))
        {
            $this->request->data = array('PaymentReminderSetting' => $settings);
        }
        $schedulerCommand = 'D:\\xampp\\php\\php.exe D:\\xampp\\htdocs\\whitevillas\\lib\\Cake\\Console\\cake.php -app D:\\xampp\\htdocs\\whitevillas\\app payment_reminder run';
        $this->set(compact('settings', 'schedulerCommand'));
    }

    public function runauto()
    {
        if(!$this->request->is('post'))
        {
            throw new MethodNotAllowedException(__('Invalid request method.'));
        }
        $settings = $this->_autoReminderSettings();
        $result = $this->_processAutoReminders($settings, true);
        $flashMessage = sprintf(
            __('Auto reminder run complete. Sent: %s. Failed: %s. Skipped: %s.'),
            (int)$result['sent'],
            (int)$result['failed'],
            (int)$result['skipped']
        );
        if(!empty($result['errors']))
        {
            $flashMessage .= ' '.implode(' ', array_slice(array_unique($result['errors']), 0, 3));
        }
        $this->Session->setFlash($flashMessage, 'flash', array('alert' => ((int)$result['failed'] > 0 ? 'warning' : 'success')));
        return $this->redirect(array('action' => 'index'));
    }

    protected function _autoReminderSettings()
    {
        try
        {
            return (array)$this->PaymentReminderSetting->currentSettings();
        }
        catch(Exception $e)
        {
            return $this->PaymentReminderSetting->defaultSettings();
        }
    }

    protected function _activeAutoChannels($settings)
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

    protected function _processAutoReminders($settings, $manualRun = false)
    {
        $settings = $this->PaymentReminderSetting->normalizeSettings($settings);
        $result = array('sent' => 0, 'failed' => 0, 'skipped' => 0, 'errors' => array());
        if(empty($settings['auto_enabled']) && !$manualRun)
        {
            $result['skipped']++;
            $result['errors'][] = __('Auto reminders are disabled.');
            return $result;
        }
        $channels = $this->_activeAutoChannels($settings);
        if(empty($channels))
        {
            $result['skipped']++;
            $result['errors'][] = __('Please enable at least one auto reminder channel.');
            return $result;
        }

        $rows = $this->_autoReminderRows($settings);
        $limit = !empty($settings['daily_limit']) ? (int)$settings['daily_limit'] : 100;
        $attempts = 0;
        $limitReached = false;
        foreach($rows as $row)
        {
            foreach($channels as $channel)
            {
                if($attempts >= $limit)
                {
                    $limitReached = true;
                    break 2;
                }
                if($this->_recentReminderAlreadySent($row, $channel, $settings))
                {
                    $result['skipped']++;
                    continue;
                }
                $attempts++;
                if($channel === 'sms')
                {
                    $sendResult = $this->_sendSmsReminderRow($row);
                }
                elseif($channel === 'email')
                {
                    $sendResult = $this->_sendEmailReminderRow($row);
                }
                else
                {
                    $sendResult = $this->_sendWhatsappAutoReminderRow($row, $settings);
                }
                if(!empty($sendResult['success']))
                {
                    $result['sent']++;
                }
                else
                {
                    $result['failed']++;
                    if(!empty($sendResult['message']))
                    {
                        $result['errors'][] = $sendResult['message'];
                    }
                }
            }
        }
        if($limitReached)
        {
            $result['errors'][] = __('Daily send limit reached. Remaining reminders will continue on the next scheduled run.');
        }
        return $result;
    }

    protected function _autoReminderRows($settings)
    {
        $this->loadModel('Admin.PlansPayment');
        $today = CakeTime::format('Y-m-d', CakeTime::convert(time(), $this->siteTimezone));
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

        $permission = $this->_reminderPermissionCondition();
        if(!empty($permission))
        {
            $conditions[] = $permission;
        }
        $dealStatusCondition = $this->_dealStatusCondition();
        if(!empty($dealStatusCondition))
        {
            $conditions[] = $dealStatusCondition;
        }
        try
        {
            $rows = (array)$this->PlansPayment->find('all', array(
                'fields' => $this->_installmentFields(),
                'joins' => $this->_installmentJoins(),
                'conditions' => $conditions,
                'order' => array('PlansPayment.date' => 'asc', 'PlansPayment.id' => 'asc'),
                'recursive' => -1
            ));
        }
        catch(Exception $e)
        {
            return array();
        }
        return $this->_groupOverdueRowsByDeal($rows, $today);
    }

    protected function _paginatedReminderRows($conditions, $joins, $fields, $today)
    {
        $limit = $this->_reminderPaginatorLimit();

        $page = 1;
        if(!empty($this->request->query['page']))
        {
            $page = (int)$this->request->query['page'];
        }
        elseif(!empty($this->request->params['named']['page']))
        {
            $page = (int)$this->request->params['named']['page'];
        }
        if($page <= 0)
        {
            $page = 1;
        }

        try
        {
            $rawRows = (array)$this->PlansPayment->find('all', array(
                'fields' => $fields,
                'joins' => $joins,
                'conditions' => $conditions,
                'order' => array('PlansPayment.date' => 'asc', 'PlansPayment.id' => 'asc'),
                'recursive' => -1
            ));
        }
        catch(Exception $e)
        {
            $rawRows = array();
        }

        $groupedRows = $this->_groupOverdueRowsByDeal($rawRows, $today);
        $count = count($groupedRows);
        $pageCount = $count > 0 ? (int)ceil($count / $limit) : 1;
        if($page > $pageCount)
        {
            $page = $pageCount;
        }
        $offset = ($page - 1) * $limit;
        $rows = array_slice($groupedRows, $offset, $limit);

        $queryOptions = (array)$this->request->query;
        unset($queryOptions['page']);
        $this->request->params['paging']['PlansPayment'] = array(
            'page' => $page,
            'current' => count($rows),
            'count' => $count,
            'prevPage' => ($page > 1),
            'nextPage' => ($page < $pageCount),
            'pageCount' => $pageCount,
            'order' => array('PlansPayment.date' => 'asc', 'PlansPayment.id' => 'asc'),
            'limit' => $limit,
            'options' => array('?' => $queryOptions),
            'paramType' => 'querystring'
        );

        return $rows;
    }

    protected function _reminderPaginatorLimit()
    {
        $limit = 0;
        if(isset($this->request->query['limit']))
        {
            $limit = (int)$this->request->query['limit'];
        }
        elseif(isset($this->request->data['limit']))
        {
            $limit = (int)$this->request->data['limit'];
        }
        elseif(isset($this->request->params['named']['limit']))
        {
            $limit = (int)$this->request->params['named']['limit'];
        }
        elseif(isset($this->passedArgs['limit']))
        {
            $limit = (int)$this->passedArgs['limit'];
        }
        if($limit <= 0)
        {
            $limit = !empty($this->pageLimit) ? (int)$this->pageLimit : 30;
        }

        $maxLimit = max((int)$this->maxLimit, 500);
        if($maxLimit <= 0)
        {
            $maxLimit = 500;
        }
        $limit = max(1, min($limit, $maxLimit));
        $this->request->query['limit'] = $limit;
        return $limit;
    }

    protected function _recentReminderAlreadySent($row, $channel, $settings)
    {
        $repeatDays = isset($settings['repeat_after_days']) ? (int)$settings['repeat_after_days'] : 0;
        $conditions = $this->_successfulReminderLogConditions($row, $channel);
        if($repeatDays > 0)
        {
            $conditions['PaymentReminder.created >='] = date('Y-m-d H:i:s', strtotime('-'.$repeatDays.' days'));
        }
        try
        {
            return ((int)$this->PaymentReminder->find('count', array('conditions' => $conditions, 'recursive' => -1)) > 0);
        }
        catch(Exception $e)
        {
            return false;
        }
    }

    protected function _successfulReminderLogConditions($row, $channel)
    {
        $conditions = array(
            'PaymentReminder.channel' => $channel,
            'PaymentReminder.reminder_type' => $this->_reminderType($row),
            'PaymentReminder.status' => array('sent', 'opened', 'prepared')
        );
        if($this->_reminderType($row) === 'overdue' && !empty($row['Deal']['id']))
        {
            $conditions['PaymentReminder.deal_id'] = (int)$row['Deal']['id'];
        }
        else
        {
            $conditions['PaymentReminder.plans_payment_id'] = !empty($row['PlansPayment']['id']) ? (int)$row['PlansPayment']['id'] : 0;
        }
        return $conditions;
    }

    protected function _reminderPermissionCondition()
    {
        $userType = strtoupper(trim((string)$this->userType));
        if($userType === 'ADR')
        {
            return array();
        }
        if((int)$this->agencyId > 0)
        {
            return array('Property.project_id' => (int)$this->agencyId);
        }
        if((int)$this->luserId > 0)
        {
            return array('Property.user_id' => (int)$this->luserId);
        }
        return array();
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
        $fields = array(
            'PlansPayment.id', 'PlansPayment.deal_id', 'PlansPayment.name', 'PlansPayment.amount', 'PlansPayment.date',
            'Deal.id', 'Deal.invoice_no', 'Deal.total_amount', 'Deal.deal_status', 'Deal.user_id',
            'Client.id', 'Client.name', 'Client.phone', 'Client.mobile', 'Client.email',
            'Property.id', 'Property.name', 'Property.address', 'Property.project_id',
            'Project.id', 'Project.name', 'Project.phone', 'Project.mobile', 'Project.email'
        );
        if(isset($this->PlansPayment) && $this->PlansPayment->hasField('recovery_promise_date'))
        {
            $fields[] = 'PlansPayment.recovery_promise_date';
        }
        return $fields;
    }

    protected function _getReminderInstallment($planId)
    {
        $this->loadModel('Admin.PlansPayment');
        $planId = (int)$planId;
        if($planId <= 0)
        {
            return array();
        }
        $conditions = array(
            'PlansPayment.id' => $planId,
            'DealsPayment.id IS NULL'
        );
        $permission = $this->_reminderPermissionCondition();
        if(!empty($permission))
        {
            $conditions[] = $permission;
        }
        $dealStatusCondition = $this->_dealStatusCondition();
        if(!empty($dealStatusCondition))
        {
            $conditions[] = $dealStatusCondition;
        }
        return (array)$this->PlansPayment->find('first', array(
            'fields' => $this->_installmentFields(),
            'joins' => $this->_installmentJoins(),
            'conditions' => $conditions,
            'recursive' => -1
        ));
    }

    protected function _buildReminderSummary($conditions, $joins, $today)
    {
        $summary = array('total' => 0, 'due_soon' => 0, 'overdue' => 0, 'overdue_deals' => 0);
        try
        {
            $rows = $this->PlansPayment->find('all', array(
                'fields' => array('PlansPayment.id', 'PlansPayment.date', 'Deal.id'),
                'joins' => $joins,
                'conditions' => $conditions,
                'recursive' => -1
            ));
            $seenOverdueDeals = array();
            foreach($rows as $row)
            {
                $dueDate = !empty($row['PlansPayment']['date']) ? $row['PlansPayment']['date'] : '';
                $summary['total']++;
                if($dueDate !== '' && $dueDate < $today)
                {
                    $summary['overdue']++;
                    $dealId = !empty($row['Deal']['id']) ? (int)$row['Deal']['id'] : 0;
                    $overdueKey = $dealId > 0 ? 'deal-'.$dealId : 'plan-'.(int)$row['PlansPayment']['id'];
                    if(empty($seenOverdueDeals[$overdueKey]))
                    {
                        $seenOverdueDeals[$overdueKey] = true;
                        $summary['overdue_deals']++;
                    }
                }
                else
                {
                    $summary['due_soon']++;
                }
            }
        }
        catch(Exception $e)
        {
            // Dashboard counts are optional.
        }
        return $summary;
    }

    protected function _groupOverdueRowsByDeal($rows, $today)
    {
        $rows = (array)$rows;
        $output = array();
        $overdueGroups = array();
        foreach($rows as $row)
        {
            $dueDate = !empty($row['PlansPayment']['date']) ? $row['PlansPayment']['date'] : '';
            $dealId = !empty($row['Deal']['id']) ? (int)$row['Deal']['id'] : 0;
            if($dueDate !== '' && $dueDate < $today && $dealId > 0)
            {
                if(empty($overdueGroups[$dealId]))
                {
                    $row['_ReminderAggregate'] = array(
                        'is_overdue_group' => true,
                        'overdue_count' => 0,
                        'overdue_amount' => 0,
                        'plan_ids' => array()
                    );
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

        foreach($overdueGroups as $dealId => $row)
        {
            $count = !empty($row['_ReminderAggregate']['overdue_count']) ? (int)$row['_ReminderAggregate']['overdue_count'] : 1;
            $amount = !empty($row['_ReminderAggregate']['overdue_amount']) ? (float)$row['_ReminderAggregate']['overdue_amount'] : 0;
            $row['PlansPayment']['name'] = sprintf(__('Overdue Installments (%s)'), $count);
            $row['PlansPayment']['amount'] = $amount;
            $output[] = $row;
        }

        usort($output, array($this, '_sortReminderRowsByDueDate'));
        return $output;
    }

    protected function _promiseDateFilterCondition($promiseFilter, $promiseStartDate, $promiseEndDate, $today)
    {
        if(!$this->PlansPayment->hasField('recovery_promise_date'))
        {
            return array();
        }
        $promiseFilter = strtolower(trim((string)$promiseFilter));
        $startDate = trim((string)$promiseStartDate);
        $endDate = trim((string)$promiseEndDate);
        $todayTs = strtotime($today);

        if($promiseFilter === 'day')
        {
            $startDate = $today;
            $endDate = $today;
        }
        elseif($promiseFilter === 'week')
        {
            $startDate = date('Y-m-d', strtotime('monday this week', $todayTs));
            $endDate = date('Y-m-d', strtotime('sunday this week', $todayTs));
        }
        elseif($promiseFilter === 'month')
        {
            $startDate = date('Y-m-01', $todayTs);
            $endDate = date('Y-m-t', $todayTs);
        }
        elseif($promiseFilter === 'year')
        {
            $startDate = date('Y-01-01', $todayTs);
            $endDate = date('Y-12-31', $todayTs);
        }

        $conditions = array();
        if($startDate !== '')
        {
            $conditions['PlansPayment.recovery_promise_date >='] = $startDate;
        }
        if($endDate !== '')
        {
            $conditions['PlansPayment.recovery_promise_date <='] = $endDate;
        }
        if(!empty($conditions))
        {
            $conditions[] = 'PlansPayment.recovery_promise_date IS NOT NULL';
        }
        return $conditions;
    }

    protected function _normalizeReminderDate($value)
    {
        $value = trim((string)$value);
        if($value === '')
        {
            return '';
        }
        if(preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $value, $match))
        {
            $year = (int)$match[1];
            $month = (int)$match[2];
            $day = (int)$match[3];
            return checkdate($month, $day, $year) ? sprintf('%04d-%02d-%02d', $year, $month, $day) : false;
        }
        if(preg_match('/^(\d{1,2})[\/\-.](\d{1,2})[\/\-.](\d{2}|\d{4})$/', $value, $match))
        {
            $day = (int)$match[1];
            $month = (int)$match[2];
            $year = (int)$match[3];
            if($year < 100)
            {
                $year += 2000;
            }
            return checkdate($month, $day, $year) ? sprintf('%04d-%02d-%02d', $year, $month, $day) : false;
        }
        $timestamp = strtotime(str_replace('/', '-', $value));
        if($timestamp === false)
        {
            return false;
        }
        return date('Y-m-d', $timestamp);
    }

    protected function _formatReminderShortDate($date)
    {
        $date = trim((string)$date);
        if($date === '' || $date === '0000-00-00')
        {
            return '';
        }
        $timestamp = strtotime($date);
        if($timestamp === false)
        {
            return '';
        }
        return date('d-m-y', $timestamp);
    }

    protected function _jsonResponse($payload)
    {
        $this->response->type('json');
        $this->response->body(json_encode($payload));
        return $this->response;
    }

    protected function _isAjaxReminderRequest()
    {
        return ($this->request->is('ajax') || !empty($this->request->query['ajax']) || !empty($this->request->data['ajax']));
    }

    protected function _sortReminderRowsByDueDate($a, $b)
    {
        $dateA = !empty($a['PlansPayment']['date']) ? strtotime($a['PlansPayment']['date']) : PHP_INT_MAX;
        $dateB = !empty($b['PlansPayment']['date']) ? strtotime($b['PlansPayment']['date']) : PHP_INT_MAX;
        if($dateA === $dateB)
        {
            $idA = !empty($a['PlansPayment']['id']) ? (int)$a['PlansPayment']['id'] : 0;
            $idB = !empty($b['PlansPayment']['id']) ? (int)$b['PlansPayment']['id'] : 0;
            return $idA - $idB;
        }
        return $dateA < $dateB ? -1 : 1;
    }

    protected function _recentReminderHistory()
    {
        $conditions = $this->_reminderLogScopeConditions(false);
        try
        {
            $history = (array)$this->PaymentReminder->find('all', array(
                'conditions' => $conditions,
                'order' => array('PaymentReminder.id' => 'desc'),
                'limit' => 20,
                'recursive' => -1
            ));
            return $this->_normalizeReminderHistoryAmounts($history);
        }
        catch(Exception $e)
        {
            return array();
        }
    }

    protected function _reminderLogScopeConditions($forceNonEmpty = false)
    {
        $conditions = array();
        $userType = strtoupper(trim((string)$this->userType));
        if($userType === 'AGY' && (int)$this->agencyId > 0)
        {
            $conditions['PaymentReminder.project_id'] = (int)$this->agencyId;
        }
        if($forceNonEmpty && empty($conditions))
        {
            $conditions['PaymentReminder.id >'] = 0;
        }
        return $conditions;
    }

    protected function _canClearReminderLogs()
    {
        $userType = strtoupper(trim((string)$this->userType));
        if($this->_isSuperAdminSession((array)$this->adminValue) || $userType === 'ADR')
        {
            return true;
        }
        return ($userType === 'AGY' && (int)$this->agencyId > 0);
    }

    protected function _normalizeReminderHistoryAmounts($history)
    {
        foreach((array)$history as $key => $historyRow)
        {
            $log = !empty($historyRow['PaymentReminder']) ? (array)$historyRow['PaymentReminder'] : array();
            if(empty($log['deal_id']) || empty($log['reminder_type']) || $log['reminder_type'] !== 'overdue')
            {
                continue;
            }
            $summary = $this->_dealOverdueSummary(array(
                'Deal' => array('id' => (int)$log['deal_id']),
                'PlansPayment' => array('date' => !empty($log['due_date']) ? $log['due_date'] : null)
            ));
            if(!empty($summary['amount']))
            {
                $history[$key]['PaymentReminder']['amount'] = (float)$summary['amount'];
            }
        }
        return $history;
    }

    protected function _sendSmsReminderRow($row)
    {
        $mobileNo = $this->_clientMobile($row);
        $message = $this->_buildReminderMessage($row, 'sms');
        if($mobileNo === '')
        {
            $error = __('Client mobile number is missing.');
            $this->_saveReminderLog($row, 'sms', 'failed', $message, $error);
            return array('success' => false, 'message' => $error);
        }

        $projectId = !empty($row['Property']['project_id']) ? (int)$row['Property']['project_id'] : (!empty($row['Deal']['project_id']) ? (int)$row['Deal']['project_id'] : (!empty($this->agencyId) ? (int)$this->agencyId : 0));
        $userId = !empty($row['Deal']['user_id']) ? (int)$row['Deal']['user_id'] : (!empty($this->luserId) ? (int)$this->luserId : 0);
        $reminderType = $this->_reminderType($row);
        $eventKey = ($reminderType === 'overdue') ? 'installment_overdue' : 'installment_reminder';

        App::uses('LeadbotGatewayClient', 'Lib');
        $GatewayModel = ClassRegistry::init('Admin.LeadbotGatewaySetting');
        $EventModel   = ClassRegistry::init('Admin.LeadbotEventSetting');
        $LogModel     = ClassRegistry::init('Admin.LeadbotNotificationLog');

        $settings = $GatewayModel->getSettings($projectId, $userId, true);
        $eventConfig = $EventModel->getEventConfig($eventKey, $projectId, $userId);

        if(!empty($settings['api_key']))
        {
            // Leadbot CRM Gateway attached - Check ON/OFF Toggles!
            $smsMasterActive = !empty($settings['auto_send_sms']);
            $smsEventActive  = !empty($eventConfig['sms_enabled']);

            if(!$smsMasterActive)
            {
                $error = __('SMS messaging is toggled OFF in your LeadBot Gateway settings.');
                $this->_saveReminderLog($row, 'sms', 'failed', $message, $error);
                return array('success' => false, 'message' => $error);
            }
            if(!$smsEventActive)
            {
                $error = __('SMS notification toggle is OFF for this installment event (%s).', $eventKey);
                $this->_saveReminderLog($row, 'sms', 'failed', $message, $error);
                return array('success' => false, 'message' => $error);
            }

            // Toggles are ON -> Dispatch via Leadbot Android SMS Gateway
            $gatewayUrl = !empty($settings['gateway_url']) ? $settings['gateway_url'] : LeadbotGatewayClient::DEFAULT_LOCAL_URL;
            $apiKey     = $settings['api_key'];
            $smsDevId   = !empty($settings['selected_sms_device_id']) ? (int)$settings['selected_sms_device_id'] : null;
            $smsSimSlot = !empty($settings['selected_sms_sim_slot']) ? (int)$settings['selected_sms_sim_slot'] : 1;
            $desc       = !empty($smsDevId) ? ('Device #' . $smsDevId . ' (SIM ' . $smsSimSlot . ')') : 'Default SMS Device';
            if(!empty($settings['is_agent_specific'])) $desc = 'Agent Device - ' . $desc;

            $smsResult = LeadbotGatewayClient::sendSms($gatewayUrl, $apiKey, array(
                'recipient' => LeadbotGatewayClient::cleanPhone($mobileNo),
                'message'   => $message,
                'device_id' => $smsDevId,
                'sim_slot'  => $smsSimSlot,
                'agency_id' => $projectId
            ));

            $isSuccess = !empty($smsResult['success']);
            $statusStr = $isSuccess ? 'sent' : 'failed';
            $errStr    = !empty($smsResult['error']) ? $smsResult['error'] : '';

            $this->_saveReminderLog($row, 'sms', $statusStr, $message, $errStr);

            $LogModel->recordLog(array(
                'project_id'                => $projectId,
                'user_id'                   => $userId > 0 ? $userId : null,
                'channel'                   => 'sms',
                'event_key'                 => $eventKey,
                'recipient_phone'           => LeadbotGatewayClient::cleanPhone($mobileNo),
                'recipient_name'            => !empty($row['Client']['name']) ? $row['Client']['name'] : 'Client',
                'message'                   => $message,
                'gateway_profile_or_device' => $desc,
                'status'                    => $isSuccess ? 'queued' : 'failed',
                'response_code'             => isset($smsResult['latency_ms']) ? $smsResult['latency_ms'] : 0,
                'response_payload'          => isset($smsResult['raw_response']) ? $smsResult['raw_response'] : '',
                'error_message'             => $errStr,
                'is_test'                   => 0
            ));

            if($isSuccess)
            {
                return array('success' => true, 'message' => __('SMS reminder queued on Android Gateway (%s).', $desc));
            }
            return array('success' => false, 'message' => $errStr !== '' ? $errStr : __('SMS dispatch failed.'));
        }

        // Legacy Fallback
        if(!$this->smsNotification)
        {
            $error = __('SMS notification is disabled.');
            $this->_saveReminderLog($row, 'sms', 'failed', $message, $error);
            return array('success' => false, 'message' => $error);
        }
        try
        {
            $this->CustomFunction->sendSms($mobileNo, $message, $this->smsSettingArr);
            $this->_saveReminderLog($row, 'sms', 'sent', $message, '');
            return array('success' => true, 'message' => __('SMS reminder has been sent.'));
        }
        catch(Exception $e)
        {
            $this->_saveReminderLog($row, 'sms', 'failed', $message, $e->getMessage());
            return array('success' => false, 'message' => $e->getMessage());
        }
    }

    protected function _sendWhatsappReminderRow($row)
    {
        $mobileNo = $this->_clientMobile($row);
        $message = $this->_buildReminderMessage($row, 'whatsapp');
        $whatsappNo = $this->_whatsappNumber($mobileNo);
        if($whatsappNo === '')
        {
            $error = __('Client WhatsApp number is missing.');
            $this->_saveReminderLog($row, 'whatsapp', 'failed', $message, $error);
            return array('success' => false, 'message' => $error);
        }

        $projectId = !empty($row['Property']['project_id']) ? (int)$row['Property']['project_id'] : (!empty($row['Deal']['project_id']) ? (int)$row['Deal']['project_id'] : (!empty($this->agencyId) ? (int)$this->agencyId : 0));
        $userId = !empty($row['Deal']['user_id']) ? (int)$row['Deal']['user_id'] : (!empty($this->luserId) ? (int)$this->luserId : 0);
        $reminderType = $this->_reminderType($row);
        $eventKey = ($reminderType === 'overdue') ? 'installment_overdue' : 'installment_reminder';

        App::uses('LeadbotGatewayClient', 'Lib');
        $GatewayModel = ClassRegistry::init('Admin.LeadbotGatewaySetting');
        $EventModel   = ClassRegistry::init('Admin.LeadbotEventSetting');
        $LogModel     = ClassRegistry::init('Admin.LeadbotNotificationLog');

        $settings = $GatewayModel->getSettings($projectId, $userId, true);
        $eventConfig = $EventModel->getEventConfig($eventKey, $projectId, $userId);

        if(!empty($settings['api_key']))
        {
            // Leadbot CRM Gateway is attached - Check ON/OFF Toggles!
            $waMasterActive = !empty($settings['auto_send_whatsapp']);
            $waEventActive  = !empty($eventConfig['whatsapp_enabled']);

            if(!$waMasterActive)
            {
                $error = __('WhatsApp messaging is toggled OFF in your LeadBot Gateway settings.');
                $this->_saveReminderLog($row, 'whatsapp', 'failed', $message, $error);
                return array('success' => false, 'message' => $error);
            }
            if(!$waEventActive)
            {
                $error = __('WhatsApp notification toggle is OFF for this installment event (%s).', $eventKey);
                $this->_saveReminderLog($row, 'whatsapp', 'failed', $message, $error);
                return array('success' => false, 'message' => $error);
            }

            // Toggles are ON -> Dispatch directly via Leadbot WhatsApp API
            $gatewayUrl = !empty($settings['gateway_url']) ? $settings['gateway_url'] : LeadbotGatewayClient::DEFAULT_LOCAL_URL;
            $apiKey     = $settings['api_key'];
            $waSession  = !empty($settings['selected_wa_session']) ? $settings['selected_wa_session'] : '';
            $desc       = !empty($waSession) ? $waSession : 'Default WhatsApp Profile';
            if(!empty($settings['is_agent_specific'])) $desc = 'Agent Key - ' . $desc;

            $waResult = LeadbotGatewayClient::sendWhatsApp($gatewayUrl, $apiKey, array(
                'recipient' => $whatsappNo,
                'message'   => $message,
                'session'   => $waSession,
                'agency_id' => $projectId
            ));

            $isSuccess = !empty($waResult['success']);
            $statusStr = $isSuccess ? 'sent' : 'failed';
            $errStr    = !empty($waResult['error']) ? $waResult['error'] : '';

            $this->_saveReminderLog($row, 'whatsapp', $statusStr, $message, $errStr);

            $LogModel->recordLog(array(
                'project_id'                => $projectId,
                'user_id'                   => $userId > 0 ? $userId : null,
                'channel'                   => 'whatsapp',
                'event_key'                 => $eventKey,
                'recipient_phone'           => $whatsappNo,
                'recipient_name'            => !empty($row['Client']['name']) ? $row['Client']['name'] : 'Client',
                'message'                   => $message,
                'gateway_profile_or_device' => $desc,
                'status'                    => $statusStr,
                'response_code'             => isset($waResult['latency_ms']) ? $waResult['latency_ms'] : 0,
                'response_payload'          => isset($waResult['raw_response']) ? $waResult['raw_response'] : '',
                'error_message'             => $errStr,
                'is_test'                   => 0
            ));

            if($isSuccess)
            {
                return array(
                    'success'  => true,
                    'api_sent' => true,
                    'message'  => __('WhatsApp reminder sent instantly via LeadBot profile (%s).', $desc),
                    'url'      => 'https://wa.me/'.$whatsappNo.'?text='.rawurlencode($message)
                );
            }
            return array('success' => false, 'message' => $errStr !== '' ? $errStr : __('WhatsApp delivery failed.'));
        }

        // Fallback: If LeadBot Gateway API key is not configured, generate wa.me link
        $result = $this->_buildWhatsappReminderLink($row);
        if(!empty($result['success']))
        {
            $this->_saveReminderLog($row, 'whatsapp', 'opened', $result['body'], '');
        }
        return $result;
    }

    protected function _sendEmailReminderRow($row)
    {
        $email = !empty($row['Client']['email']) ? trim((string)$row['Client']['email']) : '';
        $message = $this->_buildReminderMessage($row, 'email');
        $emailFormat = $this->_reminderMessageLooksHtml($message) ? 'html' : 'text';
        if($email === '')
        {
            $error = __('Client email is missing.');
            $this->_saveReminderLog($row, 'email', 'failed', $message, $error);
            return array('success' => false, 'message' => $error);
        }
        if(!$this->emailNotification)
        {
            $error = __('Email notification is disabled.');
            $this->_saveReminderLog($row, 'email', 'failed', $message, $error);
            return array('success' => false, 'message' => $error);
        }
        try
        {
            $Email = new CakeEmail();
            $Email->transport($this->emailSettype);
            if(strtolower((string)$this->emailSettype) === 'smtp')
            {
                $Email->config(array(
                    'host' => $this->emailHost,
                    'port' => $this->emailPort,
                    'username' => $this->emailUsername,
                    'password' => $this->emailPassword,
                    'timeout' => 90
                ));
            }
            $Email->from(array($this->siteEmail => $this->siteName));
            $Email->to($email);
            $Email->subject($this->_buildReminderSubject($row));
            $Email->emailFormat($emailFormat);
            $Email->send($message);
            $this->_saveReminderLog($row, 'email', 'sent', $message, '');
            return array('success' => true, 'message' => __('Email reminder has been sent.'));
        }
        catch(Exception $e)
        {
            $this->_saveReminderLog($row, 'email', 'failed', $message, $e->getMessage());
            return array('success' => false, 'message' => $e->getMessage());
        }
    }

    protected function _reminderMessageLooksHtml($message)
    {
        return preg_match('/<(html|body|table|div|p|br|h[1-6]|span|strong|img|a)\b/i', (string)$message) === 1;
    }

    protected function _buildWhatsappReminderLink($row)
    {
        $mobileNo = $this->_clientMobile($row);
        $message = $this->_buildReminderMessage($row, 'whatsapp');
        $whatsappNo = $this->_whatsappNumber($mobileNo);
        if($whatsappNo === '')
        {
            $error = __('Client WhatsApp number is missing.');
            $this->_saveReminderLog($row, 'whatsapp', 'failed', $message, $error);
            return array('success' => false, 'message' => $error);
        }

        return array(
            'success' => true,
            'message' => __('WhatsApp reminder is ready.'),
            'client_name' => !empty($row['Client']['name']) ? $row['Client']['name'] : __('Customer'),
            'membership_no' => !empty($row['Deal']['invoice_no']) ? $row['Deal']['invoice_no'] : '-',
            'mobile' => $mobileNo,
            'body' => $message,
            'url' => 'https://wa.me/'.$whatsappNo.'?text='.rawurlencode($message)
        );
    }

    protected function _sendWhatsappAutoReminderRow($row, $settings)
    {
        $mobileNo = $this->_clientMobile($row);
        $message = $this->_buildReminderMessage($row, 'whatsapp');
        $whatsappNo = $this->_whatsappNumber($mobileNo);
        if($whatsappNo === '')
        {
            $error = __('Client WhatsApp number is missing.');
            $this->_saveReminderLog($row, 'whatsapp', 'failed', $message, $error);
            return array('success' => false, 'message' => $error);
        }
        if(empty($settings['whatsapp_mode']) || $settings['whatsapp_mode'] !== 'api')
        {
            $error = __('WhatsApp auto send needs API gateway settings.');
            $this->_saveReminderLog($row, 'whatsapp', 'failed', $message, $error);
            return array('success' => false, 'message' => $error);
        }
        try
        {
            $this->_sendWhatsappGateway($whatsappNo, $message, $settings);
            $this->_saveReminderLog($row, 'whatsapp', 'sent', $message, '');
            return array('success' => true, 'message' => __('WhatsApp reminder has been sent.'));
        }
        catch(Exception $e)
        {
            $this->_saveReminderLog($row, 'whatsapp', 'failed', $message, $e->getMessage());
            return array('success' => false, 'message' => $e->getMessage());
        }
    }

    protected function _sendWhatsappGateway($mobileNo, $message, $settings)
    {
        $url = !empty($settings['whatsapp_api']) ? trim((string)$settings['whatsapp_api']) : '';
        if($url === '')
        {
            throw new Exception(__('WhatsApp API URL is missing.'));
        }
        $parsedUrl = parse_url($url);
        $host = !empty($parsedUrl['host']) ? $parsedUrl['host'] : '';
        if($host === '')
        {
            throw new Exception(__('WhatsApp API URL is invalid.'));
        }
        if(!filter_var($host, FILTER_VALIDATE_IP) && gethostbyname($host) === $host)
        {
            throw new Exception(sprintf(__('WhatsApp gateway host could not be resolved: %s'), $host));
        }

        $query = array(
            $this->_gatewayParamName($settings, 'whatsapp_mobile_param', 'number') => (string)$mobileNo,
            $this->_gatewayParamName($settings, 'whatsapp_message_param', 'message') => (string)$message
        );
        $query = array_merge($query, $this->_parseExtraGatewayParams(!empty($settings['whatsapp_extra_params']) ? $settings['whatsapp_extra_params'] : ''));
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
        try
        {
            if(strtolower(trim((string)(isset($settings['whatsapp_type']) ? $settings['whatsapp_type'] : 'Post'))) === 'get')
            {
                $response = $socket->get($url, $query, $request);
            }
            else
            {
                $response = $socket->post($url, $query, $request);
            }
        }
        catch(Exception $e)
        {
            $error = $e->getMessage();
            if(stripos($error, 'getaddrinfo') !== false || stripos($error, 'php_network_getaddresses') !== false)
            {
                $error = sprintf(__('WhatsApp gateway host could not be resolved: %s'), $host);
            }
            throw new Exception($error);
        }
        if($response === false)
        {
            throw new Exception(__('WhatsApp gateway did not return a response.'));
        }
        if(isset($response->code) && (int)$response->code >= 400)
        {
            throw new Exception(sprintf(__('WhatsApp gateway returned HTTP %s.'), (int)$response->code));
        }
        return $response;
    }

    protected function _gatewayParamName($settings, $key, $fallback)
    {
        $name = isset($settings[$key]) ? trim((string)$settings[$key]) : '';
        return $name !== '' ? $name : $fallback;
    }

    protected function _parseExtraGatewayParams($text)
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

    protected function _buildReminderSubject($row)
    {
        $invoiceNo = !empty($row['Deal']['invoice_no']) ? $row['Deal']['invoice_no'] : '-';
        if($this->_reminderType($row) === 'overdue')
        {
            return __('Overdue Payment Reminder').' - '.$invoiceNo;
        }
        return __('Installment Payment Reminder').' - '.$invoiceNo;
    }

    protected function _buildReminderMessage($row, $channel = 'sms')
    {
        $context = $this->_reminderMessageContext($row);
        $channel = strtolower(trim((string)$channel));
        if($channel === 'whatsapp')
        {
            $templateMessage = $this->_renderReminderSmsTemplate(!empty($context['is_overdue']) ? 'PRW' : 'PDW', $context);
            return $templateMessage !== '' ? $templateMessage : $this->_buildWhatsappMessage($context);
        }
        if($channel === 'email')
        {
            $templateMessage = $this->_renderReminderEmailTemplate(!empty($context['is_overdue']) ? 'PRE' : 'PDE', $context);
            return $templateMessage !== '' ? $templateMessage : $this->_buildEmailMessage($context);
        }
        $templateMessage = $this->_renderReminderSmsTemplate(!empty($context['is_overdue']) ? 'PRS' : 'PDS', $context);
        return $templateMessage !== '' ? $templateMessage : $this->_buildSmsMessage($context);
    }

    protected function _renderReminderSmsTemplate($type, $context)
    {
        $type = strtoupper(trim((string)$type));
        if($type === '')
        {
            return '';
        }
        try
        {
            $this->loadModel('Admin.Smstemplate');
            $template = (array)$this->Smstemplate->find('first', array(
                'fields' => array('Smstemplate.id', 'Smstemplate.description'),
                'conditions' => array(
                    'Smstemplate.type' => $type,
                    'Smstemplate.status' => 'Published'
                ),
                'recursive' => -1
            ));
        }
        catch(Exception $e)
        {
            return '';
        }
        if(empty($template['Smstemplate']['description']))
        {
            return '';
        }
        return trim($this->_replaceReminderTemplatePlaceholders($template['Smstemplate']['description'], $context));
    }

    protected function _renderReminderEmailTemplate($type, $context)
    {
        $type = strtoupper(trim((string)$type));
        if($type === '')
        {
            return '';
        }
        try
        {
            $this->loadModel('Admin.Emailtemplate');
            $template = (array)$this->Emailtemplate->find('first', array(
                'fields' => array('Emailtemplate.id', 'Emailtemplate.description'),
                'conditions' => array(
                    'Emailtemplate.type' => $type,
                    'Emailtemplate.status' => 'Published'
                ),
                'recursive' => -1
            ));
        }
        catch(Exception $e)
        {
            return '';
        }
        if(empty($template['Emailtemplate']['description']))
        {
            return '';
        }
        return trim($this->_replaceReminderTemplatePlaceholders($template['Emailtemplate']['description'], $context));
    }

    protected function _replaceReminderTemplatePlaceholders($template, $context)
    {
        $today = CakeTime::format($this->sysDay.$this->dateSep.$this->sysMonth.$this->dateSep.$this->sysYear, CakeTime::convert(time(), $this->siteTimezone));
        $oldestOverdueAgeLabel = ((int)$context['overdue_age'] > 0) ? ((int)$context['overdue_age']).' days' : '0 days';
        $replacements = array(
            '$clientName' => $context['client_name'],
            '$memberName' => $context['client_name'],
            '$name' => $context['client_name'],
            '$invoiceNo' => $context['invoice_no'],
            '$membershipNo' => $context['invoice_no'],
            '$propertyName' => $context['property_name'],
            '$installment' => $context['installment'],
            '$installmentStatus' => $context['installment'],
            '$amount' => $context['amount'],
            '$installmentAmount' => $context['amount'],
            '$dueDate' => $context['due_date'],
            '$originalDueDate' => $context['original_due_date'],
            '$overdueInstallmentCount' => (string)$context['overdue_count'],
            '$totalOverdueInstallmentCount' => (string)$context['overdue_count'],
            '$totalOverdueAmount' => $context['overdue_amount'],
            '$totalOverdue' => $context['overdue_amount'],
            '$overdueAge' => (string)$context['overdue_age'],
            '$agencyName' => $context['agency_name'],
            '$projectName' => $context['agency_name'],
            '$agencyPhone' => $context['agency_phone'],
            '$contactNo' => $context['agency_phone'],
            '$bankAccounts' => $context['bank_accounts'],
            '$paymentAccounts' => $context['bank_accounts'],
            '$primaryBankAccount' => $context['primary_bank_account'],
            '$bankName' => $context['bank_name'],
            '$accountTitle' => $context['account_title'],
            '$accountNo' => $context['account_no'],
            '$iban' => $context['iban'],
            '$bankIban' => $context['iban'],
            '$bankBranch' => $context['bank_branch'],
            '$swiftCode' => $context['swift_code'],
            '$agencyLogoUrl' => $context['agency_logo_url'],
            '$agencyTagline' => $context['agency_tagline'],
            '$agencyAddress' => $context['agency_address'],
            '$agencyEmail' => $context['agency_email'],
            '$agencyUan' => $context['agency_uan'],
            '$agencyWebsite' => $context['agency_website'],
            '$siteName' => $this->siteName,
            '$currency' => trim((string)$this->currency),
            '$date' => $today,
            '$url' => Router::url('/', true)
        );
        $replacements += array(
            '{{CLIENT_NAME}}' => $context['client_name'],
            '{{MEMBERSHIP_NO}}' => $context['invoice_no'],
            '{{INVOICE_NO}}' => $context['invoice_no'],
            '{{PROPERTY_FILE_NO}}' => $context['property_name'],
            '{{PROPERTY_NAME}}' => $context['property_name'],
            '{{INSTALLMENT_STATUS}}' => $context['installment'],
            '{{DUE_DATE}}' => $context['due_date'],
            '{{ORIGINAL_DUE_DATE}}' => $context['original_due_date'],
            '{{INSTALLMENT_AMOUNT}}' => $context['amount'],
            '{{AMOUNT}}' => $context['amount'],
            '{{TOTAL_OVERDUE_INSTALLMENTS}}' => (string)$context['overdue_count'],
            '{{TOTAL_OVERDUE_AMOUNT}}' => $context['overdue_amount'],
            '{{OLDEST_OVERDUE_AGE}}' => $oldestOverdueAgeLabel,
            '{{AGENCY_NAME}}' => $context['agency_name'],
            '{{PROJECT_NAME}}' => $context['agency_name'],
            '{{AGENCY_LOGO_URL}}' => $context['agency_logo_url'],
            '{{AGENCY_TAGLINE}}' => $context['agency_tagline'],
            '{{AGENCY_ADDRESS}}' => $context['agency_address'],
            '{{AGENCY_CONTACT_NO}}' => $context['agency_phone'],
            '{{AGENCY_PHONE}}' => $context['agency_phone'],
            '{{AGENCY_EMAIL}}' => $context['agency_email'],
            '{{AGENCY_UAN}}' => $context['agency_uan'],
            '{{AGENCY_WEBSITE}}' => $context['agency_website'],
            '{{BANK_ACCOUNTS}}' => $context['bank_accounts'],
            '{{PRIMARY_BANK_ACCOUNT}}' => $context['primary_bank_account'],
            '{{BANK_NAME}}' => $context['bank_name'],
            '{{ACCOUNT_TITLE}}' => $context['account_title'],
            '{{ACCOUNT_NO}}' => $context['account_no'],
            '{{IBAN}}' => $context['iban'],
            '{{BANK_BRANCH}}' => $context['bank_branch'],
            '{{SWIFT_CODE}}' => $context['swift_code'],
            '{{client_name}}' => $context['client_name'],
            '{{invoice_no}}' => $context['invoice_no'],
            '{{membership_no}}' => $context['invoice_no'],
            '{{property_name}}' => $context['property_name'],
            '{{property_file_no}}' => $context['property_name'],
            '{{installment_status}}' => $context['installment'],
            '{{amount}}' => $context['amount'],
            '{{installment_amount}}' => $context['amount'],
            '{{due_date}}' => $context['due_date'],
            '{{original_due_date}}' => $context['original_due_date'],
            '{{total_overdue_amount}}' => $context['overdue_amount'],
            '{{total_overdue_installments}}' => (string)$context['overdue_count'],
            '{{overdue_count}}' => (string)$context['overdue_count'],
            '{{oldest_overdue_age}}' => $oldestOverdueAgeLabel,
            '{{overdue_age}}' => (string)$context['overdue_age'],
            '{{agency_name}}' => $context['agency_name'],
            '{{project_name}}' => $context['agency_name'],
            '{{agency_logo_url}}' => $context['agency_logo_url'],
            '{{agency_tagline}}' => $context['agency_tagline'],
            '{{agency_address}}' => $context['agency_address'],
            '{{contact_no}}' => $context['agency_phone'],
            '{{agency_contact_no}}' => $context['agency_phone'],
            '{{agency_phone}}' => $context['agency_phone'],
            '{{agency_email}}' => $context['agency_email'],
            '{{agency_uan}}' => $context['agency_uan'],
            '{{agency_website}}' => $context['agency_website'],
            '{{bank_accounts}}' => $context['bank_accounts'],
            '{{payment_accounts}}' => $context['bank_accounts'],
            '{{primary_bank_account}}' => $context['primary_bank_account'],
            '{{bank_name}}' => $context['bank_name'],
            '{{account_title}}' => $context['account_title'],
            '{{account_no}}' => $context['account_no'],
            '{{iban}}' => $context['iban'],
            '{{bank_iban}}' => $context['iban'],
            '{{bank_branch}}' => $context['bank_branch'],
            '{{swift_code}}' => $context['swift_code']
        );
        uksort($replacements, function($a, $b) {
            return strlen($b) - strlen($a);
        });
        return strtr((string)$template, $replacements);
    }

    protected function _reminderMessageContext($row)
    {
        $overdueSummary = $this->_dealOverdueSummary($row);
        $messageDueDateRaw = $this->_reminderSendDueDateRaw();
        $isOverdue = ($this->_reminderType($row) === 'overdue');
        $overdueCount = !empty($overdueSummary['count']) ? (int)$overdueSummary['count'] : 0;
        $overdueAmountValue = !empty($overdueSummary['amount']) ? (float)$overdueSummary['amount'] : 0;
        $installmentName = !empty($row['PlansPayment']['name']) ? trim((string)$row['PlansPayment']['name']) : __('Installment');
        $installmentAmount = !empty($row['PlansPayment']['amount']) ? (float)$row['PlansPayment']['amount'] : 0;
        if($isOverdue && $overdueCount > 0)
        {
            $installmentName = sprintf(__('Overdue Installments (%s)'), $overdueCount);
            $installmentAmount = $overdueAmountValue;
        }
        $bankAccounts = $this->_agencyPaymentBankAccounts($row);
        $primaryBankAccount = $this->_primaryPaymentBankAccount($bankAccounts);
        $agencyProfile = $this->_agencyReminderProfile($row);
        return array(
            'client_name' => !empty($row['Client']['name']) ? trim((string)$row['Client']['name']) : __('Customer'),
            'invoice_no' => !empty($row['Deal']['invoice_no']) ? trim((string)$row['Deal']['invoice_no']) : '-',
            'property_name' => !empty($row['Property']['name']) ? trim((string)$row['Property']['name']) : '-',
            'installment' => $installmentName,
            'amount' => $this->currency.CakeNumber::format($installmentAmount),
            'due_date' => CakeTime::format($this->sysDay.$this->dateSep.$this->sysMonth.$this->dateSep.$this->sysYear, $messageDueDateRaw),
            'original_due_date' => !empty($row['PlansPayment']['date']) ? CakeTime::format($this->sysDay.$this->dateSep.$this->sysMonth.$this->dateSep.$this->sysYear, $row['PlansPayment']['date']) : '-',
            'agency_name' => $agencyProfile['name'],
            'agency_phone' => $agencyProfile['phone'],
            'agency_logo_url' => $agencyProfile['logo_url'],
            'agency_tagline' => $agencyProfile['tagline'],
            'agency_address' => $agencyProfile['address'],
            'agency_email' => $agencyProfile['email'],
            'agency_uan' => $agencyProfile['uan'],
            'agency_website' => $agencyProfile['website'],
            'is_overdue' => $isOverdue,
            'overdue_count' => $overdueCount,
            'overdue_amount' => $this->currency.CakeNumber::format($overdueAmountValue),
            'overdue_age' => !empty($overdueSummary['age']) ? (int)$overdueSummary['age'] : 0,
            'bank_accounts' => $this->_formatPaymentBankAccounts($bankAccounts),
            'primary_bank_account' => $this->_formatPaymentBankAccounts(array($primaryBankAccount)),
            'bank_name' => !empty($primaryBankAccount['bank_name']) ? trim((string)$primaryBankAccount['bank_name']) : '',
            'account_title' => !empty($primaryBankAccount['account_title']) ? trim((string)$primaryBankAccount['account_title']) : '',
            'account_no' => !empty($primaryBankAccount['account_no']) ? trim((string)$primaryBankAccount['account_no']) : '',
            'iban' => !empty($primaryBankAccount['iban']) ? trim((string)$primaryBankAccount['iban']) : '',
            'bank_branch' => !empty($primaryBankAccount['branch']) ? trim((string)$primaryBankAccount['branch']) : '',
            'swift_code' => !empty($primaryBankAccount['swift_code']) ? trim((string)$primaryBankAccount['swift_code']) : ''
        );
    }

    protected function _agencyPaymentBankAccounts($row)
    {
        $projectId = !empty($row['Property']['project_id']) ? (int)$row['Property']['project_id'] : (!empty($row['Project']['id']) ? (int)$row['Project']['id'] : 0);
        if($projectId <= 0)
        {
            return array();
        }
        try
        {
            $this->loadModel('Admin.Ardpaymentbankaccount');
            $this->Ardpaymentbankaccount->ensureSchema();
            return $this->Ardpaymentbankaccount->activeRowsForProject($projectId);
        }
        catch(Exception $e)
        {
            return array();
        }
    }

    protected function _primaryPaymentBankAccount($bankAccounts)
    {
        foreach((array)$bankAccounts as $row)
        {
            $data = !empty($row['Ardpaymentbankaccount']) ? (array)$row['Ardpaymentbankaccount'] : (array)$row;
            if(!empty($data['is_primary']))
            {
                return $data;
            }
        }
        foreach((array)$bankAccounts as $row)
        {
            return !empty($row['Ardpaymentbankaccount']) ? (array)$row['Ardpaymentbankaccount'] : (array)$row;
        }
        return array();
    }

    protected function _formatPaymentBankAccounts($bankAccounts)
    {
        $blocks = array();
        foreach((array)$bankAccounts as $row)
        {
            $data = !empty($row['Ardpaymentbankaccount']) ? (array)$row['Ardpaymentbankaccount'] : (array)$row;
            $lines = array();
            if(!empty($data['bank_name']))
            {
                $lines[] = 'Bank: '.trim((string)$data['bank_name']);
            }
            if(!empty($data['account_title']))
            {
                $lines[] = 'Account Title: '.trim((string)$data['account_title']);
            }
            if(!empty($data['account_no']))
            {
                $lines[] = 'Account No: '.trim((string)$data['account_no']);
            }
            if(!empty($data['iban']))
            {
                $lines[] = 'IBAN: '.trim((string)$data['iban']);
            }
            if(!empty($data['branch']))
            {
                $lines[] = 'Branch: '.trim((string)$data['branch']);
            }
            if(!empty($data['swift_code']))
            {
                $lines[] = 'SWIFT: '.trim((string)$data['swift_code']);
            }
            if(!empty($data['instructions']))
            {
                $lines[] = 'Note: '.trim((string)$data['instructions']);
            }
            if(!empty($lines))
            {
                $blocks[] = implode("\n", $lines);
            }
        }
        return implode("\n\n", $blocks);
    }

    protected function _agencyReminderProfile($row)
    {
        $projectId = !empty($row['Project']['id']) ? (int)$row['Project']['id'] : (!empty($row['Property']['project_id']) ? (int)$row['Property']['project_id'] : 0);
        $project = !empty($row['Project']) ? (array)$row['Project'] : array();
        $agencyUserInfo = $this->_agencyUserInfo($projectId);
        $config = $this->_reminderSiteConfiguration();

        $agencyName = !empty($project['name']) ? trim((string)$project['name']) : '';
        if($agencyName === '' && !empty($agencyUserInfo['name']))
        {
            $agencyName = trim((string)$agencyUserInfo['name']);
        }
        if($agencyName === '' && !empty($config['organization_name']))
        {
            $agencyName = trim((string)$config['organization_name']);
        }
        if($agencyName === '')
        {
            $agencyName = !empty($this->siteName) ? $this->siteName : 'White Villas';
        }

        $agencyAddress = !empty($agencyUserInfo['address']) ? trim((string)$agencyUserInfo['address']) : '';
        if($agencyAddress === '' && !empty($config['address']))
        {
            $agencyAddress = trim((string)$config['address']);
        }

        $agencyEmail = !empty($project['email']) ? trim((string)$project['email']) : '';
        if($agencyEmail === '' && !empty($agencyUserInfo['email']))
        {
            $agencyEmail = trim((string)$agencyUserInfo['email']);
        }
        if($agencyEmail === '' && !empty($config['email']))
        {
            $agencyEmail = trim((string)$config['email']);
        }
        if($agencyEmail === '')
        {
            $agencyEmail = !empty($this->siteEmail) ? $this->siteEmail : '';
        }

        $tagline = !empty($config['meta_desc']) ? trim((string)$config['meta_desc']) : '';
        if($tagline === '' && !empty($config['meta_title']))
        {
            $tagline = trim((string)$config['meta_title']);
        }

        $website = !empty($config['domain_name']) ? trim((string)$config['domain_name']) : '';
        if($website === '' && !empty($this->siteDomain))
        {
            $website = trim((string)$this->siteDomain);
        }
        if($website === '')
        {
            $website = Router::url('/', true);
        }

        $logoUrl = $this->_reminderImageUrlFromCandidates(array(
            !empty($agencyUserInfo['photo']) ? 'user_thumb/'.$agencyUserInfo['photo'] : '',
            !empty($agencyUserInfo['photo']) ? 'user/'.$agencyUserInfo['photo'] : '',
            !empty($agencyUserInfo['photo']) ? $agencyUserInfo['photo'] : '',
            !empty($config['photo']) ? $config['photo'] : '',
            'logo-website.fw.png'
        ));

        $phone = $this->_agencyReminderPhone($row);
        $uan = !empty($config['contact']) ? trim((string)$config['contact']) : $phone;

        return array(
            'name' => $agencyName,
            'phone' => $phone,
            'logo_url' => $logoUrl,
            'tagline' => $tagline,
            'address' => $agencyAddress,
            'email' => $agencyEmail,
            'uan' => $uan,
            'website' => $website
        );
    }

    protected function _reminderSiteConfiguration()
    {
        if($this->_reminderSiteConfigCache !== null)
        {
            return $this->_reminderSiteConfigCache;
        }
        try
        {
            $this->loadModel('Configuration');
            $row = (array)$this->Configuration->find('first', array('recursive' => -1));
            $this->_reminderSiteConfigCache = !empty($row['Configuration']) ? (array)$row['Configuration'] : array();
        }
        catch(Exception $e)
        {
            $this->_reminderSiteConfigCache = array();
        }
        return $this->_reminderSiteConfigCache;
    }

    protected function _reminderImageUrlFromCandidates($candidates)
    {
        foreach((array)$candidates as $candidate)
        {
            $candidate = trim(str_replace('\\', '/', (string)$candidate));
            if($candidate === '')
            {
                continue;
            }
            if(preg_match('/^https?:\/\//i', $candidate))
            {
                return $candidate;
            }
            $candidate = preg_replace('#^/?img/#i', '', $candidate);
            $candidate = trim($candidate, '/');
            if($candidate === '')
            {
                continue;
            }
            $path = WWW_ROOT.'img'.DS.str_replace('/', DS, $candidate);
            if(@file_exists($path))
            {
                return Router::url('/img/'.$candidate, true);
            }
        }
        return '';
    }

    protected function _reminderSendDueDateRaw($sendDate = null)
    {
        $sendDate = trim((string)$sendDate);
        if($sendDate === '')
        {
            $sendDate = CakeTime::format('Y-m-d', CakeTime::convert(time(), $this->siteTimezone));
        }
        return date('Y-m-d', strtotime($sendDate.' +5 days'));
    }

    protected function _buildSmsMessage($context)
    {
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

    protected function _agencyReminderPhone($row)
    {
        $projectId = !empty($row['Project']['id']) ? (int)$row['Project']['id'] : 0;
        $agencyUserInfo = $this->_agencyUserInfo($projectId);
        $candidates = array(
            isset($agencyUserInfo['mobile']) ? $agencyUserInfo['mobile'] : '',
            isset($agencyUserInfo['phone']) ? $agencyUserInfo['phone'] : '',
            !empty($row['Project']['mobile']) ? $row['Project']['mobile'] : '',
            !empty($row['Project']['phone']) ? $row['Project']['phone'] : '',
            isset($this->contact) ? $this->contact : ''
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
        if($projectId <= 0)
        {
            return array();
        }
        if(isset($this->_agencyUserInfoCache[$projectId]))
        {
            return $this->_agencyUserInfoCache[$projectId];
        }
        try
        {
            $this->loadModel('Admin.User');
            $row = (array)$this->User->find('first', array(
                'fields' => array('User.id', 'User.name', 'User.phone', 'User.mobile', 'User.email', 'User.address', 'User.photo'),
                'conditions' => array('User.project_id' => $projectId, 'User.ugroup_id' => 4, 'User.status' => 'Active'),
                'order' => array('User.id' => 'asc'),
                'recursive' => -1
            ));
            $this->_agencyUserInfoCache[$projectId] = !empty($row['User']) ? (array)$row['User'] : array();
        }
        catch(Exception $e)
        {
            $this->_agencyUserInfoCache[$projectId] = array();
        }
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

    protected function _buildEmailMessage($context)
    {
        $title = !empty($context['is_overdue']) ? 'Overdue Payment Reminder' : 'Installment Payment Reminder';
        $lines = array(
            $title,
            '',
            'Dear '.$context['client_name'].',',
            '',
            'This is a reminder for your installment payment.',
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
        if(!empty($context['bank_accounts']))
        {
            $lines[] = '';
            $lines[] = 'Payment Account(s)';
            $lines[] = $context['bank_accounts'];
        }
        $lines[] = '';
        $lines[] = 'Please clear the pending dues as soon as possible. If you have already paid, kindly ignore this message or contact our office for confirmation.';
        $lines[] = '';
        $lines[] = 'Regards,';
        $lines[] = $context['agency_name'];
        $lines[] = 'Contact: '.$context['agency_phone'];
        return implode("\n", $lines);
    }

    protected function _buildWhatsappMessage($context)
    {
        $lines = array();
        if(!empty($context['is_overdue']))
        {
            $lines[] = '🔔 *Overdue Payment Reminder*';
        }
        else
        {
            $lines[] = '🔔 *Installment Payment Reminder*';
        }
        $lines[] = '';
        $lines[] = 'Dear *'.$context['client_name'].'*,';
        $lines[] = '';
        if(!empty($context['is_overdue']))
        {
            $lines[] = 'We would like to remind you that your installment payment is currently overdue. Kindly clear the pending amount at your earliest convenience to avoid any further delay, penalties, or additional charges.';
        }
        else
        {
            $lines[] = 'We would like to remind you that your installment payment is coming due. Kindly arrange the payment before the due date to avoid any delay, penalties, or additional charges.';
        }
        $lines[] = '';
        $lines[] = '🏷️ *Membership No:* '.$context['invoice_no'];
        $lines[] = '🏠 *Property/File:* '.$context['property_name'];
        $lines[] = '📌 *Installment Status:* '.$context['installment'];
        $lines[] = '📅 *Due Date: '.$context['due_date'].'*';
        $lines[] = '💰 *Installment Amount:* '.$context['amount'];
        if(!empty($context['overdue_count']))
        {
            $lines[] = '';
            $lines[] = '📊 *Overdue Summary*';
            $lines[] = '• Total overdue installments: *'.$context['overdue_count'].'*';
            $lines[] = '• Total overdue amount: *'.$context['overdue_amount'].'*';
            $lines[] = '• Oldest overdue age: *'.$context['overdue_age'].' days*';
        }
        if(!empty($context['bank_accounts']))
        {
            $lines[] = '';
            $lines[] = '🏦 *Payment Account(s)*';
            $lines[] = $context['bank_accounts'];
        }
        $lines[] = '';
        $lines[] = '✅ If you have already made the payment, please ignore this message or share the payment proof for confirmation.';
        $lines[] = '';
        $lines[] = '📞 *Contact:* '.$context['agency_phone'];
        $lines[] = '';
        $lines[] = 'Regards,';
        $lines[] = '*'.$context['agency_name'].'*';
        return implode("\n", $lines);
    }

    protected function _dealOverdueSummary($row)
    {
        $summary = array('count' => 0, 'amount' => 0, 'age' => 0);
        if(empty($row['Deal']['id']))
        {
            return $summary;
        }
        $this->loadModel('Admin.PlansPayment');
        $today = CakeTime::format('Y-m-d', CakeTime::convert(time(), $this->siteTimezone));
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
        try
        {
            $rows = (array)$this->PlansPayment->find('all', array(
                'fields' => array('PlansPayment.id', 'PlansPayment.name', 'PlansPayment.amount', 'PlansPayment.date', 'Deal.id', 'Deal.plan_id'),
                'joins' => array(
                    array('table' => 'deals', 'alias' => 'Deal', 'type' => 'LEFT', 'conditions' => array('PlansPayment.deal_id=Deal.id')),
                    array('table' => 'deals_payments', 'alias' => 'DealsPayment', 'type' => 'LEFT', 'conditions' => array('PlansPayment.id=DealsPayment.plans_payment_id'))
                ),
                'conditions' => $conditions,
                'order' => array('PlansPayment.date' => 'asc'),
                'recursive' => -1
            ));
        }
        catch(Exception $e)
        {
            return $summary;
        }
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
            $age = (int)floor((strtotime($today) - strtotime($oldestDate)) / 86400);
            $summary['age'] = $age > 0 ? $age : 0;
        }
        return $summary;
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
            'created_by' => (int)$this->luserId
        );
        if($status === 'sent' || $status === 'opened')
        {
            $data['sent_at'] = date('Y-m-d H:i:s');
        }
        else
        {
            $data['failed_at'] = date('Y-m-d H:i:s');
        }
        try
        {
            $this->PaymentReminder->create();
            $this->PaymentReminder->save(array('PaymentReminder' => $data), array('validate' => false));
        }
        catch(Exception $e)
        {
            // Reminder sending must not fail only because the log cannot be written.
        }
    }

    protected function _reminderType($row)
    {
        $today = CakeTime::format('Y-m-d', CakeTime::convert(time(), $this->siteTimezone));
        if(!empty($row['PlansPayment']['date']) && $row['PlansPayment']['date'] < $today)
        {
            return 'overdue';
        }
        return 'before_due';
    }

    protected function _dealStatusCondition()
    {
        $this->loadModel('Deal');
        if(!$this->Deal->hasField('deal_status'))
        {
            return array();
        }
        return array('OR' => array(
            'Deal.deal_status IS NULL',
            'Deal.deal_status' => '',
            'NOT' => array('Deal.deal_status' => $this->_blockedDealStatuses)
        ));
    }

    protected function _ensurePaymentRemindersTable()
    {
        try
        {
            $this->PaymentReminder->ensureSchema();
            $this->PaymentReminderSetting->ensureSchema();
            $this->_ensurePaymentReminderPromiseColumn();
        }
        catch(Exception $e)
        {
            // The view will still open; live servers without CREATE permission can import the SQL file.
        }
    }

    protected function _ensurePaymentReminderPromiseColumn()
    {
        $modelReady = false;
        try
        {
            $this->loadModel('Admin.PlansPayment');
            $modelReady = true;
            $rows = (array)$this->PlansPayment->query("SHOW COLUMNS FROM `plans_payments` LIKE 'recovery_promise_date'");
            if(empty($rows))
            {
                $this->PlansPayment->query("ALTER TABLE `plans_payments` ADD COLUMN `recovery_promise_date` date DEFAULT NULL AFTER `date`");
            }
            $this->PlansPayment->schema(true);
        }
        catch(Exception $e)
        {
            // Optional helper column; the page remains usable if DB ALTER is not allowed.
        }
        if(!$modelReady)
        {
            return;
        }
        try
        {
            $this->PlansPayment->query("ALTER TABLE `plans_payments` ADD KEY `recovery_promise_date` (`recovery_promise_date`)");
        }
        catch(Exception $e)
        {
            // Index may already exist.
        }
    }

    protected function _ensurePaymentReminderTemplates()
    {
        static $checked = false;
        if($checked)
        {
            return;
        }
        $checked = true;
        try
        {
            $this->loadModel('Admin.Smstemplate');
            if(method_exists($this->Smstemplate, 'ensureOwnershipSchema'))
            {
                $this->Smstemplate->ensureOwnershipSchema();
            }
            $templates = array(
                'PRS' => array(
                    'name' => 'Overdue Payment Reminder SMS',
                    'description' => $this->_defaultSmsReminderTemplate(true)
                ),
                'PDS' => array(
                    'name' => 'Before Due Payment Reminder SMS',
                    'description' => $this->_defaultSmsReminderTemplate(false)
                ),
                'PRW' => array(
                    'name' => 'Overdue Payment Reminder WhatsApp',
                    'description' => $this->_defaultWhatsappReminderTemplate(true)
                ),
                'PDW' => array(
                    'name' => 'Before Due Payment Reminder WhatsApp',
                    'description' => $this->_defaultWhatsappReminderTemplate(false)
                )
            );
            $this->_ensureMessageTemplateRows($this->Smstemplate, 'Smstemplate', $templates);

            $this->loadModel('Admin.Emailtemplate');
            if(method_exists($this->Emailtemplate, 'ensureOwnershipSchema'))
            {
                $this->Emailtemplate->ensureOwnershipSchema();
            }
            $emailTemplates = array(
                'PRE' => array(
                    'name' => 'Overdue Payment Reminder Email',
                    'description' => $this->_defaultEmailReminderTemplate(true)
                ),
                'PDE' => array(
                    'name' => 'Before Due Payment Reminder Email',
                    'description' => $this->_defaultEmailReminderTemplate(false)
                )
            );
            $this->_ensureMessageTemplateRows($this->Emailtemplate, 'Emailtemplate', $emailTemplates);
        }
        catch(Exception $e)
        {
            // Template seeding is optional; built-in reminder messages remain available.
        }
    }

    protected function _ensureMessageTemplateRows($Model, $alias, $templates)
    {
        foreach((array)$templates as $type => $template)
        {
            $existing = (array)$Model->find('first', array(
                'fields' => array($alias.'.id', $alias.'.description'),
                'conditions' => array($alias.'.type' => $type),
                'recursive' => -1
            ));
            if(!empty($existing[$alias]['id']))
            {
                $description = isset($existing[$alias]['description']) ? (string)$existing[$alias]['description'] : '';
                if($this->_templateNeedsBankPlaceholder($type, $description))
                {
                    $Model->id = (int)$existing[$alias]['id'];
                    $Model->saveField('description', rtrim($description).$this->_bankPlaceholderAppendText($type), array('validate' => false));
                }
                continue;
            }
            $Model->create();
            $Model->save(array($alias => array(
                'name' => $template['name'],
                'description' => $template['description'],
                'status' => 'Published',
                'type' => $type
            )), array('validate' => false));
        }
    }

    protected function _templateNeedsBankPlaceholder($type, $description)
    {
        $type = strtoupper(trim((string)$type));
        if(!in_array($type, array('PRS', 'PDS', 'PRW', 'PDW', 'PRE', 'PDE'), true))
        {
            return false;
        }
        $description = (string)$description;
        foreach(array('$bankAccounts', '$primaryBankAccount', '{{bank_accounts}}', '{{primary_bank_account}}', '{{BANK_ACCOUNTS}}', '{{PRIMARY_BANK_ACCOUNT}}', '{{BANK_NAME}}', '{{ACCOUNT_NO}}') as $placeholder)
        {
            if(strpos($description, $placeholder) !== false)
            {
                return false;
            }
        }
        return true;
    }

    protected function _bankPlaceholderAppendText($type)
    {
        $type = strtoupper(trim((string)$type));
        if(in_array($type, array('PRS', 'PDS'), true))
        {
            return ' Payment Account: $primaryBankAccount.';
        }
        if(in_array($type, array('PRE', 'PDE'), true))
        {
            return "\n\nPayment Account(s)\n$bankAccounts";
        }
        return "\n\n🏦 *Payment Account(s)*\n$bankAccounts";
    }

    protected function _defaultSmsReminderTemplate($isOverdue = true)
    {
        if($isOverdue)
        {
            return 'Dear $clientName, your installment status is $installmentStatus for membership $invoiceNo. Due Date: $dueDate. Amount: $amount. Total overdue installments: $totalOverdueInstallmentCount. Total overdue amount: $totalOverdueAmount. Payment Account: $primaryBankAccount. Contact: $contactNo. Regards, $agencyName';
        }
        return 'Dear $clientName, your installment $installmentStatus for membership $invoiceNo is due on $dueDate. Amount: $amount. Payment Account: $primaryBankAccount. Please arrange payment before due date. Contact: $contactNo. Regards, $agencyName';
    }

    protected function _defaultEmailReminderTemplate($isOverdue = true)
    {
        $title = $isOverdue ? 'Overdue Payment Reminder' : 'Installment Payment Reminder';
        $titleColor = $isOverdue ? '#b00020' : '#0b3d2e';
        $intro = $isOverdue
            ? 'We would like to remind you that your installment payment is currently overdue. Kindly clear the pending amount at your earliest convenience to avoid any further delay, penalties, or additional charges.'
            : 'We would like to remind you that your installment payment is coming due. Kindly arrange the payment before the due date to avoid any delay, penalties, or additional charges.';
        $summaryBlock = '';
        if($isOverdue)
        {
            $summaryBlock = implode("\n", array(
                '      <div style="margin-top:25px; background-color:#f1f5f9; padding:18px; border-radius:8px;">',
                '        <h3 style="margin-top:0;">📊 Overdue Summary</h3>',
                '        <p style="margin:6px 0;">• Total overdue installments: <strong>{{TOTAL_OVERDUE_INSTALLMENTS}}</strong></p>',
                '        <p style="margin:6px 0;">• Total overdue amount: <strong>{{TOTAL_OVERDUE_AMOUNT}}</strong></p>',
                '        <p style="margin:6px 0;">• Oldest overdue age: <strong>{{OLDEST_OVERDUE_AGE}}</strong></p>',
                '      </div>'
            ));
        }
        return implode("\n", array(
            '<!DOCTYPE html>',
            '<html>',
            '<head>',
            '  <meta charset="UTF-8">',
            '  <title>'.$title.'</title>',
            '</head>',
            '<body style="margin:0; padding:0; background-color:#f4f6f8; font-family:Arial, Helvetica, sans-serif; color:#222;">',
            '',
            '  <div style="max-width:700px; margin:0 auto; background-color:#ffffff; border-radius:10px; overflow:hidden; border:1px solid #e5e5e5;">',
            '',
            '    <div style="background-color:#0b3d2e; color:#ffffff; text-align:center; padding:25px 20px;">',
            '      <img src="{{AGENCY_LOGO_URL}}" alt="{{AGENCY_NAME}} Logo" style="width:90px; height:90px; object-fit:contain; background-color:#ffffff; border-radius:50%; padding:8px; margin-bottom:12px;">',
            '      <h1 style="margin:0; font-size:28px;">{{AGENCY_NAME}}</h1>',
            '      <p style="margin:8px 0 0; font-size:15px;">{{AGENCY_TAGLINE}}</p>',
            '      <p style="margin:10px 0 0; font-size:13px; line-height:1.5; color:#e7f5ef;">',
            '        📍 {{AGENCY_ADDRESS}}<br>',
            '        📞 {{AGENCY_CONTACT_NO}} &nbsp; | &nbsp; ✉️ {{AGENCY_EMAIL}}',
            '      </p>',
            '    </div>',
            '',
            '    <div style="padding:30px 25px;">',
            '',
            '      <h2 style="color:'.$titleColor.'; margin-top:0;">🔔 '.$title.'</h2>',
            '',
            '      <p style="font-size:16px; line-height:1.6;">Dear <strong>{{CLIENT_NAME}}</strong>,</p>',
            '',
            '      <p style="font-size:16px; line-height:1.6;">'.$intro.'</p>',
            '',
            '      <table width="100%" cellpadding="10" cellspacing="0" style="border-collapse:collapse; margin-top:20px; font-size:15px;">',
            '        <tr style="background-color:#f8f8f8;">',
            '          <td style="border:1px solid #ddd;"><strong>🏷️ Membership No:</strong></td>',
            '          <td style="border:1px solid #ddd;">{{MEMBERSHIP_NO}}</td>',
            '        </tr>',
            '        <tr>',
            '          <td style="border:1px solid #ddd;"><strong>🏠 Property/File:</strong></td>',
            '          <td style="border:1px solid #ddd;">{{PROPERTY_FILE_NO}}</td>',
            '        </tr>',
            '        <tr style="background-color:#f8f8f8;">',
            '          <td style="border:1px solid #ddd;"><strong>📌 Installment Status:</strong></td>',
            '          <td style="border:1px solid #ddd;">{{INSTALLMENT_STATUS}}</td>',
            '        </tr>',
            '        <tr>',
            '          <td style="border:1px solid #ddd;"><strong>📅 Due Date:</strong></td>',
            '          <td style="border:1px solid #ddd;"><strong>{{DUE_DATE}}</strong></td>',
            '        </tr>',
            '        <tr style="background-color:#f8f8f8;">',
            '          <td style="border:1px solid #ddd;"><strong>💰 Installment Amount:</strong></td>',
            '          <td style="border:1px solid #ddd;"><strong>{{INSTALLMENT_AMOUNT}}</strong></td>',
            '        </tr>',
            '      </table>',
            '',
            '      <div style="background-color:#fff8e1; border:2px solid #f0b429; border-radius:10px; padding:20px; margin-top:25px; text-align:center;">',
            '        <h3 style="margin-top:0; color:#8a5a00;">🏦 Bank Account Details</h3>',
            '        <p style="font-size:15px; margin:8px 0;"><strong>Bank Name:</strong> {{BANK_NAME}}</p>',
            '        <p style="font-size:15px; margin:8px 0;"><strong>Account Title:</strong> {{ACCOUNT_TITLE}}</p>',
            '        <p style="font-size:22px; font-weight:bold; color:#0b3d2e; margin:15px 0;">Account No: <span>{{ACCOUNT_NO}}</span></p>',
            '        <p style="font-size:15px; margin:8px 0;"><strong>IBAN:</strong> {{IBAN}}</p>',
            '        <p style="font-size:13px; color:#666; margin-top:12px;">After payment, please share your payment screenshot/receipt for confirmation.</p>',
            '      </div>',
            '',
            $summaryBlock,
            '',
            '      <p style="font-size:15px; line-height:1.6; margin-top:25px;">✅ If you have already made the payment, please ignore this message or share the payment proof for confirmation.</p>',
            '',
            '      <p style="font-size:15px; line-height:1.6;">📞 <strong>Contact:</strong> {{AGENCY_CONTACT_NO}}</p>',
            '',
            '      <p style="font-size:16px; margin-top:25px;">Regards,<br><strong>{{AGENCY_NAME}}</strong></p>',
            '',
            '    </div>',
            '',
            '    <div style="background-color:#0b3d2e; color:#ffffff; text-align:center; padding:20px; font-size:14px; line-height:1.6;">',
            '      <strong>{{AGENCY_NAME}}</strong><br>',
            '      📍 Office Address: {{AGENCY_ADDRESS}}<br>',
            '      📞 Contact: {{AGENCY_CONTACT_NO}}<br>',
            '      ☎️ UAN: {{AGENCY_UAN}}<br>',
            '      ✉️ Email: {{AGENCY_EMAIL}}<br>',
            '      🌐 Website: {{AGENCY_WEBSITE}}',
            '    </div>',
            '',
            '  </div>',
            '',
            '</body>',
            '</html>'
        ));
    }

    protected function _defaultWhatsappReminderTemplate($isOverdue = true)
    {
        if(!$isOverdue)
        {
            return implode("\n", array(
                '🔔 *Installment Payment Reminder*',
                '',
                'Dear *$clientName*,',
                '',
                'We would like to remind you that your installment payment is coming due. Kindly arrange the payment before the due date to avoid any delay, penalties, or additional charges.',
                '',
                '🏷️ *Membership No:* $invoiceNo',
                '🏠 *Property/File:* $propertyName',
                '📌 *Installment Status:* $installmentStatus',
                '📅 *Due Date: $dueDate*',
                '💰 *Installment Amount:* $amount',
                '',
                '🏦 *Payment Account(s)*',
                '$bankAccounts',
                '',
                '✅ If you have already made the payment, please ignore this message or share the payment proof for confirmation.',
                '',
                '📞 *Contact:* $contactNo',
                '',
                'Regards,',
                '*$agencyName*'
            ));
        }
        return implode("\n", array(
            '🔔 *Overdue Payment Reminder*',
            '',
            'Dear *$clientName*,',
            '',
            'We would like to remind you that your installment payment is currently overdue. Kindly clear the pending amount at your earliest convenience to avoid any further delay, penalties, or additional charges.',
            '',
            '🏷️ *Membership No:* $invoiceNo',
            '🏠 *Property/File:* $propertyName',
            '📌 *Installment Status:* $installmentStatus',
            '📅 *Due Date: $dueDate*',
            '💰 *Installment Amount:* $amount',
            '',
            '📊 *Overdue Summary*',
            '• Total overdue installments: *$totalOverdueInstallmentCount*',
            '• Total overdue amount: *$totalOverdueAmount*',
            '• Oldest overdue age: *$overdueAge days*',
            '',
            '🏦 *Payment Account(s)*',
            '$bankAccounts',
            '',
            '✅ If you have already made the payment, please ignore this message or share the payment proof for confirmation.',
            '',
            '📞 *Contact:* $contactNo',
            '',
            'Regards,',
            '*$agencyName*'
        ));
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
}
?>
