<?php
App::uses('CakeEmail', 'Network/Email');
ini_set('max_execution_time', 900);
class SendemailsController extends AdminAppController
{
    public $helpers = array('Html', 'Form','Session');
    public $components = array('Session');
    public function index()
    {
        $this->loadModel('Emailsetting');
        $this->loadModel('Client');
        $this->loadModel('Lead');
        $this->loadModel('Agent');
        $EmailTemplate = ClassRegistry::init('Admin.Emailtemplate');
        if($EmailTemplate && method_exists($EmailTemplate, 'ensureOwnershipSchema'))
        {
            $EmailTemplate->ensureOwnershipSchema();
        }
        $sessionUser = (array)$this->Session->read('User');
        $isSuperAdmin = $this->_isSuperAdminSession($sessionUser);
        $currentUserId = !empty($sessionUser['User']['id']) ? (int)$sessionUser['User']['id'] : (int)$this->luserId;
        $templateConditions = array('Emailtemplate.status' => 'Published');
        if($EmailTemplate && method_exists($EmailTemplate, 'scopedCustomConditions'))
        {
            $templateConditions[] = $EmailTemplate->scopedCustomConditions($currentUserId, $isSuperAdmin, 'Emailtemplate');
        }
        else
        {
            $templateConditions[] = array(
                'OR' => array(
                    array('Emailtemplate.type' => null),
                    array('Emailtemplate.type' => '')
                )
            );
        }
        $emailTemplateList = $EmailTemplate ? $EmailTemplate->find('list',array('fields'=>array('description','name'),'conditions'=>$templateConditions,'order'=>array('name'=>'asc'))) : array();
        $this->set('emailTemplate',$emailTemplateList);
        if(!$this->request->is('post'))
        {
            try
            {
                $CrmIntegration = ClassRegistry::init('CrmIntegration');
                if($CrmIntegration)
                {
                    $CrmIntegration->ensureSchema();
                    $defaultTemplateId = (int)$CrmIntegration->defaultEmailTemplateIdForUser($currentUserId);
                    if($defaultTemplateId > 0 && $EmailTemplate)
                    {
                        $defaultTemplateRow = (array)$EmailTemplate->find('first', array(
                            'fields' => array('Emailtemplate.id', 'Emailtemplate.name', 'Emailtemplate.description'),
                            'conditions' => array_merge(array('Emailtemplate.id' => $defaultTemplateId, 'Emailtemplate.status' => 'Published'), (array)$templateConditions),
                            'recursive' => -1
                        ));
                        if(!empty($defaultTemplateRow['Emailtemplate']['description']))
                        {
                            $this->request->data['Sendemail']['email_template'] = (string)$defaultTemplateRow['Emailtemplate']['description'];
                            $this->request->data['Sendemail']['message'] = (string)$defaultTemplateRow['Emailtemplate']['description'];
                            $this->request->data['Sendemail']['subject'] = !empty($defaultTemplateRow['Emailtemplate']['name']) ? (string)$defaultTemplateRow['Emailtemplate']['name'] : '';
                        }
                    }
                }
            }
            catch(Exception $e)
            {
            }
        }
        $emailSettingArr=$this->Emailsetting->findById(1);
        $settingType=$emailSettingArr['Emailsetting']['type'];
        if($this->request->is('post'))
        {
            try
            {
                $type=$this->request->data['Sendemail']['type'];
                $emailTemplate=$this->request->data['Sendemail']['email_template'];
                $clientId=$this->request->data['Sendemail']['client_id'];
                $leadId=$this->request->data['Sendemail']['lead_id'];
                $agentId=$this->request->data['Sendemail']['agent_id'];
                $anyEmail=$this->request->data['Sendemail']['any_email'];
                $subject=$this->request->data['Sendemail']['subject'];
                $message=$this->request->data['Sendemail']['message'];
                if($type==null)
                {
                    $this->Session->setFlash(__('Please select any type in the list.'),'flash',array('alert'=>'danger'));
                }
                elseif($type=='Any' && $anyEmail==null)
                {
                    $this->Session->setFlash(__('Please type any email.'),'flash',array('alert'=>'danger'));
                }
                else
                {
                    $toEmailArr=null;
                    if($type=="Client" && $clientId!=null)
                    {
                        $toEmailArr=explode(",",$clientId);
                    }
                    if($type=="Client" && $clientId==null)
                    {
                        $typeArr=$this->Client->find('all',array('fields'=>array('Client.email')));
                        foreach($typeArr as $value)
                        $toEmailArr[]=$value['Client']['email'];
                        unset($value);
                    }
                    if($type=="Lead" && $leadId!=null)
                    {
                        $toEmailArr=explode(",",$leadId);
                        
                    }
                    if($type=="Lead" && $leadId==null)
                    {
                        $typeArr=$this->Lead->find('all',array('joins'=>array(array('table'=>'clients','alias'=>'Client','type'=>'LEFT','conditions'=>array('Lead.client_id=Client.id'))),
                                                               'fields'=>array('Client.email'),
                                                               'conditions' => array('Client.email <>'=>'')));
                        foreach($typeArr as $value)
                        $toEmailArr[]=$value['Client']['email'];
                        unset($value);
                        
                    }
                    if($type=="Agent" && $agentId!=null)
                    {
                       $toEmailArr=explode(",",$agentId);
                        
                    }
                    if($type=="Agent" && $agentId==null)
                    {
                        $typeArr=$this->Agent->find('all',array('fields'=>array('Agent.email')));
                        foreach($typeArr as $value)
                        $toEmailArr[]=$value['Agent']['email'];
                        unset($value);                        
                    }
                    if($type=="Any")
                    {
                        $toEmailArr=explode(",",$anyEmail);
                        
                    }
                    if($toEmailArr)
                    {
                        $emailSettingArr = $this->Emailsetting->findById(1);
                        $cfg = ClassRegistry::init('Configuration')->findById(1);
                        $siteName = !empty($cfg['Configuration']['name']) ? $cfg['Configuration']['name'] : 'White Villas';
                        $siteEmail = !empty($cfg['Configuration']['email']) ? $cfg['Configuration']['email'] : 'info@whitevillas.com.pk';

                        $successCount = 0;
                        $failCount = 0;
                        $errors = array();

                        foreach($toEmailArr as $toEmail)
                        {
                            $toEmail = trim($toEmail);
                            if(!empty($toEmail))
                            {
                                $sendRes = $this->_sendCrmEmail($toEmail, $subject, $message, $emailSettingArr, $siteName, $siteEmail);
                                if(!empty($sendRes['success']))
                                {
                                    $successCount++;
                                }
                                else
                                {
                                    $failCount++;
                                    $errors[] = "{$toEmail}: " . (!empty($sendRes['error']) ? $sendRes['error'] : 'Send failed');
                                }
                            }
                        }

                        if($successCount > 0 && $failCount === 0)
                        {
                            $this->Session->setFlash(__("Email has been sent successfully to %d recipient(s).", $successCount),'flash',array('alert'=>'success'));
                            return $this->redirect(array('action' => 'index'));
                        }
                        elseif($successCount > 0 && $failCount > 0)
                        {
                            $this->Session->setFlash(__("Sent to %d recipient(s), but %d failed: %s", $successCount, $failCount, implode('; ', array_slice($errors, 0, 3))),'flash',array('alert'=>'warning'));
                            return $this->redirect(array('action' => 'index'));
                        }
                        else
                        {
                            $errStr = !empty($errors) ? implode('; ', array_slice($errors, 0, 3)) : 'Failed to send email.';
                            $this->Session->setFlash($errStr,'flash',array('alert'=>'danger'));
                            return $this->redirect(array('action' => 'index'));
                        }
                    }
                    else
                    {
                        $this->Session->setFlash(__('No email to send!'),'flash',array('alert'=>'danger'));
                        return $this->redirect(array('action' => 'index'));
                    }
                }
            }
            catch (Throwable $e)
            {
                $this->Session->setFlash($e->getMessage(),'flash',array('alert'=>'danger'));
                return $this->redirect(array('action' => 'index'));
            }
        }
    }

