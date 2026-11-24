<?php
$isAdr = (strtoupper(trim((string)$userType)) === 'ADR');
$selectedDealId = isset($dealId) ? (int)$dealId : 0;
$defaultDeduction = isset($defaultDeduction) ? (float)$defaultDeduction : 0;
$refundProgressSummary = isset($refundProgressSummary) && is_array($refundProgressSummary) ? $refundProgressSummary : array();
$refundHasPaidSchedule = isset($refundHasPaidSchedule) && is_array($refundHasPaidSchedule) ? $refundHasPaidSchedule : array();
?>
<style type="text/css">
.refund-status-pill{display:inline-block;border-radius:999px;padding:5px 10px;font-size:11px;font-weight:800;text-transform:uppercase;white-space:nowrap;}
.refund-status-pending{background:#fff1c2;color:#8a6100;}
.refund-status-partial{background:#e7f0ff;color:#174ea6;}
.refund-status-completed{background:#dff7e8;color:#08753b;}
.refund-status-draft{background:#eef2f7;color:#415168;}
#RefundCreateModal .modal-dialog{width:1060px;max-width:calc(100% - 28px);}
#RefundCreateModal .modal-content{border:0;border-radius:16px;box-shadow:0 24px 70px rgba(8,31,62,.28);overflow:hidden;}
#RefundCreateModal .modal-header{background:linear-gradient(135deg,#071c36 0%,#123b68 60%,#5a1748 100%);border:0;color:#fff;padding:15px 20px;}
#RefundCreateModal .modal-title{font-weight:900;letter-spacing:.01em;}
#RefundCreateModal .modal-header .close{color:#fff;opacity:.9;margin-top:1px;}
#RefundCreateModal .modal-body{background:#f5f8fc;padding:18px;}
.refund-create-form{margin:0;}
.refund-create-section{background:#fff;border:1px solid #dbe6f3;border-radius:14px;padding:14px;margin-bottom:14px;box-shadow:0 8px 24px rgba(15,35,58,.06);}
.refund-create-section-title{margin:0 0 12px;color:#08213f;font-size:13px;font-weight:900;text-transform:uppercase;letter-spacing:.05em;}
.refund-create-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px 14px;}
.refund-create-field{min-width:0;}
.refund-create-field.full{grid-column:1/-1;}
.refund-create-field.double{grid-column:span 2;}
.refund-create-field label{display:block;margin:0 0 5px;color:#0b2344;font-size:11px;font-weight:900;text-transform:uppercase;letter-spacing:.02em;}
.refund-create-field .form-control{height:38px;border:1px solid #cbd8ea;border-radius:9px;box-shadow:none;color:#081f3d;font-weight:700;background:#fff;}
.refund-create-field textarea.form-control{height:auto;min-height:76px;resize:vertical;}
.refund-create-field .readonly-metric{background:#f1f6fc;color:#0b3a75;}
.refund-create-field .money-focus{border-color:#ffb5ca;background:#fff8fb;color:#b60039;font-size:16px;font-weight:900;}
.refund-create-check{display:flex;align-items:center;gap:8px;margin:0;color:#0b2344;font-weight:800;}
.refund-create-actions{display:flex;justify-content:flex-end;gap:9px;padding-top:2px;}
@media(max-width:900px){#RefundCreateModal .modal-dialog{width:auto;}.refund-create-grid{grid-template-columns:1fr;}.refund-create-field.double{grid-column:1/-1;}}
</style>
<div class="page-title">
    <div class="title-env">
        <h1 class="title"><?php echo __('Deal Refunds');?></h1>
    </div>
</div>

<div class="panel">
    <div class="panel-body">
        <?php echo $this->Session->flash();?>

        <div class="btn-group" style="margin-bottom:12px;">
            <button type="button" class="btn btn-success" id="OpenRefundCreateModal"><span class="fa fa-plus"></span> <?php echo __('Create Refund');?></button>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th><?php echo __('Date');?></th>
                        <th><?php echo __('Deal');?></th>
                        <th><?php echo __('Client');?></th>
                        <th><?php echo __('Father / Guardian');?></th>
                        <th><?php echo __('Agency');?></th>
                        <th><?php echo __('Total Paid');?></th>
                        <th><?php echo __('Net Refund');?></th>
                        <th><?php echo __('Refund Paid');?></th>
                        <th><?php echo __('Balance');?></th>
                        <th><?php echo __('Status');?></th>
                        <th><?php echo __('Action');?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($refundRows)){ ?>
                    <tr><td colspan="11" class="text-center"><?php echo __('No refund records found.');?></td></tr>
                    <?php } ?>
                    <?php foreach((array)$refundRows as $row){
                        $refund = (array)$row['Dealrefund'];
                        $refundId = isset($refund['id']) ? (int)$refund['id'] : 0;
                        $progress = isset($refundProgressSummary[$refundId]) ? (array)$refundProgressSummary[$refundId] : array();
                        $progressStatus = isset($progress['progress_status']) ? (string)$progress['progress_status'] : (isset($refund['status']) ? (string)$refund['status'] : '');
                        $progressClass = 'refund-status-pending';
                        if($progressStatus === 'Completed'){$progressClass = 'refund-status-completed';}
                        elseif($progressStatus === 'Partial Pending'){$progressClass = 'refund-status-partial';}
                        elseif($progressStatus === 'Draft'){$progressClass = 'refund-status-draft';}
                        $refundPaidTotal = isset($progress['paid_total']) ? (float)$progress['paid_total'] : 0;
                        $refundBalance = isset($progress['balance']) ? (float)$progress['balance'] : (float)$refund['net_refund'];
                        $hasPaidSchedule = !empty($refundHasPaidSchedule[$refundId]) || (!empty($progress['paid_count']) && (int)$progress['paid_count'] > 0);
                    ?>
                    <tr>
                        <td><?php echo h(isset($refund['request_date']) ? $refund['request_date'] : '');?></td>
                        <td><?php echo h(isset($refund['deal_id']) ? '#'.$refund['deal_id'] : '');?></td>
                        <td>
                            <strong><?php echo h(isset($refund['applicant_name']) && $refund['applicant_name'] !== '' ? $refund['applicant_name'] : (isset($row['Client']['name']) ? $row['Client']['name'] : ''));?></strong>
                            <?php if(!empty($refund['mobile_no']) || !empty($refund['email'])){ ?>
                                <br><small class="text-muted"><?php echo h(trim((isset($refund['mobile_no']) ? $refund['mobile_no'] : '').' '.(isset($refund['email']) ? $refund['email'] : '')));?></small>
                            <?php } ?>
                        </td>
                        <td>
                            <?php
                            $fatherName = !empty($refund['father_name']) ? trim((string)$refund['father_name']) : (!empty($row['Client']['fathername']) ? trim((string)$row['Client']['fathername']) : '');
                            $relationType = !empty($refund['relation_type']) ? trim((string)$refund['relation_type']) : (!empty($row['Client']['relation_type']) ? trim((string)$row['Client']['relation_type']) : '');
                            if($fatherName !== ''){
                                echo '<strong style="color:#1e293b;">' . h($fatherName) . '</strong>';
                                if($relationType !== ''){
                                    echo '<small class="text-muted" style="display:block;font-size:11px;font-weight:600;">' . h($relationType) . '</small>';
                                }
                            } else {
                                echo '<span class="text-muted">-</span>';
                            }
                            ?>
                        </td>
                        <td><?php echo h(isset($row['Agency']['name']) ? $row['Agency']['name'] : '');?></td>
                        <td><?php echo h($currency.' '.number_format((float)$refund['total_paid'], 2));?></td>
                        <td>
                            <?php echo h($currency.' '.number_format((float)$refund['net_refund'], 2));?>
                            <?php if((float)$refund['deduction_percent'] < 0){ ?>
                                <br><span class="label label-success" style="font-size:10px;">+<?php echo h(number_format(abs((float)$refund['deduction_percent']), 2));?>% <?php echo __('Markup');?></span>
                            <?php } elseif((float)$refund['deduction_percent'] > 0){ ?>
                                <br><small class="text-muted">-<?php echo h(number_format((float)$refund['deduction_percent'], 2));?>% <?php echo __('Ded.');?></small>
                            <?php } ?>
                        </td>
                        <td><?php echo h($currency.' '.number_format($refundPaidTotal, 2));?></td>
                        <td><strong><?php echo h($currency.' '.number_format($refundBalance, 2));?></strong></td>
                        <td>
                            <span class="refund-status-pill <?php echo $progressClass;?>"><?php echo h($progressStatus);?></span>
                        </td>
                        <td>
                            <?php echo $this->Html->link('<span class="fa fa-eye"></span> '.__('View'), array('controller' => 'Dealrefunds', 'action' => 'view', (int)$refund['id']), array('escape' => false, 'class' => 'btn btn-xs btn-info'));?>
                            <?php echo $this->Html->link('<span class="fa fa-edit"></span> '.__('Edit'), array('controller' => 'Dealrefunds', 'action' => 'edit', (int)$refund['id']), array('escape' => false, 'class' => 'btn btn-xs btn-warning'));?>
                            <?php if($isAdr && !$hasPaidSchedule){ ?>
                                <?php echo $this->Html->link('<span class="fa fa-trash"></span> '.__('Delete'), array('controller' => 'Dealrefunds', 'action' => 'delete', (int)$refund['id']), array('escape' => false, 'class' => 'btn btn-xs btn-danger', 'confirm' => __('Are you sure you want to delete this refund record?')));?>
                            <?php } ?>
                            <?php if(isset($refund['status']) && $refund['status'] === 'Draft'){ ?>
                                <?php echo $this->Html->link('<span class="fa fa-check"></span> '.__('Finalize'), array('controller' => 'Dealrefunds', 'action' => 'finalize', (int)$refund['id']), array('escape' => false, 'class' => 'btn btn-xs btn-success'));?>
                            <?php } ?>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <?php if(!empty($refundRows)){ ?>
        <div class="text-center">
            <ul class="pagination">
                <?php echo $this->Paginator->prev('&laquo;', array('tag' => 'li', 'escape' => false), '<a href="javascript:void(0);">&laquo;</a>', array('tag' => 'li', 'escape' => false, 'class' => 'disabled'));?>
                <?php echo $this->Paginator->numbers(array('tag' => 'li', 'currentTag' => 'a', 'currentClass' => 'active', 'separator' => ''));?>
                <?php echo $this->Paginator->next('&raquo;', array('tag' => 'li', 'escape' => false), '<a href="javascript:void(0);">&raquo;</a>', array('tag' => 'li', 'escape' => false, 'class' => 'disabled'));?>
            </ul>
        </div>
        <?php } ?>
    </div>
</div>

<div class="modal fade" id="RefundCreateModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php echo __('Create Deal Refund');?></h4>
            </div>
            <div class="modal-body">
                <?php echo $this->Form->create('Dealrefund', array('url' => array('controller' => 'Dealrefunds', 'action' => 'create'), 'class' => 'refund-create-form'));?>
                <div class="refund-create-section">
                    <h5 class="refund-create-section-title"><?php echo __('Refund Summary');?></h5>
                    <div class="refund-create-grid">
                        <div class="refund-create-field full">
                            <label><?php echo __('Deal');?></label>
                        <?php echo $this->Form->select('deal_id', $dealOptions, array('empty' => __('Please Select'), 'class' => 'form-control', 'id' => 'RefundDealSelect'));?>
                        </div>
                        <div class="refund-create-field">
                            <label><?php echo __('Request Date');?></label>
                            <?php echo $this->Form->text('request_date', array('class' => 'form-control datepicker', 'placeholder' => '03-Jun-2026', 'autocomplete' => 'off', 'data-provide' => 'datepicker'));?>
                        </div>
                        <div class="refund-create-field">
                            <label><?php echo __('Total Paid');?></label>
                            <input type="text" class="form-control readonly-metric" id="RefundTotalPaid" readonly>
                        </div>
                        <div class="refund-create-field">
                            <label id="RefundDeductionLabel"><?php echo __('Deduction Amount');?></label>
                            <input type="text" class="form-control readonly-metric" id="RefundDeductionAmount" readonly>
                        </div>
                        <div class="refund-create-field">
                            <label id="RefundPercentLabel"><?php echo __('Deduction/Markup %');?></label>
                            <?php echo $this->Form->text('deduction_percent', array('class' => 'form-control money-focus', 'id' => 'RefundDeductionPercent', 'value' => $defaultDeduction));?>
                        </div>
                        <div class="refund-create-field">
                            <label><?php echo __('Net Refund');?></label>
                            <input type="text" class="form-control money-focus" id="RefundNetAmount">
                        </div>
                        <div class="refund-create-field">
                            <label><?php echo __('Cycle Count');?></label>
                            <?php echo $this->Form->text('cycle_count', array('class' => 'form-control', 'id' => 'RefundCycleCount', 'value' => 1));?>
                        </div>
                        <div class="refund-create-field">
                            <label><?php echo __('Frequency');?></label>
                            <?php echo $this->Form->select('cycle_frequency', array('Monthly' => 'Monthly', 'Quarterly' => 'Quarterly', 'Biannually' => 'Biannually', 'Yearly' => 'Yearly'), array('class' => 'form-control'));?>
                        </div>
                        <div class="refund-create-field">
                            <label><?php echo __('Cycle Start');?></label>
                            <?php echo $this->Form->text('cycle_start_date', array('class' => 'form-control datepicker', 'placeholder' => '03-Jun-2026', 'autocomplete' => 'off', 'data-provide' => 'datepicker'));?>
                        </div>
                        <div class="refund-create-field">
                            <label><?php echo __('Finalize');?></label>
                            <label class="refund-create-check"><?php echo $this->Form->checkbox('finalize_now');?> <?php echo __('Finalize Now');?></label>
                        </div>
                        <div class="refund-create-field full">
                            <label><?php echo __('Reason');?></label>
                            <?php echo $this->Form->textarea('reason', array('class' => 'form-control', 'rows' => 3));?>
                        </div>
                    </div>
                </div>

                <div class="refund-create-section">
                    <h5 class="refund-create-section-title"><?php echo __('Applicant Details');?></h5>
                    <div class="refund-create-grid">
                        <div class="refund-create-field">
                            <label><?php echo __('Applicant Name');?></label>
                            <?php echo $this->Form->text('applicant_name', array('class' => 'form-control', 'id' => 'RefundApplicantName'));?>
                        </div>
                        <div class="refund-create-field">
                            <label><?php echo __('Relationship');?></label>
                            <?php echo $this->Form->select('relation_type', array(
                                'Father' => __('Father (S/O, D/O)'),
                                'Husband' => __('Husband (W/O)'),
                                'Wife' => __('Wife'),
                                'Mother' => __('Mother'),
                                'Guardian' => __('Guardian'),
                                'Son' => __('Son'),
                                'Daughter' => __('Daughter'),
                                'Other' => __('Other')
                            ), array('class' => 'form-control', 'id' => 'RefundApplicantRelationType', 'empty' => __('Select Relationship')));?>
                        </div>
                        <div class="refund-create-field">
                            <label id="RefundApplicantFatherLabel"><?php echo __('Father / Guardian Name');?></label>
                            <?php echo $this->Form->text('father_name', array('class' => 'form-control', 'id' => 'RefundApplicantFatherName'));?>
                        </div>
                        <div class="refund-create-field">
                            <label><?php echo __('CNIC');?></label>
                            <?php echo $this->Form->text('cnic', array('class' => 'form-control', 'id' => 'RefundApplicantCnic'));?>
                        </div>
                        <div class="refund-create-field">
                            <label><?php echo __('Passport');?></label>
                            <?php echo $this->Form->text('passport_no', array('class' => 'form-control', 'id' => 'RefundApplicantPassport'));?>
                        </div>
                        <div class="refund-create-field">
                            <label><?php echo __('Mobile');?></label>
                            <?php echo $this->Form->text('mobile_no', array('class' => 'form-control', 'id' => 'RefundApplicantMobile'));?>
                        </div>
                        <div class="refund-create-field">
                            <label><?php echo __('Email');?></label>
                            <?php echo $this->Form->text('email', array('class' => 'form-control', 'id' => 'RefundApplicantEmail'));?>
                        </div>
                        <div class="refund-create-field double">
                            <label><?php echo __('Address');?></label>
                            <?php echo $this->Form->textarea('address', array('class' => 'form-control', 'rows' => 2, 'id' => 'RefundApplicantAddress'));?>
                        </div>
                    </div>
                </div>

                <div class="refund-create-section">
                    <h5 class="refund-create-section-title"><?php echo __('Bank Details');?></h5>
                    <div class="refund-create-grid">
                        <div class="refund-create-field">
                            <label><?php echo __('Account Title');?></label>
                            <?php echo $this->Form->text('account_title', array('class' => 'form-control'));?>
                        </div>
                        <div class="refund-create-field">
                            <label><?php echo __('Account No/IBAN');?></label>
                            <?php echo $this->Form->text('account_no', array('class' => 'form-control'));?>
                        </div>
                        <div class="refund-create-field">
                            <label><?php echo __('Bank Name');?></label>
                            <?php echo $this->Form->text('bank_name', array('class' => 'form-control'));?>
                        </div>
                        <div class="refund-create-field full">
                            <label><?php echo __('Branch & City');?></label>
                            <?php echo $this->Form->text('bank_branch_city', array('class' => 'form-control'));?>
                        </div>
                    </div>
                </div>

                <div class="refund-create-actions">
                        <button type="submit" class="btn btn-success"><span class="fa fa-save"></span> <?php echo __('Save Refund');?></button>
                        <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Close');?></button>
                </div>
                <?php echo $this->Form->end();?>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
function refundParseAmount(value){
    value = (value || '').toString().replace(/,/g, '').replace(/%/g, '').trim();
    var parsed = parseFloat(value);
    return isNaN(parsed) ? 0 : parsed;
}
function refundUpdateRelationLabel(){
    var rel = jQuery('#RefundApplicantRelationType').val();
    var label = '<?php echo __('Father / Guardian Name');?>';
    if(rel === 'Husband'){
        label = '<?php echo __('Husband Name (W/O)');?>';
    } else if(rel === 'Father'){
        label = '<?php echo __('Father Name (S/O, D/O)');?>';
    } else if(rel === 'Mother'){
        label = '<?php echo __('Mother Name');?>';
    } else if(rel === 'Wife'){
        label = '<?php echo __('Wife Name');?>';
    } else if(rel === 'Guardian'){
        label = '<?php echo __('Guardian Name');?>';
    }
    jQuery('#RefundApplicantFatherLabel').text(label);
}
function refundCompute(source){
    var total = refundParseAmount(jQuery('#RefundTotalPaid').val());
    var percent = refundParseAmount(jQuery('#RefundDeductionPercent').val());
    var net = refundParseAmount(jQuery('#RefundNetAmount').val());
    if(source === 'net'){
        if(net < 0){ net = 0; }
        percent = total > 0 ? ((total - net) / total) * 100.0 : 0;
        jQuery('#RefundDeductionPercent').val(percent.toFixed(2));
    } else {
        var deductionFromPercent = (total * percent) / 100.0;
        net = Math.max(0, total - deductionFromPercent);
    }
    var deduction = total - net;
    jQuery('#RefundDeductionAmount').val(deduction.toFixed(2));
    jQuery('#RefundNetAmount').val(net.toFixed(2));

    if(percent < 0){
        jQuery('#RefundDeductionLabel').text('<?php echo __('Markup Amount (+)');?>');
        jQuery('#RefundDeductionAmount').css({'color': '#16a34a', 'font-weight': 'bold'});
        jQuery('#RefundPercentLabel').html('<?php echo __('Deduction/Markup %');?> <span class="label label-success" style="font-size:10px;"><?php echo __('Markup');?></span>');
    } else if(percent > 0){
        jQuery('#RefundDeductionLabel').text('<?php echo __('Deduction Amount (-)');?>');
        jQuery('#RefundDeductionAmount').css({'color': '#dc2626', 'font-weight': 'bold'});
        jQuery('#RefundPercentLabel').html('<?php echo __('Deduction/Markup %');?> <span class="label label-danger" style="font-size:10px;"><?php echo __('Deduction');?></span>');
    } else {
        jQuery('#RefundDeductionLabel').text('<?php echo __('Deduction Amount');?>');
        jQuery('#RefundDeductionAmount').css({'color': '', 'font-weight': ''});
        jQuery('#RefundPercentLabel').text('<?php echo __('Deduction/Markup %');?>');
    }
}

jQuery(function($){
    var lastEditedMode = 'percent';
    if($.fn.datetimepicker){
        $('#DealrefundRequestDate, #DealrefundCycleStartDate').datetimepicker({
            format: 'DD-MMM-YYYY',
            useCurrent: true
        });
    }
    else if($.fn.datepicker){
        $('#DealrefundRequestDate, #DealrefundCycleStartDate').datepicker({
            format: 'dd-M-yyyy',
            autoclose: true,
            todayHighlight: true
        });
    }
    var metaUrlBase = <?php echo json_encode($this->Html->url(array('controller' => 'Dealrefunds', 'action' => 'dealmeta')));?>;
    function loadRefundMeta(dealId){
        dealId = parseInt(dealId || 0, 10) || 0;
        if(dealId <= 0){
            $('#RefundTotalPaid').val('');
            $('#RefundDeductionAmount').val('');
            $('#RefundNetAmount').val('');
            return;
        }
        $.getJSON(metaUrlBase + '/' + dealId, function(resp){
            if(resp && resp.ok){
                var paid = parseFloat(resp.total_paid || 0);
                if(isNaN(paid)){ paid = 0; }
                $('#RefundTotalPaid').val(paid.toFixed(2));
                $('#RefundApplicantName').val(resp.client_name || '');
                $('#RefundApplicantRelationType').val(resp.client_relation_type || 'Father');
                refundUpdateRelationLabel();
                $('#RefundApplicantFatherName').val(resp.client_father_name || '');
                $('#RefundApplicantCnic').val(resp.client_cnic || '');
                $('#RefundApplicantMobile').val(resp.client_phone || '');
                $('#RefundApplicantEmail').val(resp.client_email || '');
                $('#RefundApplicantAddress').val(resp.client_address || '');
                refundCompute('percent');
            }
        });
    }
    $('#OpenRefundCreateModal').on('click', function(e){
        e.preventDefault();
        $('#RefundCreateModal').modal('show');
        var preselectedDeal = $('#RefundDealSelect').val();
        loadRefundMeta(preselectedDeal);
    });
    $('#RefundDealSelect').on('change', function(){
        loadRefundMeta($(this).val());
    });
    $('#RefundApplicantRelationType').on('change', refundUpdateRelationLabel);
    $('#RefundDeductionPercent').on('input', function(){ lastEditedMode = 'percent'; refundCompute('percent'); });
    $('#RefundNetAmount').on('input', function(){ lastEditedMode = 'net'; refundCompute('net'); });
    $('#RefundCreateModal form').on('submit', function(){ refundCompute(lastEditedMode); });
});
</script>
