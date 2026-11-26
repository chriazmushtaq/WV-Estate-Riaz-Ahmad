<?php
$dealCount = count((array)$deals);
$letterCount = count((array)$letters);
$vaultCount = count((array)$vaultDocuments);
$totalDocCount = $dealCount + $letterCount + $vaultCount;
?>
<style type="text/css">
.wv-mydocs-page {
    background: #f8fafc;
    margin: -10px -5px 0;
    padding: 16px;
    font-family: inherit;
    touch-action: auto;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    width: 100%;
}
.wv-mydocs-page * {
    box-sizing: border-box;
}

/* Hero Header - Professional Light Theme */
.wv-mydocs-hero {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-top: 4px solid #0284c7;
    border-radius: 14px;
    box-shadow: 0 4px 18px rgba(15, 23, 42, 0.05);
    color: #0f172a;
    margin-bottom: 18px;
    padding: 22px 24px;
    position: relative;
}
.wv-mydocs-hero-inner {
    align-items: center;
    display: flex;
    flex-wrap: wrap;
    gap: 18px;
    justify-content: space-between;
    position: relative;
    z-index: 1;
}
.wv-mydocs-kicker {
    align-items: center;
    color: #0284c7;
    display: inline-flex;
    font-size: 11px;
    font-weight: 900;
    gap: 6px;
    letter-spacing: 0.5px;
    margin-bottom: 5px;
    text-transform: uppercase;
}
.wv-mydocs-hero h1 {
    color: #0f172a !important;
    font-size: 24px;
    font-weight: 900;
    line-height: 1.25;
    margin: 0 0 6px;
}
.wv-mydocs-hero p {
    color: #475569 !important;
    font-size: 13.5px;
    font-weight: 500;
    line-height: 1.5;
    margin: 0;
    max-width: 680px;
}
.wv-mydocs-hero-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}
.wv-mydocs-hero-actions .btn {
    align-items: center;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    color: #fff !important;
    display: inline-flex;
    font-size: 11px;
    font-weight: 800;
    gap: 7px;
    min-height: 38px;
    padding: 9px 15px;
    text-decoration: none !important;
    text-transform: uppercase;
    transition: all 0.2s ease;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
}
.wv-mydocs-hero-actions .btn-deals {
    background: #0f172a !important;
    border-color: #0f172a !important;
}
.wv-mydocs-hero-actions .btn-payments {
    background: #0284c7 !important;
    border-color: #0284c7 !important;
}
.wv-mydocs-hero-actions .btn:hover {
    filter: brightness(1.1);
    transform: translateY(-1px);
}

