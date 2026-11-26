<?php
$requests = isset($requests) ? (array)$requests : array();
$stats = isset($stats) ? (array)$stats : array();
$statusOptions = isset($statusOptions) ? (array)$statusOptions : array();
$status = isset($status) ? (string)$status : '';
$dealOptions = isset($dealOptions) ? (array)$dealOptions : array();
$refundDateFormat = $sysDay.$dateSep.$sysMonth.$dateSep.$sysYear.(isset($sysHour) ? $dateGap.$sysHour.$timeSep.$sysMin.$dateGap.$sysMer : '');
$statusClass = function($value) {
    $key = strtolower(preg_replace('/[^a-z0-9]+/i', '-', (string)$value));
    return 'wv-refund-status-'.$key;
};
?>
<style type="text/css">
.wv-refund-page{background:transparent;margin:0;padding:0;touch-action:auto;overflow-x:auto;-webkit-overflow-scrolling:touch;width:100%;}
.wv-refund-page *{box-sizing:border-box;}
.wv-refund-hero{align-items:center;background:#ffffff;border:1px solid #e2e8f0;border-top:4px solid #0284c7;border-radius:14px;box-shadow:0 4px 16px rgba(15,23,42,.04);color:#0f172a;display:flex;gap:15px;justify-content:space-between;margin-bottom:14px;padding:20px 22px;}
.wv-refund-hero h1{color:#0f172a!important;font-size:26px;font-weight:900;margin:0 0 5px;}
.wv-refund-hero p{color:#475569!important;font-size:13px;font-weight:600;margin:0;}
.wv-refund-hero .btn{border:0;border-radius:8px;font-size:11px;font-weight:900;padding:9px 12px;text-transform:uppercase;}
.wv-refund-stats{display:grid;gap:10px;grid-template-columns:repeat(4,minmax(0,1fr));margin-bottom:12px;}
.wv-refund-stat{background:#fff;border:1px solid #dfe9f5;border-radius:10px;box-shadow:0 8px 20px rgba(15,23,42,.06);padding:12px;}
.wv-refund-stat span{color:#64748b;display:block;font-size:10px;font-weight:900;text-transform:uppercase;}
.wv-refund-stat strong{color:#07152a;display:block;font-size:22px;font-weight:900;margin-top:4px;}
.wv-refund-filter{background:#fff;border:1px solid #dfe9f5;border-radius:10px;margin-bottom:12px;padding:10px;}
.wv-refund-filter .form-control{border-radius:7px;font-weight:800;height:36px;margin-right:6px;}
.wv-refund-card{background:#fff;border:1px solid #dfe9f5;border-radius:10px;box-shadow:0 8px 20px rgba(15,23,42,.06);margin-bottom:10px;overflow:hidden;}
.wv-refund-card-head{align-items:flex-start;background:#ffffff;border-bottom:1px solid #e2e8f0;border-top:3px solid #0284c7;color:#0f172a;display:flex;gap:12px;justify-content:space-between;padding:12px 14px;}
.wv-refund-card-head h2{color:#0f172a!important;font-size:16px;font-weight:900;line-height:1.25;margin:0;}
.wv-refund-card-head small{color:#64748b!important;font-weight:700;}
.wv-refund-body{padding:12px 14px;}
.wv-refund-grid{display:grid;gap:8px;grid-template-columns:repeat(4,minmax(0,1fr));margin-bottom:10px;}
.wv-refund-box{background:#f8fbff;border:1px solid #e2eaf4;border-radius:8px;padding:9px;}
.wv-refund-box span{color:#64748b;display:block;font-size:10px;font-weight:900;text-transform:uppercase;}
.wv-refund-box strong{color:#07152a;display:block;font-size:13px;font-weight:900;margin-top:4px;word-break:break-word;}
.wv-refund-actions{display:flex;flex-wrap:wrap;gap:6px;}
.wv-refund-actions .btn{border-radius:7px;font-size:11px;font-weight:900;text-transform:uppercase;}
.wv-refund-status{border-radius:999px;display:inline-block;font-size:10px;font-weight:900;padding:6px 9px;text-transform:uppercase;white-space:nowrap;}
.wv-refund-status-pending{background:#fff1c2;color:#8a6100;}
.wv-refund-status-under-review{background:#e7f0ff;color:#174ea6;}
.wv-refund-status-approved{background:#dff7e8;color:#08753b;}
.wv-refund-status-rejected{background:#ffe4e6;color:#b91c1c;}
.wv-refund-status-cancelled{background:#eef2f7;color:#475569;}
.wv-refund-status-converted-to-refund{background:#dcfce7;color:#166534;}
.wv-refund-empty{background:#fff;border:1px dashed #cbd5e1;border-radius:10px;color:#64748b;font-size:14px;font-weight:900;padding:22px;text-align:center;}
@media(max-width:991px){.wv-refund-stats,.wv-refund-grid{grid-template-columns:repeat(2,minmax(0,1fr));}.wv-refund-hero{display:block}.wv-refund-hero .btn{margin-top:12px;width:100%;}.wv-refund-filter .form-control,.wv-refund-filter .btn{margin:0 0 6px;width:100%;}}
@media(max-width:520px){.wv-refund-page{margin:0;padding:10px}.wv-refund-stats,.wv-refund-grid{grid-template-columns:1fr}.wv-refund-card-head{display:block}.wv-refund-status{margin-top:8px}.wv-refund-actions .btn{width:100%;}}
</style>

<div class="wv-refund-page">
    <?php echo $this->Session->flash();?>
    <div class="wv-refund-hero">
        <div>
            <h1><?php echo __('Refund Requests');?></h1>
            <p><?php echo __('Apply online, track review status, and download submitted refund proof.');?></p>
        </div>
        <?php if(!empty($dealOptions)){ echo $this->Html->link('<span class="fa fa-plus"></span> '.__('Apply Refund'), array('controller' => 'MyRefundrequests', 'action' => 'apply'), array('class' => 'btn btn-success', 'escape' => false)); } ?>
    </div>

    <div class="wv-refund-stats">
        <div class="wv-refund-stat"><span><?php echo __('Total');?></span><strong><?php echo isset($stats['total']) ? (int)$stats['total'] : 0;?></strong></div>
        <div class="wv-refund-stat"><span><?php echo __('Pending Review');?></span><strong><?php echo isset($stats['pending']) ? (int)$stats['pending'] : 0;?></strong></div>
        <div class="wv-refund-stat"><span><?php echo __('Approved');?></span><strong><?php echo isset($stats['approved']) ? (int)$stats['approved'] : 0;?></strong></div>
        <div class="wv-refund-stat"><span><?php echo __('Converted');?></span><strong><?php echo isset($stats['converted']) ? (int)$stats['converted'] : 0;?></strong></div>
    </div>

    <div class="wv-refund-filter">
        <?php echo $this->Form->create(false, array('type' => 'get', 'url' => array('controller' => 'MyRefundrequests', 'action' => 'index'), 'class' => 'form-inline'));?>
        <?php echo $this->Form->select('status', $statusOptions, array('empty' => __('All Statuses'), 'value' => $status, 'class' => 'form-control'));?>
        <button type="submit" class="btn btn-info"><span class="fa fa-search"></span> <?php echo __('Filter');?></button>
        <?php echo $this->Html->link(__('Reset'), array('controller' => 'MyRefundrequests', 'action' => 'index'), array('class' => 'btn btn-default'));?>
        <?php echo $this->Form->end();?>
    </div>

    <?php if(empty($requests)){ ?>
        <div class="wv-refund-empty">
            <?php echo __('No refund requests found.');?>
            <?php if(!empty($dealOptions)){ ?>
                <br><br><?php echo $this->Html->link(__('Create your first refund request'), array('controller' => 'MyRefundrequests', 'action' => 'apply'), array('class' => 'btn btn-success'));?>
            <?php } ?>
        </div>
    <?php } ?>

    <?php foreach($requests as $row){
        $request = (array)$row['Dealrefundrequest'];
        $rid = (int)$request['id'];
        $currentStatus = isset($request['status']) ? $request['status'] : 'Pending';
        $statusUpdatedRaw = !empty($request['status_updated_at']) ? $request['status_updated_at'] : (!empty($request['reviewed_at']) ? $request['reviewed_at'] : (!empty($request['modified']) ? $request['modified'] : $request['created']));
        $statusUpdatedText = !empty($statusUpdatedRaw) ? $this->Time->format($refundDateFormat, $statusUpdatedRaw) : '-';
        $createdText = !empty($request['created']) ? $this->Time->format($refundDateFormat, $request['created']) : '-';
        $invoice = !empty($row['Deal']['invoice_no']) ? $row['Deal']['invoice_no'] : '#'.$request['deal_id'];
        $property = !empty($row['Property']['name']) ? $row['Property']['name'] : __('Property');
    ?>
        <div class="wv-refund-card">
            <div class="wv-refund-card-head">
                <div>
                    <h2><?php echo h($property);?></h2>
                    <small><?php echo __('Deal');?>: <?php echo h($invoice);?> | <?php echo __('Request');?>: <?php echo h(!empty($request['request_no']) ? $request['request_no'] : '#'.$rid);?></small>
                </div>
                <span class="wv-refund-status <?php echo $statusClass($currentStatus);?>"><?php echo h($currentStatus);?></span>
            </div>
            <div class="wv-refund-body">
                <div class="wv-refund-grid">
                    <div class="wv-refund-box"><span><?php echo __('Deal Paid Amount');?></span><strong><?php echo h($currency.' '.$this->Number->format((float)$request['requested_amount']));?></strong></div>
                    <div class="wv-refund-box"><span><?php echo __('Approved');?></span><strong><?php echo h($currency.' '.$this->Number->format((float)$request['approved_amount']));?></strong></div>
                    <div class="wv-refund-box"><span><?php echo __('Paid In Deal');?></span><strong><?php echo h($currency.' '.$this->Number->format((float)$request['total_paid']));?></strong></div>
                    <div class="wv-refund-box"><span><?php echo __('Request No');?></span><strong><?php echo h(!empty($request['request_no']) ? $request['request_no'] : '-');?></strong></div>
                    <div class="wv-refund-box"><span><?php echo __('Created');?></span><strong><?php echo h($createdText);?></strong></div>
                    <div class="wv-refund-box"><span><?php echo __('Status Updated');?></span><strong><?php echo h($statusUpdatedText);?></strong></div>
                </div>
                <div class="wv-refund-actions">
                    <?php echo $this->Html->link('<span class="fa fa-eye"></span> '.__('View'), array('controller' => 'MyRefundrequests', 'action' => 'view', $rid), array('class' => 'btn btn-info', 'escape' => false));?>
                    <?php if(!empty($request['attachment'])){ ?>
                        <?php echo $this->Html->link('<span class="fa fa-download"></span> '.__('Attachment'), array('controller' => 'MyRefundrequests', 'action' => 'download', $rid), array('class' => 'btn btn-default', 'escape' => false));?>
                    <?php } ?>
                    <?php if($currentStatus === 'Pending' || $currentStatus === 'Under Review'){ ?>
                        <?php echo $this->Form->postLink('<span class="fa fa-times"></span> '.__('Cancel'), array('controller' => 'MyRefundrequests', 'action' => 'cancel', $rid), array('class' => 'btn btn-danger', 'escape' => false, 'confirm' => __('Are you sure you want to cancel this refund request?')));?>
                    <?php } ?>
                    <?php if($currentStatus === 'Rejected'){ ?>
                        <?php echo $this->Html->link('<span class="fa fa-refresh"></span> '.__('Appeal'), array('controller' => 'MyRefundrequests', 'action' => 'appeal', $rid), array('class' => 'btn btn-warning', 'escape' => false));?>
                    <?php } ?>
                </div>
            </div>
        </div>
    <?php } ?>

    <?php if(!empty($requests)){ ?>
        <div class="text-center">
            <ul class="pagination">
                <?php echo $this->Paginator->prev('&laquo;', array('tag' => 'li', 'escape' => false), '<a href="javascript:void(0);">&laquo;</a>', array('tag' => 'li', 'escape' => false, 'class' => 'disabled'));?>
                <?php echo $this->Paginator->numbers(array('tag' => 'li', 'currentTag' => 'a', 'currentClass' => 'active', 'separator' => ''));?>
                <?php echo $this->Paginator->next('&raquo;', array('tag' => 'li', 'escape' => false), '<a href="javascript:void(0);">&raquo;</a>', array('tag' => 'li', 'escape' => false), array('tag' => 'li', 'escape' => false, 'class' => 'disabled'));?>
            </ul>
        </div>
    <?php } ?>
</div>
