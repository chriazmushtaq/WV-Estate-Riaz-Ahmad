<?php
$this->Js->JqueryEngine->jQueryObject = 'jQuery';
$this->Paginator->options(array('update' => '#resultDiv','evalScripts' => true));

$editId = isset($this->request->params['named']['edit']) ? (int)$this->request->params['named']['edit'] : 0;
$kindQuery = isset($this->request->query['kind']) ? strtolower(trim((string)$this->request->query['kind'])) : '';
if(!in_array($kindQuery, array('income','expense')))
{
    $kindQuery = '';
}
$todayIso = date('Y-m-d');
$todayDisplay = date('d-M-Y');
$normalizeVoucherDate = function($rawValue) use ($todayIso)
{
    $rawValue = trim((string)$rawValue);
    if($rawValue === '' || $rawValue === '0000-00-00')
    {
        return $todayIso;
    }
    $parsedTs = strtotime(str_replace('/', '-', $rawValue));
    if($parsedTs === false)
    {
        return $todayIso;
    }
    return date('Y-m-d', $parsedTs);
};

$initialVoucher = array(
    'id' => '',
    'project_id' => ($userType=='ADR' ? '' : (int)$agencyId),
    'entry_date' => $todayIso,
    'ledger_id' => '',
    'expense_ledger_id' => '',
    'income_ledger_id' => '',
    'type' => ($kindQuery !== '' ? $kindQuery : 'expense'),
    'status' => 'Posted',
    'amount' => '',
    'construction_site_id' => '',
    'ref_no' => '',
    'description' => ''
);
if(!empty($this->request->data['Ardtransaction']))
{
    $initialVoucher = array(
        'id' => isset($this->request->data['Ardtransaction']['id']) ? (int)$this->request->data['Ardtransaction']['id'] : '',
        'project_id' => isset($this->request->data['Ardtransaction']['project_id']) ? (int)$this->request->data['Ardtransaction']['project_id'] : ($userType=='ADR' ? '' : (int)$agencyId),
        'entry_date' => $normalizeVoucherDate(isset($this->request->data['Ardtransaction']['entry_date']) ? $this->request->data['Ardtransaction']['entry_date'] : $todayIso),
        'ledger_id' => isset($this->request->data['Ardtransaction']['ledger_id']) ? (int)$this->request->data['Ardtransaction']['ledger_id'] : '',
        'expense_ledger_id' => isset($this->request->data['Ardtransaction']['expense_ledger_id']) ? (int)$this->request->data['Ardtransaction']['expense_ledger_id'] : '',
        'income_ledger_id' => isset($this->request->data['Ardtransaction']['income_ledger_id']) ? (int)$this->request->data['Ardtransaction']['income_ledger_id'] : '',
        'type' => isset($this->request->data['Ardtransaction']['type']) ? (string)$this->request->data['Ardtransaction']['type'] : ($kindQuery !== '' ? $kindQuery : 'expense'),
        'status' => isset($this->request->data['Ardtransaction']['status']) ? (string)$this->request->data['Ardtransaction']['status'] : 'Posted',
        'amount' => isset($this->request->data['Ardtransaction']['amount']) ? (string)$this->request->data['Ardtransaction']['amount'] : '',
        'construction_site_id' => isset($this->request->data['Ardtransaction']['construction_site_id']) ? (int)$this->request->data['Ardtransaction']['construction_site_id'] : '',
        'ref_no' => isset($this->request->data['Ardtransaction']['ref_no']) ? (string)$this->request->data['Ardtransaction']['ref_no'] : '',
        'description' => isset($this->request->data['Ardtransaction']['description']) ? (string)$this->request->data['Ardtransaction']['description'] : ''
    );
}

