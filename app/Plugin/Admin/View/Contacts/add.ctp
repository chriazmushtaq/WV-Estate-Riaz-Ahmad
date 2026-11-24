<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css">
<script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js"></script>
<style type="text/css">
.ard-drop-upload{
    position:relative;
    border:2px dashed #cdd7e6;
    border-radius:8px;
    background:#f9fbff;
    padding:10px;
    min-height:128px;
    cursor:pointer;
    transition:all .2s ease;
}
.ard-drop-upload:hover,
.ard-drop-upload.drag-over{
    border-color:#2c90d9;
    background:#eef6ff;
}
.ard-drop-upload .ard-drop-message{
    font-size:12px;
    color:#6c7a92;
    text-align:center;
    margin-top:14px;
}
.ard-drop-upload .ard-drop-actions{
    text-align:center;
    margin-top:8px;
}
.ard-drop-upload .ard-drop-preview{
    text-align:center;
    min-height:74px;
}
.ard-drop-upload .ard-drop-preview img{
    max-width:100%;
    max-height:120px;
    border-radius:6px;
}
.ard-drop-upload.has-image .ard-drop-message{
    margin-top:0;
}
.ard-drop-upload input[type="file"]{
    position:absolute;
    left:-9999px;
    top:-9999px;
}
.modal,
#contactCropModal{
    display:none;
}
.modal.in{
    display:block !important;
}
#contactCropModal .modal-dialog{
    width:760px;
    max-width:95%;
}
#contactCropWrap{
    width:100%;
    max-height:460px;
    overflow:hidden;
    background:#f4f7fb;
    text-align:center;
}
#contactCropImage{
    max-width:100%;
    display:none;
    margin:0 auto;
}
.wv-contact-add-page{
    background:#f3f6fa;
    margin:-10px -10px 0;
    padding:18px;
}
.wv-contact-add-hero{
    align-items:center;
    background:linear-gradient(135deg,#07152a 0%,#102746 72%,#ff0a46 165%);
    border-radius:12px;
    box-shadow:0 18px 36px rgba(15,23,42,.16);
    color:#fff;
    display:flex;
    gap:16px;
    justify-content:space-between;
    margin-bottom:16px;
    overflow:hidden;
    padding:22px;
    position:relative;
}
.wv-contact-add-hero:after{
    background:radial-gradient(circle at 82% 0%,rgba(124,249,255,.22),rgba(124,249,255,0) 36%);
    content:"";
    inset:0;
    position:absolute;
}
.wv-contact-add-hero > *{
    position:relative;
    z-index:2;
}
.wv-contact-add-title{
    align-items:center;
    display:flex;
    gap:14px;
}
.wv-contact-add-icon{
    align-items:center;
    background:rgba(255,255,255,.13);
    border:1px solid rgba(255,255,255,.22);
    border-radius:12px;
    color:#7cf9ff;
    display:flex;
    font-size:24px;
    height:54px;
    justify-content:center;
    width:54px;
}
.wv-contact-add-title h1{
    color:#fff;
    font-size:28px;
    font-weight:900;
    line-height:1.15;
    margin:0 0 5px;
}
.wv-contact-add-title p{
    color:#d8e3f2;
    font-size:13px;
    font-weight:700;
    line-height:1.45;
    margin:0;
}
.wv-contact-add-card{
    background:#fff;
    border:1px solid #dde7f3;
    border-radius:12px;
    box-shadow:0 12px 28px rgba(15,23,42,.08);
    overflow:hidden;
}
.wv-contact-card-head{
    align-items:center;
    border-bottom:1px solid #edf2f7;
    display:flex;
    gap:12px;
    justify-content:space-between;
    padding:16px 18px;
}
.wv-contact-card-head strong{
    color:#07152a;
    display:block;
    font-size:16px;
    font-weight:900;
}
.wv-contact-card-head span{
    color:#64748b;
    display:block;
    font-size:12px;
    font-weight:700;
    margin-top:3px;
}
.wv-contact-add-body{
    padding:18px;
}
.wv-contact-add-grid{
    display:grid;
    grid-template-columns:repeat(3,minmax(0,1fr));
    gap:14px;
}
.wv-contact-add-uploads{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(140px,1fr));
    gap:14px;
}
.wv-contact-add-field{
    min-width:0;
}
.wv-contact-add-field label{
    color:#334155;
    display:block;
    font-size:11px;
    font-weight:900;
    letter-spacing:.04em;
    line-height:1.25;
    margin:0 0 7px;
    text-align:left;
    text-transform:uppercase;
}
.wv-contact-add-field .form-control,
.wv-contact-add-field select,
.wv-contact-add-field textarea{
    background:#fbfdff;
    border:1px solid #cbd8e8;
    border-radius:8px;
    box-shadow:none;
    color:#0f172a;
    font-weight:700;
    height:42px;
    max-width:none!important;
    min-width:0;
    width:100%!important;
}
.wv-contact-add-field textarea.form-control{
    height:auto;
    min-height:118px;
    resize:vertical;
}
.wv-contact-add-field .form-control:focus,
.wv-contact-add-field select:focus,
.wv-contact-add-field textarea:focus{
    background:#fff;
    border-color:#ff0a46;
    box-shadow:0 0 0 3px rgba(255,10,70,.10);
}
.wv-contact-add-full{
    grid-column:span 3;
}
.wv-contact-add-uploads{
    border-top:1px solid #edf2f7;
    margin-top:18px;
    padding-top:18px;
}
.wv-contact-add-uploads .ard-drop-upload{
    background:#f8fbff;
    border-radius:10px;
    min-height:164px;
}
.wv-contact-add-actions{
    align-items:center;
    background:#f8fafc;
    border-top:1px solid #edf2f7;
    display:flex;
    flex-wrap:wrap;
    gap:8px;
    justify-content:flex-start;
    margin:18px -18px -18px;
    padding:16px 18px;
}
.wv-contact-add-actions .btn{
    border:0;
    border-radius:8px;
    font-size:12px;
    font-weight:900;
    padding:10px 15px;
}
.wv-contact-add-actions .btn-success{
    background:#047857!important;
    color:#fff!important;
}
.wv-contact-add-actions .btn-danger{
    background:#07152a!important;
    color:#fff!important;
}
@media(max-width:991px){
    .wv-contact-add-page{margin:0;padding:12px;}
    .wv-contact-add-hero,.wv-contact-card-head{display:block;}
    .wv-contact-add-grid,
    .wv-contact-add-uploads{grid-template-columns:repeat(2,minmax(0,1fr));}
    .wv-contact-add-full{grid-column:span 2;}
}
@media(max-width:640px){
    .wv-contact-add-grid,
    .wv-contact-add-uploads{grid-template-columns:1fr;}
    .wv-contact-add-full{grid-column:span 1;}
    .wv-contact-add-title h1{font-size:23px;}
}
</style>

