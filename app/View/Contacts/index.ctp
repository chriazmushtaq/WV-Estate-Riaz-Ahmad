<?php
$contactPhoneClean = preg_replace('/[^0-9\+]/','',$contact);
$contactTimingToday = date('l');
$contactTimingRows = array(
    array('label'=>__('Monday - Thursday'),'time'=>__('09:00 AM - 06:30 PM'),'days'=>array('Monday','Tuesday','Wednesday','Thursday'),'closed'=>false),
    array('label'=>__('Friday'),'time'=>__('09:00 AM - 12:30 PM'),'days'=>array('Friday'),'closed'=>false),
    array('label'=>__('Sunday'),'time'=>__('Closed'),'days'=>array('Sunday'),'closed'=>true)
);
?>
<style>
.wv-contact-page{background:#f4f7fb;padding-bottom:48px;}
.wv-contact-hero{background:linear-gradient(135deg,#07152a 0%,#102746 64%,#ff0a46 155%);color:#fff;padding:38px 0 58px;position:relative;overflow:hidden;}
.wv-contact-hero:after{background:linear-gradient(90deg,#ff0a46 0%,#7cf9ff 55%,rgba(124,249,255,0) 100%);bottom:0;content:"";height:3px;left:0;position:absolute;right:0;}
.wv-contact-hero h1{color:#fff;font-size:34px;font-weight:900;letter-spacing:0;margin:0 0 8px;}
.wv-contact-hero p{color:#d8e3f2;font-size:14px;font-weight:700;line-height:1.55;margin:0;max-width:720px;}
.wv-contact-wrap{align-items:start;display:grid;gap:22px;grid-template-columns:minmax(0,1fr) 380px;margin-top:-34px;position:relative;z-index:2;}
.wv-contact-card{background:#fff;border:1px solid #dde7f3;border-radius:10px;box-shadow:0 16px 34px rgba(15,23,42,.10);overflow:hidden;}
.wv-contact-card-head{align-items:flex-start;border-bottom:1px solid #edf2f7;display:flex;gap:12px;padding:19px 22px;}
.wv-contact-head-icon{align-items:center;background:#fff1f4;border-radius:8px;color:#ff0a46;display:flex;flex:0 0 38px;height:38px;justify-content:center;width:38px;}
.wv-contact-card-head h2{color:#07152a;font-size:21px;font-weight:900;line-height:1.2;margin:0 0 5px;}
.wv-contact-card-head p{color:#64748b;font-size:13px;font-weight:700;line-height:1.45;margin:0;}
.wv-contact-form{padding:22px;}
.wv-contact-form label{color:#42526b;font-size:11px;font-weight:900;letter-spacing:.3px;text-transform:uppercase;}
.wv-contact-form .form-control{background:#fbfdff;border:1px solid #d8e0ea;border-radius:7px;box-shadow:none;color:#07152a;height:43px;}
.wv-contact-form .form-control:focus{background:#fff;border-color:#ff0a46;box-shadow:0 0 0 3px rgba(255,10,70,.10);}
.wv-contact-form textarea.form-control{height:auto;min-height:136px;resize:vertical;}
.wv-contact-submit{background:linear-gradient(135deg,#ff0a46,#c70032);border:0;border-radius:7px;box-shadow:0 13px 24px rgba(255,10,70,.24);color:#fff;font-weight:900;padding:13px 20px;width:100%;}
.wv-contact-submit:hover,.wv-contact-submit:focus{background:linear-gradient(135deg,#c70032,#8f0026);color:#fff;}
.wv-contact-side{display:grid;gap:16px;}
.wv-contact-info{background:linear-gradient(135deg,#07152a 0%,#101f36 100%);border:0;color:#fff;padding:18px;}
.wv-contact-info h3,.wv-hours-card h3{color:#fff;font-size:18px;font-weight:900;margin:0 0 13px;}
.wv-contact-row{align-items:flex-start;border-top:1px solid rgba(255,255,255,.12);display:flex;gap:11px;padding:13px 0;}
.wv-contact-row:first-of-type{border-top:0;padding-top:0;}
.wv-contact-row:last-child{padding-bottom:0;}
.wv-contact-icon{align-items:center;background:#ff0a46;border-radius:8px;box-shadow:0 8px 18px rgba(255,10,70,.22);color:#fff;display:flex;flex:0 0 36px;height:36px;justify-content:center;width:36px;}
.wv-contact-row strong{color:#7cf9ff;display:block;font-size:11px;font-weight:900;letter-spacing:.3px;text-transform:uppercase;}
.wv-contact-row span,.wv-contact-row a{color:#fff!important;font-size:13px;font-weight:800;line-height:1.45;text-decoration:none!important;word-break:break-word;}
.wv-hours-card{background:#07152a;border:0;color:#fff;padding:18px;}
.wv-hours-note{color:#b7c5d8;font-size:12px;font-weight:700;margin:0 0 12px;}
.wv-hours-row{align-items:center;background:rgba(255,255,255,.055);border:1px solid rgba(255,255,255,.09);border-radius:8px;display:flex;gap:12px;justify-content:space-between;margin-top:9px;padding:11px 12px;}
.wv-hours-row.is-today{background:#fff1f4;border-color:rgba(255,10,70,.28);}
.wv-hours-day{color:#e2e8f0;font-size:12px;font-weight:900;}
.wv-hours-time{background:#fff;border-radius:999px;color:#07152a;font-size:11px;font-weight:900;padding:5px 9px;white-space:nowrap;}
.wv-hours-row.is-today .wv-hours-day{color:#07152a;}
.wv-hours-row.is-closed .wv-hours-time{background:#fff;color:#ff0a46;}
.wv-contact-content{margin-top:18px;padding:18px;}
.wv-contact-content-title{align-items:center;border-bottom:1px solid #edf2f7;display:flex;gap:10px;margin:-22px -22px 18px;padding:18px 22px;}
.wv-contact-content-title i{align-items:center;background:#fff1f4;border-radius:8px;color:#ff0a46;display:flex;height:36px;justify-content:center;width:36px;}
.wv-contact-content-title h2{color:#07152a;font-size:20px;font-weight:900;margin:0;}
.wv-contact-content *{font-family:inherit!important;}
.wv-contact-content :first-child{margin-top:0;}
.wv-contact-content h1,.wv-contact-content h2,.wv-contact-content h3,.wv-contact-content .section-title{color:#07152a!important;font-size:17px!important;font-weight:900!important;line-height:1.25;margin:0 0 9px!important;}
.wv-contact-content p{color:#475569!important;font-size:13px!important;line-height:1.65;margin:0 0 14px;}
.wv-contact-content > .col-md-6{float:none!important;margin:0!important;padding:0!important;width:100%!important;}
.wv-contact-content > .col-md-6 > .row{align-items:stretch;display:grid!important;gap:12px!important;grid-template-columns:repeat(2,minmax(0,1fr));margin:0!important;}
.wv-contact-content .row:before,.wv-contact-content .row:after{display:none!important;}
.wv-contact-content .row > .col-md-6{background:linear-gradient(180deg,#ffffff 0%,#fbfdff 100%);border:1px solid #dde7f3;border-radius:10px;box-shadow:0 10px 22px rgba(15,23,42,.06);float:none!important;margin:0!important;min-height:100%;padding:16px 16px 14px!important;position:relative;width:auto!important;}
.wv-contact-content .row > .col-md-6:before{align-items:center;background:#07152a;border-radius:8px;color:#fff;display:flex;font-family:FontAwesome;height:34px;justify-content:center;position:absolute;right:14px;top:14px;width:34px;}
.wv-contact-content .row > .col-md-6:first-child:before{content:"\f041";}
.wv-contact-content .row > .col-md-6:nth-child(2):before{content:"\f017";}
.wv-contact-content .section-title{border-bottom:1px solid #edf2f7;margin:0 48px 14px 0!important;padding:0 0 12px!important;}
.wv-contact-content .contact-info h2{border-top:1px solid #edf2f7;color:#64748b!important;font-size:10px!important;letter-spacing:.35px;margin:10px 0 4px!important;padding-top:10px;text-transform:uppercase;}
.wv-contact-content .contact-info h2:first-child{border-top:0;margin-top:0!important;padding-top:0;}
.wv-contact-content .contact-info p{background:#f7fafc;border:1px solid #edf2f7;border-radius:7px;color:#07152a!important;font-size:13px!important;font-weight:900;line-height:1.45!important;margin:0!important;padding:9px 10px;}
.wv-contact-content .contact-info p + h2{margin-top:10px!important;}
.wv-contact-content > .col-md-6 > h2.section-title,.wv-contact-content > .col-md-6 > p{display:none!important;}
@media(max-width:991px){.wv-contact-wrap{display:block}.wv-contact-side{margin-top:18px}.wv-contact-hero{padding:30px 0 50px}.wv-contact-hero h1{font-size:26px}.wv-contact-content .row{grid-template-columns:1fr;}}
@media(max-width:575px){.wv-contact-card-head{display:block}.wv-contact-head-icon{margin-bottom:10px}.wv-hours-row{align-items:flex-start;display:block}.wv-hours-time{display:inline-block;margin-top:7px}.wv-contact-form{padding:18px}.wv-contact-wrap{margin-top:-24px;}}
</style>

<div class="wv-contact-page">
    <section class="wv-contact-hero">
        <div class="container">
            <h1><?php echo __('Contact %s', h($siteName));?></h1>
            <p><?php echo __('Send your property, investment, or support question to our team. Your message is saved in the CRM for follow-up.');?></p>
        </div>
    </section>
    <div class="container">
        <?php echo $this->Session->flash();?>
        <div class="wv-contact-wrap">
            <main class="wv-contact-card">
                <div class="wv-contact-card-head">
                    <div class="wv-contact-head-icon"><i class="fa fa-paper-plane"></i></div>
                    <div>
                        <h2><?php echo __('Send a Message');?></h2>
                        <p><?php echo __('Name, phone, subject, and message are required so our team can respond quickly.');?></p>
                    </div>
                </div>
                <div class="wv-contact-form">
                    <?php echo $this->Form->create('Contact', array('controller'=>'Contacts','action'=>'index','id'=>'post_req','role'=>'form'));?>
                    <div class="form-group">
                        <label><?php echo __('Name');?></label>
                        <?php echo $this->Form->input('name',array('required'=>true,'label'=>false,'class'=>'form-control','placeholder'=>__('Your name'),'div'=>false));?>
                    </div>
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label><?php echo __('Email');?></label>
                                <?php echo $this->Form->input('email',array('type'=>'email','label'=>false,'class'=>'form-control','placeholder'=>__('Email'),'div'=>false));?>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label><?php echo __('Phone');?></label>
                                <?php echo $this->Form->input('phone',array('required'=>true,'label'=>false,'class'=>'form-control','placeholder'=>__('Phone / Mobile'),'div'=>false));?>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label><?php echo __('Subject');?></label>
                        <?php echo $this->Form->input('subject',array('required'=>true,'label'=>false,'class'=>'form-control','placeholder'=>__('Subject'),'div'=>false));?>
                    </div>
                    <div class="form-group">
                        <label><?php echo __('Message');?></label>
                        <?php echo $this->Form->textarea('message',array('required'=>true,'label'=>false,'class'=>'form-control','placeholder'=>__('Write your message here...'),'div'=>false));?>
                    </div>
                    <button type="submit" class="wv-contact-submit"><i class="fa fa-paper-plane"></i> <?php echo __('Send Message');?></button>
                    <?php echo $this->Form->end();?>
                </div>
            </main>
            <aside class="wv-contact-side">
                <div class="wv-contact-card wv-contact-info">
                    <h3><?php echo __('Contact Details');?></h3>
                    <div class="wv-contact-row">
                        <div class="wv-contact-icon"><i class="fa fa-phone"></i></div>
                        <div><strong><?php echo __('Phone');?></strong><a href="tel:<?php echo h($contactPhoneClean);?>"><?php echo h($contact);?></a></div>
                    </div>
                    <div class="wv-contact-row">
                        <div class="wv-contact-icon"><i class="fa fa-envelope"></i></div>
                        <div><strong><?php echo __('Email');?></strong><a href="mailto:<?php echo h($siteEmail);?>"><?php echo h($siteEmail);?></a></div>
                    </div>
                    <div class="wv-contact-row">
                        <div class="wv-contact-icon"><i class="fa fa-building"></i></div>
                        <div><strong><?php echo __('Portal');?></strong><span><?php echo h($siteName);?></span></div>
                    </div>
                </div>
                <div class="wv-contact-card wv-hours-card">
                    <h3><?php echo __('Office Timings');?></h3>
                    <p class="wv-hours-note"><?php echo __('Current schedule for customer visits and property consultations.');?></p>
                    <?php foreach($contactTimingRows as $timingRow){
                        $isToday = in_array($contactTimingToday, $timingRow['days']);
                        $timingClass = 'wv-hours-row';
                        if($isToday){ $timingClass .= ' is-today'; }
                        if(!empty($timingRow['closed'])){ $timingClass .= ' is-closed'; }
                    ?>
                    <div class="<?php echo h($timingClass);?>">
                        <span class="wv-hours-day"><?php echo h($timingRow['label']);?><?php if($isToday){ ?> <small><?php echo __('Today');?></small><?php } ?></span>
                        <span class="wv-hours-time"><?php echo h($timingRow['time']);?></span>
                    </div>
                    <?php } ?>
                </div>
            </aside>
        </div>
        <div class="wv-contact-card wv-contact-content">
            <div class="wv-contact-content-title">
                <i class="fa fa-map-marker"></i>
                <h2><?php echo __('Office Address & Timings');?></h2>
            </div>
            <?php echo $contactValue;?>
        </div>
    </div>
</div>
