<?php
$filters = isset($filters) ? (array)$filters : array();
$isSuperAdmin = !empty($isSuperAdmin);
$canUseAgentFilters = !empty($canUseAgentFilters);
$canUseUserLevelFilters = !empty($canUseUserLevelFilters);
$sendSmsUrl = $this->Html->url(array('controller' => 'Leads', 'action' => 'leadbulkmessage', 'sms'));
$sendEmailUrl = $this->Html->url(array('controller' => 'Leads', 'action' => 'leadbulkmessage', 'email'));
?>
<script type="text/javascript">
function crmReactivateSelectedLeadIds()
{
    var selectedIds = [];
    jQuery('#crm-reactivation-form').find('input[name="data[Lead][id][]"]:checked').each(function(){
        selectedIds.push(jQuery(this).val());
    });
    return selectedIds;
}

function crmReactivateLeadMessagePopup(targetUrl, leadIds)
{
    leadIds = jQuery.isArray(leadIds) ? leadIds : [];
    if(!leadIds.length)
    {
        alert('<?php echo addslashes(__('Please select at least one lead first.'));?>');
        return false;
    }
    var popupWindow = window.open('', 'leadBulkMessagePopup', 'width=1100,height=760,scrollbars=yes,resizable=yes');
    if(!popupWindow)
    {
        alert('<?php echo addslashes(__('Please allow popups for template selection.'));?>');
        return false;
    }
    var formHtml = '<form method="post" action="' + targetUrl + '" target="leadBulkMessagePopup" style="display:none;">';
    for(var i = 0; i < leadIds.length; i++)
    {
        formHtml += '<input type="hidden" name="data[Lead][id][]" value="' + leadIds[i] + '">';
    }
    formHtml += '</form>';
    var $form = jQuery(formHtml);
    jQuery('body').append($form);
    $form.submit();
    window.setTimeout(function(){
        try
        {
            popupWindow.focus();
        }
        catch(err){}
        $form.remove();
    }, 150);
    return false;
}

function crmReactivateSubmit(mode)
{
    var targetUrl = (mode === 'email') ? '<?php echo $sendEmailUrl;?>' : '<?php echo $sendSmsUrl;?>';
    return crmReactivateLeadMessagePopup(targetUrl, crmReactivateSelectedLeadIds());
}

function crmReactivateToggleAll(source)
{
    var form = document.getElementById('crm-reactivation-form');
    if(!form)
    {
        return;
    }
    var boxes = form.querySelectorAll('input[name="data[Lead][id][]"]');
    for(var i = 0; i < boxes.length; i++)
    {
        boxes[i].checked = !!source.checked;
    }
}
</script>
<div class="page-title">
    <div class="title-env">
        <h1 class="title"><?php echo __('CRM Lead Reactivation');?></h1>
    </div>
</div>

