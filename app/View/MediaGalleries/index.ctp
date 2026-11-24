<?php
$query = isset($query) ? (array)$query : array();
$folderOptions = isset($folderOptions) ? (array)$folderOptions : array();
?>
<style>
.wv-media-front{background:#f7f9fc;padding-bottom:38px;}
.wv-media-front-hero{background:linear-gradient(135deg,#07152a 0%,#102746 68%,#ff0a46 150%);color:#fff;margin-bottom:26px;padding:28px 0;}
.wv-media-front-hero h1{color:#fff;font-size:30px;font-weight:900;margin:0 0 6px;}
.wv-media-front-hero p{color:#cbd5e1;font-size:13px;font-weight:700;margin:0;}
.wv-media-front-toolbar{background:#fff;border:1px solid #dfe7f1;border-radius:10px;box-shadow:0 12px 28px rgba(15,23,42,.06);margin-bottom:18px;padding:14px;}
.wv-media-front-filter{align-items:end;display:grid;grid-template-columns:2fr 1fr 1fr auto;gap:10px;}
.wv-media-front-field label{color:#07152a;display:block;font-size:10px;font-weight:900;margin-bottom:5px;text-transform:uppercase;}
.wv-media-front-field .form-control{border:1px solid #cfdbea;border-radius:7px;height:39px;}
.wv-media-folder-chips{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:18px;}
.wv-media-chip{background:#fff;border:1px solid #dfe7f1;border-radius:999px;color:#07152a!important;font-size:12px;font-weight:900;padding:8px 12px;text-decoration:none!important;}
.wv-media-chip:hover,.wv-media-chip.active{background:#ff0a46;border-color:#ff0a46;color:#fff!important;}
.wv-media-front-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:18px;}
.wv-media-front-card{background:#fff;border:1px solid #f4a400;border-radius:10px;box-shadow:0 12px 26px rgba(15,23,42,.08);overflow:hidden;transition:box-shadow .2s ease,transform .2s ease;}
.wv-media-front-card:hover{box-shadow:0 18px 34px rgba(15,23,42,.14);transform:translateY(-2px);}
.wv-media-front-preview{align-items:center;background:#e8eef6;display:flex;height:184px;justify-content:center;position:relative;}
.wv-media-front-preview img{height:100%;object-fit:cover;width:100%;}
.wv-media-front-preview i{color:#07152a;font-size:52px;}
.wv-media-front-badge{background:#07152a;border-radius:999px;color:#fff;font-size:10px;font-weight:900;left:10px;padding:6px 10px;position:absolute;top:10px;text-transform:uppercase;}
.wv-media-front-body{padding:13px;}
.wv-media-front-title{color:#07152a;font-size:14px;font-weight:900;line-height:1.35;margin:0 0 8px;min-height:38px;}
.wv-media-front-meta{color:#64748b;font-size:11px;font-weight:800;line-height:1.6;}
.wv-media-front-actions{border-top:1px solid #eef2f7;margin-top:12px;padding-top:11px;}
.wv-media-front-actions a{background:#fff1f4;border-radius:999px;color:#ff0a46!important;display:inline-block;font-size:11px;font-weight:900;padding:8px 12px;text-decoration:none!important;}
.wv-media-front-actions a:hover{background:#ff0a46;color:#fff!important;}
.wv-media-empty{background:#fff;border:1px dashed #cbd5e1;border-radius:10px;color:#64748b;font-weight:900;padding:30px;text-align:center;}
.wv-media-front-pagination{background:#fff;border:1px solid #dfe7f1;border-radius:10px;box-shadow:0 12px 28px rgba(15,23,42,.06);margin-top:18px;padding:14px;text-align:center;}
.wv-media-front-pagination .pagination{display:inline-block;margin:0;}
@media(max-width:1199px){.wv-media-front-grid{grid-template-columns:repeat(3,minmax(0,1fr));}}
@media(max-width:991px){.wv-media-front-filter{grid-template-columns:1fr 1fr}.wv-media-front-grid{grid-template-columns:repeat(2,minmax(0,1fr));}}
@media(max-width:640px){.wv-media-front-hero h1{font-size:24px}.wv-media-front-filter{grid-template-columns:1fr}.wv-media-front-grid{grid-template-columns:1fr;}}
</style>

<div class="wv-media-front">
    <div class="wv-media-front-hero">
        <div class="container">
            <h1><?php echo __('Media Gallery');?></h1>
            <p><?php echo __('Browse published White Villas images, documents, floor plans, and website media.');?></p>
        </div>
    </div>
    <div class="container">
        <div class="wv-media-front-toolbar">
            <?php echo $this->Form->create(false, array('type'=>'get','url'=>array('action'=>'index')));?>
            <div class="wv-media-front-filter">
                <div class="wv-media-front-field">
                    <label><?php echo __('Search');?></label>
                    <?php echo $this->Form->input('keyword', array('name'=>'keyword','label'=>false,'div'=>false,'class'=>'form-control','value'=>isset($query['keyword']) ? $query['keyword'] : '','placeholder'=>__('Search media')));?>
                </div>
                <div class="wv-media-front-field">
                    <label><?php echo __('Folder');?></label>
                    <?php echo $this->Form->select('folder_id', $folderOptions, array('name'=>'folder_id','empty'=>__('All Folders'),'label'=>false,'div'=>false,'class'=>'form-control','value'=>isset($query['folder_id']) ? $query['folder_id'] : ''));?>
                </div>
                <div class="wv-media-front-field">
                    <label><?php echo __('Type');?></label>
                    <?php echo $this->Form->select('file_type', array('image'=>__('Images'),'document'=>__('Documents')), array('name'=>'file_type','empty'=>__('All Types'),'label'=>false,'div'=>false,'class'=>'form-control','value'=>isset($query['file_type']) ? $query['file_type'] : ''));?>
                </div>
                <div>
                    <button class="btn btn-primary" type="submit"><i class="fa fa-search"></i> <?php echo __('Search');?></button>
                    <?php echo $this->Html->link('<i class="fa fa-close"></i>', array('action'=>'index'), array('escape'=>false,'class'=>'btn btn-danger','title'=>__('Reset')));?>
                </div>
            </div>
            <?php echo $this->Form->end();?>
        </div>

        <div class="wv-media-folder-chips">
            <a class="wv-media-chip <?php echo empty($folderId) ? 'active' : '';?>" href="<?php echo $this->Html->url(array('action'=>'index'));?>"><?php echo __('All');?></a>
            <?php foreach((array)$folders as $folder){ $f = $folder['MediaFolder']; ?>
                <a class="wv-media-chip <?php echo (!empty($folderId) && (int)$folderId===(int)$f['id']) ? 'active' : '';?>" href="<?php echo $this->Html->url(array('action'=>'index','?' => array('folder_id'=>$f['id'])));?>"><?php echo h($f['name']);?></a>
            <?php } ?>
        </div>

        <?php if(!empty($mediaFiles)){ ?>
        <div class="wv-media-front-grid">
            <?php foreach($mediaFiles as $media){ $m = $media['MediaFile']; ?>
            <article class="wv-media-front-card">
                <div class="wv-media-front-preview">
                    <span class="wv-media-front-badge"><?php echo h($m['file_type']);?></span>
                    <?php if($m['file_type']==='image' && !empty($m['exists'])){ ?>
                        <a href="<?php echo $this->Html->url(array('action'=>'view',$m['id']));?>" style="display:block;height:100%;width:100%;"><img src="<?php echo h($m['thumb_url']);?>" alt="<?php echo h($m['title']);?>"></a>
                    <?php }else{ ?>
                        <i class="<?php echo h($m['icon']);?>"></i>
                    <?php } ?>
                </div>
                <div class="wv-media-front-body">
                    <h3 class="wv-media-front-title"><?php echo h($m['title']);?></h3>
                    <div class="wv-media-front-meta"><?php echo h(strtoupper($m['extension']));?> · <?php echo h($m['size_label']);?></div>
                    <div class="wv-media-front-actions">
                        <?php echo $this->Html->link(__('Open').' <i class="fa fa-angle-right"></i>', array('action'=>'view',$m['id']), array('escape'=>false));?>
                    </div>
                </div>
            </article>
            <?php } ?>
        </div>
        <div class="wv-media-front-pagination">
            <ul class="pagination">
                <?php echo $this->Paginator->prev('&laquo;', array('tag'=>'li','escape'=>false), null, array('tag'=>'li','class'=>'disabled','disabledTag'=>'a','escape'=>false));?>
                <?php echo $this->Paginator->numbers(array('tag'=>'li','currentTag'=>'a','currentClass'=>'active','separator'=>''));?>
                <?php echo $this->Paginator->next('&raquo;', array('tag'=>'li','escape'=>false), null, array('tag'=>'li','class'=>'disabled','disabledTag'=>'a','escape'=>false));?>
            </ul>
        </div>
        <?php }else{ ?>
            <div class="wv-media-empty"><?php echo __('No published media found yet.');?></div>
        <?php } ?>
    </div>
</div>
