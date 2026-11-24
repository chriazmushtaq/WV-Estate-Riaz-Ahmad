<?php
$row = isset($row) ? (array)$row : array();
$request = !empty($row['Dealrefundrequest']) ? (array)$row['Dealrefundrequest'] : array();
$convertedRefund = isset($convertedRefund) ? (array)$convertedRefund : array();
$refundScheduleRows = isset($refundScheduleRows) ? (array)$refundScheduleRows : array();
$refundProgress = isset($refundProgress) ? (array)$refundProgress : array();
$refundRow = !empty($convertedRefund['Dealrefund']) ? (array)$convertedRefund['Dealrefund'] : array();
$rid = !empty($request['id']) ? (int)$request['id'] : 0;
$status = !empty($request['status']) ? $request['status'] : 'Pending';
$statusKey = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $status));
$invoice = !empty($row['Deal']['invoice_no']) ? $row['Deal']['invoice_no'] : '#'.(isset($request['deal_id']) ? $request['deal_id'] : '');
$property = !empty($row['Property']['name']) ? $row['Property']['name'] : __('Property');
$refundDocs = array(
    'client_photo' => __('Client Photo'),
    'cnic_front' => __('CNIC Front'),
    'cnic_back' => __('CNIC Back'),
    'file_front' => __('File Front'),
    'file_back' => __('File Back'),
    'client_signature' => __('Client Signature')
);
$refundDateFormat = $sysDay.$dateSep.$sysMonth.$dateSep.$sysYear.(isset($sysHour) ? $dateGap.$sysHour.$timeSep.$sysMin.$dateGap.$sysMer : '');
$createdText = !empty($request['created']) ? $this->Time->format($refundDateFormat, $request['created']) : '-';
$reviewedText = !empty($request['reviewed_at']) ? $this->Time->format($refundDateFormat, $request['reviewed_at']) : '-';
$statusUpdatedRaw = !empty($request['status_updated_at']) ? $request['status_updated_at'] : (!empty($request['reviewed_at']) ? $request['reviewed_at'] : (!empty($request['modified']) ? $request['modified'] : $request['created']));
$statusUpdatedText = !empty($statusUpdatedRaw) ? $this->Time->format($refundDateFormat, $statusUpdatedRaw) : '-';
$processLevelMap = array('Pending' => 1, 'Under Review' => 2, 'Approved' => 3, 'Rejected' => 3, 'Cancelled' => 3, 'Converted To Refund' => 4);
$processLevel = isset($processLevelMap[$status]) ? (int)$processLevelMap[$status] : 1;
$decisionLabel = __('Approved');
if($status === 'Rejected'){$decisionLabel = __('Rejected');}
if($status === 'Cancelled'){$decisionLabel = __('Cancelled');}
$processSteps = array(
    array('level' => 1, 'title' => __('Submitted'), 'date' => $createdText),
    array('level' => 2, 'title' => __('Under Review'), 'date' => ($processLevel === 2 ? $statusUpdatedText : ($processLevel > 2 ? $reviewedText : '-'))),
    array('level' => 3, 'title' => $decisionLabel, 'date' => ($processLevel === 3 ? $statusUpdatedText : ($processLevel > 3 ? $reviewedText : '-'))),
    array('level' => 4, 'title' => __('Refund Created'), 'date' => ($processLevel >= 4 ? $statusUpdatedText : '-'))
);
$clientRefundPaidTotal = isset($refundProgress['paid_total']) ? (float)$refundProgress['paid_total'] : 0;
$clientRefundBalance = isset($refundProgress['balance']) ? (float)$refundProgress['balance'] : (!empty($refundRow['net_refund']) ? (float)$refundRow['net_refund'] : 0);
$clientRefundPendingTotal = isset($refundProgress['pending_total']) ? (float)$refundProgress['pending_total'] : $clientRefundBalance;
$clientRefundProgressStatus = !empty($refundProgress['progress_status']) ? $refundProgress['progress_status'] : (!empty($refundRow['status']) ? $refundRow['status'] : '-');
$refundOnlyDate = function($dateValue) {
    $dateValue = trim((string)$dateValue);
    if($dateValue === '' || $dateValue === '0000-00-00')
    {
        return '-';
    }
    $ts = strtotime($dateValue);
    return $ts ? date('d-M-Y', $ts) : $dateValue;
};
?>
<style type="text/css">
.wv-refund-view{background:#f4f7fb;margin:-10px -5px 0;padding:14px;}
.wv-refund-view *{box-sizing:border-box;}
.wv-refund-view-head{align-items:center;background:linear-gradient(135deg,#07152a 0%,#123252 72%,#047857 150%);border-radius:10px;color:#fff;display:flex;gap:12px;justify-content:space-between;margin-bottom:12px;padding:17px 18px;}
.wv-refund-view-head h1{color:#fff!important;font-size:24px;font-weight:900;margin:0 0 4px;}
.wv-refund-view-head p{color:#e6f2ff!important;font-size:12px;font-weight:800;margin:0;}
.wv-refund-status{border-radius:999px;display:inline-block;font-size:11px;font-weight:900;padding:7px 10px;text-transform:uppercase;white-space:nowrap;}
.wv-refund-status-pending{background:#fff1c2;color:#8a6100;}
.wv-refund-status-under-review{background:#e7f0ff;color:#174ea6;}
.wv-refund-status-approved{background:#dff7e8;color:#08753b;}
.wv-refund-status-rejected{background:#ffe4e6;color:#b91c1c;}
.wv-refund-status-cancelled{background:#eef2f7;color:#475569;}
.wv-refund-status-converted-to-refund{background:#dcfce7;color:#166534;}
.wv-refund-layout{display:grid;gap:12px;grid-template-columns:1fr 320px;}
.wv-refund-card{background:#fff;border:1px solid #dfe9f5;border-radius:10px;box-shadow:0 8px 20px rgba(15,23,42,.07);padding:14px;}
.wv-refund-card h2{border-bottom:1px solid #e2eaf4;color:#07152a;font-size:16px;font-weight:900;margin:0 0 12px;padding-bottom:8px;text-transform:uppercase;}
.wv-refund-info{display:grid;gap:8px;grid-template-columns:repeat(3,minmax(0,1fr));}
.wv-refund-info-item{background:#f8fbff;border:1px solid #e2eaf4;border-radius:8px;padding:9px;}
.wv-refund-info-item span{color:#64748b;display:block;font-size:10px;font-weight:900;text-transform:uppercase;}
.wv-refund-info-item strong{color:#07152a;display:block;font-size:13px;font-weight:900;margin-top:4px;word-break:break-word;}
.wv-refund-note{background:#fbfdff;border:1px solid #e2eaf4;border-radius:8px;color:#334155;font-size:13px;font-weight:700;line-height:1.55;padding:10px;white-space:pre-line;}
.wv-refund-actions{display:flex;flex-direction:column;gap:8px;}
.wv-refund-actions .btn{border-radius:8px;font-size:12px;font-weight:900;text-transform:uppercase;}
.wv-refund-doc-list{display:grid;gap:8px;grid-template-columns:repeat(2,minmax(0,1fr));}
.wv-refund-doc-item{align-items:center;background:#f8fbff;border:1px solid #e2eaf4;border-radius:8px;display:flex;gap:8px;justify-content:space-between;padding:9px;}
.wv-refund-doc-item strong{color:#07152a;font-size:12px;font-weight:900;}
.wv-refund-doc-item .btn{border-radius:7px;font-size:11px;font-weight:900;}
.wv-refund-process{display:grid;gap:8px;grid-template-columns:repeat(4,minmax(0,1fr));}
.wv-refund-step{background:#f8fbff;border:1px solid #dbe5ef;border-radius:8px;padding:10px;}
.wv-refund-step:before{background:#cbd5e1;border-radius:999px;color:#fff;content:attr(data-step);display:block;font-size:11px;font-weight:900;height:26px;line-height:26px;margin-bottom:7px;text-align:center;width:26px;}
.wv-refund-step strong{color:#07152a;display:block;font-size:12px;font-weight:900;text-transform:uppercase;}
.wv-refund-step span{color:#64748b;display:block;font-size:11px;font-weight:800;margin-top:4px;}
.wv-refund-step.done{background:#ecfdf5;border-color:#a7f3d0;}
.wv-refund-step.done:before{background:#059669;}
.wv-refund-step.active{background:#eff6ff;border-color:#93c5fd;box-shadow:0 8px 18px rgba(37,99,235,.12);}
.wv-refund-step.active:before{background:#2563eb;}
.wv-refund-schedule-table{margin:0;}
.wv-refund-schedule-table th{background:#07152a;color:#fff!important;font-size:11px;text-transform:uppercase;white-space:nowrap;}
.wv-refund-schedule-table td{vertical-align:middle!important;}
.wv-refund-money{color:#0b3a75;font-weight:900;white-space:nowrap;}
.wv-refund-pay-status{border-radius:999px;display:inline-block;font-size:10px;font-weight:900;padding:5px 8px;text-transform:uppercase;white-space:nowrap;}
.wv-refund-pay-status-paid{background:#dff7e8;color:#08753b;}
.wv-refund-pay-status-unpaid{background:#fff3cd;color:#8a6200;}
.wv-refund-pay-status-adjusted{background:#e9eef8;color:#274268;}
@media(max-width:991px){.wv-refund-layout{grid-template-columns:1fr}.wv-refund-info{grid-template-columns:repeat(2,minmax(0,1fr));}.wv-refund-view-head{display:block}.wv-refund-status{margin-top:10px}}
@media(max-width:520px){.wv-refund-view{margin:0;padding:10px}.wv-refund-info,.wv-refund-doc-list,.wv-refund-process{grid-template-columns:1fr}.wv-refund-actions .btn{width:100%;}}
</style>

<div class="wv-refund-view">
    <?php echo $this->Session->flash();?>
    <div class="wv-refund-view-head">
        <div>
            <h1><?php echo __('Refund Request');?> #<?php echo $rid;?></h1>
            <p><?php echo h($property);?> | <?php echo __('Deal');?>: <?php echo h($invoice);?></p>
        </div>
        <span class="wv-refund-status wv-refund-status-<?php echo $statusKey;?>"><?php echo h($status);?></span>
    </div>

    <div class="wv-refund-card" style="margin-bottom:12px;">
        <h2><?php echo __('Application Process');?></h2>
        <div class="wv-refund-process">
            <?php foreach($processSteps as $step){
                $stepClass = ((int)$step['level'] < $processLevel) ? 'done' : (((int)$step['level'] === $processLevel) ? 'active' : '');
            ?>
                <div class="wv-refund-step <?php echo h($stepClass);?>" data-step="<?php echo (int)$step['level'];?>">
                    <strong><?php echo h($step['title']);?></strong>
                    <span><?php echo h($step['date']);?></span>
                </div>
            <?php } ?>
        </div>
        <div style="color:#64748b;font-size:12px;font-weight:900;margin-top:10px;"><?php echo __('Latest Status Update');?>: <?php echo h($statusUpdatedText);?></div>
    </div>

    <div class="wv-refund-layout">
        <div>
            <div class="wv-refund-card">
                <h2><?php echo __('Refund Summary');?></h2>
                <div class="wv-refund-info">
                    <div class="wv-refund-info-item"><span><?php echo __('Deal Paid Amount');?></span><strong><?php echo h($currency.' '.$this->Number->format((float)$request['requested_amount']));?></strong></div>
                    <div class="wv-refund-info-item"><span><?php echo __('Approved');?></span><strong><?php echo h($currency.' '.$this->Number->format((float)$request['approved_amount']));?></strong></div>
                    <div class="wv-refund-info-item"><span><?php echo __('Paid In Deal');?></span><strong><?php echo h($currency.' '.$this->Number->format((float)$request['total_paid']));?></strong></div>
                    <div class="wv-refund-info-item"><span><?php echo __('Request No');?></span><strong><?php echo h(!empty($request['request_no']) ? $request['request_no'] : '-');?></strong></div>
                    <div class="wv-refund-info-item"><span><?php echo __('Method');?></span><strong><?php echo h(!empty($request['preferred_method']) ? $request['preferred_method'] : '-');?></strong></div>
                    <div class="wv-refund-info-item"><span><?php echo __('Created');?></span><strong><?php echo h($createdText);?></strong></div>
                    <div class="wv-refund-info-item"><span><?php echo __('Status Updated');?></span><strong><?php echo h($statusUpdatedText);?></strong></div>
                    <div class="wv-refund-info-item"><span><?php echo __('Reviewed');?></span><strong><?php echo h($reviewedText);?></strong></div>
                </div>
            </div>

            <div class="wv-refund-card">
                <h2><?php echo __('Reason');?></h2>
                <div class="wv-refund-note"><?php echo h(!empty($request['reason']) ? $request['reason'] : '-');?></div>
                <?php if(!empty($request['client_notes'])){ ?>
                    <br><h2><?php echo __('Client Notes');?></h2>
                    <div class="wv-refund-note"><?php echo h($request['client_notes']);?></div>
                <?php } ?>
                <?php if(!empty($request['admin_notes'])){ ?>
                    <br><h2><?php echo __('Admin Notes');?></h2>
                    <div class="wv-refund-note"><?php echo h($request['admin_notes']);?></div>
                <?php } ?>
            </div>

            <div class="wv-refund-card">
                <h2><?php echo __('Applicant & Payment Details');?></h2>
                <div class="wv-refund-info">
                    <div class="wv-refund-info-item"><span><?php echo __('Name');?></span><strong><?php echo h(!empty($request['applicant_name']) ? $request['applicant_name'] : '-');?></strong></div>
                    <div class="wv-refund-info-item"><span><?php echo __('CNIC');?></span><strong><?php echo h(!empty($request['cnic']) ? $request['cnic'] : '-');?></strong></div>
                    <div class="wv-refund-info-item"><span><?php echo __('CNIC Match');?></span><strong><?php echo h(!empty($request['cnic_match_status']) ? $request['cnic_match_status'] : '-');?></strong></div>
                    <div class="wv-refund-info-item"><span><?php echo __('Mobile');?></span><strong><?php echo h(!empty($request['mobile_no']) ? $request['mobile_no'] : '-');?></strong></div>
                    <div class="wv-refund-info-item"><span><?php echo __('Email');?></span><strong><?php echo h(!empty($request['email']) ? $request['email'] : '-');?></strong></div>
                    <div class="wv-refund-info-item"><span><?php echo __('Account Title');?></span><strong><?php echo h(!empty($request['account_title']) ? $request['account_title'] : '-');?></strong></div>
                    <div class="wv-refund-info-item"><span><?php echo __('Account No');?></span><strong><?php echo h(!empty($request['account_no']) ? $request['account_no'] : '-');?></strong></div>
                    <div class="wv-refund-info-item"><span><?php echo __('IBAN');?></span><strong><?php echo h(!empty($request['iban']) ? $request['iban'] : '-');?></strong></div>
                    <div class="wv-refund-info-item"><span><?php echo __('Bank');?></span><strong><?php echo h(!empty($request['bank_name']) ? $request['bank_name'] : '-');?></strong></div>
                    <div class="wv-refund-info-item"><span><?php echo __('Branch');?></span><strong><?php echo h(!empty($request['bank_branch_city']) ? $request['bank_branch_city'] : '-');?></strong></div>
                </div>
            </div>

            <div class="wv-refund-card">
                <h2><?php echo __('Submitted Documents');?></h2>
                <div class="wv-refund-doc-list">
                    <?php foreach($refundDocs as $docKey => $docLabel){ ?>
                        <div class="wv-refund-doc-item">
                            <strong><?php echo h($docLabel);?></strong>
                            <?php if(!empty($request[$docKey])){ ?>
                                <?php echo $this->Html->link('<span class="fa fa-download"></span> '.__('Download'), array('controller' => 'MyRefundrequests', 'action' => 'download', $rid, $docKey), array('class' => 'btn btn-xs btn-default', 'escape' => false));?>
                            <?php } else { ?>
                                <span class="text-muted"><?php echo __('Missing');?></span>
                            <?php } ?>
                        </div>
                    <?php } ?>
                </div>
            </div>

            <?php if(!empty($refundRow['id'])){ ?>
                <div class="wv-refund-card">
                    <h2><?php echo __('Refund Schedule');?></h2>
                    <div class="wv-refund-info" style="margin-bottom:10px;">
                        <div class="wv-refund-info-item"><span><?php echo __('Refund Status');?></span><strong><?php echo h($clientRefundProgressStatus);?></strong></div>
                        <div class="wv-refund-info-item"><span><?php echo __('Total Paid In Deal');?></span><strong><?php echo h($currency.' '.$this->Number->format((float)$refundRow['total_paid']));?></strong></div>
                        <div class="wv-refund-info-item"><span><?php echo __('Deduction');?></span><strong><?php echo h($this->Number->format((float)$refundRow['deduction_percent']).'%');?></strong></div>
                        <div class="wv-refund-info-item"><span><?php echo __('Net Refund');?></span><strong><?php echo h($currency.' '.$this->Number->format((float)$refundRow['net_refund']));?></strong></div>
                        <div class="wv-refund-info-item"><span><?php echo __('Refund Paid');?></span><strong><?php echo h($currency.' '.$this->Number->format($clientRefundPaidTotal));?></strong></div>
                        <div class="wv-refund-info-item"><span><?php echo __('Pending / Balance');?></span><strong><?php echo h($currency.' '.$this->Number->format($clientRefundBalance));?></strong></div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped wv-refund-schedule-table">
                            <thead>
                                <tr>
                                    <th><?php echo __('Cycle');?></th>
                                    <th><?php echo __('Date');?></th>
                                    <th><?php echo __('Schedule Amount');?></th>
                                    <th><?php echo __('Paid Amount');?></th>
                                    <th><?php echo __('Status');?></th>
                                    <th><?php echo __('Paid Date');?></th>
                                    <th><?php echo __('Remarks');?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($refundScheduleRows)){ ?>
                                    <tr><td colspan="7" class="text-center"><?php echo __('Refund schedule is not generated yet.');?></td></tr>
                                <?php } ?>
                                <?php foreach($refundScheduleRows as $scheduleRow){
                                    $schedule = !empty($scheduleRow['Dealrefundschedule']) ? (array)$scheduleRow['Dealrefundschedule'] : array();
                                    $scheduleStatus = !empty($schedule['status']) ? (string)$schedule['status'] : 'Unpaid';
                                    $scheduleStatusClass = 'wv-refund-pay-status-'.strtolower(preg_replace('/[^a-z0-9]+/i', '-', $scheduleStatus));
                                ?>
                                    <tr>
                                        <td><strong><?php echo !empty($schedule['cycle_no']) ? (int)$schedule['cycle_no'] : '-';?></strong></td>
                                        <td><?php echo h($refundOnlyDate(isset($schedule['scheduled_date']) ? $schedule['scheduled_date'] : ''));?></td>
                                        <td class="wv-refund-money"><?php echo h($currency.' '.$this->Number->format((float)$schedule['amount']));?></td>
                                        <td class="wv-refund-money"><?php echo ((float)$schedule['paid_amount'] > 0) ? h($currency.' '.$this->Number->format((float)$schedule['paid_amount'])) : '-';?></td>
                                        <td><span class="wv-refund-pay-status <?php echo h($scheduleStatusClass);?>"><?php echo h($scheduleStatus);?></span></td>
                                        <td><?php echo h($refundOnlyDate(isset($schedule['paid_date']) ? $schedule['paid_date'] : ''));?></td>
                                        <td><?php echo nl2br(h(!empty($schedule['remarks']) ? $schedule['remarks'] : '-'));?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php } ?>
        </div>

        <div class="wv-refund-card">
            <h2><?php echo __('Actions');?></h2>
            <div class="wv-refund-actions">
                <?php echo $this->Html->link('<span class="fa fa-list"></span> '.__('All Requests'), array('controller' => 'MyRefundrequests', 'action' => 'index'), array('class' => 'btn btn-info', 'escape' => false));?>
                <?php if(!empty($request['attachment'])){ ?>
                    <?php echo $this->Html->link('<span class="fa fa-download"></span> '.__('Download Attachment'), array('controller' => 'MyRefundrequests', 'action' => 'download', $rid), array('class' => 'btn btn-default', 'escape' => false));?>
                <?php } ?>
                <?php if($status === 'Pending' || $status === 'Under Review'){ ?>
                    <?php echo $this->Form->postLink('<span class="fa fa-times"></span> '.__('Cancel Request'), array('controller' => 'MyRefundrequests', 'action' => 'cancel', $rid), array('class' => 'btn btn-danger', 'escape' => false, 'confirm' => __('Are you sure you want to cancel this refund request?')));?>
                <?php } ?>
                <?php if($status === 'Rejected'){ ?>
                    <?php echo $this->Html->link('<span class="fa fa-refresh"></span> '.__('Appeal / Resubmit'), array('controller' => 'MyRefundrequests', 'action' => 'appeal', $rid), array('class' => 'btn btn-warning', 'escape' => false));?>
                <?php } ?>
                <?php if(!empty($request['converted_refund_id'])){ ?>
                    <?php echo $this->Html->link('<span class="fa fa-lock"></span> '.__('Document Vault'), array('controller' => 'MyDocuments', 'action' => 'index'), array('class' => 'btn btn-success', 'escape' => false));?>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
