<?php
class NewsPost extends AppModel
{
    public $actsAs = array('search-master.Searchable');
    public $belongsTo = array(
        'NewsCategory' => array(
            'className' => 'NewsCategory',
            'foreignKey' => 'news_category_id'
        )
    );
    public $filterArgs = array('keyword' => array('type' => 'like', 'field' => array('NewsPost.title','NewsPost.slug','NewsPost.short_description','NewsPost.tags')));
    public $validate = array(
        'title' => array('rule' => 'notBlank', 'message' => 'Title is required'),
        'slug' => array('rule' => 'notBlank', 'message' => 'Slug is required'),
        'status' => array('rule' => array('inList', array('Draft','Published','Unpublished')), 'message' => 'Invalid status')
    );
}
?>
