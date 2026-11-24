<style>
/* --- PROFESSIONAL HIGH-CONTRAST METER & BILLING STATEMENT LEDGER STYLING --- */
.ledger-table-custom {
    width: 100% !important;
    border-collapse: collapse !important;
    font-size: 13.5px !important;
}

.ledger-table-custom th {
    background: #0f172a !important;
    color: #ffffff !important;
    font-size: 12px !important;
    font-weight: 800 !important;
    text-transform: uppercase !important;
    padding: 13px 15px !important;
    border: 1px solid #1e293b !important;
    vertical-align: middle !important;
    white-space: nowrap !important;
    letter-spacing: 0.5px !important;
}

.ledger-table-custom td {
    padding: 12px 15px !important;
    border: 1px solid #cbd5e1 !important;
    vertical-align: middle !important;
    height: 54px !important;
    max-height: 54px !important;
    box-sizing: border-box !important;
    color: #0f172a !important;
    font-weight: 600 !important;
}

.ledger-table-custom tr:nth-child(even) {
    background: #f8fafc !important;
}

.ledger-table-custom tr:hover {
    background: #f1f5f9 !important;
}

/* NO BACKGROUND DARK TEXT PRINT ACTION LINK */
.print-action-link {
    color: #0f172a !important;
    font-weight: 800 !important;
    background: transparent !important;
    border: none !important;
    text-decoration: none !important;
    padding: 4px 8px !important;
    display: inline-block !important;
    font-size: 13px !important;
    border-radius: 4px !important;
}

.print-action-link:hover {
    color: #0284c7 !important;
    background: #e2e8f0 !important;
    text-decoration: none !important;
}

/* STRICT SIDE-BY-SIDE EQUAL ROW HEIGHTS STYLING */
#sideBySideView .ledger-table-custom th {
    height: 48px !important;
    max-height: 48px !important;
    padding: 0 12px !important;
    vertical-align: middle !important;
    box-sizing: border-box !important;
}

#sideBySideView .sync-ledger-row {
    height: 52px !important;
    max-height: 52px !important;
    box-sizing: border-box !important;
}

#sideBySideView .sync-ledger-row td {
    height: 52px !important;
    max-height: 52px !important;
    vertical-align: middle !important;
    padding: 8px 12px !important;
    color: #0f172a !important;
    box-sizing: border-box !important;
}

#sideBySideView .badge, 
#sideBySideView .label, 
#sideBySideView .print-action-link {
    display: inline-block !important;
    vertical-align: middle !important;
    line-height: 1.2 !important;
    margin: 0 !important;
}

.subm-ledger-nav {
    display: flex !important;
    gap: 8px !important;
    margin-bottom: 20px !important;
    border-bottom: 2px solid #cbd5e1 !important;
    padding-bottom: 0 !important;
}

.subm-ledger-nav a {
    padding: 10px 20px !important;
    font-weight: 800 !important;
    font-size: 13px !important;
    color: #334155 !important;
    background: #f1f5f9 !important;
    border-radius: 8px 8px 0 0 !important;
    text-decoration: none !important;
    border: 1px solid #cbd5e1 !important;
    border-bottom: none !important;
    cursor: pointer !important;
}

.subm-ledger-nav a.active {
    background: #0284c7 !important;
    color: #ffffff !important;
    border-color: #0284c7 !important;
}

.print-letterhead, .print-footer {
    display: none;
}

/* ========================================================= */
/* ULTRA-COMPACT PERFECT A4 PRINT STYLING (@media print)     */
/* ========================================================= */
@page {
    size: A4 portrait;
    margin: 8mm 8mm 8mm 8mm;
}

