<?php
$this->Js->JqueryEngine->jQueryObject = 'jQuery';
// Paginator options
$this->Paginator->options(array(
  'update' => '#resultDiv',
  'evalScripts' => true,
));
$currency = isset($currency) ? $currency : '';
$propertyLimitUrl = '/admin/Properties/index';
if(isset($this->params['named']) && is_array($this->params['named']))
{
    foreach($this->params['named'] as $propertyLimitKey=>$propertyLimitValue)
    {
        if(in_array($propertyLimitKey,array('limit','page'),true))
        {
            continue;
        }
        $propertyLimitUrl .= '/'.rawurlencode($propertyLimitKey).':'.rawurlencode($propertyLimitValue);
    }
    unset($propertyLimitKey,$propertyLimitValue);
}

// Controller and action URLs
$url = $this->Html->url(array('controller'=>'Properties'));
$bulkUrl = $this->Html->url(array('controller'=>'Properties','action'=>'bulkupdate'));

// Paginator total count
$page_params = $this->Paginator->params();
$totalCount = isset($page_params['count']) ? (int)$page_params['count'] : (isset($Property) ? count($Property) : 0);

// Active filters count detection (across named params, POST data, and GET query params)
$activeFilterCount = 0;
$filterKeys = array('general', 'location', 'type', 'contract', 'start_price', 'end_price', 'bedroom', 'bathroom', 'Area', 'publish', 'skeyword');
$countedKeys = array();
foreach ($filterKeys as $fk) {
    $canonicalKey = strtolower($fk);
    if (isset($countedKeys[$canonicalKey])) {
        continue;
    }
    $val = '';
    if (isset($this->params['named'][$fk]) && trim((string)$this->params['named'][$fk]) !== '') {
        $val = (string)$this->params['named'][$fk];
    } elseif (isset($this->params['named'][$canonicalKey]) && trim((string)$this->params['named'][$canonicalKey]) !== '') {
        $val = (string)$this->params['named'][$canonicalKey];
    } elseif (isset($this->request->data[$fk]) && !is_array($this->request->data[$fk]) && trim((string)$this->request->data[$fk]) !== '') {
        $val = (string)$this->request->data[$fk];
    } elseif (isset($this->request->data['Property'][$fk]) && !is_array($this->request->data['Property'][$fk]) && trim((string)$this->request->data['Property'][$fk]) !== '') {
        $val = (string)$this->request->data['Property'][$fk];
    } elseif (isset($this->request->data[$canonicalKey]) && !is_array($this->request->data[$canonicalKey]) && trim((string)$this->request->data[$canonicalKey]) !== '') {
        $val = (string)$this->request->data[$canonicalKey];
    } elseif (isset($this->request->query[$fk]) && trim((string)$this->request->query[$fk]) !== '') {
        $val = (string)$this->request->query[$fk];
    } elseif (isset($this->request->query[$canonicalKey]) && trim((string)$this->request->query[$canonicalKey]) !== '') {
        $val = (string)$this->request->query[$canonicalKey];
    }
    if (trim($val) !== '') {
        $activeFilterCount++;
        $countedKeys[$canonicalKey] = true;
    }
}
$isFilterPanelExpanded = ($activeFilterCount > 0);
?>
<style>
/* High-Density SaaS Table Styles */
.wv-pro-table {
    width: 100% !important;
    margin-bottom: 0 !important;
}

.wv-pro-table > thead > tr > th {
    background-color: #f8fafc !important;
    border-bottom: 2px solid #e2e8f0 !important;
    color: #334155 !important;
    font-size: 11.5px !important;
    font-weight: 700 !important;
    letter-spacing: 0.3px !important;
    line-height: 1.2 !important;
    padding: 8px 8px !important;
    text-transform: uppercase !important;
    vertical-align: middle !important;
    white-space: nowrap !important;
}

.wv-pro-table > thead > tr > th.property-action-header {
    background-color: #082f49 !important;
    color: #ffffff !important;
}

.wv-pro-table > thead > tr > th a {
    color: #1e293b !important;
    text-decoration: none !important;
}

.wv-pro-table > thead > tr > th a:hover {
    color: #2563eb !important;
}

.wv-pro-table > tbody > tr > td {
    padding: 6px 8px !important;
    font-size: 12.5px !important;
    line-height: 1.25 !important;
    vertical-align: middle !important;
    color: #1e293b !important;
}

/* Tight Column Widths */
.wv-col-checkbox {
    width: 36px !important;
    max-width: 36px !important;
    text-align: center !important;
    padding: 6px 4px !important;
}

.wv-col-num {
    width: 42px !important;
    max-width: 48px !important;
    text-align: center !important;
    font-variant-numeric: tabular-nums !important;
    font-weight: 600 !important;
    color: #64748b !important;
    padding: 6px 4px !important;
}

.wv-col-office-id {
    width: 1% !important;
    white-space: nowrap !important;
}

.wv-col-office-id code {
    background: #f1f5f9;
    color: #0f172a;
    font-size: 11.5px;
    font-weight: 700;
    padding: 2px 6px;
    border-radius: 4px;
    border: 1px solid #e2e8f0;
}

.wv-col-name {
    white-space: nowrap !important;
    font-weight: 600 !important;
    color: #0f172a !important;
}

.wv-col-price {
    width: 1% !important;
    white-space: nowrap !important;
    font-weight: 700 !important;
    color: #0f3b63 !important;
    font-variant-numeric: tabular-nums !important;
}

.wv-col-type,
.wv-col-contract {
    width: 1% !important;
    white-space: nowrap !important;
    color: #334155 !important;
}

.wv-col-status {
    width: 1% !important;
    white-space: nowrap !important;
    text-align: center !important;
}

.wv-col-flags {
    width: 1% !important;
    white-space: nowrap !important;
    text-align: center !important;
}

