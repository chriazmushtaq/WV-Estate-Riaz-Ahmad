<?php
$photoUrl=$this->Html->url(array('controller'=>'img'));
$agencyId=(int)$Agency['Agency']['id'];
$agencyMailId=!empty($Agency['User']['id']) ? (int)$Agency['User']['id'] : $agencyId;
$agencyName=!empty($Agency['User']['name']) ? $Agency['User']['name'] : __('Not Specified');
$agencyAddress=!empty($Agency['User']['address']) ? $Agency['User']['address'] : __('Not Specified');
$agencyLocation=!empty($Agency['Location']['name']) ? $Agency['Location']['name'] : __('Not Specified');
$agencyPhone=!empty($Agency['User']['phone']) ? trim((string)$Agency['User']['phone']) : '';
$agencyMobile=!empty($Agency['User']['mobile']) ? trim((string)$Agency['User']['mobile']) : '';
$agencyEmail=!empty($Agency['User']['email']) ? trim((string)$Agency['User']['email']) : '';
$agencySkype=!empty($Agency['User']['skype']) ? trim((string)$Agency['User']['skype']) : '';
$agencyStatus=!empty($Agency['User']['status']) ? trim((string)$Agency['User']['status']) : '';
$agencyFrontView=isset($Agency['User']['front_view']) ? trim((string)$Agency['User']['front_view']) : '';
$agencyVerified=($agencyStatus==='Active' && $agencyFrontView!=='Off');
$agencyVerificationLabel=$agencyVerified ? __('Verified Real Estate Agency') : __('Agency Not Verified');
$formatAgencyMiniPrice=function($price) use ($currency){
    $price=(float)$price;
    if($price>=10000000)
    {
        return 'PKR '.number_format($price/10000000,2).' Crore';
    }
    if($price>=100000)
    {
        return 'PKR '.number_format($price/100000,2).' Lac';
    }
    return $currency.' '.number_format($price);
};
$agencyDescription=!empty($Agency['User']['description']) ? $Agency['User']['description'] : __('Not Specified');
$agencyLogo=empty($Agency['User']['photo']) ? $photoUrl.'/nia.png' : $photoUrl.'/user_thumb/'.$Agency['User']['photo'];
$agencyPropertyCount=isset($agencyPropertyCount) ? (int)$agencyPropertyCount : count((array)$allProperty);
$agencyAgentCount=isset($agencyAgentCount) ? (int)$agencyAgentCount : count((array)$agencyAgents);
$contactNumbers=array();
foreach(array(__('Mobile')=>$agencyMobile,__('Phone')=>$agencyPhone) as $contactLabel=>$contactNumber)
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
        $contactNumbers[$whatsappNumber]=array(
            'label'=>$contactLabel,
            'display'=>$contactNumber,
            'call'=>$callNumber,
            'whatsapp'=>'https://wa.me/'.$whatsappNumber.'?text='.rawurlencode(sprintf(__('Hello %s, I would like to know more about your properties.'),$agencyName))
        );
    }
}
?>
<style type="text/css">
.wv-agency-page{background:#f4f7fb;color:#07152a;padding-bottom:50px;}
.wv-agency-hero{background:#0b1f36;border-bottom:4px solid #ed0b46;color:#fff;padding:34px 0 78px;}
.wv-agency-hero-grid{align-items:center;display:grid;gap:22px;grid-template-columns:120px minmax(0,1fr);}
.wv-agency-logo{background:#fff;background-position:center;background-size:cover;border:4px solid #fff;border-radius:8px;box-shadow:0 14px 30px rgba(0,0,0,.22);height:120px;width:120px;}
.wv-agency-kicker{align-items:center;color:#bde3ff;display:flex;font-size:11px;font-weight:900;gap:7px;text-transform:uppercase;}
.wv-agency-kicker.is-muted{color:#d4deea;}
.wv-verified-tick{align-items:center;background:#1677d2;border:2px solid #fff;border-radius:50%;box-shadow:0 0 0 1px #1677d2;color:#fff;display:inline-flex;font-size:9px;height:18px;justify-content:center;width:18px;}
.wv-verified-tick.is-muted{background:#94a3b8;box-shadow:0 0 0 1px #94a3b8;}
.wv-agency-title{color:#fff;font-size:34px;font-weight:900;line-height:1.12;margin:7px 0 9px;}
.wv-agency-subtitle{color:#d4deea;font-size:13px;font-weight:700;margin:0;}
.wv-agency-hero-actions{display:flex;flex-wrap:wrap;gap:8px;margin-top:15px;}
.wv-hero-pill{align-items:center;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.18);border-radius:7px;color:#fff!important;display:inline-flex;font-size:12px;font-weight:900;gap:7px;padding:9px 11px;text-decoration:none!important;}
.wv-hero-pill.is-whatsapp{background:#0b6b43;border-color:#18a76a;}
.wv-agency-layout{align-items:start;display:grid;gap:22px;grid-template-columns:minmax(0,1fr) 340px;margin-top:-52px;}
.wv-card{background:#fff;border:1px solid #dce5ef;border-radius:8px;box-shadow:0 12px 28px rgba(15,23,42,.08);}
.wv-main-panel{padding:22px;}
.wv-section-head{align-items:end;display:flex;gap:14px;justify-content:space-between;margin:22px 0 12px;}
.wv-section-head:first-child{margin-top:0;}
.wv-section-head h2{color:#07152a;font-size:21px;font-weight:900;margin:0;}
.wv-section-head p{color:#64748b;font-size:12px;font-weight:700;margin:4px 0 0;}
.wv-info-grid{display:grid;gap:10px;grid-template-columns:repeat(3,minmax(0,1fr));}
.wv-info-card{align-items:center;background:#fff;border:1px solid #dce5ef;border-radius:8px;display:grid;grid-template-columns:38px minmax(0,1fr);min-height:78px;padding:12px;box-shadow:0 5px 14px rgba(15,23,42,.04);}
.wv-info-icon{align-items:center;background:#edf5ff;border-radius:8px;color:#1677d2;display:flex;font-size:15px;height:36px;justify-content:center;width:36px;}
.wv-info-label{color:#64748b;font-size:10px;font-weight:900;text-transform:uppercase;}
.wv-info-value{color:#07152a;font-size:13px;font-weight:900;margin-top:4px;word-break:break-word;}
.wv-info-value.is-verified{align-items:center;color:#0b4f8a;display:flex;gap:6px;}
.wv-info-value.is-not-verified{color:#64748b;}
.wv-info-value a{color:#07152a!important;text-decoration:none!important;}
.wv-info-value a:hover{text-decoration:underline!important;}
.wv-description-card{padding:20px;}
.wv-description-body{color:#334155;font-size:14px;line-height:1.75;overflow:hidden;word-break:break-word;}
.wv-description-body p{margin:0 0 12px;}
.wv-description-body ul,.wv-description-body ol{margin-bottom:12px;padding-left:22px;}
.wv-agents-grid{display:grid;gap:10px;grid-template-columns:repeat(4,2in);justify-content:start;}
.wv-agent-card{background:#fff;border:1px solid #dce5ef;border-radius:8px;color:inherit!important;display:flex;flex-direction:column;height:2in;min-height:0;overflow:hidden;text-decoration:none!important;transition:box-shadow .2s ease,transform .2s ease;width:2in;}
.wv-agent-card:hover{box-shadow:0 10px 20px rgba(15,23,42,.1);transform:translateY(-2px);}
.wv-agent-card-photo{background:#e8eef5;background-position:center;background-size:cover;flex:0 0 .78in;height:.78in;}
.wv-agent-card-body{display:flex;flex:1 1 auto;flex-direction:column;min-height:0;padding:7px 8px;}
.wv-agent-card-name{color:#07152a;font-size:11px;font-weight:900;line-height:1.25;margin:0 0 4px;min-height:27px;overflow:hidden;}
.wv-agent-card-meta{color:#64748b;font-size:8px;font-weight:700;line-height:1.35;min-height:24px;overflow:hidden;}
.wv-agent-card-meta i{color:#1677d2;margin-right:3px;width:9px;}
.wv-agent-card-link{border-top:1px solid #edf1f5;color:#0b4f8a;font-size:8px;font-weight:900;margin-top:auto;padding-top:6px;}
.wv-property-grid{display:grid;gap:12px;grid-template-columns:repeat(3,minmax(0,1fr));}
.wv-agency-page .verified-property-card{background:#fff;border:1px solid #f4a400;border-radius:8px;box-shadow:0 10px 22px rgba(15,23,42,.08);overflow:hidden;transition:box-shadow .2s ease,transform .2s ease;}
.wv-agency-page .verified-property-card:hover{box-shadow:0 14px 28px rgba(15,23,42,.13);transform:translateY(-2px);}
.wv-agency-page .verified-property-media{background:#dbe3ec;height:108px;position:relative;}
.wv-agency-page .verified-property-media a{display:block;height:100%;}
.wv-agency-page .verified-property-media-img{background-position:center;background-size:cover;height:100%;}
.wv-agency-page .verified-badge{background:#f5a400;border-radius:999px;color:#fff;font-size:9px;font-weight:900;left:8px;line-height:1;padding:5px 8px;position:absolute;top:8px;}
.wv-agency-page .verified-photo-count{background:#172033;border-radius:999px;color:#fff;font-size:9px;font-weight:800;line-height:1;padding:5px 8px;position:absolute;right:8px;top:8px;}
.wv-agency-page .verified-card-price{background:linear-gradient(135deg,#ff0a46,#b00035);border-radius:999px;bottom:8px;box-shadow:0 9px 18px rgba(176,0,53,.28);color:#fff;font-size:13px;font-weight:900;left:9px;line-height:1;max-width:calc(100% - 18px);overflow:hidden;padding:7px 10px;position:absolute;text-overflow:ellipsis;text-shadow:none;white-space:nowrap;}
.wv-agency-page .verified-property-body{padding:10px;}
.wv-agency-page .verified-property-name{color:#07152a!important;display:block;font-size:12px;font-weight:900;line-height:1.3;min-height:31px;text-decoration:none!important;}
.wv-agency-page .verified-property-name:hover{color:#ff0a46!important;}
.wv-agency-page .verified-property-specs{color:#617089;display:flex;flex-wrap:wrap;font-size:10px;gap:8px;margin:8px 0 9px;}
.wv-agency-page .verified-property-specs strong{color:#193255;font-weight:900;}
.wv-agency-page .verified-card-footer{align-items:center;border-top:1px solid #eef2f7;display:flex;gap:7px;padding-top:8px;}
.wv-agency-page .verified-owner-icon{background:#ef1717;border-radius:50%;color:#fff;flex:0 0 auto;font-size:10px;height:22px;line-height:22px;text-align:center;width:22px;}
.wv-agency-page .verified-owner-name{color:#07152a;font-size:10px;font-weight:900;line-height:1.1;max-width:96px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.wv-agency-page .verified-owner-name span{color:#ff0a46;font-size:7px;margin-left:2px;text-transform:uppercase;}
.wv-agency-page .verified-owner-commission{color:#059669;font-size:8px;font-weight:800;line-height:1.25;margin-top:2px;}
.wv-agency-page .verified-card-arrow{background:#fff1f4;border-radius:50%;color:#ff0a46!important;flex:0 0 auto;font-size:12px;height:24px;line-height:24px;margin-left:auto;text-align:center;text-decoration:none!important;width:24px;}
.wv-agency-page .verified-card-arrow:hover{background:#ff0a46;color:#fff!important;}
.wv-empty-state{background:#fff;border:1px dashed #b9c8d8;border-radius:8px;color:#64748b;font-size:13px;font-weight:800;grid-column:1/-1;padding:24px;text-align:center;}
.wv-sidebar{position:sticky;top:16px;}
.wv-contact-card{padding:18px;}
.wv-contact-title{color:#07152a;font-size:18px;font-weight:900;margin:0 0 5px;}
.wv-contact-muted{color:#64748b;font-size:12px;font-weight:700;margin:0 0 12px;}
.wv-contact-list{display:grid;gap:9px;margin-bottom:14px;}
.wv-contact-row{border-bottom:1px solid #edf1f5;padding-bottom:9px;}
.wv-contact-row:last-child{border-bottom:0;}
.wv-contact-label{color:#64748b;font-size:10px;font-weight:900;text-transform:uppercase;}
.wv-contact-value{color:#07152a!important;display:block;font-size:13px;font-weight:900;margin:3px 0 7px;text-decoration:none!important;}
.wv-contact-actions{display:grid;gap:7px;grid-template-columns:1fr 1fr;}
.wv-contact-actions a{align-items:center;border-radius:6px;display:flex;font-size:11px;font-weight:900;gap:6px;justify-content:center;padding:8px;text-decoration:none!important;}
.wv-call{background:#eef6ff;border:1px solid #bfd7ff;color:#062b57!important;}
.wv-whatsapp{background:#e9fff3;border:1px solid #93f2bd;color:#047857!important;}
.wv-agency-form label{color:#64748b;font-size:11px;font-weight:900;text-transform:uppercase;}
.wv-agency-form .form-control{border-color:#d8e0ea;border-radius:7px;box-shadow:none;}
.wv-agency-form input.form-control{height:39px;}
.wv-agency-submit{background:#ed0b46;border:0;border-radius:7px;box-shadow:0 10px 22px rgba(237,11,70,.2);color:#fff;font-weight:900;padding:11px;width:100%;}
.wv-agency-submit:hover{background:#bd0737;color:#fff;}
.wv-catalogue-link{align-items:center;background:#fff;border:1px solid #cfdbe8;border-radius:7px;color:#0b4f8a!important;display:inline-flex;font-size:12px;font-weight:900;gap:7px;padding:9px 12px;text-decoration:none!important;white-space:nowrap;}
.wv-catalogue-link:hover{background:#edf5ff;border-color:#9fc6ec;}
@media(max-width:1199px){.wv-agents-grid{grid-template-columns:repeat(3,2in);}.wv-property-grid{grid-template-columns:repeat(2,minmax(0,1fr));}}
@media(max-width:991px){.wv-agents-grid{grid-template-columns:repeat(3,2in);}}
@media(max-width:991px){.wv-agency-layout{display:block}.wv-sidebar{position:static;margin-top:20px}.wv-agency-hero-grid{grid-template-columns:96px minmax(0,1fr)}.wv-agency-logo{height:96px;width:96px}.wv-agency-title{font-size:28px}.wv-info-grid{grid-template-columns:repeat(2,minmax(0,1fr));}}
@media(max-width:767px){.wv-agency-hero{padding:28px 0 64px}.wv-agency-hero-grid{display:block}.wv-agency-logo{margin-bottom:14px}.wv-main-panel{padding:16px}.wv-section-head{align-items:flex-start;flex-direction:column}.wv-catalogue-link{justify-content:center;width:100%}.wv-agents-grid{grid-template-columns:repeat(1,2in);justify-content:center}.wv-property-grid,.wv-info-grid{grid-template-columns:1fr}.wv-contact-actions{grid-template-columns:1fr 1fr;}}
</style>

<div class="wv-agency-page">
    <section class="wv-agency-hero">
        <div class="container">
            <div class="wv-agency-hero-grid">
                <div class="wv-agency-logo" style="background-image:url('<?php echo h($agencyLogo);?>');"></div>
                <div>
                    <div class="wv-agency-kicker <?php echo $agencyVerified ? '' : 'is-muted';?>"><span class="wv-verified-tick <?php echo $agencyVerified ? '' : 'is-muted';?>"><i class="fa <?php echo $agencyVerified ? 'fa-check' : 'fa-minus';?>"></i></span> <?php echo h($agencyVerificationLabel);?></div>
                    <h1 class="wv-agency-title"><?php echo h($agencyName);?></h1>
                    <p class="wv-agency-subtitle"><i class="fa fa-map-marker"></i> <?php echo h($agencyAddress);?> <?php if($agencyLocation!==__('Not Specified')){?>- <?php echo h($agencyLocation);?><?php }?></p>
                    <div class="wv-agency-hero-actions">
                        <?php if($agencyMobile!==''){?><a class="wv-hero-pill" href="tel:<?php echo h(preg_replace('/[^0-9\+]/','',$agencyMobile));?>"><i class="fa fa-phone"></i> <?php echo __('Call Agency');?></a><?php }?>
                        <?php if(!empty($contactNumbers)){ $firstContact=reset($contactNumbers);?><a class="wv-hero-pill is-whatsapp" href="<?php echo h($firstContact['whatsapp']);?>" target="_blank" rel="noopener noreferrer"><i class="fa fa-whatsapp"></i> <?php echo __('WhatsApp');?></a><?php }?>
                        <?php echo $this->Html->link('<i class="fa fa-building"></i> '.__('View Properties'),array('controller'=>'Properties','action'=>'index','agency:'.$agencyId),array('escape'=>false,'class'=>'wv-hero-pill'));?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="container">
        <?php echo $this->Session->flash();?>
        <div class="wv-agency-layout">
            <main>
                <section class="wv-main-panel wv-card">
                    <div class="wv-section-head">
                        <div>
                            <h2><?php echo __('Agency Information');?></h2>
                            <p><?php echo __('Quick profile details for buyers and investors.');?></p>
                        </div>
                    </div>
                    <div class="wv-info-grid">

                        <div class="wv-info-card"><span class="wv-info-icon"><i class="fa fa-map-marker"></i></span><div><div class="wv-info-label"><?php echo __('Location');?></div><div class="wv-info-value"><?php echo h($agencyLocation);?></div></div></div>
                        <div class="wv-info-card"><span class="wv-info-icon"><i class="fa fa-phone"></i></span><div><div class="wv-info-label"><?php echo __('Phone');?></div><div class="wv-info-value"><?php echo $agencyPhone!=='' ? $this->Html->link(h($agencyPhone),'tel:'.preg_replace('/[^0-9\+]/','',$agencyPhone),array('escape'=>false)) : __('Not Specified');?></div></div></div>
                        <div class="wv-info-card"><span class="wv-info-icon"><i class="fa fa-mobile"></i></span><div><div class="wv-info-label"><?php echo __('Mobile');?></div><div class="wv-info-value"><?php echo $agencyMobile!=='' ? $this->Html->link(h($agencyMobile),'tel:'.preg_replace('/[^0-9\+]/','',$agencyMobile),array('escape'=>false)) : __('Not Specified');?></div></div></div>
                        <div class="wv-info-card"><span class="wv-info-icon"><i class="fa fa-check-circle"></i></span><div><div class="wv-info-label"><?php echo __('Verification');?></div><div class="wv-info-value <?php echo $agencyVerified ? 'is-verified' : 'is-not-verified';?>"><?php if($agencyVerified){?><span class="wv-verified-tick"><i class="fa fa-check"></i></span><?php }?><?php echo h($agencyVerificationLabel);?></div></div></div>
                        <div class="wv-info-card"><span class="wv-info-icon"><i class="fa fa-home"></i></span><div><div class="wv-info-label"><?php echo __('Properties');?></div><div class="wv-info-value"><?php echo (int)$agencyPropertyCount;?> <?php echo __('Published');?></div></div></div>
                        <div class="wv-info-card"><span class="wv-info-icon"><i class="fa fa-users"></i></span><div><div class="wv-info-label"><?php echo __('Agents');?></div><div class="wv-info-value"><?php echo (int)$agencyAgentCount;?> <?php echo __('Team Members');?></div></div></div>
                    </div>
                </section>

                <section class="wv-description-card wv-card" style="margin-top:18px;">
                    <div class="wv-section-head">
                        <div>
                            <h2><?php echo __('About Agency');?></h2>
                            <p><?php echo __('Professional overview and service profile.');?></p>
                        </div>
                    </div>
                    <div class="wv-description-body"><?php echo $agencyDescription;?></div>
                </section>

                <section style="margin-top:22px;">
                    <div class="wv-section-head">
                        <div>
                            <h2><?php echo __('Agency Agents');?></h2>
                            <p><?php echo __('Connect with the representatives assigned to this agency.');?></p>
                        </div>
                    </div>
                    <div class="wv-agents-grid">
                        <?php if(!empty($agencyAgents)){ foreach($agencyAgents as $agentRow){
                            $agentId=!empty($agentRow['User']['id']) ? (int)$agentRow['User']['id'] : 0;
                            $agentName=!empty($agentRow['User']['name']) ? $agentRow['User']['name'] : __('Not Specified');
                            $agentPhoto=empty($agentRow['User']['photo']) ? $photoUrl.'/user-icon.png' : $photoUrl.'/user_thumb/'.$agentRow['User']['photo'];
                            $agentMobile=!empty($agentRow['User']['mobile']) ? $agentRow['User']['mobile'] : (!empty($agentRow['User']['phone']) ? $agentRow['User']['phone'] : '');
                            $agentEmail=!empty($agentRow['User']['email']) ? $agentRow['User']['email'] : '';
                        ?>
                        <a class="wv-agent-card" href="<?php echo $this->Html->url(array('controller'=>'Agents','action'=>'view',$agentId));?>">
                            <span class="wv-agent-card-photo" style="background-image:url('<?php echo h($agentPhoto);?>');"></span>
                            <span class="wv-agent-card-body">
                                <span class="wv-agent-card-name"><?php echo h($agentName);?></span>
                                <span class="wv-agent-card-meta"><?php if($agentMobile!==''){?><i class="fa fa-phone"></i><?php echo h($agentMobile);?><br><?php }?><?php if($agentEmail!==''){?><i class="fa fa-envelope"></i><?php echo h($agentEmail);?><?php }?></span>
                                <span class="wv-agent-card-link"><?php echo __('View Agent Profile');?> <i class="fa fa-angle-right"></i></span>
                            </span>
                        </a>
                        <?php } } else { ?>
                        <div class="wv-empty-state"><?php echo __('No active agency agents found.');?></div>
                        <?php } ?>
                    </div>
                </section>

                <section style="margin-top:24px;">
                    <div class="wv-section-head">
                        <div>
                            <h2><?php echo __('Agency Properties');?></h2>
                            <p><?php echo __('Published properties represented by this agency.');?></p>
                        </div>
                        <?php echo $this->Html->link('<i class="fa fa-th-large"></i> '.__('Open Full Catalogue'),array('controller'=>'Properties','action'=>'index','agency:'.$agencyId),array('escape'=>false,'class'=>'wv-catalogue-link'));?>
                    </div>
                    <div class="wv-property-grid">
                        <?php if(!empty($allProperty)){ foreach($allProperty as $post){
                            $propertyId=(int)$post['Property']['id'];
                            $propertyName=!empty($post['Property']['name']) ? $post['Property']['name'] : __('Property');
                            $propertyPrice=isset($post['Property']['price']) ? $formatAgencyMiniPrice($post['Property']['price']) : '-';
                            $propertySlug=trim(preg_replace('/[^A-Za-z0-9]+/','-',$propertyName),'-');
                            if($propertySlug==='')$propertySlug='property-'.$propertyId;
                            $propertyUrl=$this->Html->url('/Properties/'.$propertySlug);
                            $propertyPhoto=$photoUrl.'/nia.png';
                            $propertyPhotoCount=!empty($post['PropertiesPhoto']) && is_array($post['PropertiesPhoto']) ? count($post['PropertiesPhoto']) : 0;
                            if(!empty($post['PropertiesPhoto']) && is_array($post['PropertiesPhoto']))
                            {
                                foreach($post['PropertiesPhoto'] as $photoArr)
                                {
                                    if(empty($photoArr['photo']))continue;
                                    $photoDir=!empty($photoArr['dir']) ? $photoArr['dir'] : (!empty($photoArr['dirt']) ? $photoArr['dirt'] : '');
                                    $candidatePhoto=$photoUrl.'/'.trim($photoDir).'_thumb/'.$photoArr['photo'];
                                    if(!empty($photoArr['home']) && $photoArr['home']=='Yes')
                                    {
                                        $propertyPhoto=$candidatePhoto;
                                        break;
                                    }
                                    $propertyPhoto=$candidatePhoto;
                                }
                            }
                            $ownerName=!empty($post['User']['name']) ? $post['User']['name'] : $agencyName;
                            $beds=!empty($post['Property']['bedroom']) ? $post['Property']['bedroom'] : '-';
                            $baths=!empty($post['Property']['bathroom']) ? $post['Property']['bathroom'] : '-';
                            $area=!empty($post['Property']['area']) ? $post['Property']['area'] : '-';
                            if(!empty($post['Unit']['name']) && $area !== '-')
                            {
                                $area.=' '.$post['Unit']['name'];
                            }
                        ?>
                        <article class="verified-property-card">
                            <div class="verified-property-media">
                                <a href="<?php echo h($propertyUrl);?>">
                                    <div class="verified-property-media-img" style="background-image:url('<?php echo h($propertyPhoto);?>');"></div>
                                </a>
                                <span class="verified-badge gold"><span class="fa fa-star"></span> <?php echo __('Gold Verified');?></span>
                                <span class="verified-photo-count"><span class="fa fa-camera"></span> <?php echo (int)$propertyPhotoCount;?> <?php echo __('photos');?></span>
                                <div class="verified-card-price"><?php echo h($propertyPrice);?></div>
                            </div>
                            <div class="verified-property-body">
                                <?php echo $this->Html->link(h($propertyName),$propertyUrl,array('escape'=>false,'class'=>'verified-property-name'));?>
                                <div class="verified-property-specs">
                                    <span><span class="fa fa-bed"></span> <strong><?php echo h($beds);?></strong> <?php echo __('Beds');?></span>
                                    <span><span class="fa fa-bath"></span> <strong><?php echo h($baths);?></strong> <?php echo __('Baths');?></span>
                                    <span><span class="fa fa-expand"></span> <strong><?php echo h($area);?></strong></span>
                                </div>
                                <div class="verified-card-footer">
                                    <div class="verified-owner-icon"><span class="fa fa-user"></span></div>
                                    <div>
                                        <div class="verified-owner-name"><?php echo h($ownerName);?><span><?php echo __('Owner');?></span></div>
                                        <div class="verified-owner-commission"><span class="fa fa-check-circle"></span> <?php echo __('Direct Owner - 0% Commission');?></div>
                                    </div>
                                    <?php echo $this->Html->link('<span class="fa fa-angle-right"></span>',$propertyUrl,array('escape'=>false,'class'=>'verified-card-arrow'));?>
                                </div>
                            </div>
                        </article>
                        <?php } } else { ?>
                        <div class="wv-empty-state"><?php echo __('No published agency properties found.');?></div>
                        <?php } ?>
                    </div>
                </section>
            </main>

            <aside class="wv-sidebar">
                <div class="wv-contact-card wv-card">
                    <h3 class="wv-contact-title"><?php echo __('Contact Agency');?></h3>
                    <p class="wv-contact-muted"><?php echo __('Send your inquiry directly to this agency.');?></p>
                    <?php if(!empty($contactNumbers)){?>
                    <div class="wv-contact-list">
                        <?php foreach($contactNumbers as $contact){?>
                        <div class="wv-contact-row">
                            <div class="wv-contact-label"><?php echo h($contact['label']);?></div>
                            <a class="wv-contact-value" href="tel:<?php echo h($contact['call']);?>"><?php echo h($contact['display']);?></a>
                            <div class="wv-contact-actions">
                                <a class="wv-call" href="tel:<?php echo h($contact['call']);?>"><i class="fa fa-phone"></i> <?php echo __('Call');?></a>
                                <a class="wv-whatsapp" href="<?php echo h($contact['whatsapp']);?>" target="_blank" rel="noopener noreferrer"><i class="fa fa-whatsapp"></i> <?php echo __('WhatsApp');?></a>
                            </div>
                        </div>
                        <?php }?>
                    </div>
                    <?php }?>
                    <?php echo $this->Form->create('Agency', array('controller'=>'Agencies','action'=>'mail/'.$agencyMailId,'name'=>'post_req','id'=>'post_req','class'=>'wv-agency-form'));?>
                    <div class="form-group"><label><?php echo __('Name');?></label><?php echo $this->Form->input('name',array('label'=>false,'class'=>'form-control','required'=>true,'div'=>false));?></div>
                    <div class="form-group"><label><?php echo __('E-mail');?></label><?php echo $this->Form->input('email',array('type'=>'email','label'=>false,'class'=>'form-control','required'=>true,'div'=>false));?></div>
                    <div class="form-group"><label><?php echo __('Phone Number');?></label><?php echo $this->Form->input('phone',array('type'=>'number','label'=>false,'class'=>'form-control','required'=>true,'div'=>false));?></div>
                    <div class="form-group"><label><?php echo __('Subject');?></label><?php echo $this->Form->input('subject',array('label'=>false,'class'=>'form-control','required'=>true,'div'=>false));?></div>
                    <div class="form-group"><label><?php echo __('Message');?></label><?php echo $this->Form->input('message',array('placeholder'=>__('Please keep your message as simple as possible.'),'label'=>false,'class'=>'form-control','rows'=>5,'required'=>true,'div'=>false));?></div>
                    <button class="wv-agency-submit" type="submit"><i class="fa fa-envelope"></i> <?php echo __('Send Message');?></button>
                    <?php echo $this->Form->end();?>
                </div>
            </aside>
        </div>
    </div>
</div>