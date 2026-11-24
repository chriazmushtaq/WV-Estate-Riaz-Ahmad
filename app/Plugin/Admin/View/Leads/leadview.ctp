<?php
$leadName = trim((string)$leadRow['Lead']['lead_name'])!=='' ? $leadRow['Lead']['lead_name'] : (!empty($leadRow['Client']['name']) ? $leadRow['Client']['name'] : '');
$leadMobile = trim((string)$leadRow['Lead']['lead_mobile'])!=='' ? $leadRow['Lead']['lead_mobile'] : (!empty($leadRow['Client']['mobile']) ? $leadRow['Client']['mobile'] : '');
$leadCallNo = preg_replace('/[^0-9\+]/', '', (string)$leadMobile);
$leadEmail = trim((string)$leadRow['Lead']['lead_email'])!=='' ? $leadRow['Lead']['lead_email'] : (!empty($leadRow['Client']['email']) ? $leadRow['Client']['email'] : '');
$leadAddress = trim((string)$leadRow['Lead']['lead_address'])!=='' ? $leadRow['Lead']['lead_address'] : (!empty($leadRow['Client']['address']) ? $leadRow['Client']['address'] : '');
$leadTemp = isset($leadRow['Lead']['lead_temperature']) ? strtolower(trim((string)$leadRow['Lead']['lead_temperature'])) : 'warm';
$leadStatusName = !empty($leadRow['Status']['name']) ? $leadRow['Status']['name'] : '-';
$leadStatusColor = !empty($leadRow['Status']['color']) ? $leadRow['Status']['color'] : '#3b82f6';
$historyRows = isset($historyRows) ? (array)$historyRows : array();
$leadDatePickerFormat = (isset($dpFormat) && trim((string)$dpFormat) !== '') ? trim((string)$dpFormat) : 'DD-MMM-YYYY';
$followUpDisplayFormat = trim((isset($dtFormat) ? $dtFormat : 'd-M-Y').' h:i A');

// WhatsApp link
$waClean = preg_replace('/[^0-9]/', '', (string)$leadMobile);
if(strlen($waClean) === 11 && substr($waClean, 0, 1) === '0') $waClean = '92'.substr($waClean, 1);
elseif(strlen($waClean) === 10 && substr($waClean, 0, 1) === '3') $waClean = '92'.$waClean;
$waUrl = '';
if(strlen($waClean) >= 10){
    $waMsg = urlencode("السلام علیکم " . ($leadName !== '' ? $leadName . " صاحب!" : "!") . " White Villas کے حوالے سے آپ کی انکوائری موصول ہوئی تھی۔ کیا ہم آپ کو تفصیلات شیئر کر سکتے ہیں؟");
    $waUrl = 'https://wa.me/'.$waClean.'?text='.$waMsg;
}
?>

<style type="text/css">
.lead-profile-header {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 16px 20px;
    margin-bottom: 16px;
    box-shadow: 0 1px 4px rgba(15, 23, 42, 0.05);
}
.lead-avatar-circle {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: #eff6ff;
    color: #1d4ed8;
    font-size: 18px;
    font-weight: 800;
    line-height: 48px;
    text-align: center;
    border: 2px solid #bfdbfe;
    display: inline-block;
    vertical-align: middle;
}
.lead-title-box {
    display: inline-block;
    vertical-align: middle;
    margin-left: 12px;
}
.lead-title-box h2 {
    margin: 0;
    font-size: 22px;
    font-weight: 800;
    color: #0f172a;
}
.lead-profile-panel {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 16px;
    margin-bottom: 16px;
    box-shadow: 0 1px 4px rgba(15, 23, 42, 0.05);
}
.lead-profile-panel h4 {
    margin-top: 0;
    font-size: 16px;
    font-weight: 800;
    color: #1e293b;
    border-bottom: 2px solid #f1f5f9;
    padding-bottom: 8px;
    margin-bottom: 12px;
}

