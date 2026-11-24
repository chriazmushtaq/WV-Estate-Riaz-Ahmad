<?php
App::uses('SimplePasswordHasher', 'Controller/Component/Auth');
App::uses('CakeTime', 'Utility');
App::uses('CakeSession', 'Model/Datasource');
class ClientsController extends AppController
{
    var $helpers = array('Form');
    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->authenticate();
        $this->currentDateTime=CakeTime::format('Y-m-d H:i:s',CakeTime::convert(time(),$this->siteTimezone));
    }
    public function login()
    {
        if ($this->request->is('post') && empty($this->data['Client']['email']) == false)
        {
            $email = trim((string)$this->data['Client']['email']);
            $plainPassword = isset($this->request->data['Client']['password']) ? (string)$this->request->data['Client']['password'] : '';
            $user = $this->Client->find('first', array(
                'conditions' => array('Client.email' => $email),
                'recursive' => -1
            ));
            if($user != false && !empty($user['Client']['password']) && $this->_verifyPassword($plainPassword, $user['Client']['password']))
            {
                $clientStatus = !empty($user['Client']['status']) ? trim((string)$user['Client']['status']) : 'Active';
                if(strcasecmp($clientStatus, 'Active') === 0)
                {
                    if($this->_passwordNeedsRefresh($user['Client']['password']))
                    {
                        $this->_rehashPasswordForModel($this->Client, (int)$user['Client']['id'], $plainPassword);
                    }
                    $record_arr=array('Client'=>array('id'=>$user['Client']['id'],'last_login'=>$this->currentDateTime));
                    $this->Client->save($record_arr);
                    CakeSession::renew();
                    $this->Session->setFlash(__('Thank you for logging in!'),'flash', array('alert'=> 'success'));
                    $this->Session->write('frontUser', $user);                
                    $this->Redirect(array('plugin' => false, 'admin' => false, 'controller' => 'Dashboards', 'action' => 'index'));
                    exit();
                }
                else
                {
                    if (strcasecmp($clientStatus, 'Block') === 0 || strcasecmp($clientStatus, 'Blocked') === 0) {
                        $this->Session->setFlash(__('Your account has been blocked. Please contact administrator to restore login access.'),'flash', array('alert'=> 'danger'));
                    } else {
                        $this->Session->setFlash(__d('default',"You are %s Member! Please contact administrator",$clientStatus),'flash', array('alert'=> 'danger'));
                    }
                    $this->Redirect(array('action' => 'login'));
                    exit();
                }
            }
            else
            {
                $this->Session->setFlash(__('Incorrect username/password!'),'flash', array('alert'=> 'danger'));
                $this->Redirect(array('action' => 'login'));
                exit();
            }
        } 
    }
    public function logout()
    {
        $this -> Session -> destroy();
        $this -> Session -> setFlash(__('You have logged out'),'flash', array('alert'=> 'success'));
        $this -> Redirect(array('plugin' => false, 'admin' => false, 'action' => 'login'));
        exit();
    }

    public function download_apk()
    {
        $file = APP . 'tmp' . DS . 'whitevillas.apk';
        if (file_exists($file)) {
            $this->response->file($file, array('download' => true, 'name' => 'WhiteVillas.apk'));
            return $this->response;
        }
        $webrootFile = WWW_ROOT . 'files' . DS . 'whitevillas.apk';
        if (file_exists($webrootFile)) {
            $this->response->file($webrootFile, array('download' => true, 'name' => 'WhiteVillas.apk'));
            return $this->response;
        }
        $this->Session->setFlash(__('APK file not found.'), 'flash', array('alert' => 'danger'));
        return $this->redirect(array('action' => 'login'));
    }
}
