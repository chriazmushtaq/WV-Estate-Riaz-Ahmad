<?php
App::uses('CakeTime', 'Utility');
App::uses('CakeEmail', 'Network/Email');
ini_set('max_execution_time', 300);

class AgenciesController extends AppController
{
    public $helpers = array('Html', 'Form','Session','Paginator','Js'=> array('Jquery'));
    public $components = array('Session','Paginator','search-master.Prg','CustomFunction');
    public $presetVars = true;
    var $paginate = array('page'=>1,'order'=>array('Agency.name'=>'asc'));
    protected function _ensureFrontViewColumn()
    {
        $this->loadModel('User');
        $this->User->schema(true);
        return $this->User->hasField('front_view');
    }
    protected function _frontViewCondition($alias='User')
    {
        if(!$this->_ensureFrontViewColumn())
        {
            return array();
        }
        return array('OR'=>array(
            array($alias.'.front_view'=>'On'),
            array($alias.'.front_view'=>null),
            array($alias.'.front_view'=>'')
        ));
    }
    public function index()
    {
        $this->Prg->commonProcess();
        $this->Paginator->settings = $this->paginate;
	$this->Paginator->settings['limit']=$this->pageLimit;
        $this->Paginator->settings['maxLimit']=$this->maxLimit;
	$conditions=array('User.ugroup_id'=>4,'User.status'=>'Active');
	$frontViewCondition=$this->_frontViewCondition('User');
	if(!empty($frontViewCondition))
	{
	    $conditions[]=$frontViewCondition;
	}
	$this->Paginator->settings['conditions']=$conditions;
        $this->set('Agency', $this->Paginator->paginate());
        if ($this->request->is('ajax'))
        {
            $this->render('index','ajax'); // View, Layout
        }
    }
    public function view($id=null)
    {  
        $this->loadModel('Agency');
        $conditions=array('Agency.id'=>$id,'User.ugroup_id'=>4,'User.status'=>'Active');
        $frontViewCondition=$this->_frontViewCondition('User');
        if(!empty($frontViewCondition))
        {
            $conditions[]=$frontViewCondition;
        }
        $post = $this->Agency->find('first',array('conditions'=>$conditions));
        if(!$post)
        {
            $this->Session->setFlash(__('Invalid Post'),'flash', array('alert'=> 'danger'));
            $this->redirect(array('controller' => 'Agencies', 'action' => 'index'));
        }
        $this->set('Agency',$post);
	$this->loadModel('Property');
        $agencyId=(int)$id;
        $propertyConditions=array('Property.project_id'=>$agencyId,'Property.published'=>'Yes');
        $agencyPropertyCount=(int)$this->Property->find('count',array('conditions'=>$propertyConditions));
	$allProperty=$this->Property->find('all',array('conditions'=>$propertyConditions,'order'=>array('Property.id'=>'desc'),'limit'=>9));
        $this->loadModel('User');
        $agentConditions=array('User.ugroup_id'=>2,'User.status'=>'Active');
        $membershipCondition=$this->User->projectMembershipCondition($agencyId,'User');
        if(!empty($membershipCondition))
        {
            $agentConditions[]=$membershipCondition;
        }
        $agentFrontViewCondition=$this->_frontViewCondition('User');
        if(!empty($agentFrontViewCondition))
        {
            $agentConditions[]=$agentFrontViewCondition;
        }
        $agencyAgentCount=(int)$this->User->find('count',array('conditions'=>$agentConditions,'recursive'=>-1));
        $agencyAgents=$this->User->find('all',array('conditions'=>$agentConditions,'order'=>array('User.name'=>'asc','User.id'=>'asc'),'limit'=>12,'recursive'=>-1));
        $this->set('agencyPropertyCount',$agencyPropertyCount);
        $this->set('agencyAgentCount',$agencyAgentCount);
        $this->set('agencyAgents',$agencyAgents);
	$this->set('allProperty',$allProperty);
    }
    public function mail($id=null)
    {
	try
	{
	    $this->loadModel('Agency');
	    $post = $this->Agency->findById($id);
	    $email=$post['Agency']['email'];
	    if(!$post)
	    {
		$this->Session->setFlash(__('Invalid Post'),'flash', array('alert'=> 'danger'));
		$this->redirect(array('controller' => 'Agencies', 'action' => 'index'));
	    }       
	    $message='<p>'.__('Name').': '.$this->request->data['name'].'</p>'.
	    '<p>'.__('Email').': '.$this->request->data['email'].'</p>'.
	    '<p>'.__('Subject').': '.$this->request->data['subject'].'</p>'.
	    '<p>'.__('Message').': '.$this->request->data['message'].'</p>';
	    $Email = new CakeEmail();
	    $Email->transport($this->emailSettype);
	    if($this->emailSettype=="Smtp")
	    $Email->config(array('host' => $this->emailHost,'port' =>  $this->emailPort,'username' => $this->emailUsername,'password' => $this->emailPassword,'timeout'=>90));
	    $Email->from(array($this->siteEmail =>$this->siteName));
	    $Email->to($email);
	    $Email->template('default');
	    $Email->emailFormat('html');
	    $Email->subject($this->request->data['subject']);
	    $Email->send($message);
	    /* End Email */
	    $this->Session->setFlash(__('E-mail successfully sent'),'flash',array('alert'=>'success'));
	    return $this->redirect(array('action' => 'view/',$id));           
	}
	catch(Exception $e)
	{
	    $this->Session->setFlash($e->getMessage(),'flash',array('alert'=>'danger'));
	    return $this->redirect(array('action' => 'view/',$id));
	}
    }
}
