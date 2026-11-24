<script type="text/javascript">
    var ardDefaultLedgerMap = <?php echo json_encode((array)(isset($ardDefaultMap) ? $ardDefaultMap : array()));?>;
    var rowLedgerManualSelect = {};
    function applyDefaultReceiptAccountByRow(rowNo,forceApply)
    {
        var $paymentType = $('.js-payment-type-select[data-row="'+rowNo+'"]');
        var $ledger = $('.js-ard-ledger-select[data-row="'+rowNo+'"]');
        if($paymentType.length===0 || $ledger.length===0)
        {
            return;
        }
        var paymentTypeId = parseInt($paymentType.val(),10);
        var mappedLedgerId = 0;
        if(!isNaN(paymentTypeId) && typeof ardDefaultLedgerMap[paymentTypeId] !== 'undefined')
        {
            mappedLedgerId = parseInt(ardDefaultLedgerMap[paymentTypeId],10);
            if(isNaN(mappedLedgerId))
            {
                mappedLedgerId = 0;
            }
        }
        if(forceApply || !rowLedgerManualSelect[rowNo] || !$ledger.val())
        {
            if(mappedLedgerId>0)
            {
                $ledger.val(mappedLedgerId.toString());
            }
            else if(forceApply || !rowLedgerManualSelect[rowNo])
            {
                $ledger.val('');
            }
        }
    }
    $(document).ready(function(){
        $('.paymentDate').datetimepicker({format: '<?php echo$dpFormat;?>'});
        $('.js-ard-ledger-select').each(function(){
            var rowNo = $(this).attr('data-row');
            if($(this).val())
            {
                rowLedgerManualSelect[rowNo] = true;
            }
        });
        $(document).on('change','.js-ard-ledger-select',function(){
            var rowNo = $(this).attr('data-row');
            rowLedgerManualSelect[rowNo] = true;
        });
        $(document).on('change','.js-payment-type-select',function(){
            var rowNo = $(this).attr('data-row');
            applyDefaultReceiptAccountByRow(rowNo,false);
        });
        $('.js-payment-type-select').each(function(){
            var rowNo = $(this).attr('data-row');
            applyDefaultReceiptAccountByRow(rowNo,false);
        });
        });
</script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css">
<script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js"></script>
<style type="text/css">
.payment-slip-upload-box{
    width:400px;
    max-width:100%;
    min-height:140px;
    border:1px dashed #9cb6cf;
    border-radius:6px;
    background:#f8fbff;
    padding:14px 12px 10px;
    text-align:center;
    cursor:pointer;
}
.payment-slip-upload-box.drag-over{
    border-color:#4f7da7;
    background:#eef6ff;
}
.payment-slip-upload-box .help-text{
    color:#274b6f;
    font-size:13px;
    line-height:1.4;
    margin-bottom:10px;
}
.payment-slip-preview{
    margin-top:8px;
}
.payment-slip-preview img{
    max-width:400px;
    max-height:500px;
    width:auto;
    height:auto;
    border:1px solid #d8e0e8;
    border-radius:4px;
}
#paymentSlipCropModal .modal-dialog{
    width:760px;
    max-width:95%;
}
#paymentSlipCropModal .crop-wrap{
    width:100%;
    max-height:520px;
    overflow:hidden;
    background:#111;
    text-align:center;
}
#paymentSlipCropModal .crop-wrap img{
    max-width:100%;
}
</style>
<script type="text/javascript">
var paymentSlipCropState = {
    cropper: null,
    input: null,
    box: null,
    preview: null,
    originalName: ''
};

function resetPaymentSlipCropState()
{
    if(paymentSlipCropState.cropper)
    {
        paymentSlipCropState.cropper.destroy();
        paymentSlipCropState.cropper = null;
    }
    paymentSlipCropState.input = null;
    paymentSlipCropState.box = null;
    paymentSlipCropState.preview = null;
    paymentSlipCropState.originalName = '';
    var cropImage = document.getElementById('paymentSlipCropImage');
    if(cropImage)
    {
        cropImage.src = '';
    }
}

