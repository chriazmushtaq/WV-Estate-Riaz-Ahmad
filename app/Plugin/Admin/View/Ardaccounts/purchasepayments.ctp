<?php
$isAdr = ($userType=='ADR');
$isEdit = !empty($this->request->data['Ardpurchaseinvoicepayment']['id']);
?>
<div class="page-title"><div class="title-env"><h1 class="title"><?php echo __('Construction Purchase Payments');?></h1></div></div>
<div class="panel">
    <div class="panel-heading">
        <div class="btn-group">
            <?php echo $this->Html->link('<span class="fa fa-arrow-left"></span> '.__('Construction'),array('controller'=>'Ardaccounts','action'=>'construction',$currentProjectId),array('escape'=>false,'class'=>'btn btn-default'));?>
            <button type="button" class="btn btn-success" id="openPaymentModal"><span class="fa fa-plus"></span> <?php echo $isEdit ? __('Edit Payment') : __('Add Payment');?></button>
        </div>
    </div>
    <div class="panel-body">
        <?php echo $this->Session->flash();?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <tr>
                    <th><?php echo __('Invoice');?></th>
                    <th><?php echo __('Vendor');?></th>
                    <th><?php echo __('Amount');?></th>
                    <th><?php echo __('Date');?></th>
                    <th><?php echo __('Method');?></th>
                    <th><?php echo __('Reference');?></th>
                    <th><?php echo __('Action');?></th>
                </tr>
                <?php foreach($rows as $row): $rid=(int)$row['Ardpurchaseinvoicepayment']['id']; ?>
                <tr>
                    <td><?php echo isset($invoiceList[$row['Ardpurchaseinvoicepayment']['purchase_invoice_id']]) ? h($invoiceList[$row['Ardpurchaseinvoicepayment']['purchase_invoice_id']]) : '-';?></td>
                    <td><?php echo isset($vendorList[$row['Ardpurchaseinvoicepayment']['vendor_id']]) ? h($vendorList[$row['Ardpurchaseinvoicepayment']['vendor_id']]) : '-';?></td>
                    <td><?php echo $currency.$this->Number->format($row['Ardpurchaseinvoicepayment']['amount']);?></td>
                    <td><?php echo (!empty($row['Ardpurchaseinvoicepayment']['payment_date']) && $row['Ardpurchaseinvoicepayment']['payment_date'] !== '0000-00-00') ? date('d-M-Y', strtotime((string)$row['Ardpurchaseinvoicepayment']['payment_date'])) : '-';?></td>
                    <td><?php echo h($row['Ardpurchaseinvoicepayment']['payment_method']);?></td>
                    <td><?php echo h($row['Ardpurchaseinvoicepayment']['reference_no']);?></td>
                    <td>
                        <a href="javascript:void(0);" class="btn btn-xs btn-info js-view-payment"
                           data-payment-id="<?php echo (int)$rid;?>"
                           data-invoice="<?php echo isset($invoiceList[$row['Ardpurchaseinvoicepayment']['purchase_invoice_id']]) ? h((string)$invoiceList[$row['Ardpurchaseinvoicepayment']['purchase_invoice_id']], true) : '-';?>"
                           data-vendor="<?php echo isset($vendorList[$row['Ardpurchaseinvoicepayment']['vendor_id']]) ? h((string)$vendorList[$row['Ardpurchaseinvoicepayment']['vendor_id']], true) : '-';?>"
                           data-amount="<?php echo h((string)($currency.$this->Number->format($row['Ardpurchaseinvoicepayment']['amount'])), true);?>"
                           data-date="<?php echo (!empty($row['Ardpurchaseinvoicepayment']['payment_date']) && $row['Ardpurchaseinvoicepayment']['payment_date'] !== '0000-00-00') ? h((string)date('d-M-Y', strtotime((string)$row['Ardpurchaseinvoicepayment']['payment_date'])), true) : '-';?>"
                           data-method="<?php echo h((string)$row['Ardpurchaseinvoicepayment']['payment_method'], true);?>"
                           data-reference="<?php echo h((string)$row['Ardpurchaseinvoicepayment']['reference_no'], true);?>"
                           data-note="<?php echo h((string)$row['Ardpurchaseinvoicepayment']['note'], true);?>">
                           <span class="fa fa-eye"></span>
                        </a>
                        <?php echo $this->Html->link('<span class="fa fa-edit"></span>',array('controller'=>'Ardaccounts','action'=>'purchasepayments',$currentProjectId,'edit:'.$rid),array('escape'=>false,'class'=>'btn btn-xs btn-warning'));?>
                        <?php if(!empty($row['Ardpurchaseinvoicepayment']['voucher_transaction_id'])){ ?>
                        <?php echo $this->Html->link('<span class="fa fa-print"></span>',array('controller'=>'Ardaccounts','action'=>'printvoucher',(int)$row['Ardpurchaseinvoicepayment']['voucher_transaction_id'],$currentProjectId),array('escape'=>false,'class'=>'btn btn-xs btn-primary','target'=>'_blank'));?>
                        <?php } ?>
                        <?php echo $this->Html->link('<span class="fa fa-trash"></span>',array('controller'=>'Ardaccounts','action'=>'deletepurchasepayment',$rid,$currentProjectId),array('escape'=>false,'class'=>'btn btn-xs btn-danger','onclick'=>"return confirm('".__('Are you sure?')."');"));?>
                    </td>
                </tr>
                <?php endforeach;?>
            </table>
        </div>
        <?php echo $this->element('pagination');?>
    </div>
