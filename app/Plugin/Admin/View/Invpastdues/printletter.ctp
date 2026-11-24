<?php
$overdueInstallmentCount = isset($overdueInstallmentCount) ? (int)$overdueInstallmentCount : 1;
$totalOverdueAmountValue = isset($totalOverdueAmountValue) ? (float)$totalOverdueAmountValue : (float)$deal['Invpastdue']['amount'];
$autoPrint = isset($autoPrint) ? (bool)$autoPrint : true;
$enablePdfDownload = isset($enablePdfDownload) ? (bool)$enablePdfDownload : false;
$pdfFileName = isset($pdfFileName) ? $pdfFileName : 'Demand_Letter';
$total = $totalOverdueAmountValue;

$totalDueDisplay = isset($totalDueCount) && (int)$totalDueCount > 0 ? (int)$totalDueCount : $overdueInstallmentCount;
if ($totalDueDisplay < $overdueInstallmentCount) {
    $totalDueDisplay = $overdueInstallmentCount;
}

// Format Client Name
$clientRawName = trim(!empty($deal['Client']['name']) ? $deal['Client']['name'] : '');
$clientFormattedName = $clientRawName;
if (!empty($clientRawName)) {
    if (!preg_match('/^(Mr\.|Mrs\.|Ms\.|Dr\.)/i', $clientRawName)) {
        $clientFormattedName = 'Mr. ' . ucwords(strtolower($clientRawName));
    }
} else {
    $clientFormattedName = 'Valued Customer';
}

// Format Client Address
$clientAddress = trim(!empty($deal['Client']['address']) ? $deal['Client']['address'] : '');
if (empty($clientAddress)) {
    $parts = array();
    if (!empty($deal['Client']['district'])) $parts[] = $deal['Client']['district'];
    if (!empty($deal['Client']['state'])) $parts[] = $deal['Client']['state'];
    $clientAddress = implode(', ', $parts);
}
if (empty($clientAddress)) {
    $clientAddress = 'Lahore, Pakistan';
}

// Client Contact
$clientPhone = !empty($deal['Client']['phone']) ? trim($deal['Client']['phone']) : (!empty($deal['Client']['mobile']) ? trim($deal['Client']['mobile']) : '');

// Clean Ref No
$cleanRefNo = preg_replace('/\s*\/\s*/', '/', trim(!empty($deal['Deal']['invoice_no']) ? $deal['Deal']['invoice_no'] : 'WV-L'));
$pdfFileName = 'Demand_Letter_' . str_replace('/', '_', $cleanRefNo);

// Current Issue Date (Defaults to current server date)
$tz = !empty($siteTimezone) ? $siteTimezone : 'Asia/Karachi';
$issueDate = CakeTime::format('d-M-Y', CakeTime::convert(time(), $tz));
if (empty($issueDate)) {
    $issueDate = date('d-M-Y');
}

// Project & Property Display
$rawProjectName = trim(!empty($deal['Project']['name']) ? $deal['Project']['name'] : 'White Villas');
if (stripos($rawProjectName, 'White Villas') !== false && stripos($rawProjectName, 'Awami') !== false) {
    $projectDisplayName = 'White Villas — Awami Block';
} else {
    $projectDisplayName = $rawProjectName;
}

$propUnitName = trim(!empty($deal['Property']['name']) ? $deal['Property']['name'] : '');
$typeName = trim(!empty($deal['Type']['name']) ? $deal['Type']['name'] : '');
$propertyDisplayId = $propUnitName;
if (!empty($typeName)) {
    $propertyDisplayId .= ' (' . $typeName . ')';
}

// Agency / Project Header Details
$agencyData = isset($agencyData) && is_array($agencyData) ? $agencyData : array();

$agencyRawName = !empty($agencyData['name']) ? trim($agencyData['name']) : (!empty($deal['Project']['name']) ? trim($deal['Project']['name']) : 'WHITE VILLAS');
if (stripos($agencyRawName, 'White Villas') !== false) {
    $agencyBrandName = 'WHITE VILLAS';
    $agencySubtitle = 'A Project by Sabir Construction';
} else {
    $agencyBrandName = strtoupper($agencyRawName);
    $agencySubtitle = 'A Project by Sabir Construction';
}

