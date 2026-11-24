<?php
/**
 * Billing Manager Admin Dashboard
 * Exact WordPress Plugin UI & Behavior Replication with All Cards Clickable
 */
?>
<style>
    .subm-dash-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-weight: 700;
        font-size: 12.5px;
        padding: 7px 14px;
        border-radius: 6px;
        text-decoration: none;
        color: #ffffff !important;
        border: none;
        cursor: pointer;
        transition: all 0.15s ease-in-out;
    }
    .subm-dash-btn:hover {
        opacity: 0.92;
        transform: translateY(-1px);
    }
    .btn-navy { background: #1e3a8a; }
    .btn-emerald { background: #10b981; }
    .btn-sky { background: #0284c7; }
    .btn-amber { background: #d97706; }
    .btn-rose { background: #e11d48; }

    .subm-kpi-card {
        border-radius: 10px;
        color: #ffffff !important;
        padding: 14px 18px;
        position: relative;
        overflow: hidden;
        min-height: 84px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        box-shadow: 0 3px 8px rgba(0,0,0,0.08);
        text-decoration: none !important;
        cursor: pointer;
        transition: transform 0.18s ease, box-shadow 0.18s ease;
    }
    .subm-kpi-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.2) !important;
        color: #ffffff !important;
    }
    .subm-kpi-card .kpi-num {
        font-size: 26px;
        font-weight: 900;
        line-height: 1;
    }
    .subm-kpi-card .kpi-lbl {
        font-size: 11.5px;
        font-weight: 700;
        opacity: 0.95;
        margin-top: 4px;
    }
    .subm-kpi-card .kpi-icon {
        position: absolute;
        right: 14px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 32px;
        opacity: 0.25;
        transition: opacity 0.18s ease;
    }
    .subm-kpi-card:hover .kpi-icon {
        opacity: 0.45;
    }

    .fin-card {
        border-radius: 10px;
        color: #ffffff !important;
        padding: 20px 22px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        position: relative;
        text-decoration: none !important;
        display: block;
        cursor: pointer;
        transition: transform 0.18s ease, box-shadow 0.18s ease;
    }
    .fin-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 24px rgba(0,0,0,0.22) !important;
        color: #ffffff !important;
    }
    .fin-card .fin-amt {
        font-size: 22px;
        font-weight: 900;
        line-height: 1.1;
    }
    .fin-card .fin-lbl {
        font-size: 12px;
        font-weight: 600;
        opacity: 0.9;
        margin-top: 6px;
    }
    .fin-card .fin-icon {
        position: absolute;
        right: 18px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 28px;
        opacity: 0.3;
        transition: opacity 0.18s ease;
    }
    .fin-card:hover .fin-icon {
        opacity: 0.5;
    }
</style>

<div class="billing-dashboard" style="font-family:'Inter', -apple-system, BlinkMacSystemFont, sans-serif;">

    <!-- ========================================== -->
    <!-- 1. DASHBOARD HEADER & LIVE OVERVIEW        -->
    <!-- ========================================== -->
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;flex-wrap:wrap;gap:10px;">
        <div>
            <h1 style="font-size:26px;font-weight:900;color:#0f172a;margin:0;">Dashboard</h1>
            <p style="color:#64748b;font-size:12.5px;margin:2px 0 0 0;">Live utility billing overview</p>
        </div>
        <div style="background:#0f172a;color:#ffffff;padding:6px 14px;border-radius:20px;font-size:12px;font-weight:700;display:inline-flex;align-items:center;gap:6px;">
            <i class="fa fa-calendar text-info"></i> <?php echo date('F Y \- d-M-Y h:i a'); ?>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- 2. TOP ACTION BUTTONS TOOLBAR              -->
    <!-- ========================================== -->
    <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:10px;padding:12px 16px;margin-bottom:20px;display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
        <a href="<?php echo $this->Html->url(array('controller' => 'BillingBills', 'action' => 'index')); ?>" class="subm-dash-btn btn-navy">
            <i class="fa fa-file-text-o"></i> Bill
        </a>
        <a href="<?php echo $this->Html->url(array('controller' => 'BillingReadings', 'action' => 'bulk_entry')); ?>" class="subm-dash-btn btn-sky">
            <i class="fa fa-tachometer"></i> Reading
        </a>
        <a href="<?php echo $this->Html->url(array('controller' => 'BillingPaymentRequests', 'action' => 'index')); ?>" class="subm-dash-btn btn-rose">
            <i class="fa fa-credit-card"></i> Payment Paid Requests
        </a>
        <a href="<?php echo $this->Html->url(array('controller' => 'BillingBills', 'action' => 'index', '?' => array('status' => 'unpaid'))); ?>" class="subm-dash-btn btn-emerald">
            <i class="fa fa-check-circle"></i> Pay Any Bill
        </a>
        <a href="<?php echo $this->Html->url(array('controller' => 'BillingBills', 'action' => 'generate_bulk')); ?>" class="subm-dash-btn btn-navy">
            <i class="fa fa-cubes"></i> Create Bulk Bill
        </a>
        <a href="<?php echo $this->Html->url(array('controller' => 'BillingBills', 'action' => 'generate_single')); ?>" class="subm-dash-btn btn-navy">
            <i class="fa fa-plus-circle"></i> Create Single Bill
        </a>
        <a href="<?php echo $this->Html->url(array('controller' => 'BillingReports', 'action' => 'index')); ?>" class="subm-dash-btn" style="background:#7c3aed;color:#ffffff;">
            <i class="fa fa-bar-chart"></i> Reports & Recovery Hub
        </a>

        <!-- Created Bills Dropdown Quick Navigator -->
        <div style="display:inline-flex;gap:4px;align-items:center;margin-left:auto;">
            <select id="quickBillSelect" class="form-control input-sm" style="width:140px;font-size:12px;border-radius:6px;">
                <option value="">Created Bills</option>
                <?php foreach($createdBillsList as $bId => $bNo){ ?>
                    <option value="<?php echo $bId; ?>"><?php echo h($bNo); ?></option>
                <?php } ?>
            </select>
            <button type="button" class="subm-dash-btn btn-navy" onclick="openSelectedBill()" style="padding:6px 12px;font-size:11.5px;">
                Open Bill
            </button>
        </div>

        <!-- Meter Ledgers Dropdown Quick Navigator -->
        <div style="display:inline-flex;gap:4px;align-items:center;">
            <select id="quickMeterSelect" class="form-control input-sm" style="width:140px;font-size:12px;border-radius:6px;">
                <option value="">Meter Ledgers</option>
                <?php foreach($meterLedgersList as $mId => $mNo){ ?>
                    <option value="<?php echo $mId; ?>"><?php echo h($mNo); ?></option>
                <?php } ?>
            </select>
            <button type="button" class="subm-dash-btn btn-navy" onclick="openSelectedMeter()" style="padding:6px 12px;font-size:11.5px;">
                Open Ledger
            </button>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- 3. COLORFUL METRIC BADGES ROW (ALL CLICKABLE) -->
    <!-- ========================================== -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(130px, 1fr));gap:12px;margin-bottom:16px;">
        <a href="<?php echo $this->Html->url(array('controller' => 'BillingReports', 'action' => 'index')); ?>" class="subm-kpi-card" style="background:#7c3aed;" title="Click to view Financial Reports & Defaulter Recovery Hub">
            <div class="kpi-num"><i class="fa fa-bar-chart"></i></div>
            <div class="kpi-lbl">Reports & Recovery</div>
            <i class="fa fa-line-chart kpi-icon"></i>
        </a>
        <a href="<?php echo $this->Html->url(array('controller' => 'BillingSocieties', 'action' => 'index')); ?>" class="subm-kpi-card" style="background:#0284c7;" title="Click to manage Societies">
            <div class="kpi-num"><?php echo (int)$societiesCount; ?></div>
            <div class="kpi-lbl">Society</div>
            <i class="fa fa-building kpi-icon"></i>
        </a>
        <a href="<?php echo $this->Html->url(array('controller' => 'BillingClients', 'action' => 'index')); ?>" class="subm-kpi-card" style="background:#ea580c;" title="Click to view all Clients">
            <div class="kpi-num"><?php echo (int)$totalClients; ?></div>
            <div class="kpi-lbl">Client</div>
            <i class="fa fa-users kpi-icon"></i>
        </a>
        <a href="<?php echo $this->Html->url(array('controller' => 'BillingMeters', 'action' => 'index')); ?>" class="subm-kpi-card" style="background:#0891b2;" title="Click to view all Utility Meters">
            <div class="kpi-num"><?php echo (int)$totalMeters; ?></div>
            <div class="kpi-lbl">Meter</div>
            <i class="fa fa-tachometer kpi-icon"></i>
        </a>
        <a href="<?php echo $this->Html->url(array('controller' => 'BillingMeters', 'action' => 'index', '?' => array('utility_type' => 'Electricity'))); ?>" class="subm-kpi-card" style="background:#059669;" title="Click to view Electricity Meters">
            <div class="kpi-num"><?php echo (int)$elecMeters; ?></div>
            <div class="kpi-lbl">Electricity Meters</div>
            <i class="fa fa-lightbulb-o kpi-icon"></i>
        </a>
        <a href="<?php echo $this->Html->url(array('controller' => 'BillingMeters', 'action' => 'index', '?' => array('utility_type' => 'Water'))); ?>" class="subm-kpi-card" style="background:#2563eb;" title="Click to view Water Meters">
            <div class="kpi-num"><?php echo (int)$waterMeters; ?></div>
            <div class="kpi-lbl">Water Meters</div>
            <i class="fa fa-tint kpi-icon"></i>
        </a>
        <a href="<?php echo $this->Html->url(array('controller' => 'BillingMeters', 'action' => 'index', '?' => array('utility_type' => 'Gas'))); ?>" class="subm-kpi-card" style="background:#e11d48;" title="Click to view Gas Meters">
            <div class="kpi-num"><?php echo (int)$gasMeters; ?></div>
            <div class="kpi-lbl">Gas Meters</div>
            <i class="fa fa-fire kpi-icon"></i>
        </a>
        <a href="<?php echo $this->Html->url(array('controller' => 'BillingMeters', 'action' => 'index')); ?>" class="subm-kpi-card" style="background:#d97706;" title="Click to view Meters with Number">
            <div class="kpi-num"><?php echo (int)$metersWithNumber; ?></div>
            <div class="kpi-lbl">Meters With Number</div>
            <i class="fa fa-list-ol kpi-icon"></i>
        </a>
        <a href="<?php echo $this->Html->url(array('controller' => 'BillingProperties', 'action' => 'index')); ?>" class="subm-kpi-card" style="background:#0f766e;" title="Click to view all Properties">
            <div class="kpi-num"><?php echo (int)$totalProperties; ?></div>
            <div class="kpi-lbl">Properties</div>
            <i class="fa fa-home kpi-icon"></i>
        </a>
    </div>

    <!-- Bills Status Badges Row (ALL CLICKABLE) -->
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-bottom:24px;">
        <a href="<?php echo $this->Html->url(array('controller' => 'BillingBills', 'action' => 'index')); ?>" class="subm-kpi-card" style="background:#1e3a8a;" title="Click to view Total Bills">
            <div class="kpi-num"><?php echo (int)$totalBillsCount; ?></div>
            <div class="kpi-lbl">Total Bills</div>
            <i class="fa fa-file-text kpi-icon"></i>
        </a>
        <a href="<?php echo $this->Html->url(array('controller' => 'BillingBills', 'action' => 'index', '?' => array('status' => 'paid'))); ?>" class="subm-kpi-card" style="background:#0d9488;" title="Click to view Paid Bills">
            <div class="kpi-num"><?php echo (int)$paidBillsCount; ?></div>
            <div class="kpi-lbl">Paid Bills</div>
            <i class="fa fa-check-circle kpi-icon"></i>
        </a>
        <a href="<?php echo $this->Html->url(array('controller' => 'BillingBills', 'action' => 'index', '?' => array('status' => 'unpaid'))); ?>" class="subm-kpi-card" style="background:#be123c;" title="Click to view Unpaid Bills">
            <div class="kpi-num"><?php echo (int)$unpaidBillsCount; ?></div>
            <div class="kpi-lbl">Un-Paid Bills</div>
            <i class="fa fa-times-circle kpi-icon"></i>
        </a>
    </div>

    <!-- ========================================== -->
    <!-- 4. FINANCIAL CARDS WITH DATE FILTER (ALL CLICKABLE) -->
    <!-- ========================================== -->
    <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:12px;padding:20px;margin-bottom:24px;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;flex-wrap:wrap;gap:12px;">
            <h3 style="font-size:16px;font-weight:800;color:#0f172a;margin:0;">Financial Cards</h3>

            <!-- Filter Controls Form -->
            <form method="get" action="<?php echo $this->Html->url(array('action' => 'index')); ?>" style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                <div>
                    <label style="font-size:11px;color:#64748b;font-weight:700;display:block;margin-bottom:2px;">Meter Type</label>
                    <select name="meter_type" class="form-control input-sm" style="font-size:12px;">
                        <option value="">All</option>
                        <option value="electricity" <?php if($meterTypeFilter === 'electricity') echo 'selected'; ?>>Electricity</option>
                        <option value="water" <?php if($meterTypeFilter === 'water') echo 'selected'; ?>>Water</option>
                        <option value="gas" <?php if($meterTypeFilter === 'gas') echo 'selected'; ?>>Gas</option>
                    </select>
                </div>
                <div>
                    <label style="font-size:11px;color:#64748b;font-weight:700;display:block;margin-bottom:2px;">From Date</label>
                    <input type="date" name="from_date" value="<?php echo h($fromDate); ?>" class="form-control input-sm" style="font-size:12px;" />
                </div>
                <div>
                    <label style="font-size:11px;color:#64748b;font-weight:700;display:block;margin-bottom:2px;">To Date</label>
                    <input type="date" name="to_date" value="<?php echo h($toDate); ?>" class="form-control input-sm" style="font-size:12px;" />
                </div>
                <div style="padding-top:16px;display:flex;gap:4px;">
                    <button type="submit" class="btn btn-sm btn-primary" style="font-weight:700;padding:5px 14px;background:#0284c7;border-color:#0284c7;">Apply</button>
                    <a href="<?php echo $this->Html->url(array('action' => 'index')); ?>" class="btn btn-sm btn-default" style="font-weight:700;padding:5px 12px;">Reset</a>
                </div>
            </form>
        </div>

        <!-- 4 Big Financial Cards (ALL CLICKABLE) -->
        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(210px, 1fr));gap:14px;">
            <a href="<?php echo $this->Html->url(array('controller' => 'BillingBills', 'action' => 'index')); ?>" class="fin-card" style="background:#0284c7;" title="Click to view all Bill Amount details">
                <div class="fin-amt">PKR <?php echo number_format($totalBillAmount, 2); ?></div>
                <div class="fin-lbl">Total Bill (Amount)</div>
                <i class="fa fa-bar-chart fin-icon"></i>
            </a>
            <a href="<?php echo $this->Html->url(array('controller' => 'BillingBills', 'action' => 'index', '?' => array('status' => 'paid'))); ?>" class="fin-card" style="background:#0d9488;" title="Click to view Paid Bills Amount">
                <div class="fin-amt">PKR <?php echo number_format($paidBillAmount, 2); ?></div>
                <div class="fin-lbl">Paid Bill (Amount)</div>
                <i class="fa fa-check-circle fin-icon"></i>
            </a>
            <a href="<?php echo $this->Html->url(array('controller' => 'BillingBills', 'action' => 'index', '?' => array('status' => 'unpaid', 'overdue' => '1'))); ?>" class="fin-card" style="background:#be123c;" title="Click to view Overdue Bills Amount">
                <div class="fin-amt">PKR <?php echo number_format($overdueAmount, 2); ?></div>
                <div class="fin-lbl">Overdue (Amount)</div>
                <i class="fa fa-exclamation-triangle fin-icon"></i>
            </a>
            <a href="<?php echo $this->Html->url(array('controller' => 'BillingBills', 'action' => 'index', '?' => array('status' => 'unpaid'))); ?>" class="fin-card" style="background:#ea580c;" title="Click to view Un-Paid Bills Amount">
                <div class="fin-amt">PKR <?php echo number_format($unpaidAmount, 2); ?></div>
                <div class="fin-lbl">Un-Paid (Amount)</div>
                <i class="fa fa-clock-o fin-icon"></i>
            </a>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- 5. ANALYTICS: CHARTS & STATUS GRAPHS       -->
    <!-- ========================================== -->
    <div style="display:grid;grid-template-columns:2fr 1.2fr;gap:20px;margin-bottom:24px;">
        
        <!-- Collection Summary & Bill Status Graph -->
        <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:12px;padding:20px;">
            <!-- Collection Summary Banner (CLICKABLE) -->
            <a href="<?php echo $this->Html->url(array('controller' => 'BillingBills', 'action' => 'index')); ?>" style="text-decoration:none;display:block;" title="Click to view Collection Details">
                <div style="background:#0f172a;color:#ffffff;border-radius:10px;padding:18px 22px;margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;transition:transform 0.15s ease;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">
                    <div>
                        <span style="font-size:11px;color:#94a3b8;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;">Collection Summary</span>
                        <div style="font-size:24px;font-weight:900;color:#38bdf8;margin-top:2px;">PKR <?php echo number_format($paidBillAmount, 2); ?></div>
                    </div>
                    <div style="text-align:right;">
                        <span style="font-size:12px;color:#cbd5e1;font-weight:600;">Total Outstanding:</span>
                        <div style="font-size:18px;font-weight:900;color:#f87171;">PKR <?php echo number_format($unpaidAmount, 2); ?></div>
                    </div>
                </div>
            </a>

            <!-- Bill Status Progress Bars (ALL CLICKABLE) -->
            <h4 style="font-size:14px;font-weight:800;color:#0f172a;margin:0 0 14px 0;">Bill Status Graph</h4>
            <?php 
                $totForPct = max(1, $totalBillsCount);
                $paidPct = round(($paidBillsCount / $totForPct) * 100);
                $partPct = round(($partialBillsCount / $totForPct) * 100);
                $unpaidPct = round(($unpaidBillsCount / $totForPct) * 100);
            ?>
            <a href="<?php echo $this->Html->url(array('controller' => 'BillingBills', 'action' => 'index', '?' => array('status' => 'paid'))); ?>" style="text-decoration:none;display:block;margin-bottom:12px;" title="Click to filter Paid Bills">
                <div style="display:flex;justify-content:space-between;font-size:12px;font-weight:700;color:#334155;margin-bottom:4px;">
                    <span>Paid (<?php echo (int)$paidBillsCount; ?>)</span>
                    <span><?php echo $paidPct; ?>%</span>
                </div>
                <div class="progress" style="height:10px;background:#e2e8f0;border-radius:6px;margin:0;">
                    <div class="progress-bar" style="width:<?php echo $paidPct; ?>%;background:#16a34a;"></div>
                </div>
            </a>

            <a href="<?php echo $this->Html->url(array('controller' => 'BillingBills', 'action' => 'index', '?' => array('status' => 'partial'))); ?>" style="text-decoration:none;display:block;margin-bottom:12px;" title="Click to filter Partial Bills">
                <div style="display:flex;justify-content:space-between;font-size:12px;font-weight:700;color:#334155;margin-bottom:4px;">
                    <span>Partial (<?php echo (int)$partialBillsCount; ?>)</span>
                    <span><?php echo $partPct; ?>%</span>
                </div>
                <div class="progress" style="height:10px;background:#e2e8f0;border-radius:6px;margin:0;">
                    <div class="progress-bar" style="width:<?php echo $partPct; ?>%;background:#d97706;"></div>
                </div>
            </a>

            <a href="<?php echo $this->Html->url(array('controller' => 'BillingBills', 'action' => 'index', '?' => array('status' => 'unpaid'))); ?>" style="text-decoration:none;display:block;" title="Click to filter Unpaid Bills">
                <div style="display:flex;justify-content:space-between;font-size:12px;font-weight:700;color:#334155;margin-bottom:4px;">
                    <span>Unpaid (<?php echo (int)$unpaidBillsCount; ?>)</span>
                    <span><?php echo $unpaidPct; ?>%</span>
                </div>
                <div class="progress" style="height:10px;background:#e2e8f0;border-radius:6px;margin:0;">
                    <div class="progress-bar" style="width:<?php echo $unpaidPct; ?>%;background:#dc2626;"></div>
                </div>
            </a>
        </div>

        <!-- Collection Ratio Pie / Radial Card (CLICKABLE) -->
        <a href="<?php echo $this->Html->url(array('controller' => 'BillingBills', 'action' => 'index')); ?>" style="text-decoration:none;display:flex;flex-direction:column;justify-content:space-between;background:#ffffff;border:1px solid #e2e8f0;border-radius:12px;padding:20px;transition:transform 0.15s ease;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'" title="Click to view Collection Ratio Bills">
            <h4 style="font-size:14px;font-weight:800;color:#0f172a;margin:0 0 10px 0;">Collection Ratio</h4>
            <div style="text-align:center;padding:10px 0;">
                <svg width="150" height="150" viewBox="0 0 36 36" style="display:inline-block;transform:rotate(-90deg);">
                    <circle cx="18" cy="18" r="15.915" fill="none" stroke="#ef4444" stroke-width="3.8"></circle>
                    <circle cx="18" cy="18" r="15.915" fill="none" stroke="#2563eb" stroke-width="3.8" stroke-dasharray="<?php echo $paidPct; ?>, 100"></circle>
                </svg>
            </div>
            <div style="display:flex;justify-content:center;gap:16px;font-size:12px;font-weight:700;">
                <span style="color:#2563eb;"><i class="fa fa-circle"></i> Collected (<?php echo $paidPct; ?>%)</span>
                <span style="color:#ef4444;"><i class="fa fa-circle"></i> Outstanding (<?php echo (100 - $paidPct); ?>%)</span>
            </div>
        </a>

    </div>

    <!-- Monthly Billing Graph (Last 6 Months) (CLICKABLE) -->
    <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:12px;padding:20px;margin-bottom:24px;">
        <h4 style="font-size:14px;font-weight:800;color:#0f172a;margin:0 0 16px 0;">Monthly Billing Graph (Last 6 Months)</h4>
        <div style="display:flex;align-items:flex-end;justify-content:space-between;height:180px;border-bottom:2px solid #cbd5e1;padding-bottom:10px;">
            <?php 
                $maxMonthly = 1;
                foreach($monthlyGraphData as $mg) {
                    if($mg['total'] > $maxMonthly) $maxMonthly = $mg['total'];
                }
                foreach($monthlyGraphData as $mg){
                    $totH = max(8, round(($mg['total'] / $maxMonthly) * 140));
                    $paidH = max(4, round(($mg['paid'] / $maxMonthly) * 140));
                    $dueH = max(2, round(($mg['due'] / $maxMonthly) * 140));
            ?>
                <a href="<?php echo $this->Html->url(array('controller' => 'BillingBills', 'action' => 'index', '?' => array('month' => $mg['month']))); ?>" style="display:flex;flex-direction:column;align-items:center;gap:6px;flex:1;text-decoration:none;" title="Click to view <?php echo h($mg['month']); ?> Bills">
                    <div style="display:flex;align-items:flex-end;gap:4px;height:140px;">
                        <div style="width:14px;height:<?php echo $totH; ?>px;background:#065f46;border-radius:3px 3px 0 0;" title="Total: PKR <?php echo number_format($mg['total'], 2); ?>"></div>
                        <div style="width:14px;height:<?php echo $paidH; ?>px;background:#10b981;border-radius:3px 3px 0 0;" title="Paid: PKR <?php echo number_format($mg['paid'], 2); ?>"></div>
                        <div style="width:14px;height:<?php echo $dueH; ?>px;background:#ef4444;border-radius:3px 3px 0 0;" title="Due: PKR <?php echo number_format($mg['due'], 2); ?>"></div>
                    </div>
                    <span style="font-size:11px;font-weight:700;color:#64748b;"><?php echo h($mg['month']); ?></span>
                </a>
            <?php } ?>
        </div>
        <div style="display:flex;justify-content:center;gap:20px;margin-top:12px;font-size:12px;font-weight:700;">
            <span style="color:#065f46;"><i class="fa fa-circle"></i> Total</span>
            <span style="color:#10b981;"><i class="fa fa-circle"></i> Paid</span>
            <span style="color:#ef4444;"><i class="fa fa-circle"></i> Due</span>
        </div>
    </div>

</div>

<script>
function openSelectedBill() {
    var val = document.getElementById('quickBillSelect').value;
    if(val) {
        window.open('<?php echo $this->Html->url(array("controller" => "BillingBills", "action" => "print_bill")); ?>/' + val, '_blank');
    }
}

function openSelectedMeter() {
    var val = document.getElementById('quickMeterSelect').value;
    if(val) {
        window.location.href = '<?php echo $this->Html->url(array("controller" => "BillingMeters", "action" => "ledger")); ?>/' + val;
    }
}
</script>
