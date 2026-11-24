<?php
$isAdr = (strtoupper(trim((string)$userType)) === 'ADR');
$activeTab = isset($activeSettingsTab) ? $activeSettingsTab : 'general';
if($activeTab === '')
{
    $activeTab = 'general';
}
$activeTabClass = preg_replace('/[^a-z0-9_-]/i', '', (string)$activeTab);
$isTemplateTab = ($activeTab === 'templates');
$templateEdit = !empty($this->request->data['Ardtemplate']['id']);
$templatePlaceholderCatalog = (isset($templatePlaceholderCatalog) && is_array($templatePlaceholderCatalog)) ? $templatePlaceholderCatalog : array();
$settingsCurrencyText = trim(strip_tags(isset($currency) ? (string)$currency : 'Rs'));
if($settingsCurrencyText === '')
{
    $settingsCurrencyText = 'Rs';
}
?>
<style type="text/css">
.ard-settings-panel {
    background: #f6f9fc !important;
    border: 1px solid #d9e4f1 !important;
    border-radius: 10px;
    box-shadow: 0 14px 30px rgba(15, 23, 42, 0.08);
    overflow: hidden;
}
.ard-settings-panel > .panel-body {
    padding: 16px;
}
.ard-settings-tabs {
    background: #fff;
    border: 1px solid #d9e4f1;
    border-radius: 10px;
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin: 0 0 16px !important;
    padding: 8px;
}
.ard-settings-tabs:before,
.ard-settings-tabs:after {
    display: none;
}
.ard-settings-tabs > li {
    float: none;
    margin: 0;
}
.ard-settings-tabs > li > a {
    border: 0 !important;
    border-radius: 8px !important;
    color: #10233f !important;
    font-size: 12px;
    font-weight: 800;
    line-height: 1.2;
    margin: 0 !important;
    padding: 10px 13px !important;
}
.ard-settings-tabs > li.active > a,
.ard-settings-tabs > li.active > a:hover,
.ard-settings-tabs > li.active > a:focus {
    background: #07152a !important;
    color: #fff !important;
}
.ard-settings-panel .alert {
    border: 0;
    border-radius: 8px;
    font-weight: 700;
}
.ard-settings-form {
    background: #fff;
    border: 1px solid #d9e4f1;
    border-radius: 10px;
    padding: 16px;
}
.ard-settings-form .form-group {
    align-items: start;
    background: #fff;
    border-bottom: 1px solid #eef3f8;
    display: grid;
    gap: 8px 16px;
    grid-template-columns: repeat(2, minmax(260px, 1fr));
    margin: 0 !important;
    padding: 12px 0;
}
.ard-settings-form .form-group:first-of-type {
    padding-top: 0;
}
.ard-settings-form .form-group:last-of-type {
    border-bottom: 0;
    padding-bottom: 0;
}
.ard-settings-form .form-group:before,
.ard-settings-form .form-group:after {
    display: none;
}
.ard-settings-form .form-group > label.control-label,
.ard-settings-form .form-group > div[class*="col-sm-"] {
    float: none !important;
    margin: 0 !important;
    min-width: 0;
    padding: 0 !important;
    width: auto !important;
}
.ard-settings-form .form-group > label.control-label {
    color: #10233f !important;
    font-size: 11px;
    font-weight: 900;
    letter-spacing: .04em;
    line-height: 1.3;
    text-align: left !important;
    text-transform: uppercase;
}
.ard-settings-form .form-group > label.control-label:nth-child(1) {
    grid-column: 1;
    grid-row: 1;
}
.ard-settings-form .form-group > div:nth-child(2) {
    grid-column: 1;
    grid-row: 2;
}
.ard-settings-form .form-group > label.control-label:nth-child(3) {
    grid-column: 2;
    grid-row: 1;
}
.ard-settings-form .form-group > div:nth-child(4) {
    grid-column: 2;
    grid-row: 2;
}
.ard-settings-form .form-group > div:nth-child(3):not(.col-sm-offset-2) {
    grid-column: 2;
    grid-row: 2;
}
.ard-settings-form .form-group > div.col-sm-10:nth-child(2),
.ard-settings-form .form-group > div.col-sm-offset-2 {
    grid-column: 1 / -1;
}
.ard-settings-form .form-group > div.col-sm-offset-2:first-child {
    grid-row: auto;
}
.ard-settings-form .form-control {
    background: #fbfdff;
    border: 1px solid #cbd8e8;
    border-radius: 7px;
    box-shadow: none;
    color: #07152a;
    font-weight: 700;
    height: 38px;
    max-width: none !important;
    width: 100% !important;
}
.ard-settings-form textarea.form-control {
    height: auto;
    min-height: 104px;
    resize: vertical;
}
.ard-settings-form .form-control:focus {
    background: #fff;
    border-color: #ff0a46;
    box-shadow: 0 0 0 3px rgba(255, 10, 70, 0.10);
}
.ard-settings-form .help-block {
    color: #52677f;
    font-size: 12px;
    font-weight: 700;
    line-height: 1.45;
    margin: 6px 0 0 !important;
}
.ard-settings-form label:not(.control-label) {
    color: #10233f;
    font-weight: 800;
}
.ard-settings-form input[type="checkbox"] {
    margin: 0 7px 0 0;
    vertical-align: middle;
}
.ard-settings-form .btn {
    border: 0 !important;
    border-radius: 7px;
    box-shadow: 0 8px 16px rgba(15, 23, 42, 0.10);
    font-weight: 800;
    padding: 9px 14px;
    text-shadow: none;
}
.ard-settings-form .btn-success {
    background: #047857 !important;
    color: #fff !important;
}
.ard-settings-form .btn-warning {
    background: #f59e0b !important;
    color: #07152a !important;
}
.ard-settings-form .btn-info {
    background: #1d4ed8 !important;
    color: #fff !important;
}
.ard-settings-form .btn-default {
    background: #eef3f8 !important;
    color: #07152a !important;
}
.ard-settings-panel .table-responsive {
    border: 1px solid #d9e4f1;
    border-radius: 8px;
    background: #fff;
}
.ard-settings-panel table.table {
    margin-bottom: 0;
}
.ard-settings-panel table.table > thead > tr > th,
.ard-settings-panel table.table > tbody > tr:first-child > th {
    background: #eef3f8;
    border-color: #d9e4f1;
    color: #07152a;
    font-size: 12px;
    font-weight: 900;
    text-transform: uppercase;
}
.ard-settings-panel table.table > tbody > tr > td {
    border-color: #edf2f7;
    color: #334155;
    vertical-align: middle;
}
.ard-settings-panel .modal-content {
    border: 0;
    border-radius: 10px;
    overflow: hidden;
}
.ard-settings-panel .modal-header {
    background: #07152a;
    color: #fff;
}
.ard-settings-panel .modal-title,
.ard-settings-panel .modal-header .close {
    color: #fff;
    opacity: 1;
}
.ard-settings-panel .modal-body label {
    color: #10233f;
    display: block;
    font-size: 11px;
    font-weight: 900;
    letter-spacing: .04em;
    text-transform: uppercase;
}
.ard-settings-panel .modal-body .form-control {
    border: 1px solid #cbd8e8;
    border-radius: 7px;
    box-shadow: none;
    color: #07152a;
    font-weight: 700;
}
.ard-settings-template-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 12px;
}
.ard-bank-accounts-wrap {
    grid-column: 1 / -1;
}
.ard-bank-accounts-table {
    min-width: 980px;
}
.ard-bank-accounts-table th,
.ard-bank-accounts-table td {
    vertical-align: top !important;
}
.ard-bank-accounts-table .form-control {
    height: 34px;
    min-width: 120px;
}
.ard-bank-accounts-table textarea.form-control {
    min-height: 34px;
}
.ard-bank-primary-cell,
.ard-bank-status-cell,
.ard-bank-action-cell {
    text-align: center;
    white-space: nowrap;
}
.ard-bank-preview {
    background: #f8fbff;
    border: 1px solid #d9e4f1;
    border-radius: 8px;
    color: #10233f;
    font-size: 12px;
    font-weight: 700;
    margin-top: 10px;
    min-height: 72px;
    padding: 10px;
    white-space: pre-wrap;
}
.ard-settings-form-vouchers {
    background: #f8fafc;
    display: block !important;
    max-width: 1200px;
    padding: 14px;
    width: 100%;
}
.ard-settings-form-vouchers .form-group,
.ard-settings-form-vouchers [class*="col-sm-"] {
    float: none !important;
    margin-left: 0 !important;
}
.ard-settings-form-vouchers > .form-group {
    background: transparent;
    border-bottom: 0;
    clear: both;
    display: block !important;
    padding: 0 0 14px;
    width: 100%;
}
.ard-settings-form-vouchers > .form-group:last-of-type {
    background: #fff;
    border: 1px solid #d9e4f1;
    border-radius: 8px;
    margin-top: 4px !important;
    padding: 12px !important;
}
.ard-settings-form-vouchers > .form-group > label.control-label,
.ard-settings-form-vouchers > .form-group > div[class*="col-sm-"] {
    display: block !important;
    grid-column: auto !important;
    grid-row: auto !important;
    width: 100% !important;
}
.ard-settings-form-vouchers .ard-voucher-agency-row .col-sm-4 {
    max-width: 420px;
}
.ard-voucher-single-column {
    display: flex !important;
    flex-direction: column !important;
    gap: 14px;
    max-width: 1200px;
    width: 100%;
}
.ard-voucher-single-column > * {
    flex: 0 0 auto !important;
    max-width: 1200px !important;
    width: 100% !important;
}
.ard-voucher-single-column .form-group {
    display: block !important;
    margin: 0 !important;
    padding: 0 !important;
    width: 100% !important;
}
.ard-voucher-single-column .form-group > label.control-label,
.ard-voucher-single-column .form-group > div[class*="col-sm-"] {
    display: block !important;
    float: none !important;
    margin: 0 !important;
    padding: 0 !important;
    width: 100% !important;
}
.ard-voucher-title {
    background: #eaf6ff;
    border: 1px solid #cbe8fb;
    border-radius: 8px;
    color: #12324a;
    margin: 0 0 14px;
    max-width: 1200px;
    padding: 14px 16px;
    width: 100%;
}
.ard-voucher-title h2 {
    color: #07152a;
    font-size: 18px;
    font-weight: 900;
    line-height: 1.25;
    margin: 0 0 4px;
}
.ard-voucher-title p {
    color: #4f6378;
    font-size: 13px;
    font-weight: 700;
    line-height: 1.45;
    margin: 0;
}
.ard-voucher-page {
    display: grid;
    gap: 14px;
    max-width: 1200px;
    width: 100%;
}
.ard-voucher-note {
    align-items: flex-start;
    background: #eaf6ff;
    border: 1px solid #cbe8fb;
    border-radius: 8px;
    color: #12324a;
    display: flex;
    gap: 10px;
    padding: 12px 14px;
}
.ard-voucher-note .fa {
    color: #0f78a8;
    font-size: 16px;
    line-height: 1.4;
}
.ard-voucher-note strong {
    color: #07152a;
    display: block;
    font-size: 13px;
    font-weight: 900;
    margin-bottom: 2px;
}
.ard-voucher-note span {
    color: #4f6378;
    display: block;
    font-size: 12px;
    font-weight: 700;
    line-height: 1.45;
}
.ard-voucher-section {
    background: #fff;
    border: 1px solid #d9e4f1;
    border-radius: 8px;
    overflow: hidden;
}
.ard-voucher-section-head {
    align-items: center;
    background: #07152a;
    color: #fff;
    display: flex;
    justify-content: space-between;
    gap: 12px;
    padding: 11px 14px;
}
.ard-voucher-section-head strong {
    font-size: 13px;
    font-weight: 900;
}
.ard-voucher-section-head span {
    background: rgba(255,255,255,0.14);
    border-radius: 999px;
    font-size: 11px;
    font-weight: 900;
    padding: 4px 9px;
}
.ard-voucher-grid {
    display: grid;
    gap: 10px;
    grid-template-columns: minmax(0, 1fr);
    padding: 12px;
}
.ard-voucher-card {
    align-items: center;
    background: #fbfdff;
    border: 1px solid #e2eaf3;
    border-radius: 8px;
    display: grid;
    gap: 12px;
    grid-template-columns: minmax(260px, 1fr) minmax(420px, 520px);
    min-height: 74px;
    padding: 12px;
}
.ard-voucher-name {
    color: #07152a;
    font-size: 13px;
    font-weight: 900;
    line-height: 1.35;
}
.ard-voucher-fields {
    display: grid;
    gap: 8px;
    grid-template-columns: minmax(180px, 1fr) 130px;
}
.ard-voucher-field-label {
    color: #61748a;
    display: block;
    font-size: 10px;
    font-weight: 900;
    letter-spacing: .04em;
    margin-bottom: 4px;
    text-transform: uppercase;
}
.ard-voucher-fields .form-control {
    height: 34px;
}
.ard-settings-form-vouchers .ard-voucher-page,
.ard-settings-form-vouchers .ard-voucher-section,
.ard-settings-form-vouchers .ard-voucher-grid,
.ard-settings-form-vouchers .ard-voucher-card,
.ard-settings-form-vouchers .ard-voucher-fields {
    max-width: 700px !important;
    width: 100% !important;
}
.ard-settings-form-vouchers .ard-voucher-grid {
    display: block !important;
}
.ard-settings-form-vouchers .ard-voucher-card {
    display: block !important;
    margin-bottom: 10px;
}
.ard-settings-form-vouchers .ard-voucher-card:last-child {
    margin-bottom: 0;
}
.ard-settings-form-vouchers .ard-voucher-name {
    margin-bottom: 10px;
}
.ard-settings-form-vouchers .ard-voucher-fields {
    display: grid !important;
    grid-template-columns: minmax(0, 1fr) 160px !important;
}
.ard-settings-form-vouchers > .form-group:last-of-type .col-sm-offset-2 {
    display: flex;
    justify-content: flex-end;
}
@media(max-width: 991px) {
    .ard-settings-form .form-group {
        grid-template-columns: 1fr;
    }
    .ard-settings-form .form-group > label.control-label,
    .ard-settings-form .form-group > div[class*="col-sm-"] {
        grid-column: 1 / -1 !important;
        grid-row: auto !important;
    }
    .ard-settings-tabs > li {
        flex: 1 1 145px;
    }
    .ard-settings-tabs > li > a {
        text-align: center;
    }
    .ard-voucher-card {
        grid-template-columns: 1fr;
    }
    .ard-voucher-fields {
        grid-template-columns: 1fr 120px;
    }
    .ard-settings-form-vouchers > .form-group:last-of-type .col-sm-offset-2 {
        justify-content: stretch;
    }
    .ard-settings-form-vouchers > .form-group:last-of-type .btn {
        width: 100%;
    }
}
@media(max-width: 520px) {
    .ard-voucher-grid,
    .ard-voucher-fields {
        grid-template-columns: 1fr;
    }
}
</style>
<div class="page-title"><div class="title-env"><h1 class="title"><?php echo __('Accounts Settings');?></h1></div></div>
<div class="panel ard-settings-panel">
    <div class="panel-body">
        <?php echo $this->Session->flash();?>

        <ul class="nav nav-tabs ard-settings-tabs" style="margin-bottom:15px;">
            <li class="<?php echo ($activeTab==='general') ? 'active' : '';?>">
                <?php echo $this->Html->link(__('General'),array('controller'=>'Ardaccounts','action'=>'settings',$currentProjectId,'?'=>array('tab'=>'general')),array('onclick'=>'window.location=this.href;return false;'));?>
            </li>
            <li class="<?php echo ($activeTab==='vouchers') ? 'active' : '';?>">
                <?php echo $this->Html->link(__('Voucher Prefixes'),array('controller'=>'Ardaccounts','action'=>'settings',$currentProjectId,'?'=>array('tab'=>'vouchers')),array('onclick'=>'window.location=this.href;return false;'));?>
            </li>
            <li class="<?php echo ($activeTab==='construction') ? 'active' : '';?>">
                <?php echo $this->Html->link(__('Construction'),array('controller'=>'Ardaccounts','action'=>'settings',$currentProjectId,'?'=>array('tab'=>'construction')),array('onclick'=>'window.location=this.href;return false;'));?>
            </li>
            <li class="<?php echo ($activeTab==='backup') ? 'active' : '';?>">
                <?php echo $this->Html->link(__('Backup'),array('controller'=>'Ardaccounts','action'=>'settings',$currentProjectId,'?'=>array('tab'=>'backup')),array('onclick'=>'window.location=this.href;return false;'));?>
            </li>
            <li class="<?php echo ($activeTab==='templates') ? 'active' : '';?>">
                <?php echo $this->Html->link(__('Templates'),array('controller'=>'Ardaccounts','action'=>'settings',$currentProjectId,'?'=>array('tab'=>'templates')),array('onclick'=>'window.location=this.href;return false;'));?>
            </li>
            <li class="<?php echo ($activeTab==='commission') ? 'active' : '';?>">
                <?php echo $this->Html->link('<span class="fa fa-percent"></span> '.__('Agent Commission'),array('controller'=>'Ardaccounts','action'=>'settings',$currentProjectId,'?'=>array('tab'=>'commission')),array('escape'=>false,'onclick'=>'window.location=this.href;return false;'));?>
            </li>
            <li class="<?php echo ($activeTab==='bulk') ? 'active' : '';?>">
                <?php echo $this->Html->link('<span class="fa fa-list-alt"></span> '.__('Bulk Vouchers'),array('controller'=>'Ardaccounts','action'=>'settings',$currentProjectId,'?'=>array('tab'=>'bulk')),array('escape'=>false,'onclick'=>'window.location=this.href;return false;'));?>
            </li>
        </ul>

        <?php if($isTemplateTab){?>
            <div class="alert alert-info">
                <?php echo __('Template Add/Edit has moved to dedicated pages for better workflow.');?>
            </div>
            <div class="btn-group ard-settings-template-actions">
                <?php echo $this->Html->link('<span class="fa fa-list"></span> '.__('Manage Templates'),array('controller'=>'Ardaccounts','action'=>'templates',$currentProjectId),array('escape'=>false,'class'=>'btn btn-default'));?>
                <?php echo $this->Html->link('<span class="fa fa-plus"></span> '.__('Add Template'),array('controller'=>'Ardaccounts','action'=>'templateadd',$currentProjectId),array('escape'=>false,'class'=>'btn btn-success'));?>
            </div>
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <tr>
                        <th>#</th>
                        <?php if($isAdr){?><th><?php echo __('Agency');?></th><?php }?>
                        <th><?php echo __('Type');?></th>
                        <th><?php echo __('Name');?></th>
                        <th><?php echo __('Active');?></th>
                        <th><?php echo __('Action');?></th>
                    </tr>
                    <?php $sn = 1; foreach($templateRows as $tpl): $tplId = (int)$tpl['Ardtemplate']['id']; ?>
                    <tr>
                        <td><?php echo $sn++;?></td>
                        <?php if($isAdr){?><td><?php echo h(isset($projectName[$tpl['Ardtemplate']['project_id']]) ? $projectName[$tpl['Ardtemplate']['project_id']] : '-');?></td><?php }?>
                        <td><?php echo h($tpl['Ardtemplate']['template_type']);?></td>
                        <td><?php echo h($tpl['Ardtemplate']['template_name']);?></td>
                        <td><?php echo ((int)$tpl['Ardtemplate']['is_active']===1?__('Yes'):__('No'));?></td>
                        <td>
                            <?php echo $this->Html->link('<span class="fa fa-eye"></span> '.__('View'),array('controller'=>'Ardaccounts','action'=>'templateview',$tplId,$currentProjectId),array('escape'=>false,'class'=>'btn btn-xs btn-info','target'=>'_blank'));?>
                            <?php echo $this->Html->link('<span class="fa fa-edit"></span> '.__('Edit'),array('controller'=>'Ardaccounts','action'=>'templateedit',$tplId,$currentProjectId),array('escape'=>false,'class'=>'btn btn-xs btn-warning'));?>
                            <?php echo $this->Html->link('<span class="fa fa-trash"></span> '.__('Delete'),array('controller'=>'Ardaccounts','action'=>'deletetemplate',$tplId,$currentProjectId),array('escape'=>false,'class'=>'btn btn-xs btn-danger','onclick'=>"return confirm('".__('Are you sure?')."');"));?>
                        </td>
                    </tr>
                    <?php endforeach;?>
                </table>
            </div>
        <?php } else { ?>
            <?php echo $this->Form->create('Ardsetting',array('url'=>array('controller'=>'Ardaccounts','action'=>'settings',$currentProjectId,'?'=>array('tab'=>$activeTab)),'class'=>'form-horizontal ard-settings-form ard-settings-form-'.$activeTabClass));?>
            <?php if(!$isAdr){ echo $this->Form->hidden('project_id',array('value'=>$agencyId)); }?>
            <?php echo $this->Form->hidden('active_tab',array('value'=>$activeTab));?>

            <?php if($activeTab==='vouchers'){?><div class="ard-voucher-single-column"><?php }?>

            <?php if($isAdr){?>
            <div class="form-group<?php echo ($activeTab==='vouchers') ? ' ard-voucher-agency-row' : '';?>">
                <label class="col-sm-2 control-label"><?php echo __('Agency');?></label>
                <div class="col-sm-4">
                    <?php echo $this->Form->select('project_id',$projectName,array('empty'=>__('Please Select'),'class'=>'form-control','label'=>false,'div'=>false,'id'=>'ArdSettingsProjectId'));?>
                    <p class="help-block" style="margin-bottom:0;"><?php echo __('All settings, including voucher prefixes, are saved for the selected agency.');?></p>
                </div>
            </div>
            <?php }?>

            <?php if($activeTab==='vouchers'){?>
            <div class="ard-voucher-title">
                <h2><?php echo __('Voucher Prefixes');?></h2>
                <p><?php echo __('Prefixes and start numbers are saved for the selected agency.');?></p>
            </div>
            <?php }?>

            <?php if($activeTab==='general'){?>
                <?php if($isAdr){
                    $expenseApprovalEnabled = !empty($expenseApprovalConfig['enabled']);
                    $expenseApprovalCeoId = !empty($expenseApprovalConfig['ceo_user_id']) ? (int)$expenseApprovalConfig['ceo_user_id'] : 0;
                    $expenseApprovalCeoOptions = isset($expenseApprovalCeoOptions) ? (array)$expenseApprovalCeoOptions : array();
                    $expenseApprovalRoleReadiness = isset($expenseApprovalRoleReadiness) ? (array)$expenseApprovalRoleReadiness : array();
                ?>
                <div class="form-group">
                    <div class="col-sm-12">
                        <div class="well" style="background:#f8fbff;border-color:#cbd8e8;margin-bottom:0;">
                            <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:12px;">
                                <div>
                                    <h4 style="margin:0 0 4px;font-weight:800;"><?php echo __('Expense Approval Workflow');?></h4>
                                    <p class="help-block" style="margin:0;"><?php echo __('Agency-wise flow: Accountant creates, Manager verifies, and CEO gives final approval before the expense voucher is posted.');?></p>
                                </div>
                                <?php echo $this->Html->link('<span class="fa fa-list"></span> '.__('Open Approval Queue'),array('controller'=>'Ardaccounts','action'=>'expenseapprovals',$currentProjectId),array('escape'=>false,'class'=>'btn btn-primary'));?>
                            </div>
                            <div class="row">
                                <div class="col-sm-4" style="margin-bottom:10px;">
                                    <label style="display:block;font-weight:800;"><?php echo __('Workflow Status');?></label>
                                    <label style="display:flex;align-items:center;gap:8px;min-height:38px;margin:0;">
                                        <input type="checkbox" name="data[Ardsetting][expense_approval_enabled]" value="1"<?php echo $expenseApprovalEnabled ? ' checked="checked"' : '';?>>
                                        <span><?php echo $expenseApprovalEnabled ? __('On') : __('Off');?></span>
                                    </label>
                                </div>
                                <div class="col-sm-8" style="margin-bottom:10px;">
                                    <label style="display:block;font-weight:800;"><?php echo __('Agency CEO');?></label>
                                    <?php echo $this->Form->select('expense_approval_ceo_user_id',$expenseApprovalCeoOptions,array('empty'=>__('Agency Owner Role (Default)'),'class'=>'form-control','value'=>$expenseApprovalCeoId));?>
                                </div>
                            </div>
                            <?php if(empty($expenseApprovalRoleReadiness['accountants']) || empty($expenseApprovalRoleReadiness['managers']) || (empty($expenseApprovalRoleReadiness['ceos']) && $expenseApprovalCeoId <= 0)):?>
                            <p class="help-block" style="color:#b45309;font-weight:700;margin:0;"><span class="fa fa-exclamation-triangle"></span> <?php echo __('Assign an Accountant, Manager, and CEO/Agency Owner to this agency before turning the workflow on.');?></p>
                            <?php endif;?>
                        </div>
                    </div>
                </div>
                <?php }?>
                <div class="form-group">
                    <label class="col-sm-2 control-label"><?php echo __('Company Name');?></label>
                    <div class="col-sm-4"><?php echo $this->Form->input('company_name',array('label'=>false,'div'=>false,'class'=>'form-control'));?></div>
                    <label class="col-sm-2 control-label"><?php echo __('Currency Symbol');?></label>
                    <div class="col-sm-4"><?php echo $this->Form->input('currency_symbol',array('label'=>false,'div'=>false,'class'=>'form-control'));?></div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label"><?php echo __('Rows Per Page');?></label>
                    <div class="col-sm-4"><?php echo $this->Form->input('rows_per_page',array('label'=>false,'div'=>false,'class'=>'form-control'));?></div>
                    <label class="col-sm-2 control-label"><?php echo __('Currency Position');?></label>
                    <div class="col-sm-4"><?php echo $this->Form->select('currency_position',array('left'=>__('Left'),'right'=>__('Right')),array('class'=>'form-control','label'=>false,'div'=>false));?></div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label"><?php echo __('Currency Decimals');?></label>
                    <div class="col-sm-4"><?php echo $this->Form->input('currency_decimals',array('label'=>false,'div'=>false,'class'=>'form-control'));?></div>
                    <label class="col-sm-2 control-label"><?php echo __('Fiscal Lock Date');?></label>
                    <div class="col-sm-4"><?php echo $this->Form->input('fiscal_lock_date',array('type'=>'text','label'=>false,'div'=>false,'class'=>'form-control','placeholder'=>'DD-MMM-YYYY','autocomplete'=>'off'));?></div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label"><?php echo __('Default Online Transfer Account');?></label>
                    <div class="col-sm-4">
                        <?php echo $this->Form->select('deal_receipt_online_ledger_id', isset($receiptAssetLedgerOptions) ? $receiptAssetLedgerOptions : array(0 => __('Please Select')), array('class'=>'form-control','label'=>false,'div'=>false));?>
                    </div>
                    <label class="col-sm-2 control-label"><?php echo __('Default Cash In Hand Account');?></label>
                    <div class="col-sm-4">
                        <?php echo $this->Form->select('deal_receipt_cash_ledger_id', isset($receiptAssetLedgerOptions) ? $receiptAssetLedgerOptions : array(0 => __('Please Select')), array('class'=>'form-control','label'=>false,'div'=>false));?>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10">
                        <p class="help-block" style="margin-bottom:0;">
                            <?php echo __('These defaults are agency-wise. Deal Payments auto-select these asset accounts for Online Transfer and Cash In Hand.');?>
                        </p>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label"><?php echo __('Default Purchase Expense Account');?></label>
                    <div class="col-sm-4">
                        <?php echo $this->Form->select('purchase_payment_expense_ledger_id', isset($defaultExpenseLedgerOptions) ? $defaultExpenseLedgerOptions : array(0 => __('Please Select')), array('class'=>'form-control','label'=>false,'div'=>false));?>
                        <p class="help-block" style="margin-bottom:0;"><?php echo __('Used when purchase/vendor payments auto-create expense vouchers.');?></p>
                    </div>
                    <label class="col-sm-2 control-label"><?php echo __('Default Payroll Expense Account');?></label>
                    <div class="col-sm-4">
                        <?php echo $this->Form->select('payroll_payment_expense_ledger_id', isset($defaultExpenseLedgerOptions) ? $defaultExpenseLedgerOptions : array(0 => __('Please Select')), array('class'=>'form-control','label'=>false,'div'=>false));?>
                        <p class="help-block" style="margin-bottom:0;"><?php echo __('Used when HR/payroll payments auto-create expense vouchers.');?></p>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10">
                        <p class="help-block" style="margin-bottom:0;">
                            <?php echo __('If no default is selected, the system safely falls back to the agency General Expense Account.');?>
                        </p>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10">
                        <div class="well" style="background:#f8fbff;border-color:#d6e3f2;margin-bottom:8px;">
                            <h4 style="margin-top:0;font-weight:700;"><?php echo __('Business Event Default Accounts');?></h4>
                            <p class="help-block" style="margin-bottom:12px;"><?php echo __('Agency-wise ledger mapping used by auto vouchers for deal payments, charges, refunds, transfers, commissions, tax, vendor payable, construction/WIP, and cash closing.');?></p>
                            <?php
                            $businessDefaultLedgerFields = (isset($businessDefaultLedgerFields) && is_array($businessDefaultLedgerFields)) ? $businessDefaultLedgerFields : array();
                            $businessLedgerOptionsByType = (isset($businessLedgerOptionsByType) && is_array($businessLedgerOptionsByType)) ? $businessLedgerOptionsByType : array();
                            $businessFieldIndex = 0;
                            foreach($businessDefaultLedgerFields as $businessField => $businessMeta):
                                $businessType = strtolower(trim((string)(isset($businessMeta['type']) ? $businessMeta['type'] : 'income')));
                                $businessOptions = isset($businessLedgerOptionsByType[$businessType])
                                    ? $businessLedgerOptionsByType[$businessType]
                                    : (($businessType === 'expense') ? (isset($businessExpenseLedgerOptions) ? $businessExpenseLedgerOptions : array(0 => __('Please Select'))) : (isset($businessIncomeLedgerOptions) ? $businessIncomeLedgerOptions : array(0 => __('Please Select'))));
                                if($businessFieldIndex % 2 === 0){ echo '<div class="row">'; }
                            ?>
                                <div class="col-sm-6" style="margin-bottom:10px;">
                                    <label style="font-weight:700;"><?php echo h(isset($businessMeta['label']) ? $businessMeta['label'] : Inflector::humanize($businessField));?></label>
                                    <?php echo $this->Form->select($businessField, $businessOptions, array('class'=>'form-control','label'=>false,'div'=>false));?>
                                </div>
                            <?php
                                $businessFieldIndex++;
                                if($businessFieldIndex % 2 === 0){ echo '</div>'; }
                            endforeach;
                            if($businessFieldIndex % 2 !== 0){ echo '</div>'; }
                            ?>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label"><?php echo __('Paid Receipt Link');?></label>
                    <div class="col-sm-10">
                        <label><?php echo $this->Form->checkbox('paid_receipt_share_link_enabled');?> <?php echo __('Send receipt print/snapshot URL in paid WhatsApp and email notifications');?></label>
                        <p class="help-block" style="margin-bottom:0;"><?php echo __('Agency-wise setting. When off, paid notifications are sent without the public receipt print link.');?></p>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label"><?php echo __('Paid Notification Popup');?></label>
                    <div class="col-sm-10">
                        <label><?php echo $this->Form->checkbox('paid_notification_popup_enabled');?> <?php echo __('Show WhatsApp and email send popup after adding a deal payment');?></label>
                        <p class="help-block" style="margin-bottom:0;"><?php echo __('Agency-wise setting. When off, the payment add window closes after save as before.');?></p>
                    </div>
                </div>
                <div class="form-group ard-bank-accounts-wrap">
                    <div class="col-sm-offset-2 col-sm-10">
                        <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:8px;flex-wrap:wrap;">
                            <div>
                                <strong><?php echo __('Payment Bank Accounts');?></strong>
                                <p class="help-block" style="margin:2px 0 0 !important;"><?php echo __('Agency-wise bank accounts used in payment reminder SMS, WhatsApp, and email templates.');?></p>
                            </div>
                            <button type="button" class="btn btn-info" id="AddPaymentBankAccountRow"><span class="fa fa-plus"></span> <?php echo __('Add Bank Account');?></button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered ard-bank-accounts-table" id="PaymentBankAccountsTable">
                                <thead>
                                    <tr>
                                        <th><?php echo __('Bank');?></th>
                                        <th><?php echo __('Account Title');?></th>
                                        <th><?php echo __('Account No');?></th>
                                        <th><?php echo __('IBAN');?></th>
                                        <th><?php echo __('Branch');?></th>
                                        <th><?php echo __('SWIFT');?></th>
                                        <th><?php echo __('Instructions');?></th>
                                        <th><?php echo __('Primary');?></th>
                                        <th><?php echo __('Status');?></th>
                                        <th><?php echo __('Action');?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $bankRows = !empty($paymentBankAccounts) ? (array)$paymentBankAccounts : array(array('Ardpaymentbankaccount' => array()));
                                    $bankIndex = 0;
                                    foreach($bankRows as $bankRow):
                                        $bank = !empty($bankRow['Ardpaymentbankaccount']) ? (array)$bankRow['Ardpaymentbankaccount'] : (array)$bankRow;
                                        $bankId = !empty($bank['id']) ? (int)$bank['id'] : 0;
                                        $bankStatus = !empty($bank['status']) && $bank['status'] === 'inactive' ? 'inactive' : 'active';
                                    ?>
                                    <tr class="js-bank-account-row">
                                        <td>
                                            <input type="hidden" name="data[Ardpaymentbankaccount][<?php echo (int)$bankIndex;?>][id]" value="<?php echo (int)$bankId;?>" />
                                            <input type="hidden" class="js-bank-delete" name="data[Ardpaymentbankaccount][<?php echo (int)$bankIndex;?>][delete]" value="0" />
                                            <input type="text" class="form-control" name="data[Ardpaymentbankaccount][<?php echo (int)$bankIndex;?>][bank_name]" value="<?php echo h(isset($bank['bank_name']) ? $bank['bank_name'] : '');?>" />
                                        </td>
                                        <td><input type="text" class="form-control" name="data[Ardpaymentbankaccount][<?php echo (int)$bankIndex;?>][account_title]" value="<?php echo h(isset($bank['account_title']) ? $bank['account_title'] : '');?>" /></td>
                                        <td><input type="text" class="form-control" name="data[Ardpaymentbankaccount][<?php echo (int)$bankIndex;?>][account_no]" value="<?php echo h(isset($bank['account_no']) ? $bank['account_no'] : '');?>" /></td>
                                        <td><input type="text" class="form-control" name="data[Ardpaymentbankaccount][<?php echo (int)$bankIndex;?>][iban]" value="<?php echo h(isset($bank['iban']) ? $bank['iban'] : '');?>" /></td>
                                        <td><input type="text" class="form-control" name="data[Ardpaymentbankaccount][<?php echo (int)$bankIndex;?>][branch]" value="<?php echo h(isset($bank['branch']) ? $bank['branch'] : '');?>" /></td>
                                        <td><input type="text" class="form-control" name="data[Ardpaymentbankaccount][<?php echo (int)$bankIndex;?>][swift_code]" value="<?php echo h(isset($bank['swift_code']) ? $bank['swift_code'] : '');?>" /></td>
                                        <td><textarea class="form-control" name="data[Ardpaymentbankaccount][<?php echo (int)$bankIndex;?>][instructions]" rows="1"><?php echo h(isset($bank['instructions']) ? $bank['instructions'] : '');?></textarea></td>
                                        <td class="ard-bank-primary-cell"><input type="radio" name="data[Ardpaymentbankaccount_primary]" class="js-bank-primary-radio" value="<?php echo (int)$bankIndex;?>" <?php echo !empty($bank['is_primary']) ? 'checked="checked"' : '';?> /><input type="hidden" class="js-bank-primary-value" name="data[Ardpaymentbankaccount][<?php echo (int)$bankIndex;?>][is_primary]" value="<?php echo !empty($bank['is_primary']) ? 1 : 0;?>" /></td>
                                        <td class="ard-bank-status-cell">
                                            <select class="form-control" name="data[Ardpaymentbankaccount][<?php echo (int)$bankIndex;?>][status]">
                                                <option value="active" <?php echo $bankStatus === 'active' ? 'selected="selected"' : '';?>><?php echo __('Active');?></option>
                                                <option value="inactive" <?php echo $bankStatus === 'inactive' ? 'selected="selected"' : '';?>><?php echo __('Inactive');?></option>
                                            </select>
                                        </td>
                                        <td class="ard-bank-action-cell"><button type="button" class="btn btn-danger btn-xs js-remove-bank-row"><span class="fa fa-trash"></span></button></td>
                                    </tr>
                                    <?php $bankIndex++; endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <p class="help-block" style="margin-bottom:0;"><?php echo __('Use template placeholders: $bankAccounts, $primaryBankAccount, $bankName, $accountTitle, $accountNo, $iban, $bankBranch, $swiftCode.');?></p>
                        <div class="ard-bank-preview"><?php echo nl2br(h(!empty($paymentBankAccountPreview) ? $paymentBankAccountPreview : __('No active payment bank account saved yet.')));?></div>
                    </div>
                </div>
                <?php if($isAdr){?>
                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10">
                        <button type="button" class="btn btn-warning" id="OpenReceiptMergeModalBtn"><span class="fa fa-random"></span> <?php echo __('Merge Receipt Ledger (Popup)');?></button>
                        <p class="help-block" style="margin-bottom:0;"><?php echo __('Super Admin only. Move receipt transactions/payments from one ledger to another without deleting receipts.');?></p>
                    </div>
                </div>
                <?php }?>
                <div class="form-group">
                    <label class="col-sm-2 control-label"><?php echo __('Asset/Income Ledger Categories');?></label>
                    <div class="col-sm-4">
                        <?php echo $this->Form->textarea('ledger_asset_categories',array('label'=>false,'div'=>false,'class'=>'form-control','rows'=>4));?>
                        <p class="help-block" style="margin-bottom:0;"><?php echo __('One category per line. Used in Add/Edit Ledger when Type is Bank/Asset.');?></p>
                    </div>
                    <label class="col-sm-2 control-label"><?php echo __('Expense Ledger Categories');?></label>
                    <div class="col-sm-4">
                        <?php echo $this->Form->textarea('ledger_expense_categories',array('label'=>false,'div'=>false,'class'=>'form-control','rows'=>4));?>
                        <p class="help-block" style="margin-bottom:0;"><?php echo __('One category per line. Used in Add/Edit Ledger when Type is Expense.');?></p>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label"><?php echo __('Deal Charge Types');?></label>
                    <div class="col-sm-10">
                        <?php echo $this->Form->textarea('deal_charge_types',array('label'=>false,'div'=>false,'class'=>'form-control','rows'=>3));?>
                        <p class="help-block" style="margin-bottom:0;"><?php echo __('Agency-wise list. One charge type per line (example: Development, Extra Work). These appear in Deal Payment Plan > Deal Charges popup.');?></p>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label"><?php echo __('Refund Deduction %');?></label>
                    <div class="col-sm-4">
                        <?php echo $this->Form->text('refund_deduction_percent',array('label'=>false,'div'=>false,'class'=>'form-control'));?>
                        <p class="help-block" style="margin-bottom:0;"><?php echo __('Default deduction percentage used when creating Deal Refunds (agency-wise).');?></p>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10">
                        <label><?php echo $this->Form->checkbox('currency_space');?> <?php echo __('Currency Space');?></label>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label"><?php echo __('HR Departments');?></label>
                    <div class="col-sm-4">
                        <?php echo $this->Form->textarea('hr_department_types',array('label'=>false,'div'=>false,'class'=>'form-control','rows'=>4));?>
                        <p class="help-block" style="margin-bottom:0;"><?php echo __('One department per line for HR Payroll/Labour dropdowns.');?></p>
                    </div>
                    <label class="col-sm-2 control-label"><?php echo __('HR Designations');?></label>
                    <div class="col-sm-4">
                        <?php echo $this->Form->textarea('hr_designation_types',array('label'=>false,'div'=>false,'class'=>'form-control','rows'=>4));?>
                        <p class="help-block" style="margin-bottom:0;"><?php echo __('One designation per line for HR Payroll/Labour dropdowns.');?></p>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label"><?php echo __('Construction Departments');?></label>
                    <div class="col-sm-4">
                        <?php echo $this->Form->textarea('construction_department_types',array('label'=>false,'div'=>false,'class'=>'form-control','rows'=>4));?>
                        <p class="help-block" style="margin-bottom:0;"><?php echo __('One department per line for construction staff/labour.');?></p>
                    </div>
                    <label class="col-sm-2 control-label"><?php echo __('Construction Designations');?></label>
                    <div class="col-sm-4">
                        <?php echo $this->Form->textarea('construction_designation_types',array('label'=>false,'div'=>false,'class'=>'form-control','rows'=>4));?>
                        <p class="help-block" style="margin-bottom:0;"><?php echo __('One designation per line for construction staff/labour.');?></p>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label"><?php echo __('Balloting Blocks');?></label>
                    <div class="col-sm-4">
                        <?php echo $this->Form->textarea('balloting_block_types',array('label'=>false,'div'=>false,'class'=>'form-control','rows'=>4));?>
                        <p class="help-block" style="margin-bottom:0;"><?php echo __('One block per line. Used as Block dropdown in Balloting Unit forms.');?></p>
                    </div>
                    <label class="col-sm-2 control-label"><?php echo __('Street Addresses');?></label>
                    <div class="col-sm-4">
                        <?php echo $this->Form->textarea('balloting_street_address_types',array('label'=>false,'div'=>false,'class'=>'form-control','rows'=>4));?>
                        <p class="help-block" style="margin-bottom:0;"><?php echo __('One street address per line. Used as Street Address dropdown in Balloting Unit forms.');?></p>
                    </div>
                </div>
            <?php } elseif($activeTab==='vouchers'){?>
                <?php
                $voucherRows = array(
                    array('title'=>__('Income Voucher'),'prefix'=>'income_voucher_prefix','next'=>'income_voucher_next_no'),
                    array('title'=>__('Expense Voucher'),'prefix'=>'expense_voucher_prefix','next'=>'expense_voucher_next_no'),
                    array('title'=>__('Payroll Slip'),'prefix'=>'payroll_slip_prefix','next'=>'payroll_slip_next_no'),
                    array('title'=>__('Bill Receipt'),'prefix'=>'bill_receipt_prefix','next'=>'bill_receipt_next_no'),
                    array('title'=>__('Purchase Order (PO)'),'prefix'=>'po_prefix','next'=>'po_next_no'),
                    array('title'=>__('GRN'),'prefix'=>'grn_prefix','next'=>'grn_next_no'),
                    array('title'=>__('Purchase Invoice'),'prefix'=>'purchase_invoice_prefix','next'=>'purchase_invoice_next_no'),
                    array('title'=>__('Purchase Payment'),'prefix'=>'purchase_payment_prefix','next'=>'purchase_payment_next_no'),
                    array('title'=>__('Journal Voucher'),'prefix'=>'journal_voucher_prefix','next'=>'journal_voucher_next_no'),
                    array('title'=>__('Deal Invoice'),'prefix'=>'deal_invoice_prefix','next'=>'deal_invoice_next_no'),
                    array('title'=>__('Deal Payment Receipt'),'prefix'=>'deal_payment_receipt_prefix','next'=>'deal_payment_receipt_next_no'),
                    array('title'=>__('Rental Receipt'),'prefix'=>'rental_receipt_prefix','next'=>'rental_receipt_next_no'),
                    array('title'=>__('Rental Agreement'),'prefix'=>'rental_agreement_prefix','next'=>'rental_agreement_next_no'),
                    array('title'=>__('Letters Reference (General)'),'prefix'=>'letters_prefix','next'=>'letters_next_no'),
                    array('title'=>__('Welcome Letter'),'prefix'=>'welcome_letter_prefix','next'=>'welcome_letter_next_no'),
                    array('title'=>__('Booking Letter'),'prefix'=>'booking_letter_prefix','next'=>'booking_letter_next_no'),
                    array('title'=>__('Deal Charges Agreement'),'prefix'=>'charge_letter_prefix','next'=>'charge_letter_next_no'),
                    array('title'=>__('Deal Agreement Ashtam'),'prefix'=>'deal_agreement_letter_prefix','next'=>'deal_agreement_letter_next_no'),
                    array('title'=>__('Demarcation Letter'),'prefix'=>'demarcation_letter_prefix','next'=>'demarcation_letter_next_no'),
                    array('title'=>__('Design/Map Approval Letter'),'prefix'=>'design_map_approval_prefix','next'=>'design_map_approval_next_no'),
                    array('title'=>__('NOC Letter'),'prefix'=>'noc_letter_prefix','next'=>'noc_letter_next_no'),
                    array('title'=>__('Allotment Letter'),'prefix'=>'allot_letter_prefix','next'=>'allot_letter_next_no'),
                    array('title'=>__('Payment Demand Notice'),'prefix'=>'demand_letter_prefix','next'=>'demand_letter_next_no'),
                    array('title'=>__('Payment Acknowledgement'),'prefix'=>'receipt_letter_prefix','next'=>'receipt_letter_next_no'),
                    array('title'=>__('Possession Letter'),'prefix'=>'possession_letter_prefix','next'=>'possession_letter_next_no'),
                    array('title'=>__('Transfer / Mutation Letter'),'prefix'=>'transfer_letter_prefix','next'=>'transfer_letter_next_no'),
                    array('title'=>__('Cancellation Letter'),'prefix'=>'cancel_letter_prefix','next'=>'cancel_letter_next_no'),
                    array('title'=>__('Final Cancellation'),'prefix'=>'final_cancel_letter_prefix','next'=>'final_cancel_letter_next_no'),
                    array('title'=>__('Refund Agreement Letter'),'prefix'=>'refund_letter_prefix','next'=>'refund_letter_next_no'),
                    array('title'=>__('Employee ID'),'prefix'=>'employee_code_prefix','next'=>'employee_code_next_no')
                );
                $voucherSections = array(
                    array('title'=>__('Accounting Vouchers'), 'rows'=>array_slice($voucherRows, 0, 13)),
                    array('title'=>__('Letters & Notices'), 'rows'=>array_slice($voucherRows, 13, 15)),
                    array('title'=>__('HR Codes'), 'rows'=>array_slice($voucherRows, 28))
                );
                ?>
                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10">
                        <div class="ard-voucher-page">
                            <?php foreach($voucherSections as $voucherSection){ if(empty($voucherSection['rows'])){ continue; } ?>
                            <div class="ard-voucher-section">
                                <div class="ard-voucher-section-head">
                                    <strong><?php echo h($voucherSection['title']);?></strong>
                                    <span><?php echo count($voucherSection['rows']);?> <?php echo __('Rows');?></span>
                                </div>
                                <div class="ard-voucher-grid">
                                    <?php foreach($voucherSection['rows'] as $vRow){ ?>
                                    <div class="ard-voucher-card">
                                        <div class="ard-voucher-name"><?php echo h($vRow['title']);?></div>
                                        <div class="ard-voucher-fields">
                                            <div>
                                                <span class="ard-voucher-field-label"><?php echo __('Prefix');?></span>
                                                <?php echo $this->Form->input($vRow['prefix'],array('label'=>false,'div'=>false,'class'=>'form-control'));?>
                                            </div>
                                            <div>
                                                <span class="ard-voucher-field-label"><?php echo __('Start No');?></span>
                                                <?php echo $this->Form->input($vRow['next'],array('type'=>'number','min'=>1,'label'=>false,'div'=>false,'class'=>'form-control'));?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php } ?>
                                </div>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
                </div>
            <?php } elseif($activeTab==='construction'){?>
                <div class="form-group">
                    <label class="col-sm-2 control-label"><?php echo __('Material Types');?></label>
                    <div class="col-sm-4">
                        <?php echo $this->Form->textarea('construction_material_types',array('label'=>false,'div'=>false,'class'=>'form-control','rows'=>5));?>
                        <p class="help-block" style="margin-bottom:0;"><?php echo __('One per line. To link unit with material, use format: Material|Unit (example: Cement|Bag).');?></p>
                    </div>
                    <label class="col-sm-2 control-label"><?php echo __('Unit Types');?></label>
                    <div class="col-sm-4">
                        <?php echo $this->Form->textarea('construction_unit_types',array('label'=>false,'div'=>false,'class'=>'form-control','rows'=>5));?>
                        <p class="help-block" style="margin-bottom:0;"><?php echo __('Fallback/common units used in dropdowns.');?></p>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label"><?php echo __('Labour Types');?></label>
                    <div class="col-sm-4"><?php echo $this->Form->textarea('construction_labour_types',array('label'=>false,'div'=>false,'class'=>'form-control','rows'=>5));?></div>
                    <div class="col-sm-6" style="padding-top:28px;">
                        <label><?php echo $this->Form->checkbox('low_stock_auto_draft_po');?> <?php echo __('Auto Draft PO For Low Stock');?></label>
                    </div>
                </div>
            <?php } elseif($activeTab==='backup'){?>
                <div class="form-group">
                    <label class="col-sm-2 control-label"><?php echo __('Backup Keep Count');?></label>
                    <div class="col-sm-4"><?php echo $this->Form->input('backup_keep_count',array('label'=>false,'div'=>false,'class'=>'form-control'));?></div>
                    <label class="col-sm-2 control-label"><?php echo __('Auto Backup Frequency');?></label>
                    <div class="col-sm-4"><?php echo $this->Form->select('auto_backup_frequency',array('daily'=>__('Daily'),'weekly'=>__('Weekly'),'monthly'=>__('Monthly')),array('class'=>'form-control','label'=>false,'div'=>false));?></div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label"><?php echo __('Auto Backup Time');?></label>
                    <div class="col-sm-4"><?php echo $this->Form->input('auto_backup_time',array('label'=>false,'div'=>false,'class'=>'form-control','placeholder'=>'HH:MM'));?></div>
                    <label class="col-sm-2 control-label"><?php echo __('Last Auto Run');?></label>
                    <div class="col-sm-4">
                        <?php
                        $lastAutoRun = '';
                        if(!empty($this->request->data['Ardsetting']['auto_backup_last_run']))
                        {
                            $lastAutoRun = date('d-M-Y H:i', strtotime((string)$this->request->data['Ardsetting']['auto_backup_last_run']));
                        }
                        ?>
                        <input type="text" class="form-control" readonly="readonly" value="<?php echo h($lastAutoRun !== '' ? $lastAutoRun : '-'); ?>" />
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label"><?php echo __('Default Backup Password');?></label>
                    <div class="col-sm-4">
                        <input type="password" name="data[Ardsetting][backup_zip_password]" value="" class="form-control" autocomplete="new-password" placeholder="<?php echo __('Leave blank to keep existing'); ?>" />
                        <p class="help-block" style="margin-bottom:0;"><?php echo __('Used for new manual/auto backup zip protection.');?></p>
                    </div>
                    <label class="col-sm-2 control-label"><?php echo __('Password Status');?></label>
                    <div class="col-sm-4" style="padding-top:7px;">
                        <span class="label label-<?php echo !empty($backupPasswordConfigured) ? 'success' : 'default'; ?>"><?php echo !empty($backupPasswordConfigured) ? __('Configured') : __('Not Set'); ?></span>
                        <label style="margin-left:12px;font-weight:normal;"><?php echo $this->Form->checkbox('clear_backup_zip_password');?> <?php echo __('Clear Stored Password');?></label>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10">
                        <label><?php echo $this->Form->checkbox('daily_backup_enabled');?> <?php echo __('Daily Backup');?></label>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10">
                        <?php echo $this->Html->link('<span class="fa fa-database"></span> '.__('Open Backup Center'),array('controller'=>'Ardaccounts','action'=>'backups',$currentProjectId),array('escape'=>false,'class'=>'btn btn-info'));?>
                    </div>
                </div>
            <?php }?>

            <?php if($activeTab==='commission'){?>
                <div class="alert alert-info" style="margin-bottom:20px; font-size:12.5px;">
                    <i class="fa fa-info-circle"></i> <strong><?php echo __('Real Estate Commission Architecture & Policy Engine');?></strong><br>
                    <?php echo __('Configure how sales agent and broker commissions are accrued, locked, released across payment milestones, and disbursed with tax compliance.');?>
                </div>

                <!-- CARD 1: MASTER WORKFLOW TOGGLES -->
                <div class="panel panel-default" style="border:1px solid #d9e4f1; border-radius:8px; margin-bottom:20px;">
                    <div class="panel-heading" style="background:#07152a; color:#fff; font-weight:800; padding:10px 14px;">
                        <i class="fa fa-toggle-on"></i> <?php echo __('1. Commission Release & Protection Toggles');?>
                    </div>
                    <div class="panel-body" style="padding:16px;">
                        <div class="row">
                            <div class="col-sm-4" style="margin-bottom:15px;">
                                <label style="display:block; font-weight:800; margin-bottom:5px;">
                                    <?php echo __('Milestone-Based Release');?>
                                </label>
                                <label style="display:flex; align-items:center; gap:8px;">
                                    <?php echo $this->Form->checkbox('commission_milestone_release_enabled'); ?>
                                    <span><?php echo __('Enable Phased Milestones (ON / OFF)');?></span>
                                </label>
                                <p class="help-block" style="font-size:11px; margin-top:4px;">
                                    <?php echo __('ON: Releases commission across booking & installment stages. OFF: Credits 100% upfront.');?>
                                </p>
                            </div>
                            <div class="col-sm-4" style="margin-bottom:15px;">
                                <label style="display:block; font-weight:800; margin-bottom:5px;">
                                    <?php echo __('Cancellation Clawback Hold');?>
                                </label>
                                <label style="display:flex; align-items:center; gap:8px;">
                                    <?php echo $this->Form->checkbox('commission_cancellation_clawback_enabled'); ?>
                                    <span><?php echo __('Auto-Hold on Refund (ON / OFF)');?></span>
                                </label>
                                <p class="help-block" style="font-size:11px; margin-top:4px;">
                                    <?php echo __('Automatically locks unearned commission if a client requests deal refund or cancellation.');?>
                                </p>
                            </div>
                            <div class="col-sm-4" style="margin-bottom:15px;">
                                <label style="display:block; font-weight:800; margin-bottom:5px;">
                                    <?php echo __('Multi-Agent Split Deals');?>
                                </label>
                                <label style="display:flex; align-items:center; gap:8px;">
                                    <?php echo $this->Form->checkbox('commission_split_deals_enabled'); ?>
                                    <span><?php echo __('Enable Commission Splits (ON / OFF)');?></span>
                                </label>
                                <p class="help-block" style="font-size:11px; margin-top:4px;">
                                    <?php echo __('Allows splitting commission between lead agent, closing agent, and external channel partner.');?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CARD 2: CASH FLOW PROTECTION & COLLECTION THRESHOLD -->
                <div class="panel panel-default" style="border:1px solid #d9e4f1; border-radius:8px; margin-bottom:20px;">
                    <div class="panel-heading" style="background:#132a46; color:#fff; font-weight:800; padding:10px 14px;">
                        <i class="fa fa-shield"></i> <?php echo __('2. Cash Flow Safety & Collection Lock Threshold');?>
                    </div>
                    <div class="panel-body" style="padding:16px;">
                        <div class="row">
                            <div class="col-sm-4">
                                <label style="display:block; font-weight:800; margin-bottom:5px;">
                                    <?php echo __('Minimum Collection Lock');?>
                                </label>
                                <label style="display:flex; align-items:center; gap:8px;">
                                    <?php echo $this->Form->checkbox('commission_min_collection_lock_enabled'); ?>
                                    <span><?php echo __('Lock Payout Until Threshold (ON / OFF)');?></span>
                                </label>
                                <p class="help-block" style="font-size:11px; margin-top:4px;">
                                    <?php echo __('Prevents wallet withdrawal until client pays minimum recovery %.');?>
                                </p>
                            </div>
                            <div class="col-sm-4">
                                <label style="display:block; font-weight:800; margin-bottom:5px;">
                                    <?php echo __('Minimum Recovery Required (%)');?>
                                </label>
                                <div class="input-group">
                                    <?php echo $this->Form->input('commission_min_collection_percent', array('label'=>false, 'div'=>false, 'class'=>'form-control', 'type'=>'number', 'step'=>'0.01', 'min'=>'0', 'max'=>'100')); ?>
                                    <span class="input-group-addon">%</span>
                                </div>
                                <p class="help-block" style="font-size:11px; margin-top:4px;">
                                    <?php echo __('E.g. 15%: Client must pay at least 15% of total deal before commission can be withdrawn.');?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CARD 3: MILESTONE RELEASE TRANCHES (% BREAKDOWN) -->
                <div class="panel panel-default" style="border:1px solid #d9e4f1; border-radius:8px; margin-bottom:20px;">
                    <div class="panel-heading" style="background:#1e3a5f; color:#fff; font-weight:800; padding:10px 14px;">
                        <i class="fa fa-tasks"></i> <?php echo __('3. Milestone Release Tranches (Percentage Schedule)');?>
                    </div>
                    <div class="panel-body" style="padding:16px;">
                        <p class="text-muted" style="margin-bottom:12px; font-size:12px;">
                            <?php echo __('Configure what percentage of the deal commission unlocks at each phase of client recovery. (Must total 100%)');?>
                        </p>
                        <div class="row">
                            <div class="col-sm-4">
                                <label style="display:block; font-weight:800;"><?php echo __('Tranche 1: On Down Payment / Booking');?></label>
                                <div class="input-group">
                                    <?php echo $this->Form->input('commission_tranche1_percent', array('label'=>false, 'div'=>false, 'class'=>'form-control', 'type'=>'number', 'step'=>'0.01', 'min'=>'0', 'max'=>'100')); ?>
                                    <span class="input-group-addon">%</span>
                                </div>
                                <p class="help-block" style="font-size:11px;"><?php echo __('Default: 40% upon booking payment confirmation.');?></p>
                            </div>
                            <div class="col-sm-4">
                                <label style="display:block; font-weight:800;"><?php echo __('Tranche 2: On Mid-Way Recovery / 50% Paid');?></label>
                                <div class="input-group">
                                    <?php echo $this->Form->input('commission_tranche2_percent', array('label'=>false, 'div'=>false, 'class'=>'form-control', 'type'=>'number', 'step'=>'0.01', 'min'=>'0', 'max'=>'100')); ?>
                                    <span class="input-group-addon">%</span>
                                </div>
                                <p class="help-block" style="font-size:11px;"><?php echo __('Default: 30% after ongoing installments reach milestone.');?></p>
                            </div>
                            <div class="col-sm-4">
                                <label style="display:block; font-weight:800;"><?php echo __('Tranche 3: On Final Possession / Full Recovery');?></label>
                                <div class="input-group">
                                    <?php echo $this->Form->input('commission_tranche3_percent', array('label'=>false, 'div'=>false, 'class'=>'form-control', 'type'=>'number', 'step'=>'0.01', 'min'=>'0', 'max'=>'100')); ?>
                                    <span class="input-group-addon">%</span>
                                </div>
                                <p class="help-block" style="font-size:11px;"><?php echo __('Default: 30% on handover / full settlement.');?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CARD 4: FBR WITHHOLDING TAX (SECTION 233) DEDUCTIONS -->
                <div class="panel panel-default" style="border:1px solid #d9e4f1; border-radius:8px; margin-bottom:20px;">
                    <div class="panel-heading" style="background:#274564; color:#fff; font-weight:800; padding:10px 14px;">
                        <i class="fa fa-university"></i> <?php echo __('4. FBR Withholding Tax (WHT Section 233) Slabs');?>
                    </div>
                    <div class="panel-body" style="padding:16px;">
                        <div class="row">
                            <div class="col-sm-4">
                                <label style="display:block; font-weight:800; margin-bottom:5px;">
                                    <?php echo __('WHT Deduction at Payout');?>
                                </label>
                                <label style="display:flex; align-items:center; gap:8px;">
                                    <?php echo $this->Form->checkbox('commission_wht_deduction_enabled'); ?>
                                    <span><?php echo __('Enable Tax Deduction (ON / OFF)');?></span>
                                </label>
                                <p class="help-block" style="font-size:11px; margin-top:4px;">
                                    <?php echo __('Automatically deducts withholding tax on commission payout vouchers.');?>
                                </p>
                            </div>
                            <div class="col-sm-4">
                                <label style="display:block; font-weight:800; margin-bottom:5px;">
                                    <?php echo __('Active Filer WHT Rate (%)');?>
                                </label>
                                <div class="input-group">
                                    <?php echo $this->Form->input('commission_wht_filer_percent', array('label'=>false, 'div'=>false, 'class'=>'form-control', 'type'=>'number', 'step'=>'0.01', 'min'=>'0', 'max'=>'100')); ?>
                                    <span class="input-group-addon">%</span>
                                </div>
                                <p class="help-block" style="font-size:11px; margin-top:4px;">
                                    <?php echo __('Standard FBR rate for registered active filers (e.g. 12%).');?>
                                </p>
                            </div>
                            <div class="col-sm-4">
                                <label style="display:block; font-weight:800; margin-bottom:5px;">
                                    <?php echo __('Non-Filer WHT Rate (%)');?>
                                </label>
                                <div class="input-group">
                                    <?php echo $this->Form->input('commission_wht_non_filer_percent', array('label'=>false, 'div'=>false, 'class'=>'form-control', 'type'=>'number', 'step'=>'0.01', 'min'=>'0', 'max'=>'100')); ?>
                                    <span class="input-group-addon">%</span>
                                </div>
                                <p class="help-block" style="font-size:11px; margin-top:4px;">
                                    <?php echo __('Higher penalty rate for non-filers (e.g. 24%).');?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CARD 5: DEFAULT RATES & DOUBLE-ENTRY LEDGER MAPPING -->
                <div class="panel panel-default" style="border:1px solid #d9e4f1; border-radius:8px; margin-bottom:20px;">
                    <div class="panel-heading" style="background:#0b2240; color:#fff; font-weight:800; padding:10px 14px;">
                        <i class="fa fa-book"></i> <?php echo __('5. Default Rates & Double-Entry Accounting Mapping');?>
                    </div>
                    <div class="panel-body" style="padding:16px;">
                        <div class="row" style="margin-bottom:14px;">
                            <div class="col-sm-4">
                                <label style="display:block; font-weight:800;"><?php echo __('Default Commission Rate');?></label>
                                <?php echo $this->Form->input('default_agent_commission_rate', array('label'=>false, 'div'=>false, 'class'=>'form-control', 'type'=>'number', 'step'=>'0.01', 'min'=>'0')); ?>
                                <p class="help-block" style="font-size:11px;"><?php echo __('Standard rate pre-filled on new property listings.');?></p>
                            </div>
                            <div class="col-sm-4">
                                <label style="display:block; font-weight:800;"><?php echo __('Default Calculation Type');?></label>
                                <?php echo $this->Form->select('default_agent_commission_type', array('percentage'=>__('Percentage of Deal Value (%)'), 'value'=>__('Fixed Lump Sum Value (PKR)')), array('empty'=>false, 'class'=>'form-control')); ?>
                                <p class="help-block" style="font-size:11px;"><?php echo __('Calculation mode (Percentage or Fixed PKR).');?></p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4">
                                <label style="display:block; font-weight:800;"><?php echo __('Commission Expense Ledger');?></label>
                                <?php echo $this->Form->select('agent_commission_expense_ledger_id', $defaultExpenseLedgerOptions, array('empty'=>__('Auto Default (Agent Commission Expense)'), 'class'=>'form-control')); ?>
                                <p class="help-block" style="font-size:11px;"><?php echo __('Debited when commission is accrued on deal.');?></p>
                            </div>
                            <div class="col-sm-4">
                                <label style="display:block; font-weight:800;"><?php echo __('Commission Payable Ledger (Liability)');?></label>
                                <?php echo $this->Form->select('agent_commission_payable_ledger_id', isset($businessLedgerOptionsByType['liability']) ? $businessLedgerOptionsByType['liability'] : array(), array('empty'=>__('Auto Default (Agent Commission Payable)'), 'class'=>'form-control')); ?>
                                <p class="help-block" style="font-size:11px;"><?php echo __('Credited on deal, debited on payout voucher.');?></p>
                            </div>
                            <div class="col-sm-4">
                                <label style="display:block; font-weight:800;"><?php echo __('Tax / WHT Payable Ledger');?></label>
                                <?php echo $this->Form->select('commission_wht_payable_ledger_id', isset($businessLedgerOptionsByType['liability']) ? $businessLedgerOptionsByType['liability'] : array(), array('empty'=>__('Auto Default (Tax Payable / FBR WHT)'), 'class'=>'form-control')); ?>
                                <p class="help-block" style="font-size:11px;"><?php echo __('Credited when tax is deducted at source.');?></p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php }?>

            <?php if($activeTab==='bulk'){?>
                <!-- BULK VOUCHERS CONFIGURATION -->
                <div class="alert alert-info" style="margin-bottom:20px; font-size:12.5px;">
                    <i class="fa fa-info-circle"></i> <strong><?php echo __('Bulk Voucher Heads & Policy Configuration');?></strong><br>
                    <?php echo __('Configure which default account heads appear on the Bulk Voucher entry form for daily expenses (e.g. Office, Bike/Motorcycle, Kitchen/Mess, Car Fuel, Advertisement, Material) and income (e.g. Booking, Installments, Rentals), plus cash/bank payment source and changeable date permissions.');?>
                </div>

                <!-- CARD 1: EXPENSE ACCOUNT HEADS -->
                <div class="panel panel-default" style="border:1px solid #d9e4f1; border-radius:8px; margin-bottom:20px;">
                    <div class="panel-heading" style="background:#07152a; color:#fff; font-weight:800; padding:10px 14px; display:flex; justify-content:space-between; align-items:center;">
                        <span><i class="fa fa-credit-card"></i> <?php echo __('1. Default Expense Account Heads for Bulk Entry');?></span>
                        <span style="font-weight:400; font-size:11.5px; color:#cbd5e1;"><?php echo __('Selected heads will pre-populate the daily bulk expense grid');?></span>
                    </div>
                    <div class="panel-body" style="padding:16px;">
                        <p class="help-block" style="margin-bottom:12px; font-size:12px;">
                            <i class="fa fa-lightbulb-o text-warning"></i> <?php echo __('Check the expense accounts frequently used (e.g. Office, Bike, Kitchen / Grocery, Car Expense / Fuel, Marketing / Ads, Material). Ledgers with 0 amount on bulk entry are safely ignored during posting.');?>
                        </p>
                        <div style="margin-bottom:10px;">
                            <input type="text" id="bulkExpSearch" class="form-control input-sm" placeholder="<?php echo __('Quick search expense heads...');?>" style="max-width:320px; display:inline-block;">
                            <button type="button" class="btn btn-xs btn-default" onclick="jQuery('.bulk-exp-cb').prop('checked', true);"><?php echo __('Select All');?></button>
                            <button type="button" class="btn btn-xs btn-default" onclick="jQuery('.bulk-exp-cb').prop('checked', false);"><?php echo __('Clear All');?></button>
                        </div>
                        <div class="row" id="bulkExpGrid" style="max-height:360px; overflow-y:auto; border:1px solid #e2e8f0; border-radius:6px; padding:12px 6px; background:#fafcff;">
                            <?php
                            $configuredExpSet = !empty($configuredBulkExpenseIds) ? array_flip($configuredBulkExpenseIds) : array();
                            if(!empty($bulkExpenseLedgers)):
                                foreach($bulkExpenseLedgers as $bExpRow):
                                    $bId = (int)$bExpRow['Ardledger']['id'];
                                    $bName = trim((string)$bExpRow['Ardledger']['name']);
                                    $bCat = trim((string)$bExpRow['Ardledger']['category']);
                                    $isChecked = isset($configuredExpSet[$bId]);
                            ?>
                                <div class="col-sm-4 bulk-exp-item" style="margin-bottom:8px;">
                                    <label style="font-weight:normal; cursor:pointer; font-size:12px; display:flex; align-items:center; gap:6px; margin:0; padding:6px 8px; border-radius:4px; border:1px solid #edf2f7; background:#fff;">
                                        <input type="checkbox" name="data[Ardsetting][bulk_expense_ledger_ids][]" value="<?php echo $bId;?>" class="bulk-exp-cb" <?php echo $isChecked ? 'checked' : '';?>>
                                        <span style="font-weight:600; color:#1e293b;"><?php echo h($bName);?></span>
                                        <?php if($bCat !== ''): ?><span class="label label-default" style="font-size:9.5px; margin-left:auto;"><?php echo h($bCat);?></span><?php endif; ?>
                                    </label>
                                </div>
                            <?php
                                endforeach;
                            else:
                            ?>
                                <div class="col-sm-12 text-muted" style="padding:10px;"><?php echo __('No expense ledgers found for this agency.');?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- CARD 2: INCOME ACCOUNT HEADS -->
                <div class="panel panel-default" style="border:1px solid #d9e4f1; border-radius:8px; margin-bottom:20px;">
                    <div class="panel-heading" style="background:#07152a; color:#fff; font-weight:800; padding:10px 14px; display:flex; justify-content:space-between; align-items:center;">
                        <span><i class="fa fa-arrow-circle-down"></i> <?php echo __('2. Default Income Account Heads for Bulk Entry');?></span>
                        <span style="font-weight:400; font-size:11.5px; color:#cbd5e1;"><?php echo __('Selected heads will pre-populate the daily bulk income grid');?></span>
                    </div>
                    <div class="panel-body" style="padding:16px;">
                        <p class="help-block" style="margin-bottom:12px; font-size:12px;">
                            <i class="fa fa-lightbulb-o text-warning"></i> <?php echo __('Check the revenue accounts frequently credited (e.g. Booking Income, Installment Income, Rental Income, Transfer Fee, Charges). Ledgers with 0 amount are safely ignored during posting.');?>
                        </p>
                        <div style="margin-bottom:10px;">
                            <input type="text" id="bulkIncSearch" class="form-control input-sm" placeholder="<?php echo __('Quick search income heads...');?>" style="max-width:320px; display:inline-block;">
                            <button type="button" class="btn btn-xs btn-default" onclick="jQuery('.bulk-inc-cb').prop('checked', true);"><?php echo __('Select All');?></button>
                            <button type="button" class="btn btn-xs btn-default" onclick="jQuery('.bulk-inc-cb').prop('checked', false);"><?php echo __('Clear All');?></button>
                        </div>
                        <div class="row" id="bulkIncGrid" style="max-height:360px; overflow-y:auto; border:1px solid #e2e8f0; border-radius:6px; padding:12px 6px; background:#fafcff;">
                            <?php
                            $configuredIncSet = !empty($configuredBulkIncomeIds) ? array_flip($configuredBulkIncomeIds) : array();
                            if(!empty($bulkIncomeLedgers)):
                                foreach($bulkIncomeLedgers as $bIncRow):
                                    $biId = (int)$bIncRow['Ardledger']['id'];
                                    $biName = trim((string)$bIncRow['Ardledger']['name']);
                                    $biCat = trim((string)$bIncRow['Ardledger']['category']);
                                    $isCheckedInc = isset($configuredIncSet[$biId]);
                            ?>
                                <div class="col-sm-4 bulk-inc-item" style="margin-bottom:8px;">
                                    <label style="font-weight:normal; cursor:pointer; font-size:12px; display:flex; align-items:center; gap:6px; margin:0; padding:6px 8px; border-radius:4px; border:1px solid #edf2f7; background:#fff;">
                                        <input type="checkbox" name="data[Ardsetting][bulk_income_ledger_ids][]" value="<?php echo $biId;?>" class="bulk-inc-cb" <?php echo $isCheckedInc ? 'checked' : '';?>>
                                        <span style="font-weight:600; color:#1e293b;"><?php echo h($biName);?></span>
                                        <?php if($biCat !== ''): ?><span class="label label-info" style="font-size:9.5px; margin-left:auto;"><?php echo h($biCat);?></span><?php endif; ?>
                                    </label>
                                </div>
                            <?php
                                endforeach;
                            else:
                            ?>
                                <div class="col-sm-12 text-muted" style="padding:10px;"><?php echo __('No income ledgers found for this agency.');?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- CARD 3: DEFAULT SOURCE & DATE POLICIES -->
                <div class="panel panel-default" style="border:1px solid #d9e4f1; border-radius:8px; margin-bottom:20px;">
                    <div class="panel-heading" style="background:#07152a; color:#fff; font-weight:800; padding:10px 14px;">
                        <i class="fa fa-sliders"></i> <?php echo __('3. Bulk Voucher Source Account & Date Policy');?>
                    </div>
                    <div class="panel-body" style="padding:16px;">
                        <div class="row">
                            <div class="col-sm-6" style="margin-bottom:15px;">
                                <label style="display:block; font-weight:800;"><?php echo __('Default Payment / Receipt Source Account');?></label>
                                <?php
                                $sourceSelectOptions = array(0 => __('Auto / Cash in Hand'));
                                if(!empty($bulkSourceLedgers))
                                {
                                    foreach($bulkSourceLedgers as $bsId => $bsName)
                                    {
                                        $sourceSelectOptions[$bsId] = $bsName;
                                    }
                                }
                                echo $this->Form->select('bulk_default_source_ledger_id', $sourceSelectOptions, array('empty'=>false, 'class'=>'form-control'));
                                ?>
                                <p class="help-block" style="font-size:11px; margin-top:4px;">
                                    <?php echo __('Pre-selected Cash or Bank Account on the Bulk Voucher entry form.');?>
                                </p>
                            </div>
                            <div class="col-sm-6" style="margin-bottom:15px;">
                                <label style="display:block; font-weight:800; margin-bottom:6px;">
                                    <?php echo $this->Form->checkbox('bulk_voucher_custom_date_enabled'); ?>
                                    <span><?php echo __('Allow Changeable Voucher Date (ON / OFF)');?></span>
                                </label>
                                <p class="help-block" style="font-size:11px; margin-top:4px;">
                                    <?php echo __('ON: Users can select and change the voucher date (e.g. for past day vouchers). OFF: Date is locked to today’s date.');?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <script type="text/javascript">
                jQuery(function($){
                    $('#bulkExpSearch').on('keyup', function(){
                        var val = $.trim($(this).val()).toLowerCase();
                        $('#bulkExpGrid .bulk-exp-item').each(function(){
                            $(this).toggle($(this).text().toLowerCase().indexOf(val) > -1);
                        });
                    });
                    $('#bulkIncSearch').on('keyup', function(){
                        var val = $.trim($(this).val()).toLowerCase();
                        $('#bulkIncGrid .bulk-inc-item').each(function(){
                            $(this).toggle($(this).text().toLowerCase().indexOf(val) > -1);
                        });
                    });
                });
                </script>
            <?php }?>

            <div class="form-group">
                <div class="col-sm-offset-2 col-sm-10">
                    <button type="submit" class="btn btn-success"><span class="fa fa-save"></span> <?php echo __('Update Settings');?></button>
                </div>
            </div>
            <?php echo $this->Form->end();?>
            <?php if($activeTab==='general' && $isAdr){?>
            <div class="modal fade" id="ReceiptMergeModal" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title"><?php echo __('Manual Receipt Ledger Merge');?></h4>
                        </div>
                        <div class="modal-body">
                            <?php echo $this->Form->create(false,array(
                                'id'=>'SettingsReceiptMergeForm',
                                'url'=>array('controller'=>'Ardaccounts','action'=>'normalizereceiptaccounts'),
                                'class'=>'form-horizontal'
                            ));?>
                            <?php echo $this->Form->hidden('manual_merge',array('value'=>1));?>
                            <?php echo $this->Form->hidden('return_to_settings',array('value'=>1));?>
                            <?php echo $this->Form->hidden('return_tab',array('value'=>'general'));?>
                            <?php echo $this->Form->hidden('project_id',array('value'=>(int)$currentProjectId,'id'=>'SettingsReceiptMergeProjectId'));?>
                            <div class="row">
                                <div class="col-sm-4">
                                    <label><?php echo __('Merge From Ledger');?></label>
                                    <?php echo $this->Form->select('source_ledger_id',isset($receiptMergeLedgerOptions)?(array)$receiptMergeLedgerOptions:array(),array('empty'=>__('Please Select'),'class'=>'form-control','id'=>'SettingsReceiptMergeSourceLedger'));?>
                                </div>
                                <div class="col-sm-4">
                                    <label><?php echo __('Merge To Ledger');?></label>
                                    <?php echo $this->Form->select('target_ledger_id',isset($receiptMergeLedgerOptions)?(array)$receiptMergeLedgerOptions:array(),array('empty'=>__('Please Select'),'class'=>'form-control','id'=>'SettingsReceiptMergeTargetLedger'));?>
                                </div>
                                <div class="col-sm-4">
                                    <label><?php echo __('Move Scope');?></label>
                                    <?php echo $this->Form->select('merge_mode',array(
                                        'all'=>__('All Receipt Entries'),
                                        'selected_receipts'=>__('Only Selected Receipt/Ref Numbers'),
                                        'selected_transactions'=>__('Only Selected Transaction IDs')
                                    ),array('empty'=>false,'class'=>'form-control','value'=>'all','id'=>'SettingsReceiptMergeMode'));?>
                                </div>
                            </div>
                            <div class="row" style="margin-top:10px;">
                                <div class="col-sm-6" id="SettingsReceiptRefsWrap">
                                    <label><?php echo __('Selected Receipt/Ref Numbers');?></label>
                                    <?php echo $this->Form->textarea('selected_receipt_refs',array('class'=>'form-control','rows'=>3,'id'=>'SettingsReceiptMergeRefs','placeholder'=>__("Example:\nDPR-2026-10\nDPR-2026-11")));?>
                                </div>
                                <div class="col-sm-6" id="SettingsReceiptTxIdsWrap">
                                    <label><?php echo __('Selected Transaction IDs');?></label>
                                    <?php echo $this->Form->textarea('selected_transaction_ids',array('class'=>'form-control','rows'=>3,'id'=>'SettingsReceiptMergeTxIds','placeholder'=>__("Example:\n101\n102")));?>
                                </div>
                            </div>
                            <div class="row" style="margin-top:10px;">
                                <div class="col-sm-12">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-info btn-sm" id="SettingsReceiptMergeLoadBtn"><span class="fa fa-search"></span> <?php echo __('Load Entries');?></button>
                                        <button type="button" class="btn btn-default btn-sm" id="SettingsReceiptMergeSelectAllBtn"><span class="fa fa-check-square-o"></span> <?php echo __('Select All');?></button>
                                        <button type="button" class="btn btn-default btn-sm" id="SettingsReceiptMergeClearSelectionBtn"><span class="fa fa-square-o"></span> <?php echo __('Clear Selection');?></button>
                                    </div>
                                    <small class="help-block" id="SettingsReceiptMergePickerHint" style="margin-bottom:0;"><?php echo __('Choose source ledger and click Load Entries. Select rows visually, then merge.');?></small>
                                </div>
                            </div>
                            <div class="row" style="margin-top:10px;">
                                <div class="col-sm-12">
                                    <div class="table-responsive" style="max-height:320px;overflow:auto;border:1px solid #d9e3f1;border-radius:6px;">
                                        <table class="table table-striped table-condensed" style="margin-bottom:0;">
                                            <thead>
                                                <tr>
                                                    <th style="width:34px;"><input type="checkbox" id="SettingsReceiptMergeCheckAll"></th>
                                                    <th style="width:94px;"><?php echo __('Date');?></th>
                                                    <th style="width:108px;"><?php echo __('Tx ID');?></th>
                                                    <th style="width:150px;"><?php echo __('Receipt/Ref');?></th>
                                                    <th style="width:120px;" class="text-right"><?php echo __('Amount');?></th>
                                                    <th style="width:110px;" class="text-center"><?php echo __('Linked Payments');?></th>
                                                    <th><?php echo __('Description');?></th>
                                                </tr>
                                            </thead>
                                            <tbody id="SettingsReceiptMergePickerBody">
                                                <tr><td colspan="7" class="text-muted text-center" style="padding:14px;"><?php echo __('No entries loaded.');?></td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <p class="help-block" style="margin:10px 0 0 0;">
                                <?php echo __('This moves selected receipt transactions/payments to the target ledger without deleting receipts. Entry dates stay unchanged, so date-based sorting remains correct.');?>
                            </p>
                            <div style="margin-top:12px;">
                                <button type="submit" class="btn btn-warning" onclick="return confirm('<?php echo addslashes(__('This will move selected receipt transactions/payments to the target ledger. Continue?'));?>');"><span class="fa fa-random"></span> <?php echo __('Merge Receipt Ledger');?></button>
                            </div>
                            <?php echo $this->Form->end();?>
                        </div>
                    </div>
                </div>
            </div>
            <?php }?>
        <?php }?>
    </div>
</div>
<?php if($isAdr){?>
<script type="text/javascript">
jQuery(function($){
    var $projectSelect = $('#ArdSettingsProjectId');
    if(!$projectSelect.length)
    {
        return;
    }
    $projectSelect.on('change', function(){
        var projectId = parseInt($(this).val(), 10) || 0;
        if(projectId > 0)
        {
            var tab = <?php echo json_encode($activeTab);?>;
            window.location = <?php echo json_encode($this->Html->url(array('controller'=>'Ardaccounts','action'=>'settings')));?> + '/' + projectId + '?tab=' + encodeURIComponent(tab);
        }
    });
});
</script>
<?php }?>
<?php if($activeTab==='general' && $isAdr){?>
<script type="text/javascript">
jQuery(function($){
    var mergeEntriesUrl = <?php echo json_encode($this->Html->url(array('controller'=>'Ardaccounts','action'=>'receiptmergeentries')));?>;
    var $projectSelect = $('#ArdSettingsProjectId');
    var $projectIdField = $('#SettingsReceiptMergeProjectId');
    var $mode = $('#SettingsReceiptMergeMode');
    var $refs = $('#SettingsReceiptMergeRefs');
    var $txIds = $('#SettingsReceiptMergeTxIds');
    var $pickerBody = $('#SettingsReceiptMergePickerBody');
    var $checkAll = $('#SettingsReceiptMergeCheckAll');
    var $hint = $('#SettingsReceiptMergePickerHint');

    function currentProjectId()
    {
        if($projectSelect.length)
        {
            var pid = parseInt($projectSelect.val(), 10);
            if(!isNaN(pid) && pid > 0)
            {
                return pid;
            }
        }
        var hiddenPid = parseInt($projectIdField.val(), 10);
        if(!isNaN(hiddenPid) && hiddenPid > 0)
        {
            return hiddenPid;
        }
        return <?php echo (int)$currentProjectId;?>;
    }

    function htmlEscape(v)
    {
        return $('<div/>').text(String(v || '')).html();
    }

    function renderRows(rows)
    {
        rows = $.isArray(rows) ? rows : [];
        var html = '';
        if(rows.length <= 0)
        {
            html = '<tr><td colspan="7" class="text-muted text-center" style="padding:14px;"><?php echo addslashes(__('No matching receipt entries found for this source ledger.'));?></td></tr>';
            $pickerBody.html(html);
            $checkAll.prop('checked', false);
            syncSelectionToInputs(true);
            return;
        }
        for(var i=0;i<rows.length;i++)
        {
            var r = rows[i] || {};
            var txId = String(r.id || '');
            var refNo = String(r.ref_no || '');
            var amountVal = Number(r.amount || 0);
            if(isNaN(amountVal))
            {
                amountVal = 0;
            }
            html += '<tr>' +
                '<td><input type="checkbox" class="js-settings-merge-row" data-tx-id="'+htmlEscape(txId)+'" data-ref-no="'+htmlEscape(refNo)+'"></td>' +
                '<td>'+htmlEscape(r.entry_date || '')+'</td>' +
                '<td>'+htmlEscape(txId)+'</td>' +
                '<td>'+htmlEscape(refNo !== '' ? refNo : '-')+'</td>' +
                '<td class="text-right"><?php echo addslashes($settingsCurrencyText);?> '+htmlEscape(amountVal.toLocaleString())+'</td>' +
                '<td class="text-center">'+htmlEscape(r.payment_links || 0)+'</td>' +
                '<td>'+htmlEscape(r.description || '')+'</td>' +
            '</tr>';
        }
        $pickerBody.html(html);
        $checkAll.prop('checked', false);
        syncSelectionToInputs(true);
    }

    function selectedRows()
    {
        return $pickerBody.find('.js-settings-merge-row:checked');
    }

    function syncSelectionToInputs(forceClear)
    {
        var mode = $mode.val() || 'all';
        var refs = [];
        var txIds = [];
        selectedRows().each(function(){
            var $r = $(this);
            var txId = String($r.attr('data-tx-id') || '').trim();
            var refNo = String($r.attr('data-ref-no') || '').trim();
            if(txId !== '')
            {
                txIds.push(txId);
            }
            if(refNo !== '')
            {
                refs.push(refNo);
            }
        });
        if(mode === 'selected_transactions')
        {
            if(txIds.length > 0 || forceClear === true)
            {
                $txIds.val(txIds.join("\n"));
            }
        }
        else if(mode === 'selected_receipts')
        {
            if(refs.length > 0 || forceClear === true)
            {
                $refs.val(refs.join("\n"));
            }
        }
        else if(forceClear === true)
        {
            $refs.val('');
            $txIds.val('');
        }
    }

    function toggleModeFields()
    {
        var mode = $mode.val() || 'all';
        if(mode === 'selected_receipts')
        {
            $('#SettingsReceiptRefsWrap').show();
            $('#SettingsReceiptTxIdsWrap').hide();
        }
        else if(mode === 'selected_transactions')
        {
            $('#SettingsReceiptRefsWrap').hide();
            $('#SettingsReceiptTxIdsWrap').show();
        }
        else
        {
            $('#SettingsReceiptRefsWrap').hide();
            $('#SettingsReceiptTxIdsWrap').hide();
            syncSelectionToInputs(true);
        }
    }

    $('#OpenReceiptMergeModalBtn').on('click', function(e){
        e.preventDefault();
        $projectIdField.val(currentProjectId());
        $('#ReceiptMergeModal').modal('show');
    });

    $('#SettingsReceiptMergeLoadBtn').on('click', function(e){
        e.preventDefault();
        var sourceLedgerId = parseInt($('#SettingsReceiptMergeSourceLedger').val(), 10);
        if(isNaN(sourceLedgerId) || sourceLedgerId <= 0)
        {
            alert('<?php echo addslashes(__('Please select source ledger first.'));?>');
            return;
        }
        var pid = currentProjectId();
        $projectIdField.val(pid);
        var $btn = $(this);
        var oldHtml = $btn.html();
        $btn.prop('disabled', true).html('<span class="fa fa-spinner fa-spin"></span> <?php echo addslashes(__('Loading...'));?>');
        $.getJSON(mergeEntriesUrl, { project_id: pid, source_ledger_id: sourceLedgerId, limit: 1200, _: (new Date().getTime()) })
            .done(function(resp){
                if(!resp || !resp.ok)
                {
                    renderRows([]);
                    $hint.text((resp && resp.message) ? String(resp.message) : '<?php echo addslashes(__('Unable to load entries.'));?>');
                    return;
                }
                renderRows(resp.rows || []);
                $hint.text('<?php echo addslashes(__('Loaded entries. Select rows and merge.'));?>');
            })
            .fail(function(){
                renderRows([]);
                $hint.text('<?php echo addslashes(__('Unable to load entries. Please try again.'));?>');
            })
            .always(function(){
                $btn.prop('disabled', false).html(oldHtml);
            });
    });

    $('#SettingsReceiptMergeSelectAllBtn').on('click', function(e){
        e.preventDefault();
        $checkAll.prop('checked', true).trigger('change');
    });
    $('#SettingsReceiptMergeClearSelectionBtn').on('click', function(e){
        e.preventDefault();
        $checkAll.prop('checked', false);
        $pickerBody.find('.js-settings-merge-row').prop('checked', false);
        syncSelectionToInputs(true);
    });
    $checkAll.on('change', function(){
        var checked = $(this).is(':checked');
        $pickerBody.find('.js-settings-merge-row').prop('checked', checked);
        syncSelectionToInputs(false);
    });
    $(document).on('change', '.js-settings-merge-row', function(){
        var total = $pickerBody.find('.js-settings-merge-row').length;
        var checked = selectedRows().length;
        $checkAll.prop('checked', total > 0 && total === checked);
        syncSelectionToInputs(false);
    });
    $mode.on('change', toggleModeFields);
    $('#SettingsReceiptMergeSourceLedger').on('change', function(){
        renderRows([]);
        $hint.text('<?php echo addslashes(__('Choose source ledger and click Load Entries. Select rows visually, then merge.'));?>');
    });
    $('#SettingsReceiptMergeForm').on('submit', function(){
        $projectIdField.val(currentProjectId());
        syncSelectionToInputs(false);
        var mode = $mode.val() || 'all';
        if(mode === 'selected_receipts' && $.trim($refs.val()) === '')
        {
            alert('<?php echo addslashes(__('Please select rows or enter receipt/reference numbers.'));?>');
            return false;
        }
        if(mode === 'selected_transactions' && $.trim($txIds.val()) === '')
        {
            alert('<?php echo addslashes(__('Please select rows or enter transaction IDs.'));?>');
            return false;
        }
        return true;
    });

    toggleModeFields();
});
</script>
<?php }?>
<?php if($activeTab==='general'){?>
<script type="text/javascript">
jQuery(function($){
    var nextBankIndex = <?php echo isset($bankIndex) ? (int)$bankIndex : 0;?>;
    var $bankTable = $('#PaymentBankAccountsTable');

    function bankRowHtml(index)
    {
        var base = 'data[Ardpaymentbankaccount][' + index + ']';
        return '<tr class="js-bank-account-row">' +
            '<td><input type="hidden" name="'+base+'[id]" value="0" />' +
            '<input type="hidden" class="js-bank-delete" name="'+base+'[delete]" value="0" />' +
            '<input type="text" class="form-control" name="'+base+'[bank_name]" value="" /></td>' +
            '<td><input type="text" class="form-control" name="'+base+'[account_title]" value="" /></td>' +
            '<td><input type="text" class="form-control" name="'+base+'[account_no]" value="" /></td>' +
            '<td><input type="text" class="form-control" name="'+base+'[iban]" value="" /></td>' +
            '<td><input type="text" class="form-control" name="'+base+'[branch]" value="" /></td>' +
            '<td><input type="text" class="form-control" name="'+base+'[swift_code]" value="" /></td>' +
            '<td><textarea class="form-control" name="'+base+'[instructions]" rows="1"></textarea></td>' +
            '<td class="ard-bank-primary-cell"><input type="radio" name="data[Ardpaymentbankaccount_primary]" class="js-bank-primary-radio" value="'+index+'" />' +
            '<input type="hidden" class="js-bank-primary-value" name="'+base+'[is_primary]" value="0" /></td>' +
            '<td class="ard-bank-status-cell"><select class="form-control" name="'+base+'[status]"><option value="active"><?php echo addslashes(__('Active'));?></option><option value="inactive"><?php echo addslashes(__('Inactive'));?></option></select></td>' +
            '<td class="ard-bank-action-cell"><button type="button" class="btn btn-danger btn-xs js-remove-bank-row"><span class="fa fa-trash"></span></button></td>' +
        '</tr>';
    }

    function visibleBankRows()
    {
        return $bankTable.find('.js-bank-account-row:visible');
    }

    function syncPrimaryBankRows()
    {
        var $visibleRows = visibleBankRows();
        if($visibleRows.length > 0 && !$visibleRows.find('.js-bank-primary-radio:checked').length)
        {
            $visibleRows.first().find('.js-bank-primary-radio').prop('checked', true);
        }
        $bankTable.find('.js-bank-account-row').each(function(){
            var checked = $(this).is(':visible') && $(this).find('.js-bank-primary-radio').is(':checked');
            $(this).find('.js-bank-primary-value').val(checked ? '1' : '0');
        });
    }

    $('#AddPaymentBankAccountRow').on('click', function(e){
        e.preventDefault();
        $bankTable.find('tbody').append(bankRowHtml(nextBankIndex++));
        syncPrimaryBankRows();
    });

    $(document).on('click', '.js-remove-bank-row', function(e){
        e.preventDefault();
        var $row = $(this).closest('.js-bank-account-row');
        var existingId = parseInt($row.find('input[name$="[id]"]').val(), 10) || 0;
        if(existingId > 0)
        {
            $row.find('.js-bank-delete').val('1');
            $row.hide();
        }
        else
        {
            $row.remove();
        }
        syncPrimaryBankRows();
    });

    $(document).on('change', '.js-bank-primary-radio', syncPrimaryBankRows);
    $('.ard-settings-form').on('submit', syncPrimaryBankRows);
    syncPrimaryBankRows();
});
</script>
<?php }?>
<script type="text/javascript">
jQuery(function($){
    var $fiscalLockDate = $('#ArdsettingFiscalLockDate');
    if($fiscalLockDate.length && typeof $.fn.datetimepicker === 'function')
    {
        $fiscalLockDate.datetimepicker({locale:'<?php echo $configLanguage;?>',format:'<?php echo $dpFormat;?>'});
    }
});
</script>
