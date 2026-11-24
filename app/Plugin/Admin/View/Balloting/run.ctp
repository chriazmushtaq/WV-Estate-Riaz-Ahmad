<div class="page-title">
    <div class="title-env">
        <h1 class="title"><?php echo __('Run Balloting');?></h1>
    </div>
</div>

<div class="panel">
    <div class="panel-body">
        <?php echo $this->Session->flash();?>
        <?php echo $this->Form->create('BallotingRun', array('class' => 'form-horizontal'));?>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label class="col-sm-4 control-label"><?php echo __('Agency');?></label>
                    <div class="col-sm-8">
                        <?php if(!empty($canUseAgencyFilters)){?>
                        <?php echo $this->Form->select('project_id', $projectName, array('empty' => __('Select Agency'), 'value' => $projectId, 'label' => false, 'class' => 'form-control', 'div' => false));?>
                        <?php } else { ?>
                        <?php echo $this->Form->hidden('project_id', array('value' => $projectId));?>
                        <input type="text" class="form-control" value="<?php echo h(isset($projectName[$projectId]) ? $projectName[$projectId] : __('Current Agency'));?>" readonly="readonly">
                        <?php } ?>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4 control-label"><?php echo __('Run Title');?></label>
                    <div class="col-sm-8"><?php echo $this->Form->input('title', array('label' => false, 'class' => 'form-control', 'div' => false, 'placeholder' => __('Auto title if empty')));?></div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4 control-label"><?php echo __('Property Type');?></label>
                    <div class="col-sm-8"><?php echo $this->Form->select('property_type_id', $propertyTypeOptions, array('empty' => __('All Property Types'), 'value' => $runFilters['property_type_id'], 'label' => false, 'class' => 'form-control', 'div' => false));?></div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4 control-label"><?php echo __('Unit');?></label>
                    <div class="col-sm-8"><?php echo $this->Form->select('unit_id', $unitOptions, array('empty' => __('All Units'), 'value' => $runFilters['unit_id'], 'label' => false, 'class' => 'form-control', 'div' => false));?></div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="col-sm-4 control-label"><?php echo __('Category');?></label>
                    <div class="col-sm-8"><?php echo $this->Form->select('category_id', $categoryOptions, array('empty' => __('All Categories'), 'value' => $runFilters['category_id'], 'label' => false, 'class' => 'form-control', 'div' => false));?></div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4 control-label"><?php echo __('Area Min');?></label>
                    <div class="col-sm-8"><?php echo $this->Form->input('area_min', array('type' => 'number', 'step' => '0.01', 'value' => $runFilters['area_min'], 'label' => false, 'class' => 'form-control', 'div' => false));?></div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4 control-label"><?php echo __('Area Max');?></label>
                    <div class="col-sm-8"><?php echo $this->Form->input('area_max', array('type' => 'number', 'step' => '0.01', 'value' => $runFilters['area_max'], 'label' => false, 'class' => 'form-control', 'div' => false));?></div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4 control-label"><?php echo __('Notes');?></label>
                    <div class="col-sm-8"><?php echo $this->Form->input('notes', array('type' => 'textarea', 'rows' => 3, 'label' => false, 'class' => 'form-control', 'div' => false));?></div>
                </div>
                <div class="alert alert-info" style="margin-bottom:0;">
                    <strong><?php echo __('Eligibility from Settings');?>:</strong><br>
                    <?php $s=isset($settings['BallotingSetting']) ? $settings['BallotingSetting'] : array();?>
                    <?php echo __('Min Paid');?>: <?php echo h($s['min_paid_percent']);?>% | 
                    <?php echo __('Max Overdue Inst');?>: <?php echo (int)$s['max_overdue_installments'];?> | 
                    <?php echo __('Max Overdue Days');?>: <?php echo (int)$s['max_overdue_days'];?>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-12">
                <button type="submit" name="data[BallotingRun][run_action]" value="preview" class="btn btn-default"><span class="fa fa-search"></span>&nbsp;<?php echo __('Preview');?></button>
                <button type="submit" name="data[BallotingRun][run_action]" value="execute" class="btn btn-success" onclick="return confirm('<?php echo addslashes(__('Run auto-allotment now? This will allocate available units to eligible deals.'));?>');"><span class="fa fa-play"></span>&nbsp;<?php echo __('Execute Run');?></button>
                <?php echo $this->Html->link('<span class="fa fa-dashboard"></span>&nbsp;'.__('Dashboard'), array('action' => 'dashboard', '?' => array('project_id' => $projectId)), array('escape' => false, 'class' => 'btn btn-primary'));?>
                <?php echo $this->Html->link('<span class="fa fa-hand-paper-o"></span>&nbsp;'.__('Manual Allotment'), array('action' => 'manualallotment', '?' => array('project_id' => $projectId)), array('escape' => false, 'class' => 'btn btn-warning'));?>
            </div>
        </div>
        <?php echo $this->Form->end();?>
    </div>