/* Timeline Styles */
.timeline-container {
    position: relative;
    padding-left: 28px;
    margin-top: 14px;
}
.timeline-container::before {
    content: '';
    position: absolute;
    top: 6px;
    bottom: 6px;
    left: 10px;
    width: 2px;
    background: #e2e8f0;
}
.timeline-item {
    position: relative;
    margin-bottom: 18px;
}
.timeline-dot {
    position: absolute;
    left: -28px;
    top: 4px;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: #ffffff;
    border: 3px solid #3b82f6;
    z-index: 2;
}
.timeline-dot.type-comment { border-color: #059669; background: #ecfdf5; }
.timeline-dot.type-sms { border-color: #f59e0b; background: #fffbeb; }
.timeline-dot.type-email { border-color: #06b6d4; background: #ecfeff; }
.timeline-dot.type-change { border-color: #6366f1; background: #eef2ff; }
.timeline-dot.type-create { border-color: #10b981; background: #d1fae5; }

.timeline-card {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 12px 14px;
    transition: all 0.2s ease;
}
.timeline-card:hover {
    background: #ffffff;
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.08);
    border-color: #cbd5e1;
}
.timeline-meta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    margin-bottom: 6px;
    font-size: 11.5px;
    color: #64748b;
}
.timeline-title {
    font-size: 13px;
    font-weight: 800;
    color: #0f172a;
    margin-bottom: 4px;
}
.timeline-body {
    font-size: 12.5px;
    color: #334155;
    line-height: 1.5;
    white-space: pre-wrap;
    word-break: break-word;
}

/* Quick Note Form */
.quick-note-box {
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    border-radius: 10px;
    padding: 14px;
    margin-bottom: 18px;
}
.quick-note-box h5 {
    margin-top: 0;
    font-weight: 800;
    color: #166534;
    font-size: 14px;
}
</style>

<div class="page-title">
    <div class="title-env">
        <h1 class="title"><?php echo __('Lead Profile & Conversation History');?></h1>
    </div>
</div>

<div class="panel">
    <div class="panel-heading">
        <div class="btn-group">
            <?php echo $this->Html->link('<span class="fa fa-dashboard"></span>&nbsp;'.__('CRM Dashboard'),array('controller'=>'Leads','action'=>'dashboard'),array('escape'=>false,'class'=>'btn btn-default'));?>
            <?php echo $this->Html->link('<span class="fa fa-columns"></span>&nbsp;'.__('Kanban Board'),array('controller'=>'Leads','action'=>'kanban'),array('escape'=>false,'class'=>'btn btn-primary'));?>
            <?php echo $this->Html->link('<span class="fa fa-edit"></span>&nbsp;'.__('Edit Lead'),array('controller'=>'Leads','action'=>'leadedit',$leadRow['Lead']['id']),array('escape'=>false,'class'=>'btn btn-warning'));?>
            <?php echo $this->Html->link('<span class="fa fa-history"></span>&nbsp;'.__('Detailed Audit Log'),array('controller'=>'Leads','action'=>'history',$leadRow['Lead']['id']),array('escape'=>false,'class'=>'btn btn-info'));?>
            <?php echo $this->Html->link('<span class="fa fa-user-plus"></span>&nbsp;'.__('Convert To Deal / Client'),array('controller'=>'Leads','action'=>'convertcontact',$leadRow['Lead']['id']),array('escape'=>false,'class'=>'btn btn-success','onclick'=>'return confirm("Convert this lead to client contact?");'));?>
        </div>
    </div>
    <div class="panel-body">
        <?php echo $this->Session->flash();?>

        <!-- Lead Profile Header Card -->
        <div class="lead-profile-header">
            <div class="row">
                <div class="col-md-7 col-xs-12">
                    <div class="lead-avatar-circle">
                        <?php echo mb_substr($leadName !== '' ? $leadName : 'L', 0, 1, 'UTF-8');?>
                    </div>
                    <div class="lead-title-box">
                        <h2><?php echo h($leadName);?> <small style="font-size:13px;color:#64748b;">#<?php echo (int)$leadRow['Lead']['id'];?></small></h2>
                        <div style="margin-top:4px;">
                            <span class="badge" style="background-color:<?php echo h($leadStatusColor);?>;font-size:11px;padding:3px 8px;"><?php echo h($leadStatusName);?></span>
                            <?php if($leadTemp === 'hot'){ ?>
                                <span class="badge" style="background:#ef4444;font-size:11px;padding:3px 8px;">🔥 Hot Lead</span>
                            <?php } elseif($leadTemp === 'cold'){ ?>
                                <span class="badge" style="background:#06b6d4;font-size:11px;padding:3px 8px;">❄️ Cold Lead</span>
                            <?php } else { ?>
                                <span class="badge" style="background:#f59e0b;font-size:11px;padding:3px 8px;">⚡ Warm Lead</span>
                            <?php } ?>
                            <?php if(!empty($leadRow['Lead']['lead_source'])){ ?>
                                <span class="badge" style="background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;font-size:11px;padding:3px 8px;"><i class="fa fa-bullhorn"></i> <?php echo h($leadRow['Lead']['lead_source']);?></span>
                            <?php } ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-5 col-xs-12 text-right" style="padding-top:8px;">
                    <div class="btn-group" style="margin-right:4px;">
                        <button type="button" class="btn btn-success btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="background:#25D366;border-color:#25D366;font-weight:700;">
                            <span class="fa fa-whatsapp"></span> WhatsApp <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-right">
                            <li><a href="javascript:void(0);" onclick="quickLeadbotViewDispatch('whatsapp');"><i class="fa fa-bolt text-success"></i> <?php echo __('Send via LeadBot Gateway API');?></a></li>
                            <?php if($waUrl !== ''){ ?>
                            <li><a href="<?php echo h($waUrl);?>" target="_blank"><i class="fa fa-external-link"></i> <?php echo __('Open in Web WhatsApp');?></a></li>
                            <?php } ?>
                        </ul>
                    </div>
                    <button type="button" class="btn btn-info btn-sm" onclick="quickLeadbotViewDispatch('sms');" style="font-weight:700;margin-right:4px;">
                        <span class="fa fa-comment"></span> <?php echo __('Send SMS (Gateway)');?>
                    </button>
                    <?php if(trim((string)$leadCallNo) !== ''){ ?>
                        <a href="tel:<?php echo h($leadCallNo);?>" class="btn btn-primary btn-sm" style="font-weight:700;"><span class="fa fa-phone"></span> Call</a>
                    <?php } ?>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Left Column: Lead Info Details -->
            <div class="col-md-5">
                <div class="lead-profile-panel">
                    <h4><span class="fa fa-user"></span> <?php echo __('Lead Details');?></h4>
                    <table class="table table-bordered table-striped" style="margin-bottom:0;font-size:12.5px;">
                        <tr><th style="width:38%;"><?php echo __('Mobile #');?></th><td>
                            <?php if(trim((string)$leadCallNo) !== ''){ ?>
                                <a href="tel:<?php echo h($leadCallNo);?>" style="font-weight:800;color:#2563eb;"><?php echo h($leadMobile);?></a>
                            <?php } else { echo '-'; } ?>
                        </td></tr>
                        <tr><th><?php echo __('Email');?></th><td><?php echo trim((string)$leadEmail)!=='' ? h($leadEmail) : '-';?></td></tr>
                        <tr><th><?php echo __('City / Location');?></th><td><?php echo !empty($leadRow['Lead']['lead_city']) ? h($leadRow['Lead']['lead_city']) : '-';?></td></tr>
                        <tr><th><?php echo __('Budget');?></th><td><?php echo !empty($leadRow['Lead']['lead_budget']) && (float)$leadRow['Lead']['lead_budget'] > 0 ? 'Rs. '.number_format((float)$leadRow['Lead']['lead_budget']) : '-';?></td></tr>
                        <tr><th><?php echo __('Address');?></th><td><?php echo trim((string)$leadAddress)!=='' ? h($leadAddress) : '-';?></td></tr>
                        <tr><th><?php echo __('Agency');?></th><td><?php echo !empty($leadRow['Project']['name']) ? h($leadRow['Project']['name']) : '-';?></td></tr>
                        <tr><th><?php echo __('Assigned Agent');?></th><td><?php echo !empty($leadRow['User']['name']) ? h($leadRow['User']['name']) : '-';?></td></tr>
                        <tr><th><?php echo __('Follow-up Date');?></th><td>
                            <?php
                            if(!empty($leadRow['Lead']['follow_up']) && $leadRow['Lead']['follow_up'] !== '0000-00-00 00:00:00')
                            {
                                $fuTs = strtotime($leadRow['Lead']['follow_up']);
                                $fuStr = $this->Time->format($sysDay.$dateSep.$sysMonth.$dateSep.$sysYear.$dateGap.$sysHour.$timeSep.$sysMin.$dateGap.$sysMer,$leadRow['Lead']['follow_up']);
                                if($fuTs < time())
                                {
                                    echo '<span class="text-danger" style="font-weight:800;"><span class="fa fa-exclamation-triangle"></span> ' . $fuStr . ' (Overdue)</span>';
                                }
                                else
                                {
                                    echo '<span class="text-success" style="font-weight:700;"><span class="fa fa-calendar-check-o"></span> ' . $fuStr . '</span>';
                                }
                            }
                            else
                            {
                                echo '-';
                            }
                            ?>
                        </td></tr>
                        <tr><th><?php echo __('Last Contact Date');?></th><td><?php echo (!empty($leadRow['Lead']['last_contact_date']) && $leadRow['Lead']['last_contact_date'] !== '0000-00-00') ? $this->Time->format($sysDay.$dateSep.$sysMonth.$dateSep.$sysYear,$leadRow['Lead']['last_contact_date']) : '-';?></td></tr>
                        <tr><th><?php echo __('Property');?></th><td><?php echo !empty($leadRow['Property']['name']) ? h($leadRow['Property']['name']) : '-';?></td></tr>
                        <tr><th><?php echo __('Customer Contact');?></th>
                            <td>
                                <?php if(!empty($leadRow['Client']['id'])){ ?>
                                    <strong><?php echo h($leadRow['Client']['name']);?></strong>
                                    <?php echo $this->Html->link(__('Open Profile'),array('controller'=>'Contacts','action'=>'view',$leadRow['Client']['id']),array('class'=>'btn btn-xs btn-default','style'=>'margin-left:6px;'));?>
                                <?php } else { echo '<span class="text-muted">Not converted yet</span>'; } ?>
                            </td>
                        </tr>
                    </table>
                </div>

                <?php if(!empty($leadRow['Lead']['remarks'])){ ?>
                <div class="lead-profile-panel">
                    <h4><span class="fa fa-sticky-note-o"></span> <?php echo __('Master Remarks');?></h4>
                    <div style="background:#f8fafc;border:1px solid #e2e8f0;padding:10px;border-radius:6px;font-size:12.5px;color:#334155;white-space:pre-wrap;line-height:1.5;">
                        <?php echo h($leadRow['Lead']['remarks']);?>
                    </div>
                </div>
                <?php } ?>
            </div>

            <!-- Right Column: Conversation Timeline & Quick Note -->
            <div class="col-md-7">
                <!-- Quick Note & Follow-up Logger -->
                <div class="quick-note-box">
                    <h5><span class="fa fa-plus-circle"></span> <?php echo __('Log New Conversation &amp; Follow-up');?></h5>
                    <form id="leadViewQuickNoteForm" method="post" action="<?php echo $this->Html->url(array('controller'=>'Leads','action'=>'quicklog'));?>">
                        <input type="hidden" name="id" value="<?php echo (int)$leadRow['Lead']['id'];?>">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group" style="margin-bottom:8px;">
                                    <label style="font-size:11.5px;font-weight:700;margin-bottom:3px;">Update Status</label>
                                    <select name="status_id" class="form-control input-sm">
                                        <?php foreach((array)$statusOptions as $stId => $stName){ ?>
                                            <option value="<?php echo (int)$stId;?>" <?php if(!empty($leadRow['Lead']['status_id']) && (int)$leadRow['Lead']['status_id']===(int)$stId){ echo 'selected="selected"'; }?>><?php echo h($stName);?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group" style="margin-bottom:8px;">
                                    <label style="font-size:11.5px;font-weight:700;margin-bottom:3px;"><?php echo __('Next Follow-up Date');?></label>
                                    <div class="input-group">
                                        <input type="text" id="leadFollowUpDate" name="follow_up" class="form-control input-sm datetimepicker" placeholder="<?php echo !empty($dpFormat) ? $dpFormat : 'DD-MMM-YYYY';?> hh:mm A" autocomplete="off">
                                        <span class="input-group-addon" style="cursor:pointer;" onclick="jQuery('#leadFollowUpDate').focus();"><i class="fa fa-calendar"></i></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group" style="margin-bottom:8px;">
                            <label style="font-size:11.5px;font-weight:700;margin-bottom:3px;">Conversation Notes &amp; Client Response</label>
                            <div style="margin-bottom:5px;">
                                <span class="label label-default" style="cursor:pointer;padding:3px 6px;margin-right:3px;" onclick="addLeadViewRemark('Call Connected - Client Interested')">Connected</span>
                                <span class="label label-default" style="cursor:pointer;padding:3px 6px;margin-right:3px;" onclick="addLeadViewRemark('Call Not Attended / Busy')">Not Attended</span>
                                <span class="label label-default" style="cursor:pointer;padding:3px 6px;margin-right:3px;" onclick="addLeadViewRemark('Site Visit Scheduled for this Weekend')">Site Visit</span>
                                <span class="label label-default" style="cursor:pointer;padding:3px 6px;margin-right:3px;" onclick="addLeadViewRemark('Sent Payment Plan on WhatsApp')">Sent WhatsApp</span>
                            </div>
                            <textarea name="remarks" id="leadViewRemarks" class="form-control" rows="2" placeholder="<?php echo __('Enter call notes or click preset chips above...');?>"></textarea>
                        </div>
                        <button type="submit" id="saveQuickNoteBtn" class="btn btn-success btn-sm" style="font-weight:700;"><span class="fa fa-check"></span> <?php echo __('Save Conversation &amp; Follow-up');?></button>
                    </form>
                </div>

                <!-- Conversation History Timeline -->
                <div class="lead-profile-panel">
                    <h4><span class="fa fa-comments-o"></span> <?php echo __('Conversation &amp; Activity Timeline');?> (<?php echo count($historyRows);?> <?php echo __('Events');?>)</h4>
                    
                    <?php if(empty($historyRows)){ ?>
                        <div class="text-center text-muted" style="padding:30px 10px;">
                            <span class="fa fa-history fa-2x"></span>
                            <p style="margin-top:8px;font-size:13px;"><?php echo __('No conversation history recorded yet.');?></p>
                        </div>
                    <?php } else { ?>
                        <div class="timeline-container">
                            <?php foreach($historyRows as $hRow){
                                $entry = $hRow['LeadHistory'];
                                $createdFormatted = '-';
                                if(!empty($entry['created']) && $entry['created'] !== '0000-00-00 00:00:00') {
                                    $createdFormatted = (!empty($sysDay) && !empty($sysMonth) && !empty($sysYear))
                                        ? $this->Time->format($sysDay.$dateSep.$sysMonth.$dateSep.$sysYear.$dateGap.$sysHour.$timeSep.$sysMin.$dateGap.$sysMer, $entry['created'])
                                        : date('d-M-Y h:i A', strtotime($entry['created']));
                                }
                                $actorName = !empty($entry['actor_name']) ? $entry['actor_name'] : 'Agent / Staff';
                                $title = !empty($entry['title']) ? $entry['title'] : __('Lead Update');
                                $messageBody = !empty($entry['message_body']) ? $entry['message_body'] : (!empty($entry['new_value']) ? $entry['new_value'] : '');
                            ?>
                            <div class="timeline-item">
                                <div class="timeline-dot type-<?php echo h($eType);?>"></div>
                                <div class="timeline-card">
                                    <div class="timeline-meta">
                                        <span><i class="fa fa-clock-o"></i> <?php echo h($createdFormatted);?></span>
                                        <span><i class="fa fa-user"></i> <strong><?php echo h($actorName);?></strong> <?php if(!empty($entry['actor_role'])){ echo '('.h($entry['actor_role']).')'; }?></span>
                                    </div>
                                    <div class="timeline-title">
                                        <?php if($eType === 'comment'){ ?>
                                            <span class="label label-success" style="font-size:10px;padding:2px 5px;"><i class="fa fa-comment"></i> <?php echo __('Note / Remark');?></span>
                                        <?php } elseif($eType === 'sms'){ ?>
                                            <span class="label label-warning" style="font-size:10px;padding:2px 5px;"><i class="fa fa-mobile"></i> <?php echo __('SMS Sent');?></span>
                                        <?php } elseif($eType === 'email'){ ?>
                                            <span class="label label-info" style="font-size:10px;padding:2px 5px;"><i class="fa fa-envelope"></i> <?php echo __('Email Sent');?></span>
                                        <?php } elseif($eType === 'create'){ ?>
                                            <span class="label label-primary" style="font-size:10px;padding:2px 5px;"><i class="fa fa-plus-circle"></i> <?php echo __('Created');?></span>
                                        <?php } else { ?>
                                            <span class="label label-default" style="font-size:10px;padding:2px 5px;"><i class="fa fa-refresh"></i> <?php echo __('Status / Follow-up');?></span>
                                        <?php } ?>
                                        &nbsp;<?php echo h($title);?>
                                    </div>
                                    <?php if($messageBody !== ''){ ?>
                                        <div class="timeline-body"><?php echo h($messageBody);?></div>
                                    <?php } ?>
                                </div>
                            </div>
                            <?php } ?>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($){
    if(typeof $.fn.datetimepicker === 'function'){
        $('#leadFollowUpDate').datetimepicker({
            locale: '<?php echo !empty($configLanguage) ? $configLanguage : "en";?>',
            format: '<?php echo $leadDatePickerFormat;?> hh:mm A',
            useCurrent: false,
            showTodayButton: true,
            showClear: true
        });
    }
});

function addLeadViewRemark(text) {
    var $ta = $('#leadViewRemarks');
    var cur = $.trim($ta.val());
    if (cur !== '') {
        $ta.val(cur + ' - ' + text);
    } else {
        $ta.val(text);
    }
}

$('#leadViewQuickNoteForm').on('submit', function(e){
    e.preventDefault();
    var $form = $(this);
    var $btn = $('#saveQuickNoteBtn');
    $btn.prop('disabled', true).html('<span class="fa fa-spinner fa-spin"></span> Saving...');

    $.ajax({
        url: $form.attr('action'),
        type: 'POST',
        data: $form.serialize(),
        dataType: 'json',
        success: function(resp){
            $btn.prop('disabled', false).html('<span class="fa fa-check"></span> Save Conversation &amp; Follow-up');
            if(resp && resp.status === 'success'){
                alert('Conversation note & follow-up saved successfully!');
                window.location.reload();
            } else {
                alert((resp && resp.message) ? resp.message : 'Error saving conversation note.');
            }
        },
        error: function(xhr){
            $btn.prop('disabled', false).html('<span class="fa fa-check"></span> Save Conversation &amp; Follow-up');
            var errMsg = 'Server error while saving conversation note.';
            if(xhr && xhr.responseJSON && xhr.responseJSON.message){
                errMsg = xhr.responseJSON.message;
            }
            alert(errMsg);
        }
    });
});

function quickLeadbotViewDispatch(channel) {
    var leadId = <?php echo (int)$leadRow['Lead']['id'];?>;
    var leadName = <?php echo json_encode(!empty($leadRow['Lead']['lead_name']) ? $leadRow['Lead']['lead_name'] : 'Lead Client');?>;
    var promptMsg = prompt('<?php echo __("Enter message to send via LeadBot ");?>' + (channel === 'whatsapp' ? 'WhatsApp' : 'Android SMS') + ' (or leave blank to use default template):', '');
    if (promptMsg === null) return;

    var btnText = channel === 'whatsapp' ? 'WhatsApp' : 'SMS';
    $.ajax({
        url: '<?php echo $this->Html->url(array('controller'=>'Leads','action'=>'quick_leadbot_dispatch'));?>',
        type: 'POST',
        dataType: 'json',
        data: {
            lead_id: leadId,
            channel: channel,
            message: promptMsg,
            event_key: 'client_welcome'
        }
    }).done(function(res){
        if (res && res.success) {
            alert(res.message || (btnText + ' sent successfully!'));
            window.location.reload();
        } else {
            alert('Failed: ' + (res && res.message ? res.message : 'Unknown error'));
        }
    }).fail(function(xhr){
        alert('Server or network error sending ' + btnText);
    });
}
</script>