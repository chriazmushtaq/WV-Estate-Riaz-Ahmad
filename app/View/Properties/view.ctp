<?php
$googleKeyAvailable = !empty($mapKey) && trim($mapKey) !== '';
$mapProvider = (isset($defaultMapProvider) && strtolower($defaultMapProvider)==='google' && $googleKeyAvailable) ? 'google' : 'osm';
$this->set('wvLayoutNeedsLeaflet', true);
if($mapProvider==='google' && !$this->Session->check('frontUser')){
    echo $this->Html->script('https://maps.googleapis.com/maps/api/js?key='.$mapKey);
} else {
    echo $this->Html->css('/design1000/assets/css/leaflet.css');
    echo '<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />';
    echo $this->Html->script('/design1000/assets/js/leaflet.js');
}
if(!empty($post['Property']['allow_rating']) && $post['Property']['allow_rating']){
    echo $this->Html->script('star-rating.min');
    echo $this->Html->css('star-rating.min');
}
$id = (int)$post['Property']['id'];
$photoUrl = $this->Html->url(array('controller'=>'img'));
$urlModal = $this->Html->url(array('controller'=>'Properties','action'=>'sendbymail/'.$id));
$urlCalcModal = $this->Html->url(array('controller'=>'Properties','action'=>'calculator/'.$post['Property']['price']));
$trackCallUrl = $this->Html->url(array('controller'=>'Properties','action'=>'trackcontact',$id,'call'));
$trackWhatsappUrl = $this->Html->url(array('controller'=>'Properties','action'=>'trackcontact',$id,'whatsapp'));
$trackEmailUrl = $this->Html->url(array('controller'=>'Properties','action'=>'trackcontact',$id,'email'));
$trackDocumentUrl = $this->Html->url(array('controller'=>'Properties','action'=>'trackcontact',$id,'document'));
$propertyName = !empty($post['Property']['name']) ? $post['Property']['name'] : __('Property');
$propertyAddress = !empty($post['Property']['address']) ? $post['Property']['address'] : '';
$propertyLocation = !empty($post['Location']['name']) ? $post['Location']['name'] : __('Not Specified');
$propertyType = !empty($post['Type']['name']) ? $post['Type']['name'] : __('Not Specified');
$propertyContract = !empty($post['Contract']['name']) ? $post['Contract']['name'] : __('Not Specified');
$propertyStatus = !empty($post['Property']['status']) ? $post['Property']['status'] : __('Not Specified');
$propertyPrice = !empty($post['Property']['price']) ? $currency.' '.$this->Number->format($post['Property']['price']) : __('Not Specified');
$propertyArea = !empty($post['Property']['area']) ? $post['Property']['area'].' '.$post['Unit']['name'] : __('Not Specified');
$propertyBeds = !empty($post['Property']['bedroom']) ? $post['Property']['bedroom'] : '-';
$propertyBaths = !empty($post['Property']['bathroom']) ? $post['Property']['bathroom'] : '-';
$propertyViews = $this->Number->format((!empty($post['Property']['views']) ? (int)$post['Property']['views'] : 0) + 1);
$propertyCreated = !empty($post['Property']['created']) ? date('d M Y', strtotime($post['Property']['created'])) : __('Not Specified');
$propertyUpdated = !empty($post['Property']['modified']) ? date('d M Y', strtotime($post['Property']['modified'])) : __('Not Specified');
$propertyViewUrl = $this->Function->propertyViewUrl($propertyName,$id,true);
$canonicalUrl = !empty($propertySeo['PropertySeo']['canonical_url']) ? $propertySeo['PropertySeo']['canonical_url'] : $propertyViewUrl;
if(!empty($canonicalUrl))
{
    $this->append('meta','<link rel="canonical" href="'.h($canonicalUrl).'">');
}

// Complete address resolution with hierarchy
$addressHierarchyPath = !empty($post['Property']['address_hierarchy_path']) ? trim($post['Property']['address_hierarchy_path']) : '';
if (empty($addressHierarchyPath) && class_exists('ClassRegistry')) {
    try {
        $AddresshierarchyModel = ClassRegistry::init('Admin.Addresshierarchy');
        if ($AddresshierarchyModel && method_exists($AddresshierarchyModel, 'resolveHierarchyFromRow')) {
            $resolvedHierarchy = $AddresshierarchyModel->resolveHierarchyFromRow(!empty($post['Property']) ? $post['Property'] : array());
            if (!empty($resolvedHierarchy['address_hierarchy_path'])) {
                $addressHierarchyPath = $resolvedHierarchy['address_hierarchy_path'];
            }
        }
    } catch(Exception $e) {}
}

$addressParts = array();
if (!empty($propertyAddress)) {
    foreach (explode(',', $propertyAddress) as $sa) {
        $sa = trim($sa);
        if ($sa !== '') {
            $addressParts[] = $sa;
        }
    }
}
if (!empty($propertyLocation) && $propertyLocation !== __('Not Specified')) {
    $addressParts[] = $propertyLocation;
}
if (!empty($addressHierarchyPath)) {
    foreach (explode(',', $addressHierarchyPath) as $hp) {
        $hp = trim($hp);
        if ($hp !== '') {
            $addressParts[] = $hp;
        }
    }
} else {
    $levelOrder = array('block','town','city','tehsil','district','province','country');
    foreach ($levelOrder as $level) {
        $fid = !empty($post['Property']['address_'.$level.'_id']) ? (int)$post['Property']['address_'.$level.'_id'] : 0;
        if ($fid > 0 && class_exists('ClassRegistry')) {
            try {
                $AddresshierarchyModel = ClassRegistry::init('Admin.Addresshierarchy');
                $node = $AddresshierarchyModel->find('first', array('conditions'=>array('id'=>$fid), 'fields'=>array('name'), 'recursive'=>-1));
                if (!empty($node['Addresshierarchy']['name'])) {
                    $addressParts[] = trim($node['Addresshierarchy']['name']);
                }
            } catch(Exception $e) {}
        }
    }
}

$uniqueParts = array();
$seenParts = array();
foreach ($addressParts as $part) {
    $key = mb_strtolower(trim($part), 'UTF-8');
    if ($key !== '' && !isset($seenParts[$key])) {
        $seenParts[$key] = true;
        $uniqueParts[] = trim($part);
    }
}
$completeAddress = !empty($uniqueParts) ? implode(', ', $uniqueParts) : ($propertyLocation !== __('Not Specified') ? $propertyLocation : (!empty($propertyAddress) ? $propertyAddress : __('Not Specified')));

$shareText = trim($propertyName.' | '.$propertyPrice.' | '.$completeAddress);
$encodedShareUrl = rawurlencode($propertyViewUrl);
$encodedShareText = rawurlencode($shareText);
$agencyId = !empty($post['Project']['id']) ? (int)$post['Project']['id'] : (!empty($post['Property']['project_id']) ? (int)$post['Property']['project_id'] : 0);
$agencyProfileUrl = $agencyId>0 ? $this->Html->url(array('controller'=>'Agencies','action'=>'view',$agencyId)) : $this->Html->url(array('controller'=>'Agencies','action'=>'index'));
$agentName = !empty($userArr['User']['name']) ? $userArr['User']['name'] : __('White Villas');
$agentPhone = !empty($userArr['User']['phone']) ? $userArr['User']['phone'] : '';
$agentMobile = !empty($userArr['User']['mobile']) ? $userArr['User']['mobile'] : '';
$agentEmail = !empty($userArr['User']['email']) ? $userArr['User']['email'] : '';
$assignedAgentVisible = !empty($userArr['User']['id']) && !empty($userArr['User']['ugroup_id']) && (int)$userArr['User']['ugroup_id']===2 && (!isset($userArr['User']['status']) || $userArr['User']['status']==='Active') && (!isset($userArr['User']['front_view']) || $userArr['User']['front_view']!=='Off');
$assignedAgentId = $assignedAgentVisible ? (int)$userArr['User']['id'] : 0;
$assignedAgentUrl = $assignedAgentVisible ? $this->Html->url(array('controller'=>'Agents','action'=>'view',$assignedAgentId)) : '';
$assignedAgentPhoto = (!empty($userArr['User']['photo'])) ? $photoUrl.'/user_thumb/'.$userArr['User']['photo'] : $photoUrl.'/user-icon.png';
$assignedAgentPrimaryPhone = $agentMobile !== '' ? $agentMobile : $agentPhone;

$ts = !empty($themeSettings) ? $themeSettings : array();
if (empty($ts)) {
    try {
        $RealEstateThemeSetting = ClassRegistry::init('RealEstateTheme.RealEstateThemeSetting');
        if ($RealEstateThemeSetting) {
            $tsRow = $RealEstateThemeSetting->find('first', array('conditions' => array('id' => 1), 'recursive' => -1));
            if (!empty($tsRow['RealEstateThemeSetting'])) {
                $ts = $tsRow['RealEstateThemeSetting'];
            }
        }
    } catch(Exception $e) {}
}

$btnPdfText = !empty($ts['btn_pdf_text']) ? $ts['btn_pdf_text'] : __('Payment Plan');
$btnPdfPcEnabled = !isset($ts['btn_pdf_pc_enabled']) || !empty($ts['btn_pdf_pc_enabled']);
$btnPdfMobileEnabled = !isset($ts['btn_pdf_mobile_enabled']) || !empty($ts['btn_pdf_mobile_enabled']);

$btnWaText = !empty($ts['btn_whatsapp_text']) ? $ts['btn_whatsapp_text'] : __('WhatsApp');
$btnWaPcEnabled = !isset($ts['btn_whatsapp_pc_enabled']) || !empty($ts['btn_whatsapp_pc_enabled']);
$btnWaMobileEnabled = !isset($ts['btn_whatsapp_mobile_enabled']) || !empty($ts['btn_whatsapp_mobile_enabled']);

$btnCallText = !empty($ts['btn_call_text']) ? $ts['btn_call_text'] : __('Call Owner');
$btnCallPcEnabled = !isset($ts['btn_call_pc_enabled']) || !empty($ts['btn_call_pc_enabled']);
$btnCallMobileEnabled = !isset($ts['btn_call_mobile_enabled']) || !empty($ts['btn_call_mobile_enabled']);

$cardAgentPcEnabled = !isset($ts['card_agent_pc_enabled']) || !empty($ts['card_agent_pc_enabled']);
$cardAgentMobileEnabled = !isset($ts['card_agent_mobile_enabled']) || !empty($ts['card_agent_mobile_enabled']);
$cardAgentBadgeText = !empty($ts['card_agent_badge_text']) ? $ts['card_agent_badge_text'] : __('Verified Property Representative');

