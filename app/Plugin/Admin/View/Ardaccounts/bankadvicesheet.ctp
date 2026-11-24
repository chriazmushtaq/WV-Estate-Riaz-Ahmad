<?php
$fmt = function($num) { return 'Rs. ' . number_format((float)$num, 2); };
$monthTs = strtotime((string)$month . '-01');
$monthName = ($monthTs !== false) ? date('F Y', $monthTs) : (string)$month;
$todayDate = date('d-M-Y');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?php echo __('Bank Salary Transfer Advice');?> - <?php echo h($agencyName);?> (<?php echo h($monthName);?>)</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm 12mm;
        }
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        body {
            font-family: 'Segoe UI', Arial, Helvetica, sans-serif;
            color: #0f172a;
            background: #f8fafc;
            margin: 0;
            padding: 20px;
            font-size: 12px;
            line-height: 1.4;
        }
        .advice-wrapper {
            max-width: 1100px;
            margin: 0 auto;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        }
        .no-print {
            max-width: 1100px;
            margin: 0 auto 15px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            font-weight: 600;
            font-size: 13px;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            border: none;
        }
        .btn-print { background: #0284c7; color: #fff; }
        .btn-print:hover { background: #0369a1; }
        .btn-csv { background: #16a34a; color: #fff; margin-left: 8px; }
        .btn-csv:hover { background: #15803d; }
        .btn-back { background: #64748b; color: #fff; }
        .header-section {
            border-bottom: 2px solid #0f172a;
            padding-bottom: 12px;
            margin-bottom: 18px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        .company-info h1 {
            margin: 0;
            font-size: 22px;
            font-weight: 800;
            text-transform: uppercase;
            color: #0f172a;
        }
        .company-info p {
            margin: 3px 0 0;
            font-size: 11px;
            color: #64748b;
        }
        .advice-meta {
            text-align: right;
            font-size: 11px;
            color: #64748b;
        }
        .letter-head {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 14px 18px;
            margin-bottom: 18px;
            font-size: 12px;
            line-height: 1.6;
        }
        .letter-head strong { color: #0f172a; }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 20px;
        }
        .stat-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px 14px;
            text-align: center;
        }
        .stat-card h5 {
            margin: 0 0 4px;
            font-size: 10.5px;
            text-transform: uppercase;
            color: #64748b;
            font-weight: 700;
        }
        .stat-card strong {
            font-size: 18px;
            color: #0f172a;
        }
        .sheet-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11.5px;
            margin-bottom: 25px;
        }
        .sheet-table th {
            background: #f1f5f9;
            color: #334155;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 10.5px;
            letter-spacing: 0.3px;
            border: 1px solid #cbd5e1;
            padding: 8px 6px;
            text-align: left;
        }
        .sheet-table td {
            border: 1px solid #cbd5e1;
            padding: 7px 6px;
            text-align: left;
        }
        .sheet-table tr.total-row td {
            background: #f1f5f9;
            font-weight: 800;
            border-top: 2px solid #0f172a;
            font-size: 12px;
        }
        .amount-in-words {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 6px;
            padding: 10px 16px;
            margin-bottom: 30px;
            font-size: 12.5px;
        }
        .sign-row {
            margin-top: 60px;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 40px;
            text-align: center;
        }
        .sign-line {
            border-top: 1px solid #0f172a;
            padding-top: 6px;
            font-size: 11px;
            font-weight: 700;
            color: #334155;
        }
        @media print {
            body { background: #ffffff; padding: 0; }
            .advice-wrapper { border: none; box-shadow: none; padding: 0; max-width: 100%; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

<div class="no-print">
    <div>
        <a href="javascript:window.history.back()" class="btn-action btn-back">&larr; <?php echo __('Back');?></a>
    </div>
    <div>
        <button onclick="window.print()" class="btn-action btn-print">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
            <?php echo __('Print Advice Sheet');?>
        </button>
        <button onclick="exportToCsv()" class="btn-action btn-csv">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
            <?php echo __('Export to CSV / Excel');?>
        </button>
    </div>
</div>

<div class="advice-wrapper">
    <!-- Header -->
    <div class="header-section">
        <div class="company-info">
            <h1><?php echo h($agencyName);?></h1>
            <p><?php echo __('Corporate Salary Disbursement Advice Schedule');?></p>
        </div>
        <div class="advice-meta">
            <div><strong><?php echo __('Date:');?></strong> <?php echo h($todayDate);?></div>
            <div><strong><?php echo __('Payroll Period:');?></strong> <?php echo h($monthName);?></div>
            <div><strong><?php echo __('Ref:');?></strong> BDA-<?php echo str_replace('-', '', (string)$month);?>-<?php echo (int)$projectId;?></div>
        </div>
    </div>

    <!-- Formal Letter Memo -->
    <div class="letter-head">
        <div><strong><?php echo __('To:');?></strong> <?php echo __('The Branch Manager');?></div>
        <div><strong><?php echo __('Subject:');?></strong> <?php echo __('BULK SALARY DISBURSEMENT FOR THE MONTH OF');?> <span style="text-decoration:underline; font-weight:700;"><?php echo strtoupper(h($monthName));?></span></div>
        <div style="margin-top:6px;">
            <?php echo __('Dear Sir / Madam, please debit our Corporate Account and credit the net salary amounts listed below into the designated employee accounts:');?>
        </div>
    </div>

    <!-- Metric Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <h5><?php echo __('Total Employees');?></h5>
            <strong><?php echo count($rows);?></strong>
        </div>
        <div class="stat-card">
            <h5><?php echo __('Total Gross Salaries');?></h5>
            <strong style="color:#0f172a;"><?php echo $fmt($totalGross);?></strong>
        </div>
        <div class="stat-card">
            <h5><?php echo __('Total Deductions');?></h5>
            <strong style="color:#b91c1c;"><?php echo $fmt($totalDeductions);?></strong>
        </div>
        <div class="stat-card">
            <h5><?php echo __('Total Net Disbursement');?></h5>
            <strong style="color:#0284c7;"><?php echo $fmt($totalNet);?></strong>
        </div>
    </div>

    <!-- Detailed Table -->
    <table class="sheet-table" id="adviceTable">
        <thead>
            <tr>
                <th style="width:35px; text-align:center;">#</th>
                <th style="width:75px;"><?php echo __('Code');?></th>
                <th><?php echo __('Employee Name');?></th>
                <th><?php echo __('CNIC No.');?></th>
                <th><?php echo __('Designation');?></th>
                <th><?php echo __('Bank Name');?></th>
                <th><?php echo __('Account / IBAN #');?></th>
                <th style="text-align:right;"><?php echo __('Gross Pay');?></th>
                <th style="text-align:right;"><?php echo __('Deduction');?></th>
                <th style="text-align:right;"><?php echo __('Net Salary');?></th>
                <th style="width:65px; text-align:center;"><?php echo __('Status');?></th>
            </tr>
        </thead>
        <tbody>
            <?php if(empty($rows)): ?>
            <tr>
                <td colspan="11" style="text-align:center; padding:20px; color:#94a3b8; font-style:italic;">
                    <?php echo __('No payroll records found for this period.');?>
                </td>
            </tr>
            <?php else: ?>
                <?php $sr = 1; foreach($rows as $r): ?>
                <?php
                $emp = isset($r['Ardhremployee']) ? $r['Ardhremployee'] : array();
                $pay = isset($r['Ardhpayroll']) ? $r['Ardhpayroll'] : array();
                ?>
                <tr>
                    <td style="text-align:center;"><?php echo $sr++; ?></td>
                    <td><strong><?php echo !empty($emp['employee_code']) ? h($emp['employee_code']) : ('#'.$pay['employee_id']); ?></strong></td>
                    <td><strong><?php echo h(isset($emp['full_name']) ? $emp['full_name'] : '-'); ?></strong></td>
                    <td><?php echo !empty($emp['cnic']) ? h($emp['cnic']) : '-'; ?></td>
                    <td><?php echo !empty($emp['designation']) ? h($emp['designation']) : '-'; ?></td>
                    <td><?php echo !empty($emp['bank_name']) ? h($emp['bank_name']) : '<span style="color:#b91c1c;">(Cash/Not Set)</span>'; ?></td>
                    <td><strong><?php echo !empty($emp['bank_account_no']) ? h($emp['bank_account_no']) : '-'; ?></strong></td>
                    <td style="text-align:right;"><?php echo number_format((float)$pay['gross_pay'], 2); ?></td>
                    <td style="text-align:right; color:#b91c1c;"><?php echo number_format((float)$pay['deductions'], 2); ?></td>
                    <td style="text-align:right; font-weight:700; color:#0284c7;"><?php echo number_format((float)$pay['net_pay'], 2); ?></td>
                    <td style="text-align:center;">
                        <span style="font-size:10px; font-weight:700; text-transform:uppercase; color:<?php echo ($pay['status']==='paid')?'#15803d':'#b45309';?>;">
                            <?php echo h($pay['status']); ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="7" style="text-align:right;"><?php echo __('Grand Total (PKR):');?></td>
                <td style="text-align:right;"><?php echo number_format($totalGross, 2); ?></td>
                <td style="text-align:right; color:#b91c1c;"><?php echo number_format($totalDeductions, 2); ?></td>
                <td style="text-align:right; color:#0284c7;"><?php echo number_format($totalNet, 2); ?></td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <!-- Amount in Words -->
    <div class="amount-in-words">
        <strong><?php echo __('Total Net Amount in Words:');?></strong>
        <span style="font-style:italic; font-weight:700; color:#15803d;"><?php echo h($netPayWords);?></span>
    </div>

    <!-- Signatures -->
    <div class="sign-row">
        <div>
            <div class="sign-line"><?php echo __('Prepared By (HR / Payroll Officer)');?></div>
        </div>
        <div>
            <div class="sign-line"><?php echo __('Verified By (Head of Accounts)');?></div>
        </div>
        <div>
            <div class="sign-line"><?php echo __('Authorized Signatory (Director / CEO)');?></div>
        </div>
    </div>
</div>

<script>
function exportToCsv() {
    var table = document.getElementById("adviceTable");
    var rows = table.querySelectorAll("tr");
    var csv = [];
    for (var i = 0; i < rows.length; i++) {
        var row = [], cols = rows[i].querySelectorAll("td, th");
        for (var j = 0; j < cols.length; j++) {
            var data = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, '').replace(/(\s\s+)/gm, ' ');
            data = data.replace(/"/g, '""');
            row.push('"' + data + '"');
        }
        csv.push(row.join(","));
    }
    var csvString = csv.join("\n");
    var a = document.createElement('a');
    a.href = 'data:text/csv;charset=utf-8,' + encodeURIComponent(csvString);
    a.target = '_blank';
    a.download = 'Bank_Advice_<?php echo preg_replace("/[^A-Za-z0-9_-]/", "", $month);?>.csv';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
}
</script>

</body>
</html>
