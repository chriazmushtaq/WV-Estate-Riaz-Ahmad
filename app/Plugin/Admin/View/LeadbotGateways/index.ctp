<?php
$this->set('title_for_layout', __('LeadBot CRM - WhatsApp & SMS Notification Gateway'));

$currentSettings   = isset($currentSettings) ? $currentSettings : array();
$effectiveSettings = isset($effectiveSettings) ? $effectiveSettings : array();
$events            = isset($events) ? $events : array();
$telemetry         = isset($telemetry) ? $telemetry : array();
$pingStatus        = isset($pingStatus) ? $pingStatus : array();
$projectList       = isset($projectList) ? $projectList : array();
$agencyAgents      = isset($agencyAgents) ? $agencyAgents : array();
$recentLogs        = isset($recentLogs) ? $recentLogs : array();
$targetAgencyId    = isset($targetAgencyId) ? (int)$targetAgencyId : 0;
$targetUserId      = isset($targetUserId) ? (int)$targetUserId : 0;
$targetAgentUser   = isset($targetAgentUser) ? $targetAgentUser : null;
$isSuperAdmin      = !empty($isSuperAdmin);
$isLeadbotDefault  = !empty($isLeadbotDefault);
$activeTab         = isset($activeTab) ? $activeTab : 'overview';

$isConnected    = !empty($pingStatus['connected']);
$latency        = isset($pingStatus['latency_ms']) ? $pingStatus['latency_ms'] : 0;

$waSessions     = isset($telemetry['whatsapp_sessions']) && is_array($telemetry['whatsapp_sessions']) ? $telemetry['whatsapp_sessions'] : array();
$smsDevices     = isset($telemetry['sms_devices']) && is_array($telemetry['sms_devices']) ? $telemetry['sms_devices'] : array();

$selectedWaSession = isset($currentSettings['selected_wa_session']) ? $currentSettings['selected_wa_session'] : '';
$selectedSmsDevId  = isset($currentSettings['selected_sms_device_id']) ? (int)$currentSettings['selected_sms_device_id'] : 0;
$selectedSimSlot   = isset($currentSettings['selected_sms_sim_slot']) ? (int)$currentSettings['selected_sms_sim_slot'] : 1;

$gatewayUrl = !empty($currentSettings['gateway_url']) ? $currentSettings['gateway_url'] : (!empty($effectiveSettings['gateway_url']) ? $effectiveSettings['gateway_url'] : LeadbotGatewayClient::DEFAULT_LOCAL_URL);
$apiKey     = isset($currentSettings['api_key']) ? $currentSettings['api_key'] : '';
$isAgentMode = ($targetUserId > 0);
?>
<style>
/* Modern iOS Switch Controls */
.ios-switch {
    position: relative;
    display: inline-block;
    width: 44px;
    height: 24px;
    margin-bottom: 0;
    vertical-align: middle;
}
.ios-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}
.ios-slider {
    position: absolute;
    cursor: pointer;
    top: 0; left: 0; right: 0; bottom: 0;
    background-color: #cbd5e1;
    transition: .25s ease-in-out;
    border-radius: 24px;
}
.ios-slider:before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: .25s ease-in-out;
    border-radius: 50%;
    box-shadow: 0 2px 4px rgba(0,0,0,0.25);
}
input:checked + .ios-slider.slider-green {
    background-color: #10b981;
}
input:checked + .ios-slider.slider-amber {
    background-color: #f59e0b;
}
input:checked + .ios-slider.slider-blue {
    background-color: #3b82f6;
}
input:checked + .ios-slider:before {
    transform: translateX(20px);
}

/* Interactive Agent Channel Switch Buttons */
.btn-channel-switch {
    display: inline-flex;
    align-items: center;
    border: 0;
    border-radius: 999px;
    height: 24px;
    min-width: 52px;
    padding: 2px 8px 2px 4px;
    font-size: 10.5px;
    font-weight: 800;
    line-height: 1;
    cursor: pointer;
    outline: none;
    transition: all 0.22s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 1px 3px rgba(15, 23, 42, 0.15);
    user-select: none;
    text-decoration: none !important;
}
.btn-channel-switch:hover {
    transform: scale(1.06);
    box-shadow: 0 3px 8px rgba(15, 23, 42, 0.2);
}
.btn-channel-switch:focus {
    outline: none;
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.4);
}
.btn-channel-switch:active {
    transform: scale(0.97);
}

.btn-channel-switch .switch-knob {
    width: 16px;
    height: 16px;
    background-color: #ffffff;
    border-radius: 50%;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.25);
    transition: all 0.22s cubic-bezier(0.4, 0, 0.2, 1);
    display: inline-block;
    flex-shrink: 0;
}
.btn-channel-switch .switch-label {
    margin-left: 5px;
    letter-spacing: 0.3px;
    transition: all 0.15s ease;
}

/* ON State (WhatsApp) */
.btn-channel-switch.is-on.is-wa {
    background: #10b981;
    color: #ffffff;
}
.btn-channel-switch.is-on.is-wa:hover {
    background: #059669;
}

/* ON State (SMS) */
.btn-channel-switch.is-on.is-sms {
    background: #f59e0b;
    color: #ffffff;
}
.btn-channel-switch.is-on.is-sms:hover {
    background: #d97706;
}

/* OFF State */
.btn-channel-switch.is-off {
    background: #94a3b8;
    color: #ffffff;
    flex-direction: row-reverse;
    padding: 2px 4px 2px 8px;
}
.btn-channel-switch.is-off:hover {
    background: #64748b;
}
.btn-channel-switch.is-off .switch-label {
    margin-left: 0;
    margin-right: 5px;
}

/* Loading / Toggling state */
.btn-channel-switch.is-loading {
    opacity: 0.75;
    pointer-events: none;
    cursor: wait;
}
.btn-channel-switch.is-loading .switch-knob {
    animation: switchKnobPulse 0.6s infinite alternate;
}
@keyframes switchKnobPulse {
    from { opacity: 0.5; transform: scale(0.85); }
    to { opacity: 1; transform: scale(1.1); }
}

/* Gateway Credential Scope Dropdown in Agent Table */
.agent-cred-select-wrap {
    min-width: 180px;
    display: inline-block;
}
.js-agent-cred-mode-select {
    height: 28px !important;
    padding: 2px 8px !important;
    font-size: 11.5px !important;
    font-weight: 700 !important;
    border-radius: 6px !important;
    cursor: pointer !important;
    transition: all 0.2s ease !important;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.06) !important;
    line-height: 1.3 !important;
}
.js-agent-cred-mode-select.mode-agency {
    background-color: #f1f5f9 !important;
    color: #334155 !important;
    border: 1px solid #cbd5e1 !important;
}
.js-agent-cred-mode-select.mode-agency:hover {
    background-color: #e2e8f0 !important;
    border-color: #94a3b8 !important;
}
.js-agent-cred-mode-select.mode-isolated {
    background-color: #ecfdf5 !important;
    color: #065f46 !important;
    border: 1px solid #6ee7b7 !important;
}
.js-agent-cred-mode-select.mode-isolated:hover {
    background-color: #d1fae5 !important;
    border-color: #34d399 !important;
}
.js-agent-cred-mode-select:focus {
    outline: none !important;
    border-color: #3b82f6 !important;
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.25) !important;
}
.js-agent-cred-mode-select.is-loading {
    opacity: 0.65 !important;
    pointer-events: none !important;
    cursor: wait !important;
}

/* Card & Section Layout */
.lb-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 16px rgba(15, 23, 42, 0.06);
    border: 1px solid #e2e8f0;
    margin-bottom: 24px;
    overflow: hidden;
    transition: box-shadow .2s ease;
}
.lb-card:hover {
    box-shadow: 0 8px 24px rgba(15, 23, 42, 0.1);
}
.lb-card-header {
    padding: 16px 22px;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
}
.lb-card-body {
    padding: 22px;
}

/* Metrics Box */
.metric-box {
    display: flex;
    align-items: center;
    gap: 16px;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 16px;
    margin-bottom: 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.03);
}
.metric-icon {
    width: 48px;
    height: 48px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    color: #fff;
    flex-shrink: 0;
}
.metric-val {
    font-size: 22px;
    font-weight: 800;
    color: #0f172a;
    line-height: 1.1;
}
.metric-lbl {
    font-size: 12px;
    font-weight: 600;
    color: #64748b;
    margin-top: 4px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Navigation Tabs */
.lb-nav-tabs {
    border-bottom: 2px solid #e2e8f0;
    margin-bottom: 24px;
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
    padding-left: 0;
    list-style: none;
}
.lb-nav-tabs > li > a {
    border: none !important;
    border-radius: 8px 8px 0 0;
    color: #475569;
    font-weight: 700;
    font-size: 14px;
    padding: 12px 20px;
    display: flex;
    align-items: center;
    gap: 8px;
    background: #f1f5f9;
    transition: all .2s ease;
    text-decoration: none;
}
.lb-nav-tabs > li > a:hover {
    background: #e2e8f0;
    color: #0f172a;
}
.lb-nav-tabs > li.active > a, .lb-nav-tabs > li.active > a:focus, .lb-nav-tabs > li.active > a:hover {
    background: #0f172a !important;
    color: #ffffff !important;
    box-shadow: 0 -2px 10px rgba(15, 23, 42, 0.15);
}

/* Device Pill and Badges */
.badge-telemetry {
    font-size: 11px;
    padding: 4px 8px;
    border-radius: 6px;
    font-weight: 700;
}

/* Event Matrix Row */
.event-row {
    padding: 16px 20px;
    border-bottom: 1px solid #f1f5f9;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
    transition: background-color .15s ease;
}
.event-row:hover {
    background-color: #f8fafc;
}
.event-row:last-child {
    border-bottom: none;
}

/* Merge Tag Badges */
.tag-badge {
    display: inline-block;
    background: #e0e7ff;
    color: #3730a3;
    font-size: 11px;
    font-weight: 700;
    padding: 2px 7px;
    border-radius: 4px;
    cursor: pointer;
    margin: 2px;
    font-family: monospace;
    transition: background .15s;
}
.tag-badge:hover {
    background: #c7d2fe;
}

/* Event Category Pills & Sorting */
.event-category-pill {
    cursor: pointer;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
    border: 1px solid #cbd5e1;
    background: #fff;
    color: #475569;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    user-select: none;
}
.event-category-pill:hover {
    border-color: #0284c7;
    color: #0284c7;
    background: #f0f9ff;
}
.event-category-pill.active {
    background: #0f172a;
    border-color: #0f172a;
    color: #fff;
    box-shadow: 0 4px 10px rgba(15,23,42,0.25);
}
.event-category-pill.active .badge {
    background: rgba(255,255,255,0.25);
    color: #fff;
}
.event-item-wrap {
    border-bottom: 1px solid #e2e8f0;
    transition: background .15s;
}
.event-item-wrap:last-child {
    border-bottom: none;
}
.event-item-wrap:hover {
    background: #fafbfc;
}

/* Queue Channel & Status Pills */
.queue-channel-pill, .queue-status-pill {
    cursor: pointer;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 11.5px;
    font-weight: 700;
    border: 1px solid #cbd5e1;
    background: #fff;
    color: #475569;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    user-select: none;
}
.queue-channel-pill:hover, .queue-status-pill:hover {
    border-color: #0284c7;
    color: #0284c7;
    background: #f0f9ff;
}
.queue-channel-pill.active, .queue-status-pill.active {
    background: #0f172a;
    border-color: #0f172a;
    color: #fff;
    box-shadow: 0 4px 10px rgba(15,23,42,0.25);
}
.queue-channel-pill.active .badge, .queue-status-pill.active .badge {
    background: rgba(255,255,255,0.25);
    color: #fff;
}
.queue-row.selected-for-action {
    background-color: #fef9c3 !important;
}

/* Quick Toast Notification */
#wvLeadbotToast {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 99999;
    background: #0f172a;
    color: #fff;
    padding: 12px 20px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 700;
    box-shadow: 0 10px 25px rgba(0,0,0,0.3);
    display: none;
}
</style>