<script type="text/javascript">
function formatContactCnic(value){
    var digits = (value || '').replace(/\D/g,'');
    if(digits.length > 13){
        digits = digits.substring(0,13);
    }
    if(digits.length <= 5){
        return digits;
    }
    if(digits.length <= 12){
        return digits.substring(0,5) + '-' + digits.substring(5);
    }
    return digits.substring(0,5) + '-' + digits.substring(5,12) + '-' + digits.substring(12,13);
}

function bindDropUpload(scope){
    scope.find('.ard-drop-upload').each(function(){
        var $box = $(this);
        var inputId = $box.attr('data-input-id');
        var $input = $('#' + inputId);
        var $preview = $box.find('.ard-drop-preview');

        var showPreview = function(file){
            if(!file || !file.type || file.type.indexOf('image/') !== 0){
                return;
            }
            var reader = new FileReader();
            reader.onload = function(e){
                $preview.html('<img src="' + e.target.result + '" alt="Preview">');
                $box.addClass('has-image');
            };
            reader.readAsDataURL(file);
        };

        if(!$input.length){
            return;
        }

        $box.off('click.ardDrop').on('click.ardDrop', function(e){
            if($(e.target).hasClass('js-ard-drop-browse') || $(e.target).closest('.js-ard-drop-browse').length){
                return;
            }
            $input[0].click();
        });
        $box.find('.js-ard-drop-browse').off('click.ardDrop').on('click.ardDrop', function(e){
            e.preventDefault();
            e.stopPropagation();
            $input[0].click();
        });

        $input.off('change.ardDrop').on('change.ardDrop', function(){
            if(this.files && this.files.length){
                openContactCropper(this.files[0], $input, $box, $preview, showPreview);
            }
        });

        $box.off('dragenter.ardDrop dragover.ardDrop').on('dragenter.ardDrop dragover.ardDrop', function(e){
            e.preventDefault();
            e.stopPropagation();
            $box.addClass('drag-over');
        });
        $box.off('dragleave.ardDrop').on('dragleave.ardDrop', function(e){
            e.preventDefault();
            e.stopPropagation();
            $box.removeClass('drag-over');
        });
        $box.off('drop.ardDrop').on('drop.ardDrop', function(e){
            e.preventDefault();
            e.stopPropagation();
            $box.removeClass('drag-over');
            var dt = e.originalEvent.dataTransfer;
            if(dt && dt.files && dt.files.length){
                try{
                    $input[0].files = dt.files;
                    $input.trigger('change');
                }catch(err){
                    openContactCropper(dt.files[0], $input, $box, $preview, showPreview);
                }
            }
        });
    });
}

