<?php
$this->Js->JqueryEngine->jQueryObject = 'jQuery';
$canAdd = !empty($allowAddAction);
$canEdit = !empty($allowEditAction);
$canDelete = !empty($allowDeleteAction);
$canManageAllMedia = !empty($canManageAllMedia);
$query = isset($query) ? (array)$query : array();
$folderOptions = isset($folderOptions) ? (array)$folderOptions : array();
$stats = isset($stats) ? (array)$stats : array('all'=>0,'image'=>0,'document'=>0,'frontend'=>0);
?>
<style>
.wv-media-page{background:#f6f8fb;border:1px solid #dbe5f0;border-radius:10px;padding:14px;}
.wv-media-hero{align-items:center;background:linear-gradient(135deg,#07152a 0%,#132b4b 72%,#ff0a46 150%);border-radius:9px;color:#fff;display:flex;gap:14px;justify-content:space-between;margin-bottom:14px;padding:18px 20px;}
.wv-media-hero h1{color:#fff;font-size:24px;font-weight:900;margin:0 0 4px;}
.wv-media-hero p{color:#cfe2ff;font-size:12px;font-weight:700;margin:0;}
.wv-media-actions{display:flex;flex-wrap:wrap;gap:8px;justify-content:flex-end;}
.wv-media-actions .btn{border-radius:8px;font-weight:900;}
.wv-media-shell{display:grid;grid-template-columns:280px minmax(0,1fr);gap:14px;}
.wv-media-panel{background:#fff;border:1px solid #dce6f2;border-radius:9px;box-shadow:0 10px 24px rgba(15,23,42,.06);overflow:hidden;}
.wv-media-panel-head{background:#07152a;color:#fff;font-size:13px;font-weight:900;padding:12px 14px;text-transform:uppercase;}
.wv-media-panel-body{padding:14px;}
.wv-media-folder-list{list-style:none;margin:0;padding:0;}
.wv-media-folder-list li{border-bottom:1px solid #eef2f7;}
.wv-media-folder-list li:last-child{border-bottom:0;}
.wv-media-folder-row{align-items:center;color:#10233e!important;display:flex;font-size:12px;font-weight:900;gap:9px;padding:10px 0;text-decoration:none!important;}
.wv-media-folder-row .count{background:#eaf1fb;border-radius:999px;color:#07152a;margin-left:auto;padding:3px 8px;}
.wv-media-folder-tools a{color:#64748b;margin-left:6px;text-decoration:none!important;}
.wv-media-folder-tools a:hover{color:#ff0a46;}
.wv-media-filter{align-items:end;display:grid;grid-template-columns:2fr 1fr 1fr 1fr auto;gap:10px;margin-bottom:14px;}
.wv-media-field label{color:#07152a;display:block;font-size:10px;font-weight:900;margin-bottom:5px;text-transform:uppercase;}
.wv-media-field .form-control{border:1px solid #cfdbea;border-radius:7px;height:38px;}
.wv-media-stat-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin-bottom:14px;}
.wv-media-stat{background:#fff;border:1px solid #dce6f2;border-radius:9px;padding:13px;}
.wv-media-stat strong{color:#07152a;display:block;font-size:22px;font-weight:900;line-height:1;}
.wv-media-stat span{color:#64748b;font-size:10px;font-weight:900;text-transform:uppercase;}
.wv-media-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:13px;}
.wv-media-card{background:#fff;border:1px solid #dce6f2;border-radius:9px;box-shadow:0 8px 20px rgba(15,23,42,.06);overflow:hidden;}
.wv-media-preview{align-items:center;background:#eef3f8;display:flex;height:150px;justify-content:center;position:relative;}
.wv-media-preview img{height:100%;object-fit:cover;width:100%;}
.wv-media-preview .wv-media-icon{color:#07152a;font-size:46px;}
.wv-media-badge{background:#07152a;border-radius:999px;color:#fff;font-size:10px;font-weight:900;left:9px;padding:5px 8px;position:absolute;top:9px;text-transform:uppercase;}
.wv-media-badge.front{background:#059669;}
.wv-media-info{padding:11px;}
.wv-media-title{color:#07152a;font-size:13px;font-weight:900;line-height:1.3;margin:0 0 6px;min-height:34px;}
.wv-media-meta{color:#64748b;font-size:11px;font-weight:700;line-height:1.6;}
.wv-media-card-actions{border-top:1px solid #eef2f7;display:flex;gap:6px;padding:9px 11px;}
.wv-media-card-actions .btn{border-radius:7px;font-size:11px;font-weight:900;padding:6px 9px;}
.wv-upload-grid{display:grid;grid-template-columns:1fr;gap:10px;}
.wv-upload-grid label{color:#07152a;display:block;font-size:10px;font-weight:900;margin-bottom:5px;text-transform:uppercase;}
.wv-upload-grid .form-control{border:1px solid #cfdbea;border-radius:7px;height:38px;}
.wv-upload-grid input[type=file]{background:#f8fafc;border:1px dashed #b7c6d9;border-radius:8px;padding:10px;width:100%;}
.wv-media-upload-modal .modal-dialog{max-width:520px;}
.wv-media-upload-modal .modal-content{border:0;border-radius:10px;box-shadow:0 18px 45px rgba(7,21,42,.24);overflow:hidden;}
.wv-media-upload-modal .modal-header{align-items:center;background:#07152a;border:0;color:#fff;display:flex;justify-content:space-between;padding:13px 16px;}
.wv-media-upload-modal .modal-title{color:#fff;font-size:14px;font-weight:900;line-height:1;margin:0;text-transform:uppercase;}
.wv-media-upload-modal .close{color:#fff;filter:alpha(opacity=85);font-size:24px;line-height:1;margin-top:0;opacity:.85;text-shadow:none;}
.wv-media-upload-modal .close:hover{opacity:1;}
.wv-media-upload-modal .modal-body{background:#f6f8fb;padding:16px;}
.wv-media-upload-modal .wv-upload-grid{gap:12px;}
.wv-media-upload-modal .wv-upload-grid .form-control{background:#fff;color:#07152a;font-weight:800;}
.wv-media-upload-modal .wv-upload-grid select.form-control option{color:#07152a;background:#fff;}
.wv-media-upload-modal .wv-upload-grid input[type=file]{background:#fff;border-style:solid;color:#07152a;}
.wv-media-upload-modal .wv-upload-grid .btn-success{border-radius:8px;font-weight:900;padding:10px 14px;width:100%;}
.wv-empty-media{background:#fff;border:1px dashed #b8c7da;border-radius:9px;color:#64748b;font-weight:900;padding:28px;text-align:center;}
.wv-media-pagination{margin-top:14px;text-align:center;}
.wv-media-pagination .pagination{display:inline-block;margin:0;}
@media(max-width:1199px){.wv-media-grid{grid-template-columns:repeat(3,minmax(0,1fr));}.wv-media-stat-grid{grid-template-columns:repeat(2,minmax(0,1fr));}}
@media(max-width:991px){.wv-media-shell{display:block}.wv-media-panel{margin-bottom:14px}.wv-media-filter{grid-template-columns:1fr 1fr}.wv-media-filter .btn{width:100%;}}
@media(max-width:640px){.wv-media-hero{display:block}.wv-media-actions{justify-content:flex-start;margin-top:12px}.wv-media-grid{grid-template-columns:1fr}.wv-media-filter{grid-template-columns:1fr}.wv-media-stat-grid{grid-template-columns:1fr;}}
</style>

<div class="page-title">
    <div class="title-env"><h1 class="title"><?php echo __('Media Gallery');?></h1></div>
</div>

<div class="wv-media-page">
    <?php echo $this->Session->flash();?>
    <div class="wv-media-hero">
        <div>
            <h1><?php echo __('Image & Document Gallery');?></h1>
            <p><?php echo __('Central media library for website images, property photos, documents, and new backend uploads.');?></p>
        </div>
        <div class="wv-media-actions">
            <?php if($canAdd){ ?>
            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#wvUploadMediaModal"><i class="fa fa-upload"></i> <?php echo __('Upload Media');?></button>
            <?php } ?>
            <?php if($canManageAllMedia){ ?>
            <?php echo $this->Html->link('<i class="fa fa-refresh"></i> '.__('Scan Existing Media'), array('action'=>'sync'), array('escape'=>false,'class'=>'btn btn-info'));?>
            <?php } ?>
        </div>
    </div>

    <div class="wv-media-stat-grid" style="grid-template-columns: repeat(5, minmax(0, 1fr));">
        <div class="wv-media-stat"><strong><?php echo (int)$stats['all'];?></strong><span><?php echo __('All Media');?></span></div>
        <div class="wv-media-stat"><strong><?php echo (int)$stats['image'];?></strong><span><?php echo __('Images');?></span></div>
        <div class="wv-media-stat"><strong><?php echo (int)(isset($stats['video']) ? $stats['video'] : 0);?></strong><span><?php echo __('Videos / MP4');?></span></div>
        <div class="wv-media-stat"><strong><?php echo (int)$stats['document'];?></strong><span><?php echo __('Documents / PDF');?></span></div>
        <div class="wv-media-stat"><strong><?php echo (int)$stats['frontend'];?></strong><span><?php echo __('Frontend Visible');?></span></div>
    </div>

    <div class="wv-media-shell">
        <aside>
            <div class="wv-media-panel">
                <div class="wv-media-panel-head"><?php echo __('Folders');?></div>
                <div class="wv-media-panel-body">
                    <ul class="wv-media-folder-list">
                        <li>
                            <a class="wv-media-folder-row" href="<?php echo $this->Html->url(array('action'=>'index'));?>">
                                <i class="fa fa-folder-open"></i> <?php echo __('All Media');?>
                                <span class="count"><?php echo (int)$stats['all'];?></span>
                            </a>
                        </li>
                        <?php foreach((array)$folders as $folder){ $f = $folder['MediaFolder']; ?>
                        <li>
                            <div class="wv-media-folder-row">
                                <a href="<?php echo $this->Html->url(array('action'=>'index','?' => array('folder_id'=>$f['id'])));?>" style="color:inherit;text-decoration:none;flex:1;">
                                    <i class="fa fa-folder"></i> <?php echo h($f['name']);?>
                                </a>
                                <span class="count"><?php echo (int)$f['file_count'];?></span>
                                <span class="wv-media-folder-tools">
                                    <?php if($canEdit){ echo $this->Html->link('<i class="fa fa-pencil"></i>', array('action'=>'folderedit',$f['id']), array('escape'=>false,'title'=>__('Edit Folder'))); } ?>
                                    <?php if($canDelete && empty($f['system_key'])){ echo $this->Html->link('<i class="fa fa-trash"></i>', array('action'=>'folderdelete',$f['id']), array('escape'=>false,'title'=>__('Delete Folder')), __('Delete this folder? Files will remain in All Media.')); } ?>
                                </span>
                            </div>
                        </li>
                        <?php } ?>
                    </ul>
                </div>
            </div>

            <?php if($canAdd){ ?>
            <div class="wv-media-panel">
                <div class="wv-media-panel-head"><?php echo __('Create Folder');?></div>
                <div class="wv-media-panel-body">
                    <?php echo $this->Form->create('MediaFolder', array('url'=>array('controller'=>'media_galleries','action'=>'folderadd')));?>
                    <div class="wv-upload-grid">
                        <div>
                            <label><?php echo __('Folder Name');?></label>
                            <?php echo $this->Form->text('name', array('class'=>'form-control','placeholder'=>__('Folder name')));?>
                        </div>
                        <div>
                            <label><?php echo __('Visibility');?></label>
                            <?php echo $this->Form->select('visibility', array('backend'=>__('Backend Only'),'frontend'=>__('Frontend Only'),'both'=>__('Backend + Frontend')), array('label'=>false,'div'=>false,'class'=>'form-control','value'=>'backend'));?>
                        </div>
                        <button class="btn btn-primary" type="submit"><i class="fa fa-plus"></i> <?php echo __('Add Folder');?></button>
                    </div>
                    <?php echo $this->Form->end();?>
                </div>
            </div>
            <?php } ?>

        </aside>

        <main>
            <div class="wv-media-panel">
                <div class="wv-media-panel-body">
                    <?php echo $this->Form->create(false, array('type'=>'get','url'=>array('controller'=>'media_galleries','action'=>'index')));?>
                    <div class="wv-media-filter">
                        <div class="wv-media-field">
                            <label><?php echo __('Search');?></label>
                            <input type="text" name="keyword" class="form-control" value="<?php echo h(isset($query['keyword']) ? $query['keyword'] : '');?>" placeholder="<?php echo h(__('Title, file, source'));?>" />
                        </div>
                        <div class="wv-media-field">
                            <label><?php echo __('Folder');?></label>
                            <?php echo $this->Form->select('folder_id', $folderOptions, array('name'=>'folder_id','empty'=>__('All'),'label'=>false,'div'=>false,'class'=>'form-control','value'=>isset($query['folder_id']) ? $query['folder_id'] : ''));?>
                        </div>
                        <div class="wv-media-field">
                            <label><?php echo __('Type');?></label>
                            <?php echo $this->Form->select('file_type', array('image'=>__('Images'),'video'=>__('Videos / MP4'),'document'=>__('Documents'),'other'=>__('Other')), array('name'=>'file_type','empty'=>__('All'),'label'=>false,'div'=>false,'class'=>'form-control','value'=>isset($query['file_type']) ? $query['file_type'] : ''));?>
                        </div>
                        <div class="wv-media-field">
                            <label><?php echo __('Visibility');?></label>
                            <?php echo $this->Form->select('visibility', array('backend'=>__('Backend'),'frontend'=>__('Frontend'),'both'=>__('Both')), array('name'=>'visibility','empty'=>__('All'),'label'=>false,'div'=>false,'class'=>'form-control','value'=>isset($query['visibility']) ? $query['visibility'] : ''));?>
                        </div>
                        <div>
                            <button class="btn btn-primary" type="submit"><i class="fa fa-search"></i> <?php echo __('Search');?></button>
                            <?php echo $this->Html->link('<i class="fa fa-close"></i>', array('action'=>'index'), array('escape'=>false,'class'=>'btn btn-default','title'=>__('Reset')));?>
                        </div>
                    </div>
                    <?php echo $this->Form->end();?>

                    <?php if(!empty($mediaFiles)){ ?>
                    <div class="wv-media-grid">
                        <?php foreach($mediaFiles as $media){ $m = $media['MediaFile']; ?>
                        <article class="wv-media-card">
                            <div class="wv-media-preview">
                                <span class="wv-media-badge <?php echo in_array($m['visibility'], array('frontend','both')) ? 'front' : '';?>"><?php echo h($m['visibility']);?></span>
                                <?php if($m['file_type']==='image' && !empty($m['exists'])){ ?>
                                    <a href="<?php echo h($m['url']);?>" target="_blank"><img src="<?php echo h($m['thumb_url']);?>" alt="<?php echo h($m['title']);?>"></a>
                                <?php }elseif($m['file_type']==='video' && !empty($m['exists'])){ ?>
                                    <a href="<?php echo h($m['url']);?>" target="_blank" style="display:flex;flex-direction:column;align-items:center;justify-content:center;color:#07152a;text-decoration:none;">
                                        <i class="fa fa-file-video-o wv-media-icon" style="color:#d11242;font-size:42px;"></i>
                                        <span style="font-size:10px;font-weight:800;margin-top:4px;color:#64748b;"><?php echo __('Play Video / Preview'); ?></span>
                                    </a>
                                <?php }else{ ?>
                                    <i class="<?php echo h($m['icon']);?> wv-media-icon"></i>
                                <?php } ?>
                            </div>
                            <div class="wv-media-info">
                                <h3 class="wv-media-title"><?php echo h($m['title']);?></h3>
                                <div class="wv-media-meta">
                                    <?php echo h(strtoupper($m['extension']));?> · <?php echo h($m['size_label']);?><br>
                                    <?php echo h($m['source_type']);?>
                                </div>
                            </div>
                            <div class="wv-media-card-actions">
                                <?php echo $this->Html->link('<i class="fa fa-eye"></i>', $m['url'], array('escape'=>false,'target'=>'_blank','class'=>'btn btn-default','title'=>__('Open')));?>
                                <?php echo $this->Html->link('<i class="fa fa-download"></i>', array('action'=>'download',$m['id']), array('escape'=>false,'class'=>'btn btn-info','title'=>__('Download')));?>
                                <?php if($canEdit){ echo $this->Html->link('<i class="fa fa-pencil"></i>', array('action'=>'edit',$m['id']), array('escape'=>false,'class'=>'btn btn-primary','title'=>__('Edit'))); } ?>
                                <?php if($canDelete){ echo $this->Html->link('<i class="fa fa-trash"></i>', array('action'=>'delete',$m['id']), array('escape'=>false,'class'=>'btn btn-danger','title'=>__('Delete')), __('Remove this media from gallery?')); } ?>
                            </div>
                        </article>
                        <?php } ?>
                    </div>
                    <div class="wv-media-pagination">
                        <ul class="pagination">
                            <?php echo $this->Paginator->prev('&laquo;', array('tag'=>'li','escape'=>false), null, array('tag'=>'li','class'=>'disabled','disabledTag'=>'a','escape'=>false));?>
                            <?php echo $this->Paginator->numbers(array('tag'=>'li','currentTag'=>'a','currentClass'=>'active','separator'=>''));?>
                            <?php echo $this->Paginator->next('&raquo;', array('tag'=>'li','escape'=>false), null, array('tag'=>'li','class'=>'disabled','disabledTag'=>'a','escape'=>false));?>
                        </ul>
                    </div>
                    <?php }else{ ?>
                    <div class="wv-empty-media">
                        <?php echo $canManageAllMedia ? __('No media found. Use Scan Existing Media or upload new files.') : __('No media found. Upload new files to your gallery.');?>
                    </div>
                    <?php } ?>
                </div>
            </div>
        </main>
    </div>
</div>

<?php if($canAdd){ ?>
<div class="modal fade wv-media-upload-modal" id="wvUploadMediaModal" tabindex="-1" role="dialog" aria-labelledby="wvUploadMediaModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="wvUploadMediaModalLabel"><i class="fa fa-upload"></i> <?php echo __('Upload Media');?></h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="<?php echo h(__('Close'));?>"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <?php echo $this->Form->create('MediaFile', array('url'=>array('controller'=>'media_galleries','action'=>'add'),'type'=>'file'));?>
                <div class="wv-upload-grid">
                    <div>
                        <label><?php echo __('Title');?></label>
                        <?php echo $this->Form->text('title', array('class'=>'form-control','placeholder'=>__('Optional title')));?>
                    </div>
                    <div>
                        <label><?php echo __('Folder');?></label>
                        <?php echo $this->Form->select('folder_id', $folderOptions, array('empty'=>__('All Media'),'label'=>false,'div'=>false,'class'=>'form-control'));?>
                    </div>
                    <div>
                        <label><?php echo __('Visibility');?></label>
                        <?php echo $this->Form->select('visibility', array('backend'=>__('Backend Only'),'frontend'=>__('Frontend Only'),'both'=>__('Backend + Frontend')), array('label'=>false,'div'=>false,'class'=>'form-control','value'=>'backend'));?>
                    </div>
                    <div>
                        <label><?php echo __('Select Files (Images, PDF, MP4 / Videos)');?></label>
                        <input type="file" name="data[MediaFile][upload][]" multiple="multiple" accept="image/*,video/*,audio/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.mp4,.webm,.mov,.mkv,.avi,.wmv,.m4v,.zip,.rar" />
                        <small class="text-muted" style="display:block;margin-top:4px;font-size:11px;"><?php echo __('Supports JPG, PNG, WEBP, PDF documents, MP4 / WEBM / MOV videos, and office files.');?></small>
                    </div>
                    <button class="btn btn-success" type="submit"><i class="fa fa-upload"></i> <?php echo __('Upload');?></button>
                </div>
                <?php echo $this->Form->end();?>
            </div>
        </div>
    </div>
</div>
<?php } ?>
