<div class="page-title">
    <div class="title-env">
        <h1 class="title"><i class="fa fa-calendar-check-o"></i> <?php echo __('Installment Calendar & Early Discount Settings'); ?></h1>
        <p class="description"><?php echo __('Configure client portal calendar sync alarms (.ics / Apple / Google / Outlook) and early lump-sum payment discount rules.'); ?></p>
    </div>
    <div class="breadcrumb-env">
        <ol class="breadcrumb bc-1">
            <li><a href="<?php echo $this->Html->url(array('controller' => 'Dashboards', 'action' => 'index')); ?>"><i class="fa-home"></i><?php echo __('Dashboard'); ?></a></li>
            <li><a href="#"><?php echo __('Settings'); ?></a></li>
            <li class="active"><strong><?php echo __('Installment Sync & Discount'); ?></strong></li>
        </ol>
    </div>
</div>

<?php echo $this->Session->flash(); ?>

<style type="text/css">
.installment-settings-container {
    width: 100%;
    margin: 0;
    padding: 0;
}
.installment-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    box-shadow: 0 4px 16px rgba(15, 23, 42, 0.05);
    padding: 22px;
    margin-bottom: 24px;
    height: 100%;
    display: flex;
    flex-direction: column;
}
.installment-card-header {
    border-bottom: 1px solid #edf2f7;
    padding-bottom: 14px;
    margin-bottom: 18px;
}
.installment-card-title {
    font-size: 17px;
    font-weight: 800;
    color: #0f172a;
    margin: 0 0 4px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.installment-card-desc {
    font-size: 13px;
    color: #64748b;
    margin: 0;
    line-height: 1.45;
}
.installment-card-body {
    flex: 1 1 auto;
}
.field-group {
    margin-bottom: 18px;
}
.field-label {
    display: block;
    font-weight: 700;
    font-size: 13px;
    color: #1e293b;
    margin-bottom: 6px;
}
.field-hint {
    display: block;
    font-size: 12px;
    color: #64748b;
    margin-top: 4px;
    line-height: 1.4;
}
.switch-box {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 12px 14px;
    margin-bottom: 18px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.switch-box label {
    margin: 0;
    font-weight: 700;
    color: #0f172a;
    cursor: pointer;
}
.feature-info-pill {
    background: #eff6ff;
    border-left: 4px solid #3b82f6;
    border-radius: 6px;
    padding: 10px 14px;
    margin-top: 14px;
    font-size: 12px;
    color: #1e40af;
    line-height: 1.5;
}
.urdu-textarea {
    font-family: "Jameel Noori Nastaleeq", "Noto Nastaliq Urdu", serif !important;
    font-size: 16px !important;
    line-height: 1.8 !important;
    direction: rtl !important;
}
.settings-bottom-bar {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    box-shadow: 0 4px 16px rgba(15, 23, 42, 0.05);
    padding: 16px 22px;
    margin-top: 6px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
}
</style>

<div class="installment-settings-container">
    <div class="panel panel-default" style="border-radius:12px; border:1px solid #e2e8f0; box-shadow:0 4px 16px rgba(15,23,42,.05); margin-bottom:20px;">
        <div class="panel-heading" style="padding:14px 20px; background:#fff; border-bottom:1px solid #edf2f7; border-radius:12px 12px 0 0;">
            <h3 class="panel-title" style="font-weight:800; font-size:16px; color:#0f172a;">
                <i class="fa fa-sliders text-primary"></i> <?php echo __('Portal Features & Discount Matrix'); ?>
            </h3>
            <div class="panel-options">
                <?php if(!empty($projects)){ ?>
                <form method="get" class="form-inline" style="display:inline-block;">
                    <label style="margin-right:6px; font-weight:700; font-size:13px; color:#334155;"><?php echo __('Project Scope:'); ?></label>
                    <select name="project_id" class="form-control input-sm" style="border-radius:6px; font-weight:600;" onchange="this.form.submit();">
                        <option value="0"><?php echo __('All Projects (Global Default)'); ?></option>
                        <?php foreach($projects as $pId => $pName){ ?>
                            <option value="<?php echo $pId; ?>" <?php echo ((int)$projectId === (int)$pId) ? 'selected' : ''; ?>><?php echo h($pName); ?></option>
                        <?php } ?>
                    </select>
                </form>
                <?php } ?>
            </div>
        </div>
    </div>

    <?php echo $this->Form->create('InstallmentSyncSetting', array('class' => 'form-vertical', 'role' => 'form')); ?>

    <!-- 2 FULL-WIDTH COLUMNS ROW -->
    <div class="row" style="margin-left:-10px; margin-right:-10px;">
        
        <!-- COLUMN 1: INSTALLMENT CALENDAR & SYNC -->
        <div class="col-xs-12 col-md-6 col-lg-6" style="padding-left:10px; padding-right:10px; margin-bottom:20px;">
            <div class="installment-card">
                <div class="installment-card-header">
                    <h4 class="installment-card-title">
                        <i class="fa fa-calendar text-primary"></i> <?php echo __('1. Installment Calendar & Sync'); ?>
                    </h4>
                    <p class="installment-card-desc">
                        <?php echo __('Export RFC 5545 .ics schedule with automated reminder alarms for Apple Calendar, Google Calendar, and Microsoft Outlook.'); ?>
                    </p>
                </div>

                <div class="installment-card-body">
                    <div class="switch-box">
                        <div>
                            <label for="InstallmentSyncSettingCalendarSyncEnabled"><?php echo __('Enable Calendar Sync on Portal:'); ?></label>
                            <span class="field-hint"><?php echo __('Shows "Sync Calendar" action on client and admin deal cards.'); ?></span>
                        </div>
                        <div>
                            <?php echo $this->Form->checkbox('calendar_sync_enabled', array('default' => 1, 'style' => 'width:20px; height:20px; cursor:pointer;')); ?>
                        </div>
                    </div>

                    <div class="field-group">
                        <label class="field-label"><?php echo __('Reminder Alarm Alerts (Days Before Due Date):'); ?></label>
                        <?php echo $this->Form->input('calendar_reminder_days', array(
                            'label' => false,
                            'class' => 'form-control input-lg',
                            'style' => 'border-radius:8px; font-weight:700;',
                            'placeholder' => '7,3,1',
                            'required' => true
                        )); ?>
                        <span class="field-hint"><i class="fa fa-info-circle"></i> <?php echo __('Enter comma-separated days (e.g. "7,3,1" triggers push notifications 7 days, 3 days, and 1 day before due date).'); ?></span>
                    </div>

                    <div class="field-group">
                        <label class="field-label"><?php echo __('Default Notification Time (24h format):'); ?></label>
                        <div class="input-group">
                            <span class="input-group-addon" style="border-radius:8px 0 0 8px; background:#f1f5f9;"><i class="fa fa-clock-o"></i></span>
                            <?php echo $this->Form->input('calendar_alarm_time', array(
                                'label' => false,
                                'class' => 'form-control input-lg',
                                'style' => 'border-radius:0 8px 8px 0; font-weight:700;',
                                'placeholder' => '09:00',
                                'type' => 'text'
                            )); ?>
                        </div>
                        <span class="field-hint"><?php echo __('Time of day when calendar reminder alarms fire (e.g. 09:00 for 9:00 AM).'); ?></span>
                    </div>

                    <div class="feature-info-pill">
                        <strong><i class="fa fa-check-circle"></i> <?php echo __('Cross-Platform Support:'); ?></strong>
                        <?php echo __('The generated .ics file includes title, due amounts, membership reference, and property name, recognized instantly by Apple iPhone / iPad, Mac, Android, Google Calendar, and Microsoft Outlook.'); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- COLUMN 2: EARLY PAYMENT DISCOUNT CALCULATOR -->
        <div class="col-xs-12 col-md-6 col-lg-6" style="padding-left:10px; padding-right:10px; margin-bottom:20px;">
            <div class="installment-card">
                <div class="installment-card-header">
                    <h4 class="installment-card-title">
                        <i class="fa fa-calculator text-success"></i> <?php echo __('2. Early Payment / Lump-Sum Discount'); ?>
                    </h4>
                    <p class="installment-card-desc">
                        <?php echo __('Configure the live settlement calculator to compute instant savings when clients pay advance installments.'); ?>
                    </p>
                </div>

                <div class="installment-card-body">
                    <div class="switch-box">
                        <div>
                            <label for="InstallmentSyncSettingEarlyPaymentCalculatorEnabled"><?php echo __('Enable Discount Calculator:'); ?></label>
                            <span class="field-hint"><?php echo __('Allows clients to simulate and request early payment discounts.'); ?></span>
                        </div>
                        <div>
                            <?php echo $this->Form->checkbox('early_payment_calculator_enabled', array('default' => 1, 'style' => 'width:20px; height:20px; cursor:pointer;')); ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-xs-12 col-sm-6">
                            <div class="field-group">
                                <label class="field-label"><?php echo __('Discount Calculation Mode:'); ?></label>
                                <?php echo $this->Form->input('early_payment_discount_mode', array(
                                    'label' => false,
                                    'class' => 'form-control input-lg',
                                    'style' => 'border-radius:8px; font-weight:600;',
                                    'type' => 'select',
                                    'options' => array(
                                        'percentage' => __('Flat Percentage (% on Advance)'),
                                        'annual_rate' => __('Per-Annum Pro-Rata Rebate (% p.a.)')
                                    )
                                )); ?>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-6">
                            <div class="field-group">
                                <label class="field-label"><?php echo __('Min. Advance Installments:'); ?></label>
                                <?php echo $this->Form->input('early_payment_min_installments', array(
                                    'label' => false,
                                    'class' => 'form-control input-lg',
                                    'style' => 'border-radius:8px; font-weight:700;',
                                    'type' => 'number',
                                    'min' => 1
                                )); ?>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-xs-12 col-sm-6">
                            <div class="field-group">
                                <label class="field-label"><?php echo __('Flat Discount Rate (%):'); ?></label>
                                <div class="input-group">
                                    <?php echo $this->Form->input('early_payment_discount_percent', array(
                                        'label' => false,
                                        'class' => 'form-control input-lg',
                                        'style' => 'border-radius:8px 0 0 8px; font-weight:700;',
                                        'step' => '0.01',
                                        'type' => 'number'
                                    )); ?>
                                    <span class="input-group-addon" style="border-radius:0 8px 8px 0; font-weight:700; background:#f1f5f9;">%</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-6">
                            <div class="field-group">
                                <label class="field-label"><?php echo __('Per-Annum Rate (% p.a.):'); ?></label>
                                <div class="input-group">
                                    <?php echo $this->Form->input('early_payment_annual_rate', array(
                                        'label' => false,
                                        'class' => 'form-control input-lg',
                                        'style' => 'border-radius:8px 0 0 8px; font-weight:700;',
                                        'step' => '0.01',
                                        'type' => 'number'
                                    )); ?>
                                    <span class="input-group-addon" style="border-radius:0 8px 8px 0; font-weight:700; background:#f1f5f9;">% p.a.</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-xs-12 col-sm-6">
                            <div class="switch-box" style="margin-bottom:14px;">
                                <div>
                                    <label for="InstallmentSyncSettingRequireAllOverdue"><?php echo __('Require All Overdue Clearance:'); ?></label>
                                    <span class="field-hint"><?php echo __('Clients must select 100% of overdue installments to get advance discounts.'); ?></span>
                                </div>
                                <div>
                                    <?php echo $this->Form->checkbox('require_all_overdue', array('default' => 1, 'style' => 'width:18px; height:18px; cursor:pointer;')); ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-6">
                            <div class="switch-box" style="margin-bottom:14px;">
                                <div>
                                    <label for="InstallmentSyncSettingAllowOverdueDiscountPackage"><?php echo __('Overdue Clearance Package:'); ?></label>
                                    <span class="field-hint"><?php echo __('Show "Full Overdue Settlement" button on portal modal.'); ?></span>
                                </div>
                                <div>
                                    <?php echo $this->Form->checkbox('allow_overdue_discount_package', array('default' => 1, 'style' => 'width:18px; height:18px; cursor:pointer;')); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-xs-12 col-sm-6">
                            <div class="field-group">
                                <label class="field-label"><?php echo __('Special Overdue Settlement Rebate (%):'); ?></label>
                                <div class="input-group">
                                    <?php echo $this->Form->input('overdue_special_discount_percent', array(
                                        'label' => false,
                                        'class' => 'form-control input-lg',
                                        'style' => 'border-radius:8px 0 0 8px; font-weight:700;',
                                        'step' => '0.01',
                                        'type' => 'number',
                                        'placeholder' => '0.00'
                                    )); ?>
                                    <span class="input-group-addon" style="border-radius:0 8px 8px 0; font-weight:700; background:#f1f5f9;">%</span>
                                </div>
                                <span class="field-hint"><?php echo __('Rebate/waiver applied ONLY when 100% of overdue installments are selected.'); ?></span>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-6">
                            <div class="feature-info-pill" style="margin-top:20px; background:#fffbeb; border-left-color:#f59e0b; color:#92400e;">
                                <strong><i class="fa fa-exclamation-triangle"></i> <?php echo __('Overdue Policy:'); ?></strong>
                                <?php echo __('By default, regular advance discount is only computed on upcoming future installments. Overdue installments carry 0% discount unless special overdue rebate is configured.'); ?>
                            </div>
                        </div>
                    </div>

                    <div class="field-group">
                        <label class="field-label"><?php echo __('Terms & Conditions (English):'); ?></label>
                        <?php echo $this->Form->input('early_payment_terms_en', array(
                            'label' => false,
                            'class' => 'form-control',
                            'style' => 'border-radius:8px; font-size:13px;',
                            'rows' => 2
                        )); ?>
                    </div>

                    <div class="field-group">
                        <label class="field-label"><?php echo __('Terms & Conditions (Urdu):'); ?></label>
                        <?php echo $this->Form->input('early_payment_terms_ur', array(
                            'label' => false,
                            'class' => 'form-control urdu-textarea',
                            'style' => 'border-radius:8px;',
                            'dir' => 'rtl',
                            'rows' => 2
                        )); ?>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- FULL-WIDTH BOTTOM ACTION BAR -->
    <div class="settings-bottom-bar">
        <div>
            <span style="font-weight:700; color:#334155; font-size:13px;">
                <i class="fa fa-shield text-success"></i> <?php echo __('Super Admin / CEO Authorized Settings'); ?>
            </span>
            <span class="text-muted" style="font-size:12px; margin-left:8px;">
                <?php echo __('Changes apply immediately across Client Portal & Admin Backoffice.'); ?>
            </span>
        </div>
        <div>
            <button type="submit" class="btn btn-success btn-lg" style="background:#16a34a; border-color:#16a34a; font-weight:800; padding:10px 32px; border-radius:8px; box-shadow:0 4px 12px rgba(22,163,74,.25);">
                <i class="fa fa-check-circle"></i> <?php echo __('Save All Settings'); ?>
            </button>
        </div>
    </div>

    <?php echo $this->Form->end(); ?>
</div>
