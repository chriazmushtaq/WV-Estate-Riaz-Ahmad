<script type="text/javascript">
jQuery(document).ready(function(){
    if(jQuery('#start_date').length && jQuery('#end_date').length)
    {
        jQuery('#start_date').datetimepicker({locale:'<?php echo$configLanguage;?>',format:'<?php echo $dpFormat;?>'});
        jQuery('#end_date').datetimepicker({locale:'<?php echo$configLanguage;?>',format:'<?php echo $dpFormat;?>',useCurrent:false});
        jQuery("#start_date").on("dp.change", function (e) {
            jQuery('#end_date').data("DateTimePicker").minDate(e.date);
        });
        jQuery("#end_date").on("dp.change", function (e) {
            jQuery('#start_date').data("DateTimePicker").maxDate(e.date);
        });
    }
});
var dealsHtml2PdfLoader = null;
var pendingDealControlUrl = '';
var pendingDealControlLabel = '';
var pendingNeedReason = false;

function updateDealControlStatus(changeUrl, statusLabel, needReason)
{
    pendingDealControlUrl = changeUrl;
    pendingDealControlLabel = statusLabel;
    pendingNeedReason = !!needReason;

    var statusColors = {
        'Active': '#10b981',
        'Blocked': '#2563eb',
        'Transferd': '#e83e8c',
        'Transferred': '#e83e8c',
        'Permanent Blocked': '#9c1b64',
        'Pemanent Blocked': '#9c1b64',
        'Refund': '#dc2626',
        'Refund-Running': '#f59e0b'
    };
    var statusIcons = {
        'Active': 'fa-check-circle',
        'Blocked': 'fa-lock',
        'Transferd': 'fa-exchange',
        'Transferred': 'fa-exchange',
        'Permanent Blocked': 'fa-ban',
        'Pemanent Blocked': 'fa-ban',
        'Refund': 'fa-reply',
        'Refund-Running': 'fa-hourglass-half'
    };

    var color = statusColors[statusLabel] || '#2563eb';
    var icon = statusIcons[statusLabel] || 'fa-shield';

    jQuery('#dealControlReasonModalTitle').text('<?php echo __("Change Deal Status: ");?>' + statusLabel);
    jQuery('#modalTargetStatusBadge').text(statusLabel).css({
        'background-color': color,
        'color': '#ffffff'
    });
    jQuery('#modalStatusIconBox').css('background-color', color);
    jQuery('#modalStatusIcon').attr('class', 'fa ' + icon);
    jQuery('#dealControlReasonInput').val('').css('border-color', '#cbd5e1');
    jQuery('#modalReasonError').hide();

    if (needReason) {
        jQuery('#modalReasonGroup').show();
        jQuery('#modalReasonRequiredMark').show();
        jQuery('#modalReasonHint').text('<?php echo __("Required for this status");?>');
        jQuery('#modalConfirmBtnText').text('<?php echo __("Confirm & Update to ");?>' + statusLabel);
        jQuery('#btnConfirmDealControlChange').css({'background-color': color, 'border-color': color});
    } else {
        jQuery('#modalReasonGroup').show();
        jQuery('#modalReasonRequiredMark').hide();
        jQuery('#modalReasonHint').text('<?php echo __("Optional reason note");?>');
        jQuery('#modalConfirmBtnText').text('<?php echo __("Confirm: ");?>' + statusLabel);
        jQuery('#btnConfirmDealControlChange').css({'background-color': color, 'border-color': color});
    }

    jQuery('#dealControlReasonModal').modal('show');
    setTimeout(function() {
        if (needReason) {
            jQuery('#dealControlReasonInput').focus();
        }
    }, 400);

    return false;
}

jQuery(document).on('click', '#btnConfirmDealControlChange', function(e) {
    e.preventDefault();
    var reason = jQuery.trim(jQuery('#dealControlReasonInput').val());

    if (pendingNeedReason && reason.length === 0) {
        jQuery('#dealControlReasonInput').css('border-color', '#dc2626').focus();
        jQuery('#modalReasonError').css('display', 'flex').show();
        return false;
    }

    var finalUrl = pendingDealControlUrl;
    if (reason.length > 0) {
        finalUrl += (finalUrl.indexOf('?') === -1 ? '?' : '&') + 'reason=' + encodeURIComponent(reason);
    }

    var $btn = jQuery(this);
    $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> <?php echo __("Updating...");?>');

    window.location.href = finalUrl;
});

jQuery(document).on('input propertychange', '#dealControlReasonInput', function() {
    if (jQuery.trim(jQuery(this).val()).length > 0) {
        jQuery(this).css('border-color', '#cbd5e1');
        jQuery('#modalReasonError').hide();
    }
});

jQuery(document).on('keydown', '#dealControlReasonInput', function(e) {
    if ((e.ctrlKey || e.metaKey) && (e.keyCode === 13 || e.keyCode === 10)) {
        e.preventDefault();
        jQuery('#btnConfirmDealControlChange').trigger('click');
    }
});
function ensureDealsHtml2Pdf()
{
    if(typeof html2pdf!=='undefined')
    {
        return jQuery.Deferred().resolve().promise();
    }
    if(dealsHtml2PdfLoader)
    {
        return dealsHtml2PdfLoader.promise();
    }
    dealsHtml2PdfLoader = jQuery.Deferred();
    var script = document.createElement('script');
    script.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js';
    script.onload = function(){dealsHtml2PdfLoader.resolve();};
    script.onerror = function(){
        alert('Unable to load PDF library.');
        dealsHtml2PdfLoader.reject();
        dealsHtml2PdfLoader = null;
    };
    document.getElementsByTagName('head')[0].appendChild(script);
    return dealsHtml2PdfLoader.promise();
}
function dealsPdfStamp()
{
    var now = new Date();
    var pad = function(n){ return n<10 ? '0'+n : n; };
    return now.getFullYear()+''+pad(now.getMonth()+1)+''+pad(now.getDate())+'_'+pad(now.getHours())+''+pad(now.getMinutes())+''+pad(now.getSeconds());
}
function exportDealRecordsPdf()
{
    ensureDealsHtml2Pdf().done(function(){
        var sourceElement = document.getElementById('printable_content');
        if(!sourceElement)
        {
            alert('No records found for PDF export.');
            return;
        }
        var $sourceClone = jQuery(sourceElement).clone();
        $sourceClone.find('.pbutton').css('display','none');
        $sourceClone.find('.deals-report-brand-header').css('display','table-row');
        var $wrapper = jQuery('<div id="deals-pdf-export-wrapper"></div>').css({
            position: 'absolute',
            left: '-100000px',
            top: '0',
            width: '1200px',
            background: '#fff',
            padding: '5px'
        });
        $wrapper.append($sourceClone);
        jQuery('body').append($wrapper);
        var opt = {
            margin:       [0.20,0.20,0.20,0.20],
            filename:     'deals_'+dealsPdfStamp()+'.pdf',
            image:        { type: 'jpeg', quality: 0.98 },
            html2canvas:  { scale: 2, useCORS: true },
            jsPDF:        { unit: 'in', format: 'a4', orientation: 'landscape' }
        };
        var worker = html2pdf().set(opt).from($wrapper[0]).save();
        var cleanup = function(){ $wrapper.remove(); };
        if(worker && typeof worker.then==='function')
        {
            worker.then(function(){cleanup();}).catch(function(){cleanup();});
        }
        else
        {
            cleanup();
        }
    });
    return false;
}
</script>
<style type="text/css">
.deals-page-shell {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 14px;
    margin-top: 4px;
}
.deals-page-panel {
    border: 0;
    background: transparent;
    box-shadow: none;
}
.deals-toolbar-card,
body.wv-admin-theme .panel-heading.deals-toolbar-card {
    background: #ffffff !important;
    border: 1px solid #d8e2ee !important;
    border-radius: 10px !important;
    padding: 14px 16px !important;
    margin-bottom: 14px !important;
    box-shadow: 0 4px 16px rgba(15, 23, 42, 0.05) !important;
}

