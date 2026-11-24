<?php
$agentAjaxUrl = $this->Html->url(array('controller'=>'Ajaxcontents','action'=>'agent'));
$selectedProjectId = isset($selectedProjectId) ? (int)$selectedProjectId : 0;
$selectedUserLevelId = isset($selectedUserLevelId) ? (int)$selectedUserLevelId : 0;
$selectedUserId = isset($selectedUserId) ? (int)$selectedUserId : 0;
$importIsolatedToCurrentUser = !empty($importIsolatedToCurrentUser);
$showImportCreateContactOption = !empty($showImportCreateContactOption);
$currentImportUserName = isset($currentImportUserName) ? (string)$currentImportUserName : '';
$currentImportUserLevelName = isset($currentImportUserLevelName) ? (string)$currentImportUserLevelName : '';
$currentImportAgencyName = isset($currentImportAgencyName) ? (string)$currentImportAgencyName : '';
?>
<?php if(!$importIsolatedToCurrentUser){ ?>
<script type="text/javascript">
jQuery(document).ready(function(){
    function refreshImportUsers(selectedUserId)
    {
        var projectId = jQuery('#leadImportProjectId').val() || '';
        var userLevelId = jQuery('#leadImportUserLevelId').val() || '';
        var requestUrl = '<?php echo $agentAjaxUrl;?>?id=' + encodeURIComponent(projectId) + '&ugroup_id=' + encodeURIComponent(userLevelId) + '&select_id=leadImportUserId';
        jQuery.ajax({
            type: 'get',
            url: requestUrl,
            success: function(response){
                if(response){
                    jQuery('#leadImportUserWrap').html(response);
                    if(selectedUserId){
                        jQuery('#leadImportUserId').val(String(selectedUserId));
                    }
                }
            }
        });
    }
    jQuery('#leadImportProjectId, #leadImportUserLevelId').on('change', function(){
        refreshImportUsers('');
    });
});
</script>
<?php } ?>
<div class="page-title">
    <div class="title-env">
        <h1 class="title"><?php echo __('Import Lead Contacts');?></h1>
    </div>
