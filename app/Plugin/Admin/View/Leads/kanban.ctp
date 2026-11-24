<?php
$kanbanSaveUrl = $this->Html->url(array('controller' => 'Leads', 'action' => 'updatestatus'));
$kanbanAgentAjaxUrl = $this->Html->url(array('controller' => 'Ajaxcontents', 'action' => 'agent'));
$currentUserId = (int)$this->Session->read('User.id');
$kanbanBaseDateFormat = (isset($dpFormat) && trim((string)$dpFormat) !== '') ? trim((string)$dpFormat) : 'DD-MMM-YYYY';
$kanbanFollowUpPickerFormat = trim($kanbanBaseDateFormat.' hh:mm A');
$kanbanFollowUpValueFormat = trim($kanbanBaseDateFormat.' h:i A');
$kanbanFollowUpPhpDisplayFormat = trim($sysDay.$dateSep.$sysMonth.$dateSep.$sysYear.$dateGap.$sysHour.$timeSep.$sysMin.$dateGap.$sysMer);
$kanbanCanUseAgencyFilters = !empty($canUseAgencyFilters);
$kanbanCanUseAgentFilters = !empty($canUseAgentFilters);
$kanbanCanUseUserLevelFilters = !empty($canUseUserLevelFilters);
$selectedProjectFilter = isset($selectedProjectFilter) ? (int)$selectedProjectFilter : 0;
$selectedUserLevelFilter = isset($selectedUserLevelFilter) ? (int)$selectedUserLevelFilter : 0;
$selectedUserFilter = isset($selectedUserFilter) ? (int)$selectedUserFilter : 0;
$selectedPeriodFilter = isset($selectedPeriodFilter) && trim((string)$selectedPeriodFilter) !== '' ? trim((string)$selectedPeriodFilter) : 'week';
$selectedPhoneRegion = isset($selectedPhoneRegion) && trim((string)$selectedPhoneRegion) !== '' ? trim((string)$selectedPhoneRegion) : 'all';
$selectedStartDateInput = isset($selectedStartDateInput) ? trim((string)$selectedStartDateInput) : '';
$selectedEndDateInput = isset($selectedEndDateInput) ? trim((string)$selectedEndDateInput) : '';
$selectedFilterSummary = isset($selectedFilterSummary) ? trim((string)$selectedFilterSummary) : '';
$detectRegion = function($phone){
    $raw = trim((string)$phone);
    if($raw === '') return null;
    $digits = preg_replace('/[^0-9]/', '', $raw);
    if($digits === '') return null;
    if(substr($digits, 0, 2) === '00') $digits = substr($digits, 2);
    if(substr($digits, 0, 3) === '092') $digits = substr($digits, 1);
    if(preg_match('/^92[0-9]{10}$/', $digits) || preg_match('/^03[0-9]{9}$/', $digits) || preg_match('/^3[0-9]{9}$/', $digits) || preg_match('/^0[2-9][0-9]{8,9}$/', $digits)) {
        return array('is_overseas' => false, 'badge' => '🇵🇰 PK', 'country' => 'Pakistan');
    }
    if(substr($digits, 0, 1) === '0' && strlen($digits) >= 11) $digits = substr($digits, 1);
    if(substr($digits, 0, 3) === '971') return array('is_overseas' => true, 'badge' => '🇦🇪 UAE', 'country' => 'UAE');
    if(substr($digits, 0, 3) === '966') return array('is_overseas' => true, 'badge' => '🇸🇦 KSA', 'country' => 'Saudi Arabia');
    if(substr($digits, 0, 2) === '44') return array('is_overseas' => true, 'badge' => '🇬🇧 UK', 'country' => 'UK');
    if(substr($digits, 0, 1) === '1' && strlen($digits) === 11) return array('is_overseas' => true, 'badge' => '🇺🇸 USA', 'country' => 'USA');
    if(substr($digits, 0, 3) === '968') return array('is_overseas' => true, 'badge' => '🇴🇲 Oman', 'country' => 'Oman');
    if(substr($digits, 0, 3) === '974') return array('is_overseas' => true, 'badge' => '🇶🇦 Qatar', 'country' => 'Qatar');
    if(substr($digits, 0, 3) === '973') return array('is_overseas' => true, 'badge' => '🇧🇭 Bahrain', 'country' => 'Bahrain');
    if(substr($digits, 0, 3) === '965') return array('is_overseas' => true, 'badge' => '🇰🇼 Kuwait', 'country' => 'Kuwait');
    if(substr($digits, 0, 2) === '61') return array('is_overseas' => true, 'badge' => '🇦🇺 Australia', 'country' => 'Australia');
    if(substr($digits, 0, 2) === '39') return array('is_overseas' => true, 'badge' => '🇮🇹 Italy', 'country' => 'Italy');
    if(substr($digits, 0, 2) === '49') return array('is_overseas' => true, 'badge' => '🇩🇪 Germany', 'country' => 'Germany');
    return array('is_overseas' => true, 'badge' => '🌍 Overseas', 'country' => 'Overseas');
};
$badgeLabels = array(
    'hot' => __('Hot'),
    'warm' => __('Warm'),
    'cold' => __('Cold')
);
$badgeIcons = array(
    'hot' => '&#128293;',
    'warm' => '&#127780;',
    'cold' => '&#10052;'
);
$kanbanStatusOptionHtml = '';
foreach((array)$statusRows as $statusOption)
{
    $kanbanStatusOptionHtml .= '<option value="'.(int)$statusOption['Status']['id'].'">'.h($statusOption['Status']['name']).'</option>';
}
$kanbanAutomationQuery = array();
if($selectedProjectFilter > 0)
{
    $kanbanAutomationQuery['project_id'] = (int)$selectedProjectFilter;
}
if($selectedUserLevelFilter > 0)
{
    $kanbanAutomationQuery['ugroup_id'] = (int)$selectedUserLevelFilter;
}
if($selectedUserFilter > 0)
{
    $kanbanAutomationQuery['user_id'] = (int)$selectedUserFilter;
}
$kanbanFollowUpQueueUrl = array('controller' => 'Crmautomations', 'action' => 'queue', '?' => array_merge($kanbanAutomationQuery, array('queue_type' => 'overdue')));
$kanbanLeadReactivationUrl = array('controller' => 'Crmautomations', 'action' => 'reactivation', '?' => $kanbanAutomationQuery);
$kanbanSourceRoiUrl = array('controller' => 'Crmautomations', 'action' => 'sourceroi', '?' => $kanbanAutomationQuery);
?>

<style type="text/css">
.modern-kanban-board {
    display: grid;
    grid-template-columns: repeat(3, minmax(320px, 1fr));
    gap: 12px;
    overflow: visible;
    padding: 4px 2px 6px 2px;
}
.kanban-column {
    min-width: 0;
    width: 100%;
    max-width: none;
    background: #f8fafc;
    border: 1px solid #d8e0ea;
    border-radius: 8px;
    box-shadow: 0 1px 5px rgba(15, 23, 42, 0.08);
    display: flex;
    flex-direction: column;
    max-height: calc(100vh - 265px);
}
.kanban-column.is-column-dragging {
    opacity: 0.7;
}
.kanban-column-head {
    padding: 11px 12px;
    border-bottom: 1px solid #e5eaf1;
    cursor: move;
    user-select: none;
    border-top-left-radius: 8px;
    border-top-right-radius: 8px;
    background: #ffffff;
}
.kanban-column-title-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
}
.kanban-column-title {
    font-size: 14px;
    font-weight: 800;
    margin: 0;
    color: #243447;
    display: flex;
    align-items: center;
    gap: 8px;
}
.kanban-column-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    display: inline-block;
}
.kanban-column-count {
    font-size: 12px;
    font-weight: 800;
    color: #0f172a;
    background: #eef2f7;
    border-radius: 14px;
    padding: 3px 9px;
}
.kanban-column-search {
    padding: 10px 12px 0 12px;
    background: #ffffff;
}
.kanban-column-search .input-group-addon {
    background: #f8fafc;
    color: #6b7280;
}
.kanban-column-search .form-control {
    height: 32px;
    font-size: 12px;
}
.kanban-cards.drag-over {
    background: #eff6ff !important;
    border: 2px dashed #2563eb !important;
    border-radius: 6px;
}
.kanban-card.is-dragging {
    opacity: 0.45 !important;
    transform: scale(0.98);
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
}
/* ==========================================================================
   MODERN PROFESSIONAL KANBAN CARDS STYLES
   ========================================================================== */
.kanban-cards {
    padding: 10px;
    overflow-y: auto;
    min-height: 240px;
    transition: background-color 0.2s ease;
}
.kanban-cards.drag-over {
    background: #eff6ff !important;
    border: 2px dashed #2563eb !important;
    border-radius: 8px;
}
.kanban-empty {
    padding: 24px 12px;
    text-align: center;
    color: #94a3b8;
    border: 2px dashed #e2e8f0;
    border-radius: 10px;
    background: #ffffff;
    font-size: 13px;
    font-weight: 600;
}
.kanban-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-left: 4px solid #3b82f6;
    border-radius: 10px;
    padding: 11px 12px;
    margin-bottom: 10px;
    cursor: grab;
    box-shadow: 0 1px 3px rgba(15, 23, 42, 0.05), 0 1px 2px rgba(15, 23, 42, 0.03);
    transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
    content-visibility: auto;
    contain-intrinsic-size: 240px;
    position: relative;
}
.kanban-card:hover {
    border-color: #cbd5e1;
    transform: translateY(-2px);
    box-shadow: 0 10px 18px -3px rgba(15, 23, 42, 0.08), 0 4px 6px -2px rgba(15, 23, 42, 0.04);
}
.kanban-card:last-child {
    margin-bottom: 0;
}
.kanban-card.is-dragging {
    opacity: 0.45 !important;
    transform: scale(0.97);
    cursor: grabbing;
    box-shadow: 0 14px 28px rgba(0,0,0,0.15);
}
.kanban-card.badge-hot {
    border-left-color: #ef4444 !important;
}
.kanban-card.badge-warm {
    border-left-color: #f59e0b !important;
}
.kanban-card.badge-cold {
    border-left-color: #06b6d4 !important;
}

