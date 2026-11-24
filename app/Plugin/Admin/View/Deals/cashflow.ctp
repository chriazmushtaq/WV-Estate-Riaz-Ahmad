<?php
$this->set('title_for_layout', __('Executive Cash Flow & Inflow Forecast'));
$kpis = isset($kpis) ? $kpis : array('next_30_days'=>0,'days_31_60'=>0,'days_61_90'=>0,'beyond_90'=>0,'overdue_total'=>0,'grand_total'=>0,'total_active_deals'=>0);
$monthlyForecast = isset($monthlyForecast) ? (array)$monthlyForecast : array();
$projectBreakdown = isset($projectBreakdown) ? (array)$projectBreakdown : array();
$upcomingInstallments = isset($upcomingInstallments) ? (array)$upcomingInstallments : array();
$projectsList = isset($projectsList) ? (array)$projectsList : array();
$selectedProjectId = isset($selectedProjectId) ? (int)$selectedProjectId : 0;
$currency = isset($currency) ? $currency : 'Rs. ';

$maxMonthlyVal = 1;
foreach($monthlyForecast as $monthKey => $monthAmt) {
    if($monthAmt > $maxMonthlyVal) {
        $maxMonthlyVal = $monthAmt;
    }
}
?>
<style>
.cashflow-shell {
    color: #1e293b;
}
.cashflow-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    box-shadow: 0 4px 16px rgba(15, 23, 42, 0.05);
    margin-bottom: 22px;
    overflow: hidden;
}
.cashflow-card-header {
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    padding: 14px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.cashflow-card-header h3 {
    margin: 0;
    font-size: 16px;
    font-weight: 700;
    color: #0f172a;
}
.cashflow-kpi-grid {
    display: grid;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    gap: 14px;
    margin-bottom: 22px;
}
.cashflow-kpi-card {
    background: #ffffff;
    border-radius: 10px;
    border: 1px solid #e2e8f0;
    padding: 16px;
    position: relative;
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.04);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.cashflow-kpi-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(15, 23, 42, 0.08);
}
.cashflow-kpi-card.kpi-30 { border-left: 5px solid #10b981; }
.cashflow-kpi-card.kpi-60 { border-left: 5px solid #0284c7; }
.cashflow-kpi-card.kpi-90 { border-left: 5px solid #6366f1; }
.cashflow-kpi-card.kpi-beyond { border-left: 5px solid #8b5cf6; }
.cashflow-kpi-card.kpi-overdue { border-left: 5px solid #ef4444; }

.kpi-label {
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 6px;
    color: #64748b;
}
.kpi-value {
    font-size: 20px;
    font-weight: 800;
    color: #0f172a;
    line-height: 1.2;
}
.kpi-sub {
    font-size: 11px;
    color: #94a3b8;
    margin-top: 6px;
    font-weight: 600;
}

/* Timeline bar chart */
.chart-container {
    padding: 20px;
    background: #ffffff;
}
.chart-bars-wrap {
    display: flex;
    align-items: flex-end;
    gap: 12px;
    height: 220px;
    padding-top: 20px;
    border-bottom: 2px solid #cbd5e1;
    overflow-x: auto;
}
.chart-col {
    flex: 1;
    min-width: 65px;
    display: flex;
    flex-direction: column;
    align-items: center;
    height: 100%;
    justify-content: flex-end;
}
.chart-bar-outer {
    width: 100%;
    max-width: 44px;
    background: #f1f5f9;
    border-radius: 6px 6px 0 0;
    position: relative;
    display: flex;
    align-items: flex-end;
    justify-content: center;
    height: 100%;
}
.chart-bar-inner {
    width: 100%;
    background: linear-gradient(180deg, #3b82f6 0%, #1d4ed8 100%);
    border-radius: 6px 6px 0 0;
    transition: height 0.5s ease;
    position: relative;
}
.chart-bar-inner:hover {
    background: linear-gradient(180deg, #60a5fa 0%, #2563eb 100%);
}
.chart-bar-amount {
    font-size: 10px;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 4px;
    white-space: nowrap;
    text-align: center;
}
.chart-bar-month {
    font-size: 11px;
    font-weight: 700;
    color: #475569;
    margin-top: 8px;
    text-align: center;
}

/* Data tables */
.cashflow-table {
    margin: 0;
    width: 100%;
}
.cashflow-table th {
    background: #f8fafc;
    color: #334155;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    border-bottom: 2px solid #e2e8f0 !important;
    padding: 10px 14px;
}
.cashflow-table td {
    vertical-align: middle;
    padding: 11px 14px;
    color: #334155;
    border-bottom: 1px solid #f1f5f9;
}
.cashflow-table tr:hover td {
    background: #f8fafc;
}

@media(max-width: 1200px) {
    .cashflow-kpi-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}
@media(max-width: 768px) {
    .cashflow-kpi-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="cashflow-shell">
    <div class="page-title">
        <div class="title-env" style="display:flex;align-items:center;justify-content:space-between;width:100%;">
            <div>
                <h1 class="title" style="margin:0;"><i class="fa fa-line-chart" style="color:#0284c7;"></i> <?php echo __('Executive Cash Flow Forecast');?></h1>
                <p class="description" style="margin-top:4px;color:#64748b;"><?php echo __('Forward revenue pipeline & scheduled collections for active property deals');?></p>
            </div>
            <div class="btn-group">
                <?php echo $this->Html->link('<i class="fa fa-arrow-left"></i>&nbsp;'.__('Back to Deals'), array('controller'=>'Deals', 'action'=>'index'), array('escape'=>false, 'class'=>'btn btn-default btn-sm')); ?>
                <button type="button" class="btn btn-primary btn-sm" onclick="window.print();"><i class="fa fa-print"></i>&nbsp;<?php echo __('Print Report');?></button>
            </div>
        </div>
    </div>

    <?php echo $this->Session->flash();?>

    <!-- Project Filter Bar -->
    <div class="cashflow-card" style="padding:12px 18px;margin-bottom:18px;">
        <?php echo $this->Form->create(false, array('type'=>'get', 'url'=>array('controller'=>'Deals', 'action'=>'cashflow'), 'class'=>'form-inline', 'id'=>'project-filter-form')); ?>
        <div class="row" style="align-items:center;">
            <div class="col-md-4 col-sm-6">
                <label for="project_id" style="margin-right:8px;font-weight:700;color:#334155;"><?php echo __('Filter by Project / Agency:');?></label>
                <?php echo $this->Form->select('project_id', $projectsList, array('empty'=>__('All Projects / Agencies'), 'value'=>$selectedProjectId, 'class'=>'form-control input-sm', 'style'=>'width:220px;display:inline-block;', 'onchange'=>'jQuery("#project-filter-form").submit();')); ?>
            </div>
            <div class="col-md-8 col-sm-6 text-right" style="padding-top:4px;">
                <span class="text-muted" style="font-size:12px;"><strong><?php echo __('Active Deals Evaluated:');?></strong> <span class="badge" style="background:#0284c7;font-size:12px;"><?php echo (int)$kpis['total_active_deals'];?></span></span>
                <span class="text-muted" style="font-size:12px;margin-left:14px;"><strong><?php echo __('Total Receivables:');?></strong> <span style="font-weight:800;color:#0f172a;"><?php echo $currency.number_format($kpis['grand_total'], 2);?></span></span>
            </div>
        </div>
        <?php echo $this->Form->end(); ?>
    </div>

    <!-- 5 Executive KPI Cards -->
    <div class="cashflow-kpi-grid">
        <div class="cashflow-kpi-card kpi-30">
            <div class="kpi-label"><i class="fa fa-calendar-check-o text-success"></i> <?php echo __('Next 30 Days Inflow');?></div>
            <div class="kpi-value text-success"><?php echo $currency.number_format($kpis['next_30_days'], 0);?></div>
            <div class="kpi-sub"><?php echo __('Scheduled within next month');?></div>
        </div>
        <div class="cashflow-kpi-card kpi-60">
            <div class="kpi-label"><i class="fa fa-calendar text-info"></i> <?php echo __('31 - 60 Days Inflow');?></div>
            <div class="kpi-value text-info"><?php echo $currency.number_format($kpis['days_31_60'], 0);?></div>
            <div class="kpi-sub"><?php echo __('Scheduled in 2nd month');?></div>
        </div>
        <div class="cashflow-kpi-card kpi-90">
            <div class="kpi-label"><i class="fa fa-calendar-o" style="color:#6366f1;"></i> <?php echo __('61 - 90 Days Inflow');?></div>
            <div class="kpi-value" style="color:#6366f1;"><?php echo $currency.number_format($kpis['days_61_90'], 0);?></div>
            <div class="kpi-sub"><?php echo __('Scheduled in 3rd month');?></div>
        </div>
        <div class="cashflow-kpi-card kpi-beyond">
            <div class="kpi-label"><i class="fa fa-hourglass-end" style="color:#8b5cf6;"></i> <?php echo __('Beyond 90 Days');?></div>
            <div class="kpi-value" style="color:#8b5cf6;"><?php echo $currency.number_format($kpis['beyond_90'], 0);?></div>
            <div class="kpi-sub"><?php echo __('Long-term installment pipeline');?></div>
        </div>
        <div class="cashflow-kpi-card kpi-overdue">
            <div class="kpi-label"><i class="fa fa-exclamation-triangle text-danger"></i> <?php echo __('Overdue Pipeline');?></div>
            <div class="kpi-value text-danger"><?php echo $currency.number_format($kpis['overdue_total'], 0);?></div>
            <div class="kpi-sub"><?php echo __('Immediate recovery priority');?></div>
        </div>
    </div>

    <!-- Monthly Forecast Timeline Chart -->
    <div class="cashflow-card">
        <div class="cashflow-card-header">
            <h3><i class="fa fa-bar-chart" style="color:#3b82f6;"></i> <?php echo __('Projected Cash Inflow Timeline (Next 12 Months)');?></h3>
            <span class="badge" style="background:#e2e8f0;color:#334155;font-weight:700;"><?php echo count($monthlyForecast); ?> <?php echo __('Months Projected');?></span>
        </div>
        <div class="chart-container">
            <?php if(empty($monthlyForecast)){ ?>
                <div class="alert alert-info text-center" style="margin:20px 0;"><?php echo __('No upcoming scheduled installments found for the selected project filter.');?></div>
            <?php } else { ?>
                <div class="chart-bars-wrap">
                    <?php
                    $displayMonths = array_slice($monthlyForecast, 0, 12, true);
                    foreach($displayMonths as $monthStr => $amount):
                        $pct = $maxMonthlyVal > 0 ? max(6, round(($amount / $maxMonthlyVal) * 100)) : 6;
                        $formattedMonth = date('M Y', strtotime($monthStr . '-01'));
                    ?>
                        <div class="chart-col">
                            <div class="chart-bar-amount"><?php echo $currency.number_format($amount / 1000, 0).'k';?></div>
                            <div class="chart-bar-outer">
                                <div class="chart-bar-inner" style="height: <?php echo $pct;?>%;" title="<?php echo $formattedMonth.': '.$currency.number_format($amount, 2);?>"></div>
                            </div>
                            <div class="chart-bar-month"><?php echo h($formattedMonth);?></div>
                        </div>
                    <?php endforeach;?>
                </div>
                <div class="text-right" style="margin-top:8px;color:#64748b;font-size:11px;">
                    <i class="fa fa-info-circle"></i> <?php echo __('Amounts shown based on active installment payment plans matched via FIFO recovery.');?>
                </div>
            <?php } ?>
        </div>
    </div>

    <!-- Project Breakdown Table -->
    <div class="cashflow-card">
        <div class="cashflow-card-header">
            <h3><i class="fa fa-building-o" style="color:#0284c7;"></i> <?php echo __('Project-Wise Cash Flow & Receivables Breakdown');?></h3>
        </div>
        <div class="table-responsive">
            <table class="table cashflow-table">
                <thead>
                    <tr>
                        <th><?php echo __('Project / Agency');?></th>
                        <th class="text-center"><?php echo __('Active Deals');?></th>
                        <th class="text-right"><?php echo __('Immediate Overdue');?></th>
                        <th class="text-right"><?php echo __('Next 30 Days');?></th>
                        <th class="text-right"><?php echo __('31 - 60 Days');?></th>
                        <th class="text-right"><?php echo __('61 - 90 Days');?></th>
                        <th class="text-right"><?php echo __('Beyond 90 Days');?></th>
                        <th class="text-right"><?php echo __('Total Receivables');?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($projectBreakdown)){ ?>
                        <tr><td colspan="8" class="text-center text-muted"><?php echo __('No active projects found.');?></td></tr>
                    <?php } else {
                        $totDeals = 0; $totOverdue = 0; $tot30 = 0; $tot60 = 0; $tot90 = 0; $totBeyond = 0; $totGrand = 0;
                        foreach($projectBreakdown as $pId => $pData):
                            $totDeals += $pData['active_deals'];
                            $totOverdue += $pData['overdue'];
                            $tot30 += $pData['next_30'];
                            $tot60 += $pData['days_31_60'];
                            $tot90 += $pData['days_61_90'];
                            $totBeyond += $pData['beyond_90'];
                            $totGrand += $pData['total_receivable'];
                    ?>
                        <tr>
                            <td><strong><?php echo h($pData['name']);?></strong></td>
                            <td class="text-center"><span class="badge" style="background:#f1f5f9;color:#334155;border:1px solid #cbd5e1;"><?php echo (int)$pData['active_deals'];?></span></td>
                            <td class="text-right text-danger font-weight-bold"><?php echo $currency.number_format($pData['overdue'], 2);?></td>
                            <td class="text-right text-success font-weight-bold"><?php echo $currency.number_format($pData['next_30'], 2);?></td>
                            <td class="text-right text-info"><?php echo $currency.number_format($pData['days_31_60'], 2);?></td>
                            <td class="text-right" style="color:#6366f1;"><?php echo $currency.number_format($pData['days_61_90'], 2);?></td>
                            <td class="text-right" style="color:#8b5cf6;"><?php echo $currency.number_format($pData['beyond_90'], 2);?></td>
                            <td class="text-right" style="font-weight:800;color:#0f172a;"><?php echo $currency.number_format($pData['total_receivable'], 2);?></td>
                        </tr>
                    <?php endforeach; ?>
                        <tr style="background:#f1f5f9;font-weight:800;">
                            <td><?php echo __('Total Portfolio Receivables');?></td>
                            <td class="text-center"><?php echo $totDeals;?></td>
                            <td class="text-right text-danger"><?php echo $currency.number_format($totOverdue, 2);?></td>
                            <td class="text-right text-success"><?php echo $currency.number_format($tot30, 2);?></td>
                            <td class="text-right text-info"><?php echo $currency.number_format($tot60, 2);?></td>
                            <td class="text-right" style="color:#6366f1;"><?php echo $currency.number_format($tot90, 2);?></td>
                            <td class="text-right" style="color:#8b5cf6;"><?php echo $currency.number_format($totBeyond, 2);?></td>
                            <td class="text-right" style="color:#0f172a;"><?php echo $currency.number_format($totGrand, 2);?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Upcoming Scheduled Collections Table (Next 60 Days) -->
    <div class="cashflow-card">
        <div class="cashflow-card-header">
            <h3><i class="fa fa-list-alt" style="color:#10b981;"></i> <?php echo __('Upcoming Installment Schedule (Next 60 Days)');?></h3>
            <span class="badge" style="background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0;"><?php echo count($upcomingInstallments);?> <?php echo __('Upcoming Due');?></span>
        </div>
        <div class="table-responsive">
            <table class="table cashflow-table">
                <thead>
                    <tr>
                        <th><?php echo __('Due Date');?></th>
                        <th><?php echo __('Deal Invoice #');?></th>
                        <th><?php echo __('Client Name');?></th>
                        <th><?php echo __('Contact No');?></th>
                        <th><?php echo __('Property / Project');?></th>
                        <th><?php echo __('Installment Title');?></th>
                        <th class="text-right"><?php echo __('Expected Amount');?></th>
                        <th class="text-center"><?php echo __('Action');?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($upcomingInstallments)){ ?>
                        <tr><td colspan="8" class="text-center text-muted"><?php echo __('No installments due in the next 60 days.');?></td></tr>
                    <?php } else {
                        $todayTs = strtotime(date('Y-m-d'));
                        foreach(array_slice($upcomingInstallments, 0, 30) as $inst):
                            $daysLeft = round(($inst['due_ts'] - $todayTs) / 86400);
                    ?>
                        <tr>
                            <td>
                                <strong><?php echo date('d-M-Y', strtotime($inst['due_date']));?></strong>
                                <small class="text-muted" style="display:block;font-size:11px;">
                                    <?php
                                    if($daysLeft == 0) {
                                        echo '<span class="label label-warning">'.__('Due Today').'</span>';
                                    } elseif($daysLeft == 1) {
                                        echo '<span class="label label-info">'.__('Tomorrow').'</span>';
                                    } else {
                                        echo __('In %d days', $daysLeft);
                                    }
                                    ?>
                                </small>
                            </td>
                            <td>
                                <strong><?php echo $this->Html->link(h($inst['invoice_no']), array('controller'=>'deals_payments', 'action'=>'index', $inst['deal_id']), array('title'=>__('View Deal Ledger'))); ?></strong>
                            </td>
                            <td><?php echo h($inst['client_name']);?></td>
                            <td><?php echo !empty($inst['client_contact']) ? h($inst['client_contact']) : '-';?></td>
                            <td><?php echo h($inst['property_name']).' <small class="text-muted">('.h($inst['project_name']).')</small>';?></td>
                            <td><span class="badge" style="background:#e0f2fe;color:#0369a1;"><?php echo h($inst['installment_name']);?></span></td>
                            <td class="text-right font-weight-bold" style="font-size:13px;color:#0f172a;"><?php echo $currency.number_format($inst['amount'], 2);?></td>
                            <td class="text-center">
                                <?php echo $this->Html->link('<i class="fa fa-briefcase"></i>&nbsp;'.__('Ledger'), array('controller'=>'deals_payments', 'action'=>'index', $inst['deal_id']), array('escape'=>false, 'class'=>'btn btn-xs btn-default')); ?>
                            </td>
                        </tr>
                    <?php endforeach; } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
