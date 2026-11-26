<?php
$dashboardClient = isset($portalClient) && is_array($portalClient) ? $portalClient : array();
if(empty($dashboardClient))
{
    $frontDashboardUser = (array)$this->Session->read('frontUser');
    $dashboardClient = !empty($frontDashboardUser['Client']) && is_array($frontDashboardUser['Client']) ? $frontDashboardUser['Client'] : array();
}
$clientName = !empty($dashboardClient['name']) ? $dashboardClient['name'] : __('Client');
$clientEmail = !empty($dashboardClient['email']) ? $dashboardClient['email'] : '';
$clientMobile = !empty($dashboardClient['mobile']) ? $dashboardClient['mobile'] : (!empty($dashboardClient['phone']) ? $dashboardClient['phone'] : '');
$photoUrl = $this->Html->url(array('controller'=>'img'));
$clientPhoto = !empty($frontClientPhotoUrl) ? $frontClientPhotoUrl : $photoUrl.'/user-icon.png';
if(empty($frontClientPhotoUrl) && !empty($dashboardClient['photo']))
{
    $clientPhotoDir = !empty($dashboardClient['dir']) ? $dashboardClient['dir'] : (!empty($dashboardClient['dirt']) ? $dashboardClient['dirt'] : 'client');
    $clientPhoto = $photoUrl.'/'.trim($clientPhotoDir).'_thumb/'.$dashboardClient['photo'];
}
$paidAmount = isset($dashboardSummary['paid_amount']) ? (float)$dashboardSummary['paid_amount'] : 0;
$dueAmount = isset($dashboardSummary['due_amount']) ? (float)$dashboardSummary['due_amount'] : 0;
$overdueAmount = isset($dashboardSummary['overdue_amount']) ? (float)$dashboardSummary['overdue_amount'] : 0;
$totalTracked = $paidAmount + $dueAmount;
$paidPercent = $totalTracked > 0 ? round(($paidAmount / $totalTracked) * 100) : 0;
if($paidPercent > 100){ $paidPercent = 100; }
$dealsPaymentSections = isset($dealsPaymentSections) && is_array($dealsPaymentSections) ? $dealsPaymentSections : array();
$runningRefundNotice = isset($runningRefundNotice) && is_array($runningRefundNotice) ? $runningRefundNotice : array();
$rentalDashboardSummary = isset($rentalDashboardSummary) && is_array($rentalDashboardSummary) ? $rentalDashboardSummary : array();
$useAdminDealLedger = $this->Session->check('User');
?>
<style type="text/css">
/* Light Theme Base */
body {
    background: #f8fafc;
}
.wv-client-dashboard {
    background: #f8fafc;
    margin: -10px -5px 0;
    padding: 16px;
    touch-action: auto;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    width: 100%;
}
.wv-client-dashboard * {
    box-sizing: border-box;
}

/* Hero Action Buttons Card - Professional Light Theme */
.wv-client-hero {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-top: 4px solid #0284c7;
    border-radius: 14px;
    box-shadow: 0 4px 18px rgba(15, 23, 42, 0.05);
    margin-bottom: 18px;
    padding: 16px 20px;
    position: relative;
}
.wv-client-actions {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 12px;
    width: 100%;
}
.wv-client-actions .btn {
    align-items: center;
    border-radius: 999px;
    display: inline-flex;
    font-size: 12px;
    font-weight: 800;
    gap: 8px;
    justify-content: center;
    min-height: 44px;
    padding: 10px 16px;
    text-align: center;
    text-decoration: none !important;
    text-transform: uppercase;
    transition: all 0.2s ease;
    width: 100%;
}
.wv-client-actions .btn-deals {
    background: #0f172a !important;
    border: 1px solid #0f172a !important;
    color: #ffffff !important;
    box-shadow: 0 2px 6px rgba(15, 23, 42, 0.15);
}
.wv-client-actions .btn-deals:hover {
    background: #1e293b !important;
    border-color: #1e293b !important;
    transform: translateY(-1px);
}
.wv-client-actions .btn-payments {
    background: #0284c7 !important;
    border: 1px solid #0284c7 !important;
    color: #ffffff !important;
    box-shadow: 0 2px 6px rgba(2, 132, 199, 0.2);
}
.wv-client-actions .btn-payments:hover {
    background: #0369a1 !important;
    border-color: #0369a1 !important;
    transform: translateY(-1px);
}
.wv-client-actions .btn-docs {
    background: #ffffff !important;
    border: 1px solid #cbd5e1 !important;
    color: #1e293b !important;
}
.wv-client-actions .btn-docs:hover {
    background: #f1f5f9 !important;
    border-color: #94a3b8 !important;
    transform: translateY(-1px);
}
.wv-client-actions .btn-props {
    background: #ffffff !important;
    border: 1px solid #e2e8f0 !important;
    color: #475569 !important;
}
.wv-client-actions .btn-props:hover {
    background: #f8fafc !important;
    color: #0f172a !important;
    transform: translateY(-1px);
}

/* Impersonation & Refund Alerts */
.wv-client-impersonation {
    align-items: center;
    background: #fffbeb;
    border: 1px solid #fde68a;
    border-radius: 10px;
    color: #92400e;
    display: flex;
    font-size: 12px;
    font-weight: 700;
    gap: 8px;
    margin-bottom: 16px;
    padding: 12px 16px;
}
.wv-client-refund-alert {
    align-items: center;
    background: #ecfdf5;
    border: 1px solid #a7f3d0;
    border-radius: 12px;
    color: #065f46;
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    justify-content: space-between;
    margin-bottom: 16px;
    padding: 14px 18px;
}
.wv-client-refund-alert strong {
    display: block;
    font-size: 13px;
    font-weight: 800;
}
.wv-client-refund-alert span {
    display: block;
    font-size: 12px;
    font-weight: 600;
    margin-top: 3px;
}
.wv-client-refund-alert .btn {
    border-radius: 8px;
    font-size: 11px;
    font-weight: 800;
    min-height: 34px;
    padding: 7px 14px;
    text-transform: uppercase;
}

