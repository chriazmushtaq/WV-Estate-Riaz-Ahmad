<?php
if(empty($allAgents)) {
    $rawUsers = ClassRegistry::init('User')->find('all', array(
        'conditions' => array('User.status' => 'Active'),
        'fields' => array('User.id', 'User.name', 'User.project_id'),
        'order' => array('User.name' => 'ASC'),
        'recursive' => -1
    ));
    $rawProjects = ClassRegistry::init('Project')->find('list', array('fields' => array('Project.id', 'Project.name')));
    $allAgents = array();
    foreach($rawUsers as $ru) {
        $uId = (int)$ru['User']['id'];
        $uName = trim((string)$ru['User']['name']);
        $pId = !empty($ru['User']['project_id']) ? (int)$ru['User']['project_id'] : 0;
        $agName = !empty($rawProjects[$pId]) ? $rawProjects[$pId] : 'White Villas';
        $allAgents[$uId] = $uName . ' | ' . $agName;
    }
}
?>
<div class="page-title">
    <div class="title-env">
        <h1 class="title"><i class="fa fa-history text-primary"></i> <?php echo __('Facebook Lead Ingestion & Webhook Logs');?></h1>
        <p class="description"><?php echo __('Complete real-time audit trail of all leads ingested from Facebook Lead Ads.');?></p>
    </div>
</div>

