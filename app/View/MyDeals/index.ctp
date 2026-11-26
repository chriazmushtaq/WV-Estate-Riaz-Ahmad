<?php
$dealPaymentSummary = isset($dealPaymentSummary) && is_array($dealPaymentSummary) ? $dealPaymentSummary : array();
$refundRequestBlocks = isset($refundRequestBlocks) && is_array($refundRequestBlocks) ? $refundRequestBlocks : array();
$runningRefundNotice = isset($runningRefundNotice) && is_array($runningRefundNotice) ? $runningRefundNotice : array();
$totalDeals = count((array)$postArr);
$totalDealValue = 0;
$totalPaidValue = 0;
$totalDueValue = 0;
foreach((array)$postArr as $summaryDeal)
{
    $summaryDealId = !empty($summaryDeal['MyDeal']['id']) ? (int)$summaryDeal['MyDeal']['id'] : 0;
    $summaryTotal = isset($summaryDeal['MyDeal']['total_amount']) ? (float)$summaryDeal['MyDeal']['total_amount'] : 0;
    $summaryPaid = isset($dealPaymentSummary[$summaryDealId]['paid_amount']) ? (float)$dealPaymentSummary[$summaryDealId]['paid_amount'] : 0;
    $totalDealValue += $summaryTotal;
    $totalPaidValue += $summaryPaid;
    $summaryDue = $summaryTotal - $summaryPaid;
    if($summaryDue > 0)
    {
        $totalDueValue += $summaryDue;
    }
}
$propertyActionUrl=$this->Html->url(array('controller'=>'Myproperties'));
$useAdminDealLedger = $this->Session->check('User');
?>
<style type="text/css">
/* Light Theme Base */
body {
    background: #f8fafc;
}
.wv-mydeals-page {
    background: #f8fafc;
    margin: -10px -5px 0;
    padding: 16px;
    touch-action: auto;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    width: 100%;
}
.wv-mydeals-page * {
    box-sizing: border-box;
}

/* Professional Hero Header - Light Theme */
.wv-mydeals-hero {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-top: 4px solid #0284c7;
    border-radius: 14px;
    box-shadow: 0 4px 18px rgba(15, 23, 42, 0.05);
    margin-bottom: 18px;
    padding: 22px 24px;
    position: relative;
}
.wv-mydeals-hero-inner {
    align-items: center;
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    justify-content: space-between;
}
.wv-mydeals-kicker {
    align-items: center;
    color: #0284c7;
    display: inline-flex;
    font-size: 11px;
    font-weight: 800;
    gap: 6px;
    letter-spacing: 0.5px;
    margin-bottom: 5px;
    text-transform: uppercase;
}
.wv-mydeals-hero h1 {
    color: #0f172a !important;
    font-size: 24px;
    font-weight: 900;
    line-height: 1.25;
    margin: 0 0 6px;
}
.wv-mydeals-hero p {
    color: #475569 !important;
    font-size: 13px;
    font-weight: 500;
    line-height: 1.5;
    margin: 0;
    max-width: 680px;
}
.wv-mydeals-hero-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}
.wv-mydeals-hero-actions .btn {
    align-items: center;
    border-radius: 8px;
    display: inline-flex;
    font-size: 11px;
    font-weight: 800;
    gap: 6px;
    min-height: 38px;
    padding: 9px 15px;
    text-decoration: none !important;
    text-transform: uppercase;
    transition: all 0.2s ease;
}
.wv-mydeals-hero-actions .btn-payments {
    background: #0284c7 !important;
    border: 1px solid #0284c7 !important;
    color: #ffffff !important;
    box-shadow: 0 2px 6px rgba(2, 132, 199, 0.2);
}
.wv-mydeals-hero-actions .btn-payments:hover {
    background: #0369a1 !important;
    border-color: #0369a1 !important;
    transform: translateY(-1px);
}
.wv-mydeals-hero-actions .btn-props {
    background: #ffffff !important;
    border: 1px solid #cbd5e1 !important;
    color: #0f172a !important;
}
.wv-mydeals-hero-actions .btn-props:hover {
    background: #f1f5f9 !important;
    border-color: #94a3b8 !important;
    transform: translateY(-1px);
}
.wv-mydeals-hero-actions .btn-docs {
    background: #ffffff !important;
    border: 1px solid #e2e8f0 !important;
    color: #475569 !important;
}
.wv-mydeals-hero-actions .btn-docs:hover {
    background: #f8fafc !important;
    color: #0f172a !important;
    transform: translateY(-1px);
}

/* Overall KPI Summary Cards */
.wv-mydeals-stats {
    display: grid;
    gap: 12px;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    margin-bottom: 18px;
}
.wv-mydeals-stat {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
    min-height: 105px;
    padding: 16px 18px;
    position: relative;
    transition: all 0.2s ease;
}
.wv-mydeals-stat:hover {
    border-color: #cbd5e1;
    box-shadow: 0 6px 18px rgba(15, 23, 42, 0.07);
    transform: translateY(-1px);
}
.wv-mydeals-stat-top {
    align-items: center;
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
}
.wv-mydeals-stat span {
    color: #64748b;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.4px;
    text-transform: uppercase;
}
.wv-mydeals-stat-icon {
    align-items: center;
    border-radius: 8px;
    display: flex;
    font-size: 15px;
    height: 32px;
    justify-content: center;
    width: 32px;
}
.stat-deals .wv-mydeals-stat-icon {
    background: #eff6ff;
    color: #2563eb;
}
.stat-val .wv-mydeals-stat-icon {
    background: #f1f5f9;
    color: #0f172a;
}
.stat-paid .wv-mydeals-stat-icon {
    background: #ecfdf5;
    color: #059669;
}
.stat-due .wv-mydeals-stat-icon {
    background: #fff7ed;
    color: #d97706;
}
.wv-mydeals-stat strong {
    color: #0f172a;
    display: block;
    font-size: 20px;
    font-weight: 900;
    line-height: 1.15;
    word-break: break-word;
}
.stat-paid strong {
    color: #059669;
}
.stat-due strong {
    color: #d97706;
}
.wv-mydeals-stat small {
    color: #64748b;
    display: block;
    font-size: 11px;
    font-weight: 600;
    margin-top: 4px;
}

/* Refund Running Notice */
.wv-mydeals-refund-alert {
    align-items: center;
    background: #ecfdf5;
    border: 1px solid #a7f3d0;
    border-radius: 12px;
    color: #065f46;
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    justify-content: space-between;
    margin-bottom: 18px;
    padding: 14px 18px;
}
.wv-mydeals-refund-alert strong {
    display: block;
    font-size: 13px;
    font-weight: 800;
}
.wv-mydeals-refund-alert span {
    display: block;
    font-size: 12px;
    font-weight: 600;
    margin-top: 3px;
}
.wv-mydeals-refund-alert .btn {
    border-radius: 8px;
    font-size: 11px;
    font-weight: 800;
    min-height: 34px;
    padding: 7px 14px;
    text-transform: uppercase;
}

