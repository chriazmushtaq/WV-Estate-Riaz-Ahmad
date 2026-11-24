<?php
$fmt = function($num) { return 'Rs. ' . number_format((float)$num, 2); };
$monthTs = strtotime((string)$payroll['payroll_month'] . '-01');
$monthName = ($monthTs !== false) ? date('F Y', $monthTs) : (string)$payroll['payroll_month'];
$statusUpper = strtoupper((string)$payroll['status']);
$statusColor = ($statusUpper === 'PAID') ? '#15803d' : (($statusUpper === 'PARTIAL') ? '#b45309' : '#b91c1c');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?php echo __('Salary Slip');?> - <?php echo h($employee['full_name']);?> (<?php echo h($monthName);?>)</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm 15mm;
        }
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        body {
            font-family: 'Segoe UI', Arial, Helvetica, sans-serif;
            color: #1e293b;
            background: #f8fafc;
            margin: 0;
            padding: 20px;
            font-size: 13px;
            line-height: 1.4;
        }
        .payslip-wrapper {
            max-width: 800px;
            margin: 0 auto;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        }
        .no-print {
            max-width: 800px;
            margin: 0 auto 15px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .btn-print {
            background: #0284c7;
            color: #ffffff;
            border: none;
            padding: 9px 18px;
            font-weight: 600;
            font-size: 14px;
            border-radius: 6px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-print:hover { background: #0369a1; }
        .btn-close {
            background: #64748b;
            color: #ffffff;
            border: none;
            padding: 9px 16px;
            font-weight: 600;
            font-size: 13px;
            border-radius: 6px;
            cursor: pointer;
        }
        .header-section {
            border-bottom: 2px solid #0284c7;
            padding-bottom: 14px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        .company-info h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.5px;
            text-transform: uppercase;
        }
        .company-info p {
            margin: 3px 0 0;
            font-size: 12px;
            color: #64748b;
        }
        .slip-badge {
            text-align: right;
        }
        .slip-title {
            font-size: 15px;
            font-weight: 700;
            color: #0284c7;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }
        .slip-meta {
            font-size: 11px;
            color: #64748b;
        }
        .status-pill {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 11px;
            color: #fff;
            margin-top: 5px;
        }
        .emp-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px 24px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 14px 18px;
            margin-bottom: 22px;
        }
        .emp-item {
            display: flex;
            font-size: 12.5px;
        }
        .emp-label {
            width: 140px;
            font-weight: 600;
            color: #64748b;
            flex-shrink: 0;
        }
        .emp-val {
            font-weight: 700;
            color: #0f172a;
            word-break: break-word;
        }
        .breakdown-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .breakdown-table th {
            background: #f1f5f9;
            color: #334155;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 11.5px;
            letter-spacing: 0.5px;
            border: 1px solid #cbd5e1;
            padding: 9px 12px;
        }
        .breakdown-table td {
            border: 1px solid #cbd5e1;
            padding: 8px 12px;
            font-size: 12.5px;
        }
        .breakdown-table tr.total-row td {
            background: #f8fafc;
            font-weight: 700;
            border-top: 2px solid #94a3b8;
        }
        .net-pay-banner {
            background: #f0f9ff;
            border: 2px solid #bae6fd;
            border-radius: 6px;
            padding: 14px 18px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 22px;
        }
        .net-pay-left .amount-label {
            font-size: 11.5px;
            text-transform: uppercase;
            font-weight: 700;
            color: #0369a1;
            letter-spacing: 0.5px;
        }
        .net-pay-left .amount-words {
            font-size: 13px;
            color: #334155;
            font-style: italic;
            margin-top: 2px;
        }
        .net-pay-right {
            font-size: 24px;
            font-weight: 800;
            color: #0284c7;
            text-align: right;
        }
        .payment-history {
            margin-bottom: 25px;
            background: #fafafa;
            border: 1px solid #e5e5e5;
            border-radius: 6px;
            padding: 12px 16px;
        }
        .payment-history h4 {
            margin: 0 0 8px 0;
            font-size: 12px;
            text-transform: uppercase;
            color: #525252;
            font-weight: 700;
        }
        .payment-history table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }
        .payment-history th, .payment-history td {
            border-bottom: 1px solid #e5e5e5;
            padding: 5px 8px;
            text-align: left;
        }
        .sign-section {
            margin-top: 50px;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            text-align: center;
        }
        .sign-line {
            border-top: 1px dashed #64748b;
            padding-top: 6px;
            font-size: 11px;
            font-weight: 600;
            color: #475569;
        }
        .footer-note {
            margin-top: 30px;
            text-align: center;
            font-size: 10.5px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
        }
        @media print {
            body { background: #ffffff; padding: 0; }
            .payslip-wrapper { border: none; box-shadow: none; padding: 0; max-width: 100%; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

<div class="no-print">
    <div>
        <a href="javascript:window.history.back()" class="btn-close">&larr; <?php echo __('Back');?></a>
    </div>
    <div>
        <button onclick="window.print()" class="btn-print">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
            <?php echo __('Print Salary Slip');?>
        </button>
    </div>
</div>

<div class="payslip-wrapper">
    <!-- Header -->
    <div class="header-section">
        <div class="company-info">
            <h1><?php echo h($agencyName);?></h1>
            <p><?php echo __('Property Development & Construction Accounting');?></p>
            <p style="font-size:11px;color:#94a3b8;"><?php echo __('Generated on:');?> <?php echo date('d-M-Y H:i');?></p>
        </div>
        <div class="slip-badge">
            <div class="slip-title"><?php echo __('Confidential Salary Slip');?></div>
            <div class="slip-meta"><strong><?php echo __('Pay Period:');?></strong> <?php echo h($monthName);?></div>
            <div class="slip-meta"><strong><?php echo __('Slip Ref:');?></strong> SLP-<?php echo str_replace('-', '', (string)$payroll['payroll_month']);?>-<?php echo sprintf('%04d', (int)$payroll['id']);?></div>
            <div>
                <span class="status-pill" style="background-color:<?php echo $statusColor;?>;"><?php echo h($statusUpper);?></span>
            </div>
        </div>
    </div>

    <!-- Employee Metadata Grid -->
    <div class="emp-grid">
        <div class="emp-item">
            <span class="emp-label"><?php echo __('Employee Name:');?></span>
            <span class="emp-val"><?php echo h($employee['full_name']);?></span>
        </div>
        <div class="emp-item">
            <span class="emp-label"><?php echo __('Employee Code:');?></span>
            <span class="emp-val"><?php echo !empty($employee['employee_code']) ? h($employee['employee_code']) : ('EMP-'.(int)$employee['id']);?></span>
        </div>
        <div class="emp-item">
            <span class="emp-label"><?php echo __('Designation:');?></span>
            <span class="emp-val"><?php echo !empty($employee['designation']) ? h($employee['designation']) : '-';?></span>
        </div>
        <div class="emp-item">
            <span class="emp-label"><?php echo __('Department:');?></span>
            <span class="emp-val"><?php echo !empty($employee['department']) ? h($employee['department']) : '-';?></span>
        </div>
        <div class="emp-item">
            <span class="emp-label"><?php echo __('CNIC No:');?></span>
            <span class="emp-val"><?php echo !empty($employee['cnic']) ? h($employee['cnic']) : '-';?></span>
        </div>
        <div class="emp-item">
            <span class="emp-label"><?php echo __('Joining Date:');?></span>
            <span class="emp-val"><?php echo (!empty($employee['joining_date']) && $employee['joining_date'] !== '0000-00-00') ? date('d-M-Y', strtotime((string)$employee['joining_date'])) : '-';?></span>
        </div>
        <div class="emp-item">
            <span class="emp-label"><?php echo __('Bank Name:');?></span>
            <span class="emp-val"><?php echo !empty($employee['bank_name']) ? h($employee['bank_name']) : '-';?></span>
        </div>
        <div class="emp-item">
            <span class="emp-label"><?php echo __('Bank Account #:');?></span>
            <span class="emp-val"><?php echo !empty($employee['bank_account_no']) ? h($employee['bank_account_no']) : '-';?></span>
        </div>
        <div class="emp-item">
            <span class="emp-label"><?php echo __('Working Days:');?></span>
            <span class="emp-val"><?php echo (float)$payroll['working_days'];?> <?php echo __('Days');?></span>
        </div>
        <div class="emp-item">
            <span class="emp-label"><?php echo __('Days Present / Paid:');?></span>
            <span class="emp-val"><?php echo (float)$payroll['present_days'];?> <?php echo __('Days');?></span>
        </div>
        <div class="emp-item">
            <span class="emp-label"><?php echo __('Overtime Hours:');?></span>
            <span class="emp-val"><?php echo (float)$payroll['overtime_hours'];?> <?php echo __('Hours');?></span>
        </div>
        <div class="emp-item">
            <span class="emp-label"><?php echo __('Base Salary Rate:');?></span>
            <span class="emp-val"><?php echo $fmt($employee['base_salary']);?></span>
        </div>
    </div>

    <!-- Earnings & Deductions Breakdown Table -->
    <table class="breakdown-table">
        <thead>
            <tr>
                <th style="width:50%;"><?php echo __('Earnings & Allowances');?></th>
                <th style="width:50%;"><?php echo __('Deductions & Adjustments');?></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="vertical-align:top; padding:0;">
                    <table style="width:100%; border-collapse:collapse;">
                        <tr>
                            <td style="border:none; padding:8px 12px;"><?php echo __('Earned Gross Salary');?></td>
                            <td style="border:none; padding:8px 12px; text-align:right; font-weight:600;"><?php echo $fmt($payroll['gross_pay']);?></td>
                        </tr>
                        <?php if((float)$payroll['overtime_hours'] > 0): ?>
                        <tr>
                            <td style="border:none; padding:8px 12px; color:#64748b; font-size:11.5px;"><?php echo __('Overtime');?> (<?php echo (float)$payroll['overtime_hours'];?> hrs)</td>
                            <td style="border:none; padding:8px 12px; text-align:right; color:#64748b; font-size:11.5px;"><?php echo __('Included in Gross');?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if((float)$payroll['commission_amount'] > 0): ?>
                        <tr>
                            <td style="border:none; padding:8px 12px;"><?php echo __('Sales Commissions Earned');?></td>
                            <td style="border:none; padding:8px 12px; text-align:right; font-weight:600; color:#15803d;"><?php echo $fmt($payroll['commission_amount']);?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if((float)$payroll['bonus'] > 0): ?>
                        <tr>
                            <td style="border:none; padding:8px 12px;"><?php echo __('Bonus / Performance Incentive');?></td>
                            <td style="border:none; padding:8px 12px; text-align:right; font-weight:600;"><?php echo $fmt($payroll['bonus']);?></td>
                        </tr>
                        <?php endif; ?>
                    </table>
                </td>
                <td style="vertical-align:top; padding:0;">
                    <table style="width:100%; border-collapse:collapse;">
                        <tr>
                            <td style="border:none; padding:8px 12px;"><?php echo __('Deductions (Advance / Absenteeism / Tax)');?></td>
                            <td style="border:none; padding:8px 12px; text-align:right; font-weight:600; color:#b91c1c;"><?php echo $fmt($payroll['deductions']);?></td>
                        </tr>
                        <?php if((float)$payroll['deductions'] <= 0): ?>
                        <tr>
                            <td style="border:none; padding:8px 12px; color:#94a3b8; font-style:italic;" colspan="2"><?php echo __('No deductions applied.');?></td>
                        </tr>
                        <?php endif; ?>
                    </table>
                </td>
            </tr>
            <tr class="total-row">
                <td>
                    <div style="display:flex; justify-content:space-between;">
                        <span><?php echo __('Total Earnings:');?></span>
                        <span><?php echo $fmt((float)$payroll['gross_pay'] + (float)$payroll['commission_amount'] + (float)$payroll['bonus']);?></span>
                    </div>
                </td>
                <td>
                    <div style="display:flex; justify-content:space-between;">
                        <span><?php echo __('Total Deductions:');?></span>
                        <span style="color:#b91c1c;"><?php echo $fmt($payroll['deductions']);?></span>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Net Pay Banner -->
    <div class="net-pay-banner">
        <div class="net-pay-left">
            <div class="amount-label"><?php echo __('Net Payable Salary');?></div>
            <div class="amount-words"><?php echo h($netPayWords);?></div>
        </div>
        <div class="net-pay-right">
            <?php echo $fmt($payroll['net_pay']);?>
        </div>
    </div>

    <!-- Payments Disbursed History (if any) -->
    <?php if(!empty($payments)): ?>
    <div class="payment-history">
        <h4><?php echo __('Disbursement Record');?></h4>
        <table>
            <thead>
                <tr>
                    <th><?php echo __('Payment Date');?></th>
                    <th><?php echo __('Voucher / Ref #');?></th>
                    <th><?php echo __('Method');?></th>
                    <th><?php echo __('Amount Disbursed');?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($payments as $pmt): ?>
                <tr>
                    <td><?php echo date('d-M-Y', strtotime((string)$pmt['Ardhpayment']['payment_date']));?></td>
                    <td><?php echo h($pmt['Ardhpayment']['reference_no']);?></td>
                    <td><?php echo h($pmt['Ardhpayment']['payment_method']);?></td>
                    <td style="font-weight:700; color:#15803d;"><?php echo $fmt($pmt['Ardhpayment']['amount']);?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div style="margin-top:8px; font-size:12px; display:flex; justify-content:space-between;">
            <span><strong><?php echo __('Total Disbursed:');?></strong> <?php echo $fmt($payroll['paid_amount']);?></span>
            <span><strong><?php echo __('Remaining Balance:');?></strong> <?php echo $fmt($payroll['due_amount']);?></span>
        </div>
    </div>
    <?php endif; ?>

    <!-- Signature Block -->
    <div class="sign-section">
        <div class="sign-col">
            <div class="sign-line"><?php echo __('Prepared By (HR)');?></div>
        </div>
        <div class="sign-col">
            <div class="sign-line"><?php echo __('Checked By (Accounts)');?></div>
        </div>
        <div class="sign-col">
            <div class="sign-line"><?php echo __('Approved By (Management)');?></div>
        </div>
        <div class="sign-col">
            <div class="sign-line"><?php echo __('Employee Signature');?></div>
        </div>
    </div>

    <div class="footer-note">
        <?php echo __('This is a system-generated document from ARD Accounting Module. Valid without physical stamp if electronically signed.');?>
    </div>
</div>

</body>
</html>