<div class="panel">
    <div class="panel-heading" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
        <div class="btn-group" style="display:flex;flex-wrap:wrap;gap:4px;">
            <?php echo $this->Html->link('<i class="fa fa-dashboard"></i> '.__('Overview'), array('action'=>'index'), array('class'=>'btn btn-default', 'escape'=>false));?>
            <?php echo $this->Html->link('<i class="fa fa-cogs"></i> '.__('API Accounts'), array('action'=>'settings'), array('class'=>'btn btn-default', 'escape'=>false));?>
            <?php echo $this->Html->link('<i class="fa fa-users"></i> '.__('Agent Looper'), array('action'=>'agents'), array('class'=>'btn btn-default', 'escape'=>false));?>
            <?php echo $this->Html->link('<i class="fa fa-list-alt"></i> '.__('Form Mapping'), array('action'=>'forms'), array('class'=>'btn btn-default', 'escape'=>false));?>
            <?php echo $this->Html->link('<i class="fa fa-bell"></i> '.__('Notifications'), array('action'=>'notifications'), array('class'=>'btn btn-default', 'escape'=>false));?>
            <?php echo $this->Html->link('<i class="fa fa-whatsapp"></i> '.__('WhatsApp API'), array('action'=>'whatsapp'), array('class'=>'btn btn-default', 'escape'=>false));?>
            <?php echo $this->Html->link('<i class="fa fa-history"></i> '.__('Sync Logs'), array('action'=>'logs'), array('class'=>'btn btn-primary', 'escape'=>false));?>
            <?php echo $this->Html->link('<i class="fa fa-envelope-o"></i> '.__('Notification Logs'), array('action'=>'notification_logs'), array('class'=>'btn btn-default', 'escape'=>false));?>
            <?php echo $this->Html->link('<i class="fa fa-book"></i> '.__('Documentation'), array('action'=>'docs'), array('class'=>'btn btn-default', 'escape'=>false));?>
        </div>

        <div>
            <?php echo $this->Html->link('<i class="fa fa-refresh fa-spin-hover"></i> '.__('Sync All Accounts Now'), array('action'=>'sync_now'), array('class'=>'btn btn-success', 'escape'=>false, 'style'=>'font-weight:700;'));?>
        </div>
    </div>

    <div class="panel-body">
        <?php echo $this->Session->flash();?>

        <div class="panel panel-default" style="border-radius:10px;overflow:hidden;box-shadow:0 2px 10px rgba(0,0,0,0.05);">
            <div class="panel-heading" style="background:#f8fafc;font-weight:700;display:flex;justify-content:space-between;align-items:center;">
                <span><i class="fa fa-list text-primary"></i> <?php echo __('Real-Time Ingestion Logs');?></span>
                <span class="text-muted" style="font-size:12px;font-weight:normal;">
                    <i class="fa fa-info-circle text-info"></i> <?php echo __('Select any active agent to reassign & auto-save instantly.');?>
                </span>
            </div>
            <div class="table-responsive">
                <table class="table table-striped table-bordered table-hover" style="margin-bottom:0;vertical-align:middle;">
                    <thead>
                        <tr style="background:#f1f5f9;color:#334155;">
                            <th><?php echo __('#');?></th>
                            <th><?php echo __('Leadgen ID');?></th>
                            <th><?php echo __('Lead Name');?></th>
                            <th><?php echo __('Mobile / Phone');?></th>
                            <th><?php echo __('Email');?></th>
                            <th style="min-width:220px;"><?php echo __('Assigned Agent (Change to Auto-Save)');?></th>
                            <th><?php echo __('Form Name');?></th>
                            <th><?php echo __('Status');?></th>
                            <th><?php echo __('Timestamp');?></th>
                            <th style="width:110px;text-align:center;"><?php echo __('Actions');?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($logs)){ foreach($logs as $l){ 
                            $logId = (int)$l['FacebookLeadLog']['id'];
                            $leadId = !empty($l['FacebookLeadLog']['lead_id']) ? (int)$l['FacebookLeadLog']['lead_id'] : 0;
                            $currentUserId = !empty($l['FacebookLeadLog']['assigned_user_id']) ? (int)$l['FacebookLeadLog']['assigned_user_id'] : 0;
                        ?>
                        <tr id="log-row-<?php echo $logId;?>">
                            <td><?php echo $logId;?></td>
                            <td><code style="font-size:11px;"><?php echo h($l['FacebookLeadLog']['facebook_leadgen_id']);?></code></td>
                            <td><strong style="color:#0f172a;font-size:13px;"><?php echo h($l['FacebookLeadLog']['lead_name']);?></strong></td>
                            <td>
                                <?php if(!empty($l['FacebookLeadLog']['lead_mobile'])){ ?>
                                    <a href="tel:<?php echo h($l['FacebookLeadLog']['lead_mobile']);?>" style="font-weight:700;color:#0284c7;">
                                        <i class="fa fa-phone"></i> <?php echo h($l['FacebookLeadLog']['lead_mobile']);?>
                                    </a>
                                <?php } else { echo '-'; } ?>
                            </td>
                            <td><?php echo h(!empty($l['FacebookLeadLog']['lead_email']) ? $l['FacebookLeadLog']['lead_email'] : '-');?></td>

                            <!-- Full-Width 1-Click Interactive Agent Dropdown -->
                            <td style="min-width:220px;">
                                <div style="display:flex;align-items:center;gap:6px;">
                                    <select class="form-control select-fb-agent" 
                                            data-log-id="<?php echo $logId;?>" 
                                            data-lead-id="<?php echo $leadId;?>" 
                                            style="width:100%;font-size:12px;font-weight:700;height:36px;border-radius:8px;border:1.5px solid <?php echo ($currentUserId > 0) ? '#93c5fd' : '#cbd5e1';?>;background:<?php echo ($currentUserId > 0) ? '#f0f9ff' : '#fff';?>;color:#0f172a;cursor:pointer;">
                                        <option value="0" <?php echo ($currentUserId === 0) ? 'selected' : '';?> style="color:#64748b;font-weight:normal;">-- <?php echo __('Unassigned');?> --</option>
                                        <?php if(!empty($allAgents)){ foreach($allAgents as $uId => $uName){ ?>
                                            <option value="<?php echo (int)$uId;?>" <?php echo ($currentUserId === (int)$uId) ? 'selected' : '';?> style="color:#0f172a;font-weight:600;">
                                                <?php echo h($uName);?>
                                            </option>
                                        <?php }} ?>
                                    </select>
                                    <span class="save-indicator" id="save-status-<?php echo $logId;?>" style="display:none;font-size:11px;font-weight:800;color:#10b981;white-space:nowrap;">
                                        <i class="fa fa-check-circle"></i> Saved
                                    </span>
                                </div>
                            </td>

                            <td><span class="badge" style="background:#f1f5f9;color:#334155;border:1px solid #cbd5e1;"><?php echo h($l['FacebookLeadLog']['form_name']);?></span></td>
                            <td>
                                <?php if($l['FacebookLeadLog']['status'] === 'Success'){ ?>
                                    <span class="label label-success"><i class="fa fa-check"></i> <?php echo __('Success');?></span>
                                <?php } elseif($l['FacebookLeadLog']['status'] === 'Duplicate') { ?>
                                    <span class="label label-warning"><?php echo __('Duplicate');?></span>
                                <?php } else { ?>
                                    <span class="label label-danger"><?php echo __('Error');?></span>
                                <?php } ?>
                            </td>
                            <td style="font-size:12px;color:#64748b;white-space:nowrap;">
                                <?php
                                if(!empty($l['FacebookLeadLog']['created']) && $l['FacebookLeadLog']['created'] !== '0000-00-00 00:00:00') {
                                    $logCreatedFmt = (!empty($sysDay) && !empty($sysMonth) && !empty($sysYear))
                                        ? $this->Time->format($sysDay.$dateSep.$sysMonth.$dateSep.$sysYear.$dateGap.$sysHour.$timeSep.$sysMin.$dateGap.$sysMer, $l['FacebookLeadLog']['created'])
                                        : date('d-M-Y h:i A', strtotime($l['FacebookLeadLog']['created']));
                                    echo '<i class="fa fa-calendar-o"></i> ' . h($logCreatedFmt);
                                } else {
                                    echo '-';
                                }
                                ?>
                            </td>
                            <td style="text-align:center;white-space:nowrap;">
                                <?php if($leadId > 0){ ?>
                                    <div class="btn-group">
                                        <?php echo $this->Html->link('<i class="fa fa-eye"></i>', array('controller'=>'Leads', 'action'=>'leadview', $leadId), array('class'=>'btn btn-xs btn-primary', 'escape'=>false, 'title'=>__('View CRM Lead')));?>
                                        <button type="button" class="btn btn-xs btn-warning dropdown-toggle" data-toggle="dropdown" aria-expanded="false" title="<?php echo __('Send Notification');?>">
                                            <i class="fa fa-bell"></i> <span class="caret"></span>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-right" role="menu" style="min-width:180px;text-align:left;">
                                            <li class="dropdown-header" style="font-size:10px;text-transform:uppercase;color:#94a3b8;padding:3px 15px;font-weight:700;"><i class="fa fa-bell"></i> <?php echo __('Notifications');?></li>
                                            <li><a href="javascript:void(0);" onclick="return send_fb_log_notification(<?php echo (int)$leadId;?>, 'email', this);"><i class="fa fa-envelope text-primary"></i> <?php echo __('Send Email Notification');?></a></li>
                                            <li><a href="javascript:void(0);" onclick="return send_fb_log_notification(<?php echo (int)$leadId;?>, 'sms', this);"><i class="fa fa-comment text-success"></i> <?php echo __('Send SMS Notification');?></a></li>
                                            <li><a href="javascript:void(0);" onclick="return send_fb_log_notification(<?php echo (int)$leadId;?>, 'all', this);"><i class="fa fa-paper-plane text-warning"></i> <strong><?php echo __('Send All (Email + SMS)');?></strong></a></li>
                                        </ul>
                                    </div>
                                <?php } else { echo '-'; } ?>
                            </td>
                        </tr>
                        <?php }} else { ?>
                        <tr>
                            <td colspan="10" class="text-center text-muted" style="padding:30px;">
                                <i class="fa fa-info-circle fa-2x" style="color:#94a3b8;margin-bottom:8px;"></i><br/>
                                <?php echo __('No sync logs recorded yet.');?>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div style="margin-top:15px;">
            <?php echo $this->element('pagination');?>
        </div>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function() {
    $(document).on('change', '.select-fb-agent', function() {
        var selectEl = $(this);
        var logId = selectEl.attr('data-log-id');
        var leadId = selectEl.attr('data-lead-id');
        var newUserId = selectEl.val();
        var indicator = $('#save-status-' + logId);

        // Visual highlight while updating
        selectEl.css({'border-color': '#3b82f6', 'background-color': '#eff6ff'});

        $.ajax({
            url: '<?php echo $this->Html->url(array("plugin" => "admin", "controller" => "facebook_integrations", "action" => "reassign_agent_ajax")); ?>',
            type: 'POST',
            data: {
                log_id: logId,
                lead_id: leadId,
                user_id: newUserId
            },
            dataType: 'json',
            success: function(response) {
                if (response && response.success) {
                    if (parseInt(newUserId) > 0) {
                        selectEl.css({'border-color': '#93c5fd', 'background-color': '#f0f9ff'});
                    } else {
                        selectEl.css({'border-color': '#cbd5e1', 'background-color': '#fff'});
                    }
                    indicator.fadeIn(200).delay(1500).fadeOut(400);
                } else {
                    selectEl.css({'border-color': '#ef4444', 'background-color': '#fef2f2'});
                    alert(response.message || 'Could not update agent.');
                }
            },
            error: function() {
                selectEl.css({'border-color': '#ef4444', 'background-color': '#fef2f2'});
                alert('Connection error while updating agent.');
            }
        });
    });
});

function send_fb_log_notification(leadId, type, el)
{
    type = type || 'all';
    var $btn = $(el);
    var origHtml = $btn.html();
    $btn.html('<i class="fa fa-spinner fa-spin"></i> <?php echo __("Sending...");?>').css('pointer-events', 'none');

    var url = '<?php echo $this->Html->url(array("plugin" => "admin", "controller" => "facebook_integrations", "action" => "send_notification"));?>/' + leadId + '/' + type;

    $.ajax({
        url: url,
        type: 'POST',
        dataType: 'json',
        success: function(resp){
            $btn.html(origHtml).css('pointer-events', '');
            if(resp && resp.success){
                alert('✓ ' + (resp.message || '<?php echo __("Notification dispatched successfully!");?>'));
            } else {
                alert('✗ ' + ((resp && resp.message) ? resp.message : '<?php echo __("Failed to dispatch notification.");?>'));
            }
        },
        error: function(){
            $btn.html(origHtml).css('pointer-events', '');
            alert('<?php echo __("Server error while dispatching notification.");?>');
        }
    });
    return false;
}
</script>