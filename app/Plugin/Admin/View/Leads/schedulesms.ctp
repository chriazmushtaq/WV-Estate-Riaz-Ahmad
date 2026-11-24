<?php
$templateMapJson = !empty($templateMap) ? json_encode($templateMap) : '{}';
$manageUrl = $this->Html->url(array('controller' => 'Leads', 'action' => 'manage'), true);
$bulkSmsUrl = $this->Html->url(array('controller' => 'Leads', 'action' => 'leadbulkmessage', 'sms', implode(',', (array)$selectedLeadIds)), true);
$scheduleDateFormat = (isset($dpFormat) && trim((string)$dpFormat) !== '') ? trim((string)$dpFormat) : 'DD-MMM-YYYY';
$scheduleDateTimeFormat = trim($scheduleDateFormat.' hh:mm A');
?>
<script type="text/javascript">
jQuery(document).ready(function(){
    var templateMap = <?php echo $templateMapJson ? $templateMapJson : '{}';?>;
    var selectedTemplateId = '<?php echo (int)$selectedTemplateId;?>';
    var pickerFormat = '<?php echo addslashes($scheduleDateTimeFormat);?>';

    function applyTemplate(forceApply){
        var templateId = parseInt(jQuery('#leadScheduleTemplateId').val() || '0', 10);
        if(!templateMap[templateId]){
            return;
        }
        var tpl = templateMap[templateId];
        var messageField = jQuery('#LeadMessage');
        if(forceApply || jQuery.trim(messageField.val())==='' || messageField.data('autofill')===1){
            messageField.val(tpl.description || '');
            messageField.data('autofill', 1);
        }
    }

    jQuery('#leadScheduleTemplateId').on('change', function(){ applyTemplate(true); });
    jQuery('#LeadMessage').on('input', function(){ jQuery(this).data('autofill', 0); });
    if(selectedTemplateId !== '0')
    {
        jQuery('#leadScheduleTemplateId').val(selectedTemplateId);
    }
    applyTemplate(false);

    if(typeof jQuery.fn.datetimepicker === 'function')
    {
        jQuery('#leadScheduleAtPicker').datetimepicker({
            locale: '<?php echo addslashes($configLanguage);?>',
            format: pickerFormat
        });
    }
});
</script>

<div class="page-title">
    <div class="title-env">
        <h1 class="title"><?php echo __('Schedule Lead SMS');?></h1>
    </div>
</div>

