<style type="text/css">
/* ==========================================================================
   BALLOTING RESULTS & ALLOTMENT PORTAL - PROFESSIONAL LIGHT THEME
   ========================================================================== */
.wv-ballot-page {
    background: #f8fafc;
    margin: -10px -5px 0;
    padding: 18px;
    min-height: 88vh;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    color: #0f172a;
    -webkit-overflow-scrolling: touch;
    overflow-x: auto;
}

/* Page Header / Executive Hero Card */
.wv-ballot-hero {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-top: 4px solid #0284c7;
    border-radius: 16px;
    padding: 24px 28px;
    margin-bottom: 24px;
    box-shadow: 0 4px 20px rgba(15, 23, 42, 0.05);
    position: relative;
    overflow: hidden;
}
.wv-ballot-hero:after {
    content: "";
    position: absolute;
    right: -60px;
    top: -60px;
    width: 220px;
    height: 220px;
    background: radial-gradient(circle, rgba(2, 132, 199, 0.08) 0%, rgba(255, 255, 255, 0) 70%);
    pointer-events: none;
}
.wv-ballot-hero-inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 20px;
    position: relative;
    z-index: 2;
}
.wv-ballot-hero-content {
    flex: 1 1 540px;
}
.wv-hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #e0f2fe;
    color: #0369a1;
    border: 1px solid #bae6fd;
    font-size: 11px;
    font-weight: 800;
    padding: 4px 12px;
    border-radius: 999px;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    margin-bottom: 10px;
}
.wv-ballot-hero h1 {
    font-size: 25px;
    font-weight: 900;
    margin: 0 0 8px;
    color: #0f172a !important;
    display: flex;
    align-items: center;
    gap: 12px;
    letter-spacing: -0.3px;
}
.wv-ballot-hero h1 i {
    color: #0284c7;
}
.wv-ballot-hero p {
    font-size: 13.5px;
    color: #475569;
    margin: 0;
    max-width: 700px;
    line-height: 1.55;
    font-weight: 600;
}
.wv-ballot-hero-pills {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 14px;
}
.wv-hero-pill {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 999px;
    padding: 4px 12px;
    font-size: 11.5px;
    font-weight: 800;
    color: #334155;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.wv-ballot-hero-metrics {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}
.wv-hero-metric-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 14px 18px;
    min-width: 135px;
    text-align: center;
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
    transition: all 0.2s ease;
}
.wv-hero-metric-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 14px rgba(15, 23, 42, 0.08);
}
.wv-hero-metric-card .lbl {
    display: block;
    font-size: 10.5px;
    font-weight: 800;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 3px;
}
.wv-hero-metric-card .val {
    display: block;
    font-size: 26px;
    font-weight: 900;
    line-height: 1.1;
    color: #0f172a;
}
.wv-hero-metric-card.metric-allotted .val {
    color: #059669;
}
.wv-hero-metric-card.metric-queued .val {
    color: #0284c7;
}
.wv-hero-metric-card .sub {
    display: block;
    font-size: 10.5px;
    font-weight: 700;
    color: #64748b;
    margin-top: 3px;
}
.wv-announcement-box {
    background: #fffbeb;
    border: 1px solid #fef3c7;
    border-left: 4px solid #f59e0b;
    border-radius: 10px;
    padding: 12px 16px;
    margin-top: 18px;
    font-size: 13.5px;
    color: #92400e;
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 600;
}

/* Search Card */
.wv-search-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 24px 26px;
    margin-bottom: 28px;
    box-shadow: 0 4px 18px rgba(15, 23, 42, 0.04);
}
.wv-search-title {
    font-size: 16px;
    font-weight: 800;
    color: #0f172a;
    margin: 0 0 8px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.wv-search-input-group {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin-top: 14px;
}
.wv-search-input {
    flex: 1 1 280px;
    height: 48px;
    border-radius: 10px;
    border: 1.5px solid #cbd5e1;
    background: #ffffff;
    padding: 0 16px;
    font-size: 14px;
    font-weight: 600;
    color: #0f172a;
    transition: all 0.2s ease;
}
.wv-search-input:focus {
    border-color: #0284c7;
    outline: none;
    box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15);
}
.wv-search-btn {
    background: #0f172a;
    color: #ffffff !important;
    border: none;
    border-radius: 10px;
    padding: 0 24px;
    height: 48px;
    font-size: 14px;
    font-weight: 800;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.15);
    transition: all 0.2s ease;
}
.wv-search-btn:hover {
    background: #0284c7;
    transform: translateY(-1px);
    box-shadow: 0 6px 18px rgba(2, 132, 199, 0.25);
}

