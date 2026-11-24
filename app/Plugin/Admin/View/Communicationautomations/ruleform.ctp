<div class="panel">
    <div class="panel-heading"><?php echo !empty($isEdit) ? __('Edit Automation Rule') : __('Add Automation Rule'); ?></div>
    <div class="panel-body wv-comm-wrap">
        <?php echo $this->Session->flash(); ?>
        <?php echo $this->element('communication_automation_nav'); ?>
        <div class="wv-comm-card">
            <?php echo $this->Form->create('CommunicationRule', array('class' => 'form-horizontal wv-no-auto-layout')); ?>
            <div class="wv-comm-form-grid">
                <div><?php echo $this->Form->input('name', array('label' => __('Rule Name'), 'class' => 'form-control')); ?></div>
                <div><?php echo $this->Form->input('project_id', array('label' => __('Agency'), 'type' => 'select', 'options' => $communicationProjectOptions, 'empty' => __('Global'), 'class' => 'form-control')); ?></div>
                <div><?php echo $this->Form->input('event_key', array('label' => __('Event'), 'type' => 'select', 'options' => $communicationEventOptions, 'class' => 'form-control')); ?></div>
                <div><?php echo $this->Form->input('trigger_timing', array('label' => __('Timing'), 'type' => 'select', 'options' => array('immediate' => __('Immediate'), 'before' => __('Before'), 'after' => __('After'), 'on_date' => __('On Date')), 'class' => 'form-control')); ?></div>
                <div><?php echo $this->Form->input('offset_days', array('label' => __('Offset Days'), 'type' => 'number', 'class' => 'form-control')); ?></div>
                <div><?php echo $this->Form->input('daily_limit', array('label' => __('Daily Limit'), 'type' => 'number', 'class' => 'form-control')); ?></div>
                <div class="full"><?php echo $this->Form->input('channels', array('label' => __('Channels'), 'type' => 'select', 'multiple' => 'checkbox', 'options' => $communicationChannelOptions)); ?></div>
                <div><?php echo $this->Form->input('sms_template_id', array('label' => __('SMS Template'), 'type' => 'select', 'options' => $templateOptions['sms'], 'empty' => __('None'), 'class' => 'form-control')); ?></div>
                <div><?php echo $this->Form->input('whatsapp_template_id', array('label' => __('WhatsApp Template'), 'type' => 'select', 'options' => $templateOptions['whatsapp'], 'empty' => __('None'), 'class' => 'form-control')); ?></div>
                <div><?php echo $this->Form->input('email_template_id', array('label' => __('Email Template'), 'type' => 'select', 'options' => $templateOptions['email'], 'empty' => __('None'), 'class' => 'form-control')); ?></div>
                <div><?php echo $this->Form->input('letter_template_id', array('label' => __('Letter Template'), 'type' => 'select', 'options' => $templateOptions['letter'], 'empty' => __('None'), 'class' => 'form-control')); ?></div>
                <div><?php echo $this->Form->input('status', array('label' => __('Status'), 'type' => 'select', 'options' => array('Active' => __('Active'), 'Paused' => __('Paused')), 'class' => 'form-control')); ?></div>
            </div>
            <button type="submit" class="btn btn-success"><span class="fa fa-save"></span> <?php echo __('Save Rule'); ?></button>
            <?php echo $this->Html->link(__('Cancel'), array('action' => 'rules'), array('class' => 'btn btn-default')); ?>
            <?php echo $this->Form->end(); ?>
        </div>
    </div>
</div>
