<?php
$row = isset($row) ? (array)$row : array();
$request = !empty($row['Dealrefundrequest']) ? (array)$row['Dealrefundrequest'] : array();
$rid = !empty($request['id']) ? (int)$request['id'] : 0;
$actionType = isset($actionType) ? (string)$actionType : '';
$status = !empty($request['status']) ? $request['status'] : 'Pending';
$defaultDeduction = isset($defaultDeduction) ? (float)$defaultDeduction : 0;
$totalPaid = !empty($request['total_paid']) ? (float)$request['total_paid'] : 0;
$approvedAmount = !empty($request['approved_amount']) ? (float)$request['approved_amount'] : 0;
$deductionPercent = isset($request['deduction_percent']) && (float)$request['deduction_percent'] > 0 ? (float)$request['deduction_percent'] : $defaultDeduction;
$suggestedApproved = $approvedAmount > 0 ? $approvedAmount : max(0, $totalPaid - round(($totalPaid * $deductionPercent) / 100, 2));
$suggestedDeductionAmount = max(0, round($totalPaid - $suggestedApproved, 2));
$defaultCycleCount = 1;
$defaultPerCycle = $defaultCycleCount > 0 ? round($suggestedApproved / $defaultCycleCount, 2) : $suggestedApproved;
$todayValue = date('d-M-Y');
$dealLabel = !empty($row['Deal']['invoice_no']) ? $row['Deal']['invoice_no'] : '#'.(!empty($request['deal_id']) ? $request['deal_id'] : '');
$propertyLabel = !empty($row['Property']['name']) ? $row['Property']['name'] : __('Property');
$clientName = !empty($request['applicant_name']) ? $request['applicant_name'] : (!empty($row['Client']['name']) ? $row['Client']['name'] : __('Client'));
$actionMeta = array(
    'review' => array('title' => __('Mark Under Review'), 'icon' => 'fa-search', 'class' => 'btn-info', 'post' => 'review'),
    'approve' => array('title' => __('Approve Refund Application'), 'icon' => 'fa-check', 'class' => 'btn-success', 'post' => 'approve'),
    'reject' => array('title' => __('Reject Refund Application'), 'icon' => 'fa-times', 'class' => 'btn-danger', 'post' => 'reject'),
    'convert' => array('title' => __('Create Deal Refund'), 'icon' => 'fa-share', 'class' => 'btn-warning', 'post' => 'convert')
);
$meta = isset($actionMeta[$actionType]) ? $actionMeta[$actionType] : $actionMeta['review'];
?>
<style type="text/css">
.wv-refund-action-page{color:#07152a;}
.wv-refund-action-head{align-items:center;background:linear-gradient(135deg,#07152a 0%,#123252 68%,#0f766e 130%);border-radius:8px;color:#fff;display:flex;gap:12px;justify-content:space-between;margin-bottom:14px;padding:18px 20px;}
.wv-refund-action-head h2{color:#fff!important;font-size:24px;font-weight:900;margin:0 0 4px;}
.wv-refund-action-head p{color:#dbeafe!important;font-size:13px;font-weight:800;margin:0;}
.wv-refund-card{background:#fff;border:1px solid #dbe5ef;border-radius:8px;box-shadow:0 10px 24px rgba(7,21,42,.06);margin-bottom:14px;padding:14px;}
.wv-refund-card h3{border-bottom:1px solid #e2eaf4;color:#07152a;font-size:16px;font-weight:900;margin:0 0 12px;padding-bottom:8px;text-transform:uppercase;}
.wv-refund-layout{display:grid;gap:14px;grid-template-columns:minmax(0,1fr) 360px;}
.wv-refund-info{display:grid;gap:9px;grid-template-columns:repeat(2,minmax(0,1fr));}
.wv-refund-info-item,.wv-refund-calc-box{background:#f8fbff;border:1px solid #e2eaf4;border-radius:8px;padding:10px;}
.wv-refund-info-item span,.wv-refund-calc-box span{color:#64748b;display:block;font-size:10px;font-weight:900;text-transform:uppercase;}
.wv-refund-info-item strong,.wv-refund-calc-box strong{color:#07152a;display:block;font-size:14px;font-weight:900;margin-top:4px;word-break:break-word;}
.wv-refund-calc-grid{display:grid;gap:8px;grid-template-columns:repeat(2,minmax(0,1fr));margin-bottom:12px;}
.wv-refund-calc-grid .full{grid-column:1/-1;}
.wv-refund-calc-box.net strong{color:#08753b;font-size:18px;}
.wv-refund-calc-box.deduct strong{color:#b45309;}
.wv-refund-form label{color:#334155;font-size:11px;font-weight:900;text-transform:uppercase;}
.wv-refund-form .form-control{border-radius:7px;font-weight:800;}
.wv-refund-actions{display:flex;gap:8px;flex-wrap:wrap;margin-top:12px;}
.wv-refund-actions .btn{border-radius:7px;font-weight:900;text-transform:uppercase;}
.wv-refund-check{align-items:center;background:#f8fbff;border:1px solid #e2eaf4;border-radius:8px;color:#07152a;display:flex;font-size:12px;font-weight:900;gap:7px;margin:8px 0 12px;padding:9px;}
.wv-refund-check input{margin:0;}
@media(max-width:991px){.wv-refund-layout{grid-template-columns:1fr}.wv-refund-action-head{display:block}}
@media(max-width:520px){.wv-refund-info,.wv-refund-calc-grid{grid-template-columns:1fr}.wv-refund-calc-grid .full{grid-column:auto}.wv-refund-actions .btn{width:100%;}}
</style>

<div class="wv-refund-action-page">
    <?php echo $this->Session->flash();?>

    <div class="wv-refund-action-head">
        <div>
            <h2><span class="fa <?php echo h($meta['icon']);?>"></span> <?php echo h($meta['title']);?></h2>
            <p><?php echo h($clientName);?> | <?php echo h($dealLabel);?> | <?php echo h($propertyLabel);?></p>
        </div>
        <?php echo $this->Html->link('<span class="fa fa-arrow-left"></span> '.__('Back To Application'), array('controller'=>'Dealrefundrequests','action'=>'view',$rid), array('escape'=>false,'class'=>'btn btn-default'));?>
    </div>

    <div class="wv-refund-layout">
        <div class="wv-refund-card">
            <h3><?php echo h($meta['title']);?></h3>
            <?php echo $this->Form->create('Dealrefundrequest', array('url'=>array('controller'=>'Dealrefundrequests','action'=>$meta['post'],$rid), 'class'=>'wv-refund-form', 'id'=>'RefundActionForm'));?>

            <?php if($actionType === 'review'){ ?>
                <div class="form-group">
                    <label><?php echo __('Admin Notes');?></label>
                    <?php echo $this->Form->textarea('admin_notes', array('class'=>'form-control','rows'=>5,'value'=>!empty($request['admin_notes']) ? $request['admin_notes'] : ''));?>
                </div>
                <button type="submit" class="btn <?php echo h($meta['class']);?> btn-lg"><span class="fa fa-search"></span> <?php echo __('Mark Under Review');?></button>
            <?php } ?>

            <?php if($actionType === 'approve'){ ?>
                <div class="wv-refund-calc-grid">
                    <div class="wv-refund-calc-box full"><span><?php echo __('Deal Paid Amount');?></span><strong><?php echo h($currency.' '.$this->Number->format($totalPaid));?></strong></div>
                    <div class="wv-refund-calc-box deduct"><span><?php echo __('Deduction Amount');?></span><strong id="ApprovalDeductionAmountText"><?php echo h($currency.' '.$this->Number->format($suggestedDeductionAmount));?></strong></div>
                    <div class="wv-refund-calc-box net"><span><?php echo __('Remaining / Net Refund');?></span><strong id="ApprovalNetRefundText"><?php echo h($currency.' '.$this->Number->format($suggestedApproved));?></strong></div>
                </div>
                <div class="form-group">
                    <label><?php echo __('Deduction %');?></label>
                    <?php echo $this->Form->text('deduction_percent', array('class'=>'form-control','id'=>'ApprovalDeductionPercent','value'=>$deductionPercent, 'data-total'=>number_format($totalPaid, 2, '.', '')));?>
                </div>
                <div class="form-group">
                    <label><?php echo __('Approved / Net Refund');?></label>
                    <?php echo $this->Form->text('approved_amount', array('class'=>'form-control','id'=>'ApprovalApprovedAmount','readonly'=>'readonly','value'=>number_format($suggestedApproved, 2, '.', '')));?>
                </div>
                <div class="form-group">
                    <label><?php echo __('Approval Notes');?></label>
                    <?php echo $this->Form->textarea('admin_notes', array('class'=>'form-control','rows'=>5,'value'=>!empty($request['admin_notes']) ? $request['admin_notes'] : ''));?>
                </div>
                <button type="submit" class="btn <?php echo h($meta['class']);?> btn-lg"><span class="fa fa-check"></span> <?php echo __('Approve Request');?></button>
            <?php } ?>

            <?php if($actionType === 'reject'){ ?>
                <div class="form-group">
                    <label><?php echo __('Rejection Notes');?></label>
                    <?php echo $this->Form->textarea('admin_notes', array('class'=>'form-control','rows'=>6,'value'=>!empty($request['admin_notes']) ? $request['admin_notes'] : ''));?>
                </div>
                <button type="submit" class="btn <?php echo h($meta['class']);?> btn-lg" onclick="return confirm('<?php echo h(__('Reject this refund application?'));?>');"><span class="fa fa-times"></span> <?php echo __('Reject Request');?></button>
            <?php } ?>

            <?php if($actionType === 'convert'){ ?>
                <p class="text-muted" style="font-weight:800;"><?php echo __('Review deduction and schedule settings before creating the deal refund. Tick finalize to generate the refund schedule immediately.');?></p>
                <div class="wv-refund-calc-grid">
                    <div class="wv-refund-calc-box full"><span><?php echo __('Deal Paid Amount');?></span><strong><?php echo h($currency.' '.$this->Number->format($totalPaid));?></strong></div>
                    <div class="wv-refund-calc-box deduct"><span><?php echo __('Deduction Amount');?></span><strong id="ConvertDeductionAmountText"><?php echo h($currency.' '.$this->Number->format($suggestedDeductionAmount));?></strong></div>
                    <div class="wv-refund-calc-box net"><span><?php echo __('Remaining / Net Refund');?></span><strong id="ConvertNetRefundText"><?php echo h($currency.' '.$this->Number->format($suggestedApproved));?></strong></div>
                    <div class="wv-refund-calc-box"><span><?php echo __('Per Schedule');?></span><strong id="ConvertPerCycleText"><?php echo h($currency.' '.$this->Number->format($defaultPerCycle));?></strong></div>
                    <div class="wv-refund-calc-box"><span><?php echo __('Installments');?></span><strong id="ConvertCycleSummary"><?php echo (int)$defaultCycleCount;?> <?php echo __('Monthly');?></strong></div>
                </div>
                <div class="form-group">
                    <label><?php echo __('Request Date');?></label>
                    <?php echo $this->Form->text('request_date', array('class'=>'form-control datepicker','id'=>'ConvertRequestDate','value'=>$todayValue,'autocomplete'=>'off','data-provide'=>'datepicker'));?>
                </div>
                <div class="form-group">
                    <label><?php echo __('Deduction %');?></label>
                    <?php echo $this->Form->text('deduction_percent', array('class'=>'form-control','id'=>'ConvertDeductionPercent','value'=>$deductionPercent, 'data-total'=>number_format($totalPaid, 2, '.', '')));?>
                </div>
                <div class="form-group">
                    <label><?php echo __('Net Refund');?></label>
                    <?php echo $this->Form->text('net_refund', array('class'=>'form-control','id'=>'ConvertNetRefund','readonly'=>'readonly','value'=>number_format($suggestedApproved, 2, '.', '')));?>
                </div>
                <div class="form-group">
                    <label><?php echo __('Installments / Cycles');?></label>
                    <?php echo $this->Form->text('cycle_count', array('class'=>'form-control','id'=>'ConvertCycleCount','value'=>$defaultCycleCount));?>
                </div>
                <div class="form-group">
                    <label><?php echo __('Frequency');?></label>
                    <?php echo $this->Form->select('cycle_frequency', array('Monthly'=>'Monthly','Quarterly'=>'Quarterly','Biannually'=>'Biannually','Yearly'=>'Yearly'), array('class'=>'form-control','id'=>'ConvertCycleFrequency','empty'=>false,'value'=>'Monthly'));?>
                </div>
                <div class="form-group">
                    <label><?php echo __('Schedule Start Date');?></label>
                    <?php echo $this->Form->text('cycle_start_date', array('class'=>'form-control datepicker','id'=>'ConvertCycleStartDate','value'=>$todayValue,'autocomplete'=>'off','data-provide'=>'datepicker'));?>
                </div>
                <label class="wv-refund-check"><?php echo $this->Form->checkbox('finalize_now', array('hiddenField'=>false));?> <?php echo __('Finalize now and generate refund schedule');?></label>
                <button type="submit" class="btn <?php echo h($meta['class']);?> btn-lg" onclick="return confirm('<?php echo h(__('Create this deal refund with the shown deduction and schedule summary?'));?>');"><span class="fa fa-share"></span> <?php echo __('Create Deal Refund');?></button>
            <?php } ?>

            <div class="wv-refund-actions">
                <?php echo $this->Html->link(__('Cancel'), array('controller'=>'Dealrefundrequests','action'=>'view',$rid), array('class'=>'btn btn-default'));?>
            </div>
            <?php echo $this->Form->end();?>
        </div>

        <div>
            <div class="wv-refund-card">
                <h3><?php echo __('Application Summary');?></h3>
                <div class="wv-refund-info">
                    <div class="wv-refund-info-item"><span><?php echo __('Status');?></span><strong><?php echo h($status);?></strong></div>
                    <div class="wv-refund-info-item"><span><?php echo __('Request No');?></span><strong><?php echo h(!empty($request['request_no']) ? $request['request_no'] : '-');?></strong></div>
                    <div class="wv-refund-info-item"><span><?php echo __('Client');?></span><strong><?php echo h($clientName);?></strong></div>
                    <div class="wv-refund-info-item"><span><?php echo __('Deal');?></span><strong><?php echo h($dealLabel);?></strong></div>
                    <div class="wv-refund-info-item"><span><?php echo __('Deal Paid Amount');?></span><strong><?php echo h($currency.' '.$this->Number->format($totalPaid));?></strong></div>
                    <div class="wv-refund-info-item"><span><?php echo __('Current Approved');?></span><strong><?php echo h($currency.' '.$this->Number->format($approvedAmount));?></strong></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
jQuery(function($){
    var refundCurrency = <?php echo json_encode(isset($currency) ? (string)$currency : '');?>;

    function refundParseAmount(value){
        value = (value || '').toString().replace(/,/g, '');
        var parsed = parseFloat(value);
        return isNaN(parsed) ? 0 : parsed;
    }

    function refundMoney(value){
        value = refundParseAmount(value);
        return refundCurrency + ' ' + value.toLocaleString(undefined, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function updateApprovalCalc(){
        var $percent = $('#ApprovalDeductionPercent');
        if(!$percent.length){ return; }
        var total = refundParseAmount($percent.data('total'));
        var percent = refundParseAmount($percent.val());
        if(percent < 0){ percent = 0; }
        var deduction = (total * percent) / 100.0;
        var net = Math.max(0, total - deduction);
        $('#ApprovalDeductionAmountText').text(refundMoney(deduction));
        $('#ApprovalNetRefundText').text(refundMoney(net));
        $('#ApprovalApprovedAmount').val(net.toFixed(2));
    }

    function updateConvertCalc(){
        var $percent = $('#ConvertDeductionPercent');
        if(!$percent.length){ return; }
        var total = refundParseAmount($percent.data('total'));
        var percent = refundParseAmount($percent.val());
        if(percent < 0){ percent = 0; }
        var cycleCount = parseInt($('#ConvertCycleCount').val(), 10);
        if(isNaN(cycleCount) || cycleCount < 1){ cycleCount = 1; }
        var frequency = $('#ConvertCycleFrequency').val() || 'Monthly';
        var deduction = (total * percent) / 100.0;
        var net = Math.max(0, total - deduction);
        var perCycle = cycleCount > 0 ? (net / cycleCount) : net;
        $('#ConvertDeductionAmountText').text(refundMoney(deduction));
        $('#ConvertNetRefundText').text(refundMoney(net));
        $('#ConvertNetRefund').val(net.toFixed(2));
        $('#ConvertPerCycleText').text(refundMoney(perCycle));
        $('#ConvertCycleSummary').text(cycleCount + ' ' + frequency);
    }

    $('#ApprovalDeductionPercent').on('input change keyup', updateApprovalCalc);
    $('#ConvertDeductionPercent,#ConvertCycleCount,#ConvertCycleFrequency').on('input change keyup', updateConvertCalc);
    updateApprovalCalc();
    updateConvertCalc();

    if($.fn.datetimepicker){
        $('#ConvertRequestDate,#ConvertCycleStartDate').datetimepicker({
            format: 'DD-MMM-YYYY',
            useCurrent: true
        });
    }
    else if($.fn.datepicker){
        $('#ConvertRequestDate,#ConvertCycleStartDate').datepicker({
            format: 'dd-M-yyyy',
            autoclose: true,
            todayHighlight: true
        });
    }
});
</script>
