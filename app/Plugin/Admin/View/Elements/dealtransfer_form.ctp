<?php
$defaultSettings = isset($defaultSettings) ? $defaultSettings : array();
$defaultFeeType = isset($defaultSettings['default_fee_type']) ? $defaultSettings['default_fee_type'] : 'Fixed';
$defaultTransferFee = isset($defaultSettings['default_transfer_fee']) ? (float)$defaultSettings['default_transfer_fee'] : 0;
$defaultFeePercent = isset($defaultSettings['default_fee_percent']) ? (float)$defaultSettings['default_fee_percent'] : 0;
?>
<style type="text/css">
.transfer-preview{background:#f8fbff;border:1px solid #dbe6f3;border-radius:8px;padding:12px;margin-bottom:14px;}
.transfer-preview strong{color:#08213f;}
</style>
<div class="transfer-preview" id="TransferPreview">
    <strong><?php echo __('Preview');?></strong>
    <span id="TransferPreviewText"><?php echo __('Select a source deal to calculate paid amount, balance, and suggested fee.');?></span>
</div>
<div class="form-group">
    <label class="col-sm-3 control-label"><?php echo __('Transfer Type');?></label>
    <div class="col-sm-3"><?php echo $this->Form->select('transfer_type', $typeOptions, array('class'=>'form-control','value'=>isset($this->request->data['Dealtransfer']['transfer_type']) ? $this->request->data['Dealtransfer']['transfer_type'] : 'Transfer'));?></div>
    <label class="col-sm-3 control-label"><?php echo __('Request Date');?></label>
    <div class="col-sm-3"><?php echo $this->Form->text('request_date', array('class'=>'form-control','value'=>isset($this->request->data['Dealtransfer']['request_date']) ? $this->request->data['Dealtransfer']['request_date'] : date('d-M-Y'),'autocomplete'=>'off'));?></div>
</div>
<div class="form-group">
    <label class="col-sm-3 control-label"><?php echo __('Source Deal');?></label>
    <div class="col-sm-9"><?php echo $this->Form->select('source_deal_id', $dealOptions, array('empty'=>__('Please Select'),'class'=>'form-control','id'=>'TransferSourceDeal'));?></div>
</div>
<div class="form-group merge-row">
    <label class="col-sm-3 control-label"><?php echo __('Target Deal');?></label>
    <div class="col-sm-9"><?php echo $this->Form->select('target_deal_id', $dealOptions, array('empty'=>__('Please Select'),'class'=>'form-control','id'=>'TransferTargetDeal'));?></div>
</div>
<div class="form-group transfer-row">
    <label class="col-sm-3 control-label"><?php echo __('Target Client');?></label>
    <div class="col-sm-9"><?php echo $this->Form->select('target_client_id', $clientOptions, array('empty'=>__('Same Client / Please Select'),'class'=>'form-control','id'=>'TransferTargetClient'));?></div>
</div>
<div class="form-group transfer-row">
    <label class="col-sm-3 control-label"><?php echo __('Target Agency');?></label>
    <div class="col-sm-9"><?php echo $this->Form->select('target_project_id', $agencyOptions, array('empty'=>__('Same Agency / Please Select'),'class'=>'form-control','id'=>'TransferTargetAgency'));?></div>
</div>
<div class="form-group">
    <label class="col-sm-3 control-label"><?php echo __('Fee Type');?></label>
    <div class="col-sm-3"><?php echo $this->Form->select('processing_fee_type', $feeTypes, array('class'=>'form-control','id'=>'TransferFeeType','value'=>isset($this->request->data['Dealtransfer']['processing_fee_type']) ? $this->request->data['Dealtransfer']['processing_fee_type'] : $defaultFeeType));?></div>
    <label class="col-sm-3 control-label"><?php echo __('Fee %');?></label>
    <div class="col-sm-3"><?php echo $this->Form->text('processing_fee_percent', array('class'=>'form-control','id'=>'TransferFeePercent','value'=>isset($this->request->data['Dealtransfer']['processing_fee_percent']) ? $this->request->data['Dealtransfer']['processing_fee_percent'] : $defaultFeePercent));?></div>
</div>
<div class="form-group">
    <label class="col-sm-3 control-label"><?php echo __('Processing Fee');?></label>
    <div class="col-sm-3"><?php echo $this->Form->text('processing_fee', array('class'=>'form-control','id'=>'TransferFee','value'=>isset($this->request->data['Dealtransfer']['processing_fee']) ? $this->request->data['Dealtransfer']['processing_fee'] : $defaultTransferFee));?></div>
</div>
<div class="form-group">
    <label class="col-sm-3 control-label"><?php echo __('Reason');?></label>
    <div class="col-sm-9"><?php echo $this->Form->textarea('reason', array('class'=>'form-control','rows'=>4));?></div>
</div>
<script type="text/javascript">
jQuery(function($){
    var metaUrlBase = <?php echo json_encode($this->Html->url(array('controller'=>'Dealtransfers','action'=>'dealmeta')));?>;
    var defaultTransferFee = <?php echo json_encode($defaultTransferFee);?>;
    var defaultMergeFee = <?php echo json_encode(isset($defaultSettings['default_merge_fee']) ? (float)$defaultSettings['default_merge_fee'] : 0);?>;
    var defaultFeeType = <?php echo json_encode($defaultFeeType);?>;
    var defaultFeePercent = <?php echo json_encode($defaultFeePercent);?>;
    var sourceDealTotal = 0;
    var userChangedFee = false;
    var userChangedFeeType = false;
    function amount(v){ v = (v || '').toString().replace(/,/g,''); var n = parseFloat(v); return isNaN(n) ? 0 : n; }
    function field(selector, name){
        var $el = $(selector);
        if(!$el.length && name){ $el = $('[name="data[Dealtransfer][' + name + ']"]'); }
        return $el;
    }
    function typeValue(){ return field('#DealtransferTransferType', 'transfer_type').val() || 'Transfer'; }
    function currentDefaultFixedFee(){
        return typeValue() === 'Merge' ? amount(defaultMergeFee) : amount(defaultTransferFee);
    }
    function toggleRows(){
        if(typeValue() === 'Merge'){ $('.merge-row').show(); $('.transfer-row').hide(); }
        else { $('.merge-row').hide(); $('.transfer-row').show(); }
    }
    function computeFee(total, forceDefault){
        total = amount(total);
        var $feeType = field('#TransferFeeType', 'processing_fee_type');
        var $feePercent = field('#TransferFeePercent', 'processing_fee_percent');
        var $fee = field('#TransferFee', 'processing_fee');
        var feeType = $feeType.val() || defaultFeeType || 'Fixed';
        if(feeType === 'Waived'){ $fee.val('0.00'); $feePercent.val('0.00'); return; }
        if(feeType === 'Percent'){
            var percent = amount($feePercent.val());
            if((forceDefault || percent <= 0) && amount(defaultFeePercent) > 0){
                percent = amount(defaultFeePercent);
                $feePercent.val(percent.toFixed(2));
            }
            $fee.val(((total * percent) / 100).toFixed(2));
            return;
        }
        if(feeType === 'Fixed' || feeType === 'Custom'){
            if(forceDefault || amount($fee.val()) <= 0){
                $fee.val(currentDefaultFixedFee().toFixed(2));
            }
        }
    }
    function applySourceDefaults(resp){
        if(!resp || !resp.ok){ return; }
        defaultTransferFee = amount(resp.default_transfer_fee);
        defaultMergeFee = amount(resp.default_merge_fee);
        defaultFeePercent = amount(resp.default_fee_percent);
        if(!userChangedFeeType && resp.default_fee_type){
            defaultFeeType = resp.default_fee_type;
            field('#TransferFeeType', 'processing_fee_type').val(defaultFeeType);
        }
        computeFee(sourceDealTotal, !userChangedFee);
    }
    function loadMeta(){
        var id = parseInt(field('#TransferSourceDeal', 'source_deal_id').val() || 0, 10) || 0;
        if(id <= 0){ $('#TransferPreviewText').text(<?php echo json_encode(__('Select a source deal to calculate paid amount, balance, and suggested fee.'));?>); return; }
        $.getJSON(metaUrlBase + '/' + id, function(resp){
            if(resp && resp.ok){
                sourceDealTotal = amount(resp.total_amount);
                if(!field('#TransferTargetClient', 'target_client_id').val()){ field('#TransferTargetClient', 'target_client_id').val(resp.client_id); }
                if(!field('#TransferTargetAgency', 'target_project_id').val()){ field('#TransferTargetAgency', 'target_project_id').val(resp.project_id); }
                applySourceDefaults(resp);
                $('#TransferPreviewText').text('Deal total: ' + parseFloat(resp.total_amount || 0).toFixed(2) + ' | Paid: ' + parseFloat(resp.paid_amount || 0).toFixed(2) + ' | Balance: ' + parseFloat(resp.balance_amount || 0).toFixed(2) + ' | Client: ' + (resp.client_name || '-'));
            }
        });
    }
    field('#DealtransferTransferType', 'transfer_type').on('change', function(){ toggleRows(); computeFee(sourceDealTotal, !userChangedFee); });
    field('#TransferSourceDeal', 'source_deal_id').on('change', function(){ userChangedFee = false; userChangedFeeType = false; loadMeta(); });
    field('#TransferFeeType', 'processing_fee_type').on('change', function(){ userChangedFeeType = true; computeFee(sourceDealTotal, true); });
    field('#TransferFeePercent', 'processing_fee_percent').on('input change', function(){ userChangedFee = true; computeFee(sourceDealTotal, false); });
    field('#TransferFee', 'processing_fee').on('input change', function(){ userChangedFee = true; });
    toggleRows();
    loadMeta();
});
</script>
