<?php
$this->Html->addCrumb(__('Leads'), array('controller' => 'Leads', 'action' => 'index'));
$this->Html->addCrumb(__('Sales Agent Leaderboard'), array('controller' => 'Leads', 'action' => 'leaderboard'));

$leaderboardData = isset($leaderboardData) ? (array)$leaderboardData : array();
$canUseAgencyFilters = !empty($canUseAgencyFilters);
$selectedProjectId = isset($selectedProjectId) ? (int)$selectedProjectId : 0;
$projectOptions = isset($projectOptions) ? (array)$projectOptions : array();
$selectedPeriod = isset($selectedPeriod) ? (string)$selectedPeriod : 'month';

$periodOptions = array(
    'today' => __('Today'),
    'week' => __('This Week'),
    'month' => __('This Month'),
    'year' => __('This Year'),
    'all' => __('Lifetime'),
    'custom' => __('Custom Date Range')
);

// Aggregate KPI highlights
$totalAssignedAll = 0;
$totalWonAll = 0;
$totalActivitiesAll = 0;
foreach($leaderboardData as $row)
{
    $totalAssignedAll += $row['total_assigned'];
    $totalWonAll += $row['won_count'];
    $totalActivitiesAll += $row['activities_logged'];
}
$topPerformer = !empty($leaderboardData[0]) ? $leaderboardData[0] : null;
$topConverter = null;
$topActivities = null;
$bestConversion = -1;
$maxActivities = -1;
foreach($leaderboardData as $row)
{
    if($row['total_assigned'] >= 3 && $row['conversion_rate'] > $bestConversion)
    {
        $bestConversion = $row['conversion_rate'];
        $topConverter = $row;
    }
    if($row['activities_logged'] > $maxActivities)
    {
        $maxActivities = $row['activities_logged'];
        $topActivities = $row;
    }
}
?>
<style type="text/css">
.leaderboard-hero {
    background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #4338ca 100%);
    border-radius: 12px;
    padding: 24px 28px;
    color: #fff;
    margin-bottom: 24px;
    box-shadow: 0 4px 14px rgba(49, 46, 129, 0.25);
}
.leaderboard-hero h2 {
    color: #fff;
    margin: 0 0 6px 0;
    font-size: 24px;
    font-weight: 700;
}
.leaderboard-hero p {
    color: #c7d2fe;
    margin: 0;
    font-size: 13px;
}
.podium-container {
    display: flex;
    justify-content: center;
    align-items: flex-end;
    gap: 16px;
    margin: 20px 0 30px;
    flex-wrap: wrap;
}
.podium-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 20px 16px;
    text-align: center;
    width: 220px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.06);
    position: relative;
    transition: transform 0.2s ease;
}
.podium-card:hover {
    transform: translateY(-4px);
}
.podium-card.rank-1 {
    border: 2px solid #fbbf24;
    box-shadow: 0 8px 20px rgba(251, 191, 36, 0.25);
    order: 2;
    padding-top: 28px;
}
.podium-card.rank-2 {
    border: 2px solid #94a3b8;
    order: 1;
}
.podium-card.rank-3 {
    border: 2px solid #d97706;
    order: 3;
}
.podium-medal {
    font-size: 32px;
    display: block;
    margin-bottom: 8px;
}
.podium-name {
    font-weight: 700;
    font-size: 15px;
    color: #0f172a;
    margin-bottom: 4px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.podium-stats {
    font-size: 12px;
    color: #64748b;
    margin-top: 8px;
    line-height: 1.6;
}
.podium-score {
    display: inline-block;
    background: #eef2ff;
    color: #4f46e5;
    font-weight: 700;
    font-size: 11px;
    padding: 3px 10px;
    border-radius: 999px;
    margin-top: 10px;
}
.table-leaderboard thead th {
    background: #f8fafc;
    color: #475569;
    font-weight: 700;
    font-size: 12px;
    text-transform: uppercase;
    border-bottom: 2px solid #e2e8f0;
}
.table-leaderboard tbody tr:hover {
    background: #f8fafc;
}
.health-pill {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 700;
}
.health-good { background: #dcfce7; color: #166534; }
.health-med { background: #fef3c7; color: #92400e; }
.health-bad { background: #fee2e2; color: #991b1b; }
@media print {
    .no-print { display: none !important; }
}
</style>

<div class="page-title">
    <div class="title-env">
        <h1 class="title"><?php echo __('🏆 Sales Agent Leaderboard & KPI Analytics');?></h1>
    </div>
</div>

<div class="panel">
    <div class="panel-heading no-print">
        <div class="btn-group">
            <?php echo $this->Html->link('<span class="fa fa-dashboard"></span>&nbsp;'.__('CRM Dashboard'), array('controller' => 'Leads', 'action' => 'dashboard'), array('escape' => false, 'class' => 'btn btn-primary'));?>
            <?php echo $this->Html->link('<span class="fa fa-columns"></span>&nbsp;'.__('Kanban'), array('controller' => 'Leads', 'action' => 'kanban'), array('escape' => false, 'class' => 'btn btn-info'));?>
            <?php echo $this->Html->link('<span class="fa fa-table"></span>&nbsp;'.__('Manage Leads'), array('controller' => 'Leads', 'action' => 'index'), array('escape' => false, 'class' => 'btn btn-default'));?>
            <?php echo $this->Html->link('<span class="fa fa-clock-o"></span>&nbsp;'.__('Daily Progress'), array('controller' => 'Leads', 'action' => 'dailyprogress'), array('escape' => false, 'class' => 'btn btn-success'));?>
            <button class="btn btn-default" onclick="window.print();"><i class="fa fa-print"></i> <?php echo __('Print');?></button>
        </div>
    </div>
    <div class="panel-body">
        <?php echo $this->Session->flash();?>

        <!-- Filter Bar -->
        <div class="well well-sm no-print" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;margin-bottom:20px;">
            <?php echo $this->Form->create(false, array('type' => 'get', 'url' => array('controller' => 'Leads', 'action' => 'leaderboard'), 'class' => 'form-inline', 'novalidate' => true));?>
            <?php if($canUseAgencyFilters){ ?>
            <div class="form-group" style="margin-right:12px;">
                <label style="margin-right:6px;"><?php echo __('Agency:');?></label>
                <?php echo $this->Form->select('project_id', (array)$projectOptions, array('empty' => __('All Agencies'), 'class' => 'form-control', 'required' => false, 'value' => $selectedProjectId));?>
            </div>
            <?php } ?>
            <div class="form-group" style="margin-right:12px;">
                <label style="margin-right:6px;"><?php echo __('Period:');?></label>
                <?php echo $this->Form->select('period', $periodOptions, array('empty' => false, 'class' => 'form-control', 'id' => 'leadPeriodSelect', 'required' => false, 'value' => $selectedPeriod));?>
            </div>
            <div class="form-group" id="leadDateRangeWrap" style="<?php echo $selectedPeriod === 'custom' ? '' : 'display:none;';?>margin-right:12px;">
                <input type="date" name="start_date" value="<?php echo h($selectedStartDateInput);?>" class="form-control" placeholder="Start Date" required="false">
                <input type="date" name="end_date" value="<?php echo h($selectedEndDateInput);?>" class="form-control" placeholder="End Date" required="false">
            </div>
            <button type="submit" class="btn btn-primary"><i class="fa fa-filter"></i> <?php echo __('Apply Filter');?></button>
            <?php echo $this->Html->link('<i class="fa fa-refresh"></i> '.__('Reset'), array('controller' => 'Leads', 'action' => 'leaderboard'), array('escape' => false, 'class' => 'btn btn-default'));?>
            <?php echo $this->Form->end();?>
        </div>

        <!-- Hero Header -->
        <div class="leaderboard-hero">
            <div class="row">
                <div class="col-md-7">
                    <h2><i class="fa fa-trophy text-warning"></i> <?php echo __('Sales Performance & Leaderboard');?></h2>
                    <p><?php echo __('Tracking live conversion rates, closing deals, activity engagement, and follow-up health across sales agents.');?></p>
                </div>
                <div class="col-md-5 text-right">
                    <div style="font-size:28px;font-weight:800;color:#38bdf8;"><?php echo number_format($totalWonAll);?> <span style="font-size:14px;color:#cbd5e1;font-weight:400;"><?php echo __('Deals Closed');?></span></div>
                    <div style="font-size:13px;color:#a5b4fc;"><?php echo number_format($totalAssignedAll);?> <?php echo __('Total Leads Assigned');?> | <?php echo number_format($totalActivitiesAll);?> <?php echo __('Activities Logged');?></div>
                </div>
            </div>
        </div>

        <!-- Top 3 Podium -->
        <?php if(!empty($leaderboardData)){ ?>
        <div class="podium-container no-print">
            <?php if(isset($leaderboardData[1])){ ?>
            <div class="podium-card rank-2">
                <span class="podium-medal">🥈</span>
                <div class="podium-name"><?php echo h($leaderboardData[1]['name']);?></div>
                <span class="badge" style="background:#64748b;"><?php echo __('Rank #2');?></span>
                <div class="podium-stats">
                    <strong><?php echo $leaderboardData[1]['won_count'];?></strong> Deals Won<br/>
                    <strong><?php echo $leaderboardData[1]['conversion_rate'];?>%</strong> Conversion Rate<br/>
                    <?php echo $leaderboardData[1]['activities_logged'];?> Activities Logged
                </div>
                <div class="podium-score">Score: <?php echo number_format($leaderboardData[1]['score']);?></div>
            </div>
            <?php } ?>

            <?php if(isset($leaderboardData[0])){ ?>
            <div class="podium-card rank-1">
                <span class="podium-medal">🥇</span>
                <div class="podium-name" style="font-size:17px;"><?php echo h($leaderboardData[0]['name']);?></div>
                <span class="badge" style="background:#f59e0b;font-size:12px;padding:4px 10px;">🌟 <?php echo __('Top Performer #1');?></span>
                <div class="podium-stats" style="font-size:13px;">
                    <strong style="color:#059669;font-size:16px;"><?php echo $leaderboardData[0]['won_count'];?></strong> Deals Won<br/>
                    <strong style="color:#2563eb;"><?php echo $leaderboardData[0]['conversion_rate'];?>%</strong> Conversion Rate<br/>
                    <?php echo $leaderboardData[0]['activities_logged'];?> Activities Logged
                </div>
                <div class="podium-score" style="background:#fef3c7;color:#b45309;">Score: <?php echo number_format($leaderboardData[0]['score']);?></div>
            </div>
            <?php } ?>

            <?php if(isset($leaderboardData[2])){ ?>
            <div class="podium-card rank-3">
                <span class="podium-medal">🥉</span>
                <div class="podium-name"><?php echo h($leaderboardData[2]['name']);?></div>
                <span class="badge" style="background:#d97706;"><?php echo __('Rank #3');?></span>
                <div class="podium-stats">
                    <strong><?php echo $leaderboardData[2]['won_count'];?></strong> Deals Won<br/>
                    <strong><?php echo $leaderboardData[2]['conversion_rate'];?>%</strong> Conversion Rate<br/>
                    <?php echo $leaderboardData[2]['activities_logged'];?> Activities Logged
                </div>
                <div class="podium-score">Score: <?php echo number_format($leaderboardData[2]['score']);?></div>
            </div>
            <?php } ?>
        </div>
        <?php } ?>

        <!-- Ranked Leaderboard Table -->
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-leaderboard">
                <thead>
                    <tr>
                        <th style="width:60px;text-align:center;"><?php echo __('Rank');?></th>
                        <th><?php echo __('Sales Agent');?></th>
                        <th style="text-align:center;"><?php echo __('Leads Assigned');?></th>
                        <th style="text-align:center;"><?php echo __('Deals Closed (Won)');?></th>
                        <th style="text-align:center;"><?php echo __('Lost / Cancelled');?></th>
                        <th style="text-align:center;"><?php echo __('Conversion Rate');?></th>
                        <th style="text-align:center;"><?php echo __('Overdue Leads');?></th>
                        <th style="text-align:center;"><?php echo __('Follow-up Health');?></th>
                        <th style="text-align:center;"><?php echo __('Activities Logged');?></th>
                        <th style="text-align:center;"><?php echo __('Performance Score');?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if(empty($leaderboardData)){
                        echo '<tr><td colspan="10" class="text-center text-muted" style="padding:30px;">'.__('No agent records found for this period.').'</td></tr>';
                    } else {
                        foreach($leaderboardData as $rank => $agent){
                            $rankNo = $rank + 1;
                            $medal = '';
                            if($rankNo === 1) $medal = '<span style="font-size:16px;">🥇</span> ';
                            elseif($rankNo === 2) $medal = '<span style="font-size:16px;">🥈</span> ';
                            elseif($rankNo === 3) $medal = '<span style="font-size:16px;">🥉</span> ';

                            $healthClass = 'health-good';
                            if($agent['followup_health'] < 60) $healthClass = 'health-bad';
                            elseif($agent['followup_health'] < 85) $healthClass = 'health-med';
                    ?>
                    <tr>
                        <td style="text-align:center;font-weight:bold;font-size:15px;"><?php echo $medal . $rankNo;?></td>
                        <td>
                            <strong><?php echo h($agent['name']);?></strong>
                            <?php if($rankNo === 1){ ?><span class="label label-warning" style="margin-left:6px;"><i class="fa fa-star"></i> MVP</span><?php } ?>
                            <br/><small class="text-muted"><?php echo h($agent['email']);?> <?php if($agent['mobile'] !== '') echo ' | '.h($agent['mobile']);?></small>
                        </td>
                        <td style="text-align:center;font-weight:600;font-size:14px;"><?php echo number_format($agent['total_assigned']);?></td>
                        <td style="text-align:center;font-weight:700;font-size:15px;color:#059669;"><?php echo number_format($agent['won_count']);?></td>
                        <td style="text-align:center;color:#ef4444;"><?php echo number_format($agent['lost_count']);?></td>
                        <td style="text-align:center;">
                            <div style="font-weight:700;color:#2563eb;font-size:14px;"><?php echo $agent['conversion_rate'];?>%</div>
                            <div class="progress" style="height:5px;margin:3px 0 0 0;background:#e2e8f0;">
                                <div class="progress-bar progress-bar-primary" style="width:<?php echo min(100, $agent['conversion_rate']);?>%;"></div>
                            </div>
                        </td>
                        <td style="text-align:center;font-weight:600;color:<?php echo $agent['overdue_count'] > 0 ? '#dc2626' : '#64748b';?>;">
                            <?php echo number_format($agent['overdue_count']);?>
                        </td>
                        <td style="text-align:center;">
                            <span class="health-pill <?php echo $healthClass;?>"><?php echo $agent['followup_health'];?>%</span>
                        </td>
                        <td style="text-align:center;font-weight:600;color:#475569;"><?php echo number_format($agent['activities_logged']);?></td>
                        <td style="text-align:center;font-weight:800;color:#4f46e5;font-size:14px;"><?php echo number_format($agent['score']);?></td>
                    </tr>
                    <?php
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script type="text/javascript">
(function($){
    $('#leadPeriodSelect').on('change', function(){
        if($(this).val() === 'custom'){
            $('#leadDateRangeWrap').show();
        } else {
            $('#leadDateRangeWrap').hide();
        }
    });
})(jQuery);
</script>
