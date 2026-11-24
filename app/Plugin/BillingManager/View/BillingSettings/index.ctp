<style>
/* --- DEDICATED FULL-WIDTH TAB PAGE STYLING --- */
.subm-fullwidth-wrapper,
.subm-fullwidth-wrapper form,
.subm-fullwidth-wrapper form.form-horizontal,
.subm-fullwidth-wrapper form.wv-no-auto-layout,
.subm-fullwidth-wrapper .subm-card-section,
.subm-fullwidth-wrapper .subm-bottom-action-bar {
    display: block !important;
    width: 100% !important;
    max-width: 100% !important;
    min-width: 100% !important;
    float: none !important;
    clear: both !important;
    box-sizing: border-box !important;
    grid-template-columns: none !important;
}

.subm-feature-icon-box {
    width: 38px;
    height: 38px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    margin-right: 14px;
    flex-shrink: 0;
}
.subm-feature-left-wrap {
    display: flex;
    align-items: center;
    flex: 1;
    padding-right: 20px;
}

.subm-society-bar {
    background: #f0fdf4 !important;
    border: 1px solid #86efac !important;
    border-radius: 8px !important;
    padding: 14px 20px !important;
    margin-bottom: 22px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    flex-wrap: wrap !important;
    gap: 12px !important;
    width: 100% !important;
}

.subm-tab-nav {
    display: flex !important;
    flex-wrap: wrap !important;
    list-style: none !important;
    padding: 0 !important;
    margin: 0 0 24px 0 !important;
    border-bottom: 2px solid #e2e8f0 !important;
    width: 100% !important;
    gap: 4px !important;
}

.subm-tab-nav li {
    margin: 0 !important;
}

.subm-tab-nav li a {
    display: block !important;
    padding: 11px 18px !important;
    font-weight: 700 !important;
    font-size: 13.5px !important;
    color: #475569 !important;
    text-decoration: none !important;
    border-radius: 8px 8px 0 0 !important;
    background: #f8fafc !important;
    border: 1px solid #cbd5e1 !important;
    border-bottom: none !important;
    transition: all 0.15s ease-in-out !important;
}

.subm-tab-nav li.active a,
.subm-tab-nav li a:hover {
    background: #0284c7 !important;
    color: #ffffff !important;
    border-color: #0284c7 !important;
}

.subm-card-section {
    background: #ffffff !important;
    border: 1px solid #cbd5e1 !important;
    border-radius: 10px !important;
    margin-bottom: 24px !important;
    box-shadow: 0 2px 6px rgba(0,0,0,0.04) !important;
    overflow: hidden !important;
    width: 100% !important;
}

.subm-card-title {
    background: #f8fafc !important;
    color: #0f172a !important;
    font-weight: 800 !important;
    font-size: 15px !important;
    padding: 14px 20px !important;
    border-bottom: 1px solid #e2e8f0 !important;
    display: flex !important;
    align-items: center !important;
    gap: 10px !important;
}

.subm-card-body {
    padding: 22px !important;
    width: 100% !important;
}

.subm-table-custom {
    width: 100% !important;
    border-collapse: collapse !important;
}

.subm-table-custom th {
    background: #0f172a !important;
    color: #ffffff !important;
    padding: 10px 14px !important;
    font-size: 12px !important;
    font-weight: 800 !important;
    text-transform: uppercase !important;
    border: 1px solid #334155 !important;
}

.subm-table-custom td {
    padding: 10px 14px !important;
    border: 1px solid #e2e8f0 !important;
    vertical-align: middle !important;
}

.dynamic-row-card {
    background: #f8fafc;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    padding: 14px 18px;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.subm-bottom-action-bar {
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-radius: 10px;
    padding: 18px 24px;
    margin-top: 10px;
    margin-bottom: 30px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

/* Modern iOS Style Toggle Switches */
.subm-switch {
    position: relative;
    display: inline-block;
    width: 52px;
    height: 28px;
    margin: 0;
    vertical-align: middle;
}
.subm-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}
.subm-slider {
    position: absolute;
    cursor: pointer;
    top: 0; left: 0; right: 0; bottom: 0;
    background-color: #cbd5e1;
    transition: .3s cubic-bezier(0.4, 0, 0.2, 1);
    border-radius: 28px;
}
.subm-slider:before {
    position: absolute;
    content: "";
    height: 22px;
    width: 22px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: .3s cubic-bezier(0.4, 0, 0.2, 1);
    border-radius: 50%;
    box-shadow: 0 2px 4px rgba(0,0,0,0.22);
}
.subm-switch input:checked + .subm-slider {
    background-color: #10b981;
}
.subm-switch input:checked + .subm-slider:before {
    transform: translateX(24px);
}
.subm-feature-group {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    margin-bottom: 22px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,0.03);
}
.subm-feature-group-header {
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    padding: 12px 20px;
    font-size: 14px;
    font-weight: 800;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 8px;
}
.subm-feature-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    border-bottom: 1px solid #f1f5f9;
    transition: background 0.15s ease;
}
.subm-feature-row:hover {
    background: #f8fafc;
}
.subm-feature-row:last-child {
    border-bottom: none;
}
.subm-feature-info {
    flex: 1;
    padding-right: 24px;
}
.subm-feature-title {
    font-weight: 700;
    font-size: 14.5px;
    color: #0f172a;
    margin-bottom: 4px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.subm-feature-desc {
    font-size: 12.5px;
    color: #64748b;
    margin: 0;
    line-height: 1.45;
}
.subm-badge-pill {
    font-size: 10.5px;
    font-weight: 800;
    padding: 2px 8px;
    border-radius: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Upgrades & Automation Styling */
.subm-upgrade-section-head {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-left: 5px solid #10b981;
    border-radius: 10px;
    padding: 16px 20px;
    margin: 28px 0 16px 0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0 1px 3px rgba(0,0,0,0.03);
}
.subm-upgrade-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    margin-bottom: 20px;
    overflow: hidden;
    box-shadow: 0 2px 6px rgba(15,23,42,0.03);
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}
.subm-upgrade-card:hover {
    border-color: #cbd5e1;
    box-shadow: 0 4px 12px rgba(15,23,42,0.06);
}
.subm-upgrade-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 18px 22px;
    background: #ffffff;
}
.subm-upgrade-body {
    background: #f8fafc;
    border-top: 1px solid #edf2f7;
    padding: 18px 20px 8px 20px;
}
.subm-field-box {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 12px 14px;
    margin-bottom: 12px;
    transition: all 0.2s ease;
    min-height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    box-shadow: 0 1px 2px rgba(0,0,0,0.02);
}
.subm-field-box:hover {
    border-color: #cbd5e1;
    box-shadow: 0 2px 5px rgba(0,0,0,0.04);
}
.subm-field-box:focus-within {
    border-color: #10b981;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.12);
}
.subm-field-label {
    font-size: 11px;
    font-weight: 700;
    color: #334155;
    margin-bottom: 6px;
    display: flex;
    align-items: center;
    gap: 6px;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}
.subm-field-label i {
    font-size: 12px;
}
.subm-control {
    height: 36px !important;
    font-size: 12.5px !important;
    font-weight: 600 !important;
    color: #0f172a !important;
    border: 1px solid #cbd5e1 !important;
    border-radius: 6px !important;
    padding: 6px 10px !important;
    box-shadow: inset 0 1px 2px rgba(0,0,0,0.02) !important;
    transition: all 0.15s ease !important;
    background-color: #ffffff !important;
    width: 100%;
}
.subm-control:focus {
    border-color: #10b981 !important;
    box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2) !important;
    outline: none !important;
}
.input-group .subm-control {
    border-radius: 6px 0 0 6px !important;
}
.subm-addon {
    background: #f1f5f9 !important;
    border: 1px solid #cbd5e1 !important;
    border-left: none !important;
    font-size: 11px !important;
    font-weight: 700 !important;
    color: #475569 !important;
    border-radius: 0 6px 6px 0 !important;
    padding: 0 12px !important;
    display: flex;
    align-items: center;
}
textarea.subm-control {
    height: auto !important;
    line-height: 1.4 !important;
}
.subm-field-help {
    font-size: 11px;
    color: #64748b;
    margin-top: 5px;
    margin-bottom: 0;
    line-height: 1.35;
    display: flex;
    align-items: center;
    gap: 4px;
}
.subm-cfg-label {
    font-size: 12px;
    font-weight: 700;
    color: #334155;
    margin-bottom: 5px;
    display: block;
}
.subm-cfg-help {
    font-size: 11px;
    color: #64748b;
    margin-top: 4px;
    margin-bottom: 0;
}
</style>

