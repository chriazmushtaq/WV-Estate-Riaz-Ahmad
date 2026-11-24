<?php
$deal = isset($dealRow['Deal']) ? $dealRow['Deal'] : array();
$client = isset($dealRow['Client']) ? $dealRow['Client'] : array();
$property = isset($dealRow['Property']) ? $dealRow['Property'] : array();
$type = isset($dealRow['Type']) ? $dealRow['Type'] : array();
$unit = isset($dealRow['Unit']) ? $dealRow['Unit'] : array();
?>
<div class="page-title">
    <div class="title-env">
        <h1 class="title"><?php echo __('Assign Balloting Unit');?></h1>
    </div>
</div>

<div class="panel">
    <div class="panel-heading">
        <?php echo $this->Html->link('<span class="fa fa-list"></span>&nbsp;'.__('Allotments'), array('action' => 'allotments', '?' => array('project_id' => $projectId, 'deal_id' => (int)$deal['id'])), array('escape' => false, 'class' => 'btn btn-default'));?>
    </div>
    <div class="panel-body">
        <?php echo $this->Session->flash();?>
        <div class="row">
            <div class="col-md-7">
                <table class="table table-bordered">
                    <tr><th><?php echo __('Deal');?></th><td><?php echo h($deal['invoice_no']);?> (ID: <?php echo (int)$deal['id'];?>)</td></tr>
                    <tr><th><?php echo __('Client');?></th><td><?php echo h($client['name']);?></td></tr>
                    <tr><th><?php echo __('Property');?></th><td><?php echo h($property['name']);?></td></tr>
                    <tr><th><?php echo __('Type / Unit');?></th><td><?php echo h($type['name']);?> / <?php echo h($unit['name']);?></td></tr>
                    <tr><th><?php echo __('Area');?></th><td><?php echo h($property['area']);?></td></tr>
                </table>
            </div>
            <div class="col-md-5">
                <div class="panel panel-default">
                    <div class="panel-heading"><strong><?php echo __('Eligibility Snapshot');?></strong></div>
                    <div class="panel-body">
                        <p><strong><?php echo __('Paid Amount');?>:</strong> <?php echo $currency.number_format((float)$eligibilitySummary['paid_amount'], 2);?></p>
                        <p><strong><?php echo __('Paid Percent');?>:</strong> <?php echo h($eligibilitySummary['paid_percent']);?>%</p>
                        <p><strong><?php echo __('Overdue Installments');?>:</strong> <?php echo (int)$eligibilitySummary['overdue_installments'];?></p>
                        <p><strong><?php echo __('Overdue Days');?>:</strong> <?php echo (int)$eligibilitySummary['overdue_days'];?></p>
                    </div>
                </div>
            </div>
        </div>

        <?php echo $this->Form->create('BallotingAllotment', array('class' => 'form-horizontal'));?>
        <?php echo $this->Form->input('deal_id', array('type' => 'hidden', 'value' => (int)$deal['id']));?>
        <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo __('Available Units');?></label>
            <div class="col-sm-8">
                <?php echo $this->Form->select('balloting_unit_id', $unitOptions, array('empty' => __('Select Unit'), 'label' => false, 'div' => false, 'class' => 'form-control'));?>
                <span class="help-block"><?php echo __('Only matching available units are shown based on deal type/category/unit/area rules.');?></span>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo __('Notes');?></label>
            <div class="col-sm-8"><?php echo $this->Form->input('notes', array('type' => 'textarea', 'rows' => 3, 'label' => false, 'div' => false, 'class' => 'form-control'));?></div>
        </div>
        <div class="form-group">
            <div class="col-sm-12">
                <button type="submit" class="btn btn-success"><span class="fa fa-check-circle"></span>&nbsp;<?php echo __('Assign Unit');?></button>
            </div>
        </div>
        <?php echo $this->Form->end();?>
    </div>
</div>
