<?php
$isAdr = ($userType=='ADR');
$isEdit = !empty($this->request->data['Ardpurchaseinvoice']['id']);
?>
<div class="page-title"><div class="title-env"><h1 class="title"><?php echo __('Construction Purchase Invoices');?></h1></div></div>
<div class="panel">
    <div class="panel-heading">
        <div class="btn-group">
            <?php echo $this->Html->link('<span class="fa fa-arrow-left"></span> '.__('Construction'),array('controller'=>'Ardaccounts','action'=>'construction',$currentProjectId),array('escape'=>false,'class'=>'btn btn-default'));?>
            <button type="button" class="btn btn-success" id="openInvoiceModal"><span class="fa fa-plus"></span> <?php echo $isEdit ? __('Edit Invoice') : __('Add Invoice');?></button>
        </div>
    </div>
    <div class="panel-body">
        <?php echo $this->Session->flash();?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <tr>
                    <th><?php echo __('Invoice No');?></th>
                    <th><?php echo __('Vendor');?></th>
                    <th><?php echo __('PO');?></th>
                    <th><?php echo __('GRN');?></th>
                    <th><?php echo __('Amount');?></th>
                    <th><?php echo __('Paid');?></th>
                    <th><?php echo __('Status');?></th>
                    <th><?php echo __('Action');?></th>
                </tr>
                <?php foreach($rows as $row): $rid=(int)$row['Ardpurchaseinvoice']['id']; ?>
                <tr>
                    <td><?php echo h($row['Ardpurchaseinvoice']['invoice_no']);?></td>
                    <td><?php echo isset($vendorList[$row['Ardpurchaseinvoice']['vendor_id']]) ? h($vendorList[$row['Ardpurchaseinvoice']['vendor_id']]) : '-';?></td>
                    <td><?php echo isset($poList[$row['Ardpurchaseinvoice']['po_id']]) ? h($poList[$row['Ardpurchaseinvoice']['po_id']]) : '-';?></td>
                    <td><?php echo isset($grnList[$row['Ardpurchaseinvoice']['grn_id']]) ? h($grnList[$row['Ardpurchaseinvoice']['grn_id']]) : '-';?></td>
                    <td><?php echo $currency.$this->Number->format($row['Ardpurchaseinvoice']['amount']);?></td>
                    <td><?php echo $currency.$this->Number->format($row['Ardpurchaseinvoice']['paid_amount']);?></td>
                    <td><?php echo h(ucfirst($row['Ardpurchaseinvoice']['status']));?></td>
                    <td>
                        <?php echo $this->Html->link('<span class="fa fa-edit"></span>',array('controller'=>'Ardaccounts','action'=>'purchaseinvoices',$currentProjectId,'edit:'.$rid),array('escape'=>false,'class'=>'btn btn-xs btn-warning'));?>
                        <?php echo $this->Html->link('<span class="fa fa-trash"></span>',array('controller'=>'Ardaccounts','action'=>'deletepurchaseinvoice',$rid,$currentProjectId),array('escape'=>false,'class'=>'btn btn-xs btn-danger','onclick'=>"return confirm('".__('Are you sure?')."');"));?>
                    </td>
                </tr>
                <?php endforeach;?>
            </table>
        </div>
        <?php echo $this->element('pagination');?>
    </div>
</div>

<div class="modal fade" id="invoiceEntryModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><?php echo $isEdit ? __('Edit Purchase Invoice') : __('Add Purchase Invoice');?></h4>
            </div>
            <div class="modal-body">
                <?php echo $this->Form->create('Ardpurchaseinvoice',array('url'=>array('controller'=>'Ardaccounts','action'=>'purchaseinvoices',$currentProjectId),'class'=>'form-horizontal'));?>
                <?php if(!$isAdr){ echo $this->Form->hidden('project_id',array('value'=>$agencyId)); }?>
                <?php if($isEdit){echo $this->Form->hidden('id');}?>
                <div class="row">
                    <?php if($isAdr){?><div class="col-sm-3"><?php echo $this->Form->input('project_id',array('label'=>__('Agency'),'class'=>'form-control','options'=>$projectName,'empty'=>__('Please Select')));?></div><?php }?>
                    <div class="col-sm-3"><?php echo $this->Form->input('invoice_no',array('label'=>__('Invoice No'),'class'=>'form-control'));?></div>
                    <div class="col-sm-3"><?php echo $this->Form->input('vendor_id',array('label'=>__('Vendor'),'class'=>'form-control','options'=>$vendorList,'empty'=>__('Please Select')));?></div>
                    <div class="col-sm-3"><?php echo $this->Form->input('invoice_date',array('label'=>__('Invoice Date'),'class'=>'form-control','type'=>'text'));?></div>
                </div>
                <div class="row">
                    <div class="col-sm-3"><?php echo $this->Form->input('po_id',array('label'=>__('PO'),'class'=>'form-control','options'=>$poList,'empty'=>__('Please Select')));?></div>
                    <div class="col-sm-3"><?php echo $this->Form->input('grn_id',array('label'=>__('GRN'),'class'=>'form-control','options'=>$grnList,'empty'=>__('Please Select')));?></div>
                    <div class="col-sm-2"><?php echo $this->Form->input('amount',array('label'=>__('Amount'),'class'=>'form-control'));?></div>
                    <div class="col-sm-2"><?php echo $this->Form->input('paid_amount',array('label'=>__('Paid'),'class'=>'form-control'));?></div>
                    <div class="col-sm-2"><?php echo $this->Form->input('status',array('label'=>__('Status'),'class'=>'form-control','options'=>array('open'=>__('Open'),'partial'=>__('Partial'),'paid'=>__('Paid'))));?></div>
                </div>
                <div class="row">
                    <div class="col-sm-12"><?php echo $this->Form->input('note',array('label'=>__('Note'),'class'=>'form-control'));?></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success"><span class="fa fa-save"></span> <?php echo ($isEdit?__('Update'):__('Save'));?></button>
                <?php if($isEdit){echo $this->Html->link('<span class="fa fa-close"></span> '.__('Cancel Edit'),array('controller'=>'Ardaccounts','action'=>'purchaseinvoices',$currentProjectId),array('escape'=>false,'class'=>'btn btn-danger'));}?>
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Close');?></button>
                <?php echo $this->Form->end();?>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
jQuery(function($){
    var $invoiceModal = $('#invoiceEntryModal');
    $('#openInvoiceModal').on('click', function(e){
        e.preventDefault();
        $invoiceModal.modal('show');
    });
    <?php if($isEdit){?>
    $invoiceModal.modal('show');
    <?php }?>
});
</script>
