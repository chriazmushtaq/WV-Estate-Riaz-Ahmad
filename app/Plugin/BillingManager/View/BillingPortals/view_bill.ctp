<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo __('Utility Bill') . ' - ' . h($bill['BillingBill']['bill_number']); ?></title>
    <style>
        <?php if(!empty($css)){ echo $css; } ?>
        @media print {
            .no-print { display: none !important; }
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
        .print-toolbar {
            background: #0f172a;
            color: #fff;
            padding: 12px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }
        .print-btn {
            background: #10b981;
            color: #fff;
            border: none;
            padding: 9px 20px;
            font-weight: bold;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
        }
    </style>
</head>
<body style="margin:0;padding:0;background:#f8fafc;">

    <div class="print-toolbar no-print">
        <div><strong>White Villas Housing Complex</strong> &mdash; Bill #<?php echo h($bill['BillingBill']['bill_number']); ?></div>
        <button class="print-btn" onclick="window.print();">🖨️ Print / Download PDF</button>
    </div>

    <div style="padding:20px;">
        <?php echo $rendered; ?>
    </div>

</body>
</html>
