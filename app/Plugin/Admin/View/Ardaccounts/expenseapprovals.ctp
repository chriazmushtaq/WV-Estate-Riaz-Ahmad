<?php
$isSuper = !empty($actor['is_super']);
$workflowEnabled = !empty($approvalConfig['enabled']);
$currentActorId = !empty($actor['user_id']) ? (int)$actor['user_id'] : 0;
?>
<style>
.ard-approval-head{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:14px}.ard-approval-head h2{color:#0b2344;font-weight:900;margin:0}.ard-approval-head p{color:#64748b;font-weight:700;margin:4px 0 0}.ard-approval-actions{display:flex;gap:7px;flex-wrap:wrap}
.ard-flow-banner{border-left:4px solid #16a34a;background:#f0fdf4;color:#166534;font-weight:800;margin-bottom:14px;padding:10px 12px}.ard-flow-banner.off{border-left-color:#dc2626;background:#fef2f2;color:#b91c1c}
.ard-role-warning{background:#fff7ed;border-left:4px solid #f97316;color:#9a3412;font-weight:800;margin:-4px 0 14px;padding:10px 12px}
.ard-approval-counts{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:9px;margin-bottom:14px}.ard-approval-count{background:#fff;border:1px solid #dbe6f3;border-top:4px solid #d97706;border-radius:8px;padding:10px}.ard-approval-count.ceo{border-top-color:#7c3aed}.ard-approval-count.approved{border-top-color:#16a34a}.ard-approval-count.rejected{border-top-color:#dc2626}.ard-approval-count small{display:block;color:#64748b;font-size:10px;font-weight:900;text-transform:uppercase}.ard-approval-count strong{color:#102a43;font-size:20px}
.ard-approval-filter{display:flex;gap:7px;flex-wrap:wrap;margin-bottom:10px}.ard-approval-filter .btn.active{background:#0b2344!important;color:#fff!important}
.ard-approval-table th{background:#0b2344!important;color:#fff!important;font-size:10px;text-transform:uppercase;vertical-align:middle!important;white-space:nowrap}.ard-approval-table td{font-size:11px;vertical-align:middle!important}.ard-approval-status{display:inline-block;border-radius:999px;font-size:9px;font-weight:900;padding:5px 9px;text-transform:uppercase;white-space:nowrap}.ard-approval-status.pending-manager{background:#fef3c7;color:#92400e}.ard-approval-status.pending-ceo{background:#ede9fe;color:#6d28d9}.ard-approval-status.approved{background:#dcfce7;color:#166534}.ard-approval-status.rejected{background:#fee2e2;color:#b91c1c}.ard-money{font-weight:900;white-space:nowrap}
.ard-request-actions{display:flex;gap:5px;flex-wrap:wrap}.ard-reject-form{display:flex;gap:4px;margin-top:5px}.ard-reject-form .form-control{height:30px;min-width:150px;font-size:10px}.ard-timeline-row>td{background:#f8fbff!important;padding:10px 14px!important}.ard-timeline{display:flex;align-items:flex-start;gap:0;overflow-x:auto}.ard-timeline-step{min-width:180px;position:relative;padding:0 24px 0 0}.ard-timeline-step:after{background:#cbd5e1;content:"";height:2px;position:absolute;right:4px;top:10px;width:16px}.ard-timeline-step:last-child:after{display:none}.ard-timeline-dot{background:#94a3b8;border-radius:50%;display:inline-block;height:20px;margin-right:6px;vertical-align:top;width:20px}.ard-timeline-step.done .ard-timeline-dot{background:#16a34a}.ard-timeline-step.rejected .ard-timeline-dot{background:#dc2626}.ard-timeline-copy{display:inline-block;max-width:145px}.ard-timeline-copy strong{color:#0f172a;display:block;font-size:10px}.ard-timeline-copy small{color:#64748b;display:block;font-size:9px;line-height:1.4}.ard-empty{color:#64748b;padding:24px!important;text-align:center}
@media(max-width:850px){.ard-approval-head{align-items:flex-start;flex-direction:column}.ard-approval-counts{grid-template-columns:repeat(2,1fr)}}
</style>
<div class="page-title"><div class="title-env"><h1 class="title"><?php echo __('Expense Payment Approvals');?></h1></div></div>
<div class="panel"><div class="panel-body">
<?php echo $this->Session->flash();?>
<div class="ard-approval-head">
    <div><h2><?php echo __('Accountant → Manager → CEO');?></h2><p><?php echo __('Expense requests are posted to Accounts only after final CEO approval.');?></p></div>
    <div class="ard-approval-actions">
        <?php if($isSuper):?><?php echo $this->Html->link('<i class="fa fa-cog"></i> '.__('Workflow Settings'),array('controller'=>'Ardaccounts','action'=>'settings',$currentProjectId,'?'=>array('tab'=>'general')),array('class'=>'btn btn-default','escape'=>false));?><?php endif;?>
        <?php if($workflowEnabled && $canCreate):?><?php echo $this->Html->link('<i class="fa fa-plus"></i> '.__('Create Expense Request'),array('action'=>'transactions',$currentProjectId,'?'=>array('kind'=>'expense')),array('class'=>'btn btn-danger','escape'=>false));?><?php endif;?>
        <?php echo $this->Html->link('<i class="fa fa-file-text-o"></i> '.__('Vouchers'),array('action'=>'transactions',$currentProjectId),array('class'=>'btn btn-primary','escape'=>false));?>
    </div>
</div>

<?php if($isSuper && count($projectOptions)>1):?>
<div class="ard-approval-filter">
    <?php foreach($projectOptions as $projectId=>$projectName):?><?php echo $this->Html->link(h($projectName),array('action'=>'expenseapprovals',(int)$projectId),array('class'=>'btn btn-xs '.((int)$projectId===(int)$currentProjectId?'btn-primary':'btn-default'),'escape'=>false));?><?php endforeach;?>
</div>
<?php endif;?>

<div class="ard-flow-banner<?php echo $workflowEnabled?'':' off';?>"><i class="fa <?php echo $workflowEnabled?'fa-check-circle':'fa-ban';?>"></i> <?php echo $workflowEnabled?__('Approval workflow is active for this agency. New manual expense payments require Manager verification and CEO approval.'):__('Approval workflow is off for this agency. New expense vouchers continue using direct posting.');?></div>
<?php if(empty($roleReadiness['accountants'])||empty($roleReadiness['managers'])||(empty($roleReadiness['ceos'])&&empty($approvalConfig['ceo_user_id']))):?><div class="ard-role-warning"><i class="fa fa-exclamation-triangle"></i> <?php echo __('Workflow role setup incomplete.');?> <?php if(empty($roleReadiness['accountants'])):?><?php echo __('No Accountant is assigned.');?> <?php endif;?><?php if(empty($roleReadiness['managers'])):?><?php echo __('No Manager is assigned.');?> <?php endif;?><?php if(empty($roleReadiness['ceos'])&&empty($approvalConfig['ceo_user_id'])):?><?php echo __('No CEO/Agency Owner is assigned.');?><?php endif;?></div><?php endif;?>

<div class="ard-approval-counts">
    <div class="ard-approval-count"><small><?php echo __('Pending Manager');?></small><strong><?php echo (int)$statusCounts['Pending Manager'];?></strong></div>
    <div class="ard-approval-count ceo"><small><?php echo __('Pending CEO');?></small><strong><?php echo (int)$statusCounts['Pending CEO'];?></strong></div>
    <div class="ard-approval-count approved"><small><?php echo __('Approved & Posted');?></small><strong><?php echo (int)$statusCounts['Approved'];?></strong></div>
    <div class="ard-approval-count rejected"><small><?php echo __('Rejected');?></small><strong><?php echo (int)$statusCounts['Rejected'];?></strong></div>
</div>

<div class="ard-approval-filter">
    <?php echo $this->Html->link(__('All'),array('action'=>'expenseapprovals',$currentProjectId),array('class'=>'btn btn-xs btn-default '.($statusFilter===''?'active':'')));?>
    <?php foreach(array('Pending Manager','Pending CEO','Approved','Rejected') as $filterStatus):?><?php echo $this->Html->link(h($filterStatus),array('action'=>'expenseapprovals',$currentProjectId,'?'=>array('status'=>$filterStatus)),array('class'=>'btn btn-xs btn-default '.($statusFilter===$filterStatus?'active':''),'escape'=>false));?><?php endforeach;?>
</div>

<div class="table-responsive"><table class="table table-bordered table-striped ard-approval-table">
<thead><tr><th><?php echo __('Request');?></th><th><?php echo __('Payment Date');?></th><th><?php echo __('Accountant');?></th><th><?php echo __('Payment From');?></th><th><?php echo __('Expense Account');?></th><th><?php echo __('Amount');?></th><th><?php echo __('Status');?></th><th><?php echo __('Current Since');?></th><th><?php echo __('Action');?></th></tr></thead>
<tbody>
<?php if(empty($requests)):?><tr><td colspan="9" class="ard-empty"><?php echo __('No expense approval requests found.');?></td></tr><?php endif;?>
<?php foreach((array)$requests as $row):$r=$row['Ardexpenseapprovalrequest'];$requestId=(int)$r['id'];$statusClass=strtolower(str_replace(' ','-',$r['status']));$history=isset($historyMap[$requestId])?$historyMap[$requestId]:array();$canManagerReview=$canVerify&&in_array($r['status'],array('Pending Manager','Pending CEO'),true)&&empty($r['transaction_id'])&&($isSuper||(int)$r['created_by']!==$currentActorId);$canManagerUnverify=$canManagerReview&&$r['status']==='Pending CEO'&&empty($r['ceo_approved_by']);$canRowVerify=$canVerify&&$r['status']==='Pending Manager'&&(int)$r['created_by']!==$currentActorId;$canRowApprove=$canFinalApprove&&$r['status']==='Pending CEO'&&(int)$r['created_by']!==$currentActorId&&(int)$r['manager_verified_by']!==$currentActorId;$canRowReject=$canRowVerify||$canRowApprove;$currentSince=$r['status']==='Pending CEO'?$r['manager_verified_at']:($r['status']==='Approved'?$r['ceo_approved_at']:($r['status']==='Rejected'?$r['rejected_at']:$r['submitted_at']));?>
<tr>
    <td><strong><?php echo h($r['ref_no']);?></strong><br><small>#<?php echo $requestId;?> · <?php echo h($r['description']);?></small></td>
    <td><?php echo h(date('d-M-Y',strtotime($r['entry_date'])));?></td>
    <td><?php echo h(!empty($row['Creator']['name'])?$row['Creator']['name']:(!empty($row['Creator']['username'])?$row['Creator']['username']:'#'.$r['created_by']));?><br><small><?php echo !empty($r['submitted_at']) ? h(date('d-M-Y h:i A',strtotime($r['submitted_at']))) : '-';?></small></td>
    <td><?php echo h(!empty($row['SourceLedger']['name'])?$row['SourceLedger']['name']:'#'.$r['ledger_id']);?></td>
    <td><?php echo h(!empty($row['ExpenseLedger']['name'])?$row['ExpenseLedger']['name']:'#'.$r['expense_ledger_id']);?></td>
    <td class="ard-money"><?php echo h($currency.' '.number_format((float)$r['amount'],2));?></td>
    <td><span class="ard-approval-status <?php echo h($statusClass);?>"><?php echo h($r['status']);?></span></td>
    <td><?php echo !empty($currentSince) ? h(date('d-M-Y h:i A',strtotime($currentSince))) : '-';?></td>
    <td>
        <div class="ard-request-actions">
            <?php if($canManagerReview):?><?php echo $this->Html->link('<i class="fa fa-pencil"></i> '.__('Manager Edit'),array('action'=>'reviewexpenseapproval',$requestId),array('class'=>'btn btn-xs btn-primary','escape'=>false,'title'=>__('Edit details; amount remains locked')));?><?php endif;?>
            <?php if($canRowVerify):?><?php echo $this->Form->postLink('<i class="fa fa-check"></i> '.__('Manager Verify'),array('action'=>'verifyexpenseapproval',$requestId),array('class'=>'btn btn-xs btn-warning','escape'=>false,'confirm'=>__('Verify and send this request to CEO?')));?><?php endif;?>
            <?php if($canManagerUnverify):?><?php echo $this->Form->postLink('<i class="fa fa-undo"></i> '.__('Unapprove'),array('action'=>'unverifyexpenseapproval',$requestId),array('class'=>'btn btn-xs btn-danger','escape'=>false,'confirm'=>__('Withdraw Manager approval and return this request to Pending Manager?')));?><?php endif;?>
            <?php if($canRowApprove):?><?php echo $this->Form->postLink('<i class="fa fa-check-circle"></i> '.__('CEO Approve'),array('action'=>'approveexpenseapproval',$requestId),array('class'=>'btn btn-xs btn-success','escape'=>false,'confirm'=>__('Final approve and post this expense voucher?')));?><?php endif;?>
            <?php echo $this->Html->link('<i class="fa fa-print"></i>',array('action'=>'expenseapprovalvoucher',$requestId),array('class'=>'btn btn-xs btn-info','escape'=>false,'target'=>'_blank','title'=>__('Print Approval Voucher')));?>
        </div>
        <?php if($canRowReject):?>
        <?php echo $this->Form->create('ApprovalAction',array('url'=>array('action'=>'rejectexpenseapproval',$requestId),'class'=>'ard-reject-form'));?>
        <?php echo $this->Form->text('comment',array('class'=>'form-control','required'=>true,'placeholder'=>__('Required rejection reason')));?><button type="submit" class="btn btn-xs btn-danger" title="<?php echo h(__('Reject'));?>"><i class="fa fa-times"></i></button><?php echo $this->Form->end();?>
        <?php endif;?>
    </td>
</tr>
<tr class="ard-timeline-row"><td colspan="9"><div class="ard-timeline">
    <?php if(empty($history)):?><span class="text-muted"><?php echo __('No timeline entries.');?></span><?php endif;?>
    <?php foreach($history as $historyRow):$h=$historyRow['Ardexpenseapprovalhistory'];$isRejected=$h['to_status']==='Rejected';?><div class="ard-timeline-step <?php echo $isRejected?'rejected':'done';?>"><span class="ard-timeline-dot"></span><span class="ard-timeline-copy"><strong><?php echo h($h['action']);?></strong><small><?php echo h(!empty($historyRow['Actor']['name'])?$historyRow['Actor']['name']:(!empty($historyRow['Actor']['username'])?$historyRow['Actor']['username']:'#'.$h['actor_user_id']));?></small><small><?php echo !empty($h['created']) ? h(date('d-M-Y H:i',strtotime($h['created']))) : '-';?></small><?php if(trim((string)$h['comment'])!==''):?><small><?php echo h($h['comment']);?></small><?php endif;?></span></div><?php endforeach;?>
    <?php if($r['status']==='Pending Manager'):?><div class="ard-timeline-step"><span class="ard-timeline-dot"></span><span class="ard-timeline-copy"><strong><?php echo __('Manager Verification');?></strong><small><?php echo __('Pending');?></small></span></div><?php endif;?>
    <?php if(in_array($r['status'],array('Pending Manager','Pending CEO'),true)):?><div class="ard-timeline-step"><span class="ard-timeline-dot"></span><span class="ard-timeline-copy"><strong><?php echo __('CEO Final Approval');?></strong><small><?php echo $r['status']==='Pending CEO'?__('Pending'):__('Waiting for Manager');?></small></span></div><?php endif;?>
</div></td></tr>
<?php endforeach;?>
</tbody></table></div>
<div class="text-center"><ul class="pagination"><?php echo $this->Paginator->prev('&laquo;',array('tag'=>'li','escape'=>false),'<a>&laquo;</a>',array('tag'=>'li','class'=>'disabled','escape'=>false));?><?php echo $this->Paginator->numbers(array('tag'=>'li','currentTag'=>'a','separator'=>'','currentClass'=>'active'));?><?php echo $this->Paginator->next('&raquo;',array('tag'=>'li','escape'=>false),'<a>&raquo;</a>',array('tag'=>'li','class'=>'disabled','escape'=>false));?></ul></div>
</div></div>