    public function test_email()
    {
        $to = !empty($this->request->data['test_email']) ? trim($this->request->data['test_email']) : '';
        if(empty($to))
        {
            $to = !empty($this->request->query['test_email']) ? trim($this->request->query['test_email']) : '';
        }
        if(empty($to))
        {
            $sessionUser = (array)$this->Session->read('User');
            $to = !empty($sessionUser['User']['email']) ? trim($sessionUser['User']['email']) : 'admin@whitealpinelodges.com';
        }

        $subj = "SMTP Verification Test - " . date('d-M-Y H:i:s');
        $msg = "<h3>SMTP Test Successful</h3><p>Your SMTP mail configuration is active and working properly over secure SSL port 465.</p>";
        $res = $this->_sendCrmEmail($to, $subj, $msg);

        if($this->request->is('ajax'))
        {
            $this->autoRender = false;
            header('Content-Type: application/json');
            echo json_encode($res);
            exit();
        }

        if(!empty($res['success']))
        {
            $this->Session->setFlash(__("Test email sent successfully to %s!", $to), 'flash', array('alert' => 'success'));
        }
        else
        {
            $errMsg = !empty($res['error']) ? $res['error'] : 'Unknown error';
            $this->Session->setFlash(__("SMTP Test Failed to %s: %s", $to, $errMsg), 'flash', array('alert' => 'danger'));
        }
        return $this->redirect(array('action' => 'index'));
    }

