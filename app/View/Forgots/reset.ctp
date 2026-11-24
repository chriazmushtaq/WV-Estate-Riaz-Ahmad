<?php
$accountLogo = isset($frontLogo) && strlen(trim((string)$frontLogo))>0 ? $frontLogo : 'logo-website.fw.png';
$accountTitle = !empty($siteOrganization) ? $siteOrganization : (!empty($siteName) ? $siteName : 'White Villas');
$accountContact = isset($contact) ? $contact : '';
?>
<style type="text/css">
.wv-account-page{
    background:
        radial-gradient(circle at 12% 10%,rgba(124,249,255,.15),rgba(124,249,255,0) 28%),
        radial-gradient(circle at 88% 8%,rgba(255,10,70,.15),rgba(255,10,70,0) 30%),
        linear-gradient(135deg,#f4f7fb 0%,#eef4fb 54%,#f9fbff 100%);
    min-height:calc(100vh - 160px);
    padding:34px 0 44px;
}
.wv-account-shell{
    display:grid;
    grid-template-columns:minmax(0,1fr) 430px;
    margin:0 auto;
    max-width:1080px;
    padding:0 15px;
}
.wv-account-brand{
    background:linear-gradient(135deg,#07152a 0%,#102746 70%,#220f2f 140%);
    border-radius:18px 0 0 18px;
    box-shadow:0 22px 50px rgba(15,23,42,.16);
    color:#fff;
    min-height:520px;
    overflow:hidden;
    padding:42px;
    position:relative;
}
.wv-account-brand:before{
    background:linear-gradient(90deg,rgba(255,255,255,.06) 1px,transparent 1px),linear-gradient(rgba(255,255,255,.05) 1px,transparent 1px);
    background-size:42px 42px;
    content:"";
    inset:0;
    opacity:.20;
    position:absolute;
}
.wv-account-brand:after{
    background:radial-gradient(circle,rgba(124,249,255,.23),rgba(124,249,255,0) 62%);
    bottom:-115px;
    content:"";
    height:280px;
    position:absolute;
    right:-115px;
    width:280px;
}
.wv-account-logo,
.wv-account-brand h1,
.wv-account-brand p,
.wv-account-points{
    position:relative;
    z-index:2;
}
.wv-account-logo{
    align-items:center;
    display:flex;
    gap:13px;
    margin-bottom:72px;
}
.wv-account-logo img{
    background:#fff;
    border-radius:14px;
    box-shadow:0 14px 28px rgba(0,0,0,.20);
    max-height:58px;
    max-width:126px;
    object-fit:contain;
    padding:7px;
}
.wv-account-logo strong{
    color:#fff;
    display:block;
    font-size:17px;
    font-weight:900;
    line-height:1.25;
}
.wv-account-logo span{
    color:#7cf9ff;
    display:block;
    font-size:11px;
    font-weight:900;
    letter-spacing:.7px;
    margin-top:4px;
    text-transform:uppercase;
}
.wv-account-brand h1{
    color:#fff;
    font-size:40px;
    font-weight:900;
    letter-spacing:0;
    line-height:1.08;
    margin:0 0 16px;
    max-width:470px;
}
.wv-account-brand p{
    color:#dce8f7;
    font-size:14px;
    font-weight:800;
    line-height:1.7;
    margin:0;
    max-width:500px;
}
.wv-account-points{
    bottom:38px;
    display:grid;
    gap:12px;
    left:42px;
    position:absolute;
    right:42px;
}
.wv-account-point{
    align-items:center;
    color:#eaf4ff;
    display:flex;
    font-size:13px;
    font-weight:900;
    gap:10px;
}
.wv-account-point i{
    align-items:center;
    background:rgba(124,249,255,.15);
    border:1px solid rgba(124,249,255,.26);
    border-radius:10px;
    color:#7cf9ff;
    display:flex;
    height:34px;
    justify-content:center;
    width:34px;
}
.wv-account-card{
    background:#fff;
    border:1px solid #e2eaf4;
    border-left:0;
    border-radius:0 18px 18px 0;
    box-shadow:0 22px 50px rgba(15,23,42,.16);
    min-height:520px;
    padding:42px 38px;
}
.wv-account-card h2{
    color:#07152a;
    font-size:27px;
    font-weight:900;
    line-height:1.2;
    margin:0 0 8px;
}
.wv-account-subtitle{
    color:#64748b;
    font-size:13px;
    font-weight:800;
    line-height:1.55;
    margin:0 0 24px;
}
.wv-account-card .alert{
    border:0;
    border-radius:12px;
    font-size:12px;
    font-weight:900;
    margin-bottom:16px;
}
.wv-account-field{
    margin-bottom:16px;
}
.wv-account-field label{
    color:#334155;
    display:block;
    font-size:11px;
    font-weight:900;
    letter-spacing:.35px;
    margin-bottom:7px;
    text-transform:uppercase;
}
.wv-account-input{
    align-items:center;
    background:#f8fbff;
    border:1px solid #dfe9f5;
    border-radius:12px;
    display:flex;
    min-height:48px;
    overflow:hidden;
    position:relative;
}
.wv-account-input i.input-icon{
    color:#ff0a46;
    flex:0 0 48px;
    font-size:15px;
    text-align:center;
}
.wv-account-input .form-control{
    background:transparent!important;
    border:0!important;
    box-shadow:none!important;
    color:#07152a;
    font-size:14px;
    font-weight:900;
    height:48px;
    padding:0 10px 0 0;
}
.wv-account-input:focus-within{
    border-color:#ff0a46;
    box-shadow:0 0 0 4px rgba(255,10,70,.10);
}
.wv-eye-btn{
    align-items:center;
    background:transparent;
    border:0;
    color:#64748b;
    cursor:pointer;
    display:inline-flex;
    flex:0 0 42px;
    height:48px;
    justify-content:center;
    padding:0;
}
.wv-eye-btn:hover{
    color:#07152a;
}
.wv-pass-match{
    display:none;
    font-size:11px;
    font-weight:900;
    margin-top:5px;
}
.wv-pass-match.match-ok{
    color:#059669;
    display:block;
}
.wv-pass-match.match-err{
    color:#dc2626;
    display:block;
}
.wv-account-submit{
    background:#ff0a46!important;
    border:0!important;
    border-radius:12px!important;
    box-shadow:0 14px 26px rgba(255,10,70,.25);
    color:#fff!important;
    font-size:14px;
    font-weight:900!important;
    height:50px;
    letter-spacing:.2px;
    margin-top:6px;
    text-transform:uppercase;
    width:100%;
}
.wv-account-submit:hover,
.wv-account-submit:focus{
    background:#07152a!important;
    color:#fff!important;
}
.wv-account-links{
    align-items:center;
    display:flex;
    flex-wrap:wrap;
    gap:10px;
    justify-content:space-between;
    margin-top:18px;
}
.wv-account-links a{
    color:#102746!important;
    font-size:12px;
    font-weight:900;
    text-decoration:none!important;
}
.wv-account-links a:hover{
    color:#ff0a46!important;
}
.wv-account-note{
    background:#ecfdf5;
    border:1px solid #cdeee0;
    border-radius:12px;
    color:#047857;
    font-size:12px;
    font-weight:900;
    line-height:1.55;
    margin-top:20px;
    padding:12px 13px;
}
.wv-account-note i{
    margin-right:6px;
}
@media(max-width:991px){
    .wv-account-shell{
        grid-template-columns:1fr;
        max-width:540px;
    }
    .wv-account-brand{
        border-radius:18px 18px 0 0;
        min-height:auto;
        padding:30px;
    }
    .wv-account-logo{
        margin-bottom:30px;
    }
    .wv-account-brand h1{
        font-size:31px;
    }
    .wv-account-points{
        bottom:auto;
        left:auto;
        margin-top:26px;
        position:relative;
        right:auto;
    }
    .wv-account-card{
        border-left:1px solid #e2eaf4;
        border-radius:0 0 18px 18px;
        min-height:auto;
        padding:30px 24px;
    }
}
@media(max-width:575px){
    .wv-account-page{
        padding:18px 0 28px;
    }
    .wv-account-brand,
    .wv-account-card{
        padding:22px 18px;
    }
    .wv-account-brand h1{
        font-size:26px;
    }
    .wv-account-links{
        align-items:flex-start;
        flex-direction:column;
    }
}
</style>

<div class="main-wrapper">
    <div class="main">
        <div class="main-inner">
            <div class="content wv-account-page">
                <div class="wv-account-shell">
                    <section class="wv-account-brand">
                        <div class="wv-account-logo">
                            <?php echo $this->Html->image($accountLogo,array('alt'=>$accountTitle));?>
                            <div>
                                <strong><?php echo h($accountTitle);?></strong>
                                <span><?php echo __('Security Portal');?></span>
                            </div>
                        </div>
                        <h1><?php echo __('Create New Password');?></h1>
                        <p><?php echo __('Set a strong and secure password to protect your White Villas client account.');?></p>
                        <div class="wv-account-points">
                            <div class="wv-account-point"><i class="fa fa-lock"></i><span><?php echo __('At least 6 characters recommended');?></span></div>
                            <div class="wv-account-point"><i class="fa fa-shield"></i><span><?php echo __('End-to-end encrypted password storage');?></span></div>
                            <?php if(strlen(trim((string)$accountContact))>0){ ?>
                                <div class="wv-account-point"><i class="fa fa-phone"></i><span><?php echo __('Support');?> <?php echo h($accountContact);?></span></div>
                            <?php } ?>
                        </div>
                    </section>

                    <section class="wv-account-card">
                        <?php echo $this->Session->flash();?>
                        <h2><?php echo __('Reset Password');?></h2>
                        <p class="wv-account-subtitle"><?php echo __('Enter your new password below.');?></p>

                        <?php echo $this->Form->create('Forgot', array('controller'=>'Forgots','action'=>'reset','name'=>'post_req','id'=>'reset_form','class'=>'','role'=>'form'));?>
                        
                        <div class="wv-account-field">
                            <label for="password"><?php echo __('New Password');?></label>
                            <div class="wv-account-input">
                                <i class="fa fa-key input-icon"></i>
                                <?php echo $this->Form->input('password',array('id'=>'password','value'=>'','autocomplete'=>'new-password','label'=>false,'required'=>true,'class'=>'form-control','placeholder'=>__('Enter new password'),'div'=>false,'minlength'=>'6'));?>
                                <button type="button" class="wv-eye-btn" data-target="password" title="<?php echo __('Toggle password visibility');?>"><i class="fa fa-eye"></i></button>
                            </div>
                        </div>

                        <div class="wv-account-field">
                            <label for="con_password"><?php echo __('Confirm Password');?></label>
                            <div class="wv-account-input">
                                <i class="fa fa-lock input-icon"></i>
                                <input type="password" id="con_password" name="data[Forgot][con_password]" class="form-control" autocomplete="new-password" required="required" placeholder="<?php echo __('Repeat new password');?>" minlength="6">
                                <button type="button" class="wv-eye-btn" data-target="con_password" title="<?php echo __('Toggle password visibility');?>"><i class="fa fa-eye"></i></button>
                            </div>
                            <div id="password_match_msg" class="wv-pass-match"></div>
                        </div>

                        <button type="submit" id="submit_btn" class="btn wv-account-submit"><span class="fa fa-check"></span> <?php echo __('Update Password');?></button>
                        <?php echo $this->Form->end(null);?>

                        <div class="wv-account-links">
                            <?php echo $this->Html->link(__('Back to Login'),array('controller'=>'Clients','action'=>'login'));?>
                        </div>

                        <div class="wv-account-note">
                            <i class="fa fa-shield"></i><?php echo __('Use a combination of letters, numbers, and special symbols.');?>
                        </div>
                    </section>
                </div>
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









