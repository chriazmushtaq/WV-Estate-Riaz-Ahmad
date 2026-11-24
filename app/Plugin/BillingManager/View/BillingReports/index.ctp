<style>
/* FINANCIAL REPORTS & DEFAULTER RECOVERY HUB STYLING */
.report-card {
    background: #ffffff;
    border: 2px solid #0f172a;
    border-radius: 10px;
    padding: 16px;
    margin-bottom: 20px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}

.report-card-title {
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    color: #475569;
    letter-spacing: 0.5px;
}

.report-card-value {
    font-size: 22px;
    font-weight: 900;
    margin-top: 6px;
    color: #0f172a;
}

.report-nav-tabs {
    display: flex;
    gap: 8px;
    margin-bottom: 20px;
    border-bottom: 2px solid #cbd5e1;
    padding-bottom: 0;
}

.report-nav-tabs a {
    padding: 11px 22px;
    font-weight: 800;
    font-size: 13px;
    color: #334155;
    background: #f1f5f9;
    border-radius: 8px 8px 0 0;
    text-decoration: none;
    border: 1px solid #cbd5e1;
    border-bottom: none;
    cursor: pointer;
}

.report-nav-tabs a.active {
    background: #0f172a;
    color: #ffffff;
    border-color: #0f172a;
}

.table-report-custom {
    width: 100% !important;
    border-collapse: collapse !important;
    font-size: 13px !important;
}

.table-report-custom th {
    background: #0f172a !important;
    color: #ffffff !important;
    font-size: 11.5px !important;
    font-weight: 800 !important;
    text-transform: uppercase !important;
    padding: 12px 14px !important;
    border: 1px solid #1e293b !important;
    vertical-align: middle !important;
    white-space: nowrap !important;
}

.table-report-custom td {
    padding: 10px 14px !important;
    border: 1px solid #cbd5e1 !important;
    vertical-align: middle !important;
    color: #0f172a !important;
    font-weight: 600 !important;
}

.table-report-custom tr:nth-child(even) {
    background: #f8fafc !important;
}

.btn-export-excel {
    background: #15803d !important;
    color: #ffffff !important;
    font-weight: 800 !important;
    padding: 8px 18px !important;
    font-size: 13px !important;
    border-radius: 6px !important;
    text-decoration: none !important;
    display: inline-block !important;
}

.btn-export-excel:hover {
    background: #166534 !important;
    color: #ffffff !important;
}

.btn-export-pdf {
    background: #dc2626 !important;
    color: #ffffff !important;
    font-weight: 800 !important;
    padding: 8px 18px !important;
    font-size: 13px !important;
    border-radius: 6px !important;
    text-decoration: none !important;
    display: inline-block !important;
}

.btn-export-pdf:hover {
    background: #b91c1c !important;
    color: #ffffff !important;
}

.report-filter-box {
    background: #ffffff;
    border: 2px solid #0f172a;
    border-radius: 10px;
    padding: 16px 20px;
    margin-bottom: 22px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}
</style>

<div class="row btn-print-hide" style="margin-bottom:15px;">
    <div class="col-md-12">
        <div class="page-title" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
            <div class="title-env">
                <h1 class="title" style="font-weight:900;color:#0f172a;font-size:22px;margin:0;">
                    <i class="fa fa-bar-chart text-primary"></i> <?php echo __('Financial Reports & Defaulters Recovery Hub'); ?>
                </h1>
                <p class="description" style="color:#475569;font-size:13px;margin-top:4px;font-weight:600;">
                    <?php echo __('Comprehensive financial analytics, arrears recovery tracking, 1-Click Excel export, and direct PDF downloads for'); ?> <strong><?php echo h($societyName); ?></strong>
                </p>
            </div>
            <div style="display:flex;gap:10px;">
                <a href="<?php echo $this->Html->url(array('action' => 'export_excel', 'collection', '?' => array('society_id' => $societyId, 'month' => $month, 'status' => $status))); ?>" class="btn-export-excel">
                    <i class="fa fa-file-excel-o"></i> <?php echo __('Export Excel / CSV'); ?>
                </a>
                <a href="<?php echo $this->Html->url(array('action' => 'export_pdf', 'defaulters', '?' => array('society_id' => $societyId))); ?>" target="_blank" class="btn-export-pdf">
                    <i class="fa fa-file-pdf-o"></i> <?php echo __('Export Direct PDF'); ?>
                </a>
            </div>
        </div>
    </div>