if ($cardAgentPcEnabled && $cardAgentMobileEnabled) {
    $cardAgentCardClass = '';
} elseif ($cardAgentPcEnabled && !$cardAgentMobileEnabled) {
    $cardAgentCardClass = 'd-none d-lg-block';
} elseif (!$cardAgentPcEnabled && $cardAgentMobileEnabled) {
    $cardAgentCardClass = 'd-block d-lg-none';
} else {
    $cardAgentCardClass = 'd-none';
}
$assignedAgentCallPhone = preg_replace('/[^0-9\+]/', '', (string)$assignedAgentPrimaryPhone);
$assignedAgentWhatsappPhone = preg_replace('/[^0-9]/', '', (string)$assignedAgentPrimaryPhone);
if(preg_match('/^0[0-9]{10}$/',$assignedAgentWhatsappPhone))
{
    $assignedAgentWhatsappPhone='92'.substr($assignedAgentWhatsappPhone,1);
}
$assignedAgentMessage = "Hello ".$agentName.", I am interested in this property.\n\nProperty: ".$propertyName."\nPrice: ".$propertyPrice."\nLocation: ".$completeAddress."\nProperty Link: ".$propertyViewUrl."\n\nPlease contact me with more details.";
$assignedAgentWhatsappUrl = ($assignedAgentVisible && $assignedAgentWhatsappPhone!=='') ? 'https://wa.me/'.$assignedAgentWhatsappPhone.'?text='.rawurlencode($assignedAgentMessage) : '';
$agencyLogo = $photoUrl.'/logo-website.fw.png';
if(!empty($userArr['User']['photo']))
{
    $agencyLogo = $photoUrl.'/user_thumb/'.$userArr['User']['photo'];
}
$fallbackPhone = !empty($contact) ? $contact : '+923391101156';
$primaryPhone = $agentMobile !== '' ? $agentMobile : ($agentPhone !== '' ? $agentPhone : (!empty($agencyTrust['mobile']) && $agencyTrust['mobile']!=__('Not Specified') ? $agencyTrust['mobile'] : $fallbackPhone));
$callPhone = preg_replace('/[^0-9\+]/', '', (string)$primaryPhone);
if(empty($callPhone)){ $callPhone = '+923391101156'; }
$whatsappPhone = preg_replace('/[^0-9]/', '', (string)$primaryPhone);
if(empty($whatsappPhone)){ $whatsappPhone = '923391101156'; }
if(preg_match('/^0[0-9]{10}$/',$whatsappPhone))
{
    $whatsappPhone='92'.substr($whatsappPhone,1);
}
$whatsappMessage = "Hello, I am interested in this property.\n\nProperty: ".$propertyName."\nPrice: ".$propertyPrice."\nLocation: ".$completeAddress."\nProperty Link: ".$propertyViewUrl."\n\nPlease share more details.";
$whatsappUrl = 'https://wa.me/'.$whatsappPhone.'?text='.rawurlencode($whatsappMessage);
$socialShareLinks = array(
    array('class'=>'whatsapp','icon'=>'fa-whatsapp','label'=>__('WhatsApp'),'url'=>'https://wa.me/?text='.$encodedShareText.'%0A'.$encodedShareUrl),
    array('class'=>'facebook','icon'=>'fa-facebook','label'=>__('Facebook'),'url'=>'https://www.facebook.com/sharer/sharer.php?u='.$encodedShareUrl),
    array('class'=>'twitter','icon'=>'fa-twitter','label'=>__('X / Twitter'),'url'=>'https://twitter.com/intent/tweet?text='.$encodedShareText.'&url='.$encodedShareUrl),
    array('class'=>'linkedin','icon'=>'fa-linkedin','label'=>__('LinkedIn'),'url'=>'https://www.linkedin.com/sharing/share-offsite/?url='.$encodedShareUrl),
    array('class'=>'telegram','icon'=>'fa-paper-plane','label'=>__('Telegram'),'url'=>'https://t.me/share/url?url='.$encodedShareUrl.'&text='.$encodedShareText),
    array('class'=>'email','icon'=>'fa-envelope','label'=>__('E-mail'),'url'=>'mailto:?subject='.rawurlencode($propertyName).'&body='.$encodedShareText.'%0A'.$encodedShareUrl)
);
$frontPhotos = array();
$galleryPhotos = array();
foreach((array)$post['PropertiesPhoto'] as $photoRow)
{
    if(empty($photoRow['photo']))
    {
        continue;
    }
    $photoDir = !empty($photoRow['dir']) ? $photoRow['dir'] : (!empty($photoRow['dirt']) ? $photoRow['dirt'] : '');
    $photoItem = array(
        'full' => $photoUrl.'/'.$photoDir.'/'.$photoRow['photo'],
        'thumb' => $photoUrl.'/'.$photoDir.'_thumb/'.$photoRow['photo'],
        'alt' => !empty($photoRow['alt_text']) ? $photoRow['alt_text'] : $propertyName,
        'title' => !empty($photoRow['title']) ? $photoRow['title'] : $propertyName
    );
    if(!empty($photoRow['home']) && $photoRow['home']==='Yes')
    {
        $frontPhotos[] = $photoItem;
    }
    else
    {
        $galleryPhotos[] = $photoItem;
    }
}
$photos = array_merge($frontPhotos,$galleryPhotos);
if(empty($photos))
{
    $photos[] = array('full'=>$photoUrl.'/nia.png','thumb'=>$photoUrl.'/nia.png','alt'=>$propertyName,'title'=>$propertyName);
}
$mainPhoto = $photos[0]['full'];
$hasGalleryCarousel = !empty($galleryPhotos) && count($photos)>1;
$propertyDescriptionHtml = __('Not Specified');
if(!empty($post['Property']['description']))
{
    $descriptionRaw = (string)$post['Property']['description'];
    $descriptionDecoded = html_entity_decode($descriptionRaw, ENT_QUOTES, 'UTF-8');
    $descriptionDecoded = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $descriptionDecoded);
    $descriptionDecoded = preg_replace('/<iframe\b[^>]*>.*?<\/iframe>/is', '', $descriptionDecoded);
    $descriptionHasHtml = (strip_tags($descriptionDecoded) !== $descriptionDecoded);
    if($descriptionHasHtml)
    {
        $allowedDescriptionTags = '<p><br><b><strong><i><em><u><ul><ol><li><table><thead><tbody><tr><th><td><h1><h2><h3><h4><h5><h6><div><span><blockquote><a><img><hr><sub><sup><s><del><figure><figcaption><font><mark><small>';
        $propertyDescriptionHtml = strip_tags($descriptionDecoded, $allowedDescriptionTags);
        $propertyDescriptionHtml = preg_replace('/\s+on[a-z0-9_-]+\s*=\s*(["\']).*?\1/is', '', $propertyDescriptionHtml);
        $propertyDescriptionHtml = preg_replace('/\s+on[a-z0-9_-]+\s*=\s*[^\s>]+/is', '', $propertyDescriptionHtml);
        $propertyDescriptionHtml = preg_replace('/javascript\s*:/i', '', $propertyDescriptionHtml);
    }
    else
    {
        $descriptionParts = preg_split('/\R{2,}/', trim($descriptionRaw));
        $paragraphs = array();
        foreach((array)$descriptionParts as $descriptionPart)
        {
            $descriptionPart = trim($descriptionPart);
            if($descriptionPart !== '')
            {
                $paragraphs[] = '<p>'.nl2br(htmlspecialchars($descriptionPart, ENT_NOQUOTES, 'UTF-8')).'</p>';
            }
        }
        $propertyDescriptionHtml = !empty($paragraphs) ? implode('', $paragraphs) : __('Not Specified');
    }
}
$mapLat = !empty($post['Property']['latitude']) ? (float)$post['Property']['latitude'] : 0;
$mapLng = !empty($post['Property']['longitude']) ? (float)$post['Property']['longitude'] : 0;
if(abs($mapLat)<1 && abs($mapLng)<1)
{
    $mapLat = 31.5204;
    $mapLng = 74.3587;
}
$nearbyPlaces = array();
if(!empty($post['Property']['nearby_places_json']))
{
    $decodedNearby = json_decode((string)$post['Property']['nearby_places_json'], true);
    if(is_array($decodedNearby))
    {
        foreach($decodedNearby as $nearbyRow)
        {
            if(!is_array($nearbyRow) || empty($nearbyRow['name']))
            {
                continue;
            }
            $nearbyPlaces[] = array(
                'name' => (string)$nearbyRow['name'],
                'category' => !empty($nearbyRow['category']) ? (string)$nearbyRow['category'] : __('Nearby'),
                'distance' => !empty($nearbyRow['distance']) ? (int)$nearbyRow['distance'] : 0
            );
        }
    }
}
$nearbyIconFor = function($category) {
    $key = strtolower((string)$category);
    if(strpos($key,'school')!==false || strpos($key,'college')!==false || strpos($key,'university')!==false) { return 'fa-graduation-cap'; }
    if(strpos($key,'cafe')!==false || strpos($key,'restaurant')!==false || strpos($key,'food')!==false) { return 'fa-coffee'; }
    if(strpos($key,'road')!==false || strpos($key,'street')!==false) { return 'fa-road'; }
    if(strpos($key,'hospital')!==false || strpos($key,'clinic')!==false || strpos($key,'pharmacy')!==false) { return 'fa-medkit'; }
    if(strpos($key,'park')!==false || strpos($key,'garden')!==false) { return 'fa-tree'; }
    if(strpos($key,'bank')!==false) { return 'fa-bank'; }
    if(strpos($key,'market')!==false || strpos($key,'shop')!==false || strpos($key,'mall')!==false) { return 'fa-shopping-cart'; }
    if(strpos($key,'city')!==false || strpos($key,'town')!==false || strpos($key,'village')!==false || strpos($key,'neighbourhood')!==false || strpos($key,'neighborhood')!==false || strpos($key,'suburb')!==false || strpos($key,'residential')!==false || strpos($key,'complex')!==false || strpos($key,'locality')!==false) { return 'fa-building-o'; }
    if(strpos($key,'mosque')!==false || strpos($key,'place')!==false) { return 'fa-university'; }
    if(strpos($key,'bus')!==false || strpos($key,'transport')!==false) { return 'fa-bus'; }
    return 'fa-map-marker';
};
$nearbyDistanceLabel = function($distance) {
    $distance = (int)$distance;
    if($distance <= 0)
    {
        return '';
    }
    if($distance < 1000)
    {
        return $distance.'m';
    }
    $km = $distance / 1000;
    return ($km >= 10 ? round($km) : rtrim(rtrim(number_format($km, 1), '0'), '.')).'km';
};
$mapPropertyMarkers = array();
foreach((array)$mapProperties as $mapPost)
{
    $markerLat = !empty($mapPost['Property']['latitude']) ? (float)$mapPost['Property']['latitude'] : 0;
    $markerLng = !empty($mapPost['Property']['longitude']) ? (float)$mapPost['Property']['longitude'] : 0;
    if(abs($markerLat)<1 && abs($markerLng)<1)
    {
        continue;
    }
    $markerId = !empty($mapPost['Property']['id']) ? (int)$mapPost['Property']['id'] : 0;
    $markerName = !empty($mapPost['Property']['name']) ? $mapPost['Property']['name'] : __('Property');
    $markerPrice = isset($mapPost['Property']['price']) ? $currency.' '.$this->Number->format($mapPost['Property']['price']) : __('Not Specified');
    $markerLocation = !empty($mapPost['Location']['name']) ? $mapPost['Location']['name'] : (!empty($mapPost['Property']['address']) ? $mapPost['Property']['address'] : __('Not Specified'));
    $markerUnit = !empty($mapPost['Unit']['name']) ? $mapPost['Unit']['name'] : '';
    $markerArea = !empty($mapPost['Property']['area']) ? trim($mapPost['Property']['area'].' '.$markerUnit) : __('Not Specified');
    $markerBeds = !empty($mapPost['Property']['bedroom']) ? $mapPost['Property']['bedroom'] : '-';
    $markerBaths = !empty($mapPost['Property']['bathroom']) ? $mapPost['Property']['bathroom'] : '-';
    $markerPhoto = $photoUrl.'/nia.png';
    if(!empty($mapPost['PropertiesPhoto']) && is_array($mapPost['PropertiesPhoto']))
    {
        foreach($mapPost['PropertiesPhoto'] as $markerPhotoRow)
        {
            if(empty($markerPhotoRow['photo']))
            {
                continue;
            }
            $markerPhotoDir = !empty($markerPhotoRow['dir']) ? $markerPhotoRow['dir'] : (!empty($markerPhotoRow['dirt']) ? $markerPhotoRow['dirt'] : 'propertiesphotos');
            $markerPhoto = $photoUrl.'/'.$markerPhotoDir.'_thumb/'.$markerPhotoRow['photo'];
            if(!empty($markerPhotoRow['home']) && $markerPhotoRow['home']==='Yes')
            {
                break;
            }
        }
    }
    $mapPropertyMarkers[] = array(
        'id'=>$markerId,
        'lat'=>$markerLat,
        'lng'=>$markerLng,
        'title'=>$markerName,
        'price'=>$markerPrice,
        'location'=>$markerLocation,
        'area'=>$markerArea,
        'beds'=>(string)$markerBeds,
        'baths'=>(string)$markerBaths,
        'photo'=>$markerPhoto,
        'url'=>$this->Function->propertyViewUrl($markerName,$markerId),
        'active'=>($markerId===$id)
    );
}
?>
<style>
.wv-detail-page{background:#f7f9fc;padding:18px 0 34px 0;}
.wv-breadcrumb{color:#718096;font-size:12px;margin-bottom:10px;}
.wv-detail-grid{display:grid;grid-template-columns:minmax(0,1fr) 330px;gap:22px;align-items:start;}
.wv-card{background:#fff;border:1px solid #dfe7f1;border-radius:8px;box-shadow:0 8px 22px rgba(15,23,42,.06);}
.wv-gallery-card{overflow:hidden;border-radius:8px;background:#fff;border:1px solid #dfe7f1;box-shadow:0 8px 22px rgba(15,23,42,.06);position:relative;}
.wv-gallery-main{height:460px;background:#0b1528;position:relative;overflow:hidden;display:flex;align-items:center;justify-content:center;}
.wv-gallery-main img{width:100%;height:100%;object-fit:cover;display:block;transition:opacity 0.25s ease-in-out;}
.wv-gallery-nav{position:absolute;top:50%;transform:translateY(-50%);width:44px;height:44px;border-radius:50%;background:rgba(8,18,37,.78);border:1px solid rgba(255,255,255,.35);color:#fff;display:flex;align-items:center;justify-content:center;font-size:16px;cursor:pointer;transition:all .2s ease;z-index:10;padding:0;outline:none;}
.wv-gallery-nav:hover{background:#ff0a46;border-color:#ff0a46;color:#fff;transform:translateY(-50%) scale(1.08);}
.wv-gallery-prev{left:14px;}
.wv-gallery-next{right:14px;}
.wv-badge{position:absolute;left:14px;top:14px;border-radius:16px;color:#fff;font-size:11px;font-weight:900;padding:6px 11px;z-index:5;}
.wv-badge.gold{background:#f5a400;}
.wv-photo-counter{position:absolute;right:14px;top:14px;background:rgba(8,18,37,.86);border-radius:16px;color:#fff;font-size:11px;font-weight:800;padding:6px 10px;z-index:5;}
.wv-gallery-bottom-actions{align-items:center;bottom:14px;display:flex;gap:8px;position:absolute;right:14px;z-index:5;}
.wv-view-counter,.wv-image-share{align-items:center;background:rgba(7,21,42,.92);border:1px solid rgba(255,255,255,.18);border-radius:999px;box-shadow:0 10px 24px rgba(7,21,42,.24);color:#fff!important;display:inline-flex;font-size:12px;font-weight:900;gap:6px;padding:7px 12px;text-decoration:none!important;}
.wv-image-share{background:linear-gradient(135deg,#ff0a46,#c70032);border-color:rgba(255,255,255,.28);}
.wv-image-share:hover{background:linear-gradient(135deg,#c70032,#8f0026);color:#fff!important;text-decoration:none!important;}
.wv-thumbs{display:flex;gap:10px;overflow-x:auto;padding:12px 14px;background:#ffffff;border-top:1px solid #dfe7f1;scroll-behavior:smooth;-webkit-overflow-scrolling:touch;}
.wv-thumbs::-webkit-scrollbar{height:6px;}
.wv-thumbs::-webkit-scrollbar-thumb{background:#cbd5e1;border-radius:4px;}
.wv-thumb{border:2px solid #e2e8f0;border-radius:8px;cursor:pointer;flex:0 0 92px;height:66px;overflow:hidden;position:relative;opacity:0.72;transition:all .2s ease-in-out;}
.wv-thumb:hover{opacity:1;border-color:#ff0a46;transform:translateY(-2px);box-shadow:0 4px 12px rgba(0,0,0,.12);}
.wv-thumb.active{border-color:#ff0a46;opacity:1;box-shadow:0 0 0 2px rgba(255,10,70,.3);transform:scale(1.02);}
.wv-thumb img{width:100%;height:100%;object-fit:cover;display:block;}
.wv-gallery-zoom-btn{align-items:center;background:rgba(7,21,42,.92);border:1px solid rgba(255,255,255,.24);border-radius:999px;box-shadow:0 10px 24px rgba(7,21,42,.24);color:#fff!important;display:inline-flex;font-size:12px;font-weight:900;gap:6px;padding:7px 13px;text-decoration:none!important;cursor:pointer;outline:none;transition:all .2s ease;}
.wv-gallery-zoom-btn:hover{background:linear-gradient(135deg,#ff0a46,#c70032);border-color:rgba(255,255,255,.32);color:#fff!important;transform:scale(1.04);}
.wv-gallery-main{cursor:zoom-in;}
.wv-gallery-main button,.wv-gallery-main a,.wv-gallery-main .wv-badge,.wv-gallery-main .wv-photo-counter{cursor:default;}
.wv-gallery-nav,.wv-gallery-zoom-btn,.wv-image-share{cursor:pointer!important;}

/* Photo Zoom Lightbox Modal */
.wv-zoom-modal{position:fixed;top:0;left:0;right:0;bottom:0;width:100vw;height:100vh;z-index:999999;display:none;flex-direction:column;align-items:stretch;justify-content:space-between;background:rgba(5,12,24,0.96);backdrop-filter:blur(14px);-webkit-backdrop-filter:blur(14px);overflow:hidden;}
.wv-zoom-header{display:flex;align-items:center;justify-content:space-between;padding:12px 20px;background:rgba(7,21,42,0.85);border-bottom:1px solid rgba(255,255,255,0.12);z-index:10;color:#fff;}
.wv-zoom-title-wrap{display:flex;align-items:center;gap:14px;min-width:0;}
.wv-zoom-title{font-size:15px;font-weight:900;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color:#fff;}
.wv-zoom-counter{background:rgba(255,255,255,0.12);border:1px solid rgba(255,255,255,0.18);border-radius:999px;padding:4px 12px;font-size:12px;font-weight:800;color:#8ff7ff;white-space:nowrap;}
.wv-zoom-toolbar{display:flex;align-items:center;gap:8px;}
.wv-zoom-tool-btn{background:rgba(255,255,255,0.1);border:1px solid rgba(255,255,255,0.2);border-radius:8px;color:#fff;width:38px;height:38px;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:all 0.18s ease;font-size:14px;outline:none;}
.wv-zoom-tool-btn:hover{background:#ff0a46;border-color:#ff0a46;transform:scale(1.08);}
.wv-zoom-close-btn{background:rgba(255,10,70,0.85);border:1px solid #ff5d82;border-radius:8px;color:#fff;width:38px;height:38px;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:22px;font-weight:700;line-height:1;transition:all 0.18s ease;outline:none;}
.wv-zoom-close-btn:hover{background:#ff0a46;transform:scale(1.1);box-shadow:0 0 16px rgba(255,10,70,0.6);}
.wv-zoom-stage{flex:1;position:relative;display:flex;align-items:center;justify-content:center;overflow:hidden;user-select:none;touch-action:none;}
.wv-zoom-img-wrapper{display:flex;align-items:center;justify-content:center;width:100%;height:100%;transition:transform 0.1s ease-out;cursor:grab;}
.wv-zoom-img-wrapper.is-zoomed{cursor:grab;}
.wv-zoom-img-wrapper.is-dragging{cursor:grabbing;}
.wv-zoom-img{max-width:92vw;max-height:75vh;object-fit:contain;border-radius:6px;box-shadow:0 20px 50px rgba(0,0,0,0.6);transition:opacity 0.2s ease, transform 0.2s ease;pointer-events:auto;}
.wv-zoom-nav{position:absolute;top:50%;transform:translateY(-50%);width:52px;height:52px;border-radius:50%;background:rgba(8,18,37,.88);border:1.5px solid rgba(255,255,255,.35);color:#fff;display:flex;align-items:center;justify-content:center;font-size:20px;cursor:pointer;transition:all .2s ease;z-index:10;outline:none;}
.wv-zoom-nav:hover{background:#ff0a46;border-color:#ff0a46;transform:translateY(-50%) scale(1.12);box-shadow:0 0 20px rgba(255,10,70,.5);}
.wv-zoom-prev{left:20px;}
.wv-zoom-next{right:20px;}
.wv-zoom-thumbs-strip{display:flex;gap:10px;justify-content:center;padding:10px 18px;background:rgba(7,21,42,0.92);border-top:1px solid rgba(255,255,255,0.12);overflow-x:auto;z-index:10;max-height:86px;}
.wv-zoom-thumbs-strip::-webkit-scrollbar{height:4px;}
.wv-zoom-thumbs-strip::-webkit-scrollbar-thumb{background:#64748b;border-radius:4px;}
.wv-zoom-thumb{flex:0 0 72px;height:52px;border:2px solid rgba(255,255,255,0.25);border-radius:6px;overflow:hidden;cursor:pointer;opacity:0.6;transition:all 0.2s ease;}
.wv-zoom-thumb img{width:100%;height:100%;object-fit:cover;display:block;}
.wv-zoom-thumb:hover{opacity:1;border-color:#8ff7ff;transform:translateY(-2px);}
.wv-zoom-thumb.active{opacity:1;border-color:#ff0a46;box-shadow:0 0 0 2px rgba(255,10,70,0.5);transform:scale(1.06);}
@media(max-width:767px){
    .wv-zoom-header{padding:10px 14px;}
    .wv-zoom-title{font-size:13px;max-width:140px;}
    .wv-zoom-nav{width:40px;height:40px;font-size:16px;}
    .wv-zoom-prev{left:8px;}
    .wv-zoom-next{right:8px;}
    .wv-zoom-thumb{flex:0 0 54px;height:40px;}
}
.wv-verify-strip{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin:14px 0;}
.wv-verify-item{background:#fff;border:1px solid #dfe7f1;border-radius:8px;padding:11px;display:flex;gap:10px;align-items:center;}
.wv-verify-icon{background:#fff1f4;color:#ff0a46;border-radius:50%;height:34px;line-height:34px;text-align:center;width:34px;}
.wv-verify-title{font-size:12px;font-weight:900;color:#07152a;}
.wv-verify-sub{font-size:11px;color:#718096;}
.wv-title-card{padding:18px;margin-bottom:14px;}
.wv-title-card h1{font-size:28px;font-weight:900;line-height:1.2;margin:0 0 8px;color:#07152a;}
.wv-location-line{color:#64748b;font-size:13px;}
.wv-title-meta{align-items:center;color:#52657c;display:flex;flex-wrap:wrap;font-size:12px;font-weight:800;gap:8px;margin-top:10px;}
.wv-title-meta span{align-items:center;background:#f8fafc;border:1px solid #e1e9f2;border-radius:999px;display:inline-flex;gap:6px;padding:6px 10px;}
.wv-title-meta i{color:#ff0a46;}
.wv-price-grid{display:grid;grid-template-columns:repeat(4,1fr);border:1px solid #dfe7f1;border-radius:8px;overflow:hidden;margin-top:16px;}
.wv-price-cell{background:#fff;padding:14px;text-align:center;border-right:1px solid #dfe7f1;}
.wv-price-cell:last-child{border-right:0;}
.wv-price-cell strong{display:block;color:#ff0a46;font-size:18px;font-weight:900;}
.wv-price-cell span{align-items:center;color:#64748b;display:inline-flex;font-size:11px;font-weight:800;gap:5px;justify-content:center;text-transform:uppercase;}
.wv-price-cell span i{color:#ff0a46;font-size:12px;}
.wv-section{padding:18px;margin-bottom:14px;}
.wv-section h2{font-size:18px;font-weight:900;margin:0 0 12px;color:#07152a;}
.wv-attributes{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;}
.wv-attr{border:1px solid #dfe7f1;border-radius:8px;padding:12px;background:#fbfdff;}
.wv-attr i{color:#ff0a46;margin-right:7px;}
.wv-attr-label{color:#718096;font-size:11px;text-transform:uppercase;font-weight:800;}
.wv-attr-value{color:#07152a;font-size:13px;font-weight:900;margin-top:4px;}
.wv-description{color:#334155;font-family:inherit,"Segoe UI Emoji","Apple Color Emoji","Noto Color Emoji",sans-serif;font-size:14px;line-height:1.75;overflow-wrap:anywhere;}
.wv-description p{margin:0 0 12px;}
.wv-description p:last-child{margin-bottom:0;}
.wv-description h1,.wv-description h2,.wv-description h3,.wv-description h4,.wv-description h5,.wv-description h6{color:#07152a;font-weight:900;line-height:1.3;margin:14px 0 8px;}
.wv-description ul,.wv-description ol{margin:0 0 12px 20px;padding:0;}
.wv-description li{margin:4px 0;}
.wv-description table{border-collapse:collapse;margin:12px 0;max-width:100%;width:100%;}
.wv-description th,.wv-description td{border:1px solid #dfe7f1;padding:8px 10px;vertical-align:top;}
.wv-description th{background:#f8fafc;color:#07152a;font-weight:900;}
.wv-description img{border-radius:6px;height:auto;max-width:100%;}
.wv-description blockquote{border-left:4px solid #ff0a46;color:#52657c;margin:12px 0;padding:8px 12px;background:#fff7fa;}
.wv-amenities{display:flex;flex-wrap:wrap;gap:8px;margin:0;padding:0;list-style:none;}
.wv-amenities li{align-items:center;background:#fff1f4;border:1px solid #ffd5df;border-radius:18px;color:#9f1239;display:inline-flex;font-size:12px;font-weight:800;gap:7px;padding:7px 11px;}
.wv-amenities li i{align-items:center;background:#ff0a46;border-radius:50%;color:#fff;display:inline-flex;font-size:11px;height:22px;justify-content:center;line-height:1;width:22px;}
.wv-nearby-heading{align-items:center;display:flex!important;gap:9px;}
.wv-nearby-heading i{align-items:center;background:#fff1f4;border-radius:50%;color:#ff0a46;display:inline-flex;font-size:13px;height:30px;justify-content:center;width:30px;}
.wv-nearby-places{display:grid;gap:12px;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));}
.wv-nearby-place{align-items:flex-start;background:linear-gradient(180deg,#ffffff 0%,#f8fbff 100%);border:1px solid #dbe6f2;border-radius:8px;box-shadow:0 10px 24px rgba(15,23,42,.06);display:flex;gap:12px;min-height:82px;padding:13px;position:relative;}
.wv-nearby-place:before{background:#ff0a46;border-radius:8px 0 0 8px;bottom:0;content:"";left:-1px;position:absolute;top:0;width:3px;}
.wv-nearby-place-icon{align-items:center;background:#eef8ff;border:1px solid #cfe8ff;border-radius:8px;color:#0b65c2;display:flex;flex:0 0 40px;height:40px;justify-content:center;width:40px;}
.wv-nearby-place-icon i{font-size:16px;}
.wv-nearby-place-body{min-width:0;width:100%;}
.wv-nearby-place-name{color:#07152a;font-size:14px;font-weight:900;line-height:1.28;overflow-wrap:anywhere;}
.wv-nearby-place-meta{align-items:center;display:flex;flex-wrap:wrap;gap:6px;margin-top:8px;}
.wv-nearby-category,.wv-nearby-distance{border-radius:999px;display:inline-flex;font-size:10px;font-weight:900;line-height:1;padding:6px 8px;text-transform:uppercase;}
.wv-nearby-category{background:#eef2ff;color:#1d4ed8;}
.wv-nearby-distance{background:#ecfdf5;color:#047857;}
.wv-nearby-empty{align-items:center;background:#fbfdff;border:1px dashed #b8c8da;border-radius:8px;color:#52657c;display:flex;font-size:13px;font-weight:800;gap:9px;padding:14px;}
.wv-map{height:380px;border-radius:8px;overflow:hidden;border:1px solid #dfe7f1;position:relative;z-index:1;}
.wv-map-location-pin-wrap{overflow:visible!important;}
.wv-map-location-pin{align-items:center;animation:wvMapPinBouncePulse 850ms ease-in-out infinite;color:#ff0a46;display:flex;font-size:46px;height:58px;justify-content:center;line-height:1;text-shadow:0 10px 20px rgba(7,21,42,.28);transform-origin:center bottom;width:52px;}
.wv-map-location-pin.active{color:#16a34a;font-size:62px;height:74px;text-shadow:0 14px 26px rgba(22,163,74,.34);width:66px;}
.wv-map-location-pin i{-webkit-text-stroke:2px #fff;}
@keyframes wvMapPinBouncePulse{0%,100%{transform:translateY(0) scale(1);}50%{transform:translateY(-10px) scale(1.16);}}
.wv-map-popup-card{background:#fff;border:1px solid #dfe7f1;border-radius:8px;box-shadow:0 14px 28px rgba(7,21,42,.18);overflow:hidden;width:245px;}
.wv-map-popup-img{background:#eef4fb center/cover no-repeat;height:115px;width:100%;}
.wv-map-popup-body{padding:10px 11px;}
.wv-map-popup-title{color:#07152a;font-size:13px;font-weight:900;line-height:1.3;margin-bottom:5px;}
.wv-map-popup-price{color:#b0002e;font-size:14px;font-weight:900;margin-bottom:5px;}
.wv-map-popup-meta{color:#64748b;font-size:11px;font-weight:800;line-height:1.35;}
.wv-map-popup-specs{border-top:1px solid #edf2f7;display:flex;gap:8px;margin-top:8px;padding-top:8px;}
.wv-map-popup-specs span{align-items:center;color:#07152a;display:inline-flex;font-size:11px;font-weight:900;gap:4px;}
.wv-map-popup-specs i{color:#ff0a46;}
.wv-map-popup-link{background:#07152a;border-radius:6px;color:#fff!important;display:block;font-size:11px;font-weight:900;margin-top:9px;padding:8px;text-align:center;text-decoration:none!important;}
.wv-map .leaflet-popup-content{margin:0;}
.wv-map .leaflet-popup-content-wrapper{border-radius:8px;padding:0;}
.wv-sidebar{position:sticky;top:16px;}
.wv-side-card{padding:14px;margin-bottom:14px;}
.wv-contact-card{border-color:#d7e1ee;padding:16px;}
.wv-mobile-contact-card{display:none;}
.wv-side-price{background:linear-gradient(135deg,#fff6f8,#fff);border:1px solid #ffd4df;border-radius:10px;color:#b0002e;font-family:inherit,"Segoe UI",Arial,sans-serif;font-size:36px;font-weight:900;letter-spacing:.2px;line-height:1.08;margin-bottom:9px;padding:13px 10px;text-align:center;text-shadow:0 8px 20px rgba(255,10,70,.16);}
.wv-contact-card .wv-side-muted{text-align:right;}
.wv-side-muted{color:#5d6f85;font-size:12px;font-weight:800;}
.wv-side-meta{align-items:center;display:flex;flex-wrap:wrap;gap:6px;justify-content:flex-end;line-height:1.45;}
.wv-side-meta strong{color:#07152a;font-weight:900;}
.wv-side-meta .wv-side-contract{background:#fff7ed;border:1px solid #fed7aa;border-radius:999px;color:#9a3412;display:inline-flex;font-size:11px;font-weight:900;padding:4px 8px;}
.wv-contact-row{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:14px;}
.wv-contact-row a{border-radius:8px;font-size:13px;font-weight:900;padding:12px 10px;text-align:center;text-decoration:none!important;display:flex;align-items:center;justify-content:center;gap:6px;letter-spacing:.2px;transition:all .18s ease;}
.wv-call{background:linear-gradient(135deg,#0a2540,#1e3a8a);color:#fff!important;border:1px solid #1e3a8a;box-shadow:0 6px 16px rgba(10,37,64,.25);}
.wv-call:hover{background:linear-gradient(135deg,#07152a,#0f2b66);color:#fff!important;box-shadow:0 8px 20px rgba(10,37,64,.35);transform:translateY(-1px);}
.wv-whatsapp{background:linear-gradient(135deg,#25D366,#128C7E);color:#fff!important;border:1px solid #1ebb5a;box-shadow:0 6px 16px rgba(37,211,102,.3);}
.wv-whatsapp:hover{background:linear-gradient(135deg,#20ba5a,#0d7367);color:#fff!important;box-shadow:0 8px 20px rgba(37,211,102,.4);transform:translateY(-1px);}
.wv-payment-plan-btn{background:linear-gradient(135deg,#ff0a46,#c70032);border:1px solid #ff5d82;box-shadow:0 8px 20px rgba(255,10,70,.26);color:#fff!important;display:flex;align-items:center;justify-content:center;gap:8px;font-size:13.5px;font-weight:900;border-radius:8px;margin-top:10px;padding:12px 14px;text-align:center;text-decoration:none!important;letter-spacing:.4px;text-transform:uppercase;transition:all .18s ease;cursor:pointer;width:100%;}
.wv-payment-plan-btn:hover{background:linear-gradient(135deg,#c70032,#8f0026);color:#fff!important;box-shadow:0 10px 24px rgba(255,10,70,.36);transform:translateY(-1px);}
.wv-email-btn{background:#fff;color:#07152a!important;border:1.5px solid #d1dbe8;border-radius:8px;display:flex;align-items:center;justify-content:center;gap:7px;font-size:13px;font-weight:800;margin-top:8px;padding:11px 14px;text-align:center;text-decoration:none!important;box-shadow:0 4px 10px rgba(0,0,0,.03);transition:all .18s ease;}
.wv-email-btn:hover{background:#f1f5f9;border-color:#94a3b8;color:#07152a!important;}
.wv-inquiry-title{font-size:16px;font-weight:900;color:#07152a;margin:0 0 6px;}
.wv-form .form-control{border-color:#d8e0ea;box-shadow:none;border-radius:6px;}
.wv-form label{font-size:11px;color:#64748b;text-transform:uppercase;font-weight:800;}
.wv-submit{background:#ff0a46;border:0;border-radius:7px;color:#fff;font-weight:900;padding:11px;width:100%;}
.wv-agent-card{background:linear-gradient(135deg,#07152a 0%,#102746 60%,#ff0a46 160%);border:0;color:#fff;overflow:hidden;padding:0;}
.wv-agency-head{display:flex;gap:12px;align-items:center;padding:16px;border-bottom:1px solid rgba(255,255,255,.12);}
.wv-agency-logo{background:#fff;border-radius:12px;box-shadow:0 10px 22px rgba(0,0,0,.22);display:block;height:58px;overflow:hidden;padding:5px;text-decoration:none!important;width:58px;flex:0 0 58px;transition:transform .18s ease,box-shadow .18s ease;}
.wv-agency-logo:hover{box-shadow:0 14px 28px rgba(0,0,0,.3);transform:translateY(-1px);}
.wv-agency-logo img{width:100%;height:100%;object-fit:contain;}
.wv-agency-kicker{color:#8ff7ff;font-size:10px;font-weight:900;letter-spacing:.04em;text-transform:uppercase;}
.wv-agent-name{font-size:16px;font-weight:900;color:#fff;line-height:1.2;margin-top:2px;}
.wv-agent-name-link{color:#fff!important;display:inline-block;text-decoration:none!important;}
.wv-agent-name-link:hover{color:#8ff7ff!important;text-decoration:none!important;}
.wv-agency-sub{color:rgba(255,255,255,.72);font-size:11px;font-weight:700;margin-top:3px;}
.wv-agency-body{padding:14px 16px 16px;}
.wv-agency-contact{display:grid;gap:8px;margin-bottom:12px;}
.wv-agency-contact a,.wv-agency-contact span{align-items:center;background:rgba(255,255,255,.09);border:1px solid rgba(255,255,255,.12);border-radius:8px;color:#fff!important;display:flex;font-size:12px;font-weight:800;gap:8px;min-width:0;padding:9px 10px;text-decoration:none!important;}
.wv-agency-contact i{color:#8ff7ff;width:14px;text-align:center;}
.wv-agency-contact small{display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.wv-agency-stats{display:grid;grid-template-columns:repeat(2,1fr);gap:8px;}
.wv-agency-stat{background:#fff;border-radius:8px;padding:10px;text-align:center;}
.wv-agency-stat strong{color:#07152a;display:block;font-size:16px;font-weight:900;}
.wv-agency-stat span{color:#64748b;font-size:10px;font-weight:900;text-transform:uppercase;}
.wv-trust-list{list-style:none;margin:0;padding:0;}
.wv-trust-list li{display:flex;justify-content:space-between;border-bottom:1px solid #eef2f7;padding:8px 0;font-size:12px;}
.wv-trust-list li:last-child{border-bottom:0;}
.wv-trust-list strong{color:#64748b;}
.wv-trust-list span{color:#07152a;font-weight:900;text-align:right;}
.wv-action-list{display:grid;grid-template-columns:repeat(2,1fr);gap:8px;}
.wv-action-list a{background:#f8fafc;border:1px solid #dfe7f1;border-radius:7px;color:#07152a!important;font-size:12px;font-weight:800;padding:9px;text-align:center;text-decoration:none!important;}
.wv-assigned-agent-card{overflow:hidden;padding:0;}
.wv-assigned-agent-head{align-items:center;background:#fff;display:flex;gap:12px;padding:14px;}
.wv-assigned-agent-photo{background:#eef4fb center/cover no-repeat;border:3px solid #fff;border-radius:12px;box-shadow:0 10px 22px rgba(15,23,42,.16);display:block;height:68px;width:68px;flex:0 0 68px;text-decoration:none!important;}
.wv-assigned-agent-kicker{color:#ff0a46;font-size:10px;font-weight:900;text-transform:uppercase;}
.wv-assigned-agent-name{color:#07152a!important;display:inline-block;font-size:16px;font-weight:900;line-height:1.2;margin-top:3px;text-decoration:none!important;}
.wv-assigned-agent-name:hover{color:#ff0a46!important;text-decoration:none!important;}
.wv-assigned-agent-sub{color:#64748b;font-size:11px;font-weight:800;margin-top:4px;}
.wv-assigned-agent-body{border-top:1px solid #edf2f7;padding:12px 14px 14px;}
.wv-assigned-agent-contact{display:grid;gap:7px;margin-bottom:10px;}
.wv-assigned-agent-contact a,.wv-assigned-agent-contact span{align-items:center;background:#f8fafc;border:1px solid #e3ebf4;border-radius:7px;color:#07152a!important;display:flex;font-size:12px;font-weight:800;gap:8px;min-width:0;padding:8px 9px;text-decoration:none!important;}
.wv-assigned-agent-contact i{color:#ff0a46;width:14px;text-align:center;}
.wv-assigned-agent-contact small{display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.wv-assigned-agent-actions{display:flex;gap:8px;}
.wv-assigned-agent-actions a{border-radius:7px;flex:1;font-size:12px;font-weight:900;padding:9px;text-align:center;text-decoration:none!important;}
.wv-agent-profile-btn{background:#07152a;color:#fff!important;}
.wv-agent-whatsapp-btn{background:#e9fff3;border:1px solid #93f2bd;color:#047857!important;}
.wv-share-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;}
.wv-share-btn{align-items:center;border-radius:8px;color:#fff!important;display:flex;font-size:12px;font-weight:900;gap:7px;justify-content:center;padding:11px 9px;text-decoration:none!important;}
.wv-share-btn.whatsapp{background:#16a34a;}
.wv-share-btn.facebook{background:#1877f2;}
.wv-share-btn.twitter{background:#111827;}
.wv-share-btn.linkedin{background:#0a66c2;}
.wv-share-btn.telegram{background:#229ed9;}
.wv-share-btn.email{background:#64748b;}
.wv-copy-link-row{align-items:center;background:#f8fafc;border:1px solid #dfe7f1;border-radius:8px;display:flex;gap:8px;margin-top:12px;padding:8px;}
.wv-copy-link-row input{background:#fff;border:1px solid #dfe7f1;border-radius:6px;color:#07152a;flex:1;font-size:12px;font-weight:700;height:36px;padding:0 10px;}
.wv-copy-link-row button{background:#07152a;border:0;border-radius:6px;color:#fff;font-size:12px;font-weight:900;height:36px;padding:0 12px;}
.wv-similar-section{border-top:1px solid #dfe7f1;margin-top:28px;padding:24px 0 38px;}
.wv-similar-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;}
.wv-similar-head h2{font-size:18px;font-weight:900;color:#07152a;margin:0;}
.wv-similar-view{color:#ff0a46!important;font-size:12px;font-weight:800;text-decoration:none!important;}
.wv-similar-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px;}
.wv-similar-card{border:1px solid #dfe7f1;border-radius:10px;overflow:hidden;background:#fff;box-shadow:0 12px 24px rgba(15,23,42,.05);color:inherit;display:block;text-decoration:none!important;transition:box-shadow .18s ease,transform .18s ease,border-color .18s ease;}
.wv-similar-card:hover{border-color:#ffb1c4;box-shadow:0 18px 34px rgba(15,23,42,.1);transform:translateY(-2px);text-decoration:none!important;}
.wv-similar-img{height:220px;background-size:cover;background-position:center;position:relative;}
.wv-similar-tags{position:absolute;top:10px;left:10px;display:flex;gap:6px;z-index:2;}
.wv-tag-sale,.wv-tag-direct{display:inline-flex;align-items:center;border-radius:6px;font-size:10px;font-weight:900;padding:5px 9px;}
.wv-tag-sale{background:#13bf86;color:#fff;}
.wv-tag-direct{background:#fff;color:#0b4d36;border:1px solid #c9f2e1;}
.wv-similar-body{padding:14px 14px 10px;}
.wv-similar-price{color:#07152a;font-size:18px;font-weight:900;margin-bottom:4px;}
.wv-similar-title{display:block;color:#12304f!important;font-size:13px;font-weight:800;line-height:1.35;min-height:36px;text-decoration:none!important;}
.wv-similar-meta{border-bottom:1px solid #edf2f7;color:#64748b;font-size:11px;margin-top:10px;padding-bottom:12px;}
.wv-similar-footer{display:flex;align-items:center;justify-content:space-between;padding-top:11px;}
.wv-owner-wrap{display:flex;align-items:center;gap:8px;min-width:0;}
.wv-owner-icon{align-items:center;background:#eef4fb;border-radius:50%;color:#94a3b8;display:flex;height:24px;justify-content:center;width:24px;}
.wv-owner-name{color:#07152a;font-size:11px;font-weight:800;line-height:1.1;max-width:110px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.wv-owner-label{color:#ff0a46;font-size:9px;font-weight:800;line-height:1.1;}
.wv-detail-link{color:#ff0a46!important;font-size:11px;font-weight:900;text-decoration:none!important;white-space:nowrap;}
.wv-price-history{display:grid;gap:10px;}
.wv-price-history-row{align-items:center;background:#fbfdff;border:1px solid #dfe7f1;border-radius:8px;display:grid;gap:10px;grid-template-columns:120px 1fr auto;padding:11px;}
.wv-price-history-date{color:#64748b;font-size:11px;font-weight:900;text-transform:uppercase;}
.wv-price-history-values{color:#07152a;font-size:13px;font-weight:900;}
.wv-price-history-values span{color:#64748b;font-size:11px;font-weight:800;margin:0 6px;}
.wv-price-change-badge{background:#fff1f4;border:1px solid #ffd5df;border-radius:999px;color:#9f1239;font-size:11px;font-weight:900;padding:6px 10px;white-space:nowrap;}
@media(max-width:991px){.wv-detail-grid{grid-template-columns:1fr}.wv-sidebar{position:static}.wv-gallery-main{height:320px}.wv-thumbs{gap:8px;padding:10px}.wv-thumb{flex:0 0 76px;height:54px}.wv-mobile-contact-card{display:block;margin:14px 0}.wv-sidebar>.wv-contact-card{display:none}.wv-price-grid,.wv-verify-strip,.wv-attributes,.wv-similar-grid,.wv-share-grid{grid-template-columns:1fr}.wv-gallery-bottom-actions{left:14px;right:14px;justify-content:flex-end;flex-wrap:wrap}}
@media(max-width:767px){.wv-price-history-row{grid-template-columns:1fr}.wv-price-change-badge{display:inline-block;width:max-content;}}
</style>

<div class="page-wrapper wv-detail-page">
    <div class="container">
        <div class="wv-breadcrumb"><?php echo __('Home');?> / <?php echo __('Properties');?> / <?php echo h($propertyName);?></div>
        <?php echo $this->Session->flash();?>
        <div class="wv-detail-grid">
            <main>
                <div class="wv-card wv-gallery-card" data-gallery-auto="<?php echo $hasGalleryCarousel ? '1' : '0';?>">
                    <div class="wv-gallery-main" id="wvGalleryMain" title="<?php echo __('Click to view high-resolution zoom');?>">
                        <img id="wvMainPhoto" src="<?php echo h($mainPhoto);?>" alt="<?php echo h($photos[0]['alt']);?>" title="<?php echo h($photos[0]['title']);?>">
                        <?php if(count($photos) > 1){ ?>
                        <button type="button" class="wv-gallery-nav wv-gallery-prev" id="wvGalleryPrev" aria-label="<?php echo __('Previous');?>"><i class="fa fa-chevron-left"></i></button>
                        <button type="button" class="wv-gallery-nav wv-gallery-next" id="wvGalleryNext" aria-label="<?php echo __('Next');?>"><i class="fa fa-chevron-right"></i></button>
                        <?php } ?>
                        <span class="wv-badge gold"><i class="fa fa-star"></i> <?php echo __('Gold Verified');?></span>
                        <span class="wv-photo-counter"><i class="fa fa-camera"></i> <span id="wvCurrentPhotoIndex">1</span> / <?php echo count($photos);?> <?php echo __('Visuals');?></span>
                        <div class="wv-gallery-bottom-actions">
                            <button type="button" class="wv-gallery-zoom-btn" id="wvOpenZoomBtn" title="<?php echo __('Zoom / View Fullscreen');?>"><i class="fa fa-search-plus"></i> <?php echo __('Zoom');?></button>
                            <span class="wv-view-counter"><i class="fa fa-eye"></i> <?php echo h($propertyViews);?> <?php echo __('Views');?></span>
                            <a class="wv-image-share" href="javascript:void(0)" data-toggle="modal" data-target="#shareModal"><i class="fa fa-share-alt"></i> <?php echo __('Share');?></a>
                        </div>
                    </div>
                    <div class="wv-card wv-side-card wv-contact-card wv-mobile-contact-card">
                        <div class="wv-side-price"><?php echo h($propertyPrice);?></div>
                        <div class="wv-side-muted wv-side-meta">
                            <strong><?php echo h($propertyArea);?></strong>
                            <span>&middot;</span>
                            <strong><?php echo h($completeAddress);?></strong>
                            <span>|</span>
                            <span class="wv-side-contract"><?php echo h($propertyContract);?></span>
                        </div>
                        <div class="wv-contact-row">
                            <?php if($callPhone !== '' && $btnCallMobileEnabled){ ?><a class="wv-call wv-track-contact" data-track-url="<?php echo h($trackCallUrl);?>" href="tel:<?php echo h($callPhone);?>"><i class="fa fa-phone"></i> <?php echo h($btnCallText);?></a><?php } ?>
                            <?php if($whatsappUrl !== '' && $btnWaMobileEnabled){ ?><a class="wv-whatsapp wv-track-contact" data-track-url="<?php echo h($trackWhatsappUrl);?>" href="<?php echo h($whatsappUrl);?>" target="_blank"><i class="fa fa-whatsapp"></i> <?php echo h($btnWaText);?></a><?php } ?>
                        </div>
                        <?php if($btnPdfMobileEnabled){ ?>
                        <button type="button" class="wv-payment-plan-btn wv-open-plan-modal" data-toggle="modal" data-target="#leadDownloadModal" data-bs-toggle="modal" data-bs-target="#leadDownloadModal" data-property-id="<?php echo (int)$id; ?>" data-property-name="<?php echo h($propertyName); ?>" data-property-title="<?php echo h($propertyName); ?>" style="margin-top: 10px; width: 100%;">
                            <i class="fa fa-file-pdf-o"></i> <?php echo h($btnPdfText); ?>
                        </button>
                        <?php } ?>
                        <?php echo $this->Html->link('<i class="fa fa-envelope"></i> '.__('Send E-mail'),'javascript:void(0)',array('onclick'=>"show_modal('$urlModal');",'data-track-url'=>$trackEmailUrl,'escape'=>false,'class'=>'wv-send wv-track-contact','style'=>'display:block;border-radius:7px;margin-top:10px;padding:12px;text-align:center;text-decoration:none;'));?>
                    </div>
                    <?php if(count($photos) > 1){ ?>
                    <div class="wv-thumbs" id="wvThumbsContainer">
                        <?php foreach($photos as $k=>$photo){ ?>
                        <div class="wv-thumb <?php echo $k===0 ? 'active' : '';?>" data-index="<?php echo $k+1;?>" data-full="<?php echo h($photo['full']);?>" data-alt="<?php echo h($photo['alt']);?>" data-title="<?php echo h($photo['title']);?>">
                            <img src="<?php echo h($photo['thumb']);?>" alt="<?php echo h($photo['alt']);?>" title="<?php echo h($photo['title']);?>">
                        </div>
                        <?php } ?>
                    </div>
                    <?php } ?>
                </div>

                <!-- Photo Zoom Lightbox Modal -->
                <div id="wvPhotoZoomModal" class="wv-zoom-modal" aria-hidden="true" role="dialog">
                    <div class="wv-zoom-header">
                        <div class="wv-zoom-title-wrap">
                            <span class="wv-zoom-title"><?php echo h($propertyName);?></span>
                            <span class="wv-zoom-counter"><span id="wvZoomCurrentIndex">1</span> / <?php echo count($photos);?></span>
                        </div>
                        <div class="wv-zoom-toolbar">
                            <button type="button" class="wv-zoom-tool-btn" id="wvZoomInBtn" title="<?php echo __('Zoom In (+)');?>"><i class="fa fa-search-plus"></i></button>
                            <button type="button" class="wv-zoom-tool-btn" id="wvZoomOutBtn" title="<?php echo __('Zoom Out (-)');?>"><i class="fa fa-search-minus"></i></button>
                            <button type="button" class="wv-zoom-tool-btn" id="wvZoomResetBtn" title="<?php echo __('Reset Zoom');?>"><i class="fa fa-arrows-alt"></i></button>
                            <button type="button" class="wv-zoom-close-btn" id="wvZoomCloseBtn" title="<?php echo __('Close (Esc)');?>" aria-label="<?php echo __('Close');?>">&times;</button>
                        </div>
                    </div>
                    <div class="wv-zoom-stage" id="wvZoomStage">
                        <div class="wv-zoom-img-wrapper" id="wvZoomImgWrapper">
                            <img id="wvZoomImg" src="<?php echo h($mainPhoto);?>" alt="<?php echo h($photos[0]['alt']);?>">
                        </div>
                        <?php if(count($photos) > 1){ ?>
                        <button type="button" class="wv-zoom-nav wv-zoom-prev" id="wvZoomPrev" aria-label="<?php echo __('Previous');?>"><i class="fa fa-chevron-left"></i></button>
                        <button type="button" class="wv-zoom-nav wv-zoom-next" id="wvZoomNext" aria-label="<?php echo __('Next');?>"><i class="fa fa-chevron-right"></i></button>
                        <?php } ?>
                    </div>
                    <?php if(count($photos) > 1){ ?>
                    <div class="wv-zoom-thumbs-strip" id="wvZoomThumbsStrip">
                        <?php foreach($photos as $k=>$photo){ ?>
                        <div class="wv-zoom-thumb <?php echo $k===0 ? 'active' : '';?>" data-index="<?php echo $k+1;?>" data-full="<?php echo h($photo['full']);?>" data-alt="<?php echo h($photo['alt']);?>" data-title="<?php echo h($photo['title']);?>">
                            <img src="<?php echo h($photo['thumb']);?>" alt="<?php echo h($photo['alt']);?>">
                        </div>
                        <?php } ?>
                    </div>
                    <?php } ?>
                </div>

                <div class="wv-verify-strip">
                    <div class="wv-verify-item"><div class="wv-verify-icon"><i class="fa fa-check"></i></div><div><div class="wv-verify-title"><?php echo __('Gold Verified Property');?></div><div class="wv-verify-sub"><?php echo __('Verified listing data');?></div></div></div>
                    <div class="wv-verify-item"><div class="wv-verify-icon"><i class="fa fa-calendar"></i></div><div><div class="wv-verify-title"><?php echo __('Published');?>: <?php echo h($propertyCreated);?></div><div class="wv-verify-sub"><?php echo __('Updated');?>: <?php echo h($propertyUpdated);?></div></div></div>
                    <div class="wv-verify-item"><div class="wv-verify-icon"><i class="fa fa-file"></i></div><div><div class="wv-verify-title"><?php echo !empty($post['PropertiesDocument']) ? __('Documents Available') : __('Documents Pending');?></div><div class="wv-verify-sub"><?php echo h($verificationBadges['possession_status']);?></div></div></div>
                </div>

                <section class="wv-card wv-title-card">
                    <div class="wv-location-line"><i class="fa fa-map-marker"></i> <?php echo h($completeAddress);?></div>
                    <h1><?php echo h($propertyName);?></h1>
                    <div class="wv-title-meta">
                        <span><i class="fa fa-expand"></i><?php echo h($propertyArea);?></span>
                        <span><i class="fa fa-map-marker"></i><?php echo h($completeAddress);?></span>
                        <span><i class="fa fa-file-text-o"></i><?php echo h($propertyContract);?></span>
                    </div>
                    <div class="wv-price-grid">
                        <div class="wv-price-cell"><strong><?php echo h($propertyPrice);?></strong><span><?php echo __('Price');?></span></div>
                        <div class="wv-price-cell"><strong><?php echo h($propertyBeds);?></strong><span><i class="fa fa-bed"></i><?php echo __('Beds');?></span></div>
                        <div class="wv-price-cell"><strong><?php echo h($propertyBaths);?></strong><span><i class="fa fa-bath"></i> <?php echo __('Baths');?></span></div>
                        <div class="wv-price-cell"><strong><?php echo h($propertyArea);?></strong><span><i class="fa fa-expand"></i><?php echo __('Area');?></span></div>
                    </div>
                </section>

                <?php if(!empty($priceHistory)){ ?>
                <section class="wv-card wv-section">
                    <h2><?php echo __('Price History');?></h2>
                    <div class="wv-price-history">
                        <?php foreach((array)$priceHistory as $historyRow){
                            $oldPrice = (float)$historyRow['PropertyPriceHistory']['old_price'];
                            $newPrice = (float)$historyRow['PropertyPriceHistory']['new_price'];
                            $delta = $newPrice - $oldPrice;
                        ?>
                        <div class="wv-price-history-row">
                            <div class="wv-price-history-date"><?php echo !empty($historyRow['PropertyPriceHistory']['created']) ? h(date('d M Y', strtotime($historyRow['PropertyPriceHistory']['created']))) : '-';?></div>
                            <div class="wv-price-history-values"><?php echo h($currency.' '.$this->Number->format($oldPrice));?> <span><?php echo __('to');?></span> <?php echo h($currency.' '.$this->Number->format($newPrice));?></div>
                            <div class="wv-price-change-badge"><?php echo ($delta>=0 ? '+' : '').h($currency.' '.$this->Number->format($delta));?></div>
                        </div>
                        <?php } ?>
                    </div>
                </section>
                <?php } ?>

                <section class="wv-card wv-section">
                    <h2><?php echo __('Property Overview');?></h2>
                    <div class="wv-attributes">
                        <div class="wv-attr"><div class="wv-attr-label"><i class="fa fa-hashtag"></i><?php echo __('Office ID');?></div><div class="wv-attr-value"><?php echo !empty($post['Property']['uniq_id']) ? h($post['Property']['uniq_id']) : __('Not Specified');?></div></div>
                        <div class="wv-attr"><div class="wv-attr-label"><i class="fa fa-home"></i><?php echo __('Type');?></div><div class="wv-attr-value"><?php echo h($propertyType);?></div></div>
                        <div class="wv-attr"><div class="wv-attr-label"><i class="fa fa-file-text-o"></i><?php echo __('Contract');?></div><div class="wv-attr-value"><?php echo h($propertyContract);?></div></div>
                        <div class="wv-attr"><div class="wv-attr-label"><i class="fa fa-signal"></i><?php echo __('Status');?></div><div class="wv-attr-value"><?php echo h($propertyStatus);?></div></div>
                        <div class="wv-attr"><div class="wv-attr-label"><i class="fa fa-calendar-check-o"></i><?php echo __('Updated');?></div><div class="wv-attr-value"><?php echo h($propertyUpdated);?></div></div>
                        <div class="wv-attr"><div class="wv-attr-label"><i class="fa fa-building"></i><?php echo __('Agency');?></div><div class="wv-attr-value"><?php echo h($agencyTrust['name']);?></div></div>
                    </div>
                </section>

                <section class="wv-card wv-section">
                    <h2><?php echo __('Description');?></h2>
                    <div class="wv-description"><?php echo $propertyDescriptionHtml;?></div>
                </section>

                <?php if(!empty($feature)){ ?>
                <section class="wv-card wv-section">
                    <h2><?php echo __('Amenities');?></h2>
                    <ul class="wv-amenities">
                        <?php foreach($feature as $fpost){
                            $amenityName = isset($fpost['Feature']['name']) ? (string)$fpost['Feature']['name'] : '';
                            $amenityKey = strtolower($amenityName);
                            $amenityIcon = 'fa-check';
                            if(strpos($amenityKey,'parking')!==false || strpos($amenityKey,'garage')!==false) { $amenityIcon = 'fa-car'; }
                            elseif(strpos($amenityKey,'security')!==false || strpos($amenityKey,'guard')!==false || strpos($amenityKey,'secure')!==false) { $amenityIcon = 'fa-shield'; }
                            elseif(strpos($amenityKey,'water')!==false) { $amenityIcon = 'fa-tint'; }
                            elseif(strpos($amenityKey,'electric')!==false || strpos($amenityKey,'power')!==false) { $amenityIcon = 'fa-bolt'; }
                            elseif(strpos($amenityKey,'gas')!==false) { $amenityIcon = 'fa-fire'; }
                            elseif(strpos($amenityKey,'park')!==false || strpos($amenityKey,'garden')!==false || strpos($amenityKey,'lawn')!==false) { $amenityIcon = 'fa-tree'; }
                            elseif(strpos($amenityKey,'mosque')!==false || strpos($amenityKey,'masjid')!==false) { $amenityIcon = 'fa-university'; }
                            elseif(strpos($amenityKey,'school')!==false || strpos($amenityKey,'college')!==false) { $amenityIcon = 'fa-graduation-cap'; }
                            elseif(strpos($amenityKey,'hospital')!==false || strpos($amenityKey,'clinic')!==false || strpos($amenityKey,'medical')!==false) { $amenityIcon = 'fa-medkit'; }
                            elseif(strpos($amenityKey,'road')!==false || strpos($amenityKey,'street')!==false) { $amenityIcon = 'fa-road'; }
                            elseif(strpos($amenityKey,'market')!==false || strpos($amenityKey,'shop')!==false || strpos($amenityKey,'mall')!==false) { $amenityIcon = 'fa-shopping-cart'; }
                            elseif(strpos($amenityKey,'internet')!==false || strpos($amenityKey,'wifi')!==false) { $amenityIcon = 'fa-wifi'; }
                            elseif(strpos($amenityKey,'gym')!==false || strpos($amenityKey,'fitness')!==false) { $amenityIcon = 'fa-heartbeat'; }
                            elseif(strpos($amenityKey,'pool')!==false || strpos($amenityKey,'swim')!==false) { $amenityIcon = 'fa-life-ring'; }
                            ?><li><i class="fa <?php echo h($amenityIcon);?>"></i> <?php echo h($amenityName);?></li><?php } ?>
                    </ul>
                </section>
                <?php } ?>

                <?php if(count($post['PropertiesFloorplan'])>0){ ?>
                <section class="wv-card wv-section">
                    <h2><?php echo __('Floor Plans');?></h2>
                    <?php foreach($post['PropertiesFloorplan'] as $fpost){ $floorPhoto=$fpost['dir'].'_thumb/'.$fpost['photo']; $floorAlt=!empty($fpost['alt_text']) ? $fpost['alt_text'] : $propertyName; echo $this->Html->link($this->Html->image($floorPhoto,array('alt'=>$floorAlt,'title'=>!empty($fpost['title']) ? $fpost['title'] : $floorAlt)),array('controller'=>'img','action'=>$fpost['dir'],$fpost['photo']),array('class'=>'image-popup','escape'=>false)).'&nbsp;&nbsp;'; } ?>
                </section>
                <?php } ?>

                <section class="wv-card wv-section">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;flex-wrap:wrap;gap:8px;">
                        <h2 style="margin:0;"><?php echo __('Location & Surroundings');?></h2>
                        <a href="https://www.google.com/maps/search/?api=1&query=<?php echo $mapLat.','.$mapLng;?>" target="_blank" class="btn btn-xs btn-default" style="border-radius:20px;font-size:11px;font-weight:700;padding:5px 12px;border:1px solid #cbd5e1;color:#07152a;"><i class="fa fa-external-link"></i> <?php echo __('Open in Maps');?></a>
                    </div>
                    <div class="wv-map" id="property-detail-map"></div>
                </section>

                <section class="wv-card wv-section">
                    <h2 class="wv-nearby-heading"><i class="fa fa-map-signs"></i> <?php echo __('Nearby Places');?></h2>
                    <?php if(!empty($nearbyPlaces)){ ?>
                    <div class="wv-nearby-places">
                        <?php foreach($nearbyPlaces as $nearbyPlace){ $nearbyDistance = $nearbyDistanceLabel($nearbyPlace['distance']); ?>
                        <div class="wv-nearby-place">
                            <div class="wv-nearby-place-icon"><i class="fa <?php echo h($nearbyIconFor($nearbyPlace['category']));?>"></i></div>
                            <div class="wv-nearby-place-body">
                                <div class="wv-nearby-place-name"><?php echo h($nearbyPlace['name']);?></div>
                                <div class="wv-nearby-place-meta">
                                    <span class="wv-nearby-category"><?php echo h($nearbyPlace['category']);?></span>
                                    <?php if($nearbyDistance !== ''){ ?><span class="wv-nearby-distance"><?php echo h($nearbyDistance);?></span><?php } ?>
                                </div>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                    <?php } else { ?>
                    <div class="wv-nearby-empty"><i class="fa fa-info-circle"></i> <?php echo __('No nearby places have been saved for this property yet.');?></div>
                    <?php } ?>
                </section>

            </main>

            <aside class="wv-sidebar">
                <div class="wv-card wv-side-card wv-contact-card">
                    <div class="wv-side-price"><?php echo h($propertyPrice);?></div>
                    <div class="wv-side-muted wv-side-meta">
                        <strong><?php echo h($propertyArea);?></strong>
                        <span>&middot;</span>
                        <strong><?php echo h($propertyLocation);?></strong>
                        <span>|</span>
                        <span class="wv-side-contract"><?php echo h($propertyContract);?></span>
                    </div>
                    <div class="wv-contact-row">
                        <?php if($callPhone !== '' && $btnCallPcEnabled){ ?>
                        <a class="wv-call wv-track-contact" data-track-url="<?php echo h($trackCallUrl);?>" href="tel:<?php echo h($callPhone);?>">
                            <i class="fa fa-phone"></i> <?php echo h($btnCallText);?>
                        </a>
                        <?php } ?>
                        <?php if($whatsappUrl !== '' && $btnWaPcEnabled){ ?>
                        <a class="wv-whatsapp wv-track-contact" data-track-url="<?php echo h($trackWhatsappUrl);?>" href="<?php echo h($whatsappUrl);?>" target="_blank">
                            <i class="fa fa-whatsapp"></i> <?php echo h($btnWaText);?>
                        </a>
                        <?php } ?>
                    </div>
                    <?php if($btnPdfPcEnabled){ ?>
                    <button type="button" class="wv-payment-plan-btn wv-open-plan-modal" data-toggle="modal" data-target="#leadDownloadModal" data-bs-toggle="modal" data-bs-target="#leadDownloadModal" data-property-id="<?php echo (int)$id; ?>" data-property-name="<?php echo h($propertyName); ?>" data-property-title="<?php echo h($propertyName); ?>">
                        <i class="fa fa-file-pdf-o"></i> <?php echo h($btnPdfText); ?>
                    </button>
                    <?php } ?>
                    <?php echo $this->Html->link('<i class="fa fa-envelope-o"></i> '.__('Send E-mail'),'javascript:void(0)',array('onclick'=>"show_modal('$urlModal');",'data-track-url'=>$trackEmailUrl,'escape'=>false,'class'=>'wv-email-btn wv-track-contact'));?>
                </div>

                <?php if($assignedAgentVisible && $cardAgentCardClass !== 'd-none'){ ?>
                <div class="wv-card wv-side-card wv-assigned-agent-card <?php echo $cardAgentCardClass; ?>">
                    <div class="wv-assigned-agent-head">
                        <a class="wv-assigned-agent-photo" href="<?php echo h($assignedAgentUrl);?>" style="background-image:url('<?php echo h($assignedAgentPhoto);?>');" title="<?php echo h(__('View Agent Profile'));?>"></a>
                        <div>
                            <div class="wv-assigned-agent-kicker"><?php echo __('Assigned Agent');?></div>
                            <a class="wv-assigned-agent-name" href="<?php echo h($assignedAgentUrl);?>"><?php echo h($agentName);?></a>
                            <div class="wv-assigned-agent-sub"><i class="fa fa-check-circle"></i> <?php echo h($cardAgentBadgeText);?></div>
                        </div>
                    </div>
                    <div class="wv-assigned-agent-body">
                        <div class="wv-assigned-agent-contact">
                            <?php if($assignedAgentCallPhone !== ''){ ?><a href="tel:<?php echo h($assignedAgentCallPhone);?>"><i class="fa fa-phone"></i><small><?php echo h($assignedAgentPrimaryPhone);?></small></a><?php } ?>
                            <?php if($agentEmail !== ''){ ?><span><i class="fa fa-envelope"></i><small><?php echo h($agentEmail);?></small></span><?php } ?>
                        </div>
                        <div class="wv-assigned-agent-actions">
                            <a class="wv-agent-profile-btn" href="<?php echo h($assignedAgentUrl);?>"><i class="fa fa-user"></i> <?php echo __('View Profile');?></a>
                            <?php if($assignedAgentWhatsappUrl !== '' && ($btnWaPcEnabled || $btnWaMobileEnabled)){ ?><a class="wv-agent-whatsapp-btn" href="<?php echo h($assignedAgentWhatsappUrl);?>" target="_blank"><i class="fa fa-whatsapp"></i> <?php echo h($btnWaText);?></a><?php } ?>
                        </div>
                    </div>
                </div>
                <?php } ?>

                <div class="wv-card wv-side-card">
                    <h3 class="wv-inquiry-title"><?php echo __('Interested in this property?');?></h3>
                    <div class="wv-side-muted"><?php echo __('Send your inquiry directly to the owner or sales team.');?></div>
                    <?php echo $this->Form->create('Property', array('controller'=>'Properties','action'=>'inquiry/'.$id,'class'=>'wv-form'));?>
                    <div class="form-group"><label><?php echo __('Full Name');?></label><input type="text" name="full_name" class="form-control" required="true"></div>
                    <div class="form-group"><label><?php echo __('E-mail');?></label><input type="email" name="email" class="form-control"></div>
                    <div class="form-group"><label><?php echo __('Mobile Number');?></label><input type="text" name="mobile" class="form-control" required="true"></div>
                    <div class="form-group"><label><?php echo __('Subject');?></label><input type="text" name="subject" class="form-control" value="<?php echo h(__('Inquiry for ').$propertyName);?>"></div>
                    <div class="form-group"><label><?php echo __('Message');?></label><textarea class="form-control" name="message" rows="4"><?php echo h(__('I am interested in this property. Please contact me with more details.'));?></textarea></div>
                    <button type="submit" class="wv-submit"><?php echo __('Submit Inquiry');?></button>
                    <?php echo $this->Form->end();?>
                </div>

                <div class="wv-card wv-side-card wv-agent-card">
                    <div class="wv-agency-head">
                        <a class="wv-agency-logo" href="<?php echo h($agencyProfileUrl);?>" title="<?php echo h(__('View Agency'));?>"><img src="<?php echo h($agencyLogo);?>" alt="<?php echo h($agencyTrust['name']);?>"></a>
                        <div>
                            <div class="wv-agency-kicker"><?php echo __('Verified Agency');?></div>
                            <a class="wv-agent-name wv-agent-name-link" href="<?php echo h($agencyProfileUrl);?>"><?php echo h($agencyTrust['name']);?></a>
                            <div class="wv-agency-sub"><?php echo h($agentName);?> &middot; <?php echo __('Assigned Representative');?></div>
                        </div>
                    </div>
                    <div class="wv-agency-body">
                        <div class="wv-agency-contact">
                            <?php if(!empty($agencyTrust['mobile']) && $agencyTrust['mobile']!=__('Not Specified')){ ?><a href="tel:<?php echo h(preg_replace('/[^0-9\+]/','',$agencyTrust['mobile']));?>"><i class="fa fa-mobile"></i><small><?php echo h($agencyTrust['mobile']);?></small></a><?php } ?>
                            <?php if(!empty($agencyTrust['phone']) && $agencyTrust['phone']!=__('Not Specified')){ ?><a href="tel:<?php echo h(preg_replace('/[^0-9\+]/','',$agencyTrust['phone']));?>"><i class="fa fa-phone"></i><small><?php echo h($agencyTrust['phone']);?></small></a><?php } ?>
                            <?php if(!empty($agencyTrust['email']) && $agencyTrust['email']!=__('Not Specified')){ ?><span><i class="fa fa-envelope"></i><small><?php echo h($agencyTrust['email']);?></small></span><?php } ?>
                        </div>
                        <div class="wv-agency-stats">
                            <div class="wv-agency-stat"><strong><?php echo $this->Number->format($agencyTrust['total_sold_deals']);?></strong><span><?php echo __('Sold Deals');?></span></div>
                            <div class="wv-agency-stat"><strong>24h</strong><span><?php echo __('Response');?></span></div>
                        </div>
                    </div>
                </div>

                <div class="wv-card wv-side-card">
                    <div class="wv-action-list">
                        <?php echo $this->Html->link('<i class="fa fa-share-alt"></i> '.__('Share'),'javascript:void(0)',array('data-toggle'=>'modal','data-target'=>'#shareModal','escape'=>false));?>
                        <?php echo $this->Html->link('<i class="fa fa-print"></i> '.__('Print'),array('controller'=>'Properties','action'=>'printproperty',$id),array('escape'=>false,'target'=>'_blank'));?>
                        <?php echo $this->Html->link('<i class="fa fa-calculator"></i> '.__('Calculator'),'javascript:void(0)',array('onclick'=>"show_modal('$urlCalcModal');",'escape'=>false));?>
                        <?php echo $this->Html->link('<i class="fa fa-download"></i> '.__('Documents'),'javascript:void(0)',array('data-toggle'=>'modal','data-target'=>'#dwndDocumentModal','data-track-url'=>$trackDocumentUrl,'class'=>'wv-track-contact','escape'=>false));?>
                    </div>
                </div>
            </aside>
        </div>

        <section class="wv-similar-section">
            <div class="wv-similar-head">
                <h2><?php echo __('Most Viewed Properties');?></h2>
                <?php echo $this->Html->link(__('View all').' <i class="fa fa-angle-right"></i>',array('controller'=>'Properties','action'=>'index'),array('class'=>'wv-similar-view','escape'=>false));?>
            </div>
            <?php if(!empty($similarProperties)){ ?>
            <div class="wv-similar-grid">
                <?php foreach(array_slice($similarProperties,0,3) as $similarPost){
                    $similarPhoto = $photoUrl.'/nia.png';
                    if(!empty($similarPost['PropertiesPhoto']) && is_array($similarPost['PropertiesPhoto']))
                    {
                        foreach($similarPost['PropertiesPhoto'] as $similarPhotoRow)
                        {
                            if(empty($similarPhotoRow['photo']))continue;
                            $similarPhotoDir = !empty($similarPhotoRow['dir']) ? $similarPhotoRow['dir'] : 'propertiesphotos';
                            $similarPhoto = $photoUrl.'/'.$similarPhotoDir.'_thumb/'.$similarPhotoRow['photo'];
                            if(!empty($similarPhotoRow['home']) && $similarPhotoRow['home']==='Yes')break;
                        }
                    }
                    $similarAgent=!empty($similarPost['User']['name']) ? $similarPost['User']['name'] : $siteName;
                    $similarUnit=!empty($similarPost['Unit']['name']) ? $similarPost['Unit']['name'] : '';
                    $similarUrl = $this->Function->propertyViewUrl($similarPost['Property']['name'],$similarPost['Property']['id']);
                ?>
                <a class="wv-similar-card" href="<?php echo h($similarUrl);?>">
                    <div class="wv-similar-img" style="background-image:url('<?php echo h($similarPhoto);?>');">
                        <div class="wv-similar-tags">
                            <span class="wv-tag-sale"><?php echo __('Sale');?></span>
                            <span class="wv-tag-direct"><?php echo __('Direct');?></span>
                        </div>
                    </div>
                    <div class="wv-similar-body">
                        <div class="wv-similar-price"><?php echo $currency.$this->Number->format($similarPost['Property']['price']);?></div>
                        <span class="wv-similar-title"><?php echo h($similarPost['Property']['name']);?></span>
                        <div class="wv-similar-meta">
                            <i class="fa fa-bed"></i> <?php echo h($similarPost['Property']['bedroom']);?> <?php echo __('Beds');?>
                            &nbsp;&nbsp; <i class="fa fa-bath"></i> <?php echo h($similarPost['Property']['bathroom']);?> <?php echo __('Baths');?>
                            &nbsp;&nbsp; <i class="fa fa-expand"></i> <?php echo h(trim($similarPost['Property']['area'].' '.$similarUnit));?>
                        </div>
                        <div class="wv-similar-footer">
                            <div class="wv-owner-wrap">
                                <span class="wv-owner-icon"><i class="fa fa-user-o"></i></span>
                                <span><span class="wv-owner-name"><?php echo h($similarAgent);?></span><br><span class="wv-owner-label"><?php echo __('Owner');?></span></span>
                            </div>
                            <span class="wv-detail-link"><?php echo __('View Details');?> <i class="fa fa-angle-right"></i></span>
                        </div>
                    </div>
                </a>
                <?php } ?>
            </div>
            <?php } else { ?>
            <div class="wv-card wv-section"><?php echo __('No published properties found yet.');?></div>
            <?php } ?>
        </section>
        <?php if(!empty($recentlyViewedProperties)){ ?>
        <section class="wv-similar-section">
            <div class="wv-similar-head">
                <h2><?php echo __('Recently Viewed');?></h2>
                <?php echo $this->Html->link(__('View all').' <i class="fa fa-angle-right"></i>',array('controller'=>'Properties','action'=>'index'),array('class'=>'wv-similar-view','escape'=>false));?>
            </div>
            <div class="wv-similar-grid">
                <?php foreach((array)$recentlyViewedProperties as $recentPost){
                    $recentPhoto = $photoUrl.'/nia.png';
                    if(!empty($recentPost['PropertiesPhoto']) && is_array($recentPost['PropertiesPhoto']))
                    {
                        foreach($recentPost['PropertiesPhoto'] as $recentPhotoRow)
                        {
                            if(empty($recentPhotoRow['photo']))continue;
                            $recentPhotoDir = !empty($recentPhotoRow['dir']) ? $recentPhotoRow['dir'] : 'propertiesphotos';
                            $recentPhoto = $photoUrl.'/'.$recentPhotoDir.'_thumb/'.$recentPhotoRow['photo'];
                            if(!empty($recentPhotoRow['home']) && $recentPhotoRow['home']==='Yes')break;
                        }
                    }
                    $recentAgent=!empty($recentPost['User']['name']) ? $recentPost['User']['name'] : $siteName;
                    $recentUnit=!empty($recentPost['Unit']['name']) ? $recentPost['Unit']['name'] : '';
                    $recentUrl = $this->Function->propertyViewUrl($recentPost['Property']['name'],$recentPost['Property']['id']);
                ?>
                <a class="wv-similar-card" href="<?php echo h($recentUrl);?>">
                    <div class="wv-similar-img" style="background-image:url('<?php echo h($recentPhoto);?>');">
                        <div class="wv-similar-tags">
                            <span class="wv-tag-sale"><?php echo __('Recent');?></span>
                            <span class="wv-tag-direct"><?php echo h(!empty($recentPost['Contract']['name']) ? $recentPost['Contract']['name'] : __('Property'));?></span>
                        </div>
                    </div>
                    <div class="wv-similar-body">
                        <div class="wv-similar-price"><?php echo $currency.$this->Number->format($recentPost['Property']['price']);?></div>
                        <span class="wv-similar-title"><?php echo h($recentPost['Property']['name']);?></span>
                        <div class="wv-similar-meta">
                            <i class="fa fa-bed"></i> <?php echo h($recentPost['Property']['bedroom']);?> <?php echo __('Beds');?>
                            &nbsp;&nbsp; <i class="fa fa-bath"></i> <?php echo h($recentPost['Property']['bathroom']);?> <?php echo __('Baths');?>
                            &nbsp;&nbsp; <i class="fa fa-expand"></i> <?php echo h(trim($recentPost['Property']['area'].' '.$recentUnit));?>
                        </div>
                        <div class="wv-similar-footer">
                            <div class="wv-owner-wrap">
                                <span class="wv-owner-icon"><i class="fa fa-user-o"></i></span>
                                <span><span class="wv-owner-name"><?php echo h($recentAgent);?></span><br><span class="wv-owner-label"><?php echo __('Owner');?></span></span>
                            </div>
                            <span class="wv-detail-link"><?php echo __('View Details');?> <i class="fa fa-angle-right"></i></span>
                        </div>
                    </div>
                </a>
                <?php } ?>
            </div>
        </section>
        <?php } ?>
    </div>
</div>

<?php
$schemaEnabled = !isset($propertySeo['PropertySeo']['schema_enabled']) || !empty($propertySeo['PropertySeo']['schema_enabled']);
if($schemaEnabled)
{
    $schemaData = array(
        '@context' => 'https://schema.org',
        '@type' => 'Product',
        'name' => $propertyName,
        'description' => trim(strip_tags($propertyDescriptionHtml)),
        'url' => $propertyViewUrl,
        'image' => $mainPhoto,
        'category' => 'Real Estate Property',
        'brand' => array('@type' => 'Organization', 'name' => $siteName),
        'offers' => array(
            '@type' => 'Offer',
            'price' => (float)$post['Property']['price'],
            'priceCurrency' => !empty($currencyType) ? $currencyType : 'PKR',
            'availability' => (strtolower((string)$propertyStatus)==='sold') ? 'https://schema.org/SoldOut' : 'https://schema.org/InStock',
            'url' => $propertyViewUrl
        )
    );
    if($mapLat && $mapLng)
    {
        $schemaData['geo'] = array('@type' => 'GeoCoordinates', 'latitude' => $mapLat, 'longitude' => $mapLng);
    }
?>
<script type="application/ld+json"><?php echo json_encode($schemaData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);?></script>
<?php } ?>

<script type="text/javascript">
(function() {
    var photos = <?php echo json_encode($photos);?> || [];
    var totalPhotos = photos.length;
    var currentIndex = 0;
    var galleryTimer = null;
    var zoomScale = 1;
    var zoomPosX = 0;
    var zoomPosY = 0;
    var isDragging = false;
    var startDragX = 0;
    var startDragY = 0;

    function getThumbElements() {
        return document.querySelectorAll('#wvThumbsContainer .wv-thumb');
    }

    function getZoomThumbElements() {
        return document.querySelectorAll('#wvZoomThumbsStrip .wv-zoom-thumb');
    }

    function scrollToActiveThumb(thumbEl, containerEl) {
        if (!thumbEl || !containerEl) return;
        try {
            var targetLeft = thumbEl.offsetLeft - (containerEl.clientWidth / 2) + (thumbEl.clientWidth / 2);
            if (typeof containerEl.scrollTo === 'function') {
                containerEl.scrollTo({ left: Math.max(0, targetLeft), behavior: 'smooth' });
            } else {
                containerEl.scrollLeft = Math.max(0, targetLeft);
            }
        } catch(e) {
            containerEl.scrollLeft = thumbEl.offsetLeft - 30;
        }
    }

    function setActivePhoto(index, userInitiated) {
        if (!totalPhotos) return;
        if (userInitiated && galleryTimer) {
            clearInterval(galleryTimer);
            galleryTimer = null;
        }
        currentIndex = (index + totalPhotos) % totalPhotos;
        var photo = photos[currentIndex];
        if (!photo) return;

        // 1. Update main photo
        var mainImg = document.getElementById('wvMainPhoto');
        if (mainImg) {
            mainImg.style.opacity = '0.75';
            mainImg.src = photo.full;
            mainImg.alt = photo.alt || '';
            mainImg.title = photo.title || photo.alt || '';
            setTimeout(function() { mainImg.style.opacity = '1'; }, 100);
        }

        // 2. Update photo counter
        var counter = document.getElementById('wvCurrentPhotoIndex');
        if (counter) {
            counter.textContent = (currentIndex + 1);
        }

        // 3. Update main thumbnails
        var thumbs = getThumbElements();
        for (var i = 0; i < thumbs.length; i++) {
            if (i === currentIndex) {
                thumbs[i].classList.add('active');
            } else {
                thumbs[i].classList.remove('active');
            }
        }
        var thumbContainer = document.getElementById('wvThumbsContainer');
        if (thumbs[currentIndex] && thumbContainer) {
            scrollToActiveThumb(thumbs[currentIndex], thumbContainer);
        }

        // 4. Update zoom modal if open
        var zoomModal = document.getElementById('wvPhotoZoomModal');
        if (zoomModal && zoomModal.style.display === 'flex') {
            updateZoomModalContent();
        }
    }

    function updateZoomModalContent() {
        var photo = photos[currentIndex];
        if (!photo) return;

        var zoomImg = document.getElementById('wvZoomImg');
        if (zoomImg) {
            zoomImg.style.opacity = '0.7';
            zoomImg.src = photo.full;
            zoomImg.alt = photo.alt || '';
            setTimeout(function() { zoomImg.style.opacity = '1'; }, 100);
        }

        var zoomCounter = document.getElementById('wvZoomCurrentIndex');
        if (zoomCounter) {
            zoomCounter.textContent = (currentIndex + 1);
        }

        var zoomThumbs = getZoomThumbElements();
        for (var j = 0; j < zoomThumbs.length; j++) {
            if (j === currentIndex) {
                zoomThumbs[j].classList.add('active');
            } else {
                zoomThumbs[j].classList.remove('active');
            }
        }
        var zoomStrip = document.getElementById('wvZoomThumbsStrip');
        if (zoomThumbs[currentIndex] && zoomStrip) {
            scrollToActiveThumb(zoomThumbs[currentIndex], zoomStrip);
        }
        resetZoomTransform();
    }

    function applyZoomTransform() {
        var wrapper = document.getElementById('wvZoomImgWrapper');
        var zoomImg = document.getElementById('wvZoomImg');
        if (!wrapper || !zoomImg) return;
        if (zoomScale <= 1) {
            zoomScale = 1;
            zoomPosX = 0;
            zoomPosY = 0;
            wrapper.classList.remove('is-zoomed', 'is-dragging');
        } else {
            wrapper.classList.add('is-zoomed');
        }
        zoomImg.style.transform = 'translate(' + zoomPosX + 'px, ' + zoomPosY + 'px) scale(' + zoomScale + ')';
    }

    function resetZoomTransform() {
        zoomScale = 1;
        zoomPosX = 0;
        zoomPosY = 0;
        applyZoomTransform();
    }

    function openZoomModal(initialIdx) {
        if (typeof initialIdx === 'number') {
            currentIndex = (initialIdx + totalPhotos) % totalPhotos;
        }
        var modal = document.getElementById('wvPhotoZoomModal');
        if (!modal) return;
        modal.style.display = 'flex';
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        updateZoomModalContent();
    }

    function closeZoomModal() {
        var modal = document.getElementById('wvPhotoZoomModal');
        if (!modal) return;
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
        resetZoomTransform();
    }

    function initGalleryEvents() {
        // Thumbnail clicks on main page
        var thumbContainer = document.getElementById('wvThumbsContainer');
        if (thumbContainer) {
            thumbContainer.addEventListener('click', function(e) {
                var thumb = e.target.closest('.wv-thumb');
                if (!thumb) return;
                var thumbs = Array.prototype.slice.call(getThumbElements());
                var idx = thumbs.indexOf(thumb);
                if (idx !== -1) {
                    setActivePhoto(idx, true);
                }
            });
        }

        // Prev / Next on main gallery
        var prevBtn = document.getElementById('wvGalleryPrev');
        if (prevBtn) {
            prevBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                setActivePhoto(currentIndex - 1, true);
            });
        }
        var nextBtn = document.getElementById('wvGalleryNext');
        if (nextBtn) {
            nextBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                setActivePhoto(currentIndex + 1, true);
            });
        }

        // Open zoom on clicking main photo or zoom button
        var mainImg = document.getElementById('wvMainPhoto');
        if (mainImg) {
            mainImg.addEventListener('click', function(e) {
                openZoomModal(currentIndex);
            });
        }
        var openZoomBtn = document.getElementById('wvOpenZoomBtn');
        if (openZoomBtn) {
            openZoomBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                openZoomModal(currentIndex);
            });
        }

        // Zoom modal controls
        var closeBtn = document.getElementById('wvZoomCloseBtn');
        if (closeBtn) {
            closeBtn.addEventListener('click', function(e) {
                e.preventDefault();
                closeZoomModal();
            });
        }

        var zoomPrev = document.getElementById('wvZoomPrev');
        if (zoomPrev) {
            zoomPrev.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                setActivePhoto(currentIndex - 1, true);
            });
        }

        var zoomNext = document.getElementById('wvZoomNext');
        if (zoomNext) {
            zoomNext.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                setActivePhoto(currentIndex + 1, true);
            });
        }

        var zoomInBtn = document.getElementById('wvZoomInBtn');
        if (zoomInBtn) {
            zoomInBtn.addEventListener('click', function(e) {
                e.preventDefault();
                zoomScale = Math.min(3.5, zoomScale + 0.35);
                applyZoomTransform();
            });
        }

        var zoomOutBtn = document.getElementById('wvZoomOutBtn');
        if (zoomOutBtn) {
            zoomOutBtn.addEventListener('click', function(e) {
                e.preventDefault();
                zoomScale = Math.max(1, zoomScale - 0.35);
                applyZoomTransform();
            });
        }

        var zoomResetBtn = document.getElementById('wvZoomResetBtn');
        if (zoomResetBtn) {
            zoomResetBtn.addEventListener('click', function(e) {
                e.preventDefault();
                resetZoomTransform();
            });
        }

        // Double click image in zoom modal to toggle zoom
        var zoomImg = document.getElementById('wvZoomImg');
        if (zoomImg) {
            zoomImg.addEventListener('dblclick', function(e) {
                e.preventDefault();
                if (zoomScale > 1) {
                    resetZoomTransform();
                } else {
                    zoomScale = 2;
                    applyZoomTransform();
                }
            });

            // Wheel zoom in zoom modal
            zoomImg.addEventListener('wheel', function(e) {
                e.preventDefault();
                var delta = e.deltaY < 0 ? 0.2 : -0.2;
                zoomScale = Math.min(3.5, Math.max(1, zoomScale + delta));
                applyZoomTransform();
            }, { passive: false });
        }

        // Drag/Pan zoomed image
        var stage = document.getElementById('wvZoomStage');
        var wrapper = document.getElementById('wvZoomImgWrapper');
        if (stage && wrapper) {
            stage.addEventListener('mousedown', function(e) {
                if (zoomScale <= 1) return;
                isDragging = true;
                startDragX = e.clientX - zoomPosX;
                startDragY = e.clientY - zoomPosY;
                wrapper.classList.add('is-dragging');
            });

            window.addEventListener('mousemove', function(e) {
                if (!isDragging) return;
                zoomPosX = e.clientX - startDragX;
                zoomPosY = e.clientY - startDragY;
                applyZoomTransform();
            });

            window.addEventListener('mouseup', function() {
                if (isDragging) {
                    isDragging = false;
                    if (wrapper) wrapper.classList.remove('is-dragging');
                }
            });
        }

        // Zoom thumbnails strip clicks
        var zoomStrip = document.getElementById('wvZoomThumbsStrip');
        if (zoomStrip) {
            zoomStrip.addEventListener('click', function(e) {
                var thumb = e.target.closest('.wv-zoom-thumb');
                if (!thumb) return;
                var zoomThumbs = Array.prototype.slice.call(getZoomThumbElements());
                var idx = zoomThumbs.indexOf(thumb);
                if (idx !== -1) {
                    setActivePhoto(idx, true);
                }
            });
        }

        // Keyboard navigation
        window.addEventListener('keydown', function(e) {
            var modal = document.getElementById('wvPhotoZoomModal');
            var isZoomOpen = modal && modal.style.display === 'flex';
            if (e.key === 'Escape' && isZoomOpen) {
                closeZoomModal();
            } else if (e.key === 'ArrowLeft') {
                setActivePhoto(currentIndex - 1, true);
            } else if (e.key === 'ArrowRight') {
                setActivePhoto(currentIndex + 1, true);
            }
        });

        // Touch swipe support on main photo & zoom stage
        function attachSwipe(element) {
            if (!element) return;
            var startX = 0, startY = 0;
            element.addEventListener('touchstart', function(e) {
                if (e.touches && e.touches[0]) {
                    startX = e.touches[0].clientX;
                    startY = e.touches[0].clientY;
                }
            }, { passive: true });
            element.addEventListener('touchend', function(e) {
                if (zoomScale > 1) return;
                if (e.changedTouches && e.changedTouches[0]) {
                    var diffX = e.changedTouches[0].clientX - startX;
                    var diffY = e.changedTouches[0].clientY - startY;
                    if (Math.abs(diffX) > 45 && Math.abs(diffX) > Math.abs(diffY)) {
                        if (diffX > 0) {
                            setActivePhoto(currentIndex - 1, true);
                        } else {
                            setActivePhoto(currentIndex + 1, true);
                        }
                    }
                }
            }, { passive: true });
        }
        attachSwipe(document.getElementById('wvGalleryMain'));
        attachSwipe(document.getElementById('wvZoomStage'));

        // Auto play carousel if configured
        var galleryCard = document.querySelector('.wv-gallery-card');
        if (galleryCard && galleryCard.getAttribute('data-gallery-auto') === '1' && totalPhotos > 1) {
            galleryTimer = setInterval(function() {
                var modal = document.getElementById('wvPhotoZoomModal');
                if (!modal || modal.style.display !== 'flex') {
                    setActivePhoto(currentIndex + 1, false);
                }
            }, 3500);

            galleryCard.addEventListener('mouseenter', function() {
                if (galleryTimer) { clearInterval(galleryTimer); galleryTimer = null; }
            });
            galleryCard.addEventListener('touchstart', function() {
                if (galleryTimer) { clearInterval(galleryTimer); galleryTimer = null; }
            }, { passive: true });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initGalleryEvents);
    } else {
        initGalleryEvents();
    }
})();

(function initJQueryUtilities() {
    function setup($) {
        $('#wvCopyPropertyLink').on('click', function(){
            var input = document.getElementById('wvPropertyShareLink');
            if(!input){ return; }
            input.focus();
            input.select();
            try {
                document.execCommand('copy');
                $(this).html('<i class="fa fa-check"></i> <?php echo __('Copied');?>');
            } catch(e) {}
        });
        $(document).on('click', '.wv-track-contact', function(){
            var url = $(this).attr('data-track-url');
            if(!url){ return; }
            try {
                if(navigator.sendBeacon){
                    navigator.sendBeacon(url, new Blob(['1'], {type: 'text/plain'}));
                } else {
                    $.post(url);
                }
            } catch(e) {}
        });
        $('#shareModal').one('shown.bs.modal', function(){
            if(document.getElementById('wvAddThisScript')){ return; }
            var script = document.createElement('script');
            script.id = 'wvAddThisScript';
            script.async = true;
            script.src = '//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-574048d3b7d7d507';
            document.body.appendChild(script);
        });
    }

    if (typeof jQuery !== 'undefined') {
        setup(jQuery);
    } else {
        window.addEventListener('load', function() {
            if (typeof jQuery !== 'undefined') {
                setup(jQuery);
            }
        });
    }
})();
</script>

<?php if($mapProvider==='google'){ ?>
<script type="text/javascript">
(function(){
    var pos = {lat: <?php echo $mapLat;?>, lng: <?php echo $mapLng;?>};
    var mapMarkers = <?php echo json_encode($mapPropertyMarkers);?>;
    function esc(value){
        return String(value || '').replace(/[&<>"']/g, function(c){
            return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c];
        });
    }
    function popupCard(item){
        return '<div class="wv-map-popup-card">'
            + '<div class="wv-map-popup-img" style="background-image:url(' + esc(item.photo) + ')"></div>'
            + '<div class="wv-map-popup-body">'
            + '<div class="wv-map-popup-title">' + esc(item.title) + '</div>'
            + '<div class="wv-map-popup-price">' + esc(item.price) + '</div>'
            + '<div class="wv-map-popup-meta"><i class="fa fa-map-marker"></i> ' + esc(item.location) + '</div>'
            + '<div class="wv-map-popup-specs">'
            + '<span><i class="fa fa-bed"></i>' + esc(item.beds) + ' <?php echo __('Beds');?>' + '</span>'
            + '<span><i class="fa fa-bath"></i>' + esc(item.baths) + ' <?php echo __('Baths');?>' + '</span>'
            + '<span><i class="fa fa-expand"></i>' + esc(item.area) + '</span>'
            + '</div>'
            + '<a class="wv-map-popup-link" href="' + esc(item.url) + '"><?php echo __('View Details');?></a>'
            + '</div></div>';
    }
    var map = new google.maps.Map(document.getElementById('property-detail-map'), {zoom: 15, center: pos});
    var bounds = new google.maps.LatLngBounds();
    var infoWindow = new google.maps.InfoWindow();
    var googleAnimatedMarkers = [];
    function googlePinIcon(active, pulse){
        var color = active ? '#16a34a' : '#ff0a46';
        var shadow = active ? 'rgba(22,163,74,.36)' : 'rgba(7,21,42,.26)';
        var width = active ? (pulse ? 66 : 58) : (pulse ? 50 : 44);
        var height = Math.round(width * 1.28);
        var svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 82">'
            + '<filter id="s" x="-30%" y="-20%" width="160%" height="160%"><feDropShadow dx="0" dy="8" stdDeviation="5" flood-color="' + shadow + '"/></filter>'
            + '<path filter="url(#s)" d="M32 3C17.1 3 5 15.1 5 30c0 20.8 27 49 27 49s27-28.2 27-49C59 15.1 46.9 3 32 3z" fill="' + color + '" stroke="#fff" stroke-width="4"/>'
            + '<circle cx="32" cy="30" r="10" fill="#fff" opacity=".94"/>'
            + '<circle cx="32" cy="30" r="5" fill="' + color + '"/>'
            + '</svg>';
        return {
            url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(svg),
            scaledSize: new google.maps.Size(width, height),
            anchor: new google.maps.Point(width / 2, height - 3)
        };
    }
    if(!mapMarkers.length){
        mapMarkers = [{
            lat: pos.lat,
            lng: pos.lng,
            title: <?php echo json_encode($propertyName);?>,
            price: <?php echo json_encode($propertyPrice);?>,
            location: <?php echo json_encode($propertyLocation);?>,
            area: <?php echo json_encode($propertyArea);?>,
            beds: <?php echo json_encode((string)$propertyBeds);?>,
            baths: <?php echo json_encode((string)$propertyBaths);?>,
            photo: <?php echo json_encode($mainPhoto);?>,
            url: <?php echo json_encode($propertyViewUrl);?>,
            active: true
        }];
    }
    mapMarkers.forEach(function(item){
        var markerPos = {lat: parseFloat(item.lat), lng: parseFloat(item.lng)};
        if(isNaN(markerPos.lat) || isNaN(markerPos.lng)){ return; }
        var marker = new google.maps.Marker({
            position: markerPos,
            map: map,
            title: item.title || '',
            label: null,
            icon: googlePinIcon(!!item.active, false),
            animation: google.maps.Animation.BOUNCE,
            optimized: false,
            zIndex: item.active ? 9999 : 100
        });
        marker._wvActivePin = !!item.active;
        marker.addListener('click', function(){
            infoWindow.setContent(popupCard(item));
            infoWindow.open(map, marker);
        });
        googleAnimatedMarkers.push(marker);
        bounds.extend(markerPos);
    });
    if(googleAnimatedMarkers.length){
        var googlePulseOn = false;
        setInterval(function(){
            googlePulseOn = !googlePulseOn;
            googleAnimatedMarkers.forEach(function(marker){
                marker.setIcon(googlePinIcon(marker._wvActivePin, googlePulseOn));
            });
        }, 650);
    }
    if(mapMarkers.length>1){ map.fitBounds(bounds); }
})();
</script>
<?php } else { ?>
<script type="text/javascript">
(function(){
    function initLeafletMap(){
        if(typeof L === 'undefined'){
            if(!window._leafletLoading){
                window._leafletLoading = true;
                var lcss = document.createElement('link');
                lcss.rel = 'stylesheet';
                lcss.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
                document.head.appendChild(lcss);

                var s = document.createElement('script');
                s.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
                s.onload = function(){
                    initLeafletMap();
                };
                document.head.appendChild(s);
            } else {
                setTimeout(initLeafletMap, 100);
            }
            return;
        }
        var mapEl = document.getElementById('property-detail-map');
        if(!mapEl){ return; }
        var lat = <?php echo $mapLat;?>, lng = <?php echo $mapLng;?>;
        var mapMarkers = <?php echo json_encode($mapPropertyMarkers);?>;
        function esc(value){
            return String(value || '').replace(/[&<>"']/g, function(c){
                return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c];
            });
        }
        function popupCard(item){
            return '<div class="wv-map-popup-card">'
                + '<div class="wv-map-popup-img" style="background-image:url(' + esc(item.photo) + ')"></div>'
                + '<div class="wv-map-popup-body">'
                + '<div class="wv-map-popup-title">' + esc(item.title) + '</div>'
                + '<div class="wv-map-popup-price">' + esc(item.price) + '</div>'
                + '<div class="wv-map-popup-meta"><i class="fa fa-map-marker"></i> ' + esc(item.location) + '</div>'
                + '<div class="wv-map-popup-specs">'
                + '<span><i class="fa fa-bed"></i>' + esc(item.beds) + ' <?php echo __('Beds');?>' + '</span>'
                + '<span><i class="fa fa-bath"></i>' + esc(item.baths) + ' <?php echo __('Baths');?>' + '</span>'
                + '<span><i class="fa fa-expand"></i>' + esc(item.area) + '</span>'
                + '</div>'
                + '<a class="wv-map-popup-link" href="' + esc(item.url) + '"><?php echo __('View Details');?></a>'
                + '</div></div>';
        }
        var map = L.map('property-detail-map').setView([lat, lng], 15);
        var fallbackLoaded = false;
        var tileLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {maxZoom:19, attribution:'&copy; OpenStreetMap contributors'}).addTo(map);
        tileLayer.on('tileerror', function(){
            if(fallbackLoaded){ return; }
            fallbackLoaded = true;
            map.removeLayer(tileLayer);
            L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {maxZoom:19, attribution:'&copy; OpenStreetMap contributors &copy; CARTO'}).addTo(map);
        });
        if(!mapMarkers.length){
            mapMarkers = [{
                lat: lat,
                lng: lng,
                title: <?php echo json_encode($propertyName);?>,
                price: <?php echo json_encode($propertyPrice);?>,
                location: <?php echo json_encode($propertyLocation);?>,
                area: <?php echo json_encode($propertyArea);?>,
                beds: <?php echo json_encode((string)$propertyBeds);?>,
                baths: <?php echo json_encode((string)$propertyBaths);?>,
                photo: <?php echo json_encode($mainPhoto);?>,
                url: <?php echo json_encode($propertyViewUrl);?>,
                active: true
            }];
        }
        var markerLayer = L.featureGroup().addTo(map);
        mapMarkers.forEach(function(item){
            var markerLat = parseFloat(item.lat), markerLng = parseFloat(item.lng);
            if(isNaN(markerLat) || isNaN(markerLng)){ return; }
            var pinSize = item.active ? [66, 74] : [52, 58];
            var pinAnchor = item.active ? [33, 70] : [26, 54];
            var icon = L.divIcon({
                className: 'wv-map-location-pin-wrap',
                html: '<div class="wv-map-location-pin' + (item.active ? ' active' : '') + '"><i class="fa fa-map-marker"></i></div>',
                iconSize: pinSize,
                iconAnchor: pinAnchor,
                popupAnchor: [0, -pinAnchor[1]]
            });
            L.marker([markerLat,markerLng], {icon:icon, zIndexOffset:(item.active ? 1000 : 0)}).bindPopup(popupCard(item), {closeButton:true}).addTo(markerLayer);
        });
        if(markerLayer.getLayers().length>1){
            map.fitBounds(markerLayer.getBounds().pad(0.16));
        }
        setTimeout(function(){ map.invalidateSize(); }, 150);
        setTimeout(function(){ map.invalidateSize(); }, 500);
        setTimeout(function(){ map.invalidateSize(); }, 1200);
    }
    if(document.readyState === 'complete' || document.readyState === 'interactive'){
        setTimeout(initLeafletMap, 50);
    } else {
        document.addEventListener('DOMContentLoaded', initLeafletMap);
    }
})();
</script>
<?php } ?>

<div class="modal fade" id="shareModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal"><span>&times;</span>&nbsp;</button><h6 class="modal-title"><?php echo __('Share Property');?></h6></div><div class="modal-body">
    <div class="wv-share-grid">
        <?php foreach($socialShareLinks as $shareLink){ ?>
        <a class="wv-share-btn <?php echo h($shareLink['class']);?>" href="<?php echo h($shareLink['url']);?>" target="_blank"><i class="fa <?php echo h($shareLink['icon']);?>"></i> <?php echo h($shareLink['label']);?></a>
        <?php } ?>
    </div>
    <div class="wv-copy-link-row">
        <input id="wvPropertyShareLink" type="text" readonly value="<?php echo h($propertyViewUrl);?>">
        <button type="button" id="wvCopyPropertyLink"><i class="fa fa-copy"></i> <?php echo __('Copy');?></button>
    </div>
    <div class="addthis_sharing_toolbox" style="margin-top:12px;"></div>
  </div></div></div>
</div>
<div class="modal fade" id="dwndDocumentModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal"><span>&times;</span>&nbsp;</button><h6 class="modal-title"><?php echo __('Download Documents');?></h6></div><div class="modal-body"><div class="mrg">
    <?php if(!empty($post['PropertiesDocument'])){ foreach($post['PropertiesDocument'] as $k=>$value){ $docNo=$k+1; $docTitle=!empty($value['title']) ? $value['title'] : __('Document').' '.$docNo; echo $this->Html->link('<span class="fa fa-download"></span>&nbsp;'.h($docTitle),array('controller'=>'img','action'=>$value['dir'],$value['photo']),array('target'=>'_blank','class'=>'btn btn-primary wv-track-contact','data-track-url'=>$trackDocumentUrl,'escape'=>false,'style'=>'margin-left:10px;margin-bottom:10px;')); }} else { echo __('No documents available.'); } ?>
  </div></div></div></div>
</div>

<!-- Lead Capture Payment Plan Download Modal -->
<div class="modal fade wv-lead-modal" id="leadDownloadModal" tabindex="-1" role="dialog" aria-labelledby="leadDownloadModalLabel" aria-hidden="true" style="z-index: 10500;">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 480px; margin: 30px auto;">
        <div class="modal-content" style="border: 0; border-radius: 16px; box-shadow: 0 20px 60px rgba(7,21,42,0.35); overflow: hidden;">
            <div class="modal-header" style="background: linear-gradient(135deg, #07152a 0%, #162c4b 100%); border-bottom: 3px solid #ff0a46; color: #fff; padding: 18px 22px; position: relative; border-top-left-radius: 16px; border-top-right-radius: 16px;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="background: #ff0a46; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 4px 12px rgba(255,10,70,0.4);">
                        <i class="fa fa-file-pdf-o" style="font-size: 19px; color: #fff;"></i>
                    </div>
                    <div>
                        <h4 class="modal-title" id="leadDownloadModalLabel" style="font-size: 18px; font-weight: 900; margin: 0; color: #fff; line-height: 1.2;">Download Payment Plan</h4>
                        <p id="modalPropertySubTitle" style="font-size: 12px; color: #cfe2ff; margin: 3px 0 0; font-weight: 600;">Official Payment Schedule for: <?php echo h($propertyName); ?></p>
                    </div>
                </div>
                <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close" style="position: absolute; right: 18px; top: 16px; color: #fff; background: none; border: none; font-size: 26px; line-height: 1; opacity: 0.85; text-shadow: none; cursor: pointer;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="background: #f8fafc; padding: 22px;">
                <div id="leadModalAlert" class="alert" style="display: none; font-size: 13px; border-radius: 8px; margin-bottom: 15px;"></div>
                
                <div id="autofillNoticeBadge" style="display: none; align-items: center; gap: 6px; background: #fff; border: 1px solid #e2e8f0; border-radius: 6px; padding: 5px 10px; margin-bottom: 14px; font-size: 11px; font-weight: 700; color: #475569;">
                    <i class="fa fa-magic" style="color: #f59e0b;"></i> <span>Auto-filled from saved details</span>
                </div>

                <form id="leadBrochureForm" method="post" action="<?php echo $this->Html->url(array('controller' => 'fronts', 'action' => 'download_brochure')); ?>">
                    <input type="hidden" name="property_id" id="modalPropertyId" value="<?php echo (int)$id; ?>">
                    <input type="hidden" name="utm_source" id="leadUtmSource" value="">
                    <input type="hidden" name="utm_campaign" id="leadUtmCampaign" value="">
                    <input type="hidden" name="fbclid" id="leadFbclid" value="">

                    <div style="margin-bottom: 16px;">
                        <label for="leadName" style="display: block; font-size: 12.5px; font-weight: 800; color: #07152a; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.03em;">Full Name <span style="color: #ff0a46;">*</span></label>
                        <input type="text" class="form-control" id="leadName" name="name" required placeholder="e.g. Muhammad Ali" autocomplete="name given-name family-name" autocapitalize="words" style="width: 100%; height: 46px; border-radius: 8px; border: 1.5px solid #cbd5e1; padding: 10px 14px; font-size: 15px; font-weight: 600; color: #0f172a; background: #fff;">
                    </div>

                    <div style="margin-bottom: 18px;">
                        <label for="leadMobile" style="display: block; font-size: 12.5px; font-weight: 800; color: #07152a; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.03em;">WhatsApp / Mobile Number <span style="color: #ff0a46;">*</span></label>
                        <div style="display: flex; align-items: center; border: 1.5px solid #cbd5e1; border-radius: 8px; overflow: hidden; background: #fff;">
                            <span style="background: #fff; border: none; color: #25D366; font-size: 18px; padding: 0 12px; display: flex; align-items: center;"><i class="fa fa-whatsapp"></i></span>
                            <input type="tel" class="form-control" id="leadMobile" name="mobile" required placeholder="e.g. 0300 1234567" autocomplete="tel tel-national" inputmode="tel" style="border: none; height: 46px; font-size: 15px; font-weight: 600; box-shadow: none; padding-left: 0; color: #0f172a;">
                        </div>
                        <small style="display: block; font-size: 11px; color: #64748b; margin-top: 5px;"><i class="fa fa-lock text-success"></i> Instant PDF schedule download + WhatsApp delivery.</small>
                    </div>

                    <div style="margin-top: 20px;">
                        <button type="submit" id="btnDownloadSubmit" class="btn" style="width: 100%; height: 48px; background: linear-gradient(135deg, #ff0a46 0%, #c70032 100%); border: none; border-radius: 8px; color: #fff; font-weight: 900; font-size: 14px; letter-spacing: 0.05em; text-transform: uppercase; box-shadow: 0 8px 20px rgba(255,10,70,0.35); cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;">
                            <span class="btn-text"><i class="fa fa-download"></i> Download Payment Plan (PDF)</span>
                            <span class="btn-loading" style="display: none;"><i class="fa fa-spinner fa-spin"></i> Preparing PDF...</span>
                        </button>
                    </div>
                </form>
            </div>
            <div class="modal-footer" style="background: #fff; border-top: 1px solid #edf2f7; padding: 10px 18px; justify-content: center; text-align: center;">
                <small style="font-size: 11px; color: #64748b; font-weight: 700;"><i class="fa fa-shield" style="color: #10b981;"></i> 100% Privacy Guaranteed &middot; Instant Official Schedule</small>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
(function() {
    var WVLeadMemory = {
        save: function(name, phone) {
            try {
                if (name && name.trim()) localStorage.setItem('wv_user_name', name.trim());
                if (phone && phone.trim()) localStorage.setItem('wv_user_phone', phone.trim());
            } catch(e) {}
        },
        get: function() {
            try {
                return {
                    name: localStorage.getItem('wv_user_name') || '',
                    phone: localStorage.getItem('wv_user_phone') || ''
                };
            } catch(e) {
                return { name: '', phone: '' };
            }
        },
        fromURL: function() {
            var params = new URLSearchParams(window.location.search);
            var name = params.get('name') || params.get('fullname') || params.get('full_name') || params.get('first_name') || '';
            var lastName = params.get('last_name') || '';
            if (lastName && name) name = name + ' ' + lastName;
            var phone = params.get('phone') || params.get('mobile') || params.get('tel') || params.get('whatsapp') || params.get('phone_number') || '';
            var utmSource = params.get('utm_source') || params.get('source') || '';
            var utmCampaign = params.get('utm_campaign') || '';
            var fbclid = params.get('fbclid') || params.get('fb_lead_id') || params.get('lead_id') || '';

            if (name || phone) {
                this.save(name, phone);
            }
            return { name: name, phone: phone, utm_source: utmSource, utm_campaign: utmCampaign, fbclid: fbclid };
        }
    };

    function initLeadModal() {
        var nameInput = document.getElementById('leadName');
        var mobileInput = document.getElementById('leadMobile');
        var utmSourceInput = document.getElementById('leadUtmSource');
        var utmCampaignInput = document.getElementById('leadUtmCampaign');
        var fbclidInput = document.getElementById('leadFbclid');
        var autofillBadge = document.getElementById('autofillNoticeBadge');

        var urlData = WVLeadMemory.fromURL();
        if (utmSourceInput && urlData.utm_source) utmSourceInput.value = urlData.utm_source;
        if (utmCampaignInput && urlData.utm_campaign) utmCampaignInput.value = urlData.utm_campaign;
        if (fbclidInput && urlData.fbclid) fbclidInput.value = urlData.fbclid;

        function applyAutoFill() {
            var saved = WVLeadMemory.get();
            if (nameInput && !nameInput.value && (urlData.name || saved.name)) {
                nameInput.value = urlData.name || saved.name;
            }
            if (mobileInput && !mobileInput.value && (urlData.phone || saved.phone)) {
                mobileInput.value = urlData.phone || saved.phone;
            }
            if (autofillBadge) {
                autofillBadge.style.display = (nameInput && nameInput.value && mobileInput && mobileInput.value) ? 'inline-flex' : 'none';
            }
        }
        applyAutoFill();

        var openPlanBtns = document.querySelectorAll('.wv-open-plan-modal');
        var modalPropId = document.getElementById('modalPropertyId');
        var modalPropSubtitle = document.getElementById('modalPropertySubTitle');

        openPlanBtns.forEach(function(btn) {
            btn.addEventListener('click', function() {
                var propId = this.getAttribute('data-property-id') || '<?php echo (int)$id; ?>';
                var propName = this.getAttribute('data-property-name') || <?php echo json_encode($propertyName); ?>;
                if (modalPropId) modalPropId.value = propId;
                if (modalPropSubtitle) {
                    modalPropSubtitle.textContent = 'Official Payment Schedule for: ' + propName;
                }
                applyAutoFill();
                
                // Trigger modal show for jQuery Bootstrap or Native Bootstrap
                if (typeof $ !== 'undefined' && $('#leadDownloadModal').modal) {
                    $('#leadDownloadModal').modal('show');
                } else if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    var m = bootstrap.Modal.getInstance(document.getElementById('leadDownloadModal')) || new bootstrap.Modal(document.getElementById('leadDownloadModal'));
                    m.show();
                }
            });
        });

        // Sync inputs from sidebar inquiry form to memory
        var sideInqForm = document.querySelector('.wv-form');
        if (sideInqForm) {
            var sName = sideInqForm.querySelector('input[name="full_name"]');
            var sPhone = sideInqForm.querySelector('input[name="mobile"]');
            if (sName) {
                var saved = WVLeadMemory.get();
                if (!sName.value && (urlData.name || saved.name)) sName.value = urlData.name || saved.name;
                sName.addEventListener('change', function() { WVLeadMemory.save(this.value, sPhone ? sPhone.value : ''); });
            }
            if (sPhone) {
                var saved = WVLeadMemory.get();
                if (!sPhone.value && (urlData.phone || saved.phone)) sPhone.value = urlData.phone || saved.phone;
                sPhone.addEventListener('change', function() { WVLeadMemory.save(sName ? sName.value : '', this.value); });
            }
        }

        // Handle AJAX submission & instant download
        var leadForm = document.getElementById('leadBrochureForm');
        var leadAlert = document.getElementById('leadModalAlert');
        var btnSubmit = document.getElementById('btnDownloadSubmit');

        if (leadForm) {
            leadForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                var nameVal = (nameInput ? nameInput.value : '').trim();
                var mobileVal = (mobileInput ? mobileInput.value : '').trim();
                
                if (!nameVal || !mobileVal) {
                    if (leadAlert) {
                        leadAlert.className = 'alert alert-danger';
                        leadAlert.innerHTML = '<i class="fa fa-exclamation-triangle"></i> Please provide your Name and WhatsApp/Mobile number.';
                        leadAlert.style.display = 'block';
                    }
                    return;
                }

                WVLeadMemory.save(nameVal, mobileVal);

                if (btnSubmit) {
                    btnSubmit.disabled = true;
                    var textSpan = btnSubmit.querySelector('.btn-text');
                    var loadSpan = btnSubmit.querySelector('.btn-loading');
                    if (textSpan) textSpan.style.display = 'none';
                    if (loadSpan) loadSpan.style.display = 'inline-flex';
                }
                if (leadAlert) leadAlert.style.display = 'none';

                var formData = new FormData(leadForm);

                fetch(leadForm.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(function(res) { return res.json(); })
                .then(function(data) {
                    if (btnSubmit) {
                        btnSubmit.disabled = false;
                        var textSpan = btnSubmit.querySelector('.btn-text');
                        var loadSpan = btnSubmit.querySelector('.btn-loading');
                        if (textSpan) textSpan.style.display = 'inline-flex';
                        if (loadSpan) loadSpan.style.display = 'none';
                    }

                    if (data && data.status === 'success') {
                        if (data.has_file && data.download_url) {
                            if (leadAlert) {
                                leadAlert.className = 'alert alert-success';
                                leadAlert.innerHTML = '<i class="fa fa-check-circle"></i> ' + (data.message || 'Payment plan downloading...');
                                leadAlert.style.display = 'block';
                            }

                            var downloadLink = document.createElement('a');
                            downloadLink.href = data.download_url;
                            downloadLink.target = '_blank';
                            var cleanName = data.download_filename || ((data.property_name ? data.property_name.replace(/[^a-z0-9]/gi, '_') : 'Payment_Plan') + '.pdf');
                            downloadLink.download = cleanName;
                            document.body.appendChild(downloadLink);
                            downloadLink.click();
                            document.body.removeChild(downloadLink);

                            setTimeout(function() {
                                if (typeof $ !== 'undefined' && $('#leadDownloadModal').modal) {
                                    $('#leadDownloadModal').modal('hide');
                                } else if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                                    var modalEl = document.getElementById('leadDownloadModal');
                                    var modalObj = bootstrap.Modal.getInstance(modalEl);
                                    if (modalObj) modalObj.hide();
                                }
                            }, 2500);
                        } else {
                            if (leadAlert) {
                                leadAlert.className = 'alert alert-info';
                                leadAlert.innerHTML = '<i class="fa fa-info-circle"></i> <strong>Latest Payment Plan Not Available</strong><div style="font-size:12px;margin-top:4px;">' + (data.message || 'Our sales team has received your request and will contact you shortly.') + '</div>';
                                leadAlert.style.display = 'block';
                            }

                            setTimeout(function() {
                                if (typeof $ !== 'undefined' && $('#leadDownloadModal').modal) {
                                    $('#leadDownloadModal').modal('hide');
                                } else if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                                    var modalEl = document.getElementById('leadDownloadModal');
                                    var modalObj = bootstrap.Modal.getInstance(modalEl);
                                    if (modalObj) modalObj.hide();
                                }
                            }, 4500);
                        }
                    } else {
                        if (leadAlert) {
                            leadAlert.className = 'alert alert-danger';
                            leadAlert.innerHTML = '<i class="fa fa-times-circle"></i> ' + (data.message || 'Download failed. Please try again.');
                            leadAlert.style.display = 'block';
                        }
                    }
                })
                .catch(function() {
                    if (btnSubmit) {
                        btnSubmit.disabled = false;
                        var textSpan = btnSubmit.querySelector('.btn-text');
                        var loadSpan = btnSubmit.querySelector('.btn-loading');
                        if (textSpan) textSpan.style.display = 'inline-flex';
                        if (loadSpan) loadSpan.style.display = 'none';
                    }
                    if (leadAlert) {
                        leadAlert.className = 'alert alert-success';
                        leadAlert.innerHTML = '<i class="fa fa-check-circle"></i> Thank you! Your download will begin shortly.';
                        leadAlert.style.display = 'block';
                    }
                });
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initLeadModal);
    } else {
        initLeadModal();
    }
})();
</script>
