<?php
$currencySymbol = isset($currency) ? $currency : 'Rs ';
$dateValue = !empty($post['DealsPayment']['payment_date']) ? date('d-M-Y', strtotime($post['DealsPayment']['payment_date'])) : '-';
$taxLabel = '-';
if(!empty($post['Tax']['name']))
{
    $taxLabel = $post['Tax']['name'];
    if(isset($post['Tax']['percent']) && $post['Tax']['percent'] !== '')
    {
        $taxLabel .= ' (' . $post['Tax']['percent'] . '%)';
    }
}
$paymentName = !empty($post['PlansPayment']['name']) ? $post['PlansPayment']['name'] : (!empty($post['DealsPayment']['name']) ? $post['DealsPayment']['name'] : '-');
$slipUrl = '';
if(!empty($slipRelPath))
{
    $slipUrl = rtrim($this->request->webroot, '/') . '/img/' . ltrim($slipRelPath, '/');
}
$isPdf = false;
if(!empty($slipRelPath))
{
    $ext = strtolower(pathinfo($slipRelPath, PATHINFO_EXTENSION));
    $isPdf = ($ext === 'pdf') || (stripos((string)$slipMime, 'pdf') !== false);
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?php echo __('Payment Slip Details'); ?></title>
    <style type="text/css">
        body{font-family:Arial,sans-serif;font-size:13px;color:#1a1a1a;background:#f5f7fb;margin:0;padding:16px;}
        .wrap{max-width:1080px;margin:0 auto;background:#fff;border:1px solid #d9e2ef;border-radius:6px;overflow:hidden;}
        .head{padding:14px 16px;border-bottom:1px solid #e5edf7;display:flex;justify-content:space-between;align-items:center;gap:10px;}
        .title{font-size:20px;font-weight:700;}
        .btn{display:inline-block;padding:7px 12px;border-radius:4px;text-decoration:none;border:0;cursor:pointer;font-size:12px;font-weight:600;}
        .btn-print{background:#1570a6;color:#fff;}
        .btn-close{background:#c0392b;color:#fff;}
        .body{padding:14px 16px;}
        .grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
        .card{border:1px solid #d9e2ef;border-radius:5px;}
        .card h4{margin:0;padding:10px 12px;font-size:14px;background:#f7fafc;border-bottom:1px solid #e5edf7;}
        .table{width:100%;border-collapse:collapse;}
        .table td{padding:8px 10px;border-bottom:1px solid #eff4fa;vertical-align:top;}
        .table td:first-child{width:44%;font-weight:700;color:#21314d;}
        .table tr:last-child td{border-bottom:none;}
        .slip-box{padding:10px;}
        .slip-img{max-width:100%;height:auto;display:block;border:1px solid #d9e2ef;border-radius:4px;}
        .slip-file{display:inline-block;margin-top:8px;}
        .slip-frame{width:100%;height:520px;border:1px solid #d9e2ef;border-radius:4px;}
        .slip-empty{background:#fff7e6;border:1px solid #f0c36d;border-radius:5px;color:#7a4b00;font-weight:700;margin-top:8px;padding:12px;}
        @media print{
            body{background:#fff;padding:0;}
            .wrap{border:none;}
            .no-print{display:none !important;}
            .grid{grid-template-columns:1fr 1fr;}
        }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="head">
            <div class="title"><?php echo __('Payment Slip Details'); ?></div>
            <div class="no-print">
                <button class="btn btn-print" onclick="window.print();"><?php echo __('Print'); ?></button>
                <button class="btn btn-close" onclick="window.close();"><?php echo __('Close'); ?></button>
            </div>
        </div>
        <div class="body">
            <div class="grid">
                <div class="card">
                    <h4><?php echo __('Payment Information'); ?></h4>
                    <table class="table">
                        <tr><td><?php echo __('Receipt Number'); ?></td><td><?php echo !empty($post['DealsPayment']['receipt_no']) ? h($post['DealsPayment']['receipt_no']) : '-'; ?></td></tr>
                        <tr><td><?php echo __('Payment Name'); ?></td><td><?php echo h($paymentName); ?></td></tr>
                        <tr><td><?php echo __('Amount'); ?></td><td><?php echo h($currencySymbol) . $this->Number->format((float)$post['DealsPayment']['emi']); ?></td></tr>
                        <tr><td><?php echo __('Extra/Late Amount'); ?></td><td><?php echo h($currencySymbol) . $this->Number->format((float)$post['DealsPayment']['extra_payment']); ?></td></tr>
                        <tr><td><?php echo __('Payable Amount'); ?></td><td><?php echo h($currencySymbol) . $this->Number->format((float)$post['DealsPayment']['total_amount']); ?></td></tr>
                        <tr><td><?php echo __('Tax'); ?></td><td><?php echo h($taxLabel); ?></td></tr>
                        <tr><td><?php echo __('Taxable Amount'); ?></td><td><?php echo h($currencySymbol) . $this->Number->format((float)$post['DealsPayment']['tax_amount']); ?></td></tr>
                        <tr><td><?php echo __('Gross Amount'); ?></td><td><?php echo h($currencySymbol) . $this->Number->format((float)$post['DealsPayment']['amount']); ?></td></tr>
                        <tr><td><?php echo __('Payment Type'); ?></td><td><?php echo !empty($post['Paymenttype']['name']) ? h($post['Paymenttype']['name']) : '-'; ?></td></tr>
                        <tr><td><?php echo __('Receipt Account'); ?></td><td><?php echo !empty($post['Ardledger']['name']) ? h($post['Ardledger']['name']) : '-'; ?></td></tr>
                        <tr><td><?php echo __('Payment Date'); ?></td><td><?php echo h($dateValue); ?></td></tr>
                        <tr><td><?php echo __('Transaction Reference'); ?></td><td><?php echo !empty($post['DealsPayment']['remarks']) ? h($post['DealsPayment']['remarks']) : '-'; ?></td></tr>
                    </table>
                </div>
                <div class="card">
                    <h4><?php echo __('Deal & Client'); ?></h4>
                    <table class="table">
                        <tr><td><?php echo __('Deal Invoice'); ?></td><td><?php echo !empty($post['Deal']['invoice_no']) ? h($post['Deal']['invoice_no']) : '-'; ?></td></tr>
                        <tr><td><?php echo __('Property / File'); ?></td><td><?php echo !empty($post['Property']['name']) ? h($post['Property']['name']) : '-'; ?></td></tr>
                        <tr><td><?php echo __('Client Name'); ?></td><td><?php echo !empty($post['Client']['name']) ? h($post['Client']['name']) : '-'; ?></td></tr>
                        <?php if(!empty($post['Client']['fathername'])): ?>
                        <tr><td><?php echo !empty($post['Client']['relation_type']) ? h($post['Client']['relation_type']) : __('Father / Guardian'); ?></td><td><?php echo h($post['Client']['fathername']); ?></td></tr>
                        <?php endif; ?>
                        <tr><td><?php echo __('Client Mobile'); ?></td><td><?php echo !empty($post['Client']['phone']) ? h($post['Client']['phone']) : '-'; ?></td></tr>
                    </table>
                    <div class="slip-box">
                        <strong><?php echo __('Payment Slip'); ?>:</strong>
                        <?php if($slipUrl !== ''): ?>
                            <?php if($slipIsImage): ?>
                                <a href="<?php echo h($slipUrl); ?>" target="_blank">
                                    <img src="<?php echo h($slipUrl); ?>" alt="<?php echo __('Payment Slip'); ?>" class="slip-img">
                                </a>
                            <?php elseif($isPdf): ?>
                                <iframe src="<?php echo h($slipUrl); ?>" class="slip-frame"></iframe>
                                <a class="slip-file" href="<?php echo h($slipUrl); ?>" target="_blank"><?php echo __('Open PDF in new tab'); ?></a>
                            <?php else: ?>
                                <a class="slip-file" href="<?php echo h($slipUrl); ?>" target="_blank"><?php echo __('Open attached file'); ?></a>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="slip-empty"><?php echo !empty($slipMessage) ? h($slipMessage) : __('Payment slip not uploaded.'); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