.wv-col-quality {
    width: 1% !important;
    white-space: nowrap !important;
}

.wv-col-action {
    width: 1% !important;
    white-space: nowrap !important;
    text-align: center !important;
}

/* Professional Status Badge with Icon */
.wv-status-badge {
    align-items: center !important;
    border-radius: 999px !important;
    display: inline-flex !important;
    font-size: 11px !important;
    font-weight: 700 !important;
    gap: 4px !important;
    letter-spacing: 0.2px !important;
    line-height: 1 !important;
    padding: 4px 8px !important;
    text-transform: uppercase !important;
    white-space: nowrap !important;
}

.wv-status-available {
    background-color: #dcfce7 !important;
    border: 1px solid #bbf7d0 !important;
    color: #15803d !important;
}

.wv-status-available i {
    color: #16a34a !important;
    font-size: 11px !important;
}

.wv-status-sold {
    background-color: #fee2e2 !important;
    border: 1px solid #fecaca !important;
    color: #b91c1c !important;
}

.wv-status-sold i {
    color: #dc2626 !important;
    font-size: 11px !important;
}

/* Professional Featured/Published Icon Buttons */
.wv-flags-inline {
    align-items: center !important;
    display: inline-flex !important;
    gap: 5px !important;
    justify-content: center !important;
    white-space: nowrap !important;
}

.wv-icon-flag {
    align-items: center !important;
    border-radius: 6px !important;
    display: inline-flex !important;
    height: 28px !important;
    justify-content: center !important;
    min-height: 28px !important;
    min-width: 28px !important;
    padding: 0 !important;
    text-decoration: none !important;
    transition: all 0.15s ease-in-out !important;
    width: 28px !important;
    cursor: pointer !important;
}

.wv-icon-flag:hover {
    text-decoration: none !important;
    transform: translateY(-1px);
}

/* Featured Star Flag */
.wv-flag-featured.is-active {
    background-color: #fef3c7 !important;
    border: 1px solid #fde68a !important;
    color: #d97706 !important;
    box-shadow: 0 1px 2px rgba(217, 119, 6, 0.12) !important;
}

.wv-flag-featured.is-active:hover {
    background-color: #fde68a !important;
    border-color: #fcd34d !important;
    color: #b45309 !important;
}

.wv-flag-featured.is-inactive {
    background-color: #f8fafc !important;
    border: 1px solid #e2e8f0 !important;
    color: #94a3b8 !important;
}

.wv-flag-featured.is-inactive:hover {
    background-color: #fef9c3 !important;
    border-color: #fef08a !important;
    color: #ca8a04 !important;
}

/* Published Globe/Eye Flag */
.wv-flag-published.is-active {
    background-color: #dcfce7 !important;
    border: 1px solid #bbf7d0 !important;
    color: #15803d !important;
    box-shadow: 0 1px 2px rgba(21, 128, 61, 0.12) !important;
}

.wv-flag-published.is-active:hover {
    background-color: #bbf7d0 !important;
    border-color: #86efac !important;
    color: #166534 !important;
}

.wv-flag-published.is-inactive {
    background-color: #fee2e2 !important;
    border: 1px solid #fecaca !important;
    color: #dc2626 !important;
}

.wv-flag-published.is-inactive:hover {
    background-color: #fecaca !important;
    border-color: #fca5a5 !important;
    color: #b91c1c !important;
}

/* Quality Bar & Checklist Button on Same Line */
.wv-quality-inline {
    align-items: center !important;
    display: inline-flex !important;
    gap: 7px !important;
    white-space: nowrap !important;
}

.wv-quality-bar-track {
    background-color: #e2e8f0 !important;
    border-radius: 999px !important;
    flex: 0 0 60px !important;
    height: 6px !important;
    overflow: hidden !important;
    width: 60px !important;
}

.wv-quality-bar-fill {
    background-color: #16a34a !important;
    border-radius: 999px !important;
    display: block !important;
    height: 100% !important;
    transition: width 0.3s ease !important;
}

.wv-quality-pct {
    color: #0f172a !important;
    font-size: 11px !important;
    font-weight: 800 !important;
    min-width: 26px !important;
    text-align: right !important;
    line-height: 1 !important;
}

/* Quality Checklist Icon Button */
.wv-quality-checklist-icon-btn,
.wv-btn-checklist {
    align-items: center !important;
    background-color: #eff6ff !important;
    border: 1px solid #bfdbfe !important;
    border-radius: 6px !important;
    color: #2563eb !important;
    display: inline-flex !important;
    height: 26px !important;
    justify-content: center !important;
    min-height: 26px !important;
    min-width: 26px !important;
    padding: 0 !important;
    text-decoration: none !important;
    transition: all 0.15s ease-in-out !important;
    width: 26px !important;
    cursor: pointer !important;
    box-shadow: 0 1px 2px rgba(37, 99, 235, 0.08) !important;
}

.wv-quality-checklist-icon-btn i,
.wv-btn-checklist i {
    color: #2563eb !important;
    font-family: 'FontAwesome' !important;
    font-style: normal !important;
    font-size: 13px !important;
    line-height: 1 !important;
    display: inline-block !important;
}

.wv-quality-checklist-icon-btn:hover,
.wv-quality-checklist-icon-btn:focus,
.wv-btn-checklist:hover,
.wv-btn-checklist:focus {
    background-color: #dbeafe !important;
    border-color: #93c5fd !important;
    color: #1d4ed8 !important;
    text-decoration: none !important;
    transform: translateY(-1px);
    box-shadow: 0 1px 3px rgba(37, 99, 235, 0.2) !important;
}

.wv-quality-checklist-icon-btn:hover i,
.wv-quality-checklist-icon-btn:focus i,
.wv-btn-checklist:hover i,
.wv-btn-checklist:focus i {
    color: #1d4ed8 !important;
}

