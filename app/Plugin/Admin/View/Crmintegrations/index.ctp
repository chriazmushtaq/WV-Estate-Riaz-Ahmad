<?php
$crmAjaxAgentUrl = $this->Html->url(array('controller' => 'Ajaxcontents', 'action' => 'agent'));
$providerTabs = (array)$providerOptions;
$facebookRow = !empty($facebookSettings['CrmIntegration']) ? (array)$facebookSettings['CrmIntegration'] : array();
$whatsappRow = !empty($whatsappSettings['CrmIntegration']) ? (array)$whatsappSettings['CrmIntegration'] : array();
$smsRow = !empty($smsUserSettings['CrmUserSmsSetting']) ? (array)$smsUserSettings['CrmUserSmsSetting'] : array();
$emailRow = !empty($emailUserSettings['CrmUserEmailSetting']) ? (array)$emailUserSettings['CrmUserEmailSetting'] : array();
$globalSmsRow = !empty($globalSmsSettings['Smssetting']) ? (array)$globalSmsSettings['Smssetting'] : array();
$globalEmailRow = !empty($globalEmailSettings['Emailsetting']) ? (array)$globalEmailSettings['Emailsetting'] : array();
$googleGlobalRow = !empty($googleGlobalSettings['CrmGoogleCalendarSetting']) ? (array)$googleGlobalSettings['CrmGoogleCalendarSetting'] : array();
$googleUserRow = !empty($googleUserSettings['CrmUserGoogleCalendarSetting']) ? (array)$googleUserSettings['CrmUserGoogleCalendarSetting'] : array();
$googleEffectiveRow = !empty($googleEffectiveSettings['settings']) ? (array)$googleEffectiveSettings['settings'] : array();
$googleUseGlobal = empty($googleUserRow) || !empty($googleUserRow['use_global']);
$crmHelpUrl = $this->Html->url('/help/crm_integrations_setup.html');
$smsSettingsUrl = $this->Html->url(array('controller' => 'Smssettings', 'action' => 'index'));
$emailSettingsUrl = $this->Html->url(array('controller' => 'Emailsettings', 'action' => 'index'));
$smsTemplatesUrl = $this->Html->url(array('controller' => 'Smstemplates', 'action' => 'index'));
$emailTemplatesUrl = $this->Html->url(array('controller' => 'Emailtemplates', 'action' => 'index'));
$googleGlobalConnectUrl = $this->Html->url(array('controller' => 'Crmintegrations', 'action' => 'googlecalendarconnect', 'global'));
$googleUserConnectUrl = $this->Html->url(array('controller' => 'Crmintegrations', 'action' => 'googlecalendarconnect', 'user'));
$googleGlobalDisconnectUrl = $this->Html->url(array('controller' => 'Crmintegrations', 'action' => 'googlecalendardisconnect', 'global'));
$googleUserDisconnectUrl = $this->Html->url(array('controller' => 'Crmintegrations', 'action' => 'googlecalendardisconnect', 'user'));
$selectedSmsTemplateId = !empty($this->request->data['default_sms_template_id']) ? (int)$this->request->data['default_sms_template_id'] : (isset($smsRow['default_sms_template_id']) ? (int)$smsRow['default_sms_template_id'] : 0);
$selectedEmailTemplateId = !empty($this->request->data['default_email_template_id']) ? (int)$this->request->data['default_email_template_id'] : (isset($emailRow['default_email_template_id']) ? (int)$emailRow['default_email_template_id'] : 0);
?>
<style type="text/css">
.crm-settings-panel { display:none; }
.crm-settings-panel.is-active { display:block; }
.crm-provider-switch {
    margin-bottom: 15px;
}
.crm-provider-switch .btn {
    margin-right: 6px;
    margin-bottom: 6px;
}
.crm-webhook-copy {
    display: flex;
    gap: 8px;
    align-items: center;
}
.crm-webhook-copy .form-control {
    flex: 1 1 auto;
}
.crm-gateway-own-fields.is-hidden,
.crm-email-smtp-fields.is-hidden {
    display: none;
}
.crm-config-box {
    border: 1px solid #ddd;
    background: #fafafa;
    padding: 12px;
    margin-bottom: 15px;
    border-radius: 4px;
}
.crm-config-box h4 {
    margin-top: 0;
}
</style>
<script type="text/javascript">
jQuery(document).ready(function(){
    var savedSmsTemplateId = '<?php echo (int)(isset($smsRow['default_sms_template_id']) ? $smsRow['default_sms_template_id'] : 0);?>';
    var savedEmailTemplateId = '<?php echo (int)(isset($emailRow['default_email_template_id']) ? $emailRow['default_email_template_id'] : 0);?>';
    function bindScopedUserPicker(projectSelector, userLevelSelector, wrapSelector, selectId, selectedUserId)
    {
        function refreshUsers(userId)
        {
            var projectId = jQuery(projectSelector).val() || '';
            var userLevelId = jQuery(userLevelSelector).val() || '';
            var requestUrl = '<?php echo $crmAjaxAgentUrl;?>?id=' + encodeURIComponent(projectId) + '&ugroup_id=' + encodeURIComponent(userLevelId) + '&select_id=' + encodeURIComponent(selectId);
            jQuery.ajax({
                type: 'get',
                url: requestUrl,
                success: function(response){
                    if(!response)
                    {
                        return;
                    }
                    jQuery(wrapSelector).html(response);
                    if(userId)
                    {
                        jQuery('#' + selectId).val(String(userId));
                    }
                }
            });
        }

        jQuery(projectSelector + ',' + userLevelSelector).on('change', function(){
            refreshUsers('');
        });
        if(selectedUserId)
        {
            refreshUsers(selectedUserId);
        }
    }

    function toggleOwnSettingsByElement($input)
    {
        var target = $input.attr('data-target') || '';
        if(target === '')
        {
            return;
        }
        var inputName = $input.attr('name') || '';
        var selected = $input.closest('form').find('input[name="' + inputName + '"]:checked').val() || '1';
        if(String(selected) === '0')
        {
            jQuery(target).removeClass('is-hidden');
        }
        else
        {
            jQuery(target).addClass('is-hidden');
        }
    }

    function toggleEmailSmtpFields()
    {
        var selected = jQuery('input[name="data[type]"]:checked', '#crm-email-form').val() || 'Mail';
        var $smtpWrap = jQuery('#crm-email-smtp-fields');
        if(String(selected).toLowerCase() === 'smtp')
        {
            $smtpWrap.removeClass('is-hidden');
        }
        else
        {
            $smtpWrap.addClass('is-hidden');
        }
    }

    function syncDefaultTemplateMirrorFields()
    {
        var smsTemplateId = jQuery('#CrmDefaultSmsTemplateId').val() || '';
        var emailTemplateId = jQuery('#CrmDefaultEmailTemplateId').val() || '';
        jQuery('#CrmDefaultSmsTemplateIdFlat').val(smsTemplateId);
        jQuery('#CrmDefaultEmailTemplateIdFlat').val(emailTemplateId);
    }

    function crmShowSmsCronRunResult(message, stateClass)
    {
        var $result = jQuery('#crm-sms-cron-run-result');
        if(!$result.length)
        {
            return;
        }
        $result.removeClass('alert-info alert-success alert-danger').addClass(stateClass || 'alert-info').text(message || '').show();
    }

    bindScopedUserPicker('#FacebookDefaultProjectId', '#FacebookDefaultUgroupId', '#FacebookDefaultUserWrap', 'FacebookDefaultUserId', '<?php echo (int)(isset($facebookRow['default_user_id']) ? $facebookRow['default_user_id'] : 0);?>');
    bindScopedUserPicker('#WhatsappDefaultProjectId', '#WhatsappDefaultUgroupId', '#WhatsappDefaultUserWrap', 'WhatsappDefaultUserId', '<?php echo (int)(isset($whatsappRow['default_user_id']) ? $whatsappRow['default_user_id'] : 0);?>');

    function copyWebhookUrl(targetSelector, buttonElement) {
        var input = jQuery(targetSelector).get(0);
        if(!input) {
            return;
        }
        input.focus();
        input.select();
        input.setSelectionRange(0, input.value.length);
        var originalText = jQuery(buttonElement).text();
        function markCopied() {
            jQuery(buttonElement).text('<?php echo addslashes(__('Copied'));?>');
            setTimeout(function(){
                jQuery(buttonElement).text(originalText);
            }, 1200);
        }
        if(navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(input.value).then(function(){
                markCopied();
            }, function(){
                try {
                    document.execCommand('copy');
                    markCopied();
                } catch (e) {}
            });
            return;
        }
        try {
            document.execCommand('copy');
            markCopied();
        } catch (e) {}
    }

    jQuery(document).on('click', '.crm-copy-btn', function(){
        var targetSelector = jQuery(this).attr('data-copy-target') || '';
        if(targetSelector === '') {
            return false;
        }
        copyWebhookUrl(targetSelector, this);
        return false;
    });
    jQuery(document).on('change', 'input[name="data[use_global]"]', function(){
        toggleOwnSettingsByElement(jQuery(this));
    });
    jQuery(document).on('change', '#crm-email-form input[name="data[type]"]', function(){
        toggleEmailSmtpFields();
    });
    jQuery(document).on('change', '#CrmDefaultSmsTemplateId, #CrmDefaultEmailTemplateId', function(){
        syncDefaultTemplateMirrorFields();
    });
    jQuery('#crm-sms-form, #crm-email-form').on('submit', function(){
        syncDefaultTemplateMirrorFields();
    });
    jQuery(document).on('click', '#crm-run-sms-cron-now', function(e){
        e.preventDefault();
        var $btn = jQuery(this);
        var runUrl = $btn.attr('data-run-url') || '';
        if(runUrl === '')
        {
            crmShowSmsCronRunResult('<?php echo addslashes(__('Cron URL is missing.'));?>', 'alert-danger');
            return false;
        }
        var originalHtml = $btn.html();
        $btn.prop('disabled', true).html('<span class="fa fa-spinner fa-spin"></span> <?php echo addslashes(__('Running...'));?>');
        crmShowSmsCronRunResult('<?php echo addslashes(__('Running scheduled SMS cron...'));?>', 'alert-info');
        jQuery.ajax({
            url: runUrl,
            type: 'GET',
            dataType: 'json',
            cache: false
        }).done(function(resp){
            if(resp && resp.success && resp.summary)
            {
                var summary = resp.summary || {};
                var msg = '<?php echo addslashes(__('Done'));?> - ' +
                    '<?php echo addslashes(__('Due'));?>: ' + (summary.due || 0) + ' | ' +
                    '<?php echo addslashes(__('Sent'));?>: ' + (summary.sent || 0) + ' | ' +
                    '<?php echo addslashes(__('Failed'));?>: ' + (summary.failed || 0) + ' | ' +
                    '<?php echo addslashes(__('Skipped'));?>: ' + (summary.skipped || 0);
                crmShowSmsCronRunResult(msg, 'alert-success');
            }
            else
            {
                crmShowSmsCronRunResult('<?php echo addslashes(__('Cron run did not return success.'));?>', 'alert-danger');
            }
        }).fail(function(xhr){
            var errText = '';
            try
            {
                if(xhr && xhr.responseJSON && xhr.responseJSON.message)
                {
                    errText = String(xhr.responseJSON.message);
                }
                else if(xhr && xhr.responseText)
                {
                    errText = String(xhr.responseText).substring(0, 240);
                }
            }
            catch(ignoreErr){}
            var failMsg = '<?php echo addslashes(__('Cron run failed.'));?>';
            if(errText !== '')
            {
                failMsg += ' ' + errText;
            }
            crmShowSmsCronRunResult(failMsg, 'alert-danger');
        }).always(function(){
            $btn.prop('disabled', false).html(originalHtml);
        });
        return false;
    });

    jQuery('input[name="data[use_global]"]:checked').each(function(){
        toggleOwnSettingsByElement(jQuery(this));
    });
    toggleEmailSmtpFields();
    if(savedSmsTemplateId !== '0')
    {
        jQuery('#CrmDefaultSmsTemplateId').val(savedSmsTemplateId).trigger('change');
    }
    if(savedEmailTemplateId !== '0')
    {
        jQuery('#CrmDefaultEmailTemplateId').val(savedEmailTemplateId).trigger('change');
    }
    syncDefaultTemplateMirrorFields();
});
</script>

