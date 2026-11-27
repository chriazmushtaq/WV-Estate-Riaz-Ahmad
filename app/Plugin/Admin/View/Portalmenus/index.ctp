<style type="text/css">
.wv-pm-container {
    padding: 10px 0 30px;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
}

/* Executive Header */
.wv-pm-header {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-top: 4px solid #0284c7;
    border-radius: 14px;
    padding: 22px 24px;
    margin-bottom: 20px;
    box-shadow: 0 4px 18px rgba(15, 23, 42, 0.05);
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
}
.wv-pm-header-info h1 {
    font-size: 22px;
    font-weight: 900;
    margin: 0 0 6px;
    color: #0f172a !important;
    display: flex;
    align-items: center;
    gap: 10px;
}
.wv-pm-header-info h1 i {
    color: #0284c7;
}
.wv-pm-header-info p {
    font-size: 13.5px;
    color: #64748b;
    margin: 0;
    max-width: 680px;
    line-height: 1.5;
}
.wv-pm-agency-badge {
    background: #e0f2fe;
    color: #0369a1;
    border: 1px solid #bae6fd;
    font-size: 11px;
    font-weight: 800;
    padding: 4px 12px;
    border-radius: 999px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

/* Agency Selector Toolbar */
.wv-pm-toolbar {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 16px 20px;
    margin-bottom: 20px;
    box-shadow: 0 2px 10px rgba(15, 23, 42, 0.04);
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
}
.wv-pm-agency-select-wrap {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}
.wv-pm-agency-select-wrap label {
    font-size: 12px;
    font-weight: 800;
    color: #475569;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    margin: 0;
}
.wv-pm-select {
    height: 38px;
    border-radius: 8px;
    border: 1.5px solid #cbd5e1;
    background: #f8fafc;
    color: #0f172a;
    font-size: 13px;
    font-weight: 700;
    padding: 0 14px;
    min-width: 260px;
    outline: none;
    transition: all 0.2s ease;
}
.wv-pm-select:focus {
    border-color: #0284c7;
    background: #ffffff;
    box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15);
}

/* KPI Summary Metric Cards */
.wv-pm-stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 14px;
    margin-bottom: 22px;
}
.wv-pm-stat {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 16px 18px;
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
    position: relative;
    overflow: hidden;
}
.wv-pm-stat .lbl {
    display: block;
    font-size: 11px;
    font-weight: 800;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    margin-bottom: 4px;
}
.wv-pm-stat .val {
    font-size: 24px;
    font-weight: 900;
    line-height: 1.15;
    color: #0f172a;
    display: block;
}
.wv-pm-stat.stat-enabled .val {
    color: #059669;
}
.wv-pm-stat.stat-disabled .val {
    color: #dc2626;
}
.wv-pm-stat.stat-agency .val {
    font-size: 16px;
    color: #0284c7;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.wv-pm-stat .sub {
    font-size: 11px;
    color: #64748b;
    font-weight: 600;
    margin-top: 4px;
    display: block;
}

/* Actions Bar & Quick Search */
.wv-pm-actions-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 16px;
}
.wv-pm-search-input {
    height: 38px;
    border-radius: 8px;
    border: 1.5px solid #cbd5e1;
    background: #ffffff;
    padding: 0 14px 0 34px;
    font-size: 13px;
    font-weight: 600;
    color: #0f172a;
    width: 250px;
    transition: all 0.2s ease;
}
.wv-pm-search-input:focus {
    border-color: #0284c7;
    box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.12);
    outline: none;
}
.wv-pm-search-wrap {
    position: relative;
}
.wv-pm-search-wrap i {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    font-size: 13px;
}
.wv-pm-bulk-btns {
    display: flex;
    align-items: center;
    gap: 8px;
}