<div class="subm-fullwidth-wrapper">

    <!-- Society Filter Header -->
    <div class="subm-society-bar">
        <div>
            <h4 style="font-weight:800;color:#15803d;margin:0 0 2px 0;font-size:16px;">
                <i class="fa fa-building"></i> <?php echo __('Society Isolated Settings'); ?>
            </h4>
            <span style="font-size:12.5px;color:#166534;font-weight:600;">
                <?php echo __('Currently configuring isolated parameters for society ID'); ?> #<?php echo (int)$societyId; ?>:
                <strong><?php echo h($activeSociety['name']); ?></strong>
            </span>
        </div>
        <div style="min-width:320px;">
            <select id="societySwitcher" class="form-control" style="font-weight:600;font-size:14px;color:#0369a1;border-color:#86efac;height:40px;">
                <?php foreach($societies as $soc){ 
                    $s = $soc['BillingSociety'];
                ?>
                    <option value="<?php echo $s['id']; ?>" <?php if($s['id'] == $societyId) echo 'selected'; ?>>
                        <?php echo h($s['name']); ?> (<?php echo h($s['code']); ?>)
                    </option>
                <?php } ?>
            </select>
        </div>
    </div>

    <!-- Dedicated Tab Navigation Header -->
    <ul class="subm-tab-nav">
        <li class="<?php if($activeTab == 'general') echo 'active'; ?>">
            <a href="<?php echo $this->Html->url(array('action' => 'index', '?' => array('society_id' => $societyId, 'tab' => 'general'))); ?>">
                <i class="fa fa-cog text-primary"></i> <?php echo __('General Settings'); ?>
            </a>
        </li>
        <li class="<?php if($activeTab == 'qr-bank') echo 'active'; ?>">
            <a href="<?php echo $this->Html->url(array('action' => 'index', '?' => array('society_id' => $societyId, 'tab' => 'qr-bank'))); ?>">
                <i class="fa fa-qrcode text-success"></i> <?php echo __('Bank & QR Scan-to-Pay'); ?>
            </a>
        </li>
        <li class="<?php if($activeTab == 'features') echo 'active'; ?>">
            <a href="<?php echo $this->Html->url(array('action' => 'index', '?' => array('society_id' => $societyId, 'tab' => 'features'))); ?>">
                <i class="fa fa-toggle-on text-info"></i> <?php echo __('Feature Toggles'); ?>
            </a>
        </li>
        <li class="<?php if($activeTab == 'upgrades') echo 'active'; ?>">
            <a href="<?php echo $this->Html->url(array('action' => 'index', '?' => array('society_id' => $societyId, 'tab' => 'upgrades'))); ?>" style="<?php if($activeTab == 'upgrades') echo 'border-bottom:3px solid #10b981;font-weight:700;background:rgba(16,185,129,0.08);'; ?>">
                <i class="fa fa-rocket text-success"></i> <?php echo __('Smart Upgrades & Automation'); ?>
                <span class="label label-success" style="font-size:9.5px;padding:2px 6px;margin-left:4px;border-radius:8px;background:#10b981;color:#fff;">NEW 2026</span>
            </a>
        </li>
        <li class="<?php if($activeTab == 'billing-rules') echo 'active'; ?>">
            <a href="<?php echo $this->Html->url(array('action' => 'index', '?' => array('society_id' => $societyId, 'tab' => 'billing-rules'))); ?>">
                <i class="fa fa-sliders text-warning"></i> <?php echo __('Billing Rules'); ?>
            </a>
        </li>
        <li>
            <a href="<?php echo $this->Html->url(array('controller' => 'billing_extra_charges', 'action' => 'index', '?' => array('society_id' => $societyId))); ?>">
                <i class="fa fa-list-alt text-primary"></i> <?php echo __('Society Maintenance & Charges'); ?>
            </a>
        </li>
        <li class="<?php if($activeTab == 'sms-whatsapp') echo 'active'; ?>">
            <a href="<?php echo $this->Html->url(array('action' => 'index', '?' => array('society_id' => $societyId, 'tab' => 'sms-whatsapp'))); ?>">
                <i class="fa fa-paper-plane text-danger"></i> <?php echo __('SMS / WhatsApp Gateway'); ?>
            </a>
        </li>
        <li>
            <a href="<?php echo $this->Html->url(array('controller' => 'billing_templates', 'action' => 'index')); ?>">
                <i class="fa fa-file-code-o text-success"></i> <?php echo __('Print & Email Templates'); ?>
            </a>
        </li>
        <li>
            <a href="<?php echo $this->Html->url(array('controller' => 'billing_migrations', 'action' => 'index')); ?>">
                <i class="fa fa-database text-warning"></i> <?php echo __('Restore & Backup Engine'); ?>
            </a>
        </li>
    </ul>

    <!-- DEDICATED FORM FOR ACTIVE TAB ONLY -->
    <?php echo $this->Form->create('BillingSetting', array('class' => 'form-horizontal wv-no-auto-layout', 'style' => 'width:100%;display:block;')); ?>
    <input type="hidden" name="society_id" value="<?php echo (int)$societyId; ?>" />
    <input type="hidden" name="active_tab" id="activeTabInput" value="<?php echo h($activeTab); ?>" />

    <?php switch($activeTab) { 
        case 'upgrades': ?>
            <!-- ======================================================== -->
            <!-- DEDICATED TAB: SMART UPGRADES & AUTOMATION HUB (2026)    -->
            <!-- ======================================================== -->
            <div style="background:linear-gradient(135deg, #064e3b 0%, #065f46 50%, #047857 100%);border-radius:12px;padding:24px 28px;color:#ffffff;margin-bottom:26px;box-shadow:0 4px 16px rgba(6,78,59,0.2);">
                <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:18px;">
                    <div>
                        <div style="display:flex;align-items:center;gap:12px;margin-bottom:8px;">
                            <span style="font-size:26px;background:rgba(255,255,255,0.15);padding:6px 12px;border-radius:10px;">🚀</span>
                            <div>
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <h3 style="font-weight:800;margin:0;font-size:21px;color:#ffffff;letter-spacing:-0.3px;">
                                        <?php echo __('Enterprise Smart Upgrades, Client Portal & Automation Hub'); ?>
                                    </h3>
                                    <span class="label" style="background:#10b981;font-size:10.5px;font-weight:800;padding:4px 8px;border-radius:6px;letter-spacing:0.5px;">PRO 2026</span>
                                </div>
                                <p style="margin:4px 0 0 0;font-size:13px;color:#a7f3d0;max-width:850px;line-height:1.5;">
                                    <?php echo __('Configure advanced billing engines, resident portal self-service parameters, and multi-stage automated notifications. Toggle features ON or OFF and calibrate backend grace periods, reminder days, and auto-disconnection thresholds for'); ?>
                                    <strong style="color:#ffffff;text-decoration:underline;"><?php echo h($activeSociety['name']); ?></strong>.
                                </p>
                            </div>
                        </div>
                        <div style="display:flex;align-items:center;gap:16px;margin-top:12px;flex-wrap:wrap;">
                            <span style="font-size:12px;color:#ecfdf5;background:rgba(255,255,255,0.12);padding:3px 10px;border-radius:20px;font-weight:600;"><i class="fa fa-cubes text-warning"></i> 16 Active Modules</span>
                            <span style="font-size:12px;color:#ecfdf5;background:rgba(255,255,255,0.12);padding:3px 10px;border-radius:20px;font-weight:600;"><i class="fa fa-whatsapp text-success"></i> LeadBot Cloud Gateway</span>
                            <span style="font-size:12px;color:#ecfdf5;background:rgba(255,255,255,0.12);padding:3px 10px;border-radius:20px;font-weight:600;"><i class="fa fa-check-circle text-info"></i> Zero-Code Calibration</span>
                        </div>
                    </div>
                    <div>
                        <button type="submit" class="btn btn-default" style="font-weight:800;color:#065f46;background:#ffffff;border:none;padding:12px 26px;border-radius:8px;box-shadow:0 3px 8px rgba(0,0,0,0.18);font-size:13.5px;">
                            <i class="fa fa-save text-success"></i> <?php echo __('Save All Upgrades'); ?>
                        </button>
                    </div>
                </div>
            </div>

            <!-- =================================================================== -->
            <!-- SECTION 1: ⚡ ADVANCED BILLING & FINANCIAL MANAGEMENT UPGRADES      -->
            <!-- =================================================================== -->
            <div class="subm-upgrade-section-head">
                <div>
                    <h4 style="font-weight:800;color:#0f172a;margin:0 0 3px 0;font-size:16px;">
                        <i class="fa fa-bolt text-warning"></i> <?php echo __('Section 1: Billing & Financial Automation Engines'); ?>
                    </h4>
                    <p style="margin:0;font-size:12.5px;color:#64748b;">
                        <?php echo __('Dynamic QR codes, photo proof audits, multi-slab tariffs, auto-surcharges, and defaulter enforcement.'); ?>
                    </p>
                </div>
                <span class="label label-warning subm-badge-pill" style="background:#fef3c7;color:#92400e;border:1px solid #fcd34d;font-size:11px;padding:5px 12px;"><?php echo __('Billing Core'); ?></span>
            </div>

            <!-- 1.1 Dynamic QR Scan-to-Pay -->
            <div class="subm-upgrade-card">
                <div class="subm-upgrade-header">
                    <div class="subm-feature-left-wrap">
                        <div class="subm-feature-icon-box" style="background:#ecfdf5;color:#059669;">
                            <i class="fa fa-qrcode"></i>
                        </div>
                        <div class="subm-feature-info">
                            <div class="subm-feature-title">
                                <span><?php echo __('Dynamic Payment QR Code (Raast / 1Bill / Banking)'); ?></span>
                                <span class="label label-success subm-badge-pill"><?php echo __('Zero Payment Typos'); ?></span>
                            </div>
                            <p class="subm-feature-desc">
                                <?php echo __('Generates an EMVCo compliant dynamic QR code on PDF bills and receipts. When residents scan with Meezan, Nayapay, SadaPay or HBL apps, their exact bill amount and reference auto-fill.'); ?>
                            </p>
                        </div>
                    </div>
                    <div>
                        <input type="hidden" name="data[BillingSetting][subm_upgrade_dynamic_qr_enabled]" value="0" />
                        <label class="subm-switch">
                            <input type="checkbox" name="data[BillingSetting][subm_upgrade_dynamic_qr_enabled]" value="1" <?php if(!isset($settings['subm_upgrade_dynamic_qr_enabled']) || $settings['subm_upgrade_dynamic_qr_enabled'] == '1') echo 'checked'; ?> />
                            <span class="subm-slider"></span>
                        </label>
                    </div>
                </div>
                <div class="subm-upgrade-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="subm-field-box">
                                <label class="subm-field-label">
                                    <i class="fa fa-university text-primary"></i> <?php echo __('Payment Provider'); ?>
                                </label>
                                <select name="data[BillingSetting][subm_upgrade_qr_provider]" class="form-control subm-control">
                                    <?php $selQrp = isset($settings['subm_upgrade_qr_provider']) ? $settings['subm_upgrade_qr_provider'] : 'raast'; ?>
                                    <option value="raast" <?php if($selQrp == 'raast') echo 'selected'; ?>>SBP Raast P2M</option>
                                    <option value="1bill" <?php if($selQrp == '1bill') echo 'selected'; ?>>1Link 1Bill TopUp</option>
                                    <option value="easypaisa" <?php if($selQrp == 'easypaisa') echo 'selected'; ?>>EasyPaisa Merchant Till</option>
                                    <option value="jazzcash" <?php if($selQrp == 'jazzcash') echo 'selected'; ?>>JazzCash Merchant Till</option>
                                    <option value="meezan_iban" <?php if($selQrp == 'meezan_iban') echo 'selected'; ?>>Meezan Bank IBAN Transfer</option>
                                </select>
                                <p class="subm-field-help">
                                    <i class="fa fa-info-circle text-muted"></i> <?php echo __('Underlying payment rail.'); ?>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="subm-field-box">
                                <label class="subm-field-label">
                                    <i class="fa fa-hashtag text-success"></i> <?php echo __('Account / IBAN / Till ID'); ?>
                                </label>
                                <input type="text" name="data[BillingSetting][subm_upgrade_qr_account_number]" value="<?php echo isset($settings['subm_upgrade_qr_account_number']) ? h($settings['subm_upgrade_qr_account_number']) : '0773310485346'; ?>" class="form-control subm-control" style="font-family:monospace;font-weight:bold;color:#0369a1;" />
                                <p class="subm-field-help">
                                    <i class="fa fa-info-circle text-muted"></i> <?php echo __('Receiver account number or IBAN.'); ?>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="subm-field-box">
                                <label class="subm-field-label">
                                    <i class="fa fa-id-card-o text-info"></i> <?php echo __('Account Title'); ?>
                                </label>
                                <input type="text" name="data[BillingSetting][subm_upgrade_qr_account_title]" value="<?php echo isset($settings['subm_upgrade_qr_account_title']) ? h($settings['subm_upgrade_qr_account_title']) : 'WHITE VILLAS LAHORE'; ?>" class="form-control subm-control" style="font-weight:600;" />
                                <p class="subm-field-help">
                                    <i class="fa fa-info-circle text-muted"></i> <?php echo __('Title displayed to payer.'); ?>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="subm-field-box">
                                <label class="subm-field-label">
                                    <i class="fa fa-hourglass-end text-warning"></i> <?php echo __('QR Expiry Period'); ?>
                                </label>
                                <div class="input-group">
                                    <input type="number" name="data[BillingSetting][subm_upgrade_qr_expiry_days]" value="<?php echo isset($settings['subm_upgrade_qr_expiry_days']) ? h($settings['subm_upgrade_qr_expiry_days']) : '30'; ?>" class="form-control subm-control" />
                                    <span class="input-group-addon subm-addon"><?php echo __('Days'); ?></span>
                                </div>
                                <p class="subm-field-help">
                                    <i class="fa fa-info-circle text-muted"></i> <?php echo __('Validity for QR payment link.'); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 1.2 Meter Reading Photo Proof -->
            <div class="subm-upgrade-card">
                <div class="subm-upgrade-header">
                    <div class="subm-feature-left-wrap">
                        <div class="subm-feature-icon-box" style="background:#eff6ff;color:#2563eb;">
                            <i class="fa fa-camera"></i>
                        </div>
                        <div class="subm-feature-info">
                            <div class="subm-feature-title">
                                <span><?php echo __('Meter Reading Photo Proof & Dial Snapshot'); ?></span>
                                <span class="label label-primary subm-badge-pill"><?php echo __('Audit Trail'); ?></span>
                            </div>
                            <p class="subm-feature-desc">
                                <?php echo __('Requires or encourages linemen to photograph physical meter dials during reading rounds, completely preventing disputes over erroneous readings.'); ?>
                            </p>
                        </div>
                    </div>
                    <div>
                        <input type="hidden" name="data[BillingSetting][subm_upgrade_meter_photo_enabled]" value="0" />
                        <label class="subm-switch">
                            <input type="checkbox" name="data[BillingSetting][subm_upgrade_meter_photo_enabled]" value="1" <?php if(!isset($settings['subm_upgrade_meter_photo_enabled']) || $settings['subm_upgrade_meter_photo_enabled'] == '1') echo 'checked'; ?> />
                            <span class="subm-slider"></span>
                        </label>
                    </div>
                </div>
                <div class="subm-upgrade-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="subm-field-box">
                                <label class="subm-field-label">
                                    <i class="fa fa-shield text-primary"></i> <?php echo __('Photo Enforcement Mode'); ?>
                                </label>
                                <select name="data[BillingSetting][subm_upgrade_meter_photo_required]" class="form-control subm-control">
                                    <?php $mpr = isset($settings['subm_upgrade_meter_photo_required']) ? $settings['subm_upgrade_meter_photo_required'] : 'optional'; ?>
                                    <option value="optional" <?php if($mpr == 'optional') echo 'selected'; ?>><?php echo __('Optional (Recommended)'); ?></option>
                                    <option value="mandatory" <?php if($mpr == 'mandatory') echo 'selected'; ?>><?php echo __('Strict Mandatory (Photo Required)'); ?></option>
                                </select>
                                <p class="subm-field-help">
                                    <i class="fa fa-info-circle text-muted"></i> <?php echo __('Lineman mobile app requirement.'); ?>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="subm-field-box">
                                <label class="subm-field-label">
                                    <i class="fa fa-file-image-o text-info"></i> <?php echo __('Show Thumbnail on Bill'); ?>
                                </label>
                                <select name="data[BillingSetting][subm_upgrade_meter_photo_on_bill]" class="form-control subm-control">
                                    <?php $mpb = isset($settings['subm_upgrade_meter_photo_on_bill']) ? $settings['subm_upgrade_meter_photo_on_bill'] : '1'; ?>
                                    <option value="1" <?php if($mpb == '1') echo 'selected'; ?>><?php echo __('Yes - Print Dial Photo on Bill Voucher'); ?></option>
                                    <option value="0" <?php if($mpb == '0') echo 'selected'; ?>><?php echo __('No - Keep Photo Internal Only'); ?></option>
                                </select>
                                <p class="subm-field-help">
                                    <i class="fa fa-info-circle text-muted"></i> <?php echo __('Proof of reading on PDF voucher.'); ?>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="subm-field-box">
                                <label class="subm-field-label">
                                    <i class="fa fa-database text-warning"></i> <?php echo __('Photo Storage Retention'); ?>
                                </label>
                                <div class="input-group">
                                    <input type="number" name="data[BillingSetting][subm_upgrade_meter_photo_retention_days]" value="<?php echo isset($settings['subm_upgrade_meter_photo_retention_days']) ? h($settings['subm_upgrade_meter_photo_retention_days']) : '90'; ?>" class="form-control subm-control" />
                                    <span class="input-group-addon subm-addon"><?php echo __('Days'); ?></span>
                                </div>
                                <p class="subm-field-help">
                                    <i class="fa fa-info-circle text-muted"></i> <?php echo __('Days to retain meter images on server.'); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 1.3 Multi-Slab & Seasonal Tariff Engine -->
            <div class="subm-upgrade-card">
                <div class="subm-upgrade-header">
                    <div class="subm-feature-left-wrap">
                        <div class="subm-feature-icon-box" style="background:#f0fdf4;color:#16a34a;">
                            <i class="fa fa-line-chart"></i>
                        </div>
                        <div class="subm-feature-info">
                            <div class="subm-feature-title">
                                <span><?php echo __('Multi-Slab & Seasonal Tariff Engine'); ?></span>
                                <span class="label label-info subm-badge-pill"><?php echo __('Tariff Intelligence'); ?></span>
                            </div>
                            <p class="subm-feature-desc">
                                <?php echo __('Supports automated summer vs winter pricing switches and peak-hour surcharge calculations.'); ?>
                            </p>
                        </div>
                    </div>
                    <div>
                        <input type="hidden" name="data[BillingSetting][subm_upgrade_slab_tariffs_enabled]" value="0" />
                        <label class="subm-switch">
                            <input type="checkbox" name="data[BillingSetting][subm_upgrade_slab_tariffs_enabled]" value="1" <?php if(!isset($settings['subm_upgrade_slab_tariffs_enabled']) || $settings['subm_upgrade_slab_tariffs_enabled'] == '1') echo 'checked'; ?> />
                            <span class="subm-slider"></span>
                        </label>
                    </div>
                </div>
                <div class="subm-upgrade-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="subm-field-box">
                                <label class="subm-field-label">
                                    <i class="fa fa-sun-o text-warning"></i> <?php echo __('Summer Start Month'); ?>
                                </label>
                                <select name="data[BillingSetting][subm_upgrade_summer_start_month]" class="form-control subm-control">
                                    <?php $sumM = isset($settings['subm_upgrade_summer_start_month']) ? $settings['subm_upgrade_summer_start_month'] : '04'; 
                                    $months = array('01'=>'January','02'=>'February','03'=>'March','04'=>'April','05'=>'May','06'=>'June','07'=>'July','08'=>'August','09'=>'September','10'=>'October','11'=>'November','12'=>'December');
                                    foreach($months as $k => $m){ ?>
                                        <option value="<?php echo $k; ?>" <?php if($sumM == $k) echo 'selected'; ?>><?php echo $m; ?></option>
                                    <?php } ?>
                                </select>
                                <p class="subm-field-help">
                                    <i class="fa fa-info-circle text-muted"></i> <?php echo __('When summer tariffs take effect.'); ?>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="subm-field-box">
                                <label class="subm-field-label">
                                    <i class="fa fa-snowflake-o text-info"></i> <?php echo __('Winter Start Month'); ?>
                                </label>
                                <select name="data[BillingSetting][subm_upgrade_winter_start_month]" class="form-control subm-control">
                                    <?php $winM = isset($settings['subm_upgrade_winter_start_month']) ? $settings['subm_upgrade_winter_start_month'] : '11'; 
                                    foreach($months as $k => $m){ ?>
                                        <option value="<?php echo $k; ?>" <?php if($winM == $k) echo 'selected'; ?>><?php echo $m; ?></option>
                                    <?php } ?>
                                </select>
                                <p class="subm-field-help">
                                    <i class="fa fa-info-circle text-muted"></i> <?php echo __('When winter tariffs take effect.'); ?>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="subm-field-box">
                                <label class="subm-field-label">
                                    <i class="fa fa-clock-o text-danger"></i> <?php echo __('Peak Hours Window'); ?>
                                </label>
                                <input type="text" name="data[BillingSetting][subm_upgrade_peak_hours_window]" value="<?php echo isset($settings['subm_upgrade_peak_hours_window']) ? h($settings['subm_upgrade_peak_hours_window']) : '17:00 - 22:00'; ?>" class="form-control subm-control" style="font-weight:600;" />
                                <p class="subm-field-help">
                                    <i class="fa fa-info-circle text-muted"></i> <?php echo __('e.g., 17:00 - 22:00 (5 PM to 10 PM).'); ?>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="subm-field-box">
                                <label class="subm-field-label">
                                    <i class="fa fa-bolt text-warning"></i> <?php echo __('Peak Surcharge Rate'); ?>
                                </label>
                                <div class="input-group">
                                    <input type="number" step="0.01" name="data[BillingSetting][subm_upgrade_peak_rate_addition]" value="<?php echo isset($settings['subm_upgrade_peak_rate_addition']) ? h($settings['subm_upgrade_peak_rate_addition']) : '8.50'; ?>" class="form-control subm-control" style="font-weight:bold;color:#0284c7;" />
                                    <span class="input-group-addon subm-addon"><?php echo __('PKR/Unit'); ?></span>
                                </div>
                                <p class="subm-field-help">
                                    <i class="fa fa-info-circle text-muted"></i> <?php echo __('Additional rate on peak units.'); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 1.4 Automated Late Fee Surcharge Engine & Grace Days -->
            <div class="subm-upgrade-card">
                <div class="subm-upgrade-header">
                    <div class="subm-feature-left-wrap">
                        <div class="subm-feature-icon-box" style="background:#fef2f2;color:#dc2626;">
                            <i class="fa fa-clock-o"></i>
                        </div>
                        <div class="subm-feature-info">
                            <div class="subm-feature-title">
                                <span><?php echo __('Automated Overdue Surcharge Engine & Grace Days'); ?></span>
                                <span class="label label-danger subm-badge-pill"><?php echo __('Revenue Protection'); ?></span>
                            </div>
                            <p class="subm-feature-desc">
                                <?php echo __('Automatically calculates and posts late payment surcharges on unpaid or partially paid bills after the configurable grace period expires.'); ?>
                            </p>
                        </div>
                    </div>
                    <div>
                        <input type="hidden" name="data[BillingSetting][subm_upgrade_auto_late_fee_enabled]" value="0" />
                        <label class="subm-switch">
                            <input type="checkbox" name="data[BillingSetting][subm_upgrade_auto_late_fee_enabled]" value="1" <?php if(!isset($settings['subm_upgrade_auto_late_fee_enabled']) || $settings['subm_upgrade_auto_late_fee_enabled'] == '1') echo 'checked'; ?> />
                            <span class="subm-slider"></span>
                        </label>
                    </div>
                </div>
                <div class="subm-upgrade-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="subm-field-box">
                                <label class="subm-field-label">
                                    <i class="fa fa-calendar-check-o text-danger"></i> <?php echo __('Grace Period (Post Due)'); ?>
                                </label>
                                <div class="input-group">
                                    <input type="number" name="data[BillingSetting][subm_upgrade_late_fee_grace_days]" value="<?php echo isset($settings['subm_upgrade_late_fee_grace_days']) ? h($settings['subm_upgrade_late_fee_grace_days']) : (isset($settings['subm_late_fee_grace_days']) ? h($settings['subm_late_fee_grace_days']) : '2'); ?>" class="form-control subm-control" style="font-weight:bold;color:#dc2626;" />
                                    <span class="input-group-addon subm-addon"><?php echo __('Days'); ?></span>
                                </div>
                                <p class="subm-field-help">
                                    <i class="fa fa-info-circle text-muted"></i> <?php echo __('Days allowed after due date.'); ?>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="subm-field-box">
                                <label class="subm-field-label">
                                    <i class="fa fa-calculator text-primary"></i> <?php echo __('Surcharge Mode'); ?>
                                </label>
                                <select name="data[BillingSetting][subm_upgrade_late_fee_mode]" class="form-control subm-control">
                                    <?php $lfm = isset($settings['subm_upgrade_late_fee_mode']) ? $settings['subm_upgrade_late_fee_mode'] : 'percent'; ?>
                                    <option value="percent" <?php if($lfm == 'percent') echo 'selected'; ?>><?php echo __('Percentage of Bill (%)'); ?></option>
                                    <option value="fixed" <?php if($lfm == 'fixed') echo 'selected'; ?>><?php echo __('Fixed Amount (PKR)'); ?></option>
                                </select>
                                <p class="subm-field-help">
                                    <i class="fa fa-info-circle text-muted"></i> <?php echo __('How late fee is computed.'); ?>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="subm-field-box">
                                <label class="subm-field-label">
                                    <i class="fa fa-percent text-danger"></i> <?php echo __('Surcharge Value'); ?>
                                </label>
                                <div class="input-group">
                                    <input type="number" step="0.01" name="data[BillingSetting][subm_upgrade_late_fee_rate]" value="<?php echo isset($settings['subm_upgrade_late_fee_rate']) ? h($settings['subm_upgrade_late_fee_rate']) : (isset($settings['subm_late_fee_percent']) ? h($settings['subm_late_fee_percent']) : '7.00'); ?>" class="form-control subm-control" style="font-weight:bold;" />
                                    <span class="input-group-addon subm-addon"><?php echo __('% / PKR'); ?></span>
                                </div>
                                <p class="subm-field-help">
                                    <i class="fa fa-info-circle text-muted"></i> <?php echo __('e.g., 7.0 for 7% surcharge.'); ?>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="subm-field-box">
                                <label class="subm-field-label">
                                    <i class="fa fa-history text-muted"></i> <?php echo __('Daily Auto-Execution Time'); ?>
                                </label>
                                <input type="text" name="data[BillingSetting][subm_upgrade_late_fee_cron_time]" value="<?php echo isset($settings['subm_upgrade_late_fee_cron_time']) ? h($settings['subm_upgrade_late_fee_cron_time']) : '00:05 AM'; ?>" class="form-control subm-control" style="font-weight:600;" />
                                <p class="subm-field-help">
                                    <i class="fa fa-info-circle text-muted"></i> <?php echo __('Background cron run schedule.'); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 1.5 Staged Defaulter Enforcement & Disconnection Workflow -->
            <div class="subm-upgrade-card">
                <div class="subm-upgrade-header">
                    <div class="subm-feature-left-wrap">
                        <div class="subm-feature-icon-box" style="background:#fdf2f8;color:#be185d;">
                            <i class="fa fa-gavel"></i>
                        </div>
                        <div class="subm-feature-info">
                            <div class="subm-feature-title">
                                <span><?php echo __('Staged Defaulter Enforcement & Disconnection Workflow'); ?></span>
                                <span class="label label-danger subm-badge-pill"><?php echo __('Recovery Hub'); ?></span>
                            </div>
                            <p class="subm-feature-desc">
                                <?php echo __('Multi-tiered aging pipeline (Stage 1 Courtesy -> Stage 2 Warning -> Stage 3 Disconnection) with automatic physical work order generation for linemen.'); ?>
                            </p>
                        </div>
                    </div>
                    <div>
                        <input type="hidden" name="data[BillingSetting][subm_upgrade_defaulter_workflow_enabled]" value="0" />
                        <label class="subm-switch">
                            <input type="checkbox" name="data[BillingSetting][subm_upgrade_defaulter_workflow_enabled]" value="1" <?php if(!isset($settings['subm_upgrade_defaulter_workflow_enabled']) || $settings['subm_upgrade_defaulter_workflow_enabled'] == '1') echo 'checked'; ?> />
                            <span class="subm-slider"></span>
                        </label>
                    </div>
                </div>
                <div class="subm-upgrade-body">
                    <div class="row">
                        <div class="col-md-2">
                            <div class="subm-field-box">
                                <label class="subm-field-label">
                                    <i class="fa fa-bell-o text-warning"></i> <?php echo __('Stage 1 Soft'); ?>
                                </label>
                                <div class="input-group">
                                    <input type="number" name="data[BillingSetting][subm_upgrade_defaulter_stage1_days]" value="<?php echo isset($settings['subm_upgrade_defaulter_stage1_days']) ? h($settings['subm_upgrade_defaulter_stage1_days']) : '5'; ?>" class="form-control subm-control" />
                                    <span class="input-group-addon subm-addon"><?php echo __('Days'); ?></span>
                                </div>
                                <p class="subm-field-help">
                                    <i class="fa fa-info-circle text-muted"></i> <?php echo __('Soft notice.'); ?>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="subm-field-box">
                                <label class="subm-field-label">
                                    <i class="fa fa-exclamation-triangle" style="color:#f97316;"></i> <?php echo __('Stage 2 Warn'); ?>
                                </label>
                                <div class="input-group">
                                    <input type="number" name="data[BillingSetting][subm_upgrade_defaulter_stage2_days]" value="<?php echo isset($settings['subm_upgrade_defaulter_stage2_days']) ? h($settings['subm_upgrade_defaulter_stage2_days']) : '15'; ?>" class="form-control subm-control" />
                                    <span class="input-group-addon subm-addon"><?php echo __('Days'); ?></span>
                                </div>
                                <p class="subm-field-help">
                                    <i class="fa fa-info-circle text-muted"></i> <?php echo __('Warning notice.'); ?>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="subm-field-box">
                                <label class="subm-field-label">
                                    <i class="fa fa-scissors text-danger"></i> <?php echo __('Stage 3 Cutoff'); ?>
                                </label>
                                <div class="input-group">
                                    <input type="number" name="data[BillingSetting][subm_upgrade_defaulter_stage3_days]" value="<?php echo isset($settings['subm_upgrade_defaulter_stage3_days']) ? h($settings['subm_upgrade_defaulter_stage3_days']) : '30'; ?>" class="form-control subm-control" style="font-weight:bold;color:#b91c1c;" />
                                    <span class="input-group-addon subm-addon"><?php echo __('Days'); ?></span>
                                </div>
                                <p class="subm-field-help">
                                    <i class="fa fa-info-circle text-muted"></i> <?php echo __('Disconnection order.'); ?>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="subm-field-box">
                                <label class="subm-field-label">
                                    <i class="fa fa-files-o text-info"></i> <?php echo __('Unpaid Bills'); ?>
                                </label>
                                <div class="input-group">
                                    <input type="number" name="data[BillingSetting][subm_upgrade_defaulter_unpaid_bills_count]" value="<?php echo isset($settings['subm_upgrade_defaulter_unpaid_bills_count']) ? h($settings['subm_upgrade_defaulter_unpaid_bills_count']) : '2'; ?>" class="form-control subm-control" />
                                    <span class="input-group-addon subm-addon"><?php echo __('Count'); ?></span>
                                </div>
                                <p class="subm-field-help">
                                    <i class="fa fa-info-circle text-muted"></i> <?php echo __('Trigger red flag.'); ?>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="subm-field-box">
                                <label class="subm-field-label">
                                    <i class="fa fa-money text-danger"></i> <?php echo __('Min Arrears Limit'); ?>
                                </label>
                                <div class="input-group">
                                    <input type="number" name="data[BillingSetting][subm_upgrade_defaulter_min_amount]" value="<?php echo isset($settings['subm_upgrade_defaulter_min_amount']) ? h($settings['subm_upgrade_defaulter_min_amount']) : '15000'; ?>" class="form-control subm-control" style="font-weight:bold;" />
                                    <span class="input-group-addon subm-addon"><?php echo __('PKR'); ?></span>
                                </div>
                                <p class="subm-field-help">
                                    <i class="fa fa-info-circle text-muted"></i> <?php echo __('Balance threshold.'); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 1.6 Installment & Split Payment Management -->
            <div class="subm-upgrade-card">
                <div class="subm-upgrade-header">
                    <div class="subm-feature-left-wrap">
                        <div class="subm-feature-icon-box" style="background:#f0fdfa;color:#0d9488;">
                            <i class="fa fa-calendar-check-o"></i>
                        </div>
                        <div class="subm-feature-info">
                            <div class="subm-feature-title">
                                <span><?php echo __('Installment & Split Payment Management'); ?></span>
                                <span class="label label-success subm-badge-pill"><?php echo __('Arrears Recovery'); ?></span>
                            </div>
                            <p class="subm-feature-desc">
                                <?php echo __('Enables management to break heavy accumulated arrears into structured monthly installments with separate vouchers and tracking.'); ?>
                            </p>
                        </div>
                    </div>
                    <div>
                        <input type="hidden" name="data[BillingSetting][subm_upgrade_installments_enabled]" value="0" />
                        <label class="subm-switch">
                            <input type="checkbox" name="data[BillingSetting][subm_upgrade_installments_enabled]" value="1" <?php if(!isset($settings['subm_upgrade_installments_enabled']) || $settings['subm_upgrade_installments_enabled'] == '1') echo 'checked'; ?> />
                            <span class="subm-slider"></span>
                        </label>
                    </div>
                </div>
                <div class="subm-upgrade-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="subm-field-box">
                                <label class="subm-field-label">
                                    <i class="fa fa-list-ol text-primary"></i> <?php echo __('Max Allowed Installments'); ?>
                                </label>
                                <select name="data[BillingSetting][subm_upgrade_installment_max_months]" class="form-control subm-control">
                                    <?php $maxM = isset($settings['subm_upgrade_installment_max_months']) ? $settings['subm_upgrade_installment_max_months'] : '3'; ?>
                                    <option value="2" <?php if($maxM == '2') echo 'selected'; ?>>2 Months Plan</option>
                                    <option value="3" <?php if($maxM == '3') echo 'selected'; ?>>3 Months Plan (Standard)</option>
                                    <option value="4" <?php if($maxM == '4') echo 'selected'; ?>>4 Months Plan</option>
                                    <option value="6" <?php if($maxM == '6') echo 'selected'; ?>>6 Months Plan (Extended)</option>
                                </select>
                                <p class="subm-field-help">
                                    <i class="fa fa-info-circle text-muted"></i> <?php echo __('Maximum tenure for installment plans.'); ?>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="subm-field-box">
                                <label class="subm-field-label">
                                    <i class="fa fa-money text-success"></i> <?php echo __('Min Eligible Amount'); ?>
                                </label>
                                <div class="input-group">
                                    <input type="number" name="data[BillingSetting][subm_upgrade_installment_min_amount]" value="<?php echo isset($settings['subm_upgrade_installment_min_amount']) ? h($settings['subm_upgrade_installment_min_amount']) : '20000'; ?>" class="form-control subm-control" style="font-weight:bold;" />
                                    <span class="input-group-addon subm-addon"><?php echo __('PKR'); ?></span>
                                </div>
                                <p class="subm-field-help">
                                    <i class="fa fa-info-circle text-muted"></i> <?php echo __('Bills below this cannot be split.'); ?>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="subm-field-box">
                                <label class="subm-field-label">
                                    <i class="fa fa-calendar text-info"></i> <?php echo __('Days Between Installment Dues'); ?>
                                </label>
                                <div class="input-group">
                                    <input type="number" name="data[BillingSetting][subm_upgrade_installment_interval_days]" value="<?php echo isset($settings['subm_upgrade_installment_interval_days']) ? h($settings['subm_upgrade_installment_interval_days']) : '30'; ?>" class="form-control subm-control" />
                                    <span class="input-group-addon subm-addon"><?php echo __('Days'); ?></span>
                                </div>
                                <p class="subm-field-help">
                                    <i class="fa fa-info-circle text-muted"></i> <?php echo __('Days between consecutive dues.'); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- =================================================================== -->
            <!-- SECTION 2: 🏠 CLIENT-SIDE (RESIDENT EXPERIENCE & SELF-SERVICE)       -->
            <!-- =================================================================== -->
            <div class="subm-upgrade-section-head">
                <div>
                    <h4 style="font-weight:800;color:#0f172a;margin:0 0 3px 0;font-size:16px;">
                        <i class="fa fa-user-circle text-primary"></i> <?php echo __('Section 2: Client-Side (Resident Experience & Portal)'); ?>
                    </h4>
                    <p style="margin:0;font-size:12.5px;color:#64748b;">
                        <?php echo __('Passwordless OTP login, online slip verification, consumption analytics, and digital NOC certificates.'); ?>
                    </p>
                </div>
                <span class="label label-primary subm-badge-pill" style="background:#e0f2fe;color:#0369a1;border:1px solid #bae6fd;font-size:11px;padding:5px 12px;"><?php echo __('Resident Portal'); ?></span>
            </div>

            <!-- 2.1 Passwordless WhatsApp OTP Login -->
            <div class="subm-upgrade-card">
                <div class="subm-upgrade-header">
                    <div class="subm-feature-left-wrap">
                        <div class="subm-feature-icon-box" style="background:#fffbeb;color:#d97706;">
                            <i class="fa fa-key"></i>
                        </div>
                        <div class="subm-feature-info">
                            <div class="subm-feature-title">
                                <span><?php echo __('Passwordless WhatsApp / SMS OTP Resident Login'); ?></span>
                                <span class="label label-warning subm-badge-pill"><?php echo __('Instant Access'); ?></span>
                            </div>
                            <p class="subm-feature-desc">
                                <?php echo __('Residents login instantly by entering their Consumer ID or Mobile number, receiving a secure 4-digit OTP via WhatsApp/SMS without remembering passwords.'); ?>
                            </p>
                        </div>
                    </div>
                    <div>
                        <input type="hidden" name="data[BillingSetting][subm_upgrade_otp_login_enabled]" value="0" />
                        <label class="subm-switch">
                            <input type="checkbox" name="data[BillingSetting][subm_upgrade_otp_login_enabled]" value="1" <?php if(!isset($settings['subm_upgrade_otp_login_enabled']) || $settings['subm_upgrade_otp_login_enabled'] == '1') echo 'checked'; ?> />
                            <span class="subm-slider"></span>
                        </label>
                    </div>
                </div>
                <div class="subm-upgrade-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="subm-field-box">
                                <label class="subm-field-label">
                                    <i class="fa fa-hourglass-half text-warning"></i> <?php echo __('OTP Expiry Duration'); ?>
                                </label>
                                <div class="input-group">
                                    <input type="number" name="data[BillingSetting][subm_upgrade_otp_expiry_minutes]" value="<?php echo isset($settings['subm_upgrade_otp_expiry_minutes']) ? h($settings['subm_upgrade_otp_expiry_minutes']) : '5'; ?>" class="form-control subm-control" style="font-weight:bold;" />
                                    <span class="input-group-addon subm-addon"><?php echo __('Minutes'); ?></span>
                                </div>
                                <p class="subm-field-help">
                                    <i class="fa fa-info-circle text-muted"></i> <?php echo __('Validity of generated OTP code.'); ?>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="subm-field-box">
                                <label class="subm-field-label">
                                    <i class="fa fa-repeat text-info"></i> <?php echo __('Max Resend Attempts'); ?>
                                </label>
                                <div class="input-group">
                                    <input type="number" name="data[BillingSetting][subm_upgrade_otp_max_resends]" value="<?php echo isset($settings['subm_upgrade_otp_max_resends']) ? h($settings['subm_upgrade_otp_max_resends']) : '5'; ?>" class="form-control subm-control" />
                                    <span class="input-group-addon subm-addon"><?php echo __('Per Day'); ?></span>
                                </div>
                                <p class="subm-field-help">
                                    <i class="fa fa-info-circle text-muted"></i> <?php echo __('Anti-spam rate limit on OTP generation.'); ?>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="subm-field-box">
                                <label class="subm-field-label">
                                    <i class="fa fa-paper-plane text-success"></i> <?php echo __('Primary OTP Channel'); ?>
                                </label>
                                <select name="data[BillingSetting][subm_upgrade_otp_channel]" class="form-control subm-control">
                                    <?php $otpc = isset($settings['subm_upgrade_otp_channel']) ? $settings['subm_upgrade_otp_channel'] : 'whatsapp_first'; ?>
                                    <option value="whatsapp_first" <?php if($otpc == 'whatsapp_first') echo 'selected'; ?>>WhatsApp First (with SMS Fallback)</option>
                                    <option value="whatsapp_only" <?php if($otpc == 'whatsapp_only') echo 'selected'; ?>>WhatsApp Only (Zero SMS Cost)</option>
                                    <option value="sms_only" <?php if($otpc == 'sms_only') echo 'selected'; ?>>SMS Gateway Only</option>
                                </select>
                                <p class="subm-field-help">
                                    <i class="fa fa-info-circle text-muted"></i> <?php echo __('Route for one-time passcodes.'); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2.2 Online Payment Slip Upload & 1-Click Verification -->
            <div class="subm-upgrade-card">
                <div class="subm-upgrade-header">
                    <div class="subm-feature-left-wrap">
                        <div class="subm-feature-icon-box" style="background:#f5f3ff;color:#7c3aed;">
                            <i class="fa fa-cloud-upload"></i>
                        </div>
                        <div class="subm-feature-info">
                            <div class="subm-feature-title">
                                <span><?php echo __('Online Proof-of-Payment Slip Upload with 1-Click Verification'); ?></span>
                                <span class="label label-success subm-badge-pill"><?php echo __('Self-Service'); ?></span>
                            </div>
                            <p class="subm-feature-desc">
                                <?php echo __('Residents upload bank/ATM transaction screenshots directly from their phone. Management reviews slips and approves with one click.'); ?>
                            </p>
                        </div>
                    </div>
                    <div>
                        <input type="hidden" name="data[BillingSetting][subm_upgrade_online_slip_upload_enabled]" value="0" />
                        <label class="subm-switch">
                            <input type="checkbox" name="data[BillingSetting][subm_upgrade_online_slip_upload_enabled]" value="1" <?php if(!isset($settings['subm_upgrade_online_slip_upload_enabled']) || $settings['subm_upgrade_online_slip_upload_enabled'] == '1') echo 'checked'; ?> />
                            <span class="subm-slider"></span>
                        </label>
                    </div>
                </div>
                <div class="subm-upgrade-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="subm-field-box">
                                <label class="subm-field-label">
                                    <i class="fa fa-clock-o text-danger"></i> <?php echo __('Pending Slip Auto-Expiry'); ?>
                                </label>
                                <div class="input-group">
                                    <input type="number" name="data[BillingSetting][subm_upgrade_slip_expiry_days]" value="<?php echo isset($settings['subm_upgrade_slip_expiry_days']) ? h($settings['subm_upgrade_slip_expiry_days']) : '7'; ?>" class="form-control subm-control" />
                                    <span class="input-group-addon subm-addon"><?php echo __('Days'); ?></span>
                                </div>
                                <p class="subm-field-help">
                                    <i class="fa fa-info-circle text-muted"></i> <?php echo __('Auto-cancel unverified submissions.'); ?>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="subm-field-box">
                                <label class="subm-field-label">
                                    <i class="fa fa-whatsapp text-success"></i> <?php echo __('Instant Staff Alert'); ?>
                                </label>
                                <select name="data[BillingSetting][subm_upgrade_slip_admin_notify]" class="form-control subm-control">
                                    <?php $san = isset($settings['subm_upgrade_slip_admin_notify']) ? $settings['subm_upgrade_slip_admin_notify'] : '1'; ?>
                                    <option value="1" <?php if($san == '1') echo 'selected'; ?>>Yes - Notify Accounts Officer immediately</option>
                                    <option value="0" <?php if($san == '0') echo 'selected'; ?>>No - Check portal manually</option>
                                </select>
                                <p class="subm-field-help">
                                    <i class="fa fa-info-circle text-muted"></i> <?php echo __('Alert staff when new slip is uploaded.'); ?>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="subm-field-box">
                                <label class="subm-field-label">
                                    <i class="fa fa-pie-chart text-primary"></i> <?php echo __('Allow Partial Payment Slips'); ?>
                                </label>
                                <select name="data[BillingSetting][subm_upgrade_slip_allow_partial]" class="form-control subm-control">
                                    <?php $sap = isset($settings['subm_upgrade_slip_allow_partial']) ? $settings['subm_upgrade_slip_allow_partial'] : '1'; ?>
                                    <option value="1" <?php if($sap == '1') echo 'selected'; ?>>Yes - Allow partial amount submissions</option>
                                    <option value="0" <?php if($sap == '0') echo 'selected'; ?>>No - Require full bill clearance</option>
                                </select>
                                <p class="subm-field-help">
                                    <i class="fa fa-info-circle text-muted"></i> <?php echo __('Accept part payments online.'); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2.3 Visual Consumption Analytics -->
            <div class="subm-upgrade-card">
                <div class="subm-upgrade-header">
                    <div class="subm-feature-left-wrap">
                        <div class="subm-feature-icon-box" style="background:#eff6ff;color:#0284c7;">
                            <i class="fa fa-area-chart"></i>
                        </div>
                        <div class="subm-feature-info">
                            <div class="subm-feature-title">
                                <span><?php echo __('Interactive Visual Consumption Analytics & YoY Trend Charts'); ?></span>
                                <span class="label label-info subm-badge-pill"><?php echo __('Transparency'); ?></span>
                            </div>
                            <p class="subm-feature-desc">
                                <?php echo __('Interactive bar charts on resident portal showing this month vs previous month and Year-over-Year usage comparison to spot anomalies early.'); ?>
                            </p>
                        </div>
                    </div>
                    <div>
                        <input type="hidden" name="data[BillingSetting][subm_upgrade_consumption_charts_enabled]" value="0" />
                        <label class="subm-switch">
                            <input type="checkbox" name="data[BillingSetting][subm_upgrade_consumption_charts_enabled]" value="1" <?php if(!isset($settings['subm_upgrade_consumption_charts_enabled']) || $settings['subm_upgrade_consumption_charts_enabled'] == '1') echo 'checked'; ?> />
                            <span class="subm-slider"></span>
                        </label>
                    </div>
                </div>
                <div class="subm-upgrade-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="subm-field-box">
                                <label class="subm-field-label">
                                    <i class="fa fa-calendar text-info"></i> <?php echo __('History Range Displayed on Portal'); ?>
                                </label>
                                <select name="data[BillingSetting][subm_upgrade_chart_history_months]" class="form-control subm-control">
                                    <?php $chm = isset($settings['subm_upgrade_chart_history_months']) ? $settings['subm_upgrade_chart_history_months'] : '12'; ?>
                                    <option value="6" <?php if($chm == '6') echo 'selected'; ?>>6 Months Comparison</option>
                                    <option value="12" <?php if($chm == '12') echo 'selected'; ?>>12 Months (Full Annual Cycle)</option>
                                    <option value="24" <?php if($chm == '24') echo 'selected'; ?>>24 Months (2-Year Trend)</option>
                                </select>
                                <p class="subm-field-help">
                                    <i class="fa fa-info-circle text-muted"></i> <?php echo __('Months of historical data rendered.'); ?>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="subm-field-box">
                                <label class="subm-field-label">
                                    <i class="fa fa-calculator text-primary"></i> <?php echo __('Daily Average Consumption Metric'); ?>
                                </label>
                                <select name="data[BillingSetting][subm_upgrade_chart_show_daily_avg]" class="form-control subm-control">
                                    <?php $sda = isset($settings['subm_upgrade_chart_show_daily_avg']) ? $settings['subm_upgrade_chart_show_daily_avg'] : '1'; ?>
                                    <option value="1" <?php if($sda == '1') echo 'selected'; ?>>Yes - Calculate and display daily average</option>
                                    <option value="0" <?php if($sda == '0') echo 'selected'; ?>>No - Total monthly units only</option>
                                </select>
                                <p class="subm-field-help">
                                    <i class="fa fa-info-circle text-muted"></i> <?php echo __('Helper metric for resident insights.'); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2.4 Digital Utility Clearance Certificate (NOC) -->
            <div class="subm-upgrade-card">
                <div class="subm-upgrade-header">
                    <div class="subm-feature-left-wrap">
                        <div class="subm-feature-icon-box" style="background:#f0fdf4;color:#059669;">
                            <i class="fa fa-certificate"></i>
                        </div>
                        <div class="subm-feature-info">
                            <div class="subm-feature-title">
                                <span><?php echo __('Digital Utility Clearance Certificate (No Demand Certificate / NOC)'); ?></span>
                                <span class="label label-success subm-badge-pill"><?php echo __('Verified Stamped'); ?></span>
                            </div>
                            <p class="subm-feature-desc">
                                <?php echo __('Allows fully cleared residents (PKR 0.00 balance) to download an official QR-verified, digitally stamped No Demand Certificate for move-outs and transfers.'); ?>
                            </p>
                        </div>
                    </div>
                    <div>
                        <input type="hidden" name="data[BillingSetting][subm_upgrade_digital_noc_enabled]" value="0" />
                        <label class="subm-switch">
                            <input type="checkbox" name="data[BillingSetting][subm_upgrade_digital_noc_enabled]" value="1" <?php if(!isset($settings['subm_upgrade_digital_noc_enabled']) || $settings['subm_upgrade_digital_noc_enabled'] == '1') echo 'checked'; ?> />
                            <span class="subm-slider"></span>
                        </label>
                    </div>
                </div>
                <div class="subm-upgrade-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="subm-field-box">
                                <label class="subm-field-label">
                                    <i class="fa fa-hourglass-end text-warning"></i> <?php echo __('NOC Certificate Validity'); ?>
                                </label>
                                <div class="input-group">
                                    <input type="number" name="data[BillingSetting][subm_upgrade_noc_validity_days]" value="<?php echo isset($settings['subm_upgrade_noc_validity_days']) ? h($settings['subm_upgrade_noc_validity_days']) : '30'; ?>" class="form-control subm-control" style="font-weight:bold;" />
                                    <span class="input-group-addon subm-addon"><?php echo __('Days'); ?></span>
                                </div>
                                <p class="subm-field-help">
                                    <i class="fa fa-info-circle text-muted"></i> <?php echo __('Validity period of issued certificate.'); ?>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="subm-field-box">
                                <label class="subm-field-label">
                                    <i class="fa fa-qrcode text-success"></i> <?php echo __('Anti-Counterfeit QR Code'); ?>
                                </label>
                                <select name="data[BillingSetting][subm_upgrade_noc_qr_verify]" class="form-control subm-control">
                                    <?php $nqv = isset($settings['subm_upgrade_noc_qr_verify']) ? $settings['subm_upgrade_noc_qr_verify'] : '1'; ?>
                                    <option value="1" <?php if($nqv == '1') echo 'selected'; ?>>Yes - Embed public verification QR code</option>
                                    <option value="0" <?php if($nqv == '0') echo 'selected'; ?>>No - Text certificate only</option>
                                </select>
                                <p class="subm-field-help">
                                    <i class="fa fa-info-circle text-muted"></i> <?php echo __('Scan QR to verify authenticity.'); ?>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="subm-field-box">
                                <label class="subm-field-label">
                                    <i class="fa fa-id-badge text-primary"></i> <?php echo __('Authorized Signatory Title'); ?>
                                </label>
                                <input type="text" name="data[BillingSetting][subm_upgrade_noc_signatory_title]" value="<?php echo isset($settings['subm_upgrade_noc_signatory_title']) ? h($settings['subm_upgrade_noc_signatory_title']) : 'Chief Administrator / General Secretary'; ?>" class="form-control subm-control" />
                                <p class="subm-field-help">
                                    <i class="fa fa-info-circle text-muted"></i> <?php echo __('Signature title on official NOC.'); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2.5 In-Bill Maintenance & Meter Complaint Desk -->
            <div class="subm-upgrade-card">
                <div class="subm-upgrade-header">
                    <div class="subm-feature-left-wrap">
                        <div class="subm-feature-icon-box" style="background:#fff7ed;color:#ea580c;">
                            <i class="fa fa-wrench"></i>
                        </div>
                        <div class="subm-feature-info">
                            <div class="subm-feature-title">
                                <span><?php echo __('In-Bill Maintenance & Meter Complaint Desk'); ?></span>
                                <span class="label label-warning subm-badge-pill"><?php echo __('Issue Tracking'); ?></span>
                            </div>
                            <p class="subm-feature-desc">
                                <?php echo __('Resident lodging desk embedded on bill view for reporting meter issues, low voltage, or water pressure problems with automated technician routing.'); ?>
                            </p>
                        </div>
                    </div>
                    <div>
                        <input type="hidden" name="data[BillingSetting][subm_upgrade_in_bill_complaints_enabled]" value="0" />
                        <label class="subm-switch">
                            <input type="checkbox" name="data[BillingSetting][subm_upgrade_in_bill_complaints_enabled]" value="1" <?php if(!isset($settings['subm_upgrade_in_bill_complaints_enabled']) || $settings['subm_upgrade_in_bill_complaints_enabled'] == '1') echo 'checked'; ?> />
                            <span class="subm-slider"></span>
                        </label>
                    </div>
                </div>
                <div class="subm-upgrade-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="subm-field-box">
                                <label class="subm-field-label">
                                    <i class="fa fa-clock-o text-danger"></i> <?php echo __('Auto-Escalation Threshold'); ?>
                                </label>
                                <div class="input-group">
                                    <input type="number" name="data[BillingSetting][subm_upgrade_complaint_escalation_days]" value="<?php echo isset($settings['subm_upgrade_complaint_escalation_days']) ? h($settings['subm_upgrade_complaint_escalation_days']) : '3'; ?>" class="form-control subm-control" style="font-weight:bold;" />
                                    <span class="input-group-addon subm-addon"><?php echo __('Days'); ?></span>
                                </div>
                                <p class="subm-field-help">
                                    <i class="fa fa-info-circle text-muted"></i> <?php echo __('Days before notifying Senior Administrator.'); ?>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="subm-field-box">
                                <label class="subm-field-label">
                                    <i class="fa fa-whatsapp text-success"></i> <?php echo __('Duty Lineman WhatsApp Alert'); ?>
                                </label>
                                <select name="data[BillingSetting][subm_upgrade_complaint_lineman_alert]" class="form-control subm-control">
                                    <?php $cla = isset($settings['subm_upgrade_complaint_lineman_alert']) ? $settings['subm_upgrade_complaint_lineman_alert'] : '1'; ?>
                                    <option value="1" <?php if($cla == '1') echo 'selected'; ?>>Yes - Dispatch instant alert to technical team</option>
                                    <option value="0" <?php if($cla == '0') echo 'selected'; ?>>No - Dashboard ticket only</option>
                                </select>
                                <p class="subm-field-help">
                                    <i class="fa fa-info-circle text-muted"></i> <?php echo __('Instant technician mobilization.'); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- =================================================================== -->
            <!-- SECTION 3: 🔔 MULTI-STAGE NOTIFICATIONS & WHATSAPP AUTOMATION HUB    -->
            <!-- =================================================================== -->
            <div class="subm-upgrade-section-head">
                <div>
                    <h4 style="font-weight:800;color:#0f172a;margin:0 0 3px 0;font-size:16px;">
                        <i class="fa fa-whatsapp text-success"></i> <?php echo __('Section 3: Smart Notifications & WhatsApp Automation Hub'); ?>
                    </h4>
                    <p style="margin:0;font-size:12.5px;color:#64748b;">
                        <?php echo __('Multi-stage drip reminders, instant PDF attachments, daily management digests, and 2-way AI bot.'); ?>
                    </p>
                </div>
                <span class="label label-success subm-badge-pill" style="background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0;font-size:11px;padding:5px 12px;"><?php echo __('WhatsApp LeadBot'); ?></span>
            </div>

            <!-- 3.1 Multi-Stage Drip WhatsApp Reminders Engine -->
            <div class="subm-upgrade-card">
                <div class="subm-upgrade-header">
                    <div class="subm-feature-left-wrap">
                        <div class="subm-feature-icon-box" style="background:#ecfdf5;color:#047857;">
                            <i class="fa fa-paper-plane"></i>
                        </div>
                        <div class="subm-feature-info">
                            <div class="subm-feature-title">
                                <span><?php echo __('Multi-Stage Drip WhatsApp Reminders Engine (Days Config)'); ?></span>
                                <span class="label label-success subm-badge-pill"><?php echo __('Automated Follow-ups'); ?></span>
                            </div>
                            <p class="subm-feature-desc">
                                <?php echo __('Intelligent multi-step reminder sequence triggered automatically relative to bill issue and due dates. Each notice includes the official binary PDF attachment.'); ?>
                            </p>
                        </div>
                    </div>
                    <div>
                        <input type="hidden" name="data[BillingSetting][subm_upgrade_drip_reminders_enabled]" value="0" />
                        <label class="subm-switch">
                            <input type="checkbox" name="data[BillingSetting][subm_upgrade_drip_reminders_enabled]" value="1" <?php if(!isset($settings['subm_upgrade_drip_reminders_enabled']) || $settings['subm_upgrade_drip_reminders_enabled'] == '1') echo 'checked'; ?> />
                            <span class="subm-slider"></span>
                        </label>
                    </div>
                </div>
                <div class="subm-upgrade-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="subm-field-box">
                                <label class="subm-field-label">
                                    <i class="fa fa-bell text-primary"></i> <?php echo __('Stage 1: Bill Issued'); ?>
                                </label>
                                <select name="data[BillingSetting][subm_upgrade_drip_bill_issued]" class="form-control subm-control">
                                    <?php $dbi = isset($settings['subm_upgrade_drip_bill_issued']) ? $settings['subm_upgrade_drip_bill_issued'] : '1'; ?>
                                    <option value="1" <?php if($dbi == '1') echo 'selected'; ?>>Send Instantly with PDF Bill</option>
                                    <option value="0" <?php if($dbi == '0') echo 'selected'; ?>>Manual Dispatch Only</option>
                                </select>
                                <p class="subm-field-help">
                                    <i class="fa fa-info-circle text-muted"></i> <?php echo __('Triggered on bill generation.'); ?>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="subm-field-box">
                                <label class="subm-field-label">
                                    <i class="fa fa-calendar-o text-info"></i> <?php echo __('Stage 2: Pre-Due'); ?>
                                </label>
                                <div class="input-group">
                                    <input type="number" name="data[BillingSetting][subm_upgrade_drip_pre_due_days]" value="<?php echo isset($settings['subm_upgrade_drip_pre_due_days']) ? h($settings['subm_upgrade_drip_pre_due_days']) : '3'; ?>" class="form-control subm-control" style="font-weight:bold;color:#0284c7;" />
                                    <span class="input-group-addon subm-addon"><?php echo __('Days Pre'); ?></span>
                                </div>
                                <p class="subm-field-help">
                                    <i class="fa fa-info-circle text-muted"></i> <?php echo __('Courtesy reminder.'); ?>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="subm-field-box">
                                <label class="subm-field-label">
                                    <i class="fa fa-clock-o text-warning"></i> <?php echo __('Stage 3: Due Date Alert'); ?>
                                </label>
                                <input type="text" name="data[BillingSetting][subm_upgrade_drip_due_date_time]" value="<?php echo isset($settings['subm_upgrade_drip_due_date_time']) ? h($settings['subm_upgrade_drip_due_date_time']) : '09:00 AM'; ?>" class="form-control subm-control" style="font-weight:600;" />
                                <p class="subm-field-help">
                                    <i class="fa fa-info-circle text-muted"></i> <?php echo __('Morning dispatch time.'); ?>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="subm-field-box">
                                <label class="subm-field-label">
                                    <i class="fa fa-exclamation text-orange" style="color:#f97316;"></i> <?php echo __('Stage 4: Overdue #1'); ?>
                                </label>
                                <div class="input-group">
                                    <input type="number" name="data[BillingSetting][subm_upgrade_drip_overdue_1_days]" value="<?php echo isset($settings['subm_upgrade_drip_overdue_1_days']) ? h($settings['subm_upgrade_drip_overdue_1_days']) : '2'; ?>" class="form-control subm-control" style="font-weight:bold;color:#ea580c;" />
                                    <span class="input-group-addon subm-addon"><?php echo __('Days Past'); ?></span>
                                </div>
                                <p class="subm-field-help">
                                    <i class="fa fa-info-circle text-muted"></i> <?php echo __('First overdue reminder.'); ?>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="subm-field-box">
                                <label class="subm-field-label">
                                    <i class="fa fa-exclamation-circle text-danger"></i> <?php echo __('Stage 5: Final Overdue'); ?>
                                </label>
                                <div class="input-group">
                                    <input type="number" name="data[BillingSetting][subm_upgrade_drip_overdue_2_days]" value="<?php echo isset($settings['subm_upgrade_drip_overdue_2_days']) ? h($settings['subm_upgrade_drip_overdue_2_days']) : '7'; ?>" class="form-control subm-control" style="font-weight:bold;color:#dc2626;" />
                                    <span class="input-group-addon subm-addon"><?php echo __('Days Past'); ?></span>
                                </div>
                                <p class="subm-field-help">
                                    <i class="fa fa-info-circle text-muted"></i> <?php echo __('Warning with surcharge.'); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3.2 Real-Time Payment Confirmation & WhatsApp Paid Slip PDF -->
            <div class="subm-upgrade-card">
                <div class="subm-upgrade-header">
                    <div class="subm-feature-left-wrap">
                        <div class="subm-feature-icon-box" style="background:#f0fdf4;color:#15803d;">
                            <i class="fa fa-check-circle"></i>
                        </div>
                        <div class="subm-feature-info">
                            <div class="subm-feature-title">
                                <span><?php echo __('Real-Time Payment Confirmation & WhatsApp Paid Slip PDF'); ?></span>
                                <span class="label label-success subm-badge-pill"><?php echo __('Instant Clearance'); ?></span>
                            </div>
                            <p class="subm-feature-desc">
                                <?php echo __('The instant payment is recorded (cash, bank, or online), system immediately fires a WhatsApp receipt with the official Paid Slip PDF attached.'); ?>
                            </p>
                        </div>
                    </div>
                    <div>
                        <input type="hidden" name="data[BillingSetting][subm_upgrade_instant_payment_receipt_enabled]" value="0" />
                        <label class="subm-switch">
                            <input type="checkbox" name="data[BillingSetting][subm_upgrade_instant_payment_receipt_enabled]" value="1" <?php if(!isset($settings['subm_upgrade_instant_payment_receipt_enabled']) || $settings['subm_upgrade_instant_payment_receipt_enabled'] == '1') echo 'checked'; ?> />
                            <span class="subm-slider"></span>
                        </label>
                    </div>
                </div>
                <div class="subm-upgrade-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="subm-field-box">
                                <label class="subm-field-label">
                                    <i class="fa fa-file-pdf-o text-danger"></i> <?php echo __('Attach Binary Paid Slip PDF'); ?>
                                </label>
                                <select name="data[BillingSetting][subm_upgrade_instant_receipt_attach_pdf]" class="form-control subm-control">
                                    <?php $irp = isset($settings['subm_upgrade_instant_receipt_attach_pdf']) ? $settings['subm_upgrade_instant_receipt_attach_pdf'] : '1'; ?>
                                    <option value="1" <?php if($irp == '1') echo 'selected'; ?>>Yes - Render and attach Receipt_WBxxxx.pdf</option>
                                    <option value="0" <?php if($irp == '0') echo 'selected'; ?>>No - Send text message receipt only</option>
                                </select>
                                <p class="subm-field-help">
                                    <i class="fa fa-info-circle text-muted"></i> <?php echo __('Transmits genuine PDF document to WhatsApp.'); ?>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="subm-field-box">
                                <label class="subm-field-label">
                                    <i class="fa fa-mobile text-primary" style="font-size:14px;"></i> <?php echo __('Fallback SMS if WhatsApp Fails'); ?>
                                </label>
                                <select name="data[BillingSetting][subm_upgrade_instant_receipt_fallback_sms]" class="form-control subm-control">
                                    <?php $irs = isset($settings['subm_upgrade_instant_receipt_fallback_sms']) ? $settings['subm_upgrade_instant_receipt_fallback_sms'] : '1'; ?>
                                    <option value="1" <?php if($irs == '1') echo 'selected'; ?>>Yes - Automatically dispatch SMS if LeadBot is unreachable</option>
                                    <option value="0" <?php if($irs == '0') echo 'selected'; ?>>No - WhatsApp channel only</option>
                                </select>
                                <p class="subm-field-help">
                                    <i class="fa fa-info-circle text-muted"></i> <?php echo __('High-reliability redundancy channel.'); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3.3 Daily Executive WhatsApp Digest for Management -->
            <div class="subm-upgrade-card">
                <div class="subm-upgrade-header">
                    <div class="subm-feature-left-wrap">
                        <div class="subm-feature-icon-box" style="background:#f8fafc;color:#0f172a;border:1px solid #cbd5e1;">
                            <i class="fa fa-briefcase"></i>
                        </div>
                        <div class="subm-feature-info">
                            <div class="subm-feature-title">
                                <span><?php echo __('Daily Executive WhatsApp Digest for Management'); ?></span>
                                <span class="label label-primary subm-badge-pill"><?php echo __('Executive KPI'); ?></span>
                            </div>
                            <p class="subm-feature-desc">
                                <?php echo __('Sends a daily summary directly to Society Administrator and President WhatsApp every evening: Total collections today, recovery rate, outstanding arrears, and top defaulters.'); ?>
                            </p>
                        </div>
                    </div>
                    <div>
                        <input type="hidden" name="data[BillingSetting][subm_upgrade_daily_digest_enabled]" value="0" />
                        <label class="subm-switch">
                            <input type="checkbox" name="data[BillingSetting][subm_upgrade_daily_digest_enabled]" value="1" <?php if(!isset($settings['subm_upgrade_daily_digest_enabled']) || $settings['subm_upgrade_daily_digest_enabled'] == '1') echo 'checked'; ?> />
                            <span class="subm-slider"></span>
                        </label>
                    </div>
                </div>
                <div class="subm-upgrade-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="subm-field-box">
                                <label class="subm-field-label">
                                    <i class="fa fa-clock-o text-primary"></i> <?php echo __('Daily Dispatch Time'); ?>
                                </label>
                                <input type="text" name="data[BillingSetting][subm_upgrade_daily_digest_time]" value="<?php echo isset($settings['subm_upgrade_daily_digest_time']) ? h($settings['subm_upgrade_daily_digest_time']) : '21:00 (9:00 PM)'; ?>" class="form-control subm-control" style="font-weight:600;" />
                                <p class="subm-field-help">
                                    <i class="fa fa-info-circle text-muted"></i> <?php echo __('Evening digest trigger time.'); ?>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="subm-field-box">
                                <label class="subm-field-label">
                                    <i class="fa fa-phone text-success"></i> <?php echo __('Administrator Mobile Numbers'); ?>
                                </label>
                                <input type="text" name="data[BillingSetting][subm_upgrade_daily_digest_phones]" value="<?php echo isset($settings['subm_upgrade_daily_digest_phones']) ? h($settings['subm_upgrade_daily_digest_phones']) : '03391101156'; ?>" class="form-control subm-control" style="font-family:monospace;font-weight:bold;color:#0369a1;" />
                                <p class="subm-field-help">
                                    <i class="fa fa-info-circle text-muted"></i> <?php echo __('Recipients for executive KPI digest.'); ?>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="subm-field-box">
                                <label class="subm-field-label">
                                    <i class="fa fa-list-alt text-danger"></i> <?php echo __('Include Top Defaulters'); ?>
                                </label>
                                <select name="data[BillingSetting][subm_upgrade_daily_digest_include_defaulters]" class="form-control subm-control">
                                    <?php $ddi = isset($settings['subm_upgrade_daily_digest_include_defaulters']) ? $settings['subm_upgrade_daily_digest_include_defaulters'] : '1'; ?>
                                    <option value="1" <?php if($ddi == '1') echo 'selected'; ?>>Yes - List Top 5 Highest Outstanding Defaulters</option>
                                    <option value="0" <?php if($ddi == '0') echo 'selected'; ?>>No - Financial totals only</option>
                                </select>
                                <p class="subm-field-help">
                                    <i class="fa fa-info-circle text-muted"></i> <?php echo __('Actionable summary.'); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3.4 Targeted Outage & Maintenance WhatsApp Broadcast -->
            <div class="subm-upgrade-card">
                <div class="subm-upgrade-header">
                    <div class="subm-feature-left-wrap">
                        <div class="subm-feature-icon-box" style="background:#fffbeb;color:#b45309;">
                            <i class="fa fa-bullhorn"></i>
                        </div>
                        <div class="subm-feature-info">
                            <div class="subm-feature-title">
                                <span><?php echo __('Targeted Outage & Maintenance WhatsApp Broadcast'); ?></span>
                                <span class="label label-warning subm-badge-pill"><?php echo __('Geo-Targeted'); ?></span>
                            </div>
                            <p class="subm-feature-desc">
                                <?php echo __('Send targeted outage/maintenance notices specifically to consumers linked to a specific Transformer, Tube-Well, or Block without disturbing unaffected residents.'); ?>
                            </p>
                        </div>
                    </div>
                    <div>
                        <input type="hidden" name="data[BillingSetting][subm_upgrade_targeted_broadcast_enabled]" value="0" />
                        <label class="subm-switch">
                            <input type="checkbox" name="data[BillingSetting][subm_upgrade_targeted_broadcast_enabled]" value="1" <?php if(!isset($settings['subm_upgrade_targeted_broadcast_enabled']) || $settings['subm_upgrade_targeted_broadcast_enabled'] == '1') echo 'checked'; ?> />
                            <span class="subm-slider"></span>
                        </label>
                    </div>
                </div>
                <div class="subm-upgrade-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="subm-field-box">
                                <label class="subm-field-label">
                                    <i class="fa fa-filter text-primary"></i> <?php echo __('Segmentation Modes'); ?>
                                </label>
                                <select name="data[BillingSetting][subm_upgrade_broadcast_segmentation]" class="form-control subm-control">
                                    <?php $bsg = isset($settings['subm_upgrade_broadcast_segmentation']) ? $settings['subm_upgrade_broadcast_segmentation'] : 'all'; ?>
                                    <option value="all" <?php if($bsg == 'all') echo 'selected'; ?>>Transformer Wise, Tube-Well Wise, and Block Wise</option>
                                    <option value="transformer_only" <?php if($bsg == 'transformer_only') echo 'selected'; ?>>Transformer / Sub-feeder Only</option>
                                    <option value="block_only" <?php if($bsg == 'block_only') echo 'selected'; ?>>Street / Block Only</option>
                                </select>
                                <p class="subm-field-help">
                                    <i class="fa fa-info-circle text-muted"></i> <?php echo __('Targeting granularity.'); ?>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="subm-field-box">
                                <label class="subm-field-label">
                                    <i class="fa fa-tachometer text-warning"></i> <?php echo __('Rate-Limit Anti-Ban Delay'); ?>
                                </label>
                                <div class="input-group">
                                    <input type="number" name="data[BillingSetting][subm_upgrade_broadcast_delay_ms]" value="<?php echo isset($settings['subm_upgrade_broadcast_delay_ms']) ? h($settings['subm_upgrade_broadcast_delay_ms']) : '500'; ?>" class="form-control subm-control" style="font-weight:bold;" />
                                    <span class="input-group-addon subm-addon"><?php echo __('ms'); ?></span>
                                </div>
                                <p class="subm-field-help">
                                    <i class="fa fa-info-circle text-muted"></i> <?php echo __('Pause between messages (500ms = 2 msgs/sec).'); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3.5 Interactive 2-Way WhatsApp Self-Service Bot -->
            <div class="subm-upgrade-card">
                <div class="subm-upgrade-header">
                    <div class="subm-feature-left-wrap">
                        <div class="subm-feature-icon-box" style="background:#ecfdf5;color:#059669;">
                            <i class="fa fa-comments"></i>
                        </div>
                        <div class="subm-feature-info">
                            <div class="subm-feature-title">
                                <span><?php echo __('Interactive 2-Way WhatsApp Self-Service Bot (Keywords Engine)'); ?></span>
                                <span class="label label-success subm-badge-pill"><?php echo __('24/7 AI Bot'); ?></span>
                            </div>
                            <p class="subm-feature-desc">
                                <?php echo __('Residents send keywords (e.g. BILL, DUES, SLIP, HELP) to the society WhatsApp number and the LeadBot engine auto-responds with their balance and PDF voucher immediately.'); ?>
                            </p>
                        </div>
                    </div>
                    <div>
                        <input type="hidden" name="data[BillingSetting][subm_upgrade_whatsapp_bot_enabled]" value="0" />
                        <label class="subm-switch">
                            <input type="checkbox" name="data[BillingSetting][subm_upgrade_whatsapp_bot_enabled]" value="1" <?php if(!isset($settings['subm_upgrade_whatsapp_bot_enabled']) || $settings['subm_upgrade_whatsapp_bot_enabled'] == '1') echo 'checked'; ?> />
                            <span class="subm-slider"></span>
                        </label>
                    </div>
                </div>
                <div class="subm-upgrade-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="subm-field-box">
                                <label class="subm-field-label">
                                    <i class="fa fa-tags text-success"></i> <?php echo __('Trigger Keywords (Comma-separated)'); ?>
                                </label>
                                <input type="text" name="data[BillingSetting][subm_upgrade_bot_keywords]" value="<?php echo isset($settings['subm_upgrade_bot_keywords']) ? h($settings['subm_upgrade_bot_keywords']) : 'BILL, DUES, SLIP, HELP, STATUS'; ?>" class="form-control subm-control" style="font-family:monospace;font-weight:bold;color:#059669;" />
                                <p class="subm-field-help">
                                    <i class="fa fa-info-circle text-muted"></i> <?php echo __('Keywords that trigger auto-reply.'); ?>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="subm-field-box">
                                <label class="subm-field-label">
                                    <i class="fa fa-commenting-o text-primary"></i> <?php echo __('Welcome & Menu Reply Template'); ?>
                                </label>
                                <textarea name="data[BillingSetting][subm_upgrade_bot_welcome_msg]" rows="3" class="form-control subm-control" style="font-family:monospace;font-size:12px;"><?php echo isset($settings['subm_upgrade_bot_welcome_msg']) ? h($settings['subm_upgrade_bot_welcome_msg']) : "🏛️ *WHITE VILLAS LAHORE 24/7 BILLING BOT*\nType *BILL* to get latest bill PDF\nType *SLIP* to download paid receipt\nType *DUES* to check outstanding balance"; ?></textarea>
                                <p class="subm-field-help">
                                    <i class="fa fa-info-circle text-muted"></i> <?php echo __('Greeting sent when a resident texts the society number.'); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Save Prompt for Upgrades -->
            <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:12px;padding:16px 24px;margin-top:20px;margin-bottom:10px;display:flex;align-items:center;justify-content:space-between;box-shadow:0 2px 6px rgba(0,0,0,0.03);">
                <div style="display:flex;align-items:center;gap:10px;">
                    <i class="fa fa-check-circle text-success" style="font-size:22px;"></i>
                    <div>
                        <strong style="color:#0f172a;font-size:13.5px;"><?php echo __('All 16 Smart Modules & Automation Parameters Configured'); ?></strong>
                        <p style="margin:2px 0 0 0;font-size:12px;color:#64748b;"><?php echo __('Click Save below or the button on the right to commit changes for'); ?> <?php echo h($activeSociety['name']); ?>.</p>
                    </div>
                </div>
                <button type="submit" class="btn btn-success" style="font-weight:800;padding:10px 28px;border-radius:8px;box-shadow:0 2px 6px rgba(16,185,129,0.25);">
                    <i class="fa fa-save"></i> <?php echo __('Save All Upgrades'); ?>
                </button>
            </div>
            <?php break;

        case 'qr-bank': ?>
            <!-- ========================================== -->
            <!-- DEDICATED TAB: BANK & QR SCAN-TO-PAY       -->
            <!-- ========================================== -->
            <div class="subm-card-section">
                <div class="subm-card-title"><i class="fa fa-qrcode text-success"></i> <?php echo __('SBP Raast / 1Bill EMVCo Dynamic QR Scan-to-Pay Activation'); ?></div>
                <div class="subm-card-body">
                    <?php $qrEnable = !empty($settings['subm_qr_enable']) ? 1 : 0; ?>
                    <div style="background:#f8fafc;border:2px solid #e2e8f0;border-radius:8px;padding:18px 22px;margin-bottom:24px;">
                        <input type="hidden" name="data[BillingSetting][subm_qr_enable]" value="0" />
                        <div style="display:flex;align-items:center;gap:12px;">
                            <input type="checkbox" name="data[BillingSetting][subm_qr_enable]" id="qrEnableToggle" value="1" <?php if($qrEnable) echo 'checked'; ?> style="width:18px;height:18px;cursor:pointer;" />
                            <div>
                                <label for="qrEnableToggle" style="font-weight:700;font-size:15px;color:#0f172a;margin:0;cursor:pointer;">
                                    <?php echo __('Enable Dynamic Bank QR Scan-to-Pay on Bills & Resident Portal'); ?>
                                </label>
                                <p class="text-muted" style="margin:4px 0 0 0;font-size:12.5px;">
                                    <?php echo __('Generates dynamic scannable QR images (EMVCo / SBP Raast / 1Bill standard) containing payable bill total amount and reference ID.'); ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label"><?php echo __('QR Code Standard Mode'); ?></label>
                        <div class="col-sm-6">
                            <select name="data[BillingSetting][subm_qr_type]" class="form-control" style="font-weight:bold;">
                                <option value="raast" <?php if(!isset($settings['subm_qr_type']) || $settings['subm_qr_type'] == 'raast') echo 'selected'; ?>>SBP Raast Dynamic QR (Instant Settlement)</option>
                                <option value="1bill" <?php if(isset($settings['subm_qr_type']) && $settings['subm_qr_type'] == '1bill') echo 'selected'; ?>>1Bill 1Link Invoice QR</option>
                                <option value="bank_account" <?php if(isset($settings['subm_qr_type']) && $settings['subm_qr_type'] == 'bank_account') echo 'selected'; ?>>Static Bank Account Scannable Image</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label"><?php echo __('Raast IBAN / Account Number'); ?></label>
                        <div class="col-sm-6">
                            <input type="text" name="data[BillingSetting][subm_qr_raast_iban]" value="<?php echo isset($settings['subm_qr_raast_iban']) ? h($settings['subm_qr_raast_iban']) : 'PK36UNIL0109000293817112'; ?>" class="form-control" style="font-family:monospace;font-weight:bold;color:#0369a1;" />
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label"><?php echo __('Merchant / Society ID'); ?></label>
                        <div class="col-sm-6">
                            <input type="text" name="data[BillingSetting][subm_qr_merchant_id]" value="<?php echo isset($settings['subm_qr_merchant_id']) ? h($settings['subm_qr_merchant_id']) : 'WV-LAHORE-01'; ?>" class="form-control" />
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label"><?php echo __('Receiving Bank Name'); ?></label>
                        <div class="col-sm-6">
                            <input type="text" name="data[BillingSetting][subm_qr_bank_name]" value="<?php echo isset($settings['subm_qr_bank_name']) ? h($settings['subm_qr_bank_name']) : 'United Bank Limited (UBL)'; ?>" class="form-control" />
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label"><?php echo __('Account Title'); ?></label>
                        <div class="col-sm-6">
                            <input type="text" name="data[BillingSetting][subm_qr_account_title]" value="<?php echo isset($settings['subm_qr_account_title']) ? h($settings['subm_qr_account_title']) : h($activeSociety['name']); ?>" class="form-control" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dynamic Bank Account Cards Manager -->
            <div class="subm-card-section">
                <div class="subm-card-title"><i class="fa fa-university text-primary"></i> <?php echo __('Bank Account Cards Manager (Appears on Voucher & Portal)'); ?></div>
                <div class="subm-card-body">
                    <div id="bankAccountsContainer">
                        <?php foreach($bankAccounts as $bk => $b){ ?>
                            <div class="dynamic-row-card" id="bankRow_<?php echo $bk; ?>" style="display:block;">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label><?php echo __('Bank Title'); ?></label>
                                        <input type="text" name="bank_accounts[<?php echo $bk; ?>][title]" value="<?php echo h($b['title']); ?>" class="form-control" style="font-weight:bold;" />
                                    </div>
                                    <div class="col-md-5">
                                        <label><?php echo __('Bank Details (HTML)'); ?></label>
                                        <textarea name="bank_accounts[<?php echo $bk; ?>][details]" rows="4" class="form-control" style="font-family:monospace;font-size:11.5px;"><?php echo h($b['details']); ?></textarea>
                                    </div>
                                    <div class="col-md-4">
                                        <label><?php echo __('QR Image URL'); ?></label>
                                        <input type="text" name="bank_accounts[<?php echo $bk; ?>][qr]" value="<?php echo h($b['qr']); ?>" class="form-control" />
                                        <div style="margin-top:8px;display:flex;gap:8px;align-items:center;">
                                            <?php if(!empty($b['qr'])){ ?>
                                                <img src="<?php echo h($b['qr']); ?>" style="width:48px;height:48px;border:1px solid #cbd5e1;border-radius:4px;object-fit:contain;" />
                                            <?php } ?>
                                            <button type="button" class="btn btn-xs btn-danger remove-bank-row" data-row="#bankRow_<?php echo $bk; ?>"><span class="fa fa-trash"></span> Remove Bank</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                    <button type="button" class="btn btn-primary btn-sm" id="addBankRowBtn" style="font-weight:700;margin-top:12px;">
                        <span class="fa fa-plus"></span> <?php echo __('+ Add Bank Account'); ?>
                    </button>
                </div>
            </div>
            <?php break;

        case 'features': ?>
            <!-- ========================================== -->
            <!-- DEDICATED TAB: ENTERPRISE FEATURE TOGGLES  -->
            <!-- ========================================== -->
            <div style="background:linear-gradient(135deg, #0f172a 0%, #1e293b 60%, #334155 100%);border-radius:12px;padding:22px 26px;color:#ffffff;margin-bottom:24px;box-shadow:0 4px 12px rgba(15,23,42,0.12);">
                <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:15px;">
                    <div>
                        <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px;">
                            <span style="font-size:24px;">⚡</span>
                            <h3 style="font-weight:800;margin:0;font-size:20px;color:#ffffff;letter-spacing:-0.3px;">
                                <?php echo __('Enterprise Feature Switches & Modular Automation Hub'); ?>
                            </h3>
                            <span class="label" style="background:#0284c7;font-size:11px;font-weight:700;padding:3px 8px;border-radius:6px;">13 MODULES</span>
                        </div>
                        <p style="margin:0;font-size:13px;color:#94a3b8;max-width:850px;line-height:1.5;">
                            <?php echo __('Turn enterprise automation features, AI reading aids, financial recovery engines, and sub-modules ON or OFF on demand. All settings apply instantly for'); ?>
                            <strong style="color:#ffffff;text-decoration:underline;"><?php echo h($activeSociety['name']); ?></strong>.
                        </p>
                    </div>
                    <div>
                        <button type="submit" class="btn btn-primary" style="font-weight:800;background:#0284c7;border:none;padding:10px 24px;border-radius:8px;box-shadow:0 2px 6px rgba(0,0,0,0.2);">
                            <i class="fa fa-save"></i> <?php echo __('Save Feature Toggles'); ?>
                        </button>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- ================= LEFT COLUMN ================= -->
                <div class="col-md-6">

                    <!-- GROUP 1: AI & SMART METER READING -->
                    <div class="subm-feature-group">
                        <div class="subm-feature-group-header">
                            <div style="display:flex;align-items:center;gap:10px;">
                                <i class="fa fa-camera text-primary"></i>
                                <span><?php echo __('Smart Meter Reading & AI Fraud Prevention'); ?></span>
                            </div>
                            <span class="label" style="background:#f1f5f9;color:#475569;border:1px solid #cbd5e1;font-size:11px;font-weight:700;">2 Features</span>
                        </div>

                        <!-- Feature: Camera OCR Aid -->
                        <div class="subm-feature-row">
                            <div class="subm-feature-left-wrap">
                                <div class="subm-feature-icon-box" style="background:#e0f2fe;color:#0284c7;">
                                    <i class="fa fa-camera"></i>
                                </div>
                                <div class="subm-feature-info">
                                    <div class="subm-feature-title">
                                        <span><?php echo __('Camera OCR & Smart Meter Reading Aid'); ?></span>
                                        <span class="label label-info subm-badge-pill"><?php echo __('Vision Aid'); ?></span>
                                    </div>
                                    <p class="subm-feature-desc">
                                        <?php echo __('Enables live camera shutter and smart digit detection helper in the meter reading portal and bulk entry screen, minimizing manual typo errors by lineman.'); ?>
                                    </p>
                                </div>
                            </div>
                            <div>
                                <input type="hidden" name="data[BillingSetting][subm_feat_ocr_meter_reader]" value="0" />
                                <label class="subm-switch">
                                    <input type="checkbox" name="data[BillingSetting][subm_feat_ocr_meter_reader]" value="1" <?php if(!isset($settings['subm_feat_ocr_meter_reader']) || $settings['subm_feat_ocr_meter_reader'] == '1') echo 'checked'; ?> />
                                    <span class="subm-slider"></span>
                                </label>
                            </div>
                        </div>

                        <!-- Feature: Anomaly Guard -->
                        <div class="subm-feature-row">
                            <div class="subm-feature-left-wrap">
                                <div class="subm-feature-icon-box" style="background:#fef3c7;color:#d97706;">
                                    <i class="fa fa-shield"></i>
                                </div>
                                <div class="subm-feature-info">
                                    <div class="subm-feature-title">
                                        <span><?php echo __('Consumption Anomaly & Spike Guard'); ?></span>
                                        <span class="label label-warning subm-badge-pill"><?php echo __('Fraud Guard'); ?></span>
                                    </div>
                                    <p class="subm-feature-desc">
                                        <?php echo __('Calculates 3-month moving average consumption. Automatically flags suspicious reading rollbacks (Current < Previous) and excessive spikes (>300% of average) before bills are locked.'); ?>
                                    </p>
                                </div>
                            </div>
                            <div>
                                <input type="hidden" name="data[BillingSetting][subm_feat_anomaly_guard]" value="0" />
                                <label class="subm-switch">
                                    <input type="checkbox" name="data[BillingSetting][subm_feat_anomaly_guard]" value="1" <?php if(!isset($settings['subm_feat_anomaly_guard']) || $settings['subm_feat_anomaly_guard'] == '1') echo 'checked'; ?> />
                                    <span class="subm-slider"></span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- GROUP 2: FINANCIAL ENGINES & AUTOMATION -->
                    <div class="subm-feature-group">
                        <div class="subm-feature-group-header">
                            <div style="display:flex;align-items:center;gap:10px;">
                                <i class="fa fa-dollar text-success"></i>
                                <span><?php echo __('Financial Automation & Billing Engines'); ?></span>
                            </div>
                            <span class="label" style="background:#f1f5f9;color:#475569;border:1px solid #cbd5e1;font-size:11px;font-weight:700;">3 Features</span>
                        </div>

                        <!-- Feature: Auto Late Fee -->
                        <div class="subm-feature-row">
                            <div class="subm-feature-left-wrap">
                                <div class="subm-feature-icon-box" style="background:#fee2e2;color:#dc2626;">
                                    <i class="fa fa-clock-o"></i>
                                </div>
                                <div class="subm-feature-info">
                                    <div class="subm-feature-title">
                                        <span><?php echo __('Automated Overdue Late-Fee Surcharge Engine'); ?></span>
                                        <span class="label label-danger subm-badge-pill"><?php echo __('Auto Revenue'); ?></span>
                                    </div>
                                    <p class="subm-feature-desc">
                                        <?php echo __('Automatically calculates and posts late payment surcharges on unpaid or partially paid bills past due date. Includes 1-Click batch runner and comprehensive audit trail.'); ?>
                                    </p>
                                </div>
                            </div>
                            <div>
                                <input type="hidden" name="data[BillingSetting][subm_feat_auto_late_fee]" value="0" />
                                <label class="subm-switch">
                                    <input type="checkbox" name="data[BillingSetting][subm_feat_auto_late_fee]" value="1" <?php if(!isset($settings['subm_feat_auto_late_fee']) || $settings['subm_feat_auto_late_fee'] == '1') echo 'checked'; ?> />
                                    <span class="subm-slider"></span>
                                </label>
                            </div>
                        </div>

                        <!-- Feature: Solar Net-Metering -->
                        <div class="subm-feature-row">
                            <div class="subm-feature-left-wrap">
                                <div class="subm-feature-icon-box" style="background:#fef3c7;color:#ca8a04;">
                                    <i class="fa fa-sun-o"></i>
                                </div>
                                <div class="subm-feature-info">
                                    <div class="subm-feature-title">
                                        <span><?php echo __('Solar Net-Metering & Dual-Source Units'); ?></span>
                                        <span class="label label-warning subm-badge-pill"><?php echo __('Green Energy'); ?></span>
                                    </div>
                                    <p class="subm-feature-desc">
                                        <?php echo __('Enables bidirectional Import vs Export kWh units accounting on solar consumers. Computes net billable units and carries forward excess units as utility bill credits.'); ?>
                                    </p>
                                </div>
                            </div>
                            <div>
                                <input type="hidden" name="data[BillingSetting][subm_feat_solar_net_metering]" value="0" />
                                <label class="subm-switch">
                                    <input type="checkbox" name="data[BillingSetting][subm_feat_solar_net_metering]" value="1" <?php if(!isset($settings['subm_feat_solar_net_metering']) || $settings['subm_feat_solar_net_metering'] == '1') echo 'checked'; ?> />
                                    <span class="subm-slider"></span>
                                </label>
                            </div>
                        </div>

                        <!-- Feature: Instant Payment Webhook -->
                        <div class="subm-feature-row">
                            <div class="subm-feature-left-wrap">
                                <div class="subm-feature-icon-box" style="background:#dcfce7;color:#16a34a;">
                                    <i class="fa fa-bolt"></i>
                                </div>
                                <div class="subm-feature-info">
                                    <div class="subm-feature-title">
                                        <span><?php echo __('Instant Payment Gateway Webhook & Auto-Settlement'); ?></span>
                                        <span class="label label-success subm-badge-pill"><?php echo __('Instant Pay'); ?></span>
                                    </div>
                                    <p class="subm-feature-desc">
                                        <?php echo __('Live webhook listener for JazzCash, EasyPaisa, Kuickpay, and 1Link banks. Automatically verifies payment callbacks, marks bill as Paid, and creates verified accounts ledger voucher.'); ?>
                                    </p>
                                </div>
                            </div>
                            <div>
                                <input type="hidden" name="data[BillingSetting][subm_feat_auto_webhook]" value="0" />
                                <label class="subm-switch">
                                    <input type="checkbox" name="data[BillingSetting][subm_feat_auto_webhook]" value="1" <?php if(!isset($settings['subm_feat_auto_webhook']) || $settings['subm_feat_auto_webhook'] == '1') echo 'checked'; ?> />
                                    <span class="subm-slider"></span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- GROUP 4: ENERGY AUDIT & NOTIFICATIONS -->
                    <div class="subm-feature-group">
                        <div class="subm-feature-group-header">
                            <div style="display:flex;align-items:center;gap:10px;">
                                <i class="fa fa-bar-chart text-info"></i>
                                <span><?php echo __('Grid Audit & Instant Receipts'); ?></span>
                            </div>
                            <span class="label" style="background:#f1f5f9;color:#475569;border:1px solid #cbd5e1;font-size:11px;font-weight:700;">2 Features</span>
                        </div>

                        <!-- Feature: Line Loss Audit -->
                        <div class="subm-feature-row">
                            <div class="subm-feature-left-wrap">
                                <div class="subm-feature-icon-box" style="background:#fef3c7;color:#b45309;">
                                    <i class="fa fa-flash"></i>
                                </div>
                                <div class="subm-feature-info">
                                    <div class="subm-feature-title">
                                        <span><?php echo __('Master Feeder / Transformer Line Loss & Theft Audit'); ?></span>
                                        <span class="label label-warning subm-badge-pill"><?php echo __('Energy Audit'); ?></span>
                                    </div>
                                    <p class="subm-feature-desc">
                                        <?php echo __('Reconciles incoming society master meter/feeder units against consumer sub-meter billed sums. Computes line loss percentage and flags high-loss zones indicative of power theft.'); ?>
                                    </p>
                                </div>
                            </div>
                            <div>
                                <input type="hidden" name="data[BillingSetting][subm_feat_line_loss_audit]" value="0" />
                                <label class="subm-switch">
                                    <input type="checkbox" name="data[BillingSetting][subm_feat_line_loss_audit]" value="1" <?php if(!isset($settings['subm_feat_line_loss_audit']) || $settings['subm_feat_line_loss_audit'] == '1') echo 'checked'; ?> />
                                    <span class="subm-slider"></span>
                                </label>
                            </div>
                        </div>

                        <!-- Feature: Instant WhatsApp/SMS Digital Receipts -->
                        <div class="subm-feature-row">
                            <div class="subm-feature-left-wrap">
                                <div class="subm-feature-icon-box" style="background:#dcfce7;color:#15803d;">
                                    <i class="fa fa-whatsapp"></i>
                                </div>
                                <div class="subm-feature-info">
                                    <div class="subm-feature-title">
                                        <span><?php echo __('WhatsApp & SMS Instant Digital Payment Receipts'); ?></span>
                                        <span class="label label-success subm-badge-pill"><?php echo __('Receipts'); ?></span>
                                    </div>
                                    <p class="subm-feature-desc">
                                        <?php echo __('Immediately dispatches verified payment confirmation receipts with transaction reference and printable receipt link via SMS & WhatsApp when payment is recorded.'); ?>
                                    </p>
                                </div>
                            </div>
                            <div>
                                <input type="hidden" name="data[BillingSetting][subm_feat_instant_receipts]" value="0" />
                                <label class="subm-switch">
                                    <input type="checkbox" name="data[BillingSetting][subm_feat_instant_receipts]" value="1" <?php if(!isset($settings['subm_feat_instant_receipts']) || $settings['subm_feat_instant_receipts'] == '1') echo 'checked'; ?> />
                                    <span class="subm-slider"></span>
                                </label>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- ================= RIGHT COLUMN ================= -->
                <div class="col-md-6">

                    <!-- GROUP 3: DEFAULTER RECOVERY & PROPERTY CLEARANCE -->
                    <div class="subm-feature-group">
                        <div class="subm-feature-group-header">
                            <div style="display:flex;align-items:center;gap:10px;">
                                <i class="fa fa-gavel text-danger"></i>
                                <span><?php echo __('Defaulter Recovery & Property Clearance'); ?></span>
                            </div>
                            <span class="label" style="background:#f1f5f9;color:#475569;border:1px solid #cbd5e1;font-size:11px;font-weight:700;">2 Features</span>
                        </div>

                        <!-- Feature: Staged Defaulter Enforcement -->
                        <div class="subm-feature-row">
                            <div class="subm-feature-left-wrap">
                                <div class="subm-feature-icon-box" style="background:#fee2e2;color:#b91c1c;">
                                    <i class="fa fa-ban"></i>
                                </div>
                                <div class="subm-feature-info">
                                    <div class="subm-feature-title">
                                        <span><?php echo __('Staged Defaulter Enforcement & Disconnection Work Orders'); ?></span>
                                        <span class="label label-danger subm-badge-pill"><?php echo __('Enforcement'); ?></span>
                                    </div>
                                    <p class="subm-feature-desc">
                                        <?php echo __('Tracks overdue aging stages (Grace -> Notice -> Red-Flagged -> Disconnection Ready) and generates official printable A4 Lineman Meter Disconnection Work Orders.'); ?>
                                    </p>
                                </div>
                            </div>
                            <div>
                                <input type="hidden" name="data[BillingSetting][subm_feat_defaulter_recovery]" value="0" />
                                <label class="subm-switch">
                                    <input type="checkbox" name="data[BillingSetting][subm_feat_defaulter_recovery]" value="1" <?php if(!isset($settings['subm_feat_defaulter_recovery']) || $settings['subm_feat_defaulter_recovery'] == '1') echo 'checked'; ?> />
                                    <span class="subm-slider"></span>
                                </label>
                            </div>
                        </div>

                        <!-- Feature: Tenant NDC Clearance -->
                        <div class="subm-feature-row">
                            <div class="subm-feature-left-wrap">
                                <div class="subm-feature-icon-box" style="background:#e0f2fe;color:#0369a1;">
                                    <i class="fa fa-certificate"></i>
                                </div>
                                <div class="subm-feature-info">
                                    <div class="subm-feature-title">
                                        <span><?php echo __('Tenant Move-Out Clearance & No Demand Certificate (NDC)'); ?></span>
                                        <span class="label label-primary subm-badge-pill"><?php echo __('Clearance'); ?></span>
                                    </div>
                                    <p class="subm-feature-desc">
                                        <?php echo __('Tenant vs Landlord billing profile separation. Calculates pro-rata final consumption upon tenant departure and generates official printable stamped No Demand Certificate.'); ?>
                                    </p>
                                </div>
                            </div>
                            <div>
                                <input type="hidden" name="data[BillingSetting][subm_feat_tenant_ndc]" value="0" />
                                <label class="subm-switch">
                                    <input type="checkbox" name="data[BillingSetting][subm_feat_tenant_ndc]" value="1" <?php if(!isset($settings['subm_feat_tenant_ndc']) || $settings['subm_feat_tenant_ndc'] == '1') echo 'checked'; ?> />
                                    <span class="subm-slider"></span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- GROUP 5: CORE MODULE SWITCHES -->
                    <div class="subm-feature-group">
                        <div class="subm-feature-group-header">
                            <div style="display:flex;align-items:center;gap:10px;">
                                <i class="fa fa-cogs text-secondary"></i>
                                <span><?php echo __('Core Sub-Modules & Public Portals'); ?></span>
                            </div>
                            <span class="label" style="background:#f1f5f9;color:#475569;border:1px solid #cbd5e1;font-size:11px;font-weight:700;">4 Features</span>
                        </div>

                        <!-- Feature: Water Utility -->
                        <div class="subm-feature-row">
                            <div class="subm-feature-left-wrap">
                                <div class="subm-feature-icon-box" style="background:#e0f2fe;color:#0284c7;">
                                    <i class="fa fa-tint"></i>
                                </div>
                                <div class="subm-feature-info">
                                    <div class="subm-feature-title">
                                        <span><?php echo __('Water Utility Module'); ?></span>
                                        <span class="label label-info subm-badge-pill"><?php echo __('Water Billing'); ?></span>
                                    </div>
                                    <p class="subm-feature-desc"><?php echo __('Enable volumetric water meter readings, water tariffs, and bill lines.'); ?></p>
                                </div>
                            </div>
                            <div>
                                <input type="hidden" name="data[BillingSetting][subm_feature_water_billing]" value="0" />
                                <label class="subm-switch">
                                    <input type="checkbox" name="data[BillingSetting][subm_feature_water_billing]" value="1" <?php if(!isset($settings['subm_feature_water_billing']) || $settings['subm_feature_water_billing'] == '1') echo 'checked'; ?> />
                                    <span class="subm-slider"></span>
                                </label>
                            </div>
                        </div>

                        <!-- Feature: Gas Utility -->
                        <div class="subm-feature-row">
                            <div class="subm-feature-left-wrap">
                                <div class="subm-feature-icon-box" style="background:#fee2e2;color:#dc2626;">
                                    <i class="fa fa-fire"></i>
                                </div>
                                <div class="subm-feature-info">
                                    <div class="subm-feature-title">
                                        <span><?php echo __('Gas Utility Module'); ?></span>
                                        <span class="label label-danger subm-badge-pill"><?php echo __('Gas Billing'); ?></span>
                                    </div>
                                    <p class="subm-feature-desc"><?php echo __('Enable gas meter readings, gas tariffs, and gas bill generation.'); ?></p>
                                </div>
                            </div>
                            <div>
                                <input type="hidden" name="data[BillingSetting][subm_feature_gas_billing]" value="0" />
                                <label class="subm-switch">
                                    <input type="checkbox" name="data[BillingSetting][subm_feature_gas_billing]" value="1" <?php if(!isset($settings['subm_feature_gas_billing']) || $settings['subm_feature_gas_billing'] == '1') echo 'checked'; ?> />
                                    <span class="subm-slider"></span>
                                </label>
                            </div>
                        </div>

                        <!-- Feature: Meter Swap -->
                        <div class="subm-feature-row">
                            <div class="subm-feature-left-wrap">
                                <div class="subm-feature-icon-box" style="background:#fef3c7;color:#d97706;">
                                    <i class="fa fa-refresh"></i>
                                </div>
                                <div class="subm-feature-info">
                                    <div class="subm-feature-title">
                                        <span><?php echo __('Meter Swap & Replacement Wizard'); ?></span>
                                        <span class="label label-warning subm-badge-pill"><?php echo __('Meter Ops'); ?></span>
                                    </div>
                                    <p class="subm-feature-desc"><?php echo __('Allows replacing faulty meters, archiving final readings, and carrying forward balances.'); ?></p>
                                </div>
                            </div>
                            <div>
                                <input type="hidden" name="data[BillingSetting][subm_feature_meter_swaps]" value="0" />
                                <label class="subm-switch">
                                    <input type="checkbox" name="data[BillingSetting][subm_feature_meter_swaps]" value="1" <?php if(!isset($settings['subm_feature_meter_swaps']) || $settings['subm_feature_meter_swaps'] == '1') echo 'checked'; ?> />
                                    <span class="subm-slider"></span>
                                </label>
                            </div>
                        </div>

                        <!-- Feature: Resident Portal -->
                        <div class="subm-feature-row">
                            <div class="subm-feature-left-wrap">
                                <div class="subm-feature-icon-box" style="background:#dcfce7;color:#16a34a;">
                                    <i class="fa fa-globe"></i>
                                </div>
                                <div class="subm-feature-info">
                                    <div class="subm-feature-title">
                                        <span><?php echo __('Online Resident Portal'); ?></span>
                                        <span class="label label-success subm-badge-pill"><?php echo __('Portal'); ?></span>
                                    </div>
                                    <p class="subm-feature-desc"><?php echo __('Enable public resident search portal to check bills, download PDFs, and submit proof of payment.'); ?></p>
                                </div>
                            </div>
                            <div>
                                <input type="hidden" name="data[BillingSetting][subm_feature_client_portal]" value="0" />
                                <label class="subm-switch">
                                    <input type="checkbox" name="data[BillingSetting][subm_feature_client_portal]" value="1" <?php if(!isset($settings['subm_feature_client_portal']) || $settings['subm_feature_client_portal'] == '1') echo 'checked'; ?> />
                                    <span class="subm-slider"></span>
                                </label>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <?php break;

        case 'billing-rules': ?>
            <!-- ========================================== -->
            <!-- DEDICATED TAB: BILLING RULES (2-COLUMNS)   -->
            <!-- ========================================== -->
            <div class="row">
                <!-- Left Column -->
                <div class="col-md-6">
                    <!-- Base Utility Rates & Due Period -->
                    <div class="subm-card-section">
                        <div class="subm-card-title"><i class="fa fa-sliders text-warning"></i> <?php echo __('Base Utility Rates & Due Period'); ?></div>
                        <div class="subm-card-body">
                            <div class="form-group">
                                <label class="col-sm-5 control-label"><?php echo __('Electricity Rate (per Unit)'); ?></label>
                                <div class="col-sm-7">
                                    <input type="number" step="0.01" name="data[BillingSetting][subm_rate_elec]" value="<?php echo isset($settings['subm_rate_elec']) ? h($settings['subm_rate_elec']) : '58.34'; ?>" class="form-control" style="font-weight:bold;color:#0284c7;" />
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-5 control-label"><?php echo __('Water Rate (per Unit)'); ?></label>
                                <div class="col-sm-7">
                                    <input type="number" step="0.01" name="data[BillingSetting][subm_rate_water]" value="<?php echo isset($settings['subm_rate_water']) ? h($settings['subm_rate_water']) : '100'; ?>" class="form-control" style="font-weight:bold;color:#0284c7;" />
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-5 control-label"><?php echo __('Gas Rate (per Unit)'); ?></label>
                                <div class="col-sm-7">
                                    <input type="number" step="0.01" name="data[BillingSetting][subm_rate_gas]" value="<?php echo isset($settings['subm_rate_gas']) ? h($settings['subm_rate_gas']) : '350'; ?>" class="form-control" style="font-weight:bold;color:#0284c7;" />
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-5 control-label"><?php echo __('Due After (Days)'); ?></label>
                                <div class="col-sm-5">
                                    <input type="number" name="data[BillingSetting][subm_due_after_days]" value="<?php echo isset($settings['subm_due_after_days']) ? h($settings['subm_due_after_days']) : '3'; ?>" class="form-control" />
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-5 control-label"><?php echo __('Overdue Surcharge %'); ?></label>
                                <div class="col-sm-5">
                                    <input type="number" step="0.01" name="data[BillingSetting][subm_late_fee_percent]" value="<?php echo isset($settings['subm_late_fee_percent']) ? h($settings['subm_late_fee_percent']) : '7'; ?>" class="form-control" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Auto Late-Fee Scheduler -->
                    <div class="subm-card-section">
                        <div class="subm-card-title"><i class="fa fa-clock-o text-primary"></i> <?php echo __('Auto Late-Fee Scheduler (Society Wise)'); ?></div>
                        <div class="subm-card-body">
                            <div class="form-group">
                                <label class="col-sm-5 control-label"><?php echo __('Daily Late Fee Cron'); ?></label>
                                <div class="col-sm-7">
                                    <label style="margin-top:7px;font-weight:400;"><input type="checkbox" name="data[BillingSetting][subm_late_fee_cron_enabled]" value="1" <?php if(!isset($settings['subm_late_fee_cron_enabled']) || $settings['subm_late_fee_cron_enabled'] == '1') echo 'checked'; ?> /> <?php echo __('Apply late fee automatically after due date'); ?></label>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-5 control-label"><?php echo __('Default Grace Days'); ?></label>
                                <div class="col-sm-5">
                                    <input type="number" name="data[BillingSetting][subm_late_fee_grace_days]" value="<?php echo isset($settings['subm_late_fee_grace_days']) ? h($settings['subm_late_fee_grace_days']) : '0'; ?>" class="form-control" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-5 control-label"><?php echo __('Unpaid Grace Days'); ?></label>
                                <div class="col-sm-5">
                                    <input type="number" name="data[BillingSetting][subm_late_fee_grace_unpaid]" value="<?php echo isset($settings['subm_late_fee_grace_unpaid']) ? h($settings['subm_late_fee_grace_unpaid']) : '0'; ?>" class="form-control" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-5 control-label"><?php echo __('Partial Grace Days'); ?></label>
                                <div class="col-sm-5">
                                    <input type="number" name="data[BillingSetting][subm_late_fee_grace_partial]" value="<?php echo isset($settings['subm_late_fee_grace_partial']) ? h($settings['subm_late_fee_grace_partial']) : '0'; ?>" class="form-control" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="col-md-6">
                    <!-- Meter Type Rules -->
                    <div class="subm-card-section">
                        <div class="subm-card-title"><i class="fa fa-tachometer text-danger"></i> <?php echo __('Meter Type Rules & Grace Days'); ?></div>
                        <div class="subm-card-body" style="padding:0;overflow-x:auto;">
                            <table class="subm-table-custom">
                                <thead>
                                    <tr>
                                        <th><?php echo __('METER TYPE'); ?></th>
                                        <th><?php echo __('ENABLE'); ?></th>
                                        <th><?php echo __('GRACE'); ?></th>
                                        <th><?php echo __('MODE'); ?></th>
                                        <th><?php echo __('VALUE'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $meterTypes = array('Electricity', 'Water', 'Gas');
                                    foreach($meterTypes as $mt){ 
                                        $mtSlug = strtolower($mt);
                                    ?>
                                        <tr>
                                            <td><strong><?php echo $mt; ?></strong></td>
                                            <td><label style="font-weight:400;"><input type="checkbox" name="data[BillingSetting][subm_meter_rule_<?php echo $mtSlug; ?>_enable]" value="1" <?php if(isset($settings['subm_meter_rule_'.$mtSlug.'_enable']) && $settings['subm_meter_rule_'.$mtSlug.'_enable'] == '1') echo 'checked'; ?> /> Yes</label></td>
                                            <td><input type="number" name="data[BillingSetting][subm_meter_rule_<?php echo $mtSlug; ?>_grace]" value="<?php echo isset($settings['subm_meter_rule_'.$mtSlug.'_grace']) ? h($settings['subm_meter_rule_'.$mtSlug.'_grace']) : '0'; ?>" class="form-control input-sm" style="width:65px;" /></td>
                                            <td>
                                                <select name="data[BillingSetting][subm_meter_rule_<?php echo $mtSlug; ?>_mode]" class="form-control input-sm" style="width:85px;">
                                                    <option value="percent" <?php if(isset($settings['subm_meter_rule_'.$mtSlug.'_mode']) && $settings['subm_meter_rule_'.$mtSlug.'_mode'] == 'percent') echo 'selected'; ?>>%</option>
                                                    <option value="fixed" <?php if(isset($settings['subm_meter_rule_'.$mtSlug.'_mode']) && $settings['subm_meter_rule_'.$mtSlug.'_mode'] == 'fixed') echo 'selected'; ?>>Fixed</option>
                                                </select>
                                            </td>
                                            <td><input type="number" step="0.1" name="data[BillingSetting][subm_meter_rule_<?php echo $mtSlug; ?>_val]" value="<?php echo isset($settings['subm_meter_rule_'.$mtSlug.'_val']) ? h($settings['subm_meter_rule_'.$mtSlug.'_val']) : '7'; ?>" class="form-control input-sm" style="width:65px;" /></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Full Width Area Slabs -->
            <div class="row">
                <div class="col-md-12">
                    <div class="subm-card-section">
                        <div class="subm-card-title"><i class="fa fa-cubes text-info"></i> <?php echo __('Area Slabs (Cost per Unit)'); ?></div>
                        <div class="subm-card-body">
                            <p class="description" style="color:#64748b;font-size:12.5px;margin-bottom:14px;">
                                <?php echo __('Meter-type slab by Meter Type + Property Type + Area Unit/Area Size + Consumed Units (Min/Max). During bill generation, matching slab rate is used.'); ?>
                            </p>

                            <div id="slabs_container" style="width:100%;">
                                <?php foreach($areaSlabs as $sk => $s){ ?>
                                    <div class="dynamic-row-card" id="slabRow_<?php echo $sk; ?>">
                                        <label>Meter Type</label>
                                        <select name="slab_meter_type[]" class="form-control input-sm" style="width:110px;">
                                            <option value="electricity" <?php if(!empty($s['meter_type']) && $s['meter_type'] == 'electricity') echo 'selected'; ?>>Electricity</option>
                                            <option value="water" <?php if(!empty($s['meter_type']) && $s['meter_type'] == 'water') echo 'selected'; ?>>Water</option>
                                            <option value="gas" <?php if(!empty($s['meter_type']) && $s['meter_type'] == 'gas') echo 'selected'; ?>>Gas</option>
                                        </select>

                                        <label>Type</label>
                                        <select name="slab_property_type[]" class="form-control input-sm" style="width:110px;">
                                            <option value="">Any</option>
                                            <?php foreach($propertyTypes as $pt){ ?>
                                                <option value="<?php echo $pt; ?>" <?php if(!empty($s['property_type']) && $s['property_type'] == $pt) echo 'selected'; ?>><?php echo $pt; ?></option>
                                            <?php } ?>
                                        </select>

                                        <label>Min Area</label>
                                        <input type="number" step="0.1" name="slab_area_min[]" value="<?php echo isset($s['area_min']) ? h($s['area_min']) : ''; ?>" class="form-control input-sm" style="width:65px;" />

                                        <label>Max Area</label>
                                        <input type="number" step="0.1" name="slab_area_max[]" value="<?php echo isset($s['area_max']) ? h($s['area_max']) : ''; ?>" class="form-control input-sm" style="width:65px;" />

                                        <label>Min Units</label>
                                        <input type="number" name="slab_min_units[]" value="<?php echo isset($s['min_units']) ? h($s['min_units']) : '0'; ?>" class="form-control input-sm" style="width:70px;" />

                                        <label>Max Units</label>
                                        <input type="number" name="slab_max_units[]" value="<?php echo isset($s['max_units']) ? h($s['max_units']) : ''; ?>" class="form-control input-sm" style="width:70px;" />

                                        <label>Rate (PKR)</label>
                                        <input type="number" step="0.01" name="slab_rate[]" value="<?php echo isset($s['rate']) ? h($s['rate']) : '0'; ?>" class="form-control input-sm" style="width:85px;" />

                                        <button type="button" class="btn btn-xs btn-danger remove-slab-row" data-row="#slabRow_<?php echo $sk; ?>" style="margin-left:auto;"><span class="fa fa-trash"></span> Remove</button>
                                    </div>
                                <?php } ?>
                            </div>

                            <button type="button" class="btn btn-primary btn-sm" id="addSlabBtn" style="font-weight:700;margin-top:12px;">
                                <span class="fa fa-plus"></span> <?php echo __('+ Add Unit Slab'); ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php break;

        case 'sms-whatsapp': ?>
            <!-- ========================================== -->
            <!-- DEDICATED TAB: SMS / WHATSAPP GATEWAY     -->
            <!-- ========================================== -->
            <div class="subm-card-section">
                <div class="subm-card-title"><i class="fa fa-paper-plane text-danger"></i> <?php echo __('SMS / WhatsApp Gateway Configuration'); ?></div>
                <div class="subm-card-body">
                    <?php $useSysSms = !empty($settings['subm_use_system_sms_gateway']) ? 1 : 0; ?>
                    <div style="background:#f8fafc;border:2px solid #e2e8f0;border-radius:8px;padding:18px 22px;margin-bottom:24px;">
                        <input type="hidden" name="data[BillingSetting][subm_use_system_sms_gateway]" value="0" />
                        <div style="display:flex;align-items:center;gap:12px;">
                            <input type="checkbox" name="data[BillingSetting][subm_use_system_sms_gateway]" id="useSystemSmsGateway" value="1" <?php if($useSysSms) echo 'checked'; ?> style="width:18px;height:18px;cursor:pointer;" />
                            <div>
                                <label for="useSystemSmsGateway" style="font-weight:700;font-size:15px;color:#0f172a;margin:0;cursor:pointer;">
                                    <?php echo __('Use Central System SMS Gateway'); ?>
                                </label>
                                <p class="text-muted" style="margin:4px 0 0 0;font-size:12.5px;">
                                    <?php echo __('Routes all bill notifications through global portal gateway parameters.'); ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div id="customSmsGatewayWrapper">
                        <div class="form-group">
                            <label class="col-sm-3 control-label"><?php echo __('API Endpoint URL'); ?></label>
                            <div class="col-sm-7">
                                <input type="text" name="data[BillingSetting][subm_custom_sms_url]" id="customSmsUrl" value="<?php echo isset($settings['subm_custom_sms_url']) ? h($settings['subm_custom_sms_url']) : 'https://api.sms-gateway.pk/v1/send'; ?>" class="form-control" style="font-family:monospace;" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label"><?php echo __('API Secret Token / Key'); ?></label>
                            <div class="col-sm-6">
                                <input type="password" name="data[BillingSetting][subm_custom_sms_token]" id="customSmsToken" value="<?php echo isset($settings['subm_custom_sms_token']) ? h($settings['subm_custom_sms_token']) : 'WV_API_TOKEN_881923'; ?>" class="form-control" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label"><?php echo __('Sender ID / Mask'); ?></label>
                            <div class="col-sm-4">
                                <input type="text" name="data[BillingSetting][subm_custom_sms_sender]" id="customSmsSender" value="<?php echo isset($settings['subm_custom_sms_sender']) ? h($settings['subm_custom_sms_sender']) : 'WHITEVILLAS'; ?>" class="form-control" style="font-weight:bold;" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label"><?php echo __('HTTP Request Method'); ?></label>
                            <div class="col-sm-4">
                                <select name="data[BillingSetting][subm_custom_sms_method]" id="customSmsMethod" class="form-control">
                                    <option value="POST" <?php if(!isset($settings['subm_custom_sms_method']) || $settings['subm_custom_sms_method'] == 'POST') echo 'selected'; ?>>POST (Form Data / JSON)</option>
                                    <option value="GET" <?php if(isset($settings['subm_custom_sms_method']) && $settings['subm_custom_sms_method'] == 'GET') echo 'selected'; ?>>GET (URL Parameters)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Test SMS Dispatcher -->
            <div class="subm-card-section">
                <div class="subm-card-title"><i class="fa fa-paper-plane text-success"></i> <?php echo __('Dispatch Test SMS Notification'); ?></div>
                <div class="subm-card-body">
                    <div class="form-group">
                        <label class="col-sm-3 control-label"><?php echo __('Test Mobile Number'); ?></label>
                        <div class="col-sm-5">
                            <input type="text" id="testSmsPhone" class="form-control" value="03391101156" placeholder="03391101156" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label"><?php echo __('Test Message Body'); ?></label>
                        <div class="col-sm-7">
                            <textarea id="testSmsMessage" rows="2" class="form-control">White Villas Lahore: Test Gateway dispatch verified successfully!</textarea>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-offset-3 col-sm-9">
                            <button type="button" id="btnSendTestSms" class="btn btn-success" style="font-weight:800;padding:8px 24px;">
                                <i class="fa fa-send"></i> <?php echo __('Send Test SMS Now'); ?>
                            </button>
                        </div>
                    </div>
                    <div id="testSmsResultAlert" style="display:none;margin-top:14px;"></div>
                </div>
            </div>
            <?php break;

        case 'templates': ?>
            <!-- ========================================== -->
            <!-- DEDICATED TAB: PRINT & EMAIL TEMPLATES     -->
            <!-- ========================================== -->
            <div class="subm-card-section">
                <div class="subm-card-title"><i class="fa fa-print text-success"></i> <?php echo __('HTML Bill Voucher Print Header & Designer'); ?></div>
                <div class="subm-card-body">
                    <div class="form-group">
                        <label class="col-sm-2 control-label"><?php echo __('Enable Custom Header'); ?></label>
                        <div class="col-sm-10">
                            <label style="margin-top:7px;font-weight:400;">
                                <input type="checkbox" name="data[BillingSetting][subm_print_header_enabled]" value="1" <?php if(!isset($settings['subm_print_header_enabled']) || $settings['subm_print_header_enabled'] == '1') echo 'checked'; ?> /> 
                                <?php echo __('Show custom header block on printed vouchers and exported reports'); ?>
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label"><?php echo __('Header HTML Template'); ?></label>
                        <div class="col-sm-10">
                            <textarea name="data[BillingSetting][subm_print_header_html]" rows="6" class="form-control" style="font-family:monospace;font-size:12px;width:100%;"><?php echo isset($settings['subm_print_header_html']) ? h($settings['subm_print_header_html']) : "<!-- Modern Plugin Header -->\n<div style=\"align-items:center;justify-content:space-between;padding:18px 22px;margin:0 0 16px;background:#ffffff;border-radius:14px;\">\n  <div><div style=\"font-size:20px;font-weight:700;\">{{society_name}}</div></div>\n</div>"; ?></textarea>
                            <span class="help-block" style="font-size:11.5px;color:#64748b;">
                                <?php echo __('Placeholders available'); ?>: <code>{{society_logo}}</code>, <code>{{society_name}}</code>, <code>{{society_address}}</code>, <code>{{society_mobile}}</code>, <code>{{print_title}}</code>, <code>{{print_date}}</code>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Print Templates Listing from Database -->
            <div class="subm-card-section">
                <div class="subm-card-title"><i class="fa fa-file-code-o text-primary"></i> <?php echo __('Registered HTML Invoice Formats & Bank Vouchers'); ?></div>
                <div class="subm-card-body" style="padding:0;overflow-x:auto;">
                    <table class="subm-table-custom">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th><?php echo __('TEMPLATE NAME'); ?></th>
                                <th><?php echo __('TYPE'); ?></th>
                                <th><?php echo __('DEFAULT'); ?></th>
                                <th><?php echo __('ACTION'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($templates)){ ?>
                                <tr><td colspan="5" class="text-center text-muted" style="padding:20px;"><?php echo __('No bill print templates found in database.'); ?></td></tr>
                            <?php } else { foreach($templates as $t){ $tm = $t['BillingTemplate']; ?>
                                <tr>
                                    <td>#<?php echo (int)$tm['id']; ?></td>
                                    <td><strong style="font-size:13.5px;color:#0f172a;"><?php echo h(!empty($tm['name']) ? $tm['name'] : 'HTML Bill Voucher Template #' . $tm['id']); ?></strong></td>
                                    <td><span class="label label-default"><?php echo h(!empty($tm['template_type']) ? $tm['template_type'] : 'print_bill'); ?></span></td>
                                    <td>
                                        <?php if(!empty($tm['is_default'])){ ?>
                                            <span class="label label-success"><i class="fa fa-check-circle"></i> <?php echo __('Active Default'); ?></span>
                                        <?php } else { ?>
                                            <span class="text-muted"><?php echo __('Standard'); ?></span>
                                        <?php } ?>
                                    </td>
                                     <td>
                                        <a href="<?php echo $this->Html->url(array('controller' => 'billing_templates', 'action' => 'preview', $tm['id'])); ?>" target="_blank" class="btn btn-xs btn-info" style="font-weight:700;margin-right:4px;">
                                            <i class="fa fa-eye"></i> <?php echo __('Preview / View Print'); ?>
                                        </a>
                                        <a href="<?php echo $this->Html->url(array('controller' => 'billing_templates', 'action' => 'edit', $tm['id'])); ?>" class="btn btn-xs btn-primary" style="font-weight:700;">
                                            <i class="fa fa-pencil"></i> <?php echo __('Edit HTML Code'); ?>
                                        </a>
                                     </td>
                                </tr>
                            <?php } } ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php break;

        case 'other-charges': ?>
            <!-- ========================================== -->
            <!-- DEDICATED TAB: SOCIETY MAINTENANCE        -->
            <!-- ========================================== -->
            <div class="subm-card-section">
                <div class="subm-card-title" style="justify-content:space-between;">
                    <div><i class="fa fa-list-alt text-primary"></i> <?php echo __('Society Extra Charges & Fixed Heads'); ?></div>
                    <a href="<?php echo $this->Html->url(array('controller' => 'billing_extra_charges', 'action' => 'add')); ?>" class="btn btn-xs btn-primary" style="font-weight:700;">
                        <i class="fa fa-plus"></i> <?php echo __('+ Add Extra Charge Head'); ?>
                    </a>
                </div>
                <div class="subm-card-body" style="padding:0;overflow-x:auto;">
                    <table class="subm-table-custom">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th><?php echo __('SOCIETY'); ?></th>
                                <th><?php echo __('CHARGE TITLE'); ?></th>
                                <th><?php echo __('AMOUNT (PKR)'); ?></th>
                                <th><?php echo __('TYPE'); ?></th>
                                <th><?php echo __('APPLICATION'); ?></th>
                                <th><?php echo __('STATUS'); ?></th>
                                <th><?php echo __('ACTIONS'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($extraCharges)){ ?>
                                <tr><td colspan="8" class="text-center text-muted" style="padding:20px;"><?php echo __('No extra charges configured.'); ?></td></tr>
                            <?php } else { foreach($extraCharges as $ch){ $c = $ch['BillingExtraCharge']; ?>
                                <tr>
                                    <td>#<?php echo (int)$c['id']; ?></td>
                                    <td><strong style="color:#0369a1;"><i class="fa fa-building"></i> <?php echo h($activeSociety['name']); ?></strong></td>
                                    <td><strong style="font-size:13.5px;color:#0f172a;"><?php echo h($c['title']); ?></strong></td>
                                    <td><strong style="color:#0284c7;">PKR <?php echo number_format((float)$c['amount'], 2); ?></strong></td>
                                    <td><span class="label label-info"><?php echo ucfirst(h($c['charge_type'])); ?></span></td>
                                    <td><span class="label label-default"><?php echo ucfirst(h($c['apply_to'])); ?></span></td>
                                    <td><span class="label label-success"><?php echo ucfirst(h($c['status'])); ?></span></td>
                                    <td>
                                        <a href="<?php echo $this->Html->url(array('controller' => 'billing_extra_charges', 'action' => 'edit', $c['id'])); ?>" class="btn btn-xs btn-default"><i class="fa fa-pencil"></i></a>
                                        <a href="<?php echo $this->Html->url(array('controller' => 'billing_extra_charges', 'action' => 'delete', $c['id'])); ?>" class="btn btn-xs btn-danger" onclick="return confirm('Delete this charge head?');"><i class="fa fa-trash"></i></a>
                                    </td>
                                </tr>
                            <?php } } ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Society Maintenance Breakdown (Conditional Rules) -->
            <div class="subm-card-section">
                <div class="subm-card-title" style="justify-content:space-between;">
                    <div><i class="fa fa-sliders text-warning"></i> <?php echo __('Society Maintenance Breakdown (Conditional by Property Type & Area)'); ?></div>
                    <button type="button" class="btn btn-xs btn-warning" data-toggle="modal" data-target="#copyRulesModal" style="font-weight:700;">
                        <i class="fa fa-copy"></i> <?php echo __('Copy Rules from another Society'); ?>
                    </button>
                </div>
                <div class="subm-card-body">
                    <p class="description" style="color:#64748b;font-size:12.5px;margin-bottom:14px;">
                        <?php echo __('Define breakdown items (Services Charges, Water Bill, Garbage Collection, Security Guard) isolated per society, property type, and area ranges.'); ?>
                    </p>

                    <div id="m_heads_container" style="width:100%;">
                        <?php foreach($maintenanceHeads as $mk => $m){ ?>
                            <div class="dynamic-row-card" id="mHeadRow_<?php echo $mk; ?>">
                                <label>Item Name</label>
                                <input type="text" name="m_head_name[]" value="<?php echo h($m['name']); ?>" class="form-control input-sm" style="width:150px;font-weight:bold;" />

                                <label>Property Type</label>
                                <select name="m_head_property_type[]" class="form-control input-sm" style="width:120px;">
                                    <?php foreach($propertyTypes as $pt){ ?>
                                        <option value="<?php echo $pt; ?>" <?php if(!empty($m['property_type']) && $m['property_type'] == $pt) echo 'selected'; ?>><?php echo $pt; ?></option>
                                    <?php } ?>
                                </select>

                                <label>Amount (PKR)</label>
                                <input type="number" step="0.01" name="m_head_amount[]" value="<?php echo isset($m['amount']) ? h($m['amount']) : '0'; ?>" class="form-control input-sm" style="width:90px;font-weight:bold;color:#0284c7;" />

                                <label>Min Area</label>
                                <input type="number" step="0.1" name="m_head_min_area[]" value="<?php echo isset($m['min_area']) ? h($m['min_area']) : ''; ?>" class="form-control input-sm" style="width:65px;" />

                                <label>Max Area</label>
                                <input type="number" step="0.1" name="m_head_max_area[]" value="<?php echo isset($m['max_area']) ? h($m['max_area']) : ''; ?>" class="form-control input-sm" style="width:65px;" />

                                <label>Unit</label>
                                <select name="m_head_unit[]" class="form-control input-sm" style="width:90px;">
                                    <?php foreach($areaUnits as $au){ ?>
                                        <option value="<?php echo $au; ?>" <?php if(!empty($m['unit']) && $m['unit'] == $au) echo 'selected'; ?>><?php echo $au; ?></option>
                                    <?php } ?>
                                </select>

                                <button type="button" class="btn btn-xs btn-danger remove-m-head-row" data-row="#mHeadRow_<?php echo $mk; ?>" style="margin-left:auto;"><span class="fa fa-trash"></span> Remove</button>
                            </div>
                        <?php } ?>
                    </div>

                    <button type="button" class="btn btn-success btn-sm" id="addMHeadBtn" style="font-weight:700;margin-top:12px;">
                        <span class="fa fa-plus"></span> <?php echo __('+ Add Breakdown Head Item'); ?>
                    </button>
                </div>
            </div>
            <?php break;

        case 'backup': ?>
            <!-- ========================================== -->
            <!-- DEDICATED TAB: RESTORE & BACKUP ENGINE     -->
            <!-- ========================================== -->
            <div class="subm-card-section">
                <div class="subm-card-title"><i class="fa fa-database text-warning"></i> <?php echo __('Billing System Backup & Migration Hub'); ?></div>
                <div class="subm-card-body">
                    <!-- Database Counts Status -->
                    <div class="row" style="margin-bottom:20px;">
                        <div class="col-md-3 col-sm-6">
                            <div style="background:#0f172a;color:#fff;border-radius:10px;padding:16px;">
                                <div style="font-size:11px;text-transform:uppercase;color:#94a3b8;font-weight:700;"><?php echo __('Total Invoices'); ?></div>
                                <div style="font-size:22px;font-weight:800;color:#38bdf8;margin:6px 0;"><?php echo isset($stats['bills']) ? (int)$stats['bills'] : 0; ?></div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div style="background:#0f172a;color:#fff;border-radius:10px;padding:16px;">
                                <div style="font-size:11px;text-transform:uppercase;color:#94a3b8;font-weight:700;"><?php echo __('Meter Readings'); ?></div>
                                <div style="font-size:22px;font-weight:800;color:#10b981;margin:6px 0;"><?php echo isset($stats['readings']) ? (int)$stats['readings'] : 0; ?></div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div style="background:#0f172a;color:#fff;border-radius:10px;padding:16px;">
                                <div style="font-size:11px;text-transform:uppercase;color:#94a3b8;font-weight:700;"><?php echo __('Utility Meters'); ?></div>
                                <div style="font-size:22px;font-weight:800;color:#f59e0b;margin:6px 0;"><?php echo isset($stats['meters']) ? (int)$stats['meters'] : 0; ?></div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div style="background:#0f172a;color:#fff;border-radius:10px;padding:16px;">
                                <div style="font-size:11px;text-transform:uppercase;color:#94a3b8;font-weight:700;"><?php echo __('Consumers & Units'); ?></div>
                                <div style="font-size:22px;font-weight:800;color:#a855f7;margin:6px 0;"><?php echo (isset($stats['clients']) ? (int)$stats['clients'] : 0) . ' / ' . (isset($stats['properties']) ? (int)$stats['properties'] : 0); ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:24px;text-align:center;">
                                <h3 style="font-weight:800;color:#0f172a;margin-top:0;"><i class="fa fa-download text-success"></i> <?php echo __('Export Billing Backup (.JSON)'); ?></h3>
                                <p style="color:#64748b;font-size:13px;margin-bottom:20px;">
                                    <?php echo __('Download a complete standalone JSON backup of all utility bills, meters, consumers, historical readings, maintenance heads, and print templates.'); ?>
                                </p>
                                <a href="<?php echo $this->Html->url(array('controller' => 'billing_migrations', 'action' => 'export_json')); ?>" class="btn btn-success btn-lg" style="font-weight:800;padding:12px 24px;">
                                    <i class="fa fa-download"></i> <?php echo __('Download Backup (JSON)'); ?>
                                </a>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:24px;text-align:center;">
                                <h3 style="font-weight:800;color:#0f172a;margin-top:0;"><i class="fa fa-upload text-info"></i> <?php echo __('Restore / Import Backup (.JSON)'); ?></h3>
                                <p style="color:#64748b;font-size:13px;margin-bottom:20px;">
                                    <?php echo __('Upload an existing JSON backup file to restore utility bills, meters, readings, and consumer records.'); ?>
                                </p>
                                <form action="<?php echo $this->Html->url(array('controller' => 'billing_migrations', 'action' => 'import_json')); ?>" method="post" enctype="multipart/form-data" style="display:inline-block;">
                                    <div class="form-group" style="margin-bottom:12px;">
                                        <input type="file" name="backup_file" accept=".json" class="form-control" required style="display:inline-block;width:auto;" />
                                    </div>
                                    <button type="submit" class="btn btn-info btn-lg" style="font-weight:800;padding:10px 24px;" onclick="return confirm('Restore database from uploaded JSON backup?');">
                                        <i class="fa fa-upload"></i> <?php echo __('Restore Backup'); ?>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php break;

        default: ?>
            <!-- ========================================== -->
            <!-- DEDICATED TAB: GENERAL SETTINGS            -->
            <!-- ========================================== -->
            <div class="subm-card-section">
                <div class="subm-card-title"><i class="fa fa-cog text-primary"></i> <?php echo __('Society Branding & Details (On Printed Bills)'); ?></div>
                <div class="subm-card-body">
                    <div class="form-group">
                        <label class="col-sm-2 control-label"><?php echo __('Society Name'); ?></label>
                        <div class="col-sm-8">
                            <input type="text" name="data[BillingSetting][subm_society_name]" value="<?php echo isset($settings['subm_society_name']) ? h($settings['subm_society_name']) : h($activeSociety['name']); ?>" class="form-control" style="font-weight:bold;" />
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label"><?php echo __('Society Code'); ?></label>
                        <div class="col-sm-4">
                            <input type="text" name="data[BillingSetting][subm_society_code]" value="<?php echo isset($settings['subm_society_code']) ? h($settings['subm_society_code']) : h($activeSociety['code']); ?>" class="form-control" />
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label"><?php echo __('Office Address'); ?></label>
                        <div class="col-sm-10">
                            <textarea name="data[BillingSetting][subm_society_address]" rows="2" class="form-control"><?php echo isset($settings['subm_society_address']) ? h($settings['subm_society_address']) : (!empty($activeSociety['address']) ? h($activeSociety['address']) : 'Awami Block White Villas Bahria Town Lahore, PAKISTAN'); ?></textarea>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label"><?php echo __('Helpline Phone'); ?></label>
                        <div class="col-sm-5">
                            <input type="text" name="data[BillingSetting][subm_society_phone]" value="<?php echo isset($settings['subm_society_phone']) ? h($settings['subm_society_phone']) : (!empty($activeSociety['phone']) ? h($activeSociety['phone']) : '+92339 1101156'); ?>" class="form-control" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Prefixes & Starting Numbers -->
            <div class="subm-card-section">
                <div class="subm-card-title"><i class="fa fa-list-ol text-info"></i> <?php echo __('Numbering Prefixes & Start Counters'); ?></div>
                <div class="subm-card-body">
                    <div class="form-group">
                        <label class="col-sm-2 control-label"><?php echo __('Currency Symbol'); ?></label>
                        <div class="col-sm-4">
                            <input type="text" name="data[BillingSetting][subm_currency]" value="<?php echo isset($settings['subm_currency']) ? h($settings['subm_currency']) : 'PKR'; ?>" class="form-control" style="font-weight:bold;" />
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label"><?php echo __('Consumer ID Prefix/Start'); ?></label>
                        <div class="col-sm-4">
                            <input type="text" name="data[BillingSetting][subm_prefix_con]" value="<?php echo isset($settings['subm_prefix_con']) ? h($settings['subm_prefix_con']) : 'WV'; ?>" class="form-control" placeholder="Prefix e.g. WV" />
                        </div>
                        <div class="col-sm-4">
                            <input type="number" name="data[BillingSetting][subm_start_con]" value="<?php echo isset($settings['subm_start_con']) ? h($settings['subm_start_con']) : '7006'; ?>" class="form-control" placeholder="Start e.g. 7006" />
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label"><?php echo __('Property Prefix/Start'); ?></label>
                        <div class="col-sm-4">
                            <input type="text" name="data[BillingSetting][subm_prefix_prop]" value="<?php echo isset($settings['subm_prefix_prop']) ? h($settings['subm_prefix_prop']) : 'WP'; ?>" class="form-control" placeholder="Prefix e.g. WP" />
                        </div>
                        <div class="col-sm-4">
                            <input type="number" name="data[BillingSetting][subm_start_prop]" value="<?php echo isset($settings['subm_start_prop']) ? h($settings['subm_start_prop']) : '8000'; ?>" class="form-control" placeholder="Start e.g. 8000" />
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label"><?php echo __('Bill Prefix/Start'); ?></label>
                        <div class="col-sm-4">
                            <input type="text" name="data[BillingSetting][subm_prefix_bill]" value="<?php echo isset($settings['subm_prefix_bill']) ? h($settings['subm_prefix_bill']) : 'WB'; ?>" class="form-control" placeholder="Prefix e.g. WB" />
                        </div>
                        <div class="col-sm-4">
                            <input type="number" name="data[BillingSetting][subm_start_bill]" value="<?php echo isset($settings['subm_start_bill']) ? h($settings['subm_start_bill']) : '9000'; ?>" class="form-control" placeholder="Start e.g. 9000" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Date Display Format -->
            <div class="subm-card-section">
                <div class="subm-card-title"><i class="fa fa-calendar text-warning"></i> <?php echo __('Date Display Format'); ?></div>
                <div class="subm-card-body">
                    <div class="form-group">
                        <label class="col-sm-2 control-label"><?php echo __('System Date Format'); ?></label>
                        <div class="col-sm-5">
                            <select name="data[BillingSetting][subm_date_format]" class="form-control">
                                <?php 
                                $currDf = isset($settings['subm_date_format']) ? $settings['subm_date_format'] : 'd-M-Y';
                                $dfOptions = array(
                                    'd-M-Y' => date('d-M-Y') . ' (d-M-Y)',
                                    'Y-m-d' => date('Y-m-d') . ' (Y-m-d)',
                                    'd/m/Y' => date('d/m/Y') . ' (d/m/Y)',
                                    'm/d/Y' => date('m/d/Y') . ' (m/d/Y)'
                                );
                                foreach($dfOptions as $dfKey => $dfLabel){ ?>
                                    <option value="<?php echo $dfKey; ?>" <?php if($currDf == $dfKey) echo 'selected'; ?>><?php echo $dfLabel; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table View & Line Borders -->
            <div class="subm-card-section">
                <div class="subm-card-title"><i class="fa fa-table text-primary"></i> <?php echo __('Table Line Borders (All Views)'); ?></div>
                <div class="subm-card-body">
                    <div class="form-group">
                        <label class="col-sm-2 control-label"><?php echo __('Show Table Lines'); ?></label>
                        <div class="col-sm-10">
                            <label style="margin-top:7px;font-weight:400;"><input type="checkbox" name="data[BillingSetting][subm_table_lines_enabled]" value="1" <?php if(!isset($settings['subm_table_lines_enabled']) || $settings['subm_table_lines_enabled'] == '1') echo 'checked'; ?> /> <?php echo __('Enable visible borders on plugin tables'); ?></label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label"><?php echo __('Table Line Color'); ?></label>
                        <div class="col-sm-3">
                            <div class="input-group">
                                <input type="color" name="data[BillingSetting][subm_table_line_color]" value="<?php echo isset($settings['subm_table_line_color']) ? h($settings['subm_table_line_color']) : '#051f4d'; ?>" class="form-control" style="height:36px;padding:2px;" />
                                <span class="input-group-addon" style="font-weight:bold;"><?php echo isset($settings['subm_table_line_color']) ? h($settings['subm_table_line_color']) : '#051f4d'; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Print Header Block HTML -->
            <div class="subm-card-section">
                <div class="subm-card-title"><i class="fa fa-print text-danger"></i> <?php echo __('Print Header Block HTML (List Records)'); ?></div>
                <div class="subm-card-body">
                    <div class="form-group">
                        <label class="col-sm-2 control-label"><?php echo __('Enable Header'); ?></label>
                        <div class="col-sm-10">
                            <label style="margin-top:7px;font-weight:400;"><input type="checkbox" name="data[BillingSetting][subm_print_header_enabled]" value="1" <?php if(!isset($settings['subm_print_header_enabled']) || $settings['subm_print_header_enabled'] == '1') echo 'checked'; ?> /> <?php echo __('Show custom header on every table print/export view'); ?></label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label"><?php echo __('Header HTML'); ?></label>
                        <div class="col-sm-10">
                            <textarea name="data[BillingSetting][subm_print_header_html]" rows="4" class="form-control" style="font-family:monospace;font-size:11.5px;"><?php echo isset($settings['subm_print_header_html']) ? h($settings['subm_print_header_html']) : "<!-- Modern Plugin Header -->\n<div style=\"align-items:center;justify-content:space-between;padding:18px 22px;margin:0 0 16px;background:#ffffff;border-radius:14px;\">\n  <div><div style=\"font-size:20px;font-weight:700;\">{{society_name}}</div></div>\n</div>"; ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bill Notice & Urdu Warning Banners -->
            <div class="subm-card-section">
                <div class="subm-card-title"><i class="fa fa-exclamation-triangle text-warning"></i> <?php echo __('Bill Notice & Warning Banner (Urdu)'); ?></div>
                <div class="subm-card-body">
                    <div class="form-group">
                        <label class="col-sm-2 control-label"><?php echo __('Notice Banner Text (Urdu)'); ?></label>
                        <div class="col-sm-10">
                            <textarea name="data[BillingSetting][subm_bill_notice]" rows="3" class="form-control urdu-font" style="font-size:16px;direction:rtl;text-align:right;"><?php echo isset($settings['subm_bill_notice']) ? h($settings['subm_bill_notice']) : 'احتیاط: ادائیگی سے پہلے براہِ کرم بل کی تفصیلات کی تصدیق کر لیں۔ بل پر درج اکاؤنٹ اور کیو آر کوڈ کی معلومات استعمال کریں۔'; ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <?php break; 
    } ?>

    <!-- DEDICATED BOTTOM SAVE BUTTON -->
    <div class="subm-bottom-action-bar">
        <div style="font-size:13px;color:#64748b;font-weight:600;">
            <i class="fa fa-shield text-success"></i> <?php echo __('Changes apply immediately for Society'); ?> #<?php echo (int)$societyId; ?>
        </div>
        <button type="submit" class="btn btn-success btn-lg" style="font-weight:800;padding:10px 40px;border-radius:8px;">
            <i class="fa fa-save"></i> <?php echo __('Save Tab Settings'); ?>
        </button>
    </div>
    <?php echo $this->Form->end(); ?>

</div>

<script type="text/javascript">
document.addEventListener('DOMContentLoaded', function() {
    // Society Switcher
    var socElem = document.getElementById('societySwitcher');
    if (socElem) {
        socElem.addEventListener('change', function() {
            var socId = this.value;
            var currentTab = document.getElementById('activeTabInput').value || 'general';
            window.location.href = '?society_id=' + encodeURIComponent(socId) + '&tab=' + encodeURIComponent(currentTab);
        });
    }

    // Add Slab Row
    var slabIndex = <?php echo count($areaSlabs); ?>;
    var addSlabBtn = document.getElementById('addSlabBtn');
    if (addSlabBtn) {
        addSlabBtn.addEventListener('click', function() {
            var container = document.getElementById('slabs_container');
            if (!container) return;
            var rowId = 'slabRow_' + slabIndex;

            var html = '<div class="dynamic-row-card" id="' + rowId + '">' +
                '<label>Meter Type</label>' +
                '<select name="slab_meter_type[]" class="form-control input-sm" style="width:110px;">' +
                    '<option value="electricity">Electricity</option>' +
                    '<option value="water">Water</option>' +
                    '<option value="gas">Gas</option>' +
                '</select>' +
                '<label>Type</label>' +
                '<select name="slab_property_type[]" class="form-control input-sm" style="width:110px;">' +
                    '<option value="">Any</option>' +
                    '<?php foreach($propertyTypes as $pt){ echo "<option value=\"" . h($pt) . "\">" . h($pt) . "</option>"; } ?>' +
                '</select>' +
                '<label>Min Area</label>' +
                '<input type="number" step="0.1" name="slab_area_min[]" value="" class="form-control input-sm" style="width:65px;" />' +
                '<label>Max Area</label>' +
                '<input type="number" step="0.1" name="slab_area_max[]" value="" class="form-control input-sm" style="width:65px;" />' +
                '<label>Min Units</label>' +
                '<input type="number" name="slab_min_units[]" value="0" class="form-control input-sm" style="width:70px;" />' +
                '<label>Max Units</label>' +
                '<input type="number" name="slab_max_units[]" value="100" class="form-control input-sm" style="width:70px;" />' +
                '<label>Rate (PKR)</label>' +
                '<input type="number" step="0.01" name="slab_rate[]" value="50" class="form-control input-sm" style="width:85px;" />' +
                '<button type="button" class="btn btn-xs btn-danger remove-slab-row" data-row="#' + rowId + '" style="margin-left:auto;"><span class="fa fa-trash"></span> Remove</button>' +
            '</div>';

            var temp = document.createElement('div');
            temp.innerHTML = html;
            container.appendChild(temp.firstChild);
            slabIndex++;
        });
    }

    // Add Maintenance Breakdown Head Row
    var mHeadIndex = <?php echo count($maintenanceHeads); ?>;
    var addMHeadBtn = document.getElementById('addMHeadBtn');
    if (addMHeadBtn) {
        addMHeadBtn.addEventListener('click', function() {
            var container = document.getElementById('m_heads_container');
            if (!container) return;
            var rowId = 'mHeadRow_' + mHeadIndex;

            var html = '<div class="dynamic-row-card" id="' + rowId + '">' +
                '<label>Item Name</label>' +
                '<input type="text" name="m_head_name[]" value="" placeholder="e.g. Security Guard" class="form-control input-sm" style="width:150px;font-weight:bold;" />' +
                '<label>Property Type</label>' +
                '<select name="m_head_property_type[]" class="form-control input-sm" style="width:120px;">' +
                    '<?php foreach($propertyTypes as $pt){ echo "<option value=\"" . h($pt) . "\">" . h($pt) . "</option>"; } ?>' +
                '</select>' +
                '<label>Amount (PKR)</label>' +
                '<input type="number" step="0.01" name="m_head_amount[]" value="500" class="form-control input-sm" style="width:90px;font-weight:bold;color:#0284c7;" />' +
                '<label>Min Area</label>' +
                '<input type="number" step="0.1" name="m_head_min_area[]" value="" class="form-control input-sm" style="width:65px;" />' +
                '<label>Max Area</label>' +
                '<input type="number" step="0.1" name="m_head_max_area[]" value="" class="form-control input-sm" style="width:65px;" />' +
                '<label>Unit</label>' +
                '<select name="m_head_unit[]" class="form-control input-sm" style="width:90px;">' +
                    '<?php foreach($areaUnits as $au){ echo "<option value=\"" . h($au) . "\">" . h($au) . "</option>"; } ?>' +
                '</select>' +
                '<button type="button" class="btn btn-xs btn-danger remove-m-head-row" data-row="#' + rowId + '" style="margin-left:auto;"><span class="fa fa-trash"></span> Remove</button>' +
            '</div>';

            var temp = document.createElement('div');
            temp.innerHTML = html;
            container.appendChild(temp.firstChild);
            mHeadIndex++;
        });
    }

    // Remove Maintenance Head Row
    document.addEventListener('click', function(e) {
        if(e.target && (e.target.classList.contains('remove-m-head-row') || e.target.closest('.remove-m-head-row'))) {
            var btn = e.target.classList.contains('remove-m-head-row') ? e.target : e.target.closest('.remove-m-head-row');
            var targetRow = document.querySelector(btn.getAttribute('data-row'));
            if(targetRow) targetRow.remove();
        }
    });

    // Remove Slab Row
    document.addEventListener('click', function(e) {
        if(e.target && (e.target.classList.contains('remove-slab-row') || e.target.closest('.remove-slab-row'))) {
            var btn = e.target.classList.contains('remove-slab-row') ? e.target : e.target.closest('.remove-slab-row');
            var targetRow = document.querySelector(btn.getAttribute('data-row'));
            if(targetRow) targetRow.remove();
        }
    });

    // Add Bank Account Row
    var bankIndex = <?php echo count($bankAccounts); ?>;
    var addBankBtn = document.getElementById('addBankRowBtn');
    if (addBankBtn) {
        addBankBtn.addEventListener('click', function() {
            var container = document.getElementById('bankAccountsContainer');
            if (!container) return;
            var rowId = 'bankRow_' + bankIndex;

            var html = '<div class="dynamic-row-card" id="' + rowId + '" style="display:block;">' +
                '<div class="row">' +
                    '<div class="col-md-3">' +
                        '<label>Bank Title</label>' +
                        '<input type="text" name="bank_accounts[' + bankIndex + '][title]" class="form-control" placeholder="e.g. Bank Account Title" />' +
                    '</div>' +
                    '<div class="col-md-5">' +
                        '<label>Bank Details (HTML)</label>' +
                        '<textarea name="bank_accounts[' + bankIndex + '][details]" rows="4" class="form-control" style="font-family:monospace;font-size:11.5px;" placeholder="<p><strong>Account Details</strong></p>"></textarea>' +
                    '</div>' +
                    '<div class="col-md-4">' +
                        '<label>QR Image URL</label>' +
                        '<input type="text" name="bank_accounts[' + bankIndex + '][qr]" class="form-control" placeholder="https://.../qr.jpg" />' +
                        '<div style="margin-top:8px;">' +
                            '<button type="button" class="btn btn-xs btn-danger remove-bank-row" data-row="#' + rowId + '"><span class="fa fa-trash"></span> Remove Bank</button>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
            '</div>';

            var temp = document.createElement('div');
            temp.innerHTML = html;
            container.appendChild(temp.firstChild);
            bankIndex++;
        });
    }

    // Remove Bank Row
    document.addEventListener('click', function(e) {
        if(e.target && (e.target.classList.contains('remove-bank-row') || e.target.closest('.remove-bank-row'))) {
            var btn = e.target.classList.contains('remove-bank-row') ? e.target : e.target.closest('.remove-bank-row');
            var targetRow = document.querySelector(btn.getAttribute('data-row'));
            if(targetRow) targetRow.remove();
        }
    });

    // Test SMS Dispatcher
    var testSmsBtn = document.getElementById('btnSendTestSms');
    if (testSmsBtn) {
        testSmsBtn.addEventListener('click', function() {
            var phone = (document.getElementById('testSmsPhone').value || '').trim();
            var msg = (document.getElementById('testSmsMessage').value || '').trim();
            var resultAlert = document.getElementById('testSmsResultAlert');

            if(!phone) {
                resultAlert.className = 'alert alert-danger';
                resultAlert.innerHTML = '<strong>Error:</strong> Please enter a test mobile number.';
                resultAlert.style.display = 'block';
                return;
            }

            testSmsBtn.disabled = true;
            testSmsBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Sending...';
            resultAlert.style.display = 'none';

            var formData = new FormData();
            formData.append('society_id', document.getElementById('societySwitcher') ? document.getElementById('societySwitcher').value : 1);
            formData.append('test_phone', phone);
            formData.append('test_message', msg);
            formData.append('use_system_gateway', document.getElementById('useSystemSmsGateway') && document.getElementById('useSystemSmsGateway').checked ? 1 : 0);
            formData.append('custom_url', document.getElementById('customSmsUrl') ? document.getElementById('customSmsUrl').value : '');
            formData.append('custom_token', document.getElementById('customSmsToken') ? document.getElementById('customSmsToken').value : '');
            formData.append('custom_sender', document.getElementById('customSmsSender') ? document.getElementById('customSmsSender').value : '');
            formData.append('custom_method', document.getElementById('customSmsMethod') ? document.getElementById('customSmsMethod').value : 'POST');

            fetch('<?php echo $this->Html->url(array('controller' => 'billing_settings', 'action' => 'test_sms')); ?>', {
                method: 'POST',
                body: formData
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                testSmsBtn.disabled = false;
                testSmsBtn.innerHTML = '<i class="fa fa-send"></i> Send Test SMS Now';
                if(data.status === 'success') {
                    resultAlert.className = 'alert alert-success';
                    resultAlert.innerHTML = '<strong><i class="fa fa-check-circle"></i> Success:</strong> ' + data.message + '<br/><small>Response: ' + data.response + '</small>';
                } else {
                    resultAlert.className = 'alert alert-danger';
                    resultAlert.innerHTML = '<strong><i class="fa fa-exclamation-circle"></i> Error:</strong> ' + data.message;
                }
                resultAlert.style.display = 'block';
            })
            .catch(function(err) {
                testSmsBtn.disabled = false;
                testSmsBtn.innerHTML = '<i class="fa fa-send"></i> Send Test SMS Now';
                resultAlert.className = 'alert alert-danger';
                resultAlert.innerHTML = '<strong><i class="fa fa-exclamation-circle"></i> Connection Error:</strong> ' + err.message;
                resultAlert.style.display = 'block';
            });
        });
    }
});
</script>