<div class="page-title">
    <div class="title-env">
        <h1 class="title"><?php echo __('CRM Settings');?></h1>
    </div>
</div>

<div class="panel">
    <div class="panel-heading">
        <div class="btn-group">
            <?php echo $this->Html->link('<span class="fa fa-dashboard"></span>&nbsp;'.__('CRM Dashboard'), array('controller' => 'Leads', 'action' => 'dashboard'), array('escape' => false, 'class' => 'btn btn-default'));?>
            <?php echo $this->Html->link('<span class="fa fa-upload"></span>&nbsp;'.__('CRM Bulk Import'), array('controller' => 'Crmintegrations', 'action' => 'bulkimport'), array('escape' => false, 'class' => 'btn btn-success'));?>
            <?php echo $this->Html->link('<span class="fa fa-random"></span>&nbsp;'.__('CRM Routing'), array('controller' => 'Crmintegrations', 'action' => 'routes'), array('escape' => false, 'class' => 'btn btn-primary'));?>
            <?php echo $this->Html->link('<span class="fa fa-history"></span>&nbsp;'.__('CRM Sync Logs'), array('controller' => 'Crmintegrations', 'action' => 'logs'), array('escape' => false, 'class' => 'btn btn-info'));?>
            <a href="<?php echo $crmHelpUrl;?>" target="_blank" class="btn btn-warning"><span class="fa fa-book"></span>&nbsp;<?php echo __('Integration Help');?></a>
        </div>
    </div>
    <div class="panel-body">
        <?php echo $this->Session->flash();?>
        <div class="crm-provider-switch">
            <?php foreach($providerTabs as $providerKey => $providerLabel){
                $providerUrl = $this->Html->url(array('action' => 'index', '?' => array('tab' => $providerKey)));
                $providerBtnClass = ($activeTab === $providerKey) ? 'btn btn-primary' : 'btn btn-default';
            ?>
            <a class="<?php echo $providerBtnClass;?>" href="<?php echo $providerUrl;?>" onclick="window.location.href=this.href; return false;"><?php echo h($providerLabel);?></a>
            <?php } ?>
        </div>

        <?php if(in_array($activeTab, array('facebook_leads', 'whatsapp_cloud'), true)){ ?>
        <div class="alert alert-info">
            <strong><?php echo __('Webhook Note');?></strong><br/>
            <?php echo __('Meta webhooks require a public HTTPS URL. Localhost is fine for backend settings, but production auto-import must point to your live domain.');?>
            <br/>
            <a href="<?php echo $crmHelpUrl;?>" target="_blank"><strong><?php echo __('Open Full Setup Guide');?></strong></a>
        </div>
        <?php } elseif($activeTab === 'google_calendar'){ ?>
        <div class="alert alert-info">
            <strong><?php echo __('Google Calendar Note');?></strong><br/>
            <?php echo __('Save Google Client ID and Client Secret first, then connect the account. Use Global lets a user sync through the shared system Google Calendar, while Use My Own keeps follow-ups and tasks isolated to that user\'s own Google account.');?>
            <br/>
            <a href="<?php echo $crmHelpUrl;?>" target="_blank"><strong><?php echo __('Open Full Setup Guide');?></strong></a>
        </div>
        <?php } else { ?>
        <div class="alert alert-info">
            <strong><?php echo __('Gateway Note');?></strong><br/>
            <?php echo __('Use Global will keep using your existing system SMS/Email settings. Use My Own will save gateway details for only the current logged-in user, without changing anyone else.');?>
            <br/>
            <a href="<?php echo $crmHelpUrl;?>" target="_blank"><strong><?php echo __('Open Full Setup Guide');?></strong></a>
        </div>
        <?php } ?>

        <div class="tab-content">
            <div class="crm-settings-panel <?php echo ($activeTab === 'sms_gateway') ? 'is-active' : '';?>" id="crm-sms-panel">
                <?php echo $this->Form->create(false, array('url' => array('action' => 'index', '?' => array('tab' => 'sms_gateway')), 'class' => 'form-horizontal', 'id' => 'crm-sms-form'));?>
                <?php echo $this->Form->hidden('provider', array('value' => 'sms_gateway'));?>
                <input type="hidden" name="default_sms_template_id" id="CrmDefaultSmsTemplateIdFlat" value="<?php echo (int)$selectedSmsTemplateId;?>" />
                <div class="row">
                    <div class="col-md-6">
                        <div class="crm-config-box">
                            <h4><?php echo __('Gateway Mode');?></h4>
                            <div class="radio"><label><input type="radio" name="data[use_global]" value="1" data-target="#crm-sms-own-fields" <?php if(!empty($smsRow['use_global'])){ echo 'checked="checked"'; }?> /> <?php echo __('Use Global SMS Settings');?></label></div>
                            <div class="radio" style="margin-top:8px;"><label><input type="radio" name="data[use_global]" value="0" data-target="#crm-sms-own-fields" <?php if(isset($smsRow['use_global']) && (int)$smsRow['use_global'] === 0){ echo 'checked="checked"'; }?> /> <?php echo __('Use My Own SMS Gateway');?></label></div>
                            <p class="help-block" style="margin-top:10px;"><?php echo __('Users with CRM Settings page permission can choose their own SMS gateway or continue using the global system gateway.');?></p>
                            <a href="<?php echo $smsSettingsUrl;?>" class="btn btn-default"><span class="fa fa-cog"></span> <?php echo __('Open Global SMS Settings');?></a>
                        </div>
                        <div class="crm-config-box">
                            <h4><?php echo __('Global SMS Summary');?></h4>
                            <p><strong><?php echo __('API Link');?>:</strong> <?php echo h(!empty($globalSmsRow['api']) ? $globalSmsRow['api'] : __('Not Set'));?></p>
                            <p><strong><?php echo __('Sender ID');?>:</strong> <?php echo h(!empty($globalSmsRow['senderid']) ? $globalSmsRow['senderid'] : __('Not Set'));?></p>
                            <p><strong><?php echo __('Type');?>:</strong> <?php echo h(!empty($globalSmsRow['type']) ? $globalSmsRow['type'] : __('Not Set'));?></p>
                        </div>
                        <div id="crm-sms-own-fields" class="crm-gateway-own-fields<?php echo (!isset($smsRow['use_global']) || !empty($smsRow['use_global'])) ? ' is-hidden' : '';?>">
                            <div class="form-group"><label class="col-sm-4 control-label"><?php echo __('API Link');?></label><div class="col-sm-8"><input type="text" name="data[api]" value="<?php echo h(isset($smsRow['api']) ? $smsRow['api'] : '');?>" class="form-control" /></div></div>
                            <div class="form-group"><label class="col-sm-4 control-label"><?php echo __('Sender ID');?></label><div class="col-sm-8"><input type="text" name="data[senderid]" value="<?php echo h(isset($smsRow['senderid']) ? $smsRow['senderid'] : '');?>" class="form-control" /></div></div>
                            <div class="form-group"><label class="col-sm-4 control-label"><?php echo __('User Name');?></label><div class="col-sm-8"><input type="text" name="data[username]" value="<?php echo h(isset($smsRow['username']) ? $smsRow['username'] : '');?>" class="form-control" /></div></div>
                            <div class="form-group"><label class="col-sm-4 control-label"><?php echo __('Password/API Key');?></label><div class="col-sm-8"><input type="password" name="data[password]" value="<?php echo h(isset($smsRow['password']) ? $smsRow['password'] : '');?>" class="form-control" /></div></div>
                            <div class="form-group"><label class="col-sm-4 control-label"><?php echo __('Heading Username');?></label><div class="col-sm-8"><input type="text" name="data[husername]" value="<?php echo h(isset($smsRow['husername']) ? $smsRow['husername'] : '');?>" class="form-control" /></div></div>
                            <div class="form-group"><label class="col-sm-4 control-label"><?php echo __('Heading Password');?></label><div class="col-sm-8"><input type="text" name="data[hpassword]" value="<?php echo h(isset($smsRow['hpassword']) ? $smsRow['hpassword'] : '');?>" class="form-control" /></div></div>
                            <div class="form-group"><label class="col-sm-4 control-label"><?php echo __('Heading Mobile No');?></label><div class="col-sm-8"><input type="text" name="data[hmobile]" value="<?php echo h(isset($smsRow['hmobile']) ? $smsRow['hmobile'] : '');?>" class="form-control" /></div></div>
                            <div class="form-group"><label class="col-sm-4 control-label"><?php echo __('Heading Message');?></label><div class="col-sm-8"><input type="text" name="data[hmessage]" value="<?php echo h(isset($smsRow['hmessage']) ? $smsRow['hmessage'] : '');?>" class="form-control" /></div></div>
                            <div class="form-group"><label class="col-sm-4 control-label"><?php echo __('Heading Sender ID');?></label><div class="col-sm-8"><input type="text" name="data[hsenderid]" value="<?php echo h(isset($smsRow['hsenderid']) ? $smsRow['hsenderid'] : '');?>" class="form-control" /></div></div>
                            <div class="form-group"><label class="col-sm-4 control-label"><?php echo __('Type');?></label><div class="col-sm-8" style="padding-top:7px;"><label class="radio-inline"><input type="radio" name="data[type]" value="Get" <?php if(!isset($smsRow['type']) || strcasecmp((string)$smsRow['type'], 'Get') === 0){ echo 'checked="checked"'; }?> /> <?php echo __('Get');?></label><label class="radio-inline"><input type="radio" name="data[type]" value="Post" <?php if(isset($smsRow['type']) && strcasecmp((string)$smsRow['type'], 'Post') === 0){ echo 'checked="checked"'; }?> /> <?php echo __('Post');?></label></div></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="crm-config-box">
                            <h4><?php echo __('Test SMS Gateway');?></h4>
                            <p class="text-muted"><?php echo __('This button sends one live SMS using either the global gateway or your own saved/entered gateway, based on the selected mode above.');?></p>
                            <div class="form-group"><label class="col-sm-4 control-label"><?php echo __('Test Mobile');?></label><div class="col-sm-8"><input type="text" name="test_mobile" value="" class="form-control" placeholder="<?php echo __('Enter mobile number');?>" /></div></div>
                            <div class="form-group"><label class="col-sm-4 control-label"><?php echo __('Test Message');?></label><div class="col-sm-8"><textarea name="test_message" class="form-control" rows="4"><?php echo __('This is a CRM SMS gateway test message.');?></textarea></div></div>
                        </div>
                        <div class="crm-config-box">
                            <h4><?php echo __('Lead Bulk Messaging');?></h4>
                            <p class="text-muted"><?php echo __('Lead bulk SMS already uses the Leads template type and selected leads flow. After this setup, that same screen will use your chosen mode: global gateway or your own gateway, and it will preselect the SMS template chosen here.');?></p>
                            <div class="form-group">
                                <label class="col-sm-4 control-label"><?php echo __('Default Template');?></label>
                                <div class="col-sm-8">
                                    <select name="data[default_sms_template_id]" id="CrmDefaultSmsTemplateId" class="form-control">
                                        <option value=""><?php echo __('Please Select');?></option>
                                        <?php foreach((array)$smsTemplateOptions as $templateGroupLabel => $templateGroupRows){ ?>
                                            <?php if(is_array($templateGroupRows)){ ?>
                                                <optgroup label="<?php echo h($templateGroupLabel);?>">
                                                    <?php foreach((array)$templateGroupRows as $templateId => $templateName){ ?>
                                                        <option value="<?php echo (int)$templateId;?>" <?php if((int)$selectedSmsTemplateId === (int)$templateId){ echo 'selected="selected"'; }?>><?php echo h($templateName);?></option>
                                                    <?php } ?>
                                                </optgroup>
                                            <?php } else { ?>
                                                <option value="<?php echo (int)$templateGroupLabel;?>" <?php if((int)$selectedSmsTemplateId === (int)$templateGroupLabel){ echo 'selected="selected"'; }?>><?php echo h($templateGroupRows);?></option>
                                            <?php } ?>
                                        <?php } ?>
                                    </select>
                                    <p class="help-block"><?php echo __('This template will open as default in Leads bulk SMS for the logged-in user.');?></p>
                                </div>
                            </div>
                            <?php echo $this->Html->link('<span class="fa fa-users"></span> '.__('Open Manage Leads'), array('controller' => 'Leads', 'action' => 'manage'), array('escape' => false, 'class' => 'btn btn-info'));?>
                            <a href="<?php echo $smsTemplatesUrl;?>" class="btn btn-default"><span class="fa fa-files-o"></span> <?php echo __('Open My SMS Templates');?></a>
                        </div>
                    
                        <?php if(!empty($isSuperAdmin)){ ?>
                        <div class="crm-config-box">
                            <h4><?php echo __('Scheduled SMS Cron Jobs');?></h4>
                            <p class="text-muted"><?php echo __('Run this cron every 5 minutes to process queued/scheduled lead SMS.');?></p>
                            <div class="form-group">
                                <label class="col-sm-4 control-label"><?php echo __('Cron URL');?></label>
                                <div class="col-sm-8">
                                    <div class="crm-webhook-copy">
                                        <input type="text" readonly="readonly" class="form-control" id="SmsCronUrlInput" value="<?php echo h((string)$smsCronUrl);?>" />
                                        <button type="button" class="btn btn-default crm-copy-btn" data-copy-target="#SmsCronUrlInput"><?php echo __('Copy');?></button>
                                    </div>
                                    <p class="help-block"><?php echo __('Recommended for cPanel URL cron jobs.');?></p>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-4 control-label"><?php echo __('URL Sample');?></label>
                                <div class="col-sm-8"><input type="text" readonly="readonly" class="form-control" value="<?php echo h((string)$smsCronUrlSample);?>" /></div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-4 control-label"><?php echo __('CLI Command');?></label>
                                <div class="col-sm-8"><input type="text" readonly="readonly" class="form-control" value="<?php echo h((string)$smsCronCommand);?>" /></div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-4 control-label"><?php echo __('CLI Sample');?></label>
                                <div class="col-sm-8"><input type="text" readonly="readonly" class="form-control" value="<?php echo h((string)$smsCronSample);?>" /></div>
                            </div>
                            <div class="form-group" style="margin-bottom:0;">
                                <label class="col-sm-4 control-label"><?php echo __('Run Now');?></label>
                                <div class="col-sm-8">
                                    <button type="button" id="crm-run-sms-cron-now" class="btn btn-primary" data-run-url="<?php echo h((string)$smsCronUrl);?>"><span class="fa fa-play"></span> <?php echo __('Run Scheduled SMS Cron Now');?></button>
                                    <div id="crm-sms-cron-run-result" class="alert alert-info" style="margin-top:10px;display:none;"></div>
                                </div>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                </div>
                <div class="form-group text-left"><div class="col-sm-12"><button type="submit" name="submit_action" value="save" class="btn btn-success"><span class="fa fa-save"></span> <?php echo __('Save SMS Settings');?></button> <button type="submit" name="submit_action" value="test" class="btn btn-primary"><span class="fa fa-paper-plane"></span> <?php echo __('Test SMS Gateway');?></button></div></div>
                <?php echo $this->Form->end();?>
            </div>
            <div class="crm-settings-panel <?php echo ($activeTab === 'email_gateway') ? 'is-active' : '';?>" id="crm-email-panel">
                <?php echo $this->Form->create(false, array('url' => array('action' => 'index', '?' => array('tab' => 'email_gateway')), 'class' => 'form-horizontal', 'id' => 'crm-email-form'));?>
                <?php echo $this->Form->hidden('provider', array('value' => 'email_gateway'));?>
                <input type="hidden" name="default_email_template_id" id="CrmDefaultEmailTemplateIdFlat" value="<?php echo (int)$selectedEmailTemplateId;?>" />
                <div class="row">
                    <div class="col-md-6">
                        <div class="crm-config-box">
                            <h4><?php echo __('Gateway Mode');?></h4>
                            <div class="radio"><label><input type="radio" name="data[use_global]" value="1" data-target="#crm-email-own-fields" <?php if(!empty($emailRow['use_global'])){ echo 'checked="checked"'; }?> /> <?php echo __('Use Global Email Settings');?></label></div>
                            <div class="radio" style="margin-top:8px;"><label><input type="radio" name="data[use_global]" value="0" data-target="#crm-email-own-fields" <?php if(isset($emailRow['use_global']) && (int)$emailRow['use_global'] === 0){ echo 'checked="checked"'; }?> /> <?php echo __('Use My Own Email Gateway');?></label></div>
                            <p class="help-block" style="margin-top:10px;"><?php echo __('Users with CRM Settings page permission can choose their own email gateway or continue using the global system email settings.');?></p>
                            <a href="<?php echo $emailSettingsUrl;?>" class="btn btn-default"><span class="fa fa-cog"></span> <?php echo __('Open Global Email Settings');?></a>
                        </div>
                        <div class="crm-config-box">
                            <h4><?php echo __('Global Email Summary');?></h4>
                            <p><strong><?php echo __('Type');?>:</strong> <?php echo h(!empty($globalEmailRow['type']) ? $globalEmailRow['type'] : __('Not Set'));?></p>
                            <p><strong><?php echo __('Host');?>:</strong> <?php echo h(!empty($globalEmailRow['host']) ? $globalEmailRow['host'] : __('Not Set'));?></p>
                            <p><strong><?php echo __('Port');?>:</strong> <?php echo h(!empty($globalEmailRow['port']) ? $globalEmailRow['port'] : __('Not Set'));?></p>
                        </div>
                        <div id="crm-email-own-fields" class="crm-gateway-own-fields<?php echo (!isset($emailRow['use_global']) || !empty($emailRow['use_global'])) ? ' is-hidden' : '';?>">
                            <div class="form-group"><label class="col-sm-4 control-label"><?php echo __('Email Type');?></label><div class="col-sm-8" style="padding-top:7px;"><label class="radio-inline"><input type="radio" name="data[type]" value="Mail" <?php if(!isset($emailRow['type']) || strcasecmp((string)$emailRow['type'], 'Mail') === 0){ echo 'checked="checked"'; }?> /> <?php echo __('LOCALHOST');?></label><label class="radio-inline"><input type="radio" name="data[type]" value="Smtp" <?php if(isset($emailRow['type']) && strcasecmp((string)$emailRow['type'], 'Smtp') === 0){ echo 'checked="checked"'; }?> /> <?php echo __('SMTP');?></label></div></div>
                            <div id="crm-email-smtp-fields" class="crm-email-smtp-fields<?php echo (isset($emailRow['type']) && strcasecmp((string)$emailRow['type'], 'Smtp') === 0) ? '' : ' is-hidden';?>">
                                <div class="form-group"><label class="col-sm-4 control-label"><?php echo __('Server Name/Host');?></label><div class="col-sm-8"><input type="text" name="data[host]" value="<?php echo h(isset($emailRow['host']) ? $emailRow['host'] : '');?>" class="form-control" /></div></div>
                                <div class="form-group"><label class="col-sm-4 control-label"><?php echo __('Port');?></label><div class="col-sm-8"><input type="text" name="data[port]" value="<?php echo h(isset($emailRow['port']) ? $emailRow['port'] : '');?>" class="form-control" /></div></div>
                                <div class="form-group"><label class="col-sm-4 control-label"><?php echo __('User Name');?></label><div class="col-sm-8"><input type="text" name="data[username]" value="<?php echo h(isset($emailRow['username']) ? $emailRow['username'] : '');?>" class="form-control" /></div></div>
                                <div class="form-group"><label class="col-sm-4 control-label"><?php echo __('Password');?></label><div class="col-sm-8"><input type="password" name="data[password]" value="<?php echo h(isset($emailRow['password']) ? $emailRow['password'] : '');?>" class="form-control" /></div></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="crm-config-box">
                            <h4><?php echo __('Test Email Gateway');?></h4>
                            <p class="text-muted"><?php echo __('This button sends one live email using either the global gateway or your own saved/entered gateway, based on the selected mode above.');?></p>
                            <div class="form-group"><label class="col-sm-4 control-label"><?php echo __('Test Email');?></label><div class="col-sm-8"><input type="text" name="test_email" value="" class="form-control" placeholder="<?php echo __('Enter email address');?>" /></div></div>
                            <div class="form-group"><label class="col-sm-4 control-label"><?php echo __('Subject');?></label><div class="col-sm-8"><input type="text" name="test_subject" value="<?php echo __('CRM Gateway Test Email');?>" class="form-control" /></div></div>
                            <div class="form-group"><label class="col-sm-4 control-label"><?php echo __('Message');?></label><div class="col-sm-8"><textarea name="test_message" class="form-control" rows="6"><?php echo __('This is a CRM email gateway test message.');?></textarea></div></div>
                        </div>
                        <div class="crm-config-box">
                            <h4><?php echo __('Lead Bulk Messaging');?></h4>
                            <p class="text-muted"><?php echo __('Lead bulk Email already uses the Leads template type and selected leads flow. After this setup, that same screen will use your chosen mode: global gateway or your own gateway, and it will preselect the Email template chosen here.');?></p>
                            <div class="form-group">
                                <label class="col-sm-4 control-label"><?php echo __('Default Template');?></label>
                                <div class="col-sm-8">
                                    <select name="data[default_email_template_id]" id="CrmDefaultEmailTemplateId" class="form-control">
                                        <option value=""><?php echo __('Please Select');?></option>
                                        <?php foreach((array)$emailTemplateOptions as $templateGroupLabel => $templateGroupRows){ ?>
                                            <?php if(is_array($templateGroupRows)){ ?>
                                                <optgroup label="<?php echo h($templateGroupLabel);?>">
                                                    <?php foreach((array)$templateGroupRows as $templateId => $templateName){ ?>
                                                        <option value="<?php echo (int)$templateId;?>" <?php if((int)$selectedEmailTemplateId === (int)$templateId){ echo 'selected="selected"'; }?>><?php echo h($templateName);?></option>
                                                    <?php } ?>
                                                </optgroup>
                                            <?php } else { ?>
                                                <option value="<?php echo (int)$templateGroupLabel;?>" <?php if((int)$selectedEmailTemplateId === (int)$templateGroupLabel){ echo 'selected="selected"'; }?>><?php echo h($templateGroupRows);?></option>
                                            <?php } ?>
                                        <?php } ?>
                                    </select>
                                    <p class="help-block"><?php echo __('This template will open as default in Leads bulk Email for the logged-in user.');?></p>
                                </div>
                            </div>
                            <?php echo $this->Html->link('<span class="fa fa-users"></span> '.__('Open Manage Leads'), array('controller' => 'Leads', 'action' => 'manage'), array('escape' => false, 'class' => 'btn btn-info'));?>
                            <a href="<?php echo $emailTemplatesUrl;?>" class="btn btn-default"><span class="fa fa-files-o"></span> <?php echo __('Open My Email Templates');?></a>
                        </div>
                    </div>
                </div>
                <div class="form-group text-left"><div class="col-sm-12"><button type="submit" name="submit_action" value="save" class="btn btn-success"><span class="fa fa-save"></span> <?php echo __('Save Email Settings');?></button> <button type="submit" name="submit_action" value="test" class="btn btn-primary"><span class="fa fa-paper-plane"></span> <?php echo __('Test Email Gateway');?></button></div></div>
                <?php echo $this->Form->end();?>
            </div>

            <div class="crm-settings-panel <?php echo ($activeTab === 'google_calendar') ? 'is-active' : '';?>" id="crm-google-calendar-panel">
                <?php if(!empty($isSuperAdmin)){ ?>
                <?php echo $this->Form->create(false, array('url' => array('action' => 'index', '?' => array('tab' => 'google_calendar')), 'class' => 'form-horizontal'));?>
                <?php echo $this->Form->hidden('provider', array('value' => 'google_calendar_global'));?>
                <div class="crm-config-box">
                    <h4><?php echo __('Global Google Calendar');?></h4>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group"><label class="col-sm-4 control-label"><?php echo __('Client ID');?></label><div class="col-sm-8"><input type="text" name="data[client_id]" value="<?php echo h(isset($googleGlobalRow['client_id']) ? $googleGlobalRow['client_id'] : '');?>" class="form-control" /></div></div>
                            <div class="form-group"><label class="col-sm-4 control-label"><?php echo __('Client Secret');?></label><div class="col-sm-8"><input type="password" name="data[client_secret]" value="<?php echo h(isset($googleGlobalRow['client_secret']) ? $googleGlobalRow['client_secret'] : '');?>" class="form-control" /></div></div>
                            <div class="form-group"><label class="col-sm-4 control-label"><?php echo __('Calendar ID');?></label><div class="col-sm-8"><input type="text" name="data[calendar_id]" value="<?php echo h(isset($googleGlobalRow['calendar_id']) ? $googleGlobalRow['calendar_id'] : 'primary');?>" class="form-control" placeholder="primary" /></div></div>
                            <div class="form-group"><label class="col-sm-4 control-label"><?php echo __('Callback URL');?></label><div class="col-sm-8"><div class="crm-webhook-copy"><input type="text" readonly="readonly" class="form-control" id="GoogleCalendarCallbackUrlGlobal" value="<?php echo h($googleCalendarCallbackUrl);?>" /><button type="button" class="btn btn-default crm-copy-btn" data-copy-target="#GoogleCalendarCallbackUrlGlobal"><?php echo __('Copy');?></button></div></div></div>
                        </div>
                        <div class="col-md-6">
                            <div class="crm-config-box" style="margin-bottom:0;">
                                <h4><?php echo __('Connection Status');?></h4>
                                <p><strong><?php echo __('Connected Account');?>:</strong> <?php echo h(!empty($googleGlobalRow['google_email']) ? $googleGlobalRow['google_email'] : __('Not Connected'));?></p>
                                <p><strong><?php echo __('Calendar');?>:</strong> <?php echo h(!empty($googleGlobalRow['calendar_id']) ? $googleGlobalRow['calendar_id'] : 'primary');?></p>
                                <p><strong><?php echo __('Token Expiry');?>:</strong> <?php echo h(!empty($googleGlobalRow['token_expires_at']) ? $googleGlobalRow['token_expires_at'] : __('Not Available'));?></p>
                                <p class="text-muted"><?php echo __('Save credentials first, then connect the Google account. Users who choose Use Global will sync through this shared calendar connection.');?></p>
                                <p>
                                    <a href="<?php echo $googleGlobalConnectUrl;?>" class="btn btn-primary"><span class="fa fa-google"></span> <?php echo __('Connect Global Account');?></a>
                                    <?php if(!empty($googleGlobalRow['is_connected'])){ ?>
                                    <a href="<?php echo $googleGlobalDisconnectUrl;?>" class="btn btn-danger"><span class="fa fa-chain-broken"></span> <?php echo __('Disconnect');?></a>
                                    <?php } ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="form-group text-left"><div class="col-sm-12"><button type="submit" name="submit_action" value="save" class="btn btn-success"><span class="fa fa-save"></span> <?php echo __('Save Global Google Settings');?></button> <button type="submit" name="submit_action" value="test" class="btn btn-info"><span class="fa fa-plug"></span> <?php echo __('Test Global Connection');?></button></div></div>
                </div>
                <?php echo $this->Form->end();?>
                <?php } ?>

                <?php echo $this->Form->create(false, array('url' => array('action' => 'index', '?' => array('tab' => 'google_calendar')), 'class' => 'form-horizontal'));?>
                <?php echo $this->Form->hidden('provider', array('value' => 'google_calendar_user'));?>
                <div class="crm-config-box">
                    <h4><?php echo __('My Google Calendar');?></h4>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="radio"><label><input type="radio" name="data[use_global]" value="1" data-target="#crm-google-user-own-fields" <?php if($googleUseGlobal){ echo 'checked="checked"'; }?> /> <?php echo __('Use Global Google Calendar');?></label></div>
                            <div class="radio" style="margin-top:8px;"><label><input type="radio" name="data[use_global]" value="0" data-target="#crm-google-user-own-fields" <?php if(!$googleUseGlobal){ echo 'checked="checked"'; }?> /> <?php echo __('Use My Own Google Calendar');?></label></div>
                            <div class="checkbox" style="margin-top:12px;"><label><input type="checkbox" name="data[sync_enabled]" value="1" <?php if(!empty($googleUserRow['sync_enabled'])){ echo 'checked="checked"'; }?> /> <?php echo __('Enable Google Calendar Sync');?></label></div>
                            <div class="checkbox"><label><input type="checkbox" name="data[sync_follow_up]" value="1" <?php if(!isset($googleUserRow['sync_follow_up']) || !empty($googleUserRow['sync_follow_up'])){ echo 'checked="checked"'; }?> /> <?php echo __('Sync Lead Follow-up');?></label></div>
                            <div class="checkbox"><label><input type="checkbox" name="data[sync_calendar_tasks]" value="1" <?php if(!isset($googleUserRow['sync_calendar_tasks']) || !empty($googleUserRow['sync_calendar_tasks'])){ echo 'checked="checked"'; }?> /> <?php echo __('Sync Calendar Tasks');?></label></div>
                            <div id="crm-google-user-own-fields" class="crm-gateway-own-fields<?php echo $googleUseGlobal ? ' is-hidden' : '';?>" style="margin-top:15px;">
                                <div class="form-group"><label class="col-sm-4 control-label"><?php echo __('Client ID');?></label><div class="col-sm-8"><input type="text" name="data[client_id]" value="<?php echo h(isset($googleUserRow['client_id']) ? $googleUserRow['client_id'] : '');?>" class="form-control" /></div></div>
                                <div class="form-group"><label class="col-sm-4 control-label"><?php echo __('Client Secret');?></label><div class="col-sm-8"><input type="password" name="data[client_secret]" value="<?php echo h(isset($googleUserRow['client_secret']) ? $googleUserRow['client_secret'] : '');?>" class="form-control" /></div></div>
                                <div class="form-group"><label class="col-sm-4 control-label"><?php echo __('Calendar ID');?></label><div class="col-sm-8"><input type="text" name="data[calendar_id]" value="<?php echo h(isset($googleUserRow['calendar_id']) ? $googleUserRow['calendar_id'] : 'primary');?>" class="form-control" placeholder="primary" /></div></div>
                                <div class="form-group"><label class="col-sm-4 control-label"><?php echo __('Callback URL');?></label><div class="col-sm-8"><div class="crm-webhook-copy"><input type="text" readonly="readonly" class="form-control" id="GoogleCalendarCallbackUrlUser" value="<?php echo h($googleCalendarCallbackUrl);?>" /><button type="button" class="btn btn-default crm-copy-btn" data-copy-target="#GoogleCalendarCallbackUrlUser"><?php echo __('Copy');?></button></div></div></div>
                                <div class="form-group"><div class="col-sm-12 text-right"><a href="<?php echo $googleUserConnectUrl;?>" class="btn btn-primary"><span class="fa fa-google"></span> <?php echo __('Connect My Account');?></a> <?php if(!empty($googleUserRow['is_connected'])){ ?><a href="<?php echo $googleUserDisconnectUrl;?>" class="btn btn-danger"><span class="fa fa-chain-broken"></span> <?php echo __('Disconnect');?></a><?php } ?>
                    </div></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="crm-config-box" style="margin-bottom:0;">
                                <h4><?php echo __('Effective Sync Status');?></h4>
                                <p><strong><?php echo __('Mode');?>:</strong> <?php echo $googleUseGlobal ? __('Global') : __('My Own');?></p>
                                <p><strong><?php echo __('Connected Account');?>:</strong> <?php echo h(!empty($googleEffectiveRow['google_email']) ? $googleEffectiveRow['google_email'] : __('Not Connected'));?></p>
                                <p><strong><?php echo __('Calendar');?>:</strong> <?php echo h(!empty($googleEffectiveRow['calendar_id']) ? $googleEffectiveRow['calendar_id'] : 'primary');?></p>
                                <p><strong><?php echo __('Last Sync');?>:</strong> <?php echo h(!empty($googleUserRow['last_sync_at']) ? $googleUserRow['last_sync_at'] : __('Not Synced Yet'));?></p>
                                <p class="text-muted"><?php echo __('When sync is enabled, lead follow-up updates and calendar task saves will push to Google Calendar for this user. Use Sync My Upcoming Items to send current pending records too.');?></p>
                            </div>
                        </div>
                    </div>
                    <div class="form-group text-left"><div class="col-sm-12"><button type="submit" name="submit_action" value="save" class="btn btn-success"><span class="fa fa-save"></span> <?php echo __('Save Google Calendar Settings');?></button> <button type="submit" name="submit_action" value="test" class="btn btn-info"><span class="fa fa-plug"></span> <?php echo __('Test Connection');?></button> <button type="submit" name="submit_action" value="sync" class="btn btn-primary"><span class="fa fa-refresh"></span> <?php echo __('Sync My Upcoming Items');?></button></div></div>
                </div>
                <?php echo $this->Form->end();?>
            </div>

            <div class="crm-settings-panel <?php echo ($activeTab === 'facebook_leads') ? 'is-active' : '';?>" id="crm-facebook-panel">
                <?php echo $this->Form->create(false, array('url' => array('action' => 'index', '?' => array('tab' => 'facebook_leads')), 'class' => 'form-horizontal'));?>
                <?php echo $this->Form->hidden('provider', array('value' => 'facebook_leads'));?>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="col-sm-4 control-label"><?php echo __('Enabled');?></label>
                            <div class="col-sm-8" style="padding-top:7px;">
                                <label><input type="checkbox" name="data[enabled]" value="1" <?php if(!empty($facebookRow['enabled'])){ echo 'checked="checked"'; }?> /> <?php echo __('Enable Facebook Lead Ads auto-import');?></label>
                            </div>
                        </div>
                        <div class="form-group"><label class="col-sm-4 control-label"><?php echo __('App ID');?></label><div class="col-sm-8"><input type="text" name="data[app_id]" value="<?php echo h(isset($facebookRow['app_id']) ? $facebookRow['app_id'] : '');?>" class="form-control" /></div></div>
                        <div class="form-group"><label class="col-sm-4 control-label"><?php echo __('App Secret');?></label><div class="col-sm-8"><input type="text" name="data[app_secret]" value="<?php echo h(isset($facebookRow['app_secret']) ? $facebookRow['app_secret'] : '');?>" class="form-control" /></div></div>
                        <div class="form-group"><label class="col-sm-4 control-label"><?php echo __('Page Access Token');?></label><div class="col-sm-8"><textarea name="data[access_token]" class="form-control" rows="4"><?php echo h(isset($facebookRow['access_token']) ? $facebookRow['access_token'] : '');?></textarea></div></div>
                        <div class="form-group"><label class="col-sm-4 control-label"><?php echo __('Verify Token');?></label><div class="col-sm-8"><input type="text" name="data[verify_token]" value="<?php echo h(isset($facebookRow['verify_token']) ? $facebookRow['verify_token'] : '');?>" class="form-control" /><p class="help-block"><?php echo __('Use the same token inside your Meta webhook setup.');?></p></div></div>
                        <div class="form-group"><label class="col-sm-4 control-label"><?php echo __('Page ID');?></label><div class="col-sm-8"><input type="text" name="data[page_id]" value="<?php echo h(isset($facebookRow['page_id']) ? $facebookRow['page_id'] : '');?>" class="form-control" /><p class="help-block"><?php echo __('Optional default page ID. Route mappings can override by specific form/page IDs.');?></p></div></div>
                        <div class="form-group"><label class="col-sm-4 control-label"><?php echo __('Webhook URL');?></label><div class="col-sm-8"><div class="crm-webhook-copy"><input type="text" readonly="readonly" class="form-control" id="FacebookWebhookUrlInput" value="<?php echo h($facebookWebhookUrl);?>" /><button type="button" class="btn btn-default crm-copy-btn" data-copy-target="#FacebookWebhookUrlInput"><?php echo __('Copy');?></button></div></div></div>
                    </div>
                    <div class="col-md-6">
                        <h4 style="margin-top:0;"><?php echo __('Default Assignment');?></h4>
                        <p class="text-muted"><?php echo __('If no route mapping matches, imported Facebook leads will go to this selected portal/user.');?></p>
                        <div class="form-group"><label class="col-sm-4 control-label"><?php echo __('Agency');?></label><div class="col-sm-8"><?php echo $this->Form->select('project_id', $projectOptions, array('empty' => __('Please Select'), 'label' => false, 'class' => 'form-control', 'div' => false, 'id' => 'FacebookDefaultProjectId', 'value' => isset($facebookRow['default_project_id']) ? $facebookRow['default_project_id'] : 0));?></div></div>
                        <div class="form-group"><label class="col-sm-4 control-label"><?php echo __('User Level');?></label><div class="col-sm-8"><?php echo $this->Form->select('ugroup_id', $userLevelOptions, array('empty' => __('Please Select'), 'label' => false, 'class' => 'form-control', 'div' => false, 'id' => 'FacebookDefaultUgroupId', 'value' => isset($facebookRow['default_ugroup_id']) ? $facebookRow['default_ugroup_id'] : 0));?></div></div>
                        <div class="form-group"><label class="col-sm-4 control-label"><?php echo __('User');?></label><div class="col-sm-8" id="FacebookDefaultUserWrap"><?php echo $this->Form->select('user_id', $facebookUserOptions, array('empty' => __('Please Select'), 'label' => false, 'class' => 'form-control', 'div' => false, 'id' => 'FacebookDefaultUserId', 'value' => isset($facebookRow['default_user_id']) ? $facebookRow['default_user_id'] : 0));?></div></div>
                        <div class="form-group"><label class="col-sm-4 control-label"><?php echo __('Default Status');?></label><div class="col-sm-8"><?php echo $this->Form->select('status_id', $statusOptions, array('empty' => __('Use First Status'), 'label' => false, 'class' => 'form-control', 'div' => false, 'value' => isset($facebookRow['default_status_id']) ? $facebookRow['default_status_id'] : 0));?></div></div>
                        <div class="form-group"><label class="col-sm-4 control-label"><?php echo __('Lead Source');?></label><div class="col-sm-8"><input type="text" name="data[lead_source]" value="<?php echo h(isset($facebookRow['default_lead_source']) ? $facebookRow['default_lead_source'] : 'Facebook Lead Ads');?>" class="form-control" /></div></div>
                        <div class="form-group"><label class="col-sm-4 control-label"><?php echo __('Contact Link');?></label><div class="col-sm-8" style="padding-top:7px;"><label><input type="checkbox" name="data[auto_create_contact]" value="1" <?php if(!empty($facebookRow['auto_create_contact'])){ echo 'checked="checked"'; }?> /> <?php echo __('Auto create/link imported leads as customer contacts');?></label></div></div>
                    </div>
                </div>
                <div class="form-group text-left"><div class="col-sm-12"><button type="submit" class="btn btn-success"><span class="fa fa-save"></span> <?php echo __('Save Facebook Settings');?></button></div></div>
                <?php echo $this->Form->end();?>
            </div>

            <div class="crm-settings-panel <?php echo ($activeTab === 'whatsapp_cloud') ? 'is-active' : '';?>" id="crm-whatsapp-panel">
                <?php echo $this->Form->create(false, array('url' => array('action' => 'index', '?' => array('tab' => 'whatsapp_cloud')), 'class' => 'form-horizontal'));?>
                <?php echo $this->Form->hidden('provider', array('value' => 'whatsapp_cloud'));?>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group"><label class="col-sm-4 control-label"><?php echo __('Enabled');?></label><div class="col-sm-8" style="padding-top:7px;"><label><input type="checkbox" name="data[enabled]" value="1" <?php if(!empty($whatsappRow['enabled'])){ echo 'checked="checked"'; }?> /> <?php echo __('Enable WhatsApp Cloud API auto-import');?></label></div></div>
                        <div class="form-group"><label class="col-sm-4 control-label"><?php echo __('Business Account ID');?></label><div class="col-sm-8"><input type="text" name="data[business_account_id]" value="<?php echo h(isset($whatsappRow['business_account_id']) ? $whatsappRow['business_account_id'] : '');?>" class="form-control" /></div></div>
                        <div class="form-group"><label class="col-sm-4 control-label"><?php echo __('Phone Number ID');?></label><div class="col-sm-8"><input type="text" name="data[phone_number_id]" value="<?php echo h(isset($whatsappRow['phone_number_id']) ? $whatsappRow['phone_number_id'] : '');?>" class="form-control" /><p class="help-block"><?php echo __('Optional default number ID. Route mappings can override by exact WhatsApp number ID.');?></p></div></div>
                        <div class="form-group"><label class="col-sm-4 control-label"><?php echo __('Access Token');?></label><div class="col-sm-8"><textarea name="data[access_token]" class="form-control" rows="4"><?php echo h(isset($whatsappRow['access_token']) ? $whatsappRow['access_token'] : '');?></textarea></div></div>
                        <div class="form-group"><label class="col-sm-4 control-label"><?php echo __('Verify Token');?></label><div class="col-sm-8"><input type="text" name="data[verify_token]" value="<?php echo h(isset($whatsappRow['verify_token']) ? $whatsappRow['verify_token'] : '');?>" class="form-control" /></div></div>
                        <div class="form-group"><label class="col-sm-4 control-label"><?php echo __('Webhook URL');?></label><div class="col-sm-8"><div class="crm-webhook-copy"><input type="text" readonly="readonly" class="form-control" id="WhatsappWebhookUrlInput" value="<?php echo h($whatsappWebhookUrl);?>" /><button type="button" class="btn btn-default crm-copy-btn" data-copy-target="#WhatsappWebhookUrlInput"><?php echo __('Copy');?></button></div></div></div>
                    </div>
                    <div class="col-md-6">
                        <h4 style="margin-top:0;"><?php echo __('Default Assignment');?></h4>
                        <p class="text-muted"><?php echo __('If no route mapping matches, incoming WhatsApp leads will go to this selected portal/user.');?></p>
                        <div class="form-group"><label class="col-sm-4 control-label"><?php echo __('Agency');?></label><div class="col-sm-8"><?php echo $this->Form->select('project_id', $projectOptions, array('empty' => __('Please Select'), 'label' => false, 'class' => 'form-control', 'div' => false, 'id' => 'WhatsappDefaultProjectId', 'value' => isset($whatsappRow['default_project_id']) ? $whatsappRow['default_project_id'] : 0));?></div></div>
                        <div class="form-group"><label class="col-sm-4 control-label"><?php echo __('User Level');?></label><div class="col-sm-8"><?php echo $this->Form->select('ugroup_id', $userLevelOptions, array('empty' => __('Please Select'), 'label' => false, 'class' => 'form-control', 'div' => false, 'id' => 'WhatsappDefaultUgroupId', 'value' => isset($whatsappRow['default_ugroup_id']) ? $whatsappRow['default_ugroup_id'] : 0));?></div></div>
                        <div class="form-group"><label class="col-sm-4 control-label"><?php echo __('User');?></label><div class="col-sm-8" id="WhatsappDefaultUserWrap"><?php echo $this->Form->select('user_id', $whatsappUserOptions, array('empty' => __('Please Select'), 'label' => false, 'class' => 'form-control', 'div' => false, 'id' => 'WhatsappDefaultUserId', 'value' => isset($whatsappRow['default_user_id']) ? $whatsappRow['default_user_id'] : 0));?></div></div>
                        <div class="form-group"><label class="col-sm-4 control-label"><?php echo __('Default Status');?></label><div class="col-sm-8"><?php echo $this->Form->select('status_id', $statusOptions, array('empty' => __('Use First Status'), 'label' => false, 'class' => 'form-control', 'div' => false, 'value' => isset($whatsappRow['default_status_id']) ? $whatsappRow['default_status_id'] : 0));?></div></div>
                        <div class="form-group"><label class="col-sm-4 control-label"><?php echo __('Lead Source');?></label><div class="col-sm-8"><input type="text" name="data[lead_source]" value="<?php echo h(isset($whatsappRow['default_lead_source']) ? $whatsappRow['default_lead_source'] : 'WhatsApp Cloud API');?>" class="form-control" /></div></div>
                        <div class="form-group"><label class="col-sm-4 control-label"><?php echo __('Contact Link');?></label><div class="col-sm-8" style="padding-top:7px;"><label><input type="checkbox" name="data[auto_create_contact]" value="1" <?php if(!empty($whatsappRow['auto_create_contact'])){ echo 'checked="checked"'; }?> /> <?php echo __('Auto create/link imported leads as customer contacts');?></label></div></div>
                    </div>
                </div>
                <div class="form-group text-left"><div class="col-sm-12"><button type="submit" class="btn btn-success"><span class="fa fa-save"></span> <?php echo __('Save WhatsApp Settings');?></button></div></div>
                <?php echo $this->Form->end();?>
            </div>
        </div>
    </div>
</div>