</div>
<div class="panel">
    <div class="panel-heading">
        <div class="btn-group">
            <?php echo $this->Html->link('<span class="fa fa-dashboard"></span>&nbsp;'.__('CRM Dashboard'),array('controller'=>'Leads','action'=>'dashboard'),array('escape'=>false,'class'=>'btn btn-default'));?>
            <?php echo $this->Html->link('<span class="fa fa-plus"></span>&nbsp;'.__('Add Lead Contact'),array('controller'=>'Leads','action'=>'leadadd'),array('escape'=>false,'class'=>'btn btn-success'));?>
            <?php echo $this->Html->link('<span class="fa fa-columns"></span>&nbsp;'.__('Kanban'),array('controller'=>'Leads','action'=>'kanban'),array('escape'=>false,'class'=>'btn btn-primary'));?>
            <?php echo $this->Html->link('<span class="fa fa-download"></span>&nbsp;'.__('Download Sample CSV'),array('controller'=>'Leads','action'=>'samplecsv'),array('escape'=>false,'class'=>'btn btn-info'));?>
        </div>
    </div>
    <div class="panel-body">
        <?php echo $this->Session->flash();?>

        <?php if(!empty($duplicateReport)){ ?>
        <div class="alert alert-warning" style="background:#fffbeb;border:1px solid #fde68a;color:#92400e;border-radius:8px;padding:16px;margin-bottom:24px;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
                <h4 style="margin:0;font-weight:800;color:#b45309;">
                    <span class="fa fa-exclamation-triangle"></span> <?php echo __('Duplicate Leads Detection Report (%s Skipped)', count($duplicateReport));?>
                </h4>
                <span class="badge" style="background:#dc2626;font-size:12px;padding:5px 10px;"><?php echo __('Not Re-added to Prevent Duplicate Leads');?></span>
            </div>
            <p style="font-size:13px;margin-bottom:14px;color:#78350f;">
                <?php echo __('The following leads from your uploaded file already exist in the CRM. They were <strong>automatically skipped</strong> to prevent duplicate entries, and an inquiry note has been logged to the original lead history.');?>
            </p>
            <div class="table-responsive" style="background:#ffffff;border-radius:6px;box-shadow:0 1px 3px rgba(0,0,0,0.08);">
                <table class="table table-bordered table-striped" style="margin-bottom:0;font-size:12px;">
                    <thead>
                        <tr style="background:#fef3c7;color:#92400e;">
                            <th style="width:50px;">#</th>
                            <th><?php echo __('Lead / Client Name');?></th>
                            <th><?php echo __('Mobile #');?></th>
                            <th><?php echo __('Email');?></th>
                            <th style="background:#fef9c3;color:#854d0e;"><span class="fa fa-user"></span> <?php echo __('Currently Assigned Agent');?></th>
                            <th><?php echo __('Agency / Project');?></th>
                            <th><?php echo __('Current Status');?></th>
                            <th><?php echo __('Original Date');?></th>
                            <th><?php echo __('Existing Lead');?></th>
                            <th><?php echo __('Action Taken');?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($duplicateReport as $dIdx => $dRow){ ?>
                        <tr>
                            <td><strong><?php echo isset($dRow['row_num']) ? (int)$dRow['row_num'] : ($dIdx + 1);?></strong></td>
                            <td><strong><?php echo h($dRow['lead_name']);?></strong></td>
                            <td><strong style="color:#2563eb;"><?php echo h($dRow['mobile']);?></strong></td>
                            <td><?php echo trim((string)$dRow['email']) !== '' ? h($dRow['email']) : '-';?></td>
                            <td style="background:#fefce8;font-weight:700;color:#1e293b;">
                                <i class="fa fa-user" style="color:#3b82f6;"></i> <?php echo h($dRow['assigned_agent']);?>
                            </td>
                            <td><?php echo h($dRow['agency']);?></td>
                            <td><span class="label label-info" style="font-size:11px;"><?php echo h($dRow['status']);?></span></td>
                            <td><?php echo h($dRow['created_date']);?></td>
                            <td>
                                <?php if(!empty($dRow['existing_lead_id'])){ ?>
                                    <a href="<?php echo $this->Html->url(array('controller'=>'Leads','action'=>'leadview',$dRow['existing_lead_id']));?>" target="_blank" class="btn btn-xs btn-primary" style="font-weight:700;">
                                        <span class="fa fa-external-link"></span> #<?php echo (int)$dRow['existing_lead_id'];?>
                                    </a>
                                <?php } else { echo '-'; } ?>
                            </td>
                            <td>
                                <span class="label label-danger" style="font-size:10px;padding:3px 6px;">
                                    <span class="fa fa-ban"></span> <?php echo __('Duplicate Skipped');?>
                                </span>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php } ?>

        <?php echo $this->Form->create('Lead', array('controller'=>'Leads','action'=>'import','class'=>'form-horizontal','type'=>'file'));?>

        <?php if(!$importIsolatedToCurrentUser){ ?>
        <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo __('Agency');?></label>
            <div class="col-sm-4">
                <?php echo $this->Form->select('project_id',$projectOptions,array('id'=>'leadImportProjectId','label'=>false,'class'=>'form-control','empty'=>__('Please Select'),'div'=>false,'value'=>$selectedProjectId));?>
            </div>
            <label class="col-sm-2 control-label"><?php echo __('User Level');?></label>
            <div class="col-sm-4">
                <?php echo $this->Form->select('ugroup_id',$userLevelOptions,array('id'=>'leadImportUserLevelId','label'=>false,'class'=>'form-control','empty'=>__('All User Levels'),'div'=>false,'value'=>$selectedUserLevelId));?>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo __('User');?></label>
            <div class="col-sm-10" id="leadImportUserWrap">
                <?php echo $this->Form->select('user_id',$agentOptions,array('id'=>'leadImportUserId','label'=>false,'class'=>'form-control','empty'=>__('Please Select'),'div'=>false,'value'=>$selectedUserId));?>
            </div>
        </div>
        <?php } else {
            echo $this->Form->hidden('project_id',array('value'=>$selectedProjectId,'id'=>'leadImportProjectId'));
            echo $this->Form->hidden('ugroup_id',array('value'=>$selectedUserLevelId,'id'=>'leadImportUserLevelId'));
            echo $this->Form->hidden('user_id',array('value'=>$selectedUserId,'id'=>'leadImportUserId'));
        ?>
        <div class="alert alert-info">
            <strong><?php echo __('Import Scope');?></strong><br/>
            <?php echo __('Imported leads will be saved only in your logged-in account.');?>
            <?php if($currentImportAgencyName!==''){ ?><br/><?php echo __('Agency');?>: <?php echo h($currentImportAgencyName);?><?php } ?>
            <?php if($currentImportUserLevelName!==''){ ?><br/><?php echo __('User Level');?>: <?php echo h($currentImportUserLevelName);?><?php } ?>
            <?php if($currentImportUserName!==''){ ?><br/><?php echo __('User');?>: <?php echo h($currentImportUserName);?><?php } ?>
        </div>
        <?php
        } ?>

        <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo __('Import File');?></label>
            <div class="col-sm-10">
                <?php echo $this->Form->input('import_file',array('type'=>'file','label'=>false,'div'=>false,'class'=>'form-control','accept'=>'.csv,.xlsx'));?>
                <p class="help-block"><?php echo __('Supported: CSV and XLSX. For older Excel (.xls), save file as CSV and upload.');?></p>
            </div>
        </div>

        <?php if($showImportCreateContactOption){ ?>
        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
                <label>
                    <?php echo $this->Form->checkbox('create_contact',array('value'=>1,'hiddenField'=>false,'checked'=>false));?>
                    <?php echo __('Auto create/link imported leads as customer contacts');?>
                </label>
                <p class="help-block"><?php echo __('Unchecked: import into Leads only. Checked: also create/link matching customer contacts.');?></p>
            </div>
        </div>
        <?php } ?>

        <div class="form-group text-left">
            <div class="col-sm-offset-2 col-sm-10">
                <button type="submit" class="btn btn-success"><span class="fa fa-upload"></span> <?php echo __('Import');?></button>
                <?php echo $this->Html->link('<span class="fa fa-close"></span> '.__('Close'),array('controller'=>'Leads','action'=>'dashboard'),array('class'=>'btn btn-danger','escape'=>false));?>
            </div>
        </div>

        <?php echo $this->Form->end();?>

        <hr/>
        <h4><?php echo __('Sample File Columns');?></h4>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th><?php echo __('lead_name');?></th>
                        <th><?php echo __('lead_mobile');?></th>
                        <th><?php echo __('lead_email');?></th>
                        <th><?php echo __('lead_address');?></th>
                        <th><?php echo __('lead_source');?></th>
                        <?php if(!$importIsolatedToCurrentUser){ ?>
                        <th><?php echo __('user_level');?></th>
                        <th><?php echo __('user_id');?></th>
                        <?php } ?>
                        <th><?php echo __('status');?></th>
                        <th><?php echo __('follow_up');?></th>
                        <th><?php echo __('remarks');?></th>
                        <th><?php echo __('property');?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Ahmad Raza</td>
                        <td>923001234567</td>
                        <td>ahmad@example.com</td>
                        <td>Johar Town Lahore</td>
                        <td>Facebook</td>
                        <?php if(!$importIsolatedToCurrentUser){ ?>
                        <td>CRM</td>
                        <td>58</td>
                        <?php } ?>
                        <td>New</td>
                        <td>2026-04-02 15:00:00</td>
                        <td>Interested in 3 marla file</td>
                        <td>1 Marla Plot</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
