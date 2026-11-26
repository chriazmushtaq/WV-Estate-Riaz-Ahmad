<style type="text/css">
.wv-mypayments-page{background:#f8fafc;margin:-10px -5px 0;padding:16px;touch-action:auto;overflow-x:auto;-webkit-overflow-scrolling:touch;width:100%;}
.wv-mypayments-page *{box-sizing:border-box;}

/* Hero Header - Professional Light Theme */
.wv-mypayments-hero{background:#ffffff;border:1px solid #e2e8f0;border-top:4px solid #0284c7;border-radius:14px;box-shadow:0 4px 18px rgba(15,23,42,.05);color:#0f172a;margin-bottom:18px;padding:22px 24px;position:relative;}
.wv-mypayments-hero-inner{align-items:center;display:flex;gap:18px;justify-content:space-between;position:relative;z-index:1;}
.wv-mypayments-kicker{color:#0284c7;font-size:11px;font-weight:900;letter-spacing:0.5px;margin-bottom:4px;text-transform:uppercase;}
.wv-mypayments-hero h1{color:#0f172a!important;font-size:24px;font-weight:900;line-height:1.2;margin:0 0 6px;}
.wv-mypayments-hero p{color:#475569!important;font-size:13.5px;font-weight:600;line-height:1.5;margin:0;max-width:680px;}
.wv-mypayments-hero-actions{display:flex;flex-wrap:wrap;gap:8px;justify-content:flex-end;}
.wv-mypayments-hero-actions .btn{align-items:center;border:1px solid #e2e8f0;border-radius:8px;display:inline-flex;font-size:11px;font-weight:800;gap:6px;padding:9px 15px;text-transform:uppercase;color:#fff!important;box-shadow:0 2px 6px rgba(0,0,0,.1);text-decoration:none!important;transition:all .2s;min-height:38px;}
.wv-mypayments-hero-actions .btn-deals{background:#0f172a!important;border-color:#0f172a!important;}
.wv-mypayments-hero-actions .btn-overdue{background:#dc2626!important;border-color:#dc2626!important;}
.wv-mypayments-hero-actions .btn:hover{filter:brightness(1.1);transform:translateY(-1px);}

/* Overall Stats KPIs */
.wv-mypayments-stats{display:grid;gap:12px;grid-template-columns:repeat(4,minmax(0,1fr));margin-bottom:18px;}
.wv-mypayments-stat{background:#fff;border:1px solid #e2e8f0;border-radius:10px;box-shadow:0 4px 14px rgba(15,23,42,.04);padding:14px 16px;position:relative;overflow:hidden;}
.wv-mypayments-stat:before{background:#0284c7;border-radius:999px;content:"";height:44px;opacity:.08;position:absolute;right:-13px;top:-13px;width:44px;}
.wv-mypayments-stat.stat-paid:before{background:#059669;}
.wv-mypayments-stat.stat-balance:before{background:#dc2626;}
.wv-mypayments-stat.stat-deals:before{background:#ff0a46;}
.wv-mypayments-stat span{color:#64748b;display:block;font-size:11px;font-weight:800;letter-spacing:0.3px;margin-bottom:4px;text-transform:uppercase;}
.wv-mypayments-stat strong{color:#07152a;display:block;font-size:20px;font-weight:900;line-height:1.15;}
.wv-mypayments-stat.stat-paid strong{color:#059669;}
.wv-mypayments-stat.stat-balance strong{color:#dc2626;}
.wv-mypayments-stat small{color:#64748b;display:block;font-size:11px;font-weight:700;margin-top:4px;}

/* Table Card & Toolbar */
.wv-payments-card{background:#fff;border:1px solid #e2e8f0;border-radius:12px;box-shadow:0 6px 18px rgba(15,23,42,.05);overflow:hidden;}
.wv-table-toolbar{align-items:center;background:#fff;border-bottom:1px solid #edf2f7;display:flex;flex-wrap:wrap;gap:12px;justify-content:space-between;padding:14px 18px;}
.wv-table-toolbar-left{align-items:center;display:flex;flex-wrap:wrap;gap:10px;}
.wv-table-title{color:#0f172a;font-size:15px;font-weight:900;margin:0;}
.wv-table-badge{background:#e0f2fe;border:1px solid #bae6fd;border-radius:6px;color:#0284c7;font-size:11px;font-weight:800;padding:3px 8px;}
.wv-table-toolbar-right{align-items:center;display:flex;flex-wrap:wrap;gap:8px;}
.wv-search-input-wrap{position:relative;}
.wv-search-input-wrap i{color:#94a3b8;left:10px;position:absolute;top:50%;transform:translateY(-50%);font-size:12px;}
.wv-search-input{background:#f8fafc;border:1px solid #cbd5e1;border-radius:8px;color:#1e293b;font-size:12px;font-weight:600;height:34px;padding:6px 10px 6px 28px;transition:all .18s;width:220px;}
.wv-search-input:focus{background:#fff;border-color:#ff0a46;box-shadow:0 0 0 3px rgba(255,10,70,.12);outline:0;}
.wv-prop-select{background:#f8fafc;border:1px solid #cbd5e1;border-radius:8px;color:#1e293b;font-size:12px;font-weight:700;height:34px;padding:4px 10px;width:auto;min-width:180px;}
.wv-sort-btn{align-items:center;background:#f8fafc;border:1px solid #cbd5e1;border-radius:8px;color:#334155;cursor:pointer;display:inline-flex;font-size:11px;font-weight:800;gap:6px;height:34px;padding:0 12px;transition:all .18s;}
.wv-sort-btn:hover{background:#e2e8f0;color:#0f172a;}

/* Table Styling - Light Grid Lines */
.wv-table-wrap{overflow-x:auto;-webkit-overflow-scrolling:touch;touch-action:pan-x pan-y;width:100%;}
.wv-pro-table{border-collapse:collapse;margin-bottom:0;width:100%;min-width:780px;border:1px solid #e2e8f0!important;}
.wv-pro-table thead th{background:#f8fafc;border:1px solid #e2e8f0!important;border-bottom:2px solid #cbd5e1!important;color:#475569;font-size:11px;font-weight:900;letter-spacing:0.3px;padding:11px 12px;text-transform:uppercase;white-space:nowrap;}
.wv-pro-table thead th.sortable{cursor:pointer;user-select:none;}
.wv-pro-table thead th.sortable:hover{color:#0f172a;background:#f1f5f9;}
.wv-pro-table tbody td{border:1px solid #e9eef5!important;color:#1e293b;font-size:12px;font-weight:600;padding:11px 12px;vertical-align:middle;}
.wv-pro-table tbody tr:hover{background:#f8fafc;}

/* Column Specific Styling */
.wv-col-seq{color:#64748b;font-weight:700;text-align:center;width:45px;}
.wv-prop-name{color:#0f172a;font-size:13px;font-weight:700;}
.wv-deal-inv{color:#64748b;font-size:11px;}
.wv-remarks-2line{display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;text-overflow:ellipsis;line-height:1.4;max-height:2.8em;font-size:11px;color:#475569;word-break:break-word;}

/* Buttons - Clean Dark Icons, No Background */
.wv-print-btn{align-items:center;justify-content:center;background:transparent!important;border:0!important;border-radius:6px;box-shadow:none!important;color:#1e293b!important;display:inline-flex;font-size:15px;padding:4px 6px;text-decoration:none!important;transition:all .15s ease-in-out;}
.wv-print-btn:hover{color:#ff0a46!important;transform:scale(1.15);}
.wv-slip-btn{align-items:center;justify-content:center;background:transparent!important;border:0!important;box-shadow:none!important;color:#334155!important;display:inline-flex;font-size:15px;padding:4px 6px;text-decoration:none!important;transition:all .15s ease-in-out;}
.wv-slip-btn:hover{color:#0284c7!important;transform:scale(1.15);}

/* Summary Footer */
.wv-summary-row td{background:#f8fafc;border:1px solid #e2e8f0!important;border-top:2px solid #cbd5e1!important;font-size:12px;color:#0f172a;}

/* Empty State */
.wv-empty-box{padding:40px 20px;text-align:center;color:#64748b;}
.wv-empty-box i{font-size:36px;color:#cbd5e1;display:block;margin-bottom:10px;}

@media (max-width: 991px){
    .wv-mypayments-stats{grid-template-columns:repeat(2,minmax(0,1fr));}
    .wv-table-toolbar{display:block;}
    .wv-table-toolbar-right{margin-top:10px;justify-content:flex-start;}
    .wv-search-input{width:100%;}
    .wv-prop-select{width:100%;}
}
@media (max-width: 767px){
    .wv-mypayments-hero{padding:16px;}
    .wv-mypayments-hero-inner{display:block;}
    .wv-mypayments-hero-actions{justify-content:flex-start;margin-top:12px;}
    .wv-mypayments-hero-actions .btn{width:100%;}
    .wv-mypayments-stats{grid-template-columns:1fr;}
    .wv-pro-table thead th, .wv-pro-table tbody td{padding:9px 10px;}
}
</style>

<div class="wv-mypayments-page">
    <?php echo $this->Session->flash(); ?>

    <!-- Hero Header -->
    <div class="wv-mypayments-hero no-print">
        <div class="wv-mypayments-hero-inner">
            <div>
                <div class="wv-mypayments-kicker"><i class="fa fa-shield"></i> <?php echo __('Client Financial Portal'); ?></div>
                <h1><?php echo __('My Payment Receipts'); ?></h1>
                <p><?php echo __('Complete verified transaction history of all payment receipts and installments across your property deals, sorted by paid date.'); ?></p>
            </div>
            <div class="wv-mypayments-hero-actions">
                <?php echo $this->Html->link('<i class="fa fa-home"></i> ' . __('My Deals'), array('plugin' => false, 'admin' => false, 'controller' => 'MyDeals', 'action' => 'index'), array('class' => 'btn btn-deals', 'escape' => false)); ?>
                <?php echo $this->Html->link('<i class="fa fa-clock-o"></i> ' . __('Overdue Schedule'), array('plugin' => false, 'admin' => false, 'controller' => 'MyInvpastdues', 'action' => 'index'), array('class' => 'btn btn-overdue', 'escape' => false)); ?>
            </div>
        </div>
    </div>

    <!-- Overall Statistics KPIs -->
    <?php 
    $totPaid = isset($overallStats['total_paid']) ? (float)$overallStats['total_paid'] : 0;
    $recCount = isset($overallStats['receipts_count']) ? (int)$overallStats['receipts_count'] : 0;
    $dealsCount = isset($overallStats['deals_count']) ? (int)$overallStats['deals_count'] : 0;
    $totDue = isset($overallStats['total_due']) ? (float)$overallStats['total_due'] : 0;
    $paymentsList = !empty($allPaymentsList) ? $allPaymentsList : array();
    ?>
    <div class="wv-mypayments-stats no-print">
        <div class="wv-mypayments-stat stat-paid">
            <span><?php echo __('Total Paid'); ?></span>
            <strong><?php echo $currency . $this->Number->format($totPaid); ?></strong>
            <small><?php echo __('Across all properties'); ?></small>
        </div>
        <div class="wv-mypayments-stat">
            <span><?php echo __('Verified Receipts'); ?></span>
            <strong><?php echo (int)$recCount; ?></strong>
            <small><?php echo __('Confirmed payment slips'); ?></small>
        </div>
        <div class="wv-mypayments-stat stat-deals">
            <span><?php echo __('Active Deals'); ?></span>
            <strong><?php echo (int)$dealsCount; ?></strong>
            <small><?php echo __('Confirmed properties'); ?></small>
        </div>
        <div class="wv-mypayments-stat stat-balance">
            <span><?php echo __('Total Balance Due'); ?></span>
            <strong><?php echo $currency . $this->Number->format($totDue); ?></strong>
            <small><?php echo __('Remaining installment total'); ?></small>
        </div>
    </div>

    <!-- Single Professional Payment Receipts Table Card -->
    <div class="wv-payments-card">
        <!-- Toolbar with Search & Controls -->
        <div class="wv-table-toolbar no-print">
            <div class="wv-table-toolbar-left">
                <h3 class="wv-table-title"><i class="fa fa-table" style="color:#ff0a46;margin-right:4px;"></i> <?php echo __('Payment Records'); ?></h3>
                <span class="wv-table-badge" id="wvVisibleCountBadge"><?php echo count($paymentsList); ?> <?php echo __('Receipts'); ?></span>
            </div>
            <div class="wv-table-toolbar-right">
                <div class="wv-search-input-wrap">
                    <i class="fa fa-search"></i>
                    <input type="text" id="wvPaymentSearch" class="wv-search-input" placeholder="<?php echo __('Search receipts, dates, deals...'); ?>" onkeyup="wvFilterTable();">
                </div>
                <?php if(!empty($clientDeals) && count($clientDeals) > 1){ ?>
                    <select id="wvPropertyFilter" class="wv-prop-select" onchange="wvFilterTable();">
                        <option value="all"><?php echo __('All Properties'); ?> (<?php echo count($clientDeals); ?>)</option>
                        <?php foreach($clientDeals as $cd): 
                            $cId = (int)$cd['Deal']['id'];
                            $cName = !empty($cd['Property']['name']) ? $cd['Property']['name'] : __('Deal');
                            $cInv = !empty($cd['Deal']['invoice_no']) ? $cd['Deal']['invoice_no'] : ('#' . $cId);
                            $isSelected = (!empty($selectedDealId) && $selectedDealId === $cId);
                        ?>
                            <option value="<?php echo $cId; ?>" <?php echo $isSelected ? 'selected' : ''; ?>>
                                <?php echo h($cName . ' (' . $cInv . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php } ?>
                <button type="button" class="wv-sort-btn" id="wvSortBtn" onclick="wvToggleSort();" title="<?php echo __('Toggle Date Sort Order'); ?>">
                    <i class="fa fa-sort-amount-desc" id="wvSortIcon"></i> <span id="wvSortText"><?php echo __('Newest First'); ?></span>
                </button>
            </div>
        </div>

        <?php if(empty($paymentsList)){ ?>
            <div class="wv-empty-box">
                <i class="fa fa-file-text-o"></i>
                <h4 style="font-size:16px;font-weight:900;color:#0f172a;margin:0 0 6px;"><?php echo __('No Payment Records Found'); ?></h4>
                <p style="margin:0;font-size:13px;"><?php echo __('There are currently no verified payment receipts recorded for your account.'); ?></p>
            </div>
        <?php } else { ?>
            <div class="wv-table-wrap">
                <table class="table wv-pro-table table-bordered" id="wvPaymentsTable">
                    <thead>
                        <tr>
                            <th class="wv-col-seq"><?php echo __('#'); ?></th>
                            <th><?php echo __('Property / Deal'); ?></th>
                            <th><?php echo __('Receipt No.'); ?></th>
                            <th class="sortable" onclick="wvToggleSort();" title="<?php echo __('Click to sort by date'); ?>">
                                <?php echo __('Paid Date'); ?> <i class="fa fa-sort" id="wvThSortIcon" style="color:#94a3b8;margin-left:3px;"></i>
                            </th>
                            <th><?php echo __('Payment / Installment'); ?></th>
                            <th style="text-align:right;"><?php echo __('Amount Paid'); ?></th>
                            <th><?php echo __('Payment Method'); ?></th>
                            <th><?php echo __('Transaction Ref / Remarks'); ?></th>
                            <th style="text-align:center;width:65px;"><?php echo __('Action'); ?></th>
                        </tr>
                    </thead>
                    <tbody id="wvTableBody">
                        <?php 
                        $serialNo = 1;
                        $totalTableAmount = 0;
                        $totalTableLateFees = 0;
                        foreach($paymentsList as $rcp): 
                            $id = $rcp['id_list'];
                            $primaryId = $rcp['first_id'];
                            $dealId = (int)$rcp['deal_id'];
                            $slipFile = $rcp['slip_file'];
                            $paidAmount = (float)$rcp['paid_amount'];
                            $lateFees = (float)$rcp['late_fees'];
                            $totalTableAmount += $paidAmount;
                            $totalTableLateFees += $lateFees;
                            $paymentNames = !empty($rcp['payment_names']) ? implode(', ', $rcp['payment_names']) : (!empty($rcp['first_row']['PlansPayment']['name']) ? $rcp['first_row']['PlansPayment']['name'] : '-');
                            $paidDateLabel = !empty($rcp['payment_date']) ? $this->Time->format($sysDay . $dateSep . $sysMonth . $dateSep . $sysYear, $rcp['payment_date']) : '-';
                            $rawDate = !empty($rcp['payment_date']) ? $rcp['payment_date'] : '';
                            $propName = !empty($rcp['property_name']) ? $rcp['property_name'] : __('Property');
                            $unitName = !empty($rcp['unit_name']) ? $rcp['unit_name'] : '';
                            $dealInv = !empty($rcp['deal_invoice']) ? $rcp['deal_invoice'] : ('#' . $dealId);
                        ?>
                            <tr class="wv-payment-row" data-deal-id="<?php echo $dealId; ?>" data-date="<?php echo h($rawDate); ?>" data-amount="<?php echo $paidAmount; ?>">
                                <td class="wv-col-seq wv-seq-cell"><?php echo $serialNo++; ?></td>
                                <td>
                                    <div class="wv-prop-name"><?php echo h($propName); ?><?php if($unitName !== ''){ echo ' &bull; ' . h($unitName); } ?></div>
                                    <span class="wv-deal-inv"><?php echo h($dealInv); ?></span>
                                </td>
                                <td>
                                    <?php echo h($rcp['receipt_no']); ?>
                                </td>
                                <td>
                                    <?php echo h($paidDateLabel); ?>
                                </td>
                                <td>
                                    <?php echo h($paymentNames); ?>
                                </td>
                                <td style="text-align:right;">
                                    <?php echo $currency . $this->Number->format($paidAmount); ?>
                                    <?php if($lateFees > 0){ ?>
                                        <div style="font-size:10px;color:#dc2626;margin-top:2px;">+ <?php echo $currency . $this->Number->format($lateFees); ?> <?php echo __('Late fee'); ?></div>
                                    <?php } ?>
                                </td>
                                <td>
                                    <?php echo h($rcp['payment_type']); ?>
                                </td>
                                <td>
                                    <?php if(!empty($rcp['remarks'])){ ?>
                                        <div class="wv-remarks-2line" title="<?php echo h($rcp['remarks']); ?>"><?php echo h($rcp['remarks']); ?></div>
                                    <?php } else { ?>
                                        <span style="color:#94a3b8;">-</span>
                                    <?php } ?>
                                </td>
                                <td style="text-align:center;">
                                    <?php echo $this->Html->link('<i class="fa fa-print"></i>', array('plugin' => false, 'admin' => false, 'controller' => 'MyPayments', 'action' => 'printreceipt', $id), array('target' => '_blank', 'rel' => 'noopener', 'escape' => false, 'class' => 'wv-print-btn', 'title' => __('Print Receipt'))); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <tr id="wvNoMatchRow" style="display:none;">
                            <td colspan="9" style="text-align:center;padding:30px;color:#64748b;">
                                <i class="fa fa-search" style="font-size:24px;color:#cbd5e1;display:block;margin-bottom:6px;"></i>
                                <?php echo __('No payment records match your search or filter criteria.'); ?>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr class="wv-summary-row">
                            <td colspan="5" align="left">
                                <strong style="color:#0f172a;"><?php echo __('Total Verified Payments:'); ?></strong>
                            </td>
                            <td style="text-align:right;">
                                <strong style="color:#059669;font-size:14px;" id="wvSummaryTotal">
                                    <?php echo $currency . $this->Number->format($totalTableAmount); ?>
                                </strong>
                            </td>
                            <td colspan="3">
                                <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
                                    <?php if($totalTableLateFees > 0){ ?>
                                        <span style="color:#dc2626;font-weight:700;font-size:11px;">
                                            <i class="fa fa-exclamation-triangle"></i> <?php echo __('Late Fees:'); ?> <?php echo $currency . $this->Number->format($totalTableLateFees); ?>
                                        </span>
                                    <?php } else { echo '<span></span>'; } ?>
                                    <span style="color:#64748b;font-weight:800;font-size:11px;" id="wvSummaryCount">
                                        <?php echo count($paymentsList); ?> <?php echo __('Receipts Processed'); ?>
                                    </span>
                                </div>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        <?php } ?>
    </div>
</div>

<script type="text/javascript">
var currentSortOrder = 'desc';

function wvFilterTable() {
    var searchInput = document.getElementById('wvPaymentSearch');
    var query = searchInput ? searchInput.value.toLowerCase().trim() : '';

    var propSelect = document.getElementById('wvPropertyFilter');
    var selectedDeal = propSelect ? propSelect.value : 'all';

    var rows = document.querySelectorAll('.wv-payment-row');
    var visibleCount = 0;
    var runningTotal = 0;
    var seq = 1;

    for (var i = 0; i < rows.length; i++) {
        var row = rows[i];
        var dealId = row.getAttribute('data-deal-id');
        var rowText = row.innerText.toLowerCase();
        var rowAmount = parseFloat(row.getAttribute('data-amount')) || 0;

        var matchesDeal = (selectedDeal === 'all' || dealId === selectedDeal);
        var matchesQuery = (query === '' || rowText.indexOf(query) !== -1);

        if (matchesDeal && matchesQuery) {
            row.style.display = '';
            var seqCell = row.querySelector('.wv-seq-cell');
            if (seqCell) {
                seqCell.textContent = seq++;
            }
            visibleCount++;
            runningTotal += rowAmount;
        } else {
            row.style.display = 'none';
        }
    }

    var noMatchRow = document.getElementById('wvNoMatchRow');
    if (noMatchRow) {
        noMatchRow.style.display = (visibleCount === 0 && rows.length > 0) ? '' : 'none';
    }

    var badge = document.getElementById('wvVisibleCountBadge');
    if (badge) {
        badge.textContent = visibleCount + ' <?php echo __("Receipts"); ?>';
    }

    var countSpan = document.getElementById('wvSummaryCount');
    if (countSpan) {
        countSpan.textContent = visibleCount + ' <?php echo __("Receipts Processed"); ?>';
    }
}

function wvToggleSort() {
    var tbody = document.getElementById('wvTableBody');
    if (!tbody) return;

    var rows = Array.prototype.slice.call(tbody.querySelectorAll('.wv-payment-row'));
    currentSortOrder = (currentSortOrder === 'desc') ? 'asc' : 'desc';

    rows.sort(function(a, b) {
        var dateA = a.getAttribute('data-date') || '';
        var dateB = b.getAttribute('data-date') || '';
        if (dateA === dateB) return 0;
        if (currentSortOrder === 'desc') {
            return (dateA > dateB) ? -1 : 1;
        } else {
            return (dateA < dateB) ? -1 : 1;
        }
    });

    for (var i = 0; i < rows.length; i++) {
        tbody.appendChild(rows[i]);
    }

    var noMatchRow = document.getElementById('wvNoMatchRow');
    if (noMatchRow) {
        tbody.appendChild(noMatchRow);
    }

    var sortText = document.getElementById('wvSortText');
    var sortIcon = document.getElementById('wvSortIcon');
    var thSortIcon = document.getElementById('wvThSortIcon');

    if (currentSortOrder === 'desc') {
        if (sortText) sortText.textContent = '<?php echo __("Newest First"); ?>';
        if (sortIcon) sortIcon.className = 'fa fa-sort-amount-desc';
        if (thSortIcon) thSortIcon.className = 'fa fa-sort-desc';
    } else {
        if (sortText) sortText.textContent = '<?php echo __("Oldest First"); ?>';
        if (sortIcon) sortIcon.className = 'fa fa-sort-amount-asc';
        if (thSortIcon) thSortIcon.className = 'fa fa-sort-asc';
    }

    wvFilterTable();
}

document.addEventListener('DOMContentLoaded', function() {
    wvFilterTable();
});
</script>
