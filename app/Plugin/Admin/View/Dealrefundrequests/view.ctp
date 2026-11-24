<?php
$row = isset($row) ? (array)$row : array();
$request = !empty($row['Dealrefundrequest']) ? (array)$row['Dealrefundrequest'] : array();
$rid = !empty($request['id']) ? (int)$request['id'] : 0;
$status = !empty($request['status']) ? $request['status'] : 'Pending';
$statusClass = 'wv-refund-status-'.strtolower(preg_replace('/[^a-z0-9]+/i','-',$status));
$defaultDeduction = isset($defaultDeduction) ? (float)$defaultDeduction : 0;
$requestedAmount = !empty($request['requested_amount']) ? (float)$request['requested_amount'] : 0;
$approvedAmount = !empty($request['approved_amount']) ? (float)$request['approved_amount'] : 0;
$totalPaid = !empty($request['total_paid']) ? (float)$request['total_paid'] : 0;
$deductionPercent = isset($request['deduction_percent']) && (float)$request['deduction_percent'] > 0 ? (float)$request['deduction_percent'] : $defaultDeduction;
$suggestedApproved = $approvedAmount > 0 ? $approvedAmount : max(0, $totalPaid - round(($totalPaid * $deductionPercent) / 100, 2));
$suggestedDeductionAmount = max(0, round($totalPaid - $suggestedApproved, 2));
$defaultCycleCount = 1;
$defaultPerCycle = $defaultCycleCount > 0 ? round($suggestedApproved / $defaultCycleCount, 2) : $suggestedApproved;
$todayValue = date('d-M-Y');
$dealLabel = !empty($row['Deal']['invoice_no']) ? $row['Deal']['invoice_no'] : '#'.(!empty($request['deal_id']) ? $request['deal_id'] : '');
$propertyLabel = !empty($row['Property']['name']) ? $row['Property']['name'] : __('Property');
$clientName = !empty($request['applicant_name']) ? $request['applicant_name'] : (!empty($row['Client']['name']) ? $row['Client']['name'] : __('Client'));
$refundDocs = array(
    'client_photo' => __('Client Photo'),
    'cnic_front' => __('CNIC Front'),
    'cnic_back' => __('CNIC Back'),
    'file_front' => __('File Front'),
    'file_back' => __('File Back'),
    'client_signature' => __('Client Signature')
);
$refundDateFormat = $sysDay.$dateSep.$sysMonth.$dateSep.$sysYear.$dateGap.$sysHour.$timeSep.$sysMin.$dateGap.$sysMer;
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
$hasAdminStatusAction = ($status !== 'Converted To Refund');
?>
<style type="text/css">
.wv-refund-view{color:#07152a;}
.wv-refund-view-head{align-items:center;background:linear-gradient(135deg,#07152a 0%,#123252 68%,#0f766e 130%);border-radius:8px;color:#fff;display:flex;gap:12px;justify-content:space-between;margin-bottom:14px;padding:18px 20px;}
.wv-refund-view-head h2{color:#fff!important;font-size:24px;font-weight:900;margin:0 0 4px;}
.wv-refund-view-head p{color:#dbeafe!important;font-size:13px;font-weight:800;margin:0;}
.wv-refund-status{border-radius:999px;display:inline-block;font-size:11px;font-weight:900;padding:7px 10px;text-transform:uppercase;white-space:nowrap;}
.wv-refund-status-pending{background:#fff1c2;color:#8a6100;}
.wv-refund-status-under-review{background:#e7f0ff;color:#174ea6;}
.wv-refund-status-approved{background:#dff7e8;color:#08753b;}
.wv-refund-status-rejected{background:#ffe4e6;color:#b91c1c;}
.wv-refund-status-cancelled{background:#eef2f7;color:#475569;}
.wv-refund-status-converted-to-refund{background:#dcfce7;color:#166534;}
.wv-refund-view-grid{display:grid;gap:14px;grid-template-columns:minmax(0,1fr) 360px;}
.wv-refund-card{background:#fff;border:1px solid #dbe5ef;border-radius:8px;box-shadow:0 10px 24px rgba(7,21,42,.06);margin-bottom:14px;padding:14px;}
.wv-refund-card h3{border-bottom:1px solid #e2eaf4;color:#07152a;font-size:16px;font-weight:900;margin:0 0 12px;padding-bottom:8px;text-transform:uppercase;}
.wv-refund-info{display:grid;gap:9px;grid-template-columns:repeat(3,minmax(0,1fr));}
.wv-refund-info-item{background:#f8fbff;border:1px solid #e2eaf4;border-radius:8px;padding:10px;}
.wv-refund-info-item span{color:#64748b;display:block;font-size:10px;font-weight:900;text-transform:uppercase;}
.wv-refund-info-item strong{color:#07152a;display:block;font-size:13px;font-weight:900;margin-top:4px;word-break:break-word;}
.wv-refund-note{background:#fbfdff;border:1px solid #e2eaf4;border-radius:8px;color:#334155;font-size:13px;font-weight:700;line-height:1.55;padding:10px;white-space:pre-line;}
.wv-refund-action-form{border-top:1px solid #e2eaf4;margin-top:12px;padding-top:12px;}
.wv-refund-action-form label{color:#334155;font-size:11px;font-weight:900;text-transform:uppercase;}
.wv-refund-action-form .form-control{border-radius:7px;font-weight:800;}
.wv-refund-calc-grid{display:grid;gap:8px;grid-template-columns:repeat(2,minmax(0,1fr));margin-bottom:10px;}
.wv-refund-calc-grid .full{grid-column:1/-1;}
.wv-refund-calc-box{background:#f8fbff;border:1px solid #e2eaf4;border-radius:8px;padding:9px;}
.wv-refund-calc-box span{color:#64748b;display:block;font-size:10px;font-weight:900;text-transform:uppercase;}
.wv-refund-calc-box strong{color:#07152a;display:block;font-size:15px;font-weight:900;margin-top:4px;word-break:break-word;}
.wv-refund-calc-box.net strong{color:#08753b;font-size:18px;}
.wv-refund-calc-box.deduct strong{color:#b45309;}
.wv-refund-check{align-items:center;background:#f8fbff;border:1px solid #e2eaf4;border-radius:8px;color:#07152a;display:flex;font-size:12px;font-weight:900;gap:7px;margin:8px 0 12px;padding:9px;}
.wv-refund-check input{margin:0;}
.wv-refund-actions{display:flex;flex-direction:column;gap:8px;}
.wv-refund-actions .btn{border-radius:7px;font-weight:900;text-align:left;text-transform:uppercase;}
.wv-refund-doc-list{display:grid;gap:8px;grid-template-columns:repeat(2,minmax(0,1fr));}
.wv-refund-doc-item{align-items:center;background:#f8fbff;border:1px solid #e2eaf4;border-radius:8px;display:flex;gap:8px;justify-content:space-between;padding:9px;}
.wv-refund-doc-item strong{color:#07152a;font-size:12px;font-weight:900;}
.wv-refund-doc-item .btn{border-radius:7px;font-size:11px;font-weight:900;}
.wv-refund-process{display:grid;gap:8px;grid-template-columns:repeat(4,minmax(0,1fr));}
.wv-refund-step{background:#f8fbff;border:1px solid #dbe5ef;border-radius:8px;padding:10px;position:relative;}
.wv-refund-step:before{background:#cbd5e1;border-radius:999px;color:#fff;content:attr(data-step);display:block;font-size:11px;font-weight:900;height:26px;line-height:26px;margin-bottom:7px;text-align:center;width:26px;}
.wv-refund-step strong{color:#07152a;display:block;font-size:12px;font-weight:900;text-transform:uppercase;}
.wv-refund-step span{color:#64748b;display:block;font-size:11px;font-weight:800;margin-top:4px;}
.wv-refund-step.done{background:#ecfdf5;border-color:#a7f3d0;}
.wv-refund-step.done:before{background:#059669;}
.wv-refund-step.active{background:#eff6ff;border-color:#93c5fd;box-shadow:0 8px 18px rgba(37,99,235,.12);}
.wv-refund-step.active:before{background:#2563eb;}
.wv-refund-action-dropdown .btn{border-radius:7px;font-weight:900;text-align:left;text-transform:uppercase;width:100%;}
.wv-refund-action-dropdown .dropdown-menu{border-radius:8px;min-width:230px;padding:6px;}
.wv-refund-action-dropdown .dropdown-menu li a{font-size:12px;font-weight:900;padding:8px 10px;text-transform:uppercase;}
@media(max-width:991px){.wv-refund-view-grid{grid-template-columns:1fr}.wv-refund-info{grid-template-columns:repeat(2,minmax(0,1fr));}.wv-refund-view-head{display:block}.wv-refund-status{margin-top:10px;}}
@media(max-width:520px){.wv-refund-info,.wv-refund-doc-list,.wv-refund-calc-grid,.wv-refund-process{grid-template-columns:1fr}.wv-refund-calc-grid .full{grid-column:auto}.wv-refund-actions .btn{text-align:center;}}
</style>

<div class="wv-refund-view">
    <?php echo $this->Session->flash();?>

    <div class="wv-refund-view-head">
        <div>
            <h2><?php echo __('Refund Application');?> #<?php echo $rid;?></h2>
            <p><?php echo h($clientName);?> | <?php echo h($dealLabel);?> | <?php echo h($propertyLabel);?></p>
        </div>
        <span class="wv-refund-status <?php echo h($statusClass);?>"><?php echo h($status);?></span>
    </div>

    <div class="wv-refund-card">
        <h3><?php echo __('Application Process');?></h3>
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

    <div class="wv-refund-view-grid">
        <div>
            <div class="wv-refund-card">
                <h3><?php echo __('Refund Summary');?></h3>
                <div class="wv-refund-info">
                    <div class="wv-refund-info-item"><span><?php echo __('Deal Paid Amount');?></span><strong><?php echo h($currency.' '.$this->Number->format($requestedAmount));?></strong></div>
                    <div class="wv-refund-info-item"><span><?php echo __('Approved / Net Refund');?></span><strong><?php echo h($currency.' '.$this->Number->format($approvedAmount));?></strong></div>
                    <div class="wv-refund-info-item"><span><?php echo __('Paid In Deal');?></span><strong><?php echo h($currency.' '.$this->Number->format($totalPaid));?></strong></div>
                    <div class="wv-refund-info-item"><span><?php echo __('Request No');?></span><strong><?php echo h(!empty($request['request_no']) ? $request['request_no'] : '-');?></strong></div>
                    <div class="wv-refund-info-item"><span><?php echo __('Deduction %');?></span><strong><?php echo h($deductionPercent);?></strong></div>
                    <div class="wv-refund-info-item"><span><?php echo __('Deduction Amount');?></span><strong><?php echo h($currency.' '.$this->Number->format($suggestedDeductionAmount));?></strong></div>
                    <div class="wv-refund-info-item"><span><?php echo __('Preferred Method');?></span><strong><?php echo h(!empty($request['preferred_method']) ? $request['preferred_method'] : '-');?></strong></div>
                    <div class="wv-refund-info-item"><span><?php echo __('Created');?></span><strong><?php echo h($createdText);?></strong></div>
                    <div class="wv-refund-info-item"><span><?php echo __('Status Updated');?></span><strong><?php echo h($statusUpdatedText);?></strong></div>
                    <div class="wv-refund-info-item"><span><?php echo __('Agency');?></span><strong><?php echo h(!empty($row['Project']['name']) ? $row['Project']['name'] : '-');?></strong></div>
                    <div class="wv-refund-info-item"><span><?php echo __('Reviewed By');?></span><strong><?php echo h(!empty($row['ReviewedBy']['name']) ? $row['ReviewedBy']['name'] : '-');?></strong></div>
                    <div class="wv-refund-info-item"><span><?php echo __('Reviewed At');?></span><strong><?php echo h($reviewedText);?></strong></div>
                </div>
            </div>

            <div class="wv-refund-card">
                <h3><?php echo __('Applicant Details');?></h3>
                <div class="wv-refund-info">
                    <div class="wv-refund-info-item"><span><?php echo __('Name');?></span><strong><?php echo h(!empty($request['applicant_name']) ? $request['applicant_name'] : '-');?></strong></div>
                    <div class="wv-refund-info-item"><span><?php echo __('Father Name');?></span><strong><?php echo h(!empty($request['father_name']) ? $request['father_name'] : '-');?></strong></div>
                    <div class="wv-refund-info-item"><span><?php echo __('CNIC');?></span><strong><?php echo h(!empty($request['cnic']) ? $request['cnic'] : '-');?></strong></div>
                    <div class="wv-refund-info-item"><span><?php echo __('CNIC Front Read');?></span><strong><?php echo h(!empty($request['cnic_front_number']) ? $request['cnic_front_number'] : '-');?></strong></div>
                    <div class="wv-refund-info-item"><span><?php echo __('CNIC Match');?></span><strong><?php echo h(!empty($request['cnic_match_status']) ? $request['cnic_match_status'] : '-');?></strong></div>
                    <div class="wv-refund-info-item"><span><?php echo __('Passport');?></span><strong><?php echo h(!empty($request['passport_no']) ? $request['passport_no'] : '-');?></strong></div>
                    <div class="wv-refund-info-item"><span><?php echo __('Mobile');?></span><strong><?php echo h(!empty($request['mobile_no']) ? $request['mobile_no'] : '-');?></strong></div>
                    <div class="wv-refund-info-item"><span><?php echo __('Email');?></span><strong><?php echo h(!empty($request['email']) ? $request['email'] : '-');?></strong></div>
                    <div class="wv-refund-info-item"><span><?php echo __('Account Title');?></span><strong><?php echo h(!empty($request['account_title']) ? $request['account_title'] : '-');?></strong></div>
                    <div class="wv-refund-info-item"><span><?php echo __('Account No');?></span><strong><?php echo h(!empty($request['account_no']) ? $request['account_no'] : '-');?></strong></div>
                    <div class="wv-refund-info-item"><span><?php echo __('IBAN');?></span><strong><?php echo h(!empty($request['iban']) ? $request['iban'] : '-');?></strong></div>
                    <div class="wv-refund-info-item"><span><?php echo __('Bank');?></span><strong><?php echo h(!empty($request['bank_name']) ? $request['bank_name'] : '-');?></strong></div>
                    <div class="wv-refund-info-item"><span><?php echo __('Branch / City');?></span><strong><?php echo h(!empty($request['bank_branch_city']) ? $request['bank_branch_city'] : '-');?></strong></div>
                    <div class="wv-refund-info-item"><span><?php echo __('Wallet');?></span><strong><?php echo h(trim((!empty($request['wallet_name']) ? $request['wallet_name'] : '').' '.(!empty($request['wallet_no']) ? $request['wallet_no'] : '')) ?: '-');?></strong></div>
                </div>
                <?php if(!empty($request['address'])){ ?>
                    <br><div class="wv-refund-note"><?php echo h($request['address']);?></div>
                <?php } ?>
            </div>

            <div class="wv-refund-card">
                <h3><?php echo __('Reason & Notes');?></h3>
                <div class="wv-refund-note"><?php echo h(!empty($request['reason']) ? $request['reason'] : '-');?></div>
                <?php if(!empty($request['client_notes'])){ ?>
                    <br><h3><?php echo __('Client Notes');?></h3>
                    <div class="wv-refund-note"><?php echo h($request['client_notes']);?></div>
                <?php } ?>
                <?php if(!empty($request['admin_notes'])){ ?>
                    <br><h3><?php echo __('Admin Notes');?></h3>
                    <div class="wv-refund-note"><?php echo h($request['admin_notes']);?></div>
                <?php } ?>
            </div>

            <div class="wv-refund-card">
                <h3><?php echo __('Submitted Documents');?></h3>
                <div class="wv-refund-doc-list">
                    <?php foreach($refundDocs as $docKey => $docLabel){ ?>
                        <div class="wv-refund-doc-item">
                            <strong><?php echo h($docLabel);?></strong>
                            <?php if(!empty($request[$docKey])){ ?>
                                <?php echo $this->Html->link('<span class="fa fa-download"></span> '.__('Download'), array('controller'=>'Dealrefundrequests','action'=>'download',$rid,$docKey), array('escape'=>false,'class'=>'btn btn-xs btn-default'));?>
                            <?php } else { ?>
                                <span class="text-muted"><?php echo __('Missing');?></span>
                            <?php } ?>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>

        <div>
            <div class="wv-refund-card">
                <h3><?php echo __('Actions');?></h3>
                <div class="wv-refund-actions">
                    <?php echo $this->Html->link('<span class="fa fa-list"></span> '.__('All Applications'), array('controller'=>'Dealrefundrequests','action'=>'index'), array('escape'=>false,'class'=>'btn btn-info'));?>
                    <?php echo $this->Html->link('<span class="fa fa-undo"></span> '.__('Deal Refunds'), array('controller'=>'Dealrefunds','action'=>'index'), array('escape'=>false,'class'=>'btn btn-default'));?>
                    <?php if($hasAdminStatusAction){ ?>
                        <div class="btn-group wv-refund-action-dropdown">
                            <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
                                <span class="fa fa-cogs"></span> <?php echo __('Application Actions');?> <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-right">
                                <?php if(!in_array($status, array('Cancelled','Rejected','Converted To Refund'), true)){ ?>
                                    <li><?php echo $this->Html->link('<span class="fa fa-search"></span> '.__('Mark Under Review'), array('controller'=>'Dealrefundrequests','action'=>'reviewform',$rid), array('escape'=>false));?></li>
                                    <li><?php echo $this->Html->link('<span class="fa fa-check"></span> '.__('Approve Request'), array('controller'=>'Dealrefundrequests','action'=>'approveform',$rid), array('escape'=>false));?></li>
                                <?php } ?>
                                <?php if($status !== 'Converted To Refund'){ ?>
                                    <li><?php echo $this->Html->link('<span class="fa fa-times"></span> '.__('Reject Request'), array('controller'=>'Dealrefundrequests','action'=>'rejectform',$rid), array('escape'=>false));?></li>
                                <?php } ?>
                                <?php if($status === 'Approved' && empty($request['converted_refund_id'])){ ?>
                                    <li role="separator" class="divider"></li>
                                    <li><?php echo $this->Html->link('<span class="fa fa-share"></span> '.__('Create Deal Refund'), array('controller'=>'Dealrefundrequests','action'=>'convertform',$rid), array('escape'=>false));?></li>
                                <?php } ?>
                            </ul>
                        </div>
                    <?php } ?>
                    <?php if(!empty($request['attachment'])){ ?>
                        <?php echo $this->Html->link('<span class="fa fa-download"></span> '.__('Download Attachment'), array('controller'=>'Dealrefundrequests','action'=>'download',$rid), array('escape'=>false,'class'=>'btn btn-primary'));?>
                    <?php } ?>
                    <?php if(!empty($request['converted_refund_id'])){ ?>
                        <?php echo $this->Html->link('<span class="fa fa-check"></span> '.__('Open Converted Refund'), array('controller'=>'Dealrefunds','action'=>'view',(int)$request['converted_refund_id']), array('escape'=>false,'class'=>'btn btn-success'));?>
                    <?php } ?>
                </div>
            </div>

            <?php if(false && !in_array($status, array('Cancelled','Rejected','Converted To Refund'), true)){ ?>
                <div class="wv-refund-card">
                    <h3><?php echo __('Mark Under Review');?></h3>
                    <?php echo $this->Form->create('Dealrefundrequest', array('url'=>array('controller'=>'Dealrefundrequests','action'=>'review',$rid), 'class'=>'wv-refund-action-form'));?>
                    <label><?php echo __('Admin Notes');?></label>
                    <?php echo $this->Form->textarea('admin_notes', array('class'=>'form-control','rows'=>3,'value'=>!empty($request['admin_notes']) ? $request['admin_notes'] : ''));?>
                    <br><button type="submit" class="btn btn-info btn-block"><span class="fa fa-search"></span> <?php echo __('Under Review');?></button>
                    <?php echo $this->Form->end();?>
                </div>

                <div class="wv-refund-card">
                    <h3><?php echo __('Approve Request');?></h3>
                    <?php echo $this->Form->create('Dealrefundrequest', array('url'=>array('controller'=>'Dealrefundrequests','action'=>'approve',$rid), 'class'=>'wv-refund-action-form'));?>
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
                        <?php echo $this->Form->textarea('admin_notes', array('class'=>'form-control','rows'=>3,'value'=>!empty($request['admin_notes']) ? $request['admin_notes'] : ''));?>
                    </div>
                    <button type="submit" class="btn btn-success btn-block"><span class="fa fa-check"></span> <?php echo __('Approve');?></button>
                    <?php echo $this->Form->end();?>
                </div>

                <div class="wv-refund-card">
                    <h3><?php echo __('Reject Request');?></h3>
                    <?php echo $this->Form->create('Dealrefundrequest', array('url'=>array('controller'=>'Dealrefundrequests','action'=>'reject',$rid), 'class'=>'wv-refund-action-form'));?>
                    <label><?php echo __('Rejection Notes');?></label>
                    <?php echo $this->Form->textarea('admin_notes', array('class'=>'form-control','rows'=>3,'value'=>!empty($request['admin_notes']) ? $request['admin_notes'] : ''));?>
                    <br><button type="submit" class="btn btn-danger btn-block" onclick="return confirm('<?php echo h(__('Reject this refund application?'));?>');"><span class="fa fa-times"></span> <?php echo __('Reject');?></button>
                    <?php echo $this->Form->end();?>
                </div>
            <?php } ?>

            <?php if(false && $status === 'Approved' && empty($request['converted_refund_id'])){ ?>
                <div class="wv-refund-card">
                    <h3><?php echo __('Create Deal Refund Summary');?></h3>
                    <p class="text-muted" style="font-weight:800;"><?php echo __('Review deduction and schedule settings before creating the deal refund. Tick finalize to generate the refund schedule immediately.');?></p>
                    <?php echo $this->Form->create('Dealrefundrequest', array('url'=>array('controller'=>'Dealrefundrequests','action'=>'convert',$rid), 'class'=>'wv-refund-action-form', 'id'=>'RefundConvertForm'));?>
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
                    <button type="submit" class="btn btn-warning btn-block" onclick="return confirm('<?php echo h(__('Create this deal refund with the shown deduction and schedule summary?'));?>');"><span class="fa fa-share"></span> <?php echo __('Create Deal Refund');?></button>
                    <?php echo $this->Form->end();?>
                </div>
            <?php } ?>
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