    protected function _sendCrmEmail($toEmail, $subject, $message, $emailSettingArr = null, $siteName = null, $siteEmail = null)
    {
        try
        {
            $toEmail = trim((string)$toEmail);
            if(empty($toEmail) || !filter_var($toEmail, FILTER_VALIDATE_EMAIL))
            {
                return array('success' => false, 'error' => "Invalid email address: {$toEmail}");
            }

            if(empty($emailSettingArr))
            {
                $this->loadModel('Emailsetting');
                $emailSettingArr = $this->Emailsetting->findById(1);
            }
            $setting = !empty($emailSettingArr['Emailsetting']) ? $emailSettingArr['Emailsetting'] : array();

            if(empty($siteName) || empty($siteEmail))
            {
                $cfg = ClassRegistry::init('Configuration')->findById(1);
                $siteName = !empty($cfg['Configuration']['name']) ? $cfg['Configuration']['name'] : 'White Villas';
                $siteEmail = !empty($cfg['Configuration']['email']) ? $cfg['Configuration']['email'] : 'info@whitevillas.com.pk';
            }

            $transportType = (!empty($setting['type'])) ? $setting['type'] : 'Smtp';
            $Email = new CakeEmail();
            $Email->transport($transportType);

            $smtpSender = !empty($setting['username']) ? trim($setting['username']) : $siteEmail;

            if($transportType === 'Smtp')
            {
                $host = !empty($setting['host']) ? trim($setting['host']) : 'localhost';
                $port = !empty($setting['port']) ? (int)$setting['port'] : 465;
                $username = !empty($setting['username']) ? trim($setting['username']) : '';
                $password = !empty($setting['password']) ? $setting['password'] : '';

                $smtpConfig = array(
                    'host' => $host,
                    'port' => $port,
                    'username' => $username,
                    'password' => $password,
                    'timeout' => 15
                );

                if($port === 465)
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
                elseif($port === 587)
                {
                    $smtpConfig['tls'] = true;
                }

                $Email->config($smtpConfig);
            }

            $Email->charset('utf-8');
            $Email->headerCharset('utf-8');

            $actualFrom = ($transportType === 'Smtp' && !empty($smtpSender)) ? $smtpSender : $siteEmail;
            $Email->from(array($actualFrom => $siteName));
            if(!empty($siteEmail) && strtolower($siteEmail) !== strtolower($actualFrom))
            {
                $Email->replyTo($siteEmail, $siteName);
            }

            $Email->to($toEmail);
            $Email->template('default');
            $Email->emailFormat('html');

            $cleanSubj = preg_replace('/[\x{1F300}-\x{1F6FF}\x{1F900}-\x{1F9FF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}]/u', '', (string)$subject);
            $cleanSubj = trim(preg_replace('/\s+/', ' ', $cleanSubj));
            $Email->subject($cleanSubj);

            $Email->send($message);
            CakeLog::write('info', "CRM Email sent successfully to {$toEmail} | Subject: {$cleanSubj}");
            return array('success' => true);
        }
        catch(Throwable $e)
        {
            CakeLog::write('error', "CRM Email failed to {$toEmail}: " . $e->getMessage());
            return array('success' => false, 'error' => $e->getMessage());
        }
    }
    public function clientsearch()
    {
        $this->autoRender = false;
        $this->request->onlyAllow('ajax');
        // get the search term from URL
        $this->loadModel('Client');
        $term = $this->request->query['q'];
        $users = $this->Client->find('all',array('conditions' => array('Client.name LIKE' => '%'.$term.'%','Client.email <>'=>'')));
        // Format the result for select2
        $result = array();
        foreach($users as $key => $user)
        {
            $result[$key]['id'] = $user['Client']['email'];
            $result[$key]['text'] = $user['Client']['name'];
        }
        $users = $result;        
        echo json_encode($users);
    }
    public function leadsearch()
    {
        $this->autoRender = false;
        $this->request->onlyAllow('ajax');
        // get the search term from URL
        $this->loadModel('Lead');
        $term = $this->request->query['q'];
        $users = $this->Lead->find('all',array('joins'=>array(array('table'=>'clients','alias'=>'Client','type'=>'LEFT','conditions'=>array('Lead.client_id=Client.id'))),
                                               'fields'=>array('Client.name','Client.email'),
                                               'conditions' => array('Client.name LIKE' => '%'.$term.'%','Client.email <>'=>'')));
        // Format the result for select2
        $result = array();
        foreach($users as $key => $user)
        {
            $result[$key]['id'] =   $user['Client']['email'];
            $result[$key]['text'] = $user['Client']['name'];
        }
        $users = $result;        
        echo json_encode($users);
    }
    public function agentsearch()
    {
        $this->autoRender = false;
        $this->request->onlyAllow('ajax');
        // get the search term from URL
        $this->loadModel('Agent');
        $term = $this->request->query['q'];
        $users = $this->Agent->find('all',array('conditions' => array('Agent.name LIKE' => '%'.$term.'%','Agent.ugroup_id'=>2,'Agent.email <>'=>'')));
        // Format the result for select2
        $result = array();
        foreach($users as $key => $user)
        {
            $result[$key]['id'] = $user['Agent']['email'];
            $result[$key]['text'] = $user['Agent']['name'];
        }
        $users = $result;        
        echo json_encode($users);
    }
}
