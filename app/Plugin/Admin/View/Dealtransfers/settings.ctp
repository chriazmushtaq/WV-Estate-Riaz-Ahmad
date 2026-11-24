<?php
$projectId = isset($projectId) ? (int)$projectId : 0;
$agencyOptions = isset($agencyOptions) ? $agencyOptions : array();
$feeLedgerOptions = isset($feeLedgerOptions) ? $feeLedgerOptions : array();
$feeTypes = isset($feeTypes) ? $feeTypes : array();
$currentAgencyName = isset($agencyOptions[$projectId]) ? $agencyOptions[$projectId] : ('Agency #' . $projectId);
?>
<style type="text/css">
.dt-settings-shell {
    margin-bottom: 25px;
}
.dt-agency-selector-panel {
    background: #ffffff;
    border: 1px solid #dbe6f3;
    border-radius: 10px;
    padding: 16px 20px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
    box-shadow: 0 4px 14px rgba(15,35,58,.04);
}
.dt-agency-selector-left {
    display: flex;
    align-items: center;
    gap: 14px;
}
.dt-agency-icon {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    background: #eff6ff;
    color: #0b63ce;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    border: 1px solid #bfdbfe;
    flex-shrink: 0;
}
.dt-agency-title-wrap .dt-agency-tag {
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    color: #0b63ce;
    letter-spacing: 0.5px;
}
.dt-agency-title-wrap h3 {
    margin: 2px 0 0;
    font-size: 17px;
    font-weight: 900;
    color: #08213f;
}
.dt-agency-selector-right {
    display: flex;
    align-items: center;
    gap: 12px;
}
.dt-agency-selector-right label {
    margin: 0;
    font-size: 12px;
    font-weight: 700;
    color: #475569;
    text-transform: uppercase;
    white-space: nowrap;
}
.dt-agency-dropdown {
    min-width: 260px;
    height: 40px;
    font-size: 13px;
    font-weight: 700;
    color: #08213f;
    border-radius: 8px;
    border: 2px solid #0b63ce;
    background-color: #f8fbff;
    padding: 6px 12px;
    cursor: pointer;
}
.dt-agency-dropdown:focus {
    border-color: #0b63ce;
    box-shadow: 0 0 0 4px rgba(11,99,206,.15);
}
.dt-agency-loading-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    color: #0b63ce;
    font-size: 12px;
    font-weight: 700;
    padding: 5px 12px;
    border-radius: 6px;
}

.dt-security-callout {
    background: #eef7ff;
    border-left: 4px solid #0b63ce;
    border-radius: 6px;
    padding: 12px 16px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
}
.dt-security-callout .sec-text {
    font-size: 13px;
    color: #1e3a5f;
}
.dt-security-callout .sec-text strong {
    color: #0b63ce;
    font-weight: 700;
}

/* Force Form to override global auto-grid styles */
body.wv-admin-theme form.wv-no-auto-layout,
form.wv-no-auto-layout {
    display: block !important;
    width: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
}

/* 2x2 Grid for Settings Cards */
.dt-cards-grid {
    display: grid !important;
    grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
    gap: 20px !important;
    margin-bottom: 20px !important;
    width: 100% !important;
}
@media (max-width: 1100px) {
    .dt-cards-grid {
        grid-template-columns: 1fr !important;
    }
}

