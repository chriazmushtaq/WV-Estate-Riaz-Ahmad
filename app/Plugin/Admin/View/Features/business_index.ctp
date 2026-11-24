<?php
$featureCfg = isset($featureCfg) ? $featureCfg : array('name' => __('Feature'));
$featureKey = isset($featureKey) ? (string)$featureKey : '';
$rows = isset($rows) && is_array($rows) ? $rows : array();
$projectName = isset($projectName) && is_array($projectName) ? $projectName : array();
$selectedProjectId = isset($selectedProjectId) ? (int)$selectedProjectId : 0;
$canUseAgencyFilters = !empty($canUseAgencyFilters);
$baseUrl = array('controller' => 'Businessfeatures', 'action' => (string)$featureCfg['route']);
?>

<div class="page-title">
    <div class="title-env">
        <h1 class="title"><?php echo h($featureCfg['name']); ?></h1>
    </div>
</div>

<div class="panel">
    <div class="panel-heading">
        <?php echo $this->Html->link('<span class="fa fa-dashboard"></span> '.__('Features Dashboard'), array('controller' => 'Businessfeatures', 'action' => 'dashboard'), array('class' => 'btn btn-default', 'escape' => false)); ?>
        <?php echo $this->Html->link('<span class="fa fa-plus"></span> '.__('Add New'), array('controller' => 'Businessfeatures', 'action' => 'add', $featureKey, '?' => array('project_id' => $selectedProjectId)), array('class' => 'btn btn-success', 'escape' => false)); ?>
        <?php echo $this->Html->link('<span class="fa fa-cog"></span> '.__('Settings'), array('controller' => 'Businessfeatures', 'action' => 'settings', $featureKey, '?' => array('project_id' => $selectedProjectId)), array('class' => 'btn btn-info', 'escape' => false)); ?>
    </div>
    <div class="panel-body">
        <?php echo $this->Session->flash(); ?>

        <?php if($canUseAgencyFilters){ ?>
        <form method="get" class="form-inline" action="<?php echo $this->Html->url($baseUrl); ?>" style="margin-bottom:12px;">
            <label style="margin-right:8px;"><?php echo __('Agency'); ?></label>
            <select name="project_id" class="form-control" style="min-width:260px;">
                <option value="0"><?php echo __('All Agency'); ?></option>
                <?php foreach($projectName as $pid => $pname){ ?>
                    <option value="<?php echo (int)$pid; ?>" <?php echo ((int)$pid === $selectedProjectId) ? 'selected="selected"' : ''; ?>><?php echo h($pname); ?></option>
                <?php } ?>
            </select>
            <button type="submit" class="btn btn-success"><span class="fa fa-search"></span> <?php echo __('Apply'); ?></button>
        </form>
        <?php } ?>

        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <?php if($canUseAgencyFilters){ ?><th><?php echo __('Agency'); ?></th><?php } ?>
                        <th><?php echo __('Title'); ?></th>
                        <th><?php echo __('Trigger'); ?></th>
                        <th><?php echo __('Priority'); ?></th>
                        <th><?php echo __('Status'); ?></th>
                        <th style="width:180px;"><?php echo __('Action'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($rows)){ ?>
                    <tr><td colspan="<?php echo $canUseAgencyFilters ? 7 : 6; ?>" class="text-center"><?php echo __('No records found.'); ?></td></tr>
                    <?php } else { $sr = 0; foreach($rows as $row){ ?>
                    <tr>
                        <td><?php echo ++$sr; ?></td>
                        <?php if($canUseAgencyFilters){ ?><td><?php echo h(isset($row['Project']['name']) ? $row['Project']['name'] : '-'); ?></td><?php } ?>
                        <td><?php echo h($row['Businessfeature']['title']); ?></td>
                        <td><?php echo h($row['Businessfeature']['trigger_event']); ?></td>
                        <td><?php echo (int)$row['Businessfeature']['priority']; ?></td>
                        <td><?php echo h($row['Businessfeature']['status']); ?></td>
                        <td>
                            <?php echo $this->Html->link('<span class="fa fa-eye"></span>', array('controller' => 'Businessfeatures', 'action' => 'view', (int)$row['Businessfeature']['id']), array('class' => 'btn btn-xs btn-info', 'escape' => false, 'title' => __('View'))); ?>
                            <?php echo $this->Html->link('<span class="fa fa-edit"></span>', array('controller' => 'Businessfeatures', 'action' => 'edit', (int)$row['Businessfeature']['id']), array('class' => 'btn btn-xs btn-primary', 'escape' => false, 'title' => __('Edit'))); ?>
                            <?php echo $this->Form->postLink('<span class="fa fa-trash"></span>', array('controller' => 'Businessfeatures', 'action' => 'delete', (int)$row['Businessfeature']['id']), array('class' => 'btn btn-xs btn-danger', 'escape' => false, 'title' => __('Delete')), __('Are you sure you want to delete this record?')); ?>
                        </td>
                    </tr>
                    <?php } } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