<div class="panel">
    <div class="panel-heading">
        <div class="btn-group">
            <?php echo $this->Html->link('<span class="fa fa-cogs"></span>&nbsp;'.__('Automation Settings'), array('controller' => 'Crmautomations', 'action' => 'index'), array('escape' => false, 'class' => 'btn btn-default'));?>
            <?php echo $this->Html->link('<span class="fa fa-random"></span>&nbsp;'.__('Assignment Rules'), array('controller' => 'Crmautomations', 'action' => 'assignmentrules', '?' => array('project_id' => isset($filters['project_id']) ? $filters['project_id'] : 0)), array('escape' => false, 'class' => 'btn btn-default'));?>
            <?php echo $this->Html->link('<span class="fa fa-clock-o"></span>&nbsp;'.__('Follow-up Queue'), array('controller' => 'Crmautomations', 'action' => 'queue'), array('escape' => false, 'class' => 'btn btn-default'));?>
            <?php echo $this->Html->link('<span class="fa fa-bullseye"></span>&nbsp;'.__('Lead Reactivation'), array('controller' => 'Crmautomations', 'action' => 'reactivation'), array('escape' => false, 'class' => 'btn btn-success'));?>
            <?php echo $this->Html->link('<span class="fa fa-pie-chart"></span>&nbsp;'.__('Source ROI'), array('controller' => 'Crmautomations', 'action' => 'sourceroi', '?' => array('project_id' => isset($filters['project_id']) ? $filters['project_id'] : 0)), array('escape' => false, 'class' => 'btn btn-info'));?>
        </div>
    </div>
    <div class="panel-body">
        <?php echo $this->Session->flash();?>

        <div class="alert alert-info">
            <strong><?php echo __('Use Case');?></strong><br/>
            <?php echo __('Find old leads that have gone cold, then send bulk SMS or Email using the same lead template flow and CRM gateway selection already used in your Leads module.');?>
        </div>

        <?php echo $this->Form->create(false, array('type' => 'get', 'class' => 'form-horizontal'));?>
        <div class="form-group">
            <?php if($isSuperAdmin){ ?>
            <label class="col-sm-1 control-label"><?php echo __('Agency');?></label>
            <div class="col-sm-3">
                <?php echo $this->Form->select('project_id', (array)$projectOptions, array('empty' => __('All Agency'), 'label' => false, 'class' => 'form-control', 'div' => false, 'value' => isset($filters['project_id']) ? $filters['project_id'] : 0));?>
            </div>
            <?php } ?>
            <?php if($canUseUserLevelFilters){ ?>
            <label class="col-sm-1 control-label"><?php echo __('User Level');?></label>
            <div class="col-sm-2">
                <?php echo $this->Form->select('ugroup_id', (array)$userLevelOptions, array('empty' => __('All Levels'), 'label' => false, 'class' => 'form-control', 'div' => false, 'value' => isset($filters['ugroup_id']) ? $filters['ugroup_id'] : 0));?>
            </div>
            <?php } ?>
            <?php if($canUseAgentFilters){ ?>
            <label class="col-sm-1 control-label"><?php echo __('User');?></label>
            <div class="col-sm-2">
                <?php echo $this->Form->select('user_id', (array)$userOptions, array('empty' => __('All Users'), 'label' => false, 'class' => 'form-control', 'div' => false, 'value' => isset($filters['user_id']) ? $filters['user_id'] : 0));?>
            </div>
            <?php } ?>
            <label class="col-sm-1 control-label"><?php echo __('Status');?></label>
            <div class="col-sm-2">
                <?php echo $this->Form->select('status_id', (array)$statusOptions, array('empty' => __('All Status'), 'label' => false, 'class' => 'form-control', 'div' => false, 'value' => isset($filters['status_id']) ? $filters['status_id'] : 0));?>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-1 control-label"><?php echo __('Older Than');?></label>
            <div class="col-sm-2">
                <?php echo $this->Form->input('older_than_days', array('type' => 'number', 'min' => 1, 'label' => false, 'class' => 'form-control', 'div' => false, 'value' => isset($filters['older_than_days']) ? (int)$filters['older_than_days'] : 30));?>
            </div>
            <div class="col-sm-9">
                <button type="submit" class="btn btn-success"><span class="fa fa-filter"></span>&nbsp;<?php echo __('Apply');?></button>
                <?php echo $this->Html->link('<span class="fa fa-refresh"></span>&nbsp;'.__('Reset'), array('controller' => 'Crmautomations', 'action' => 'reactivation'), array('escape' => false, 'class' => 'btn btn-default'));?>
            </div>
        </div>
        <?php echo $this->Form->end();?>

        <?php echo $this->Form->create('Lead', array('id' => 'crm-reactivation-form', 'url' => array('controller' => 'Crmautomations', 'action' => 'reactivation', '?' => $filters)));?>
        <?php echo $this->Form->hidden('send_mode', array('id' => 'crm-reactivation-send-mode', 'value' => ''));?>
        <div style="margin-bottom:10px;">
            <button type="button" class="btn btn-success" onclick="return crmReactivateSubmit('sms');"><span class="fa fa-comment"></span>&nbsp;<?php echo __('Bulk SMS');?></button>
            <button type="button" class="btn btn-primary" onclick="return crmReactivateSubmit('email');"><span class="fa fa-envelope"></span>&nbsp;<?php echo __('Bulk Email');?></button>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th><input type="checkbox" onclick="crmReactivateToggleAll(this);" /></th>
                        <th><?php echo __('Lead');?></th>
                        <th><?php echo __('Agency');?></th>
                        <th><?php echo __('Assigned To');?></th>
                        <th><?php echo __('Status');?></th>
                        <th><?php echo __('Last Contact');?></th>
                        <th><?php echo __('Follow Up');?></th>
                        <th><?php echo __('Action');?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($leadRows)){ ?>
                    <tr><td colspan="8" class="text-center"><?php echo __('No stale leads found.');?></td></tr>
                    <?php } ?>
                    <?php foreach((array)$leadRows as $row){ ?>
                    <?php
                        $leadName = trim((string)(isset($row['Lead']['lead_name']) ? $row['Lead']['lead_name'] : ''));
                        if($leadName === '')
                        {
                            $leadName = !empty($row['Client']['name']) ? (string)$row['Client']['name'] : '-';
                        }
                        $leadMobile = trim((string)(isset($row['Lead']['lead_mobile']) ? $row['Lead']['lead_mobile'] : ''));
                        if($leadMobile === '')
                        {
                            $leadMobile = !empty($row['Client']['mobile']) ? (string)$row['Client']['mobile'] : '-';
                        }
                        $leadCallNo = preg_replace('/[^0-9\+]/', '', (string)$leadMobile);
                    ?>
                    <tr>
                        <td><input type="checkbox" name="data[Lead][id][]" value="<?php echo (int)$row['Lead']['id'];?>" /></td>
                        <td>
                            <strong><?php echo h($leadName);?></strong><br/>
                            <small>
                            <?php
                            if($leadMobile === '' || $leadMobile === '-')
                            {
                                echo '-';
                            }
                            elseif($leadCallNo !== '')
                            {
                                echo $this->Html->link(h($leadMobile), 'tel:'.$leadCallNo, array('escape' => false));
                            }
                            else
                            {
                                echo h($leadMobile);
                            }
                            ?>
                            </small>
                        </td>
                        <td><?php echo h(!empty($row['Project']['name']) ? $row['Project']['name'] : '-');?></td>
                        <td><?php echo h(!empty($row['User']['name']) ? $row['User']['name'] : '-');?></td>
                        <td><?php echo h(!empty($row['Status']['name']) ? $row['Status']['name'] : '-');?></td>
                        <td><?php echo !empty($row['Lead']['last_contact_date']) ? $this->Time->format($sysDay.$dateSep.$sysMonth.$dateSep.$sysYear, $row['Lead']['last_contact_date']) : '-';?></td>
                        <td><?php echo (!empty($row['Lead']['follow_up']) && $row['Lead']['follow_up'] !== '0000-00-00 00:00:00') ? $this->Time->format($sysDay.$dateSep.$sysMonth.$dateSep.$sysYear.$dateGap.$sysHour.$timeSep.$sysMin.$dateGap.$sysMer, $row['Lead']['follow_up']) : '-';?></td>
                        <td class="pbutton">
                            <?php
                                $reactivationLeadId = (int)$row['Lead']['id'];
                                $hasRowAction = !empty($allowViewAction) || !empty($allowEditAction) || !empty($allowAddAction);
                            ?>
                            <?php if($hasRowAction){ ?>
                            <div class="btn-group">
                                <button type="button" class="btn btn-primary dropdown-toggle btn-xs" data-toggle="dropdown" aria-expanded="false">
                                    <?php echo __('Action');?> <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu" role="menu">
                                    <?php if(!empty($allowViewAction)){ ?>
                                    <li><?php echo $this->Html->link('<span class="fa fa-arrows-alt"></span>&nbsp;'.__('View'), array('controller' => 'Leads', 'action' => 'leadview', $reactivationLeadId), array('escape' => false));?></li>
                                    <?php } ?>
                                    <?php if(!empty($allowEditAction)){ ?>
                                    <li><?php echo $this->Html->link('<span class="fa fa-edit"></span>&nbsp;'.__('Edit'), array('controller' => 'Leads', 'action' => 'leadedit', $reactivationLeadId), array('escape' => false));?></li>
                                    <?php } ?>
                                    <?php if(!empty($allowAddAction)){ ?>
                                    <li><?php echo $this->Html->link('<span class="fa fa-comment"></span>&nbsp;'.__('Send SMS'), 'javascript:void(0);', array('escape' => false, 'onclick' => "return crmReactivateLeadMessagePopup('".$sendSmsUrl."', [".$reactivationLeadId."]);"));?></li>
                                    <li><?php echo $this->Html->link('<span class="fa fa-envelope"></span>&nbsp;'.__('Send Email'), 'javascript:void(0);', array('escape' => false, 'onclick' => "return crmReactivateLeadMessagePopup('".$sendEmailUrl."', [".$reactivationLeadId."]);"));?></li>
                                    <?php } ?>
                                </ul>
                            </div>
                            <?php } else { echo '-'; } ?>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        <?php echo $this->Form->end();?>

        <div class="text-center">
            <ul class="pagination" style="margin:0;">
                <?php echo $this->Paginator->prev('&laquo;', array('escape' => false), '<span class="disabled">&laquo;</span>', array('escape' => false));?>
                <?php echo $this->Paginator->numbers(array('modulus' => 5));?>
                <?php echo $this->Paginator->next('&raquo;', array('escape' => false), '<span class="disabled">&raquo;</span>', array('escape' => false));?>
            </ul>
        </div>
    </div>
</div>
