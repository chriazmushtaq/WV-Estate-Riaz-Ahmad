<?php
$selectedScope = isset($selectedScope) ? strtolower(trim((string)$selectedScope)) : 'all';
if($selectedScope === '')
{
    $selectedScope = 'all';
}
$scopeLabel = isset($scopeLabel) ? (string)$scopeLabel : __('All Leads');
$upcomingCount = isset($upcomingCount) ? (int)$upcomingCount : count((array)$upcomingLeads);
$selectedFollowupPeriod = isset($selectedFollowupPeriod) ? (string)$selectedFollowupPeriod : 'today';
$followupPeriodLabel = isset($followupPeriodLabel) ? (string)$followupPeriodLabel : __('Today');
$followupStartInput = isset($followupStartInput) ? (string)$followupStartInput : '';
$followupEndInput = isset($followupEndInput) ? (string)$followupEndInput : '';
$followupTableCount = isset($followupTableCount) ? (int)$followupTableCount : count((array)$upcomingLeads);
$followupCurrentPage = isset($followupCurrentPage) ? (int)$followupCurrentPage : 1;
$followupTotalPages = isset($followupTotalPages) ? (int)$followupTotalPages : 1;
$wonCount = isset($wonCount) ? (int)$wonCount : 0;
$lostCount = isset($lostCount) ? (int)$lostCount : 0;
$inProgressCount = isset($inProgressCount) ? (int)$inProgressCount : 0;
$winRate = isset($winRate) ? (float)$winRate : 0;
$lostRate = isset($lostRate) ? (float)$lostRate : 0;
$lostReasonsMap = isset($lostReasonsMap) ? (array)$lostReasonsMap : array();
$dashboardUrl = array('controller' => 'Leads', 'action' => 'dashboard');
$dashboardBaseQuery = isset($dashboardBaseQuery) ? (array)$dashboardBaseQuery : array();
$selectedAgentName = isset($selectedAgentName) ? (string)$selectedAgentName : __('All Agents');
$reportAgency = isset($reportAgency) ? (array)$reportAgency : array();
$headerAgencyName = !empty($reportAgency['name']) ? (string)$reportAgency['name'] : (isset($siteOrganization) ? (string)$siteOrganization : '');
$headerAgencyAddress = !empty($reportAgency['address']) ? (string)$reportAgency['address'] : (isset($siteAddress) ? (string)$siteAddress : '');
$headerAgencyPhone = !empty($reportAgency['phone']) ? (string)$reportAgency['phone'] : '';
$headerAgencyMobile = !empty($reportAgency['mobile']) ? (string)$reportAgency['mobile'] : '';
$headerAgencyEmail = !empty($reportAgency['email']) ? (string)$reportAgency['email'] : (isset($siteEmail) ? (string)$siteEmail : '');
$resolveImage = function($candidates){
    foreach((array)$candidates as $candidate)
    {
        $candidate = trim((string)$candidate);
        if($candidate === '')
        {
            continue;
        }
        $candidate = trim(str_replace('\\', '/', $candidate), '/');
        $filePath = WWW_ROOT.'img'.DS.str_replace('/', DS, $candidate);
        if(file_exists($filePath))
        {
            return $candidate;
        }
    }
    return '';
};
$headerAgencyLogo = $resolveImage(array(
    !empty($reportAgency['photo']) ? 'agency_thumb/'.$reportAgency['photo'] : '',
    !empty($reportAgency['photo']) ? $reportAgency['photo'] : '',
    !empty($frontLogo) ? $frontLogo : ''
));
$headerAgencyLogoUrl = $headerAgencyLogo !== '' ? $this->Html->url('/img/'.$headerAgencyLogo, true) : '';
$buildScopeQuery = function($scopeName) use($dashboardBaseQuery, $selectedFollowupPeriod, $followupStartInput, $followupEndInput){
    $query = array_merge((array)$dashboardBaseQuery, array(
        'scope' => (string)$scopeName,
        'followup_period' => (string)$selectedFollowupPeriod
    ));
    if($selectedFollowupPeriod === 'custom')
    {
        if(trim((string)$followupStartInput) !== '')$query['followup_start'] = (string)$followupStartInput;
        if(trim((string)$followupEndInput) !== '')$query['followup_end'] = (string)$followupEndInput;
    }
    return $query;
};
$buildFollowupQuery = function($periodName, $pageNo = 1) use($dashboardBaseQuery, $selectedScope, $followupStartInput, $followupEndInput){
    $query = array_merge((array)$dashboardBaseQuery, array(
        'scope' => (string)$selectedScope,
        'followup_period' => (string)$periodName,
        'followup_page' => max(1, (int)$pageNo)
    ));
    if((string)$periodName === 'custom')
    {
        if(trim((string)$followupStartInput) !== '')$query['followup_start'] = (string)$followupStartInput;
        if(trim((string)$followupEndInput) !== '')$query['followup_end'] = (string)$followupEndInput;
    }
    return $query;
};
$formatPercent = function($count, $total){
    $total = (int)$total;
    if($total <= 0)
    {
        return '0%';
    }
    return number_format(((int)$count * 100) / $total, 1).'%';
};
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
$formatWhatsAppUrl = function($phone, $name = '') {
    $raw = trim((string)$phone);
    if($raw === '') return '';
    $digits = preg_replace('/[^0-9]/', '', $raw);
    if($digits === '') return '';
    if(substr($digits, 0, 2) === '00') $digits = substr($digits, 2);
    if(substr($digits, 0, 1) === '0') $digits = '92' . substr($digits, 1);
    if(strlen($digits) < 10) return '';
    $msg = rawurlencode("Hi " . trim((string)$name) . ", reaching out regarding your inquiry.");
    return 'https://api.whatsapp.com/send?phone=' . $digits . '&text=' . $msg;
};
$renderTempBadge = function($temp) {
    $t = strtolower(trim((string)$temp));
    if($t === 'hot') {
        return '<span class="label label-danger" style="background:#ef4444;margin-right:4px;"><i class="fa fa-fire"></i> Hot</span>';
    } elseif($t === 'cold') {
        return '<span class="label label-default" style="background:#94a3b8;margin-right:4px;"><i class="fa fa-snowflake-o"></i> Cold</span>';
    } else {
        return '<span class="label label-warning" style="background:#f59e0b;margin-right:4px;"><i class="fa fa-sun-o"></i> Warm</span>';
    }
};
$crmAutomationQuery = array();
if(!empty($dashboardBaseQuery['project_id']))
{
    $crmAutomationQuery['project_id'] = (int)$dashboardBaseQuery['project_id'];
}
if(!empty($dashboardBaseQuery['user_id']))
{
    $crmAutomationQuery['user_id'] = (int)$dashboardBaseQuery['user_id'];
}
$followUpQueueUrl = array('controller' => 'Crmautomations', 'action' => 'queue', '?' => array_merge($crmAutomationQuery, array('queue_type' => 'overdue')));
$leadReactivationUrl = array('controller' => 'Crmautomations', 'action' => 'reactivation', '?' => $crmAutomationQuery);
$sourceRoiUrl = array('controller' => 'Crmautomations', 'action' => 'sourceroi', '?' => $crmAutomationQuery);
?>
<style type="text/css">
/* Fix Admin Layout Offset: Prevent Body Content from Sliding Under Left Sidebar */
.content-wrapper,
.page-content > .content-wrapper,
.page-container .main-content,
.dashboard-content {
    margin-left: 280px !important;
    width: calc(100% - 280px) !important;
    position: relative !important;
    box-sizing: border-box !important;
    float: none !important;
}

@media (max-width: 991px) {
    .content-wrapper,
    .page-content > .content-wrapper,
    .page-container .main-content,
    .dashboard-content {
        margin-left: 0 !important;
        width: 100% !important;
    }
}

