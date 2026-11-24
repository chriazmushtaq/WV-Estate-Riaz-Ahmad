<?php
$forgotLogo = strlen(trim((string)$frontLogo))>0 ? $frontLogo : 'logo-website.fw.png';
$forgotTitle = !empty($siteOrganization) ? $siteOrganization : $siteName;
?>
<style type="text/css">
html, body{background:#07152a!important;display:block!important;margin:0!important;min-height:100vh!important;padding:0!important;width:100%!important;}
body > .page-container, .page-container{background:#07152a!important;margin:0!important;min-height:100vh!important;padding:0!important;width:100%!important;}
body > .page-container > br,
body > .page-container > center,
.page-container > br,
.page-container > center{display:none!important;}
.wv-forgot-page{align-items:center;background:radial-gradient(circle at 12% 18%,rgba(124,249,255,.18),rgba(124,249,255,0) 28%),radial-gradient(circle at 88% 12%,rgba(255,10,70,.20),rgba(255,10,70,0) 30%),linear-gradient(135deg,#07152a 0%,#102746 66%,#220f2f 130%);display:flex;justify-content:center;min-height:100vh;padding:26px 16px;position:relative;width:100%;}
.wv-forgot-page:before{background:linear-gradient(90deg,rgba(255,255,255,.06) 1px,transparent 1px),linear-gradient(rgba(255,255,255,.05) 1px,transparent 1px);background-size:42px 42px;content:"";inset:0;opacity:.24;position:absolute;}
.wv-forgot-card{background:#fff;border-radius:22px;box-shadow:0 26px 60px rgba(0,0,0,.30);max-width:460px;overflow:hidden;position:relative;width:100%;z-index:2;}
.wv-forgot-head{background:linear-gradient(135deg,#07152a 0%,#102746 74%,#ff0a46 165%);color:#fff;padding:28px 30px 34px;position:relative;}
.wv-forgot-head:after{background:radial-gradient(circle,rgba(124,249,255,.22),rgba(124,249,255,0) 60%);content:"";height:180px;position:absolute;right:-70px;top:-70px;width:180px;}
.wv-forgot-logo{align-items:center;display:flex;gap:12px;margin-bottom:24px;position:relative;z-index:2;}
.wv-forgot-logo img{background:#fff;border-radius:12px;box-shadow:0 12px 24px rgba(0,0,0,.20);max-height:52px;max-width:108px;object-fit:contain;padding:7px;}
.wv-forgot-logo strong{color:#fff;display:block;font-size:15px;font-weight:900;line-height:1.25;}
.wv-forgot-logo span{color:#7cf9ff;display:block;font-size:10px;font-weight:900;letter-spacing:.7px;margin-top:4px;text-transform:uppercase;}
.wv-forgot-head h1{color:#fff;font-size:28px;font-weight:900;line-height:1.15;margin:0 0 9px;position:relative;z-index:2;}
.wv-forgot-head p{color:#dce8f7;font-size:13px;font-weight:800;line-height:1.6;margin:0;position:relative;z-index:2;}
.wv-forgot-body{padding:30px;}
.wv-forgot-body .alert{border:0;border-radius:12px;font-size:12px;font-weight:900;margin-bottom:16px;}
.wv-forgot-field{margin-bottom:17px;}
.wv-forgot-field label{color:#334155;display:block;font-size:11px;font-weight:900;letter-spacing:.35px;margin-bottom:7px;text-transform:uppercase;}
.wv-forgot-input{align-items:center;background:#f8fbff;border:1px solid #dfe9f5;border-radius:12px;display:flex;min-height:48px;overflow:hidden;position:relative;}
.wv-forgot-input i.input-icon{color:#ff0a46;flex:0 0 48px;font-size:15px;text-align:center;}
.wv-forgot-input .form-control{background:transparent!important;border:0!important;box-shadow:none!important;color:#07152a;font-size:14px;font-weight:900;height:48px;padding:0 10px 0 0;}
.wv-forgot-input:focus-within{border-color:#ff0a46;box-shadow:0 0 0 4px rgba(255,10,70,.10);}
.wv-eye-btn{align-items:center;background:transparent;border:0;color:#64748b;cursor:pointer;display:inline-flex;flex:0 0 42px;height:48px;justify-content:center;padding:0;}
.wv-eye-btn:hover{color:#07152a;}
.wv-pass-match{display:none;font-size:11px;font-weight:900;margin-top:5px;}
.wv-pass-match.match-ok{color:#059669;display:block;}
.wv-pass-match.match-err{color:#dc2626;display:block;}
.wv-forgot-submit{background:#ff0a46!important;border:0!important;border-radius:12px!important;box-shadow:0 14px 26px rgba(255,10,70,.25);color:#fff!important;font-size:14px;font-weight:900!important;height:50px;margin-top:6px;text-transform:uppercase;width:100%;}
.wv-forgot-submit:hover,.wv-forgot-submit:focus{background:#07152a!important;color:#fff!important;}
.wv-forgot-links{align-items:center;display:flex;gap:10px;justify-content:space-between;margin-top:18px;}
.wv-forgot-links a{color:#102746!important;font-size:12px;font-weight:900;text-decoration:none!important;}
.wv-forgot-links a:hover{color:#ff0a46!important;}
.wv-forgot-note{background:#f8fbff;border:1px solid #dfe9f5;border-radius:12px;color:#64748b;font-size:12px;font-weight:800;line-height:1.55;margin-top:22px;padding:12px 13px;}
.wv-forgot-note i{color:#047857;margin-right:6px;}
@media(max-width:575px){.wv-forgot-page{padding:14px}.wv-forgot-head,.wv-forgot-body{padding:22px 18px}.wv-forgot-head h1{font-size:24px}.wv-forgot-links{align-items:flex-start;flex-direction:column;}}
</style>

<div class="wv-forgot-page">
    <div class="wv-forgot-card">
        <div class="wv-forgot-head">
            <div class="wv-forgot-logo">
                <?php echo $this->Html->image($forgotLogo,array('alt'=>$siteName));?>
                <div>
                    <strong><?php echo h($forgotTitle);?></strong>
                    <span><?php echo __('Security Portal');?></span>
                </div>
            </div>
            <h1><?php echo __('Reset Password');?></h1>
            <p><?php echo __('Create a new secure password for your account.');?></p>
        </div>
        <div class="wv-forgot-body">
            <?php echo $this->Session->flash();?>
            <?php echo $this->Form->create('Forgot', array('controller'=>'Forgots','action'=>'reset','name'=>'post_req','id'=>'reset_form','class'=>'form-horizontal','role'=>'form'));?>
            
            <div class="wv-forgot-field">
                <label for="password"><?php echo __('New Password');?></label>
                <div class="wv-forgot-input">
                    <i class="fa fa-key input-icon"></i>
                    <?php echo $this->Form->input('password',array('id'=>'password','value'=>'','label'=>false,'class'=>'form-control','autocomplete'=>'new-password','div'=>false,'type'=>'password','required'=>true,'placeholder'=>__('Enter new password'),'minlength'=>'6'));?>
                    <button type="button" class="wv-eye-btn" data-target="password" title="<?php echo __('Toggle password visibility');?>"><i class="fa fa-eye"></i></button>
                </div>
            </div>

            <div class="wv-forgot-field">
                <label for="con_password"><?php echo __('Confirm Password');?></label>
                <div class="wv-forgot-input">
                    <i class="fa fa-lock input-icon"></i>
                    <input type="password" id="con_password" name="data[Forgot][con_password]" class="form-control" autocomplete="new-password" required="required" placeholder="<?php echo __('Repeat new password');?>" minlength="6">
                    <button type="button" class="wv-eye-btn" data-target="con_password" title="<?php echo __('Toggle password visibility');?>"><i class="fa fa-eye"></i></button>
                </div>
                <div id="password_match_msg" class="wv-pass-match"></div>
            </div>

            <button type="submit" id="submit_btn" class="btn wv-forgot-submit"><i class="fa fa-check"></i> <?php echo __('Update Password');?></button>
            <?php echo $this->Form->end();?>
            
            <div class="wv-forgot-links">
                <?php echo $this->Html->link(__('Back to Verification'),array('controller'=>'Forgots','action'=>'presetcode'));?>
                <?php echo $this->Html->link(__('Back to Login'),array('controller'=>'Users','action'=>'login_form'));?>
            </div>
            <div class="wv-forgot-note">
                <i class="fa fa-shield"></i><?php echo __('Use at least 6 characters with a combination of letters and numbers.');?>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function(){
    function checkPasswords(){
        var p1 = $('#password').val();
        var p2 = $('#con_password').val();
        var $msg = $('#password_match_msg');
        if(p2.length === 0){
            $msg.removeClass('match-ok match-err').hide();
            document.getElementById('con_password').setCustomValidity('');
            return;
        }
        if(p1 === p2){
            $msg.removeClass('match-err').addClass('match-ok').html('<i class="fa fa-check-circle"></i> <?php echo __("Passwords match");?>').show();
            document.getElementById('con_password').setCustomValidity('');
        } else {
            $msg.removeClass('match-ok').addClass('match-err').html('<i class="fa fa-times-circle"></i> <?php echo __("Passwords do not match");?>').show();
            document.getElementById('con_password').setCustomValidity("<?php echo __('Passwords do not match');?>");
        }
    }
    $('#password, #con_password').on('input change keyup', checkPasswords);

    $('.wv-eye-btn').on('click', function(e){
        e.preventDefault();
        var targetId = $(this).data('target');
        var $input = $('#' + targetId);
        var $icon = $(this).find('i');
        if($input.attr('type') === 'password'){
            $input.attr('type', 'text');
            $icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            $input.attr('type', 'password');
            $icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });
});
</script>