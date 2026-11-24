<?php
$this->Js->JqueryEngine->jQueryObject = 'jQuery';
$formMode = isset($ledgerFormMode) ? strtolower((string)$ledgerFormMode) : 'add';
$isView = ($formMode === 'view');
$isEdit = ($formMode === 'edit');

$assetRows = isset($assetRows) ? $assetRows : array();
$expenseRows = isset($expenseRows) ? $expenseRows : array();
$ledgerEntryCounts = isset($ledgerEntryCounts) ? $ledgerEntryCounts : array();
$projectNameMap = (isset($projectName) && is_array($projectName)) ? $projectName : array();
$ledgerAgencyOptions = (isset($ledgerAgencyOptions) && is_array($ledgerAgencyOptions)) ? $ledgerAgencyOptions : $projectNameMap;
$canFilterLedgerAgency = !empty($canFilterLedgerAgency);
$selectedLedgerAgencyId = isset($selectedLedgerAgencyId) ? (int)$selectedLedgerAgencyId : (int)$currentProjectId;
$ledgerPaginatorOptions = array('update' => '#resultDiv','evalScripts' => true);
if($canFilterLedgerAgency)
{
    $ledgerPaginatorOptions['url'] = array('?' => array('project_id' => ($selectedLedgerAgencyId > 0 ? $selectedLedgerAgencyId : '')));
}
$this->Paginator->options($ledgerPaginatorOptions);
$ledgerCategoryOptionsByProject = (isset($ledgerCategoryOptionsByProject) && is_array($ledgerCategoryOptionsByProject)) ? $ledgerCategoryOptionsByProject : array();
$ledgerAssetCategoryOptions = (isset($ledgerAssetCategoryOptions) && is_array($ledgerAssetCategoryOptions)) ? $ledgerAssetCategoryOptions : array();
$ledgerExpenseCategoryOptions = (isset($ledgerExpenseCategoryOptions) && is_array($ledgerExpenseCategoryOptions)) ? $ledgerExpenseCategoryOptions : array();
$employeeLedgerMetaByLedgerId = (isset($employeeLedgerMetaByLedgerId) && is_array($employeeLedgerMetaByLedgerId)) ? $employeeLedgerMetaByLedgerId : array();
$hrLedgerSectionLabel = __('HR Employees');
$labourLedgerSectionLabel = __('Labour Employees');
$uncategorizedLedgerLabel = __('Uncategorized');

