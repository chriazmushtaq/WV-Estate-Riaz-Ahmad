<div class="page-title">
    <div class="title-env">
        <h1 class="title"><span class="fa fa-plug"></span> <?php echo __('Edit Utility Meter'); ?></h1>
        <p class="description"><?php echo __('Update meter configuration, multiplier, assigned society, and property unit.'); ?></p>
    </div>
    <div class="pull-right" style="margin-top:10px;">
        <?php echo $this->Html->link('&larr; ' . __('Back to Meters'), array('action' => 'index'), array('escape' => false, 'class' => 'btn btn-default')); ?>
    </div>
</div>

<div class="panel">
    <div class="panel-body">
        <?php echo $this->Session->flash(); ?>

        <?php echo $this->Form->create('BillingMeter', array('class' => 'form-horizontal', 'style' => 'max-width:850px;')); ?>
            <?php echo $this->Form->input('id', array('type' => 'hidden')); ?>

            <div class="form-group">
                <label class="col-sm-4 control-label"><?php echo __('Society / Project'); ?> <span class="text-danger">*</span></label>
                <div class="col-sm-8">
                    <?php echo $this->Form->input('society_id', array('type' => 'select', 'options' => $societies, 'class' => 'form-control', 'label' => false, 'div' => false, 'required' => true)); ?>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-4 control-label"><?php echo __('Assigned Property Unit'); ?> <span class="text-danger">*</span></label>
                <div class="col-sm-8">
                    <?php echo $this->Form->input('billing_property_id', array('type' => 'select', 'options' => $properties, 'empty' => __('-- Select Property Unit --'), 'class' => 'form-control', 'label' => false, 'div' => false, 'required' => true)); ?>
                    <span class="help-block" style="font-size:11px;"><?php echo __('All units from White Villas & White Villas Awami Block.'); ?></span>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-4 control-label"><?php echo __('Meter Number / Code'); ?> <span class="text-danger">*</span></label>
                <div class="col-sm-8">
                    <?php echo $this->Form->input('meter_number', array('class' => 'form-control', 'label' => false, 'div' => false, 'required' => true)); ?>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-4 control-label"><?php echo __('Utility Type'); ?></label>
                <div class="col-sm-8">
                    <?php echo $this->Form->input('utility_type', array('type' => 'select', 'options' => array('Electricity' => 'Electricity', 'Water' => 'Water', 'Gas' => 'Gas', 'Maintenance' => 'Maintenance'), 'class' => 'form-control', 'label' => false, 'div' => false)); ?>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-4 control-label"><?php echo __('Meter Multiplier'); ?></label>
                <div class="col-sm-8">
                    <?php echo $this->Form->input('multiplier', array('type' => 'number', 'step' => '0.01', 'class' => 'form-control', 'label' => false, 'div' => false)); ?>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-4 control-label"><?php echo __('Initial Reading'); ?></label>
                <div class="col-sm-8">
                    <?php echo $this->Form->input('initial_reading', array('type' => 'number', 'step' => '0.01', 'class' => 'form-control', 'label' => false, 'div' => false)); ?>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-4 control-label"><?php echo __('Current Reading'); ?></label>
                <div class="col-sm-8">
                    <?php echo $this->Form->input('current_reading', array('type' => 'number', 'step' => '0.01', 'class' => 'form-control', 'label' => false, 'div' => false)); ?>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-4 control-label"><?php echo __('Status'); ?></label>
                <div class="col-sm-8">
                    <?php echo $this->Form->input('status', array('type' => 'select', 'options' => array('active' => 'Active', 'inactive' => 'Inactive', 'faulty' => 'Faulty'), 'class' => 'form-control', 'label' => false, 'div' => false)); ?>
                </div>
            </div>

            <div class="form-group" style="margin-top:24px;">
                <div class="col-sm-offset-4 col-sm-8">
                    <button type="submit" class="btn btn-success" style="font-weight:800;padding:10px 28px;">
                        <span class="fa fa-save"></span> <?php echo __('Update Meter'); ?>
                    </button>
                    <a href="<?php echo $this->Html->url(array('action' => 'index')); ?>" class="btn btn-default"><?php echo __('Cancel'); ?></a>
                </div>
            </div>
        <?php echo $this->Form->end(); ?>

    </div>
</div>
