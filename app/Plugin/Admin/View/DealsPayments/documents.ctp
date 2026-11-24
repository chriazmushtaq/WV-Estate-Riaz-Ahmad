<?php
$dealRow = !empty($dealMeta) ? $dealMeta : array();
$dealInfo = !empty($dealRow['Deal']) ? $dealRow['Deal'] : array();
$clientInfo = !empty($dealRow['Client']) ? $dealRow['Client'] : array();
$propertyInfo = !empty($dealRow['Property']) ? $dealRow['Property'] : array();
$projectInfo = !empty($dealRow['Project']) ? $dealRow['Project'] : array();
$clientWhatsAppPhone = !empty($clientInfo['mobile']) ? $clientInfo['mobile'] : (!empty($clientInfo['phone']) ? $clientInfo['phone'] : '');
$clientDisplayName = !empty($clientInfo['name']) ? $clientInfo['name'] : __('Client');
$propertyDisplayName = !empty($propertyInfo['name']) ? $propertyInfo['name'] : __('your file');
$paidTabUrl = $this->Html->url(array('controller'=>'deals_payments','action'=>'index',$dealId));
$paymentPlanTabUrl = $this->Html->url(array('controller'=>'plans_payments','action'=>'index',$dealId));
$documentTabUrl = $this->Html->url(array('controller'=>'deals_payments','action'=>'documents',$dealId));
$lettersTabUrl = $this->Html->url(array('controller'=>'deals_payments','action'=>'letters',$dealId));
$communicationTabUrl = $this->Html->url(array('controller'=>'deals_payments','action'=>'communication',$dealId));
echo $this->Html->css('https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css');
echo $this->Html->script('https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js');
?>

<style type="text/css">
.deal-documents-wrap {
    background: #f6f9fc;
    border: 1px solid #dbe4ee;
    padding: 12px;
}
.deal-documents-head {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    margin-bottom: 10px;
}
.deal-documents-meta {
    font-size: 12px;
    color: #31465b;
}
.deal-doc-upload-panel {
    border: 1px solid #dbe4ee;
    background: #fff;
    padding: 10px;
    margin-bottom: 10px;
}
#dealDocumentUploadModal .deal-doc-upload-panel {
    border: 0;
    background: transparent;
    padding: 0;
    margin-bottom: 0;
}
.deal-doc-drop-upload {
    border: 2px dashed #9bb0c4;
    background: #f9fbfd;
    border-radius: 6px;
    padding: 12px;
    text-align: center;
    transition: all 0.2s ease;
    cursor: pointer;
    position: relative;
    min-height: 130px;
}
.deal-doc-drop-upload.drag-over,
.deal-doc-drop-upload:hover {
    border-color: #2f6c9f;
    background: #eef5fb;
}
.deal-doc-browse-wrap {
    position: relative;
    overflow: hidden;
    cursor: pointer;
    margin-bottom: 0;
}
.deal-doc-browse-wrap input[type="file"] {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
}
.deal-doc-drop-message {
    font-size: 12px;
    color: #4b647e;
    margin-bottom: 8px;
}
.deal-doc-drop-preview {
    min-height: 52px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    gap: 6px;
}
.deal-doc-drop-preview img {
    max-height: 92px;
    max-width: 100%;
    border: 1px solid #d4dde8;
    border-radius: 4px;
}
.deal-doc-thumb {
    max-width: 75px;
    max-height: 75px;
    border: 1px solid #d4dde8;
    border-radius: 4px;
}
.deal-doc-action-group .btn {
    margin-right: 4px;
    margin-bottom: 3px;
}
</style>

