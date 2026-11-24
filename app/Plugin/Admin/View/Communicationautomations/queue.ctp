<div class="panel">
    <div class="panel-heading"><?php echo __('Communication Queue'); ?></div>
    <div class="panel-body wv-comm-wrap">
        <?php echo $this->Session->flash(); ?>
        <?php echo $this->element('communication_automation_nav'); ?>
        <div class="wv-comm-card">
            <div class="row">
                <div class="col-md-8">
                    <?php echo $this->Form->create(false, array('type' => 'get', 'url' => array('action' => 'queue'), 'class' => 'form-inline')); ?>
                    <?php echo $this->Form->select('status', array('all' => __('All'), 'pending' => __('Pending'), 'sent' => __('Sent'), 'prepared' => __('Prepared'), 'failed' => __('Failed')), array('value' => $filters['status'], 'class' => 'form-control')); ?>
                    <?php echo $this->Form->select('channel', $communicationChannelOptions, array('empty' => __('All Channels'), 'value' => $filters['channel'], 'class' => 'form-control')); ?>
                    <?php echo $this->Form->input('keyword', array('label' => false, 'value' => $filters['keyword'], 'class' => 'form-control', 'placeholder' => __('Search'))); ?>
                    <button class="btn btn-primary" type="submit"><span class="fa fa-search"></span></button>
                    <?php echo $this->Form->end(); ?>
                </div>
                <div class="col-md-4 text-right">
                    <?php echo $this->Form->create('CommunicationQueue', array('url' => array('action' => 'runqueue'), 'class' => 'form-inline')); ?>
                    <?php echo $this->Form->input('limit', array('label' => false, 'value' => 25, 'type' => 'number', 'class' => 'form-control', 'style' => 'width:90px;')); ?>
                    <button class="btn btn-success" type="submit"><span class="fa fa-play"></span> <?php echo __('Run Queue'); ?></button>
                    <?php echo $this->Form->end(); ?>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-striped wv-comm-table">
                <thead><tr><th>#</th><th><?php echo __('Channel'); ?></th><th><?php echo __('Recipient'); ?></th><th><?php echo __('Subject'); ?></th><th><?php echo __('Source'); ?></th><th><?php echo __('Status'); ?></th><th><?php echo __('Attempts'); ?></th><th><?php echo __('Action'); ?></th></tr></thead>
                <tbody>
                <?php if(!empty($rows)){ foreach($rows as $row){ $q=$row['CommunicationQueue']; ?>
                    <tr>
                        <td><?php echo (int)$q['id']; ?></td>
                        <td><?php echo h($q['channel']); ?></td>
                        <td><strong><?php echo h($q['recipient_name']); ?></strong><br><span class="text-muted"><?php echo h($q['recipient_mobile']); ?> <?php echo h($q['recipient_email']); ?></span></td>
                        <td><?php echo h($q['subject']); ?><div class="wv-comm-body-preview text-muted"><?php echo h(strip_tags($q['body'])); ?></div></td>
                        <td><?php echo h($q['source_type']); ?> #<?php echo (int)$q['source_id']; ?></td>
                        <td><span class="wv-comm-badge <?php echo h(strtolower($q['status'])); ?>"><?php echo h($q['status']); ?></span><?php if(!empty($q['delivery_url'])){ ?><br><a href="<?php echo h($q['delivery_url']); ?>" target="_blank"><?php echo __('Open'); ?></a><?php } ?><br><small><?php echo h($q['error_text']); ?></small></td>
                        <td><?php echo (int)$q['attempts']; ?></td>
                        <td>
                            <?php if($q['status'] === 'failed' || $q['status'] === 'prepared'){ ?>
                                <?php echo $this->Form->postLink('<span class="fa fa-refresh"></span>', array('action' => 'retry', $q['id']), array('escape' => false, 'class' => 'btn btn-warning btn-xs'), __('Retry this queue item?')); ?>
                            <?php } ?>
                        </td>
                    </tr>
                <?php }}else{ ?>
                    <tr><td colspan="8" class="text-center"><?php echo __('No queue items found.'); ?></td></tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
        <?php echo $this->element('pagination'); ?>
    </div>
</div>
