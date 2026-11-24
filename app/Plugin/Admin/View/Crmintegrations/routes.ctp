<?php
$crmAjaxAgentUrl = $this->Html->url(array('controller' => 'Ajaxcontents', 'action' => 'agent'));
$selectedProvider = isset($routeForm['provider']) ? (string)$routeForm['provider'] : 'facebook_leads';
$selectedRouteTypes = isset($routeTypeOptionsMap[$selectedProvider]) ? (array)$routeTypeOptionsMap[$selectedProvider] : array();
$crmHelpUrl = $this->Html->url('/help/crm_integrations_setup.html');
?>
<script type="text/javascript">
jQuery(document).ready(function(){
    var routeTypeOptionsMap = <?php echo json_encode($routeTypeOptionsMap);?>;

    function refreshRouteTypeOptions() {
        var provider = jQuery('#CrmRouteProvider').val() || 'facebook_leads';
        var selectedValue = jQuery('#CrmRouteMatchType').attr('data-selected') || '';
        var options = routeTypeOptionsMap[provider] || {};
        var html = '';
        jQuery.each(options, function(optionValue, optionLabel){
            var isSelected = (String(selectedValue) === String(optionValue)) ? ' selected="selected"' : '';
            html += '<option value="' + optionValue + '"' + isSelected + '>' + optionLabel + '</option>';
        });
        jQuery('#CrmRouteMatchType').html(html);
    }

    function refreshRouteUsers(selectedUserId) {
        var projectId = jQuery('#CrmRouteProjectId').val() || '';
        var userLevelId = jQuery('#CrmRouteUgroupId').val() || '';
        var requestUrl = '<?php echo $crmAjaxAgentUrl;?>?id=' + encodeURIComponent(projectId) + '&ugroup_id=' + encodeURIComponent(userLevelId) + '&select_id=CrmRouteUserId';
        jQuery.ajax({
            type: 'get',
            url: requestUrl,
            success: function(response){
                if(!response) {
                    return;
                }
                jQuery('#CrmRouteUserWrap').html(response);
                if(selectedUserId) {
                    jQuery('#CrmRouteUserId').val(String(selectedUserId));
                }
            }
        });
    }

    refreshRouteTypeOptions();
    jQuery('#CrmRouteProvider').on('change', function(){
        jQuery('#CrmRouteMatchType').attr('data-selected', '');
        refreshRouteTypeOptions();
    });
    jQuery('#CrmRouteProjectId, #CrmRouteUgroupId').on('change', function(){
        refreshRouteUsers('');
    });
    refreshRouteUsers('<?php echo (int)(isset($routeForm['user_id']) ? $routeForm['user_id'] : 0);?>');
});
</script>

<div class="page-title">
    <div class="title-env">
        <h1 class="title"><?php echo __('CRM Routing');?></h1>
    </div>
</div>

