<?php
$this->Js->JqueryEngine->jQueryObject = 'jQuery';
$this->Paginator->options(array('update' => '#resultDiv','evalScripts' => true));
$isEdit = !empty($this->request->data['Ardbudget']['id']);
$isAdr = ($userType=='ADR');
?>
<div id="resultDiv">
<div class="page-title"><div class="title-env"><h1 class="title"><?php echo __('Budget Entries');?></h1></div></div>
<div class="panel"><?php echo $this->Session->flash();?>
    <div class="panel-body">
        <?php echo $this->Form->create('Ardbudget',array('class'=>'form-horizontal'));?>
        <?php if(!$isAdr){echo $this->Form->hidden('project_id',array('value'=>$agencyId));}?>
        <?php if($isEdit){echo $this->Form->hidden('id');}?>
        <?php if($isAdr){?><div class="form-group"><label class="col-sm-2 control-label"><?php echo __('Agency');?></label><div class="col-sm-4"><?php echo $this->Form->select('project_id',$projectName,array('empty'=>__('Please Select'),'class'=>'form-control','label'=>false,'div'=>false));?></div></div><?php }?>
        <div class="form-group"><label class="col-sm-2 control-label"><?php echo __('Ledger');?></label><div class="col-sm-4"><?php echo $this->Form->select('ledger_id',$ledgerList,array('empty'=>__('Select Ledger'),'class'=>'form-control','label'=>false,'div'=>false));?></div><label class="col-sm-2 control-label"><?php echo __('Budget Month');?></label><div class="col-sm-4"><?php echo $this->Form->input('budget_month',array('label'=>false,'div'=>false,'class'=>'form-control','placeholder'=>'YYYY-MM'));?></div></div>
        <div class="form-group"><label class="col-sm-2 control-label"><?php echo __('Budget Amount');?></label><div class="col-sm-4"><?php echo $this->Form->input('budget_amount',array('label'=>false,'div'=>false,'class'=>'form-control'));?></div><label class="col-sm-2 control-label"><?php echo __('Notes');?></label><div class="col-sm-4"><?php echo $this->Form->input('notes',array('label'=>false,'div'=>false,'class'=>'form-control'));?></div></div>
        <div class="form-group"><div class="col-sm-offset-2 col-sm-10"><button type="submit" class="btn btn-success"><span class="fa fa-save"></span> <?php echo ($isEdit?__('Update'):__('Save'));?></button><?php if($isEdit){echo $this->Html->link('<span class="fa fa-close"></span> '.__('Cancel'),array('controller'=>'Ardaccounts','action'=>'budgets',$currentProjectId),array('escape'=>false,'class'=>'btn btn-danger'));}?></div></div>
        <?php echo $this->Form->end();?>
    </div>
    <?php echo $this->element('pagination');?>
    <table class="table table-striped table-bordered"><tr><th>#</th><th><?php echo __('Ledger');?></th><th><?php echo __('Month');?></th><th><?php echo __('Amount');?></th><th><?php echo __('Action');?></th></tr>
        <?php $p=$this->Paginator->params();$s=1*$p['limit']*($p['page']-1)+1; foreach($rows as $row): $rid=(int)$row['Ardbudget']['id'];?>
        <tr><td><?php echo $s++;?></td><td><?php echo h($row['Ardbudget']['ledger_id']);?></td><td><?php echo h($row['Ardbudget']['budget_month']);?></td><td><?php echo $currency.$this->Number->format($row['Ardbudget']['budget_amount']);?></td><td><?php echo $this->Html->link(__('Edit'),array('controller'=>'Ardaccounts','action'=>'budgets',$currentProjectId,'edit:'.$rid),array('class'=>'btn btn-xs btn-warning'));?> <?php echo $this->Html->link(__('Delete'),array('controller'=>'Ardaccounts','action'=>'deletebudget',$rid,$currentProjectId),array('class'=>'btn btn-xs btn-danger','onclick'=>"return confirm('".__('Are you sure?')."');"));?></td></tr>
        <?php endforeach;?>
    </table>
    <?php echo $this->element('pagination');?>
</div>
</div>
