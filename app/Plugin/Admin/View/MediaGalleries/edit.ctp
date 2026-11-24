<?php
$media = isset($media) ? (array)$media : array();
$m = !empty($media['MediaFile']) ? (array)$media['MediaFile'] : array();
$folderOptions = isset($folderOptions) ? (array)$folderOptions : array();
$isEditableImage = (!empty($m['file_type']) && $m['file_type']==='image' && !empty($m['exists']) && !empty($m['url']));
$canReplaceEditedImage = (!empty($m['source_type']) && $m['source_type']==='library');
$editorImageUrl = !empty($m['url']) ? $m['url'] : '';
?>
<style>
.wv-media-edit{background:#f6f8fb;border:1px solid #dbe5f0;border-radius:10px;padding:14px;}
.wv-media-edit-head{align-items:center;background:linear-gradient(135deg,#07152a 0%,#132b4b 72%,#ff0a46 150%);border-radius:9px;color:#fff;display:flex;justify-content:space-between;margin-bottom:14px;padding:18px 20px;}
.wv-media-edit-head h1{color:#fff;font-size:22px;font-weight:900;margin:0;}
.wv-media-edit-head p{color:#cfe2ff;font-size:12px;font-weight:700;margin:4px 0 0;}
.wv-media-edit-grid{display:grid;grid-template-columns:320px minmax(0,1fr);gap:14px;}
.wv-media-preview-card,.wv-media-form-card{background:#fff;border:1px solid #dce6f2;border-radius:9px;box-shadow:0 10px 24px rgba(15,23,42,.06);overflow:hidden;}
.wv-media-preview{align-items:center;background:#eef3f8;display:flex;height:260px;justify-content:center;}
.wv-media-preview img{height:100%;object-fit:cover;width:100%;}
.wv-media-preview i{color:#07152a;font-size:68px;}
.wv-media-preview-meta{padding:14px;}
.wv-media-preview-meta h3{color:#07152a;font-size:15px;font-weight:900;line-height:1.35;margin:0 0 8px;}
.wv-media-preview-meta p{color:#64748b;font-size:12px;font-weight:700;line-height:1.7;margin:0;}
.wv-media-form-card{padding:16px;}
.wv-media-form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px;}
.wv-media-field.full{grid-column:1/-1;}
.wv-media-field label{color:#07152a;display:block;font-size:10px;font-weight:900;margin-bottom:5px;text-transform:uppercase;}
.wv-media-field .form-control{border:1px solid #cfdbea;border-radius:7px;height:40px;}
.wv-media-field textarea.form-control{height:80px;}
.wv-media-field input[type=file]{background:#f8fafc;border:1px dashed #b7c6d9;border-radius:8px;padding:10px;width:100%;}
.wv-media-note{background:#fff7ed;border:1px solid #fed7aa;border-radius:8px;color:#9a3412;font-size:12px;font-weight:800;margin-top:8px;padding:10px;}
.wv-media-actions{border-top:1px solid #eef2f7;margin-top:16px;padding-top:14px;}
.wv-media-actions .btn{border-radius:8px;font-weight:900;}
.wv-image-editor-card{background:#fff;border:1px solid #dce6f2;border-radius:9px;box-shadow:0 10px 24px rgba(15,23,42,.06);margin-top:14px;padding:16px;}
.wv-image-editor-title{align-items:flex-start;display:flex;justify-content:space-between;gap:12px;margin-bottom:12px;}
.wv-image-editor-title h3{color:#07152a;font-size:16px;font-weight:900;margin:0;}
.wv-image-editor-title p{color:#64748b;font-size:12px;font-weight:700;line-height:1.6;margin:4px 0 0;}
.wv-image-editor-badge{background:#fff7ed;border:1px solid #fed7aa;border-radius:999px;color:#9a3412;display:inline-block;font-size:10px;font-weight:900;padding:6px 10px;text-transform:uppercase;white-space:nowrap;}
.wv-image-editor-toolbar{align-items:center;background:#f8fafc;border:1px solid #e2eaf5;border-radius:8px;display:flex;flex-wrap:wrap;gap:8px;margin-bottom:12px;padding:10px;}
.wv-image-editor-toolbar .btn,.wv-image-editor-actions .btn{border-radius:8px;font-weight:900;}
.wv-image-editor-toolbar .form-control{border:1px solid #cfdbea;border-radius:8px;font-size:12px;font-weight:800;height:34px;min-width:96px;width:auto;}
.wv-image-editor-canvas-wrap{align-items:center;background:#0b1527;border:1px solid #132b4b;border-radius:10px;display:flex;justify-content:center;min-height:280px;overflow:auto;padding:14px;}
.wv-image-editor-canvas{background:#fff;border-radius:8px;box-shadow:0 12px 28px rgba(0,0,0,.22);cursor:crosshair;height:auto;max-height:620px;max-width:100%;}
.wv-image-editor-actions{align-items:center;border-top:1px solid #eef2f7;display:flex;flex-wrap:wrap;gap:8px;margin-top:12px;padding-top:12px;}
.wv-image-editor-status{color:#64748b;font-size:11px;font-weight:800;margin-left:auto;}
.wv-image-editor-help{background:#f1f5f9;border:1px solid #dbe5f0;border-radius:8px;color:#334155;font-size:12px;font-weight:700;line-height:1.6;margin-top:10px;padding:10px;}
.wv-image-editor-update-original{align-items:center;display:flex;gap:10px;justify-content:space-between;margin-top:10px;}
.wv-image-editor-update-original .btn{border-radius:8px;font-weight:900;}
@media(max-width:767px){.wv-media-edit-grid{display:block}.wv-media-preview-card{margin-bottom:14px}.wv-media-form-grid{grid-template-columns:1fr}.wv-media-edit-head{display:block}.wv-media-edit-head .btn{margin-top:12px;}.wv-image-editor-title{display:block}.wv-image-editor-badge{margin-top:8px}.wv-image-editor-status{flex-basis:100%;margin-left:0}.wv-image-editor-canvas-wrap{min-height:220px;padding:8px}}
</style>

<div class="page-title">
    <div class="title-env"><h1 class="title"><?php echo __('Edit Media');?></h1></div>
</div>

<div class="wv-media-edit">
    <?php echo $this->Session->flash();?>
    <div class="wv-media-edit-head">
        <div>
            <h1><?php echo __('Update Media File');?></h1>
            <p><?php echo __('Edit gallery title, folder, visibility, and replace files uploaded directly into this media library.');?></p>
        </div>
        <?php echo $this->Html->link('<i class="fa fa-arrow-left"></i> '.__('Back'), array('action'=>'index'), array('escape'=>false,'class'=>'btn btn-default'));?>
    </div>

    <div class="wv-media-edit-grid">
        <aside class="wv-media-preview-card">
            <div class="wv-media-preview">
                <?php if(!empty($m['file_type']) && $m['file_type']==='image' && !empty($m['exists'])){ ?>
                    <img src="<?php echo h($m['thumb_url']);?>" alt="<?php echo h($m['title']);?>">
                <?php }else{ ?>
                    <i class="<?php echo h(!empty($m['icon']) ? $m['icon'] : 'fa fa-file-o');?>"></i>
                <?php } ?>
            </div>
            <div class="wv-media-preview-meta">
                <h3><?php echo h(!empty($m['title']) ? $m['title'] : __('Media File'));?></h3>
                <p>
                    <?php echo h(!empty($m['original_name']) ? $m['original_name'] : $m['file_name']);?><br>
                    <?php echo h(strtoupper(!empty($m['extension']) ? $m['extension'] : ''));?> &middot; <?php echo h(!empty($m['size_label']) ? $m['size_label'] : '0 KB');?><br>
                    <?php echo __('Source');?>: <?php echo h(!empty($m['source_type']) ? $m['source_type'] : 'library');?>
                </p>
            </div>
        </aside>

        <section class="wv-media-form-card">
            <?php echo $this->Form->create('MediaFile', array('url'=>array('controller'=>'media_galleries','action'=>'edit',(int)$m['id']),'type'=>'file'));?>
            <?php echo $this->Form->hidden('id');?>
            <div class="wv-media-form-grid">
                <div class="wv-media-field">
                    <label><?php echo __('Title');?></label>
                    <?php echo $this->Form->text('title', array('class'=>'form-control'));?>
                </div>
                <div class="wv-media-field">
                    <label><?php echo __('Alt Text');?></label>
                    <?php echo $this->Form->text('alt_text', array('class'=>'form-control'));?>
                </div>
                <div class="wv-media-field">
                    <label><?php echo __('Folder');?></label>
                    <?php echo $this->Form->select('folder_id', $folderOptions, array('empty'=>__('All Media'),'label'=>false,'div'=>false,'class'=>'form-control'));?>
                </div>
                <div class="wv-media-field">
                    <label><?php echo __('Visibility');?></label>
                    <?php echo $this->Form->select('visibility', array('backend'=>__('Backend Only'),'frontend'=>__('Frontend Only'),'both'=>__('Backend + Frontend')), array('label'=>false,'div'=>false,'class'=>'form-control'));?>
                </div>
                <div class="wv-media-field">
                    <label><?php echo __('Status');?></label>
                    <?php echo $this->Form->select('status', array('Active'=>__('Active'),'Inactive'=>__('Inactive'),'Deleted'=>__('Deleted')), array('label'=>false,'div'=>false,'class'=>'form-control'));?>
                </div>
                <div class="wv-media-field">
                    <label><?php echo __('Replace File');?></label>
                    <?php if(!empty($m['source_type']) && $m['source_type']==='library'){ ?>
                        <input type="file" name="data[MediaFile][upload]" />
                    <?php }else{ ?>
                        <div class="wv-media-note"><?php echo __('Indexed system media cannot be replaced here. You can update its title, folder, and visibility safely.');?></div>
                    <?php } ?>
                </div>
            </div>
            <div class="wv-media-actions">
                <button class="btn btn-primary" type="submit"><i class="fa fa-save"></i> <?php echo __('Save & Close');?></button>
                <?php echo $this->Html->link('<i class="fa fa-close"></i> '.__('Cancel'), array('action'=>'index'), array('escape'=>false,'class'=>'btn btn-default'));?>
            </div>
            <?php echo $this->Form->end();?>

            <?php if($isEditableImage){ ?>
            <div class="wv-image-editor-card">
                <div class="wv-image-editor-title">
                    <div>
                        <h3><?php echo __('Image Tools');?></h3>
                        <p><?php echo __('Crop by dragging on the image. Rotate, mirror, reset, download a preview, then save your edited version.');?></p>
                    </div>
                    <?php if($canReplaceEditedImage){ ?>
                        <span class="wv-image-editor-badge"><?php echo __('Copy or replace');?></span>
                    <?php }else{ ?>
                        <span class="wv-image-editor-badge"><?php echo __('Copy only');?></span>
                    <?php } ?>
                </div>

                <div class="wv-image-editor-toolbar">
                    <button type="button" class="btn btn-default btn-sm" id="wvRotateLeft"><i class="fa fa-rotate-left"></i> <?php echo __('Left');?></button>
                    <button type="button" class="btn btn-default btn-sm" id="wvRotateRight"><i class="fa fa-rotate-right"></i> <?php echo __('Right');?></button>
                    <button type="button" class="btn btn-default btn-sm" id="wvFlipHorizontal"><i class="fa fa-arrows-h"></i> <?php echo __('Mirror H');?></button>
                    <button type="button" class="btn btn-default btn-sm" id="wvFlipVertical"><i class="fa fa-arrows-v"></i> <?php echo __('Mirror V');?></button>
                    <button type="button" class="btn btn-warning btn-sm" id="wvApplyCrop"><i class="fa fa-crop"></i> <?php echo __('Apply Crop');?></button>
                    <button type="button" class="btn btn-default btn-sm" id="wvResetImage"><i class="fa fa-refresh"></i> <?php echo __('Reset');?></button>
                    <select class="form-control" id="wvExportFormat">
                        <option value="jpeg"><?php echo __('Save JPG');?></option>
                        <option value="png"><?php echo __('Save PNG');?></option>
                    </select>
                </div>

                <div class="wv-image-editor-canvas-wrap">
                    <canvas class="wv-image-editor-canvas" id="wvImageEditorCanvas"></canvas>
                </div>

                <div class="wv-image-editor-actions">
                    <button type="button" class="btn btn-info" id="wvDownloadEdited"><i class="fa fa-download"></i> <?php echo __('Download Preview');?></button>
                    <button type="button" class="btn btn-primary" id="wvSaveEditedCopy"><i class="fa fa-copy"></i> <?php echo __('Save Copy & Close');?></button>
                    <?php if($canReplaceEditedImage){ ?>
                        <button type="button" class="btn btn-danger" id="wvReplaceCurrent"><i class="fa fa-save"></i> <?php echo __('Replace & Close');?></button>
                    <?php } ?>
                    <span class="wv-image-editor-status" id="wvEditorStatus"><?php echo __('Loading image editor...');?></span>
                </div>

                <div class="wv-image-editor-help">
                    <?php if($canReplaceEditedImage){ ?>
                        <?php echo __('Use Save Edited Copy to keep the original. Use Replace Current only for images uploaded directly into Media Gallery.');?>
                    <?php }else{ ?>
                        <?php echo __('This image belongs to another module, so edits are saved as a new gallery copy and the original file stays unchanged.');?>
                        <div class="wv-image-editor-update-original">
                            <span><?php echo __('To overwrite this same source file and keep its original name, use Save Update.');?></span>
                            <button type="button" class="btn btn-success btn-sm" id="wvUpdateOriginal"><i class="fa fa-check-circle"></i> <?php echo __('Save Update & Close');?></button>
                        </div>
                    <?php } ?>
                </div>

                <?php echo $this->Form->create('MediaFile', array('url'=>array('controller'=>'media_galleries','action'=>'saveimageedit', (int)$m['id']), 'id'=>'wvImageEditorForm'));?>
                    <?php echo $this->Form->hidden('edited_image', array('id'=>'wvEditedImageData'));?>
                    <?php echo $this->Form->hidden('save_mode', array('id'=>'wvEditedSaveMode', 'value'=>'copy'));?>
                <?php echo $this->Form->end();?>
            </div>
            <?php } ?>
        </section>
    </div>
</div>

<?php if($isEditableImage){ ?>
<script type="text/javascript">
(function($){
    var imageUrl = <?php echo json_encode($editorImageUrl);?>;
    var originalExtension = <?php echo json_encode(strtolower(pathinfo(!empty($m['file_name']) ? $m['file_name'] : '', PATHINFO_EXTENSION)));?>;
    if(originalExtension === 'jpeg'){
        originalExtension = 'jpg';
    }
    var canvas = document.getElementById('wvImageEditorCanvas');
    if(!canvas || !imageUrl){
        return;
    }
    var ctx = canvas.getContext('2d');
    var workCanvas = document.createElement('canvas');
    var workCtx = workCanvas.getContext('2d');
    var originalImage = new Image();
    var selection = null;
    var selecting = false;
    var startPoint = null;
    var statusBox = $('#wvEditorStatus');

    function setStatus(text){
        statusBox.text(text);
    }

    function clamp(value, min, max){
        return Math.max(min, Math.min(max, value));
    }

    function normalizeSelection(raw){
        if(!raw){
            return null;
        }
        var x1 = clamp(raw.x1, 0, canvas.width);
        var y1 = clamp(raw.y1, 0, canvas.height);
        var x2 = clamp(raw.x2, 0, canvas.width);
        var y2 = clamp(raw.y2, 0, canvas.height);
        var x = Math.round(Math.min(x1, x2));
        var y = Math.round(Math.min(y1, y2));
        var w = Math.round(Math.abs(x2 - x1));
        var h = Math.round(Math.abs(y2 - y1));
        if(w < 2 || h < 2){
            return null;
        }
        return {x:x, y:y, w:w, h:h};
    }

    function drawSelection(){
        var box = normalizeSelection(selection);
        if(!box){
            return;
        }
        ctx.save();
        ctx.fillStyle = 'rgba(255,10,70,.16)';
        ctx.fillRect(box.x, box.y, box.w, box.h);
        ctx.strokeStyle = '#ff0a46';
        ctx.lineWidth = Math.max(2, Math.round(canvas.width / 900));
        ctx.strokeRect(box.x, box.y, box.w, box.h);
        ctx.fillStyle = 'rgba(7,21,42,.54)';
        ctx.fillRect(0, 0, canvas.width, box.y);
        ctx.fillRect(0, box.y + box.h, canvas.width, canvas.height - (box.y + box.h));
        ctx.fillRect(0, box.y, box.x, box.h);
        ctx.fillRect(box.x + box.w, box.y, canvas.width - (box.x + box.w), box.h);
        ctx.restore();
    }

    function render(){
        canvas.width = workCanvas.width;
        canvas.height = workCanvas.height;
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        ctx.drawImage(workCanvas, 0, 0);
        drawSelection();
        setStatus(canvas.width + ' x ' + canvas.height);
    }

    function replaceWork(newCanvas){
        workCanvas = newCanvas;
        workCtx = workCanvas.getContext('2d');
        selection = null;
        render();
    }

    function resetImage(){
        var maxSide = 2400;
        var width = originalImage.naturalWidth || originalImage.width;
        var height = originalImage.naturalHeight || originalImage.height;
        var scale = Math.min(1, maxSide / Math.max(width, height));
        workCanvas.width = Math.max(1, Math.round(width * scale));
        workCanvas.height = Math.max(1, Math.round(height * scale));
        workCtx.clearRect(0, 0, workCanvas.width, workCanvas.height);
        workCtx.drawImage(originalImage, 0, 0, workCanvas.width, workCanvas.height);
        selection = null;
        render();
    }

    function eventPoint(event){
        var originalEvent = event.originalEvent || event;
        var pointer = originalEvent;
        if(originalEvent.touches && originalEvent.touches.length){
            pointer = originalEvent.touches[0];
        }else if(originalEvent.changedTouches && originalEvent.changedTouches.length){
            pointer = originalEvent.changedTouches[0];
        }
        var rect = canvas.getBoundingClientRect();
        return {
            x: (pointer.clientX - rect.left) * (canvas.width / rect.width),
            y: (pointer.clientY - rect.top) * (canvas.height / rect.height)
        };
    }

    function rotateImage(degrees){
        var temp = document.createElement('canvas');
        temp.width = workCanvas.height;
        temp.height = workCanvas.width;
        var tempCtx = temp.getContext('2d');
        tempCtx.translate(temp.width / 2, temp.height / 2);
        tempCtx.rotate(degrees * Math.PI / 180);
        tempCtx.drawImage(workCanvas, -workCanvas.width / 2, -workCanvas.height / 2);
        replaceWork(temp);
    }

    function flipImage(horizontal){
        var temp = document.createElement('canvas');
        temp.width = workCanvas.width;
        temp.height = workCanvas.height;
        var tempCtx = temp.getContext('2d');
        if(horizontal){
            tempCtx.translate(temp.width, 0);
            tempCtx.scale(-1, 1);
        }else{
            tempCtx.translate(0, temp.height);
            tempCtx.scale(1, -1);
        }
        tempCtx.drawImage(workCanvas, 0, 0);
        replaceWork(temp);
    }

    function applyCrop(){
        var box = normalizeSelection(selection);
        if(!box || box.w < 20 || box.h < 20){
            setStatus(<?php echo json_encode(__('Drag a larger crop area first.'));?>);
            return;
        }
        var temp = document.createElement('canvas');
        temp.width = box.w;
        temp.height = box.h;
        temp.getContext('2d').drawImage(workCanvas, box.x, box.y, box.w, box.h, 0, 0, box.w, box.h);
        replaceWork(temp);
    }

    function exportDataUrl(forceOriginalType){
        var format = forceOriginalType ? (originalExtension === 'png' ? 'png' : 'jpeg') : $('#wvExportFormat').val();
        if(format === 'png'){
            return workCanvas.toDataURL('image/png');
        }
        return workCanvas.toDataURL('image/jpeg', 0.92);
    }

    function downloadEdited(){
        var format = $('#wvExportFormat').val() === 'png' ? 'png' : 'jpg';
        var link = document.createElement('a');
        link.href = exportDataUrl();
        link.download = 'edited-media-<?php echo (int)$m['id'];?>.' + format;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    function submitEdited(mode){
        setStatus(<?php echo json_encode(__('Preparing edited image...'));?>);
        $('#wvEditedImageData').val(exportDataUrl(mode === 'update_original'));
        $('#wvEditedSaveMode').val(mode === 'replace' ? 'replace' : (mode === 'update_original' ? 'update_original' : 'copy'));
        document.getElementById('wvImageEditorForm').submit();
    }

    $(canvas).on('mousedown touchstart', function(event){
        if(!canvas.width || !canvas.height){
            return;
        }
        event.preventDefault();
        selecting = true;
        startPoint = eventPoint(event);
        selection = {x1:startPoint.x, y1:startPoint.y, x2:startPoint.x, y2:startPoint.y};
        render();
    });

    $(document).on('mousemove.wvImageEditor touchmove.wvImageEditor', function(event){
        if(!selecting || !startPoint){
            return;
        }
        event.preventDefault();
        var point = eventPoint(event);
        selection = {x1:startPoint.x, y1:startPoint.y, x2:point.x, y2:point.y};
        render();
    });

    $(document).on('mouseup.wvImageEditor touchend.wvImageEditor touchcancel.wvImageEditor', function(event){
        if(!selecting){
            return;
        }
        event.preventDefault();
        selecting = false;
        startPoint = null;
        render();
    });

    $('#wvRotateLeft').on('click', function(){ rotateImage(-90); });
    $('#wvRotateRight').on('click', function(){ rotateImage(90); });
    $('#wvFlipHorizontal').on('click', function(){ flipImage(true); });
    $('#wvFlipVertical').on('click', function(){ flipImage(false); });
    $('#wvApplyCrop').on('click', applyCrop);
    $('#wvResetImage').on('click', resetImage);
    $('#wvDownloadEdited').on('click', downloadEdited);
    $('#wvSaveEditedCopy').on('click', function(){ submitEdited('copy'); });
    $('#wvUpdateOriginal').on('click', function(){
        if(confirm(<?php echo json_encode(__('This will overwrite the original image file using the same file name. Continue?'));?>)){
            submitEdited('update_original');
        }
    });
    $('#wvReplaceCurrent').on('click', function(){
        if(confirm(<?php echo json_encode(__('Replace this gallery image with the edited version?'));?>)){
            submitEdited('replace');
        }
    });

    originalImage.onload = function(){
        resetImage();
    };
    originalImage.onerror = function(){
        setStatus(<?php echo json_encode(__('Image could not be loaded for editing.'));?>);
    };
    originalImage.src = imageUrl + (imageUrl.indexOf('?') === -1 ? '?' : '&') + 'v=' + (new Date().getTime());
})(jQuery);
</script>
<?php } ?>