/* Card Header */
.kanban-card-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    margin-bottom: 8px;
}
.kanban-lead-identity {
    display: flex;
    align-items: center;
    gap: 8px;
    min-width: 0;
    flex: 1;
}
.kanban-avatar-badge {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #eff6ff;
    color: #1d4ed8;
    font-weight: 800;
    font-size: 11px;
    line-height: 32px;
    text-align: center;
    flex-shrink: 0;
    border: 1px solid #dbeafe;
    box-shadow: 0 1px 2px rgba(0,0,0,0.04);
    text-transform: uppercase;
}
.kanban-lead-name-wrap {
    min-width: 0;
    flex: 1;
}
.kanban-lead-name {
    font-size: 13.5px;
    font-weight: 800;
    color: #0f172a;
    line-height: 1.25;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin: 0;
}
.kanban-lead-id-tag {
    font-size: 10.5px;
    font-weight: 600;
    color: #94a3b8;
}
.kanban-badge-select {
    width: auto !important;
    height: 26px;
    padding: 1px 6px;
    font-size: 11px;
    font-weight: 700;
    border-radius: 6px;
    border: 1px solid #e2e8f0;
    background: #f8fafc;
    color: #334155;
    cursor: pointer;
    flex-shrink: 0;
}

/* Card Body & Meta */
.kanban-card-meta {
    font-size: 12px;
    color: #475569;
    margin-bottom: 8px;
    line-height: 1.45;
}
.kanban-meta-row {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 4px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.kanban-meta-icon {
    width: 14px;
    text-align: center;
    color: #64748b;
    font-size: 12px;
}
.kanban-meta-link {
    color: #0f172a;
    font-weight: 700;
    text-decoration: none !important;
}
.kanban-meta-link:hover {
    color: #2563eb;
}

/* Badges line */
.kanban-tags-row {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 4px;
    margin: 6px 0;
}
.kanban-pill {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 10px;
    font-weight: 700;
    border-radius: 12px;
    padding: 2px 7px;
    line-height: 1.3;
}
.kanban-pill.pill-source {
    background: #f1f5f9;
    color: #475569;
    border: 1px solid #e2e8f0;
}
.kanban-pill.pill-overdue {
    background: #fef2f2;
    color: #b91c1c;
    border: 1px solid #fecaca;
    font-weight: 800;
}
.kanban-pill.pill-today {
    background: #f0fdf4;
    color: #15803d;
    border: 1px solid #bbf7d0;
    font-weight: 800;
}
.kanban-pill.pill-upcoming {
    background: #eff6ff;
    color: #1d4ed8;
    border: 1px solid #bfdbfe;
}

/* Remarks Snippet */
.kanban-remark-box {
    background: #f8fafc;
    border-left: 3px solid #cbd5e1;
    border-radius: 4px;
    padding: 4px 7px;
    font-size: 11px;
    color: #475569;
    margin: 6px 0;
    font-style: italic;
    line-height: 1.35;
    max-height: 48px;
    overflow: hidden;
}

/* Inline Followup & Controls */
.kanban-card-controls {
    display: flex;
    align-items: center;
    gap: 5px;
    margin: 7px 0 8px 0;
    padding-top: 6px;
    border-top: 1px dashed #e2e8f0;
}
.kanban-status-select {
    flex: 1;
    height: 26px;
    padding: 1px 6px;
    font-size: 11.5px;
    font-weight: 600;
    border-radius: 6px;
    border: 1px solid #cbd5e1;
    background: #f8fafc;
    color: #1e293b;
}
.kanban-followup-wrap {
    display: flex;
    align-items: center;
    margin: 4px 0 6px 0;
}
.kanban-followup-wrap .input-group.date .form-control {
    font-size: 11px;
    height: 25px;
    padding: 2px 6px;
    border-radius: 5px 0 0 5px;
}
.kanban-followup-wrap .input-group.date .input-group-addon {
    padding: 2px 7px;
    font-size: 11px;
    border-radius: 0 5px 5px 0;
}

/* Modern Action Toolbar */
.kanban-action-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 3px;
    padding-top: 7px;
    border-top: 1px solid #f1f5f9;
    margin-top: 4px;
}
.crm-card-btn {
    flex: 1;
    height: 26px;
    line-height: 24px;
    padding: 0 4px;
    border-radius: 5px;
    font-size: 11px;
    font-weight: 700;
    text-align: center;
    border: 1px solid transparent;
    transition: all 0.15s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 3px;
    text-decoration: none !important;
}
.crm-card-btn.btn-wa {
    background: #25D366;
    color: #ffffff;
    border-color: #22bf5b;
}
.crm-card-btn.btn-wa:hover {
    background: #1eb956;
    color: #ffffff;
}
.crm-card-btn.btn-call {
    background: #0284c7;
    color: #ffffff;
    border-color: #0369a1;
}
.crm-card-btn.btn-call:hover {
    background: #0369a1;
    color: #ffffff;
}
.crm-card-btn.btn-view {
    background: #f1f5f9;
    color: #334155;
    border-color: #cbd5e1;
}
.crm-card-btn.btn-view:hover {
    background: #e2e8f0;
    color: #0f172a;
}
.crm-card-btn.btn-edit {
    background: #f8fafc;
    color: #d97706;
    border-color: #fde68a;
}
.crm-card-btn.btn-edit:hover {
    background: #fef3c7;
    color: #b45309;
}
.crm-card-btn.btn-sms {
    background: #f8fafc;
    color: #059669;
    border-color: #a7f3d0;
}
.crm-card-btn.btn-sms:hover {
    background: #ecfdf5;
    color: #047857;
}
.crm-card-btn.btn-vcard {
    background: #f8fafc;
    color: #7c3aed;
    border-color: #ddd6fe;
}
.crm-card-btn.btn-vcard:hover {
    background: #f5f3ff;
    color: #6d28d9;
}
.kanban-badges-legend {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 6px;
}
.kanban-badge-pill {
    font-size: 11px;
    font-weight: 700;
    border-radius: 10px;
    padding: 4px 9px;
    border: 1px solid transparent;
    background: #f8fafc;
}
.kanban-badge-pill.hot {
    color: #b42318;
    border-color: #fecaca;
    background: #fff1f2;
}
.kanban-badge-pill.warm {
    color: #b45309;
    border-color: #fed7aa;
    background: #fff7ed;
}
.kanban-badge-pill.cold {
    color: #1d4ed8;
    border-color: #bfdbfe;
    background: #eff6ff;
}
.kanban-save-status {
    font-size: 12px;
    font-weight: 600;
    margin-left: auto;
    padding: 4px 8px;
    border-radius: 10px;
}
.kanban-save-status.state-ready {
    color: #4b5563;
    background: #eef2f7;
}
.kanban-save-status.state-saving {
    color: #7c2d12;
    background: #ffedd5;
}
.kanban-save-status.state-ok {
    color: #14532d;
    background: #dcfce7;
}
.kanban-save-status.state-error {
    color: #991b1b;
    background: #fee2e2;
}
.kanban-filter-panel {
    margin-bottom: 12px;
    padding: 12px;
    border: 1px solid #d8e0ea;
    border-radius: 8px;
    background: #ffffff;
    box-shadow: 0 1px 5px rgba(15, 23, 42, 0.06);
}
.kanban-filter-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    align-items: flex-end;
}
.kanban-filter-field {
    flex: 1 1 180px;
    min-width: 180px;
}
.kanban-filter-field label {
    display: block;
    font-size: 12px;
    font-weight: 700;
    color: #3a4b5f;
    margin-bottom: 5px;
}
.kanban-filter-field .form-control {
    width: 100%;
}
.kanban-filter-actions {
    display: flex;
    gap: 8px;
    align-items: center;
    flex-wrap: wrap;
}
.kanban-filter-summary {
    margin-top: 10px;
    font-size: 12px;
    color: #4f5d75;
}
.kanban-board-meta {
    background: #f8fafc;
    border: 1px solid #e5eaf1;
    border-radius: 8px;
    padding: 10px 12px;
    margin-bottom: 10px;
}
.kanban-total-chip {
    display: inline-block;
    background: #0f172a;
    border-radius: 14px;
    color: #fff;
    font-size: 12px;
    font-weight: 800;
    padding: 5px 10px;
}
@media (max-width: 1199px) {
    .modern-kanban-board {
        grid-template-columns: repeat(2, minmax(300px, 1fr));
    }
}
@media (max-width: 991px) {
    .kanban-column {
        min-width: 0;
        width: 100%;
        max-width: none;
    }
    .modern-kanban-board {
        grid-template-columns: repeat(1, minmax(0, 1fr));
    }
    .kanban-filter-field,
    .kanban-filter-actions {
        flex-basis: 100%;
        min-width: 100%;
    }
}
</style>

