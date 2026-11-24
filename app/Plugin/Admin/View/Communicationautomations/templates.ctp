<div class="panel">
    <div class="panel-heading">
        <?php echo __('Communication Templates'); ?>
        <div class="pull-right"><?php echo $this->Html->link('<span class="fa fa-plus"></span>&nbsp;'.__('Add Template'), array('action' => 'templateadd'), array('escape' => false, 'class' => 'btn btn-success btn-sm')); ?></div>
    </div>
    <div class="panel-body wv-comm-wrap">
        <?php echo $this->Session->flash(); ?>
        <?php echo $this->element('communication_automation_nav'); ?>
        <div class="wv-comm-card">
            <?php echo $this->Form->create(false, array('type' => 'get', 'url' => array('action' => 'templates'), 'class' => 'wv-comm-filter')); ?>
            <div class="form-group"><?php echo $this->Form->select('channel', $communicationChannelOptions, array('empty' => __('All Channels'), 'value' => $filters['channel'], 'class' => 'form-control')); ?></div>
            <div class="form-group"><?php echo $this->Form->select('event_key', $communicationEventOptions, array('empty' => __('All Events'), 'value' => $filters['event_key'], 'class' => 'form-control')); ?></div>
            <div class="form-group"><?php echo $this->Form->input('keyword', array('label' => false, 'value' => $filters['keyword'], 'class' => 'form-control', 'placeholder' => __('Search templates'))); ?></div>
            <div class="form-group"><button class="btn btn-primary" type="submit"><span class="fa fa-search"></span> <?php echo __('Search'); ?></button> <?php echo $this->Html->link(__('Reset'), array('action' => 'templates'), array('class' => 'btn btn-default')); ?></div>
            <?php echo $this->Form->end(); ?>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-striped wv-comm-table">
                <thead><tr><th><?php echo $this->Paginator->sort('id', '#'); ?></th><th><?php echo __('Name'); ?></th><th><?php echo __('Code'); ?></th><th><?php echo __('Channel'); ?></th><th><?php echo __('Event'); ?></th><th><?php echo __('Status'); ?></th><th><?php echo __('Actions'); ?></th></tr></thead>
                <tbody>
                <?php if(!empty($rows)){ foreach($rows as $row){ $t=$row['CommunicationTemplate']; $statusClass=strtolower($t['status']); ?>
                    <tr>
                        <td><?php echo (int)$t['id']; ?></td>
                        <td><strong><?php echo h($t['name']); ?></strong><div class="text-muted"><?php echo h($t['subject']); ?></div></td>
                        <td><?php echo h($t['code']); ?></td>
                        <td><?php echo h($t['channel']); ?></td>
                        <td><?php echo h(!empty($communicationEventOptions[$t['event_key']]) ? $communicationEventOptions[$t['event_key']] : $t['event_key']); ?></td>
                        <td><span class="wv-comm-badge <?php echo h($statusClass); ?>"><?php echo h($t['status']); ?></span></td>
                        <td>
                            <?php echo $this->Html->link('<span class="fa fa-eye"></span>', array('action' => 'templatepreview', $t['id']), array('escape' => false, 'class' => 'btn btn-default btn-xs', 'target' => '_blank', 'title' => __('Preview'))); ?>
                            <?php echo $this->Html->link('<span class="fa fa-edit"></span>', array('action' => 'templateedit', $t['id']), array('escape' => false, 'class' => 'btn btn-primary btn-xs', 'title' => __('Edit'))); ?>
                            <?php $nextStatus = ($t['status'] === 'Published') ? 'Unpublished' : 'Published'; ?>
                            <?php echo $this->Html->link('<span class="fa fa-toggle-on"></span>', array('action' => 'templatestatus', $t['id'], $nextStatus), array('escape' => false, 'class' => 'btn btn-warning btn-xs', 'title' => __('Change Status'))); ?>
                        </td>
                    </tr>
                <?php }}else{ ?>
                    <tr><td colspan="7" class="text-center"><?php echo __('No templates found.'); ?></td></tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
        <?php echo $this->element('pagination'); ?>
    </div>
</div>
