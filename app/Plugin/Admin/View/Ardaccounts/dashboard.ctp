<?php
$dashboardFilter = (isset($dashboardFilter) && is_array($dashboardFilter)) ? $dashboardFilter : array();
$dashboardChart = (isset($dashboardChart) && is_array($dashboardChart)) ? $dashboardChart : array();
$filterPreset = isset($dashboardFilter['preset']) ? (string)$dashboardFilter['preset'] : 'monthly';
$filterStart = isset($dashboardFilter['start']) ? (string)$dashboardFilter['start'] : '';
$filterEnd = isset($dashboardFilter['end']) ? (string)$dashboardFilter['end'] : '';
$allowedPresets = array('daily','weekly','monthly','yearly','custom','lifetime');
if(!in_array($filterPreset, $allowedPresets))
{
    $filterPreset = 'monthly';
}
$chartIncomeAmount = isset($dashboardChart['income_amount']) ? (float)$dashboardChart['income_amount'] : 0.0;
$chartExpenseAmount = isset($dashboardChart['expense_amount']) ? (float)$dashboardChart['expense_amount'] : 0.0;
$chartIncomeCount = isset($dashboardChart['income_count']) ? (int)$dashboardChart['income_count'] : 0;
$chartExpenseCount = isset($dashboardChart['expense_count']) ? (int)$dashboardChart['expense_count'] : 0;
$chartTransferCount = isset($dashboardChart['transfer_count']) ? (int)$dashboardChart['transfer_count'] : 0;
$totalFlowAmount = $chartIncomeAmount + $chartExpenseAmount;
$incomeSharePercent = ($totalFlowAmount > 0) ? round(($chartIncomeAmount / $totalFlowAmount) * 100, 2) : 0.00;
$expenseSharePercent = ($totalFlowAmount > 0) ? round(($chartExpenseAmount / $totalFlowAmount) * 100, 2) : 0.00;
$netMarginPercent = ($chartIncomeAmount > 0) ? round((($chartIncomeAmount - $chartExpenseAmount) / $chartIncomeAmount) * 100, 2) : 0.00;
$currencyText = trim(strip_tags(isset($currency) ? (string)$currency : 'Rs'));
if($currencyText === '')
{
    $currencyText = 'Rs';
}
$dashboardQuery = array(
    'preset' => $filterPreset,
    'start' => $filterStart,
    'end' => $filterEnd
);
if(isset($this->request->query['project_id']) && trim((string)$this->request->query['project_id']) !== '')
{
    $dashboardQuery['project_id'] = (int)$this->request->query['project_id'];
}
?>
<style type="text/css">
.ard-dashboard-panel .panel-body{background:#eef3fa;}
.ard-heading-actions{display:flex;align-items:center;justify-content:space-between;gap:8px;flex-wrap:wrap;}
.ard-dashboard-filter{
    display:flex;
    align-items:flex-end;
    gap:10px;
    flex-wrap:wrap;
    overflow:visible;
    margin:8px 0 16px;
    padding:10px 12px;
    border:1px solid #d7e2f1;
    border-radius:10px;
    background:#f6f9ff;
}
.ard-filter-item{display:flex;flex-direction:column;gap:4px;min-width:160px;flex:1 1 160px;}
.ard-filter-item label{font-size:11px;font-weight:700;color:#577194;letter-spacing:.04em;text-transform:uppercase;margin:0;}
.ard-dashboard-filter .form-control{height:40px;width:100%;}
.ard-filter-submit{flex:0 0 auto;min-width:110px;}
.ard-filter-submit .btn{height:40px;width:100%;}
.ard-col-description{max-width:360px;white-space:normal;word-break:break-word;}
.ard-percentage-bars{display:flex;flex-direction:column;gap:12px;}
.ard-percent-row{display:flex;justify-content:space-between;align-items:center;gap:10px;font-size:12px;color:#355070;font-weight:600;}
.ard-percent-row .label{padding:0;font-size:12px;font-weight:700;letter-spacing:.03em;text-transform:uppercase;background:none;color:#355070;}
.ard-percent-row .amount{color:#173a62;font-weight:700;}
.ard-percent-row .pct{color:#0f5f9a;font-weight:700;}
.ard-percent-progress{height:14px;background:#e5edf8;border-radius:999px;overflow:hidden;box-shadow:inset 0 1px 2px rgba(0,0,0,.06);}
.ard-percent-progress > span{display:block;height:100%;border-radius:999px;}
.ard-percent-progress .income{background:linear-gradient(90deg,#2aa866,#3bcf87);}
.ard-percent-progress .expense{background:linear-gradient(90deg,#d9534f,#f27d7a);}
.is-pdf-export .no-pdf{display:none !important;}
.ard-dashboard-grid{margin:0 -6px;}
.ard-dashboard-stat{padding:0 6px;margin-bottom:12px;}
.ard-stat-card{
    border:1px solid #d4e0ef;
    border-radius:12px;
    background:#fff;
    padding:12px 14px;
    min-height:96px;
    box-shadow:0 2px 8px rgba(25,60,115,.06);
}
.ard-stat-link{display:block;color:inherit;text-decoration:none;}
.ard-stat-link:hover,.ard-stat-link:focus{color:inherit;text-decoration:none;}
.ard-stat-link .ard-stat-card{transition:all .16s ease;cursor:pointer;}
.ard-stat-link:hover .ard-stat-card,.ard-stat-link:focus .ard-stat-card{border-color:#9db4d6;box-shadow:0 4px 12px rgba(25,60,115,.12);transform:translateY(-1px);}
.ard-stat-label{font-size:11px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:#567092;margin-bottom:5px;}
.ard-stat-value{font-size:24px;font-weight:700;color:#173a62;line-height:1.2;}
.ard-stat-note{font-size:11px;color:#6f84a3;margin-top:4px;}
.ard-stat-card.is-blue{background:linear-gradient(135deg,#eaf3ff,#f8fbff);}
.ard-stat-card.is-green{background:linear-gradient(135deg,#e9f8ef,#f8fffb);}
.ard-stat-card.is-red{background:linear-gradient(135deg,#fdeeee,#fff9f9);}
.ard-stat-card.is-amber{background:linear-gradient(135deg,#fff5e8,#fffcf8);}
.ard-section-card{
    border:1px solid #d4e0ef;
    border-radius:12px;
    background:#fff;
    box-shadow:0 2px 8px rgba(25,60,115,.06);
    margin-bottom:14px;
    overflow:hidden;
}
.ard-section-head{
    padding:12px 14px;
    border-bottom:1px solid #e4ecf7;
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:8px;
    flex-wrap:wrap;
}
.ard-section-head h4{margin:0;color:#173a62;font-weight:700;}
.ard-section-body{padding:12px 14px;}
.ard-chart-wrap{position:relative;height:320px;}
.ard-actions .btn{margin-right:4px;}
.ard-actions .btn:last-child{margin-right:0;}
@media (max-width:991px){
    .ard-heading-actions{align-items:stretch;}
    .ard-filter-item{min-width:100%;flex:1 1 100%;}
    .ard-filter-submit{min-width:100%;flex:1 1 100%;}
    .ard-stat-value{font-size:21px;}
}
@media print{
    html,body{background:#fff !important;}
    .navbar,#sidebar-main,.sidebar,.navigation,.navigation-header,.page-title,.modal,.modal-backdrop,.no-print{display:none !important;}
    .main-content,.page-container,.panel,.panel-body{margin:0 !important;padding:0 !important;background:#fff !important;box-shadow:none !important;border:none !important;}
    .ard-section-card,.ard-stat-card{break-inside:avoid-page;border:1px solid #d7dfe9 !important;box-shadow:none !important;}
}
</style>

<div class="page-title">
    <div class="title-env">
        <h1 class="title"><?php echo __('Accounts Dashboard');?></h1>
        <p class="description"><?php echo __('Business performance overview');?></p>
    </div>
</div>

<div class="panel ard-dashboard-panel">
    <div class="panel-heading no-print">
        <div class="ard-heading-actions">
            <div class="btn-group">
            <?php
            echo $this->Html->link('<span class="fa fa-plus"></span> '.__('Add Income'),array('controller'=>'Ardaccounts','action'=>'transactions',$currentProjectId,'?'=>array('kind'=>'income')),array('escape'=>false,'class'=>'btn btn-success'));
            echo $this->Html->link('<span class="fa fa-minus"></span> '.__('Add Expense'),array('controller'=>'Ardaccounts','action'=>'transactions',$currentProjectId,'?'=>array('kind'=>'expense')),array('escape'=>false,'class'=>'btn btn-danger'));
            echo $this->Html->link('<span class="fa fa-exchange"></span> '.__('Journal Voucher'),array('controller'=>'Ardaccounts','action'=>'transfer',$currentProjectId),array('escape'=>false,'class'=>'btn btn-primary'));
            echo $this->Html->link('<span class="fa fa-search"></span> '.__('Check Voucher'),array('controller'=>'Ardaccounts','action'=>'transactions',$currentProjectId),array('escape'=>false,'class'=>'btn btn-info'));
            echo $this->Html->link('<span class="fa fa-bar-chart"></span> '.__('Reports'),array('controller'=>'Ardaccounts','action'=>'reports',$currentProjectId),array('escape'=>false,'class'=>'btn btn-primary'));
            echo $this->Html->link('<span class="fa fa-database"></span> '.__('Backups'),array('controller'=>'Ardaccounts','action'=>'backups',$currentProjectId),array('escape'=>false,'class'=>'btn btn-primary'));
            echo $this->Html->link('<span class="fa fa-building"></span> '.__('Construction'),array('controller'=>'Ardaccounts','action'=>'construction',$currentProjectId),array('escape'=>false,'class'=>'btn btn-primary'));
            echo $this->Html->link('<span class="fa fa-users"></span> '.__('Vendors'),array('controller'=>'Ardaccounts','action'=>'vendors',$currentProjectId),array('escape'=>false,'class'=>'btn btn-primary'));
            echo $this->Html->link('<span class="fa fa-cubes"></span> '.__('Stock'),array('controller'=>'Ardaccounts','action'=>'stock',$currentProjectId),array('escape'=>false,'class'=>'btn btn-primary'));
            echo $this->Html->link('<span class="fa fa-building-o"></span> '.__('Construction Projects'),array('controller'=>'Ardaccounts','action'=>'constructionprojects',$currentProjectId),array('escape'=>false,'class'=>'btn btn-primary'));
            echo $this->Html->link('<span class="fa fa-filter"></span> '.__('Refine'), '#ard-dashboard-filter', array('escape'=>false,'class'=>'btn btn-warning'));
            ?>
            </div>
            <div class="btn-group no-pdf">
                <button type="button" class="btn btn-primary" onclick="window.print();"><span class="fa fa-print"></span> <?php echo __('Print Dashboard');?></button>
                <button type="button" class="btn btn-danger" onclick="downloadDashboardPdf();"><span class="fa fa-file-pdf-o"></span> <?php echo __('Export PDF');?></button>
            </div>
        </div>
    </div>
        <div class="panel-body">
        <?php echo $this->Session->flash();?>
        <div id="ard-dashboard-export-target">
        <?php echo $this->Form->create(false,array('type'=>'get','url'=>array('controller'=>'Ardaccounts','action'=>'dashboard',$currentProjectId),'class'=>'ard-dashboard-filter','id'=>'ard-dashboard-filter'));?>
            <div class="ard-filter-item">
                <label><?php echo __('Filter');?></label>
                <select name="preset" class="form-control js-dashboard-auto">
                    <option value="daily" <?php echo ($filterPreset==='daily')?'selected':'';?>><?php echo __('Daily');?></option>
                    <option value="weekly" <?php echo ($filterPreset==='weekly')?'selected':'';?>><?php echo __('Weekly');?></option>
                    <option value="monthly" <?php echo ($filterPreset==='monthly')?'selected':'';?>><?php echo __('Monthly');?></option>
                    <option value="yearly" <?php echo ($filterPreset==='yearly')?'selected':'';?>><?php echo __('Yearly');?></option>
                    <option value="custom" <?php echo ($filterPreset==='custom')?'selected':'';?>><?php echo __('Custom');?></option>
                    <option value="lifetime" <?php echo ($filterPreset==='lifetime')?'selected':'';?>><?php echo __('Lifetime');?></option>
                </select>
            </div>
            <div class="ard-filter-item">
                <label><?php echo __('Start Date');?></label>
                <input type="date" name="start" value="<?php echo h($filterStart);?>" class="form-control" />
            </div>
            <div class="ard-filter-item">
                <label><?php echo __('End Date');?></label>
                <input type="date" name="end" value="<?php echo h($filterEnd);?>" class="form-control" />
            </div>
            <?php if($userType=='ADR'){?>
            <div class="ard-filter-item">
                <label><?php echo __('Agency');?></label>
                <select name="project_id" class="form-control js-dashboard-auto">
                    <option value=""><?php echo __('All');?></option>
                    <?php foreach((array)$projectName as $projectId => $projectLabel){?>
                    <option value="<?php echo (int)$projectId;?>" <?php echo ((int)$currentProjectId === (int)$projectId) ? 'selected' : '';?>><?php echo h($projectLabel);?></option>
                    <?php }?>
                </select>
            </div>
            <?php }?>
            <div class="ard-filter-submit">
                <button class="btn btn-default" type="submit"><?php echo __('Apply');?></button>
            </div>
        <?php echo $this->Form->end();?>

        <div class="row ard-dashboard-grid">
            <div class="col-lg-3 col-md-4 col-sm-6 ard-dashboard-stat">
                <?php echo $this->Html->link(
                    '<div class="ard-stat-card is-blue"><div class="ard-stat-label">'.__('Total Assets').'</div><div class="ard-stat-value">'.$currency.$this->Number->format($stats['total_assets']).'</div><div class="ard-stat-note">'.__('Filtered Income').' | '.number_format((float)$incomeSharePercent,2).'%</div></div>',
                    array('controller'=>'Ardaccounts','action'=>'transactions',$currentProjectId,'?'=>array_merge($dashboardQuery,array('kind'=>'income'))),
                    array('escape'=>false,'class'=>'ard-stat-link')
                );?>
            </div>
            <div class="col-lg-3 col-md-4 col-sm-6 ard-dashboard-stat">
                <?php echo $this->Html->link(
                    '<div class="ard-stat-card is-red"><div class="ard-stat-label">'.__('Total Expenses').'</div><div class="ard-stat-value">'.$currency.$this->Number->format($stats['total_expenses']).'</div><div class="ard-stat-note">'.__('Filtered Expense').' | '.number_format((float)$expenseSharePercent,2).'%</div></div>',
                    array('controller'=>'Ardaccounts','action'=>'transactions',$currentProjectId,'?'=>array_merge($dashboardQuery,array('kind'=>'expense'))),
                    array('escape'=>false,'class'=>'ard-stat-link')
                );?>
            </div>
            <div class="col-lg-3 col-md-4 col-sm-6 ard-dashboard-stat">
                <?php echo $this->Html->link(
                    '<div class="ard-stat-card is-green"><div class="ard-stat-label">'.__('Net Balance').'</div><div class="ard-stat-value">'.$currency.$this->Number->format($stats['net_balance']).'</div><div class="ard-stat-note">'.__('Income - Expense').' | '.number_format((float)$netMarginPercent,2).'%</div></div>',
                    array('controller'=>'Ardaccounts','action'=>'reports',$currentProjectId,'?'=>array_merge($dashboardQuery,array('tab'=>'balance'))),
                    array('escape'=>false,'class'=>'ard-stat-link')
                );?>
            </div>
            <div class="col-lg-3 col-md-4 col-sm-6 ard-dashboard-stat">
                <?php echo $this->Html->link(
                    '<div class="ard-stat-card"><div class="ard-stat-label">'.__('Cash In Hand').'</div><div class="ard-stat-value">'.$currency.$this->Number->format($stats['cash_in_hand']).'</div><div class="ard-stat-note">'.__('Bank/Cash Ledgers').'</div></div>',
                    array('controller'=>'Ardaccounts','action'=>'ledgers',$currentProjectId),
                    array('escape'=>false,'class'=>'ard-stat-link')
                );?>
            </div>

            <div class="col-lg-3 col-md-4 col-sm-6 ard-dashboard-stat">
                <?php echo $this->Html->link(
                    '<div class="ard-stat-card"><div class="ard-stat-label">'.__('Receivables').'</div><div class="ard-stat-value">'.$currency.$this->Number->format($stats['receivables']).'</div><div class="ard-stat-note">'.__('Outstanding').'</div></div>',
                    array('controller'=>'Ardaccounts','action'=>'reports',$currentProjectId,'?'=>array_merge($dashboardQuery,array('tab'=>'aging'))),
                    array('escape'=>false,'class'=>'ard-stat-link')
                );?>
            </div>
            <div class="col-lg-3 col-md-4 col-sm-6 ard-dashboard-stat">
                <?php echo $this->Html->link(
                    '<div class="ard-stat-card"><div class="ard-stat-label">'.__('Payables').'</div><div class="ard-stat-value">'.$currency.$this->Number->format($stats['payables']).'</div><div class="ard-stat-note">'.__('Outstanding').'</div></div>',
                    array('controller'=>'Ardaccounts','action'=>'reports',$currentProjectId,'?'=>array_merge($dashboardQuery,array('tab'=>'vendor_outstanding'))),
                    array('escape'=>false,'class'=>'ard-stat-link')
                );?>
            </div>
            <div class="col-lg-3 col-md-4 col-sm-6 ard-dashboard-stat">
                <?php echo $this->Html->link(
                    '<div class="ard-stat-card is-amber"><div class="ard-stat-label">'.__('Construction Spend').'</div><div class="ard-stat-value">'.$currency.$this->Number->format($stats['construction_spend']).'</div><div class="ard-stat-note">'.__('Filtered Stock In').'</div></div>',
                    array('controller'=>'Ardaccounts','action'=>'stock',$currentProjectId),
                    array('escape'=>false,'class'=>'ard-stat-link')
                );?>
            </div>
            <div class="col-lg-3 col-md-4 col-sm-6 ard-dashboard-stat">
                <?php echo $this->Html->link(
                    '<div class="ard-stat-card"><div class="ard-stat-label">'.__('Construction Due').'</div><div class="ard-stat-value">'.$currency.$this->Number->format($stats['construction_due']).'</div><div class="ard-stat-note">'.__('Pending Invoices').'</div></div>',
                    array('controller'=>'Ardaccounts','action'=>'purchaseinvoices',$currentProjectId),
                    array('escape'=>false,'class'=>'ard-stat-link')
                );?>
            </div>

            <div class="col-lg-3 col-md-4 col-sm-6 ard-dashboard-stat">
                <?php echo $this->Html->link(
                    '<div class="ard-stat-card"><div class="ard-stat-label">'.__('Payroll Due').'</div><div class="ard-stat-value">'.$currency.$this->Number->format($stats['payroll_due']).'</div><div class="ard-stat-note">'.__('Pending Salaries').'</div></div>',
                    array('controller'=>'Ardaccounts','action'=>'hrpayroll',$currentProjectId),
                    array('escape'=>false,'class'=>'ard-stat-link')
                );?>
            </div>
            <div class="col-lg-3 col-md-4 col-sm-6 ard-dashboard-stat">
                <?php echo $this->Html->link(
                    '<div class="ard-stat-card"><div class="ard-stat-label">'.__('Site Cost Variance').'</div><div class="ard-stat-value">'.$currency.$this->Number->format($stats['site_cost_variance']).'</div><div class="ard-stat-note">'.__('Actual - Budget').'</div></div>',
                    array('controller'=>'Ardaccounts','action'=>'reports',$currentProjectId,'?'=>array_merge($dashboardQuery,array('tab'=>'site_cost'))),
                    array('escape'=>false,'class'=>'ard-stat-link')
                );?>
            </div>
            <div class="col-lg-3 col-md-4 col-sm-6 ard-dashboard-stat">
                <?php echo $this->Html->link(
                    '<div class="ard-stat-card"><div class="ard-stat-label">'.__('Low Stock').'</div><div class="ard-stat-value">'.(int)$stats['low_stock_count'].'</div><div class="ard-stat-note">'.__('Items Need Reorder').'</div></div>',
                    array('controller'=>'Ardaccounts','action'=>'reorderrules',$currentProjectId),
                    array('escape'=>false,'class'=>'ard-stat-link')
                );?>
            </div>
            <div class="col-lg-3 col-md-4 col-sm-6 ard-dashboard-stat">
                <?php echo $this->Html->link(
                    '<div class="ard-stat-card"><div class="ard-stat-label">'.__('Transactions').'</div><div class="ard-stat-value">'.(int)$counts['transactions'].'</div><div class="ard-stat-note">'.__('In Selected Filter').'</div></div>',
                    array('controller'=>'Ardaccounts','action'=>'transactions',$currentProjectId,'?'=>$dashboardQuery),
                    array('escape'=>false,'class'=>'ard-stat-link')
                );?>
            </div>
        </div>

        <div class="ard-section-card">
            <div class="ard-section-head">
                <h4><?php echo __('Recent Transactions');?></h4>
                <div>
                    <?php echo $this->Html->link(__('View All'),array('controller'=>'Ardaccounts','action'=>'transactions',$currentProjectId),array('class'=>'btn btn-xs btn-default'));?>
                </div>
            </div>
            <div class="ard-section-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <tr>
                            <th style="width:120px;"><?php echo __('Date');?></th>
                            <th><?php echo __('Account');?></th>
                            <th style="width:170px;"><?php echo __('Reference');?></th>
                            <th><?php echo __('Description');?></th>
                            <th style="width:120px;"><?php echo __('Type');?></th>
                            <th style="width:150px;" class="text-right"><?php echo __('Amount');?></th>
                            <th style="width:150px;"><?php echo __('Actions');?></th>
                        </tr>
                        <?php if(empty($recentTransactions)){?>
                            <tr>
                                <td colspan="7" class="text-center text-muted"><?php echo __('No transactions found in selected filter.');?></td>
                            </tr>
                        <?php }else{ foreach($recentTransactions as $row){?>
                            <tr>
                                <td><?php echo (!empty($row['Ardtransaction']['entry_date']) && $row['Ardtransaction']['entry_date'] !== '0000-00-00') ? date('d-M-Y', strtotime((string)$row['Ardtransaction']['entry_date'])) : '-';?></td>
                                <td><?php echo h(isset($row['Ardledger']['name']) ? $row['Ardledger']['name'] : '-');?></td>
                                <td><?php echo h(!empty($row['Ardtransaction']['ref_no']) ? $row['Ardtransaction']['ref_no'] : '-');?></td>
                                <td class="ard-col-description"><?php echo nl2br(h(!empty($row['Ardtransaction']['description']) ? $row['Ardtransaction']['description'] : '-'));?></td>
                                <td><?php echo h(strtoupper((string)$row['Ardtransaction']['type']));?></td>
                                <td class="text-right"><?php echo $currency.$this->Number->format($row['Ardtransaction']['amount']);?></td>
                                <td class="ard-actions">
                                    <?php
                                    $txId = (int)$row['Ardtransaction']['id'];
                                    $txProjectId = (int)$row['Ardtransaction']['project_id'];
                                    echo $this->Html->link(__('Print'),array('controller'=>'Ardaccounts','action'=>'printvoucher',$txId,$txProjectId),array('class'=>'btn btn-xs btn-info','target'=>'_blank'));
                                    echo $this->Html->link(__('Edit'),array('controller'=>'Ardaccounts','action'=>'transactions',$txProjectId,'edit:'.$txId),array('class'=>'btn btn-xs btn-warning'));
                                    ?>
                                </td>
                            </tr>
                        <?php }}?>
                    </table>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="ard-section-card">
                    <div class="ard-section-head">
                        <h4><?php echo __('Income vs Expense');?></h4>
                    </div>
                    <div class="ard-section-body">
                        <div class="ard-chart-wrap">
                            <canvas id="ardAmountChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="ard-section-card">
                    <div class="ard-section-head">
                        <h4><?php echo __('Entries Analysis');?></h4>
                    </div>
                    <div class="ard-section-body">
                        <div class="ard-chart-wrap">
                            <canvas id="ardEntryChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="ard-section-card">
            <div class="ard-section-head">
                <h4><?php echo __('Income & Expense Percentage Bars');?></h4>
            </div>
            <div class="ard-section-body">
                <div class="ard-percentage-bars">
                    <div>
                        <div class="ard-percent-row">
                            <span class="label"><?php echo __('Income');?></span>
                            <span class="amount"><?php echo $currency.$this->Number->format((float)$chartIncomeAmount);?></span>
                            <span class="pct"><?php echo number_format((float)$incomeSharePercent,2);?>%</span>
                        </div>
                        <div class="ard-percent-progress"><span class="income" style="width:<?php echo number_format((float)$incomeSharePercent,2,'.','');?>%;"></span></div>
                    </div>
                    <div>
                        <div class="ard-percent-row">
                            <span class="label"><?php echo __('Expense');?></span>
                            <span class="amount"><?php echo $currency.$this->Number->format((float)$chartExpenseAmount);?></span>
                            <span class="pct"><?php echo number_format((float)$expenseSharePercent,2);?>%</span>
                        </div>
                        <div class="ard-percent-progress"><span class="expense" style="width:<?php echo number_format((float)$expenseSharePercent,2,'.','');?>%;"></span></div>
                    </div>
                </div>
            </div>
        </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script type="text/javascript">
(function(){
    if(typeof jQuery !== 'undefined')
    {
        var $dashboardFilterForm = jQuery('.ard-dashboard-filter');
        $dashboardFilterForm.on('change', 'select.js-dashboard-auto', function(){
            $dashboardFilterForm.trigger('submit');
        });
    }

    if(typeof Chart === 'undefined')
    {
        return;
    }

    var incomeAmount = <?php echo json_encode((float)$chartIncomeAmount);?>;
    var expenseAmount = <?php echo json_encode((float)$chartExpenseAmount);?>;
    var incomeCount = <?php echo json_encode((int)$chartIncomeCount);?>;
    var expenseCount = <?php echo json_encode((int)$chartExpenseCount);?>;
    var transferCount = <?php echo json_encode((int)$chartTransferCount);?>;
    var currencyText = <?php echo json_encode($currencyText);?>;

    var amountCtx = document.getElementById('ardAmountChart');
    if(amountCtx)
    {
        new Chart(amountCtx, {
            type: 'doughnut',
            data: {
                labels: ['Income', 'Expense'],
                datasets: [{
                    data: [incomeAmount, expenseAmount],
                    backgroundColor: ['#2aa866', '#e25757'],
                    borderColor: ['#ffffff', '#ffffff'],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: {
                        callbacks: {
                            label: function(context){
                                var total = 0;
                                if(context.dataset && context.dataset.data)
                                {
                                    for(var i=0;i<context.dataset.data.length;i++)
                                    {
                                        total += Number(context.dataset.data[i] || 0);
                                    }
                                }
                                var pct = total > 0 ? ((Number(context.raw || 0) / total) * 100).toFixed(2) : '0.00';
                                var val = Number(context.raw || 0).toLocaleString();
                                return context.label + ': ' + currencyText + ' ' + val + ' (' + pct + '%)';
                            }
                        }
                    }
                }
            }
        });
    }

    var entryCtx = document.getElementById('ardEntryChart');
    if(entryCtx)
    {
        var totalEntries = incomeCount + expenseCount + transferCount;
        var incomePctByEntries = totalEntries > 0 ? (incomeCount / totalEntries) * 100 : 0;
        var expensePctByEntries = totalEntries > 0 ? (expenseCount / totalEntries) * 100 : 0;
        var transferPctByEntries = totalEntries > 0 ? (transferCount / totalEntries) * 100 : 0;
        var incomeLabel = 'Income (' + incomePctByEntries.toFixed(1) + '%)';
        var expenseLabel = 'Expense (' + expensePctByEntries.toFixed(1) + '%)';
        var journalLabel = 'Journal (' + transferPctByEntries.toFixed(1) + '%)';
        new Chart(entryCtx, {
            type: 'bar',
            data: {
                labels: [incomeLabel, expenseLabel, journalLabel],
                datasets: [{
                    label: 'Percentage',
                    data: [incomePctByEntries, expensePctByEntries, transferPctByEntries],
                    backgroundColor: ['#4f86e8', '#ff9c42', '#8c67d9'],
                    borderRadius: 6,
                    maxBarThickness: 56
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            precision: 0,
                            callback: function(value){
                                return value + '%';
                            }
                        }
                    }
                }
            }
        });
    }
})();

function dashboardPdfStamp()
{
    var d = new Date();
    var pad = function(v){ return (v < 10 ? '0' : '') + v; };
    return d.getFullYear().toString() + pad(d.getMonth() + 1) + pad(d.getDate()) + '_' + pad(d.getHours()) + pad(d.getMinutes()) + pad(d.getSeconds());
}

function downloadDashboardPdf()
{
    var target = document.getElementById('ard-dashboard-export-target');
    if(!target || typeof html2pdf === 'undefined')
    {
        window.print();
        return;
    }
    target.classList.add('is-pdf-export');
    var options = {
        margin: [8, 8, 8, 8],
        filename: 'accounts_dashboard_' + dashboardPdfStamp() + '.pdf',
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2, useCORS: true, scrollY: 0, backgroundColor: '#ffffff' },
        jsPDF: { unit: 'mm', format: 'a4', orientation: 'landscape' },
        pagebreak: { mode: ['css', 'legacy'] }
    };
    html2pdf().set(options).from(target).save().then(function(){
        target.classList.remove('is-pdf-export');
    }).catch(function(){
        target.classList.remove('is-pdf-export');
    });
}
</script>
