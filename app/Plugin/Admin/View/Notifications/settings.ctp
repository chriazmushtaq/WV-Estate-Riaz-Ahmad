<?php
$this->set('title_for_layout', __('Notification Settings & Toggles'));
$allSettings = isset($allSettings) ? $allSettings : array();

$isToggled = function($key) use ($allSettings) {
    if (isset($allSettings[$key])) {
        $val = trim((string)$allSettings[$key]['setting_value']);
        return ($val === '1' || strtolower($val) === 'true' || strtolower($val) === 'yes');
    }
    return true;
};

$getVal = function($key, $def = '') use ($allSettings) {
    return isset($allSettings[$key]) ? $allSettings[$key]['setting_value'] : $def;
};
?>
<style>
/* Modern iOS Style Toggle Switch */
.switch {
    position: relative;
    display: inline-block;
    width: 48px;
    height: 26px;
    margin-bottom: 0;
    vertical-align: middle;
}
.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}
.slider {
    position: absolute;
    cursor: pointer;
    top: 0; left: 0; right: 0; bottom: 0;
    background-color: #cbd5e1;
    transition: .3s cubic-bezier(0.4, 0, 0.2, 1);
    border-radius: 34px;
}
.slider:before {
    position: absolute;
    content: "";
    height: 20px;
    width: 20px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: .3s cubic-bezier(0.4, 0, 0.2, 1);
    border-radius: 50%;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}
input:checked + .slider {
    background-color: #10b981;
}
input:focus + .slider {
    box-shadow: 0 0 1px #10b981;
}
input:checked + .slider:before {
    transform: translateX(22px);
}
.setting-row {
    padding: 16px 20px;
    border-bottom: 1px solid #f1f5f9;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    transition: background .15s;
}
.setting-row:hover {
    background: #f8fafc;
}
.setting-row:last-child {
    border-bottom: none;
}
.setting-title {
    font-weight: 800;
    font-size: 14px;
    color: #0f172a;
    margin-bottom: 3px;
}
.setting-desc {
    font-size: 12.5px;
    color: #64748b;
    margin: 0;
    line-height: 1.4;
}
.setting-badge {
    font-size: 11px;
    font-weight: 700;
    padding: 3px 8px;
    border-radius: 6px;
    margin-left: 6px;
}
</style>

