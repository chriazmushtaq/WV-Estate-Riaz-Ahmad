<?php
/**
 * Frontend Check Bill Portal
 * WordPress Plugin Behavior & UI Replication
 */
?>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Noto+Nastaliq+Urdu:wght@400;700&display=swap">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

<<style>
    .subm-portal-wrapper {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        max-width: 840px;
        margin: 20px auto;
        padding: 0 14px;
        color: #0f172a;
    }
    .urdu-font {
        font-family: 'Noto Nastaliq Urdu', serif;
        direction: rtl;
        text-align: right;
    }
    .portal-card {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 14px;
        padding: 20px 22px;
        box-shadow: 0 4px 18px rgba(0, 0, 0, 0.04);
        margin-bottom: 18px;
    }
    .tab-pill-btn {
        display: inline-block;
        padding: 7px 18px;
        font-size: 13px;
        font-weight: 700;
        border-radius: 20px;
        text-decoration: none;
        transition: all 0.2s ease;
        border: 1px solid transparent;
        cursor: pointer;
    }
    .tab-pill-btn.active {
        background: #1e293b;
        color: #ffffff;
    }
    .tab-pill-btn.inactive {
        background: #ffffff;
        color: #64748b;
        border-color: #e2e8f0;
    }
    .tab-pill-btn.inactive:hover {
        background: #f8fafc;
        color: #0f172a;
    }
    .btn-search-blue {
        background: #0369a1;
        color: #ffffff;
        font-weight: 700;
        font-size: 14px;
        padding: 10px 24px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s;
        box-shadow: 0 4px 12px rgba(3, 105, 161, 0.25);
    }
    .btn-search-blue:hover {
        background: #0284c7;
        box-shadow: 0 6px 16px rgba(2, 132, 199, 0.35);
    }
    @media (max-width: 767px) {
        .subm-portal-wrapper {
            margin: 10px auto;
            padding: 0 8px;
        }
        .portal-card {
            padding: 14px 14px;
            border-radius: 12px;
            margin-bottom: 14px;
        }
        .bill-header-row {
            flex-direction: column;
            align-items: flex-start !important;
            gap: 8px !important;
        }
        .bill-details-grid {
            grid-template-columns: 1fr 1fr !important;
            gap: 10px !important;
            padding: 12px !important;
        }
        .meter-reading-section {
            grid-template-columns: 1fr !important;
            gap: 12px !important;
            padding: 12px !important;
        }
        .bill-bottom-actions {
            flex-direction: column !important;
            align-items: stretch !important;
        }
        .bill-bottom-actions a {
            width: 100% !important;
            justify-content: center !important;
        }
        .portal-bottom-grid {
            grid-template-columns: 1fr !important;
        }
    }
</style>

