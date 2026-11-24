<?php
$reportTabs = isset($reportTabs) ? $reportTabs : array();
$activeReportTab = isset($activeReportTab) ? (string)$activeReportTab : 'daily';
$reportPreset = isset($reportPreset) ? (string)$reportPreset : 'custom';
$reportStart = isset($reportStart) ? (string)$reportStart : '';
$reportEnd = isset($reportEnd) ? (string)$reportEnd : '';
$reportData = isset($reportData) ? $reportData : array('title' => 'Report', 'columns' => array(), 'rows' => array(), 'rightCols' => array());
$rightCols = isset($reportData['rightCols']) ? $reportData['rightCols'] : array();
$rawCols = isset($reportData['rawCols']) ? (array)$reportData['rawCols'] : array();
$reportAgency = (isset($reportAgency) && is_array($reportAgency)) ? $reportAgency : array();
$reportVendorList = (isset($reportVendorList) && is_array($reportVendorList)) ? $reportVendorList : array();
$selectedVendorId = isset($selectedVendorId) ? (int)$selectedVendorId : 0;
$selectedVendorName = isset($selectedVendorName) ? trim((string)$selectedVendorName) : '';
$vendorLedgerSummary = (isset($vendorLedgerSummary) && is_array($vendorLedgerSummary)) ? $vendorLedgerSummary : array();
$vendorLedgerCredit = isset($vendorLedgerSummary['credit_material']) ? (float)$vendorLedgerSummary['credit_material'] : 0.0;
$vendorLedgerDebit = isset($vendorLedgerSummary['debit_paid']) ? (float)$vendorLedgerSummary['debit_paid'] : 0.0;
$vendorLedgerBalance = isset($vendorLedgerSummary['balance']) ? (float)$vendorLedgerSummary['balance'] : ($vendorLedgerCredit - $vendorLedgerDebit);
$formatReportDate = function($value) {
    $value = trim((string)$value);
    if($value === '' || $value === '0000-00-00' || $value === '0000-00-00 00:00:00')
    {
        return '-';
    }
    $ts = strtotime(str_replace('/', '-', $value));
    if($ts === false)
    {
        return '-';
    }
    return date('d-M-Y', $ts);
};
$formatReportInputDate = function($value) {
    $value = trim((string)$value);
    if($value === '' || $value === '0000-00-00' || $value === '0000-00-00 00:00:00')
    {
        return '';
    }
    $ts = strtotime(str_replace('/', '-', $value));
    if($ts === false)
    {
        return '';
    }
    return date('d-M-Y', $ts);
};
$reportStartDisplay = $formatReportInputDate($reportStart);
$reportEndDisplay = $formatReportInputDate($reportEnd);
$isCustomReportRange = ($reportPreset === 'custom');
$isLifetimeReportRange = ($reportPreset === 'lifetime');
$reportStartPlaceholder = $isLifetimeReportRange ? __('All dates') : __('Start date');
$reportEndPlaceholder = $isLifetimeReportRange ? __('All dates') : __('End date');
$formatReportDateTime = function($value) {
    $value = trim((string)$value);
    if($value === '' || $value === '0000-00-00' || $value === '0000-00-00 00:00:00')
    {
        return date('d-M-Y H:i:s');
    }
    $ts = strtotime(str_replace('/', '-', $value));
    if($ts === false)
    {
        return date('d-M-Y H:i:s');
    }
    return date('d-M-Y H:i:s', $ts);
};
$printDateTime = $formatReportDateTime(isset($currentDateTime) ? $currentDateTime : '');
$filterLabelMap = array(
    'daily' => __('Daily'),
    'weekly' => __('Weekly'),
    'monthly' => __('Monthly'),
    'yearly' => __('Yearly'),
    'custom' => __('Custom'),
    'lifetime' => __('Lifetime')
);
$selectedFilterLabel = isset($filterLabelMap[$reportPreset]) ? $filterLabelMap[$reportPreset] : ucfirst((string)$reportPreset);
$selectedFilterText = __('Selected Filter').': '.$selectedFilterLabel;
if($reportPreset === 'daily')
{
    $selectedFilterText .= ' | '.__('Date').': '.$formatReportDate($reportStart);
}
elseif($reportPreset === 'weekly')
{
    $selectedFilterText .= ' | '.__('Start').': '.$formatReportDate($reportStart);
    $selectedFilterText .= ' | '.__('End').': '.$formatReportDate($reportEnd);
}
elseif($reportPreset === 'monthly')
{
    $monthLabel = '-';
    if($reportStart !== '')
    {
        $monthTs = strtotime($reportStart);
        if($monthTs !== false)
        {
            $monthLabel = date('F Y', $monthTs);
        }
    }
    $selectedFilterText .= ' | '.__('Month').': '.$monthLabel;
}
elseif($reportPreset === 'yearly')
{
    $yearLabel = '-';
    if($reportStart !== '')
    {
        $yearTs = strtotime($reportStart);
        if($yearTs !== false)
        {
            $yearLabel = date('Y', $yearTs);
        }
    }
    $selectedFilterText .= ' | '.__('Year').': '.$yearLabel;
}
elseif($reportPreset === 'custom')
{
    $selectedFilterText .= ' | '.__('Start').': '.$formatReportDate($reportStart);
    $selectedFilterText .= ' | '.__('End').': '.$formatReportDate($reportEnd);
}
$selectedProjectLabel = !empty($isAgencySystemManager) ? __('All Assigned Agencies') : __('All Agencies');
if(isset($projectName) && is_array($projectName) && (int)$currentProjectId > 0 && !empty($projectName[(int)$currentProjectId]))
{
    $selectedProjectLabel = (string)$projectName[(int)$currentProjectId];
}
elseif(isset($projectName) && is_array($projectName) && $userType === 'AGY' && empty($isAgencySystemManager))
{
    $firstAgencyName = reset($projectName);
    if(is_string($firstAgencyName) && trim($firstAgencyName) !== '')
    {
        $selectedProjectLabel = trim($firstAgencyName);
    }
}
$selectedFilterText .= ' | '.__('Agency').': '.$selectedProjectLabel;