/* Stats KPI Cards */
.wv-mydocs-stats {
    display: grid;
    gap: 12px;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    margin-bottom: 18px;
}
.wv-mydocs-stat {
    align-items: center;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    box-shadow: 0 4px 14px rgba(15, 23, 42, 0.04);
    cursor: pointer;
    display: flex;
    gap: 14px;
    padding: 14px 18px;
    position: relative;
    overflow: hidden;
    transition: all 0.2s ease;
}
.wv-mydocs-stat:hover {
    border-color: #cbd5e1;
    box-shadow: 0 6px 18px rgba(15, 23, 42, 0.08);
    transform: translateY(-1px);
}
.wv-mydocs-stat.active {
    border-color: #ff0a46;
    box-shadow: 0 0 0 2px rgba(255, 10, 70, 0.15);
}
.wv-mydocs-stat-icon {
    align-items: center;
    border-radius: 10px;
    display: inline-flex;
    flex-shrink: 0;
    font-size: 20px;
    height: 44px;
    justify-content: center;
    width: 44px;
}
.stat-deals .wv-mydocs-stat-icon {
    background: #e0f2fe;
    color: #0284c7;
}
.stat-letters .wv-mydocs-stat-icon {
    background: #fef3c7;
    color: #d97706;
}
.stat-vault .wv-mydocs-stat-icon {
    background: #ecfdf5;
    color: #059669;
}
.wv-mydocs-stat-info {
    flex-grow: 1;
    min-width: 0;
}
.wv-mydocs-stat-info span {
    color: #64748b;
    display: block;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.3px;
    margin-bottom: 2px;
    text-transform: uppercase;
}
.wv-mydocs-stat-info strong {
    color: #0f172a;
    display: block;
    font-size: 22px;
    font-weight: 900;
    line-height: 1.15;
}
.wv-mydocs-stat-info small {
    color: #94a3b8;
    display: block;
    font-size: 11px;
    font-weight: 600;
    margin-top: 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Nav & Filter Toolbar */
.wv-docs-toolbar {
    align-items: center;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    box-shadow: 0 4px 14px rgba(15, 23, 42, 0.04);
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    justify-content: space-between;
    margin-bottom: 16px;
    padding: 10px 14px;
}
.wv-docs-tabs {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}
.wv-tab-btn {
    align-items: center;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    color: #475569;
    cursor: pointer;
    display: inline-flex;
    font-size: 12px;
    font-weight: 800;
    gap: 6px;
    min-height: 34px;
    padding: 6px 14px;
    transition: all 0.18s ease;
    user-select: none;
}
.wv-tab-btn:hover {
    background: #edf2f7;
    color: #0f172a;
}
.wv-tab-btn.active {
    background: #07152a;
    border-color: #07152a;
    color: #fff;
}
.wv-tab-btn .badge-pill {
    background: rgba(0, 0, 0, 0.08);
    border-radius: 999px;
    font-size: 10px;
    font-weight: 800;
    padding: 2px 7px;
}
.wv-tab-btn.active .badge-pill {
    background: rgba(255, 255, 255, 0.2);
    color: #fff;
}
.wv-docs-search-wrap {
    position: relative;
}
.wv-docs-search-wrap i {
    color: #94a3b8;
    font-size: 12px;
    left: 10px;
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
}
.wv-docs-search {
    background: #f8fafc;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    color: #1e293b;
    font-size: 12px;
    font-weight: 600;
    height: 34px;
    padding: 6px 10px 6px 30px;
    transition: all 0.18s ease;
    width: 220px;
}
.wv-docs-search:focus {
    background: #fff;
    border-color: #ff0a46;
    box-shadow: 0 0 0 3px rgba(255, 10, 70, 0.12);
    outline: 0;
}

/* Document Cards */
.wv-doc-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    box-shadow: 0 6px 18px rgba(15, 23, 42, 0.05);
    margin-bottom: 16px;
    overflow: hidden;
    transition: all 0.2s ease;
}
.wv-doc-card-header {
    align-items: center;
    background: #fff;
    border-bottom: 1px solid #edf2f7;
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    justify-content: space-between;
    padding: 14px 18px;
}
.wv-doc-card-title {
    align-items: center;
    color: #0f172a;
    display: flex;
    font-size: 15px;
    font-weight: 900;
    gap: 9px;
    margin: 0;
}
.wv-doc-card-title i {
    color: #ff0a46;
    font-size: 16px;
}
.wv-doc-card-badge {
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    color: #475569;
    font-size: 11px;
    font-weight: 800;
    padding: 3px 9px;
}

/* Professional Tables - Light Grid Lines */
.wv-doc-table-wrap {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    touch-action: pan-x pan-y;
    width: 100%;
}
.wv-doc-table {
    border-collapse: collapse;
    margin-bottom: 0;
    width: 100%;
    min-width: 720px;
    border: 1px solid #e2e8f0 !important;
}
.wv-doc-table thead th {
    background: #f8fafc;
    border: 1px solid #e2e8f0 !important;
    border-bottom: 2px solid #cbd5e1 !important;
    color: #475569;
    font-size: 11px;
    font-weight: 900;
    letter-spacing: 0.3px;
    padding: 11px 13px;
    text-transform: uppercase;
    white-space: nowrap;
}
.wv-doc-table tbody td {
    border: 1px solid #e9eef5 !important;
    color: #1e293b;
    font-size: 12px;
    font-weight: 600;
    padding: 11px 13px;
    vertical-align: middle;
}
.wv-doc-table tbody tr:hover {
    background: #f8fafc;
}

/* Column Specific Formats */
.wv-col-num {
    color: #64748b;
    font-weight: 700;
    text-align: center;
    width: 44px;
}
.wv-doc-name {
    color: #0f172a;
    font-size: 13px;
    font-weight: 800;
}
.wv-doc-sub {
    color: #64748b;
    font-size: 11px;
    font-weight: 600;
    margin-top: 2px;
}
.wv-doc-date {
    color: #334155;
    font-size: 12px;
    font-weight: 700;
    white-space: nowrap;
}
.wv-doc-amount {
    color: #059669;
    font-size: 13px;
    font-weight: 800;
    white-space: nowrap;
}

