<?php
$clientName = !empty($deal['Client']['name']) ? h($deal['Client']['name']) : 'Valued Client';
$clientFather = !empty($deal['Client']['father_name']) ? h($deal['Client']['father_name']) : (!empty($deal['Client']['guardian_name']) ? h($deal['Client']['guardian_name']) : '-');
$clientCnic = !empty($deal['Client']['cnic']) ? h($deal['Client']['cnic']) : (!empty($deal['Client']['nic']) ? h($deal['Client']['nic']) : '-');
$clientPhone = !empty($deal['Client']['mobile']) ? h($deal['Client']['mobile']) : (!empty($deal['Client']['phone']) ? h($deal['Client']['phone']) : '-');
$clientEmail = !empty($deal['Client']['email']) ? h($deal['Client']['email']) : '-';
$clientAddress = !empty($deal['Client']['address']) ? h($deal['Client']['address']) : 'Islamabad / Rawalpindi, Pakistan';

$propName = !empty($deal['Property']['name']) ? h($deal['Property']['name']) : 'White Villas Executive';
$propType = !empty($deal['Property']['type']) ? h($deal['Property']['type']) : 'Residential Villa';
$propArea = trim((!empty($deal['Property']['area']) ? $deal['Property']['area'] : '3 Marla') . ' ' . (!empty($deal['Unit']['name']) ? $deal['Unit']['name'] : ''));
$plotNo = !empty($deal['Property']['plot_no']) ? h($deal['Property']['plot_no']) : (!empty($deal['Property']['unit_no']) ? h($deal['Property']['unit_no']) : 'Executive Unit');
$blockName = !empty($deal['Property']['block']) ? h($deal['Property']['block']) : (!empty($deal['Property']['sector']) ? h($deal['Property']['sector']) : 'Block A');
$dealCode = !empty($deal['Deal']['invoice_no']) ? h($deal['Deal']['invoice_no']) : (!empty($deal['Deal']['code']) ? h($deal['Deal']['code']) : 'WV-DEAL-'.$deal['Deal']['id']);
$dealDate = !empty($deal['Deal']['date']) ? date('d-M-Y', strtotime($deal['Deal']['date'])) : (!empty($deal['Deal']['created']) ? date('d-M-Y', strtotime($deal['Deal']['created'])) : '-');
$planName = !empty($deal['Plan']['name']) ? h($deal['Plan']['name']) : '40-Month Easy Installment Plan';

