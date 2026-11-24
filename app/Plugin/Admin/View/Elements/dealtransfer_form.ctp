<?php
$defaultSettings = isset($defaultSettings) ? $defaultSettings : array();
$defaultFeeType = isset($defaultSettings['default_fee_type']) ? $defaultSettings['default_fee_type'] : 'Fixed';
$defaultTransferFee = isset($defaultSettings['default_transfer_fee']) ? (float)$defaultSettings['default_transfer_fee'] : 0;
$defaultFeePercent = isset($defaultSettings['default_fee_percent']) ? (float)$defaultSettings['default_fee_percent'] : 0;
$currencySymbol = isset($currency) ? $currency : 'PKR';
?>
<style type="text/css">
.dt-form-shell {
    margin-bottom: 20px;
}
.dt-preview-panel {
    background: #ffffff;
    border: 1px solid #dbe6f3;
    border-radius: 10px;
    padding: 16px 20px;
    margin-bottom: 22px;
    box-shadow: 0 4px 14px rgba(15,35,58,.04);
}
.dt-preview-panel-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 14px;
    padding-bottom: 10px;
    border-bottom: 1px solid #f1f5f9;
}
.dt-preview-panel-head strong {
    font-size: 14px;
    font-weight: 800;
    color: #08213f;
    display: flex;
    align-items: center;
    gap: 8px;
}
.dt-preview-panel-head .dt-hint {
    font-size: 12px;
    color: #64748b;
    font-weight: 600;
}
.dt-kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 12px;
}
.dt-kpi-card {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 10px 14px;
    transition: all 0.2s ease;
}
.dt-kpi-card.paid {
    background: #f0fdf4;
    border-color: #bbf7d0;
}
.dt-kpi-card.balance {
    background: #fffbeb;
    border-color: #fde68a;
}
.dt-kpi-card .kpi-label {
    display: block;
    font-size: 11px;
    font-weight: 800;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    margin-bottom: 4px;
}
.dt-kpi-card.paid .kpi-label { color: #15803d; }
.dt-kpi-card.balance .kpi-label { color: #b45309; }
.dt-kpi-card .kpi-value {
    display: block;
    font-size: 18px;
    font-weight: 900;
    color: #08213f;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.dt-kpi-card.paid .kpi-value { color: #166534; }
.dt-kpi-card.balance .kpi-value { color: #92400e; }
.dt-kpi-card .kpi-sub {
    display: block;
    font-size: 11px;
    color: #94a3b8;
    margin-top: 2px;
}

.dt-section-card {
    background: #ffffff;
    border: 1px solid #dbe6f3;
    border-radius: 10px;
    margin-bottom: 20px;
    box-shadow: 0 4px 12px rgba(15,35,58,.03);
    overflow: hidden;
}
.dt-section-header {
    background: #f8fbff;
    border-bottom: 1px solid #e5edf7;
    padding: 12px 18px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.dt-section-header h4 {
    margin: 0;
    font-size: 14px;
    font-weight: 800;
    color: #08213f;
    display: flex;
    align-items: center;
    gap: 8px;
}
.dt-section-header h4 .fa {
    color: #0b63ce;
}
.dt-section-body {
    padding: 18px 20px;
}

.dt-mode-alert {
    border-radius: 6px;
    padding: 10px 14px;
    font-size: 12px;
    font-weight: 600;
    line-height: 1.45;
    margin-bottom: 16px;
    display: flex;
    align-items: flex-start;
    gap: 10px;
}
.dt-mode-alert.transfer-info {
    background: #eff6ff;
    border-left: 4px solid #2563eb;
    color: #1e40af;
}
.dt-mode-alert.merge-info {
    background: #f0fdf4;
    border-left: 4px solid #16a34a;
    color: #166534;
}

.dt-form-row {
    display: flex;
    gap: 16px;
    margin-bottom: 16px;
    flex-wrap: wrap;
}
.dt-form-row:last-child {
    margin-bottom: 0;
}
.dt-col {
    flex: 1;
    min-width: 220px;
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.dt-col.dt-col-full {
    flex: 1 1 100%;
}
.dt-col label {
    font-size: 12px;
    font-weight: 700;
    color: #334155;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    margin: 0;
}
.dt-col label .req {
    color: #e11d48;
}
.dt-col .form-control {
    border-radius: 6px;
    border: 1px solid #cbd5e1;
    height: 38px;
    padding: 6px 12px;
    font-size: 13px;
    color: #0f172a;
    font-weight: 600;
}
.dt-col textarea.form-control {
    height: auto;
    font-weight: 500;
}
.dt-col .form-control:focus {
    border-color: #0b63ce;
    box-shadow: 0 0 0 3px rgba(11, 99, 206, 0.12);
}
.dt-helper-text {
    font-size: 11px;
    color: #64748b;
}

/* Hidden container for backward compatibility */
#TransferPreviewText {
    display: none;
}
</style>

<div class="dt-form-shell">
    <!-- Live Financial KPI Preview Dashboard -->
    <div class="dt-preview-panel" id="TransferPreview">
        <div class="dt-preview-panel-head">
            <strong><i class="fa fa-dashboard" style="color:#0b63ce;"></i> <?php echo __('Source Deal Financial Summary');?></strong>
            <span class="dt-hint" id="TransferPreviewStatus"><?php echo __('Select a source deal to inspect credit value & fees');?></span>
            <span id="TransferPreviewText"><?php echo __('Select a source deal to calculate paid amount, balance, and suggested fee.');?></span>
        </div>
        <div class="dt-kpi-grid">
            <div class="dt-kpi-card">
                <span class="kpi-label"><i class="fa fa-tag"></i> <?php echo __('Total Deal Value');?></span>
                <span class="kpi-value" id="kpi_total_amount">0.00</span>
                <span class="kpi-sub"><?php echo __('Gross invoice amount');?></span>
            </div>
            <div class="dt-kpi-card paid">
                <span class="kpi-label"><i class="fa fa-check-circle"></i> <?php echo __('Paid / Merged Credit');?></span>
                <span class="kpi-value" id="kpi_paid_amount">0.00</span>
                <span class="kpi-sub"><?php echo __('Available payment balance');?></span>
            </div>
            <div class="dt-kpi-card balance">
                <span class="kpi-label"><i class="fa fa-hourglass-half"></i> <?php echo __('Remaining Balance');?></span>
                <span class="kpi-value" id="kpi_balance_amount">0.00</span>
                <span class="kpi-sub"><?php echo __('Outstanding due');?></span>
            </div>
            <div class="dt-kpi-card">
                <span class="kpi-label"><i class="fa fa-user"></i> <?php echo __('Registered Client');?></span>
                <span class="kpi-value" id="kpi_client_name" style="font-size:15px;"><?php echo __('None Selected');?></span>
                <span class="kpi-sub" id="kpi_property_sub"><?php echo __('Property / Unit info');?></span>
            </div>
        </div>
    </div>

    <!-- Section 1: Operation Mode & Source Deal -->
    <div class="dt-section-card">
        <div class="dt-section-header">
            <h4><i class="fa fa-exchange"></i> <?php echo __('Step 1: Choose Operation & Source Deal');?></h4>
            <span style="font-size:12px;font-weight:700;color:#64748b;"><?php echo __('Transfer or Merge Configuration');?></span>
        </div>
        <div class="dt-section-body">
            <div class="dt-form-row">
                <div class="dt-col">
                    <label><?php echo __('Transfer Type');?> <span class="req">*</span></label>
                    <?php echo $this->Form->select('transfer_type', $typeOptions, array('class'=>'form-control','id'=>'DealtransferTransferType','value'=>isset($this->request->data['Dealtransfer']['transfer_type']) ? $this->request->data['Dealtransfer']['transfer_type'] : 'Transfer'));?>
                    <span class="dt-helper-text"><?php echo __('Transfer assigns deal to another client. Merge consolidates paid funds into a target deal.');?></span>
                </div>
                <div class="dt-col">
                    <label><?php echo __('Request Date');?> <span class="req">*</span></label>
                    <div style="position:relative;">
                        <?php echo $this->Form->text('request_date', array('class'=>'form-control','value'=>isset($this->request->data['Dealtransfer']['request_date']) ? $this->request->data['Dealtransfer']['request_date'] : date('d-M-Y'),'autocomplete'=>'off'));?>
                    </div>
                    <span class="dt-helper-text"><?php echo __('Official date recorded for this transfer/merge agreement.');?></span>
                </div>
            </div>

            <div class="dt-form-row">
                <div class="dt-col dt-col-full">
                    <label><?php echo __('Source Deal to Transfer / Merge');?> <span class="req">*</span></label>
                    <?php echo $this->Form->select('source_deal_id', $dealOptions, array('empty'=>__('--- Please Select Source Deal ---'),'class'=>'form-control','id'=>'TransferSourceDeal','style'=>'font-weight:600;'));?>
                    <span class="dt-helper-text"><?php echo __('Select the deal that will either change owner or be surrendered into a target deal.');?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 2: Destination Assignment -->
    <div class="dt-section-card">
        <div class="dt-section-header">
            <h4><i class="fa fa-bullseye"></i> <?php echo __('Step 2: Destination & Target Details');?></h4>
            <span style="font-size:12px;font-weight:700;color:#64748b;"><?php echo __('Target Deal or Transferee Client');?></span>
        </div>
        <div class="dt-section-body">
            <!-- Dynamic Merge Mode Info Banner -->
            <div class="merge-row dt-mode-alert merge-info" style="display:none;">
                <i class="fa fa-info-circle fa-lg" style="margin-top:2px;"></i>
                <div>
                    <strong><?php echo __('Merge Mode Active:');?></strong>
                    <?php echo __('All payments from the source deal will be summarized into a single non-destructive credit entry (DMG-) on the target deal. Original source receipts remain preserved in the audit ledger, and the source deal will be marked as Transferd.');?>
                </div>
            </div>

            <!-- Dynamic Transfer Mode Info Banner -->
            <div class="transfer-row dt-mode-alert transfer-info">
                <i class="fa fa-info-circle fa-lg" style="margin-top:2px;"></i>
                <div>
                    <strong><?php echo __('Transfer Ownership Mode Active:');?></strong>
                    <?php echo __('The deal ownership will be transferred to the target client. Existing payment installments and property link will be preserved.');?>
                </div>
            </div>

            <!-- Merge Row: Target Deal Selector -->
            <div class="dt-form-row merge-row" style="display:none;">
                <div class="dt-col dt-col-full">
                    <label><?php echo __('Target Deal (Receives the Merged Credit)');?> <span class="req">*</span></label>
                    <?php echo $this->Form->select('target_deal_id', $dealOptions, array('empty'=>__('--- Please Select Target Deal ---'),'class'=>'form-control','id'=>'TransferTargetDeal','style'=>'font-weight:600;'));?>
                    <span class="dt-helper-text"><?php echo __('Select the active deal that will receive the merged credit payment and plan allocation.');?></span>
                </div>
            </div>

            <!-- Transfer Rows: Target Client & Agency -->
            <div class="dt-form-row transfer-row">
                <div class="dt-col">
                    <label><?php echo __('New Owner / Target Client');?> <span class="req">*</span></label>
                    <?php echo $this->Form->select('target_client_id', $clientOptions, array('empty'=>__('Same Client / Please Select'),'class'=>'form-control','id'=>'TransferTargetClient'));?>
                    <span class="dt-helper-text"><?php echo __('The client who will legally assume ownership of this deal.');?></span>
                </div>
                <div class="dt-col">
                    <label><?php echo __('Target Agency Scope');?></label>
                    <?php echo $this->Form->select('target_project_id', $agencyOptions, array('empty'=>__('Same Agency / Please Select'),'class'=>'form-control','id'=>'TransferTargetAgency'));?>
                    <span class="dt-helper-text"><?php echo __('Leave as Same Agency unless this deal is moving across authorized branches.');?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 3: Processing Fee & Accounting Voucher -->
    <div class="dt-section-card">
        <div class="dt-section-header">
            <h4><i class="fa fa-calculator"></i> <?php echo __('Step 3: Processing Fee & Accounting Voucher');?></h4>
            <span style="font-size:12px;font-weight:700;color:#64748b;"><?php echo __('Automated Double-Entry Fee Record');?></span>
        </div>
        <div class="dt-section-body">
            <div class="dt-form-row">
                <div class="dt-col">
                    <label><?php echo __('Processing Fee Type');?></label>
                    <?php echo $this->Form->select('processing_fee_type', $feeTypes, array('class'=>'form-control','id'=>'TransferFeeType','value'=>isset($this->request->data['Dealtransfer']['processing_fee_type']) ? $this->request->data['Dealtransfer']['processing_fee_type'] : $defaultFeeType));?>
                    <span class="dt-helper-text"><?php echo __('Fixed rate, percentage of deal value, custom, or waived.');?></span>
                </div>
                <div class="dt-col">
                    <label><?php echo __('Fee Percentage (%)');?></label>
                    <?php echo $this->Form->text('processing_fee_percent', array('class'=>'form-control','id'=>'TransferFeePercent','placeholder'=>'0.00','value'=>isset($this->request->data['Dealtransfer']['processing_fee_percent']) ? $this->request->data['Dealtransfer']['processing_fee_percent'] : $defaultFeePercent));?>
                    <span class="dt-helper-text"><?php echo __('Applied if Fee Type is set to Percentage.');?></span>
                </div>
                <div class="dt-col">
                    <label><?php echo __('Processing Fee Payable ('.$currencySymbol.')');?></label>
                    <?php echo $this->Form->text('processing_fee', array('class'=>'form-control','id'=>'TransferFee','placeholder'=>'0.00','style'=>'font-size:15px;font-weight:800;color:#0b63ce;','value'=>isset($this->request->data['Dealtransfer']['processing_fee']) ? $this->request->data['Dealtransfer']['processing_fee'] : $defaultTransferFee));?>
                    <span class="dt-helper-text"><?php echo __('Amount to be collected before finalization. Voucher prefix: DTV-.');?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 4: Audit Trail & Reason -->
    <div class="dt-section-card">
        <div class="dt-section-header">
            <h4><i class="fa fa-pencil-square-o"></i> <?php echo __('Step 4: Written Reason & Audit Documentation');?></h4>
            <span style="font-size:12px;font-weight:700;color:#64748b;"><?php echo __('Mandatory Audit Justification');?></span>
        </div>
        <div class="dt-section-body">
            <div class="dt-form-row">
                <div class="dt-col dt-col-full">
                    <label><?php echo __('Business Justification / Reason for Transfer or Merge');?> <span class="req">*</span></label>
                    <?php echo $this->Form->textarea('reason', array('class'=>'form-control','rows'=>3,'placeholder'=>__('Provide a complete reason for this request (e.g. "Customer requested plot upgrade from Unit 10 to Unit 22 as per signed application dated 24-Nov-2026"). This will be permanently recorded in the deal audit trail.')));?>
                    <span class="dt-helper-text"><?php echo __('Detailed reasons protect the agency and provide clear historical context for management and auditors.');?></span>
                </div>
            </div>
        </div>
    </div>
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
    function formatMoney(n){
        return parseFloat(n || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }
    function typeValue(){ return field('#DealtransferTransferType', 'transfer_type').val() || 'Transfer'; }
    function currentDefaultFixedFee(){
        return typeValue() === 'Merge' ? amount(defaultMergeFee) : amount(defaultTransferFee);
    }
    function toggleRows(){
        if(typeValue() === 'Merge'){
            $('.merge-row').show();
            $('.transfer-row').hide();
        } else {
            $('.merge-row').hide();
            $('.transfer-row').show();
        }
    }
    function computeFee(total, forceDefault){
        total = amount(total);
        var $feeType = field('#TransferFeeType', 'processing_fee_type');
        var $feePercent = field('#TransferFeePercent', 'processing_fee_percent');
        var $fee = field('#TransferFee', 'processing_fee');
        var feeType = $feeType.val() || defaultFeeType || 'Fixed';
        if(feeType === 'Waived'){
            $fee.val('0.00');
            $feePercent.val('0.00');
            return;
        }
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
        if(id <= 0){
            $('#TransferPreviewStatus').text(<?php echo json_encode(__('Select a source deal to inspect credit value & fees'));?>);
            $('#TransferPreviewText').text(<?php echo json_encode(__('Select a source deal to calculate paid amount, balance, and suggested fee.'));?>);
            $('#kpi_total_amount').text('0.00');
            $('#kpi_paid_amount').text('0.00');
            $('#kpi_balance_amount').text('0.00');
            $('#kpi_client_name').text(<?php echo json_encode(__('None Selected'));?>);
            $('#kpi_property_sub').text(<?php echo json_encode(__('Property / Unit info'));?>);
            return;
        }
        $.getJSON(metaUrlBase + '/' + id, function(resp){
            if(resp && resp.ok){
                sourceDealTotal = amount(resp.total_amount);
                var paidTotal = amount(resp.paid_amount);
                var balanceTotal = amount(resp.balance_amount);
                var clientName = resp.client_name || '-';

                // Update visual KPI metrics
                $('#kpi_total_amount').text(formatMoney(sourceDealTotal));
                $('#kpi_paid_amount').text(formatMoney(paidTotal));
                $('#kpi_balance_amount').text(formatMoney(balanceTotal));
                $('#kpi_client_name').text(clientName);
                $('#kpi_property_sub').text('Deal #' + (resp.deal_id || id) + ' | Agency #' + (resp.project_id || '-'));
                $('#TransferPreviewStatus').html('<span style="color:#16a34a;font-weight:700;"><i class="fa fa-check-circle"></i> Deal #' + id + ' Verified</span>');

                if(!field('#TransferTargetClient', 'target_client_id').val()){ field('#TransferTargetClient', 'target_client_id').val(resp.client_id); }
                if(!field('#TransferTargetAgency', 'target_project_id').val()){ field('#TransferTargetAgency', 'target_project_id').val(resp.project_id); }
                applySourceDefaults(resp);
                $('#TransferPreviewText').text('Deal total: ' + parseFloat(resp.total_amount || 0).toFixed(2) + ' | Paid: ' + parseFloat(resp.paid_amount || 0).toFixed(2) + ' | Balance: ' + parseFloat(resp.balance_amount || 0).toFixed(2) + ' | Client: ' + clientName);
            }
        });
    }

    field('#DealtransferTransferType', 'transfer_type').on('change', function(){
        toggleRows();
        computeFee(sourceDealTotal, !userChangedFee);
    });
    field('#TransferSourceDeal', 'source_deal_id').on('change', function(){
        userChangedFee = false;
        userChangedFeeType = false;
        loadMeta();
    });
    field('#TransferFeeType', 'processing_fee_type').on('change', function(){
        userChangedFeeType = true;
        computeFee(sourceDealTotal, true);
    });
    field('#TransferFeePercent', 'processing_fee_percent').on('input change', function(){
        userChangedFee = true;
        computeFee(sourceDealTotal, false);
    });
    field('#TransferFee', 'processing_fee').on('input change', function(){
        userChangedFee = true;
    });

    toggleRows();
    loadMeta();
});
</script>