@media print {
    /* Hide ALL theme wrappers, headers, sidebars, footers, search bars */
    body *, html * {
        visibility: hidden !important;
    }

    /* Only show the print-container body content */
    .print-container, .print-container * {
        visibility: visible !important;
    }

    .print-container {
        position: absolute !important;
        left: 0 !important;
        top: 0 !important;
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        background: #ffffff !important;
        font-family: Arial, Helvetica, sans-serif !important;
        font-size: 9.5pt !important;
        color: #000000 !important;
    }

    .btn-print-hide, .page-title, .subm-ledger-nav {
        display: none !important;
        visibility: hidden !important;
    }

    .print-letterhead {
        display: block !important;
        margin-bottom: 8px !important;
        padding-bottom: 6px !important;
        border-bottom: 2px solid #0f172a !important;
    }

    .print-letterhead h2 {
        font-size: 17px !important;
        margin: 0 !important;
    }

    .print-header-box {
        border: 1.5px solid #0f172a !important;
        border-radius: 6px !important;
        margin-bottom: 10px !important;
        box-shadow: none !important;
    }

    .print-header-box td {
        padding: 6px 10px !important;
        font-size: 10px !important;
    }

    .kpi-table-wrap {
        margin-bottom: 10px !important;
    }

    .kpi-table-wrap td {
        padding: 6px 10px !important;
        font-size: 11px !important;
        border: 1px solid #0f172a !important;
    }

    .ledger-table-panel {
        border: 1.5px solid #0f172a !important;
        margin-bottom: 12px !important;
        border-radius: 6px !important;
        box-shadow: none !important;
    }

    .ledger-table-panel .panel-heading {
        background: #f1f5f9 !important;
        color: #0f172a !important;
        padding: 6px 10px !important;
        font-size: 10.5px !important;
        font-weight: 800 !important;
    }

    .ledger-table-custom {
        width: 100% !important;
        border: 1px solid #0f172a !important;
        font-size: 9.5px !important;
    }

    .ledger-table-custom th {
        background: #f1f5f9 !important;
        color: #0f172a !important;
        border: 1px solid #0f172a !important;
        padding: 4px 6px !important;
        font-size: 9px !important;
    }

    .ledger-table-custom td {
        border: 1px solid #0f172a !important;
        padding: 3px 6px !important;
        height: 26px !important;
        max-height: 26px !important;
        font-size: 9.5px !important;
        color: #000000 !important;
    }

    .badge, .label {
        border: 1px solid #0f172a !important;
        padding: 1px 4px !important;
        font-size: 8.5px !important;
        color: #000000 !important;
        background: transparent !important;
    }

    .print-footer {
        display: block !important;
        margin-top: 10px !important;
        padding-top: 8px !important;
        border-top: 1px solid #0f172a !important;
        page-break-inside: avoid !important;
    }

    tr {
        page-break-inside: avoid !important;
    }
}
</style>