</div>

<div class="modal fade" id="paymentEntryModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><?php echo $isEdit ? __('Edit Purchase Payment') : __('Add Purchase Payment');?></h4>
            </div>
            <div class="modal-body">
                <?php echo $this->Form->create('Ardpurchaseinvoicepayment',array('url'=>array('controller'=>'Ardaccounts','action'=>'purchasepayments',$currentProjectId),'class'=>'form-horizontal'));?>
                <?php echo $this->Form->hidden('id');?>
                <?php if(!$isAdr){ echo $this->Form->hidden('project_id',array('value'=>$agencyId)); }?>
                <div class="row">
                    <?php if($isAdr){?><div class="col-sm-3"><?php echo $this->Form->input('project_id',array('label'=>__('Agency'),'class'=>'form-control','options'=>$projectName,'empty'=>__('Please Select')));?></div><?php }?>
                    <div class="col-sm-3"><?php echo $this->Form->input('purchase_invoice_id',array('label'=>__('Invoice'),'class'=>'form-control','options'=>$invoiceList,'empty'=>__('Please Select')));?></div>
                    <div class="col-sm-3"><?php echo $this->Form->input('vendor_id',array('label'=>__('Vendor'),'class'=>'form-control','options'=>$vendorList,'empty'=>__('Please Select')));?></div>
                    <div class="col-sm-3"><?php echo $this->Form->input('debit_ledger_id',array('label'=>__('Debit Ledger'),'class'=>'form-control','options'=>$ledgerList,'empty'=>__('Please Select')));?></div>
                </div>
                <div class="row">
                    <div class="col-sm-3"><?php echo $this->Form->input('amount',array('label'=>__('Amount'),'class'=>'form-control'));?></div>
                    <div class="col-sm-3"><?php echo $this->Form->input('payment_date',array('label'=>__('Payment Date'),'class'=>'form-control','type'=>'text'));?></div>
                    <div class="col-sm-3"><?php echo $this->Form->input('payment_method',array('label'=>__('Payment Method'),'class'=>'form-control'));?></div>
                    <div class="col-sm-3"><?php echo $this->Form->input('reference_no',array('label'=>__('Reference No'),'class'=>'form-control'));?></div>
                </div>
                <div class="row">
                    <div class="col-sm-12"><?php echo $this->Form->input('note',array('label'=>__('Note'),'class'=>'form-control'));?></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success"><span class="fa fa-save"></span> <?php echo ($isEdit?__('Update'):__('Save'));?></button>
                <?php if($isEdit){echo $this->Html->link('<span class="fa fa-close"></span> '.__('Cancel Edit'),array('controller'=>'Ardaccounts','action'=>'purchasepayments',$currentProjectId),array('escape'=>false,'class'=>'btn btn-danger'));}?>
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Close');?></button>
                <?php echo $this->Form->end();?>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="paymentViewModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><?php echo __('Purchase Payment Details');?></h4>
            </div>
            <div class="modal-body">
                <table class="table table-bordered" style="margin-bottom:0;">
                    <tr><th style="width:35%;"><?php echo __('Invoice');?></th><td id="PayViewInvoice">-</td></tr>
                    <tr><th><?php echo __('Vendor');?></th><td id="PayViewVendor">-</td></tr>
                    <tr><th><?php echo __('Amount');?></th><td id="PayViewAmount">-</td></tr>
                    <tr><th><?php echo __('Date');?></th><td id="PayViewDate">-</td></tr>
                    <tr><th><?php echo __('Method');?></th><td id="PayViewMethod">-</td></tr>
                    <tr><th><?php echo __('Reference');?></th><td id="PayViewReference">-</td></tr>
                    <tr><th><?php echo __('Note');?></th><td id="PayViewNote">-</td></tr>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Close');?></button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
