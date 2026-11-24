<?php
$this->set('title_for_layout', __('Deals & Financial Risk Management Settings'));
$canManage = isset($canManageDealFeatures) ? (bool)$canManageDealFeatures : true;
?>
<style>
.deal-settings-card {
    background: #ffffff;
    border: 1px solid #d8e2ee;
    border-radius: 10px;
    box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
    margin-bottom: 24px;
    overflow: hidden;
}
.deal-settings-header {
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
    color: #ffffff;
    padding: 18px 22px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.deal-settings-header h3 {
    margin: 0;
    font-size: 18px;
    font-weight: 700;
    color: #ffffff;
    display: flex;
    align-items: center;
    gap: 10px;
}
.deal-settings-body {
    padding: 24px;
    background: #f8fafc;
}
.deal-feature-item {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 18px 20px;
    margin-bottom: 16px;
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 20px;
    transition: border-color 0.2s, box-shadow 0.2s;
}
.deal-feature-item:hover {
    border-color: #cbd5e1;
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.04);
}
.deal-feature-icon {
    width: 44px;
    height: 44px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
}
.deal-feature-content {
    flex-grow: 1;
}
.deal-feature-content h4 {
    margin: 0 0 6px 0;
    font-size: 15px;
    font-weight: 700;
    color: #0f172a;
}
.deal-feature-content p {
    margin: 0;
    font-size: 13px;
    color: #64748b;
    line-height: 1.5;
}
.deal-feature-toggle {
    flex-shrink: 0;
    text-align: right;
    min-width: 140px;
}
.deal-badge-active {
    background: #ecfdf5;
    color: #065f46;
    border: 1px solid #a7f3d0;
    padding: 4px 8px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 700;
    display: inline-block;
    margin-top: 6px;
}
.deal-badge-inactive {
    background: #fef2f2;
    color: #991b1b;
    border: 1px solid #fecaca;
    padding: 4px 8px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 700;
    display: inline-block;
    margin-top: 6px;
}
</style>

<div class="page-title">
    <div class="title-env" style="display:flex;align-items:center;justify-content:space-between;width:100%;">
        <div>
            <h1 class="title" style="margin:0;"><i class="fa fa-shield text-primary"></i> <?php echo __('Deals & Financial Risk Management Settings');?></h1>
            <p class="description" style="margin-top:4px;color:#64748b;"><?php echo __('Configure centralized deal document storage, automated recovery risk badges, and cash flow forecasting.');?></p>
        </div>
        <div class="btn-group">
            <?php echo $this->Html->link('<i class="fa fa-line-chart"></i>&nbsp;'.__('Cash Flow Forecast'), array('controller'=>'Deals', 'action'=>'cashflow'), array('escape'=>false, 'class'=>'btn btn-info btn-sm')); ?>
            <?php echo $this->Html->link('<i class="fa fa-briefcase"></i>&nbsp;'.__('All Deals'), array('controller'=>'Deals', 'action'=>'index'), array('escape'=>false, 'class'=>'btn btn-default btn-sm')); ?>
            <?php echo $this->Html->link('<i class="fa fa-cogs"></i>&nbsp;'.__('General Settings'), array('controller'=>'Configurations', 'action'=>'index'), array('escape'=>false, 'class'=>'btn btn-default btn-sm')); ?>
        </div>
    </div>
</div>

<?php echo $this->Session->flash();?>

