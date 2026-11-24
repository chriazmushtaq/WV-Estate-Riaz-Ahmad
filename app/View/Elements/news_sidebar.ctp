<aside class="wv-news-sidebar">
    <div class="wv-news-widget">
        <h3><i class="fa fa-folder-open"></i> <?php echo __('Categories');?></h3>
        <ul>
            <li><?php echo $this->Html->link(__('All News'),array('controller'=>'News','action'=>'index'));?></li>
            <?php foreach((array)$categories as $cat): ?>
                <li><?php echo $this->Html->link(h($cat['NewsCategory']['name']),array('controller'=>'News','action'=>'index','?'=>array('category'=>$cat['NewsCategory']['slug'])),array('escape'=>false));?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php if(!empty($featuredPosts)): ?>
    <div class="wv-news-widget">
        <h3><i class="fa fa-star"></i> <?php echo __('Featured');?></h3>
        <?php foreach($featuredPosts as $post): ?>
            <div class="wv-side-post">
                <?php if(!empty($post['NewsPost']['thumbnail'])) echo $this->Html->image('news/'.$post['NewsPost']['thumbnail']);?>
                <div><?php echo $this->Html->link(h($post['NewsPost']['title']),array('controller'=>'News','action'=>'view',$post['NewsPost']['slug']),array('escape'=>false));?><span><?php echo h(date('d M Y',strtotime($post['NewsPost']['published_at'])));?></span></div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <div class="wv-news-widget">
        <h3><i class="fa fa-clock-o"></i> <?php echo __('Recent Updates');?></h3>
        <?php foreach((array)$recentPosts as $post): ?>
            <div class="wv-side-post">
                <?php if(!empty($post['NewsPost']['thumbnail'])) echo $this->Html->image('news/'.$post['NewsPost']['thumbnail']);?>
                <div><?php echo $this->Html->link(h($post['NewsPost']['title']),array('controller'=>'News','action'=>'view',$post['NewsPost']['slug']),array('escape'=>false));?><span><?php echo h(date('d M Y',strtotime($post['NewsPost']['published_at'])));?></span></div>
            </div>
        <?php endforeach; ?>
    </div>
</aside>
