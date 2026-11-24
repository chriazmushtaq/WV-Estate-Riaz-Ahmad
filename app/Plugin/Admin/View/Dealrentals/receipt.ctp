<?php $r=$rental['DealRental']; $isEdit=!empty($this->request->data['DealRentalReceipt']['id']); ?>
<style>
.wv-rental-receipt{max-width:980px;background:#fff;border:1px solid #dbe6f3;border-radius:12px;padding:18px;box-shadow:0 12px 34px rgba(15,35,58,.07)}
.wv-rental-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px}.wv-rental-field.full{grid-column:1/-1}
.wv-rental-field label{display:block;font-size:11px;font-weight:900;text-transform:uppercase;color:#0b2344;margin-bottom:5px}
.wv-rental-field .form-control{height:39px;border-radius:8px;border:1px solid #cbd8ea;box-shadow:none;font-weight:700}.wv-rental-field textarea.form-control{height:auto;min-height:85px}
.wv-rental-note{background:#f8fbff;border:1px dashed #bdd2eb;border-radius:10px;padding:10px;margin-bottom:14px;color:#31445f;font-weight:700}
@media(max-width:900px){.wv-rental-grid{grid-template-columns:1fr}}
</style>
<div class="page-title"><div class="title-env"><h1 class="title"><?php echo $isEdit ? __('Edit Rental Receipt') : __('Add Rental Receipt');?></h1></div></div>
<div class="panel"><div class="panel-body">
<?php echo $this->Session->flash();?>
<div class="wv-rental-receipt">
<div class="wv-rental-note"><?php echo __('Agreement');?>: <strong><?php echo h($r['agreement_no']);?></strong> | <?php echo __('Tenant');?>: <strong><?php echo h($r['tenant_name']);?></strong> | <?php echo __('Monthly Rent');?>: <strong><?php echo h($currency.' '.number_format((float)$r['monthly_rent'],2));?></strong></div>
<?php if(!empty($selectedSchedule['DealRentalSchedule']['id'])): $selected=$selectedSchedule['DealRentalSchedule'];?>
<div class="wv-rental-note">
    <?php echo __('Selected Rent');?>: <strong><?php echo h(date('M Y',strtotime($selected['rent_month'].'-01')));?></strong> |
    <?php echo __('Due');?>: <strong><?php echo h(date('d-M-Y',strtotime($selected['due_date'])));?></strong> |
    <?php echo __('Remaining');?>: <strong><?php echo h($currency.' '.number_format((float)$selected['balance_amount'],2));?></strong><br>
    <small><?php echo __('Short payments remain due on this month. Any excess is automatically allocated to the following rent months.');?></small>
</div>
<?php endif;?>
<?php echo $this->Form->create('DealRentalReceipt', array('class'=>'wv-no-auto-layout'));?>
<div class="wv-rental-grid">
    <div class="wv-rental-field"><label><?php echo __('Receipt No');?></label><?php echo $this->Form->text('receipt_no', array('class'=>'form-control','placeholder'=>__('Auto if empty')));?></div>
    <div class="wv-rental-field"><label><?php echo __('Rent Month');?></label><?php echo $this->Form->text('rent_month', array('class'=>'form-control','placeholder'=>'YYYY-MM'));?></div>
    <div class="wv-rental-field"><label><?php echo __('Receipt Date');?></label><?php $receiptDateValue=isset($this->request->data['DealRentalReceipt']['receipt_date'])?$this->request->data['DealRentalReceipt']['receipt_date']:date('Y-m-d');echo $this->Form->text('receipt_date', array('class'=>'form-control datepicker','autocomplete'=>'off','value'=>date('d-M-Y',strtotime($receiptDateValue))));?></div>
    <div class="wv-rental-field"><label><?php echo __('Rent Amount');?></label><?php echo $this->Form->text('rent_amount', array('class'=>'form-control','value'=>isset($this->request->data['DealRentalReceipt']['rent_amount'])?$this->request->data['DealRentalReceipt']['rent_amount']:$r['monthly_rent']));?></div>
    <div class="wv-rental-field"><label><?php echo __('Additional Monthly Expense');?></label><?php echo $this->Form->text('expense_amount', array('class'=>'form-control','placeholder'=>'0.00'));?></div>
    <div class="wv-rental-field"><label><?php echo __('Pay From Account');?></label><?php echo $this->Form->select('source_ledger_id', isset($paymentAccounts)?$paymentAccounts:array(), array('empty'=>__('Select Cash / Bank Account'),'class'=>'form-control','required'=>true));?></div>
    <div class="wv-rental-field full"><label><?php echo __('Remarks');?></label><?php echo $this->Form->textarea('remarks', array('class'=>'form-control','rows'=>3));?></div>
</div>
<div style="border-top:1px solid #e3ebf6;margin-top:16px;padding-top:14px;display:flex;gap:10px;">
    <button class="btn btn-success"><i class="fa fa-save"></i> <?php echo $isEdit ? __('Update Receipt') : __('Save Receipt');?></button>
    <?php echo $this->Html->link(__('Back'), array('action'=>'view',(int)$r['id']), array('class'=>'btn btn-primary'));?>
</div>
<?php echo $this->Form->end();?>
</div>
</div></div>
