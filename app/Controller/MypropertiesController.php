<?php
class MypropertiesController extends AppController
{
    protected $_portalVisibleDealStatuses = array('Active','Blocked');
    protected function _visibleDealConditions($baseConditions=array())
    {
        $conditions = $baseConditions;
        if($this->Deal->hasField('deal_status'))
        {
            $conditions[] = array(
                'OR'=>array(
                    array('Deal.deal_status'=>null),
                    array('Deal.deal_status'=>''),
                    array('Deal.deal_status'=>$this->_portalVisibleDealStatuses)
                )
            );
        }
        return $conditions;
    }
    protected function _isClientProperty($propertyId)
    {
        $propertyId = (int)$propertyId;
        if($propertyId<=0)
        {
            return false;
        }
        $conditions = $this->_visibleDealConditions(array('Deal.client_id'=>$this->userId,'Deal.property_id'=>$propertyId));
        return $this->Deal->find('count',array('conditions'=>$conditions,'recursive'=>-1))>0;
    }
    protected function _resolveClientPropertyId($id)
    {
        $id = (int)$id;
        if($id<=0)
        {
            return null;
        }
        if($this->_isClientProperty($id))
        {
            return $id;
        }
        $conditions = $this->_visibleDealConditions(array('Deal.client_id'=>$this->userId,'Deal.id'=>$id));
        $deal = $this->Deal->find('first',array(
            'fields'=>array('Deal.property_id'),
            'conditions'=>$conditions,
            'recursive'=>-1
        ));
        if(!empty($deal['Deal']['property_id']))
        {
            return (int)$deal['Deal']['property_id'];
        }
        return null;
    }
    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->authenticate();
        $this->userId=$this->userValue['Client']['id'];
        $this->loadModel('Deal');
        $dealConditions=$this->_visibleDealConditions(array('Deal.client_id'=>$this->userId));
        $dealCount=$this->Deal->find('count',array('conditions'=>$dealConditions));
        if($dealCount==0)
        {
            $this->Session->setFlash(__('Invalid Post.'),'flash',array('alert'=>'danger'));
            $this->redirect(array('controller'=>'Dashboards','action' => 'index'));
        }
    }
    public function index($id=null)
    {
        $this->layout = null;
        $propertyId = $this->_resolveClientPropertyId($id);
        if(!$propertyId)
        {
            $this->Session->setFlash(__('Invalid Post.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('controller'=>'Dashboards','action' => 'index'));
        }
        $this->loadModel('Property');
        $post = $this->Property->findById($propertyId);
        $this->set('post',$post);
    }
    public function photo($id=null)
    {
        $this->layout = null;
        $propertyId = $this->_resolveClientPropertyId($id);
        if(!$propertyId)
        {
            $this->Session->setFlash(__('Invalid Post.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('controller'=>'Dashboards','action' => 'index'));
        }
        $this->loadModel('PropertiesPhoto');
        $propertiesPhoto=$this->PropertiesPhoto->findAllByPropertyId($propertyId);
        $this->set('PropertiesPhoto',$propertiesPhoto);
    }
    public function photoview($id = null)
    {
        $this->layout = null;
        $this->loadModel('PropertiesPhoto');
        $post = $this->PropertiesPhoto->findById($id);
        if (!$post)
        {
            $this->Session->setFlash(__('Invalid Post.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('controller'=>'Dashboards','action' => 'index'));
        }
        if(!$this->_isClientProperty($post['PropertiesPhoto']['property_id']))
        {
            $this->Session->setFlash(__('Invalid Post.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('controller'=>'Dashboards','action' => 'index'));
        }
        $photoImg=$post['PropertiesPhoto']['dir'].'/'.$post['PropertiesPhoto']['photo'];
        $this->set('photoImg',$photoImg);
        $this->set('id',$post['PropertiesPhoto']['property_id']);   
    }
    public function document($id=null)
    {
        $this->layout = null;
        $propertyId = $this->_resolveClientPropertyId($id);
        if(!$propertyId)
        {
            $this->Session->setFlash(__('Invalid Post.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('controller'=>'Dashboards','action' => 'index'));
        }
        $this->loadModel('PropertiesDocument');
        $propertiesDocument=$this->PropertiesDocument->findAllByPropertyId($propertyId);
        $this->set('PropertiesDocument',$propertiesDocument);
    }
    public function documentview($id = null)
    {
        $this->layout = null;
        $this->loadModel('PropertiesDocument');
        $post = $this->PropertiesDocument->findById($id);
        if (!$post)
        {
            $this->Session->setFlash(__('Invalid Post.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('controller'=>'Dashboards','action' => 'index'));
        }
        if(!$this->_isClientProperty($post['PropertiesDocument']['property_id']))
        {
            $this->Session->setFlash(__('Invalid Post.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('controller'=>'Dashboards','action' => 'index'));
        }
        $photoImg=$post['PropertiesDocument']['dir'].'/'.$post['PropertiesDocument']['photo'];
        $this->set('photoImg',$photoImg);
        $this->set('id',$post['PropertiesDocument']['property_id']);
    }
}