<div class="panel">
    <div class="panel-heading">
        <div class="btn-group">
            <?php echo $this->Html->link('<span class="fa fa-plug"></span>&nbsp;'.__('CRM Settings'), array('controller' => 'Crmintegrations', 'action' => 'index'), array('escape' => false, 'class' => 'btn btn-default'));?>
            <?php echo $this->Html->link('<span class="fa fa-upload"></span>&nbsp;'.__('CRM Bulk Import'), array('controller' => 'Crmintegrations', 'action' => 'bulkimport'), array('escape' => false, 'class' => 'btn btn-success'));?>
            <?php echo $this->Html->link('<span class="fa fa-history"></span>&nbsp;'.__('CRM Sync Logs'), array('controller' => 'Crmintegrations', 'action' => 'logs'), array('escape' => false, 'class' => 'btn btn-info'));?>
            <a href="<?php echo $crmHelpUrl;?>" target="_blank" class="btn btn-warning"><span class="fa fa-book"></span>&nbsp;<?php echo __('Integration Help');?></a>
        </div>
    </div>
    <div class="panel-body">
        <?php echo $this->Session->flash();?>

        <div class="alert alert-info">
            <strong><?php echo __('Routing Rule');?></strong><br/>
            <?php echo __('Map a Facebook Form ID, Facebook Page ID, or WhatsApp Phone Number ID to a selected agency, user level, and user portal. Specific route mapping overrides provider default settings.');?>
        </div>

        <?php echo $this->Form->create(false, array('url' => array('action' => 'routes'), 'class' => 'form-horizontal'));?>
        <?php echo $this->Form->hidden('id', array('value' => isset($routeForm['id']) ? $routeForm['id'] : 0));?>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label class="col-sm-4 control-label"><?php echo __('Provider');?></label>
                    <div class="col-sm-8">
                        <?php echo $this->Form->select('provider', $providerOptions, array('label' => false, 'class' => 'form-control', 'div' => false, 'id' => 'CrmRouteProvider', 'value' => isset($routeForm['provider']) ? $routeForm['provider'] : 'facebook_leads'));?>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4 control-label"><?php echo __('Source Type');?></label>
                    <div class="col-sm-8">
                        <?php echo $this->Form->select('match_type', $selectedRouteTypes, array('label' => false, 'class' => 'form-control', 'div' => false, 'id' => 'CrmRouteMatchType', 'value' => isset($routeForm['match_type']) ? $routeForm['match_type'] : 'form_id', 'data-selected' => isset($routeForm['match_type']) ? $routeForm['match_type'] : 'form_id'));?>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4 control-label"><?php echo __('External ID');?></label>
                    <div class="col-sm-8">
                        <input type="text" name="data[external_id]" value="<?php echo h(isset($routeForm['external_id']) ? $routeForm['external_id'] : '');?>" class="form-control" />
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4 control-label"><?php echo __('Title');?></label>
                    <div class="col-sm-8">
                        <input type="text" name="data[title]" value="<?php echo h(isset($routeForm['title']) ? $routeForm['title'] : '');?>" class="form-control" />
                        <p class="help-block"><?php echo __('Optional label to identify this source mapping in backend.');?></p>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4 control-label"><?php echo __('Lead Source');?></label>
                    <div class="col-sm-8">
                        <input type="text" name="data[lead_source]" value="<?php echo h(isset($routeForm['lead_source']) ? $routeForm['lead_source'] : '');?>" class="form-control" />
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="col-sm-4 control-label"><?php echo __('Agency');?></label>
                    <div class="col-sm-8">
                        <?php echo $this->Form->select('project_id', $projectOptions, array('empty' => __('Please Select'), 'label' => false, 'class' => 'form-control', 'div' => false, 'id' => 'CrmRouteProjectId', 'value' => isset($routeForm['project_id']) ? $routeForm['project_id'] : 0));?>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4 control-label"><?php echo __('User Level');?></label>
                    <div class="col-sm-8">
                        <?php echo $this->Form->select('ugroup_id', $userLevelOptions, array('empty' => __('Please Select'), 'label' => false, 'class' => 'form-control', 'div' => false, 'id' => 'CrmRouteUgroupId', 'value' => isset($routeForm['ugroup_id']) ? $routeForm['ugroup_id'] : 0));?>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4 control-label"><?php echo __('User');?></label>
                    <div class="col-sm-8" id="CrmRouteUserWrap">
                        <?php echo $this->Form->select('user_id', $routeUserOptions, array('empty' => __('Please Select'), 'label' => false, 'class' => 'form-control', 'div' => false, 'id' => 'CrmRouteUserId', 'value' => isset($routeForm['user_id']) ? $routeForm['user_id'] : 0));?>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4 control-label"><?php echo __('Status');?></label>
                    <div class="col-sm-8">
                        <?php echo $this->Form->select('status_id', $statusOptions, array('empty' => __('Use Provider Default'), 'label' => false, 'class' => 'form-control', 'div' => false, 'value' => isset($routeForm['status_id']) ? $routeForm['status_id'] : 0));?>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4 control-label"><?php echo __('Flags');?></label>
                    <div class="col-sm-8" style="padding-top:7px;">
                        <label style="display:block;">
                            <input type="checkbox" name="data[auto_create_contact]" value="1" <?php if(!empty($routeForm['auto_create_contact'])){ echo 'checked="checked"'; }?> />
                            <?php echo __('Auto create/link customer contact');?>
                        </label>
                        <label style="display:block;">
                            <input type="checkbox" name="data[is_active]" value="1" <?php if(!empty($routeForm['is_active'])){ echo 'checked="checked"'; }?> />
                            <?php echo __('Route is active');?>
                        </label>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group text-left">
            <div class="col-sm-12">
                <button type="submit" class="btn btn-success"><span class="fa fa-save"></span> <?php echo !empty($routeForm['id']) ? __('Update Route') : __('Save Route');?></button>
                <?php if(!empty($routeForm['id'])){ ?>
                    <?php echo $this->Html->link('<span class="fa fa-close"></span> '.__('Cancel Edit'), array('action' => 'routes'), array('escape' => false, 'class' => 'btn btn-default'));?>
                <?php } ?>
            </div>
        </div>
        <?php echo $this->Form->end();?>

        <hr/>

        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th><?php echo __('Provider');?></th>
                        <th><?php echo __('Source Type');?></th>
                        <th><?php echo __('External ID');?></th>
                        <th><?php echo __('Title');?></th>
                        <th><?php echo __('Agency');?></th>
                        <th><?php echo __('User Level');?></th>
                        <th><?php echo __('User');?></th>
                        <th><?php echo __('Status');?></th>
                        <th><?php echo __('Lead Source');?></th>
                        <th><?php echo __('State');?></th>
                        <th><?php echo __('Action');?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($routeRows)){ ?>
                    <tr><td colspan="11" class="text-center"><?php echo __('No CRM routing rules saved yet.');?></td></tr>
                    <?php } ?>
                    <?php foreach((array)$routeRows as $routeRow){
                        $row = (array)$routeRow['CrmIntegrationRoute'];
                        $providerLabel = isset($providerOptions[$row['provider']]) ? $providerOptions[$row['provider']] : $row['provider'];
                        $typeLabel = isset($routeTypeOptionsMap[$row['provider']][$row['match_type']]) ? $routeTypeOptionsMap[$row['provider']][$row['match_type']] : $row['match_type'];
                    ?>
                    <tr>
                        <td><?php echo h($providerLabel);?></td>
                        <td><?php echo h($typeLabel);?></td>
                        <td><?php echo h($row['external_id']);?></td>
                        <td><?php echo h($row['title']);?></td>
                        <td><?php echo h(isset($routeRow['Project']['name']) ? $routeRow['Project']['name'] : '');?></td>
                        <td><?php echo h(isset($routeRow['Ugroup']['name']) ? $routeRow['Ugroup']['name'] : '');?></td>
                        <td><?php echo h(isset($routeRow['User']['name']) ? $routeRow['User']['name'] : '');?></td>
                        <td><?php echo h(isset($routeRow['Status']['name']) ? $routeRow['Status']['name'] : '');?></td>
                        <td><?php echo h($row['lead_source']);?></td>
                        <td><?php echo !empty($row['is_active']) ? __('Active') : __('Disabled');?><?php if(!empty($row['auto_create_contact'])){ ?><br/><?php echo __('Contact Link: On');?><?php } ?></td>
                        <td><?php echo $this->Html->link(__('Edit'), array('action' => 'routes', '?' => array('edit' => (int)$row['id'])), array('class' => 'btn btn-xs btn-primary'));?> <?php echo $this->Html->link(__('Delete'), array('action' => 'routedelete', (int)$row['id']), array('class' => 'btn btn-xs btn-danger'), __('Are you sure you want to delete this route?'));?></td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


