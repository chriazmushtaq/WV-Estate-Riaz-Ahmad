<?php
$c = !empty($bClient['BillingClient']) ? $bClient['BillingClient'] : array();
$clientName = !empty($c['name']) ? $c['name'] : __('Valued Consumer');
$clientPhone = !empty($c['phone']) ? $c['phone'] : (!empty($c['whatsapp']) ? $c['whatsapp'] : '-');
$clientEmail = !empty($c['email']) ? $c['email'] : '-';
$clientAddress = !empty($c['address']) ? $c['address'] : '-';
$consumerId = !empty($c['id']) ? ('WV70' . str_pad($c['id'], 2, '0', STR_PAD_LEFT)) : '';
$rawClientId = !empty($c['id']) ? (int)$c['id'] : 0;
?>

<style type="text/css">
:root {
    --wv-primary: #0284c7;
    --wv-primary-dark: #0369a1;
    --wv-dark: #0f172a;
    --wv-light: #ffffff;
    --wv-bg-soft: #f8fafc;
    --wv-border: #cbd5e1;
    --wv-text-main: #0f172a;
    --wv-text-sub: #334155;
    --wv-text-muted: #64748b;
    --wv-success: #15803d;
    --wv-danger: #dc2626;
    --wv-warning: #b45309;
}

.wv-portal-wrap {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    color: var(--wv-text-main) !important;
    padding: 6px 0 40px 0;
    touch-action: auto;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    width: 100%;
}

/* 1. TOP 50/50 HALF & HALF SPLIT GRID */
.wv-top-split-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 18px;
    margin-bottom: 20px;
}
.wv-card-half {
    background: #ffffff !important;
    border: 1px solid #cbd5e1 !important;
    border-top: 4px solid #0284c7 !important;
    border-radius: 14px !important;
    padding: 20px 22px !important;
    box-shadow: 0 3px 14px rgba(15, 23, 42, 0.04) !important;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}
.wv-card-half.meters-card {
    border-top-color: #10b981 !important;
}

.wv-hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #e0f2fe !important;
    border: 1px solid #bae6fd !important;
    color: #0369a1 !important;
    padding: 4px 12px;
    border-radius: 999px;
    font-size: 11.5px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 8px;
}
.wv-client-title {
    font-size: 20px !important;
    font-weight: 900 !important;
    color: #0f172a !important;
    margin: 0 0 10px 0 !important;
    line-height: 1.3 !important;
    letter-spacing: -0.2px;
}
.wv-info-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
    font-size: 13px !important;
}
.wv-info-row {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #334155 !important;
    font-weight: 600;
}
.wv-info-row .lbl {
    color: #64748b !important;
    font-weight: 700;
    min-width: 65px;
}
.wv-info-row .val {
    color: #0f172a !important;
    font-weight: 700;
}

.wv-chip-prop {
    background: #e0f2fe !important;
    color: #0369a1 !important;
    border: 1px solid #bae6fd !important;
    font-size: 11.5px !important;
    font-weight: 700 !important;
    padding: 4px 10px;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}
.wv-chip-meter {
    background: #dcfce7 !important;
    color: #15803d !important;
    border: 1px solid #bbf7d0 !important;
    font-size: 11.5px !important;
    font-weight: 700 !important;
    padding: 4px 10px;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

/* Meter Image Thumbnail & Snapshot Box */
.wv-meter-photo-thumb {
    position: relative;
    width: 58px;
    height: 58px;
    border-radius: 10px;
    overflow: hidden;
    border: 1.5px solid #cbd5e1;
    cursor: pointer;
    background: #0f172a;
    flex-shrink: 0;
    transition: all 0.2s ease;
}
.wv-meter-photo-thumb:hover {
    border-color: #0284c7;
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(2, 132, 199, 0.3);
}
.wv-meter-photo-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.wv-meter-photo-thumb .zoom-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: rgba(15, 23, 42, 0.75);
    color: #ffffff;
    font-size: 9px;
    font-weight: 800;
    text-align: center;
    padding: 2px 0;
    text-transform: uppercase;
}

.wv-meter-no-photo {
    width: 58px;
    height: 58px;
    border-radius: 10px;
    border: 1.5px dashed #cbd5e1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 2px;
    background: #f8fafc;
    flex-shrink: 0;
}

/* 2. KPI METRICS TILES */
.wv-kpi-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 22px;
}
.wv-kpi-card {
    background: #ffffff !important;
    border: 1px solid #cbd5e1 !important;
    border-radius: 14px !important;
    padding: 18px 20px !important;
    box-shadow: 0 2px 10px rgba(15, 23, 42, 0.04) !important;
    position: relative;
    overflow: hidden;
}
.wv-kpi-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 8px;
}
.wv-kpi-title {
    font-size: 12px !important;
    font-weight: 800 !important;
    text-transform: uppercase !important;
    color: #475569 !important;
    letter-spacing: 0.4px;
}
.wv-kpi-icon-wrap {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 17px;
}
.wv-kpi-val {
    font-size: 24px !important;
    font-weight: 900 !important;
    line-height: 1.15 !important;
    color: #0f172a !important;
}
.wv-kpi-sub {
    font-size: 12.5px !important;
    color: #475569 !important;
    font-weight: 600 !important;
    margin-top: 4px;
}

/* 3. TABS NAVIGATION */
.wv-tabs-bar {
    display: flex;
    align-items: center;
    gap: 8px;
    border-bottom: 2px solid #cbd5e1;
    margin-bottom: 20px;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    touch-action: pan-x pan-y;
    white-space: nowrap;
}
.wv-tab-btn {
    padding: 12px 20px;
    font-size: 14px !important;
    font-weight: 800 !important;
    color: #475569 !important;
    border-bottom: 3px solid transparent;
    text-decoration: none !important;
    cursor: pointer;
    transition: all 0.15s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.wv-tab-btn:hover {
    color: #0284c7 !important;
}
.wv-tab-btn.active {
    color: #0284c7 !important;
    border-bottom-color: #0284c7 !important;
    background: #f0f9ff !important;
    border-radius: 8px 8px 0 0;
}

/* 4. FILTER TOOLBAR */
.wv-filter-bar {
    background: #ffffff !important;
    border: 1px solid #cbd5e1 !important;
    border-radius: 12px !important;
    padding: 16px 20px !important;
    margin-bottom: 18px !important;
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 14px;
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.03) !important;
}
.wv-filter-group {
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.wv-filter-group label {
    font-size: 11.5px !important;
    font-weight: 800 !important;
    color: #475569 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.3px;
    margin: 0;
}
.wv-filter-input {
    height: 38px;
    padding: 6px 12px;
    border: 1.5px solid #cbd5e1 !important;
    border-radius: 8px !important;
    font-size: 13px !important;
    font-weight: 700 !important;
    color: #0f172a !important;
    background: #ffffff !important;
    outline: none;
}
.wv-filter-input:focus {
    border-color: #0284c7 !important;
    box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.12) !important;
}

/* 5. LEDGER TABLE FOR PC */
.wv-ledger-wrapper {
    background: #ffffff !important;
    border: 1px solid #cbd5e1 !important;
    border-radius: 14px !important;
    overflow-x: auto !important;
    -webkit-overflow-scrolling: touch !important;
    touch-action: pan-x pan-y !important;
    box-shadow: 0 4px 16px rgba(15, 23, 42, 0.04) !important;
    width: 100% !important;
}
.wv-ledger-table {
    width: 100% !important;
    min-width: 820px !important;
    border-collapse: separate !important;
    border-spacing: 0 !important;
    font-size: 13px !important;
}
.wv-ledger-table thead th {
    background: #0f172a !important;
    color: #ffffff !important;
    font-weight: 800 !important;
    font-size: 11.5px !important;
    text-transform: uppercase !important;
    letter-spacing: 0.5px !important;
    padding: 13px 14px !important;
    border-bottom: 1px solid #1e293b !important;
    white-space: nowrap !important;
    vertical-align: middle !important;
}
.wv-ledger-table tbody td {
    padding: 13px 14px !important;
    border-bottom: 1px solid #e2e8f0 !important;
    color: #0f172a !important;
    font-weight: 700 !important;
    vertical-align: middle !important;
    background: #ffffff !important;
}
.wv-ledger-table tbody tr:nth-child(even) td {
    background: #f8fafc !important;
}
.wv-ledger-table tbody tr:hover td {
    background: #f1f5f9 !important;
}

