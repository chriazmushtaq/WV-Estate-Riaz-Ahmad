<div class="page-title">
    <div class="title-env">
        <h1 class="title"><i class="fa fa-whatsapp text-success"></i> <?php echo __('Project WhatsApp & Portal Notification Settings'); ?></h1>
        <p class="description"><?php echo __('Configure project-based ON/OFF toggles, WhatsApp automation schedules, receipt delivery, and client portal notification bell preferences.'); ?></p>
    </div>
    <div class="breadcrumb-env">
        <ol class="breadcrumb bc-1">
            <li><a href="<?php echo $this->Html->url(array('controller' => 'Dashboards', 'action' => 'index')); ?>"><i class="fa-home"></i><?php echo __('Dashboard'); ?></a></li>
            <li><a href="<?php echo $this->Html->url(array('controller' => 'Notifications', 'action' => 'index')); ?>"><?php echo __('Notifications'); ?></a></li>
            <li class="active"><strong><?php echo __('Project WhatsApp Settings'); ?></strong></li>
        </ol>
    </div>
</div>

<?php echo $this->Session->flash(); ?>

<style type="text/css">
.pns-container {
    width: 100%;
    margin: 0;
    padding: 0;
}
.pns-selector-card {
    background: #07152a;
    color: #ffffff;
    border-radius: 12px;
    padding: 18px 24px;
    margin-bottom: 22px;
    box-shadow: 0 4px 16px rgba(7, 21, 42, 0.15);
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
}
.pns-selector-label {
    font-size: 16px;
    font-weight: 800;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
}
.pns-selector-dropdown {
    min-width: 280px;
    max-width: 380px;
}
.pns-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    box-shadow: 0 4px 16px rgba(15, 23, 42, 0.05);
    padding: 22px;
    margin-bottom: 24px;
}
.pns-card-header {
    border-bottom: 1px solid #edf2f7;
    padding-bottom: 14px;
    margin-bottom: 18px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.pns-card-title {
    font-size: 17px;
    font-weight: 800;
    color: #0f172a;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
}
.pns-toggle-box {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 14px 16px;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    transition: all 0.2s ease;
}
.pns-toggle-box:hover {
    border-color: #cbd5e1;
    background: #f1f5f9;
}
.pns-toggle-text {
    flex: 1 1 auto;
    padding-right: 18px;
}
.pns-toggle-label {
    font-size: 14px;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 3px;
    display: block;
}
.pns-toggle-desc {
    font-size: 12px;
    color: #64748b;
    margin: 0;
    line-height: 1.4;
}
.pns-switch-wrapper {
    flex: 0 0 auto;
}
/* Modern Toggle Switch */
.pns-switch {
    position: relative;
    display: inline-block;
    width: 48px;
    height: 26px;
    margin: 0;
}
.pns-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}
.pns-slider {
    position: absolute;
    cursor: pointer;
    top: 0; left: 0; right: 0; bottom: 0;
    background-color: #cbd5e1;
    transition: .3s;
    border-radius: 26px;
}
.pns-slider:before {
    position: absolute;
    content: "";
    height: 20px;
    width: 20px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: .3s;
    border-radius: 50%;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}
input:checked + .pns-slider {
    background-color: #10b981;
}
input:checked + .pns-slider:before {
    transform: translateX(22px);
}
.template-box {
    margin-bottom: 18px;
}
.template-label {
    font-weight: 700;
    font-size: 13px;
    color: #1e293b;
    margin-bottom: 6px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.template-tags {
    font-size: 11px;
    color: #0284c7;
    background: #e0f2fe;
    padding: 3px 8px;
    border-radius: 4px;
    font-weight: 600;
}
.template-textarea {
    font-family: Consolas, Menlo, Monaco, monospace;
    font-size: 13px;
    line-height: 1.5;
    background: #fafafa;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    padding: 12px;
}
.webhook-box {
    background: #f8fafc;
    border: 1px dashed #94a3b8;
    border-radius: 8px;
    padding: 12px 14px;
    font-family: monospace;
    font-size: 13px;
    color: #0f172a;
    word-break: break-all;
}
.btn-save-master {
    background: #10b981;
    color: #ffffff;
    font-weight: 800;
    font-size: 15px;
    padding: 12px 28px;
    border-radius: 8px;
    border: none;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25);
    transition: all 0.2s ease;
}
.btn-save-master:hover {
    background: #059669;
    color: #ffffff;
    transform: translateY(-1px);
}
</style>