/* Deals Top Action & KPI Bar */
.deals-top-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    width: 100%;
}
.deals-actions-group {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
}
.deals-actions-group .btn {
    border-radius: 6px !important;
    font-weight: 700 !important;
    font-size: 12px !important;
    height: 35px !important;
    padding: 0 13px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 6px !important;
    white-space: nowrap !important;
    box-shadow: 0 2px 4px rgba(15, 23, 42, 0.06) !important;
    transition: all 0.15s ease !important;
    text-decoration: none !important;
    line-height: 1 !important;
}
.deals-actions-group .btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(15, 23, 42, 0.12) !important;
}
.deals-actions-group .btn-success {
    background: #10b981 !important;
    border-color: #10b981 !important;
    color: #ffffff !important;
}
.deals-actions-group .btn-success:hover {
    background: #059669 !important;
    border-color: #059669 !important;
}
.deals-actions-group .btn-danger {
    background: #ef4444 !important;
    border-color: #ef4444 !important;
    color: #ffffff !important;
}
.deals-actions-group .btn-danger:hover {
    background: #dc2626 !important;
    border-color: #dc2626 !important;
}
.deals-actions-group .btn-default,
body.wv-admin-theme .panel-heading .deals-actions-group .btn-default {
    background: #ffffff !important;
    border: 1px solid #cbd5e1 !important;
    color: #334155 !important;
}
.deals-actions-group .btn-default:hover,
body.wv-admin-theme .panel-heading .deals-actions-group .btn-default:hover {
    background: #f8fafc !important;
    color: #0f172a !important;
}
.deals-actions-group .btn-info {
    background: #0284c7 !important;
    border-color: #0284c7 !important;
    color: #ffffff !important;
}
.deals-actions-group .btn-info:hover {
    background: #0369a1 !important;
    border-color: #0369a1 !important;
}
.deals-actions-group .btn-primary {
    background: #2563eb !important;
    border-color: #2563eb !important;
    color: #ffffff !important;
}
.deals-actions-group .btn-primary:hover {
    background: #1d4ed8 !important;
    border-color: #1d4ed8 !important;
}

/* Deals KPI Health Pills */
.deals-kpi-group {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
    margin-left: auto;
}
.deals-kpi-group .health-kpi-pill {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    gap: 8px !important;
    height: 35px !important;
    padding: 0 12px !important;
    border-radius: 6px !important;
    font-size: 11.5px !important;
    text-decoration: none !important;
    white-space: nowrap !important;
    box-sizing: border-box !important;
    transition: all 0.15s ease !important;
}
.deals-kpi-group .health-kpi-pill .health-kpi-text {
    font-size: 11.5px !important;
    font-weight: 700 !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 5px !important;
}
.deals-kpi-group .health-kpi-pill .badge {
    border-radius: 10px !important;
    padding: 2px 7px !important;
    font-size: 11px !important;
    font-weight: 800 !important;
    line-height: 1.2 !important;
}

