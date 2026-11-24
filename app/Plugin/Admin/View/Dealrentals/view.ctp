<?php $r=$rental['DealRental']; ?>
<style>
.wv-rental-summary{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-bottom:14px}
.wv-rental-card{background:#fff;border:1px solid #dbe6f3;border-radius:12px;padding:13px;box-shadow:0 8px 22px rgba(15,35,58,.06)}
.wv-rental-card small{display:block;text-transform:uppercase;font-weight:900;color:#64748b}.wv-rental-card strong{font-size:17px;color:#08213f}
.wv-rental-table th{background:#0b2344!important;color:#fff!important;text-transform:uppercase;font-size:12px}
.wv-rental-ledger-summary{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:10px;margin:14px 0}
.wv-rental-ledger-summary .wv-rental-card strong{display:block;font-size:15px;margin-top:3px}
.wv-rental-tabs{border-bottom:2px solid #dbe6f3;margin-top:16px}
.wv-rental-tabs>li>a{border-radius:7px 7px 0 0;color:#40536c;font-weight:800}
.wv-rental-tabs>li.active>a,.wv-rental-tabs>li.active>a:hover{background:#0b2344!important;border-color:#0b2344!important;color:#fff!important}
.wv-rental-tab-content{padding-top:14px}
.wv-rental-status{border-radius:999px;display:inline-block;font-size:10px;font-weight:900;padding:5px 9px;text-transform:uppercase;white-space:nowrap}
.wv-rental-status-paid{background:#dcfce7;color:#166534}.wv-rental-status-partially-paid{background:#fef3c7;color:#92400e}.wv-rental-status-overdue{background:#fee2e2;color:#b91c1c}.wv-rental-status-due{background:#dbeafe;color:#1d4ed8}.wv-rental-status-upcoming{background:#e2e8f0;color:#475569}
.wv-increment-status{border-radius:999px;display:inline-block;font-size:10px;font-weight:900;padding:5px 9px;text-transform:uppercase}.wv-increment-status.Pending{background:#fef3c7;color:#92400e}.wv-increment-status.Approved{background:#dcfce7;color:#166534}.wv-increment-status.Rejected{background:#fee2e2;color:#b91c1c}
.wv-rental-access{border-radius:999px;display:inline-block;font-size:10px;font-weight:900;padding:5px 9px;text-transform:uppercase}.wv-rental-access.Active{background:#dcfce7;color:#166534}.wv-rental-access.Blocked{background:#fee2e2;color:#dc2626}.wv-rental-access.Unknown{background:#e2e8f0;color:#475569}
.wv-rental-overdue-days{color:#b91c1c;font-size:10px;font-weight:900;white-space:nowrap}
.wv-rental-paid-row td{color:#64748b}.wv-rental-paid-row td:first-child{border-left:3px solid #16a34a!important}
.wv-rental-form-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin-bottom:12px}.wv-rental-form-grid .full{grid-column:1/-1}.wv-rental-form-grid label{display:block;font-size:10px;font-weight:900;text-transform:uppercase}.wv-rental-form-grid .form-control{height:36px}.wv-rental-form-grid textarea.form-control{height:72px}
.wv-rental-inline-form{display:grid;grid-template-columns:130px 110px 110px 110px minmax(180px,1fr) auto;gap:6px;align-items:end}.wv-rental-inline-form .form-control{height:32px;font-size:11px}
@media(max-width:900px){.wv-rental-summary{grid-template-columns:1fr}}
@media(max-width:900px){.wv-rental-ledger-summary{grid-template-columns:1fr 1fr}}
@media(max-width:900px){.wv-rental-form-grid{grid-template-columns:1fr}.wv-rental-form-grid .full{grid-column:auto}.wv-rental-inline-form{grid-template-columns:1fr 1fr}}
</style>
<div class="page-title"><div class="title-env"><h1 class="title"><?php echo __('Rental Agreement');?></h1></div></div>
<div class="panel"><div class="panel-body">
<?php echo $this->Session->flash();?>
<div class="btn-group" style="margin-bottom:12px;">
    <?php echo $this->Html->link('<i class="fa fa-money"></i> '.__('Add Receipt'), array('action'=>'receipt',(int)$r['id']), array('class'=>'btn btn-success','escape'=>false));?>
    <?php echo $this->Html->link('<i class="fa fa-print"></i> '.__('Print Agreement'), array('action'=>'agreement',(int)$r['id']), array('class'=>'btn btn-info','escape'=>false,'target'=>'_blank'));?>
    <?php echo $this->Html->link('<i class="fa fa-edit"></i> '.__('Edit'), array('action'=>'edit',(int)$r['id']), array('class'=>'btn btn-warning','escape'=>false));?>
    <?php echo $this->Html->link('<i class="fa fa-list"></i> '.__('All Rentals'), array('action'=>'index'), array('class'=>'btn btn-default','escape'=>false));?>
</div>
<div class="wv-rental-summary">
    <div class="wv-rental-card"><small><?php echo __('Agreement No');?></small><strong><?php echo h($r['agreement_no']);?></strong></div>
    <div class="wv-rental-card"><small><?php echo __('Tenant');?></small><strong><?php echo h($r['tenant_name']);?></strong></div>
    <div class="wv-rental-card"><small><?php echo __('Monthly Rent');?></small><strong><?php echo h($currency.' '.number_format((float)$r['monthly_rent'],2));?></strong></div>
    <div class="wv-rental-card"><small><?php echo __('Security Deposit');?></small><strong><?php echo h($currency.' '.number_format((float)$r['security_deposit'],2));?></strong></div>
</div>
<div class="wv-rental-card" style="margin-bottom:14px;">
    <div class="row">
        <div class="col-sm-4"><b><?php echo __('Deal');?>:</b> <?php echo h(!empty($rental['Deal']['invoice_no'])?$rental['Deal']['invoice_no']:'#'.$r['deal_id']);?></div>
        <div class="col-sm-4"><b><?php echo __('Client');?>:</b> <?php echo h(!empty($rental['Client']['name'])?$rental['Client']['name']:'-');?></div>
        <div class="col-sm-4"><b><?php echo __('Rental Status');?>:</b> <?php echo h($r['status']);?></div>
        <div class="col-sm-4"><b><?php echo __('Client Access');?>:</b> <?php $portalAccess=!empty($r['client_portal_access'])?$r['client_portal_access']:'Unknown';?><span class="wv-rental-access <?php echo h($portalAccess);?>"><?php echo h($portalAccess);?></span></div>
        <div class="col-sm-4"><b><?php echo __('Period');?>:</b> <?php echo h(date('d-M-Y',strtotime($r['start_date'])).' to '.date('d-M-Y',strtotime($r['end_date'])));?></div>
        <div class="col-sm-4"><b><?php echo __('Due Day');?>:</b> <?php echo (int)$r['due_day'];?></div>
        <div class="col-sm-4"><b><?php echo __('Mobile');?>:</b> <?php echo h($r['tenant_mobile']);?></div>
    </div>
</div>
<?php $scheduleSummary=isset($scheduleSummary)?$scheduleSummary:array('scheduled'=>0,'paid'=>0,'outstanding'=>0,'overdue'=>0,'credit'=>0);?>
<div class="wv-rental-ledger-summary">
    <div class="wv-rental-card"><small><?php echo __('Scheduled Rent');?></small><strong><?php echo h($currency.' '.number_format((float)$scheduleSummary['scheduled'],2));?></strong></div>
    <div class="wv-rental-card"><small><?php echo __('Paid Rent');?></small><strong><?php echo h($currency.' '.number_format((float)$scheduleSummary['paid'],2));?></strong></div>
    <div class="wv-rental-card"><small><?php echo __('Outstanding');?></small><strong><?php echo h($currency.' '.number_format((float)$scheduleSummary['outstanding'],2));?></strong></div>
    <div class="wv-rental-card"><small><?php echo __('Overdue');?></small><strong><?php echo h($currency.' '.number_format((float)$scheduleSummary['overdue'],2));?></strong></div>
    <div class="wv-rental-card"><small><?php echo __('Advance Credit');?></small><strong><?php echo h($currency.' '.number_format((float)$scheduleSummary['credit'],2));?></strong></div>
</div>
<ul class="nav nav-tabs wv-rental-tabs" role="tablist">
    <li><a href="#rental-receipts" data-toggle="tab"><i class="fa fa-file-text-o"></i> <?php echo __('Paid Rent');?></a></li>
    <li class="active"><a href="#upcoming-rent" data-toggle="tab"><i class="fa fa-calendar"></i> <?php echo __('Upcoming Rent');?></a></li>
    <li><a href="#tenant-collections" data-toggle="tab"><i class="fa fa-download"></i> <?php echo __('Tenant Collections');?></a></li>
    <li><a href="#rental-profit" data-toggle="tab"><i class="fa fa-line-chart"></i> <?php echo __('Profitability');?></a></li>
    <li><a href="#rent-increments" data-toggle="tab"><i class="fa fa-level-up"></i> <?php echo __('Rent Increments');?></a></li>
    <li><a href="#rental-maintenance" data-toggle="tab"><i class="fa fa-wrench"></i> <?php echo __('Maintenance');?></a></li>
    <li><a href="#rental-inspections" data-toggle="tab"><i class="fa fa-check-square-o"></i> <?php echo __('Inspections');?></a></li>
</ul>
<div class="tab-content wv-rental-tab-content">
<div class="tab-pane active" id="upcoming-rent">
<div class="table-responsive"><table class="table table-bordered table-striped wv-rental-table">
<thead><tr><th>#</th><th><?php echo __('Rent Month');?></th><th><?php echo __('Due Date');?></th><th><?php echo __('Rent');?></th><th><?php echo __('Paid');?></th><th><?php echo __('Balance');?></th><th><?php echo __('Status');?></th><th><?php echo __('Action');?></th></tr></thead>
<tbody>
<?php if(empty($schedules)){ ?><tr><td colspan="8" class="text-center"><?php echo __('No rental schedule found. Check the agreement start date, end date, and monthly rent.');?></td></tr><?php } ?>
<?php foreach((array)$schedules as $scheduleIndex=>$scheduleRow): $schedule=$scheduleRow['DealRentalSchedule'];
    $statusClass=strtolower(str_replace(' ','-',$schedule['status']));
    $isOverdue=((float)$schedule['balance_amount']>0.009 && !empty($schedule['due_date']) && $schedule['due_date']<date('Y-m-d'));
    $overdueDays=$isOverdue?(int)floor((strtotime(date('Y-m-d'))-strtotime($schedule['due_date']))/86400):0;
?>
<tr class="<?php echo $schedule['status']==='Paid'?'wv-rental-paid-row':'';?>">
    <td><?php echo (int)$scheduleIndex+1;?></td>
    <td><strong><?php echo h(date('M Y',strtotime($schedule['rent_month'].'-01')));?></strong></td>
    <td><?php echo h(date('d-M-Y',strtotime($schedule['due_date'])));?><?php if($overdueDays>0):?><br><span class="wv-rental-overdue-days"><?php echo $overdueDays.' '.__('days overdue');?></span><?php endif;?></td>
    <td><?php echo h($currency.' '.number_format((float)$schedule['rent_amount'],2));?></td>
    <td><strong><?php echo h($currency.' '.number_format((float)$schedule['paid_amount'],2));?></strong></td>
    <td><strong><?php echo h($currency.' '.number_format((float)$schedule['balance_amount'],2));?></strong></td>
    <td><span class="wv-rental-status wv-rental-status-<?php echo h($statusClass);?>"><?php echo h(__($schedule['status']));?></span></td>
    <td><?php if((float)$schedule['balance_amount']>0.009){ echo $this->Html->link('<i class="fa fa-money"></i> '.__('Add Payment'),array('action'=>'receipt',(int)$r['id'],'?'=>array('schedule_id'=>(int)$schedule['id'])),array('class'=>'btn btn-xs btn-success','escape'=>false)); } else { $paidReceiptLinks=isset($scheduleReceiptMap[(int)$schedule['id']])?(array)$scheduleReceiptMap[(int)$schedule['id']]:array(); if(!empty($paidReceiptLinks)){ foreach($paidReceiptLinks as $paidReceiptId=>$paidReceiptLabel){ echo $this->Html->link('<i class="fa fa-print"></i>',array('action'=>'printreceipt',(int)$paidReceiptId),array('class'=>'btn btn-xs btn-info','escape'=>false,'target'=>'_blank','title'=>__('Print Receipt').' '.$paidReceiptLabel)).' '; } if((int)$schedule['id']===(int)$latestPaidScheduleId && (int)$latestPaidReceiptId>0){ echo $this->Html->link('<i class="fa fa-trash"></i>',array('action'=>'deletereceipt',(int)$latestPaidReceiptId),array('class'=>'btn btn-xs btn-danger','escape'=>false,'title'=>__('Delete Latest Paid Rent'),'confirm'=>__('Delete the latest paid rent receipt and reverse its linked Accounts voucher and rental expense?'))); } } else { echo '<span class="text-success"><i class="fa fa-check"></i> '.__('Paid').'</span>'; } }?></td>
</tr>
<?php endforeach;?>
</tbody></table></div>
</div>
<div class="tab-pane" id="rent-increments">
    <div class="wv-rental-card" style="margin-bottom:12px">
        <div class="row">
            <div class="col-sm-3"><small><?php echo __('Management');?></small><strong><?php echo !empty($r['rent_increment_enabled'])?__('On'):__('Off');?></strong></div>
            <div class="col-sm-3"><small><?php echo __('Annual Rule');?></small><strong><?php echo h((!empty($r['rent_increment_type'])?$r['rent_increment_type']:'Percentage').' '.number_format((float)$r['rent_increment_value'],2).($r['rent_increment_type']==='Percentage'?'%':''));?></strong></div>
            <div class="col-sm-3"><small><?php echo __('First Increment');?></small><strong><?php echo h(!empty($r['rent_increment_first_date'])?date('d-M-Y',strtotime($r['rent_increment_first_date'])):'-');?></strong></div>
            <div class="col-sm-3"><small><?php echo __('Approval');?></small><strong><?php echo __('Required');?></strong></div>
        </div>
    </div>
    <?php if(empty($r['rent_increment_enabled'])):?><div class="alert alert-info"><?php echo __('Rent increment management is Off for this rental. Enable it from Edit Rental to create annual proposals.');?></div><?php endif;?>
    <div class="table-responsive"><table class="table table-bordered table-striped wv-rental-table">
        <thead><tr><th><?php echo __('Effective Date');?></th><th><?php echo __('Rule');?></th><th><?php echo __('Old Rent');?></th><th><?php echo __('New Rent');?></th><th><?php echo __('Status');?></th><th><?php echo __('Approved At');?></th><th><?php echo __('Action');?></th></tr></thead>
        <tbody>
        <?php if(empty($rentIncrements)):?><tr><td colspan="7" class="text-center"><?php echo __('No annual rent increment proposals.');?></td></tr><?php endif;?>
        <?php foreach((array)$rentIncrements as $incrementRow):$increment=$incrementRow['DealRentalIncrement'];?>
            <tr>
                <td><strong><?php echo h(date('d-M-Y',strtotime($increment['effective_date'])));?></strong></td>
                <td><?php echo h($increment['increment_type'].' '.number_format((float)$increment['increment_value'],2).($increment['increment_type']==='Percentage'?'%':''));?></td>
                <td><?php echo h($currency.' '.number_format((float)$increment['old_rent'],2));?></td>
                <td><strong><?php echo h($currency.' '.number_format((float)$increment['new_rent'],2));?></strong></td>
                <td><span class="wv-increment-status <?php echo h($increment['status']);?>"><?php echo h($increment['status']);?></span></td>
                <td><?php echo h(!empty($increment['approved_at'])?date('d-M-Y H:i',strtotime($increment['approved_at'])):'-');?></td>
                <td><?php if($increment['status']==='Pending' && !empty($allowEditAction)){ echo $this->Form->postLink('<i class="fa fa-check"></i> '.__('Approve'),array('action'=>'approveincrement',(int)$increment['id']),array('class'=>'btn btn-xs btn-success','escape'=>false,'confirm'=>__('Approve this annual rent increment and update future unpaid schedules?'))).' '; echo $this->Form->postLink('<i class="fa fa-times"></i> '.__('Reject'),array('action'=>'rejectincrement',(int)$increment['id']),array('class'=>'btn btn-xs btn-danger','escape'=>false,'confirm'=>__('Reject this rent increment proposal?'))); }?></td>
            </tr>
        <?php endforeach;?>
        </tbody>
    </table></div>
</div>
<div class="tab-pane" id="rental-receipts">
<div class="table-responsive"><table class="table table-bordered table-striped wv-rental-table">
<thead><tr><th><?php echo __('Receipt');?></th><th><?php echo __('Month');?></th><th><?php echo __('Date');?></th><th><?php echo __('Rent');?></th><th><?php echo __('Expense');?></th><th><?php echo __('Net');?></th><th><?php echo __('Mode');?></th><th><?php echo __('Action');?></th></tr></thead>
<tbody>
<?php if(empty($receipts)){ ?><tr><td colspan="8" class="text-center"><?php echo __('No rental receipts found.');?></td></tr><?php } ?>
<?php foreach((array)$receipts as $row): $x=$row['DealRentalReceipt']; ?>
<tr>
    <td><?php echo h($x['receipt_no']);?></td><td><?php echo h(date('M Y',strtotime($x['rent_month'].'-01')));?></td><td><?php echo h(date('d-M-Y',strtotime($x['receipt_date'])));?></td>
    <td><strong><?php echo h($currency.' '.number_format((float)$x['rent_amount'],2));?></strong></td>
    <td><?php echo h($currency.' '.number_format((float)$x['expense_amount'],2));?></td>
    <td><strong><?php echo h($currency.' '.number_format((float)$x['net_amount'],2));?></strong></td>
    <td><?php echo h($x['payment_mode']);?></td>
    <td><?php echo $this->Html->link('<i class="fa fa-print"></i>', array('action'=>'printreceipt',(int)$x['id']), array('class'=>'btn btn-xs btn-info','escape'=>false,'target'=>'_blank','title'=>__('Print Receipt')));?> <?php echo $this->Html->link('<i class="fa fa-edit"></i>', array('action'=>'editreceipt',(int)$x['id']), array('class'=>'btn btn-xs btn-warning','escape'=>false,'title'=>__('Edit Receipt')));?> <?php echo $this->Html->link('<i class="fa fa-trash"></i>', array('action'=>'deletereceipt',(int)$x['id']), array('class'=>'btn btn-xs btn-danger','escape'=>false,'title'=>__('Delete Receipt'),'confirm'=>__('Delete this receipt and linked rental expense?')));?></td>
</tr>
<?php endforeach; ?>
</tbody></table></div>
</div>

<div class="tab-pane" id="tenant-collections">
<?php echo $this->Form->create('DealRentalTenantCollection',array('url'=>array('action'=>'view',(int)$r['id']))); echo $this->Form->hidden('RentalOperation.type',array('value'=>'tenant_collection'));?>
<div class="wv-rental-card" style="margin-bottom:12px"><div class="wv-rental-form-grid">
    <div><label><?php echo __('Rent Month');?></label><?php echo $this->Form->text('rent_month',array('class'=>'form-control','value'=>date('Y-m'),'placeholder'=>'YYYY-MM'));?></div>
    <div><label><?php echo __('Collection Date');?></label><?php echo $this->Form->text('collection_date',array('class'=>'form-control datepicker','value'=>date('d-M-Y')));?></div>
    <div><label><?php echo __('Tenant Rent');?></label><?php echo $this->Form->text('rent_amount',array('class'=>'form-control','value'=>$r['monthly_rent']));?></div>
    <div><label><?php echo __('Late Fee');?></label><?php echo $this->Form->text('late_fee',array('class'=>'form-control','value'=>'0'));?></div>
    <div><label><?php echo __('Other Income');?></label><?php echo $this->Form->text('other_amount',array('class'=>'form-control','value'=>'0'));?></div>
    <div><label><?php echo __('Received In');?></label><?php echo $this->Form->select('source_ledger_id',isset($paymentAccounts)?$paymentAccounts:array(),array('class'=>'form-control','empty'=>__('Select Cash / Bank'),'required'=>true));?></div>
    <div class="full"><label><?php echo __('Remarks');?></label><?php echo $this->Form->textarea('remarks',array('class'=>'form-control','rows'=>2));?></div>
</div><button class="btn btn-success"><i class="fa fa-save"></i> <?php echo __('Save Tenant Collection');?></button></div>
<?php echo $this->Form->end();?>
<div class="table-responsive"><table class="table table-bordered table-striped wv-rental-table"><thead><tr><th><?php echo __('Collection');?></th><th><?php echo __('Month');?></th><th><?php echo __('Date');?></th><th><?php echo __('Rent');?></th><th><?php echo __('Fees / Other');?></th><th><?php echo __('Total');?></th><th><?php echo __('Account');?></th><th><?php echo __('Action');?></th></tr></thead><tbody>
<?php if(empty($tenantCollections)):?><tr><td colspan="8" class="text-center"><?php echo __('No tenant collections recorded.');?></td></tr><?php endif;?>
<?php foreach((array)$tenantCollections as $collectionRow):$collection=$collectionRow['DealRentalTenantCollection'];?><tr><td><?php echo h($collection['collection_no']);?></td><td><?php echo h($collection['rent_month']);?></td><td><?php echo h($collection['collection_date']);?></td><td><?php echo h($currency.' '.number_format((float)$collection['rent_amount'],2));?></td><td><?php echo h($currency.' '.number_format((float)$collection['late_fee']+(float)$collection['other_amount'],2));?></td><td><strong><?php echo h($currency.' '.number_format((float)$collection['total_amount'],2));?></strong></td><td><?php echo h($collection['payment_mode']);?></td><td><?php echo $this->Form->postLink('<i class="fa fa-trash"></i>',array('action'=>'view',(int)$r['id']),array('data'=>array('RentalOperation'=>array('type'=>'delete_tenant_collection','id'=>(int)$collection['id'])),'class'=>'btn btn-xs btn-danger','escape'=>false,'confirm'=>__('Delete this collection and reverse its income voucher?')));?></td></tr><?php endforeach;?>
</tbody></table></div>
</div>

<div class="tab-pane" id="rental-profit">
<?php $profitability=isset($profitability)?$profitability:array('tenant_income'=>0,'owner_payouts'=>0,'additional_expenses'=>0,'maintenance_cost'=>0,'net_profit'=>0);?>
<div class="wv-rental-ledger-summary">
    <div class="wv-rental-card"><small><?php echo __('Tenant Income');?></small><strong><?php echo h($currency.' '.number_format((float)$profitability['tenant_income'],2));?></strong></div>
    <div class="wv-rental-card"><small><?php echo __('Owner Payouts');?></small><strong><?php echo h($currency.' '.number_format((float)$profitability['owner_payouts'],2));?></strong></div>
    <div class="wv-rental-card"><small><?php echo __('Other Expenses');?></small><strong><?php echo h($currency.' '.number_format((float)$profitability['additional_expenses'],2));?></strong></div>
    <div class="wv-rental-card"><small><?php echo __('Agency Maintenance');?></small><strong><?php echo h($currency.' '.number_format((float)$profitability['maintenance_cost'],2));?></strong></div>
    <div class="wv-rental-card"><small><?php echo __('Net Business Profit');?></small><strong style="color:<?php echo (float)$profitability['net_profit']>=0?'#166534':'#b91c1c';?>"><?php echo h($currency.' '.number_format((float)$profitability['net_profit'],2));?></strong></div>
</div>
</div>

<div class="tab-pane" id="rental-maintenance">
<?php echo $this->Form->create('DealRentalMaintenance',array('url'=>array('action'=>'view',(int)$r['id']))); echo $this->Form->hidden('RentalOperation.type',array('value'=>'maintenance'));?>
<div class="wv-rental-card" style="margin-bottom:12px"><div class="wv-rental-form-grid">
    <div><label><?php echo __('Date');?></label><?php echo $this->Form->text('request_date',array('class'=>'form-control datepicker','value'=>date('d-M-Y')));?></div>
    <div><label><?php echo __('Category');?></label><?php echo $this->Form->select('category',array('General'=>'General','Plumbing'=>'Plumbing','Electrical'=>'Electrical','Structural'=>'Structural','Appliance'=>'Appliance','Security'=>'Security'),array('class'=>'form-control','empty'=>false));?></div>
    <div><label><?php echo __('Priority');?></label><?php echo $this->Form->select('priority',array('Low'=>'Low','Normal'=>'Normal','High'=>'High','Urgent'=>'Urgent'),array('class'=>'form-control','empty'=>false));?></div>
    <div><label><?php echo __('Status');?></label><?php echo $this->Form->select('status',array('Submitted'=>'Submitted','In Review'=>'In Review','Approved'=>'Approved','In Progress'=>'In Progress','Resolved'=>'Resolved','Rejected'=>'Rejected'),array('class'=>'form-control','empty'=>false));?></div>
    <div class="full"><label><?php echo __('Title');?></label><?php echo $this->Form->text('title',array('class'=>'form-control','required'=>true));?></div>
    <div class="full"><label><?php echo __('Description');?></label><?php echo $this->Form->textarea('description',array('class'=>'form-control'));?></div>
    <div><label><?php echo __('Estimated Cost');?></label><?php echo $this->Form->text('estimated_cost',array('class'=>'form-control','value'=>'0'));?></div>
    <div><label><?php echo __('Approved Cost');?></label><?php echo $this->Form->text('approved_cost',array('class'=>'form-control','value'=>'0'));?></div>
</div><button class="btn btn-success"><i class="fa fa-plus"></i> <?php echo __('Add Maintenance Request');?></button></div><?php echo $this->Form->end();?>
<?php foreach((array)$maintenanceRequests as $maintenanceRow):$m=$maintenanceRow['DealRentalMaintenance'];?>
<div class="wv-rental-card" style="margin-bottom:9px"><div style="display:flex;justify-content:space-between;gap:10px"><strong><?php echo h($m['request_no'].' - '.$m['title']);?></strong><span class="wv-rental-status wv-rental-status-<?php echo h(strtolower(str_replace(' ','-',$m['status'])));?>"><?php echo h($m['status']);?></span></div><small><?php echo h($m['request_date'].' | '.$m['category'].' | '.$m['priority'].' | '.$m['submitted_from']);?></small><p><?php echo nl2br(h($m['description']));?></p>
<?php echo $this->Form->create('DealRentalMaintenance',array('url'=>array('action'=>'view',(int)$r['id']))); echo $this->Form->hidden('RentalOperation.type',array('value'=>'maintenance')); echo $this->Form->hidden('id',array('value'=>(int)$m['id'])); echo $this->Form->hidden('request_no',array('value'=>$m['request_no'])); echo $this->Form->hidden('request_date',array('value'=>$m['request_date'])); echo $this->Form->hidden('category',array('value'=>$m['category'])); echo $this->Form->hidden('priority',array('value'=>$m['priority'])); echo $this->Form->hidden('title',array('value'=>$m['title'])); echo $this->Form->hidden('description',array('value'=>$m['description'])); echo $this->Form->hidden('estimated_cost',array('value'=>$m['estimated_cost'])); echo $this->Form->hidden('approved_cost',array('value'=>$m['approved_cost'])); echo $this->Form->hidden('submitted_from',array('value'=>$m['submitted_from']));?>
<div class="wv-rental-inline-form"><div><label><?php echo __('Status');?></label><?php echo $this->Form->select('status',array('Submitted'=>'Submitted','In Review'=>'In Review','Approved'=>'Approved','In Progress'=>'In Progress','Resolved'=>'Resolved','Rejected'=>'Rejected'),array('class'=>'form-control','value'=>$m['status'],'empty'=>false));?></div><div><label><?php echo __('Actual');?></label><?php echo $this->Form->text('actual_cost',array('class'=>'form-control','value'=>$m['actual_cost']));?></div><div><label><?php echo __('Owner Charge');?></label><?php echo $this->Form->text('owner_charge_amount',array('class'=>'form-control','value'=>$m['owner_charge_amount']));?></div><div><label><?php echo __('Agency Charge');?></label><?php echo $this->Form->text('agency_charge_amount',array('class'=>'form-control','value'=>$m['agency_charge_amount']));?></div><div><label><?php echo __('Resolution');?></label><?php echo $this->Form->text('resolution_notes',array('class'=>'form-control','value'=>$m['resolution_notes']));?></div><button class="btn btn-xs btn-primary"><i class="fa fa-save"></i></button></div><?php echo $this->Form->end();?>
</div><?php endforeach;?>
<?php if(empty($maintenanceRequests)):?><div class="text-center wv-rental-card"><?php echo __('No maintenance requests.');?></div><?php endif;?>
</div>

<div class="tab-pane" id="rental-inspections">
<?php echo $this->Form->create('DealRentalInspection',array('url'=>array('action'=>'view',(int)$r['id']),'type'=>'file')); echo $this->Form->hidden('RentalOperation.type',array('value'=>'inspection'));?>
<div class="wv-rental-card" style="margin-bottom:12px"><div class="wv-rental-form-grid">
    <div><label><?php echo __('Type');?></label><?php echo $this->Form->select('inspection_type',array('Possession'=>'Possession','Periodic'=>'Periodic','Handover'=>'Handover'),array('class'=>'form-control','empty'=>false));?></div><div><label><?php echo __('Date');?></label><?php echo $this->Form->text('inspection_date',array('class'=>'form-control datepicker','value'=>date('d-M-Y')));?></div><div><label><?php echo __('Status');?></label><?php echo $this->Form->select('status',array('Scheduled'=>'Scheduled','Completed'=>'Completed','Cancelled'=>'Cancelled'),array('class'=>'form-control','empty'=>false));?></div><div><label><?php echo __('Inspector');?></label><?php echo $this->Form->text('inspector_name',array('class'=>'form-control'));?></div>
    <div><label><?php echo __('Electricity Meter');?></label><?php echo $this->Form->text('electricity_meter',array('class'=>'form-control'));?></div><div><label><?php echo __('Gas Meter');?></label><?php echo $this->Form->text('gas_meter',array('class'=>'form-control'));?></div><div><label><?php echo __('Water Meter');?></label><?php echo $this->Form->text('water_meter',array('class'=>'form-control'));?></div><div><label><?php echo __('Keys');?></label><?php echo $this->Form->number('keys_count',array('class'=>'form-control','min'=>0));?></div>
    <div><label><?php echo __('Security Deposit');?></label><?php echo $this->Form->select('security_deposit_status',array('Pending'=>'Pending','Received'=>'Received','Adjusted'=>'Adjusted','Refunded'=>'Refunded'),array('class'=>'form-control','empty'=>__('Select')));?></div><div><label><?php echo __('Client Acknowledged');?></label><?php echo $this->Form->checkbox('client_acknowledged');?></div><div class="full"><label><?php echo __('Condition Notes');?></label><?php echo $this->Form->textarea('condition_notes',array('class'=>'form-control'));?></div><div class="full"><label><?php echo __('Condition Photos');?></label><?php echo $this->Form->file('photos.',array('multiple'=>'multiple','accept'=>'image/jpeg,image/png,image/gif,image/webp'));?></div>
</div><button class="btn btn-success"><i class="fa fa-save"></i> <?php echo __('Save Checklist');?></button></div><?php echo $this->Form->end();?>
<div class="table-responsive"><table class="table table-bordered table-striped wv-rental-table"><thead><tr><th><?php echo __('Type');?></th><th><?php echo __('Date');?></th><th><?php echo __('Status');?></th><th><?php echo __('Inspector');?></th><th><?php echo __('Meters');?></th><th><?php echo __('Keys');?></th><th><?php echo __('Deposit');?></th><th><?php echo __('Notes / Photos');?></th></tr></thead><tbody><?php if(empty($inspections)):?><tr><td colspan="8" class="text-center"><?php echo __('No inspection records.');?></td></tr><?php endif;?><?php foreach((array)$inspections as $inspectionRow):$i=$inspectionRow['DealRentalInspection'];$inspectionPhotos=json_decode(isset($i['photos_json'])?$i['photos_json']:'',true);?><tr><td><?php echo h($i['inspection_type']);?></td><td><?php echo h($i['inspection_date']);?></td><td><?php echo h($i['status']);?></td><td><?php echo h($i['inspector_name']);?></td><td><?php echo h('E: '.$i['electricity_meter'].' | G: '.$i['gas_meter'].' | W: '.$i['water_meter']);?></td><td><?php echo (int)$i['keys_count'];?></td><td><?php echo h($i['security_deposit_status']);?></td><td><?php echo nl2br(h($i['condition_notes']));?><?php foreach((array)$inspectionPhotos as $photo):?><a href="<?php echo h($this->Html->url('/files/rental_inspections/'.rawurlencode($photo)));?>" target="_blank"><img src="<?php echo h($this->Html->url('/files/rental_inspections/'.rawurlencode($photo)));?>" alt="" style="width:46px;height:46px;object-fit:cover;margin:4px;border:1px solid #ccd8e8"></a><?php endforeach;?></td></tr><?php endforeach;?></tbody></table></div>
</div>
</div>
</div></div>