/* Status Badges */
.badge-status {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 11px !important;
    font-weight: 800 !important;
    text-transform: uppercase !important;
    padding: 4px 10px;
    border-radius: 999px;
    letter-spacing: 0.3px;
}
.badge-status.paid {
    background: #dcfce7 !important;
    color: #15803d !important;
    border: 1px solid #bbf7d0 !important;
}
.badge-status.partial {
    background: #fef3c7 !important;
    color: #b45309 !important;
    border: 1px solid #fde68a !important;
}
.badge-status.unpaid {
    background: #fee2e2 !important;
    color: #b91c1c !important;
    border: 1px solid #fecaca !important;
}

/* Action Buttons */
.btn-print-voucher {
    background: transparent !important;
    color: #0f172a !important;
    font-weight: 800 !important;
    font-size: 11.5px !important;
    padding: 6px 12px;
    border-radius: 6px;
    text-decoration: none !important;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    transition: all 0.15s ease;
    border: 1.5px solid #cbd5e1 !important;
    white-space: nowrap;
}
.btn-print-voucher i {
    color: #0f172a !important;
    transition: color 0.15s ease;
}
.btn-print-voucher:hover {
    background: #f1f5f9 !important;
    color: #0284c7 !important;
    border-color: #0284c7 !important;
    transform: translateY(-1px);
    box-shadow: 0 3px 8px rgba(15, 23, 42, 0.08);
}
.btn-print-voucher:hover i {
    color: #0284c7 !important;
}

.btn-pay-proof {
    background: #f59e0b !important;
    color: #ffffff !important;
    font-weight: 800 !important;
    font-size: 11.5px !important;
    padding: 6px 12px;
    border-radius: 6px;
    text-decoration: none !important;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    transition: all 0.15s ease;
    border: none !important;
    cursor: pointer;
    white-space: nowrap;
    box-shadow: 0 2px 8px rgba(245, 158, 11, 0.25);
}
.btn-pay-proof:hover {
    background: #d97706 !important;
    color: #ffffff !important;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(217, 119, 6, 0.35);
}

.btn-print-receipt {
    background: transparent !important;
    color: #15803d !important;
    font-weight: 800 !important;
    font-size: 11.5px !important;
    padding: 6px 12px;
    border-radius: 6px;
    text-decoration: none !important;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    transition: all 0.15s ease;
    border: 1.5px solid #16a34a !important;
    white-space: nowrap;
}
.btn-print-receipt i {
    color: #15803d !important;
    transition: color 0.15s ease;
}
.btn-print-receipt:hover {
    background: #15803d !important;
    color: #ffffff !important;
    border-color: #15803d !important;
    transform: translateY(-1px);
    box-shadow: 0 3px 8px rgba(21, 128, 61, 0.2);
}
.btn-print-receipt:hover i {
    color: #ffffff !important;
}

/* 6. MOBILE BILL CARDS VIEW (HIDDEN ON DESKTOP) */
.wv-mobile-bill-list {
    display: none;
}

/* 7. RESPONSIVE BREAKPOINTS (MOBILE & TABLET) */
@media (max-width: 991px) {
    .wv-top-split-grid {
        grid-template-columns: 1fr;
    }
    .wv-kpi-row {
        grid-template-columns: repeat(2, 1fr);
    }
    .wv-client-title {
        font-size: 19px !important;
    }
}

@media (max-width: 767px) {
    .wv-top-split-grid {
        grid-template-columns: 1fr;
        gap: 14px;
    }
    .wv-kpi-row {
        grid-template-columns: 1fr;
        gap: 12px;
    }
    .wv-filter-bar {
        flex-direction: column;
        align-items: stretch;
    }
    .wv-filter-bar form {
        flex-direction: column;
        width: 100%;
    }
    .wv-filter-group {
        width: 100%;
    }
    .wv-filter-input {
        width: 100% !important;
    }

    /* Switch to clean mobile cards layout on smartphones */
    .wv-ledger-wrapper {
        display: none;
    }
    .wv-mobile-bill-list {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }
    .wv-mobile-bill-card {
        background: #ffffff !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 14px !important;
        padding: 16px 18px !important;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.05) !important;
    }
    .wv-mb-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-bottom: 10px;
        border-bottom: 1px solid #e2e8f0;
        margin-bottom: 12px;
    }
    .wv-mb-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px 14px;
        font-size: 12.5px;
        margin-bottom: 14px;
    }
    .wv-mb-grid .mb-item span {
        display: block;
        font-size: 10.5px !important;
        font-weight: 800 !important;
        color: #64748b !important;
        text-transform: uppercase;
        margin-bottom: 2px;
    }
    .wv-mb-grid .mb-item strong {
        font-size: 13.5px !important;
        color: #0f172a !important;
        font-weight: 800 !important;
    }
}
</style>

<div class="dashboard-subheader">
    <div style="display:flex;align-items:center;justify-content:space-between;width:100%;flex-wrap:wrap;gap:12px;">
        <div>
            <?php echo $this->Session->flash(); ?>
            <h2 style="color:#ffffff !important;font-weight:900;margin:0;font-size:24px;text-shadow:0 2px 10px rgba(0,0,0,0.45);">
                <i class="fa fa-bolt" style="color:#f59e0b;margin-right:6px;"></i> <?php echo __('Utility');?> <span style="color:#7cf9ff !important;"><?php echo __('Bills & Ledger');?></span>
            </h2>
            <span style="color:#cbd5e1 !important;font-size:13px;font-weight:700;margin-top:4px;display:block;">
                <?php echo __('Track monthly utility consumption, view connected meters, and manage billing statements.'); ?>
            </span>
        </div>
        <div>
            <span style="background:rgba(255,255,255,0.12);border:1px solid rgba(255,255,255,0.2);color:#ffffff !important;padding:6px 14px;border-radius:999px;font-size:12px;font-weight:800;letter-spacing:0.5px;display:inline-flex;align-items:center;gap:6px;">
                <i class="fa fa-check-circle" style="color:#22c55e;"></i> <?php echo __('Verified Resident Portal'); ?>
            </span>
        </div>
    </div>
</div><!-- /.dashboard-subheader -->