</div>

<?php echo $this->Session->flash(); ?>

<!-- MULTI-FILTER CONTROL BAR -->
<div class="report-filter-box btn-print-hide">
    <form method="get" action="<?php echo $this->Html->url(array('action' => 'index')); ?>" class="form-inline" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;">
        
        <!-- 1. Society Filter -->
        <div class="form-group" style="margin:0;">
            <label style="font-size:11px;font-weight:800;text-transform:uppercase;color:#475569;display:block;margin-bottom:4px;"><?php echo __('HOUSING SOCIETY'); ?></label>
            <select name="society_id" class="form-control input-sm" style="font-weight:700;border-radius:6px;min-width:180px;">
                <?php foreach($societiesList as $sId => $sName){ ?>
                    <option value="<?php echo $sId; ?>" <?php echo ($societyId == $sId) ? 'selected' : ''; ?>><?php echo h($sName); ?></option>
                <?php } ?>
            </select>
        </div>

        <!-- 2. Billing Month Filter -->
        <div class="form-group" style="margin:0;">
            <label style="font-size:11px;font-weight:800;text-transform:uppercase;color:#475569;display:block;margin-bottom:4px;"><?php echo __('BILLING PERIOD'); ?></label>
            <select name="month" class="form-control input-sm" style="font-weight:700;border-radius:6px;min-width:140px;">
                <option value="all" <?php echo ($month == 'all') ? 'selected' : ''; ?>>-- All Months --</option>
                <?php foreach($monthsList as $mVal => $mLabel){ ?>
                    <option value="<?php echo $mVal; ?>" <?php echo ($month == $mVal) ? 'selected' : ''; ?>><?php echo h($mLabel); ?></option>
                <?php } ?>
            </select>
        </div>

        <!-- 3. Payment Status Filter -->
        <div class="form-group" style="margin:0;">
            <label style="font-size:11px;font-weight:800;text-transform:uppercase;color:#475569;display:block;margin-bottom:4px;"><?php echo __('PAYMENT STATUS'); ?></label>
            <select name="status" class="form-control input-sm" style="font-weight:700;border-radius:6px;min-width:130px;">
                <option value="all" <?php echo ($status == 'all') ? 'selected' : ''; ?>>-- All Statuses --</option>
                <option value="paid" <?php echo ($status == 'paid') ? 'selected' : ''; ?>>Paid</option>
                <option value="unpaid" <?php echo ($status == 'unpaid') ? 'selected' : ''; ?>>Unpaid</option>
                <option value="partial" <?php echo ($status == 'partial') ? 'selected' : ''; ?>>Partial</option>
            </select>
        </div>

        <!-- 4. Utility Type Filter -->
        <div class="form-group" style="margin:0;">
            <label style="font-size:11px;font-weight:800;text-transform:uppercase;color:#475569;display:block;margin-bottom:4px;"><?php echo __('UTILITY TYPE'); ?></label>
            <select name="utility_type" class="form-control input-sm" style="font-weight:700;border-radius:6px;min-width:130px;">
                <option value="all" <?php echo ($utilityType == 'all') ? 'selected' : ''; ?>>-- All Utilities --</option>
                <option value="Electricity" <?php echo ($utilityType == 'Electricity') ? 'selected' : ''; ?>>Electricity</option>
                <option value="Water" <?php echo ($utilityType == 'Water') ? 'selected' : ''; ?>>Water</option>
                <option value="Gas" <?php echo ($utilityType == 'Gas') ? 'selected' : ''; ?>>Gas</option>
            </select>
        </div>

        <!-- 5. Search Keyword -->
        <div class="form-group" style="margin:0;flex-grow:1;">
            <label style="font-size:11px;font-weight:800;text-transform:uppercase;color:#475569;display:block;margin-bottom:4px;"><?php echo __('SEARCH CONSUMER / UNIT / BILL #'); ?></label>
            <input type="text" name="search" value="<?php echo h($search); ?>" placeholder="Type Consumer Name, Phone, Property, Bill #..." class="form-control input-sm" style="font-weight:700;border-radius:6px;width:100%;">
        </div>

        <!-- 6. Filter & Reset Buttons -->
        <div class="form-group" style="margin:0;display:flex;gap:6px;">
            <button type="submit" class="btn btn-sm btn-primary" style="font-weight:800;padding:6px 16px;">
                <i class="fa fa-filter"></i> <?php echo __('Apply Filter'); ?>
            </button>
            <a href="<?php echo $this->Html->url(array('action' => 'index')); ?>" class="btn btn-sm btn-default" style="font-weight:700;padding:6px 12px;">
                <i class="fa fa-refresh"></i> <?php echo __('Reset'); ?>
            </a>
        </div>

    </form>
