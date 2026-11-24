<div class="page-title">
    <div class="title-env">
        <h1 class="title"><span class="fa fa-user-plus"></span> <?php echo __('Add New Billing Consumer'); ?></h1>
        <p class="description"><?php echo __('Register a new resident consumer profile and link to existing agency clients.'); ?></p>
    </div>
    <div class="pull-right" style="margin-top:10px;">
        <?php echo $this->Html->link('&larr; ' . __('Back to Directory'), array('action' => 'index'), array('escape' => false, 'class' => 'btn btn-default')); ?>
    </div>
</div>

<div class="panel">
    <div class="panel-body">
        <?php echo $this->Session->flash(); ?>

        <?php echo $this->Form->create('BillingClient', array('class' => 'form-horizontal', 'style' => 'max-width:800px;')); ?>
            <div class="form-group">
                <label class="col-sm-4 control-label"><?php echo __('Consumer Full Name'); ?> <span class="text-danger">*</span></label>
                <div class="col-sm-8">
                    <?php echo $this->Form->input('name', array('class' => 'form-control', 'placeholder' => 'e.g. Irfan Rasool S/O Ghulam Rasool', 'label' => false, 'div' => false, 'required' => true)); ?>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-4 control-label"><?php echo __('Link to App Client'); ?></label>
                <div class="col-sm-8">
                    <?php echo $this->Form->input('app_client_id', array('type' => 'select', 'options' => $appClients, 'empty' => __('-- Link with Existing White Villas Client (Optional) --'), 'class' => 'form-control', 'label' => false, 'div' => false)); ?>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-4 control-label"><?php echo __('Mobile Phone'); ?> <span class="text-danger">*</span></label>
                <div class="col-sm-8">
                    <?php echo $this->Form->input('phone', array('class' => 'form-control', 'placeholder' => '03001234567', 'label' => false, 'div' => false, 'required' => true)); ?>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-4 control-label"><?php echo __('WhatsApp Number'); ?></label>
                <div class="col-sm-8">
                    <?php echo $this->Form->input('whatsapp', array('class' => 'form-control', 'placeholder' => '03001234567', 'label' => false, 'div' => false)); ?>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-4 control-label"><?php echo __('Email Address'); ?></label>
                <div class="col-sm-8">
                    <?php echo $this->Form->input('email', array('class' => 'form-control', 'label' => false, 'div' => false)); ?>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-4 control-label"><?php echo __('CNIC / National ID'); ?></label>
                <div class="col-sm-8">
                    <?php echo $this->Form->input('cnic', array('class' => 'form-control', 'placeholder' => '35201-1234567-1', 'label' => false, 'div' => false)); ?>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-4 control-label"><?php echo __('Address'); ?></label>
                <div class="col-sm-8">
                    <?php echo $this->Form->input('address', array('class' => 'form-control', 'label' => false, 'div' => false)); ?>
                </div>
            </div>

            <div class="form-group" style="margin-top:24px;">
                <div class="col-sm-offset-4 col-sm-8">
                    <button type="submit" class="btn btn-success" style="font-weight:800;padding:10px 28px;">
                        <span class="fa fa-save"></span> <?php echo __('Save Consumer Profile'); ?>
                    </button>
                    <a href="<?php echo $this->Html->url(array('action' => 'index')); ?>" class="btn btn-default"><?php echo __('Cancel'); ?></a>
                </div>
            </div>
        <?php echo $this->Form->end(); ?>

    </div>
</div>
