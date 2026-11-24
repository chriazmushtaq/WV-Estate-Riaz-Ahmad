<?php
App::uses('CakeEmail', 'Network/Email');
App::uses('HttpSocket', 'Network/Http');

class WebsitecontactsController extends AdminAppController
{
    public $components = array('Session','Paginator','CustomFunction');
    public $helpers = array('Html','Form','Session','Paginator');
    public $uses = array('WebsiteContact','Lead');
    public $paginate = array('WebsiteContact'=>array('limit'=>25,'order'=>array('WebsiteContact.id'=>'desc')));

    public function index()
    {
        $this->set('isSuperAdmin', $this->_isSuperAdminSession((array)$this->adminValue));
        $this->Paginator->settings = $this->paginate['WebsiteContact'];
        $contacts = $this->Paginator->paginate('WebsiteContact');
        $leadFields = array('Lead.id');
        foreach(array('property_id','user_id','lead_name','lead_mobile','lead_email','lead_source','remarks') as $leadField)
        {
            if($this->Lead->hasField($leadField))
            {
                $leadFields[] = 'Lead.'.$leadField;
            }
        }
        $leadDateField = null;
        foreach(array('created','modified','last_contact_date','follow_up') as $dateField)
        {
            if($this->Lead->hasField($dateField))
            {
                $leadDateField = $dateField;
                $leadFields[] = 'Lead.'.$dateField;
                break;
            }
        }
        $joins = array();
        if($this->Lead->hasField('property_id'))
        {
            $propertyFields = array();
            $this->loadModel('Admin.Property');
            foreach(array('id','name','uniq_id') as $propertyField)
            {
                if($this->Property->hasField($propertyField))
                {
                    $propertyFields[] = 'Property.'.$propertyField;
                }
            }
            $leadFields = array_merge($leadFields,$propertyFields);
            $joins[] = array(
                'table'=>'properties',
                'alias'=>'Property',
                'type'=>'LEFT',
                'conditions'=>array('Lead.property_id = Property.id')
            );
        }
        $leadConditions = array();
        if($this->Lead->hasField('lead_source'))
        {
            $leadConditions['Lead.lead_source'] = 'Web Inquiry';
        }
        $leadOrder = array('Lead.id'=>'desc');
        if($leadDateField !== null)
        {
            $leadOrder = array('Lead.'.$leadDateField=>'desc','Lead.id'=>'desc');
        }
        $propertyInquiries = $this->Lead->find('all',array(
            'fields'=>$leadFields,
            'joins'=>$joins,
            'conditions'=>$leadConditions,
            'order'=>$leadOrder,
            'limit'=>50,
            'recursive'=>-1
        ));
        $this->set('contacts',$contacts);
        $this->set('propertyInquiries',$propertyInquiries);
        $this->set('propertyInquiryDateField',$leadDateField);
    }

    public function mark($id=null,$status='Read')
    {
        $this->autoRender=false;
        $id=(int)$id;
        if($id>0)
        {
            $this->WebsiteContact->id=$id;
            $this->WebsiteContact->saveField('status',$status);
            $this->Session->setFlash(__('Website contact status updated.'),'flash',array('alert'=>'success'));
        }
        return $this->redirect(array('action'=>'index'));
    }