$openOnLoad = ($editId > 0 || $kindQuery !== '');
$requestedOpenMode = isset($this->request->query['mode']) ? strtolower(trim((string)$this->request->query['mode'])) : '';
if(!in_array($requestedOpenMode, array('view', 'edit')))
{
    $requestedOpenMode = '';
}
$openMode = ($editId > 0 ? ($requestedOpenMode === 'view' ? 'view' : 'edit') : 'add');
$expenseApprovalEnabled = !empty($expenseApprovalEnabled);
$canCreateExpenseApproval = !empty($canCreateExpenseApproval);
$ledgerListByProject = (isset($ledgerListByProject) && is_array($ledgerListByProject)) ? $ledgerListByProject : array();
?>
<style type="text/css">
.ard-voucher-wrap .panel-body{background:#f3f6fb;}
.ard-voucher-card{background:#fff;border:1px solid #d8e1ef;border-radius:12px;margin-bottom:14px;overflow:hidden;}
.ard-voucher-card-head{padding:12px 14px;border-bottom:1px solid #e8edf6;font-weight:700;color:#1f3556;}
.ard-voucher-empty{padding:26px 10px;color:#7e8ea6;text-align:center;font-size:12px;border:1px dashed #d0dceb;border-radius:10px;background:#fafcff;}
.ard-right{text-align:right;}
.voucher-agency-note{display:block;font-size:11px;color:#4f627f;opacity:.65;line-height:1.3;margin-top:2px;}
#voucherModal .modal-header{background:#f7f9fc;border-bottom:1px solid #e8edf6;}
#voucherModal .modal-title{font-weight:700;color:#203a5d;}
.voucher-fixed-value{align-items:center;background:#f8fafc;border:1px solid #cbd8e8;color:#172033;display:none;font-weight:800;min-height:38px;padding:9px 12px;}
.voucher-workflow-note{background:#fff7ed;border-left:4px solid #d97706;color:#92400e;display:none;font-size:11px;font-weight:700;margin-bottom:14px;padding:10px 12px;}

/* Clean table styling */
.ard-voucher-wrap .table{margin-bottom:0;}
.ard-voucher-wrap .table > thead > tr > th,
.ard-voucher-wrap .table > tbody > tr > th{background:#f8fafc;color:#334155;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.4px;vertical-align:middle!important;border-bottom:2px solid #cbd5e1;padding:10px 8px;white-space:nowrap;}
.ard-voucher-wrap .table > tbody > tr > td{vertical-align:middle!important;padding:8px 8px;font-size:12px;color:#334155;}
.ard-voucher-wrap .table > tbody > tr:hover{background:#f8fbff!important;}
.ard-type-badge{display:inline-block;font-weight:700;padding:3px 8px;border-radius:4px;font-size:11px;letter-spacing:0.3px;white-space:nowrap;}
.ard-type-expense{background-color:#d9534f!important;color:#fff!important;}
.ard-type-income{background-color:#28a745!important;color:#fff!important;}
.ard-table-actions{display:inline-flex;align-items:center;gap:3px;white-space:nowrap;}
.ard-table-actions .btn{padding:2px 7px;font-size:11px;line-height:1.5;border-radius:3px;}

/* Professional Single-Line Transaction Filter Bar */
.ard-tx-filter-bar{background:#ffffff!important;border:1px solid #dbeafe!important;border-radius:10px!important;padding:8px 12px!important;margin:12px 0 16px 0!important;box-shadow:0 1px 3px rgba(0,0,0,0.05)!important;}
.ard-tx-filter-form{display:flex!important;flex-direction:row!important;flex-wrap:nowrap!important;align-items:center!important;gap:7px!important;margin:0!important;padding:0!important;overflow-x:auto!important;overflow-y:hidden!important;white-space:nowrap!important;}
.ard-filter-item{display:inline-flex!important;align-items:center!important;margin:0!important;padding:0!important;flex-shrink:0!important;vertical-align:middle!important;}
.ard-input-wrap{position:relative!important;display:inline-flex!important;align-items:center!important;margin:0!important;padding:0!important;}
.ard-input-wrap .ard-input-icon{position:absolute!important;left:10px!important;top:50%!important;transform:translateY(-50%)!important;color:#64748b!important;font-size:13px!important;line-height:1!important;pointer-events:none!important;z-index:3!important;}
.ard-datepicker-wrap .ard-input-icon{pointer-events:auto!important;cursor:pointer!important;color:#0284c7!important;}
.ard-filter-select{height:34px!important;line-height:34px!important;padding:0 24px 0 30px!important;border:1px solid #cbd5e1!important;border-radius:6px!important;background-color:#ffffff!important;color:#1e293b!important;font-size:12px!important;font-weight:600!important;box-shadow:none!important;cursor:pointer!important;vertical-align:middle!important;margin:0!important;-webkit-appearance:menulist!important;appearance:menulist!important;}
#filterProjectId{min-width:170px!important;max-width:215px!important;}
#filterDatePreset{width:125px!important;}
#filterSort{width:145px!important;}
.ard-preset-pills{display:inline-flex!important;align-items:center!important;gap:2px!important;background:#f1f5f9!important;padding:2px!important;border-radius:6px!important;border:1px solid #e2e8f0!important;}
.ard-preset-pills .js-preset-btn{height:28px!important;line-height:28px!important;padding:0 10px!important;font-size:12px!important;font-weight:700!important;border:none!important;border-radius:4px!important;background:transparent!important;color:#475569!important;box-shadow:none!important;cursor:pointer!important;transition:all 0.15s ease!important;margin:0!important;}
.ard-preset-pills .js-preset-btn:hover{background:#ffffff!important;color:#0f172a!important;box-shadow:0 1px 2px rgba(0,0,0,0.08)!important;}
.ard-preset-pills .js-preset-btn.active{background:#0284c7!important;color:#ffffff!important;box-shadow:0 1px 3px rgba(2,132,199,0.35)!important;}
.ard-date-range-item{display:inline-flex!important;align-items:center!important;gap:5px!important;}
.ard-date-input{height:34px!important;line-height:34px!important;width:115px!important;padding:0 8px 0 28px!important;border:1px solid #cbd5e1!important;border-radius:6px!important;background-color:#ffffff!important;color:#1e293b!important;font-size:12px!important;font-weight:700!important;font-family:inherit!important;box-shadow:none!important;cursor:pointer!important;vertical-align:middle!important;margin:0!important;}
.ard-date-input:focus,.ard-filter-select:focus{border-color:#0284c7!important;box-shadow:0 0 0 3px rgba(2,132,199,0.15)!important;outline:none!important;}
.ard-date-sep{color:#64748b!important;font-size:12px!important;font-weight:700!important;padding:0 2px!important;user-select:none!important;}
.ard-btn-group{display:inline-flex!important;align-items:center!important;gap:5px!important;}
.ard-filter-apply-btn{height:34px!important;line-height:34px!important;padding:0 14px!important;background:#0284c7!important;border:1px solid #0284c7!important;color:#ffffff!important;font-size:12px!important;font-weight:800!important;border-radius:6px!important;box-shadow:0 1px 3px rgba(2,132,199,0.3)!important;display:inline-flex!important;align-items:center!important;gap:5px!important;cursor:pointer!important;transition:all 0.15s ease!important;}
.ard-filter-apply-btn:hover{background:#0369a1!important;border-color:#0369a1!important;color:#ffffff!important;box-shadow:0 3px 6px rgba(2,132,199,0.4)!important;}
.ard-filter-reset-btn{height:34px!important;line-height:32px!important;padding:0 12px!important;background:#ffffff!important;border:1px solid #cbd5e1!important;color:#475569!important;font-size:12px!important;font-weight:700!important;border-radius:6px!important;display:inline-flex!important;align-items:center!important;gap:5px!important;cursor:pointer!important;transition:all 0.15s ease!important;text-decoration:none!important;}
.ard-filter-reset-btn:hover{background:#f8fafc!important;color:#0f172a!important;border-color:#94a3b8!important;text-decoration:none!important;}
.ui-datepicker{z-index:99999!important;}
</style>

<div id="resultDiv">
    <div class="page-title">
        <div class="title-env">
            <h1 class="title"><?php echo !empty($pageTitleCustom) ? h($pageTitleCustom) : (!empty($typeFilter) && $typeFilter === 'expense' ? __('Expense Transactions') : (!empty($typeFilter) && $typeFilter === 'income' ? __('Income Transactions') : __('Transactions')));?></h1>
        </div>
    </div>

    <div class="panel ard-voucher-wrap">
        <?php echo $this->Session->flash();?>
        <?php
        $tabProjectId = (!empty($currentProjectId) && (int)$currentProjectId > 0) ? (int)$currentProjectId : null;
        ?>
        <div class="panel-heading">
            <div class="btn-group">
                <?php echo $this->Html->link('<span class="fa fa-home"></span>&nbsp;'.__('ARD Dashboard'),array('controller'=>'Ardaccounts','action'=>'dashboard',$tabProjectId),array('escape'=>false,'class'=>'btn btn-default'));?>
                <?php echo $this->Html->link('<span class="fa fa-exchange"></span>&nbsp;'.__('All Transactions'),array('controller'=>'Ardaccounts','action'=>'transactions',$tabProjectId),array('escape'=>false,'class'=>'btn '.(empty($isExpenseMode) && empty($isIncomeMode) && (empty($typeFilter) || ($typeFilter !== 'expense' && $typeFilter !== 'income')) ? 'btn-info active' : 'btn-default')));?>
                <?php echo $this->Html->link('<span class="fa fa-arrow-circle-down"></span>&nbsp;'.__('Income Transactions'),array('controller'=>'Ardaccounts','action'=>'incometransactions',$tabProjectId),array('escape'=>false,'class'=>'btn '.(!empty($isIncomeMode) || (!empty($typeFilter) && $typeFilter === 'income') ? 'btn-success active' : 'btn-default')));?>
                <?php echo $this->Html->link('<span class="fa fa-credit-card"></span>&nbsp;'.__('Expense Transactions'),array('controller'=>'Ardaccounts','action'=>'expensetransactions',$tabProjectId),array('escape'=>false,'class'=>'btn '.(!empty($isExpenseMode) || (!empty($typeFilter) && $typeFilter === 'expense') ? 'btn-danger active' : 'btn-default')));?>
                <?php echo $this->Html->link('<span class="fa fa-book"></span>&nbsp;'.__('Ledgers'),array('controller'=>'Ardaccounts','action'=>'ledgers',$tabProjectId),array('escape'=>false,'class'=>'btn btn-primary'));?>
                <?php echo $this->Html->link('<span class="fa fa-calculator"></span>&nbsp;'.__('Cash Closing'),array('controller'=>'Ardaccounts','action'=>'cashclosing',$tabProjectId),array('escape'=>false,'class'=>'btn btn-default'));?>
                <?php echo $this->Html->link('<span class="fa fa-check-square-o"></span>&nbsp;'.__('Expense Approvals'),array('controller'=>'Ardaccounts','action'=>'expenseapprovals',$tabProjectId),array('escape'=>false,'class'=>'btn btn-warning'));?>
                <a href="javascript:void(0);" class="btn btn-success js-voucher-add" data-kind="income"><span class="fa fa-plus"></span>&nbsp;<?php echo __('Add Income');?></a>
                <?php if(!$expenseApprovalEnabled || $canCreateExpenseApproval):?><a href="javascript:void(0);" class="btn btn-danger js-voucher-add" data-kind="expense"><span class="fa fa-minus"></span>&nbsp;<?php echo $expenseApprovalEnabled ? __('Create Expense Request') : __('Add Expense');?></a><?php endif;?>
                <a href="javascript:void(0);" class="btn btn-info js-voucher-add"><span class="fa fa-file-text-o"></span>&nbsp;<?php echo __('Add Voucher');?></a>
            </div>
        </div>

        <?php
        $currentAction = !empty($isExpenseMode) ? 'expensetransactions' : (!empty($isIncomeMode) ? 'incometransactions' : 'transactions');
        $filterFormUrl = array('controller' => 'Ardaccounts', 'action' => $currentAction);
        if(!empty($currentProjectId) && (int)$currentProjectId > 0)
        {
            $filterFormUrl[] = (int)$currentProjectId;
        }
        $resetUrl = $this->Html->url($filterFormUrl);
        $filterProjVal = isset($filterProjectId) ? $filterProjectId : 'all';
        $filterPresetVal = isset($filterDatePreset) ? $filterDatePreset : 'all';
        $filterStartVal = isset($filterStartDate) ? $filterStartDate : '';
        $filterEndVal = isset($filterEndDate) ? $filterEndDate : '';
        $filterSortVal = isset($filterSort) ? $filterSort : 'date_desc';

        // Configure Paginator options so pagination preserves all active filters
        $paginatorParams = array();
        if($filterProjVal !== '' && $filterProjVal !== 'all' && (int)$filterProjVal > 0)
        {
            $paginatorParams['project_id'] = (int)$filterProjVal;
        }
        elseif($filterProjVal === 'all' || (string)$filterProjVal === '0')
        {
            $paginatorParams['project_id'] = 'all';
        }
        if(!empty($filterPresetVal) && $filterPresetVal !== 'all')
        {
            $paginatorParams['date_preset'] = $filterPresetVal;
        }
        if(!empty($filterStartVal))
        {
            $paginatorParams['start_date'] = $filterStartVal;
        }
        if(!empty($filterEndVal))
        {
            $paginatorParams['end_date'] = $filterEndVal;
        }
        if(!empty($filterSortVal) && $filterSortVal !== 'date_desc')
        {
            $paginatorParams['sort'] = $filterSortVal;
        }
        if(!empty($typeFilter))
        {
            $paginatorParams['type'] = $typeFilter;
        }

        $this->Paginator->options(array(
            'update' => '#resultDiv',
            'evalScripts' => true,
            'url' => array('?' => $paginatorParams)
        ));
        ?>

        <!-- Single-line filter bar after upper side buttons -->
        <div class="ard-tx-filter-bar">
            <form method="get" action="<?php echo $this->Html->url($filterFormUrl);?>" class="ard-tx-filter-form" id="txFilterForm">
                <?php if(!empty($typeFilter)): ?>
                    <input type="hidden" name="type" value="<?php echo h($typeFilter); ?>">
                <?php endif; ?>

                <!-- Project Filter -->
                <div class="ard-filter-item">
                    <div class="ard-input-wrap" title="<?php echo __('Filter by Project / Agency');?>">
                        <i class="fa fa-building-o ard-input-icon"></i>
                        <select name="project_id" id="filterProjectId" class="form-control ard-filter-select">
                            <option value="all" <?php echo ($filterProjVal === 'all' || (string)$filterProjVal === '0') ? 'selected' : '';?>><?php echo __('All Projects');?></option>
                            <?php if(!empty($projectName) && is_array($projectName)): ?>
                                <?php foreach($projectName as $pId => $pName): ?>
                                    <option value="<?php echo $pId;?>" <?php echo ((string)$filterProjVal === (string)$pId) ? 'selected' : '';?>><?php echo h($pName);?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>

                <!-- Date Preset Dropdown -->
                <div class="ard-filter-item">
                    <div class="ard-input-wrap" title="<?php echo __('Quick Period Preset');?>">
                        <i class="fa fa-calendar ard-input-icon"></i>
                        <select name="date_preset" id="filterDatePreset" class="form-control ard-filter-select">
                            <option value="all" <?php echo ($filterPresetVal === 'all' || $filterPresetVal === '') ? 'selected' : '';?>><?php echo __('All Dates');?></option>
                            <option value="today" <?php echo ($filterPresetVal === 'today') ? 'selected' : '';?>><?php echo __('Today');?></option>
                            <option value="week" <?php echo ($filterPresetVal === 'week') ? 'selected' : '';?>><?php echo __('This Week');?></option>
                            <option value="month" <?php echo ($filterPresetVal === 'month') ? 'selected' : '';?>><?php echo __('This Month');?></option>
                            <option value="year" <?php echo ($filterPresetVal === 'year') ? 'selected' : '';?>><?php echo __('This Year');?></option>
                            <option value="custom" <?php echo ($filterPresetVal === 'custom') ? 'selected' : '';?>><?php echo __('Custom');?></option>
                        </select>
                    </div>
                </div>

                <!-- Quick Date Preset Buttons -->
                <div class="ard-filter-item ard-preset-pills">
                    <button type="button" class="btn js-preset-btn <?php echo ($filterPresetVal === 'today') ? 'active' : '';?>" data-preset="today"><?php echo __('Today');?></button>
                    <button type="button" class="btn js-preset-btn <?php echo ($filterPresetVal === 'week') ? 'active' : '';?>" data-preset="week"><?php echo __('Week');?></button>
                    <button type="button" class="btn js-preset-btn <?php echo ($filterPresetVal === 'month') ? 'active' : '';?>" data-preset="month"><?php echo __('Month');?></button>
                    <button type="button" class="btn js-preset-btn <?php echo ($filterPresetVal === 'year') ? 'active' : '';?>" data-preset="year"><?php echo __('Year');?></button>
                </div>

                <!-- Start Date to End Date Inputs as Calendar Pickers -->
                <div class="ard-filter-item ard-date-range-item">
                    <div class="ard-input-wrap ard-datepicker-wrap" title="<?php echo __('Click calendar to choose Start Date');?>">
                        <i class="fa fa-calendar-check-o ard-input-icon"></i>
                        <input type="text" name="start_date" id="filterStartDate" class="form-control ard-date-input js-ard-datepicker" placeholder="<?php echo __('Start Date');?>" value="<?php echo h($filterStartVal);?>" autocomplete="off" />
                    </div>
                    <span class="ard-date-sep"><?php echo __('to');?></span>
                    <div class="ard-input-wrap ard-datepicker-wrap" title="<?php echo __('Click calendar to choose End Date');?>">
                        <i class="fa fa-calendar ard-input-icon"></i>
                        <input type="text" name="end_date" id="filterEndDate" class="form-control ard-date-input js-ard-datepicker" placeholder="<?php echo __('End Date');?>" value="<?php echo h($filterEndVal);?>" autocomplete="off" />
                    </div>
                </div>

                <!-- Sort Filter -->
                <div class="ard-filter-item">
                    <div class="ard-input-wrap" title="<?php echo __('Sort transactions');?>">
                        <i class="fa fa-sort ard-input-icon"></i>
                        <select name="sort" id="filterSort" class="form-control ard-filter-select">
                            <option value="date_desc" <?php echo ($filterSortVal === 'date_desc') ? 'selected' : '';?>><?php echo __('Date (Newest)');?></option>
                            <option value="date_asc" <?php echo ($filterSortVal === 'date_asc') ? 'selected' : '';?>><?php echo __('Date (Oldest)');?></option>
                            <option value="amount_desc" <?php echo ($filterSortVal === 'amount_desc') ? 'selected' : '';?>><?php echo __('Amount (High-Low)');?></option>
                            <option value="amount_asc" <?php echo ($filterSortVal === 'amount_asc') ? 'selected' : '';?>><?php echo __('Amount (Low-High)');?></option>
                            <option value="id_desc" <?php echo ($filterSortVal === 'id_desc') ? 'selected' : '';?>><?php echo __('ID (Newest)');?></option>
                        </select>
                    </div>
                </div>

                <!-- Apply Filter & Reset Buttons -->
                <div class="ard-filter-item ard-btn-group">
                    <button type="submit" class="btn ard-filter-apply-btn"><i class="fa fa-filter"></i> <?php echo __('Apply Filter');?></button>
                    <a href="<?php echo $resetUrl;?>" class="btn ard-filter-reset-btn" title="<?php echo __('Reset All Filters');?>"><i class="fa fa-refresh"></i> <?php echo __('Reset');?></a>
                </div>
            </form>
        </div>

        <?php echo $this->element('pagination');?>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                <tr>
                    <th style="width:35px;" class="text-center">#</th>
                    <th style="width:105px;"><?php echo __('Date');?></th>
                    <th><?php echo __('Ledger');?></th>
                    <th style="width:95px;" class="text-center"><?php echo __('Type');?></th>
                    <th style="width:85px;" class="text-center"><?php echo __('Status');?></th>
                    <th style="width:120px;" class="ard-right"><?php echo __('Amount');?></th>
                    <th style="width:100px;"><?php echo __('Stock Store');?></th>
                    <th style="width:115px;"><?php echo __('Reference');?></th>
                    <th><?php echo __('Description');?></th>
                    <th style="width:180px;" class="text-center"><?php echo __('Action');?></th>
                </tr>
                </thead>
                <tbody>
                <?php
                $pageParams = $this->Paginator->params();
                $limit = $pageParams['limit'];
                $page = $pageParams['page'];
                $serialNo = 1*$limit*($page-1)+1;
                if(empty($rows))
                {
                    ?>
                    <tr><td colspan="10" class="ard-voucher-empty"><?php echo __('No vouchers found.');?></td></tr>
                    <?php
                }
                foreach($rows as $row):
                    $id=(int)$row['Ardtransaction']['id'];
                    $txDate = isset($row['Ardtransaction']['entry_date']) ? (string)$row['Ardtransaction']['entry_date'] : '';
                    $txLedgerId = isset($row['Ardtransaction']['ledger_id']) ? (int)$row['Ardtransaction']['ledger_id'] : 0;
                    $txExpenseLedgerId = isset($row['Ardtransaction']['expense_ledger_id']) ? (int)$row['Ardtransaction']['expense_ledger_id'] : 0;
                    $txIncomeLedgerId = isset($row['Ardtransaction']['income_ledger_id']) ? (int)$row['Ardtransaction']['income_ledger_id'] : 0;
                    $txType = isset($row['Ardtransaction']['type']) ? (string)$row['Ardtransaction']['type'] : 'expense';
                    $txTypeLower = strtolower(trim((string)$txType));
                    $rowLedgerType = !empty($row['Ardledger']['type']) ? strtolower(trim((string)$row['Ardledger']['type'])) : '';
                    if(!empty($isExpenseMode) && ($txTypeLower !== 'expense' || $rowLedgerType === 'income'))
                    {
                        continue;
                    }
                    if(!empty($isIncomeMode) && ($txTypeLower !== 'income' || $rowLedgerType === 'expense'))
                    {
                        continue;
                    }
                    $txStatus = isset($row['Ardtransaction']['status']) ? (string)$row['Ardtransaction']['status'] : 'Posted';
                    $txAmount = isset($row['Ardtransaction']['amount']) ? (string)$row['Ardtransaction']['amount'] : '';
                    $txSiteId = isset($row['Ardtransaction']['construction_site_id']) ? (int)$row['Ardtransaction']['construction_site_id'] : 0;
                    $txRef = isset($row['Ardtransaction']['ref_no']) ? (string)$row['Ardtransaction']['ref_no'] : '';
                    $txDesc = isset($row['Ardtransaction']['description']) ? (string)$row['Ardtransaction']['description'] : '';
                ?>
                <tr>
                    <td class="text-center" style="font-weight:600;"><?php echo $serialNo++;?></td>
                    <td style="white-space:nowrap;font-weight:600;"><?php echo (!empty($row['Ardtransaction']['entry_date']) && $row['Ardtransaction']['entry_date'] !== '0000-00-00') ? date('d-M-Y', strtotime((string)$row['Ardtransaction']['entry_date'])) : '-';?></td>
                    <td>
                        <?php
                        $sourceLedgerName = isset($row['Ardledger']['name']) ? trim((string)$row['Ardledger']['name']) : '';
                        $expenseLedgerName = isset($row['ExpenseLedger']['name']) ? trim((string)$row['ExpenseLedger']['name']) : '';
                        $incomeLedgerName = isset($row['IncomeLedger']['name']) ? trim((string)$row['IncomeLedger']['name']) : '';
                        if($txTypeLower === 'expense' && $expenseLedgerName !== '')
                        {
                            echo '<strong>'.h($expenseLedgerName).'</strong> <span class="text-muted" style="font-size:11px;">('.__('From').': '.h($sourceLedgerName).')</span>';
                        }
                        elseif($txTypeLower === 'income' && $incomeLedgerName !== '')
                        {
                            echo '<strong>'.h($incomeLedgerName).'</strong> <span class="text-muted" style="font-size:11px;">('.__('To').': '.h($sourceLedgerName).')</span>';
                        }
                        else
                        {
                            echo '<strong>'.h($sourceLedgerName).'</strong>';
                        }
                        ?>
                        <?php if($userType=='ADR' && !empty($row['Agency']['name'])){?><span class="voucher-agency-note"><i class="fa fa-building-o"></i> <?php echo __('Agency');?>: <?php echo h($row['Agency']['name']);?></span><?php }?>
                    </td>
                    <td class="text-center" style="white-space:nowrap;">
                        <?php if($txTypeLower === 'expense'): ?>
                            <span class="label label-danger ard-type-badge ard-type-expense"><i class="fa fa-arrow-circle-down"></i> <?php echo __('Expense');?></span>
                        <?php elseif($txTypeLower === 'income'): ?>
                            <span class="label label-success ard-type-badge ard-type-income"><i class="fa fa-arrow-circle-up"></i> <?php echo __('Income');?></span>
                        <?php else: ?>
                            <span class="label label-default ard-type-badge"><?php echo h(ucfirst($txType));?></span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center" style="white-space:nowrap;">
                        <?php
                        $statusClass = 'default';
                        if($txStatus === 'Posted' || $txStatus === 'Approved'){ $statusClass = 'success'; }
                        elseif($txStatus === 'Draft'){ $statusClass = 'warning'; }
                        elseif($txStatus === 'Void' || $txStatus === 'Reversed'){ $statusClass = 'danger'; }
                        ?>
                        <span class="label label-<?php echo h($statusClass);?>" style="font-weight:700;padding:3px 7px;border-radius:3px;font-size:10px;letter-spacing:0.3px;text-transform:uppercase;"><?php echo h($txStatus);?></span>
                    </td>
                    <td class="ard-right" style="white-space:nowrap;font-family:monospace;font-size:13px;font-weight:700;color:<?php echo ($txTypeLower === 'expense' ? '#d9534f' : ($txTypeLower === 'income' ? '#28a745' : '#1e293b'));?>;">
                        <?php echo ($txTypeLower === 'expense' ? '- ' : ($txTypeLower === 'income' ? '+ ' : '')) . $currency . ' ' . $this->Number->format($row['Ardtransaction']['amount']);?>
                    </td>
                    <td><?php echo !empty($row['Ardsite']['site_name']) ? h($row['Ardsite']['site_name']) : '<span class="text-muted">-</span>';?></td>
                    <td style="white-space:nowrap;font-weight:600;font-size:11px;color:#334155;"><?php echo !empty($row['Ardtransaction']['ref_no']) ? h($row['Ardtransaction']['ref_no']) : '<span class="text-muted">-</span>';?></td>
                    <td style="font-size:11px;color:#475569;"><?php echo !empty($row['Ardtransaction']['description']) ? h($row['Ardtransaction']['description']) : '<span class="text-muted">-</span>';?></td>
                    <td class="text-center" style="white-space:nowrap;width:1%;">
                        <div class="ard-table-actions">
                            <button type="button"
                                    class="btn btn-xs btn-default js-voucher-open"
                                    data-mode="view"
                                    data-id="<?php echo $id;?>"
                                    data-project-id="<?php echo (int)$row['Ardtransaction']['project_id'];?>"
                                    data-entry-date="<?php echo h($txDate);?>"
                                    data-ledger-id="<?php echo $txLedgerId;?>"
                                    data-ledger-name="<?php echo h($sourceLedgerName);?>"
                                    data-expense-ledger-id="<?php echo $txExpenseLedgerId;?>"
                                    data-expense-ledger-name="<?php echo h($expenseLedgerName);?>"
                                    data-income-ledger-id="<?php echo $txIncomeLedgerId;?>"
                                    data-income-ledger-name="<?php echo h($incomeLedgerName);?>"
                                    data-type="<?php echo h($txType);?>"
                                    data-status="<?php echo h($txStatus);?>"
                                    data-amount="<?php echo h($txAmount);?>"
                                    data-site-id="<?php echo $txSiteId;?>"
                                    data-ref-no="<?php echo h($txRef);?>"
                                    data-description="<?php echo h($txDesc);?>"
                                    title="<?php echo __('View Voucher');?>"><i class="fa fa-eye"></i> <?php echo __('View');?></button>
                            <button type="button"
                                    class="btn btn-xs btn-warning js-voucher-open"
                                    data-mode="edit"
                                    data-id="<?php echo $id;?>"
                                    data-project-id="<?php echo (int)$row['Ardtransaction']['project_id'];?>"
                                    data-entry-date="<?php echo h($txDate);?>"
                                    data-ledger-id="<?php echo $txLedgerId;?>"
                                    data-ledger-name="<?php echo h($sourceLedgerName);?>"
                                    data-expense-ledger-id="<?php echo $txExpenseLedgerId;?>"
                                    data-expense-ledger-name="<?php echo h($expenseLedgerName);?>"
                                    data-income-ledger-id="<?php echo $txIncomeLedgerId;?>"
                                    data-income-ledger-name="<?php echo h($incomeLedgerName);?>"
                                    data-type="<?php echo h($txType);?>"
                                    data-status="<?php echo h($txStatus);?>"
                                    data-amount="<?php echo h($txAmount);?>"
                                    data-site-id="<?php echo $txSiteId;?>"
                                    data-ref-no="<?php echo h($txRef);?>"
                                    data-description="<?php echo h($txDesc);?>"
                                    title="<?php echo __('Edit Voucher');?>"><i class="fa fa-pencil"></i> <?php echo __('Edit');?></button>
                            <?php
                            echo $this->Html->link(
                                '<i class="fa fa-print"></i> '.__('Print'),
                                array('controller'=>'Ardaccounts','action'=>'printvoucher',$id,(int)$row['Ardtransaction']['project_id']),
                                array('class'=>'btn btn-xs btn-info','target'=>'_blank','escape'=>false,'title'=>__('Print Voucher'))
                            );
                            ?>
                            <?php
                            echo $this->Html->link(
                                '<i class="fa fa-trash"></i> '.__('Delete'),
                                array('controller'=>'Ardaccounts','action'=>'deletetransaction',$id,$currentProjectId),
                                array('escape'=>false,'class'=>'btn btn-xs btn-danger','title'=>__('Delete Voucher'),'onclick'=>"return confirm('".__('Are you sure?')."');")
                            );
                            ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach;?>
                </tbody>
            </table>
        </div>
        <?php echo $this->element('pagination');?>
    </div>
</div>

<div class="modal fade" id="voucherModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="voucherModalTitle"><?php echo __('Add Voucher');?></h4>
            </div>
            <div class="modal-body">
                <?php echo $this->Form->create('Ardtransaction',array('id'=>'VoucherForm','url'=>array('controller'=>'Ardaccounts','action'=>'transactions',$currentProjectId),'class'=>'form-horizontal'));?>
                <?php echo $this->Form->hidden('id',array('id'=>'VoucherId'));?>
                <?php
                if($userType!='ADR')
                {
                    echo $this->Form->hidden('project_id',array('value'=>$agencyId,'id'=>'VoucherProjectId'));
                }
                ?>
                <?php if($userType=='ADR'){?>
                <div class="form-group">
                    <label class="col-sm-3 control-label"><?php echo __('Agency');?></label>
                    <div class="col-sm-9"><?php echo $this->Form->select('project_id',$projectName,array('id'=>'VoucherProjectId','empty'=>__('Please Select'),'class'=>'form-control','label'=>false,'div'=>false));?></div>
                </div>
                <?php }?>
                <div class="voucher-workflow-note" id="VoucherWorkflowNote"><span class="fa fa-lock"></span> <?php echo __('Approval workflow is active. Type and status are controlled automatically.');?></div>
                <div class="form-group">
                    <label class="col-sm-3 control-label"><?php echo __('Date');?></label>
                    <div class="col-sm-9">
                        <input type="text"
                               id="VoucherDate"
                               name="data[Ardtransaction][entry_date]"
                               class="form-control"
                               value="<?php echo h($todayDisplay);?>"
                               placeholder="<?php echo h(__('16-Jun-2026'));?>"
                               autocomplete="off" />
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label"><?php echo __('Asset/Bank From');?></label>
                    <div class="col-sm-9">
                        <?php echo $this->Form->select('ledger_id',$ledgerList,array('id'=>'VoucherLedger','empty'=>__('Please Select'),'class'=>'form-control','label'=>false,'div'=>false));?>
                        <?php if($userType=='ADR'){?><small class="help-block"><?php echo __('Ledger options are shown as Agency | Ledger (Type).');?></small><?php }?>
                    </div>
                </div>
                <div class="form-group" id="VoucherExpenseLedgerWrap">
                    <label class="col-sm-3 control-label"><?php echo __('Expense Account');?></label>
                    <div class="col-sm-9">
                        <?php echo $this->Form->select('expense_ledger_id',$expenseLedgerList,array('id'=>'VoucherExpenseLedger','empty'=>__('Please Select'),'class'=>'form-control','label'=>false,'div'=>false));?>
                        <?php if($userType=='ADR'){?><small class="help-block"><?php echo __('Expense account options are shown as Agency | Ledger (Type).');?></small><?php }?>
                    </div>
                </div>
                <div class="form-group" id="VoucherIncomeLedgerWrap">
                    <label class="col-sm-3 control-label"><?php echo __('Income Account');?></label>
                    <div class="col-sm-9">
                        <?php echo $this->Form->select('income_ledger_id',$ledgerList,array('id'=>'VoucherIncomeLedger','empty'=>__('Please Select'),'class'=>'form-control','label'=>false,'div'=>false));?>
                        <?php if($userType=='ADR'){?><small class="help-block"><?php echo __('Income account options are shown as Agency | Ledger (Type).');?></small><?php }?>
                    </div>
                </div>
                <div class="form-group" id="VoucherTypeWrap">
                    <label class="col-sm-3 control-label"><?php echo __('Type');?></label>
                    <div class="col-sm-9"><?php echo $this->Form->select('type',array('expense'=>__('Expense'),'income'=>__('Income')),array('id'=>'VoucherType','empty'=>false,'class'=>'form-control','label'=>false,'div'=>false));?><div class="voucher-fixed-value" id="VoucherTypeFixed"><span class="fa fa-minus-circle"></span>&nbsp; <?php echo __('Expense');?></div></div>
                </div>
                <div class="form-group" id="VoucherStatusWrap">
                    <label class="col-sm-3 control-label"><?php echo __('Status');?></label>
                    <div class="col-sm-9"><?php echo $this->Form->select('status',array('Draft'=>__('Draft'),'Approved'=>__('Approved'),'Posted'=>__('Posted'),'Void'=>__('Void'),'Reversed'=>__('Reversed')),array('id'=>'VoucherStatus','empty'=>false,'class'=>'form-control','label'=>false,'div'=>false));?><div class="voucher-fixed-value" id="VoucherStatusFixed"><span class="fa fa-clock-o"></span>&nbsp; <?php echo __('Pending Manager Approval');?></div></div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label"><?php echo __('Amount');?></label>
                    <div class="col-sm-9"><?php echo $this->Form->input('amount',array('id'=>'VoucherAmount','label'=>false,'div'=>false,'class'=>'form-control','placeholder'=>__('Amount')));?></div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label"><?php echo __('Stock Store');?></label>
                    <div class="col-sm-9"><?php echo $this->Form->select('construction_site_id',$siteList,array('id'=>'VoucherSite','empty'=>__('None'),'class'=>'form-control','label'=>false,'div'=>false));?></div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label"><?php echo __('Reference No');?></label>
                    <div class="col-sm-9"><?php echo $this->Form->input('ref_no',array('id'=>'VoucherRefNo','label'=>false,'div'=>false,'class'=>'form-control','placeholder'=>__('Reference No')));?></div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label"><?php echo __('Description');?></label>
                    <div class="col-sm-9"><?php echo $this->Form->input('description',array('id'=>'VoucherDescription','label'=>false,'div'=>false,'class'=>'form-control','placeholder'=>__('Description')));?></div>
                </div>
                <div class="form-group" id="voucherModalSubmitWrap">
                    <div class="col-sm-offset-3 col-sm-9">
                        <button type="submit" class="btn btn-success" id="voucherModalSubmit"><span class="fa fa-save"></span> <?php echo __('Save Voucher');?></button>
                    </div>
                </div>
                <?php echo $this->Form->end();?>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
jQuery(function($){
    var isSuperAdmin = <?php echo ($userType=='ADR' ? 'true' : 'false');?>;
    var defaultProjectId = '<?php echo ($userType=='ADR' ? (int)$currentProjectId : (int)$agencyId);?>';
    var defaultVoucherDate = '<?php echo addslashes($todayIso);?>';
    var defaultVoucherDateDisplay = '<?php echo addslashes($todayDisplay);?>';
    var approvalWorkflowEnabled = <?php echo $expenseApprovalEnabled ? 'true' : 'false';?>;
    var approvalWorkflowEnabledByProject = <?php echo json_encode((array)$expenseApprovalEnabledByProject);?>;
    var sourceLedgerAllMap = <?php echo json_encode((array)$ledgerList);?>;
    var sourceLedgerMapByProject = <?php echo json_encode((array)$ledgerListByProject);?>;
    var approvalSourceLedgerAllMap = <?php echo json_encode((array)$approvalSourceLedgerList);?>;
    var approvalSourceLedgerMapByProject = <?php echo json_encode((array)$approvalSourceLedgerListByProject);?>;
    var expenseLedgerAllMap = <?php echo json_encode((array)$expenseLedgerList);?>;
    var expenseLedgerMapByProject = <?php echo json_encode((array)$expenseLedgerListByProject);?>;
    var ledgerEmptyLabel = '<?php echo addslashes(__('Please Select'));?>';

    function normalizeVoucherDate(value)
    {
        var dateText = $.trim(String(value || ''));
        if(dateText === '' || dateText === '0000-00-00')
        {
            return defaultVoucherDateDisplay;
        }
        if(/^\d{4}-\d{2}-\d{2}$/.test(dateText))
        {
            var iso = dateText.split('-');
            var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
            return iso[2] + '-' + months[parseInt(iso[1],10)-1] + '-' + iso[0];
        }
        if(/^\d{2}-[A-Za-z]{3}-\d{4}$/.test(dateText))
        {
            return dateText;
        }
        var parsed = new Date(dateText.replace(/-/g, ' '));
        if(!isNaN(parsed.getTime()))
        {
            var day = ('0' + parsed.getDate()).slice(-2);
            var parsedMonths = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
            return day + '-' + parsedMonths[parsed.getMonth()] + '-' + parsed.getFullYear();
        }
        return defaultVoucherDateDisplay;
    }

    function replaceVoucherSelectOptions($select, optionMap, selectedValue)
    {
        $select.empty().append($('<option/>').attr('value','').text(ledgerEmptyLabel));
        $.each(optionMap || {}, function(id, label){
            $select.append($('<option/>').attr('value', String(id)).text(label));
        });
        $select.val(String(selectedValue || ''));
        if($select.val() === null)
        {
            $select.val('');
        }
    }

    function refreshVoucherLedgerOptions(projectId, selectedLedgerId)
    {
        if(!isSuperAdmin)
        {
            var localSelected = String(selectedLedgerId || '');
            if(localSelected !== '')
            {
                $('#VoucherLedger').val(localSelected);
                if($('#VoucherLedger').val() === null)
                {
                    $('#VoucherLedger').val('');
                }
            }
            return;
        }
        var key = String(projectId || '');
        var scopedMap = sourceLedgerAllMap;
        if(
            key !== '' &&
            typeof sourceLedgerMapByProject[key] !== 'undefined' &&
            Object.keys(sourceLedgerMapByProject[key]).length > 0
        )
        {
            scopedMap = sourceLedgerMapByProject[key];
        }
        var $ledger = $('#VoucherLedger');
        $ledger.empty();
        $ledger.append($('<option/>').attr('value','').text(ledgerEmptyLabel));
        $.each(scopedMap, function(id, label){
            $ledger.append($('<option/>').attr('value', String(id)).text(label));
        });
        var selected = String(selectedLedgerId || '');
        if(selected !== '')
        {
            $ledger.val(selected);
            if($ledger.val() === null)
            {
                $ledger.val('');
            }
        }
        else
        {
            $ledger.val('');
        }
    }

    function refreshExpenseLedgerOptions(projectId, selectedLedgerId)
    {
        if(!isSuperAdmin)
        {
            var localSelected = String(selectedLedgerId || '');
            if(localSelected !== '')
            {
                $('#VoucherExpenseLedger').val(localSelected);
                if($('#VoucherExpenseLedger').val() === null)
                {
                    $('#VoucherExpenseLedger').val('');
                }
            }
            return;
        }
        var key = String(projectId || '');
        var scopedMap = expenseLedgerAllMap;
        if(
            key !== '' &&
            typeof expenseLedgerMapByProject[key] !== 'undefined' &&
            Object.keys(expenseLedgerMapByProject[key]).length > 0
        )
        {
            scopedMap = expenseLedgerMapByProject[key];
        }
        var $ledger = $('#VoucherExpenseLedger');
        $ledger.empty();
        $ledger.append($('<option/>').attr('value','').text(ledgerEmptyLabel));
        $.each(scopedMap, function(id, label){
            $ledger.append($('<option/>').attr('value', String(id)).text(label));
        });
        var selected = String(selectedLedgerId || '');
        if(selected !== '')
        {
            $ledger.val(selected);
            if($ledger.val() === null)
            {
                $ledger.val('');
            }
        }
        else
        {
            $ledger.val('');
        }
    }

    function refreshIncomeLedgerOptions(projectId, selectedLedgerId)
    {
        if(!isSuperAdmin)
        {
            var localSelected = String(selectedLedgerId || '');
            if(localSelected !== '')
            {
                $('#VoucherIncomeLedger').val(localSelected);
                if($('#VoucherIncomeLedger').val() === null)
                {
                    $('#VoucherIncomeLedger').val('');
                }
            }
            return;
        }
        var key = String(projectId || '');
        var scopedMap = sourceLedgerAllMap;
        if(
            key !== '' &&
            typeof sourceLedgerMapByProject[key] !== 'undefined' &&
            Object.keys(sourceLedgerMapByProject[key]).length > 0
        )
        {
            scopedMap = sourceLedgerMapByProject[key];
        }
        var $ledger = $('#VoucherIncomeLedger');
        $ledger.empty();
        $ledger.append($('<option/>').attr('value','').text(ledgerEmptyLabel));
        $.each(scopedMap, function(id, label){
            $ledger.append($('<option/>').attr('value', String(id)).text(label));
        });
        var selected = String(selectedLedgerId || '');
        if(selected !== '')
        {
            $ledger.val(selected);
            if($ledger.val() === null)
            {
                $ledger.val('');
            }
        }
        else
        {
            $ledger.val('');
        }
    }

    function ensureSelectOption($select, value, label)
    {
        var v = String(value || '');
        if(v === '')
        {
            return;
        }
        if($select.find('option[value=\"' + v.replace(/\"/g, '\\\\\"') + '\"]').length === 0)
        {
            $select.append($('<option/>').attr('value', v).text(label || ('#' + v)));
        }
    }

    function toggleExpenseLedgerField(typeValue)
    {
        var kind = String(typeValue || '').toLowerCase();
        if(kind === 'expense')
        {
            $('#VoucherExpenseLedgerWrap').show();
            $('#VoucherIncomeLedgerWrap').hide();
            $('#VoucherIncomeLedger').val('');
        }
        else
        {
            $('#VoucherExpenseLedgerWrap').hide();
            $('#VoucherExpenseLedger').val('');
            $('#VoucherIncomeLedgerWrap').show();
        }
    }

    function setVoucherReadonly(isReadonly)
    {
        var $fields = $('#VoucherForm').find('input,select,textarea');
        $fields.prop('disabled', false);
        if(isReadonly)
        {
            $fields.not('#VoucherId').prop('disabled', true);
            $('#voucherModalSubmitWrap').hide();
        }
        else
        {
            $('#voucherModalSubmitWrap').show();
        }
        $('#VoucherId').prop('disabled', false);
        if($('#VoucherProjectId').attr('type') === 'hidden')
        {
            $('#VoucherProjectId').prop('disabled', false);
        }
    }

    function setApprovalExpenseMode(isApprovalExpense)
    {
        $('#VoucherWorkflowNote').toggle(isApprovalExpense);
        $('#VoucherTypeFixed,#VoucherStatusFixed').css('display', isApprovalExpense ? 'flex' : 'none');
        $('#VoucherType,#VoucherStatus').toggle(!isApprovalExpense);
        if(isApprovalExpense)
        {
            var projectId = String($('#VoucherProjectId').val() || defaultProjectId || '');
            var approvalMap = approvalSourceLedgerAllMap;
            if(projectId !== '' && approvalSourceLedgerMapByProject[projectId])
            {
                approvalMap = approvalSourceLedgerMapByProject[projectId];
            }
            replaceVoucherSelectOptions($('#VoucherLedger'), approvalMap, '');
            $('#VoucherType').val('expense');
            $('#VoucherStatus').val('Draft');
            $('#VoucherIncomeLedgerWrap').hide();
            $('#VoucherIncomeLedger').val('');
        }
    }

    function projectUsesExpenseApproval(projectId)
    {
        var key = String(projectId || '');
        if(key !== '' && typeof approvalWorkflowEnabledByProject[key] !== 'undefined')
        {
            return parseInt(approvalWorkflowEnabledByProject[key], 10) === 1;
        }
        return approvalWorkflowEnabled;
    }

    function openVoucherModal(mode, data)
    {
        data = data || {};
        var title = '<?php echo addslashes(__('Add Voucher'));?>';
        if(mode === 'edit'){ title = '<?php echo addslashes(__('Edit Voucher'));?>'; }
        if(mode === 'view'){ title = '<?php echo addslashes(__('View Voucher'));?>'; }
        $('#voucherModalTitle').text(title);

        $('#VoucherId').val(data.id || '');
        var resolvedProjectId = String(data.project_id || '');
        if(resolvedProjectId === '' && defaultProjectId !== '0')
        {
            resolvedProjectId = String(defaultProjectId);
        }
        if($('#VoucherProjectId').length){ $('#VoucherProjectId').val(resolvedProjectId); }
        refreshVoucherLedgerOptions(resolvedProjectId, data.ledger_id || '');
        refreshExpenseLedgerOptions(resolvedProjectId, data.expense_ledger_id || '');
        refreshIncomeLedgerOptions(resolvedProjectId, data.income_ledger_id || '');
        ensureSelectOption($('#VoucherLedger'), data.ledger_id || '', data.ledger_name || ('Ledger #' + String(data.ledger_id || '')));
        ensureSelectOption($('#VoucherExpenseLedger'), data.expense_ledger_id || '', data.expense_ledger_name || ('Expense Ledger #' + String(data.expense_ledger_id || '')));
        ensureSelectOption($('#VoucherIncomeLedger'), data.income_ledger_id || '', data.income_ledger_name || ('Income Ledger #' + String(data.income_ledger_id || '')));
        $('#VoucherDate').val(normalizeVoucherDate(data.entry_date));
        var normalizedType = String(data.type || 'expense').toLowerCase();
        if(normalizedType !== 'income' && normalizedType !== 'expense')
        {
            normalizedType = 'expense';
        }
        $('#VoucherType').val(normalizedType);
        toggleExpenseLedgerField($('#VoucherType').val());
        if(String(data.expense_ledger_id || '') !== '')
        {
            $('#VoucherExpenseLedger').val(String(data.expense_ledger_id));
        }
        if(String(data.income_ledger_id || '') !== '')
        {
            $('#VoucherIncomeLedger').val(String(data.income_ledger_id));
        }
        $('#VoucherStatus').val(data.status || 'Posted');
        $('#VoucherAmount').val(data.amount || '');
        $('#VoucherSite').val(data.construction_site_id || '');
        $('#VoucherRefNo').val(data.ref_no || '');
        $('#VoucherDescription').val(data.description || '');

        var isApprovalExpense = projectUsesExpenseApproval(resolvedProjectId) && mode === 'add' && normalizedType === 'expense';
        setApprovalExpenseMode(isApprovalExpense);

        setVoucherReadonly(mode === 'view');
        if(mode === 'edit')
        {
            $('#voucherModalSubmit').html('<span class="fa fa-save"></span> <?php echo addslashes(__('Update Voucher'));?>');
        }
        else if(isApprovalExpense)
        {
            $('#voucherModalSubmit').html('<span class="fa fa-paper-plane"></span> <?php echo addslashes(__('Submit for Approval'));?>');
        }
        else
        {
            $('#voucherModalSubmit').html('<span class="fa fa-save"></span> <?php echo addslashes(__('Save Voucher'));?>');
        }
        $('#voucherModal').modal('show');
    }

    $('.js-voucher-add').on('click', function(e){
        e.preventDefault();
        var kind = ($(this).attr('data-kind') || '').toLowerCase();
        if(kind !== 'income' && kind !== 'expense'){ kind = 'expense'; }
        openVoucherModal('add', {
            id: '',
            project_id: (defaultProjectId === '0' ? '' : defaultProjectId),
            entry_date: defaultVoucherDateDisplay,
            ledger_id: '',
            expense_ledger_id: '',
            income_ledger_id: '',
            type: kind,
            status: 'Posted',
            amount: '',
            construction_site_id: '',
            ref_no: '',
            description: ''
        });
    });

    $('.js-voucher-open').on('click', function(e){
        e.preventDefault();
        var $btn = $(this);
        openVoucherModal($btn.attr('data-mode'), {
            id: $btn.attr('data-id') || '',
            project_id: $btn.attr('data-project-id') || '',
            entry_date: $btn.attr('data-entry-date') || defaultVoucherDate,
            ledger_id: $btn.attr('data-ledger-id') || '',
            ledger_name: $btn.attr('data-ledger-name') || '',
            expense_ledger_id: $btn.attr('data-expense-ledger-id') || '',
            expense_ledger_name: $btn.attr('data-expense-ledger-name') || '',
            income_ledger_id: $btn.attr('data-income-ledger-id') || '',
            income_ledger_name: $btn.attr('data-income-ledger-name') || '',
            type: $btn.attr('data-type') || 'expense',
            status: $btn.attr('data-status') || 'Posted',
            amount: $btn.attr('data-amount') || '',
            construction_site_id: $btn.attr('data-site-id') || '',
            ref_no: $btn.attr('data-ref-no') || '',
            description: $btn.attr('data-description') || ''
        });
    });

    $('#VoucherProjectId').on('change', function(){
        refreshVoucherLedgerOptions($(this).val(), '');
        refreshExpenseLedgerOptions($(this).val(), '');
        refreshIncomeLedgerOptions($(this).val(), '');
        var isApprovalExpense = projectUsesExpenseApproval($(this).val()) && String($('#VoucherId').val() || '') === '' && $('#VoucherType').val() === 'expense';
        setApprovalExpenseMode(isApprovalExpense);
        $('#voucherModalSubmit').html(isApprovalExpense ? '<span class="fa fa-paper-plane"></span> <?php echo addslashes(__('Submit for Approval'));?>' : '<span class="fa fa-save"></span> <?php echo addslashes(__('Save Voucher'));?>');
    });
    $('#VoucherType').on('change', function(){
        toggleExpenseLedgerField($(this).val());
    });
    // Filter bar preset auto-population & quick buttons
    function ardFormatDate(d) {
        var y = d.getFullYear();
        var m = ('0' + (d.getMonth() + 1)).slice(-2);
        var day = ('0' + d.getDate()).slice(-2);
        return y + '-' + m + '-' + day;
    }

    $('#filterDatePreset').on('change', function(){
        var preset = $(this).val();
        var today = new Date();
        if(preset === 'today') {
            var str = ardFormatDate(today);
            $('#filterStartDate').val(str);
            $('#filterEndDate').val(str);
        } else if(preset === 'week') {
            var dayOfWeek = today.getDay(); // 0 is Sun, 1 is Mon
            var diff = dayOfWeek === 0 ? -6 : 1 - dayOfWeek;
            var mon = new Date(today);
            mon.setDate(today.getDate() + diff);
            var sun = new Date(mon);
            sun.setDate(mon.getDate() + 6);
            $('#filterStartDate').val(ardFormatDate(mon));
            $('#filterEndDate').val(ardFormatDate(sun));
        } else if(preset === 'month') {
            var firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
            var lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
            $('#filterStartDate').val(ardFormatDate(firstDay));
            $('#filterEndDate').val(ardFormatDate(lastDay));
        } else if(preset === 'year') {
            var firstDayYear = new Date(today.getFullYear(), 0, 1);
            var lastDayYear = new Date(today.getFullYear(), 11, 31);
            $('#filterStartDate').val(ardFormatDate(firstDayYear));
            $('#filterEndDate').val(ardFormatDate(lastDayYear));
        } else if(preset === 'all') {
            $('#filterStartDate').val('');
            $('#filterEndDate').val('');
        }
        $('.js-preset-btn').removeClass('active');
        $('.js-preset-btn[data-preset="' + preset + '"]').addClass('active');
    });

    $('.js-preset-btn').on('click', function(e){
        e.preventDefault();
        var preset = $(this).attr('data-preset');
        $('#filterDatePreset').val(preset).trigger('change');
        $('#txFilterForm').submit();
    });

    $('#filterStartDate, #filterEndDate').on('change input', function(){
        if($('#filterStartDate').val() || $('#filterEndDate').val()) {
            $('#filterDatePreset').val('custom');
            $('.js-preset-btn').removeClass('active');
        }
    });

    if($.fn.datepicker)
    {
        $('#VoucherDate').datepicker({dateFormat:'dd-M-yy',changeMonth:true,changeYear:true});
        $('.js-ard-datepicker').datepicker({
            dateFormat: 'yy-mm-dd',
            changeMonth: true,
            changeYear: true,
            showAnim: 'fadeIn'
        });
    }
    else if($.fn.datetimepicker)
    {
        $('.js-ard-datepicker').datetimepicker({
            format: 'YYYY-MM-DD',
            showTodayButton: true,
            showClear: true
        });
    }

    $('.ard-datepicker-wrap').on('click', '.ard-input-icon', function(e){
        e.stopPropagation();
        var $inp = $(this).siblings('input.js-ard-datepicker');
        if($inp.data('DateTimePicker')) {
            $inp.data('DateTimePicker').show();
        } else {
            $inp.focus();
        }
    });

    <?php if($openOnLoad){?>
    openVoucherModal('<?php echo $openMode;?>', <?php echo json_encode($initialVoucher);?>);
    <?php }?>
});
</script>