<div class="print-container">

    <!-- Official Printable A4 Letterhead Header -->
    <div class="print-letterhead">
        <div style="display:flex;justify-content:space-between;align-items:center;">
            <div>
                <h2 style="margin:0;font-weight:900;color:#0f172a;text-transform:uppercase;">
                    <?php echo h($societyName); ?>
                </h2>
                <div style="font-size:11.5px;color:#334155;margin-top:2px;font-weight:700;">
                    <?php echo h($societyAddress); ?> | Phone: <?php echo h($societyPhone); ?> | Email: <?php echo h($societyEmail); ?>
                </div>
            </div>
            <div style="text-align:right;">
                <div style="font-size:15px;font-weight:900;color:#0369a1;text-transform:uppercase;letter-spacing:0.5px;">
                    CONSUMER UTILITY STATEMENT
                </div>
                <div style="font-size:11px;font-weight:700;color:#475569;margin-top:2px;">
                    DATE: <?php echo strtoupper(date('d-M-Y')); ?>
                </div>
            </div>
        </div>
    </div>

    <!-- On-Screen Page Title Bar -->
    <div class="page-title" style="margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
        <div class="title-env">
            <h1 class="title" style="font-weight:800;color:#0f172a;font-size:22px;margin:0;">
                <span class="fa fa-line-chart text-primary"></span> <?php echo __('Consumer Meter & Financial Ledger Statement'); ?>
            </h1>
            <p class="description" style="color:#475569;font-size:13px;margin-top:4px;font-weight:600;">
                <?php echo __('Official utility consumption records, meter readings, and invoice payment ledger for'); ?> <strong><?php echo h(!empty($meter['BillingProperty']['BillingClient']['name']) ? $meter['BillingProperty']['BillingClient']['name'] : 'Consumer'); ?></strong>
            </p>
        </div>
        <div style="display:flex;gap:10px;" class="btn-print-hide">
            <button onclick="window.print()" class="btn btn-success" style="font-weight:800;padding:9px 22px;font-size:13.5px;">
                <i class="fa fa-print"></i> <?php echo __('Print A4 Official Ledger'); ?>
            </button>
            <a href="<?php echo $this->Html->url(array('action' => 'index')); ?>" class="btn btn-default" style="font-weight:700;padding:9px 18px;">
                &larr; <?php echo __('Back to Meters'); ?>
            </a>
        </div>
    </div>

    <?php echo $this->Session->flash(); ?>

    <!-- Official Tabular Consumer & Meter Statement Header Block (2 Columns / 2x2 Grid) -->
    <div class="print-header-box" style="background:#ffffff;border:2px solid #0f172a;border-radius:10px;margin-bottom:18px;box-shadow:0 4px 12px rgba(0,0,0,0.06);overflow:hidden;">
        <div style="background:#0f172a;color:#ffffff;padding:10px 18px;display:flex;justify-content:space-between;align-items:center;">
            <div style="font-weight:800;font-size:14px;text-transform:uppercase;letter-spacing:0.5px;color:#ffffff;">
                <i class="fa fa-file-text-o text-warning"></i> <?php echo __('OFFICIAL UTILITY CONSUMER STATEMENT'); ?>
            </div>
            <div style="font-size:11.5px;font-weight:800;color:#38bdf8;">
                <?php echo __('STATEMENT DATE'); ?>: <?php echo strtoupper(date('d-M-Y')); ?>
            </div>
        </div>

        <div class="table-responsive" style="margin:0;">
            <table class="table table-bordered" style="margin:0;vertical-align:middle;font-size:13px;border-collapse:collapse;width:100%;">
                <tbody>
                    <!-- Row 1: Consumer Profile & Property Unit Location -->
                    <tr>
                        <!-- 1. ASSIGNED CONSUMER (LEFT) -->
                        <td style="width:50%;background:#f8fafc;padding:14px;vertical-align:top;border-right:2px solid #cbd5e1;border-bottom:1.5px solid #cbd5e1;">
                            <div style="font-size:10.5px;text-transform:uppercase;color:#475569;font-weight:800;letter-spacing:0.5px;"><?php echo __('CONSUMER PROFILE (PRIMARY)'); ?></div>
                            <div style="font-size:17px;font-weight:800;color:#0f172a;margin-top:4px;line-height:1.3;">
                                <i class="fa fa-user text-primary"></i> <?php echo !empty($meter['BillingProperty']['BillingClient']['name']) ? h($meter['BillingProperty']['BillingClient']['name']) : 'N/A'; ?>
                            </div>
                            <div style="margin-top:6px;font-size:12px;color:#334155;font-weight:700;">
                                <?php if(!empty($meter['BillingProperty']['BillingClient']['id'])){ ?>
                                    <span class="badge" style="background:#0284c7;color:#fff;font-weight:800;font-size:10.5px;padding:2px 7px;">ID: WV70<?php echo str_pad($meter['BillingProperty']['BillingClient']['id'], 2, '0', STR_PAD_LEFT); ?></span>
                                <?php } ?>
                                <?php if(!empty($meter['BillingProperty']['BillingClient']['phone'])){ ?>
                                    <span style="margin-left:8px;color:#0f172a;"><i class="fa fa-phone text-success"></i> <strong><?php echo h($meter['BillingProperty']['BillingClient']['phone']); ?></strong></span>
                                <?php } ?>
                            </div>
                        </td>

                        <!-- 2. PROPERTY UNIT LOCATION (RIGHT) -->
                        <td style="width:50%;background:#ffffff;padding:14px;vertical-align:top;border-bottom:1.5px solid #cbd5e1;">
                            <div style="font-size:10.5px;text-transform:uppercase;color:#475569;font-weight:800;letter-spacing:0.5px;"><?php echo __('PROPERTY UNIT LOCATION'); ?></div>
                            <div style="font-size:16px;font-weight:800;color:#0f172a;margin-top:4px;">
                                <i class="fa fa-building text-info"></i> 
                                <?php echo !empty($meter['BillingProperty']['title']) ? h($meter['BillingProperty']['title']) : (!empty($meter['BillingProperty']['property_no']) ? ('Unit #' . h($meter['BillingProperty']['property_no'])) : 'N/A'); ?>
                            </div>
                            <div style="margin-top:6px;font-size:12px;color:#334155;font-weight:700;">
                                Category: <strong style="color:#0f172a;"><?php echo !empty($meter['BillingProperty']['property_type']) ? h($meter['BillingProperty']['property_type']) : 'House'; ?></strong>
                            </div>
                        </td>
                    </tr>

                    <!-- Row 2: Meter Identification & Meter Reading -->
                    <tr>
                        <!-- 3. METER IDENTIFICATION (LEFT) -->
                        <td style="width:50%;background:#f8fafc;padding:14px;vertical-align:top;border-right:2px solid #cbd5e1;">
                            <div style="font-size:10.5px;text-transform:uppercase;color:#475569;font-weight:800;letter-spacing:0.5px;"><?php echo __('METER IDENTIFICATION'); ?></div>
                            <div style="font-size:17px;font-weight:800;color:#0369a1;margin-top:4px;">
                                <i class="fa fa-tachometer text-warning"></i> <?php echo h($meter['BillingMeter']['meter_number']); ?>
                            </div>
                            <div style="margin-top:4px;">
                                <span class="label label-info" style="font-size:11px;padding:3px 7px;font-weight:800;"><?php echo h($meter['BillingMeter']['utility_type']); ?></span>
                                <span class="label label-success" style="font-size:11px;padding:3px 7px;font-weight:800;margin-left:3px;"><?php echo ucfirst(h($meter['BillingMeter']['status'])); ?></span>
                            </div>
                        </td>

                        <!-- 4. METER READINGS STATUS (RIGHT) -->
                        <td style="width:50%;background:#ffffff;padding:14px;vertical-align:top;">
                            <div style="font-size:10.5px;text-transform:uppercase;color:#475569;font-weight:800;letter-spacing:0.5px;"><?php echo __('CURRENT METER READING'); ?></div>
                            <div style="font-size:18px;font-weight:800;color:#059669;margin-top:4px;">
                                <?php echo number_format((float)$meter['BillingMeter']['current_reading'], 2); ?> <span style="font-size:11.5px;color:#0f172a;font-weight:800;">Units</span>
                            </div>
                            <div style="margin-top:4px;font-size:11.5px;color:#334155;font-weight:700;">
                                Initial Reading: <strong style="color:#0f172a;"><?php echo number_format((float)$meter['BillingMeter']['initial_reading'], 2); ?></strong>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Financial Summary KPI Stat Table (2 Columns / 2x2 Grid) -->
    <div class="kpi-table-wrap" style="margin-bottom:18px;">
        <table class="table table-bordered" style="margin:0;vertical-align:middle;font-size:12.5px;border:2px solid #0f172a;width:100%;border-collapse:collapse;">
            <tbody>
                <!-- Row 1: Units Consumed & Billed Amount -->
                <tr>
                    <td style="width:50%;padding:10px 14px;background:#f8fafc;border:1px solid #cbd5e1;">
                        <div style="font-size:10px;text-transform:uppercase;color:#475569;font-weight:800;letter-spacing:0.5px;"><?php echo __('TOTAL UNITS CONSUMED'); ?></div>
                        <div style="font-size:18px;font-weight:800;color:#0284c7;margin-top:4px;">
                            <?php echo number_format($summary['totalUnits'], 1); ?> <span style="font-size:12px;color:#475569;font-weight:700;">Units</span>
                        </div>
                    </td>
                    <td style="width:50%;padding:10px 14px;background:#ffffff;border:1px solid #cbd5e1;">
                        <div style="font-size:10px;text-transform:uppercase;color:#475569;font-weight:800;letter-spacing:0.5px;"><?php echo __('TOTAL BILLED AMOUNT'); ?></div>
                        <div style="font-size:18px;font-weight:800;color:#d97706;margin-top:4px;">
                            PKR <?php echo number_format($summary['totalBilled'], 2); ?>
                        </div>
                    </td>
                </tr>

                <!-- Row 2: Payments Received & Outstanding Balance -->
                <tr>
                    <td style="width:50%;padding:10px 14px;background:#ffffff;border:1px solid #cbd5e1;">
                        <div style="font-size:10px;text-transform:uppercase;color:#475569;font-weight:800;letter-spacing:0.5px;"><?php echo __('TOTAL PAYMENTS RECEIVED'); ?></div>
                        <div style="font-size:18px;font-weight:800;color:#059669;margin-top:4px;">
                            PKR <?php echo number_format($summary['totalPaid'], 2); ?>
                        </div>
                    </td>
                    <td style="width:50%;padding:10px 14px;background:<?php echo ($summary['totalBalance'] > 0) ? '#fef2f2' : '#f0fdf4'; ?>;border:1px solid #cbd5e1;">
                        <div style="font-size:10px;text-transform:uppercase;color:#475569;font-weight:800;letter-spacing:0.5px;"><?php echo __('OUTSTANDING ARREARS / BALANCE'); ?></div>
                        <div style="font-size:18px;font-weight:800;color:<?php echo ($summary['totalBalance'] > 0) ? '#dc2626' : '#059669'; ?>;margin-top:4px;">
                            PKR <?php echo number_format($summary['totalBalance'], 2); ?>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Ledger View Mode Switcher Nav -->
    <div class="subm-ledger-nav btn-print-hide">
        <a href="#unifiedView" class="active" id="btnUnifiedView">
            <i class="fa fa-table"></i> <?php echo __('Unified Master Financial Ledger (100% Equal Row Heights)'); ?>
        </a>
        <a href="#sideBySideView" id="btnSideView">
            <i class="fa fa-columns"></i> <?php echo __('Side-by-Side Synchronized View'); ?>
        </a>
    </div>

    <div class="tab-content" style="width:100%;">

        <!-- VIEW 1: UNIFIED MASTER FINANCIAL LEDGER TABLE -->
        <div class="tab-pane active" id="unifiedView" style="width:100%;display:block;">
            <div class="panel ledger-table-panel" style="background:#ffffff;border:2px solid #0f172a;border-radius:10px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.06);margin-bottom:24px;">
                <div class="panel-heading" style="background:#0f172a;color:#ffffff;padding:14px 18px;">
                    <h3 class="panel-title" style="font-weight:800;font-size:14.5px;color:#ffffff;margin:0;display:flex;align-items:center;justify-content:space-between;">
                        <span><i class="fa fa-calculator text-warning"></i> <?php echo __('Unified Month-by-Month Meter & Payment Financial Ledger'); ?></span>
                        <span class="badge" style="background:#0284c7;font-weight:800;font-size:11.5px;padding:3px 9px;"><?php echo count($combinedLedger); ?> Month Records</span>
                    </h3>
                </div>
                <div class="panel-body" style="padding:0;overflow-x:auto;">
                    <table class="ledger-table-custom">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th><?php echo __('MONTH'); ?></th>
                                <th><?php echo __('PREVIOUS'); ?></th>
                                <th><?php echo __('CURRENT'); ?></th>
                                <th><?php echo __('CONSUMED'); ?></th>
                                <th><?php echo __('BILL NO'); ?></th>
                                <th><?php echo __('BILLED (PKR)'); ?></th>
                                <th><?php echo __('PAID (PKR)'); ?></th>
                                <th><?php echo __('BALANCE (PKR)'); ?></th>
                                <th><?php echo __('STATUS'); ?></th>
                                <th class="btn-print-hide"><?php echo __('ACTION'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($combinedLedger)){ ?>
                                <tr><td colspan="11" class="text-center text-muted" style="padding:24px;"><?php echo __('No reading or billing ledger records found for this meter.'); ?></td></tr>
                            <?php } else { $idx = 1; foreach($combinedLedger as $row){ 
                                $r = $row['reading'];
                                $b = $row['bill'];
                                $st = !empty($b['payment_status']) ? strtolower($b['payment_status']) : 'unpaid';
                                $stClass = ($st == 'paid') ? 'success' : (($st == 'partial') ? 'warning' : 'danger');
                            ?>
                                <tr class="sync-ledger-row">
                                    <td style="font-weight:800;color:#475569;"><?php echo $idx++; ?></td>
                                    <td><strong style="color:#0369a1;font-size:13.5px;"><i class="fa fa-calendar"></i> <?php echo h($row['formatted_month']); ?></strong></td>
                                    <td><strong style="color:#0f172a;"><?php echo !empty($r) ? number_format((float)$r['previous_reading'], 2) : '-'; ?></strong></td>
                                    <td><strong style="color:#059669;font-size:13.5px;"><?php echo !empty($r) ? number_format((float)$r['current_reading'], 2) : '-'; ?></strong></td>
                                    <td>
                                        <?php if(!empty($r)){ ?>
                                            <span class="badge" style="background:#0284c7;font-weight:800;font-size:11.5px;padding:4px 8px;"><?php echo number_format((float)$r['units_consumed'], 1); ?> Units</span>
                                        <?php } else { echo '-'; } ?>
                                    </td>
                                    <td>
                                        <?php if(!empty($b)){ ?>
                                            <strong style="color:#0f172a;font-family:monospace;font-size:12.5px;"><?php echo h(!empty($b['bill_number']) ? $b['bill_number'] : ('#' . $b['id'])); ?></strong>
                                        <?php } else { echo '-'; } ?>
                                    </td>
                                    <td><strong style="color:#0f172a;font-size:13px;"><?php echo !empty($b) ? ('PKR ' . number_format((float)$b['total_amount'], 2)) : '-'; ?></strong></td>
                                    <td><strong style="color:#059669;font-size:13px;"><?php echo !empty($b) ? ('PKR ' . number_format((float)$b['paid_amount'], 2)) : '-'; ?></strong></td>
                                    <td>
                                        <?php if(!empty($b)){ ?>
                                            <strong style="color:<?php echo ($row['balance'] > 0) ? '#dc2626' : '#059669'; ?>;font-size:13px;">
                                                PKR <?php echo number_format($row['balance'], 2); ?>
                                            </strong>
                                        <?php } else { echo '-'; } ?>
                                    </td>
                                    <td>
                                        <?php if(!empty($b)){ ?>
                                            <span class="label label-<?php echo $stClass; ?>" style="font-size:11px;padding:4px 8px;font-weight:800;"><?php echo ucfirst($st); ?></span>
                                        <?php } else { ?>
                                            <span class="label label-default" style="font-weight:700;">Unbilled</span>
                                        <?php } ?>
                                    </td>
                                    <td class="btn-print-hide">
                                        <?php if(!empty($b)){ ?>
                                            <a href="<?php echo $this->Html->url(array('controller' => 'billing_bills', 'action' => 'print_bill', $b['id'])); ?>" target="_blank" class="print-action-link">
                                                <i class="fa fa-print text-primary"></i> <?php echo __('Print Bill'); ?>
                                            </a>
                                        <?php } else { echo '-'; } ?>
                                    </td>
                                </tr>
                            <?php } } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- VIEW 2: SIDE-BY-SIDE SYNCHRONIZED ROW HEIGHT PANELS -->
        <div class="tab-pane" id="sideBySideView" style="width:100%;display:none;">
            <div class="row">
                <!-- Left Panel: Readings Log -->
                <div class="col-md-6">
                    <div class="panel ledger-table-panel" style="background:#ffffff;border:2px solid #0f172a;border-radius:10px;margin-bottom:20px;box-shadow:0 4px 12px rgba(0,0,0,0.06);overflow:hidden;">
                        <div class="panel-heading" style="background:#0f172a;color:#ffffff;padding:14px 18px;">
                            <h3 class="panel-title" style="font-weight:800;font-size:14.5px;color:#ffffff;margin:0;">
                                <i class="fa fa-list text-primary"></i> <?php echo __('Historical Meter Readings Log'); ?>
                            </h3>
                        </div>
                        <div class="panel-body" style="padding:0;overflow-x:auto;">
                            <table class="ledger-table-custom">
                                <thead>
                                    <tr class="sync-ledger-row">
                                        <th><?php echo __('MONTH'); ?></th>
                                        <th><?php echo __('PREVIOUS'); ?></th>
                                        <th><?php echo __('CURRENT'); ?></th>
                                        <th><?php echo __('CONSUMED'); ?></th>
                                        <th><?php echo __('READER'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(empty($combinedLedger)){ ?>
                                        <tr class="sync-ledger-row"><td colspan="5" class="text-center text-muted"><?php echo __('No readings recorded.'); ?></td></tr>
                                    <?php } else { foreach($combinedLedger as $row){ $r = $row['reading']; ?>
                                        <tr class="sync-ledger-row">
                                            <td><strong style="color:#0369a1;font-size:13px;"><?php echo h($row['formatted_month']); ?></strong></td>
                                            <td><strong style="color:#0f172a;"><?php echo !empty($r) ? number_format((float)$r['previous_reading'], 2) : '-'; ?></strong></td>
                                            <td><strong style="color:#059669;font-size:13px;"><?php echo !empty($r) ? number_format((float)$r['current_reading'], 2) : '-'; ?></strong></td>
                                            <td>
                                                <?php if(!empty($r)){ ?>
                                                    <span class="badge" style="background:#0284c7;font-weight:800;"><?php echo number_format((float)$r['units_consumed'], 1); ?> Units</span>
                                                <?php } else { echo '-'; } ?>
                                            </td>
                                            <td><strong style="color:#334155;font-size:11px;"><?php echo !empty($r['reader_name']) ? h($r['reader_name']) : 'Admin'; ?></strong></td>
                                        </tr>
                                    <?php } } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Right Panel: Invoices Ledger -->
                <div class="col-md-6">
                    <div class="panel ledger-table-panel" style="background:#ffffff;border:2px solid #0f172a;border-radius:10px;margin-bottom:20px;box-shadow:0 4px 12px rgba(0,0,0,0.06);overflow:hidden;">
                        <div class="panel-heading" style="background:#0f172a;color:#ffffff;padding:14px 18px;">
                            <h3 class="panel-title" style="font-weight:800;font-size:14.5px;color:#ffffff;margin:0;">
                                <i class="fa fa-file-text-o text-success"></i> <?php echo __('Invoices & Payment History Ledger'); ?>
                            </h3>
                        </div>
                        <div class="panel-body" style="padding:0;overflow-x:auto;">
                            <table class="ledger-table-custom">
                                <thead>
                                    <tr class="sync-ledger-row">
                                        <th><?php echo __('BILL NO'); ?></th>
                                        <th><?php echo __('PERIOD'); ?></th>
                                        <th><?php echo __('TOTAL (PKR)'); ?></th>
                                        <th><?php echo __('STATUS'); ?></th>
                                        <th class="btn-print-hide"><?php echo __('ACTION'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(empty($combinedLedger)){ ?>
                                        <tr class="sync-ledger-row"><td colspan="5" class="text-center text-muted"><?php echo __('No invoices generated.'); ?></td></tr>
                                    <?php } else { foreach($combinedLedger as $row){ $b = $row['bill']; 
                                        $st = !empty($b['payment_status']) ? strtolower($b['payment_status']) : 'unpaid';
                                        $stClass = ($st == 'paid') ? 'success' : (($st == 'partial') ? 'warning' : 'danger');
                                    ?>
                                        <tr class="sync-ledger-row">
                                            <td>
                                                <?php if(!empty($b)){ ?>
                                                    <strong style="color:#0f172a;font-family:monospace;"><?php echo h(!empty($b['bill_number']) ? $b['bill_number'] : ('#' . $b['id'])); ?></strong>
                                                <?php } else { echo '-'; } ?>
                                            </td>
                                            <td><strong style="color:#0f172a;"><?php echo h($row['formatted_month']); ?></strong></td>
                                            <td><strong style="color:#0369a1;"><?php echo !empty($b) ? ('PKR ' . number_format((float)$b['total_amount'], 2)) : '-'; ?></strong></td>
                                            <td>
                                                <?php if(!empty($b)){ ?>
                                                    <span class="label label-<?php echo $stClass; ?>" style="font-weight:800;"><?php echo ucfirst($st); ?></span>
                                                <?php } else { echo '-'; } ?>
                                            </td>
                                            <td class="btn-print-hide">
                                                <?php if(!empty($b)){ ?>
                                                    <a href="<?php echo $this->Html->url(array('controller' => 'billing_bills', 'action' => 'print_bill', $b['id'])); ?>" target="_blank" class="print-action-link">
                                                        <i class="fa fa-print text-primary"></i> <?php echo __('Print'); ?>
                                                    </a>
                                                <?php } else { echo '-'; } ?>
                                            </td>
                                        </tr>
                                    <?php } } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Printable Official A4 Footer & Signature Lines -->
    <div class="print-footer">
        <div style="text-align:center;font-size:10.5px;color:#334155;font-weight:700;margin-bottom:14px;border-bottom:1px dashed #0f172a;padding-bottom:8px;">
            <strong><?php echo h($societyName); ?></strong> — <?php echo h($societyAddress); ?> | Helpline: <?php echo h($societyPhone); ?> | Email: <?php echo h($societyEmail); ?>
        </div>
        <div style="display:flex;justify-content:space-between;align-items:flex-end;margin-top:14px;">
            <div style="width:40%;">
                <div style="font-size:10px;color:#475569;margin-bottom:28px;font-weight:700;">
                    * Official computer-generated statement issued by <?php echo h($societyName); ?> Accounts & Billing Department.
                </div>
                <div style="border-top:1.5px solid #000000;padding-top:3px;font-weight:800;font-size:10.5px;color:#000000;">
                    PREPARED BY (ACCOUNTS DEPT)
                </div>
            </div>
            <div style="width:40%;text-align:right;">
                <div style="font-size:10px;color:#475569;margin-bottom:28px;font-weight:700;">
                    Official Society Seal & Stamp
                </div>
                <div style="border-top:1.5px solid #000000;padding-top:3px;font-weight:800;font-size:10.5px;color:#000000;">
                    AUTHORIZED SIGNATURE & STAMP
                </div>
            </div>
        </div>
    </div>

</div>

<script type="text/javascript">
function equalizeSideBySideRows() {
    var viewSide = document.getElementById('sideBySideView');
    if (!viewSide || viewSide.style.display === 'none') return;

    var leftTh = viewSide.querySelector('.col-md-6:first-child thead tr');
    var rightTh = viewSide.querySelector('.col-md-6:last-child thead tr');
    if (leftTh && rightTh) {
        leftTh.style.height = '';
        rightTh.style.height = '';
        var thH = Math.max(leftTh.offsetHeight, rightTh.offsetHeight, 48);
        leftTh.style.height = thH + 'px';
        rightTh.style.height = thH + 'px';
    }

    var leftTrs = viewSide.querySelectorAll('.col-md-6:first-child tbody tr');
    var rightTrs = viewSide.querySelectorAll('.col-md-6:last-child tbody tr');
    var count = Math.min(leftTrs.length, rightTrs.length);

    for (var i = 0; i < count; i++) {
        leftTrs[i].style.height = '';
        rightTrs[i].style.height = '';
        var h1 = leftTrs[i].offsetHeight;
        var h2 = rightTrs[i].offsetHeight;
        var maxH = Math.max(h1, h2, 52);
        leftTrs[i].style.height = maxH + 'px';
        rightTrs[i].style.height = maxH + 'px';
    }
}

function switchLedgerTab(targetTab) {
    var btnUnified = document.getElementById('btnUnifiedView');
    var btnSide = document.getElementById('btnSideView');
    var viewUnified = document.getElementById('unifiedView');
    var viewSide = document.getElementById('sideBySideView');

    if (!btnUnified || !btnSide || !viewUnified || !viewSide) return;

    if (targetTab === 'sideBySideView' || targetTab === '#sideBySideView') {
        btnSide.classList.add('active');
        btnUnified.classList.remove('active');
        viewSide.style.display = 'block';
        viewUnified.style.display = 'none';
        setTimeout(equalizeSideBySideRows, 50);
    } else {
        btnUnified.classList.add('active');
        btnSide.classList.remove('active');
        viewUnified.style.display = 'block';
        viewSide.style.display = 'none';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    var btnUnified = document.getElementById('btnUnifiedView');
    var btnSide = document.getElementById('btnSideView');

    if (btnUnified) {
        btnUnified.addEventListener('click', function(e) {
            e.preventDefault();
            switchLedgerTab('unifiedView');
            if(history.pushState) {
                history.pushState(null, null, '#unifiedView');
            }
        });
    }

    if (btnSide) {
        btnSide.addEventListener('click', function(e) {
            e.preventDefault();
            switchLedgerTab('sideBySideView');
            if(history.pushState) {
                history.pushState(null, null, '#sideBySideView');
            }
        });
    }

    // Check hash on page load
    var hash = window.location.hash;
    if (hash === '#sideBySideView') {
        switchLedgerTab('sideBySideView');
    } else {
        switchLedgerTab('unifiedView');
    }
});

window.addEventListener('hashchange', function() {
    var hash = window.location.hash;
    switchLedgerTab(hash);
});

window.addEventListener('resize', function() {
    equalizeSideBySideRows();
});
</script>