</div>

<?php if(!empty($runSummary)){?>
<div class="panel panel-success">
    <div class="panel-body">
        <h4><?php echo __('Run Summary');?></h4>
        <p><strong><?php echo __('Run ID');?>:</strong> <?php echo (int)$runSummary['run_id'];?></p>
        <p><strong><?php echo __('Eligible');?>:</strong> <?php echo (int)$runSummary['eligible'];?></p>
        <p><strong><?php echo __('Assigned');?>:</strong> <?php echo (int)$runSummary['assigned'];?></p>
        <p><strong><?php echo __('Skipped');?>:</strong> <?php echo (int)$runSummary['skipped'];?></p>
    </div>
</div>
<?php }?>

<div class="row">
    <div class="col-md-6">
        <div class="panel panel-default">
            <div class="panel-heading"><strong><?php echo __('Eligible Deals Preview');?></strong> (<?php echo count((array)$eligiblePreview);?>)</div>
            <div class="panel-body" style="max-height:420px; overflow:auto;">
                <table class="table table-striped table-bordered">
                    <tr>
                        <th><?php echo __('Deal');?></th>
                        <th><?php echo __('Client');?></th>
                        <th><?php echo __('Paid %');?></th>
                        <th><?php echo __('Overdue');?></th>
                    </tr>
                    <?php if(empty($eligiblePreview)){?>
                    <tr><td colspan="4" class="text-center"><?php echo __('No eligible deals');?></td></tr>
                    <?php } else { foreach($eligiblePreview as $row){?>
                    <tr>
                        <td><?php echo h($row['Deal']['invoice_no']);?></td>
                        <td><?php echo h($row['Client']['name']);?></td>
                        <td><?php echo h($row[0]['paid_percent']);?>%</td>
                        <td><?php echo (int)$row[0]['overdue_installments'];?> / <?php echo (int)$row[0]['overdue_days'];?></td>
                    </tr>
                    <?php }}?>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="panel panel-default">
            <div class="panel-heading"><strong><?php echo __('Available Units Preview');?></strong> (<?php echo count((array)$availablePreview);?>)</div>
            <div class="panel-body" style="max-height:420px; overflow:auto;">
                <table class="table table-striped table-bordered">
                    <tr>
                        <th><?php echo __('Allotment #');?></th>
                        <th><?php echo __('Address');?></th>
                    </tr>
                    <?php if(empty($availablePreview)){?>
                    <tr><td colspan="2" class="text-center"><?php echo __('No available units');?></td></tr>
                    <?php } else { foreach($availablePreview as $row){?>
                    <tr>
                        <td><?php echo h($row['BallotingUnit']['allotment_no']);?></td>
                        <td><?php echo h($row['BallotingUnit']['street_address']);?></td>
                    </tr>
                    <?php }}?>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="panel panel-default">
    <div class="panel-heading"><strong><?php echo __('Recent Run History');?></strong></div>
    <div class="panel-body">
        <table class="table table-striped table-bordered">
            <tr>
                <th><?php echo __('#');?></th>
                <th><?php echo __('Title');?></th>
                <th><?php echo __('Eligible');?></th>
                <th><?php echo __('Allotted');?></th>
                <th><?php echo __('Skipped');?></th>
                <th><?php echo __('Run Date');?></th>
            </tr>
            <?php if(empty($recentRuns)){?>
            <tr><td colspan="6" class="text-center"><?php echo __('No run history');?></td></tr>
            <?php } else { $sn=1; foreach($recentRuns as $run){?>
            <tr>
                <td><?php echo $sn++;?></td>
                <td><?php echo h($run['BallotingRun']['title']);?></td>
                <td><?php echo (int)$run['BallotingRun']['total_eligible'];?></td>
                <td><?php echo (int)$run['BallotingRun']['total_allotted'];?></td>
                <td><?php echo (int)$run['BallotingRun']['total_skipped'];?></td>
                <td><?php echo !empty($run['BallotingRun']['run_date']) ? $this->Time->format($sysDay.$dateSep.$sysMonth.$dateSep.$sysYear, $run['BallotingRun']['run_date']) : '';?></td>
            </tr>
            <?php }}?>
        </table>
    </div>
</div>
