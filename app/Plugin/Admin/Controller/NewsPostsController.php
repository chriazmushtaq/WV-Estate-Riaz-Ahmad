<?php
class NewsPostsController extends AdminAppController
{
    public $helpers = array('Html','Form','Session','Paginator','Tinymce','Js'=>array('Jquery'));
    public $components = array('Session','Paginator','search-master.Prg');
    public $uses = array('NewsPost','NewsCategory');
    public $presetVars = true;
    public $paginate = array('page'=>1,'order'=>array('NewsPost.id'=>'desc'));

    public function index()
    {
        $this->Prg->commonProcess();
        $this->Paginator->settings = $this->paginate;
        $this->Paginator->settings['conditions'] = $this->NewsPost->parseCriteria($this->Prg->parsedParams());
        $this->Paginator->settings['limit'] = $this->pageLimit;
        $this->Paginator->settings['maxLimit'] = $this->maxLimit;
        $this->Paginator->settings['recursive'] = 0;
        $this->set('posts', $this->Paginator->paginate('NewsPost'));
        if($this->request->is('ajax'))
        {
            $this->render('index','ajax');
        }
    }

    public function add()
    {
        $this->_setFormVars();
        if($this->request->is('post'))
        {
            try
            {
                $data = $this->_preparePostData($this->request->data);
                $this->NewsPost->create();
                if($this->NewsPost->save($data))
                {
                    $this->Session->setFlash(__('News post has been saved.'),'flash',array('alert'=>'success'));
                    return $this->redirect(array('action'=>'index'));
                }
                $this->Session->setFlash(__('News post could not be saved.'),'flash',array('alert'=>'danger'));
            }
            catch(Exception $e)
            {
                $this->Session->setFlash($e->getMessage(),'flash',array('alert'=>'danger'));
            }
        }
    }

    public function edit($id=null)
    {
        $this->_setFormVars();
        $id = (int)$id;
        if($id<=0)
        {
            throw new NotFoundException(__('Invalid news post'));
        }
        $row = $this->NewsPost->find('first',array('conditions'=>array('NewsPost.id'=>$id),'recursive'=>0));
        if(empty($row))
        {
            throw new NotFoundException(__('Invalid news post'));
        }
        if($this->request->is(array('post','put')))
        {
            try
            {
                $data = $this->_preparePostData($this->request->data, $id, $row);
                if($this->NewsPost->save($data))
                {
                    $this->Session->setFlash(__('News post has been updated.'),'flash',array('alert'=>'success'));
                    return $this->redirect(array('action'=>'index'));
                }
                $this->Session->setFlash(__('News post could not be updated.'),'flash',array('alert'=>'danger'));
            }
            catch(Exception $e)
            {
                $this->Session->setFlash($e->getMessage(),'flash',array('alert'=>'danger'));
            }
        }
        else
        {
            $this->request->data = $row;
        }
        $this->set('post',$row);
    }

    public function status($id=null,$status='Published')
    {
        $id = (int)$id;
        if($id>0 && in_array($status,array('Published','Draft','Unpublished'),true))
        {
            $save = array('id'=>$id,'status'=>$status);
            if($status==='Published')
            {
                $save['published_at'] = date('Y-m-d H:i:s');
            }
            $this->NewsPost->save(array('NewsPost'=>$save),false,array_keys($save));
            $this->Session->setFlash(__('Post status updated.'),'flash',array('alert'=>'success'));
        }
        return $this->redirect(array('action'=>'index'));
    }

    public function deleteall()
    {
        if($this->request->is('post') && !empty($this->request->data['NewsPost']['id']))
        {
            foreach((array)$this->request->data['NewsPost']['id'] as $id)
            {
                $row = $this->NewsPost->findById((int)$id);
                if(!empty($row['NewsPost']['thumbnail']))
                {
                    $this->_deleteThumbnail($row['NewsPost']['thumbnail']);
                }
                $this->NewsPost->delete((int)$id);
            }
            $this->Session->setFlash(__('Selected posts have been deleted.'),'flash',array('alert'=>'success'));
        }
        return $this->redirect(array('action'=>'index'));
    }

    protected function _setFormVars()
    {
        $categories = $this->NewsCategory->find('list',array(
            'fields'=>array('NewsCategory.id','NewsCategory.name'),
            'conditions'=>array('NewsCategory.status'=>'Published'),
            'order'=>array('NewsCategory.sort_order'=>'asc','NewsCategory.name'=>'asc')
        ));
        $this->set('categories',$categories);
        $this->set('statusOptions',array('Draft'=>__('Draft'),'Published'=>__('Published'),'Unpublished'=>__('Unpublished')));
    }

    protected function _preparePostData($data,$id=null,$existing=array())
    {
        $row = isset($data['NewsPost']) ? (array)$data['NewsPost'] : array();
        if($id!==null)
        {
            $row['id'] = (int)$id;
        }
        $row['title'] = isset($row['title']) ? trim((string)$row['title']) : '';
        $row['slug'] = !empty($row['slug']) ? $this->_slug($row['slug']) : $this->_slug($row['title']);
        $row['status'] = !empty($row['status']) ? $row['status'] : 'Draft';
        $row['is_featured'] = !empty($row['is_featured']) ? 1 : 0;
        $row['allow_comments'] = !empty($row['allow_comments']) ? 1 : 0;
        $row['news_category_id'] = !empty($row['news_category_id']) ? (int)$row['news_category_id'] : null;
        if($row['status']==='Published' && empty($row['published_at']))
        {
            $row['published_at'] = date('Y-m-d H:i:s');
        }
        $uploaded = $this->_uploadThumbnail(isset($row['thumbnail_file']) ? $row['thumbnail_file'] : array());
        unset($row['thumbnail_file']);
        if($uploaded!=='')
        {
            if(!empty($existing['NewsPost']['thumbnail']))
            {
                $this->_deleteThumbnail($existing['NewsPost']['thumbnail']);
            }
            $row['thumbnail'] = $uploaded;
        }
        elseif(!empty($existing['NewsPost']['thumbnail']))
        {
            $row['thumbnail'] = $existing['NewsPost']['thumbnail'];
        }
        return array('NewsPost'=>$row);
    }

    protected function _uploadThumbnail($file)
    {
        if(empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name']))
        {
            return '';
        }
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if(!in_array($ext,array('jpg','jpeg','png','gif','webp'),true))
        {
            throw new Exception(__('Invalid thumbnail image type.'));
        }
        $dir = WWW_ROOT.'img'.DS.'news'.DS;
        if(!is_dir($dir))
        {
            mkdir($dir,0775,true);
        }
        $name = 'news-'.date('YmdHis').'-'.mt_rand(1000,9999).'.'.$ext;
        if(!move_uploaded_file($file['tmp_name'],$dir.$name))
        {
            throw new Exception(__('Thumbnail could not be uploaded.'));
        }
        $this->_recordUploadInMediaGallery('news_post', 0, $name, 'news', array(
            'original_name' => $file['name'],
            'title' => sprintf(__('News Thumbnail - %s'), $file['name'])
        ));
        return $name;
    }

    protected function _deleteThumbnail($name)
    {
        $path = WWW_ROOT.'img'.DS.'news'.DS.basename((string)$name);
        if(is_file($path))
        {
            @unlink($path);
        }
    }

    protected function _slug($text)
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/','-',(string)$text),'-'));
        return $slug!=='' ? $slug : 'news-post';
    }
}
?>
