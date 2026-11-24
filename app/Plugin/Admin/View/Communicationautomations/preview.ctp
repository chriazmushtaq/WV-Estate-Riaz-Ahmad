<div class="panel">
    <div class="panel-heading"><?php echo __('Automation Preview'); ?></div>
    <div class="panel-body wv-comm-wrap">
        <?php echo $this->Session->flash(); ?>
        <?php echo $this->element('communication_automation_nav'); ?>

        <div class="wv-comm-card">
            <?php echo $this->Form->create('PreviewCommunication', array('class' => 'form-horizontal wv-no-auto-layout')); ?>
            <div class="wv-comm-form-grid">
                <div><?php echo $this->Form->input('audience', array('label' => __('Audience'), 'type' => 'select', 'options' => $audienceOptions, 'class' => 'form-control')); ?></div>
                <div><?php echo $this->Form->input('project_id', array('label' => __('Agency'), 'type' => 'select', 'options' => $communicationProjectOptions, 'empty' => __('All / Global'), 'class' => 'form-control')); ?></div>
                <div><?php echo $this->Form->input('days_before', array('label' => __('Due Soon Days'), 'type' => 'number', 'class' => 'form-control')); ?></div>
                <div><?php echo $this->Form->input('attachment_url', array('label' => __('PDF / Attachment Link'), 'class' => 'form-control')); ?></div>
                <div class="full"><?php echo $this->Form->input('channels', array('label' => __('Channels'), 'type' => 'select', 'multiple' => 'checkbox', 'options' => $communicationChannelOptions)); ?></div>
                <div><?php echo $this->Form->input('sms_template_id', array('label' => __('SMS Template'), 'type' => 'select', 'options' => $templateOptions['sms'], 'empty' => __('None'), 'class' => 'form-control')); ?></div>
                <div><?php echo $this->Form->input('whatsapp_template_id', array('label' => __('WhatsApp Template'), 'type' => 'select', 'options' => $templateOptions['whatsapp'], 'empty' => __('None'), 'class' => 'form-control')); ?></div>
                <div><?php echo $this->Form->input('email_template_id', array('label' => __('Email Template'), 'type' => 'select', 'options' => $templateOptions['email'], 'empty' => __('None'), 'class' => 'form-control')); ?></div>
                <div><?php echo $this->Form->input('letter_template_id', array('label' => __('Letter Template'), 'type' => 'select', 'options' => $templateOptions['letter'], 'empty' => __('None'), 'class' => 'form-control')); ?></div>
                <div class="full"><?php echo $this->Form->input('custom_recipients', array('label' => __('Custom Recipients'), 'type' => 'textarea', 'rows' => 4, 'class' => 'form-control', 'placeholder' => "Name, Mobile, Email")); ?></div>
            </div>
            <button type="submit" class="btn btn-primary"><span class="fa fa-eye"></span> <?php echo __('Preview'); ?></button>
            <?php echo $this->Html->link(__('Bulk Send'), array('action' => 'bulk'), array('class' => 'btn btn-default')); ?>
            <?php echo $this->Form->end(); ?>
        </div>

        <div class="row">
            <div class="col-md-8">
                <?php if(!empty($previews)){ foreach($previews as $preview){ ?>
                    <div class="wv-comm-card">
                        <h4>
                            <?php echo h(ucfirst($preview['channel'])); ?>
                            <span class="wv-comm-badge pending"><?php echo h($preview['template']['name']); ?></span>
                        </h4>
                        <?php if(trim((string)$preview['subject']) !== ''){ ?>
                            <p><strong><?php echo __('Subject'); ?>:</strong> <?php echo h($preview['subject']); ?></p>
                        <?php } ?>
                        <?php if($preview['channel'] === 'email' || $preview['channel'] === 'letter'){ ?>
                            <div class="wv-comm-preview-html"><?php echo $preview['body']; ?></div>
                        <?php }else{ ?>
                            <pre class="wv-comm-preview-text"><?php echo h($preview['body']); ?></pre>
                        <?php } ?>
                    </div>
                <?php }}else{ ?>
                    <div class="wv-comm-card text-center"><?php echo __('Select templates and click Preview.'); ?></div>
                <?php } ?>
            </div>
            <div class="col-md-4">
                <div class="wv-comm-card">
                    <h4><?php echo __('Source'); ?></h4>
                    <p class="wv-comm-sub"><?php echo h($sourceSummary); ?></p>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped wv-comm-table">
                            <tbody>
                            <?php foreach((array)$tokens as $key => $value){ ?>
                                <tr>
                                    <th><?php echo h('{'.$key.'}'); ?></th>
                                    <td><?php echo h($value); ?></td>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