<div class="row">
    <div class="col-md-12">
        <!-- Prominent Gateway Hub Integration Banner -->
        <div style="background: linear-gradient(135deg, #0f172a, #1e293b); border-radius: 12px; padding: 18px 24px; margin-bottom: 20px; box-shadow: 0 4px 16px rgba(0,0,0,0.15); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px; border: 1px solid #334155;">
            <div style="display: flex; align-items: center; gap: 16px;">
                <div style="width: 48px; height: 48px; border-radius: 12px; background: linear-gradient(135deg, #25D366, #128C7E); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 24px; box-shadow: 0 4px 12px rgba(37,211,102,0.4);">
                    <i class="fa fa-whatsapp"></i>
                </div>
                <div>
                    <h4 style="margin: 0; font-weight: 800; font-size: 16px; color: #f8fafc;">
                        <?php echo __('LeadBot CRM WhatsApp & SMS Notification Gateway'); ?>
                        <span class="label label-success" style="font-size: 11px; margin-left: 8px;">INTEGRATED</span>
                    </h4>
                    <p style="margin: 4px 0 0; color: #94a3b8; font-size: 13px;">
                        <?php echo __('Configure isolated agency profiles, Android SMS SIM routing, event toggles, and live testing.'); ?>
                    </p>
                </div>
            </div>
            <div>
                <a href="<?php echo $this->Html->url(array('controller' => 'LeadbotGateways', 'action' => 'index')); ?>" class="btn btn-success" style="font-weight: 800; border-radius: 8px; padding: 10px 22px; box-shadow: 0 4px 12px rgba(37,211,102,0.3);">
                    <i class="fa fa-sliders"></i> <?php echo __('Open WhatsApp & SMS Gateway Hub'); ?> &rarr;
                </a>
            </div>
        </div>

        <?php echo $this->Form->create(false, array('url' => array('action' => 'settings'), 'id' => 'settingsForm')); ?>
        
        <div class="panel panel-default notif-panel">
            <div class="panel-heading" style="display: flex; align-items: center; justify-content: space-between; padding: 18px 22px; flex-wrap: wrap; gap: 12px;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="width: 44px; height: 44px; border-radius: 12px; background: linear-gradient(135deg, #0ea5e9, #0284c7); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 20px; box-shadow: 0 4px 12px rgba(14,165,233,0.35);">
                        <i class="fa fa-sliders"></i>
                    </div>
                    <div>
                        <h4 style="margin: 0; font-weight: 800; font-size: 18px; color: #0f172a;"><?php echo __('Notification Control Center'); ?></h4>
                        <span style="font-size: 12.5px; color: #64748b;"><?php echo __('Enable or disable notification triggers system-wide across all user roles'); ?></span>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <button type="submit" class="btn btn-primary" style="font-weight: 800; border-radius: 8px; padding: 8px 24px;">
                        <i class="fa fa-save"></i> <?php echo __('Save Settings Changes'); ?>
                    </button>
                </div>
            </div>

            <!-- Section 1: Leads & CRM Events -->
            <div style="background: #f8fafc; padding: 12px 20px; border-bottom: 1px solid #e2e8f0; border-top: 1px solid #e2e8f0; font-weight: 800; font-size: 13.5px; color: #334155; text-transform: uppercase; letter-spacing: 0.5px;">
                <i class="fa fa-users text-primary"></i> <?php echo __('Leads & CRM Notification Events'); ?>
            </div>
            <div class="setting-row">
                <div>
                    <div class="setting-title">
                        <?php echo __('New Lead Assignment'); ?>
                        <span class="setting-badge badge-primary"><?php echo __('Agents'); ?></span>
                    </div>
                    <p class="setting-desc"><?php echo __('Notify agents immediately when a new lead is assigned or transferred to them by managers or CRM.'); ?></p>
                </div>
                <label class="switch">
                    <input type="checkbox" name="NotificationSetting[enable_lead_assignment]" value="1" <?php echo $isToggled('enable_lead_assignment') ? 'checked' : ''; ?>>
                    <span class="slider"></span>
                </label>
            </div>

            <div class="setting-row">
                <div>
                    <div class="setting-title">
                        <?php echo __('Facebook Lead Auto-Sync Alert'); ?>
                        <span class="setting-badge badge-info"><?php echo __('Agents & CRM'); ?></span>
                    </div>
                    <p class="setting-desc"><?php echo __('Alert assigned agents and CRM team instantly when leads arrive from Facebook Instant Forms or webhook sync.'); ?></p>
                </div>
                <label class="switch">
                    <input type="checkbox" name="NotificationSetting[enable_facebook_sync]" value="1" <?php echo $isToggled('enable_facebook_sync') ? 'checked' : ''; ?>>
                    <span class="slider"></span>
                </label>
            </div>

            <div class="setting-row">
                <div>
                    <div class="setting-title">
                        <?php echo __('Upcoming Follow-up Reminders'); ?>
                        <span class="setting-badge badge-warning"><?php echo __('Agents'); ?></span>
                    </div>
                    <p class="setting-desc"><?php echo __('Dispatch advance reminder alerts before a client follow-up is scheduled (e.g. 30 minutes ahead).'); ?></p>
                </div>
                <label class="switch">
                    <input type="checkbox" name="NotificationSetting[enable_followup_reminder]" value="1" <?php echo $isToggled('enable_followup_reminder') ? 'checked' : ''; ?>>
                    <span class="slider"></span>
                </label>
            </div>

            <div class="setting-row">
                <div>
                    <div class="setting-title">
                        <?php echo __('Overdue Follow-up Warnings'); ?>
                        <span class="setting-badge badge-danger"><?php echo __('Agents & Managers'); ?></span>
                    </div>
                    <p class="setting-desc"><?php echo __('Alert agents and system managers when scheduled follow-ups have passed without conversation remarks.'); ?></p>
                </div>
                <label class="switch">
                    <input type="checkbox" name="NotificationSetting[enable_overdue_alert]" value="1" <?php echo $isToggled('enable_overdue_alert') ? 'checked' : ''; ?>>
                    <span class="slider"></span>
                </label>
            </div>

            <!-- Section 2: Sales & Financial Events -->
            <div style="background: #f8fafc; padding: 12px 20px; border-bottom: 1px solid #e2e8f0; border-top: 1px solid #e2e8f0; font-weight: 800; font-size: 13.5px; color: #334155; text-transform: uppercase; letter-spacing: 0.5px;">
                <i class="fa fa-money text-success"></i> <?php echo __('Sales, Deals & Financial Events'); ?>
            </div>
            <div class="setting-row">
                <div>
                    <div class="setting-title">
                        <?php echo __('Deal Closed / Booking Confirmed'); ?>
                        <span class="setting-badge badge-success"><?php echo __('CEO & Managers'); ?></span>
                    </div>
                    <p class="setting-desc"><?php echo __('Notify executive management and agency leaders whenever a deal is booked or successfully closed.'); ?></p>
                </div>
                <label class="switch">
                    <input type="checkbox" name="NotificationSetting[enable_deal_closed]" value="1" <?php echo $isToggled('enable_deal_closed') ? 'checked' : ''; ?>>
                    <span class="slider"></span>
                </label>
            </div>

            <div class="setting-row">
                <div>
                    <div class="setting-title">
                        <?php echo __('Expense Approval Requests'); ?>
                        <span class="setting-badge badge-warning"><?php echo __('CEO & Approvers'); ?></span>
                    </div>
                    <p class="setting-desc"><?php echo __('Notify approving managers and CEO when accountants create an expense request requiring approval.'); ?></p>
                </div>
                <label class="switch">
                    <input type="checkbox" name="NotificationSetting[enable_expense_approval]" value="1" <?php echo $isToggled('enable_expense_approval') ? 'checked' : ''; ?>>
                    <span class="slider"></span>
                </label>
            </div>

            <div class="setting-row">
                <div>
                    <div class="setting-title">
                        <?php echo __('Deal Installment Receipts Posted'); ?>
                        <span class="setting-badge badge-info"><?php echo __('Accountants'); ?></span>
                    </div>
                    <p class="setting-desc"><?php echo __('Notify accounting personnel when installments or booking fee receipts are posted on deals.'); ?></p>
                </div>
                <label class="switch">
                    <input type="checkbox" name="NotificationSetting[enable_payment_received]" value="1" <?php echo $isToggled('enable_payment_received') ? 'checked' : ''; ?>>
                    <span class="slider"></span>
                </label>
            </div>

            <div class="setting-row">
                <div>
                    <div class="setting-title">
                        <?php echo __('WhatsApp Overdue Demand Notice: Attach Demand Letter PDF'); ?>
                        <span class="setting-badge" style="background:#25d366;color:#ffffff;"><?php echo __('Overdue Invoices / Past Dues'); ?></span>
                    </div>
                    <p class="setting-desc"><?php echo __('When sending WhatsApp notifications from Overdue Invoices (Invpastdues), attach the official Demand Letter PDF with complete overdue installment breakdown. If toggled OFF, only send the overdue text message summary.'); ?></p>
                </div>
                <label class="switch">
                    <input type="checkbox" name="NotificationSetting[whatsapp_demand_pdf_attachment]" value="1" <?php echo $isToggled('whatsapp_demand_pdf_attachment') ? 'checked' : ''; ?>>
                    <span class="slider"></span>
                </label>
            </div>

            <div class="setting-row">
                <div>
                    <div class="setting-title">
                        <?php echo __('WhatsApp Payment Receipt: Attach Official Receipt PDF'); ?>
                        <span class="setting-badge" style="background:#25d366;color:#ffffff;"><?php echo __('Paid Invoices / Deals'); ?></span>
                    </div>
                    <p class="setting-desc"><?php echo __('When sending WhatsApp notifications from Paid Invoices (Invpaids), attach the official Payment Receipt PDF matching the exact print preview layout. If toggled OFF, send only the payment confirmation text summary.'); ?></p>
                </div>
                <label class="switch">
                    <input type="checkbox" name="NotificationSetting[whatsapp_receipt_pdf_attachment]" value="1" <?php echo $isToggled('whatsapp_receipt_pdf_attachment') ? 'checked' : ''; ?>>
                    <span class="slider"></span>
                </label>
            </div>

            <!-- Section 3: Admin Broadcasts & Client Preferences -->
            <div style="background: #f8fafc; padding: 12px 20px; border-bottom: 1px solid #e2e8f0; border-top: 1px solid #e2e8f0; font-weight: 800; font-size: 13.5px; color: #334155; text-transform: uppercase; letter-spacing: 0.5px;">
                <i class="fa fa-cogs text-purple"></i> <?php echo __('System Broadcasts & Audio Chimes'); ?>
            </div>
            <div class="setting-row">
                <div>
                    <div class="setting-title">
                        <?php echo __('Custom Admin Broadcasts'); ?>
                        <span class="setting-badge" style="background: #8b5cf6; color: #fff;"><?php echo __('All Users'); ?></span>
                    </div>
                    <p class="setting-desc"><?php echo __('Allow Super Admin to broadcast custom announcements and urgent notices across user levels.'); ?></p>
                </div>
                <label class="switch">
                    <input type="checkbox" name="NotificationSetting[enable_admin_broadcast]" value="1" <?php echo $isToggled('enable_admin_broadcast') ? 'checked' : ''; ?>>
                    <span class="slider"></span>
                </label>
            </div>

            <div class="setting-row">
                <div>
                    <div class="setting-title">
                        <?php echo __('Audio Alert Chime'); ?>
                        <button type="button" class="btn btn-xs btn-default" id="btnTestSound" style="margin-left: 8px; font-weight: 700; border-radius: 4px;">
                            <i class="fa fa-volume-up text-primary"></i> <?php echo __('Test Sound'); ?>
                        </button>
                    </div>
                    <p class="setting-desc"><?php echo __('Play a soft alert chime in the browser when a new notification drops in.'); ?></p>
                </div>
                <label class="switch">
                    <input type="checkbox" name="NotificationSetting[enable_sound_chime]" value="1" <?php echo $isToggled('enable_sound_chime') ? 'checked' : ''; ?>>
                    <span class="slider"></span>
                </label>
            </div>

            <div class="setting-row">
                <div>
                    <div class="setting-title">
                        <?php echo __('Browser Native OS Desktop Notifications'); ?>
                        <button type="button" class="btn btn-xs btn-default" id="btnTestDesktopNotif" style="margin-left: 8px; font-weight: 700; border-radius: 4px;">
                            <i class="fa fa-bell text-warning"></i> <?php echo __('Test Push Alert'); ?>
                        </button>
                    </div>
                    <p class="setting-desc"><?php echo __('Trigger native Windows / macOS / Mobile OS push banners even if the browser tab is minimized.'); ?></p>
                </div>
                <label class="switch">
                    <input type="checkbox" name="NotificationSetting[enable_browser_push]" value="1" <?php echo $isToggled('enable_browser_push') ? 'checked' : ''; ?>>
                    <span class="slider"></span>
                </label>
            </div>

            <!-- Follow-up Timing -->
            <div class="setting-row">
                <div>
                    <div class="setting-title"><?php echo __('Advance Follow-up Lead Time (Minutes)'); ?></div>
                    <p class="setting-desc"><?php echo __('How many minutes before a scheduled follow-up should the notification be delivered?'); ?></p>
                </div>
                <div style="width: 120px;">
                    <input type="number" name="NotificationSetting[followup_reminder_minutes]" class="form-control text-center" value="<?php echo h($getVal('followup_reminder_minutes', '30')); ?>" min="5" max="1440" style="border-radius: 8px; font-weight: 800;">
                </div>
            </div>

            <div class="panel-footer" style="background: #ffffff; padding: 16px 22px; border-top: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: flex-end;">
                <button type="submit" class="btn btn-primary" style="font-weight: 800; border-radius: 8px; padding: 8px 24px;">
                    <i class="fa fa-save"></i> <?php echo __('Save Settings Changes'); ?>
                </button>
            </div>
        </div>
        <?php echo $this->Form->end(); ?>

        <?php
        $cpanelPhpPath = '/usr/local/bin/php';
        $cliCronPath = str_replace('\\', '/', ROOT . DS . 'cron_notifications.php');
        $cliCronCmd = $cpanelPhpPath . ' -q ' . $cliCronPath . ' > /dev/null 2>&1';
        $webCronUrl = Router::url('/admin/notifications/cron_reminders', true);
        $webCronCmd = 'curl -s "' . $webCronUrl . '" > /dev/null 2>&1';

        $cliSyncPath = str_replace('\\', '/', ROOT . DS . 'sync_notifications.php');
        $cliSyncCmd = $cpanelPhpPath . ' -q ' . $cliSyncPath . ' > /dev/null 2>&1';
        $webSyncUrl = Router::url('/sync_notifications.php?run=1', true);
        $webSyncCmd = 'curl -s "' . $webSyncUrl . '" > /dev/null 2>&1';
        ?>

        <!-- Section: Automated cPanel Cron Job Details -->
        <div class="panel panel-default notif-panel" style="margin-top: 24px; border: 1px solid #bfdbfe; background: #f0f9ff; border-radius: 12px; box-shadow: 0 4px 16px rgba(14,165,233,0.08); overflow: hidden;">
            <div class="panel-heading" style="background: transparent; border-bottom: 1px solid #bae6fd; padding: 18px 22px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="width: 44px; height: 44px; border-radius: 12px; background: linear-gradient(135deg, #0284c7, #0369a1); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 20px; box-shadow: 0 4px 12px rgba(2,132,199,0.35);">
                        <i class="fa fa-clock-o"></i>
                    </div>
                    <div>
                        <h4 style="margin: 0; font-weight: 800; font-size: 17px; color: #0c4a6e;"><?php echo __('cPanel Cron Jobs & Automated Background Schedules'); ?></h4>
                        <span style="font-size: 12.5px; color: #0369a1;"><?php echo __('Auto-configured with your exact server paths and installed domain for zero-configuration setup.'); ?></span>
                    </div>
                </div>
            </div>
            <div class="panel-body" style="padding: 20px 22px;">
                <div style="margin-bottom: 20px;">
                    <h5 style="font-weight: 800; color: #0f172a; margin-top: 0; margin-bottom: 6px;">
                        <i class="fa fa-bell text-primary"></i> <?php echo __('1. Background Follow-up Reminders & Overdue Alerts (Every 5 Minutes)'); ?>
                    </h5>
                    <p style="font-size: 13px; color: #475569; margin-bottom: 12px;">
                        <?php echo __('Dispatches advance follow-up warnings (30 mins prior), detects overdue follow-ups, and auto-cleans old logs in the background.'); ?>
                    </p>

                    <!-- Option 1: Direct PHP CLI Command -->
                    <div style="margin-bottom: 12px;">
                        <label style="font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 4px;">
                            <?php echo __('Option 1: Direct PHP CLI Command (Recommended for cPanel)'); ?>
                        </label>
                        <div class="input-group">
                            <input type="text" id="cronCliCmd" class="form-control" value="<?php echo h($cliCronCmd); ?>" readonly style="background: #ffffff; font-family: monospace; font-size: 12px; font-weight: 700; color: #0f172a;">
                            <span class="input-group-btn">
                                <button class="btn btn-primary" type="button" onclick="copyNotifCmd('cronCliCmd', this)" style="font-weight: 700;">
                                    <i class="fa fa-copy"></i> <?php echo __('Copy CLI Command'); ?>
                                </button>
                            </span>
                        </div>
                    </div>

                    <!-- Option 2: cURL Web URL Command -->
                    <div>
                        <label style="font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 4px;">
                            <?php echo __('Option 2: cURL Web Cron Command'); ?>
                        </label>
                        <div class="input-group">
                            <input type="text" id="cronWebCmd" class="form-control" value="<?php echo h($webCronCmd); ?>" readonly style="background: #ffffff; font-family: monospace; font-size: 12px; font-weight: 700; color: #0f172a;">
                            <span class="input-group-btn">
                                <button class="btn btn-default" type="button" onclick="copyNotifCmd('cronWebCmd', this)" style="font-weight: 700;">
                                    <i class="fa fa-copy"></i> <?php echo __('Copy cURL Command'); ?>
                                </button>
                            </span>
                        </div>
                    </div>
                </div>

                <hr style="border-top: 1px solid #e0f2fe; margin: 20px 0;">

                <div style="margin-bottom: 8px;">
                    <h5 style="font-weight: 800; color: #0f172a; margin-top: 0; margin-bottom: 6px;">
                        <i class="fa fa-magic text-purple"></i> <?php echo __('2. One-Time Historical Event Backfill (CLI or Web URL)'); ?>
                    </h5>
                    <p style="font-size: 13px; color: #475569; margin-bottom: 12px;">
                        <?php echo __('If you prefer running the historical backfill via cPanel Terminal or browser URL rather than the button below:'); ?>
                    </p>

                    <!-- Option 1: CLI Backfill -->
                    <div style="margin-bottom: 12px;">
                        <label style="font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 4px;">
                            <?php echo __('Terminal / SSH Command:'); ?>
                        </label>
                        <div class="input-group">
                            <input type="text" id="syncCliCmd" class="form-control" value="<?php echo h($cliSyncCmd); ?>" readonly style="background: #ffffff; font-family: monospace; font-size: 12px; font-weight: 700; color: #0f172a;">
                            <span class="input-group-btn">
                                <button class="btn btn-primary" type="button" onclick="copyNotifCmd('syncCliCmd', this)" style="font-weight: 700;">
                                    <i class="fa fa-copy"></i> <?php echo __('Copy CLI Command'); ?>
                                </button>
                            </span>
                        </div>
                    </div>

                    <!-- Option 2: Web URL -->
                    <div>
                        <label style="font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 4px;">
                            <?php echo __('Browser Direct Sync URL:'); ?>
                        </label>
                        <div class="input-group">
                            <input type="text" id="syncWebUrl" class="form-control" value="<?php echo h($webSyncUrl); ?>" readonly style="background: #ffffff; font-family: monospace; font-size: 12px; font-weight: 700; color: #0f172a;">
                            <span class="input-group-btn">
                                <a href="<?php echo h($webSyncUrl); ?>" target="_blank" class="btn btn-default" style="font-weight: 700;">
                                    <i class="fa fa-external-link"></i> <?php echo __('Open in Browser'); ?>
                                </a>
                                <button class="btn btn-default" type="button" onclick="copyNotifCmd('syncWebUrl', this)" style="font-weight: 700;">
                                    <i class="fa fa-copy"></i> <?php echo __('Copy URL'); ?>
                                </button>
                            </span>
                        </div>
                    </div>
                </div>

                <div style="margin-top: 18px; padding: 12px 16px; background: #e0f2fe; border-radius: 8px; font-size: 12.5px; color: #0369a1; line-height: 1.5;">
                    <strong><i class="fa fa-lightbulb-o"></i> <?php echo __('How to add in cPanel Cron Jobs:'); ?></strong><br>
                    1. Log into your <strong>cPanel</strong> &rarr; Click <strong>Cron Jobs</strong>.<br>
                    2. Under <em>Common Settings</em>, choose <strong>"Once Per 5 Minutes (*/5 * * * *)"</strong>.<br>
                    3. In the <strong>Command</strong> box, paste the <strong>Option 1 (CLI Command)</strong> above and click <strong>Add New Cron Job</strong>.
                </div>
            </div>
        </div>

        <!-- Section: Auto-Generate Historical Events (Backfill) -->
        <div class="panel panel-default notif-panel" style="margin-top: 24px; border: 1px solid #e0e7ff; background: #faf5ff; border-radius: 12px; box-shadow: 0 4px 16px rgba(126,34,206,0.06); overflow: hidden;">
            <div class="panel-heading" style="background: transparent; border-bottom: 1px solid #e9d5ff; padding: 18px 22px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="width: 44px; height: 44px; border-radius: 12px; background: linear-gradient(135deg, #a855f7, #7e22ce); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 20px; box-shadow: 0 4px 12px rgba(168,85,247,0.35);">
                        <i class="fa fa-magic"></i>
                    </div>
                    <div>
                        <h4 style="margin: 0; font-weight: 800; font-size: 17px; color: #581c87;"><?php echo __('Historical Events Auto-Sync (Backfill)'); ?></h4>
                        <span style="font-size: 12.5px; color: #7e22ce;"><?php echo __('Auto-generate notifications for all your already existing leads, scheduled follow-ups, won deals, and expense requests.'); ?></span>
                    </div>
                </div>
                <div>
                    <button type="button" class="btn btn-primary" id="btnSyncExistingEvents" style="background: #7e22ce; border-color: #6b21a8; font-weight: 800; border-radius: 8px; padding: 9px 22px; box-shadow: 0 4px 12px rgba(126,34,206,0.35);">
                        <i class="fa fa-bolt"></i> <?php echo __('Auto-Generate Events Now'); ?>
                    </button>
                </div>
            </div>
            <div class="panel-body" style="padding: 16px 22px;">
                <div style="display: flex; align-items: flex-start; gap: 14px;">
                    <i class="fa fa-info-circle text-purple" style="font-size: 20px; margin-top: 2px; color: #7e22ce;"></i>
                    <div style="font-size: 13px; color: #4c1d95; line-height: 1.5;">
                        <strong><?php echo __('Zero Duplication Guarantee:'); ?></strong> <?php echo __('Each existing lead, deal, and follow-up is mapped with an idempotent cryptographic key. You can safely run this sync anytime—it will never generate duplicate notifications for already synced records.'); ?>
                    </div>
                </div>
                <div id="syncResultAlert" style="display: none; margin-top: 14px;"></div>
            </div>
        </div>

    </div>
</div>

<script type="text/javascript">
$(document).ready(function() {
    $('#btnTestSound').on('click', function(e) {
        e.preventDefault();
        if (window.WhiteVillasNotifications) {
            window.WhiteVillasNotifications.playChime();
        } else {
            alert('Audio chime loaded!');
        }
    });

    $('#btnTestDesktopNotif').on('click', function(e) {
        e.preventDefault();
        if (window.WhiteVillasNotifications) {
            window.WhiteVillasNotifications.requestPermissionAndTest();
        } else {
            if ("Notification" in window) {
                Notification.requestPermission().then(function(perm) {
                    if (perm === "granted") {
                        new Notification("White Villas Alert", {
                            body: "Test notification: Desktop OS notifications are working perfectly!",
                            icon: "/img/admin-notification-icon.png"
                        });
                    } else {
                        alert("Permission status: " + perm);
                    }
                });
            }
        }
    });

    $('#btnSyncExistingEvents').on('click', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var origHtml = $btn.html();
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> <?php echo __('Scanning & Syncing...'); ?>');
        $('#syncResultAlert').hide();

        $.ajax({
            url: '<?php echo $this->Html->url(array('action' => 'sync_existing_events')); ?>?ajax=1',
            type: 'GET',
            dataType: 'json',
            success: function(resp) {
                $btn.prop('disabled', false).html(origHtml);
                if (resp && resp.status === 'success') {
                    var s = resp.stats || {};
                    var details = ' (' + (s.leads_synced || 0) + ' Leads, ' + (s.followups_synced || 0) + ' Follow-ups, ' + (s.deals_synced || 0) + ' Deals, ' + (s.expenses_synced || 0) + ' Expenses)';
                    $('#syncResultAlert').html('<div class="alert alert-success" style="border-radius:8px; margin-bottom:0;"><i class="fa fa-check-circle"></i> ' + (resp.message || 'Successfully synced!') + '<br><small>' + details + '</small></div>').fadeIn();
                    if (window.WhiteVillasNotifications && typeof window.WhiteVillasNotifications.fetchLatest === 'function') {
                        window.WhiteVillasNotifications.fetchLatest();
                    }
                } else {
                    $('#syncResultAlert').html('<div class="alert alert-danger" style="border-radius:8px; margin-bottom:0;"><i class="fa fa-exclamation-circle"></i> ' + (resp.message || 'Error occurred.') + '</div>').fadeIn();
                }
            },
            error: function(xhr) {
                $btn.prop('disabled', false).html(origHtml);
                $('#syncResultAlert').html('<div class="alert alert-danger" style="border-radius:8px; margin-bottom:0;"><i class="fa fa-exclamation-circle"></i> Server communication error. Please try again.</div>').fadeIn();
            }
        });
    });
});

function copyNotifCmd(id, btn) {
    var input = document.getElementById(id);
    if (!input) return;
    input.select();
    input.setSelectionRange(0, 99999);
    try {
        navigator.clipboard.writeText(input.value);
    } catch (err) {
        document.execCommand("copy");
    }
    var $btn = $(btn);
    var origHtml = $btn.html();
    $btn.html('<i class="fa fa-check text-success"></i> <?php echo __('Copied!'); ?>');
    setTimeout(function() {
        $btn.html(origHtml);
    }, 2500);
}
</script>