    public function reply($id=null)
    {
        $id = (int)$id;
        if(!$this->request->is('post') || $id <= 0)
        {
            throw new NotFoundException(__('Invalid request'));
        }
        $contact = $this->WebsiteContact->find('first', array(
            'conditions' => array('WebsiteContact.id' => $id),
            'recursive' => -1
        ));
        if(empty($contact))
        {
            throw new NotFoundException(__('Website contact not found'));
        }

        $reply = !empty($this->request->data['WebsiteContactReply']) ? $this->request->data['WebsiteContactReply'] : array();
        $method = strtolower(trim((string)(isset($reply['method']) ? $reply['method'] : '')));
        $message = trim((string)(isset($reply['message']) ? $reply['message'] : ''));
        if($message === '')
        {
            $this->Session->setFlash(__('Please type reply message.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action'=>'index'));
        }

        try
        {
            if($method === 'email')
            {
                $this->_sendWebsiteContactEmail($contact, $message);
                $this->_markContactRead($id);
                $this->Session->setFlash(__('Email reply has been sent.'),'flash',array('alert'=>'success'));
                return $this->redirect(array('action'=>'index'));
            }
            if($method === 'sms')
            {
                $mobileNo = trim((string)$contact['WebsiteContact']['phone']);
                if($mobileNo === '')
                {
                    $this->Session->setFlash(__('Phone number is missing for this contact.'),'flash',array('alert'=>'danger'));
                    return $this->redirect(array('action'=>'index'));
                }
                $this->_sendWebsiteContactSms($mobileNo, $message);
                $this->_markContactRead($id);
                $this->Session->setFlash(__('SMS reply has been sent.'),'flash',array('alert'=>'success'));
                return $this->redirect(array('action'=>'index'));
            }
            if($method === 'whatsapp')
            {
                $whatsappUrl = $this->_websiteContactWhatsappUrl($contact, $message);
                if($whatsappUrl === '')
                {
                    $this->Session->setFlash(__('Phone number is missing for WhatsApp reply.'),'flash',array('alert'=>'danger'));
                    return $this->redirect(array('action'=>'index'));
                }
                $this->_markContactRead($id);
                return $this->redirect($whatsappUrl);
            }
            $this->Session->setFlash(__('Please select reply method.'),'flash',array('alert'=>'danger'));
        }
        catch(Exception $e)
        {
            $this->Session->setFlash($e->getMessage(),'flash',array('alert'=>'danger'));
        }
        return $this->redirect(array('action'=>'index'));
    }

    public function edit($id=null)
    {
        if(!$this->_isSuperAdminSession((array)$this->adminValue))
        {
            $this->Session->setFlash(__('Only super admin can edit website contacts.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action'=>'index'));
        }
        $id = (int)$id;
        if($id <= 0)
        {
            throw new NotFoundException(__('Invalid website contact'));
        }
        $contact = $this->WebsiteContact->find('first', array(
            'conditions' => array('WebsiteContact.id' => $id),
            'recursive' => -1
        ));
        if(empty($contact))
        {
            throw new NotFoundException(__('Website contact not found'));
        }
        if($this->request->is(array('post','put')))
        {
            $allowedFields = array('name','email','phone','subject','message','source','status');
            $saveData = array('id' => $id);
            foreach($allowedFields as $field)
            {
                if(isset($this->request->data['WebsiteContact'][$field]))
                {
                    $saveData[$field] = $this->request->data['WebsiteContact'][$field];
                }
            }
            if($this->WebsiteContact->save(array('WebsiteContact' => $saveData), false, array_merge(array('id'), $allowedFields)))
            {
                $this->Session->setFlash(__('Website contact has been updated.'),'flash',array('alert'=>'success'));
                return $this->redirect(array('action'=>'index'));
            }
            $this->Session->setFlash(__('Website contact could not be updated.'),'flash',array('alert'=>'danger'));
        }
        else
        {
            $this->request->data = $contact;
        }
        $this->set('contact', $contact);
    }

    public function delete($id=null)
    {
        if(!$this->_isSuperAdminSession((array)$this->adminValue))
        {
            $this->Session->setFlash(__('Only super admin can delete website contacts.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action'=>'index'));
        }
        $id = (int)$id;
        if($id > 0 && $this->WebsiteContact->delete($id))
        {
            $this->Session->setFlash(__('Website contact has been deleted.'),'flash',array('alert'=>'success'));
        }
        else
        {
            $this->Session->setFlash(__('Website contact could not be deleted.'),'flash',array('alert'=>'danger'));
        }
        return $this->redirect(array('action'=>'index'));
    }

