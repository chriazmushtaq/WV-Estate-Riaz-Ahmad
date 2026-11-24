<?php
$property = isset($post['Property']) ? (array)$post['Property'] : array();
$historyRows = isset($historyRows) ? (array)$historyRows : array();
$ownerRows = isset($ownerRows) ? (array)$ownerRows : array();
$currency = isset($currency) ? $currency : '';
$view = $this;
$money = function($amount) use ($currency) {
    return $currency.number_format((float)$amount, 0);
};
$formatDate = function($dateValue) use ($view, $sysDay, $dateSep, $sysMonth, $sysYear) {
    if(empty($dateValue) || $dateValue === '0000-00-00' || $dateValue === '0000-00-00 00:00:00')
    {
        return '-';
    }
    return $view->Time->format($sysDay.$dateSep.$sysMonth.$dateSep.$sysYear, $dateValue);
};
$createdDate = !empty($property['created']) ? $property['created'] : (!empty($property['date']) ? $property['date'] : '');
$soldDate = '';
$firstOwner = '';
foreach($ownerRows as $ownerRow)
{
    if($soldDate === '' && !empty($ownerRow['Deal']['date']))
    {
        $soldDate = $ownerRow['Deal']['date'];
    }
    if($firstOwner === '' && !empty($ownerRow['Client']['name']))
    {
        $firstOwner = $ownerRow['Client']['name'];
    }
}
$eventsByDealId = array();
$propertyOnlyEvents = array();
$generalPropertyEventTypes = array(
    'price_changed'=>true,
    'deal_price_adjusted'=>true,
    'deal_schedule_adjusted'=>true
);
foreach($historyRows as $historyRow)
{
    $history = isset($historyRow['PropertyHistory']) ? (array)$historyRow['PropertyHistory'] : array();
    $dealId = !empty($history['deal_id']) ? (int)$history['deal_id'] : 0;
    $eventType = !empty($history['event_type']) ? (string)$history['event_type'] : '';
    if($dealId>0 && empty($generalPropertyEventTypes[$eventType]))
    {
        if(!isset($eventsByDealId[$dealId]))
        {
            $eventsByDealId[$dealId] = array();
        }
        $eventsByDealId[$dealId][] = $historyRow;
    }
    else
    {
        $propertyOnlyEvents[] = $historyRow;
    }
}
?>
<style type="text/css">
.property-history-shell { background:#f5f7fb; border:1px solid #d9e2ef; padding:16px; }
.property-history-card { background:#fff; border:1px solid #d8e2ee; border-radius:8px; box-shadow:0 8px 20px rgba(15,23,42,.06); margin-bottom:14px; overflow:hidden; }
.property-history-card h4 { margin:0; padding:13px 16px; border-bottom:1px solid #e5edf6; color:#0f172a; font-size:15px; font-weight:800; }
.property-history-body { padding:16px; }
.property-kpi-row { display:flex; flex-wrap:wrap; gap:10px; }
.property-kpi { flex:1 1 190px; border:1px solid #e2e8f0; border-radius:8px; padding:12px; background:#fbfdff; }
.property-kpi small { display:block; color:#64748b; font-weight:700; text-transform:uppercase; font-size:11px; }
.property-kpi strong { display:block; color:#0f172a; font-size:16px; margin-top:4px; }
.owner-timeline { list-style:none; padding:0; margin:0; position:relative; }
.owner-timeline li { border-left:3px solid #2563eb; padding:0 0 16px 14px; margin-left:8px; position:relative; }
.owner-timeline li:before { content:''; position:absolute; left:-8px; top:1px; width:13px; height:13px; border-radius:50%; background:#2563eb; border:2px solid #fff; box-shadow:0 0 0 1px #2563eb; }
.owner-timeline-title { font-weight:800; color:#0f172a; }
.owner-timeline-meta { color:#64748b; font-size:12px; margin-top:3px; }
.owner-purchase-date { display:inline-block; margin-top:5px; padding:4px 9px; border-radius:999px; background:#e8f7ef; color:#047857; font-size:12px; font-weight:800; }
.owner-event-box { margin-top:10px; border:1px solid #e2e8f0; border-radius:8px; overflow:hidden; background:#fbfdff; }
.owner-event-title { padding:8px 10px; background:#f1f5f9; color:#334155; font-weight:800; font-size:12px; text-transform:uppercase; }
.owner-event-box .table { margin-bottom:0; background:#fff; }
.owner-event-box th { background:#f8fafc; color:#475569; font-size:11px; text-transform:uppercase; }
.property-history-table .table { margin-bottom:0; }
.property-history-table th { background:#eef3f8; color:#0f172a; text-transform:uppercase; font-size:12px; font-weight:800; }
.property-event-badge { display:inline-block; padding:3px 8px; border-radius:999px; background:#e0ecff; color:#1d4ed8; font-size:11px; font-weight:800; }
</style>

<div class="page-title">
    <div class="title-env">
        <h1 class="title"><?php echo __('Property History');?></h1>
    </div>
</div>

<div class="panel">
    <div class="panel-heading">
        <div class="btn-group">
            <?php echo $this->Html->link('<span class="fa fa-arrow-left"></span>&nbsp;'.__('Back to Properties'),array('controller'=>'Properties','action'=>'index'),array('escape'=>false,'class'=>'btn btn-default'));?>
            <?php echo $this->Html->link('<span class="fa fa-edit"></span>&nbsp;'.__('Edit Property'),array('controller'=>'Properties','action'=>'edit',isset($property['id']) ? $property['id'] : 0),array('escape'=>false,'class'=>'btn btn-warning'));?>
        </div>
    </div>
    <div class="panel-body">
        <?php echo $this->Session->flash();?>
        <div class="property-history-shell">
            <div class="property-history-card">
                <h4><?php echo h(isset($property['name']) ? $property['name'] : __('Property'));?></h4>
                <div class="property-history-body">
                    <div class="property-kpi-row">
                        <div class="property-kpi"><small><?php echo __('Office ID');?></small><strong><?php echo h(isset($property['uniq_id']) ? $property['uniq_id'] : '-');?></strong></div>
                        <div class="property-kpi"><small><?php echo __('Create Date');?></small><strong><?php echo h($formatDate($createdDate));?></strong></div>
                        <div class="property-kpi"><small><?php echo __('Sold Date');?></small><strong><?php echo h($formatDate($soldDate));?></strong></div>
                        <div class="property-kpi"><small><?php echo __('First Owner');?></small><strong><?php echo $firstOwner !== '' ? h($firstOwner) : '-';?></strong></div>
                        <div class="property-kpi"><small><?php echo __('Current Price');?></small><strong><?php echo $money(isset($property['price']) ? $property['price'] : 0);?></strong></div>
                        <div class="property-kpi"><small><?php echo __('Status');?></small><strong><?php echo h(isset($property['status']) ? $property['status'] : '-');?></strong></div>
                    </div>
                </div>
            </div>

            <div class="property-history-card">
                <h4><?php echo __('Owner Milestones');?></h4>
                <div class="property-history-body">
                    <ul class="owner-timeline">
                        <?php foreach($ownerRows as $ownerRow){
                            $deal = isset($ownerRow['Deal']) ? (array)$ownerRow['Deal'] : array();
                            $client = isset($ownerRow['Client']) ? (array)$ownerRow['Client'] : array();
                            $dealStatus = !empty($deal['deal_status']) ? $deal['deal_status'] : 'Active';
                            $phone = !empty($client['mobile']) ? $client['mobile'] : (!empty($client['phone']) ? $client['phone'] : '');
                            $dealId = !empty($deal['id']) ? (int)$deal['id'] : 0;
                            $ownerEventRows = ($dealId>0 && !empty($eventsByDealId[$dealId])) ? (array)$eventsByDealId[$dealId] : array();
                        ?>
                        <li>
                            <div class="owner-timeline-title"><?php echo !empty($client['name']) ? h($client['name']) : __('Unknown Owner');?></div>
                            <div class="owner-purchase-date"><?php echo __('Purchase Date');?>: <?php echo h($formatDate(isset($deal['date']) ? $deal['date'] : ''));?></div>
                            <div class="owner-timeline-meta">
                                <?php echo __('Deal');?>: <?php echo h(!empty($deal['invoice_no']) ? $deal['invoice_no'] : ('#'.(isset($deal['id']) ? $deal['id'] : '')));?>
                                &nbsp; | &nbsp;<?php echo __('Status');?>: <span class="property-event-badge"><?php echo h($dealStatus);?></span>
                                <?php if($phone !== ''){ ?>&nbsp; | &nbsp;<?php echo __('Mobile');?>: <?php echo h($phone);?><?php } ?>
                            </div>
                            <div class="owner-timeline-meta">
                                <?php
                                $ownerPrice = isset($deal['amount']) ? $deal['amount'] : ((isset($deal['total_amount']) ? (float)$deal['total_amount'] : 0) + (isset($deal['discount']) ? (float)$deal['discount'] : 0));
                                ?>
                                <?php echo __('Price');?>: <?php echo $money($ownerPrice);?>
                                &nbsp; | &nbsp;<?php echo __('Discount');?>: <?php echo $money(isset($deal['discount']) ? $deal['discount'] : 0);?>
                                &nbsp; | &nbsp;<?php echo __('Total');?>: <?php echo $money(isset($deal['total_amount']) ? $deal['total_amount'] : 0);?>
                                <?php if(!empty($deal['deal_status_reason'])){ ?>&nbsp; | &nbsp;<?php echo __('Reason');?>: <?php echo h($deal['deal_status_reason']);?><?php } ?>
                            </div>
                            <div class="owner-event-box">
                                <div class="owner-event-title"><?php echo __('Recorded Ownership Events');?></div>
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered">
                                        <thead>
                                            <tr>
                                                <th><?php echo __('Date');?></th>
                                                <th><?php echo __('Event');?></th>
                                                <th><?php echo __('Old');?></th>
                                                <th><?php echo __('New');?></th>
                                                <th><?php echo __('Difference');?></th>
                                                <th><?php echo __('User');?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($ownerEventRows as $ownerEventRow){
                                                $ownerEvent = isset($ownerEventRow['PropertyHistory']) ? (array)$ownerEventRow['PropertyHistory'] : array();
                                            ?>
                                            <tr>
                                                <td><?php echo h($formatDate(isset($ownerEvent['created']) ? $ownerEvent['created'] : ''));?></td>
                                                <td><span class="property-event-badge"><?php echo h(!empty($ownerEvent['title']) ? $ownerEvent['title'] : $ownerEvent['event_type']);?></span></td>
                                                <td><?php echo trim((string)(isset($ownerEvent['old_value']) ? $ownerEvent['old_value'] : '')) !== '' ? h($ownerEvent['old_value']) : '-';?></td>
                                                <td><?php echo trim((string)(isset($ownerEvent['new_value']) ? $ownerEvent['new_value'] : '')) !== '' ? h($ownerEvent['new_value']) : '-';?></td>
                                                <td><?php echo !empty($ownerEvent['amount_delta']) ? $money($ownerEvent['amount_delta']) : '-';?></td>
                                                <td><?php echo !empty($ownerEventRow['Actor']['name']) ? h($ownerEventRow['Actor']['name']) : '-';?></td>
                                            </tr>
                                            <?php } ?>
                                            <?php if(empty($ownerEventRows)){ ?>
                                            <tr><td colspan="6" class="text-center"><?php echo __('No recorded ownership events for this owner yet.');?></td></tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </li>
                        <?php } ?>
                        <?php if(empty($ownerRows)){ ?>
                        <li><div class="owner-timeline-title"><?php echo __('No owner/deal milestone found.');?></div></li>
                        <?php } ?>
                    </ul>
                </div>
            </div>

            <div class="property-history-card property-history-table">
                <h4><?php echo __('General Property Events');?></h4>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th><?php echo __('#');?></th>
                                <th><?php echo __('Date');?></th>
                                <th><?php echo __('Event');?></th>
                                <th><?php echo __('Old');?></th>
                                <th><?php echo __('New');?></th>
                                <th><?php echo __('Difference');?></th>
                                <th><?php echo __('Deal');?></th>
                                <th><?php echo __('User');?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $sr=1; foreach($propertyOnlyEvents as $historyRow){
                                $history = isset($historyRow['PropertyHistory']) ? (array)$historyRow['PropertyHistory'] : array();
                            ?>
                            <tr>
                                <td><?php echo $sr++;?></td>
                                <td><?php echo h($formatDate(isset($history['created']) ? $history['created'] : ''));?></td>
                                <td><span class="property-event-badge"><?php echo h(!empty($history['title']) ? $history['title'] : $history['event_type']);?></span></td>
                                <td><?php echo trim((string)(isset($history['old_value']) ? $history['old_value'] : '')) !== '' ? h($history['old_value']) : '-';?></td>
                                <td><?php echo trim((string)(isset($history['new_value']) ? $history['new_value'] : '')) !== '' ? h($history['new_value']) : '-';?></td>
                                <td><?php echo !empty($history['amount_delta']) ? $money($history['amount_delta']) : '-';?></td>
                                <td><?php echo !empty($history['deal_id']) ? '#'.(int)$history['deal_id'] : '-';?></td>
                                <td><?php echo !empty($historyRow['Actor']['name']) ? h($historyRow['Actor']['name']) : '-';?></td>
                            </tr>
                            <?php } ?>
                            <?php if(empty($propertyOnlyEvents)){ ?>
                            <tr><td colspan="8" class="text-center"><?php echo __('No general property events yet. Owner events are shown below each owner milestone.');?></td></tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