/* Menus Table / List Container */
.wv-pm-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    box-shadow: 0 4px 18px rgba(15, 23, 42, 0.04);
    overflow: hidden;
    margin-bottom: 24px;
}
.wv-pm-table {
    width: 100%;
    border-collapse: collapse;
    margin: 0;
}
.wv-pm-table thead th {
    background: #0f172a;
    color: #ffffff;
    font-size: 11.5px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 14px 18px;
    border-bottom: 1px solid #1e293b;
    white-space: nowrap;
}
.wv-pm-table tbody td {
    padding: 16px 18px;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
}
.wv-pm-table tbody tr:hover td {
    background: #f8fafc;
}
.wv-pm-table tbody tr:last-child td {
    border-bottom: none;
}

/* Menu Identity Box */
.wv-pm-menu-identity {
    display: flex;
    align-items: center;
    gap: 14px;
}
.wv-pm-menu-icon {
    width: 42px;
    height: 42px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
    border: 1px solid transparent;
}
.wv-pm-menu-name {
    font-size: 15px;
    font-weight: 900;
    color: #0f172a;
    margin: 0 0 3px;
    line-height: 1.25;
}
.wv-pm-menu-route {
    font-size: 11.5px;
    color: #64748b;
    font-family: monospace;
    font-weight: 700;
}
.wv-pm-menu-desc {
    font-size: 12.5px;
    color: #475569;
    line-height: 1.45;
    max-width: 440px;
}

/* Status Badges */
.wv-pm-status-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 11px;
    font-weight: 800;
    padding: 4px 10px;
    border-radius: 999px;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    white-space: nowrap;
}
.wv-pm-status-pill.is-active {
    background: #ecfdf5;
    color: #059669;
    border: 1px solid #a7f3d0;
}
.wv-pm-status-pill.is-inactive {
    background: #fee2e2;
    color: #b91c1c;
    border: 1px solid #fecaca;
}

/* Modern iOS Style Toggle Switch */
.wv-toggle-switch {
    position: relative;
    display: inline-block;
    width: 50px;
    height: 28px;
    margin: 0;
}
.wv-toggle-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}
.wv-toggle-slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #cbd5e1;
    transition: .25s cubic-bezier(0.4, 0, 0.2, 1);
    border-radius: 34px;
}
.wv-toggle-slider:before {
    position: absolute;
    content: "";
    height: 22px;
    width: 22px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: .25s cubic-bezier(0.4, 0, 0.2, 1);
    border-radius: 50%;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
}
input:checked + .wv-toggle-slider {
    background-color: #10b981;
}
input:focus + .wv-toggle-slider {
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
}
input:checked + .wv-toggle-slider:before {
    transform: translateX(22px);
}

/* Drag & Drop Reordering Styles */
.wv-drag-handle {
    cursor: grab;
    color: #94a3b8;
    padding: 6px 8px;
    border-radius: 6px;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    user-select: none;
    font-size: 13px;
}
.wv-drag-handle:hover {
    cursor: grab;
    color: #0284c7;
    background: #e0f2fe;
}
.wv-drag-handle:active {
    cursor: grabbing;
}
.wv-order-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: #f1f5f9;
    color: #334155;
    font-size: 11px;
    font-weight: 800;
    border: 1px solid #e2e8f0;
}
.wv-btn-move {
    border-radius: 6px;
    padding: 4px 7px;
    color: #475569;
    font-size: 11px;
    border: 1px solid #cbd5e1;
    background: #ffffff;
    transition: all 0.15s ease;
}
.wv-btn-move:hover {
    background: #0284c7;
    color: #ffffff;
    border-color: #0284c7;
}
.wv-menu-row.is-dragging {
    opacity: 0.35;
    background: #e0f2fe !important;
}
.wv-menu-row.is-drag-over-top {
    border-top: 3px solid #0284c7 !important;
}
.wv-menu-row.is-drag-over-bottom {
    border-bottom: 3px solid #0284c7 !important;
}

/* Toast Alert Feedback */
#wvToastAlert {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 999999;
    background: #0f172a;
    color: #ffffff;
    padding: 12px 20px;
    border-radius: 10px;
    box-shadow: 0 8px 24px rgba(15, 23, 42, 0.25);
    font-size: 13px;
    font-weight: 700;
    display: none;
    align-items: center;
    gap: 10px;
    transition: all 0.2s ease;
}

