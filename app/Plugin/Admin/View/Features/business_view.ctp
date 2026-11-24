<?php
$row = isset($row) ? $row : array();
$featureCfg = isset($featureCfg) ? $featureCfg : array('name' => __('Feature'), 'route' => 'dashboard');
$feature = isset($row['Businessfeature']) ? $row['Businessfeature'] : array();
$projectNameText = isset($row['Project']['name']) ? $row['Project']['name'] : '-';
?>

<div class="page-title">
    <div class="title-env">
        <h1 class="title"><?php echo h($featureCfg['name']); ?> - <?php echo __('View Record'); ?></h1>
    </div>
</div>

<div class="panel">
    <div class="panel-heading">
        <?php echo $this->Html->link('<span class="fa fa-arrow-left"></span> '.__('Back'), array('controller' => 'Businessfeatures', 'action' => $featureCfg['route'], '?' => array('project_id' => (int)(isset($feature['project_id']) ? $feature['project_id'] : 0))), array('class' => 'btn btn-default', 'escape' => false)); ?>
        <?php echo $this->Html->link('<span class="fa fa-edit"></span> '.__('Edit'), array('controller' => 'Businessfeatures', 'action' => 'edit', (int)(isset($feature['id']) ? $feature['id'] : 0)), array('class' => 'btn btn-primary', 'escape' => false)); ?>
    </div>
    <div class="panel-body">
        <?php echo $this->Session->flash(); ?>
        <table class="table table-bordered">
            <tr><th style="width:220px;"><?php echo __('Agency'); ?></th><td><?php echo h($projectNameText); ?></td></tr>
            <tr><th><?php echo __('Title'); ?></th><td><?php echo h(isset($feature['title']) ? $feature['title'] : ''); ?></td></tr>
            <tr><th><?php echo __('Subtitle'); ?></th><td><?php echo h(isset($feature['subtitle']) ? $feature['subtitle'] : ''); ?></td></tr>
            <tr><th><?php echo __('Trigger Event'); ?></th><td><?php echo h(isset($feature['trigger_event']) ? $feature['trigger_event'] : ''); ?></td></tr>
            <tr><th><?php echo __('Priority'); ?></th><td><?php echo (int)(isset($feature['priority']) ? $feature['priority'] : 0); ?></td></tr>
            <tr><th><?php echo __('Status'); ?></th><td><?php echo h(isset($feature['status']) ? $feature['status'] : ''); ?></td></tr>
            <tr><th><?php echo __('Start Date'); ?></th><td><?php echo !empty($feature['start_date']) ? h(date('d-M-Y',strtotime($feature['start_date']))) : '-'; ?></td></tr>
            <tr><th><?php echo __('End Date'); ?></th><td><?php echo !empty($feature['end_date']) ? h(date('d-M-Y',strtotime($feature['end_date']))) : '-'; ?></td></tr>
            <tr><th><?php echo __('Rule JSON'); ?></th><td><pre style="white-space:pre-wrap;"><?php echo h(isset($feature['rule_json']) ? $feature['rule_json'] : ''); ?></pre></td></tr>
            <tr><th><?php echo __('Notes'); ?></th><td><?php echo nl2br(h(isset($feature['notes']) ? $feature['notes'] : '')); ?></td></tr>
            <tr><th><?php echo __('Created'); ?></th><td><?php echo !empty($feature['created']) ? h(date('d-M-Y H:i',strtotime($feature['created']))) : '-'; ?></td></tr>
            <tr><th><?php echo __('Modified'); ?></th><td><?php echo !empty($feature['modified']) ? h(date('d-M-Y H:i',strtotime($feature['modified']))) : '-'; ?></td></tr>
        </table>
    </div>
</div>