$agencyHeadOffice = '';
$agencyCityCountry = '';
if (!empty($agencyData['address'])) {
    $addr = trim($agencyData['address']);
    $addrParts = preg_split('/[\r\n,]+/', $addr);
    $addrParts = array_values(array_filter(array_map('trim', $addrParts)));
    if (count($addrParts) >= 2) {
        $agencyCityCountry = array_pop($addrParts);
        $agencyHeadOffice = implode(', ', $addrParts);
    } else {
        $agencyHeadOffice = $addr;
        $agencyCityCountry = 'Lahore, Pakistan';
    }
} else {
    $agencyHeadOffice = '49-AA, First Floor, Sector D Commercial';
    $agencyCityCountry = 'Bahria Town, Lahore, Pakistan';
}
$agencyFullAddress = $agencyHeadOffice . (!empty($agencyCityCountry) ? ', ' . $agencyCityCountry : '');

$agencyPhone = '';
if (!empty($agencyData['mobile'])) {
    $agencyPhone = trim($agencyData['mobile']);
} elseif (!empty($agencyData['phone'])) {
    $agencyPhone = trim($agencyData['phone']);
} elseif (!empty($contact)) {
    $agencyPhone = trim($contact);
} else {
    $agencyPhone = '+92-339-1101156';
}
if (preg_match('/^\+?92\s*(\d{3})\s*(\d{7})$/', $agencyPhone, $m)) {
    $agencyPhone = '+92-' . $m[1] . '-' . $m[2];
} elseif (preg_match('/^0(\d{3})\s*(\d{7})$/', $agencyPhone, $m)) {
    $agencyPhone = '+92-' . $m[1] . '-' . $m[2];
}

$agencyEmail = !empty($agencyData['email']) ? trim($agencyData['email']) : (!empty($siteEmail) ? trim($siteEmail) : 'info@whitevillas.com.pk');
$agencyWeb = 'whitevillas.com.pk';

// Surcharges & Due Days
$dueDaysValue = isset($dueDays) ? (int)$dueDays : 7;
$dueDaysText = ($dueDaysValue === 7) ? 'seven (7) days' : $dueDaysValue . ' days';
$lateFeesPercent = isset($lateFees) ? $lateFees : 0;

// Currency & Numbers in Words
$currencyCode = 'PKR';
$totalConsiderationWords = '';
$totalOverdueWords = '';
if (isset($this->NumToWord)) {
    $totalConsiderationWords = trim($this->NumToWord->NumberToWord($deal['Deal']['total_amount'], ''));
    $totalOverdueWords = trim($this->NumToWord->NumberToWord($totalOverdueAmountValue, ''));
}

