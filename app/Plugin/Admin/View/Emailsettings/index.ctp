<script type="text/javascript">
    $(document).ready(function(){
	<?php if($this->request->data['Emailsetting']['type']=="Smtp"){?>$('#smtp').show();<?php }else{?>$('#smtp').hide();<?php }?>
	$('#EmailsettingTypeSmtp').click(function(){$('#smtp').show();});
	$('#EmailsettingTypeMail').click(function(){$('#smtp').hide();});
	});
</script>

<div class="page-header-content wv-page-header">
    <div class="page-title wv-page-title-box">
        <h4 class="wv-page-title"><i class="fa fa-envelope text-primary"></i> <?php echo __('Email Configuration & SMTP');?></h4>
        <small class="wv-page-subtitle"><?php echo __('Configure mail server delivery settings, credentials, and test SMTP connections');?></small>
    </div>
</div>

<div class="panel panel-flat">
    <div class="panel-body">
        <?php echo $this->Session->flash();?>
        <?php echo $this->Form->create('Emailsetting', array('controller' => 'Emailsettings', 'action' => 'index','name'=>'post_req','id'=>'post_req','class'=>'form-horizontal'));?>
        
        <div class="panel panel-default" style="border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 20px; overflow: hidden;">
            <div class="panel-heading" style="background:#f8fafc; padding: 12px 18px;">
                <strong style="color: #0f172a; font-size: 14px;"><i class="fa fa-server text-muted"></i> <?php echo __('Delivery Driver & Protocol');?></strong>
            </div>
            <div class="panel-body" style="padding: 18px;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="control-label col-xs-12 col-sm-3"><?php echo __('Mail Delivery Type');?></label>
                    <div class="col-xs-12 col-sm-9">
                        <div style="display:flex; gap:20px; align-items:center; min-height:38px;">
                            <?php echo $this->Form->input('type',array('type'=>'radio','options'=>array("Mail"=>" Localhost (sendmail)","Smtp"=>" External SMTP Server"),'legend'=>false,'before' => '<label class="radio-inline" style="font-weight:600; cursor:pointer;">','separator' => '</label><label class="radio-inline" style="font-weight:600; cursor:pointer;">','label' => false,'div'=>false));?>
                        </div>
                        <span class="help-block text-muted" style="margin-top:6px;"><?php echo __('Use SMTP for reliable transactional deliveries, SSL/TLS security, and cloud mail relays.'); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div id="smtp">
            <div class="panel panel-default" style="border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 20px; overflow: hidden;">
                <div class="panel-heading" style="background:#f8fafc; padding: 12px 18px;">
                    <strong style="color: #0f172a; font-size: 14px;"><i class="fa fa-lock text-muted"></i> <?php echo __('SMTP Host & Authentication Credentials');?></strong>
                </div>
                <div class="panel-body" style="padding: 18px;">
                    <div class="row">
                        <div class="col-xs-12 col-md-8">
                            <div class="form-group required">
                                <label class="control-label"><?php echo __('Server Host / Hostname');?></label>
                                <?php echo $this->Form->input('host',array('label' => false,'class'=>'form-control','placeholder'=>__('e.g. mail.whitealpinelodges.com or smtp.gmail.com'),'div'=>false));?>
                            </div>
                        </div>
                        <div class="col-xs-12 col-md-4">
                            <div class="form-group required">
                                <label class="control-label"><?php echo __('Port (TLS: 587, SSL: 465)');?></label>
                                <?php echo $this->Form->input('port',array('label' => false,'class'=>'form-control','placeholder'=>__('e.g. 587'),'div'=>false));?>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-xs-12 col-md-6">
                            <div class="form-group required">
                                <label class="control-label"><?php echo __('SMTP Username / Email');?></label>
                                <?php echo $this->Form->input('username',array('label' => false,'class'=>'form-control','placeholder'=>__('e.g. notifications@whitealpinelodges.com'),'div'=>false));?>
                            </div>
                        </div>
                        <div class="col-xs-12 col-md-6">
                            <div class="form-group required">
                                <label class="control-label"><?php echo __('SMTP Password');?></label>
                                <div class="input-group">
                                    <?php echo $this->Form->input('password',array(
                                        'type'=>'text',
                                        'id'=>'smtp_password_field',
                                        'label' => false,
                                        'class'=>'form-control',
                                        'placeholder'=>__('Password'),
                                        'autocomplete'=>'new-password',
                                        'value'=>(!empty($this->request->data['Emailsetting']['password']) ? $this->request->data['Emailsetting']['password'] : ''),
                                        'div'=>false
                                    ));?>
                                    <span class="input-group-btn">
                                        <button type="button" class="btn btn-default" id="toggle_pwd_btn" onclick="toggleSmtpPasswordVisibility()" title="Toggle Mask / Unmask Password" style="height:42px;">
                                            <i class="fa fa-eye" id="toggle_pwd_icon"></i>
                                        </button>
                                    </span>
                                </div>
                                <span class="help-block text-muted" style="font-size:11.5px; margin-top:5px;">
                                    <i class="fa fa-shield text-success"></i> <?php echo __('Stored securely for SMTP authentication.'); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="wv-form-footer form-actions">
            <button type="button" onclick="promptTestEmail()" class="btn btn-default"><i class="fa fa-paper-plane text-primary"></i> <?php echo __('Send Test Email');?></button>
            <button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> <?php echo __('Save Settings');?></button>
        </div>
        <?php echo $this->Form->end(null);?>
    </div>
</div>

<script type="text/javascript">
function toggleSmtpPasswordVisibility() {
    var f = document.getElementById('smtp_password_field');
    var icon = document.getElementById('toggle_pwd_icon');
    if (f.type === 'text') {
        f.type = 'password';
        icon.className = 'fa fa-eye-slash';
    } else {
        f.type = 'text';
        icon.className = 'fa fa-eye';
    }
}

function promptTestEmail() {
    var email = prompt("Enter recipient email address to test your SMTP connection:", "admin@whitealpinelodges.com");
    if(email && email.indexOf('@') > 0) {
        var form = document.getElementById('post_req');
        var input = document.getElementById('temp_test_email_field');
        if(!input) {
            input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'test_email';
            input.id = 'temp_test_email_field';
            form.appendChild(input);
        }
        input.value = email;
        form.action = '<?php echo $this->Html->url(array("controller" => "Emailsettings", "action" => "test_email")); ?>';
        form.submit();
    }
}
</script>
