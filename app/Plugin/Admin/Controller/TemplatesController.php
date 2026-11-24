<?php
class TemplatesController extends AdminAppController {
    public $helpers = array('Html', 'Form','Session','Paginator','Tinymce','Js'=> array('Jquery'));
    public $components = array('Session','Paginator','search-master.Prg');
    public $presetVars = true;
    var $paginate = array('page'=>1,'order'=>array('Template.name'=>'asc'));
    public function beforeFilter()
    {
	parent::beforeFilter();
        if ($this->_isSuperAdminSession((array)$this->adminValue))
        {
            $this->userPermissionArr = $this->_defaultFullPermission();
            $this->set('userPermissionArr', $this->userPermissionArr);
            $this->set('allowAddAction', true);
            $this->set('allowEditAction', true);
            $this->set('allowDeleteAction', true);
            $this->set('allowViewAction', true);
        }
	$this->templateTypeArr=array(
	    'PRO' =>__('Print Property'),
	    'EMA'=>__('Email Property'),
        'RLT_WELCOME'=>__('Letter: Welcome'),
        'RLT_BOOKING'=>__('Letter: Booking'),
	    'RLT_ALLOT'=>__('Letter: Allotment'),
	    'RLT_CHARGE'=>__('Letter: Deal Charges Notice'),
	    'RLT_DEMAND'=>__('Letter: Payment Demand'),
	    'RLT_RECEIPT'=>__('Letter: Payment Acknowledgement'),
	    'RLT_POSSESSION'=>__('Letter: Possession'),
	    'RLT_TRANSFER'=>__('Letter: Transfer / Mutation'),
	    'RLT_CANCEL'=>__('Letter: Cancellation'),
        'RLT_REFUND'=>__('Letter: Refund Agreement'),
        'RLT_REFUND_CLEARANCE'=>__('Letter: Refund Clearance / No Claim'),
        'RLT_RENTAL_AGREEMENT'=>__('Letter: Rental Agreement'),
        'RLT_RENTAL_RECEIPT'=>__('Letter: Rental Receipt'),
        'RLT_DEMARCATION'=>__('Letter: Demarcation'),
        'RLT_DESIGN_MAP_APPROVAL'=>__('Letter: Design / Map Approval'),
        'RLT_NOC'=>__('Letter: NOC')
	);
    }    

    protected function _redirectToRealestateTemplates()
    {
        $this->Session->setFlash(__('Please manage letter templates from Real Estate Letters templates.'),'flash',array('alert'=>'info'));
        return $this->redirect(array('controller'=>'Realestateletters','action'=>'templates'));
    }

    protected function _prepareTemplateRequestData()
    {
        if(isset($this->request->data['Template']) && is_array($this->request->data['Template']))
        {
            unset($this->request->data['Template']['description_visual']);
            if(isset($this->request->data['Template']['description']))
            {
                $this->request->data['Template']['description'] = (string)$this->request->data['Template']['description'];
            }
            if(empty($this->request->data['Template']['status']))
            {
                $this->request->data['Template']['status'] = 'Published';
            }
        }
        foreach((array)$this->request->data as $key => $row)
        {
            if(is_array($row) && isset($row['Template']) && is_array($row['Template']))
            {
                unset($this->request->data[$key]['Template']['description_visual']);
                if(isset($this->request->data[$key]['Template']['description']))
                {
                    $this->request->data[$key]['Template']['description'] = (string)$this->request->data[$key]['Template']['description'];
                }
                if(empty($this->request->data[$key]['Template']['status']))
                {
                    $this->request->data[$key]['Template']['status'] = 'Published';
                }
            }
        }
    }

    protected function _isFullHtmlDocument($html)
    {
        $html = trim((string)$html);
        if($html === '')
        {
            return false;
        }
        $prefix = strtolower(substr($html, 0, 600));
        return (strpos($prefix, '<!doctype html') !== false || strpos($prefix, '<html') !== false || (strpos($prefix, '<head') !== false && strpos($prefix, '<body') !== false));
    }