<div class="panel">
    <div class="panel-heading">
        <div class="btn-group">
            <a href="<?php echo h($manageUrl);?>" class="btn btn-default"><span class="fa fa-list"></span>&nbsp;<?php echo __('Manage Leads');?></a>
            <?php echo $this->Html->link('<span class="fa fa-comment"></span>&nbsp;'.__('Send Now (Bulk SMS)'), $bulkSmsUrl, array('escape'=>false,'class'=>'btn btn-success'));?>
            <?php echo $this->Html->link('<span class="fa fa-dashboard"></span>&nbsp;'.__('CRM Dashboard'), array('controller'=>'Leads','action'=>'dashboard'), array('escape'=>false,'class'=>'btn btn-primary'));?>
        </div>
    </div>
    <div class="panel-body">
        <?php echo $this->Session->flash();?>

        <div class="alert <?php echo !empty($gatewayConfigured) ? 'alert-success' : 'alert-warning';?>">
            <strong><?php echo __('Send Using');?></strong>: <?php echo h((string)$gatewaySourceLabel);?>
            <?php if(trim((string)$gatewayMetaLabel) !== ''){ ?>
                <span style="margin-left:8px;"><?php echo h((string)$gatewayMetaLabel);?></span>
            <?php } ?>
            <span style="margin-left:8px;" class="label label-<?php echo ((string)$gatewaySourceMode === 'user') ? 'primary' : 'default';?>">
                <?php echo ((string)$gatewaySourceMode === 'user') ? __('CRM Selected') : __('Global Selected');?>
            </span>
            <?php if(empty($gatewayConfigured)){ ?>
                <div style="margin-top:6px;"><?php echo __('Gateway looks incomplete. Please update CRM SMS settings before sending.');?></div>
            <?php } ?>
        </div>

        <?php if(empty($selectedLeadIds)){ ?>
            <div class="alert alert-warning">
                <?php echo __('No leads selected. Please select leads from Manage Leads, then use Schedule SMS.');?>
            </div>
        <?php } else { ?>
            <?php echo $this->Form->create('Lead', array('url' => array('controller'=>'Leads','action'=>'schedulesms'), 'class' => 'form-horizontal'));?>
            <?php foreach((array)$selectedLeadIds as $k => $leadId){ echo $this->Form->hidden('id.'.$k, array('value' => (int)$leadId)); } ?>

            <div class="form-group">
                <label class="col-sm-2 control-label"><?php echo __('Selected Leads');?></label>
                <div class="col-sm-10">
                    <p class="form-control-static"><strong><?php echo (int)count((array)$leadRows);?></strong></p>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-2 control-label"><?php echo __('Select Template');?></label>
                <div class="col-sm-10">
                    <?php echo $this->Form->select('template_id', (array)$templateOptions, array('id'=>'leadScheduleTemplateId','label'=>false,'class'=>'form-control','empty'=>__('Please Select'),'div'=>false,'selected'=>(int)$selectedTemplateId));?>
                    <div style="margin-top:8px;">
                        <button type="submit" name="submit_action" value="save_default" class="btn btn-default">
                            <span class="fa fa-bookmark"></span>&nbsp;<?php echo __('Save as My Default Template');?>
                        </button>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-2 control-label"><?php echo __('Message');?></label>
                <div class="col-sm-10">
                    <?php echo $this->Form->textarea('message', array('id'=>'LeadMessage','label'=>false,'class'=>'form-control','div'=>false,'rows'=>6,'required'=>true));?>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-2 control-label"><?php echo __('Schedule At');?></label>
                <div class="col-sm-4">
                    <div class="input-group date" id="leadScheduleAtPicker">
                        <?php echo $this->Form->input('schedule_at', array('label'=>false,'class'=>'form-control','div'=>false,'required'=>true,'placeholder'=>__('Select Date & Time')));?>
                        <span class="input-group-addon"><span class="fa fa-calendar"></span></span>
                    </div>
                </div>
                <div class="col-sm-6">
                    <p class="form-control-static text-muted"><?php echo __('Use your server/local timezone while selecting date and time.');?></p>
                </div>
            </div>

            <div class="form-group">
                <div class="col-sm-offset-2 col-sm-10">
                    <button type="submit" name="submit_action" value="schedule" class="btn btn-success"><span class="fa fa-clock-o"></span>&nbsp;<?php echo __('Schedule SMS');?></button>
                    <a href="<?php echo h($manageUrl);?>" class="btn btn-danger"><span class="fa fa-close"></span>&nbsp;<?php echo __('Close');?></a>
                </div>
            </div>
            <?php echo $this->Form->end();?>

            <hr />
            <h4><?php echo __('Recipients');?></h4>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th><?php echo __('#');?></th>
                            <th><?php echo __('Lead Name');?></th>
                            <th><?php echo __('Mobile');?></th>
                            <th><?php echo __('Agency');?></th>
                            <th><?php echo __('Assigned User');?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $sr = 1; foreach((array)$leadRows as $row){ ?>
                        <?php
                            $leadName = trim((string)(isset($row['Lead']['lead_name']) ? $row['Lead']['lead_name'] : ''));
                            if($leadName === '')
                            {
                                $leadName = trim((string)(isset($row['Client']['name']) ? $row['Client']['name'] : ''));
                            }
                            $leadMobile = trim((string)(isset($row['Lead']['lead_mobile']) ? $row['Lead']['lead_mobile'] : ''));
                            if($leadMobile === '')
                            {
                                $leadMobile = trim((string)(isset($row['Client']['mobile']) ? $row['Client']['mobile'] : ''));
                            }
                            if($leadMobile === '')
                            {
                                $leadMobile = trim((string)(isset($row['Client']['phone']) ? $row['Client']['phone'] : ''));
                            }
                            $leadCallNo = preg_replace('/[^0-9\+]/', '', (string)$leadMobile);
                            $agencyName = trim((string)(isset($row['Project']['name']) ? $row['Project']['name'] : ''));
                            $agentName = trim((string)(isset($row['User']['name']) ? $row['User']['name'] : ''));
                        ?>
                        <tr>
                            <td><?php echo (int)$sr++;?></td>
                            <td><?php echo h($leadName !== '' ? $leadName : '-');?></td>
                            <td>
                                <?php
                                if($leadMobile === '')
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
                            </td>
                            <td><?php echo h($agencyName !== '' ? $agencyName : '-');?></td>
                            <td><?php echo h($agentName !== '' ? $agentName : '-');?></td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        <?php } ?>

        <hr />
        <h4><?php echo __('Recent Scheduled SMS');?></h4>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th><?php echo __('#');?></th>
                        <th><?php echo __('Lead');?></th>
                        <th><?php echo __('Mobile');?></th>
                        <th><?php echo __('Scheduled At');?></th>
                        <th><?php echo __('Status');?></th>
                        <th><?php echo __('Sent At');?></th>
                        <th><?php echo __('Error');?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($recentRows)){ ?>
                        <tr><td colspan="7" class="text-center"><?php echo __('No schedule records found.');?></td></tr>
                    <?php } ?>
                    <?php $sr = 1; foreach((array)$recentRows as $row){ ?>
                    <?php
                        $schedule = isset($row['LeadSmsSchedule']) ? (array)$row['LeadSmsSchedule'] : array();
                        $leadId = !empty($schedule['lead_id']) ? (int)$schedule['lead_id'] : 0;
                        $leadTitle = isset($leadNameMap[$leadId]) ? (string)$leadNameMap[$leadId] : ('#'.$leadId);
                        $status = strtolower(trim((string)(isset($schedule['status']) ? $schedule['status'] : 'queued')));
                        $statusClass = 'default';
                        if($status === 'sent')$statusClass = 'success';
                        elseif($status === 'failed')$statusClass = 'danger';
                        elseif($status === 'processing')$statusClass = 'warning';
                    ?>
                    <tr>
                        <td><?php echo (int)$sr++;?></td>
                        <td><?php echo h($leadTitle);?></td>
                        <td><?php echo h(isset($schedule['mobile']) ? (string)$schedule['mobile'] : '-');?></td>
                        <td><?php echo !empty($schedule['scheduled_at']) ? $this->Time->format($sysDay.$dateSep.$sysMonth.$dateSep.$sysYear.$dateGap.$sysHour.$timeSep.$sysMin.$dateGap.$sysMer, $schedule['scheduled_at']) : '-';?></td>
                        <td><span class="label label-<?php echo h($statusClass);?>"><?php echo h(ucfirst($status));?></span></td>
                        <td><?php echo !empty($schedule['sent_at']) ? $this->Time->format($sysDay.$dateSep.$sysMonth.$dateSep.$sysYear.$dateGap.$sysHour.$timeSep.$sysMin.$dateGap.$sysMer, $schedule['sent_at']) : '-';?></td>
                        <td><?php echo h(!empty($schedule['last_error']) ? (string)$schedule['last_error'] : '-');?></td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
