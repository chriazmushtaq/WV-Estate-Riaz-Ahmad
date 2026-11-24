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
    grid-template-columns:360px minmax(0,1fr);
    margin:0 auto;
    max-width:1120px;
    padding:0 15px;
}
.wv-account-brand{
    background:linear-gradient(135deg,#07152a 0%,#102746 70%,#220f2f 140%);
    border-radius:18px 0 0 18px;
    box-shadow:0 22px 50px rgba(15,23,42,.16);
    color:#fff;
    min-height:570px;
    overflow:hidden;
    padding:38px 34px;
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
    margin-bottom:68px;
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
    font-size:36px;
    font-weight:900;
    letter-spacing:0;
    line-height:1.1;
    margin:0 0 16px;
}
.wv-account-brand p{
    color:#dce8f7;
    font-size:13px;
    font-weight:800;
    line-height:1.7;
    margin:0;
}
.wv-account-points{
    bottom:34px;
    display:grid;
    gap:12px;
    left:34px;
    position:absolute;
    right:34px;
}
.wv-account-point{
    align-items:center;
    color:#eaf4ff;
    display:flex;
    font-size:12px;
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
    min-height:570px;
    padding:38px;
}
.wv-account-card h2{
    color:#07152a;
    font-size:28px;
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
.wv-account-form-grid{
    display:grid;
    gap:16px;
    grid-template-columns:repeat(2,minmax(0,1fr));
}
.wv-account-field{
    margin-bottom:0;
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
}
.wv-account-input i{
    color:#ff0a46;
    flex:0 0 48px;
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
    padding:0 14px 0 0;
}
.wv-account-input:focus-within{
    border-color:#ff0a46;
    box-shadow:0 0 0 4px rgba(255,10,70,.10);
}
.wv-account-captcha{
    background:#f8fbff;
    border:1px solid #dfe9f5;
    border-radius:12px;
    padding:12px;
}
.wv-account-captcha img{
    border-radius:8px;
    display:block;
    margin-bottom:10px;
    max-width:100%;
}
.wv-account-captcha input,
.wv-account-captcha .form-control{
    background:#fff!important;
    border:1px solid #dfe9f5!important;
    border-radius:9px!important;
    box-shadow:none!important;
    color:#07152a;
    font-weight:900;
    height:40px;
    width:100%;
}
.wv-account-actions{
    align-items:center;
    display:flex;
    flex-wrap:wrap;
    gap:14px;
    justify-content:space-between;
    margin-top:22px;
}
.wv-account-submit{
    background:#ff0a46!important;
    border:0!important;
    border-radius:12px!important;
    box-shadow:0 14px 26px rgba(255,10,70,.25);
    color:#fff!important;
    font-size:13px;
    font-weight:900!important;
    height:48px;
    letter-spacing:.2px;
    min-width:190px;
    padding:0 22px;
    text-transform:uppercase;
}
.wv-account-submit:hover,
.wv-account-submit:focus{
    background:#07152a!important;
    color:#fff!important;
}
.wv-account-link{
    color:#102746!important;
    font-size:12px;
    font-weight:900;
    text-decoration:none!important;
}
.wv-account-link:hover{
    color:#ff0a46!important;
}
@media(max-width:991px){
    .wv-account-shell{
        grid-template-columns:1fr;
        max-width:680px;
    }
    .wv-account-brand{
        border-radius:18px 18px 0 0;
        min-height:auto;
        padding:30px;
    }
    .wv-account-logo{
        margin-bottom:30px;
    }
    .wv-account-points{
        bottom:auto;
        left:auto;
        margin-top:24px;
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
        font-size:27px;
    }
    .wv-account-form-grid{
        grid-template-columns:1fr;
    }
    .wv-account-actions{
        align-items:stretch;
        flex-direction:column;
    }
    .wv-account-submit{
        width:100%;
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
                                <span><?php echo __('Client Portal');?></span>
                            </div>
                        </div>
                        <h1><?php echo __('Create Your Account');?></h1>
                        <p><?php echo __('Register once and manage your White Villas client services, property inquiries, and account communication from one place.');?></p>
                        <div class="wv-account-points">
                            <div class="wv-account-point"><i class="fa fa-shield"></i><span><?php echo __('Secure client account access');?></span></div>
                            <div class="wv-account-point"><i class="fa fa-envelope"></i><span><?php echo __('Email verification keeps records protected');?></span></div>
                            <?php if(strlen(trim((string)$accountContact))>0){ ?>
                                <div class="wv-account-point"><i class="fa fa-phone"></i><span><?php echo __('Support');?> <?php echo h($accountContact);?></span></div>
                            <?php } ?>
                        </div>
                    </section>

                    <section class="wv-account-card">
                        <?php echo $this->Session->flash();?>
                        <h2><?php echo __('Create Account');?></h2>
                        <p class="wv-account-subtitle"><?php echo __('Enter your details below. You can sign in after your account is created and verified.');?></p>

                        <?php echo $this->Form->create('Register', array('controller'=>'Register','action'=>'index','name'=>'post_req','id'=>'post_req','class'=>'','role'=>'form','type'=>'file'));?>
                        <div class="wv-account-form-grid">
                            <div class="wv-account-field">
                                <label for="RegisterEmail"><?php echo __('Email');?></label>
                                <div class="wv-account-input">
                                    <i class="fa fa-envelope"></i>
                                    <?php echo $this->Form->input('email',array('id'=>'RegisterEmail','type'=>'email','label'=>false,'class'=>'form-control','placeholder'=>__('Email'),'div'=>false));?>
                                </div>
                            </div>

                            <div class="wv-account-field">
                                <label for="RegisterName"><?php echo __('Name');?></label>
                                <div class="wv-account-input">
                                    <i class="fa fa-user"></i>
                                    <?php echo $this->Form->input('name',array('id'=>'RegisterName','label'=>false,'class'=>'form-control','placeholder'=>__('Name'),'div'=>false));?>
                                </div>
                            </div>

                            <div class="wv-account-field">
                                <label for="RegisterPassword"><?php echo __('Password');?></label>
                                <div class="wv-account-input">
                                    <i class="fa fa-lock"></i>
                                    <?php echo $this->Form->input('password',array('id'=>'RegisterPassword','type'=>'password','label'=>false,'class'=>'form-control','placeholder'=>__('Password'),'minlength'=>'4','maxlength'=>'15','div'=>false));?>
                                </div>
                            </div>

                            <div class="wv-account-field">
                                <label for="RegisterMobile"><?php echo __('Mobile');?></label>
                                <div class="wv-account-input">
                                    <i class="fa fa-phone"></i>
                                    <?php echo $this->Form->input('mobile',array('id'=>'RegisterMobile','label'=>false,'class'=>'form-control','placeholder'=>__('Mobile'),'div'=>false));?>
                                </div>
                            </div>

                            <div class="wv-account-field">
                                <label for="RegisterAddress"><?php echo __('Address');?></label>
                                <div class="wv-account-input">
                                    <i class="fa fa-map-marker"></i>
                                    <?php echo $this->Form->input('address',array('id'=>'RegisterAddress','label'=>false,'class'=>'form-control','placeholder'=>__('Address'),'div'=>false));?>
                                </div>
                            </div>

                            <div class="wv-account-field">
                                <label><?php echo __('Security Code');?></label>
                                <div class="wv-account-captcha">
                                    <?php echo $this->Captcha->render($captchaSettings);?>
                                </div>
                            </div>
                        </div>

                        <div class="wv-account-actions">
                            <button type="submit" class="btn wv-account-submit"><i class="fa fa-user-plus"></i> <?php echo __('Create Account');?></button>
                            <?php echo $this->Html->link(__('I already have an account').' <i class="fa fa-long-arrow-right"></i>',array('controller'=>'Clients','action'=>'login'),array('escape'=>false,'class'=>'wv-account-link'));?>
                        </div>
                        <?php echo $this->Form->end();?>
                    </section>
                </div>
            </div>
        </div>
    </div>
</div>