<div class="wv-portal-wrap">

    <!-- ========================================== -->
    <!-- 1. HALF & HALF TOP SPLIT (50% / 50%)       -->
    <!-- ========================================== -->
    <div class="wv-top-split-grid">
        
        <!-- Left 50%: Consumer Info Card -->
        <div class="wv-card-half">
            <div>
                <div class="wv-hero-badge">
                    <i class="fa fa-user-circle"></i> <?php echo __('Consumer Profile'); ?> <?php if(!empty($consumerId)){ echo '• ' . h($consumerId); } ?>
                </div>
                <h2 class="wv-client-title"><?php echo h($clientName); ?></h2>
                
                <div class="wv-info-list">
                    <?php if($clientPhone !== '-'){ ?>
                        <div class="wv-info-row">
                            <span class="lbl"><i class="fa fa-phone" style="color:#0284c7;"></i> <?php echo __('Phone'); ?>:</span>
                            <strong class="val"><?php echo h($clientPhone); ?></strong>
                        </div>
                    <?php } ?>
                    <?php if($clientEmail !== '-'){ ?>
                        <div class="wv-info-row">
                            <span class="lbl"><i class="fa fa-envelope" style="color:#0284c7;"></i> <?php echo __('Email'); ?>:</span>
                            <strong class="val"><?php echo h($clientEmail); ?></strong>
                        </div>
                    <?php } ?>
                    <?php if($clientAddress !== '-'){ ?>
                        <div class="wv-info-row">
                            <span class="lbl"><i class="fa fa-map-marker" style="color:#0284c7;"></i> <?php echo __('Address'); ?>:</span>
                            <strong class="val"><?php echo h($clientAddress); ?></strong>
                        </div>
                    <?php } ?>
                </div>
            </div>

            <div style="margin-top:14px;padding-top:10px;border-top:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;">
                <span style="font-size:11.5px;color:#64748b;font-weight:700;">Account Status: <strong style="color:#15803d;">Active</strong></span>
                <span style="font-size:11px;background:#e0f2fe;color:#0369a1;padding:3px 8px;border-radius:4px;font-weight:800;">White Villas</span>
            </div>
        </div>

        <!-- Right 50%: Connected Meters & Assigned Units Card (with Meter Reading Snapshot Photo) -->
        <div class="wv-card-half meters-card">
            <div>
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
                    <div class="wv-hero-badge" style="background:#dcfce7 !important;color:#15803d !important;border-color:#bbf7d0 !important;">
                        <i class="fa fa-tachometer"></i> <?php echo __('Connected Meters'); ?> (<?php echo count($meters); ?>)
                    </div>
                    <span style="font-size:11.5px;color:#475569;font-weight:800;">
                        <i class="fa fa-building-o text-primary"></i> <?php echo count($properties); ?> <?php echo __('Units Assigned'); ?>
                    </span>
                </div>

                <!-- Meters Compact List with Photo on Right -->
                <div style="display:flex;flex-direction:column;gap:8px;">
                    <?php if(empty($meters)){ ?>
                        <div style="background:#f8fafc;border:1px dashed #cbd5e1;border-radius:8px;padding:14px;text-align:center;color:#64748b;font-size:12.5px;">
                            <?php echo __('No active meter linked to this profile.'); ?>
                        </div>
                    <?php } else { foreach($meters as $mRow){ $m = $mRow['BillingMeter'];
                        $uType = strtolower(trim($m['utility_type']));
                        $uIcon = ($uType === 'water') ? 'fa-tint' : (($uType === 'gas') ? 'fa-fire' : 'fa-bolt');
                        $uColor = ($uType === 'water') ? '#0284c7' : (($uType === 'gas') ? '#ea580c' : '#15803d');
                        $uBg = ($uType === 'water') ? '#e0f2fe' : (($uType === 'gas') ? '#ffedd5' : '#dcfce7');
                        $prevReading = isset($m['previous_reading']) ? (float)$m['previous_reading'] : (isset($m['initial_reading']) ? (float)$m['initial_reading'] : 0.0);
                        $currReading = (float)$m['current_reading'];
                        $photoUrl = !empty($m['latest_photo_url']) ? $m['latest_photo_url'] : '';
                        $readingDate = !empty($m['latest_reading_date']) ? date('d-M-Y', strtotime($m['latest_reading_date'])) : '';
                    ?>
                        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:10px 14px;display:flex;align-items:center;justify-content:space-between;gap:12px;">
                            
                            <!-- Left: Meter Icon & Meter Number -->
                            <div style="display:flex;align-items:center;gap:10px;min-width:140px;">
                                <div style="width:36px;height:36px;border-radius:8px;background:<?php echo $uBg; ?>;color:<?php echo $uColor; ?>;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0;">
                                    <i class="fa <?php echo $uIcon; ?>"></i>
                                </div>
                                <div>
                                    <strong style="font-size:13px;color:#0f172a !important;display:block;"><?php echo h($m['meter_number']); ?></strong>
                                    <span style="font-size:11px;color:#475569;font-weight:700;text-transform:uppercase;"><?php echo ucfirst($uType); ?> Meter</span>
                                </div>
                            </div>

                            <!-- Center: Previous & Current Readings -->
                            <div style="display:flex;align-items:center;gap:12px;text-align:right;">
                                <div>
                                    <span style="font-size:10px;color:#64748b;font-weight:800;display:block;text-transform:uppercase;"><?php echo __('Previous'); ?></span>
                                    <strong style="font-size:13px;color:#475569 !important;"><?php echo number_format($prevReading, 1); ?></strong>
                                </div>
                                <div style="border-left:1px solid #cbd5e1;padding-left:12px;">
                                    <span style="font-size:10px;color:#0284c7;font-weight:800;display:block;text-transform:uppercase;"><?php echo __('Current'); ?></span>
                                    <strong style="font-size:14px;color:#0f172a !important;"><?php echo number_format($currReading, 1); ?></strong>
                                </div>
                            </div>

                            <!-- Right: Latest Reading Meter Image Thumbnail -->
                            <div>
                                <?php if(!empty($photoUrl)){ ?>
                                    <div class="wv-meter-photo-thumb" onclick="openMeterPhotoModal('<?php echo h($photoUrl); ?>', '<?php echo h($m['meter_number']); ?> (<?php echo ucfirst($uType); ?>)', '<?php echo h($readingDate); ?>')" title="<?php echo __('Click to view high-res reading photo'); ?>">
                                        <img src="<?php echo h($photoUrl); ?>" alt="Meter Snapshot" />
                                        <div class="zoom-overlay"><i class="fa fa-search-plus"></i> View</div>
                                    </div>
                                <?php } else { ?>
                                    <div class="wv-meter-no-photo" title="<?php echo __('No reading photo attached'); ?>">
                                        <i class="fa fa-camera" style="color:#94a3b8;font-size:15px;"></i>
                                        <span style="font-size:9px;color:#94a3b8;font-weight:800;text-transform:uppercase;"><?php echo __('Photo'); ?></span>
                                    </div>
                                <?php } ?>
                            </div>

                        </div>
                    <?php } } ?>
                </div>
            </div>

            <!-- Assigned Properties Chips at Bottom -->
            <?php if(!empty($properties)){ ?>
                <div style="display:flex;gap:6px;flex-wrap:wrap;margin-top:10px;padding-top:8px;border-top:1px solid #f1f5f9;">
                    <?php foreach($properties as $pRow){ $p = $pRow['BillingProperty']; ?>
                        <span class="wv-chip-prop">
                            <i class="fa fa-home"></i> <?php echo h($p['title']); ?> (<?php echo h($p['property_no']); ?>)
                        </span>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>

    </div>

    <!-- ========================================== -->
    <!-- 2. KPI METRICS SUMMARY TILES (4 TILES)     -->
    <!-- ========================================== -->
    <div class="wv-kpi-row">
        <!-- Tile 1: Total Invoices -->
        <div class="wv-kpi-card">
            <div class="wv-kpi-top">
                <span class="wv-kpi-title"><?php echo __('Total Invoices'); ?></span>
                <div class="wv-kpi-icon-wrap" style="background:#e0f2fe;color:#0284c7;">
                    <i class="fa fa-file-text-o"></i>
                </div>
            </div>
            <div class="wv-kpi-val" style="color:#0284c7 !important;"><?php echo (int)$totalBillsCount; ?></div>
            <div class="wv-kpi-sub">Total Billed: <strong style="color:#0f172a;">PKR <?php echo number_format($totalBilledAmount, 2); ?></strong></div>
        </div>

        <!-- Tile 2: Total Paid -->
        <div class="wv-kpi-card">
            <div class="wv-kpi-top">
                <span class="wv-kpi-title"><?php echo __('Total Paid'); ?></span>
                <div class="wv-kpi-icon-wrap" style="background:#dcfce7;color:#15803d;">
                    <i class="fa fa-check-circle"></i>
                </div>
            </div>
            <div class="wv-kpi-val" style="color:#15803d !important;">PKR <?php echo number_format($totalPaidAmount, 2); ?></div>
            <div class="wv-kpi-sub">Cleared Payments</div>
        </div>

        <!-- Tile 3: Outstanding Balance -->
        <div class="wv-kpi-card" style="<?php if($totalBalanceOutstanding > 0){ echo 'border-color:#fca5a5 !important;'; } ?>">
            <div class="wv-kpi-top">
                <span class="wv-kpi-title"><?php echo __('Outstanding Balance'); ?></span>
                <div class="wv-kpi-icon-wrap" style="<?php echo ($totalBalanceOutstanding > 0) ? 'background:#fee2e2;color:#dc2626;' : 'background:#dcfce7;color:#15803d;'; ?>">
                    <i class="fa <?php echo ($totalBalanceOutstanding > 0) ? 'fa-exclamation-triangle' : 'fa-smile-o'; ?>"></i>
                </div>
            </div>
            <div class="wv-kpi-val" style="color:<?php echo ($totalBalanceOutstanding > 0) ? '#dc2626' : '#15803d'; ?> !important;">
                PKR <?php echo number_format($totalBalanceOutstanding, 2); ?>
            </div>
            <div class="wv-kpi-sub">
                <?php echo ($totalBalanceOutstanding > 0) ? '<span style="color:#dc2626;font-weight:800;">Payment Due</span>' : '<span style="color:#15803d;font-weight:800;">All Dues Cleared</span>'; ?>
            </div>
        </div>

        <!-- Tile 4: Total Units Consumed -->
        <div class="wv-kpi-card">
            <div class="wv-kpi-top">
                <span class="wv-kpi-title"><?php echo __('Units Consumed'); ?></span>
                <div class="wv-kpi-icon-wrap" style="background:#ede9fe;color:#4f46e5;">
                    <i class="fa fa-tachometer"></i>
                </div>
            </div>
            <div class="wv-kpi-val" style="color:#4f46e5 !important;"><?php echo number_format($totalUnitsConsumed, 1); ?></div>
            <div class="wv-kpi-sub">Cumulative Meter Units</div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- 3. TABS NAVIGATION                         -->
    <!-- ========================================== -->
    <div class="wv-tabs-bar">
        <a class="wv-tab-btn active" onclick="switchUtilityTab('tab-ledger', this)">
            <i class="fa fa-list"></i> <?php echo __('Billing Ledger & Invoices'); ?>
        </a>
        <a class="wv-tab-btn" onclick="switchUtilityTab('tab-meters', this)">
            <i class="fa fa-tachometer"></i> <?php echo __('Connected Meters'); ?> (<?php echo count($meters); ?>)
        </a>
        <a class="wv-tab-btn" onclick="switchUtilityTab('tab-payment', this)">
            <i class="fa fa-credit-card"></i> <?php echo __('Payment Methods & Raast'); ?>
        </a>
    </div>

    <!-- ========================================== -->
    <!-- TAB 1: BILLING STATEMENT & INVOICES        -->
    <!-- ========================================== -->
    <div id="tab-ledger" class="utility-tab-content">
        
        <!-- Filter Controls Form -->
        <div class="wv-filter-bar">
            <form method="get" action="<?php echo $this->Html->url(array('action' => 'index')); ?>" style="display:flex;align-items:flex-end;gap:12px;flex-wrap:wrap;flex:1;">
                <div class="wv-filter-group">
                    <label><?php echo __('Filter by Meter'); ?></label>
                    <select name="meter_id" class="wv-filter-input" style="min-width:180px;">
                        <option value=""><?php echo __('All Connected Meters'); ?></option>
                        <?php foreach($meters as $mRow){ $m = $mRow['BillingMeter']; ?>
                            <option value="<?php echo $m['id']; ?>" <?php if($filterMeterId == $m['id']) echo 'selected'; ?>>
                                <?php echo h($m['meter_number']); ?> (<?php echo ucfirst($m['utility_type']); ?>)
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div class="wv-filter-group">
                    <label><?php echo __('Payment Status'); ?></label>
                    <select name="status" class="wv-filter-input" style="min-width:140px;">
                        <option value=""><?php echo __('All Statuses'); ?></option>
                        <option value="unpaid" <?php if($filterStatus === 'unpaid') echo 'selected'; ?>><?php echo __('Unpaid'); ?></option>
                        <option value="partial" <?php if($filterStatus === 'partial') echo 'selected'; ?>><?php echo __('Partial'); ?></option>
                        <option value="paid" <?php if($filterStatus === 'paid') echo 'selected'; ?>><?php echo __('Paid'); ?></option>
                    </select>
                </div>

                <div class="wv-filter-group">
                    <label><?php echo __('Billing Month'); ?></label>
                    <input type="month" name="month" value="<?php echo h($filterMonth); ?>" class="wv-filter-input" />
                </div>

                <div style="display:flex;gap:8px;">
                    <button type="submit" class="btn btn-primary" style="background:#0284c7;border-color:#0284c7;font-weight:800;height:38px;padding:0 18px;border-radius:8px;display:inline-flex;align-items:center;gap:6px;">
                        <i class="fa fa-filter"></i> <?php echo __('Filter'); ?>
                    </button>
                    <a href="<?php echo $this->Html->url(array('action' => 'index')); ?>" class="btn btn-default" style="font-weight:700;height:38px;padding:8px 14px;border-radius:8px;border:1px solid #cbd5e1;">
                        <?php echo __('Reset'); ?>
                    </a>
                </div>
            </form>

            <div>
                <a href="<?php echo $this->Html->url(array('action' => 'statement', !empty($filterMeterId) ? $filterMeterId : '')); ?>" target="_blank" class="btn-print-voucher" style="font-weight:800;height:38px;padding:8px 18px;border-radius:8px;font-size:13px;gap:8px;">
                    <i class="fa fa-print"></i> <?php echo __('Print Statement'); ?>
                </a>
            </div>
        </div>

        <!-- 5A. DESKTOP LEDGER TABLE -->
        <div class="wv-ledger-wrapper">
            <div class="table-responsive">
                <table class="wv-ledger-table">
                    <thead>
                        <tr>
                            <th><?php echo __('Bill #'); ?></th>
                            <th><?php echo __('Period'); ?></th>
                            <th><?php echo __('Meter No'); ?></th>
                            <th><?php echo __('Units'); ?></th>
                            <th><?php echo __('Utility Amt'); ?></th>
                            <th><?php echo __('Maintenance'); ?></th>
                            <th><?php echo __('Arrears'); ?></th>
                            <th><?php echo __('Total Bill'); ?></th>
                            <th><?php echo __('Paid'); ?></th>
                            <th><?php echo __('Balance'); ?></th>
                            <th><?php echo __('Due Date'); ?></th>
                            <th><?php echo __('Status'); ?></th>
                            <th style="text-align:center;min-width:180px;"><?php echo __('Action'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($bills)){ ?>
                            <tr>
                                <td colspan="13" style="text-align:center;padding:40px 14px;color:#94a3b8;font-size:14px;background:#ffffff;">
                                    <i class="fa fa-file-text-o" style="font-size:32px;display:block;margin-bottom:10px;opacity:0.4;"></i>
                                    <strong><?php echo __('No utility bills found matching your selection.'); ?></strong>
                                </td>
                            </tr>
                        <?php } else { foreach($bills as $bRow){ 
                            $b = $bRow['BillingBill'];
                            $bId = (int)$b['id'];
                            $pId = !empty($b['billing_property_id']) ? (int)$b['billing_property_id'] : 0;
                            $uType = !empty($b['utility_type']) ? strtolower(trim((string)$b['utility_type'])) : 'electricity';
                            $meterNo = isset($propMeterMap[$pId . '_' . $uType]) ? $propMeterMap[$pId . '_' . $uType]['meter_number'] : (!empty($metersMap) ? (reset($metersMap)['meter_number']) : ('Unit #' . $pId));
                            $st = strtolower(trim((string)$b['payment_status']));
                            $remPayable = max(0.0, (float)$b['total_amount'] - (float)$b['paid_amount']);
                            $hasPendingProof = isset($pendingProofMap[$bId]);
                        ?>
                            <tr>
                                <td>
                                    <strong style="color:#0f172a;font-size:13.5px;"><?php echo h($b['bill_number']); ?></strong>
                                </td>
                                <td>
                                    <span style="background:#e2e8f0;color:#0f172a;font-weight:800;font-size:11px;padding:3px 8px;border-radius:5px;">
                                        <?php echo h($b['billing_period']); ?>
                                    </span>
                                </td>
                                <td>
                                    <strong style="color:#0f172a;"><?php echo h($meterNo); ?></strong>
                                    <small style="color:#475569;display:block;font-size:11px;font-weight:700;"><?php echo ucfirst($uType); ?></small>
                                </td>
                                <td>
                                    <strong style="color:#0f172a;"><?php echo number_format((float)$b['units_consumed'], 1); ?></strong>
                                    <small style="color:#475569;display:block;font-size:11px;font-weight:600;">@ <?php echo number_format((float)$b['unit_rate'], 2); ?></small>
                                </td>
                                <td style="color:#0f172a;">PKR <?php echo number_format((float)$b['consumption_amount'], 2); ?></td>
                                <td style="color:#0f172a;">PKR <?php echo number_format((float)$b['extra_charges_amount'], 2); ?></td>
                                <td>
                                    <?php if((float)$b['arrears_amount'] > 0){ ?>
                                        <span style="color:#dc2626;font-weight:800;">PKR <?php echo number_format((float)$b['arrears_amount'], 2); ?></span>
                                    <?php } else { ?>
                                        <span style="color:#64748b;font-weight:600;">0.00</span>
                                    <?php } ?>
                                </td>
                                <td>
                                    <strong style="color:#0284c7;font-size:14px;">PKR <?php echo number_format((float)$b['total_amount'], 2); ?></strong>
                                </td>
                                <td>
                                    <strong style="color:#15803d;">PKR <?php echo number_format((float)$b['paid_amount'], 2); ?></strong>
                                </td>
                                <td>
                                    <?php if((float)$b['balance_amount'] > 0){ ?>
                                        <strong style="color:#dc2626;font-size:13.5px;">PKR <?php echo number_format((float)$b['balance_amount'], 2); ?></strong>
                                    <?php } else { ?>
                                        <span style="color:#15803d;font-weight:800;">PKR 0.00</span>
                                    <?php } ?>
                                </td>
                                <td style="color:#334155;font-weight:700;">
                                    <?php echo !empty($b['due_date']) ? date('d-M-Y', strtotime((string)$b['due_date'])) : '-'; ?>
                                </td>
                                <td>
                                    <?php if($st === 'paid'){ ?>
                                        <span class="badge-status paid"><i class="fa fa-check-circle"></i> <?php echo __('PAID'); ?></span>
                                    <?php } elseif($hasPendingProof){ ?>
                                        <span class="badge-status partial" title="Payment proof submitted, awaiting verification"><i class="fa fa-clock-o"></i> <?php echo __('PROOF PENDING'); ?></span>
                                    <?php } elseif($st === 'partial'){ ?>
                                        <span class="badge-status partial"><i class="fa fa-clock-o"></i> <?php echo __('PARTIAL'); ?></span>
                                    <?php } else { ?>
                                        <span class="badge-status unpaid"><i class="fa fa-times-circle"></i> <?php echo __('UNPAID'); ?></span>
                                    <?php } ?>
                                </td>
                                <td style="text-align:center;">
                                    <div style="display:inline-flex;gap:6px;align-items:center;justify-content:center;flex-wrap:wrap;">
                                        <?php if($st !== 'paid'){ ?>
                                            <button type="button" class="btn-pay-proof" onclick="openPaymentProofModal(<?php echo $bId; ?>, '<?php echo h($b['bill_number']); ?>', '<?php echo addslashes($clientName); ?>', <?php echo $remPayable; ?>, '<?php echo addslashes($meterNo); ?>', '<?php echo ucfirst($uType); ?>')" title="<?php echo __('Submit bank transfer or online payment proof'); ?>">
                                                <i class="fa fa-upload"></i> <?php echo $hasPendingProof ? __('Update Proof') : __('Pay / Proof'); ?>
                                            </button>
                                        <?php } ?>
                                        <a href="<?php echo $this->Html->url(array('action' => 'print_bill', $bId)); ?>" target="_blank" class="btn-print-voucher" title="<?php echo __('Print Bill Copy'); ?>">
                                            <i class="fa fa-print"></i> <?php echo __('Bill'); ?>
                                        </a>
                                        <?php if($st === 'paid' || (float)$b['paid_amount'] > 0){ ?>
                                            <a href="<?php echo $this->Html->url(array('action' => 'print_receipt', $bId)); ?>" target="_blank" class="btn-print-receipt" title="<?php echo __('View / Print Official Paid Receipt Slip'); ?>">
                                                <i class="fa fa-file-text-o"></i> <?php echo __('Paid Slip'); ?>
                                            </a>
                                        <?php } ?>
                                    </div>
                                </td>
                            </tr>
                        <?php } } ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 5B. MOBILE BILL CARDS (DISPLAYED ON SMARTPHONES < 768px) -->
        <div class="wv-mobile-bill-list">
            <?php if(empty($bills)){ ?>
                <div style="background:#ffffff;border:1px solid #cbd5e1;border-radius:12px;padding:30px 16px;text-align:center;color:#64748b;">
                    <i class="fa fa-file-text-o" style="font-size:28px;display:block;margin-bottom:8px;opacity:0.5;"></i>
                    <strong><?php echo __('No utility bills found.'); ?></strong>
                </div>
            <?php } else { foreach($bills as $bRow){ 
                $b = $bRow['BillingBill'];
                $bId = (int)$b['id'];
                $pId = !empty($b['billing_property_id']) ? (int)$b['billing_property_id'] : 0;
                $uType = !empty($b['utility_type']) ? strtolower(trim((string)$b['utility_type'])) : 'electricity';
                $meterNo = isset($propMeterMap[$pId . '_' . $uType]) ? $propMeterMap[$pId . '_' . $uType]['meter_number'] : (!empty($metersMap) ? (reset($metersMap)['meter_number']) : ('Unit #' . $pId));
                $st = strtolower(trim((string)$b['payment_status']));
                $remPayable = max(0.0, (float)$b['total_amount'] - (float)$b['paid_amount']);
                $hasPendingProof = isset($pendingProofMap[$bId]);
            ?>
                <div class="wv-mobile-bill-card">
                    <div class="wv-mb-header">
                        <div>
                            <strong style="font-size:15px;color:#0f172a !important;display:block;"><?php echo h($b['bill_number']); ?></strong>
                            <span style="font-size:11.5px;color:#475569;font-weight:700;">Period: <?php echo h($b['billing_period']); ?></span>
                        </div>
                        <div>
                            <?php if($st === 'paid'){ ?>
                                <span class="badge-status paid"><i class="fa fa-check-circle"></i> <?php echo __('PAID'); ?></span>
                            <?php } elseif($hasPendingProof){ ?>
                                <span class="badge-status partial"><i class="fa fa-clock-o"></i> <?php echo __('PROOF PENDING'); ?></span>
                            <?php } elseif($st === 'partial'){ ?>
                                <span class="badge-status partial"><i class="fa fa-clock-o"></i> <?php echo __('PARTIAL'); ?></span>
                            <?php } else { ?>
                                <span class="badge-status unpaid"><i class="fa fa-times-circle"></i> <?php echo __('UNPAID'); ?></span>
                            <?php } ?>
                        </div>
                    </div>

                    <div class="wv-mb-grid">
                        <div class="mb-item">
                            <span><?php echo __('Meter'); ?></span>
                            <strong><?php echo h($meterNo); ?> (<?php echo ucfirst($uType); ?>)</strong>
                        </div>
                        <div class="mb-item">
                            <span><?php echo __('Units Consumed'); ?></span>
                            <strong><?php echo number_format((float)$b['units_consumed'], 1); ?> Units</strong>
                        </div>
                        <div class="mb-item">
                            <span><?php echo __('Total Bill'); ?></span>
                            <strong style="color:#0284c7 !important;font-size:15px;">PKR <?php echo number_format((float)$b['total_amount'], 2); ?></strong>
                        </div>
                        <div class="mb-item">
                            <span><?php echo __('Balance Due'); ?></span>
                            <strong style="color:<?php echo ((float)$b['balance_amount'] > 0) ? '#dc2626' : '#15803d'; ?> !important;">
                                PKR <?php echo number_format((float)$b['balance_amount'], 2); ?>
                            </strong>
                        </div>
                        <div class="mb-item">
                            <span><?php echo __('Due Date'); ?></span>
                            <strong style="color:#334155 !important;"><?php echo !empty($b['due_date']) ? date('d-M-Y', strtotime((string)$b['due_date'])) : '-'; ?></strong>
                        </div>
                        <div class="mb-item">
                            <span><?php echo __('Paid Amount'); ?></span>
                            <strong style="color:#15803d !important;">PKR <?php echo number_format((float)$b['paid_amount'], 2); ?></strong>
                        </div>
                    </div>

                    <div style="display:flex;flex-direction:column;gap:8px;border-top:1px solid #e2e8f0;padding-top:10px;">
                        <?php if($st !== 'paid'){ ?>
                            <button type="button" class="btn btn-warning" onclick="openPaymentProofModal(<?php echo $bId; ?>, '<?php echo h($b['bill_number']); ?>', '<?php echo addslashes($clientName); ?>', <?php echo $remPayable; ?>, '<?php echo addslashes($meterNo); ?>', '<?php echo ucfirst($uType); ?>')" style="width:100%;font-weight:800;background:#f59e0b;border:none;padding:9px 14px;border-radius:8px;color:#fff;display:flex;align-items:center;justify-content:center;gap:6px;">
                                <i class="fa fa-upload"></i> <?php echo $hasPendingProof ? __('Update Payment Proof') : __('Pay Online / Submit Proof'); ?>
                            </button>
                        <?php } ?>
                        <div style="display:flex;gap:8px;">
                            <a href="<?php echo $this->Html->url(array('action' => 'print_bill', $bId)); ?>" target="_blank" class="btn-print-voucher" style="flex:1;justify-content:center;padding:9px 12px;">
                                <i class="fa fa-print"></i> <?php echo __('View Bill'); ?>
                            </a>
                            <?php if($st === 'paid' || (float)$b['paid_amount'] > 0){ ?>
                                <a href="<?php echo $this->Html->url(array('action' => 'print_receipt', $bId)); ?>" target="_blank" class="btn-print-receipt" style="flex:1;justify-content:center;padding:9px 12px;">
                                    <i class="fa fa-file-text-o"></i> <?php echo __('Paid Slip'); ?>
                                </a>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            <?php } } ?>
        </div>

    </div>

    <!-- ========================================== -->
    <!-- TAB 2: CONNECTED METERS                    -->
    <!-- ========================================== -->
    <div id="tab-meters" class="utility-tab-content" style="display:none;">
        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(300px, 1fr));gap:18px;">
            <?php if(empty($meters)){ ?>
                <div style="background:#fff;padding:32px;border-radius:14px;border:1px solid #cbd5e1;grid-column:1/-1;text-align:center;color:#64748b;">
                    <i class="fa fa-tachometer" style="font-size:32px;margin-bottom:10px;display:block;opacity:0.4;"></i>
                    <strong><?php echo __('No meters connected to this account.'); ?></strong>
                </div>
            <?php } else { foreach($meters as $mRow){ $m = $mRow['BillingMeter'];
                $uType = strtolower(trim($m['utility_type']));
                $uIcon = ($uType === 'water') ? 'fa-tint' : (($uType === 'gas') ? 'fa-fire' : 'fa-bolt');
                $uColor = ($uType === 'water') ? '#0284c7' : (($uType === 'gas') ? '#ea580c' : '#15803d');
                $prevReading = isset($m['previous_reading']) ? (float)$m['previous_reading'] : (isset($m['initial_reading']) ? (float)$m['initial_reading'] : 0.0);
                $currReading = (float)$m['current_reading'];
                $photoUrl = !empty($m['latest_photo_url']) ? $m['latest_photo_url'] : '';
                $readingDate = !empty($m['latest_reading_date']) ? date('d-M-Y', strtotime($m['latest_reading_date'])) : '';
            ?>
                <div style="background:#ffffff;border:1px solid #cbd5e1;border-radius:14px;padding:20px;box-shadow:0 4px 14px rgba(15, 23, 42, 0.04);position:relative;">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
                        <div style="display:flex;align-items:center;gap:12px;">
                            <div style="width:44px;height:44px;border-radius:12px;background:<?php echo $uColor; ?>15;color:<?php echo $uColor; ?>;display:flex;align-items:center;justify-content:center;font-size:20px;">
                                <i class="fa <?php echo $uIcon; ?>"></i>
                            </div>
                            <div>
                                <h3 style="font-size:16px;font-weight:900;color:#0f172a !important;margin:0;"><?php echo h($m['meter_number']); ?></h3>
                                <span style="font-size:11.5px;color:#475569;font-weight:800;text-transform:uppercase;"><?php echo ucfirst($uType); ?> Meter</span>
                            </div>
                        </div>
                        <span class="badge-status paid"><?php echo strtoupper($m['status']); ?></span>
                    </div>

                    <!-- Readings Grid: Previous Reading BEFORE Current Reading -->
                    <div style="background:#f8fafc;border-radius:10px;padding:12px 16px;font-size:12.5px;display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;border:1px solid #e2e8f0;">
                        <div>
                            <span style="color:#64748b;font-size:11px;font-weight:800;display:block;text-transform:uppercase;"><?php echo __('Previous Reading'); ?></span>
                            <strong style="color:#475569 !important;font-size:16px;"><?php echo number_format($prevReading, 2); ?></strong>
                        </div>
                        <div style="text-align:right;">
                            <span style="color:#0284c7;font-size:11px;font-weight:800;display:block;text-transform:uppercase;"><?php echo __('Current Reading'); ?></span>
                            <strong style="color:#0f172a !important;font-size:16px;"><?php echo number_format($currReading, 2); ?></strong>
                        </div>
                    </div>

                    <!-- Latest Meter Snapshot Preview -->
                    <?php if(!empty($photoUrl)){ ?>
                        <div style="background:#f1f5f9;border:1px solid #cbd5e1;border-radius:10px;padding:10px 14px;display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
                            <div style="display:flex;align-items:center;gap:10px;">
                                <div class="wv-meter-photo-thumb" style="width:46px;height:46px;" onclick="openMeterPhotoModal('<?php echo h($photoUrl); ?>', '<?php echo h($m['meter_number']); ?>', '<?php echo h($readingDate); ?>')">
                                    <img src="<?php echo h($photoUrl); ?>" alt="Meter Snapshot" />
                                </div>
                                <div>
                                    <span style="font-size:10.5px;color:#64748b;font-weight:800;text-transform:uppercase;display:block;"><?php echo __('Verified Snapshot'); ?></span>
                                    <strong style="font-size:12px;color:#0f172a;"><?php echo !empty($readingDate) ? $readingDate : 'Recorded'; ?></strong>
                                </div>
                            </div>
                            <button type="button" class="btn btn-xs btn-default" onclick="openMeterPhotoModal('<?php echo h($photoUrl); ?>', '<?php echo h($m['meter_number']); ?>', '<?php echo h($readingDate); ?>')" style="font-weight:800;font-size:11px;border:1px solid #cbd5e1;">
                                <i class="fa fa-eye"></i> <?php echo __('View Photo'); ?>
                            </button>
                        </div>
                    <?php } ?>

                    <div style="text-align:right;">
                        <a href="<?php echo $this->Html->url(array('action' => 'index', '?' => array('meter_id' => $m['id']))); ?>" class="btn btn-sm btn-default" style="font-weight:800;font-size:12px;border-radius:7px;width:100%;padding:8px 0;border:1px solid #cbd5e1;">
                            <i class="fa fa-list"></i> <?php echo __('View Meter Bills'); ?>
                        </a>
                    </div>
                </div>
            <?php } } ?>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- TAB 3: PAYMENT METHODS & RAAST             -->
    <!-- ========================================== -->
    <div id="tab-payment" class="utility-tab-content" style="display:none;">
        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(320px, 1fr));gap:20px;">
            
            <!-- Bank Transfer Details Card -->
            <div style="background:#ffffff;border:1px solid #cbd5e1;border-radius:16px;padding:24px;box-shadow:0 4px 14px rgba(15, 23, 42, 0.04);">
                <div style="display:flex;align-items:center;gap:12px;margin-bottom:18px;">
                    <div style="width:44px;height:44px;border-radius:12px;background:#0284c715;color:#0284c7;display:flex;align-items:center;justify-content:center;font-size:20px;">
                        <i class="fa fa-university"></i>
                    </div>
                    <div>
                        <h3 style="font-size:17px;font-weight:900;color:#0f172a !important;margin:0;"><?php echo __('Bank Transfer / Online Deposit'); ?></h3>
                        <span style="font-size:12px;color:#475569;font-weight:700;"><?php echo __('Direct Bank Deposit & IBFT Transfer'); ?></span>
                    </div>
                </div>

                <div style="background:#f8fafc;border:1px solid #cbd5e1;border-radius:12px;padding:18px;font-size:13.5px;line-height:1.8;color:#0f172a !important;font-family:monospace;white-space:pre-wrap;font-weight:700;"><?php echo h($bankDetails); ?></div>

                <p style="font-size:12.5px;color:#475569;margin-top:16px;line-height:1.6;font-weight:600;">
                    <i class="fa fa-info-circle text-info"></i> <?php echo __('Please mention your Bill Number in the transfer remarks/description for immediate reconciliation.'); ?>
                </p>
            </div>

            <!-- SBP Raast / 1Bill Instant Pay Card -->
            <div style="background:#ffffff;border:1px solid #cbd5e1;border-radius:16px;padding:24px;box-shadow:0 4px 14px rgba(15, 23, 42, 0.04);">
                <div style="display:flex;align-items:center;gap:12px;margin-bottom:18px;">
                    <div style="width:44px;height:44px;border-radius:12px;background:#10b98115;color:#15803d;display:flex;align-items:center;justify-content:center;font-size:20px;">
                        <i class="fa fa-qrcode"></i>
                    </div>
                    <div>
                        <h3 style="font-size:17px;font-weight:900;color:#0f172a !important;margin:0;"><?php echo __('Raast Instant Payment'); ?></h3>
                        <span style="font-size:12px;color:#475569;font-weight:700;"><?php echo __('State Bank of Pakistan (SBP) Raast ID'); ?></span>
                    </div>
                </div>

                <div style="background:#ecfdf5;border:1.5px solid #a7f3d0;border-radius:12px;padding:20px;text-align:center;">
                    <span style="font-size:11.5px;font-weight:800;color:#065f46;text-transform:uppercase;display:block;"><?php echo __('Registered Raast ID'); ?></span>
                    <strong style="font-size:24px;color:#065f46 !important;letter-spacing:1.5px;display:block;margin:8px 0;font-weight:900;"><?php echo h($raastId); ?></strong>
                    <span style="font-size:12.5px;color:#047857;font-weight:700;"><?php echo __('Pay instantly from any mobile banking app with zero transaction fees.'); ?></span>
                </div>

                <div style="margin-top:16px;text-align:center;">
                    <a href="<?php echo $this->Html->url('/billing'); ?>" target="_blank" class="btn btn-default" style="font-weight:800;font-size:13px;border-radius:8px;padding:8px 18px;border:1px solid #cbd5e1;">
                        <i class="fa fa-globe"></i> <?php echo __('Open Public Bill Search Portal'); ?>
                    </a>
                </div>
            </div>

        </div>
    </div>

				</div><!-- /.container-fluid -->						
			</div><!-- /.dashboard-content -->			
		</div><!-- /.dashboard-wrapper -->
	</div><!-- /.page-wrapper -->
</div>

<!-- ========================================== -->
<!-- 1. MODAL: SUBMIT ONLINE PAYMENT PROOF      -->
<!-- ========================================== -->
<div id="modalSubmitPayment" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(15,23,42,0.85);z-index:999999;align-items:center;justify-content:center;padding:16px;overflow-y:auto;" onclick="closePaymentProofModal()">
    <div style="position:relative;max-width:540px;width:100%;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 25px 50px rgba(0,0,0,0.5);margin:auto;" onclick="event.stopPropagation()">
        
        <form method="post" action="<?php echo $this->Html->url(array('action' => 'submit_payment')); ?>" enctype="multipart/form-data">
            <input type="hidden" name="data[PaymentRef][bill_id]" id="payBillId" value="" />
            <input type="hidden" name="data[PaymentRef][billing_client_id]" id="payClientId" value="<?php echo $rawClientId; ?>" />

            <div style="background:#0284c7;color:#ffffff;padding:16px 22px;display:flex;align-items:center;justify-content:space-between;">
                <h4 style="margin:0;font-size:17px;font-weight:900;color:#ffffff;display:flex;align-items:center;gap:8px;">
                    <i class="fa fa-credit-card"></i> <?php echo __('Submit Online Payment Proof'); ?>
                </h4>
                <button type="button" onclick="closePaymentProofModal()" style="background:transparent;border:none;color:#ffffff;font-size:24px;cursor:pointer;line-height:1;padding:0 4px;">&times;</button>
            </div>

            <div style="padding:22px;">
                <!-- Summary Card Header -->
                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:12px 16px;margin-bottom:18px;">
                    <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:4px;">
                        <span style="color:#64748b;font-weight:700;"><?php echo __('Bill Number:'); ?></span>
                        <strong id="modalPayBillNo" style="color:#0f172a;font-family:monospace;font-size:14px;">-</strong>
                    </div>
                    <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:4px;">
                        <span style="color:#64748b;font-weight:700;"><?php echo __('Consumer:'); ?></span>
                        <strong id="modalPayClientName" style="color:#0f172a;"><?php echo h($clientName); ?></strong>
                    </div>
                    <div style="display:flex;justify-content:space-between;font-size:13.5px;padding-top:6px;border-top:1px solid #e2e8f0;margin-top:6px;">
                        <span style="color:#64748b;font-weight:700;"><?php echo __('Remaining Due Balance:'); ?></span>
                        <strong id="modalPayDue" style="color:#0284c7;font-size:16px;font-weight:900;">PKR 0.00</strong>
                    </div>
                </div>

                <!-- Paid Amount -->
                <div style="margin-bottom:14px;">
                    <label style="display:block;font-size:12.5px;font-weight:800;color:#334155;text-transform:uppercase;margin-bottom:5px;">
                        <?php echo __('Paid Amount (PKR)'); ?> <span style="color:#dc2626;">*</span>
                    </label>
                    <input type="number" step="0.01" name="data[PaymentRef][amount]" id="payAmountInput" class="form-control" required style="width:100%;height:40px;border-radius:8px;border:1.5px solid #cbd5e1;padding:6px 12px;font-size:14px;font-weight:800;color:#0f172a;" />
                </div>

                <!-- Payment Method -->
                <div style="margin-bottom:14px;">
                    <label style="display:block;font-size:12.5px;font-weight:800;color:#334155;text-transform:uppercase;margin-bottom:5px;">
                        <?php echo __('Payment Method'); ?> <span style="color:#dc2626;">*</span>
                    </label>
                    <select name="data[PaymentRef][payment_method]" id="payMethodSelect" class="form-control" style="width:100%;height:40px;border-radius:8px;border:1.5px solid #cbd5e1;padding:6px 12px;font-size:13.5px;font-weight:700;color:#0f172a;" required>
                        <option value="JazzCash">JazzCash (TID: 981452707)</option>
                        <option value="EasyPaisa">EasyPaisa</option>
                        <option value="Bank Transfer">Bank Transfer (0773310485346)</option>
                        <option value="Raast">Raast Instant Payment (03391101156)</option>
                        <option value="Cash Deposit">Cash Deposit Slip</option>
                    </select>
                </div>

                <!-- Transaction Reference / TID Number -->
                <div style="margin-bottom:14px;">
                    <label style="display:block;font-size:12.5px;font-weight:800;color:#334155;text-transform:uppercase;margin-bottom:5px;">
                        <?php echo __('Transaction Ref / TID Number'); ?> <span style="color:#dc2626;">*</span>
                    </label>
                    <input type="text" name="data[PaymentRef][transaction_ref]" class="form-control" placeholder="e.g. TID 981452707 or Bank IBFT Ref #" required style="width:100%;height:40px;border-radius:8px;border:1.5px solid #cbd5e1;padding:6px 12px;font-size:13.5px;font-weight:700;color:#0f172a;" />
                </div>

                <!-- Screenshot Proof File -->
                <div style="margin-bottom:14px;">
                    <label style="display:block;font-size:12.5px;font-weight:800;color:#334155;text-transform:uppercase;margin-bottom:5px;">
                        <?php echo __('Upload Payment Receipt / Screenshot'); ?>
                    </label>
                    <input type="file" name="slip_file" accept="image/*,.pdf" class="form-control" style="width:100%;border-radius:8px;border:1.5px solid #cbd5e1;padding:6px 10px;font-size:13px;" />
                    <small style="color:#64748b;font-size:11.5px;display:block;margin-top:4px;">
                        <?php echo __('Attach screenshot of JazzCash, Bank Transfer or EasyPaisa confirmation screen.'); ?>
                    </small>
                </div>

                <!-- Optional Notes -->
                <div style="margin-bottom:6px;">
                    <label style="display:block;font-size:12.5px;font-weight:800;color:#334155;text-transform:uppercase;margin-bottom:5px;">
                        <?php echo __('Account Title / Sender Remarks (Optional)'); ?>
                    </label>
                    <textarea name="data[PaymentRef][notes]" class="form-control" rows="2" placeholder="Sender Account Title or additional notes..." style="width:100%;border-radius:8px;border:1.5px solid #cbd5e1;padding:8px 12px;font-size:13px;"></textarea>
                </div>
            </div>

            <div style="background:#f8fafc;padding:14px 22px;border-top:1px solid #e2e8f0;display:flex;justify-content:flex-end;gap:10px;">
                <button type="button" onclick="closePaymentProofModal()" class="btn btn-default" style="font-weight:700;border:1px solid #cbd5e1;padding:8px 18px;border-radius:8px;">
                    <?php echo __('Cancel'); ?>
                </button>
                <button type="submit" class="btn btn-primary" style="background:#f59e0b;border-color:#d97706;color:#ffffff;font-weight:900;padding:8px 24px;border-radius:8px;box-shadow:0 4px 12px rgba(245,158,11,0.25);">
                    <i class="fa fa-paper-plane"></i> <?php echo __('Submit Payment Proof'); ?>
                </button>
            </div>
        </form>

    </div>
</div>

<!-- ========================================== -->
<!-- 2. LIGHTBOX MODAL FOR METER READING PHOTO  -->
<!-- ========================================== -->
<div id="meterPhotoModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(15,23,42,0.88);z-index:999999;align-items:center;justify-content:center;padding:20px;" onclick="closeMeterPhotoModal()">
    <div style="position:relative;max-width:560px;width:100%;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 25px 50px rgba(0,0,0,0.5);" onclick="event.stopPropagation()">
        <div style="background:#0f172a;color:#ffffff;padding:14px 20px;display:flex;align-items:center;justify-content:space-between;">
            <strong id="modalMeterTitle" style="font-size:15px;color:#ffffff;"><i class="fa fa-camera" style="color:#0284c7;"></i> Meter Reading Snapshot</strong>
            <button type="button" onclick="closeMeterPhotoModal()" style="background:transparent;border:none;color:#ffffff;font-size:24px;cursor:pointer;line-height:1;padding:0 4px;">&times;</button>
        </div>
        <div style="padding:16px;text-align:center;background:#0f172a;display:flex;align-items:center;justify-content:center;min-height:260px;">
            <img id="modalMeterImg" src="" alt="Meter Reading" style="max-width:100%;max-height:65vh;border-radius:10px;object-fit:contain;box-shadow:0 4px 20px rgba(0,0,0,0.5);" />
        </div>
        <div style="padding:14px 20px;background:#f8fafc;display:flex;justify-content:space-between;align-items:center;font-size:12.5px;color:#475569;border-top:1px solid #e2e8f0;">
            <span id="modalMeterDate" style="font-weight:700;"></span>
            <a id="modalMeterLink" href="" target="_blank" class="btn btn-xs btn-default" style="font-weight:800;border:1px solid #cbd5e1;padding:5px 12px;border-radius:6px;">
                <i class="fa fa-external-link"></i> Open Full Photo
            </a>
        </div>
    </div>
</div>

<script>
function switchUtilityTab(tabId, el) {
    document.querySelectorAll('.utility-tab-content').forEach(function(t) {
        t.style.display = 'none';
    });
    document.querySelectorAll('.wv-tabs-bar .wv-tab-btn').forEach(function(a) {
        a.classList.remove('active');
    });
    var target = document.getElementById(tabId);
    if(target) target.style.display = 'block';
    if(el) el.classList.add('active');
}

function openMeterPhotoModal(imgUrl, meterTitle, dateStr) {
    var modal = document.getElementById('meterPhotoModal');
    var img = document.getElementById('modalMeterImg');
    var title = document.getElementById('modalMeterTitle');
    var dateEl = document.getElementById('modalMeterDate');
    var link = document.getElementById('modalMeterLink');
    if(!modal || !img) return;

    img.src = imgUrl;
    link.href = imgUrl;
    if(title && meterTitle) title.innerHTML = '<i class="fa fa-camera" style="color:#0284c7;"></i> ' + meterTitle + ' Snapshot';
    if(dateEl) dateEl.innerText = dateStr ? ('Recorded Date: ' + dateStr) : 'Verified Reading Snapshot';
    
    modal.style.display = 'flex';
}

function closeMeterPhotoModal() {
    var modal = document.getElementById('meterPhotoModal');
    if(modal) modal.style.display = 'none';
}

function openPaymentProofModal(billId, billNumber, clientName, remainingDue, meterNo, utilityType) {
    var modal = document.getElementById('modalSubmitPayment');
    if (!modal) return;

    document.getElementById('payBillId').value = billId;
    document.getElementById('modalPayBillNo').innerText = billNumber;
    if(clientName) document.getElementById('modalPayClientName').innerText = clientName;
    document.getElementById('modalPayDue').innerText = 'PKR ' + parseFloat(remainingDue).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('payAmountInput').value = parseFloat(remainingDue).toFixed(2);

    modal.style.display = 'flex';
}

function closePaymentProofModal() {
    var modal = document.getElementById('modalSubmitPayment');
    if (modal) modal.style.display = 'none';
}

document.addEventListener('keydown', function(e) {
    if(e.key === 'Escape') {
        closeMeterPhotoModal();
        closePaymentProofModal();
    }
});
</script>
