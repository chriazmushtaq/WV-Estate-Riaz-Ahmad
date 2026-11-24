<?php
$isAdr = ($userType=='ADR');
$isEdit = !empty($this->request->data['Ardhpayment']['id']);
$employeeScope = isset($employeeScope) ? (string)$employeeScope : 'all';
$employeeScopeLabel = isset($employeeScopeLabel) ? (string)$employeeScopeLabel : __('All Employees');
$employeeScopeQuery = (isset($employeeScopeQuery) && is_array($employeeScopeQuery)) ? $employeeScopeQuery : array();
$isLabourScope = ($employeeScope === 'labour');
$pageTitle = $isLabourScope ? __('Labour Payroll Payments') : __('HR Payroll Payments');
?>
<div class="page-title"><div class="title-env"><h1 class="title"><?php echo h($pageTitle);?></h1></div></div>
<div class="panel">
    <div class="panel-heading">
        <?php echo $this->Html->link('<span class="fa fa-arrow-left"></span> '.__('HR Payroll'),array('controller'=>'Ardaccounts','action'=>'hrpayroll',$currentProjectId,'?' => $employeeScopeQuery),array('escape'=>false,'class'=>'btn btn-default'));?>
        <?php if($isLabourScope){ ?>
            <?php echo $this->Html->link('<span class="fa fa-users"></span> '.__('All Payments'),array('controller'=>'Ardaccounts','action'=>'hrpayments',$currentProjectId),array('escape'=>false,'class'=>'btn btn-default'));?>
        <?php } else { ?>
            <?php echo $this->Html->link('<span class="fa fa-users"></span> '.__('Labour Payments'),array('controller'=>'Ardaccounts','action'=>'hrpayments',$currentProjectId,'?' => array('employee_scope' => 'labour')),array('escape'=>false,'class'=>'btn btn-warning'));?>
        <?php } ?>
    </div>
    <div class="panel-body">
        <?php echo $this->Session->flash();?>
        <div class="alert alert-info" style="margin-bottom:15px;">
            <strong><?php echo __('Current Scope');?>:</strong> <?php echo h($employeeScopeLabel);?>
        </div>
        <?php echo $this->Form->create('Ardhpayment',array('url'=>array('controller'=>'Ardaccounts','action'=>'hrpayments',$currentProjectId),'class'=>'form-horizontal'));?>
        <?php echo $this->Form->hidden('id');?>
        <input type="hidden" name="data[Ardhpayment][employee_scope]" value="<?php echo h($employeeScope);?>">
        <?php if(!$isAdr){ echo $this->Form->hidden('project_id',array('value'=>$agencyId)); }?>
        <div class="row">
            <?php if($isAdr){?><div class="col-sm-3"><?php echo $this->Form->input('project_id',array('label'=>__('Agency'),'class'=>'form-control','options'=>$projectName,'empty'=>__('Please Select')));?></div><?php }?>
            <div class="col-sm-3"><?php echo $this->Form->input('payroll_id',array('label'=>__('Payroll Month'),'class'=>'form-control','options'=>$payrollList,'empty'=>__('Please Select')));?></div>
            <div class="col-sm-3"><?php echo $this->Form->input('employee_id',array('label'=>__('Employee'),'class'=>'form-control','options'=>$employeeList,'empty'=>__('Please Select')));?></div>
            <div class="col-sm-3"><?php echo $this->Form->input('debit_ledger_id',array('label'=>__('Payment Ledger'),'class'=>'form-control','options'=>$ledgerList,'empty'=>__('Please Select')));?></div>
        </div>
        <div class="row">
            <div class="col-sm-2"><?php echo $this->Form->input('amount',array('label'=>__('Amount'),'class'=>'form-control'));?></div>
            <div class="col-sm-2"><?php echo $this->Form->input('payment_date',array('label'=>__('Payment Date'),'class'=>'form-control','type'=>'text'));?></div>
            <div class="col-sm-2"><?php echo $this->Form->input('payment_method',array('label'=>__('Method'),'class'=>'form-control'));?></div>
            <div class="col-sm-2"><?php echo $this->Form->input('reference_no',array('label'=>__('Reference'),'class'=>'form-control'));?></div>
            <div class="col-sm-4"><?php echo $this->Form->input('note',array('label'=>__('Note'),'class'=>'form-control'));?></div>
        </div>
        <br>
        <button type="submit" class="btn btn-success"><span class="fa fa-save"></span> <?php echo ($isEdit?__('Update'):__('Save'));?></button>
        <?php if($isEdit){echo $this->Html->link('<span class="fa fa-close"></span> '.__('Cancel Edit'),array('controller'=>'Ardaccounts','action'=>'hrpayments',$currentProjectId,'?' => $employeeScopeQuery),array('escape'=>false,'class'=>'btn btn-danger'));}?>
        <?php echo $this->Form->end();?>

        <hr>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <tr>
                    <th><?php echo __('Payroll');?></th>
                    <th><?php echo __('Employee');?></th>
                    <th><?php echo __('Amount');?></th>
                    <th><?php echo __('Date');?></th>
                    <th><?php echo __('Method');?></th>
                    <th><?php echo __('Reference');?></th>
                    <th><?php echo __('Action');?></th>
                </tr>
                <?php foreach($rows as $row): $rid=(int)$row['Ardhpayment']['id']; ?>
                <tr>
                    <td><?php echo isset($payrollList[$row['Ardhpayment']['payroll_id']]) ? h($payrollList[$row['Ardhpayment']['payroll_id']]) : '-';?></td>
                    <td><?php echo isset($employeeList[$row['Ardhpayment']['employee_id']]) ? h($employeeList[$row['Ardhpayment']['employee_id']]) : '-';?></td>
                    <td><?php echo $currency.$this->Number->format($row['Ardhpayment']['amount']);?></td>
                    <td><?php echo (!empty($row['Ardhpayment']['payment_date']) && $row['Ardhpayment']['payment_date'] !== '0000-00-00') ? date('d-M-Y', strtotime((string)$row['Ardhpayment']['payment_date'])) : '-';?></td>
                    <td><?php echo h($row['Ardhpayment']['payment_method']);?></td>
                    <td><?php echo h($row['Ardhpayment']['reference_no']);?></td>
                    <td>
                        <?php echo $this->Html->link('<span class="fa fa-edit"></span>',array('controller'=>'Ardaccounts','action'=>'hrpayments',$currentProjectId,'edit:'.$rid,'?' => $employeeScopeQuery),array('escape'=>false,'class'=>'btn btn-xs btn-warning'));?>
                        <?php echo $this->Html->link('<span class="fa fa-trash"></span>',array('controller'=>'Ardaccounts','action'=>'deletehrpayment',$rid,$currentProjectId,'?' => $employeeScopeQuery),array('escape'=>false,'class'=>'btn btn-xs btn-danger','onclick'=>"return confirm('".__('Are you sure?')."');"));?>
                    </td>
                </tr>
                <?php endforeach;?>
            </table>
        </div>
        <?php echo $this->element('pagination');?>
    </div>
</div>
