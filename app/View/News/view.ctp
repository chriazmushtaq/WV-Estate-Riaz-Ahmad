<style>
.wv-news-wrap{background:#f4f7fb;padding:20px 0 34px}.wv-news-inner{margin:0 auto;max-width:1180px;padding:0 14px}.wv-news-layout{display:grid;grid-template-columns:minmax(0,1fr) 320px;gap:18px}.wv-article{background:#fff;border:1px solid #dbe5f2;border-radius:8px;box-shadow:0 10px 24px rgba(15,23,42,.07);overflow:hidden}.wv-article-img{background:#e5edf7;max-height:430px;overflow:hidden}.wv-article-img img{display:block;max-height:430px;object-fit:cover;width:100%}.wv-article-body{padding:20px}.wv-article-meta{color:#64748b;font-size:12px;font-weight:900;margin-bottom:8px;text-transform:uppercase}.wv-article h1{color:#07152a;font-size:34px;font-weight:900;line-height:1.18;margin:0 0 12px}.wv-article-summary{background:#f8fafc;border-left:4px solid #ff0a46;color:#334155;font-size:15px;font-weight:800;line-height:1.6;margin:0 0 18px;padding:12px}.wv-article-content{color:#1f2937;font-size:15px;line-height:1.75}.wv-article-content img{height:auto;max-width:100%}.wv-news-sidebar{display:grid;gap:14px}.wv-news-widget{background:#fff;border:1px solid #dbe5f2;border-radius:8px;box-shadow:0 10px 24px rgba(15,23,42,.07);padding:14px}.wv-news-widget h3{color:#07152a;font-size:16px;font-weight:900;margin:0 0 10px}.wv-news-widget ul{list-style:none;margin:0;padding:0}.wv-news-widget li{border-top:1px solid #edf2f7;padding:9px 0}.wv-news-widget li:first-child{border-top:0}.wv-news-widget a{color:#07152a;font-weight:900;text-decoration:none}.wv-side-post{border-top:1px solid #edf2f7;display:flex;gap:10px;padding:10px 0}.wv-side-post:first-of-type{border-top:0}.wv-side-post img{border-radius:6px;height:54px;object-fit:cover;width:72px}.wv-side-post span{color:#64748b;display:block;font-size:11px;font-weight:800;margin-top:4px}@media(max-width:1050px){.wv-news-layout{grid-template-columns:1fr}}@media(max-width:650px){.wv-article h1{font-size:26px}.wv-article-body{padding:15px}}
</style>
<div class="wv-news-wrap">
    <div class="wv-news-inner">
        <div class="wv-news-layout">
            <main class="wv-article">
                <?php if(!empty($post['NewsPost']['thumbnail'])): ?><div class="wv-article-img"><?php echo $this->Html->image('news/'.$post['NewsPost']['thumbnail'],array('alt'=>$post['NewsPost']['title']));?></div><?php endif; ?>
                <div class="wv-article-body">
                    <div class="wv-article-meta"><?php echo h(!empty($post['NewsCategory']['name']) ? $post['NewsCategory']['name'] : __('News'));?> | <?php echo h(date('d M Y',strtotime($post['NewsPost']['published_at'])));?> | <?php echo (int)$post['NewsPost']['views']+1;?> <?php echo __('Views');?></div>
                    <h1><?php echo h($post['NewsPost']['title']);?></h1>
                    <?php if(!empty($post['NewsPost']['short_description'])): ?><div class="wv-article-summary"><?php echo h($post['NewsPost']['short_description']);?></div><?php endif; ?>
                    <div class="wv-article-content"><?php echo $post['NewsPost']['body'];?></div>
                </div>
            </main>
            <?php echo $this->element('news_sidebar');?>
        </div>
    </div>
</div>