@media (max-width: 991px) {
    .wv-pm-stats {
        grid-template-columns: repeat(2, 1fr);
    }
}
@media (max-width: 768px) {
    .wv-pm-header {
        flex-direction: column;
        align-items: flex-start;
    }
    .wv-pm-toolbar {
        flex-direction: column;
        align-items: stretch;
    }
    .wv-pm-select {
        width: 100%;
    }
    .wv-pm-stats {
        grid-template-columns: 1fr;
    }
    .wv-pm-search-input {
        width: 100%;
    }
    .wv-pm-table thead {
        display: none;
    }
    .wv-pm-table, .wv-pm-table tbody, .wv-pm-table tr, .wv-pm-table td {
        display: block;
        width: 100%;
    }
    .wv-pm-table tr {
        border-bottom: 2px solid #e2e8f0;
        padding: 14px 0;
    }
    .wv-pm-table td {
        padding: 8px 16px;
        border: none;
    }
    .wv-pm-toggle-cell {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-top: 10px !important;
        border-top: 1px solid #f1f5f9 !important;
        margin-top: 8px;
    }
}
</style>

<div class="wv-pm-container">
    <?php echo $this->Session->flash(); ?>

    <!-- Executive Header Card -->
    <div class="wv-pm-header">
        <div class="wv-pm-header-info">
            <span class="wv-pm-agency-badge">
                <i class="fa fa-shield"></i> <?php echo __('Client Portal Access Control'); ?>
            </span>
            <h1>
                <i class="fa fa-sliders"></i> <?php echo __('Agency-Based Client Portal Menu Access'); ?>
            </h1>
            <p>
                <?php echo __('Configure which sidebar menu items are visible and accessible to clients on a per-agency basis. Turning a menu toggle OFF immediately hides it from the sidebar and strictly forbids client direct URL access.'); ?>
            </p>
        </div>
        <div>
            <?php echo $this->Html->link('<i class="fa fa-cogs"></i> ' . __('General Settings'), array('controller' => 'Configurations', 'action' => 'index'), array('escape' => false, 'class' => 'btn btn-default btn-sm', 'style' => 'font-weight:800; border-radius:8px;')); ?>
        </div>
    </div>

    <!-- Agency Selector Toolbar -->
    <div class="wv-pm-toolbar">
        <div class="wv-pm-agency-select-wrap">
            <label for="agencySelector"><i class="fa fa-building-o"></i> <?php echo __('Select Target Agency:'); ?></label>
            <?php if ($isSuper): ?>
                <select id="agencySelector" class="wv-pm-select" onchange="wvSwitchAgency(this.value);">
                    <option value="0" <?php echo ($selectedAgencyId === 0) ? 'selected' : ''; ?>>
                        <?php echo __('Global Default (All Agencies)'); ?>
                    </option>
                    <?php foreach ($agencyList as $id => $name): ?>
                        <option value="<?php echo (int)$id; ?>" <?php echo ($selectedAgencyId === (int)$id) ? 'selected' : ''; ?>>
                            <?php echo h($name); ?> (ID #<?php echo (int)$id; ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php else: ?>
                <span class="badge" style="background:#0f172a; color:#fff; font-size:13px; font-weight:800; padding:8px 14px; border-radius:8px;">
                    <i class="fa fa-building"></i> <?php echo h($selectedAgencyName); ?>
                </span>
            <?php endif; ?>
        </div>

        <div class="wv-pm-bulk-btns">
            <?php echo $this->Form->create('BulkToggle', array('url' => array('controller' => 'Portalmenus', 'action' => 'bulk_toggle'), 'style' => 'display:inline-flex; gap:8px;')); ?>
            <input type="hidden" name="agency_id" value="<?php echo (int)$selectedAgencyId; ?>">
            <button type="submit" name="bulk_action" value="enable_all" class="btn btn-sm btn-success" style="font-weight:800; border-radius:8px;" onclick="return confirm('<?php echo __('Enable all 14 portal menus for this agency?'); ?>');">
                <i class="fa fa-check-circle"></i> <?php echo __('Enable All'); ?>
            </button>
            <button type="submit" name="bulk_action" value="disable_all" class="btn btn-sm btn-danger" style="font-weight:800; border-radius:8px;" onclick="return confirm('<?php echo __('Disable all portal menus for this agency? Clients will be blocked from accessing these pages.'); ?>');">
                <i class="fa fa-ban"></i> <?php echo __('Disable All'); ?>
            </button>
            <?php echo $this->Form->end(); ?>
        </div>
    </div>

    <!-- Summary Metrics -->
    <div class="wv-pm-stats">
        <div class="wv-pm-stat stat-agency">
            <span class="lbl"><?php echo __('Active Scope'); ?></span>
            <strong class="val"><?php echo h($selectedAgencyName); ?></strong>
            <span class="sub"><?php echo ($selectedAgencyId === 0) ? __('Applies to all agencies by default') : __('Customized for this agency'); ?></span>
        </div>
        <div class="wv-pm-stat">
            <span class="lbl"><?php echo __('Total Portal Menus'); ?></span>
            <strong class="val"><?php echo count($menus); ?></strong>
            <span class="sub"><?php echo __('Configurable portal features'); ?></span>
        </div>
        <div class="wv-pm-stat stat-enabled">
            <span class="lbl"><?php echo __('Live on Portal (ON)'); ?></span>
            <strong class="val" id="statEnabledCount"><?php echo (int)$enabledCount; ?></strong>
            <span class="sub"><?php echo __('Accessible to clients'); ?></span>
        </div>
        <div class="wv-pm-stat stat-disabled">
            <span class="lbl"><?php echo __('Disabled & Blocked (OFF)'); ?></span>
            <strong class="val" id="statDisabledCount"><?php echo (int)$disabledCount; ?></strong>
            <span class="sub"><?php echo __('Hidden from sidebar & blocked'); ?></span>
        </div>
    </div>

    <!-- Search & Quick Filters -->
    <div class="wv-pm-actions-bar">
        <div class="wv-pm-search-wrap">
            <i class="fa fa-search"></i>
            <input type="text" id="wvMenuSearch" class="wv-pm-search-input" placeholder="<?php echo __('Filter menus by name or route...'); ?>" onkeyup="wvFilterMenus();">
        </div>
        <div>
            <span style="font-size:12px; color:#64748b; font-weight:700;">
                <i class="fa fa-arrows text-info"></i> <?php echo __('Drag and drop rows or use arrow buttons to re-order menus on the client portal sidebar.'); ?>
            </span>
        </div>
    </div>

    <!-- Main Menus Table with Toggles Form -->
    <?php echo $this->Form->create('AgencyPortalMenu', array('url' => array('controller' => 'Portalmenus', 'action' => 'index', '?' => array('agency_id' => $selectedAgencyId)), 'id' => 'wvPortalMenuForm')); ?>
    <input type="hidden" name="data[AgencyPortalMenu][agency_id]" value="<?php echo (int)$selectedAgencyId; ?>">
    <input type="hidden" name="data[ordered_keys]" id="wvOrderedKeys" value="">

    <div class="wv-pm-card">
        <table class="wv-pm-table" id="wvMenusTable">
            <thead>
                <tr>
                    <th style="width:105px; text-align:center;"><i class="fa fa-arrows"></i> <?php echo __('Drag / #'); ?></th>
                    <th style="width:280px;"><?php echo __('Portal Menu Item'); ?></th>
                    <th><?php echo __('Feature Purpose & Access Scope'); ?></th>
                    <th style="width:130px; text-align:center;"><?php echo __('Current Status'); ?></th>
                    <th style="width:110px; text-align:center;"><?php echo __('Toggle Access'); ?></th>
                    <th style="width:95px; text-align:center;"><?php echo __('Move'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $sr = 1;
                foreach ($menus as $key => $m): 
                    $isEnabled = !empty($m['is_enabled']);
                    $iconColor = !empty($m['color']) ? $m['color'] : '#0284c7';
                    $iconBg = !empty($m['bg']) ? $m['bg'] : '#e0f2fe';
                ?>
                <tr class="wv-menu-row" draggable="true" data-key="<?php echo h($key); ?>" data-name="<?php echo strtolower(h($m['name'] . ' ' . $m['controller'])); ?>" id="row_<?php echo h($key); ?>">
                    <td style="text-align:center;">
                        <div style="display:flex; align-items:center; justify-content:center; gap:6px;">
                            <span class="wv-drag-handle" title="<?php echo __('Click and drag up/down to reorder'); ?>">
                                <i class="fa fa-bars"></i>
                            </span>
                            <span class="wv-order-badge"><?php echo $sr; ?></span>
                            <input type="hidden" class="wv-order-val" name="data[MenuOrder][<?php echo h($key); ?>]" value="<?php echo $sr; ?>">
                        </div>
                    </td>
                    <td>
                        <div class="wv-pm-menu-identity">
                            <div class="wv-pm-menu-icon" style="background:<?php echo h($iconBg); ?>; color:<?php echo h($iconColor); ?>;">
                                <i class="<?php echo h($m['icon']); ?>"></i>
                            </div>
                            <div>
                                <h4 class="wv-pm-menu-name"><?php echo h($m['name']); ?></h4>
                                <span class="wv-pm-menu-route">/<?php echo h($m['controller']); ?></span>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="wv-pm-menu-desc"><?php echo h($m['description']); ?></div>
                    </td>
                    <td style="text-align:center;" class="wv-status-cell">
                        <span class="wv-pm-status-pill <?php echo $isEnabled ? 'is-active' : 'is-inactive'; ?>" id="status-badge-<?php echo h($key); ?>">
                            <i class="fa <?php echo $isEnabled ? 'fa-check-circle' : 'fa-ban'; ?>"></i>
                            <span class="badge-text"><?php echo $isEnabled ? __('LIVE') : __('OFF'); ?></span>
                        </span>
                    </td>
                    <td style="text-align:center;" class="wv-pm-toggle-cell">
                        <label class="wv-toggle-switch" title="<?php echo __('Toggle sidebar visibility & page access'); ?>">
                            <input type="checkbox" 
                                   name="data[MenuToggle][<?php echo h($key); ?>]" 
                                   value="1" 
                                   class="wv-menu-toggle-input" 
                                   data-key="<?php echo h($key); ?>" 
                                   data-agency="<?php echo (int)$selectedAgencyId; ?>" 
                                   <?php echo $isEnabled ? 'checked="checked"' : ''; ?> 
                                   onchange="wvAjaxToggle(this);">
                            <span class="wv-toggle-slider"></span>
                        </label>
                    </td>
                    <td style="text-align:center;">
                        <div class="btn-group" role="group" style="display:inline-flex; gap:3px;">
                            <button type="button" class="btn btn-default btn-xs wv-btn-move" onclick="wvMoveRow(this, -1);" title="<?php echo __('Move Up'); ?>">
                                <i class="fa fa-arrow-up"></i>
                            </button>
                            <button type="button" class="btn btn-default btn-xs wv-btn-move" onclick="wvMoveRow(this, 1);" title="<?php echo __('Move Down'); ?>">
                                <i class="fa fa-arrow-down"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php $sr++; endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Bottom Submit Bar -->
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
        <div style="font-size:12.5px; color:#64748b;">
            <i class="fa fa-shield text-success"></i> <?php echo __('Disabled menus are automatically removed from client sidebar. Re-ordered menus display on client portal in this exact order.'); ?>
        </div>
        <button type="submit" class="btn btn-primary" style="font-weight:900; border-radius:10px; padding:10px 24px; font-size:13.5px; box-shadow:0 4px 14px rgba(2, 132, 199, 0.25);">
            <i class="fa fa-save"></i> <?php echo __('Save All Menu Settings'); ?>
        </button>
    </div>

    <?php echo $this->Form->end(); ?>
</div>

<!-- Realtime Floating Toast Notification -->
<div id="wvToastAlert">
    <i class="fa fa-check-circle" style="color:#10b981; font-size:16px;"></i>
    <span id="wvToastMsg">Setting saved</span>
</div>

<script type="text/javascript">
function wvSwitchAgency(agencyId) {
    var url = '<?php echo $this->Html->url(array("controller" => "Portalmenus", "action" => "index")); ?>';
    window.location.href = url + '?agency_id=' + encodeURIComponent(agencyId);
}

function wvFilterMenus() {
    var q = document.getElementById('wvMenuSearch').value.toLowerCase().trim();
    var rows = document.querySelectorAll('.wv-menu-row');
    rows.forEach(function(row) {
        var text = row.getAttribute('data-name');
        if (!q || text.indexOf(q) !== -1) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function wvAjaxToggle(inputEl) {
    var key = inputEl.getAttribute('data-key');
    var agencyId = inputEl.getAttribute('data-agency');
    var isEnabled = inputEl.checked ? 1 : 0;
    var badge = document.getElementById('status-badge-' + key);

    // Immediate optimistic badge update
    if (badge) {
        if (isEnabled) {
            badge.className = 'wv-pm-status-pill is-active';
            badge.innerHTML = '<i class="fa fa-check-circle"></i> <span class="badge-text"><?php echo __("LIVE"); ?></span>';
        } else {
            badge.className = 'wv-pm-status-pill is-inactive';
            badge.innerHTML = '<i class="fa fa-ban"></i> <span class="badge-text"><?php echo __("OFF"); ?></span>';
        }
    }

    wvRecountStats();

    var xhr = new XMLHttpRequest();
    xhr.open('POST', '<?php echo $this->Html->url(array("controller" => "Portalmenus", "action" => "toggle")); ?>', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

    var params = 'agency_id=' + encodeURIComponent(agencyId) + 
                 '&menu_key=' + encodeURIComponent(key) + 
                 '&is_enabled=' + encodeURIComponent(isEnabled);

    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                try {
                    var res = JSON.parse(xhr.responseText);
                    if (res.status === 'success') {
                        wvShowToast(res.message || 'Updated successfully', isEnabled ? '#10b981' : '#ef4444');
                    } else {
                        wvShowToast(res.message || 'Update error', '#ef4444');
                    }
                } catch(e) {
                    wvShowToast('Updated', '#10b981');
                }
            } else {
                wvShowToast('Failed to save toggle state. Please try again.', '#ef4444');
            }
        }
    };
    xhr.send(params);
}

function wvRecountStats() {
    var inputs = document.querySelectorAll('.wv-menu-toggle-input');
    var enabled = 0;
    var disabled = 0;
    inputs.forEach(function(i) {
        if (i.checked) {
            enabled++;
        } else {
            disabled++;
        }
    });
    var statE = document.getElementById('statEnabledCount');
    var statD = document.getElementById('statDisabledCount');
    if (statE) statE.innerText = enabled;
    if (statD) statD.innerText = disabled;
}

var toastTimeout = null;
function wvShowToast(msg, iconColor) {
    var toast = document.getElementById('wvToastAlert');
    var toastMsg = document.getElementById('wvToastMsg');
    if (!toast) return;

    toastMsg.innerText = msg;
    var icon = toast.querySelector('i');
    if (icon) {
        icon.style.color = iconColor || '#10b981';
    }

    toast.style.display = 'flex';
    if (toastTimeout) clearTimeout(toastTimeout);
    toastTimeout = setTimeout(function() {
        toast.style.display = 'none';
    }, 3200);
}

// --- Drag & Drop and Move Up/Down Implementation ---
var dragSourceRow = null;

function wvInitDragAndDrop() {
    var tbody = document.querySelector('#wvMenusTable tbody');
    if (!tbody) return;

    var rows = tbody.querySelectorAll('.wv-menu-row');
    rows.forEach(function(row) {
        row.setAttribute('draggable', 'true');

        row.addEventListener('dragstart', function(e) {
            dragSourceRow = this;
            this.classList.add('is-dragging');
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', this.getAttribute('data-key'));
        });

        row.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            if (dragSourceRow && dragSourceRow !== this) {
                var rect = this.getBoundingClientRect();
                var midY = rect.top + rect.height / 2;
                if (e.clientY < midY) {
                    this.classList.add('is-drag-over-top');
                    this.classList.remove('is-drag-over-bottom');
                } else {
                    this.classList.add('is-drag-over-bottom');
                    this.classList.remove('is-drag-over-top');
                }
            }
        });

        row.addEventListener('dragleave', function() {
            this.classList.remove('is-drag-over-top');
            this.classList.remove('is-drag-over-bottom');
        });

        row.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('is-drag-over-top');
            this.classList.remove('is-drag-over-bottom');
            if (dragSourceRow && dragSourceRow !== this) {
                var rect = this.getBoundingClientRect();
                var midY = rect.top + rect.height / 2;
                if (e.clientY < midY) {
                    this.parentNode.insertBefore(dragSourceRow, this);
                } else {
                    this.parentNode.insertBefore(dragSourceRow, this.nextSibling);
                }
                wvUpdateOrderAndSave(true);
            }
        });

        row.addEventListener('dragend', function() {
            this.classList.remove('is-dragging');
            tbody.querySelectorAll('.wv-menu-row').forEach(function(r) {
                r.classList.remove('is-drag-over-top');
                r.classList.remove('is-drag-over-bottom');
            });
            dragSourceRow = null;
        });
    });
}