function renderPaymentSlipPreview($preview, file, existingUrl, existingName)
{
    if(!$preview || !$preview.length)
    {
        return;
    }
    if(existingUrl)
    {
        $preview.html('<a href="'+existingUrl+'" target="_blank"><img src="'+existingUrl+'" alt="Payment Slip Preview"></a>');
        return;
    }
    if(!file)
    {
        if(existingName)
        {
            $preview.html('<div class="alert alert-info" style="margin-bottom:0;padding:6px 10px;"><strong>'+existingName+'</strong></div>');
        }
        else
        {
            $preview.html('');
        }
        return;
    }
    var name = file.name ? file.name : 'selected-file';
    var type = file.type ? file.type : '';
    var lower = name.toLowerCase();
    var isImage = /^image\//i.test(type) || /\.(jpg|jpeg|png|gif|webp)$/i.test(lower);
    if(isImage)
    {
        var reader = new FileReader();
        reader.onload = function(e)
        {
            $preview.html('<img src="'+e.target.result+'" alt="Payment Slip Preview">');
        };
        reader.readAsDataURL(file);
    }
    else
    {
        $preview.html('<div class="alert alert-info" style="margin-bottom:0;padding:6px 10px;"><strong>'+name+'</strong></div>');
    }
}

function openPaymentSlipCropper(file, $input, $box, $preview)
{
    if(typeof Cropper === 'undefined')
    {
        renderPaymentSlipPreview($preview, file, '', '');
        return;
    }
    var cropImage = document.getElementById('paymentSlipCropImage');
    if(!cropImage)
    {
        renderPaymentSlipPreview($preview, file, '', '');
        return;
    }
    paymentSlipCropState.input = $input;
    paymentSlipCropState.box = $box;
    paymentSlipCropState.preview = $preview;
    paymentSlipCropState.originalName = file.name || ('payment_slip_'+(new Date().getTime())+'.png');
    var reader = new FileReader();
    reader.onload = function(e)
    {
        cropImage.src = e.target.result;
        $('#paymentSlipCropModal').modal('show');
    };
    reader.readAsDataURL(file);
}

function bindPaymentSlipUpload(scope)
{
    scope.find('.js-payment-slip-box').each(function(){
        var $box = $(this);
        if($box.data('bound'))
        {
            return;
        }
        $box.data('bound', 1);
        var inputSelector = $box.attr('data-target-input');
        var previewSelector = $box.attr('data-preview-target');
        var $input = $(inputSelector);
        var $preview = $(previewSelector);
        var $browse = $box.find('.js-slip-browse');
        var $clear = $box.find('.js-slip-clear');

        function clearFile()
        {
            $input.val('');
            renderPaymentSlipPreview($preview, null, '', '');
        }

        function setInputFile(file)
        {
            if(!file || !$input.length)
            {
                return;
            }
            try
            {
                var dt = new DataTransfer();
                dt.items.add(file);
                $input[0].files = dt.files;
            }
            catch(err)
            {
                // Ignore DataTransfer fallback issues.
            }
        }

        function handleFile(file)
        {
            if(!file)
            {
                return;
            }
            var name = file.name ? file.name.toLowerCase() : '';
            var isImage = /^image\//i.test(file.type || '') || /\.(jpg|jpeg|png|gif|webp)$/.test(name);
            if(isImage)
            {
                openPaymentSlipCropper(file, $input, $box, $preview);
            }
            else
            {
                setInputFile(file);
                renderPaymentSlipPreview($preview, file, '', '');
            }
        }

        $box.off('click.paymentslip').on('click.paymentslip', function(e){
            if($(e.target).closest('button').length)
            {
                return;
            }
            $input.trigger('click');
        });
        $browse.off('click.paymentslip').on('click.paymentslip', function(e){
            e.preventDefault();
            e.stopPropagation();
            $input.trigger('click');
        });
        $clear.off('click.paymentslip').on('click.paymentslip', function(e){
            e.preventDefault();
            e.stopPropagation();
            clearFile();
        });
        $input.off('change.paymentslip').on('change.paymentslip', function(){
            if(this.files && this.files.length)
            {
                handleFile(this.files[0]);
            }
        });
        $box.off('dragenter.paymentslip dragover.paymentslip').on('dragenter.paymentslip dragover.paymentslip', function(e){
            e.preventDefault();
            e.stopPropagation();
            $box.addClass('drag-over');
        });
        $box.off('dragleave.paymentslip').on('dragleave.paymentslip', function(e){
            e.preventDefault();
            e.stopPropagation();
            $box.removeClass('drag-over');
        });
        $box.off('drop.paymentslip').on('drop.paymentslip', function(e){
            e.preventDefault();
            e.stopPropagation();
            $box.removeClass('drag-over');
            var dt = e.originalEvent && e.originalEvent.dataTransfer ? e.originalEvent.dataTransfer : null;
            if(!dt || !dt.files || !dt.files.length)
            {
                return;
            }
            handleFile(dt.files[0]);
        });
    });
}

