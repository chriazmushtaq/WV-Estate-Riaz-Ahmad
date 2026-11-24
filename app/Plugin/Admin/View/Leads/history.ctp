<?php
$leadStatusName = !empty($leadRow['Status']['name']) ? (string)$leadRow['Status']['name'] : '-';
$leadStatusColor = !empty($leadRow['Status']['color']) ? (string)$leadRow['Status']['color'] : '#337ab7';
$leadAgencyName = !empty($leadRow['Project']['name']) ? (string)$leadRow['Project']['name'] : '-';
$leadOwnerName = !empty($leadRow['User']['name']) ? (string)$leadRow['User']['name'] : '-';
$leadOwnerRole = !empty($leadRow['LeadOwnerRole']['name']) ? (string)$leadRow['LeadOwnerRole']['name'] : '';
$leadFollowUpDisplay = '-';
if(!empty($leadRow['Lead']['follow_up']) && $leadRow['Lead']['follow_up'] !== '0000-00-00 00:00:00')
{
    $leadFollowUpDisplay = date('d-M-Y h:i A', strtotime($leadRow['Lead']['follow_up']));
}
$leadLastContactDisplay = '-';
if(!empty($leadRow['Lead']['last_contact_date']) && $leadRow['Lead']['last_contact_date'] !== '0000-00-00')
{
    $leadLastContactDisplay = date('d-M-Y', strtotime($leadRow['Lead']['last_contact_date']));
}
elseif(!empty($leadRow['Lead']['follow_up']) && $leadRow['Lead']['follow_up'] !== '0000-00-00 00:00:00')
{
    $leadLastContactDisplay = date('d-M-Y', strtotime($leadRow['Lead']['follow_up']));
}
?>
<style>
.lead-history-toolbar { margin-bottom: 15px; }
.lead-history-toolbar .btn { margin-right: 8px; margin-bottom: 8px; }
.lead-history-summary { margin-bottom: 20px; }
.lead-history-summary .summary-card {
    border: 1px solid #d9d9d9;
    border-radius: 4px;
    min-height: 108px;
    padding: 12px 14px;
    margin-bottom: 15px;
    background: #fff;
}
.lead-history-summary .summary-label {
    font-size: 11px;
    font-weight: bold;
    text-transform: uppercase;
    color: #777;
    margin-bottom: 6px;
}
.lead-history-summary .summary-value {
    font-size: 16px;
    color: #222;
    word-break: break-word;
}
.lead-history-badge {
    display: inline-block;
    border-radius: 3px;
    padding: 4px 8px;
    color: #fff;
    font-size: 11px;
    font-weight: bold;
    text-transform: uppercase;
}
.lead-history-badge.type-create { background: #5cb85c; }
.lead-history-badge.type-change { background: #337ab7; }
.lead-history-badge.type-sms { background: #f0ad4e; }
.lead-history-badge.type-email { background: #5bc0de; }
.lead-history-badge.type-default { background: #777; }
.lead-history-message {
    max-width: 440px;
    white-space: pre-wrap;
    word-break: break-word;
}
.lead-history-cell {
    white-space: pre-wrap;
    word-break: break-word;
    max-width: 220px;
}
.lead-history-actor small {
    display: block;
    color: #777;
    margin-top: 4px;
}
.no-history-note {
    padding: 25px 10px;
    text-align: center;
    color: #777;
}
@media print {
    .no-print {
        display: none !important;
    }
    body {
        background: #fff !important;
    }
}
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script type="text/javascript">
function leadHistoryPdfStamp()
{
    var d = new Date();
    function pad(n){ return (n < 10 ? '0' : '') + n; }
    return d.getFullYear().toString() + pad(d.getMonth() + 1) + pad(d.getDate()) + '_' + pad(d.getHours()) + pad(d.getMinutes()) + pad(d.getSeconds());
}
function exportLeadHistoryPdf()
{
    var node = document.getElementById('lead-history-export-target');
    if(!node)
    {
        alert('No history found for PDF export.');
        return false;
    }
    if(typeof html2pdf === 'undefined')
    {
        alert('PDF export library is not loaded.');
        return false;
    }
    var filename = 'lead_history_<?php echo (int)$leadRow['Lead']['id'];?>_' + leadHistoryPdfStamp() + '.pdf';
    html2pdf().set({
        margin: [0.3, 0.3, 0.3, 0.3],
        filename: filename,
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2, useCORS: true, scrollY: 0 },
        jsPDF: { unit: 'in', format: 'a4', orientation: 'landscape' }
    }).from(node).save();
    return false;
}
</script>

<div class="row">
    <div class="col-md-12">
        <div class="lead-history-toolbar no-print">
            <?php echo $this->Html->link('<span class="fa fa-arrow-left"></span>&nbsp;'.__('Back to Manage Leads'), array('controller' => 'Leads', 'action' => 'manage'), array('escape' => false, 'class' => 'btn btn-default'));?>
            <?php echo $this->Html->link('<span class="fa fa-arrows-alt"></span>&nbsp;'.__('View Lead'), array('controller' => 'Leads', 'action' => 'leadview', (int)$leadRow['Lead']['id']), array('escape' => false, 'class' => 'btn btn-primary'));?>
            <?php echo $this->Html->link('<span class="fa fa-file-pdf-o"></span>&nbsp;'.__('Export PDF'), '#', array('escape' => false, 'class' => 'btn btn-success', 'onclick' => 'return exportLeadHistoryPdf();'));?>
        </div>

        <div class="box box-primary" id="lead-history-export-target">
            <div class="box-header with-border">
                <h3 class="box-title"><?php echo __('Lead History');?></h3>
            </div>
            <div class="box-body">
                <div class="row lead-history-summary">
                    <div class="col-md-3 col-sm-6">
                        <div class="summary-card">
                            <div class="summary-label"><?php echo __('Lead');?></div>
                            <div class="summary-value"><?php echo h(!empty($leadRow['Lead']['lead_name']) ? $leadRow['Lead']['lead_name'] : '-');?></div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="summary-card">
                            <div class="summary-label"><?php echo __('Agency');?></div>
                            <div class="summary-value"><?php echo h($leadAgencyName);?></div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="summary-card">
                            <div class="summary-label"><?php echo __('Assigned User');?></div>
                            <div class="summary-value"><?php echo h($leadOwnerName);?><?php if($leadOwnerRole !== ''){ ?><br><small><?php echo h($leadOwnerRole);?></small><?php } ?></div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="summary-card">
                            <div class="summary-label"><?php echo __('Status');?></div>
                            <div class="summary-value"><span style="background:<?php echo h($leadStatusColor);?>;color:#fff;padding:5px 10px;display:inline-block;border-radius:3px;"><?php echo h($leadStatusName);?></span></div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="summary-card">
                            <div class="summary-label"><?php echo __('Mobile');?></div>
                            <div class="summary-value"><?php echo h(!empty($leadRow['Lead']['lead_mobile']) ? $leadRow['Lead']['lead_mobile'] : '-');?></div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="summary-card">
                            <div class="summary-label"><?php echo __('Email');?></div>
                            <div class="summary-value"><?php echo h(!empty($leadRow['Lead']['lead_email']) ? $leadRow['Lead']['lead_email'] : '-');?></div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="summary-card">
                            <div class="summary-label"><?php echo __('Last Contact Date');?></div>
                            <div class="summary-value"><?php echo h($leadLastContactDisplay);?></div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="summary-card">
                            <div class="summary-label"><?php echo __('Follow Up');?></div>
                            <div class="summary-value"><?php echo h($leadFollowUpDisplay);?></div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th style="width:140px;"><?php echo __('Date / Time');?></th>
                                <th style="width:150px;"><?php echo __('Changed By');?></th>
                                <th style="width:110px;"><?php echo __('Action');?></th>
                                <th><?php echo __('Changes / Notes');?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(!empty($displayHistoryRows)){ ?>
                                <?php foreach($displayHistoryRows as $historyDisplayRow){ ?>
                                    <?php
                                    $entry = isset($historyDisplayRow['entry']) ? (array)$historyDisplayRow['entry'] : array();
                                    $entryType = strtolower(trim((string)(isset($entry['entry_type']) ? $entry['entry_type'] : 'change')));
                                    $badgeClass = 'type-default';
                                    if(in_array($entryType, array('create','change','sms','email'), true))
                                    {
                                        $badgeClass = 'type-'.$entryType;
                                    }
                                    $createdText = '-';
                                    if(!empty($entry['created']))
                                    {
                                        $createdText = date('d-M-Y h:i A', strtotime($entry['created']));
                                    }
                                    $actorName = trim((string)(isset($entry['actor_name']) ? $entry['actor_name'] : ''));
                                    if($actorName === '')
                                    {
                                        $actorName = 'System';
                                    }
                                    $detailsText = !empty($historyDisplayRow['details_text']) ? (string)$historyDisplayRow['details_text'] : '-';
                                    ?>
                                    <tr>
                                        <td><?php echo h($createdText);?></td>
                                        <td class="lead-history-actor">
                                            <?php echo h($actorName);?>
                                            <?php if(!empty($entry['actor_role'])){ ?>
                                                <small><?php echo h($entry['actor_role']);?></small>
                                            <?php } ?>
                                        </td>
                                        <td>
                                            <span class="lead-history-badge <?php echo h($badgeClass);?>"><?php echo h(!empty($entry['title']) ? $entry['title'] : ucfirst($entryType));?></span>
                                        </td>
                                        <td>
                                            <div class="lead-history-message"><?php echo nl2br(h($detailsText));?></div>
                                        </td>
                                    </tr>
                                <?php } ?>
                            <?php } else { ?>
                                <tr>
                                    <td colspan="4" class="no-history-note"><?php echo __('No history found for this lead.');?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