/* Search Result Boxes */
.wv-search-result-box {
    margin-top: 20px;
    padding: 20px 22px;
    border-radius: 14px;
    box-shadow: 0 2px 10px rgba(15, 23, 42, 0.04);
}
.wv-result-allotted {
    background: #ecfdf5;
    border: 1.5px solid #a7f3d0;
}
.wv-result-pending {
    background: #eff6ff;
    border: 1.5px solid #bfdbfe;
}
.wv-result-none {
    background: #fff1f2;
    border: 1.5px solid #fecdd3;
}

/* Balloted Cards Grid & Professional Card Design */
.wv-section-heading {
    font-size: 18px;
    font-weight: 900;
    color: #0f172a;
    margin: 28px 0 16px;
    display: flex;
    align-items: center;
    gap: 10px;
}
.wv-allotment-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
    gap: 24px;
    margin-bottom: 32px;
}
.wv-allotment-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    box-shadow: 0 6px 20px rgba(15, 23, 42, 0.05);
    overflow: hidden;
    position: relative;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}
.wv-allotment-card:hover {
    box-shadow: 0 14px 34px rgba(15, 23, 42, 0.1);
    transform: translateY(-3px);
    border-color: #cbd5e1;
}
.wv-allotment-header {
    background: #f8fafc;
    border-bottom: 1px solid #f1f5f9;
    padding: 16px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}
.wv-allotment-title {
    font-size: 15px;
    font-weight: 800;
    margin: 0;
    color: #0f172a !important;
    display: flex;
    align-items: center;
    gap: 8px;
}
.wv-allotment-badge {
    background: #ecfdf5;
    color: #059669;
    border: 1px solid #a7f3d0;
    font-size: 11px;
    font-weight: 900;
    padding: 4px 10px;
    border-radius: 999px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    white-space: nowrap;
}
.wv-allotment-body {
    padding: 20px;
    flex: 1 1 auto;
}

/* Plot Highlight Box */
.wv-allotment-highlight {
    background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
    border: 1.5px dashed #86efac;
    border-radius: 14px;
    padding: 16px 18px;
    text-align: center;
    margin-bottom: 18px;
}
.wv-plot-label {
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    color: #059669;
    margin-bottom: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
}
.wv-plot-value {
    font-size: 28px;
    font-weight: 900;
    color: #064e3b;
    line-height: 1.2;
    letter-spacing: -0.5px;
}
.wv-plot-subline {
    font-size: 12.5px;
    color: #047857;
    font-weight: 700;
    margin-top: 4px;
}

/* Specification Rows */
.wv-spec-table {
    margin-bottom: 18px;
}
.wv-spec-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 9px 0;
    border-bottom: 1px solid #f1f5f9;
    font-size: 13px;
}
.wv-spec-row:last-child {
    border-bottom: none;
}
.wv-spec-label {
    color: #64748b;
    font-weight: 600;
}
.wv-spec-val {
    color: #0f172a;
    font-weight: 800;
    text-align: right;
}

/* Card Action Buttons */
.wv-card-actions {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-top: 18px;
}
.wv-btn-print-letter {
    background: #0f172a;
    color: #ffffff !important;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    text-align: center;
    border-radius: 10px;
    padding: 11px 16px;
    font-size: 13px;
    font-weight: 800;
    text-decoration: none !important;
    transition: all 0.2s ease;
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.15);
    border: none;
}
.wv-btn-print-letter:hover {
    background: #0284c7;
    color: #ffffff !important;
    transform: translateY(-1px);
    box-shadow: 0 6px 18px rgba(2, 132, 199, 0.25);
}
.wv-btn-cert-qr {
    background: #ffffff;
    color: #059669 !important;
    border: 1.5px solid #10b981;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    text-align: center;
    border-radius: 10px;
    padding: 10px 16px;
    font-size: 13px;
    font-weight: 800;
    text-decoration: none !important;
    transition: all 0.2s ease;
    box-shadow: 0 2px 8px rgba(16, 185, 129, 0.08);
}
.wv-btn-cert-qr:hover {
    background: #ecfdf5;
    border-color: #059669;
    color: #047857 !important;
    transform: translateY(-1px);
}
.wv-card-stamp-note {
    font-size: 11.5px;
    color: #64748b;
    text-align: center;
    margin-top: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    font-weight: 600;
}

