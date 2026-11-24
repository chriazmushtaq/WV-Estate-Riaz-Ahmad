<?php
$p = $plan['Plan'];
$difference = round($planTotal-$scheduleTotal,2);
$isBalanced = !empty($scheduleRows) && abs($difference) <= 0.01;
?>
<style>
.plan-view{margin:0 auto;max-width:1500px}.plan-view-head{align-items:center;background:#102746;border-radius:8px;color:#fff;display:flex;justify-content:space-between;padding:18px 20px}.plan-view-head h2{color:#fff;font-size:20px;font-weight:800;margin:0}.plan-view-head p{color:#c5d5e8;margin:5px 0 0}.plan-view-actions{display:flex;gap:8px}.plan-view-actions .btn{border-radius:6px;font-weight:700}.plan-view-summary{display:grid;gap:12px;grid-template-columns:repeat(4,1fr);margin:14px 0}.plan-view-card{background:#fff;border:1px solid #d5e1ee;border-radius:8px;padding:15px}.plan-view-card small{color:#667a92;display:block;font-size:10px;font-weight:800;text-transform:uppercase}.plan-view-card strong{color:#102746;display:block;font-size:19px;margin-top:5px}.plan-view-card.match{background:#eaf8f3;border-color:#83d6b8}.plan-view-card.difference{background:#fff3e9;border-color:#f0bc89}.plan-view-table{background:#fff;border:1px solid #d5e1ee;border-radius:8px;overflow:hidden}.plan-view-table .table{margin:0}.plan-view-table th{background:#203a5c;color:#fff;font-size:11px;padding:11px;text-transform:uppercase}.plan-view-table td{padding:11px;vertical-align:middle}.plan-view-table tfoot td{background:#edf4fb;color:#102746;font-weight:800}.plan-empty{color:#667a92;padding:32px;text-align:center}@media(max-width:850px){.plan-view-head{align-items:flex-start;flex-direction:column;gap:14px}.plan-view-summary{grid-template-columns:1fr 1fr}.plan-view-table{overflow-x:auto}.plan-view-table .table{min-width:850px}}@media(max-width:500px){.plan-view-summary{grid-template-columns:1fr}.plan-view-actions{width:100%}.plan-view-actions .btn{flex:1}}
</style>
<div class="page-title"><div class="title-env"><h1 class="title"><?php echo __('View Payment Plan');?></h1></div></div>
<div class="plan-view">
    <?php echo $this->Session->flash();?>
    <div class="plan-view-head">
        <div>
            <h2><?php echo h($p['name']);?></h2>
            <p><?php echo __('Read-only payment schedule and calculated totals.');?></p>
        </div>
        <div class="plan-view-actions">
            <?php echo $this->Html->link('<span class="fa fa-edit"></span>&nbsp;'.__('Edit'),array('action'=>'edit',(int)$p['id']),array('escape'=>false,'class'=>'btn btn-info'));?>
            <?php echo $this->Html->link('<span class="fa fa-arrow-left"></span>&nbsp;'.__('Back'),array('action'=>'index'),array('escape'=>false,'class'=>'btn btn-default'));?>
        </div>
    </div>
    <div class="plan-view-summary">
        <div class="plan-view-card"><small><?php echo __('Total Payment');?></small><strong>Rs <?php echo CakeNumber::format($planTotal);?></strong></div>
        <div class="plan-view-card"><small><?php echo __('Scheduled Payments');?></small><strong><?php echo (int)$paymentCount;?></strong></div>
        <div class="plan-view-card"><small><?php echo __('Status');?></small><strong><?php echo h($p['status']);?></strong></div>
        <div class="plan-view-card <?php echo $isBalanced?'match':'difference';?>"><small><?php echo __('Schedule Difference');?></small><strong>Rs <?php echo CakeNumber::format($difference);?></strong></div>
    </div>
    <div class="plan-view-table">
        <?php if(empty($scheduleRows)):?>
            <div class="plan-empty"><span class="fa fa-info-circle"></span>&nbsp; <?php echo __('This is a legacy payment plan and has no detailed schedule template.');?></div>
        <?php else:?>
        <table class="table table-bordered table-striped">
            <thead><tr><th>#</th><th><?php echo __('Payment');?></th><th><?php echo __('Title');?></th><th><?php echo __('Amount As');?></th><th><?php echo __('Value');?></th><th><?php echo __('Payments');?></th><th><?php echo __('Every Months');?></th><th><?php echo __('Each Payment');?></th><th><?php echo __('Row Total');?></th></tr></thead>
            <tbody>
            <?php foreach($scheduleRows as $index=>$row):?>
                <tr>
                    <td><?php echo $index+1;?></td>
                    <td><?php echo h(Inflector::humanize($row['payment_type']));?></td>
                    <td><strong><?php echo h($row['title']);?></strong></td>
                    <td><?php echo $row['amount_mode']==='percentage'?__('Percentage'):__('Amount');?></td>
                    <td><?php echo $row['amount_mode']==='percentage'?h($row['amount_value']).'%':'Rs '.CakeNumber::format($row['amount_value']);?></td>
                    <td><?php echo (int)$row['repeat_count'];?></td>
                    <td><?php echo (int)$row['interval_months'];?></td>
                    <td>Rs <?php echo CakeNumber::format($row['unit_amount']);?></td>
                    <td><strong>Rs <?php echo CakeNumber::format($row['row_total']);?></strong></td>
                </tr>
            <?php endforeach;?>
            </tbody>
            <tfoot><tr><td colspan="8"><?php echo __('Schedule Total');?></td><td>Rs <?php echo CakeNumber::format($scheduleTotal);?></td></tr></tfoot>
        </table>
        <?php endif;?>
    </div>
</div>
