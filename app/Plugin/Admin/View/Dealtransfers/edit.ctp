<div class="page-title"><div class="title-env"><h1 class="title"><?php echo __('Edit Deal Transfer / Merge');?></h1></div></div>
<div class="panel">
    <div class="panel-body">
        <?php echo $this->Session->flash();?>
        <?php echo $this->Form->create('Dealtransfer', array('class'=>'form-horizontal'));?>
        <?php echo $this->Form->hidden('transfer_no');?>
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
                <button type="submit" class="btn btn-success"><span class="fa fa-save"></span> <?php echo __('Save Changes');?></button>
                <?php echo $this->Html->link(__('Cancel'), array('controller'=>'Dealtransfers','action'=>'view',(int)$this->request->data['Dealtransfer']['id']), array('class'=>'btn btn-default'));?>
            </div>
        </div>
        <?php echo $this->Form->end();?>
    </div>
</div>
