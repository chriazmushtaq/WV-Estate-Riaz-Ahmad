<style>
.wv-rental-hero{background:linear-gradient(135deg,#071c36,#123b68 62%,#6b174b);border-radius:10px;color:#fff;padding:18px 20px;margin-bottom:14px;display:flex;justify-content:space-between;align-items:center;gap:12px}
.wv-rental-hero h2{margin:0;color:#fff!important;font-weight:900}
.wv-rental-hero p{margin:4px 0 0;color:#d9e8fb}
.wv-rental-table th{background:#0b2344!important;color:#fff!important;text-transform:uppercase;font-size:12px}
.wv-rental-status{display:inline-block;border-radius:999px;padding:4px 10px;font-size:11px;font-weight:900;text-transform:uppercase}
.wv-rental-status.Active{background:#dff7e8;color:#08753b}.wv-rental-status.Closed{background:#e8eef7;color:#274268}.wv-rental-status.Cancelled{background:#ffe1e8;color:#9d1035}
.wv-rental-access{display:inline-block;border-radius:999px;padding:4px 10px;font-size:11px;font-weight:900;text-transform:uppercase}.wv-rental-access.Active{background:#dcfce7;color:#166534}.wv-rental-access.Blocked{background:#fee2e2;color:#dc2626}.wv-rental-access.Unknown{background:#e2e8f0;color:#475569}
.wv-rental-money{white-space:nowrap;font-weight:900}.wv-rental-money-paid{color:#08753b}.wv-rental-money-due{color:#b42318}
.wv-rental-table tfoot td{background:#eef4fb!important;border-top:2px solid #0b2344!important;font-weight:900}.wv-rental-total-label{text-align:right;text-transform:uppercase;color:#0b2344}
.wv-rental-actions{display:inline-block;white-space:nowrap}.wv-rental-actions>.btn-group>.btn{border-radius:8px!important;height:32px;min-width:34px;padding:6px 9px}.wv-rental-actions .dropdown-menu{left:auto;min-width:190px;right:0}.wv-rental-actions .dropdown-menu>li>a,.wv-rental-actions .dropdown-menu>li>span{display:block;font-size:12px;font-weight:800;padding:8px 13px}.wv-rental-actions .dropdown-menu>li>a>i,.wv-rental-actions .dropdown-menu>li>span>i{margin-right:7px;width:14px}.wv-rental-delete-item>a{color:#dc2626!important}.wv-rental-delete-disabled{color:#dc2626;cursor:not-allowed;opacity:.48}
</style>
<div class="page-title"><div class="title-env"><h1 class="title"><?php echo __('Rental Income');?></h1></div></div>
<div class="panel"><div class="panel-body">
<?php echo $this->Session->flash();?>
<div class="wv-rental-hero">
    <div><h2><?php echo __('Deal Rental Income');?></h2><p><?php echo __('Manage rental agreements, receipts, auto rental expenses, and reports.');?></p></div>
    <div>
        <?php echo $this->Html->link('<i class="fa fa-plus"></i> '.__('Add Rental'), array('action'=>'add'), array('class'=>'btn btn-success','escape'=>false));?>
        <?php echo $this->Html->link('<i class="fa fa-bar-chart"></i> '.__('Reports'), array('action'=>'reports'), array('class'=>'btn btn-info','escape'=>false));?>
    </div>
</div>
<?php echo $this->Form->create(false, array('type'=>'get','class'=>'form-inline','style'=>'margin-bottom:12px;'));?>
    <?php echo $this->Form->select('deal_id', $dealOptions, array('empty'=>__('All Deals'),'class'=>'form-control','value'=>isset($this->request->query['deal_id'])?$this->request->query['deal_id']:''));?>
    <?php echo $this->Form->select('status', array('Active'=>'Active','Closed'=>'Closed','Cancelled'=>'Cancelled'), array('empty'=>__('All Status'),'class'=>'form-control','value'=>isset($this->request->query['status'])?$this->request->query['status']:''));?>
    <?php echo $this->Form->select('client_portal_access', array('Active'=>__('Active'),'Blocked'=>__('Blocked')), array('empty'=>__('All Client Access'),'class'=>'form-control','value'=>isset($this->request->query['client_portal_access'])?$this->request->query['client_portal_access']:''));?>
    <button class="btn btn-primary"><i class="fa fa-filter"></i> <?php echo __('Filter');?></button>
<?php echo $this->Form->end();?>
<div class="table-responsive">
<table class="table table-bordered table-striped wv-rental-table">
<thead><tr><th><?php echo __('Agreement');?></th><th><?php echo __('Deal');?></th><th><?php echo __('Client');?></th><th><?php echo __('Father / Guardian');?></th><th><?php echo __('Tenant');?></th><th><?php echo __('Monthly Rent');?></th><th><?php echo __('Paid');?></th><th><?php echo __('Due');?></th><th><?php echo __('Period');?></th><th><?php echo __('Status');?></th><th><?php echo __('Client Access');?></th><th class="actions-col"><?php echo __('Action');?></th></tr></thead>
<tbody>
<?php if(empty($rentals)){ ?><tr><td colspan="12" class="text-center"><?php echo __('No rental agreements found.');?></td></tr><?php } ?>
<?php 
$totalRentals = !empty($rentals) ? count($rentals) : 0;
$rentalIndex = 0;
foreach((array)$rentals as $row): 
    $rentalIndex++;
    $r=$row['DealRental']; 
    $rentalId=(int)$r['id']; 
    $hasPaidRent=!empty($paidRentalMap[$rentalId]); 
    $scheduleTotals=isset($rentalScheduleTotals[$rentalId])?$rentalScheduleTotals[$rentalId]:array('paid'=>0,'due'=>0); 
?>
<tr>
    <td><strong><?php echo h($r['agreement_no']);?></strong></td>
    <td><?php echo h(!empty($row['Deal']['invoice_no'])?$row['Deal']['invoice_no']:'#'.$r['deal_id']);?></td>
    <td><?php echo h(!empty($row['Client']['name'])?$row['Client']['name']:'-');?></td>
    <td>
        <?php
        $fatherName = !empty($row['Client']['fathername']) ? trim((string)$row['Client']['fathername']) : '';
        $relationType = !empty($row['Client']['relation_type']) ? trim((string)$row['Client']['relation_type']) : '';
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
    <td><strong><?php echo h($r['tenant_name']);?></strong><br><small><?php echo h($r['tenant_mobile']);?></small></td>
    <td><strong><?php echo h($currency.' '.number_format((float)$r['monthly_rent'],2));?></strong></td>
    <td><span class="wv-rental-money wv-rental-money-paid"><?php echo h($currency.' '.number_format((float)$scheduleTotals['paid'],2));?></span></td>
    <td><span class="wv-rental-money wv-rental-money-due"><?php echo h($currency.' '.number_format((float)$scheduleTotals['due'],2));?></span></td>
    <td><?php echo h(date('d-M-Y',strtotime($r['start_date'])));?> <?php echo __('to');?> <?php echo h(date('d-M-Y',strtotime($r['end_date'])));?></td>
    <td><span class="wv-rental-status <?php echo h($r['status']);?>"><?php echo h($r['status']);?></span></td>
    <td><?php $portalAccess=!empty($r['client_portal_access'])?$r['client_portal_access']:'Unknown';?><span class="wv-rental-access <?php echo h($portalAccess);?>"><?php echo h($portalAccess);?></span></td>
    <td class="actions-col">
        <div class="wv-rental-actions">
            <div class="btn-group">
                <button type="button" class="btn btn-xs btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="<?php echo h(__('Actions'));?>"><i class="fa fa-cog"></i> <?php echo __('Action');?> <span class="caret"></span></button>
                <ul class="dropdown-menu dropdown-menu-right" role="menu">
                    <li><?php echo $this->Html->link('<i class="fa fa-eye"></i> '.__('View Rental'), array('action'=>'view',(int)$r['id']), array('escape'=>false));?></li>
                    <li><?php echo $this->Html->link('<i class="fa fa-money text-success"></i> '.__('Add Rent Payment'), array('action'=>'receipt',(int)$r['id']), array('escape'=>false));?></li>
                    <li><?php echo $this->Html->link('<i class="fa fa-print text-info"></i> '.__('Print Agreement'), array('action'=>'agreement',(int)$r['id']), array('escape'=>false,'target'=>'_blank'));?></li>
                    <li class="divider"></li>
                    <li><?php echo $this->Html->link('<i class="fa fa-edit text-warning"></i> '.__('Edit Rental'), array('action'=>'edit',(int)$r['id']), array('escape'=>false));?></li>
                    <li class="divider"></li>
                    <?php if($hasPaidRent){ ?>
                        <li class="disabled"><span class="wv-rental-delete-disabled" title="<?php echo h(__('Cannot delete: paid rent is recorded.'));?>"><i class="fa fa-trash"></i> <?php echo __('Delete Rental');?></span></li>
                    <?php } else { ?>
                        <li class="wv-rental-delete-item"><?php echo $this->Html->link('<i class="fa fa-trash text-danger"></i> '.__('Delete Rental'), array('action'=>'delete',(int)$r['id']), array('escape'=>false,'confirm'=>__('Delete this rental agreement?')));?></li>
                    <?php } ?>
                </ul>
            </div>
        </div>
    </td>
</tr>
<?php endforeach; ?>
</tbody>
<?php if(!empty($rentals)){ ?>
<tfoot><tr>
    <td colspan="4" class="wv-rental-total-label"><?php echo __('Showing Records Total');?></td>
    <td><span class="wv-rental-money"><?php echo h($currency.' '.number_format((float)$rentalPageTotals['monthly_rent'],2));?></span></td>
    <td><span class="wv-rental-money wv-rental-money-paid"><?php echo h($currency.' '.number_format((float)$rentalPageTotals['paid'],2));?></span></td>
    <td><span class="wv-rental-money wv-rental-money-due"><?php echo h($currency.' '.number_format((float)$rentalPageTotals['due'],2));?></span></td>
    <td colspan="4"></td>
</tr></tfoot>
<?php } ?>
</table>
</div>
<div class="text-center"><ul class="pagination"><?php echo $this->Paginator->prev('&laquo;', array('tag'=>'li','escape'=>false), '<a>&laquo;</a>', array('tag'=>'li','escape'=>false,'class'=>'disabled'));?><?php echo $this->Paginator->numbers(array('tag'=>'li','currentTag'=>'a','separator'=>'','currentClass'=>'active'));?><?php echo $this->Paginator->next('&raquo;', array('tag'=>'li','escape'=>false), '<a>&raquo;</a>', array('tag'=>'li','escape'=>false,'class'=>'disabled'));?></ul></div>
</div></div>
