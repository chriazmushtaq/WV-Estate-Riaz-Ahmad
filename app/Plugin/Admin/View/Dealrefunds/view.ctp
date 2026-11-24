<?php
$refundRow = isset($refund['Dealrefund']) ? $refund['Dealrefund'] : array();
$dealRow = isset($dealData['Deal']) ? $dealData['Deal'] : array();
$clientRow = isset($dealData['Client']) ? $dealData['Client'] : array();
$propertyRow = isset($dealData['Property']) ? $dealData['Property'] : array();
$status = isset($refundRow['status']) ? $refundRow['status'] : 'Draft';
$canFinalize = ($status === 'Draft');
$summaryDealNo = isset($dealRow['invoice_no']) && trim((string)$dealRow['invoice_no']) !== '' ? $dealRow['invoice_no'] : '#'.(int)$refundRow['deal_id'];
$summaryClientName = '';
if(isset($clientRow['name']) && trim((string)$clientRow['name']) !== '')
{
    $summaryClientName = $clientRow['name'];
}
elseif(isset($refundRow['applicant_name']) && trim((string)$refundRow['applicant_name']) !== '')
{
    $summaryClientName = $refundRow['applicant_name'];
}
$summaryPropertyName = isset($propertyRow['name']) && trim((string)$propertyRow['name']) !== '' ? $propertyRow['name'] : '';
if($summaryPropertyName === '' && !empty($dealRow['property_id']))
{
    $summaryPropertyName = 'Property #'.(int)$dealRow['property_id'];
}
if($summaryPropertyName === '')
{
    $summaryPropertyName = '-';
}
$refundProgress = isset($refundProgress) && is_array($refundProgress) ? $refundProgress : array();
$progressStatus = isset($refundProgress['progress_status']) ? (string)$refundProgress['progress_status'] : $status;
$refundClearanceLetterId = isset($refundClearanceLetterId) ? (int)$refundClearanceLetterId : 0;
$lastPaidScheduleId = isset($lastPaidScheduleId) ? (int)$lastPaidScheduleId : 0;
$refundPaidTotal = isset($refundProgress['paid_total']) ? (float)$refundProgress['paid_total'] : 0;
$refundBalance = isset($refundProgress['balance']) ? (float)$refundProgress['balance'] : (float)(isset($refundRow['net_refund']) ? $refundRow['net_refund'] : 0);
$refundDateFormat = function($dateValue) {
    $dateValue = trim((string)$dateValue);
    if($dateValue === '' || $dateValue === '0000-00-00')
    {
        return '';
    }
    $ts = strtotime($dateValue);
    return $ts ? date('d-M-Y', $ts) : $dateValue;
};
?>
<style type="text/css">
.refund-overview-card{border:1px solid #d8e4f2;border-radius:16px;background:linear-gradient(135deg,#ffffff 0%,#f8fbff 58%,#fff7fb 100%);box-shadow:0 12px 32px rgba(8,31,62,.08);margin:0 0 16px;padding:18px 22px;}
.refund-overview-grid{display:grid;grid-template-columns:repeat(3,minmax(190px,1fr));gap:14px 22px;}
.refund-overview-item{border-left:3px solid #0b2344;padding:4px 0 4px 12px;min-height:52px;}
.refund-overview-item .k{display:block;color:#687890;font-size:11px;font-weight:800;letter-spacing:.03em;text-transform:uppercase;margin-bottom:5px;}
.refund-overview-item .v{display:block;color:#071c36;font-size:15px;font-weight:800;line-height:1.25;word-break:break-word;}
.refund-overview-item.money .v{color:#0b3a75;font-size:16px;}
.refund-overview-item.net{border-left-color:#0fa36b;background:rgba(15,163,107,.07);border-radius:10px;padding:10px 12px;}
.refund-overview-item.net .v{color:#067548;font-size:17px;}
.refund-overview-item.balance{border-left-color:#ff0050;background:rgba(255,0,80,.06);border-radius:10px;padding:10px 12px;}
.refund-overview-item.balance .v{color:#b60039;font-size:17px;}
.refund-overview-item.status{border-left-color:#f59e0b;background:rgba(245,158,11,.08);border-radius:10px;padding:10px 12px;}
.refund-summary-card{border:1px solid #dbe6f3;border-radius:16px;background:#fff;box-shadow:0 12px 34px rgba(15,35,58,.08);margin-bottom:16px;overflow:hidden;}
.refund-summary-head{display:flex;align-items:center;justify-content:space-between;gap:12px;background:linear-gradient(135deg,#071c36 0%,#123b68 58%,#5a1748 100%);padding:13px 16px;color:#fff;}
.refund-summary-head h4{margin:0;color:#fff!important;font-weight:900;font-size:15px;letter-spacing:.01em;text-shadow:0 1px 1px rgba(0,0,0,.25);}
.refund-summary-head .hint{font-size:11px;font-weight:800;color:#fff!important;text-transform:uppercase;letter-spacing:.05em;text-shadow:0 1px 1px rgba(0,0,0,.25);}
.refund-detail-grid{display:grid;grid-template-columns:repeat(3,minmax(180px,1fr));gap:12px;padding:16px;background:linear-gradient(180deg,#fff 0%,#f7fbff 100%);}
.refund-detail-item{border:1px solid #dfe9f6;border-radius:12px;padding:11px 13px;background:#fff;min-height:64px;box-shadow:0 8px 18px rgba(15,35,58,.045);}
.refund-detail-item.address{grid-column:1/-1;}
.refund-detail-item .k{display:block;font-size:10px;text-transform:uppercase;color:#64748b;font-weight:900;letter-spacing:.05em;margin-bottom:5px;}
.refund-detail-item .v{display:block;color:#071c36;font-weight:800;word-break:break-word;line-height:1.35;}
.refund-detail-item.bank .v{color:#0b3a75;}
.refund-status-pill{display:inline-block;border-radius:999px;padding:4px 10px;font-size:11px;font-weight:800;text-transform:uppercase;}
.refund-status-paid{background:#dff7e8;color:#08753b;}
.refund-status-unpaid{background:#fff3cd;color:#8a6200;}
.refund-status-adjusted{background:#e9eef8;color:#274268;}
#RefundPayModal .modal-dialog{width:430px;max-width:calc(100% - 28px);}
#RefundPayModal .modal-content{border:0;border-radius:14px;box-shadow:0 18px 50px rgba(8,31,62,.24);overflow:hidden;}
#RefundPayModal .modal-header{background:#08213f;color:#fff;padding:12px 16px;border:0;}
#RefundPayModal .modal-header .close{color:#fff;opacity:.9;margin-top:1px;}
#RefundPayModal .modal-title{font-size:16px;font-weight:800;}
#RefundPayModal .modal-body{padding:14px 16px 12px;background:#f8fbff;}
.refund-pay-form .form-group{margin:0 0 10px;}
.refund-pay-form label{display:block;margin:0 0 5px;color:#0b2344;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.02em;}
.refund-pay-form .form-control{height:36px;border-radius:8px;border-color:#cbd8ea;box-shadow:none;font-weight:700;color:#08213f;}
.refund-pay-form textarea.form-control{height:auto;min-height:58px;resize:vertical;}
.refund-pay-form .input-group-addon{border-color:#cbd8ea;background:#fff;color:#08213f;border-radius:0 8px 8px 0;cursor:pointer;}
.refund-pay-form .input-group .form-control{border-radius:8px 0 0 8px;}
.refund-pay-form .help-block{margin:5px 0 0;color:#64748b;font-size:11px;line-height:1.35;}
.refund-pay-actions{display:flex;gap:8px;justify-content:flex-end;margin-top:2px;padding-top:8px;border-top:1px solid #e3ebf7;}
.refund-schedule-card{border:1px solid #dbe6f3;border-radius:16px;background:#fff;box-shadow:0 12px 34px rgba(15,35,58,.08);overflow:hidden;margin-bottom:8px;}
.refund-schedule-title{display:flex;justify-content:space-between;align-items:center;gap:12px;padding:13px 16px;background:#f8fbff;border-bottom:1px solid #e1eaf6;}
.refund-schedule-title h4{margin:0;color:#08213f;font-size:15px;font-weight:900;}
.refund-schedule-title span{color:#64748b;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.04em;}
.refund-schedule-table{table-layout:auto;width:100%;margin:0;}
.refund-schedule-table th,.refund-schedule-table td{vertical-align:middle;border-color:#e3ebf6!important;}
.refund-schedule-table thead th{background:linear-gradient(135deg,#071c36 0%,#173b63 100%);color:#fff!important;border-color:#173b63!important;text-transform:uppercase;letter-spacing:.03em;font-weight:800;}
.refund-schedule-table tbody tr:hover{background:#f8fbff;}
.refund-schedule-table .money-cell{font-weight:900;color:#0b3a75;}
.refund-schedule-table .remarks-cell{min-width:260px;color:#31445f;line-height:1.45;}
.refund-schedule-table th:nth-child(-n+6),.refund-schedule-table td:nth-child(-n+6){white-space:nowrap;width:1%;}
.refund-schedule-table th:nth-child(8),.refund-schedule-table td:nth-child(8){white-space:nowrap;width:1%;}
.refund-schedule-table th{font-size:12px;}
@media(max-width:900px){.refund-detail-grid,.refund-overview-grid{grid-template-columns:1fr;}}
</style>
<div class="page-title">
    <div class="title-env">
        <h1 class="title"><?php echo __('Refund Schedule');?></h1>
    </div>
</div>

<div class="panel">
    <div class="panel-body">
        <?php echo $this->Session->flash();?>

        <div class="btn-group" style="margin-bottom:12px;">
            <?php if($canFinalize){ ?>
                <?php echo $this->Html->link('<span class="fa fa-check"></span> '.__('Finalize Refund'), array('controller'=>'Dealrefunds','action'=>'finalize',(int)$refundRow['id']), array('escape'=>false,'class'=>'btn btn-success'));?>
            <?php } ?>
            <?php echo $this->Html->link('<span class="fa fa-file-text-o"></span> '.__('Print Agreement'), array('controller'=>'Dealrefunds','action'=>'agreement',(int)$refundRow['id']), array('escape'=>false,'class'=>'btn btn-info','target'=>'_blank'));?>
            <?php if($progressStatus === 'Completed') { ?>
                <?php if($refundClearanceLetterId > 0) { ?>
                    <?php echo $this->Html->link('<span class="fa fa-certificate"></span> '.__('Print Clearance Letter'), array('controller'=>'Realestateletters','action'=>'printletter',$refundClearanceLetterId), array('escape'=>false,'class'=>'btn btn-success','target'=>'_blank'));?>
                <?php } else { ?>
                    <?php echo $this->Html->link('<span class="fa fa-certificate"></span> '.__('Create Clearance Letter'), array('controller'=>'Dealrefunds','action'=>'clearance',(int)$refundRow['id']), array('escape'=>false,'class'=>'btn btn-success','target'=>'_blank'));?>
                <?php } ?>
            <?php } ?>
            <?php echo $this->Html->link('<span class="fa fa-list"></span> '.__('All Refunds'), array('controller'=>'Dealrefunds','action'=>'index'), array('escape'=>false,'class'=>'btn btn-default'));?>
        </div>

        <div class="refund-overview-card">
            <div class="refund-overview-grid">
                <div class="refund-overview-item">
                    <span class="k"><?php echo __('Deal');?></span>
                    <span class="v"><?php echo h($summaryDealNo);?></span>
                </div>
                <div class="refund-overview-item">
                    <span class="k"><?php echo __('Client Name');?></span>
                    <span class="v"><?php echo h($summaryClientName !== '' ? $summaryClientName : '-');?></span>
                </div>
                <div class="refund-overview-item">
                    <span class="k"><?php echo __('Property');?></span>
                    <span class="v"><?php echo h($summaryPropertyName);?></span>
                </div>
                <div class="refund-overview-item money">
                    <span class="k"><?php echo __('Total Paid');?></span>
                    <span class="v"><?php echo h($currency.' '.number_format((float)$refundRow['total_paid'],2));?></span>
                </div>
                <?php
                $dPercent = (float)$refundRow['deduction_percent'];
                $dAmount = (float)$refundRow['deduction_amount'];
                $isMarkup = ($dPercent < 0 || $dAmount < 0);
                ?>
                <div class="refund-overview-item">
                    <span class="k"><?php echo $isMarkup ? __('Markup') : __('Deduction');?></span>
                    <span class="v" style="<?php echo $isMarkup ? 'color:#16a34a;font-weight:bold;' : '';?>"><?php echo h(($isMarkup ? '+' : '') . number_format(abs($dPercent),2).'%'.($isMarkup ? ' ' . __('(Markup)') : ''));?></span>
                </div>
                <div class="refund-overview-item net">
                    <span class="k"><?php echo __('Net Refund');?></span>
                    <span class="v"><?php echo h($currency.' '.number_format((float)$refundRow['net_refund'],2));?></span>
                </div>
                <div class="refund-overview-item money">
                    <span class="k"><?php echo __('Refund Paid');?></span>
                    <span class="v"><?php echo h($currency.' '.number_format($refundPaidTotal,2));?></span>
                </div>
                <div class="refund-overview-item balance">
                    <span class="k"><?php echo __('Balance');?></span>
                    <span class="v"><?php echo h($currency.' '.number_format($refundBalance,2));?></span>
                </div>
                <div class="refund-overview-item status">
                    <span class="k"><?php echo __('Live Status');?></span>
                    <span class="v"><?php echo h($progressStatus);?></span>
                </div>
            </div>
        </div>

        <div class="refund-summary-card">
            <div class="refund-summary-head">
                <h4><?php echo __('Refund Contact Details');?></h4>
                <span class="hint"><?php echo __('Applicant Profile');?></span>
            </div>
            <div class="refund-detail-grid">
                <div class="refund-detail-item"><span class="k"><?php echo __('Applicant');?></span><span class="v"><?php echo h(isset($refundRow['applicant_name']) ? $refundRow['applicant_name'] : '');?></span></div>
                <div class="refund-detail-item"><span class="k"><?php echo !empty($refundRow['relation_type']) ? h($refundRow['relation_type']).' '.__('Name') : __('Father / Guardian Name');?></span><span class="v"><?php echo h(isset($refundRow['father_name']) ? $refundRow['father_name'] : '');?><?php if(!empty($refundRow['relation_type'])){ echo ' <small class="text-muted">('.h($refundRow['relation_type']).')</small>'; } ?></span></div>
                <div class="refund-detail-item"><span class="k"><?php echo __('Mobile');?></span><span class="v"><?php echo h(isset($refundRow['mobile_no']) ? $refundRow['mobile_no'] : '');?></span></div>
                <div class="refund-detail-item"><span class="k"><?php echo __('CNIC / Passport');?></span><span class="v"><?php echo h(trim((isset($refundRow['cnic']) ? $refundRow['cnic'] : '').' '.(isset($refundRow['passport_no']) ? $refundRow['passport_no'] : '')));?></span></div>
                <div class="refund-detail-item"><span class="k"><?php echo __('Email');?></span><span class="v"><?php echo h(isset($refundRow['email']) ? $refundRow['email'] : '');?></span></div>
                <div class="refund-detail-item bank"><span class="k"><?php echo __('Bank');?></span><span class="v"><?php echo h(trim((isset($refundRow['bank_name']) ? $refundRow['bank_name'] : '').' '.(isset($refundRow['bank_branch_city']) ? $refundRow['bank_branch_city'] : '')));?></span></div>
                <div class="refund-detail-item bank"><span class="k"><?php echo __('Account Title');?></span><span class="v"><?php echo h(isset($refundRow['account_title']) ? $refundRow['account_title'] : '');?></span></div>
                <div class="refund-detail-item bank"><span class="k"><?php echo __('Account No / IBAN');?></span><span class="v"><?php echo h(isset($refundRow['account_no']) ? $refundRow['account_no'] : '');?></span></div>
                <div class="refund-detail-item address"><span class="k"><?php echo __('Address');?></span><span class="v"><?php echo h(isset($refundRow['address']) ? $refundRow['address'] : '');?></span></div>
            </div>
        </div>

        <div class="refund-schedule-card">
            <div class="refund-schedule-title">
                <h4><?php echo __('Refund Payment Schedule');?></h4>
                <span><?php echo h(count((array)$scheduleRows));?> <?php echo __('Cycles');?></span>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-striped refund-schedule-table">
                    <thead>
                        <tr>
                            <th><?php echo __('Cycle');?></th>
                            <th><?php echo __('Date');?></th>
                            <th><?php echo __('Schedule Amount');?></th>
                            <th><?php echo __('Paid Amount');?></th>
                            <th><?php echo __('Status');?></th>
                            <th><?php echo __('Paid Date');?></th>
                            <th><?php echo __('Remarks');?></th>
                            <th><?php echo __('Action');?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($scheduleRows)){ ?>
                        <tr><td colspan="8" class="text-center"><?php echo __('No schedule found.');?></td></tr>
                        <?php } ?>
                        <?php foreach((array)$scheduleRows as $row){
                            $s = $row['Dealrefundschedule'];
                            $voucherTransactionId = !empty($s['voucher_transaction_id']) ? (int)$s['voucher_transaction_id'] : 0;
                            $rowStatus = isset($s['status']) ? (string)$s['status'] : '';
                            $statusClass = 'refund-status-unpaid';
                            if($rowStatus === 'Paid'){$statusClass = 'refund-status-paid';}
                            elseif($rowStatus === 'Adjusted'){$statusClass = 'refund-status-adjusted';}
                        ?>
                        <tr>
                            <td><strong><?php echo (int)$s['cycle_no'];?></strong></td>
                            <td><?php echo h($refundDateFormat($s['scheduled_date']));?></td>
                            <td class="money-cell"><?php echo h($currency.' '.number_format((float)$s['amount'],2));?></td>
                            <td class="money-cell"><?php echo ((float)$s['paid_amount'] > 0) ? h($currency.' '.number_format((float)$s['paid_amount'],2)) : '-';?></td>
                            <td><span class="refund-status-pill <?php echo $statusClass;?>"><?php echo h($rowStatus);?></span></td>
                            <td><?php echo h($refundDateFormat($s['paid_date']));?></td>
                            <td class="remarks-cell"><?php echo nl2br(h(isset($s['remarks']) ? $s['remarks'] : ''));?></td>
                            <td>
                                <?php if($rowStatus === 'Unpaid' && (float)$s['amount'] > 0){ ?>
                                    <button type="button" class="btn btn-xs btn-success js-pay-refund" data-id="<?php echo (int)$s['id'];?>" data-amount="<?php echo (float)$s['amount'];?>"><span class="fa fa-money"></span> <?php echo __('Pay');?></button>
                                <?php } elseif($rowStatus === 'Paid' && $voucherTransactionId > 0) { ?>
                                    <?php echo $this->Html->link('<span class="fa fa-print"></span> '.__('Print'), array('controller'=>'Ardaccounts','action'=>'printvoucher',$voucherTransactionId,(int)$refundRow['project_id']), array('target'=>'_blank', 'escape'=>false, 'class'=>'btn btn-xs btn-info'));?>
                                    <?php if((int)$s['id'] === $lastPaidScheduleId) { ?>
                                        <button type="button"
                                                class="btn btn-xs btn-warning js-edit-refund"
                                                data-id="<?php echo (int)$s['id'];?>"
                                                data-amount="<?php echo (float)$s['paid_amount'];?>"
                                                data-date="<?php echo h($refundDateFormat($s['paid_date']));?>"
                                                data-ledger="<?php echo (int)$s['paid_ledger_id'];?>"
                                                data-remarks="<?php echo h(isset($s['remarks']) ? $s['remarks'] : '');?>">
                                            <span class="fa fa-pencil"></span> <?php echo __('Edit');?>
                                        </button>
                                        <?php echo $this->Form->postLink('<span class="fa fa-trash"></span> '.__('Delete'), array('controller'=>'Dealrefunds','action'=>'deletepayment',(int)$s['id']), array('escape'=>false,'class'=>'btn btn-xs btn-danger','confirm'=>__('Delete last paid refund entry and reopen schedule?')));?>
                                    <?php } ?>
                                <?php } else { ?>
                                    <span class="text-muted"><?php echo h($rowStatus);?></span>
                                <?php } ?>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="RefundPayModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog refund-pay-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="RefundPayModalTitle"><?php echo __('Pay Refund');?></h4>
            </div>
            <div class="modal-body">
                <?php echo $this->Form->create('Dealrefundschedule', array('url' => array('controller'=>'Dealrefunds','action'=>'pay'), 'class' => 'refund-pay-form', 'id' => 'RefundPayForm'));?>
                <?php echo $this->Form->hidden('id', array('id' => 'RefundScheduleId'));?>
                <div class="form-group">
                    <label><?php echo __('Paid Date');?></label>
                    <div class="input-group">
                        <?php echo $this->Form->text('paid_date', array('class' => 'form-control refund-date-picker', 'id' => 'RefundPaidDate', 'value' => date('d-F-Y'), 'autocomplete' => 'off'));?>
                        <span class="input-group-addon" id="RefundPaidDatePickerBtn"><span class="fa fa-calendar"></span></span>
                    </div>
                </div>
                <div class="form-group">
                    <label><?php echo __('Amount');?></label>
                    <?php echo $this->Form->text('paid_amount', array('class' => 'form-control', 'id' => 'RefundPayAmount'));?>
                    <p class="help-block"><?php echo __('Short or extra payment auto-adjusts the refund schedule. One voucher is created for this payment only.');?></p>
                </div>
                <div class="form-group">
                    <label><?php echo __('Pay From');?></label>
                    <?php echo $this->Form->select('paid_ledger_id', $assetLedgerOptions, array('class' => 'form-control', 'id' => 'RefundPaidLedgerId'));?>
                </div>
                <div class="form-group">
                    <label><?php echo __('Remarks');?></label>
                    <?php echo $this->Form->textarea('remarks', array('class' => 'form-control', 'rows' => 2, 'id' => 'RefundRemarks'));?>
                </div>
                <div class="refund-pay-actions">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Close');?></button>
                    <button type="submit" class="btn btn-success" id="RefundPaySubmit"><?php echo __('Save Payment');?></button>
                </div>
                <?php echo $this->Form->end();?>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
jQuery(function($){
    if($.fn.datetimepicker){
        $('#RefundPaidDate').datetimepicker({
            format: 'DD-MMMM-YYYY',
            useCurrent: true
        });
    } else if($.fn.datepicker){
        $('#RefundPaidDate').datepicker({
            format: 'dd-MM-yyyy',
            autoclose: true,
            todayHighlight: true
        });
    }
    $('#RefundPaidDatePickerBtn').on('click', function(){
        var $input = $('#RefundPaidDate');
        $input.focus();
        if($.fn.datetimepicker && $input.data('DateTimePicker')){
            $input.data('DateTimePicker').show();
        } else if($.fn.datepicker){
            $input.datepicker('show');
        }
    });
    $('.js-pay-refund').on('click', function(){
        var id = $(this).data('id');
        var amt = $(this).data('amount');
        $('#RefundScheduleId').val(id);
        $('#RefundPayAmount').val(parseFloat(amt || 0).toFixed(2));
        $('#RefundPayModalTitle').text(<?php echo json_encode(__('Pay Refund'));?>);
        $('#RefundPaySubmit').removeClass('btn-warning').addClass('btn-success').text(<?php echo json_encode(__('Save Payment'));?>);
        $('#RefundRemarks').val('');
        $('#RefundPayForm').attr('action', <?php echo json_encode($this->Html->url(array('controller'=>'Dealrefunds','action'=>'pay')));?> + '/' + id);
        $('#RefundPayModal').modal('show');
    });
    $('.js-edit-refund').on('click', function(){
        var id = $(this).data('id');
        var amt = $(this).data('amount');
        var paidDate = $(this).data('date') || '';
        var ledgerId = $(this).data('ledger') || '';
        var remarks = $(this).attr('data-remarks') || '';
        $('#RefundScheduleId').val(id);
        $('#RefundPayAmount').val(parseFloat(amt || 0).toFixed(2));
        if(paidDate !== ''){
            $('#RefundPaidDate').val(paidDate);
        }
        $('#RefundPaidLedgerId').val(ledgerId);
        $('#RefundRemarks').val(remarks);
        $('#RefundPayModalTitle').text(<?php echo json_encode(__('Edit Refund Payment'));?>);
        $('#RefundPaySubmit').removeClass('btn-success').addClass('btn-warning').text(<?php echo json_encode(__('Update Payment'));?>);
        $('#RefundPayForm').attr('action', <?php echo json_encode($this->Html->url(array('controller'=>'Dealrefunds','action'=>'editpayment')));?> + '/' + id);
        $('#RefundPayModal').modal('show');
    });
});
</script>
