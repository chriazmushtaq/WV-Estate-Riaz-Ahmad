<?php
$curMonth = isset($this->request->query['filter_month']) ? $this->request->query['filter_month'] : date('Y-m');
?>
<style>
.subm-admin-wrap { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif; color: #0f172a; background: #f8fafc; padding: 12px; }
.subm-top-filter { background: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 14px 18px; margin-bottom: 14px; display: flex; flex-wrap: wrap; gap: 10px; align-items: flex-end; box-shadow: 0 1px 3px rgba(0,0,0,0.04); }
.subm-filter-group { display: flex; flex-direction: column; gap: 4px; }
.subm-filter-group label { font-size: 11.5px; font-weight: 800; color: #1e293b; margin: 0; text-transform: uppercase; letter-spacing: 0.3px; }
.subm-filter-group input, .subm-filter-group select { height: 34px; padding: 4px 10px; font-size: 12.5px; font-weight: 600; color: #0f172a; border: 1.5px solid #cbd5e1; border-radius: 6px; background: #ffffff; }
.subm-filter-group input:focus, .subm-filter-group select:focus { border-color: #0284c7; outline: none; box-shadow: 0 0 0 2px rgba(2,132,199,0.15); }
.subm-btn-filter { background: #0284c7; color: #ffffff !important; border: 1px solid #0369a1; font-weight: 800; font-size: 12px; height: 34px; padding: 0 18px; border-radius: 6px; cursor: pointer; text-transform: uppercase; letter-spacing: 0.5px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
.subm-btn-filter:hover { background: #0369a1; color: #ffffff !important; }
.subm-btn-reset { background: #334155; color: #ffffff !important; border: 1px solid #1e293b; font-weight: 800; font-size: 12px; height: 34px; padding: 0 16px; border-radius: 6px; text-decoration: none !important; display: inline-flex; align-items: center; text-transform: uppercase; letter-spacing: 0.5px; }
.subm-btn-reset:hover { background: #1e293b; color: #ffffff !important; }
.subm-btn-export { background: #0d9488; color: #ffffff !important; border: 1px solid #0f766e; font-weight: 800; font-size: 12px; height: 34px; padding: 0 16px; border-radius: 6px; text-decoration: none !important; display: inline-flex; align-items: center; gap: 5px; text-transform: uppercase; letter-spacing: 0.5px; }
.subm-btn-export:hover { background: #0f766e; color: #ffffff !important; }

.subm-actions-bar { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 16px; }
.subm-action-btn { background: #0f172a; color: #ffffff !important; border: 1px solid #1e293b; font-weight: 800; font-size: 12.5px; padding: 9px 16px; border-radius: 6px; cursor: pointer; text-decoration: none !important; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 5px rgba(0,0,0,0.15); transition: all 0.15s ease-in-out; }
.subm-action-btn:hover { background: #1e293b; color: #ffffff !important; transform: translateY(-1px); }

.subm-history-header { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px; margin-bottom: 10px; }
.subm-history-header h3 { margin: 0; font-size: 16px; font-weight: 800; color: #0f172a; }
.subm-bulk-btns { display: flex; gap: 6px; align-items: center; flex-wrap: wrap; }
.subm-bulk-btn { background: #0284c7; color: #ffffff !important; border: none; font-size: 11.5px; font-weight: 800; padding: 7px 14px; border-radius: 5px; cursor: pointer; transition: all 0.15s ease; }
.subm-bulk-btn:hover { background: #0369a1; color: #ffffff !important; }
.subm-bulk-delete { background: #dc2626; color: #ffffff !important; border: none; font-size: 11.5px; font-weight: 800; padding: 7px 14px; border-radius: 5px; cursor: pointer; transition: all 0.15s ease; }
.subm-bulk-delete:hover { background: #b91c1c; color: #ffffff !important; }

.subm-table-wrap { background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 8px; overflow-x: auto; box-shadow: 0 2px 6px rgba(0,0,0,0.06); }
.subm-custom-table { width: 100%; border-collapse: collapse; font-size: 11.5px; background: #ffffff; }
.subm-custom-table th { background: #0f172a; color: #ffffff !important; font-weight: 800; text-transform: uppercase; font-size: 11px; padding: 11px 9px; text-align: left; border: 1px solid #1e293b; white-space: nowrap; }
.subm-custom-table td { padding: 9px 8px; border: 1px solid #e2e8f0; vertical-align: middle; color: #0f172a !important; font-weight: 600; }
.subm-custom-table tbody tr:hover { background-color: #f1f5f9; }
.subm-custom-table tfoot th { background: #f8fafc; color: #0f172a !important; font-weight: 900; font-size: 11.5px; padding: 11px 9px; border: 1.5px solid #cbd5e1; }

.subm-row-btn-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 4px; min-width: 172px; width: 172px; }
.subm-rbtn, .subm-rbtn *, .subm-rbtn i, .subm-rbtn span, .subm-rbtn .fa { color: #ffffff !important; fill: #ffffff !important; }
.subm-rbtn { display: inline-flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 800; padding: 5px 6px; border-radius: 4px; border: none; text-decoration: none !important; cursor: pointer; text-align: center; height: 27px; box-shadow: 0 1px 3px rgba(0,0,0,0.18); transition: all 0.12s ease; }
.subm-rbtn i, .subm-rbtn span, .subm-rbtn .fa { font-size: 12px; line-height: 1; }
.subm-rbtn:hover { opacity: 0.92; transform: scale(1.05); color: #ffffff !important; }
.subm-rbtn-view { background: #0284c7 !important; }
.subm-rbtn-pay { background: #16a34a !important; font-weight: 800; }
.subm-rbtn-edit { background: #2563eb !important; }
.subm-rbtn-wa { background: #16a34a !important; }
.subm-rbtn-wa-paid { background: #059669 !important; border: 1px solid #047857 !important; }
.subm-rbtn-wa-paid:hover { background: #047857 !important; }
.subm-rbtn-sms { background: #0891b2 !important; }
.subm-rbtn-email { background: #475569 !important; }
.subm-rbtn-del { background: #dc2626 !important; }

.subm-badge-late { color: #b91c1c; font-weight: 900; font-size: 11px; background: #fee2e2; padding: 2px 6px; border-radius: 4px; border: 1px solid #fecaca; }
.subm-badge-due { color: #15803d; font-weight: 900; font-size: 11px; background: #dcfce7; padding: 2px 6px; border-radius: 4px; border: 1px solid #bbf7d0; }

.subm-queue-panel { margin-top: 24px; background: #fff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 16px; }
.subm-queue-header { font-size: 14px; font-weight: 800; color: #0f172a; margin-bottom: 12px; display: flex; align-items: center; gap: 8px; }
</style>

<div class="subm-admin-wrap">
    <?php echo $this->Session->flash(); ?>

    <!-- Top Filter Form -->
    <form method="get" action="<?php echo $this->Html->url(array('action' => 'index')); ?>" class="subm-top-filter">
        <div class="subm-filter-group" style="flex:1; min-width:140px;">
            <label><?php echo __('Bill No'); ?></label>
            <input type="text" name="filter_bill" value="<?php echo isset($this->request->query['filter_bill']) ? h($this->request->query['filter_bill']) : ''; ?>" placeholder="<?php echo __('Search bill no'); ?>" />
        </div>

        <div class="subm-filter-group" style="flex:1; min-width:140px;">
            <label><?php echo __('Client Name'); ?></label>
            <input type="text" name="filter_client_name" value="<?php echo isset($this->request->query['filter_client_name']) ? h($this->request->query['filter_client_name']) : ''; ?>" placeholder="<?php echo __('Search client name'); ?>" />
        </div>

        <div class="subm-filter-group" style="min-width:130px;">
            <label><?php echo __('Month'); ?></label>
            <input type="month" name="filter_month" value="<?php echo isset($this->request->query['filter_month']) ? h($this->request->query['filter_month']) : ''; ?>" />
        </div>

        <div class="subm-filter-group" style="min-width:120px;">
            <label><?php echo __('Start Date'); ?></label>
            <input type="date" name="filter_start_date" value="<?php echo isset($this->request->query['filter_start_date']) ? h($this->request->query['filter_start_date']) : ''; ?>" />
        </div>

        <div class="subm-filter-group" style="min-width:120px;">
            <label><?php echo __('End Date'); ?></label>
            <input type="date" name="filter_end_date" value="<?php echo isset($this->request->query['filter_end_date']) ? h($this->request->query['filter_end_date']) : ''; ?>" />
        </div>

        <div class="subm-filter-group" style="min-width:100px;">
            <label><?php echo __('Status'); ?></label>
            <select name="filter_status">
                <option value=""><?php echo __('All'); ?></option>
                <option value="unpaid" <?php if(isset($this->request->query['filter_status']) && $this->request->query['filter_status'] === 'unpaid') echo 'selected'; ?>><?php echo __('Unpaid'); ?></option>
                <option value="partial" <?php if(isset($this->request->query['filter_status']) && $this->request->query['filter_status'] === 'partial') echo 'selected'; ?>><?php echo __('Partial'); ?></option>
                <option value="paid" <?php if(isset($this->request->query['filter_status']) && $this->request->query['filter_status'] === 'paid') echo 'selected'; ?>><?php echo __('Paid'); ?></option>
            </select>
        </div>

        <div class="subm-filter-group" style="min-width:110px;">
            <label><?php echo __('Meter Type'); ?></label>
            <select name="filter_meter_type">
                <option value=""><?php echo __('All'); ?></option>
                <option value="electricity" <?php if(isset($this->request->query['filter_meter_type']) && $this->request->query['filter_meter_type'] === 'electricity') echo 'selected'; ?>><?php echo __('Electricity'); ?></option>
                <option value="water" <?php if(isset($this->request->query['filter_meter_type']) && $this->request->query['filter_meter_type'] === 'water') echo 'selected'; ?>><?php echo __('Water'); ?></option>
                <option value="gas" <?php if(isset($this->request->query['filter_meter_type']) && $this->request->query['filter_meter_type'] === 'gas') echo 'selected'; ?>><?php echo __('Gas'); ?></option>
            </select>
        </div>

        <button type="submit" class="subm-btn-filter"><i class="fa fa-filter"></i> <?php echo __('Filter'); ?></button>
        <a href="<?php echo $this->Html->url(array('action' => 'index', '?' => array('all' => '1'))); ?>" class="subm-btn-reset"><i class="fa fa-undo"></i> <?php echo __('Reset'); ?></a>
        <a href="javascript:window.print();" class="subm-btn-export"><i class="fa fa-print"></i> <?php echo __('Print'); ?></a>
        <a href="<?php echo $this->Html->url(array('action' => 'index', '?' => array_merge($this->request->query, array('action' => 'export_csv')))); ?>" class="subm-btn-export"><i class="fa fa-file-excel-o"></i> <?php echo __('Export CSV'); ?></a>
    </form>

    <!-- Top Mission Controller Action Buttons -->
    <div class="subm-actions-bar">
        <button type="button" class="subm-action-btn" data-toggle="modal" data-target="#modalBulkBill">
            <span class="fa fa-flash text-warning"></span> <?php echo __('Add New Create Bulk Bill'); ?>
        </button>
        <button type="button" class="subm-action-btn" data-toggle="modal" data-target="#modalSingleBill">
            <span class="fa fa-plus text-info"></span> <?php echo __('Add New Create Single Bill'); ?>
        </button>
        <button type="button" class="subm-action-btn" data-toggle="modal" data-target="#modalCustomBill">
            <span class="fa fa-wrench text-success"></span> <?php echo __('Add New Create Custom Bill'); ?>
        </button>
        <button type="button" class="subm-action-btn" style="background:#0284c7;color:#fff;border-color:#0284c7;" data-toggle="modal" data-target="#modalBulkPdfDownload">
            <span class="fa fa-file-pdf-o text-white"></span> <?php echo __('📦 Bulk PDF / Print'); ?>
        </button>
        <a href="<?php echo $this->Html->url(array('action' => 'run_late_fees')); ?>" class="subm-action-btn" style="background:#dc2626;color:#ffffff !important;border-color:#b91c1c;" onclick="return confirm('Execute Automated Overdue Late-Fee Engine now? This will apply society surcharge policy to all overdue unpaid bills past grace period.');">
            <span class="fa fa-clock-o text-white"></span> <?php echo __('⚡ Run Overdue Surcharges'); ?>
        </a>
        <a href="<?php echo $this->Html->url(array('action' => 'repair_carry_forward')); ?>" class="subm-action-btn" onclick="return confirm('Run one-time carry-forward repair for existing bills? This will rebalance old arrears automatically.');">
            <span class="fa fa-refresh"></span> <?php echo __('Repair Existing Carry-Forward'); ?>
        </a>
    </div>

    <!-- History Bar with Multi-Select Actions -->
    <?php echo $this->Form->create('BillingBill', array('id' => 'bulkActionForm', 'style' => 'margin:0;')); ?>
    <input type="hidden" name="subm_action" id="bulkSubmAction" value="" />

    <div class="subm-history-header">
        <h3><?php echo __('History'); ?></h3>
        <div class="subm-bulk-btns">
            <button type="button" class="subm-bulk-btn" style="background:#0284c7;color:#ffffff;border-color:#0284c7;" onclick="downloadSelectedBillsMergedPdf(true)"><i class="fa fa-file-pdf-o"></i> <?php echo __('⬇️ Download Selected PDF'); ?></button>
            <button type="button" class="subm-bulk-btn" style="background:#16a34a;color:#ffffff;border-color:#16a34a;" onclick="downloadSelectedBillsMergedPdf(false)"><i class="fa fa-print"></i> <?php echo __('🖨️ Print Selected'); ?></button>
            <button type="button" class="subm-bulk-btn" style="background:#059669;color:#ffffff;border-color:#059669;font-weight:800;" onclick="openBulkWhatsappModal()"><i class="fa fa-whatsapp"></i> <?php echo __('Send Bulk WhatsApp'); ?></button>
            <button type="button" class="subm-bulk-btn" onclick="submitBulkAction('send_sms')"><?php echo __('Send SMS'); ?></button>
            <button type="button" class="subm-bulk-btn" onclick="submitBulkAction('send_email')"><?php echo __('Send Email'); ?></button>
            <button type="button" class="subm-bulk-btn" onclick="submitBulkAction('send_both')"><?php echo __('Send Both'); ?></button>
            <button type="button" class="subm-bulk-delete" onclick="submitBulkAction('bulk_delete')"><?php echo __('Delete Selected'); ?></button>
            <span style="font-size:12px;color:#64748b;font-weight:bold;margin-left:6px;">Selected: <strong id="selectedCount" style="color:#0f172a;">0</strong></span>
        </div>
    </div>

    <!-- Main Invoices Table -->
    <div class="subm-table-wrap">
        <table class="subm-custom-table">
            <thead>
                <tr>
                    <th style="width:36px;text-align:center;color:#ffffff;font-weight:800;">#</th>
                    <th style="width:28px;text-align:center;"><input type="checkbox" id="checkAll" /></th>
                    <th><?php echo __('BILL #'); ?></th>
                    <th><?php echo __('CONSUMER ID'); ?></th>
                    <th><?php echo __('CLIENT'); ?></th>
                    <th><?php echo __('HOUSE / PLOT #'); ?></th>
                    <th><?php echo __('METER NO'); ?></th>
                    <th><?php echo __('METER TYPE'); ?></th>
                    <th><?php echo __('MONTH'); ?></th>
                    <th><?php echo __('BILL'); ?></th>
                    <th><?php echo __('LATE FEE STATUS'); ?></th>
                    <th><?php echo __('TOTAL'); ?></th>
                    <th><?php echo __('PAID'); ?></th>
                    <th><?php echo __('BAL'); ?></th>
                    <th><?php echo __('PREV PENDING &rarr; CHARGED'); ?></th>
                    <th><?php echo __('REF#'); ?></th>
                    <th><?php echo __('STATUS'); ?></th>
                    <th><?php echo __('PAID DATE'); ?></th>
                    <th style="text-align:center; min-width:185px; width:185px;"><?php echo __('ACTION'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($bills)){ ?>
                    <tr><td colspan="19" style="text-align:center;padding:32px;color:#64748b;font-weight:700;"><?php echo __('No bills found for the selected criteria.'); ?></td></tr>
                <?php } else { 
                    $srNo = 1;
                    foreach($bills as $b){ 
                    $bi = $b['BillingBill']; 
                    $p = $b['BillingProperty']; 
                    $c = !empty($b['BillingClient']) ? $b['BillingClient'] : (isset($p['BillingClient']) ? $p['BillingClient'] : array());
                    $m = isset($p['BillingMeter'][0]) ? $p['BillingMeter'][0] : array();
                    
                    $consumerId = !empty($c['id']) ? ('WV70' . str_pad($c['id'], 2, '0', STR_PAD_LEFT)) : '-';
                    $clientName = !empty($c['name']) ? strtoupper($c['name']) : 'NO CLIENT';
                    $unitNo = !empty($p['property_no']) ? $p['property_no'] : '-';
                    $meterNo = !empty($m['meter_number']) ? $m['meter_number'] : ('MTR-' . $bi['billing_property_id']);
                    $monthLabel = date('M Y', strtotime($bi['billing_period'] . '-01'));
                    $billAmt = (float)$bi['consumption_amount'];
                    $lateFee = (float)$bi['late_fee'];
                    $totalAmt = (float)$bi['total_amount'];
                    $paidAmt = (float)$bi['paid_amount'];
                    $balAmt = (float)$bi['balance_amount'];
                    $arrears = (float)$bi['arrears_amount'];
                    $ref = !empty($bi['transaction_ref']) ? $bi['transaction_ref'] : '-';
                    $payDate = !empty($bi['payment_date']) ? date('d M Y', strtotime($bi['payment_date'])) : '-';
                ?>
                    <tr>
                        <td style="text-align:center;font-weight:800;color:#0f172a;background:#f8fafc;"><?php echo $srNo++; ?></td>
                        <td style="text-align:center;">
                            <input type="checkbox" name="data[bill_ids][]" value="<?php echo $bi['id']; ?>" class="row-checkbox" />
                        </td>
                        <td><strong style="color:#0284c7;font-weight:800;"><?php echo h($bi['bill_number']); ?></strong></td>
                        <td><strong style="color:#0f172a;"><?php echo h($consumerId); ?></strong></td>
                        <td><strong style="color:#0f172a;"><?php echo h($clientName); ?></strong></td>
                        <td style="color:#0f172a;font-weight:700;"><?php echo h($unitNo); ?></td>
                        <td><strong style="color:#334155;"><?php echo h($meterNo); ?></strong></td>
                        <td style="color:#0f172a;"><?php echo ucfirst(h($bi['utility_type'])); ?></td>
                        <td style="color:#0f172a;font-weight:700;"><?php echo h($monthLabel); ?></td>
                        <td style="color:#0f172a;font-weight:700;"><?php echo number_format($billAmt, 2); ?></td>
                        <td>
                            <?php if($bi['payment_status'] === 'paid'){ ?>
                                <span class="subm-badge-due"><?php echo __('Due/0d'); ?></span>
                            <?php } elseif($lateFee > 0){ ?>
                                <span class="subm-badge-late"><?php echo __('Late Fee/') . number_format($lateFee, 2); ?></span>
                            <?php } else { ?>
                                <span class="subm-badge-due"><?php echo __('Due/10d'); ?></span>
                            <?php } ?>
                        </td>
                        <td><strong style="color:#0f172a;font-size:12px;"><?php echo number_format($totalAmt, 2); ?></strong></td>
                        <td style="color:#15803d;font-weight:800;"><?php echo number_format($paidAmt, 2); ?></td>
                        <td style="<?php echo ($balAmt > 0) ? 'color:#b91c1c;font-weight:800;' : 'color:#0f172a;font-weight:700;'; ?>"><?php echo number_format($balAmt, 2); ?></td>
                        <td>
                            <?php if($arrears > 0){ ?>
                                <span style="color:#15803d;font-weight:800;font-size:11px;"><?php echo number_format($arrears, 2) . ' &rarr; ' . number_format($arrears, 2); ?></span>
                            <?php } else { ?>
                                <span style="color:#64748b;font-weight:600;font-size:11px;">0.00 &rarr; 0.00</span>
                            <?php } ?>
                        </td>
                        <td><small style="color:#334155;font-weight:700;"><?php echo h($ref); ?></small></td>
                        <td>
                            <?php if($bi['payment_status'] === 'paid'){ ?>
                                <span style="background:#dcfce7;color:#15803d;font-weight:800;padding:3px 8px;border-radius:4px;border:1px solid #bbf7d0;"><?php echo __('Paid'); ?></span>
                            <?php } elseif($bi['payment_status'] === 'partial'){ ?>
                                <span style="background:#fef3c7;color:#b45309;font-weight:800;padding:3px 8px;border-radius:4px;border:1px solid #fde68a;"><?php echo __('Partial'); ?></span>
                            <?php } elseif($bi['payment_status'] === 'carry_forward'){ ?>
                                <span style="background:#f1f5f9;color:#0369a1;font-weight:800;padding:3px 8px;border-radius:4px;border:1px solid #e2e8f0;"><?php echo __('Carry-Forward'); ?></span>
                            <?php } else { ?>
                                <span style="background:#fee2e2;color:#b91c1c;font-weight:800;padding:3px 8px;border-radius:4px;border:1px solid #fecaca;"><?php echo __('Unpaid'); ?></span>
                            <?php } ?>
                        </td>
                        <td style="color:#0f172a;font-weight:700;"><?php echo h($payDate); ?></td>
                        <td style="text-align:center;">
                            <div class="subm-row-btn-grid">
                                <!-- LINE 1: Core Bill Operations (4 Buttons) -->
                                <a href="<?php echo $this->Html->url(array('action' => 'print_bill', $bi['id'])); ?>" target="_blank" class="subm-rbtn subm-rbtn-view" title="Print Bill">
                                    <i class="fa fa-print" style="color:#ffffff !important;"></i>
                                </a>
                                <?php if((float)$paidAmt > 0 || $bi['payment_status'] === 'paid'){ 
                                    $isPartial = ($bi['payment_status'] === 'partial');
                                    $slipLabel = $isPartial ? 'Partial Receipt' : 'Payment Receipt';
                                    $slipBg = $isPartial ? '#d97706' : '#059669';
                                ?>
                                    <a href="<?php echo $this->Html->url(array('action' => 'print_receipt', $bi['id'])); ?>" target="_blank" class="subm-rbtn" style="background:<?php echo $slipBg; ?> !important; color:#ffffff !important;" title="<?php echo $slipLabel; ?>">
                                        <i class="fa fa-file-text-o" style="color:#ffffff !important;"></i>
                                    </a>
                                <?php } else { ?>
                                    <button type="button" class="subm-rbtn subm-rbtn-pay" onclick="openPayModal(<?php echo htmlspecialchars(json_encode($bi)); ?>)" title="Pay Bill">
                                        <i class="fa fa-credit-card" style="color:#ffffff !important;"></i>
                                    </button>
                                <?php } ?>
                                <button type="button" class="subm-rbtn subm-rbtn-edit" onclick="openEditModal(<?php echo htmlspecialchars(json_encode($bi)); ?>)" title="Edit Bill">
                                    <i class="fa fa-pencil" style="color:#ffffff !important;"></i>
                                </button>
                                <button type="button" class="subm-rbtn" style="background:#0284c7 !important; color:#ffffff !important;" onclick="openAdjustModal(<?php echo htmlspecialchars(json_encode($bi)); ?>)" title="Adjust Bill (Debit / Credit)">
                                    <i class="fa fa-sliders" style="color:#ffffff !important;"></i>
                                </button>

                                <!-- LINE 2: Communication & Management (4 Buttons) -->
                                <?php 
                                    $isPaid = ($bi['payment_status'] === 'paid' || ((float)$paidAmt >= (float)$totalAmt && (float)$totalAmt > 0));
                                    $isPartial = ($bi['payment_status'] === 'partial' || ((float)$paidAmt > 0 && (float)$paidAmt < (float)$totalAmt));
                                    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
                                    $host = !empty($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
                                    $printBillFullUrl = $protocol . $host . $this->Html->url(array('action' => 'print_bill', $bi['id']));
                                    $printReceiptFullUrl = $protocol . $host . $this->Html->url(array('action' => 'print_receipt', $bi['id']));
                                    $payableAfterDue = $totalAmt + (($lateFee > 0) ? 0 : round($billAmt * 0.1, 2));

                                    if(!empty($c['phone'])){ 
                                        $cleanPhone = preg_replace('/[^0-9]/', '', (string)$c['phone']);
                                        if(substr($cleanPhone, 0, 1) === '0') $cleanPhone = '92' . substr($cleanPhone, 1);

                                        if($isPaid || ($isPartial && (float)$paidAmt > 0)){
                                            // Paid Bill WhatsApp Message (Refined & Modern)
                                            $waPaidText = "🏛️ *WHITE VILLAS LAHORE*\n"
                                                        . "✅ *OFFICIAL PAYMENT RECEIPT*\n"
                                                        . "━━━━━━━━━━━━━━━━━━━━━\n"
                                                        . "🧾 *Receipt #:* #" . $bi['bill_number'] . "  |  🆔 *ID:* " . $consumerId . "\n"
                                                        . "👤 *Resident:* " . $clientName . "\n"
                                                        . "🏠 *Property:* " . $unitNo . "\n"
                                                        . "📅 *Period:* " . $monthLabel . "\n"
                                                        . "━━━━━━━━━━━━━━━━━━━━━\n"
                                                        . "🟢 *PAID AMOUNT:* *PKR " . number_format($paidAmt, 2) . "*\n"
                                                        . "📅 *Payment Date:* " . $payDate . "\n"
                                                        . "💳 *Ref / TID:* " . (!empty($ref) && $ref !== '-' ? h($ref) : 'Cleared') . "\n"
                                                        . "⚖️ *Remaining Balance:* *PKR " . number_format($balAmt, 2) . "* (" . ($balAmt <= 0 ? "Fully Cleared" : "Partial") . ")\n"
                                                        . "✨ *Status:* *CLEARED & PAID IN FULL*\n"
                                                        . "━━━━━━━━━━━━━━━━━━━━━\n"
                                                        . "📎 *Official Paid Slip PDF Attached*\n"
                                                        . "📄 *View in Browser:* " . $printReceiptFullUrl . "\n"
                                                        . "🙏 *Thank you for your prompt payment!*\n"
                                                        . "📞 *Office:* +92 339 1101156";
                                            $waMsg = urlencode($waPaidText);
                                            $waTitle = "Send Paid Receipt via WhatsApp (Includes PDF Paid Slip Attachment)";
                                            $waBtnClass = "subm-rbtn subm-rbtn-wa subm-rbtn-wa-paid";
                                        } else {
                                            // Unpaid Bill WhatsApp Message (Refined, Modern & Concise)
                                            $unitsLine = ((float)$bi['units_consumed'] > 0) ? ("📊 *Units Consumed:* " . number_format((float)$bi['units_consumed'], 1) . " kWh\n") : "";
                                            $arrearsLine = ($arrears > 0) ? ("⏳ *Previous Arrears:* PKR " . number_format($arrears, 2) . "\n") : "";
                                            $lateFeeLine = ($lateFee > 0) ? ("⚠️ *Late Fee:* PKR " . number_format($lateFee, 2) . "\n") : "";

                                            $waUnpaidText = "🏛️ *WHITE VILLAS LAHORE*\n"
                                                          . "⚡ *UTILITY BILL NOTICE* — *" . $monthLabel . "*\n"
                                                          . "━━━━━━━━━━━━━━━━━━━━━\n"
                                                          . "📋 *Bill #:* #" . $bi['bill_number'] . "  |  🆔 *ID:* " . $consumerId . "\n"
                                                          . "👤 *Resident:* " . $clientName . "\n"
                                                          . "🏠 *Property:* " . $unitNo . "\n"
                                                          . "⚡ *Utility:* " . ucfirst($bi['utility_type']) . " (" . $meterNo . ")\n"
                                                          . $unitsLine
                                                          . "━━━━━━━━━━━━━━━━━━━━━\n"
                                                          . "💰 *Current Bill:* PKR " . number_format($billAmt, 2) . "\n"
                                                          . $arrearsLine
                                                          . $lateFeeLine
                                                          . "💵 *PAYABLE BY DUE DATE:* *PKR " . number_format($totalAmt, 2) . "*\n"
                                                          . "🔴 *Due Date:* *" . date('d-M-Y', strtotime((string)$bi['due_date'])) . "*\n"
                                                          . "🚨 *After Due Date:* PKR " . number_format($payableAfterDue, 2) . "\n"
                                                          . "━━━━━━━━━━━━━━━━━━━━━\n"
                                                          . "📎 *Official PDF Bill Attached*\n"
                                                          . "📄 *View in Browser:* " . $printBillFullUrl . "\n"
                                                          . "🏦 *Online Accounts:* Meezan / JazzCash (0339-1101156)\n"
                                                          . "📞 *Office:* +92 339 1101156";
                                            $waMsg = urlencode($waUnpaidText);
                                            $waTitle = "Send Unpaid Bill via WhatsApp (Includes PDF Bill Attachment)";
                                            $waBtnClass = "subm-rbtn subm-rbtn-wa";
                                        }
                                ?>
                                    <button type="button" class="<?php echo $waBtnClass; ?>" title="<?php echo $isPaid ? __('Send Paid Receipt via LeadBot WhatsApp API (Includes PDF Paid Slip)') : __('Send Unpaid Bill Notice via LeadBot WhatsApp API (Includes PDF Bill)'); ?>" data-wa-fallback="https://wa.me/<?php echo $cleanPhone; ?>?text=<?php echo $waMsg; ?>" onclick="openBillDispatchModal('whatsapp', <?php echo (int)$bi['id']; ?>, '<?php echo addslashes($clientName); ?>', '<?php echo !empty($c['phone']) ? addslashes($c['phone']) : ''; ?>', '<?php echo $isPaid ? 'paid' : 'unpaid'; ?>', '<?php echo addslashes($bi['bill_number']); ?>', '<?php echo addslashes($monthLabel); ?>', '<?php echo $isPaid ? number_format($paidAmt, 2) : number_format($totalAmt, 2); ?>', this)">
                                        <i class="fa fa-whatsapp" style="color:#ffffff !important;"></i>
                                    </button>
                                <?php } else { ?>
                                    <span class="subm-rbtn subm-rbtn-wa" style="opacity:0.4;" title="<?php echo __('No phone registered for resident'); ?>"><i class="fa fa-whatsapp" style="color:#ffffff !important;"></i></span>
                                <?php } ?>

                                <button type="button" class="subm-rbtn subm-rbtn-sms" title="<?php echo $isPaid ? __('Send Paid Receipt SMS via Default Gateway') : __('Send Unpaid Bill Reminder SMS via Default Gateway'); ?>" onclick="openBillDispatchModal('sms', <?php echo (int)$bi['id']; ?>, '<?php echo addslashes($clientName); ?>', '<?php echo !empty($c['phone']) ? addslashes($c['phone']) : ''; ?>', '<?php echo $isPaid ? 'paid' : 'unpaid'; ?>', '<?php echo addslashes($bi['bill_number']); ?>', '<?php echo addslashes($monthLabel); ?>', '<?php echo $isPaid ? number_format($paidAmt, 2) : number_format($totalAmt, 2); ?>', this)">
                                    <i class="fa fa-commenting" style="color:#ffffff !important;"></i>
                                </button>
                                <button type="button" class="subm-rbtn subm-rbtn-email" title="Send Email"><i class="fa fa-envelope" style="color:#ffffff !important;"></i></button>
                                <a href="<?php echo $this->Html->url(array('action' => 'delete', $bi['id'])); ?>" class="subm-rbtn subm-rbtn-del" title="Delete Bill" onclick="return confirm('Delete this bill record?');">
                                    <i class="fa fa-trash" style="color:#ffffff !important;"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php } } ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="8" style="color:#0f172a !important; font-weight:800;"><?php echo __('TOTALS: ') . $totalCount . __(' BILLS'); ?></th>
                    <th style="color:#0f172a !important; font-weight:800;"><?php echo number_format($sumBillAmount, 2); ?></th>
                    <th style="color:#64748b;">-</th>
                    <th style="color:#0f172a !important; font-weight:900; font-size:12px;"><?php echo number_format($sumTotal, 2); ?></th>
                    <th style="color:#15803d !important; font-weight:900;"><?php echo number_format($sumPaid, 2); ?></th>
                    <th style="color:#b91c1c !important; font-weight:900;"><?php echo number_format($sumBalance, 2); ?></th>
                    <th colspan="5"></th>
                </tr>
            </tfoot>
        </table>
    </div>
    <?php echo $this->Form->end(); ?>

    <!-- Bottom Section: Payment Reference Queue -->
    <div class="subm-queue-panel">
        <div class="subm-queue-header">
            <span class="fa fa-list-alt text-primary"></span> <?php echo __('Payment Reference Queue'); ?>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;margin-bottom:12px;">
            <div style="flex:1;min-width:200px;">
                <input type="text" placeholder="<?php echo __('Bill, Consumer, Client, Ref'); ?>" class="form-control input-sm" />
            </div>
            <button type="button" class="btn btn-sm btn-primary" style="font-weight:bold;"><?php echo __('Filter'); ?></button>
            <button type="button" class="btn btn-sm btn-default"><?php echo __('Reset'); ?></button>
            <span class="text-muted" style="font-size:11px;margin-left:auto;"><?php echo __('Showing 0 request(s)'); ?></span>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-striped" style="font-size:11.5px;margin:0;">
                <thead style="background:#0f172a;color:#fff;">
                    <tr>
                        <th>ID</th><th>BILL</th><th>CONSUMER</th><th>CLIENT</th><th>METHOD</th><th>REF #</th><th>PROOF</th><th>REQUESTED</th><th>APPLIED</th><th>PENDING</th><th>PAID DATE</th><th>STATUS</th><th>ACTION</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td colspan="13" class="text-center text-muted" style="padding:18px;"><?php echo __('No payment references found.'); ?></td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL 1: CREATE BULK BILL                                -->
<!-- ======================================================== -->
<!-- ======================================================== -->
<!-- MODAL 1: CREATE BULK BILL (WITH PRE-FLIGHT AUDIT)         -->
<!-- ======================================================== -->
<div class="modal fade" id="modalBulkBill" tabindex="-1" role="dialog">
    <div class="modal-dialog" style="max-width:720px;">
        <div class="modal-content" style="border-radius:8px;border:none;box-shadow:0 20px 40px rgba(0,0,0,0.2);">
            <?php echo $this->Form->create('BillingBill', array('action' => 'index', 'id' => 'bulkBillForm', 'class' => 'form-horizontal', 'onsubmit' => "return confirm('Generate ALL bills for selected month?');")); ?>
            <input type="hidden" name="subm_action" value="generate_bulk" />
            
            <div class="modal-header" style="background:#0f172a;color:#ffffff;padding:16px 20px;border-radius:8px 8px 0 0;display:flex;align-items:center;justify-content:space-between;">
                <h4 class="modal-title" style="font-weight:800;font-size:17px;color:#ffffff;margin:0;">
                    <span class="fa fa-flash text-warning"></span> <?php echo __('Create Bulk Bill'); ?>
                </h4>
                <button type="button" class="close" data-dismiss="modal" style="color:#ffffff;opacity:0.9;font-size:22px;border:none;background:transparent;">&times;</button>
            </div>

            <div class="modal-body" style="padding:20px 22px;max-height:calc(85vh - 110px);overflow-y:auto;background:#ffffff;">
                <p style="font-size:12.5px;color:#475569;margin-bottom:16px;line-height:1.4;">
                    <?php echo __('Bulk generation automatically parses all active properties and meters, calculates energy consumption from recorded readings, applies property maintenance, and carries forward past unpaid arrears.'); ?>
                </p>

                <!-- Month & Society Scope Row -->
                <div class="row" style="margin-bottom:12px;">
                    <div class="col-sm-6">
                        <label style="font-weight:700;font-size:12.5px;color:#1e293b;margin-bottom:4px;display:block;">
                            <?php echo __('Billing Month'); ?> <span class="text-danger">*</span>
                        </label>
                        <input type="month" name="data[BillingBill][billing_period]" id="bulkMonth" value="<?php echo date('Y-m'); ?>" class="form-control" required style="border-radius:6px;border:1.5px solid #cbd5e1;height:38px;font-weight:700;" onchange="runBulkPreflightAudit()" />
                    </div>
                    <div class="col-sm-6">
                        <label style="font-weight:700;font-size:12.5px;color:#1e293b;margin-bottom:4px;display:block;">
                            <?php echo __('Society / Sector Scope'); ?>
                        </label>
                        <select name="data[BillingBill][society_id]" id="bulkSocietyId" class="form-control" style="border-radius:6px;border:1.5px solid #cbd5e1;height:38px;font-weight:600;" onchange="runBulkPreflightAudit()">
                            <option value="0"><?php echo __('All Societies & Properties'); ?></option>
                            <?php foreach($societies as $sId => $sName){ ?>
                                <option value="<?php echo $sId; ?>"><?php echo h($sName); ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <!-- Dates & Template Row -->
                <div class="row" style="margin-bottom:12px;">
                    <div class="col-sm-4">
                        <label style="font-weight:700;font-size:12.5px;color:#1e293b;margin-bottom:4px;display:block;">
                            <?php echo __('Issue Date'); ?> <span class="text-danger">*</span>
                        </label>
                        <input type="date" name="data[BillingBill][issue_date]" value="<?php echo date('Y-m-d'); ?>" class="form-control" required style="border-radius:6px;border:1.5px solid #cbd5e1;height:38px;font-weight:600;" />
                    </div>
                    <div class="col-sm-4">
                        <label style="font-weight:700;font-size:12.5px;color:#1e293b;margin-bottom:4px;display:block;">
                            <?php echo __('Due Date'); ?> <span class="text-danger">*</span>
                        </label>
                        <input type="date" name="data[BillingBill][due_date]" value="<?php echo date('Y-m-d', strtotime('+10 days')); ?>" class="form-control" required style="border-radius:6px;border:1.5px solid #cbd5e1;height:38px;font-weight:600;" />
                    </div>
                    <div class="col-sm-4">
                        <label style="font-weight:700;font-size:12.5px;color:#1e293b;margin-bottom:4px;display:block;">
                            <?php echo __('Bill Template'); ?>
                        </label>
                        <?php echo $this->Form->input('template_id', array('type' => 'select', 'options' => $templates, 'class' => 'form-control', 'style' => 'border-radius:6px;border:1.5px solid #cbd5e1;height:38px;font-weight:600;', 'label' => false, 'div' => false)); ?>
                    </div>
                </div>

                <!-- PRE-FLIGHT AUDIT KPI CARD BOX -->
                <div style="background:#f8fafc;border:1.5px solid #cbd5e1;border-radius:8px;padding:14px 16px;margin:16px 0;">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
                        <span style="font-weight:800;font-size:12.5px;color:#0f172a;text-transform:uppercase;letter-spacing:0.4px;">
                            <i class="fa fa-shield text-primary"></i> <?php echo __('Pre-Flight Validation & Revenue Audit'); ?>
                        </span>
                        <button type="button" class="btn btn-xs btn-default" onclick="runBulkPreflightAudit()" style="font-weight:700;font-size:11px;">
                            <i class="fa fa-refresh"></i> <?php echo __('Re-Scan'); ?>
                        </button>
                    </div>

                    <div style="display:grid;grid-template-columns:repeat(4, 1fr);gap:8px;margin-bottom:10px;">
                        <div style="background:#ecfdf5;border:1px solid #a7f3d0;border-radius:6px;padding:8px 10px;text-align:center;">
                            <div style="font-size:10.5px;font-weight:800;color:#065f46;text-transform:uppercase;">Readings Ready</div>
                            <div id="pfReadingsFound" style="font-size:16px;font-weight:900;color:#047857;">-</div>
                        </div>
                        <div style="background:#fff7ed;border:1px solid #fed7aa;border-radius:6px;padding:8px 10px;text-align:center;">
                            <div style="font-size:10.5px;font-weight:800;color:#9a3412;text-transform:uppercase;">Missing Readings</div>
                            <div id="pfMissingReadings" style="font-size:16px;font-weight:900;color:#c2410c;">-</div>
                        </div>
                        <div style="background:#f1f5f9;border:1px solid #cbd5e1;border-radius:6px;padding:8px 10px;text-align:center;">
                            <div style="font-size:10.5px;font-weight:800;color:#475569;text-transform:uppercase;">Existing Bills</div>
                            <div id="pfDuplicates" style="font-size:16px;font-weight:900;color:#334155;">-</div>
                        </div>
                        <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:6px;padding:8px 10px;text-align:center;">
                            <div style="font-size:10.5px;font-weight:800;color:#1e40af;text-transform:uppercase;">Est. Billing</div>
                            <div id="pfEstRevenue" style="font-size:14px;font-weight:900;color:#1d4ed8;">PKR -</div>
                        </div>
                    </div>

                    <!-- Collapsible Missing Meters Drawer -->
                    <div id="pfMissingAlert" style="display:none;background:#fff1f2;border:1px solid #fecdd3;border-radius:6px;padding:8px 12px;font-size:11.5px;color:#9f1239;">
                        <strong><i class="fa fa-exclamation-triangle"></i> Attention:</strong> <span id="pfMissingCountText">0</span> meters do not have a recorded reading for this month. 
                        <a href="javascript:void(0);" onclick="$('#pfMissingList').slideToggle(150);" style="font-weight:800;color:#be123c;text-decoration:underline;margin-left:4px;">View Unread Meters</a>
                        <div id="pfMissingList" style="display:none;margin-top:8px;max-height:120px;overflow-y:auto;background:#ffffff;border:1px solid #fecdd3;border-radius:4px;padding:6px 10px;">
                        </div>
                    </div>
                </div>

                <!-- Calculation Toggles -->
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
                    <label style="margin:0;background:#f8fafc;border:1px solid #e2e8f0;padding:10px 12px;border-radius:6px;cursor:pointer;display:flex;align-items:center;gap:8px;">
                        <input type="checkbox" name="data[BillingBill][apply_maintenance]" id="bulkApplyMaint" value="1" checked onchange="runBulkPreflightAudit()" />
                        <span style="font-size:12.5px;font-weight:700;color:#1e293b;"><?php echo __('Include Property Maintenance'); ?></span>
                    </label>
                    <label style="margin:0;background:#f8fafc;border:1px solid #e2e8f0;padding:10px 12px;border-radius:6px;cursor:pointer;display:flex;align-items:center;gap:8px;">
                        <input type="checkbox" name="data[BillingBill][apply_other_charges]" id="bulkApplyOther" value="1" checked onchange="runBulkPreflightAudit()" />
                        <span style="font-size:12.5px;font-weight:700;color:#1e293b;"><?php echo __('Include Recurring Extra Charges'); ?></span>
                    </label>
                </div>

                <!-- Zero-Unit Handling Rule -->
                <div class="form-group" style="margin:0 0 14px 0;">
                    <label style="font-weight:700;font-size:12.5px;color:#1e293b;margin-bottom:4px;display:block;">
                        <?php echo __('Zero-Unit Meter Handling Rule'); ?>
                    </label>
                    <select name="data[BillingBill][zero_unit_action]" class="form-control" style="border-radius:6px;border:1.5px solid #cbd5e1;height:38px;font-weight:600;">
                        <option value="generate"><?php echo __('Generate Bill with 0 Units (Maintenance + Arrears Only)'); ?></option>
                        <option value="skip_zero"><?php echo __('Skip Generating Bill if 0 Units and No Reading'); ?></option>
                    </select>
                </div>

                <!-- Rate Overrides Section -->
                <div style="background:#f8fafc;padding:12px 14px;border-radius:6px;border:1px solid #e2e8f0;">
                    <div style="font-weight:700;font-size:12px;color:#334155;margin-bottom:8px;"><?php echo __('Bulk Rate Overrides (Optional)'); ?></div>
                    <div class="row">
                        <div class="col-sm-4">
                            <label style="font-size:11px;font-weight:700;color:#64748b;"><?php echo __('Electricity Rate'); ?></label>
                            <input type="number" step="0.01" name="data[BillingBill][rate_override_elec]" id="bulkRateElec" placeholder="Default: <?php echo number_format($defaultElecRate, 2); ?>" class="form-control" style="height:34px;font-size:12px;font-weight:600;" oninput="runBulkPreflightAudit()" />
                        </div>
                        <div class="col-sm-4">
                            <label style="font-size:11px;font-weight:700;color:#64748b;"><?php echo __('Water Rate'); ?></label>
                            <input type="number" step="0.01" name="data[BillingBill][rate_override_water]" id="bulkRateWater" placeholder="Default: <?php echo number_format($defaultWaterRate, 2); ?>" class="form-control" style="height:34px;font-size:12px;font-weight:600;" oninput="runBulkPreflightAudit()" />
                        </div>
                        <div class="col-sm-4">
                            <label style="font-size:11px;font-weight:700;color:#64748b;"><?php echo __('Gas Rate'); ?></label>
                            <input type="number" step="0.01" name="data[BillingBill][rate_override_gas]" id="bulkRateGas" placeholder="Default: <?php echo number_format($defaultGasRate, 2); ?>" class="form-control" style="height:34px;font-size:12px;font-weight:600;" oninput="runBulkPreflightAudit()" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer" style="background:#ffffff;border-top:1px solid #f1f5f9;padding:12px 22px;display:flex;align-items:center;justify-content:space-between;">
                <button type="button" class="btn btn-default" data-dismiss="modal" style="font-weight:600;"><?php echo __('Cancel'); ?></button>
                <button type="submit" class="btn btn-primary" style="font-weight:800;padding:8px 24px;border-radius:6px;font-size:13.5px;background:#0284c7;border:none;">
                    <i class="fa fa-flash"></i> <?php echo __('Create All Bills'); ?>
                </button>
            </div>
            <?php echo $this->Form->end(); ?>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL 2: CREATE SINGLE BILL                              -->
<!-- ======================================================== -->
<!-- ======================================================== -->
<!-- MODAL 2: CREATE SINGLE BILL (WordPress Plugin UI Exact)   -->
<!-- ======================================================== -->
<div class="modal fade" id="modalSingleBill" tabindex="-1" role="dialog">
    <div class="modal-dialog" style="max-width:680px;">
        <div class="modal-content" style="border-radius:8px;box-shadow:0 20px 40px rgba(0,0,0,0.2);border:none;">
            <?php echo $this->Form->create('BillingBill', array('action' => 'index', 'id' => 'singleBillForm')); ?>
            <input type="hidden" name="subm_action" value="generate_single" />
            
            <div class="modal-header" style="background:#ffffff;border-bottom:1px solid #f1f5f9;padding:16px 20px;border-radius:8px 8px 0 0;display:flex;align-items:center;justify-content:space-between;">
                <h4 class="modal-title" style="font-weight:700;font-size:17px;color:#1e293b;margin:0;">
                    <?php echo __('Create Single Bill'); ?>
                </h4>
                <button type="button" class="close" data-dismiss="modal" style="font-size:22px;color:#64748b;opacity:0.8;border:none;background:transparent;">&times;</button>
            </div>

            <div class="modal-body" style="padding:20px 22px;max-height:calc(85vh - 110px);overflow-y:auto;background:#ffffff;">
                <!-- Meter Selector -->
                <div class="form-group" style="margin-bottom:14px;">
                    <label style="font-weight:600;font-size:13px;color:#334155;margin-bottom:4px;display:block;">
                        <?php echo __('Meter'); ?>
                    </label>
                    <select name="data[BillingBill][meter_id]" id="sbMeterId" class="form-control" required style="width:100%;font-weight:600;border-radius:6px;border:1px solid #cbd5e1;height:40px;font-size:13px;" onchange="updateSingleBillPreview()">
                        <option value=""><?php echo __('Select'); ?></option>
                        <?php foreach($billMeters as $bm){ 
                            $bmt = $bm['BillingMeter']; 
                            $bmp = $bm['BillingProperty']; 
                            $pId = !empty($bmt['billing_property_id']) ? (int)$bmt['billing_property_id'] : 0;
                            $bmc = isset($propToClient[$pId]) ? $propToClient[$pId] : (!empty($bmp['BillingClient']) ? $bmp['BillingClient'] : array());
                            $cName = !empty($bmc['name']) ? $bmc['name'] : 'No Consumer';
                            $pTitle = !empty($bmp['title']) ? $bmp['title'] : ('Unit #' . $pId);
                            $cCode = !empty($bmc['client_code']) ? $bmc['client_code'] : ('WV' . (7000 + $pId));
                            $mNum = !empty($bmt['meter_number']) ? $bmt['meter_number'] : '0000';
                            $uType = !empty($bmt['utility_type']) ? $bmt['utility_type'] : 'Electricity';
                        ?>
                            <option value="<?php echo $bmt['id']; ?>" data-utility="<?php echo h($uType); ?>" data-meterno="<?php echo h($mNum); ?>">
                                <?php echo h($cName) . ' · Property: ' . h($pTitle) . ' · ' . h($cCode) . ' · Meter: ' . h($mNum) . ' · Type: ' . h($uType); ?>
                            </option>
                        <?php } ?>
                    </select>
                    <div style="font-size:11px;color:#94a3b8;margin-top:3px;">
                        <?php echo sprintf(__('Meters loaded: %d'), count($billMeters)); ?>
                    </div>
                </div>

                <!-- Month -->
                <div class="form-group" style="margin-bottom:14px;">
                    <label style="font-weight:600;font-size:13px;color:#334155;margin-bottom:4px;display:block;">
                        <?php echo __('Month'); ?>
                    </label>
                    <input type="month" name="data[BillingBill][billing_period]" id="sbMonth" value="<?php echo date('Y-m'); ?>" class="form-control" required style="border-radius:6px;border:1px solid #cbd5e1;height:40px;font-weight:600;font-size:13px;" onchange="updateSingleBillPreview()" />
                    <div id="sbReadingNotice" style="margin-top:6px;"></div>
                </div>

                <!-- On-the-Fly Reading Quick Entry Box (Auto-shown when no reading exists) -->
                <div id="sbReadingEntryBox" style="display:none;background:#fffbeb;border:1.5px solid #fde68a;border-radius:8px;padding:12px 14px;margin-bottom:14px;">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                        <span style="font-weight:800;font-size:11.5px;color:#92400e;text-transform:uppercase;">
                            <i class="fa fa-pencil"></i> <?php echo __('Quick Reading Entry (No Reading Recorded)'); ?>
                        </span>
                        <span style="font-size:11px;color:#b45309;font-weight:600;"><?php echo __('Saves reading automatically'); ?></span>
                    </div>
                    <div class="row">
                        <div class="col-sm-6">
                            <label style="font-size:11px;font-weight:700;color:#78350f;margin-bottom:3px;display:block;"><?php echo __('Previous Reading'); ?></label>
                            <input type="number" step="0.1" name="data[BillingBill][input_prev_reading]" id="sbInputPrev" placeholder="0.0" class="form-control" style="height:36px;font-weight:700;border:1.5px solid #fcd34d;" oninput="calcSingleBillFromInlineReadings()" />
                        </div>
                        <div class="col-sm-6">
                            <label style="font-size:11px;font-weight:700;color:#78350f;margin-bottom:3px;display:block;"><?php echo __('Current Reading'); ?></label>
                            <input type="number" step="0.1" name="data[BillingBill][input_curr_reading]" id="sbInputCurr" placeholder="0.0" class="form-control" style="height:36px;font-weight:700;border:1.5px solid #fcd34d;" oninput="calcSingleBillFromInlineReadings()" />
                        </div>
                    </div>
                    <div style="margin-top:6px;font-size:11.5px;font-weight:700;color:#b45309;">
                        <?php echo __('Calculated Units:'); ?> <span id="sbInlineUnits" style="font-size:14px;font-weight:900;color:#92400e;">0.0</span> Units
                    </div>
                </div>

                <!-- Issue Date -->
                <div class="form-group" style="margin-bottom:14px;">
                    <label style="font-weight:600;font-size:13px;color:#334155;margin-bottom:4px;display:block;">
                        <?php echo __('Issue Date'); ?>
                    </label>
                    <input type="date" name="data[BillingBill][issue_date]" id="sbIssueDate" value="<?php echo date('Y-m-d'); ?>" class="form-control" required style="border-radius:6px;border:1px solid #cbd5e1;height:40px;font-weight:600;font-size:13px;" onchange="calcDefaultDueDate()" />
                </div>

                <!-- Due Date -->
                <div class="form-group" style="margin-bottom:14px;">
                    <label style="font-weight:600;font-size:13px;color:#334155;margin-bottom:4px;display:block;">
                        <?php echo __('Due Date'); ?>
                    </label>
                    <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                        <label style="margin:0;font-weight:700;font-size:13px;cursor:pointer;display:inline-flex;align-items:center;gap:6px;color:#334155;">
                            <input type="checkbox" id="sbDefaultDueCheck" checked onchange="toggleDefaultDueDate()" />
                            <span><?php echo __('Default due'); ?></span>
                        </label>
                        <span id="sbDuePreviewText" style="font-size:11.5px;color:#94a3b8;">
                            Preview: <?php echo date('d-M-Y', strtotime('+' . $defaultDueDays . ' days')); ?>
                        </span>
                        <div style="margin-left:auto;min-width:180px;">
                            <input type="date" name="data[BillingBill][due_date]" id="sbDueDate" value="<?php echo date('Y-m-d', strtotime('+' . $defaultDueDays . ' days')); ?>" class="form-control" style="border-radius:6px;border:1px solid #cbd5e1;height:38px;font-weight:600;font-size:13px;" required />
                        </div>
                    </div>
                    <!-- Quick Presets -->
                    <div style="display:flex;align-items:center;gap:6px;margin-top:6px;">
                        <span style="font-size:11px;font-weight:700;color:#64748b;">Presets:</span>
                        <button type="button" class="btn btn-xs btn-default" onclick="setSbDueDays(7)" style="font-size:10.5px;font-weight:700;">+7 Days</button>
                        <button type="button" class="btn btn-xs btn-default" onclick="setSbDueDays(10)" style="font-size:10.5px;font-weight:700;">+10 Days</button>
                        <button type="button" class="btn btn-xs btn-default" onclick="setSbDueDays(15)" style="font-size:10.5px;font-weight:700;">+15 Days</button>
                        <button type="button" class="btn btn-xs btn-default" onclick="setSbDueMonthEnd()" style="font-size:10.5px;font-weight:700;">End of Month</button>
                    </div>
                </div>

                <!-- Template -->
                <div class="form-group" style="margin-bottom:14px;">
                    <label style="font-weight:600;font-size:13px;color:#334155;margin-bottom:4px;display:block;">
                        <?php echo __('Template'); ?>
                    </label>
                    <?php echo $this->Form->input('template_id', array('type' => 'select', 'options' => $templates, 'id' => 'sbTemplateId', 'class' => 'form-control', 'style' => 'border-radius:6px;border:1px solid #cbd5e1;height:40px;font-weight:600;font-size:13px;', 'label' => false, 'div' => false)); ?>
                </div>

                <!-- Apply Maintenance Breakdown Toggle -->
                <div class="form-group" style="margin-bottom:10px;">
                    <label style="font-weight:600;font-size:13px;color:#334155;margin-bottom:2px;display:block;">
                        <?php echo __('Apply Maintenance Breakdown'); ?>
                    </label>
                    <label style="margin:0;font-weight:700;font-size:13px;cursor:pointer;display:inline-flex;align-items:center;gap:6px;color:#334155;">
                        <input type="checkbox" name="data[BillingBill][apply_maintenance]" id="sbApplyMaint" value="1" checked onclick="toggleMaintSection()" onchange="toggleMaintSection()" />
                        <span><?php echo __('ON'); ?></span>
                    </label>
                </div>

                <!-- Apply Other Charges Toggle -->
                <div class="form-group" style="margin-bottom:16px;">
                    <label style="font-weight:600;font-size:13px;color:#334155;margin-bottom:2px;display:block;">
                        <?php echo __('Apply Other Charges'); ?>
                    </label>
                    <label style="margin:0;font-weight:700;font-size:13px;cursor:pointer;display:inline-flex;align-items:center;gap:6px;color:#334155;">
                        <input type="checkbox" name="data[BillingBill][apply_other_charges]" id="sbApplyOther" value="1" checked onclick="toggleOtherSection()" onchange="toggleOtherSection()" />
                        <span><?php echo __('ON'); ?></span>
                    </label>
                </div>

                <!-- Optional Rate Overrides Card Box -->
                <div style="background:#ffffff;border:1px solid #cbd5e1;border-radius:8px;padding:16px;margin-bottom:16px;">
                    <div style="font-weight:700;font-size:13px;color:#334155;margin-bottom:12px;">
                        <?php echo __('Optional Rate Overrides (Single Bill)'); ?>
                    </div>

                    <!-- Electricity Unit Rate -->
                    <div class="form-group" style="margin-bottom:12px;">
                        <label style="font-weight:600;font-size:12px;color:#475569;margin-bottom:3px;display:block;">
                            <?php echo __('Electricity Unit Rate (Optional)'); ?>
                        </label>
                        <input type="number" step="0.01" name="data[BillingBill][rate_override_elec]" id="sbRateElec" placeholder="Default: <?php echo number_format($defaultElecRate, 2); ?>" class="form-control" style="border-radius:6px;border:1px solid #cbd5e1;height:38px;font-weight:600;font-size:13px;" oninput="updateSingleBillPreview()" />
                    </div>

                    <!-- Water Unit Rate -->
                    <div class="form-group" style="margin-bottom:12px;">
                        <label style="font-weight:600;font-size:12px;color:#475569;margin-bottom:3px;display:block;">
                            <?php echo __('Water Unit Rate (Optional)'); ?>
                        </label>
                        <input type="number" step="0.01" name="data[BillingBill][rate_override_water]" id="sbRateWater" placeholder="Default: <?php echo number_format($defaultWaterRate, 2); ?>" class="form-control" style="border-radius:6px;border:1px solid #cbd5e1;height:38px;font-weight:600;font-size:13px;" oninput="updateSingleBillPreview()" />
                    </div>

                    <!-- Gas Unit Rate -->
                    <div class="form-group" style="margin-bottom:14px;">
                        <label style="font-weight:600;font-size:12px;color:#475569;margin-bottom:3px;display:block;">
                            <?php echo __('Gas Unit Rate (Optional)'); ?>
                        </label>
                        <input type="number" step="0.01" name="data[BillingBill][rate_override_gas]" id="sbRateGas" placeholder="Default: <?php echo number_format($defaultGasRate, 2); ?>" class="form-control" style="border-radius:6px;border:1px solid #cbd5e1;height:38px;font-weight:600;font-size:13px;" oninput="updateSingleBillPreview()" />
                    </div>

                    <!-- Maintenance Breakdown Box (Shown when ON, hidden when OFF) -->
                    <div class="form-group" id="sbMaintGroup" style="margin-bottom:14px;">
                        <label style="font-weight:600;font-size:12px;color:#475569;margin-bottom:6px;display:block;">
                            <?php echo __('Maintenance Breakdown (Optional Amount Override)'); ?>
                        </label>
                        <div id="sbMaintBox" style="background:#ffffff;border:none;padding:0;">
                            <div style="background:#ffffff;border:1px dashed #cbd5e1;border-radius:6px;padding:10px 12px;font-size:12px;color:#94a3b8;">
                                <?php echo __('No maintenance items found for selected property.'); ?>
                            </div>
                        </div>
                    </div>

                    <!-- Other Charges Box (Shown when ON, hidden when OFF) -->
                    <div class="form-group" id="sbOtherGroup" style="margin-bottom:14px;">
                        <label style="font-weight:600;font-size:12px;color:#475569;margin-bottom:6px;display:block;">
                            <?php echo __('Other Charges (Optional Amount Override)'); ?>
                        </label>
                        <div id="sbOtherBox" style="background:#ffffff;border:none;padding:0;">
                            <div style="background:#ffffff;border:1px dashed #cbd5e1;border-radius:6px;padding:10px 12px;font-size:12px;color:#94a3b8;">
                                <?php echo __('No active other charges for selected property.'); ?>
                            </div>
                        </div>
                    </div>

                    <!-- Total Bill Review (Auto) - 3x3 Grid -->
                    <div class="form-group" style="margin-bottom:0;">
                        <label style="font-weight:700;font-size:12.5px;color:#334155;margin-bottom:6px;display:block;">
                            <?php echo __('Total Bill Review (Auto)'); ?>
                        </label>
                        
                        <div style="display:grid;grid-template-columns:repeat(3, 1fr);gap:8px;">
                            <!-- Tile 1: Consumed Units -->
                            <div style="background:#ffffff;border:1px solid #cbd5e1;border-radius:6px;padding:8px 10px;">
                                <div style="font-size:10px;font-weight:600;color:#64748b;">Consumed Units</div>
                                <div id="statUnits" style="font-size:13.5px;font-weight:700;color:#0f172a;margin-top:2px;">0.00</div>
                            </div>
                            <!-- Tile 2: Utility Charges -->
                            <div style="background:#ffffff;border:1px solid #cbd5e1;border-radius:6px;padding:8px 10px;">
                                <div style="font-size:10px;font-weight:600;color:#64748b;">Utility Charges</div>
                                <div id="statUtility" style="font-size:13.5px;font-weight:700;color:#0f172a;margin-top:2px;">PKR 0.00</div>
                            </div>
                            <!-- Tile 3: Maintenance Charges -->
                            <div style="background:#ffffff;border:1px solid #cbd5e1;border-radius:6px;padding:8px 10px;">
                                <div style="font-size:10px;font-weight:600;color:#64748b;">Maintenance Charges</div>
                                <div id="statMaintenance" style="font-size:13.5px;font-weight:700;color:#0f172a;margin-top:2px;">PKR 0.00</div>
                            </div>
                            <!-- Tile 4: Other Charges -->
                            <div style="background:#ffffff;border:1px solid #cbd5e1;border-radius:6px;padding:8px 10px;">
                                <div style="font-size:10px;font-weight:600;color:#64748b;">Other Charges</div>
                                <div id="statOther" style="font-size:13.5px;font-weight:700;color:#0f172a;margin-top:2px;">PKR 0.00</div>
                            </div>
                            <!-- Tile 5: Arrears -->
                            <div style="background:#ffffff;border:1px solid #cbd5e1;border-radius:6px;padding:8px 10px;">
                                <div style="font-size:10px;font-weight:600;color:#64748b;">Arrears</div>
                                <div id="statArrears" style="font-size:13.5px;font-weight:700;color:#0f172a;margin-top:2px;">PKR 0.00</div>
                            </div>
                            <!-- Tile 6: Current Bill -->
                            <div style="background:#ffffff;border:1px solid #cbd5e1;border-radius:6px;padding:8px 10px;">
                                <div style="font-size:10px;font-weight:600;color:#64748b;">Current Bill</div>
                                <div id="statCurrentBill" style="font-size:13.5px;font-weight:700;color:#0f172a;margin-top:2px;">PKR 0.00</div>
                            </div>
                            <!-- Tile 7: Total Bill (Within Due) -->
                            <div style="background:#ffffff;border:1px solid #cbd5e1;border-radius:6px;padding:8px 10px;">
                                <div style="font-size:10px;font-weight:600;color:#64748b;">Total Bill (Within Due)</div>
                                <div id="statTotalWithinDue" style="font-size:14px;font-weight:800;color:#0f172a;margin-top:2px;">PKR 0.00</div>
                            </div>
                            <!-- Tile 8: Late Fee (After Due) -->
                            <div style="background:#ffffff;border:1px solid #cbd5e1;border-radius:6px;padding:8px 10px;">
                                <div style="font-size:10px;font-weight:600;color:#64748b;">Late Fee (After Due)</div>
                                <div id="statLateFee" style="font-size:13.5px;font-weight:700;color:#0f172a;margin-top:2px;">PKR 0.00</div>
                            </div>
                            <!-- Tile 9: Payable After Due -->
                            <div style="background:#ffffff;border:1px solid #cbd5e1;border-radius:6px;padding:8px 10px;">
                                <div style="font-size:10px;font-weight:600;color:#64748b;">Payable After Due</div>
                                <div id="statPayableAfterDue" style="font-size:14px;font-weight:800;color:#0f172a;margin-top:2px;">PKR 0.00</div>
                            </div>
                        </div>

                        <!-- Duplicate Alert Banner -->
                        <div id="sbDupBanner" style="margin-top:10px;background:#fff1f2;border:1px solid #fecdd3;border-radius:6px;padding:8px 12px;color:#e11d48;font-size:11.5px;font-weight:600;display:none;">
                            Bill for this Meter No (<span id="sbDupMeterNo"></span>) and month <span id="sbDupMonth"></span> already exists. New creation will be blocked.
                        </div>
                    </div>

                </div>
            </div>

            <div class="modal-footer" style="background:#ffffff;border-top:1px solid #f1f5f9;padding:12px 20px;display:flex;align-items:center;justify-content:space-between;">
                <label style="margin:0;font-weight:700;font-size:12.5px;cursor:pointer;display:inline-flex;align-items:center;gap:6px;color:#1e293b;">
                    <input type="checkbox" name="data[BillingBill][auto_print]" value="1" checked />
                    <span><?php echo __('Open Printable Invoice in New Tab Immediately'); ?></span>
                </label>
                <button type="submit" id="sbSubmitBtn" class="btn" style="background:#0284c7;color:#ffffff;font-weight:700;padding:8px 30px;border-radius:6px;font-size:13.5px;border:none;box-shadow:none;">
                    <?php echo __('Create Bill'); ?>
                </button>
            </div>
            <?php echo $this->Form->end(); ?>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL 3: CREATE CUSTOM BILL (ITEMIZED BUILDER)           -->
<!-- ======================================================== -->
<div class="modal fade" id="modalCustomBill" tabindex="-1" role="dialog">
    <div class="modal-dialog" style="max-width:760px;">
        <div class="modal-content" style="border-radius:8px;box-shadow:0 20px 40px rgba(0,0,0,0.2);border:none;">
            <?php echo $this->Form->create('BillingBill', array('action' => 'index', 'id' => 'customBillForm', 'class' => 'form-horizontal')); ?>
            <input type="hidden" name="subm_action" value="generate_custom" />
            
            <div class="modal-header" style="background:#0f172a;color:#ffffff;padding:16px 20px;border-radius:8px 8px 0 0;display:flex;align-items:center;justify-content:space-between;">
                <h4 class="modal-title" style="font-weight:800;font-size:17px;color:#ffffff;margin:0;">
                    <span class="fa fa-wrench text-success"></span> <?php echo __('Create Custom Invoice / Bill'); ?>
                </h4>
                <button type="button" class="close" data-dismiss="modal" style="color:#ffffff;opacity:0.9;font-size:22px;border:none;background:transparent;">&times;</button>
            </div>

            <div class="modal-body" style="padding:20px 22px;max-height:calc(85vh - 110px);overflow-y:auto;background:#ffffff;">
                <!-- Scope & Property -->
                <div class="row" style="margin-bottom:12px;">
                    <div class="col-sm-6">
                        <label style="font-weight:700;font-size:12.5px;color:#1e293b;margin-bottom:4px;display:block;">
                            <?php echo __('Society / Project'); ?> <span class="text-danger">*</span>
                        </label>
                        <select name="data[BillingBill][society_id]" class="form-control" required style="border-radius:6px;border:1.5px solid #cbd5e1;height:38px;font-weight:600;">
                            <?php foreach($societies as $sId => $sName){ ?>
                                <option value="<?php echo $sId; ?>"><?php echo h($sName); ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-sm-6">
                        <label style="font-weight:700;font-size:12.5px;color:#1e293b;margin-bottom:4px;display:block;">
                            <?php echo __('Property Unit / Consumer'); ?> <span class="text-danger">*</span>
                        </label>
                        <select name="data[BillingBill][property_id]" id="cbPropertyId" class="form-control" required style="border-radius:6px;border:1.5px solid #cbd5e1;height:38px;font-weight:600;">
                            <option value="">-- Select Property Unit --</option>
                            <?php foreach($properties as $pr){ $prop = $pr['BillingProperty']; $cl = $pr['BillingClient']; ?>
                                <option value="<?php echo $prop['id']; ?>">
                                    <?php echo (!empty($cl['name']) ? $cl['name'] : 'No Client') . ' - ' . (!empty($prop['property_no']) ? $prop['property_no'] : '-') . ' (' . $prop['title'] . ')'; ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <!-- Dates & Period -->
                <div class="row" style="margin-bottom:14px;">
                    <div class="col-sm-4">
                        <label style="font-weight:700;font-size:12.5px;color:#1e293b;margin-bottom:4px;display:block;">
                            <?php echo __('Billing Month'); ?> <span class="text-danger">*</span>
                        </label>
                        <input type="month" name="data[BillingBill][billing_period]" value="<?php echo date('Y-m'); ?>" class="form-control" required style="border-radius:6px;border:1.5px solid #cbd5e1;height:38px;font-weight:600;" />
                    </div>
                    <div class="col-sm-4">
                        <label style="font-weight:700;font-size:12.5px;color:#1e293b;margin-bottom:4px;display:block;">
                            <?php echo __('Issue Date'); ?> <span class="text-danger">*</span>
                        </label>
                        <input type="date" name="data[BillingBill][issue_date]" value="<?php echo date('Y-m-d'); ?>" class="form-control" required style="border-radius:6px;border:1.5px solid #cbd5e1;height:38px;font-weight:600;" />
                    </div>
                    <div class="col-sm-4">
                        <label style="font-weight:700;font-size:12.5px;color:#1e293b;margin-bottom:4px;display:block;">
                            <?php echo __('Due Date'); ?> <span class="text-danger">*</span>
                        </label>
                        <input type="date" name="data[BillingBill][due_date]" value="<?php echo date('Y-m-d', strtotime('+10 days')); ?>" class="form-control" required style="border-radius:6px;border:1.5px solid #cbd5e1;height:38px;font-weight:600;" />
                    </div>
                </div>

                <!-- ITEMIZED CHARGES BUILDER -->
                <div style="background:#f8fafc;border:1.5px solid #cbd5e1;border-radius:8px;padding:14px 16px;margin-bottom:14px;">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                        <span style="font-weight:800;font-size:12.5px;color:#0f172a;text-transform:uppercase;">
                            <i class="fa fa-list-ol text-success"></i> <?php echo __('Itemized Charges & Breakdown Builder'); ?>
                        </span>
                        <button type="button" class="btn btn-xs btn-success" onclick="addCustomBillRow()" style="font-weight:700;font-size:11.5px;padding:3px 10px;">
                            <i class="fa fa-plus"></i> <?php echo __('Add Line Item'); ?>
                        </button>
                    </div>

                    <!-- Quick Category Presets Bar -->
                    <div style="margin-bottom:10px;display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
                        <span style="font-size:11px;font-weight:700;color:#64748b;">Quick Presets:</span>
                        <button type="button" class="btn btn-xs btn-default" onclick="addCustomBillRow('Water Delivery', 'Water Bowser / Tanker Delivery', 1500)" style="font-size:10.5px;font-weight:600;">+ Water Bowser</button>
                        <button type="button" class="btn btn-xs btn-default" onclick="addCustomBillRow('Construction Water', 'Construction Water Supply Fee', 5000)" style="font-size:10.5px;font-weight:600;">+ Construction Water</button>
                        <button type="button" class="btn btn-xs btn-default" onclick="addCustomBillRow('Fine / Penalty', 'Debris / Construction Material Fine', 3000)" style="font-size:10.5px;font-weight:600;">+ Debris Fine</button>
                        <button type="button" class="btn btn-xs btn-default" onclick="addCustomBillRow('Connection Fee', 'New Utility Connection Fee', 10000)" style="font-size:10.5px;font-weight:600;">+ New Connection</button>
                        <button type="button" class="btn btn-xs btn-default" onclick="addCustomBillRow('Transfer Fee', 'Property Transfer / NOC Fee', 25000)" style="font-size:10.5px;font-weight:600;">+ Transfer Fee</button>
                        <button type="button" class="btn btn-xs btn-default" onclick="addCustomBillRow('Security Deposit', 'Refundable Security Deposit', 20000)" style="font-size:10.5px;font-weight:600;">+ Security Deposit</button>
                    </div>

                    <!-- Dynamic Rows Container -->
                    <div id="cbRowsContainer" style="display:flex;flex-direction:column;gap:8px;">
                        <!-- Initial Row -->
                        <div class="cb-item-row" style="display:grid;grid-template-columns:140px 1fr 120px 36px;gap:8px;align-items:center;">
                            <select name="data[BillingBill][items][0][category]" class="form-control input-sm" style="border-radius:4px;font-weight:600;font-size:12px;">
                                <option value="Custom Charge">Custom Charge</option>
                                <option value="Water Delivery">Water Delivery</option>
                                <option value="Construction Water">Construction Water</option>
                                <option value="Fine / Penalty">Fine / Penalty</option>
                                <option value="Connection Fee">Connection Fee</option>
                                <option value="Transfer Fee">Transfer Fee</option>
                                <option value="Security Deposit">Security Deposit</option>
                                <option value="Maintenance">Maintenance</option>
                                <option value="Other">Other</option>
                            </select>
                            <input type="text" name="data[BillingBill][items][0][desc]" placeholder="Item description (e.g. Water Tanker Delivery #4)" class="form-control input-sm" required style="border-radius:4px;font-weight:600;font-size:12px;" />
                            <input type="number" step="0.01" name="data[BillingBill][items][0][amount]" placeholder="0.00" class="form-control input-sm cb-item-amount" required style="border-radius:4px;font-weight:700;font-size:12.5px;text-align:right;" oninput="recalcCustomBillTotal()" />
                            <button type="button" class="btn btn-sm btn-danger" onclick="removeCustomBillRow(this)" style="padding:4px 8px;border-radius:4px;"><i class="fa fa-times"></i></button>
                        </div>
                    </div>

                    <!-- Grand Total Preview -->
                    <div style="margin-top:12px;padding-top:10px;border-top:1px solid #cbd5e1;display:flex;align-items:center;justify-content:space-between;">
                        <span style="font-weight:800;font-size:13px;color:#0f172a;">Total Invoice Amount:</span>
                        <span style="font-size:16px;font-weight:900;color:#047857;">PKR <span id="cbGrandTotal">0.00</span></span>
                    </div>
                </div>

                <!-- Optional Recurring Toggles -->
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <label style="margin:0;background:#f8fafc;border:1px solid #e2e8f0;padding:10px 12px;border-radius:6px;cursor:pointer;display:flex;align-items:center;gap:8px;">
                        <input type="checkbox" name="data[BillingBill][include_maintenance]" value="1" />
                        <span style="font-size:12px;font-weight:700;color:#1e293b;"><?php echo __('Include Property Maintenance Fee'); ?></span>
                    </label>
                    <label style="margin:0;background:#f8fafc;border:1px solid #e2e8f0;padding:10px 12px;border-radius:6px;cursor:pointer;display:flex;align-items:center;gap:8px;">
                        <input type="checkbox" name="data[BillingBill][include_other_charges]" value="1" />
                        <span style="font-size:12px;font-weight:700;color:#1e293b;"><?php echo __('Include Active Recurring Charges'); ?></span>
                    </label>
                </div>
            </div>

            <div class="modal-footer" style="background:#ffffff;border-top:1px solid #f1f5f9;padding:12px 22px;display:flex;align-items:center;justify-content:space-between;">
                <button type="button" class="btn btn-default" data-dismiss="modal" style="font-weight:600;"><?php echo __('Cancel'); ?></button>
                <button type="submit" class="btn btn-success" style="font-weight:800;padding:8px 24px;border-radius:6px;font-size:13.5px;background:#16a34a;border:none;">
                    <i class="fa fa-check"></i> <?php echo __('Create Custom Invoice'); ?>
                </button>
            </div>
            <?php echo $this->Form->end(); ?>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL 4: RECORD PAYMENT                                  -->
<!-- ======================================================== -->
<div class="modal fade" id="modalPayment" tabindex="-1" role="dialog">
    <div class="modal-dialog" style="max-width:550px;">
        <div class="modal-content">
            <div class="modal-header" style="background:#16a34a;color:#fff;">
                <button type="button" class="close" data-dismiss="modal" style="color:#fff;">&times;</button>
                <h4 class="modal-title"><span class="fa fa-credit-card"></span> <?php echo __('Record Payment'); ?></h4>
            </div>
            <?php echo $this->Form->create('BillingBill', array('action' => 'index', 'class' => 'form-horizontal')); ?>
            <input type="hidden" name="subm_action" value="record_payment" />
            <input type="hidden" name="data[BillingBill][bill_id]" id="payBillId" value="" />
            <div class="modal-body">
                <div class="well" style="background:#f8fafc;padding:10px;margin-bottom:15px;font-size:12px;">
                    <div><strong>Bill #:</strong> <span id="payBillNo"></span></div>
                    <div><strong>Total Due:</strong> PKR <span id="payTotal"></span></div>
                    <div><strong>Balance:</strong> PKR <span id="payBal" style="color:#dc2626;font-weight:bold;"></span></div>
                    <div><strong>Late / Overdue Fee:</strong> PKR <span id="payLateFee" style="color:#e11d48;font-weight:bold;">0.00</span></div>
                </div>

                <div class="form-group">
                    <label class="col-sm-4 control-label"><?php echo __('Payment Mode'); ?> <span class="text-danger">*</span></label>
                    <div class="col-sm-8">
                        <select name="data[BillingBill][payment_type]" id="payTypeSelect" class="form-control" onchange="togglePayTypeFields()" required>
                            <option value="full"><?php echo __('Full Payment (Settle Entire Bill)'); ?></option>
                            <option value="partial"><?php echo __('Partial Payment'); ?></option>
                        </select>
                    </div>
                </div>

                <div class="form-group" id="payAmountGroup" style="display:none;">
                    <label class="col-sm-4 control-label"><?php echo __('Partial Paid Amount (PKR)'); ?> <span class="text-danger">*</span></label>
                    <div class="col-sm-8">
                        <input type="number" step="0.01" name="data[BillingBill][pay_amount]" id="payAmount" class="form-control" placeholder="0.00" />
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-4 control-label"><?php echo __('Payment Date'); ?> <span class="text-danger">*</span></label>
                    <div class="col-sm-8">
                        <input type="date" name="data[BillingBill][pay_date]" value="<?php echo date('Y-m-d'); ?>" class="form-control" required />
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-4 control-label"><?php echo __('Overdue Fee Waiver'); ?></label>
                    <div class="col-sm-8">
                        <label style="font-weight:700;color:#dc2626;cursor:pointer;margin-top:5px;display:block;">
                            <input type="checkbox" name="data[BillingBill][waive_late_fee]" id="payWaiveFeeCheck" value="1" onchange="toggleWaiveFeeInput()" /> <?php echo __('Waive Overdue / Late Fee'); ?>
                        </label>
                    </div>
                </div>

                <div class="form-group" id="payWaivedGroup" style="display:none;">
                    <label class="col-sm-4 control-label"><?php echo __('Waived Amount (PKR)'); ?></label>
                    <div class="col-sm-8">
                        <input type="number" step="0.01" name="data[BillingBill][waived_late_fee]" id="payWaivedFeeAmt" placeholder="0.00" class="form-control" />
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-4 control-label"><?php echo __('Payment Method'); ?> <span class="text-danger">*</span></label>
                    <div class="col-sm-8">
                        <select name="data[BillingBill][payment_method]" class="form-control" required>
                            <option value="Cash">Cash</option>
                            <option value="Bank Transfer">Bank Transfer (UBL / Meezan)</option>
                            <option value="JazzCash">JazzCash</option>
                            <option value="EasyPaisa">EasyPaisa</option>
                            <option value="Cheque">Cheque</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-4 control-label"><?php echo __('Payment Ref / Note'); ?></label>
                    <div class="col-sm-8">
                        <input type="text" name="data[BillingBill][payment_ref]" placeholder="<?php echo __('Transaction ID, Cheque No, etc.'); ?>" class="form-control" />
                    </div>
                </div>

                <div style="background:#f0fdf4;border:1.5px solid #86efac;border-radius:8px;padding:12px;margin-top:14px;display:flex;align-items:center;gap:12px;">
                    <i class="fa fa-paper-plane text-success" style="font-size:22px;"></i>
                    <span style="font-size:12px;color:#166534;font-weight:700;">
                        <?php echo __('After confirming payment, you will immediately get 1-click buttons to send the Official Paid Bill Notification via WhatsApp & SMS with the official PDF Paid Slip attached.'); ?>
                    </span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Cancel'); ?></button>
                <button type="submit" class="btn btn-success" style="font-weight:800;"><?php echo __('Confirm Payment'); ?></button>
            </div>
            <?php echo $this->Form->end(); ?>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- ======================================================== -->
<!-- MODAL: POST-PAYMENT SUCCESS NOTIFICATION DELIVERY        -->
<!-- ======================================================== -->
<?php if(!empty($justPaidBill)){ ?>
<div class="modal fade in" id="modalPaymentSuccessNotification" tabindex="-1" role="dialog" style="display:block;background:rgba(15,23,42,0.7);z-index:99999;">
    <div class="modal-dialog" style="max-width:580px;margin-top:60px;">
        <div class="modal-content" style="border-radius:14px;box-shadow:0 25px 50px -12px rgba(0,0,0,0.45);border:none;overflow:hidden;">
            <div class="modal-header" style="background:linear-gradient(135deg,#059669 0%,#10b981 100%);color:#ffffff;padding:20px 24px;">
                <button type="button" class="close" data-dismiss="modal" onclick="closePaymentSuccessModal()" style="color:#ffffff;opacity:0.9;font-size:26px;">&times;</button>
                <div style="display:flex;align-items:center;gap:14px;">
                    <div style="width:46px;height:46px;border-radius:50%;background:rgba(255,255,255,0.22);display:flex;align-items:center;justify-content:center;font-size:24px;">
                        ✓
                    </div>
                    <div>
                        <h4 class="modal-title" style="font-weight:900;font-size:19px;margin:0;color:#ffffff;">
                            <?php echo __('Payment Recorded Successfully!'); ?>
                        </h4>
                        <span style="font-size:12.5px;opacity:0.95;">
                            <?php echo __('Official receipt generated & income transaction posted to ledger.'); ?>
                        </span>
                    </div>
                </div>
            </div>

            <div class="modal-body" style="padding:22px;background:#ffffff;">
                <!-- Summary Card -->
                <div style="background:#f8fafc;border:1.5px solid #e2e8f0;border-radius:10px;padding:16px 18px;margin-bottom:18px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;border-bottom:1px dashed #cbd5e1;padding-bottom:8px;">
                        <span style="font-size:12px;color:#64748b;font-weight:700;text-transform:uppercase;"><?php echo __('Bill Number:'); ?></span>
                        <strong style="color:#0284c7;font-size:15px;"><?php echo h($justPaidBill['bill_number']); ?></strong>
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                        <span style="font-size:13px;color:#475569;"><?php echo __('Resident:'); ?></span>
                        <strong style="color:#0f172a;font-size:13.5px;"><?php echo h($justPaidBill['client_name']); ?></strong>
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                        <span style="font-size:13px;color:#475569;"><?php echo __('Property / Plot:'); ?></span>
                        <strong style="color:#0f172a;"><?php echo h($justPaidBill['unit_no']); ?></strong>
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                        <span style="font-size:13px;color:#475569;"><?php echo __('Phone / WhatsApp:'); ?></span>
                        <strong style="color:#059669;font-family:monospace;"><?php echo h(!empty($justPaidBill['phone']) ? $justPaidBill['phone'] : __('No Phone')); ?></strong>
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-top:10px;padding-top:10px;border-top:1.5px solid #e2e8f0;">
                        <span style="font-size:14px;color:#0f172a;font-weight:800;"><?php echo __('Amount Received:'); ?></span>
                        <strong style="color:#16a34a;font-size:20px;font-weight:900;">PKR <?php echo number_format($justPaidBill['paid_amount'], 2); ?></strong>
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-top:4px;">
                        <span style="font-size:12px;color:#64748b;"><?php echo __('Remaining Balance:'); ?></span>
                        <strong style="color:<?php echo ($justPaidBill['balance_amount'] > 0) ? '#dc2626' : '#16a34a'; ?>;">
                            PKR <?php echo number_format($justPaidBill['balance_amount'], 2); ?>
                            <?php if($justPaidBill['balance_amount'] <= 0){ echo ' (Fully Cleared & Settled)'; } ?>
                        </strong>
                    </div>
                </div>

                <!-- Instant Notification Dispatch Action Box -->
                <div style="background:#f0fdf4;border:1.5px solid #86efac;border-radius:10px;padding:18px;text-align:center;">
                    <div style="font-size:13px;font-weight:800;color:#166534;margin-bottom:14px;text-transform:uppercase;letter-spacing:0.5px;">
                        <i class="fa fa-paper-plane"></i> <?php echo __('Send Paid Bill Notification to Resident'); ?>
                    </div>
                    <div style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap;">
                        <?php if(!empty($justPaidBill['clean_phone'])){ ?>
                            <button type="button" class="btn btn-success" id="btnPostPayWhatsapp" onclick="sendBillWhatsappAjax(<?php echo (int)$justPaidBill['bill_id']; ?>, this)" data-wa-fallback="<?php echo $justPaidBill['wa_url']; ?>" style="background:#059669;border-color:#059669;font-weight:800;padding:10px 18px;font-size:13.5px;display:inline-flex;align-items:center;gap:8px;box-shadow:0 3px 10px rgba(5,150,105,0.35);">
                                <i class="fa fa-whatsapp" style="font-size:20px;"></i> <?php echo __('Send WhatsApp via LeadBot API'); ?>
                            </button>
                            <button type="button" class="btn btn-info" id="btnPostPaySms" onclick="sendBillSmsAjax(<?php echo (int)$justPaidBill['bill_id']; ?>, this)" style="background:#0284c7;border-color:#0284c7;font-weight:800;padding:10px 18px;font-size:13.5px;display:inline-flex;align-items:center;gap:8px;box-shadow:0 3px 10px rgba(2,132,199,0.35);">
                                <i class="fa fa-commenting" style="font-size:18px;"></i> <?php echo __('Send SMS via Default Gateway'); ?>
                            </button>
                        <?php } else { ?>
                            <div class="alert alert-warning" style="margin:0 0 10px 0;font-size:12px;">
                                <i class="fa fa-exclamation-triangle"></i> <?php echo __('Client has no phone number registered. Update client profile to enable WhatsApp/SMS.'); ?>
                            </div>
                        <?php } ?>
                        <a href="<?php echo $justPaidBill['print_receipt_url']; ?>" target="_blank" class="btn btn-default" style="font-weight:800;padding:10px 18px;font-size:13.5px;display:inline-flex;align-items:center;gap:8px;">
                            <i class="fa fa-print"></i> <?php echo __('Print Paid Slip'); ?>
                        </a>
                    </div>
                    <?php if(!empty($justPaidBill['clean_phone'])){ ?>
                        <div style="margin-top:10px;font-size:11.5px;color:#64748b;">
                            <span style="opacity:0.85;"><?php echo __('Direct LeadBot backend integration active.'); ?></span> &bull; 
                            <a href="<?php echo $justPaidBill['wa_url']; ?>" target="_blank" style="color:#059669;text-decoration:none;font-weight:700;"><i class="fa fa-external-link"></i> <?php echo __('Open Desktop WhatsApp as fallback'); ?></a>
                        </div>
                    <?php } ?>
                    <div id="postPaySmsStatus" style="margin-top:12px;font-weight:700;font-size:12.5px;display:none;"></div>
                </div>
            </div>

            <div class="modal-footer" style="background:#f8fafc;padding:12px 20px;text-align:right;">
                <button type="button" class="btn btn-default" data-dismiss="modal" onclick="closePaymentSuccessModal()" style="font-weight:700;padding:8px 20px;">
                    <?php echo __('Done / Close'); ?>
                </button>
            </div>
        </div>
    </div>
</div>
<?php } ?>

<!-- ======================================================== -->
<!-- MODAL: MODERN PROFESSIONAL DISPATCH DIALOG WITH CLOSE/CANCEL -->
<!-- ======================================================== -->
<div class="modal fade" id="modalModernDispatchNotice" tabindex="-1" role="dialog" style="background:rgba(15,23,42,0.7);z-index:999999;">
    <div class="modal-dialog" style="max-width:540px;margin-top:70px;">
        <div class="modal-content" style="border-radius:16px;box-shadow:0 25px 60px -15px rgba(0,0,0,0.5);border:none;overflow:hidden;background:#ffffff;">
            <!-- Dynamic Header -->
            <div id="mDispatchHeader" style="background:linear-gradient(135deg,#059669 0%,#10b981 100%);color:#ffffff;padding:18px 24px;display:flex;align-items:center;justify-content:space-between;">
                <div style="display:flex;align-items:center;gap:12px;">
                    <div id="mDispatchHeaderIcon" style="width:42px;height:42px;border-radius:50%;background:rgba(255,255,255,0.22);display:flex;align-items:center;justify-content:center;font-size:20px;color:#fff;">
                        <i class="fa fa-whatsapp"></i>
                    </div>
                    <div>
                        <h4 id="mDispatchTitle" style="font-weight:900;font-size:17.5px;margin:0;color:#ffffff;">
                            <?php echo __('Confirm WhatsApp Delivery'); ?>
                        </h4>
                        <span id="mDispatchSubtitle" style="font-size:12px;opacity:0.92;color:#f0fdf4;">
                            <?php echo __('LeadBot CRM REST API • Automated PDF Attachment'); ?>
                        </span>
                    </div>
                </div>
                <button type="button" class="close" onclick="closeModernDispatchModal()" style="color:#ffffff;opacity:0.9;font-size:26px;line-height:1;border:none;background:transparent;cursor:pointer;">&times;</button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body" style="padding:22px;background:#ffffff;">
                <!-- State 1: Confirmation Form -->
                <div id="mDispatchStateConfirm">
                    <div style="background:#f8fafc;border:1.5px solid #e2e8f0;border-radius:12px;padding:16px;margin-bottom:16px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;padding-bottom:10px;border-bottom:1px dashed #cbd5e1;">
                            <span style="font-size:12px;color:#64748b;font-weight:700;text-transform:uppercase;"><?php echo __('Resident / Client:'); ?></span>
                            <strong id="mDispatchClientName" style="color:#0f172a;font-size:14px;">-</strong>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                            <span style="font-size:12.5px;color:#64748b;font-weight:600;"><?php echo __('Mobile / WhatsApp:'); ?></span>
                            <span id="mDispatchPhone" style="font-family:monospace;font-size:13.5px;font-weight:800;color:#059669;background:#dcfce7;padding:3px 8px;border-radius:6px;">-</span>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                            <span style="font-size:12.5px;color:#64748b;font-weight:600;"><?php echo __('Bill # & Period:'); ?></span>
                            <strong id="mDispatchBillPeriod" style="color:#0284c7;font-size:13px;">-</strong>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:center;">
                            <span style="font-size:12.5px;color:#64748b;font-weight:600;"><?php echo __('Amount:'); ?></span>
                            <strong id="mDispatchAmount" style="color:#0f172a;font-size:16px;font-weight:900;">PKR 0.00</strong>
                        </div>
                    </div>

                    <!-- PDF Attachment Feature Banner -->
                    <div id="mDispatchPdfNotice" style="background:#f0fdf4;border:1.5px solid #86efac;border-radius:10px;padding:12px 14px;display:flex;align-items:center;gap:10px;margin-bottom:16px;">
                        <i class="fa fa-file-pdf-o" style="color:#dc2626;font-size:22px;"></i>
                        <div style="font-size:12px;color:#166534;line-height:1.4;">
                            <strong style="color:#14532d;"><?php echo __('Official PDF Attachment Included:'); ?></strong><br>
                            <?php echo __('A high-resolution PDF voucher will be generated and delivered as an attachment.'); ?>
                        </div>
                    </div>

                    <div style="font-size:11.5px;color:#64748b;text-align:center;">
                        <?php echo __('Click "Send Now" to dispatch immediately via backend gateway.'); ?>
                    </div>
                </div>

                <!-- State 2: Sending Progress -->
                <div id="mDispatchStateSending" style="display:none;padding:30px 10px;text-align:center;">
                    <div style="font-size:46px;color:#059669;margin-bottom:16px;">
                        <i class="fa fa-spinner fa-spin"></i>
                    </div>
                    <h4 id="mDispatchSendingTitle" style="font-weight:800;font-size:17px;color:#0f172a;margin-bottom:8px;">
                        <?php echo __('Connecting to LeadBot Gateway...'); ?>
                    </h4>
                    <p style="font-size:13px;color:#64748b;margin:0;">
                        <?php echo __('Generating official PDF voucher and dispatching message. Please wait...'); ?>
                    </p>
                </div>

                <!-- State 3: Success Result -->
                <div id="mDispatchStateSuccess" style="display:none;padding:24px 10px;text-align:center;">
                    <div style="width:60px;height:60px;border-radius:50%;background:#dcfce7;color:#16a34a;display:inline-flex;align-items:center;justify-content:center;font-size:32px;margin-bottom:14px;box-shadow:0 4px 14px rgba(22,163,74,0.25);">
                        ✓
                    </div>
                    <h4 style="font-weight:900;font-size:18px;color:#0f172a;margin-bottom:8px;">
                        <?php echo __('Dispatched Successfully!'); ?>
                    </h4>
                    <p id="mDispatchSuccessMsg" style="font-size:13px;color:#166534;margin:0 0 14px 0;line-height:1.5;">
                        -
                    </p>
                    <div id="mDispatchSuccessMeta" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:8px 12px;font-size:11.5px;color:#64748b;display:inline-block;">
                        <span id="mDispatchSuccessId">-</span>
                    </div>
                </div>

                <!-- State 4: Error Result -->
                <div id="mDispatchStateError" style="display:none;padding:24px 10px;text-align:center;">
                    <div style="width:60px;height:60px;border-radius:50%;background:#fee2e2;color:#dc2626;display:inline-flex;align-items:center;justify-content:center;font-size:30px;margin-bottom:14px;box-shadow:0 4px 14px rgba(220,38,38,0.2);">
                        <i class="fa fa-exclamation-triangle"></i>
                    </div>
                    <h4 style="font-weight:900;font-size:18px;color:#991b1b;margin-bottom:8px;">
                        <?php echo __('Delivery Notice'); ?>
                    </h4>
                    <p id="mDispatchErrorMsg" style="font-size:13px;color:#dc2626;margin:0 0 16px 0;line-height:1.5;">
                        -
                    </p>
                    <div id="mDispatchFallbackWrap" style="display:none;margin-top:10px;">
                        <a id="mDispatchFallbackLink" href="#" target="_blank" class="btn btn-warning" style="font-weight:800;font-size:13px;padding:8px 18px;">
                            <i class="fa fa-external-link"></i> <?php echo __('Open Desktop WhatsApp as Fallback'); ?>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer" style="background:#f8fafc;padding:14px 22px;display:flex;justify-content:flex-end;gap:10px;border-top:1px solid #e2e8f0;">
                <button type="button" class="btn btn-default" id="btnModernDispatchCancel" onclick="closeModernDispatchModal()" style="font-weight:700;padding:9px 18px;border-radius:8px;">
                    <i class="fa fa-times"></i> <?php echo __('Cancel / Close'); ?>
                </button>
                <button type="button" class="btn btn-success" id="btnModernDispatchExecute" onclick="executeModernDispatch()" style="font-weight:800;padding:9px 22px;border-radius:8px;background:#059669;border-color:#059669;box-shadow:0 3px 10px rgba(5,150,105,0.3);">
                    <i class="fa fa-paper-plane"></i> <span id="mDispatchExecuteLabel"><?php echo __('Send Now via LeadBot'); ?></span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL: BULK WHATSAPP DISPATCH PROGRESS WITH PDF ATTACHMENTS -->
<!-- ======================================================== -->
<div class="modal fade" id="modalBulkWhatsappProgress" tabindex="-1" role="dialog" style="background:rgba(15,23,42,0.7);z-index:999999;">
    <div class="modal-dialog" style="max-width:620px;margin-top:50px;">
        <div class="modal-content" style="border-radius:16px;box-shadow:0 25px 60px -15px rgba(0,0,0,0.5);border:none;overflow:hidden;background:#ffffff;">
            <!-- Header -->
            <div style="background:linear-gradient(135deg,#059669 0%,#10b981 100%);color:#ffffff;padding:18px 24px;display:flex;align-items:center;justify-content:space-between;">
                <div style="display:flex;align-items:center;gap:12px;">
                    <div style="width:42px;height:42px;border-radius:50%;background:rgba(255,255,255,0.22);display:flex;align-items:center;justify-content:center;font-size:20px;color:#fff;">
                        <i class="fa fa-whatsapp"></i>
                    </div>
                    <div>
                        <h4 style="font-weight:900;font-size:17.5px;margin:0;color:#ffffff;">
                            <?php echo __('Bulk WhatsApp Dispatch Engine'); ?>
                        </h4>
                        <span style="font-size:12px;opacity:0.92;color:#f0fdf4;">
                            <?php echo __('Automated PDF Attachments • LeadBot CRM REST API'); ?>
                        </span>
                    </div>
                </div>
                <button type="button" class="close" onclick="closeBulkWhatsappModal()" style="color:#ffffff;opacity:0.9;font-size:26px;line-height:1;border:none;background:transparent;cursor:pointer;">&times;</button>
            </div>

            <!-- Body -->
            <div class="modal-body" style="padding:22px;background:#ffffff;">
                <!-- Step 1: Pre-flight Confirmation -->
                <div id="bwaStateConfirm">
                    <div style="background:#f0fdf4;border:1.5px solid #86efac;border-radius:12px;padding:16px;margin-bottom:18px;text-align:center;">
                        <div style="font-size:13px;font-weight:800;color:#166534;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:6px;">
                            <i class="fa fa-check-square-o"></i> <?php echo __('Ready to Dispatch WhatsApp to Selected Bills'); ?>
                        </div>
                        <div style="font-size:26px;font-weight:900;color:#059669;margin-bottom:4px;">
                            <span id="bwaSelectedCount">0</span> <span style="font-size:16px;font-weight:700;color:#166534;"><?php echo __('Bills Selected'); ?></span>
                        </div>
                        <p style="font-size:12px;color:#475569;margin:0;">
                            <?php echo __('Each resident will receive their personalized message with the official PDF voucher attached.'); ?>
                        </p>
                    </div>

                    <!-- Routing Rules Explanation -->
                    <div style="background:#f8fafc;border:1.5px solid #e2e8f0;border-radius:12px;padding:16px;margin-bottom:18px;">
                        <div style="font-weight:800;font-size:12.5px;color:#0f172a;margin-bottom:10px;text-transform:uppercase;">
                            <i class="fa fa-cogs text-primary"></i> <?php echo __('Automated Intelligent Routing:'); ?>
                        </div>
                        <div style="display:flex;flex-direction:column;gap:10px;font-size:12px;color:#334155;">
                            <div style="display:flex;align-items:flex-start;gap:10px;">
                                <span style="color:#dc2626;font-size:15px;margin-top:1px;"><i class="fa fa-file-text"></i></span>
                                <div>
                                    <strong style="color:#0f172a;"><?php echo __('Unpaid Bills:'); ?></strong>
                                    <span><?php echo __('Dispatches refined due bill notice, consumed units, payable amount, due date & attaches official high-resolution Bill PDF.'); ?></span>
                                </div>
                            </div>
                            <div style="display:flex;align-items:flex-start;gap:10px;">
                                <span style="color:#16a34a;font-size:15px;margin-top:1px;"><i class="fa fa-check-circle"></i></span>
                                <div>
                                    <strong style="color:#0f172a;"><?php echo __('Paid Bills:'); ?></strong>
                                    <span><?php echo __('Dispatches official payment receipt, amount received, payment date, transaction ref & attaches official high-resolution Paid Slip PDF.'); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div style="font-size:11.5px;color:#64748b;text-align:center;">
                        <?php echo __('Click "Start Dispatch" to begin background delivery through LeadBot REST API.'); ?>
                    </div>
                </div>

                <!-- Step 2: Live Progress & Activity Log -->
                <div id="bwaStateProgress" style="display:none;">
                    <div style="margin-bottom:14px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;font-size:12.5px;">
                            <strong style="color:#0f172a;" id="bwaProgressStatus"><?php echo __('Starting dispatch...'); ?></strong>
                            <span style="font-weight:800;color:#059669;" id="bwaProgressPercent">0%</span>
                        </div>
                        <div style="background:#e2e8f0;border-radius:10px;height:12px;overflow:hidden;">
                            <div id="bwaProgressBar" style="width:0%;height:100%;background:linear-gradient(90deg,#059669,#10b981);transition:width 0.3s ease;"></div>
                        </div>
                    </div>

                    <!-- Scrollable Live Log Box -->
                    <div style="font-size:12px;font-weight:700;color:#64748b;margin-bottom:6px;text-transform:uppercase;">
                        <?php echo __('Live Delivery Log:'); ?>
                    </div>
                    <div id="bwaLogContainer" style="background:#0f172a;color:#f8fafc;border-radius:10px;padding:12px 14px;max-height:220px;min-height:160px;overflow-y:auto;font-family:monospace;font-size:11.5px;line-height:1.5;">
                        <!-- Log entries injected dynamically -->
                    </div>
                </div>

                <!-- Step 3: Complete Summary -->
                <div id="bwaStateComplete" style="display:none;text-align:center;padding:15px 10px;">
                    <div style="width:62px;height:62px;border-radius:50%;background:#dcfce7;color:#16a34a;display:inline-flex;align-items:center;justify-content:center;font-size:32px;margin-bottom:14px;box-shadow:0 4px 14px rgba(22,163,74,0.25);">
                        ✓
                    </div>
                    <h4 style="font-weight:900;font-size:18.5px;color:#0f172a;margin-bottom:8px;">
                        <?php echo __('Bulk Dispatch Completed!'); ?>
                    </h4>
                    <p style="font-size:13px;color:#475569;margin-bottom:16px;">
                        <?php echo __('WhatsApp messages with official PDF attachments have been processed via LeadBot CRM API.'); ?>
                    </p>

                    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:18px;">
                        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:12px;">
                            <span style="font-size:11px;color:#64748b;font-weight:700;text-transform:uppercase;"><?php echo __('Total Selected:'); ?></span>
                            <div style="font-size:20px;font-weight:900;color:#0f172a;" id="bwaStatTotal">0</div>
                        </div>
                        <div style="background:#f0fdf4;border:1px solid #86efac;border-radius:10px;padding:12px;">
                            <span style="font-size:11px;color:#166534;font-weight:700;text-transform:uppercase;"><?php echo __('Dispatched:'); ?></span>
                            <div style="font-size:20px;font-weight:900;color:#16a34a;" id="bwaStatSent">0</div>
                        </div>
                        <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:10px;padding:12px;">
                            <span style="font-size:11px;color:#991b1b;font-weight:700;text-transform:uppercase;"><?php echo __('Failed / Skipped:'); ?></span>
                            <div style="font-size:20px;font-weight:900;color:#dc2626;" id="bwaStatFailed">0</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="modal-footer" style="background:#f8fafc;padding:14px 22px;display:flex;justify-content:flex-end;gap:10px;border-top:1px solid #e2e8f0;">
                <button type="button" class="btn btn-default" id="btnBwaCancel" onclick="closeBulkWhatsappModal()" style="font-weight:700;padding:9px 18px;border-radius:8px;">
                    <i class="fa fa-times"></i> <?php echo __('Cancel / Close'); ?>
                </button>
                <button type="button" class="btn btn-success" id="btnBwaStart" onclick="startBulkWhatsappExecution()" style="font-weight:800;padding:9px 24px;border-radius:8px;background:#059669;border-color:#059669;box-shadow:0 3px 10px rgba(5,150,105,0.3);">
                    <i class="fa fa-paper-plane"></i> <?php echo __('Start Bulk Dispatch Now'); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL 5: EDIT BILL (WordPress Plugin UI Exact)           -->
<!-- ======================================================== -->
<div class="modal fade" id="modalEditBill" tabindex="-1" role="dialog">
    <div class="modal-dialog" style="max-width:680px;">
        <div class="modal-content" style="border-radius:8px;box-shadow:0 20px 40px rgba(0,0,0,0.2);border:none;">
            <?php echo $this->Form->create('BillingBill', array('action' => 'index', 'id' => 'editBillForm')); ?>
            <input type="hidden" name="subm_action" value="update_bill" />
            <input type="hidden" name="data[BillingBill][edit_bill_id]" id="editBillId" value="" />
            <input type="hidden" name="data[BillingBill][arrears_amount]" id="editArrearsInput" value="0.00" />
            <input type="hidden" name="data[BillingBill][late_fee]" id="editLateFeeInput" value="0.00" />
            <input type="hidden" name="data[BillingBill][adjustment_plus]" id="editAdjPlusInput" value="0.00" />
            <input type="hidden" name="data[BillingBill][adjustment_minus]" id="editAdjMinusInput" value="0.00" />

            <div class="modal-header" style="background:#ffffff;border-bottom:1px solid #f1f5f9;padding:16px 20px;border-radius:8px 8px 0 0;display:flex;align-items:center;justify-content:space-between;">
                <h4 class="modal-title" style="font-weight:700;font-size:17px;color:#1e293b;margin:0;">
                    <?php echo __('Edit Bill'); ?>
                </h4>
                <button type="button" class="close" data-dismiss="modal" style="font-size:22px;color:#64748b;opacity:0.8;border:none;background:transparent;">&times;</button>
            </div>

            <div class="modal-body" style="padding:20px 22px;max-height:calc(85vh - 110px);overflow-y:auto;background:#ffffff;">
                <!-- Bill No -->
                <div class="form-group" style="margin-bottom:14px;">
                    <label style="font-weight:600;font-size:13px;color:#334155;margin-bottom:4px;display:block;">
                        <?php echo __('Bill No'); ?>
                    </label>
                    <input type="text" name="data[BillingBill][bill_number]" id="editBillNo" class="form-control" style="border-radius:6px;border:1px solid #cbd5e1;height:40px;font-weight:600;font-size:13px;background:#ffffff;" />
                </div>

                <!-- Due Date -->
                <div class="form-group" style="margin-bottom:14px;">
                    <label style="font-weight:600;font-size:13px;color:#334155;margin-bottom:4px;display:block;">
                        <?php echo __('Due Date'); ?>
                    </label>
                    <input type="date" name="data[BillingBill][due_date]" id="editDueDate" class="form-control" style="border-radius:6px;border:1px solid #cbd5e1;height:40px;font-weight:600;font-size:13px;" required />
                </div>

                <!-- Template (For Print) -->
                <div class="form-group" style="margin-bottom:16px;">
                    <label style="font-weight:600;font-size:13px;color:#334155;margin-bottom:4px;display:block;">
                        <?php echo __('Template (For Print)'); ?>
                    </label>
                    <select name="data[BillingBill][template_id]" id="editTemplateId" class="form-control" style="border-radius:6px;border:1px solid #cbd5e1;height:40px;font-weight:600;font-size:13px;">
                        <?php foreach($templates as $tId => $tName){ ?>
                            <option value="<?php echo $tId; ?>"><?php echo h($tName); ?></option>
                        <?php } ?>
                    </select>
                </div>

                <!-- Section 1: Utility Charges (Edit Unit Rate) -->
                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:14px 16px;margin-bottom:16px;">
                    <div style="font-weight:700;font-size:13px;color:#1e293b;margin-bottom:10px;">
                        <?php echo __('Utility Charges (Edit Unit Rate)'); ?>
                    </div>
                    
                    <div style="background:#ffffff;border:1.5px dashed #cbd5e1;border-radius:6px;padding:12px 14px;">
                        <div class="form-group" style="margin-bottom:10px;">
                            <label style="font-size:11px;font-weight:600;color:#64748b;margin-bottom:3px;display:block;">
                                <?php echo __('Description'); ?>
                            </label>
                            <input type="text" name="data[BillingBill][utility_desc]" id="editUtilityDesc" class="form-control" value="Electricity Charges" style="height:36px;border-radius:5px;border:1px solid #cbd5e1;font-size:12.5px;" />
                        </div>

                        <div class="form-group" style="margin-bottom:10px;">
                            <label style="font-size:11px;font-weight:600;color:#64748b;margin-bottom:3px;display:block;">
                                <?php echo __('Units'); ?>
                            </label>
                            <input type="number" step="0.01" name="data[BillingBill][utility_units]" id="editUtilityUnits" class="form-control" value="0.00" style="height:36px;border-radius:5px;border:1px solid #cbd5e1;font-size:12.5px;background:#f8fafc;" oninput="recalcEditBillTotal()" />
                        </div>

                        <div class="form-group" style="margin-bottom:10px;">
                            <label style="font-size:11px;font-weight:600;color:#64748b;margin-bottom:3px;display:block;">
                                <?php echo __('Unit Rate'); ?>
                            </label>
                            <input type="number" step="0.01" name="data[BillingBill][utility_rate]" id="editUtilityRate" class="form-control" value="58.34" style="height:36px;border-radius:5px;border:1px solid #cbd5e1;font-size:12.5px;" oninput="recalcEditBillTotal()" />
                        </div>

                        <div class="form-group" style="margin-bottom:0;">
                            <label style="font-size:11px;font-weight:600;color:#64748b;margin-bottom:3px;display:block;">
                                <?php echo __('Amount'); ?>
                            </label>
                            <input type="number" step="0.01" name="data[BillingBill][utility_amount]" id="editUtilityAmount" class="form-control" value="0.00" style="height:36px;border-radius:5px;border:1px solid #cbd5e1;font-size:12.5px;background:#f8fafc;" oninput="recalcEditBillTotal(true)" />
                        </div>
                    </div>
                </div>

                <!-- Section 2: Maintenance Breakdown -->
                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:14px 16px;margin-bottom:16px;">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
                        <div style="font-weight:700;font-size:13px;color:#1e293b;">
                            <?php echo __('Maintenance Breakdown'); ?>
                        </div>
                        <button type="button" class="btn btn-xs" style="background:#0284c7;color:#fff;font-weight:700;border-radius:4px;padding:3px 8px;font-size:11px;" onclick="addEditMaintRow('', 0)">
                            <i class="fa fa-plus"></i> <?php echo __('Add Item'); ?>
                        </button>
                    </div>

                    <div id="editMaintContainer" style="display:flex;flex-direction:column;gap:10px;">
                        <!-- Dynamic items rendered here -->
                    </div>
                </div>

                <!-- Section 3: Other Charges -->
                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:14px 16px;margin-bottom:16px;">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
                        <div style="font-weight:700;font-size:13px;color:#1e293b;">
                            <?php echo __('Other Charges'); ?>
                        </div>
                        <button type="button" class="btn btn-xs" style="background:#0284c7;color:#fff;font-weight:700;border-radius:4px;padding:3px 8px;font-size:11px;" onclick="addEditOtherRow('', 0)">
                            <i class="fa fa-plus"></i> <?php echo __('Add Item'); ?>
                        </button>
                    </div>

                    <div id="editOtherContainer" style="display:flex;flex-direction:column;gap:10px;">
                        <!-- Dynamic items or empty state rendered here -->
                    </div>
                </div>

                <!-- Section 4: KPI Breakdown Tiles Grid -->
                <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;padding:12px;margin-bottom:6px;">
                    <!-- Row 1: 5 tiles -->
                    <div style="display:grid;grid-template-columns:repeat(5, 1fr);gap:6px;margin-bottom:8px;">
                        <div style="background:#ffffff;border:1px solid #cbd5e1;border-radius:6px;padding:6px 8px;">
                            <div style="font-size:10px;font-weight:600;color:#64748b;"><?php echo __('Utility'); ?></div>
                            <div id="editTileUtility" style="font-size:13px;font-weight:700;color:#0f172a;margin-top:2px;">0.00</div>
                        </div>
                        <div style="background:#ffffff;border:1px solid #cbd5e1;border-radius:6px;padding:6px 8px;">
                            <div style="font-size:10px;font-weight:600;color:#64748b;"><?php echo __('Maintenance'); ?></div>
                            <div id="editTileMaintenance" style="font-size:13px;font-weight:700;color:#0f172a;margin-top:2px;">0.00</div>
                        </div>
                        <div style="background:#ffffff;border:1px solid #cbd5e1;border-radius:6px;padding:6px 8px;">
                            <div style="font-size:10px;font-weight:600;color:#64748b;"><?php echo __('Other Charges'); ?></div>
                            <div id="editTileOther" style="font-size:13px;font-weight:700;color:#0f172a;margin-top:2px;">0.00</div>
                        </div>
                        <div style="background:#ffffff;border:1px solid #cbd5e1;border-radius:6px;padding:6px 8px;">
                            <div style="font-size:10px;font-weight:600;color:#64748b;"><?php echo __('Arrears'); ?></div>
                            <div id="editTileArrears" style="font-size:13px;font-weight:700;color:#0f172a;margin-top:2px;">0.00</div>
                        </div>
                        <div style="background:#ffffff;border:1px solid #cbd5e1;border-radius:6px;padding:6px 8px;">
                            <div style="font-size:10px;font-weight:600;color:#64748b;"><?php echo __('Late Fee'); ?></div>
                            <div id="editTileLateFee" style="font-size:13px;font-weight:700;color:#0f172a;margin-top:2px;">0.00</div>
                        </div>
                    </div>

                    <!-- Row 2: 3 tiles -->
                    <div style="display:grid;grid-template-columns:repeat(3, 1fr);gap:6px;">
                        <div style="background:#ffffff;border:1px solid #cbd5e1;border-radius:6px;padding:6px 8px;">
                            <div style="font-size:10px;font-weight:600;color:#64748b;"><?php echo __('Adjustment (+/-)'); ?></div>
                            <div id="editTileAdjustment" style="font-size:13px;font-weight:700;color:#0f172a;margin-top:2px;">0.00 / 0.00</div>
                        </div>
                        <div style="background:#ffffff;border:1px solid #cbd5e1;border-radius:6px;padding:6px 8px;">
                            <div style="font-size:10px;font-weight:600;color:#64748b;"><?php echo __('Current Bill'); ?></div>
                            <div id="editTileCurrentBill" style="font-size:13.5px;font-weight:700;color:#0f172a;margin-top:2px;">0.00</div>
                        </div>
                        <div style="background:#ffffff;border:1px solid #cbd5e1;border-radius:6px;padding:6px 8px;">
                            <div style="font-size:10px;font-weight:600;color:#64748b;"><?php echo __('Total Payable'); ?></div>
                            <div id="editTileTotalPayable" style="font-size:14px;font-weight:900;color:#0f172a;margin-top:2px;">0.00</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer" style="background:#ffffff;border-top:1px solid #f1f5f9;padding:12px 22px;display:flex;align-items:center;justify-content:flex-start;">
                <button type="submit" class="btn" style="background:#0284c7;color:#ffffff;font-weight:700;padding:8px 30px;border-radius:6px;font-size:13.5px;border:none;box-shadow:none;transition:all 0.2s ease;">
                    <?php echo __('Update Bill'); ?>
                </button>
            </div>
            <?php echo $this->Form->end(); ?>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL 6: ADJUST BILL (CREDIT / DEBIT AUDIT LEDGER)       -->
<!-- ======================================================== -->
<div class="modal fade" id="modalAdjustBill" tabindex="-1" role="dialog">
    <div class="modal-dialog" style="max-width:620px;">
        <div class="modal-content" style="border-radius:8px;box-shadow:0 20px 40px rgba(0,0,0,0.2);border:none;">
            <?php echo $this->Form->create('BillingBillAdjustment', array('action' => 'index', 'id' => 'adjustBillForm')); ?>
            <input type="hidden" name="subm_action" value="add_adjustment" />
            <input type="hidden" name="data[BillingBillAdjustment][bill_id]" id="adjBillId" value="" />
            <input type="hidden" name="data[BillingBillAdjustment][type]" id="adjTypeInput" value="credit" />

            <div class="modal-header" style="background:#ffffff;border-bottom:1px solid #f1f5f9;padding:16px 20px;border-radius:8px 8px 0 0;display:flex;align-items:center;justify-content:space-between;">
                <h4 class="modal-title" style="font-weight:700;font-size:17px;color:#1e293b;margin:0;">
                    <i class="fa fa-sliders text-primary"></i> <?php echo __('Bill Adjustment (Debit / Credit)'); ?>
                </h4>
                <button type="button" class="close" data-dismiss="modal" style="font-size:22px;color:#64748b;opacity:0.8;border:none;background:transparent;">&times;</button>
            </div>

            <div class="modal-body" style="padding:20px 22px;max-height:calc(85vh - 110px);overflow-y:auto;background:#ffffff;">
                <!-- Bill Information Summary Banner -->
                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:12px 14px;margin-bottom:16px;">
                    <div style="display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid #e2e8f0;padding-bottom:6px;margin-bottom:8px;">
                        <span style="font-size:12px;font-weight:700;color:#64748b;">BILL NUMBER: <strong id="adjBillNo" style="color:#0f172a;font-size:13px;">-</strong></span>
                        <span id="adjBillStatusBadge" style="font-size:10px;font-weight:800;padding:2px 8px;border-radius:12px;text-transform:uppercase;background:#e2e8f0;color:#475569;">UNPAID</span>
                    </div>
                    <div style="display:grid;grid-template-columns:repeat(3, 1fr);gap:10px;font-size:11px;">
                        <div>
                            <span style="color:#64748b;display:block;">Consumer</span>
                            <strong id="adjConsumerName" style="color:#1e293b;font-size:12px;">-</strong>
                        </div>
                        <div>
                            <span style="color:#64748b;display:block;">Current Total</span>
                            <strong id="adjCurrentTotal" style="color:#1e293b;font-size:12.5px;">PKR 0.00</strong>
                        </div>
                        <div>
                            <span style="color:#64748b;display:block;">Current Balance</span>
                            <strong id="adjCurrentBalance" style="color:#dc2626;font-size:12.5px;">PKR 0.00</strong>
                        </div>
                    </div>
                </div>

                <!-- Adjustment Type Selector (Credit vs Debit) -->
                <div class="form-group" style="margin-bottom:14px;">
                    <label style="font-weight:700;font-size:12.5px;color:#334155;margin-bottom:6px;display:block;">
                        <?php echo __('Adjustment Action Type'); ?> <span class="text-danger">*</span>
                    </label>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                        <button type="button" id="btnAdjCredit" class="btn" onclick="selectAdjType('credit')" style="background:#ecfdf5;border:2px solid #10b981;color:#047857;font-weight:800;padding:10px;border-radius:6px;text-align:left;transition:all 0.2s;">
                            <div style="font-size:13px;"><i class="fa fa-minus-circle"></i> Credit Note (Decrease Bill)</div>
                            <div style="font-size:10px;font-weight:500;color:#059669;margin-top:2px;">Waiver, discount, reading correction</div>
                        </button>
                        <button type="button" id="btnAdjDebit" class="btn" onclick="selectAdjType('debit')" style="background:#f8fafc;border:2px solid #cbd5e1;color:#64748b;font-weight:800;padding:10px;border-radius:6px;text-align:left;transition:all 0.2s;">
                            <div style="font-size:13px;"><i class="fa fa-plus-circle"></i> Debit Note (Increase Bill)</div>
                            <div style="font-size:10px;font-weight:500;color:#64748b;margin-top:2px;">Surcharge, penalty, damage fine</div>
                        </button>
                    </div>
                </div>

                <!-- Adjustment Amount -->
                <div class="form-group" style="margin-bottom:14px;">
                    <label style="font-weight:700;font-size:12.5px;color:#334155;margin-bottom:4px;display:block;">
                        <?php echo __('Adjustment Amount (PKR)'); ?> <span class="text-danger">*</span>
                    </label>
                    <input type="number" step="0.01" min="0.01" name="data[BillingBillAdjustment][amount]" id="adjAmountInput" class="form-control" placeholder="e.g. 500.00" style="height:42px;font-size:14px;font-weight:700;border-radius:6px;border:1px solid #cbd5e1;" required oninput="recalcAdjustModalPreview()" />
                </div>

                <!-- Reason / Description with Preset Chips -->
                <div class="form-group" style="margin-bottom:14px;">
                    <label style="font-weight:700;font-size:12.5px;color:#334155;margin-bottom:4px;display:block;">
                        <?php echo __('Reason / Audit Note'); ?> <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="data[BillingBillAdjustment][reason]" id="adjReasonInput" class="form-control" placeholder="e.g. Meter reading correction approved by Admin" style="height:38px;font-size:12.5px;border-radius:6px;border:1px solid #cbd5e1;margin-bottom:6px;" required />
                    
                    <!-- Quick Presets -->
                    <div style="display:flex;flex-wrap:wrap;gap:4px;">
                        <span class="badge" style="background:#f1f5f9;color:#475569;cursor:pointer;padding:4px 8px;font-weight:600;border:1px solid #e2e8f0;border-radius:4px;" onclick="setAdjReason('Meter reading correction waiver')">Meter Correction</span>
                        <span class="badge" style="background:#f1f5f9;color:#475569;cursor:pointer;padding:4px 8px;font-weight:600;border:1px solid #e2e8f0;border-radius:4px;" onclick="setAdjReason('Late payment fee waiver')">Late Fee Waiver</span>
                        <span class="badge" style="background:#f1f5f9;color:#475569;cursor:pointer;padding:4px 8px;font-weight:600;border:1px solid #e2e8f0;border-radius:4px;" onclick="setAdjReason('Management approved discount')">Management Relief</span>
                        <span class="badge" style="background:#f1f5f9;color:#475569;cursor:pointer;padding:4px 8px;font-weight:600;border:1px solid #e2e8f0;border-radius:4px;" onclick="setAdjReason('Special maintenance / repair surcharge')">Repair Surcharge</span>
                        <span class="badge" style="background:#f1f5f9;color:#475569;cursor:pointer;padding:4px 8px;font-weight:600;border:1px solid #e2e8f0;border-radius:4px;" onclick="setAdjReason('Meter tampering / damage penalty')">Damage Penalty</span>
                    </div>
                </div>

                <!-- Approved By -->
                <div class="form-group" style="margin-bottom:16px;">
                    <label style="font-weight:600;font-size:12px;color:#64748b;margin-bottom:3px;display:block;">
                        <?php echo __('Approved By / Officer'); ?>
                    </label>
                    <input type="text" name="data[BillingBillAdjustment][approved_by]" id="adjApprovedByInput" value="Admin" class="form-control" style="height:36px;font-size:12px;border-radius:6px;border:1px solid #cbd5e1;" />
                </div>

                <!-- Live Before / After Calculation Preview Box -->
                <div style="background:#f0fdf4;border:1.5px solid #86efac;border-radius:8px;padding:12px 14px;margin-bottom:16px;">
                    <div style="font-size:11px;font-weight:800;color:#166534;text-transform:uppercase;margin-bottom:6px;">
                        <i class="fa fa-calculator"></i> Impact on Total Bill
                    </div>
                    <div style="display:flex;align-items:center;justify-content:space-between;font-size:12px;margin-bottom:3px;">
                        <span style="color:#475569;">Current Bill Total:</span>
                        <strong id="adjPreviewBase" style="color:#0f172a;">PKR 0.00</strong>
                    </div>
                    <div style="display:flex;align-items:center;justify-content:space-between;font-size:12px;margin-bottom:3px;">
                        <span style="color:#475569;">New Adjustment:</span>
                        <strong id="adjPreviewDelta" style="color:#15803d;">- PKR 0.00 (Credit)</strong>
                    </div>
                    <div style="display:flex;align-items:center;justify-content:space-between;font-size:13.5px;border-top:1px dashed #86efac;padding-top:6px;margin-top:4px;">
                        <strong style="color:#166534;">Projected Total Payable:</strong>
                        <strong id="adjPreviewFinal" style="color:#0f172a;font-size:15px;font-weight:900;">PKR 0.00</strong>
                    </div>
                </div>

                <!-- Past Recorded Adjustments Audit Log for this Bill -->
                <div>
                    <div style="font-weight:700;font-size:12.5px;color:#1e293b;margin-bottom:6px;">
                        <i class="fa fa-history"></i> <?php echo __('Recorded Adjustments on this Bill'); ?>
                    </div>
                    <div id="adjHistoryContainer" style="background:#ffffff;border:1px solid #e2e8f0;border-radius:6px;overflow:hidden;">
                        <!-- Rendered by JS -->
                    </div>
                </div>
            </div>

            <div class="modal-footer" style="background:#ffffff;border-top:1px solid #f1f5f9;padding:12px 22px;display:flex;align-items:center;justify-content:space-between;">
                <button type="button" class="btn btn-default" data-dismiss="modal" style="font-weight:600;font-size:13px;border-radius:6px;"><?php echo __('Cancel'); ?></button>
                <button type="submit" class="btn btn-primary" style="background:#0284c7;font-weight:800;padding:8px 24px;border-radius:6px;font-size:13.5px;border:none;">
                    <i class="fa fa-check"></i> <?php echo __('Save Adjustment & Update Bill'); ?>
                </button>
            </div>
            <?php echo $this->Form->end(); ?>
        </div>
    </div>
</div>

<!-- Modal 7: Bulk Bills Merged PDF Downloader & Batch Print Center -->
<div class="modal fade" id="modalBulkPdfDownload" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" style="max-width:620px;margin:30px auto;">
        <div class="modal-content" style="border-radius:12px;box-shadow:0 20px 40px rgba(0,0,0,0.25);border:none;overflow:hidden;">
            <div class="modal-header" style="background:linear-gradient(135deg,#0284c7,#0369a1);color:#fff;padding:16px 22px;border:none;">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true" style="color:#fff;opacity:0.9;font-size:24px;">&times;</button>
                <h4 class="modal-title" style="font-weight:900;letter-spacing:0.4px;font-size:16px;display:flex;align-items:center;gap:8px;">
                    <i class="fa fa-file-pdf-o text-red-300"></i> <?php echo __('Bulk Bills Merged PDF Downloader'); ?>
                </h4>
                <div style="font-size:12px;opacity:0.85;margin-top:2px;">Compile and export all invoices for a month or custom filter into a single merged multi-page PDF document.</div>
            </div>

            <div class="modal-body" style="padding:22px;background:#ffffff;">
                <div class="row">
                    <!-- Billing Month -->
                    <div class="col-md-6 form-group">
                        <label style="font-weight:700;font-size:12.5px;color:#334155;"><?php echo __('Billing Month'); ?> <span class="text-danger">*</span></label>
                        <input type="month" id="bulkPdfMonth" class="form-control" value="<?php echo !empty($this->request->query['filter_month']) ? h($this->request->query['filter_month']) : date('Y-m'); ?>" style="height:38px;font-weight:700;border-radius:6px;" />
                    </div>

                    <!-- Society / Phase -->
                    <div class="col-md-6 form-group">
                        <label style="font-weight:700;font-size:12.5px;color:#334155;"><?php echo __('Society / Sector'); ?></label>
                        <select id="bulkPdfSociety" class="form-control" style="height:38px;border-radius:6px;">
                            <option value=""><?php echo __('All Societies / Sectors'); ?></option>
                            <?php if(!empty($societies)){ foreach($societies as $socId => $socName){ ?>
                                <option value="<?php echo $socId; ?>"><?php echo h($socName); ?></option>
                            <?php }} ?>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <!-- Utility Type -->
                    <div class="col-md-6 form-group">
                        <label style="font-weight:700;font-size:12.5px;color:#334155;"><?php echo __('Utility Type'); ?></label>
                        <select id="bulkPdfUtility" class="form-control" style="height:38px;border-radius:6px;">
                            <option value="all"><?php echo __('All Utilities (Electricity, Water, Gas)'); ?></option>
                            <option value="Electricity"><?php echo __('Electricity Only'); ?></option>
                            <option value="Water"><?php echo __('Water Only'); ?></option>
                            <option value="Gas"><?php echo __('Gas Only'); ?></option>
                        </select>
                    </div>

                    <!-- Payment Status -->
                    <div class="col-md-6 form-group">
                        <label style="font-weight:700;font-size:12.5px;color:#334155;"><?php echo __('Payment Status'); ?></label>
                        <select id="bulkPdfStatus" class="form-control" style="height:38px;border-radius:6px;">
                            <option value="all"><?php echo __('All Statuses (Unpaid, Paid, Partial)'); ?></option>
                            <option value="unpaid"><?php echo __('Unpaid Bills Only'); ?></option>
                            <option value="paid"><?php echo __('Paid Bills Only'); ?></option>
                            <option value="partial"><?php echo __('Partial Bills Only'); ?></option>
                        </select>
                    </div>
                </div>

                <!-- Live Status & Document Info Box -->
                <div style="background:#f8fafc;border:1.5px solid #e2e8f0;border-radius:8px;padding:14px;margin-top:10px;">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                        <span style="font-size:12px;font-weight:700;color:#475569;"><i class="fa fa-info-circle text-info"></i> PDF Export Format:</span>
                        <span class="badge" style="background:#dcfce7;color:#166534;font-weight:800;font-size:11.5px;padding:4px 10px;border-radius:4px;border:1px solid #bbf7d0;">A4 Portrait &bull; High Resolution (Vector)</span>
                    </div>
                    <div style="font-size:11.5px;color:#64748b;line-height:1.5;">
                        • Each bill invoice is isolated to exactly 1 A4 page with crisp QR codes and official payment slips.<br />
                        • You can directly download the compiled PDF file or open the multi-page print preview to print on your office printer.
                    </div>
                </div>
            </div>

            <div class="modal-footer" style="background:#ffffff;border-top:1px solid #f1f5f9;padding:14px 22px;display:flex;align-items:center;justify-content:space-between;">
                <button type="button" class="btn btn-default" data-dismiss="modal" style="font-weight:600;font-size:13px;border-radius:6px;"><?php echo __('Cancel'); ?></button>
                <div style="display:flex;gap:8px;">
                    <button type="button" class="btn" onclick="submitBulkPdfModal(false)" style="background:#0284c7;color:#fff;font-weight:800;padding:9px 18px;border-radius:6px;font-size:13px;border:none;">
                        <i class="fa fa-print"></i> <?php echo __('Open Merged Print View'); ?>
                    </button>
                    <button type="button" class="btn" onclick="submitBulkPdfModal(true)" style="background:#16a34a;color:#fff;font-weight:800;padding:9px 20px;border-radius:6px;font-size:13px;border:none;box-shadow:0 2px 8px rgba(22,163,74,0.3);">
                        <i class="fa fa-download"></i> <?php echo __('Download Merged PDF'); ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
function downloadSelectedBillsMergedPdf(isDownload) {
    var selectedIds = [];
    document.querySelectorAll('.row-checkbox:checked').forEach(function(c) {
        if (c.value) selectedIds.push(c.value);
    });

    if (selectedIds.length === 0) {
        alert('Please select at least one bill checkbox from the table.');
        return;
    }

    var baseUrl = '<?php echo $this->Html->url(array("plugin" => "billing_manager", "controller" => "billing_bills", "action" => "bulk_print")); ?>';
    var url = baseUrl + '?bill_ids=' + selectedIds.join(',');
    if (isDownload) {
        url += '&download_pdf=1';
    }
    window.open(url, '_blank');
}

function submitBulkPdfModal(isDownload) {
    var month = document.getElementById('bulkPdfMonth') ? document.getElementById('bulkPdfMonth').value : '';
    var societyId = document.getElementById('bulkPdfSociety') ? document.getElementById('bulkPdfSociety').value : '';
    var utilityType = document.getElementById('bulkPdfUtility') ? document.getElementById('bulkPdfUtility').value : 'all';
    var status = document.getElementById('bulkPdfStatus') ? document.getElementById('bulkPdfStatus').value : 'all';

    var baseUrl = '<?php echo $this->Html->url(array("plugin" => "billing_manager", "controller" => "billing_bills", "action" => "bulk_print")); ?>';
    var params = [];
    if (month) params.push('month=' + encodeURIComponent(month));
    if (societyId) params.push('society_id=' + encodeURIComponent(societyId));
    if (utilityType && utilityType !== 'all') params.push('utility_type=' + encodeURIComponent(utilityType));
    if (status && status !== 'all') params.push('status=' + encodeURIComponent(status));
    if (isDownload) params.push('download_pdf=1');

    var finalUrl = baseUrl + (params.length > 0 ? ('?' + params.join('&')) : '');
    $('#modalBulkPdfDownload').modal('hide');
    window.open(finalUrl, '_blank');
}

document.addEventListener('DOMContentLoaded', function() {
    var checkAll = document.getElementById('checkAll');
    var rowChecks = document.querySelectorAll('.row-checkbox');
    var countEl = document.getElementById('selectedCount');

    function updateCount() {
        var count = 0;
        rowChecks.forEach(function(c) { if(c.checked) count++; });
        if(countEl) countEl.innerText = count;
    }

    if(checkAll) {
        checkAll.addEventListener('change', function() {
            var checked = this.checked;
            rowChecks.forEach(function(c) { c.checked = checked; });
            updateCount();
        });
    }

    rowChecks.forEach(function(c) {
        c.addEventListener('change', updateCount);
    });
});

function submitBulkAction(action) {
    var count = 0;
    document.querySelectorAll('.row-checkbox').forEach(function(c) { if(c.checked) count++; });
    if(count === 0) {
        showModernResultModal(false, '<?php echo __("Please select at least one bill checkbox."); ?>', null, null);
        return;
    }
    if(action === 'send_whatsapp') {
        openBulkWhatsappModal();
        return;
    }
    if(action === 'bulk_delete') {
        if(!confirm('Are you sure you want to delete ' + count + ' selected bills?')) return;
    }
    document.getElementById('bulkSubmAction').value = action;
    document.getElementById('bulkActionForm').submit();
}

var currentModalBill = null;
var currentEditBill = null;

function togglePayTypeFields() {
    var pType = document.getElementById('payTypeSelect').value;
    var pAmtGroup = document.getElementById('payAmountGroup');
    if (pType === 'full') {
        if (pAmtGroup) pAmtGroup.style.display = 'none';
    } else {
        if (pAmtGroup) pAmtGroup.style.display = 'block';
    }
    recalcModalNetPayable();
}

function recalcModalNetPayable() {
    if (!currentModalBill) return;
    var origBal = parseFloat(currentModalBill.balance_amount || 0);
    var lateFee = parseFloat(currentModalBill.late_fee || 0);
    var isWaived = document.getElementById('payWaiveFeeCheck').checked;
    
    var waivedAmt = 0;
    if (isWaived) {
        var customWaived = parseFloat(document.getElementById('payWaivedFeeAmt').value || 0);
        waivedAmt = customWaived > 0 ? customWaived : lateFee;
    }
    
    var netBal = Math.max(0, origBal - waivedAmt);
    document.getElementById('payBal').innerText = netBal.toFixed(2);
    
    var pType = document.getElementById('payTypeSelect').value;
    if (pType === 'full') {
        document.getElementById('payAmount').value = netBal.toFixed(2);
    }
}

function toggleWaiveFeeInput() {
    var isChecked = document.getElementById('payWaiveFeeCheck').checked;
    var group = document.getElementById('payWaivedGroup');
    if (group) {
        group.style.display = isChecked ? 'block' : 'none';
    }
    recalcModalNetPayable();
}

function openPayModal(bill) {
    currentModalBill = bill;
    document.getElementById('payBillId').value = bill.id;
    document.getElementById('payBillNo').innerText = bill.bill_number;
    document.getElementById('payTotal').innerText = parseFloat(bill.total_amount).toFixed(2);
    document.getElementById('payBal').innerText = parseFloat(bill.balance_amount).toFixed(2);
    if (document.getElementById('payLateFee')) {
        document.getElementById('payLateFee').innerText = parseFloat(bill.late_fee || 0).toFixed(2);
    }
    document.getElementById('payTypeSelect').value = 'full';
    document.getElementById('payAmount').value = parseFloat(bill.balance_amount).toFixed(2);
    
    var waiveCheck = document.getElementById('payWaiveFeeCheck');
    if (waiveCheck) {
        waiveCheck.checked = false;
        toggleWaiveFeeInput();
    }
    var waiveAmtInput = document.getElementById('payWaivedFeeAmt');
    if (waiveAmtInput) {
        waiveAmtInput.value = parseFloat(bill.late_fee || 0).toFixed(2);
    }
    
    togglePayTypeFields();
    $('#modalPayment').modal('show');
}

function renderEditMaintRows(items) {
    var container = document.getElementById('editMaintContainer');
    if (!container) return;
    container.innerHTML = '';
    
    if (!items || items.length === 0) {
        items = [
            { desc: 'Services Chargers', amount: 500.00 },
            { desc: 'Water Bill', amount: 1101.00 },
            { desc: 'Garbage Collection', amount: 350.00 }
        ];
    }

    items.forEach(function(item) {
        addEditMaintRow(item.desc, item.amount);
    });
}

function addEditMaintRow(desc, amount) {
    var container = document.getElementById('editMaintContainer');
    if (!container) return;
    
    var idx = container.querySelectorAll('.edit-maint-card').length;
    var card = document.createElement('div');
    card.className = 'edit-maint-card';
    card.style = 'background:#ffffff;border:1.5px dashed #cbd5e1;border-radius:6px;padding:10px 12px;position:relative;';
    
    card.innerHTML = 
        '<button type="button" onclick="this.closest(\'.edit-maint-card\').remove(); recalcEditBillTotal();" style="position:absolute;right:8px;top:6px;background:transparent;border:none;color:#94a3b8;font-size:16px;cursor:pointer;line-height:1;" title="Remove">&times;</button>' +
        '<div class="form-group" style="margin-bottom:8px;">' +
            '<label style="font-size:11px;font-weight:600;color:#64748b;margin-bottom:3px;display:block;">Description</label>' +
            '<input type="text" name="data[BillingBill][maint_items][' + idx + '][desc]" class="form-control edit-maint-desc" value="' + escapeHtml(desc) + '" style="height:34px;border-radius:5px;border:1px solid #cbd5e1;font-size:12px;" />' +
        '</div>' +
        '<div class="form-group" style="margin-bottom:0;">' +
            '<label style="font-size:11px;font-weight:600;color:#64748b;margin-bottom:3px;display:block;">Amount</label>' +
            '<input type="number" step="0.01" name="data[BillingBill][maint_items][' + idx + '][amount]" class="form-control edit-maint-amount" value="' + parseFloat(amount || 0).toFixed(2) + '" style="height:34px;border-radius:5px;border:1px solid #cbd5e1;font-size:12px;" oninput="recalcEditBillTotal()" />' +
        '</div>';
        
    container.appendChild(card);
    recalcEditBillTotal();
}

function renderEditOtherRows(items) {
    var container = document.getElementById('editOtherContainer');
    if (!container) return;
    container.innerHTML = '';
    
    if (!items || items.length === 0) {
        container.innerHTML = '<div id="editOtherEmptyMsg" style="background:#ffffff;border:1.5px dashed #cbd5e1;border-radius:6px;padding:12px 14px;color:#94a3b8;font-size:12px;font-style:italic;">No other-charge rows found in this bill.</div>';
        return;
    }

    items.forEach(function(item) {
        addEditOtherRow(item.desc, item.amount);
    });
}

function addEditOtherRow(desc, amount) {
    var container = document.getElementById('editOtherContainer');
    if (!container) return;
    
    var emptyMsg = document.getElementById('editOtherEmptyMsg');
    if (emptyMsg) emptyMsg.remove();
    
    var idx = container.querySelectorAll('.edit-other-card').length;
    var card = document.createElement('div');
    card.className = 'edit-other-card';
    card.style = 'background:#ffffff;border:1.5px dashed #cbd5e1;border-radius:6px;padding:10px 12px;position:relative;';
    
    card.innerHTML = 
        '<button type="button" onclick="this.closest(\'.edit-other-card\').remove(); checkOtherEmptyState(); recalcEditBillTotal();" style="position:absolute;right:8px;top:6px;background:transparent;border:none;color:#94a3b8;font-size:16px;cursor:pointer;line-height:1;" title="Remove">&times;</button>' +
        '<div class="form-group" style="margin-bottom:8px;">' +
            '<label style="font-size:11px;font-weight:600;color:#64748b;margin-bottom:3px;display:block;">Description</label>' +
            '<input type="text" name="data[BillingBill][other_items][' + idx + '][desc]" class="form-control edit-other-desc" value="' + escapeHtml(desc) + '" style="height:34px;border-radius:5px;border:1px solid #cbd5e1;font-size:12px;" />' +
        '</div>' +
        '<div class="form-group" style="margin-bottom:0;">' +
            '<label style="font-size:11px;font-weight:600;color:#64748b;margin-bottom:3px;display:block;">Amount</label>' +
            '<input type="number" step="0.01" name="data[BillingBill][other_items][' + idx + '][amount]" class="form-control edit-other-amount" value="' + parseFloat(amount || 0).toFixed(2) + '" style="height:34px;border-radius:5px;border:1px solid #cbd5e1;font-size:12px;" oninput="recalcEditBillTotal()" />' +
        '</div>';
        
    container.appendChild(card);
    recalcEditBillTotal();
}

function checkOtherEmptyState() {
    var container = document.getElementById('editOtherContainer');
    if (!container) return;
    if (container.querySelectorAll('.edit-other-card').length === 0) {
        container.innerHTML = '<div id="editOtherEmptyMsg" style="background:#ffffff;border:1.5px dashed #cbd5e1;border-radius:6px;padding:12px 14px;color:#94a3b8;font-size:12px;font-style:italic;">No other-charge rows found in this bill.</div>';
    }
}

function recalcEditBillTotal(manualAmt) {
    var uUnitsInp = document.getElementById('editUtilityUnits');
    var uRateInp = document.getElementById('editUtilityRate');
    var uAmtInp = document.getElementById('editUtilityAmount');
    if (!uUnitsInp || !uRateInp || !uAmtInp) return;

    var units = parseFloat(uUnitsInp.value || 0);
    var rate = parseFloat(uRateInp.value || 0);
    
    var utilityAmt = 0;
    if (manualAmt) {
        utilityAmt = parseFloat(uAmtInp.value || 0);
    } else {
        utilityAmt = units * rate;
        uAmtInp.value = utilityAmt.toFixed(2);
    }

    var maintAmt = 0;
    document.querySelectorAll('.edit-maint-amount').forEach(function(inp) {
        maintAmt += parseFloat(inp.value || 0);
    });

    var otherAmt = 0;
    document.querySelectorAll('.edit-other-amount').forEach(function(inp) {
        otherAmt += parseFloat(inp.value || 0);
    });

    var arrears = parseFloat(document.getElementById('editArrearsInput').value || 0);
    var currentBill = utilityAmt + maintAmt + otherAmt;
    
    var lateFee = Math.round(currentBill * 0.07 * 100) / 100;
    document.getElementById('editLateFeeInput').value = lateFee.toFixed(2);

    var adjPlus = parseFloat(document.getElementById('editAdjPlusInput').value || 0);
    var adjMinus = parseFloat(document.getElementById('editAdjMinusInput').value || 0);
    var totalPayable = currentBill + arrears + lateFee + adjPlus - adjMinus;

    // Update KPI tiles
    if (document.getElementById('editTileUtility')) document.getElementById('editTileUtility').innerText = utilityAmt.toFixed(2);
    if (document.getElementById('editTileMaintenance')) document.getElementById('editTileMaintenance').innerText = maintAmt.toFixed(2);
    if (document.getElementById('editTileOther')) document.getElementById('editTileOther').innerText = otherAmt.toFixed(2);
    if (document.getElementById('editTileArrears')) document.getElementById('editTileArrears').innerText = arrears.toFixed(2);
    if (document.getElementById('editTileLateFee')) document.getElementById('editTileLateFee').innerText = lateFee.toFixed(2);
    if (document.getElementById('editTileAdjustment')) document.getElementById('editTileAdjustment').innerText = adjPlus.toFixed(2) + ' / ' + adjMinus.toFixed(2);
    if (document.getElementById('editTileCurrentBill')) document.getElementById('editTileCurrentBill').innerText = currentBill.toFixed(2);
    if (document.getElementById('editTileTotalPayable')) document.getElementById('editTileTotalPayable').innerText = totalPayable.toFixed(2);
}

function openEditModal(bill) {
    if (!bill) return;
    currentEditBill = bill;
    
    document.getElementById('editBillId').value = bill.id;
    document.getElementById('editBillNo').value = bill.bill_number || '';
    document.getElementById('editDueDate').value = bill.due_date || '';
    if (document.getElementById('editTemplateId')) {
        document.getElementById('editTemplateId').value = bill.template_id || 11;
    }
    
    var uType = bill.utility_type || 'Electricity';
    var uDesc = uType + ' Charges';
    var units = parseFloat(bill.units_consumed || 0);
    var rate = parseFloat(bill.unit_rate || (uType.toLowerCase() === 'water' ? 100 : (uType.toLowerCase() === 'gas' ? 350 : 58.34)));
    var amt = parseFloat(bill.consumption_amount || (units * rate));

    var maintItems = [];
    var otherItems = [];

    if (bill.extra_charges_json) {
        try {
            var parsed = JSON.parse(bill.extra_charges_json);
            if (Array.isArray(parsed)) {
                parsed.forEach(function(item) {
                    var cat = (item.category || '').toLowerCase();
                    if (cat === 'utility') {
                        if (item.desc) uDesc = item.desc;
                        if (item.units !== undefined) units = parseFloat(item.units);
                        if (item.rate !== undefined) rate = parseFloat(item.rate);
                        if (item.amount !== undefined) amt = parseFloat(item.amount);
                    } else if (cat === 'maintenance') {
                        maintItems.push({ desc: item.desc || 'Maintenance', amount: parseFloat(item.amount || 0) });
                    } else if (cat === 'other' || cat === 'extra') {
                        otherItems.push({ desc: item.desc || 'Other Charge', amount: parseFloat(item.amount || 0) });
                    }
                });
            }
        } catch(e) {}
    }

    // Fallback if no maintenance items in JSON: use property data or default heads
    if (maintItems.length === 0) {
        var pId = bill.billing_property_id;
        if (window.WV_PROPERTIES && window.WV_PROPERTIES[pId] && window.WV_PROPERTIES[pId].maintenance_items && window.WV_PROPERTIES[pId].maintenance_items.length > 0) {
            window.WV_PROPERTIES[pId].maintenance_items.forEach(function(mi) {
                maintItems.push({ desc: mi.name, amount: parseFloat(mi.amount || 0) });
            });
        } else {
            maintItems = [
                { desc: 'Services Chargers', amount: 500.00 },
                { desc: 'Water Bill', amount: 1101.00 },
                { desc: 'Garbage Collection', amount: 350.00 }
            ];
        }
    }

    document.getElementById('editUtilityDesc').value = uDesc;
    document.getElementById('editUtilityUnits').value = units.toFixed(2);
    document.getElementById('editUtilityRate').value = rate.toFixed(2);
    document.getElementById('editUtilityAmount').value = amt.toFixed(2);

    renderEditMaintRows(maintItems);
    renderEditOtherRows(otherItems);

    var arrears = parseFloat(bill.arrears_amount || 0);
    var lateFee = parseFloat(bill.late_fee || (amt * 0.07));
    document.getElementById('editArrearsInput').value = arrears.toFixed(2);
    document.getElementById('editLateFeeInput').value = lateFee.toFixed(2);

    var adjPlus = 0;
    var adjMinus = 0;
    if (window.WV_ADJUSTMENTS && window.WV_ADJUSTMENTS[bill.id]) {
        window.WV_ADJUSTMENTS[bill.id].forEach(function(a) {
            var aType = (a.type || '').toLowerCase();
            var aAmt = parseFloat(a.amount || 0);
            if (aType === 'credit') {
                adjMinus += aAmt;
            } else {
                adjPlus += aAmt;
            }
        });
    }
    document.getElementById('editAdjPlusInput').value = adjPlus.toFixed(2);
    document.getElementById('editAdjMinusInput').value = adjMinus.toFixed(2);

    recalcEditBillTotal();
    $('#modalEditBill').modal('show');
}

var currentAdjustBill = null;
var currentAdjustType = 'credit';

function selectAdjType(type) {
    currentAdjustType = type;
    document.getElementById('adjTypeInput').value = type;
    var btnC = document.getElementById('btnAdjCredit');
    var btnD = document.getElementById('btnAdjDebit');
    
    if (type === 'credit') {
        btnC.style.background = '#ecfdf5';
        btnC.style.borderColor = '#10b981';
        btnC.style.color = '#047857';
        btnD.style.background = '#f8fafc';
        btnD.style.borderColor = '#cbd5e1';
        btnD.style.color = '#64748b';
    } else {
        btnD.style.background = '#fef2f2';
        btnD.style.borderColor = '#ef4444';
        btnD.style.color = '#b91c1c';
        btnC.style.background = '#f8fafc';
        btnC.style.borderColor = '#cbd5e1';
        btnC.style.color = '#64748b';
    }
    recalcAdjustModalPreview();
}

function setAdjReason(reason) {
    document.getElementById('adjReasonInput').value = reason;
}

function recalcAdjustModalPreview() {
    if (!currentAdjustBill) return;
    var baseTotal = parseFloat(currentAdjustBill.total_amount || 0);
    var amt = parseFloat(document.getElementById('adjAmountInput').value || 0);
    
    var deltaStr = '';
    var finalTotal = baseTotal;
    
    if (currentAdjustType === 'credit') {
        finalTotal = Math.max(0, baseTotal - amt);
        deltaStr = '- PKR ' + amt.toFixed(2) + ' (Credit)';
        document.getElementById('adjPreviewDelta').style.color = '#15803d';
    } else {
        finalTotal = baseTotal + amt;
        deltaStr = '+ PKR ' + amt.toFixed(2) + ' (Debit)';
        document.getElementById('adjPreviewDelta').style.color = '#dc2626';
    }

    document.getElementById('adjPreviewBase').innerText = 'PKR ' + baseTotal.toFixed(2);
    document.getElementById('adjPreviewDelta').innerText = deltaStr;
    document.getElementById('adjPreviewFinal').innerText = 'PKR ' + finalTotal.toFixed(2);
}

function renderAdjustHistoryTable(billId) {
    var container = document.getElementById('adjHistoryContainer');
    if (!container) return;
    
    var adjs = (window.WV_ADJUSTMENTS && window.WV_ADJUSTMENTS[billId]) ? window.WV_ADJUSTMENTS[billId] : [];
    
    if (!adjs || adjs.length === 0) {
        container.innerHTML = '<div style="padding:10px 14px;color:#94a3b8;font-size:12px;font-style:italic;">No adjustments recorded on this bill yet.</div>';
        return;
    }

    var html = '<table class="table table-condensed table-striped" style="margin:0;font-size:11px;">' +
        '<thead style="background:#f8fafc;"><tr><th>Type</th><th>Amount</th><th>Reason</th><th>By</th><th>Date</th><th style="text-align:right;">Action</th></tr></thead><tbody>';
    
    adjs.forEach(function(a) {
        var isCredit = (a.type.toLowerCase() === 'credit');
        var badgeBg = isCredit ? '#ecfdf5' : '#fef2f2';
        var badgeColor = isCredit ? '#047857' : '#b91c1c';
        var sign = isCredit ? '-' : '+';
        var typeLabel = isCredit ? 'CREDIT' : 'DEBIT';

        html += '<tr>' +
            '<td><span style="background:' + badgeBg + ';color:' + badgeColor + ';font-weight:800;padding:2px 6px;border-radius:4px;font-size:10px;">' + typeLabel + '</span></td>' +
            '<td style="font-weight:700;color:' + badgeColor + ';">' + sign + ' ' + parseFloat(a.amount).toFixed(2) + '</td>' +
            '<td>' + escapeHtml(a.reason) + '</td>' +
            '<td>' + escapeHtml(a.approved_by || 'Admin') + '</td>' +
            '<td style="color:#64748b;">' + escapeHtml(a.created_at || '') + '</td>' +
            '<td style="text-align:right;"><form method="post" action="' + window.location.pathname + '" style="display:inline;" onsubmit="return confirm(\'Delete this adjustment?\');"><input type="hidden" name="subm_action" value="delete_adjustment" /><input type="hidden" name="data[BillingBillAdjustment][adj_id]" value="' + a.id + '" /><button type="submit" class="btn btn-xs btn-danger" style="padding:1px 5px;font-size:10px;"><i class="fa fa-trash"></i></button></form></td>' +
        '</tr>';
    });

    html += '</tbody></table>';
    container.innerHTML = html;
}

function openAdjustModal(bill) {
    if (!bill) return;
    currentAdjustBill = bill;

    document.getElementById('adjBillId').value = bill.id;
    document.getElementById('adjBillNo').innerText = bill.bill_number || '-';
    
    var clientName = '-';
    if (bill.BillingClient && bill.BillingClient.name) clientName = bill.BillingClient.name;
    else if (bill.billing_client_id && window.WV_PROPERTIES && bill.billing_property_id && window.WV_PROPERTIES[bill.billing_property_id]) clientName = 'Property #' + bill.billing_property_id;
    document.getElementById('adjConsumerName').innerText = clientName;

    document.getElementById('adjCurrentTotal').innerText = 'PKR ' + parseFloat(bill.total_amount || 0).toFixed(2);
    document.getElementById('adjCurrentBalance').innerText = 'PKR ' + parseFloat(bill.balance_amount || 0).toFixed(2);

    var stBadge = document.getElementById('adjBillStatusBadge');
    if (stBadge) {
        var st = (bill.payment_status || 'unpaid').toUpperCase();
        stBadge.innerText = st;
        stBadge.style.background = (st === 'PAID') ? '#dcfce7' : ((st === 'PARTIAL') ? '#fef3c7' : '#fee2e2');
        stBadge.style.color = (st === 'PAID') ? '#15803d' : ((st === 'PARTIAL') ? '#b45309' : '#b91c1c');
    }

    document.getElementById('adjAmountInput').value = '';
    document.getElementById('adjReasonInput').value = '';
    selectAdjType('credit');
    renderAdjustHistoryTable(bill.id);
    recalcAdjustModalPreview();

    $('#modalAdjustBill').modal('show');
}

window.WV_METERS = <?php echo json_encode(!empty($metersData) ? $metersData : array()); ?>;
window.WV_READINGS = <?php echo json_encode(!empty($allReadingsLookup) ? $allReadingsLookup : array()); ?>;
window.WV_LATEST_READINGS = <?php echo json_encode(!empty($latestReadingsLookup) ? $latestReadingsLookup : array()); ?>;
window.WV_PROPERTIES = <?php echo json_encode(!empty($propertiesData) ? $propertiesData : array()); ?>;
window.WV_EXISTING_BILLS = <?php echo json_encode(!empty($existingBillsLookup) ? $existingBillsLookup : array()); ?>;
window.WV_ADJUSTMENTS = <?php echo json_encode(!empty($adjustmentsByBill) ? $adjustmentsByBill : array()); ?>;
window.WV_LATE_FEE_RULES = <?php echo json_encode(!empty($lateFeeRules) ? $lateFeeRules : array('mode' => 'percent', 'val' => 7.0, 'min' => 200.0, 'max' => 2000.0)); ?>;
window.WV_DEFAULT_RATES = {
    elec: <?php echo (float)$defaultElecRate; ?>,
    water: <?php echo (float)$defaultWaterRate; ?>,
    gas: <?php echo (float)$defaultGasRate; ?>
};

var defaultDueDaysOffset = <?php echo (int)$defaultDueDays; ?>;

function escapeHtml(text) {
    if (text === null || text === undefined) return '';
    return String(text).replace(/[&<>"']/g, function(m) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m];
    });
}

function normalizeMonthKey(raw) {
    if (!raw) return '';
    var s = String(raw).trim();
    var m1 = s.match(/^(\d{4})[-\/](\d{1,2})/);
    if (m1) {
        return m1[1] + '-' + String(m1[2]).padStart(2, '0');
    }
    var m2 = s.match(/^(\d{1,2})[-\/](\d{4})/);
    if (m2) {
        return m2[2] + '-' + String(m2[1]).padStart(2, '0');
    }
    var mNames = { "jan": "01", "feb": "02", "mar": "03", "apr": "04", "may": "05", "jun": "06", "jul": "07", "aug": "08", "sep": "09", "oct": "10", "nov": "11", "dec": "12" };
    var parts = s.toLowerCase().split(/[\s,]+/);
    var yr = '', mo = '';
    parts.forEach(function(p) {
        if (/^\d{4}$/.test(p)) yr = p;
        var sub3 = p.substr(0, 3);
        if (mNames[sub3]) mo = mNames[sub3];
    });
    if (yr && mo) return yr + '-' + mo;
    return s;
}

function formatDisplayMonth(norm) {
    if (!norm) return '';
    var parts = norm.split('-');
    if (parts.length === 2) {
        var mNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
        var idx = parseInt(parts[1], 10) - 1;
        if (idx >= 0 && idx < 12) {
            return mNames[idx] + '-' + parts[0];
        }
    }
    return norm;
}

function calcDefaultDueDate() {
    var issueVal = document.getElementById('sbIssueDate').value;
    if (!issueVal) return;
    var dt = new Date(issueVal);
    dt.setDate(dt.getDate() + defaultDueDaysOffset);
    var yyyy = dt.getFullYear();
    var mm = String(dt.getMonth() + 1).padStart(2, '0');
    var dd = String(dt.getDate()).padStart(2, '0');
    var formattedDate = yyyy + '-' + mm + '-' + dd;
    
    var monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
    var previewStr = 'Preview: ' + dd + '-' + monthNames[dt.getMonth()] + '-' + yyyy;
    var prevEl = document.getElementById('sbDuePreviewText');
    if (prevEl) prevEl.innerText = previewStr;
    
    if (document.getElementById('sbDefaultDueCheck').checked) {
        document.getElementById('sbDueDate').value = formattedDate;
    }
}

function toggleDefaultDueDate() {
    var isChecked = document.getElementById('sbDefaultDueCheck').checked;
    if (isChecked) {
        calcDefaultDueDate();
    }
}

function handleSectionVisibility() {
    var maintChecked = $('#sbApplyMaint').is(':checked');
    var otherChecked = $('#sbApplyOther').is(':checked');

    if (maintChecked) {
        $('#sbMaintGroup').show();
    } else {
        $('#sbMaintGroup').hide();
    }

    if (otherChecked) {
        $('#sbOtherGroup').show();
    } else {
        $('#sbOtherGroup').hide();
    }
}

function toggleMaintSection() {
    var isChecked = $('#sbApplyMaint').is(':checked');
    if (isChecked) {
        $('#sbMaintGroup').slideDown(150);
    } else {
        $('#sbMaintGroup').slideUp(150);
    }
    recalcTotalReview();
}

function toggleOtherSection() {
    var isChecked = $('#sbApplyOther').is(':checked');
    if (isChecked) {
        $('#sbOtherGroup').slideDown(150);
    } else {
        $('#sbOtherGroup').slideUp(150);
    }
    recalcTotalReview();
}

function updateSingleBillPreview() {
    handleSectionVisibility();

    var meterEl = document.getElementById('sbMeterId');
    var monthEl = document.getElementById('sbMonth');
    if (!meterEl || !monthEl) return;

    var meterId = meterEl.value;
    var month = monthEl.value;
    var btn = document.getElementById('sbSubmitBtn');

    if (!meterId || !month) {
        resetReviewBoxes();
        return;
    }

    var meter = (window.WV_METERS && window.WV_METERS[meterId]) ? window.WV_METERS[meterId] : null;
    var propId = meter ? meter.property_id : 0;
    var uType = meter ? String(meter.utility_type).toLowerCase().trim() : 'electricity';
    var meterNo = meter ? meter.meter_number : '';

    var normMonth = normalizeMonthKey(month);
    var displayMonth = formatDisplayMonth(normMonth);

    // 1. Reading Lookup for Selected Month
    var rKey1 = meterId + '_' + normMonth;
    var rKey2 = meterId + '_' + month;
    var reading = null;
    if (window.WV_READINGS) {
        if (window.WV_READINGS[rKey1]) {
            reading = window.WV_READINGS[rKey1];
        } else if (window.WV_READINGS[rKey2]) {
            reading = window.WV_READINGS[rKey2];
        }
    }

    var latestReading = (window.WV_LATEST_READINGS && window.WV_LATEST_READINGS[meterId]) ? window.WV_LATEST_READINGS[meterId] : null;

    var readingNotice = document.getElementById('sbReadingNotice');
    if (reading) {
        var pR = parseFloat(reading.previous_reading);
        var cR = parseFloat(reading.current_reading);
        var uC = parseFloat(reading.units_consumed);
        if (readingNotice) {
            readingNotice.innerHTML = '<div style="color:#065f46;background:#d1fae5;border:1.5px solid #34d399;border-radius:6px;padding:8px 12px;font-size:12px;font-weight:700;margin-top:6px;"><i class="fa fa-check-circle"></i> Reading found for <strong>' + displayMonth + '</strong>: Prev <strong>' + pR.toFixed(2) + '</strong> &rarr; Curr <strong>' + cR.toFixed(2) + '</strong> = <strong>' + uC.toFixed(2) + ' consumed units</strong></div>';
        }
    } else {
        var latestInfo = '';
        if (latestReading && latestReading.reading_month) {
            latestInfo = ' (Latest saved: ' + formatDisplayMonth(latestReading.reading_month) + ' with ' + parseFloat(latestReading.units_consumed).toFixed(2) + ' units).';
        }
        if (readingNotice) {
            readingNotice.innerHTML = '<div style="color:#991b1b;background:#fee2e2;border:1.5px solid #f87171;border-radius:6px;padding:8px 12px;font-size:12px;font-weight:700;margin-top:6px;"><i class="fa fa-exclamation-triangle"></i> Error / Notice: This month (<strong>' + displayMonth + '</strong>) has no saved reading.' + latestInfo + ' Consumed units set to <strong>0.00</strong>. (Bill can still be generated).</div>';
        }
    }

    // 2. Populate Maintenance Breakdown inputs
    var propData = (window.WV_PROPERTIES && window.WV_PROPERTIES[propId]) ? window.WV_PROPERTIES[propId] : null;
    var maintItems = (propData && propData.maintenance_items) ? propData.maintenance_items : [];
    
    if (maintItems.length > 0) {
        var mHtml = '';
        maintItems.forEach(function(item) {
            mHtml += '<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">';
            mHtml += '<span style="font-weight:700;font-size:12px;color:#1e3a8a;">' + item.name + '</span>';
            mHtml += '<input type="number" step="0.01" class="form-control sb-maint-input" name="data[BillingBill][maint_items][' + item.name + ']" value="' + parseFloat(item.amount).toFixed(2) + '" style="width:140px;height:36px;border-radius:6px;font-weight:600;text-align:right;border:1px solid #cbd5e1;" oninput="recalcTotalReview()" />';
            mHtml += '</div>';
        });
        document.getElementById('sbMaintBox').innerHTML = mHtml;
    } else {
        document.getElementById('sbMaintBox').innerHTML = '<div style="background:#ffffff;border:1px dashed #cbd5e1;border-radius:6px;padding:10px 12px;font-size:12px;color:#94a3b8;"><?php echo __("No maintenance items found for selected property."); ?></div>';
    }

    // 3. Populate Other Charges inputs
    var otherItems = (propData && propData.other_charges_items) ? propData.other_charges_items : [];
    if (otherItems.length > 0) {
        var oHtml = '';
        otherItems.forEach(function(item) {
            oHtml += '<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">';
            oHtml += '<span style="font-weight:700;font-size:12px;color:#1e3a8a;">' + item.name + '</span>';
            oHtml += '<input type="number" step="0.01" class="form-control sb-other-input" name="data[BillingBill][other_items][' + item.name + ']" value="' + parseFloat(item.amount).toFixed(2) + '" style="width:140px;height:36px;border-radius:6px;font-weight:600;text-align:right;border:1px solid #cbd5e1;" oninput="recalcTotalReview()" />';
            oHtml += '</div>';
        });
        document.getElementById('sbOtherBox').innerHTML = oHtml;
    } else {
        document.getElementById('sbOtherBox').innerHTML = '<div style="background:#ffffff;border:1px dashed #cbd5e1;border-radius:6px;padding:10px 12px;font-size:12px;color:#94a3b8;"><?php echo __("No active other charges for selected property."); ?></div>';
    }

    // 4. Check duplicate bill
    var dupKey1 = propId + '_' + uType + '_' + normMonth;
    var dupKey2 = propId + '_' + uType + '_' + month;
    var existingBill = null;
    if (window.WV_EXISTING_BILLS) {
        if (window.WV_EXISTING_BILLS[dupKey1]) {
            existingBill = window.WV_EXISTING_BILLS[dupKey1];
        } else if (window.WV_EXISTING_BILLS[dupKey2]) {
            existingBill = window.WV_EXISTING_BILLS[dupKey2];
        }
    }

    var dupBanner = document.getElementById('sbDupBanner');
    var dupMeterNo = document.getElementById('sbDupMeterNo');
    var dupMonth = document.getElementById('sbDupMonth');

    if (existingBill) {
        if (dupBanner) {
            dupBanner.style.display = 'block';
            dupBanner.innerHTML = '<i class="fa fa-ban"></i> Error: Bill for this Meter No (<strong>' + (meterNo || '') + '</strong>) and month <strong>' + displayMonth + '</strong> already exists (Bill #' + (existingBill.bill_number || '') + ' &mdash; Total PKR ' + parseFloat(existingBill.total_amount || 0).toFixed(2) + '). New creation is blocked.';
        }
        if (btn) {
            btn.disabled = true;
            btn.style.background = '#94a3b8';
            btn.style.cursor = 'not-allowed';
            btn.innerHTML = '🚫 <?php echo __("Duplicate Blocked"); ?>';
        }
    } else {
        if (dupBanner) dupBanner.style.display = 'none';
        if (btn) {
            btn.disabled = false;
            btn.style.background = '#0284c7';
            btn.style.cursor = 'pointer';
            btn.innerHTML = '<?php echo __("Create"); ?>';
        }
    }

    recalcTotalReview();

    // 5. Live Server Validation & Calculation via AJAX
    var previewUrl = '<?php echo $this->Html->url(array("plugin" => "billing_manager", "controller" => "billing_bills", "action" => "get_meter_bill_preview")); ?>';
    $.ajax({
        url: previewUrl,
        type: 'GET',
        dataType: 'json',
        data: {
            meter_id: meterId,
            month: normMonth,
            rate_elec: $('#sbRateElec').val(),
            rate_water: $('#sbRateWater').val(),
            rate_gas: $('#sbRateGas').val(),
            apply_maint: $('#sbApplyMaint').is(':checked') ? 1 : 0,
            apply_other: $('#sbApplyOther').is(':checked') ? 1 : 0
        },
        success: function(res) {
            if (res && res.status === 'success') {
                document.getElementById('statUnits').innerText = parseFloat(res.units_consumed).toFixed(2);
                document.getElementById('statUtility').innerText = 'PKR ' + parseFloat(res.consumption_amount).toFixed(2);
                document.getElementById('statMaintenance').innerText = 'PKR ' + parseFloat(res.maintenance_amount).toFixed(2);
                document.getElementById('statOther').innerText = 'PKR ' + parseFloat(res.other_charges_amount).toFixed(2);
                document.getElementById('statArrears').innerText = 'PKR ' + parseFloat(res.arrears_amount).toFixed(2);
                document.getElementById('statCurrentBill').innerText = 'PKR ' + parseFloat(res.current_bill).toFixed(2);
                document.getElementById('statTotalWithinDue').innerText = 'PKR ' + parseFloat(res.total_within_due).toFixed(2);
                document.getElementById('statLateFee').innerText = 'PKR ' + parseFloat(res.late_fee).toFixed(2);
                document.getElementById('statPayableAfterDue').innerText = 'PKR ' + parseFloat(res.payable_after_due).toFixed(2);

                var rNotice = document.getElementById('sbReadingNotice');
                if (res.reading_found) {
                    $('#sbReadingEntryBox').slideUp(150);
                    if (rNotice) {
                        rNotice.innerHTML = '<div style="color:#065f46;background:#d1fae5;border:1.5px solid #34d399;border-radius:6px;padding:8px 12px;font-size:12px;font-weight:700;margin-top:6px;"><i class="fa fa-check-circle"></i> Reading found for <strong>' + displayMonth + '</strong>: Prev <strong>' + parseFloat(res.previous_reading).toFixed(2) + '</strong> &rarr; Curr <strong>' + parseFloat(res.current_reading).toFixed(2) + '</strong> = <strong>' + parseFloat(res.units_consumed).toFixed(2) + ' consumed units</strong></div>';
                    }
                } else {
                    $('#sbReadingEntryBox').slideDown(150);
                    if ($('#sbInputPrev').val() === '' || parseFloat($('#sbInputPrev').val()) === 0) {
                        $('#sbInputPrev').val(parseFloat(res.previous_reading || 0).toFixed(1));
                    }
                    var lInfo = '';
                    if (res.latest_reading_month) {
                        lInfo = ' (Latest saved: ' + formatDisplayMonth(res.latest_reading_month) + ' with ' + parseFloat(res.latest_reading_units).toFixed(2) + ' units).';
                    }
                    if (rNotice) {
                        rNotice.innerHTML = '<div style="color:#991b1b;background:#fee2e2;border:1.5px solid #f87171;border-radius:6px;padding:8px 12px;font-size:12px;font-weight:700;margin-top:6px;"><i class="fa fa-exclamation-triangle"></i> Notice: No reading saved for <strong>' + displayMonth + '</strong>' + lInfo + '. You can enter Previous & Current readings below to save on-the-fly!</div>';
                    }
                }

                var dBanner = document.getElementById('sbDupBanner');
                if (res.is_duplicate) {
                    if (dBanner) {
                        dBanner.style.display = 'block';
                        dBanner.innerHTML = '<i class="fa fa-ban"></i> Error: Bill for this Meter No (<strong>' + (res.meter_number || '') + '</strong>) and month <strong>' + displayMonth + '</strong> already exists (Bill #' + (res.existing_bill_number || '') + ' &mdash; Total PKR ' + parseFloat(res.existing_total_amount || 0).toFixed(2) + '). New creation is blocked.';
                    }
                    if (btn) {
                        btn.disabled = true;
                        btn.style.background = '#94a3b8';
                        btn.style.cursor = 'not-allowed';
                        btn.innerHTML = '🚫 Duplicate Blocked';
                    }
                } else {
                    if (dBanner) dBanner.style.display = 'none';
                    if (btn) {
                        btn.disabled = false;
                        btn.style.background = '#0284c7';
                        btn.style.cursor = 'pointer';
                        btn.innerHTML = 'Create Bill';
                    }
                }
            }
        }
    });
}

function calcSingleBillFromInlineReadings() {
    var inPrev = parseFloat($('#sbInputPrev').val() || 0);
    var inCurr = parseFloat($('#sbInputCurr').val() || 0);
    var inUnits = Math.max(0, inCurr - inPrev);
    
    $('#sbInlineUnits').text(inUnits.toFixed(1));
    
    // Update live stat preview
    var meterId = $('#sbMeterId').val();
    var meter = (window.WV_METERS && window.WV_METERS[meterId]) ? window.WV_METERS[meterId] : null;
    var uType = meter ? String(meter.utility_type).toLowerCase().trim() : 'electricity';
    
    var defRates = window.WV_DEFAULT_RATES || { elec: 58.34, water: 100, gas: 350 };
    var unitRate = defRates.elec;
    if (uType === 'water') {
        var rW = $('#sbRateWater').val();
        unitRate = (rW !== '' && !isNaN(parseFloat(rW))) ? parseFloat(rW) : defRates.water;
    } else if (uType === 'gas') {
        var rG = $('#sbRateGas').val();
        unitRate = (rG !== '' && !isNaN(parseFloat(rG))) ? parseFloat(rG) : defRates.gas;
    } else {
        var rE = $('#sbRateElec').val();
        unitRate = (rE !== '' && !isNaN(parseFloat(rE))) ? parseFloat(rE) : defRates.elec;
    }
    
    var utilityAmt = inUnits * unitRate;
    document.getElementById('statUnits').innerText = inUnits.toFixed(2);
    document.getElementById('statUtility').innerText = 'PKR ' + utilityAmt.toFixed(2);
    
    var maintTotal = 0.0;
    if ($('#sbApplyMaint').is(':checked')) {
        $('.sb-maint-input').each(function() {
            var val = parseFloat($(this).val());
            if (!isNaN(val)) maintTotal += val;
        });
    }
    var otherTotal = 0.0;
    if ($('#sbApplyOther').is(':checked')) {
        $('.sb-other-input').each(function() {
            var val = parseFloat($(this).val());
            if (!isNaN(val)) otherTotal += val;
        });
    }
    
    var propId = meter ? meter.property_id : 0;
    var propData = (window.WV_PROPERTIES && window.WV_PROPERTIES[propId]) ? window.WV_PROPERTIES[propId] : null;
    var arrearsAmt = propData ? parseFloat(propData.arrears_amount) : 0.0;
    
    var currentBill = utilityAmt + maintTotal + otherTotal;
    var totalWithinDue = currentBill + arrearsAmt;
    var lateFee = Math.round(currentBill * 0.07 * 100) / 100;
    var payableAfterDue = totalWithinDue + lateFee;
    
    document.getElementById('statCurrentBill').innerText = 'PKR ' + currentBill.toFixed(2);
    document.getElementById('statTotalWithinDue').innerText = 'PKR ' + totalWithinDue.toFixed(2);
    document.getElementById('statLateFee').innerText = 'PKR ' + lateFee.toFixed(2);
    document.getElementById('statPayableAfterDue').innerText = 'PKR ' + payableAfterDue.toFixed(2);
}

function setSbDueDays(days) {
    var issueVal = document.getElementById('sbIssueDate').value;
    if (!issueVal) issueVal = new Date().toISOString().split('T')[0];
    var dt = new Date(issueVal);
    dt.setDate(dt.getDate() + parseInt(days, 10));
    var yyyy = dt.getFullYear();
    var mm = String(dt.getMonth() + 1).padStart(2, '0');
    var dd = String(dt.getDate()).padStart(2, '0');
    document.getElementById('sbDueDate').value = yyyy + '-' + mm + '-' + dd;
    document.getElementById('sbDefaultDueCheck').checked = false;
    var monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
    var prevEl = document.getElementById('sbDuePreviewText');
    if (prevEl) prevEl.innerText = 'Preview: ' + dd + '-' + monthNames[dt.getMonth()] + '-' + yyyy;
}

function setSbDueMonthEnd() {
    var issueVal = document.getElementById('sbIssueDate').value;
    if (!issueVal) issueVal = new Date().toISOString().split('T')[0];
    var dt = new Date(issueVal);
    var endDt = new Date(dt.getFullYear(), dt.getMonth() + 1, 0);
    var yyyy = endDt.getFullYear();
    var mm = String(endDt.getMonth() + 1).padStart(2, '0');
    var dd = String(endDt.getDate()).padStart(2, '0');
    document.getElementById('sbDueDate').value = yyyy + '-' + mm + '-' + dd;
    document.getElementById('sbDefaultDueCheck').checked = false;
    var monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
    var prevEl = document.getElementById('sbDuePreviewText');
    if (prevEl) prevEl.innerText = 'Preview: ' + dd + '-' + monthNames[endDt.getMonth()] + '-' + yyyy;
}

// Bulk Pre-Flight Audit Runner
function runBulkPreflightAudit() {
    var month = $('#bulkMonth').val();
    if (!month) return;
    
    var socId = $('#bulkSocietyId').val() || 0;
    var auditUrl = '<?php echo $this->Html->url(array("plugin" => "billing_manager", "controller" => "billing_bills", "action" => "get_bulk_preflight_audit")); ?>';
    
    $.ajax({
        url: auditUrl,
        type: 'GET',
        dataType: 'json',
        data: {
            month: month,
            society_id: socId,
            apply_maint: $('#bulkApplyMaint').is(':checked') ? 1 : 0,
            apply_other: $('#bulkApplyOther').is(':checked') ? 1 : 0,
            rate_elec: $('#bulkRateElec').val(),
            rate_water: $('#bulkRateWater').val(),
            rate_gas: $('#bulkRateGas').val()
        },
        success: function(res) {
            if (res && res.status === 'success') {
                $('#pfReadingsFound').text(res.readings_found);
                $('#pfMissingReadings').text(res.missing_readings);
                $('#pfDuplicates').text(res.duplicates_count);
                $('#pfEstRevenue').text('PKR ' + parseFloat(res.est_grand_total || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                
                if (res.missing_readings > 0) {
                    $('#pfMissingCountText').text(res.missing_readings);
                    var listHtml = '<table class="table table-condensed" style="margin:0;font-size:11px;"><thead><tr><th>Meter #</th><th>Property</th><th>Consumer</th><th>Type</th></tr></thead><tbody>';
                    if (res.missing_meters_list && res.missing_meters_list.length > 0) {
                        res.missing_meters_list.forEach(function(m) {
                            listHtml += '<tr><td><strong>' + escapeHtml(m.meter_number) + '</strong></td><td>' + escapeHtml(m.property_title) + '</td><td>' + escapeHtml(m.client_name) + '</td><td><span class="badge" style="background:#0284c7;color:#fff;font-size:10px;">' + escapeHtml(m.utility_type) + '</span></td></tr>';
                        });
                    }
                    listHtml += '</tbody></table>';
                    $('#pfMissingList').html(listHtml);
                    $('#pfMissingAlert').slideDown(150);
                } else {
                    $('#pfMissingAlert').slideUp(150);
                }
            }
        }
    });
}

// Custom Bill Multi-Item Row Helpers
function addCustomBillRow(category, desc, amount) {
    var container = document.getElementById('cbRowsContainer');
    if (!container) return;
    
    var idx = container.querySelectorAll('.cb-item-row').length;
    var row = document.createElement('div');
    row.className = 'cb-item-row';
    row.style = 'display:grid;grid-template-columns:140px 1fr 120px 36px;gap:8px;align-items:center;';
    
    var selectedCat = category || 'Custom Charge';
    var itemDesc = desc || '';
    var itemAmt = amount ? parseFloat(amount).toFixed(2) : '';
    
    var cats = ['Custom Charge', 'Water Delivery', 'Construction Water', 'Fine / Penalty', 'Connection Fee', 'Transfer Fee', 'Security Deposit', 'Maintenance', 'Other'];
    var optionsHtml = '';
    cats.forEach(function(c) {
        optionsHtml += '<option value="' + c + '" ' + (c.toLowerCase() === selectedCat.toLowerCase() ? 'selected' : '') + '>' + c + '</option>';
    });
    
    row.innerHTML = 
        '<select name="data[BillingBill][items][' + idx + '][category]" class="form-control input-sm" style="border-radius:4px;font-weight:600;font-size:12px;">' + optionsHtml + '</select>' +
        '<input type="text" name="data[BillingBill][items][' + idx + '][desc]" value="' + escapeHtml(itemDesc) + '" placeholder="Item description" class="form-control input-sm" required style="border-radius:4px;font-weight:600;font-size:12px;" />' +
        '<input type="number" step="0.01" name="data[BillingBill][items][' + idx + '][amount]" value="' + itemAmt + '" placeholder="0.00" class="form-control input-sm cb-item-amount" required style="border-radius:4px;font-weight:700;font-size:12.5px;text-align:right;" oninput="recalcCustomBillTotal()" />' +
        '<button type="button" class="btn btn-sm btn-danger" onclick="removeCustomBillRow(this)" style="padding:4px 8px;border-radius:4px;"><i class="fa fa-times"></i></button>';
        
    container.appendChild(row);
    recalcCustomBillTotal();
}

function removeCustomBillRow(btn) {
    var row = btn.closest('.cb-item-row');
    if (row) {
        var container = document.getElementById('cbRowsContainer');
        if (container.querySelectorAll('.cb-item-row').length > 1) {
            row.remove();
        } else {
            // Clear inputs instead of removing last row
            row.querySelector('input[type="text"]').value = '';
            row.querySelector('input[type="number"]').value = '';
        }
        recalcCustomBillTotal();
    }
}

function recalcCustomBillTotal() {
    var total = 0;
    document.querySelectorAll('.cb-item-amount').forEach(function(inp) {
        var val = parseFloat(inp.value || 0);
        if (!isNaN(val)) total += val;
    });
    $('#cbGrandTotal').text(total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
}

function recalcTotalReview() {
    var meterEl = document.getElementById('sbMeterId');
    var monthEl = document.getElementById('sbMonth');
    if (!meterEl || !monthEl || !meterEl.value || !monthEl.value) {
        resetReviewBoxes();
        return;
    }

    var meterId = meterEl.value;
    var month = monthEl.value;
    var meter = (window.WV_METERS && window.WV_METERS[meterId]) ? window.WV_METERS[meterId] : null;
    var propId = meter ? meter.property_id : 0;
    var uType = meter ? String(meter.utility_type).toLowerCase().trim() : 'electricity';

    var normMonth = normalizeMonthKey(month);

    // 1. Reading & Units lookup
    var rKey1 = meterId + '_' + normMonth;
    var rKey2 = meterId + '_' + month;
    var reading = null;
    if (window.WV_READINGS) {
        if (window.WV_READINGS[rKey1]) {
            reading = window.WV_READINGS[rKey1];
        } else if (window.WV_READINGS[rKey2]) {
            reading = window.WV_READINGS[rKey2];
        }
    }

    var units = 0.0;
    if (reading) {
        units = parseFloat(reading.units_consumed);
        if (isNaN(units)) units = 0.0;
    }

    // 2. Unit Rate
    var rateElecInp = $('#sbRateElec').val();
    var rateWaterInp = $('#sbRateWater').val();
    var rateGasInp = $('#sbRateGas').val();
    var defRates = window.WV_DEFAULT_RATES || { elec: 58.34, water: 100, gas: 350 };

    var unitRate = defRates.elec;
    if (uType === 'water') {
        unitRate = (rateWaterInp !== '' && !isNaN(parseFloat(rateWaterInp))) ? parseFloat(rateWaterInp) : defRates.water;
    } else if (uType === 'gas') {
        unitRate = (rateGasInp !== '' && !isNaN(parseFloat(rateGasInp))) ? parseFloat(rateGasInp) : defRates.gas;
    } else {
        unitRate = (rateElecInp !== '' && !isNaN(parseFloat(rateElecInp))) ? parseFloat(rateElecInp) : defRates.elec;
    }

    var utilityAmt = units * unitRate;

    // 3. Maintenance Sum
    var maintTotal = 0.0;
    var applyMaint = $('#sbApplyMaint').is(':checked');
    if (applyMaint) {
        $('.sb-maint-input').each(function() {
            var val = parseFloat($(this).val());
            if (!isNaN(val)) maintTotal += val;
        });
    }

    // 4. Other Charges Sum
    var otherTotal = 0.0;
    var applyOther = $('#sbApplyOther').is(':checked');
    if (applyOther) {
        $('.sb-other-input').each(function() {
            var val = parseFloat($(this).val());
            if (!isNaN(val)) otherTotal += val;
        });
    }

    // 5. Arrears (Past unpaid balances auto carry-forward)
    var propData = (window.WV_PROPERTIES && window.WV_PROPERTIES[propId]) ? window.WV_PROPERTIES[propId] : null;
    var arrearsAmt = propData ? parseFloat(propData.arrears_amount) : 0.0;

    // 6. Current Bill & Totals
    var currentBill = utilityAmt + maintTotal + otherTotal;
    var totalWithinDue = currentBill + arrearsAmt;

    // 7. Late Fee: from backend settings rules
    var lateFee = 0.0;
    if (currentBill > 0) {
        var rules = window.WV_LATE_FEE_RULES || { mode: 'percent', val: 7.0, min: 200.0, max: 2000.0 };
        if (rules.mode === 'fixed') {
            lateFee = parseFloat(rules.val);
        } else {
            var pctAmt = currentBill * (parseFloat(rules.val) / 100.0);
            lateFee = Math.max(parseFloat(rules.min), Math.min(parseFloat(rules.max), Math.round(pctAmt)));
        }
    }
    var payableAfterDue = totalWithinDue + lateFee;

    // Update 9 Stat Tiles
    document.getElementById('statUnits').innerText = units.toFixed(2);
    document.getElementById('statUtility').innerText = 'PKR ' + utilityAmt.toFixed(2);
    document.getElementById('statMaintenance').innerText = 'PKR ' + maintTotal.toFixed(2);
    document.getElementById('statOther').innerText = 'PKR ' + otherTotal.toFixed(2);
    document.getElementById('statArrears').innerText = 'PKR ' + arrearsAmt.toFixed(2);
    document.getElementById('statCurrentBill').innerText = 'PKR ' + currentBill.toFixed(2);
    document.getElementById('statTotalWithinDue').innerText = 'PKR ' + totalWithinDue.toFixed(2);
    document.getElementById('statLateFee').innerText = 'PKR ' + lateFee.toFixed(2);
    document.getElementById('statPayableAfterDue').innerText = 'PKR ' + payableAfterDue.toFixed(2);
}

function resetReviewBoxes() {
    document.getElementById('statUnits').innerText = '0.00';
    document.getElementById('statUtility').innerText = 'PKR 0.00';
    document.getElementById('statMaintenance').innerText = 'PKR 0.00';
    document.getElementById('statOther').innerText = 'PKR 0.00';
    document.getElementById('statArrears').innerText = 'PKR 0.00';
    document.getElementById('statCurrentBill').innerText = 'PKR 0.00';
    document.getElementById('statTotalWithinDue').innerText = 'PKR 0.00';
    document.getElementById('statLateFee').innerText = 'PKR 0.00';
    document.getElementById('statPayableAfterDue').innerText = 'PKR 0.00';
    var readingNotice = document.getElementById('sbReadingNotice');
    if (readingNotice) readingNotice.innerHTML = '';
    var dupBanner = document.getElementById('sbDupBanner');
    if (dupBanner) dupBanner.style.display = 'none';
    var btn = document.getElementById('sbSubmitBtn');
    if (btn) {
        btn.disabled = false;
        btn.style.background = '#0284c7';
        btn.style.cursor = 'pointer';
        btn.innerHTML = '<?php echo __("Create"); ?>';
    }
}

jQuery(document).ready(function($) {
    if ($.fn.select2) {
        $('#sbMeterId').select2({
            placeholder: '<?php echo __("Select"); ?>',
            allowClear: true,
            dropdownParent: $('#modalSingleBill'),
            width: '100%'
        }).on('change select2:select select2:unselect', function() {
            updateSingleBillPreview();
        });
    } else {
        $('#sbMeterId').on('change', updateSingleBillPreview);
    }

    $('#sbMonth, #sbRateElec, #sbRateWater, #sbRateGas').on('input change blur keyup paste', updateSingleBillPreview);
    $('#sbIssueDate').on('change blur', calcDefaultDueDate);
    $('#sbDefaultDueCheck').on('change', toggleDefaultDueDate);

    $('#modalSingleBill').on('shown.bs.modal', function() {
        handleSectionVisibility();
        calcDefaultDueDate();
        if ($('#sbMeterId').val()) {
            updateSingleBillPreview();
        } else {
            var firstVal = $('#sbMeterId option[value!=""]').first().val();
            if (firstVal) {
                $('#sbMeterId').val(firstVal).trigger('change');
            }
        }
    });

    $('#modalBulkBill').on('shown.bs.modal', function() {
        runBulkPreflightAudit();
    });

    <?php
    $autoPrintBillId = $this->Session->read('WV_AUTO_PRINT_BILL_ID');
    if (!empty($autoPrintBillId)) {
        $this->Session->delete('WV_AUTO_PRINT_BILL_ID');
        ?>
        var printUrl = '<?php echo $this->Html->url(array("plugin" => "billing_manager", "controller" => "billing_bills", "action" => "print_bill", $autoPrintBillId)); ?>';
        window.open(printUrl, '_blank');
        <?php
    }
    ?>
});

function closePaymentSuccessModal() {
    var m = document.getElementById('modalPaymentSuccessNotification');
    if(m) {
        m.style.display = 'none';
        m.classList.remove('in');
    }
}

var activeDispatchContext = null;

function openBillDispatchModal(channel, billId, clientName, phone, statusType, billNo, month, amount, btn) {
    if(!phone || phone.trim() === '') {
        showModernResultModal(false, '<?php echo __("Recipient has no registered phone number in the system."); ?>', null, null);
        return;
    }

    activeDispatchContext = {
        channel: channel,
        billId: billId,
        clientName: clientName,
        phone: phone,
        statusType: statusType,
        billNo: billNo,
        month: month,
        amount: amount,
        btn: btn
    };

    var modal = document.getElementById('modalModernDispatchNotice');
    if(!modal) return;

    // Reset States
    document.getElementById('mDispatchStateConfirm').style.display = 'block';
    document.getElementById('mDispatchStateSending').style.display = 'none';
    document.getElementById('mDispatchStateSuccess').style.display = 'none';
    document.getElementById('mDispatchStateError').style.display = 'none';

    var header = document.getElementById('mDispatchHeader');
    var headerIcon = document.getElementById('mDispatchHeaderIcon');
    var title = document.getElementById('mDispatchTitle');
    var subtitle = document.getElementById('mDispatchSubtitle');
    var pdfNotice = document.getElementById('mDispatchPdfNotice');
    var execBtn = document.getElementById('btnModernDispatchExecute');
    var execLabel = document.getElementById('mDispatchExecuteLabel');
    var cancelBtn = document.getElementById('btnModernDispatchCancel');

    cancelBtn.style.display = 'inline-block';
    cancelBtn.innerHTML = '<i class="fa fa-times"></i> <?php echo __("Cancel / Close"); ?>';
    execBtn.style.display = 'inline-block';
    execBtn.disabled = false;

    // Populate data
    document.getElementById('mDispatchClientName').textContent = clientName;
    document.getElementById('mDispatchPhone').textContent = phone;
    document.getElementById('mDispatchBillPeriod').textContent = '#' + billNo + ' (' + month + ')';
    document.getElementById('mDispatchAmount').textContent = 'PKR ' + amount + (statusType === 'paid' ? ' (Paid)' : ' (Due)');

    if(channel === 'whatsapp') {
        header.style.background = 'linear-gradient(135deg, #059669 0%, #10b981 100%)';
        headerIcon.innerHTML = '<i class="fa fa-whatsapp"></i>';
        title.textContent = (statusType === 'paid') ? '<?php echo __("Confirm WhatsApp Paid Slip Delivery"); ?>' : '<?php echo __("Confirm WhatsApp Bill Notice Delivery"); ?>';
        subtitle.textContent = '<?php echo __("LeadBot CRM REST API • Automated PDF Attachment"); ?>';
        pdfNotice.style.display = 'flex';
        execBtn.className = 'btn btn-success';
        execBtn.style.background = '#059669';
        execBtn.style.borderColor = '#059669';
        execLabel.textContent = '<?php echo __("Send WhatsApp Now via LeadBot"); ?>';
    } else {
        header.style.background = 'linear-gradient(135deg, #0284c7 0%, #38bdf8 100%)';
        headerIcon.innerHTML = '<i class="fa fa-commenting"></i>';
        title.textContent = (statusType === 'paid') ? '<?php echo __("Confirm SMS Paid Receipt Delivery"); ?>' : '<?php echo __("Confirm SMS Bill Reminder Delivery"); ?>';
        subtitle.textContent = '<?php echo __("Default System SMS Gateway"); ?>';
        pdfNotice.style.display = 'none';
        execBtn.className = 'btn btn-info';
        execBtn.style.background = '#0284c7';
        execBtn.style.borderColor = '#0284c7';
        execLabel.textContent = '<?php echo __("Send SMS Now via Default Gateway"); ?>';
    }

    modal.style.display = 'block';
    modal.classList.add('in');
}

function closeModernDispatchModal() {
    var modal = document.getElementById('modalModernDispatchNotice');
    if(modal) {
        modal.style.display = 'none';
        modal.classList.remove('in');
    }
}

function executeModernDispatch() {
    if(!activeDispatchContext) return;

    var ctx = activeDispatchContext;
    var execBtn = document.getElementById('btnModernDispatchExecute');
    var cancelBtn = document.getElementById('btnModernDispatchCancel');

    document.getElementById('mDispatchStateConfirm').style.display = 'none';
    document.getElementById('mDispatchStateSending').style.display = 'block';
    execBtn.style.display = 'none';
    cancelBtn.style.display = 'none';

    var sendingTitle = document.getElementById('mDispatchSendingTitle');
    if(ctx.channel === 'whatsapp') {
        sendingTitle.textContent = '<?php echo __("Connecting to LeadBot Gateway & Attaching Official PDF..."); ?>';
    } else {
        sendingTitle.textContent = '<?php echo __("Connecting to Default SMS Gateway..."); ?>';
    }

    var endpoint = (ctx.channel === 'whatsapp')
        ? '<?php echo $this->Html->url(array("action" => "send_bill_whatsapp")); ?>/' + ctx.billId + '?json=1'
        : '<?php echo $this->Html->url(array("action" => "send_bill_sms")); ?>/' + ctx.billId + '?json=1';

    $.ajax({
        url: endpoint,
        type: 'GET',
        dataType: 'json',
        success: function(resp) {
            document.getElementById('mDispatchStateSending').style.display = 'none';
            cancelBtn.style.display = 'inline-block';
            cancelBtn.innerHTML = '<i class="fa fa-check"></i> <?php echo __("Done / Close"); ?>';

            if(resp && resp.success) {
                document.getElementById('mDispatchStateSuccess').style.display = 'block';
                document.getElementById('mDispatchSuccessMsg').textContent = resp.message || 'Notification successfully dispatched!';
                var metaHtml = '<strong>Recipient:</strong> ' + (resp.recipient || ctx.phone);
                if(resp.message_id) {
                    metaHtml += ' &bull; <strong>Message ID:</strong> ' + resp.message_id;
                }
                if(resp.has_pdf) {
                    metaHtml += ' &bull; <strong style="color:#059669;">✓ Official PDF Voucher Attached</strong>';
                }
                document.getElementById('mDispatchSuccessMeta').innerHTML = metaHtml;

                if(ctx.btn) {
                    ctx.btn.innerHTML = '<i class="fa fa-check" style="color:#ffffff !important;"></i>';
                    ctx.btn.style.background = '#059669';
                }
            } else {
                document.getElementById('mDispatchStateError').style.display = 'block';
                var errDetail = (resp && resp.message) ? resp.message : 'Gateway delivery failed.';
                document.getElementById('mDispatchErrorMsg').textContent = errDetail;

                var fallbackWrap = document.getElementById('mDispatchFallbackWrap');
                var fallbackLink = document.getElementById('mDispatchFallbackLink');
                if(resp && resp.fallback_url && ctx.channel === 'whatsapp') {
                    fallbackWrap.style.display = 'block';
                    fallbackLink.href = resp.fallback_url;
                } else {
                    fallbackWrap.style.display = 'none';
                }
            }
        },
        error: function(xhr) {
            document.getElementById('mDispatchStateSending').style.display = 'none';
            cancelBtn.style.display = 'inline-block';
            cancelBtn.innerHTML = '<i class="fa fa-times"></i> <?php echo __("Close"); ?>';

            document.getElementById('mDispatchStateError').style.display = 'block';
            document.getElementById('mDispatchErrorMsg').textContent = 'Network communication error connecting to gateway service.';
        }
    });
}

function showModernResultModal(isSuccess, message, metaText, fallbackUrl) {
    var modal = document.getElementById('modalModernDispatchNotice');
    if(!modal) return;

    document.getElementById('mDispatchStateConfirm').style.display = 'none';
    document.getElementById('mDispatchStateSending').style.display = 'none';
    
    var execBtn = document.getElementById('btnModernDispatchExecute');
    var cancelBtn = document.getElementById('btnModernDispatchCancel');
    execBtn.style.display = 'none';
    cancelBtn.style.display = 'inline-block';
    cancelBtn.innerHTML = '<i class="fa fa-times"></i> <?php echo __("Done / Close"); ?>';

    if(isSuccess) {
        document.getElementById('mDispatchStateSuccess').style.display = 'block';
        document.getElementById('mDispatchStateError').style.display = 'none';
        document.getElementById('mDispatchSuccessMsg').textContent = message;
        document.getElementById('mDispatchSuccessMeta').innerHTML = metaText || '';
    } else {
        document.getElementById('mDispatchStateSuccess').style.display = 'none';
        document.getElementById('mDispatchStateError').style.display = 'block';
        document.getElementById('mDispatchErrorMsg').textContent = message;
        var fallbackWrap = document.getElementById('mDispatchFallbackWrap');
        var fallbackLink = document.getElementById('mDispatchFallbackLink');
        if(fallbackUrl) {
            fallbackWrap.style.display = 'block';
            fallbackLink.href = fallbackUrl;
        } else {
            fallbackWrap.style.display = 'none';
        }
    }

    modal.style.display = 'block';
    modal.classList.add('in');
}

function sendBillWhatsappAjax(billId, btn) {
    if(!billId) return;
    var origHtml = btn ? btn.innerHTML : '';
    var origClass = btn ? btn.className : '';
    var fallbackUrl = btn ? (btn.getAttribute('data-wa-fallback') || '') : '';

    if(btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i>' + (btn.classList.contains('btn') ? ' <?php echo __("Sending via LeadBot..."); ?>' : '');
    }
    var statusEl = document.getElementById('postPaySmsStatus');
    if(statusEl) {
        statusEl.style.display = 'block';
        statusEl.style.color = '#059669';
        statusEl.innerHTML = '<i class="fa fa-spinner fa-spin"></i> <?php echo __("Dispatching WhatsApp & PDF Slip via LeadBot API..."); ?>';
    }

    $.ajax({
        url: '<?php echo $this->Html->url(array("action" => "send_bill_whatsapp")); ?>/' + billId + '?json=1',
        type: 'GET',
        dataType: 'json',
        success: function(resp) {
            if(resp && resp.success) {
                if(btn) {
                    btn.disabled = false;
                    if(btn.classList.contains('btn')) {
                        btn.className = 'btn btn-success';
                        btn.innerHTML = '<i class="fa fa-check-circle"></i> <?php echo __("Sent via LeadBot!"); ?>';
                    } else {
                        btn.innerHTML = '<i class="fa fa-check" style="color:#ffffff !important;"></i>';
                        btn.style.background = '#059669';
                        setTimeout(function(){
                            btn.innerHTML = origHtml;
                            btn.className = origClass;
                        }, 4000);
                    }
                }
                if(statusEl) {
                    statusEl.style.color = '#15803d';
                    statusEl.innerHTML = '✓ ' + (resp.message || 'WhatsApp message & official PDF dispatched via LeadBot!');
                }
                showModernResultModal(true, resp.message || 'WhatsApp message & official PDF slip dispatched via LeadBot API!', '<strong>Recipient:</strong> ' + (resp.recipient || '') + ' &bull; <strong>Message ID:</strong> ' + (resp.message_id || '') + ' &bull; <strong style="color:#059669;">✓ PDF Slip Attached</strong>', null);
            } else {
                if(btn) {
                    btn.disabled = false;
                    btn.innerHTML = origHtml;
                    btn.className = origClass;
                }
                var errDetail = (resp && resp.message) ? resp.message : 'Failed to dispatch WhatsApp message via LeadBot.';
                if(statusEl) {
                    statusEl.style.color = '#dc2626';
                    statusEl.innerHTML = '⚠ ' + errDetail;
                }
                var targetFallback = (resp && resp.fallback_url) ? resp.fallback_url : fallbackUrl;
                showModernResultModal(false, errDetail, null, targetFallback);
            }
        },
        error: function(xhr) {
            if(btn) {
                btn.disabled = false;
                btn.innerHTML = origHtml;
                btn.className = origClass;
            }
            if(statusEl) {
                statusEl.style.color = '#dc2626';
                statusEl.innerHTML = '⚠ <?php echo __("Gateway network error connecting to LeadBot."); ?>';
            }
            showModernResultModal(false, 'Failed to connect to LeadBot WhatsApp service.', null, fallbackUrl);
        }
    });
}

function sendBillSmsAjax(billId, btn) {
    if(!billId) return;
    var origHtml = btn ? btn.innerHTML : '';
    var origClass = btn ? btn.className : '';
    if(btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i>' + (btn.classList.contains('btn') ? ' <?php echo __("Sending SMS..."); ?>' : '');
    }
    var statusEl = document.getElementById('postPaySmsStatus');
    if(statusEl) {
        statusEl.style.display = 'block';
        statusEl.style.color = '#0284c7';
        statusEl.innerHTML = '<i class="fa fa-spinner fa-spin"></i> <?php echo __("Dispatching SMS via Default Gateway..."); ?>';
    }

    $.ajax({
        url: '<?php echo $this->Html->url(array("action" => "send_bill_sms")); ?>/' + billId + '?json=1',
        type: 'GET',
        dataType: 'json',
        success: function(resp) {
            if(resp && resp.success) {
                if(btn) {
                    btn.disabled = false;
                    if(btn.classList.contains('btn')) {
                        btn.className = 'btn btn-success';
                        btn.innerHTML = '<i class="fa fa-check-circle"></i> <?php echo __("SMS Sent!"); ?>';
                    } else {
                        btn.innerHTML = '<i class="fa fa-check" style="color:#ffffff !important;"></i>';
                        setTimeout(function(){
                            btn.innerHTML = origHtml;
                            btn.className = origClass;
                        }, 4000);
                    }
                }
                if(statusEl) {
                    statusEl.style.color = '#15803d';
                    statusEl.innerHTML = '✓ ' + (resp.message || 'SMS successfully dispatched via default gateway!');
                }
                showModernResultModal(true, resp.message || 'SMS successfully dispatched to resident!', '<strong>Phone:</strong> ' + (resp.phone || ''), null);
            } else {
                if(btn) {
                    btn.disabled = false;
                    btn.innerHTML = origHtml;
                    btn.className = origClass;
                }
                if(statusEl) {
                    statusEl.style.color = '#dc2626';
                    statusEl.innerHTML = '⚠ ' + (resp && resp.message ? resp.message : 'Failed to dispatch SMS.');
                }
                showModernResultModal(false, (resp && resp.message) ? resp.message : 'Could not dispatch SMS.', null, null);
            }
        },
        error: function(xhr) {
            if(btn) {
                btn.disabled = false;
                btn.innerHTML = origHtml;
                btn.className = origClass;
            }
            if(statusEl) {
                statusEl.style.color = '#dc2626';
                statusEl.innerHTML = '⚠ <?php echo __("Gateway network error."); ?>';
            }
            showModernResultModal(false, 'Failed to connect to SMS service.', null, null);
        }
    });
}

/* ======================================================== */
/* BULK WHATSAPP DISPATCH ENGINE HANDLERS                   */
/* ======================================================== */
var bulkWhatsappActive = false;
var bulkWhatsappStopRequested = false;
var bulkWhatsappSelectedIds = [];

function openBulkWhatsappModal() {
    var checkedBoxes = document.querySelectorAll('.row-checkbox:checked');
    if(checkedBoxes.length === 0) {
        showModernResultModal(false, '<?php echo __("Please select at least one bill checkbox from the table list first."); ?>', null, null);
        return;
    }

    bulkWhatsappSelectedIds = [];
    checkedBoxes.forEach(function(c) {
        if(c.value && c.value !== '') {
            bulkWhatsappSelectedIds.push(c.value);
        }
    });

    if(bulkWhatsappSelectedIds.length === 0) {
        showModernResultModal(false, '<?php echo __("No valid bills selected."); ?>', null, null);
        return;
    }

    var modal = document.getElementById('modalBulkWhatsappProgress');
    if(!modal) return;

    // Reset UI to Confirmation State
    document.getElementById('bwaStateConfirm').style.display = 'block';
    document.getElementById('bwaStateProgress').style.display = 'none';
    document.getElementById('bwaStateComplete').style.display = 'none';
    document.getElementById('bwaSelectedCount').textContent = bulkWhatsappSelectedIds.length;

    var startBtn = document.getElementById('btnBwaStart');
    var cancelBtn = document.getElementById('btnBwaCancel');
    startBtn.style.display = 'inline-block';
    startBtn.disabled = false;
    startBtn.innerHTML = '<i class="fa fa-paper-plane"></i> <?php echo __("Start Bulk Dispatch Now"); ?>';
    cancelBtn.style.display = 'inline-block';
    cancelBtn.innerHTML = '<i class="fa fa-times"></i> <?php echo __("Cancel / Close"); ?>';

    bulkWhatsappActive = false;
    bulkWhatsappStopRequested = false;

    modal.style.display = 'block';
    modal.classList.add('in');
}

function closeBulkWhatsappModal() {
    if(bulkWhatsappActive) {
        if(confirm('Bulk WhatsApp dispatch is currently running. Are you sure you want to stop?')) {
            bulkWhatsappStopRequested = true;
        }
        return;
    }
    var modal = document.getElementById('modalBulkWhatsappProgress');
    if(modal) {
        modal.style.display = 'none';
        modal.classList.remove('in');
    }
}

function startBulkWhatsappExecution() {
    if(bulkWhatsappSelectedIds.length === 0) return;

    bulkWhatsappActive = true;
    bulkWhatsappStopRequested = false;

    document.getElementById('bwaStateConfirm').style.display = 'none';
    document.getElementById('bwaStateProgress').style.display = 'block';
    document.getElementById('bwaStateComplete').style.display = 'none';

    var startBtn = document.getElementById('btnBwaStart');
    var cancelBtn = document.getElementById('btnBwaCancel');
    startBtn.style.display = 'none';
    cancelBtn.innerHTML = '<i class="fa fa-stop"></i> <?php echo __("Stop / Cancel"); ?>';

    var logBox = document.getElementById('bwaLogContainer');
    logBox.innerHTML = '<div style="color:#38bdf8;">[' + new Date().toLocaleTimeString() + '] Initializing Bulk WhatsApp Dispatch for ' + bulkWhatsappSelectedIds.length + ' bills...</div>';

    var total = bulkWhatsappSelectedIds.length;
    var currentIdx = 0;
    var successCount = 0;
    var failCount = 0;

    function processNext() {
        if(bulkWhatsappStopRequested) {
            logBox.innerHTML += '<div style="color:#f59e0b;">[' + new Date().toLocaleTimeString() + '] ⏹ Bulk dispatch stopped by user.</div>';
            finishBulkExecution(total, successCount, failCount);
            return;
        }

        if(currentIdx >= total) {
            finishBulkExecution(total, successCount, failCount);
            return;
        }

        var billId = bulkWhatsappSelectedIds[currentIdx];
        var itemNum = currentIdx + 1;
        var percent = Math.round((currentIdx / total) * 100);

        document.getElementById('bwaProgressBar').style.width = percent + '%';
        document.getElementById('bwaProgressPercent').textContent = percent + '%';
        document.getElementById('bwaProgressStatus').textContent = 'Processing ' + itemNum + ' of ' + total + ' (Bill ID #' + billId + ')...';

        logBox.innerHTML += '<div style="color:#94a3b8;">[' + new Date().toLocaleTimeString() + '] [' + itemNum + '/' + total + '] Rendering PDF & dispatching Bill #' + billId + '...</div>';
        logBox.scrollTop = logBox.scrollHeight;

        $.ajax({
            url: '<?php echo $this->Html->url(array("action" => "send_bill_whatsapp")); ?>/' + billId + '?json=1',
            type: 'GET',
            dataType: 'json',
            timeout: 90000,
            success: function(resp) {
                if(resp && resp.success) {
                    successCount++;
                    var name = resp.client_name || 'Resident';
                    var num = resp.bill_number || billId;
                    var phone = resp.recipient || '';
                    var pdfTag = resp.has_pdf ? ' <span style="color:#86efac;font-weight:bold;">[PDF Attached]</span>' : '';
                    logBox.innerHTML += '<div style="color:#4ade80;">✓ [' + itemNum + '/' + total + '] Sent Bill #' + num + ' to ' + name + ' (' + phone + ')' + pdfTag + ' via LeadBot</div>';
                } else {
                    failCount++;
                    var err = (resp && resp.message) ? resp.message : 'Delivery failed';
                    logBox.innerHTML += '<div style="color:#f87171;">⚠️ [' + itemNum + '/' + total + '] Bill #' + billId + ' failed: ' + err + '</div>';
                }
                logBox.scrollTop = logBox.scrollHeight;
                currentIdx++;
                setTimeout(processNext, 500);
            },
            error: function(xhr, status, error) {
                failCount++;
                logBox.innerHTML += '<div style="color:#f87171;">⚠️ [' + itemNum + '/' + total + '] Bill #' + billId + ' connection error: ' + (error || status) + '</div>';
                logBox.scrollTop = logBox.scrollHeight;
                currentIdx++;
                setTimeout(processNext, 500);
            }
        });
    }

    processNext();
}

function finishBulkExecution(total, successCount, failCount) {
    bulkWhatsappActive = false;
    document.getElementById('bwaProgressBar').style.width = '100%';
    document.getElementById('bwaProgressPercent').textContent = '100%';

    document.getElementById('bwaStateProgress').style.display = 'none';
    document.getElementById('bwaStateComplete').style.display = 'block';

    document.getElementById('bwaStatTotal').textContent = total;
    document.getElementById('bwaStatSent').textContent = successCount;
    document.getElementById('bwaStatFailed').textContent = failCount;

    var cancelBtn = document.getElementById('btnBwaCancel');
    cancelBtn.style.display = 'inline-block';
    cancelBtn.innerHTML = '<i class="fa fa-check"></i> <?php echo __("Done / Close"); ?>';
}
</script>