// Bank Account Details (specifically for this agency/project)
$bankAccount = array(
    'account_title' => 'WHITE VILLAS',
    'account_no' => '0773310485346',
    'iban' => 'PK50UNIL0109000310485346',
    'bank_name' => 'UBL (United Bank Limited)',
    'branch_code' => '0773'
);
if (!empty($bankDetails) && is_array($bankDetails)) {
    if (!empty($bankDetails['account_title'])) $bankAccount['account_title'] = $bankDetails['account_title'];
    if (!empty($bankDetails['account_no'])) $bankAccount['account_no'] = $bankDetails['account_no'];
    if (!empty($bankDetails['iban'])) $bankAccount['iban'] = $bankDetails['iban'];
    if (!empty($bankDetails['bank_name'])) $bankAccount['bank_name'] = $bankDetails['bank_name'];
    if (!empty($bankDetails['branch_code'])) $bankAccount['branch_code'] = $bankDetails['branch_code'];
} elseif (!empty($siteAccount)) {
    if (preg_match('/Account\s*Title\s*:\s*([^\r\n]+)/i', $siteAccount, $m)) {
        $bankAccount['account_title'] = trim($m[1], " \t\n\r\0\x0B\"'");
    }
    if (preg_match('/Account\s*NO\s*:\s*([^\r\n]+)/i', $siteAccount, $m)) {
        $bankAccount['account_no'] = trim($m[1], " \t\n\r\0\x0B\"'");
    }
    if (preg_match('/IBAN\s*:\s*([^\r\n]+)/i', $siteAccount, $m)) {
        $bankAccount['iban'] = trim($m[1], " \t\n\r\0\x0B\"'");
    }
    if (preg_match('/BRANCH\s*CODE\s*:\s*([^\r\n]+)/i', $siteAccount, $m)) {
        $bankAccount['branch_code'] = trim($m[1], " \t\n\r\0\x0B\"'");
    }
    if (preg_match('/Bank\s*Name\s*:\s*([^\r\n]+)/i', $siteAccount, $m)) {
        $bName = trim($m[1], " \t\n\r\0\x0B\"'");
        if (stripos($bName, 'UBL') !== false && stripos($bName, 'United Bank Limited') !== false) {
            $bName = 'UBL (United Bank Limited)';
        }
        $bankAccount['bank_name'] = $bName;
    }
}

if (!empty($bankAccount['account_title'])) {
    $bankAccount['account_title'] = strtoupper($bankAccount['account_title']);
}
if (!empty($bankAccount['bank_name']) && (stripos($bankAccount['bank_name'], 'UBL') !== false || stripos($bankAccount['bank_name'], 'United Bank') !== false)) {
    $bankAccount['bank_name'] = 'UBL (United Bank Limited)';
}

