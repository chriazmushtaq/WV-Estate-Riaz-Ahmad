<div class="panel">
    <div class="panel-heading"><?php echo __('Communication Timeline'); ?></div>
    <div class="panel-body wv-comm-wrap">
        <?php echo $this->Session->flash(); ?>
        <?php echo $this->element('communication_automation_nav'); ?>
        <div class="wv-comm-card">
            <?php echo $this->Form->create(false, array('type' => 'get', 'url' => array('action' => 'timeline'), 'class' => 'wv-comm-filter')); ?>
            <div class="form-group"><?php echo $this->Form->input('recipient_type', array('label' => false, 'value' => $filters['recipient_type'], 'class' => 'form-control', 'placeholder' => __('Recipient Type'))); ?></div>
            <div class="form-group"><?php echo $this->Form->input('recipient_id', array('label' => false, 'value' => $filters['recipient_id'], 'class' => 'form-control', 'placeholder' => __('Recipient ID'))); ?></div>
            <div class="form-group"><?php echo $this->Form->input('source_type', array('label' => false, 'value' => $filters['source_type'], 'class' => 'form-control', 'placeholder' => __('Source Type'))); ?></div>
            <div class="form-group"><?php echo $this->Form->input('source_id', array('label' => false, 'value' => $filters['source_id'], 'class' => 'form-control', 'placeholder' => __('Source ID'))); ?></div>
            <div class="form-group full"><?php echo $this->Form->input('keyword', array('label' => false, 'value' => $filters['keyword'], 'class' => 'form-control', 'placeholder' => __('Search message'))); ?></div>
            <div class="form-group"><button class="btn btn-primary" type="submit"><span class="fa fa-search"></span> <?php echo __('Search'); ?></button> <?php echo $this->Html->link(__('Reset'), array('action' => 'timeline'), array('class' => 'btn btn-default')); ?></div>
            <?php echo $this->Form->end(); ?>
        </div>
        <?php if(!empty($rows)){ foreach($rows as $row){ $l=$row['CommunicationLog']; ?>
            <div class="wv-comm-card">
                <h4><?php echo h(ucfirst($l['channel'])); ?> - <?php echo h($l['subject']); ?> <span class="wv-comm-badge <?php echo h(strtolower($l['status'])); ?>"><?php echo h($l['status']); ?></span></h4>
                <p class="wv-comm-sub">
                    <?php echo !empty($l['created']) ? h(date('d-M-Y H:i',strtotime($l['created']))) : '-'; ?> |
                    <?php echo h($l['recipient_type']); ?> #<?php echo (int)$l['recipient_id']; ?> |
                    <?php echo h($l['recipient_name']); ?> |
                    <?php echo h($l['source_type']); ?> #<?php echo (int)$l['source_id']; ?>
                </p>
                <div><?php echo nl2br(h(strip_tags($l['message']))); ?></div>
                <?php if(!empty($l['delivery_url'])){ ?><p><a href="<?php echo h($l['delivery_url']); ?>" target="_blank"><?php echo __('Open Prepared Link'); ?></a></p><?php } ?>
            </div>
        <?php }}else{ ?>
            <div class="wv-comm-card text-center"><?php echo __('No timeline entries found.'); ?></div>
        <?php } ?>
    </div>
</div>
