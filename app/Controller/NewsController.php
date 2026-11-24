<?php
class NewsController extends AppController
{
    public $helpers = array('Html','Form','Session','Paginator','Text');
    public $components = array('Session','Paginator');
    public $uses = array('NewsPost','NewsCategory');
    public $paginate = array(
        'NewsPost' => array(
            'limit' => 9,
            'order' => array('NewsPost.published_at'=>'desc','NewsPost.id'=>'desc'),
            'recursive' => 0
        )
    );

    public function index()
    {
        $conditions = array('NewsPost.status'=>'Published');
        $categorySlug = isset($this->request->query['category']) ? trim((string)$this->request->query['category']) : '';
        $selectedCategory = array();
        if($categorySlug !== '')
        {
            $selectedCategory = $this->NewsCategory->find('first',array(
                'conditions'=>array('NewsCategory.slug'=>$categorySlug,'NewsCategory.status'=>'Published'),
                'recursive'=>-1
            ));
            if(!empty($selectedCategory))
            {
                $conditions['NewsPost.news_category_id'] = (int)$selectedCategory['NewsCategory']['id'];
            }
        }
        $this->Paginator->settings = $this->paginate['NewsPost'];
        $this->Paginator->settings['conditions'] = $conditions;
        $posts = $this->Paginator->paginate('NewsPost');
        $this->_setSidebarData();
        $this->set(compact('posts','selectedCategory'));
    }

    public function view($slug=null)
    {
        $slug = trim((string)$slug);
        if($slug === '')
        {
            throw new NotFoundException(__('Invalid news post'));
        }
        $post = $this->NewsPost->find('first',array(
            'conditions'=>array('NewsPost.slug'=>$slug,'NewsPost.status'=>'Published'),
            'recursive'=>0
        ));
        if(empty($post))
        {
            throw new NotFoundException(__('Invalid news post'));
        }
        $this->NewsPost->id = (int)$post['NewsPost']['id'];
        $this->NewsPost->saveField('views',(int)$post['NewsPost']['views']+1);
        $this->_setSidebarData((int)$post['NewsPost']['id']);
        $this->set('post',$post);
    }

    protected function _setSidebarData($excludePostId=0)
    {
        $categories = $this->NewsCategory->find('all',array(
            'conditions'=>array('NewsCategory.status'=>'Published'),
            'order'=>array('NewsCategory.sort_order'=>'asc','NewsCategory.name'=>'asc'),
            'recursive'=>-1
        ));
        $recentConditions = array('NewsPost.status'=>'Published');
        if((int)$excludePostId>0)
        {
            $recentConditions['NewsPost.id !='] = (int)$excludePostId;
        }
        $recentPosts = $this->NewsPost->find('all',array(
            'conditions'=>$recentConditions,
            'order'=>array('NewsPost.published_at'=>'desc','NewsPost.id'=>'desc'),
            'limit'=>5,
            'recursive'=>0
        ));
        $featuredPosts = $this->NewsPost->find('all',array(
            'conditions'=>array('NewsPost.status'=>'Published','NewsPost.is_featured'=>1),
            'order'=>array('NewsPost.published_at'=>'desc','NewsPost.id'=>'desc'),
            'limit'=>3,
            'recursive'=>0
        ));
        $this->set(compact('categories','recentPosts','featuredPosts'));
    }
}
?>
