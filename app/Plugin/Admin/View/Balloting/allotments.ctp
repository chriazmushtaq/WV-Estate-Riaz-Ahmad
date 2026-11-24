<?php
$this->Js->JqueryEngine->jQueryObject = 'jQuery';
$this->Paginator->options(array(
    'update' => '#resultDiv',
    'evalScripts' => true,
));
$startDateInput = isset($startDateInput) ? (string)$startDateInput : '';
$endDateInput = isset($endDateInput) ? (string)$endDateInput : '';
$reportAgency = isset($reportAgency) ? (array)$reportAgency : array();
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
$selectedAgencyLabel = (!empty($projectId) && !empty($projectName[$projectId])) ? $projectName[$projectId] : __('All Agency');
$allotmentsUrl = array('action' => 'allotments');
if((int)$dealId > 0)
{
    $allotmentsUrl[] = (int)$dealId;
}
$queryBase = array();
if((int)$projectId > 0)$queryBase['project_id'] = (int)$projectId;
if((int)$dealId > 0)$queryBase['deal_id'] = (int)$dealId;
if(trim((string)$keyword) !== '')$queryBase['keyword'] = trim((string)$keyword);
if(trim((string)$status) !== '')$queryBase['status'] = trim((string)$status);
if(trim((string)$startDateInput) !== '')$queryBase['start_date'] = trim((string)$startDateInput);
if(trim((string)$endDateInput) !== '')$queryBase['end_date'] = trim((string)$endDateInput);
$csvUrl = array('action' => 'allotments');
if((int)$dealId > 0)
{
    $csvUrl[] = (int)$dealId;
}
$csvUrl['?'] = array_merge($queryBase, array('export' => 'csv'));
?>
<script type="text/javascript">
jQuery(document).ready(function(){
    if(jQuery('#allotment_start_date').length && jQuery('#allotment_end_date').length)
    {
        jQuery('#allotment_start_date').datetimepicker({locale:'<?php echo $configLanguage;?>',format:'<?php echo $dpFormat;?>'});
        jQuery('#allotment_end_date').datetimepicker({locale:'<?php echo $configLanguage;?>',format:'<?php echo $dpFormat;?>',useCurrent:false});
        jQuery("#allotment_start_date").on("dp.change", function (e) {
            jQuery('#allotment_end_date').data("DateTimePicker").minDate(e.date);
        });
        jQuery("#allotment_end_date").on("dp.change", function (e) {
            jQuery('#allotment_start_date').data("DateTimePicker").maxDate(e.date);
        });
    }
});
var ballotingAllotmentsPdfLoader = null;
function ensureBallotingAllotmentsPdf()
{
    if(typeof html2pdf !== 'undefined')
    {
        return jQuery.Deferred().resolve().promise();
    }
    if(ballotingAllotmentsPdfLoader)
    {
        return ballotingAllotmentsPdfLoader.promise();
    }
    ballotingAllotmentsPdfLoader = jQuery.Deferred();
    var script = document.createElement('script');
    script.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js';
    script.onload = function(){ ballotingAllotmentsPdfLoader.resolve(); };
    script.onerror = function(){
        alert('Unable to load PDF library.');
        ballotingAllotmentsPdfLoader.reject();
        ballotingAllotmentsPdfLoader = null;
    };
    document.getElementsByTagName('head')[0].appendChild(script);
    return ballotingAllotmentsPdfLoader.promise();
}
function ballotingAllotmentsPdfStamp()
{
    var now = new Date();
    var pad = function(n){ return n < 10 ? '0' + n : n; };
    return now.getFullYear() + '' + pad(now.getMonth() + 1) + '' + pad(now.getDate()) + '_' + pad(now.getHours()) + '' + pad(now.getMinutes()) + '' + pad(now.getSeconds());
}
function exportBallotingAllotmentsPdf()
{
    ensureBallotingAllotmentsPdf().done(function(){
        var sourceElement = document.getElementById('allotments_printable');
        if(!sourceElement)
        {
            alert('No records found for PDF export.');
            return;
        }
        var exportNode = sourceElement.cloneNode(true);
        var $exportNode = jQuery(exportNode);
        $exportNode.find('.no-print').remove();
        var $wrapper = jQuery('<div id="balloting-allotments-pdf-export-wrapper"></div>').css({
            position: 'fixed',
            left: '0',
            top: '0',
            width: '1400px',
            maxWidth: '1400px',
            background: '#fff',
            padding: '8px',
            opacity: '0.01',
            pointerEvents: 'none',
            zIndex: '-1'
        });
        $wrapper.append($exportNode);
        jQuery('body').append($wrapper);
        var opt = {
            margin: [0.20,0.20,0.20,0.20],
            filename: 'balloting_allotments_' + ballotingAllotmentsPdfStamp() + '.pdf',
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { scale: 2, useCORS: true },
            jsPDF: { unit: 'in', format: 'a4', orientation: 'landscape' }
        };
        var worker = html2pdf().set(opt).from($exportNode[0]).save();
        var cleanup = function(){ $wrapper.remove(); };
        if(worker && typeof worker.then === 'function')
        {
            worker.then(function(){ cleanup(); }).catch(function(){ cleanup(); });
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
@page {
    size: landscape;
    margin: 8mm;
}
.balloting-allotments-brand-header {
    width: 100%;
    border-bottom: 1px solid #e5e5e5;
    margin-bottom: 10px;
}
.balloting-allotments-brand-logo-cell {
    width: 180px;
    vertical-align: middle;
}
.balloting-allotments-brand-logo-cell img {
    max-height: 90px;
    width: auto;
}
.balloting-allotments-brand-details {
    vertical-align: middle;
    text-align: center;
}
.balloting-allotments-brand-details h3 {
    margin: 0 0 4px 0;
}
.balloting-allotments-filter-summary {
    text-align: center;
    margin-bottom: 10px;
    font-size: 12px;
}
.balloting-allotments-table th {
    background: #f7f9fb;
    font-weight: 600;
}
@media print {
    .no-print {
        display: none !important;
    }
    #allotments_printable {
        display: block !important;
    }
}
</style>
<div id="resultDiv">
    <div class="page-title">
        <div class="title-env">
            <h1 class="title"><?php echo __('Balloting Allotments');?></h1>
        </div>
    </div>

    <div class="panel">
        <div class="panel-heading clearfix no-print">
            <div class="pull-left" style="margin-bottom:8px;">
                <?php echo $this->Html->link('<span class="fa fa-dashboard"></span>&nbsp;'.__('Dashboard'), array('action' => 'dashboard', '?' => array('project_id' => $projectId)), array('escape' => false, 'class' => 'btn btn-primary'));?>
                <?php echo $this->Html->link('<span class="fa fa-hand-paper-o"></span>&nbsp;'.__('Manual Allotment'), array('action' => 'manualallotment', '?' => array('project_id' => $projectId)), array('escape' => false, 'class' => 'btn btn-warning'));?>
                <?php echo $this->Html->link('<span class="fa fa-random"></span>&nbsp;'.__('Run Balloting'), array('action' => 'run', '?' => array('project_id' => $projectId)), array('escape' => false, 'class' => 'btn btn-success'));?>
            </div>
            <div class="pull-right" style="margin-bottom:8px;">
                <button type="button" class="btn btn-default" onclick="window.print();"><span class="fa fa-print"></span>&nbsp;<?php echo __('Print');?></button>
                <button type="button" class="btn btn-danger" onclick="return exportBallotingAllotmentsPdf();"><span class="fa fa-file-pdf-o"></span>&nbsp;<?php echo __('Export PDF');?></button>
                <?php echo $this->Html->link('<span class="fa fa-file-excel-o"></span>&nbsp;'.__('Export CSV'), $csvUrl, array('escape' => false, 'class' => 'btn btn-info'));?>
            </div>
        </div>
        <div class="panel-body">
            <?php echo $this->Session->flash();?>
            <div class="no-print">
                <?php echo $this->Form->create(false, array('type' => 'get', 'url' => $allotmentsUrl, 'class' => 'form-inline'));?>
                <div class="form-group">
                    <?php if(!empty($canUseAgencyFilters)){?>
                    <?php echo $this->Form->select('project_id', $projectName, array('empty' => __('All Agency'), 'value' => $projectId, 'label' => false, 'div' => false, 'class' => 'form-control'));?>
                    <?php } else { ?>
                    <?php echo $this->Form->hidden('project_id', array('value' => $projectId));?>
                    <?php } ?>
                </div>
                <div class="form-group"><?php echo $this->Form->input('deal_id', array('type' => 'number', 'value' => $dealId, 'label' => false, 'div' => false, 'class' => 'form-control', 'placeholder' => __('Deal ID')));?></div>
                <div class="form-group"><?php echo $this->Form->input('keyword', array('value' => $keyword, 'label' => false, 'div' => false, 'class' => 'form-control', 'placeholder' => __('Search by client/deal/number')));?></div>
                <div class="form-group"><?php echo $this->Form->select('status', $allotmentStatusArr, array('empty' => __('All Status'), 'value' => $status, 'label' => false, 'div' => false, 'class' => 'form-control'));?></div>
                <div class="form-group"><?php echo $this->Form->input('start_date', array('id' => 'allotment_start_date', 'value' => $startDateInput, 'label' => false, 'div' => false, 'class' => 'form-control', 'placeholder' => __('Start Date')));?></div>
                <div class="form-group"><?php echo $this->Form->input('end_date', array('id' => 'allotment_end_date', 'value' => $endDateInput, 'label' => false, 'div' => false, 'class' => 'form-control', 'placeholder' => __('End Date')));?></div>
                <button type="submit" class="btn btn-default"><?php echo __('Filter');?></button>
                <?php echo $this->Html->link(__('Reset'), $allotmentsUrl, array('class' => 'btn btn-link'));?>
                <?php echo $this->Form->end();?>
            </div>

            <div id="allotments_printable" style="margin-top:12px;">
                <table class="balloting-allotments-brand-header">
                    <tr>
                        <td class="balloting-allotments-brand-logo-cell">
                            <?php if($headerAgencyLogo !== ''){ echo $this->Html->image($headerAgencyLogo, array('alt' => $headerAgencyName)); } ?>
                        </td>
                        <td class="balloting-allotments-brand-details">
                            <h3><?php echo h($headerAgencyName);?></h3>
                            <?php if(trim((string)$headerAgencyAddress) !== ''){?><div><?php echo h($headerAgencyAddress);?></div><?php }?>
                            <div>
                                <?php if(trim((string)$headerAgencyPhone) !== ''){?><span><?php echo __('Phone');?>: <?php echo h($headerAgencyPhone);?></span><?php }?>
                                <?php if(trim((string)$headerAgencyMobile) !== ''){?><span style="margin-left:10px;"><?php echo __('Mobile');?>: <?php echo h($headerAgencyMobile);?></span><?php }?>
                                <?php if(trim((string)$headerAgencyEmail) !== ''){?><span style="margin-left:10px;"><?php echo __('Email');?>: <?php echo h($headerAgencyEmail);?></span><?php }?>
                            </div>
                        </td>
                    </tr>
                </table>
                <div class="balloting-allotments-filter-summary">
                    <strong><?php echo __('Agency');?>:</strong> <?php echo h($selectedAgencyLabel);?>
                    <?php if(trim((string)$startDateInput) !== '' || trim((string)$endDateInput) !== ''){?>
                    &nbsp;|&nbsp;<strong><?php echo __('Date Range');?>:</strong> <?php echo h($startDateInput !== '' ? $startDateInput : __('N/A'));?> - <?php echo h($endDateInput !== '' ? $endDateInput : __('N/A'));?>
                    <?php }?>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered balloting-allotments-table">
                        <tr>
                            <th><?php echo __('Allotment #');?></th>
                            <th><?php echo __('Deal');?></th>
                            <th><?php echo __('Client');?></th>
                            <th><?php echo __('Father / Guardian');?></th>
                            <th><?php echo __('Street Address');?></th>
                            <th><?php echo __('Eligibility Snapshot');?></th>
                            <th><?php echo __('Status');?></th>
                            <th class="no-print"><?php echo __('Action');?></th>
                        </tr>
                        <?php if(empty($rows)){?>
                        <tr><td colspan="8" class="text-center"><?php echo __('No allotments found');?></td></tr>
                        <?php } else { foreach($rows as $row){
                            $id = (int)$row['BallotingAllotment']['id'];
                            $dealIdRow = (int)$row['BallotingAllotment']['deal_id'];
                        ?>
                        <tr>
                            <td>
                                <strong><?php echo h($row['BallotingAllotment']['allotment_no']);?></strong><br>
                                <small><?php echo __('Date');?>: <?php echo !empty($row['BallotingAllotment']['allotment_date']) ? $this->Time->format($sysDay.$dateSep.$sysMonth.$dateSep.$sysYear, $row['BallotingAllotment']['allotment_date']) : '';?></small>
                            </td>
                            <td><?php echo h($row['Deal']['invoice_no']);?><br><small>ID: <?php echo $dealIdRow;?></small></td>
                            <td><?php echo h($row['Client']['name']);?></td>
                            <td>
                                <?php
                                $fatherName = !empty($row['Client']['fathername']) ? trim((string)$row['Client']['fathername']) : '';
                                $relationType = !empty($row['Client']['relation_type']) ? trim((string)$row['Client']['relation_type']) : '';
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
                            <td><?php echo h($row['BallotingAllotment']['street_address']);?></td>
                            <td>
                                <?php echo __('Paid');?>: <?php echo h($row['BallotingAllotment']['paid_percent']);?>%<br>
                                <?php echo __('Overdue Installments');?>: <?php echo (int)$row['BallotingAllotment']['overdue_installments'];?><br>
                                <?php echo __('Overdue Days');?>: <?php echo (int)$row['BallotingAllotment']['overdue_days'];?>
                            </td>
                            <td><span class="label label-<?php echo ($row['BallotingAllotment']['status']==='Allotted') ? 'success' : 'danger';?>"><?php echo h($row['BallotingAllotment']['status']);?></span></td>
                            <td class="no-print">
                                <?php echo $this->Html->link('<span class="fa fa-link"></span>', array('action' => 'manualallotment', '?' => array('project_id' => $projectId, 'deal_id' => $dealIdRow)), array('escape' => false, 'class' => 'btn btn-primary btn-xs', 'title' => __('Manual Allotment')));?>
                                <?php if($row['BallotingAllotment']['status']==='Allotted'){?>
                                <?php echo $this->Html->link('<span class="fa fa-times-circle"></span>', array('action' => 'cancelallotment', $id), array('escape' => false, 'class' => 'btn btn-danger btn-xs', 'title' => __('Cancel'), 'confirm' => __('Cancel this allotment and release unit?')));?>
                                <?php }?>
                                <?php if(!empty($canUseAgencyFilters) && $row['BallotingAllotment']['status']==='Cancelled'){?>
                                <?php echo $this->Html->link('<span class="fa fa-trash"></span>', array('action' => 'deletecancelledallotment', $id, '?' => array('project_id' => $projectId)), array('escape' => false, 'class' => 'btn btn-danger btn-xs', 'title' => __('Delete Cancelled'), 'confirm' => __('Delete this cancelled allotment permanently?')));?>
                                <?php }?>
                                <?php echo $this->Html->link('<span class="fa fa-file-text-o"></span>', array('controller' => 'Realestateletters', 'action' => 'add', 'RLT_ALLOT', $dealIdRow), array('escape' => false, 'class' => 'btn btn-success btn-xs', 'title' => __('Create Letter')));?>
                                <?php if($row['BallotingAllotment']['status']==='Allotted'){?>
                                <?php echo $this->Html->link('<span class="fa fa-map-marker"></span>', array('controller' => 'Realestateletters', 'action' => 'add', 'RLT_DEMARCATION', $dealIdRow), array('escape' => false, 'class' => 'btn btn-info btn-xs', 'title' => __('Create Demarcation Letter')));?>
                                <?php }?>
                            </td>
                        </tr>
                        <?php }}?>
                    </table>
                </div>
            </div>
            <?php echo $this->element('pagination');?>
        </div>
    </div>
</div>
