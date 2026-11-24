<?php
$fmt = function($amount) { return 'Rs ' . number_format((float)$amount, 0); };
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Daily Cash Closing</title>
    <style>
        @page{size:A4 portrait;margin:10mm}
        body{font-family:Arial,Helvetica,sans-serif;color:#000;margin:0}
        .sheet{max-width:760px;margin:0 auto}
        .head{text-align:center;border-bottom:2px solid #000;padding-bottom:8px;margin-bottom:12px}
        .head h1{margin:0;font-size:22px;text-transform:uppercase}
        .head p{margin:4px 0 0;font-size:12px}
        table{width:100%;border-collapse:collapse;font-size:13px}
        th,td{border:1px solid #000;padding:8px;text-align:left}
        th{background:#f1f1f1}
        .sign{display:flex;justify-content:space-between;margin-top:42px;font-size:12px}
        .line{border-top:1px solid #000;width:210px;text-align:center;padding-top:5px}
        .no-print{text-align:right;margin-bottom:10px}
        .no-print button{padding:8px 12px;font-weight:bold}
        @media print{.no-print{display:none}}
    </style>
</head>
<body>
<div class="sheet">
    <div class="no-print"><button onclick="window.print()">Print</button></div>
    <div class="head">
        <h1><?php echo h($agencyName);?></h1>
        <p><?php echo __('Daily Cash Closing');?> | <?php echo h(date('d-M-Y', strtotime($closing['closing_date'])));?> | <?php echo h($ledgerName);?></p>
    </div>
    <table>
        <tr><th><?php echo __('Opening Cash');?></th><td><?php echo $fmt($closing['opening_cash']);?></td></tr>
        <tr><th><?php echo __('Received Cash');?></th><td><?php echo $fmt($closing['received_cash']);?></td></tr>
        <tr><th><?php echo __('Expenses');?></th><td><?php echo $fmt($closing['expense_cash']);?></td></tr>
        <tr><th><?php echo __('Bank Deposit');?></th><td><?php echo $fmt($closing['bank_deposit']);?></td></tr>
        <tr><th><?php echo __('Adjustment');?></th><td><?php echo $fmt($closing['adjustment_amount']);?></td></tr>
        <tr><th><?php echo __('Closing Cash');?></th><td><strong><?php echo $fmt($closing['closing_cash']);?></strong></td></tr>
        <tr><th><?php echo __('Counted Cash');?></th><td><?php echo $fmt($closing['counted_cash']);?></td></tr>
        <tr><th><?php echo __('Difference');?></th><td><?php echo $fmt($closing['difference_amount']);?></td></tr>
        <tr><th><?php echo __('Status');?></th><td><?php echo h($closing['status']);?></td></tr>
        <tr><th><?php echo __('Note');?></th><td><?php echo h($closing['note']);?></td></tr>
    </table>
    <div class="sign">
        <div class="line"><?php echo __('Cashier');?></div>
        <div class="line"><?php echo __('Accounts Manager');?></div>
    </div>
</div>
</body>
</html>