<script type="text/javascript">
(function($){
    var kanbanSaveUrl = '<?php echo $kanbanSaveUrl;?>';
    var kanbanAgentAjaxUrl = '<?php echo $kanbanAgentAjaxUrl;?>';
    var columnOrderStorageKey = 'wv_leads_kanban_columns_<?php echo (int)$currentUserId;?>';
    var draggedCard = null;
    var draggedCardSourceStatusId = 0;
    var cardDropped = false;
    var draggedColumn = null;
    var persistTimer = null;
    var persistXhr = null;
    var pendingPersistAll = false;
    var pendingColumnStatusIds = {};
    var lastDropSaveAt = 0;
    var followUpSaveTimers = {};
    var badgeWeight = {hot: 1, warm: 2, cold: 3};
    var kanbanDateFormat = '<?php echo addslashes($kanbanFollowUpPickerFormat);?>';
    var kanbanStatusOptionsHtml = '<?php echo addslashes($kanbanStatusOptionHtml);?>';
    var canUseAgencyFilters = <?php echo $kanbanCanUseAgencyFilters ? 'true' : 'false';?>;
    var canUseUserLevelFilters = <?php echo $kanbanCanUseUserLevelFilters ? 'true' : 'false';?>;

    function normalizeBadge(value)
    {
        value = String(value || '').toLowerCase();
        return badgeWeight[value] ? value : 'warm';
    }

    function setSaveStatus(text, stateClass)
    {
        var $status = $('#kanbanSaveState');
        if(!$status.length)
        {
            return;
        }
        $status.removeClass('state-ready state-saving state-ok state-error').addClass(stateClass).text(text);
    }

    function normalizeSearchText(value)
    {
        return $.trim(String(value || '').toLowerCase()).replace(/\s+/g, ' ');
    }

    function getColumnFilterTerm($column)
    {
        return normalizeSearchText($column.find('.kanban-column-search-input').val());
    }

    function applyColumnFilter($column)
    {
        var term = getColumnFilterTerm($column);
        if(term === '')
        {
            $column.find('.kanban-card:hidden').show();
            return;
        }
        $column.find('.kanban-card').each(function(){
            var $card = $(this);
            var haystack = normalizeSearchText($card.attr('data-search-text'));
            $card.toggle(term === '' || haystack.indexOf(term) !== -1);
        });
    }

    function refreshColumnCount($column)
    {
        var totalCount = $column.find('.kanban-card').length;
        var visibleCount = $column.find('.kanban-card:visible').length;
        var term = getColumnFilterTerm($column);
        $column.find('.kanban-column-count').text(term !== '' ? (visibleCount + '/' + totalCount) : totalCount);
    }

    function refreshEmptyState($cardsWrap)
    {
        var $column = $cardsWrap.closest('.kanban-column');
        var hasCards = $cardsWrap.find('.kanban-card:visible').length > 0;
        var $emptyBox = $cardsWrap.find('.kanban-empty');
        if(!$emptyBox.length)
        {
            return;
        }
        if(hasCards)
        {
            $emptyBox.hide();
        }
        else
        {
            $emptyBox.text(getColumnFilterTerm($column) !== '' ? '<?php echo addslashes(__('No matching leads'));?>' : '<?php echo addslashes(__('No leads'));?>');
            $emptyBox.show();
        }
    }

    function refreshAllColumnCounts()
    {
        $('.kanban-column').each(function(){
            var $column = $(this);
            applyColumnFilter($column);
            refreshColumnCount($column);
            refreshEmptyState($column.find('.kanban-cards'));
        });
    }

    function applyBadgeClass($card, badge)
    {
        $card.removeClass('badge-hot badge-warm badge-cold');
        $card.addClass('badge-' + badge);
        $card.attr('data-badge', badge);
        $card.find('.kanban-badge-select').val(badge);
    }

    function ensureStatusOptions($select)
    {
        if(!$select || !$select.length || parseInt($select.attr('data-options-loaded'), 10) === 1)
        {
            return;
        }
        var currentValue = String($select.val() || '');
        $select.html(kanbanStatusOptionsHtml);
        $select.val(currentValue);
        $select.attr('data-options-loaded', '1');
    }

    function markDirtyStatusIds(statusIds)
    {
        if(!$.isArray(statusIds))
        {
            pendingPersistAll = true;
            pendingColumnStatusIds = {};
            return;
        }
        $.each(statusIds, function(_, sid){
            var statusId = parseInt(sid, 10) || 0;
            if(statusId > 0)
            {
                pendingColumnStatusIds[String(statusId)] = 1;
            }
        });
    }

    function collectPayload(statusIds)
    {
        var payload = [];
        var statusFilter = {};
        if($.isArray(statusIds) && statusIds.length)
        {
            $.each(statusIds, function(_, sid){
                var statusId = parseInt(sid, 10) || 0;
                if(statusId > 0)
                {
                    statusFilter[String(statusId)] = 1;
                }
            });
        }
        $('.kanban-column').each(function(){
            var statusId = parseInt($(this).attr('data-status-id'), 10) || 0;
            if(!$.isEmptyObject(statusFilter) && !statusFilter[String(statusId)])
            {
                return;
            }
            $(this).find('.kanban-card').each(function(index){
                var $card = $(this);
                var leadId = parseInt($card.attr('data-lead-id'), 10) || 0;
                if(leadId <= 0 || statusId <= 0)
                {
                    return;
                }
                var badge = normalizeBadge($card.attr('data-badge'));
                var position = index + 1;
                $card.attr('data-status-id', statusId);
                $card.attr('data-position', position);
                payload.push({
                    id: leadId,
                    status_id: statusId,
                    position: position,
                    badge: badge
                });
            });
        });
        return payload;
    }

    function persistBoard(statusIds)
    {
        markDirtyStatusIds(statusIds);
        window.clearTimeout(persistTimer);
        persistTimer = window.setTimeout(function(){
            var changedStatusIds = [];
            if(!pendingPersistAll)
            {
                $.each(pendingColumnStatusIds, function(sid){
                    var parsed = parseInt(sid, 10) || 0;
                    if(parsed > 0)
                    {
                        changedStatusIds.push(parsed);
                    }
                });
            }
            var cards = pendingPersistAll ? collectPayload() : collectPayload(changedStatusIds);
            pendingPersistAll = false;
            pendingColumnStatusIds = {};
            if(!cards.length)
            {
                return;
            }
            setSaveStatus('<?php echo addslashes(__('Saving...'));?>', 'state-saving');
            if(persistXhr && persistXhr.readyState !== 4)
            {
                persistXhr.abort();
            }
            persistXhr = $.ajax({
                url: kanbanSaveUrl,
                type: 'POST',
                dataType: 'json',
                data: {cards: JSON.stringify(cards)},
                success: function(resp){
                    if(resp && resp.success)
                    {
                        setSaveStatus('<?php echo addslashes(__('Auto-saved'));?>', 'state-ok');
                    }
                    else
                    {
                        setSaveStatus((resp && resp.message) ? resp.message : '<?php echo addslashes(__('Save failed'));?>', 'state-error');
                    }
                },
                error: function(xhr, textStatus){
                    if(textStatus === 'abort')
                    {
                        return;
                    }
                    setSaveStatus('<?php echo addslashes(__('Save failed'));?>', 'state-error');
                }
            });
        }, 120);
    }

    function placeDraggedCard(container, afterElement)
    {
        if(!draggedCard || !container)
        {
            return;
        }
        if(afterElement === null)
        {
            if(container.lastElementChild !== draggedCard)
            {
                container.appendChild(draggedCard);
            }
            return;
        }
        if(afterElement === draggedCard || afterElement.previousElementSibling === draggedCard)
        {
            return;
        }
        container.insertBefore(draggedCard, afterElement);
    }

    function getCardAfterElement(container, y)
    {
        var cards = [].slice.call(container.querySelectorAll('.kanban-card:not(.is-dragging)'));
        var closest = {offset: Number.NEGATIVE_INFINITY, element: null};
        cards.forEach(function(card){
            var box = card.getBoundingClientRect();
            var offset = y - box.top - (box.height / 2);
            if(offset < 0 && offset > closest.offset)
            {
                closest = {offset: offset, element: card};
            }
        });
        return closest.element;
    }

    function getColumnAfterElement(container, x)
    {
        var columns = [].slice.call(container.querySelectorAll('.kanban-column:not(.is-column-dragging)'));
        var closest = {offset: Number.NEGATIVE_INFINITY, element: null};
        columns.forEach(function(column){
            var box = column.getBoundingClientRect();
            var offset = x - box.left - (box.width / 2);
            if(offset < 0 && offset > closest.offset)
            {
                closest = {offset: offset, element: column};
            }
        });
        return closest.element;
    }

    function saveColumnOrder()
    {
        if(typeof window.localStorage === 'undefined')
        {
            return;
        }
        var order = [];
        $('.kanban-column').each(function(){
            var statusId = parseInt($(this).attr('data-status-id'), 10) || 0;
            if(statusId > 0)
            {
                order.push(statusId);
            }
        });
        window.localStorage.setItem(columnOrderStorageKey, JSON.stringify(order));
    }

    function applySavedColumnOrder()
    {
        if(typeof window.localStorage === 'undefined')
        {
            return;
        }
        var raw = window.localStorage.getItem(columnOrderStorageKey);
        if(!raw)
        {
            return;
        }
        var parsed = [];
        try
        {
            parsed = JSON.parse(raw);
        }
        catch(err)
        {
            parsed = [];
        }
        if(!$.isArray(parsed) || !parsed.length)
        {
            return;
        }
        var $board = $('#modernKanbanBoard');
        $.each(parsed, function(_, statusId){
            var sid = parseInt(statusId, 10) || 0;
            if(sid <= 0)
            {
                return;
            }
            var $column = $board.find('.kanban-column[data-status-id="' + sid + '"]');
            if($column.length)
            {
                $board.append($column);
            }
        });
    }

    function syncKanbanCustomDateFields()
    {
        var period = $.trim(String($('#kanbanFilterPeriod').val() || '')).toLowerCase();
        var isCustom = (period === 'custom');
        $('.kanban-custom-date-wrap').toggle(isCustom);
        $('#kanbanFilterStartDate, #kanbanFilterEndDate').prop('disabled', !isCustom);
    }

    function normalizeKanbanUserFilterSelect($scope)
    {
        var $target = $scope && $scope.length ? $scope : $(document);
        var $select = $target.find('#kanbanFilterUserId');
        if(!$select.length)
        {
            return;
        }
        $select.find('option:first').text('<?php echo addslashes(__('All Users'));?>');
    }

    function refreshKanbanUsers(selectedUserId)
    {
        if(!canUseAgencyFilters)
        {
            return;
        }
        var projectId = $('#kanbanFilterProjectId').val() || '';
        var userLevelId = canUseUserLevelFilters ? ($('#kanbanFilterUserLevelId').val() || '') : '';
        var requestUrl = kanbanAgentAjaxUrl + '?id=' + encodeURIComponent(projectId) + '&ugroup_id=' + encodeURIComponent(userLevelId) + '&select_id=kanbanFilterUserId';
        $.ajax({
            type: 'get',
            url: requestUrl,
            success: function(response){
                if(!response)
                {
                    return;
                }
                $('#kanbanFilterUserWrap').html(response);
                normalizeKanbanUserFilterSelect($('#kanbanFilterUserWrap'));
                if(selectedUserId)
                {
                    $('#kanbanFilterUserId').val(String(selectedUserId));
                }
            }
        });
    }

    window.updateLeadKanbanStatus = function(leadId, statusId){
        leadId = parseInt(leadId, 10) || 0;
        statusId = parseInt(statusId, 10) || 0;
        if(leadId <= 0 || statusId <= 0)
        {
            return;
        }
        var $card = $('.kanban-card[data-lead-id="' + leadId + '"]');
        var $targetWrap = $('.kanban-cards[data-status-id="' + statusId + '"]');
        if($card.length && $targetWrap.length)
        {
            var oldStatusId = parseInt($card.attr('data-status-id'), 10) || 0;
            $targetWrap.prepend($card);
            ensureStatusOptions($card.find('.kanban-status-select'));
            $card.find('.kanban-status-select').val(statusId);
            refreshAllColumnCounts();
            persistBoard([oldStatusId, statusId]);
            return;
        }
        $.ajax({
            url: kanbanSaveUrl,
            type: 'POST',
            dataType: 'json',
            data: {id: leadId, status_id: statusId},
            success: function(resp){
                if(resp && resp.success)
                {
                    window.location.reload();
                }
                else
                {
                    alert((resp && resp.message) ? resp.message : 'Unable to update status');
                }
            },
            error: function(){
                alert('Unable to update status');
            }
        });
    };

    $(function(){
        syncKanbanCustomDateFields();
        $('#kanbanFilterPeriod').on('change', syncKanbanCustomDateFields);
        if(canUseAgencyFilters)
        {
            normalizeKanbanUserFilterSelect($(document));
            $('#kanbanFilterProjectId, #kanbanFilterUserLevelId').on('change', function(){
                refreshKanbanUsers('');
            });
        }

        var board = document.getElementById('modernKanbanBoard');
        if(!board)
        {
            return;
        }

        applySavedColumnOrder();
        refreshAllColumnCounts();
        setSaveStatus('<?php echo addslashes(__('Auto-save ready'));?>', 'state-ready');

        $('#modernKanbanBoard').on('dragstart', '.kanban-card', function(e){
            draggedCard = this;
            draggedCardSourceStatusId = parseInt($(this).attr('data-status-id'), 10) || 0;
            cardDropped = false;
            $(this).addClass('is-dragging');
            if(e.originalEvent && e.originalEvent.dataTransfer)
            {
                e.originalEvent.dataTransfer.effectAllowed = 'move';
                e.originalEvent.dataTransfer.setData('text/plain', $(this).attr('data-lead-id'));
            }
        });

        $('#modernKanbanBoard').on('dragend', '.kanban-card', function(){
            $(this).removeClass('is-dragging');
            draggedCard = null;
            refreshAllColumnCounts();
            draggedCardSourceStatusId = 0;
            cardDropped = false;
        });

        $('#modernKanbanBoard').on('dragover', '.kanban-cards', function(e){
            if(!draggedCard)
            {
                return;
            }
            e.preventDefault();
            $(this).addClass('drag-over');
            var afterElement = getCardAfterElement(this, e.originalEvent.clientY);
            placeDraggedCard(this, afterElement);
            refreshEmptyState($(this));
        });

        $('#modernKanbanBoard').on('dragleave', '.kanban-cards', function(e){
            $(this).removeClass('drag-over');
        });

        $('#modernKanbanBoard').on('drop', '.kanban-cards', function(e){
            $(this).removeClass('drag-over');
            if(!draggedCard)
            {
                return;
            }
            e.preventDefault();
            var destinationStatusId = parseInt($(this).attr('data-status-id'), 10) || 0;
            var $card = $(draggedCard);
            var leadId = parseInt($card.attr('data-lead-id'), 10) || 0;
            var oldStatusId = draggedCardSourceStatusId;
            var badge = normalizeBadge($card.attr('data-badge'));
            var position = $card.index() + 1;

            $card.attr('data-status-id', destinationStatusId);
            $card.attr('data-position', position);

            // Instant optimistic color update
            var targetDot = $(this).closest('.kanban-column').find('.kanban-column-dot');
            if(targetDot.length && targetDot.css('background-color')) {
                $card.css('border-left-color', targetDot.css('background-color'));
            }

            refreshAllColumnCounts();
            lastDropSaveAt = new Date().getTime();
            cardDropped = true;

            // Fast 10ms single-card AJAX save when moving across columns
            if(leadId > 0 && destinationStatusId > 0) {
                setSaveStatus('<?php echo addslashes(__('Saving...'));?>', 'state-saving');
                $.ajax({
                    url: kanbanSaveUrl,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        id: leadId,
                        status_id: destinationStatusId,
                        badge: badge,
                        position: position
                    },
                    success: function(resp){
                        if(resp && resp.success) {
                            setSaveStatus('<?php echo addslashes(__('Auto-saved'));?>', 'state-ok');
                        } else {
                            setSaveStatus((resp && resp.message) ? resp.message : '<?php echo addslashes(__('Save failed'));?>', 'state-error');
                        }
                    },
                    error: function(){
                        setSaveStatus('<?php echo addslashes(__('Save failed'));?>', 'state-error');
                    }
                });
            } else {
                persistBoard([draggedCardSourceStatusId, destinationStatusId]);
            }
        });

        $('#modernKanbanBoard').on('dragstart', '.kanban-column-head', function(e){
            if(draggedCard)
            {
                return;
            }
            draggedColumn = $(this).closest('.kanban-column').get(0);
            $(draggedColumn).addClass('is-column-dragging');
            if(e.originalEvent && e.originalEvent.dataTransfer)
            {
                e.originalEvent.dataTransfer.effectAllowed = 'move';
                e.originalEvent.dataTransfer.setData('text/plain', 'column');
            }
        });

        $('#modernKanbanBoard').on('dragend', '.kanban-column-head', function(){
            if(draggedColumn)
            {
                $(draggedColumn).removeClass('is-column-dragging');
            }
            draggedColumn = null;
            saveColumnOrder();
        });

        $('#modernKanbanBoard').on('input', '.kanban-column-search-input', function(){
            var $column = $(this).closest('.kanban-column');
            applyColumnFilter($column);
            refreshColumnCount($column);
            refreshEmptyState($column.find('.kanban-cards'));
        });

        $('#modernKanbanBoard').on('dragover', function(e){
            if(!draggedColumn)
            {
                return;
            }
            e.preventDefault();
            var afterColumn = getColumnAfterElement(this, e.originalEvent.clientX);
            if(afterColumn === null)
            {
                this.appendChild(draggedColumn);
            }
            else
            {
                this.insertBefore(draggedColumn, afterColumn);
            }
        });

        $('#modernKanbanBoard').on('change', '.kanban-badge-select', function(){
            var $card = $(this).closest('.kanban-card');
            var badge = normalizeBadge($(this).val());
            applyBadgeClass($card, badge);
            var $columnForBadge = $card.closest('.kanban-column');
            applyColumnFilter($columnForBadge);
            refreshColumnCount($columnForBadge);
            refreshEmptyState($columnForBadge.find('.kanban-cards'));
            var leadId = parseInt($card.attr('data-lead-id'), 10) || 0;
            var statusId = parseInt($card.attr('data-status-id'), 10) || 0;
            if(leadId > 0 && statusId > 0)
            {
                setSaveStatus('<?php echo addslashes(__('Saving...'));?>', 'state-saving');
                $.ajax({
                    url: kanbanSaveUrl,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        id: leadId,
                        status_id: statusId,
                        badge: badge
                    },
                    success: function(resp){
                        if(resp && resp.success)
                        {
                            setSaveStatus('<?php echo addslashes(__('Auto-saved'));?>', 'state-ok');
                        }
                        else
                        {
                            setSaveStatus((resp && resp.message) ? resp.message : '<?php echo addslashes(__('Save failed'));?>', 'state-error');
                        }
                    },
                    error: function(){
                        setSaveStatus('<?php echo addslashes(__('Save failed'));?>', 'state-error');
                    }
                });
            }
        });

        $('#modernKanbanBoard').on('change', '.kanban-status-select', function(){
            ensureStatusOptions($(this));
            var statusId = parseInt($(this).val(), 10) || 0;
            var $card = $(this).closest('.kanban-card');
            var oldStatusId = parseInt($card.attr('data-status-id'), 10) || 0;
            var $target = $('.kanban-cards[data-status-id="' + statusId + '"]');
            if(statusId <= 0 || !$target.length)
            {
                return;
            }
            $card.attr('data-status-id', statusId);
            $target.prepend($card);
            var $oldColumn = $('.kanban-column[data-status-id="' + oldStatusId + '"]');
            applyColumnFilter($oldColumn);
            refreshColumnCount($oldColumn);
            refreshEmptyState($oldColumn.find('.kanban-cards'));
            applyColumnFilter($target.closest('.kanban-column'));
            refreshColumnCount($target.closest('.kanban-column'));
            refreshEmptyState($target);
            persistBoard([oldStatusId, statusId]);
        });

        $('#modernKanbanBoard').on('focus mousedown', '.kanban-status-select', function(){
            ensureStatusOptions($(this));
        });

        $('#modernKanbanBoard').on('click', '.lead-move-up', function(){
            var $card = $(this).closest('.kanban-card');
            var statusId = parseInt($card.attr('data-status-id'), 10) || 0;
            var $prev = $card.prev('.kanban-card');
            if($prev.length)
            {
                $card.insertBefore($prev);
                persistBoard([statusId]);
            }
        });

        $('#modernKanbanBoard').on('click', '.lead-move-down', function(){
            var $card = $(this).closest('.kanban-card');
            var statusId = parseInt($card.attr('data-status-id'), 10) || 0;
            var $next = $card.next('.kanban-card');
            if($next.length)
            {
                $card.insertAfter($next);
                persistBoard([statusId]);
            }
        });

        function saveKanbanCardFollowUp($card, followUpValue)
        {
            var leadId = parseInt($card.attr('data-lead-id'), 10) || 0;
            var statusId = parseInt($card.attr('data-status-id'), 10) || 0;
            var badge = normalizeBadge($card.attr('data-badge'));
            if(leadId <= 0 || statusId <= 0)
            {
                return;
            }
            if(followUpValue === '')
            {
                return;
            }
            var lastSavedValue = $.trim(String($card.attr('data-followup-last') || ''));
            if(lastSavedValue !== '' && lastSavedValue === followUpValue)
            {
                return;
            }

            setSaveStatus('<?php echo addslashes(__('Saving...'));?>', 'state-saving');
            $.ajax({
                url: kanbanSaveUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    id: leadId,
                    status_id: statusId,
                    badge: badge,
                    follow_up: followUpValue
                },
                success: function(resp){
                    if(resp && resp.success)
                    {
                        $card.attr('data-followup-last', followUpValue);
                        setSaveStatus('<?php echo addslashes(__('Follow Up updated'));?>', 'state-ok');
                    }
                    else
                    {
                        setSaveStatus((resp && resp.message) ? resp.message : '<?php echo addslashes(__('Unable to update Follow Up'));?>', 'state-error');
                    }
                },
                error: function(){
                    setSaveStatus('<?php echo addslashes(__('Unable to update Follow Up'));?>', 'state-error');
                }
            });
        }

        function queueKanbanCardFollowUpSave($card, followUpValue)
        {
            var leadId = parseInt($card.attr('data-lead-id'), 10) || 0;
            if(leadId <= 0)
            {
                return;
            }
            if(followUpSaveTimers[leadId])
            {
                window.clearTimeout(followUpSaveTimers[leadId]);
            }
            followUpSaveTimers[leadId] = window.setTimeout(function(){
                saveKanbanCardFollowUp($card, followUpValue);
            }, 160);
        }

        function initKanbanFollowupPicker($picker)
        {
            if(!$picker || !$picker.length || !$.fn.datetimepicker)
            {
                return;
            }
            if(parseInt($picker.attr('data-picker-ready'), 10) === 1)
            {
                return;
            }
            $picker.datetimepicker({
                locale: '<?php echo $configLanguage;?>',
                format: kanbanDateFormat,
                useCurrent: false,
                sideBySide: true
            });
            $picker.on('dp.change.kanban', function(){
                var $self = $(this);
                if(parseInt($self.attr('data-initializing'), 10) === 1)
                {
                    return;
                }
                window.setTimeout(function(){
                    var $card = $self.closest('.kanban-card');
                    var followUpValue = $.trim($self.find('.kanban-followup-input').val());
                    queueKanbanCardFollowUpSave($card, followUpValue);
                }, 30);
            });

            var rawFollowUp = $.trim(String($picker.attr('data-followup-raw') || ''));
            if(rawFollowUp !== '' && rawFollowUp !== '0000-00-00 00:00:00' && typeof moment !== 'undefined')
            {
                var followMoment = moment(rawFollowUp, 'YYYY-MM-DD HH:mm:ss', true);
                if(!followMoment.isValid())
                {
                    followMoment = moment(rawFollowUp);
                }
                if(followMoment.isValid())
                {
                    $picker.attr('data-initializing', '1');
                    $picker.data('DateTimePicker').date(followMoment);
                    $picker.attr('data-initializing', '0');
                }
            }
            $picker.attr('data-picker-ready', '1');
        }

        $('#modernKanbanBoard').on('focusin click', '.kanban-followup-input, .kanban-followup-picker .input-group-addon, .kanban-followup-picker .fa-calendar', function(){
            var $picker = $(this).closest('.kanban-followup-picker');
            initKanbanFollowupPicker($picker);
            if($picker.data('DateTimePicker'))
            {
                $picker.data('DateTimePicker').show();
            }
        });

        $('#modernKanbanBoard').on('change blur', '.kanban-followup-input', function(){
            var $input = $(this);
            var followUpValue = $.trim($input.val());
            if(followUpValue === '')
            {
                return;
            }
            var $card = $input.closest('.kanban-card');
            queueKanbanCardFollowUpSave($card, followUpValue);
        });
    });
})(jQuery);
</script>

