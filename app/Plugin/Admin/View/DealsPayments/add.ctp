<script type="text/javascript">
    var currentInstallmentAmount = parseFloat('<?php echo (float)$currentInstallmentAmount;?>');
    var ardDefaultLedgerMap = <?php echo json_encode((array)(isset($ardDefaultMap) ? $ardDefaultMap : array()));?>;
    var isArdLedgerTouched = <?php echo (!empty($this->request->data['DealsPayment']['ard_ledger_id']) ? 'true' : 'false');?>;
    function updateCarryForwardHint()
    {
        var paidAmount=parseFloat($('#DealsPaymentEmi').val());
        if(isNaN(paidAmount))paidAmount=0;
        var diff=paidAmount-currentInstallmentAmount;
        var $hint=$('#carryForwardHint');
        $hint.removeClass('text-danger text-success text-info');
        if(paidAmount<=0)
        {
            $hint.addClass('text-danger').text('Enter paid amount greater than zero.');
            return;
        }
        if(diff<0)
        {
            $hint.addClass('text-danger').text('<?php echo __('Short payment');?>: Rs '+Math.abs(diff).toFixed(2)+' <?php echo __('will carry forward to next installment.');?>');
        }
        else if(diff>0)
        {
            $hint.addClass('text-success').text('<?php echo __('Over payment');?>: Rs '+diff.toFixed(2)+' <?php echo __('will auto-adjust in upcoming installments.');?>');
        }
        else
        {
            $hint.addClass('text-info').text('<?php echo __('Exact installment payment. No carry forward.');?>');
        }
    }
    function applyDefaultReceiptAccount(forceApply)
    {
        var $paymentType = $('#DealsPaymentPaymenttypeId');
        var $ledger = $('#DealsPaymentArdLedgerId');
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
        if(forceApply || !isArdLedgerTouched || !$ledger.val())
        {
            if(mappedLedgerId>0)
            {
                $ledger.val(mappedLedgerId.toString());
            }
            else if(forceApply || !isArdLedgerTouched)
            {
                $ledger.val('');
            }
        }
    }
    $(document).ready(function(){
        $('#paymentDate').datetimepicker({locale:'<?php echo$configLanguage;?>',format: '<?php echo$dpFormat;?>',useCurrent:false,showTodayButton:true,showClear:true});
        $('#DealsPaymentPaymentDate').attr('autocomplete','off').on('focus',function(){
            var picker = $('#paymentDate').data('DateTimePicker');
            if(picker){ picker.show(); }
        });
        $('#post_req').on('submit',function(){
            var $btn=$(this).find('button[type=submit]');
            if($btn.data('saving')){ return false; }
            $btn.data('saving',1).prop('disabled',true).html('<span class="fa fa-spinner fa-spin"></span>&nbsp;<?php echo __('Saving');?>');
        });
        $('#DealsPaymentTaxId').change(function(){calculateAmount();})
        $('#DealsPaymentEmi').on('input blur change',function(){calculateAmount();})
        $('#DealsPaymentExtraPayment').on('input blur change',function(){calculateAmount();})
        $('#DealsPaymentArdLedgerId').on('change',function(){isArdLedgerTouched=true;});
        $('#DealsPaymentPaymenttypeId').on('change',function(){applyDefaultReceiptAccount(false);});
        $("#paymentDate").on("dp.change", function (e) {
        dueDays=parseFloat($('#DealsPaymentDueDays').val());
        dueDate=$('#DealsPaymentDueDate').val();
        lateFees=parseFloat($('#DealsPaymentLateFees').val());
        currDate=e.date;
        var todayTarget = moment(dueDate);
        targetDueDate=moment(todayTarget).add('days', dueDays);
        if(targetDueDate<currDate)
        {
            lateDate=currDate.diff(dueDate, 'days');
            extraPayment=Math.round(((emi*lateFees)/100)*lateDate/365);
        }
        else
        extraPayment=0;
        $('#DealsPaymentExtraPayment').val(extraPayment);
        calculateAmount();});
        applyDefaultReceiptAccount(false);
        calculateAmount();
        });
    function calculateAmount()
    {
        emi=parseFloat($('#DealsPaymentEmi').val());
        extraPayment=parseFloat($('#DealsPaymentExtraPayment').val());
        if(isNaN(emi))
        emi=0;
        if(isNaN(extraPayment))
        extraPayment=0;
        totalAmount=emi+extraPayment;
        tax=parseFloat($('#DealsPaymentTaxId').find(':selected').text());
        if(isNaN(tax))
        tax=0;
        tax_amount=Math.round(parseFloat(totalAmount)*parseFloat(tax)/100);
        net_amount=tax_amount+totalAmount;
        $('#DealsPaymentTaxAmount').val(tax_amount);
        $('#DealsPaymentNetAmount').val(net_amount);
        updateCarryForwardHint();
    }
