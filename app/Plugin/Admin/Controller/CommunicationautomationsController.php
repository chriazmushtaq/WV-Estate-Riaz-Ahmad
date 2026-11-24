<?php
App::uses('AdminAppController', 'Admin.Controller');
App::uses('CakeEmail', 'Network/Email');
App::uses('HttpSocket', 'Network/Http');
App::uses('Router', 'Routing');

class CommunicationautomationsController extends AdminAppController
{
    public $helpers = array('Html', 'Form', 'Session', 'Paginator', 'Js' => array('Jquery'));
    public $components = array('Session', 'Paginator');
    public $uses = array(
        'Admin.CommunicationTemplate',
        'Admin.CommunicationRule',
        'Admin.CommunicationQueue',
        'Admin.CommunicationLog'
    );

    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->_ensureCommunicationSchema();
        $this->_ensureCommunicationAutomationMenu();
        $this->set('communicationEventOptions', $this->_eventOptions());
        $this->set('communicationChannelOptions', $this->_channelOptions());
        $this->set('communicationProjectOptions', $this->_projectOptions());
    }

    protected function _ensureCommunicationSchema()
    {
        try
        {
            $this->CommunicationTemplate->ensureSchema();
            $this->CommunicationRule->ensureSchema();
            $this->CommunicationQueue->ensureSchema();
            $this->CommunicationLog->ensureSchema();
        }
        catch(Exception $e)
        {
            // Pages stay available; DB users without CREATE permission can import the SQL file.
        }
    }

    protected function _projectOptions()
    {
        try
        {
            return (array)$this->CustomFunction->agencyWiseList($this->userType, $this->agencyId);
        }
        catch(Exception $e)
        {
            return array();
        }
    }

    protected function _eventOptions()
    {
        return array(
            'lead_created' => __('Lead Created'),
            'deal_created' => __('Deal Created'),
            'payment_received' => __('Payment Received'),
            'installment_before_due' => __('Installment Before Due'),
            'installment_due_today' => __('Installment Due Today'),
            'installment_overdue' => __('Installment Overdue'),
            'refund_completed' => __('Refund Schedule Completed'),
            'letter_issued' => __('Letter Issued'),
            'birthday' => __('Client Birthday'),
            'custom_bulk' => __('Custom Bulk Campaign')
        );
    }

    protected function _channelOptions()
    {
        return array(
            'sms' => __('SMS'),
            'whatsapp' => __('WhatsApp'),
            'email' => __('Email'),
            'letter' => __('Letter Task')
        );
    }

    protected function _templateOptionsByChannel()
    {
        $options = array('sms' => array(), 'whatsapp' => array(), 'email' => array(), 'letter' => array());
        $rows = (array)$this->CommunicationTemplate->find('all', array(
            'fields' => array('CommunicationTemplate.id', 'CommunicationTemplate.name', 'CommunicationTemplate.channel'),
            'conditions' => array('CommunicationTemplate.status' => 'Published'),
            'order' => array('CommunicationTemplate.channel' => 'asc', 'CommunicationTemplate.name' => 'asc'),
            'recursive' => -1
        ));
        foreach($rows as $row)
        {
            $template = (array)$row['CommunicationTemplate'];
            $channel = strtolower(trim((string)$template['channel']));
            if(!isset($options[$channel]))
            {
                $options[$channel] = array();
            }
            $options[$channel][(int)$template['id']] = $template['name'];
        }
        return $options;
    }

    protected function _projectScopeConditions($alias = 'CommunicationTemplate', $field = 'project_id')
    {
        if($this->_isSuperAdminSession())
        {
            return array();
        }
        if((int)$this->agencyId > 0)
        {
            return array($alias.'.'.$field => array(0, (int)$this->agencyId));
        }
        return array($alias.'.'.$field => 0);
    }

    protected function _selectedProjectId($raw = null)
    {
        $projectId = (int)$raw;
        if(!$this->_isSuperAdminSession() && (int)$this->agencyId > 0)
        {
            return (int)$this->agencyId;
        }
        return $projectId > 0 ? $projectId : 0;
    }

    public function index()
    {
        $templateCount = (int)$this->CommunicationTemplate->find('count', array('recursive' => -1));
        $ruleCount = (int)$this->CommunicationRule->find('count', array('recursive' => -1));
        $pendingCount = (int)$this->CommunicationQueue->find('count', array('conditions' => array('CommunicationQueue.status' => 'pending'), 'recursive' => -1));
        $failedCount = (int)$this->CommunicationQueue->find('count', array('conditions' => array('CommunicationQueue.status' => 'failed'), 'recursive' => -1));
        $sentCount = (int)$this->CommunicationLog->find('count', array('conditions' => array('CommunicationLog.status' => array('sent', 'prepared')), 'recursive' => -1));
        $recentQueue = (array)$this->CommunicationQueue->find('all', array(
            'order' => array('CommunicationQueue.id' => 'desc'),
            'limit' => 10,
            'recursive' => -1
        ));
        $recentLogs = (array)$this->CommunicationLog->find('all', array(
            'order' => array('CommunicationLog.id' => 'desc'),
            'limit' => 10,
            'recursive' => -1
        ));
        $this->set(compact('templateCount', 'ruleCount', 'pendingCount', 'failedCount', 'sentCount', 'recentQueue', 'recentLogs'));
    }

    public function templates()
    {
        $filters = array(
            'channel' => isset($this->request->query['channel']) ? trim((string)$this->request->query['channel']) : '',
            'event_key' => isset($this->request->query['event_key']) ? trim((string)$this->request->query['event_key']) : '',
            'keyword' => isset($this->request->query['keyword']) ? trim((string)$this->request->query['keyword']) : ''
        );
        $conditions = array();
        if($filters['channel'] !== '')
        {
            $conditions['CommunicationTemplate.channel'] = $filters['channel'];
        }
        if($filters['event_key'] !== '')
        {
            $conditions['CommunicationTemplate.event_key'] = $filters['event_key'];
        }
        if($filters['keyword'] !== '')
        {
            $conditions[] = array('OR' => array(
                'CommunicationTemplate.name LIKE' => '%'.$filters['keyword'].'%',
                'CommunicationTemplate.code LIKE' => '%'.$filters['keyword'].'%',
                'CommunicationTemplate.subject LIKE' => '%'.$filters['keyword'].'%'
            ));
        }
        $scope = $this->_projectScopeConditions('CommunicationTemplate');
        if(!empty($scope))
        {
            $conditions[] = $scope;
        }
        $this->Paginator->settings = array(
            'CommunicationTemplate' => array(
                'conditions' => $conditions,
                'order' => array('CommunicationTemplate.id' => 'desc'),
                'limit' => 30,
                'recursive' => -1
            )
        );
        $rows = (array)$this->Paginator->paginate('CommunicationTemplate');
        $this->set(compact('rows', 'filters'));
    }

    public function templateadd()
    {
        $this->_saveTemplateForm();
        $this->render('templateform');
    }

    public function templateedit($id = null)
    {
        $id = (int)$id;
        $row = (array)$this->CommunicationTemplate->findById($id);
        if(empty($row['CommunicationTemplate']['id']))
        {
            $this->Session->setFlash(__('Template was not found.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'templates'));
        }
        $this->_saveTemplateForm($row);
        $this->render('templateform');
    }

    protected function _saveTemplateForm($existing = array())
    {
        $isEdit = !empty($existing['CommunicationTemplate']['id']);
        if($this->request->is(array('post', 'put')))
        {
            $data = !empty($this->request->data['CommunicationTemplate']) ? (array)$this->request->data['CommunicationTemplate'] : array();
            if($isEdit)
            {
                $data['id'] = (int)$existing['CommunicationTemplate']['id'];
            }
            $data['project_id'] = $this->_selectedProjectId(isset($data['project_id']) ? $data['project_id'] : 0);
            $saveData = $this->CommunicationTemplate->normalizeSaveData($data, (int)$this->luserId);
            if(trim((string)$saveData['name']) === '' || trim((string)$saveData['body']) === '')
            {
                $this->Session->setFlash(__('Please enter template name and body.'), 'flash', array('alert' => 'danger'));
            }
            else
            {
                $this->CommunicationTemplate->create();
                if($this->CommunicationTemplate->save(array('CommunicationTemplate' => $saveData), array('validate' => false)))
                {
                    $this->Session->setFlash(__('Communication template saved.'), 'flash', array('alert' => 'success'));
                    return $this->redirect(array('action' => 'templates'));
                }
                $this->Session->setFlash(__('Template could not be saved.'), 'flash', array('alert' => 'danger'));
            }
        }
        elseif($isEdit)
        {
            $this->request->data = $existing;
        }
        $this->set('isEdit', $isEdit);
    }

    public function templatepreview($id = null)
    {
        $this->layout = 'ajax';
        $id = (int)$id;
        $row = (array)$this->CommunicationTemplate->findById($id);
        if(empty($row['CommunicationTemplate']['id']))
        {
            throw new NotFoundException(__('Template not found.'));
        }
        $tokens = $this->_sampleTokens();
        $template = (array)$row['CommunicationTemplate'];
        $subject = $this->_renderWithTokens($template['subject'], $tokens);
        $body = $this->_renderWithTokens($template['body'], $tokens);
        $this->set(compact('template', 'subject', 'body'));
    }

    public function templatestatus($id = null, $status = 'Published')
    {
        $id = (int)$id;
        $status = ($status === 'Unpublished') ? 'Unpublished' : 'Published';
        $this->CommunicationTemplate->id = $id;
        if($id > 0 && $this->CommunicationTemplate->saveField('status', $status, array('validate' => false)))
        {
            $this->Session->setFlash(__('Template status updated.'), 'flash', array('alert' => 'success'));
        }
        else
        {
            $this->Session->setFlash(__('Template status could not be updated.'), 'flash', array('alert' => 'danger'));
        }
        return $this->redirect(array('action' => 'templates'));
    }

    public function rules()
    {
        $conditions = array();
        $scope = $this->_projectScopeConditions('CommunicationRule');
        if(!empty($scope))
        {
            $conditions[] = $scope;
        }
        $this->Paginator->settings = array(
            'CommunicationRule' => array(
                'conditions' => $conditions,
                'order' => array('CommunicationRule.id' => 'desc'),
                'limit' => 30,
                'recursive' => -1
            )
        );
        $rows = (array)$this->Paginator->paginate('CommunicationRule');
        $templateOptions = $this->_templateOptionsByChannel();
        $this->set(compact('rows', 'templateOptions'));
    }

    public function ruleadd()
    {
        $this->_saveRuleForm();
        $this->render('ruleform');
    }

    public function ruleedit($id = null)
    {
        $id = (int)$id;
        $row = (array)$this->CommunicationRule->findById($id);
        if(empty($row['CommunicationRule']['id']))
        {
            $this->Session->setFlash(__('Automation rule was not found.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'rules'));
        }
        $this->_saveRuleForm($row);
        $this->render('ruleform');
    }

    protected function _saveRuleForm($existing = array())
    {
        $isEdit = !empty($existing['CommunicationRule']['id']);
        if($this->request->is(array('post', 'put')))
        {
            $data = !empty($this->request->data['CommunicationRule']) ? (array)$this->request->data['CommunicationRule'] : array();
            if($isEdit)
            {
                $data['id'] = (int)$existing['CommunicationRule']['id'];
            }
            $data['project_id'] = $this->_selectedProjectId(isset($data['project_id']) ? $data['project_id'] : 0);
            $saveData = $this->CommunicationRule->normalizeSaveData($data, (int)$this->luserId);
            if(trim((string)$saveData['name']) === '' || trim((string)$saveData['channels']) === '')
            {
                $this->Session->setFlash(__('Please enter rule name and select at least one channel.'), 'flash', array('alert' => 'danger'));
            }
            else
            {
                $this->CommunicationRule->create();
                if($this->CommunicationRule->save(array('CommunicationRule' => $saveData), array('validate' => false)))
                {
                    $this->Session->setFlash(__('Automation rule saved.'), 'flash', array('alert' => 'success'));
                    return $this->redirect(array('action' => 'rules'));
                }
                $this->Session->setFlash(__('Automation rule could not be saved.'), 'flash', array('alert' => 'danger'));
            }
        }
        elseif($isEdit)
        {
            $this->request->data = $existing;
            if(!empty($this->request->data['CommunicationRule']['channels']))
            {
                $this->request->data['CommunicationRule']['channels'] = explode(',', $this->request->data['CommunicationRule']['channels']);
            }
        }
        $templateOptions = $this->_templateOptionsByChannel();
        $this->set(compact('isEdit', 'templateOptions'));
    }

    public function rulestatus($id = null, $status = 'Active')
    {
        $id = (int)$id;
        $status = ($status === 'Paused') ? 'Paused' : 'Active';
        $this->CommunicationRule->id = $id;
        if($id > 0 && $this->CommunicationRule->saveField('status', $status, array('validate' => false)))
        {
            $this->Session->setFlash(__('Rule status updated.'), 'flash', array('alert' => 'success'));
        }
        else
        {
            $this->Session->setFlash(__('Rule status could not be updated.'), 'flash', array('alert' => 'danger'));
        }
        return $this->redirect(array('action' => 'rules'));
    }

    public function queue()
    {
        $filters = array(
            'status' => isset($this->request->query['status']) ? trim((string)$this->request->query['status']) : 'pending',
            'channel' => isset($this->request->query['channel']) ? trim((string)$this->request->query['channel']) : '',
            'keyword' => isset($this->request->query['keyword']) ? trim((string)$this->request->query['keyword']) : ''
        );
        $conditions = array();
        if($filters['status'] !== 'all' && $filters['status'] !== '')
        {
            $conditions['CommunicationQueue.status'] = $filters['status'];
        }
        if($filters['channel'] !== '')
        {
            $conditions['CommunicationQueue.channel'] = $filters['channel'];
        }
        if($filters['keyword'] !== '')
        {
            $conditions[] = array('OR' => array(
                'CommunicationQueue.recipient_name LIKE' => '%'.$filters['keyword'].'%',
                'CommunicationQueue.recipient_mobile LIKE' => '%'.$filters['keyword'].'%',
                'CommunicationQueue.recipient_email LIKE' => '%'.$filters['keyword'].'%',
                'CommunicationQueue.subject LIKE' => '%'.$filters['keyword'].'%'
            ));
        }
        $scope = $this->_projectScopeConditions('CommunicationQueue');
        if(!empty($scope))
        {
            $conditions[] = $scope;
        }
        $this->Paginator->settings = array(
            'CommunicationQueue' => array(
                'conditions' => $conditions,
                'order' => array('CommunicationQueue.id' => 'desc'),
                'limit' => 40,
                'recursive' => -1
            )
        );
        $rows = (array)$this->Paginator->paginate('CommunicationQueue');
        $this->set(compact('rows', 'filters'));
    }

    public function runqueue()
    {
        if(!$this->request->is('post'))
        {
            throw new MethodNotAllowedException(__('Invalid request method.'));
        }
        $limit = !empty($this->request->data['CommunicationQueue']['limit']) ? (int)$this->request->data['CommunicationQueue']['limit'] : 25;
        $result = $this->_processQueue($limit);
        $this->Session->setFlash(sprintf(__('Queue processed. Sent: %s. Prepared: %s. Failed: %s.'), (int)$result['sent'], (int)$result['prepared'], (int)$result['failed']), 'flash', array('alert' => ((int)$result['failed'] > 0 ? 'warning' : 'success')));
        return $this->redirect(array('action' => 'queue'));
    }

    public function retry($id = null)
    {
        if(!$this->request->is('post'))
        {
            throw new MethodNotAllowedException(__('Invalid request method.'));
        }
        $id = (int)$id;
        $this->CommunicationQueue->id = $id;
        if($id > 0 && $this->CommunicationQueue->save(array('CommunicationQueue' => array(
            'id' => $id,
            'status' => 'pending',
            'error_text' => '',
            'failed_at' => null,
            'modified_by' => (int)$this->luserId
        )), array('validate' => false)))
        {
            $this->Session->setFlash(__('Queue item moved back to pending.'), 'flash', array('alert' => 'success'));
        }
        else
        {
            $this->Session->setFlash(__('Queue item could not be retried.'), 'flash', array('alert' => 'danger'));
        }
        return $this->redirect(array('action' => 'queue'));
    }

    public function logs()
    {
        $filters = array(
            'channel' => isset($this->request->query['channel']) ? trim((string)$this->request->query['channel']) : '',
            'status' => isset($this->request->query['status']) ? trim((string)$this->request->query['status']) : '',
            'keyword' => isset($this->request->query['keyword']) ? trim((string)$this->request->query['keyword']) : ''
        );
        $conditions = array();
        if($filters['channel'] !== '')
        {
            $conditions['CommunicationLog.channel'] = $filters['channel'];
        }
        if($filters['status'] !== '')
        {
            $conditions['CommunicationLog.status'] = $filters['status'];
        }
        if($filters['keyword'] !== '')
        {
            $conditions[] = array('OR' => array(
                'CommunicationLog.recipient_name LIKE' => '%'.$filters['keyword'].'%',
                'CommunicationLog.recipient_mobile LIKE' => '%'.$filters['keyword'].'%',
                'CommunicationLog.recipient_email LIKE' => '%'.$filters['keyword'].'%',
                'CommunicationLog.subject LIKE' => '%'.$filters['keyword'].'%'
            ));
        }
        $scope = $this->_projectScopeConditions('CommunicationLog');
        if(!empty($scope))
        {
            $conditions[] = $scope;
        }
        $this->Paginator->settings = array(
            'CommunicationLog' => array(
                'conditions' => $conditions,
                'order' => array('CommunicationLog.id' => 'desc'),
                'limit' => 50,
                'recursive' => -1
            )
        );
        $rows = (array)$this->Paginator->paginate('CommunicationLog');
        $this->set(compact('rows', 'filters'));
    }

    public function timeline()
    {
        $filters = array(
            'recipient_type' => isset($this->request->query['recipient_type']) ? trim((string)$this->request->query['recipient_type']) : '',
            'recipient_id' => isset($this->request->query['recipient_id']) ? (int)$this->request->query['recipient_id'] : 0,
            'source_type' => isset($this->request->query['source_type']) ? trim((string)$this->request->query['source_type']) : '',
            'source_id' => isset($this->request->query['source_id']) ? (int)$this->request->query['source_id'] : 0,
            'client_id' => isset($this->request->query['client_id']) ? (int)$this->request->query['client_id'] : 0,
            'deal_id' => isset($this->request->query['deal_id']) ? (int)$this->request->query['deal_id'] : 0,
            'keyword' => isset($this->request->query['keyword']) ? trim((string)$this->request->query['keyword']) : ''
        );
        $conditions = array();
        if($filters['recipient_type'] !== '')
        {
            $conditions['CommunicationLog.recipient_type'] = $filters['recipient_type'];
        }
        if($filters['recipient_id'] > 0)
        {
            $conditions['CommunicationLog.recipient_id'] = $filters['recipient_id'];
        }
        if($filters['source_type'] !== '')
        {
            $conditions['CommunicationLog.source_type'] = $filters['source_type'];
        }
        if($filters['source_id'] > 0)
        {
            $conditions['CommunicationLog.source_id'] = $filters['source_id'];
        }
        if($filters['client_id'] > 0)
        {
            $conditions['CommunicationLog.recipient_type'] = 'client';
            $conditions['CommunicationLog.recipient_id'] = $filters['client_id'];
        }
        if($filters['deal_id'] > 0)
        {
            $conditions['CommunicationLog.source_type'] = 'deal';
            $conditions['CommunicationLog.source_id'] = $filters['deal_id'];
        }
        if($filters['keyword'] !== '')
        {
            $conditions[] = array('OR' => array(
                'CommunicationLog.recipient_name LIKE' => '%'.$filters['keyword'].'%',
                'CommunicationLog.subject LIKE' => '%'.$filters['keyword'].'%',
                'CommunicationLog.message LIKE' => '%'.$filters['keyword'].'%'
            ));
        }
        $scope = $this->_projectScopeConditions('CommunicationLog');
        if(!empty($scope))
        {
            $conditions[] = $scope;
        }
        $rows = (array)$this->CommunicationLog->find('all', array(
            'conditions' => $conditions,
            'order' => array('CommunicationLog.created' => 'desc', 'CommunicationLog.id' => 'desc'),
            'limit' => 100,
            'recursive' => -1
        ));

        $queueConditions = array();
        if($filters['recipient_type'] !== '')
        {
            $queueConditions['CommunicationQueue.recipient_type'] = $filters['recipient_type'];
        }
        if($filters['recipient_id'] > 0)
        {
            $queueConditions['CommunicationQueue.recipient_id'] = $filters['recipient_id'];
        }
        if($filters['source_type'] !== '')
        {
            $queueConditions['CommunicationQueue.source_type'] = $filters['source_type'];
        }
        if($filters['source_id'] > 0)
        {
            $queueConditions['CommunicationQueue.source_id'] = $filters['source_id'];
        }
        if($filters['client_id'] > 0)
        {
            $queueConditions['CommunicationQueue.recipient_type'] = 'client';
            $queueConditions['CommunicationQueue.recipient_id'] = $filters['client_id'];
        }
        if($filters['deal_id'] > 0)
        {
            $queueConditions['CommunicationQueue.source_type'] = 'deal';
            $queueConditions['CommunicationQueue.source_id'] = $filters['deal_id'];
        }
        if($filters['keyword'] !== '')
        {
            $queueConditions[] = array('OR' => array(
                'CommunicationQueue.recipient_name LIKE' => '%'.$filters['keyword'].'%',
                'CommunicationQueue.subject LIKE' => '%'.$filters['keyword'].'%',
                'CommunicationQueue.body LIKE' => '%'.$filters['keyword'].'%'
            ));
        }
        $queueScope = $this->_projectScopeConditions('CommunicationQueue');
        if(!empty($queueScope))
        {
            $queueConditions[] = $queueScope;
        }
        $queueRows = (array)$this->CommunicationQueue->find('all', array(
            'conditions' => $queueConditions,
            'order' => array('CommunicationQueue.created' => 'desc', 'CommunicationQueue.id' => 'desc'),
            'limit' => 50,
            'recursive' => -1
        ));

        $dealCommunicationRows = array();
        try
        {
            $this->loadModel('Admin.DealCommunication');
            $dealCommunicationConditions = array();
            if($filters['client_id'] > 0)
            {
                $dealCommunicationConditions['DealCommunication.client_id'] = $filters['client_id'];
            }
            if($filters['deal_id'] > 0)
            {
                $dealCommunicationConditions['DealCommunication.deal_id'] = $filters['deal_id'];
            }
            if($filters['source_type'] === 'deal' && $filters['source_id'] > 0)
            {
                $dealCommunicationConditions['DealCommunication.deal_id'] = $filters['source_id'];
            }
            if($filters['recipient_type'] === 'client' && $filters['recipient_id'] > 0)
            {
                $dealCommunicationConditions['DealCommunication.client_id'] = $filters['recipient_id'];
            }
            if($filters['keyword'] !== '')
            {
                $dealCommunicationConditions[] = array('OR' => array(
                    'DealCommunication.subject LIKE' => '%'.$filters['keyword'].'%',
                    'DealCommunication.message LIKE' => '%'.$filters['keyword'].'%',
                    'DealCommunication.contact_no LIKE' => '%'.$filters['keyword'].'%'
                ));
            }
            if(!empty($dealCommunicationConditions))
            {
                $dealCommunicationRows = (array)$this->DealCommunication->find('all', array(
                    'conditions' => $dealCommunicationConditions,
                    'order' => array('DealCommunication.created' => 'desc', 'DealCommunication.id' => 'desc'),
                    'limit' => 50,
                    'recursive' => -1
                ));
            }
        }
        catch(Exception $e)
        {
            $dealCommunicationRows = array();
        }

        $this->set(compact('rows', 'queueRows', 'dealCommunicationRows', 'filters'));
    }

    public function preview()
    {
        $templateOptions = $this->_templateOptionsByChannel();
        $audienceOptions = array(
            'sample' => __('Sample Data'),
            'overdue_installments' => __('Overdue Installments'),
            'due_soon_installments' => __('Due Soon Installments'),
            'clients' => __('All Clients'),
            'leads' => __('Leads'),
            'agents' => __('Agents'),
            'custom' => __('Custom Recipients')
        );
        $defaults = array(
            'audience' => 'sample',
            'project_id' => $this->_selectedProjectId($this->agencyId),
            'days_before' => 7,
            'limit' => 1,
            'channels' => array('sms', 'whatsapp', 'email', 'letter'),
            'sms_template_id' => !empty($templateOptions['sms']) ? key($templateOptions['sms']) : 0,
            'whatsapp_template_id' => !empty($templateOptions['whatsapp']) ? key($templateOptions['whatsapp']) : 0,
            'email_template_id' => !empty($templateOptions['email']) ? key($templateOptions['email']) : 0,
            'letter_template_id' => !empty($templateOptions['letter']) ? key($templateOptions['letter']) : 0,
            'attachment_url' => '',
            'custom_recipients' => ''
        );
        if(empty($this->request->data))
        {
            $this->request->data = array('PreviewCommunication' => $defaults);
        }

        $previews = array();
        $tokens = $this->_sampleTokens();
        $sourceSummary = __('Sample placeholder data');
        if($this->request->is(array('post', 'put')))
        {
            $data = array_merge($defaults, (array)$this->request->data['PreviewCommunication']);
            $data['project_id'] = $this->_selectedProjectId($data['project_id']);
            $data['limit'] = 1;
            $channels = $this->_selectedChannels($data);
            if($data['audience'] !== 'sample')
            {
                $rows = $this->_bulkAudienceRows($data);
                if(!empty($rows[0]))
                {
                    $tokens = $this->_tokensFromAudienceRow($rows[0], $data);
                    $sourceSummary = $this->_previewSourceSummary($rows[0]);
                }
                else
                {
                    $this->Session->setFlash(__('No matching audience record was found. Showing sample placeholder data.'), 'flash', array('alert' => 'warning'));
                }
            }
            foreach($channels as $channel)
            {
                $templateId = !empty($data[$channel.'_template_id']) ? (int)$data[$channel.'_template_id'] : 0;
                $template = $this->_templateById($templateId, $channel);
                if(empty($template))
                {
                    continue;
                }
                $previews[] = array(
                    'channel' => $channel,
                    'template' => $template,
                    'subject' => $this->_renderWithTokens(isset($template['subject']) ? $template['subject'] : '', $tokens),
                    'body' => $this->_renderWithTokens(isset($template['body']) ? $template['body'] : '', $tokens)
                );
            }
            if(empty($previews))
            {
                $this->Session->setFlash(__('Please select at least one valid template to preview.'), 'flash', array('alert' => 'warning'));
            }
        }
        $this->set(compact('templateOptions', 'audienceOptions', 'previews', 'tokens', 'sourceSummary'));
    }

    public function bulk()
    {
        $templateOptions = $this->_templateOptionsByChannel();
        $audienceOptions = array(
            'overdue_installments' => __('Overdue Installments'),
            'due_soon_installments' => __('Due Soon Installments'),
            'clients' => __('All Clients'),
            'leads' => __('Leads'),
            'agents' => __('Agents'),
            'custom' => __('Custom Recipients')
        );
        $defaults = array(
            'audience' => 'overdue_installments',
            'project_id' => $this->_selectedProjectId($this->agencyId),
            'days_before' => 7,
            'limit' => 100,
            'channels' => array('sms'),
            'sms_template_id' => !empty($templateOptions['sms']) ? key($templateOptions['sms']) : 0,
            'whatsapp_template_id' => !empty($templateOptions['whatsapp']) ? key($templateOptions['whatsapp']) : 0,
            'email_template_id' => !empty($templateOptions['email']) ? key($templateOptions['email']) : 0,
            'letter_template_id' => !empty($templateOptions['letter']) ? key($templateOptions['letter']) : 0,
            'attachment_url' => '',
            'custom_recipients' => ''
        );
        if(empty($this->request->data))
        {
            $this->request->data = array('BulkCommunication' => $defaults);
        }
        if($this->request->is('post'))
        {
            $data = array_merge($defaults, (array)$this->request->data['BulkCommunication']);
            $data['project_id'] = $this->_selectedProjectId($data['project_id']);
            $channels = $this->_selectedChannels($data);
            if(empty($channels))
            {
                $this->Session->setFlash(__('Please select at least one channel.'), 'flash', array('alert' => 'danger'));
            }
            else
            {
                $rows = $this->_bulkAudienceRows($data);
                $queued = 0;
                foreach($rows as $audienceRow)
                {
                    $tokens = $this->_tokensFromAudienceRow($audienceRow, $data);
                    foreach($channels as $channel)
                    {
                        $templateId = !empty($data[$channel.'_template_id']) ? (int)$data[$channel.'_template_id'] : 0;
                        $template = $this->_templateById($templateId, $channel);
                        if(empty($template))
                        {
                            continue;
                        }
                        if($this->_queueRenderedTemplate($template, $tokens, $audienceRow, $data))
                        {
                            $queued++;
                        }
                    }
                }
                $this->Session->setFlash(sprintf(__('%s communication item(s) queued.'), (int)$queued), 'flash', array('alert' => $queued > 0 ? 'success' : 'warning'));
                if(!empty($data['process_now']) && $queued > 0)
                {
                    $result = $this->_processQueue(min(100, (int)$data['limit']));
                    $this->Session->setFlash(sprintf(__('%s item(s) queued. Queue run sent %s, prepared %s, failed %s.'), (int)$queued, (int)$result['sent'], (int)$result['prepared'], (int)$result['failed']), 'flash', array('alert' => ((int)$result['failed'] > 0 ? 'warning' : 'success')));
                }
                return $this->redirect(array('action' => 'queue'));
            }
        }
        $this->set(compact('templateOptions', 'audienceOptions'));
    }

    protected function _selectedChannels($data)
    {
        $rawChannels = isset($data['channels']) ? $data['channels'] : array();
        if(!is_array($rawChannels))
        {
            $rawChannels = explode(',', (string)$rawChannels);
        }
        $channels = array();
        foreach($rawChannels as $channel)
        {
            $channel = strtolower(trim((string)$channel));
            if(in_array($channel, array('sms', 'whatsapp', 'email', 'letter'), true))
            {
                $channels[$channel] = $channel;
            }
        }
        return array_values($channels);
    }

    protected function _templateById($templateId, $channel)
    {
        $templateId = (int)$templateId;
        if($templateId <= 0)
        {
            return array();
        }
        $row = (array)$this->CommunicationTemplate->find('first', array(
            'conditions' => array(
                'CommunicationTemplate.id' => $templateId,
                'CommunicationTemplate.channel' => $channel,
                'CommunicationTemplate.status' => 'Published'
            ),
            'recursive' => -1
        ));
        return !empty($row['CommunicationTemplate']) ? (array)$row['CommunicationTemplate'] : array();
    }

    protected function _bulkAudienceRows($data)
    {
        $audience = isset($data['audience']) ? trim((string)$data['audience']) : 'overdue_installments';
        $limit = !empty($data['limit']) ? (int)$data['limit'] : 100;
        if($limit <= 0)
        {
            $limit = 100;
        }
        if($limit > 1000)
        {
            $limit = 1000;
        }
        if($audience === 'custom')
        {
            return $this->_customAudienceRows(isset($data['custom_recipients']) ? $data['custom_recipients'] : '', $limit);
        }
        if($audience === 'overdue_installments' || $audience === 'due_soon_installments')
        {
            return $this->_installmentAudienceRows($data, $audience, $limit);
        }
        if($audience === 'leads')
        {
            return $this->_leadAudienceRows($data, $limit);
        }
        if($audience === 'agents')
        {
            return $this->_agentAudienceRows($data, $limit);
        }
        return $this->_clientAudienceRows($data, $limit);
    }

    protected function _installmentAudienceRows($data, $audience, $limit)
    {
        $this->loadModel('Admin.PlansPayment');
        $today = date('Y-m-d');
        $daysBefore = !empty($data['days_before']) ? (int)$data['days_before'] : 7;
        if($daysBefore < 0)
        {
            $daysBefore = 0;
        }
        if($daysBefore > 90)
        {
            $daysBefore = 90;
        }
        $conditions = array('DealsPayment.id IS NULL');
        if($audience === 'overdue_installments')
        {
            $conditions[] = array('PlansPayment.date <' => $today);
        }
        else
        {
            $conditions[] = array('PlansPayment.date >=' => $today, 'PlansPayment.date <=' => date('Y-m-d', strtotime($today.' + '.$daysBefore.' days')));
        }
        $projectId = $this->_selectedProjectId(isset($data['project_id']) ? $data['project_id'] : 0);
        if($projectId > 0)
        {
            $conditions[] = array('Property.project_id' => $projectId);
        }
        elseif(!$this->_isSuperAdminSession() && (int)$this->agencyId > 0)
        {
            $conditions[] = array('Property.project_id' => (int)$this->agencyId);
        }
        return (array)$this->PlansPayment->find('all', array(
            'fields' => array(
                'PlansPayment.id','PlansPayment.deal_id','PlansPayment.name','PlansPayment.date','PlansPayment.amount',
                'Deal.id','Deal.invoice_no','Deal.client_id',
                'Client.id','Client.name','Client.phone','Client.mobile','Client.email',
                'Property.id','Property.name','Property.project_id','Property.uniq_id',
                'Project.id','Project.name','Project.phone','Project.mobile','Project.email'
            ),
            'joins' => array(
                array('table' => 'deals', 'alias' => 'Deal', 'type' => 'INNER', 'conditions' => array('Deal.id = PlansPayment.deal_id')),
                array('table' => 'clients', 'alias' => 'Client', 'type' => 'LEFT', 'conditions' => array('Client.id = Deal.client_id')),
                array('table' => 'properties', 'alias' => 'Property', 'type' => 'LEFT', 'conditions' => array('Property.id = Deal.property_id')),
                array('table' => 'projects', 'alias' => 'Project', 'type' => 'LEFT', 'conditions' => array('Project.id = Property.project_id')),
                array('table' => 'deals_payments', 'alias' => 'DealsPayment', 'type' => 'LEFT', 'conditions' => array('DealsPayment.plans_payment_id = PlansPayment.id'))
            ),
            'conditions' => $conditions,
            'order' => array('PlansPayment.date' => 'asc', 'PlansPayment.id' => 'asc'),
            'limit' => $limit,
            'recursive' => -1
        ));
    }

    protected function _clientAudienceRows($data, $limit)
    {
        $this->loadModel('Client');
        return (array)$this->Client->find('all', array(
            'fields' => array('Client.id','Client.name','Client.phone','Client.mobile','Client.email'),
            'conditions' => array('OR' => array('Client.phone <>' => '', 'Client.mobile <>' => '', 'Client.email <>' => '')),
            'order' => array('Client.id' => 'desc'),
            'limit' => $limit,
            'recursive' => -1
        ));
    }

    protected function _leadAudienceRows($data, $limit)
    {
        $this->loadModel('Admin.Lead');
        $conditions = array('OR' => array('Lead.lead_mobile <>' => '', 'Lead.lead_email <>' => ''));
        $projectId = $this->_selectedProjectId(isset($data['project_id']) ? $data['project_id'] : 0);
        if($projectId > 0)
        {
            $conditions['Lead.project_id'] = $projectId;
        }
        return (array)$this->Lead->find('all', array(
            'fields' => array('Lead.id','Lead.project_id','Lead.lead_name','Lead.lead_mobile','Lead.lead_email'),
            'conditions' => $conditions,
            'order' => array('Lead.id' => 'desc'),
            'limit' => $limit,
            'recursive' => -1
        ));
    }

    protected function _agentAudienceRows($data, $limit)
    {
        $this->loadModel('User');
        return (array)$this->User->find('all', array(
            'fields' => array('User.id','User.name','User.phone','User.mobile','User.email'),
            'conditions' => array(
                'OR' => array('User.phone <>' => '', 'User.mobile <>' => '', 'User.email <>' => '')
            ),
            'order' => array('User.name' => 'asc'),
            'limit' => $limit,
            'recursive' => -1
        ));
    }

    protected function _customAudienceRows($text, $limit)
    {
        $rows = array();
        $lines = preg_split('/\r\n|\r|\n/', (string)$text);
        foreach($lines as $line)
        {
            if(count($rows) >= $limit)
            {
                break;
            }
            $line = trim($line);
            if($line === '')
            {
                continue;
            }
            $parts = array_map('trim', explode(',', $line));
            $rows[] = array('CustomRecipient' => array(
                'id' => 0,
                'name' => isset($parts[0]) ? $parts[0] : '',
                'phone' => isset($parts[1]) ? $parts[1] : '',
                'email' => isset($parts[2]) ? $parts[2] : ''
            ));
        }
        return $rows;
    }

    protected function _tokensFromAudienceRow($row, $data)
    {
        $tokens = $this->_sampleTokens();
        $tokens['attachment_url'] = isset($data['attachment_url']) ? trim((string)$data['attachment_url']) : '';
        if(!empty($row['Client']))
        {
            $client = (array)$row['Client'];
            $tokens['client_id'] = isset($client['id']) ? $client['id'] : '';
            $tokens['client_name'] = isset($client['name']) ? $client['name'] : '';
            $tokens['client_phone'] = !empty($client['mobile']) ? $client['mobile'] : (isset($client['phone']) ? $client['phone'] : '');
            $tokens['client_email'] = isset($client['email']) ? $client['email'] : '';
        }
        if(!empty($row['Lead']))
        {
            $lead = (array)$row['Lead'];
            $tokens['client_id'] = isset($lead['id']) ? $lead['id'] : '';
            $tokens['client_name'] = isset($lead['lead_name']) ? $lead['lead_name'] : '';
            $tokens['client_phone'] = isset($lead['lead_mobile']) ? $lead['lead_mobile'] : '';
            $tokens['client_email'] = isset($lead['lead_email']) ? $lead['lead_email'] : '';
            $tokens['lead_id'] = isset($lead['id']) ? $lead['id'] : '';
        }
        if(!empty($row['User']))
        {
            $user = (array)$row['User'];
            $tokens['client_id'] = isset($user['id']) ? $user['id'] : '';
            $tokens['client_name'] = isset($user['name']) ? $user['name'] : '';
            $tokens['client_phone'] = !empty($user['mobile']) ? $user['mobile'] : (isset($user['phone']) ? $user['phone'] : '');
            $tokens['client_email'] = isset($user['email']) ? $user['email'] : '';
            $tokens['agent_name'] = isset($user['name']) ? $user['name'] : '';
        }
        if(!empty($row['CustomRecipient']))
        {
            $custom = (array)$row['CustomRecipient'];
            $tokens['client_id'] = 0;
            $tokens['client_name'] = isset($custom['name']) ? $custom['name'] : '';
            $tokens['client_phone'] = isset($custom['phone']) ? $custom['phone'] : '';
            $tokens['client_email'] = isset($custom['email']) ? $custom['email'] : '';
        }
        if(!empty($row['Deal']))
        {
            $deal = (array)$row['Deal'];
            $tokens['deal_id'] = isset($deal['id']) ? $deal['id'] : '';
            $tokens['deal_invoice_no'] = isset($deal['invoice_no']) ? $deal['invoice_no'] : '';
        }
        if(!empty($row['PlansPayment']))
        {
            $plan = (array)$row['PlansPayment'];
            $tokens['plan_id'] = isset($plan['id']) ? $plan['id'] : '';
            $tokens['installment_name'] = isset($plan['name']) ? $plan['name'] : '';
            $tokens['due_date'] = isset($plan['date']) ? $plan['date'] : '';
            $tokens['amount'] = isset($plan['amount']) ? $plan['amount'] : '';
        }
        if(!empty($row['Property']))
        {
            $property = (array)$row['Property'];
            $tokens['property_id'] = isset($property['id']) ? $property['id'] : '';
            $tokens['property_name'] = isset($property['name']) ? $property['name'] : '';
            $tokens['property_file_no'] = !empty($property['uniq_id']) ? $property['uniq_id'] : (isset($property['name']) ? $property['name'] : '');
        }
        if(!empty($row['Project']))
        {
            $project = (array)$row['Project'];
            $tokens['agency_name'] = !empty($project['name']) ? $project['name'] : $tokens['agency_name'];
            $tokens['agency_phone'] = !empty($project['mobile']) ? $project['mobile'] : (!empty($project['phone']) ? $project['phone'] : $tokens['agency_phone']);
            $tokens['agency_email'] = !empty($project['email']) ? $project['email'] : $tokens['agency_email'];
            $tokens['project_id'] = isset($project['id']) ? $project['id'] : '';
        }
        if($tokens['client_name'] === '')
        {
            $tokens['client_name'] = __('Client');
        }
        return $tokens;
    }

    protected function _previewSourceSummary($row)
    {
        if(!empty($row['PlansPayment']))
        {
            $clientName = !empty($row['Client']['name']) ? $row['Client']['name'] : __('Client');
            $dealNo = !empty($row['Deal']['invoice_no']) ? $row['Deal']['invoice_no'] : '';
            $planName = !empty($row['PlansPayment']['name']) ? $row['PlansPayment']['name'] : __('Installment');
            return trim($clientName.' | '.$dealNo.' | '.$planName, ' |');
        }
        if(!empty($row['Client']))
        {
            return trim((!empty($row['Client']['name']) ? $row['Client']['name'] : __('Client')).' | '.(!empty($row['Client']['mobile']) ? $row['Client']['mobile'] : (isset($row['Client']['phone']) ? $row['Client']['phone'] : '')), ' |');
        }
        if(!empty($row['Lead']))
        {
            return trim((!empty($row['Lead']['lead_name']) ? $row['Lead']['lead_name'] : __('Lead')).' | '.(isset($row['Lead']['lead_mobile']) ? $row['Lead']['lead_mobile'] : ''), ' |');
        }
        if(!empty($row['User']))
        {
            return trim((!empty($row['User']['name']) ? $row['User']['name'] : __('Agent')).' | '.(!empty($row['User']['mobile']) ? $row['User']['mobile'] : (isset($row['User']['phone']) ? $row['User']['phone'] : '')), ' |');
        }
        if(!empty($row['CustomRecipient']))
        {
            return trim((!empty($row['CustomRecipient']['name']) ? $row['CustomRecipient']['name'] : __('Custom Recipient')).' | '.(isset($row['CustomRecipient']['phone']) ? $row['CustomRecipient']['phone'] : ''), ' |');
        }
        return __('Sample placeholder data');
    }

    protected function _queueRenderedTemplate($template, $tokens, $audienceRow, $data)
    {
        $channel = strtolower(trim((string)$template['channel']));
        $subject = $this->_renderWithTokens(isset($template['subject']) ? $template['subject'] : '', $tokens);
        $body = $this->_renderWithTokens(isset($template['body']) ? $template['body'] : '', $tokens);
        if(trim($body) === '')
        {
            return false;
        }
        $sourceType = 'bulk';
        $sourceId = 0;
        if(!empty($audienceRow['PlansPayment']['id']))
        {
            $sourceType = 'plans_payment';
            $sourceId = (int)$audienceRow['PlansPayment']['id'];
        }
        elseif(!empty($audienceRow['Deal']['id']))
        {
            $sourceType = 'deal';
            $sourceId = (int)$audienceRow['Deal']['id'];
        }
        elseif(!empty($audienceRow['Lead']['id']))
        {
            $sourceType = 'lead';
            $sourceId = (int)$audienceRow['Lead']['id'];
        }
        $recipientType = !empty($audienceRow['Lead']) ? 'lead' : (!empty($audienceRow['User']) ? 'user' : 'client');
        $recipientId = (int)(isset($tokens['client_id']) ? $tokens['client_id'] : 0);
        return $this->CommunicationQueue->enqueue(array(
            'project_id' => (int)(isset($tokens['project_id']) ? $tokens['project_id'] : (isset($data['project_id']) ? $data['project_id'] : 0)),
            'template_id' => (int)$template['id'],
            'channel' => $channel,
            'recipient_type' => $recipientType,
            'recipient_id' => $recipientId,
            'recipient_name' => isset($tokens['client_name']) ? $tokens['client_name'] : '',
            'recipient_mobile' => isset($tokens['client_phone']) ? $tokens['client_phone'] : '',
            'recipient_email' => isset($tokens['client_email']) ? $tokens['client_email'] : '',
            'subject' => $subject,
            'body' => $body,
            'attachment_url' => isset($tokens['attachment_url']) ? $tokens['attachment_url'] : '',
            'source_type' => $sourceType,
            'source_id' => $sourceId,
            'scheduled_at' => date('Y-m-d H:i:s'),
            'created_by' => (int)$this->luserId,
            'modified_by' => (int)$this->luserId
        ));
    }

    protected function _sampleTokens()
    {
        return array(
            'today' => date('Y-m-d'),
            'subject' => 'White Villas Notification',
            'site_name' => isset($this->siteName) ? $this->siteName : 'White Villas',
            'site_url' => isset($this->siteDomain) ? $this->siteDomain : '',
            'agency_name' => isset($this->siteName) ? $this->siteName : 'White Villas',
            'agency_phone' => isset($this->contact) ? $this->contact : '',
            'agency_email' => isset($this->siteEmail) ? $this->siteEmail : '',
            'agency_address' => isset($this->siteAddress) ? $this->siteAddress : '',
            'agency_logo_url' => '',
            'client_id' => '101',
            'client_name' => 'Sample Client',
            'client_phone' => '03000000000',
            'client_email' => 'client@example.com',
            'deal_id' => '118',
            'deal_invoice_no' => 'WV-118',
            'property_id' => '359',
            'property_name' => 'Sample Property',
            'property_file_no' => 'WV-FILE-001',
            'plan_id' => '501',
            'installment_name' => 'Installment',
            'due_date' => date('Y-m-d'),
            'amount' => '100000',
            'receipt_url' => '',
            'letter_title' => 'Issued Letter',
            'letter_status' => 'Issued',
            'letter_url' => '',
            'attachment_url' => ''
        );
    }

    protected function _renderWithTokens($text, $tokens)
    {
        $replace = array();
        foreach((array)$tokens as $key => $value)
        {
            $key = (string)$key;
            $value = (string)$value;
            $replace['{'.$key.'}'] = $value;
            $replace['{{'.strtoupper($key).'}}'] = $value;
            $replace['{{'.$key.'}}'] = $value;
            $replace['$'.$this->_camelTokenName($key)] = $value;
        }
        return strtr((string)$text, $replace);
    }

    protected function _camelTokenName($key)
    {
        $parts = explode('_', strtolower((string)$key));
        $camel = array_shift($parts);
        foreach($parts as $part)
        {
            $camel .= ucfirst($part);
        }
        return $camel;
    }

    protected function _processQueue($limit = 25)
    {
        $limit = (int)$limit;
        if($limit <= 0)
        {
            $limit = 25;
        }
        if($limit > 500)
        {
            $limit = 500;
        }
        $conditions = array(
            'CommunicationQueue.status' => 'pending',
            'OR' => array(
                'CommunicationQueue.scheduled_at IS NULL',
                'CommunicationQueue.scheduled_at <=' => date('Y-m-d H:i:s')
            )
        );
        $scope = $this->_projectScopeConditions('CommunicationQueue');
        if(!empty($scope))
        {
            $conditions[] = $scope;
        }
        $rows = (array)$this->CommunicationQueue->find('all', array(
            'conditions' => $conditions,
            'order' => array('CommunicationQueue.scheduled_at' => 'asc', 'CommunicationQueue.id' => 'asc'),
            'limit' => $limit,
            'recursive' => -1
        ));
        $result = array('sent' => 0, 'prepared' => 0, 'failed' => 0);
        foreach($rows as $row)
        {
            $delivery = $this->_processQueueRow($row);
            if(!empty($delivery['success']) && !empty($delivery['prepared']))
            {
                $result['prepared']++;
            }
            elseif(!empty($delivery['success']))
            {
                $result['sent']++;
            }
            else
            {
                $result['failed']++;
            }
        }
        return $result;
    }

    protected function _processQueueRow($row)
    {
        $queue = !empty($row['CommunicationQueue']) ? (array)$row['CommunicationQueue'] : array();
        if(empty($queue['id']))
        {
            return array('success' => false, 'message' => __('Invalid queue row.'));
        }
        $channel = strtolower(trim((string)$queue['channel']));
        $delivery = array('success' => false, 'message' => __('Unsupported channel.'));
        if($channel === 'sms')
        {
            $delivery = $this->_deliverSms($queue);
        }
        elseif($channel === 'email')
        {
            $delivery = $this->_deliverEmail($queue);
        }
        elseif($channel === 'whatsapp')
        {
            $delivery = $this->_deliverWhatsapp($queue);
        }
        elseif($channel === 'letter')
        {
            $delivery = array('success' => true, 'prepared' => true, 'delivery_url' => Router::url(array('controller' => 'Realestateletters', 'action' => 'add'), true), 'message' => __('Letter task prepared.'));
        }

        $status = !empty($delivery['success']) ? (!empty($delivery['prepared']) ? 'prepared' : 'sent') : 'failed';
        $save = array(
            'id' => (int)$queue['id'],
            'status' => $status,
            'attempts' => (int)$queue['attempts'] + 1,
            'modified_by' => (int)$this->luserId,
            'error_text' => !empty($delivery['success']) ? '' : (isset($delivery['message']) ? $delivery['message'] : '')
        );
        if(!empty($delivery['delivery_url']))
        {
            $save['delivery_url'] = $delivery['delivery_url'];
        }
        if($status === 'sent' || $status === 'prepared')
        {
            $save['sent_at'] = date('Y-m-d H:i:s');
        }
        else
        {
            $save['failed_at'] = date('Y-m-d H:i:s');
        }
        $this->CommunicationQueue->save(array('CommunicationQueue' => $save), array('validate' => false));
        $loggedRow = $row;
        $loggedRow['CommunicationQueue'] = array_merge($queue, $save);
        $this->CommunicationLog->writeFromQueue($loggedRow, $status, !empty($save['error_text']) ? $save['error_text'] : '');
        return $delivery;
    }

    protected function _deliverSms($queue)
    {
        $mobile = $this->_normalizeMobile(isset($queue['recipient_mobile']) ? $queue['recipient_mobile'] : '');
        if($mobile === '')
        {
            return array('success' => false, 'message' => __('Recipient mobile is missing.'));
        }
        if(empty($this->smsNotification))
        {
            return array('success' => false, 'message' => __('SMS notifications are disabled in settings.'));
        }
        try
        {
            $this->CustomFunction->sendSms($mobile, (string)$queue['body'], $this->smsSettingArr);
            return array('success' => true, 'message' => __('SMS sent.'));
        }
        catch(Exception $e)
        {
            return array('success' => false, 'message' => $e->getMessage());
        }
    }

    protected function _deliverEmail($queue)
    {
        $email = trim((string)(isset($queue['recipient_email']) ? $queue['recipient_email'] : ''));
        if($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL))
        {
            return array('success' => false, 'message' => __('Recipient email is missing or invalid.'));
        }
        if(empty($this->emailNotification))
        {
            return array('success' => false, 'message' => __('Email notifications are disabled in settings.'));
        }
        $body = (string)$queue['body'];
        if(!empty($queue['attachment_url']))
        {
            $body .= '<p style="font-family:Arial,Helvetica,sans-serif;font-size:14px;">'.__('Attachment / PDF').': <a href="'.h($queue['attachment_url']).'">'.h($queue['attachment_url']).'</a></p>';
        }
        try
        {
            $Email = new CakeEmail();
            $Email->transport($this->emailSettype);
            if($this->emailSettype === 'Smtp')
            {
                $Email->config(array('host' => $this->emailHost, 'port' => $this->emailPort, 'username' => $this->emailUsername, 'password' => $this->emailPassword, 'timeout' => 90));
            }
            $Email->from(array($this->siteEmail => $this->siteName));
            $Email->to($email);
            $Email->template('default');
            $Email->emailFormat('html');
            $Email->subject(trim((string)$queue['subject']) !== '' ? (string)$queue['subject'] : $this->siteName);
            $Email->send($body);
            return array('success' => true, 'message' => __('Email sent.'));
        }
        catch(Exception $e)
        {
            return array('success' => false, 'message' => $e->getMessage());
        }
    }

    protected function _deliverWhatsapp($queue)
    {
        $mobile = $this->_normalizeMobile(isset($queue['recipient_mobile']) ? $queue['recipient_mobile'] : '');
        if($mobile === '')
        {
            return array('success' => false, 'message' => __('Recipient WhatsApp number is missing.'));
        }
        $apiSettings = $this->_whatsappApiSettings();
        if(!empty($apiSettings['whatsapp_mode']) && $apiSettings['whatsapp_mode'] === 'api' && !empty($apiSettings['whatsapp_api']))
        {
            return $this->_sendWhatsappApi($mobile, (string)$queue['body'], $apiSettings);
        }
        $url = $this->_whatsappWebUrl($mobile, (string)$queue['body']);
        return array('success' => true, 'prepared' => true, 'delivery_url' => $url, 'message' => __('WhatsApp link prepared.'));
    }

    protected function _whatsappApiSettings()
    {
        try
        {
            $this->loadModel('Admin.PaymentReminderSetting');
            if(method_exists($this->PaymentReminderSetting, 'currentSettings'))
            {
                return (array)$this->PaymentReminderSetting->currentSettings();
            }
        }
        catch(Exception $e)
        {
        }
        return array();
    }

    protected function _sendWhatsappApi($mobile, $message, $settings)
    {
        try
        {
            $http = new HttpSocket(array('timeout' => 30));
            $params = array(
                !empty($settings['whatsapp_mobile_param']) ? $settings['whatsapp_mobile_param'] : 'number' => $mobile,
                !empty($settings['whatsapp_message_param']) ? $settings['whatsapp_message_param'] : 'message' => $message
            );
            if(!empty($settings['whatsapp_extra_params']))
            {
                parse_str((string)$settings['whatsapp_extra_params'], $extraParams);
                if(is_array($extraParams))
                {
                    $params = array_merge($params, $extraParams);
                }
            }
            $headers = array();
            if(!empty($settings['whatsapp_token']) && !empty($settings['whatsapp_token_header']))
            {
                $headers[$settings['whatsapp_token_header']] = $settings['whatsapp_token'];
            }
            $method = !empty($settings['whatsapp_type']) && strtolower($settings['whatsapp_type']) === 'get' ? 'get' : 'post';
            $response = ($method === 'get') ? $http->get($settings['whatsapp_api'], $params, array('header' => $headers)) : $http->post($settings['whatsapp_api'], $params, array('header' => $headers));
            if((int)$response->code >= 200 && (int)$response->code < 300)
            {
                return array('success' => true, 'message' => __('WhatsApp API sent.'));
            }
            return array('success' => false, 'message' => __('WhatsApp API failed with status').' '.$response->code);
        }
        catch(Exception $e)
        {
            return array('success' => false, 'message' => $e->getMessage());
        }
    }

    protected function _normalizeMobile($mobile)
    {
        $mobile = preg_replace('/[^0-9+]/', '', (string)$mobile);
        if($mobile === '')
        {
            return '';
        }
        if(substr($mobile, 0, 1) === '+')
        {
            $mobile = substr($mobile, 1);
        }
        if(substr($mobile, 0, 2) === '00')
        {
            $mobile = substr($mobile, 2);
        }
        if(substr($mobile, 0, 1) === '0')
        {
            $mobile = '92'.substr($mobile, 1);
        }
        return $mobile;
    }

    protected function _whatsappWebUrl($mobile, $message)
    {
        return 'https://wa.me/'.$this->_normalizeMobile($mobile).'?text='.rawurlencode((string)$message);
    }

    protected function _ensureCommunicationAutomationMenu()
    {
        try
        {
            $Page = ClassRegistry::init('Page');
            $PageRight = ClassRegistry::init('PageRight');
            $Ugroup = ClassRegistry::init('Ugroup');
            if(!$Page || !$PageRight || !$Ugroup)
            {
                return;
            }
            $root = (array)$Page->find('first', array(
                'fields' => array('Page.id'),
                'conditions' => array(
                    'LOWER(Page.controller_name)' => 'communicationautomations',
                    'LOWER(Page.action_name)' => 'index',
                    'Page.parent_id' => 0
                ),
                'recursive' => -1
            ));
            $rootData = array(
                'model_name' => 'Communication Automation',
                'page_name' => 'Communication Automation',
                'controller_name' => 'Communicationautomations',
                'action_name' => 'index',
                'icon' => 'fa fa-comments',
                'parent_id' => 0,
                'ordering' => 18,
                'published' => 'Yes',
                'sel_name' => 'communication_templates,communication_rules,communication_queue,communication_logs'
            );
            if(!empty($root['Page']['id']))
            {
                $rootData['id'] = (int)$root['Page']['id'];
            }
            $Page->create();
            $Page->save(array('Page' => $rootData), array('validate' => false));
            $rootId = !empty($rootData['id']) ? (int)$rootData['id'] : (int)$Page->id;
            if($rootId <= 0)
            {
                return;
            }
            $pages = array(
                array('page_name' => 'Automation Rules', 'action_name' => 'rules', 'icon' => 'fa fa-random', 'ordering' => 1, 'published' => 'Yes'),
                array('page_name' => 'Message Templates', 'action_name' => 'templates', 'icon' => 'fa fa-file-text-o', 'ordering' => 2, 'published' => 'Yes'),
                array('page_name' => 'Bulk Send Center', 'action_name' => 'bulk', 'icon' => 'fa fa-send', 'ordering' => 3, 'published' => 'Yes'),
                array('page_name' => 'Message Queue', 'action_name' => 'queue', 'icon' => 'fa fa-tasks', 'ordering' => 4, 'published' => 'Yes'),
                array('page_name' => 'Communication Logs', 'action_name' => 'logs', 'icon' => 'fa fa-history', 'ordering' => 5, 'published' => 'Yes'),
                array('page_name' => 'Communication Timeline', 'action_name' => 'timeline', 'icon' => 'fa fa-list-alt', 'ordering' => 6, 'published' => 'Yes'),
                array('page_name' => 'Add Communication Template', 'action_name' => 'templateadd', 'icon' => 'fa fa-plus', 'ordering' => 20, 'published' => 'No'),
                array('page_name' => 'Edit Communication Template', 'action_name' => 'templateedit', 'icon' => 'fa fa-edit', 'ordering' => 21, 'published' => 'No'),
                array('page_name' => 'Preview Communication Template', 'action_name' => 'templatepreview', 'icon' => 'fa fa-eye', 'ordering' => 22, 'published' => 'No'),
                array('page_name' => 'Change Communication Template Status', 'action_name' => 'templatestatus', 'icon' => 'fa fa-toggle-on', 'ordering' => 23, 'published' => 'No'),
                array('page_name' => 'Add Automation Rule', 'action_name' => 'ruleadd', 'icon' => 'fa fa-plus', 'ordering' => 24, 'published' => 'No'),
                array('page_name' => 'Edit Automation Rule', 'action_name' => 'ruleedit', 'icon' => 'fa fa-edit', 'ordering' => 25, 'published' => 'No'),
                array('page_name' => 'Change Automation Rule Status', 'action_name' => 'rulestatus', 'icon' => 'fa fa-toggle-on', 'ordering' => 26, 'published' => 'No'),
                array('page_name' => 'Run Communication Queue', 'action_name' => 'runqueue', 'icon' => 'fa fa-play', 'ordering' => 27, 'published' => 'No'),
                array('page_name' => 'Retry Communication Queue', 'action_name' => 'retry', 'icon' => 'fa fa-refresh', 'ordering' => 28, 'published' => 'No')
            );
            $pageIds = array($rootId);
            foreach($pages as $pageRow)
            {
                $existing = (array)$Page->find('first', array(
                    'fields' => array('Page.id'),
                    'conditions' => array(
                        'LOWER(Page.controller_name)' => 'communicationautomations',
                        'LOWER(Page.action_name)' => strtolower($pageRow['action_name'])
                    ),
                    'recursive' => -1
                ));
                $saveData = array(
                    'model_name' => 'Communication Automation',
                    'page_name' => $pageRow['page_name'],
                    'controller_name' => 'Communicationautomations',
                    'action_name' => $pageRow['action_name'],
                    'icon' => $pageRow['icon'],
                    'parent_id' => $rootId,
                    'ordering' => $pageRow['ordering'],
                    'published' => $pageRow['published'],
                    'sel_name' => 'communication_templates,communication_rules,communication_queue,communication_logs'
                );
                if(!empty($existing['Page']['id']))
                {
                    $saveData['id'] = (int)$existing['Page']['id'];
                }
                $Page->create();
                $Page->save(array('Page' => $saveData), array('validate' => false));
                $pageId = !empty($saveData['id']) ? (int)$saveData['id'] : (int)$Page->id;
                if($pageId > 0)
                {
                    $pageIds[$pageId] = $pageId;
                }
            }
            $groups = (array)$Ugroup->find('all', array('fields' => array('Ugroup.id', 'Ugroup.type', 'Ugroup.name'), 'recursive' => -1));
            foreach($groups as $groupRow)
            {
                $group = (array)$groupRow['Ugroup'];
                $groupId = !empty($group['id']) ? (int)$group['id'] : 0;
                if($groupId <= 0)
                {
                    continue;
                }
                $type = strtoupper(trim((string)(isset($group['type']) ? $group['type'] : '')));
                $name = strtoupper(trim((string)(isset($group['name']) ? $group['name'] : '')));
                $grant = ($groupId === 1 || in_array($type, array('ADR', 'AGY'), true) || preg_match('/\bSUPER\b.*\bADMIN\b/', $name));
                foreach($pageIds as $pageId)
                {
                    $existingRight = (array)$PageRight->find('first', array(
                        'fields' => array('PageRight.id'),
                        'conditions' => array('PageRight.page_id' => (int)$pageId, 'PageRight.ugroup_id' => $groupId),
                        'recursive' => -1
                    ));
                    $right = array(
                        'page_id' => (int)$pageId,
                        'ugroup_id' => $groupId,
                        'view_right' => $grant ? 1 : 0,
                        'save_right' => $grant ? 1 : 0,
                        'update_right' => $grant ? 1 : 0,
                        'search_right' => $grant ? 1 : 0
                    );
                    if(!empty($existingRight['PageRight']['id']))
                    {
                        $right['id'] = (int)$existingRight['PageRight']['id'];
                    }
                    $PageRight->create();
                    $PageRight->save(array('PageRight' => $right), array('validate' => false));
                }
            }
        }
        catch(Exception $e)
        {
        }
    }
}
?>