<div class="row">
    <!-- Top Breadcrumb & Gateway Hub Header -->
    <div class="col-md-12">
        <div class="lb-card" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #fff; border: none;">
            <div class="lb-card-body" style="padding: 26px 30px;">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
                    <div>
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <span style="background: #25D366; width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 24px; color: #fff; box-shadow: 0 4px 12px rgba(37,211,102,0.4);">
                                <i class="fa fa-whatsapp"></i>
                            </span>
                            <span style="background: #0284c7; width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 24px; color: #fff; box-shadow: 0 4px 12px rgba(2,132,199,0.4);">
                                <i class="fa fa-commenting"></i>
                            </span>
                            <div>
                                <h2 style="margin: 0; font-size: 24px; font-weight: 800; color: #fff; letter-spacing: -0.5px;">
                                    <?php echo __('LeadBot CRM — WhatsApp & SMS Gateway Hub'); ?>
                                </h2>
                                <span style="font-size: 13px; color: #94a3b8; font-weight: 500;">
                                    <?php echo __('Enterprise Automated Notification Engine, Agent API Key Isolation & Android Hardware Dispatcher'); ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Context Switchers: Agency & Agent -->
                    <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                        <?php if ($isSuperAdmin): ?>
                            <!-- Agency Context Switcher -->
                            <form method="get" action="<?php echo $this->Html->url(array('action' => 'index')); ?>" style="display: flex; align-items: center; gap: 6px; margin: 0;">
                                <input type="hidden" name="tab" value="<?php echo h($activeTab); ?>">
                                <input type="hidden" name="user_id" value="0">
                                <label style="margin: 0; font-size: 12px; font-weight: 700; color: #cbd5e1; white-space: nowrap;">
                                    <i class="fa fa-building-o text-warning"></i> <?php echo __('Agency:'); ?>
                                </label>
                                <select name="project_id" class="form-control input-sm" onchange="this.form.submit()" style="font-weight: 700; min-width: 190px; border-radius: 8px;">
                                    <option value="0" <?php echo ($targetAgencyId === 0) ? 'selected' : ''; ?>>
                                        🌐 <?php echo __('Platform Master / Global'); ?>
                                    </option>
                                    <?php foreach ($projectList as $pId => $pName): ?>
                                        <option value="<?php echo $pId; ?>" <?php echo ($targetAgencyId === (int)$pId) ? 'selected' : ''; ?>>
                                            🏢 <?php echo h($pName); ?> (#<?php echo $pId; ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                        <?php else: ?>
                            <div style="background: rgba(255,255,255,0.1); padding: 6px 12px; border-radius: 8px; font-weight: 700; font-size: 12px; color: #e2e8f0;">
                                <i class="fa fa-lock"></i> <?php echo __('Agency Isolated: #') . $targetAgencyId; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Agent Scope Selector (Every agent can use their own API key & hardware) -->
                        <form method="get" action="<?php echo $this->Html->url(array('action' => 'index')); ?>" style="display: flex; align-items: center; gap: 6px; margin: 0;">
                            <input type="hidden" name="tab" value="<?php echo h($activeTab); ?>">
                            <input type="hidden" name="project_id" value="<?php echo $targetAgencyId; ?>">
                            <label style="margin: 0; font-size: 12px; font-weight: 700; color: #cbd5e1; white-space: nowrap;">
                                <i class="fa fa-user-secret text-success"></i> <?php echo __('Agent Scope:'); ?>
                            </label>
                            <select name="user_id" class="form-control input-sm" onchange="this.form.submit()" style="font-weight: 700; min-width: 210px; border-radius: 8px;">
                                <option value="0" <?php echo ($targetUserId === 0) ? 'selected' : ''; ?>>
                                    🏢 <?php echo __('Agency Shared Gateway'); ?>
                                </option>
                                <?php if (!empty($agencyAgents)): ?>
                                    <optgroup label="<?php echo __('Individual Agent API Keys'); ?>">
                                        <?php foreach ($agencyAgents as $ag): 
                                            $uId = (int)$ag['User']['id'];
                                            $hasKey = !empty($ag['has_own_key']);
                                        ?>
                                            <option value="<?php echo $uId; ?>" <?php echo ($targetUserId === $uId) ? 'selected' : ''; ?>>
                                                👤 <?php echo h($ag['User']['name']); ?> (<?php echo $hasKey ? '🔑 Personal Key' : '📎 Agency Default'; ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                <?php endif; ?>
                            </select>
                        </form>

                        <button type="button" class="btn btn-default btn-sm" id="btnRefreshTelemetry" style="font-weight: 700; border-radius: 8px; padding: 6px 14px;">
                            <i class="fa fa-refresh" id="telemetrySpinIcon"></i> <?php echo __('Refresh'); ?>
                        </button>

                        <!-- Set LeadBot as Default Button -->
                        <button type="button" class="btn btn-sm <?php echo $isLeadbotDefault ? 'btn-success' : 'btn-warning'; ?>" id="btnToggleDefaultGateway" style="font-weight: 800; border-radius: 8px; padding: 6px 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.2); transition: all 0.2s ease;">
                            <i class="fa <?php echo $isLeadbotDefault ? 'fa-check-circle' : 'fa-star'; ?>" id="iconDefaultGateway"></i> 
                            <span id="textDefaultGateway"><?php echo $isLeadbotDefault ? __('LeadBot is Default (SMS & WA)') : __('Use LeadBot as Default (SMS & WA)'); ?></span>
                        </button>
                    </div>
                </div>

                <!-- System Default Messaging Status Banner -->
                <div id="bannerDefaultGateway" class="alert <?php echo $isLeadbotDefault ? 'alert-success' : 'alert-info'; ?>" style="margin-top: 16px; margin-bottom: 0; border-radius: 8px; padding: 10px 16px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; background: <?php echo $isLeadbotDefault ? 'rgba(16, 185, 129, 0.2)' : 'rgba(59, 130, 246, 0.15)'; ?>; border: 1px solid <?php echo $isLeadbotDefault ? 'rgba(16, 185, 129, 0.4)' : 'rgba(59, 130, 246, 0.3)'; ?>;">
                    <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                        <span class="badge" id="badgeDefaultGateway" style="background: <?php echo $isLeadbotDefault ? '#10b981' : '#64748b'; ?>; font-weight: 800; padding: 5px 10px; font-size: 11px; text-transform: uppercase;">
                            <i class="fa <?php echo $isLeadbotDefault ? 'fa-check-circle' : 'fa-info-circle'; ?>"></i> <?php echo $isLeadbotDefault ? __('DEFAULT SYSTEM ENGINE') : __('SECONDARY / CRM EVENTS ONLY'); ?>
                        </span>
                        <span id="descDefaultGateway" style="font-size: 13px; font-weight: 700; color: #fff;">
                            <?php echo $isLeadbotDefault ? __('LeadBot CRM is active as the DEFAULT gateway for all system SMS & WhatsApp messages.') : __('Custom HTTP SMS Gateway (/admin/Smssettings) is currently handling system SMS. Click to use LeadBot as default.'); ?>
                        </span>
                    </div>
                    <div>
                        <button type="button" class="btn btn-xs <?php echo $isLeadbotDefault ? 'btn-default' : 'btn-success'; ?>" id="btnToggleDefaultGatewayAlt" style="font-weight: 800; border-radius: 6px; padding: 4px 12px;">
                            <?php echo $isLeadbotDefault ? __('Switch to Custom SMS') : __('Set LeadBot as Default'); ?>
                        </button>
                    </div>
                </div>

                <!-- Scope Indicator Alert Bar -->
                <?php if ($isAgentMode && !empty($targetAgentUser)): ?>
                    <div style="margin-top: 18px; background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.35); border-radius: 8px; padding: 10px 16px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                        <div>
                            <span class="badge" style="background: #10b981; font-weight: 800; padding: 4px 8px; font-size: 11px;">
                                <i class="fa fa-key"></i> <?php echo __('AGENT PERSONAL GATEWAY'); ?>
                            </span>
                            <span style="margin-left: 8px; font-size: 13.5px; font-weight: 700; color: #e2e8f0;">
                                <?php echo __('Editing credentials for Agent:'); ?> <strong style="color: #fff;"><?php echo h($targetAgentUser['name']); ?></strong> (ID #<?php echo (int)$targetAgentUser['id']; ?>)
                            </span>
                        </div>
                        <div style="font-size: 12px; color: #cbd5e1;">
                            <?php echo __('Leads assigned to this agent will dispatch using their personal API key, WhatsApp session, and Android device.'); ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="col-md-12">
        <ul class="nav nav-tabs lb-nav-tabs" role="tablist">
            <li class="<?php echo ($activeTab === 'overview') ? 'active' : ''; ?>">
                <a href="#tab-overview" data-toggle="tab" role="tab"><i class="fa fa-dashboard text-primary"></i> <?php echo __('Gateway & Resources'); ?></a>
            </li>
            <li class="<?php echo ($activeTab === 'settings') ? 'active' : ''; ?>">
                <a href="#tab-settings" data-toggle="tab" role="tab"><i class="fa fa-cogs text-info"></i> <?php echo __('API & Device Assignment'); ?></a>
            </li>
            <li class="<?php echo ($activeTab === 'events') ? 'active' : ''; ?>">
                <a href="#tab-events" data-toggle="tab" role="tab"><i class="fa fa-toggle-on text-success"></i> <?php echo __('Event Notifications & Toggles'); ?></a>
            </li>
            <?php if (!empty($canManageQueue)): ?>
            <li class="<?php echo ($activeTab === 'queue') ? 'active' : ''; ?>">
                <a href="#tab-queue" data-toggle="tab" role="tab">
                    <i class="fa fa-clock-o text-warning"></i> <?php echo __('Outbox & Queue'); ?>
                    <?php if (!empty($queueCounts['total'])): ?>
                        <span class="badge" style="background: #f59e0b; color: #fff; font-size: 10px; font-weight: 800;" id="badgeQueueTotal">
                            <?php echo (int)$queueCounts['total']; ?>
                        </span>
                    <?php endif; ?>
                </a>
            </li>
            <?php endif; ?>
            <li class="<?php echo ($activeTab === 'test') ? 'active' : ''; ?>">
                <a href="#tab-test" data-toggle="tab" role="tab"><i class="fa fa-paper-plane text-warning"></i> <?php echo __('Interactive Test Center'); ?></a>
            </li>
            <li class="<?php echo ($activeTab === 'logs') ? 'active' : ''; ?>">
                <a href="#tab-logs" data-toggle="tab" role="tab"><i class="fa fa-history text-danger"></i> <?php echo __('Dispatch Audit Logs'); ?></a>
            </li>
        </ul>

        <div class="tab-content">
            <!-- TAB 1: OVERVIEW & RESOURCES -->
            <div class="tab-pane fade <?php echo ($activeTab === 'overview') ? 'active in' : ''; ?>" id="tab-overview">
                <!-- 4 Top Status Metric Boxes -->
                <div class="row">
                    <div class="col-md-3 col-sm-6">
                        <div class="metric-box">
                            <div class="metric-icon" style="background: <?php echo $isConnected ? '#10b981' : '#ef4444'; ?>;">
                                <i class="fa <?php echo $isConnected ? 'fa-check' : 'fa-times'; ?>"></i>
                            </div>
                            <div>
                                <div class="metric-val"><?php echo $isConnected ? __('Online') : __('Offline'); ?></div>
                                <div class="metric-lbl"><?php echo __('LeadBot CRM API'); ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="metric-box">
                            <div class="metric-icon" style="background: #3b82f6;">
                                <i class="fa fa-clock-o"></i>
                            </div>
                            <div>
                                <div class="metric-val"><?php echo $latency; ?> <small style="font-size: 13px;">ms</small></div>
                                <div class="metric-lbl"><?php echo __('Ping Latency'); ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="metric-box">
                            <div class="metric-icon" style="background: #25D366;">
                                <i class="fa fa-whatsapp"></i>
                            </div>
                            <div>
                                <div class="metric-val"><?php echo count($waSessions); ?></div>
                                <div class="metric-lbl"><?php echo __('WhatsApp Profiles'); ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="metric-box">
                            <div class="metric-icon" style="background: #f59e0b;">
                                <i class="fa fa-android"></i>
                            </div>
                            <div>
                                <div class="metric-val"><?php echo count($smsDevices); ?></div>
                                <div class="metric-lbl"><?php echo __('Android SMS Devices'); ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Hardware & Profile Resource Tables -->
                <div class="row">
                    <!-- WhatsApp Profiles Card -->
                    <div class="col-md-6">
                        <div class="lb-card">
                            <div class="lb-card-header">
                                <h4 style="margin: 0; font-weight: 800; font-size: 15px; color: #0f172a;">
                                    <i class="fa fa-whatsapp text-success"></i> <?php echo __('Discovered WhatsApp Sender Profiles'); ?>
                                </h4>
                                <span class="badge" style="background: #25D366; color: #fff; font-weight: 700;"><?php echo count($waSessions); ?> <?php echo __('Sessions'); ?></span>
                            </div>
                            <div class="lb-card-body" style="padding: 0;">
                                <?php if (!empty($waSessions)): ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover" style="margin: 0;">
                                            <thead>
                                                <tr style="background: #f8fafc; font-size: 12px; color: #64748b;">
                                                    <th><?php echo __('Profile / Session'); ?></th>
                                                    <th><?php echo __('Phone #'); ?></th>
                                                    <th><?php echo __('Engine Status'); ?></th>
                                                    <th class="text-right"><?php echo __('Assigned'); ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($waSessions as $ws): 
                                                    $isCurrentSelected = ($selectedWaSession === $ws['session_id']);
                                                    $isOnline = ($ws['status'] === 'CONNECTED');
                                                ?>
                                                    <tr>
                                                        <td>
                                                            <strong style="color: #0f172a; font-size: 13.5px;"><?php echo h($ws['display_name']); ?></strong>
                                                            <div style="font-size: 11px; color: #64748b;"><?php echo h($ws['session_id']); ?></div>
                                                        </td>
                                                        <td>
                                                            <?php if (!empty($ws['phone_number'])): ?>
                                                                <span class="label label-info" style="font-size: 11px;"><i class="fa fa-phone"></i> <?php echo h($ws['phone_number']); ?></span>
                                                            <?php else: ?>
                                                                <span class="text-muted">-</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <?php if ($isOnline): ?>
                                                                <span class="label label-success" style="font-size: 11px;"><i class="fa fa-check-circle"></i> CONNECTED</span>
                                                            <?php else: ?>
                                                                <span class="label label-default" style="font-size: 11px;"><?php echo h($ws['status']); ?></span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td class="text-right">
                                                            <?php if ($isCurrentSelected): ?>
                                                                <span class="badge" style="background: #0ea5e9; font-weight: 700;"><i class="fa fa-check"></i> <?php echo __('ACTIVE'); ?></span>
                                                            <?php else: ?>
                                                                <span class="text-muted" style="font-size: 12px;"><?php echo __('Standby'); ?></span>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div style="padding: 40px; text-align: center; color: #64748b;">
                                        <i class="fa fa-whatsapp" style="font-size: 40px; color: #cbd5e1; margin-bottom: 12px;"></i>
                                        <p style="margin: 0; font-weight: 700;"><?php echo __('No WhatsApp profiles discovered on gateway.'); ?></p>
                                        <small><?php echo __('Check gateway URL or create a session in LeadBot CRM.'); ?></small>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Available Android SMS Devices Card -->
                    <div class="col-md-6">
                        <div class="lb-card">
                            <div class="lb-card-header">
                                <h4 style="margin: 0; font-weight: 800; font-size: 15px; color: #0f172a;">
                                    <i class="fa fa-android text-warning"></i> <?php echo __('Connected Android SMS Devices & SIMs'); ?>
                                </h4>
                                <span class="badge badge-warning"><?php echo count($smsDevices); ?> <?php echo __('Devices'); ?></span>
                            </div>
                            <div class="lb-card-body" style="padding: 0;">
                                <?php if (!empty($smsDevices)): ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover" style="margin: 0;">
                                            <thead>
                                                <tr style="background: #f8fafc; font-size: 12px; color: #64748b;">
                                                    <th><?php echo __('Device & Model'); ?></th>
                                                    <th><?php echo __('Battery'); ?></th>
                                                    <th><?php echo __('SIM Slots & Carrier'); ?></th>
                                                    <th><?php echo __('Status'); ?></th>
                                                    <th class="text-right"><?php echo __('Assigned'); ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($smsDevices as $sd): 
                                                    $isCurrentDev = ($selectedSmsDevId === (int)$sd['id']);
                                                    $isDevOnline = !empty($sd['is_online']);
                                                    $battery = (int)$sd['battery_level'];
                                                ?>
                                                    <tr>
                                                        <td>
                                                            <strong style="color: #0f172a; font-size: 13.5px;"><?php echo h($sd['device_name']); ?></strong>
                                                            <?php if (!empty($sd['model_name'])): ?>
                                                                <div style="font-size: 11px; color: #64748b;"><?php echo h($sd['model_name']); ?></div>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <div style="display: flex; align-items: center; gap: 6px;">
                                                                <i class="fa <?php echo ($battery > 50) ? 'fa-battery-full text-success' : (($battery > 20) ? 'fa-battery-half text-warning' : 'fa-battery-empty text-danger'); ?>"></i>
                                                                <span style="font-weight: 700; font-size: 12px;"><?php echo $battery; ?>%</span>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div style="font-size: 12px;">
                                                                <span class="label label-info" style="font-size: 10.5px;">SIM 1: <?php echo !empty($sd['carrier_sim1']) ? h($sd['carrier_sim1']) : 'Active'; ?></span>
                                                                <?php if (!empty($sd['sim_count']) && $sd['sim_count'] > 1): ?>
                                                                    <span class="label label-default" style="font-size: 10.5px;">SIM 2: <?php echo !empty($sd['carrier_sim2']) ? h($sd['carrier_sim2']) : 'Secondary'; ?></span>
                                                                <?php endif; ?>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <?php if ($isDevOnline): ?>
                                                                <span class="label label-success" style="font-size: 11px;"><i class="fa fa-wifi"></i> ONLINE</span>
                                                            <?php else: ?>
                                                                <span class="label label-default" style="font-size: 11px;"><i class="fa fa-power-off"></i> STANDBY</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td class="text-right">
                                                            <?php if ($isCurrentDev): ?>
                                                                <span class="badge" style="background: #f59e0b; font-weight: 700;"><i class="fa fa-check"></i> <?php echo __('ACTIVE'); ?></span>
                                                            <?php else: ?>
                                                                <span class="text-muted" style="font-size: 12px;"><?php echo __('Available'); ?></span>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div style="padding: 40px; text-align: center; color: #64748b;">
                                        <i class="fa fa-mobile" style="font-size: 40px; color: #cbd5e1; margin-bottom: 12px;"></i>
                                        <p style="margin: 0; font-weight: 700;"><?php echo __('No Android SMS devices paired with gateway.'); ?></p>
                                        <small><?php echo __('Install the LeadBot Mobile Gateway APK on your Android device to start dispatching mobile SMS.'); ?></small>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Agency Agents Gateway Management Table -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="lb-card">
                            <div class="lb-card-header">
                                <div>
                                    <h4 style="margin: 0; font-weight: 800; font-size: 15px; color: #0f172a;">
                                        <i class="fa fa-users text-primary"></i> <?php echo __('Agency Agents — Personal Gateway & Hardware Status'); ?>
                                    </h4>
                                    <span style="font-size: 12.5px; color: #64748b;">
                                        <?php echo __('Every agent can have their own LeadBot API key, connected WhatsApp session, and Android device for their assigned leads.'); ?>
                                    </span>
                                </div>
                                <span class="badge" style="background: #3b82f6; font-weight: 700;"><?php echo count($agencyAgents); ?> <?php echo __('Agents'); ?></span>
                            </div>
                            <div class="lb-card-body" style="padding: 0;">
                                <?php if (!empty($agencyAgents)): ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover" style="margin: 0;">
                                            <thead>
                                                <tr style="background: #f8fafc; font-size: 12px; color: #64748b;">
                                                    <th><?php echo __('Agent Name & Mobile'); ?></th>
                                                    <th><?php echo __('Gateway Credentials'); ?></th>
                                                    <th><?php echo __('WhatsApp Profile'); ?></th>
                                                    <th><?php echo __('Android SMS Device'); ?></th>
                                                    <th><?php echo __('WhatsApp Auto'); ?></th>
                                                    <th><?php echo __('SMS Auto'); ?></th>
                                                    <th class="text-right"><?php echo __('Action'); ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($agencyAgents as $ag): 
                                                    $u = $ag['User'];
                                                    $st = $ag['gateway_setting'];
                                                    $hasOwnKey = !empty($ag['has_own_key']);
                                                    $isIsolated = !empty($ag['has_own_key']) || (!empty($ag['credential_mode']) && $ag['credential_mode'] === 'isolated');
                                                    $waOn = !empty($st) ? !empty($st['auto_send_whatsapp']) : !empty($currentSettings['auto_send_whatsapp']);
                                                    $smsOn = !empty($st) ? !empty($st['auto_send_sms']) : !empty($currentSettings['auto_send_sms']);
                                                ?>
                                                    <tr>
                                                        <td>
                                                            <strong style="color: #0f172a; font-size: 13.5px;"><?php echo h($u['name']); ?></strong>
                                                            <div style="font-size: 11.5px; color: #64748b;">
                                                                <i class="fa fa-phone"></i> <?php echo h(!empty($u['mobile']) ? $u['mobile'] : $u['phone']); ?> | ID: #<?php echo (int)$u['id']; ?>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="agent-cred-select-wrap">
                                                                <select class="form-control input-sm js-agent-cred-mode-select <?php echo $isIsolated ? 'mode-isolated' : 'mode-agency'; ?>"
                                                                        data-user-id="<?php echo (int)$u['id']; ?>"
                                                                        data-project-id="<?php echo !empty($u['project_id']) ? (int)$u['project_id'] : (int)$targetAgencyId; ?>"
                                                                        data-agent-name="<?php echo h($u['name']); ?>"
                                                                        data-has-key="<?php echo (!empty($st['api_key']) || !empty($st['personal_api_key'])) ? 1 : 0; ?>"
                                                                        title="<?php echo __('Change gateway credential scope: Inherit agency default or use dedicated isolated personal credentials'); ?>">
                                                                    <option value="agency" <?php echo !$isIsolated ? 'selected' : ''; ?>>
                                                                        &#128279; <?php echo __('Inherit Agency Default'); ?>
                                                                    </option>
                                                                    <option value="isolated" <?php echo $isIsolated ? 'selected' : ''; ?>>
                                                                        &#128273; <?php echo __('Agent Own (Isolated)'); ?>
                                                                    </option>
                                                                </select>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <?php if (!empty($st['selected_wa_session'])): ?>
                                                                <span class="label label-info" style="font-size: 11px;"><i class="fa fa-whatsapp"></i> <?php echo h($st['selected_wa_session']); ?></span>
                                                            <?php else: ?>
                                                                <span class="text-muted" style="font-size: 12px;"><?php echo __('Agency Profile'); ?></span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <?php if (!empty($st['selected_sms_device_id'])): ?>
                                                                <span class="label label-warning" style="font-size: 11px;"><i class="fa fa-android"></i> Device #<?php echo (int)$st['selected_sms_device_id']; ?> (SIM <?php echo (int)$st['selected_sms_sim_slot']; ?>)</span>
                                                            <?php else: ?>
                                                                <span class="text-muted" style="font-size: 12px;"><?php echo __('Agency Device'); ?></span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <button type="button" 
                                                                    class="js-agent-channel-toggle btn-channel-switch <?php echo $waOn ? 'is-on is-wa' : 'is-off'; ?>" 
                                                                    data-channel="whatsapp" 
                                                                    data-user-id="<?php echo (int)$u['id']; ?>" 
                                                                    data-project-id="<?php echo !empty($u['project_id']) ? (int)$u['project_id'] : (int)$targetAgencyId; ?>" 
                                                                    data-state="<?php echo $waOn ? 1 : 0; ?>"
                                                                    title="<?php echo $waOn ? __('Click to turn WhatsApp Auto OFF') : __('Click to turn WhatsApp Auto ON'); ?>">
                                                                <span class="switch-knob"></span>
                                                                <span class="switch-label"><?php echo $waOn ? 'ON' : 'OFF'; ?></span>
                                                            </button>
                                                        </td>
                                                        <td>
                                                            <button type="button" 
                                                                    class="js-agent-channel-toggle btn-channel-switch <?php echo $smsOn ? 'is-on is-sms' : 'is-off'; ?>" 
                                                                    data-channel="sms" 
                                                                    data-user-id="<?php echo (int)$u['id']; ?>" 
                                                                    data-project-id="<?php echo !empty($u['project_id']) ? (int)$u['project_id'] : (int)$targetAgencyId; ?>" 
                                                                    data-state="<?php echo $smsOn ? 1 : 0; ?>"
                                                                    title="<?php echo $smsOn ? __('Click to turn SMS Auto OFF') : __('Click to turn SMS Auto ON'); ?>">
                                                                <span class="switch-knob"></span>
                                                                <span class="switch-label"><?php echo $smsOn ? 'ON' : 'OFF'; ?></span>
                                                            </button>
                                                        </td>
                                                        <td class="text-right">
                                                            <a href="<?php echo $this->Html->url(array('action'=>'index','?'=>array('project_id'=>$targetAgencyId,'user_id'=>(int)$u['id'],'tab'=>'settings'))); ?>" class="btn btn-xs btn-primary" style="font-weight: 700; border-radius: 6px;">
                                                                <i class="fa fa-cog"></i> <?php echo __('Configure Gateway'); ?>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div style="padding: 30px; text-align: center; color: #64748b;">
                                        <p style="margin: 0;"><?php echo __('No agents registered in this agency.'); ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 2: API & DEVICE/PROFILE ASSIGNMENT -->
            <div class="tab-pane fade <?php echo ($activeTab === 'settings') ? 'active in' : ''; ?>" id="tab-settings">
                <?php echo $this->Form->create(false, array('url' => array('action' => 'save_settings'), 'id' => 'formGatewaySettings')); ?>
                <input type="hidden" name="project_id" value="<?php echo $targetAgencyId; ?>">
                <input type="hidden" name="user_id" value="<?php echo $targetUserId; ?>">
                <input type="hidden" name="LeadbotGatewaySetting[project_id]" value="<?php echo $targetAgencyId; ?>">
                <input type="hidden" name="LeadbotGatewaySetting[user_id]" value="<?php echo $targetUserId; ?>">

                <div class="row">
                    <!-- Column 1: API Gateway Credentials -->
                    <div class="col-md-6">
                        <div class="lb-card">
                            <div class="lb-card-header">
                                <h4 style="margin: 0; font-weight: 800; font-size: 15px; color: #0f172a;">
                                    <i class="fa fa-key text-primary"></i> 
                                    <?php if ($isAgentMode && !empty($targetAgentUser)): ?>
                                        <?php echo __('Agent Personal API Credentials — ') . h($targetAgentUser['name']); ?>
                                    <?php else: ?>
                                        <?php echo __('Agency Gateway Connection Settings'); ?>
                                    <?php endif; ?>
                                </h4>
                            </div>
                            <div class="lb-card-body">
                                <div class="form-group">
                                    <label style="font-weight: 800; color: #334155; font-size: 13px;">
                                        <?php echo __('Gateway Server Base URL:'); ?> <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="LeadbotGatewaySetting[gateway_url]" id="inputGatewayUrl" class="form-control" value="<?php echo h($gatewayUrl); ?>" placeholder="e.g. http://localhost/whatsapp or https://leadbotcrm.com" required style="font-weight: 600; border-radius: 8px;">
                                    <div style="margin-top: 8px; display: flex; gap: 8px; flex-wrap: wrap;">
                                        <button type="button" class="btn btn-xs btn-default" onclick="$('#inputGatewayUrl').val('http://localhost/whatsapp');" style="font-weight: 700;">
                                            <i class="fa fa-desktop text-info"></i> <?php echo __('Local Gateway (XAMPP)'); ?>
                                        </button>
                                        <button type="button" class="btn btn-xs btn-default" onclick="$('#inputGatewayUrl').val('https://leadbotcrm.com');" style="font-weight: 700;">
                                            <i class="fa fa-cloud text-success"></i> <?php echo __('Live Cloud Server (leadbotcrm.com)'); ?>
                                        </button>
                                    </div>
                                    <small class="text-muted" style="display: block; margin-top: 6px;">
                                        <?php echo __('The REST API server hosting the WhatsApp engine and Android SMS bridges.'); ?>
                                    </small>
                                </div>

                                <div class="form-group" style="margin-top: 20px;">
                                    <label style="font-weight: 800; color: #334155; font-size: 13px;">
                                        <?php if ($isAgentMode): ?>
                                            <?php echo __('Agent Personal API Secret Key (X-API-KEY):'); ?>
                                        <?php else: ?>
                                            <?php echo __('Agency API Secret Key (X-API-KEY):'); ?> <span class="text-danger">*</span>
                                        <?php endif; ?>
                                    </label>
                                    <div class="input-group">
                                        <input type="password" name="LeadbotGatewaySetting[api_key]" id="inputApiKey" class="form-control" value="<?php echo h($apiKey); ?>" placeholder="<?php echo $isAgentMode ? __('Enter agent personal key (or leave empty to inherit agency default)') : 'e.g. owa_live_...'; ?>" style="font-weight: 600; border-radius: 8px 0 0 8px; font-family: monospace;">
                                        <span class="input-group-btn">
                                            <button type="button" class="btn btn-default" id="btnToggleKeyVis" title="Show/Hide Key" style="border-radius: 0;">
                                                <i class="fa fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-info" id="btnTestConn" style="border-radius: 0 8px 8px 0; font-weight: 700;">
                                                <i class="fa fa-plug"></i> <?php echo __('Verify'); ?>
                                            </button>
                                        </span>
                                    </div>
                                    <div id="connTestResult" style="margin-top: 8px; font-weight: 700; font-size: 12.5px; display: none;"></div>
                                    <small class="text-muted" style="display: block; margin-top: 6px;">
                                        <?php if ($isAgentMode): ?>
                                            <?php echo __('Leave empty if this agent should use the shared agency gateway.'); ?>
                                        <?php else: ?>
                                            <?php echo __('Obtain from LeadBot CRM Developer API Keys panel for your agency.'); ?>
                                        <?php endif; ?>
                                    </small>
                                </div>

                                <hr style="margin: 22px 0;">

                                <!-- Master Toggles -->
                                <h5 style="font-weight: 800; font-size: 14px; color: #0f172a; margin-bottom: 16px;">
                                    <?php echo ($isAgentMode) ? __('Agent Channel Master Switches') : __('Agency Channel Master Switches'); ?>
                                </h5>
                                <div style="display: flex; flex-direction: column; gap: 14px;">
                                    <div style="display: flex; align-items: center; justify-content: space-between; background: #f8fafc; padding: 12px 16px; border-radius: 8px; border: 1px solid #e2e8f0;">
                                        <div>
                                            <strong style="font-size: 13.5px; color: #0f172a;"><i class="fa fa-whatsapp text-success"></i> <?php echo __('Automated WhatsApp Dispatch'); ?></strong>
                                            <div style="font-size: 12px; color: #64748b;"><?php echo __('Enable automatic WhatsApp notifications for this scope'); ?></div>
                                        </div>
                                        <label class="ios-switch">
                                            <input type="checkbox" class="js-master-toggle" data-channel="whatsapp" name="LeadbotGatewaySetting[auto_send_whatsapp]" value="1" <?php echo !empty($currentSettings['auto_send_whatsapp']) ? 'checked' : ''; ?>>
                                            <span class="ios-slider slider-green"></span>
                                        </label>
                                    </div>

                                    <div style="display: flex; align-items: center; justify-content: space-between; background: #f8fafc; padding: 12px 16px; border-radius: 8px; border: 1px solid #e2e8f0;">
                                        <div>
                                            <strong style="font-size: 13.5px; color: #0f172a;"><i class="fa fa-comment-o text-warning"></i> <?php echo __('Automated SMS Mobile Dispatch'); ?></strong>
                                            <div style="font-size: 12px; color: #64748b;"><?php echo __('Enable automatic mobile SMS carrier dispatch for this scope'); ?></div>
                                        </div>
                                        <label class="ios-switch">
                                            <input type="checkbox" class="js-master-toggle" data-channel="sms" name="LeadbotGatewaySetting[auto_send_sms]" value="1" <?php echo !empty($currentSettings['auto_send_sms']) ? 'checked' : ''; ?>>
                                            <span class="ios-slider slider-amber"></span>
                                        </label>
                                    </div>

                                    <!-- System Default Switch in Tab Settings -->
                                    <div style="display: flex; align-items: center; justify-content: space-between; background: #f0fdf4; padding: 12px 16px; border-radius: 8px; border: 1px solid #bbf7d0;">
                                        <div>
                                            <strong style="font-size: 13.5px; color: #166534;"><i class="fa fa-star text-warning"></i> <?php echo __('Use LeadBot as System Default (SMS & WA)'); ?></strong>
                                            <div style="font-size: 12px; color: #15803d;"><?php echo __('Route ALL CRM & transactional system SMS through LeadBot Android Gateway instead of custom HTTP API'); ?></div>
                                        </div>
                                        <label class="ios-switch">
                                            <input type="checkbox" id="chkLeadbotDefaultSetting" value="1" <?php echo $isLeadbotDefault ? 'checked' : ''; ?>>
                                            <span class="ios-slider slider-green"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Column 2: WhatsApp Profile & Android Device Selection -->
                    <div class="col-md-6">
                        <div class="lb-card">
                            <div class="lb-card-header">
                                <h4 style="margin: 0; font-weight: 800; font-size: 15px; color: #0f172a;">
                                    <i class="fa fa-sliders text-success"></i> <?php echo __('Profile & Hardware Routing Assignment'); ?>
                                </h4>
                            </div>
                            <div class="lb-card-body">
                                <!-- Profile Selection -->
                                <div class="form-group">
                                    <label style="font-weight: 800; color: #334155; font-size: 13px;">
                                        <i class="fa fa-whatsapp text-success"></i> <?php echo __('Assigned WhatsApp Profile (Sender Session):'); ?>
                                    </label>
                                    <select name="LeadbotGatewaySetting[selected_wa_session]" id="selectWaSession" class="form-control" style="font-weight: 700; border-radius: 8px;">
                                        <option value=""><?php echo __('-- Auto-Select First Online Profile --'); ?></option>
                                        <?php foreach ($waSessions as $ws): 
                                            $isSel = ($selectedWaSession === $ws['session_id']);
                                            $phoneStr = !empty($ws['phone_number']) ? ' (' . $ws['phone_number'] . ')' : '';
                                            $statStr  = ($ws['status'] === 'CONNECTED') ? '🟢 ONLINE' : '⚪ ' . $ws['status'];
                                        ?>
                                            <option value="<?php echo h($ws['session_id']); ?>" <?php echo $isSel ? 'selected' : ''; ?>>
                                                <?php echo h($ws['display_name']) . $phoneStr . ' - [' . $statStr . ']'; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input type="hidden" name="LeadbotGatewaySetting[selected_wa_profile_name]" id="hiddenWaProfileName" value="<?php echo h($currentSettings['selected_wa_profile_name']); ?>">
                                    <small class="text-muted" style="display: block; margin-top: 6px;">
                                        <?php echo __('Messages will be dispatched from this connected WhatsApp business or personal account.'); ?>
                                    </small>
                                </div>

                                <hr style="margin: 22px 0;">

                                <!-- Android Device Selection -->
                                <div class="form-group">
                                    <label style="font-weight: 800; color: #334155; font-size: 13px;">
                                        <i class="fa fa-android text-warning"></i> <?php echo __('Assigned Android Gateway Device:'); ?>
                                    </label>
                                    <select name="LeadbotGatewaySetting[selected_sms_device_id]" id="selectSmsDevice" class="form-control" style="font-weight: 700; border-radius: 8px;">
                                        <option value=""><?php echo __('-- Auto-Select Online Device --'); ?></option>
                                        <?php foreach ($smsDevices as $sd): 
                                            $isDevSel = ($selectedSmsDevId === (int)$sd['id']);
                                            $statStr  = !empty($sd['is_online']) ? '🟢 ONLINE (' . (int)$sd['battery_level'] . '%)' : '⚪ STANDBY';
                                        ?>
                                            <option value="<?php echo (int)$sd['id']; ?>" <?php echo $isDevSel ? 'selected' : ''; ?>>
                                                <?php echo h($sd['device_name']) . ' - [' . $statStr . ']'; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input type="hidden" name="LeadbotGatewaySetting[selected_sms_device_name]" id="hiddenSmsDeviceName" value="<?php echo h($currentSettings['selected_sms_device_name']); ?>">
                                    <small class="text-muted" style="display: block; margin-top: 6px;">
                                        <?php echo __('Physical Android smartphone executing automated mobile carrier SMS.'); ?>
                                    </small>
                                </div>

                                <!-- SIM Slot Selection -->
                                <div class="form-group" style="margin-top: 18px;">
                                    <label style="font-weight: 800; color: #334155; font-size: 13px;">
                                        <i class="fa fa-credit-card text-info"></i> <?php echo __('SIM Card Slot to Use for SMS Dispatch:'); ?>
                                    </label>
                                    <div style="display: flex; gap: 14px; margin-top: 6px;">
                                        <label style="font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                                            <input type="radio" name="LeadbotGatewaySetting[selected_sms_sim_slot]" value="1" <?php echo ($selectedSimSlot === 1) ? 'checked' : ''; ?>>
                                            <span>📱 SIM Slot 1 (Primary SIM)</span>
                                        </label>
                                        <label style="font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                                            <input type="radio" name="LeadbotGatewaySetting[selected_sms_sim_slot]" value="2" <?php echo ($selectedSimSlot === 2) ? 'checked' : ''; ?>>
                                            <span>📱 SIM Slot 2 (Secondary SIM)</span>
                                        </label>
                                    </div>
                                </div>

                                <hr style="margin: 22px 0;">

                                <div class="form-group">
                                    <label style="font-weight: 800; color: #334155; font-size: 13px;">
                                        <i class="fa fa-phone text-danger"></i> <?php echo __('Administration CC Mobile Number (Optional):'); ?>
                                    </label>
                                    <input type="text" name="LeadbotGatewaySetting[admin_mobile]" class="form-control" value="<?php echo h($currentSettings['admin_mobile']); ?>" placeholder="e.g. 03001234567 or +923001234567" style="font-weight: 600; border-radius: 8px;">
                                    <small class="text-muted" style="display: block; margin-top: 4px;">
                                        <?php echo __('If set, a blind copy (CC) of all critical client alerts will be dispatched to this number.'); ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 text-right" style="margin-bottom: 30px;">
                        <button type="submit" class="btn btn-primary" style="font-weight: 800; border-radius: 8px; padding: 12px 36px; font-size: 15px; box-shadow: 0 4px 14px rgba(2, 132, 199, 0.35);">
                            <i class="fa fa-save"></i> <?php echo __('Save Gateway & Hardware Routing'); ?>
                        </button>
                    </div>
                </div>
                <?php echo $this->Form->end(); ?>
            </div>

            <!-- TAB 3: EVENT NOTIFICATIONS & TOGGLES (19 Real Estate Triggers) -->
            <div class="tab-pane fade <?php echo ($activeTab === 'events') ? 'active in' : ''; ?>" id="tab-events">
                <?php echo $this->Form->create(false, array('url' => array('action' => 'save_events'), 'id' => 'formEventsSettings')); ?>
                <input type="hidden" name="project_id" value="<?php echo $targetAgencyId; ?>">
                <input type="hidden" name="user_id" value="<?php echo $targetUserId; ?>">

                <?php
                $catCounts = array('all' => count($events), 'contacts' => 0, 'leads' => 0, 'deals' => 0, 'finance' => 0, 'operations' => 0);
                foreach ($events as $evRow) {
                    $c = !empty($evRow['category']) ? $evRow['category'] : 'operations';
                    if (!isset($catCounts[$c])) {
                        $catCounts[$c] = 0;
                    }
                    $catCounts[$c]++;
                }
                ?>

                <div class="lb-card">
                    <div class="lb-card-header">
                        <div>
                            <h4 style="margin: 0; font-weight: 800; font-size: 16px; color: #0f172a;">
                                <i class="fa fa-bell-o text-primary"></i> <?php echo __('19 Automated Notification Events & Interactive Toggles'); ?>
                            </h4>
                            <span style="font-size: 13px; color: #64748b;">
                                <?php echo __('Sort, filter, and instantly toggle WhatsApp (WA) or SMS notifications for LeadBot CRM platform and Agency Admins.'); ?>
                            </span>
                        </div>
                        <button type="submit" class="btn btn-success" style="font-weight: 800; border-radius: 8px; padding: 8px 24px;">
                            <i class="fa fa-save"></i> <?php echo __('Save All Template Changes'); ?>
                        </button>
                    </div>

                    <!-- Interactive Sorting, Filtering & Search Controls Toolbar -->
                    <div style="background: #f8fafc; border-bottom: 2px solid #e2e8f0; padding: 16px 20px;">
                        <!-- Row 1: Search Box + Sort Dropdown + Scope Badge + Bulk Actions -->
                        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 14px;">
                            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap; flex: 1; max-width: 650px;">
                                <!-- Live Search Input -->
                                <div style="position: relative; flex: 1; min-width: 200px;">
                                    <i class="fa fa-search" style="position: absolute; left: 12px; top: 9px; color: #94a3b8; font-size: 13px;"></i>
                                    <input type="text" id="eventFilterSearch" class="form-control input-sm" placeholder="<?php echo __('Search events by name, keyword, or key...'); ?>" style="padding-left: 32px; border-radius: 8px; font-weight: 600; border-color: #cbd5e1;">
                                </div>

                                <!-- Sorting Selector -->
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <label style="margin: 0; font-size: 12px; font-weight: 700; color: #475569; white-space: nowrap;">
                                        <i class="fa fa-sort-amount-asc text-primary"></i> <?php echo __('Sort:'); ?>
                                    </label>
                                    <select id="eventSortSelect" class="form-control input-sm" style="font-weight: 700; border-radius: 8px; min-width: 220px; border-color: #cbd5e1;">
                                        <option value="workflow">📋 <?php echo __('Workflow / Category Order'); ?></option>
                                        <option value="name_asc">🔤 <?php echo __('Event Name (A → Z)'); ?></option>
                                        <option value="name_desc">🔤 <?php echo __('Event Name (Z → A)'); ?></option>
                                        <option value="wa_on">🟢 <?php echo __('WhatsApp Active First (ON)'); ?></option>
                                        <option value="wa_off">⚪ <?php echo __('WhatsApp Disabled (OFF)'); ?></option>
                                        <option value="sms_on">🟡 <?php echo __('SMS Active First (ON)'); ?></option>
                                        <option value="sms_off">⚪ <?php echo __('SMS Disabled (OFF)'); ?></option>
                                        <option value="category">📁 <?php echo __('Business Category Group'); ?></option>
                                    </select>
                                </div>
                            </div>

                            <!-- Scope Badge & Bulk Actions Dropdown -->
                            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                <!-- Scope Badge -->
                                <span class="badge" style="background: <?php echo ($targetAgencyId === 0 && $targetUserId === 0) ? '#0284c7' : '#059669'; ?>; padding: 6px 12px; font-size: 11.5px; font-weight: 700;">
                                    <?php if ($targetAgencyId === 0 && $targetUserId === 0): ?>
                                        🌐 <?php echo __('LeadBot CRM Platform Master'); ?>
                                    <?php elseif ($targetUserId > 0): ?>
                                        👤 <?php echo __('Agent Personal Override'); ?>
                                    <?php else: ?>
                                        🏢 <?php echo __('Agency #' . $targetAgencyId . ' Defaults'); ?>
                                    <?php endif; ?>
                                </span>

                                <!-- Bulk Toggle Quick Actions -->
                                <div class="btn-group">
                                    <button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown" aria-expanded="false" style="font-weight: 800; border-radius: 6px; padding: 6px 12px; border-color: #cbd5e1;">
                                        <i class="fa fa-sliders text-primary"></i> <?php echo __('Bulk Toggles'); ?> <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-right" role="menu" style="font-size: 12.5px; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.15); min-width: 220px;">
                                        <li class="dropdown-header" style="font-weight: 800; color: #15803d; text-transform: uppercase;">
                                            <i class="fa fa-whatsapp"></i> <?php echo __('WhatsApp (WA) Actions'); ?>
                                        </li>
                                        <li><a href="javascript:void(0);" class="btn-bulk-toggle" data-field="whatsapp_enabled" data-state="1"><i class="fa fa-check text-success"></i> <?php echo __('Turn All WA ON (Visible)'); ?></a></li>
                                        <li><a href="javascript:void(0);" class="btn-bulk-toggle" data-field="whatsapp_enabled" data-state="0"><i class="fa fa-times text-danger"></i> <?php echo __('Turn All WA OFF (Visible)'); ?></a></li>
                                        <li class="divider"></li>
                                        <li class="dropdown-header" style="font-weight: 800; color: #b45309; text-transform: uppercase;">
                                            <i class="fa fa-comment-o"></i> <?php echo __('SMS Actions'); ?>
                                        </li>
                                        <li><a href="javascript:void(0);" class="btn-bulk-toggle" data-field="sms_enabled" data-state="1"><i class="fa fa-check text-success"></i> <?php echo __('Turn All SMS ON (Visible)'); ?></a></li>
                                        <li><a href="javascript:void(0);" class="btn-bulk-toggle" data-field="sms_enabled" data-state="0"><i class="fa fa-times text-danger"></i> <?php echo __('Turn All SMS OFF (Visible)'); ?></a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Row 2: Category Filter Pills -->
                        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                            <span class="event-category-pill active" data-cat="all">
                                <i class="fa fa-th-large"></i> <?php echo __('All Events'); ?>
                                <span class="badge" style="font-size: 10px;"><?php echo $catCounts['all']; ?></span>
                            </span>
                            <span class="event-category-pill" data-cat="contacts">
                                <i class="fa fa-user-plus" style="color: #6366f1;"></i> <?php echo __('Contacts & Portal'); ?>
                                <span class="badge" style="font-size: 10px;"><?php echo isset($catCounts['contacts']) ? $catCounts['contacts'] : 0; ?></span>
                            </span>
                            <span class="event-category-pill" data-cat="leads">
                                <i class="fa fa-user-circle" style="color: #0284c7;"></i> <?php echo __('Leads & Inquiries'); ?>
                                <span class="badge" style="font-size: 10px;"><?php echo isset($catCounts['leads']) ? $catCounts['leads'] : 0; ?></span>
                            </span>
                            <span class="event-category-pill" data-cat="deals">
                                <i class="fa fa-handshake-o" style="color: #10b981;"></i> <?php echo __('Deals & Bookings'); ?>
                                <span class="badge" style="font-size: 10px;"><?php echo isset($catCounts['deals']) ? $catCounts['deals'] : 0; ?></span>
                            </span>
                            <span class="event-category-pill" data-cat="finance">
                                <i class="fa fa-money" style="color: #059669;"></i> <?php echo __('Payments & Accounts'); ?>
                                <span class="badge" style="font-size: 10px;"><?php echo isset($catCounts['finance']) ? $catCounts['finance'] : 0; ?></span>
                            </span>
                            <span class="event-category-pill" data-cat="operations">
                                <i class="fa fa-building" style="color: #ea580c;"></i> <?php echo __('Operations & Milestone'); ?>
                                <span class="badge" style="font-size: 10px;"><?php echo isset($catCounts['operations']) ? $catCounts['operations'] : 0; ?></span>
                            </span>

                            <span id="eventsFilterCounter" style="margin-left: auto; font-size: 12px; color: #64748b; font-weight: 600;">
                                <?php echo sprintf(__('Showing %d of %d events'), $catCounts['all'], $catCounts['all']); ?>
                            </span>
                        </div>
                    </div>

                    <!-- Events List Container -->
                    <div class="lb-card-body" id="eventsListContainer" style="padding: 0;">
                        <?php foreach ($events as $ek => $ev): 
                            $waChecked    = !empty($ev['whatsapp_enabled']);
                            $smsChecked   = !empty($ev['sms_enabled']);
                            $agentChecked = !empty($ev['notify_agent']);
                            $clientChecked = !empty($ev['notify_client']);
                            $category     = !empty($ev['category']) ? $ev['category'] : 'operations';
                            $sortOrder    = isset($ev['sort_order']) ? (int)$ev['sort_order'] : 999;
                            $catLabel     = !empty($ev['category_label']) ? $ev['category_label'] : 'CRM Event';
                            $iconClass    = !empty($ev['icon']) ? $ev['icon'] : 'fa-bell';
                            $badgeColor   = !empty($ev['color']) ? $ev['color'] : '#0284c7';
                        ?>
                            <div class="event-item-wrap" id="item-wrap-<?php echo $ek; ?>"
                                 data-event-key="<?php echo $ek; ?>"
                                 data-event-label="<?php echo h(strtolower($ev['event_label'])); ?>"
                                 data-desc="<?php echo h(strtolower($ev['event_description'])); ?>"
                                 data-category="<?php echo h($category); ?>"
                                 data-sort-order="<?php echo $sortOrder; ?>"
                                 data-wa="<?php echo $waChecked ? '1' : '0'; ?>"
                                 data-sms="<?php echo $smsChecked ? '1' : '0'; ?>">
                                <div class="event-row">
                                    <div style="flex: 1; min-width: 250px;">
                                        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                            <span class="badge" style="background: <?php echo $badgeColor; ?>; color: #fff; font-size: 10.5px; font-weight: 700; padding: 4px 8px;">
                                                <i class="fa <?php echo $iconClass; ?>"></i> <?php echo h($catLabel); ?>
                                            </span>
                                            <strong style="font-size: 14.5px; color: #0f172a;"><?php echo h($ev['event_label']); ?></strong>
                                            <span class="badge" style="background: #e2e8f0; color: #334155; font-size: 11px;"><?php echo h($ek); ?></span>
                                        </div>
                                        <p style="margin: 4px 0 0; font-size: 12.5px; color: #64748b; line-height: 1.4;">
                                            <?php echo h($ev['event_description']); ?>
                                        </p>
                                    </div>

                                    <!-- Dual Switches for WhatsApp & SMS with Instant AJAX Toggle -->
                                    <div style="display: flex; align-items: center; gap: 20px; flex-wrap: wrap;">
                                        <!-- WhatsApp Switch -->
                                        <div style="display: flex; align-items: center; gap: 6px;">
                                            <span style="font-size: 12px; font-weight: 800; color: #15803d; text-transform: uppercase;">
                                                <i class="fa fa-whatsapp"></i> WA
                                            </span>
                                            <label class="ios-switch">
                                                <input type="checkbox" class="js-event-toggle" data-event-key="<?php echo $ek; ?>" data-field="whatsapp_enabled" name="LeadbotEvent[<?php echo $ek; ?>][whatsapp_enabled]" value="1" <?php echo $waChecked ? 'checked' : ''; ?>>
                                                <span class="ios-slider slider-green"></span>
                                            </label>
                                        </div>

                                        <!-- SMS Switch -->
                                        <div style="display: flex; align-items: center; gap: 6px;">
                                            <span style="font-size: 12px; font-weight: 800; color: #b45309; text-transform: uppercase;">
                                                <i class="fa fa-comment-o"></i> SMS
                                            </span>
                                            <label class="ios-switch">
                                                <input type="checkbox" class="js-event-toggle" data-event-key="<?php echo $ek; ?>" data-field="sms_enabled" name="LeadbotEvent[<?php echo $ek; ?>][sms_enabled]" value="1" <?php echo $smsChecked ? 'checked' : ''; ?>>
                                                <span class="ios-slider slider-amber"></span>
                                            </label>
                                        </div>

                                        <!-- Notify Agent Switch -->
                                        <div style="display: flex; align-items: center; gap: 6px;">
                                            <span style="font-size: 11px; font-weight: 700; color: #475569;">Agent:</span>
                                            <label class="ios-switch">
                                                <input type="checkbox" class="js-event-toggle" data-event-key="<?php echo $ek; ?>" data-field="notify_agent" name="LeadbotEvent[<?php echo $ek; ?>][notify_agent]" value="1" <?php echo $agentChecked ? 'checked' : ''; ?>>
                                                <span class="ios-slider slider-blue"></span>
                                            </label>
                                        </div>

                                        <!-- Notify Client Switch -->
                                        <div style="display: flex; align-items: center; gap: 6px;">
                                            <span style="font-size: 11px; font-weight: 700; color: #475569;">Client:</span>
                                            <label class="ios-switch">
                                                <input type="checkbox" class="js-event-toggle" data-event-key="<?php echo $ek; ?>" data-field="notify_client" name="LeadbotEvent[<?php echo $ek; ?>][notify_client]" value="1" <?php echo $clientChecked ? 'checked' : ''; ?>>
                                                <span class="ios-slider slider-blue"></span>
                                            </label>
                                        </div>

                                        <!-- Template Editor Accordion Button -->
                                        <button type="button" class="btn btn-default btn-xs btn-toggle-tpl" data-target="#tpl-collapse-<?php echo $ek; ?>" style="font-weight: 700; border-radius: 6px; padding: 6px 12px;">
                                            <i class="fa fa-edit"></i> <?php echo __('Templates'); ?> <i class="fa fa-chevron-down"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Expandable Template Editor -->
                                <div class="collapse" id="tpl-collapse-<?php echo $ek; ?>" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 20px 24px;">
                                    <div style="margin-bottom: 12px;">
                                        <span style="font-weight: 800; font-size: 12px; color: #475569; text-transform: uppercase;"><?php echo __('Available Placeholder Tags (Click to insert):'); ?></span>
                                        <div style="margin-top: 6px;">
                                            <span class="tag-badge" onclick="insertTag('<?php echo $ek; ?>_wa', '{client_name}')">{client_name}</span>
                                            <span class="tag-badge" onclick="insertTag('<?php echo $ek; ?>_wa', '{phone}')">{phone}</span>
                                            <span class="tag-badge" onclick="insertTag('<?php echo $ek; ?>_wa', '{username}')">{username}</span>
                                            <span class="tag-badge" onclick="insertTag('<?php echo $ek; ?>_wa', '{password}')">{password}</span>
                                            <span class="tag-badge" onclick="insertTag('<?php echo $ek; ?>_wa', '{login_url}')">{login_url}</span>
                                            <span class="tag-badge" onclick="insertTag('<?php echo $ek; ?>_wa', '{agency_name}')">{agency_name}</span>
                                            <span class="tag-badge" onclick="insertTag('<?php echo $ek; ?>_wa', '{agency_phone}')">{agency_phone}</span>
                                            <span class="tag-badge" onclick="insertTag('<?php echo $ek; ?>_wa', '{agent_name}')">{agent_name}</span>
                                            <span class="tag-badge" onclick="insertTag('<?php echo $ek; ?>_wa', '{amount}')">{amount}</span>
                                            <span class="tag-badge" onclick="insertTag('<?php echo $ek; ?>_wa', '{due_date}')">{due_date}</span>
                                            <span class="tag-badge" onclick="insertTag('<?php echo $ek; ?>_wa', '{deal_invoice}')">{deal_invoice}</span>
                                            <span class="tag-badge" onclick="insertTag('<?php echo $ek; ?>_wa', '{receipt_no}')">{receipt_no}</span>
                                            <span class="tag-badge" onclick="insertTag('<?php echo $ek; ?>_wa', '{remarks}')">{remarks}</span>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <label style="font-weight: 800; color: #166534; font-size: 12.5px;">
                                                <i class="fa fa-whatsapp"></i> <?php echo __('WhatsApp Message Template:'); ?>
                                            </label>
                                            <textarea name="LeadbotEvent[<?php echo $ek; ?>][whatsapp_template]" id="<?php echo $ek; ?>_wa" class="form-control" rows="4" style="font-family: monospace; font-size: 12.5px; border-radius: 8px;"><?php echo h($ev['whatsapp_template']); ?></textarea>
                                        </div>
                                        <div class="col-md-6">
                                            <label style="font-weight: 800; color: #9a3412; font-size: 12.5px;">
                                                <i class="fa fa-comment-o"></i> <?php echo __('SMS Message Template:'); ?>
                                            </label>
                                            <textarea name="LeadbotEvent[<?php echo $ek; ?>][sms_template]" id="<?php echo $ek; ?>_sms" class="form-control" rows="4" style="font-family: monospace; font-size: 12.5px; border-radius: 8px;"><?php echo h($ev['sms_template']); ?></textarea>
                                        </div>
                                    </div>

                                    <input type="hidden" name="LeadbotEvent[<?php echo $ek; ?>][event_label]" value="<?php echo h($ev['event_label']); ?>">
                                    <input type="hidden" name="LeadbotEvent[<?php echo $ek; ?>][event_description]" value="<?php echo h($ev['event_description']); ?>">
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="lb-card-header" style="background: #fff; justify-content: flex-end;">
                        <button type="submit" class="btn btn-success" style="font-weight: 800; border-radius: 8px; padding: 10px 28px;">
                            <i class="fa fa-save"></i> <?php echo __('Save All Event Templates'); ?>
                        </button>
                    </div>
                </div>
                <?php echo $this->Form->end(); ?>
            </div>

            <?php if (!empty($canManageQueue)): ?>
            <!-- TAB: OUTBOX & MESSAGE QUEUE (WHATSAPP & SMS) -->
            <div class="tab-pane fade <?php echo ($activeTab === 'queue') ? 'active in' : ''; ?>" id="tab-queue">
                <div class="lb-card">
                    <div class="lb-card-header">
                        <div>
                            <h4 style="margin: 0; font-weight: 800; font-size: 16px; color: #0f172a;">
                                <i class="fa fa-clock-o text-warning"></i> <?php echo __('WhatsApp & SMS Outbox & Message Queue'); ?>
                            </h4>
                            <span style="font-size: 13px; color: #64748b;">
                                <?php echo __('Monitor, cancel, or delete pending notification dispatches for WhatsApp and Android SMS hardware.'); ?>
                            </span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span class="badge" style="background: <?php echo ($targetAgencyId === 0 && $targetUserId === 0) ? '#0284c7' : '#059669'; ?>; padding: 6px 12px; font-weight: 800; font-size: 12px;">
                                <?php if ($targetAgencyId === 0 && $targetUserId === 0): ?>
                                    🌐 <?php echo __('Global Queue Master'); ?>
                                <?php elseif ($targetUserId > 0): ?>
                                    👤 <?php echo __('Agent Personal Queue'); ?>
                                <?php else: ?>
                                    🏢 <?php echo __('Agency #' . $targetAgencyId . ' Queue'); ?>
                                <?php endif; ?>
                            </span>
                            <button type="button" class="btn btn-default btn-sm" id="btnRefreshQueue" style="font-weight: 700; border-radius: 8px;">
                                <i class="fa fa-refresh"></i> <?php echo __('Refresh'); ?>
                            </button>
                        </div>
                    </div>

                    <!-- Queue Summary Metric Counters -->
                    <div style="background: #f8fafc; border-bottom: 2px solid #e2e8f0; padding: 18px 20px;">
                        <div class="row" style="margin: 0 -8px;">
                            <div class="col-md-3 col-sm-6" style="padding: 0 8px; margin-bottom: 8px;">
                                <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 16px; display: flex; align-items: center; gap: 12px; box-shadow: 0 2px 6px rgba(0,0,0,0.03);">
                                    <div style="width: 40px; height: 40px; border-radius: 8px; background: #fef3c7; color: #d97706; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                                        <i class="fa fa-clock-o"></i>
                                    </div>
                                    <div>
                                        <div style="font-size: 20px; font-weight: 800; color: #0f172a;" id="metricQueueTotal"><?php echo isset($queueCounts['total']) ? (int)$queueCounts['total'] : 0; ?></div>
                                        <div style="font-size: 12px; color: #64748b; font-weight: 700;"><?php echo __('Total Active Queued'); ?></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6" style="padding: 0 8px; margin-bottom: 8px;">
                                <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 16px; display: flex; align-items: center; gap: 12px; box-shadow: 0 2px 6px rgba(0,0,0,0.03);">
                                    <div style="width: 40px; height: 40px; border-radius: 8px; background: #dcfce7; color: #16a34a; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                                        <i class="fa fa-whatsapp"></i>
                                    </div>
                                    <div>
                                        <div style="font-size: 20px; font-weight: 800; color: #0f172a;" id="metricQueueWa"><?php echo isset($queueCounts['whatsapp']) ? (int)$queueCounts['whatsapp'] : 0; ?></div>
                                        <div style="font-size: 12px; color: #64748b; font-weight: 700;"><?php echo __('WhatsApp Queue'); ?></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6" style="padding: 0 8px; margin-bottom: 8px;">
                                <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 16px; display: flex; align-items: center; gap: 12px; box-shadow: 0 2px 6px rgba(0,0,0,0.03);">
                                    <div style="width: 40px; height: 40px; border-radius: 8px; background: #ffedd5; color: #ea580c; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                                        <i class="fa fa-comment-o"></i>
                                    </div>
                                    <div>
                                        <div style="font-size: 20px; font-weight: 800; color: #0f172a;" id="metricQueueSms"><?php echo isset($queueCounts['sms']) ? (int)$queueCounts['sms'] : 0; ?></div>
                                        <div style="font-size: 12px; color: #64748b; font-weight: 700;"><?php echo __('Android SMS Queue'); ?></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6" style="padding: 0 8px; margin-bottom: 8px;">
                                <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 16px; display: flex; align-items: center; gap: 12px; box-shadow: 0 2px 6px rgba(0,0,0,0.03);">
                                    <div style="width: 40px; height: 40px; border-radius: 8px; background: #f1f5f9; color: #64748b; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                                        <i class="fa fa-ban"></i>
                                    </div>
                                    <div>
                                        <div style="font-size: 20px; font-weight: 800; color: #0f172a;" id="metricQueueCancelled"><?php echo isset($queueCounts['cancelled']) ? (int)$queueCounts['cancelled'] : 0; ?></div>
                                        <div style="font-size: 12px; color: #64748b; font-weight: 700;"><?php echo __('Cancelled Dispatches'); ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Queue Filters & Bulk Action Controls -->
                        <div style="margin-top: 14px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                            <!-- Channel Sub-Pills (Separate SMS & WhatsApp) -->
                            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                <span class="queue-channel-pill active" data-channel="all">
                                    <i class="fa fa-th-list"></i> <?php echo __('All Channels'); ?>
                                </span>
                                <span class="queue-channel-pill" data-channel="whatsapp">
                                    <i class="fa fa-whatsapp" style="color: #16a34a;"></i> <?php echo __('WhatsApp Queue'); ?>
                                    <span class="badge" style="font-size: 10px; background: #dcfce7; color: #166534;"><?php echo isset($queueCounts['whatsapp']) ? (int)$queueCounts['whatsapp'] : 0; ?></span>
                                </span>
                                <span class="queue-channel-pill" data-channel="sms">
                                    <i class="fa fa-comment-o" style="color: #ea580c;"></i> <?php echo __('SMS Queue'); ?>
                                    <span class="badge" style="font-size: 10px; background: #ffedd5; color: #9a3412;"><?php echo isset($queueCounts['sms']) ? (int)$queueCounts['sms'] : 0; ?></span>
                                </span>
                            </div>

                            <!-- Status Filter Sub-Pills -->
                            <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                                <span class="queue-status-pill active" data-status="queued">
                                    <i class="fa fa-clock-o text-warning"></i> <?php echo __('Queued / Pending'); ?>
                                </span>
                                <span class="queue-status-pill" data-status="cancelled">
                                    <i class="fa fa-ban text-muted"></i> <?php echo __('Cancelled'); ?>
                                </span>
                                <span class="queue-status-pill" data-status="failed">
                                    <i class="fa fa-times text-danger"></i> <?php echo __('Failed'); ?>
                                </span>
                                <span class="queue-status-pill" data-status="all">
                                    <i class="fa fa-history"></i> <?php echo __('All Records'); ?>
                                </span>
                            </div>

                            <!-- Live Search & Bulk Controls -->
                            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                <div style="position: relative; width: 200px;">
                                    <i class="fa fa-search" style="position: absolute; left: 10px; top: 9px; color: #94a3b8; font-size: 12px;"></i>
                                    <input type="text" id="queueSearchInput" class="form-control input-sm" placeholder="<?php echo __('Search recipient/text...'); ?>" style="padding-left: 28px; border-radius: 8px;">
                                </div>
                                <button type="button" class="btn btn-warning btn-sm" id="btnBulkCancelQueue" disabled style="font-weight: 700; border-radius: 8px;">
                                    <i class="fa fa-ban"></i> <?php echo __('Cancel Selected'); ?> (<span class="selected-queue-count">0</span>)
                                </button>
                                <button type="button" class="btn btn-danger btn-sm" id="btnBulkDeleteQueue" disabled style="font-weight: 700; border-radius: 8px;">
                                    <i class="fa fa-trash"></i> <?php echo __('Delete Selected'); ?> (<span class="selected-queue-count">0</span>)
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Queue Table -->
                    <div class="lb-card-body" style="padding: 0;">
                        <div class="table-responsive" style="margin: 0;">
                            <table class="table table-hover" id="tableQueueList" style="margin: 0; font-size: 13px;">
                                <thead style="background: #f1f5f9; color: #334155; font-size: 12px; text-transform: uppercase;">
                                    <tr>
                                        <th style="width: 35px; text-align: center;">
                                            <input type="checkbox" id="chkSelectAllQueue">
                                        </th>
                                        <th style="width: 60px;"><?php echo __('ID'); ?></th>
                                        <th style="width: 110px;"><?php echo __('Channel'); ?></th>
                                        <th style="width: 160px;"><?php echo __('Trigger Event'); ?></th>
                                        <th style="width: 170px;"><?php echo __('Recipient'); ?></th>
                                        <th><?php echo __('Message Content'); ?></th>
                                        <th style="width: 140px;"><?php echo __('Device / Profile'); ?></th>
                                        <th style="width: 100px; text-align: center;"><?php echo __('Status'); ?></th>
                                        <th style="width: 130px;"><?php echo __('Created'); ?></th>
                                        <th style="width: 130px; text-align: right;"><?php echo __('Actions'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($queueItems)): ?>
                                        <?php foreach ($queueItems as $qRow): 
                                            $qId      = (int)$qRow['LeadbotNotificationLog']['id'];
                                            $qChan    = strtolower(trim((string)$qRow['LeadbotNotificationLog']['channel']));
                                            $qStat    = strtolower(trim((string)$qRow['LeadbotNotificationLog']['status']));
                                            $qPhone   = $qRow['LeadbotNotificationLog']['recipient_phone'];
                                            $qName    = !empty($qRow['LeadbotNotificationLog']['recipient_name']) ? $qRow['LeadbotNotificationLog']['recipient_name'] : 'Client';
                                            $qEvt     = $qRow['LeadbotNotificationLog']['event_key'];
                                            $qMsg     = $qRow['LeadbotNotificationLog']['message'];
                                            $qDev     = !empty($qRow['LeadbotNotificationLog']['gateway_profile_or_device']) ? $qRow['LeadbotNotificationLog']['gateway_profile_or_device'] : 'Auto Gateway';
                                            $qDate    = $qRow['LeadbotNotificationLog']['created'];
                                            $isQueued = in_array($qStat, array('queued', 'pending'), true);
                                        ?>
                                            <tr class="queue-row" id="queue-row-<?php echo $qId; ?>"
                                                data-id="<?php echo $qId; ?>"
                                                data-channel="<?php echo $qChan; ?>"
                                                data-status="<?php echo $qStat; ?>"
                                                data-phone="<?php echo h(strtolower($qPhone)); ?>"
                                                data-name="<?php echo h(strtolower($qName)); ?>"
                                                data-msg="<?php echo h(strtolower($qMsg)); ?>">
                                                <td style="text-align: center; vertical-align: middle;">
                                                    <input type="checkbox" class="chk-queue-item" value="<?php echo $qId; ?>">
                                                </td>
                                                <td style="vertical-align: middle; font-weight: 700; color: #64748b;">
                                                    #<?php echo $qId; ?>
                                                </td>
                                                <td style="vertical-align: middle;">
                                                    <?php if ($qChan === 'whatsapp'): ?>
                                                        <span class="badge" style="background: #dcfce7; color: #166534; font-weight: 800; padding: 4px 8px; font-size: 11px;">
                                                            <i class="fa fa-whatsapp"></i> WhatsApp
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge" style="background: #ffedd5; color: #9a3412; font-weight: 800; padding: 4px 8px; font-size: 11px;">
                                                            <i class="fa fa-comment-o"></i> Android SMS
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td style="vertical-align: middle;">
                                                    <strong style="color: #0f172a; font-size: 12.5px;"><?php echo h(ucwords(str_replace('_', ' ', $qEvt))); ?></strong>
                                                </td>
                                                <td style="vertical-align: middle;">
                                                    <div style="font-weight: 700; color: #0f172a;"><?php echo h($qName); ?></div>
                                                    <code style="font-size: 11.5px;"><?php echo h($qPhone); ?></code>
                                                </td>
                                                <td style="vertical-align: middle;">
                                                    <div style="max-height: 44px; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; line-height: 1.4; color: #334155; font-size: 12px;" title="<?php echo h($qMsg); ?>">
                                                        <?php echo h($qMsg); ?>
                                                    </div>
                                                </td>
                                                <td style="vertical-align: middle; font-size: 12px; color: #64748b;">
                                                    <i class="fa fa-server"></i> <?php echo h($qDev); ?>
                                                </td>
                                                <td style="text-align: center; vertical-align: middle;">
                                                    <?php if ($isQueued): ?>
                                                        <span class="badge badge-status" style="background: #fef3c7; color: #92400e; font-weight: 800;">
                                                            <i class="fa fa-clock-o"></i> Queued
                                                        </span>
                                                    <?php elseif ($qStat === 'cancelled'): ?>
                                                        <span class="badge badge-status" style="background: #f1f5f9; color: #475569; font-weight: 800;">
                                                            <i class="fa fa-ban"></i> Cancelled
                                                        </span>
                                                    <?php elseif ($qStat === 'failed'): ?>
                                                        <span class="badge badge-status" style="background: #fee2e2; color: #991b1b; font-weight: 800;">
                                                            <i class="fa fa-times"></i> Failed
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge badge-status" style="background: #dcfce7; color: #166534; font-weight: 800;">
                                                            <i class="fa fa-check"></i> Sent
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td style="vertical-align: middle; font-size: 11.5px; color: #64748b;">
                                                    <?php echo date('d M Y, h:i A', strtotime($qDate)); ?>
                                                </td>
                                                <td style="text-align: right; vertical-align: middle; white-space: nowrap;">
                                                    <?php if ($isQueued): ?>
                                                        <button type="button" class="btn btn-warning btn-xs js-cancel-queue-item" data-id="<?php echo $qId; ?>" title="<?php echo __('Cancel Queued Notification'); ?>" style="font-weight: 700; border-radius: 6px; padding: 3px 8px;">
                                                            <i class="fa fa-ban"></i> <?php echo __('Cancel'); ?>
                                                        </button>
                                                    <?php else: ?>
                                                        <button type="button" class="btn btn-default btn-xs js-retry-queue-item" data-id="<?php echo $qId; ?>" title="<?php echo __('Re-dispatch Notification'); ?>" style="font-weight: 700; border-radius: 6px; padding: 3px 8px;">
                                                            <i class="fa fa-repeat text-primary"></i> <?php echo __('Retry'); ?>
                                                        </button>
                                                    <?php endif; ?>
                                                    <button type="button" class="btn btn-danger btn-xs js-delete-queue-item" data-id="<?php echo $qId; ?>" title="<?php echo __('Permanently Delete from Queue'); ?>" style="font-weight: 700; border-radius: 6px; padding: 3px 8px; margin-left: 4px;">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr id="emptyQueueRow">
                                            <td colspan="10" style="text-align: center; padding: 40px 20px; color: #64748b;">
                                                <div style="font-size: 36px; margin-bottom: 10px;">🎉</div>
                                                <h4 style="margin: 0 0 6px 0; font-weight: 800; color: #0f172a;"><?php echo __('The Message Queue is Empty'); ?></h4>
                                                <p style="margin: 0; font-size: 13px;"><?php echo __('All WhatsApp and SMS notification dispatches have been processed.'); ?></p>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- TAB 4: INTERACTIVE TEST CENTER -->
            <div class="tab-pane fade <?php echo ($activeTab === 'test') ? 'active in' : ''; ?>" id="tab-test">
                <div class="row">
                    <!-- WhatsApp Live Test Form -->
                    <div class="col-md-6">
                        <div class="lb-card">
                            <div class="lb-card-header">
                                <h4 style="margin: 0; font-weight: 800; font-size: 15px; color: #0f172a;">
                                    <i class="fa fa-whatsapp text-success"></i> <?php echo __('Live WhatsApp Test Dispatch'); ?>
                                </h4>
                            </div>
                            <div class="lb-card-body">
                                <form id="formTestWa">
                                    <div class="form-group">
                                        <label style="font-weight: 800; color: #334155; font-size: 13px;">
                                            <?php echo __('Target WhatsApp Number:'); ?> <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" id="testWaPhone" class="form-control" placeholder="e.g. 03001234567 or 923001234567" required style="font-weight: 700; border-radius: 8px;">
                                        <small class="text-muted"><?php echo __('Enter local (03xx) or international format. Leading zeros are auto-converted to +92.'); ?></small>
                                    </div>

                                    <div class="form-group">
                                        <label style="font-weight: 800; color: #334155; font-size: 13px;">
                                            <?php echo __('Sender Profile Session:'); ?>
                                        </label>
                                        <select id="testWaSession" class="form-control" style="font-weight: 700; border-radius: 8px;">
                                            <option value=""><?php echo __('-- Use Active Assigned Session --'); ?></option>
                                            <?php foreach ($waSessions as $ws): ?>
                                                <option value="<?php echo h($ws['session_id']); ?>">
                                                    <?php echo h($ws['display_name']) . ' (' . h($ws['session_id']) . ')'; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label style="font-weight: 800; color: #334155; font-size: 13px;">
                                            <?php echo __('Test Message:'); ?>
                                        </label>
                                        <textarea id="testWaMessage" class="form-control" rows="3" style="border-radius: 8px;">White Villas WhatsApp Gateway test notification. Time: <?php echo date('d-M-Y h:i A'); ?>. Your gateway is operational!</textarea>
                                    </div>

                                    <button type="submit" id="btnSendWaTest" class="btn btn-success" style="font-weight: 800; border-radius: 8px; width: 100%; padding: 10px;">
                                        <i class="fa fa-paper-plane"></i> <?php echo __('Send Test WhatsApp Message'); ?>
                                    </button>
                                </form>

                                <div id="waTestResultBox" style="margin-top: 18px; display: none; padding: 14px; border-radius: 8px;"></div>
                            </div>
                        </div>
                    </div>

                    <!-- SMS Live Test Form -->
                    <div class="col-md-6">
                        <div class="lb-card">
                            <div class="lb-card-header">
                                <h4 style="margin: 0; font-weight: 800; font-size: 15px; color: #0f172a;">
                                    <i class="fa fa-android text-warning"></i> <?php echo __('Live Android SMS Carrier Test Dispatch'); ?>
                                </h4>
                            </div>
                            <div class="lb-card-body">
                                <form id="formTestSms">
                                    <div class="form-group">
                                        <label style="font-weight: 800; color: #334155; font-size: 13px;">
                                            <?php echo __('Target Mobile Number:'); ?> <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" id="testSmsPhone" class="form-control" placeholder="e.g. 03001234567 or 923001234567" required style="font-weight: 700; border-radius: 8px;">
                                        <small class="text-muted"><?php echo __('Carrier SMS recipient mobile.'); ?></small>
                                    </div>

                                    <div class="form-group">
                                        <label style="font-weight: 800; color: #334155; font-size: 13px;">
                                            <?php echo __('Sending Android Device:'); ?>
                                        </label>
                                        <select id="testSmsDevice" class="form-control" style="font-weight: 700; border-radius: 8px;">
                                            <option value=""><?php echo __('-- Use Active Assigned Device --'); ?></option>
                                            <?php foreach ($smsDevices as $sd): ?>
                                                <option value="<?php echo (int)$sd['id']; ?>">
                                                    <?php echo h($sd['device_name']) . ' - [Device #' . (int)$sd['id'] . ']'; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label style="font-weight: 800; color: #334155; font-size: 13px;">
                                            <?php echo __('SIM Card Slot:'); ?>
                                        </label>
                                        <select id="testSmsSimSlot" class="form-control" style="font-weight: 700; border-radius: 8px;">
                                            <option value="1">SIM 1</option>
                                            <option value="2">SIM 2</option>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label style="font-weight: 800; color: #334155; font-size: 13px;">
                                            <?php echo __('SMS Content:'); ?>
                                        </label>
                                        <textarea id="testSmsMessage" class="form-control" rows="3" style="border-radius: 8px;">White Villas SMS Gateway Test. Carrier SMS dispatched at <?php echo date('h:i A'); ?>. Gateway is active.</textarea>
                                    </div>

                                    <button type="submit" id="btnSendSmsTest" class="btn btn-warning" style="font-weight: 800; border-radius: 8px; width: 100%; padding: 10px; color: #fff;">
                                        <i class="fa fa-paper-plane"></i> <?php echo __('Dispatch Test SMS to Android Gateway'); ?>
                                    </button>
                                </form>

                                <div id="smsTestResultBox" style="margin-top: 18px; display: none; padding: 14px; border-radius: 8px;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 5: AUDIT LOGS -->
            <div class="tab-pane fade <?php echo ($activeTab === 'logs') ? 'active in' : ''; ?>" id="tab-logs">
                <div class="lb-card">
                    <div class="lb-card-header">
                        <div>
                            <h4 style="margin: 0; font-weight: 800; font-size: 15px; color: #0f172a;">
                                <i class="fa fa-history text-danger"></i> <?php echo __('Recent Dispatch History & Audit Trail'); ?>
                            </h4>
                            <span style="font-size: 12px; color: #64748b;">
                                <?php if ($targetUserId > 0): ?>
                                    <?php echo __('Showing logs isolated to Agent: ') . h($targetAgentUser ? $targetAgentUser['name'] : '#' . $targetUserId); ?>
                                <?php elseif ($targetAgencyId > 0): ?>
                                    <?php echo __('Showing logs isolated to Agency: #') . $targetAgencyId; ?>
                                <?php else: ?>
                                    <?php echo __('Showing global platform dispatches.'); ?>
                                <?php endif; ?>
                            </span>
                        </div>
                        <a href="<?php echo $this->Html->url(array('action' => 'logs', '?' => array('project_id' => $targetAgencyId, 'user_id' => $targetUserId))); ?>" class="btn btn-default btn-sm" style="font-weight: 700; border-radius: 6px;">
                            <i class="fa fa-external-link"></i> <?php echo __('Open Full Audit Logs Explorer'); ?> &rarr;
                        </a>
                    </div>
                    <div style="padding: 10px 20px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                        <div style="position: relative; width: 280px;">
                            <i class="fa fa-search" style="position: absolute; left: 10px; top: 9px; color: #94a3b8; font-size: 12px;"></i>
                            <input type="text" id="recentLogsSearch" class="form-control input-sm" placeholder="<?php echo __('Filter recent logs...'); ?>" style="padding-left: 28px; border-radius: 6px;">
                        </div>
                        <span class="text-muted" style="font-size: 12px; font-weight: 600;">
                            <?php echo sprintf(__('Showing %d most recent dispatches'), count($recentLogs)); ?>
                        </span>
                    </div>
                    <div class="lb-card-body" style="padding: 0;">
                        <?php if (!empty($recentLogs)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover" style="margin: 0;">
                                    <thead>
                                        <tr style="background: #f8fafc; font-size: 12px; color: #64748b;">
                                            <th><?php echo __('Time'); ?></th>
                                            <th><?php echo __('Channel'); ?></th>
                                            <th><?php echo __('Event Key'); ?></th>
                                            <th><?php echo __('Recipient'); ?></th>
                                            <th><?php echo __('Message'); ?></th>
                                            <th><?php echo __('Device / Profile'); ?></th>
                                            <th class="text-right"><?php echo __('Status'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentLogs as $lg): 
                                            $log = $lg['LeadbotNotificationLog'];
                                            $isSent = in_array($log['status'], array('sent', 'queued', 'delivered'), true);
                                        ?>
                                            <tr>
                                                <td style="font-size: 12px; color: #64748b; white-space: nowrap;">
                                                    <?php echo date('d-M h:i A', strtotime($log['created'])); ?>
                                                </td>
                                                <td>
                                                    <?php if ($log['channel'] === 'whatsapp'): ?>
                                                        <span class="label label-success" style="font-size: 11px;"><i class="fa fa-whatsapp"></i> WhatsApp</span>
                                                    <?php else: ?>
                                                        <span class="label label-warning" style="font-size: 11px;"><i class="fa fa-comment-o"></i> SMS</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge" style="background: #f1f5f9; color: #334155; font-size: 10.5px;"><?php echo h($log['event_key']); ?></span>
                                                </td>
                                                <td>
                                                    <strong style="color: #0f172a; font-size: 12.5px;"><?php echo h($log['recipient_name']); ?></strong>
                                                    <div style="font-size: 11px; color: #64748b;"><?php echo h($log['recipient_phone']); ?></div>
                                                </td>
                                                <td style="max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 12px;">
                                                    <?php echo h($log['message']); ?>
                                                </td>
                                                <td style="font-size: 11.5px; color: #475569;">
                                                    <?php echo h($log['gateway_profile_or_device']); ?>
                                                </td>
                                                <td class="text-right">
                                                    <?php if ($isSent): ?>
                                                        <span class="label label-success" style="font-size: 11px; text-transform: uppercase;"><?php echo h($log['status']); ?></span>
                                                    <?php else: ?>
                                                        <span class="label label-danger" style="font-size: 11px; text-transform: uppercase;"><?php echo h($log['status']); ?></span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div style="padding: 40px; text-align: center; color: #64748b;">
                                <i class="fa fa-history" style="font-size: 40px; color: #cbd5e1; margin-bottom: 12px;"></i>
                                <p style="margin: 0; font-weight: 700;"><?php echo __('No notification dispatches recorded yet for this scope.'); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Set Isolated API Key for Agent -->
<div class="modal fade" id="agentIsolateKeyModal" tabindex="-1" role="dialog" aria-labelledby="agentIsolateKeyModalLabel">
    <div class="modal-dialog modal-sm" role="document" style="margin-top: 100px;">
        <div class="modal-content" style="border-radius: 12px; border: 1px solid #cbd5e1; box-shadow: 0 10px 30px rgba(15,23,42,0.2); overflow: hidden;">
            <div class="modal-header" style="background: linear-gradient(135deg, #07152a 0%, #15304f 100%); color: #fff; padding: 14px 18px;">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: #fff; opacity: 0.8; font-size: 20px;">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="agentIsolateKeyModalLabel" style="font-weight: 800; font-size: 14px; margin: 0; color: #fff;">
                    <i class="fa fa-key text-warning" style="margin-right: 6px;"></i> <?php echo __('Isolate Agent Gateway'); ?>
                </h4>
            </div>
            <div class="modal-body" style="padding: 18px;">
                <p style="font-size: 12px; color: #475569; margin-bottom: 12px;">
                    <?php echo __('Enter dedicated LeadBot API key to isolate'); ?> <strong id="modalAgentName" style="color: #0f172a;"></strong>:
                </p>
                <div class="form-group" style="margin-bottom: 10px;">
                    <label style="font-weight: 700; font-size: 11.5px; color: #334155; margin-bottom: 4px;">
                        <?php echo __('Personal API Key (X-API-KEY):'); ?> <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <input type="password" id="modalInputApiKey" class="form-control input-sm" placeholder="e.g. owa_live_..." style="font-family: monospace; font-size: 11.5px; border-radius: 6px 0 0 6px;">
                        <span class="input-group-btn">
                            <button type="button" class="btn btn-sm btn-default" id="modalBtnToggleKey" title="Show/Hide Key" style="border-radius: 0 6px 6px 0;">
                                <i class="fa fa-eye"></i>
                            </button>
                        </span>
                    </div>
                </div>
                <small class="text-muted" style="display: block; font-size: 11px;">
                    <i class="fa fa-info-circle text-info"></i> <?php echo __('Leads assigned to this agent will dispatch exclusively through their dedicated personal gateway.'); ?>
                </small>
            </div>
            <div class="modal-footer" style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 10px 18px; display: flex; justify-content: space-between; align-items: center;">
                <button type="button" class="btn btn-xs btn-default" id="modalBtnCancel" data-dismiss="modal" style="font-weight: 700; border-radius: 6px;">
                    <?php echo __('Cancel'); ?>
                </button>
                <button type="button" class="btn btn-xs btn-success" id="modalBtnSaveKey" style="font-weight: 700; border-radius: 6px; padding: 5px 14px;">
                    <i class="fa fa-check"></i> <?php echo __('Save & Isolate'); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Floating Toast Notification -->
<div id="wvLeadbotToast"></div>

<script type="text/javascript">
function showToast(text, isError) {
    var toast = $('#wvLeadbotToast');
    toast.css('background', isError ? '#ef4444' : '#0f172a');
    toast.html((isError ? '<i class="fa fa-times-circle text-danger"></i> ' : '<i class="fa fa-check-circle text-success"></i> ') + text).stop(true, true).fadeIn(200);
    setTimeout(function(){ toast.fadeOut(300); }, 3000);
}

function insertTag(textareaId, tag) {
    var ta = document.getElementById(textareaId);
    if (!ta) return;
    var start = ta.selectionStart;
    var end   = ta.selectionEnd;
    var text  = ta.value;
    ta.value  = text.substring(0, start) + tag + text.substring(end);
    ta.selectionStart = ta.selectionEnd = start + tag.length;
    ta.focus();
}

(function($){
    // 1-Click Interactive Toggle for LeadBot as System Default Gateway
    $(document).on('click', '#btnToggleDefaultGateway, #btnToggleDefaultGatewayAlt', function(e){
        e.preventDefault();
        var btn = $('#btnToggleDefaultGateway');
        var altBtn = $('#btnToggleDefaultGatewayAlt');
        var chkSetting = $('#chkLeadbotDefaultSetting');
        var isCurrentlyDefault = btn.hasClass('btn-success');
        var targetState = isCurrentlyDefault ? 0 : 1;

        btn.prop('disabled', true);
        altBtn.prop('disabled', true);
        $('#iconDefaultGateway').removeClass('fa-check-circle fa-star').addClass('fa-spinner fa-spin');

        $.ajax({
            url: '<?php echo $this->Html->url(array("plugin" => "admin", "controller" => "leadbot_gateways", "action" => "ajax_set_default")); ?>',
            type: 'POST',
            dataType: 'json',
            data: {
                project_id: <?php echo (int)$targetAgencyId; ?>,
                user_id: <?php echo (int)$targetUserId; ?>,
                state: targetState
            },
            success: function(resp) {
                btn.prop('disabled', false);
                altBtn.prop('disabled', false);
                if (resp && resp.success) {
                    var isDef = resp.is_leadbot_default;
                    chkSetting.prop('checked', isDef);
                    if (isDef) {
                        btn.removeClass('btn-warning').addClass('btn-success');
                        $('#iconDefaultGateway').removeClass('fa-spinner fa-spin fa-star').addClass('fa-check-circle');
                        $('#textDefaultGateway').text('<?php echo __("LeadBot is Default (SMS & WA)"); ?>');
                        
                        $('#bannerDefaultGateway').removeClass('alert-info').addClass('alert-success')
                            .css({'background': 'rgba(16, 185, 129, 0.2)', 'border-color': 'rgba(16, 185, 129, 0.4)'});
                        $('#badgeDefaultGateway').css('background', '#10b981').html('<i class="fa fa-check-circle"></i> <?php echo __("DEFAULT SYSTEM ENGINE"); ?>');
                        $('#descDefaultGateway').text('<?php echo __("LeadBot CRM is active as the DEFAULT gateway for all system SMS & WhatsApp messages."); ?>');
                        altBtn.removeClass('btn-success').addClass('btn-default').text('<?php echo __("Switch to Custom SMS"); ?>');
                    } else {
                        btn.removeClass('btn-success').addClass('btn-warning');
                        $('#iconDefaultGateway').removeClass('fa-spinner fa-spin fa-check-circle').addClass('fa-star');
                        $('#textDefaultGateway').text('<?php echo __("Use LeadBot as Default (SMS & WA)"); ?>');
                        
                        $('#bannerDefaultGateway').removeClass('alert-success').addClass('alert-info')
                            .css({'background': 'rgba(59, 130, 246, 0.15)', 'border-color': 'rgba(59, 130, 246, 0.3)'});
                        $('#badgeDefaultGateway').css('background', '#64748b').html('<i class="fa fa-info-circle"></i> <?php echo __("SECONDARY / CRM EVENTS ONLY"); ?>');
                        $('#descDefaultGateway').text('<?php echo __("Custom HTTP SMS Gateway (/admin/Smssettings) is currently handling system SMS. Click to use LeadBot as default."); ?>');
                        altBtn.removeClass('btn-default').addClass('btn-success').text('<?php echo __("Set LeadBot as Default"); ?>');
                    }
                    showToast(resp.message, false);
                } else {
                    $('#iconDefaultGateway').removeClass('fa-spinner fa-spin').addClass(isCurrentlyDefault ? 'fa-check-circle' : 'fa-star');
                    showToast((resp && resp.message) ? resp.message : 'Error setting default gateway', true);
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false);
                altBtn.prop('disabled', false);
                $('#iconDefaultGateway').removeClass('fa-spinner fa-spin').addClass(isCurrentlyDefault ? 'fa-check-circle' : 'fa-star');
                showToast('Network error: ' + xhr.status, true);
            }
        });
    });

    $(document).on('change', '#chkLeadbotDefaultSetting', function(){
        $('#btnToggleDefaultGateway').trigger('click');
    });

    // Accordion Toggle
    $('.btn-toggle-tpl').on('click', function(e){
        e.preventDefault();
        var target = $(this).data('target');
        $(target).collapse('toggle');
    });

    // Key Visibility Toggle
    $('#btnToggleKeyVis').on('click', function(){
        var inp = $('#inputApiKey');
        inp.attr('type', (inp.attr('type') === 'password') ? 'text' : 'password');
        $(this).find('i').toggleClass('fa-eye fa-eye-slash');
    });

    // 1-Click Interactive Event Toggles
    $(document).on('change', '.js-event-toggle', function(){
        var chk = $(this);
        var eventKey = chk.data('event-key');
        var field = chk.data('field');
        var state = chk.is(':checked') ? 1 : 0;
        var projectId = <?php echo (int)$targetAgencyId; ?>;
        var userId = <?php echo (int)$targetUserId; ?>;

        $.ajax({
            url: '<?php echo $this->Html->url(array('action' => 'ajax_toggle_event')); ?>',
            type: 'POST',
            dataType: 'json',
            data: {
                project_id: projectId,
                user_id: userId,
                event_key: eventKey,
                field: field,
                state: state
            }
        }).done(function(res){
            if(res && res.success){
                showToast(res.message || 'Toggle saved', false);
                var wrap = chk.closest('.event-item-wrap');
                if (field === 'whatsapp_enabled') {
                    wrap.attr('data-wa', state).data('wa', state);
                } else if (field === 'sms_enabled') {
                    wrap.attr('data-sms', state).data('sms', state);
                }
            } else {
                showToast('Failed to save toggle: ' + (res && res.error ? res.error : 'Error'), true);
                chk.prop('checked', !state);
            }
        }).fail(function(){
            showToast('Network error saving toggle.', true);
            chk.prop('checked', !state);
        });
    });

    // 1-Click Interactive Master Channel Toggles (Settings Tab)
    $(document).on('change', '.js-master-toggle', function(){
        var chk = $(this);
        var channel = chk.data('channel');
        var state = chk.is(':checked') ? 1 : 0;
        var projectId = chk.data('project-id') !== undefined ? chk.data('project-id') : <?php echo (int)$targetAgencyId; ?>;
        var userId = chk.data('user-id') !== undefined ? chk.data('user-id') : <?php echo (int)$targetUserId; ?>;

        $.ajax({
            url: '<?php echo $this->Html->url(array('action' => 'ajax_toggle_master')); ?>',
            type: 'POST',
            dataType: 'json',
            data: {
                project_id: projectId,
                user_id: userId,
                channel: channel,
                state: state
            }
        }).done(function(res){
            if(res && res.success){
                showToast(res.message || (channel + ' master toggle updated'), false);
            } else {
                showToast(res && res.message ? res.message : 'Failed to update master toggle', true);
                chk.prop('checked', !state);
            }
        }).fail(function(){
            showToast('Network error saving master toggle.', true);
            chk.prop('checked', !state);
        });
    });

    // 1-Click Interactive Agent Channel Toggle Buttons in Table
    $(document).on('click', '.js-agent-channel-toggle', function(e){
        e.preventDefault();
        var btn = $(this);
        if (btn.hasClass('is-loading')) return;

        var channel = btn.data('channel');
        var currentState = parseInt(btn.data('state'), 10) || 0;
        var newState = currentState === 1 ? 0 : 1;
        var userId = parseInt(btn.data('user-id'), 10) || 0;
        var projectId = parseInt(btn.data('project-id'), 10) || <?php echo (int)$targetAgencyId; ?>;

        btn.addClass('is-loading');

        $.ajax({
            url: '<?php echo $this->Html->url(array('action' => 'ajax_toggle_master')); ?>',
            type: 'POST',
            dataType: 'json',
            data: {
                project_id: projectId,
                user_id: userId,
                channel: channel,
                state: newState
            }
        }).done(function(res){
            btn.removeClass('is-loading');
            if (res && res.success) {
                btn.data('state', newState);
                var isWa = (channel === 'whatsapp');
                if (newState === 1) {
                    btn.removeClass('is-off').addClass('is-on ' + (isWa ? 'is-wa' : 'is-sms'));
                    btn.find('.switch-label').text('ON');
                    btn.attr('title', 'Click to turn ' + (isWa ? 'WhatsApp' : 'SMS') + ' Auto OFF');
                } else {
                    btn.removeClass('is-on is-wa is-sms').addClass('is-off');
                    btn.find('.switch-label').text('OFF');
                    btn.attr('title', 'Click to turn ' + (isWa ? 'WhatsApp' : 'SMS') + ' Auto ON');
                }
                showToast(res.message || ((isWa ? 'WhatsApp' : 'SMS') + ' auto toggle updated.'), false);
            } else {
                showToast(res && res.message ? res.message : 'Failed to update toggle.', true);
            }
        }).fail(function(){
            btn.removeClass('is-loading');
            showToast('Network error updating channel toggle.', true);
        });
    });

    // 1-Click Interactive Gateway Credential Mode Dropdown (Agency vs Isolated)
    var pendingCredSelect = null;

    $(document).on('change', '.js-agent-cred-mode-select', function(){
        var sel = $(this);
        var newMode = sel.val();
        var prevMode = (newMode === 'isolated') ? 'agency' : 'isolated';
        var userId = parseInt(sel.data('user-id'), 10) || 0;
        var projectId = parseInt(sel.data('project-id'), 10) || <?php echo (int)$targetAgencyId; ?>;
        var agentName = sel.data('agent-name') || ('ID #' + userId);
        var hasStoredKey = parseInt(sel.data('has-key'), 10) === 1;

        if (newMode === 'isolated' && !hasStoredKey) {
            pendingCredSelect = {
                select: sel,
                userId: userId,
                projectId: projectId,
                agentName: agentName
            };
            $('#modalAgentName').text(agentName);
            $('#modalInputApiKey').val('');
            $('#agentIsolateKeyModal').modal('show');
            setTimeout(function(){ $('#modalInputApiKey').focus(); }, 350);
            return;
        }

        applyCredentialModeChange(sel, userId, projectId, newMode, null, prevMode);
    });

    function applyCredentialModeChange(sel, userId, projectId, mode, apiKey, fallbackMode) {
        sel.addClass('is-loading');

        $.ajax({
            url: '<?php echo $this->Html->url(array('action' => 'ajax_set_credential_mode')); ?>',
            type: 'POST',
            dataType: 'json',
            data: {
                user_id: userId,
                project_id: projectId,
                mode: mode,
                api_key: apiKey
            }
        }).done(function(res){
            sel.removeClass('is-loading');
            if (res && res.success) {
                var isIso = (res.mode === 'isolated');
                sel.val(res.mode);
                if (isIso) {
                    sel.removeClass('mode-agency').addClass('mode-isolated');
                    if (res.has_key) {
                        sel.data('has-key', 1);
                    }
                } else {
                    sel.removeClass('mode-isolated').addClass('mode-agency');
                }
                showToast(res.message || 'Credential mode updated.', false);
            } else {
                sel.val(fallbackMode || 'agency');
                showToast(res && res.message ? res.message : 'Failed to update credential mode.', true);
            }
        }).fail(function(){
            sel.removeClass('is-loading');
            sel.val(fallbackMode || 'agency');
            showToast('Network error updating credential mode.', true);
        });
    }

    // Modal: Save & Isolate Button
    $('#modalBtnSaveKey').on('click', function(){
        if (!pendingCredSelect) return;
        var key = $.trim($('#modalInputApiKey').val());
        if (!key) {
            alert('Please enter a personal API key, or click Cancel.');
            $('#modalInputApiKey').focus();
            return;
        }

        var sel = pendingCredSelect.select;
        var uId = pendingCredSelect.userId;
        var pId = pendingCredSelect.projectId;
        pendingCredSelect = null;

        $('#agentIsolateKeyModal').modal('hide');
        applyCredentialModeChange(sel, uId, pId, 'isolated', key, 'agency');
    });

    // Modal: If user cancels without saving, revert select to 'agency'
    $('#agentIsolateKeyModal').on('hidden.bs.modal', function(){
        if (pendingCredSelect) {
            pendingCredSelect.select.val('agency').removeClass('mode-isolated').addClass('mode-agency');
            pendingCredSelect = null;
        }
    });

    // Modal: Toggle password visibility
    $('#modalBtnToggleKey').on('click', function(){
        var inp = $('#modalInputApiKey');
        var ico = $(this).find('i');
        if (inp.attr('type') === 'password') {
            inp.attr('type', 'text');
            ico.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            inp.attr('type', 'password');
            ico.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    // Notification Toggles: Real-time Search, Category Filtering & Dynamic Sorting
    var activeCategory = 'all';
    var currentSearchQuery = '';

    function filterAndCountEvents() {
        var items = $('.event-item-wrap');
        var visibleCount = 0;
        var query = currentSearchQuery.toLowerCase().trim();

        items.each(function(){
            var el = $(this);
            var cat = (el.data('category') || 'operations').toString();
            var label = (el.data('event-label') || '').toString();
            var key = (el.data('event-key') || '').toString();
            var desc = (el.data('desc') || '').toString();

            var matchesCategory = (activeCategory === 'all' || cat === activeCategory);
            var matchesSearch = (query === '' || label.indexOf(query) !== -1 || key.indexOf(query) !== -1 || desc.indexOf(query) !== -1);

            if (matchesCategory && matchesSearch) {
                el.show();
                visibleCount++;
            } else {
                el.hide();
            }
        });

        $('#eventsFilterCounter').text('Showing ' + visibleCount + ' of ' + items.length + ' events');
    }

    // Category Pill Filter Click
    $('.event-category-pill').on('click', function(){
        $('.event-category-pill').removeClass('active');
        $(this).addClass('active');
        activeCategory = $(this).data('cat');
        filterAndCountEvents();
    });

    // Instant Search Input
    $('#eventFilterSearch').on('input', function(){
        currentSearchQuery = $(this).val();
        filterAndCountEvents();
    });

    // Dynamic Event Sorting
    $('#eventSortSelect').on('change', function(){
        var sortType = $(this).val();
        var container = $('#eventsListContainer');
        var items = container.children('.event-item-wrap').get();

        items.sort(function(a, b){
            var $a = $(a), $b = $(b);
            if (sortType === 'name_asc') {
                return ($a.data('event-label') || '').localeCompare($b.data('event-label') || '');
            } else if (sortType === 'name_desc') {
                return ($b.data('event-label') || '').localeCompare($a.data('event-label') || '');
            } else if (sortType === 'wa_on') {
                var waA = parseInt($a.data('wa') || 0), waB = parseInt($b.data('wa') || 0);
                if (waA !== waB) return waB - waA;
                return parseInt($a.data('sort-order') || 0) - parseInt($b.data('sort-order') || 0);
            } else if (sortType === 'wa_off') {
                var waA = parseInt($a.data('wa') || 0), waB = parseInt($b.data('wa') || 0);
                if (waA !== waB) return waA - waB;
                return parseInt($a.data('sort-order') || 0) - parseInt($b.data('sort-order') || 0);
            } else if (sortType === 'sms_on') {
                var smsA = parseInt($a.data('sms') || 0), smsB = parseInt($b.data('sms') || 0);
                if (smsA !== smsB) return smsB - smsA;
                return parseInt($a.data('sort-order') || 0) - parseInt($b.data('sort-order') || 0);
            } else if (sortType === 'sms_off') {
                var smsA = parseInt($a.data('sms') || 0), smsB = parseInt($b.data('sms') || 0);
                if (smsA !== smsB) return smsA - smsB;
                return parseInt($a.data('sort-order') || 0) - parseInt($b.data('sort-order') || 0);
            } else if (sortType === 'category') {
                var catA = ($a.data('category') || '').toString(), catB = ($b.data('category') || '').toString();
                if (catA !== catB) return catA.localeCompare(catB);
                return parseInt($a.data('sort-order') || 0) - parseInt($b.data('sort-order') || 0);
            } else {
                // Default: workflow sequence
                return parseInt($a.data('sort-order') || 0) - parseInt($b.data('sort-order') || 0);
            }
        });

        $.each(items, function(idx, itm){
            container.append(itm);
        });
    });

    // Bulk Toggle Actions (Turn all visible WA/SMS ON or OFF)
    $('.btn-bulk-toggle').on('click', function(e){
        e.preventDefault();
        var field = $(this).data('field');
        var state = parseInt($(this).data('state') || 0);
        var cat = activeCategory;
        var channelName = (field === 'whatsapp_enabled') ? 'WhatsApp' : 'SMS';
        var stateName   = state ? 'ENABLED' : 'DISABLED';

        var confMsg = 'Bulk Update: Turn all visible ' + channelName + ' event toggles ' + stateName + ' for the current scope?';
        if (!confirm(confMsg)) {
            return;
        }

        $.ajax({
            url: '<?php echo $this->Html->url(array('action' => 'ajax_bulk_toggle_events')); ?>',
            type: 'POST',
            dataType: 'json',
            data: {
                project_id: <?php echo (int)$targetAgencyId; ?>,
                user_id: <?php echo (int)$targetUserId; ?>,
                field: field,
                state: state,
                category: cat
            }
        }).done(function(res){
            if (res && res.success) {
                showToast(res.message, false);
                // Update visible items
                $('.event-item-wrap:visible').each(function(){
                    var wrap = $(this);
                    wrap.find('.js-event-toggle[data-field="' + field + '"]').prop('checked', !!state);
                    if (field === 'whatsapp_enabled') {
                        wrap.attr('data-wa', state).data('wa', state);
                    } else if (field === 'sms_enabled') {
                        wrap.attr('data-sms', state).data('sms', state);
                    }
                });
            } else {
                showToast(res && res.error ? res.error : 'Failed to perform bulk toggle update', true);
            }
        }).fail(function(){
            showToast('Network error performing bulk toggle update.', true);
        });
    });

    // Test Connection / Verify Key
    $('#btnTestConn').on('click', function(){
        var btn = $(this);
        var box = $('#connTestResult');
        var url = $('#inputGatewayUrl').val();
        var key = $('#inputApiKey').val();

        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
        box.hide();

        $.ajax({
            url: '<?php echo $this->Html->url(array('action' => 'fetch_telemetry')); ?>',
            type: 'GET',
            dataType: 'json'
        }).done(function(data){
            btn.prop('disabled', false).html('<i class="fa fa-plug"></i> <?php echo __('Verify'); ?>');
            if (data && data.ping && data.ping.connected) {
                box.removeClass('text-danger').addClass('text-success').html('<i class="fa fa-check-circle"></i> Connected to LeadBot Gateway! Latency: ' + data.ping.latency_ms + 'ms').fadeIn();
            } else {
                box.removeClass('text-success').addClass('text-danger').html('<i class="fa fa-times-circle"></i> Connection failed: ' + (data && data.ping && data.ping.error ? data.ping.error : 'Timeout')).fadeIn();
            }
        }).fail(function(){
            btn.prop('disabled', false).html('<i class="fa fa-plug"></i> <?php echo __('Verify'); ?>');
            box.removeClass('text-success').addClass('text-danger').html('<i class="fa fa-times-circle"></i> Network error reaching server endpoint.').fadeIn();
        });
    });

    // Refresh Telemetry
    $('#btnRefreshTelemetry').on('click', function(){
        var icon = $('#telemetrySpinIcon');
        icon.addClass('fa-spin');
        $.ajax({
            url: '<?php echo $this->Html->url(array('action' => 'fetch_telemetry')); ?>',
            type: 'GET',
            dataType: 'json'
        }).always(function(){
            window.location.reload();
        });
    });

    // Live Test: WhatsApp
    $('#formTestWa').on('submit', function(e){
        e.preventDefault();
        var btn = $('#btnSendWaTest');
        var box = $('#waTestResultBox');
        var phone = $('#testWaPhone').val();
        var session = $('#testWaSession').val();
        var msg = $('#testWaMessage').val();

        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> <?php echo __('Dispatching...'); ?>');
        box.hide();

        $.ajax({
            url: '<?php echo $this->Html->url(array('action' => 'test_whatsapp')); ?>',
            type: 'POST',
            data: { 
                phone: phone, 
                session: session, 
                message: msg,
                project_id: <?php echo (int)$targetAgencyId; ?>,
                user_id: <?php echo (int)$targetUserId; ?>
            },
            dataType: 'json'
        }).done(function(res){
            btn.prop('disabled', false).html('<i class="fa fa-paper-plane"></i> <?php echo __('Send Test WhatsApp Message'); ?>');
            box.show();
            if (res && res.success) {
                box.css({'background': '#dcfce7', 'border': '1px solid #86efac', 'color': '#166534'});
                box.html('<strong><i class="fa fa-check-circle"></i> Message Dispatched Successfully!</strong><br><small>Latency: ' + (res.latency_ms || 0) + 'ms | Gateway Ref: ' + (res.response_ref || 'OK') + '</small>');
            } else {
                box.css({'background': '#fee2e2', 'border': '1px solid #fca5a5', 'color': '#991b1b'});
                box.html('<strong><i class="fa fa-times-circle"></i> Dispatch Failed:</strong><br><small>' + (res && res.error ? res.error : 'Unknown gateway error') + '</small>');
            }
        }).fail(function(){
            btn.prop('disabled', false).html('<i class="fa fa-paper-plane"></i> <?php echo __('Send Test WhatsApp Message'); ?>');
            box.show().css({'background': '#fee2e2', 'border': '1px solid #fca5a5', 'color': '#991b1b'}).html('<strong><i class="fa fa-times-circle"></i> Network/Server error occurred.</strong>');
        });
    });

    // Live Test: SMS
    $('#formTestSms').on('submit', function(e){
        e.preventDefault();
        var btn = $('#btnSendSmsTest');
        var box = $('#smsTestResultBox');
        var phone = $('#testSmsPhone').val();
        var devId = $('#testSmsDevice').val();
        var slot  = $('#testSmsSimSlot').val();
        var msg   = $('#testSmsMessage').val();

        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> <?php echo __('Dispatching...'); ?>');
        box.hide();

        $.ajax({
            url: '<?php echo $this->Html->url(array('action' => 'test_sms')); ?>',
            type: 'POST',
            data: { 
                phone: phone, 
                device_id: devId, 
                sim_slot: slot, 
                message: msg,
                project_id: <?php echo (int)$targetAgencyId; ?>,
                user_id: <?php echo (int)$targetUserId; ?>
            },
            dataType: 'json'
        }).done(function(res){
            btn.prop('disabled', false).html('<i class="fa fa-paper-plane"></i> <?php echo __('Dispatch Test SMS to Android Gateway'); ?>');
            box.show();
            if (res && res.success) {
                box.css({'background': '#fef3c7', 'border': '1px solid #fcd34d', 'color': '#92400e'});
                box.html('<strong><i class="fa fa-check-circle"></i> SMS Queued for Hardware Carrier Dispatch!</strong><br><small>Outbox Ref: ' + (res.outbox_id || 'DISPATCH-QUEUED') + ' | Latency: ' + (res.latency_ms || 0) + 'ms</small>');
            } else {
                box.css({'background': '#fee2e2', 'border': '1px solid #fca5a5', 'color': '#991b1b'});
                box.html('<strong><i class="fa fa-times-circle"></i> SMS Dispatch Failed:</strong><br><small>' + (res && res.error ? res.error : 'Unknown gateway error') + '</small>');
            }
        }).fail(function(){
            btn.prop('disabled', false).html('<i class="fa fa-paper-plane"></i> <?php echo __('Dispatch Test SMS to Android Gateway'); ?>');
            box.show().css({'background': '#fee2e2', 'border': '1px solid #fca5a5', 'color': '#991b1b'}).html('<strong><i class="fa fa-times-circle"></i> Network/Server error occurred.</strong>');
        });
    });

    // -------------------------------------------------------------
    // OUTBOX & MESSAGE QUEUE MANAGER (Super Admin & Agency Admin)
    // -------------------------------------------------------------
    var activeQueueChannel = 'all';
    var activeQueueStatus  = 'queued';
    var queueSearchQuery   = '';

    function updateQueueSelectedCount() {
        var checked = $('.chk-queue-item:checked');
        var count = checked.length;
        $('.selected-queue-count').text(count);
        $('#btnBulkCancelQueue').prop('disabled', count === 0);
        $('#btnBulkDeleteQueue').prop('disabled', count === 0);

        $('.queue-row').removeClass('selected-for-action');
        checked.each(function(){
            $(this).closest('.queue-row').addClass('selected-for-action');
        });
    }

    function filterQueueTable() {
        var rows = $('.queue-row');
        var visibleCount = 0;

        rows.each(function(){
            var row = $(this);
            var ch = (row.data('channel') || '').toString().toLowerCase();
            var st = (row.data('status') || '').toString().toLowerCase();
            var ph = (row.data('phone') || '').toString();
            var nm = (row.data('name') || '').toString();
            var mg = (row.data('msg') || '').toString();

            var matchesChannel = (activeQueueChannel === 'all' || ch === activeQueueChannel);
            var matchesStatus  = false;
            if (activeQueueStatus === 'all') {
                matchesStatus = true;
            } else if (activeQueueStatus === 'queued') {
                matchesStatus = (st === 'queued' || st === 'pending');
            } else {
                matchesStatus = (st === activeQueueStatus);
            }

            var matchesSearch = (queueSearchQuery === '' || ph.indexOf(queueSearchQuery) !== -1 || nm.indexOf(queueSearchQuery) !== -1 || mg.indexOf(queueSearchQuery) !== -1);

            if (matchesChannel && matchesStatus && matchesSearch) {
                row.show();
                visibleCount++;
            } else {
                row.hide();
                row.find('.chk-queue-item').prop('checked', false);
            }
        });

        if (visibleCount === 0) {
            if ($('#queueNoMatchRow').length === 0) {
                $('#tableQueueList tbody').append('<tr id="queueNoMatchRow"><td colspan="10" style="text-align:center; padding: 30px; color: #94a3b8;"><i class="fa fa-info-circle"></i> No queue notifications matching selected filters.</td></tr>');
            } else {
                $('#queueNoMatchRow').show();
            }
        } else {
            $('#queueNoMatchRow').hide();
        }

        updateQueueSelectedCount();
    }

    // Channel Filter Sub-Tabs
    $('.queue-channel-pill').on('click', function(){
        $('.queue-channel-pill').removeClass('active');
        $(this).addClass('active');
        activeQueueChannel = $(this).data('channel');
        filterQueueTable();
    });

    // Status Filter Sub-Tabs
    $('.queue-status-pill').on('click', function(){
        $('.queue-status-pill').removeClass('active');
        $(this).addClass('active');
        activeQueueStatus = $(this).data('status');
        filterQueueTable();
    });

    // Live Search in Queue
    $('#queueSearchInput').on('input', function(){
        queueSearchQuery = $(this).val().toLowerCase().trim();
        filterQueueTable();
    });

    // Select All Checkbox
    $('#chkSelectAllQueue').on('change', function(){
        var isChecked = $(this).is(':checked');
        $('.queue-row:visible .chk-queue-item').prop('checked', isChecked);
        updateQueueSelectedCount();
    });

    $(document).on('change', '.chk-queue-item', function(){
        updateQueueSelectedCount();
    });

    // Refresh Queue Button
    $('#btnRefreshQueue').on('click', function(){
        var url = new URL(window.location.href);
        url.searchParams.set('tab', 'queue');
        window.location.href = url.toString();
    });

    // Action 1: Cancel Single Queue Item
    $(document).on('click', '.js-cancel-queue-item', function(e){
        e.preventDefault();
        var btn = $(this);
        var id = btn.data('id');

        if (!confirm('Cancel queued notification #' + id + '? It will not be dispatched.')) {
            return;
        }

        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

        $.ajax({
            url: '<?php echo $this->Html->url(array('action' => 'ajax_cancel_queue')); ?>',
            type: 'POST',
            data: { id: id },
            dataType: 'json'
        }).done(function(res){
            if (res && res.success) {
                showToast(res.message, false);
                var row = $('#queue-row-' + id);
                row.attr('data-status', 'cancelled').data('status', 'cancelled');
                row.find('.badge-status').css({'background': '#f1f5f9', 'color': '#475569'}).html('<i class="fa fa-ban"></i> Cancelled');
                btn.replaceWith('<button type="button" class="btn btn-default btn-xs js-retry-queue-item" data-id="' + id + '" title="Re-dispatch Notification" style="font-weight: 700; border-radius: 6px; padding: 3px 8px;"><i class="fa fa-repeat text-primary"></i> Retry</button>');

                // Decrement counter if currently filtering queued
                var curTotal = parseInt($('#metricQueueTotal').text() || 0);
                if (curTotal > 0) $('#metricQueueTotal').text(curTotal - 1);
                var curCancelled = parseInt($('#metricQueueCancelled').text() || 0);
                $('#metricQueueCancelled').text(curCancelled + 1);

                if (activeQueueStatus === 'queued') {
                    row.fadeOut(300, function(){ filterQueueTable(); });
                }
            } else {
                showToast(res && res.error ? res.error : 'Failed to cancel queue item', true);
                btn.prop('disabled', false).html('<i class="fa fa-ban"></i> <?php echo __('Cancel'); ?>');
            }
        }).fail(function(){
            showToast('Network error while cancelling queue item.', true);
            btn.prop('disabled', false).html('<i class="fa fa-ban"></i> <?php echo __('Cancel'); ?>');
        });
    });

    // Action 2: Bulk Cancel Selected Queue Items
    $('#btnBulkCancelQueue').on('click', function(e){
        e.preventDefault();
        var checked = $('.chk-queue-item:checked');
        var ids = [];
        checked.each(function(){
            ids.push($(this).val());
        });

        if (ids.length === 0) {
            alert('Please select at least one queue notification to cancel.');
            return;
        }

        if (!confirm('Cancel ' + ids.length + ' selected queued notification(s)?')) {
            return;
        }

        var btn = $(this);
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Cancelling...');

        $.ajax({
            url: '<?php echo $this->Html->url(array('action' => 'ajax_bulk_cancel_queue')); ?>',
            type: 'POST',
            data: { ids: ids },
            dataType: 'json'
        }).done(function(res){
            btn.prop('disabled', false).html('<i class="fa fa-ban"></i> <?php echo __('Cancel Selected'); ?> (<span class="selected-queue-count">0</span>)');
            if (res && res.success) {
                showToast(res.message, false);
                $.each(ids, function(idx, id){
                    var row = $('#queue-row-' + id);
                    row.attr('data-status', 'cancelled').data('status', 'cancelled');
                    row.find('.badge-status').css({'background': '#f1f5f9', 'color': '#475569'}).html('<i class="fa fa-ban"></i> Cancelled');
                    row.find('.js-cancel-queue-item').replaceWith('<button type="button" class="btn btn-default btn-xs js-retry-queue-item" data-id="' + id + '" title="Re-dispatch Notification" style="font-weight: 700; border-radius: 6px; padding: 3px 8px;"><i class="fa fa-repeat text-primary"></i> Retry</button>');
                });

                $('.chk-queue-item').prop('checked', false);
                $('#chkSelectAllQueue').prop('checked', false);
                updateQueueSelectedCount();

                if (activeQueueStatus === 'queued') {
                    filterQueueTable();
                }
            } else {
                showToast(res && res.error ? res.error : 'Failed to cancel selected notifications', true);
            }
        }).fail(function(){
            btn.prop('disabled', false).html('<i class="fa fa-ban"></i> <?php echo __('Cancel Selected'); ?> (<span class="selected-queue-count">0</span>)');
            showToast('Network error performing bulk cancel.', true);
        });
    });

    // Action 3: Delete Single Queue Item
    $(document).on('click', '.js-delete-queue-item', function(e){
        e.preventDefault();
        var btn = $(this);
        var id = btn.data('id');

        if (!confirm('Permanently delete notification #' + id + ' from the queue? This cannot be undone.')) {
            return;
        }

        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

        $.ajax({
            url: '<?php echo $this->Html->url(array('action' => 'ajax_delete_queue')); ?>',
            type: 'POST',
            data: { id: id },
            dataType: 'json'
        }).done(function(res){
            if (res && res.success) {
                showToast(res.message, false);
                $('#queue-row-' + id).fadeOut(300, function(){
                    $(this).remove();
                    filterQueueTable();
                });
            } else {
                showToast(res && res.error ? res.error : 'Failed to delete queue item', true);
                btn.prop('disabled', false).html('<i class="fa fa-trash"></i>');
            }
        }).fail(function(){
            showToast('Network error while deleting queue item.', true);
            btn.prop('disabled', false).html('<i class="fa fa-trash"></i>');
        });
    });

    // Action 4: Bulk Delete Selected Queue Items
    $('#btnBulkDeleteQueue').on('click', function(e){
        e.preventDefault();
        var checked = $('.chk-queue-item:checked');
        var ids = [];
        checked.each(function(){
            ids.push($(this).val());
        });

        if (ids.length === 0) {
            alert('Please select at least one queue notification to delete.');
            return;
        }

        if (!confirm('Permanently delete ' + ids.length + ' selected notification(s) from queue? This cannot be undone.')) {
            return;
        }

        var btn = $(this);
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Deleting...');

        $.ajax({
            url: '<?php echo $this->Html->url(array('action' => 'ajax_bulk_delete_queue')); ?>',
            type: 'POST',
            data: { ids: ids },
            dataType: 'json'
        }).done(function(res){
            btn.prop('disabled', false).html('<i class="fa fa-trash"></i> <?php echo __('Delete Selected'); ?> (<span class="selected-queue-count">0</span>)');
            if (res && res.success) {
                showToast(res.message, false);
                $.each(ids, function(idx, id){
                    $('#queue-row-' + id).remove();
                });
                $('.chk-queue-item').prop('checked', false);
                $('#chkSelectAllQueue').prop('checked', false);
                updateQueueSelectedCount();
                filterQueueTable();
            } else {
                showToast(res && res.error ? res.error : 'Failed to delete selected notifications', true);
            }
        }).fail(function(){
            btn.prop('disabled', false).html('<i class="fa fa-trash"></i> <?php echo __('Delete Selected'); ?> (<span class="selected-queue-count">0</span>)');
            showToast('Network error performing bulk delete.', true);
        });
    });

    // Action 5: Re-dispatch / Retry Queue Item
    $(document).on('click', '.js-retry-queue-item', function(e){
        e.preventDefault();
        var btn = $(this);
        var id = btn.data('id');

        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Retrying...');

        $.ajax({
            url: '<?php echo $this->Html->url(array('action' => 'ajax_retry_queue')); ?>',
            type: 'POST',
            data: { id: id },
            dataType: 'json'
        }).done(function(res){
            btn.prop('disabled', false).html('<i class="fa fa-repeat text-primary"></i> Retry');
            if (res && res.success) {
                showToast(res.message, false);
                var row = $('#queue-row-' + id);
                row.attr('data-status', res.status).data('status', res.status);
                var isSent = (res.status === 'sent');
                row.find('.badge-status').css({
                    'background': isSent ? '#dcfce7' : '#fef3c7',
                    'color': isSent ? '#166534' : '#92400e'
                }).html('<i class="fa ' + (isSent ? 'fa-check' : 'fa-clock-o') + '"></i> ' + (isSent ? 'Sent' : 'Queued'));
            } else {
                showToast(res && res.error ? res.error : 'Failed to retry notification', true);
            }
        }).fail(function(){
            btn.prop('disabled', false).html('<i class="fa fa-repeat text-primary"></i> Retry');
            showToast('Network error retrying notification.', true);
        });
    });

    // Recent logs real-time search
    $('#recentLogsSearch').on('input', function(){
        var q = $(this).val().toLowerCase().trim();
        $('#tab-logs table tbody tr').each(function(){
            var text = $(this).text().toLowerCase();
            $(this).toggle(q === '' || text.indexOf(q) !== -1);
        });
    });

    // Tab switching and hash/query persistence
    $('.lb-nav-tabs a[data-toggle="tab"]').on('click', function(e){
        e.preventDefault();
        $(this).tab('show');
        var hash = $(this).attr('href');
        var tabKey = hash.replace('#tab-', '');
        if (history.replaceState) {
            var url = new URL(window.location.href);
            url.searchParams.set('tab', tabKey);
            history.replaceState(null, '', url.toString());
        }
    });

    // Check URL query tab or hash on page load
    var urlParams = new URLSearchParams(window.location.search);
    var initialTab = urlParams.get('tab') || '<?php echo h($activeTab); ?>';
    if (window.location.hash) {
        var hTab = window.location.hash.replace('#tab-', '').replace('#', '');
        if ($('#tab-' + hTab).length) {
            initialTab = hTab;
        }
    }
    if (initialTab && $('#tab-' + initialTab).length) {
        $('.lb-nav-tabs a[href="#tab-' + initialTab + '"]').tab('show');
    }

    // Initial filter apply on page load
    filterQueueTable();
})(jQuery);
</script>
