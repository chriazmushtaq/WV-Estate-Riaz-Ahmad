<div class="panel">
    <div class="panel-heading"><?php echo __('Bulk Send Center'); ?></div>
    <div class="panel-body wv-comm-wrap">
        <?php echo $this->Session->flash(); ?>
        <?php echo $this->element('communication_automation_nav'); ?>
        <div class="wv-comm-card">
            <h4><?php echo __('Create Queue From Audience'); ?></h4>
            <?php echo $this->Form->create('BulkCommunication', array('class' => 'form-horizontal wv-no-auto-layout')); ?>
            <div class="wv-comm-form-grid">
                <div><?php echo $this->Form->input('audience', array('label' => __('Audience'), 'type' => 'select', 'options' => $audienceOptions, 'class' => 'form-control')); ?></div>
                <div><?php echo $this->Form->input('project_id', array('label' => __('Agency'), 'type' => 'select', 'options' => $communicationProjectOptions, 'empty' => __('All / Global'), 'class' => 'form-control')); ?></div>
                <div><?php echo $this->Form->input('days_before', array('label' => __('Due Soon Days'), 'type' => 'number', 'class' => 'form-control')); ?></div>
                <div><?php echo $this->Form->input('limit', array('label' => __('Limit'), 'type' => 'number', 'class' => 'form-control')); ?></div>
                <div class="full"><?php echo $this->Form->input('channels', array('label' => __('Channels'), 'type' => 'select', 'multiple' => 'checkbox', 'options' => $communicationChannelOptions)); ?></div>
                <div><?php echo $this->Form->input('sms_template_id', array('label' => __('SMS Template'), 'type' => 'select', 'options' => $templateOptions['sms'], 'empty' => __('None'), 'class' => 'form-control')); ?></div>
                <div><?php echo $this->Form->input('whatsapp_template_id', array('label' => __('WhatsApp Template'), 'type' => 'select', 'options' => $templateOptions['whatsapp'], 'empty' => __('None'), 'class' => 'form-control')); ?></div>
                <div><?php echo $this->Form->input('email_template_id', array('label' => __('Email Template'), 'type' => 'select', 'options' => $templateOptions['email'], 'empty' => __('None'), 'class' => 'form-control')); ?></div>
                <div><?php echo $this->Form->input('letter_template_id', array('label' => __('Letter Template'), 'type' => 'select', 'options' => $templateOptions['letter'], 'empty' => __('None'), 'class' => 'form-control')); ?></div>
                <div class="full"><?php echo $this->Form->input('attachment_url', array('label' => __('PDF / Attachment Link'), 'class' => 'form-control', 'placeholder' => __('Optional ledger, receipt, or letter PDF link'))); ?></div>
                <div class="full"><?php echo $this->Form->input('custom_recipients', array('label' => __('Custom Recipients'), 'type' => 'textarea', 'rows' => 5, 'class' => 'form-control', 'placeholder' => "Name, Mobile, Email\nName 2, 03000000000, email@example.com")); ?></div>
                <div class="full"><?php echo $this->Form->input('process_now', array('label' => __('Run queue immediately after creating'), 'type' => 'checkbox')); ?></div>
            </div>
            <button type="submit" class="btn btn-success"><span class="fa fa-plus"></span> <?php echo __('Create Queue'); ?></button>
            <?php echo $this->Html->link(__('View Queue'), array('action' => 'queue'), array('class' => 'btn btn-default')); ?>
            <?php echo $this->Form->end(); ?>
        </div>
    </div>
</div>