$printAgencyName = '';
if(!empty($reportAgency['name']))
{
    $printAgencyName = trim((string)$reportAgency['name']);
}
elseif(isset($siteOrganization) && trim((string)$siteOrganization) !== '')
{
    $printAgencyName = trim((string)$siteOrganization);
}
elseif(isset($siteName) && trim((string)$siteName) !== '')
{
    $printAgencyName = trim((string)$siteName);
}
else
{
    $printAgencyName = 'White Villas';
}
$printAgencyAddress = !empty($reportAgency['address']) ? trim((string)$reportAgency['address']) : (isset($siteAddress) ? trim((string)$siteAddress) : '');
$printAgencyPhone = !empty($reportAgency['phone']) ? trim((string)$reportAgency['phone']) : '';
$printAgencyMobile = !empty($reportAgency['mobile']) ? trim((string)$reportAgency['mobile']) : (isset($contact) ? trim((string)$contact) : '');
$printAgencyEmail = !empty($reportAgency['email']) ? trim((string)$reportAgency['email']) : (isset($siteEmail) ? trim((string)$siteEmail) : '');
$printContactBits = array();
if($printAgencyPhone !== ''){$printContactBits[] = __('Phone').': '.$printAgencyPhone;}
if($printAgencyMobile !== ''){$printContactBits[] = __('Mobile').': '.$printAgencyMobile;}
if($printAgencyEmail !== ''){$printContactBits[] = __('Email').': '.$printAgencyEmail;}
$printContactLine = implode(' | ', $printContactBits);
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
$printAgencyLogo = $resolveImage(array(
    !empty($reportAgency['photo']) ? 'agency_thumb/'.(string)$reportAgency['photo'] : '',
    !empty($reportAgency['photo']) ? 'agency/'.(string)$reportAgency['photo'] : '',
    !empty($reportAgency['photo']) ? (string)$reportAgency['photo'] : '',
    !empty($frontLogo) ? (string)$frontLogo : ''
));

$baseQuery = array(
    'tab' => $activeReportTab,
    'preset' => $reportPreset,
    'start' => $reportStart,
    'end' => $reportEnd,
    'project_id' => $currentProjectId
);
if($selectedVendorId > 0)
{
    $baseQuery['vendor_id'] = $selectedVendorId;
}
$exportQuery = $baseQuery;
$exportQuery['export'] = 'csv';

