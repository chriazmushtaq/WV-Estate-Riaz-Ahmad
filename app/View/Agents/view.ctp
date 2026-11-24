<?php
$photoUrl=$this->Html->url(array('controller'=>'img'));
$agentId=(int)$agent['User']['id'];
$agentName=!empty($agent['User']['name']) ? $agent['User']['name'] : __('Not Specified');
$agentPhoto=empty($agent['User']['photo']) ? $photoUrl.'/user-icon.png' : $photoUrl.'/user_thumb/'.$agent['User']['photo'];
$agentPhone=!empty($agent['User']['phone']) ? $agent['User']['phone'] : '';
$agentMobile=!empty($agent['User']['mobile']) ? $agent['User']['mobile'] : '';
$agentEmail=!empty($agent['User']['email']) ? $agent['User']['email'] : '';
$agentAddress=!empty($agent['User']['address']) ? $agent['User']['address'] : __('Not Specified');
$agentSkype=!empty($agent['User']['skype']) ? $agent['User']['skype'] : __('Not Specified');
$primaryPhone=$agentMobile!='' ? $agentMobile : $agentPhone;
$assignedPropertyCount=isset($assignedPropertyCount) ? (int)$assignedPropertyCount : count((array)$allProperty);
$agentContactNumbers=array();
foreach(array(__('Mobile')=>$agentMobile,__('Second Mobile')=>$agentPhone) as $contactLabel=>$contactNumber)
{
    $contactNumber=trim((string)$contactNumber);
    $callNumber=preg_replace('/[^0-9\+]/','',$contactNumber);
    $whatsappNumber=preg_replace('/[^0-9]/','',$contactNumber);
    if(strpos($whatsappNumber,'0092')===0)
    {
        $whatsappNumber=substr($whatsappNumber,2);
    }
    elseif(preg_match('/^0[0-9]{10}$/',$whatsappNumber))
    {
        $whatsappNumber='92'.substr($whatsappNumber,1);
    }
    if($contactNumber!=='' && $whatsappNumber!=='')
    {
        $agentContactNumbers[$whatsappNumber]=array(
            'label'=>$contactLabel,
            'display'=>$contactNumber,
            'call'=>$callNumber,
            'whatsapp'=>'https://wa.me/'.$whatsappNumber.'?text='.rawurlencode(sprintf(__('Hello %s, I would like to contact you regarding a property.'),$agentName))
        );
    }
}
?>
<style>
.wv-agent-view{background:#f4f7fb;padding-bottom:48px;}
.wv-agent-hero{background:#0b1f36;border-bottom:4px solid #ff0a46;color:#fff;padding:34px 0 76px;}
.wv-agent-hero h1{color:#fff;font-size:32px;font-weight:900;margin:0 0 7px;}
.wv-agent-hero p{color:#d4deea;font-size:13px;font-weight:700;margin:0;}
.wv-agent-layout{align-items:start;display:grid;gap:22px;grid-template-columns:minmax(0,1fr) 330px;margin-top:-50px;}
.wv-card{background:#fff;border:1px solid #dce5ef;border-radius:8px;box-shadow:0 12px 28px rgba(15,23,42,.08);}
.wv-profile-card{overflow:hidden;}
.wv-profile-head{align-items:center;background:#fff;display:flex;gap:20px;padding:22px;}
.wv-profile-photo{background:#fff;background-position:center;background-size:cover;border:4px solid #fff;border-radius:8px;box-shadow:0 10px 24px rgba(15,23,42,.16);flex:0 0 116px;height:116px;width:116px;}
.wv-profile-kicker{align-items:center;color:#1677d2;display:flex;font-size:11px;font-weight:900;gap:7px;text-transform:uppercase;}
.wv-verified-tick{align-items:center;background:#1677d2;border:2px solid #fff;border-radius:50%;box-shadow:0 0 0 1px #1677d2;color:#fff;display:inline-flex;font-size:9px;height:18px;justify-content:center;width:18px;}
.wv-profile-name{color:#07152a;font-size:28px;font-weight:900;line-height:1.15;margin:3px 0 8px;}
.wv-profile-meta{color:#64748b;font-size:13px;font-weight:700;}
.wv-socials{display:flex;gap:8px;margin-top:12px;}
.wv-socials a{align-items:center;background:#edf5ff;border:1px solid #cfe4fb;border-radius:50%;color:#1677d2!important;display:flex;height:32px;justify-content:center;text-decoration:none!important;width:32px;}
.wv-section{margin-top:16px;padding:20px;}
.wv-section h2{color:#07152a;font-size:18px;font-weight:900;margin:0 0 12px;}
.wv-info-section{margin-top:18px;}
.wv-section-heading{align-items:end;display:flex;justify-content:space-between;margin-bottom:12px;}
.wv-section-heading h2{color:#07152a;font-size:18px;font-weight:900;margin:0;}
.wv-section-heading p{color:#64748b;font-size:12px;font-weight:700;margin:4px 0 0;}
.wv-info-grid{display:grid;gap:10px;grid-template-columns:repeat(3,minmax(0,1fr));}
.wv-info-item{align-items:center;background:#fff;border:1px solid #dce5ef;border-radius:8px;display:grid;grid-template-columns:38px minmax(0,1fr);min-height:78px;padding:12px;box-shadow:0 5px 14px rgba(15,23,42,.04);}
.wv-info-icon{align-items:center;background:#edf5ff;border-radius:8px;color:#1677d2;display:flex;font-size:15px;height:36px;justify-content:center;width:36px;}
.wv-info-label{color:#64748b;font-size:11px;font-weight:900;text-transform:uppercase;}
.wv-info-value{color:#07152a;font-size:13px;font-weight:900;margin-top:4px;word-break:break-word;}
.wv-description{color:#334155;font-size:13px;line-height:1.65;}
.wv-sidebar{position:sticky;top:16px;}
.wv-side-card{margin-bottom:14px;padding:18px;}
.wv-side-title{color:#07152a;font-size:16px;font-weight:900;margin:0 0 6px;}
.wv-side-muted{color:#64748b;font-size:12px;font-weight:700;margin-bottom:12px;}
.wv-call-row{display:flex;gap:8px;margin-bottom:10px;}
.wv-call-row a{border-radius:7px;flex:1;font-size:12px;font-weight:900;padding:10px;text-align:center;text-decoration:none!important;}
.wv-call{background:#eef6ff;border:1px solid #bfd7ff;color:#062b57!important;}
.wv-whatsapp{background:#e9fff3;border:1px solid #93f2bd;color:#047857!important;}
.wv-contact-number-list{display:grid;gap:10px;margin-bottom:14px;}
.wv-contact-number{background:#f8fafc;border:1px solid #dce5ef;border-radius:8px;padding:10px;}
.wv-contact-number-head{align-items:center;display:flex;gap:7px;justify-content:space-between;margin-bottom:8px;}
.wv-contact-number-label{color:#64748b;font-size:10px;font-weight:900;text-transform:uppercase;}
.wv-contact-number-value{color:#07152a!important;font-size:13px;font-weight:900;text-decoration:none!important;}
.wv-contact-number-actions{display:grid;gap:7px;grid-template-columns:1fr 1fr;}
.wv-contact-number-actions a{align-items:center;border-radius:6px;display:flex;font-size:11px;font-weight:900;gap:6px;justify-content:center;padding:8px;text-decoration:none!important;}
.wv-inline-contact{align-items:center;display:flex;flex-wrap:wrap;gap:5px;}
.wv-inline-contact>a:first-child{color:#07152a!important;text-decoration:none!important;}
.wv-inline-contact>a:first-child:hover{text-decoration:underline!important;}
.wv-inline-whatsapp{align-items:center;background:#e9fff3;border:1px solid #93f2bd;border-radius:5px;color:#047857!important;display:inline-flex;height:25px;justify-content:center;text-decoration:none!important;width:27px;}
.wv-inline-whatsapp:hover{background:#d5fae6;}
.wv-agent-form label{color:#64748b;font-size:11px;font-weight:900;text-transform:uppercase;}
.wv-agent-form .form-control{border-color:#d8e0ea;border-radius:7px;box-shadow:none;}
.wv-agent-form input.form-control{height:39px;}
.wv-submit{background:#ed0b46;border:0;border-radius:7px;box-shadow:0 10px 22px rgba(237,11,70,.2);color:#fff;font-weight:900;padding:11px;width:100%;}
.wv-submit:hover{background:#bd0737;color:#fff;}
.wv-assigned-link{color:#1677d2!important;display:inline-block;font-size:11px;font-weight:900;margin-left:5px;text-decoration:none!important;}
.wv-assigned-link:hover{color:#0b4f8a!important;text-decoration:underline!important;}
.wv-properties-section{margin-top:22px;}
.wv-properties-head{align-items:end;display:flex;gap:14px;justify-content:space-between;margin-bottom:12px;}
.wv-properties-head h2{color:#07152a;font-size:20px;font-weight:900;margin:0;}
.wv-properties-head p{color:#64748b;font-size:12px;font-weight:700;margin:4px 0 0;}
.wv-catalogue-link{align-items:center;background:#fff;border:1px solid #cfdbe8;border-radius:7px;color:#0b4f8a!important;display:inline-flex;font-size:12px;font-weight:900;gap:7px;padding:9px 12px;text-decoration:none!important;white-space:nowrap;}
.wv-catalogue-link:hover{background:#edf5ff;border-color:#9fc6ec;}
.wv-property-grid{display:grid;gap:14px;grid-template-columns:repeat(2,minmax(0,1fr));}
.wv-property-card{background:#fff;border:1px solid #dce5ef;border-radius:8px;color:inherit!important;display:block;overflow:hidden;text-decoration:none!important;transition:box-shadow .2s ease,transform .2s ease;}
.wv-property-card:hover{box-shadow:0 12px 26px rgba(15,23,42,.11);transform:translateY(-2px);}
.wv-property-photo{aspect-ratio:16/9;background-color:#e8eef5;background-position:center;background-size:cover;position:relative;}
.wv-property-badge{background:#1677d2;border-radius:5px;color:#fff;font-size:10px;font-weight:900;left:10px;padding:5px 8px;position:absolute;top:10px;text-transform:uppercase;}
.wv-property-body{padding:13px;}
.wv-property-title{color:#07152a;font-size:15px;font-weight:900;line-height:1.35;margin:0 0 7px;}
.wv-property-meta{color:#64748b;font-size:11px;font-weight:700;min-height:18px;}
.wv-property-meta i{color:#1677d2;margin-right:4px;}
.wv-property-footer{align-items:center;border-top:1px solid #edf1f5;display:flex;justify-content:space-between;margin-top:11px;padding-top:10px;}
.wv-property-price{color:#ed0b46;font-size:15px;font-weight:900;}
.wv-property-open{color:#0b4f8a;font-size:11px;font-weight:900;}
.wv-property-empty{background:#fff;border:1px dashed #b9c8d8;border-radius:8px;color:#64748b;font-size:13px;font-weight:800;grid-column:1/-1;padding:24px;text-align:center;}
@media(max-width:991px){.wv-agent-layout{display:block}.wv-sidebar{position:static;margin-top:18px}.wv-profile-head{display:flex}.wv-agent-hero h1{font-size:26px;}}
@media(max-width:767px){.wv-info-grid{grid-template-columns:repeat(2,minmax(0,1fr));}.wv-property-grid{grid-template-columns:1fr;}.wv-properties-head{align-items:flex-start;flex-direction:column;}.wv-catalogue-link{width:100%;justify-content:center;}}
@media(max-width:520px){.wv-profile-head{align-items:flex-start;display:block}.wv-profile-photo{margin-bottom:15px}.wv-info-grid{grid-template-columns:1fr}.wv-info-item{min-height:70px}.wv-agent-hero{padding-top:27px}.wv-agent-hero h1{font-size:24px;}}
</style>

<div class="wv-agent-view">
    <section class="wv-agent-hero">
        <div class="container">
            <h1><?php echo __('Agent Details');?></h1>
            <p><?php echo __('Verified White Villas representative profile and contact channel.');?></p>
        </div>
    </section>
    <div class="container">
        <?php echo $this->Session->flash();?>
        <div class="wv-agent-layout">
            <main>
                <section class="wv-card wv-profile-card">
                    <div class="wv-profile-head">
                        <div class="wv-profile-photo" style="background-image:url('<?php echo h($agentPhoto);?>');"></div>
                        <div>
                            <div class="wv-profile-kicker"><span class="wv-verified-tick"><i class="fa fa-check"></i></span> <?php echo __('Verified Agent');?></div>
                            <div class="wv-profile-name"><?php echo h($agentName);?></div>
                            <div class="wv-profile-meta"><i class="fa fa-map-marker"></i> <?php echo h($agentAddress);?></div>
                            <div class="wv-socials">
                                <?php if(!empty($agent['User']['facebook'])){ echo $this->Html->link('<i class="fa fa-facebook"></i>','http://www.facebook.com/'.$agent['User']['facebook'],array('escape'=>false,'target'=>'_blank')); } ?>
                                <?php if(!empty($agent['User']['twitter'])){ echo $this->Html->link('<i class="fa fa-twitter"></i>','http://www.twitter.com/'.$agent['User']['twitter'],array('escape'=>false,'target'=>'_blank')); } ?>
                                <?php if(!empty($agent['User']['linkedin'])){ echo $this->Html->link('<i class="fa fa-linkedin"></i>','http://www.linkedin.in/'.$agent['User']['linkedin'],array('escape'=>false,'target'=>'_blank')); } ?>
                            </div>
                        </div>
                    </div>
                </section>
                <section class="wv-info-section">
                    <div class="wv-section-heading">
                        <div>
                            <h2><?php echo __('Information');?></h2>
                            <p><?php echo __('Agent contact and assignment details.');?></p>
                        </div>
                    </div>
                    <div class="wv-info-grid">
                        <div class="wv-info-item"><span class="wv-info-icon"><i class="fa fa-user"></i></span><div><div class="wv-info-label"><?php echo __('Name');?></div><div class="wv-info-value"><?php echo h($agentName);?></div></div></div>
                        <div class="wv-info-item"><span class="wv-info-icon"><i class="fa fa-mobile"></i></span><div><div class="wv-info-label"><?php echo __('Mobile');?></div><div class="wv-info-value"><?php if($agentMobile!=''){ $mobileKey=preg_replace('/[^0-9]/','',$agentMobile); if(strpos($mobileKey,'0092')===0)$mobileKey=substr($mobileKey,2); elseif(preg_match('/^0[0-9]{10}$/',$mobileKey))$mobileKey='92'.substr($mobileKey,1); ?><span class="wv-inline-contact"><a href="tel:<?php echo h(preg_replace('/[^0-9\+]/','',$agentMobile));?>"><?php echo h($agentMobile);?></a><a class="wv-inline-whatsapp" href="https://wa.me/<?php echo h($mobileKey);?>" target="_blank" rel="noopener noreferrer" title="<?php echo __('Chat on WhatsApp');?>"><i class="fa fa-whatsapp"></i></a></span><?php }else{ echo __('Not Specified'); }?></div></div></div>
                        <div class="wv-info-item"><span class="wv-info-icon"><i class="fa fa-phone"></i></span><div><div class="wv-info-label"><?php echo __('Second Mobile');?></div><div class="wv-info-value"><?php if($agentPhone!=''){ $phoneKey=preg_replace('/[^0-9]/','',$agentPhone); if(strpos($phoneKey,'0092')===0)$phoneKey=substr($phoneKey,2); elseif(preg_match('/^0[0-9]{10}$/',$phoneKey))$phoneKey='92'.substr($phoneKey,1); ?><span class="wv-inline-contact"><a href="tel:<?php echo h(preg_replace('/[^0-9\+]/','',$agentPhone));?>"><?php echo h($agentPhone);?></a><a class="wv-inline-whatsapp" href="https://wa.me/<?php echo h($phoneKey);?>" target="_blank" rel="noopener noreferrer" title="<?php echo __('Chat on WhatsApp');?>"><i class="fa fa-whatsapp"></i></a></span><?php }else{ echo __('Not Specified'); }?></div></div></div>
                        <div class="wv-info-item"><span class="wv-info-icon"><i class="fa fa-envelope"></i></span><div><div class="wv-info-label"><?php echo __('E-mail');?></div><div class="wv-info-value"><?php echo $agentEmail!='' ? h($agentEmail) : __('Not Specified');?></div></div></div>
                        <div class="wv-info-item"><span class="wv-info-icon"><i class="fa fa-skype"></i></span><div><div class="wv-info-label"><?php echo __('Skype');?></div><div class="wv-info-value"><?php echo h($agentSkype);?></div></div></div>
                        <div class="wv-info-item"><span class="wv-info-icon"><i class="fa fa-building"></i></span><div><div class="wv-info-label"><?php echo __('Assigned Properties');?></div><div class="wv-info-value"><?php echo (int)$assignedPropertyCount;?> <?php echo __('Properties');?> <?php echo $this->Html->link(__('View Below'),'#assigned-properties',array('class'=>'wv-assigned-link'));?></div></div></div>
                    </div>
                </section>
                <section class="wv-card wv-section">
                    <h2><?php echo __('About Agent');?></h2>
                    <div class="wv-description"><?php echo !empty($agent['User']['description']) ? $agent['User']['description'] : __('Not Specified');?></div>
                </section>
                <section class="wv-properties-section" id="assigned-properties">
                    <div class="wv-properties-head">
                        <div>
                            <h2><?php echo __('Assigned Properties');?></h2>
                            <p><?php echo sprintf(__('Published properties assigned to %s.'),h($agentName));?></p>
                        </div>
                        <?php echo $this->Html->link('<i class="fa fa-th-large"></i> '.__('Open Full Catalogue'),array('controller'=>'Properties','action'=>'index','agent:'.$agentId),array('escape'=>false,'class'=>'wv-catalogue-link'));?>
                    </div>
                    <div class="wv-property-grid">
                        <?php if(!empty($allProperty)){ foreach($allProperty as $post){
                            $propertyId=(int)$post['Property']['id'];
                            $propertyName=!empty($post['Property']['name']) ? $post['Property']['name'] : '';
                            $propertySlug=trim(preg_replace('/[^A-Za-z0-9]+/','-',$propertyName),'-');
                            if($propertySlug==='')$propertySlug='property-'.$propertyId;
                            $propertyUrl=$this->Html->url('/Properties/'.$propertySlug);
                            $propertyPhoto=$photoUrl.'/nia.png';
                            if(!empty($post['PropertiesPhoto']) && is_array($post['PropertiesPhoto']))
                            {
                                foreach($post['PropertiesPhoto'] as $photoArr)
                                {
                                    if(empty($photoArr['photo']))continue;
                                    $propertyPhoto=$photoUrl.'/'.$photoArr['dir'].'_thumb/'.$photoArr['photo'];
                                    if(!empty($photoArr['home']) && $photoArr['home']=='Yes')break;
                                }
                            }
                            $propertyLocation=!empty($post['Location']['name']) ? $post['Location']['name'] : __('Location not specified');
                        ?>
                        <a class="wv-property-card" href="<?php echo h($propertyUrl);?>">
                            <div class="wv-property-photo" style="background-image:url('<?php echo h($propertyPhoto);?>');"><span class="wv-property-badge"><?php echo __('Assigned');?></span></div>
                            <div class="wv-property-body">
                                <h3 class="wv-property-title"><?php echo h($propertyName);?></h3>
                                <div class="wv-property-meta"><i class="fa fa-map-marker"></i><?php echo h($propertyLocation);?></div>
                                <div class="wv-property-footer">
                                    <span class="wv-property-price"><?php echo $currency.$this->Number->format($post['Property']['price']);?></span>
                                    <span class="wv-property-open"><?php echo __('View Property');?> <i class="fa fa-angle-right"></i></span>
                                </div>
                            </div>
                        </a>
                        <?php } } else { ?>
                        <div class="wv-property-empty"><?php echo __('No assigned properties found.');?></div>
                        <?php } ?>
                    </div>
                </section>
            </main>
            <aside class="wv-sidebar">
                <div class="wv-card wv-side-card">
                    <h3 class="wv-side-title"><?php echo __('Contact Agent');?></h3>
                    <div class="wv-side-muted"><?php echo __('Send your inquiry directly to this representative.');?></div>
                    <?php if(!empty($agentContactNumbers)){?>
                    <div class="wv-contact-number-list">
                        <?php foreach($agentContactNumbers as $contact){?>
                        <div class="wv-contact-number">
                            <div class="wv-contact-number-head"><span class="wv-contact-number-label"><?php echo h($contact['label']);?></span><a class="wv-contact-number-value" href="tel:<?php echo h($contact['call']);?>"><?php echo h($contact['display']);?></a></div>
                            <div class="wv-contact-number-actions">
                                <a class="wv-call" href="tel:<?php echo h($contact['call']);?>"><i class="fa fa-phone"></i> <?php echo __('Call');?></a>
                                <a class="wv-whatsapp" href="<?php echo h($contact['whatsapp']);?>" target="_blank" rel="noopener noreferrer"><i class="fa fa-whatsapp"></i> <?php echo __('WhatsApp');?></a>
                            </div>
                        </div>
                        <?php }?>
                    </div>
                    <?php }?>
                    <?php echo $this->Form->create('Agent', array('controller'=>'Agents','action'=>'mail/'.$agentId,'name'=>'post_req','id'=>'post_req','class'=>'wv-agent-form','type'=>'file'));?>
                    <div class="form-group"><label><?php echo __('Name');?></label><input type="text" name="name" class="form-control" required="true"></div>
                    <div class="form-group"><label><?php echo __('E-mail');?></label><input type="email" name="email" class="form-control" required="true"></div>
                    <div class="form-group"><label><?php echo __('Subject');?></label><input type="text" name="subject" class="form-control" required="true"></div>
                    <div class="form-group"><label><?php echo __('Message');?></label><textarea class="form-control" rows="5" required="true" name="message" placeholder="<?php echo __('Please keep your message as simple as possible.');?>"></textarea></div>
                    <button class="wv-submit"><i class="fa fa-envelope"></i> <?php echo __('Send Message');?></button>
                    <?php echo $this->Form->end();?>
                </div>
            </aside>
        </div>
    </div>
</div>
