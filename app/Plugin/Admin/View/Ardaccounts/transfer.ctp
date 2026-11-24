<?php $isAdr = ($userType=='ADR'); ?>
<div class="page-title"><div class="title-env"><h1 class="title"><?php echo __('Journal Voucher');?></h1></div></div>
<div class="panel"><?php echo $this->Session->flash();?>
    <div class="panel-body">
        <?php echo $this->Form->create('Ardtransaction',array('class'=>'form-horizontal'));?>
        <?php if(!$isAdr){echo $this->Form->hidden('project_id',array('value'=>$agencyId));}?>
        <?php if($isAdr){?><div class="form-group"><label class="col-sm-2 control-label"><?php echo __('Agency');?></label><div class="col-sm-4"><?php echo $this->Form->select('project_id',$projectName,array('empty'=>__('Please Select'),'class'=>'form-control','label'=>false,'div'=>false));?></div></div><?php }?>
        <div class="form-group"><label class="col-sm-2 control-label"><?php echo __('Debit Ledger (From)');?></label><div class="col-sm-4"><?php echo $this->Form->select('from_ledger_id',$bankLedgers,array('empty'=>__('Select'),'class'=>'form-control','label'=>false,'div'=>false));?></div><label class="col-sm-2 control-label"><?php echo __('Credit Ledger (To)');?></label><div class="col-sm-4"><?php echo $this->Form->select('to_ledger_id',$bankLedgers,array('empty'=>__('Select'),'class'=>'form-control','label'=>false,'div'=>false));?></div></div>
        <div class="form-group"><div class="col-sm-offset-2 col-sm-10"><p class="help-block" style="margin-top:0;"><?php echo __('This Journal Voucher transfers amount from one Asset/Bank ledger to another: From ledger posts in Debit side, To ledger posts in Credit side with same reference.');?></p></div></div>
        <div class="form-group"><label class="col-sm-2 control-label"><?php echo __('Amount');?></label><div class="col-sm-4"><?php echo $this->Form->input('amount',array('label'=>false,'div'=>false,'class'=>'form-control'));?></div><label class="col-sm-2 control-label"><?php echo __('Date');?></label><div class="col-sm-4"><?php echo $this->Form->input('entry_date',array('type'=>'text','default'=>$date,'label'=>false,'div'=>false,'class'=>'form-control'));?></div></div>
        <div class="form-group"><label class="col-sm-2 control-label"><?php echo __('Ref No');?></label><div class="col-sm-4"><?php echo $this->Form->input('ref_no',array('label'=>false,'div'=>false,'class'=>'form-control','placeholder'=>'Auto if empty (JV-...)'));?></div><label class="col-sm-2 control-label"><?php echo __('Description');?></label><div class="col-sm-4"><?php echo $this->Form->input('description',array('label'=>false,'div'=>false,'class'=>'form-control'));?></div></div>
        <div class="form-group"><div class="col-sm-offset-2 col-sm-10"><button type="submit" class="btn btn-success"><span class="fa fa-save"></span> <?php echo __('Post Journal Voucher');?></button></div></div>
        <?php echo $this->Form->end();?>

        <h4><?php echo __('Recent Journal Vouchers');?></h4>
        <table class="table table-striped table-bordered">
            <tr><th><?php echo __('Date');?></th><th><?php echo __('Debit Ledger');?></th><th><?php echo __('Amount');?></th><th><?php echo __('Reference');?></th><th><?php echo __('Description');?></th></tr>
            <?php foreach($recentTransfers as $row):?>
            <tr>
                <td><?php echo (!empty($row['Ardtransaction']['entry_date']) && $row['Ardtransaction']['entry_date'] !== '0000-00-00') ? date('d-M-Y', strtotime((string)$row['Ardtransaction']['entry_date'])) : '-';?></td>
                <td><?php echo h($row['Ardledger']['name']);?></td>
                <td><?php echo $currency.$this->Number->format($row['Ardtransaction']['amount']);?></td>
                <td><?php echo h($row['Ardtransaction']['ref_no']);?></td>
                <td><?php echo h($row['Ardtransaction']['description']);?></td>
            </tr>
            <?php endforeach;?>
        </table>
    </div>
</div>
