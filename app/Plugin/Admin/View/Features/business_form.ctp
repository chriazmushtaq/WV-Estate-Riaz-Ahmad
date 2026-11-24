<?php
$featureCfg = isset($featureCfg) ? $featureCfg : array('name' => __('Feature'));
$featureKey = isset($featureKey) ? (string)$featureKey : '';
$isEdit = !empty($isEdit);
$selectedProjectId = isset($selectedProjectId) ? (int)$selectedProjectId : 0;
$projectName = isset($projectName) && is_array($projectName) ? $projectName : array();
$canUseAgencyFilters = !empty($canUseAgencyFilters);
$titleText = $isEdit ? __('Edit Record') : __('Add Record');
?>

<div class="page-title">
    <div class="title-env">
        <h1 class="title"><?php echo h($featureCfg['name']); ?> - <?php echo h($titleText); ?></h1>
    </div>
</div>

<div class="panel">
    <div class="panel-heading">
        <?php echo $this->Html->link('<span class="fa fa-arrow-left"></span> '.__('Back'), array('controller' => 'Businessfeatures', 'action' => $featureCfg['route'], '?' => array('project_id' => $selectedProjectId)), array('class' => 'btn btn-default', 'escape' => false)); ?>
    </div>
    <div class="panel-body">
        <?php echo $this->Session->flash(); ?>
        <?php echo $this->Form->create('Businessfeature', array('class' => 'form-horizontal')); ?>
        <?php if($isEdit){ echo $this->Form->hidden('id'); } ?>
        <?php echo $this->Form->hidden('feature_key', array('value' => $featureKey)); ?>

        <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo __('Agency'); ?></label>
            <div class="col-sm-4">
                <?php if($canUseAgencyFilters){ ?>
                    <?php echo $this->Form->select('project_id', $projectName, array('class' => 'form-control', 'empty' => __('Please Select'))); ?>
                <?php } else { ?>
                    <?php echo $this->Form->hidden('project_id', array('value' => $selectedProjectId)); ?>
                    <input type="text" class="form-control" readonly value="<?php echo h(isset($projectName[$selectedProjectId]) ? $projectName[$selectedProjectId] : '-'); ?>">
                <?php } ?>
            </div>
            <label class="col-sm-2 control-label"><?php echo __('Status'); ?></label>
            <div class="col-sm-4">
                <?php echo $this->Form->select('status', array('Active' => __('Active'), 'Inactive' => __('Inactive')), array('class' => 'form-control')); ?>
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo __('Title'); ?></label>
            <div class="col-sm-4"><?php echo $this->Form->input('title', array('class' => 'form-control', 'label' => false, 'div' => false, 'required' => true)); ?></div>
            <label class="col-sm-2 control-label"><?php echo __('Subtitle'); ?></label>
            <div class="col-sm-4"><?php echo $this->Form->input('subtitle', array('class' => 'form-control', 'label' => false, 'div' => false)); ?></div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo __('Trigger Event'); ?></label>
            <div class="col-sm-4"><?php echo $this->Form->input('trigger_event', array('class' => 'form-control', 'label' => false, 'div' => false, 'placeholder' => __('e.g. deal_created, payment_overdue'))); ?></div>
            <label class="col-sm-2 control-label"><?php echo __('Priority'); ?></label>
            <div class="col-sm-4"><?php echo $this->Form->input('priority', array('class' => 'form-control', 'label' => false, 'div' => false, 'type' => 'number', 'min' => '0')); ?></div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo __('Start Date'); ?></label>
            <div class="col-sm-4"><?php echo $this->Form->input('start_date', array('class' => 'form-control', 'label' => false, 'div' => false, 'type' => 'date')); ?></div>
            <label class="col-sm-2 control-label"><?php echo __('End Date'); ?></label>
            <div class="col-sm-4"><?php echo $this->Form->input('end_date', array('class' => 'form-control', 'label' => false, 'div' => false, 'type' => 'date')); ?></div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo __('Rule JSON'); ?></label>
            <div class="col-sm-10">
                <?php echo $this->Form->textarea('rule_json', array('class' => 'form-control', 'label' => false, 'div' => false, 'rows' => 5, 'placeholder' => '{"key":"value"}')); ?>
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo __('Notes'); ?></label>
            <div class="col-sm-10">
                <?php echo $this->Form->textarea('notes', array('class' => 'form-control', 'label' => false, 'div' => false, 'rows' => 4)); ?>
            </div>
        </div>

        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
                <button type="submit" class="btn btn-success"><span class="fa fa-save"></span> <?php echo __('Save'); ?></button>
                <?php echo $this->Html->link('<span class="fa fa-close"></span> '.__('Close'), array('controller' => 'Businessfeatures', 'action' => $featureCfg['route'], '?' => array('project_id' => $selectedProjectId)), array('class' => 'btn btn-danger', 'escape' => false)); ?>
            </div>
        </div>
        <?php echo $this->Form->end(); ?>
    </div>
</div>

