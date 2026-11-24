<?php
$ledger = isset($ledger) ? $ledger : array();
$agency = isset($agency) ? $agency : array();
$ardSettings = isset($ardSettings) ? $ardSettings : array();
$statementRows = isset($statementRows) ? $statementRows : array();
$debitTotal = isset($debitTotal) ? (float)$debitTotal : 0.0;
$creditTotal = isset($creditTotal) ? (float)$creditTotal : 0.0;
$closingBalance = isset($closingBalance) ? (float)$closingBalance : 0.0;
$statementFilter = isset($statementFilter) ? $statementFilter : array();
$currencySymbolRaw = isset($currency) ? trim((string)$currency) : 'Rs';
$currencySymbol = trim(strip_tags($currencySymbolRaw));
if($currencySymbol === '')
{
    $currencySymbol = 'Rs';
}
$currencySymbol .= ' ';

$companyName = !empty($ardSettings['company_name']) ? $ardSettings['company_name'] : (!empty($agency['name']) ? $agency['name'] : __('Agency'));
$agencyAddress = !empty($agency['address']) ? $agency['address'] : '-';
$agencyPhone = !empty($agency['phone']) ? $agency['phone'] : '-';
$agencyMobile = !empty($agency['mobile']) ? $agency['mobile'] : '-';
$agencyEmail = !empty($agency['email']) ? $agency['email'] : '-';

$resolveImage = function($candidates) {
    foreach ($candidates as $candidate) {
        if (!is_string($candidate)) {
            continue;
        }
        $candidate = trim(str_replace('\\', '/', $candidate), '/');
        if ($candidate === '') {
            continue;
        }
        $filePath = WWW_ROOT . 'img' . DS . str_replace('/', DS, $candidate);
        if (file_exists($filePath)) {
            return $candidate;
        }
    }
    return '';
};
$agencyLogo = $resolveImage(array(
    !empty($agency['photo']) ? 'agency_thumb/' . $agency['photo'] : '',
    !empty($agency['photo']) ? $agency['photo'] : '',
    !empty($frontLogo) ? $frontLogo : ''
));
if($agencyLogo === '' && !empty($frontLogo))
{
    $agencyLogo = trim((string)$frontLogo, '/');
}