/* Queued Bookings Cards - Professional Redesign */
.wv-queued-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-top: 3.5px solid #0284c7;
    border-radius: 14px;
    padding: 18px 20px;
    box-shadow: 0 4px 16px rgba(15, 23, 42, 0.04);
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: all 0.22s ease;
}
.wv-queued-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 28px rgba(15, 23, 42, 0.09);
    border-color: #cbd5e1;
}
.wv-queued-card-head {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 12px;
    padding-bottom: 12px;
    border-bottom: 1px solid #f1f5f9;
}
.wv-queued-title-wrap {
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 0;
}
.wv-queued-icon {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    background: #e0f2fe;
    border: 1px solid #bae6fd;
    color: #0284c7;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    flex-shrink: 0;
}
.wv-queued-proj {
    display: block;
    font-size: 10.5px;
    font-weight: 800;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    line-height: 1.2;
    margin-bottom: 2px;
}
.wv-queued-name {
    margin: 0;
    font-size: 15.5px;
    font-weight: 900;
    color: #0f172a;
    line-height: 1.25;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.wv-queued-badge {
    background: #eff6ff;
    color: #0284c7;
    border: 1px solid #bfdbfe;
    font-size: 10px;
    font-weight: 900;
    padding: 4px 10px;
    border-radius: 999px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    white-space: nowrap;
    flex-shrink: 0;
}
.wv-pulse-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #0284c7;
    display: inline-block;
    box-shadow: 0 0 0 2px rgba(2, 132, 199, 0.2);
}
.wv-queued-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px 10px;
    margin-bottom: 12px;
}
.wv-queued-box {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 8px 10px;
}
.wv-queued-box .lbl {
    display: block;
    font-size: 10px;
    font-weight: 800;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    margin-bottom: 3px;
}
.wv-queued-box .lbl i {
    color: #0284c7;
    margin-right: 3px;
}
.wv-queued-box .val {
    display: block;
    font-size: 12px;
    font-weight: 800;
    color: #0f172a;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.wv-queued-strip {
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    border-radius: 8px;
    padding: 7px 10px;
    font-size: 11px;
    font-weight: 700;
    color: #166534;
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 12px;
}
.wv-queued-actions {
    display: flex;
    align-items: center;
    gap: 8px;
    padding-top: 10px;
    border-top: 1px solid #f1f5f9;
}
.wv-qbtn {
    border: 1px solid #cbd5e1 !important;
    background: #ffffff !important;
    color: #334155 !important;
    font-size: 11px !important;
    font-weight: 800 !important;
    border-radius: 6px !important;
    padding: 5px 12px !important;
    display: inline-flex !important;
    align-items: center;
    gap: 4px;
    transition: all 0.15s ease !important;
    text-decoration: none !important;
}
.wv-qbtn:hover {
    background: #f8fafc !important;
    color: #0284c7 !important;
    border-color: #0284c7 !important;
}

