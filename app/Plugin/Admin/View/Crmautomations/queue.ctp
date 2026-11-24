<?php
$filters = isset($filters) ? (array)$filters : array();
$isSuperAdmin = !empty($isSuperAdmin);
$canUseAgentFilters = !empty($canUseAgentFilters);
$canUseUserLevelFilters = !empty($canUseUserLevelFilters);
$sendSmsUrl = $this->Html->url(array('controller' => 'Leads', 'action' => 'leadbulkmessage', 'sms'));
$sendEmailUrl = $this->Html->url(array('controller' => 'Leads', 'action' => 'leadbulkmessage', 'email'));
$scheduleSmsUrl = $this->Html->url(array('controller' => 'Leads', 'action' => 'schedulesms'));
?>
<script type="text/javascript">
function crmQueueSelectedLeadIds()
{
    var selectedIds = [];
    jQuery('.crm-queue-select:checked').each(function(){
        selectedIds.push(jQuery(this).val());
    });
    return selectedIds;
}

function crmQueueToggleAll(source)
{
    jQuery('.crm-queue-select').prop('checked', !!(source && source.checked));
}

function crmQueueLeadMessagePopup(targetUrl, leadIds)
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

function crmQueueBulkMessage(targetUrl)
{
    return crmQueueLeadMessagePopup(targetUrl, crmQueueSelectedLeadIds());
}
</script>
<div class="page-title">
    <div class="title-env">
        <h1 class="title"><?php echo __('CRM Follow-up Queue');?></h1>
    </div>
</div>