</div>

<!-- TOP FINANCIAL KPI SUMMARY CARDS -->
<div class="row btn-print-hide">
    <div class="col-md-3">
        <div class="report-card" style="border-left:5px solid #0284c7;">
            <div class="report-card-title"><?php echo __('TOTAL INVOICES GENERATED'); ?></div>
            <div class="report-card-value" style="color:#0284c7;">
                <?php echo number_format($totalBillsCount); ?> <span style="font-size:13px;color:#475569;font-weight:700;">Bills</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="report-card" style="border-left:5px solid #d97706;">
            <div class="report-card-title"><?php echo __('TOTAL BILLED REVENUE'); ?></div>
            <div class="report-card-value" style="color:#d97706;">
                PKR <?php echo number_format($totalBilled, 2); ?>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="report-card" style="border-left:5px solid #059669;">
            <div class="report-card-title"><?php echo __('TOTAL PAYMENTS COLLECTED'); ?></div>
            <div class="report-card-value" style="color:#059669;">
                PKR <?php echo number_format($totalPaid, 2); ?>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="report-card" style="border-left:5px solid #dc2626;background:<?php echo ($totalArrears > 0) ? '#fef2f2' : '#ffffff'; ?>;">
            <div class="report-card-title"><?php echo __('OUTSTANDING ARREARS & DEFAULTS'); ?></div>
            <div class="report-card-value" style="color:#dc2626;">
                PKR <?php echo number_format($totalArrears, 2); ?>
                <div style="font-size:11.5px;color:#475569;font-weight:700;margin-top:2px;">
                    Recovery Rate: <strong style="color:<?php echo ($recoveryRate >= 80) ? '#059669' : '#dc2626'; ?>;"><?php echo $recoveryRate; ?>%</strong>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- REPORT NAVIGATION TABS -->
<div class="report-nav-tabs btn-print-hide">
    <a href="#tabCollection" class="active" id="btnTabCollection">
        <i class="fa fa-line-chart"></i> <?php echo __('Consumer Collection Summary'); ?> (<?php echo count($consumerCollectionList); ?>)
    </a>
    <a href="#tabDefaulters" id="btnTabDefaulters">
        <i class="fa fa-exclamation-triangle text-danger"></i> <?php echo __('Defaulters & Arrears Recovery List'); ?> (<?php echo count($defaultersList); ?>)
    </a>
    <a href="#tabReadings" id="btnTabReadings">
        <i class="fa fa-tachometer text-info"></i> <?php echo __('Meter Readings History Log'); ?> (<?php echo count($readingsLog); ?>)
    </a>
    <a href="<?php echo $this->Html->url(array('action' => 'line_loss_audit', '?' => array('society_id' => $societyId))); ?>" style="background:#0f172a;color:#ffffff !important;border-color:#0f172a;">
        <i class="fa fa-flash text-warning"></i> <?php echo __('⚡ Feeder Line Loss & Theft Audit'); ?>
    </a>
