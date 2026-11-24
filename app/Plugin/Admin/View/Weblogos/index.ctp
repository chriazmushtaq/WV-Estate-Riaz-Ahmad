<style>
.wv-weblogo-page{background:#f6f8fb;border:1px solid #dbe5f0;border-radius:10px;padding:14px;}
.wv-weblogo-head{background:linear-gradient(135deg,#07152a 0%,#132b4b 72%,#ff0a46 150%);border-radius:9px;color:#fff;margin-bottom:14px;padding:18px 20px;}
.wv-weblogo-head h2{color:#fff;font-size:22px;font-weight:900;margin:0;}
.wv-weblogo-head p{color:#cfe2ff;font-size:12px;font-weight:700;margin:4px 0 0;}
.wv-weblogo-card{background:#fff;border:1px solid #dce6f2;border-radius:10px;box-shadow:0 10px 24px rgba(15,23,42,.06);margin-bottom:14px;padding:18px;}
.wv-weblogo-card h4{color:#07152a;font-size:16px;font-weight:900;margin:0 0 6px;}
.wv-weblogo-card .text-muted{color:#64748b;font-size:12px;font-weight:700;line-height:1.6;}
.wv-weblogo-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px;}
.wv-weblogo-grid .full{grid-column:1/-1;}
.wv-weblogo-field label{color:#07152a;display:block;font-size:10px;font-weight:900;margin:0 0 6px;text-align:left;text-transform:uppercase;}
.wv-weblogo-field .form-control{border:1px solid #cfdbea;border-radius:8px;height:40px;}
.wv-weblogo-field textarea.form-control{height:auto;}
.wv-weblogo-field input[type=file]{background:#f8fafc;border:1px dashed #b7c6d9;border-radius:8px;padding:10px;width:100%;}
.wv-weblogo-logo-row{align-items:flex-start;display:grid;grid-template-columns:minmax(0,1fr) 220px;gap:14px;}
.wv-weblogo-preview{align-items:center;background:#f8fafc;border:1px solid #dce6f2;border-radius:10px;display:flex;justify-content:center;min-height:128px;padding:12px;}
.wv-weblogo-preview img{max-height:110px;max-width:100%;object-fit:contain;}
.wv-weblogo-empty{color:#64748b;font-size:12px;font-weight:800;text-align:center;}
.wv-weblogo-selected-name{color:#047857;font-size:11px;font-weight:900;margin-top:8px;}
.wv-weblogo-actions{border-top:1px solid #eef2f7;display:flex;flex-wrap:wrap;gap:8px;margin-top:16px;padding-top:14px;}
.wv-weblogo-actions .btn{border-radius:8px;font-weight:900;}
@media(max-width:767px){.wv-weblogo-grid,.wv-weblogo-logo-row{grid-template-columns:1fr}.wv-weblogo-card{padding:14px}}
</style>

<div class="page-title">
    <div class="title-env">
        <h1 class="title"><?php echo __('Organisation');?> <?php echo __('Logo');?></h1>
    </div>
</div>

<script type="text/javascript">
jQuery(function($){
    var $logoInput = $('#WeblogoPhoto');
    var $preview = $('.wv-weblogo-preview');
    var $name = $('#WeblogoSelectedName');
    var submitPrepared = false;

    function renderLogoPreview(file, storeFallbackData){
        if(!file){
            $name.text('');
            $('#WeblogoLogoData,#WeblogoLogoName').val('');
            return;
        }
        $name.text('<?php echo h(__('Selected'));?>: ' + file.name);
        if(!file.type || file.type.indexOf('image/') !== 0){
            $('#WeblogoLogoData,#WeblogoLogoName').val('');
            return;
        }
        if(!window.FileReader){
            return;
        }
        var reader = new FileReader();
        reader.onload = function(e){
            $preview.html('<img src="'+e.target.result+'" alt="<?php echo h(__('Selected Logo'));?>">');
            if(storeFallbackData){
                $('#WeblogoLogoData').val(e.target.result || '');
                $('#WeblogoLogoName').val(file.name || 'organisation-logo.png');
            }
        };
        reader.readAsDataURL(file);
    }

    function prepareLogoDataFromFile(file, callback){
        if(!file || !file.type || file.type.indexOf('image/') !== 0){
            callback(false);
            return;
        }
        if(!window.FileReader){
            callback(false);
            return;
        }
        var reader = new FileReader();
        reader.onload = function(e){
            var dataUrl = e.target.result || '';
            if(!window.HTMLCanvasElement){
                $('#WeblogoLogoData').val(dataUrl);
                $('#WeblogoLogoName').val(file.name || 'organisation-logo.png');
                callback(true);
                return;
            }
            var img = new Image();
            img.onload = function(){
                var maxHeight = 220;
                var width = img.width || 1;
                var height = img.height || 1;
                if(height > maxHeight){
                    var ratio = maxHeight / height;
                    width = Math.max(1, Math.round(width * ratio));
                    height = maxHeight;
                }
                var canvas = document.createElement('canvas');
                canvas.width = width;
                canvas.height = height;
                var ctx = canvas.getContext('2d');
                ctx.clearRect(0, 0, width, height);
                ctx.drawImage(img, 0, 0, width, height);
                var outputType = file.type === 'image/png' ? 'image/png' : 'image/jpeg';
                $('#WeblogoLogoData').val(canvas.toDataURL(outputType, 0.92));
                $('#WeblogoLogoName').val(file.name || 'organisation-logo.png');
                callback(true);
            };
            img.onerror = function(){
                $('#WeblogoLogoData').val(dataUrl);
                $('#WeblogoLogoName').val(file.name || 'organisation-logo.png');
                callback(true);
            };
            img.src = dataUrl;
        };
        reader.onerror = function(){
            callback(false);
        };
        reader.readAsDataURL(file);
    }

    $logoInput.on('change.weblogoPreview', function(){
        var input = this;
        if(input.files && input.files.length){
            renderLogoPreview(input.files[0], false);
        }else{
            $name.text('');
            $('#WeblogoLogoData,#WeblogoLogoName').val('');
        }
    });

    $logoInput.on('click.weblogoNative', function(){
        $(this).data('nativeSelecting', 1);
    });

    $(document).on('wv:mediaGallerySelected', function(e, data){
        if(data && data.inputId === 'WeblogoPhoto' && data.file){
            $logoInput.data('nativeSelecting', 0);
            $('#WeblogoGalleryMediaId').val(data.item && data.item.id ? data.item.id : '');
            renderLogoPreview(data.file, true);
        }
    });

    $(document).on('click.weblogoPickerId', '#wvMediaPickerModal .js-wv-use-media', function(){
        var item = $(this).closest('.wv-media-picker-card').data('item');
        if(item && item.id){
            $('#WeblogoGalleryMediaId').val(item.id);
        }
    });

    $logoInput.on('change.weblogoGalleryId', function(){
        if($(this).data('nativeSelecting')){
            $('#WeblogoGalleryMediaId').val('');
            $('#WeblogoLogoData,#WeblogoLogoName').val('');
            $(this).data('nativeSelecting', 0);
        }
    });

    $('.wv-weblogo-form').on('submit.weblogo', function(e){
        if(submitPrepared){
            return true;
        }
        var input = $logoInput.get(0);
        if(input && input.files && input.files.length){
            e.preventDefault();
            prepareLogoDataFromFile(input.files[0], function(){
                submitPrepared = true;
                $('.wv-weblogo-form').get(0).submit();
            });
            return false;
        }
        if(!$('#WeblogoGalleryMediaId').val()){
            $('#WeblogoLogoData,#WeblogoLogoName').val('');
        }
        return true;
    });
});
</script>

<div class="wv-weblogo-page">
    <div class="wv-weblogo-head">
        <h2><?php echo __('Organisation Logo');?></h2>
        <p><?php echo __('Update website logo and client portal embed settings.');?></p>
    </div>

    <div class="wv-weblogo-card">
        <?php echo $this->Session->flash();?>
        <?php echo $this->Form->create('Weblogo', array('url' => array('controller' => 'Weblogos', 'action' => 'index'), 'name' => 'post_req', 'id' => 'post_req', 'class' => 'wv-weblogo-form', 'type' => 'file', 'enctype' => 'multipart/form-data'));?>

        <div class="wv-weblogo-logo-row">
            <div class="wv-weblogo-field">
                <label for="WeblogoPhoto"><?php echo __('Upload Image');?> <span class="text-muted"><?php echo __('JPG/PNG, height less than 220px');?></span></label>
                <?php echo $this->Form->input('photo', array('type' => 'file', 'label' => false, 'class' => '', 'placeholder' => __('Upload Image'), 'div' => false, 'accept' => '.jpg,.jpeg,.png,image/jpeg,image/png', 'id' => 'WeblogoPhoto', 'data-wv-logo-max-height' => '220'));?>
                <?php echo $this->Form->hidden('gallery_media_id', array('id'=>'WeblogoGalleryMediaId', 'value'=>''));?>
                <?php echo $this->Form->hidden('logo_data', array('id'=>'WeblogoLogoData', 'value'=>''));?>
                <?php echo $this->Form->hidden('logo_name', array('id'=>'WeblogoLogoName', 'value'=>''));?>
                <div class="wv-weblogo-selected-name" id="WeblogoSelectedName"></div>
            </div>
            <div class="wv-weblogo-preview">
                <?php if(strlen($frontLogo) > 0){ echo $this->Html->image($frontLogo, array('alt' => $siteName)); }else{ ?>
                    <div class="wv-weblogo-empty"><?php echo __('No logo uploaded');?></div>
                <?php } ?>
            </div>
        </div>

        <hr>
        <h4><?php echo __('Client Portal Embed Settings');?></h4>
        <p class="text-muted" style="margin-bottom:15px;">
            <?php echo __('Configure shortcode and embed HTML for client portal login that you can use on other websites or inside page builders.');?>
        </p>

        <div class="wv-weblogo-grid">
            <div class="wv-weblogo-field full">
                <label><?php echo __('Portal Login URL');?></label>
                <input type="text" class="form-control" readonly="readonly" value="<?php echo h($clientPortalLoginUrl);?>">
            </div>

            <div class="wv-weblogo-field">
                <label><?php echo __('Embed Width');?></label>
                <?php echo $this->Form->input('client_portal_embed_width', array('label' => false, 'class' => 'form-control', 'placeholder' => '100%', 'div' => false));?>
            </div>
            <div class="wv-weblogo-field">
                <label><?php echo __('Embed Height (px)');?></label>
                <?php echo $this->Form->input('client_portal_embed_height', array('type' => 'number', 'min' => 300, 'max' => 2000, 'label' => false, 'class' => 'form-control', 'placeholder' => '740', 'div' => false));?>
            </div>

            <div class="wv-weblogo-field">
                <label><?php echo __('Button Label');?></label>
                <?php echo $this->Form->input('client_portal_button_label', array('label' => false, 'class' => 'form-control', 'placeholder' => __('Client Portal Login'), 'div' => false));?>
            </div>
            <div class="wv-weblogo-field">
                <label><?php echo __('Open Target');?></label>
                <?php echo $this->Form->select('client_portal_open_target', array('_blank' => __('New Tab'), '_self' => __('Same Tab')), array('empty' => false, 'label' => false, 'class' => 'form-control', 'div' => false));?>
            </div>
        </div>

        <div class="wv-weblogo-actions">
            <button type="submit" class="btn btn-success"><span class="fa fa-save"></span> <?php echo __('Save');?></button>
            <?php echo $this->Html->link('<span class="fa fa-trash"></span>&nbsp;'.__('Delete Logo'), array('controller' => 'Weblogos', 'action' => 'weblogodel'), array('escape' => false, 'class' => 'btn btn-danger'));?>
        </div>

        <?php echo $this->Form->end();?>
    </div>

    <div class="wv-weblogo-card">
        <h4><?php echo __('Usage Documentation');?></h4>
        <p class="text-muted"><?php echo __('Use any one of the following options on your external website or CMS page.');?></p>

        <div class="wv-weblogo-field">
            <label><?php echo __('Shortcode');?></label>
            <textarea class="form-control" rows="2" readonly="readonly"><?php echo h($clientPortalShortcode);?></textarea>
            <p class="help-block"><?php echo __('Use this token in systems that support shortcode mapping (you can map it to iframe/html widget).');?></p>
        </div>

        <div class="wv-weblogo-field">
            <label><?php echo __('Embed Code (Iframe)');?></label>
            <textarea class="form-control" rows="4" readonly="readonly"><?php echo h($clientPortalEmbedCode);?></textarea>
            <p class="help-block"><?php echo __('Paste inside HTML block/widget to show login form directly on another website page.');?></p>
        </div>

        <div class="wv-weblogo-field">
            <label><?php echo __('In-Page Link/Button Code');?></label>
            <textarea class="form-control" rows="3" readonly="readonly"><?php echo h($clientPortalInPageCode);?></textarea>
            <p class="help-block"><?php echo __('Use this if you only want a simple link/button to your client portal login.');?></p>
        </div>

        <div class="alert alert-info" style="margin-bottom:0;">
            <strong><?php echo __('Note');?>:</strong>
            <?php echo __('After changing settings above, click Save first, then copy updated code snippets.');?>
        </div>
    </div>
</div>