function wvMoveRow(btn, delta) {
    var row = btn.closest('.wv-menu-row');
    if (!row) return;

    if (delta === -1 && row.previousElementSibling) {
        row.parentNode.insertBefore(row, row.previousElementSibling);
        wvUpdateOrderAndSave(true);
    } else if (delta === 1 && row.nextElementSibling) {
        row.parentNode.insertBefore(row.nextElementSibling, row);
        wvUpdateOrderAndSave(true);
    }
}

function wvUpdateOrderAndSave(sendAjax) {
    var tbody = document.querySelector('#wvMenusTable tbody');
    if (!tbody) return;

    var rows = tbody.querySelectorAll('.wv-menu-row');
    var orderedKeys = [];
    var idx = 1;

    rows.forEach(function(row) {
        var key = row.getAttribute('data-key');
        if (key) {
            orderedKeys.push(key);
            var badge = row.querySelector('.wv-order-badge');
            if (badge) badge.innerText = idx;
            var hidden = row.querySelector('.wv-order-val');
            if (hidden) hidden.value = idx;
            idx++;
        }
    });

    var orderedInput = document.getElementById('wvOrderedKeys');
    if (orderedInput) {
        orderedInput.value = orderedKeys.join(',');
    }

    if (sendAjax) {
        var agencyId = '<?php echo (int)$selectedAgencyId; ?>';
        var postData = 'agency_id=' + encodeURIComponent(agencyId) + '&order=' + encodeURIComponent(orderedKeys.join(','));

        var xhr = new XMLHttpRequest();
        xhr.open('POST', '<?php echo $this->Html->url(array("controller" => "Portalmenus", "action" => "reorder")); ?>', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    try {
                        var res = JSON.parse(xhr.responseText);
                        if (res.status === 'success') {
                            wvShowToast(res.message || 'Menu order saved! Sidebar updated.', '#0284c7');
                        }
                    } catch(e) {
                        wvShowToast('Menu order updated!', '#0284c7');
                    }
                }
            }
        };
        xhr.send(postData);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    wvInitDragAndDrop();
    wvUpdateOrderAndSave(false);
});
</script>
