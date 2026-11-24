<?php
/**
 * White Villas Lahore — Online Utility & Maintenance Bill Check Portal
 * Rendered under Active Real Estate Theme Layout
 *
 * Features:
 * - Active Theme Styling & Consistent Header Integration
 * - Multi-Identifier Search (Consumer ID, Bill ID, Meter Number)
 * - Automatic Search Boxes Hiding on Bill Load with "Search Another Bill" Action
 * - Authentic Backend Print Preview Voucher (Not Raw Data)
 * - Zoom & Print-Ready Voucher Controls
 * - 6-Month Billing History Accordion with Dynamic Bill Swapping
 */
$pageTitle = 'Online Bill Check & Payment Portal';
$this->set('title_for_layout', $pageTitle . ' | ' . (!empty($societyInfo['name']) ? $societyInfo['name'] : 'White Villas Lahore'));
$hasLoadedBill = !empty($bill) && !empty($renderedHtml);
?>

<style>
/* =========================================================================
   PORTAL WRAPPER & ACTIVE THEME STYLING
   ========================================================================= */
.wv-portal-wrap {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    color: #0f172a;
    width: 100%;
    margin: 0 auto;
}

.wv-portal-header-banner {
    background: linear-gradient(135deg, #07152a 0%, #064e3b 100%);
    color: #ffffff;
    border-radius: 12px;
    padding: 24px 28px;
    margin-bottom: 24px;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.12);
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.wv-portal-title {
    font-size: 22px;
    font-weight: 900;
    margin: 0;
    letter-spacing: -0.3px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.wv-portal-subtitle {
    margin: 4px 0 0 0;
    font-size: 13px;
    color: #a7f3d0;
}

.wv-portal-urdu {
    font-family: 'Noto Nastaliq Urdu', serif;
    direction: rtl;
    text-align: right;
    font-size: 15px;
    color: #6ee7b7;
    font-weight: 700;
}

.wv-portal-badges {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.wv-portal-badge {
    background: rgba(255, 255, 255, 0.12);
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: #ffffff;
    font-size: 11.5px;
    font-weight: 700;
    padding: 6px 12px;
    border-radius: 30px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    text-decoration: none;
    transition: all 0.2s;
}

.wv-portal-badge:hover {
    background: rgba(255, 255, 255, 0.22);
    color: #ffffff;
}

/* =========================================================================
   HERO SEARCH CARD (HIDDEN ONCE BILL IS LOADED)
   ========================================================================= */
.wv-search-container {
    background: #ffffff;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 16px rgba(15, 23, 42, 0.05);
    padding: 24px;
    margin-bottom: 24px;
    transition: all 0.3s ease;
}

.wv-search-tabs {
    display: flex;
    background: #f1f5f9;
    border-radius: 8px;
    padding: 4px;
    gap: 4px;
    margin-bottom: 20px;
}

.wv-tab-btn {
    flex: 1;
    background: transparent;
    border: none;
    padding: 10px 14px;
    font-size: 13px;
    font-weight: 700;
    color: #64748b;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
    text-align: center;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
}

.wv-tab-btn:hover {
    color: #0f172a;
}

.wv-tab-btn.active {
    background: #ffffff;
    color: #064e3b;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
}

.wv-input-group {
    position: relative;
    display: flex;
    align-items: center;
    flex: 1;
}

.wv-input-icon {
    position: absolute;
    left: 16px;
    color: #047857;
    font-size: 16px;
    pointer-events: none;
}

.wv-search-input {
    width: 100%;
    padding: 13px 16px 13px 44px;
    font-size: 15px;
    border: 2px solid #cbd5e1;
    border-radius: 8px;
    font-weight: 700;
    color: #0f172a;
    background: #ffffff;
    transition: all 0.2s;
}

.wv-search-input:focus {
    outline: none;
    border-color: #047857;
    box-shadow: 0 0 0 4px rgba(4, 120, 87, 0.12);
}

.wv-btn-submit {
    background: linear-gradient(135deg, #064e3b 0%, #047857 100%);
    color: #ffffff;
    font-weight: 800;
    font-size: 14.5px;
    border: none;
    border-radius: 8px;
    padding: 13px 28px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s;
    white-space: nowrap;
    box-shadow: 0 3px 8px rgba(4, 120, 87, 0.25);
}

.wv-btn-submit:hover {
    background: linear-gradient(135deg, #022c22 0%, #064e3b 100%);
    transform: translateY(-1px);
    box-shadow: 0 5px 14px rgba(4, 120, 87, 0.35);
}

.wv-sample-chips {
    margin-top: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
    font-size: 12px;
    color: #64748b;
}

.wv-chip {
    background: #e2e8f0;
    color: #1e293b;
    padding: 3px 10px;
    border-radius: 14px;
    cursor: pointer;
    font-family: monospace;
    font-weight: 700;
    transition: all 0.2s;
}

.wv-chip:hover {
    background: #047857;
    color: #ffffff;
}

/* =========================================================================
   ACTIVE BILL TOP BAR (SHOWN WHEN BILL IS LOADED)
   ========================================================================= */
.wv-active-bill-bar {
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-left: 5px solid #047857;
    border-radius: 10px;
    padding: 14px 18px;
    margin-bottom: 20px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
}

.wv-btn-search-another {
    background: #f1f5f9;
    color: #0f172a;
    border: 1.5px solid #cbd5e1;
    border-radius: 6px;
    padding: 8px 16px;
    font-size: 13px;
    font-weight: 800;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s;
}

.wv-btn-search-another:hover {
    background: #047857;
    color: #ffffff;
    border-color: #047857;
}

.wv-badge-paid {
    background: #dcfce7;
    color: #15803d;
    border: 1px solid #86efac;
    padding: 4px 10px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.wv-badge-unpaid {
    background: #fef3c7;
    color: #b45309;
    border: 1px solid #fde68a;
    padding: 4px 10px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.wv-badge-overdue {
    background: #fee2e2;
    color: #b91c1c;
    border: 1px solid #fca5a5;
    padding: 4px 10px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.wv-btn-action {
    background: #ffffff;
    color: #0f172a;
    border: 1.5px solid #cbd5e1;
    border-radius: 6px;
    padding: 8px 14px;
    font-size: 12.5px;
    font-weight: 700;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s;
    text-decoration: none;
}

.wv-btn-action:hover {
    background: #f8fafc;
    color: #047857;
    border-color: #047857;
}

.wv-btn-print {
    background: #16a34a;
    color: #ffffff;
    border: none;
    border-radius: 6px;
    padding: 8px 16px;
    font-size: 13px;
    font-weight: 800;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    box-shadow: 0 2px 6px rgba(22, 163, 74, 0.3);
    transition: all 0.2s;
}

.wv-btn-print:hover {
    background: #15803d;
    color: #ffffff;
}

/* =========================================================================
   VOUCHER PREVIEW CONTAINER (BACKEND PRINT PREVIEW)
   ========================================================================= */
.wv-voucher-wrapper {
    background: #525659;
    border-radius: 10px;
    padding: 20px 10px;
    box-shadow: inset 0 2px 8px rgba(0, 0, 0, 0.25);
    margin-bottom: 24px;
    display: flex;
    flex-direction: column;
    align-items: center;
    overflow-x: auto;
}

.wv-voucher-toolbar {
    width: 100%;
    max-width: 820px;
    background: #323639;
    color: #ffffff;
    padding: 8px 16px;
    border-radius: 8px 8px 0 0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 12px;
    font-weight: 700;
}

.wv-voucher-frame {
    width: 100%;
    max-width: 820px;
    height: 1140px;
    background: #ffffff;
    border: none;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.35);
    border-radius: 0 0 8px 8px;
    display: block;
    transition: transform 0.2s ease;
    transform-origin: top center;
}

/* Alert Banner */
.wv-alert-box {
    background: #fef2f2;
    border-left: 4px solid #dc2626;
    border-radius: 8px;
    padding: 14px 18px;
    margin-bottom: 20px;
    display: none;
}
.wv-alert-box.active {
    display: block;
}

/* 6 Months History Accordion */
.wv-history-box {
    background: #ffffff;
    border-radius: 10px;
    border: 1px solid #e2e8f0;
    padding: 20px;
    margin-bottom: 24px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

.wv-history-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
    margin-top: 10px;
}

.wv-history-table th {
    background: #f8fafc;
    color: #475569;
    font-weight: 700;
    text-transform: uppercase;
    font-size: 11px;
    padding: 10px 12px;
    border-bottom: 2px solid #e2e8f0;
    text-align: left;
}

.wv-history-table td {
    padding: 10px 12px;
    border-bottom: 1px solid #f1f5f9;
    color: #1e293b;
}

.wv-history-table tr:hover td {
    background: #f8fafc;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .wv-search-tabs {
        flex-direction: column;
    }
    .wv-voucher-wrapper {
        padding: 10px 4px;
    }
    .wv-voucher-frame {
        height: 820px;
    }
}
</style>

<div class="wv-portal-wrap">

    <!-- 1. HEADER BANNER (THEME INTEGRATED) -->
    <div class="wv-portal-header-banner">
        <div>
            <h1 class="wv-portal-title">
                <i class="fa fa-file-text-o" style="color:#34d399;"></i> Utility &amp; Maintenance Bill Check
            </h1>
            <p class="wv-portal-subtitle">
                Official resident bill lookup, verified duplicate voucher preview, and payment portal.
            </p>
        </div>
        <div class="wv-portal-urdu">
            وائٹ ولاز لاہور — آن لائن یوٹیلیٹی و مینٹیننس بل پورٹل
        </div>
        <div class="wv-portal-badges">
            <a href="tel:03091101156" class="wv-portal-badge" title="Official Accounts Desk">
                <i class="fa fa-phone"></i> 0309-1101156
            </a>
            <a href="https://wa.me/923091101156" target="_blank" class="wv-portal-badge" title="WhatsApp Support">
                <i class="fa fa-whatsapp text-success"></i> WhatsApp Help
            </a>
        </div>
    </div>

    <!-- 2. SEARCH CARD CONTAINER (AUTOMATICALLY HIDDEN WHEN BILL IS LOADED) -->
    <div id="searchCardContainer" class="wv-search-container" style="<?php echo $hasLoadedBill ? 'display: none;' : 'display: block;'; ?>">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;flex-wrap:wrap;gap:8px;">
            <div>
                <h3 style="font-size:17px;font-weight:800;color:#0f172a;margin:0;">
                    <i class="fa fa-search text-success"></i> Find Your Bill Voucher
                </h3>
                <p style="margin:2px 0 0 0;font-size:12.5px;color:#64748b;">
                    Enter any one identifier below to load your complete official bill print preview.
                </p>
            </div>
            <div style="font-size:13px;font-weight:700;color:#047857;" class="wv-portal-urdu">
                صارف کا شناختی نمبر یا بل نمبر درج کریں
            </div>
        </div>

        <!-- Mode Selector Tabs -->
        <div class="wv-search-tabs">
            <button type="button" class="wv-tab-btn active" data-type="consumer_id" onclick="setSearchMode('consumer_id')">
                <i class="fa fa-user-circle-o"></i> 1. Consumer ID (صارف شناختی نمبر)
            </button>
            <button type="button" class="wv-tab-btn" data-type="bill_id" onclick="setSearchMode('bill_id')">
                <i class="fa fa-file-text-o"></i> 2. Bill ID / Ref (بل ریفرنس نمبر)
            </button>
            <button type="button" class="wv-tab-btn" data-type="meter_number" onclick="setSearchMode('meter_number')">
                <i class="fa fa-tachometer"></i> 3. Meter Number (میٹر نمبر)
            </button>
        </div>

        <!-- Search Form -->
        <form id="onlineBillForm" onsubmit="handleOnlineBillSearch(event)">
            <input type="hidden" id="selectedInputType" name="input_type" value="<?php echo h($inputType); ?>">

            <div style="display:flex;gap:12px;flex-wrap:wrap;">
                <div class="wv-input-group">
                    <i class="fa fa-id-card-o wv-input-icon" id="searchIcon"></i>
                    <input type="text"
                           id="billQueryInput"
                           name="query"
                           class="wv-search-input"
                           placeholder="e.g. WV7010 or WV-CON-1024"
                           value="<?php echo h($query); ?>"
                           required
                           autocomplete="off">
                </div>
                <button type="submit" class="wv-btn-submit" id="btnSubmitSearch">
                    <span id="btnSearchIcon"><i class="fa fa-arrow-circle-right"></i></span>
                    <span id="btnSearchText">Check Bill / بل چیک کریں</span>
                </button>
            </div>

            <!-- Quick Clickable Chips -->
            <div class="wv-sample-chips">
                <span>Quick Test Demo:</span>
                <span class="wv-chip" onclick="quickFillSearch('WV7010', 'consumer_id')" title="Live DB Resident WV7010">WV7010 (Live DB)</span>
                <span class="wv-chip" onclick="quickFillSearch('WB9170', 'bill_id')" title="Live DB Bill WB9170">WB9170 (Live DB)</span>
                <span class="wv-chip" onclick="quickFillSearch('WV-CON-1024', 'consumer_id')" title="Demo Consumer ID">WV-CON-1024</span>
                <span class="wv-chip" onclick="quickFillSearch('BILL-2026-0089', 'bill_id')" title="Demo Bill ID">BILL-2026-0089</span>
                <span class="wv-chip" onclick="quickFillSearch('MTR-55412', 'meter_number')" title="Demo Meter No">MTR-55412</span>
            </div>
        </form>
    </div>

    <!-- 3. INLINE ALERT BANNER (IF RECORD NOT FOUND) -->
    <div id="alertBanner" class="wv-alert-box <?php echo (!empty($error)) ? 'active' : ''; ?>">
        <div style="display:flex;align-items:flex-start;gap:12px;">
            <i class="fa fa-exclamation-triangle" style="font-size:20px;color:#dc2626;margin-top:2px;"></i>
            <div style="flex:1;">
                <h4 style="font-size:14.5px;font-weight:800;color:#991b1b;margin:0 0 4px 0;" id="alertTitle">
                    Bill Record Not Found (کوئی بل نہیں ملا)
                </h4>
                <p style="margin:0;font-size:13px;line-height:1.4;color:#7f1d1d;" id="alertMessage">
                    <?php echo !empty($error) ? h($error) : 'We could not find any active bill record matching your query. Please double-check your Consumer ID or Bill Reference from your paper voucher.'; ?>
                </p>
                <div style="margin-top:8px;font-size:12px;color:#991b1b;">
                    <strong>Need assistance?</strong> Contact White Villas Accounts desk at 
                    <a href="tel:03091101156" style="color:#b91c1c;font-weight:bold;">0309-1101156</a> or WhatsApp 
                    <a href="https://wa.me/923091101156" target="_blank" style="color:#b91c1c;font-weight:bold;">0309-1101156</a>.
                </div>
            </div>
        </div>
    </div>

    <!-- 4. ACTIVE BILL TOP NAV BAR (SHOWN WHEN BILL IS LOADED) -->
    <div id="activeBillBar" class="wv-active-bill-bar" style="<?php echo $hasLoadedBill ? 'display: flex;' : 'display: none;'; ?>">
        <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
            <!-- Button to unhide search boxes -->
            <button type="button" class="wv-btn-search-another" onclick="showSearchCard()" title="Search for another resident bill">
                <i class="fa fa-search"></i> Search Another Bill (نیا بل تلاش کریں)
            </button>

            <!-- Resident & Bill Summary -->
            <div style="font-size:13px;">
                <span style="color:#64748b;font-weight:600;">Showing Bill:</span>
                <strong id="barConsumerName" style="color:#0f172a;margin-left:4px;">
                    <?php echo !empty($bill['consumer_name']) ? h($bill['consumer_name']) : ''; ?>
                </strong>
                <span style="color:#94a3b8;margin:0 6px;">|</span>
                <span style="color:#64748b;">Ref:</span>
                <strong id="barBillNumber" style="color:#0369a1;font-family:monospace;">
                    <?php echo !empty($bill['bill_number']) ? h($bill['bill_number']) : ''; ?>
                </strong>
            </div>

            <!-- Status Pill -->
            <div id="barStatusBadge">
                <?php
                if (!empty($bill['payment_status'])) {
                    $st = strtolower($bill['payment_status']);
                    if ($st === 'paid') {
                        echo '<span class="wv-badge-paid"><i class="fa fa-check-circle"></i> PAID</span>';
                    } elseif ($st === 'overdue') {
                        echo '<span class="wv-badge-overdue"><i class="fa fa-exclamation-circle"></i> OVERDUE</span>';
                    } else {
                        echo '<span class="wv-badge-unpaid"><i class="fa fa-clock-o"></i> UNPAID</span>';
                    }
                }
                ?>
            </div>
        </div>

        <!-- Action Buttons -->
        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
            <button type="button" class="wv-btn-print" onclick="printVoucher()">
                <i class="fa fa-print"></i> Print Bill (پرنٹ بل)
            </button>
            <button type="button" class="wv-btn-action" onclick="downloadVoucherPdf()">
                <i class="fa fa-download"></i> Download PDF
            </button>
            <a id="btnOpenNewTab" href="<?php echo $this->Html->url(array('action' => 'print_bill', !empty($bill['id']) ? $bill['id'] : '')); ?>" target="_blank" class="wv-btn-action" title="Open full voucher in new window">
                <i class="fa fa-external-link"></i> Full View
            </a>
        </div>
    </div>

    <!-- 5. BACKEND PRINT PREVIEW VOUCHER SECTION -->
    <div id="billVoucherSection" style="<?php echo $hasLoadedBill ? 'display: block;' : 'display: none;'; ?>">
        <div class="wv-voucher-wrapper">
            <!-- Toolbar with Zoom & Controls -->
            <div class="wv-voucher-toolbar">
                <div style="display:flex;align-items:center;gap:10px;">
                    <i class="fa fa-file-text-o text-success"></i>
                    <span>Official Letter-Size Bill Voucher Preview (backend print format)</span>
                </div>
                <div style="display:flex;align-items:center;gap:8px;">
                    <button type="button" class="btn btn-sm btn-dark" onclick="zoomVoucher(0.1)" title="Zoom In" style="padding:2px 8px;font-size:11px;">
                        <i class="fa fa-search-plus"></i> +
                    </button>
                    <button type="button" class="btn btn-sm btn-dark" onclick="zoomVoucher(-0.1)" title="Zoom Out" style="padding:2px 8px;font-size:11px;">
                        <i class="fa fa-search-minus"></i> -
                    </button>
                    <button type="button" class="btn btn-sm btn-dark" onclick="resetVoucherZoom()" title="Reset Zoom" style="padding:2px 8px;font-size:11px;">
                        100%
                    </button>
                    <button type="button" class="btn btn-sm btn-success" onclick="printVoucher()" style="padding:2px 10px;font-size:11px;font-weight:700;">
                        <i class="fa fa-print"></i> Print
                    </button>
                </div>
            </div>

            <!-- Authentic Voucher Preview Frame -->
            <iframe id="billVoucherFrame"
                    class="wv-voucher-frame"
                    srcdoc="<?php echo htmlspecialchars($renderedHtml); ?>"
                    onload="adjustFrameHeight(this)">
            </iframe>
        </div>

        <!-- 6. BILLING HISTORY SECTION (IF AVAILABLE) -->
        <div id="historyContainer" class="wv-history-box" style="<?php echo (!empty($history) ? 'display:block;' : 'display:none;'); ?>">
            <div style="display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid #e2e8f0;padding-bottom:10px;margin-bottom:10px;">
                <h4 style="font-size:15px;font-weight:800;color:#0f172a;margin:0;">
                    <i class="fa fa-history text-primary"></i> Last 6 Months Billing History
                </h4>
                <span style="font-size:12px;color:#64748b;">Click "View" to preview that month's official bill voucher</span>
            </div>
            <div style="overflow-x:auto;">
                <table class="wv-history-table">
                    <thead>
                        <tr>
                            <th>Billing Month</th>
                            <th>Bill #</th>
                            <th>Total Amount</th>
                            <th>Paid Amount</th>
                            <th>Balance</th>
                            <th>Status</th>
                            <th style="text-align:center;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="historyTableBody">
                        <?php if (!empty($history)): ?>
                            <?php foreach ($history as $h): ?>
                                <tr>
                                    <td><strong><?php echo h($h['billing_month']); ?></strong></td>
                                    <td><span style="font-family:monospace;"><?php echo h($h['bill_number']); ?></span></td>
                                    <td>PKR <?php echo number_format((float)$h['total_amount'], 2); ?></td>
                                    <td>PKR <?php echo number_format((float)$h['paid_amount'], 2); ?></td>
                                    <td>PKR <?php echo number_format((float)$h['balance_amount'], 2); ?></td>
                                    <td>
                                        <?php if ($h['payment_status'] === 'paid'): ?>
                                            <span class="wv-badge-paid">PAID</span>
                                        <?php else: ?>
                                            <span class="wv-badge-unpaid">UNPAID</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align:center;">
                                        <button type="button" class="btn btn-sm btn-outline-success" onclick="loadSpecificBill('<?php echo h($h['bill_number']); ?>')" style="padding:2px 10px;font-size:11.5px;font-weight:700;">
                                            <i class="fa fa-eye"></i> View Bill
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<script>
var currentVoucherZoom = 1.0;
var searchCardContainer = document.getElementById('searchCardContainer');
var activeBillBar = document.getElementById('activeBillBar');
var billVoucherSection = document.getElementById('billVoucherSection');
var alertBanner = document.getElementById('alertBanner');
var billVoucherFrame = document.getElementById('billVoucherFrame');

/**
 * Switch Search Mode Tabs
 */
function setSearchMode(type) {
    document.getElementById('selectedInputType').value = type;
    var tabs = document.querySelectorAll('.wv-tab-btn');
    tabs.forEach(function(t) {
        if (t.getAttribute('data-type') === type) {
            t.classList.add('active');
        } else {
            t.classList.remove('active');
        }
    });

    var input = document.getElementById('billQueryInput');
    var icon = document.getElementById('searchIcon');

    if (type === 'consumer_id') {
        input.placeholder = 'e.g. WV7010 or WV-CON-1024';
        icon.className = 'fa fa-id-card-o wv-input-icon';
    } else if (type === 'bill_id') {
        input.placeholder = 'e.g. WB9170 or BILL-2026-0089';
        icon.className = 'fa fa-file-text-o wv-input-icon';
    } else if (type === 'meter_number') {
        input.placeholder = 'e.g. MTR-55412 or 025 0362071';
        icon.className = 'fa fa-tachometer wv-input-icon';
    }
    input.focus();
}

/**
 * Quick fill demo chip
 */
function quickFillSearch(val, type) {
    setSearchMode(type);
    document.getElementById('billQueryInput').value = val;
    handleOnlineBillSearch(null);
}

/**
 * Reveal search card when user clicks "Search Another Bill"
 */
function showSearchCard() {
    searchCardContainer.style.display = 'block';
    searchCardContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
    document.getElementById('billQueryInput').focus();
    document.getElementById('billQueryInput').select();
}

/**
 * AJAX Bill Search Form Handler
 */
function handleOnlineBillSearch(e) {
    if (e && e.preventDefault) {
        e.preventDefault();
    }

    var query = document.getElementById('billQueryInput').value.trim();
    var inputType = document.getElementById('selectedInputType').value;

    if (!query) {
        alert('Please enter a Consumer ID, Bill Reference, or Meter Number.');
        return;
    }

    var btn = document.getElementById('btnSubmitSearch');
    var btnIcon = document.getElementById('btnSearchIcon');
    var btnText = document.getElementById('btnSearchText');

    btn.disabled = true;
    btnIcon.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';
    btnText.textContent = 'Searching...';

    // Hide any previous alert
    alertBanner.classList.remove('active');

    var postData = {
        query: query,
        input_type: inputType
    };

    fetch('<?php echo $this->Html->url(array("action" => "checkOnline")); ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: JSON.stringify(postData)
    })
    .then(function(res) {
        return res.json();
    })
    .then(function(data) {
        btn.disabled = false;
        btnIcon.innerHTML = '<i class="fa fa-arrow-circle-right"></i>';
        btnText.textContent = 'Check Bill / بل چیک کریں';

        if (data && data.success && data.rendered_html) {
            // 1. HIDE SEARCH BOXES WHEN BILL IS LOADED
            searchCardContainer.style.display = 'none';

            // 2. SHOW ACTIVE BILL BAR & UPDATE LABELS
            activeBillBar.style.display = 'flex';
            document.getElementById('barConsumerName').textContent = data.bill.consumer_name || 'Valued Resident';
            document.getElementById('barBillNumber').textContent = data.bill.bill_number || '';

            var st = (data.bill.payment_status || 'unpaid').toLowerCase();
            var badgeHtml = '';
            if (st === 'paid') {
                badgeHtml = '<span class="wv-badge-paid"><i class="fa fa-check-circle"></i> PAID</span>';
            } else if (st === 'overdue') {
                badgeHtml = '<span class="wv-badge-overdue"><i class="fa fa-exclamation-circle"></i> OVERDUE</span>';
            } else {
                badgeHtml = '<span class="wv-badge-unpaid"><i class="fa fa-clock-o"></i> UNPAID</span>';
            }
            document.getElementById('barStatusBadge').innerHTML = badgeHtml;

            // Update Full View link
            var fullViewUrl = '<?php echo $this->Html->url(array("action" => "print_bill")); ?>/' + (data.bill.id || '');
            document.getElementById('btnOpenNewTab').href = fullViewUrl;

            // 3. RENDER BACKEND PRINT PREVIEW VOUCHER (IN IFRAME)
            billVoucherSection.style.display = 'block';
            billVoucherFrame.srcdoc = data.rendered_html;

            // 4. UPDATE HISTORY TABLE IF PRESENT
            if (data.history && data.history.length > 0) {
                var tbody = document.getElementById('historyTableBody');
                tbody.innerHTML = '';
                data.history.forEach(function(h) {
                    var tr = document.createElement('tr');
                    var stBadge = (h.payment_status === 'paid') ? '<span class="wv-badge-paid">PAID</span>' : '<span class="wv-badge-unpaid">UNPAID</span>';
                    tr.innerHTML = '<td><strong>' + escapeHtml(h.billing_month) + '</strong></td>' +
                                   '<td><span style="font-family:monospace;">' + escapeHtml(h.bill_number) + '</span></td>' +
                                   '<td>PKR ' + parseFloat(h.total_amount).toFixed(2) + '</td>' +
                                   '<td>PKR ' + parseFloat(h.paid_amount).toFixed(2) + '</td>' +
                                   '<td>PKR ' + parseFloat(h.balance_amount).toFixed(2) + '</td>' +
                                   '<td>' + stBadge + '</td>' +
                                   '<td style="text-align:center;"><button type="button" class="btn btn-sm btn-outline-success" onclick="loadSpecificBill(\'' + escapeHtml(h.bill_number) + '\')" style="padding:2px 10px;font-size:11.5px;font-weight:700;"><i class="fa fa-eye"></i> View Bill</button></td>';
                    tbody.appendChild(tr);
                });
                document.getElementById('historyContainer').style.display = 'block';
            }

            // Scroll down to active bill bar smoothly
            activeBillBar.scrollIntoView({ behavior: 'smooth', block: 'start' });

        } else {
            // Show alert error
            var msg = (data && data.message) ? data.message : 'No bill record found for the provided identifier.';
            document.getElementById('alertMessage').textContent = msg;
            alertBanner.classList.add('active');
            alertBanner.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    })
    .catch(function(err) {
        btn.disabled = false;
        btnIcon.innerHTML = '<i class="fa fa-arrow-circle-right"></i>';
        btnText.textContent = 'Check Bill / بل چیک کریں';

        document.getElementById('alertMessage').textContent = 'Unable to connect to the billing server. Please try again.';
        alertBanner.classList.add('active');
    });
}

/**
 * Load specific past bill
 */
function loadSpecificBill(billNo) {
    document.getElementById('billQueryInput').value = billNo;
    setSearchMode('bill_id');
    handleOnlineBillSearch(null);
}

/**
 * Trigger Print on the authentic voucher frame
 */
function printVoucher() {
    if (billVoucherFrame && billVoucherFrame.contentWindow) {
        billVoucherFrame.contentWindow.focus();
        billVoucherFrame.contentWindow.print();
    } else {
        window.print();
    }
}

/**
 * Download PDF action
 */
function downloadVoucherPdf() {
    printVoucher();
}

/**
 * Zoom controls for the voucher preview
 */
function zoomVoucher(delta) {
    currentVoucherZoom = Math.max(0.6, Math.min(1.5, currentVoucherZoom + delta));
    billVoucherFrame.style.transform = 'scale(' + currentVoucherZoom + ')';
}

function resetVoucherZoom() {
    currentVoucherZoom = 1.0;
    billVoucherFrame.style.transform = 'scale(1)';
}

/**
 * Adjust iframe height once loaded
 */
function adjustFrameHeight(iframe) {
    try {
        if (iframe.contentWindow && iframe.contentWindow.document && iframe.contentWindow.document.body) {
            var h = iframe.contentWindow.document.body.scrollHeight;
            if (h && h > 800) {
                iframe.style.height = (h + 30) + 'px';
            }
        }
    } catch(e) {}
}

function escapeHtml(text) {
    if (!text) return '';
    return text.toString()
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}
</script>