<div class="deal-settings-card">
    <div class="deal-settings-header">
        <h3><i class="fa fa-sliders"></i> <?php echo __('Enterprise Deal Engines & Permission Controls');?></h3>
        <span class="badge" style="background:rgba(255,255,255,0.15);color:#fff;font-weight:600;padding:6px 12px;"><?php echo __('Active System Controls');?></span>
    </div>

    <div class="deal-settings-body">
        <?php echo $this->Form->create('Configuration', array('url' => array('controller' => 'Configurations', 'action' => 'dealsettings'))); ?>

        <!-- Feature 1: Centralized Document Vault -->
        <div class="deal-feature-item">
            <div class="deal-feature-icon" style="background:#e0f2fe;color:#0369a1;">
                <i class="fa fa-folder-open"></i>
            </div>
            <div class="deal-feature-content">
                <h4><?php echo __('Centralized Deal Document Vault');?></h4>
                <p><?php echo __('Provides a dedicated "Document Vault" tab inside every deal ledger (`deals_payments/index/{id}`) where staff can securely upload and preview client CNIC/passports, signed booking forms, deposit slips, cheques, possession certificates, and allotment deeds.');?></p>
                <?php
                    $vaultVal = isset($this->request->data['Configuration']['deal_vault_enabled']) ? (int)$this->request->data['Configuration']['deal_vault_enabled'] : 1;
                ?>
                <span class="<?php echo $vaultVal ? 'deal-badge-active' : 'deal-badge-inactive'; ?>">
                    <i class="fa fa-circle"></i> <?php echo $vaultVal ? __('Module Active') : __('Module Disabled'); ?>
                </span>
            </div>
            <div class="deal-feature-toggle">
                <?php
                echo $this->Form->select('deal_vault_enabled', array('1' => __('Enabled (ON)'), '0' => __('Disabled (OFF)')), array('empty' => false, 'class' => 'form-control input-sm', 'style' => 'font-weight:700;'));
                ?>
            </div>
        </div>

        <!-- Feature 2: Deal Health & Recovery Risk Scoring -->
        <div class="deal-feature-item">
            <div class="deal-feature-icon" style="background:#fef3c7;color:#b45309;">
                <i class="fa fa-heartbeat"></i>
            </div>
            <div class="deal-feature-content">
                <h4><?php echo __('Deal Health & Recovery Risk Scoring Engine');?></h4>
                <p><?php echo __('Automatically audits scheduled installments against client payments using chronological FIFO matching. Labels each deal on the index with actionable badges: 🟢 Healthy (Up-to-date), 🟡 Attention (1–30d overdue), 🟠 Moderate (31–60d overdue), and 🔴 Critical (>60d overdue).');?></p>
                <?php
                    $riskVal = isset($this->request->data['Configuration']['deal_risk_score_enabled']) ? (int)$this->request->data['Configuration']['deal_risk_score_enabled'] : 1;
                ?>
                <span class="<?php echo $riskVal ? 'deal-badge-active' : 'deal-badge-inactive'; ?>">
                    <i class="fa fa-circle"></i> <?php echo $riskVal ? __('Module Active') : __('Module Disabled'); ?>
                </span>
            </div>
            <div class="deal-feature-toggle">
                <?php
                echo $this->Form->select('deal_risk_score_enabled', array('1' => __('Enabled (ON)'), '0' => __('Disabled (OFF)')), array('empty' => false, 'class' => 'form-control input-sm', 'style' => 'font-weight:700;'));
                ?>
            </div>
        </div>

        <!-- Feature 3: Executive Cash Flow Forecasting -->
        <div class="deal-feature-item">
            <div class="deal-feature-icon" style="background:#ecfdf5;color:#047857;">
                <i class="fa fa-line-chart"></i>
            </div>
            <div class="deal-feature-content">
                <h4><?php echo __('Executive Cash Flow & Inflow Forecast Dashboard');?></h4>
                <p><?php echo __('Enables the executive forecasting dashboard (`/admin/deals/cashflow`) displaying real-time projected collections across Next 30 Days, 31–60 Days, 61–90 Days, Beyond 90 Days, and Overdue pipeline with monthly visual timeline charts.');?></p>
                <?php
                    $forecastVal = isset($this->request->data['Configuration']['deal_cashflow_forecast_enabled']) ? (int)$this->request->data['Configuration']['deal_cashflow_forecast_enabled'] : 1;
                ?>
                <span class="<?php echo $forecastVal ? 'deal-badge-active' : 'deal-badge-inactive'; ?>">
                    <i class="fa fa-circle"></i> <?php echo $forecastVal ? __('Module Active') : __('Module Disabled'); ?>
                </span>
            </div>
            <div class="deal-feature-toggle">
                <?php
                echo $this->Form->select('deal_cashflow_forecast_enabled', array('1' => __('Enabled (ON)'), '0' => __('Disabled (OFF)')), array('empty' => false, 'class' => 'form-control input-sm', 'style' => 'font-weight:700;'));
                ?>
            </div>
        </div>

        <!-- Feature 4: Role-Based Admin Guard -->
        <div class="deal-feature-item" style="border-left: 4px solid #ef4444;background:#fffbfa;">
            <div class="deal-feature-icon" style="background:#fee2e2;color:#b91c1c;">
                <i class="fa fa-lock"></i>
            </div>
            <div class="deal-feature-content">
                <h4><?php echo __('Restrict Deal Features to Administrator & CEO Roles Only');?></h4>
                <p><?php echo __('When enabled, cash flow forecasting, document upload/deletion in the vault, and risk configuration settings are hidden from agents and general staff.');?></p>
            </div>
            <div class="deal-feature-toggle" style="padding-top:10px;">
                <label style="font-weight:700;color:#334155;cursor:pointer;">
                    <?php
                    echo $this->Form->checkbox('deal_features_admin_only', array('value' => 1, 'hiddenField' => true));
                    ?>
                    &nbsp;<?php echo __('Admin & CEO Only'); ?>
                </label>
            </div>
        </div>

        <!-- Submit Toolbar -->
        <div style="margin-top:24px;padding-top:16px;border-top:1px solid #e2e8f0;display:flex;align-items:center;justify-content:space-between;">
            <span class="text-muted" style="font-size:12px;">
                <i class="fa fa-info-circle"></i> <?php echo __('Changes take effect immediately across all user sessions.');?>
            </span>
            <div>
                <?php echo $this->Html->link('<i class="fa fa-refresh"></i>&nbsp;'.__('Reset'), array('controller' => 'Configurations', 'action' => 'dealsettings'), array('escape' => false, 'class' => 'btn btn-default btn-sm', 'style' => 'margin-right:8px;')); ?>
                <button type="submit" class="btn btn-success btn-sm" style="font-weight:700;padding-left:18px;padding-right:18px;">
                    <i class="fa fa-save"></i>&nbsp;<?php echo __('Save Settings');?>
                </button>
            </div>
        </div>

        <?php echo $this->Form->end(); ?>
    </div>
</div>