/* Deals Grid */
.wv-mydeals-grid {
    display: grid;
    gap: 18px;
    grid-template-columns: repeat(2, minmax(0, 1fr));
}

/* Deal Card - Light Theme */
.wv-deal-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    box-shadow: 0 4px 14px rgba(15, 23, 42, 0.05);
    overflow: hidden;
    transition: all 0.2s ease;
}
.wv-deal-card:hover {
    border-color: #cbd5e1;
    box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
}
.wv-deal-card-head {
    align-items: flex-start;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    border-left: 4px solid #0284c7;
    display: flex;
    gap: 12px;
    justify-content: space-between;
    padding: 14px 18px;
}
.wv-deal-card-head .wv-deal-title {
    color: #0f172a !important;
    font-size: 16px;
    font-weight: 900;
    line-height: 1.25;
    margin: 0;
}
.wv-deal-card-head .wv-deal-sub {
    color: #64748b !important;
    font-size: 12px;
    font-weight: 600;
    margin-top: 3px;
}
.wv-deal-badge {
    align-items: center;
    background: #ecfdf5;
    border: 1px solid #a7f3d0;
    border-radius: 6px;
    color: #059669 !important;
    display: inline-flex;
    font-size: 11px;
    font-weight: 800;
    gap: 5px;
    padding: 4px 10px;
    white-space: nowrap;
}

/* Deal Body */
.wv-deal-body {
    padding: 16px 18px 18px;
}

/* Money Boxes */
.wv-deal-money {
    display: grid;
    gap: 10px;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    margin-bottom: 14px;
}
.wv-deal-money-box {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 10px 12px;
}
.wv-deal-money-box span {
    color: #64748b;
    display: block;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 0.3px;
    margin-bottom: 3px;
    text-transform: uppercase;
}
.wv-deal-money-box strong {
    color: #0f172a;
    display: block;
    font-size: 14px;
    font-weight: 900;
    line-height: 1.2;
    word-break: break-word;
}
.wv-deal-money-box.paid strong {
    color: #059669;
}
.wv-deal-money-box.due strong {
    color: #d97706;
}

