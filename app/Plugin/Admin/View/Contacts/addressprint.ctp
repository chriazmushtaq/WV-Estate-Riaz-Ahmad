<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('Address Labels Print Preview - White Villas'); ?></title>
    <style>
        @page {
            size: A4 portrait;
            margin: 6mm 6mm;
        }
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        body {
            background: #0f172a;
            color: #0f172a;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }

        /* ============================================================
           EXECUTIVE TOP TOOLBAR (SCREEN ONLY)
           ============================================================ */
        .screen-toolbar {
            position: sticky;
            top: 0;
            left: 0;
            right: 0;
            z-index: 9999;
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.12);
            padding: 12px 24px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5);
        }
        .toolbar-inner {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
        }
        .toolbar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .toolbar-icon-box {
            width: 38px;
            height: 38px;
            border-radius: 8px;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.35);
        }
        .toolbar-title-wrap h1 {
            color: #f8fafc;
            font-size: 15px;
            font-weight: 700;
            margin: 0;
            line-height: 1.2;
            letter-spacing: -0.01em;
        }
        .toolbar-meta {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 3px;
        }
        .badge-pill {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 11px;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 20px;
            line-height: 1.3;
        }
        .badge-blue {
            background: rgba(59, 130, 246, 0.2);
            color: #93c5fd;
            border: 1px solid rgba(59, 130, 246, 0.3);
        }
        .badge-slate {
            background: rgba(148, 163, 184, 0.15);
            color: #cbd5e1;
            border: 1px solid rgba(148, 163, 184, 0.25);
        }
        .badge-amber {
            background: rgba(245, 158, 11, 0.15);
            color: #fcd34d;
            border: 1px solid rgba(245, 158, 11, 0.3);
        }
        .toolbar-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .btn-toolbar {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            font-size: 13px;
            font-weight: 700;
            padding: 9px 18px;
            border-radius: 7px;
            cursor: pointer;
            border: none;
            transition: all 0.15s ease;
            text-decoration: none;
        }
        .btn-print {
            background: #2563eb;
            color: #ffffff;
            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.4);
        }
        .btn-print:hover {
            background: #1d4ed8;
            box-shadow: 0 6px 18px rgba(37, 99, 235, 0.55);
            transform: translateY(-1px);
        }
        .btn-close {
            background: rgba(255, 255, 255, 0.1);
            color: #e2e8f0;
            border: 1px solid rgba(255, 255, 255, 0.15);
        }
        .btn-close:hover {
            background: rgba(255, 255, 255, 0.18);
            color: #ffffff;
        }

        .print-tip-bar {
            background: rgba(30, 41, 59, 0.7);
            border-top: 1px solid rgba(255, 255, 255, 0.06);
            color: #94a3b8;
            font-size: 11.5px;
            padding: 6px 24px;
            text-align: center;
        }
        .print-tip-bar strong {
            color: #f1f5f9;
        }

        /* ============================================================
           SHEET WORKSPACE & A4 CONTAINER
           ============================================================ */
        .workspace-wrap {
            padding: 24px 12px 60px;
        }
        .sheet-page {
            background: #ffffff;
            width: 210mm;
            min-height: 297mm;
            max-height: 297mm;
            margin: 0 auto 30px;
            padding: 8mm 7mm;
            box-shadow: 0 15px 45px rgba(0, 0, 0, 0.45), 0 1px 3px rgba(0, 0, 0, 0.2);
            border-radius: 4px;
            position: relative;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            page-break-after: always;
            break-after: page;
        }
        .sheet-page:last-child {
            margin-bottom: 0;
            page-break-after: avoid;
            break-after: avoid;
        }

        /* ============================================================
           2x4 LABEL GRID (STANDARD 8 LABELS PER A4)
           ============================================================ */
        .label-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            grid-auto-rows: 67.5mm;
            column-gap: 5mm;
            row-gap: 3.5mm;
            width: 100%;
        }

        /* ============================================================
           INDIVIDUAL ADDRESS LABEL CARD
           ============================================================ */
        .label-card {
            background: #ffffff;
            border: 1.5px solid #0f172a;
            border-radius: 5px;
            height: 67.5mm;
            max-height: 67.5mm;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
            position: relative;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }

        /* RECIPIENT MAIN SECTION */
        .recipient-section {
            padding: 7px 11px 4px;
            display: flex;
            flex-direction: column;
            flex: 1;
            min-height: 0;
            justify-content: flex-start;
        }
        .label-header-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 3px;
        }
        .tag-deliver-to {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: #0f172a;
            color: #ffffff;
            font-size: 7.2pt;
            font-weight: 800;
            letter-spacing: 0.5px;
            padding: 2px 7px;
            border-radius: 3px;
            text-transform: uppercase;
            line-height: 1.2;
        }
        .tag-priority {
            font-size: 6.5pt;
            font-weight: 700;
            color: #64748b;
            letter-spacing: 0.4px;
            text-transform: uppercase;
        }

        .recipient-name {
            color: #0f172a;
            font-size: 12.5pt;
            font-weight: 800;
            letter-spacing: -0.015em;
            line-height: 1.15;
            margin: 2px 0 3px;
            text-transform: uppercase;
            max-height: 29pt;
            overflow: hidden;
            word-wrap: break-word;
        }

        .recipient-address {
            color: #1e293b;
            font-size: 8.8pt;
            font-weight: 600;
            line-height: 1.32;
            margin-bottom: 4px;
            max-height: 25mm;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 4;
            -webkit-box-orient: vertical;
            word-wrap: break-word;
        }

        .phone-callout-pill {
            align-self: flex-start;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #f8fafc;
            border: 1.2px solid #cbd5e1;
            border-radius: 4px;
            padding: 2.5px 8px;
            margin-top: auto;
            color: #0f172a;
        }
        .phone-callout-pill .pill-ico {
            width: 12px;
            height: 12px;
            color: #2563eb;
            flex-shrink: 0;
        }
        .phone-callout-pill .pill-lbl {
            font-size: 7pt;
            font-weight: 800;
            color: #475569;
            letter-spacing: 0.4px;
        }
        .phone-callout-pill .pill-val {
            font-size: 9.5pt;
            font-weight: 800;
            letter-spacing: 0.3px;
            color: #0f172a;
        }

        /* SENDER / RETURN FOOTER */
        .sender-section {
            border-top: 1.2px solid #0f172a;
            background: #f8fafc;
            padding: 4px 8px;
            height: 19mm;
            max-height: 19mm;
            box-sizing: border-box;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }
        .sender-text-col {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .sender-tag {
            font-size: 5.6pt;
            font-weight: 800;
            color: #64748b;
            letter-spacing: 0.4px;
            text-transform: uppercase;
            line-height: 1;
            margin-bottom: 2px;
        }
        .sender-org {
            font-size: 7.8pt;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            text-transform: uppercase;
            margin-bottom: 1.5px;
        }
        .sender-details {
            font-size: 5.8pt;
            font-weight: 600;
            color: #334155;
            line-height: 1.2;
            max-height: 16px;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            word-wrap: break-word;
        }
        .sender-contact-line {
            font-size: 5.8pt;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.15;
            margin-top: 1px;
        }

        .sender-logo-col {
            width: 90px;
            height: 52px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .sender-logo-img {
            max-width: 90px;
            max-height: 48px;
            object-fit: contain;
            display: block;
        }
        .sender-logo-fallback {
            border: 1px dashed #94a3b8;
            border-radius: 4px;
            padding: 3px 6px;
            text-align: center;
            width: 100%;
        }
        .sender-logo-fallback .fb-title {
            font-size: 6.8pt;
            font-weight: 900;
            color: #0f172a;
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }
        .sender-logo-fallback .fb-sub {
            font-size: 4.8pt;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
        }

        /* EMPTY STATE */
        .empty-state-card {
            background: #ffffff;
            border-radius: 12px;
            max-width: 540px;
            margin: 60px auto;
            padding: 40px 32px;
            text-align: center;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .empty-icon-box {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: #eff6ff;
            color: #2563eb;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
        }
        .empty-state-card h2 {
            font-size: 18px;
            font-weight: 800;
            color: #0f172a;
            margin: 0 0 8px;
        }
        .empty-state-card p {
            font-size: 13px;
            color: #64748b;
            margin: 0 0 24px;
            line-height: 1.5;
        }
        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #0f172a;
            color: #ffffff;
            font-size: 13px;
            font-weight: 700;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.15s ease;
        }
        .btn-back:hover {
            background: #1e293b;
            color: #ffffff;
        }

        /* ============================================================
           MEDIA PRINT OPTIMIZATIONS
           ============================================================ */
        @media print {
            body {
                background: #ffffff !important;
                color: #000000 !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .screen-toolbar,
            .print-tip-bar,
            .no-print {
                display: none !important;
            }
            .workspace-wrap {
                padding: 0 !important;
                margin: 0 !important;
            }
            .sheet-page {
                box-shadow: none !important;
                border-radius: 0 !important;
                margin: 0 !important;
                padding: 7mm 7mm !important;
                width: 100% !important;
                max-width: 100% !important;
                min-height: 297mm !important;
                max-height: 297mm !important;
                page-break-after: always !important;
                break-after: page !important;
            }
            .sheet-page:last-child {
                page-break-after: avoid !important;
                break-after: avoid !important;
            }
            .label-card {
                border: 1.5px solid #000000 !important;
                background: #ffffff !important;
            }
            .tag-deliver-to {
                background: #000000 !important;
                color: #ffffff !important;
            }
            .sender-section {
                border-top: 1.2px solid #000000 !important;
                background: #f8fafc !important;
            }
            .phone-callout-pill {
                border: 1.2px solid #000000 !important;
                background: #ffffff !important;
                color: #000000 !important;
            }
            .phone-callout-pill .pill-ico {
                color: #000000 !important;
            }
        }
    </style>
</head>
<body>

<?php
$contacts = isset($contacts) && is_array($contacts) ? $contacts : array();
$agencyHeaders = isset($agencyHeaders) && is_array($agencyHeaders) ? $agencyHeaders : array();
$siteFallback = array(
    'name' => isset($siteOrganization) ? (string)$siteOrganization : (isset($siteName) ? (string)$siteName : ''),
    'address' => isset($siteAddress) ? (string)$siteAddress : '',
    'phone' => isset($contact) ? (string)$contact : '',
    'mobile' => '',
    'email' => isset($siteEmail) ? (string)$siteEmail : '',
    'logo' => isset($frontLogo) ? (string)$frontLogo : ''
);
$view = $this;
$logoUrlFromPath = function($logo) use ($view) {
    $logo = trim((string)$logo);
    if($logo === '')
    {
        return '';
    }
    if(preg_match('#^https?://#i', $logo) || strpos($logo, '/') === 0)
    {
        return $logo;
    }
    return $view->Html->url('/img/'.ltrim($logo, '/'));
};

$totalContacts = count($contacts);
$sheetChunks = !empty($contacts) ? array_chunk($contacts, 8) : array();
$totalPages = count($sheetChunks);
?>

<?php if(empty($contacts)){ ?>
    <!-- EMPTY STATE VIEW -->
    <div class="workspace-wrap">
        <div class="empty-state-card">
            <div class="empty-icon-box">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                </svg>
            </div>
            <h2><?php echo __('No Contacts Selected'); ?></h2>
            <p><?php echo __('Please select one or more contacts from the Contacts directory table before launching Address Print.'); ?></p>
            <a href="javascript:window.close()" class="btn-back">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
                <?php echo __('Close Window'); ?>
            </a>
        </div>
    </div>
<?php }else{ ?>

    <!-- SCREEN ONLY: EXECUTIVE CONTROL TOOLBAR -->
    <div class="screen-toolbar no-print">
        <div class="toolbar-inner">
            <div class="toolbar-brand">
                <div class="toolbar-icon-box">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="6 9 6 2 18 2 18 9"></polyline>
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                        <rect x="6" y="14" width="12" height="8"></rect>
                    </svg>
                </div>
                <div class="toolbar-title-wrap">
                    <h1><?php echo __('Address Labels Print Preview'); ?></h1>
                    <div class="toolbar-meta">
                        <span class="badge-pill badge-blue">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                            </svg>
                            <?php echo sprintf(__('%d Label(s) Ready'), $totalContacts); ?>
                        </span>
                        <span class="badge-pill badge-slate">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                            </svg>
                            <?php echo sprintf(__('A4 Sheet • 2×4 Grid (8 / Page)')); ?>
                        </span>
                        <span class="badge-pill badge-amber">
                            <?php echo sprintf(__('Total Sheets: %d'), $totalPages); ?>
                        </span>
                    </div>
                </div>
            </div>

            <div class="toolbar-actions">
                <button class="btn-toolbar btn-print" onclick="window.print()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="6 9 6 2 18 2 18 9"></polyline>
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                        <rect x="6" y="14" width="12" height="8"></rect>
                    </svg>
                    <?php echo __('Print Labels'); ?>
                </button>
                <button class="btn-toolbar btn-close" onclick="window.close()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                    <?php echo __('Close'); ?>
                </button>
            </div>
        </div>
    </div>

    <!-- SCREEN PRINT TIP BAR -->
    <div class="print-tip-bar no-print">
        <?php echo __('For perfect physical sticker printing: In your browser print settings, select <strong>Paper Size: A4</strong>, <strong>Margins: Default (or None)</strong>, and enable <strong>Background graphics</strong>.'); ?>
    </div>

    <!-- PRINT WORKSPACE -->
    <div class="workspace-wrap">
        <?php foreach($sheetChunks as $sheetIdx => $chunk): ?>
        <div class="sheet-page">
            <div class="label-grid">
                <?php foreach($chunk as $row):
                    $c = !empty($row['Contact']) ? (array)$row['Contact'] : array();
                    $projectId = !empty($c['project_id']) ? (int)$c['project_id'] : 0;
                    $sender = !empty($agencyHeaders[$projectId]) ? $agencyHeaders[$projectId] : (!empty($agencyHeaders[0]) ? $agencyHeaders[0] : $siteFallback);
                    $recipientName = trim((string)(isset($c['name']) ? $c['name'] : ''));
                    $addressParts = array();
                    foreach(array('address', 'district', 'state') as $addressField)
                    {
                        if(!empty($c[$addressField]) && !in_array(trim((string)$c[$addressField]), $addressParts, true))
                        {
                            $addressParts[] = trim((string)$c[$addressField]);
                        }
                    }
                    $recipientAddress = implode(",\n", $addressParts);
                    $recipientPhone = !empty($c['mobile']) ? trim((string)$c['mobile']) : (!empty($c['phone']) ? trim((string)$c['phone']) : '');
                    $senderName = !empty($sender['name']) ? trim((string)$sender['name']) : $siteFallback['name'];
                    $senderAddress = !empty($sender['address']) ? trim((string)$sender['address']) : $siteFallback['address'];
                    $senderPhone = !empty($sender['mobile']) ? trim((string)$sender['mobile']) : (!empty($sender['phone']) ? trim((string)$sender['phone']) : $siteFallback['phone']);
                    $senderLogo = !empty($sender['logo']) ? $logoUrlFromPath($sender['logo']) : $logoUrlFromPath($siteFallback['logo']);
                ?>
                <div class="label-card">
                    <!-- RECIPIENT INFORMATION (TOP) -->
                    <div class="recipient-section">
                        <div class="label-header-row">
                            <span class="tag-deliver-to">
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                    <circle cx="12" cy="10" r="3"></circle>
                                </svg>
                                <?php echo __('DELIVER TO / RECIPIENT'); ?>
                            </span>
                            <span class="tag-priority"><?php echo __('PRIORITY PARCEL'); ?></span>
                        </div>

                        <div class="recipient-name" title="<?php echo h($recipientName); ?>">
                            <?php echo h($recipientName !== '' ? $recipientName : __('CUSTOMER')); ?>
                        </div>

                        <div class="recipient-address">
                            <?php echo nl2br(h($recipientAddress !== '' ? $recipientAddress : __('Address not specified'))); ?>
                        </div>

                        <?php if($recipientPhone !== ''){ ?>
                        <div class="phone-callout-pill">
                            <svg class="pill-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                            </svg>
                            <span class="pill-lbl"><?php echo __('PH:'); ?></span>
                            <span class="pill-val"><?php echo h($recipientPhone); ?></span>
                        </div>
                        <?php } ?>
                    </div>

                    <!-- SENDER / RETURN ADDRESS (BOTTOM FOOTER) -->
                    <div class="sender-section">
                        <div class="sender-text-col">
                            <span class="sender-tag"><?php echo __('IF UNDELIVERED, RETURN TO:'); ?></span>
                            <div class="sender-org" title="<?php echo h($senderName); ?>"><?php echo h($senderName); ?></div>
                            <?php if($senderAddress !== ''){ ?>
                            <div class="sender-details" title="<?php echo h($senderAddress); ?>"><?php echo h($senderAddress); ?></div>
                            <?php } ?>
                            <?php if($senderPhone !== ''){ ?>
                            <div class="sender-contact-line"><?php echo __('Tel:'); ?> <?php echo h($senderPhone); ?></div>
                            <?php } ?>
                        </div>
                        <div class="sender-logo-col">
                            <?php if($senderLogo !== ''){ ?>
                                <img src="<?php echo h($senderLogo); ?>" alt="<?php echo h($senderName); ?>" class="sender-logo-img">
                            <?php }else{ ?>
                                <div class="sender-logo-fallback">
                                    <div class="fb-title"><?php echo h(!empty($senderName) ? substr($senderName, 0, 14) : __('WHITE VILLAS')); ?></div>
                                    <div class="fb-sub"><?php echo __('OFFICIAL'); ?></div>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <script type="text/javascript">
    setTimeout(function(){
        if(typeof window.print !== 'undefined'){
            window.print();
        }
    }, 400);
    </script>
<?php } ?>

</body>
</html>
