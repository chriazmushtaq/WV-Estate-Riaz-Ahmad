<?php
$statusOptions = isset($statusOptions) ? $statusOptions : array();
$typeOptions = isset($typeOptions) ? $typeOptions : array();
$isSuperAdminSession = !empty($isSuperAdminSession);
$canEditDealTransferFee = !empty($canEditDealTransferFee) || !empty($allowEditAction) || !empty($userPermissionArr['update_right']);
?>
<style>
    .dt-fee-form{display:flex;gap:4px;align-items:center;min-width:170px;}
    .dt-fee-form .form-control{width:110px;height:28px;padding:3px 6px;}
    .dt-action-wrap{white-space:nowrap;position:relative;}
    .dt-action-menu{left:auto;right:0;min-width:190px;}
    .dt-action-menu>li>a,.dt-action-menu>li>span{display:block;padding:7px 14px;clear:both;font-weight:400;line-height:1.42857143;white-space:nowrap;}
    .dt-action-menu>li>span{color:#999;cursor:not-allowed;}
    @media (min-width: 768px){.dt-transfer-table-wrap.table-responsive{overflow:visible;}}
    .dt-fee-status{display:block;margin-top:4px;}
</style>
<div class="page-title">
    <div class="title-env">
        <h1 class="title"><?php echo __('Deal Transfer');?></h1>
    </div>
</div>

<div class="panel">
    <div class="panel-body">
        <?php echo $this->Session->flash();?>
        <div class="btn-group" style="margin-bottom:12px;">
            <?php echo $this->Html->link('<span class="fa fa-plus"></span> '.__('New Transfer / Merge'), array('controller'=>'Dealtransfers','action'=>'add'), array('escape'=>false,'class'=>'btn btn-success'));?>
            <?php echo $this->Html->link('<span class="fa fa-cog"></span> '.__('Settings'), array('controller'=>'Dealtransfers','action'=>'settings'), array('escape'=>false,'class'=>'btn btn-info'));?>
        </div>

        <?php echo $this->Form->create('Filter', array('type'=>'get','class'=>'form-inline','style'=>'margin-bottom:12px;'));?>
            <?php echo $this->Form->select('type', $typeOptions, array('empty'=>__('All Types'),'class'=>'form-control','value'=>isset($this->request->query['type']) ? $this->request->query['type'] : ''));?>
            <?php echo $this->Form->select('status', $statusOptions, array('empty'=>__('All Status'),'class'=>'form-control','value'=>isset($this->request->query['status']) ? $this->request->query['status'] : ''));?>
            <button type="submit" class="btn btn-primary"><span class="fa fa-search"></span> <?php echo __('Filter');?></button>
        <?php echo $this->Form->end();?>

        <div class="table-responsive dt-transfer-table-wrap">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th><?php echo __('No');?></th>
                        <th><?php echo __('Date');?></th>
                        <th><?php echo __('Type');?></th>
                        <th><?php echo __('Source Deal');?></th>
                        <th><?php echo __('Source Client');?></th>
                        <th><?php echo __('Father / Guardian');?></th>
                        <th><?php echo __('Target');?></th>
                        <th><?php echo __('Agency');?></th>
                        <th><?php echo __('Fee');?></th>
                        <th><?php echo __('Status');?></th>
                        <th><?php echo __('Action');?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($transferRows)){ ?>
                        <tr><td colspan="11" class="text-center"><?php echo __('No deal transfer records found.');?></td></tr>
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
                    ?>
                    <tr>
                        <td><strong><?php echo h($t['transfer_no']);?></strong></td>
                        <td><?php echo h($t['request_date']);?></td>
                        <td><?php echo h($t['transfer_type']);?></td>
                        <td><?php echo h($sourceDealNo);?></td>
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
                        <td><?php echo h($targetLabel);?></td>
                        <td><?php echo h((isset($row['SourceAgency']['name']) ? $row['SourceAgency']['name'] : '').' -> '.(isset($row['TargetAgency']['name']) ? $row['TargetAgency']['name'] : ''));?></td>
                        <td>
                            <?php if($canEditFee){ ?>
                                <?php echo $this->Form->create('Dealtransfer', array('url'=>array('controller'=>'Dealtransfers','action'=>'updatefee',(int)$t['id']),'class'=>'dt-fee-form'));?>
                                    <?php echo $this->Form->input('processing_fee', array('type'=>'number','step'=>'0.01','min'=>'0','label'=>false,'div'=>false,'class'=>'form-control input-sm','value'=>number_format($feeAmount, 2, '.', '')));?>
                                    <button type="submit" class="btn btn-xs btn-primary"><span class="fa fa-save"></span> <?php echo __('Save');?></button>
                                <?php echo $this->Form->end();?>
                            <?php } else { ?>
                                <?php echo h($currency.' '.number_format($feeAmount, 2));?>
                                <?php if($feeIsPaid){ ?><span class="label label-success dt-fee-status"><?php echo __('Paid / Locked');?></span><?php } ?>
                            <?php } ?>
                        </td>
                        <td><span class="label label-<?php echo $t['status']==='Finalized' ? 'success' : ($t['status']==='Rejected' ? 'danger' : ($t['status']==='Reversed' ? 'default' : 'warning'));?>"><?php echo h($t['status']);?></span></td>
                        <td class="dt-action-wrap">
                            <div class="btn-group">
                                <button type="button" class="btn btn-xs btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <span class="fa fa-bars"></span> <?php echo __('Actions');?> <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu dt-action-menu" role="menu">
                                    <li><?php echo $this->Html->link('<span class="fa fa-eye"></span> '.__('View'), array('controller'=>'Dealtransfers','action'=>'view',(int)$t['id']), array('escape'=>false));?></li>
                                    <?php if($feeIsPaid){ ?>
                                        <li><?php echo $this->Html->link('<span class="fa fa-print"></span> '.__('Paid Voucher'), array('controller'=>'Ardaccounts','action'=>'printvoucher',$feeVoucherTxId,$feeVoucherProjectId), array('escape'=>false,'target'=>'_blank'));?></li>
                                    <?php } elseif($t['status'] !== 'Rejected' && $t['status'] !== 'Reversed' && $feeAmount > 0){ ?>
                                        <li><?php echo $this->Html->link('<span class="fa fa-print"></span> '.__('Print Voucher'), array('controller'=>'Dealtransfers','action'=>'printfeevoucher',(int)$t['id']), array('escape'=>false,'target'=>'_blank'));?></li>
                                        <li><?php echo $this->Html->link('<span class="fa fa-money"></span> '.__('Pay Fees'), array('controller'=>'Dealtransfers','action'=>'payfeevoucher',(int)$t['id']), array('escape'=>false,'confirm'=>__('Pay processing fee voucher now with the current amount?')));?></li>
                                    <?php } ?>
                                    <?php if($t['status'] === 'Finalized'){ ?>
                                        <li class="divider"></li>
                                        <li><?php echo $this->Html->link('<span class="fa fa-file-text-o"></span> '.__('Agreement'), array('controller'=>'Dealtransfers','action'=>'agreement',(int)$t['id']), array('escape'=>false,'target'=>'_blank'));?></li>
                                        <?php if(!empty($isSuperAdminSession)){ ?>
                                            <li><?php echo $this->Html->link('<span class="fa fa-undo"></span> '.__('Reverse'), array('controller'=>'Dealtransfers','action'=>'reverse',(int)$t['id']), array('escape'=>false,'confirm'=>__('Reverse this finalized transfer/merge and remove its fee charge/voucher?')));?></li>
                                        <?php } ?>
                                    <?php } ?>
                                    <?php if($t['status'] === 'Draft'){ ?>
                                        <li class="divider"></li>
                                        <li><?php echo $this->Html->link('<span class="fa fa-edit"></span> '.__('Edit'), array('controller'=>'Dealtransfers','action'=>'edit',(int)$t['id']), array('escape'=>false));?></li>
                                        <?php if($feePaymentPending){ ?>
                                            <li class="disabled"><span title="<?php echo __('Pay processing fee before finalization.');?>"><span class="fa fa-lock"></span> <?php echo __('Fee Pending');?></span></li>
                                        <?php } else { ?>
                                            <li><?php echo $this->Html->link('<span class="fa fa-check"></span> '.__('Finalize'), array('controller'=>'Dealtransfers','action'=>'finalize',(int)$t['id']), array('escape'=>false,'confirm'=>__('Finalize this deal transfer now?')));?></li>
                                        <?php } ?>
                                        <li><?php echo $this->Html->link('<span class="fa fa-ban"></span> '.__('Reject'), array('controller'=>'Dealtransfers','action'=>'reject',(int)$t['id']), array('escape'=>false,'confirm'=>__('Reject this request?')));?></li>
                                    <?php } ?>
                                    <?php if($t['status'] === 'Draft' || $t['status'] === 'Reversed'){ ?>
                                        <li class="divider"></li>
                                        <li><?php echo $this->Html->link('<span class="fa fa-trash"></span> '.__('Delete'), array('controller'=>'Dealtransfers','action'=>'delete',(int)$t['id']), array('escape'=>false,'confirm'=>__('Delete this deal transfer record?')));?></li>
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