$balanceSheetSummary = (isset($balanceSheetSummary) && is_array($balanceSheetSummary)) ? $balanceSheetSummary : array();
$bsIncomeTotal = isset($balanceSheetSummary['income_total']) ? (float)$balanceSheetSummary['income_total'] : 0.0;
$bsExpenseTotal = isset($balanceSheetSummary['expense_total']) ? (float)$balanceSheetSummary['expense_total'] : 0.0;
$bsNetBalance = isset($balanceSheetSummary['net_balance']) ? (float)$balanceSheetSummary['net_balance'] : ($bsIncomeTotal - $bsExpenseTotal);
$bsTotalBase = $bsIncomeTotal + $bsExpenseTotal;
$bsIncomePercent = ($bsTotalBase > 0) ? round(($bsIncomeTotal / $bsTotalBase) * 100, 2) : 0.0;
$bsExpensePercent = ($bsTotalBase > 0) ? round(($bsExpenseTotal / $bsTotalBase) * 100, 2) : 0.0;
if($bsIncomePercent < 0){$bsIncomePercent = 0.0;}
if($bsIncomePercent > 100){$bsIncomePercent = 100.0;}
if($bsExpensePercent < 0){$bsExpensePercent = 0.0;}
if($bsExpensePercent > 100){$bsExpensePercent = 100.0;}
$bsDonutIncomeEdge = number_format($bsIncomePercent, 2, '.', '');
$currencyText = trim(strip_tags(isset($currency) ? (string)$currency : 'Rs'));
if($currencyText === '')
{
    $currencyText = 'Rs';
}
?>
<style type="text/css">
.ard-reports-wrap{background:#edf2f8;padding:14px;border-radius:12px;}
.ard-head{display:flex;justify-content:space-between;align-items:flex-start;gap:8px;flex-wrap:wrap;margin-bottom:12px;}
.ard-head h1{margin:0;font-size:42px;font-weight:700;color:#1f3556;line-height:1.1;}
.ard-head p{margin:6px 0 0;color:#5a6f8b;font-size:16px;}
.ard-report-tabs{display:flex;flex-wrap:wrap;gap:8px;margin:8px 0 14px;}
.ard-report-tabs a{background:#dfe8f4;border:1px solid #d3deed;color:#39557a;padding:7px 12px;border-radius:10px;font-size:12px;font-weight:600;text-decoration:none;}
.ard-report-tabs a.active{background:#fff;border-color:#6f90bc;color:#1c3254;}
.ard-report-filter{display:flex;align-items:center;gap:10px;flex-wrap:nowrap;margin-bottom:14px;overflow-x:auto;padding-bottom:2px;}
.ard-report-filter .form-control{height:42px;min-width:170px;width:auto;flex:0 0 auto;}
.ard-report-filter .btn{height:42px;flex:0 0 auto;white-space:nowrap;}
.ard-print-header{display:none;}
.ard-print-head-row{position:relative;min-height:64px;padding:0 130px;text-align:center;display:flex;align-items:center;justify-content:center;}
.ard-print-logo{position:absolute;left:0;top:50%;transform:translateY(-50%);width:120px;text-align:left;}
.ard-print-logo img{max-height:56px;max-width:120px;object-fit:contain;}
.ard-print-meta{display:inline-block;text-align:center;max-width:78%;}
.ard-print-meta h2{margin:0;font-size:24px;font-weight:700;color:#1f3556;line-height:1.1;}
.ard-print-meta p{margin:4px 0 0;font-size:12px;color:#4b607f;}
.ard-print-filter-meta{display:none;}
.ard-report-panel{background:#fff;border:1px solid #cfdbec;border-radius:16px;overflow:hidden;}
.ard-report-head{display:flex;justify-content:space-between;align-items:center;gap:8px;padding:14px 18px;border-bottom:1px solid #e2eaf6;}
.ard-report-title{font-size:22px;font-weight:700;color:#1e3657;}
.ard-report-actions .btn{margin-left:6px;}
.ard-report-table{margin:0;}
.ard-report-table thead th{background:#f1f5fb;color:#6a7f9f;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid #dce7f6;}
.ard-report-table thead .ard-column-group th{background:#173f83;color:#fff;text-align:center;font-size:14px;letter-spacing:0;padding:11px 8px;}
.ard-report-table td{font-size:13px;color:#233a5c;vertical-align:middle;}
.ard-report-table .ard-total-row td{background:#eaf1fb;font-weight:700;}
.ard-right{text-align:right;font-family:Arial,sans-serif;}
.ard-vendor-card .v,.ard-bs-metric-head span:last-child,.ard-bs-net{font-family:Arial,sans-serif;}
.ard-empty{text-align:center;color:#8194ad;padding:20px 12px !important;}
.ard-balance-analysis-wrap{margin:12px 12px 14px;}
.ard-balance-analysis-toggle{display:block;width:100%;text-align:left;border:1px solid #cddbef;background:#f7faff;color:#1f3556;font-weight:700;}
.ard-balance-analysis-toggle .fa-chevron-down{transition:transform .18s ease;}
.ard-balance-analysis-toggle:not(.collapsed) .fa-chevron-down{transform:rotate(180deg);}
.ard-balance-analysis-card{border:1px solid #d8e4f4;border-radius:10px;background:#fbfdff;padding:14px;margin-top:10px;display:flex;align-items:center;gap:18px;flex-wrap:wrap;}
.ard-balance-analysis-chart{flex:0 0 220px;display:flex;justify-content:center;}
.ard-bs-donut{width:200px;height:200px;border-radius:50%;position:relative;background:#d9e2ef;}
.ard-bs-donut-inner{position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);width:120px;height:120px;border-radius:50%;background:#fff;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;border:1px solid #deebfb;}
.ard-bs-donut-inner strong{font-size:20px;color:#1d3d64;line-height:1.1;}
.ard-bs-donut-inner span{font-size:11px;color:#5b7292;font-weight:600;line-height:1.2;}
.ard-balance-analysis-meta{flex:1 1 330px;}
.ard-bs-metric{margin-bottom:10px;padding-bottom:10px;border-bottom:1px solid #e3ecf8;}
.ard-bs-metric:last-child{margin-bottom:0;padding-bottom:0;border-bottom:none;}
.ard-bs-metric-head{display:flex;justify-content:space-between;align-items:center;gap:8px;font-size:13px;color:#203b5f;font-weight:700;}
.ard-bs-progress{height:8px;background:#e8eef7;border-radius:30px;overflow:hidden;margin-top:7px;}
.ard-bs-progress > span{display:block;height:100%;}
.ard-bs-income span{background:#2ea66f;}
.ard-bs-expense span{background:#e05b5b;}
.ard-bs-net{font-size:14px;color:#1d3b61;font-weight:700;}
.ard-vendor-cards{display:flex;flex-wrap:wrap;gap:12px;padding:12px 18px;border-bottom:1px solid #e2eaf6;background:#f9fbff;}
.ard-vendor-card{flex:1 1 220px;border:1px solid #d7e4f6;border-radius:10px;background:#fff;padding:10px 12px;}
.ard-vendor-card .k{display:block;font-size:12px;font-weight:700;color:#607b9d;text-transform:uppercase;letter-spacing:.04em;margin-bottom:6px;}
.ard-vendor-card .v{display:block;font-size:22px;font-weight:700;color:#1f3a60;line-height:1.2;}
.is-pdf-export .ard-print-header{display:block !important;margin:0 0 12px 0;padding:0 0 10px 0;border-bottom:1px solid #d7e1ef;}
.is-pdf-export .ard-report-tabs,
.is-pdf-export .ard-report-filter,
.is-pdf-export .ard-top-actions{
    display:none !important;
}
.is-pdf-export .ard-print-filter-meta{display:flex !important;justify-content:space-between;align-items:center;gap:8px;padding:7px 10px;border:1px solid #d6e1f0;border-radius:8px;background:#f7faff;margin:0 0 10px 0;font-size:12px;color:#355070;}
.is-pdf-export .ard-report-head{display:block !important;padding:0 0 8px 0;border-bottom:1px solid #ddd !important;margin-bottom:10px;text-align:center;}
.is-pdf-export .ard-report-title{text-align:center !important;}
.is-pdf-export .ard-report-actions{display:none !important;}
@media (max-width: 991px){
    .ard-report-filter{flex-wrap:wrap;overflow-x:visible;}
    .ard-report-filter .form-control{width:100%;min-width:100%;flex:1 1 100%;}
    .ard-report-filter .btn{width:100%;flex:1 1 100%;}
    .ard-balance-analysis-chart{flex:1 1 100%;}
}
@media print{
    .navbar,#sidebar-main,.panel-heading,.ard-report-tabs,.ard-report-filter,.ard-top-actions,.ard-report-actions,.page-title{display:none !important;}
    .ard-balance-analysis-wrap{display:none !important;}
    .page-content,.content-wrapper,.content,.panel,.panel-body,.ard-reports-wrap,.ard-report-panel{background:#fff !important;border:none !important;box-shadow:none !important;padding:0 !important;margin:0 !important;}
    .ard-report-head{display:block !important;padding:0 0 8px 0;border-bottom:1px solid #ddd !important;margin-bottom:10px;text-align:center;}
    .ard-report-title{text-align:center !important;}
    .ard-print-header{display:block !important;margin:0 0 12px 0;padding:0 0 10px 0;border-bottom:1px solid #d7e1ef;}
    .ard-print-filter-meta{display:flex !important;justify-content:space-between;align-items:center;gap:8px;padding:7px 10px;border:1px solid #d6e1f0;border-radius:8px;background:#f7faff;margin:0 0 10px 0;font-size:12px;color:#355070;}
    .ard-report-table .ard-total-row td{background:#eaf1fb !important;font-weight:700 !important;-webkit-print-color-adjust:exact;print-color-adjust:exact;}
}
</style>

<div class="page-title">
    <div class="title-env">
        <h1 class="title"><?php echo __('Reports Center');?></h1>
        <p class="description"><?php echo __('Accounts, stock, construction, vendor, payroll and KPI reporting');?></p>
    </div>
</div>

<div class="panel">
    <div class="panel-heading ard-top-actions">
        <div class="btn-group">
            <?php
            echo $this->Html->link('<span class="fa fa-plus"></span> '.__('Add Income'),array('controller'=>'Ardaccounts','action'=>'transactions',$currentProjectId,'?'=>array('kind'=>'income')),array('escape'=>false,'class'=>'btn btn-success'));
            echo $this->Html->link('<span class="fa fa-minus"></span> '.__('Add Expense'),array('controller'=>'Ardaccounts','action'=>'transactions',$currentProjectId,'?'=>array('kind'=>'expense')),array('escape'=>false,'class'=>'btn btn-danger'));
            echo $this->Html->link('<span class="fa fa-exchange"></span> '.__('Journal Voucher'),array('controller'=>'Ardaccounts','action'=>'transfer',$currentProjectId),array('escape'=>false,'class'=>'btn btn-primary'));
            ?>
        </div>
    </div>
    <div class="panel-body ard-reports-wrap">
        <?php echo $this->Session->flash();?>

        <div id="ard-report-export-target">
        <div class="ard-print-header">
            <div class="ard-print-head-row">
                <?php if($printAgencyLogo !== ''){?>
                <div class="ard-print-logo"><?php echo $this->Html->image($printAgencyLogo, array('alt' => $printAgencyName));?></div>
                <?php }?>
                <div class="ard-print-meta">
                    <h2><?php echo h($printAgencyName);?></h2>
                    <?php if($printAgencyAddress !== ''){?><p><?php echo h($printAgencyAddress);?></p><?php }?>
                    <?php if($printContactLine !== ''){?><p><?php echo h($printContactLine);?></p><?php }?>
                </div>
            </div>
        </div>
        <div class="ard-print-filter-meta">
            <div><?php echo h($selectedFilterText);?></div>
            <div><?php echo __('Print Date');?>: <?php echo h($printDateTime);?></div>
        </div>

        <div class="ard-report-tabs">
            <?php foreach($reportTabs as $tabItem):
                $tabId = (string)$tabItem['id'];
                $tabLabel = (string)$tabItem['label'];
                $tabClass = ($tabId === $activeReportTab) ? 'active' : '';
                $tabQuery = $baseQuery;
                $tabQuery['tab'] = $tabId;
                if($tabId === 'daily' && $activeReportTab !== 'daily')
                {
                    $tabQuery['preset'] = 'daily';
                    $tabQuery['start'] = '';
                    $tabQuery['end'] = '';
                }
            ?>
                <?php echo $this->Html->link(
                    h($tabLabel),
                    array('controller' => 'Ardaccounts', 'action' => 'reports', $currentProjectId, '?' => $tabQuery),
                    array('class' => $tabClass)
                );?>
            <?php endforeach;?>
        </div>

        <?php echo $this->Form->create(false,array('type'=>'get','url'=>array('controller'=>'Ardaccounts','action'=>'reports',$currentProjectId),'class'=>'ard-report-filter'));?>
            <input type="hidden" name="tab" value="<?php echo h($activeReportTab);?>">
            <select name="preset" class="form-control js-report-auto">
                <option value="daily" <?php echo ($reportPreset==='daily')?'selected':'';?>><?php echo __('Daily');?></option>
                <option value="weekly" <?php echo ($reportPreset==='weekly')?'selected':'';?>><?php echo __('Weekly');?></option>
                <option value="monthly" <?php echo ($reportPreset==='monthly')?'selected':'';?>><?php echo __('Monthly');?></option>
                <option value="yearly" <?php echo ($reportPreset==='yearly')?'selected':'';?>><?php echo __('Yearly');?></option>
                <option value="custom" <?php echo ($reportPreset==='custom')?'selected':'';?>><?php echo __('Custom');?></option>
                <option value="lifetime" <?php echo ($reportPreset==='lifetime')?'selected':'';?>><?php echo __('Lifetime');?></option>
            </select>
            <input type="text" name="start" id="ard_report_start_date" value="<?php echo h($reportStartDisplay);?>" class="form-control" placeholder="<?php echo h($reportStartPlaceholder);?>" <?php echo $isCustomReportRange ? '' : 'readonly="readonly"';?> />
            <input type="text" name="end" id="ard_report_end_date" value="<?php echo h($reportEndDisplay);?>" class="form-control" placeholder="<?php echo h($reportEndPlaceholder);?>" <?php echo $isCustomReportRange ? '' : 'readonly="readonly"';?> />
            <?php if($activeReportTab === 'vendor_ledger'){?>
            <select name="vendor_id" class="form-control">
                <option value=""><?php echo __('All Vendors');?></option>
                <?php foreach((array)$reportVendorList as $vendorId => $vendorLabel){?>
                <option value="<?php echo (int)$vendorId;?>" <?php echo ((int)$selectedVendorId === (int)$vendorId) ? 'selected' : '';?>><?php echo h($vendorLabel);?></option>
                <?php }?>
            </select>
            <?php }?>
            <?php if($userType=='ADR' || !empty($isAgencySystemManager)){?>
            <select name="project_id" class="form-control js-report-auto">
                <option value=""><?php echo !empty($isAgencySystemManager) ? __('All Assigned Agencies') : __('All Agencies');?></option>
                <?php foreach((array)$projectName as $projectId => $projectLabel){?>
                <option value="<?php echo (int)$projectId;?>" <?php echo ((int)$currentProjectId === (int)$projectId) ? 'selected' : '';?>><?php echo h($projectLabel);?></option>
                <?php }?>
            </select>
            <?php }?>
            <button type="submit" class="btn btn-default"><?php echo __('Apply');?></button>
        <?php echo $this->Form->end();?>

        <div class="ard-report-panel">
            <div class="ard-report-head">
                <div class="ard-report-title"><?php echo h($reportData['title']);?></div>
                <div class="ard-report-actions">
                    <button type="button" class="btn btn-primary" onclick="window.print();"><?php echo __('Print');?></button>
                    <button type="button" class="btn btn-danger" onclick="downloadReportPdf();"><?php echo __('Download PDF');?></button>
                    <?php echo $this->Html->link(__('Export CSV'),array('controller'=>'Ardaccounts','action'=>'reports',$currentProjectId,'?'=>$exportQuery),array('class'=>'btn btn-default ard-report-export'));?>
                </div>
            </div>
            <?php if($activeReportTab === 'vendor_ledger' && $selectedVendorId > 0 && $selectedVendorName !== ''){?>
            <div style="padding:8px 18px;border-bottom:1px solid #e2eaf6;font-size:13px;color:#415d81;">
                <strong><?php echo __('Vendor');?>:</strong> <?php echo h($selectedVendorName);?>
            </div>
            <?php }?>
            <?php if($activeReportTab === 'vendor_ledger'){?>
            <div class="ard-vendor-cards">
                <div class="ard-vendor-card">
                    <span class="k"><?php echo __('Credit Material');?></span>
                    <span class="v"><?php echo h($currencyText.' '.number_format($vendorLedgerCredit, 2));?></span>
                </div>
                <div class="ard-vendor-card">
                    <span class="k"><?php echo __('Debit Paid');?></span>
                    <span class="v"><?php echo h($currencyText.' '.number_format($vendorLedgerDebit, 2));?></span>
                </div>
                <div class="ard-vendor-card">
                    <span class="k"><?php echo __('Balance');?></span>
                    <span class="v"><?php echo h($currencyText.' '.number_format($vendorLedgerBalance, 2));?></span>
                </div>
            </div>
            <?php }?>
            <div class="table-responsive">
                <table class="table table-bordered table-striped ard-report-table">
                    <thead>
                        <?php if(!empty($reportData['columnGroups'])){?>
                        <tr class="ard-column-group">
                            <?php foreach((array)$reportData['columnGroups'] as $group){?>
                            <th colspan="<?php echo max(1, (int)(isset($group['colspan']) ? $group['colspan'] : 1));?>"><?php echo h(isset($group['label']) ? $group['label'] : '');?></th>
                            <?php }?>
                        </tr>
                        <?php }?>
                        <tr>
                            <?php foreach((array)$reportData['columns'] as $idx => $col):?>
                                <th class="<?php echo in_array($idx,$rightCols)?'ard-right':'';?>"><?php echo h($col);?></th>
                            <?php endforeach;?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($reportData['rows'])){?>
                            <tr><td colspan="<?php echo max(1,count((array)$reportData['columns']));?>" class="ard-empty"><?php echo __('No records in selected range.');?></td></tr>
                        <?php }else{foreach((array)$reportData['rows'] as $row){
                            $leadCell = isset($row[0]) ? strtolower(trim(preg_replace('/\s+/', ' ', strip_tags((string)$row[0])))) : '';
                            $isTotalRow = ($leadCell !== '' && preg_match('/\btotal\b/i', $leadCell));
                        ?>
                            <tr class="<?php echo $isTotalRow ? 'ard-total-row' : '';?>">
                                <?php foreach((array)$row as $idx => $cell){?>
                                    <td class="<?php echo in_array($idx,$rightCols)?'ard-right':'';?>"><?php echo in_array($idx, $rawCols, true) ? $cell : h($cell);?></td>
                                <?php }?>
                            </tr>
                        <?php }}?>
                    </tbody>
                </table>
            </div>
            <?php if($activeReportTab === 'balance'){?>
            <div class="ard-balance-analysis-wrap">
                <button type="button" class="btn ard-balance-analysis-toggle collapsed" data-toggle="collapse" data-target="#balanceSheetAnalysis" aria-expanded="false" aria-controls="balanceSheetAnalysis">
                    <span class="fa fa-pie-chart"></span> <?php echo __('Balance Sheet Analysis');?>
                    <span class="pull-right fa fa-chevron-down"></span>
                </button>
                <div id="balanceSheetAnalysis" class="collapse">
                    <div class="ard-balance-analysis-card">
                        <div class="ard-balance-analysis-chart">
                            <?php
                            $donutStyle = ($bsTotalBase > 0)
                                ? 'background:conic-gradient(#2ea66f 0 '.$bsDonutIncomeEdge.'%, #e05b5b '.$bsDonutIncomeEdge.'% 100%);'
                                : 'background:#d9e2ef;';
                            ?>
                            <div class="ard-bs-donut" style="<?php echo h($donutStyle);?>">
                                <div class="ard-bs-donut-inner">
                                    <strong><?php echo h(number_format($bsIncomePercent, 2));?>%</strong>
                                    <span><?php echo __('Income Share');?></span>
                                </div>
                            </div>
                        </div>
                        <div class="ard-balance-analysis-meta">
                            <div class="ard-bs-metric ard-bs-income">
                                <div class="ard-bs-metric-head">
                                    <span><?php echo __('Income');?></span>
                                    <span><?php echo h($currencyText.' '.number_format($bsIncomeTotal, 2));?> (<?php echo h(number_format($bsIncomePercent, 2));?>%)</span>
                                </div>
                                <div class="ard-bs-progress"><span style="width:<?php echo h($bsIncomePercent);?>%;"></span></div>
                            </div>
                            <div class="ard-bs-metric ard-bs-expense">
                                <div class="ard-bs-metric-head">
                                    <span><?php echo __('Expenses & Payables');?></span>
                                    <span><?php echo h($currencyText.' '.number_format($bsExpenseTotal, 2));?> (<?php echo h(number_format($bsExpensePercent, 2));?>%)</span>
                                </div>
                                <div class="ard-bs-progress"><span style="width:<?php echo h($bsExpensePercent);?>%;"></span></div>
                            </div>
                            <div class="ard-bs-net">
                                <?php echo __('Net Business Position');?>: <?php echo h($currencyText.' '.number_format($bsNetBalance, 2));?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php }?>
        </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script type="text/javascript">
jQuery(function($){
    var $reportFilterForm = $('.ard-report-filter');
    var $preset = $reportFilterForm.find('select[name="preset"]');
    var $startDate = $('#ard_report_start_date');
    var $endDate = $('#ard_report_end_date');
    function useCustomDateRange()
    {
        $preset.val('custom');
    }
    function syncDateFieldState()
    {
        var preset = $preset.val();
        var isCustom = (preset === 'custom');
        var isLifetime = (preset === 'lifetime');
        $startDate.prop('readonly', !isCustom).attr('placeholder', isLifetime ? '<?php echo h(addslashes(__('All dates')));?>' : '<?php echo h(addslashes(__('Start date')));?>');
        $endDate.prop('readonly', !isCustom).attr('placeholder', isLifetime ? '<?php echo h(addslashes(__('All dates')));?>' : '<?php echo h(addslashes(__('End date')));?>');
        if(isLifetime)
        {
            $startDate.val('');
            $endDate.val('');
        }
    }
    $reportFilterForm.on('change', 'select.js-report-auto', function(){
        if(this.name === 'preset')
        {
            syncDateFieldState();
        }
        $reportFilterForm.trigger('submit');
    });
    $reportFilterForm.on('change', '#ard_report_start_date, #ard_report_end_date', useCustomDateRange);
    if($.fn.datetimepicker)
    {
        if($('#ard_report_start_date').length)
        {
            $('#ard_report_start_date').datetimepicker({locale:'<?php echo $configLanguage;?>',format:'DD-MMM-YYYY'});
        }
        if($('#ard_report_end_date').length)
        {
            $('#ard_report_end_date').datetimepicker({locale:'<?php echo $configLanguage;?>',format:'DD-MMM-YYYY',useCurrent:false});
        }
        $('#ard_report_start_date').on('dp.change', function (e) {
            useCustomDateRange();
            if($('#ard_report_end_date').data('DateTimePicker'))
            {
                $('#ard_report_end_date').data('DateTimePicker').minDate(e.date);
            }
        });
        $('#ard_report_end_date').on('dp.change', function (e) {
            useCustomDateRange();
            if($('#ard_report_start_date').data('DateTimePicker'))
            {
                $('#ard_report_start_date').data('DateTimePicker').maxDate(e.date);
            }
        });
    }
    syncDateFieldState();
});

function reportPdfStamp()
{
    var d = new Date();
    function p(n){ return (n < 10 ? '0' : '') + n; }
    return d.getFullYear().toString() + p(d.getMonth()+1) + p(d.getDate()) + '_' + p(d.getHours()) + p(d.getMinutes()) + p(d.getSeconds());
}

function downloadReportPdf()
{
    var target = document.getElementById('ard-report-export-target');
    if(!target || typeof html2pdf === 'undefined')
    {
        return false;
    }
    target.classList.add('is-pdf-export');
    var reportTitle = "<?php echo addslashes((string)$reportData['title']);?>".replace(/[^a-z0-9_\-]+/gi,'_');
    if(reportTitle === '')
    {
        reportTitle = 'report';
    }
    var exportNode = target.cloneNode(true);
    var wrapper = document.createElement('div');
    wrapper.id = 'ard-report-pdf-wrapper';
    wrapper.style.position = 'fixed';
    wrapper.style.left = '0';
    wrapper.style.top = '0';
    wrapper.style.width = '1600px';
    wrapper.style.maxWidth = '1600px';
    wrapper.style.background = '#ffffff';
    wrapper.style.padding = '0';
    wrapper.style.opacity = '0.01';
    wrapper.style.pointerEvents = 'none';
    wrapper.style.zIndex = '-1';
    exportNode.style.width = '100%';
    wrapper.appendChild(exportNode);
    document.body.appendChild(wrapper);
    var options = {
        margin: [3, 3, 3, 3],
        filename: reportTitle + '_' + reportPdfStamp() + '.pdf',
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2, useCORS: true, scrollX: 0, scrollY: 0, windowWidth: 1600 },
        jsPDF: { unit: 'mm', format: 'a4', orientation: 'landscape' },
        pagebreak: { mode: ['css', 'legacy'] }
    };
    var cleanup = function(){
        if(wrapper && wrapper.parentNode){ wrapper.parentNode.removeChild(wrapper); }
        target.classList.remove('is-pdf-export');
    };
    html2pdf().set(options).from(exportNode).save().then(cleanup).catch(cleanup);
    return false;
}
</script>