</div>

<div class="tab-content" style="width:100%;">

    <!-- TAB 1: CONSUMER FINANCIAL COLLECTION SUMMARY TABLE -->
    <div class="tab-pane active" id="tabCollection" style="display:block;">
        
        <!-- DETAILED CONSUMER COLLECTION TABLE WITH CONSUMER NAME -->
        <div class="panel" style="background:#ffffff;border:2px solid #0f172a;border-radius:10px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.06);margin-bottom:30px;">
            <div class="panel-heading" style="background:#0f172a;color:#ffffff;padding:14px 18px;display:flex;justify-content:space-between;align-items:center;">
                <h3 class="panel-title" style="font-weight:800;font-size:15px;color:#ffffff;margin:0;">
                    <i class="fa fa-users text-warning"></i> <?php echo __('Detailed Consumer Billing & Collection Summary Table'); ?>
                </h3>
                <a href="<?php echo $this->Html->url(array('action' => 'export_excel', 'collection', '?' => array('society_id' => $societyId, 'month' => $month, 'status' => $status))); ?>" class="btn btn-xs btn-success" style="font-weight:800;padding:4px 12px;">
                    <i class="fa fa-file-excel-o"></i> <?php echo __('Download Collection CSV'); ?>
                </a>
            </div>
            <div class="panel-body" style="padding:0;overflow-x:auto;">
                <table class="table-report-custom">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th><?php echo __('BILL NO'); ?></th>
                            <th><?php echo __('CONSUMER PROFILE (PRIMARY)'); ?></th>
                            <th><?php echo __('PHONE NO'); ?></th>
                            <th><?php echo __('PROPERTY UNIT'); ?></th>
                            <th><?php echo __('METER NO'); ?></th>
                            <th><?php echo __('PERIOD'); ?></th>
                            <th><?php echo __('BILLED (PKR)'); ?></th>
                            <th><?php echo __('PAID (PKR)'); ?></th>
                            <th><?php echo __('BALANCE (PKR)'); ?></th>
                            <th><?php echo __('STATUS'); ?></th>
                            <th><?php echo __('ACTION'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($consumerCollectionList)){ ?>
                            <tr><td colspan="12" class="text-center text-muted" style="padding:24px;"><?php echo __('No consumer collection records match the selected filters.'); ?></td></tr>
                        <?php } else { $cIdx = 1; foreach($consumerCollectionList as $cRow){ 
                            $st = strtolower($cRow['status']);
                            $stClass = ($st == 'paid') ? 'success' : (($st == 'partial') ? 'warning' : 'danger');
                        ?>
                            <tr>
                                <td style="font-weight:800;color:#475569;"><?php echo $cIdx++; ?></td>
                                <td><strong style="color:#0f172a;font-family:monospace;"><?php echo h($cRow['bill_number']); ?></strong></td>
                                <td>
                                    <strong style="color:#0f172a;font-size:13.5px;"><i class="fa fa-user text-primary"></i> <?php echo h($cRow['consumer_name']); ?></strong>
                                </td>
                                <td><strong style="color:#059669;"><?php echo h($cRow['consumer_phone']); ?></strong></td>
                                <td><strong style="color:#334155;"><?php echo h($cRow['property_title']); ?></strong></td>
                                <td><strong style="color:#0369a1;"><?php echo h($cRow['meter_number']); ?></strong></td>
                                <td><strong style="color:#0369a1;font-size:13px;"><i class="fa fa-calendar"></i> <?php echo h($cRow['formatted_month']); ?></strong></td>
                                <td><strong style="color:#d97706;font-size:13.5px;">PKR <?php echo number_format($cRow['total_amount'], 2); ?></strong></td>
                                <td><strong style="color:#059669;font-size:13.5px;">PKR <?php echo number_format($cRow['paid_amount'], 2); ?></strong></td>
                                <td><strong style="color:<?php echo ($cRow['balance_amount'] > 0) ? '#dc2626' : '#059669'; ?>;font-size:13.5px;">PKR <?php echo number_format($cRow['balance_amount'], 2); ?></strong></td>
                                <td>
                                    <span class="label label-<?php echo $stClass; ?>" style="font-size:11px;padding:4px 8px;font-weight:800;"><?php echo ucfirst($st); ?></span>
                                </td>
                                <td>
                                    <a href="<?php echo $this->Html->url(array('controller' => 'billing_bills', 'action' => 'print_bill', $cRow['bill_id'])); ?>" target="_blank" class="btn btn-xs btn-primary" style="font-weight:800;padding:3px 8px;">
                                        <i class="fa fa-print"></i> <?php echo __('Print Bill'); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php } } ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- MONTH-BY-MONTH SUMMARY TABLE -->
        <div class="panel" style="background:#ffffff;border:2px solid #0f172a;border-radius:10px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.06);margin-bottom:30px;">
            <div class="panel-heading" style="background:#0f172a;color:#ffffff;padding:14px 18px;">
                <h3 class="panel-title" style="font-weight:800;font-size:15px;color:#ffffff;margin:0;">
                    <i class="fa fa-calculator text-warning"></i> <?php echo __('Month-by-Month Aggregate Collection Summary'); ?>
                </h3>
            </div>
            <div class="panel-body" style="padding:0;overflow-x:auto;">
                <table class="table-report-custom">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th><?php echo __('BILLING PERIOD'); ?></th>
                            <th><?php echo __('INVOICES GENERATED'); ?></th>
                            <th><?php echo __('BILLED AMOUNT (PKR)'); ?></th>
                            <th><?php echo __('PAID AMOUNT (PKR)'); ?></th>
                            <th><?php echo __('OUTSTANDING ARREARS (PKR)'); ?></th>
                            <th><?php echo __('COLLECTION RATE'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($monthlyBreakdown)){ ?>
                            <tr><td colspan="7" class="text-center text-muted" style="padding:24px;"><?php echo __('No monthly collection records found.'); ?></td></tr>
                        <?php } else { $mIdx = 1; foreach($monthlyBreakdown as $mRow){ 
                            $recRate = ($mRow['billed'] > 0) ? round(($mRow['paid'] / $mRow['billed']) * 100, 1) : 100;
                        ?>
                            <tr>
                                <td style="font-weight:800;color:#475569;"><?php echo $mIdx++; ?></td>
                                <td><strong style="color:#0369a1;font-size:14px;"><i class="fa fa-calendar"></i> <?php echo h($mRow['formatted_month']); ?></strong></td>
                                <td><strong style="color:#0f172a;"><?php echo number_format($mRow['bills_count']); ?> Bills</strong></td>
                                <td><strong style="color:#d97706;font-size:13.5px;">PKR <?php echo number_format($mRow['billed'], 2); ?></strong></td>
                                <td><strong style="color:#059669;font-size:13.5px;">PKR <?php echo number_format($mRow['paid'], 2); ?></strong></td>
                                <td><strong style="color:<?php echo ($mRow['balance'] > 0) ? '#dc2626' : '#059669'; ?>;font-size:13.5px;">PKR <?php echo number_format($mRow['balance'], 2); ?></strong></td>
                                <td>
                                    <span class="label label-<?php echo ($recRate >= 80) ? 'success' : 'danger'; ?>" style="font-size:11.5px;padding:4px 8px;font-weight:800;">
                                        <?php echo $recRate; ?>%
                                    </span>
                                </td>
                            </tr>
                        <?php } } ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- TAB 2: DEFAULTERS & OUTSTANDING ARREARS RECOVERY LIST -->
    <div class="tab-pane" id="tabDefaulters" style="display:none;">
        <div class="panel" style="background:#ffffff;border:2px solid #dc2626;border-radius:10px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.06);margin-bottom:30px;">
            <div class="panel-heading" style="background:#dc2626;color:#ffffff;padding:14px 18px;display:flex;justify-content:space-between;align-items:center;">
                <h3 class="panel-title" style="font-weight:800;font-size:15px;color:#ffffff;margin:0;">
                    <i class="fa fa-exclamation-triangle text-warning"></i> <?php echo __('Outstanding Arrears & Defaulter Recovery Dashboard'); ?>
                </h3>
                <div>
                    <a href="<?php echo $this->Html->url(array('action' => 'export_excel', 'defaulters', '?' => array('society_id' => $societyId))); ?>" class="btn btn-xs btn-success" style="font-weight:800;padding:4px 12px;margin-right:6px;">
                        <i class="fa fa-file-excel-o"></i> <?php echo __('Export Defaulters Excel'); ?>
                    </a>
                    <a href="<?php echo $this->Html->url(array('action' => 'export_pdf', 'defaulters', '?' => array('society_id' => $societyId))); ?>" target="_blank" class="btn btn-xs btn-default" style="font-weight:800;padding:4px 12px;color:#0f172a;">
                        <i class="fa fa-print"></i> <?php echo __('Print PDF Report'); ?>
                    </a>
                </div>
            </div>
            <div class="panel-body" style="padding:0;overflow-x:auto;">
                <table class="table-report-custom">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th><?php echo __('BILL NO'); ?></th>
                            <th><?php echo __('CONSUMER NAME'); ?></th>
                            <th><?php echo __('PHONE NO'); ?></th>
                            <th><?php echo __('PROPERTY UNIT'); ?></th>
                            <th><?php echo __('MONTH'); ?></th>
                            <th><?php echo __('DUE DATE'); ?></th>
                            <th><?php echo __('OVERDUE DAYS'); ?></th>
                            <th><?php echo __('ENFORCEMENT STAGE'); ?></th>
                            <th><?php echo __('ARREARS (PKR)'); ?></th>
                            <th><?php echo __('ACTION / REMINDER'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($defaultersList)){ ?>
                            <tr><td colspan="11" class="text-center text-success" style="padding:24px;font-weight:800;"><i class="fa fa-check-circle"></i> <?php echo __('Excellent! No outstanding defaulter arrears found for this filter.'); ?></td></tr>
                        <?php } else { $dIdx = 1; foreach($defaultersList as $def){ 
                            $waMsg = rawurlencode("Dear " . $def['consumer_name'] . ", your " . $societyName . " Utility Bill #" . $def['bill_number'] . " for " . $def['formatted_month'] . " has an outstanding balance of PKR " . number_format($def['balance_amount'], 2) . ". Please clear your dues immediately to avoid disconnection.");
                            $waPhone = preg_replace('/[^0-9]/', '', $def['consumer_phone']);
                            if (substr($waPhone, 0, 1) == '0') {
                                $waPhone = '92' . substr($waPhone, 1);
                            }
                            $oDays = (int)$def['overdue_days'];
                            $stageBadge = '<span class="label label-default" style="font-weight:800;font-size:10.5px;">Grace Period</span>';
                            if($oDays > 30) {
                                $stageBadge = '<span class="label label-danger" style="background:#dc2626;font-weight:800;font-size:10.5px;"><i class="fa fa-ban"></i> Disconnection Ready</span>';
                            } elseif($oDays > 15) {
                                $stageBadge = '<span class="label label-warning" style="background:#ea580c;font-weight:800;font-size:10.5px;"><i class="fa fa-exclamation-circle"></i> Final Warning</span>';
                            } elseif($oDays > 7) {
                                $stageBadge = '<span class="label label-info" style="background:#0284c7;font-weight:800;font-size:10.5px;"><i class="fa fa-envelope"></i> Notice Sent</span>';
                            }
                        ?>
                            <tr>
                                <td style="font-weight:800;color:#475569;"><?php echo $dIdx++; ?></td>
                                <td><strong style="color:#0f172a;font-family:monospace;"><?php echo h($def['bill_number']); ?></strong></td>
                                <td><strong style="color:#0f172a;"><i class="fa fa-user text-primary"></i> <?php echo h($def['consumer_name']); ?></strong></td>
                                <td><strong style="color:#059669;"><?php echo h($def['consumer_phone']); ?></strong></td>
                                <td><strong style="color:#334155;"><?php echo h($def['property_title']); ?></strong></td>
                                <td><strong style="color:#0369a1;"><?php echo h($def['formatted_month']); ?></strong></td>
                                <td><strong style="color:#dc2626;"><?php echo h($def['due_date']); ?></strong></td>
                                <td>
                                    <?php if($def['overdue_days'] > 0){ ?>
                                        <span class="badge" style="background:#dc2626;color:#ffffff;font-weight:800;padding:4px 8px;"><?php echo $def['overdue_days']; ?> Days Overdue</span>
                                    <?php } else { ?>
                                        <span class="badge" style="background:#d97706;color:#ffffff;font-weight:800;padding:4px 8px;">Due Today</span>
                                    <?php } ?>
                                </td>
                                <td><?php echo $stageBadge; ?></td>
                                <td><strong style="color:#dc2626;font-size:14px;">PKR <?php echo number_format($def['balance_amount'], 2); ?></strong></td>
                                <td>
                                    <?php if(!empty($waPhone)){ ?>
                                        <a href="https://wa.me/<?php echo $waPhone; ?>?text=<?php echo $waMsg; ?>" target="_blank" class="btn btn-xs btn-success" style="font-weight:800;padding:3px 8px;background:#25D366;border-color:#25D366;">
                                            <i class="fa fa-whatsapp"></i> <?php echo __('WhatsApp'); ?>
                                        </a>
                                    <?php } ?>
                                    <a href="<?php echo $this->Html->url(array('controller' => 'billing_bills', 'action' => 'print_bill', $def['bill_id'])); ?>" target="_blank" class="btn btn-xs btn-primary" style="font-weight:800;padding:3px 8px;margin-left:4px;">
                                        <i class="fa fa-print"></i> <?php echo __('Bill'); ?>
                                    </a>
                                    <a href="<?php echo $this->Html->url(array('action' => 'disconnection_order', $def['bill_id'])); ?>" target="_blank" class="btn btn-xs btn-danger" style="font-weight:800;padding:3px 8px;margin-left:4px;" title="<?php echo __('Print Disconnection Work Order'); ?>">
                                        <i class="fa fa-cut"></i> <?php echo __('Work Order'); ?>
                                    </a>
                                </td>
                                </td>
                            </tr>
                        <?php } } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB 3: METER READINGS HISTORY LOG -->
    <div class="tab-pane" id="tabReadings" style="display:none;">
        <div class="panel" style="background:#ffffff;border:2px solid #0f172a;border-radius:10px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.06);margin-bottom:30px;">
            <div class="panel-heading" style="background:#0f172a;color:#ffffff;padding:14px 18px;display:flex;justify-content:space-between;align-items:center;">
                <h3 class="panel-title" style="font-weight:800;font-size:15px;color:#ffffff;margin:0;">
                    <i class="fa fa-list text-info"></i> <?php echo __('Historical Meter Reading Audits Log'); ?>
                </h3>
                <a href="<?php echo $this->Html->url(array('action' => 'export_excel', 'readings', '?' => array('society_id' => $societyId))); ?>" class="btn btn-xs btn-success" style="font-weight:800;padding:4px 12px;">
                    <i class="fa fa-file-excel-o"></i> <?php echo __('Download Readings CSV'); ?>
                </a>
            </div>
            <div class="panel-body" style="padding:0;overflow-x:auto;">
                <table class="table-report-custom">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th><?php echo __('READING DATE'); ?></th>
                            <th><?php echo __('MONTH'); ?></th>
                            <th><?php echo __('CONSUMER'); ?></th>
                            <th><?php echo __('PROPERTY UNIT'); ?></th>
                            <th><?php echo __('METER NO'); ?></th>
                            <th><?php echo __('UTILITY'); ?></th>
                            <th><?php echo __('PREVIOUS'); ?></th>
                            <th><?php echo __('CURRENT'); ?></th>
                            <th><?php echo __('CONSUMED'); ?></th>
                            <th><?php echo __('READER'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($readingsLog)){ ?>
                            <tr><td colspan="11" class="text-center text-muted" style="padding:24px;"><?php echo __('No reading log records found.'); ?></td></tr>
                        <?php } else { $rIdx = 1; foreach($readingsLog as $rLog){ ?>
                            <tr>
                                <td style="font-weight:800;color:#475569;"><?php echo $rIdx++; ?></td>
                                <td><strong style="color:#0f172a;"><?php echo h($rLog['date']); ?></strong></td>
                                <td><strong style="color:#0369a1;"><?php echo h($rLog['formatted_month']); ?></strong></td>
                                <td><strong style="color:#0f172a;"><?php echo h($rLog['consumer_name']); ?></strong></td>
                                <td><strong style="color:#334155;"><?php echo h($rLog['property_title']); ?></strong></td>
                                <td><strong style="color:#0369a1;"><?php echo h($rLog['meter_number']); ?></strong></td>
                                <td><span class="label label-info" style="font-weight:800;"><?php echo h($rLog['utility_type']); ?></span></td>
                                <td><strong style="color:#0f172a;"><?php echo number_format($rLog['previous_reading'], 2); ?></strong></td>
                                <td><strong style="color:#059669;"><?php echo number_format($rLog['current_reading'], 2); ?></strong></td>
                                <td><span class="badge" style="background:#0284c7;font-weight:800;"><?php echo number_format($rLog['units_consumed'], 1); ?> Units</span></td>
                                <td><strong style="color:#475569;font-size:11.5px;"><?php echo h($rLog['reader_name']); ?></strong></td>
                            </tr>
                        <?php } } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<script type="text/javascript">