</script>

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
.wv-payment-add-page{
    max-width:1180px;
    margin:0 auto 22px;
}
.wv-payment-add-page .page-title{
    background:linear-gradient(135deg,#07152a 0%,#102746 72%,#ff0a46 170%);
    border:0;
    border-radius:14px 14px 0 0;
    box-shadow:0 16px 34px rgba(15,35,61,.14);
    margin:0;
    padding:18px 22px;
}
.wv-payment-add-page .page-title .title-env{
    display:block;
}
.wv-payment-add-page .page-title .title{
    color:#fff;
    font-size:24px;
    font-weight:900;
    letter-spacing:0;
    margin:0;
}
.wv-payment-add-page .page-title .title:before{
    content:"\f0d6";
    color:#7cf9ff;
    display:inline-block;
    font-family:FontAwesome;
    margin-right:10px;
}
.wv-payment-add-page .panel{
    background:#fff;
    border:1px solid #dce7f3;
    border-radius:0 0 14px 14px;
    box-shadow:0 18px 42px rgba(15,35,61,.12);
    overflow:hidden;
}
.wv-payment-add-page .panel-heading{
    display:none;
}
.wv-payment-add-page .panel-body{
    background:linear-gradient(180deg,#ffffff 0%,#f8fbff 100%);
    padding:18px 20px 20px;
}
.wv-payment-add-page .alert{
    border:0;
    border-radius:10px;
    font-weight:900;
    margin-bottom:14px;
}
.wv-payment-add-page .form-horizontal{
    margin:0;
}
.wv-payment-add-page .form-group{
    align-items:flex-start;
    background:#fff;
    border:1px solid #e1eaf4;
    border-radius:12px;
    box-shadow:0 8px 18px rgba(15,35,61,.05);
    display:flex;
    flex-wrap:wrap;
    margin:0 0 10px;
    padding:11px 12px 8px;
}
.wv-payment-add-page .form-horizontal{
    display:grid;
    gap:12px;
    grid-template-columns:repeat(3,minmax(0,1fr));
}
.wv-payment-add-page .form-group{
    display:block;
    margin:0;
    min-height:100%;
}
.wv-payment-add-page .form-group:before,
.wv-payment-add-page .form-group:after{
    display:none;
}
.wv-payment-add-page .form-group > label,
.wv-payment-add-page .form-group > div{
    display:block!important;
    float:none!important;
    max-width:100%!important;
    min-width:0!important;
    padding-left:0!important;
    padding-right:0!important;
    width:100%!important;
}
.wv-payment-add-page .control-label{
    color:#475569!important;
    display:block;
    font-size:11px;
    font-weight:900!important;
    letter-spacing:.25px;
    line-height:1.25;
    margin:0 0 7px;
    padding:0!important;
    text-align:left!important;
    text-transform:uppercase;
}
.wv-payment-add-page .control-label small{
    font-size:11px;
    font-weight:900;
}
.wv-payment-add-page .receipt-preview-label{
    margin-top:10px;
}
.wv-payment-add-page .input{
    display:block!important;
    max-width:100%!important;
    min-width:0!important;
    width:100%!important;
}
.wv-payment-add-page .form-control{
    background:#fff!important;
    border:1px solid #ccd9e7!important;
    border-radius:9px!important;
    box-shadow:none!important;
    color:#07152a;
    font-size:13px;
    font-weight:800;
    height:40px;
    max-width:100%!important;
    min-width:0!important;
    width:100%!important;
}
.wv-payment-add-page select.form-control,
.wv-payment-add-page select,
.wv-payment-add-page input[type="text"],
.wv-payment-add-page input[type="number"],
.wv-payment-add-page input[type="email"],
.wv-payment-add-page input[type="password"]{
    display:block!important;
    max-width:100%!important;
    min-width:0!important;
    width:100%!important;
}
.wv-payment-add-page .form-group:has(#DealsPaymentEmi),
.wv-payment-add-page .form-group.payment-amount-row{
    background:linear-gradient(135deg,#fff7f9 0%,#f8fbff 100%);
    border-color:#ffd1dc;
    box-shadow:0 12px 28px rgba(255,10,70,.10);
}
.wv-payment-add-page .payment-amount-row{
    display:grid;
    gap:10px;
    grid-template-columns:minmax(0,1fr) minmax(0,1.2fr);
}
.wv-payment-add-page .payment-amount-row > label,
.wv-payment-add-page .payment-amount-row > div{
    width:100%!important;
}
.wv-payment-add-page .payment-amount-row > label:nth-of-type(1){
    grid-column:1;
    grid-row:1;
}
.wv-payment-add-page .payment-amount-row > div:nth-of-type(1){
    grid-column:1;
    grid-row:2;
}
.wv-payment-add-page .payment-amount-row > label:nth-of-type(2){
    grid-column:2;
    grid-row:1;
}
.wv-payment-add-page .payment-amount-row > div:nth-of-type(2){
    grid-column:2;
    grid-row:2;
}
.wv-payment-add-page .payment-amount-row .input{
    width:100%;
}
.wv-payment-add-page #DealsPaymentEmi{
    border-color:#ff9ab3!important;
    color:#07152a;
    font-size:17px;
    font-weight:900;
    height:48px;
}
.wv-payment-add-page textarea.form-control{
    min-height:92px;
}
.wv-payment-add-page .form-control[readonly],
.wv-payment-add-page input[readonly]{
    background:#f8fbff!important;
    color:#172a3f;
}
.wv-payment-add-page .form-control:focus,
.wv-payment-add-page select:focus,
.wv-payment-add-page input:focus{
    border-color:#ff0a46!important;
    box-shadow:0 0 0 4px rgba(255,10,70,.10)!important;
}
.wv-payment-add-page .input-group.date{
    width:100%;
}
.wv-payment-add-page .input-group-addon{
    background:#07152a!important;
    border-color:#07152a!important;
    border-radius:0 9px 9px 0!important;
    color:#7cf9ff!important;
    min-width:42px;
}
.wv-payment-add-page .help-block{
    color:#64748b;
    font-size:11px;
    font-weight:800;
    line-height:1.45;
    margin:6px 0 0;
}
.wv-payment-add-page #carryForwardHint{
    background:#fff;
    border:1px solid #ffd1dc;
    border-radius:8px;
    color:#07152a;
    display:block;
    font-size:11px;
    font-weight:900;
    padding:7px 9px;
}
.wv-payment-add-page .form-group > .col-sm-4 > strong{
    align-items:center;
    background:#f8fbff;
    border:1px solid #ccd9e7;
    border-radius:9px;
    color:#07152a;
    display:flex;
    font-size:13px;
    font-weight:900;
    min-height:40px;
    padding:9px 11px;
    width:100%;
}
.wv-payment-add-page .form-group.text-left{
    display:block;
    grid-column:1 / -1;
    padding:14px;
}
.wv-payment-add-page .form-group.text-left .col-sm-offset-2{
    margin-left:0!important;
}
.wv-payment-add-page .form-group.text-left .col-sm-8{
    float:none!important;
    width:100%!important;
}
.wv-payment-add-page .form-group.single-line > .col-sm-4{
    width:100%!important;
}
.wv-payment-add-page .payment-slip-upload-wrap{
    background:linear-gradient(135deg,#f8fbff 0%,#eef6ff 100%);
    border:1px solid #d7e4f2;
    border-radius:12px;
    padding:14px;
}
.wv-payment-add-page .payment-slip-upload-wrap > label{
    color:#07152a;
    display:block;
    font-size:12px;
    font-weight:900;
    letter-spacing:.25px;
    margin:0 0 9px;
    text-transform:uppercase;
}
.wv-payment-add-page .payment-slip-upload-wrap > label small{
    font-size:12px;
    font-weight:900;
}
.wv-payment-add-page .payment-slip-upload-box{
    align-items:center;
    background:#fff;
    border:1px dashed #8fb0cf;
    border-radius:12px;
    box-shadow:inset 0 1px 0 rgba(255,255,255,.9);
    cursor:pointer;
    display:flex;
    flex-wrap:wrap;
    gap:8px;
    justify-content:center;
    min-height:104px;
    padding:14px;
    text-align:center;
    width:100%;
}
.wv-payment-add-page .payment-slip-upload-box.drag-over{
    background:#ecf7ff;
    border-color:#ff0a46;
    box-shadow:0 0 0 4px rgba(255,10,70,.08);
}
.wv-payment-add-page .payment-slip-upload-box .help-text{
    color:#334155;
    flex:1 1 100%;
    font-size:12px;
    font-weight:800;
    line-height:1.45;
    margin:0 0 2px;
}
.wv-payment-add-page .payment-slip-preview img{
    border-radius:10px;
    box-shadow:0 10px 24px rgba(15,35,61,.12);
    max-width:100%;
}
.wv-payment-add-page .btn{
    border-radius:999px!important;
    font-size:12px;
    font-weight:900!important;
    min-height:36px;
    padding:8px 14px;
}
.wv-payment-add-page .btn-success,
.wv-payment-add-page .btn-primary{
    background:#ff0a46!important;
    border-color:#ff0a46!important;
    box-shadow:0 10px 20px rgba(255,10,70,.18);
    color:#fff!important;
}
.wv-payment-add-page .btn-success:hover,
.wv-payment-add-page .btn-primary:hover{
    background:#07152a!important;
    border-color:#07152a!important;
}
.wv-payment-add-page .btn-danger{
    background:#07152a!important;
    border-color:#07152a!important;
    color:#fff!important;
}
.wv-payment-add-page .payment-form-actions{
    align-items:center;
    display:flex;
    flex-wrap:wrap;
    gap:9px;
    margin-top:14px;
}
#paymentSlipCropModal .modal-content{
    border:0;
    border-radius:14px;
    box-shadow:0 24px 54px rgba(15,35,61,.18);
    overflow:hidden;
}
#paymentSlipCropModal .modal-header{
    background:linear-gradient(135deg,#07152a 0%,#102746 100%);
    border:0;
    color:#fff;
}
#paymentSlipCropModal .modal-title{
    color:#fff;
    font-weight:900;
}
#paymentSlipCropModal .modal-footer{
    background:#f8fbff;
    border-top:1px solid #dce7f3;
}
@media(max-width:991px){
    .wv-payment-add-page{
        max-width:100%;
    }
    .wv-payment-add-page .control-label{
        padding-top:0!important;
    }
    .wv-payment-add-page .form-group > label,
    .wv-payment-add-page .form-group > div{
        width:100%!important;
    }
    .wv-payment-add-page .form-group > label:not(:first-child){
        margin-top:10px;
    }
}
@media(max-width:1199px){
    .wv-payment-add-page .form-horizontal{
        grid-template-columns:repeat(2,minmax(0,1fr));
    }
}
@media(max-width:767px){
    .wv-payment-add-page .form-horizontal{
        grid-template-columns:1fr;
    }
    .wv-payment-add-page .payment-amount-row{
        grid-template-columns:1fr;
    }
    .wv-payment-add-page .payment-amount-row > label:nth-of-type(1),
    .wv-payment-add-page .payment-amount-row > div:nth-of-type(1),
    .wv-payment-add-page .payment-amount-row > label:nth-of-type(2),
    .wv-payment-add-page .payment-amount-row > div:nth-of-type(2){
        grid-column:1;
        grid-row:auto;
    }
}
@media(max-width:575px){
    .wv-payment-add-page .page-title{
        border-radius:10px 10px 0 0;
        padding:15px;
    }
    .wv-payment-add-page .page-title .title{
        font-size:20px;
    }
    .wv-payment-add-page .panel-body{
        padding:12px;
    }
    .wv-payment-add-page .form-group{
        padding:10px;
    }
    .wv-payment-add-page .payment-form-actions .btn{
        width:100%;
    }
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

function renderPaymentSlipPreview($preview, file, existingUrl)
{
    if(!$preview || !$preview.length)
    {
        return;
    }
    if(existingUrl)
    {
        $preview.html('<img src="'+existingUrl+'" alt="Payment Slip Preview">');
        return;
    }
    if(!file)
    {
        $preview.html('');
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

function ensurePaymentSlipCropperAssets(done)
{
    if(typeof Cropper !== 'undefined')
    {
        done(true);
        return;
    }
    if(!document.getElementById('paymentSlipCropCss'))
    {
        var link = document.createElement('link');
        link.id = 'paymentSlipCropCss';
        link.rel = 'stylesheet';
        link.href = 'https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css';
        document.head.appendChild(link);
    }
    var existing = document.getElementById('paymentSlipCropScript');
    if(existing)
    {
        existing.addEventListener('load', function(){ done(typeof Cropper !== 'undefined'); });
        existing.addEventListener('error', function(){ done(false); });
        return;
    }
    var script = document.createElement('script');
    script.id = 'paymentSlipCropScript';
    script.src = 'https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js';
    script.onload = function(){ done(typeof Cropper !== 'undefined'); };
    script.onerror = function(){ done(false); };
    document.head.appendChild(script);
}

function openPaymentSlipCropper(file, $input, $box, $preview)
{
    ensurePaymentSlipCropperAssets(function(ready){
        if(!ready)
        {
            renderPaymentSlipPreview($preview, file, '');
            return;
        }
        var cropImage = document.getElementById('paymentSlipCropImage');
    if(!cropImage)
    {
        renderPaymentSlipPreview($preview, file, '');
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
    });
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
            renderPaymentSlipPreview($preview, null, '');
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
                renderPaymentSlipPreview($preview, file, '');
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
            renderPaymentSlipPreview(paymentSlipCropState.preview, croppedFile, '');
            $('#paymentSlipCropModal').modal('hide');
        }, 'image/png', 0.95);
    });
}

