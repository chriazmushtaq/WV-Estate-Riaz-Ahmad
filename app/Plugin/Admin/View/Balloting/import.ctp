<div class="page-title">
    <div class="title-env">
        <h1 class="title"><?php echo __('Import Balloting Units');?></h1>
    </div>
</div>

<div class="panel">
    <div class="panel-body">
        <?php echo $this->Session->flash();?>
        <?php echo $this->Form->create('Balloting', array('type' => 'file', 'class' => 'form-horizontal'));?>
        <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo __('Agency');?></label>
            <div class="col-sm-4">
                <?php if(!empty($canUseAgencyFilters)){?>
                <?php echo $this->Form->select('project_id', $projectName, array('empty' => __('Select Agency'), 'value' => $projectId, 'label' => false, 'class' => 'form-control', 'div' => false));?>
                <?php } else { ?>
                <?php echo $this->Form->hidden('project_id', array('value' => $projectId));?>
                <input type="text" class="form-control" value="<?php echo h(isset($projectName[$projectId]) ? $projectName[$projectId] : __('Current Agency'));?>" readonly="readonly">
                <?php } ?>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo __('CSV File');?></label>
            <div class="col-sm-6"><?php echo $this->Form->input('csv_file', array('type' => 'file', 'label' => false, 'div' => false, 'class' => 'form-control'));?></div>
        </div>
        <div class="form-group">
            <div class="col-sm-12">
                <button type="submit" class="btn btn-success"><span class="fa fa-upload"></span>&nbsp;<?php echo __('Import');?></button>
                <?php echo $this->Html->link('<span class="fa fa-download"></span>&nbsp;'.__('Download Sample CSV'), array('action' => 'importsample'), array('escape' => false, 'class' => 'btn btn-warning'));?>
                <?php echo $this->Html->link('<span class="fa fa-list"></span>&nbsp;'.__('Manage Units'), array('action' => 'units', '?' => array('project_id' => $projectId)), array('escape' => false, 'class' => 'btn btn-primary'));?>
            </div>
        </div>
        <?php echo $this->Form->end();?>

        <div class="alert alert-info">
            <strong><?php echo __('CSV Header Examples');?>:</strong><br>
            <code>allotment_no,property_type,unit,area,property_no,street_no,block_name,street_address,notes</code>
        </div>
        <div class="alert alert-warning">
            <?php echo __('You can re-import same allotment number. It will update existing record for the selected agency.');?>
        </div>

        <?php if(!empty($importSummary)){?>
        <div class="panel panel-default">
            <div class="panel-heading"><strong><?php echo __('Import Summary');?></strong></div>
            <div class="panel-body">
                <p><strong><?php echo __('Inserted');?>:</strong> <?php echo (int)$importSummary['inserted'];?></p>
                <p><strong><?php echo __('Updated');?>:</strong> <?php echo (int)$importSummary['updated'];?></p>
                <p><strong><?php echo __('Skipped');?>:</strong> <?php echo (int)$importSummary['skipped'];?></p>
            </div>
        </div>
        <?php }?>
    </div>
</div>