/* Compact Action Dropdown Button in Table */
.property-action-cell .property-action-button,
.property-action-cell .btn {
    background-color: #082f49 !important;
    border-color: #082f49 !important;
    color: #ffffff !important;
    font-size: 12px !important;
    font-weight: 600 !important;
    height: 28px !important;
    min-height: 28px !important;
    line-height: 26px !important;
    padding: 0 10px !important;
    border-radius: 6px !important;
}

.property-edit-link {
    font-weight: 700;
}

.property-price-cell {
    color: #0f3b63;
    font-weight: 700;
    white-space: nowrap;
}

/* Executive Header Bar */
.wv-pro-header-bar {
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    flex-wrap: wrap !important;
    gap: 12px !important;
    margin-bottom: 14px !important;
    background: #ffffff !important;
    border: 1px solid #e2e8f0 !important;
    border-radius: 10px !important;
    padding: 10px 16px !important;
    box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04) !important;
}

.wv-pro-header-left {
    display: flex !important;
    align-items: center !important;
    gap: 12px !important;
    flex-wrap: wrap !important;
    min-width: 0 !important;
}

.wv-pro-page-title {
    margin: 0 !important;
    padding: 0 !important;
    font-size: 19px !important;
    font-weight: 800 !important;
    color: #0f172a !important;
    line-height: 1.2 !important;
    letter-spacing: -0.3px !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 8px !important;
    white-space: nowrap !important;
}

.wv-pro-title-icon {
    color: #2563eb !important;
    font-size: 18px !important;
}

.wv-pro-count-badge {
    display: inline-flex !important;
    align-items: center !important;
    gap: 5px !important;
    background: #f1f5f9 !important;
    border: 1px solid #e2e8f0 !important;
    border-radius: 999px !important;
    padding: 3px 10px !important;
    font-size: 12px !important;
    font-weight: 700 !important;
    color: #475569 !important;
    line-height: 1.2 !important;
    white-space: nowrap !important;
}

.wv-pro-count-num {
    color: #0f172a !important;
    font-weight: 800 !important;
}

.wv-pro-count-txt {
    color: #64748b !important;
    font-weight: 600 !important;
}

.wv-pro-header-right {
    display: inline-flex !important;
    align-items: center !important;
    flex: 0 0 auto !important;
}

.wv-pro-actions-row {
    display: inline-flex !important;
    align-items: center !important;
    flex-wrap: wrap !important;
    gap: 8px !important;
}

.wv-pro-action-btn {
    border-radius: 7px !important;
    font-size: 12.5px !important;
    font-weight: 600 !important;
    height: 36px !important;
    min-height: 36px !important;
    padding: 0 12px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 6px !important;
    text-decoration: none !important;
    transition: all 0.15s ease !important;
    white-space: nowrap !important;
}

/* Bulk Actions Group in Header */
.wv-bulk-action-inline {
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
    border-left: 1px solid #cbd5e1 !important;
    padding-left: 10px !important;
    margin-left: 2px !important;
}

.wv-bulk-action-select {
    background: #ffffff !important;
    border: 1px solid #cbd5e1 !important;
    border-radius: 7px !important;
    box-shadow: none !important;
    color: #1e293b !important;
    font-size: 12.5px !important;
    font-weight: 600 !important;
    height: 36px !important;
    min-height: 36px !important;
    min-width: 155px !important;
    width: auto !important;
    padding: 6px 10px !important;
    transition: border-color 0.15s ease, box-shadow 0.15s ease !important;
}

.wv-bulk-action-select:focus {
    border-color: #2563eb !important;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.18) !important;
    outline: none !important;
}

.wv-bulk-action-apply-btn {
    align-items: center !important;
    background: #2563eb !important;
    border: 1px solid #1d4ed8 !important;
    border-radius: 7px !important;
    box-shadow: 0 1px 2px rgba(37, 99, 235, 0.22) !important;
    color: #ffffff !important;
    display: inline-flex !important;
    font-size: 12.5px !important;
    font-weight: 700 !important;
    height: 36px !important;
    justify-content: center !important;
    min-height: 36px !important;
    padding: 0 13px !important;
    transition: background 0.15s ease, transform 0.15s ease !important;
    white-space: nowrap !important;
}

.wv-bulk-action-apply-btn:hover,
.wv-bulk-action-apply-btn:focus {
    background: #1d4ed8 !important;
    border-color: #1e40af !important;
    color: #ffffff !important;
    transform: translateY(-1px);
}

/* Dedicated Filters Toggle Button */
.wv-pro-filter-toggle {
    background: #ffffff !important;
    border: 1px solid #cbd5e1 !important;
    border-radius: 7px !important;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04) !important;
    color: #334155 !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 7px !important;
    font-size: 12.5px !important;
    font-weight: 600 !important;
    height: 36px !important;
    min-height: 36px !important;
    padding: 0 13px !important;
    transition: all 0.15s ease !important;
    white-space: nowrap !important;
    cursor: pointer !important;
}

.wv-pro-filter-toggle:hover,
.wv-pro-filter-toggle:focus {
    background: #f8fafc !important;
    border-color: #94a3b8 !important;
    color: #0f172a !important;
}

.wv-pro-filter-toggle.is-active,
.wv-pro-filter-toggle[aria-expanded="true"]:not(.collapsed) {
    background: #eff6ff !important;
    border-color: #93c5fd !important;
    color: #1d4ed8 !important;
}

.wv-pro-filter-toggle .wv-filter-icon {
    color: #2563eb !important;
    font-size: 13px !important;
}

.wv-pro-filter-toggle .wv-filter-badge {
    background: #2563eb !important;
    border-radius: 999px !important;
    color: #ffffff !important;
    font-size: 10.5px !important;
    font-weight: 700 !important;
    line-height: 1.2 !important;
    padding: 2px 7px !important;
}

