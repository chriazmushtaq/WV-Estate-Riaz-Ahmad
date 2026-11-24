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
function updateDealControlStatus(changeUrl,statusLabel,needReason)
{
    var reason = '';
    if(needReason)
    {
        reason = prompt('Please enter reason for "'+statusLabel+'" status:','');
        if(reason===null)
        {
            return false;
        }
        reason = jQuery.trim(reason);
        if(reason.length===0)
        {
            alert('Reason is required for this status.');
            return false;
        }
        changeUrl += (changeUrl.indexOf('?')===-1 ? '?' : '&') + 'reason=' + encodeURIComponent(reason);
    }
    if(!confirm('Change deal status to "'+statusLabel+'"?'))
    {
        return false;
    }
    window.location.href = changeUrl;
    return false;
}
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
    min-width: 0;
    max-width: none;
    width: 1%;
    align-items: center;
    gap: 6px;
    white-space: nowrap;
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
.deals-data-table th:not(.pbutton):not(.deal-status-col):not(.deal-control-col),
.deals-data-table td:not(.pbutton):not(.deal-status-col):not(.deal-control-cell) {
    white-space: nowrap;
}
.deals-data-table .pbutton,
.deals-data-table .deal-status-col,
.deals-data-table .deal-control-col,
.deals-data-table .deal-control-cell {
    width: auto;
    white-space: nowrap;
}
.deals-data-table .deal-control-col {
    min-width: 0;
    width: 1%;
    text-align: center;
}
.deals-data-table .deal-status-col {
    min-width: 0;
    text-align: center;
}
.deals-data-table .pbutton,
.deals-data-table .deal-action-cell {
    width: 1%;
    min-width: 0;
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
.deal-control-cell .btn-group {
    display: inline-block;
    margin-left: 6px;
    margin-top: 0 !important;
    vertical-align: middle;
}
.deals-data-table > tbody > tr > td.deal-control-cell {
    display: flex;
}
.deal-control-cell .btn {
    border-radius: 6px;
    line-height: 1;
    min-width: 26px;
    padding: 4px 8px;
}
.deal-action-cell .dropdown-menu,
.deal-control-cell .dropdown-menu {
    border-color: #d8e2ee;
    border-radius: 8px;
    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.12);
}
.deal-action-cell .dropdown-menu > li > a,
.deal-control-cell .dropdown-menu > li > a {
    color: #334155;
    font-weight: 600;
    padding: 8px 14px;
}
.deal-action-cell .dropdown-menu > li > a:hover,
.deal-control-cell .dropdown-menu > li > a:hover {
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
                        <th class="pbutton"><?php echo __('Action');?></th>
                        <?php }?>
                    </tr>
                </thead>
                <tbody>
                    <?php $totalInvoice=0;$totalPaidBeforeBalance=0;$totalBalance=0;
                    foreach ($Deal as $post):
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
                            <?php if(strlen($dealControlReason)>0){?>
                            <a class="label deal-control-toggle deal-control-status-badge" style="background-color:<?php echo $dealControlColor;?>;color:#fff;" data-toggle="collapse" href="#<?php echo $dealControlReasonId;?>" aria-expanded="false" aria-controls="<?php echo $dealControlReasonId;?>" title="<?php echo __('Click to view reason');?>"><?php echo h($dealControlStatus);?></a>
                            <div id="<?php echo $dealControlReasonId;?>" class="collapse deal-control-reason-full"><strong><?php echo __('Reason');?>:</strong> <?php echo nl2br(h($dealControlReason));?></div>
                            <?php }else{?>
                            <span class="label deal-control-status-badge" style="background-color:<?php echo $dealControlColor;?>;color:#fff;"><?php echo h($dealControlStatus);?></span>
                            <?php }?>
                            <?php if($canChangeDealControlStatus){?>
                            <div class="btn-group pbutton" style="margin-top:4px;">
                                <button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown" aria-expanded="false" title="<?php echo __('Change Deal Control');?>" aria-label="<?php echo __('Change Deal Control');?>">
                                  +
                                </button>
                                <ul class="dropdown-menu" role="menu">
                                <?php foreach($dealControlStatuses as $controlStatus){?>
                                    <?php
                                    $changeUrl = $this->Html->url(array('controller'=>'Deals','action'=>'changestatus',$id,$controlStatus));
                                    $requireReason = in_array($controlStatus,$dealPaymentBlockedStatuses) ? 'true' : 'false';
                                    ?>
                                    <li><?php echo $this->Html->link(h($controlStatus),'#',array('escape'=>false,'onclick'=>"return updateDealControlStatus('".$changeUrl."','".addslashes($controlStatus)."',".$requireReason.");"));?></li>
                                <?php }?>
                                </ul>
                            </div>
                            <?php }?>
                        </td>
                        <?php if($canShowDealAction){?>
                        <td class="pbutton deal-action-cell">
                            <div class="btn-group">
                                <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                  <?php echo __('Action');?> <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu" role="menu">
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
                        <td class="pbutton"></td>
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
</div>
</div>
</div>
