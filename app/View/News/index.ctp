<style>
.wv-news-wrap{background:#f4f7fb;padding:20px 0 34px}.wv-news-inner{margin:0 auto;max-width:1180px;padding:0 14px}.wv-news-hero{background:linear-gradient(135deg,#07152a,#102746 65%,#ff0a46 150%);border-radius:10px;color:#fff;margin-bottom:18px;padding:26px}.wv-news-hero h1{color:#fff;font-size:34px;font-weight:900;margin:0 0 6px}.wv-news-hero p{color:#dbeafe;font-weight:700;margin:0}.wv-news-layout{display:grid;grid-template-columns:minmax(0,1fr) 320px;gap:18px}.wv-news-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px}.wv-news-card{background:#fff;border:1px solid #dbe5f2;border-radius:8px;box-shadow:0 10px 24px rgba(15,23,42,.07);overflow:hidden}.wv-news-img{background:#e5edf7;height:180px;overflow:hidden}.wv-news-img img{height:100%;object-fit:cover;width:100%;transition:transform .25s}.wv-news-card:hover .wv-news-img img{transform:scale(1.04)}.wv-news-body{padding:14px}.wv-news-meta{color:#64748b;font-size:11px;font-weight:900;margin-bottom:8px;text-transform:uppercase}.wv-news-body h2{font-size:18px;font-weight:900;line-height:1.25;margin:0 0 8px}.wv-news-body h2 a{color:#07152a;text-decoration:none}.wv-news-body p{color:#64748b;font-size:13px;font-weight:700;line-height:1.5;margin:0 0 12px}.wv-read{background:#ff0a46;border-radius:7px;color:#fff!important;display:inline-block;font-size:12px;font-weight:900;padding:9px 11px;text-decoration:none!important}.wv-news-sidebar{display:grid;gap:14px}.wv-news-widget{background:#fff;border:1px solid #dbe5f2;border-radius:8px;box-shadow:0 10px 24px rgba(15,23,42,.07);padding:14px}.wv-news-widget h3{color:#07152a;font-size:16px;font-weight:900;margin:0 0 10px}.wv-news-widget ul{list-style:none;margin:0;padding:0}.wv-news-widget li{border-top:1px solid #edf2f7;padding:9px 0}.wv-news-widget li:first-child{border-top:0}.wv-news-widget a{color:#07152a;font-weight:900;text-decoration:none}.wv-side-post{border-top:1px solid #edf2f7;display:flex;gap:10px;padding:10px 0}.wv-side-post:first-of-type{border-top:0}.wv-side-post img{border-radius:6px;height:54px;object-fit:cover;width:72px}.wv-side-post span{color:#64748b;display:block;font-size:11px;font-weight:800;margin-top:4px}.wv-pagination{margin-top:16px;text-align:center}@media(max-width:1050px){.wv-news-layout{grid-template-columns:1fr}.wv-news-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:650px){.wv-news-grid{grid-template-columns:1fr}.wv-news-hero h1{font-size:26px}}
</style>
<div class="wv-news-wrap">
    <div class="wv-news-inner">
        <div class="wv-news-hero">
            <h1><?php echo !empty($selectedCategory) ? h($selectedCategory['NewsCategory']['name']) : __('News & Updates');?></h1>
            <p><?php echo __('Latest society updates, announcements, property news, and development information.');?></p>
        </div>
        <div class="wv-news-layout">
            <main>
                <div class="wv-news-grid">
                    <?php foreach($posts as $post): ?>
                    <article class="wv-news-card">
                        <div class="wv-news-img">
                            <?php if(!empty($post['NewsPost']['thumbnail'])) echo $this->Html->image('news/'.$post['NewsPost']['thumbnail'],array('alt'=>$post['NewsPost']['title']));?>
                        </div>
                        <div class="wv-news-body">
                            <div class="wv-news-meta"><?php echo h(!empty($post['NewsCategory']['name']) ? $post['NewsCategory']['name'] : __('News'));?> | <?php echo h(date('d M Y',strtotime($post['NewsPost']['published_at'])));?></div>
                            <h2><?php echo $this->Html->link(h($post['NewsPost']['title']),array('action'=>'view',$post['NewsPost']['slug']),array('escape'=>false));?></h2>
                            <p><?php echo h($this->Text->truncate($post['NewsPost']['short_description'],150));?></p>
                            <?php echo $this->Html->link(__('Read More'),array('action'=>'view',$post['NewsPost']['slug']),array('class'=>'wv-read'));?>
                        </div>
                    </article>
                    <?php endforeach; ?>
                </div>
                <div class="wv-pagination"><?php echo $this->element('pagination',array('IsSearch'=>'No','IsDropdown'=>'No'));?></div>
            </main>
            <?php echo $this->element('news_sidebar');?>
        </div>
    </div>
</div>
