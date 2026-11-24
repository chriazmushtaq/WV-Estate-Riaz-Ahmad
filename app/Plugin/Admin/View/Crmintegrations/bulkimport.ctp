<?php
$crmAjaxAgentUrl = $this->Html->url(array('controller' => 'Ajaxcontents', 'action' => 'agent'));
$crmHelpUrl = $this->Html->url('/help/crm_integrations_setup.html');
$selectedProjectId = isset($selectedProjectId) ? (int)$selectedProjectId : 0;
$selectedUserLevelId = isset($selectedUserLevelId) ? (int)$selectedUserLevelId : 0;
$selectedUserIds = isset($selectedUserIds) ? (array)$selectedUserIds : array();
$selectedUserIdsCsv = implode(',', array_map('intval', $selectedUserIds));
$selectedUserOptions = isset($selectedUserOptions) ? (array)$selectedUserOptions : array();
$autoCreateContact = !empty($autoCreateContact);
$currentImportAgencyName = isset($currentImportAgencyName) ? (string)$currentImportAgencyName : '';
$isSuperAdmin = !empty($isSuperAdmin);
$sampleUrl = array('controller' => 'Crmintegrations', 'action' => 'bulkimport', '?' => array('download' => 'sample'));
?>
<script type="text/javascript">
jQuery(document).ready(function(){
    function refreshBulkImportUsers(selectedCsv)
    {
        var projectId = jQuery('#crmBulkImportProjectId').val() || '';
        var userLevelId = jQuery('#crmBulkImportUserLevelId').val() || '';
        var requestUrl = '<?php echo $crmAjaxAgentUrl;?>?id=' + encodeURIComponent(projectId) +
            '&ugroup_id=' + encodeURIComponent(userLevelId) +
            '&select_id=crmBulkImportUserIds' +
            '&field_name=' + encodeURIComponent('CrmBulkImport.user_ids') +
            '&multiple=1' +
            '&selected=' + encodeURIComponent(selectedCsv || '');
        jQuery.ajax({
            type: 'get',
            url: requestUrl,
            success: function(response){
                if(response)
                {
                    jQuery('#crmBulkImportUserWrap').html(response);
                }
            }
        });
    }

    jQuery('#crmBulkImportProjectId, #crmBulkImportUserLevelId').on('change', function(){
        refreshBulkImportUsers('');
    });

    <?php if($selectedProjectId > 0 && $selectedUserLevelId > 0){ ?>
    refreshBulkImportUsers('<?php echo addslashes($selectedUserIdsCsv);?>');
    <?php } ?>
});
</script>

<div class="page-title">
    <div class="title-env">
        <h1 class="title"><?php echo __('CRM Bulk Import');?></h1>
    </div>
</div>