function initPaymentSlipCropModal()
{
    $(document).off('shown.bs.modal.paymentslipcrop', '#paymentSlipCropModal').on('shown.bs.modal.paymentslipcrop', '#paymentSlipCropModal', function(){
        var cropImage = document.getElementById('paymentSlipCropImage');
        if(!cropImage || !cropImage.src)
        {
            return;
        }
        if(paymentSlipCropState.cropper)
        {
            paymentSlipCropState.cropper.destroy();
            paymentSlipCropState.cropper = null;
        }
        paymentSlipCropState.cropper = new Cropper(cropImage, {
            viewMode: 1,
            autoCropArea: 1,
            dragMode: 'move',
            responsive: true,
            background: false
        });
    });

    $(document).off('hidden.bs.modal.paymentslipcrop', '#paymentSlipCropModal').on('hidden.bs.modal.paymentslipcrop', '#paymentSlipCropModal', function(){
        resetPaymentSlipCropState();
    });

    $(document).off('click.paymentslipcropapply', '#paymentSlipCropApply').on('click.paymentslipcropapply', '#paymentSlipCropApply', function(){
        if(!paymentSlipCropState.cropper || !paymentSlipCropState.input || !paymentSlipCropState.preview)
        {
            $('#paymentSlipCropModal').modal('hide');
            return;
        }
        var canvas = paymentSlipCropState.cropper.getCroppedCanvas({
            fillColor: '#fff'
        });
        if(!canvas)
        {
            $('#paymentSlipCropModal').modal('hide');
            return;
        }
        canvas.toBlob(function(blob){
            if(!blob)
            {
                $('#paymentSlipCropModal').modal('hide');
                return;
            }
            var originalName = paymentSlipCropState.originalName || ('payment_slip_'+Date.now()+'.png');
            var extension = 'png';
            var mime = 'image/png';
            if(/\.jpe?g$/i.test(originalName))
            {
                extension = 'jpg';
                mime = 'image/jpeg';
            }
            else if(/\.webp$/i.test(originalName))
            {
                extension = 'webp';
                mime = 'image/webp';
            }
            var croppedName = originalName.replace(/\.[^.]+$/, '') + '_cropped.' + extension;
            var croppedFile;
            try
            {
                croppedFile = new File([blob], croppedName, {type: mime, lastModified: Date.now()});
            }
            catch(err)
            {
                croppedFile = blob;
                croppedFile.name = croppedName;
            }
            try
            {
                var dt = new DataTransfer();
                dt.items.add(croppedFile);
                paymentSlipCropState.input[0].files = dt.files;
            }
            catch(e){}
            renderPaymentSlipPreview(paymentSlipCropState.preview, croppedFile, '', '');
            $('#paymentSlipCropModal').modal('hide');
        }, 'image/png', 0.95);
    });
}