.wv-pro-filter-toggle .wv-chevron-icon {
    color: #64748b !important;
    font-size: 11px !important;
    transition: transform 0.22s ease !important;
}

.wv-pro-filter-toggle.is-active .wv-chevron-icon,
.wv-pro-filter-toggle[aria-expanded="true"]:not(.collapsed) .wv-chevron-icon {
    transform: rotate(180deg) !important;
}

/* Collapsible Filter Panel Card */
.wv-pro-filter-panel {
    margin-bottom: 14px !important;
}

.wv-pro-filter-card {
    background: #ffffff !important;
    border: 1px solid #e2e8f0 !important;
    border-radius: 10px !important;
    box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04) !important;
    padding: 14px 16px !important;
}

.wv-pro-filter-grid {
    display: grid !important;
    grid-template-columns: 1.4fr 1.4fr 1fr 1fr 1fr 1fr !important;
    gap: 10px 10px !important;
    align-items: flex-end !important;
    width: 100% !important;
}

.wv-pro-filter-item {
    min-width: 0 !important;
    display: flex !important;
    flex-direction: column !important;
}

.wv-pro-filter-label {
    color: #475569 !important;
    display: block !important;
    font-size: 11px !important;
    font-weight: 700 !important;
    letter-spacing: 0.3px !important;
    margin: 0 0 4px 0 !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    text-transform: uppercase !important;
    white-space: nowrap !important;
}

.wv-pro-label-spacer {
    visibility: hidden !important;
}

.wv-pro-filter-input,
.wv-pro-filter-select,
.wv-pro-filter-item .form-control {
    background: #ffffff !important;
    border: 1px solid #cbd5e1 !important;
    border-radius: 7px !important;
    box-shadow: none !important;
    color: #1e293b !important;
    font-size: 12.5px !important;
    height: 36px !important;
    min-height: 36px !important;
    padding: 6px 10px !important;
    transition: border-color 0.15s ease, box-shadow 0.15s ease !important;
    width: 100% !important;
}

.wv-pro-filter-input:focus,
.wv-pro-filter-select:focus,
.wv-pro-filter-item .form-control:focus {
    border-color: #2563eb !important;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.18) !important;
    outline: none !important;
}

.wv-pro-filter-input::placeholder,
.wv-pro-filter-item .form-control::placeholder {
    color: #94a3b8 !important;
    font-size: 12px !important;
}

.wv-pro-action-btn-group {
    align-items: center !important;
    display: flex !important;
    gap: 8px !important;
    height: 36px !important;
    width: 100% !important;
}

.wv-pro-btn-apply {
    align-items: center !important;
    background: #2563eb !important;
    border: 1px solid #1d4ed8 !important;
    border-radius: 7px !important;
    box-shadow: 0 1px 2px rgba(37, 99, 235, 0.22) !important;
    color: #ffffff !important;
    display: inline-flex !important;
    flex: 1 1 auto !important;
    font-size: 12.5px !important;
    font-weight: 700 !important;
    height: 36px !important;
    justify-content: center !important;
    min-height: 36px !important;
    padding: 0 13px !important;
    transition: background 0.15s ease, transform 0.15s ease !important;
    white-space: nowrap !important;
}

.wv-pro-btn-apply:hover,
.wv-pro-btn-apply:focus {
    background: #1d4ed8 !important;
    border-color: #1e40af !important;
    color: #ffffff !important;
    transform: translateY(-1px);
}

.wv-pro-btn-reset {
    align-items: center !important;
    background: #ffffff !important;
    border: 1px solid #cbd5e1 !important;
    border-radius: 7px !important;
    color: #64748b !important;
    display: inline-flex !important;
    flex: 0 0 auto !important;
    font-size: 12px !important;
    font-weight: 600 !important;
    height: 36px !important;
    justify-content: center !important;
    min-height: 36px !important;
    padding: 0 12px !important;
    transition: all 0.15s ease !important;
    text-decoration: none !important;
    white-space: nowrap !important;
}

.wv-pro-btn-reset:hover {
    background: #f1f5f9 !important;
    color: #0f172a !important;
    border-color: #94a3b8 !important;
    text-decoration: none !important;
}

.wv-pro-table-card {
    margin-bottom: 24px !important;
}

/* Responsive Breakpoints */
@media (max-width: 991px) {
    .wv-pro-header-bar {
        flex-direction: column !important;
        align-items: stretch !important;
        gap: 10px !important;
        padding: 12px 14px !important;
    }

    .wv-pro-header-left {
        justify-content: space-between !important;
        width: 100% !important;
    }

    .wv-pro-header-right {
        width: 100% !important;
    }

    .wv-pro-actions-row {
        width: 100% !important;
        display: flex !important;
        flex-wrap: wrap !important;
        gap: 6px !important;
    }

    .wv-pro-action-btn {
        flex: 1 1 auto !important;
        min-width: 0 !important;
        text-align: center !important;
        font-size: 12px !important;
        padding: 0 8px !important;
    }

    .wv-bulk-action-inline {
        border-left: 0 !important;
        margin-left: 0 !important;
        margin-top: 4px !important;
        padding-left: 0 !important;
        width: 100% !important;
        display: flex !important;
    }

    .wv-bulk-action-select {
        flex: 1 1 auto !important;
        min-width: 0 !important;
        width: 100% !important;
    }

    .wv-bulk-action-apply-btn {
        flex: 0 0 auto !important;
    }

    .wv-pro-filter-toggle {
        width: 100% !important;
        margin-top: 4px !important;
        justify-content: space-between !important;
    }

    .wv-pro-filter-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        gap: 9px !important;
    }

    .wv-pro-col-wide,
    .wv-pro-filter-actions {
        grid-column: span 2 !important;
    }
}

@media (max-width: 575px) {
    .wv-pro-filter-grid {
        grid-template-columns: 1fr !important;
    }
    .wv-pro-col-wide,
    .wv-pro-filter-actions {
        grid-column: span 1 !important;
    }
}
</style>

