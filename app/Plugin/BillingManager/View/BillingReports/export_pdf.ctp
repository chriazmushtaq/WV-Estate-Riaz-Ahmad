<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Official Defaulters & Outstanding Arrears Report - <?php echo h($societyName); ?></title>
    <style>
        @page {
            size: A4 portrait;
            margin: 10mm;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10pt;
            color: #000000;
            background: #ffffff;
            margin: 0;
            padding: 0;
        }
        .letterhead {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2.5px solid #0f172a;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .letterhead h1 {
            font-size: 18px;
            font-weight: 900;
            margin: 0;
            color: #0f172a;
            text-transform: uppercase;
        }
        .letterhead .sub-text {
            font-size: 11px;
            color: #334155;
            font-weight: 700;
            margin-top: 3px;
        }
        .doc-title {
            text-align: right;
        }
        .doc-title .title {
            font-size: 14px;
            font-weight: 900;
            color: #dc2626;
            text-transform: uppercase;
        }
        .doc-title .date {
            font-size: 11px;
            color: #475569;
            margin-top: 2px;
            font-weight: 700;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5pt;
            margin-bottom: 15px;
        }
        th {
            background: #f1f5f9;
            color: #0f172a;
            font-weight: 800;
            text-transform: uppercase;
            padding: 6px 8px;
            border: 1px solid #0f172a;
            font-size: 9pt;
        }
        td {
            padding: 5px 8px;
            border: 1px solid #0f172a;
            vertical-align: middle;
        }
        tr:nth-child(even) {
            background: #fafafa;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #0f172a;
            display: flex;
            justify-content: space-between;
            font-size: 9.5pt;
            page-break-inside: avoid;
        }
    </style>
</head>
<body onload="window.print()">

    <div class="letterhead">
        <div>
            <h1><?php echo h($societyName); ?></h1>
            <div class="sub-text">
                <?php echo h($societyAddress); ?> | Phone: <?php echo h($societyPhone); ?> | Email: <?php echo h($societyEmail); ?>
            </div>
        </div>
        <div class="doc-title">
            <div class="title">DEFAULTERS & ARREARS RECOVERY REPORT</div>
            <div class="date">DATE: <?php echo strtoupper(date('d-M-Y')); ?></div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Bill #</th>
                <th>Consumer Profile</th>
                <th>Phone No</th>
                <th>Property Location</th>
                <th>Period</th>
                <th>Due Date</th>
                <th>Overdue</th>
                <th class="text-right">Arrears (PKR)</th>
            </tr>
        </thead>
        <tbody>
            <?php if(empty($defaultersList)){ ?>
                <tr><td colspan="9" class="text-center" style="padding:20px;font-weight:bold;">No outstanding defaulter records found.</td></tr>
            <?php } else { $idx = 1; $totalArrearsSum = 0; foreach($defaultersList as $def){ 
                $totalArrearsSum += $def['balance_amount'];
            ?>
                <tr>
                    <td class="text-center"><strong><?php echo $idx++; ?></strong></td>
                    <td><strong><?php echo h($def['bill_number']); ?></strong></td>
                    <td><strong><?php echo h($def['consumer_name']); ?></strong></td>
                    <td><?php echo h($def['consumer_phone']); ?></td>
                    <td><?php echo h($def['property_title']); ?></td>
                    <td class="text-center"><?php echo h($def['formatted_month']); ?></td>
                    <td class="text-center"><?php echo h($def['due_date']); ?></td>
                    <td class="text-center"><strong><?php echo $def['overdue_days']; ?> Days</strong></td>
                    <td class="text-right"><strong>PKR <?php echo number_format($def['balance_amount'], 2); ?></strong></td>
                </tr>
            <?php } ?>
                <tr style="background:#f1f5f9;font-weight:bold;">
                    <td colspan="8" class="text-right">TOTAL OUTSTANDING ARREARS:</td>
                    <td class="text-right" style="color:#dc2626;font-size:11pt;">PKR <?php echo number_format($totalArrearsSum, 2); ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <div class="footer">
        <div style="width:45%;">
            <div style="font-size:8.5pt;color:#475569;margin-bottom:30px;">
                * Official computer-generated defaulters recovery summary for Accounts & Recovery Dept.
            </div>
            <div style="border-top:1.5px solid #000;padding-top:3px;font-weight:bold;">
                PREPARED BY (ACCOUNTS OFFICER)
            </div>
        </div>
        <div style="width:45%;text-align:right;">
            <div style="font-size:8.5pt;color:#475569;margin-bottom:30px;">
                Official Society Stamp & Signature
            </div>
            <div style="border-top:1.5px solid #000;padding-top:3px;font-weight:bold;">
                AUTHORIZED RECOVERY MANAGER
            </div>
        </div>
    </div>

</body>
</html>