    public function leadreply($id=null)
    {
        $id = (int)$id;
        if(!$this->request->is('post') || $id <= 0)
        {
            throw new NotFoundException(__('Invalid request'));
        }
        $lead = $this->Lead->find('first', array(
            'conditions' => array('Lead.id' => $id),
            'recursive' => -1
        ));
        if(empty($lead))
        {
            throw new NotFoundException(__('Website inquiry not found'));
        }
        $reply = !empty($this->request->data['WebsiteContactReply']) ? $this->request->data['WebsiteContactReply'] : array();
        $method = strtolower(trim((string)(isset($reply['method']) ? $reply['method'] : '')));
        $message = trim((string)(isset($reply['message']) ? $reply['message'] : ''));
        if($message === '')
        {
            $this->Session->setFlash(__('Please type reply message.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action'=>'index'));
        }
        try
        {
            if($method === 'email')
            {
                $this->_sendWebsiteLeadEmail($lead, $message);
                $this->Session->setFlash(__('Email reply has been sent.'),'flash',array('alert'=>'success'));
                return $this->redirect(array('action'=>'index'));
            }
            if($method === 'sms')
            {
                $mobileNo = !empty($lead['Lead']['lead_mobile']) ? trim((string)$lead['Lead']['lead_mobile']) : '';
                if($mobileNo === '')
                {
                    $this->Session->setFlash(__('Mobile number is missing for this inquiry.'),'flash',array('alert'=>'danger'));
                    return $this->redirect(array('action'=>'index'));
                }
                $this->_sendWebsiteContactSms($mobileNo, $message);
                $this->Session->setFlash(__('SMS reply has been sent.'),'flash',array('alert'=>'success'));
                return $this->redirect(array('action'=>'index'));
            }
            if($method === 'whatsapp')
            {
                $whatsappUrl = $this->_websiteLeadWhatsappUrl($lead, $message);
                if($whatsappUrl === '')
                {
                    $this->Session->setFlash(__('Mobile number is missing for WhatsApp reply.'),'flash',array('alert'=>'danger'));
                    return $this->redirect(array('action'=>'index'));
                }
                return $this->redirect($whatsappUrl);
            }
            $this->Session->setFlash(__('Please select reply method.'),'flash',array('alert'=>'danger'));
        }
        catch(Exception $e)
        {
            $this->Session->setFlash($e->getMessage(),'flash',array('alert'=>'danger'));
        }
        return $this->redirect(array('action'=>'index'));
    }

    public function deletelead($id=null)
    {
        if(!$this->_isSuperAdminSession((array)$this->adminValue))
        {
            $this->Session->setFlash(__('Only super admin can delete website inquiries.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action'=>'index'));
        }
        $id = (int)$id;
        if($id > 0 && $this->Lead->delete($id))
        {
            $this->Session->setFlash(__('Website property inquiry has been deleted.'),'flash',array('alert'=>'success'));
        }
        else
        {
            $this->Session->setFlash(__('Website property inquiry could not be deleted.'),'flash',array('alert'=>'danger'));
        }
        return $this->redirect(array('action'=>'index'));
    }

    protected function _sendWebsiteContactEmail($contact, $message)
    {
        $toEmail = trim((string)$contact['WebsiteContact']['email']);
        if($toEmail === '')
        {
            throw new Exception(__('Email address is missing for this contact.'));
        }
        $subject = trim((string)$contact['WebsiteContact']['subject']);
        if($subject === '')
        {
            $subject = __('Website Contact Reply');
        }
        return $this->_sendWebsiteReplyEmail($toEmail, 'Re: '.$subject, $message);
    }

    protected function _websiteContactWhatsappUrl($contact, $message)
    {
        $phone = $this->_normalizeWebsiteContactPhone($contact['WebsiteContact']['phone'], true);
        if($phone === '')
        {
            return '';
        }
        return 'https://api.whatsapp.com/send?phone='.$phone.'&text='.rawurlencode($message);
    }