/* Deals 1-Line Filters Row */
.deals-filter-row {
    display: flex;
    flex-direction: row;
    align-items: center;
    gap: 8px;
    margin-top: 14px;
    padding-top: 14px;
    border-top: 1px solid #e5edf6;
    width: 100%;
}
.deals-filter-col {
    flex: 1 1 0;
    min-width: 0;
}
.deals-filter-select,
.deals-filter-col .form-control {
    height: 35px !important;
    border-radius: 6px !important;
    border: 1px solid #cbd5e1 !important;
    font-size: 12px !important;
    color: #1e293b !important;
    background-color: #ffffff !important;
    padding: 6px 10px !important;
    width: 100% !important;
    box-shadow: none !important;
    box-sizing: border-box !important;
    line-height: 1.4 !important;
    transition: border-color 0.15s ease, box-shadow 0.15s ease !important;
}
.deals-filter-select:focus,
.deals-filter-col .form-control:focus {
    border-color: #3b82f6 !important;
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.15) !important;
    outline: none !important;
}
.deals-filter-col-date .input-group {
    display: flex !important;
    width: 100% !important;
    margin: 0 !important;
}
.deals-filter-col-date .input-group .form-control {
    border-top-right-radius: 0 !important;
    border-bottom-right-radius: 0 !important;
    border-right: 0 !important;
    flex: 1 1 auto !important;
}
.deals-filter-col-date .input-group-addon {
    height: 35px !important;
    border-radius: 0 6px 6px 0 !important;
    border: 1px solid #cbd5e1 !important;
    border-left: 0 !important;
    background: #f8fafc !important;
    color: #64748b !important;
    padding: 0 10px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    box-sizing: border-box !important;
}
.deals-filter-actions {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    flex: 0 0 auto;
    white-space: nowrap;
}
.deals-filter-actions .btn {
    height: 35px !important;
    border-radius: 6px !important;
    font-weight: 700 !important;
    font-size: 12px !important;
    padding: 0 14px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 6px !important;
    white-space: nowrap !important;
    box-shadow: 0 2px 4px rgba(15, 23, 42, 0.08) !important;
    transition: all 0.15s ease !important;
    line-height: 1 !important;
}
.deals-filter-actions .btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(15, 23, 42, 0.12) !important;
}
.deals-filter-actions .btn-success {
    background: #10b981 !important;
    border-color: #10b981 !important;
    color: #ffffff !important;
}
.deals-filter-actions .btn-success:hover {
    background: #059669 !important;
    border-color: #059669 !important;
}
.deals-filter-actions .btn-warning {
    background: #f59e0b !important;
    border-color: #f59e0b !important;
    color: #ffffff !important;
}
.deals-filter-actions .btn-warning:hover {
    background: #d97706 !important;
    border-color: #d97706 !important;
}
.deal-control-cell {
    min-width: 185px !important;
    width: 185px !important;
    text-align: center !important;
    vertical-align: middle !important;
    white-space: nowrap !important;
    overflow: visible !important;
}
.deal-control-flex-wrap {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 6px !important;
    vertical-align: middle !important;
    width: auto !important;
    min-width: max-content !important;
    max-width: none !important;
    white-space: nowrap !important;
}
.deals-data-table {
    table-layout: auto;
    width: max-content;
    min-width: 100%;
}
.deals-data-table th,
.deals-data-table td {
    vertical-align: middle;
}
.deals-data-table th:not(.pbutton):not(.deal-status-col),
.deals-data-table td:not(.pbutton):not(.deal-status-col) {
    white-space: nowrap;
}
.deals-data-table .pbutton,
.deals-data-table .deal-status-col {
    width: auto;
    white-space: nowrap;
}
.deals-data-table .deal-control-col,
.deals-data-table .deal-control-cell,
.deals-data-table th.deal-control-col,
.deals-data-table td.deal-control-cell {
    min-width: 185px !important;
    width: 185px !important;
    text-align: center !important;
    white-space: nowrap !important;
    overflow: visible !important;
}
.deals-data-table .deal-status-col {
    min-width: 0;
    text-align: center;
}
.deals-data-table th.pbutton:first-child,
.deals-data-table td.pbutton:first-child {
    width: 40px !important;
    min-width: 40px !important;
}
.deals-data-table .deal-action-cell {
    width: auto !important;
    min-width: 110px !important;
}
.deal-control-toggle {
    font-size: 11px;
    display: inline-block;
    margin-left: 0;
    text-decoration: none;
    cursor: pointer;
}
.deal-control-reason-full {
    margin-top: 8px;
    padding: 8px 10px;
    background: #f8fafc;
    border: 1px solid #d8e2ee;
    border-radius: 8px;
    max-width: 280px;
    word-break: break-word;
    white-space: normal;
    color: #334155;
    box-shadow: inset 3px 0 0 #2563eb;
}
.deal-control-status-badge {
    border: 0;
    cursor: pointer;
    display: inline-block;
    line-height: 1.2;
    vertical-align: middle;
}
.deal-control-status-badge:hover,
.deal-control-status-badge:focus {
    color: #fff;
    opacity: .9;
    text-decoration: none;
    outline: none;
}
@media print {
    .pbutton {
        display: none !important;
    }
    .deals-report-brand-header {
        display: table-row !important;
    }
}
.deals-report-brand-header {
    width: 100%;
    display: none;
    border-bottom: 1px solid #d8e2ee;
    margin-bottom: 10px;
}
.deals-report-brand-logo-cell {
    width: 180px;
    vertical-align: middle;
}
.deals-report-brand-logo-cell img {
    max-height: 95px;
    width: auto;
}
.deals-report-brand-details {
    vertical-align: middle;
    text-align: center;
}
.deals-report-brand-details h2 {
    margin: 0 0 4px 0;
    color: #0f172a;
    font-weight: 800;
}
.deals-report-brand-line {
    margin: 0 auto;
    color: #475569;
}
.deals-print-area {
    background: #fff;
}
.deals-panel-body {
    padding: 0;
}
.deals-report-title {
    margin: 10px 0 14px 0;
    text-align: center;
}
.deals-report-title h3 {
    margin: 0;
    font-size: 24px;
    color: #0f172a;
    font-weight: 800;
    text-decoration: none;
}
.deals-table-wrap {
    border: 1px solid #d8e2ee;
    border-radius: 8px;
    overflow-x: auto;
    overflow-y: visible;
    max-width: 100%;
    -webkit-overflow-scrolling: touch;
    background: #fff;
    box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
}
.deals-data-table {
    margin-bottom: 0;
    border: 0;
}
.deals-data-table > thead > tr > th {
    background: #eef3f8;
    color: #0f172a;
    border-color: #d8e2ee;
    font-size: 12px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0;
    padding-top: 11px;
    padding-bottom: 11px;
}
.deals-data-table > tbody > tr > td {
    color: #334155;
    border-color: #e5edf6;
    padding-top: 10px;
    padding-bottom: 10px;
}
.deals-data-table > tbody > tr:hover > td {
    background: #f8fbff;
}
.deals-data-table a {
    color: #1d4ed8;
    font-weight: 700;
}
.deals-data-table .label {
    border-radius: 999px;
    padding: 4px 9px;
    font-size: 11px;
    font-weight: 800;
}
.deals-total-row td {
    background: #f1f5f9 !important;
    color: #0f172a;
    font-weight: 800;
}
.deal-action-cell .btn {
    min-width: 0;
    border-radius: 6px;
    font-weight: 700;
    padding-left: 10px;
    padding-right: 10px;
    white-space: nowrap;
}
/* Unified Deal Control Status Button & Dropdown in Table Cell */
.deal-control-status-btn {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 7px !important;
    height: 32px !important;
    min-height: 32px !important;
    width: auto !important;
    min-width: max-content !important;
    max-width: none !important;
    padding: 0 14px !important;
    border-radius: 999px !important;
    border: 1px solid rgba(255, 255, 255, 0.35) !important;
    color: #ffffff !important;
    font-size: 11.5px !important;
    font-weight: 800 !important;
    letter-spacing: 0.35px !important;
    text-transform: uppercase !important;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.16) !important;
    cursor: pointer !important;
    transition: all 0.18s cubic-bezier(0.4, 0, 0.2, 1) !important;
    white-space: nowrap !important;
    overflow: visible !important;
    word-break: normal !important;
    line-height: 1 !important;
    text-decoration: none !important;
    flex-shrink: 0 !important;
}
.deal-control-status-btn:hover,
.deal-control-status-btn:focus,
.deal-control-btn-group.open .deal-control-status-btn,
.btn-group.open .deal-control-status-btn {
    filter: brightness(1.1) !important;
    transform: translateY(-1px) !important;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.22) !important;
    color: #ffffff !important;
    outline: none !important;
    text-decoration: none !important;
}
.deal-control-status-btn .caret {
    margin-left: 5px !important;
    border-top: 4px solid #ffffff !important;
    border-right: 4px solid transparent !important;
    border-left: 4px solid transparent !important;
    border-bottom: 0 !important;
    display: inline-block !important;
    vertical-align: middle !important;
    transition: transform 0.15s ease !important;
    flex-shrink: 0 !important;
}
.btn-group.open .deal-control-status-btn .caret {
    transform: rotate(180deg) !important;
}
.btn-group.dropup .deal-control-status-btn .caret,
.btn-group.dropup.open .deal-control-status-btn .caret {
    transform: none !important;
    border-top: 0 !important;
    border-bottom: 4px solid #ffffff !important;
}
.deal-control-badge-dot {
    width: 7px !important;
    height: 7px !important;
    border-radius: 50% !important;
    background-color: #ffffff !important;
    display: inline-block !important;
    box-shadow: 0 0 0 1.5px rgba(255, 255, 255, 0.5) !important;
    flex-shrink: 0 !important;
}
.deal-control-badge-text {
    display: inline-block !important;
    white-space: nowrap !important;
    overflow: visible !important;
    text-overflow: clip !important;
    max-width: none !important;
    color: #ffffff !important;
    font-weight: 800 !important;
    font-size: 11.5px !important;
    letter-spacing: 0.3px !important;
    line-height: 1 !important;
    text-transform: uppercase !important;
    flex-shrink: 0 !important;
}
.deal-control-reason-icon {
    font-size: 11.5px !important;
    color: rgba(255, 255, 255, 0.95) !important;
    margin-left: 2px !important;
    flex-shrink: 0 !important;
}
.deal-control-btn-group {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    width: auto !important;
    max-width: none !important;
    min-width: max-content !important;
    margin: 0 !important;
    vertical-align: middle !important;
    white-space: nowrap !important;
}
.deal-control-dropdown-menu,
body > .dropdown-menu.deal-control-dropdown-menu {
    min-width: 220px !important;
    border-radius: 10px !important;
    border: 1px solid #cbd5e1 !important;
    box-shadow: 0 16px 36px -4px rgba(15, 23, 42, 0.22), 0 4px 10px -2px rgba(15, 23, 42, 0.08) !important;
    padding: 6px !important;
    background: #ffffff !important;
}
.deal-control-menu-reason {
    padding: 4px 8px 6px !important;
}
.deal-control-menu-reason-box {
    background: #f8fafc;
    border: 1px solid #d8e2ee;
    border-radius: 6px;
    padding: 7px 9px;
    font-size: 11px;
    color: #334155;
    box-shadow: inset 3px 0 0 #2563eb;
    max-width: 250px;
    white-space: normal;
    word-break: break-word;
    text-align: left;
}
.deal-control-menu-reason-box strong {
    color: #0f172a;
    display: block;
    margin-bottom: 2px;
}
.deal-control-menu-reason-box .deal-control-reason-text {
    line-height: 1.35;
    color: #475569;
}
.deal-control-dropdown-menu .dropdown-header,
body > .dropdown-menu.deal-control-dropdown-menu .dropdown-header {
    font-size: 11px !important;
    font-weight: 800 !important;
    color: #64748b !important;
    text-transform: uppercase !important;
    letter-spacing: 0.5px !important;
    padding: 6px 12px 4px !important;
}
.deal-control-dropdown-menu > li > a,
body > .dropdown-menu.deal-control-dropdown-menu > li > a {
    display: flex !important;
    align-items: center !important;
    gap: 8px !important;
    padding: 8px 12px !important;
    font-size: 12.5px !important;
    font-weight: 600 !important;
    color: #334155 !important;
    border-radius: 6px !important;
    transition: all 0.15s ease !important;
}
.deal-control-dropdown-menu > li > a:hover,
body > .dropdown-menu.deal-control-dropdown-menu > li > a:hover {
    background: #f1f5f9 !important;
    color: #0f172a !important;
}
.deal-control-dropdown-menu > li.active-status > a,
body > .dropdown-menu.deal-control-dropdown-menu > li.active-status > a {
    background: #eff6ff !important;
    color: #1d4ed8 !important;
    font-weight: 700 !important;
}
.deal-action-cell .dropdown-menu {
    border-color: #d8e2ee;
    border-radius: 8px;
    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.12);
}
.deal-action-cell .dropdown-menu > li > a {
    color: #334155;
    font-weight: 600;
    padding: 8px 14px;
}
.deal-action-cell .dropdown-menu > li > a:hover {
    background: #eef3f8;
    color: #0f172a;
}
.deals-report-datetime {
    margin-top: 12px;
    text-align: center;
    color: #64748b;
    font-size: 13px;
}
.deals-print-card {
    background: #fff;
    border: 1px solid #d8e2ee;
    border-radius: 8px;
    padding: 14px;
    box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
}
.health-kpi-pill,
body.wv-admin-theme .panel-heading a.health-kpi-pill,
body.wv-admin-theme .deals-toolbar-card .health-kpi-pill {
    transition: all 0.2s ease;
    box-shadow: 0 2px 6px rgba(15, 23, 42, 0.04);
    text-decoration: none !important;
}
.health-kpi-pill:hover,
body.wv-admin-theme .panel-heading a.health-kpi-pill:hover,
body.wv-admin-theme .deals-toolbar-card .health-kpi-pill:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.1);
    text-decoration: none !important;
}
.health-kpi-pill.active-kpi,
body.wv-admin-theme .panel-heading a.health-kpi-pill.active-kpi,
body.wv-admin-theme .deals-toolbar-card .health-kpi-pill.active-kpi {
    border-width: 2px !important;
    box-shadow: 0 4px 14px rgba(15, 23, 42, 0.15) !important;
}

/* Force readable text on light card backgrounds inside panel-heading */
body.wv-admin-theme .panel-heading a.health-kpi-healthy,
body.wv-admin-theme .panel-heading a.health-kpi-healthy span,
body.wv-admin-theme .panel-heading a.health-kpi-healthy .health-kpi-text,
.health-kpi-healthy,
.health-kpi-healthy .health-kpi-text {
    color: #064e3b !important;
    font-weight: 800 !important;
}
body.wv-admin-theme .panel-heading a.health-kpi-healthy i,
.health-kpi-healthy i {
    color: #059669 !important;
}

body.wv-admin-theme .panel-heading a.health-kpi-attention,
body.wv-admin-theme .panel-heading a.health-kpi-attention span,
body.wv-admin-theme .panel-heading a.health-kpi-attention .health-kpi-text,
.health-kpi-attention,
.health-kpi-attention .health-kpi-text {
    color: #78350f !important;
    font-weight: 800 !important;
}
body.wv-admin-theme .panel-heading a.health-kpi-attention i,
.health-kpi-attention i {
    color: #d97706 !important;
}

