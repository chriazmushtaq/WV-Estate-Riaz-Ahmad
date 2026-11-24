<div class="page-title">
    <div class="title-env">
        <h1 class="title"><span class="fa fa-list-alt"></span> <?php echo __('Edit Maintenance Charge Head'); ?></h1>
        <p class="description"><?php echo __('Update fee amount and applicability rules.'); ?></p>
    </div>
    <div class="pull-right" style="margin-top:10px;">
        <?php echo $this->Html->link('&larr; ' . __('Back to Charges'), array('action' => 'index'), array('escape' => false, 'class' => 'btn btn-default')); ?>
    </div>
</div>

<div class="panel">
    <div class="panel-body">
        <?php echo $this->Session->flash(); ?>

        <?php echo $this->Form->create('BillingExtraCharge', array('class' => 'form-horizontal', 'style' => 'max-width:800px;')); ?>
            <?php echo $this->Form->input('id', array('type' => 'hidden')); ?>

            <div class="form-group">
                <label class="col-sm-4 control-label"><?php echo __('Housing Society / Agency'); ?> <span class="text-danger">*</span></label>
                <div class="col-sm-8">
                    <?php echo $this->Form->input('society_id', array('type' => 'select', 'options' => $societies, 'class' => 'form-control', 'label' => false, 'div' => false, 'required' => true)); ?>
                    <small class="help-block" style="font-size:11px;color:#64748b;"><?php echo __('Select the housing society or agency this charge head belongs to.'); ?></small>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-4 control-label"><?php echo __('Charge Title'); ?> <span class="text-danger">*</span></label>
                <div class="col-sm-8">
                    <?php echo $this->Form->input('title', array('class' => 'form-control', 'label' => false, 'div' => false, 'required' => true)); ?>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-4 control-label"><?php echo __('Amount (PKR)'); ?> <span class="text-danger">*</span></label>
                <div class="col-sm-8">
                    <?php echo $this->Form->input('amount', array('type' => 'number', 'step' => '0.01', 'class' => 'form-control', 'label' => false, 'div' => false, 'required' => true)); ?>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-4 control-label"><?php echo __('Charge Type'); ?></label>
                <div class="col-sm-8">
                    <?php echo $this->Form->input('charge_type', array('type' => 'select', 'options' => array('recurring' => 'Monthly Recurring', 'one_time' => 'One-Time Charge'), 'class' => 'form-control', 'label' => false, 'div' => false)); ?>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-4 control-label"><?php echo __('Apply To'); ?></label>
                <div class="col-sm-8">
                    <?php echo $this->Form->input('apply_to', array('type' => 'select', 'options' => array('all' => 'All Units', 'residential' => 'Residential Only', 'commercial' => 'Commercial Only'), 'class' => 'form-control', 'label' => false, 'div' => false)); ?>
                </div>
            </div>

            <div class="form-group" style="margin-top:24px;">
                <div class="col-sm-offset-4 col-sm-8">
                    <button type="submit" class="btn btn-success" style="font-weight:800;padding:10px 28px;">
                        <span class="fa fa-save"></span> <?php echo __('Update Charge Head'); ?>
                    </button>
                    <a href="<?php echo $this->Html->url(array('action' => 'index')); ?>" class="btn btn-default"><?php echo __('Cancel'); ?></a>
                </div>
            </div>
        <?php echo $this->Form->end(); ?>

    </div>
</div>
