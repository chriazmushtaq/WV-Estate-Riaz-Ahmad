<?php
$statusOptions = isset($statusOptions) ? $statusOptions : array();
$typeOptions = isset($typeOptions) ? $typeOptions : array();
$isSuperAdminSession = !empty($isSuperAdminSession);
$canEditDealTransferFee = !empty($canEditDealTransferFee) || !empty($allowEditAction) || !empty($userPermissionArr['update_right']);
$currency = isset($currency) ? $currency : '';
?>
<style type="text/css">
.dt-index-shell {
    background: #f4f7fb;
    border: 1px solid #dbe6f3;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 25px;
}
.dt-page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 18px;
    padding-bottom: 15px;
    border-bottom: 1px solid #e2e8f0;
}
.dt-page-header h1 {
    margin: 0;
    font-size: 22px;
    font-weight: 800;
    color: #08213f;
    letter-spacing: -0.3px;
}
.dt-page-header p {
    margin: 4px 0 0;
    font-size: 13px;
    color: #64748b;
}
.dt-filter-bar {
    background: #ffffff;
    border: 1px solid #dbe6f3;
    border-radius: 8px;
    padding: 12px 16px;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    box-shadow: 0 2px 6px rgba(15,35,58,.03);
}
.dt-table-card {
    background: #ffffff;
    border: 1px solid #dbe6f3;
    border-radius: 8px;
    box-shadow: 0 4px 14px rgba(15,35,58,.04);
    overflow: hidden;
}
.dt-table-card table {
    margin-bottom: 0;
}
.dt-table-card thead th {
    background: #f8fbff;
    color: #334155;
    font-size: 12px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    border-bottom: 2px solid #e2e8f0;
    padding: 10px 12px;
}
.dt-table-card tbody td {
    padding: 10px 12px;
    vertical-align: middle;
    font-size: 13px;
}
.dt-fee-form {
    display: flex;
    gap: 4px;
    align-items: center;
    min-width: 170px;
}
.dt-fee-form .form-control {
    width: 110px;
    height: 28px;
    padding: 3px 6px;
    font-weight: 700;
}
.dt-action-wrap {
    white-space: nowrap;
    position: relative;
}
.dt-action-menu {
    left: auto;
    right: 0;
    min-width: 190px;
}
.dt-action-menu > li > a, .dt-action-menu > li > span {
    display: block;
    padding: 7px 14px;
    clear: both;
    font-weight: 500;
    line-height: 1.42857143;
    white-space: nowrap;
}
.dt-action-menu > li > span {
    color: #999;
    cursor: not-allowed;
}
@media (min-width: 768px) {
    .dt-transfer-table-wrap.table-responsive {
        overflow: visible;
    }
}
.dt-fee-status {
    display: block;
    margin-top: 4px;
}
.dt-type-pill {
    display: inline-block;
    padding: 3px 7px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
}
.dt-type-pill.transfer { background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; }
.dt-type-pill.merge { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
</style>

<div class="dt-index-shell">
    <div class="dt-page-header">
        <div>
            <h1><i class="fa fa-exchange" style="color:#0b63ce;margin-right:6px;"></i> <?php echo __('Deal Transfers & Mergings');?></h1>
            <p><?php echo __('Monitor ownership reassignment, payment mergers, processing fee vouchers, and legal agreements.');?></p>
        </div>
        <div style="display:flex;gap:8px;">
            <?php echo $this->Html->link('<span class="fa fa-plus"></span> '.__('New Transfer / Merge'), array('controller'=>'Dealtransfers','action'=>'add'), array('escape'=>false,'class'=>'btn btn-success','style'=>'font-weight:800;padding:8px 16px;'));?>
            <?php echo $this->Html->link('<span class="fa fa-sliders"></span> '.__('Settings'), array('controller'=>'Dealtransfers','action'=>'settings'), array('escape'=>false,'class'=>'btn btn-info','style'=>'font-weight:700;padding:8px 16px;'));?>
        </div>
    </div>

    <?php echo $this->Session->flash();?>

    <div class="dt-filter-bar">
        <?php echo $this->Form->create('Filter', array('type'=>'get','class'=>'form-inline','style'=>'display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin:0;'));?>
            <label style="font-size:12px;font-weight:700;color:#475569;text-transform:uppercase;margin:0;"><i class="fa fa-filter"></i> <?php echo __('Filter Records:');?></label>
            <?php echo $this->Form->select('type', $typeOptions, array('empty'=>__('All Transfer Types'),'class'=>'form-control input-sm','value'=>isset($this->request->query['type']) ? $this->request->query['type'] : '','style'=>'font-weight:600;min-width:160px;'));?>
            <?php echo $this->Form->select('status', $statusOptions, array('empty'=>__('All Statuses'),'class'=>'form-control input-sm','value'=>isset($this->request->query['status']) ? $this->request->query['status'] : '','style'=>'font-weight:600;min-width:140px;'));?>
            <button type="submit" class="btn btn-primary btn-sm" style="font-weight:700;"><span class="fa fa-search"></span> <?php echo __('Apply Filter');?></button>
            <?php if(!empty($this->request->query['type']) || !empty($this->request->query['status'])){ ?>
                <?php echo $this->Html->link('<span class="fa fa-times"></span> '.__('Reset'), array('controller'=>'Dealtransfers','action'=>'index'), array('escape'=>false,'class'=>'btn btn-default btn-sm'));?>
            <?php } ?>
        <?php echo $this->Form->end();?>
    </div>

    <div class="dt-table-card">
        <div class="table-responsive dt-transfer-table-wrap">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th><?php echo __('Transfer #');?></th>
                        <th><?php echo __('Date');?></th>
                        <th><?php echo __('Type');?></th>
                        <th><?php echo __('Source Deal');?></th>
                        <th><?php echo __('Source Client');?></th>
                        <th><?php echo __('Father / Guardian');?></th>
                        <th><?php echo __('Target');?></th>
                        <th><?php echo __('Agency Scope');?></th>
                        <th><?php echo __('Fee Payable');?></th>
                        <th><?php echo __('Status');?></th>
                        <th style="width:100px;text-align:center;"><?php echo __('Actions');?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($transferRows)){ ?>
                        <tr><td colspan="11" class="text-center" style="padding:30px;color:#64748b;font-weight:600;"><?php echo __('No deal transfer records found.');?></td></tr>
                    <?php } ?>
                    <?php foreach((array)$transferRows as $row){
                        $t = $row['Dealtransfer'];
                        $feeVoucherTxId = !empty($t['fee_voucher_transaction_id']) ? (int)$t['fee_voucher_transaction_id'] : 0;
                        $feeVoucherExists = !empty($t['fee_voucher_exists']);
                        $feeAmount = (float)$t['processing_fee'];
                        $feeIsPaid = $feeVoucherTxId > 0 && $feeVoucherExists;
                        $canEditFee = $canEditDealTransferFee && $t['status'] === 'Draft' && !$feeIsPaid;
                        $feePaymentPending = $t['status'] === 'Draft' && $feeAmount > 0 && !$feeIsPaid;
                        $feeVoucherProjectId = (!empty($t['transfer_type']) && $t['transfer_type'] === 'Merge' && !empty($t['target_project_id'])) ? (int)$t['target_project_id'] : (int)$t['source_project_id'];
                        $sourceDealNo = !empty($row['SourceDeal']['invoice_no']) ? $row['SourceDeal']['invoice_no'] : '#'.(int)$t['source_deal_id'];
                        $targetLabel = '-';
                        if($t['transfer_type'] === 'Merge')
                        {
                            $targetLabel = !empty($row['TargetDeal']['invoice_no']) ? $row['TargetDeal']['invoice_no'] : '#'.(int)$t['target_deal_id'];
                        }
                        elseif(!empty($row['TargetClient']['name']))
                        {
                            $targetLabel = $row['TargetClient']['name'];
                        }
                        $isMergeType = ($t['transfer_type'] === 'Merge');
                    ?>
                    <tr>
                        <td>
                            <strong style="color:#08213f;font-size:13px;"><?php echo h($t['transfer_no']);?></strong>
                        </td>
                        <td><?php echo !empty($t['request_date']) ? date('d-M-Y', strtotime($t['request_date'])) : '-';?></td>
                        <td>
                            <span class="dt-type-pill <?php echo $isMergeType ? 'merge' : 'transfer';?>">
                                <?php echo $isMergeType ? '<i class="fa fa-code-fork"></i> Merge' : '<i class="fa fa-user-plus"></i> Transfer';?>
                            </span>
                        </td>
                        <td><strong><?php echo h($sourceDealNo);?></strong></td>
                        <td><?php echo h(isset($row['SourceClient']['name']) ? $row['SourceClient']['name'] : '');?></td>
                        <td>
                            <?php
                            $fatherName = !empty($row['SourceClient']['fathername']) ? trim((string)$row['SourceClient']['fathername']) : '';
                            $relationType = !empty($row['SourceClient']['relation_type']) ? trim((string)$row['SourceClient']['relation_type']) : '';
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
                        <td><strong><?php echo h($targetLabel);?></strong></td>
                        <td>
                            <small class="text-muted">
                                <?php echo h((isset($row['SourceAgency']['name']) ? $row['SourceAgency']['name'] : '').' -> '.(isset($row['TargetAgency']['name']) ? $row['TargetAgency']['name'] : ''));?>
                            </small>
                        </td>
                        <td>
                            <?php if($canEditFee){ ?>
                                <?php echo $this->Form->create('Dealtransfer', array('url'=>array('controller'=>'Dealtransfers','action'=>'updatefee',(int)$t['id']),'class'=>'dt-fee-form'));?>
                                    <?php echo $this->Form->input('processing_fee', array('type'=>'number','step'=>'0.01','min'=>'0','label'=>false,'div'=>false,'class'=>'form-control input-sm','value'=>number_format($feeAmount, 2, '.', '')));?>
                                    <button type="submit" class="btn btn-xs btn-primary"><span class="fa fa-save"></span> <?php echo __('Save');?></button>
                                <?php echo $this->Form->end();?>
                            <?php } else { ?>
                                <strong><?php echo h($currency.' '.number_format($feeAmount, 2));?></strong>
                                <?php if($feeIsPaid){ ?><span class="label label-success dt-fee-status"><?php echo __('Paid / Locked');?></span><?php } ?>
                            <?php } ?>
                        </td>
                        <td>
                            <span class="label label-<?php echo $t['status']==='Finalized' ? 'success' : ($t['status']==='Rejected' ? 'danger' : ($t['status']==='Reversed' ? 'default' : 'warning'));?>" style="font-weight:800;font-size:11px;">
                                <?php echo h($t['status']);?>
                            </span>
                        </td>
                        <td class="dt-action-wrap text-center">
                            <div class="btn-group">
                                <button type="button" class="btn btn-xs btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="font-weight:700;">
                                    <span class="fa fa-bars"></span> <?php echo __('Actions');?> <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu dt-action-menu" role="menu">
                                    <li><?php echo $this->Html->link('<span class="fa fa-eye"></span> '.__('View Record'), array('controller'=>'Dealtransfers','action'=>'view',(int)$t['id']), array('escape'=>false));?></li>
                                    <?php if($feeIsPaid){ ?>
                                        <li><?php echo $this->Html->link('<span class="fa fa-print"></span> '.__('Paid Voucher'), array('controller'=>'Ardaccounts','action'=>'printvoucher',$feeVoucherTxId,$feeVoucherProjectId), array('escape'=>false,'target'=>'_blank'));?></li>
                                    <?php } elseif($t['status'] !== 'Rejected' && $t['status'] !== 'Reversed' && $feeAmount > 0){ ?>
                                        <li><?php echo $this->Html->link('<span class="fa fa-print"></span> '.__('Print Fee Voucher'), array('controller'=>'Dealtransfers','action'=>'printfeevoucher',(int)$t['id']), array('escape'=>false,'target'=>'_blank'));?></li>
                                        <li><?php echo $this->Html->link('<span class="fa fa-money"></span> '.__('Pay Fee (Post Voucher)'), array('controller'=>'Dealtransfers','action'=>'payfeevoucher',(int)$t['id']), array('escape'=>false,'confirm'=>__('Pay processing fee voucher now with the current amount?')));?></li>
                                    <?php } ?>
                                    <?php if($t['status'] === 'Finalized'){ ?>
                                        <li class="divider"></li>
                                        <li><?php echo $this->Html->link('<span class="fa fa-file-text-o"></span> '.__('Print Agreement'), array('controller'=>'Dealtransfers','action'=>'agreement',(int)$t['id']), array('escape'=>false,'target'=>'_blank'));?></li>
                                        <?php if(!empty($isSuperAdminSession)){ ?>
                                            <li><?php echo $this->Html->link('<span class="fa fa-undo"></span> '.__('Reverse Transfer'), array('controller'=>'Dealtransfers','action'=>'reverse',(int)$t['id']), array('escape'=>false,'confirm'=>__('Reverse this finalized transfer/merge and restore previous states?')));?></li>
                                        <?php } ?>
                                    <?php } ?>
                                    <?php if($t['status'] === 'Draft'){ ?>
                                        <li class="divider"></li>
                                        <li><?php echo $this->Html->link('<span class="fa fa-edit"></span> '.__('Edit Draft'), array('controller'=>'Dealtransfers','action'=>'edit',(int)$t['id']), array('escape'=>false));?></li>
                                        <?php if($feePaymentPending){ ?>
                                            <li class="disabled"><span title="<?php echo __('Pay processing fee before finalization.');?>"><span class="fa fa-lock"></span> <?php echo __('Fee Pending');?></span></li>
                                        <?php } else { ?>
                                            <li><?php echo $this->Html->link('<span class="fa fa-check"></span> '.__('Finalize Deal Transfer'), array('controller'=>'Dealtransfers','action'=>'finalize',(int)$t['id']), array('escape'=>false,'confirm'=>__('Finalize this deal transfer now?')));?></li>
                                        <?php } ?>
                                        <li><?php echo $this->Html->link('<span class="fa fa-ban"></span> '.__('Reject Request'), array('controller'=>'Dealtransfers','action'=>'reject',(int)$t['id']), array('escape'=>false,'confirm'=>__('Reject this request?')));?></li>
                                    <?php } ?>
                                    <?php if($t['status'] === 'Draft' || $t['status'] === 'Reversed'){ ?>
                                        <li class="divider"></li>
                                        <li><?php echo $this->Html->link('<span class="fa fa-trash"></span> '.__('Delete Record'), array('controller'=>'Dealtransfers','action'=>'delete',(int)$t['id']), array('escape'=>false,'confirm'=>__('Delete this deal transfer record?')));?></li>
                                    <?php } ?>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <?php if(!empty($transferRows)){ ?>
        <div style="padding:15px;text-align:center;">
            <ul class="pagination" style="margin:0;">
                <?php echo $this->Paginator->prev('&laquo;', array('tag'=>'li','escape'=>false), '<a href="javascript:void(0);">&laquo;</a>', array('tag'=>'li','escape'=>false,'class'=>'disabled'));?>
                <?php echo $this->Paginator->numbers(array('tag'=>'li','currentTag'=>'a','currentClass'=>'active','separator'=>''));?>
                <?php echo $this->Paginator->next('&raquo;', array('tag'=>'li','escape'=>false), '<a href="javascript:void(0);">&raquo;</a>', array('tag'=>'li','escape'=>false,'class'=>'disabled'));?>
            </ul>
        </div>
        <?php } ?>
    </div>
</div>