body.wv-admin-theme .panel-heading a.health-kpi-moderate,
body.wv-admin-theme .panel-heading a.health-kpi-moderate span,
body.wv-admin-theme .panel-heading a.health-kpi-moderate .health-kpi-text,
.health-kpi-moderate,
.health-kpi-moderate .health-kpi-text {
    color: #7c2d12 !important;
    font-weight: 800 !important;
}
body.wv-admin-theme .panel-heading a.health-kpi-moderate i,
.health-kpi-moderate i {
    color: #ea580c !important;
}

body.wv-admin-theme .panel-heading a.health-kpi-critical,
body.wv-admin-theme .panel-heading a.health-kpi-critical span,
body.wv-admin-theme .panel-heading a.health-kpi-critical .health-kpi-text,
.health-kpi-critical,
.health-kpi-critical .health-kpi-text {
    color: #7f1d1d !important;
    font-weight: 800 !important;
}
body.wv-admin-theme .panel-heading a.health-kpi-critical i,
.health-kpi-critical i {
    color: #dc2626 !important;
}

body.wv-admin-theme .panel-heading .health-kpi-pill .badge,
.health-kpi-pill .badge {
    color: #ffffff !important;
    font-weight: 800 !important;
    text-shadow: none !important;
}

body.wv-admin-theme .panel-heading .btn-default,
body.wv-admin-theme .deals-toolbar-card .btn-default {
    color: #1e293b !important;
    background-color: #ffffff !important;
    border-color: #cbd5e1 !important;
}
@media(max-width: 1199px) {
    .deals-filter-col {
        min-width: 110px;
    }
}
@media(max-width: 991px) {
    .deals-top-bar {
        flex-direction: column;
        align-items: stretch;
    }
    .deals-kpi-group {
        margin-left: 0;
        justify-content: flex-start;
    }
    .deals-filter-row {
        flex-wrap: wrap;
    }
    .deals-filter-col {
        flex: 1 1 calc(33.333% - 8px);
        min-width: 140px;
    }
    .deals-filter-actions {
        width: 100%;
        justify-content: flex-start;
        margin-top: 4px;
    }
}
@media(max-width: 640px) {
    .deals-filter-col {
        flex: 1 1 100%;
    }
    .deals-actions-group .btn,
    .deals-kpi-group .health-kpi-pill {
        flex: 1 1 calc(50% - 6px);
        justify-content: center;
    }
}
</style>
<?php
 $this->Js->JqueryEngine->jQueryObject = 'jQuery';
 $dealControlStatuses = isset($dealControlStatuses) ? $dealControlStatuses : array('Active','Blocked','Transferd','Permanent Blocked','Refund','Refund-Running');
 $dealPaymentStatuses = isset($dealPaymentStatuses) ? $dealPaymentStatuses : array('Paid','Partial');
 $dealPaymentBlockedStatuses = isset($dealPaymentBlockedStatuses) ? $dealPaymentBlockedStatuses : array('Transferd','Transferred','Permanent Blocked','Pemanent Blocked','Refund','Refund-Running');
 $canChangeDealControlStatus = isset($canChangeDealControlStatus) ? (bool)$canChangeDealControlStatus : false;
 $canAccessRiskScore = isset($canAccessRiskScore) ? (bool)$canAccessRiskScore : false;
 $canAccessForecast = isset($canAccessForecast) ? (bool)$canAccessForecast : false;
 $healthSummary = isset($healthSummary) && is_array($healthSummary) ? $healthSummary : array('total'=>0,'healthy'=>0,'attention'=>0,'moderate'=>0,'critical'=>0);
 $dealHealthMap = isset($dealHealthMap) && is_array($dealHealthMap) ? $dealHealthMap : array();
 $selectedHealthStatus = isset($selectedHealthStatus) ? $selectedHealthStatus : '';
 $selectedDealPaymentStatus = isset($selectedDealPaymentStatus) ? $selectedDealPaymentStatus : '';
 $selectedDealControlStatus = isset($selectedDealControlStatus) ? $selectedDealControlStatus : '';
 $canUseAgencyFilters = isset($canUseAgencyFilters) ? (bool)$canUseAgencyFilters : false;
 $selectedProjectFilter = isset($selectedProjectFilter) ? (int)$selectedProjectFilter : 0;
 $dealChargeTotalsMap = isset($dealChargeTotalsMap) && is_array($dealChargeTotalsMap) ? $dealChargeTotalsMap : array();
 $selectedStartDateInput = isset($selectedStartDateInput) ? $selectedStartDateInput : '';
 $selectedEndDateInput = isset($selectedEndDateInput) ? $selectedEndDateInput : '';
 $currentProjectId = isset($currentProjectId) ? $currentProjectId : null;
 $canShowDealAction = isset($canShowDealAction) ? (bool)$canShowDealAction : true;
 if(isset($this->request->params['named']['health_status']))$selectedHealthStatus=$this->request->params['named']['health_status'];
 if(isset($this->request->params['named']['deal_payment_status']))$selectedDealPaymentStatus=$this->request->params['named']['deal_payment_status'];
 if(isset($this->request->params['named']['deal_control_status']))$selectedDealControlStatus=$this->request->params['named']['deal_control_status'];
 if(isset($this->request->params['named']['project_id']))$selectedProjectFilter=(int)$this->request->params['named']['project_id'];
 if(isset($this->request->params['named']['start_date']))$selectedStartDateInput=$this->request->params['named']['start_date'];
 if(isset($this->request->params['named']['end_date']))$selectedEndDateInput=$this->request->params['named']['end_date'];
 $baseDealsIndexUrl = array('controller'=>'Deals','action'=>'index');
 $baseDealsExportUrl = array('controller'=>'Deals','action'=>'exportcsv');
 if($currentProjectId!==null && $currentProjectId!=='')
 {
    $baseDealsIndexUrl[] = $currentProjectId;
    $baseDealsExportUrl[] = $currentProjectId;
 }
 $searchFormUrl = $baseDealsIndexUrl;
 $resetUrl = $baseDealsIndexUrl;
 $exportNamedParams = array();
 if(isset($this->request->params['named']['isSearch']))$exportNamedParams['isSearch']=$this->request->params['named']['isSearch'];
 if(isset($this->request->params['named']['keyword']) && strlen(trim($this->request->params['named']['keyword']))>0)$exportNamedParams['keyword']=$this->request->params['named']['keyword'];
 if(strlen(trim($selectedHealthStatus))>0)$exportNamedParams['health_status']=$selectedHealthStatus;
 if(strlen(trim($selectedDealPaymentStatus))>0)$exportNamedParams['deal_payment_status']=$selectedDealPaymentStatus;
 if(strlen(trim($selectedDealControlStatus))>0)$exportNamedParams['deal_control_status']=$selectedDealControlStatus;
 if($canUseAgencyFilters && $selectedProjectFilter>0)$exportNamedParams['project_id']=$selectedProjectFilter;
 if(strlen(trim($selectedStartDateInput))>0)$exportNamedParams['start_date']=$selectedStartDateInput;
 if(strlen(trim($selectedEndDateInput))>0)$exportNamedParams['end_date']=$selectedEndDateInput;
 $exportCsvUrl = array_merge($baseDealsExportUrl,$exportNamedParams);
 $dealsLimitUrl = array('admin'=>true,'plugin'=>'admin','controller'=>'Deals','action'=>'index');
 if($currentProjectId!==null && $currentProjectId!=='')
 {
    $dealsLimitUrl[] = $currentProjectId;
 }
 if(isset($this->params['named']) && is_array($this->params['named']))
 {
    foreach($this->params['named'] as $limitUrlKey=>$limitUrlValue)
    {
        if(in_array($limitUrlKey,array('limit','page'),true))
        {
            continue;
        }
        $dealsLimitUrl[$limitUrlKey] = $limitUrlValue;
    }
    unset($limitUrlKey,$limitUrlValue);
 }
 $dealControlFilterOptions=array();
 foreach($dealControlStatuses as $dealControlFilterStatus)
 {
    $dealControlFilterOptions[$dealControlFilterStatus]=__($dealControlFilterStatus);
 }
 $dealPaymentFilterOptions=array();
 foreach($dealPaymentStatuses as $dealPaymentFilterStatus)
 {
    $dealPaymentFilterOptions[$dealPaymentFilterStatus]=__($dealPaymentFilterStatus);
 }
