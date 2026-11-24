<?php
echo $this->Html->css('select2/select2');
echo $this->Html->css('select2/select2-bootstrap');
echo $this->Html->script('select2.min');
$allDatesQuery = array('all_dates' => 1);
if ($selectedProjectId > 0) { $allDatesQuery['project_id'] = $selectedProjectId; }
if ($selectedRentalId > 0) { $allDatesQuery['rental_id'] = $selectedRentalId; }
if ($selectedStatus !== '') { $allDatesQuery['status'] = $selectedStatus; }
?>
<style>
.wv-report-head{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:14px}.wv-report-head h2{margin:0;color:#0b2344;font-weight:900}.wv-report-head p{margin:3px 0 0;color:#64748b;font-weight:700}
.wv-report-filters{display:grid;grid-template-columns:repeat(6,minmax(130px,1fr));gap:10px;align-items:end;border:1px solid #dbe6f3;padding:12px;margin-bottom:14px;background:#f8fbff}.wv-report-filter label{display:block;color:#334155;font-size:10px;font-weight:900;margin-bottom:4px;text-transform:uppercase}.wv-report-filter .form-control{height:38px;border:1px solid #cbd8e8;border-radius:6px;box-shadow:none;font-weight:700;width:100%}.wv-report-filter-actions{display:flex;gap:6px;flex-wrap:wrap}
.wv-report-stats{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:10px;margin-bottom:14px}.wv-report-stat{background:#fff;border:1px solid #dbe6f3;border-top:4px solid #2563eb;border-radius:8px;padding:11px;min-height:78px}.wv-report-stat.green{border-top-color:#16a34a}.wv-report-stat.red{border-top-color:#dc2626}.wv-report-stat.amber{border-top-color:#d97706}.wv-report-stat.cyan{border-top-color:#0891b2}.wv-report-stat.violet{border-top-color:#7c3aed}.wv-report-stat small{display:block;color:#64748b;font-size:10px;font-weight:900;text-transform:uppercase}.wv-report-stat strong{color:#102a43;display:block;font-size:17px;margin-top:5px;white-space:nowrap}.wv-report-stat.net-negative strong{color:#b91c1c}.wv-report-stat.net-positive strong{color:#166534}
.wv-report-table{margin-bottom:0}.wv-report-table th{background:#0b2344!important;color:#fff!important;font-size:10px;text-transform:uppercase;vertical-align:middle!important;white-space:nowrap}.wv-report-table td{font-size:11px;vertical-align:middle!important}.wv-report-table tfoot td{background:#eaf1f9!important;border-top:2px solid #0b2344!important;font-weight:900;white-space:nowrap}.wv-money{font-weight:800;white-space:nowrap}.wv-money-paid{color:#166534}.wv-money-due{color:#b42318}.wv-money-profit{color:#166534}.wv-money-loss{color:#b91c1c}.wv-report-status{display:inline-block;border-radius:999px;font-size:9px;font-weight:900;padding:4px 8px;text-transform:uppercase}.wv-report-status.Active{background:#dcfce7;color:#166534}.wv-report-status.Closed{background:#e2e8f0;color:#334155}.wv-report-status.Cancelled{background:#fee2e2;color:#b91c1c}
.wv-report-tabs{border-bottom:2px solid #dbe6f3;margin-top:16px}.wv-report-tabs>li>a{border-radius:6px 6px 0 0;color:#334155;font-size:12px;font-weight:900}.wv-report-tabs>li.active>a,.wv-report-tabs>li.active>a:hover{background:#0b2344!important;border-color:#0b2344!important;color:#fff!important}.wv-report-tab-content{padding-top:12px}.wv-report-section-title{color:#0b2344;font-size:15px;font-weight:900;margin:16px 0 8px}.wv-report-period{background:#eaf1f9;border-radius:5px;color:#0b2344;display:inline-block;font-size:11px;font-weight:900;padding:5px 9px}
.wv-report-filter .select2-container{width:100%!important}.wv-report-filter .select2-container .select2-choice{height:38px;line-height:36px;border:1px solid #cbd8e8;border-radius:6px;font-weight:700}.wv-report-filter .select2-container .select2-choice .select2-arrow b{background-position:0 6px}
@media(max-width:1200px){.wv-report-filters{grid-template-columns:repeat(3,minmax(160px,1fr))}.wv-report-stats{grid-template-columns:repeat(3,minmax(0,1fr))}}
@media(max-width:700px){.wv-report-head{align-items:flex-start;flex-direction:column}.wv-report-filters{grid-template-columns:1fr}.wv-report-stats{grid-template-columns:repeat(2,minmax(0,1fr))}.wv-report-stat strong{font-size:14px}}
@media print{.wv-no-print,.main-header,.main-sidebar,.left-side,.page-title{display:none!important}.content-wrapper,.right-side,.main-content{margin:0!important}.panel,.panel-body{border:0!important;box-shadow:none!important;padding:0!important}.wv-report-tabs{display:none!important}.tab-content>.tab-pane{display:block!important;opacity:1!important}.wv-report-tab-content{padding:0}.wv-report-table th{background:#e5e7eb!important;color:#111!important}.wv-report-stats{grid-template-columns:repeat(5,1fr)}.wv-report-stat{box-shadow:none}.wv-report-section-title{page-break-after:avoid}.table-responsive{overflow:visible!important}.wv-report-table{font-size:8px}.wv-report-table th,.wv-report-table td{padding:4px!important}}
</style>
<div class="page-title"><div class="title-env"><h1 class="title"><?php echo __('Rental Management Report');?></h1></div></div>
<div class="panel"><div class="panel-body">
<?php echo $this->Session->flash();?>
<div class="wv-report-head">
    <div><h2><?php echo __('Rental Performance & Profitability');?></h2><p><span class="wv-report-period"><i class="fa fa-calendar"></i> <?php echo h($reportPeriod);?></span></p></div>
    <div class="wv-no-print">
        <button type="button" class="btn btn-info" onclick="window.print();"><i class="fa fa-print"></i> <?php echo __('Print');?></button>
        <?php echo $this->Html->link('<i class="fa fa-list"></i> '.__('All Rentals'), array('action'=>'index'), array('class'=>'btn btn-default','escape'=>false));?>
    </div>
</div>

<?php echo $this->Form->create(false, array('type'=>'get','class'=>'wv-report-filters wv-no-print'));?>
    <?php if($isSuperAdmin):?><div class="wv-report-filter"><label><?php echo __('Agency');?></label><?php echo $this->Form->select('project_id',$projectOptions,array('empty'=>__('All Agencies'),'class'=>'form-control','value'=>$selectedProjectId));?></div><?php endif;?>
    <div class="wv-report-filter"><label><?php echo __('Rental Agreement');?></label><?php echo $this->Form->select('rental_id',$rentalOptions,array('id'=>'RentalReportRentalId','empty'=>__('All Rental Agreements'),'class'=>'form-control','value'=>$selectedRentalId));?></div>
    <div class="wv-report-filter"><label><?php echo __('Rental Status');?></label><?php echo $this->Form->select('status',array('Active'=>__('Active'),'Closed'=>__('Closed'),'Cancelled'=>__('Cancelled')),array('empty'=>__('All Statuses'),'class'=>'form-control','value'=>$selectedStatus));?></div>
    <div class="wv-report-filter"><label><?php echo __('From Date');?></label><input type="text" name="from" class="form-control datepicker" autocomplete="off" value="<?php echo h($fromDate);?>"></div>
    <div class="wv-report-filter"><label><?php echo __('To Date');?></label><input type="text" name="to" class="form-control datepicker" autocomplete="off" value="<?php echo h($toDate);?>"></div>
    <div class="wv-report-filter-actions">
        <button class="btn btn-primary" type="submit"><i class="fa fa-filter"></i> <?php echo __('Apply');?></button>
        <?php echo $this->Html->link('<i class="fa fa-calendar-o"></i> '.__('All Dates'),array('action'=>'reports','?'=>$allDatesQuery),array('class'=>'btn btn-default','escape'=>false));?>
        <?php echo $this->Html->link('<i class="fa fa-refresh"></i>',array('action'=>'reports'),array('class'=>'btn btn-default','escape'=>false,'title'=>__('Reset')));?>
    </div>
<?php echo $this->Form->end();?>

<div class="wv-report-stats">
    <div class="wv-report-stat"><small><?php echo __('Scheduled Rent');?></small><strong><?php echo h($currency.' '.number_format((float)$totals['scheduled'],2));?></strong></div>
    <div class="wv-report-stat green"><small><?php echo __('Schedule Paid');?></small><strong><?php echo h($currency.' '.number_format((float)$totals['schedule_paid'],2));?></strong></div>
    <div class="wv-report-stat amber"><small><?php echo __('Outstanding');?></small><strong><?php echo h($currency.' '.number_format((float)$totals['outstanding'],2));?></strong></div>
    <div class="wv-report-stat red"><small><?php echo __('Overdue');?></small><strong><?php echo h($currency.' '.number_format((float)$totals['overdue'],2));?></strong></div>
    <div class="wv-report-stat cyan"><small><?php echo __('Upcoming');?></small><strong><?php echo h($currency.' '.number_format((float)$totals['upcoming'],2));?></strong></div>
    <div class="wv-report-stat green"><small><?php echo __('Tenant Income');?></small><strong><?php echo h($currency.' '.number_format((float)$totals['tenant_income'],2));?></strong></div>
    <div class="wv-report-stat violet"><small><?php echo __('Owner Payouts');?></small><strong><?php echo h($currency.' '.number_format((float)$totals['owner_payouts'],2));?></strong></div>
    <div class="wv-report-stat amber"><small><?php echo __('Additional Expenses');?></small><strong><?php echo h($currency.' '.number_format((float)$totals['additional_expenses'],2));?></strong></div>
    <div class="wv-report-stat red"><small><?php echo __('Agency Maintenance');?></small><strong><?php echo h($currency.' '.number_format((float)$totals['maintenance_cost'],2));?></strong></div>
    <div class="wv-report-stat <?php echo (float)$totals['net_profit']>=0?'net-positive':'net-negative';?>"><small><?php echo __('Net Business Profit');?></small><strong><?php echo h($currency.' '.number_format((float)$totals['net_profit'],2));?></strong></div>
</div>

<div class="table-responsive">
<table class="table table-bordered table-striped wv-report-table">
<thead><tr><th><?php echo __('Agreement');?></th><th><?php echo __('Client / Deal');?></th><th><?php echo __('Status');?></th><th><?php echo __('Scheduled');?></th><th><?php echo __('Schedule Paid');?></th><th><?php echo __('Outstanding');?></th><th><?php echo __('Overdue');?></th><th><?php echo __('Upcoming');?></th><th><?php echo __('Tenant Income');?></th><th><?php echo __('Owner Payout');?></th><th><?php echo __('Extra Expense');?></th><th><?php echo __('Maintenance');?></th><th><?php echo __('Net Profit');?></th><th class="wv-no-print"><?php echo __('Action');?></th></tr></thead>
<tbody>
<?php if(empty($reportRows)):?><tr><td colspan="14" class="text-center"><?php echo __('No rental agreements found for the selected filters.');?></td></tr><?php endif;?>
<?php foreach((array)$reportRows as $row):$r=$row['DealRental'];$m=$row['Report'];?>
<tr>
    <td><strong><?php echo h($r['agreement_no']);?></strong><br><small><?php echo h(date('d-M-Y',strtotime($r['start_date'])).' '.__('to').' '.date('d-M-Y',strtotime($r['end_date'])));?></small></td>
    <td><strong><?php echo h(!empty($row['Client']['name'])?$row['Client']['name']:'-');?></strong><br><small><?php echo h(!empty($row['Deal']['invoice_no'])?$row['Deal']['invoice_no']:'#'.$r['deal_id']);?></small></td>
    <td><span class="wv-report-status <?php echo h($r['status']);?>"><?php echo h($r['status']);?></span></td>
    <td><span class="wv-money"><?php echo h($currency.' '.number_format((float)$m['scheduled'],2));?></span></td>
    <td><span class="wv-money wv-money-paid"><?php echo h($currency.' '.number_format((float)$m['schedule_paid'],2));?></span></td>
    <td><span class="wv-money wv-money-due"><?php echo h($currency.' '.number_format((float)$m['outstanding'],2));?></span></td>
    <td><span class="wv-money wv-money-due"><?php echo h($currency.' '.number_format((float)$m['overdue'],2));?></span></td>
    <td><span class="wv-money"><?php echo h($currency.' '.number_format((float)$m['upcoming'],2));?></span></td>
    <td><span class="wv-money wv-money-paid"><?php echo h($currency.' '.number_format((float)$m['tenant_income'],2));?></span></td>
    <td><span class="wv-money"><?php echo h($currency.' '.number_format((float)$m['owner_payouts'],2));?></span><?php if((float)$m['credit']>0):?><br><small><?php echo __('Credit');?>: <?php echo h($currency.' '.number_format((float)$m['credit'],2));?></small><?php endif;?></td>
    <td><span class="wv-money"><?php echo h($currency.' '.number_format((float)$m['additional_expenses'],2));?></span></td>
    <td><span class="wv-money"><?php echo h($currency.' '.number_format((float)$m['maintenance_cost'],2));?></span><?php if((float)$m['owner_charges']>0):?><br><small><?php echo __('Owner Charge');?>: <?php echo h($currency.' '.number_format((float)$m['owner_charges'],2));?></small><?php endif;?></td>
    <td><span class="wv-money <?php echo (float)$m['net_profit']>=0?'wv-money-profit':'wv-money-loss';?>"><?php echo h($currency.' '.number_format((float)$m['net_profit'],2));?></span></td>
    <td class="wv-no-print"><?php echo $this->Html->link('<i class="fa fa-eye"></i>',array('action'=>'view',(int)$r['id']),array('class'=>'btn btn-xs btn-primary','escape'=>false,'title'=>__('View Rental')));?></td>
</tr>
<?php endforeach;?>
</tbody>
<?php if(!empty($reportRows)):?><tfoot><tr><td colspan="3" class="text-right"><?php echo __('Filtered Total');?></td><td><?php echo h($currency.' '.number_format((float)$totals['scheduled'],2));?></td><td><?php echo h($currency.' '.number_format((float)$totals['schedule_paid'],2));?></td><td><?php echo h($currency.' '.number_format((float)$totals['outstanding'],2));?></td><td><?php echo h($currency.' '.number_format((float)$totals['overdue'],2));?></td><td><?php echo h($currency.' '.number_format((float)$totals['upcoming'],2));?></td><td><?php echo h($currency.' '.number_format((float)$totals['tenant_income'],2));?></td><td><?php echo h($currency.' '.number_format((float)$totals['owner_payouts'],2));?></td><td><?php echo h($currency.' '.number_format((float)$totals['additional_expenses'],2));?></td><td><?php echo h($currency.' '.number_format((float)$totals['maintenance_cost'],2));?></td><td><?php echo h($currency.' '.number_format((float)$totals['net_profit'],2));?></td><td class="wv-no-print"></td></tr></tfoot><?php endif;?>
</table>
</div>

<ul class="nav nav-tabs wv-report-tabs wv-no-print" role="tablist">
    <li class="active"><a href="#owner-payments" data-toggle="tab"><i class="fa fa-money"></i> <?php echo __('Owner Payments');?> (<?php echo count($receipts);?>)</a></li>
    <li><a href="#tenant-collections" data-toggle="tab"><i class="fa fa-download"></i> <?php echo __('Tenant Collections');?> (<?php echo count($tenantCollections);?>)</a></li>
    <li><a href="#rental-expenses" data-toggle="tab"><i class="fa fa-credit-card"></i> <?php echo __('Expenses');?> (<?php echo count($monthlyExpenses)+count($maintenanceRequests);?>)</a></li>
</ul>
<div class="tab-content wv-report-tab-content">
<div class="tab-pane active" id="owner-payments">
    <h3 class="wv-report-section-title"><?php echo __('Owner Rental Payments');?></h3>
    <div class="table-responsive"><table class="table table-bordered table-striped wv-report-table"><thead><tr><th><?php echo __('Date');?></th><th><?php echo __('Receipt');?></th><th><?php echo __('Agreement');?></th><th><?php echo __('Client');?></th><th><?php echo __('Rent Month');?></th><th><?php echo __('Payment Method');?></th><th><?php echo __('Owner Rent');?></th><th><?php echo __('Additional Expense');?></th><th><?php echo __('Total Outflow');?></th><th class="wv-no-print"><?php echo __('Print');?></th></tr></thead><tbody>
    <?php if(empty($receipts)):?><tr><td colspan="10" class="text-center"><?php echo __('No owner rental payments found.');?></td></tr><?php endif;?>
    <?php foreach((array)$receipts as $row):$x=$row['DealRentalReceipt'];?><tr><td><?php echo h($x['receipt_date']);?></td><td><strong><?php echo h($x['receipt_no']);?></strong></td><td><?php echo h(!empty($row['DealRental']['agreement_no'])?$row['DealRental']['agreement_no']:'-');?></td><td><?php echo h(!empty($row['Client']['name'])?$row['Client']['name']:'-');?></td><td><?php echo h($x['rent_month']);?></td><td><?php echo h($x['payment_mode']);?></td><td class="wv-money"><?php echo h($currency.' '.number_format((float)$x['rent_amount'],2));?></td><td class="wv-money"><?php echo h($currency.' '.number_format((float)$x['expense_amount'],2));?></td><td class="wv-money"><?php echo h($currency.' '.number_format((float)$x['rent_amount']+(float)$x['expense_amount'],2));?></td><td class="wv-no-print"><?php echo $this->Html->link('<i class="fa fa-print"></i>',array('action'=>'printreceipt',(int)$x['id']),array('class'=>'btn btn-xs btn-info','escape'=>false,'target'=>'_blank','title'=>__('Print Receipt')));?></td></tr><?php endforeach;?>
    </tbody><?php if(!empty($receipts)):?><tfoot><tr><td colspan="6" class="text-right"><?php echo __('Total');?></td><td><?php echo h($currency.' '.number_format((float)$totals['owner_payouts'],2));?></td><td><?php echo h($currency.' '.number_format((float)$totals['additional_expenses'],2));?></td><td><?php echo h($currency.' '.number_format((float)$totals['owner_payouts']+(float)$totals['additional_expenses'],2));?></td><td class="wv-no-print"></td></tr></tfoot><?php endif;?></table></div>
</div>
<div class="tab-pane" id="tenant-collections">
    <h3 class="wv-report-section-title"><?php echo __('Tenant Rent Collections');?></h3>
    <div class="table-responsive"><table class="table table-bordered table-striped wv-report-table"><thead><tr><th><?php echo __('Date');?></th><th><?php echo __('Collection');?></th><th><?php echo __('Agreement');?></th><th><?php echo __('Client');?></th><th><?php echo __('Rent Month');?></th><th><?php echo __('Base Rent');?></th><th><?php echo __('Late Fee');?></th><th><?php echo __('Other');?></th><th><?php echo __('Total Income');?></th><th><?php echo __('Payment Method');?></th></tr></thead><tbody>
    <?php if(empty($tenantCollections)):?><tr><td colspan="10" class="text-center"><?php echo __('No tenant collections found.');?></td></tr><?php endif;?>
    <?php foreach((array)$tenantCollections as $row):$x=$row['DealRentalTenantCollection'];?><tr><td><?php echo h($x['collection_date']);?></td><td><strong><?php echo h($x['collection_no']);?></strong></td><td><?php echo h(!empty($row['DealRental']['agreement_no'])?$row['DealRental']['agreement_no']:'-');?></td><td><?php echo h(!empty($row['Client']['name'])?$row['Client']['name']:'-');?></td><td><?php echo h($x['rent_month']);?></td><td class="wv-money"><?php echo h($currency.' '.number_format((float)$x['rent_amount'],2));?></td><td class="wv-money"><?php echo h($currency.' '.number_format((float)$x['late_fee'],2));?></td><td class="wv-money"><?php echo h($currency.' '.number_format((float)$x['other_amount'],2));?></td><td class="wv-money wv-money-paid"><?php echo h($currency.' '.number_format((float)$x['total_amount'],2));?></td><td><?php echo h($x['payment_mode']);?></td></tr><?php endforeach;?>
    </tbody><?php if(!empty($tenantCollections)):?><tfoot><tr><td colspan="5" class="text-right"><?php echo __('Total');?></td><td><?php echo h($currency.' '.number_format((float)$totals['tenant_rent'],2));?></td><td><?php echo h($currency.' '.number_format((float)$totals['late_fees'],2));?></td><td><?php echo h($currency.' '.number_format((float)$totals['other_income'],2));?></td><td><?php echo h($currency.' '.number_format((float)$totals['tenant_income'],2));?></td><td></td></tr></tfoot><?php endif;?></table></div>
</div>
<div class="tab-pane" id="rental-expenses">
    <h3 class="wv-report-section-title"><?php echo __('Additional Monthly Expenses');?></h3>
    <div class="table-responsive"><table class="table table-bordered table-striped wv-report-table"><thead><tr><th><?php echo __('Date');?></th><th><?php echo __('Agreement');?></th><th><?php echo __('Client');?></th><th><?php echo __('Month');?></th><th><?php echo __('Amount');?></th><th><?php echo __('Remarks');?></th></tr></thead><tbody>
    <?php if(empty($monthlyExpenses)):?><tr><td colspan="6" class="text-center"><?php echo __('No additional monthly expenses found.');?></td></tr><?php endif;?>
    <?php foreach((array)$monthlyExpenses as $row):$x=$row['DealRentalMonthlyExpense'];?><tr><td><?php echo h($x['expense_date']);?></td><td><?php echo h(!empty($row['DealRental']['agreement_no'])?$row['DealRental']['agreement_no']:'-');?></td><td><?php echo h(!empty($row['Client']['name'])?$row['Client']['name']:'-');?></td><td><?php echo h($x['expense_month']);?></td><td class="wv-money"><?php echo h($currency.' '.number_format((float)$x['amount'],2));?></td><td><?php echo h($x['remarks']);?></td></tr><?php endforeach;?>
    </tbody><?php if(!empty($monthlyExpenses)):?><tfoot><tr><td colspan="4" class="text-right"><?php echo __('Total');?></td><td><?php echo h($currency.' '.number_format((float)$totals['additional_expenses'],2));?></td><td></td></tr></tfoot><?php endif;?></table></div>
    <h3 class="wv-report-section-title"><?php echo __('Maintenance Charges');?></h3>
    <div class="table-responsive"><table class="table table-bordered table-striped wv-report-table"><thead><tr><th><?php echo __('Date');?></th><th><?php echo __('Request');?></th><th><?php echo __('Agreement');?></th><th><?php echo __('Client');?></th><th><?php echo __('Title');?></th><th><?php echo __('Status');?></th><th><?php echo __('Owner Charge');?></th><th><?php echo __('Agency Cost');?></th></tr></thead><tbody>
    <?php if(empty($maintenanceRequests)):?><tr><td colspan="8" class="text-center"><?php echo __('No maintenance charges found.');?></td></tr><?php endif;?>
    <?php foreach((array)$maintenanceRequests as $row):$x=$row['DealRentalMaintenance'];?><tr><td><?php echo h($x['request_date']);?></td><td><strong><?php echo h($x['request_no']);?></strong></td><td><?php echo h(!empty($row['DealRental']['agreement_no'])?$row['DealRental']['agreement_no']:'-');?></td><td><?php echo h(!empty($row['Client']['name'])?$row['Client']['name']:'-');?></td><td><?php echo h($x['title']);?></td><td><?php echo h($x['status']);?></td><td class="wv-money"><?php echo h($currency.' '.number_format((float)$x['owner_charge_amount'],2));?></td><td class="wv-money"><?php echo h($currency.' '.number_format((float)$x['agency_charge_amount'],2));?></td></tr><?php endforeach;?>
    </tbody><?php if(!empty($maintenanceRequests)):?><tfoot><tr><td colspan="6" class="text-right"><?php echo __('Total');?></td><td><?php echo h($currency.' '.number_format((float)$totals['owner_charges'],2));?></td><td><?php echo h($currency.' '.number_format((float)$totals['maintenance_cost'],2));?></td></tr></tfoot><?php endif;?></table></div>
</div>
</div>
</div></div>
<script type="text/javascript">
jQuery(function($){
    if($.fn.select2 && $('#RentalReportRentalId').length){
        $('#RentalReportRentalId').select2({placeholder:'<?php echo addslashes(__('Search Client or Agreement'));?>',allowClear:true,width:'100%'});
    }
});
</script>
