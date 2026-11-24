<?php
$requests = isset($requests) ? (array)$requests : array();
$stats = isset($stats) ? (array)$stats : array();
$statusOptions = isset($statusOptions) ? (array)$statusOptions : array();
$status = isset($status) ? (string)$status : '';
$keyword = isset($keyword) ? (string)$keyword : '';
$dealOptions = isset($dealOptions) ? (array)$dealOptions : array();
$eligibleDeals = isset($eligibleDeals) ? (array)$eligibleDeals : array();
$prefixSetting = isset($prefixSetting) ? (array)$prefixSetting : array();
$prefixProjectOptions = isset($prefixProjectOptions) ? (array)$prefixProjectOptions : array();
$refundDateFormat = $sysDay.$dateSep.$sysMonth.$dateSep.$sysYear.$dateGap.$sysHour.$timeSep.$sysMin.$dateGap.$sysMer;
?>
<style type="text/css">
.wv-refund-admin{color:#07152a;}
.wv-refund-admin-hero{background:linear-gradient(135deg,#07152a 0%,#123252 68%,#0f766e 130%);border-radius:8px;color:#fff;margin-bottom:14px;padding:18px 20px;}
.wv-refund-admin-hero h2{color:#fff!important;font-size:24px;font-weight:900;margin:0 0 4px;}
.wv-refund-admin-hero p{color:#dbeafe!important;font-size:13px;font-weight:800;margin:0;}
.wv-refund-admin-stats{display:grid;gap:10px;grid-template-columns:repeat(4,minmax(0,1fr));margin-bottom:14px;}
.wv-refund-admin-stat{background:#fff;border:1px solid #dbe5ef;border-radius:8px;box-shadow:0 10px 24px rgba(7,21,42,.06);padding:13px 14px;}
.wv-refund-admin-stat span{color:#64748b;display:block;font-size:11px;font-weight:900;text-transform:uppercase;}
.wv-refund-admin-stat strong{color:#0f766e;display:block;font-size:24px;font-weight:900;margin-top:4px;}
.wv-refund-admin-panel{background:#fff;border:1px solid #dbe5ef;border-radius:8px;margin-bottom:14px;padding:12px;}
.wv-refund-allow-grid{display:grid;gap:12px;grid-template-columns:minmax(0,1fr) minmax(0,1fr) minmax(0,1.2fr);}
.wv-refund-allow-form{background:#f8fbff;border:1px solid #e2eaf4;border-radius:8px;padding:12px;}
.wv-refund-allow-form label{color:#334155;font-size:11px;font-weight:900;text-transform:uppercase;}
.wv-refund-allow-form .form-control{border-radius:7px;font-weight:800;min-height:38px;}
.wv-refund-allow-list{background:#fbfdff;border:1px solid #e2eaf4;border-radius:8px;max-height:260px;overflow:auto;padding:10px;}
.wv-refund-allow-item{align-items:center;background:#fff;border:1px solid #e2eaf4;border-radius:8px;display:flex;gap:8px;justify-content:space-between;margin-bottom:7px;padding:9px;}
.wv-refund-allow-item strong{display:block;font-size:13px;font-weight:900;}
.wv-refund-allow-item small{color:#64748b;display:block;font-weight:800;}
.wv-refund-admin-filter{align-items:end;display:grid;gap:10px;grid-template-columns:220px 1fr 220px;}
.wv-refund-admin-filter label{color:#334155;font-size:11px;font-weight:900;text-transform:uppercase;}
.wv-refund-admin-filter .form-control{border-radius:7px;font-weight:800;height:38px;}
.wv-refund-status{border-radius:999px;display:inline-block;font-size:10px;font-weight:900;padding:6px 9px;text-transform:uppercase;white-space:nowrap;}
.wv-refund-status-pending{background:#fff1c2;color:#8a6100;}
.wv-refund-status-under-review{background:#e7f0ff;color:#174ea6;}
.wv-refund-status-approved{background:#dff7e8;color:#08753b;}
.wv-refund-status-rejected{background:#ffe4e6;color:#b91c1c;}
.wv-refund-status-cancelled{background:#eef2f7;color:#475569;}
.wv-refund-status-converted-to-refund{background:#dcfce7;color:#166534;}
.wv-refund-admin-table th{background:#07152a;color:#fff;font-size:11px;text-transform:uppercase;}
.wv-refund-admin-table td{vertical-align:middle!important;}
.wv-refund-client-cell strong{display:block;font-weight:900;}
.wv-refund-client-cell small{color:#64748b;font-weight:800;}
.wv-refund-admin-actions{white-space:nowrap;}
.wv-refund-admin-actions .btn{margin:1px;}
.wv-refund-admin-actions .dropdown-menu{border-radius:8px;min-width:210px;padding:6px;}
.wv-refund-admin-actions .dropdown-menu li a{font-size:12px;font-weight:900;padding:8px 10px;text-transform:uppercase;}
@media(max-width:991px){.wv-refund-admin-stats{grid-template-columns:repeat(2,minmax(0,1fr));}.wv-refund-allow-grid,.wv-refund-admin-filter{grid-template-columns:1fr;}.wv-refund-admin-actions{white-space:normal;}}
@media(max-width:520px){.wv-refund-admin-stats{grid-template-columns:1fr;}}
</style>

<div class="wv-refund-admin">
    <?php echo $this->Session->flash();?>

    <div class="wv-refund-admin-hero">
        <h2><?php echo __('Refund Applications');?></h2>
        <p><?php echo __('Client online refund requests for society/admin review. Approved requests can be converted into the existing deal refund schedule.');?></p>
    </div>

    <div class="wv-refund-admin-stats">
        <div class="wv-refund-admin-stat"><span><?php echo __('Total');?></span><strong><?php echo isset($stats['total']) ? (int)$stats['total'] : 0;?></strong></div>
        <div class="wv-refund-admin-stat"><span><?php echo __('Pending Review');?></span><strong><?php echo isset($stats['pending']) ? (int)$stats['pending'] : 0;?></strong></div>
        <div class="wv-refund-admin-stat"><span><?php echo __('Approved');?></span><strong><?php echo isset($stats['approved']) ? (int)$stats['approved'] : 0;?></strong></div>
        <div class="wv-refund-admin-stat"><span><?php echo __('Converted');?></span><strong><?php echo isset($stats['converted']) ? (int)$stats['converted'] : 0;?></strong></div>
    </div>

    <div class="wv-refund-admin-panel">
        <h4 style="font-weight:900;margin:0 0 10px;"><span class="fa fa-check-square-o"></span> <?php echo __('Client Refund Apply Allowed Deals');?></h4>
        <div class="wv-refund-allow-grid">
            <div class="wv-refund-allow-form">
                <?php echo $this->Form->create('Deal', array('url'=>array('controller'=>'Dealrefundrequests','action'=>'allowdeal')));?>
                <label><?php echo __('Select Deal');?></label>
                <?php echo $this->Form->select('deal_id', $dealOptions, array('empty'=>__('Please Select Deal'), 'class'=>'form-control'));?>
                <br>
                <button type="submit" class="btn btn-success btn-block"><span class="fa fa-check"></span> <?php echo __('Allow Client To Apply Refund');?></button>
                <?php echo $this->Form->end();?>
            </div>
            <div class="wv-refund-allow-form">
                <?php echo $this->Form->create('Ardsetting', array('url'=>array('controller'=>'Dealrefundrequests','action'=>'saveprefix')));?>
                <?php if(!empty($prefixProjectOptions)){ ?>
                    <label><?php echo __('Agency');?></label>
                    <?php echo $this->Form->select('project_id', $prefixProjectOptions, array('class'=>'form-control', 'value'=>isset($prefixSetting['project_id']) ? (int)$prefixSetting['project_id'] : 0, 'empty'=>__('Select Agency')));?>
                    <br>
                <?php } else { ?>
                    <?php echo $this->Form->hidden('project_id', array('value'=>isset($prefixSetting['project_id']) ? (int)$prefixSetting['project_id'] : 0));?>
                <?php } ?>
                <label><?php echo __('Refund Request Prefix');?></label>
                <?php echo $this->Form->text('refund_request_prefix', array('class'=>'form-control', 'value'=>isset($prefixSetting['refund_request_prefix']) ? $prefixSetting['refund_request_prefix'] : 'RFR-'));?>
                <br>
                <label><?php echo __('Next Automation No');?></label>
                <?php echo $this->Form->text('refund_request_next_no', array('class'=>'form-control', 'value'=>isset($prefixSetting['refund_request_next_no']) ? (int)$prefixSetting['refund_request_next_no'] : 1));?>
                <br>
                <button type="submit" class="btn btn-primary btn-block"><span class="fa fa-save"></span> <?php echo __('Save Agency Prefix');?></button>
                <?php echo $this->Form->end();?>
            </div>
            <div class="wv-refund-allow-list">
                <?php if(empty($eligibleDeals)){ ?>
                    <div class="text-muted" style="font-weight:800;"><?php echo __('No deals are currently enabled for client refund application.');?></div>
                <?php } ?>
                <?php foreach($eligibleDeals as $dealRow){
                    $deal = !empty($dealRow['Deal']) ? (array)$dealRow['Deal'] : array();
                    $dealId = !empty($deal['id']) ? (int)$deal['id'] : 0;
                    $dealTitle = !empty($deal['invoice_no']) ? $deal['invoice_no'] : 'Deal #'.$dealId;
                    $meta = array();
                    if(!empty($dealRow['Client']['name'])){$meta[] = $dealRow['Client']['name'];}
                    if(!empty($dealRow['Property']['name'])){$meta[] = $dealRow['Property']['name'];}
                    if(!empty($dealRow['Project']['name'])){$meta[] = $dealRow['Project']['name'];}
                ?>
                    <div class="wv-refund-allow-item">
                        <div>
                            <strong><?php echo h($dealTitle);?></strong>
                            <small><?php echo h(implode(' - ', $meta));?></small>
                            <?php if(!empty($deal['client_refund_allowed_at'])){ ?><small><?php echo __('Allowed');?>: <?php echo h($deal['client_refund_allowed_at']);?></small><?php } ?>
                        </div>
                        <?php echo $this->Form->postLink('<span class="fa fa-times"></span>', array('controller'=>'Dealrefundrequests','action'=>'disallowdeal',$dealId), array('escape'=>false,'class'=>'btn btn-xs btn-danger','title'=>__('Remove From Client List'),'confirm'=>__('Remove this deal from client refund application list?')));?>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>

    <div class="wv-refund-admin-panel">
        <form method="get" action="<?php echo $this->Html->url(array('controller'=>'Dealrefundrequests','action'=>'index'));?>">
            <div class="wv-refund-admin-filter">
                <div>
                    <label><?php echo __('Status');?></label>
                    <select name="status" class="form-control">
                        <option value=""><?php echo __('All Statuses');?></option>
                        <?php foreach($statusOptions as $statusKey => $statusLabel){ ?>
                            <option value="<?php echo h($statusKey);?>" <?php echo (string)$status === (string)$statusKey ? 'selected="selected"' : '';?>><?php echo h($statusLabel);?></option>
                        <?php } ?>
                    </select>
                </div>
                <div>
                    <label><?php echo __('Keyword');?></label>
                    <input type="text" name="keyword" class="form-control" value="<?php echo h($keyword);?>" placeholder="<?php echo h(__('Client, mobile, CNIC, deal, property'));?>">
                </div>
                <div>
                    <button type="submit" class="btn btn-primary btn-block"><span class="fa fa-search"></span> <?php echo __('Search');?></button>
                    <?php echo $this->Html->link(__('Reset'), array('controller'=>'Dealrefundrequests','action'=>'index'), array('class'=>'btn btn-default btn-block', 'style'=>'margin-top:6px;'));?>
                </div>
            </div>
        </form>
    </div>

    <div class="wv-refund-admin-panel">
        <div class="table-responsive">
            <table class="table table-bordered table-striped wv-refund-admin-table">
                <thead>
                    <tr>
                        <th><?php echo __('#');?></th>
                        <th><?php echo __('Date');?></th>
                        <th><?php echo __('Request No');?></th>
                        <th><?php echo __('Client');?></th>
                        <th><?php echo __('Father / Guardian');?></th>
                        <th><?php echo __('Deal / Property');?></th>
                        <th><?php echo __('Deal Paid Amount');?></th>
                        <th><?php echo __('Approved');?></th>
                        <th><?php echo __('Status');?></th>
                        <th><?php echo __('Status Updated');?></th>
                        <th><?php echo __('Actions');?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($requests)){ ?>
                        <tr><td colspan="11" class="text-center"><?php echo __('No refund applications found.');?></td></tr>
                    <?php } ?>
                    <?php foreach($requests as $row){
                        $request = !empty($row['Dealrefundrequest']) ? (array)$row['Dealrefundrequest'] : array();
                        $rid = !empty($request['id']) ? (int)$request['id'] : 0;
                        $statusText = !empty($request['status']) ? $request['status'] : 'Pending';
                        $statusClass = 'wv-refund-status-'.strtolower(preg_replace('/[^a-z0-9]+/i','-',$statusText));
                        $statusUpdatedRaw = !empty($request['status_updated_at']) ? $request['status_updated_at'] : (!empty($request['reviewed_at']) ? $request['reviewed_at'] : (!empty($request['modified']) ? $request['modified'] : $request['created']));
                        $statusUpdatedText = !empty($statusUpdatedRaw) ? $this->Time->format($refundDateFormat, $statusUpdatedRaw) : '-';
                        $clientName = !empty($request['applicant_name']) ? $request['applicant_name'] : (!empty($row['Client']['name']) ? $row['Client']['name'] : __('Client'));
                        $clientContact = trim((!empty($request['mobile_no']) ? $request['mobile_no'] : (!empty($row['Client']['mobile']) ? $row['Client']['mobile'] : '')).' '.(!empty($request['email']) ? $request['email'] : (!empty($row['Client']['email']) ? $row['Client']['email'] : '')));
                        $dealLabel = !empty($row['Deal']['invoice_no']) ? $row['Deal']['invoice_no'] : '#'.(!empty($request['deal_id']) ? $request['deal_id'] : '');
                        $propertyLabel = !empty($row['Property']['name']) ? $row['Property']['name'] : '';
                    ?>
                        <tr>
                            <td><?php echo $rid;?></td>
                            <td><?php echo h(!empty($request['created']) ? $this->Time->format($sysDay.$dateSep.$sysMonth.$dateSep.$sysYear, $request['created']) : '-');?></td>
                            <td><strong><?php echo h(!empty($request['request_no']) ? $request['request_no'] : '-');?></strong></td>
                            <td class="wv-refund-client-cell">
                                <strong><?php echo h($clientName);?></strong>
                                <?php if($clientContact !== ''){ ?><small><?php echo h($clientContact);?></small><?php } ?>
                            </td>
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
                            <td>
                                <strong><?php echo h($dealLabel);?></strong>
                                <?php if($propertyLabel !== ''){ ?><br><small><?php echo h($propertyLabel);?></small><?php } ?>
                                <?php if(!empty($row['Project']['name'])){ ?><br><small><?php echo h($row['Project']['name']);?></small><?php } ?>
                            </td>
                            <td><?php echo h($currency.' '.$this->Number->format((float)$request['requested_amount']));?></td>
                            <td><?php echo h($currency.' '.$this->Number->format((float)$request['approved_amount']));?></td>
                            <td><span class="wv-refund-status <?php echo h($statusClass);?>"><?php echo h($statusText);?></span></td>
                            <td><?php echo h($statusUpdatedText);?></td>
                            <td class="wv-refund-admin-actions">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">
                                        <span class="fa fa-cogs"></span> <?php echo __('Actions');?> <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-right">
                                        <li><?php echo $this->Html->link('<span class="fa fa-eye"></span> '.__('View'), array('controller'=>'Dealrefundrequests','action'=>'view',$rid), array('escape'=>false));?></li>
                                        <?php if(!empty($request['attachment'])){ ?>
                                            <li><?php echo $this->Html->link('<span class="fa fa-download"></span> '.__('Attachment'), array('controller'=>'Dealrefundrequests','action'=>'download',$rid), array('escape'=>false));?></li>
                                        <?php } ?>
                                        <?php if(!in_array($statusText, array('Cancelled','Rejected','Converted To Refund'), true)){ ?>
                                            <li role="separator" class="divider"></li>
                                            <li><?php echo $this->Html->link('<span class="fa fa-search"></span> '.__('Mark Under Review'), array('controller'=>'Dealrefundrequests','action'=>'reviewform',$rid), array('escape'=>false));?></li>
                                            <li><?php echo $this->Html->link('<span class="fa fa-check"></span> '.__('Approve Request'), array('controller'=>'Dealrefundrequests','action'=>'approveform',$rid), array('escape'=>false));?></li>
                                        <?php } ?>
                                        <?php if($statusText !== 'Converted To Refund'){ ?>
                                            <li><?php echo $this->Html->link('<span class="fa fa-times"></span> '.__('Reject Request'), array('controller'=>'Dealrefundrequests','action'=>'rejectform',$rid), array('escape'=>false));?></li>
                                        <?php } ?>
                                        <?php if($statusText === 'Approved' && empty($request['converted_refund_id'])){ ?>
                                            <li role="separator" class="divider"></li>
                                            <li><?php echo $this->Html->link('<span class="fa fa-share"></span> '.__('Create Deal Refund'), array('controller'=>'Dealrefundrequests','action'=>'convertform',$rid), array('escape'=>false));?></li>
                                        <?php } ?>
                                        <?php if(!empty($request['converted_refund_id'])){ ?>
                                            <li role="separator" class="divider"></li>
                                            <li><?php echo $this->Html->link('<span class="fa fa-undo"></span> '.__('Refund'), array('controller'=>'Dealrefunds','action'=>'view',(int)$request['converted_refund_id']), array('escape'=>false));?></li>
                                        <?php } ?>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <?php if(!empty($requests)){ ?>
            <div class="text-center">
                <ul class="pagination">
                    <?php echo $this->Paginator->prev('&laquo;', array('tag'=>'li','escape'=>false), '<a href="javascript:void(0);">&laquo;</a>', array('tag'=>'li','escape'=>false,'class'=>'disabled'));?>
                    <?php echo $this->Paginator->numbers(array('tag'=>'li','currentTag'=>'a','currentClass'=>'active','separator'=>''));?>
                    <?php echo $this->Paginator->next('&raquo;', array('tag'=>'li','escape'=>false), '<a href="javascript:void(0);">&raquo;</a>', array('tag'=>'li','escape'=>false,'class'=>'disabled'));?>
                </ul>
            </div>
        <?php } ?>
    </div>
</div>