/* Plain Text Status Display */
.wv-status-text {
    font-size: 12px;
    font-weight: 700;
}
.wv-status-text.status-active {
    color: #059669;
}
.wv-status-text.status-blocked {
    color: #dc2626;
}
.wv-status-text.status-other {
    color: #475569;
}

/* Action Buttons - Clean, Professional & Touch Friendly */
.wv-btn-action {
    align-items: center;
    border-radius: 6px;
    display: inline-flex;
    font-size: 11px;
    font-weight: 800;
    gap: 6px;
    justify-content: center;
    min-height: 32px;
    padding: 6px 11px;
    text-decoration: none !important;
    transition: all 0.15s ease-in-out;
    white-space: nowrap;
}
.wv-btn-print {
    background: #f8fafc;
    border: 1px solid #cbd5e1;
    color: #0f172a !important;
}
.wv-btn-print:hover {
    background: #0f172a;
    border-color: #0f172a;
    color: #fff !important;
}
.wv-btn-pdf {
    background: #fff1f2;
    border: 1px solid #fecdd3;
    color: #e11d48 !important;
}
.wv-btn-pdf:hover {
    background: #e11d48;
    border-color: #e11d48;
    color: #fff !important;
}
.wv-btn-view {
    background: #f0f9ff;
    border: 1px solid #bae6fd;
    color: #0284c7 !important;
}
.wv-btn-view:hover {
    background: #0284c7;
    border-color: #0284c7;
    color: #fff !important;
}
.wv-btn-download {
    background: #ecfdf5;
    border: 1px solid #a7f3d0;
    color: #059669 !important;
}
.wv-btn-download:hover {
    background: #059669;
    border-color: #059669;
    color: #fff !important;
}

/* Empty States */
.wv-doc-empty {
    color: #64748b;
    padding: 36px 20px;
    text-align: center;
}
.wv-doc-empty i {
    color: #cbd5e1;
    display: block;
    font-size: 38px;
    margin-bottom: 10px;
}
.wv-doc-empty h4 {
    color: #334155;
    font-size: 14px;
    font-weight: 800;
    margin: 0 0 4px;
}
.wv-doc-empty p {
    font-size: 12px;
    margin: 0;
}

