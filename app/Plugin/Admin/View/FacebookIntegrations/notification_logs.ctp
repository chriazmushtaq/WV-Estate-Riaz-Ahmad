<div class="page-title">
    <div class="title-env">
        <h1 class="title"><i class="fa fa-envelope-o text-primary"></i> <?php echo __('Facebook & Lead Notification Logs');?></h1>
        <p class="description"><?php echo __('Comprehensive audit trail of all automated & manual notification dispatches (Email & SMS) with 1-click admin resend.');?></p>
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
            <?php echo $this->Html->link('<i class="fa fa-history"></i> '.__('Sync Logs'), array('action'=>'logs'), array('class'=>'btn btn-default', 'escape'=>false));?>
            <?php echo $this->Html->link('<i class="fa fa-envelope-o"></i> '.__('Notification Logs'), array('action'=>'notification_logs'), array('class'=>'btn btn-primary', 'escape'=>false));?>
            <?php echo $this->Html->link('<i class="fa fa-book"></i> '.__('Documentation'), array('action'=>'docs'), array('class'=>'btn btn-default', 'escape'=>false));?>
        </div>

        <div style="display:flex;gap:8px;align-items:center;">
            <?php if(!empty($kpis['failed']) && $kpis['failed'] > 0){ ?>
            <button type="button" id="btnResendAllFailed" class="btn btn-danger" style="font-weight:700;">
                <i class="fa fa-refresh"></i> <?php echo __('Resend All Failed (%d)', $kpis['failed']);?>
            </button>
            <?php } ?>
            <?php echo $this->Html->link('<i class="fa fa-arrow-left"></i> '.__('Back to Leads'), array('controller'=>'leads', 'action'=>'index'), array('class'=>'btn btn-default', 'escape'=>false));?>
        </div>
    </div>

    <div class="panel-body">
        <?php echo $this->Session->flash();?>

        <!-- KPI Summary Cards -->
        <div class="row" style="margin-bottom:20px;">
            <div class="col-sm-3 col-xs-6" style="margin-bottom:10px;">
                <div class="xe-widget xe-counter" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:15px;display:flex;align-items:center;gap:15px;">
                    <div class="xe-icon" style="width:48px;height:48px;border-radius:10px;background:#e0f2fe;color:#0284c7;display:flex;align-items:center;justify-content:center;font-size:22px;">
                        <i class="fa fa-paper-plane"></i>
                    </div>
                    <div class="xe-label">
                        <strong class="num" style="font-size:24px;font-weight:700;color:#0f172a;display:block;"><?php echo number_format(!empty($kpis['total']) ? $kpis['total'] : 0);?></strong>
                        <span style="color:#64748b;font-size:12px;font-weight:600;text-transform:uppercase;"><?php echo __('Total Dispatches');?></span>
                    </div>
                </div>
            </div>

            <div class="col-sm-3 col-xs-6" style="margin-bottom:10px;">
                <div class="xe-widget xe-counter" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:15px;display:flex;align-items:center;gap:15px;">
                    <div class="xe-icon" style="width:48px;height:48px;border-radius:10px;background:#dcfce7;color:#16a34a;display:flex;align-items:center;justify-content:center;font-size:22px;">
                        <i class="fa fa-envelope"></i>
                    </div>
                    <div class="xe-label">
                        <strong class="num" style="font-size:24px;font-weight:700;color:#16a34a;display:block;"><?php echo number_format(!empty($kpis['emails']) ? $kpis['emails'] : 0);?></strong>
                        <span style="color:#64748b;font-size:12px;font-weight:600;text-transform:uppercase;"><?php echo __('Delivered Emails');?></span>
                    </div>
                </div>
            </div>

            <div class="col-sm-3 col-xs-6" style="margin-bottom:10px;">
                <div class="xe-widget xe-counter" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:15px;display:flex;align-items:center;gap:15px;">
                    <div class="xe-icon" style="width:48px;height:48px;border-radius:10px;background:#ede9fe;color:#7c3aed;display:flex;align-items:center;justify-content:center;font-size:22px;">
                        <i class="fa fa-commenting"></i>
                    </div>
                    <div class="xe-label">
                        <strong class="num" style="font-size:24px;font-weight:700;color:#7c3aed;display:block;"><?php echo number_format(!empty($kpis['sms']) ? $kpis['sms'] : 0);?></strong>
                        <span style="color:#64748b;font-size:12px;font-weight:600;text-transform:uppercase;"><?php echo __('Delivered SMS');?></span>
                    </div>
                </div>
            </div>

            <div class="col-sm-3 col-xs-6" style="margin-bottom:10px;">
                <div class="xe-widget xe-counter" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:15px;display:flex;align-items:center;gap:15px;">
                    <div class="xe-icon" style="width:48px;height:48px;border-radius:10px;background:#fee2e2;color:#dc2626;display:flex;align-items:center;justify-content:center;font-size:22px;">
                        <i class="fa fa-exclamation-triangle"></i>
                    </div>
                    <div class="xe-label">
                        <strong class="num" style="font-size:24px;font-weight:700;color:#dc2626;display:block;"><?php echo number_format(!empty($kpis['failed']) ? $kpis['failed'] : 0);?></strong>
                        <span style="color:#64748b;font-size:12px;font-weight:600;text-transform:uppercase;"><?php echo __('Failed / Attention');?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter and Search Toolbar -->
        <div class="well well-sm" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;margin-bottom:20px;">
            <form method="get" action="<?php echo $this->Html->url(array('action'=>'notification_logs'));?>" class="form-inline" style="display:flex;flex-wrap:wrap;gap:10px;align-items:center;">
                <div class="form-group">
                    <input type="text" name="q" value="<?php echo h(!empty($q) ? $q : '');?>" class="form-control input-sm" placeholder="<?php echo __('Search Name, Phone, Email...');?>" style="min-width:200px;border-radius:6px;" />
                </div>

                <div class="form-group">
                    <select name="channel" class="form-control input-sm" style="border-radius:6px;">
                        <option value="all" <?php echo (empty($channel) || $channel === 'all') ? 'selected' : '';?>><?php echo __('All Channels');?></option>
                        <option value="email" <?php echo (!empty($channel) && $channel === 'email') ? 'selected' : '';?>><?php echo __('Email Only');?></option>
                        <option value="sms" <?php echo (!empty($channel) && $channel === 'sms') ? 'selected' : '';?>><?php echo __('SMS Only');?></option>
                    </select>
                </div>

                <div class="form-group">
                    <select name="status" class="form-control input-sm" style="border-radius:6px;">
                        <option value="all" <?php echo (empty($status) || $status === 'all') ? 'selected' : '';?>><?php echo __('All Statuses');?></option>
                        <option value="success" <?php echo (!empty($status) && $status === 'success') ? 'selected' : '';?>><?php echo __('Success Only');?></option>
                        <option value="failed" <?php echo (!empty($status) && $status === 'failed') ? 'selected' : '';?>><?php echo __('Failed Only');?></option>
                    </select>
                </div>

                <div class="form-group">
                    <select name="recipient_type" class="form-control input-sm" style="border-radius:6px;">
                        <option value="all" <?php echo (empty($recipientType) || $recipientType === 'all') ? 'selected' : '';?>><?php echo __('All Recipients');?></option>
                        <option value="agent" <?php echo (!empty($recipientType) && $recipientType === 'agent') ? 'selected' : '';?>><?php echo __('Agent');?></option>
                        <option value="admin" <?php echo (!empty($recipientType) && $recipientType === 'admin') ? 'selected' : '';?>><?php echo __('Agency Admin');?></option>
                        <option value="client" <?php echo (!empty($recipientType) && $recipientType === 'client') ? 'selected' : '';?>><?php echo __('Lead Client');?></option>
                        <option value="all_agents" <?php echo (!empty($recipientType) && $recipientType === 'all_agents') ? 'selected' : '';?>><?php echo __('All Active Agents');?></option>
                    </select>
                </div>

                <button type="submit" class="btn btn-sm btn-primary" style="font-weight:600;border-radius:6px;">
                    <i class="fa fa-filter"></i> <?php echo __('Filter Logs');?>
                </button>
                <?php if(!empty($q) || (!empty($channel) && $channel !== 'all') || (!empty($status) && $status !== 'all') || (!empty($recipientType) && $recipientType !== 'all')){ ?>
                <a href="<?php echo $this->Html->url(array('action'=>'notification_logs'));?>" class="btn btn-sm btn-default" style="border-radius:6px;">
                    <i class="fa fa-times"></i> <?php echo __('Reset');?>
                </a>
                <?php } ?>
            </form>
        </div>

        <!-- Notification Logs Table -->
        <div class="panel panel-default" style="border-radius:10px;overflow:hidden;box-shadow:0 2px 10px rgba(0,0,0,0.05);">
            <div class="panel-heading" style="background:#f8fafc;font-weight:700;display:flex;justify-content:space-between;align-items:center;">
                <span><i class="fa fa-list text-primary"></i> <?php echo __('Delivery Log Records');?></span>
                <span class="text-muted" style="font-size:12px;font-weight:normal;">
                    <i class="fa fa-info-circle text-info"></i> <?php echo __('Click "Resend" on any row to instantly re-dispatch that notification.');?>
                </span>
            </div>
            <div class="table-responsive">
                <table class="table table-striped table-bordered table-hover" style="margin-bottom:0;vertical-align:middle;">
                    <thead>
                        <tr style="background:#f1f5f9;color:#334155;font-size:12px;">
                            <th style="width:50px;"><?php echo __('#');?></th>
                            <th><?php echo __('Lead Details');?></th>
                            <th><?php echo __('Recipient & Destination');?></th>
                            <th style="text-align:center;width:90px;"><?php echo __('Channel');?></th>
                            <th style="text-align:center;width:110px;"><?php echo __('Trigger');?></th>
                            <th style="text-align:center;width:100px;"><?php echo __('Status');?></th>
                            <th><?php echo __('Subject / Details');?></th>
                            <th style="text-align:center;width:80px;"><?php echo __('Retries');?></th>
                            <th style="width:140px;"><?php echo __('Timestamp');?></th>
                            <th style="width:110px;text-align:center;"><?php echo __('Actions');?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($logs)){ foreach($logs as $l){ 
                            $logId = (int)$l['FacebookNotificationLog']['id'];
                            $leadId = !empty($l['FacebookNotificationLog']['lead_id']) ? (int)$l['FacebookNotificationLog']['lead_id'] : 0;
                            $ch = strtolower(trim((string)$l['FacebookNotificationLog']['channel']));
                            $st = strtolower(trim((string)$l['FacebookNotificationLog']['status']));
                            $rType = strtolower(trim((string)$l['FacebookNotificationLog']['recipient_type']));
                            $resendCount = (int)$l['FacebookNotificationLog']['resend_count'];
                        ?>
                        <tr id="notif-log-row-<?php echo $logId;?>">
                            <td><?php echo $logId;?></td>
                            <td>
                                <?php if($leadId > 0){ ?>
                                    <a href="<?php echo $this->Html->url(array('controller'=>'leads', 'action'=>'leadview', $leadId));?>" style="font-weight:700;color:#0284c7;" target="_blank">
                                        #<?php echo $leadId;?> - <?php echo h($l['FacebookNotificationLog']['lead_name']);?>
                                    </a>
                                <?php } else { ?>
                                    <strong style="color:#0f172a;"><?php echo h($l['FacebookNotificationLog']['lead_name']);?></strong>
                                <?php } ?>

                                <?php if(!empty($l['FacebookNotificationLog']['lead_phone'])){ ?>
                                    <div style="font-size:11px;color:#64748b;margin-top:2px;">
                                        <i class="fa fa-phone"></i> <?php echo h($l['FacebookNotificationLog']['lead_phone']);?>
                                    </div>
                                <?php } ?>
                                <?php if(!empty($l['FacebookNotificationLog']['lead_email'])){ ?>
                                    <div style="font-size:11px;color:#64748b;">
                                        <i class="fa fa-envelope-o"></i> <?php echo h($l['FacebookNotificationLog']['lead_email']);?>
                                    </div>
                                <?php } ?>
                            </td>

                            <td>
                                <div>
                                    <?php 
                                    if($rType === 'admin'){ 
                                        echo '<span class="badge" style="background:#475569;font-size:10px;">Agency Admin</span> ';
                                    } elseif($rType === 'all_agents'){
                                        echo '<span class="badge" style="background:#0284c7;font-size:10px;">All Active Agents</span> ';
                                    } elseif($rType === 'client'){
                                        echo '<span class="badge" style="background:#16a34a;font-size:10px;">Lead Client</span> ';
                                    } else {
                                        echo '<span class="badge" style="background:#7c3aed;font-size:10px;">Agent</span> ';
                                    }
                                    ?>
                                    <strong style="color:#1e293b;"><?php echo h($l['FacebookNotificationLog']['recipient_name']);?></strong>
                                </div>
                                <div style="font-size:12px;margin-top:3px;font-family:monospace;color:#0369a1;">
                                    <?php echo h($l['FacebookNotificationLog']['recipient_target']);?>
                                </div>
                            </td>

                            <td style="text-align:center;">
                                <?php if($ch === 'email'){ ?>
                                    <span class="label label-info" style="font-size:11px;padding:4px 8px;border-radius:12px;">
                                        <i class="fa fa-envelope"></i> Email
                                    </span>
                                <?php } else { ?>
                                    <span class="label label-success" style="font-size:11px;padding:4px 8px;border-radius:12px;background:#10b981;">
                                        <i class="fa fa-comment"></i> SMS
                                    </span>
                                <?php } ?>
                            </td>

                            <td style="text-align:center;">
                                <?php 
                                $trig = $l['FacebookNotificationLog']['trigger_type'];
                                if($trig === 'auto_sync'){
                                    echo '<span class="label label-default" style="font-size:10px;background:#e2e8f0;color:#334155;">Auto Sync</span>';
                                } elseif($trig === 'manual_single'){
                                    echo '<span class="label label-default" style="font-size:10px;background:#fef3c7;color:#92400e;">1-Click Send</span>';
                                } elseif($trig === 'bulk_action'){
                                    echo '<span class="label label-default" style="font-size:10px;background:#fce7f3;color:#9d174d;">Bulk Action</span>';
                                } else {
                                    echo '<span class="label label-default" style="font-size:10px;background:#dbeafe;color:#1e40af;">Admin Resend</span>';
                                }
                                ?>
                            </td>

                            <td style="text-align:center;" id="notif-status-cell-<?php echo $logId;?>">
                                <?php if($st === 'success'){ ?>
                                    <span class="label label-success" style="font-size:11px;padding:4px 8px;border-radius:12px;">
                                        <i class="fa fa-check-circle"></i> Success
                                    </span>
                                <?php } else { ?>
                                    <span class="label label-danger" style="font-size:11px;padding:4px 8px;border-radius:12px;" title="<?php echo h($l['FacebookNotificationLog']['error_message']);?>">
                                        <i class="fa fa-times-circle"></i> Failed
                                    </span>
                                <?php } ?>
                            </td>

                            <td style="max-width:260px;">
                                <?php if(!empty($l['FacebookNotificationLog']['subject'])){ ?>
                                    <div style="font-weight:600;font-size:12px;color:#0f172a;margin-bottom:2px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?php echo h($l['FacebookNotificationLog']['subject']);?>">
                                        <?php echo h($l['FacebookNotificationLog']['subject']);?>
                                    </div>
                                <?php } ?>
                                <?php if(!empty($l['FacebookNotificationLog']['error_message'])){ ?>
                                    <div style="font-size:11px;color:<?php echo ($st === 'failed') ? '#dc2626' : '#64748b';?>;">
                                        <i class="fa fa-info-circle"></i> <?php echo h($l['FacebookNotificationLog']['error_message']);?>
                                    </div>
                                <?php } ?>
                            </td>

                            <td style="text-align:center;" id="notif-retries-cell-<?php echo $logId;?>">
                                <span class="badge" style="background:<?php echo ($resendCount > 0) ? '#0284c7' : '#94a3b8';?>;font-size:11px;">
                                    <?php echo $resendCount;?>
                                </span>
                            </td>

                            <td style="font-size:11px;color:#64748b;">
                                <?php echo date('d-M-Y h:i A', strtotime($l['FacebookNotificationLog']['created']));?>
                                <?php if(!empty($l['FacebookNotificationLog']['last_resend_at'])){ ?>
                                    <div style="font-size:10px;color:#0284c7;margin-top:2px;">
                                        Resent: <?php echo date('d-M-Y h:i A', strtotime($l['FacebookNotificationLog']['last_resend_at']));?>
                                    </div>
                                <?php } ?>
                            </td>

                            <td style="text-align:center;">
                                <button type="button" class="btn btn-xs btn-primary btn-resend-notification" 
                                        data-log-id="<?php echo $logId;?>" 
                                        data-channel="<?php echo h($ch);?>" 
                                        style="font-weight:600;border-radius:4px;padding:3px 8px;" 
                                        title="<?php echo __('Resend this notification now');?>">
                                    <i class="fa fa-paper-plane"></i> <?php echo __('Resend');?>
                                </button>
                            </td>
                        </tr>
                        <?php }} else { ?>
                        <tr>
                            <td colspan="10" class="text-center" style="padding:40px;color:#94a3b8;">
                                <i class="fa fa-inbox" style="font-size:36px;display:block;margin-bottom:10px;"></i>
                                <?php echo __('No notification logs recorded yet. Automated logs will appear here when Facebook leads arrive or notifications are dispatched.');?>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <div class="panel-footer" style="background:#f8fafc;display:flex;justify-content:space-between;align-items:center;padding:12px 20px;">
                <div class="text-muted" style="font-size:12px;">
                    <?php echo $this->Paginator->counter(array('format' => __('Page {:page} of {:pages}, showing {:current} records out of {:count} total')));?>
                </div>
                <ul class="pagination pagination-sm" style="margin:0;">
                    <?php
                        echo $this->Paginator->prev('« ' . __('Previous'), array('tag' => 'li'), null, array('tag' => 'li', 'class' => 'disabled', 'disabledTag' => 'a'));
                        echo $this->Paginator->numbers(array('separator' => '', 'currentTag' => 'a', 'currentClass' => 'active', 'tag' => 'li', 'first' => 1));
                        echo $this->Paginator->next(__('Next') . ' »', array('tag' => 'li', 'currentClass' => 'disabled'), null, array('tag' => 'li', 'class' => 'disabled', 'disabledTag' => 'a'));
                    ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($){
    // Single 1-Click Resend Notification
    $(document).on('click', '.btn-resend-notification', function(e){
        e.preventDefault();
        var $btn = $(this);
        var logId = $btn.data('log-id');
        var ch = $btn.data('channel') || 'notification';
        if(!logId) return;

        var origHtml = $btn.html();
        $btn.html('<i class="fa fa-spinner fa-spin"></i>').prop('disabled', true);

        var url = '<?php echo $this->Html->url(array("action" => "resend_notification"));?>/' + logId;

        $.ajax({
            url: url,
            type: 'POST',
            dataType: 'json',
            success: function(resp){
                $btn.html(origHtml).prop('disabled', false);
                if(resp && resp.success){
                    // Update row UI
                    $('#notif-status-cell-' + logId).html('<span class="label label-success" style="font-size:11px;padding:4px 8px;border-radius:12px;"><i class="fa fa-check-circle"></i> Success</span>');
                    if(resp.resend_count !== undefined){
                        $('#notif-retries-cell-' + logId).html('<span class="badge" style="background:#0284c7;font-size:11px;">' + resp.resend_count + '</span>');
                    }
                    alert('✓ ' + (resp.message || 'Notification resent successfully!'));
                } else {
                    var err = (resp && resp.message) ? resp.message : 'Failed to resend notification.';
                    alert('✗ ' + err);
                }
            },
            error: function(){
                $btn.html(origHtml).prop('disabled', false);
                alert('Server or network error while resending notification.');
            }
        });
    });

    // Resend All Failed Button
    $('#btnResendAllFailed').on('click', function(e){
        e.preventDefault();
        if(!confirm('<?php echo __("Are you sure you want to retry all failed notifications? This will re-dispatch up to 50 failed notifications.");?>')){
            return false;
        }

        var $btn = $(this);
        var origHtml = $btn.html();
        $btn.html('<i class="fa fa-spinner fa-spin"></i> <?php echo __("Retrying...");?>').prop('disabled', true);

        var url = '<?php echo $this->Html->url(array("action" => "resend_all_failed"));?>';

        $.ajax({
            url: url,
            type: 'POST',
            dataType: 'json',
            success: function(resp){
                $btn.html(origHtml).prop('disabled', false);
                if(resp && resp.success){
                    alert('✓ ' + (resp.message || 'Bulk retry complete!'));
                    window.location.reload();
                } else {
                    alert('✗ ' + ((resp && resp.message) ? resp.message : 'Bulk retry encountered issues.'));
                    window.location.reload();
                }
            },
            error: function(){
                $btn.html(origHtml).prop('disabled', false);
                window.location = url;
            }
        });
    });
});
</script>
