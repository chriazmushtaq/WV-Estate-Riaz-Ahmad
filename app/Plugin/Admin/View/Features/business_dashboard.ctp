<?php
$cards = isset($cards) && is_array($cards) ? $cards : array();
$projectName = isset($projectName) && is_array($projectName) ? $projectName : array();
$selectedProjectId = isset($selectedProjectId) ? (int)$selectedProjectId : 0;
$canUseAgencyFilters = !empty($canUseAgencyFilters);
?>
<div class="page-title">
    <div class="title-env">
        <h1 class="title"><?php echo __('Growth Features Dashboard'); ?></h1>
    </div>
</div>

<div class="panel">
    <div class="panel-heading">
        <?php echo $this->Html->link('<span class="fa fa-home"></span> '.__('Main Dashboard'), array('controller' => 'Dashboards', 'action' => 'index'), array('class' => 'btn btn-default', 'escape' => false)); ?>
    </div>
    <div class="panel-body">
        <?php echo $this->Session->flash(); ?>

        <div class="alert alert-info" style="margin-bottom:14px;">
            <h4 style="margin-top:0;"><?php echo __('How To Use This Dashboard'); ?></h4>
            <ol style="margin-bottom:8px;padding-left:18px;">
                <li><?php echo __('Select agency first (Super Admin only).'); ?></li>
                <li><?php echo __('Open any feature card using Manage button.'); ?></li>
                <li><?php echo __('Create records from Add, and manage them with View/Edit/Delete.'); ?></li>
                <li><?php echo __('Use Settings inside each feature page to save feature rules.'); ?></li>
            </ol>
            <div><?php echo __('Tip: This module helps operations/workflows only. It does not change your existing deal/payment core logic.'); ?></div>
        </div>

        <?php if($canUseAgencyFilters){ ?>
        <form method="get" class="form-inline" action="<?php echo $this->Html->url(array('controller' => 'Businessfeatures', 'action' => 'dashboard')); ?>" style="margin-bottom:12px;">
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

        <div class="row">
            <?php foreach($cards as $card){ ?>
            <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12" style="margin-bottom:14px;">
                <div class="panel panel-primary" style="min-height:155px;">
                    <div class="panel-body text-center">
                        <div style="font-size:28px;margin-bottom:6px;"><span class="fa <?php echo h($card['icon']); ?>"></span></div>
                        <h4 style="margin:0 0 6px 0;"><?php echo h($card['title']); ?></h4>
                        <h2 style="margin:0 0 10px 0;"><?php echo (int)$card['count']; ?></h2>
                        <?php echo $this->Html->link(__('Manage').' <span class="fa fa-arrow-right"></span>', $card['url'], array('class' => 'btn btn-xs btn-default', 'escape' => false)); ?>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>
</div>
