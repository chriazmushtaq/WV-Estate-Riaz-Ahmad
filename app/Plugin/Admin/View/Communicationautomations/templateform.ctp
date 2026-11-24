<div class="panel">
    <div class="panel-heading"><?php echo !empty($isEdit) ? __('Edit Communication Template') : __('Add Communication Template'); ?></div>
    <div class="panel-body wv-comm-wrap">
        <?php echo $this->Session->flash(); ?>
        <?php echo $this->element('communication_automation_nav'); ?>
        <div class="wv-comm-card">
            <?php echo $this->Form->create('CommunicationTemplate', array('class' => 'form-horizontal wv-no-auto-layout')); ?>
            <div class="wv-comm-form-grid">
                <div><?php echo $this->Form->input('name', array('label' => __('Template Name'), 'class' => 'form-control')); ?></div>
                <div><?php echo $this->Form->input('code', array('label' => __('Code'), 'class' => 'form-control', 'placeholder' => 'AUTO-CUSTOM-SMS')); ?></div>
                <div><?php echo $this->Form->input('channel', array('label' => __('Channel'), 'type' => 'select', 'options' => $communicationChannelOptions, 'class' => 'form-control')); ?></div>
                <div><?php echo $this->Form->input('event_key', array('label' => __('Event'), 'type' => 'select', 'options' => $communicationEventOptions, 'class' => 'form-control')); ?></div>
                <div><?php echo $this->Form->input('project_id', array('label' => __('Agency'), 'type' => 'select', 'options' => $communicationProjectOptions, 'empty' => __('Global'), 'class' => 'form-control')); ?></div>
                <div><?php echo $this->Form->input('status', array('label' => __('Status'), 'type' => 'select', 'options' => array('Published' => __('Published'), 'Unpublished' => __('Unpublished')), 'class' => 'form-control')); ?></div>
                <div class="full"><?php echo $this->Form->input('subject', array('label' => __('Email / Letter Subject'), 'class' => 'form-control')); ?></div>
                <div class="full"><?php echo $this->Form->input('body', array('label' => __('Template Body'), 'type' => 'textarea', 'rows' => 16, 'class' => 'form-control')); ?></div>
            </div>
            <p class="help-block"><?php echo __('Use placeholders like {client_name}, {deal_invoice_no}, {property_name}, {amount}, {due_date}, {agency_name}, {attachment_url}, {letter_url}.'); ?></p>
            <button type="submit" class="btn btn-success"><span class="fa fa-save"></span> <?php echo __('Save Template'); ?></button>
            <?php echo $this->Html->link(__('Cancel'), array('action' => 'templates'), array('class' => 'btn btn-default')); ?>
            <?php echo $this->Form->end(); ?>
        </div>
    </div>
</div>