<div class="page-title">
    <div class="title-env">
        <h1 class="title"><?php echo __('Leads Kanban Board');?></h1>
    </div>
</div>

<div class="panel">
    <div class="panel-heading">
        <div class="btn-group">
            <?php echo $this->Html->link('<span class="fa fa-dashboard"></span>&nbsp;'.__('CRM Dashboard'),array('controller'=>'Leads','action'=>'dashboard'),array('escape'=>false,'class'=>'btn btn-default'));?>
            <?php echo $this->Html->link('<span class="fa fa-plus"></span>&nbsp;'.__('Add Lead Contact'),array('controller'=>'Leads','action'=>'leadadd'),array('escape'=>false,'class'=>'btn btn-success'));?>
            <?php echo $this->Html->link('<span class="fa fa-upload"></span>&nbsp;'.__('Import Leads'),array('controller'=>'Leads','action'=>'import'),array('escape'=>false,'class'=>'btn btn-info'));?>
            <?php echo $this->Html->link('<span class="fa fa-trophy"></span>&nbsp;'.__('Leaderboard'),array('controller'=>'Leads','action'=>'leaderboard'),array('escape'=>false,'class'=>'btn btn-warning'));?>
            <?php echo $this->Html->link('<span class="fa fa-list"></span>&nbsp;'.__('Manage Leads'),array('controller'=>'Leads','action'=>'manage'),array('escape'=>false,'class'=>'btn btn-primary'));?>
            <?php echo $this->Html->link('<span class="fa fa-clock-o"></span>&nbsp;'.__('Follow-up Queue'),$kanbanFollowUpQueueUrl,array('escape'=>false,'class'=>'btn btn-default'));?>
            <?php echo $this->Html->link('<span class="fa fa-bullseye"></span>&nbsp;'.__('Lead Reactivation'),$kanbanLeadReactivationUrl,array('escape'=>false,'class'=>'btn btn-success'));?>
            <?php echo $this->Html->link('<span class="fa fa-pie-chart"></span>&nbsp;'.__('Source ROI'),$kanbanSourceRoiUrl,array('escape'=>false,'class'=>'btn btn-info'));?>
        </div>
    </div>
    <div class="panel-body">
        <?php echo $this->Session->flash();?>
        <?php echo $this->Form->create(false, array('type' => 'get', 'url' => array('controller' => 'Leads', 'action' => 'kanban'), 'class' => 'kanban-filter-panel', 'novalidate' => true));?>
        <div class="kanban-filter-grid">
            <?php if($kanbanCanUseAgencyFilters){ ?>
            <div class="kanban-filter-field">
                <label for="kanbanFilterProjectId"><?php echo __('Agency');?></label>
                <?php echo $this->Form->select('kanban_project_id', (array)$projectOptions, array('name' => 'project_id', 'id' => 'kanbanFilterProjectId', 'empty' => __('All Agency'), 'label' => false, 'class' => 'form-control', 'div' => false, 'required' => false, 'value' => $selectedProjectFilter));?>
            </div>
            <?php } ?>
            <?php if($kanbanCanUseUserLevelFilters){ ?>
            <div class="kanban-filter-field">
                <label for="kanbanFilterUserLevelId"><?php echo __('User Level');?></label>
                <?php echo $this->Form->select('kanban_ugroup_id', (array)$userLevelOptions, array('name' => 'ugroup_id', 'id' => 'kanbanFilterUserLevelId', 'empty' => __('All User Levels'), 'label' => false, 'class' => 'form-control', 'div' => false, 'required' => false, 'value' => $selectedUserLevelFilter));?>
            </div>
            <?php } ?>
            <?php if($kanbanCanUseAgentFilters){ ?>
            <div class="kanban-filter-field">
                <label for="kanbanFilterUserId"><?php echo __('User');?></label>
                <div id="kanbanFilterUserWrap">
                    <?php echo $this->Form->select('kanban_user_id', (array)$agentFilterOptions, array('name' => 'user_id', 'id' => 'kanbanFilterUserId', 'empty' => __('All Users'), 'label' => false, 'class' => 'form-control', 'div' => false, 'required' => false, 'value' => $selectedUserFilter));?>
                </div>
            </div>
            <?php } ?>
            <div class="kanban-filter-field">
                <label for="kanbanFilterPeriod"><?php echo __('Period');?></label>
                <?php echo $this->Form->select('kanban_period', (array)$kanbanPeriodOptions, array('name' => 'period', 'id' => 'kanbanFilterPeriod', 'label' => false, 'class' => 'form-control', 'div' => false, 'required' => false, 'value' => $selectedPeriodFilter));?>
            </div>
            <div class="kanban-filter-field">
                <label for="kanbanFilterPhoneRegion"><?php echo __('Phone Region');?></label>
                <?php
                $phoneRegionOptions = array(
                    'all' => __('All Phone Numbers'),
                    'overseas' => __('🌍 Overseas Numbers Only'),
                    'local' => __('🇵🇰 Local (Pakistan) Only')
                );
                echo $this->Form->select('kanban_phone_region', $phoneRegionOptions, array('name' => 'phone_region', 'id' => 'kanbanFilterPhoneRegion', 'label' => false, 'class' => 'form-control', 'div' => false, 'required' => false, 'value' => $selectedPhoneRegion));
                ?>
            </div>
            <div class="kanban-filter-field kanban-custom-date-wrap">
                <label for="kanbanFilterStartDate"><?php echo __('Start Date');?></label>
                <input type="date" name="start_date" id="kanbanFilterStartDate" value="<?php echo h($selectedStartDateInput);?>" class="form-control" required="false">
            </div>
            <div class="kanban-filter-field kanban-custom-date-wrap">
                <label for="kanbanFilterEndDate"><?php echo __('End Date');?></label>
                <input type="date" name="end_date" id="kanbanFilterEndDate" value="<?php echo h($selectedEndDateInput);?>" class="form-control" required="false">
            </div>
            <div class="kanban-filter-actions">
                <button type="submit" class="btn btn-primary"><span class="fa fa-filter"></span> <?php echo __('Apply');?></button>
                <?php echo $this->Html->link('<span class="fa fa-refresh"></span> '.__('Reset'), array('controller' => 'Leads', 'action' => 'kanban'), array('class' => 'btn btn-default', 'escape' => false));?>
            </div>
        </div>
        <?php if($selectedFilterSummary !== ''){ ?>
        <div class="kanban-filter-summary">
            <strong><?php echo __('Current Filters');?>:</strong> <?php echo h($selectedFilterSummary);?>
        </div>
        <?php } ?>
        <?php echo $this->Form->end();?>

        <div class="clearfix kanban-board-meta">
            <div style="float:left;">
                <span class="kanban-total-chip"><?php echo __('Total Leads');?>: <?php echo (int)$leadTotal;?></span>
            </div>
            <div class="kanban-badges-legend" style="float:right;">
                <span class="kanban-badge-pill hot">&#128293; <?php echo __('Hot');?></span>
                <span class="kanban-badge-pill warm">&#127780; <?php echo __('Warm');?></span>
                <span class="kanban-badge-pill cold">&#10052; <?php echo __('Cold');?></span>
                <span id="kanbanSaveState" class="kanban-save-status state-ready"><?php echo __('Auto-save ready');?></span>
            </div>
        </div>
        <div class="clearfix"></div>

        <div id="modernKanbanBoard" class="modern-kanban-board">
            <?php foreach((array)$statusRows as $statusRow){
                $sid = (int)$statusRow['Status']['id'];
                $cards = isset($kanbanRows[$sid]) ? $kanbanRows[$sid] : array();
                $color = !empty($statusRow['Status']['color']) ? $statusRow['Status']['color'] : '#3a87ad';
            ?>
            <section class="kanban-column" data-status-id="<?php echo $sid;?>">
                <div class="kanban-column-head" draggable="true">
                    <div class="kanban-column-title-row">
                        <h3 class="kanban-column-title">
                            <span class="kanban-column-dot" style="background:<?php echo h($color);?>;"></span>
                            <?php echo h($statusRow['Status']['name']);?>
                        </h3>
                        <span class="kanban-column-count"><?php echo count($cards);?></span>
                    </div>
                </div>
                <div class="kanban-column-search">
                    <div class="input-group input-group-sm">
                        <span class="input-group-addon"><span class="fa fa-search"></span></span>
                        <input type="text" class="form-control kanban-column-search-input" placeholder="<?php echo __('Search leads in this status');?>">
                    </div>
                </div>
                <div class="kanban-cards" data-status-id="<?php echo $sid;?>">
                    <?php foreach($cards as $leadRow){
                        $leadId = (int)$leadRow['Lead']['id'];
                        $leadName = trim((string)$leadRow['Lead']['lead_name'])!=='' ? $leadRow['Lead']['lead_name'] : (isset($leadRow['Client']['name']) ? $leadRow['Client']['name'] : '');
                        $leadMobile = trim((string)$leadRow['Lead']['lead_mobile'])!=='' ? $leadRow['Lead']['lead_mobile'] : (isset($leadRow['Client']['mobile']) ? $leadRow['Client']['mobile'] : '');
                        $leadCallNo = preg_replace('/[^0-9\+]/', '', (string)$leadMobile);
                        $leadEmail = trim((string)$leadRow['Lead']['lead_email'])!=='' ? $leadRow['Lead']['lead_email'] : (isset($leadRow['Client']['email']) ? $leadRow['Client']['email'] : '');
                        $leadBadge = strtolower(trim((string)$leadRow['Lead']['lead_temperature']));
                        if(!in_array($leadBadge, array('hot','warm','cold'), true))
                        {
                            $leadBadge = 'warm';
                        }
                        $followUpRaw = isset($leadRow['Lead']['follow_up']) ? trim((string)$leadRow['Lead']['follow_up']) : '';
                        if($followUpRaw === '0000-00-00 00:00:00')
                        {
                            $followUpRaw = '';
                        }
                        $followUpFormatted = '';
                        if($followUpRaw !== '')
                        {
                            $followTs = strtotime($followUpRaw);
                            if($followTs !== false)
                            {
                                $followUpFormatted = date($kanbanFollowUpPhpDisplayFormat, $followTs);
                            }
                        }
                        $kanbanRemark = trim((string)$leadRow['Lead']['remarks']);
                        if($kanbanRemark === '')
                        {
                            $kanbanRemark = '-';
                        }
                        elseif(strlen($kanbanRemark) > 80)
                        {
                            $kanbanRemark = substr($kanbanRemark,0,80).'...';
                        }
                        $leadSearchText = trim(implode(' ', array(
                            $leadName,
                            $leadMobile,
                            $leadEmail,
                            isset($leadRow['Lead']['lead_source']) ? $leadRow['Lead']['lead_source'] : '',
                            $kanbanRemark
                        )));
                    ?>
                                        <?php
                        // Avatar initials generator
                        $nameParts = explode(' ', trim((string)$leadName));
                        $initials = '';
                        if(!empty($nameParts[0])) $initials .= mb_substr($nameParts[0], 0, 1, 'UTF-8');
                        if(!empty($nameParts[1])) $initials .= mb_substr($nameParts[1], 0, 1, 'UTF-8');
                        if($initials === '') $initials = '#'.$leadId;

                        // Overdue check
                        $isCardOverdue = (!empty($followUpRaw) && strtotime($followUpRaw) < time());
                        $isCardToday = (!empty($followUpRaw) && date('Y-m-d', strtotime($followUpRaw)) === date('Y-m-d'));

                        // WhatsApp URL
                        $waClean = preg_replace('/[^0-9]/', '', (string)$leadMobile);
                        if(strlen($waClean) === 11 && substr($waClean, 0, 1) === '0') $waClean = '92'.substr($waClean, 1);
                        elseif(strlen($waClean) === 10 && substr($waClean, 0, 1) === '3') $waClean = '92'.$waClean;
                        $waUrl = '';
                        if(strlen($waClean) >= 10){
                            $waMsg = urlencode("السلام علیکم " . ($leadName !== '' ? $leadName . " صاحب!" : "!") . " White Villas کے حوالے سے آپ کی انکوائری موصول ہوئی تھی۔ کیا ہم آپ کو تفصیلات شیئر کر سکتے ہیں؟");
                            $waUrl = 'https://wa.me/'.$waClean.'?text='.$waMsg;
                        }

                        $leadRegion = $detectRegion($leadMobile);
                        $isCardOverseas = ($leadRegion && $leadRegion['is_overseas']);
                    ?>
                    <article class="kanban-card badge-<?php echo h($leadBadge);?>" draggable="true" data-lead-id="<?php echo $leadId;?>" data-status-id="<?php echo $sid;?>" data-badge="<?php echo h($leadBadge);?>" data-position="<?php echo (int)$leadRow['Lead']['kanban_position'];?>" data-is-overseas="<?php echo $isCardOverseas ? '1' : '0';?>" data-search-text="<?php echo h($leadSearchText);?>">
                        <!-- Card Top: Avatar, Name & Temperature -->
                        <div class="kanban-card-top">
                            <div class="kanban-lead-identity">
                                <div class="kanban-avatar-badge"><?php echo h($initials);?></div>
                                <div class="kanban-lead-name-wrap">
                                    <h4 class="kanban-lead-name" title="<?php echo h($leadName);?>"><?php echo h($leadName);?></h4>
                                    <span class="kanban-lead-id-tag">#<?php echo $leadId;?></span>
                                </div>
                            </div>
                            <select class="form-control input-sm kanban-badge-select">
                                <?php foreach($badgeLabels as $badgeKey => $badgeText){ ?>
                                <option value="<?php echo h($badgeKey);?>" <?php if($leadBadge===$badgeKey){ echo 'selected="selected"'; }?>><?php echo $badgeIcons[$badgeKey].' '.h($badgeText);?></option>
                                <?php } ?>
                            </select>
                        </div>

                        <!-- Card Body: Contact Info -->
                        <div class="kanban-card-meta">
                            <?php if(trim((string)$leadMobile) !== ''){ ?>
                            <div class="kanban-meta-row">
                                <span class="fa fa-phone kanban-meta-icon" style="color:<?php echo $isCardOverseas ? '#4338ca' : '#2563eb';?>;"></span>
                                <a href="tel:<?php echo h($leadCallNo);?>" class="kanban-meta-link"><?php echo h($leadMobile);?></a>
                            </div>
                            <?php } ?>
                            <?php if(trim((string)$leadEmail) !== ''){ ?>
                            <div class="kanban-meta-row" title="<?php echo h($leadEmail);?>">
                                <span class="fa fa-envelope-o kanban-meta-icon"></span>
                                <span><?php echo h($leadEmail);?></span>
                            </div>
                            <?php } ?>

                            <!-- Badges (Source, Overseas, Followup Alert) -->
                            <div class="kanban-tags-row">
                                <?php if($isCardOverseas){ ?>
                                    <span class="kanban-pill" style="background:#4338ca;color:#fff;border:1px solid #3730a3;font-weight:700;" title="Overseas Client (<?php echo h($leadRegion['country']);?>)"><i class="fa fa-globe"></i> <?php echo h($leadRegion['badge']);?></span>
                                <?php } ?>
                                <?php if(!empty($leadRow['Lead']['lead_source'])){ ?>
                                    <span class="kanban-pill pill-source"><i class="fa fa-bullhorn"></i> <?php echo h($leadRow['Lead']['lead_source']);?></span>
                                <?php } ?>
                                <?php if($isCardOverdue){ ?>
                                    <span class="kanban-pill pill-overdue"><i class="fa fa-exclamation-triangle"></i> Overdue</span>
                                <?php } elseif($isCardToday){ ?>
                                    <span class="kanban-pill pill-today"><i class="fa fa-clock-o"></i> Today</span>
                                <?php } ?>
                            </div>

                            <!-- Remarks Snippet -->
                            <?php if($kanbanRemark !== '-'){ ?>
                            <div class="kanban-remark-box" title="<?php echo h($leadRow['Lead']['remarks']);?>">
                                <i class="fa fa-quote-left" style="font-size:9px;opacity:0.6;"></i> <?php echo h($kanbanRemark);?>
                            </div>
                            <?php } ?>

                            <!-- Inline Follow-up Picker -->
                            <div class="kanban-followup-wrap">
                                <div class="input-group date kanban-followup-picker" id="kanbanFollowUp<?php echo $leadId;?>" data-followup-raw="<?php echo h($followUpRaw);?>" data-initializing="0">
                                    <input type="text" class="form-control input-sm kanban-followup-input" value="<?php echo h($followUpFormatted);?>" placeholder="<?php echo __('Set Follow-up...');?>">
                                    <span class="input-group-addon"><span class="fa fa-calendar"></span></span>
                                </div>
                            </div>
                        </div>

                        <!-- Card Controls: Move up/down & Status select -->
                        <div class="kanban-card-controls">
                            <button type="button" class="btn btn-default btn-xs lead-move-up" title="<?php echo __('Move Up');?>" style="padding:2px 6px;height:26px;"><span class="fa fa-arrow-up"></span></button>
                            <button type="button" class="btn btn-default btn-xs lead-move-down" title="<?php echo __('Move Down');?>" style="padding:2px 6px;height:26px;"><span class="fa fa-arrow-down"></span></button>
                            <select class="form-control input-sm kanban-status-select" title="<?php echo __('Move to status');?>" data-options-loaded="0">
                                <option value="<?php echo (int)$sid;?>" selected="selected"><?php echo h($statusRow['Status']['name']);?></option>
                            </select>
                        </div>

                        <!-- Modern Professional Action Toolbar -->
                        <div class="kanban-action-bar">
                            <?php if($waUrl !== ''){ ?>
                                <a href="<?php echo h($waUrl);?>" target="_blank" class="crm-card-btn btn-wa" title="WhatsApp Chat"><span class="fa fa-whatsapp"></span> WhatsApp</a>
                            <?php } ?>
                            <button type="button" class="crm-card-btn btn-call crm-quick-log-btn"
                                data-lead-id="<?php echo $leadId;?>"
                                data-lead-name="<?php echo h($leadName);?>"
                                data-lead-mobile="<?php echo h($leadMobile);?>"
                                data-lead-status="<?php echo (int)$sid;?>"
                                data-lead-temp="<?php echo h($leadBadge);?>"
                                data-lead-followup="<?php echo h($followUpRaw);?>"
                                title="Quick Call Log &amp; Note"><span class="fa fa-phone"></span> Call</button>
                            <?php echo $this->Html->link('<span class="fa fa-eye"></span>',array('controller'=>'Leads','action'=>'leadview',$leadId),array('class'=>'crm-card-btn btn-view','escape'=>false,'title'=>__('View Profile')));?>
                            <?php echo $this->Html->link('<span class="fa fa-file-text-o"></span>',array('controller'=>'Leads','action'=>'quotation',$leadId),array('class'=>'crm-card-btn','style'=>'background:#eef2ff;color:#4f46e5;border-color:#c7d2fe;','escape'=>false,'title'=>__('Create Digital Quotation')));?>
                            <?php echo $this->Html->link('<span class="fa fa-pencil"></span>',array('controller'=>'Leads','action'=>'leadedit',$leadId),array('class'=>'crm-card-btn btn-edit','escape'=>false,'title'=>__('Edit Lead')));?>
                            <?php echo $this->Html->link('<span class="fa fa-comment"></span>',array('controller'=>'Leads','action'=>'leadbulkmessage','sms',$leadId),array('class'=>'crm-card-btn btn-sms','escape'=>false,'title'=>__('Send SMS')));?>
                            <?php echo $this->Html->link('<span class="fa fa-address-card-o"></span>',array('controller'=>'Leads','action'=>'convertcontact',$leadId,'?' => array('phone' => 1)),array('class'=>'crm-card-btn btn-vcard','escape'=>false,'title'=>__('Save to Phone Contacts'),'target'=>'_blank'));?>
                        </div>
                    </article>
                    <?php } ?>
                    <div class="kanban-empty" <?php if(!empty($cards)){?>style="display:none;"<?php } ?>>
                        <?php echo __('No leads');?>
                    </div>
                </div>
            </section>
            <?php } ?>
        </div>
    </div>
