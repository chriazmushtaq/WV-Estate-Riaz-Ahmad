<?php
$featureCfg = isset($featureCfg) ? $featureCfg : array('name' => __('Feature'), 'route' => 'dashboard');
$featureKey = isset($featureKey) ? (string)$featureKey : '';
$rows = isset($rows) && is_array($rows) ? $rows : array();
$formRow = isset($formRow) && is_array($formRow) ? $formRow : array('id' => 0, 'project_id' => 0, 'setting_key' => '', 'setting_value' => '', 'status' => 'Active');
$projectName = isset($projectName) && is_array($projectName) ? $projectName : array();
$selectedProjectId = isset($selectedProjectId) ? (int)$selectedProjectId : 0;
$canUseAgencyFilters = !empty($canUseAgencyFilters);
?>
<div class="page-title">
    <div class="title-env">
        <h1 class="title"><?php echo h($featureCfg['name']); ?> - <?php echo __('Settings'); ?></h1>
    </div>
</div>

<div class="panel">
    <div class="panel-heading">
        <?php echo $this->Html->link('<span class="fa fa-arrow-left"></span> '.__('Back'), array('controller' => 'Businessfeatures', 'action' => $featureCfg['route'], '?' => array('project_id' => $selectedProjectId)), array('class' => 'btn btn-default', 'escape' => false)); ?>
    </div>
    <div class="panel-body">
        <?php echo $this->Session->flash(); ?>

        <?php if($canUseAgencyFilters){ ?>
        <form method="get" class="form-inline" action="<?php echo $this->Html->url(array('controller' => 'Businessfeatures', 'action' => 'settings', $featureKey)); ?>" style="margin-bottom:12px;">
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

        <?php echo $this->Form->create('Businessfeaturesetting', array('class' => 'form-horizontal')); ?>
        <?php echo $this->Form->hidden('task', array('value' => 'save_setting')); ?>
        <?php echo $this->Form->hidden('id', array('value' => (int)(isset($formRow['id']) ? $formRow['id'] : 0))); ?>
        <div class="well">
            <div class="row">
                <div class="col-md-3">
                    <label><?php echo __('Agency'); ?></label>
                    <?php if($canUseAgencyFilters){ ?>
                        <?php echo $this->Form->select('project_id', $projectName, array('class' => 'form-control', 'empty' => __('Please Select'), 'value' => (int)(isset($formRow['project_id']) ? $formRow['project_id'] : $selectedProjectId))); ?>
                    <?php } else { ?>
                        <?php echo $this->Form->hidden('project_id', array('value' => (int)$selectedProjectId)); ?>
                        <input type="text" class="form-control" readonly value="<?php echo h(isset($projectName[$selectedProjectId]) ? $projectName[$selectedProjectId] : '-'); ?>">
                    <?php } ?>
                </div>
                <div class="col-md-3">
                    <label><?php echo __('Setting Key'); ?></label>
                    <?php echo $this->Form->input('setting_key', array('label' => false, 'div' => false, 'class' => 'form-control', 'value' => isset($formRow['setting_key']) ? $formRow['setting_key'] : '')); ?>
                </div>
                <div class="col-md-3">
                    <label><?php echo __('Status'); ?></label>
                    <?php echo $this->Form->select('status', array('Active' => __('Active'), 'Inactive' => __('Inactive')), array('class' => 'form-control', 'value' => isset($formRow['status']) ? $formRow['status'] : 'Active')); ?>
                </div>
                <div class="col-md-3">
                    <label>&nbsp;</label><br>
                    <button type="submit" class="btn btn-success"><span class="fa fa-save"></span> <?php echo __('Save Setting'); ?></button>
                </div>
            </div>
            <div class="row" style="margin-top:10px;">
                <div class="col-md-12">
                    <label><?php echo __('Setting Value'); ?></label>
                    <?php echo $this->Form->textarea('setting_value', array('label' => false, 'div' => false, 'class' => 'form-control', 'rows' => 3, 'value' => isset($formRow['setting_value']) ? $formRow['setting_value'] : '')); ?>
                </div>
            </div>
        </div>
        <?php echo $this->Form->end(); ?>

        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <?php if($canUseAgencyFilters){ ?><th><?php echo __('Agency'); ?></th><?php } ?>
                        <th><?php echo __('Setting Key'); ?></th>
                        <th><?php echo __('Setting Value'); ?></th>
                        <th><?php echo __('Status'); ?></th>
                        <th style="width:150px;"><?php echo __('Action'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($rows)){ ?>
                        <tr><td colspan="<?php echo $canUseAgencyFilters ? 6 : 5; ?>" class="text-center"><?php echo __('No settings found.'); ?></td></tr>
                    <?php } else { $sr = 0; foreach($rows as $row){ ?>
                        <tr>
                            <td><?php echo ++$sr; ?></td>
                            <?php if($canUseAgencyFilters){ ?><td><?php echo h(isset($row['Project']['name']) ? $row['Project']['name'] : '-'); ?></td><?php } ?>
                            <td><?php echo h($row['Businessfeaturesetting']['setting_key']); ?></td>
                            <td><pre style="white-space:pre-wrap;"><?php echo h($row['Businessfeaturesetting']['setting_value']); ?></pre></td>
                            <td><?php echo h($row['Businessfeaturesetting']['status']); ?></td>
                            <td>
                                <?php echo $this->Html->link('<span class="fa fa-edit"></span>', array('controller' => 'Businessfeatures', 'action' => 'settings', $featureKey, '?' => array('project_id' => $selectedProjectId, 'edit' => (int)$row['Businessfeaturesetting']['id'])), array('class' => 'btn btn-xs btn-primary', 'escape' => false, 'title' => __('Edit'))); ?>
                                <?php echo $this->Form->create('Businessfeaturesetting', array('url' => array('controller' => 'Businessfeatures', 'action' => 'settings', $featureKey, '?' => array('project_id' => $selectedProjectId)), 'style' => 'display:inline;')); ?>
                                <?php echo $this->Form->hidden('task', array('value' => 'delete_setting')); ?>
                                <?php echo $this->Form->hidden('id', array('value' => (int)$row['Businessfeaturesetting']['id'])); ?>
                                <button type="submit" class="btn btn-xs btn-danger" onclick="return confirm('<?php echo __('Delete this setting?'); ?>');"><span class="fa fa-trash"></span></button>
                                <?php echo $this->Form->end(); ?>
                            </td>
                        </tr>
                    <?php } } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