jQuery(function($){
    var $paymentModal = $('#paymentEntryModal');
    var $paymentViewModal = $('#paymentViewModal');
    var openViewId = <?php echo !empty($this->request->query['view']) ? (int)$this->request->query['view'] : 0;?>;
    var fallbackPaymentView = <?php echo json_encode(isset($paymentViewRow) ? $paymentViewRow : array()); ?>;
    function nvl(value){
        var text = $.trim((value || '').toString());
        return text === '' ? '-' : text;
    }
    $('#openPaymentModal').on('click', function(e){
        e.preventDefault();
        $paymentModal.modal('show');
    });
    $(document).on('click', '.js-view-payment', function(e){
        e.preventDefault();
        var $btn = $(this);
        $('#PayViewInvoice').text(nvl($btn.attr('data-invoice')));
        $('#PayViewVendor').text(nvl($btn.attr('data-vendor')));
        $('#PayViewAmount').text(nvl($btn.attr('data-amount')));
        $('#PayViewDate').text(nvl($btn.attr('data-date')));
        $('#PayViewMethod').text(nvl($btn.attr('data-method')));
        $('#PayViewReference').text(nvl($btn.attr('data-reference')));
        $('#PayViewNote').text(nvl($btn.attr('data-note')));
        $paymentViewModal.modal('show');
    });
    <?php if($isEdit){?>
    $paymentModal.modal('show');
    <?php }?>
    if(openViewId > 0)
    {
        var $targetView = $('.js-view-payment[data-payment-id="' + openViewId + '"]').first();
        if($targetView.length)
        {
            $targetView.trigger('click');
        }
        else if(fallbackPaymentView && typeof fallbackPaymentView === 'object' && (parseInt(fallbackPaymentView.id || 0, 10) === openViewId))
        {
            $('#PayViewInvoice').text(nvl(fallbackPaymentView.invoice));
            $('#PayViewVendor').text(nvl(fallbackPaymentView.vendor));
            $('#PayViewAmount').text(nvl(fallbackPaymentView.amount));
            $('#PayViewDate').text(nvl(fallbackPaymentView.date));
            $('#PayViewMethod').text(nvl(fallbackPaymentView.method));
            $('#PayViewReference').text(nvl(fallbackPaymentView.reference));
            $('#PayViewNote').text(nvl(fallbackPaymentView.note));
            $paymentViewModal.modal('show');
        }
    }
});
</script>