// Paginator options
$this->Paginator->options(array(
  'update' => '#resultDiv',
  'evalScripts' => true,
));
 $reportAgency = isset($reportAgency) ? (array)$reportAgency : array();
 $resolveImage = function($candidates){
    foreach((array)$candidates as $candidate)
    {
        $candidate = trim((string)$candidate);
        if($candidate==='')
        {
            continue;
        }
        $candidate = trim(str_replace('\\','/',$candidate),'/');
        $filePath = WWW_ROOT.'img'.DS.str_replace('/',DS,$candidate);
        if(file_exists($filePath))
        {
            return $candidate;
        }
    }
    return '';
 };
 $headerAgencyName = !empty($reportAgency['name']) ? $reportAgency['name'] : $siteOrganization;
 $headerAgencyAddress = !empty($reportAgency['address']) ? $reportAgency['address'] : $siteAddress;
 $headerAgencyPhone = !empty($reportAgency['phone']) ? $reportAgency['phone'] : '';
 $headerAgencyMobile = !empty($reportAgency['mobile']) ? $reportAgency['mobile'] : '';
 $headerAgencyEmail = !empty($reportAgency['email']) ? $reportAgency['email'] : $siteEmail;
 $headerAgencyLogo = $resolveImage(array(
    !empty($reportAgency['photo']) ? 'agency_thumb/'.$reportAgency['photo'] : '',
    !empty($reportAgency['photo']) ? $reportAgency['photo'] : '',
    !empty($frontLogo) ? $frontLogo : ''
 ));
?>
<div id="resultDiv">
<div class="page-header-content wv-page-header" style="margin-bottom:16px;">
    <div class="page-title wv-page-title-box">
        <h4 class="wv-page-title"><i class="fa fa-handshake-o text-primary"></i> <?php echo __('Booked Properties & Deals Management');?></h4>
        <small class="wv-page-subtitle"><?php echo __('Comprehensive property sales records, installment schedules, client agreements and cash flow tracking');?></small>
    </div>
    <div class="heading-elements wv-page-header-actions">
        <?php $url=$this->Html->url(array('controller'=>'Deals'));echo $this->Html->link('<span class="fa fa-plus"></span>&nbsp;'.__('Add New Deal'),array('controller'=>'Deals','action'=>'add'),array('escape'=>false,'class'=>'btn btn-success'));?>
        <?php echo $this->Html->link('<span class="fa fa-print"></span>&nbsp;'.__('Print'),'#',array('id'=>'printme','escape'=>false,'class'=>'btn btn-default'));?>
        <?php echo $this->Html->link('<span class="fa fa-file-excel-o"></span>&nbsp;'.__('Export CSV'),$exportCsvUrl,array('escape'=>false,'class'=>'btn btn-info'));?>
        <?php echo $this->Html->link('<span class="fa fa-file-pdf-o"></span>&nbsp;'.__('Export PDF'),'#',array('onclick'=>"return exportDealRecordsPdf();",'escape'=>false,'class'=>'btn btn-success'));?>
        <?php if(!empty($canAccessForecast)){ echo $this->Html->link('<span class="fa fa-line-chart"></span>&nbsp;'.__('Cash Flow Forecast'),array('controller'=>'Deals','action'=>'cashflow'),array('escape'=>false,'class'=>'btn btn-primary')); }?>
        <?php echo $this->Html->link('<span class="fa fa-trash"></span>&nbsp;'.__('Delete'),'#',array('name'=>'deleteallfrm','id'=>'deleteallfrm','onclick'=>'check_perform_delete();','escape'=>false,'class'=>'btn btn-danger'));?>
    </div>
</div>
<div class="deals-page-shell">
<div class="panel deals-page-panel"><?php echo $this->Session->flash();?>
    <div class="panel-heading deals-toolbar-card">
        <div class="deals-top-bar">
            <div class="deals-actions-group">
                <?php $url=$this->Html->url(array('controller'=>'Deals'));echo $this->Html->link('<span class="fa fa-plus"></span>&nbsp;'.__('Add New Deal'),array('controller'=>'Deals','action'=>'add'),array('escape'=>false,'class'=>'btn btn-success'));?>
                <?php echo $this->Html->link('<span class="fa fa-trash"></span>&nbsp;'.__('Delete'),'#',array('name'=>'deleteallfrm','id'=>'deleteallfrm','onclick'=>'check_perform_delete();','escape'=>false,'class'=>'btn btn-danger'));?>
                <?php echo $this->Html->link('<span class="fa fa-print"></span>&nbsp;'.__('Print'),'#',array('id'=>'printme','escape'=>false,'class'=>'btn btn-default'));?>
                <?php echo $this->Html->link('<span class="fa fa-file-excel-o"></span>&nbsp;'.__('Export CSV'),$exportCsvUrl,array('escape'=>false,'class'=>'btn btn-info'));?>
                <?php echo $this->Html->link('<span class="fa fa-file-pdf-o"></span>&nbsp;'.__('Export PDF'),'#',array('onclick'=>"return exportDealRecordsPdf();",'escape'=>false,'class'=>'btn btn-success'));?>
                <?php if(!empty($canAccessForecast)){ echo $this->Html->link('<span class="fa fa-line-chart"></span>&nbsp;'.__('Cash Flow Forecast'),array('controller'=>'Deals','action'=>'cashflow'),array('escape'=>false,'class'=>'btn btn-primary')); }?>
            </div>
            <?php if(!empty($canAccessRiskScore)){ ?>
            <div class="deals-kpi-group">
                <a href="<?php echo $this->Html->url(array_merge($baseDealsIndexUrl, array('health_status'=>'healthy'))); ?>" class="health-kpi-pill health-kpi-healthy <?php echo ($selectedHealthStatus==='healthy' ? 'active-kpi' : ''); ?>">
                    <span class="health-kpi-text"><i class="fa fa-check-circle"></i> <?php echo __('Healthy / Up-to-date');?></span>
                    <span class="badge"><?php echo (int)$healthSummary['healthy'];?></span>
                </a>
                <a href="<?php echo $this->Html->url(array_merge($baseDealsIndexUrl, array('health_status'=>'attention'))); ?>" class="health-kpi-pill health-kpi-attention <?php echo ($selectedHealthStatus==='attention' ? 'active-kpi' : ''); ?>">
                    <span class="health-kpi-text"><i class="fa fa-exclamation-circle"></i> <?php echo __('Attention (1-30d)');?></span>
                    <span class="badge"><?php echo (int)$healthSummary['attention'];?></span>
                </a>
                <a href="<?php echo $this->Html->url(array_merge($baseDealsIndexUrl, array('health_status'=>'moderate'))); ?>" class="health-kpi-pill health-kpi-moderate <?php echo ($selectedHealthStatus==='moderate' ? 'active-kpi' : ''); ?>">
                    <span class="health-kpi-text"><i class="fa fa-clock-o"></i> <?php echo __('Moderate (31-60d)');?></span>
                    <span class="badge"><?php echo (int)$healthSummary['moderate'];?></span>
                </a>
                <a href="<?php echo $this->Html->url(array_merge($baseDealsIndexUrl, array('health_status'=>'critical'))); ?>" class="health-kpi-pill health-kpi-critical <?php echo ($selectedHealthStatus==='critical' ? 'active-kpi' : ''); ?>">
                    <span class="health-kpi-text"><i class="fa fa-times-circle"></i> <?php echo __('Critical (>60d)');?></span>
                    <span class="badge"><?php echo (int)$healthSummary['critical'];?></span>
                </a>
            </div>
            <?php } ?>
        </div>
        <?php echo $this->Form->create('Deal',array('name'=>'searchfrm','url'=>$searchFormUrl));?>
        <div class="deals-filter-row">
            <label class="sr-only" for="deal_payment_status"><?php echo __('Filters');?></label>
            <?php if($canUseAgencyFilters){?>
            <div class="deals-filter-col">
                <?php echo $this->Form->select('project_id',$projectName,array('empty'=>__('All Agency'),'label'=>false,'class'=>'form-control deals-filter-select','div'=>false,'value'=>$selectedProjectFilter));?>
            </div>
            <?php }?>
            <div class="deals-filter-col">
                <?php echo $this->Form->select('deal_payment_status',$dealPaymentFilterOptions,array('empty'=>__('All Deal Status'),'label'=>false,'class'=>'form-control deals-filter-select','div'=>false,'value'=>$selectedDealPaymentStatus));?>
            </div>
            <div class="deals-filter-col">
                <?php echo $this->Form->select('deal_control_status',$dealControlFilterOptions,array('empty'=>__('All Deal Control'),'label'=>false,'class'=>'form-control deals-filter-select','div'=>false,'value'=>$selectedDealControlStatus));?>
            </div>
            <?php if(!empty($canAccessRiskScore)){ ?>
            <div class="deals-filter-col">
                <?php
                $healthFilterOptions = array(
                    'healthy' => __('🟢 Healthy (On Track)'),
                    'attention' => __('🟡 Attention (1-30d)'),
                    'moderate' => __('🟠 Moderate (31-60d)'),
                    'critical' => __('🔴 Critical (>60d)')
                );
                echo $this->Form->select('health_status', $healthFilterOptions, array('empty'=>__('All Deal Health'), 'label'=>false, 'class'=>'form-control deals-filter-select', 'div'=>false, 'value'=>$selectedHealthStatus));
                ?>
            </div>
            <?php } ?>
            <div class="deals-filter-col deals-filter-col-date">
                <div class="input-group date" id="start_date">
                    <?php echo $this->Form->input('start_date',array('type'=>'text','label'=>false,'class'=>'form-control','div'=>false,'value'=>$selectedStartDateInput,'placeholder'=>__('Start Date')));?>
                    <span class="input-group-addon"><span class="fa fa-calendar"></span></span>
                </div>
            </div>
            <div class="deals-filter-col deals-filter-col-date">
                <div class="input-group date" id="end_date">
                    <?php echo $this->Form->input('end_date',array('type'=>'text','label'=>false,'class'=>'form-control','div'=>false,'value'=>$selectedEndDateInput,'placeholder'=>__('End Date')));?>
                    <span class="input-group-addon"><span class="fa fa-calendar"></span></span>
                </div>
            </div>
            <div class="deals-filter-actions">
                <button type="submit" class="btn btn-success"><span class="fa fa-search"></span> <?php echo __('Search');?></button>
                <?php echo$this->Form->hidden('isSearch');?>
                <?php echo$this->Html->link('<span class="fa fa-refresh"></span>&nbsp;'.__('Reset'),$resetUrl,array('class'=>'btn btn-warning','escape'=>false));?>
            </div>
        </div>
        <?php echo $this->Form->end();?>
    </div>
        <?php echo $this->element('pagination',array('UseNamedLimit'=>true,'NamedLimitBaseUrl'=>$dealsLimitUrl));
        $pageParams = $this->Paginator->params();
        $limit = $pageParams['limit'];
        $page = $pageParams['page'];
        $serialNo = 1*$limit*($page-1);?>