// Logo: dynamically check agency/project photo first, then site logo
$logoSrc = '';
$logoCandidates = array();
if (!empty($agencyData['photo'])) {
    $logoCandidates[] = WWW_ROOT . 'img' . DS . 'user' . DS . $agencyData['photo'];
    $logoCandidates[] = WWW_ROOT . 'img' . DS . 'user_thumb' . DS . $agencyData['photo'];
    $logoCandidates[] = WWW_ROOT . 'img' . DS . 'agency' . DS . $agencyData['photo'];
    $logoCandidates[] = WWW_ROOT . 'img' . DS . 'agency_thumb' . DS . $agencyData['photo'];
    $logoCandidates[] = WWW_ROOT . 'img' . DS . $agencyData['photo'];
}
if (!empty($frontLogo)) {
    $logoCandidates[] = WWW_ROOT . 'img' . DS . $frontLogo;
}
foreach ($logoCandidates as $candidatePath) {
    if (file_exists($candidatePath) && !is_dir($candidatePath)) {
        $ext = strtolower(pathinfo($candidatePath, PATHINFO_EXTENSION));
        if ($ext === 'jpg') $ext = 'jpeg';
        $logoBase64 = base64_encode(file_get_contents($candidatePath));
        $logoSrc = 'data:image/' . $ext . ';base64,' . $logoBase64;
        break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?php echo h(__('Payment Demand Letter - ' . $cleanRefNo)); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style type="text/css">
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        body {
            margin: 0;
            padding: 12px 0 24px 0;
            background: #e2e8f0;
            font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
            color: #0f172a;
            font-size: 11px;
            line-height: 1.4;
        }

        /* Action Buttons */
        .letter-actions-wrap {
            width: 100%;
            max-width: 790px;
            margin: 0 auto 12px auto;
            text-align: right;
        }
        .action-btn {
            border: 0;
            border-radius: 4px;
            color: #fff;
            cursor: pointer;
            font-weight: 700;
            font-size: 13px;
            margin-left: 8px;
            padding: 9px 18px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.15);
            transition: opacity 0.2s;
        }
        .action-btn:hover {
            opacity: 0.9;
        }
        .btn-print { background: #07152a; }
        .btn-pdf { background: #9f002d; }

        /* Printable Letter Page (Exact A4 Portrait Layout) */
        .letter-page {
            width: 100%;
            max-width: 790px;
            margin: 0 auto;
            background: #ffffff;
            border: 1.5px solid #07152a;
            padding: 18px 22px 14px 22px;
            box-sizing: border-box;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        /* Header */
        .letter-header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 1.5px solid #07152a;
            padding-bottom: 8px;
            margin-bottom: 8px;
        }
        .letter-header-table td {
            border: 0 !important;
            padding: 0 0 8px 0 !important;
            vertical-align: middle;
        }
        .header-brand-cell {
            text-align: left;
        }
        .header-brand-wrap {
            display: table;
        }
        .header-logo-box {
            display: table-cell;
            vertical-align: middle;
            width: 62px;
            padding-right: 10px;
        }
        .header-logo-img {
            max-height: 58px;
            max-width: 60px;
            width: auto;
            height: auto;
            display: block;
            object-fit: contain;
        }
        .header-text-box {
            display: table-cell;
            vertical-align: middle;
        }
        .brand-name-text {
            font-size: 24px;
            font-weight: 900;
            color: #0b2545;
            letter-spacing: 0.6px;
            line-height: 1.1;
            margin: 0;
            text-transform: uppercase;
        }
        .brand-subtitle-text {
            font-size: 11.5px;
            font-weight: 600;
            color: #475569;
            margin: 2px 0 0 0;
            letter-spacing: 0.2px;
        }
        .header-office-cell {
            text-align: right;
            vertical-align: middle;
            font-size: 10px;
            color: #1e293b;
            line-height: 1.38;
        }
        .header-office-cell strong {
            color: #0f172a;
        }

        /* Ref & Date Box */
        .ref-date-container {
            border: 1.2px solid #07152a;
            border-radius: 3px;
            padding: 5px 12px;
            margin-bottom: 8px;
            background: #ffffff;
        }
        .ref-date-table {
            width: 100%;
            border-collapse: collapse;
        }
        .ref-date-table td {
            border: 0 !important;
            padding: 0 !important;
            font-size: 11px;
            color: #07152a;
            vertical-align: middle;
        }
        .ref-no-text {
            text-align: left;
        }
        .date-text {
            text-align: right;
        }

        /* Title Badge */
        .title-badge-wrap {
            text-align: center;
            margin: 4px 0 8px 0;
        }
        .title-badge {
            display: inline-block;
            background: #07152a;
            color: #ffffff;
            font-size: 13px;
            font-weight: 800;
            letter-spacing: 1.4px;
            padding: 5px 22px;
            text-transform: uppercase;
            border-radius: 2px;
        }

        /* Two Cards Grid */
        .details-grid-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-bottom: 8px;
        }
        .card-cell {
            width: calc(50% - 5px);
            vertical-align: top;
            padding: 0 !important;
            border: 0 !important;
        }
        .card-gap {
            width: 10px;
            padding: 0 !important;
            border: 0 !important;
        }
        .info-card {
            border: 1.2px solid #07152a;
            border-radius: 3px;
            overflow: hidden;
            background: #ffffff;
            min-height: 78px;
            box-sizing: border-box;
        }
        .info-card-header {
            background: #eef2f7;
            border-bottom: 1.2px solid #07152a;
            padding: 4px 8px;
            font-size: 9.5px;
            font-weight: 800;
            color: #07152a;
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }
        .info-card-body {
            padding: 6px 8px;
            font-size: 10.5px;
            line-height: 1.4;
            color: #1e293b;
        }
        .allottee-name {
            font-size: 11px;
            color: #07152a;
            margin-bottom: 2px;
        }
        .allottee-line {
            margin-bottom: 2px;
        }

        /* Subject Line */
        .subject-container {
            font-size: 11.5px;
            color: #07152a;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 3px;
            margin: 7px 0 7px 0;
            font-weight: 600;
        }
        .subject-container strong {
            color: #07152a;
        }

        /* Body Paragraphs */
        .letter-content-body {
            font-size: 11px;
            line-height: 1.42;
            color: #1e293b;
            text-align: justify;
        }
        .letter-content-body p {
            margin: 0 0 6px 0;
        }
        .letter-content-body strong {
            color: #07152a;
        }

        /* Summary Table */
        .highlights-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin: 7px 0 8px 0;
            font-size: 10.5px;
        }
        .highlights-table th {
            background: #07152a;
            color: #ffffff;
            font-size: 9.5px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            padding: 6px 8px;
            border: 1px solid #07152a;
            text-align: left;
        }
        .highlights-table td {
            border: 1px solid #07152a;
            padding: 6px 8px;
            color: #07152a;
            font-size: 10.5px;
            vertical-align: middle;
        }
        .highlights-table .amount-red {
            color: #b91c1c;
            font-size: 11.5px;
            font-weight: 800;
        }

        /* Due notice line */
        .clearance-notice-line {
            font-size: 11px;
            line-height: 1.42;
            color: #1e293b;
            margin: 6px 0 7px 0;
            text-align: justify;
        }
        .clearance-notice-line strong {
            color: #07152a;
        }

        /* Bank Instructions Box */
        .bank-box-wrap {
            border: 1.2px solid #07152a;
            border-radius: 3px;
            padding: 6px 12px;
            margin-bottom: 6px;
            background: #ffffff;
        }
        .bank-box-heading {
            font-size: 10.5px;
            font-weight: 800;
            color: #07152a;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            margin-bottom: 2px;
        }
        .bank-box-instruction {
            font-size: 10px;
            color: #334155;
            margin-bottom: 4px;
        }
        .bank-data-table {
            width: 100%;
            border-collapse: collapse;
        }
        .bank-data-table td {
            padding: 0 !important;
            border: 0 !important;
            vertical-align: top;
        }
        .bank-sub-table {
            width: 100%;
            border-collapse: collapse;
        }
        .bank-sub-table td {
            padding: 2px 4px 2px 0 !important;
            border: 0 !important;
            font-size: 10.5px;
            vertical-align: middle;
        }
        .bank-sub-table .lbl {
            width: 90px;
            color: #475569;
        }
        .bank-sub-table .val {
            color: #0f172a;
            font-weight: 700;
        }

        /* Caution Alert Box */
        .caution-alert-card {
            border: 1.2px solid #dc2626;
            border-radius: 3px;
            padding: 5px 10px;
            margin-bottom: 6px;
            background: #fffcfc;
            font-size: 10px;
            line-height: 1.38;
            color: #991b1b;
        }
        .caution-alert-card strong {
            color: #7f1d1d;
        }

        /* Support / Ledger text */
        .support-reconciliation-text {
            font-size: 10.5px;
            line-height: 1.36;
            color: #1e293b;
            margin-bottom: 6px;
        }
        .support-reconciliation-text strong {
            color: #07152a;
        }

        /* Sign-off & Signature Area */
        .closing-salut {
            font-size: 10.5px;
            color: #1e293b;
            margin-bottom: 4px;
        }
        .signature-spacer {
            height: 30px;
        }
        .signature-meta-table {
            width: 100%;
            border-collapse: collapse;
        }
        .signature-meta-table td {
            padding: 0 !important;
            border: 0 !important;
            vertical-align: bottom;
        }
        .sign-director-name {
            font-size: 12px;
            font-weight: 800;
            color: #07152a;
            border-bottom: 1.2px solid #07152a;
            display: inline-block;
            padding-bottom: 2px;
            min-width: 160px;
        }
        .sign-director-dept {
            font-size: 10px;
            color: #475569;
            margin-top: 3px;
        }
        .sign-notice-ref {
            text-align: right;
            font-size: 9.5px;
            color: #64748b;
        }

        /* Footer */
        .letter-footer-note {
            border-top: 1.2px solid #07152a;
            padding-top: 5px;
            margin-top: 8px;
            text-align: center;
            font-size: 9px;
            color: #334155;
            letter-spacing: 0.2px;
        }

        /* Print Specific Styling */
        @media print {
            @page {
                size: A4 portrait;
                margin: 6mm 6mm 6mm 6mm;
            }
            body {
                background: #ffffff !important;
                padding: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .letter-page {
                border: 1.5px solid #07152a !important;
                box-shadow: none !important;
                max-width: 100% !important;
                width: 100% !important;
                padding: 14px 18px 12px 18px !important;
                margin: 0 auto !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
        }
    </style>
</head>
<body>

<div class="letter-actions-wrap no-print">
    <button type="button" class="action-btn btn-print" onclick="printDemandLetter();">
        <svg style="width:16px;height:16px;fill:currentColor" viewBox="0 0 24 24"><path d="M19 8H5c-1.66 0-3 1.34-3 3v6h4v4h12v-4h4v-6c0-1.66-1.34-3-3-3zm-3 11H8v-5h8v5zm3-7c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1zm-1-9H6v4h12V3z"/></svg>
        <?php echo __('Print'); ?>
    </button>
    <button type="button" class="action-btn btn-pdf" onclick="return downloadDemandLetterPdf();">
        <svg style="width:16px;height:16px;fill:currentColor" viewBox="0 0 24 24"><path d="M19 9h-4V3H9v6H5l7 7 7-7zM5 18v2h14v-2H5z"/></svg>
        <?php echo __('Download PDF'); ?>
    </button>
</div>

<div class="letter-page" id="demand-letter-content">
    <!-- Header -->
    <table class="letter-header-table">
        <tr>
            <td class="header-brand-cell">
                <div class="header-brand-wrap">
                    <?php if (!empty($logoSrc)): ?>
                        <div class="header-logo-box">
                            <img src="<?php echo $logoSrc; ?>" alt="<?php echo h($agencyBrandName); ?>" class="header-logo-img" />
                        </div>
                    <?php endif; ?>
                    <div class="header-text-box">
                        <h1 class="brand-name-text"><?php echo h($agencyBrandName); ?></h1>
                        <div class="brand-subtitle-text"><?php echo h($agencySubtitle); ?></div>
                    </div>
                </div>
            </td>
            <td class="header-office-cell">
                <div><strong>Head Office:</strong> <?php echo h($agencyHeadOffice); ?></div>
                <div><?php echo h($agencyCityCountry); ?></div>
                <div><strong>Phone:</strong> <?php echo h($agencyPhone); ?></div>
                <div><strong>Email:</strong> <?php echo h($agencyEmail); ?> | <strong>Web:</strong> <?php echo h($agencyWeb); ?></div>
            </td>
        </tr>
    </table>

    <!-- Ref & Issue Date Bar -->
    <div class="ref-date-container">
        <table class="ref-date-table">
            <tr>
                <td class="ref-no-text"><strong>Ref. No:</strong> <?php echo h($cleanRefNo); ?></td>
                <td class="date-text"><strong>Date:</strong> <span class="letter-issue-date" id="letter-issue-date"><?php echo h($issueDate); ?></span></td>
            </tr>
        </table>
    </div>

    <!-- Letter Title Badge -->
    <div class="title-badge-wrap">
        <div class="title-badge"><?php echo __('PAYMENT DEMAND LETTER'); ?></div>
    </div>

    <!-- Details Cards Grid -->
    <table class="details-grid-table">
        <tr>
            <td class="card-cell">
                <div class="info-card">
                    <div class="info-card-header"><?php echo __('ISSUED TO (ALLOTTEE / PURCHASER)'); ?></div>
                    <div class="info-card-body">
                        <div class="allottee-name"><strong><?php echo h($clientFormattedName); ?></strong></div>
                        <div class="allottee-line"><?php echo h($clientAddress); ?></div>
                        <div class="allottee-line"><strong>Contact:</strong> <?php echo h($clientPhone); ?></div>
                    </div>
                </div>
            </td>
            <td class="card-gap"></td>
            <td class="card-cell">
                <div class="info-card">
                    <div class="info-card-header"><?php echo __('PROPERTY & ALLOTMENT DETAILS'); ?></div>
                    <div class="info-card-body">
                        <div class="allottee-line"><strong>Project:</strong> <?php echo h($projectDisplayName); ?></div>
                        <div class="allottee-line"><strong>Property ID:</strong> <?php echo h($propertyDisplayId); ?></div>
                        <div class="allottee-line"><strong>Total Consideration:</strong> PKR <?php echo $this->Number->format($deal['Deal']['total_amount']); ?>/-</div>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <!-- Subject Line -->
    <div class="subject-container">
        <strong>Subject:</strong> Formal Demand Notice for Overdue Installment Payment &mdash; <strong>Unit No. <?php echo h($propUnitName); ?></strong>
    </div>

    <!-- Body Paragraphs -->
    <div class="letter-content-body">
        <p>Dear Sir / Madam,</p>
        <p>
            This is a formal reminder regarding the outstanding payment for your allotted <strong><?php echo h($propertyDisplayId); ?></strong> located in <strong><?php echo h($projectDisplayName); ?></strong>, for a total consideration of <strong>PKR <?php echo $this->Number->format($deal['Deal']['total_amount']); ?>/- (Rupees <?php echo h($totalConsiderationWords); ?>)</strong>.
        </p>
        <p>
            As per your Allotment Agreement, installments for Unit <strong><?php echo h($propUnitName); ?> (<?php echo $overdueInstallmentCount; ?> Overdue (Total: <?php echo $totalDueDisplay; ?>))</strong> are currently past due, and the total principal amount now due is <strong>PKR <?php echo $this->Number->format($totalOverdueAmountValue); ?>/- (Rupees <?php echo h($totalOverdueWords); ?>)</strong> plus applicable service and surcharge charges.
        </p>
    </div>

    <!-- Highlights Table -->
    <table class="highlights-table">
        <thead>
            <tr>
                <th style="width: 38%;">PROPERTY UNIT</th>
                <th style="width: 22%;">TOTAL CONSIDERATION</th>
                <th style="width: 20%;">OVERDUE INSTALLMENTS</th>
                <th style="width: 20%;">TOTAL OVERDUE AMOUNT</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong><?php echo h($propertyDisplayId); ?></strong></td>
                <td>PKR <?php echo $this->Number->format($deal['Deal']['total_amount']); ?>/-</td>
                <td><?php echo $overdueInstallmentCount; ?> Overdue (Total: <?php echo $totalDueDisplay; ?>)</td>
                <td class="amount-red">PKR <?php echo $this->Number->format($totalOverdueAmountValue); ?>/-</td>
            </tr>
        </tbody>
    </table>

    <!-- Due notice & surcharge line -->
    <div class="clearance-notice-line">
        We kindly request you to clear the payment of <strong>PKR <?php echo $this->Number->format($totalOverdueAmountValue); ?>/-</strong> within <strong><?php echo h($dueDaysText); ?></strong> from the issuance of this notice to avoid late payment penalties. Surcharge at the rate of <strong><?php echo h($lateFeesPercent); ?>%</strong> will be applied on delayed payments as per current company terms.
    </div>

    <!-- Authorized Payment & Bank Deposit Instructions -->
    <div class="bank-box-wrap">
        <div class="bank-box-heading"><?php echo __('AUTHORIZED PAYMENT & BANK DEPOSIT INSTRUCTIONS'); ?></div>
        <div class="bank-box-instruction">Payment for this transaction must be made strictly via <strong>Crossed Cheque / Online Bank Transfer</strong> in favour of:</div>
        <table class="bank-data-table">
            <tr>
                <td style="width: 58%;">
                    <table class="bank-sub-table">
                        <tr>
                            <td class="lbl">Account Title:</td>
                            <td class="val"><?php echo h($bankAccount['account_title']); ?></td>
                        </tr>
                        <tr>
                            <td class="lbl">Account No:</td>
                            <td class="val"><?php echo h($bankAccount['account_no']); ?></td>
                        </tr>
                        <tr>
                            <td class="lbl">IBAN:</td>
                            <td class="val"><?php echo h($bankAccount['iban']); ?></td>
                        </tr>
                    </table>
                </td>
                <td style="width: 42%;">
                    <table class="bank-sub-table">
                        <tr>
                            <td class="lbl">Bank Name:</td>
                            <td class="val"><?php echo h($bankAccount['bank_name']); ?></td>
                        </tr>
                        <tr>
                            <td class="lbl">Branch Code:</td>
                            <td class="val"><?php echo h($bankAccount['branch_code']); ?></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <!-- Caution Box -->
    <div class="caution-alert-card">
        <div><strong>Important Note:</strong> Please prepare a separate crossed cheque for any applicable service tax or surcharge amount.</div>
        <div><strong>Caution:</strong> Failure to pay 3 consecutive installments will result in permanent cancellation of allotment with no refund.</div>
    </div>

    <!-- Reconciliation contact -->
    <div class="support-reconciliation-text">
        Please feel free to contact our accounts office at <strong><?php echo h($agencyPhone); ?></strong> for any clarification or ledger reconciliation. Thank you for your prompt attention to this matter.
    </div>

    <!-- Sign-off & Signature Area -->
    <div class="closing-salut">Yours sincerely,</div>
    <div class="signature-spacer"></div>
    <table class="signature-meta-table">
        <tr>
            <td style="text-align: left;">
                <div class="sign-director-name">Director</div>
                <div class="sign-director-dept">White Villas &mdash; Accounts &amp; Recovery</div>
            </td>
            <td class="sign-notice-ref">
                Official Demand Notice &mdash; Ref: <?php echo h($cleanRefNo); ?>
            </td>
        </tr>
    </table>

    <!-- Footer -->
    <div class="letter-footer-note">
        <?php echo h($agencyBrandName); ?> | <?php echo h($agencyFullAddress); ?> | Tel: <?php echo h($agencyPhone); ?> | <?php echo h($agencyEmail); ?>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script type="text/javascript">
function getFormattedCurrentDate() {
    var now = new Date();
    var day = ('0' + now.getDate()).slice(-2);
    var monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
    var mon = monthNames[now.getMonth()];
    var year = now.getFullYear();
    return day + '-' + mon + '-' + year;
}

function updateIssueDate() {
    var cur = getFormattedCurrentDate();
    var els = document.querySelectorAll('.letter-issue-date');
    for (var i = 0; i < els.length; i++) {
        els[i].textContent = cur;
    }
}

function printDemandLetter() {
    updateIssueDate();
    window.print();
}

function downloadDemandLetterPdf() {
    updateIssueDate();
    var safeName = "<?php echo addslashes($pdfFileName); ?>".replace(/[^a-z0-9_\-]+/gi, '_');
    var element = document.getElementById('demand-letter-content');
    if (!element) {
        return false;
    }
    if (typeof html2pdf === 'undefined') {
        alert('PDF generator is initializing, opening print dialog instead.');
        window.print();
        return false;
    }
    var opt = {
        margin:       [0.15, 0.15, 0.15, 0.15],
        filename:     safeName + '.pdf',
        image:        { type: 'jpeg', quality: 0.98 },
        html2canvas:  { scale: 2, useCORS: true, logging: false },
        jsPDF:        { unit: 'in', format: 'a4', orientation: 'portrait' }
    };
    html2pdf().set(opt).from(element).save();
    return false;
}

window.addEventListener('DOMContentLoaded', function() {
    updateIssueDate();
});

<?php if ($enablePdfDownload) { ?>
window.onload = function() {
    updateIssueDate();
    setTimeout(downloadDemandLetterPdf, 500);
};
<?php } elseif ($autoPrint) { ?>
window.onload = function() {
    updateIssueDate();
    if (typeof(window.print) !== 'undefined') {
        window.print();
    }
};
<?php } ?>
</script>
</body>
</html>