/* KPI Summary Cards */
.wv-client-stats {
    display: grid;
    gap: 14px;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    margin-bottom: 18px;
}
.wv-client-stat {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
    min-height: 115px;
    padding: 16px 18px;
    position: relative;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.wv-client-stat:hover {
    box-shadow: 0 6px 18px rgba(15, 23, 42, 0.08);
    transform: translateY(-1px);
}
.wv-client-stat-top {
    align-items: center;
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
}
.wv-client-stat-label {
    color: #64748b;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.4px;
    text-transform: uppercase;
}
.wv-client-stat-icon {
    align-items: center;
    border-radius: 10px;
    display: flex;
    font-size: 17px;
    height: 38px;
    justify-content: center;
    width: 38px;
}
.stat-deals .wv-client-stat-icon {
    background: #eff6ff;
    color: #2563eb;
}
.stat-paid .wv-client-stat-icon {
    background: #ecfdf5;
    color: #059669;
}
.stat-due .wv-client-stat-icon {
    background: #fff7ed;
    color: #d97706;
}
.stat-overdue .wv-client-stat-icon {
    background: #fef2f2;
    color: #dc2626;
}
.wv-client-stat-value {
    color: #0f172a;
    font-size: 22px;
    font-weight: 900;
    line-height: 1.15;
    word-break: break-word;
}
.stat-paid .wv-client-stat-value {
    color: #059669;
}
.stat-due .wv-client-stat-value {
    color: #d97706;
}
.stat-overdue .wv-client-stat-value {
    color: #dc2626;
}
.wv-client-stat-meta {
    color: #64748b;
    font-size: 11px;
    font-weight: 600;
    margin-top: 6px;
}

/* My Rentals Section (Light Theme) */
.wv-rental-dashboard {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
    margin-bottom: 18px;
    padding: 16px 18px;
}
.wv-rental-dashboard-head {
    align-items: center;
    display: flex;
    justify-content: space-between;
    margin-bottom: 14px;
}
.wv-rental-dashboard-title {
    align-items: center;
    color: #0f172a;
    display: flex;
    font-size: 15px;
    font-weight: 800;
    gap: 8px;
    margin: 0;
}
.wv-rental-dashboard-title i {
    color: #0284c7;
}
.wv-rental-dashboard-link {
    align-items: center;
    color: #0284c7 !important;
    display: inline-flex;
    font-size: 12px;
    font-weight: 800;
    gap: 4px;
    text-decoration: none !important;
}
.wv-rental-stats {
    display: grid;
    gap: 10px;
    grid-template-columns: repeat(6, minmax(0, 1fr));
}
.wv-rental-stat {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    color: #0f172a !important;
    display: block;
    min-height: 105px;
    padding: 12px;
    position: relative;
    text-decoration: none !important;
    transition: all 0.18s ease;
}
.wv-rental-stat:hover {
    background: #ffffff;
    border-color: #cbd5e1;
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06);
    transform: translateY(-1px);
}
.wv-rental-stat-icon {
    align-items: center;
    background: #e0f2fe;
    border-radius: 6px;
    color: #0284c7;
    display: flex;
    font-size: 14px;
    height: 28px;
    justify-content: center;
    margin-bottom: 8px;
    width: 28px;
}
.wv-rental-stat.paid .wv-rental-stat-icon,
.wv-rental-stat.net .wv-rental-stat-icon {
    background: #ecfdf5;
    color: #059669;
}
.wv-rental-stat.upcoming .wv-rental-stat-icon {
    background: #fff7ed;
    color: #d97706;
}
.wv-rental-stat.overdue .wv-rental-stat-icon {
    background: #fef2f2;
    color: #dc2626;
}
.wv-rental-stat.charges .wv-rental-stat-icon {
    background: #f3e8ff;
    color: #7c3aed;
}
.wv-rental-stat-label {
    color: #64748b;
    font-size: 10px;
    font-weight: 800;
    margin-bottom: 4px;
    text-transform: uppercase;
}
.wv-rental-stat-value {
    color: #0f172a;
    font-size: 15px;
    font-weight: 800;
    line-height: 1.25;
}
.wv-rental-stat.period .wv-rental-stat-value {
    font-size: 11px;
    line-height: 1.4;
}
.wv-rental-stat-meta {
    color: #64748b;
    font-size: 10px;
    font-weight: 600;
    margin-top: 4px;
}

/* Main Dashboard Grid */
.wv-client-grid {
    display: grid;
    gap: 18px;
    grid-template-columns: minmax(0, 1fr) 310px;
}
.wv-client-panel {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    box-shadow: 0 4px 14px rgba(15, 23, 42, 0.04);
    overflow: hidden;
}
.wv-client-panel-head {
    align-items: center;
    background: #ffffff;
    border-bottom: 1px solid #edf2f7;
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    justify-content: space-between;
    padding: 16px 20px;
}
.wv-client-panel-title {
    color: #0f172a;
    font-size: 16px;
    font-weight: 800;
    margin: 0;
}
.wv-client-panel-sub {
    color: #64748b;
    font-size: 12px;
    font-weight: 600;
    margin-top: 2px;
}
.wv-client-panel-head .btn {
    border-radius: 8px;
    font-size: 11px;
    font-weight: 800;
    min-height: 34px;
    padding: 7px 14px;
}

