<?php
$isLeadbotDefault = !empty($isLeadbotDefault);
$leadbotSettings = isset($leadbotSettings) ? $leadbotSettings : array();
$isLeadbotConnected = !empty($isLeadbotConnected);
$leadbotTelemetry = isset($leadbotTelemetry) ? $leadbotTelemetry : array();

$selectedSession = !empty($leadbotSettings['selected_wa_session']) ? $leadbotSettings['selected_wa_session'] : 'wa-03391101165';
$selectedDevice = !empty($leadbotSettings['selected_sms_device_id']) ? ('Device #' . $leadbotSettings['selected_sms_device_id']) : 'Device #34';
?>
<div class="page-title">
    <div class="title-env">
        <h1 class="title"><?php echo __('SMS & WhatsApp Settings');?></h1>
    </div>
</div>
<?php echo $this->Session->flash();?>

<div class="col-sm-12">
    <!-- Primary Gateway Selection Panel -->
    <div class="panel <?php echo $isLeadbotDefault ? 'panel-success' : 'panel-default'; ?>" id="panelGatewayEngine" style="border-width: 2px; box-shadow: 0 2px 10px rgba(0,0,0,0.06);">
        <div class="panel-heading" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; padding: 16px 20px;">
            <h3 class="panel-title" style="font-weight: 800; font-size: 16px; margin: 0;">
                <i class="fa fa-exchange text-primary"></i> <?php echo __('Default Messaging Engine (SMS & WhatsApp)');?>
            </h3>
            <span class="badge" id="badgeDefaultStatus" style="background: <?php echo $isLeadbotDefault ? '#10b981' : '#64748b'; ?>; font-size: 12px; font-weight: 800; padding: 6px 14px; text-transform: uppercase;">
                <?php echo $isLeadbotDefault ? __('🚀 LeadBot CRM (Default Engine)') : __('Custom HTTP SMS Gateway'); ?>
            </span>
        </div>
        <div class="panel-body" style="padding: 20px;">
            <div class="row" style="display: flex; align-items: center; flex-wrap: wrap;">
                <div class="col-md-8 col-sm-12">
                    <div id="leadbotStatusAlert" class="alert <?php echo $isLeadbotDefault ? 'alert-success' : 'alert-info'; ?>" style="margin-bottom: 10px; border-radius: 8px;">
                        <h4 style="margin: 0 0 6px 0; font-weight: 800; font-size: 15px;">
                            <?php if ($isLeadbotDefault): ?>
                                <i class="fa fa-check-circle text-success"></i> <?php echo __('LeadBot CRM is currently active as the DEFAULT gateway for SMS & WhatsApp!'); ?>
                            <?php else: ?>
                                <i class="fa fa-info-circle text-info"></i> <?php echo __('Custom HTTP SMS Gateway is currently used for system SMS. Click below to use LeadBot as default.'); ?>
                            <?php endif; ?>
                        </h4>
                        <p style="margin: 0; font-size: 13px; line-height: 1.5;">
                            <?php if ($isLeadbotDefault): ?>
                                <?php echo __('All system SMS (registration, verification, reset password, deal notifications, receipts) are dispatched via LeadBot Android Gateway without per-SMS API fees. WhatsApp alerts are routed via your connected WhatsApp profile.'); ?>
                            <?php else: ?>
                                <?php echo __('Switching to LeadBot allows sending SMS through your connected Android phone SIM and unified WhatsApp routing with live telemetry and logs.'); ?>
                            <?php endif; ?>
                        </p>
                    </div>
                    <div style="font-size: 12px; color: #64748b; display: flex; gap: 14px; flex-wrap: wrap;">
                        <span><i class="fa fa-cloud text-info"></i> <strong>Gateway:</strong> <?php echo !empty($leadbotSettings['gateway_url']) ? h($leadbotSettings['gateway_url']) : 'https://leadbotcrm.com'; ?></span>
                        <span><i class="fa fa-whatsapp text-success"></i> <strong>WhatsApp Session:</strong> <?php echo h($selectedSession); ?></span>
                        <span><i class="fa fa-android text-warning"></i> <strong>Android SMS Bridge:</strong> <?php echo h($selectedDevice); ?></span>
                    </div>
                </div>
                <div class="col-md-4 col-sm-12 text-right" style="margin-top: 10px;">
                    <button type="button" class="btn <?php echo $isLeadbotDefault ? 'btn-warning' : 'btn-success'; ?> btn-lg" id="btnToggleLeadbotDefault" style="font-weight: 800; border-radius: 8px; padding: 12px 24px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); transition: all 0.2s ease;">
                        <i class="fa <?php echo $isLeadbotDefault ? 'fa-undo' : 'fa-rocket'; ?>" id="toggleLeadbotIcon"></i> 
                        <span id="toggleLeadbotBtnText">
                            <?php echo $isLeadbotDefault ? __('Switch to Custom SMS Gateway') : __('Use LeadBot as Default for SMS & WA'); ?>
                        </span>
                    </button>
                    <div style="margin-top: 8px;">
                        <a href="<?php echo $this->Html->url(array('plugin' => 'admin', 'controller' => 'leadbot_gateways', 'action' => 'index')); ?>" class="btn btn-link btn-xs" style="color: #0284c7; font-weight: 700;">
                            <i class="fa fa-sliders"></i> <?php echo __('Manage LeadBot WhatsApp & Android Devices'); ?> &rarr;
                        </a>
                    </div>
                </div>
            </div>
            <div id="toggleDefaultAjaxMsg" style="margin-top: 12px; display: none;"></div>
        </div>
    </div>

    <!-- Custom SMS Gateway Configuration Panel -->
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title"><i class="fa fa-cogs"></i> <?php echo __('Custom HTTP SMS Gateway Configuration (Fallback / Standalone)');?></h3>
        </div>
        <div class="panel-body">
            <?php echo $this->Form->create('Smssetting', array('controller' => 'Smssettings', 'action' => 'index','name'=>'post_req','id'=>'post_req','class'=>'form-horizontal'));?>
                <input type="hidden" name="data[Smssetting][gateway_type]" id="hidden_gateway_type" value="<?php echo $isLeadbotDefault ? 'leadbot' : 'custom'; ?>">
                <input type="hidden" name="data[Smssetting][is_leadbot_default]" id="hidden_is_leadbot_default" value="<?php echo $isLeadbotDefault ? '1' : '0'; ?>">

                <div class="form-group">
                    <label for="api" class="col-sm-2 control-label"><?php echo __('API Link');?></label>
                    <div class="col-sm-4">
                        <?php echo $this->Form->input('api',array('label' => false,'class'=>'form-control','placeholder'=>__('API Link'),'div'=>false));?>
                    </div>
                    <label for="senderid" class="col-sm-2 control-label"><?php echo __('Sender ID');?></label>
                    <div class="col-sm-4">
                        <?php echo $this->Form->input('senderid',array('label' => false,'class'=>'form-control','placeholder'=>__('Sender ID'),'div'=>false));?>
                    </div>
                </div>
                <div class="form-group">
                    <label for="username" class="col-sm-2 control-label"><?php echo __('User Name');?></label>
                    <div class="col-sm-4">
                        <?php echo $this->Form->input('username',array('label' => false,'class'=>'form-control','placeholder'=>__('User Name'),'div'=>false));?>
                    </div>
                    <label for="password" class="col-sm-2 control-label"><?php echo __('Password/API Key');?></label>
                    <div class="col-sm-4">
                        <div class="input-group">
                            <?php echo $this->Form->input('password',array('type'=>'text','id'=>'sms_password_field','label' => false,'class'=>'form-control','placeholder'=>__('Password/API Key'),'div'=>false,'autocomplete'=>'off'));?>
                            <span class="input-group-btn">
                                <button type="button" class="btn btn-default" id="toggleSmsPassBtn" title="<?php echo __('Toggle Visibility');?>"><i class="fa fa-eye" id="toggleSmsPassIcon"></i></button>
                            </span>
                        </div>
                        <small class="text-muted" style="font-size:11px;"><?php echo __('Key is displayed in plain text and securely preserved without bcrypt hashing.');?></small>
                    </div>                        
                </div>
                <div class="form-group">
                    <label for="husername" class="col-sm-2 control-label"><?php echo __('Heading Username');?></label>
                    <div class="col-sm-4">
                        <?php echo $this->Form->input('husername',array('label' => false,'class'=>'form-control','placeholder'=>__('Username field provided by sms gateway'),'div'=>false));?>
                    </div>
                    <label for="hpassword" class="col-sm-2 control-label"><?php echo __('Heading Password');?></label>
                    <div class="col-sm-4">
                        <?php echo $this->Form->input('hpassword',array('label' => false,'class'=>'form-control','placeholder'=>__('Password field provided by sms gateway'),'div'=>false));?>
                    </div>
                </div>
                <div class="form-group">
                    <label for="hmobile" class="col-sm-2 control-label"><?php echo __('Heading Mobile No');?></label>
                    <div class="col-sm-4">
                        <?php echo $this->Form->input('hmobile',array('label' => false,'class'=>'form-control','placeholder'=>__('Mobile No field provided by sms gateway'),'div'=>false));?>
                    </div>
                    <label for="hmessage" class="col-sm-2 control-label"><?php echo __('Heading Message');?></label>
                    <div class="col-sm-4">
                        <?php echo $this->Form->input('hmessage',array('label' => false,'class'=>'form-control','placeholder'=>__('Message field provided by sms gateway'),'div'=>false));?>
                    </div>
                </div>
                <div class="form-group">
                    <label for="hsenderid" class="col-sm-2 control-label"><?php echo __('Heading Sender Id');?></label>
                    <div class="col-sm-4">
                        <?php echo $this->Form->input('hsenderid',array('label' => false,'class'=>'form-control','placeholder'=>__('Sender Id field provided by sms gateway'),'div'=>false));?>
                    </div>
                    <label for="type" class="col-sm-2 control-label"><?php echo __('Type');?></label>
                    <div class="col-sm-4">
                        <?php echo $this->Form->input('type',array('type'=>'radio','options'=>array("Get"=>__('Get'),"Post"=>__('Post')),'default'=>'Get','legend'=>false,'before' => '<label class="radio-inline">','separator' => '</label><label class="radio-inline">','after'=>'</label>','label' => false,'div'=>false));?>
                    </div>
                </div>
                <div class="form-group text-left">
                    <div class="col-sm-offset-2 col-sm-10">
                        <button type="submit" class="btn btn-success"><span class="fa fa-refresh"></span>&nbsp;<?php echo __('Save Settings');?></button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Live SMS Gateway Testing Panel -->
    <div class="panel panel-info" style="margin-top:20px;">
        <div class="panel-heading">
            <h3 class="panel-title"><i class="fa fa-paper-plane"></i> <?php echo __('Send Test SMS via Active Default Gateway');?></h3>
        </div>
        <div class="panel-body">
            <p class="text-muted" style="margin-bottom:15px;">
                <?php echo __('Verify real-time SMS delivery to ensure that the active messaging gateway dispatches properly to mobile carriers.');?>
                <span id="activeEngineNotice" class="label <?php echo $isLeadbotDefault ? 'label-success' : 'label-default'; ?>">
                    <?php echo $isLeadbotDefault ? __('Dispatching via LeadBot Android Gateway') : __('Dispatching via Custom HTTP API'); ?>
                </span>
            </p>
            <div class="row">
                <div class="col-sm-4">
                    <label class="control-label"><strong><?php echo __('Recipient Phone Number');?></strong></label>
                    <input type="text" id="test_sms_mobile" class="form-control" placeholder="e.g. 03114432729 or +923114432729" value="03114432729">
                </div>
                <div class="col-sm-6">
                    <label class="control-label"><strong><?php echo __('Test Message');?></strong></label>
                    <input type="text" id="test_sms_message" class="form-control" value="White Villas live SMS gateway test message.">
                </div>
                <div class="col-sm-2" style="margin-top:25px;">
                    <button type="button" class="btn btn-primary btn-block" id="btnSendTestSms">
                        <i class="fa fa-send"></i> <?php echo __('Send Test SMS');?>
                    </button>
                </div>
            </div>
            <div id="test_sms_result" style="margin-top:15px;display:none;"></div>
        </div>
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    // Password toggle
    $('#toggleSmsPassBtn').on('click', function() {
        var input = $('#sms_password_field');
        var icon = $('#toggleSmsPassIcon');
        if (input.attr('type') === 'text') {
            input.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        } else {
            input.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        }
    });

    // 1-Click Interactive Toggle for LeadBot as Default Gateway
    $('#btnToggleLeadbotDefault').on('click', function() {
        var btn = $(this);
        var currentIsLeadbot = ($('#hidden_gateway_type').val() === 'leadbot');
        var targetType = currentIsLeadbot ? 'custom' : 'leadbot';

        btn.prop('disabled', true);
        $('#toggleLeadbotIcon').removeClass('fa-rocket fa-undo').addClass('fa-spinner fa-spin');
        $('#toggleDefaultAjaxMsg').hide().empty();

        $.ajax({
            url: '<?php echo $this->Html->url(array("plugin" => "admin", "controller" => "smssettings", "action" => "ajax_toggle_default")); ?>',
            type: 'POST',
            data: { gateway_type: targetType },
            dataType: 'json',
            success: function(resp) {
                btn.prop('disabled', false);
                if (resp && resp.success) {
                    var isLb = resp.is_leadbot_default;
                    $('#hidden_gateway_type').val(resp.gateway_type);
                    $('#hidden_is_leadbot_default').val(isLb ? '1' : '0');

                    if (isLb) {
                        $('#panelGatewayEngine').removeClass('panel-default').addClass('panel-success');
                        $('#badgeDefaultStatus').css('background', '#10b981').text('<?php echo __("🚀 LeadBot CRM (Default Engine)"); ?>');
                        btn.removeClass('btn-success').addClass('btn-warning');
                        $('#toggleLeadbotIcon').removeClass('fa-spinner fa-spin fa-rocket').addClass('fa-undo');
                        $('#toggleLeadbotBtnText').text('<?php echo __("Switch to Custom SMS Gateway"); ?>');
                        $('#leadbotStatusAlert').removeClass('alert-info').addClass('alert-success').html(
                            '<h4 style="margin: 0 0 6px 0; font-weight: 800; font-size: 15px;"><i class="fa fa-check-circle text-success"></i> <?php echo __("LeadBot CRM is currently active as the DEFAULT gateway for SMS & WhatsApp!"); ?></h4>' +
                            '<p style="margin: 0; font-size: 13px; line-height: 1.5;"><?php echo __("All system SMS (registration, verification, reset password, deal notifications, receipts) are dispatched via LeadBot Android Gateway without per-SMS API fees."); ?></p>'
                        );
                        $('#activeEngineNotice').removeClass('label-default').addClass('label-success').text('<?php echo __("Dispatching via LeadBot Android Gateway"); ?>');
                    } else {
                        $('#panelGatewayEngine').removeClass('panel-success').addClass('panel-default');
                        $('#badgeDefaultStatus').css('background', '#64748b').text('<?php echo __("Custom HTTP SMS Gateway"); ?>');
                        btn.removeClass('btn-warning').addClass('btn-success');
                        $('#toggleLeadbotIcon').removeClass('fa-spinner fa-spin fa-undo').addClass('fa-rocket');
                        $('#toggleLeadbotBtnText').text('<?php echo __("Use LeadBot as Default for SMS & WA"); ?>');
                        $('#leadbotStatusAlert').removeClass('alert-success').addClass('alert-info').html(
                            '<h4 style="margin: 0 0 6px 0; font-weight: 800; font-size: 15px;"><i class="fa fa-info-circle text-info"></i> <?php echo __("Custom HTTP SMS Gateway is currently used for system SMS. Click below to use LeadBot as default."); ?></h4>' +
                            '<p style="margin: 0; font-size: 13px; line-height: 1.5;"><?php echo __("Switching to LeadBot allows sending SMS through your connected Android phone SIM and unified WhatsApp routing with live telemetry and logs."); ?></p>'
                        );
                        $('#activeEngineNotice').removeClass('label-success').addClass('label-default').text('<?php echo __("Dispatching via Custom HTTP API"); ?>');
                    }

                    $('#toggleDefaultAjaxMsg').html('<div class="alert alert-success" style="border-radius: 8px; margin-bottom: 0;"><i class="fa fa-check"></i> ' + resp.message + '</div>').fadeIn();
                    setTimeout(function() { $('#toggleDefaultAjaxMsg').fadeOut(); }, 4000);
                } else {
                    $('#toggleLeadbotIcon').removeClass('fa-spinner fa-spin').addClass(currentIsLeadbot ? 'fa-undo' : 'fa-rocket');
                    $('#toggleDefaultAjaxMsg').html('<div class="alert alert-danger" style="border-radius: 8px; margin-bottom: 0;"><i class="fa fa-times"></i> ' + ((resp && resp.message) ? resp.message : 'Error changing gateway') + '</div>').fadeIn();
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false);
                $('#toggleLeadbotIcon').removeClass('fa-spinner fa-spin').addClass(currentIsLeadbot ? 'fa-undo' : 'fa-rocket');
                $('#toggleDefaultAjaxMsg').html('<div class="alert alert-danger" style="border-radius: 8px; margin-bottom: 0;"><i class="fa fa-times"></i> Network error: ' + xhr.status + '</div>').fadeIn();
            }
        });
    });

    // Test SMS
    $('#btnSendTestSms').on('click', function() {
        var btn = $(this);
        var mobile = $.trim($('#test_sms_mobile').val());
        var message = $.trim($('#test_sms_message').val());
        var resDiv = $('#test_sms_result');

        if (!mobile) {
            resDiv.html('<div class="alert alert-warning"><i class="fa fa-exclamation-triangle"></i> Please enter a recipient mobile number.</div>').show();
            return;
        }

        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Sending...');
        resDiv.hide().empty();

        $.ajax({
            url: '<?php echo $this->Html->url(array("plugin" => "admin", "controller" => "smssettings", "action" => "test_sms")); ?>',
            type: 'POST',
            data: { test_mobile: mobile, test_message: message },
            dataType: 'json',
            success: function(resp) {
                btn.prop('disabled', false).html('<i class="fa fa-send"></i> <?php echo __("Send Test SMS");?>');
                if (resp && resp.status === 'success') {
                    resDiv.html('<div class="alert alert-success"><i class="fa fa-check-circle"></i> ' + resp.message + '</div>').fadeIn();
                } else {
                    var errMsg = (resp && resp.message) ? resp.message : 'Unknown gateway error';
                    resDiv.html('<div class="alert alert-danger"><i class="fa fa-times-circle"></i> ' + errMsg + '</div>').fadeIn();
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fa fa-send"></i> <?php echo __("Send Test SMS");?>');
                resDiv.html('<div class="alert alert-danger"><i class="fa fa-times-circle"></i> Network error connecting to test endpoint (' + xhr.status + ').</div>').fadeIn();
            }
        });
    });
});
</script>