$(document).ready(function(){
    bindPaymentSlipUpload($(document));
    initPaymentSlipCropModal();
});
</script>

<div class="wv-payment-add-page">
<div class="page-title"> <div class="title-env"> <h1 class="title"><?php echo __('Add Payment');?></h1></div></div><div class="panel">
    <div class="panel-heading">
    </div>
                <div class="panel-body"><?php echo $this->Session->flash();?>
                <?php echo $this->Form->create('DealsPayment', array( 'controller' => 'DealsPayments', 'action' => "add/$dealId",'name'=>'post_req','id'=>'post_req','class'=>'form-horizontal','type'=>'file'));?>
            <div class="form-group">
                <label class="col-sm-2 control-label"><small><?php echo __('Client Name').':';?></small></label>
                <div class="col-sm-4">
                    <input type="text" class="form-control" value="<?php echo h(isset($dealClientName) ? $dealClientName : '-');?>" readonly="readonly">
                </div>
                <label class="col-sm-2 control-label"><small><?php echo __('Property Name').':';?></small></label>
                <div class="col-sm-4">
                    <input type="text" class="form-control" value="<?php echo h(isset($dealPropertyName) ? $dealPropertyName : '-');?>" readonly="readonly">
                </div>
            </div>
            <div class="form-group single-line">
                <label class="col-sm-2 control-label"><small><?php echo __('Membership No').':';?></small></label>
                <div class="col-sm-4">
                    <input type="text" class="form-control" value="<?php echo h(isset($dealMembershipNo) ? $dealMembershipNo : '-');?>" readonly="readonly">
                </div>
                <label class="col-sm-2 control-label receipt-preview-label"><small><?php echo __('Receipt No').':';?></small></label>
                <div class="col-sm-4">
                    <input type="text" class="form-control" value="<?php echo h(isset($dealReceiptNoPreview) ? $dealReceiptNoPreview : '-');?>" readonly="readonly">
                </div>
            </div>
            <div class="form-group payment-amount-row">
                <label for="group_name" class="col-sm-2 control-label"><small><?php echo __('Name').':';?></small></label>
                <div class="col-sm-4">
                   <strong><?php echo$paymentName;?></strong>
                </div>
                <label for="group_name" class="col-sm-2 control-label"><small><?php echo __('Amount').':';?></small></label>
                <div class="col-sm-4">
                   <?php echo $this->Form->input('emi', array('value'=>$emi,'label'=>false,'placeholder'=>__('Paid Amount'),'class'=>'form-control')); ?>
                   <small id="carryForwardHint" class="help-block text-info"><?php echo __('Exact installment payment. No carry forward.');?></small>
                </div>
            </div>
            <div class="form-group single-line">
                <label for="group_name" class="col-sm-2 control-label"><small><?php echo __('Extra/Late Amount').':';?></small></label>
                <div class="col-sm-4">
                   <?php echo $this->Form->input('extra_payment', array('value'=>$extraPayment,'label'=>false,'placeholder'=>__('Extra/Late Amount'),'class'=>'form-control')); ?>
                </div>               
            </div>
            <div class="form-group">
                <label for="group_name" class="col-sm-2 control-label"><small><?php echo __('Tax').':';?></small></label>
                <div class="col-sm-4">
                   <?php echo$this->Form->select('tax_id',array($tax),array('empty'=>__('Select Tax if applicable'),'label'=>false,'class'=>'form-control'));?>
                </div>
                <label for="group_name" class="col-sm-2 control-label"><small><?php echo __('Taxable Amount').':';?></small></label>
                <div class="col-sm-4">
                   <?php echo $this->Form->input('tax_amount', array('value'=>0,'label'=>false,'placeholder'=>__('Taxable Amount'),'class'=>'form-control','readonly'=>'readonly')); ?>
                </div>               
            </div>
            <div class="form-group">
                <label for="group_name" class="col-sm-2 control-label"><small><?php echo __('Payable Amount').':';?></small></label>
                <div class="col-sm-4">
                   <?php echo $this->Form->input('net_amount', array('value'=>$totalAmount,'label'=>false,'placeholder'=>__('Net Amount'),'class'=>'form-control','readonly'=>'readonly')); ?>
                </div>
                <label for="group_name" class="col-sm-2 control-label"><small><?php echo __('Payment Type').':';?></small></label>
                <div class="col-sm-4">
                   <?php echo $this->Form->select('paymenttype_id',$paymentType,array('empty'=>__('Please Select'),'label'=>false,'class'=>'form-control')); ?>
                </div>
               
            </div>
            <div class="form-group single-line">
                <label for="group_name" class="col-sm-2 control-label"><small><?php echo __('Receipt Account').':';?></small></label>
                <div class="col-sm-4">
                   <?php echo $this->Form->select('ard_ledger_id',isset($ardLedgerList)?$ardLedgerList:array(),array('empty'=>__('Auto by Payment Type'),'value'=>isset($ardSelectedLedgerId)?$ardSelectedLedgerId:0,'label'=>false,'class'=>'form-control')); ?>
                   <small class="help-block"><?php echo __('Auto uses Accounts Settings > General defaults (Online Transfer / Cash In Hand) for this agency. You can still select any other asset account manually.');?></small>
                </div>
            </div>
            <div class="form-group">
                <label for="group_name" class="col-sm-2 control-label"><small><?php echo __('Payment Date').':';?></small></label>
                <div class="col-sm-4">
                   <div class="input-group date" id="paymentDate">                        
                    <?php echo $this->Form->input('payment_date',array('type'=>'text','label' => false,'class'=>'form-control js-payment-date-picker','placeholder'=>__('Payment Date'),'div'=>false,'default'=>$date,'autocomplete'=>'off'));?>
                    <span class="input-group-addon"><span class="fa fa-calendar"></span></span>
                    </div>
                </div>
                <label for="group_name" class="col-sm-2 control-label"><small><?php echo __('Transaction Reference').':';?></small></label>
                <div class="col-sm-4">
                   <?php echo $this->Form->input('remarks', array('label'=>false,'placeholder'=>__('Transaction Reference'),'class'=>'form-control')); ?>
                </div>
            </div>
            <div class="form-group text-left">
                <div class="col-sm-offset-2 col-sm-8">
                    <div class="payment-slip-upload-wrap">
                        <label><small><?php echo __('Payment Slip').':';?></small></label>
                        <div class="payment-slip-upload-box js-payment-slip-box" data-target-input="#DealsPaymentPaymentSlip" data-preview-target="#DealsPaymentPaymentSlipPreview">
                            <div class="help-text"><?php echo __('Drag and drop payment slip here, or use Browse. Supported: PDF, DOC, DOCX, JPG, JPEG, PNG, GIF, WEBP');?></div>
                            <button type="button" class="btn btn-primary btn-sm js-slip-browse"><?php echo __('Browse');?></button>
                            <button type="button" class="btn btn-danger btn-sm js-slip-clear"><?php echo __('Clear');?></button>
                        </div>
                        <?php echo $this->Form->input('payment_slip', array('type'=>'file','label'=>false,'div'=>false,'id'=>'DealsPaymentPaymentSlip','class'=>'js-payment-slip-input','style'=>'display:none;','accept'=>'.pdf,.doc,.docx,.jpg,.jpeg,.png,.gif,.webp'));?>
                        <div id="DealsPaymentPaymentSlipPreview" class="payment-slip-preview js-payment-slip-preview"></div>
                    </div>
                    <div class="payment-form-actions">
                      <button type="submit" class="btn btn-success"><span class="fa fa-plus-circle"></span>&nbsp;<?php echo __('Save');?></button>
                      <?php if(!empty($isPopup)): ?>
                          <button type="button" class="btn btn-danger" onclick="window.close();"><span class="fa fa-close"></span>&nbsp;<?php echo __('Close');?></button>
                          <?php echo $this->Form->hidden('popup', array('value' => 1)); ?>
                      <?php else: ?>
                          <?php echo$this->Html->link('<span class="fa fa-close"></span>&nbsp;'.__('Close'),array('controller'=>'deals_payments','action'=>'index',$dealId),array('class'=>'btn btn-danger','escape'=>false));?>
                      <?php endif; ?>
                    </div>
                    <?php echo$this->Form->input('dealId',array('type'=>'hidden','name'=>'dealId','value'=>$dealId));
                    echo $this->Form->hidden('due_days',array('value'=>$dueDays));echo $this->Form->hidden('due_date',array('value'=>$dueDate));echo $this->Form->hidden('late_fees',array('value'=>$lateFees));?>
                </div>
            </div>
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
