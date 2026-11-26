<?php
$c = $bClient['BillingClient'];
$clientName = !empty($c['name']) ? $c['name'] : __('Valued Consumer');
$clientPhone = !empty($c['phone']) ? $c['phone'] : '-';
$clientAddress = !empty($c['address']) ? $c['address'] : '-';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('Utility Account Statement'); ?> - <?php echo h($clientName); ?></title>
    <style>
        @page {
            size: A4 portrait;
            margin: 10mm 10mm 10mm 10mm;
        }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
            font-size: 12px;
            color: #0f172a;
            margin: 0;
            padding: 20px;
            background: #ffffff;
            touch-action: auto;
        }
        .statement-table-wrap {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            touch-action: pan-x pan-y;
            width: 100%;
            margin-bottom: 20px;
        }
        .statement-table-wrap table {
            min-width: 760px;
            margin-bottom: 0;
        }
        .statement-header {
            border-bottom: 2px solid #0f172a;
            padding-bottom: 12px;
            margin-bottom: 16px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }
        .statement-header h1 {
            margin: 0;
            font-size: 20px;
            font-weight: 900;
            color: #0f172a;
            text-transform: uppercase;
        }
        .consumer-box {
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 16px;
            display: flex;
            justify-content: space-between;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11.5px;
            margin-bottom: 20px;
        }
        table th {
            background: #0f172a;
            color: #ffffff;
            font-size: 10.5px;
            font-weight: 800;
            text-transform: uppercase;
            padding: 8px 10px;
            border: 1px solid #1e293b;
            text-align: left;
        }
        table td {
            padding: 8px 10px;
            border: 1px solid #cbd5e1;
            vertical-align: middle;
        }
        table tr:nth-child(even) {
            background: #f8fafc;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .badge-paid { color: #065f46; font-weight: 800; }
        .badge-unpaid { color: #991b1b; font-weight: 800; }
        .badge-partial { color: #92400e; font-weight: 800; }
        .no-print {
            margin-bottom: 16px;
        }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0 !important; }
        }
    </style>
</head>
<body>

    <div class="no-print" style="text-align:right;">
        <button onclick="window.print()" style="background:#0f172a;color:#fff;border:none;padding:8px 18px;font-weight:bold;border-radius:6px;cursor:pointer;">
            Print Statement
        </button>
    </div>

    <div class="statement-header">
        <div>
            <h1>White Villas Housing Complex</h1>
            <div style="font-size:11px;color:#64748b;font-weight:700;">Utility Billing Account Statement & Ledger</div>
        </div>
        <div style="text-align:right;font-size:11px;color:#64748b;">
            <div><strong>Date:</strong> <?php echo date('d-M-Y h:i A'); ?></div>
            <div><strong>Consumer ID:</strong> #<?php echo (int)$bClient['BillingClient']['id']; ?></div>
        </div>
    </div>

    <div class="consumer-box">
        <div>
            <div style="font-size:10px;color:#64748b;font-weight:800;text-transform:uppercase;">Consumer Name</div>
            <strong style="font-size:14px;color:#0f172a;"><?php echo h($clientName); ?></strong>
            <div style="font-size:11px;color:#475569;margin-top:2px;">Phone: <?php echo h($clientPhone); ?></div>
        </div>
        <div style="text-align:right;">
            <div style="font-size:10px;color:#64748b;font-weight:800;text-transform:uppercase;">Assigned Units / Properties</div>
            <?php if(!empty($properties)){ foreach($properties as $p){ ?>
                <div style="font-weight:700;"><?php echo h($p['BillingProperty']['title']); ?> (<?php echo h($p['BillingProperty']['property_no']); ?>)</div>
            <?php } } else { echo '<div style="color:#94a3b8;">-</div>'; } ?>
        </div>
    </div>

    <div class="statement-table-wrap">
    <table>
        <thead>
            <tr>
                <th>Bill #</th>
                <th>Period</th>
                <th>Meter No</th>
                <th class="text-right">Units</th>
                <th class="text-right">Utility (PKR)</th>
                <th class="text-right">Maint. (PKR)</th>
                <th class="text-right">Arrears (PKR)</th>
                <th class="text-right">Total Bill (PKR)</th>
                <th class="text-right">Paid (PKR)</th>
                <th class="text-right">Balance (PKR)</th>
                <th class="text-center">Due Date</th>
                <th class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php 
                $totBill = 0.0; $totPaid = 0.0; $totBal = 0.0; $totUnits = 0.0;
                if(empty($bills)){ 
            ?>
                <tr><td colspan="12" class="text-center" style="padding:20px;color:#94a3b8;">No bills recorded on this account.</td></tr>
            <?php } else { foreach($bills as $bRow){ 
                $b = $bRow['BillingBill'];
                $pId = !empty($b['billing_property_id']) ? (int)$b['billing_property_id'] : 0;
                $uType = !empty($b['utility_type']) ? strtolower(trim((string)$b['utility_type'])) : 'electricity';
                $meterNo = isset($propMeterMap[$pId . '_' . $uType]) ? $propMeterMap[$pId . '_' . $uType]['meter_number'] : (!empty($metersMap) ? (reset($metersMap)['meter_number']) : ('#' . $pId));
                $st = strtolower(trim((string)$b['payment_status']));
                $totBill += (float)$b['total_amount'];
                $totPaid += (float)$b['paid_amount'];
                $totBal += (float)$b['balance_amount'];
                $totUnits += (float)$b['units_consumed'];
            ?>
                <tr>
                    <td><strong><?php echo h($b['bill_number']); ?></strong></td>
                    <td><?php echo h($b['billing_period']); ?></td>
                    <td><?php echo h($meterNo); ?></td>
                    <td class="text-right"><?php echo number_format((float)$b['units_consumed'], 1); ?></td>
                    <td class="text-right"><?php echo number_format((float)$b['consumption_amount'], 2); ?></td>
                    <td class="text-right"><?php echo number_format((float)$b['extra_charges_amount'], 2); ?></td>
                    <td class="text-right"><?php echo number_format((float)$b['arrears_amount'], 2); ?></td>
                    <td class="text-right"><strong><?php echo number_format((float)$b['total_amount'], 2); ?></strong></td>
                    <td class="text-right" style="color:#065f46;"><?php echo number_format((float)$b['paid_amount'], 2); ?></td>
                    <td class="text-right" style="color:<?php echo ((float)$b['balance_amount'] > 0) ? '#991b1b' : '#065f46'; ?>;font-weight:bold;">
                        <?php echo number_format((float)$b['balance_amount'], 2); ?>
                    </td>
                    <td class="text-center"><?php echo !empty($b['due_date']) ? date('d-M-Y', strtotime((string)$b['due_date'])) : '-'; ?></td>
                    <td class="text-center">
                        <?php if($st === 'paid'){ ?>
                            <span class="badge-paid">PAID</span>
                        <?php } elseif($st === 'partial'){ ?>
                            <span class="badge-partial">PARTIAL</span>
                        <?php } else { ?>
                            <span class="badge-unpaid">UNPAID</span>
                        <?php } ?>
                    </td>
                </tr>
            <?php } } ?>
        </tbody>
        <tfoot>
            <tr style="background:#f1f5f9;font-weight:bold;font-size:12px;">
                <td colspan="3">STATEMENT TOTALS</td>
                <td class="text-right"><?php echo number_format($totUnits, 1); ?></td>
                <td colspan="3"></td>
                <td class="text-right">PKR <?php echo number_format($totBill, 2); ?></td>
                <td class="text-right" style="color:#065f46;">PKR <?php echo number_format($totPaid, 2); ?></td>
                <td class="text-right" style="color:#991b1b;">PKR <?php echo number_format($totBal, 2); ?></td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>
    </div>

    <div style="font-size:10px;color:#94a3b8;border-top:1px solid #cbd5e1;padding-top:8px;text-align:center;">
        This is a computer-generated account statement from White Villas Management Information System. No physical signature required.
    </div>

</body>
</html>