    protected function _sendWebsiteLeadEmail($lead, $message)
    {
        $toEmail = !empty($lead['Lead']['lead_email']) ? trim((string)$lead['Lead']['lead_email']) : '';
        if($toEmail === '')
        {
            throw new Exception(__('Email address is missing for this inquiry.'));
        }
        return $this->_sendWebsiteReplyEmail($toEmail, __('Website Property Inquiry Reply'), $message);
    }

    protected function _websiteLeadWhatsappUrl($lead, $message)
    {
        $phone = !empty($lead['Lead']['lead_mobile']) ? $this->_normalizeWebsiteContactPhone($lead['Lead']['lead_mobile'], true) : '';
        if($phone === '')
        {
            return '';
        }
        return 'https://api.whatsapp.com/send?phone='.$phone.'&text='.rawurlencode($message);
    }

    protected function _sendWebsiteReplyEmail($toEmail, $subject, $message)
    {
        $Email = new CakeEmail();
        $emailSettings = $this->_websiteContactEmailSettings($this->_currentAdminUserId());
        $emailRow = !empty($emailSettings['Emailsetting']) ? (array)$emailSettings['Emailsetting'] : array();
        $emailType = !empty($emailRow['type']) ? (string)$emailRow['type'] : (isset($this->emailSettype) ? (string)$this->emailSettype : 'Mail');
        $Email->transport($emailType);
        if($emailType == 'Smtp' || $emailType == 'SMTP')
        {
            $Email->config(array(
                'host' => isset($emailRow['host']) ? (string)$emailRow['host'] : (isset($this->emailHost) ? (string)$this->emailHost : ''),
                'port' => isset($emailRow['port']) ? (string)$emailRow['port'] : (isset($this->emailPort) ? (string)$this->emailPort : ''),
                'username' => isset($emailRow['username']) ? (string)$emailRow['username'] : (isset($this->emailUsername) ? (string)$this->emailUsername : ''),
                'password' => isset($emailRow['password']) ? (string)$emailRow['password'] : (isset($this->emailPassword) ? (string)$this->emailPassword : ''),
                'timeout' => 25
            ));
        }
        $Email->from(array($this->siteEmail => $this->siteName));
        $Email->to($toEmail);
        $Email->emailFormat('html');
        $Email->subject($subject);
        return $this->_sendWebsiteContactCakeEmail($Email, nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8')));
    }

    protected function _sendWebsiteContactSms($mobileNo, $message)
    {
        if(empty($this->smsNotification))
        {
            throw new Exception(__('Sms notification is disabled.'));
        }
        $smsArr = isset($this->smsSettingArr) ? (array)$this->smsSettingArr : array();
        if(empty($smsArr['Smssetting']['api']))
        {
            $Smssetting = ClassRegistry::init('Admin.Smssetting');
            if($Smssetting)
            {
                $smsArr = (array)$Smssetting->findById(1);
            }
        }
        $smsRow = !empty($smsArr['Smssetting']) ? (array)$smsArr['Smssetting'] : array();
        $url = isset($smsRow['api']) ? trim((string)$smsRow['api']) : '';
        if($url === '')
        {
            throw new Exception(__('SMS API URL is missing.'));
        }
        $query = array(
            $this->_websiteSmsParamName($smsRow, 'husername', 'username') => isset($smsRow['username']) ? (string)$smsRow['username'] : '',
            $this->_websiteSmsParamName($smsRow, 'hpassword', 'password') => isset($smsRow['password']) ? (string)$smsRow['password'] : '',
            $this->_websiteSmsParamName($smsRow, 'hsenderid', 'sender') => isset($smsRow['senderid']) ? (string)$smsRow['senderid'] : '',
            $this->_websiteSmsParamName($smsRow, 'hmobile', 'mobile') => (string)$mobileNo,
            $this->_websiteSmsParamName($smsRow, 'hmessage', 'message') => (string)$message,
            'smstype' => 'TRANS'
        );
        $HttpSocket = new HttpSocket(array('timeout' => 12));
        try
        {
            if(strtolower(trim((string)(isset($smsRow['type']) ? $smsRow['type'] : 'Get'))) === 'get')
            {
                $response = $HttpSocket->get($url, $query);
            }
            else
            {
                $response = $HttpSocket->post($url, $query);
            }
        }
        catch(Exception $e)
        {
            throw new Exception(__('SMS could not be sent with invoice SMS settings: ').$e->getMessage());
        }
        if($response === false)
        {
            throw new Exception(__('SMS gateway did not return a response.'));
        }
        if(isset($response->code) && (int)$response->code >= 400)
        {
            throw new Exception(sprintf(__('SMS gateway returned HTTP %s.'), (int)$response->code));
        }
        return true;
    }

    protected function _websiteSmsParamName($smsRow, $field, $fallback)
    {
        if(isset($smsRow[$field]) && trim((string)$smsRow[$field]) !== '')
        {
            return trim((string)$smsRow[$field]);
        }
        return $fallback;
    }

    protected function _websiteContactSmsSettings($userId=0)
    {
        $globalSmsGateway = isset($this->smsSettingArr) ? (array)$this->smsSettingArr : array();
        if(empty($globalSmsGateway['Smssetting']['api']))
        {
            try
            {
                $Smssetting = ClassRegistry::init('Admin.Smssetting');
                if($Smssetting)
                {
                    $globalSmsGateway = (array)$Smssetting->findById(1);
                }
            }
            catch(Exception $e)
            {
            }
        }
        $userId = (int)$userId;
        if($userId > 0)
        {
            try
            {
                $CrmIntegration = ClassRegistry::init('CrmIntegration');
                if($CrmIntegration)
                {
                    $CrmIntegration->ensureSchema();
                    $bundle = (array)$CrmIntegration->effectiveSmsGatewayForUser($userId, $globalSmsGateway);
                    if(!empty($bundle['settings']) && $this->_websiteContactSmsGatewayUsable((array)$bundle['settings']))
                    {
                        return (array)$bundle['settings'];
                    }
                }
            }
            catch(Exception $e)
            {
            }
        }
        if($this->_websiteContactSmsGatewayUsable($globalSmsGateway))
        {
            return $globalSmsGateway;
        }
        $fallbackGateway = $this->_websiteContactFallbackSmsSettings();
        if(!empty($fallbackGateway))
        {
            return $fallbackGateway;
        }
        return $globalSmsGateway;
    }

    protected function _websiteContactFallbackSmsSettings()
    {
        try
        {
            $CrmUserSmsSetting = ClassRegistry::init('CrmUserSmsSetting');
            if(!$CrmUserSmsSetting)
            {
                return array();
            }
            $CrmUserSmsSetting->schema(true);
            $row = (array)$CrmUserSmsSetting->find('first', array(
                'conditions' => array(
                    'CrmUserSmsSetting.use_global' => 0,
                    'CrmUserSmsSetting.api !=' => ''
                ),
                'order' => array('CrmUserSmsSetting.id' => 'desc'),
                'recursive' => -1
            ));
            if(empty($row['CrmUserSmsSetting']))
            {
                return array();
            }
            return array(
                'Smssetting' => array(
                    'api' => isset($row['CrmUserSmsSetting']['api']) ? (string)$row['CrmUserSmsSetting']['api'] : '',
                    'senderid' => isset($row['CrmUserSmsSetting']['senderid']) ? (string)$row['CrmUserSmsSetting']['senderid'] : '',
                    'username' => isset($row['CrmUserSmsSetting']['username']) ? (string)$row['CrmUserSmsSetting']['username'] : '',
                    'password' => isset($row['CrmUserSmsSetting']['password']) ? (string)$row['CrmUserSmsSetting']['password'] : '',
                    'husername' => isset($row['CrmUserSmsSetting']['husername']) ? (string)$row['CrmUserSmsSetting']['husername'] : '',
                    'hpassword' => isset($row['CrmUserSmsSetting']['hpassword']) ? (string)$row['CrmUserSmsSetting']['hpassword'] : '',
                    'hmobile' => isset($row['CrmUserSmsSetting']['hmobile']) ? (string)$row['CrmUserSmsSetting']['hmobile'] : '',
                    'hmessage' => isset($row['CrmUserSmsSetting']['hmessage']) ? (string)$row['CrmUserSmsSetting']['hmessage'] : '',
                    'hsenderid' => isset($row['CrmUserSmsSetting']['hsenderid']) ? (string)$row['CrmUserSmsSetting']['hsenderid'] : '',
                    'type' => isset($row['CrmUserSmsSetting']['type']) ? (string)$row['CrmUserSmsSetting']['type'] : 'Get'
                )
            );
        }
        catch(Exception $e)
        {
            return array();
        }
    }

    protected function _websiteContactSmsGatewayUsable($settings)
    {
        $smsRow = !empty($settings['Smssetting']) ? (array)$settings['Smssetting'] : array();
        $api = isset($smsRow['api']) ? trim((string)$smsRow['api']) : '';
        if($api === '')
        {
            return false;
        }
        $host = parse_url($api, PHP_URL_HOST);
        if($host === null || $host === false || trim((string)$host) === '')
        {
            return false;
        }
        $host = strtolower(trim((string)$host));
        if($host === 'sms.alriazdevelopers.com')
        {
            return false;
        }
        if(!filter_var($host, FILTER_VALIDATE_IP) && gethostbyname($host) === $host)
        {
            return false;
        }
        return true;
    }

    protected function _websiteContactEmailSettings($userId=0)
    {
        $globalEmailGateway = array(
            'Emailsetting' => array(
                'type' => isset($this->emailSettype) ? (string)$this->emailSettype : 'Mail',
                'host' => isset($this->emailHost) ? (string)$this->emailHost : '',
                'port' => isset($this->emailPort) ? (string)$this->emailPort : '',
                'username' => isset($this->emailUsername) ? (string)$this->emailUsername : '',
                'password' => isset($this->emailPassword) ? (string)$this->emailPassword : ''
            )
        );
        $userId = (int)$userId;
        if($userId > 0)
        {
            try
            {
                $CrmIntegration = ClassRegistry::init('CrmIntegration');
                if($CrmIntegration)
                {
                    $CrmIntegration->ensureSchema();
                    $bundle = (array)$CrmIntegration->effectiveEmailGatewayForUser($userId, $globalEmailGateway);
                    if(!empty($bundle['settings']))
                    {
                        return (array)$bundle['settings'];
                    }
                }
            }
            catch(Exception $e)
            {
            }
        }
        return $globalEmailGateway;
    }

    protected function _sendWebsiteContactCakeEmail($Email, $body)
    {
        $oldReporting = error_reporting();
        error_reporting($oldReporting & ~E_DEPRECATED & ~E_USER_DEPRECATED);
        try
        {
            $result = $Email->send($body);
            error_reporting($oldReporting);
            return $result;
        }
        catch(Exception $e)
        {
            error_reporting($oldReporting);
            throw new Exception(__('Could not send email: ').$e->getMessage());
        }
    }

    protected function _currentAdminUserId()
    {
        $sessionUser = (array)$this->Session->read('User');
        if(!empty($sessionUser['User']['id']))
        {
            return (int)$sessionUser['User']['id'];
        }
        if(!empty($this->luserId))
        {
            return (int)$this->luserId;
        }
        return 0;
    }

    protected function _normalizeWebsiteContactPhone($phone, $forWhatsapp=false)
    {
        $digits = preg_replace('/[^0-9]/', '', (string)$phone);
        if($digits === '')
        {
            return '';
        }
        if($forWhatsapp && strlen($digits) == 11 && substr($digits, 0, 1) === '0')
        {
            $digits = '92'.substr($digits, 1);
        }
        return $digits;
    }

    protected function _markContactRead($id)
    {
        $this->WebsiteContact->id = (int)$id;
        return $this->WebsiteContact->saveField('status', 'Read');
    }
}
