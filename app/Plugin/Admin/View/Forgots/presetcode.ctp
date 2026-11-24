
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
.wv-forgot-input{align-items:center;background:#f8fbff;border:1px solid #dfe9f5;border-radius:12px;display:flex;min-height:52px;overflow:hidden;}
.wv-forgot-input i{color:#ff0a46;flex:0 0 48px;font-size:16px;text-align:center;}
.wv-forgot-input .form-control{background:transparent!important;border:0!important;box-shadow:none!important;color:#07152a;font-size:18px;font-weight:900;height:52px;letter-spacing:3px;padding:0 14px 0 0;text-align:center;}
.wv-forgot-input:focus-within{border-color:#ff0a46;box-shadow:0 0 0 4px rgba(255,10,70,.10);}
.wv-forgot-hint{color:#64748b;font-size:12px;font-weight:800;margin-top:6px;text-align:center;}
.wv-forgot-submit{background:#ff0a46!important;border:0!important;border-radius:12px!important;box-shadow:0 14px 26px rgba(255,10,70,.25);color:#fff!important;font-size:14px;font-weight:900!important;height:50px;text-transform:uppercase;width:100%;}
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
                    <span><?php echo __('OTP Verification');?></span>
                </div>
            </div>
            <h1><?php echo __('Verification Code');?></h1>
            <p><?php echo __('Enter the verification code sent to your registered email and mobile number.');?></p>
        </div>
        <div class="wv-forgot-body">
            <?php echo $this->Session->flash();?>
            <?php echo $this->Form->create('Forgot', array('controller'=>'Forgots','action'=>'presetcode','name'=>'post_req','id'=>'post_req','class'=>'form-horizontal','role'=>'form'));?>
            <div class="wv-forgot-field">
                <label for="verificationcode"><?php echo __('Enter Verification Code');?></label>
                <div class="wv-forgot-input">
                    <i class="fa fa-shield"></i>
                    <?php echo $this->Form->input('verificationcode',array('id'=>'verificationcode','type'=>'text','required'=>true,'label'=>false,'class'=>'form-control','div'=>false,'placeholder'=>__('Code / OTP'),'autocomplete'=>'one-time-code','autofocus'=>true));?>
                </div>
                <div class="wv-forgot-hint"><?php echo __('Please check your email inbox, spam folder, or SMS.');?></div>
            </div>
            <button type="submit" class="btn wv-forgot-submit"><i class="fa fa-check-circle"></i> <?php echo __('Verify Code');?></button>
            <?php echo $this->Form->end();?>
            <div class="wv-forgot-links">
                <?php echo $this->Html->link(__('Resend Code'),array('controller'=>'Forgots','action'=>'password'));?>
                <?php echo $this->Html->link(__('Back to Login'),array('controller'=>'Users','action'=>'login_form'));?>
            </div>
            <div class="wv-forgot-note">
                <i class="fa fa-lock"></i><?php echo __('For security, verification codes expire after use. Do not share your code.');?>
            </div>
        </div>
    </div>
</div>