<div id="resultDiv">
    <?php echo $this->Session->flash();?>

    <!-- 1. EXECUTIVE TOP HEADER BAR -->
    <div class="wv-pro-header-bar">
        <div class="wv-pro-header-left">
            <h1 class="wv-pro-page-title">
                <i class="fa fa-home wv-pro-title-icon"></i>
                <span><?php echo __('Properties');?></span>
            </h1>
            <span class="wv-pro-count-badge" title="<?php echo __('Total Records'); ?>">
                <span class="wv-pro-count-num"><?php echo number_format($totalCount); ?></span>
                <span class="wv-pro-count-txt"><?php echo __('records'); ?></span>
            </span>
        </div>

        <div class="wv-pro-header-right">
            <div class="wv-pro-actions-row">
                <!-- Upper Action Buttons -->
                <?php echo $this->Html->link('<span class="fa fa-plus"></span>&nbsp;'.__('Add New Property'),array('controller'=>'Properties','action'=>'add'),array('escape'=>false,'class'=>'btn btn-success wv-pro-action-btn'));?>
                <?php echo $this->Html->link('<span class="fa fa-edit"></span>&nbsp;'.__('Edit'),'javascript:void(0);',array('name'=>'editallfrm','id'=>'editallfrm','onclick'=>"check_perform_editfull('$url');",'escape'=>false,'class'=>'btn btn-warning wv-pro-action-btn'));?>
                <?php echo $this->Html->link('<span class="fa fa-trash"></span>&nbsp;'.__('Delete'),'javascript:void(0);',array('name'=>'deleteallfrm','id'=>'deleteallfrm','onclick'=>'check_perform_delete();','escape'=>false,'class'=>'btn btn-danger wv-pro-action-btn'));?>
                <?php echo $this->Html->link('<span class="fa fa-dashboard"></span>&nbsp;'.__('Enhancement Overview'),array('controller'=>'Propertyenhancements','action'=>'index'),array('escape'=>false,'class'=>'btn btn-info wv-pro-action-btn'));?>
                <?php echo $this->Html->link('<span class="fa fa-map-marker"></span>&nbsp;'.__('Missing Map'),array('controller'=>'Propertyenhancements','action'=>'missinglocations'),array('escape'=>false,'class'=>'btn btn-primary wv-pro-action-btn'));?>

                <!-- Bulk Actions Group -->
                <div class="wv-bulk-action-inline">
                    <?php echo $this->Form->select('Property.bulk_action_top',array(
                        'publish_yes'=>__('Publish Selected'),
                        'publish_no'=>__('Unpublish Selected'),
                        'featured_yes'=>__('Feature Selected'),
                        'featured_no'=>__('Unfeature Selected'),
                        'status_available'=>__('Mark Available'),
                        'status_sold'=>__('Mark Sold')
                    ),array('empty'=>__('Bulk Action'),'id'=>'propertyBulkActionSelect','class'=>'form-control wv-bulk-action-select','div'=>false,'label'=>false));?>
                    <button type="button" class="btn btn-primary wv-bulk-action-apply-btn" id="propertyBulkActionApplyBtn" onclick="performBulkUpdate();">
                        <span class="fa fa-check"></span> <?php echo __('Apply');?>
                    </button>
                </div>

                <!-- Dedicated Filters Toggle Button -->
                <button type="button" class="btn btn-default wv-pro-filter-toggle<?php echo $isFilterPanelExpanded ? ' is-active' : ' collapsed'; ?>" data-toggle="collapse" data-target="#propertiesFilterPanel" aria-expanded="<?php echo $isFilterPanelExpanded ? 'true' : 'false'; ?>" aria-controls="propertiesFilterPanel">
                    <i class="fa fa-sliders wv-filter-icon"></i>
                    <span class="wv-filter-btn-text"><?php echo __('Filters'); ?></span>
                    <?php if($activeFilterCount > 0): ?>
                        <span class="badge wv-filter-badge"><?php echo $activeFilterCount; ?></span>
                    <?php endif; ?>
                    <i class="fa fa-chevron-down wv-chevron-icon"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- 2. COLLAPSIBLE SEARCH & FILTERS PANEL -->
    <div id="propertiesFilterPanel" class="collapse<?php echo $isFilterPanelExpanded ? ' in' : ''; ?> wv-pro-filter-panel">
        <div class="wv-pro-filter-card">
            <?php echo $this->Form->create(array('name'=>'searchfrm','id'=>'searchfrmactive','action' => 'index'));?>
            <div class="wv-pro-filter-grid">
                <!-- LINE 1 (Desktop) -->
                <div class="wv-pro-filter-item wv-pro-col-wide">
                    <label class="wv-pro-filter-label" for="general"><strong><?php echo __('General Search');?></strong></label>
                    <?php echo $this->Form->input('general',array('id'=>'general','label' => false,'class'=>'form-control wv-pro-filter-input','placeholder'=> __('Search Title, Address, Office ID (e.g. WV-LD698DE)'),'div'=>false));?>
                </div>
                <div class="wv-pro-filter-item wv-pro-col-wide">
                    <label class="wv-pro-filter-label" for="location"><strong><?php echo __('Location');?></strong></label>
                    <?php echo $this->Form->input('location',array('id'=>'location','label' => false,'class'=>'form-control wv-pro-filter-input','placeholder'=> __('Colony, Block, Sector, City or State'),'div'=>false));?>
                </div> 
                <div class="wv-pro-filter-item">
                    <label class="wv-pro-filter-label" for="type"><strong><?php echo __('Kind of property');?></strong></label>
                    <?php echo $this->Form->select('type',$type,array('id'=>'type','label' => false,'class'=>'form-control wv-pro-filter-select','empty'=>__('All Property Types'),'div'=>false));?>
                </div> 
                <div class="wv-pro-filter-item">
                    <label class="wv-pro-filter-label" for="contract"><strong><?php echo __('Contract');?></strong></label>
                    <?php echo $this->Form->select('contract',$contract,array('id'=>'contract','label' => false,'class'=>'form-control wv-pro-filter-select','empty'=>__('All Contracts'),'div'=>false));?>
                </div> 
                <div class="wv-pro-filter-item">
                    <label class="wv-pro-filter-label" for="start_price"><strong><?php echo __('Minimum price');?></strong></label>
                    <?php echo $this->Form->input('start_price',array('id'=>'start_price','label' => false,'class'=>'form-control wv-pro-filter-input','placeholder'=> __('Min Price (e.g. 500000)'),'div'=>false));?>
                </div> 
                <div class="wv-pro-filter-item">
                    <label class="wv-pro-filter-label" for="end_price"><strong><?php echo __('Maximum price');?></strong></label>
                    <?php echo $this->Form->input('end_price',array('id'=>'end_price','label' => false,'class'=>'form-control wv-pro-filter-input','placeholder'=> __('Max Price (e.g. 15000000)'),'div'=>false));?>
                </div> 

                <!-- LINE 2 (Desktop) -->
                <div class="wv-pro-filter-item">
                    <label class="wv-pro-filter-label" for="bedroom"><strong><?php echo __('Bedroom');?></strong></label>
                    <?php echo $this->Form->input('bedroom',array('id'=>'bedroom','label' => false,'class'=>'form-control wv-pro-filter-input','placeholder'=> __('Bedrooms (e.g. 2, 3)'),'div'=>false));?>
                </div> 
                <div class="wv-pro-filter-item">
                    <label class="wv-pro-filter-label" for="bathroom"><strong><?php echo __('Bathroom');?></strong></label>
                    <?php echo $this->Form->input('bathroom',array('id'=>'bathroom','label' => false,'class'=>'form-control wv-pro-filter-input','placeholder'=> __('Bathrooms (e.g. 2, 3)'),'div'=>false));?>
                </div> 
                <div class="wv-pro-filter-item">
                    <label class="wv-pro-filter-label" for="Area"><strong><?php echo __('Area');?></strong></label>
                    <?php echo $this->Form->input('Area',array('id'=>'Area','label' => false,'class'=>'form-control wv-pro-filter-input','placeholder'=> __('Area (e.g. 3, 5, 10)'),'div'=>false));?>
                </div> 
                <div class="wv-pro-filter-item">
                    <label class="wv-pro-filter-label" for="publish"><strong><?php echo __('Published');?></strong></label>
                    <?php $sex=array('Yes'=>'Yes','No'=>'No');
                    echo $this->Form->select('publish',$sex,array('id'=>'publish','label' => false,'class'=>'form-control wv-pro-filter-select','empty'=>__('All Status'),'div'=>false));?>
                </div> 
                <div class="wv-pro-filter-item">
                    <label class="wv-pro-filter-label" for="skeyword"><strong><?php echo __('Status');?></strong></label>
                    <?php $status=array(__('Available')=>__('Available'),__('Sold')=>__('Sold'));
                    echo $this->Form->select('skeyword',$status,array('id'=>'skeyword','label' => false,'class'=>'form-control wv-pro-filter-select','empty'=>__('All Status'),'div'=>false));?>
                </div> 
                <div class="wv-pro-filter-item wv-pro-filter-actions">
                    <label class="wv-pro-filter-label wv-pro-label-spacer" aria-hidden="true">&nbsp;</label>
                    <div class="wv-pro-action-btn-group">
                        <?php echo $this->Form->input('searchfrm',array('type'=>'hidden','value'=>'active'));?>
                        <?php echo $this->Form->button('<span class="fa fa-search"></span>&nbsp;'.__('Search / Apply'),array('class'=>'btn btn-primary wv-pro-btn-apply','escape'=>false));?>
                        <a href="<?php echo $this->Html->url(array('action'=>'index')); ?>" class="btn btn-default wv-pro-btn-reset" title="<?php echo __('Reset Filters'); ?>">
                            <span class="fa fa-refresh"></span>&nbsp;<?php echo __('Reset'); ?>
                        </a>
                    </div>
                </div>              
            </div>
            <?php echo $this->Form->end();?>
        </div>
    </div>

    <!-- 3. DATA TABLE CARD -->
    <div class="panel panel-flat wv-pro-table-card">
        <?php echo $this->element('pagination',array('IsSearch'=>'No','UseNamedLimit'=>true,'UseQueryLimit'=>true,'NamedLimitBaseUrl'=>$propertyLimitUrl));
        $limit = $page_params['limit'];
        $page = $page_params['page'];
        $serial_no = 1*$limit*($page-1)+1;?>
        <?php echo $this->Form->create(array('name'=>'deleteallfrm','action' => 'deleteall'));?>
        <?php echo $this->Session->flash();?>
        <?php echo $this->Form->hidden('Property.bulk_action', array('id' => 'propertyBulkActionHidden')); ?>
        <div class="table-responsive">
            <table class="table table-striped table-bordered wv-pro-table">
                <thead>
                    <tr>
                        <th class="pbutton wv-col-checkbox"><?php echo $this->Form->checkbox('checkbox', array('value'=>'deleteall','name'=>'selectAll','label'=>false,'id'=>'selectAll','hiddenField'=>false));?></th>
                        <th class="wv-col-num"><?php echo $this->Paginator->sort('id', __('#'), array('direction' => 'desc'));?></th>
                        <th class="wv-col-office-id"><?php echo $this->Paginator->sort('uniq_id', __('Office ID'), array('direction' => 'asc'));?></th>
                        <th class="wv-col-name"><?php echo $this->Paginator->sort('name', __('Name'), array('direction' => 'asc'));?></th>
                        <th class="wv-col-price"><?php echo $this->Paginator->sort('price', __('Price'), array('direction' => 'asc'));?></th>
                        <th class="wv-col-type"><?php echo $this->Paginator->sort('type', __('Type'), array('direction' => 'asc'));?></th>
                        <th class="wv-col-contract"><?php echo $this->Paginator->sort('Contract.name', __('Contract'), array('direction' => 'asc'));?></th>
                        <th class="wv-col-status"><?php echo $this->Paginator->sort('status', __('Status'), array('direction' => 'asc'));?></th>
                        <th class="wv-col-flags"><?php echo __('Featured / Published');?></th>
                        <th class="wv-col-quality"><?php echo __('Quality');?></th>
                        <th class="pbutton property-action-header wv-col-action"><?php echo __('Action');?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($Property as $post):
                    $id=$post['Property']['id'];
                    $editUrl=$url.'/edit/'.$id;
                    $propertySlug=trim(preg_replace('/[^A-Za-z0-9]+/','-',(string)$post['Property']['name']),'-');
                    if($propertySlug==='')$propertySlug='property-'.$id;
                    $frontPropertyUrl=$this->Html->url('/Properties/'.$propertySlug);
                    $isFeatured = ($post['Property']['featured'] === 'Yes');
                    $isPublished = ($post['Property']['published'] === 'Yes');
                    $propStatus = $post['Property']['status'];
                    $isAvailable = (strtolower($propStatus) === 'available');
                    $quality = isset($propertyQualityScores[$id]) ? $propertyQualityScores[$id] : array('score'=>0,'missing'=>array());
                    $qualityScore = (int)$quality['score'];
                    ?>
                    <tr>
                        <td class="pbutton wv-col-checkbox"><?php if($userType!="AGT" || $post['Property']['user_id']==$luserId){ echo $this->Form->checkbox(false,array('value' => $post['Property']['id'],'name'=>'data[Property][id][]','id'=>"DeleteCheckbox$id",'class'=>'chkselect')); } else echo "&nbsp;";?></td>
                        <td class="wv-col-num"><?php echo $serial_no++; ?></td>
                        <td class="wv-col-office-id"><code><?php echo h($post['Property']['uniq_id']);?></code></td>
                        <td class="wv-col-name"><?php echo h($post['Property']['name']);?></td>
                        <td class="property-price-cell wv-col-price"><?php echo h($currency.number_format((float)$post['Property']['price'], 0));?></td>
                        <td class="wv-col-type"><?php echo h($post['Type']['name']);?></td>
                        <td class="wv-col-contract"><?php echo h($post['Contract']['name']);?></td>
                        <td class="wv-col-status">
                            <?php if($isAvailable): ?>
                                <span class="wv-status-badge wv-status-available"><i class="fa fa-check-circle"></i> <?php echo __($propStatus);?></span>
                            <?php else: ?>
                                <span class="wv-status-badge wv-status-sold"><i class="fa fa-tag"></i> <?php echo __($propStatus);?></span>
                            <?php endif; ?>
                        </td>
                        <td class="wv-col-flags">
                            <div class="wv-flags-inline">
                                <?php if($userType!="AGT" || $post['Property']['user_id']==$luserId): ?>
                                    <?php echo $this->Html->link(
                                        '<i class="fa ' . ($isFeatured ? 'fa-star' : 'fa-star-o') . '"></i>',
                                        array('controller'=>'Properties','action'=>'featured',$id, $isFeatured ? 'Yes' : 'No'),
                                        array('escape'=>false, 'class'=>'wv-icon-flag wv-flag-featured ' . ($isFeatured ? 'is-active' : 'is-inactive'), 'title'=> $isFeatured ? __('Featured (Click to Unfeature)') : __('Not Featured (Click to Feature)'))
                                    ); ?>
                                    <?php echo $this->Html->link(
                                        '<i class="fa ' . ($isPublished ? 'fa-globe' : 'fa-eye-slash') . '"></i>',
                                        array('controller'=>'Properties','action'=>'published',$id, $isPublished ? 'Yes' : 'No'),
                                        array('escape'=>false, 'class'=>'wv-icon-flag wv-flag-published ' . ($isPublished ? 'is-active' : 'is-inactive'), 'title'=> $isPublished ? __('Published (Click to Unpublish)') : __('Unpublished (Click to Publish)'))
                                    ); ?>
                                <?php else: ?>
                                    <span class="wv-icon-flag wv-flag-featured <?php echo $isFeatured ? 'is-active' : 'is-inactive'; ?>" title="<?php echo $isFeatured ? __('Featured') : __('Not Featured'); ?>"><i class="fa <?php echo $isFeatured ? 'fa-star' : 'fa-star-o'; ?>"></i></span>
                                    <span class="wv-icon-flag wv-flag-published <?php echo $isPublished ? 'is-active' : 'is-inactive'; ?>" title="<?php echo $isPublished ? __('Published') : __('Unpublished'); ?>"><i class="fa <?php echo $isPublished ? 'fa-globe' : 'fa-eye-slash'; ?>"></i></span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="wv-col-quality">
                            <div class="wv-quality-inline">
                                <div class="wv-quality-bar-track" title="<?php echo $qualityScore;?>%">
                                    <span class="wv-quality-bar-fill" style="width:<?php echo $qualityScore;?>%"></span>
                                </div>
                                <span class="wv-quality-pct"><?php echo $qualityScore;?>%</span>
                                <?php echo $this->Html->link(
                                    '<i class="fa fa-check-square-o"></i>',
                                    array('controller'=>'Propertyenhancements','action'=>'quality',$id),
                                    array('escape'=>false, 'class'=>'wv-quality-checklist-icon-btn', 'title'=>__('Quality Checklist'))
                                );?>
                            </div>
                        </td>
                        <td class="pbutton property-action-cell wv-col-action"><?php if($userType=="AGT" && $post['Property']['user_id']!=$luserId){?>
                        <?php echo $this->Html->link('<span class="fa fa-arrows-alt"></span>&nbsp;&nbsp;&nbsp;&nbsp;'.__('View'),'javascript:void(0);',array('class'=>'btn property-action-button','onclick'=>"show_modal('$url/View/$id');",'escape'=>false));}
                        else {?>
                        <div class="btn-group">
                        <button type="button" class="btn btn-primary dropdown-toggle property-action-button" data-toggle="dropdown" aria-expanded="false">
                        <?php echo __('Action');?> <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-right" role="menu">
                          <li><?php echo $this->Html->link('<span class="fa fa-image"></span>&nbsp;'.__('Photos'),array('controller'=>'properties_photos','action'=>"index/$id"),array('escape'=>false));?></li>
                          <li><?php echo $this->Html->link('<span class="fa fa-file"></span>&nbsp;'.__('Documents'),array('controller'=>'properties_documents','action'=>"index/$id"),array('escape'=>false));?></li>
                          <li><?php echo $this->Html->link('<span class="fa fa-building-o fa-fw"></span>&nbsp;'.__('Floor Plans'),array('controller'=>'properties_floorplans','action'=>"index/$id"),array('escape'=>false));?></li>
                          <li><?php echo $this->Html->link('<span class="fa fa-magic"></span>&nbsp;'.__('Enhancements'),array('controller'=>'Propertyenhancements','action'=>'quality',$id),array('escape'=>false));?></li>
                          <li><?php echo $this->Html->link('<span class="fa fa-tags"></span>&nbsp;'.__('Media Details'),array('controller'=>'Propertyenhancements','action'=>'media',$id),array('escape'=>false));?></li>
                          <li><?php echo $this->Html->link('<span class="fa fa-search"></span>&nbsp;'.__('SEO'),array('controller'=>'Propertyenhancements','action'=>'seo',$id),array('escape'=>false));?></li>
                          <li><?php echo $this->Html->link('<span class="fa fa-line-chart"></span>&nbsp;'.__('Lead Clicks'),array('controller'=>'Propertyenhancements','action'=>'leads',$id),array('escape'=>false));?></li>
                          <li><?php echo $this->Html->link('<span class="fa fa-history"></span>&nbsp;'.__('History'),array('controller'=>'Properties','action'=>'history',$id),array('escape'=>false));?></li>
                          <li><?php echo $this->Html->link('<span class="fa fa-copy"></span>&nbsp;'.__('Duplicate'),array('controller'=>'Properties','action'=>'duplicate',$id),array('escape'=>false,'onclick'=>"return confirm('".__('Duplicate this property as unpublished?')."');"));?></li>
                          <li><?php echo $this->Html->link('<span class="fa fa-external-link"></span>&nbsp;'.__('Visit'),$frontPropertyUrl,array('escape'=>false,'target'=>'_blank'));?></li>
                          <li class="divider"></li>
                          <li><?php echo $this->Html->link('<span class="fa fa-arrows-alt"></span>&nbsp;'.__('View'),'javascript:void(0);',array('onclick'=>"show_modal('$url/View/$id');",'escape'=>false));?></li>
                          <li><a class="property-edit-link" href="<?php echo h($editUrl);?>" onclick="window.location.href=this.href; return false;"><span class="fa fa-edit"></span>&nbsp;<?php echo __('Edit');?></a></li>
                          <li> <?php echo $this->Html->Link('<span class="fa fa-trash"></span>&nbsp;'.__('Delete'),'javascript:void(0);',array('onclick'=>"check_perform_sdelete('$id');",'escape'=>false));?></li>
                        </ul>
                      </div><?php }?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php unset($post); ?>
                </tbody>
            </table>
        </div>
        <?php echo $this->Form->end();?>
        <?php echo $this->element('pagination',array('UseNamedLimit'=>true,'UseQueryLimit'=>true,'NamedLimitBaseUrl'=>$propertyLimitUrl));?>
    </div>