var contactCropState = {
    cropper: null,
    objectUrl: null,
    input: null,
    box: null,
    preview: null,
    fallbackPreview: null,
    fileName: 'image.jpg',
    fileType: 'image/jpeg'
};

function openContactCropper(file, $input, $box, $preview, fallbackPreview){
    if(!file || !file.type || file.type.indexOf('image/') !== 0){
        return;
    }
    if(typeof Cropper === 'undefined'){
        fallbackPreview(file);
        return;
    }
    if(contactCropState.objectUrl){
        URL.revokeObjectURL(contactCropState.objectUrl);
        contactCropState.objectUrl = null;
    }
    contactCropState.input = $input;
    contactCropState.box = $box;
    contactCropState.preview = $preview;
    contactCropState.fallbackPreview = fallbackPreview;
    contactCropState.fileName = file.name || 'image.jpg';
    contactCropState.fileType = file.type || 'image/jpeg';
    contactCropState.objectUrl = URL.createObjectURL(file);
    $('#contactCropImage').attr('src', contactCropState.objectUrl);
    $('#contactCropModal').modal('show');
}

function initContactCropperModal(){
    $('#contactCropModal').off('shown.bs.modal.contactCrop').on('shown.bs.modal.contactCrop', function(){
        if(typeof Cropper === 'undefined'){
            return;
        }
        var image = document.getElementById('contactCropImage');
        if(contactCropState.cropper){
            contactCropState.cropper.destroy();
            contactCropState.cropper = null;
        }
        contactCropState.cropper = new Cropper(image, {
            viewMode: 1,
            autoCropArea: 1,
            responsive: true,
            background: false
        });
    });

    $('#contactCropModal').off('hidden.bs.modal.contactCrop').on('hidden.bs.modal.contactCrop', function(){
        if(contactCropState.cropper){
            contactCropState.cropper.destroy();
            contactCropState.cropper = null;
        }
        if(contactCropState.objectUrl){
            URL.revokeObjectURL(contactCropState.objectUrl);
            contactCropState.objectUrl = null;
        }
    });

    $('#contactCropApply').off('click.contactCrop').on('click.contactCrop', function(){
        if(!contactCropState.cropper || !contactCropState.input || !contactCropState.preview){
            $('#contactCropModal').modal('hide');
            return;
        }
        var mime = 'image/jpeg';
        var safeName = (contactCropState.fileName || 'image').replace(/\.[^/.]+$/, '') + '.jpg';
        var canvas = contactCropState.cropper.getCroppedCanvas({
            maxWidth: 1600,
            maxHeight: 1600,
            imageSmoothingEnabled: true,
            imageSmoothingQuality: 'high'
        });
        if(!canvas){
            $('#contactCropModal').modal('hide');
            return;
        }
        canvas.toBlob(function(blob){
            if(!blob){
                $('#contactCropModal').modal('hide');
                return;
            }
            var croppedFile = new File([blob], safeName, {type: mime, lastModified: Date.now()});
            try{
                var dt = new DataTransfer();
                dt.items.add(croppedFile);
                contactCropState.input[0].files = dt.files;
            }catch(err){}
            contactCropState.preview.html('<img src="' + canvas.toDataURL(mime, 0.92) + '" alt="Preview">');
            contactCropState.box.addClass('has-image');
            $('#contactCropModal').modal('hide');
        }, mime, 0.92);
    });
}

$(document).ready(function(){
    $('#pincode').on('input blur', function(){
        $(this).val(formatContactCnic($(this).val()));
    });
    bindDropUpload($(document));
    initContactCropperModal();
});
</script>

