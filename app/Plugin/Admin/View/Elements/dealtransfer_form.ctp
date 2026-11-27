<?php
$defaultSettings = isset($defaultSettings) ? $defaultSettings : array();
$defaultFeeType = isset($defaultSettings['default_fee_type']) ? $defaultSettings['default_fee_type'] : 'Fixed';
$defaultFeeDeductionSource = isset($defaultSettings['default_fee_deduction_source']) ? $defaultSettings['default_fee_deduction_source'] : 'file_paid';
$defaultTransferFee = isset($defaultSettings['default_transfer_fee']) ? (float)$defaultSettings['default_transfer_fee'] : 0;
$defaultMergeFee = isset($defaultSettings['default_merge_fee']) ? (float)$defaultSettings['default_merge_fee'] : 0;
$defaultFeePercent = isset($defaultSettings['default_fee_percent']) ? (float)$defaultSettings['default_fee_percent'] : 0;
$currencySymbol = isset($currency) ? $currency : 'PKR';
$currentFeeDeductionSource = isset($this->request->data['Dealtransfer']['fee_deduction_source']) ? $this->request->data['Dealtransfer']['fee_deduction_source'] : $defaultFeeDeductionSource;
?>
<style type="text/css">
.dt-form-shell {
    margin-bottom: 25px;
}
/* Step Navigation Bar */
.dt-step-nav {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}
.dt-step-pill {
    flex: 1;
    min-width: 180px;
    background: #ffffff;
    border: 1px solid #dbe6f3;
    border-radius: 8px;
    padding: 12px 14px;
    display: flex;
    align-items: center;
    gap: 10px;
    box-shadow: 0 2px 6px rgba(15,35,58,.03);
    transition: all 0.2s ease;
}
.dt-step-pill.active {
    border-color: #0b63ce;
    background: #f0f7ff;
}
.dt-step-num {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: #e2e8f0;
    color: #475569;
    font-size: 13px;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.dt-step-pill.active .dt-step-num {
    background: #0b63ce;
    color: #ffffff;
}
.dt-step-text strong {
    display: block;
    font-size: 13px;
    color: #08213f;
    font-weight: 800;
}
.dt-step-text span {
    font-size: 11px;
    color: #64748b;
    font-weight: 500;
}

/* Financial Dashboard Panel */
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
.dt-kpi-card.net-merge {
    background: #eff6ff;
    border-color: #bfdbfe;
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
.dt-kpi-card.net-merge .kpi-label { color: #1d4ed8; }
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
.dt-kpi-card.net-merge .kpi-value { color: #1e40af; }
.dt-kpi-card .kpi-sub {
    display: block;
    font-size: 11px;
    color: #94a3b8;
    margin-top: 2px;
}

/* Step Card Styles */
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
    padding: 14px 20px;
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
.dt-section-header h4 .step-tag {
    background: #0b63ce;
    color: #ffffff;
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
}
.dt-section-body {
    padding: 18px 20px;
}

.dt-mode-alert {
    border-radius: 6px;
    padding: 12px 16px;
    font-size: 12.5px;
    font-weight: 600;
    line-height: 1.45;
    margin-bottom: 16px;
    display: flex;
    align-items: flex-start;
    gap: 12px;
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

/* Fee Deduction Mode Selection Radio Cards */
.dt-deduction-options {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 14px;
    margin-bottom: 18px;
}
.dt-deduction-card {
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    padding: 14px 16px;
    background: #f8fafc;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: flex-start;
    gap: 12px;
}
.dt-deduction-card:hover {
    border-color: #94a3b8;
    background: #f1f5f9;
}
.dt-deduction-card.selected {
    border-color: #0b63ce;
    background: #f0f7ff;
    box-shadow: 0 0 0 3px rgba(11,99,206,.12);
}
.dt-deduction-card input[type="radio"] {
    margin: 3px 0 0;
    cursor: pointer;
    width: 17px;
    height: 17px;
}
.dt-deduction-body strong {
    display: block;
    font-size: 13px;
    font-weight: 800;
    color: #08213f;
    margin-bottom: 3px;
}
.dt-deduction-body p {
    margin: 0;
    font-size: 11.5px;
    color: #64748b;
    line-height: 1.35;
}
.dt-deduction-body .badge-tag {
    display: inline-block;
    font-size: 10px;
    font-weight: 800;
    padding: 2px 6px;
    border-radius: 3px;
    margin-top: 5px;
    text-transform: uppercase;
}
.badge-tag.auto-deduct {
    background: #dcfce7;
    color: #15803d;
    border: 1px solid #86efac;
}
.badge-tag.separate-cash {
    background: #fef3c7;
    color: #b45309;
    border: 1px solid #fde68a;
}

/* Live Settlement Calculator Box */
.dt-calc-box {
    background: #fdfefe;
    border: 1px solid #dbe6f3;
    border-radius: 8px;
    padding: 16px 20px;
    margin-top: 15px;
    box-shadow: inset 0 1px 3px rgba(0,0,0,0.02);
}
.dt-calc-title {
    font-size: 13px;
    font-weight: 800;
    color: #08213f;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.dt-calc-table {
    width: 100%;
    border-collapse: collapse;
}
.dt-calc-table tr td {
    padding: 6px 0;
    font-size: 13px;
    color: #334155;
    border-bottom: 1px dashed #e2e8f0;
}
.dt-calc-table tr:last-child td {
    border-bottom: none;
    padding-top: 10px;
    font-weight: 800;
    font-size: 14px;
}
.dt-calc-table tr td.text-right {
    text-align: right;
    font-weight: 700;
}

/* Review Breakdown Table (Step 4) */
.dt-review-table-container {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    overflow: hidden;
    margin-top: 15px;
}
.dt-review-table {
    width: 100%;
    border-collapse: collapse;
}
.dt-review-table th {
    background: #f8fafc;
    padding: 10px 14px;
    font-size: 11.5px;
    font-weight: 800;
    color: #475569;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    border-bottom: 1px solid #e2e8f0;
}
.dt-review-table td {
    padding: 10px 14px;
    font-size: 12.5px;
    color: #1e293b;
    border-bottom: 1px solid #f1f5f9;
}
.dt-review-table tr:last-child td {
    border-bottom: none;
}
.dt-review-table .highlight-row {
    background: #f0fdf4;
    font-weight: 700;
}

/* Backward compatibility hidden element */
#TransferPreviewText {
    display: none;
}
</style>

<div class="dt-form-shell">
    <!-- Step Navigation Pills -->
    <div class="dt-step-nav">
        <div class="dt-step-pill active" id="step_nav_1">
            <div class="dt-step-num">1</div>
            <div class="dt-step-text">
                <strong><?php echo __('Operation & Source');?></strong>
                <span><?php echo __('Deal & Date selection');?></span>
            </div>
        </div>
        <div class="dt-step-pill" id="step_nav_2">
            <div class="dt-step-num">2</div>
            <div class="dt-step-text">
                <strong><?php echo __('Destination Details');?></strong>
                <span><?php echo __('Target deal or new owner');?></span>
            </div>
        </div>
        <div class="dt-step-pill" id="step_nav_3">
            <div class="dt-step-num">3</div>
            <div class="dt-step-text">
                <strong><?php echo __('Processing Fee & Settlement');?></strong>
                <span><?php echo __('Auto-deduct or separate cash');?></span>
            </div>
        </div>
        <div class="dt-step-pill" id="step_nav_4">
            <div class="dt-step-num">4</div>
            <div class="dt-step-text">
                <strong><?php echo __('Reason & Review');?></strong>
                <span><?php echo __('Safety verification & save');?></span>
            </div>
        </div>
    </div>

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
                <span class="kpi-label"><i class="fa fa-check-circle"></i> <?php echo __('Paid Available Credit');?></span>
                <span class="kpi-value" id="kpi_paid_amount">0.00</span>
                <span class="kpi-sub"><?php echo __('Funds paid by client');?></span>
            </div>
            <div class="dt-kpi-card balance">
                <span class="kpi-label"><i class="fa fa-hourglass-half"></i> <?php echo __('Remaining Balance');?></span>
                <span class="kpi-value" id="kpi_balance_amount">0.00</span>
                <span class="kpi-sub"><?php echo __('Outstanding due on source');?></span>
            </div>
            <div class="dt-kpi-card net-merge merge-row" style="display:none;">
                <span class="kpi-label"><i class="fa fa-arrow-circle-right"></i> <?php echo __('Net Merge to Destination');?></span>
                <span class="kpi-value" id="kpi_net_merge_amount">0.00</span>
                <span class="kpi-sub" id="kpi_net_merge_sub"><?php echo __('After fee deduction');?></span>
            </div>
            <div class="dt-kpi-card">
                <span class="kpi-label"><i class="fa fa-user"></i> <?php echo __('Source Client');?></span>
                <span class="kpi-value" id="kpi_client_name" style="font-size:15px;"><?php echo __('None Selected');?></span>
                <span class="kpi-sub" id="kpi_property_sub"><?php echo __('Property / Unit info');?></span>
            </div>
        </div>
    </div>

    <!-- Step 1: Operation Mode & Source Deal -->
    <div class="dt-section-card">
        <div class="dt-section-header">
            <h4>
                <span class="step-tag"><?php echo __('Step 1');?></span>
                <i class="fa fa-exchange" style="color:#0b63ce;"></i>
                <?php echo __('Choose Operation & Source Deal');?>
            </h4>
            <span style="font-size:12px;font-weight:700;color:#64748b;"><?php echo __('Transfer Deal vs Merge Deal');?></span>
        </div>
        <div class="dt-section-body">
            <div class="dt-form-row">
                <div class="dt-col">
                    <label><?php echo __('Transfer Type');?> <span class="req">*</span></label>
                    <?php echo $this->Form->select('transfer_type', $typeOptions, array('class'=>'form-control','id'=>'DealtransferTransferType','value'=>isset($this->request->data['Dealtransfer']['transfer_type']) ? $this->request->data['Dealtransfer']['transfer_type'] : 'Transfer'));?>
                    <span class="dt-helper-text"><?php echo __('Transfer changes deal ownership. Merge consolidates source paid funds into a target deal.');?></span>
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
                    <label><?php echo __('Source Deal (To Transfer or Consolidate)');?> <span class="req">*</span></label>
                    <?php echo $this->Form->select('source_deal_id', $dealOptions, array('empty'=>__('--- Please Select Source Deal ---'),'class'=>'form-control','id'=>'TransferSourceDeal','style'=>'font-weight:600;'));?>
                    <span class="dt-helper-text"><?php echo __('Select the deal whose ownership will transfer or whose payments will merge into destination file.');?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Step 2: Destination Assignment -->
    <div class="dt-section-card">
        <div class="dt-section-header">
            <h4>
                <span class="step-tag"><?php echo __('Step 2');?></span>
                <i class="fa fa-bullseye" style="color:#0b63ce;"></i>
                <?php echo __('Destination & Target Assignment Details');?>
            </h4>
            <span style="font-size:12px;font-weight:700;color:#64748b;"><?php echo __('Destination File or New Owner Client');?></span>
        </div>
        <div class="dt-section-body">
            <!-- Dynamic Merge Mode Info Banner -->
            <div class="merge-row dt-mode-alert merge-info" style="display:none;">
                <i class="fa fa-info-circle fa-lg" style="margin-top:2px;"></i>
                <div>
                    <strong><?php echo __('Merge Mode Active:');?></strong>
                    <?php echo __('Payments from the source deal will be merged into the target deal as an official credit entry (DMG-). Original source receipts remain preserved for audit, and the source file will be marked as Transferd.');?>
                </div>
            </div>

            <!-- Dynamic Transfer Mode Info Banner -->
            <div class="transfer-row dt-mode-alert transfer-info">
                <i class="fa fa-info-circle fa-lg" style="margin-top:2px;"></i>
                <div>
                    <strong><?php echo __('Transfer Ownership Mode Active:');?></strong>
                    <?php echo __('The deal ownership will be legally assigned to the target client. Existing payment installments and property connection remain intact.');?>
                </div>
            </div>

            <!-- Merge Row: Target Deal Selector & Target KPI Preview -->
            <div class="dt-form-row merge-row" style="display:none;">
                <div class="dt-col dt-col-full">
                    <label><?php echo __('Destination Deal (Receives the Merged Credit)');?> <span class="req">*</span></label>
                    <?php echo $this->Form->select('target_deal_id', $dealOptions, array('empty'=>__('--- Please Select Destination Deal ---'),'class'=>'form-control','id'=>'TransferTargetDeal','style'=>'font-weight:600;'));?>
                    <span class="dt-helper-text"><?php echo __('Select the destination deal that will receive the net merged credit payment.');?></span>
                </div>
            </div>

            <!-- Target Deal Live Preview Cards -->
            <div class="merge-row" id="targetDealPreviewBox" style="display:none;margin-bottom:16px;">
                <div style="background:#f1f5f9;border:1px solid #cbd5e1;border-radius:8px;padding:12px 16px;">
                    <div style="font-size:12px;font-weight:800;color:#334155;margin-bottom:8px;text-transform:uppercase;">
                        <i class="fa fa-folder-open-o" style="color:#0b63ce;"></i> <?php echo __('Destination Deal Live Status:');?>
                    </div>
                    <div class="dt-kpi-grid">
                        <div class="dt-kpi-card" style="background:#ffffff;">
                            <span class="kpi-label"><?php echo __('Target Client');?></span>
                            <span class="kpi-value" id="target_kpi_client" style="font-size:14px;">-</span>
                        </div>
                        <div class="dt-kpi-card" style="background:#ffffff;">
                            <span class="kpi-label"><?php echo __('Target Total Value');?></span>
                            <span class="kpi-value" id="target_kpi_total">0.00</span>
                        </div>
                        <div class="dt-kpi-card paid" style="background:#ffffff;">
                            <span class="kpi-label"><?php echo __('Target Existing Paid');?></span>
                            <span class="kpi-value" id="target_kpi_paid">0.00</span>
                        </div>
                        <div class="dt-kpi-card balance" style="background:#ffffff;">
                            <span class="kpi-label"><?php echo __('Target Due Balance');?></span>
                            <span class="kpi-value" id="target_kpi_balance">0.00</span>
                        </div>
                        <div class="dt-kpi-card net-merge" style="background:#ffffff;">
                            <span class="kpi-label"><?php echo __('Projected Balance After Merge');?></span>
                            <span class="kpi-value" id="target_kpi_projected_balance">0.00</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Transfer Rows: Target Client & Agency -->
            <div class="dt-form-row transfer-row">
                <div class="dt-col">
                    <label><?php echo __('New Owner / Transferee Client');?> <span class="req">*</span></label>
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

    <!-- Step 3: Processing Fee & Deduction Settlement Mode -->
    <div class="dt-section-card">
        <div class="dt-section-header">
            <h4>
                <span class="step-tag"><?php echo __('Step 3');?></span>
                <i class="fa fa-calculator" style="color:#0b63ce;"></i>
                <?php echo __('Processing Fee & Deduction Settlement Method');?>
            </h4>
            <span style="font-size:12px;font-weight:700;color:#64748b;"><?php echo __('Auto-Deduct from Paid Funds or Pay in Cash');?></span>
        </div>
        <div class="dt-section-body">
            <!-- Fee Deduction Source Selection Cards -->
            <label style="font-size:12px;font-weight:700;color:#334155;text-transform:uppercase;letter-spacing:0.3px;margin-bottom:8px;display:block;">
                <?php echo __('How Should the Processing Fee be Settled?');?> <span class="req">*</span>
            </label>
            <div class="dt-deduction-options">
                <div class="dt-deduction-card <?php echo ($currentFeeDeductionSource === 'file_paid') ? 'selected' : '';?>" id="card_fee_file_paid">
                    <input type="radio" name="data[Dealtransfer][fee_deduction_source]" id="fee_source_file_paid" value="file_paid" <?php echo ($currentFeeDeductionSource === 'file_paid') ? 'checked="checked"' : '';?> />
                    <div class="dt-deduction-body">
                        <strong><i class="fa fa-minus-circle" style="color:#15803d;"></i> <?php echo __('Deduct from Paid Amount (Property Balance)');?></strong>
                        <p><?php echo __('Auto-deduct processing fee from client\'s paid payments on the source deal. The net remaining balance is credited to the destination file. Safest and fastest.');?></p>
                        <span class="badge-tag auto-deduct"><i class="fa fa-check"></i> <?php echo __('Auto-Settled / No Pre-Payment Needed');?></span>
                    </div>
                </div>

                <div class="dt-deduction-card <?php echo ($currentFeeDeductionSource === 'cash_separate') ? 'selected' : '';?>" id="card_fee_cash_separate">
                    <input type="radio" name="data[Dealtransfer][fee_deduction_source]" id="fee_source_cash_separate" value="cash_separate" <?php echo ($currentFeeDeductionSource === 'cash_separate') ? 'checked="checked"' : '';?> />
                    <div class="dt-deduction-body">
                        <strong><i class="fa fa-money" style="color:#b45309;"></i> <?php echo __('Cash / Bank (Pay Separately)');?></strong>
                        <p><?php echo __('Client pays the processing fee in cash or bank transfer separately. 100% of the source paid amount merges into the destination file.');?></p>
                        <span class="badge-tag separate-cash"><i class="fa fa-clock-o"></i> <?php echo __('Fee Voucher Must Be Paid First');?></span>
                    </div>
                </div>
            </div>

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
                    <label><?php echo __('Processing Fee Amount ('.$currencySymbol.')');?></label>
                    <?php echo $this->Form->text('processing_fee', array('class'=>'form-control','id'=>'TransferFee','placeholder'=>'0.00','style'=>'font-size:15px;font-weight:800;color:#0b63ce;','value'=>isset($this->request->data['Dealtransfer']['processing_fee']) ? $this->request->data['Dealtransfer']['processing_fee'] : $defaultTransferFee));?>
                    <span class="dt-helper-text"><?php echo __('Total fee calculated for this transaction.');?></span>
                </div>
            </div>

            <!-- Live Financial Settlement Breakdown -->
            <div class="dt-calc-box" id="settlementCalcBox">
                <div class="dt-calc-title">
                    <i class="fa fa-balance-scale" style="color:#0b63ce;"></i>
                    <span><?php echo __('Live Financial Settlement Calculator:');?></span>
                </div>
                <table class="dt-calc-table">
                    <tr>
                        <td><i class="fa fa-arrow-circle-right" style="color:#16a34a;"></i> <?php echo __('Source Property Paid Amount:');?></td>
                        <td class="text-right" id="calc_gross_paid">0.00</td>
                    </tr>
                    <tr id="calc_fee_row">
                        <td><i class="fa fa-minus-circle" style="color:#dc2626;"></i> <?php echo __('Processing Fee Deduction:');?> (<span id="calc_fee_mode_text"><?php echo __('Deducted from Paid Balance');?></span>)</td>
                        <td class="text-right" id="calc_fee_deducted" style="color:#dc2626;">- 0.00</td>
                    </tr>
                    <tr class="highlight-row">
                        <td><i class="fa fa-check-circle" style="color:#0b63ce;"></i> <strong><?php echo __('Net Merge Credit Transferred to Destination:');?></strong></td>
                        <td class="text-right" id="calc_net_merge" style="color:#0b63ce;font-size:16px;">0.00</td>
                    </tr>
                </table>
                <div id="fee_warning_msg" style="display:none;margin-top:10px;padding:8px 12px;background:#fef2f2;border-left:4px solid #ef4444;color:#b91c1c;font-size:12px;font-weight:700;">
                    <i class="fa fa-exclamation-triangle"></i> <?php echo __('Warning: Processing fee cannot exceed source deal paid amount when deducting from paid balance.');?>
                </div>
            </div>
        </div>
    </div>

    <!-- Step 4: Audit Trail, Reason & Live Review Breakdown -->
    <div class="dt-section-card">
        <div class="dt-section-header">
            <h4>
                <span class="step-tag"><?php echo __('Step 4');?></span>
                <i class="fa fa-pencil-square-o" style="color:#0b63ce;"></i>
                <?php echo __('Written Reason & Pre-Finalization Review');?>
            </h4>
            <span style="font-size:12px;font-weight:700;color:#64748b;"><?php echo __('Safe, Readable & Editable Breakdown');?></span>
        </div>
        <div class="dt-section-body">
            <div class="dt-form-row">
                <div class="dt-col dt-col-full">
                    <label><?php echo __('Business Justification / Written Reason');?> <span class="req">*</span></label>
                    <?php echo $this->Form->textarea('reason', array('class'=>'form-control','rows'=>3,'placeholder'=>__('Enter clear reason for transfer or merge (e.g. "Customer requested plot upgrade from Unit 10 to Unit 22. Fee deducted from paid file payments."). This will be permanently recorded in the audit trail.')));?>
                    <span class="dt-helper-text"><?php echo __('Detailed reasons protect the agency and provide clear historical context for management and auditors.');?></span>
                </div>
            </div>

            <!-- Complete Pre-Finalization Review Summary Table -->
            <div class="dt-review-table-container">
                <div style="background:#f8fafc;padding:10px 14px;border-bottom:1px solid #e2e8f0;font-size:13px;font-weight:800;color:#08213f;">
                    <i class="fa fa-eye" style="color:#0b63ce;"></i> <?php echo __('Pre-Finalization Review Summary (Read & Verify Before Saving):');?>
                </div>
                <table class="dt-review-table">
                    <tr>
                        <th style="width:28%;"><?php echo __('Parameter');?></th>
                        <th><?php echo __('Configured Value & Settlement Terms');?></th>
                    </tr>
                    <tr>
                        <td><strong><?php echo __('Operation Mode');?></strong></td>
                        <td id="review_operation_mode">Transfer Deal</td>
                    </tr>
                    <tr>
                        <td><strong><?php echo __('Source Deal & Property');?></strong></td>
                        <td id="review_source_deal"><?php echo __('None Selected');?></td>
                    </tr>
                    <tr>
                        <td><strong><?php echo __('Destination / Target');?></strong></td>
                        <td id="review_target_dest"><?php echo __('None Selected');?></td>
                    </tr>
                    <tr>
                        <td><strong><?php echo __('Source Property Paid Balance');?></strong></td>
                        <td id="review_gross_paid">0.00</td>
                    </tr>
                    <tr>
                        <td><strong><?php echo __('Processing Fee & Method');?></strong></td>
                        <td id="review_fee_method">0.00 (Deduct from Paid Amount)</td>
                    </tr>
                    <tr class="highlight-row">
                        <td><strong><?php echo __('Net Merged Credit to Destination');?></strong></td>
                        <td id="review_net_merge" style="color:#0b63ce;font-size:14px;">0.00</td>
                    </tr>
                    <tr>
                        <td><strong><?php echo __('Audit & Safety Verification');?></strong></td>
                        <td style="color:#15803d;font-weight:700;">
                            <i class="fa fa-shield"></i> <?php echo __('Non-destructive audit trail enabled. Source deal receipts remain intact in database.');?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
jQuery(function($){
    var metaUrlBase = <?php echo json_encode($this->Html->url(array('controller'=>'Dealtransfers','action'=>'dealmeta')));?>;
    var defaultTransferFee = <?php echo json_encode($defaultTransferFee);?>;
    var defaultMergeFee = <?php echo json_encode($defaultMergeFee);?>;
    var defaultFeeType = <?php echo json_encode($defaultFeeType);?>;
    var defaultFeePercent = <?php echo json_encode($defaultFeePercent);?>;
    var defaultFeeDeductionSource = <?php echo json_encode($defaultFeeDeductionSource);?>;

    var sourceDealTotal = 0;
    var sourceDealPaid = 0;
    var sourceDealBalance = 0;
    var sourceClientName = '';
    var sourceDealInvoice = '';

    var targetDealTotal = 0;
    var targetDealPaid = 0;
    var targetDealBalance = 0;
    var targetClientName = '';
    var targetDealInvoice = '';

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
    function deductionSourceValue(){
        return $('input[name="data[Dealtransfer][fee_deduction_source]"]:checked').val() || 'file_paid';
    }

    function toggleRows(){
        var isMerge = (typeValue() === 'Merge');
        if(isMerge){
            $('.merge-row').show();
            $('.transfer-row').hide();
            $('#step_nav_2 .dt-step-text strong').text(<?php echo json_encode(__('Destination Deal'));?>);
            $('#step_nav_2 .dt-step-text span').text(<?php echo json_encode(__('Target deal receiving funds'));?>);
        } else {
            $('.merge-row').hide();
            $('.transfer-row').show();
            $('#step_nav_2 .dt-step-text strong').text(<?php echo json_encode(__('New Owner Assignment'));?>);
            $('#step_nav_2 .dt-step-text span').text(<?php echo json_encode(__('Target client & agency'));?>);
        }
        updateCalculations();
    }

    function computeFee(total, forceDefault){
        total = amount(total);
        var $feeType = field('#TransferFeeType', 'processing_fee_type');
        var $feePercent = field('#TransferFeePercent', 'processing_fee_percent');
        var $fee = field('#TransferFee', 'processing_fee');
        var feeType = $feeType.val() || defaultFeeType || 'Fixed';
        var defFixed = (typeValue() === 'Merge') ? amount(defaultMergeFee) : amount(defaultTransferFee);

        if(feeType === 'Waived'){
            $fee.val('0.00');
            $feePercent.val('0.00');
            updateCalculations();
            return;
        }
        if(feeType === 'Percent'){
            var percent = amount($feePercent.val());
            if((forceDefault || percent <= 0) && amount(defaultFeePercent) > 0){
                percent = amount(defaultFeePercent);
                $feePercent.val(percent.toFixed(2));
            }
            $fee.val(((total * percent) / 100).toFixed(2));
            updateCalculations();
            return;
        }
        if(feeType === 'Fixed' || feeType === 'Custom'){
            if(forceDefault || amount($fee.val()) <= 0){
                $fee.val(defFixed.toFixed(2));
            }
        }
        updateCalculations();
    }

    function updateCalculations(){
        var fee = amount(field('#TransferFee', 'processing_fee').val());
        var deductionMode = deductionSourceValue();
        var isMerge = (typeValue() === 'Merge');

        var netMerge = 0;
        var feeDeducted = 0;

        if(isMerge){
            if(deductionMode === 'file_paid'){
                feeDeducted = fee;
                if(fee > sourceDealPaid && sourceDealPaid > 0){
                    $('#fee_warning_msg').show();
                    netMerge = 0;
                } else {
                    $('#fee_warning_msg').hide();
                    netMerge = Math.max(0, sourceDealPaid - fee);
                }
                $('#calc_fee_row').show();
                $('#calc_fee_mode_text').text(<?php echo json_encode(__('Auto-deducted from paid balance'));?>);
                $('#calc_fee_deducted').text('- ' + formatMoney(feeDeducted));
                $('#kpi_net_merge_sub').text(<?php echo json_encode(__('After fee auto-deduction'));?>);
            } else {
                feeDeducted = 0;
                netMerge = sourceDealPaid;
                $('#fee_warning_msg').hide();
                $('#calc_fee_row').show();
                $('#calc_fee_mode_text').text(<?php echo json_encode(__('Paid separately via Cash/Bank'));?>);
                $('#calc_fee_deducted').text('0.00 (Cash)');
                $('#kpi_net_merge_sub').text(<?php echo json_encode(__('100% merged (Fee paid separately)'));?>);
            }
        } else {
            $('#fee_warning_msg').hide();
            netMerge = sourceDealPaid;
            $('#calc_fee_row').show();
            $('#calc_fee_mode_text').text((deductionMode === 'file_paid') ? <?php echo json_encode(__('Deducted from property balance'));?> : <?php echo json_encode(__('Paid separately via Cash/Bank'));?>);
            $('#calc_fee_deducted').text((deductionMode === 'file_paid' ? '- ' : '') + formatMoney(fee));
        }

        // Update Settlement Box
        $('#calc_gross_paid').text(formatMoney(sourceDealPaid));
        $('#calc_net_merge').text(formatMoney(netMerge));
        $('#kpi_net_merge_amount').text(formatMoney(netMerge));

        // Update Target Deal Projected Balance
        if(targetDealBalance > 0){
            var projectedBalance = Math.max(0, targetDealBalance - netMerge);
            $('#target_kpi_projected_balance').text(formatMoney(projectedBalance));
        } else {
            $('#target_kpi_projected_balance').text('0.00');
        }

        // Update Review Summary Table (Step 4)
        $('#review_operation_mode').html('<strong>' + (isMerge ? <?php echo json_encode(__('Deal Merge (Consolidation)'));?> : <?php echo json_encode(__('Ownership Transfer'));?>) + '</strong>');
        var srcText = sourceDealInvoice ? (sourceDealInvoice + ' (' + sourceClientName + ')') : <?php echo json_encode(__('None Selected'));?>;
        $('#review_source_deal').text(srcText);

        var destText = '';
        if(isMerge){
            destText = targetDealInvoice ? (targetDealInvoice + ' (' + targetClientName + ')') : <?php echo json_encode(__('None Selected'));?>;
        } else {
            var targetClientOpt = field('#TransferTargetClient', 'target_client_id').find('option:selected').text();
            destText = targetClientOpt || <?php echo json_encode(__('Same Client'));?>;
        }
        $('#review_target_dest').text(destText);

        $('#review_gross_paid').text('Rs ' + formatMoney(sourceDealPaid));
        var feeDesc = 'Rs ' + formatMoney(fee) + ' (' + ((deductionMode === 'file_paid') ? <?php echo json_encode(__('Deduct from Paid Amount'));?> : <?php echo json_encode(__('Cash / Bank Separate'));?>) + ')';
        $('#review_fee_method').text(feeDesc);
        $('#review_net_merge').text('Rs ' + formatMoney(netMerge));
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
        if(resp.default_fee_deduction_source && !$('input[name="data[Dealtransfer][fee_deduction_source]"]:checked').length){
            $('input[name="data[Dealtransfer][fee_deduction_source]"][value="' + resp.default_fee_deduction_source + '"]').prop('checked', true);
            $('.dt-deduction-card').removeClass('selected');
            $('#card_fee_' + resp.default_fee_deduction_source).addClass('selected');
        }
        computeFee(sourceDealTotal, !userChangedFee);
    }

    function loadSourceMeta(){
        var id = parseInt(field('#TransferSourceDeal', 'source_deal_id').val() || 0, 10) || 0;
        if(id <= 0){
            sourceDealTotal = 0;
            sourceDealPaid = 0;
            sourceDealBalance = 0;
            sourceClientName = '';
            sourceDealInvoice = '';
            $('#TransferPreviewStatus').text(<?php echo json_encode(__('Select a source deal to inspect credit value & fees'));?>);
            $('#kpi_total_amount').text('0.00');
            $('#kpi_paid_amount').text('0.00');
            $('#kpi_balance_amount').text('0.00');
            $('#kpi_net_merge_amount').text('0.00');
            $('#kpi_client_name').text(<?php echo json_encode(__('None Selected'));?>);
            $('#kpi_property_sub').text(<?php echo json_encode(__('Property / Unit info'));?>);
            updateCalculations();
            return;
        }
        $.getJSON(metaUrlBase + '/' + id, function(resp){
            if(resp && resp.ok){
                sourceDealTotal = amount(resp.total_amount);
                sourceDealPaid = amount(resp.paid_amount);
                sourceDealBalance = amount(resp.balance_amount);
                sourceClientName = resp.client_name || '-';
                sourceDealInvoice = resp.invoice_no || ('Deal #' + id);

                $('#kpi_total_amount').text(formatMoney(sourceDealTotal));
                $('#kpi_paid_amount').text(formatMoney(sourceDealPaid));
                $('#kpi_balance_amount').text(formatMoney(sourceDealBalance));
                $('#kpi_client_name').text(sourceClientName);
                $('#kpi_property_sub').text(sourceDealInvoice + (resp.property_name ? (' | ' + resp.property_name) : ''));
                $('#TransferPreviewStatus').html('<span style="color:#16a34a;font-weight:700;"><i class="fa fa-check-circle"></i> ' + sourceDealInvoice + ' Verified</span>');

                if(!field('#TransferTargetClient', 'target_client_id').val()){ field('#TransferTargetClient', 'target_client_id').val(resp.client_id); }
                if(!field('#TransferTargetAgency', 'target_project_id').val()){ field('#TransferTargetAgency', 'target_project_id').val(resp.project_id); }
                applySourceDefaults(resp);
                updateCalculations();
            }
        });
    }

    function loadTargetMeta(){
        var id = parseInt(field('#TransferTargetDeal', 'target_deal_id').val() || 0, 10) || 0;
        if(id <= 0){
            targetDealTotal = 0;
            targetDealPaid = 0;
            targetDealBalance = 0;
            targetClientName = '';
            targetDealInvoice = '';
            $('#targetDealPreviewBox').hide();
            updateCalculations();
            return;
        }
        $.getJSON(metaUrlBase + '/' + id, function(resp){
            if(resp && resp.ok){
                targetDealTotal = amount(resp.total_amount);
                targetDealPaid = amount(resp.paid_amount);
                targetDealBalance = amount(resp.balance_amount);
                targetClientName = resp.client_name || '-';
                targetDealInvoice = resp.invoice_no || ('Deal #' + id);

                $('#target_kpi_client').text(targetClientName + (resp.property_name ? (' - ' + resp.property_name) : ''));
                $('#target_kpi_total').text(formatMoney(targetDealTotal));
                $('#target_kpi_paid').text(formatMoney(targetDealPaid));
                $('#target_kpi_balance').text(formatMoney(targetDealBalance));
                $('#targetDealPreviewBox').show();
                updateCalculations();
            }
        });
    }

    // Deduction card toggle
    $('.dt-deduction-card').on('click', function(){
        $('.dt-deduction-card').removeClass('selected');
        $(this).addClass('selected');
        $(this).find('input[type="radio"]').prop('checked', true);
        updateCalculations();
    });
    $('input[name="data[Dealtransfer][fee_deduction_source]"]').on('change', function(){
        $('.dt-deduction-card').removeClass('selected');
        $('#card_fee_' + $(this).val()).addClass('selected');
        updateCalculations();
    });

    field('#DealtransferTransferType', 'transfer_type').on('change', function(){
        toggleRows();
        computeFee(sourceDealTotal, !userChangedFee);
    });
    field('#TransferSourceDeal', 'source_deal_id').on('change', function(){
        userChangedFee = false;
        userChangedFeeType = false;
        loadSourceMeta();
    });
    field('#TransferTargetDeal', 'target_deal_id').on('change', function(){
        loadTargetMeta();
    });
    field('#TransferTargetClient', 'target_client_id').on('change', function(){
        updateCalculations();
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
        updateCalculations();
    });

    toggleRows();
    loadSourceMeta();
    if(field('#TransferTargetDeal', 'target_deal_id').val()){
        loadTargetMeta();
    }
});
</script>