<?php echo $this->Form->create(array('name'=>'deleteallfrm','action' => 'deleteall'));?>
<?php echo $this->Session->flash();?>
<div id="printable_content">
    <?php echo $this->Html->css('print');?>
    <div class="panel-body deals-panel-body deals-print-card">
        <table width="100%" class="table" style="border-top:0;">
            <tr class="deals-report-brand-header">
                <td class="deals-report-brand-logo-cell">
                    <?php if(strlen($headerAgencyLogo)>0){ echo $this->Html->image($headerAgencyLogo,array('alt'=>$headerAgencyName)); }?>
                </td>
                <td class="deals-report-brand-details">
                    <h2><?php echo h($headerAgencyName);?></h2>
                    <p class="deals-report-brand-line"><?php echo h($headerAgencyAddress);?></p>
                    <p class="deals-report-brand-line">
                        <?php
                        $headerContactParts = array();
                        if(strlen(trim($headerAgencyPhone))>0)$headerContactParts[] = __('Phone').': '.h($headerAgencyPhone);
                        if(strlen(trim($headerAgencyMobile))>0)$headerContactParts[] = __('Mobile').': '.h($headerAgencyMobile);
                        if(strlen(trim($headerAgencyEmail))>0)$headerContactParts[] = __('Email').': '.h($headerAgencyEmail);
                        echo implode(' | ',$headerContactParts);
                        ?>
                    </p>
                </td>
            </tr>
        </table>
        <div class="deals-report-title">
            <h3><?php echo __('Booked Properties Report');?></h3>
        </div>
        <div class="table-responsive deals-table-wrap">
            <table class="table table-striped table-bordered deals-data-table">
                <thead>
                    <tr>
                        <th class="pbutton"><?php echo $this->Form->checkbox('checkbox', array('value'=>'deleteall','name'=>'selectAll','label'=>false,'id'=>'selectAll','hiddenField'=>false));?></th>
                        <th><?php echo $this->Paginator->sort('id', __('#'), array('direction' => 'desc'));?></th>
                        <th><?php echo $this->Paginator->sort('Client.name', __('Customer Name'), array('direction' => 'asc'));?></th>
                        <th><?php echo $this->Paginator->sort('Client.fathername', __('Father / Guardian'), array('direction' => 'asc'));?></th>
                        <th><?php echo $this->Paginator->sort('Client.phon', __('Mobile No'), array('direction' => 'asc'));?></th>
                        <th><?php echo $this->Paginator->sort('Property.name', __('Property / File'), array('direction' => 'asc'));?></th>
                        <th><?php echo $this->Paginator->sort('invoice_no', __('Membership No'), array('direction' => 'asc'));?></th>
                        <th><?php echo $this->Paginator->sort('date', __('Booking Date'), array('direction' => 'asc'));?></th>
                        <th><?php echo $this->Paginator->sort('Property.area', __('Area'), array('direction' => 'asc'));?></th>
                        <th><?php echo $this->Paginator->sort('net_total_amount', __('Total Amount'), array('direction' => 'asc'));?></th>
                        <th><?php echo __('Paid');?></th>
                        <th><?php echo __('Balance Due');?></th>
                        <?php if(!empty($canAccessRiskScore)){?>
                        <th class="deal-health-col" style="text-align:center;"><?php echo __('Health');?></th>
                        <?php }?>
                        <th class="deal-status-col"><?php echo __('Status');?></th>
                        <th class="deal-control-col"><?php echo __('Deal Control');?></th>
                        <?php if($canShowDealAction){?>
                        <th class="pbutton actions-col"><?php echo __('Action');?></th>
                        <?php }?>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $totalInvoice = 0;
                    $totalPaidBeforeBalance = 0;
                    $totalBalance = 0;
                    $totalDeals = !empty($Deal) ? count($Deal) : 0;
                    $dealIndex = 0;
                    foreach ($Deal as $post):
                    $dealIndex++;
                    $id=$post['Deal']['id'];
                    $storedTotalAmount = isset($post['Deal']['total_amount']) ? (float)$post['Deal']['total_amount'] : 0;
                    $dealBaseAmount = isset($post['Deal']['amount']) ? (float)$post['Deal']['amount'] : 0;
                    $dealDiscountAmount = isset($post['Deal']['discount']) ? (float)$post['Deal']['discount'] : 0;
                    $dealChargeAmount = isset($dealChargeTotalsMap[$id]) ? (float)$dealChargeTotalsMap[$id] : 0;
                    if($storedTotalAmount>0)
                    {
                        $totalAmount = round($storedTotalAmount,2);
                    }
                    else
                    {
                        if($dealBaseAmount<=0 && $storedTotalAmount>0)
                        {
                            $dealBaseAmount = max(0, $storedTotalAmount - $dealChargeAmount);
                        }
                        $propertyAfterDiscount = round($dealBaseAmount - max(0,$dealDiscountAmount),2);
                        if($propertyAfterDiscount<0)
                        {
                            $propertyAfterDiscount = 0;
                        }
                        $totalAmount = round($propertyAfterDiscount + max(0,$dealChargeAmount),2);
                    }
                    $paidBeforeBalance=isset($post['Deal']['payment']) ? (float)$post['Deal']['payment'] : 0;
                    $balanceDue=$totalAmount-$paidBeforeBalance;
                    $totalInvoice=$totalInvoice+$totalAmount;$totalPaidBeforeBalance=$totalPaidBeforeBalance+$paidBeforeBalance;$totalBalance=$totalBalance+$balanceDue;
                    if($balanceDue<=0)
                    $status="Paid";
                    else
                    $status="Partial";
                    $dealControlStatus = (!empty($post['Deal']['deal_status']) ? $post['Deal']['deal_status'] : 'Active');
                    $dealControlReason = (!empty($post['Deal']['deal_status_reason']) ? trim($post['Deal']['deal_status_reason']) : '');
                    $dealControlReasonId = 'deal-control-reason-'.$id;
                    $isPaymentBlocked = in_array($dealControlStatus,$dealPaymentBlockedStatuses);
                    $dealControlColor = '#28a745';
                    if($dealControlStatus==='Blocked')$dealControlColor='#0d6efd';
                    elseif($dealControlStatus==='Transferd' || $dealControlStatus==='Transferred')$dealControlColor='#e83e8c';
                    elseif($dealControlStatus==='Permanent Blocked' || $dealControlStatus==='Pemanent Blocked')$dealControlColor='#9c1b64';
                    elseif($dealControlStatus==='Refund')$dealControlColor='#7b1e57';
                    elseif($dealControlStatus==='Refund-Running')$dealControlColor='#5f2c80';
                    ?>
                    <tr>
                        <td class="pbutton"><?php echo $this->Form->checkbox(false,array('value' => $post['Deal']['id'],'name'=>'data[Deal][id][]','id'=>"DeleteCheckbox$id",'class'=>'chkselect'));?></td>
                        <td><?php echo $serialNo++;?></td>
                        <td><?php echo $this->Html->link(h($post['Client']['name']),array('controller'=>'deals_payments','action'=>'index',$id),array('escape'=>false,'title'=>__('Show Payment')));?></td>
                        <td>
                            <?php
                            $fatherName = !empty($post['Client']['fathername']) ? trim((string)$post['Client']['fathername']) : '';
                            $relationType = !empty($post['Client']['relation_type']) ? trim((string)$post['Client']['relation_type']) : '';
                            if($fatherName !== ''){
                                echo '<strong style="color:#1e293b;">' . h($fatherName) . '</strong>';
                                if($relationType !== ''){
                                    echo '<small class="text-muted" style="display:block;font-size:11px;font-weight:600;">' . h($relationType) . '</small>';
                                }
                            } else {
                                echo '<span class="text-muted">-</span>';
                            }
                            ?>
                        </td>
                        <td>
                            <?php
                            $dealMobileDisplay = trim((string)$post['Client']['mobile']) !== '' ? $post['Client']['mobile'] : (isset($post['Client']['phone']) ? $post['Client']['phone'] : '');
                            $dealCallNo = preg_replace('/[^0-9\+]/', '', (string)$dealMobileDisplay);
                            if(trim((string)$dealMobileDisplay) === '')
                            {
                                echo '-';
                            }
                            elseif(trim((string)$dealCallNo) !== '')
                            {
                                echo $this->Html->link(h($dealMobileDisplay), 'tel:'.$dealCallNo, array('escape' => false));
                            }
                            else
                            {
                                echo h($dealMobileDisplay);
                            }
                            ?>
                        </td>
                        <td><?php echo h($post['Property']['name']);?></td>
                        <td><?php echo h($post['Deal']['invoice_no']);?></td>
                        <td><?php echo $this->Time->format($sysDay.$dateSep.$sysMonth.$dateSep.$sysYear,h($post['Deal']['date']));?></td>
                        <td><?php echo $post['Property']['area'].' '.h($post['Unit']['name']);?></td>
                        <td><?php echo $currency.$this->Number->format($totalAmount);?></td>
                        <td><?php echo $currency.$this->Number->format($paidBeforeBalance);?></td>
                        <td><?php echo $currency.$this->Number->format(h($balanceDue));?></td>
                        <?php if(!empty($canAccessRiskScore)){
                            $dealHealth = isset($dealHealthMap[$id]) ? $dealHealthMap[$id] : array('status'=>'healthy','overdue_days'=>0,'overdue_amount'=>0);
                            $healthStatus = $dealHealth['status'];
                            $overdueDays = (int)$dealHealth['overdue_days'];
                            $overdueAmt = (float)$dealHealth['overdue_amount'];
                        ?>
                        <td class="deal-health-col" style="text-align:center;">
                            <?php if($healthStatus === 'healthy'){ ?>
                                <span class="label label-success" title="<?php echo __('Up-to-date on all installments'); ?>" style="display:inline-block;padding:5px 8px;font-size:11px;"><i class="fa fa-check-circle"></i> <?php echo __('Healthy');?></span>
                            <?php } elseif($healthStatus === 'attention'){ ?>
                                <span class="label label-warning" title="<?php echo __('Overdue %d days (Rs. %s)', $overdueDays, number_format($overdueAmt)); ?>" style="display:inline-block;padding:5px 8px;font-size:11px;"><i class="fa fa-exclamation-circle"></i> <?php echo __('Attention (%sd)', $overdueDays);?></span>
                            <?php } elseif($healthStatus === 'moderate'){ ?>
                                <span class="label" style="background:#f97316;color:#fff;display:inline-block;padding:5px 8px;font-size:11px;" title="<?php echo __('Overdue %d days (Rs. %s)', $overdueDays, number_format($overdueAmt)); ?>"><i class="fa fa-clock-o"></i> <?php echo __('Moderate (%sd)', $overdueDays);?></span>
                            <?php } else { ?>
                                <span class="label label-danger" title="<?php echo __('Critical: Overdue %d days (Rs. %s)', $overdueDays, number_format($overdueAmt)); ?>" style="display:inline-block;padding:5px 8px;font-size:11px;"><i class="fa fa-times-circle"></i> <?php echo __('Critical (%sd)', $overdueDays);?></span>
                            <?php } ?>
                        </td>
                        <?php } ?>
                        <td class="deal-status-col"><span class="label label-<?php if($status=="Paid")echo"success";else echo"danger";?>"><?php echo __($status);?></span></td>
                        <td class="deal-control-cell">
                            <div class="deal-control-flex-wrap">
                                <?php if($canChangeDealControlStatus){?>
                                <div class="btn-group deal-control-btn-group">
                                    <button type="button" class="btn btn-xs dropdown-toggle deal-control-status-btn" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="background-color:<?php echo $dealControlColor;?>;" title="<?php echo __('Current Deal Control: ').h($dealControlStatus).'. '.__('Click to change status');?><?php echo strlen($dealControlReason)>0 ? ' | '.__('Reason: ').h($dealControlReason) : '';?>">
                                        <span class="deal-control-badge-dot"></span>
                                        <span class="deal-control-badge-text"><?php echo h($dealControlStatus);?></span>
                                        <?php if(strlen($dealControlReason)>0){?>
                                        <i class="fa fa-info-circle deal-control-reason-icon" title="<?php echo __('Has reason: ').h($dealControlReason);?>"></i>
                                        <?php }?>
                                        <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu deal-control-dropdown-menu" role="menu">
                                        <li class="dropdown-header"><i class="fa fa-sliders"></i> <?php echo __('Change Deal Control');?></li>
                                        <?php if(strlen($dealControlReason)>0){?>
                                        <li class="deal-control-menu-reason">
                                            <div class="deal-control-menu-reason-box">
                                                <strong><i class="fa fa-info-circle text-primary"></i> <?php echo __('Current Reason');?>:</strong>
                                                <div class="deal-control-reason-text"><?php echo nl2br(h($dealControlReason));?></div>
                                            </div>
                                        </li>
                                        <li class="divider"></li>
                                        <?php }?>
                                        <?php foreach($dealControlStatuses as $controlStatus){
                                            $changeUrl = $this->Html->url(array('controller'=>'Deals','action'=>'changestatus',$id,$controlStatus));
                                            $requireReason = in_array($controlStatus,$dealPaymentBlockedStatuses) ? 'true' : 'false';
                                            $isCurrent = ($dealControlStatus === $controlStatus);
                                            $statusIcon = 'fa-circle-o';
                                            $statusColor = '#28a745';
                                            if($controlStatus==='Active') { $statusIcon = 'fa-check-circle'; $statusColor = '#10b981'; }
                                            elseif($controlStatus==='Blocked') { $statusIcon = 'fa-lock'; $statusColor = '#2563eb'; }
                                            elseif($controlStatus==='Transferd' || $controlStatus==='Transferred') { $statusIcon = 'fa-exchange'; $statusColor = '#e83e8c'; }
                                            elseif($controlStatus==='Permanent Blocked' || $controlStatus==='Pemanent Blocked') { $statusIcon = 'fa-ban'; $statusColor = '#9c1b64'; }
                                            elseif($controlStatus==='Refund') { $statusIcon = 'fa-reply'; $statusColor = '#dc2626'; }
                                            elseif($controlStatus==='Refund-Running') { $statusIcon = 'fa-hourglass-half'; $statusColor = '#f59e0b'; }
                                        ?>
                                            <li class="<?php echo $isCurrent ? 'active-status' : '';?>">
                                                <?php 
                                                $statusLinkHtml = '<i class="fa '.$statusIcon.'" style="color:'.$statusColor.'; font-size:12px; width:16px;"></i> ' . h($controlStatus);
                                                if ($isCurrent) {
                                                    $statusLinkHtml .= ' <span class="label label-default" style="font-size:9px; padding:1px 5px; margin-left:auto;">' . __('Current') . '</span>';
                                                }
                                                echo $this->Html->link($statusLinkHtml, '#', array('escape'=>false, 'onclick'=>"return updateDealControlStatus('".$changeUrl."','".addslashes($controlStatus)."',".$requireReason.");"));
                                                ?>
                                            </li>
                                        <?php }?>
                                    </ul>
                                </div>
                                <?php }else{?>
                                <span class="deal-control-status-btn deal-control-status-static" style="background-color:<?php echo $dealControlColor;?>; cursor:default;" title="<?php echo h($dealControlStatus);?><?php echo strlen($dealControlReason)>0 ? ' | '.__('Reason: ').h($dealControlReason) : '';?>">
                                    <span class="deal-control-badge-dot"></span>
                                    <span class="deal-control-badge-text"><?php echo h($dealControlStatus);?></span>
                                    <?php if(strlen($dealControlReason)>0){?>
                                    <i class="fa fa-info-circle deal-control-reason-icon" title="<?php echo __('Reason: ').h($dealControlReason);?>"></i>
                                    <?php }?>
                                </span>
                                <?php }?>
                            </div>
                        </td>
                        <?php if($canShowDealAction){?>
                        <td class="pbutton deal-action-cell actions-col">
                            <div class="btn-group">
                                <button type="button" class="btn btn-xs btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false" title="<?php echo h(__('Actions'));?>">
                                  <i class="fa fa-cog"></i> <?php echo __('Action');?> <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-right" role="menu">
                                <li><?php echo $this->Html->link('<span class="fa fa-briefcase"></span>&nbsp;'.__('Show Payment Plan'),array('controller'=>'plans_payments','action'=>'index',$id),array('escape'=>false));?></li>
                                <?php if(!$isPaymentBlocked){?>
                                <li><?php echo $this->Html->link('<span class="fa fa-dollar"></span>&nbsp;'.__('Make Payment'),array('controller'=>'deals_payments','action'=>'add',$id),array('escape'=>false));?></li>
                                <?php }else{?>
                                <li><a href="javascript:void(0);" style="color:#999;"><span class="fa fa-lock"></span>&nbsp;<?php echo __('Make Payment (Blocked)');?></a></li>
                                <?php }?>
                                <li><?php echo $this->Html->link('<span class="fa fa-briefcase"></span>&nbsp;'.__('Show Payment'),array('controller'=>'deals_payments','action'=>'index',$id),array('escape'=>false));?></li>
                                <li><?php echo $this->Html->link('<span class="fa fa-print"></span>&nbsp;'.__('Print Invoice'),array('controller'=>'Deals','action'=>'printinvoice',$id),array('target'=>'_blank','escape'=>false));?></li>
                                <li><?php echo $this->Html->link('<span class="fa fa-print"></span>&nbsp;'.__('Print Alotment Letter'),array('controller'=>'Deals','action'=>'allotment',$id),array('escape'=>false));?></li>
                                <li><?php echo $this->Html->link('<span class="fa fa-dashboard"></span>&nbsp;'.__('Letters Dashboard'),array('controller'=>'Realestateletters','action'=>'dashboard'),array('escape'=>false));?></li>
                                <li><?php echo $this->Html->link('<span class="fa fa-random"></span>&nbsp;'.__('Balloting Dashboard'),array('controller'=>'Balloting','action'=>'dashboard'),array('escape'=>false));?></li>
                                <li class="divider"></li>
                                <li><?php echo $this->Html->link('<span class="fa fa-folder-open"></span>&nbsp;'.__('Deal Letters History'),array('controller'=>'Realestateletters','action'=>'index',$id),array('escape'=>false));?></li>
                                <li><?php echo $this->Html->link('<span class="fa fa-arrows-alt"></span>&nbsp;'.__('View'),'#',array('onclick'=>"show_modal('$url/View/$id');",'escape'=>false));?></li>
                                <li><?php echo $this->Html->link('<span class="fa fa-edit"></span>&nbsp;'.__('Edit'),'#',array('name'=>'editallfrm','onclick'=>"check_perform_sedit('$url','$id');",'escape'=>false));?></li>
                                <li><?php echo $this->Html->Link('<span class="fa fa-trash"></span>&nbsp;'.__('Delete'),'#',array('onclick'=>"check_perform_sdelete('$id');",'escape'=>false));?></li>
                                </ul>
                            </div>
                        </td>
                        <?php }?>
                    </tr>
                    <?php endforeach;?>
                    <?php unset($post);?>
                    <tr class="deals-total-row">
                        <td class="pbutton"></td>
                        <td class="pbutton"></td>
                        <td colspan="7" align="center"><h4 style="margin:0;"><?php echo __('Total');?></h4></td>
                        <td><?php echo $currency.$this->Number->format($totalInvoice);?></td>
                        <td><?php echo $currency.$this->Number->format($totalPaidBeforeBalance);?></td>
                        <td><?php echo $currency.$this->Number->format($totalBalance);?></td>
                        <?php if(!empty($canAccessRiskScore)){?>
                        <td></td>
                        <?php }?>
                        <td></td>
                        <td></td>
                        <?php if($canShowDealAction){?>
                        <td class="pbutton actions-col"></td>
                        <?php }?>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="deals-report-datetime">
            <strong><?php echo __('Dated / Time');?>:</strong> <?php echo date('d-m-y h:i:s');?>
        </div>
    </div>