</div>

<!-- Quick Call / Activity Log Modal -->
<div class="modal fade" id="crmQuickLogModal" tabindex="-1" role="dialog" aria-labelledby="crmQuickLogModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="border-radius:10px;box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2);">
            <div class="modal-header" style="background:#1e40af;color:#fff;border-top-left-radius:10px;border-top-right-radius:10px;padding:14px 18px;">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:#fff;opacity:0.85;"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="crmQuickLogModalLabel" style="font-weight:800;"><span class="fa fa-phone"></span> Quick Lead Update &amp; Call Log</h4>
            </div>
            <form id="crmQuickLogForm" method="post" action="<?php echo $this->Html->url(array('controller'=>'Leads','action'=>'quicklog'));?>">
                <input type="hidden" name="id" id="quickLogLeadId" value="">
                <div class="modal-body" style="padding:18px;">
                    <div class="well well-sm" style="background:#f8fafc;border:1px solid #e2e8f0;margin-bottom:14px;border-radius:8px;">
                        <div class="row">
                            <div class="col-xs-7">
                                <strong id="quickLogLeadName" style="font-size:15px;color:#0f172a;">-</strong>
                            </div>
                            <div class="col-xs-5 text-right">
                                <span id="quickLogLeadMobile" style="font-size:14px;color:#2563eb;font-weight:700;">-</span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label style="font-weight:700;font-size:12px;">Pipeline Status</label>
                                <select name="status_id" id="quickLogStatusId" class="form-control input-sm">
                                    <?php foreach((array)$statusRows as $stRow){ ?>
                                        <option value="<?php echo (int)$stRow['Status']['id'];?>"><?php echo h($stRow['Status']['name']);?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label style="font-weight:700;font-size:12px;">Lead Temperature</label>
                                <select name="lead_temperature" id="quickLogTemperature" class="form-control input-sm">
                                    <option value="hot">🔥 Hot (Immediate Buying)</option>
                                    <option value="warm">⚡ Warm (Interested / Evaluating)</option>
                                    <option value="cold">❄️ Cold (Future / Low Budget)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label style="font-weight:700;font-size:12px;">Next Follow-up Date &amp; Time</label>
                        <input type="text" name="follow_up" id="quickLogFollowup" class="form-control input-sm datetimepicker" placeholder="YYYY-MM-DD HH:MM">
                    </div>

                    <div class="form-group">
                        <label style="font-weight:700;font-size:12px;">Call Outcome &amp; Activity Remarks</label>
                        <div style="margin-bottom:6px;">
                            <span class="label label-default crm-preset-chip" style="cursor:pointer;margin-right:4px;display:inline-block;padding:4px 8px;" onclick="addPresetRemark('Call Connected - Very Interested')">Call Connected</span>
                            <span class="label label-default crm-preset-chip" style="cursor:pointer;margin-right:4px;display:inline-block;padding:4px 8px;" onclick="addPresetRemark('Not Attended / Busy')">Not Attended</span>
                            <span class="label label-default crm-preset-chip" style="cursor:pointer;margin-right:4px;display:inline-block;padding:4px 8px;" onclick="addPresetRemark('Scheduled Site Visit for this Sunday')">Site Visit</span>
                            <span class="label label-default crm-preset-chip" style="cursor:pointer;margin-right:4px;display:inline-block;padding:4px 8px;" onclick="addPresetRemark('Sent Payment Plan on WhatsApp')">Sent WhatsApp</span>
                        </div>
                        <textarea name="remarks" id="quickLogRemarks" class="form-control" rows="3" placeholder="Enter notes or click quick chips above..."></textarea>
                    </div>
                </div>
                <div class="modal-footer" style="background:#f8fafc;border-bottom-left-radius:10px;border-bottom-right-radius:10px;">
                    <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Cancel</button>
                    <button type="submit" id="crmQuickLogSubmitBtn" class="btn btn-primary btn-sm" style="font-weight:700;"><span class="fa fa-check"></span> Save Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Lost Reason Modal -->
<div class="modal fade" id="crmLostReasonModal" tabindex="-1" role="dialog" aria-labelledby="crmLostReasonTitle">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content" style="border-radius:10px;overflow:hidden;box-shadow:0 8px 25px rgba(0,0,0,0.18);">
            <form id="crmLostReasonForm" action="<?php echo $this->Html->url(array('controller'=>'Leads','action'=>'updatestatus'));?>" method="post">
                <div class="modal-header" style="background:linear-gradient(135deg, #991b1b, #ef4444);color:#fff;padding:14px 18px;">
                    <button type="button" class="close" data-dismiss="modal" style="color:#fff;opacity:0.8;">&times;</button>
                    <h4 class="modal-title" id="crmLostReasonTitle" style="font-weight:700;font-size:15px;">
                        <i class="fa fa-times-circle"></i> <?php echo __('📉 Mark Lead as Lost / Cancelled');?>
                    </h4>
                </div>
                <div class="modal-body" style="padding:16px;">
                    <input type="hidden" name="id" id="lostReasonLeadId" value="">
                    <input type="hidden" name="status_id" id="lostReasonStatusId" value="">

                    <div style="margin-bottom:12px;">
                        <strong id="lostReasonLeadName" style="color:#0f172a;font-size:14px;">Lead</strong>
                    </div>

                    <div class="form-group" style="margin-bottom:12px;">
                        <label style="font-weight:700;color:#334155;font-size:12px;"><?php echo __('Why was this lead lost?');?></label>
                        <select name="lost_reason" id="lostReasonSelect" class="form-control input-sm" required>
                            <option value="Price too high / Budget issue">💰 Price too high / Budget issue</option>
                            <option value="Location not preferred">📍 Location not preferred</option>
                            <option value="Bought from competitor">🏢 Bought from competitor</option>
                            <option value="Invalid / Wrong number">📵 Invalid / Wrong number</option>
                            <option value="Unresponsive after multiple calls">⏳ Unresponsive after multiple calls</option>
                            <option value="Not interested / Plan postponed">🗓️ Not interested / Plan postponed</option>
                            <option value="Other">📝 Other Reason</option>
                        </select>
                    </div>

                    <div class="form-group" style="margin-bottom:0;">
                        <label style="font-weight:700;color:#334155;font-size:12px;"><?php echo __('Additional Notes:');?></label>
                        <textarea name="lost_notes" id="lostReasonNotes" rows="2" class="form-control input-sm" placeholder="<?php echo __('Optional notes explaining client feedback...');?>"></textarea>
                    </div>
                </div>
                <div class="modal-footer" style="background:#f8fafc;padding:10px 16px;">
                    <button type="button" class="btn btn-default btn-sm" data-dismiss="modal"><?php echo __('Cancel');?></button>
                    <button type="submit" id="crmLostReasonSubmitBtn" class="btn btn-danger btn-sm" style="font-weight:700;"><span class="fa fa-check"></span> <?php echo __('Confirm & Mark Lost');?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
function addPresetRemark(text) {
    var $ta = $('#quickLogRemarks');
    var cur = $.trim($ta.val());
    if (cur !== '') {
        $ta.val(cur + ' - ' + text);
    } else {
        $ta.val(text);
    }
}

$(document).on('click', '.crm-quick-log-btn', function(e){
    e.preventDefault();
    e.stopPropagation();
    var $btn = $(this);
    $('#quickLogLeadId').val($btn.data('lead-id'));
    $('#quickLogLeadName').text($btn.data('lead-name') || 'Lead #' + $btn.data('lead-id'));
    $('#quickLogLeadMobile').text($btn.data('lead-mobile') || '-');
    $('#quickLogStatusId').val($btn.data('lead-status'));
    $('#quickLogTemperature').val($btn.data('lead-temp') || 'warm');
    $('#quickLogFollowup').val($btn.data('lead-followup') || '');
    $('#quickLogRemarks').val('');
    $('#crmQuickLogModal').modal('show');
});

$('#crmQuickLogForm').on('submit', function(e){
    e.preventDefault();
    var $form = $(this);
    var $btn = $('#crmQuickLogSubmitBtn');
    $btn.prop('disabled', true).html('<span class="fa fa-spinner fa-spin"></span> Saving...');

    $.ajax({
        url: $form.attr('action'),
        type: 'POST',
        data: $form.serialize(),
        dataType: 'json',
        success: function(resp){
            $btn.prop('disabled', false).html('<span class="fa fa-check"></span> Save Update');
            if(resp && resp.status === 'success'){
                $('#crmQuickLogModal').modal('hide');
                var leadId = $('#quickLogLeadId').val();
                var newStatusId = parseInt($('#quickLogStatusId').val(), 10);
                var newTemp = $('#quickLogTemperature').val();
                var $card = $('.kanban-card[data-lead-id="' + leadId + '"]');
                if($card.length){
                    $card.removeClass('badge-hot badge-warm badge-cold').addClass('badge-' + newTemp);
                    $card.attr('data-badge', newTemp);
                    $card.find('.kanban-badge-select').val(newTemp);
                    var curStatusId = parseInt($card.attr('data-status-id'), 10);
                    if(curStatusId !== newStatusId){
                        var $targetCol = $('.kanban-cards[data-status-id="' + newStatusId + '"]');
                        if($targetCol.length){
                            $targetCol.prepend($card);
                            $card.attr('data-status-id', newStatusId);
                            $card.find('.kanban-status-select').val(newStatusId);
                            if(typeof refreshAllColumnCounts === 'function'){
                                refreshAllColumnCounts();
                            }
                        }
                    }
                }
                setSaveStatus('Auto-saved', 'state-ok');
            } else {
                alert((resp && resp.message) ? resp.message : 'Error saving lead update.');
            }
        },
        error: function(){
            $btn.prop('disabled', false).html('<span class="fa fa-check"></span> Save Update');
            alert('Server error while saving update.');
        }
    });
});

window.promptLostReason = function(leadId, statusId, leadName) {
    $('#lostReasonLeadId').val(leadId);
    $('#lostReasonStatusId').val(statusId);
    $('#lostReasonLeadName').text(leadName || 'Lead #' + leadId);
    $('#lostReasonNotes').val('');
    $('#crmLostReasonModal').modal('show');
};

$('#crmLostReasonForm').on('submit', function(e){
    e.preventDefault();
    var $form = $(this);
    var $btn = $('#crmLostReasonSubmitBtn');
    $btn.prop('disabled', true).html('<span class="fa fa-spinner fa-spin"></span> Saving...');

    $.ajax({
        url: $form.attr('action'),
        type: 'POST',
        data: $form.serialize(),
        dataType: 'json',
        success: function(resp){
            $btn.prop('disabled', false).html('<span class="fa fa-check"></span> Confirm & Mark Lost');
            if(resp && resp.success){
                $('#crmLostReasonModal').modal('hide');
                var leadId = $('#lostReasonLeadId').val();
                var newStatusId = parseInt($('#lostReasonStatusId').val(), 10);
                var $card = $('.kanban-card[data-lead-id="' + leadId + '"]');
                if($card.length && newStatusId > 0){
                    var $targetCol = $('.kanban-cards[data-status-id="' + newStatusId + '"]');
                    if($targetCol.length){
                        $targetCol.prepend($card);
                        $card.attr('data-status-id', newStatusId);
                        $card.find('.kanban-status-select').val(newStatusId);
                        if(typeof refreshAllColumnCounts === 'function'){
                            refreshAllColumnCounts();
                        }
                    }
                }
                setSaveStatus('Marked as Lost', 'state-ok');
            } else {
                alert((resp && resp.message) ? resp.message : 'Error updating status.');
            }
        },
        error: function(){
            $btn.prop('disabled', false).html('<span class="fa fa-check"></span> Confirm & Mark Lost');
            alert('Server error while saving.');
        }
    });
});
</script>