$initialLedger = array(
    'id' => '',
    'project_id' => ($userType=='ADR' ? '' : (int)$agencyId),
    'name' => '',
    'type' => 'bank',
    'category' => '',
    'account_no' => '',
    'mobile_no' => '',
    'address' => ''
);
if(!empty($this->request->data['Ardledger']))
{
    $initialLedger = array(
        'id' => isset($this->request->data['Ardledger']['id']) ? (int)$this->request->data['Ardledger']['id'] : '',
        'project_id' => isset($this->request->data['Ardledger']['project_id']) ? (int)$this->request->data['Ardledger']['project_id'] : ($userType=='ADR' ? '' : (int)$agencyId),
        'name' => isset($this->request->data['Ardledger']['name']) ? (string)$this->request->data['Ardledger']['name'] : '',
        'type' => isset($this->request->data['Ardledger']['type']) ? (string)$this->request->data['Ardledger']['type'] : 'bank',
        'category' => isset($this->request->data['Ardledger']['category']) ? (string)$this->request->data['Ardledger']['category'] : '',
        'account_no' => isset($this->request->data['Ardledger']['account_no']) ? (string)$this->request->data['Ardledger']['account_no'] : '',
        'mobile_no' => isset($this->request->data['Ardledger']['mobile_no']) ? (string)$this->request->data['Ardledger']['mobile_no'] : '',
        'address' => isset($this->request->data['Ardledger']['address']) ? (string)$this->request->data['Ardledger']['address'] : ''
    );
}
$initialType = strtolower(trim((string)(isset($initialLedger['type']) ? $initialLedger['type'] : 'bank')));
$initialCategoryOptions = ($initialType === 'expense') ? $ledgerExpenseCategoryOptions : $ledgerAssetCategoryOptions;
$groupLedgerRowsByCategory = function($rows) use ($employeeLedgerMetaByLedgerId, $hrLedgerSectionLabel, $labourLedgerSectionLabel, $uncategorizedLedgerLabel){
    $groupedRows = array();
    foreach((array)$rows as $row)
    {
        $ledgerId = isset($row['Ardledger']['id']) ? (int)$row['Ardledger']['id'] : 0;
        $employeeMeta = ($ledgerId > 0 && isset($employeeLedgerMetaByLedgerId[$ledgerId])) ? (array)$employeeLedgerMetaByLedgerId[$ledgerId] : array();
        $categoryLabel = trim((string)(isset($row['Ardledger']['category']) ? $row['Ardledger']['category'] : ''));
        if(!empty($employeeMeta))
        {
            $salaryType = strtolower(trim((string)(isset($employeeMeta['salary_type']) ? $employeeMeta['salary_type'] : 'monthly')));
            $categoryLabel = ($salaryType === 'daily') ? $labourLedgerSectionLabel : $hrLedgerSectionLabel;
        }
        if($categoryLabel === '')
        {
            $categoryLabel = $uncategorizedLedgerLabel;
        }
        if(!isset($groupedRows[$categoryLabel]))
        {
            $groupedRows[$categoryLabel] = array();
        }
        $groupedRows[$categoryLabel][] = $row;
    }
    foreach($groupedRows as $groupLabel => $groupRows)
    {
        usort($groupRows, function($leftRow, $rightRow) use ($employeeLedgerMetaByLedgerId){
            $leftLedgerId = isset($leftRow['Ardledger']['id']) ? (int)$leftRow['Ardledger']['id'] : 0;
            $rightLedgerId = isset($rightRow['Ardledger']['id']) ? (int)$rightRow['Ardledger']['id'] : 0;
            $leftMeta = ($leftLedgerId > 0 && isset($employeeLedgerMetaByLedgerId[$leftLedgerId])) ? (array)$employeeLedgerMetaByLedgerId[$leftLedgerId] : array();
            $rightMeta = ($rightLedgerId > 0 && isset($employeeLedgerMetaByLedgerId[$rightLedgerId])) ? (array)$employeeLedgerMetaByLedgerId[$rightLedgerId] : array();
            $leftName = trim((string)(isset($leftMeta['full_name']) ? $leftMeta['full_name'] : ''));
            $rightName = trim((string)(isset($rightMeta['full_name']) ? $rightMeta['full_name'] : ''));
            if($leftName === '')
            {
                $leftName = trim((string)(isset($leftRow['Ardledger']['name']) ? $leftRow['Ardledger']['name'] : ''));
            }
            if($rightName === '')
            {
                $rightName = trim((string)(isset($rightRow['Ardledger']['name']) ? $rightRow['Ardledger']['name'] : ''));
            }
            return strcasecmp($leftName, $rightName);
        });
        $groupedRows[$groupLabel] = $groupRows;
    }
    $sectionOrder = array(
        $uncategorizedLedgerLabel => 900,
        $hrLedgerSectionLabel => 901,
        $labourLedgerSectionLabel => 902
    );
    uksort($groupedRows, function($leftLabel, $rightLabel) use ($sectionOrder){
        $leftOrder = isset($sectionOrder[$leftLabel]) ? (int)$sectionOrder[$leftLabel] : 50;
        $rightOrder = isset($sectionOrder[$rightLabel]) ? (int)$sectionOrder[$rightLabel] : 50;
        if($leftOrder === $rightOrder)
        {
            return strcasecmp((string)$leftLabel, (string)$rightLabel);
        }
        return ($leftOrder < $rightOrder) ? -1 : 1;
    });
    return $groupedRows;
};
$assetRowsByCategory = $groupLedgerRowsByCategory($assetRows);
$expenseRowsByCategory = $groupLedgerRowsByCategory($expenseRows);
?>
<style type="text/css">
.ard-ledgers-wrap .panel-body{background:#f3f6fb;}
.ard-ledger-card{background:#fff;border:1px solid #d8e1ef;border-radius:12px;margin-bottom:14px;overflow:hidden;}
.ard-ledger-card-head{padding:12px 14px;border-bottom:1px solid #e8edf6;font-weight:700;color:#1f3556;}
.ard-ledger-card-head .badge{background:#e9f1ff;color:#2e4f82;font-size:11px;}
.ard-ledger-card-head .btn{padding:2px 8px;font-size:11px;}
.ard-ledger-card-body{padding:14px;}
.ard-ledger-category{border:1px solid #dfe7f5;border-radius:9px;background:#f9fbff;margin-bottom:10px;overflow:hidden;}
.ard-ledger-category:last-child{margin-bottom:0;}
.ard-ledger-category-head a{display:block;padding:8px 10px;color:#27446d;font-weight:700;font-size:12px;text-decoration:none;}
.ard-ledger-category-head a:hover,.ard-ledger-category-head a:focus{background:#f1f6ff;text-decoration:none;}
.ard-ledger-category-body{padding:10px;}
.ard-ledger-item{border:1px solid #e6edf8;border-radius:10px;padding:10px 12px;margin-bottom:10px;background:#fcfdff;}
.ard-ledger-item:last-child{margin-bottom:0;}
.ard-ledger-item-top{display:flex;justify-content:space-between;align-items:flex-start;gap:8px;}
.ard-ledger-name{font-weight:700;color:#233a5c;font-size:13px;line-height:1.35;}
.ard-ledger-agency{font-size:11px;color:#233a5c;opacity:.6;line-height:1.35;margin-top:2px;}
.ard-ledger-meta{margin-top:3px;color:#6f7f96;font-size:11px;line-height:1.45;}
.ard-ledger-balance{font-size:14px;font-weight:700;color:#1f3556;text-align:right;white-space:nowrap;}
.ard-ledger-actions{margin-top:7px;text-align:right;}
.ard-ledger-actions .btn{padding:2px 7px;font-size:11px;border-radius:15px;}
.ard-ledger-empty{padding:26px 10px;color:#7e8ea6;text-align:center;font-size:12px;border:1px dashed #d0dceb;border-radius:10px;background:#fafcff;}
.ard-ledger-filter{display:flex;align-items:flex-end;gap:10px;margin:0 0 14px;padding:12px 14px;background:#fff;border:1px solid #d8e1ef;border-radius:8px;}
.ard-ledger-filter-field{min-width:260px;}
.ard-ledger-filter label{display:block;margin:0 0 5px;color:#233a5c;font-size:11px;font-weight:700;}
.ard-ledger-filter .form-control{height:38px;}
#ledgerModal .modal-header{background:#f7f9fc;border-bottom:1px solid #e8edf6;}
#ledgerModal .modal-title{font-weight:700;color:#203a5d;}
@media print{
    .btn-group,.ard-ledger-filter,.ard-ledger-actions,.pagination,#ledgerModal,.modal,.page-title .description{display:none !important;}
    .ard-ledgers-wrap .panel-body{background:#fff !important;}
    .ard-ledger-card,.ard-ledger-category,.ard-ledger-item{break-inside:avoid;page-break-inside:avoid;}
    #resultDiv{margin:0 !important;padding:0 !important;}
}
@media (max-width:991px){
    .ard-ledger-filter{align-items:stretch;flex-direction:column;}
    .ard-ledger-filter-field{min-width:0;width:100%;}
    .ard-ledger-item-top{display:block;}
    .ard-ledger-balance,.ard-ledger-actions{text-align:left;}
}
</style>

<div id="resultDiv">
    <div class="page-title">
        <div class="title-env">
            <h1 class="title"><?php echo __('ARD Ledgers');?></h1>
            <p class="description"><?php echo __('Manage asset and expense account books');?></p>
        </div>
    </div>

    <div class="panel ard-ledgers-wrap">
        <?php echo $this->Session->flash();?>
        <div class="panel-heading">
            <div class="btn-group">
                <?php echo $this->Html->link('<span class="fa fa-home"></span>&nbsp;'.__('ARD Dashboard'),array('controller'=>'Ardaccounts','action'=>'dashboard',$currentProjectId),array('escape'=>false,'class'=>'btn btn-default'));?>
                <?php echo $this->Html->link('<span class="fa fa-money"></span>&nbsp;'.__('Vouchers'),array('controller'=>'Ardaccounts','action'=>'transactions',$currentProjectId),array('escape'=>false,'class'=>'btn btn-success'));?>
                <a href="javascript:void(0);" class="btn btn-info js-ledger-add"><span class="fa fa-plus"></span>&nbsp;<?php echo __('Add Ledger');?></a>
                <a href="javascript:void(0);" class="btn btn-primary" id="LedgerPrintBtn"><span class="fa fa-print"></span>&nbsp;<?php echo __('Print');?></a>
            </div>
        </div>

        <div class="panel-body">
            <?php if($canFilterLedgerAgency){?>
            <form method="get" class="ard-ledger-filter no-print" action="<?php echo $this->Html->url(array('controller'=>'Ardaccounts','action'=>'ledgers'));?>">
                <div class="ard-ledger-filter-field">
                    <label for="LedgerAgencyFilter"><?php echo __('Agency');?></label>
                    <select name="project_id" id="LedgerAgencyFilter" class="form-control">
                        <option value=""><?php echo __('All Agencies');?></option>
                        <?php foreach($ledgerAgencyOptions as $filterAgencyId => $filterAgencyName){?>
                            <option value="<?php echo (int)$filterAgencyId;?>"<?php echo ((int)$filterAgencyId === $selectedLedgerAgencyId) ? ' selected="selected"' : '';?>><?php echo h($filterAgencyName);?></option>
                        <?php }?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary"><span class="fa fa-filter"></span>&nbsp;<?php echo __('Apply');?></button>
                <?php echo $this->Html->link('<span class="fa fa-refresh"></span>&nbsp;'.__('Reset'),array('controller'=>'Ardaccounts','action'=>'ledgers','?' => array('project_id'=>'')),array('escape'=>false,'class'=>'btn btn-default'));?>
            </form>
            <?php }?>
            <div class="row">
                <div class="col-md-6">
                    <div class="ard-ledger-card">
                        <div class="ard-ledger-card-head">
                            <?php echo __('Asset Ledgers');?>
                            <span class="badge pull-right"><?php echo count($assetRows);?></span>
                        </div>
                        <div class="ard-ledger-card-body">
                            <?php if(empty($assetRows)){?>
                                <div class="ard-ledger-empty"><?php echo __('No asset ledgers found on this page.');?></div>
                            <?php }else{?>
                                <div id="AssetLedgerCategoryAccordion">
                                    <?php $assetCategoryIndex = 0; foreach((array)$assetRowsByCategory as $assetCategoryLabel => $assetCategoryRows){
                                        $assetCategoryIndex++;
                                        $assetCategoryPanelId = 'assetLedgerCategory_'.$assetCategoryIndex;
                                        $assetCategoryOpen = ($assetCategoryIndex === 1);
                                    ?>
                                    <div class="ard-ledger-category">
                                        <div class="ard-ledger-category-head">
                                            <a href="#<?php echo $assetCategoryPanelId;?>" data-toggle="collapse" data-parent="#AssetLedgerCategoryAccordion" aria-expanded="<?php echo $assetCategoryOpen ? 'true' : 'false';?>">
                                                <?php echo h($assetCategoryLabel);?>
                                                <span class="badge pull-right"><?php echo count((array)$assetCategoryRows);?></span>
                                            </a>
                                        </div>
                                        <div id="<?php echo $assetCategoryPanelId;?>" class="panel-collapse collapse <?php echo $assetCategoryOpen ? 'in' : '';?>">
                                            <div class="ard-ledger-category-body">
                                                <?php foreach((array)$assetCategoryRows as $row){
                                                    $id = (int)$row['Ardledger']['id'];
                                                    $agencyLabel = '';
                                                    if($canFilterLedgerAgency)
                                                    {
                                                        $projectId = (int)$row['Ardledger']['project_id'];
                                                        if(isset($projectNameMap[$projectId]))$agencyLabel = (string)$projectNameMap[$projectId];
                                                        elseif(isset($projectNameMap[(string)$projectId]))$agencyLabel = (string)$projectNameMap[(string)$projectId];
                                                        if($agencyLabel==='' && $projectId>0)$agencyLabel = __('Agency').' #'.$projectId;
                                                    }
                                                    $entryCount = isset($ledgerEntryCounts[$id]) ? (int)$ledgerEntryCounts[$id] : 0;
                                                    $employeeLedgerMeta = isset($employeeLedgerMetaByLedgerId[$id]) ? (array)$employeeLedgerMetaByLedgerId[$id] : array();
                                                    $rawLedgerName = trim((string)$row['Ardledger']['name']);
                                                    $displayLedgerName = $rawLedgerName;
                                                    if(!empty($employeeLedgerMeta['full_name']))
                                                    {
                                                        $displayLedgerName = trim((string)$employeeLedgerMeta['full_name']);
                                                        $employeeCode = trim((string)(isset($employeeLedgerMeta['employee_code']) ? $employeeLedgerMeta['employee_code'] : ''));
                                                        if($employeeCode !== '')
                                                        {
                                                            $displayLedgerName .= ' ['.$employeeCode.']';
                                                        }
                                                    }
                                                    $metaParts = array();
                                                    if(strlen(trim((string)$row['Ardledger']['account_no']))>0){$metaParts[] = __('A/C ').h($row['Ardledger']['account_no']);}
                                                    if(strlen(trim((string)$row['Ardledger']['mobile_no']))>0){$metaParts[] = h($row['Ardledger']['mobile_no']);}
                                                    if(!empty($employeeLedgerMeta))
                                                    {
                                                        $employeeSalaryType = strtolower(trim((string)(isset($employeeLedgerMeta['salary_type']) ? $employeeLedgerMeta['salary_type'] : 'monthly')));
                                                        $metaParts[] = __('Type').': '.(($employeeSalaryType === 'daily') ? __('Labour') : __('HR'));
                                                    }
                                                    if($displayLedgerName !== '' && $rawLedgerName !== '' && strcasecmp($displayLedgerName, $rawLedgerName) !== 0)
                                                    {
                                                        $metaParts[] = __('Ledger').': '.h($rawLedgerName);
                                                    }
                                                    $metaParts[] = __('Entries').': '.$entryCount;
                                                ?>
                                                <div class="ard-ledger-item">
                                                    <div class="ard-ledger-item-top">
                                                        <div>
                                                            <div class="ard-ledger-name"><?php echo h($displayLedgerName);?></div>
                                                            <?php if($agencyLabel!==''){?><div class="ard-ledger-agency"><?php echo __('Agency');?>: <?php echo h($agencyLabel);?></div><?php }?>
                                                            <div class="ard-ledger-meta"><?php echo implode(' | ',$metaParts);?></div>
                                                        </div>
                                                        <div>
                                                            <div class="ard-ledger-balance"><?php echo $currency.$this->Number->format($row['Ardledger']['balance']);?></div>
                                                            <div class="ard-ledger-actions">
                                                                <button type="button" class="btn btn-default btn-xs js-ledger-open" data-mode="view" data-id="<?php echo $id;?>" data-project-id="<?php echo (int)$row['Ardledger']['project_id'];?>" data-name="<?php echo h($row['Ardledger']['name']);?>" data-type="<?php echo h($row['Ardledger']['type']);?>" data-category="<?php echo h($row['Ardledger']['category']);?>" data-account-no="<?php echo h($row['Ardledger']['account_no']);?>" data-mobile-no="<?php echo h($row['Ardledger']['mobile_no']);?>" data-address="<?php echo h($row['Ardledger']['address']);?>"><?php echo __('View');?></button>
                                                                <?php echo $this->Html->link(__('Statement'),array('controller'=>'Ardaccounts','action'=>'ledgerstatement',$id,$currentProjectId),array('class'=>'btn btn-info btn-xs'));?>
                                                                <button type="button" class="btn btn-warning btn-xs js-ledger-open" data-mode="edit" data-id="<?php echo $id;?>" data-project-id="<?php echo (int)$row['Ardledger']['project_id'];?>" data-name="<?php echo h($row['Ardledger']['name']);?>" data-type="<?php echo h($row['Ardledger']['type']);?>" data-category="<?php echo h($row['Ardledger']['category']);?>" data-account-no="<?php echo h($row['Ardledger']['account_no']);?>" data-mobile-no="<?php echo h($row['Ardledger']['mobile_no']);?>" data-address="<?php echo h($row['Ardledger']['address']);?>"><?php echo __('Edit');?></button>
                                                                <?php if($entryCount>0){?>
                                                                    <button type="button" class="btn btn-default btn-xs" disabled="disabled" title="<?php echo __('Cannot delete: entries found in this ledger');?>"><?php echo __('Delete Locked');?></button>
                                                                <?php }else{?>
                                                                    <?php echo $this->Html->link(__('Delete'),array('controller'=>'Ardaccounts','action'=>'deleteledger',$id,$currentProjectId),array('class'=>'btn btn-danger btn-xs','onclick'=>"return confirm('".__('Are you sure?')."');"));?>
                                                                <?php }?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php }?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php }?>
                                </div>
                            <?php }?>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="ard-ledger-card">
                        <div class="ard-ledger-card-head">
                            <?php echo __('Expense Ledgers');?>
                            <span class="badge pull-right"><?php echo count($expenseRows);?></span>
                        </div>
                        <div class="ard-ledger-card-body">
                            <?php if(empty($expenseRows)){?>
                                <div class="ard-ledger-empty"><?php echo __('No expense ledgers found on this page.');?></div>
                            <?php }else{?>
                                <div id="ExpenseLedgerCategoryAccordion">
                                    <?php $expenseCategoryIndex = 0; foreach((array)$expenseRowsByCategory as $expenseCategoryLabel => $expenseCategoryRows){
                                        $expenseCategoryIndex++;
                                        $expenseCategoryPanelId = 'expenseLedgerCategory_'.$expenseCategoryIndex;
                                        $expenseCategoryOpen = ($expenseCategoryIndex === 1);
                                    ?>
                                    <div class="ard-ledger-category">
                                        <div class="ard-ledger-category-head">
                                            <a href="#<?php echo $expenseCategoryPanelId;?>" data-toggle="collapse" data-parent="#ExpenseLedgerCategoryAccordion" aria-expanded="<?php echo $expenseCategoryOpen ? 'true' : 'false';?>">
                                                <?php echo h($expenseCategoryLabel);?>
                                                <span class="badge pull-right"><?php echo count((array)$expenseCategoryRows);?></span>
                                            </a>
                                        </div>
                                        <div id="<?php echo $expenseCategoryPanelId;?>" class="panel-collapse collapse <?php echo $expenseCategoryOpen ? 'in' : '';?>">
                                            <div class="ard-ledger-category-body">
                                                <?php foreach((array)$expenseCategoryRows as $row){
                                                    $id = (int)$row['Ardledger']['id'];
                                                    $agencyLabel = '';
                                                    if($canFilterLedgerAgency)
                                                    {
                                                        $projectId = (int)$row['Ardledger']['project_id'];
                                                        if(isset($projectNameMap[$projectId]))$agencyLabel = (string)$projectNameMap[$projectId];
                                                        elseif(isset($projectNameMap[(string)$projectId]))$agencyLabel = (string)$projectNameMap[(string)$projectId];
                                                        if($agencyLabel==='' && $projectId>0)$agencyLabel = __('Agency').' #'.$projectId;
                                                    }
                                                    $entryCount = isset($ledgerEntryCounts[$id]) ? (int)$ledgerEntryCounts[$id] : 0;
                                                    $employeeLedgerMeta = isset($employeeLedgerMetaByLedgerId[$id]) ? (array)$employeeLedgerMetaByLedgerId[$id] : array();
                                                    $rawLedgerName = trim((string)$row['Ardledger']['name']);
                                                    $displayLedgerName = $rawLedgerName;
                                                    if(!empty($employeeLedgerMeta['full_name']))
                                                    {
                                                        $displayLedgerName = trim((string)$employeeLedgerMeta['full_name']);
                                                        $employeeCode = trim((string)(isset($employeeLedgerMeta['employee_code']) ? $employeeLedgerMeta['employee_code'] : ''));
                                                        if($employeeCode !== '')
                                                        {
                                                            $displayLedgerName .= ' ['.$employeeCode.']';
                                                        }
                                                    }
                                                    $metaParts = array();
                                                    if(strlen(trim((string)$row['Ardledger']['account_no']))>0){$metaParts[] = __('A/C ').h($row['Ardledger']['account_no']);}
                                                    if(strlen(trim((string)$row['Ardledger']['mobile_no']))>0){$metaParts[] = h($row['Ardledger']['mobile_no']);}
                                                    if(!empty($employeeLedgerMeta))
                                                    {
                                                        $employeeSalaryType = strtolower(trim((string)(isset($employeeLedgerMeta['salary_type']) ? $employeeLedgerMeta['salary_type'] : 'monthly')));
                                                        $metaParts[] = __('Type').': '.(($employeeSalaryType === 'daily') ? __('Labour') : __('HR'));
                                                    }
                                                    if($displayLedgerName !== '' && $rawLedgerName !== '' && strcasecmp($displayLedgerName, $rawLedgerName) !== 0)
                                                    {
                                                        $metaParts[] = __('Ledger').': '.h($rawLedgerName);
                                                    }
                                                    $metaParts[] = __('Entries').': '.$entryCount;
                                                ?>
                                                <div class="ard-ledger-item">
                                                    <div class="ard-ledger-item-top">
                                                        <div>
                                                            <div class="ard-ledger-name"><?php echo h($displayLedgerName);?></div>
                                                            <?php if($agencyLabel!==''){?><div class="ard-ledger-agency"><?php echo __('Agency');?>: <?php echo h($agencyLabel);?></div><?php }?>
                                                            <div class="ard-ledger-meta"><?php echo implode(' | ',$metaParts);?></div>
                                                        </div>
                                                        <div>
                                                            <div class="ard-ledger-balance"><?php echo $currency.$this->Number->format($row['Ardledger']['balance']);?></div>
                                                            <div class="ard-ledger-actions">
                                                                <button type="button" class="btn btn-default btn-xs js-ledger-open" data-mode="view" data-id="<?php echo $id;?>" data-project-id="<?php echo (int)$row['Ardledger']['project_id'];?>" data-name="<?php echo h($row['Ardledger']['name']);?>" data-type="<?php echo h($row['Ardledger']['type']);?>" data-category="<?php echo h($row['Ardledger']['category']);?>" data-account-no="<?php echo h($row['Ardledger']['account_no']);?>" data-mobile-no="<?php echo h($row['Ardledger']['mobile_no']);?>" data-address="<?php echo h($row['Ardledger']['address']);?>"><?php echo __('View');?></button>
                                                                <?php echo $this->Html->link(__('Statement'),array('controller'=>'Ardaccounts','action'=>'ledgerstatement',$id,$currentProjectId),array('class'=>'btn btn-info btn-xs'));?>
                                                                <button type="button" class="btn btn-warning btn-xs js-ledger-open" data-mode="edit" data-id="<?php echo $id;?>" data-project-id="<?php echo (int)$row['Ardledger']['project_id'];?>" data-name="<?php echo h($row['Ardledger']['name']);?>" data-type="<?php echo h($row['Ardledger']['type']);?>" data-category="<?php echo h($row['Ardledger']['category']);?>" data-account-no="<?php echo h($row['Ardledger']['account_no']);?>" data-mobile-no="<?php echo h($row['Ardledger']['mobile_no']);?>" data-address="<?php echo h($row['Ardledger']['address']);?>"><?php echo __('Edit');?></button>
                                                                <?php if($entryCount>0){?>
                                                                    <button type="button" class="btn btn-default btn-xs" disabled="disabled" title="<?php echo __('Cannot delete: entries found in this ledger');?>"><?php echo __('Delete Locked');?></button>
                                                                <?php }else{?>
                                                                    <?php echo $this->Html->link(__('Delete'),array('controller'=>'Ardaccounts','action'=>'deleteledger',$id,$currentProjectId),array('class'=>'btn btn-danger btn-xs','onclick'=>"return confirm('".__('Are you sure?')."');"));?>
                                                                <?php }?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php }?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php }?>
                                </div>
                            <?php }?>
                        </div>
                    </div>
                </div>
            </div>
            <?php
            $ledgerLimitBaseUrl = array('controller'=>'Ardaccounts','action'=>'ledgers');
            if($selectedLedgerAgencyId > 0)
            {
                $ledgerLimitBaseUrl[] = $selectedLedgerAgencyId;
            }
            echo $this->element('pagination', array('UseNamedLimit'=>true, 'NamedLimitBaseUrl'=>$ledgerLimitBaseUrl));
            ?>
        </div>
    </div>
</div>

<div class="modal fade" id="ledgerModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="ledgerModalTitle"><?php echo __('Add Ledger');?></h4>
            </div>
            <div class="modal-body">
                <?php echo $this->Form->create('Ardledger',array('id'=>'LedgerForm','url'=>array('controller'=>'Ardaccounts','action'=>'ledgers',$currentProjectId),'class'=>'form-horizontal'));?>
                <?php echo $this->Form->hidden('id',array('id'=>'LedgerId'));?>
                <?php
                if($userType!='ADR')
                {
                    echo $this->Form->hidden('project_id',array('value'=>$agencyId,'id'=>'LedgerProjectId'));
                }
                ?>
                <?php if($userType=='ADR'){?>
                <div class="form-group">
                    <label class="col-sm-3 control-label"><?php echo __('Agency');?></label>
                    <div class="col-sm-9">
                        <?php echo $this->Form->select('project_id',$projectName,array('id'=>'LedgerProjectId','empty'=>__('Please Select'),'class'=>'form-control','label'=>false,'div'=>false));?>
                    </div>
                </div>
                <?php }?>
                <div class="form-group">
                    <label class="col-sm-3 control-label"><?php echo __('Ledger Name');?></label>
                    <div class="col-sm-9">
                        <?php echo $this->Form->input('name',array('id'=>'LedgerName','label'=>false,'div'=>false,'class'=>'form-control','placeholder'=>__('Ledger Name')));?>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label"><?php echo __('Type');?></label>
                    <div class="col-sm-9">
                        <?php echo $this->Form->select('type',array('bank'=>__('Bank/Asset'),'expense'=>__('Expense')),array('id'=>'LedgerType','empty'=>false,'class'=>'form-control','label'=>false,'div'=>false));?>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label"><?php echo __('Category');?></label>
                    <div class="col-sm-9">
                        <?php echo $this->Form->select('category',$initialCategoryOptions,array('id'=>'LedgerCategory','empty'=>__('Please Select'),'label'=>false,'div'=>false,'class'=>'form-control'));?>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label"><?php echo __('Account No');?></label>
                    <div class="col-sm-9">
                        <?php echo $this->Form->input('account_no',array('id'=>'LedgerAccountNo','label'=>false,'div'=>false,'class'=>'form-control','placeholder'=>__('Account No')));?>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label"><?php echo __('Mobile');?></label>
                    <div class="col-sm-9">
                        <?php echo $this->Form->input('mobile_no',array('id'=>'LedgerMobileNo','label'=>false,'div'=>false,'class'=>'form-control','placeholder'=>__('Mobile')));?>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label"><?php echo __('Address');?></label>
                    <div class="col-sm-9">
                        <?php echo $this->Form->input('address',array('id'=>'LedgerAddress','label'=>false,'div'=>false,'class'=>'form-control','placeholder'=>__('Address')));?>
                    </div>
                </div>
                <div class="form-group" id="ledgerModalSubmitWrap">
                    <div class="col-sm-offset-3 col-sm-9">
                        <button type="submit" class="btn btn-success" id="ledgerModalSubmit"><span class="fa fa-save"></span> <?php echo __('Save Ledger');?></button>
                    </div>
                </div>
                <?php echo $this->Form->end();?>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
jQuery(function($){
    $('#LedgerPrintBtn').on('click', function(e){
        e.preventDefault();
        window.print();
    });

    var ledgerCategoryOptionsByProject = <?php echo json_encode($ledgerCategoryOptionsByProject);?>;

    function firstAvailableProjectId()
    {
        var firstProjectId = '';
        $.each(ledgerCategoryOptionsByProject, function(projectId){
            if(String(projectId) !== '')
            {
                firstProjectId = String(projectId);
                return false;
            }
        });
        return firstProjectId;
    }

    function getActiveCategoryOptions(projectId, type)
    {
        var pid = String(projectId || '');
        if(pid === '' || typeof ledgerCategoryOptionsByProject[pid] === 'undefined')
        {
            pid = firstAvailableProjectId();
        }
        var row = (pid !== '' && typeof ledgerCategoryOptionsByProject[pid] !== 'undefined') ? ledgerCategoryOptionsByProject[pid] : {};
        var ledgerType = (String(type || '').toLowerCase() === 'expense') ? 'expense' : 'bank';
        if(typeof row[ledgerType] === 'undefined' || row[ledgerType] === null)
        {
            return {};
        }
        return row[ledgerType];
    }

    function refreshCategoryOptions(selectedCategory)
    {
        var projectId = $('#LedgerProjectId').length ? $('#LedgerProjectId').val() : '';
        var type = $('#LedgerType').val() || 'bank';
        var options = getActiveCategoryOptions(projectId, type);
        var html = '<option value=""><?php echo addslashes(__('Please Select'));?></option>';
        var hasSelected = false;
        $.each(options, function(key, label){
            var optionValue = String(key || '');
            var optionLabel = String(label || '');
            var selected = (String(selectedCategory || '') !== '' && optionValue === String(selectedCategory));
            if(selected)
            {
                hasSelected = true;
            }
            html += '<option value="'+$('<div/>').text(optionValue).html()+'"'+(selected ? ' selected="selected"' : '')+'>'+$('<div/>').text(optionLabel).html()+'</option>';
        });
        if(!hasSelected && String(selectedCategory || '') !== '')
        {
            var safeValue = $('<div/>').text(String(selectedCategory)).html();
            html += '<option value="'+safeValue+'" selected="selected">'+safeValue+'</option>';
        }
        $('#LedgerCategory').html(html);
    }

    function setReadonlyState(isReadonly)
    {
        var $fields = $('#LedgerForm').find('input,select,textarea');
        $fields.prop('disabled', false);
        if(isReadonly)
        {
            $fields.not('#LedgerId').prop('disabled', true);
            $('#ledgerModalSubmitWrap').hide();
        }
        else
        {
            $('#ledgerModalSubmitWrap').show();
        }
        $('#LedgerId').prop('disabled', false);
        if($('#LedgerProjectId').attr('type') === 'hidden')
        {
            $('#LedgerProjectId').prop('disabled', false);
        }
    }

    function openLedgerModal(mode, data)
    {
        data = data || {};
        var title = '<?php echo addslashes(__('Add Ledger'));?>';
        if(mode === 'edit'){ title = '<?php echo addslashes(__('Edit Ledger'));?>'; }
        if(mode === 'view'){ title = '<?php echo addslashes(__('View Ledger'));?>'; }
        $('#ledgerModalTitle').text(title);

        $('#LedgerId').val(data.id || '');
        if($('#LedgerProjectId').length){ $('#LedgerProjectId').val(data.project_id || ''); }
        $('#LedgerName').val(data.name || '');
        $('#LedgerType').val(data.type || 'bank');
        refreshCategoryOptions(data.category || '');
        $('#LedgerAccountNo').val(data.account_no || '');
        $('#LedgerMobileNo').val(data.mobile_no || '');
        $('#LedgerAddress').val(data.address || '');

        setReadonlyState(mode === 'view');
        if(mode === 'edit')
        {
            $('#ledgerModalSubmit').html('<span class="fa fa-save"></span> <?php echo addslashes(__('Update Ledger'));?>');
        }
        else
        {
            $('#ledgerModalSubmit').html('<span class="fa fa-save"></span> <?php echo addslashes(__('Save Ledger'));?>');
        }
        $('#ledgerModal').modal('show');
    }

    $('.js-ledger-add').on('click', function(e){
        e.preventDefault();
        openLedgerModal('add', {
            id: '',
            project_id: <?php echo ($userType=='ADR' ? "''" : (int)$agencyId);?>,
            name: '',
            type: 'bank',
            category: '',
            account_no: '',
            mobile_no: '',
            address: ''
        });
    });

    $('.js-ledger-open').on('click', function(e){
        e.preventDefault();
        var $btn = $(this);
        openLedgerModal($btn.attr('data-mode'), {
            id: $btn.attr('data-id') || '',
            project_id: $btn.attr('data-project-id') || '',
            name: $btn.attr('data-name') || '',
            type: $btn.attr('data-type') || 'bank',
            category: $btn.attr('data-category') || '',
            account_no: $btn.attr('data-account-no') || '',
            mobile_no: $btn.attr('data-mobile-no') || '',
            address: $btn.attr('data-address') || ''
        });
    });

    <?php if($isEdit || $isView){?>
    openLedgerModal('<?php echo ($isView ? 'view' : 'edit');?>', <?php echo json_encode($initialLedger);?>);
    <?php }?>

    $(document).on('change', '#LedgerType,#LedgerProjectId', function(){
        refreshCategoryOptions('');
    });
});
</script>