<div class="panel">
    <div class="panel-heading">
        <div class="btn-group">
            <?php echo $this->Html->link('<span class="fa fa-plug"></span>&nbsp;'.__('CRM Settings'), array('controller' => 'Crmintegrations', 'action' => 'index'), array('escape' => false, 'class' => 'btn btn-default'));?>
            <?php echo $this->Html->link('<span class="fa fa-upload"></span>&nbsp;'.__('CRM Bulk Import'), array('controller' => 'Crmintegrations', 'action' => 'bulkimport'), array('escape' => false, 'class' => 'btn btn-success'));?>
            <?php echo $this->Html->link('<span class="fa fa-random"></span>&nbsp;'.__('CRM Routing'), array('controller' => 'Crmintegrations', 'action' => 'routes'), array('escape' => false, 'class' => 'btn btn-primary'));?>
            <?php echo $this->Html->link('<span class="fa fa-history"></span>&nbsp;'.__('CRM Sync Logs'), array('controller' => 'Crmintegrations', 'action' => 'logs'), array('escape' => false, 'class' => 'btn btn-info'));?>
            <?php echo $this->Html->link('<span class="fa fa-download"></span>&nbsp;'.__('Download Sample CSV'), $sampleUrl, array('escape' => false, 'class' => 'btn btn-warning'));?>
            <?php echo $this->Html->link('<span class="fa fa-book"></span>&nbsp;'.__('Setup Guide'), $crmHelpUrl, array('escape' => false, 'class' => 'btn btn-default', 'target' => '_blank'));?>
        </div>
    </div>
    <div class="panel-body">
        <?php echo $this->Session->flash();?>

        <div class="alert alert-info">
            <strong><?php echo __('How It Works');?></strong><br/>
            <?php echo __('Upload one CSV or XLSX file, choose one user level, then select one or more users. The file rows will be shuffled first and then distributed equally in random round-robin order across the selected users.');?>
        </div>

        <?php echo $this->Form->create('CrmBulkImport', array('url' => array('controller' => 'Crmintegrations', 'action' => 'bulkimport'), 'class' => 'form-horizontal', 'type' => 'file'));?>

        <?php if($isSuperAdmin){ ?>
        <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo __('Agency');?></label>
            <div class="col-sm-4">
                <?php echo $this->Form->select('project_id', (array)$projectOptions, array('id' => 'crmBulkImportProjectId', 'label' => false, 'class' => 'form-control', 'empty' => __('Please Select'), 'div' => false, 'value' => $selectedProjectId));?>
            </div>
            <label class="col-sm-2 control-label"><?php echo __('User Level');?></label>
            <div class="col-sm-4">
                <?php echo $this->Form->select('ugroup_id', (array)$userLevelOptions, array('id' => 'crmBulkImportUserLevelId', 'label' => false, 'class' => 'form-control', 'empty' => __('Please Select'), 'div' => false, 'value' => $selectedUserLevelId));?>
            </div>
        </div>
        <?php } else {
            echo $this->Form->hidden('project_id', array('value' => $selectedProjectId, 'id' => 'crmBulkImportProjectId'));
        ?>
        <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo __('Agency');?></label>
            <div class="col-sm-10">
                <div class="form-control" style="background:#f9f9f9;">
                    <?php echo h($currentImportAgencyName !== '' ? $currentImportAgencyName : __('Current Agency'));?>
                </div>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo __('User Level');?></label>
            <div class="col-sm-10">
                <?php echo $this->Form->select('ugroup_id', (array)$userLevelOptions, array('id' => 'crmBulkImportUserLevelId', 'label' => false, 'class' => 'form-control', 'empty' => __('Please Select'), 'div' => false, 'value' => $selectedUserLevelId));?>
            </div>
        </div>
        <?php } ?>

        <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo __('Select Users');?></label>
            <div class="col-sm-10" id="crmBulkImportUserWrap">
                <?php echo $this->Form->select('user_ids', $selectedUserOptions, array('id' => 'crmBulkImportUserIds', 'label' => false, 'class' => 'form-control', 'div' => false, 'multiple' => 'multiple', 'style' => 'min-height:220px;', 'value' => $selectedUserIds));?>
                <p class="help-block"><?php echo __('Hold Ctrl or Command to select multiple users. Leads will be split equally across the selected users.');?></p>
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo __('Import File');?></label>
            <div class="col-sm-10">
                <?php echo $this->Form->input('import_file', array('type' => 'file', 'label' => false, 'div' => false, 'class' => 'form-control', 'accept' => '.csv,.xlsx'));?>
                <p class="help-block"><?php echo __('Supported: CSV and XLSX. For older Excel (.xls), save file as CSV and upload.');?></p>
            </div>
        </div>

        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
                <label>
                    <?php echo $this->Form->checkbox('create_contact', array('value' => 1, 'hiddenField' => false, 'checked' => $autoCreateContact));?>
                    <?php echo __('Auto create/link imported leads as customer contacts');?>
                </label>
                <p class="help-block"><?php echo __('Unchecked: import into Leads only. Checked: also create/link matching customer contacts.');?></p>
            </div>
        </div>

        <div class="form-group text-left">
            <div class="col-sm-offset-2 col-sm-10">
                <button type="submit" class="btn btn-success"><span class="fa fa-upload"></span>&nbsp;<?php echo __('Import And Distribute');?></button>
                <?php echo $this->Html->link('<span class="fa fa-close"></span>&nbsp;'.__('Close'), array('controller' => 'Leads', 'action' => 'dashboard'), array('escape' => false, 'class' => 'btn btn-danger'));?>
            </div>
        </div>

        <?php echo $this->Form->end();?>

        <hr />
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