$projectName = !empty($deal['Project']['name']) ? h($deal['Project']['name']) : 'WHITE VILLAS';
$projectAddress = !empty($deal['Project']['address']) ? h($deal['Project']['address']) : 'Main Islamabad Expressway / GT Road, Rawalpindi/Islamabad';
$projectPhone = !empty($deal['Project']['phone']) ? h($deal['Project']['phone']) : '+92 300 0000000';
$projectEmail = !empty($deal['Project']['email']) ? h($deal['Project']['email']) : 'info@whitevillas.com.pk';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Official Statement of Account - <?php echo $dealCode; ?> - <?php echo $clientName; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #0F172A;
            --primary-light: #1E293B;
            --accent: #FFD700;
            --accent-crimson: #FF2A5F;
            --success: #059669;
            --warning: #D97706;
            --text-dark: #0F172A;
            --text-muted: #64748B;
            --border: #E2E8F0;
            --bg-card: #F8FAFC;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #ECEFF1;
            color: var(--text-dark);
            line-height: 1.5;
            padding: 30px 15px;
            font-size: 13px;
        }

        .statement-container {
            max-width: 960px;
            margin: 0 auto;
            background: #FFFFFF;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
            padding: 40px;
            position: relative;
            overflow: hidden;
            border: 1px solid #E2E8F0;
        }

        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 85px;
            font-weight: 900;
            color: rgba(15, 23, 42, 0.025);
            pointer-events: none;
            text-transform: uppercase;
            letter-spacing: 12px;
            z-index: 0;
            white-space: nowrap;
        }

        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid var(--primary);
            padding-bottom: 20px;
            margin-bottom: 24px;
            position: relative;
            z-index: 1;
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .logo-img {
            width: 72px;
            height: 72px;
            border-radius: 12px;
            object-fit: cover;
            border: 2px solid var(--accent);
        }

        .company-info h1 {
            font-size: 22px;
            font-weight: 800;
            color: var(--primary);
            letter-spacing: 0.5px;
            line-height: 1.2;
        }

        .company-info .subtitle {
            font-size: 11.5px;
            color: var(--text-muted);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 4px;
        }

        .company-info .meta-line {
            font-size: 11px;
            color: #475569;
        }

        .statement-title-badge {
            text-align: right;
        }

        .doc-badge {
            display: inline-block;
            background: var(--primary);
            color: #FFFFFF;
            font-size: 12px;
            font-weight: 800;
            padding: 6px 14px;
            border-radius: 6px;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        .doc-meta-row {
            font-size: 11.5px;
            color: var(--text-muted);
            margin-bottom: 2px;
        }

        .doc-meta-row strong {
            color: var(--primary);
            font-family: 'JetBrains Mono', monospace;
        }

        /* 2-Column Info Grid */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
            margin-bottom: 24px;
            position: relative;
            z-index: 1;
        }

        .info-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 16px;
        }

        .info-card-header {
            font-size: 11px;
            font-weight: 800;
            color: var(--primary);
            text-transform: uppercase;
            letter-spacing: 0.8px;
            border-bottom: 1px dashed #CBD5E1;
            padding-bottom: 8px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            font-size: 11.5px;
            margin-bottom: 6px;
        }

        .info-row:last-child {
            margin-bottom: 0;
        }

        .info-row .label {
            color: var(--text-muted);
            font-weight: 500;
        }

        .info-row .val {
            font-weight: 700;
            color: var(--primary);
            text-align: right;
        }

        /* Financial Snapshot Banner */
        .financial-summary {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 24px;
            position: relative;
            z-index: 1;
        }

        .summary-stat-box {
            background: #FFFFFF;
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 14px;
            text-align: center;
            border-top: 4px solid var(--primary);
        }

        .summary-stat-box.paid {
            border-top-color: var(--success);
            background: #F0FDF4;
        }

        .summary-stat-box.balance {
            border-top-color: #0288D1;
            background: #F0F9FF;
        }

        .summary-stat-box.overdue {
            border-top-color: var(--accent-crimson);
            background: #FFF1F2;
        }

        .stat-label {
            font-size: 10px;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .stat-amount {
            font-size: 16px;
            font-weight: 800;
            color: var(--primary);
            font-family: 'JetBrains Mono', monospace;
        }

        .summary-stat-box.paid .stat-amount {
            color: var(--success);
        }

        .summary-stat-box.overdue .stat-amount {
            color: var(--accent-crimson);
        }

        .progress-bar-wrap {
            margin-top: 8px;
            background: #E2E8F0;
            border-radius: 999px;
            height: 6px;
            overflow: hidden;
        }

        .progress-bar-fill {
            background: var(--success);
            height: 100%;
            border-radius: 999px;
        }

        /* Ledger Table */
        .ledger-section {
            margin-bottom: 24px;
            position: relative;
            z-index: 1;
        }

        .section-heading {
            font-size: 12px;
            font-weight: 800;
            color: var(--primary);
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .ledger-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            background: #FFFFFF;
            border: 1px solid var(--border);
            border-radius: 8px;
            overflow: hidden;
        }

        .ledger-table th {
            background: var(--primary);
            color: #FFFFFF;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 9.5px;
            letter-spacing: 0.5px;
            padding: 9px 8px;
            text-align: left;
        }

        .ledger-table td {
            padding: 8px 8px;
            border-bottom: 1px solid #EDF2F7;
            color: #334155;
        }

        .ledger-table tr:nth-child(even) td {
            background-color: #F8FAFC;
        }

        .ledger-table tr:last-child td {
            border-bottom: none;
        }

        .badge {
            display: inline-block;
            padding: 2px 7px;
            border-radius: 4px;
            font-size: 8.5px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .badge-paid {
            background: #DCFCE7;
            color: #166534;
        }

        .badge-partial {
            background: #FEF3C7;
            color: #92400E;
        }

        .badge-pending {
            background: #F1F5F9;
            color: #475569;
        }

        .badge-overdue {
            background: #FFE4E6;
            color: #9F1239;
        }

        .num-cell {
            text-align: right;
            font-family: 'JetBrains Mono', monospace;
            font-weight: 600;
        }

        /* Footer & Verification */
        .statement-footer {
            display: grid;
            grid-template-columns: 140px 1fr 180px;
            gap: 20px;
            align-items: center;
            border-top: 1px solid var(--border);
            padding-top: 20px;
            margin-top: 20px;
            position: relative;
            z-index: 1;
        }

        .qr-code-box {
            text-align: center;
        }

        .qr-code-img {
            width: 110px;
            height: 110px;
            border: 1px solid #CBD5E1;
            border-radius: 8px;
            padding: 4px;
            background: #FFFFFF;
        }

        .qr-caption {
            font-size: 8.5px;
            color: var(--text-muted);
            margin-top: 4px;
            font-weight: 600;
        }

        .bank-info-box {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 10px;
        }

        .bank-info-title {
            font-weight: 800;
            color: var(--primary);
            text-transform: uppercase;
            margin-bottom: 4px;
            font-size: 9.5px;
        }

        .signature-box {
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            height: 100%;
        }

        .signature-line {
            border-top: 1.5px solid var(--primary);
            margin-top: 50px;
            padding-top: 6px;
            font-size: 10px;
            font-weight: 800;
            color: var(--primary);
            text-transform: uppercase;
        }

        .disclaimer-text {
            font-size: 8.5px;
            color: #94A3B8;
            margin-top: 16px;
            text-align: center;
            line-height: 1.4;
        }

        /* Action Toolbar */
        .action-toolbar {
            position: fixed;
            bottom: 24px;
            right: 24px;
            display: flex;
            gap: 10px;
            z-index: 999;
        }

        .btn-action {
            background: var(--primary);
            color: #FFFFFF;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 6px 18px rgba(15, 23, 42, 0.25);
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .btn-action:hover {
            background: #1E293B;
            transform: translateY(-2px);
        }

        .btn-action.btn-secondary {
            background: #FFFFFF;
            color: var(--primary);
            border: 1px solid var(--border);
        }

        @media print {
            body {
                background: #FFFFFF;
                padding: 0;
                font-size: 11px;
            }

            .statement-container {
                box-shadow: none;
                border: none;
                padding: 0;
                max-width: 100%;
            }

            .action-toolbar {
                display: none !important;
            }

            .watermark {
                display: block;
            }

            @page {
                size: A4 portrait;
                margin: 12mm 12mm 12mm 12mm;
            }

            .ledger-table tr {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>

    <!-- Floating Action Toolbar -->
    <div class="action-toolbar">
        <button class="btn-action" onclick="window.print();">
            🖨️ Print / Save as PDF
        </button>
        <button class="btn-action btn-secondary" onclick="window.close();">
            ✕ Close
        </button>
    </div>

    <div class="statement-container">
        <div class="watermark">WHITE VILLAS</div>

        <!-- Header -->
        <div class="header-top">
            <div class="logo-section">
                <img src="https://whitevillas.com.pk/img/logo.png" alt="White Villas" class="logo-img" onerror="this.src='https://portal.whitevillas.com.pk/img/logo.png';">
                <div class="company-info">
                    <h1><?php echo $projectName; ?></h1>
                    <div class="subtitle">Official Statement of Account & Ledger</div>
                    <div class="meta-line">📍 <?php echo $projectAddress; ?></div>
                    <div class="meta-line">📞 <?php echo $projectPhone; ?> | ✉️ <?php echo $projectEmail; ?></div>
                </div>
            </div>
            <div class="statement-title-badge">
                <div class="doc-badge">Official Ledger Statement</div>
                <div class="doc-meta-row">Statement No: <strong><?php echo $statementNo; ?></strong></div>
                <div class="doc-meta-row">Issue Date: <strong><?php echo $statementDate; ?></strong></div>
                <div class="doc-meta-row">Deal Code: <strong><?php echo $dealCode; ?></strong></div>
            </div>
        </div>

        <!-- 2-Column Info Grid -->
        <div class="info-grid">
            <div class="info-card">
                <div class="info-card-header">
                    <span>👤 Client Particulars</span>
                    <span style="font-size:9.5px;color:var(--text-muted);">ID #<?php echo $deal['Client']['id']; ?></span>
                </div>
                <div class="info-row">
                    <span class="label">Allottee Name:</span>
                    <span class="val"><?php echo $clientName; ?></span>
                </div>
                <div class="info-row">
                    <span class="label">Father / Guardian:</span>
                    <span class="val"><?php echo $clientFather; ?></span>
                </div>
                <div class="info-row">
                    <span class="label">CNIC / Passport:</span>
                    <span class="val"><?php echo $clientCnic; ?></span>
                </div>
                <div class="info-row">
                    <span class="label">Registered Phone:</span>
                    <span class="val"><?php echo $clientPhone; ?></span>
                </div>
                <div class="info-row">
                    <span class="label">Mailing Address:</span>
                    <span class="val" style="max-width:60%;"><?php echo $clientAddress; ?></span>
                </div>
            </div>

            <div class="info-card">
                <div class="info-card-header">
                    <span>🏡 Property & Allotment Profile</span>
                    <span style="font-size:9.5px;color:var(--text-muted);"><?php echo $deal['Deal']['deal_status']; ?></span>
                </div>
                <div class="info-row">
                    <span class="label">Property / Unit:</span>
                    <span class="val"><?php echo $propName; ?></span>
                </div>
                <div class="info-row">
                    <span class="label">Category / Area:</span>
                    <span class="val"><?php echo $propArea; ?> (<?php echo $propType; ?>)</span>
                </div>
                <div class="info-row">
                    <span class="label">Block / Sector:</span>
                    <span class="val"><?php echo $blockName; ?> (Plot #<?php echo $plotNo; ?>)</span>
                </div>
                <div class="info-row">
                    <span class="label">Booking Date:</span>
                    <span class="val"><?php echo $dealDate; ?></span>
                </div>
                <div class="info-row">
                    <span class="label">Payment Plan:</span>
                    <span class="val"><?php echo $planName; ?></span>
                </div>
            </div>
        </div>

        <!-- Financial Summary Snapshot -->
        <div class="financial-summary">
            <div class="summary-stat-box">
                <div class="stat-label">Total Contract Value</div>
                <div class="stat-amount">Rs <?php echo number_format($totalDealAmount); ?></div>
            </div>
            <div class="summary-stat-box paid">
                <div class="stat-label">Total Amount Paid</div>
                <div class="stat-amount">Rs <?php echo number_format($totalPaidAmount); ?></div>
                <div class="progress-bar-wrap">
                    <div class="progress-bar-fill" style="width: <?php echo $paidPercentage; ?>%;"></div>
                </div>
            </div>
            <div class="summary-stat-box balance">
                <div class="stat-label">Remaining Balance</div>
                <div class="stat-amount">Rs <?php echo number_format($balanceAmount); ?></div>
            </div>
            <div class="summary-stat-box overdue">
                <div class="stat-label">Overdue Balance</div>
                <div class="stat-amount"><?php echo ($overdueAmount > 0) ? 'Rs ' . number_format($overdueAmount) : 'NIL (Up to Date)'; ?></div>
            </div>
        </div>

        <!-- Itemized Installment & Payment Ledger Table -->
        <div class="ledger-section">
            <div class="section-heading">
                <span>📋 Consolidated Installment Schedule & Transactions</span>
                <span style="font-size:10px;color:var(--text-muted);font-weight:600;">Total Receipts Issued: <?php echo count($payments); ?></span>
            </div>

            <table class="ledger-table">
                <thead>
                    <tr>
                        <th style="width: 30px; text-align: center;">#</th>
                        <th>Milestone / Description</th>
                        <th style="width: 80px;">Due Date</th>
                        <th class="num-cell" style="width: 95px;">Scheduled (Rs)</th>
                        <th class="num-cell" style="width: 95px;">Paid (Rs)</th>
                        <th class="num-cell" style="width: 95px;">Pending (Rs)</th>
                        <th style="width: 75px; text-align: center;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $sr = 1;
                    if (!empty($scheduleItems)):
                        foreach ($scheduleItems as $item):
                            $badgeClass = 'badge-pending';
                            if ($item['status'] === 'Paid') $badgeClass = 'badge-paid';
                            elseif ($item['status'] === 'Partial') $badgeClass = 'badge-partial';
                            elseif ($item['status'] === 'Overdue') $badgeClass = 'badge-overdue';

                            $dueDateFormatted = !empty($item['due_date']) ? date('d-M-Y', strtotime($item['due_date'])) : '-';
                    ?>
                    <tr>
                        <td style="text-align: center; color: var(--text-muted); font-weight: 700;"><?php echo $sr++; ?></td>
                        <td style="font-weight: 600; color: var(--primary);"><?php echo h($item['name']); ?></td>
                        <td style="color: #475569; font-size: 10px;"><?php echo $dueDateFormatted; ?></td>
                        <td class="num-cell"><?php echo number_format($item['amount']); ?></td>
                        <td class="num-cell" style="color: var(--success);"><?php echo number_format($item['paid_amount']); ?></td>
                        <td class="num-cell" style="<?php echo ($item['pending_amount'] > 0) ? 'color: var(--accent-crimson); font-weight:700;' : 'color: #94A3B8;'; ?>">
                            <?php echo number_format($item['pending_amount']); ?>
                        </td>
                        <td style="text-align: center;">
                            <span class="badge <?php echo $badgeClass; ?>"><?php echo $item['status']; ?></span>
                        </td>
                    </tr>
                    <?php 
                        endforeach;
                    else:
                    ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 20px; color: var(--text-muted);">
                            No installment schedule recorded for this deal.
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Verified Payment Receipts Section -->
        <?php if (!empty($payments)): ?>
        <div class="ledger-section" style="margin-top: 16px;">
            <div class="section-heading">
                <span>🧾 Official Payment Receipts Record</span>
            </div>
            <table class="ledger-table">
                <thead>
                    <tr>
                        <th style="width: 30px; text-align: center;">#</th>
                        <th>Receipt No</th>
                        <th>Transaction Date</th>
                        <th>Payment Mode</th>
                        <th>Instrument / Cheque #</th>
                        <th class="num-cell">Paid Amount (Rs)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $psr = 1;
                    foreach ($payments as $p):
                        $rec = $p['MyPayment'];
                        $recNo = !empty($rec['receipt_no']) ? h($rec['receipt_no']) : 'R-'.$rec['id'];
                        $paidDate = !empty($rec['paid_date']) ? date('d-M-Y', strtotime($rec['paid_date'])) : (!empty($rec['date']) ? date('d-M-Y', strtotime($rec['date'])) : '-');
                        $mode = !empty($rec['payment_mode']) ? h($rec['payment_mode']) : (!empty($p['Paymenttype']['name']) ? h($p['Paymenttype']['name']) : 'Bank Transfer');
                        $ref = !empty($rec['cheque_no']) ? h($rec['cheque_no']) : (!empty($rec['transaction_id']) ? h($rec['transaction_id']) : 'Online Verified');
                        $amt = isset($rec['total_amount']) ? (float)$rec['total_amount'] : (isset($rec['amount']) ? (float)$rec['amount'] : 0);
                    ?>
                    <tr>
                        <td style="text-align: center; color: var(--text-muted); font-weight: 700;"><?php echo $psr++; ?></td>
                        <td style="font-weight: 700; color: var(--primary); font-family: 'JetBrains Mono', monospace;"><?php echo $recNo; ?></td>
                        <td><?php echo $paidDate; ?></td>
                        <td><?php echo $mode; ?></td>
                        <td style="font-size: 10px; color: #475569;"><?php echo $ref; ?></td>
                        <td class="num-cell" style="color: var(--success); font-weight: 700;"><?php echo number_format($amt); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <!-- Footer & Verification Section -->
        <div class="statement-footer">
            <div class="qr-code-box">
                <img src="<?php echo $qrUrl; ?>" alt="Verification QR Code" class="qr-code-img">
                <div class="qr-caption">Scan to Verify Authenticity</div>
            </div>

            <div class="bank-info-box">
                <div class="bank-info-title">🏦 Bank Wire / Deposit Details for Payment:</div>
                <div><strong>Account Title:</strong> WHITE VILLAS (PVT) LTD</div>
                <div><strong>Bank Name:</strong> Meezan Bank Ltd / Faysal Bank Islamic</div>
                <div><strong>Account No:</strong> 0102-0103498821 | <strong>IBAN:</strong> PK89MEZN0001020103498821</div>
                <div style="margin-top: 4px; font-size: 9px; color: var(--text-muted);">Please mention Deal Code <strong><?php echo $dealCode; ?></strong> in deposit narration.</div>
            </div>

            <div class="signature-box">
                <div class="signature-line">Authorized Signatory & Seal<br><span style="font-size:8px;font-weight:500;color:var(--text-muted);">White Villas Accounts Dept</span></div>
            </div>
        </div>

        <div class="disclaimer-text">
            This is an official system-generated Statement of Account certified by White Villas Property Management. For official verifications, banking, or tax inquiries, visit <?php echo Router::url('/', true); ?> or contact accounts at <?php echo $projectPhone; ?>.
        </div>
    </div>

</body>
</html>