<div class="wv-contact-add-page">
    <?php echo $this->Session->flash();?>
    <div class="wv-contact-add-hero">
        <div class="wv-contact-add-title">
            <div class="wv-contact-add-icon"><i class="fa fa-user-plus"></i></div>
            <div>
                <h1><?php echo __('Add Contact');?></h1>
                <p><?php echo __('Create a customer profile with communication, address, and identity details.');?></p>
            </div>
        </div>
    </div>

    <div class="wv-contact-add-card">
        <div class="wv-contact-card-head">
            <div>
                <strong><?php echo __('Contact Details');?></strong>
                <span><?php echo __('Customer profile, identity, and communication record.');?></span>
            </div>
        </div>
        <?php echo $this->Form->create('Contact', array('controller' => 'Contacts', 'action' => 'add', 'name' => 'post_req', 'id' => 'post_req', 'class' => 'form-horizontal', 'type' => 'file'));?>
        <div class="wv-contact-add-body">
            <div class="wv-contact-add-grid">
                <?php if($userType!='AGT' && $userType!='AGY'){?>
                <div class="wv-contact-add-field">
                    <label><?php echo __('Agency');?></label>
                    <?php echo $this->Form->select('project_id', $agency, array('label' => false, 'class' => 'form-control', 'empty' => __('No Agency'), 'div' => false));?>
                </div>
                <?php }?>

                <div class="wv-contact-add-field">
                    <label><?php echo __('Category');?></label>
                    <?php echo $this->Form->select('category_id', $category, array('label' => false, 'class' => 'form-control', 'empty' => __('Select'), 'div' => false));?>
                </div>

                <div class="wv-contact-add-field">
                    <label><?php echo __('Name');?></label>
                    <?php echo $this->Form->input('name', array('label' => false, 'class' => 'form-control', 'placeholder' => __('Name'), 'div' => false));?>
                </div>

                <div class="wv-contact-add-field">
                    <label><?php echo __('Relationship Type');?></label>
                    <?php
                    echo $this->Form->input('relation_type', array(
                        'label' => false,
                        'class' => 'form-control',
                        'div' => false,
                        'type' => 'select',
                        'options' => array(
                            'Father' => __('Father (S/O, D/O)'),
                            'Husband' => __('Husband (W/O)'),
                            'Wife' => __('Wife'),
                            'Mother' => __('Mother'),
                            'Guardian' => __('Guardian'),
                            'Son' => __('Son'),
                            'Daughter' => __('Daughter'),
                            'Other' => __('Other')
                        ),
                        'default' => 'Father',
                        'empty' => __('Select Relationship')
                    ));
                    ?>
                </div>

                <div class="wv-contact-add-field">
                    <label><?php echo __('Father / Guardian Name');?></label>
                    <?php echo $this->Form->input('fathername', array('label' => false, 'class' => 'form-control', 'placeholder' => __('Father / Guardian / Spouse Name'), 'div' => false));?>
                </div>

                <div class="wv-contact-add-field">
                    <label><?php echo __('Gender');?></label>
                    <?php
                    echo $this->Form->input('gender', array(
                        'label' => false,
                        'class' => 'form-control',
                        'div' => false,
                        'type' => 'select',
                        'options' => array('Male' => 'Male', 'Female' => 'Female', 'Other' => 'Other'),
                        'empty' => __('Select Gender')
                    ));
                    ?>
                </div>

                <div class="wv-contact-add-field">
                    <label><?php echo __('Cell Phone');?></label>
                    <?php echo $this->Form->input('mobile', array('label' => false, 'class' => 'form-control', 'placeholder' => __('+923012365897'), 'div' => false));?>
                </div>

                <div class="wv-contact-add-field">
                    <label><?php echo __('Mobile SMS');?></label>
                    <?php echo $this->Form->input('phone', array('label' => false, 'class' => 'form-control', 'placeholder' => __('+923001234567'), 'div' => false));?>
                </div>

                <div class="wv-contact-add-field">
                    <label><?php echo __('Email');?></label>
                    <?php echo $this->Form->input('email', array('label' => false, 'class' => 'form-control', 'placeholder' => __('Email'), 'div' => false));?>
                </div>

                <div class="wv-contact-add-field">
                    <label><?php echo __('CNIC or ZIP Code');?></label>
                    <?php echo $this->Form->input('pincode', array(
                        'label' => false,
                        'class' => 'form-control',
                        'placeholder' => 'e.g. 35301-1234567-1 or 54000',
                        'div' => false,
                        'id' => 'pincode',
                        'maxlength' => 15,
                        'required' => true
                    )); ?>
                </div>

                <div class="wv-contact-add-field wv-contact-add-full">
                    <?php echo $this->element('address_hierarchy_fields', array('fieldPrefix'=>'', 'current'=>isset($this->request->data['Contact']) ? $this->request->data['Contact'] : array(), 'wrapperId'=>'ContactAddAddressHierarchy'));?>
                </div>

                <div class="wv-contact-add-field wv-contact-add-full">
                    <label><?php echo __('Street / House Address');?></label>
                    <?php echo $this->Form->input('address', array('label' => false, 'class' => 'form-control', 'placeholder' => __('House #, Street #, Sector / Area details...'), 'div' => false));?>
                </div>
            </div>

            <div class="wv-contact-add-uploads">
                <div class="wv-contact-add-field">
                    <label><?php echo __('Profile Photo');?></label>
                    <div class="ard-drop-upload" data-input-id="ContactPhoto">
                        <div class="ard-drop-preview"></div>
                        <div class="ard-drop-message"><?php echo __('Drag & drop image here');?></div>
                        <div class="ard-drop-actions"><button type="button" class="btn btn-default btn-xs js-ard-drop-browse"><?php echo __('Browse');?></button></div>
                        <?php echo $this->Form->input('photo', array('type' => 'file', 'label' => false, 'div' => false, 'accept' => 'image/*', 'id' => 'ContactPhoto'));?>
                    </div>
                </div>

                <div class="wv-contact-add-field">
                    <label><?php echo __('ID Front (CNIC Front)');?></label>
                    <div class="ard-drop-upload" data-input-id="ContactIdFront">
                        <div class="ard-drop-preview"></div>
                        <div class="ard-drop-message"><?php echo __('Drag & drop image here');?></div>
                        <div class="ard-drop-actions"><button type="button" class="btn btn-default btn-xs js-ard-drop-browse"><?php echo __('Browse');?></button></div>
                        <?php echo $this->Form->input('id_front', array('type' => 'file', 'label' => false, 'div' => false, 'accept' => 'image/*', 'id' => 'ContactIdFront'));?>
                    </div>
                </div>

                <div class="wv-contact-add-field">
                    <label><?php echo __('ID Back (CNIC Back)');?></label>
                    <div class="ard-drop-upload" data-input-id="ContactIdBack">
                        <div class="ard-drop-preview"></div>
                        <div class="ard-drop-message"><?php echo __('Drag & drop image here');?></div>
                        <div class="ard-drop-actions"><button type="button" class="btn btn-default btn-xs js-ard-drop-browse"><?php echo __('Browse');?></button></div>
                        <?php echo $this->Form->input('id_back', array('type' => 'file', 'label' => false, 'div' => false, 'accept' => 'image/*', 'id' => 'ContactIdBack'));?>
                    </div>
                </div>

                <div class="wv-contact-add-field">
                    <label><?php echo __('Gallery / Side Photo');?></label>
                    <div class="ard-drop-upload" data-input-id="ContactSidePhoto">
                        <div class="ard-drop-preview"></div>
                        <div class="ard-drop-message"><?php echo __('Drag & drop image here');?></div>
                        <div class="ard-drop-actions"><button type="button" class="btn btn-default btn-xs js-ard-drop-browse"><?php echo __('Browse');?></button></div>
                        <?php echo $this->Form->input('side_photo', array('type' => 'file', 'label' => false, 'div' => false, 'accept' => 'image/*', 'id' => 'ContactSidePhoto'));?>
                    </div>
                </div>
            </div>

            <div class="wv-contact-add-actions">
                <button type="submit" class="btn btn-success"><span class="fa fa-plus-circle"></span>&nbsp;<?php echo __('Save');?></button>
                <?php echo $this->Html->link('<span class="fa fa-close"></span>&nbsp;'.__('Close'), array('action' => 'index'), array('class' => 'btn btn-danger', 'escape' => false));?>
            </div>

        <div class="modal fade" id="contactCropModal" tabindex="-1" role="dialog" aria-hidden="true" style="display:none;">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title"><?php echo __('Crop Image');?></h4>
                    </div>
                    <div class="modal-body">
                        <div id="contactCropWrap">
                            <img id="contactCropImage" src="" alt="Crop Image">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Cancel');?></button>
                        <button type="button" class="btn btn-success" id="contactCropApply"><?php echo __('Apply Crop');?></button>
                    </div>
                </div>
            </div>
        </div>
        <?php echo $this->Form->end();?>
    </div>
</div>
</div>
