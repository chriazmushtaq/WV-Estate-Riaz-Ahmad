<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo __('Expense Approval Voucher');?></title>
    <style type="text/css">
        body { background:#eef2f7; margin:0; padding:18px; }
        .print-toolbar { font-family:Arial,sans-serif; margin:0 auto 10px; max-width:820px; text-align:right; }
        .print-toolbar button { background:#2563eb; border:0; color:#fff; cursor:pointer; font-weight:700; padding:10px 15px; }
        @media print {
            body { background:#fff; padding:0; }
            .print-toolbar { display:none!important; }
            .eav { border-color:#94a3b8; margin:0 auto; page-break-inside:avoid; }
        }
    </style>
</head>
<body>
    <div class="print-toolbar"><button type="button" onclick="window.print();"><span aria-hidden="true">&#128438;</span> <?php echo __('Print');?></button></div>
    <?php echo $voucherHtml;?>
</body>
</html>