/* Payment Sections & Deal Card */
.wv-payment-sections {
    display: grid;
    gap: 16px;
    padding: 16px;
}
.wv-payment-deal-section {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    overflow: hidden;
}
.wv-payment-deal-head {
    align-items: center;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    border-left: 4px solid #0284c7;
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    justify-content: space-between;
    padding: 12px 16px;
}
.wv-payment-deal-title {
    color: #0f172a !important;
    font-size: 14px;
    font-weight: 800;
    line-height: 1.25;
    margin: 0;
}
.wv-payment-deal-meta {
    color: #64748b !important;
    font-size: 12px;
    font-weight: 600;
    margin-top: 2px;
}
.wv-payment-deal-stats {
    align-items: center;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}
.wv-payment-deal-pill {
    align-items: center;
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    color: #334155 !important;
    display: inline-flex;
    font-size: 11px;
    font-weight: 700;
    gap: 5px;
    padding: 5px 9px;
    white-space: nowrap;
}
.wv-payment-deal-pill.received {
    background: #ecfdf5;
    border-color: #a7f3d0;
    color: #059669 !important;
}
.wv-payment-ledger-link {
    align-items: center;
    background: #0f172a !important;
    border: 0 !important;
    border-radius: 6px !important;
    color: #ffffff !important;
    display: inline-flex;
    font-size: 11px !important;
    font-weight: 800 !important;
    gap: 5px;
    min-height: 28px;
    padding: 5px 11px !important;
    text-decoration: none !important;
    transition: all 0.15s ease;
}
.wv-payment-ledger-link:hover {
    background: #1e293b !important;
}

/* Touch-Scrollable Table Container (Up/Down/Left/Right Friendly) */
.wv-swipe-hint {
    align-items: center;
    background: #f1f5f9;
    border-bottom: 1px solid #e2e8f0;
    color: #64748b;
    display: none;
    font-size: 11px;
    font-weight: 700;
    gap: 6px;
    padding: 7px 14px;
}
.wv-swipe-hint i {
    color: #0284c7;
}
.wv-table-scroll-container {
    border-top: 1px solid #e2e8f0;
    overflow-x: auto;
    overflow-y: hidden;
    overscroll-behavior-x: contain;
    position: relative;
    touch-action: pan-x pan-y;
    width: 100%;
    -webkit-overflow-scrolling: touch;
}
.wv-client-table {
    border-collapse: collapse;
    margin: 0 !important;
    min-width: 860px;
    width: 100%;
}
.wv-client-table thead tr {
    background: #f8fafc;
}
.wv-client-table th {
    background: #f8fafc !important;
    border: 1px solid #e2e8f0 !important;
    border-bottom: 2px solid #cbd5e1 !important;
    color: #475569 !important;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.35px;
    padding: 11px 13px !important;
    text-transform: uppercase;
    white-space: nowrap;
}
.wv-client-table td {
    border: 1px solid #e9eef5 !important;
    color: #1e293b;
    font-size: 12px;
    font-weight: 600;
    padding: 11px 13px !important;
    vertical-align: middle !important;
}
.wv-client-table tbody tr:hover td {
    background: #f8fafc;
}

/* Table Cell Formatting - Plain Text & Clean Badges */
.wv-receipt-code {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 4px;
    color: #0f172a;
    display: inline-block;
    font-family: monospace;
    font-size: 12px;
    font-weight: 700;
    padding: 3px 8px;
    white-space: nowrap;
}
.wv-payment-amount {
    color: #059669;
    font-size: 13px;
    font-weight: 800;
    white-space: nowrap;
}
.wv-fee-amount {
    font-size: 12px;
    font-weight: 700;
    white-space: nowrap;
}
.wv-fee-amount.has-late {
    color: #dc2626;
}
.wv-empty-payments {
    color: #64748b;
    font-size: 13px;
    font-weight: 700;
    padding: 32px 20px !important;
    text-align: center;
}

/* Sidebar Cards */
.wv-side-card {
    padding: 18px 20px;
}
.wv-side-title {
    align-items: center;
    color: #0f172a;
    display: flex;
    font-size: 15px;
    font-weight: 800;
    gap: 8px;
    margin: 0 0 14px;
}
.wv-side-title i {
    color: #0284c7;
}

/* Payment Progress */
.wv-progress-shell {
    align-items: center;
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    border-radius: 999px;
    display: flex;
    height: 18px;
    margin-bottom: 10px;
    overflow: hidden;
    position: relative;
}
.wv-progress-bar {
    background: linear-gradient(90deg, #10b981, #059669);
    border-radius: 999px;
    display: block;
    height: 100%;
    min-width: 34px;
    position: relative;
    transition: width 0.4s ease;
}
.wv-progress-bar.is-zero {
    background: transparent;
    min-width: 34px;
}
.wv-progress-label {
    color: #ffffff;
    display: block;
    font-size: 10px;
    font-weight: 800;
    line-height: 18px;
    position: absolute;
    right: 7px;
    text-align: right;
    top: 0;
    white-space: nowrap;
}
.wv-progress-bar.is-zero .wv-progress-label {
    color: #64748b;
    position: static;
    text-align: center;
    width: 100%;
}
.wv-progress-meta {
    align-items: center;
    color: #64748b;
    display: flex;
    font-size: 12px;
    font-weight: 700;
    justify-content: space-between;
}
.wv-progress-meta strong {
    color: #059669;
}

/* Overdue Warning Callout */
.wv-overdue-note {
    background: #fef2f2;
    border: 1px solid #fecdd3;
    border-radius: 10px;
    color: #991b1b;
    font-size: 12px;
    font-weight: 700;
    line-height: 1.45;
    margin-top: 14px;
    padding: 12px 14px;
}
.wv-overdue-note i {
    color: #dc2626;
    margin-right: 4px;
}

/* Quick Navigation Links */
.wv-quick-links {
    display: grid;
    gap: 8px;
    margin-top: 18px;
}
.wv-quick-link {
    align-items: center;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    color: #1e293b !important;
    display: flex;
    font-size: 12px;
    font-weight: 700;
    gap: 10px;
    min-height: 42px;
    padding: 10px 12px;
    text-decoration: none !important;
    transition: all 0.18s ease;
}
.wv-quick-link i.link-icon {
    align-items: center;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    color: #0284c7;
    display: flex;
    font-size: 13px;
    height: 28px;
    justify-content: center;
    width: 28px;
}
.wv-quick-link span {
    flex-grow: 1;
}
.wv-quick-link i.fa-angle-right {
    color: #94a3b8;
    font-size: 14px;
}
.wv-quick-link:hover {
    background: #ffffff;
    border-color: #cbd5e1;
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.05);
    color: #0284c7 !important;
    transform: translateX(2px);
}

