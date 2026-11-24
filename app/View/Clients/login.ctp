<?php
$clientLoginLogo = strlen(trim((string)$frontLogo))>0 ? $frontLogo : 'logo-website.fw.png';
$clientLoginTitle = !empty($siteOrganization) ? $siteOrganization : $siteName;
$clientContactClean = preg_replace('/[^0-9\+]/','',(string)$contact);
?>
<style type="text/css">
.wv-client-login-page{
    background:#eef3f8;
    min-height:calc(100vh - 160px);
    padding:32px 0 40px;
}
.wv-client-login-page *{
    box-sizing:border-box;
}
.wv-client-login-shell{
    display:grid;
    grid-template-columns:minmax(0,1fr) minmax(380px,430px);
    max-width:1060px;
    margin:0 auto;
    padding:0 16px;
}
.wv-client-login-brand{
    background:linear-gradient(180deg,#ffffff 0%,#f4f8fd 100%);
    border:1px solid #dce7f3;
    border-radius:12px 0 0 12px;
    box-shadow:0 18px 38px rgba(15,23,42,.13);
    color:#07152a;
    min-height:540px;
    overflow:hidden;
    padding:40px;
    position:relative;
}
.wv-client-login-brand:before{
    background:repeating-linear-gradient(90deg,rgba(15,23,42,.04) 0,rgba(15,23,42,.04) 1px,transparent 1px,transparent 42px);
    content:"";
    inset:0;
    opacity:.45;
    position:absolute;
}
.wv-client-login-brand:after{
    background:linear-gradient(135deg,rgba(14,165,233,.14),rgba(255,10,70,.12));
    border-radius:999px;
    bottom:-110px;
    content:"";
    height:250px;
    position:absolute;
    right:-100px;
    width:250px;
}
.wv-client-login-logo{
    align-items:center;
    display:flex;
    gap:14px;
    margin-bottom:66px;
    position:relative;
    z-index:2;
}
.wv-client-login-logo img{
    background:#fff;
    border-radius:10px;
    box-shadow:0 12px 24px rgba(0,0,0,.18);
    max-height:60px;
    max-width:130px;
    object-fit:contain;
    padding:8px;
}
.wv-client-login-logo strong{
    color:#07152a;
    display:block;
    font-size:17px;
    font-weight:900;
    line-height:1.25;
}
.wv-client-login-logo span{
    color:#0f4c81;
    display:block;
    font-size:11px;
    font-weight:900;
    letter-spacing:0;
    margin-top:4px;
    text-transform:uppercase;
}
.wv-client-login-brand h1{
    color:#07152a;
    font-size:38px;
    font-weight:900;
    letter-spacing:0;
    line-height:1.08;
    margin:0 0 16px;
    max-width:470px;
    position:relative;
    z-index:2;
}
.wv-client-login-brand p{
    color:#475569;
    font-size:14px;
    font-weight:800;
    line-height:1.7;
    margin:0;
    max-width:500px;
    position:relative;
    z-index:2;
}
.wv-client-login-points{
    bottom:34px;
    display:grid;
    gap:12px;
    left:40px;
    position:absolute;
    right:40px;
    z-index:2;
}
.wv-client-login-point{
    align-items:center;
    color:#1e293b;
    display:flex;
    font-size:13px;
    font-weight:900;
    gap:10px;
}
.wv-client-login-point i{
    align-items:center;
    background:#eff6ff;
    border:1px solid #cfe0f5;
    border-radius:8px;
    color:#0f4c81;
    display:flex;
    height:34px;
    justify-content:center;
    width:34px;
}
.wv-client-login-card{
    background:#fff;
    border:1px solid #dce7f3;
    border-left:0;
    border-radius:0 12px 12px 0;
    box-shadow:0 18px 38px rgba(15,23,42,.13);
    min-height:540px;
    padding:40px 38px;
}
.wv-client-login-card h2{
    color:#07152a;
    font-size:27px;
    font-weight:900;
    line-height:1.2;
    margin:0 0 8px;
}
.wv-client-login-subtitle{
    color:#64748b;
    font-size:13px;
    font-weight:800;
    line-height:1.55;
    margin:0 0 24px;
}
.wv-client-login-card .alert{
    border:1px solid #dbe5f1;
    border-radius:12px;
    font-size:12px;
    font-weight:900;
    margin-bottom:16px;
}
.wv-client-login-field{
    margin-bottom:16px;
}
.wv-client-login-field label{
    color:#334155;
    display:block;
    font-size:11px;
    font-weight:900;
    letter-spacing:0;
    margin-bottom:7px;
    text-transform:uppercase;
}
.wv-client-login-input{
    align-items:center;
    background:#ffffff;
    border:1px solid #dfe9f5;
    border-radius:10px;
    display:flex;
    min-height:50px;
    overflow:hidden;
}
.wv-client-login-input i{
    color:#0f4c81;
    flex:0 0 50px;
    text-align:center;
}
.wv-client-login-input .form-control{
    background:transparent!important;
    border:0!important;
    box-shadow:none!important;
    color:#07152a;
    font-size:14px;
    font-weight:900;
    height:50px;
    min-width:0;
    padding:0 14px 0 0;
}
.wv-client-login-input:focus-within{
    border-color:#0f4c81;
    box-shadow:0 0 0 4px rgba(15,76,129,.10);
}
.wv-client-login-password-toggle{
    align-items:center;
    background:transparent;
    border:0;
    color:#334155;
    display:flex;
    flex:0 0 46px;
    font-size:14px;
    height:50px;
    justify-content:center;
    padding:0;
}
.wv-client-login-password-toggle:hover,
.wv-client-login-password-toggle:focus{
    color:#0f4c81;
    outline:0;
}
.wv-client-login-submit{
    background:linear-gradient(135deg,#0f4c81 0%,#0b6aa7 100%)!important;
    border:0!important;
    border-radius:10px!important;
    box-shadow:0 14px 26px rgba(15,76,129,.20);
    color:#fff!important;
    font-size:14px;
    font-weight:900!important;
    height:50px;
    letter-spacing:0;
    margin-top:4px;
    text-transform:uppercase;
    width:100%;
}
.wv-client-login-submit:hover,
.wv-client-login-submit:focus{
    background:#07152a!important;
    color:#fff!important;
}
.wv-client-login-links{
    align-items:center;
    display:flex;
    flex-wrap:wrap;
    gap:10px;
    justify-content:space-between;
    margin-top:18px;
}
.wv-client-login-links a{
    color:#102746!important;
    font-size:12px;
    font-weight:900;
    text-decoration:none!important;
}
.wv-client-login-links a:hover{
    color:#0b6aa7!important;
}
.wv-client-register-box{
    background:#f8fbff;
    border:1px solid #dfe9f5;
    border-radius:10px;
    margin-top:20px;
    padding:14px;
}
.wv-client-register-box a{
    align-items:center;
    color:#07152a!important;
    display:flex;
    font-size:12px;
    font-weight:900;
    justify-content:space-between;
    padding:8px 0;
    text-decoration:none!important;
}
.wv-client-register-box a + a{
    border-top:1px solid #e7edf5;
}
.wv-client-register-box a:hover{
    color:#ff0a46!important;
}
.wv-client-login-note{
    background:#ecfdf5;
    border:1px solid #cdeee0;
    border-radius:10px;
    color:#047857;
    font-size:12px;
    font-weight:900;
    line-height:1.55;
    margin-top:20px;
    padding:12px 13px;
}
.wv-client-login-note i{
    margin-right:6px;
}

/* Desktop App Card */
.wv-client-app-card{
    background:linear-gradient(135deg, #07152a 0%, #0f2b4c 100%)!important;
    border:1px solid #1e3a5f!important;
    border-radius:12px!important;
    box-shadow:0 12px 28px rgba(7,21,42,0.22)!important;
    color:#fff!important;
    margin-top:18px!important;
    padding:16px!important;
}
.wv-client-app-header{
    align-items:center!important;
    display:flex!important;
    gap:12px!important;
    margin-bottom:10px!important;
}
.wv-client-app-header i.fa-android{
    align-items:center!important;
    background:linear-gradient(135deg, #10b981 0%, #059669 100%)!important;
    border-radius:10px!important;
    box-shadow:0 6px 14px rgba(16,185,129,0.35)!important;
    color:#fff!important;
    display:flex!important;
    font-size:22px!important;
    height:42px!important;
    justify-content:center!important;
    width:42px!important;
}
.wv-client-app-header strong{
    color:#ffffff!important;
    display:block!important;
    font-size:14px!important;
    font-weight:900!important;
    line-height:1.25!important;
}
.wv-client-app-header span{
    color:#6ee7b7!important;
    display:block!important;
    font-size:10.5px!important;
    font-weight:800!important;
    margin-top:2px!important;
}
.wv-client-app-desc{
    color:#e2e8f0!important;
    font-size:11.5px!important;
    font-weight:600!important;
    line-height:1.5!important;
    margin:0 0 12px!important;
}
.wv-client-app-download-btn{
    align-items:center!important;
    background:linear-gradient(135deg, #10b981 0%, #059669 100%)!important;
    border:0!important;
    border-radius:8px!important;
    box-shadow:0 8px 18px rgba(16,185,129,0.35)!important;
    color:#ffffff!important;
    display:flex!important;
    font-size:12px!important;
    font-weight:900!important;
    gap:9px!important;
    height:44px!important;
    justify-content:center!important;
    text-decoration:none!important;
    text-transform:uppercase!important;
    width:100%!important;
}
.wv-client-app-download-btn span,
.wv-client-app-download-btn i{
    color:#ffffff!important;
}
.wv-client-app-download-btn:hover,.wv-client-app-download-btn:focus{
    background:#047857!important;
    color:#ffffff!important;
    text-decoration:none!important;
}

/* Mobile Hero App Download View */
.wv-mobile-app-hero-wrap{
    display:none;
}
.wv-desktop-login-section{
    display:block;
}

@media(max-width:768px){
    .wv-client-login-page{
        padding:16px 0 24px;
    }
    .wv-client-login-shell{
        display:block;
        padding:0 12px;
        max-width:480px;
    }
    .wv-client-login-brand{
        display:none!important;
    }
    .wv-client-login-card{
        border:0;
        border-radius:18px;
        box-shadow:0 14px 34px rgba(15,23,42,.12);
        min-height:auto;
        padding:20px 18px;
    }

    /* Mobile Hero Showcase */
    .wv-mobile-app-hero-wrap{
        display:block;
        text-align:center;
    }
    .wv-mobile-app-hero-card{
        background:linear-gradient(145deg, #07152a 0%, #0d284a 100%)!important;
        border:1px solid #1e3a5f!important;
        border-radius:16px!important;
        box-shadow:0 16px 36px rgba(7,21,42,0.30)!important;
        padding:24px 18px!important;
        color:#ffffff!important;
        margin-bottom:16px!important;
        text-align:center!important;
    }
    .wv-mobile-hero-icon{
        display:inline-flex!important;
        align-items:center!important;
        justify-content:center!important;
        width:64px!important;
        height:64px!important;
        border-radius:16px!important;
        background:linear-gradient(135deg, #10b981 0%, #059669 100%)!important;
        box-shadow:0 8px 24px rgba(16,185,129,0.45)!important;
        color:#ffffff!important;
        font-size:32px!important;
        margin-bottom:14px!important;
    }
    .wv-client-login-card .wv-mobile-hero-title,
    .wv-mobile-hero-title,
    h2.wv-mobile-hero-title{
        color:#ffffff!important;
        font-size:22px!important;
        font-weight:900!important;
        line-height:1.25!important;
        margin:0 0 8px!important;
        text-shadow:0 2px 6px rgba(0,0,0,0.5)!important;
    }
    .wv-mobile-hero-badge{
        display:inline-block!important;
        background:#10b981!important;
        color:#ffffff!important;
        font-size:10.5px!important;
        font-weight:900!important;
        padding:4px 12px!important;
        border-radius:20px!important;
        margin-bottom:14px!important;
        letter-spacing:0.6px!important;
        box-shadow:0 3px 10px rgba(16,185,129,0.35)!important;
    }
    .wv-mobile-hero-desc{
        color:#e2e8f0!important;
        font-size:12px!important;
        font-weight:600!important;
        line-height:1.55!important;
        margin:0 0 16px!important;
    }
    .wv-mobile-features-box{
        background:rgba(255,255,255,0.08)!important;
        border:1px solid rgba(255,255,255,0.16)!important;
        border-radius:12px!important;
        padding:12px 14px!important;
        text-align:left!important;
        margin-bottom:18px!important;
    }
    .wv-mobile-feature-row{
        color:#ffffff!important;
        font-size:12px!important;
        font-weight:700!important;
        margin-bottom:7px!important;
        display:flex!important;
        align-items:center!important;
        gap:8px!important;
    }
    .wv-mobile-feature-row:last-child{
        margin-bottom:0!important;
    }
    .wv-mobile-feature-row span{
        color:#ffffff!important;
        font-size:12px!important;
        font-weight:700!important;
    }
    .wv-mobile-feature-row i{
        color:#34d399!important;
        font-size:13px!important;
    }
    .wv-mobile-download-btn-hero{
        display:flex!important;
        align-items:center!important;
        justify-content:center!important;
        gap:10px!important;
        background:linear-gradient(135deg, #10b981 0%, #059669 100%)!important;
        color:#ffffff!important;
        border:0!important;
        border-radius:10px!important;
        font-size:13px!important;
        font-weight:900!important;
        height:48px!important;
        width:100%!important;
        text-decoration:none!important;
        box-shadow:0 8px 20px rgba(16,185,129,0.45)!important;
    }
    .wv-mobile-download-btn-hero span,
    .wv-mobile-download-btn-hero i{
        color:#ffffff!important;
    }

    /* Web form hidden by default on mobile */
    .wv-desktop-login-section{
        display:none;
        margin-top:14px;
        padding-top:14px;
        border-top:1px dashed #cbd5e1;
    }
    .wv-mobile-web-login-toggle{
        display:block;
        text-align:center;
        margin-top:10px;
    }
    .wv-mobile-web-login-toggle a{
        color:#0f4c81!important;
        font-size:11.5px;
        font-weight:800;
        text-decoration:underline!important;
        cursor:pointer;
    }
}
</style>

<div class="main-wrapper">
    <div class="main">
        <div class="main-inner">
            <div class="content wv-client-login-page">
                <div class="wv-client-login-shell">
                    <!-- Left Brand Panel (Desktop) -->
                    <section class="wv-client-login-brand">
                        <div class="wv-client-login-logo">
                            <?php echo $this->Html->image($clientLoginLogo,array('alt'=>$siteName));?>
                            <div>
                                <strong><?php echo h($clientLoginTitle);?></strong>
                                <span><?php echo __('Client Portal');?></span>
                            </div>
                        </div>
                        <h1><?php echo __('Welcome Back');?></h1>
                        <p><?php echo __('Access your deals, payment ledger, invoices, receipts, profile, and published property services from your %s client account.', h($clientLoginTitle));?></p>
                        <div class="wv-client-login-points">
                            <div class="wv-client-login-point"><i class="fa fa-file-text-o"></i><span><?php echo __('View deal invoices and letters');?></span></div>
                            <div class="wv-client-login-point"><i class="fa fa-list"></i><span><?php echo __('Track payment ledger and receipts');?></span></div>
                            <div class="wv-client-login-point"><i class="fa fa-phone"></i><span><?php echo __('Need help? Call');?> <?php echo h($contact);?></span></div>
                        </div>
                    </section>

                    <!-- Right Card (Desktop: Login + App Card | Mobile: App Showcase Hero) -->
                    <section class="wv-client-login-card">
                        <?php echo $this->Session->flash();?>

                        <!-- Mobile-Only Hero Showcase -->
                        <div class="wv-mobile-app-hero-wrap">
                            <div class="wv-mobile-app-hero-card">
                                <div class="wv-mobile-hero-icon"><i class="fa fa-android"></i></div>
                                <h2 class="wv-mobile-hero-title" style="color:#ffffff!important;"><?php echo __('Download Our Android App');?></h2>
                                <span class="wv-mobile-hero-badge"><?php echo __('OFFICIAL CLIENT APP PUBLISHED');?></span>
                                <p class="wv-mobile-hero-desc"><?php echo __('For the best mobile experience, install the official %s Android App directly on your phone.', h($clientLoginTitle));?></p>

                                <div class="wv-mobile-features-box">
                                    <div class="wv-mobile-feature-row"><i class="fa fa-check-circle"></i><span><?php echo __('Instant Deal Payment Receipt Alerts');?></span></div>
                                    <div class="wv-mobile-feature-row"><i class="fa fa-check-circle"></i><span><?php echo __('View Deal Ledgers & Installment Overdues');?></span></div>
                                    <div class="wv-mobile-feature-row"><i class="fa fa-check-circle"></i><span><?php echo __('Allotment Letters & Official Statements');?></span></div>
                                    <div class="wv-mobile-feature-row"><i class="fa fa-check-circle"></i><span><?php echo __('Real-time Mobile Push Notifications');?></span></div>
                                </div>

                                <a href="<?php echo $this->Html->url('/files/whitevillas.apk');?>" class="btn wv-mobile-download-btn-hero" download="WhiteVillas.apk">
                                    <i class="fa fa-download"></i>
                                    <span><?php echo __('DOWNLOAD ANDROID APP (.APK)');?></span>
                                </a>
                            </div>

                            <div class="wv-mobile-web-login-toggle">
                                <a id="toggleWebLoginFormBtn"><i class="fa fa-globe"></i> <?php echo __('Or Sign In via Web Browser');?></a>
                            </div>
                        </div>

                        <!-- Login Form Section (Always visible on Desktop, collapsible on Mobile) -->
                        <div class="wv-desktop-login-section" id="webLoginFormSection">
                            <h2><?php echo __('Client Login');?></h2>
                            <p class="wv-client-login-subtitle"><?php echo __('Sign in with your registered email and password.');?></p>

                            <?php echo $this->Form->create('Client', array('url'=>array('controller'=>'Clients','action'=>'login'),'name'=>'post_req','id'=>'post_req','autocomplete'=>'off'));?>
                            <div class="wv-client-login-field">
                                <label for="ClientEmail"><?php echo __('Email Id');?></label>
                                <div class="wv-client-login-input">
                                    <i class="fa fa-envelope"></i>
                                    <?php echo $this->Form->input('email',array('id'=>'ClientEmail','type'=>'email','required'=>true,'label'=>false,'class'=>'form-control validate[required]','placeholder'=>__('Email Id'),'div'=>false,'autocomplete'=>'email'));?>
                                </div>
                            </div>

                            <div class="wv-client-login-field">
                                <label for="ClientPassword"><?php echo __('Password');?></label>
                                <div class="wv-client-login-input">
                                    <i class="fa fa-lock"></i>
                                    <?php echo $this->Form->input('password',array('id'=>'ClientPassword','type'=>'password','required'=>true,'label'=>false,'class'=>'form-control validate[required,minSize[4],maxSize[15]]','placeholder'=>__('Password'),'div'=>false,'autocomplete'=>'current-password'));?>
                                    <button type="button" class="wv-client-login-password-toggle" id="ClientPasswordToggle" aria-label="<?php echo h(__('Show password')); ?>"><span class="fa fa-eye"></span></button>
                                </div>
                            </div>

                            <button type="submit" class="btn wv-client-login-submit"><span class="fa fa-sign-in"></span> <?php echo __('Login');?></button>
                            <?php echo $this->Form->end(null);?>

                            <div class="wv-client-login-links">
                                <?php echo $this->Html->link(__('Forgot Password'),array('controller'=>'Forgots','action'=>'password'));?>
                                <?php if($clientContactClean !== ''){ ?>
                                    <a href="tel:<?php echo h($clientContactClean);?>"><?php echo __('Call Support');?></a>
                                <?php } ?>
                            </div>

                            <?php if($frontRegistration==1){?>
                                <div class="wv-client-register-box">
                                    <?php echo $this->Html->link('<span><i class="glyphicon glyphicon-user"></i> '.__('New User? Create Account').'</span><i class="fa fa-angle-right"></i>',array('controller'=>'Registers','action'=>'index'),array('escape'=>false));?>
                                    <?php echo $this->Html->link('<span><i class="glyphicon glyphicon-share-alt"></i> '.__('Re-Send Email Verification').'</span><i class="fa fa-angle-right"></i>',array('controller'=>'Emailverifications','action'=>'resend'),array('escape'=>false));?>
                                </div>
                            <?php }?>

                            <div class="wv-client-login-note">
                                <i class="fa fa-shield"></i><?php echo __('Your client account gives secure access to your %s records.', h($clientLoginTitle));?>
                            </div>

                            <!-- Desktop App Download Box -->
                            <div class="wv-client-app-card">
                                <div class="wv-client-app-header">
                                    <i class="fa fa-android"></i>
                                    <div>
                                        <strong><?php echo __('Download Our Android App');?></strong>
                                        <span><?php echo __('Official Mobile App Now Published!');?></span>
                                    </div>
                                </div>
                                <p class="wv-client-app-desc"><?php echo __('Install our client mobile app to receive instant payment receipt alerts, deal invoices, and real-time push notifications.');?></p>
                                <a href="<?php echo $this->Html->url('/files/whitevillas.apk');?>" class="btn wv-client-app-download-btn" download="WhiteVillas.apk">
                                    <i class="fa fa-download"></i>
                                    <span><?php echo __('Download Android App (.APK)');?></span>
                                </a>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
(function($){
    $(document).on('click', '#ClientPasswordToggle', function(){
        var $password = $('#ClientPassword');
        var showing = $password.attr('type') === 'text';
        $password.attr('type', showing ? 'password' : 'text');
        $(this).attr('aria-label', showing ? '<?php echo h(__('Show password')); ?>' : '<?php echo h(__('Hide password')); ?>');
        $(this).find('.fa').toggleClass('fa-eye', showing).toggleClass('fa-eye-slash', !showing);
    });

    $(document).on('click', '#toggleWebLoginFormBtn', function(){
        $('#webLoginFormSection').slideToggle(300);
    });
})(jQuery);
</script>