<div class="pns-container">
    <!-- Project Selector Bar -->
    <div class="pns-selector-card">
        <div class="pns-selector-label">
            <i class="fa fa-building-o text-warning"></i>
            <span><?php echo __('Configure Settings For Project / Agency:'); ?></span>
        </div>
        <div class="pns-selector-dropdown">
            <select id="pnsProjectSelect" class="form-control" style="background:#ffffff; color:#0f172a; font-weight:700; border-radius:8px;">
                <?php foreach($projects as $pid => $pName): ?>
                <option value="<?php echo $pid; ?>" <?php echo ((int)$pid === (int)$projectId) ? 'selected="selected"' : ''; ?>>
                    <?php echo h($pName); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <?php echo $this->Form->create('ProjectNotificationSetting', array('class' => 'form-horizontal')); ?>

    <div class="row">
        <!-- Column 1: Feature Toggles & Gateway -->
        <div class="col-md-6">
            <div class="pns-card">
                <div class="pns-card-header">
                    <h3 class="pns-card-title"><i class="fa fa-toggle-on text-primary"></i> <?php echo __('Module ON/OFF Master Toggles'); ?></h3>
                    <span class="badge badge-info"><?php echo h(isset($projects[$projectId]) ? $projects[$projectId] : 'Project'); ?></span>
                </div>

                <!-- 1. In-Portal Notifications Bell -->
                <div class="pns-toggle-box">
                    <div class="pns-toggle-text">
                        <label class="pns-toggle-label"><i class="fa fa-bell text-warning"></i> <?php echo __('In-Portal Notification Bell & Activity Stream'); ?></label>
                        <p class="pns-toggle-desc"><?php echo __('Displays the live notification bell in the client header with unread badge counter and real-time activity dropdown for invoices, receipts, and letters.'); ?></p>
                    </div>
                    <div class="pns-switch-wrapper">
                        <label class="pns-switch">
                            <?php echo $this->Form->checkbox('in_portal_notifications', array('div' => false, 'hiddenField' => true, 'checked' => !empty($settings['in_portal_notifications']))); ?>
                            <span class="pns-slider"></span>
                        </label>
                    </div>
                </div>

                <!-- 2. WhatsApp Installment Reminders -->
                <div class="pns-toggle-box">
                    <div class="pns-toggle-text">
                        <label class="pns-toggle-label"><i class="fa fa-calendar-times-o text-danger"></i> <?php echo __('Automated WhatsApp Due Reminders (7-Day & 1-Day)'); ?></label>
                        <p class="pns-toggle-desc"><?php echo __('Automatically dispatches WhatsApp installment alerts 7 days before and 1 day before the due date with a direct statement pay link.'); ?></p>
                    </div>
                    <div class="pns-switch-wrapper">
                        <label class="pns-switch">
                            <?php echo $this->Form->checkbox('whatsapp_reminders', array('div' => false, 'hiddenField' => true, 'checked' => !empty($settings['whatsapp_reminders']))); ?>
                            <span class="pns-slider"></span>
                        </label>
                    </div>
                </div>

                <!-- 3. WhatsApp Payment Receipts -->
                <div class="pns-toggle-box">
                    <div class="pns-toggle-text">
                        <label class="pns-toggle-label"><i class="fa fa-check-circle text-success"></i> <?php echo __('Instant WhatsApp Payment Receipt Delivery'); ?></label>
                        <p class="pns-toggle-desc"><?php echo __('Immediately sends a verified payment acknowledgement on WhatsApp with receipt number, remaining balance, and downloadable receipt URL whenever a payment is registered.'); ?></p>
                    </div>
                    <div class="pns-switch-wrapper">
                        <label class="pns-switch">
                            <?php echo $this->Form->checkbox('whatsapp_receipts', array('div' => false, 'hiddenField' => true, 'checked' => !empty($settings['whatsapp_receipts']))); ?>
                            <span class="pns-slider"></span>
                        </label>
                    </div>
                </div>

                <!-- 4. WhatsApp Letters Notice -->
                <div class="pns-toggle-box">
                    <div class="pns-toggle-text">
                        <label class="pns-toggle-label"><i class="fa fa-file-text-o text-info"></i> <?php echo __('Immediate Notice on Official Letters (NOC, Allotment, Possession)'); ?></label>
                        <p class="pns-toggle-desc"><?php echo __('Sends an instant WhatsApp broadcast whenever an official real estate letter or document is issued in the portal.'); ?></p>
                    </div>
                    <div class="pns-switch-wrapper">
                        <label class="pns-switch">
                            <?php echo $this->Form->checkbox('whatsapp_letters', array('div' => false, 'hiddenField' => true, 'checked' => !empty($settings['whatsapp_letters']))); ?>
                            <span class="pns-slider"></span>
                        </label>
                    </div>
                </div>

                <!-- 5. Two-Way WhatsApp Bot -->
                <div class="pns-toggle-box">
                    <div class="pns-toggle-text">
                        <label class="pns-toggle-label"><i class="fa fa-comments-o text-success"></i> <?php echo __('Two-Way WhatsApp Bot Auto-Responder'); ?></label>
                        <p class="pns-toggle-desc"><?php echo __('Enables the intelligent bot to reply automatically to client queries for LEDGER, NEXT DUE, STATEMENT, BALLOT, or HELP.'); ?></p>
                    </div>
                    <div class="pns-switch-wrapper">
                        <label class="pns-switch">
                            <?php echo $this->Form->checkbox('whatsapp_bot', array('div' => false, 'hiddenField' => true, 'checked' => !empty($settings['whatsapp_bot']))); ?>
                            <span class="pns-slider"></span>
                        </label>
                    </div>
                </div>

                <!-- Dedicated Gateway Selection -->
                <div class="form-group" style="margin-top:20px; padding:0 15px;">
                    <label style="font-weight:700; color:#0f172a;"><?php echo __('Dedicated WhatsApp Sender Gateway:'); ?></label>
                    <?php echo $this->Form->select('whatsapp_gateway_id', $gateways, array('class' => 'form-control', 'empty' => false, 'value' => isset($settings['whatsapp_gateway_id']) ? $settings['whatsapp_gateway_id'] : 0)); ?>
                    <span class="help-block" style="font-size:12px; color:#64748b;"><?php echo __('Select whether to route alerts through the Master Agency number or a dedicated project line.'); ?></span>
                </div>

                <div class="form-group" style="padding:0 15px;">
                    <label style="font-weight:700; color:#0f172a;"><?php echo __('Reminder Schedule (Days Before Due Date):'); ?></label>
                    <?php echo $this->Form->input('reminder_days_before', array('type' => 'text', 'class' => 'form-control', 'label' => false, 'div' => false, 'value' => isset($settings['reminder_days_before']) ? $settings['reminder_days_before'] : '7,1', 'placeholder' => '7,1')); ?>
                    <span class="help-block" style="font-size:12px; color:#64748b;"><?php echo __('Comma-separated days before due date to trigger reminder (default: 7,1).'); ?></span>
                </div>
            </div>

            <!-- Two-Way Bot Webhook Info Card -->
            <div class="pns-card">
                <div class="pns-card-header">
                    <h3 class="pns-card-title"><i class="fa fa-plug text-primary"></i> <?php echo __('WhatsApp Cloud API Webhook Integration'); ?></h3>
                </div>
                <p style="font-size:13px; color:#475569;"><?php echo __('In your Meta Developer App (WhatsApp Configuration > Webhook), set your Callback URL to:'); ?></p>
                <div class="webhook-box">
                    <strong><?php echo h($webhookUrl); ?></strong>
                </div>
                <p style="font-size:12px; color:#64748b; margin-top:10px;">
                    <i class="fa fa-info-circle"></i> <?php echo __('Verify Token: Set your verify token to <strong>whitevillas_whatsapp_webhook</strong> or any secret token configured in your Meta App.'); ?>
                </p>
            </div>
        </div>

        <!-- Column 2: Notification & WhatsApp Message Templates -->
        <div class="col-md-6">
            <div class="pns-card">
                <div class="pns-card-header">
                    <h3 class="pns-card-title"><i class="fa fa-pencil-square-o text-success"></i> <?php echo __('WhatsApp & Notification Templates'); ?></h3>
                </div>

                <!-- Template 1: 7-Day Reminder -->
                <div class="template-box">
                    <div class="template-label">
                        <span><i class="fa fa-clock-o text-warning"></i> <?php echo __('7-Day Installment Reminder Template'); ?></span>
                        <span class="template-tags">{client_name}, {property_name}, {due_amount}, {due_date}, {pay_link}</span>
                    </div>
                    <?php echo $this->Form->textarea('template_reminder_7d', array('class' => 'form-control template-textarea', 'rows' => 4, 'value' => isset($settings['template_reminder_7d']) ? $settings['template_reminder_7d'] : '')); ?>
                </div>

                <!-- Template 2: 1-Day Urgent Reminder -->
                <div class="template-box">
                    <div class="template-label">
                        <span><i class="fa fa-exclamation-triangle text-danger"></i> <?php echo __('1-Day Urgent Due Tomorrow Template'); ?></span>
                        <span class="template-tags">{client_name}, {property_name}, {due_amount}, {due_date}, {pay_link}</span>
                    </div>
                    <?php echo $this->Form->textarea('template_reminder_1d', array('class' => 'form-control template-textarea', 'rows' => 4, 'value' => isset($settings['template_reminder_1d']) ? $settings['template_reminder_1d'] : '')); ?>
                </div>

                <!-- Template 3: Payment Receipt -->
                <div class="template-box">
                    <div class="template-label">
                        <span><i class="fa fa-check-circle text-success"></i> <?php echo __('Instant Payment Receipt Alert Template'); ?></span>
                        <span class="template-tags">{client_name}, {amount}, {receipt_no}, {paid_date}, {due_amount}, {receipt_link}</span>
                    </div>
                    <?php echo $this->Form->textarea('template_receipt', array('class' => 'form-control template-textarea', 'rows' => 4, 'value' => isset($settings['template_receipt']) ? $settings['template_receipt'] : '')); ?>
                </div>

                <!-- Template 4: Official Letter Issued -->
                <div class="template-box">
                    <div class="template-label">
                        <span><i class="fa fa-file-text-o text-info"></i> <?php echo __('Official Letter Issuance Alert Template'); ?></span>
                        <span class="template-tags">{client_name}, {letter_type}, {ref_no}, {issue_date}, {letter_link}</span>
                    </div>
                    <?php echo $this->Form->textarea('template_letter', array('class' => 'form-control template-textarea', 'rows' => 4, 'value' => isset($settings['template_letter']) ? $settings['template_letter'] : '')); ?>
                </div>

                <!-- Template 5: Bot Welcome / Help -->
                <div class="template-box">
                    <div class="template-label">
                        <span><i class="fa fa-comments text-primary"></i> <?php echo __('Two-Way Bot Welcome & Menu Template'); ?></span>
                        <span class="template-tags">{client_name}, {project_name}</span>
                    </div>
                    <?php echo $this->Form->textarea('template_bot_welcome', array('class' => 'form-control template-textarea', 'rows' => 4, 'value' => isset($settings['template_bot_welcome']) ? $settings['template_bot_welcome'] : '')); ?>
                </div>
            </div>

            <!-- Save Action Button -->
            <div style="text-align:right; margin-bottom:24px;">
                <button type="submit" class="btn btn-save-master">
                    <i class="fa fa-check"></i> <?php echo __('Save Project Notification Settings'); ?>
                </button>
            </div>
            <?php echo $this->Form->end(); ?>

            <!-- Test WhatsApp Message Box -->
            <div class="pns-card" style="background:#f0fdf4; border:1px solid #bbf7d0;">
                <div class="pns-card-header" style="border-color:#bbf7d0;">
                    <h3 class="pns-card-title text-success"><i class="fa fa-paper-plane text-success"></i> <?php echo __('Live WhatsApp Delivery Test'); ?></h3>
                </div>
                <?php echo $this->Form->create('TestWhatsapp', array('url' => array('action' => 'index', '?' => array('project_id' => $projectId)))); ?>
                <?php echo $this->Form->hidden('send_test', array('value' => 1)); ?>
                <div class="form-group" style="padding:0 15px;">
                    <label style="font-weight:700; color:#14532d;"><?php echo __('Recipient Mobile Number:'); ?></label>
                    <input type="text" name="data[TestWhatsapp][mobile]" class="form-control" placeholder="03XXXXXXXXX or 923XXXXXXXXX" required="required">
                </div>
                <div class="form-group" style="padding:0 15px;">
                    <label style="font-weight:700; color:#14532d;"><?php echo __('Test Message:'); ?></label>
                    <textarea name="data[TestWhatsapp][message]" class="form-control" rows="2">Hello! This is a live test from White Villas Automated Notification Engine.</textarea>
                </div>
                <div style="padding:0 15px;">
                    <button type="submit" class="btn btn-success" style="font-weight:700;">
                        <i class="fa fa-paper-plane"></i> <?php echo __('Send Test WhatsApp Now'); ?>
                    </button>
                </div>
                <?php echo $this->Form->end(); ?>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($){
    $('#pnsProjectSelect').on('change', function(){
        var pId = $(this).val();
        window.location = '<?php echo $this->Html->url(array("action" => "index")); ?>?project_id=' + encodeURIComponent(pId);
    });
});
</script>