.dt-card {
    background: #ffffff;
    border: 1px solid #dbe6f3;
    border-radius: 10px;
    box-shadow: 0 4px 14px rgba(15,35,58,.04);
    overflow: hidden;
    display: flex;
    flex-direction: column;
}
.dt-card-header {
    background: #f8fbff;
    border-bottom: 1px solid #e5edf7;
    padding: 14px 18px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.dt-card-header h3 {
    margin: 0;
    font-size: 15px;
    font-weight: 900;
    color: #08213f;
    display: flex;
    align-items: center;
    gap: 8px;
}
.dt-card-header h3 .fa {
    color: #0b63ce;
}
.dt-badge {
    font-size: 11px;
    font-weight: 800;
    padding: 3px 8px;
    border-radius: 4px;
    text-transform: uppercase;
}
.dt-badge-core { background: #e0f2fe; color: #0369a1; }
.dt-badge-safe { background: #dcfce7; color: #15803d; }
.dt-badge-finance { background: #fef3c7; color: #b45309; }

.dt-card-body {
    padding: 18px 20px;
    flex: 1;
}

.dt-toggles-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.dt-toggle-tile {
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 12px 14px;
    background: #fafcff;
    display: flex;
    align-items: flex-start;
    gap: 12px;
    cursor: pointer;
    transition: all 0.2s ease;
    margin: 0;
}
.dt-toggle-tile:hover {
    border-color: #94a3b8;
    background: #f1f5f9;
}
.dt-toggle-tile.safe-guard {
    border-color: #bbf7d0;
    background: #f0fdf4;
}
.dt-toggle-tile input[type="checkbox"] {
    margin: 3px 0 0;
    cursor: pointer;
    width: 17px;
    height: 17px;
    flex-shrink: 0;
}
.dt-toggle-content {
    flex: 1;
}
.dt-toggle-title {
    font-size: 13px;
    font-weight: 800;
    color: #0f172a;
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}
.dt-toggle-desc {
    font-size: 12px;
    color: #64748b;
    margin-top: 3px;
    line-height: 1.35;
}
.dt-recommended-pill {
    display: inline-block;
    font-size: 10px;
    font-weight: 800;
    color: #15803d;
    background: #dcfce7;
    border: 1px solid #86efac;
    padding: 1px 6px;
    border-radius: 3px;
    text-transform: uppercase;
}
.dt-caution-pill {
    display: inline-block;
    font-size: 10px;
    font-weight: 800;
    color: #b45309;
    background: #fef3c7;
    border: 1px solid #fde68a;
    padding: 1px 6px;
    border-radius: 3px;
    text-transform: uppercase;
}

.dt-form-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
    margin-bottom: 14px;
}
.dt-form-group:last-child {
    margin-bottom: 0;
}
.dt-form-group label {
    font-size: 12px;
    font-weight: 700;
    color: #334155;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    margin: 0;
}
.dt-form-group .form-control {
    border-radius: 6px;
    border: 1px solid #cbd5e1;
    height: 38px;
    padding: 6px 12px;
    font-size: 13px;
    color: #0f172a;
    font-weight: 600;
}
.dt-form-group .form-control:focus {
    border-color: #0b63ce;
    box-shadow: 0 0 0 3px rgba(11, 99, 206, 0.12);
}
.dt-field-hint {
    font-size: 11px;
    color: #64748b;
    line-height: 1.35;
}

.dt-fee-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
    margin-bottom: 14px;
}
@media (max-width: 600px) {
    .dt-fee-grid {
        grid-template-columns: 1fr;
    }
}

.dt-action-footer {
    background: #ffffff;
    border: 1px solid #dbe6f3;
    border-radius: 10px;
    padding: 16px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 14px;
    box-shadow: 0 4px 14px rgba(15,35,58,.04);
    width: 100% !important;
}
</style>

<div class="page-title">
    <div class="title-env">
        <h1 class="title"><i class="fa fa-sliders"></i> <?php echo __('Deal Transfer & Merge Settings');?></h1>
        <p class="description" style="color:rgba(255,255,255,0.85);margin-top:4px;"><?php echo __('Configure governance policies, automatic vouchering, fee schedules, and safety rules for deal transfers and mergings.');?></p>
    </div>
    <div class="heading-elements" style="margin:0;">
        <?php echo $this->Html->link('<i class="fa fa-arrow-left"></i> '.__('Back to Transfers'), array('controller'=>'Dealtransfers','action'=>'index'), array('escape'=>false,'class'=>'btn btn-default btn-sm','style'=>'font-weight:700;'));?>
    </div>
</div>

<div class="dt-settings-shell">
    <?php echo $this->Session->flash();?>

    <!-- Prominent Auto-Load Agency Filter Card -->
    <div class="dt-agency-selector-panel">
        <div class="dt-agency-selector-left">
            <div class="dt-agency-icon"><i class="fa fa-building-o"></i></div>
            <div class="dt-agency-title-wrap">
                <span class="dt-agency-tag"><i class="fa fa-map-marker"></i> <?php echo __('Active Agency Scope');?></span>
                <h3><?php echo h($currentAgencyName);?> <small style="color:#64748b;font-weight:600;">(#<?php echo (int)$projectId;?>)</small></h3>
            </div>
        </div>
        <div class="dt-agency-selector-right">
            <label for="AgencyFilterSelect"><i class="fa fa-filter"></i> <?php echo __('Select Agency:');?></label>
            <div>
                <?php echo $this->Form->select('agency_filter_id', $agencyOptions, array(
                    'id' => 'AgencyFilterSelect',
                    'class' => 'form-control dt-agency-dropdown',
                    'value' => $projectId,
                    'empty' => false
                )); ?>
            </div>
            <span class="dt-agency-loading-pill" id="AgencyFilterLoading" style="display:none;">
                <i class="fa fa-circle-o-notch fa-spin"></i> <?php echo __('Auto-fetching settings...');?>
            </span>
        </div>
    </div>

    <!-- Security Safeguards Advisory -->
    <div class="dt-security-callout">
        <div class="sec-text">
            <strong><i class="fa fa-shield"></i> <?php echo __('Recommended Safeguards:');?></strong>
            <?php echo __('Keep "Merge Same Client Only" and "Reason Required" enabled at all times to prevent legal disputes and ensure non-destructive double-entry audit trails.');?>
        </div>
    </div>

    <!-- Main Settings Form with class wv-no-auto-layout -->
    <?php echo $this->Form->create('Dealtransfersetting', array('class'=>'form-horizontal wv-no-auto-layout','id'=>'DealtransferSettingForm'));?>
    <?php echo $this->Form->hidden('project_id', array('value' => $projectId));?>

    <!-- 2x2 Clean Cards Grid -->
    <div class="dt-cards-grid">
        <!-- Card 1: Core Module Operations -->
        <div class="dt-card">
            <div class="dt-card-header">
                <h3><i class="fa fa-cubes"></i> <?php echo __('Core Operations & Permissions');?></h3>
                <span class="dt-badge dt-badge-core"><?php echo __('Module Lifecycle');?></span>
            </div>
            <div class="dt-card-body">
                <div class="dt-toggles-list">
                    <label class="dt-toggle-tile">
                        <?php echo $this->Form->checkbox('module_enabled');?>
                        <div class="dt-toggle-content">
                            <div class="dt-toggle-title"><?php echo __('Master Module Enabled');?></div>
                            <div class="dt-toggle-desc"><?php echo __('Enable or disable the deal transfer and merge functionality across this agency.');?></div>
                        </div>
                    </label>

                    <label class="dt-toggle-tile">
                        <?php echo $this->Form->checkbox('transfer_enabled');?>
                        <div class="dt-toggle-content">
                            <div class="dt-toggle-title"><?php echo __('Deal Transfer Enabled');?></div>
                            <div class="dt-toggle-desc"><?php echo __('Allows transferring active deal ownership and payment responsibility to a new client.');?></div>
                        </div>
                    </label>

                    <label class="dt-toggle-tile">
                        <?php echo $this->Form->checkbox('merge_enabled');?>
                        <div class="dt-toggle-content">
                            <div class="dt-toggle-title"><?php echo __('Deal Merge Enabled');?></div>
                            <div class="dt-toggle-desc"><?php echo __('Allows merging source deal payments into a target deal with summary credit (DMG-).');?></div>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        <!-- Card 2: Security & Risk Control -->
        <div class="dt-card">
            <div class="dt-card-header">
                <h3><i class="fa fa-lock"></i> <?php echo __('Security, Audit & Compliance');?></h3>
                <span class="dt-badge dt-badge-safe"><?php echo __('Risk Control');?></span>
            </div>
            <div class="dt-card-body">
                <div class="dt-toggles-list">
                    <label class="dt-toggle-tile safe-guard">
                        <?php echo $this->Form->checkbox('same_client_merge_only');?>
                        <div class="dt-toggle-content">
                            <div class="dt-toggle-title">
                                <?php echo __('Merge Same Client Only');?>
                                <span class="dt-recommended-pill"><?php echo __('Safest / Recommended');?></span>
                            </div>
                            <div class="dt-toggle-desc"><?php echo __('Restricts deal merging strictly to the same customer. Prevents combining deals belonging to different clients.');?></div>
                        </div>
                    </label>

                    <label class="dt-toggle-tile safe-guard">
                        <?php echo $this->Form->checkbox('require_reason');?>
                        <div class="dt-toggle-content">
                            <div class="dt-toggle-title">
                                <?php echo __('Written Reason Required');?>
                                <span class="dt-recommended-pill"><?php echo __('Audit Trail');?></span>
                            </div>
                            <div class="dt-toggle-desc"><?php echo __('Forces operators to provide a clear written justification before saving or finalizing a transfer/merge.');?></div>
                        </div>
                    </label>

                    <label class="dt-toggle-tile">
                        <?php echo $this->Form->checkbox('agency_transfer_enabled');?>
                        <div class="dt-toggle-content">
                            <div class="dt-toggle-title">
                                <?php echo __('Allow Cross-Agency Transfer');?>
                                <span class="dt-caution-pill"><?php echo __('Restricted');?></span>
                            </div>
                            <div class="dt-toggle-desc"><?php echo __('Allows transferring deals between different agencies/projects. Keep disabled if agencies operate independently.');?></div>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        <!-- Card 3: Financial Accounting & Fee Vouchers (ARD) -->
        <div class="dt-card">
            <div class="dt-card-header">
                <h3><i class="fa fa-book"></i> <?php echo __('Financial Accounting & Fee Vouchers (ARD)');?></h3>
                <span class="dt-badge dt-badge-finance"><?php echo __('Double Entry Integration');?></span>
            </div>
            <div class="dt-card-body">
                <div class="dt-toggles-list" style="margin-bottom:16px;">
                    <label class="dt-toggle-tile">
                        <?php echo $this->Form->checkbox('create_fee_charge');?>
                        <div class="dt-toggle-content">
                            <div class="dt-toggle-title"><?php echo __('Create Deal Processing Charge');?></div>
                            <div class="dt-toggle-desc"><?php echo __('Adds a formal fee charge on the deal charges schedule upon finalization.');?></div>
                        </div>
                    </label>

                    <label class="dt-toggle-tile safe-guard">
                        <?php echo $this->Form->checkbox('create_fee_voucher');?>
                        <div class="dt-toggle-content">
                            <div class="dt-toggle-title">
                                <?php echo __('Create ARD Fee Voucher (DTV-)');?>
                                <span class="dt-recommended-pill"><?php echo __('Safe Accounting');?></span>
                            </div>
                            <div class="dt-toggle-desc"><?php echo __('Generates an official payment voucher in the double-entry accounting ledger to record fee receipt.');?></div>
                        </div>
                    </label>
                </div>

                <div class="dt-form-group">
                    <label><i class="fa fa-university"></i> <?php echo __('Fee Voucher Receiving Ledger / Account');?></label>
                    <?php echo $this->Form->select('fee_voucher_ledger_id', $feeLedgerOptions, array('class'=>'form-control','style'=>'font-weight:600;'));?>
                    <span class="dt-field-hint"><?php echo __('Specify which cash or bank ledger receives the processing fee income for this agency.');?></span>
                </div>
            </div>
        </div>

        <!-- Card 4: Default Fee Pricing Structure -->
        <div class="dt-card">
            <div class="dt-card-header">
                <h3><i class="fa fa-money"></i> <?php echo __('Default Fee Pricing Structure');?></h3>
                <span class="dt-badge dt-badge-core"><?php echo __('Billing Rates');?></span>
            </div>
            <div class="dt-card-body">
                <div class="dt-fee-grid">
                    <div class="dt-form-group">
                        <label><?php echo __('Default Fee Type');?></label>
                        <?php echo $this->Form->select('default_fee_type', $feeTypes, array('class'=>'form-control'));?>
                        <span class="dt-field-hint"><?php echo __('Fixed, percent, or waived.');?></span>
                    </div>

                    <div class="dt-form-group">
                        <label><?php echo __('Default Fee Percentage (%)');?></label>
                        <?php echo $this->Form->text('default_fee_percent', array('class'=>'form-control','placeholder'=>'0.00'));?>
                        <span class="dt-field-hint"><?php echo __('Used if set to Percentage.');?></span>
                    </div>

                    <div class="dt-form-group">
                        <label><?php echo __('Default Transfer Fee Amount');?></label>
                        <?php echo $this->Form->text('default_transfer_fee', array('class'=>'form-control','placeholder'=>'0.00'));?>
                        <span class="dt-field-hint"><?php echo __('Fixed fee for ownership transfer.');?></span>
                    </div>

                    <div class="dt-form-group">
                        <label><?php echo __('Default Merge Fee Amount');?></label>
                        <?php echo $this->Form->text('default_merge_fee', array('class'=>'form-control','placeholder'=>'0.00'));?>
                        <span class="dt-field-hint"><?php echo __('Fixed fee for consolidating deals.');?></span>
                    </div>
                </div>

                <div class="dt-form-group">
                    <label><?php echo __('Agency Policy & Agreement Notes');?></label>
                    <?php echo $this->Form->textarea('notes', array('class'=>'form-control','rows'=>3,'placeholder'=>__('Enter terms, approval policies, or instructions displayed on the transfer review form.')));?>
                    <span class="dt-field-hint"><?php echo __('Internal guidance notes for agents operating in this agency.');?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Clean Action Footer -->
    <div class="dt-action-footer">
        <div style="font-size:13px;color:#64748b;font-weight:600;">
            <i class="fa fa-info-circle" style="color:#0b63ce;"></i>
            <?php echo __('Saving settings applies to agency:');?> <strong style="color:#08213f;"><?php echo h($currentAgencyName);?></strong>.
        </div>
        <div style="display:flex;gap:10px;">
            <?php echo $this->Html->link(__('Cancel'), array('controller'=>'Dealtransfers','action'=>'index'), array('class'=>'btn btn-default','style'=>'font-weight:700;padding:8px 18px;'));?>
            <button type="submit" class="btn btn-success" style="font-weight:800;padding:8px 24px;">
                <span class="fa fa-check-circle"></span> <?php echo __('Save Deal Settings');?>
            </button>
        </div>
    </div>

    <?php echo $this->Form->end();?>
</div>

<script type="text/javascript">
jQuery(function($){
    // Auto-fetch agency data when user selects a different agency from the filter dropdown
    $('#AgencyFilterSelect').on('change', function(){
        var selectedId = $(this).val();
        if(selectedId){
            $('#AgencyFilterLoading').fadeIn(150);
            var targetUrl = <?php echo json_encode($this->Html->url(array('action'=>'settings'))); ?>;
            window.location.href = targetUrl + '?project_id=' + selectedId;
        }
    });
});
</script>