</div>
<?php echo $this->Form->end();?>
<?php echo $this->element('pagination',array('UseNamedLimit'=>true,'NamedLimitBaseUrl'=>$dealsLimitUrl));?>

<!-- Modal: Change Deal Control Status & Reason -->
<div class="modal fade" id="dealControlReasonModal" tabindex="-1" role="dialog" aria-labelledby="dealControlReasonModalTitle" aria-hidden="true" style="z-index: 10500;">
    <div class="modal-dialog" role="document" style="max-width: 520px; margin: 60px auto;">
        <div class="modal-content" style="border-radius: 12px; border: 1px solid #cbd5e1; box-shadow: 0 20px 45px -5px rgba(15, 23, 42, 0.35); overflow: hidden;">
            <div class="modal-header" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #ffffff; padding: 18px 24px; border-bottom: 1px solid #334155; display: flex; align-items: center; justify-content: space-between;">
                <h4 class="modal-title" id="dealControlReasonModalTitle" style="font-size: 16px; font-weight: 700; color: #ffffff; margin: 0; display: flex; align-items: center; gap: 9px;">
                    <i class="fa fa-sliders" style="color: #38bdf8;"></i> <?php echo __('Change Deal Status');?>
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: #94a3b8; opacity: 1; font-size: 22px; font-weight: 400; text-shadow: none; margin-top: -2px; transition: color 0.15s ease;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="padding: 24px; background: #ffffff;">
                <!-- Status Preview Card -->
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 16px; margin-bottom: 20px; display: flex; align-items: center; gap: 14px;">
                    <div id="modalStatusIconBox" style="width: 44px; height: 44px; border-radius: 50%; background: #28a745; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0; box-shadow: 0 4px 10px rgba(0,0,0,0.15);">
                        <i id="modalStatusIcon" class="fa fa-shield"></i>
                    </div>
                    <div>
                        <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 3px;">
                            <?php echo __('Target Deal Status');?>
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span id="modalTargetStatusBadge" style="display: inline-block; padding: 4px 14px; border-radius: 999px; background: #28a745; color: #ffffff; font-size: 12px; font-weight: 800; letter-spacing: 0.35px; text-transform: uppercase; box-shadow: 0 2px 6px rgba(0,0,0,0.15);">
                                Active
                            </span>
                            <span style="font-size: 12.5px; color: #475569; font-weight: 500;">
                                <?php echo __('Will be updated immediately.');?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Reason Input Group -->
                <div id="modalReasonGroup" class="form-group" style="margin-bottom: 0;">
                    <label for="dealControlReasonInput" style="font-size: 13px; font-weight: 700; color: #1e293b; margin-bottom: 6px; display: block;">
                        <?php echo __('Reason / Remarks');?> <span id="modalReasonRequiredMark" class="text-danger" style="display:none;">*</span>
                        <span id="modalReasonHint" style="font-size: 11.5px; font-weight: 500; color: #64748b; float: right; margin-top: 2px;"></span>
                    </label>
                    <textarea id="dealControlReasonInput" class="form-control" rows="3" placeholder="<?php echo __('Enter administrative reason note (e.g. client cancellation, payment dispute, bounced cheque, bank loan issue)...');?>" style="border-radius: 8px; border: 1.5px solid #cbd5e1; font-size: 13px; color: #0f172a; resize: vertical; padding: 10px 12px; min-height: 85px; box-shadow: inset 0 1px 2px rgba(0,0,0,0.04); transition: border-color 0.15s ease, box-shadow 0.15s ease;"></textarea>
                    <div id="modalReasonError" style="display: none; margin-top: 8px; font-size: 12px; font-weight: 600; color: #dc2626; align-items: center; gap: 6px;">
                        <i class="fa fa-exclamation-triangle"></i> <?php echo __('A reason note is strictly required before changing this status.');?>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 14px 24px; display: flex; align-items: center; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-default" data-dismiss="modal" style="border-radius: 7px; font-weight: 600; font-size: 13px; padding: 8px 18px; color: #475569; background: #ffffff; border: 1px solid #cbd5e1;">
                    <i class="fa fa-times"></i> <?php echo __('Cancel');?>
                </button>
                <button type="button" id="btnConfirmDealControlChange" class="btn btn-primary" style="border-radius: 7px; font-weight: 700; font-size: 13px; padding: 8px 20px; background-color: #2563eb; border-color: #2563eb; color: #ffffff; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);">
                    <i class="fa fa-check"></i> <span id="modalConfirmBtnText"><?php echo __('Confirm & Update Status');?></span>
                </button>
            </div>
        </div>
    </div>
</div>
</div>
</div>
</div>
