<script type="text/javascript">
    $(document).ready(function(){
	<?php if($this->request->data['Emailsetting']['type']=="Smtp"){?>$('#smtp').show();<?php }else{?>$('#smtp').hide();<?php }?>
	$('#EmailsettingTypeSmtp').click(function(){$('#smtp').show();});
	$('#EmailsettingTypeMail').click(function(){$('#smtp').hide();});
	});
</script>
<div class="page-title"> <div class="title-env"> <h1 class="title"><?php echo __('Email Settings');?></h1></div></div>
<?php echo $this->Session->flash();?>
   <div class="col-sm-12">    
        <div class="panel panel-default">
            <div class="panel-body">
                <?php echo $this->Form->create('Emailsetting', array('controller' => 'Emailsettings', 'action' => 'index','name'=>'post_req','id'=>'post_req','class'=>'form-horizontal'));?>
                    <div class="form-group">
                        <label for="site_name" class="col-sm-2 control-label"><?php echo __('Email Type');?></label>
                        <div class="col-sm-4">
				<?php echo $this->Form->input('type',array('type'=>'radio','options'=>array("Mail"=>"LOCALHOST","Smtp"=>"SMTP"),'legend'=>false,'before' => '<label class="radio-inline">','separator' => '</label><label class="radio-inline">','label' => false,'div'=>false));?>
			</div>                        
                    </div>
		    <div id="smtp">
			<div class="form-group">
			    <label for="site_name" class="col-sm-2 control-label"><?php echo __('Server Name/Host');?></label>
			    <div class="col-sm-4">
			       <?php echo $this->Form->input('host',array('label' => false,'class'=>'form-control','placeholder'=>__('Server Name'),'div'=>false));?>
			    </div>
			    <label for="site_name" class="col-sm-2 control-label"><?php echo __('Port');?></label>
			    <div class="col-sm-4">
			       <?php echo $this->Form->input('port',array('label' => false,'class'=>'form-control','placeholder'=>__('Port'),'div'=>false));?>
			    </div>
			</div>
			<div class="form-group">
			    <label for="site_name" class="col-sm-2 control-label"><?php echo __('User Name');?></label>
			    <div class="col-sm-4">
			       <?php echo $this->Form->input('username',array('label' => false,'class'=>'form-control','placeholder'=>__('User Name'),'div'=>false));?>
			    </div>
			    <label for="site_name" class="col-sm-2 control-label"><?php echo __('Password');?></label>
			    <div class="col-sm-4">
				<div class="input-group">
				   <?php echo $this->Form->input('password',array(
					'type'=>'text',
					'id'=>'smtp_password_field',
					'label' => false,
					'class'=>'form-control',
					'placeholder'=>__('Password (Visible Plain Text)'),
					'autocomplete'=>'new-password',
					'value'=>(!empty($this->request->data['Emailsetting']['password']) ? $this->request->data['Emailsetting']['password'] : ''),
					'div'=>false
				   ));?>
				   <span class="input-group-btn">
					<button type="button" class="btn btn-default" id="toggle_pwd_btn" onclick="toggleSmtpPasswordVisibility()" title="Toggle Mask / Unmask Password">
					    <i class="fa fa-eye" id="toggle_pwd_icon"></i>
					</button>
				   </span>
				</div>
				<span class="help-block" style="font-size:11px; margin-top:3px; color:#555;">
				    <i class="fa fa-unlock-alt text-success"></i> Password is displayed in plain text and stored directly for SMTP authentication (never hashed).
				</span>
			    </div>                        
			</div>
		    </div>
                    <div class="form-group text-left">
                        <div class="col-sm-offset-2 col-sm-10">
                            <button type="submit" class="btn btn-success"><span class="fa fa-refresh"></span>&nbsp;<?php echo __('Save Settings');?></button>
                            <button type="button" onclick="promptTestEmail()" class="btn btn-info" style="margin-left:10px;"><span class="fa fa-paper-plane"></span>&nbsp;<?php echo __('Send Test Email');?></button>
                        </div>
                    </div>
                <?php echo$this->Form->end(null);?>
                </div>
            </div>
        </div>
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