<?php
$defaultSettings = isset($defaultSettings) ? $defaultSettings : array();
?>
<div class="page-title"><div class="title-env"><h1 class="title"><?php echo __('New Deal Transfer / Merge');?></h1></div></div>
<div class="panel">
    <div class="panel-body">
        <?php echo $this->Session->flash();?>
        <?php echo $this->Form->create('Dealtransfer', array('class'=>'form-horizontal'));?>
        <?php echo $this->element('dealtransfer_form', array(
            'dealOptions'=>$dealOptions,
            'clientOptions'=>$clientOptions,
            'agencyOptions'=>$agencyOptions,
            'typeOptions'=>$typeOptions,
            'feeTypes'=>$feeTypes,
            'defaultSettings'=>$defaultSettings
        ));?>
        <div class="form-group">
            <div class="col-sm-offset-3 col-sm-9">
                <label style="margin-right:12px;"><?php echo $this->Form->checkbox('finalize_now');?> <?php echo __('Finalize Now');?></label>
                <button type="submit" class="btn btn-success"><span class="fa fa-save"></span> <?php echo __('Save Request');?></button>
                <?php echo $this->Html->link(__('Cancel'), array('controller'=>'Dealtransfers','action'=>'index'), array('class'=>'btn btn-default'));?>
            </div>
        </div>
        <?php echo $this->Form->end();?>
    </div>
</div>