/* Payment Progress Bar */
.wv-deal-progress {
    align-items: center;
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    border-radius: 999px;
    display: flex;
    height: 18px;
    margin-bottom: 14px;
    overflow: hidden;
    position: relative;
}
.wv-deal-progress-fill {
    background: linear-gradient(90deg, #10b981, #059669);
    border-radius: 999px;
    display: block;
    height: 100%;
    min-width: 34px;
    position: relative;
    transition: width 0.4s ease;
}
.wv-deal-progress-fill.is-zero {
    background: transparent;
    min-width: 34px;
}
.wv-deal-progress-label {
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
.wv-deal-progress-fill.is-zero .wv-deal-progress-label {
    color: #64748b;
    position: static;
    text-align: center;
    width: 100%;
}

/* Deal Meta Grid */
.wv-deal-meta {
    display: grid;
    gap: 8px;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    margin-bottom: 14px;
}
.wv-deal-meta-item {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 8px 12px;
}
.wv-deal-meta-item span {
    color: #64748b;
    display: block;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 0.3px;
    text-transform: uppercase;
}
.wv-deal-meta-item strong {
    color: #0f172a;
    display: block;
    font-size: 12px;
    font-weight: 800;
    margin-top: 2px;
    word-break: break-word;
}

/* Refund Status Box */
.wv-deal-refund-status {
    align-items: center;
    background: #fff7ed;
    border: 1px solid #fed7aa;
    border-radius: 8px;
    color: #9a3412;
    display: flex;
    gap: 8px;
    justify-content: space-between;
    margin: 0 0 12px;
    padding: 9px 12px;
}
.wv-deal-refund-status strong {
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
}
.wv-deal-refund-status .btn {
    border-radius: 6px;
    font-size: 10px;
    font-weight: 800;
    padding: 4px 10px;
    text-transform: uppercase;
    white-space: nowrap;
}

/* Action Buttons Container - Mobile Finger-Scrollable (Up/Down/Left/Right) */
.wv-actions-scroll-wrap {
    margin-top: 10px;
    overflow-x: auto;
    overflow-y: hidden;
    overscroll-behavior-x: contain;
    position: relative;
    touch-action: pan-x pan-y;
    -webkit-overflow-scrolling: touch;
    width: 100%;
}
.wv-deal-actions-hint {
    align-items: center;
    color: #64748b;
    display: none;
    font-size: 11px;
    font-weight: 700;
    gap: 6px;
    margin-bottom: 6px;
}
.wv-deal-actions-hint i {
    color: #0284c7;
}
.wv-deal-actions {
    align-items: stretch;
    display: grid;
    gap: 6px;
    grid-template-columns: repeat(6, minmax(0, 1fr));
}
.wv-deal-actions .wv-action-btn,
.wv-deal-actions a.wv-action-btn,
.wv-deal-actions button.wv-action-btn {
    align-items: center;
    border: 0;
    border-radius: 8px !important;
    box-shadow: 0 2px 6px rgba(15, 23, 42, 0.08);
    color: #ffffff !important;
    cursor: pointer;
    display: flex;
    flex-direction: column;
    font-size: 10px;
    font-weight: 800;
    justify-content: center;
    letter-spacing: 0.3px;
    line-height: 1.15;
    min-height: 48px;
    padding: 7px 4px;
    text-align: center;
    text-decoration: none !important;
    text-transform: uppercase;
    transition: all 0.18s ease;
}
.wv-deal-actions .wv-action-btn * {
    color: #ffffff !important;
}
.wv-deal-actions .wv-action-btn .fa,
.wv-deal-actions .wv-action-btn .glyphicon {
    display: block;
    font-size: 14px;
    margin-bottom: 3px;
}
.wv-deal-actions .wv-action-btn:hover {
    filter: brightness(1.12);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.16);
}

/* Button Color Schemes - Professional & Legible */
.wv-btn-calendar { background: #0284c7 !important; }
.wv-btn-discount { background: #d97706 !important; }
.wv-btn-invoice { background: #0369a1 !important; }
.wv-btn-ledger { background: #059669 !important; }
.wv-btn-pdf { background: #dc2626 !important; }
.wv-btn-details { background: #4f46e5 !important; }

.wv-btn-letters { background: #2563eb !important; }
.wv-btn-vault { background: #7c3aed !important; }
.wv-btn-refund { background: #ea580c !important; }
.wv-btn-photos { background: #475569 !important; }
.wv-btn-documents { background: #0f766e !important; }
.wv-btn-pay { background: #16a34a !important; }

.wv-deal-actions .wv-action-btn.disabled {
    cursor: not-allowed;
    opacity: 0.55;
    pointer-events: none;
}

/* Empty State */
.wv-mydeals-empty {
    background: #ffffff;
    border: 1px dashed #cbd5e1;
    border-radius: 12px;
    color: #64748b;
    font-size: 14px;
    font-weight: 800;
    padding: 40px 20px;
    text-align: center;
}

/* Responsive Breakpoints - Mobile Readability & Finger Scrolling */
@media (max-width: 1199px) {
    .wv-mydeals-grid {
        grid-template-columns: 1fr;
    }
}
@media (max-width: 991px) {
    .wv-mydeals-stats {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    .wv-deal-actions {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
    .wv-deal-actions-hint {
        display: flex;
    }
}
@media (max-width: 767px) {
    .wv-mydeals-page {
        margin: 0;
        padding: 10px;
    }
    .wv-mydeals-hero {
        padding: 16px;
    }
    .wv-mydeals-hero-inner {
        flex-direction: column;
        align-items: flex-start;
    }
    .wv-mydeals-hero h1 {
        font-size: 20px;
    }
    .wv-mydeals-hero-actions {
        width: 100%;
        margin-top: 10px;
    }
    .wv-mydeals-hero-actions .btn {
        flex: 1 1 auto;
        justify-content: center;
    }
    .wv-mydeals-stats {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }
    .wv-mydeals-stat {
        padding: 14px;
        min-height: auto;
    }
    .wv-mydeals-stat strong {
        font-size: 18px;
    }
    .wv-deal-card-head {
        padding: 12px 14px;
    }
    .wv-deal-body {
        padding: 14px;
    }
    .wv-deal-money {
        gap: 8px;
    }
    .wv-deal-money-box strong {
        font-size: 12.5px;
    }
    .wv-deal-meta {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 6px;
    }
    .wv-deal-actions {
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 6px;
    }
    .wv-deal-actions .wv-action-btn {
        min-height: 44px;
        font-size: 9.5px;
        padding: 6px 2px;
    }
}
@media (max-width: 480px) {
    .wv-mydeals-stats {
        grid-template-columns: 1fr;
    }
    .wv-deal-card-head {
        flex-direction: column;
        align-items: flex-start;
        gap: 6px;
    }
    .wv-deal-money {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
    .wv-deal-actions {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}
</style>

<div class="wv-mydeals-page">
    <?php echo $this->Session->flash(); ?>

    <!-- Professional Hero Header - Light Theme -->
    <div class="wv-mydeals-hero">
        <div class="wv-mydeals-hero-inner">
            <div>
                <div class="wv-mydeals-kicker"><i class="fa fa-handshake-o"></i> <?php echo __('Client Deal Portfolio'); ?></div>
                <h1><?php echo __('My Property Deals & Bookings'); ?></h1>
                <p><?php echo __('Comprehensive verified portfolio of your property bookings, installment schedules, invoices, and society legal records.'); ?></p>
            </div>
            <div class="wv-mydeals-hero-actions">
                <?php echo $this->Html->link('<i class="fa fa-credit-card"></i> '.__('Payment Ledger'), array('plugin'=>false,'admin'=>false,'controller'=>'MyPayments','action'=>'index'), array('class'=>'btn btn-payments','escape'=>false)); ?>
                <?php echo $this->Html->link('<i class="fa fa-folder-open-o"></i> '.__('My Documents'), array('plugin'=>false,'admin'=>false,'controller'=>'MyDocuments','action'=>'index'), array('class'=>'btn btn-docs','escape'=>false)); ?>
                <?php echo $this->Html->link('<i class="fa fa-building-o"></i> '.__('Properties'), array('plugin'=>false,'admin'=>false,'controller'=>'Properties','action'=>'index'), array('class'=>'btn btn-props','escape'=>false)); ?>
            </div>
        </div>
    </div>

    <!-- Overall KPI Summary Cards -->
    <div class="wv-mydeals-stats">
        <div class="wv-mydeals-stat stat-deals">
            <div class="wv-mydeals-stat-top">
                <span><?php echo __('Total Deals'); ?></span>
                <div class="wv-mydeals-stat-icon"><i class="fa fa-briefcase"></i></div>
            </div>
            <strong><?php echo (int)$totalDeals; ?></strong>
            <small><?php echo __('Active property deals'); ?></small>
        </div>

        <div class="wv-mydeals-stat stat-val">
            <div class="wv-mydeals-stat-top">
                <span><?php echo __('Total Value'); ?></span>
                <div class="wv-mydeals-stat-icon"><i class="fa fa-money"></i></div>
            </div>
            <strong><?php echo $currency.$this->Number->format($totalDealValue); ?></strong>
            <small><?php echo __('Registered invoice amount'); ?></small>
        </div>

        <div class="wv-mydeals-stat stat-paid">
            <div class="wv-mydeals-stat-top">
                <span><?php echo __('Paid Amount'); ?></span>
                <div class="wv-mydeals-stat-icon"><i class="fa fa-check-circle"></i></div>
            </div>
            <strong><?php echo $currency.$this->Number->format($totalPaidValue); ?></strong>
            <small><?php echo __('Cleared payments'); ?></small>
        </div>

        <div class="wv-mydeals-stat stat-due">
            <div class="wv-mydeals-stat-top">
                <span><?php echo __('Balance Due'); ?></span>
                <div class="wv-mydeals-stat-icon"><i class="fa fa-clock-o"></i></div>
            </div>
            <strong><?php echo $currency.$this->Number->format($totalDueValue); ?></strong>
            <small><?php echo __('Remaining balance'); ?></small>
        </div>
    </div>

    <?php if(!empty($runningRefundNotice['request_id'])){ ?>
        <div class="wv-mydeals-refund-alert">
            <div>
                <strong><i class="fa fa-refresh"></i> <?php echo __('Your Deal Refund is running'); ?></strong>
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

    <?php if(empty($postArr)){ ?>
        <div class="wv-mydeals-empty">
            <i class="fa fa-folder-open-o" style="font-size:36px;color:#cbd5e1;display:block;margin-bottom:10px;"></i>
            <?php echo __('No property deals found in your account.'); ?>
        </div>
    <?php }else{ ?>
        <div class="wv-mydeals-grid">
            <?php foreach($postArr as $post):
                $id=(int)$post['MyDeal']['id'];
                $propertyId=(int)$post['MyDeal']['property_id'];
                $propertyName = !empty($post['Property']['name']) ? $post['Property']['name'] : __('Property');
                $dealTotal = isset($post['MyDeal']['total_amount']) ? (float)$post['MyDeal']['total_amount'] : 0;
                $dealPaid = isset($dealPaymentSummary[$id]['paid_amount']) ? (float)$dealPaymentSummary[$id]['paid_amount'] : 0;
                $dealDue = $dealTotal - $dealPaid;
                if($dealDue < 0){ $dealDue = 0; }
                $receiptCount = isset($dealPaymentSummary[$id]['receipt_count']) ? (int)$dealPaymentSummary[$id]['receipt_count'] : 0;
                $progressPercent = $dealTotal > 0 ? round(($dealPaid / $dealTotal) * 100) : 0;
                if($progressPercent > 100){ $progressPercent = 100; }
                $dealArea = !empty($post['Property']['area']) ? $post['Property']['area'] : '-';
                $dealUnit = !empty($post['Unit']['name']) ? ' '.$post['Unit']['name'] : '';
                $planName = !empty($post['Plan']['name']) ? $post['Plan']['name'] : '-';
                $dealLedgerUrl = $useAdminDealLedger ? array('plugin'=>'admin','controller'=>'deals_payments','action'=>'index',$id,'?'=>array('client_preview'=>1)) : array('plugin'=>false,'admin'=>false,'controller'=>'MyPayments','action'=>'index',$id);
                $dealLedgerPdfUrl = array('plugin'=>false,'admin'=>false,'controller'=>'MyDeals','action'=>'ledgerpdf',$id);
                $clientRefundAllowed = !empty($post['MyDeal']['client_refund_allowed']);
                $refundRequestBlock = isset($refundRequestBlocks[$id]) ? (array)$refundRequestBlocks[$id] : array();
                $refundRequestStatus = !empty($refundRequestBlock['status']) ? $refundRequestBlock['status'] : __('In Process');
                $refundRequestId = !empty($refundRequestBlock['id']) ? (int)$refundRequestBlock['id'] : 0;
                $canApplyRefund = !empty($clientRefundAllowed) && (empty($refundRequestBlock) || $refundRequestStatus === 'Cancelled');
                $dealBilling = !empty($dealsBillingMap[$id]) ? $dealsBillingMap[$id] : array();
                $hasBillingMeter = !empty($dealBilling['has_billing']);
            ?>
                <div class="wv-deal-card">
                    <div class="wv-deal-card-head">
                        <div>
                            <h2 class="wv-deal-title"><?php echo h($propertyName); ?></h2>
                            <div class="wv-deal-sub">
                                <i class="fa fa-file-text-o"></i> <?php echo __('Invoice'); ?>: <strong><?php echo h(!empty($post['MyDeal']['invoice_no']) ? $post['MyDeal']['invoice_no'] : '-'); ?></strong>
                            </div>
                        </div>
                        <span class="wv-deal-badge"><i class="fa fa-check"></i> <?php echo (int)$progressPercent; ?>% <?php echo __('Paid'); ?></span>
                    </div>

                    <div class="wv-deal-body">
                        <!-- Money Summary Boxes -->
                        <div class="wv-deal-money">
                            <div class="wv-deal-money-box">
                                <span><?php echo __('Total Price'); ?></span>
                                <strong><?php echo $currency.$this->Number->format($dealTotal); ?></strong>
                            </div>
                            <div class="wv-deal-money-box paid">
                                <span><?php echo __('Paid Amount'); ?></span>
                                <strong><?php echo $currency.$this->Number->format($dealPaid); ?></strong>
                            </div>
                            <div class="wv-deal-money-box due">
                                <span><?php echo __('Balance Due'); ?></span>
                                <strong><?php echo $currency.$this->Number->format($dealDue); ?></strong>
                            </div>
                        </div>

                        <!-- Progress Bar -->
                        <div class="wv-deal-progress" aria-label="<?php echo h(__('Payment Progress')); ?>">
                            <span class="wv-deal-progress-fill<?php echo (int)$progressPercent <= 0 ? ' is-zero' : ''; ?>" style="width:<?php echo (int)$progressPercent; ?>%;">
                                <span class="wv-deal-progress-label"><?php echo (int)$progressPercent; ?>%</span>
                            </span>
                        </div>

                        <!-- Deal Meta Information -->
                        <div class="wv-deal-meta">
                            <div class="wv-deal-meta-item">
                                <span><?php echo __('Invoice Date'); ?></span>
                                <strong><?php echo $this->Time->format($sysDay.$dateSep.$sysMonth.$dateSep.$sysYear, h($post['MyDeal']['date'])); ?></strong>
                            </div>
                            <div class="wv-deal-meta-item">
                                <span><?php echo __('Area & Unit'); ?></span>
                                <strong><?php echo h($dealArea.$dealUnit); ?></strong>
                            </div>
                            <div class="wv-deal-meta-item">
                                <span><?php echo __('Payment Plan'); ?></span>
                                <strong><?php echo h($planName); ?></strong>
                            </div>
                            <div class="wv-deal-meta-item">
                                <span><?php echo __('Receipts Count'); ?></span>
                                <strong><?php echo (int)$receiptCount; ?> <?php echo __('Receipts'); ?></strong>
                            </div>
                        </div>

                        <?php if(!empty($refundRequestId)){ ?>
                            <div class="wv-deal-refund-status">
                                <strong><i class="fa fa-refresh"></i> <?php echo __('Refund Status'); ?>: <?php echo h($refundRequestStatus); ?></strong>
                                <?php echo $this->Html->link(__('View'), array('plugin'=>false,'admin'=>false,'controller'=>'MyRefundrequests','action'=>'view',$refundRequestId), array('class'=>'btn btn-warning')); ?>
                            </div>
                        <?php } ?>

                        <!-- Action Buttons - Touch Scrollable Container -->
                        <div class="wv-actions-scroll-wrap">
                            <div class="wv-deal-actions">
                                <!-- Line 1 -->
                                <?php echo $this->Html->link('<i class="fa fa-calendar"></i><span>'.__('Calendar').'</span>', array('plugin'=>false,'admin'=>false,'controller'=>'MyDeals','action'=>'calendar_ics',$id), array('class'=>'wv-action-btn wv-btn-calendar','escape'=>false,'title'=>__('Add Installment Schedule to Calendar'))); ?>
                                <button type="button" class="wv-action-btn wv-btn-discount" onclick="openEarlyDiscountModal(<?php echo $id; ?>);" title="<?php echo __('Calculate Early Settlement Discount'); ?>"><i class="fa fa-calculator"></i><span><?php echo __('Discount'); ?></span></button>
                                <?php echo $this->Html->link('<i class="fa fa-print"></i><span>'.__('Payment Plan').'</span>', array('plugin'=>false,'admin'=>false,'controller'=>'MyDeals','action'=>'printinvoice',$id), array('class'=>'wv-action-btn wv-btn-invoice','target'=>'_blank','escape'=>false,'title'=>__('Print Payment Plan'))); ?>
                                <?php echo $this->Html->link('<i class="fa fa-credit-card"></i><span>'.__('Payments').'</span>', $dealLedgerUrl, array('class'=>'wv-action-btn wv-btn-ledger','escape'=>false,'title'=>__('View Payments'))); ?>
                                <?php echo $this->Html->link('<i class="fa fa-file-pdf-o"></i><span>'.__('Ledger PDF').'</span>', $dealLedgerPdfUrl, array('class'=>'wv-action-btn wv-btn-pdf','target'=>'_blank','rel'=>'noopener','escape'=>false,'title'=>__('Download Ledger PDF'))); ?>
                                <?php echo $this->Html->link('<i class="fa fa-info-circle"></i><span>'.__('Details').'</span>', '#', array('onclick'=>"show_modal('MyDeals/view/$id');",'class'=>'wv-action-btn wv-btn-details','escape'=>false,'title'=>__('View Deal Details'))); ?>

                                <!-- Line 2 -->
                                <?php echo $this->Html->link('<i class="fa fa-envelope-o"></i><span>'.__('Letters').'</span>', array('plugin'=>false,'admin'=>false,'controller'=>'MyDeals','action'=>'letters',$id), array('class'=>'wv-action-btn wv-btn-letters','escape'=>false,'title'=>__('Deal Letters & Notices'))); ?>
                                <?php echo $this->Html->link('<i class="fa fa-shield"></i><span>'.__('Vault').'</span>', array('plugin'=>false,'admin'=>false,'controller'=>'MyDocuments','action'=>'index'), array('class'=>'wv-action-btn wv-btn-vault','escape'=>false,'title'=>__('Document Vault'))); ?>
                                <?php 
                                if(!empty($refundRequestId)){
                                    echo $this->Html->link('<i class="fa fa-refresh"></i><span>'.__('Refund').'</span>', array('plugin'=>false,'admin'=>false,'controller'=>'MyRefundrequests','action'=>'view',$refundRequestId), array('class'=>'wv-action-btn wv-btn-refund','escape'=>false,'title'=>__('View Refund Status').': '.$refundRequestStatus));
                                } elseif(!empty($canApplyRefund)){
                                    echo $this->Html->link('<i class="fa fa-undo"></i><span>'.__('Refund').'</span>', array('plugin'=>false,'admin'=>false,'controller'=>'MyRefundrequests','action'=>'apply',$id), array('class'=>'wv-action-btn wv-btn-refund','escape'=>false,'title'=>__('Apply for Refund')));
                                } else {
                                    echo $this->Html->link('<i class="fa fa-undo"></i><span>'.__('Refund').'</span>', '#', array('class'=>'wv-action-btn wv-btn-refund disabled','escape'=>false,'title'=>__('Refund not available')));
                                }
                                ?>
                                <?php echo $this->Html->link('<i class="fa fa-image"></i><span>'.__('Photos').'</span>', '#', array('onclick'=>"show_modal('$propertyActionUrl/photo/$propertyId');",'class'=>'wv-action-btn wv-btn-photos','escape'=>false,'title'=>__('Property Photos'))); ?>
                                <?php echo $this->Html->link('<i class="fa fa-file-text-o"></i><span>'.__('Docs').'</span>', '#', array('onclick'=>"show_modal('$propertyActionUrl/document/$propertyId');",'class'=>'wv-action-btn wv-btn-documents','escape'=>false,'title'=>__('Property Documents'))); ?>
                                <?php if(!empty($hasBillingMeter)){ ?>
                                    <?php echo $this->Html->link('<i class="fa fa-bolt"></i><span>'.__('Utility Bill').'</span>', array('plugin'=>false,'admin'=>false,'controller'=>'UtilityBills','action'=>'index'), array('class'=>'wv-action-btn wv-btn-pay','escape'=>false,'title'=>__('View & Pay Utility Bills'))); ?>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; unset($post); ?>
        </div>
    <?php } ?>
</div>

<!-- =========================================================================
     MODAL: Early Payment & Lump-Sum Discount Calculator
     ========================================================================= -->
<div class="modal fade" id="earlyDiscountModal" tabindex="-1" role="dialog" aria-labelledby="earlyDiscountModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" style="border-radius:14px; overflow:hidden; border:none; box-shadow:0 20px 40px rgba(15,23,42,.25);">
            <div class="modal-header" style="background:#0f172a; color:#fff; padding:16px 20px; border-bottom:1px solid #1e293b;">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true" style="color:#fff; opacity:.85; font-size:24px;">&times;</button>
                <h4 class="modal-title" id="earlyDiscountModalLabel" style="font-weight:800; display:flex; align-items:center; gap:8px;">
                    <i class="fa fa-calculator text-warning"></i> <?php echo __('Early Payment & Lump-Sum Discount Calculator'); ?>
                </h4>
            </div>
            <div class="modal-body" style="padding:20px; background:#f8fafc;">
                <div id="earlyDiscountLoader" class="text-center" style="padding:40px 0;">
                    <i class="fa fa-spinner fa-spin fa-3x text-primary"></i>
                    <p style="margin-top:10px; font-weight:600; color:#64748b;"><?php echo __('Loading installment schedule & discount rules...'); ?></p>
                </div>

                <div id="earlyDiscountContent" style="display:none;">
                    <!-- Deal Meta Banner -->
                    <div style="background:#fff; border:1px solid #e2e8f0; border-radius:10px; padding:12px 16px; margin-bottom:14px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
                        <div>
                            <span style="font-size:11px; text-transform:uppercase; color:#64748b; font-weight:700;"><?php echo __('Property / Deal'); ?></span>
                            <h4 id="calcDealTitle" style="margin:2px 0 0; color:#0f172a; font-weight:800;">-</h4>
                        </div>
                        <div class="text-right">
                            <span style="font-size:11px; text-transform:uppercase; color:#64748b; font-weight:700;"><?php echo __('Total Remaining Balance'); ?></span>
                            <h4 id="calcTotalPending" style="margin:2px 0 0; color:#dc2626; font-weight:800;">-</h4>
                        </div>
                    </div>

                    <!-- Overdue Notice Banner (Visible only when overdue installments exist) -->
                    <div id="calcOverdueBanner" style="display:none; background:#fef2f2; border:1px solid #fecaca; border-left:5px solid #dc2626; border-radius:8px; padding:12px 14px; margin-bottom:14px;">
                        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
                            <div>
                                <strong style="color:#991b1b; font-size:13px; display:block;">
                                    <i class="fa fa-exclamation-triangle"></i> <span id="calcOverdueCountText">0 Overdue Installments</span> (<span id="calcOverdueAmountText">Rs. 0</span>)
                                </strong>
                                <span style="font-size:12px; color:#b91c1c;">
                                    <?php echo __('Important: Early payment discounts apply ONLY to upcoming advance installments (0% on overdue). All overdue installments must be selected and cleared.'); ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Dynamic Overdue Selection Warning Toast -->
                    <div id="calcOverdueWarning" style="display:none; background:#fffbeb; border:1px solid #fde68a; border-left:5px solid #f59e0b; border-radius:8px; padding:10px 14px; margin-bottom:14px;">
                        <strong style="color:#92400e; font-size:12px;">
                            <i class="fa fa-warning"></i> <?php echo __('Overdue Clearance Required:'); ?>
                        </strong>
                        <span id="calcOverdueWarningText" style="font-size:12px; color:#b45309;">
                            <?php echo __('You must select all overdue installments to unlock early payment discounts on upcoming installments.'); ?>
                        </span>
                    </div>

                    <!-- Preset Quick Selectors -->
                    <div style="margin-bottom:14px;">
                        <label style="font-size:12px; font-weight:700; color:#334155; text-transform:uppercase; margin-bottom:6px; display:block;">
                            <?php echo __('Settlement Packages & Advance Selectors:'); ?>
                        </label>
                        <div style="display:flex; flex-wrap:wrap; gap:8px;">
                            <button type="button" id="btnOnlyOverdue" class="btn btn-danger btn-sm" style="font-weight:700; border-radius:6px;" onclick="selectOverdueOnly();">
                                <i class="fa fa-exclamation-circle"></i> <?php echo __('Clear All Overdue Only'); ?>
                            </button>
                            <button type="button" class="btn btn-warning btn-sm" style="background:#f59e0b; border-color:#f59e0b; color:#fff; font-weight:700; border-radius:6px;" onclick="selectOverduePlusUpcoming(3);">
                                <i class="fa fa-plus-circle"></i> <?php echo __('Allow Discount (Overdue + Next 3 Months)'); ?>
                            </button>
                            <button type="button" class="btn btn-warning btn-sm" style="background:#d97706; border-color:#d97706; color:#fff; font-weight:700; border-radius:6px;" onclick="selectOverduePlusUpcoming(6);">
                                <i class="fa fa-plus-circle"></i> <?php echo __('Allow Discount (Overdue + Next 6 Months)'); ?>
                            </button>
                            <button type="button" id="btnSpecialOverdueDiscount" class="btn btn-info btn-sm" style="font-weight:700; border-radius:6px; background:#0284c7; border-color:#0284c7; display:none;" onclick="selectSpecialOverduePackage();">
                                <i class="fa fa-gift"></i> <?php echo __('Special Overdue Clearance Package'); ?>
                            </button>
                            <button type="button" class="btn btn-primary btn-sm" style="background:#0f172a; border-color:#0f172a; font-weight:700; border-radius:6px;" onclick="selectFullDealSettlement();">
                                <i class="fa fa-star"></i> <?php echo __('100% Full Deal Settlement'); ?>
                            </button>
                        </div>
                    </div>

                    <!-- Installments Checklist Table (Touch Scrollable) -->
                    <div class="table-responsive" style="max-height:230px; overflow-x:auto; overflow-y:auto; -webkit-overflow-scrolling:touch; touch-action:pan-x pan-y; overscroll-behavior-x:contain; background:#fff; border:1px solid #e2e8f0; border-radius:8px; margin-bottom:14px;">
                        <table class="table table-condensed table-hover" style="margin:0; font-size:13px; min-width:580px;">
                            <thead style="background:#f8fafc; position:sticky; top:0; z-index:2; border-bottom:2px solid #cbd5e1;">
                                <tr>
                                    <th width="40" class="text-center"><input type="checkbox" id="calcSelectAll" onchange="toggleAllInstallmentCheckboxes(this.checked);"></th>
                                    <th><?php echo __('Installment Name'); ?></th>
                                    <th><?php echo __('Due Date / Status'); ?></th>
                                    <th class="text-center"><?php echo __('Discount Eligibility'); ?></th>
                                    <th class="text-right"><?php echo __('Amount Due'); ?></th>
                                </tr>
                            </thead>
                            <tbody id="calcInstallmentsBody">
                            </tbody>
                        </table>
                    </div>

                    <!-- Calculation Live Summary Card -->
                    <div style="background:linear-gradient(135deg,#f0fdf4 0%,#dcfce7 100%); border:2px solid #86efac; border-radius:10px; padding:14px; margin-bottom:12px;">
                        <div class="row">
                            <div class="col-xs-6 col-sm-3 text-center" style="border-right:1px dashed #bbf7d0;">
                                <span style="font-size:11px; font-weight:700; color:#166534; text-transform:uppercase;"><?php echo __('Selected Subtotal'); ?></span>
                                <div id="calcSubtotalDisplay" style="font-size:16px; font-weight:800; color:#14532d; margin-top:2px;">Rs. 0</div>
                                <small id="calcBreakdownNote" style="font-size:10px; color:#15803d; display:block;">-</small>
                            </div>
                            <div class="col-xs-6 col-sm-3 text-center" style="border-right:1px dashed #bbf7d0;">
                                <span style="font-size:11px; font-weight:700; color:#166534; text-transform:uppercase;"><?php echo __('Upcoming Discount Rate'); ?></span>
                                <div id="calcRateDisplay" style="font-size:16px; font-weight:800; color:#15803d; margin-top:2px;">0%</div>
                                <small style="font-size:10px; color:#15803d; display:block;"><?php echo __('(0% on Overdue)'); ?></small>
                            </div>
                            <div class="col-xs-6 col-sm-3 text-center" style="border-right:1px dashed #bbf7d0;">
                                <span style="font-size:11px; font-weight:700; color:#166534; text-transform:uppercase;"><?php echo __('Total Savings'); ?></span>
                                <div id="calcSavingsDisplay" style="font-size:17px; font-weight:900; color:#059669; margin-top:2px;">- Rs. 0</div>
                                <small id="calcSavingsDetail" style="font-size:10px; color:#047857; display:block;">Rs. 0 saved</small>
                            </div>
                            <div class="col-xs-6 col-sm-3 text-center">
                                <span style="font-size:11px; font-weight:700; color:#166534; text-transform:uppercase;"><?php echo __('Net Payable Total'); ?></span>
                                <div id="calcNetPayableDisplay" style="font-size:18px; font-weight:900; color:#047857; margin-top:2px;">Rs. 0</div>
                                <small style="font-size:10px; color:#065f46; display:block; font-weight:700;"><?php echo __('Ready for Online Pay'); ?></small>
                            </div>
                        </div>
                    </div>

                    <!-- Terms Notes -->
                    <div style="background:#fff; border:1px solid #e2e8f0; border-radius:8px; padding:10px 14px; font-size:12px; color:#475569;">
                        <div id="calcTermsEn" style="margin-bottom:4px;"></div>
                        <div id="calcTermsUr" style="direction:rtl; font-family:'Jameel Noori Nastaleeq',serif; font-size:15px; color:#334155;"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="background:#fff; border-top:1px solid #e2e8f0; padding:12px 20px;">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Close'); ?></button>
                <a id="calcSyncIcsBtn" href="#" class="btn btn-info" style="background:#0284c7; border-color:#0284c7; font-weight:700;">
                    <i class="fa fa-calendar"></i> <?php echo __('Download .ICS Calendar'); ?>
                </a>
                <a href="<?php echo $this->Html->url(array('controller'=>'UtilityBills','action'=>'index')); ?>" class="btn btn-success" style="background:#16a34a; border-color:#16a34a; font-weight:700;">
                    <i class="fa fa-credit-card"></i> <?php echo __('Proceed to Pay Online'); ?>
                </a>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
var currentCalcData = null;
var isSpecialOverdueMode = false;

function openEarlyDiscountModal(dealId) {
    $('#earlyDiscountModal').modal('show');
    $('#earlyDiscountLoader').show();
    $('#earlyDiscountContent').hide();
    isSpecialOverdueMode = false;

    $.ajax({
        url: '<?php echo $this->Html->url(array("controller"=>"MyDeals","action"=>"early_discount_data")); ?>/' + dealId,
        type: 'GET',
        dataType: 'json',
        success: function(resp) {
            $('#earlyDiscountLoader').hide();
            if(resp && resp.status === 'success') {
                currentCalcData = resp;
                renderEarlyDiscountModal(resp);
                $('#earlyDiscountContent').show();
            } else {
                alert(resp.message || '<?php echo __("Unable to load discount data.");?>');
                $('#earlyDiscountModal').modal('hide');
            }
        },
        error: function() {
            $('#earlyDiscountLoader').hide();
            alert('<?php echo __("Failed to communicate with server.");?>');
            $('#earlyDiscountModal').modal('hide');
        }
    });
}

function renderEarlyDiscountModal(data) {
    var deal = data.deal;
    var settings = data.settings;
    var installments = data.installments;

    $('#calcDealTitle').text(deal.property_name + ' (' + deal.invoice_no + ')');
    $('#calcTotalPending').text('Rs. ' + Number(deal.total_pending_balance).toLocaleString());
    $('#calcTermsEn').text(settings.terms_en || '');
    $('#calcTermsUr').text(settings.terms_ur || '');
    $('#calcSyncIcsBtn').attr('href', '<?php echo $this->Html->url(array("controller"=>"MyDeals","action"=>"calendar_ics")); ?>/' + deal.id);

    // Overdue banner visibility
    if(deal.total_overdue_count > 0) {
        $('#calcOverdueBanner').show();
        $('#calcOverdueCountText').text(deal.total_overdue_count + ' <?php echo __("Overdue Installment(s)");?>');
        $('#calcOverdueAmountText').text('Rs. ' + Number(deal.total_overdue_amount).toLocaleString());
        $('#btnOnlyOverdue').show();
        if(settings.allow_overdue_discount_package && parseFloat(settings.overdue_special_discount_percent) > 0) {
            $('#btnSpecialOverdueDiscount').show();
        } else {
            $('#btnSpecialOverdueDiscount').hide();
        }
    } else {
        $('#calcOverdueBanner').hide();
        $('#btnOnlyOverdue').hide();
        $('#btnSpecialOverdueDiscount').hide();
    }

    var html = '';
    for(var i = 0; i < installments.length; i++) {
        var row = installments[i];
        var isOverdue = row.is_overdue;
        var rowBg = isOverdue ? 'background:#fff1f2;' : '';
        var statusBadge = isOverdue 
            ? '<span class="badge" style="background:#dc2626; font-size:10px; padding:3px 6px;"><?php echo __("OVERDUE");?> (' + row.overdue_days + 'd)</span>'
            : '<span class="badge" style="background:#2563eb; font-size:10px; padding:3px 6px;"><?php echo __("UPCOMING");?></span>';
        var discTag = isOverdue
            ? '<span style="color:#dc2626; font-weight:700; font-size:11px;"><i class="fa fa-times-circle"></i> 0% <?php echo __("No Discount");?></span>'
            : '<span style="color:#16a34a; font-weight:700; font-size:11px;"><i class="fa fa-check-circle"></i> <?php echo __("Eligible");?></span>';

        html += '<tr style="cursor:pointer; ' + rowBg + '" onclick="toggleRowCheckbox(' + i + ', event);">' +
            '<td class="text-center"><input type="checkbox" class="calc-inst-checkbox" data-index="' + i + '" data-amount="' + row.pending_amount + '" data-overdue="' + (isOverdue ? 1 : 0) + '" data-months="' + row.months_ahead + '" onchange="recalculateDiscount();"></td>' +
            '<td><strong>' + escapeHtml(row.name) + '</strong></td>' +
            '<td>' + escapeHtml(row.due_date_formatted) + ' &nbsp;' + statusBadge + '</td>' +
            '<td class="text-center">' + discTag + '</td>' +
            '<td class="text-right" style="font-weight:700; color:' + (isOverdue ? '#dc2626' : '#0f172a') + ';">Rs. ' + Number(row.pending_amount).toLocaleString() + '</td>' +
        '</tr>';
    }
    $('#calcInstallmentsBody').html(html);

    // If there are overdue installments, default select ALL overdue + next upcoming
    if(deal.total_overdue_count > 0) {
        selectOverduePlusUpcoming(3);
    } else {
        var defaultSelectCount = Math.min(installments.length, Math.max(3, settings.min_installments || 3));
        selectUpcomingOnly(defaultSelectCount);
    }
}

function escapeHtml(text) {
    return $('<div>').text(text).html();
}

function toggleRowCheckbox(index, evt) {
    if(evt.target.tagName.toLowerCase() === 'input') {
        return;
    }
    var $cb = $('.calc-inst-checkbox[data-index="' + index + '"]');
    $cb.prop('checked', !$cb.prop('checked'));
    recalculateDiscount();
}

function toggleAllInstallmentCheckboxes(checked) {
    isSpecialOverdueMode = false;
    $('.calc-inst-checkbox').prop('checked', checked);
    recalculateDiscount();
}

function selectOverdueOnly() {
    isSpecialOverdueMode = false;
    $('.calc-inst-checkbox').prop('checked', false);
    $('.calc-inst-checkbox[data-overdue="1"]').prop('checked', true);
    recalculateDiscount();
}

function selectUpcomingOnly(count) {
    isSpecialOverdueMode = false;
    $('.calc-inst-checkbox').prop('checked', false);
    var $upcoming = $('.calc-inst-checkbox[data-overdue="0"]');
    var max = (count === 'all') ? $upcoming.length : Math.min($upcoming.length, parseInt(count));
    for(var i = 0; i < max; i++) {
        $($upcoming[i]).prop('checked', true);
    }
    recalculateDiscount();
}

function selectOverduePlusUpcoming(upcomingCount) {
    isSpecialOverdueMode = false;
    $('.calc-inst-checkbox').prop('checked', false);
    // Select ALL overdue
    $('.calc-inst-checkbox[data-overdue="1"]').prop('checked', true);
    // Select next upcoming
    var $upcoming = $('.calc-inst-checkbox[data-overdue="0"]');
    var max = Math.min($upcoming.length, parseInt(upcomingCount));
    for(var i = 0; i < max; i++) {
        $($upcoming[i]).prop('checked', true);
    }
    recalculateDiscount();
}

function selectFullDealSettlement() {
    isSpecialOverdueMode = false;
    $('.calc-inst-checkbox').prop('checked', true);
    recalculateDiscount();
}

function selectSpecialOverduePackage() {
    isSpecialOverdueMode = true;
    $('.calc-inst-checkbox').prop('checked', false);
    $('.calc-inst-checkbox[data-overdue="1"]').prop('checked', true);
    recalculateDiscount();
}

function recalculateDiscount() {
    if(!currentCalcData) return;
    var deal = currentCalcData.deal;
    var settings = currentCalcData.settings;

    var totalOverdueInList = deal.total_overdue_count || 0;
    var selectedOverdueCount = 0;
    var selectedOverdueAmount = 0;

    var selectedUpcomingCount = 0;
    var selectedUpcomingAmount = 0;
    var upcomingDiscount = 0;

    $('.calc-inst-checkbox:checked').each(function() {
        var isOverdue = parseInt($(this).data('overdue')) === 1;
        var amt = parseFloat($(this).data('amount')) || 0;
        var monthsAhead = parseInt($(this).data('months')) || 0;

        if(isOverdue) {
            selectedOverdueCount++;
            selectedOverdueAmount += amt;
        } else {
            selectedUpcomingCount++;
            selectedUpcomingAmount += amt;

            if(settings.discount_mode === 'annual_rate') {
                var annualRate = parseFloat(settings.annual_rate) || 8.0;
                var disc = amt * (annualRate / 100.0) * (monthsAhead / 12.0);
                upcomingDiscount += Math.max(0, disc);
            } else {
                var flatPercent = parseFloat(settings.discount_percent) || 5.0;
                var disc = amt * (flatPercent / 100.0);
                upcomingDiscount += Math.max(0, disc);
            }
        }
    });

    // Check overdue clearance condition:
    // If client selected ANY upcoming installment while missing ANY overdue installment
    var hasMissingOverdue = (totalOverdueInList > 0 && selectedOverdueCount < totalOverdueInList);
    var requireAllOverdue = parseInt(settings.require_all_overdue) === 1;

    if(hasMissingOverdue && selectedUpcomingCount > 0 && requireAllOverdue) {
        var missingCount = totalOverdueInList - selectedOverdueCount;
        $('#calcOverdueWarning').show();
        $('#calcOverdueWarningText').html('<strong>' + missingCount + ' <?php echo __("overdue installment(s) are unchecked.");?></strong> <?php echo __("You must select all overdue installments (0% discount) to qualify for early payment discounts on upcoming installments.");?>');
        upcomingDiscount = 0;
    } else {
        $('#calcOverdueWarning').hide();
    }

    // Special Overdue Discount / Surcharge Waiver Mode:
    var overdueDiscount = 0;
    if(isSpecialOverdueMode && selectedOverdueCount === totalOverdueInList && totalOverdueInList > 0) {
        var specialRate = parseFloat(settings.overdue_special_discount_percent) || 0;
        if(specialRate > 0) {
            overdueDiscount = selectedOverdueAmount * (specialRate / 100.0);
        }
    }

    // Check min upcoming installments threshold for regular discount
    var minRequired = parseInt(settings.min_installments) || 1;
    var effectiveUpcomingDiscount = 0;
    var effectiveRateLabel = '0%';

    if(selectedUpcomingCount >= minRequired && selectedUpcomingAmount > 0 && (!hasMissingOverdue || !requireAllOverdue)) {
        effectiveUpcomingDiscount = Math.round(upcomingDiscount);
        var effectivePercent = ((effectiveUpcomingDiscount / selectedUpcomingAmount) * 100).toFixed(1);
        effectiveRateLabel = effectivePercent + '%';
    } else if(selectedUpcomingCount > 0) {
        if(hasMissingOverdue && requireAllOverdue) {
            effectiveRateLabel = '<span style="font-size:11px; color:#dc2626; font-weight:700;"><?php echo __("Locked (Clear Overdue First)");?></span>';
        } else {
            effectiveRateLabel = '<span style="font-size:11px; color:#d97706; font-weight:700;">(Min ' + minRequired + ' <?php echo __("Required");?>)</span>';
        }
    }

    var totalSelected = selectedOverdueAmount + selectedUpcomingAmount;
    var totalEffectiveDiscount = effectiveUpcomingDiscount + Math.round(overdueDiscount);
    var netPayable = Math.max(0, Math.round(totalSelected - totalEffectiveDiscount));

    $('#calcSubtotalDisplay').text('Rs. ' + totalSelected.toLocaleString());
    $('#calcBreakdownNote').text('Overdue: Rs. ' + selectedOverdueAmount.toLocaleString() + ' | Upcoming: Rs. ' + selectedUpcomingAmount.toLocaleString());
    $('#calcRateDisplay').html(effectiveRateLabel);
    $('#calcSavingsDisplay').text('- Rs. ' + totalEffectiveDiscount.toLocaleString());
    $('#calcSavingsDetail').text('Rs. ' + totalEffectiveDiscount.toLocaleString() + ' <?php echo __("net total savings");?>');
    $('#calcNetPayableDisplay').text('Rs. ' + netPayable.toLocaleString());

    var totalCbs = $('.calc-inst-checkbox').length;
    var totalChecked = $('.calc-inst-checkbox:checked').length;
    $('#calcSelectAll').prop('checked', totalCbs > 0 && totalChecked === totalCbs);
}
</script>