$filterPeriod = isset($statementFilter['period']) ? strtolower((string)$statementFilter['period']) : 'lifetime';
$filterStartDate = isset($statementFilter['start_date']) ? (string)$statementFilter['start_date'] : '';
$filterEndDate = isset($statementFilter['end_date']) ? (string)$statementFilter['end_date'] : '';
$filterLabelMap = array(
    'daily' => __('Daily'),
    'monthly' => __('Monthly'),
    'yearly' => __('Yearly'),
    'lifetime' => __('Lifetime'),
    'custom' => __('Custom')
);
$formatDisplayDate = function($value) {
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
$formatDisplayDateTime = function($value) {
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
$selectedFilterLabel = isset($filterLabelMap[$filterPeriod]) ? $filterLabelMap[$filterPeriod] : __('Lifetime');
$selectedStartLabel = $formatDisplayDate($filterStartDate);
$selectedEndLabel = $formatDisplayDate($filterEndDate);
$printDateTime = $formatDisplayDateTime(isset($currentDateTime) ? $currentDateTime : '');
$ledgerTypeLabel = (isset($ledger['type']) && strtolower((string)$ledger['type']) === 'expense') ? __('Expense') : __('Bank/Asset');
$showCustomRange = ($filterPeriod === 'custom');
$safeLedgerName = preg_replace('/[^A-Za-z0-9_-]/', '_', isset($ledger['name']) ? (string)$ledger['name'] : 'ledger');
if(trim((string)$safeLedgerName) === '')
{
    $safeLedgerName = 'ledger';
}
?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<style type="text/css">
.ard-statement-wrap .panel{border:1px solid #dbe5f2;}
.ard-statement-wrap .panel-body{background:#ffffff;}
.ard-statement-toolbar{display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:12px;}
.statement-filter-form{display:flex;align-items:flex-end;gap:8px;flex-wrap:wrap;margin-bottom:12px;}
.statement-filter-form .field-wrap{display:flex;flex-direction:column;gap:4px;}
.statement-filter-form label{font-size:11px;color:#5d6e86;font-weight:600;margin:0;}
.statement-filter-form select,.statement-filter-form input{height:34px;min-width:130px;}
.ard-statement-card{border:1px solid #dfe7f3;border-radius:8px;padding:14px;margin-bottom:12px;background:#fff;}
.ard-statement-head{border-bottom:1px solid #e6edf8;padding-bottom:10px;margin-bottom:10px;}
.ard-statement-head-row{display:flex;align-items:center;justify-content:center;gap:14px;}
.ard-statement-logo{width:90px;min-width:90px;text-align:left;}
.ard-statement-logo img{max-width:82px;max-height:72px;}
.ard-statement-head-main{text-align:center;flex:1 1 auto;}
.ard-statement-company{font-size:24px;font-weight:700;color:#1f3556;line-height:1.2;}
.ard-statement-meta{font-size:12px;color:#5d6e86;margin-top:5px;line-height:1.5;}
.ard-statement-title{font-size:21px;font-weight:700;color:#233a5c;text-align:center;margin:8px 0 0;}
.statement-info-row{display:flex;flex-wrap:wrap;gap:10px;}
.statement-info-card{flex:1 1 32%;min-width:260px;border:1px solid #dce5f2;border-radius:8px;background:#fbfdff;padding:10px;}
.statement-info-card h5{margin:0 0 8px;font-size:13px;font-weight:700;color:#233a5c;border-bottom:1px solid #e7eef9;padding-bottom:6px;}
.statement-info-line{display:flex;align-items:flex-start;gap:8px;font-size:12px;line-height:1.45;margin-bottom:5px;}
.statement-info-line:last-child{margin-bottom:0;}
.statement-info-line .field-key{min-width:105px;color:#5f6f86;font-weight:700;}
.statement-info-line .field-val{flex:1;color:#2c3f5f;word-break:break-word;}
.ard-statement-table thead th{background:#f3f7fd;color:#253d60;font-size:12px;font-weight:700;}
.ard-statement-table tbody td{font-size:12px;vertical-align:top;}
.ard-statement-table tfoot td{background:#f7faff;font-weight:700;color:#20395e;}
.text-right{text-align:right;}
.statement-selected-filter{border:1px solid #e0e8f4;background:#f8fbff;border-radius:8px;padding:8px 10px;margin-bottom:10px;font-size:12px;color:#2f4462;}
.statement-selected-filter strong{color:#1f3556;}
.statement-print-footer{margin-top:10px;padding-top:8px;border-top:1px solid #d7dfec;font-size:12px;color:#456082;text-align:right;}
.ard-action-icons{display:flex;align-items:center;justify-content:center;gap:4px;flex-wrap:nowrap;white-space:nowrap;}
.ard-action-icons .btn{width:28px;height:28px;padding:0;display:inline-flex;align-items:center;justify-content:center;line-height:1;}
.ard-action-icons .btn .fa{font-size:12px;}
.is-pdf-export .no-pdf{display:none !important;}
@media (max-width:991px){
    .statement-info-card{min-width:100%;flex-basis:100%;}
    .ard-statement-head-row{display:block;}
    .ard-statement-logo{text-align:center;margin:0 auto 8px;}
    .statement-filter-form select,.statement-filter-form input{min-width:100%;}
}
@media print{
    @page { size: A4 portrait; margin: 8mm; }
    html,body{background:#fff !important;}
    .navbar,#sidebar-main,.sidebar,.navigation,.navigation-header,.page-title,.no-print,.modal,.modal-backdrop,.alert{display:none !important;}
    .page-container,.page-content,.content-wrapper,.content,.ard-statement-wrap{margin:0 !important;padding:0 !important;width:100% !important;max-width:100% !important;}
    .ard-statement-wrap .panel,.ard-statement-wrap .panel-body,.ard-statement-card{border:none !important;box-shadow:none !important;padding:0 !important;margin:0 0 8px 0 !important;}
    .ard-statement-title{margin-top:3px;font-size:20px;}
    .ard-statement-company{font-size:22px;}
    .ard-statement-meta{font-size:11px;}
    .ard-statement-head-row{display:flex !important;align-items:center !important;justify-content:center !important;gap:10px !important;}
    .ard-statement-logo{width:86px !important;min-width:86px !important;text-align:left !important;margin:0 !important;}
    .ard-statement-logo img{max-width:78px !important;max-height:68px !important;}
    .ard-statement-head-main{text-align:center !important;}
    .ard-statement-title{text-align:center !important;}
    .statement-info-row{display:flex !important;gap:6px !important;}
    .statement-info-card{border:1px solid #d7dfec !important;page-break-inside:avoid !important;padding:8px !important;min-width:0 !important;flex:1 1 32% !important;}
    .statement-info-card h5{font-size:12px;margin-bottom:6px;padding-bottom:4px;}
    .statement-info-line{font-size:11px;margin-bottom:3px;}
    .table-responsive{overflow:visible !important;}
    .ard-statement-table{width:100% !important;table-layout:fixed !important;border-collapse:collapse !important;}
    .ard-statement-table thead{display:table-header-group !important;}
    .ard-statement-table tfoot{display:table-footer-group !important;}
    .ard-statement-table tr{page-break-inside:avoid !important;}
    .ard-statement-table th,.ard-statement-table td{font-size:10px !important;padding:4px 5px !important;line-height:1.25 !important;word-break:break-word !important;white-space:normal !important;}
    .ard-statement-table th:nth-child(1),.ard-statement-table td:nth-child(1){width:4% !important;}
    .ard-statement-table th:nth-child(2),.ard-statement-table td:nth-child(2){width:10% !important;}
    .ard-statement-table th:nth-child(3),.ard-statement-table td:nth-child(3){width:17% !important;}
    .ard-statement-table th:nth-child(4),.ard-statement-table td:nth-child(4){width:8% !important;}
    .ard-statement-table th:nth-child(6),.ard-statement-table td:nth-child(6){width:10% !important;}
    .ard-statement-table th:nth-child(7),.ard-statement-table td:nth-child(7){width:10% !important;}
    .ard-statement-table th:nth-child(8),.ard-statement-table td:nth-child(8){width:11% !important;}
    .statement-print-footer{font-size:10px !important;margin-top:6px !important;padding-top:5px !important;}
}
</style>

<div id="resultDiv" class="ard-statement-wrap">
    <div class="panel">
        <div class="panel-body">
            <div class="ard-statement-toolbar no-print">
                <div class="btn-group">
                    <?php echo $this->Html->link('<span class="fa fa-arrow-left"></span>&nbsp;'.__('Back To Ledgers'),array('controller'=>'Ardaccounts','action'=>'ledgers',$currentProjectId),array('escape'=>false,'class'=>'btn btn-default'));?>
                    <?php
                    $csvQuery = array(
                        'period' => $filterPeriod,
                        'start_date' => $filterStartDate,
                        'end_date' => $filterEndDate,
                        'export' => 'csv'
                    );
                    echo $this->Html->link('<span class="fa fa-file-text-o"></span>&nbsp;'.__('Export CSV'),array('controller'=>'Ardaccounts','action'=>'ledgerstatement',(int)$ledger['id'],$currentProjectId,'?' => $csvQuery),array('escape'=>false,'class'=>'btn btn-success'));
                    ?>
                    <button type="button" class="btn btn-danger" onclick="downloadStatementPdf();"><span class="fa fa-file-pdf-o"></span>&nbsp;<?php echo __('Download PDF');?></button>
                </div>
                <button type="button" class="btn btn-primary" onclick="window.print();"><span class="fa fa-print"></span> <?php echo __('Print');?></button>
            </div>
            <form method="get" class="statement-filter-form no-print" action="<?php echo $this->Html->url(array('controller'=>'Ardaccounts','action'=>'ledgerstatement',(int)$ledger['id'],$currentProjectId));?>">
                <div class="field-wrap">
                    <label><?php echo __('Filter');?></label>
                    <select name="period" class="form-control">
                        <option value="daily" <?php echo ($filterPeriod === 'daily') ? 'selected="selected"' : '';?>><?php echo __('Daily');?></option>
                        <option value="monthly" <?php echo ($filterPeriod === 'monthly') ? 'selected="selected"' : '';?>><?php echo __('Monthly');?></option>
                        <option value="yearly" <?php echo ($filterPeriod === 'yearly') ? 'selected="selected"' : '';?>><?php echo __('Yearly');?></option>
                        <option value="lifetime" <?php echo ($filterPeriod === 'lifetime') ? 'selected="selected"' : '';?>><?php echo __('Lifetime');?></option>
                        <option value="custom" <?php echo ($filterPeriod === 'custom') ? 'selected="selected"' : '';?>><?php echo __('Custom');?></option>
                    </select>
                </div>
                <div class="field-wrap">
                    <label><?php echo __('Start Date');?></label>
                    <input type="date" name="start_date" class="form-control" value="<?php echo h($filterStartDate);?>">
                </div>
                <div class="field-wrap">
                    <label><?php echo __('End Date');?></label>
                    <input type="date" name="end_date" class="form-control" value="<?php echo h($filterEndDate);?>">
                </div>
                <div class="field-wrap">
                    <button type="submit" class="btn btn-info"><?php echo __('Filter');?></button>
                </div>
            </form>

            <div id="statementPrintable">
            <div class="ard-statement-card">
                <div class="ard-statement-head">
                    <div class="ard-statement-head-row">
                        <div class="ard-statement-logo">
                            <?php if($agencyLogo !== ''){ echo $this->Html->image($agencyLogo, array('alt' => $companyName)); } ?>
                        </div>
                        <div class="ard-statement-head-main">
                            <div class="ard-statement-company"><?php echo h($companyName);?></div>
                            <div class="ard-statement-meta">
                                <?php echo __('Address');?>: <?php echo h($agencyAddress);?>
                                &nbsp;|&nbsp;<?php echo __('Phone');?>: <?php echo h($agencyPhone);?>
                                &nbsp;|&nbsp;<?php echo __('Mobile');?>: <?php echo h($agencyMobile);?>
                                &nbsp;|&nbsp;<?php echo __('Email');?>: <?php echo h($agencyEmail);?>
                            </div>
                            <div class="ard-statement-title"><?php echo __('Ledger Statement');?> | <?php echo h($ledgerTypeLabel);?></div>
                        </div>
                    </div>
                </div>
                <div class="statement-selected-filter">
                    <strong><?php echo __('Selected Filter');?>:</strong>
                    <?php echo h($selectedFilterLabel);?>
                    <?php if($showCustomRange){?>
                        &nbsp;|&nbsp;<strong><?php echo __('Start');?>:</strong> <?php echo h($selectedStartLabel);?>
                        &nbsp;|&nbsp;<strong><?php echo __('End');?>:</strong> <?php echo h($selectedEndLabel);?>
                    <?php }?>
                </div>

                <div class="statement-info-row">
                    <div class="statement-info-card">
                        <h5><?php echo __('Ledger Details');?></h5>
                        <div class="statement-info-line"><span class="field-key"><?php echo __('Ledger Name');?>:</span><span class="field-val"><?php echo h(isset($ledger['name']) ? $ledger['name'] : '-');?></span></div>
                        <div class="statement-info-line"><span class="field-key"><?php echo __('Type');?>:</span><span class="field-val"><?php echo h(isset($ledger['type']) && strtolower((string)$ledger['type'])==='expense' ? __('Expense') : __('Bank/Asset'));?></span></div>
                        <div class="statement-info-line"><span class="field-key"><?php echo __('Category');?>:</span><span class="field-val"><?php echo h(!empty($ledger['category']) ? $ledger['category'] : '-');?></span></div>
                    </div>
                    <div class="statement-info-card">
                        <h5><?php echo __('Account Details');?></h5>
                        <div class="statement-info-line"><span class="field-key"><?php echo __('Account No');?>:</span><span class="field-val"><?php echo h(!empty($ledger['account_no']) ? $ledger['account_no'] : '-');?></span></div>
                        <div class="statement-info-line"><span class="field-key"><?php echo __('Mobile');?>:</span><span class="field-val"><?php echo h(!empty($ledger['mobile_no']) ? $ledger['mobile_no'] : '-');?></span></div>
                        <div class="statement-info-line"><span class="field-key"><?php echo __('Address');?>:</span><span class="field-val"><?php echo h(!empty($ledger['address']) ? $ledger['address'] : '-');?></span></div>
                    </div>
                    <div class="statement-info-card">
                        <h5><?php echo __('Balance Summary');?></h5>
                        <div class="statement-info-line"><span class="field-key"><?php echo __('Total Debit');?>:</span><span class="field-val"><?php echo $currencySymbol.$this->Number->format($debitTotal);?></span></div>
                        <div class="statement-info-line"><span class="field-key"><?php echo __('Total Credit');?>:</span><span class="field-val"><?php echo $currencySymbol.$this->Number->format($creditTotal);?></span></div>
                        <div class="statement-info-line"><span class="field-key"><?php echo __('Current Balance');?>:</span><span class="field-val"><?php echo $currencySymbol.$this->Number->format(isset($ledger['balance']) ? $ledger['balance'] : 0);?></span></div>
                    </div>
                </div>
            </div>

            <h4 style="margin:6px 0 10px;color:#253d60;"><?php echo __('Transaction History');?></h4>
            <div class="table-responsive">
                <table class="table table-bordered ard-statement-table">
                    <thead>
                        <tr>
                            <th style="width:55px;"><?php echo __('#');?></th>
                            <th style="width:120px;"><?php echo __('Date');?></th>
                            <th style="width:150px;"><?php echo __('Reference');?></th>
                            <th style="width:120px;"><?php echo __('Type');?></th>
                            <th><?php echo __('Description');?></th>
                            <th class="text-right" style="width:130px;"><?php echo __('Debit');?></th>
                            <th class="text-right" style="width:130px;"><?php echo __('Credit');?></th>
                            <th class="text-right" style="width:140px;"><?php echo __('Balance');?></th>
                            <th class="no-print no-pdf" style="width:150px;"><?php echo __('Action');?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($statementRows)){?>
                            <tr>
                                <td colspan="9" class="text-center"><?php echo __('No transactions found for this ledger.');?></td>
                            </tr>
                        <?php }else{
                            $rowNo = 0;
                            foreach($statementRows as $tx){
                                $rowNo++;
                                $txDate = '-';
                                if(!empty($tx['entry_date']) && $tx['entry_date'] !== '0000-00-00')
                                {
                                    $ts = strtotime((string)$tx['entry_date']);
                                    if($ts !== false)
                                    {
                                        $txDate = date('d-M-Y', $ts);
                                    }
                                }
                        ?>
                        <tr>
                            <td><?php echo $rowNo;?></td>
                            <td><?php echo h($txDate);?></td>
                            <td><?php echo h(!empty($tx['ref_no']) ? $tx['ref_no'] : '-');?></td>
                            <td><?php echo h(ucwords(str_replace('_',' ',(string)$tx['type'])));?></td>
                            <td><?php echo h(!empty($tx['description']) ? $tx['description'] : '-');?></td>
                            <td class="text-right"><?php echo ((float)$tx['debit_amount'] > 0) ? $currencySymbol.$this->Number->format($tx['debit_amount']) : '-';?></td>
                            <td class="text-right"><?php echo ((float)$tx['credit_amount'] > 0) ? $currencySymbol.$this->Number->format($tx['credit_amount']) : '-';?></td>
                            <td class="text-right"><?php echo $currencySymbol.$this->Number->format($tx['running_balance']);?></td>
                            <td class="no-print no-pdf">
                                <?php
                                $txId = isset($tx['id']) ? (int)$tx['id'] : 0;
                                if($txId > 0)
                                {
                                    echo '<div class="ard-action-icons">';
                                    echo $this->Html->link(
                                        '<span class="fa fa-print"></span>',
                                        array('controller' => 'Ardaccounts', 'action' => 'printvoucher', $txId, $currentProjectId),
                                        array('class' => 'btn btn-xs btn-info', 'target' => '_blank', 'escape' => false, 'title' => __('Print'), 'aria-label' => __('Print'))
                                    );                                    
                                    echo $this->Html->link(
                                        '<span class="fa fa-eye"></span>',
                                        array('controller' => 'Ardaccounts', 'action' => 'transactions', $currentProjectId, 'edit:'.$txId, '?' => array('mode' => 'view')),
                                        array('class' => 'btn btn-xs btn-default', 'escape' => false, 'title' => __('View'), 'aria-label' => __('View'))
                                    );                                    
                                    echo $this->Html->link(
                                        '<span class="fa fa-pencil"></span>',
                                        array('controller' => 'Ardaccounts', 'action' => 'transactions', $currentProjectId, 'edit:'.$txId),
                                        array('class' => 'btn btn-xs btn-warning', 'escape' => false, 'title' => __('Edit'), 'aria-label' => __('Edit'))
                                    );                                    
                                    echo $this->Html->link(
                                        '<span class="fa fa-trash"></span>',
                                        array('controller' => 'Ardaccounts', 'action' => 'deletetransaction', $txId, $currentProjectId),
                                        array('class' => 'btn btn-xs btn-danger', 'onclick' => "return confirm('".__('Are you sure?')."');", 'escape' => false, 'title' => __('Delete'), 'aria-label' => __('Delete'))
                                    );
                                    echo '</div>';
                                }
                                else
                                {
                                    echo '-';
                                }
                                ?>
                            </td>
                        </tr>
                        <?php }}?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5" class="text-right"><?php echo __('Totals');?></td>
                            <td class="text-right"><?php echo $currencySymbol.$this->Number->format($debitTotal);?></td>
                            <td class="text-right"><?php echo $currencySymbol.$this->Number->format($creditTotal);?></td>
                            <td class="text-right"><?php echo $currencySymbol.$this->Number->format($closingBalance);?></td>
                            <td class="no-print no-pdf"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="statement-print-footer">
                <?php echo __('Print Date / Time');?>: <?php echo h($printDateTime);?>
            </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
function downloadStatementPdf()
{
    var target = document.getElementById('statementPrintable');
    if(!target)
    {
        return;
    }
    if(typeof html2pdf === 'undefined')
    {
        window.print();
        return;
    }
    target.classList.add('is-pdf-export');
    var options = {
        margin: [6, 6, 8, 6],
        filename: 'ledger_statement_<?php echo h($safeLedgerName);?>_<?php echo date('Ymd_His');?>.pdf',
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2, useCORS: true, scrollX: 0, scrollY: 0 },
        jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' },
        pagebreak: { mode: ['css', 'legacy'] }
    };
    html2pdf().set(options).from(target).save().then(function(){
        target.classList.remove('is-pdf-export');
    }).catch(function(){
        target.classList.remove('is-pdf-export');
    });
}
</script>
