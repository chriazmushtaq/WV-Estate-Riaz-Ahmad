<?php
$t = isset($transfer['Dealtransfer']) ? $transfer['Dealtransfer'] : array();
$sourceDealNo = !empty($sourceDeal['Deal']['invoice_no']) ? $sourceDeal['Deal']['invoice_no'] : '#'.(int)$t['source_deal_id'];
$targetDealNo = !empty($targetDeal['Deal']['invoice_no']) ? $targetDeal['Deal']['invoice_no'] : ((int)$t['target_deal_id'] > 0 ? '#'.(int)$t['target_deal_id'] : '-');
$feeVoucherTxId = !empty($t['fee_voucher_transaction_id']) ? (int)$t['fee_voucher_transaction_id'] : 0;
$feeVoucherProjectId = (!empty($t['transfer_type']) && $t['transfer_type'] === 'Merge' && !empty($t['target_project_id'])) ? (int)$t['target_project_id'] : (int)$t['source_project_id'];
$displayCurrency = isset($currency) ? $currency : '';
$rawSteps = (array)$steps;
$hasStep = function($needles) use ($rawSteps) {
    foreach((array)$needles as $needle)
    {
        $needle = strtolower(trim((string)$needle));
        if($needle === '') { continue; }
        foreach($rawSteps as $step)
        {
            if(strpos(strtolower((string)$step), $needle) !== false)
            {
                return true;
            }
        }
    }
    return false;
};
$transferStatus = isset($t['status']) ? (string)$t['status'] : 'Draft';
$isFinalized = in_array($transferStatus, array('Finalized','Reversed'));
$isReversed = ($transferStatus === 'Reversed') || $hasStep(array('reversed by admin'));
$hasProcessingFee = isset($t['processing_fee']) && (float)$t['processing_fee'] > 0;
$hasFeeCharge = !empty($t['fee_charge_id']);
$progressMilestones = array();
$progressMilestones[] = array('label' => __('Request created'), 'state' => 'done', 'note' => !empty($t['transfer_no']) ? $t['transfer_no'] : '');
$progressMilestones[] = array('label' => __('Source deal and client verified'), 'state' => 'done', 'note' => $sourceDealNo);
$progressMilestones[] = array('label' => __('Processing fee calculated'), 'state' => 'done', 'note' => $displayCurrency.' '.number_format((float)$t['processing_fee'],2));
if(!empty($t['transfer_type']) && $t['transfer_type'] === 'Merge')
{
    $progressMilestones[] = array('label' => __('Target deal selected for merge'), 'state' => ((int)$t['target_deal_id'] > 0 ? 'done' : 'pending'), 'note' => $targetDealNo);
    $progressMilestones[] = array('label' => __('Source payment history kept for audit'), 'state' => ($isFinalized || $isReversed || $hasStep(array('source deal payment history kept','original deal history remains')) ? 'done' : 'pending'), 'note' => __('Source ledger remains visible'));
    $progressMilestones[] = array('label' => __('Target merge payment entry'), 'state' => ($isFinalized || $hasStep(array('target merge payment receipt')) ? 'done' : 'pending'), 'note' => __('Single target receipt after finalize'));
}
else
{
    $progressMilestones[] = array('label' => __('Client / agency transfer'), 'state' => ($isFinalized ? 'done' : 'pending'), 'note' => $targetDealNo);
}
if($hasProcessingFee)
{
    $progressMilestones[] = array('label' => __('Processing fee charge'), 'state' => ($hasFeeCharge ? 'done' : ($isFinalized ? 'skipped' : 'pending')), 'note' => $hasFeeCharge ? __('Created') : ($isFinalized ? __('Not created') : __('Pending finalization')));
    $progressMilestones[] = array('label' => __('Processing fee voucher payment'), 'state' => ($feeVoucherTxId > 0 ? 'done' : 'pending'), 'note' => $feeVoucherTxId > 0 ? __('Paid voucher posted') : __('Print unpaid voucher or use Pay Fee'));
}
$progressMilestones[] = array('label' => __('Deal transfer finalized'), 'state' => ($isFinalized ? 'done' : 'pending'), 'note' => $isFinalized ? $transferStatus : __('Pending'));
$progressMilestones[] = array('label' => (!empty($t['transfer_type']) && $t['transfer_type'] === 'Merge') ? __('Deal merge agreement ready') : __('Deal transfer agreement ready'), 'state' => ($isFinalized ? 'done' : 'pending'), 'note' => $isFinalized ? __('Ready to print') : __('After finalization'));
if($isReversed)
{
    $progressMilestones[] = array('label' => (!empty($t['transfer_type']) && $t['transfer_type'] === 'Merge') ? __('Deal merge reversed by admin') : __('Deal transfer reversed by admin'), 'state' => 'done', 'note' => __('Reversal completed'));
}
$doneMilestones = 0;
foreach($progressMilestones as $m)
{
    if($m['state'] === 'done') { $doneMilestones++; }
}
$totalMilestones = count($progressMilestones);
$pdfFileName = 'Deal-Transfer-'.preg_replace('/[^A-Za-z0-9_-]/', '-', !empty($t['transfer_no']) ? $t['transfer_no'] : (string)(isset($t['id']) ? $t['id'] : date('YmdHis'))).'.pdf';
?>
<style type="text/css">
.dt-page-shell{background:#f5f8fc;border:1px solid #dbe6f3;border-radius:8px;padding:12px;}
.dt-page-actions{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:12px;flex-wrap:wrap;}
.dt-page-actions .btn-group{margin-bottom:0;}
.dt-print-actions{display:flex;gap:8px;flex-wrap:wrap;}
.dt-print-title{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:12px;background:#08213f;color:#fff;border-radius:8px;padding:14px 16px;}
.dt-print-title h2{margin:0;font-size:20px;font-weight:900;color:#fff;letter-spacing:0;}
.dt-print-title .dt-ref{font-size:12px;font-weight:800;color:#cfe0f5;text-align:right;}
.dt-status-pill{display:inline-block;padding:5px 10px;border-radius:4px;background:#168a53;color:#fff;font-size:12px;font-weight:900;text-transform:uppercase;}
.dt-status-pill.draft{background:#f0ad4e;}
.dt-status-pill.reversed{background:#6c7a89;}
.dt-status-pill.rejected{background:#c0392b;}
.dt-card{border:1px solid #dbe6f3;border-radius:8px;background:#fff;margin-bottom:10px;box-shadow:0 6px 16px rgba(15,35,58,.05);}
.dt-card h4{margin:0;padding:10px 12px;background:#f8fbff;border-bottom:1px solid #dbe6f3;color:#08213f;font-weight:900;font-size:16px;}
.dt-grid{display:grid;grid-template-columns:repeat(4,minmax(160px,1fr));gap:10px;padding:12px;}
.dt-item{border-left:3px solid #0b3a75;background:#fbfdff;padding:8px 9px;min-height:58px;}
.dt-item .k{display:block;font-size:11px;color:#64748b;font-weight:800;text-transform:uppercase;}
.dt-item .v{display:block;font-weight:800;color:#071c36;word-break:break-word;}
.dt-progress-head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px 12px 4px;}
.dt-progress-title{font-weight:900;color:#08213f;}
.dt-progress-count{font-weight:800;font-size:12px;color:#52657d;background:#f4f8fc;border:1px solid #dbe6f3;border-radius:4px;padding:5px 9px;white-space:nowrap;}
.dt-milestones{display:grid;grid-template-columns:repeat(3,minmax(220px,1fr));gap:10px;padding:10px 12px 12px;}
.dt-step{position:relative;display:grid;grid-template-columns:34px 1fr;gap:9px;align-items:start;border:1px solid #dbe6f3;background:#fbfdff;border-radius:8px;padding:10px;min-height:82px;}
.dt-step-icon{width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:900;background:#e8eef7;color:#5f7188;}
.dt-step-title{font-weight:900;color:#0b2038;line-height:1.25;}
.dt-step-note{font-size:12px;color:#6a7a91;margin-top:3px;line-height:1.3;}
.dt-step-badge{display:inline-block;font-size:10px;font-weight:900;text-transform:uppercase;border-radius:3px;padding:4px 6px;white-space:nowrap;margin-top:7px;}
.dt-step.done{border-color:#bfe4cf;background:#f5fbf7;}
.dt-step.done .dt-step-icon{background:#168a53;color:#fff;}
.dt-step.done .dt-step-badge{background:#dff3e8;color:#137145;}
.dt-step.pending{border-color:#f0d3a2;background:#fffbf3;}
.dt-step.pending .dt-step-icon{background:#f0ad4e;color:#fff;}
.dt-step.pending .dt-step-badge{background:#fff1d6;color:#97610c;}
.dt-step.skipped{border-color:#d7e0ec;background:#f8fafc;}
.dt-step.skipped .dt-step-icon{background:#8391a4;color:#fff;}
.dt-step.skipped .dt-step-badge{background:#edf1f5;color:#607086;}
.dt-reason-body{padding:12px;min-height:48px;color:#1b3048;line-height:1.5;}
@media(max-width:1200px){.dt-grid{grid-template-columns:repeat(2,minmax(180px,1fr));}.dt-milestones{grid-template-columns:repeat(2,minmax(220px,1fr));}}
@media(max-width:900px){.dt-grid,.dt-milestones{grid-template-columns:1fr;}.dt-progress-head,.dt-print-title{display:block;}.dt-progress-count{display:inline-block;margin-top:8px;}.dt-print-title .dt-ref{text-align:left;margin-top:8px;}}
@media print{
    body{background:#fff!important;}
    .page-title,.dt-page-actions,.dt-no-print,.sidebar-menu,.main-menu,.navbar,.user-info-navbar,.page-loading-overlay{display:none!important;}
    .panel,.panel-body{border:0!important;box-shadow:none!important;padding:0!important;margin:0!important;background:#fff!important;}
    .dt-page-shell{border:0!important;background:#fff!important;padding:0!important;}
    .dt-card{box-shadow:none!important;break-inside:avoid;page-break-inside:avoid;}
    .dt-print-title{border-radius:0!important;}
    .dt-grid{grid-template-columns:repeat(4,1fr)!important;}
    .dt-milestones{grid-template-columns:repeat(3,1fr)!important;}
}
</style>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<div class="page-title"><div class="title-env"><h1 class="title"><?php echo __('Deal Transfer Detail');?></h1></div></div>
<div class="panel">
    <div class="panel-body">
        <?php echo $this->Session->flash();?>
        <div class="dt-page-actions dt-no-print">
            <div class="btn-group">
                <?php if(isset($t['status']) && $t['status'] === 'Draft'){ ?>
                    <?php echo $this->Html->link('<span class="fa fa-check"></span> '.__('Finalize'), array('controller'=>'Dealtransfers','action'=>'finalize',(int)$t['id']), array('escape'=>false,'class'=>'btn btn-success','confirm'=>__('Finalize this deal transfer now?')));?>
                    <?php echo $this->Html->link('<span class="fa fa-edit"></span> '.__('Edit'), array('controller'=>'Dealtransfers','action'=>'edit',(int)$t['id']), array('escape'=>false,'class'=>'btn btn-warning'));?>
                <?php } ?>
                <?php echo $this->Html->link('<span class="fa fa-list"></span> '.__('All Transfers'), array('controller'=>'Dealtransfers','action'=>'index'), array('escape'=>false,'class'=>'btn btn-default'));?>
                <?php if($feeVoucherTxId > 0){ ?>
                    <?php echo $this->Html->link('<span class="fa fa-print"></span> '.__('Print Paid Voucher'), array('controller'=>'Ardaccounts','action'=>'printvoucher',$feeVoucherTxId,$feeVoucherProjectId), array('escape'=>false,'class'=>'btn btn-primary','target'=>'_blank'));?>
                <?php } elseif(isset($t['status']) && $t['status'] !== 'Rejected' && (float)$t['processing_fee'] > 0){ ?>
                    <?php echo $this->Html->link('<span class="fa fa-print"></span> '.__('Print Fee Voucher'), array('controller'=>'Dealtransfers','action'=>'printfeevoucher',(int)$t['id']), array('escape'=>false,'class'=>'btn btn-info','target'=>'_blank'));?>
                    <?php echo $this->Html->link('<span class="fa fa-money"></span> '.__('Pay Fee'), array('controller'=>'Dealtransfers','action'=>'payfeevoucher',(int)$t['id']), array('escape'=>false,'class'=>'btn btn-primary','confirm'=>__('Mark processing fee as paid and create Accounts voucher now?')));?>
                <?php } ?>
                <?php if(isset($t['status']) && $t['status'] === 'Finalized'){ ?>
                    <?php echo $this->Html->link('<span class="fa fa-print"></span> '.__('Print Agreement'), array('controller'=>'Dealtransfers','action'=>'agreement',(int)$t['id']), array('escape'=>false,'class'=>'btn btn-success','target'=>'_blank'));?>
                    <?php if(!empty($isSuperAdminSession)){ ?>
                    <?php echo $this->Html->link('<span class="fa fa-undo"></span> '.__('Reverse Transfer'), array('controller'=>'Dealtransfers','action'=>'reverse',(int)$t['id']), array('escape'=>false,'class'=>'btn btn-danger','confirm'=>__('Reverse this finalized transfer/merge and remove its fee charge/voucher?')));?>
                    <?php } ?>
                <?php } ?>
            </div>
            <div class="dt-print-actions">
                <button type="button" id="dtPrintBtn" class="btn btn-default"><span class="fa fa-print"></span> <?php echo __('Print');?></button>
                <button type="button" id="dtPdfBtn" class="btn btn-primary"><span class="fa fa-file-pdf-o"></span> <?php echo __('Export PDF');?></button>
            </div>
        </div>

        <div id="dealTransferPrintArea" class="dt-page-shell">
            <div class="dt-print-title">
                <h2><?php echo h((!empty($t['transfer_type']) && $t['transfer_type'] === 'Merge') ? __('Deal Merge Detail') : __('Deal Transfer Detail'));?></h2>
                <div class="dt-ref">
                    <span class="dt-status-pill <?php echo h(strtolower($transferStatus));?>"><?php echo h($transferStatus);?></span><br>
                    <?php echo h(!empty($t['transfer_no']) ? $t['transfer_no'] : ('#'.(int)$t['id']));?>
                </div>
            </div>

            <div class="dt-card">
                <h4><?php echo __('Transfer Summary');?></h4>
                <div class="dt-grid">
                    <div class="dt-item"><span class="k"><?php echo __('Transfer No');?></span><span class="v"><?php echo h($t['transfer_no']);?></span></div>
                    <div class="dt-item"><span class="k"><?php echo __('Type');?></span><span class="v"><?php echo h($t['transfer_type']);?></span></div>
                    <div class="dt-item"><span class="k"><?php echo __('Status');?></span><span class="v"><?php echo h($t['status']);?></span></div>
                    <div class="dt-item"><span class="k"><?php echo __('Source Deal');?></span><span class="v"><?php echo h($sourceDealNo);?></span></div>
                    <div class="dt-item"><span class="k"><?php echo __('Target Deal');?></span><span class="v"><?php echo h($targetDealNo);?></span></div>
                    <div class="dt-item"><span class="k"><?php echo __('Processing Fee');?></span><span class="v"><?php echo h($displayCurrency.' '.number_format((float)$t['processing_fee'],2));?></span></div>
                    <div class="dt-item"><span class="k"><?php echo __('Deal Total');?></span><span class="v"><?php echo h($displayCurrency.' '.number_format((float)$t['deal_total_amount'],2));?></span></div>
                    <div class="dt-item"><span class="k"><?php echo __('Paid');?></span><span class="v"><?php echo h($displayCurrency.' '.number_format((float)$t['deal_paid_amount'],2));?></span></div>
                    <div class="dt-item"><span class="k"><?php echo __('Balance');?></span><span class="v"><?php echo h($displayCurrency.' '.number_format((float)$t['deal_balance_amount'],2));?></span></div>
                    <div class="dt-item"><span class="k"><?php echo __('Fee Voucher');?></span><span class="v"><?php
                        if($feeVoucherTxId > 0)
                        {
                            echo $this->Html->link(__('Print Paid Voucher'), array('controller'=>'Ardaccounts','action'=>'printvoucher',$feeVoucherTxId,$feeVoucherProjectId), array('target'=>'_blank'));
                        }
                        elseif(isset($t['status']) && $t['status'] !== 'Rejected' && (float)$t['processing_fee'] > 0)
                        {
                            echo $this->Html->link(__('Print Voucher'), array('controller'=>'Dealtransfers','action'=>'printfeevoucher',(int)$t['id']), array('target'=>'_blank'));
                            echo ' | ';
                            echo $this->Html->link(__('Pay Fee'), array('controller'=>'Dealtransfers','action'=>'payfeevoucher',(int)$t['id']), array('confirm'=>__('Mark processing fee as paid and create Accounts voucher now?')));
                        }
                        else
                        {
                            echo '-';
                        }
                    ?></span></div>
                    <div class="dt-item"><span class="k"><?php echo __('Agreement');?></span><span class="v"><?php
                        if(isset($t['status']) && $t['status'] === 'Finalized')
                        {
                            echo $this->Html->link(__('Print Agreement'), array('controller'=>'Dealtransfers','action'=>'agreement',(int)$t['id']), array('target'=>'_blank'));
                        }
                        else
                        {
                            echo __('Available after finalization');
                        }
                    ?></span></div>
                </div>
            </div>

            <div class="dt-card">
                <h4><?php echo __('Process Steps');?></h4>
                <div class="dt-progress-head">
                    <div class="dt-progress-title"><?php echo h($transferStatus);?> <?php echo __('Workflow');?></div>
                    <div class="dt-progress-count"><?php echo h($doneMilestones.' / '.$totalMilestones.' '.__('Completed'));?></div>
                </div>
                <div class="dt-milestones">
                    <?php foreach($progressMilestones as $milestone){
                        $state = in_array($milestone['state'], array('done','pending','skipped')) ? $milestone['state'] : 'pending';
                        $icon = ($state === 'done') ? 'fa-check' : (($state === 'skipped') ? 'fa-minus' : 'fa-clock-o');
                        $badge = ($state === 'done') ? __('Completed') : (($state === 'skipped') ? __('Skipped') : __('Pending'));
                    ?>
                        <div class="dt-step <?php echo h($state);?>">
                            <div class="dt-step-icon"><span class="fa <?php echo h($icon);?>"></span></div>
                            <div>
                                <div class="dt-step-title"><?php echo h($milestone['label']);?></div>
                                <?php if(!empty($milestone['note'])){ ?><div class="dt-step-note"><?php echo h($milestone['note']);?></div><?php } ?>
                                <div class="dt-step-badge"><?php echo h($badge);?></div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>

            <div class="dt-card">
                <h4><?php echo __('Reason');?></h4>
                <div class="dt-reason-body"><?php echo nl2br(h(isset($t['reason']) ? $t['reason'] : ''));?></div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
(function(){
    var fileName = <?php echo json_encode($pdfFileName);?>;
    var printButton = document.getElementById('dtPrintBtn');
    var pdfButton = document.getElementById('dtPdfBtn');
    var area = document.getElementById('dealTransferPrintArea');
    if(printButton)
    {
        printButton.onclick = function(){ window.print(); };
    }
    if(pdfButton)
    {
        pdfButton.onclick = function(){
            if(!area) { window.print(); return; }
            if(typeof html2pdf === 'undefined')
            {
                window.print();
                return;
            }
            var options = {
                margin: [8, 8, 8, 8],
                filename: fileName,
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2, useCORS: true, backgroundColor: '#ffffff' },
                jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' },
                pagebreak: { mode: ['css', 'legacy'] }
            };
            html2pdf().set(options).from(area).save();
        };
    }
})();
</script>