$(document).ready(function(){
    bindPaymentSlipUpload($(document));
    initPaymentSlipCropModal();
});
</script>
<?php
$editPaymentIds = isset($editPaymentIds) && trim((string)$editPaymentIds) !== '' ? trim((string)$editPaymentIds) : '';
if($editPaymentIds === '')
{
    $editPaymentIdList = array();
    foreach((array)$DealsPayment as $editPaymentRow)
    {
        if(!empty($editPaymentRow['DealsPayment']['id']))
        {
            $editPaymentIdList[] = (int)$editPaymentRow['DealsPayment']['id'];
        }
    }
    $editPaymentIds = implode(',', array_unique($editPaymentIdList));
}
$editPaymentCount = isset($editPaymentCount) ? (int)$editPaymentCount : count((array)$DealsPayment);
?>
<div <?php if(!$isError){?>class="container"<?php }?>>
    <div class="panel panel-custom mrg">
	<div class="panel-heading"><?php echo __('Edit');?> <?php echo __('Deals Payments');?><?php if(!$isError){?><button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button><?php }?></div>            
                <div class="panel-body"><?php echo $this->Session->flash();?>
                <?php echo $this->Form->create('DealsPayment', array( 'controller' => 'DealsPayments','action'=>"edit/$editPaymentIds",'name'=>'post_req','id'=>'post_req','class'=>'form-horizontal','type'=>'file'));?>
					<?php foreach (array_slice((array)$DealsPayment,0,1) as $k=>$post): $id=$post['DealsPayment']['id'];$form_no=$k+1;?>
                    <?php
                    $currentSlipFile = !empty($post['DealsPayment']['payment_slip']) ? trim((string)$post['DealsPayment']['payment_slip']) : '';
                    $currentSlipDir = !empty($post['DealsPayment']['payment_slip_dir']) ? trim((string)$post['DealsPayment']['payment_slip_dir']) : 'paymentslips';
                    $currentSlipMime = !empty($post['DealsPayment']['payment_slip_mime']) ? trim((string)$post['DealsPayment']['payment_slip_mime']) : '';
                    $currentSlipIsImage = !empty($post['DealsPayment']['payment_slip_is_image']) ? 1 : 0;
                    $currentSlipRelPath = '';
                    if($currentSlipFile !== '')
                    {
                        $trimmedSlipDir = trim(str_replace('\\', '/', $currentSlipDir), '/');
                        if($trimmedSlipDir === '')
                        {
                            $trimmedSlipDir = 'paymentslips';
                        }
                        $slipFilePath = WWW_ROOT . 'img' . DS . str_replace('/', DS, $trimmedSlipDir) . DS . $currentSlipFile;
                        if(file_exists($slipFilePath))
                        {
                            $currentSlipRelPath = $trimmedSlipDir . '/' . $currentSlipFile;
                        }
                    }
                    ?>
						<div class="panel panel-default">
							<div class="panel-heading"><strong><small class="text-danger"><?php echo $editPaymentCount>1 ? __('Receipt Group').' ('.$editPaymentCount.' '.__('Payments').')' : __('Payment Details');?></small></strong></div>
		  <div class="panel-body">
		<div class="form-group">
		    <label for="group_name" class="col-sm-4 control-label"><small><?php echo __('Payment Type').':';?></small></label>
		    <div class="col-sm-4">
		       <?php echo $this->Form->select("DealsPaymentBulk.paymenttype_id",$paymentType,array('empty'=>__('Please Select'),'label'=>false,'class'=>'form-control js-payment-type-select','data-row'=>'bulk','value'=>$post['DealsPayment']['paymenttype_id'])); ?>
		    </div>                                           
		</div>
        <div class="form-group">
            <label for="group_name" class="col-sm-4 control-label"><small><?php echo __('Receipt Account').':';?></small></label>
            <div class="col-sm-4">
               <?php echo $this->Form->select("DealsPaymentBulk.ard_ledger_id",isset($ardLedgerList)?$ardLedgerList:array(),array('empty'=>__('Auto by Payment Type'),'label'=>false,'class'=>'form-control js-ard-ledger-select','data-row'=>'bulk','value'=>isset($post['DealsPayment']['ard_ledger_id'])?$post['DealsPayment']['ard_ledger_id']:'')); ?>
            </div>                                           
        </div>
		<div class="form-group">
		    <label for="group_name" class="col-sm-4 control-label"><small><?php echo __('Payment Date').':';?></small></label>
		    <div class="col-sm-4">
		       <div class="input-group date paymentDate" data-date-format="<?php echo $dpFormat;?>">                        
			<?php echo $this->Form->input("DealsPaymentBulk.payment_date",array('type'=>'text','value'=>$this->Time->format($dtFormat,$post['DealsPayment']['payment_date']),'label' => false,'class'=>'form-control','placeholder'=>__('Payment Date'),'div'=>false));?>
			<span class="input-group-addon"><span class="fa fa-calendar"></span></span>
		    </div>
		    </div>                                           
		</div>
		    <div class="form-group">
			<label for="group_name" class="col-sm-4 control-label"><small><?php echo __('Transaction Reference').':';?></small></label>
			<div class="col-sm-4">
			   <?php echo $this->Form->input("DealsPaymentBulk.remarks", array('label'=>false,'placeholder'=>__('Transaction Reference'),'class'=>'form-control','value'=>$post['DealsPayment']['remarks'])); ?>
			</div>                                           
		    </div>
            <div class="form-group">
                <label for="group_name" class="col-sm-4 control-label"><small><?php echo __('Payment Slip').':';?></small></label>
                <div class="col-sm-8">
                    <div class="payment-slip-upload-box js-payment-slip-box" data-target-input="#DealsPaymentBulkPaymentSlip" data-preview-target="#DealsPaymentBulkPaymentSlipPreview">
                        <div class="help-text"><?php echo __('Drag and drop payment slip here, or use Browse. Supported: PDF, DOC, DOCX, JPG, JPEG, PNG, GIF, WEBP');?></div>
                        <button type="button" class="btn btn-primary btn-sm js-slip-browse"><?php echo __('Browse');?></button>
                        <button type="button" class="btn btn-danger btn-sm js-slip-clear"><?php echo __('Clear');?></button>
                    </div>
                    <?php echo $this->Form->input("DealsPaymentBulk.payment_slip", array('type'=>'file','label'=>false,'div'=>false,'id'=>"DealsPaymentBulkPaymentSlip",'class'=>'js-payment-slip-input','style'=>'display:none;','accept'=>'.pdf,.doc,.docx,.jpg,.jpeg,.png,.gif,.webp'));?>
                    <div id="DealsPaymentBulkPaymentSlipPreview" class="payment-slip-preview js-payment-slip-preview">
                        <?php if($currentSlipRelPath !== ''): ?>
                            <?php if($currentSlipIsImage): ?>
                                <?php echo $this->Html->link(
                                    $this->Html->image($currentSlipRelPath, array('alt' => __('Payment Slip'))),
                                    '/img/' . $currentSlipRelPath,
                                    array('escape' => false, 'target' => '_blank')
                                ); ?>
                            <?php else: ?>
                                <?php echo $this->Html->link(__('View Existing Slip'), '/img/' . $currentSlipRelPath, array('target' => '_blank', 'class' => 'btn btn-default btn-xs')); ?>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    <?php echo $this->Form->hidden("DealsPaymentBulk.payment_ids", array('value'=>$editPaymentIds)); ?>
                </div>
            </div>
		      <div class="form-group text-left">
			  <div class="col-sm-offset-4 col-sm-6">
			      <?php echo $this->Form->hidden("DealsPaymentBulk.primary_id",array('value'=>$id));?>
			  </div>
		      </div>
		  </div>
		</div>
                    <?php endforeach; ?>
                        <?php unset($post); ?>
                        <div class="form-group text-left">
                        <div class="col-sm-offset-4 col-sm-6">                            
                            <button type="submit" class="btn btn-success"><span class="fa fa-refresh"></span>&nbsp;<?php echo __('Update');?></button>
                            <?php if(!$isError){?><button type="button" class="btn btn-danger" data-dismiss="modal"><span class="fa fa-remove"></span>&nbsp;<?php echo __('Cancel');?></button><?php }?>
                        </div>
                    </div>
		    <?php echo$this->Form->input('dealId',array('type'=>'hidden','name'=>'dealId','value'=>$dealId));?>
                <?php echo$this->Form->end();?>
        </div>
    </div>
</div>
<div class="modal fade" id="paymentSlipCropModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php echo __('Crop Payment Slip');?></h4>
            </div>
            <div class="modal-body">
                <div class="crop-wrap">
                    <img id="paymentSlipCropImage" src="" alt="Crop Payment Slip">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Cancel');?></button>
                <button type="button" class="btn btn-success" id="paymentSlipCropApply"><?php echo __('Apply Crop');?></button>
            </div>
        </div>
    </div>
</div>