.crm-dashboard-layout {
    margin-top: 6px;
}
.crm-dashboard-shell {
    background: #f5f7fb;
    border: 1px solid #dce3ed;
    padding: 14px;
}
.crm-card-link {
    display: block;
    color: inherit;
    text-decoration: none !important;
}
.crm-kpi-grid,
.crm-status-grid {
    margin-left: -6px;
    margin-right: -6px;
}
.crm-kpi-grid > div,
.crm-status-grid > div {
    padding-left: 6px;
    padding-right: 6px;
}
.crm-kpi-card,
.crm-status-card {
    border: 1px solid #d8e0ea;
    background: #ffffff;
    border-radius: 8px;
    padding: 12px;
    margin-bottom: 12px;
    box-shadow: 0 1px 4px rgba(15, 23, 42, 0.06);
    transition: all 0.2s ease-in-out;
    min-height: 96px;
}
.crm-kpi-card:hover,
.crm-status-card:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 14px rgba(32, 44, 68, 0.12);
}
.crm-kpi-card.active,
.crm-status-card.active {
    border-color: #1f78d1;
    box-shadow: 0 0 0 2px rgba(31, 120, 209, 0.16);
}
.crm-kpi-icon {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    text-align: center;
    line-height: 36px;
    color: #fff;
    font-size: 14px;
    margin-bottom: 8px;
}
.crm-kpi-icon.total { background: #1f78d1; }
.crm-kpi-icon.today { background: #16a34a; }
.crm-kpi-icon.upcoming { background: #f59e0b; }
.crm-kpi-title {
    margin: 0;
    font-size: 13px;
    font-weight: 700;
    color: #334155;
}
.crm-kpi-value {
    margin-top: 6px;
    font-size: 24px;
    line-height: 1;
    font-weight: 800;
    color: #0f172a;
}
.crm-kpi-hint {
    margin-top: 7px;
    font-size: 11px;
    color: #64748b;
}
.crm-percent-line {
    margin-top: 7px;
    color: #475569;
    font-size: 11px;
    font-weight: 700;
}
.crm-status-title {
    font-size: 13px;
    font-weight: 700;
    color: #334155;
    margin-bottom: 6px;
}
.crm-status-value {
    font-size: 20px;
    font-weight: 800;
    line-height: 1;
    color: #0f172a;
}
.crm-status-strip {
    width: 100%;
    height: 4px;
    border-radius: 8px;
    margin-bottom: 8px;
}
.crm-table-panel {
    border: 1px solid #dce3ed;
    border-radius: 8px;
    background: #fff;
    box-shadow: 0 1px 5px rgba(15, 23, 42, 0.06);
    padding: 12px;
}
.crm-table-heading {
    margin-bottom: 10px;
}
.crm-table-heading h4 {
    margin: 0;
    font-size: 20px;
    font-weight: 800;
    color: #0f172a;
}
.crm-filter-chip {
    display: inline-block;
    margin-top: 6px;
    padding: 3px 8px;
    border-radius: 20px;
    background: #eff6ff;
    color: #1e40af;
    font-size: 11px;
    font-weight: 700;
}
.crm-action-icons .btn {
    margin-right: 4px;
}
.crm-followup-filter {
    display: table;
    width: 100%;
    margin-bottom: 10px;
}
.crm-followup-filter-title,
.crm-followup-filter-actions {
    display: table-cell;
    vertical-align: middle;
}
.crm-followup-filter-actions {
    text-align: right;
}
.crm-followup-filter .btn {
    margin-left: 4px;
}
.crm-custom-date-form {
    display: inline-block;
    margin-left: 6px;
}
.crm-custom-date-form .form-control {
    display: inline-block;
    width: 118px;
    height: 30px;
    margin-left: 4px;
}
.crm-table-modern > thead > tr > th {
    background: #f8fafc;
    color: #334155;
    border-bottom: 1px solid #d8e0ea;
    font-weight: 800;
    font-size: 12px;
}
.crm-table-modern > tbody > tr > td {
    vertical-align: middle;
    color: #1f2937;
}
.crm-status-pill {
    border-radius: 14px;
    color: #fff;
    display: inline-block;
    font-size: 11px;
    font-weight: 800;
    padding: 4px 9px;
}
.crm-table-pager {
    border-top: 1px solid #e5eaf1;
    margin-top: 8px;
    padding-top: 10px;
}
.crm-table-pager .btn {
    min-width: 94px;
}
.crm-recent-wrap {
    margin-top: 16px;
}
.crm-export-actions .btn {
    margin-left: 4px;
}
.crm-print-header {
    text-align: center;
    margin-bottom: 8px;
}
.crm-print-logo {
    max-height: 72px;
    max-width: 220px;
    margin: 0 auto 6px auto;
    display: block;
}
.crm-print-header h2 {
    margin: 0 0 4px 0;
    font-size: 30px;
}
.crm-print-sub {
    font-size: 12px;
    margin-bottom: 6px;
}
@media (max-width: 991px) {
    .crm-right-pane {
        margin-top: 8px;
    }
    .crm-followup-filter,
    .crm-followup-filter-title,
    .crm-followup-filter-actions {
        display: block;
        text-align: left;
    }
    .crm-followup-filter-actions .btn,
    .crm-custom-date-form {
        margin-top: 6px;
        margin-left: 0;
    }
}
</style>
<script type="text/javascript">
(function($){
    function getRecentExportRows()
    {
        var rows = [];
        $('#recentLeadsExportTable tbody tr').each(function(){
            var $tr = $(this);
            if($tr.hasClass('no-export-row'))
            {
                return;
            }
            rows.push([
                $.trim($tr.find('td:eq(0)').text()),
                $.trim($tr.find('td:eq(1)').text()),
                $.trim($tr.find('td:eq(2)').text()),
                $.trim($tr.find('td:eq(3)').text()),
                $.trim($tr.find('td:eq(4)').text()),
                $.trim($tr.find('td:eq(5)').text()),
                $.trim($tr.find('td:eq(6)').text()),
                $.trim($tr.find('td:eq(7)').text())
            ]);
        });
        return rows;
    }

    function csvEscape(value)
    {
        var text = String(value || '');
        if(/[",\n]/.test(text))
        {
            text = '"' + text.replace(/"/g, '""') + '"';
        }
        return text;
    }

    window.exportRecentLeadsCsv = function(){
        var rows = getRecentExportRows();
        if(!rows.length)
        {
            alert('<?php echo addslashes(__('No records found for CSV export.'));?>');
            return false;
        }
        var csvLines = [];
        csvLines.push(csvEscape('<?php echo addslashes(__('Filter'));?>') + ',' + csvEscape('<?php echo addslashes($scopeLabel);?>'));
        csvLines.push(csvEscape('<?php echo addslashes(__('Agent Name'));?>') + ',' + csvEscape('<?php echo addslashes($selectedAgentName);?>'));
        csvLines.push('');
        csvLines.push([
            '<?php echo addslashes(__('#'));?>',
            '<?php echo addslashes(__('Lead Name'));?>',
            '<?php echo addslashes(__('SMS Mobile #'));?>',
            '<?php echo addslashes(__('Email'));?>',
            '<?php echo addslashes(__('Last Contact Date'));?>',
            '<?php echo addslashes(__('Follow Up'));?>',
            '<?php echo addslashes(__('Remarks'));?>',
            '<?php echo addslashes(__('Status'));?>'
        ].map(csvEscape).join(','));
        for(var i=0;i<rows.length;i++)
        {
            csvLines.push(rows[i].map(csvEscape).join(','));
        }
        var csvBlob = new Blob(["\uFEFF" + csvLines.join("\n")], {type:'text/csv;charset=utf-8;'});
        var stamp = (new Date()).toISOString().replace(/[-:T]/g,'').slice(0,14);
        var filename = 'recent_leads_' + stamp + '.csv';
        var link = document.createElement('a');
        link.href = URL.createObjectURL(csvBlob);
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        return false;
    };

    window.printRecentLeadsTable = function(){
        var source = document.getElementById('recentLeadsPrintable');
        if(!source)
        {
            return false;
        }
        var popup = window.open('', 'recent_leads_print', 'width=1100,height=800');
        if(!popup)
        {
            return false;
        }
        popup.document.write('<html><head><title><?php echo addslashes(__('Recent Leads Report'));?></title>');
        popup.document.write('<style>body{font-family:Arial,sans-serif;padding:15px;}table{width:100%;border-collapse:collapse;}th,td{border:1px solid #999;padding:6px;font-size:12px;}th{background:#f2f2f2;}h2,h3,p{margin:0 0 5px 0;}.text-center{text-align:center;}.crm-print-header{text-align:center;}.crm-print-logo{max-height:72px;max-width:220px;margin:0 auto 6px auto;display:block;}</style>');
        popup.document.write('</head><body>');
        popup.document.write(source.innerHTML);
        popup.document.write('</body></html>');
        popup.document.close();
        popup.focus();
        popup.print();
        return false;
    };

    var dashboardPdfLoader = null;
    function ensureDashboardPdfLib()
    {
        if(typeof html2pdf !== 'undefined')
        {
            return $.Deferred().resolve().promise();
        }
        if(dashboardPdfLoader)
        {
            return dashboardPdfLoader.promise();
        }
        dashboardPdfLoader = $.Deferred();
        var script = document.createElement('script');
        script.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js';
        script.onload = function(){ dashboardPdfLoader.resolve(); };
        script.onerror = function(){ dashboardPdfLoader.reject(); dashboardPdfLoader = null; };
        document.getElementsByTagName('head')[0].appendChild(script);
        return dashboardPdfLoader.promise();
    }

    window.exportRecentLeadsPdf = function(){
        var source = document.getElementById('recentLeadsPrintable');
        if(!source)
        {
            return false;
        }
        ensureDashboardPdfLib().done(function(){
            var node = source.cloneNode(true);
            node.style.display = 'block';
            var opt = {
                margin: [8,8,8,8],
                filename: 'recent_leads_' + (new Date()).toISOString().replace(/[-:T]/g,'').slice(0,14) + '.pdf',
                image: {type:'jpeg', quality:0.98},
                html2canvas: {scale:2, useCORS:true},
                jsPDF: {unit:'mm', format:'a4', orientation:'landscape'}
            };
            html2pdf().set(opt).from(node).save();
        }).fail(function(){
            printRecentLeadsTable();
        });
        return false;
    };
})(jQuery);
</script>

<div class="page-title">
    <div class="title-env">
        <h1 class="title"><?php echo __('Leads CRM Dashboard');?></h1>
    </div>
</div>
<div class="panel">
    <div class="panel-heading">
        <div class="btn-group">
            <?php echo $this->Html->link('<span class="fa fa-plus"></span>&nbsp;'.__('Add Lead Contact'),array('controller'=>'Leads','action'=>'leadadd'),array('escape'=>false,'class'=>'btn btn-success'));?>
            <?php echo $this->Html->link('<span class="fa fa-upload"></span>&nbsp;'.__('Import Leads'),array('controller'=>'Leads','action'=>'import'),array('escape'=>false,'class'=>'btn btn-info'));?>
            <?php echo $this->Html->link('<span class="fa fa-columns"></span>&nbsp;'.__('Kanban Board'),array('controller'=>'Leads','action'=>'kanban'),array('escape'=>false,'class'=>'btn btn-primary'));?>
            <?php echo $this->Html->link('<span class="fa fa-trophy"></span>&nbsp;'.__('Leaderboard'),array('controller'=>'Leads','action'=>'leaderboard'),array('escape'=>false,'class'=>'btn btn-warning'));?>
            <button type="button" class="btn btn-default" onclick="openDailyAgendaModal();"><span class="fa fa-calendar-check-o text-primary"></span>&nbsp;<strong><?php echo __('Daily Agenda');?></strong></button>
            <?php echo $this->Html->link('<span class="fa fa-line-chart"></span>&nbsp;'.__('Progress'),array('controller'=>'Leads','action'=>'dailyprogress'),array('escape'=>false,'class'=>'btn btn-warning','target'=>'_blank'));?>
            <?php echo $this->Html->link('<span class="fa fa-list"></span>&nbsp;'.__('Manage Leads'),array('controller'=>'Leads','action'=>'manage'),array('escape'=>false,'class'=>'btn btn-default'));?>
            <?php echo $this->Html->link('<span class="fa fa-clock-o"></span>&nbsp;'.__('Follow-up Queue'),$followUpQueueUrl,array('escape'=>false,'class'=>'btn btn-default'));?>
            <?php echo $this->Html->link('<span class="fa fa-bullseye"></span>&nbsp;'.__('Lead Reactivation'),$leadReactivationUrl,array('escape'=>false,'class'=>'btn btn-success'));?>
            <?php echo $this->Html->link('<span class="fa fa-pie-chart"></span>&nbsp;'.__('Source ROI'),$sourceRoiUrl,array('escape'=>false,'class'=>'btn btn-info'));?>
        </div>
    </div>
    <div class="panel-body">
        <?php echo $this->Session->flash();?>

        <div class="crm-dashboard-shell">
        <div class="row crm-dashboard-layout">
            <div class="col-md-5">
                <div class="row crm-kpi-grid">
                    <div class="col-sm-6 col-xs-12">
                        <?php
                        echo $this->Html->link(
                            '<div class="crm-kpi-card'.($selectedScope === 'all' ? ' active' : '').'">'.
                            '<div class="crm-kpi-icon total"><span class="fa fa-users"></span></div>'.
                            '<p class="crm-kpi-title">'.__('Total Leads').'</p>'.
                            '<div class="crm-kpi-value">'.(int)$totalLeads.'</div>'.
                            '<div class="crm-kpi-hint">'.__('Show all lead data').'</div>'.
                            '<div class="crm-percent-line">'.__('Applied filter').': 100%</div>'.
                            '</div>',
                            array('controller'=>'Leads','action'=>'dashboard','?' => $buildScopeQuery('all')),
                            array('escape'=>false,'class'=>'crm-card-link')
                        );
                        ?>
                    </div>
                    <div class="col-sm-6 col-xs-12">
                        <?php
                        echo $this->Html->link(
                            '<div class="crm-kpi-card'.($selectedScope === 'today' ? ' active' : '').'">'.
                            '<div class="crm-kpi-icon today"><span class="fa fa-calendar-check-o"></span></div>'.
                            '<p class="crm-kpi-title">'.__('Follow Ups Today').'</p>'.
                            '<div class="crm-kpi-value">'.(int)$followUpTodayCount.'</div>'.
                            '<div class="crm-kpi-hint">'.__('Auto-filter: today only').'</div>'.
                            '<div class="crm-percent-line">'.$formatPercent($followUpTodayCount, $totalLeads).' '.__('of assigned leads').'</div>'.
                            '</div>',
                            array('controller'=>'Leads','action'=>'dashboard','?' => $buildScopeQuery('today')),
                            array('escape'=>false,'class'=>'crm-card-link')
                        );
                        ?>
                    </div>
                    <div class="col-sm-6 col-xs-12">
                        <?php
                        echo $this->Html->link(
                            '<div class="crm-kpi-card'.($selectedScope === 'overdue' ? ' active' : '').'" style="border-left: 3px solid #ef4444;">'.
                            '<div class="crm-kpi-icon" style="background:#ef4444;"><span class="fa fa-exclamation-triangle"></span></div>'.
                            '<p class="crm-kpi-title" style="color:#b91c1c;">'.__('Overdue Follow Ups').'</p>'.
                            '<div class="crm-kpi-value" style="color:#b91c1c;">'.(int)$followUpOverdueCount.'</div>'.
                            '<div class="crm-kpi-hint">'.__('Pending attention').'</div>'.
                            '<div class="crm-percent-line">'.$formatPercent($followUpOverdueCount, $totalLeads).' '.__('of assigned leads').'</div>'.
                            '</div>',
                            array('controller'=>'Leads','action'=>'dashboard','?' => $buildScopeQuery('overdue')),
                            array('escape'=>false,'class'=>'crm-card-link')
                        );
                        ?>
                    </div>
                    <div class="col-sm-6 col-xs-12">
                        <?php
                        echo $this->Html->link(
                            '<div class="crm-kpi-card'.($selectedScope === 'overseas' ? ' active' : '').'" style="border-left: 3px solid #4f46e5;">'.
                            '<div class="crm-kpi-icon" style="background:#4f46e5;"><span class="fa fa-globe"></span></div>'.
                            '<p class="crm-kpi-title" style="color:#4338ca;">'.__('🌍 Overseas Leads').'</p>'.
                            '<div class="crm-kpi-value" style="color:#4338ca;">'.(int)$overseasCount.'</div>'.
                            '<div class="crm-kpi-hint">'.__('International clients').'</div>'.
                            '<div class="crm-percent-line">'.$formatPercent($overseasCount, $totalLeads).' '.__('of total leads').'</div>'.
                            '</div>',
                            array('controller'=>'Leads','action'=>'dashboard','?' => $buildScopeQuery('overseas')),
                            array('escape'=>false,'class'=>'crm-card-link')
                        );
                        ?>
                    </div>
                    <div class="col-sm-6 col-xs-12">
                        <?php
                        echo $this->Html->link(
                            '<div class="crm-kpi-card'.($selectedScope === 'local' ? ' active' : '').'" style="border-left: 3px solid #059669;">'.
                            '<div class="crm-kpi-icon" style="background:#059669;"><span class="fa fa-map-marker"></span></div>'.
                            '<p class="crm-kpi-title" style="color:#047857;">'.__('🇵🇰 Local Leads').'</p>'.
                            '<div class="crm-kpi-value" style="color:#047857;">'.(int)$localCount.'</div>'.
                            '<div class="crm-kpi-hint">'.__('Pakistan clients').'</div>'.
                            '<div class="crm-percent-line">'.$formatPercent($localCount, $totalLeads).' '.__('of total leads').'</div>'.
                            '</div>',
                            array('controller'=>'Leads','action'=>'dashboard','?' => $buildScopeQuery('local')),
                            array('escape'=>false,'class'=>'crm-card-link')
                        );
                        ?>
                    </div>
                    <div class="col-sm-6 col-xs-12">
                        <div class="crm-kpi-card" style="border-left: 3px solid #10b981;">
                            <div class="crm-kpi-icon" style="background:#10b981;"><span class="fa fa-check-circle"></span></div>
                            <p class="crm-kpi-title" style="color:#047857;"><?php echo __('Won / Closed');?></p>
                            <div class="crm-kpi-value" style="color:#047857;"><?php echo (int)$wonCount;?></div>
                            <div class="crm-kpi-hint"><?php echo __('Deals closed successfully');?></div>
                            <div class="crm-percent-line"><strong><?php echo $winRate;?>%</strong> <?php echo __('Win Rate');?></div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xs-12">
                        <div class="crm-kpi-card" style="border-left: 3px solid #f43f5e;">
                            <div class="crm-kpi-icon" style="background:#f43f5e;"><span class="fa fa-times-circle"></span></div>
                            <p class="crm-kpi-title" style="color:#be123c;"><?php echo __('Lost / Cancelled');?></p>
                            <div class="crm-kpi-value" style="color:#be123c;"><?php echo (int)$lostCount;?></div>
                            <div class="crm-kpi-hint"><?php echo __('Deals lost or cancelled');?></div>
                            <div class="crm-percent-line"><strong><?php echo $lostRate;?>%</strong> <?php echo __('Lost Rate');?></div>
                        </div>
                    </div>
                </div>

                <!-- Win / Loss & Lost Reason Analytics Card -->
                <div class="panel" style="border:1px solid #e2e8f0;border-radius:10px;margin-top:16px;box-shadow:0 1px 3px rgba(0,0,0,0.05);">
                    <div class="panel-heading" style="background:#f8fafc;border-bottom:1px solid #e2e8f0;padding:12px 16px;">
                        <h4 class="panel-title" style="font-weight:700;font-size:14px;color:#1e293b;margin:0;">
                            <i class="fa fa-pie-chart text-danger"></i> <?php echo __('📉 Lost Reason Analytics');?>
                        </h4>
                    </div>
                    <div class="panel-body" style="padding:14px 16px;">
                        <?php if(empty($lostReasonsMap)){ ?>
                        <div class="text-muted text-center" style="padding:12px 0;font-size:12px;">
                            <i class="fa fa-info-circle"></i> <?php echo __('No lost reasons logged yet. Move cards to Cancelled on Kanban to capture reasons.');?>
                        </div>
                        <?php } else { ?>
                            <?php
                            $maxReasonCount = max($lostReasonsMap);
                            foreach($lostReasonsMap as $reasonName => $rCount){
                                $rPct = ($lostCount > 0) ? round(($rCount / $lostCount) * 100, 1) : 0;
                                $barW = ($maxReasonCount > 0) ? round(($rCount / $maxReasonCount) * 100) : 0;
                            ?>
                            <div style="margin-bottom:10px;">
                                <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:3px;">
                                    <span style="font-weight:600;color:#334155;"><?php echo h($reasonName);?></span>
                                    <span style="font-weight:700;color:#e11d48;"><?php echo $rCount;?> <small style="color:#64748b;">(<?php echo $rPct;?>%)</small></span>
                                </div>
                                <div class="progress" style="height:6px;margin:0;background:#f1f5f9;border-radius:3px;">
                                    <div class="progress-bar progress-bar-danger" style="width:<?php echo $barW;?>%;background:#f43f5e;"></div>
                                </div>
                            </div>
                            <?php } ?>
                        <?php } ?>
                    </div>
                </div>

                <div class="row crm-status-grid">
                    <?php foreach((array)$statusRows as $statusRow){
                        $statusId = (int)$statusRow['Status']['id'];
                        $count = isset($statusMap[$statusId]) ? (int)$statusMap[$statusId] : 0;
                        $color = !empty($statusRow['Status']['color']) ? $statusRow['Status']['color'] : '#3a87ad';
                        $statusScope = 'status_'.$statusId;
                        $isActiveStatus = ($selectedScope === strtolower($statusScope));
                    ?>
                    <div class="col-sm-6 col-xs-12">
                        <?php
                        echo $this->Html->link(
                            '<div class="crm-status-card'.($isActiveStatus ? ' active' : '').'">'.
                            '<div class="crm-status-strip" style="background:'.h($color).';"></div>'.
                            '<div class="crm-status-title">'.h($statusRow['Status']['name']).'</div>'.
                            '<div class="crm-status-value">'.$count.'</div>'.
                            '<div class="crm-percent-line">'.$formatPercent($count, $totalLeads).' '.__('of assigned leads').'</div>'.
                            '</div>',
                            array('controller'=>'Leads','action'=>'dashboard','?' => $buildScopeQuery($statusScope)),
                            array('escape'=>false,'class'=>'crm-card-link')
                        );
                        ?>
                    </div>
                    <?php } ?>
                </div>
            </div>

            <div class="col-md-7 crm-right-pane">
                <div class="crm-table-panel">
                    <div class="crm-followup-filter">
                        <div class="crm-followup-filter-title">
                            <h4><?php echo __('Upcoming Follow Ups');?></h4>
                            <span class="crm-filter-chip"><?php echo __('Period').': '.h($followupPeriodLabel);?></span>
                            <span class="crm-filter-chip"><?php echo __('Records').': '.(int)$followupTableCount;?></span>
                        </div>
                        <div class="crm-followup-filter-actions">
                            <?php
                            $periodButtons = array('today' => __('Today'), 'week' => __('Week'), 'month' => __('Month'), 'overdue' => __('🚨 Overdue'), 'all' => __('All'));
                            foreach($periodButtons as $periodKey => $periodText)
                            {
                                echo $this->Html->link($periodText, array('controller'=>'Leads','action'=>'dashboard','?' => $buildFollowupQuery($periodKey, 1)), array('class' => 'btn btn-sm '.($selectedFollowupPeriod === $periodKey ? 'btn-primary' : 'btn-default')));
                            }
                            ?>
                            <form method="get" action="<?php echo h($this->Html->url(array('controller'=>'Leads','action'=>'dashboard')));?>" class="crm-custom-date-form">
                                <?php foreach($dashboardBaseQuery as $queryKey => $queryValue){ ?>
                                <input type="hidden" name="<?php echo h($queryKey);?>" value="<?php echo h($queryValue);?>">
                                <?php } ?>
                                <input type="hidden" name="scope" value="<?php echo h($selectedScope);?>">
                                <input type="hidden" name="followup_period" value="custom">
                                <input type="text" name="followup_start" class="form-control input-sm" value="<?php echo h($followupStartInput);?>" placeholder="<?php echo __('Start Date');?>">
                                <input type="text" name="followup_end" class="form-control input-sm" value="<?php echo h($followupEndInput);?>" placeholder="<?php echo __('End Date');?>">
                                <button type="submit" class="btn btn-sm <?php echo $selectedFollowupPeriod === 'custom' ? 'btn-primary' : 'btn-default';?>"><?php echo __('Date');?></button>
                            </form>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered crm-table-modern">
                            <thead>
                                <tr>
                                    <th><?php echo __('#');?></th>
                                    <th><?php echo __('Lead Name');?></th>
                                    <th><?php echo __('Mobile');?></th>
                                    <th><?php echo __('Follow Up');?></th>
                                    <th><?php echo __('Status');?></th>
                                    <th><?php echo __('Action');?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $i=(($followupCurrentPage - 1) * 12) + 1; foreach((array)$upcomingLeads as $row){
                                    $leadName = trim((string)$row['Lead']['lead_name'])!=='' ? $row['Lead']['lead_name'] : (isset($row['Client']['name']) ? $row['Client']['name'] : '');
                                    $leadMobile = trim((string)$row['Lead']['lead_mobile'])!=='' ? $row['Lead']['lead_mobile'] : (isset($row['Client']['mobile']) ? $row['Client']['mobile'] : '');
                                    $leadCallNo = preg_replace('/[^0-9\+]/', '', (string)$leadMobile);
                                ?>
                                <?php
                                    $waUrl = $formatWhatsAppUrl($leadMobile, $leadName);
                                    $tempBadge = isset($row['Lead']['lead_temperature']) ? $renderTempBadge($row['Lead']['lead_temperature']) : '';
                                    $leadStatusId = isset($row['Lead']['status_id']) ? (int)$row['Lead']['status_id'] : 0;
                                    $leadTemp = isset($row['Lead']['lead_temperature']) ? $row['Lead']['lead_temperature'] : 'warm';
                                    $leadFollowupRaw = !empty($row['Lead']['follow_up']) ? $row['Lead']['follow_up'] : '';
                                    $isOverdue = (!empty($leadFollowupRaw) && $leadFollowupRaw !== '0000-00-00 00:00:00' && strtotime($leadFollowupRaw) < time());
                                ?>
                                <tr id="lead-row-<?php echo (int)$row['Lead']['id'];?>" class="<?php echo $isOverdue ? 'danger' : '';?>">
                                    <td><?php echo $i++;?></td>
                                    <td>
                                        <?php echo $tempBadge;?>
                                        <strong><?php echo h($leadName);?></strong>
<?php 
    $isFbSync = (
        (!empty($row['Lead']['lead_source']) && (stripos($row['Lead']['lead_source'], 'Facebook') !== false || stripos($row['Lead']['lead_source'], 'FB') !== false)) ||
        (!empty($row['Lead']['remarks']) && (stripos($row['Lead']['remarks'], 'Facebook') !== false || stripos($row['Lead']['remarks'], 'White Alpine') !== false || stripos($row['Lead']['remarks'], 'White ICE') !== false || stripos($row['Lead']['remarks'], 'Form ID') !== false))
    );
    if($isFbSync){ 
?>
    <span class="label label-primary" style="background:#1877f2;border:none;color:#fff;font-size:10px;font-weight:700;padding:2px 5px;border-radius:3px;margin-left:3px;vertical-align:middle;" title="Synced from Facebook Lead Ads">
        <i class="fa fa-facebook-square"></i> FB Auto Sync
    </span>
<?php } ?>
                                    </td>
                                    <td>
                                        <?php
                                        if(trim((string)$leadMobile) === '')
                                        {
                                            echo '-';
                                        }
                                        elseif(trim((string)$leadCallNo) !== '')
                                        {
                                            echo $this->Html->link(h($leadMobile), 'tel:'.$leadCallNo, array('escape' => false, 'style' => 'font-weight:700;'));
                                        }
                                        else
                                        {
                                            echo h($leadMobile);
                                        }
                                        $reg = $detectRegion($leadMobile);
                                        if($reg && $reg['is_overseas']){
                                            echo '<br/><span class="badge" style="background:#4f46e5;color:#fff;font-size:9.5px;padding:2px 6px;margin-top:3px;display:inline-block;" title="Overseas Client ('.$reg['country'].')"><i class="fa fa-globe"></i> '.$reg['badge'].'</span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        if(!empty($row['Lead']['follow_up']) && $row['Lead']['follow_up'] !== '0000-00-00 00:00:00')
                                        {
                                            $formattedDate = $this->Time->format($sysDay.$dateSep.$sysMonth.$dateSep.$sysYear.$dateGap.$sysHour.$timeSep.$sysMin.$dateGap.$sysMer,$row['Lead']['follow_up']);
                                            if($isOverdue)
                                            {
                                                echo '<span class="text-danger" style="font-weight:800;" title="Overdue Follow-up"><span class="fa fa-exclamation-circle"></span> ' . $formattedDate . '</span>';
                                            }
                                            else
                                            {
                                                echo $formattedDate;
                                            }
                                        }
                                        else
                                        {
                                            echo '-';
                                        }
                                        ?>
                                    </td>
                                    <td><span class="crm-status-pill" id="lead-status-pill-<?php echo (int)$row['Lead']['id'];?>" style="background-color:<?php echo h($row['Status']['color']);?>;"><?php echo h($row['Status']['name']);?></span></td>
                                    <td class="crm-action-icons" style="white-space:nowrap;">
                                        <?php if($waUrl !== ''){ ?>
                                            <a href="<?php echo h($waUrl);?>" target="_blank" class="btn btn-xs btn-success" title="Chat on WhatsApp" style="background:#25D366;border-color:#25D366;"><span class="fa fa-whatsapp"></span></a>
                                        <?php } ?>
                                        <button type="button" class="btn btn-xs btn-info crm-quick-log-btn"
                                            data-lead-id="<?php echo (int)$row['Lead']['id'];?>"
                                            data-lead-name="<?php echo h($leadName);?>"
                                            data-lead-mobile="<?php echo h($leadMobile);?>"
                                            data-lead-status="<?php echo $leadStatusId;?>"
                                            data-lead-temp="<?php echo h($leadTemp);?>"
                                            data-lead-followup="<?php echo h($leadFollowupRaw);?>"
                                            title="Quick Log / Call Note"><span class="fa fa-phone"></span></button>
                                        <?php echo $this->Html->link('<span class="fa fa-eye"></span>',array('controller'=>'Leads','action'=>'leadview',$row['Lead']['id']),array('class'=>'btn btn-xs btn-default','escape'=>false,'title'=>__('View Lead')));?>
                                        <?php echo $this->Html->link('<span class="fa fa-pencil"></span>',array('controller'=>'Leads','action'=>'leadedit',$row['Lead']['id']),array('class'=>'btn btn-xs btn-warning','escape'=>false,'title'=>__('Edit Lead')));?>
                                    </td>
                                </tr>
                                <?php } ?>
                                <?php if(empty($upcomingLeads)){ ?>
                                <tr><td colspan="6" class="text-center"><?php echo __('No follow ups found');?></td></tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="crm-table-pager">
                        <div class="pull-left">
                            <?php echo __('Page');?> <?php echo (int)$followupCurrentPage;?> <?php echo __('of');?> <?php echo (int)$followupTotalPages;?>
                        </div>
                        <div class="pull-right">
                            <?php
                            $prevDisabled = $followupCurrentPage <= 1;
                            $nextDisabled = $followupCurrentPage >= $followupTotalPages;
                            echo $this->Html->link('<span class="fa fa-chevron-left"></span>&nbsp;'.__('Previous'), $prevDisabled ? 'javascript:void(0);' : array('controller'=>'Leads','action'=>'dashboard','?' => $buildFollowupQuery($selectedFollowupPeriod, $followupCurrentPage - 1)), array('escape'=>false,'class'=>'btn btn-sm '.($prevDisabled ? 'btn-default disabled' : 'btn-default')));
                            echo ' ';
                            echo $this->Html->link(__('Next').'&nbsp;<span class="fa fa-chevron-right"></span>', $nextDisabled ? 'javascript:void(0);' : array('controller'=>'Leads','action'=>'dashboard','?' => $buildFollowupQuery($selectedFollowupPeriod, $followupCurrentPage + 1)), array('escape'=>false,'class'=>'btn btn-sm '.($nextDisabled ? 'btn-default disabled' : 'btn-primary')));
                            ?>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                </div>
            </div>
        </div>
        </div>

        <div class="crm-table-panel crm-recent-wrap">
            <div class="crm-table-heading">
                <div class="pull-right crm-export-actions">
                    <?php echo $this->Html->link('<span class="fa fa-print"></span>&nbsp;'.__('Print'), 'javascript:void(0);', array('escape' => false, 'class' => 'btn btn-default btn-sm', 'onclick' => 'return printRecentLeadsTable();'));?>
                    <?php echo $this->Html->link('<span class="fa fa-file-pdf-o"></span>&nbsp;'.__('Export PDF'), 'javascript:void(0);', array('escape' => false, 'class' => 'btn btn-success btn-sm', 'onclick' => 'return exportRecentLeadsPdf();'));?>
                    <?php echo $this->Html->link('<span class="fa fa-file-excel-o"></span>&nbsp;'.__('Export CSV'), 'javascript:void(0);', array('escape' => false, 'class' => 'btn btn-info btn-sm', 'onclick' => 'return exportRecentLeadsCsv();'));?>
                </div>
                <h4><?php echo __('Recent Leads');?></h4>
                <span class="crm-filter-chip"><?php echo __('Filter').': '.h($scopeLabel);?></span>
                <span class="crm-filter-chip"><?php echo __('Agent Name').': '.h($selectedAgentName);?></span>
                <div class="clearfix"></div>
            </div>
            <div class="table-responsive">
                <table class="table table-striped table-bordered crm-table-modern" id="recentLeadsExportTable">
                    <thead>
                        <tr>
                            <th><?php echo __('#');?></th>
                            <th><?php echo __('Lead Name');?></th>
                            <th><?php echo __('SMS Mobile #');?></th>
                            <th><?php echo __('Email');?></th>
                            <th><?php echo __('Last Contact Date');?></th>
                            <th><?php echo __('Follow Up');?></th>
                            <th><?php echo __('Remarks');?></th>
                            <th><?php echo __('Status');?></th>
                            <th><?php echo __('Action');?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $iRecent=1; foreach((array)$recentLeads as $row){
                            $leadName = trim((string)$row['Lead']['lead_name'])!=='' ? $row['Lead']['lead_name'] : (isset($row['Client']['name']) ? $row['Client']['name'] : '');
                            $leadMobile = trim((string)$row['Lead']['lead_mobile'])!=='' ? $row['Lead']['lead_mobile'] : (isset($row['Client']['mobile']) ? $row['Client']['mobile'] : '');
                            if(trim((string)$leadMobile) === '' && !empty($row['Client']['phone']))
                            {
                                $leadMobile = (string)$row['Client']['phone'];
                            }
                            $leadCallNo = preg_replace('/[^0-9\+]/', '', (string)$leadMobile);
                            $leadEmail = trim((string)$row['Lead']['lead_email'])!=='' ? $row['Lead']['lead_email'] : (isset($row['Client']['email']) ? $row['Client']['email'] : '');
                            $leadRemark = trim((string)$row['Lead']['remarks']);
                        ?>
                        <tr>
                            <td><?php echo $iRecent++;?></td>
                            <td>
                                <?php echo h($leadName);?>
                                <?php 
                                    $isFbSync = (
                                        (!empty($row['Lead']['lead_source']) && (stripos($row['Lead']['lead_source'], 'Facebook') !== false || stripos($row['Lead']['lead_source'], 'FB') !== false)) ||
                                        (!empty($row['Lead']['remarks']) && (stripos($row['Lead']['remarks'], 'Facebook') !== false || stripos($row['Lead']['remarks'], 'White Alpine') !== false || stripos($row['Lead']['remarks'], 'White ICE') !== false || stripos($row['Lead']['remarks'], 'Form ID') !== false))
                                    );
                                    if($isFbSync){
                                        echo '<span class="label label-primary" style="background:#1877f2;border:none;color:#fff;font-size:10px;font-weight:700;padding:2px 5px;border-radius:3px;display:inline-block;margin-left:3px;vertical-align:middle;" title="Synced from Facebook Lead Ads"><i class="fa fa-facebook-square"></i> FB Auto Sync</span>';
                                    }
                                ?>
                            </td>
                            <td>
                                <?php
                                if(trim((string)$leadMobile) === '')
                                {
                                    echo '-';
                                }
                                elseif(trim((string)$leadCallNo) !== '')
                                {
                                    echo $this->Html->link(h($leadMobile), 'tel:'.$leadCallNo, array('escape' => false));
                                }
                                else
                                {
                                    echo h($leadMobile);
                                }
                                $reg = $detectRegion($leadMobile);
                                if($reg && $reg['is_overseas']){
                                    echo '<br/><span class="badge" style="background:#4f46e5;color:#fff;font-size:9.5px;padding:2px 6px;margin-top:3px;display:inline-block;" title="Overseas Client ('.$reg['country'].')"><i class="fa fa-globe"></i> '.$reg['badge'].'</span>';
                                }
                                ?>
                            </td>
                            <td><?php echo h($leadEmail);?></td>
                            <td><?php
                                if(!empty($row['Lead']['last_contact_date']) && $row['Lead']['last_contact_date'] !== '0000-00-00')
                                {
                                    echo $this->Time->format($sysDay.$dateSep.$sysMonth.$dateSep.$sysYear, $row['Lead']['last_contact_date']);
                                }
                                elseif(!empty($row['Lead']['follow_up']) && $row['Lead']['follow_up'] !== '0000-00-00 00:00:00')
                                {
                                    echo date($sysDay.$dateSep.$sysMonth.$dateSep.$sysYear, strtotime($row['Lead']['follow_up']));
                                }
                                else
                                {
                                    echo '-';
                                }
                            ?></td>
                            <td><?php echo !empty($row['Lead']['follow_up']) && $row['Lead']['follow_up'] !== '0000-00-00 00:00:00' ? $this->Time->format($sysDay.$dateSep.$sysMonth.$dateSep.$sysYear.$dateGap.$sysHour.$timeSep.$sysMin.$dateGap.$sysMer,$row['Lead']['follow_up']) : '-';?></td>
                            <td title="<?php echo h($leadRemark);?>">
                                <?php
                                if($leadRemark === '')
                                {
                                    echo '-';
                                }
                                elseif(strlen($leadRemark) > 70)
                                {
                                    echo h(substr($leadRemark,0,70)).'...';
                                }
                                else
                                {
                                    echo h($leadRemark);
                                }
                                ?>
                            </td>
                            <td><span class="crm-status-pill" id="recent-status-pill-<?php echo (int)$row['Lead']['id'];?>" style="background-color:<?php echo h($row['Status']['color']);?>;"><?php echo h($row['Status']['name']);?></span></td>
                            <td class="crm-action-icons" style="white-space:nowrap;">
                                <?php $recentWa = $formatWhatsAppUrl($leadMobile, $leadName); if($recentWa !== ''){ ?>
                                    <a href="<?php echo h($recentWa);?>" target="_blank" class="btn btn-xs btn-success" title="Chat on WhatsApp" style="background:#25D366;border-color:#25D366;"><span class="fa fa-whatsapp"></span></a>
                                <?php } ?>
                                <button type="button" class="btn btn-xs btn-info crm-quick-log-btn"
                                    data-lead-id="<?php echo (int)$row['Lead']['id'];?>"
                                    data-lead-name="<?php echo h($leadName);?>"
                                    data-lead-mobile="<?php echo h($leadMobile);?>"
                                    data-lead-status="<?php echo (int)$row['Lead']['status_id'];?>"
                                    data-lead-temp="<?php echo isset($row['Lead']['lead_temperature']) ? h($row['Lead']['lead_temperature']) : 'warm';?>"
                                    data-lead-followup="<?php echo !empty($row['Lead']['follow_up']) ? h($row['Lead']['follow_up']) : '';?>"
                                    title="Quick Log / Call Note"><span class="fa fa-phone"></span></button>
                                <?php echo $this->Html->link('<span class="fa fa-eye"></span>',array('controller'=>'Leads','action'=>'leadview',$row['Lead']['id']),array('class'=>'btn btn-xs btn-default','escape'=>false,'title'=>__('View Lead')));?>
                                <?php echo $this->Html->link('<span class="fa fa-pencil"></span>',array('controller'=>'Leads','action'=>'leadedit',$row['Lead']['id']),array('class'=>'btn btn-xs btn-warning','escape'=>false,'title'=>__('Edit Lead')));?>
                            </td>
                        </tr>
                        <?php } ?>
                        <?php if(empty($recentLeads)){ ?>
                        <tr class="no-export-row"><td colspan="9" class="text-center"><?php echo __('No leads found');?></td></tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="recentLeadsPrintable" style="display:none;">
            <div class="crm-print-header">
                <?php if($headerAgencyLogoUrl !== ''){ ?>
                <img src="<?php echo h($headerAgencyLogoUrl);?>" alt="<?php echo h($headerAgencyName);?>" class="crm-print-logo">
                <?php } ?>
                <h2><?php echo h($headerAgencyName);?></h2>
                <?php if(trim((string)$headerAgencyAddress) !== ''){ ?>
                <div class="crm-print-sub"><?php echo h($headerAgencyAddress);?></div>
                <?php } ?>
                <div class="crm-print-sub">
                    <?php
                    $printContact = array();
                    if(trim((string)$headerAgencyPhone) !== '')$printContact[] = __('Phone').': '.h($headerAgencyPhone);
                    if(trim((string)$headerAgencyMobile) !== '')$printContact[] = __('Mobile').': '.h($headerAgencyMobile);
                    if(trim((string)$headerAgencyEmail) !== '')$printContact[] = __('Email').': '.h($headerAgencyEmail);
                    echo implode(' | ', $printContact);
                    ?>
                </div>
                <h3><?php echo __('Recent Leads Report');?></h3>
                <div class="crm-print-sub"><strong><?php echo __('Selected Filter');?>:</strong> <?php echo h($scopeLabel);?> | <strong><?php echo __('Agent Name');?>:</strong> <?php echo h($selectedAgentName);?></div>
            </div>
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th><?php echo __('#');?></th>
                        <th><?php echo __('Lead Name');?></th>
                        <th><?php echo __('SMS Mobile #');?></th>
                        <th><?php echo __('Email');?></th>
                        <th><?php echo __('Last Contact Date');?></th>
                        <th><?php echo __('Follow Up');?></th>
                        <th><?php echo __('Remarks');?></th>
                        <th><?php echo __('Status');?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $iPrint = 1; foreach((array)$recentLeads as $row){
                        $leadNamePrint = trim((string)$row['Lead']['lead_name'])!=='' ? $row['Lead']['lead_name'] : (isset($row['Client']['name']) ? $row['Client']['name'] : '');
                        $leadMobilePrint = trim((string)$row['Lead']['lead_mobile'])!=='' ? $row['Lead']['lead_mobile'] : (isset($row['Client']['mobile']) ? $row['Client']['mobile'] : '');
                        if(trim((string)$leadMobilePrint) === '' && !empty($row['Client']['phone']))
                        {
                            $leadMobilePrint = (string)$row['Client']['phone'];
                        }
                        $leadEmailPrint = trim((string)$row['Lead']['lead_email'])!=='' ? $row['Lead']['lead_email'] : (isset($row['Client']['email']) ? $row['Client']['email'] : '');
                        $leadRemarkPrint = trim((string)$row['Lead']['remarks']);
                    ?>
                    <tr>
                        <td><?php echo $iPrint++;?></td>
                        <td><?php echo h($leadNamePrint);?></td>
                        <td><?php echo h($leadMobilePrint);?></td>
                        <td><?php echo h($leadEmailPrint);?></td>
                        <td><?php
                            if(!empty($row['Lead']['last_contact_date']) && $row['Lead']['last_contact_date'] !== '0000-00-00')
                            {
                                echo $this->Time->format($sysDay.$dateSep.$sysMonth.$dateSep.$sysYear, $row['Lead']['last_contact_date']);
                            }
                            elseif(!empty($row['Lead']['follow_up']) && $row['Lead']['follow_up'] !== '0000-00-00 00:00:00')
                            {
                                echo date($sysDay.$dateSep.$sysMonth.$dateSep.$sysYear, strtotime($row['Lead']['follow_up']));
                            }
                            else
                            {
                                echo '-';
                            }
                        ?></td>
                        <td><?php echo !empty($row['Lead']['follow_up']) && $row['Lead']['follow_up'] !== '0000-00-00 00:00:00' ? $this->Time->format($sysDay.$dateSep.$sysMonth.$dateSep.$sysYear.$dateGap.$sysHour.$timeSep.$sysMin.$dateGap.$sysMer,$row['Lead']['follow_up']) : '-';?></td>
                        <td><?php echo h($leadRemarkPrint);?></td>
                        <td><?php echo h($row['Status']['name']);?></td>
                    </tr>
                    <?php } ?>
                    <?php if(empty($recentLeads)){ ?>
                    <tr><td colspan="8" class="text-center"><?php echo __('No leads found');?></td></tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Quick Call / Activity Log Modal -->
<div class="modal fade" id="crmQuickLogModal" tabindex="-1" role="dialog" aria-labelledby="crmQuickLogModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="border-radius:8px;">
            <div class="modal-header" style="background:#1e40af;color:#fff;border-top-left-radius:8px;border-top-right-radius:8px;">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:#fff;opacity:0.8;"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="crmQuickLogModalLabel" style="font-weight:800;"><span class="fa fa-phone"></span> Quick Lead Update &amp; Call Log</h4>
            </div>
            <form id="crmQuickLogForm" method="post" action="<?php echo $this->Html->url(array('controller'=>'Leads','action'=>'quicklog'));?>">
                <input type="hidden" name="id" id="quickLogLeadId" value="">
                <div class="modal-body" style="padding:18px;">
                    <div class="well well-sm" style="background:#f8fafc;border:1px solid #e2e8f0;margin-bottom:14px;">
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
                                <label style="font-weight:700;">Pipeline Status</label>
                                <select name="status_id" id="quickLogStatusId" class="form-control">
                                    <?php foreach((array)$statusRows as $stRow){ ?>
                                        <option value="<?php echo (int)$stRow['Status']['id'];?>"><?php echo h($stRow['Status']['name']);?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label style="font-weight:700;">Lead Temperature</label>
                                <select name="lead_temperature" id="quickLogTemperature" class="form-control">
                                    <option value="hot">🔥 Hot (Immediate Buying)</option>
                                    <option value="warm">⚡ Warm (Interested / Evaluating)</option>
                                    <option value="cold">❄️ Cold (Future / Low Budget)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label style="font-weight:700;">Next Follow-up Date &amp; Time</label>
                        <input type="text" name="follow_up" id="quickLogFollowup" class="form-control datetimepicker" placeholder="YYYY-MM-DD HH:MM">
                    </div>

                    <div class="form-group">
                        <label style="font-weight:700;">Call Outcome &amp; Activity Remarks</label>
                        <div style="margin-bottom:6px;">
                            <span class="label label-default crm-preset-chip" style="cursor:pointer;margin-right:4px;display:inline-block;padding:4px 8px;" onclick="addPresetRemark('Call Connected - Very Interested')">Call Connected</span>
                            <span class="label label-default crm-preset-chip" style="cursor:pointer;margin-right:4px;display:inline-block;padding:4px 8px;" onclick="addPresetRemark('Not Attended / Busy')">Not Attended</span>
                            <span class="label label-default crm-preset-chip" style="cursor:pointer;margin-right:4px;display:inline-block;padding:4px 8px;" onclick="addPresetRemark('Scheduled Site Visit for this Sunday')">Site Visit</span>
                            <span class="label label-default crm-preset-chip" style="cursor:pointer;margin-right:4px;display:inline-block;padding:4px 8px;" onclick="addPresetRemark('Sent Payment Plan on WhatsApp')">Sent WhatsApp</span>
                        </div>
                        <textarea name="remarks" id="quickLogRemarks" class="form-control" rows="3" placeholder="Enter notes or click quick chips above..."></textarea>
                    </div>
                </div>
                <div class="modal-footer" style="background:#f8fafc;">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" id="crmQuickLogSubmitBtn" class="btn btn-primary" style="font-weight:700;"><span class="fa fa-check"></span> Save Update</button>
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

$(document).on('click', '.crm-quick-log-btn', function(){
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
                var newStatusText = $('#quickLogStatusId option:selected').text();
                $('#lead-status-pill-' + leadId).text(newStatusText);
                $('#recent-status-pill-' + leadId).text(newStatusText);
                alert('Lead update saved successfully!');
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

/* =========================================================
   DAILY AGENDA & LIVE FOLLOW-UP CHIME NOTIFICATION ENGINE
   ========================================================= */
function playFollowupChime() {
    try {
        var AudioContext = window.AudioContext || window.webkitAudioContext;
        if (!AudioContext) return;
        var ctx = new AudioContext();

        // 2-tone melodic crystal bell chime (880Hz -> 1174.66Hz)
        var now = ctx.currentTime;
        var osc1 = ctx.createOscillator();
        var gain1 = ctx.createGain();
        osc1.type = 'sine';
        osc1.frequency.setValueAtTime(880, now); // A5
        gain1.gain.setValueAtTime(0.3, now);
        gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.6);
        osc1.connect(gain1);
        gain1.connect(ctx.destination);
        osc1.start(now);
        osc1.stop(now + 0.6);

        var osc2 = ctx.createOscillator();
        var gain2 = ctx.createGain();
        osc2.type = 'sine';
        osc2.frequency.setValueAtTime(1174.66, now + 0.2); // D6
        gain2.gain.setValueAtTime(0.35, now + 0.2);
        gain2.gain.exponentialRampToValueAtTime(0.001, now + 0.9);
        osc2.connect(gain2);
        gain2.connect(ctx.destination);
        osc2.start(now + 0.2);
        osc2.stop(now + 0.9);
    } catch(e) {
        // AudioContext disabled or restricted by browser policy
    }
}

var lastNotifiedLeadIds = {};

function checkDueFollowups() {
    $.ajax({
        url: '<?php echo $this->Html->url(array('controller' => 'Leads', 'action' => 'due_followups_check'));?>',
        type: 'GET',
        dataType: 'json',
        success: function(res) {
            if (res && res.has_due && res.leads && res.leads.length > 0) {
                var newDueFound = false;
                res.leads.forEach(function(lead) {
                    if (!lastNotifiedLeadIds[lead.id]) {
                        lastNotifiedLeadIds[lead.id] = true;
                        newDueFound = true;

                        // Play sound chime
                        playFollowupChime();

                        // Native Browser Desktop Notification
                        if (window.Notification && Notification.permission === 'granted') {
                            new Notification('🔔 Follow-up Due: ' + lead.name, {
                                body: 'Scheduled for ' + lead.follow_up_time + (lead.mobile ? ' (Phone: ' + lead.mobile + ')' : ''),
                                icon: '<?php echo $this->Html->url('/img/logo.png', true);?>'
                            });
                        }
                    }
                });
            }
        }
    });
}

// Request notification permission on first user interaction
if (window.Notification && Notification.permission === 'default') {
    document.addEventListener('click', function requestNotif() {
        Notification.requestPermission();
        document.removeEventListener('click', requestNotif);
    }, { once: true });
}

// Run periodic live check every 60 seconds
setInterval(checkDueFollowups, 60000);
setTimeout(checkDueFollowups, 4000);

// Daily Agenda Modal Functions
window.openDailyAgendaModal = function() {
    $('#crmDailyAgendaModal').modal('show');
    $('#agendaLoadingState').show();
    $('#agendaContentState').hide();

    $.ajax({
        url: '<?php echo $this->Html->url(array('controller' => 'Leads', 'action' => 'daily_agenda'));?>',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            $('#agendaLoadingState').hide();
            $('#agendaContentState').show();

            $('#agendaAgentName').text(data.agent_name || 'Agent');
            $('#agendaTodayCount').text(data.today_count || 0);
            $('#agendaOverdueCount').text(data.overdue_count || 0);
            $('#agendaOverseasCount').text(data.overseas_count || 0);
            $('#agendaActiveCount').text(data.total_active || 0);

            var listHtml = '';
            if (data.today_leads && data.today_leads.length > 0) {
                data.today_leads.forEach(function(l) {
                    var tempBadge = (l.temp === 'hot') ? '<span class="label label-danger">🔥 Hot</span>' :
                                    (l.temp === 'cold' ? '<span class="label label-info">❄️ Cold</span>' : '<span class="label label-warning">☀️ Warm</span>');
                    listHtml += '<li class="list-group-item" style="display:flex;justify-content:space-between;align-items:center;">' +
                        '<div>' +
                            '<strong>' + (l.name || 'Lead #' + l.id) + '</strong> ' + tempBadge + '<br/>' +
                            '<small class="text-muted"><i class="fa fa-phone"></i> ' + (l.mobile || '-') + ' | <i class="fa fa-clock-o"></i> ' + l.follow_up + '</small>' +
                        '</div>' +
                        '<div>' +
                            '<a href="<?php echo $this->Html->url(array('controller'=>'Leads','action'=>'leadview'));?>/' + l.id + '" class="btn btn-xs btn-primary"><i class="fa fa-eye"></i> View</a> ' +
                            (l.mobile ? '<a href="tel:' + l.mobile + '" class="btn btn-xs btn-success"><i class="fa fa-phone"></i> Call</a>' : '') +
                        '</div>' +
                    '</li>';
                });
            } else {
                listHtml = '<li class="list-group-item text-center text-muted"><i class="fa fa-check-circle text-success"></i> No pending follow-ups remaining for today!</li>';
            }
            $('#agendaLeadsList').html(listHtml);
        },
        error: function() {
            $('#agendaLoadingState').html('<div class="alert alert-danger">Error loading daily agenda.</div>');
        }
    });
};
</script>

        </div><!-- /.crm-dashboard-shell -->
    </div><!-- /.panel-body -->
</div><!-- /.panel -->

<!-- Daily Agenda Modal -->
<div class="modal fade" id="crmDailyAgendaModal" tabindex="-1" role="dialog" aria-labelledby="crmDailyAgendaTitle">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="border-radius:12px;overflow:hidden;box-shadow:0 10px 25px rgba(0,0,0,0.2);">
            <div class="modal-header" style="background:linear-gradient(135deg, #1e1b4b, #4338ca);color:#fff;padding:20px 24px;">
                <button type="button" class="close" data-dismiss="modal" style="color:#fff;opacity:0.8;">&times;</button>
                <h4 class="modal-title" id="crmDailyAgendaTitle" style="font-weight:700;font-size:18px;">
                    <i class="fa fa-sun-o text-warning"></i> <?php echo __('Daily CRM Action Agenda');?>
                </h4>
                <p style="margin:4px 0 0 0;font-size:12px;color:#c7d2fe;"><?php echo __('Good day, ');?><strong id="agendaAgentName">Agent</strong>! <?php echo __('Here is your priority focus for today.');?></p>
            </div>
            <div class="modal-body" style="padding:24px;">
                <div id="agendaLoadingState" class="text-center" style="padding:30px 0;">
                    <span class="fa fa-spinner fa-spin fa-2x text-primary"></span>
                    <p class="text-muted" style="margin-top:8px;"><?php echo __('Loading your daily agenda...');?></p>
                </div>
                <div id="agendaContentState" style="display:none;">
                    <div class="row" style="margin-bottom:18px;">
                        <div class="col-xs-4 text-center">
                            <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:12px 6px;">
                                <div style="font-size:22px;font-weight:800;color:#2563eb;" id="agendaTodayCount">0</div>
                                <div style="font-size:11px;font-weight:700;color:#1e40af;text-transform:uppercase;"><?php echo __('Today');?></div>
                            </div>
                        </div>
                        <div class="col-xs-4 text-center">
                            <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:12px 6px;">
                                <div style="font-size:22px;font-weight:800;color:#dc2626;" id="agendaOverdueCount">0</div>
                                <div style="font-size:11px;font-weight:700;color:#991b1b;text-transform:uppercase;"><?php echo __('Overdue');?></div>
                            </div>
                        </div>
                        <div class="col-xs-4 text-center">
                            <div style="background:#eef2ff;border:1px solid #c7d2fe;border-radius:8px;padding:12px 6px;">
                                <div style="font-size:22px;font-weight:800;color:#4f46e5;" id="agendaOverseasCount">0</div>
                                <div style="font-size:11px;font-weight:700;color:#3730a3;text-transform:uppercase;"><?php echo __('🌍 Overseas');?></div>
                            </div>
                        </div>
                    </div>

                    <h5 style="font-weight:700;color:#334155;margin-bottom:8px;"><i class="fa fa-calendar-check-o text-primary"></i> <?php echo __('Scheduled Follow-ups for Today:');?></h5>
                    <ul class="list-group" id="agendaLeadsList" style="max-height:240px;overflow-y:auto;margin-bottom:0;">
                    </ul>
                </div>
            </div>
            <div class="modal-footer" style="background:#f8fafc;border-top:1px solid #e2e8f0;">
                <?php echo $this->Html->link('<i class="fa fa-columns"></i> '.__('Open Kanban Board'), array('controller'=>'Leads','action'=>'kanban'), array('class'=>'btn btn-primary', 'escape'=>false));?>
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Close Agenda');?></button>
            </div>
        </div>
    </div>
</div>