/* Responsive Breakpoints */
@media (max-width: 991px) {
    .wv-ballot-hero-inner {
        flex-direction: column;
        align-items: flex-start;
    }
    .wv-ballot-hero-metrics {
        width: 100%;
        display: grid;
        grid-template-columns: 1fr 1fr;
    }
}
@media (max-width: 768px) {
    .wv-ballot-page {
        padding: 12px;
    }
    .wv-ballot-hero {
        padding: 20px 18px;
    }
    .wv-ballot-hero h1 {
        font-size: 20px;
    }
    .wv-search-card {
        padding: 18px;
    }
    .wv-allotment-grid {
        grid-template-columns: 1fr;
        gap: 16px;
    }
    .wv-plot-value {
        font-size: 24px;
    }
    .wv-queued-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="wv-ballot-page">
    <?php echo $this->Session->flash(); ?>

    <!-- Executive Header Card -->
    <div class="wv-ballot-hero">
        <div class="wv-ballot-hero-inner">
            <div class="wv-ballot-hero-content">
                <span class="wv-hero-badge"><i class="fa fa-shield"></i> <?php echo __('Official Verification Portal'); ?></span>
                <h1><i class="fa fa-cube text-primary"></i> <?php echo __('e-Balloting Results & Digital Plot Allocation'); ?></h1>
                <p><?php echo __('Official digital allocation portal for White Villas. Verify your allotted plot coordinates, block and street dimensions, and download or print your official allotment letters with duplicate copy watermark and QR authentication.'); ?></p>
                <div class="wv-ballot-hero-pills">
                    <span class="wv-hero-pill"><i class="fa fa-check-circle text-success"></i> <?php echo __('Verified Allocation Database'); ?></span>
                    <span class="wv-hero-pill"><i class="fa fa-qrcode text-info"></i> <?php echo __('QR Code Secured Letters'); ?></span>
                    <span class="wv-hero-pill"><i class="fa fa-shield text-warning"></i> <?php echo __('Watermark Authenticated'); ?></span>
                </div>
            </div>
            <div class="wv-ballot-hero-metrics">
                <div class="wv-hero-metric-card metric-allotted">
                    <span class="lbl"><?php echo __('Allotted Units'); ?></span>
                    <strong class="val"><?php echo count($allotments); ?></strong>
                    <small class="sub"><i class="fa fa-check text-success"></i> <?php echo count($allotments) > 0 ? __('Plot Assigned') : __('Awaiting Draw'); ?></small>
                </div>
                <div class="wv-hero-metric-card metric-queued">
                    <span class="lbl"><?php echo __('Queued Deals'); ?></span>
                    <strong class="val"><?php echo count($clientDeals); ?></strong>
                    <small class="sub"><i class="fa fa-hourglass-half text-primary"></i> <?php echo __('In Next Balloting'); ?></small>
                </div>
            </div>
        </div>

        <?php if(!empty($announcements)): ?>
        <?php foreach($announcements as $ann): ?>
        <div class="wv-announcement-box">
            <i class="fa fa-bullhorn"></i>
            <span><?php echo h($ann); ?></span>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Live Allotment Search Bar -->
    <div class="wv-search-card">
        <h3 class="wv-search-title"><i class="fa fa-search text-primary"></i> <?php echo __('Live Balloting Allotment Status Search'); ?></h3>
        <p style="font-size:13px; color:#64748b; margin:0 0 12px;"><?php echo __('Enter your Deal / Invoice Number, Registration No, or 13-digit CNIC to check your real-time balloting allotment status:'); ?></p>
        <form method="get" action="<?php echo $this->Html->url(array('action' => 'index')); ?>">
            <div class="wv-search-input-group">
                <input type="text" name="q" class="wv-search-input" placeholder="<?php echo __('e.g. WV-L/SC/2025-3033, 38101-XXXXXXX-X, or Plot No'); ?>" value="<?php echo h($searchQuery); ?>" required="required">
                <button type="submit" class="wv-search-btn">
                    <i class="fa fa-search"></i> <?php echo __('Check Balloting Result'); ?>
                </button>
            </div>
        </form>

        <!-- Search Results Box -->
        <?php if($searchPerformed && !empty($searchResult)): ?>
            <?php if($searchResult['status'] === 'ALLOTTED'): ?>
            <div class="wv-search-result-box wv-result-allotted">
                <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:14px; margin-bottom:14px;">
                    <div>
                        <h4 style="margin:0 0 4px; font-weight:900; color:#065f46; font-size:17px;">
                            <i class="fa fa-check-circle"></i> <?php echo __('Plot Allotted Successfully!'); ?>
                        </h4>
                        <p style="margin:0; font-size:13px; color:#047857;">
                            <?php echo __('Allottee:'); ?> <strong><?php echo h($searchResult['client_name']); ?></strong> | <?php echo __('Project:'); ?> <strong><?php echo h($searchResult['project_name']); ?></strong>
                        </p>
                    </div>
                    <div style="display:flex; gap:8px; flex-wrap:wrap;">
                        <?php if(!empty($searchResult['allotment_letter_url'])): ?>
                        <a href="<?php echo h($searchResult['allotment_letter_url']); ?>" target="_blank" class="btn" style="background:#0f172a; color:#ffffff !important; font-weight:800; border-radius:8px; padding:8px 16px; font-size:13px;">
                            <i class="fa fa-print"></i> <?php echo __('Print Allotment Letter (Duplicate)'); ?>
                        </a>
                        <?php endif; ?>
                        <a href="<?php echo h($searchResult['certificate_url']); ?>" target="_blank" class="btn" style="background:#059669; color:#ffffff !important; font-weight:800; border-radius:8px; padding:8px 16px; font-size:13px;">
                            <i class="fa fa-qrcode"></i> <?php echo __('Download QR Certificate'); ?>
                        </a>
                    </div>
                </div>
                <div class="row" style="background:#ffffff; border:1px solid #a7f3d0; border-radius:10px; padding:12px; margin:0;">
                    <div class="col-sm-3 col-xs-6" style="padding:6px 10px;">
                        <span style="font-size:11px; text-transform:uppercase; color:#64748b; font-weight:700; display:block;"><?php echo __('Plot / Unit'); ?></span>
                        <strong style="font-size:15px; color:#065f46;"><?php echo h($searchResult['property_no']); ?></strong>
                    </div>
                    <div class="col-sm-3 col-xs-6" style="padding:6px 10px;">
                        <span style="font-size:11px; text-transform:uppercase; color:#64748b; font-weight:700; display:block;"><?php echo __('Block / Sector'); ?></span>
                        <strong style="font-size:15px; color:#0f172a;"><?php echo h($searchResult['block_name']); ?></strong>
                    </div>
                    <div class="col-sm-3 col-xs-6" style="padding:6px 10px;">
                        <span style="font-size:11px; text-transform:uppercase; color:#64748b; font-weight:700; display:block;"><?php echo __('Street No'); ?></span>
                        <strong style="font-size:15px; color:#0f172a;"><?php echo h($searchResult['street_no']); ?></strong>
                    </div>
                    <div class="col-sm-3 col-xs-6" style="padding:6px 10px;">
                        <span style="font-size:11px; text-transform:uppercase; color:#64748b; font-weight:700; display:block;"><?php echo __('Size / Area'); ?></span>
                        <strong style="font-size:15px; color:#0f172a;"><?php echo h($searchResult['unit_area']); ?></strong>
                    </div>
                </div>
            </div>
            <?php elseif($searchResult['status'] === 'PENDING_BALLOTING'): ?>
            <div class="wv-search-result-box wv-result-pending">
                <h4 style="margin:0 0 6px; font-weight:800; color:#1e40af; font-size:16px;">
                    <i class="fa fa-info-circle"></i> <?php echo __('Deal Verified - Queued for Next Balloting'); ?>
                </h4>
                <p style="margin:0; font-size:13.5px; color:#1d4ed8; line-height:1.5;"><?php echo h($searchResult['message']); ?></p>
            </div>
            <?php else: ?>
            <div class="wv-search-result-box wv-result-none">
                <h4 style="margin:0 0 6px; font-weight:800; color:#9f1239; font-size:16px;">
                    <i class="fa fa-exclamation-circle"></i> <?php echo __('No Allotment Record Found'); ?>
                </h4>
                <p style="margin:0; font-size:13.5px; color:#be123c; line-height:1.5;"><?php echo h($searchResult['message']); ?></p>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- Client's Allotted Units Section (Logged in View) -->
    <?php if(!empty($clientId)): ?>
    <h3 class="wv-section-heading">
        <i class="fa fa-key text-warning"></i> <?php echo __('My Allotted Properties & Plots'); ?>
    </h3>

    <?php if(!empty($allotments)): ?>
    <div class="wv-allotment-grid">
        <?php foreach($allotments as $a):
            $allot = $a['BallotingAllotment'];
            $projName = !empty($a['Project']['name']) ? $a['Project']['name'] : 'White Villas';
            $propNo = !empty($allot['property_no']) ? $allot['property_no'] : (!empty($allot['allotment_no']) ? 'Unit #' . $allot['allotment_no'] : 'Allotted Unit');
            $block = !empty($allot['block_name']) ? $allot['block_name'] : 'Main Block';
            $street = !empty($allot['street_no']) ? $allot['street_no'] : 'Main';
            $size = $allot['unit_area'] . ' ' . (!empty($allot['area_unit']) ? $allot['area_unit'] : 'Marla');
            $category = !empty($allot['permanent_type']) ? $allot['permanent_type'] : 'Standard';
            $allotDate = !empty($allot['allotment_date']) ? date('d-M-Y', strtotime($allot['allotment_date'])) : date('d-M-Y');
            $allotRef = !empty($allot['allotment_no']) ? ('WV-ALLOT-' . $allot['allotment_no']) : ('WV-' . $allot['id']);
        ?>
        <div class="wv-allotment-card">
            <div>
                <div class="wv-allotment-header">
                    <h4 class="wv-allotment-title">
                        <i class="fa fa-building-o text-primary"></i> <?php echo h($projName); ?>
                    </h4>
                    <span class="wv-allotment-badge"><i class="fa fa-check-circle"></i> <?php echo __('Allotted'); ?></span>
                </div>
                <div class="wv-allotment-body">
                    <!-- Plot Highlight Box -->
                    <div class="wv-allotment-highlight">
                        <div class="wv-plot-label">
                            <i class="fa fa-map-marker"></i> <?php echo __('Officially Allotted Plot / Unit'); ?>
                        </div>
                        <div class="wv-plot-value"><?php echo h($propNo); ?></div>
                        <div class="wv-plot-subline"><?php echo h($block); ?> &bull; <?php echo h($size); ?></div>
                    </div>

                    <!-- Specification Rows -->
                    <div class="wv-spec-table">
                        <div class="wv-spec-row">
                            <span class="wv-spec-label"><?php echo __('Block / Sector:'); ?></span>
                            <span class="wv-spec-val"><?php echo h($block); ?></span>
                        </div>
                        <div class="wv-spec-row">
                            <span class="wv-spec-label"><?php echo __('Street:'); ?></span>
                            <span class="wv-spec-val"><?php echo h($street); ?></span>
                        </div>
                        <div class="wv-spec-row">
                            <span class="wv-spec-label"><?php echo __('Plot Size:'); ?></span>
                            <span class="wv-spec-val"><?php echo h($size); ?></span>
                        </div>
                        <div class="wv-spec-row">
                            <span class="wv-spec-label"><?php echo __('Category / Type:'); ?></span>
                            <span class="wv-spec-val"><span class="badge" style="background:#0f172a; font-weight:700;"><?php echo h($category); ?></span></span>
                        </div>
                        <div class="wv-spec-row">
                            <span class="wv-spec-label"><?php echo __('Allotment Ref:'); ?></span>
                            <span class="wv-spec-val" style="font-family:monospace; font-size:13px; color:#0284c7;"><?php echo h($allotRef); ?></span>
                        </div>
                        <div class="wv-spec-row">
                            <span class="wv-spec-label"><?php echo __('Allotment Date:'); ?></span>
                            <span class="wv-spec-val"><?php echo h($allotDate); ?></span>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="wv-card-actions">
                        <a href="<?php echo $this->Html->url(array('action' => 'allotmentletter', $allot['id'])); ?>" target="_blank" class="wv-btn-print-letter">
                            <i class="fa fa-print"></i> <?php echo __('Print Allotment Letter (Duplicate)'); ?>
                        </a>
                        <a href="<?php echo $this->Html->url(array('action' => 'certificate', $allot['id'])); ?>" target="_blank" class="wv-btn-cert-qr">
                            <i class="fa fa-qrcode"></i> <?php echo __('Download QR Certificate'); ?>
                        </a>
                    </div>

                    <div class="wv-card-stamp-note">
                        <i class="fa fa-shield text-success"></i> <?php echo __('Original backend allotment letter issued with Duplicate watermark'); ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div style="background:#ffffff; border:1px solid #e2e8f0; border-radius:16px; padding:36px 20px; text-align:center; color:#64748b; margin-bottom:28px; box-shadow:0 4px 14px rgba(15,23,42,0.04);">
        <i class="fa fa-cube" style="font-size:42px; color:#cbd5e1; margin-bottom:12px; display:inline-block;"></i>
        <h4 style="font-weight:900; color:#0f172a; margin-bottom:6px; font-size:17px;"><?php echo __('No Allotments Recorded Yet'); ?></h4>
        <p style="margin:0 auto; max-width:540px; font-size:13.5px; color:#64748b; line-height:1.5;"><?php echo __('Your bookings are queued for upcoming balloting runs. Ensure your installments are up to date to maintain top balloting priority.'); ?></p>
    </div>
    <?php endif; ?>

    <!-- Other Deals Pending Balloting -->
    <?php if(!empty($clientDeals)): ?>
    <h3 class="wv-section-heading" style="font-size:17px; margin-top:32px;">
        <i class="fa fa-hourglass-half text-primary"></i> <?php echo __('Other Bookings Queued for Next Balloting'); ?>
        <span class="badge" style="background:#e0f2fe; color:#0284c7; font-size:11px; font-weight:800; border-radius:999px; padding:3px 10px; margin-left:6px;"><?php echo count($clientDeals); ?> <?php echo __('Bookings'); ?></span>
    </h3>
    <div class="row">
        <?php foreach($clientDeals as $cd):
            $d = $cd['MyDeal'];
            $dealId = (int)$d['id'];
            $pName = !empty($cd['Property']['name']) ? $cd['Property']['name'] : 'Plot/Villa';
            $projName = !empty($cd['Project']['name']) ? $cd['Project']['name'] : 'White Villas';
            $pType = !empty($cd['Property']['type']) ? $cd['Property']['type'] : (!empty($cd['Property']['property_type']) ? $cd['Property']['property_type'] : __('Standard Unit'));
            $invoice = !empty($d['invoice_no']) ? $d['invoice_no'] : ('#' . $dealId);
            $bookingDate = !empty($d['date']) ? date('d-M-Y', strtotime($d['date'])) : '-';
        ?>
        <div class="col-md-4 col-sm-6" style="margin-bottom:20px;">
            <div class="wv-queued-card">
                <!-- Card Header -->
                <div class="wv-queued-card-head">
                    <div class="wv-queued-title-wrap">
                        <div class="wv-queued-icon">
                            <i class="fa fa-building-o"></i>
                        </div>
                        <div style="min-width:0;">
                            <span class="wv-queued-proj"><?php echo h($projName); ?></span>
                            <h4 class="wv-queued-name" title="<?php echo h($pName); ?>"><?php echo h($pName); ?></h4>
                        </div>
                    </div>
                    <span class="wv-queued-badge">
                        <span class="wv-pulse-dot"></span> <?php echo __('QUEUED'); ?>
                    </span>
                </div>

                <!-- 2x2 Key Data Grid -->
                <div class="wv-queued-grid">
                    <div class="wv-queued-box">
                        <span class="lbl"><i class="fa fa-file-text-o"></i> <?php echo __('Invoice No'); ?></span>
                        <strong class="val"><?php echo h($invoice); ?></strong>
                    </div>
                    <div class="wv-queued-box">
                        <span class="lbl"><i class="fa fa-calendar"></i> <?php echo __('Booking Date'); ?></span>
                        <strong class="val"><?php echo h($bookingDate); ?></strong>
                    </div>
                    <div class="wv-queued-box">
                        <span class="lbl"><i class="fa fa-tag"></i> <?php echo __('Category / Type'); ?></span>
                        <strong class="val"><?php echo h($pType); ?></strong>
                    </div>
                    <div class="wv-queued-box">
                        <span class="lbl"><i class="fa fa-cube"></i> <?php echo __('Draw Priority'); ?></span>
                        <strong class="val" style="color:#059669;"><i class="fa fa-check-circle"></i> <?php echo __('Active Queue'); ?></strong>
                    </div>
                </div>

                <!-- Verified Queue Notification Strip -->
                <div class="wv-queued-strip">
                    <i class="fa fa-shield text-success"></i>
                    <span><?php echo __('Installments Verified &bull; Queued for next plot allotment'); ?></span>
                </div>

                <!-- Action Toolbar -->
                <div class="wv-queued-actions">
                    <?php echo $this->Html->link('<i class="fa fa-eye"></i> ' . __('Deal Details'), array('plugin' => false, 'admin' => false, 'controller' => 'MyDeals', 'action' => 'index'), array('escape' => false, 'class' => 'wv-qbtn')); ?>
                    <?php echo $this->Html->link('<i class="fa fa-dollar"></i> ' . __('Payment Ledger'), array('plugin' => false, 'admin' => false, 'controller' => 'MyPayments', 'action' => 'index'), array('escape' => false, 'class' => 'wv-qbtn')); ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php endif; ?>
</div>
