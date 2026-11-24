<?php
$isSuperAdmin = !empty($isSuperAdmin);
$canUseAgentFilters = !empty($canUseAgentFilters);
$canUseUserLevelFilters = !empty($canUseUserLevelFilters);
?>
<div class="page-title">
    <div class="title-env">
        <h1 class="title"><?php echo __('CRM Source ROI');?></h1>
    </div>
</div>

<div class="panel">
    <div class="panel-heading">
        <div class="btn-group">
            <?php echo $this->Html->link('<span class="fa fa-cogs"></span>&nbsp;'.__('Automation Settings'), array('controller' => 'Crmautomations', 'action' => 'index', '?' => array('project_id' => (int)$filters['project_id'])), array('escape' => false, 'class' => 'btn btn-default'));?>
            <?php echo $this->Html->link('<span class="fa fa-random"></span>&nbsp;'.__('Assignment Rules'), array('controller' => 'Crmautomations', 'action' => 'assignmentrules', '?' => array('project_id' => (int)$filters['project_id'])), array('escape' => false, 'class' => 'btn btn-default'));?>
            <?php echo $this->Html->link('<span class="fa fa-clock-o"></span>&nbsp;'.__('Follow-up Queue'), array('controller' => 'Crmautomations', 'action' => 'queue'), array('escape' => false, 'class' => 'btn btn-default'));?>
            <?php echo $this->Html->link('<span class="fa fa-bullseye"></span>&nbsp;'.__('Lead Reactivation'), array('controller' => 'Crmautomations', 'action' => 'reactivation'), array('escape' => false, 'class' => 'btn btn-primary'));?>
            <?php echo $this->Html->link('<span class="fa fa-pie-chart"></span>&nbsp;'.__('Source ROI'), array('controller' => 'Crmautomations', 'action' => 'sourceroi', '?' => array('project_id' => (int)$filters['project_id'])), array('escape' => false, 'class' => 'btn btn-success'));?>
            <?php echo $this->Html->link('<span class="fa fa-plug"></span>&nbsp;'.__('CRM Settings'), array('controller' => 'Crmintegrations', 'action' => 'index'), array('escape' => false, 'class' => 'btn btn-info'));?>
        </div>
    </div>
    <div class="panel-body">
        <?php echo $this->Session->flash();?>

        <div class="alert alert-info">
            <strong><?php echo __('What This Shows');?></strong><br/>
            <?php echo __('This report groups leads by source, Facebook form, ad, and campaign, then maps bookings and collections using the linked client/deal records inside your current agency and user permissions.');?>
        </div>

        <?php echo $this->Form->create(false, array('type' => 'get', 'class' => 'form-inline'));?>
        <div class="row" style="margin-bottom:15px;">
            <?php if($isSuperAdmin){ ?>
            <div class="col-md-3" style="margin-bottom:10px;">
                <label><?php echo __('Agency');?></label>
                <?php echo $this->Form->select('project_id', (array)$projectOptions, array('empty' => __('All Agencies'), 'label' => false, 'class' => 'form-control', 'div' => false, 'value' => isset($filters['project_id']) ? $filters['project_id'] : 0));?>
            </div>
            <?php } ?>
            <?php if($canUseUserLevelFilters){ ?>
            <div class="col-md-2" style="margin-bottom:10px;">
                <label><?php echo __('User Level');?></label>
                <?php echo $this->Form->select('ugroup_id', (array)$userLevelOptions, array('empty' => __('All Levels'), 'label' => false, 'class' => 'form-control', 'div' => false, 'value' => isset($filters['ugroup_id']) ? $filters['ugroup_id'] : 0));?>
            </div>
            <?php } ?>
            <?php if($canUseAgentFilters){ ?>
            <div class="col-md-2" style="margin-bottom:10px;">
                <label><?php echo __('User');?></label>
                <?php echo $this->Form->select('user_id', (array)$userOptions, array('empty' => __('All Users'), 'label' => false, 'class' => 'form-control', 'div' => false, 'value' => isset($filters['user_id']) ? $filters['user_id'] : 0));?>
            </div>
            <?php } ?>
            <div class="col-md-2" style="margin-bottom:10px;">
                <label><?php echo __('Start Date');?></label>
                <?php echo $this->Form->input('start_date', array('type' => 'text', 'label' => false, 'class' => 'form-control', 'div' => false, 'value' => isset($filters['start_date']) ? $filters['start_date'] : '', 'placeholder' => 'YYYY-MM-DD'));?>
            </div>
            <div class="col-md-2" style="margin-bottom:10px;">
                <label><?php echo __('End Date');?></label>
                <?php echo $this->Form->input('end_date', array('type' => 'text', 'label' => false, 'class' => 'form-control', 'div' => false, 'value' => isset($filters['end_date']) ? $filters['end_date'] : '', 'placeholder' => 'YYYY-MM-DD'));?>
            </div>
            <div class="col-md-1" style="margin-bottom:10px;padding-top:25px;white-space:nowrap;">
                <button type="submit" class="btn btn-success"><span class="fa fa-filter"></span> <?php echo __('Apply');?></button>
                <?php echo $this->Html->link(__('Reset'), array('controller' => 'Crmautomations', 'action' => 'sourceroi'), array('class' => 'btn btn-default'));?>
            </div>
        </div>
        <?php echo $this->Form->end();?>

        <div class="row">
            <div class="col-md-2 col-sm-6">
                <div class="panel panel-info panel-body text-center"><h4><?php echo (int)$summaryCards['total_leads'];?></h4><div><?php echo __('Leads');?></div></div>
            </div>
            <div class="col-md-2 col-sm-6">
                <div class="panel panel-success panel-body text-center"><h4><?php echo (int)$summaryCards['total_contacted'];?></h4><div><?php echo __('Contacted');?></div></div>
            </div>
            <div class="col-md-2 col-sm-6">
                <div class="panel panel-warning panel-body text-center"><h4><?php echo (int)$summaryCards['total_visits'];?></h4><div><?php echo __('Visits');?></div></div>
            </div>
            <div class="col-md-2 col-sm-6">
                <div class="panel panel-primary panel-body text-center"><h4><?php echo (int)$summaryCards['total_bookings'];?></h4><div><?php echo __('Bookings');?></div></div>
            </div>
            <div class="col-md-2 col-sm-6">
                <div class="panel panel-default panel-body text-center"><h4><?php echo number_format((float)$summaryCards['total_sales'], 0);?></h4><div><?php echo __('Sales');?></div></div>
            </div>
            <div class="col-md-2 col-sm-6">
                <div class="panel panel-danger panel-body text-center"><h4><?php echo number_format((float)$summaryCards['total_collections'], 0);?></h4><div><?php echo __('Collections');?></div></div>
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading"><strong><?php echo __('Source Performance');?></strong></div>
            <div class="panel-body" style="overflow:auto;">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th><?php echo __('#');?></th>
                            <th><?php echo __('Source');?></th>
                            <th><?php echo __('Campaign');?></th>
                            <th><?php echo __('Form ID');?></th>
                            <th><?php echo __('Ad ID');?></th>
                            <th><?php echo __('Leads');?></th>
                            <th><?php echo __('Contacted');?></th>
                            <th><?php echo __('Visits');?></th>
                            <th><?php echo __('Bookings');?></th>
                            <th><?php echo __('Sales');?></th>
                            <th><?php echo __('Collections');?></th>
                            <th><?php echo __('Assigned Users');?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if(!empty($reportRows)){ $sr=1; foreach($reportRows as $row){ ?>
                        <tr>
                            <td><?php echo $sr++;?></td>
                            <td><?php echo h($row['source_name']);?></td>
                            <td><?php echo trim((string)$row['utm_campaign']) !== '' ? h($row['utm_campaign']) : '-';?></td>
                            <td><?php echo trim((string)$row['form_id']) !== '' ? h($row['form_id']) : '-';?></td>
                            <td><?php echo trim((string)$row['ad_id']) !== '' ? h($row['ad_id']) : '-';?></td>
                            <td><?php echo (int)$row['lead_count'];?></td>
                            <td><?php echo (int)$row['contacted_count'];?></td>
                            <td><?php echo (int)$row['visit_count'];?></td>
                            <td><?php echo (int)$row['booking_count'];?></td>
                            <td><?php echo number_format((float)$row['sales_amount'], 0);?></td>
                            <td><?php echo number_format((float)$row['collection_amount'], 0);?></td>
                            <td><?php echo trim((string)$row['users_text']) !== '' ? h($row['users_text']) : '-';?></td>
                        </tr>
                    <?php } } else { ?>
                        <tr><td colspan="12" class="text-center text-muted"><?php echo __('No source ROI data found for selected filters.');?></td></tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
