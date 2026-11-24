<?php
class EmailsettingsController extends AdminAppController {
    public $helpers = array('Html', 'Form','Session');
    public $components = array('Session','search-master.Prg');
    public function index()
    {
        
        $id=1;        
        $post = $this->Emailsetting->findById($id); 
        if ($this->request->is('post'))
        {
            $this->Emailsetting->id = $id;
            try
            {
                if($this->request->data['Emailsetting']['type']=="Mail")
                {
                    $this->request->data['Emailsetting']['host']=NULL;
                    $this->request->data['Emailsetting']['username']=NULL;
                    $this->request->data['Emailsetting']['password']=NULL;
                    $this->request->data['Emailsetting']['port']=NULL;
                }
                else
                {
                    if(isset($this->request->data['Emailsetting']['host'])) $this->request->data['Emailsetting']['host'] = trim($this->request->data['Emailsetting']['host']);
                    if(isset($this->request->data['Emailsetting']['username'])) $this->request->data['Emailsetting']['username'] = trim($this->request->data['Emailsetting']['username']);
                    if(isset($this->request->data['Emailsetting']['port'])) $this->request->data['Emailsetting']['port'] = trim($this->request->data['Emailsetting']['port']);
                    if(isset($this->request->data['Emailsetting']['password'])) $this->request->data['Emailsetting']['password'] = trim($this->request->data['Emailsetting']['password']);
                }
                if ($this->Emailsetting->save($this->request->data))
                {
                    $this->Session->setFlash(__('Your Setting has been saved successfully. Plain text SMTP password is saved in database.'),'flash',array('alert'=>'success'));
                    return $this->redirect(array('action' => 'index'));
                }
            }
            catch (Exception $e)
            {
                $this->Session->setFlash(__('Setting Problem: %s', $e->getMessage()),'flash',array('alert'=>'danger'));
                return $this->redirect(array('action' => 'index'));
            }
        }
        if (!$this->request->data)
        {
            $this->request->data = $post;
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

        try
        {
            App::uses('CakeEmail', 'Network/Email');
            $setting = $this->Emailsetting->findById(1);
            $s = !empty($setting['Emailsetting']) ? $setting['Emailsetting'] : array();

            // If credentials were submitted via the form, test those directly
            if(!empty($this->request->data['Emailsetting']))
            {
                $formData = $this->request->data['Emailsetting'];
                if(!empty($formData['type'])) $s['type'] = trim($formData['type']);
                if(isset($formData['host'])) $s['host'] = trim($formData['host']);
                if(isset($formData['port'])) $s['port'] = trim($formData['port']);
                if(isset($formData['username'])) $s['username'] = trim($formData['username']);
                if(isset($formData['password'])) $s['password'] = trim($formData['password']);
            }

            $cfg = ClassRegistry::init('Configuration')->findById(1);
            $siteName = !empty($cfg['Configuration']['name']) ? $cfg['Configuration']['name'] : 'White Villas';
            $siteEmail = !empty($cfg['Configuration']['email']) ? $cfg['Configuration']['email'] : 'info@whitevillas.com.pk';

            $Email = new CakeEmail();
            $transport = (!empty($s['type'])) ? $s['type'] : 'Smtp';
            $Email->transport($transport);

            if($transport === 'Smtp')
            {
                $port = !empty($s['port']) ? (int)$s['port'] : 465;
                $smtpConfig = array(
                    'host' => !empty($s['host']) ? $s['host'] : 'localhost',
                    'port' => $port,
                    'username' => !empty($s['username']) ? $s['username'] : '',
                    'password' => !empty($s['password']) ? $s['password'] : '',
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

            $smtpSender = !empty($s['username']) ? trim($s['username']) : $siteEmail;
            $actualFrom = ($transport === 'Smtp' && !empty($smtpSender)) ? $smtpSender : $siteEmail;
            $Email->from(array($actualFrom => $siteName));
            if(!empty($siteEmail) && strtolower($siteEmail) !== strtolower($actualFrom))
            {
                $Email->replyTo($siteEmail, $siteName);
            }

            $Email->to($to);
            $Email->template('default');
            $Email->emailFormat('html');
            $Email->subject("SMTP Test from Email Settings - " . date('d-M-Y H:i:s'));
            $Email->send("<h3>SMTP Connection Test Passed!</h3><p>Your SMTP mail configuration is verified and fully operational.</p>");

            // If test passed and form data was submitted, persist to database
            if(!empty($this->request->data['Emailsetting']))
            {
                $this->Emailsetting->id = 1;
                $this->Emailsetting->save($this->request->data);
            }

            $this->Session->setFlash(__("Test email sent successfully to %s! SMTP credentials verified and active.", $to), 'flash', array('alert' => 'success'));
        }
        catch(Throwable $e)
        {
            $this->Session->setFlash(__("SMTP Test Failed: %s", $e->getMessage()), 'flash', array('alert' => 'danger'));
        }
        return $this->redirect(array('action' => 'index'));
    }   
}