<div class="subm-portal-wrapper">

    <?php 
        $hasRecord = ($query !== '' && !empty($bill));
    ?>

    <!-- ========================================== -->
    <!-- 1. SEARCH BOX CARD (HIDDEN WHEN RECORD FOUND) -->
    <!-- ========================================== -->
    <?php if(!$hasRecord){ ?>
        <div class="portal-card">
            <!-- Pill Tabs -->
            <div style="display:flex;gap:10px;margin-bottom:16px;">
                <a href="<?php echo $this->Html->url(array('action' => 'check_bill', '?' => array('tab' => 'bill'))); ?>" class="tab-pill-btn <?php echo ($activeTab !== 'history') ? 'active' : 'inactive'; ?>">
                    <?php echo __('Check Bill'); ?>
                </a>
                <a href="<?php echo $this->Html->url(array('action' => 'check_bill', '?' => array('tab' => 'history', 'q' => $query))); ?>" class="tab-pill-btn <?php echo ($activeTab === 'history') ? 'active' : 'inactive'; ?>">
                    <?php echo __('Check History'); ?>
                </a>
            </div>

            <!-- Search Form -->
            <form method="get" action="<?php echo $this->Html->url(array('action' => 'check_bill')); ?>">
                <input type="hidden" name="tab" value="<?php echo h($activeTab); ?>" />
                <div class="form-group" style="margin-bottom:14px;">
                    <label style="display:block;font-size:13.5px;font-weight:600;color:#334155;margin-bottom:6px;">
                        <?php echo __('Meter Consumer ID / Meter Number / Bill Number / Bill ID'); ?>
                    </label>
                    <input type="text" name="q" value="<?php echo h($query); ?>" class="form-control" placeholder="<?php echo __('Enter meter consumer ID (e.g. WV7010), meter number or bill number'); ?>" required style="width:100%;padding:11px 15px;font-size:14px;color:#0f172a;border:1px solid #cbd5e1;border-radius:8px;outline:none;background:#fff;" />
                </div>

                <button type="submit" class="btn-search-blue">
                    <?php echo ($activeTab === 'history') ? __('Search History') : __('Search Bill'); ?>
                </button>
            </form>
        </div>
    <?php } else { ?>
        <!-- BACK / SEARCH ANOTHER BILL BUTTON BAR -->
        <div style="margin-bottom:14px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">
            <a href="<?php echo $this->Html->url(array('action' => 'check_bill')); ?>" class="btn-back-search" style="display:inline-flex;align-items:center;gap:8px;background:#ffffff;color:#0f172a;font-weight:700;font-size:13px;padding:9px 18px;border-radius:8px;text-decoration:none;border:1px solid #cbd5e1;box-shadow:0 2px 8px rgba(0,0,0,0.04);transition:all 0.2s;">
                <i class="fa fa-arrow-left text-primary"></i> <?php echo __('Back / Search Another Bill'); ?>
            </a>
            <span style="font-size:12px;color:#64748b;font-weight:600;">
                <?php echo __('Search Result For'); ?>: <strong style="color:#0284c7;font-family:monospace;"><?php echo h($query); ?></strong>
            </span>
        </div>
    <?php } ?>

    <!-- ========================================== -->
    <!-- SEARCH RESULTS SECTION                     -->
    <!-- ========================================== -->
    <?php if($query !== ''){ ?>
        <?php if(!empty($bill)){ 
            $b = $bill['BillingBill']; 
            $p = $bill['BillingProperty']; 
            $c = $bill['BillingClient'];
            $isPaid = ($b['payment_status'] === 'paid');
            $isPartial = ($b['payment_status'] === 'partial');
        ?>
            <!-- Bill Found Result Box -->
            <div class="portal-card" style="border-top:4px solid #0284c7;">
                <!-- Payment Submission Status Alert (If Any Request Submitted) -->
                <?php if(!empty($latestPaymentReq)){ 
                    $reqSt = $latestPaymentReq['BillingPaymentRef']['status'];
                    $reqTid = $latestPaymentReq['BillingPaymentRef']['transaction_ref'];
                ?>
                    <?php if($reqSt === 'pending'){ ?>
                        <div style="background:#fef3c7;border:1px solid #fde68a;border-radius:8px;padding:10px 14px;margin-bottom:16px;display:flex;align-items:center;justify-content:space-between;color:#92400e;">
                            <div>
                                <strong style="font-size:13.5px;"><i class="fa fa-clock-o text-warning"></i> Payment Request Submitted (TID: <?php echo h($reqTid); ?>)</strong>
                                <span style="display:block;font-size:11.5px;color:#b45309;margin-top:2px;">Submitted on <?php echo date('d-M-Y H:i', strtotime($latestPaymentReq['BillingPaymentRef']['submission_date'])); ?> — Pending admin verification.</span>
                            </div>
                            <span style="background:#f59e0b;color:#fff;padding:4px 10px;border-radius:12px;font-size:11px;font-weight:800;display:inline-block;">Pending Verification</span>
                        </div>
                    <?php } elseif($reqSt === 'rejected'){ ?>
                        <div style="background:#fee2e2;border:1px solid #fecaca;border-radius:8px;padding:10px 14px;margin-bottom:16px;color:#991b1b;">
                            <div style="display:flex;align-items:center;justify-content:space-between;">
                                <strong style="font-size:13.5px;"><i class="fa fa-exclamation-triangle text-danger"></i> Payment Request Rejected (TID: <?php echo h($reqTid); ?>)</strong>
                                <span style="background:#ef4444;color:#fff;padding:4px 10px;border-radius:12px;font-size:11px;font-weight:800;display:inline-block;">Rejected</span>
                            </div>
                            <div style="font-size:12px;margin-top:4px;color:#b91c1c;">
                                <strong>Reason:</strong> <?php echo h(!empty($latestPaymentReq['BillingPaymentRef']['rejection_reason']) ? $latestPaymentReq['BillingPaymentRef']['rejection_reason'] : 'Verification failed.'); ?>
                            </div>
                        </div>
                    <?php } ?>
                <?php } ?>

                <!-- Top Header Bar -->
                <div class="bill-header-row" style="display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid #e2e8f0;padding-bottom:14px;margin-bottom:16px;">
                    <div>
                        <div style="font-size:11px;font-weight:800;color:#0284c7;text-transform:uppercase;letter-spacing:0.8px;">
                            <?php echo date('F Y', strtotime($b['billing_period'] . '-01')); ?> BILLING INVOICE
                        </div>
                        <h3 style="margin:2px 0 0 0;font-size:20px;font-weight:900;color:#0f172a;">
                            <?php echo __('BILL #') . h($b['bill_number']); ?>
                        </h3>
                    </div>
                    <div>
                        <?php if($isPaid){ ?>
                            <span style="background:#dcfce7;color:#15803d;border:1px solid #bbf7d0;font-weight:900;padding:6px 16px;border-radius:20px;font-size:12.5px;display:inline-flex;align-items:center;gap:6px;">
                                <i class="fa fa-check-circle"></i> <?php echo __('PAID'); ?>
                            </span>
                        <?php } elseif($isPartial){ ?>
                            <span style="background:#fef3c7;color:#b45309;border:1px solid #fde68a;font-weight:900;padding:6px 16px;border-radius:20px;font-size:12.5px;display:inline-flex;align-items:center;gap:6px;">
                                <i class="fa fa-clock-o"></i> <?php echo __('PARTIALLY PAID'); ?>
                            </span>
                        <?php } else { ?>
                            <span style="background:#fee2e2;color:#b91c1c;border:1px solid #fecaca;font-weight:900;padding:6px 16px;border-radius:20px;font-size:12.5px;display:inline-flex;align-items:center;gap:6px;">
                                <i class="fa fa-exclamation-circle"></i> <?php echo __('UNPAID'); ?>
                            </span>
                        <?php } ?>
                    </div>
                </div>

                <!-- Consumer & Property Info -->
                <div class="bill-details-grid" style="display:grid;grid-template-columns:repeat(4, 1fr);gap:14px;background:#f8fafc;padding:14px 16px;border-radius:10px;border:1px solid #e2e8f0;margin-bottom:14px;">
                    <div>
                        <span style="font-size:10.5px;color:#64748b;font-weight:700;display:block;text-transform:uppercase;margin-bottom:2px;"><?php echo __('Consumer ID'); ?></span>
                        <strong style="color:#0f172a;font-size:14px;font-family:monospace;background:#fef08a;color:#854d0e;padding:2px 8px;border-radius:4px;display:inline-block;"><?php echo !empty($c['id']) ? ('WV70' . str_pad($c['id'], 2, '0', STR_PAD_LEFT)) : (!empty($p['property_no']) ? $p['property_no'] : '-'); ?></strong>
                    </div>
                    <div>
                        <span style="font-size:10.5px;color:#64748b;font-weight:700;display:block;text-transform:uppercase;margin-bottom:2px;"><?php echo __('Consumer Name'); ?></span>
                        <strong style="color:#0f172a;font-size:13.5px;line-height:1.2;display:block;"><?php echo h(!empty($c['name']) ? $c['name'] : '-'); ?></strong>
                    </div>
                    <div>
                        <span style="font-size:10.5px;color:#64748b;font-weight:700;display:block;text-transform:uppercase;margin-bottom:2px;"><?php echo __('Property Unit'); ?></span>
                        <strong style="color:#0f172a;font-size:13.5px;"><?php echo h(!empty($p['property_no']) ? $p['property_no'] : (!empty($p['title']) ? $p['title'] : '-')); ?></strong>
                    </div>
                    <div>
                        <span style="font-size:10.5px;color:#64748b;font-weight:700;display:block;text-transform:uppercase;margin-bottom:2px;"><?php echo __('Due Date'); ?></span>
                        <strong style="color:#dc2626;font-size:13.5px;"><?php echo !empty($b['due_date']) ? date('d-M-Y', strtotime((string)$b['due_date'])) : '-'; ?></strong>
                    </div>
                </div>

                <!-- Meter Reading Stats + Actual Meter Photo -->
                <div class="meter-reading-section" style="background:#ffffff;border:1px solid #cbd5e1;border-radius:10px;padding:14px 16px;margin-bottom:16px;display:grid;grid-template-columns:3fr 1.2fr;gap:14px;align-items:center;">
                    <div>
                        <div style="font-size:11.5px;font-weight:800;color:#0284c7;text-transform:uppercase;margin-bottom:8px;display:flex;align-items:center;gap:6px;">
                            <i class="fa fa-tachometer"></i> <?php echo __('Meter & Reading Information'); ?>
                        </div>
                        <div style="display:grid;grid-template-columns:repeat(4, 1fr);gap:10px;font-size:12.5px;">
                            <div>
                                <span style="font-size:10.5px;color:#64748b;display:block;"><?php echo __('Utility Type'); ?></span>
                                <strong style="color:#0f172a;"><?php echo h(!empty($b['utility_type']) ? $b['utility_type'] : 'Electricity'); ?></strong>
                            </div>
                            <div>
                                <span style="font-size:10.5px;color:#64748b;display:block;"><?php echo __('Previous'); ?></span>
                                <strong style="color:#0f172a;"><?php echo number_format((float)$b['previous_reading'], 2); ?></strong>
                            </div>
                            <div>
                                <span style="font-size:10.5px;color:#64748b;display:block;"><?php echo __('Current'); ?></span>
                                <strong style="color:#0f172a;"><?php echo number_format((float)$b['current_reading'], 2); ?></strong>
                            </div>
                            <div>
                                <span style="font-size:10.5px;color:#64748b;display:block;"><?php echo __('Units Consumed'); ?></span>
                                <strong style="color:#0284c7;"><?php echo number_format((float)$b['units_consumed'], 2); ?></strong>
                            </div>
                        </div>
                    </div>

                    <!-- Meter Image Thumbnail / Lightbox Box -->
                    <div style="text-align:center;">
                        <?php if(!empty($meterImageUrl)){ ?>
                            <div style="background:#f8fafc;border:1px solid #cbd5e1;border-radius:8px;padding:6px;">
                                <div style="font-size:9.5px;font-weight:800;color:#475569;text-transform:uppercase;margin-bottom:3px;"><?php echo __('Last Meter Photo'); ?></div>
                                <a href="<?php echo h($this->Html->url($meterImageUrl)); ?>" target="_blank" title="Click to inspect full meter photo">
                                    <img src="<?php echo h($this->Html->url($meterImageUrl)); ?>" alt="Meter Photo" style="width:100%;max-width:140px;height:75px;object-fit:cover;border-radius:6px;border:1px solid #94a3b8;box-shadow:0 2px 6px rgba(0,0,0,0.12);" />
                                </a>
                                <div style="font-size:9px;color:#0284c7;font-weight:700;margin-top:2px;"><i class="fa fa-search-plus"></i> View Image</div>
                            </div>
                        <?php } else { ?>
                            <div style="background:#f8fafc;border:1px dashed #cbd5e1;border-radius:8px;padding:10px;text-align:center;">
                                <i class="fa fa-tachometer text-info" style="font-size:20px;"></i>
                                <div style="font-size:9.5px;font-weight:800;color:#334155;margin-top:2px;"><?php echo __('Verified Reading'); ?></div>
                            </div>
                        <?php } ?>
                    </div>
                </div>

                <!-- Amount Summary & Action Buttons -->
                <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;border-top:1px solid #e2e8f0;padding-top:14px;" class="bill-bottom-actions">
                    <div>
                        <span style="font-size:11.5px;color:#64748b;display:block;"><?php echo __('Total Amount Payable:'); ?></span>
                        <span style="font-size:24px;font-weight:900;color:#0284c7;">PKR <?php echo number_format((float)$b['total_amount'], 2); ?></span>
                    </div>

                    <div style="display:flex;gap:8px;flex-wrap:wrap;">
                        <?php if(!$isPaid){ ?>
                            <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#modalSubmitPayment" data-bs-toggle="modal" data-bs-target="#modalSubmitPayment" onclick="openSubmitPaymentModal()" style="background:#f59e0b;border-color:#d97706;font-weight:800;padding:9px 18px;border-radius:8px;color:#fff;display:inline-flex;align-items:center;gap:6px;box-shadow:0 4px 12px rgba(245,158,11,0.25);font-size:13px;cursor:pointer;">
                                <i class="fa fa-upload"></i> <?php echo __('Pay Online / Submit Proof'); ?>
                            </button>
                        <?php } ?>
                        <a href="<?php echo $this->Html->url(array('controller' => 'BillingBills', 'action' => 'print_bill', $b['id'])); ?>" target="_blank" class="btn btn-primary" style="background:#0284c7;border-color:#0284c7;font-weight:800;padding:9px 18px;border-radius:8px;text-decoration:none;color:#fff;display:inline-flex;align-items:center;gap:6px;box-shadow:0 4px 12px rgba(2,132,199,0.2);font-size:13px;">
                            <i class="fa fa-print"></i> <?php echo __('Print / Download Bill'); ?>
                        </a>
                        <?php if($isPaid || (float)$b['paid_amount'] > 0){ ?>
                            <a href="<?php echo $this->Html->url(array('controller' => 'BillingBills', 'action' => 'print_receipt', $b['id'])); ?>" target="_blank" class="btn btn-success" style="background:#16a34a;border-color:#16a34a;font-weight:800;padding:9px 18px;border-radius:8px;text-decoration:none;color:#fff;display:inline-flex;align-items:center;gap:6px;box-shadow:0 4px 12px rgba(22,163,74,0.2);font-size:13px;">
                                <i class="fa fa-file-text-o"></i> <?php echo __('Payment Receipt'); ?>
                            </a>
                        <?php } ?>
                    </div>
                </div>
            </div>

            <!-- History View (If Tab is History) -->
            <?php if($activeTab === 'history' && !empty($history)){ ?>
                <div class="portal-card">
                    <h4 style="font-size:15px;font-weight:800;color:#0f172a;margin:0 0 14px 0;">
                        <i class="fa fa-history text-primary"></i> <?php echo __('Previous Billing History (Last 12 Months)'); ?>
                    </h4>
                    <div class="table-responsive">
                        <table style="width:100%;border-collapse:collapse;font-size:12.5px;">
                            <thead>
                                <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;text-align:left;">
                                    <th style="padding:8px 10px;color:#475569;"><?php echo __('Billing Month'); ?></th>
                                    <th style="padding:8px 10px;color:#475569;"><?php echo __('Bill #'); ?></th>
                                    <th style="padding:8px 10px;color:#475569;text-align:right;"><?php echo __('Total (PKR)'); ?></th>
                                    <th style="padding:8px 10px;color:#475569;text-align:right;"><?php echo __('Paid (PKR)'); ?></th>
                                    <th style="padding:8px 10px;color:#475569;text-align:center;"><?php echo __('Status'); ?></th>
                                    <th style="padding:8px 10px;color:#475569;text-align:center;"><?php echo __('Action'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($history as $hb){ 
                                    $hbi = $hb['BillingBill'];
                                    $hPaid = ($hbi['payment_status'] === 'paid');
                                ?>
                                    <tr style="border-bottom:1px solid #f1f5f9;">
                                        <td style="padding:8px 10px;font-weight:600;"><?php echo date('M Y', strtotime($hbi['billing_period'] . '-01')); ?></td>
                                        <td style="padding:8px 10px;font-family:monospace;"><?php echo h($hbi['bill_number']); ?></td>
                                        <td style="padding:8px 10px;text-align:right;font-weight:700;"><?php echo number_format((float)$hbi['total_amount'], 2); ?></td>
                                        <td style="padding:8px 10px;text-align:right;color:#16a34a;font-weight:700;"><?php echo number_format((float)$hbi['paid_amount'], 2); ?></td>
                                        <td style="padding:8px 10px;text-align:center;">
                                            <?php if($hPaid){ ?>
                                                <span style="background:#dcfce7;color:#15803d;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:700;">Paid</span>
                                            <?php } else { ?>
                                                <span style="background:#fee2e2;color:#b91c1c;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:700;">Unpaid</span>
                                            <?php } ?>
                                        </td>
                                        <td style="padding:8px 10px;text-align:center;">
                                            <a href="<?php echo $this->Html->url(array('controller' => 'BillingBills', 'action' => 'print_bill', $hbi['id'])); ?>" target="_blank" style="color:#0284c7;font-weight:700;text-decoration:none;">
                                                <i class="fa fa-print"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php } ?>

        <?php } else { ?>
            <!-- Not Found Box -->
            <div class="portal-card" style="text-align:center;padding:28px 18px;border-color:#fecaca;background:#fff5f5;">
                <div style="font-size:32px;color:#ef4444;margin-bottom:6px;">🔍</div>
                <h4 style="font-weight:800;color:#991b1b;margin:0 0 4px 0;"><?php echo __('No Bill Found'); ?></h4>
                <p style="color:#7f1d1d;font-size:13px;max-width:480px;margin:0 auto 12px auto;">
                    <?php echo __('Could not find any bill matching') . ' "<strong>' . h($query) . '</strong>". ' . __('Please check your Consumer ID or Bill Number from your printed bill and try again.'); ?>
                </p>
                <a href="<?php echo $this->Html->url(array('action' => 'check_bill')); ?>" class="btn-search-blue" style="display:inline-block;text-decoration:none;margin-top:6px;">
                    <i class="fa fa-arrow-left"></i> <?php echo __('Back to Search'); ?>
                </a>
            </div>
        <?php } ?>
    <?php } ?>

    <!-- ========================================== -->
    <!-- 2. URDU INSTRUCTIONS & SAMPLE CARD         -->
    <!-- ========================================== -->
    <div class="portal-card" style="padding:32px 28px;">
        <!-- Centered Urdu Title -->
        <h2 class="urdu-font" style="text-align:center;font-size:30px;font-weight:700;color:#0f172a;margin:0 0 24px 0;">
            اپنا بل کیسے دیکھیں؟
        </h2>

        <!-- Sub Card Grid Container -->
        <div style="background:#f8fafc;border:1px solid #f1f5f9;border-radius:14px;padding:24px;">
            <div class="row" style="display:flex;flex-wrap:wrap;align-items:center;">
                
                <!-- Left Column: Sample Voucher Card -->
                <div class="col-md-5 col-sm-12" style="margin-bottom:16px;">
                    <div style="border:2px dashed #cbd5e1;border-radius:12px;background:#ffffff;padding:24px 20px;text-align:center;">
                        <div style="font-size:11px;font-weight:800;color:#94a3b8;letter-spacing:1.5px;text-transform:uppercase;margin-bottom:18px;">
                            SAMPLE FROM YOUR BILL
                        </div>

                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
                            <span style="font-size:13px;font-weight:700;color:#334155;">Consumer ID:</span>
                            <div style="display:flex;align-items:center;gap:8px;">
                                <span style="background:#fef08a;color:#854d0e;font-family:monospace;font-weight:800;font-size:13px;padding:4px 12px;border-radius:4px;">WV7000</span>
                                <span style="color:#eab308;font-size:18px;font-weight:900;">&larr;</span>
                            </div>
                        </div>

                        <div style="display:flex;justify-content:space-between;align-items:center;">
                            <span style="font-size:13px;font-weight:700;color:#334155;">Bill No:</span>
                            <div style="display:flex;align-items:center;gap:8px;">
                                <span style="background:#fef08a;color:#854d0e;font-family:monospace;font-weight:800;font-size:13px;padding:4px 12px;border-radius:4px;">123456</span>
                                <span style="color:#eab308;font-size:18px;font-weight:900;">&larr;</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Urdu Step Instructions & WhatsApp Button -->
                <div class="col-md-7 col-sm-12">
                    <div class="urdu-font" style="font-size:15px;color:#1e293b;line-height:2.2;margin-bottom:20px;">
                        <div style="display:flex;align-items:flex-start;justify-content:flex-end;gap:10px;margin-bottom:10px;">
                            <span>اپنا "Consumer ID" یا "Bill Number" اوپر دیے گئے خانے میں درج کریں۔</span>
                            <span style="color:#22c55e;font-size:17px;margin-top:2px;"><i class="fa fa-check-circle"></i></span>
                        </div>
                        <div style="display:flex;align-items:flex-start;justify-content:flex-end;gap:10px;margin-bottom:10px;">
                            <span>"Search Bill" بٹن پر کلک کر کے اپنا تازہ ترین بل یا پچھلی ہسٹری دیکھیں۔</span>
                            <span style="color:#22c55e;font-size:17px;margin-top:2px;"><i class="fa fa-check-circle"></i></span>
                        </div>
                        <div style="display:flex;align-items:flex-start;justify-content:flex-end;gap:10px;">
                            <span>بل چیک کرنے یا ڈاؤن لوڈ کرنے میں کسی بھی قسم کی دشواری کی صورت میں براہ کرم ہم سے رابطہ کریں۔</span>
                            <span style="color:#3b82f6;font-size:17px;margin-top:2px;"><i class="fa fa-info-circle"></i></span>
                        </div>
                    </div>

                    <!-- WhatsApp CTA Button -->
                    <a href="https://wa.me/923286694001" target="_blank" style="background:#22c55e;color:#ffffff;font-weight:700;font-size:16px;padding:12px 20px;border-radius:10px;display:flex;align-items:center;justify-content:center;gap:10px;text-decoration:none;box-shadow:0 4px 14px rgba(34,197,94,0.35);transition:all 0.2s;">
                        <i class="fa fa-whatsapp" style="font-size:22px;"></i> Support: 03286694001
                    </a>
                </div>

            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- 3. BOTTOM CARDS: PAYMENT OPTIONS & QR CODE -->
    <!-- ========================================== -->
    <div style="display:grid;grid-template-columns:2fr 1.1fr;gap:20px;margin-top:20px;" class="portal-bottom-grid">
        
        <!-- Left Card: PAYMENT OPTIONS (Dark Indigo Gradient) -->
        <div style="background:linear-gradient(135deg, #1e1b4b 0%, #312e81 100%);color:#ffffff;border-radius:16px;padding:24px;position:relative;overflow:hidden;box-shadow:0 10px 25px -5px rgba(30,27,75,0.4);">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;">
                <div style="display:flex;align-items:center;gap:8px;font-size:13px;font-weight:800;letter-spacing:1px;text-transform:uppercase;">
                    <span style="background:rgba(255,255,255,0.15);padding:4px 8px;border-radius:6px;">💳</span>
                    <span>PAYMENT OPTIONS</span>
                </div>
                <span style="background:#facc15;color:#000000;font-weight:800;font-size:10px;padding:3px 10px;border-radius:12px;text-transform:uppercase;letter-spacing:0.5px;">
                    DIRECT TRANSFER
                </span>
            </div>

            <!-- Bank Transfer Box -->
            <div style="background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.12);border-radius:10px;padding:12px 16px;margin-bottom:12px;">
                <div style="font-size:9.5px;font-weight:800;color:#93c5fd;letter-spacing:0.8px;text-transform:uppercase;margin-bottom:3px;">
                    BANK TRANSFER
                </div>
                <div style="font-size:16px;font-weight:900;font-family:monospace;letter-spacing:1px;color:#ffffff;">
                    0773310485346
                </div>
            </div>

            <!-- Jazz Cash Box -->
            <div style="background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.12);border-radius:10px;padding:12px 16px;margin-bottom:16px;">
                <div style="font-size:9.5px;font-weight:800;color:#93c5fd;letter-spacing:0.8px;text-transform:uppercase;margin-bottom:3px;">
                    JAZZ CASH
                </div>
                <div style="font-size:16px;font-weight:900;font-family:monospace;letter-spacing:1px;color:#ffffff;">
                    TID 981452707
                </div>
            </div>

            <!-- Subtext & WhatsApp Link -->
            <div style="font-size:11px;color:#cbd5e1;line-height:1.5;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
                <span>Kindly pay your bill within due date. Verify our Account title and share receipt to Whatsapp:</span>
                <span style="background:#22c55e;color:#ffffff;font-weight:800;padding:2px 8px;border-radius:4px;font-size:10.5px;">03286694001</span>
            </div>
        </div>

        <!-- Right Card: PAY VIA APP (Orange / Amber Gradient) -->
        <div style="background:linear-gradient(135deg, #ea580c 0%, #f97316 100%);color:#ffffff;border-radius:16px;padding:24px;text-align:center;display:flex;flex-direction:column;align-items:center;justify-content:space-between;box-shadow:0 10px 25px -5px rgba(234,88,12,0.4);">
            <div style="font-size:12px;font-weight:900;letter-spacing:1.5px;text-transform:uppercase;margin-bottom:14px;">
                PAY VIA APP
            </div>

            <!-- QR Code Frame -->
            <div style="background:#ffffff;padding:10px;border-radius:12px;box-shadow:0 4px 12px rgba(0,0,0,0.15);margin-bottom:16px;">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=140x140&data=JAZZCASH:981452707" alt="Pay QR" style="width:130px;height:130px;display:block;" />
            </div>

            <!-- Scan Button -->
            <button type="button" style="background:#ffffff;color:#ea580c;font-weight:900;font-size:11.5px;letter-spacing:1px;text-transform:uppercase;border:none;padding:8px 20px;border-radius:20px;cursor:pointer;display:inline-flex;align-items:center;gap:6px;box-shadow:0 2px 8px rgba(0,0,0,0.1);">
                <i class="fa fa-qrcode"></i> SCAN TO PAY
            </button>
        </div>

    </div>

</div>

<script>
    // Responsive adjustments for bottom grid on small devices
    if(window.innerWidth < 768) {
        var grid = document.querySelector('.portal-bottom-grid');
        if(grid) grid.style.gridTemplateColumns = '1fr';
    }

    function openSubmitPaymentModal() {
        var modalEl = document.getElementById('modalSubmitPayment');
        if (!modalEl) return;
        
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            var modalObj = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
            modalObj.show();
        } else if (typeof $ !== 'undefined' && $.fn && $.fn.modal) {
            $('#modalSubmitPayment').modal('show');
        } else {
            modalEl.style.display = 'block';
            modalEl.classList.add('show');
            modalEl.style.backgroundColor = 'rgba(0,0,0,0.5)';
        }
    }

    function closeSubmitPaymentModal() {
        var modalEl = document.getElementById('modalSubmitPayment');
        if (!modalEl) return;
        
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            var modalObj = bootstrap.Modal.getInstance(modalEl);
            if (modalObj) modalObj.hide();
        }
        if (typeof $ !== 'undefined' && $.fn && $.fn.modal) {
            $('#modalSubmitPayment').modal('hide');
        }
        modalEl.style.display = 'none';
        modalEl.classList.remove('show');
    }
