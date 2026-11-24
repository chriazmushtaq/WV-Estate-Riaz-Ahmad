<?php
$dealCount = count((array)$deals);
$receiptCount = count((array)$receipts);
$letterCount = count((array)$letters);
$vaultCount = count((array)$vaultDocuments);
?>
<style type="text/css">
.wv-doc-page{background:#f4f7fb;margin:-10px -5px 0;padding:14px;}
.wv-doc-hero{background:linear-gradient(135deg,#07152a 0%,#12355a 72%,#ff0a46 165%);border-radius:10px;color:#fff;margin-bottom:12px;padding:18px;}
.wv-doc-hero h1{color:#fff!important;font-size:27px;font-weight:900;margin:0 0 5px;}
.wv-doc-hero p{color:#eef6ff!important;font-size:12px;font-weight:800;margin:0;}
.wv-doc-stats{display:grid;gap:10px;grid-template-columns:repeat(4,minmax(0,1fr));margin-bottom:12px;}
.wv-doc-stat,.wv-doc-card{background:#fff;border:1px solid #dfe9f5;border-radius:8px;box-shadow:0 8px 20px rgba(15,23,42,.06);padding:12px;}
.wv-doc-stat span{color:#64748b;display:block;font-size:10px;font-weight:900;text-transform:uppercase;}
.wv-doc-stat strong{color:#ff0a46;display:block;font-size:24px;font-weight:900;margin-top:4px;}
.wv-doc-card{margin-bottom:12px;}
.wv-doc-card h3{color:#07152a;font-size:16px;font-weight:900;margin:0 0 10px;}
.wv-doc-table th{background:#07152a!important;color:#fff!important;font-size:11px!important;text-transform:uppercase;}
.wv-doc-table td{font-size:12px!important;vertical-align:middle!important;}
.wv-doc-actions .btn{border:0;border-radius:7px;font-size:11px;font-weight:900;margin:1px;padding:6px 9px;}
@media(max-width:767px){.wv-doc-stats{grid-template-columns:repeat(2,minmax(0,1fr));}.wv-doc-page{margin:0;padding:10px;}.wv-doc-table{min-width:760px;}}
</style>
<div class="wv-doc-page">
    <?php echo $this->Session->flash(); ?>
    <div class="wv-doc-hero">
        <h1><?php echo __('My Documents'); ?></h1>
        <p><?php echo __('Download invoices, receipts, ledger PDFs, issued letters, refund clearance, agreements, and uploaded vault files.'); ?></p>
    </div>
    <div class="wv-doc-stats">
        <div class="wv-doc-stat"><span><?php echo __('Deals'); ?></span><strong><?php echo (int)$dealCount; ?></strong></div>
        <div class="wv-doc-stat"><span><?php echo __('Receipts'); ?></span><strong><?php echo (int)$receiptCount; ?></strong></div>
        <div class="wv-doc-stat"><span><?php echo __('Letters'); ?></span><strong><?php echo (int)$letterCount; ?></strong></div>
        <div class="wv-doc-stat"><span><?php echo __('Vault Files'); ?></span><strong><?php echo (int)$vaultCount; ?></strong></div>
    </div>

    <div class="wv-doc-card">
        <h3><?php echo __('Deal Documents'); ?></h3>
        <div class="table-responsive">
            <table class="table table-bordered table-striped wv-doc-table">
                <thead><tr><th><?php echo __('Deal'); ?></th><th><?php echo __('Property'); ?></th><th><?php echo __('Invoice'); ?></th><th><?php echo __('Ledger'); ?></th></tr></thead>
                <tbody>
                <?php if(!empty($deals)){ foreach($deals as $row){ $deal=$row['MyDeal']; $property=!empty($row['Property']) ? $row['Property'] : array(); $dealId=(int)$deal['id']; ?>
                    <tr>
                        <td><?php echo h(!empty($deal['invoice_no']) ? $deal['invoice_no'] : 'Deal #'.$dealId); ?></td>
                        <td><?php echo h(!empty($property['name']) ? $property['name'] : '-'); ?></td>
                        <td class="wv-doc-actions"><?php echo $this->Html->link('<span class="fa fa-print"></span> '.__('Invoice'), array('controller'=>'MyDeals','action'=>'printinvoice',$dealId), array('escape'=>false,'target'=>'_blank','class'=>'btn btn-info')); ?></td>
                        <td class="wv-doc-actions"><?php echo $this->Html->link('<span class="fa fa-file-pdf-o"></span> '.__('Ledger PDF'), array('controller'=>'MyDeals','action'=>'ledgerpdf',$dealId), array('escape'=>false,'target'=>'_blank','class'=>'btn btn-danger')); ?></td>
                    </tr>
                <?php }}else{ ?><tr><td colspan="4" class="text-center"><?php echo __('No deals found.'); ?></td></tr><?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="wv-doc-card">
        <h3><?php echo __('Receipts'); ?></h3>
        <div class="table-responsive">
            <table class="table table-bordered table-striped wv-doc-table">
                <thead><tr><th><?php echo __('Receipt'); ?></th><th><?php echo __('Deal'); ?></th><th><?php echo __('Payment Date'); ?></th><th><?php echo __('Amount'); ?></th><th><?php echo __('Action'); ?></th></tr></thead>
                <tbody>
                <?php if(!empty($receipts)){ foreach($receipts as $row){ $payment=$row['MyPayment']; $deal=$row['Deal']; ?>
                    <tr>
                        <td><?php echo h(!empty($payment['receipt_no']) ? $payment['receipt_no'] : 'Receipt #'.$payment['id']); ?></td>
                        <td><?php echo h(!empty($deal['invoice_no']) ? $deal['invoice_no'] : '-'); ?></td>
                        <td><?php echo !empty($payment['payment_date']) ? h(date('d-M-Y',strtotime($payment['payment_date']))) : '-'; ?></td>
                        <td><?php echo $currency.$this->Number->format(isset($payment['amount']) ? $payment['amount'] : 0); ?></td>
                        <td class="wv-doc-actions"><?php echo $this->Html->link('<span class="fa fa-print"></span> '.__('Receipt'), array('controller'=>'MyPayments','action'=>'printreceipt',(int)$payment['id']), array('escape'=>false,'target'=>'_blank','class'=>'btn btn-info')); ?></td>
                    </tr>
                <?php }}else{ ?><tr><td colspan="5" class="text-center"><?php echo __('No receipts found.'); ?></td></tr><?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="wv-doc-card">
        <h3><?php echo __('Issued Letters'); ?></h3>
        <div class="table-responsive">
            <table class="table table-bordered table-striped wv-doc-table">
                <thead><tr><th><?php echo __('Reference'); ?></th><th><?php echo __('Deal'); ?></th><th><?php echo __('Title'); ?></th><th><?php echo __('Date'); ?></th><th><?php echo __('Action'); ?></th></tr></thead>
                <tbody>
                <?php if(!empty($letters)){ foreach($letters as $row){ $letter=$row['Realestateletter']; $deal=$row['Deal']; ?>
                    <tr>
                        <td><?php echo h(!empty($letter['reference_no']) ? $letter['reference_no'] : 'LTR-'.$letter['id']); ?></td>
                        <td><?php echo h(!empty($deal['invoice_no']) ? $deal['invoice_no'] : '-'); ?></td>
                        <td><?php echo h(!empty($letter['title']) ? $letter['title'] : '-'); ?></td>
                        <td><?php echo !empty($letter['letter_date']) ? h(date('d-M-Y',strtotime($letter['letter_date']))) : '-'; ?></td>
                        <td class="wv-doc-actions"><?php echo $this->Html->link('<span class="fa fa-print"></span> '.__('Print'), array('controller'=>'MyDeals','action'=>'printletter',(int)$letter['deal_id'],(int)$letter['id']), array('escape'=>false,'target'=>'_blank','class'=>'btn btn-primary')); ?></td>
                    </tr>
                <?php }}else{ ?><tr><td colspan="5" class="text-center"><?php echo __('No issued letters found.'); ?></td></tr><?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="wv-doc-card">
        <h3><?php echo __('Secure Vault Files'); ?></h3>
        <div class="table-responsive">
            <table class="table table-bordered table-striped wv-doc-table">
                <thead><tr><th><?php echo __('Type'); ?></th><th><?php echo __('Deal'); ?></th><th><?php echo __('Date'); ?></th><th><?php echo __('File'); ?></th><th><?php echo __('Action'); ?></th></tr></thead>
                <tbody>
                <?php if(!empty($vaultDocuments)){ foreach($vaultDocuments as $row){ $doc=$row['DealDocument']; $deal=$row['Deal']; ?>
                    <tr>
                        <td><?php echo h(!empty($doc['document_type']) ? $doc['document_type'] : 'Document'); ?></td>
                        <td><?php echo h(!empty($deal['invoice_no']) ? $deal['invoice_no'] : '-'); ?></td>
                        <td><?php echo h(!empty($doc['document_date']) ? $doc['document_date'] : '-'); ?></td>
                        <td><?php echo h(!empty($doc['photo']) ? $doc['photo'] : '-'); ?></td>
                        <td class="wv-doc-actions">
                            <?php echo $this->Html->link('<span class="fa fa-eye"></span> '.__('View'), array('controller'=>'MyDocuments','action'=>'viewfile',(int)$doc['id']), array('escape'=>false,'target'=>'_blank','class'=>'btn btn-info')); ?>
                            <?php echo $this->Html->link('<span class="fa fa-download"></span> '.__('Download'), array('controller'=>'MyDocuments','action'=>'download',(int)$doc['id']), array('escape'=>false,'class'=>'btn btn-success')); ?>
                        </td>
                    </tr>
                <?php }}else{ ?><tr><td colspan="5" class="text-center"><?php echo __('No vault files found.'); ?></td></tr><?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
