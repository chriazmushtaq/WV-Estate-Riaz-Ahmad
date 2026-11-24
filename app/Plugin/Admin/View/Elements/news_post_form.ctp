<style>
.wv-news-form{background:#f6f8fb;display:block;margin:0;max-width:none!important;padding:18px;width:100%!important}
.wv-news-panel{background:#fff;border:1px solid #dbe5f2;border-radius:8px;box-shadow:0 10px 24px rgba(15,23,42,.06);display:block;margin:0;max-width:none!important;padding:20px;width:100%!important}
.wv-news-panel>form{display:block;margin:0;max-width:none!important;width:100%!important}
.wv-news-title{color:#07152a;font-size:22px;font-weight:900;margin:0 0 20px}
.wv-news-grid{align-items:start;display:grid;grid-template-columns:minmax(0,1fr) 250px;gap:20px;width:100%}
.wv-news-main{max-width:none;min-width:0;width:auto}
.wv-news-main .form-group{margin:0 0 16px}
.wv-news-main .control-label{color:#243b5a;display:block;float:none;font-size:12px;font-weight:800;margin:0 0 7px;padding:0;text-align:left;text-transform:uppercase;width:100%}
.wv-news-main .col-sm-10{float:none;padding:0;width:100%}
.wv-news-main .form-control{border:1px solid #cbd8e8;border-radius:6px;box-shadow:none;height:42px;padding:9px 12px;width:100%}
.wv-news-main textarea.form-control{height:auto;min-height:105px;resize:vertical}
.wv-news-main .mce-tinymce,.wv-news-main .tox-tinymce,.wv-news-main iframe{max-width:100%!important}
.wv-news-editor-gallery{align-items:center;display:flex;gap:10px;margin:-4px 0 16px}
.wv-news-editor-gallery>input[type=file]{display:none!important}
.wv-news-editor-gallery .wv-gallery-upload-tools{margin:0}
.wv-news-editor-gallery .wv-gallery-upload-tools .btn-default{display:none}
.wv-news-editor-gallery .wv-gallery-selected-preview{display:none!important}
.wv-news-editor-gallery-note{color:#5f7189;font-size:12px}
.wv-news-sidebar{min-width:0;width:250px}
.wv-news-box{background:#fbfcfe;border:1px solid #dbe5f2;border-radius:8px;padding:15px;width:100%}
.wv-news-box+.wv-news-box{margin-top:14px}
.wv-news-box h4{border-bottom:1px solid #e5edf6;color:#07152a;font-size:15px;font-weight:900;margin:0 0 14px;padding-bottom:10px}
.wv-news-box .form-group{margin:0 0 14px}
.wv-news-box label{color:#243b5a;font-size:11px;font-weight:800;margin-bottom:6px;text-transform:uppercase}
.wv-news-box .form-control{border:1px solid #cbd8e8;border-radius:6px;box-shadow:none;height:40px;padding:8px 10px;width:100%}
.wv-news-date-picker{display:flex;position:relative;width:100%}
.wv-news-date-picker .form-control{border-radius:6px 0 0 6px;min-width:0}
.wv-news-date-picker .input-group-addon{align-items:center;background:#edf4ff;border:1px solid #cbd8e8;border-left:0;border-radius:0 6px 6px 0;color:#075985;cursor:pointer;display:flex;justify-content:center;min-width:40px;padding:0}
.wv-news-box input[type=file]{height:auto;padding:8px}
.wv-news-box .checkbox{margin:12px 0}
.wv-news-box .checkbox label{align-items:center;display:flex;gap:8px;text-transform:uppercase}
.wv-news-box .checkbox input{margin:0;position:static}
.wv-preview-thumb{border-radius:6px;display:block;margin-bottom:10px;max-height:180px;object-fit:cover;width:100%}
.wv-news-actions{border-top:1px solid #e5edf6;display:flex;gap:9px;margin-top:20px;padding-top:18px}
.wv-news-actions .btn{min-width:96px}
@media(max-width:850px){.wv-news-form{padding:10px}.wv-news-panel{padding:14px}.wv-news-grid{grid-template-columns:minmax(0,1fr);overflow:visible}.wv-news-sidebar{width:100%}}
</style>
<div class="wv-news-form">
    <div class="wv-news-panel">
        <h3 class="wv-news-title"><i class="fa fa-newspaper-o"></i> <?php echo h($formTitle);?></h3>
        <?php echo $this->Session->flash();?>
        <?php echo $this->Form->create('NewsPost',array('class'=>'form-horizontal wv-no-auto-layout','type'=>'file'));?>
        <div class="wv-news-grid">
            <div class="wv-news-main">
                <div class="form-group"><label class="col-sm-2 control-label"><?php echo __('Title');?></label><div class="col-sm-10"><?php echo $this->Form->input('title',array('label'=>false,'div'=>false,'class'=>'form-control','placeholder'=>__('News title')));?></div></div>
                <div class="form-group"><label class="col-sm-2 control-label"><?php echo __('Slug');?></label><div class="col-sm-10"><?php echo $this->Form->input('slug',array('label'=>false,'div'=>false,'class'=>'form-control','placeholder'=>__('auto-generated if empty')));?></div></div>
                <div class="form-group"><label class="col-sm-2 control-label"><?php echo __('Short Description');?></label><div class="col-sm-10"><?php echo $this->Form->input('short_description',array('type'=>'textarea','rows'=>4,'label'=>false,'div'=>false,'class'=>'form-control','placeholder'=>__('Card excerpt / summary')));?></div></div>
                <div class="form-group"><label class="col-sm-2 control-label"><?php echo __('Full HTML Content');?></label><div class="col-sm-10"><?php echo $this->Tinymce->input('body',array('id'=>'NewsPostBody','label'=>false,'class'=>'form-control','div'=>false),array('language'=>$configLanguage,'directionality'=>$dirType),'full');?></div></div>
                <div class="wv-news-editor-gallery">
                    <input type="file" id="NewsPostBodyGalleryImage" name="news_body_gallery_image" accept="image/*">
                    <span class="wv-news-editor-gallery-note"><i class="fa fa-info-circle"></i> <?php echo __('Insert a gallery image at the current editor position.');?></span>
                </div>
                <div class="form-group"><label class="col-sm-2 control-label"><?php echo __('SEO Title');?></label><div class="col-sm-10"><?php echo $this->Form->input('meta_title',array('label'=>false,'div'=>false,'class'=>'form-control'));?></div></div>
                <div class="form-group"><label class="col-sm-2 control-label"><?php echo __('SEO Description');?></label><div class="col-sm-10"><?php echo $this->Form->input('meta_description',array('type'=>'textarea','rows'=>3,'label'=>false,'div'=>false,'class'=>'form-control'));?></div></div>
            </div>
            <aside class="wv-news-sidebar">
                <div class="wv-news-box">
                    <h4><?php echo __('Publish Settings');?></h4>
                    <div class="form-group"><label><?php echo __('Category');?></label><?php echo $this->Form->input('news_category_id',array('label'=>false,'div'=>false,'class'=>'form-control','options'=>$categories,'empty'=>__('Uncategorized')));?></div>
                    <div class="form-group"><label><?php echo __('Status');?></label><?php echo $this->Form->input('status',array('label'=>false,'div'=>false,'class'=>'form-control','options'=>$statusOptions,'default'=>'Draft'));?></div>
                    <div class="form-group">
                        <label><?php echo __('Published At');?></label>
                        <div class="input-group date wv-news-date-picker" id="NewsPostPublishedAtPicker">
                            <?php echo $this->Form->input('published_at',array('id'=>'NewsPostPublishedAt','type'=>'text','label'=>false,'div'=>false,'class'=>'form-control','autocomplete'=>'off','placeholder'=>'YYYY-MM-DD HH:MM:SS'));?>
                            <span class="input-group-addon" title="<?php echo h(__('Select date and time'));?>"><i class="fa fa-calendar"></i></span>
                        </div>
                        <small class="text-muted"><?php echo __('Leave empty to use the current date and time automatically when published.');?></small>
                    </div>
                    <div class="checkbox"><label><?php echo $this->Form->checkbox('is_featured',array('hiddenField'=>false));?> <?php echo __('Featured Post');?></label></div>
                    <div class="checkbox"><label><?php echo $this->Form->checkbox('allow_comments',array('hiddenField'=>false));?> <?php echo __('Allow Comments');?></label></div>
                </div>
                <div class="wv-news-box">
                    <h4><?php echo __('Thumbnail Image');?></h4>
                    <?php if(!empty($this->request->data['NewsPost']['thumbnail'])){ echo $this->Html->image('news/'.$this->request->data['NewsPost']['thumbnail'],array('class'=>'wv-preview-thumb')); } ?>
                    <?php echo $this->Form->input('thumbnail_file',array('type'=>'file','accept'=>'image/*','label'=>false,'div'=>false,'class'=>'form-control'));?>
                    <small class="text-muted"><?php echo __('Allowed: jpg, jpeg, png, gif, webp');?></small>
                </div>
                <div class="wv-news-box">
                    <h4><?php echo __('Tags');?></h4>
                    <?php echo $this->Form->input('tags',array('label'=>false,'div'=>false,'class'=>'form-control','placeholder'=>__('comma separated')));?>
                </div>
            </aside>
        </div>
        <div class="wv-news-actions"><button class="btn btn-success" type="submit"><i class="fa fa-save"></i> <?php echo h($submitText);?></button><?php echo $this->Html->link(__('Back'),array('action'=>'index'),array('class'=>'btn btn-danger'));?></div>
        <?php echo $this->Form->end();?>
    </div>
</div>
<script>
(function(window,$){
    if(!$){ return; }
    $(function(){
        var $bodyGallery=$('#NewsPostBodyGalleryImage');
        var $publishedAtPicker=$('#NewsPostPublishedAtPicker');
        if($publishedAtPicker.length && $.fn.datetimepicker){
            $publishedAtPicker.datetimepicker({
                locale:'<?php echo addslashes($configLanguage);?>',
                format:'YYYY-MM-DD HH:mm:ss',
                useCurrent:false,
                showTodayButton:true,
                showClear:true,
                showClose:true,
                allowInputToggle:true
            });
        }
        var renameGalleryButton=function(){
            $bodyGallery.siblings('.wv-gallery-upload-tools').find('.btn-info').html('<i class="fa fa-picture-o"></i> <?php echo addslashes(__('Insert Image from Gallery'));?>');
        };
        renameGalleryButton();
        window.setTimeout(renameGalleryButton,100);
        $(document).on('wv:mediaGallerySelected.newsPostBody',function(event,data){
            if(!data || data.inputId!=='NewsPostBodyGalleryImage' || !data.item || !data.item.public_url){ return; }
            var title=data.item.title || data.item.file_name || '';
            var $image=$('<img>',{src:data.item.public_url,alt:title}).css({maxWidth:'100%',height:'auto'});
            var html='<p>'+$('<div>').append($image).html()+'</p>';
            var editor=window.tinymce && window.tinymce.get ? window.tinymce.get('NewsPostBody') : null;
            if(editor){
                editor.focus();
                editor.execCommand('mceInsertContent',false,html);
            }else{
                var $body=$('#NewsPostBody');
                $body.val(($body.val() || '')+html);
            }
            $bodyGallery.val('');
        });
    });
})(window,window.jQuery);
</script>