</script>

<?php if(!empty($bill)){ 
    $b = $bill['BillingBill']; 
    $c = $bill['BillingClient'];
    $remPayable = max(0.0, (float)$b['total_amount'] - (float)$b['paid_amount']);
?>
    <!-- Modal Submit Payment Request -->
    <div class="modal fade" id="modalSubmitPayment" tabindex="-1" role="dialog" aria-labelledby="modalSubmitPaymentLabel">
        <div class="modal-dialog" role="document">
            <form method="post" action="<?php echo $this->Html->url(array('action' => 'submit_payment')); ?>" enctype="multipart/form-data">
                <input type="hidden" name="data[PaymentRef][bill_id]" value="<?php echo (int)$b['id']; ?>" />
                <input type="hidden" name="data[PaymentRef][billing_client_id]" value="<?php echo (int)$b['billing_client_id']; ?>" />
                <input type="hidden" name="data[PaymentRef][query_no]" value="<?php echo h($query); ?>" />

                <div class="modal-content" style="border-radius:14px;overflow:hidden;">
                    <div class="modal-header" style="background:#0284c7;color:#ffffff;padding:16px 20px;display:flex;align-items:center;justify-content:space-between;">
                        <h4 class="modal-title" style="font-weight:800;font-size:16px;margin:0;">
                            <i class="fa fa-credit-card"></i> <?php echo __('Submit Online Payment Proof'); ?>
                        </h4>
                        <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" onclick="closeSubmitPaymentModal()" style="color:#ffffff;opacity:0.9;font-size:24px;border:none;background:none;cursor:pointer;line-height:1;">&times;</button>
                    </div>
                    <div class="modal-body" style="padding:20px;">
                        <!-- Summary Info Header -->
                        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:12px 14px;margin-bottom:16px;">
                            <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:4px;">
                                <span style="color:#64748b;"><?php echo __('Bill Number:'); ?></span>
                                <strong style="color:#0f172a;font-family:monospace;"><?php echo h($b['bill_number']); ?></strong>
                            </div>
                            <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:4px;">
                                <span style="color:#64748b;"><?php echo __('Consumer Name:'); ?></span>
                                <strong style="color:#0f172a;"><?php echo h(!empty($c['name']) ? $c['name'] : '-'); ?></strong>
                            </div>
                            <div style="display:flex;justify-content:space-between;font-size:13.5px;">
                                <span style="color:#64748b;"><?php echo __('Total Remaining Due:'); ?></span>
                                <strong style="color:#0284c7;font-size:15px;">PKR <?php echo number_format($remPayable, 2); ?></strong>
                            </div>
                        </div>

                        <!-- Amount Input -->
                        <div class="form-group" style="margin-bottom:14px;">
                            <label style="font-size:13px;font-weight:700;color:#334155;"><?php echo __('Paid Amount (PKR)'); ?> *</label>
                            <input type="number" step="0.01" name="data[PaymentRef][amount]" value="<?php echo $remPayable; ?>" class="form-control" required style="border-radius:6px;padding:9px 12px;font-size:14px;" />
                        </div>

                        <!-- Payment Method -->
                        <div class="form-group" style="margin-bottom:14px;">
                            <label style="font-size:13px;font-weight:700;color:#334155;"><?php echo __('Payment Method'); ?> *</label>
                            <select name="data[PaymentRef][payment_method]" class="form-control" style="border-radius:6px;padding:9px 12px;font-size:14px;" required>
                                <option value="JazzCash">JazzCash (TID: 981452707)</option>
                                <option value="EasyPaisa">EasyPaisa</option>
                                <option value="Bank Transfer">Bank Transfer (0773310485346)</option>
                                <option value="Raast">Raast Instant Payment</option>
                                <option value="Cash Deposit">Cash Deposit Slip</option>
                            </select>
                        </div>

                        <!-- TID / Ref Number -->
                        <div class="form-group" style="margin-bottom:14px;">
                            <label style="font-size:13px;font-weight:700;color:#334155;"><?php echo __('Transaction Reference / TID Number'); ?> *</label>
                            <input type="text" name="data[PaymentRef][transaction_ref]" class="form-control" placeholder="e.g. TID 981452707 or Bank Ref #" required style="border-radius:6px;padding:9px 12px;font-size:14px;" />
                        </div>

                        <!-- Screenshot Proof File -->
                        <div class="form-group" style="margin-bottom:14px;">
                            <label style="font-size:13px;font-weight:700;color:#334155;"><?php echo __('Upload Payment Screenshot / Receipt Image'); ?></label>
                            <input type="file" name="slip_file" accept="image/*" class="form-control" style="border-radius:6px;padding:6px 10px;font-size:13px;" />
                            <small class="text-muted" style="font-size:11px;"><?php echo __('Attach screenshot of JazzCash, Bank Transfer or EasyPaisa confirmation screen.'); ?></small>
                        </div>

                        <!-- Optional Notes -->
                        <div class="form-group" style="margin-bottom:0;">
                            <label style="font-size:13px;font-weight:700;color:#334155;"><?php echo __('Account Title / Optional Notes'); ?></label>
                            <textarea name="data[PaymentRef][notes]" class="form-control" rows="2" placeholder="Sender Name or additional notes..." style="border-radius:6px;padding:8px 12px;font-size:13px;"></textarea>
                        </div>
                    </div>

                    <div class="modal-footer" style="background:#f8fafc;padding:14px 20px;">
                        <button type="button" class="btn btn-default" data-dismiss="modal" data-bs-dismiss="modal" onclick="closeSubmitPaymentModal()" style="font-weight:600;"><?php echo __('Cancel'); ?></button>
                        <button type="submit" class="btn btn-primary" style="background:#0284c7;border-color:#0284c7;font-weight:800;padding:8px 20px;">
                            <i class="fa fa-paper-plane"></i> <?php echo __('Submit Payment Proof'); ?>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
<?php } ?>
