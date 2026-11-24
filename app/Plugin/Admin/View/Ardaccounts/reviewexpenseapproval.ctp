<?php
$r = (array)$row['Ardexpenseapprovalrequest'];
$entryDate = !empty($r['entry_date']) ? date('d-M-Y', strtotime($r['entry_date'])) : date('d-M-Y');
$currencyLabel = trim(strip_tags((string)$currency));
if($currencyLabel === '') { $currencyLabel = 'Rs'; }
$canReturn = $r['status'] === 'Pending CEO' && empty($r['transaction_id']) && empty($r['ceo_approved_by']);
?>
<style>
.eae-head{align-items:center;display:flex;gap:12px;justify-content:space-between;margin-bottom:14px}.eae-head h2{color:#0b2344;font-size:21px;font-weight:900;margin:0}.eae-head p{color:#64748b;font-size:11px;font-weight:700;margin:4px 0 0}.eae-status{background:#fef3c7;border-radius:999px;color:#92400e;display:inline-block;font-size:10px;font-weight:900;padding:6px 10px;text-transform:uppercase}.eae-status.ceo{background:#ede9fe;color:#6d28d9}.eae-notice{background:#eff6ff;border-left:4px solid #2563eb;color:#1e3a8a;font-size:11px;font-weight:800;margin-bottom:14px;padding:10px 12px}.eae-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}.eae-field{min-width:0}.eae-field.full{grid-column:1/-1}.eae-field label{color:#172033;display:block;font-size:10px;font-weight:900;margin-bottom:6px;text-transform:uppercase}.eae-field .form-control{border:1px solid #cbd8e8;border-radius:6px;height:42px;width:100%}.eae-field textarea.form-control{height:105px;resize:vertical}.eae-fixed{align-items:center;background:#f1f5f9;border:1px solid #cbd8e8;border-radius:6px;color:#172033;display:flex;font-weight:800;height:42px;padding:0 12px}.eae-fixed.amount{color:#b91c1c;font-size:16px}.eae-actions{border-top:1px solid #dbe3ee;display:flex;gap:8px;margin-top:18px;padding-top:15px}.eae-return{margin-left:auto}@media(max-width:760px){.eae-grid{grid-template-columns:1fr}.eae-field.full{grid-column:auto}.eae-actions{align-items:stretch;flex-direction:column}.eae-return{margin-left:0}}
</style>
<div class="page-title"><div class="title-env"><h1 class="title"><?php echo __('Review Expense Voucher');?></h1></div></div>
<div class="panel"><div class="panel-body">
<?php echo $this->Session->flash();?>
<div class="eae-head">
    <div><h2><?php echo h($r['ref_no']);?></h2><p><?php echo __('Manager may correct voucher details before final CEO approval.');?></p></div>
    <span class="eae-status<?php echo $r['status']==='Pending CEO'?' ceo':'';?>"><?php echo h($r['status']);?></span>
</div>
<div class="eae-notice"><i class="fa fa-lock"></i> <?php echo __('Amount is locked to the Accountant request and cannot be changed by Manager. Type and workflow status are controlled automatically.');?></div>

<?php echo $this->Form->create('ExpenseApprovalEdit', array('url' => array('action' => 'reviewexpenseapproval', (int)$r['id']), 'type' => 'post'));?>
<div class="eae-grid">
    <div class="eae-field"><label><?php echo __('Date');?></label><?php echo $this->Form->text('entry_date', array('class'=>'form-control','value'=>$entryDate,'required'=>true,'autocomplete'=>'off','placeholder'=>'16-Jun-2026'));?></div>
    <div class="eae-field"><label><?php echo __('Asset / Bank From');?></label><?php echo $this->Form->select('ledger_id',$sourceLedgers,array('class'=>'form-control','value'=>(int)$r['ledger_id'],'empty'=>__('Please Select'),'required'=>true));?></div>
    <div class="eae-field"><label><?php echo __('Expense Account');?></label><?php echo $this->Form->select('expense_ledger_id',$expenseLedgers,array('class'=>'form-control','value'=>(int)$r['expense_ledger_id'],'empty'=>__('Please Select'),'required'=>true));?></div>
    <div class="eae-field"><label><?php echo __('Amount (Locked)');?></label><div class="eae-fixed amount"><i class="fa fa-lock" style="margin-right:7px"></i><?php echo h($currencyLabel.' '.number_format((float)$r['amount'],2));?></div></div>
    <div class="eae-field"><label><?php echo __('Type');?></label><div class="eae-fixed"><?php echo __('Expense');?></div></div>
    <div class="eae-field"><label><?php echo __('Workflow Status');?></label><div class="eae-fixed"><?php echo h($r['status']);?></div></div>
    <div class="eae-field"><label><?php echo __('Stock Store');?></label><?php echo $this->Form->select('construction_site_id',$siteList,array('class'=>'form-control','value'=>(int)$r['construction_site_id'],'empty'=>__('None')));?></div>
    <div class="eae-field"><label><?php echo __('Reference No');?></label><?php echo $this->Form->text('ref_no',array('class'=>'form-control','value'=>$r['ref_no'],'maxlength'=>80));?></div>
    <div class="eae-field full"><label><?php echo __('Description');?></label><?php echo $this->Form->textarea('description',array('class'=>'form-control','value'=>$r['description']));?></div>
</div>
<div class="eae-actions">
    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> <?php echo __('Save Manager Changes');?></button>
    <?php echo $this->Html->link('<i class="fa fa-arrow-left"></i> '.__('Back to Approvals'),array('action'=>'expenseapprovals',$projectId),array('class'=>'btn btn-default','escape'=>false));?>
</div>
<?php echo $this->Form->end();?>
<?php if($canReturn):?><div class="eae-actions" style="justify-content:flex-end"><?php echo $this->Form->postLink('<i class="fa fa-undo"></i> '.__('Return to Unapproved'),array('action'=>'unverifyexpenseapproval',(int)$r['id']),array('class'=>'btn btn-danger','escape'=>false,'confirm'=>__('Withdraw Manager approval and return this request to Pending Manager?')));?></div><?php endif;?>
</div></div>
<script>
jQuery(function($){
    var $date=$('#ExpenseApprovalEditEntryDate');
    if($.fn.datepicker){$date.datepicker({dateFormat:'dd-M-yy',changeMonth:true,changeYear:true});}
});
</script>
