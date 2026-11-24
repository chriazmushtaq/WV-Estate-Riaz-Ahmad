<?php
class NewsCategoriesController extends AdminAppController
{
    public $helpers = array('Html','Form','Session','Paginator','Js'=>array('Jquery'));
    public $components = array('Session','Paginator','search-master.Prg');
    public $uses = array('NewsCategory');
    public $presetVars = true;
    public $paginate = array('page'=>1,'order'=>array('NewsCategory.sort_order'=>'asc','NewsCategory.name'=>'asc'));

    public function index()
    {
        $this->Prg->commonProcess();
        $this->Paginator->settings = $this->paginate;
        $this->Paginator->settings['conditions'] = $this->NewsCategory->parseCriteria($this->Prg->parsedParams());
        $this->Paginator->settings['limit'] = $this->pageLimit;
        $this->Paginator->settings['maxLimit'] = $this->maxLimit;
        $this->set('categories', $this->Paginator->paginate('NewsCategory'));
        if($this->request->is('ajax'))
        {
            $this->render('index','ajax');
        }
    }

    public function add()
    {
        if($this->request->is('post'))
        {
            $data = $this->_prepareCategoryData($this->request->data);
            $this->NewsCategory->create();
            if($this->NewsCategory->save($data))
            {
                $this->Session->setFlash(__('News category has been saved.'),'flash',array('alert'=>'success'));
                return $this->redirect(array('action'=>'index'));
            }
            $this->Session->setFlash(__('News category could not be saved.'),'flash',array('alert'=>'danger'));
        }
    }

    public function edit($id=null)
    {
        $id = (int)$id;
        if($id<=0)
        {
            throw new NotFoundException(__('Invalid category'));
        }
        $row = $this->NewsCategory->findById($id);
        if(empty($row))
        {
            throw new NotFoundException(__('Invalid category'));
        }
        if($this->request->is(array('post','put')))
        {
            $data = $this->_prepareCategoryData($this->request->data, $id);
            if($this->NewsCategory->save($data))
            {
                $this->Session->setFlash(__('News category has been updated.'),'flash',array('alert'=>'success'));
                return $this->redirect(array('action'=>'index'));
            }
            $this->Session->setFlash(__('News category could not be updated.'),'flash',array('alert'=>'danger'));
        }
        else
        {
            $this->request->data = $row;
        }
    }

    public function status($id=null,$status='Published')
    {
        $id = (int)$id;
        if($id>0 && in_array($status,array('Published','Draft','Unpublished'),true))
        {
            $this->NewsCategory->id = $id;
            $this->NewsCategory->saveField('status',$status);
            $this->Session->setFlash(__('Category status updated.'),'flash',array('alert'=>'success'));
        }
        return $this->redirect(array('action'=>'index'));
    }

    public function deleteall()
    {
        if($this->request->is('post') && !empty($this->request->data['NewsCategory']['id']))
        {
            foreach((array)$this->request->data['NewsCategory']['id'] as $id)
            {
                $this->NewsCategory->delete((int)$id);
            }
            $this->Session->setFlash(__('Selected categories have been deleted.'),'flash',array('alert'=>'success'));
        }
        return $this->redirect(array('action'=>'index'));
    }

    protected function _prepareCategoryData($data,$id=null)
    {
        $row = isset($data['NewsCategory']) ? (array)$data['NewsCategory'] : array();
        if($id!==null)
        {
            $row['id'] = (int)$id;
        }
        $row['name'] = isset($row['name']) ? trim((string)$row['name']) : '';
        $row['slug'] = isset($row['slug']) ? trim((string)$row['slug']) : '';
        if($row['slug']==='')
        {
            $row['slug'] = $this->_slug($row['name']);
        }
        else
        {
            $row['slug'] = $this->_slug($row['slug']);
        }
        $row['status'] = !empty($row['status']) ? $row['status'] : 'Published';
        $row['sort_order'] = isset($row['sort_order']) ? (int)$row['sort_order'] : 0;
        return array('NewsCategory'=>$row);
    }

    protected function _slug($text)
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/','-',(string)$text),'-'));
        return $slug!=='' ? $slug : 'category';
    }
}
?>