/* Responsive Breakpoints for Complete Mobile Friendliness */
@media (max-width: 991px) {
    .wv-mydocs-stats {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
    .wv-docs-toolbar {
        flex-direction: column;
        align-items: stretch;
    }
    .wv-docs-tabs {
        width: 100%;
        overflow-x: auto;
        padding-bottom: 4px;
    }
    .wv-docs-search-wrap {
        width: 100%;
    }
    .wv-docs-search {
        width: 100%;
    }
}

@media (max-width: 767px) {
    .wv-mydocs-page {
        margin: 0;
        padding: 10px;
    }
    .wv-mydocs-hero {
        padding: 16px;
    }
    .wv-mydocs-hero-inner {
        flex-direction: column;
        align-items: flex-start;
    }
    .wv-mydocs-hero-actions {
        width: 100%;
        margin-top: 10px;
    }
    .wv-mydocs-hero-actions .btn {
        flex: 1 1 auto;
        justify-content: center;
    }
    .wv-mydocs-stats {
        grid-template-columns: 1fr;
        gap: 10px;
    }
    .wv-doc-card-header {
        padding: 12px 14px;
    }
    .wv-doc-table thead th,
    .wv-doc-table tbody td {
        padding: 9px 10px;
    }
    .wv-doc-table {
        min-width: 620px;
    }
    .wv-btn-action {
        min-height: 36px;
        padding: 6px 10px;
    }
}
</style>

<div class="wv-mydocs-page">
    <?php echo $this->Session->flash(); ?>

    <!-- Hero Header -->
    <div class="wv-mydocs-hero">
        <div class="wv-mydocs-hero-inner">
            <div>
                <div class="wv-mydocs-kicker"><i class="fa fa-folder-open"></i> <?php echo __('Client Document Center'); ?></div>
                <h1><?php echo __('My Documents & Records'); ?></h1>
                <p><?php echo __('Access, print and download verified property booking invoices, financial ledger statements, official society letters, and secure customer vault files.'); ?></p>
            </div>
            <div class="wv-mydocs-hero-actions">
                <?php echo $this->Html->link('<i class="fa fa-handshake-o"></i> '.__('View My Deals'), array('controller' => 'MyDeals', 'action' => 'index'), array('escape' => false, 'class' => 'btn btn-deals')); ?>
                <?php echo $this->Html->link('<i class="fa fa-credit-card"></i> '.__('Payment Receipts'), array('controller' => 'MyPayments', 'action' => 'index'), array('escape' => false, 'class' => 'btn btn-payments')); ?>
            </div>
        </div>
    </div>

    <!-- Stats KPI Cards (No Receipts) -->
    <div class="wv-mydocs-stats">
        <div class="wv-mydocs-stat stat-deals" onclick="wvFilterDocs('deals')">
            <div class="wv-mydocs-stat-icon">
                <i class="fa fa-building-o"></i>
            </div>
            <div class="wv-mydocs-stat-info">
                <span><?php echo __('Deal Statements'); ?></span>
                <strong><?php echo (int)$dealCount; ?></strong>
                <small><?php echo __('Invoices & Ledger Statements'); ?></small>
            </div>
        </div>

        <div class="wv-mydocs-stat stat-letters" onclick="wvFilterDocs('letters')">
            <div class="wv-mydocs-stat-icon">
                <i class="fa fa-envelope-open-o"></i>
            </div>
            <div class="wv-mydocs-stat-info">
                <span><?php echo __('Official Letters'); ?></span>
                <strong><?php echo (int)$letterCount; ?></strong>
                <small><?php echo __('Issued Society Notices'); ?></small>
            </div>
        </div>

        <div class="wv-mydocs-stat stat-vault" onclick="wvFilterDocs('vault')">
            <div class="wv-mydocs-stat-icon">
                <i class="fa fa-shield"></i>
            </div>
            <div class="wv-mydocs-stat-info">
                <span><?php echo __('Vault Documents'); ?></span>
                <strong><?php echo (int)$vaultCount; ?></strong>
                <small><?php echo __('Secure Records & Files'); ?></small>
            </div>
        </div>
    </div>

    <!-- Quick Navigation Toolbar -->
    <div class="wv-docs-toolbar">
        <div class="wv-docs-tabs">
            <button type="button" class="wv-tab-btn active" id="tab-all" onclick="wvFilterDocs('all')">
                <i class="fa fa-th-list"></i> <?php echo __('All Documents'); ?> <span class="badge-pill"><?php echo (int)$totalDocCount; ?></span>
            </button>
            <button type="button" class="wv-tab-btn" id="tab-deals" onclick="wvFilterDocs('deals')">
                <i class="fa fa-building-o"></i> <?php echo __('Deal Statements'); ?> <span class="badge-pill"><?php echo (int)$dealCount; ?></span>
            </button>
            <button type="button" class="wv-tab-btn" id="tab-letters" onclick="wvFilterDocs('letters')">
                <i class="fa fa-envelope-open-o"></i> <?php echo __('Official Letters'); ?> <span class="badge-pill"><?php echo (int)$letterCount; ?></span>
            </button>
            <button type="button" class="wv-tab-btn" id="tab-vault" onclick="wvFilterDocs('vault')">
                <i class="fa fa-shield"></i> <?php echo __('Vault Documents'); ?> <span class="badge-pill"><?php echo (int)$vaultCount; ?></span>
            </button>
        </div>

        <div class="wv-docs-search-wrap">
            <i class="fa fa-search"></i>
            <input type="text" id="wvDocSearch" class="wv-docs-search" placeholder="<?php echo __('Search documents...'); ?>" onkeyup="wvLiveSearch(this.value)">
        </div>
    </div>

    <!-- Section 1: Deal Statements & Invoices -->
    <div class="wv-doc-card wv-doc-section" id="section-deals">
        <div class="wv-doc-card-header">
            <h3 class="wv-doc-card-title">
                <i class="fa fa-file-text-o"></i> <?php echo __('Deal Statements & Invoices'); ?>
            </h3>
            <span class="wv-doc-card-badge"><?php echo sprintf(__('%d Total Deals'), (int)$dealCount); ?></span>
        </div>

        <?php if(!empty($deals)): ?>
            <div class="wv-doc-table-wrap">
                <table class="table wv-doc-table">
                    <thead>
                        <tr>
                            <th class="wv-col-num">#</th>
                            <th><?php echo __('Deal / Invoice'); ?></th>
                            <th><?php echo __('Property'); ?></th>
                            <th><?php echo __('Booking Date'); ?></th>
                            <th><?php echo __('Total Price'); ?></th>
                            <th><?php echo __('Status'); ?></th>
                            <th class="text-center" style="width:110px;"><?php echo __('Invoice'); ?></th>
                            <th class="text-center" style="width:130px;"><?php echo __('Ledger PDF'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $i = 1;
                        foreach($deals as $row): 
                            $deal = $row['MyDeal'];
                            $property = !empty($row['Property']) ? $row['Property'] : array();
                            $dealId = (int)$deal['id'];
                            $invoiceNo = !empty($deal['invoice_no']) ? $deal['invoice_no'] : 'Deal #'.$dealId;
                            $propName = !empty($property['name']) ? $property['name'] : '-';
                            $bookingDate = !empty($deal['date']) ? date('d-M-Y', strtotime($deal['date'])) : '-';
                            $totalAmount = isset($deal['total_amount']) ? (float)$deal['total_amount'] : (isset($deal['total_price']) ? (float)$deal['total_price'] : 0);
                            $dealStatus = !empty($deal['deal_status']) ? $deal['deal_status'] : 'Active';
                            $statusClass = strtolower($dealStatus) === 'active' ? 'status-active' : (strtolower($dealStatus) === 'blocked' ? 'status-blocked' : 'status-other');
                        ?>
                            <tr class="wv-doc-row" data-search="<?php echo h(strtolower($invoiceNo.' '.$propName.' '.$dealStatus.' '.$bookingDate)); ?>">
                                <td class="wv-col-num"><?php echo $i++; ?></td>
                                <td>
                                    <div class="wv-doc-name"><?php echo h($invoiceNo); ?></div>
                                    <div class="wv-doc-sub">Deal ID: #<?php echo $dealId; ?></div>
                                </td>
                                <td>
                                    <div class="wv-doc-name"><?php echo h($propName); ?></div>
                                    <?php if(!empty($property['floor'])): ?>
                                        <div class="wv-doc-sub">Floor: <?php echo h($property['floor']); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="wv-doc-date"><?php echo h($bookingDate); ?></td>
                                <td class="wv-doc-amount"><?php echo $currency . $this->Number->format($totalAmount); ?></td>
                                <td>
                                    <span class="wv-status-text <?php echo $statusClass; ?>">
                                        <?php echo h($dealStatus); ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <?php echo $this->Html->link(
                                        '<i class="fa fa-print"></i> ' . __('Invoice'),
                                        array('controller' => 'MyDeals', 'action' => 'printinvoice', $dealId),
                                        array('escape' => false, 'target' => '_blank', 'class' => 'wv-btn-action wv-btn-print', 'title' => __('Print Booking Invoice'))
                                    ); ?>
                                </td>
                                <td class="text-center">
                                    <?php echo $this->Html->link(
                                        '<i class="fa fa-file-pdf-o"></i> ' . __('Ledger PDF'),
                                        array('controller' => 'MyDeals', 'action' => 'ledgerpdf', $dealId),
                                        array('escape' => false, 'target' => '_blank', 'class' => 'wv-btn-action wv-btn-pdf', 'title' => __('Download Official Ledger Statement'))
                                    ); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="wv-doc-empty">
                <i class="fa fa-folder-open-o"></i>
                <h4><?php echo __('No Deal Documents Available'); ?></h4>
                <p><?php echo __('You currently have no active property deals registered in the portal.'); ?></p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Section 2: Official Issued Letters -->
    <div class="wv-doc-card wv-doc-section" id="section-letters">
        <div class="wv-doc-card-header">
            <h3 class="wv-doc-card-title">
                <i class="fa fa-envelope-o"></i> <?php echo __('Official Issued Letters'); ?>
            </h3>
            <span class="wv-doc-card-badge"><?php echo sprintf(__('%d Total Letters'), (int)$letterCount); ?></span>
        </div>

        <?php if(!empty($letters)): ?>
            <div class="wv-doc-table-wrap">
                <table class="table wv-doc-table">
                    <thead>
                        <tr>
                            <th class="wv-col-num">#</th>
                            <th><?php echo __('Reference No.'); ?></th>
                            <th><?php echo __('Property / Deal'); ?></th>
                            <th><?php echo __('Letter Title / Subject'); ?></th>
                            <th><?php echo __('Issued Date'); ?></th>
                            <th class="text-center" style="width:130px;"><?php echo __('Action'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $j = 1;
                        foreach($letters as $row):
                            $letter = $row['Realestateletter'];
                            $deal = !empty($row['Deal']) ? $row['Deal'] : array();
                            $propName = !empty($row['Property']['name']) ? $row['Property']['name'] : '-';
                            $refNo = !empty($letter['reference_no']) ? $letter['reference_no'] : 'LTR-'.$letter['id'];
                            $title = !empty($letter['title']) ? $letter['title'] : (!empty($letter['letter_type']) ? $letter['letter_type'] : 'Official Letter');
                            $letterDate = !empty($letter['letter_date']) ? date('d-M-Y', strtotime($letter['letter_date'])) : (!empty($letter['created']) ? date('d-M-Y', strtotime($letter['created'])) : '-');
                            $dealInvoice = !empty($deal['invoice_no']) ? $deal['invoice_no'] : (!empty($letter['deal_id']) ? 'Deal #'.$letter['deal_id'] : '-');
                        ?>
                            <tr class="wv-doc-row" data-search="<?php echo h(strtolower($refNo.' '.$propName.' '.$title.' '.$dealInvoice.' '.$letterDate)); ?>">
                                <td class="wv-col-num"><?php echo $j++; ?></td>
                                <td>
                                    <div class="wv-doc-name"><?php echo h($refNo); ?></div>
                                    <div class="wv-doc-sub">Letter ID: #<?php echo (int)$letter['id']; ?></div>
                                </td>
                                <td>
                                    <div class="wv-doc-name"><?php echo h($propName); ?></div>
                                    <div class="wv-doc-sub"><?php echo h($dealInvoice); ?></div>
                                </td>
                                <td>
                                    <div class="wv-doc-name"><?php echo h($title); ?></div>
                                    <?php if(!empty($letter['letter_type']) && $letter['letter_type'] !== $title): ?>
                                        <div class="wv-doc-sub"><?php echo h($letter['letter_type']); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="wv-doc-date"><?php echo h($letterDate); ?></td>
                                <td class="text-center">
                                    <?php echo $this->Html->link(
                                        '<i class="fa fa-print"></i> ' . __('View / Print'),
                                        array('controller' => 'MyDeals', 'action' => 'printletter', (int)$letter['deal_id'], (int)$letter['id']),
                                        array('escape' => false, 'target' => '_blank', 'class' => 'wv-btn-action wv-btn-print', 'title' => __('Print Official Letter'))
                                    ); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="wv-doc-empty">
                <i class="fa fa-envelope-open-o"></i>
                <h4><?php echo __('No Issued Letters Found'); ?></h4>
                <p><?php echo __('Official society notices, allotment letters, and booking confirmations will appear here when published.'); ?></p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Section 3: Secure Vault Documents -->
    <div class="wv-doc-card wv-doc-section" id="section-vault">
        <div class="wv-doc-card-header">
            <h3 class="wv-doc-card-title">
                <i class="fa fa-shield"></i> <?php echo __('Secure Vault Documents'); ?>
            </h3>
            <span class="wv-doc-card-badge"><?php echo sprintf(__('%d Total Files'), (int)$vaultCount); ?></span>
        </div>

        <?php if(!empty($vaultDocuments)): ?>
            <div class="wv-doc-table-wrap">
                <table class="table wv-doc-table">
                    <thead>
                        <tr>
                            <th class="wv-col-num">#</th>
                            <th><?php echo __('Document Type'); ?></th>
                            <th><?php echo __('Property / Deal'); ?></th>
                            <th><?php echo __('Date'); ?></th>
                            <th><?php echo __('File Name'); ?></th>
                            <th class="text-center" style="width:160px;"><?php echo __('Actions'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $k = 1;
                        foreach($vaultDocuments as $row):
                            $doc = $row['DealDocument'];
                            $deal = !empty($row['Deal']) ? $row['Deal'] : array();
                            $propName = !empty($row['Property']['name']) ? $row['Property']['name'] : '-';
                            $docType = !empty($doc['document_type']) ? $doc['document_type'] : 'Document';
                            $dealInvoice = !empty($deal['invoice_no']) ? $deal['invoice_no'] : (!empty($doc['deal_id']) ? 'Deal #'.$doc['deal_id'] : '-');
                            $docDate = !empty($doc['document_date']) ? date('d-M-Y', strtotime($doc['document_date'])) : (!empty($doc['created']) ? date('d-M-Y', strtotime($doc['created'])) : '-');
                            $fileName = !empty($doc['photo']) ? basename($doc['photo']) : 'file';
                        ?>
                            <tr class="wv-doc-row" data-search="<?php echo h(strtolower($docType.' '.$propName.' '.$dealInvoice.' '.$fileName.' '.$docDate)); ?>">
                                <td class="wv-col-num"><?php echo $k++; ?></td>
                                <td>
                                    <div class="wv-doc-name"><?php echo h($docType); ?></div>
                                    <div class="wv-doc-sub">Record #<?php echo (int)$doc['id']; ?></div>
                                </td>
                                <td>
                                    <div class="wv-doc-name"><?php echo h($propName); ?></div>
                                    <div class="wv-doc-sub"><?php echo h($dealInvoice); ?></div>
                                </td>
                                <td class="wv-doc-date"><?php echo h($docDate); ?></td>
                                <td>
                                    <div class="wv-doc-name" style="font-weight:600;font-size:11px;word-break:break-all;">
                                        <i class="fa fa-paperclip"></i> <?php echo h($fileName); ?>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div style="display:inline-flex;gap:4px;">
                                        <?php echo $this->Html->link(
                                            '<i class="fa fa-eye"></i> ' . __('View'),
                                            array('controller' => 'MyDocuments', 'action' => 'viewfile', (int)$doc['id']),
                                            array('escape' => false, 'target' => '_blank', 'class' => 'wv-btn-action wv-btn-view', 'title' => __('View Document'))
                                        ); ?>
                                        <?php echo $this->Html->link(
                                            '<i class="fa fa-download"></i> ' . __('Download'),
                                            array('controller' => 'MyDocuments', 'action' => 'download', (int)$doc['id']),
                                            array('escape' => false, 'class' => 'wv-btn-action wv-btn-download', 'title' => __('Download Document'))
                                        ); ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="wv-doc-empty">
                <i class="fa fa-shield"></i>
                <h4><?php echo __('No Vault Documents Found'); ?></h4>
                <p><?php echo __('Customer identification, scanned property agreements, and verified files will be securely displayed here once archived.'); ?></p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script type="text/javascript">
function wvFilterDocs(sectionKey) {
    var tabs = document.querySelectorAll('.wv-tab-btn');
    tabs.forEach(function(btn) { btn.classList.remove('active'); });

    var stats = document.querySelectorAll('.wv-mydocs-stat');
    stats.forEach(function(st) { st.classList.remove('active'); });

    var activeTab = document.getElementById('tab-' + sectionKey);
    if(activeTab) { activeTab.classList.add('active'); }

    var statSelector = document.querySelector('.stat-' + sectionKey);
    if(statSelector) { statSelector.classList.add('active'); }

    var secDeals = document.getElementById('section-deals');
    var secLetters = document.getElementById('section-letters');
    var secVault = document.getElementById('section-vault');

    if(sectionKey === 'all') {
        if(secDeals) secDeals.style.display = '';
        if(secLetters) secLetters.style.display = '';
        if(secVault) secVault.style.display = '';
    } else if(sectionKey === 'deals') {
        if(secDeals) secDeals.style.display = '';
        if(secLetters) secLetters.style.display = 'none';
        if(secVault) secVault.style.display = 'none';
    } else if(sectionKey === 'letters') {
        if(secDeals) secDeals.style.display = 'none';
        if(secLetters) secLetters.style.display = '';
        if(secVault) secVault.style.display = 'none';
    } else if(sectionKey === 'vault') {
        if(secDeals) secDeals.style.display = 'none';
        if(secLetters) secLetters.style.display = 'none';
        if(secVault) secVault.style.display = '';
    }
}

function wvLiveSearch(query) {
    query = (query || '').toLowerCase().trim();
    var rows = document.querySelectorAll('.wv-doc-row');
    rows.forEach(function(row) {
        var searchData = row.getAttribute('data-search') || '';
        if(!query || searchData.indexOf(query) !== -1) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}
</script>