<div id="deal-documents-content">
    <?php echo $this->Session->flash(); ?>
    <div class="deal-documents-wrap">
        <div class="deal-documents-head">
            <div class="deal-documents-meta">
                <strong><?php echo __('Agency'); ?>:</strong> <?php echo h(!empty($projectInfo['name']) ? $projectInfo['name'] : '-'); ?>
                &nbsp; | &nbsp;
                <strong><?php echo __('Client'); ?>:</strong> <?php echo h(!empty($clientInfo['name']) ? $clientInfo['name'] : '-'); ?>
                &nbsp; | &nbsp;
                <strong><?php echo __('File'); ?>:</strong> <?php echo h(!empty($propertyInfo['name']) ? $propertyInfo['name'] : '-'); ?>
            </div>
            <div>
                <button type="button" class="btn btn-success btn-sm js-doc-upload-toggle" data-target="#dealDocumentUploadModal">
                    <span class="fa fa-upload"></span>&nbsp;<?php echo __('Add Document'); ?>
                </button>
            </div>
        </div>

        <ul class="nav nav-tabs deal-comm-tabs no-print" style="margin-bottom:10px;">
            <li><a href="<?php echo $paidTabUrl; ?>"><?php echo __('Paid'); ?></a></li>
            <li><a href="<?php echo $paymentPlanTabUrl; ?>"><?php echo __('Payment Plan'); ?></a></li>
            <li class="active"><a href="javascript:void(0);"><?php echo __('Document'); ?></a></li>
            <li><a href="<?php echo $lettersTabUrl; ?>"><?php echo __('Letters'); ?></a></li>
            <li><a href="<?php echo $communicationTabUrl; ?>"><?php echo __('Communication'); ?></a></li>
        </ul>

        <div class="modal fade" id="dealDocumentUploadModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="<?php echo __('Close'); ?>"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title"><?php echo __('Add Document'); ?></h4>
                    </div>
                    <div class="modal-body">
                        <div id="deal-doc-upload-panel" class="deal-doc-upload-panel" data-upload-url="<?php echo h($this->Html->url(array('controller'=>'deals_payments','action'=>'documents',$dealId))); ?>">
                            <div class="row">
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label class="control-label"><?php echo __('Document Type'); ?></label>
                                        <?php
                                        echo $this->Form->select('document_type',$documentTypeOptions,array(
                                            'empty'=>__('Please Select'),
                                            'class'=>'form-control',
                                            'label'=>false,
                                            'div'=>false,
                                            'required'=>true
                                        ));
                                        ?>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label class="control-label"><?php echo __('Date'); ?></label>
                                        <input type="date" name="data[DealDocument][document_date]" class="form-control js-doc-date-picker" value="<?php echo h(date('Y-m-d')); ?>" />
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="control-label"><?php echo __('Upload File'); ?></label>
                                        <div class="deal-doc-drop-upload" data-input-id="DealDocumentUploadFile">
                                            <div class="deal-doc-drop-message">
                                                <?php echo __('Drag & drop file here, or use Browse. Supported: PDF, DOC, DOCX, JPG, PNG, GIF, WEBP'); ?>
                                            </div>
                                            <div class="deal-doc-drop-preview"></div>
                                            <div style="margin-top:8px;">
                                                <span class="btn btn-info btn-xs js-doc-browse deal-doc-browse-wrap">
                                                    <?php echo __('Browse'); ?>
                                                    <input type="file" name="data[DealDocument][upload_file]" id="DealDocumentUploadFile" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif,.webp" />
                                                </span>
                                                <button type="button" class="btn btn-danger btn-xs js-doc-clear"><?php echo __('Clear'); ?></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group" style="margin-bottom:0;">
                                <button type="button" class="btn btn-success js-doc-upload-submit">
                                    <span class="fa fa-save"></span>&nbsp;<?php echo __('Upload Document'); ?>
                                </button>
                                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Close'); ?></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th style="width:50px;"><?php echo __('#'); ?></th>
                        <th style="width:130px;"><?php echo __('Date'); ?></th>
                        <th style="width:160px;"><?php echo __('Document Type'); ?></th>
                        <th><?php echo __('File Name'); ?></th>
                        <th style="width:100px;"><?php echo __('Preview'); ?></th>
                        <th style="width:260px;"><?php echo __('Action'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($dealDocuments)){ $sr=1; foreach($dealDocuments as $row){
                        $doc = !empty($row['DealDocument']) ? $row['DealDocument'] : array();
                        $docId = !empty($doc['id']) ? (int)$doc['id'] : 0;
                        $docDir = !empty($doc['dir']) ? trim($doc['dir'],'/') : 'dealsdocuments';
                        $docFile = !empty($doc['photo']) ? $doc['photo'] : '';
                        $docUrl = $this->Html->url('/img/'.$docDir.'/'.$docFile);
                        $docFullUrl = $this->Html->url('/img/'.$docDir.'/'.$docFile, true);
                        $docDate = !empty($doc['document_date']) && $doc['document_date'] !== '0000-00-00' ? $this->Time->format('d-M-Y',$doc['document_date']) : '-';
                        $docTypeLabel = !empty($doc['document_type']) ? $doc['document_type'] : 'Others';
                        $waMessage = sprintf(
                            "Dear %s,\nPlease find attached %s for %s.\nRegards,\nWhite Villas",
                            $clientDisplayName,
                            $docTypeLabel,
                            $propertyDisplayName
                        );
                        $isImage = !empty($doc['is_image']);
                        if(!$isImage && preg_match('/\.(jpe?g|png|gif|webp)$/i',$docFile))
                        {
                            $isImage = true;
                        }
                        ?>
                        <tr>
                            <td><?php echo $sr++; ?></td>
                            <td><?php echo h($docDate); ?></td>
                            <td><?php echo h($docTypeLabel); ?></td>
                            <td><?php echo h($docFile); ?></td>
                            <td>
                                <?php if($isImage && $docFile!==''){ ?>
                                    <img class="deal-doc-thumb" src="<?php echo h($docUrl); ?>" alt="<?php echo __('Document Image'); ?>" />
                                <?php } else { ?>
                                    <span class="label label-default"><?php echo __('File'); ?></span>
                                <?php } ?>
                            </td>
                            <td class="deal-doc-action-group">
                                <?php if($docFile!==''){ ?>
                                    <a class="btn btn-info btn-xs" target="_blank" href="<?php echo h($docUrl); ?>"><span class="fa fa-eye"></span>&nbsp;<?php echo __('View'); ?></a>
                                    <button type="button" class="btn btn-success btn-xs wv-whatsapp-btn js-wv-whatsapp-send"
                                        data-wv-wa-phone="<?php echo h($clientWhatsAppPhone); ?>"
                                        data-wv-wa-message="<?php echo h($waMessage); ?>"
                                        data-wv-wa-attachment="<?php echo h($docFullUrl); ?>"
                                        title="<?php echo h(__('Send this document on WhatsApp')); ?>">
                                        <span class="fa fa-whatsapp"></span>&nbsp;<?php echo __('WhatsApp'); ?>
                                    </button>
                                <?php } ?>
                                <a class="btn btn-danger btn-xs js-deal-document-delete" href="<?php echo $this->Html->url(array('controller'=>'deals_payments','action'=>'deletedocument',$dealId,$docId)); ?>" data-url="<?php echo $this->Html->url(array('controller'=>'deals_payments','action'=>'deletedocument',$dealId,$docId)); ?>">
                                    <span class="fa fa-trash"></span>&nbsp;<?php echo __('Delete'); ?>
                                </a>
                            </td>
                        </tr>
                    <?php }} else { ?>
                        <tr>
                            <td colspan="6" class="text-center"><?php echo __('No documents uploaded for this deal yet.'); ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="dealDocCropModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="<?php echo __('Close'); ?>"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><?php echo __('Crop Image'); ?></h4>
                </div>
                <div class="modal-body">
                    <div class="text-center">
                        <img id="dealDocCropImage" src="" alt="<?php echo __('Crop'); ?>" style="max-width:100%;max-height:62vh;" />
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Cancel'); ?></button>
                    <button type="button" class="btn btn-primary" id="dealDocCropApply"><?php echo __('Apply Crop'); ?></button>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
(function($){
    if(typeof $ === 'undefined' || !$('#deal-documents-content').length){
        return;
    }
    if(typeof initDealDocumentsUi === 'function'){
        initDealDocumentsUi($('#deal-documents-content'));
        return;
    }

    var cropState = { cropper: null, input: null, box: null, originalName: '' };
    function resetCrop(){
        if(cropState.cropper){
            cropState.cropper.destroy();
            cropState.cropper = null;
        }
        cropState.input = null;
        cropState.box = null;
        cropState.originalName = '';
        $('#dealDocCropImage').attr('src','');
    }
    function renderPreview($box,file){
        var $preview = $box.find('.deal-doc-drop-preview');
        if(!$preview.length){ return; }
        if(!file){ $preview.html(''); return; }
        var name = file.name || '<?php echo addslashes(__('Selected file')); ?>';
        if(file.type && file.type.toLowerCase().indexOf('image/')===0){
            var fr = new FileReader();
            fr.onload = function(e){
                $preview.html('<img src="'+e.target.result+'" alt="<?php echo addslashes(__('Preview')); ?>"><div><small>'+name+'</small></div>');
            };
            fr.readAsDataURL(file);
        }else{
            $preview.html('<div><span class="label label-default"><?php echo addslashes(__('File')); ?></span>&nbsp;<small>'+name+'</small></div>');
        }
    }
    function openCrop(file,$input,$box){
        if(!file || !file.type || file.type.toLowerCase().indexOf('image/')!==0 || typeof Cropper === 'undefined'){
            renderPreview($box,file);
            return;
        }
        var fr = new FileReader();
        fr.onload = function(e){
            cropState.input = $input;
            cropState.box = $box;
            cropState.originalName = file.name || 'cropped-image.png';
            $('#dealDocCropImage').attr('src',e.target.result);
            $('#dealDocCropModal').modal('show');
        };
        fr.readAsDataURL(file);
    }

    $('#dealDocCropModal').off('shown.bs.modal.dealdoc').on('shown.bs.modal.dealdoc', function(){
        var image = document.getElementById('dealDocCropImage');
        if(!image || !image.src || typeof Cropper === 'undefined'){ return; }
        if(cropState.cropper){ cropState.cropper.destroy(); cropState.cropper = null; }
        cropState.cropper = new Cropper(image,{ viewMode:1, dragMode:'move', autoCropArea:1, responsive:true, background:false });
    }).off('hidden.bs.modal.dealdoc').on('hidden.bs.modal.dealdoc', function(){
        resetCrop();
    });

    $(document).off('click.dealdocapply','#dealDocCropApply').on('click.dealdocapply','#dealDocCropApply',function(){
        if(!cropState.cropper || !cropState.input || !cropState.box){ $('#dealDocCropModal').modal('hide'); return; }
        var canvas = cropState.cropper.getCroppedCanvas({ maxWidth:1600, maxHeight:1600, fillColor:'#fff' });
        if(!canvas){ $('#dealDocCropModal').modal('hide'); return; }
        canvas.toBlob(function(blob){
            if(!blob){ $('#dealDocCropModal').modal('hide'); return; }
            var originalName = cropState.originalName || ('cropped_'+Date.now()+'.png');
            var croppedName = originalName.replace(/\.[^.]+$/, '') + '_cropped.png';
            var fileObj;
            try{
                fileObj = new File([blob], croppedName, {type:'image/png',lastModified:Date.now()});
            }catch(e){
                fileObj = blob; fileObj.name = croppedName;
            }
            if(typeof DataTransfer !== 'undefined'){
                var dt = new DataTransfer();
                dt.items.add(fileObj);
                cropState.input[0].files = dt.files;
            }
            renderPreview(cropState.box,fileObj);
            $('#dealDocCropModal').modal('hide');
        },'image/png',0.95);
    });

    $('#deal-documents-content .js-doc-upload-toggle').off('click.dealdoc').on('click.dealdoc',function(e){
        e.preventDefault();
        var modalSelector = $(this).data('target') || '#dealDocumentUploadModal';
        $(modalSelector).modal('show');
    });
    $('#deal-documents-content .js-doc-date-picker').each(function(){
        if($(this).attr('type') !== 'date' && $.fn.datepicker){
            try{ $(this).datepicker('destroy'); }catch(e){}
            $(this).datepicker({format:'dd-M-yyyy',autoclose:true,todayHighlight:true});
        }
    });
    $('#deal-documents-content .js-doc-upload-submit').off('click.dealdoc').on('click.dealdoc', function(e){
        e.preventDefault();
        e.stopPropagation();
        var $btn = $(this);
        var $panel = $btn.closest('#deal-doc-upload-panel');
        var uploadUrl = $panel.data('upload-url') || window.location.href;
        var $type = $panel.find('[name="data[DealDocument][document_type]"]').first();
        var $date = $panel.find('[name="data[DealDocument][document_date]"]').first();
        var $fileInput = $panel.find('input[name="data[DealDocument][upload_file]"]').first();
        var file = ($fileInput.length && $fileInput[0].files && $fileInput[0].files.length) ? $fileInput[0].files[0] : null;
        if(!file){
            alert('<?php echo addslashes(__('Please select a file to upload.')); ?>');
            return false;
        }
        var fd = new FormData();
        fd.append('data[DealDocument][document_type]', ($type.val() || 'Others'));
        fd.append('data[DealDocument][document_date]', ($date.val() || ''));
        fd.append('data[DealDocument][upload_file]', file);
        var oldHtml = $btn.html();
        $btn.prop('disabled', true).html('<span class="fa fa-spinner fa-spin"></span>&nbsp;<?php echo addslashes(__('Uploading...')); ?>');
        $.ajax({
            url: uploadUrl,
            type: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            cache: false,
            success: function(resp){
                $('#dealDocumentUploadModal').modal('hide');
                $('body').removeClass('modal-open');
                $('.modal-backdrop').remove();
                var parsed = $('<div>').html(resp);
                var $wrap = parsed.find('#deal-documents-content');
                if($wrap.length){
                    if(typeof initDealDocumentsUi === 'function'){
                        $('#deal-documents-content').html($wrap.html());
                        initDealDocumentsUi($('#deal-documents-content'));
                    }else{
                        window.location.reload();
                    }
                    return;
                }
                if(resp && typeof resp === 'string'){
                    if(typeof initDealDocumentsUi === 'function'){
                        $('#deal-documents-content').html(resp);
                        initDealDocumentsUi($('#deal-documents-content'));
                    }else{
                        window.location.reload();
                    }
                    return;
                }
                window.location.reload();
            },
            error: function(){
                alert('<?php echo addslashes(__('Unable to upload document. Please try again.')); ?>');
            },
            complete: function(){
                $btn.prop('disabled', false).html(oldHtml);
            }
        });
        return false;
    });
    $('#deal-documents-content .deal-doc-drop-upload').each(function(){
        var $box = $(this);
        var inputId = $box.data('input-id');
        var $input = $('#'+inputId);
        if(!$input.length){ return; }
        var openPicker = function(){
            var nativeInput = $input.get(0);
            if(nativeInput && typeof nativeInput.click === 'function'){
                nativeInput.click();
            }else{
                $input.trigger('click');
            }
        };
        $box.off('dragenter.dealdoc dragover.dealdoc dragleave.dealdoc drop.dealdoc click.dealdoc');
        $box.on('dragenter.dealdoc dragover.dealdoc',function(e){ e.preventDefault(); e.stopPropagation(); $box.addClass('drag-over'); });
        $box.on('dragleave.dealdoc',function(e){ e.preventDefault(); e.stopPropagation(); $box.removeClass('drag-over'); });
        $box.on('click.dealdoc',function(e){
            if($(e.target).closest('button, label, a, .js-doc-browse').length){ return; }
            e.preventDefault(); openPicker();
        });
        $box.on('drop.dealdoc',function(e){
            e.preventDefault(); e.stopPropagation(); $box.removeClass('drag-over');
            var files = e.originalEvent && e.originalEvent.dataTransfer ? e.originalEvent.dataTransfer.files : null;
            if(!files || !files.length){ return; }
            if(typeof DataTransfer !== 'undefined'){
                var dt = new DataTransfer(); dt.items.add(files[0]); $input[0].files = dt.files; openCrop(dt.files[0],$input,$box);
            }else{ openCrop(files[0],$input,$box); }
        });
        $box.find('.js-doc-browse').off('click.dealdoc').on('click.dealdoc',function(e){ e.stopPropagation(); });
        $box.find('.js-doc-clear').off('click.dealdoc').on('click.dealdoc',function(e){ e.preventDefault(); e.stopPropagation(); $input.val(''); renderPreview($box,null); });
        $input.off('change.dealdoc').on('change.dealdoc',function(){ var file=this.files&&this.files.length?this.files[0]:null; openCrop(file,$input,$box); });
    });
    $('#deal-documents-content .js-deal-document-delete').off('click.dealdoc').on('click.dealdoc',function(e){
        if(!confirm('<?php echo addslashes(__('Are you sure you want to delete this document?')); ?>')){ e.preventDefault(); }
    });
})(jQuery);
</script>
