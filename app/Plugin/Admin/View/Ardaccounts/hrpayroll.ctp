<?php
$employeeScope = isset($employeeScope) ? (string)$employeeScope : 'all';
$employeeScopeLabel = isset($employeeScopeLabel) ? (string)$employeeScopeLabel : __('All Employees');
$employeeScopeQuery = (isset($employeeScopeQuery) && is_array($employeeScopeQuery)) ? $employeeScopeQuery : array();
$isLabourScope = ($employeeScope === 'labour');
$pageTitle = $isLabourScope ? __('Labour Control') : __('HR Payroll');
$sheetQuery = array_merge(array('sheet_month' => date('Y-m')), $employeeScopeQuery);
?>
<div class="page-title"><div class="title-env"><h1 class="title"><?php echo h($pageTitle);?></h1></div></div>
<div class="panel">
    <div class="panel-heading">
        <div class="btn-group">
            <?php
            echo $this->Html->link('<span class="fa fa-id-badge"></span> '.__('Employees'),array('controller'=>'Ardaccounts','action'=>'hremployees',$currentProjectId,'?' => $employeeScopeQuery),array('escape'=>false,'class'=>'btn btn-primary'));
            echo $this->Html->link('<span class="fa fa-calendar"></span> '.__('Payroll Entries'),array('controller'=>'Ardaccounts','action'=>'hrpayrollentries',$currentProjectId,'?' => $employeeScopeQuery),array('escape'=>false,'class'=>'btn btn-success'));
            echo $this->Html->link('<span class="fa fa-table"></span> '.__('Attendance Sheet'),array('controller'=>'Ardaccounts','action'=>'hrpayrollentries',$currentProjectId,'?' => $sheetQuery),array('escape'=>false,'class'=>'btn btn-warning'));
            echo $this->Html->link('<span class="fa fa-money"></span> '.__('Payroll Payments'),array('controller'=>'Ardaccounts','action'=>'hrpayments',$currentProjectId,'?' => $employeeScopeQuery),array('escape'=>false,'class'=>'btn btn-info'));
            echo $this->Html->link('<span class="fa fa-bank"></span> '.__('Bank Advice Sheet'),array('controller'=>'Ardaccounts','action'=>'bankadvicesheet',$currentProjectId,'?' => array('month' => date('Y-m'))),array('escape'=>false,'class'=>'btn btn-default','target'=>'_blank'));
            echo $this->Html->link('<span class="fa fa-users"></span> '.__('Site Labour Muster Roll'),array('controller'=>'Ardaccounts','action'=>'labourcontrol',$currentProjectId),array('escape'=>false,'class'=>'btn btn-warning'));
            echo $this->Html->link('<span class="fa fa-percent"></span> '.__('Commission Settings'),array('controller'=>'Ardaccounts','action'=>'settings',$currentProjectId,'?'=>array('tab'=>'commission')),array('escape'=>false,'class'=>'btn btn-default'));
            echo $this->Html->link('<span class="fa fa-cog"></span> '.__('Payroll Settings'),array('controller'=>'Ardaccounts','action'=>'settings',$currentProjectId,'?'=>array('tab'=>'vouchers')),array('escape'=>false,'class'=>'btn btn-default'));
            ?>
        </div>
    </div>
    <div class="panel-body">
        <?php echo $this->Session->flash();?>
        <div class="alert alert-info" style="margin-bottom:15px;">
            <strong><?php echo __('Current Scope');?>:</strong> <?php echo h($employeeScopeLabel);?>
        </div>
        <div class="row">
            <div class="col-sm-3"><div class="well text-center"><h5><?php echo __('Employees');?></h5><h3><?php echo (int)$summary['employees'];?></h3></div></div>
            <div class="col-sm-3"><div class="well text-center"><h5><?php echo __('Payroll Entries');?></h5><h3><?php echo (int)$summary['payroll_entries'];?></h3></div></div>
            <div class="col-sm-3"><div class="well text-center"><h5><?php echo __('Payroll Payments');?></h5><h3><?php echo (int)$summary['payments'];?></h3></div></div>
            <div class="col-sm-3"><div class="well text-center"><h5><?php echo __('Payroll Due');?></h5><h3><?php echo $currency.$this->Number->format($summary['due']);?></h3></div></div>
        </div>

        <h4><?php echo __('Recent Employees');?></h4>
        <table class="table table-striped table-bordered">
            <tr><th><?php echo __('Name');?></th><th><?php echo __('Code');?></th><th><?php echo __('Department');?></th><th><?php echo __('Designation');?></th><th><?php echo __('Salary');?></th><th><?php echo __('Status');?></th></tr>
            <?php foreach($recentEmployees as $row):?>
            <tr>
                <td><?php echo h($row['Ardhremployee']['full_name']);?></td>
                <td><?php echo h($row['Ardhremployee']['employee_code']);?></td>
                <td><?php echo h($row['Ardhremployee']['department']);?></td>
                <td><?php echo h($row['Ardhremployee']['designation']);?></td>
                <td><?php echo $currency.$this->Number->format($row['Ardhremployee']['base_salary']);?></td>
                <td><?php echo h(ucfirst($row['Ardhremployee']['status']));?></td>
            </tr>
            <?php endforeach;?>
        </table>
    </div>
</div>