<div class="panel">
    <div class="panel-heading">
        <div class="btn-group">
            <?php echo $this->Html->link('<span class="fa fa-cogs"></span>&nbsp;'.__('Automation Settings'), array('controller' => 'Crmautomations', 'action' => 'index'), array('escape' => false, 'class' => 'btn btn-default'));?>
            <?php echo $this->Html->link('<span class="fa fa-random"></span>&nbsp;'.__('Assignment Rules'), array('controller' => 'Crmautomations', 'action' => 'assignmentrules', '?' => array('project_id' => isset($filters['project_id']) ? $filters['project_id'] : 0)), array('escape' => false, 'class' => 'btn btn-default'));?>
            <?php echo $this->Html->link('<span class="fa fa-clock-o"></span>&nbsp;'.__('Follow-up Queue'), array('controller' => 'Crmautomations', 'action' => 'queue'), array('escape' => false, 'class' => 'btn btn-success'));?>
            <?php echo $this->Html->link('<span class="fa fa-bullseye"></span>&nbsp;'.__('Lead Reactivation'), array('controller' => 'Crmautomations', 'action' => 'reactivation'), array('escape' => false, 'class' => 'btn btn-primary'));?>
            <?php echo $this->Html->link('<span class="fa fa-pie-chart"></span>&nbsp;'.__('Source ROI'), array('controller' => 'Crmautomations', 'action' => 'sourceroi', '?' => array('project_id' => isset($filters['project_id']) ? $filters['project_id'] : 0)), array('escape' => false, 'class' => 'btn btn-info'));?>
        </div>
    </div>
    <div class="panel-body">
        <?php echo $this->Session->flash();?>

        <?php echo $this->Form->create(false, array('type' => 'get', 'class' => 'form-horizontal'));?>
        <?php if($isSuperAdmin){ ?>
        <div class="form-group">
            <label class="col-sm-1 control-label"><?php echo __('Agency');?></label>
            <div class="col-sm-3">
                <?php echo $this->Form->select('project_id', (array)$projectOptions, array('empty' => __('All Agency'), 'label' => false, 'class' => 'form-control', 'div' => false, 'value' => isset($filters['project_id']) ? $filters['project_id'] : 0));?>
            </div>
            <label class="col-sm-1 control-label"><?php echo __('Queue');?></label>
            <div class="col-sm-2">
                <?php echo $this->Form->select('queue_type', (array)$queueTypeOptions, array('label' => false, 'class' => 'form-control', 'div' => false, 'value' => isset($filters['queue_type']) ? $filters['queue_type'] : 'overdue'));?>
            </div>
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
        </div>
        <div class="form-group">
            <label class="col-sm-1 control-label"><?php echo __('Status');?></label>
            <div class="col-sm-3">
                <?php echo $this->Form->select('status_id', (array)$statusOptions, array('empty' => __('All Status'), 'label' => false, 'class' => 'form-control', 'div' => false, 'value' => isset($filters['status_id']) ? $filters['status_id'] : 0));?>
            </div>
            <div class="col-sm-8">
                <button type="submit" class="btn btn-success"><span class="fa fa-filter"></span>&nbsp;<?php echo __('Apply');?></button>
                <?php echo $this->Html->link('<span class="fa fa-refresh"></span>&nbsp;'.__('Reset'), array('controller' => 'Crmautomations', 'action' => 'queue'), array('escape' => false, 'class' => 'btn btn-default'));?>
            </div>
        </div>
        <?php } else { ?>
            <?php $queueFilterCol = $canUseAgentFilters ? 2 : 3; $queueActionCol = $canUseAgentFilters ? 3 : 4; ?>
            <div class="form-group">
                <?php if($canUseAgentFilters){ ?>
                <label class="col-sm-1 control-label"><?php echo __('User');?></label>
                <div class="col-sm-2">
                    <?php echo $this->Form->select('user_id', (array)$userOptions, array('empty' => __('All Users'), 'label' => false, 'class' => 'form-control', 'div' => false, 'value' => isset($filters['user_id']) ? $filters['user_id'] : 0));?>
                </div>
                <?php } ?>
                <label class="col-sm-1 control-label"><?php echo __('Queue');?></label>
                <div class="col-sm-<?php echo $queueFilterCol;?>">
                    <?php echo $this->Form->select('queue_type', (array)$queueTypeOptions, array('label' => false, 'class' => 'form-control', 'div' => false, 'value' => isset($filters['queue_type']) ? $filters['queue_type'] : 'overdue'));?>
                </div>
                <label class="col-sm-1 control-label"><?php echo __('Status');?></label>
                <div class="col-sm-<?php echo $queueFilterCol;?>">
                    <?php echo $this->Form->select('status_id', (array)$statusOptions, array('empty' => __('All Status'), 'label' => false, 'class' => 'form-control', 'div' => false, 'value' => isset($filters['status_id']) ? $filters['status_id'] : 0));?>
                </div>
                <div class="col-sm-<?php echo $queueActionCol;?>">
                    <button type="submit" class="btn btn-success"><span class="fa fa-filter"></span>&nbsp;<?php echo __('Apply');?></button>
                    <?php echo $this->Html->link('<span class="fa fa-refresh"></span>&nbsp;'.__('Reset'), array('controller' => 'Crmautomations', 'action' => 'queue'), array('escape' => false, 'class' => 'btn btn-default'));?>
                </div>
            </div>
        <?php } ?>
        <?php echo $this->Form->end();?>

        <?php if(!empty($allowAddAction)){ ?>
        <div style="margin-bottom:10px;">
            <button type="button" class="btn btn-success" onclick="return crmQueueBulkMessage('<?php echo $sendSmsUrl;?>');"><span class="fa fa-comment"></span>&nbsp;<?php echo __('Bulk SMS');?></button>
            <button type="button" class="btn btn-primary" onclick="return crmQueueBulkMessage('<?php echo $sendEmailUrl;?>');"><span class="fa fa-envelope"></span>&nbsp;<?php echo __('Bulk Email');?></button>
            <button type="button" class="btn btn-info" onclick="return crmQueueBulkMessage('<?php echo $scheduleSmsUrl;?>');"><span class="fa fa-clock-o"></span>&nbsp;<?php echo __('Schedule SMS');?></button>
        </div>
        <?php } ?>

        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th><input type="checkbox" onclick="crmQueueToggleAll(this);" /></th>
                        <th><?php echo __('#');?></th>
                        <th><?php echo __('Lead');?></th>
                        <th><?php echo __('Agency');?></th>
                        <th><?php echo __('Assigned To');?></th>
                        <th><?php echo __('Status');?></th>
                        <th><?php echo __('Last Contact');?></th>
                        <th><?php echo __('Follow Up');?></th>
                        <th><?php echo __('Remarks');?></th>
                        <th><?php echo __('Action');?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($leadRows)){ ?>
                    <tr><td colspan="10" class="text-center"><?php echo __('No leads found.');?></td></tr>
                    <?php } ?>
                    <?php $sr = 1; foreach((array)$leadRows as $row){ ?>
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
                        $leadRemark = trim((string)(isset($row['Lead']['remarks']) ? $row['Lead']['remarks'] : ''));
                        $leadCallNo = preg_replace('/[^0-9\+]/', '', (string)$leadMobile);
                    ?>
                    <tr>
                        <td><input type="checkbox" class="crm-queue-select" value="<?php echo (int)$row['Lead']['id'];?>" /></td>
                        <td><?php echo (int)$sr++;?></td>
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
                        <td title="<?php echo h($leadRemark);?>">
                            <?php
                            if($leadRemark === '')
                            {
                                echo '-';
                            }
                            elseif(strlen($leadRemark) > 90)
                            {
                                echo h(substr($leadRemark, 0, 90)).'...';
                            }
                            else
                            {
                                echo h($leadRemark);
                            }
                            ?>
                        </td>
                        <td class="pbutton">
                            <?php
                                $queueLeadId = (int)$row['Lead']['id'];
                                $hasRowAction = !empty($allowViewAction) || !empty($allowEditAction) || !empty($allowAddAction);
                            ?>
                            <?php if($hasRowAction){ ?>
                            <div class="btn-group">
                                <button type="button" class="btn btn-primary dropdown-toggle btn-xs" data-toggle="dropdown" aria-expanded="false">
                                    <?php echo __('Action');?> <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu" role="menu">
                                    <?php if(!empty($allowViewAction)){ ?>
                                    <li><?php echo $this->Html->link('<span class="fa fa-arrows-alt"></span>&nbsp;'.__('View'), array('controller' => 'Leads', 'action' => 'leadview', $queueLeadId), array('escape' => false));?></li>
                                    <li><?php echo $this->Html->link('<span class="fa fa-history"></span>&nbsp;'.__('History'), array('controller' => 'Leads', 'action' => 'history', $queueLeadId), array('escape' => false));?></li>
                                    <?php } ?>
                                    <?php if(!empty($allowEditAction)){ ?>
                                    <li><?php echo $this->Html->link('<span class="fa fa-edit"></span>&nbsp;'.__('Edit'), array('controller' => 'Leads', 'action' => 'leadedit', $queueLeadId), array('escape' => false));?></li>
                                    <?php } ?>
                                    <?php if(!empty($allowAddAction)){ ?>
                                    <li><?php echo $this->Html->link('<span class="fa fa-comment"></span>&nbsp;'.__('Send SMS'), 'javascript:void(0);', array('escape' => false, 'onclick' => "return crmQueueLeadMessagePopup('".$sendSmsUrl."', [".$queueLeadId."]);"));?></li>
                                    <li><?php echo $this->Html->link('<span class="fa fa-envelope"></span>&nbsp;'.__('Send Email'), 'javascript:void(0);', array('escape' => false, 'onclick' => "return crmQueueLeadMessagePopup('".$sendEmailUrl."', [".$queueLeadId."]);"));?></li>
                                    <li><?php echo $this->Html->link('<span class="fa fa-clock-o"></span>&nbsp;'.__('Schedule SMS'), 'javascript:void(0);', array('escape' => false, 'onclick' => "return crmQueueLeadMessagePopup('".$scheduleSmsUrl."', [".$queueLeadId."]);"));?></li>
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

        <div class="text-center">
            <ul class="pagination" style="margin:0;">
                <?php echo $this->Paginator->prev('&laquo;', array('escape' => false), '<span class="disabled">&laquo;</span>', array('escape' => false));?>
                <?php echo $this->Paginator->numbers(array('modulus' => 5));?>
                <?php echo $this->Paginator->next('&raquo;', array('escape' => false), '<span class="disabled">&raquo;</span>', array('escape' => false));?>
            </ul>
        </div>
    </div>
</div>
