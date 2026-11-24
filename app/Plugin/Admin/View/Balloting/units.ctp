<?php
$this->Paginator->options(array(
    'update' => '#resultDiv',
    'evalScripts' => true,
));
?>
<div id="resultDiv">
    <div class="page-title">
        <div class="title-env">
            <h1 class="title"><?php echo __('Balloting Units Inventory');?></h1>
        </div>
    </div>

    <div class="panel">
        <div class="panel-heading">
            <?php echo $this->Html->link('<span class="fa fa-plus"></span>&nbsp;'.__('Add Unit'), array('action' => 'unitadd', '?' => array('project_id' => $projectId)), array('escape' => false, 'class' => 'btn btn-success'));?>
            <?php echo $this->Html->link('<span class="fa fa-upload"></span>&nbsp;'.__('Import CSV'), array('action' => 'import', '?' => array('project_id' => $projectId)), array('escape' => false, 'class' => 'btn btn-info'));?>
            <?php echo $this->Html->link('<span class="fa fa-hand-paper-o"></span>&nbsp;'.__('Manual Allotment'), array('action' => 'manualallotment', '?' => array('project_id' => $projectId)), array('escape' => false, 'class' => 'btn btn-warning'));?>
            <?php echo $this->Html->link('<span class="fa fa-dashboard"></span>&nbsp;'.__('Dashboard'), array('action' => 'dashboard', '?' => array('project_id' => $projectId)), array('escape' => false, 'class' => 'btn btn-primary'));?>
        </div>
        <div class="panel-body">
            <?php echo $this->Session->flash();?>
            <?php echo $this->Form->create(false, array('type' => 'get', 'class' => 'form-inline'));?>
            <div class="form-group">
                <?php if(!empty($canUseAgencyFilters)){?>
                <?php echo $this->Form->select('project_id', $projectName, array('empty' => __('All Agency'), 'value' => $projectId, 'label' => false, 'div' => false, 'class' => 'form-control'));?>
                <?php } else { ?>
                <?php echo $this->Form->hidden('project_id', array('value' => $projectId));?>
                <?php } ?>
            </div>
            <div class="form-group"><?php echo $this->Form->input('keyword', array('value' => $keyword, 'label' => false, 'div' => false, 'class' => 'form-control', 'placeholder' => __('Search by number/address')));?></div>
            <div class="form-group"><?php echo $this->Form->select('status', $unitStatusArr, array('empty' => __('All Status'), 'value' => $status, 'label' => false, 'div' => false, 'class' => 'form-control'));?></div>
            <div class="form-group"><?php echo $this->Form->select('property_type_id', $propertyTypeOptions, array('empty' => __('All Property Types'), 'value' => $typeId, 'label' => false, 'div' => false, 'class' => 'form-control'));?></div>
            <div class="form-group"><?php echo $this->Form->select('unit_id', $unitOptions, array('empty' => __('All Units'), 'value' => $unitId, 'label' => false, 'div' => false, 'class' => 'form-control'));?></div>
            <div class="form-group"><?php echo $this->Form->select('category_id', $categoryOptions, array('empty' => __('All Categories'), 'value' => $categoryId, 'label' => false, 'div' => false, 'class' => 'form-control'));?></div>
            <button type="submit" class="btn btn-default"><?php echo __('Filter');?></button>
            <?php echo $this->Html->link(__('Reset'), array('action' => 'units', '?' => array('project_id' => $projectId)), array('class' => 'btn btn-link'));?>
            <?php echo $this->Form->end();?>

            <div class="table-responsive" style="margin-top:12px;">
                <table class="table table-striped table-bordered">
                    <tr>
                        <th><?php echo __('Allotment #');?></th>
                        <th><?php echo __('Property Type');?></th>
                        <th><?php echo __('Unit / Area');?></th>
                        <th><?php echo __('Category');?></th>
                        <th><?php echo __('Street');?></th>
                        <th><?php echo __('Status');?></th>
                        <th><?php echo __('Action');?></th>
                    </tr>
                    <?php if(empty($rows)){?>
                    <tr><td colspan="7" class="text-center"><?php echo __('No balloting units found');?></td></tr>
                    <?php } else { foreach($rows as $row){
                        $id=(int)$row['BallotingUnit']['id'];
                        ?>
                    <tr>
                        <td>
                            <strong><?php echo h($row['BallotingUnit']['allotment_no']);?></strong><br>
                            <small><?php echo __('Property No');?>: <?php echo h($row['BallotingUnit']['property_no']);?> | <?php echo __('Street No');?>: <?php echo h($row['BallotingUnit']['street_no']);?></small>
                        </td>
                        <td><?php echo h($row['Type']['name']);?></td>
                        <td><?php echo h($row['Unit']['name']);?> / <?php echo h($row['BallotingUnit']['unit_area']);?></td>
                        <td><?php echo h($row['Category']['name']);?></td>
                        <td><?php echo h($row['BallotingUnit']['street_address']);?><br><small><?php echo __('Block');?>: <?php echo h($row['BallotingUnit']['block_name']);?></small></td>
                        <td><span class="label label-<?php echo ($row['BallotingUnit']['status']==='Available') ? 'success' : (($row['BallotingUnit']['status']==='Allotted') ? 'primary' : 'warning');?>"><?php echo h($row['BallotingUnit']['status']);?></span></td>
                        <td>
                            <?php echo $this->Html->link('<span class="fa fa-eye"></span>', array('action' => 'unitview', $id), array('escape' => false, 'class' => 'btn btn-default btn-xs', 'title' => __('View')));?>
                            <?php echo $this->Html->link('<span class="fa fa-edit"></span>', array('action' => 'unitedit', $id), array('escape' => false, 'class' => 'btn btn-primary btn-xs', 'title' => __('Edit')));?>
                            <?php echo $this->Html->link('<span class="fa fa-trash"></span>', array('action' => 'unitdelete', $id), array('escape' => false, 'class' => 'btn btn-danger btn-xs', 'title' => __('Delete'), 'confirm' => __('Delete this unit?')));?>
                        </td>
                    </tr>
                    <?php }}?>
                </table>
            </div>
            <?php echo $this->element('pagination');?>
        </div>
    </div>
</div>