document.addEventListener('DOMContentLoaded', function() {
    var btnCol = document.getElementById('btnTabCollection');
    var btnDef = document.getElementById('btnTabDefaulters');
    var btnRead = document.getElementById('btnTabReadings');

    var tabCol = document.getElementById('tabCollection');
    var tabDef = document.getElementById('tabDefaulters');
    var tabRead = document.getElementById('tabReadings');

    function switchReportTab(activeBtn, activeTab) {
        [btnCol, btnDef, btnRead].forEach(function(b) { if (b) b.classList.remove('active'); });
        [tabCol, tabDef, tabRead].forEach(function(t) { if (t) t.style.display = 'none'; });

        if (activeBtn) activeBtn.classList.add('active');
        if (activeTab) activeTab.style.display = 'block';
    }

    if (btnCol) btnCol.addEventListener('click', function(e) { e.preventDefault(); switchReportTab(btnCol, tabCol); });
    if (btnDef) btnDef.addEventListener('click', function(e) { e.preventDefault(); switchReportTab(btnDef, tabDef); });
    if (btnRead) btnRead.addEventListener('click', function(e) { e.preventDefault(); switchReportTab(btnRead, tabRead); });

    var hash = window.location.hash;
    if (hash === '#tabDefaulters') {
        switchReportTab(btnDef, tabDef);
    } else if (hash === '#tabReadings') {
        switchReportTab(btnRead, tabRead);
    } else {
        switchReportTab(btnCol, tabCol);
    }
});
</script>