</div>

<script type="text/javascript">
(function($){
    // Keep Filter toggle button state in sync with Bootstrap collapse show/hide events
    $('#propertiesFilterPanel').on('show.bs.collapse', function(){
        $('[data-target="#propertiesFilterPanel"]').addClass('is-active').removeClass('collapsed').attr('aria-expanded', 'true');
    }).on('hide.bs.collapse', function(){
        $('[data-target="#propertiesFilterPanel"]').removeClass('is-active').addClass('collapsed').attr('aria-expanded', 'false');
    });

    // Sync bulk action dropdown value to hidden input in deleteallfrm
    $(document).off('change.wvBulkActionSync', '#propertyBulkActionSelect')
        .on('change.wvBulkActionSync', '#propertyBulkActionSelect', function(){
            $('#propertyBulkActionHidden').val($(this).val());
        });
})(jQuery);

function performBulkUpdate() {
    var bulkVal = jQuery('#propertyBulkActionSelect').val();
    if (!bulkVal) {
        alert('<?php echo h(__('Please select a bulk action')); ?>');
        jQuery('#propertyBulkActionSelect').focus();
        return false;
    }
    var checkedCount = jQuery('input.chkselect:checked').length;
    if (checkedCount === 0) {
        alert('<?php echo h(__('Please select at least one property')); ?>');
        return false;
    }
    var $hidden = jQuery('#propertyBulkActionHidden');
    if (!$hidden.length) {
        var $form = jQuery('form[name="deleteallfrm"]');
        $hidden = jQuery('<input type="hidden" name="data[Property][bulk_action]" id="propertyBulkActionHidden">').appendTo($form);
    }
    $hidden.val(bulkVal);
    document.deleteallfrm.action = '<?php echo h($bulkUrl);?>';
    document.deleteallfrm.submit();
    return true;
}
</script>
