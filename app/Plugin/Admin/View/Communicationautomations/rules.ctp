<div class="panel">
    <div class="panel-heading">
        <?php echo __('Automation Rules'); ?>
        <div class="pull-right"><?php echo $this->Html->link('<span class="fa fa-plus"></span>&nbsp;'.__('Add Rule'), array('action' => 'ruleadd'), array('escape' => false, 'class' => 'btn btn-success btn-sm')); ?></div>
    </div>
    <div class="panel-body wv-comm-wrap">
        <?php echo $this->Session->flash(); ?>
        <?php echo $this->element('communication_automation_nav'); ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped wv-comm-table">
                <thead><tr><th>#</th><th><?php echo __('Rule'); ?></th><th><?php echo __('Event'); ?></th><th><?php echo __('Timing'); ?></th><th><?php echo __('Channels'); ?></th><th><?php echo __('Status'); ?></th><th><?php echo __('Actions'); ?></th></tr></thead>
                <tbody>
                <?php if(!empty($rows)){ foreach($rows as $row){ $r=$row['CommunicationRule']; ?>
                    <tr>
                        <td><?php echo (int)$r['id']; ?></td>
                        <td><strong><?php echo h($r['name']); ?></strong></td>
                        <td><?php echo h(!empty($communicationEventOptions[$r['event_key']]) ? $communicationEventOptions[$r['event_key']] : $r['event_key']); ?></td>
                        <td><?php echo h($r['trigger_timing']); ?> <?php echo (int)$r['offset_days']; ?></td>
                        <td><?php echo h($r['channels']); ?></td>
                        <td><span class="wv-comm-badge <?php echo h(strtolower($r['status'])); ?>"><?php echo h($r['status']); ?></span></td>
                        <td>
                            <?php echo $this->Html->link('<span class="fa fa-edit"></span>', array('action' => 'ruleedit', $r['id']), array('escape' => false, 'class' => 'btn btn-primary btn-xs')); ?>
                            <?php $nextStatus = ($r['status'] === 'Active') ? 'Paused' : 'Active'; ?>
                            <?php echo $this->Html->link('<span class="fa fa-toggle-on"></span>', array('action' => 'rulestatus', $r['id'], $nextStatus), array('escape' => false, 'class' => 'btn btn-warning btn-xs')); ?>
                        </td>
                    </tr>
                <?php }}else{ ?>
                    <tr><td colspan="7" class="text-center"><?php echo __('No rules found.'); ?></td></tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
        <?php echo $this->element('pagination'); ?>
    </div>
</div>