/* Mobile & Touch Responsiveness (Scroll Up/Down/Left/Right) */
@media (max-width: 1199px) {
    .wv-client-stats {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    .wv-rental-stats {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
    .wv-client-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 991px) {
    .wv-swipe-hint {
        display: flex;
    }
    /* Hide client profile person card and impersonation alert on mobile / tablet view */
    .wv-client-hero .wv-client-person {
        display: none !important;
    }
    .wv-client-impersonation {
        display: none !important;
    }
    .wv-client-hero {
        padding: 14px !important;
        margin-bottom: 16px !important;
        border-top: 3px solid #0284c7 !important;
        box-shadow: 0 2px 10px rgba(15, 23, 42, 0.04) !important;
    }
    .wv-client-hero-inner {
        display: block !important;
    }
    /* Action buttons: 2 in a line */
    .wv-client-actions {
        display: grid !important;
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 10px !important;
        width: 100% !important;
        margin-top: 0 !important;
    }
    .wv-client-actions .btn {
        justify-content: center !important;
        min-height: 44px !important;
        padding: 10px 12px !important;
        text-align: center !important;
        width: 100% !important;
        font-size: 12px !important;
        font-weight: 800 !important;
        border-radius: 999px !important;
        display: flex !important;
        align-items: center !important;
        gap: 6px !important;
    }
}

@media (max-width: 767px) {
    .wv-client-dashboard {
        margin: 0;
        padding: 10px;
    }
    .wv-client-hero {
        padding: 12px !important;
        margin-bottom: 14px !important;
    }
    .wv-client-actions {
        display: grid !important;
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 8px !important;
        width: 100% !important;
        margin-top: 0 !important;
    }
    .wv-client-actions .btn {
        min-height: 42px !important;
        padding: 9px 8px !important;
        font-size: 11.5px !important;
    }
    .wv-client-refund-alert {
        flex-direction: column;
        align-items: stretch;
    }
    .wv-client-refund-alert .btn {
        width: 100%;
        text-align: center;
    }
    .wv-client-stats {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }
    .wv-client-stat {
        padding: 14px;
        min-height: auto;
    }
    .wv-client-stat-value {
        font-size: 19px;
    }
    .wv-rental-stats {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 8px;
    }
    .wv-payment-deal-head {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }
    .wv-payment-deal-stats {
        width: 100%;
        justify-content: space-between;
    }
    .wv-client-table th,
    .wv-client-table td {
        padding: 9px 11px !important;
    }
}

@media (max-width: 480px) {
    /* Keep action buttons 2 in a line on smaller screens */
    .wv-client-actions {
        display: grid !important;
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 8px !important;
        width: 100% !important;
    }
    .wv-client-actions .btn {
        min-height: 42px !important;
        padding: 8px 6px !important;
        font-size: 11px !important;
    }
    .wv-client-stats {
        grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        gap: 8px !important;
    }
    .wv-rental-stats {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="wv-client-dashboard">
    <?php echo $this->Session->flash(); ?>

    <?php if(!empty($isFrontImpersonation)){ ?>
        <div class="wv-client-impersonation">
            <i class="fa fa-user-secret"></i>
            <span><?php echo __('Admin view is active for this client account. Dashboard data is loaded from the selected client record.'); ?></span>
        </div>
    <?php } ?>

    <?php if(!empty($runningRefundNotice['request_id'])){ ?>
        <div class="wv-client-refund-alert">
            <div>
                <strong><i class="fa fa-refresh"></i> <?php echo __('Your Deal Refund is running.'); ?></strong>
                <span>
                    <?php echo __('Pending Balance'); ?>:
                    <?php echo h($currency.$this->Number->format((float)$runningRefundNotice['pending_total'])); ?>
                    <?php if(!empty($runningRefundNotice['request_no'])){ ?>
                        | <?php echo __('Request'); ?>: <?php echo h($runningRefundNotice['request_no']); ?>
                    <?php } ?>
                </span>
            </div>
            <?php echo $this->Html->link(__('View Schedule'), array('plugin'=>false,'admin'=>false,'controller'=>'MyRefundrequests','action'=>'view',(int)$runningRefundNotice['request_id']), array('class'=>'btn btn-success')); ?>
        </div>
    <?php } ?>

    <!-- Hero Action Buttons Card (Professional Light Theme) -->
    <div class="wv-client-hero">
        <div class="wv-client-actions">
            <?php echo $this->Html->link('<i class="fa fa-handshake-o"></i> '.__('My Deals'), array('plugin'=>false,'admin'=>false,'controller'=>'MyDeals','action'=>'index'), array('class'=>'btn btn-deals','escape'=>false)); ?>
            <?php echo $this->Html->link('<i class="fa fa-credit-card"></i> '.__('Payments'), array('plugin'=>false,'admin'=>false,'controller'=>'MyPayments','action'=>'index'), array('class'=>'btn btn-payments','escape'=>false)); ?>
            <?php echo $this->Html->link('<i class="fa fa-folder-open-o"></i> '.__('Documents'), array('plugin'=>false,'admin'=>false,'controller'=>'MyDocuments','action'=>'index'), array('class'=>'btn btn-docs','escape'=>false)); ?>
            <?php echo $this->Html->link('<i class="fa fa-building-o"></i> '.__('Properties'), array('plugin'=>false,'admin'=>false,'controller'=>'Properties','action'=>'index'), array('class'=>'btn btn-props','escape'=>false)); ?>
        </div>
    </div>

    <!-- 4 KPI Summary Cards -->
    <div class="wv-client-stats">
        <div class="wv-client-stat stat-deals">
            <div class="wv-client-stat-top">
                <span class="wv-client-stat-label"><?php echo __('Visible Deals'); ?></span>
                <div class="wv-client-stat-icon"><i class="fa fa-briefcase"></i></div>
            </div>
            <div class="wv-client-stat-value"><?php echo (int)$dashboardSummary['deal_count']; ?></div>
            <div class="wv-client-stat-meta"><?php echo __('Active and blocked deals in your portal.'); ?></div>
        </div>

        <div class="wv-client-stat stat-paid">
            <div class="wv-client-stat-top">
                <span class="wv-client-stat-label"><?php echo __('Paid Amount'); ?></span>
                <div class="wv-client-stat-icon"><i class="fa fa-check-circle"></i></div>
            </div>
            <div class="wv-client-stat-value"><?php echo $currency.$this->Number->format($paidAmount); ?></div>
            <div class="wv-client-stat-meta"><?php echo __('Receipts'); ?>: <?php echo (int)$dashboardSummary['payment_count']; ?></div>
        </div>

        <div class="wv-client-stat stat-due">
            <div class="wv-client-stat-top">
                <span class="wv-client-stat-label"><?php echo __('Due Amount'); ?></span>
                <div class="wv-client-stat-icon"><i class="fa fa-clock-o"></i></div>
            </div>
            <div class="wv-client-stat-value"><?php echo $currency.$this->Number->format($dueAmount); ?></div>
            <div class="wv-client-stat-meta"><?php echo __('Deals With Due'); ?>: <?php echo (int)$dashboardSummary['due_deal_count']; ?></div>
        </div>

        <div class="wv-client-stat stat-overdue">
            <div class="wv-client-stat-top">
                <span class="wv-client-stat-label"><?php echo __('Overdue'); ?></span>
                <div class="wv-client-stat-icon"><i class="fa fa-exclamation-circle"></i></div>
            </div>
            <div class="wv-client-stat-value"><?php echo $currency.$this->Number->format($overdueAmount); ?></div>
            <div class="wv-client-stat-meta"><?php echo __('Installments'); ?>: <?php echo (int)$dashboardSummary['overdue_installment_count']; ?></div>
        </div>
    </div>

    <!-- Rentals Section (Conditional) -->
    <?php if(!empty($rentalDashboardSummary['rental_count'])){
        $myRentalsUrl = array('plugin'=>false,'admin'=>false,'controller'=>'MyRentals','action'=>'index');
        $rentalStart = !empty($rentalDashboardSummary['start_date']) ? date('d-M-Y', strtotime($rentalDashboardSummary['start_date'])) : '-';
        $rentalEnd = !empty($rentalDashboardSummary['end_date']) ? date('d-M-Y', strtotime($rentalDashboardSummary['end_date'])) : '-';
    ?>
        <section class="wv-rental-dashboard">
            <div class="wv-rental-dashboard-head">
                <h2 class="wv-rental-dashboard-title"><i class="fa fa-home"></i> <?php echo __('My Rentals'); ?></h2>
                <?php echo $this->Html->link(__('View Rentals').' <i class="fa fa-arrow-right"></i>', $myRentalsUrl, array('class'=>'wv-rental-dashboard-link','escape'=>false)); ?>
            </div>
            <div class="wv-rental-stats">
                <?php echo $this->Html->link('<div class="wv-rental-stat-icon"><i class="fa fa-calendar"></i></div><div class="wv-rental-stat-label">'.__('Rent Period').'</div><div class="wv-rental-stat-value">'.h($rentalStart).' '.__('to').' '.h($rentalEnd).'</div><div class="wv-rental-stat-meta">'.__('Total Rent').': '.h($currency.$this->Number->format((float)$rentalDashboardSummary['scheduled'])).'</div>', $myRentalsUrl, array('class'=>'wv-rental-stat period','escape'=>false)); ?>
                <?php echo $this->Html->link('<div class="wv-rental-stat-icon"><i class="fa fa-money"></i></div><div class="wv-rental-stat-label">'.__('Paid to You').'</div><div class="wv-rental-stat-value">'.h($currency.$this->Number->format((float)$rentalDashboardSummary['paid'])).'</div>', $myRentalsUrl, array('class'=>'wv-rental-stat paid','escape'=>false)); ?>
                <?php echo $this->Html->link('<div class="wv-rental-stat-icon"><i class="fa fa-clock-o"></i></div><div class="wv-rental-stat-label">'.__('Up-Coming').'</div><div class="wv-rental-stat-value">'.h($currency.$this->Number->format((float)$rentalDashboardSummary['upcoming'])).'</div>', $myRentalsUrl, array('class'=>'wv-rental-stat upcoming','escape'=>false)); ?>
                <?php echo $this->Html->link('<div class="wv-rental-stat-icon"><i class="fa fa-exclamation-triangle"></i></div><div class="wv-rental-stat-label">'.__('Overdue').'</div><div class="wv-rental-stat-value">'.h($currency.$this->Number->format((float)$rentalDashboardSummary['overdue'])).'</div>', $myRentalsUrl, array('class'=>'wv-rental-stat overdue','escape'=>false)); ?>
                <?php echo $this->Html->link('<div class="wv-rental-stat-icon"><i class="fa fa-wrench"></i></div><div class="wv-rental-stat-label">'.__('Owner Charges').'</div><div class="wv-rental-stat-value">'.h($currency.$this->Number->format((float)$rentalDashboardSummary['owner_charges'])).'</div>', $myRentalsUrl, array('class'=>'wv-rental-stat charges','escape'=>false)); ?>
                <?php echo $this->Html->link('<div class="wv-rental-stat-icon"><i class="fa fa-check-circle"></i></div><div class="wv-rental-stat-label">'.__('Net Received').'</div><div class="wv-rental-stat-value">'.h($currency.$this->Number->format((float)$rentalDashboardSummary['net_received'])).'</div>', $myRentalsUrl, array('class'=>'wv-rental-stat net','escape'=>false)); ?>
            </div>
        </section>
    <?php } ?>

    <!-- Utility Bills Section (Conditional) -->
    <?php if(!empty($utilityDashboardSummary['has_bills'])){
        $myUtilityUrl = array('plugin'=>false,'admin'=>false,'controller'=>'MyUtilityBills','action'=>'index');
        $uTotalBilled = isset($utilityDashboardSummary['total_billed']) ? (float)$utilityDashboardSummary['total_billed'] : 0.0;
        $uTotalPaid = isset($utilityDashboardSummary['total_paid']) ? (float)$utilityDashboardSummary['total_paid'] : 0.0;
        $uBalance = isset($utilityDashboardSummary['balance_outstanding']) ? (float)$utilityDashboardSummary['balance_outstanding'] : 0.0;
        $uUnpaidCount = isset($utilityDashboardSummary['unpaid_count']) ? (int)$utilityDashboardSummary['unpaid_count'] : 0;
        $uBillsCount = isset($utilityDashboardSummary['bills_count']) ? (int)$utilityDashboardSummary['bills_count'] : 0;
    ?>
        <section class="wv-rental-dashboard" style="margin-bottom:20px;">
            <div class="wv-rental-dashboard-head">
                <h2 class="wv-rental-dashboard-title"><i class="fa fa-bolt" style="color:#d97706;"></i> <?php echo __('Utility Bills & Power Ledger'); ?></h2>
                <?php echo $this->Html->link(__('View Full Utility Ledger').' <i class="fa fa-arrow-right"></i>', $myUtilityUrl, array('class'=>'wv-rental-dashboard-link','escape'=>false)); ?>
            </div>
            <div class="wv-rental-stats" style="grid-template-columns:repeat(4,minmax(0,1fr));">
                <?php echo $this->Html->link('<div class="wv-rental-stat-icon" style="background:#e0f2fe;color:#0284c7;"><i class="fa fa-file-text-o"></i></div><div class="wv-rental-stat-label">'.__('Total Bills').'</div><div class="wv-rental-stat-value">'.(int)$uBillsCount.' '.__('Bills').'</div><div class="wv-rental-stat-meta">'.__('Total Billed').': PKR '.number_format($uTotalBilled, 2).'</div>', $myUtilityUrl, array('class'=>'wv-rental-stat','escape'=>false)); ?>
                <?php echo $this->Html->link('<div class="wv-rental-stat-icon" style="background:#ecfdf5;color:#059669;"><i class="fa fa-check-circle"></i></div><div class="wv-rental-stat-label">'.__('Total Paid').'</div><div class="wv-rental-stat-value" style="color:#059669;">PKR '.number_format($uTotalPaid, 2).'</div><div class="wv-rental-stat-meta">'.__('Cleared Payments').'</div>', $myUtilityUrl, array('class'=>'wv-rental-stat paid','escape'=>false)); ?>
                <?php echo $this->Html->link('<div class="wv-rental-stat-icon" style="background:'.($uBalance > 0 ? '#fef2f2' : '#ecfdf5').';color:'.($uBalance > 0 ? '#dc2626' : '#059669').';"><i class="fa '.($uBalance > 0 ? 'fa-exclamation-triangle' : 'fa-check').'"></i></div><div class="wv-rental-stat-label">'.__('Balance Due').'</div><div class="wv-rental-stat-value" style="color:'.($uBalance > 0 ? '#dc2626' : '#059669').';">PKR '.number_format($uBalance, 2).'</div><div class="wv-rental-stat-meta">'.((int)$uUnpaidCount > 0 ? ((int)$uUnpaidCount.' '.__('Unpaid / Partial Invoices')) : __('All Dues Cleared')).'</div>', $myUtilityUrl, array('class'=>'wv-rental-stat '.($uBalance > 0 ? 'overdue' : 'paid'),'escape'=>false)); ?>
                <?php echo $this->Html->link('<div class="wv-rental-stat-icon" style="background:#f1f5f9;color:#0f172a;"><i class="fa fa-book"></i></div><div class="wv-rental-stat-label">'.__('Account Statement').'</div><div class="wv-rental-stat-value" style="font-size:14px;color:#0284c7;">'.__('View Statement').'</div><div class="wv-rental-stat-meta">'.__('Click to Open Full Ledger').'</div>', $myUtilityUrl, array('class'=>'wv-rental-stat','escape'=>false)); ?>
            </div>
        </section>
    <?php } ?>

    <!-- Main Dashboard Grid -->
    <div class="wv-client-grid">
        <!-- Main Left Column: Payment Receipts By Deal -->
        <div class="wv-client-panel">
            <div class="wv-client-panel-head">
                <div>
                    <h2 class="wv-client-panel-title"><?php echo __('Payment Receipts By Deal'); ?></h2>
                    <div class="wv-client-panel-sub"><?php echo __('All payment receipt transactions grouped deal wise.'); ?></div>
                </div>
                <?php echo $this->Html->link('<i class="fa fa-list"></i> '.__('Show All Payments'), array('plugin'=>false,'admin'=>false,'controller' => 'MyPayments', 'action' => 'index'), array('class' => 'btn btn-info btn-sm','escape'=>false)); ?>
            </div>

            <?php if(!empty($runningRefundNotice['request_id'])){ ?>
                <div class="wv-client-refund-alert" style="margin:14px 14px 0;">
                    <div>
                        <strong><i class="fa fa-refresh"></i> <?php echo __('Your Deal Refund is running.'); ?></strong>
                        <span>
                            <?php echo __('Pending Balance'); ?>:
                            <?php echo h($currency.$this->Number->format((float)$runningRefundNotice['pending_total'])); ?>
                            <?php if(!empty($runningRefundNotice['request_no'])){ ?>
                                | <?php echo __('Request'); ?>: <?php echo h($runningRefundNotice['request_no']); ?>
                            <?php } ?>
                        </span>
                    </div>
                    <?php echo $this->Html->link(__('View Schedule'), array('plugin'=>false,'admin'=>false,'controller'=>'MyRefundrequests','action'=>'view',(int)$runningRefundNotice['request_id']), array('class'=>'btn btn-success')); ?>
                </div>
            <?php } ?>

            <?php if(!empty($dealsPaymentSections)){ ?>
                <div class="wv-payment-sections">
                    <?php foreach($dealsPaymentSections as $sectionDealId=>$paymentSection):
                        $sectionDeal = !empty($paymentSection['Deal']) ? $paymentSection['Deal'] : array();
                        $sectionProperty = !empty($paymentSection['Property']) ? $paymentSection['Property'] : array();
                        $sectionPayments = !empty($paymentSection['payments']) ? (array)$paymentSection['payments'] : array();
                        $sectionInvoice = !empty($sectionDeal['invoice_no']) ? $sectionDeal['invoice_no'] : __('Deal').' #'.(int)$sectionDealId;
                        $sectionPropertyName = !empty($sectionProperty['name']) ? $sectionProperty['name'] : __('Property');
                        $sectionReceiptCount = !empty($paymentSection['receipt_count']) ? (int)$paymentSection['receipt_count'] : count($sectionPayments);
                        $sectionReceivedTotal = isset($paymentSection['received_total']) ? (float)$paymentSection['received_total'] : 0;
                        $sectionLedgerUrl = $useAdminDealLedger ? array('plugin'=>'admin','controller'=>'deals_payments','action'=>'index',(int)$sectionDealId,'?'=>array('client_preview'=>1)) : array('plugin'=>false,'admin'=>false,'controller'=>'MyPayments','action'=>'index',(int)$sectionDealId);
                    ?>
                        <div class="wv-payment-deal-section">
                            <div class="wv-payment-deal-head">
                                <div>
                                    <h3 class="wv-payment-deal-title"><?php echo h($sectionInvoice); ?></h3>
                                    <div class="wv-payment-deal-meta"><i class="fa fa-building-o"></i> <?php echo h($sectionPropertyName); ?></div>
                                </div>
                                <div class="wv-payment-deal-stats">
                                    <span class="wv-payment-deal-pill"><i class="fa fa-file-text-o"></i> <?php echo (int)$sectionReceiptCount; ?> <?php echo __('Receipts'); ?></span>
                                    <span class="wv-payment-deal-pill received"><i class="fa fa-check"></i> <?php echo $currency.$this->Number->format($sectionReceivedTotal); ?></span>
                                    <?php echo $this->Html->link('<i class="fa fa-list"></i> '.__('Ledger'), $sectionLedgerUrl, array('class'=>'wv-payment-ledger-link','escape'=>false)); ?>
                                </div>
                            </div>

                            <!-- Mobile Swipe Hint for Left/Right Scrolling -->
                            <div class="wv-swipe-hint">
                                <i class="fa fa-arrows-h"></i> <span><?php echo __('Scroll horizontally with fingers to view all payment details'); ?></span>
                            </div>

                            <!-- Finger-Scrollable Table Container (Up/Down/Left/Right) -->
                            <div class="wv-table-scroll-container">
                                <table class="table wv-client-table">
                                    <thead>
                                        <tr>
                                            <th><?php echo __('Receipt No.'); ?></th>
                                            <th><?php echo __('Paid Amount'); ?></th>
                                            <th><?php echo __('Installment + Late Fee'); ?></th>
                                            <th><?php echo __('Payment Name'); ?></th>
                                            <th><?php echo __('Paid Date'); ?></th>
                                            <th><?php echo __('Payment Type'); ?></th>
                                            <th><?php echo __('Transaction Reference / Remarks'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($sectionPayments as $post):
                                            $firstPaymentRow = !empty($post['first_row']) ? $post['first_row'] : array();
                                            $paymentName = !empty($post['payment_names']) ? implode(', ', (array)$post['payment_names']) : (!empty($firstPaymentRow['PlansPayment']['name']) ? $firstPaymentRow['PlansPayment']['name'] : (!empty($firstPaymentRow['DealsPayment']['name']) ? $firstPaymentRow['DealsPayment']['name'] : '-'));
                                            $paidAmount = isset($post['paid_amount']) ? (float)$post['paid_amount'] : 0;
                                            $lateFees = isset($post['late_fees']) ? (float)$post['late_fees'] : 0;
                                            $receiptNo = !empty($post['receipt_no']) ? $post['receipt_no'] : '-';
                                            $paidDate = !empty($firstPaymentRow['DealsPayment']['payment_date']) ? $this->Time->format($sysDay.$dateSep.$sysMonth.$dateSep.$sysYear, h($firstPaymentRow['DealsPayment']['payment_date'])) : '-';
                                            $paymentType = !empty($firstPaymentRow['Paymenttype']['name']) ? $firstPaymentRow['Paymenttype']['name'] : '-';
                                            $remarks = !empty($firstPaymentRow['DealsPayment']['remarks']) ? $firstPaymentRow['DealsPayment']['remarks'] : '-';
                                        ?>
                                            <tr>
                                                <td><span class="wv-receipt-code"><?php echo h($receiptNo); ?></span></td>
                                                <td><span class="wv-payment-amount"><?php echo $currency.$this->Number->format($paidAmount); ?></span></td>
                                                <td>
                                                    <span class="wv-fee-amount"><?php echo $currency.$this->Number->format($paidAmount); ?></span>
                                                    <?php if($lateFees > 0): ?>
                                                        <span style="color:#64748b;"> + </span>
                                                        <span class="wv-fee-amount has-late"><?php echo $currency.$this->Number->format($lateFees); ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo h($paymentName); ?></td>
                                                <td style="white-space:nowrap;"><?php echo h($paidDate); ?></td>
                                                <td><?php echo h($paymentType); ?></td>
                                                <td><?php echo h($remarks); ?></td>
                                            </tr>
                                        <?php endforeach; unset($post); ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endforeach; unset($paymentSection); ?>
                </div>
            <?php }else{ ?>
                <div class="wv-empty-payments">
                    <i class="fa fa-folder-open-o" style="font-size:32px;color:#cbd5e1;display:block;margin-bottom:8px;"></i>
                    <?php echo __('No payment records found.'); ?>
                </div>
            <?php } ?>
        </div>

        <!-- Sidebar Right Column: Progress & Quick Navigation -->
        <div class="wv-client-panel">
            <div class="wv-side-card">
                <h3 class="wv-side-title"><i class="fa fa-pie-chart"></i> <?php echo __('Payment Progress'); ?></h3>
                <div class="wv-progress-shell">
                    <div class="wv-progress-bar<?php echo (int)$paidPercent <= 0 ? ' is-zero' : ''; ?>" style="width:<?php echo (int)$paidPercent; ?>%;">
                        <span class="wv-progress-label"><?php echo (int)$paidPercent; ?>%</span>
                    </div>
                </div>
                <div class="wv-progress-meta">
                    <span><?php echo __('Paid of Total'); ?></span>
                    <strong><?php echo (int)$paidPercent; ?>%</strong>
                </div>

                <?php if($overdueAmount > 0){ ?>
                    <div class="wv-overdue-note">
                        <i class="fa fa-exclamation-triangle"></i>
                        <span><?php echo __('Some installments are currently overdue. Please review your payment schedule.'); ?></span>
                    </div>
                <?php } ?>

                <div class="wv-quick-links">
                    <?php echo $this->Html->link(
                        '<i class="fa fa-handshake-o link-icon"></i><span>'.__('View My Deals').'</span><i class="fa fa-angle-right"></i>',
                        array('plugin'=>false,'admin'=>false,'controller'=>'MyDeals','action'=>'index'),
                        array('class'=>'wv-quick-link','escape'=>false)
                    ); ?>
                    <?php echo $this->Html->link(
                        '<i class="fa fa-credit-card link-icon"></i><span>'.__('Payment Receipts').'</span><i class="fa fa-angle-right"></i>',
                        array('plugin'=>false,'admin'=>false,'controller'=>'MyPayments','action'=>'index'),
                        array('class'=>'wv-quick-link','escape'=>false)
                    ); ?>
                    <?php echo $this->Html->link(
                        '<i class="fa fa-folder-open-o link-icon"></i><span>'.__('My Documents & Vault').'</span><i class="fa fa-angle-right"></i>',
                        array('plugin'=>false,'admin'=>false,'controller'=>'MyDocuments','action'=>'index'),
                        array('class'=>'wv-quick-link','escape'=>false)
                    ); ?>
                    <?php echo $this->Html->link(
                        '<i class="fa fa-clock-o link-icon"></i><span>'.__('Invoices Past Due').'</span><i class="fa fa-angle-right"></i>',
                        array('plugin'=>false,'admin'=>false,'controller'=>'MyInvpastdues','action'=>'index'),
                        array('class'=>'wv-quick-link','escape'=>false)
                    ); ?>
                    <?php echo $this->Html->link(
                        '<i class="fa fa-user-circle-o link-icon"></i><span>'.__('My Profile').'</span><i class="fa fa-angle-right"></i>',
                        array('plugin'=>false,'admin'=>false,'controller'=>'MyProfiles','action'=>'index'),
                        array('class'=>'wv-quick-link','escape'=>false)
                    ); ?>
                </div>
            </div>
        </div>
    </div>
</div>
