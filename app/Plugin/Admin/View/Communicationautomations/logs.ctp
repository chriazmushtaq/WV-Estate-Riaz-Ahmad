<div class="panel">
    <div class="panel-heading"><?php echo __('Communication Logs'); ?></div>
    <div class="panel-body wv-comm-wrap">
        <?php echo $this->Session->flash(); ?>
        <?php echo $this->element('communication_automation_nav'); ?>
        <div class="wv-comm-card">
            <?php echo $this->Form->create(false, array('type' => 'get', 'url' => array('action' => 'logs'), 'class' => 'form-inline')); ?>
            <?php echo $this->Form->select('channel', $communicationChannelOptions, array('empty' => __('All Channels'), 'value' => $filters['channel'], 'class' => 'form-control')); ?>
            <?php echo $this->Form->select('status', array('sent' => __('Sent'), 'prepared' => __('Prepared'), 'failed' => __('Failed')), array('empty' => __('All Status'), 'value' => $filters['status'], 'class' => 'form-control')); ?>
            <?php echo $this->Form->input('keyword', array('label' => false, 'value' => $filters['keyword'], 'class' => 'form-control', 'placeholder' => __('Search'))); ?>
            <button class="btn btn-primary" type="submit"><span class="fa fa-search"></span></button>
            <?php echo $this->Html->link(__('Reset'), array('action' => 'logs'), array('class' => 'btn btn-default')); ?>
            <?php echo $this->Form->end(); ?>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-striped wv-comm-table">
                <thead><tr><th>#</th><th><?php echo __('Date'); ?></th><th><?php echo __('Channel'); ?></th><th><?php echo __('Recipient'); ?></th><th><?php echo __('Subject'); ?></th><th><?php echo __('Source'); ?></th><th><?php echo __('Status'); ?></th></tr></thead>
                <tbody>
                <?php if(!empty($rows)){ foreach($rows as $row){ $l=$row['CommunicationLog']; ?>
                    <tr>
                        <td><?php echo (int)$l['id']; ?></td>
                        <td><?php echo !empty($l['created']) ? h(date('d-M-Y H:i',strtotime($l['created']))) : '-'; ?></td>
                        <td><?php echo h($l['channel']); ?></td>
                        <td><strong><?php echo h($l['recipient_name']); ?></strong><br><span class="text-muted"><?php echo h($l['recipient_mobile']); ?> <?php echo h($l['recipient_email']); ?></span></td>
                        <td><?php echo h($l['subject']); ?><div class="wv-comm-body-preview text-muted"><?php echo h(strip_tags($l['message'])); ?></div></td>
                        <td><?php echo h($l['source_type']); ?> #<?php echo (int)$l['source_id']; ?></td>
                        <td><span class="wv-comm-badge <?php echo h(strtolower($l['status'])); ?>"><?php echo h($l['status']); ?></span><br><small><?php echo h($l['error_text']); ?></small></td>
                    </tr>
                <?php }}else{ ?>
                    <tr><td colspan="7" class="text-center"><?php echo __('No logs found.'); ?></td></tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
        <?php echo $this->element('pagination'); ?>
    </div>
</div>
