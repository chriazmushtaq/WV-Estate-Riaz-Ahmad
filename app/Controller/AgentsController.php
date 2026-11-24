<?php
App::uses('CakeTime', 'Utility');
App::uses('CakeEmail', 'Network/Email');
ini_set('max_execution_time', 300);

class AgentsController extends AppController
{
    public $helpers = array('Html', 'Form','Session','Paginator','Js'=> array('Jquery'));
    public $components = array('Session','Paginator','search-master.Prg','CustomFunction');
    public $presetVars = true;
    var $paginate = array('joins'=>array(array('table'=>'ugroups','alias'=>'Ugroup','type'=>'LEFT','conditions'=>array('Ugroup.id=Agent.ugroup_id')),
                                         array('table'=>'properties','alias'=>'Property','type'=>'LEFT','conditions'=>array('Property.user_id=Agent.id'))),
                          'page'=>1,'order'=>array('Agent.name'=>'asc'),'group'=>array('Agent.id'));
    protected function _ensureFrontViewColumn()
    {
        $this->Agent->schema(true);
        return $this->Agent->hasField('front_view');
    }
    protected function _ensureSortIdColumn()
    {
        $this->Agent->schema(true);
        if($this->Agent->hasField('sort_id'))
        {
            return true;
        }
        $afterColumn = $this->_ensureFrontViewColumn() ? 'front_view' : 'status';
        try
        {
            $this->Agent->query("ALTER TABLE `users` ADD `sort_id` INT(11) NOT NULL DEFAULT 0 AFTER `".$afterColumn."`");
            $this->Agent->schema(true);
        }
        catch(Exception $e)
        {
            $this->Agent->schema(true);
        }
        return $this->Agent->hasField('sort_id');
    }
    protected function _agentSortOrder($alias='Agent')
    {
        $alias = preg_replace('/[^A-Za-z0-9_]/', '', (string)$alias);
        if($alias === '')
        {
            $alias = 'Agent';
        }
        return array(
            '(CASE WHEN '.$alias.'.sort_id > 0 THEN 0 ELSE 1 END)' => 'asc',
            $alias.'.sort_id' => 'asc',
            $alias.'.name' => 'asc',
            $alias.'.id' => 'asc'
        );
    }
    protected function _frontViewCondition($alias='Agent')
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
        if($this->_ensureSortIdColumn())
        {
            $this->Paginator->settings['order']=$this->_agentSortOrder('Agent');
        }
	$this->Agent->virtualFields= array('total_count' => 'Count(Property.id)');
        $this->Paginator->settings['fields']=array('Agent.*','total_count');
        $conditions=array('Ugroup.id'=>2,'Agent.status'=>'Active');
        $frontViewCondition=$this->_frontViewCondition('Agent');
        if(!empty($frontViewCondition))
        {
            $conditions[]=$frontViewCondition;
        }
        $this->Paginator->settings['conditions'] = $conditions;
        $this->Paginator->settings['limit']=$this->pageLimit;
        $this->Paginator->settings['maxLimit']=$this->maxLimit;
        $this->set('agent', $this->Paginator->paginate());
        if ($this->request->is('ajax'))
        {
            $this->render('index','ajax'); // View, Layout
        }
    }
    public function view($id=null)
    {  
        $this->loadModel('User');
        $conditions=array('User.id'=>$id,'User.ugroup_id'=>2,'User.status'=>'Active');
        $frontViewCondition=$this->_frontViewCondition('User');
        if(!empty($frontViewCondition))
        {
            $conditions[]=$frontViewCondition;
        }
        $post = $this->User->find('first',array('conditions'=>$conditions,'fields'=>array('User.*')));
        if(!$post)
        {
            $this->Session->setFlash(__('Invalid Post'),'flash', array('alert'=> 'danger'));
            $this->redirect(array('controller' => 'Properties', 'action' => 'index'));
        }
        $this->set('agent',$post);
	$this->loadModel('Property');
        $propertyConditions=array('Property.user_id'=>(int)$id,'Property.published'=>'Yes');
        $assignedPropertyCount=(int)$this->Property->find('count',array('conditions'=>$propertyConditions));
	$allProperty=$this->Property->find('all',array('conditions'=>$propertyConditions,'order'=>array('Property.id'=>'desc'),'limit'=>8));
        $this->set('assignedPropertyCount',$assignedPropertyCount);
	$this->set('allProperty',$allProperty);
    }
    public function mail($id=null)
    {
	try
	{
	    $this->loadModel('User');
	    $post = $this->User->findById($id);
	    $email=$post['User']['email'];
	    if(!$post)
	    {
		$this->Session->setFlash(__('Invalid Post'),'flash', array('alert'=> 'danger'));
		$this->redirect(array('controller' => 'Agents', 'action' => 'index'));
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
	    $this->Session->setFlash('E-mail successfully sent','flash',array('alert'=>'success'));
	    return $this->redirect(array('action' => 'view/',$id));           
	}
	catch(Exception $e)
	{
	    $this->Session->setFlash($e->getMessage(),'flash',array('alert'=>'danger'));
	    return $this->redirect(array('action' => 'view/',$id));
	}
    }
}
