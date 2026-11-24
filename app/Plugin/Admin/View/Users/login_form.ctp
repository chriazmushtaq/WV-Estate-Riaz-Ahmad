<?php
$loginToken = isset($loginCsrfToken) ? (string)$loginCsrfToken : '';
$loginLogo = strlen(trim((string)$frontLogo))>0 ? $frontLogo : 'logo-website.fw.png';
$loginTitle = !empty($siteOrganization) ? $siteOrganization : $siteName;
?>
<style type="text/css">
body{
    background:#eef3f8!important;
}
body > .page-container{
    background:#eef3f8;
    margin:0!important;
    min-height:100vh;
    padding:0!important;
}
body > .page-container > br,
body > .page-container > center{
    display:none!important;
}
.wv-admin-login{
    align-items:center;
    background:
        radial-gradient(circle at 10% 14%,rgba(14,165,233,.10),rgba(14,165,233,0) 24%),
        radial-gradient(circle at 90% 10%,rgba(37,99,235,.08),rgba(37,99,235,0) 24%),
        linear-gradient(180deg,#f8fbff 0%,#eef3f8 100%);
    display:flex;
    justify-content:center;
    min-height:100vh;
    overflow:hidden;
    padding:26px 16px;
    position:relative;
}
.wv-admin-login:before{
    background:linear-gradient(90deg,rgba(15,23,42,.04) 1px,transparent 1px),linear-gradient(rgba(15,23,42,.035) 1px,transparent 1px);
    background-size:42px 42px;
    content:"";
    inset:0;
    opacity:.55;
    position:absolute;
}
.wv-login-shell{
    display:grid;
    gap:0;
    grid-template-columns:minmax(0,1fr) 430px;
    max-width:1100px;
    position:relative;
    width:100%;
    z-index:2;
}
.wv-login-brand{
    background:linear-gradient(180deg,#ffffff 0%,#f4f8fd 100%);
    border:1px solid #dbe5f1;
    border-radius:22px 0 0 22px;
    box-shadow:0 24px 54px rgba(15,23,42,.10);
    color:#07152a;
    min-height:560px;
    overflow:hidden;
    padding:42px;
    position:relative;
}
.wv-login-brand:after{
    background:linear-gradient(135deg,rgba(14,165,233,.14),rgba(37,99,235,.10));
    border-radius:999px;
    bottom:-120px;
    content:"";
    height:280px;
    position:absolute;
    right:-110px;
    width:280px;
}
.wv-login-logo{
    align-items:center;
    display:flex;
    gap:14px;
    margin-bottom:74px;
    position:relative;
    z-index:2;
}
.wv-login-logo img{
    background:#fff;
    border-radius:14px;
    box-shadow:0 14px 28px rgba(15,23,42,.10);
    max-height:58px;
    max-width:120px;
    object-fit:contain;
    padding:7px;
}
.wv-login-logo strong{
    color:#07152a;
    display:block;
    font-size:17px;
    font-weight:900;
    line-height:1.25;
}
.wv-login-logo span{
    color:#0f4c81;
    display:block;
    font-size:11px;
    font-weight:900;
    letter-spacing:.7px;
    margin-top:4px;
    text-transform:uppercase;
}
.wv-login-brand h1{
    color:#07152a;
    font-size:42px;
    font-weight:900;
    letter-spacing:0;
    line-height:1.05;
    margin:0 0 16px;
    max-width:460px;
    position:relative;
    z-index:2;
}
.wv-login-brand p{
    color:#475569;
    font-size:14px;
    font-weight:800;
    line-height:1.7;
    margin:0;
    max-width:480px;
    position:relative;
    z-index:2;
}
.wv-login-points{
    bottom:38px;
    display:grid;
    gap:12px;
    left:42px;
    position:absolute;
    right:42px;
    z-index:2;
}
.wv-login-point{
    align-items:center;
    color:#1e293b;
    display:flex;
    font-size:13px;
    font-weight:900;
    gap:10px;
}
.wv-login-point i{
    align-items:center;
    background:#eff6ff;
    border:1px solid #cfe0f5;
    border-radius:10px;
    color:#0f4c81;
    display:flex;
    height:34px;
    justify-content:center;
    width:34px;
}
.wv-login-card{
    background:#ffffff;
    border:1px solid #dbe5f1;
    border-radius:0 22px 22px 0;
    box-shadow:0 24px 54px rgba(15,23,42,.12);
    min-height:560px;
    padding:42px 38px;
}
.wv-login-card h2{
    color:#07152a;
    font-size:26px;
    font-weight:900;
    line-height:1.2;
    margin:0 0 8px;
}
.wv-login-card .wv-login-subtitle{
    color:#64748b;
    font-size:13px;
    font-weight:800;
    line-height:1.55;
    margin:0 0 24px;
}
.wv-login-card .alert{
    border:1px solid #dbe5f1;
    border-radius:12px;
    font-size:12px;
    font-weight:900;
    margin-bottom:16px;
}
.wv-login-field{
    margin-bottom:16px;
}
.wv-login-field label{
    color:#334155;
    display:block;
    font-size:11px;
    font-weight:900;
    letter-spacing:.35px;
    margin-bottom:7px;
    text-transform:uppercase;
}
.wv-login-input{
    align-items:center;
    background:#ffffff;
    border:1px solid #dfe9f5;
    border-radius:12px;
    display:flex;
    min-height:48px;
    overflow:hidden;
}
.wv-login-input i{
    color:#0f4c81;
    flex:0 0 48px;
    text-align:center;
}
.wv-login-input .form-control{
    background:transparent!important;
    border:0!important;
    box-shadow:none!important;
    color:#07152a;
    font-size:14px;
    font-weight:900;
    height:48px;
    padding:0 14px 0 0;
}
.wv-login-input .form-control:focus{
    box-shadow:none!important;
}
.wv-login-input:focus-within{
    border-color:#0f4c81;
    box-shadow:0 0 0 4px rgba(15,76,129,.10);
}
.wv-login-hidden-field{
    height:0!important;
    left:-9999px!important;
    opacity:0!important;
    overflow:hidden!important;
    position:absolute!important;
    width:0!important;
}
.wv-login-submit{
    background:linear-gradient(135deg,#0f4c81 0%,#0b6aa7 100%)!important;
    border:0!important;
    border-radius:12px!important;
    box-shadow:0 14px 26px rgba(15,76,129,.20);
    color:#fff!important;
    font-size:14px;
    font-weight:900!important;
    height:50px;
    letter-spacing:.2px;
    margin-top:4px;
    text-transform:uppercase;
    width:100%;
}
.wv-login-submit:hover,
.wv-login-submit:focus{
    background:#07152a!important;
    color:#fff!important;
}
.wv-login-links{
    align-items:center;
    display:flex;
    gap:10px;
    justify-content:space-between;
    margin-top:18px;
}
.wv-login-links a{
    color:#102746!important;
    font-size:12px;
    font-weight:900;
    text-decoration:none!important;
}
.wv-login-links a:hover{
    color:#0b6aa7!important;
}
.wv-login-note{
    background:#f8fbff;
    border:1px solid #dfe9f5;
    border-radius:12px;
    color:#64748b;
    font-size:12px;
    font-weight:800;
    line-height:1.55;
    margin-top:22px;
    padding:12px 13px;
}
.wv-login-note i{
    color:#047857;
    margin-right:6px;
}
@media(max-width:991px){
    .wv-login-shell{
        grid-template-columns:1fr;
        max-width:560px;
    }
    .wv-login-brand{
        display:none;
    }
    .wv-login-card{
        border-radius:22px;
        min-height:auto;
        padding:30px 24px;
    }
}
@media(max-width:575px){
    .wv-admin-login{
        padding:12px;
    }
    .wv-login-card{
        border-radius:18px;
        box-shadow:0 14px 32px rgba(15,23,42,.10);
        min-height:calc(100vh - 24px);
        padding:22px 18px;
    }
    .wv-login-links{
        align-items:flex-start;
        flex-direction:column;
    }
}
</style>

<div class="wv-admin-login">
    <div class="wv-login-shell">
        <section class="wv-login-brand">
            <div class="wv-login-logo">
                <?php echo $this->Html->image($loginLogo,array('alt'=>$siteName));?>
                <div>
                    <strong><?php echo h($loginTitle);?></strong>
                    <span><?php echo __('Administration');?></span>
                </div>
            </div>
            <h1><?php echo __('%s Admin', h($loginTitle));?></h1>
            <p><?php echo __('Control property inventory, CRM operations, payments, reports, and system users from the secure staff portal.');?></p>
            <div class="wv-login-points">
                <div class="wv-login-point"><i class="fa fa-lock"></i><span><?php echo __('Protected staff access');?></span></div>
                <div class="wv-login-point"><i class="fa fa-line-chart"></i><span><?php echo __('Business dashboard and reporting');?></span></div>
                <div class="wv-login-point"><i class="fa fa-shield"></i><span><?php echo __('Session and attempt protection enabled');?></span></div>
            </div>
        </section>

        <section class="wv-login-card">
            <?php echo $this->Session->flash();?>
            <h2><?php echo __('Sign in');?></h2>
            <p class="wv-login-subtitle"><?php echo __('Use your authorized admin account to continue.');?></p>

            <?php echo $this->Form->create('User', array('url'=>array('controller'=>'Users','action'=>'login_form'),'name'=>'post_req','id'=>'post_req','autocomplete'=>'off'));?>
            <?php echo $this->Form->hidden('login_token',array('value'=>$loginToken));?>
            <div class="wv-login-hidden-field" aria-hidden="true">
                <?php echo $this->Form->input('company_site',array('label'=>false,'div'=>false,'autocomplete'=>'off','tabindex'=>'-1'));?>
            </div>

            <div class="wv-login-field">
                <label for="username"><?php echo __('Username or Email');?></label>
                <div class="wv-login-input">
                    <i class="fa fa-user-o"></i>
                    <?php echo $this->Form->input('username',array('id'=>'username','type'=>'text','required'=>true,'label'=>false,'class'=>'form-control','div'=>false,'placeholder'=>__('Enter username or email'),'autocomplete'=>'username','maxlength'=>120));?>
                </div>
            </div>

            <div class="wv-login-field">
                <label for="password"><?php echo __('Password');?></label>
                <div class="wv-login-input">
                    <i class="fa fa-key"></i>
                    <?php echo $this->Form->input('password',array('id'=>'password','type'=>'password','required'=>true,'label'=>false,'class'=>'form-control','div'=>false,'placeholder'=>__('Enter password'),'autocomplete'=>'current-password','maxlength'=>120));?>
                </div>
            </div>

            <button type="submit" class="btn wv-login-submit"><i class="fa fa-sign-in"></i> <?php echo __('Login');?></button>
            <?php echo $this->Form->end();?>

            <div class="wv-login-links">
                <?php echo $this->Html->link(__('Forgot Password'),array('controller'=>'Forgots','action'=>'password'));?>
                <?php echo $this->Html->link(__('Forgot User Name'),array('controller'=>'Forgots','action'=>'username'));?>
            </div>

            <div class="wv-login-note">
                <i class="fa fa-check-circle"></i><?php echo __('Login attempts are monitored for account protection.');?>
            </div>
        </section>
    </div>
</div>
