<?php $s = isset($settings['BallotingSetting']) ? $settings['BallotingSetting'] : array(); ?>

<style type="text/css">
.ballot-dash-wrap {
    width: 100%;
}
.ballot-hero {
    border: 1px solid #dbe5ef;
    border-radius: 12px;
    background: linear-gradient(135deg, #f8fbff 0%, #ffffff 72%);
    padding: 14px;
    margin-bottom: 14px;
}
.ballot-title {
    margin: 0;
    font-size: 28px;
    font-weight: 700;
    color: #1f3247;
}
.ballot-sub {
    margin: 4px 0 0;
    font-size: 13px;
    color: #677c91;
}
.ballot-toolbar {
    margin-top: 12px;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: center;
}
.ballot-toolbar .form-inline {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin: 0;
}
.ballot-toolbar .form-control {
    min-width: 170px;
}
.ballot-toolbar .btn {
    border-radius: 8px;
    font-weight: 700;
}
.ballot-stat-card {
    border: 1px solid #dbe5f0;
    border-radius: 10px;
    background: #fff;
    box-shadow: 0 2px 8px rgba(16, 38, 64, .05);
    padding: 14px 10px 12px;
    text-align: center;
    margin-bottom: 12px;
}
.ballot-stat-label {
    font-size: 12px;
    color: #5d7288;
    text-transform: uppercase;
    font-weight: 700;
    letter-spacing: .4px;
    min-height: 32px;
}
.ballot-stat-value {
    font-size: 34px;
    line-height: 1.1;
    font-weight: 800;
    color: #16304a;
}
.ballot-panel {
    border: 1px solid #dbe4ef;
    border-radius: 12px;
    overflow: hidden;
    background: #fff;
    margin-bottom: 14px;
}
.ballot-panel-head {
    padding: 11px 14px;
    border-bottom: 1px solid #e1e9f2;
    font-size: 16px;
    font-weight: 700;
    color: #20384e;
    background: #f7fafe;
}
.ballot-panel-body {
    padding: 12px 14px;
}
.ballot-settings-table {
    margin-bottom: 0;
}
.ballot-settings-table > tbody > tr > th {
    width: 42%;
    color: #4d657c;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: .3px;
    border-top: 1px solid #edf2f7;
}
.ballot-settings-table > tbody > tr > td {
    border-top: 1px solid #edf2f7;
    color: #20384e;
    font-weight: 600;
}
.ballot-table-wrap .table {
    margin-bottom: 0;
}
.ballot-table-wrap .table > thead > tr > th {
    background: #f8fbfd;
    color: #31506f;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: .35px;
}
@media (max-width: 991px) {
    .ballot-title {
        font-size: 24px;
    }
}
</style>

<div class="ballot-dash-wrap">
    <?php echo $this->Session->flash();?>

    <div class="ballot-hero">
        <h1 class="ballot-title"><?php echo __('Balloting Dashboard');?></h1>
        <p class="ballot-sub"><?php echo __('Monitor units, allotments, eligibility, and latest runs in one compact view.');?></p>

        <div class="ballot-toolbar">
            <?php echo $this->Form->create(false, array('type' => 'get', 'class' => 'form-inline'));?>
                <div class="form-group">
                    <?php if(!empty($canUseAgencyFilters)){?>
                        <?php echo $this->Form->select('project_id', $projectName, array('empty' => __('All Agency'), 'value' => $projectId, 'class' => 'form-control', 'label' => false, 'div' => false));?>
                    <?php } else { ?>
                        <?php echo $this->Form->hidden('project_id', array('value' => $projectId));?>
                    <?php } ?>
                </div>
                <button type="submit" class="btn btn-info"><span class="fa fa-filter"></span>&nbsp;<?php echo __('Load');?></button>
            <?php echo $this->Form->end();?>

            <?php echo $this->Html->link('<span class="fa fa-th-list"></span>&nbsp;'.__('Allotments'), array('action' => 'allotments', '?' => array('project_id' => $projectId)), array('escape' => false, 'class' => 'btn btn-default'));?>
            <?php echo $this->Html->link('<span class="fa fa-list"></span>&nbsp;'.__('Manage Units'), array('action' => 'units', '?' => array('project_id' => $projectId)), array('escape' => false, 'class' => 'btn btn-primary'));?>
            <?php echo $this->Html->link('<span class="fa fa-hand-paper-o"></span>&nbsp;'.__('Manual Allotment'), array('action' => 'manualallotment', '?' => array('project_id' => $projectId)), array('escape' => false, 'class' => 'btn btn-warning'));?>
            <?php echo $this->Html->link('<span class="fa fa-random"></span>&nbsp;'.__('Run Balloting'), array('action' => 'run', '?' => array('project_id' => $projectId)), array('escape' => false, 'class' => 'btn btn-success'));?>
            <?php echo $this->Html->link('<span class="fa fa-cog"></span>&nbsp;'.__('Settings'), array('action' => 'settings', '?' => array('project_id' => $projectId)), array('escape' => false, 'class' => 'btn btn-default'));?>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="ballot-stat-card">
                <div class="ballot-stat-label"><?php echo __('Total Units');?></div>
                <div class="ballot-stat-value"><?php echo (int)$totalUnits;?></div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="ballot-stat-card">
                <div class="ballot-stat-label"><?php echo __('Available');?></div>
                <div class="ballot-stat-value"><?php echo (int)$availableUnits;?></div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="ballot-stat-card">
                <div class="ballot-stat-label"><?php echo __('Allotted Units');?></div>
                <div class="ballot-stat-value"><?php echo (int)$allottedUnits;?></div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="ballot-stat-card">
                <div class="ballot-stat-label"><?php echo __('Active Allotments');?></div>
                <div class="ballot-stat-value"><?php echo (int)$activeAllotments;?></div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="ballot-stat-card">
                <div class="ballot-stat-label"><?php echo __('Cancelled');?></div>
                <div class="ballot-stat-value"><?php echo (int)$cancelledAllotments;?></div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="ballot-stat-card">
                <div class="ballot-stat-label"><?php echo __('Eligible Deals');?></div>
                <div class="ballot-stat-value"><?php echo (int)$eligibleCount;?></div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="ballot-panel">
                <div class="ballot-panel-head"><?php echo __('Current Settings');?></div>
                <div class="ballot-panel-body">
                    <table class="table ballot-settings-table">
                        <tbody>
                            <tr>
                                <th><?php echo __('Booking Date From');?></th>
                                <td><?php echo !empty($s['booking_date_from']) ? h($s['booking_date_from']) : __('Not Set');?></td>
                            </tr>
                            <tr>
                                <th><?php echo __('Booking Date To');?></th>
                                <td><?php echo !empty($s['booking_date_to']) ? h($s['booking_date_to']) : __('Not Set');?></td>
                            </tr>
                            <tr>
                                <th><?php echo __('Min Paid %');?></th>
                                <td><?php echo h($s['min_paid_percent']);?>%</td>
                            </tr>
                            <tr>
                                <th><?php echo __('Max Overdue Installments');?></th>
                                <td><?php echo (int)$s['max_overdue_installments'];?></td>
                            </tr>
                            <tr>
                                <th><?php echo __('Max Overdue Days');?></th>
                                <td><?php echo (int)$s['max_overdue_days'];?></td>
                            </tr>
                            <tr>
                                <th><?php echo __('Require Active Deal');?></th>
                                <td><?php echo !empty($s['require_active_deal']) ? __('Yes') : __('No');?></td>
                            </tr>
                            <tr>
                                <th><?php echo __('Selection Order');?></th>
                                <td><?php echo isset($selectionOrderArr[$s['selection_order']]) ? h($selectionOrderArr[$s['selection_order']]) : h($s['selection_order']);?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="ballot-panel">
                <div class="ballot-panel-head"><?php echo __('Recent Runs');?></div>
                <div class="ballot-panel-body ballot-table-wrap">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th><?php echo __('#');?></th>
                                <th><?php echo __('Title');?></th>
                                <th><?php echo __('Eligible');?></th>
                                <th><?php echo __('Allotted');?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($recentRuns)){?>
                                <tr><td colspan="4" class="text-center"><?php echo __('No run history yet');?></td></tr>
                            <?php } else { $sn=1; foreach($recentRuns as $run){?>
                                <tr>
                                    <td><?php echo $sn++;?></td>
                                    <td><?php echo h($run['BallotingRun']['title']);?></td>
                                    <td><?php echo (int)$run['BallotingRun']['total_eligible'];?></td>
                                    <td><?php echo (int)$run['BallotingRun']['total_allotted'];?></td>
                                </tr>
                            <?php }}?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="ballot-panel">
        <div class="ballot-panel-head"><?php echo __('Recent Allotments');?></div>
        <div class="ballot-panel-body ballot-table-wrap">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th><?php echo __('#');?></th>
                        <th><?php echo __('Allotment No');?></th>
                        <th><?php echo __('Deal');?></th>
                        <th><?php echo __('Client');?></th>
                        <th><?php echo __('Street Address');?></th>
                        <th><?php echo __('Date');?></th>
                        <th><?php echo __('Status');?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($recentAllotments)){?>
                        <tr><td colspan="7" class="text-center"><?php echo __('No allotments found');?></td></tr>
                    <?php } else { $sn=1; foreach($recentAllotments as $row){?>
                        <tr>
                            <td><?php echo $sn++;?></td>
                            <td><?php echo h($row['BallotingAllotment']['allotment_no']);?></td>
                            <td><?php echo h($row['Deal']['invoice_no']);?></td>
                            <td><?php echo h($row['Client']['name']);?></td>
                            <td><?php echo h($row['BallotingUnit']['street_address']);?></td>
                            <td><?php echo !empty($row['BallotingAllotment']['allotment_date']) ? $this->Time->format($sysDay.$dateSep.$sysMonth.$dateSep.$sysYear, $row['BallotingAllotment']['allotment_date']) : '';?></td>
                            <td><span class="label label-<?php echo ($row['BallotingAllotment']['status']==='Allotted') ? 'success' : 'danger';?>"><?php echo h($row['BallotingAllotment']['status']);?></span></td>
                        </tr>
                    <?php }}?>
                </tbody>
            </table>
        </div>
    </div>
</div>
