<?php
class NewsCategory extends AppModel
{
    public $actsAs = array('search-master.Searchable');
    public $hasMany = array(
        'NewsPost' => array(
            'className' => 'NewsPost',
            'foreignKey' => 'news_category_id'
        )
    );
    public $filterArgs = array('keyword' => array('type' => 'like', 'field' => array('NewsCategory.name','NewsCategory.slug')));
    public $validate = array(
        'name' => array('rule' => 'notBlank', 'message' => 'Category name is required'),
        'slug' => array('rule' => 'notBlank', 'message' => 'Slug is required')
    );
}
?>
