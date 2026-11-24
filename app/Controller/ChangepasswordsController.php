<?php
App::uses('SimplePasswordHasher', 'Controller/Component/Auth');
class ChangepasswordsController extends AppController
{
    public $helpers = array('Html', 'Form','Session');
    public $components = array('Session');
    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->authenticate();
        $this->userId=$this->userValue['Client']['id'];
    }
    public function index()
    {
       if ($this->request->is(array('post', 'put')))
        {
            $userValue=$this->Changepassword->findById($this->userId);
            $oldPassword = isset($this->request->data['Changepassword']['oldPassword']) ? (string)$this->request->data['Changepassword']['oldPassword'] : '';
            $newPassword = isset($this->request->data['Changepassword']['password']) ? (string)$this->request->data['Changepassword']['password'] : '';
            if(!empty($userValue['Changepassword']['password']) && $this->_verifyPassword($oldPassword, $userValue['Changepassword']['password']))
            {
                if($this->Changepassword->save(array('id'=>$this->userId,'password'=>$newPassword)))
                {
                    $this->Session->setFlash(__('Password Changed Successfully'),'flash',array('alert'=>'success'));
                }
            }
            else
            {
                $this->Session->setFlash(__('Invalid Password.'),'flash',array('alert'=>'danger'));
            }
            $this->redirect(array('action' => 'index'));
        }   
    }
}