    public function index()
    {
        return $this->_redirectToRealestateTemplates();
	$this->set('typeArr',$this->templateTypeArr);
	$this->Prg->commonProcess();
        $this->Paginator->settings = $this->paginate;
        $this->Paginator->settings['conditions'] = $this->Template->parseCriteria($this->Prg->parsedParams());
	$this->Paginator->settings['limit']=$this->pageLimit;
        $this->Paginator->settings['maxLimit']=$this->maxLimit;
        $this->set('Template', $this->Paginator->paginate());     
	if ($this->request->is('ajax'))
        {
            $this->render('index','ajax'); // View, Layout
        }
    }    
    public function add()
    {
        return $this->_redirectToRealestateTemplates();
	$this->set('typeArr',$this->templateTypeArr);
        if ($this->request->is('post'))
        {
            $this->Template->create();
            $this->_prepareTemplateRequestData();
            if(empty($this->request->data['Template']['is_new']))
            {
                $this->request->data['Template']['is_new'] = 'Yes';
            }
            try
            {
                if ($this->Template->save($this->request->data))
                {
                    $this->Session->setFlash(__('Template has been saved.'),'flash',array('alert'=>'success'));
                    return $this->redirect(array('action' => 'add'));
                }
            }
            catch (Exception $e)
            {
                $this->Session->setFlash(__('Invalid Post','flash'),array('alert'=>'danger'));
                return $this->redirect(array('action' => 'index'));
            }
        }
    }

    public function templateadd()
    {
        return $this->_redirectToRealestateTemplates();
        $result = $this->add();
        if($result)
        {
            return $result;
        }
        $this->render('add');
    }

    public function edit($id = null)
    {
        return $this->_redirectToRealestateTemplates();
	$this->set('typeArr',$this->templateTypeArr);
        if (!$id)
        {
            throw new NotFoundException(__('Invalid post'));
        }
        $ids=explode(",",$id);
        $post=array();
        foreach($ids as $id)
        {
            $post[]=$this->Template->findByid($id);
        }
        $this->set('Template',$post);
        if (!$post)
        {
            throw new NotFoundException(__('Invalid post'));
        }
        if ($this->request->is(array('post', 'put')))
        {
            $this->_prepareTemplateRequestData();
            try
            {
                if ($this->Template->saveAll($this->request->data))
                {
                    $this->Session->setFlash(__('Template has been saved.'),'flash',array('alert'=>'success'));
                    return $this->redirect(array('action' => 'index'));
                }
            }
            catch (Exception $e)
            {
                $this->Session->setFlash(__('Invalid Post.'),'flash',array('alert'=>'danger'));
                return $this->redirect(array('action' => 'index'));
            }
            $this->set('isError',true);
        }
        else
        {
            $this->layout = 'tinymce-absolute';
            $this->set('isError',false);
        }
        if (!$this->request->data)
        {
            $this->request->data = $post;
        }
    }    

    public function templateedit($id = null)
    {
        return $this->_redirectToRealestateTemplates();
        return $this->edit($id);
    }
    public function deleteall()
    {
        return $this->_redirectToRealestateTemplates();
        try
        {
            if ($this->request->is('post'))
            {
                foreach($this->data['Template']['id'] as $key => $value)
                {
                    $this->Template->deleteAll(array('Template.id'=>$value,'Template.is_new'=>'Yes'));
                }
                $this->Session->setFlash(__('Template has been deleted.'),'flash',array('alert'=>'success'));
            }        
            $this->redirect(array('action' => 'index'));
        }
        catch (Exception $e)
        {
            $this->Session->setFlash(__('Please delete related record first.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'index'));
        }
    }
    public function view($id = null)
    {
        return $this->_redirectToRealestateTemplates();
        $this->layout = null;
        if (!$id)
        {
            $this->Session->setFlash(__('Invalid Post.'),'flash',array('alert'=>'danger'));
            $this->redirect(array('action' => 'index'));
        }
	$this->set('typeArr',$this->templateTypeArr);
        $post = $this->Template->findById($id);
        $this->set('post',$post);
        $this->set('isFullHtmlTemplate', !empty($post['Template']['description']) && $this->_isFullHtmlDocument($post['Template']['description']));
    }
}

