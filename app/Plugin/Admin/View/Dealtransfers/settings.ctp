<div class="page-title"><div class="title-env"><h1 class="title"><?php echo __('Deal Transfer Settings');?></h1></div></div>
<div class="panel">
    <div class="panel-body">
        <?php echo $this->Session->flash();?>
        <?php if(!empty($canUseAgencyFilters)){ ?>
            <?php echo $this->Form->create('AgencyFilter', array('type'=>'get','class'=>'form-inline','style'=>'margin-bottom:14px;'));?>
                <?php echo $this->Form->select('project_id', $agencyOptions, array('class'=>'form-control','value'=>$projectId));?>
                <button class="btn btn-primary" type="submit"><span class="fa fa-refresh"></span> <?php echo __('Load Agency');?></button>
            <?php echo $this->Form->end();?>
        <?php } ?>

        <?php echo $this->Form->create('Dealtransfersetting', array('class'=>'form-horizontal'));?>
        <div class="form-group">
            <div class="col-sm-offset-3 col-sm-9">
                <label style="margin-right:15px;"><?php echo $this->Form->checkbox('module_enabled');?> <?php echo __('Module Enabled');?></label>
                <label style="margin-right:15px;"><?php echo $this->Form->checkbox('transfer_enabled');?> <?php echo __('Transfer Enabled');?></label>
                <label style="margin-right:15px;"><?php echo $this->Form->checkbox('merge_enabled');?> <?php echo __('Merge Enabled');?></label>
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-offset-3 col-sm-9">
                <label style="margin-right:15px;"><?php echo $this->Form->checkbox('agency_transfer_enabled');?> <?php echo __('Allow Agency to Agency');?></label>
                <label style="margin-right:15px;"><?php echo $this->Form->checkbox('same_client_merge_only');?> <?php echo __('Merge Same Client Only');?></label>
                <label style="margin-right:15px;"><?php echo $this->Form->checkbox('require_reason');?> <?php echo __('Reason Required');?></label>
                <label style="margin-right:15px;"><?php echo $this->Form->checkbox('create_fee_charge');?> <?php echo __('Create Processing Fee Charge');?></label>
                <label><?php echo $this->Form->checkbox('create_fee_voucher');?> <?php echo __('Create Fee Voucher');?></label>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 control-label"><?php echo __('Fee Voucher Account');?></label>
            <div class="col-sm-9">
                <?php echo $this->Form->select('fee_voucher_ledger_id', $feeLedgerOptions, array('class'=>'form-control'));?>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 control-label"><?php echo __('Default Fee Type');?></label>
            <div class="col-sm-3"><?php echo $this->Form->select('default_fee_type', $feeTypes, array('class'=>'form-control'));?></div>
            <label class="col-sm-3 control-label"><?php echo __('Default Fee %');?></label>
            <div class="col-sm-3"><?php echo $this->Form->text('default_fee_percent', array('class'=>'form-control'));?></div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 control-label"><?php echo __('Default Transfer Fee');?></label>
            <div class="col-sm-3"><?php echo $this->Form->text('default_transfer_fee', array('class'=>'form-control'));?></div>
            <label class="col-sm-3 control-label"><?php echo __('Default Merge Fee');?></label>
            <div class="col-sm-3"><?php echo $this->Form->text('default_merge_fee', array('class'=>'form-control'));?></div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 control-label"><?php echo __('Notes');?></label>
            <div class="col-sm-9"><?php echo $this->Form->textarea('notes', array('class'=>'form-control','rows'=>4));?></div>
        </div>
        <div class="form-group">
            <div class="col-sm-offset-3 col-sm-9">
                <button type="submit" class="btn btn-success"><span class="fa fa-save"></span> <?php echo __('Save Settings');?></button>
                <?php echo $this->Html->link(__('Back'), array('controller'=>'Dealtransfers','action'=>'index'), array('class'=>'btn btn-default'));?>
            </div>
        </div>
        <?php echo $this->Form->end();?>
    </div>
</div>
