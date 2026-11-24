<?php
$historyRows = isset($historyRows) ? (array)$historyRows : array();
$changeStats = isset($changeStats) ? (array)$changeStats : array();
$statusStats = isset($statusStats) ? (array)$statusStats : array();
$agentStats = isset($agentStats) ? (array)$agentStats : array();
$agentProgressRows = isset($agentProgressRows) ? (array)$agentProgressRows : array();
$agentDateStats = isset($agentDateStats) ? (array)$agentDateStats : array();
$isLifetimeProgress = !empty($isLifetimeProgress);
$reportAgency = isset($reportAgency) ? (array)$reportAgency : array();
$showAgencyAgentColumns = !empty($showAgencyAgentColumns);
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
$maxChange = !empty($changeStats) ? max($changeStats) : 0;
$maxStatus = !empty($statusStats) ? max($statusStats) : 0;
$maxAgent = !empty($agentStats) ? max($agentStats) : 0;
$barWidth = function($value, $max){
    $max = (int)$max;
    if($max <= 0)
    {
        return 0;
    }
    return max(4, min(100, round(((int)$value / $max) * 100)));
};
$progressColorClass = function($percent){
    $percent = (float)$percent;
    if($percent >= 90)
    {
        return 'best';
    }
    if($percent > 80)
    {
        return 'high';
    }
    if($percent >= 50)
    {
        return 'mid';
    }
    if($percent < 30)
    {
        return 'low';
    }
    return 'watch';
};
?>
<style type="text/css">
.progress-report-surface {
    background: #f5f7fb;
    border: 1px solid #d9e2ef;
    padding: 16px;
}
.progress-filter-panel {
    border: 1px solid #d8e2ee;
    background: #fff;
    padding: 14px;
    margin-bottom: 14px;
    border-radius: 8px;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
}
.progress-filter-row {
    display: table;
    width: 100%;
}
.progress-filter-field {
    display: table-cell;
    padding: 0 6px 8px 0;
    vertical-align: bottom;
}
.progress-filter-field label {
    display: block;
    font-size: 12px;
    margin-bottom: 4px;
    color: #475569;
    font-weight: 700;
}
.progress-summary {
    margin: 2px 0 14px;
    color: #334155;
    background: #fff;
    border: 1px solid #d8e2ee;
    border-radius: 8px;
    padding: 10px 12px;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
}
.progress-summary strong {
    color: #0f172a;
}
.progress-chart-grid {
    margin-top: 0;
}
.progress-chart-card {
    border: 1px solid #d8e2ee;
    background: #fff;
    padding: 16px;
    margin-bottom: 14px;
    border-radius: 8px;
    box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
}
.progress-chart-card h4 {
    margin-top: 0;
    margin-bottom: 12px;
    color: #0f172a;
    font-size: 15px;
    font-weight: 800;
}
.progress-bar-row {
    margin-bottom: 12px;
}
.progress-bar-label {
    font-weight: 700;
    margin-bottom: 4px;
    color: #1e293b;
}
.progress-bar-track {
    background: #e8edf4;
    height: 22px;
    position: relative;
    border-radius: 999px;
    overflow: hidden;
}
.progress-bar-fill {
    background: #2563eb;
    height: 22px;
    color: #fff;
    font-size: 11px;
    line-height: 22px;
    padding-left: 8px;
    min-width: 34px;
    border-radius: 999px;
    font-weight: 800;
}
.progress-bar-fill.status { background: #059669; }
.progress-bar-fill.agent { background: #d97706; }
.progress-bar-fill.agent-progress-low { background: #dc2626; }
.progress-bar-fill.agent-progress-watch { background: #64748b; }
.progress-bar-fill.agent-progress-mid { background: #2563eb; }
.progress-bar-fill.agent-progress-high { background: #facc15; color: #111827; }
.progress-bar-fill.agent-progress-best { background: #16a34a; }
.progress-best-badge {
    display: inline-block;
    margin-left: 6px;
    padding: 2px 7px;
    border-radius: 12px;
    background: #16a34a;
    color: #fff;
    font-size: 10px;
    font-weight: 800;
    text-transform: uppercase;
}
.progress-bar-meta {
    font-size: 11px;
    color: #64748b;
    margin-top: 4px;
}
.progress-table-card {
    border: 1px solid #d8e2ee;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
    margin-bottom: 14px;
    overflow: hidden;
}
.progress-table-card .table,
.progress-chart-card .table {
    margin-bottom: 0;
}
.progress-table-card .table > thead > tr > th,
.progress-chart-card .table > thead > tr > th {
    background: #eef3f8;
    color: #0f172a;
    border-bottom: 1px solid #d8e2ee;
    font-size: 12px;
    text-transform: uppercase;
    font-weight: 800;
}
.progress-table-card .table > tbody > tr > td,
.progress-chart-card .table > tbody > tr > td {
    color: #334155;
    vertical-align: middle;
}
.progress-print-header {
    display: none;
    text-align: left;
    margin-bottom: 12px;
    border-bottom: 1px solid #d8e2ee;
    padding-bottom: 10px;
}
.progress-print-brand-row {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 14px;
    text-align: left;
}
.progress-print-logo {
    max-height: 70px;
    max-width: 160px;
    display: inline-block;
    flex: 0 0 auto;
}
.progress-print-header h2,
.progress-print-header h3 {
    margin: 0 0 5px 0;
}
.progress-print-header h3 {
    text-align: center;
    margin-top: 8px;
}
.progress-print-sub {
    font-size: 12px;
    margin-bottom: 4px;
}
@media print {
    .no-print { display: none !important; }
    .panel { border: 0; }
    .progress-chart-card { page-break-inside: avoid; }
}
@media(max-width: 991px) {
    .progress-filter-row,
    .progress-filter-field {
        display: block;
        width: 100%;
    }
}
</style>
<script type="text/javascript">
(function($){
    var progressPdfLoader = null;
    function ensureProgressPdfLib()
    {
        if(typeof html2pdf !== 'undefined')
        {
            return $.Deferred().resolve().promise();
        }
        if(progressPdfLoader)
        {
            return progressPdfLoader.promise();
        }
        progressPdfLoader = $.Deferred();
        var script = document.createElement('script');
        script.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js';
        script.onload = function(){ progressPdfLoader.resolve(); };
        script.onerror = function(){ progressPdfLoader.reject(); progressPdfLoader = null; };
        document.getElementsByTagName('head')[0].appendChild(script);
        return progressPdfLoader.promise();
    }
    window.exportLeadProgressPdf = function(){
        var source = document.getElementById('leadProgressPrintable');
        if(!source)
        {
            return false;
        }
        ensureProgressPdfLib().done(function(){
            var node = source.cloneNode(true);
            node.style.display = 'block';
            $(node).find('.progress-print-header').css('display','block');
            var opt = {
                margin: [8,8,8,8],
                filename: 'lead_progress_' + (new Date()).toISOString().replace(/[-:T]/g,'').slice(0,14) + '.pdf',
                image: {type:'jpeg', quality:0.98},
                html2canvas: {scale:2, useCORS:true},
                jsPDF: {unit:'mm', format:'a4', orientation:'landscape'}
            };
            html2pdf().set(opt).from(node).save();
        });
        return false;
    };
    $(document).on('change', '#LeadDailyprogressForm select, #LeadDailyprogressForm input[type="date"]', function(){
        $('#LeadDailyprogressForm').submit();
    });
})(jQuery);
</script>

<div class="page-title">
    <div class="title-env">
        <h1 class="title"><?php echo __('Leads Progress');?></h1>
    </div>
</div>
<div class="panel">
    <div class="panel-heading">
        <div class="btn-group">
            <?php echo $this->Html->link('<span class="fa fa-dashboard"></span>&nbsp;'.__('CRM Dashboard'),array('controller'=>'Leads','action'=>'dashboard'),array('escape'=>false,'class'=>'btn btn-default'));?>
            <?php echo $this->Html->link('<span class="fa fa-columns"></span>&nbsp;'.__('Kanban'),array('controller'=>'Leads','action'=>'kanban'),array('escape'=>false,'class'=>'btn btn-primary'));?>
            <?php echo $this->Html->link('<span class="fa fa-plus"></span>&nbsp;'.__('Add Lead Contact'),array('controller'=>'Leads','action'=>'leadadd'),array('escape'=>false,'class'=>'btn btn-success'));?>
            <?php echo $this->Html->link('<span class="fa fa-file-pdf-o"></span>&nbsp;'.__('Export PDF'),'#',array('escape'=>false,'class'=>'btn btn-info','onclick'=>'return exportLeadProgressPdf();'));?>
        </div>
    </div>
    <div class="panel-body">
        <?php echo $this->Session->flash();?>

        <div class="progress-filter-panel">
            <?php echo $this->Form->create(false, array('type'=>'get','url'=>array('controller'=>'Leads','action'=>'dailyprogress'),'id'=>'LeadDailyprogressForm', 'novalidate' => true));?>
            <div class="progress-filter-row">
                <?php if(!empty($canUseAgencyFilters)){ ?>
                <div class="progress-filter-field">
                    <label><?php echo __('Agency');?></label>
                    <?php echo $this->Form->select('project_id',(array)$projectOptions,array('empty'=>__('All Agencies'),'label'=>false,'div'=>false,'class'=>'form-control','required'=>false,'value'=>$selectedProjectId));?>
                </div>
                <?php } ?>
                <?php if(!empty($canUseAgentFilters)){ ?>
                <div class="progress-filter-field">
                    <label><?php echo __('Agent');?></label>
                    <?php echo $this->Form->select('user_id',(array)$agentOptions,array('empty'=>__('All Agents'),'label'=>false,'div'=>false,'class'=>'form-control','required'=>false,'value'=>$selectedAgentId));?>
                </div>
                <?php } ?>
                <div class="progress-filter-field">
                    <label><?php echo __('Period');?></label>
                    <?php echo $this->Form->select('period',(array)$periodOptions,array('label'=>false,'div'=>false,'class'=>'form-control','required'=>false,'value'=>$selectedPeriod));?>
                </div>
                <div class="progress-filter-field">
                    <label><?php echo __('Start Date');?></label>
                    <input type="date" name="start_date" value="<?php echo h($selectedStartDateInput);?>" class="form-control" required="false">
                </div>
                <div class="progress-filter-field">
                    <label><?php echo __('End Date');?></label>
                    <input type="date" name="end_date" value="<?php echo h($selectedEndDateInput);?>" class="form-control" required="false">
                </div>
                <div class="progress-filter-field">
                    <button type="submit" class="btn btn-success"><span class="fa fa-search"></span> <?php echo __('Search');?></button>
                    <?php echo $this->Html->link('<span class="fa fa-refresh"></span> '.__('Reset'),array('controller'=>'Leads','action'=>'dailyprogress'),array('escape'=>false,'class'=>'btn btn-default'));?>
                </div>
            </div>
            <?php echo $this->Form->end();?>
        </div>

        <div id="leadProgressPrintable" class="progress-report-surface">
        <div class="progress-print-header">
            <div class="progress-print-brand-row">
            <?php if($headerAgencyLogoUrl !== ''){ ?>
            <img src="<?php echo h($headerAgencyLogoUrl);?>" alt="<?php echo h($headerAgencyName);?>" class="progress-print-logo">
            <?php } ?>
            <div>
            <h2><?php echo h($headerAgencyName);?></h2>
            <?php if(trim((string)$headerAgencyAddress) !== ''){ ?><div class="progress-print-sub"><?php echo h($headerAgencyAddress);?></div><?php } ?>
            <div class="progress-print-sub">
                <?php
                $printContact = array();
                if(trim((string)$headerAgencyPhone) !== '')$printContact[] = __('Phone').': '.h($headerAgencyPhone);
                if(trim((string)$headerAgencyMobile) !== '')$printContact[] = __('Mobile').': '.h($headerAgencyMobile);
                if(trim((string)$headerAgencyEmail) !== '')$printContact[] = __('Email').': '.h($headerAgencyEmail);
                echo implode(' | ', $printContact);
                ?>
            </div>
            </div>
            </div>
            <h3><?php echo __('Leads Progress Report');?></h3>
        </div>

        <div class="progress-summary">
            <strong><?php echo __('Agency');?>:</strong> <?php echo h($selectedAgencyName);?>
            &nbsp; | &nbsp;<strong><?php echo __('Agent');?>:</strong> <?php echo h($selectedAgentName);?>
            &nbsp; | &nbsp;<strong><?php echo __('Date');?>:</strong> <?php echo h($dateRangeLabel);?>
            &nbsp; | &nbsp;<strong><?php echo __('Changes');?>:</strong> <?php echo count($historyRows);?>
        </div>

        <div class="row progress-chart-grid">
            <div class="col-md-4">
                <div class="progress-chart-card">
                    <h4><?php echo __('Lead Status Changes');?></h4>
                    <?php foreach($statusStats as $label => $count){ ?>
                    <div class="progress-bar-row">
                        <div class="progress-bar-label"><?php echo h($label);?></div>
                        <div class="progress-bar-track"><div class="progress-bar-fill status" style="width:<?php echo $barWidth($count,$maxStatus);?>%;"><?php echo (int)$count;?></div></div>
                    </div>
                    <?php } ?>
                    <?php if(empty($statusStats)){ ?><p><?php echo __('No status changes found.');?></p><?php } ?>
                </div>
            </div>
            <div class="col-md-4">
                <div class="progress-chart-card">
                    <h4><?php echo __('Other Change Types');?></h4>
                    <?php foreach($changeStats as $label => $count){ ?>
                    <div class="progress-bar-row">
                        <div class="progress-bar-label"><?php echo h($label);?></div>
                        <div class="progress-bar-track"><div class="progress-bar-fill" style="width:<?php echo $barWidth($count,$maxChange);?>%;"><?php echo (int)$count;?></div></div>
                    </div>
                    <?php } ?>
                    <?php if(empty($changeStats)){ ?><p><?php echo __('No changes found.');?></p><?php } ?>
                </div>
            </div>
            <div class="col-md-4">
                <div class="progress-chart-card">
                    <h4><?php echo __('Agent Progress');?></h4>
                    <p class="progress-bar-meta"><?php echo $isLifetimeProgress ? __('Progress is based on all assigned leads with any follow-up date change.') : __('Progress is based on follow-ups due in the selected date filter whose follow-up date was changed.');?></p>
                    <?php foreach($agentProgressRows as $progressRow){
                        $progressPercent = isset($progressRow['percent']) ? (float)$progressRow['percent'] : 0;
                        $progressClass = $progressColorClass($progressPercent);
                        $progressWidth = max(4, min(100, round($progressPercent)));
                    ?>
                    <div class="progress-bar-row">
                        <div class="progress-bar-label">
                            <?php echo h($progressRow['agent_name']);?>
                            <?php if($progressPercent >= 90){ ?><span class="progress-best-badge"><?php echo __('Best');?></span><?php } ?>
                        </div>
                        <div class="progress-bar-track"><div class="progress-bar-fill agent-progress-<?php echo h($progressClass);?>" style="width:<?php echo $progressWidth;?>%;"><?php echo round($progressPercent);?>%</div></div>
                        <div class="progress-bar-meta">
                            <?php echo __('Follow-up changed leads');?>: <?php echo (int)$progressRow['progressed_leads'];?>
                            / <?php echo $isLifetimeProgress ? __('Total leads') : __('Due follow-ups');?>: <?php echo (int)$progressRow['total_leads'];?>
                        </div>
                    </div>
                    <?php } ?>
                    <?php if(empty($agentProgressRows)){ ?><p><?php echo __('No agent progress found.');?></p><?php } ?>
                </div>
            </div>
        </div>

        <div class="progress-table-card table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th><?php echo __('#');?></th>
                        <th><?php echo __('Date');?></th>
                        <?php if($showAgencyAgentColumns){ ?>
                        <th><?php echo __('Agency');?></th>
                        <th><?php echo __('Agent');?></th>
                        <?php } ?>
                        <th><?php echo __('Lead');?></th>
                        <th><?php echo __('Mobile No');?></th>
                        <th><?php echo __('Follow Up');?></th>
                        <th><?php echo __('Changed By');?></th>
                        <th><?php echo __('Change');?></th>
                        <th><?php echo __('Old');?></th>
                        <th><?php echo __('New');?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $sr = 1; foreach($historyRows as $row){
                        $history = isset($row['LeadHistory']) ? (array)$row['LeadHistory'] : array();
                        $lead = isset($row['Lead']) ? (array)$row['Lead'] : array();
                        $leadName = !empty($lead['lead_name']) ? $lead['lead_name'] : ('#'.(isset($history['lead_id']) ? $history['lead_id'] : ''));
                        $mobileNo = !empty($lead['lead_mobile']) ? trim((string)$lead['lead_mobile']) : (!empty($row['Client']['mobile']) ? trim((string)$row['Client']['mobile']) : (!empty($row['Client']['phone']) ? trim((string)$row['Client']['phone']) : ''));
                        $callNo = preg_replace('/[^0-9\+]/', '', $mobileNo);
                        $followUpText = (!empty($lead['follow_up']) && $lead['follow_up'] !== '0000-00-00 00:00:00') ? $this->Time->format($sysDay.$dateSep.$sysMonth.$dateSep.$sysYear.$dateGap.$sysHour.$timeSep.$sysMin.$dateGap.$sysMer,$lead['follow_up']) : '-';
                        $fieldLabel = !empty($history['field_label']) ? $history['field_label'] : (!empty($history['title']) ? $history['title'] : __('Other'));
                    ?>
                    <tr>
                        <td><?php echo $sr++;?></td>
                        <td><?php echo !empty($history['created']) ? $this->Time->format($sysDay.$dateSep.$sysMonth.$dateSep.$sysYear.$dateGap.$sysHour.$timeSep.$sysMin.$dateGap.$sysMer,$history['created']) : '-';?></td>
                        <?php if($showAgencyAgentColumns){ ?>
                        <td><?php echo !empty($row['Project']['name']) ? h($row['Project']['name']) : '-';?></td>
                        <td><?php echo !empty($row['AssignedUser']['name']) ? h($row['AssignedUser']['name']) : '-';?></td>
                        <?php } ?>
                        <td><?php echo h($leadName);?></td>
                        <td><?php echo ($mobileNo !== '' && $callNo !== '') ? $this->Html->link(h($mobileNo),'tel:'.$callNo,array('escape'=>false)) : ($mobileNo !== '' ? h($mobileNo) : '-');?></td>
                        <td><?php echo h($followUpText);?></td>
                        <td><?php echo !empty($history['actor_name']) ? h($history['actor_name']) : '-';?></td>
                        <td><?php echo h($fieldLabel);?></td>
                        <td><?php echo trim((string)(isset($history['old_value']) ? $history['old_value'] : '')) !== '' ? h($history['old_value']) : '-';?></td>
                        <td><?php echo trim((string)(isset($history['new_value']) ? $history['new_value'] : '')) !== '' ? h($history['new_value']) : '-';?></td>
                    </tr>
                    <?php } ?>
                    <?php if(empty($historyRows)){ ?>
                    <tr><td colspan="<?php echo $showAgencyAgentColumns ? 11 : 9;?>" class="text-center"><?php echo __('No progress records found.');?></td></tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <div class="progress-chart-card">
            <h4><?php echo __('Agent Follow-up / Change Performance By Date');?></h4>
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th><?php echo __('Agent');?></th>
                            <th><?php echo __('Date');?></th>
                            <th><?php echo __('Total Changes');?></th>
                            <th><?php echo __('Status Changes');?></th>
                            <th><?php echo __('Follow-up / Contact Date');?></th>
                            <th><?php echo __('Remarks');?></th>
                            <th><?php echo __('Contact Info');?></th>
                            <th><?php echo __('Other');?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($agentDateStats as $agentName => $dateRows){ ?>
                            <?php foreach((array)$dateRows as $changeDate => $statRow){ ?>
                            <tr>
                                <td><?php echo h($agentName);?></td>
                                <td><?php echo h($changeDate);?></td>
                                <td><?php echo (int)$statRow['total'];?></td>
                                <td><?php echo (int)$statRow['status'];?></td>
                                <td><?php echo (int)$statRow['follow_up'];?></td>
                                <td><?php echo (int)$statRow['remarks'];?></td>
                                <td><?php echo (int)$statRow['contact'];?></td>
                                <td><?php echo (int)$statRow['other'];?></td>
                            </tr>
                            <?php } ?>
                        <?php } ?>
                        <?php if(empty($agentDateStats)){ ?>
                        <tr><td colspan="8" class="text-center"><?php echo __('No agent performance found.');?></td></tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
        </div>
    </div>
</div>
