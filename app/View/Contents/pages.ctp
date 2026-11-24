<?php
$pageUrl = !empty($contentPost['Content']['page_url']) ? strtolower((string)$contentPost['Content']['page_url']) : strtolower((string)$contentId);
$isAboutPage = ($pageUrl === 'about-us');
?>
<?php if($isAboutPage){ ?>
<?php
$photoUrl = $this->Html->url(array('controller'=>'img'));
$aboutLogo = !empty($frontLogo) ? $frontLogo : 'logo-website.fw.png';
$aboutHeroImage = $photoUrl.'/slides/0a2b09cf329f3b177d997398ededba0a.jpg';
$aboutPhoneClean = preg_replace('/[^0-9\+]/','',$contact);
?>
<style>
.wv-about-page{background:#f4f7fb;color:#1f2937;padding-bottom:48px;}
.wv-about-hero{background:linear-gradient(135deg,rgba(7,21,42,.92),rgba(16,39,70,.86)),url('<?php echo h($aboutHeroImage);?>') center/cover no-repeat;color:#fff;padding:54px 0 76px;position:relative;overflow:hidden;}
.wv-about-hero:after{background:linear-gradient(90deg,#ff0a46 0%,#7cf9ff 58%,rgba(124,249,255,0) 100%);bottom:0;content:"";height:3px;left:0;position:absolute;right:0;}
.wv-about-hero-grid{align-items:center;display:grid;gap:28px;grid-template-columns:minmax(0,1fr) 360px;}
.wv-about-kicker{background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.22);border-radius:999px;color:#7cf9ff;display:inline-block;font-size:11px;font-weight:900;letter-spacing:1.3px;margin-bottom:13px;padding:7px 12px;text-transform:uppercase;}
.wv-about-hero h1{color:#fff;font-size:42px;font-weight:900;line-height:1.1;margin:0 0 14px;}
.wv-about-hero p{color:#d8e3f2;font-size:15px;font-weight:700;line-height:1.65;margin:0;max-width:760px;}
.wv-about-actions{display:flex;flex-wrap:wrap;gap:10px;margin-top:22px;}
.wv-about-btn{align-items:center;border-radius:999px;display:inline-flex;font-size:12px;font-weight:900;gap:8px;padding:12px 17px;text-decoration:none!important;}
.wv-about-btn-primary{background:#ff0a46;color:#fff!important;box-shadow:0 14px 28px rgba(255,10,70,.25);}
.wv-about-btn-light{background:#fff;color:#07152a!important;}
.wv-about-brand-card{background:rgba(255,255,255,.95);border:1px solid rgba(255,255,255,.35);border-radius:10px;box-shadow:0 18px 40px rgba(2,8,23,.24);padding:24px;text-align:center;}
.wv-about-brand-card img{display:block;margin:0 auto 14px;max-height:82px;max-width:180px;object-fit:contain;}
.wv-about-brand-card h2{color:#07152a;font-size:24px;font-weight:900;margin:0 0 8px;}
.wv-about-brand-card p{color:#64748b;font-size:13px;font-weight:800;line-height:1.5;margin:0;}
.wv-about-shell{margin-top:-42px;position:relative;z-index:2;}
.wv-about-stats{display:grid;gap:14px;grid-template-columns:repeat(3,minmax(0,1fr));margin-bottom:24px;}
.wv-about-stat{background:#07152a;border:1px solid rgba(124,249,255,.14);border-radius:10px;box-shadow:0 16px 30px rgba(15,23,42,.15);padding:20px;}
.wv-about-stat strong{color:#7cf9ff;display:block;font-size:27px;font-weight:900;line-height:1;}
.wv-about-stat span{color:#cbd5e1;display:block;font-size:12px;font-weight:900;margin-top:8px;text-transform:uppercase;}
.wv-about-main{display:grid;gap:22px;grid-template-columns:minmax(0,1fr) 340px;}
.wv-about-card{background:#fff;border:1px solid #dde7f3;border-radius:10px;box-shadow:0 14px 30px rgba(15,23,42,.08);overflow:hidden;}
.wv-about-card-head{border-bottom:1px solid #edf2f7;padding:20px 22px;}
.wv-about-card-head h2{color:#07152a;font-size:24px;font-weight:900;margin:0 0 7px;}
.wv-about-card-head p{color:#64748b;font-size:13px;font-weight:700;line-height:1.55;margin:0;}
.wv-about-story{padding:22px;}
.wv-about-story p{color:#475569;font-size:14px;line-height:1.75;margin:0 0 15px;}
.wv-about-feature-grid{display:grid;gap:12px;grid-template-columns:repeat(2,minmax(0,1fr));margin-top:18px;}
.wv-about-feature{background:#fbfdff;border:1px solid #e3ebf5;border-radius:10px;padding:16px;}
.wv-about-feature i{align-items:center;background:#fff1f4;border-radius:8px;color:#ff0a46;display:flex;height:36px;justify-content:center;margin-bottom:12px;width:36px;}
.wv-about-feature h3{color:#07152a;font-size:15px;font-weight:900;margin:0 0 7px;}
.wv-about-feature p{color:#64748b;font-size:12px;font-weight:700;line-height:1.55;margin:0;}
.wv-about-side{display:grid;gap:16px;}
.wv-about-contact{background:linear-gradient(135deg,#07152a 0%,#101f36 100%);color:#fff;padding:18px;}
.wv-about-contact h3{color:#fff;font-size:18px;font-weight:900;margin:0 0 12px;}
.wv-about-contact-row{align-items:flex-start;border-top:1px solid rgba(255,255,255,.12);display:flex;gap:10px;padding:12px 0;}
.wv-about-contact-row:first-of-type{border-top:0;padding-top:0;}
.wv-about-contact-row:last-child{padding-bottom:0;}
.wv-about-contact-row i{align-items:center;background:#ff0a46;border-radius:8px;color:#fff;display:flex;flex:0 0 34px;height:34px;justify-content:center;width:34px;}
.wv-about-contact-row strong{color:#7cf9ff;display:block;font-size:11px;font-weight:900;text-transform:uppercase;}
.wv-about-contact-row a,.wv-about-contact-row span{color:#fff!important;font-size:13px;font-weight:800;line-height:1.45;text-decoration:none!important;word-break:break-word;}
.wv-about-values{padding:18px;}
.wv-about-values h3{color:#07152a;font-size:18px;font-weight:900;margin:0 0 13px;}
.wv-about-values ul{list-style:none;margin:0;padding:0;}
.wv-about-values li{align-items:flex-start;border-top:1px solid #edf2f7;color:#42526b;display:flex;font-size:13px;font-weight:800;gap:10px;line-height:1.45;padding:12px 0;}
.wv-about-values li:first-child{border-top:0;padding-top:0;}
.wv-about-values li:before{background:#e8fff8;border-radius:50%;color:#047857;content:"\f00c";font-family:FontAwesome;font-size:10px;height:20px;line-height:20px;text-align:center;width:20px;flex:0 0 20px;}
.wv-about-process{margin-top:22px;}
.wv-about-process-grid{display:grid;gap:14px;grid-template-columns:repeat(3,minmax(0,1fr));}
.wv-about-process-step{background:#fff;border:1px solid #dde7f3;border-radius:10px;box-shadow:0 12px 26px rgba(15,23,42,.07);padding:18px;}
.wv-about-process-step span{background:#07152a;border-radius:999px;color:#7cf9ff;display:inline-block;font-size:11px;font-weight:900;margin-bottom:12px;padding:6px 10px;}
.wv-about-process-step h3{color:#07152a;font-size:16px;font-weight:900;margin:0 0 8px;}
.wv-about-process-step p{color:#64748b;font-size:13px;font-weight:700;line-height:1.55;margin:0;}
@media(max-width:991px){.wv-about-hero-grid,.wv-about-main{grid-template-columns:1fr}.wv-about-stats,.wv-about-process-grid{grid-template-columns:1fr}.wv-about-brand-card{text-align:left}.wv-about-brand-card img{margin-left:0}.wv-about-hero h1{font-size:32px}}
@media(max-width:575px){.wv-about-hero{padding:36px 0 60px}.wv-about-feature-grid{grid-template-columns:1fr}.wv-about-actions{display:block}.wv-about-btn{justify-content:center;margin-bottom:8px;width:100%}}
</style>
<div class="wv-about-page">
    <section class="wv-about-hero">
        <div class="container">
            <div class="wv-about-hero-grid">
                <div>
                    <span class="wv-about-kicker"><?php echo __('About %s', h($siteName));?></span>
                    <h1><?php echo __('Property guidance with trust, clarity, and local market focus.');?></h1>
                    <p><?php echo __('%s helps buyers, sellers, investors, and families explore verified property opportunities with practical guidance, transparent information, and CRM-backed follow-up.', h($siteName));?></p>
                    <div class="wv-about-actions">
                        <?php echo $this->Html->link('<i class="fa fa-search"></i> '.__('Browse Properties'),array('controller'=>'Properties','action'=>'index'),array('escape'=>false,'class'=>'wv-about-btn wv-about-btn-primary'));?>
                        <?php echo $this->Html->link('<i class="fa fa-phone"></i> '.__('Contact Team'),array('controller'=>'Contacts','action'=>'index'),array('escape'=>false,'class'=>'wv-about-btn wv-about-btn-light'));?>
                    </div>
                </div>
                <div class="wv-about-brand-card">
                    <?php echo $this->Html->image($aboutLogo,array('alt'=>$siteName));?>
                    <h2><?php echo h($siteName);?></h2>
                    <p><?php echo __('A modern real estate portal built for verified inventory, responsive service, and confident property decisions.');?></p>
                </div>
            </div>
        </div>
    </section>
    <div class="container wv-about-shell">
        <div class="wv-about-stats">
            <div class="wv-about-stat"><strong><?php echo __('Verified');?></strong><span><?php echo __('Property Listings');?></span></div>
            <div class="wv-about-stat"><strong><?php echo __('Focused');?></strong><span><?php echo __('Client Guidance');?></span></div>
            <div class="wv-about-stat"><strong><?php echo __('Reliable');?></strong><span><?php echo __('After-Sale Support');?></span></div>
        </div>
        <div class="wv-about-main">
            <main class="wv-about-card">
                <div class="wv-about-card-head">
                    <h2><?php echo __('Who We Are');?></h2>
                    <p><?php echo __('A property business shaped around service, transparency, and practical real estate decisions.');?></p>
                </div>
                <div class="wv-about-story">
                    <p><?php echo __('%s is a premier real estate platform helping clients discover published properties, compare options, and connect with the right team for their requirement. Our work combines market knowledge with organized CRM processes so every inquiry can be followed properly.', h($siteName));?></p>
                    <p><?php echo __('Whether a client is searching for a plot, villa, house, apartment, or commercial opportunity, our goal is to make the process easier to understand and easier to act on.');?></p>
                    <div class="wv-about-feature-grid">
                        <div class="wv-about-feature">
                            <i class="fa fa-home"></i>
                            <h3><?php echo __('Verified Inventory');?></h3>
                            <p><?php echo __('Published properties are presented with clear pricing, area, location, and contract details.');?></p>
                        </div>
                        <div class="wv-about-feature">
                            <i class="fa fa-handshake-o"></i>
                            <h3><?php echo __('Client First Service');?></h3>
                            <p><?php echo __('We keep communication simple, responsive, and focused on the client requirement.');?></p>
                        </div>
                        <div class="wv-about-feature">
                            <i class="fa fa-line-chart"></i>
                            <h3><?php echo __('Market Guidance');?></h3>
                            <p><?php echo __('Our team supports better decisions through local property knowledge and practical advice.');?></p>
                        </div>
                        <div class="wv-about-feature">
                            <i class="fa fa-shield"></i>
                            <h3><?php echo __('Transparent Process');?></h3>
                            <p><?php echo __('Property information, customer inquiries, and follow-ups are handled with proper records.');?></p>
                        </div>
                    </div>
                </div>
            </main>
            <aside class="wv-about-side">
                <div class="wv-about-card wv-about-contact">
                    <h3><?php echo __('Connect With Us');?></h3>
                    <div class="wv-about-contact-row">
                        <i class="fa fa-phone"></i>
                        <div><strong><?php echo __('Phone');?></strong><a href="tel:<?php echo h($aboutPhoneClean);?>"><?php echo h($contact);?></a></div>
                    </div>
                    <div class="wv-about-contact-row">
                        <i class="fa fa-envelope"></i>
                        <div><strong><?php echo __('Email');?></strong><a href="mailto:<?php echo h($siteEmail);?>"><?php echo h($siteEmail);?></a></div>
                    </div>
                    <div class="wv-about-contact-row">
                        <i class="fa fa-building"></i>
                        <div><strong><?php echo __('Portal');?></strong><span><?php echo h($siteName);?></span></div>
                    </div>
                </div>
                <div class="wv-about-card wv-about-values">
                    <h3><?php echo __('Our Standards');?></h3>
                    <ul>
                        <li><?php echo __('Clear property information before client visits.');?></li>
                        <li><?php echo __('Respectful communication with buyers and owners.');?></li>
                        <li><?php echo __('Organized follow-up for every serious inquiry.');?></li>
                        <li><?php echo __('Support from inquiry through deal progress.');?></li>
                    </ul>
                </div>
            </aside>
        </div>
        <section class="wv-about-process">
            <div class="wv-about-process-grid">
                <div class="wv-about-process-step">
                    <span><?php echo __('Step 01');?></span>
                    <h3><?php echo __('Understand Requirement');?></h3>
                    <p><?php echo __('We listen to budget, location, area, and investment goals before suggesting options.');?></p>
                </div>
                <div class="wv-about-process-step">
                    <span><?php echo __('Step 02');?></span>
                    <h3><?php echo __('Shortlist Properties');?></h3>
                    <p><?php echo __('We help compare relevant published inventory so clients can choose with confidence.');?></p>
                </div>
                <div class="wv-about-process-step">
                    <span><?php echo __('Step 03');?></span>
                    <h3><?php echo __('Support Next Action');?></h3>
                    <p><?php echo __('Our team assists with visits, inquiries, and deal follow-up through the CRM workflow.');?></p>
                </div>
            </div>
        </section>
    </div>
</div>
<?php } else { ?>
<div class="main-wrapper">
	    <div class="main">
	        <div class="main-inner">
	        		        
	<div class="content-title">
                <div class="content-title-inner">
                        <div class="container">		
                                <h1><?php echo h($linkName1);?> <span><?php echo h($linkName2);?></span></h1>	
                        </div><!-- /.container -->
                </div><!-- /.content-title-inner -->
        </div><!-- /.content-title -->
	            <div class="content">
	                <div class="container">
                                <?php echo str_replace("<script","",$contentPost['Content']['main_content']);?>
                        </div><!-- /.container -->
	            </div><!-- /.content -->
	        </div><!-- /.main-inner -->
	    </div><!-- /.main -->
    </div><!-- /.main-wrapper -->
<?php } ?>
