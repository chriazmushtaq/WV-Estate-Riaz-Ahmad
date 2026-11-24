<?php
$ts = !empty($themeSettings) ? $themeSettings : array();

App::uses('RealEstateThemeSetting', 'RealEstateTheme.Model');
$themeModel = new RealEstateThemeSetting();

if (!function_exists('wv_render_rich_html')) {
    function wv_render_rich_html($text, $allowBreaks = true) {
        if ($text === null || $text === '') {
            return '';
        }
        $allowed = '<a><b><strong><i><em><u><br><p><span><small><font><strike><s><mark><sup><sub><ul><ol><li><div><h1><h2><h3><h4><h5><h6>';
        $clean = strip_tags((string)$text, $allowed);
        if ($allowBreaks && strpos($clean, '<br') === false && strpos($clean, '<p') === false && strpos($clean, '<div') === false) {
            $clean = nl2br($clean);
        }
        return $clean;
    }
}
if (!function_exists('wv_render_rich_title')) {
    function wv_render_rich_title($title) {
        if ($title === null || $title === '') {
            return '';
        }
        $allowed = '<b><strong><i><em><u><br><span><small><font><mark><sup><sub>';
        $clean = strip_tags((string)$title, $allowed);
        if (strpos($clean, '<br') === false) {
            $clean = nl2br($clean);
        }
        return $clean;
    }
}

// Hero Video & Metadata
$brandName = !empty($ts['brand_name']) ? $ts['brand_name'] : 'White Villas & Lodges';
$heroVideoUrl = !empty($ts['hero_video_url']) ? $ts['hero_video_url'] : 'https://www.ramadacostadelsol.com/wp-content/uploads/2025/03/Ramada-Costa-del-Sol-1080px.mp4';

// Intro Section (Matching exact Screenshot 1)
$introHeading = !empty($ts['intro_heading']) ? $ts['intro_heading'] : 'UNLOCK YOUR NEXT HOME';
$introSubheading = !empty($ts['intro_subheading']) ? $ts['intro_subheading'] : 'Unrivalled opportunities, unlimited potential';

$f1Badge = !empty($ts['intro_f1_badge']) ? $ts['intro_f1_badge'] : 'COSTA DEL SOL';
$f1Title = !empty($ts['intro_f1_title']) ? $ts['intro_f1_title'] : "EXCEPTIONAL APARTMENTS\nFOR SALE";
$f1Desc = !empty($ts['intro_f1_desc']) ? $ts['intro_f1_desc'] : 'Ramada Hotels & Suites on Spain’s legendary Costa del Sol can offer an array of exceptional apartments. Enjoy sun-kissed holidays in your own apartment and achieve generous rental returns.';
$f1Btn = !empty($ts['intro_f1_btn_text']) ? $ts['intro_f1_btn_text'] : 'CONTACT US';
$f1Img = !empty($ts['intro_f1_image']) ? $ts['intro_f1_image'] : 'https://www.ramadacostadelsol.com/wp-content/uploads/2024/10/MDS-306-living-room-800v.webp';

$f2Badge = !empty($ts['intro_f2_badge']) ? $ts['intro_f2_badge'] : 'RESORT-BASED OWNERSHIP';
$f2Title = !empty($ts['intro_f2_title']) ? $ts['intro_f2_title'] : 'STRONG RENTAL RETURNS';
$f2Desc = !empty($ts['intro_f2_desc']) ? $ts['intro_f2_desc'] : "Whether you’re looking for a new home, holiday property or an investment opportunity with strong potential rental income, Ramada Hotel & Suites is all-encompassing. Enjoy the extensive facilities offered by resort-based ownership, including 24-hour reception and security, concierge service, multiple restaurants, bars, and a gym – plus a golden beach within walking distance.\n\nEmbrace the essence of a family-friendly haven that serves as the perfect gateway to explore the vibrant Costa del Sol. Perfectly located just 27km from Malaga airport, residents can enjoy an enviable blend of comfort and convenience. Elevate your lifestyle with the freedom to holiday at your leisure and a lucrative rental opportunity.";
$f2Btn = !empty($ts['intro_f2_btn_text']) ? $ts['intro_f2_btn_text'] : 'CONTACT US';
$f2Img = !empty($ts['intro_f2_image']) ? $ts['intro_f2_image'] : 'https://www.ramadacostadelsol.com/wp-content/uploads/2024/10/MDS-306-dining-area-720v.webp';

// Resorts / Properties Showcase (Matching exact Screenshot 2)
$resortsSubtitle = !empty($ts['resorts_subtitle']) ? $ts['resorts_subtitle'] : 'EXCLUSIVE DEVELOPMENTS';
$resortsTitle = !empty($ts['resorts_title']) ? $ts['resorts_title'] : 'PROPERTIES FOR SALE';

// Property Card Buttons & PC/Mobile Visibility Settings
$btnPdfText = !empty($ts['btn_pdf_text']) ? $ts['btn_pdf_text'] : __('Download Payment Plan (PDF)');
$btnPdfPcEnabled = !isset($ts['btn_pdf_pc_enabled']) || !empty($ts['btn_pdf_pc_enabled']);
$btnPdfMobileEnabled = !isset($ts['btn_pdf_mobile_enabled']) || !empty($ts['btn_pdf_mobile_enabled']);

$btnWaText = !empty($ts['btn_whatsapp_text']) ? $ts['btn_whatsapp_text'] : __('WhatsApp');
$btnWaPcEnabled = !isset($ts['btn_whatsapp_pc_enabled']) || !empty($ts['btn_whatsapp_pc_enabled']);
$btnWaMobileEnabled = !isset($ts['btn_whatsapp_mobile_enabled']) || !empty($ts['btn_whatsapp_mobile_enabled']);

$btnCallText = !empty($ts['btn_call_text']) ? $ts['btn_call_text'] : __('Call Owner');
$btnCallPcEnabled = !isset($ts['btn_call_pc_enabled']) || !empty($ts['btn_call_pc_enabled']);
$btnCallMobileEnabled = !isset($ts['btn_call_mobile_enabled']) || !empty($ts['btn_call_mobile_enabled']);

// Responsive classes
if ($btnPdfPcEnabled && $btnPdfMobileEnabled) {
    $btnPdfClass = 'd-block';
} elseif ($btnPdfPcEnabled && !$btnPdfMobileEnabled) {
    $btnPdfClass = 'd-none d-md-block';
} elseif (!$btnPdfPcEnabled && $btnPdfMobileEnabled) {
    $btnPdfClass = 'd-block d-md-none';
} else {
    $btnPdfClass = 'd-none';
}

if ($btnWaPcEnabled && $btnWaMobileEnabled) {
    $btnWaClass = 'd-inline-flex';
} elseif ($btnWaPcEnabled && !$btnWaMobileEnabled) {
    $btnWaClass = 'd-none d-md-inline-flex';
} elseif (!$btnWaPcEnabled && $btnWaMobileEnabled) {
    $btnWaClass = 'd-inline-flex d-md-none';
} else {
    $btnWaClass = 'd-none';
}

$cardAgentPcEnabled = !isset($ts['card_agent_pc_enabled']) || !empty($ts['card_agent_pc_enabled']);
$cardAgentMobileEnabled = !isset($ts['card_agent_mobile_enabled']) || !empty($ts['card_agent_mobile_enabled']);
$cardAgentBadgeText = !empty($ts['card_agent_badge_text']) ? $ts['card_agent_badge_text'] : __('Verified Agent');

if ($cardAgentPcEnabled && $cardAgentMobileEnabled) {
    $cardAgentClass = 'd-inline-flex';
} elseif ($cardAgentPcEnabled && !$cardAgentMobileEnabled) {
    $cardAgentClass = 'd-none d-md-inline-flex';
} elseif (!$cardAgentPcEnabled && $cardAgentMobileEnabled) {
    $cardAgentClass = 'd-inline-flex d-md-none';
} else {
    $cardAgentClass = 'd-none';
}

$resortShowcaseItems = array(
    array(
        'index_label' => '1 / 4',
        'title' => "LEAXURY\nSTUDIO\nAPARTMENT IN\nNARAN",
        'name' => 'LEAXURY STUDIO APARTMENT IN NARAN',
        'status' => 'AVAILABLE',
        'status_color' => '#198754',
        'price' => 'FROM PKR 90.00 Lac',
        'desc' => 'Achieve fantastic results with an initial 5-year rental management programme, which offers a lucrative shared revenue scheme.',
        'image' => 'https://www.ramadacostadelsol.com/wp-content/uploads/2024/10/marina-park-costa-del-sol-234-5.jpg',
        'spec1_label' => 'APARTMENT SPECIFICATION',
        'spec1_left' => '<div><strong>Floors:</strong> 1</div><div><strong>Bed:</strong> 1</div>',
        'spec1_right' => '<div><strong>Area:</strong> 337 Sq Ft</div><div><strong>Bath:</strong> 1</div>',
        'spec2_label' => 'INVESTMENT HIGHLIGHTS',
        'spec2_left' => '<div><strong>Contract Type:</strong> Installments</div><div><strong>Project:</strong> White Alpine Lodges</div>',
        'spec2_right' => '<div><strong>Status:</strong> <span style="color:#198754; font-weight:700;">Available</span></div><div><strong>Agent:</strong> White Villas</div>',
        'url' => $this->Html->url('/Properties')
    ),
    array(
        'index_label' => '2 / 4',
        'title' => "TOWN HOUSE\nS.F",
        'name' => 'TOWN HOUSE-S.F',
        'status' => 'SOLD OUT',
        'status_color' => '#d11242',
        'price' => 'FROM €240,000',
        'desc' => 'Stunning renovated and fully furnished townhouses with private garden and terrace, offering exceptional long-term capital appreciation.',
        'image' => 'https://www.ramadacostadelsol.com/wp-content/uploads/2024/10/marina-del-sol-234-5.jpg',
        'spec1_label' => 'TOWN HOUSE SPECIFICATION',
        'spec1_left' => '<div><strong>Floors:</strong> 2</div><div><strong>Bed:</strong> 2</div>',
        'spec1_right' => '<div><strong>Area:</strong> 64m2</div><div><strong>Bath:</strong> 1.5</div>',
        'spec2_label' => 'INVESTMENT HIGHLIGHTS',
        'spec2_left' => '<div><strong>Contract Type:</strong> Direct</div><div><strong>Project:</strong> White Villas Luxury</div>',
        'spec2_right' => '<div><strong>Status:</strong> <span style="color:#d11242; font-weight:700;">Sold</span></div><div><strong>Agent:</strong> White Villas</div>',
        'url' => $this->Html->url('/Properties')
    ),
    array(
        'index_label' => '3 / 4',
        'title' => "SIERRA\nMARINA",
        'name' => 'SIERRA MARINA',
        'status' => 'AVAILABLE',
        'status_color' => '#198754',
        'price' => 'FROM €210,000',
        'desc' => 'Spacious Mediterranean apartments nestled in lush gardens with panoramic sea and mountain views and resort facilities.',
        'image' => 'https://www.ramadacostadelsol.com/wp-content/uploads/2024/10/sierra-marina-costa-del-sol-234-5.jpg',
        'spec1_label' => 'APARTMENT SPECIFICATION',
        'spec1_left' => '<div><strong>Floors:</strong> 1</div><div><strong>Bed:</strong> 1 to 2</div>',
        'spec1_right' => '<div><strong>Area:</strong> 55m2</div><div><strong>Bath:</strong> 1 to 2</div>',
        'spec2_label' => 'INVESTMENT HIGHLIGHTS',
        'spec2_left' => '<div><strong>Contract Type:</strong> Instalments</div><div><strong>Project:</strong> Sierra Residences</div>',
        'spec2_right' => '<div><strong>Status:</strong> <span style="color:#198754; font-weight:700;">Available</span></div><div><strong>Agent:</strong> White Villas</div>',
        'url' => $this->Html->url('/Properties')
    ),
    array(
        'index_label' => '4 / 4',
        'title' => "LOS\nAMIGOS",
        'name' => 'LOS AMIGOS BEACH',
        'status' => 'AVAILABLE',
        'status_color' => '#198754',
        'price' => 'FROM €275,000',
        'desc' => 'Direct beach access residences featuring private infinity pools, wellness club membership, and premier concierge hospitality.',
        'image' => 'https://www.ramadacostadelsol.com/wp-content/uploads/2024/10/los-amigos-costa-del-sol-234-5.jpg',
        'spec1_label' => 'VILLA SUITE SPECIFICATION',
        'spec1_left' => '<div><strong>Floors:</strong> 2</div><div><strong>Bed:</strong> 3</div>',
        'spec1_right' => '<div><strong>Area:</strong> 120m2</div><div><strong>Bath:</strong> 3</div>',
        'spec2_label' => 'INVESTMENT HIGHLIGHTS',
        'spec2_left' => '<div><strong>Contract Type:</strong> Flexible</div><div><strong>Project:</strong> Beachfront Club</div>',
        'spec2_right' => '<div><strong>Status:</strong> <span style="color:#198754; font-weight:700;">Available</span></div><div><strong>Agent:</strong> White Villas</div>',
        'url' => $this->Html->url('/Properties')
    )
);

// If database properties exist, format each real backend property for frontend homepage showcase
$maxPropCount = !empty($ts['properties_count']) ? max(1, min(30, (int)$ts['properties_count'])) : 4;
if (!empty($allviewedProperty)) {
    $dbShowcaseItems = array();
    $totalAvailable = count($allviewedProperty);
    $targetLimit = min($maxPropCount, $totalAvailable);
    
    foreach ($allviewedProperty as $idx => $p) {
        if ($idx >= $targetLimit) break;
        $prop = isset($p['Property']) ? $p['Property'] : $p;
        $idxNum = $idx + 1;
        
        $rawTitle = !empty($prop['name']) ? $prop['name'] : (!empty($prop['title']) ? $prop['title'] : 'PROPERTY ' . $idxNum);
        
        // Multi-line stage title
        $titleWords = explode(' ', trim($rawTitle));
        if (count($titleWords) >= 3) {
            $half = ceil(count($titleWords) / 2);
            $stageTitle = implode(' ', array_slice($titleWords, 0, $half)) . "\n" . implode(' ', array_slice($titleWords, $half));
        } elseif (count($titleWords) === 2) {
            $stageTitle = $titleWords[0] . "\n" . $titleWords[1];
        } else {
            $stageTitle = $rawTitle;
        }

        // Status & Color
        $rawStatus = !empty($prop['status']) ? strtoupper(trim($prop['status'])) : 'AVAILABLE';
        if ($rawStatus === 'SOLD' || $rawStatus === 'SOLDOUT' || $rawStatus === 'SOLD OUT') {
            $propStatus = 'SOLD OUT';
            $propStatusColor = '#d11242';
        } elseif ($rawStatus === 'RESERVED' || $rawStatus === 'BOOKED') {
            $propStatus = 'RESERVED';
            $propStatusColor = '#f59e0b';
        } else {
            $propStatus = 'AVAILABLE';
            $propStatusColor = '#198754';
        }

        // Price formatting in Lac / Crore / PKR
        $price = !empty($prop['price']) ? (float)$prop['price'] : 0;
        if ($price >= 10000000) {
            $priceDisplay = 'FROM PKR ' . number_format($price / 10000000, 2) . ' Crore';
        } elseif ($price >= 100000) {
            $priceDisplay = 'FROM PKR ' . number_format($price / 100000, 2) . ' Lac';
        } elseif ($price > 0) {
            $priceDisplay = 'FROM PKR ' . number_format($price);
        } else {
            $priceDisplay = 'PRICE ON INQUIRY';
        }

        // Description
        $rawDesc = !empty($prop['description']) ? trim(strip_tags($prop['description'])) : '';
        if (!empty($rawDesc)) {
            $propDesc = (strlen($rawDesc) > 180) ? substr($rawDesc, 0, 175) . '...' : $rawDesc;
        } else {
            $propDesc = 'Achieve fantastic results with an initial 5-year rental management programme, which offers a lucrative shared revenue scheme.';
        }

        // Photo file retrieval from PropertiesPhoto relation or direct column
        $photoFile = '';
        if (!empty($p['PropertiesPhoto']) && is_array($p['PropertiesPhoto'])) {
            foreach ($p['PropertiesPhoto'] as $pp) {
                if (!empty($pp['photo'])) {
                    $photoFile = $pp['photo'];
                    if (!empty($pp['home']) && $pp['home'] === 'Yes') {
                        break;
                    }
                }
            }
        }
        if (!empty($photoFile)) {
            $propImg = $this->Html->url('/img/propertiesphotos/' . $photoFile);
        } elseif (!empty($prop['photo'])) {
            $propImg = $this->Html->url('/img/propertiesphotos/' . $prop['photo']);
        } else {
            $propImg = 'https://www.ramadacostadelsol.com/wp-content/uploads/2024/10/marina-park-costa-del-sol-234-5.jpg';
        }

        // Specifications
        $typeName = !empty($p['Type']['name']) ? $p['Type']['name'] : (!empty($prop['type']) ? $prop['type'] : 'APARTMENT');
        $projectName = !empty($p['Project']['name']) ? $p['Project']['name'] : (!empty($p['Location']['name']) ? $p['Location']['name'] : 'White Villas');
        $contractName = !empty($p['Contract']['name']) ? $p['Contract']['name'] : 'Installments';
        $unitName = !empty($p['Unit']['name']) ? $p['Unit']['name'] : 'Marla';

        $propFloors = !empty($prop['floor']) ? $prop['floor'] : (!empty($prop['floors']) ? $prop['floors'] : '1');
        $propBeds = !empty($prop['bedroom']) ? $prop['bedroom'] : (!empty($prop['bedrooms']) ? $prop['bedrooms'] : '1');
        $propBaths = !empty($prop['bathroom']) ? $prop['bathroom'] : (!empty($prop['bathrooms']) ? $prop['bathrooms'] : '1');
        $propArea = !empty($prop['area']) ? ($prop['area'] . ' ' . $unitName) : (!empty($prop['size']) ? $prop['size'] : '337 Sq Ft');

        $propUrl = !empty($prop['id']) ? $this->Html->url(array('controller' => 'Properties', 'action' => 'view', $prop['id'])) : $this->Html->url('/Properties');
        $agentNameDisplay = !empty($p['User']['name']) ? $p['User']['name'] : $brandName;
        $agentPhoneDisplay = !empty($p['User']['mobile']) ? $p['User']['mobile'] : (!empty($contact) ? $contact : '+923391101156');
        $planPdfFile = !empty($prop['payment_plan_pdf']) ? $prop['payment_plan_pdf'] : '';

        $dbShowcaseItems[] = array(
            'id' => !empty($prop['id']) ? (int)$prop['id'] : 0,
            'agent_name' => $agentNameDisplay,
            'agent_phone' => $agentPhoneDisplay,
            'plan_pdf' => $planPdfFile,
            'index_label' => $idxNum . ' / ' . $targetLimit,
            'title' => $stageTitle,
            'name' => strtoupper($rawTitle),
            'status' => $propStatus,
            'status_color' => $propStatusColor,
            'price' => $priceDisplay,
            'desc' => $propDesc,
            'image' => $propImg,
            'spec1_label' => strtoupper($typeName) . ' SPECIFICATION',
            'spec1_left' => '<div><strong>Floors:</strong> ' . h($propFloors) . '</div><div><strong>Bed:</strong> ' . h($propBeds) . '</div>',
            'spec1_right' => '<div><strong>Area:</strong> ' . h($propArea) . '</div><div><strong>Bath:</strong> ' . h($propBaths) . '</div>',
            'spec2_label' => 'INVESTMENT HIGHLIGHTS',
            'spec2_left' => '<div><strong>Contract Type:</strong> ' . h($contractName) . '</div><div><strong>Project:</strong> ' . h($projectName) . '</div>',
            'spec2_right' => '<div><strong>Status:</strong> <span style="color:' . $propStatusColor . '; font-weight:700;">' . h($propStatus) . '</span></div><div><strong>Agent:</strong> ' . h($brandName) . '</div>',
            'url' => $propUrl
        );
    }
    
    if (!empty($dbShowcaseItems)) {
        $resortShowcaseItems = $dbShowcaseItems;
    }
}

// Reasons to Own
$reasonsSubtitle = !empty($ts['reasons_subtitle']) ? $ts['reasons_subtitle'] : 'EXCELLENT LIFESTYLE & INVESTMENT';
$reasonsTitle = !empty($ts['reasons_title']) ? $ts['reasons_title'] : 'REASONS TO OWN';
$reasonsDesc = !empty($ts['reasons_desc']) ? $ts['reasons_desc'] : 'A unique opportunity to purchase a luxury holiday home or lucrative investment property on the sun-drenched Costa del Sol with fully managed rental returns.';
$reasonsBtn = !empty($ts['reasons_btn_text']) ? $ts['reasons_btn_text'] : 'CONTACT US';
$reasonsImg = !empty($ts['reasons_image']) ? $ts['reasons_image'] : 'https://www.ramadacostadelsol.com/wp-content/uploads/2024/03/family-with-kids-running-on-the-beach-800v.webp';
$reasonsList = array();
$rawReasons = !empty($ts['reasons_items']) ? $ts['reasons_items'] : '';
if (!empty($rawReasons)) {
    $rawLines = preg_split('/\r\n|\r|\n/', (string)$rawReasons);
    foreach ($rawLines as $line) {
        $trimmed = trim($line);
        if ($trimmed !== '') {
            $reasonsList[] = $trimmed;
        }
    }
}
if (empty($reasonsList)) {
    $reasonsList = array(
        'High, guaranteed rental returns',
        'Generous owner holiday usage',
        '24-hour concierge, security, and resort services',
        'Prime location with scenic panoramic views'
    );
}

// Surrounding Area
$areaSubtitle = !empty($ts['area_subtitle']) ? $ts['area_subtitle'] : 'SURROUNDING AREA';
$areaTitle = !empty($ts['area_title']) ? $ts['area_title'] : 'MORE THAN A HOME';
$areaDesc = !empty($ts['area_desc']) ? $ts['area_desc'] : 'Explore the finest coastal destinations, premier golf clubs, and exceptional dining.';
$areaBtn = !empty($ts['area_btn_text']) ? $ts['area_btn_text'] : 'CONTACT US';
$areaCards = array(
    array(
        'title' => !empty($ts['area_card1_title']) ? $ts['area_card1_title'] : 'FUENGIROLA',
        'desc' => !empty($ts['area_card1_desc']) ? $ts['area_card1_desc'] : 'A lively coastal resort featuring miles of sandy beaches.',
        'image' => !empty($ts['area_card1_image']) ? $ts['area_card1_image'] : 'https://www.ramadacostadelsol.com/wp-content/uploads/2024/03/nerja-beach-costa-del-sol-spain-playa-burriana-600.webp'
    ),
    array(
        'title' => !empty($ts['area_card2_title']) ? $ts['area_card2_title'] : 'LA CALA DE MIJAS',
        'desc' => !empty($ts['area_card2_desc']) ? $ts['area_card2_desc'] : 'A charming village retaining authentic Andalusian character.',
        'image' => !empty($ts['area_card2_image']) ? $ts['area_card2_image'] : 'https://www.ramadacostadelsol.com/wp-content/uploads/2024/03/golf-costa-del-sol-spain-600.webp'
    ),
    array(
        'title' => !empty($ts['area_card3_title']) ? $ts['area_card3_title'] : 'MARBELLA & PUERTO BANÚS',
        'desc' => !empty($ts['area_card3_desc']) ? $ts['area_card3_desc'] : 'World-famous luxury marina and designer shopping.',
        'image' => !empty($ts['area_card3_image']) ? $ts['area_card3_image'] : 'https://www.ramadacostadelsol.com/wp-content/uploads/2024/03/views-on-the-alcazar-fortress-malaga-costa-del-sol-600.webp'
    )
);

// Other Properties / Horizons
$otherSubtitle = !empty($ts['other_subtitle']) ? $ts['other_subtitle'] : 'OTHER PROPERTIES';
$otherTitle = !empty($ts['other_title']) ? $ts['other_title'] : 'EXPAND YOUR HORIZONS';
$otherDesc = !empty($ts['other_desc']) ? $ts['other_desc'] : 'Discover an exceptional portfolio of properties in Spain, UK, and worldwide destinations.';
$otherImg = !empty($ts['other_image']) ? $ts['other_image'] : 'https://www.ramadacostadelsol.com/wp-content/uploads/2024/03/la-cala-golf-residences-costa-del-sol-spain-800.webp';
$otherDisclaimer = !empty($ts['other_disclaimer']) ? $ts['other_disclaimer'] : 'Purchaser should verify all details, floor plans, dimensions and specification with the sales team before entering contract.';

// Section Visibility Toggles
$heroSectionEnabled = !isset($ts['hero_section_enabled']) || !empty($ts['hero_section_enabled']);
$tickerEnabled = !isset($ts['ticker_enabled']) || !empty($ts['ticker_enabled']);
$introSectionEnabled = !isset($ts['intro_section_enabled']) || !empty($ts['intro_section_enabled']);
$propertiesSectionEnabled = !isset($ts['properties_section_enabled']) || !empty($ts['properties_section_enabled']);
$reasonsSectionEnabled = !isset($ts['reasons_section_enabled']) || !empty($ts['reasons_section_enabled']);
$areaSectionEnabled = !isset($ts['area_section_enabled']) || !empty($ts['area_section_enabled']);
$otherSectionEnabled = !isset($ts['other_section_enabled']) || !empty($ts['other_section_enabled']);
$amenitiesEnabled = !isset($ts['amenities_enabled']) || !empty($ts['amenities_enabled']);
$contactSectionEnabled = !isset($ts['contact_section_enabled']) || !empty($ts['contact_section_enabled']);
$newsletterSectionEnabled = !isset($ts['newsletter_section_enabled']) || !empty($ts['newsletter_section_enabled']);

// Ticker Bar Variables
$tickerBadgeText = !empty($ts['ticker_badge_text']) ? $ts['ticker_badge_text'] : '🔥 LATEST UPDATES';
$tickerBadgeBg = !empty($ts['ticker_badge_bg']) ? $ts['ticker_badge_bg'] : '#d11242';
$tickerBadgeColor = !empty($ts['ticker_badge_color']) ? $ts['ticker_badge_color'] : '#ffffff';
$tickerBgColor = !empty($ts['ticker_bg_color']) ? $ts['ticker_bg_color'] : '#07152a';
$tickerTextColor = !empty($ts['ticker_text_color']) ? $ts['ticker_text_color'] : '#ffffff';
$tickerSpeed = !empty($ts['ticker_speed']) ? (int)$ts['ticker_speed'] : 30;
$tickerLink = !empty($ts['ticker_link']) ? $ts['ticker_link'] : '';
$tickerRawText = !empty($ts['ticker_text']) ? $ts['ticker_text'] : '📢 Special Launch Offer: 10% Discount on Luxury 2 & 3 Bed Apartments! 🔥 | ⚡ Limited inventory available with flexible 36-month installment plans 🌴 | 💎 Book your site tour today and get exclusive VIP benefits 🏡 | ✨ Call +92339 1101156 for instant consultation!';
$tickerMobileHideBadge = !isset($ts['ticker_mobile_hide_badge']) || !empty($ts['ticker_mobile_hide_badge']);
$tickerMobileEnabled = !isset($ts['ticker_mobile_enabled']) || !empty($ts['ticker_mobile_enabled']);
$sectionsOrder = $themeModel->parseSectionsOrder(!empty($ts['sections_order']) ? $ts['sections_order'] : '');

$tickerItems = preg_split('/[\r\n|]+/', $tickerRawText);
$formattedTickerItems = array();
foreach ($tickerItems as $tItem) {
    $tTrim = trim($tItem);
    if (!empty($tTrim)) {
        $formattedTickerItems[] = '<span class="r-ticker-item">' . $tTrim . '</span>';
    }
}
$renderedTickerText = implode(' <span class="r-ticker-sep mx-3" style="color: #7cf9ff; opacity: 0.85;">⚡</span> ', $formattedTickerItems);

// Amenities & World-Class Facilities
$amenitiesSubtitle = !empty($ts['amenities_super_title']) ? $ts['amenities_super_title'] : 'WORLD-CLASS LIVING';
$amenitiesTitle = !empty($ts['amenities_main_title']) ? $ts['amenities_main_title'] : 'Premium Amenities & Modern Facilities';
$amenitiesDesc = !empty($ts['amenities_desc']) ? $ts['amenities_desc'] : 'Experience an extraordinary standard of living with cutting-edge recreational hubs, uninterrupted utilities, executive leisure facilities, and state-of-the-art wellness zones.';
$amenitiesList = $themeModel->decodeAmenities(!empty($ts['amenities_json']) ? $ts['amenities_json'] : '');

// Contact Form
$contactSubtitle = !empty($ts['contact_subtitle']) ? $ts['contact_subtitle'] : "GET IN TOUCH";
$contactTitle = !empty($ts['contact_title']) ? $ts['contact_title'] : 'REQUEST A CONSULTATION';
$contactBtn = !empty($ts['contact_btn_text']) ? $ts['contact_btn_text'] : 'GET IN TOUCH';

// Newsletter & Accordion
$newsTitle = !empty($ts['newsletter_title']) ? $ts['newsletter_title'] : 'Stay up-to-date';
$newsSubtitle = !empty($ts['newsletter_subtitle']) ? $ts['newsletter_subtitle'] : 'Be the first to hear about our special offers and news.';
$newsBtn = !empty($ts['newsletter_btn_text']) ? $ts['newsletter_btn_text'] : 'SIGN UP HERE';
$accTitle = !empty($ts['accordion_title']) ? $ts['accordion_title'] : 'ABOUT IDILIQ HOTELS & RESORTS';
$accContent = !empty($ts['accordion_content']) ? $ts['accordion_content'] : 'IDILIQ Hotels & Resorts is part of the IDILIQ Group of companies, which own and operate a collection of 10 Hotels & Resorts in Spain, Orlando and the UK, 7 of which are Wyndham or Ramada branded.<br><br><strong>Spain &gt;</strong> <a href="https://www.wyndhamgrandcostadelsol.com" target="_blank" rel="noopener" class="text-danger text-decoration-none">Wyndham Grand Costa del Sol</a> | <a href="https://royalmarbellagolfresort.com" target="_blank" rel="noopener" class="text-danger text-decoration-none">Royal Marbella Golf Resort</a> &bull; <strong>Tenerife &gt;</strong> <a href="https://www.wyndhamresidencescostaadeje.com" target="_blank" rel="noopener" class="text-danger text-decoration-none">Wyndham Residences Costa Adeje</a> &bull; <strong>Majorca &gt;</strong> Wyndham Grand Porto Colom';
?>

<!-- =========================================================================
     DYNAMIC HOMEPAGE SECTIONS RENDERER (Configured by Sections Order)
     ========================================================================= -->
<?php
foreach ($sectionsOrder as $secKey):
    if ($secKey === 'hero'):
?>
    <!-- 1. Hero Video Section (#header_video) -->
    <?php if ($heroSectionEnabled): ?>
    <?php 
    $heroAudioBtnEnabled = !isset($ts['hero_audio_btn_enabled']) || !empty($ts['hero_audio_btn_enabled']); 
    $heroVideoType = 'direct';
    $youtubeId = '';
    $vimeoId = '';

    if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/|youtube\.com\/shorts\/)([^"&?\/\s]{11})/i', $heroVideoUrl, $ytMatch)) {
        $heroVideoType = 'youtube';
        $youtubeId = $ytMatch[1];
    } elseif (preg_match('/(?:vimeo\.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|video\/|)(\d+))/i', $heroVideoUrl, $vmMatch)) {
        $heroVideoType = 'vimeo';
        $vimeoId = !empty($vmMatch[3]) ? $vmMatch[3] : (!empty($vmMatch[1]) ? $vmMatch[1] : '');
    }
    ?>
    <div id="header_video" class="position-relative w-100 overflow-hidden bg-dark" style="height: 100vh; min-height: 600px; max-height: 850px;">
        <div class="position-absolute top-0 start-0 w-100 h-100 overflow-hidden" style="z-index: 1;">
            <?php if ($heroVideoType === 'youtube'): ?>
                <div class="hero-embed-wrapper">
                    <iframe id="heroYoutubeIframe" 
                            src="https://www.youtube-nocookie.com/embed/<?php echo h($youtubeId); ?>?autoplay=1&mute=1&loop=1&playlist=<?php echo h($youtubeId); ?>&controls=0&showinfo=0&rel=0&modestbranding=1&iv_load_policy=3&disablekb=1&playsinline=1&enablejsapi=1" 
                            allow="autoplay; encrypted-media" 
                            allowfullscreen 
                            title="Hero Video Background"></iframe>
                </div>
            <?php elseif ($heroVideoType === 'vimeo'): ?>
                <div class="hero-embed-wrapper">
                    <iframe id="heroVimeoIframe" 
                            src="https://player.vimeo.com/video/<?php echo h($vimeoId); ?>?autoplay=1&muted=1&loop=1&background=1&autopause=0" 
                            allow="autoplay; fullscreen" 
                            allowfullscreen 
                            title="Hero Video Background"></iframe>
                </div>
            <?php else: ?>
                <video id="heroBgVideo" width="1280" height="720" class="w-100 h-100" style="object-fit: cover; object-position: center;" preload="auto" loop autoplay muted playsinline>
                    <source src="<?php echo h($heroVideoUrl); ?>" type="video/mp4">
                    <source src="https://www.ramadacostadelsol.com/wp-content/uploads/2025/03/Ramada-Costa-del-Sol-1080px.mp4" type="video/mp4">
                </video>
            <?php endif; ?>
        </div>
        <div class="position-absolute top-0 start-0 w-100 h-100" style="background: rgba(0, 0, 0, 0.25); z-index: 2; pointer-events: none;"></div>

        <?php if ($heroAudioBtnEnabled): ?>
        <button id="heroAudioToggle" type="button" class="btn-hero-audio" aria-label="Toggle Audio" title="Mute / Unmute Audio" style="position: absolute; bottom: 35px; right: 35px; z-index: 10; background: rgba(7, 21, 42, 0.85); color: #ffffff; border: 1px solid rgba(255,255,255,0.25); border-radius: 50px; padding: 9px 18px; font-size: 13px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); cursor: pointer; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(0,0,0,0.4);">
            <i id="heroAudioIcon" class="fa fa-volume-off text-danger" style="font-size: 16px;"></i>
            <span id="heroAudioLabel" style="letter-spacing: 0.06em; text-transform: uppercase; font-size: 11px;">Unmute Video</span>
        </button>
        <script>
        (function() {
            var btn = document.getElementById('heroAudioToggle');
            var icon = document.getElementById('heroAudioIcon');
            var label = document.getElementById('heroAudioLabel');
            var isMuted = true;
            var videoType = '<?php echo $heroVideoType; ?>';

            if (btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    isMuted = !isMuted;

                    if (!isMuted) {
                        if (videoType === 'youtube') {
                            var iframe = document.getElementById('heroYoutubeIframe');
                            if (iframe && iframe.contentWindow) {
                                iframe.contentWindow.postMessage('{"event":"command","func":"unMute","args":""}', '*');
                                iframe.contentWindow.postMessage('{"event":"command","func":"setVolume","args":[80]}', '*');
                            }
                        } else if (videoType === 'vimeo') {
                            var vIframe = document.getElementById('heroVimeoIframe');
                            if (vIframe && vIframe.contentWindow) {
                                vIframe.contentWindow.postMessage('{"method":"setVolume","value":"1"}', '*');
                            }
                        } else {
                            var vid = document.getElementById('heroBgVideo');
                            if (vid) {
                                vid.muted = false;
                                vid.volume = 0.8;
                                vid.play().catch(function(){});
                            }
                        }

                        if (icon) icon.className = 'fa fa-volume-up text-success';
                        if (label) label.textContent = 'Mute Audio';
                        btn.style.borderColor = 'rgba(25, 135, 84, 0.6)';
                    } else {
                        if (videoType === 'youtube') {
                            var iframe = document.getElementById('heroYoutubeIframe');
                            if (iframe && iframe.contentWindow) {
                                iframe.contentWindow.postMessage('{"event":"command","func":"mute","args":""}', '*');
                            }
                        } else if (videoType === 'vimeo') {
                            var vIframe = document.getElementById('heroVimeoIframe');
                            if (vIframe && vIframe.contentWindow) {
                                vIframe.contentWindow.postMessage('{"method":"setVolume","value":"0"}', '*');
                            }
                        } else {
                            var vid = document.getElementById('heroBgVideo');
                            if (vid) {
                                vid.muted = true;
                            }
                        }

                        if (icon) icon.className = 'fa fa-volume-off text-danger';
                        if (label) label.textContent = 'Unmute Video';
                        btn.style.borderColor = 'rgba(255, 255, 255, 0.25)';
                    }
                });
            }
        })();
        </script>
        <?php endif; ?>
    </div>
    <?php endif; ?>

<?php elseif ($secKey === 'ticker'): ?>
    <!-- 2. Flash News & Update Ticker Bar -->
    <?php if ($tickerEnabled && $tickerMobileEnabled && !empty($renderedTickerText)): ?>
    <div class="r-ticker-bar" style="background: <?php echo h($tickerBgColor); ?>; border-top: 1px solid rgba(255,255,255,0.08); border-bottom: 2px solid <?php echo h($tickerBadgeBg); ?>; color: <?php echo h($tickerTextColor); ?>; position: relative; z-index: 20; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.15);">
        <div class="container-fluid px-3 px-md-4">
            <div class="d-flex align-items-center py-2">
                <div class="r-ticker-badge align-items-center flex-shrink-0 <?php echo $tickerMobileHideBadge ? 'd-none d-md-flex' : 'd-flex'; ?>" style="background: <?php echo h($tickerBadgeBg); ?>; color: <?php echo h($tickerBadgeColor); ?>; padding: 6px 14px; border-radius: 4px; font-weight: 800; font-size: 11.5px; letter-spacing: 0.06em; text-transform: uppercase; margin-right: 15px; box-shadow: 0 2px 8px rgba(0,0,0,0.25);">
                    <span class="r-ticker-pulse me-2"></span>
                    <span><?php echo $tickerBadgeText; ?></span>
                </div>

                <div class="r-ticker-content-wrapper flex-grow-1 overflow-hidden position-relative" style="white-space: nowrap; height: 32px; display: flex; align-items: center;">
                    <div class="r-ticker-track" style="animation-duration: <?php echo (int)$tickerSpeed; ?>s;">
                        <span class="r-ticker-text"><?php echo $renderedTickerText; ?></span>
                        <span class="r-ticker-text" aria-hidden="true"><?php echo $renderedTickerText; ?></span>
                    </div>
                </div>

                <?php if (!empty($tickerLink)): ?>
                <div class="ms-3 flex-shrink-0 d-none d-md-block">
                    <a href="<?php echo h($tickerLink); ?>" class="btn btn-xs text-uppercase fw-bold" style="background: rgba(255,255,255,0.12); color: #ffffff; border: 1px solid rgba(255,255,255,0.25); border-radius: 4px; font-size: 11px; padding: 4px 12px; transition: all 0.2s ease;">
                        <i class="fa fa-arrow-right me-1"></i> View
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

<?php elseif ($secKey === 'intro'): ?>
    <!-- 3. Intro Section with Breadcrumb & Split Features (#intro) - Matching exact Screenshot 1 -->
    <?php if ($introSectionEnabled): ?>
    <section id="intro" class="py-5 bg-white" style="padding-top: 70px !important; padding-bottom: 70px !important;">
        <div class="r-container">
            <!-- Breadcrumb & Top Center Heading -->
            <div class="text-center mb-5 pb-3">
                <div class="ybread mb-4">
                    <nav aria-label="breadcrumbs">
                        <p class="small text-uppercase mb-0" style="letter-spacing: 2px; font-size: 11px; color: #888;">
                            <a href="<?php echo $this->Html->url('/'); ?>" class="text-muted text-decoration-none">HOME</a>
                            <span class="separator text-muted mx-2"> &raquo; </span>
                            <span class="last fw-bold" style="color: #666;">REAL ESTATE</span>
                        </p>
                    </nav>
                </div>
                
                <h1 class="vc_custom_heading text-uppercase mb-3" style="font-family: 'League Spartan', sans-serif; font-size: clamp(32px, 4.2vw, 54px); color: #666666; line-height: 1.1; font-weight: 300; letter-spacing: 0.06em;">
                    <?php echo wv_render_rich_title($introHeading); ?>
                </h1>
                <p class="fs-5 mb-0" style="font-size: 16px; color: #666666; line-height: 1.5; font-weight: 400; letter-spacing: 0.02em;">
                    <?php echo wv_render_rich_html($introSubheading); ?>
                </p>
            </div>

            <!-- Split Feature 1 (Text Left, Image Right) -->
            <div class="row align-items-center g-4 g-lg-5 mb-5 pb-4">
                <div class="col-12 col-lg-6 pe-lg-5">
                    <div class="text-uppercase fw-bold mb-2" style="font-family: 'League Spartan', sans-serif; font-size: 15px; color: #d11242; letter-spacing: 0.08em;">
                        <strong><?php echo wv_render_rich_title($f1Badge); ?></strong>
                    </div>
                    <h2 class="h2 fw-bold text-uppercase mb-3" style="font-family: 'League Spartan', sans-serif; color: #555555; line-height: 1.15; font-size: clamp(26px, 3.2vw, 36px); letter-spacing: 0.04em;">
                        <strong><?php echo wv_render_rich_title($f1Title); ?></strong>
                    </h2>
                    <div class="text-secondary mb-4" style="line-height: 1.8; font-size: 0.95rem; color: #555555;">
                        <p><?php echo wv_render_rich_html($f1Desc); ?></p>
                    </div>
                    <a href="#contact-form" class="nectar-button">
                        <span><?php echo h($f1Btn); ?></span>
                    </a>
                </div>
                <div class="col-12 col-lg-6">
                    <div class="position-relative overflow-hidden rounded-0 shadow-sm r-ramada-img-card">
                        <img src="<?php echo h($f1Img); ?>" alt="<?php echo h($f1Title); ?>" class="img-fluid w-100 r-zoom-img" style="height: 440px; object-fit: cover;">
                    </div>
                </div>
            </div>

            <!-- Split Feature 2 (Image Left, Text Right - Zig-zag) -->
            <div class="row align-items-center g-4 g-lg-5 my-4 my-md-5 pt-3">
                <div class="col-12 col-lg-6">
                    <div class="position-relative overflow-hidden rounded-0 shadow-sm r-ramada-img-card">
                        <img src="<?php echo h($f2Img); ?>" alt="<?php echo h($f2Title); ?>" class="img-fluid w-100 r-zoom-img" style="height: 540px; object-fit: cover;">
                    </div>
                </div>
                <div class="col-12 col-lg-6 ps-lg-5">
                    <div class="text-uppercase fw-bold mb-2" style="font-family: 'League Spartan', sans-serif; font-size: 15px; color: #d11242; letter-spacing: 0.08em;">
                        <strong><?php echo wv_render_rich_title($f2Badge); ?></strong>
                    </div>
                    <h2 class="h2 fw-bold text-uppercase mb-3" style="font-family: 'League Spartan', sans-serif; color: #555555; line-height: 1.15; font-size: clamp(26px, 3.2vw, 36px); letter-spacing: 0.04em;">
                        <strong><?php echo wv_render_rich_title($f2Title); ?></strong>
                    </h2>
                    <div class="text-secondary mb-4" style="line-height: 1.8; font-size: 0.95rem; color: #555555;">
                        <p><?php echo wv_render_rich_html($f2Desc); ?></p>
                    </div>
                    <a href="#contact-form" class="nectar-button">
                        <span><?php echo h($f2Btn); ?></span>
                    </a>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

<?php elseif ($secKey === 'properties'): ?>
    <!-- 4. PROPERTIES FOR SALE SHOWCASE (#resorts-showcase) - Matching exact Screenshot 2 -->
    <?php if ($propertiesSectionEnabled): ?>
    <section id="resorts-showcase" class="py-5 bg-white position-relative" style="overflow-x: clip; padding-top: 70px !important; padding-bottom: 70px !important;">
        <div class="container-fluid px-0">
            <!-- Main Section Header -->
            <div class="text-center mb-5 pb-4 px-3">
                <div class="text-uppercase fw-bold mb-2" style="font-family: 'League Spartan', sans-serif; font-size: 15px; color: #d11242; letter-spacing: 0.1em;">
                    <strong><?php echo h($resortsSubtitle); ?></strong>
                </div>
                <h2 class="display-5 fw-bold text-uppercase mb-3" style="font-family: 'League Spartan', sans-serif; font-size: clamp(32px, 4vw, 44px); color: #555555; letter-spacing: 0.05em;">
                    <strong><?php echo h($resortsTitle); ?></strong>
                </h2>
                <div class="mx-auto my-3" style="width: 50px; height: 3px; background-color: #d11242;"></div>
            </div>

            <!-- Stage Rows Loop -->
            <?php foreach ($resortShowcaseItems as $resort): ?>
            <div class="r-property-stage-row">
                <div class="r-showcase-split-wrap">
                    <div class="row g-0 align-items-center">
                        <!-- Left Column: Counter, Big Title, Desc -->
                        <div class="col-12 col-lg-5 r-showcase-left-col">
                            <div class="r-resort-counter"><?php echo h($resort['index_label']); ?></div>
                            <h2 class="r-resort-main-title"><?php echo nl2br(h($resort['title'])); ?></h2>
                            <div class="r-resort-main-desc">
                                <p><?php echo h($resort['desc']); ?></p>
                            </div>
                        </div>

                        <!-- Right Column: Tall Hero Image with Overlapping Floating White Specification Card -->
                        <div class="col-12 col-lg-7 r-showcase-right-col position-relative">
                            <div class="r-resort-img-container">
                                <a href="<?php echo $resort['url']; ?>" class="d-block w-100 h-100">
                                    <img src="<?php echo h($resort['image']); ?>" alt="<?php echo h($resort['name']); ?>" class="r-resort-hero-img">
                                </a>
                            </div>

                            <!-- Overlapping Floating White Specification Card (Matching Screenshot 2) -->
                            <div class="r-resort-floating-card">
                                <div class="r-card-status-badge" style="color: <?php echo $resort['status_color']; ?>;">
                                    <?php echo h($resort['status']); ?>
                                </div>
                                <div class="r-card-price-sub">
                                    <?php echo h($resort['price']); ?>
                                </div>

                                <!-- Box 1: Specification Table -->
                                <div class="r-card-spec-label"><?php echo h($resort['spec1_label']); ?></div>
                                <div class="r-card-spec-box">
                                    <table>
                                        <tbody>
                                            <tr>
                                                <td style="width: 50%; padding: 3px 6px;"><?php echo $resort['spec1_left']; ?></td>
                                                <td style="width: 50%; padding: 3px 6px;"><?php echo $resort['spec1_right']; ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Box 2: Investment Highlights Table -->
                                <div class="r-card-spec-label"><?php echo h($resort['spec2_label']); ?></div>
                                <div class="r-card-spec-box">
                                    <table>
                                        <tbody>
                                            <tr>
                                                <td style="width: 50%; padding: 3px 6px;"><?php echo $resort['spec2_left']; ?></td>
                                                <td style="width: 50%; padding: 3px 6px;"><?php echo $resort['spec2_right']; ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="mt-3 d-flex flex-column gap-2">
                                    <a href="#contact-form" class="nectar-button w-100" style="padding: 11px 20px !important; min-width: unset; font-size: 13px;">
                                        <span><?php echo __('CONTACT US'); ?></span>
                                    </a>
                                    <?php if ($btnPdfClass !== 'd-none'): ?>
                                    <button type="button" class="btn btn-outline-danger w-100 py-2 r-btn-download-plan <?php echo $btnPdfClass; ?>" data-bs-toggle="modal" data-bs-target="#leadDownloadModal" data-property-id="<?php echo !empty($resort['id']) ? (int)$resort['id'] : 0; ?>" data-property-name="<?php echo h($resort['name']); ?>" style="font-family: 'League Spartan', sans-serif; font-size: 12px; font-weight: 800; letter-spacing: 0.05em; border-radius: 0; text-transform: uppercase; border-width: 1.5px;">
                                        <i class="fa fa-file-pdf-o me-1"></i> <?php echo h($btnPdfText); ?>
                                    </button>
                                    <?php endif; ?>
                                </div>

                                <?php if ($cardAgentClass !== 'd-none' || $btnWaClass !== 'd-none'): ?>
                                <!-- Verified Agent Badge & WhatsApp Direct Quick-Chat -->
                                <div class="mt-3 pt-2 border-top d-flex align-items-center justify-content-between" style="font-size: 12px;">
                                    <div>
                                        <?php if ($cardAgentClass !== 'd-none'): ?>
                                        <span class="badge bg-light text-dark border px-2 py-1 align-items-center gap-1 <?php echo $cardAgentClass; ?>" style="font-size: 11px; font-weight: 700;">
                                            <i class="fa fa-shield text-success"></i> <?php echo h(!empty($resort['agent_name']) ? $resort['agent_name'] : $brandName); ?><?php if ($cardAgentBadgeText): ?> &middot; <span class="text-muted"><?php echo h($cardAgentBadgeText); ?></span><?php endif; ?>
                                        </span>
                                        <?php endif; ?>
                                    </div>
                                    <?php 
                                    $waPhone = preg_replace('/[^0-9]/', '', !empty($resort['agent_phone']) ? $resort['agent_phone'] : '923391101156');
                                    $waText = urlencode('Hello, I am interested in ' . $resort['name'] . ' listed on White Villas. Please share more details.');
                                    ?>
                                    <?php if ($btnWaClass !== 'd-none'): ?>
                                    <a href="https://wa.me/<?php echo $waPhone; ?>?text=<?php echo $waText; ?>" target="_blank" class="btn btn-sm btn-success px-2 py-1 align-items-center gap-1 <?php echo $btnWaClass; ?>" style="font-size: 11px; font-weight: 800; background: #25D366; border: none; border-radius: 4px;">
                                        <i class="fa fa-whatsapp"></i> <?php echo h($btnWaText); ?>
                                    </a>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

<?php elseif ($secKey === 'reasons'): ?>
    <!-- 5. Reasons to Own (#reasons-to-own) -->
    <?php if ($reasonsSectionEnabled): ?>
    <section id="reasons-to-own" class="py-5 text-white position-relative" style="background-color: #d11242; padding-top: 80px !important; padding-bottom: 80px !important;">
        <div class="r-container">
            <div class="row align-items-center g-4 g-lg-5">
                <!-- Left Column -->
                <div class="col-12 col-lg-6">
                    <h2 class="display-6 fw-bold mb-4 text-uppercase" style="font-family: 'League Spartan', sans-serif; color: #f2e8dd; letter-spacing: 0.04em;">
                        <strong><?php echo wv_render_rich_title($reasonsTitle); ?></strong>
                    </h2>
                    
                    <ul class="list-unstyled mb-4 text-white" style="font-size: 0.95rem; line-height: 2;">
                        <?php foreach ($reasonsList as $reason): ?>
                            <li class="d-flex align-items-start mb-2">
                                <i class="fa fa-star text-warning me-3 mt-1 flex-shrink-0"></i>
                                <span><?php echo wv_render_rich_html($reason, false); ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <a href="#contact-form" class="nectar-button see-through-white">
                        <span><?php echo h($reasonsBtn); ?></span>
                    </a>
                </div>

                <!-- Right Column -->
                <div class="col-12 col-lg-6 text-center">
                    <div class="position-relative overflow-hidden rounded-1 shadow-lg">
                        <img src="<?php echo h($reasonsImg); ?>" alt="<?php echo h($reasonsTitle); ?>" class="img-fluid w-100" style="max-height: 520px; object-fit: cover;">
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

<?php elseif ($secKey === 'area'): ?>
    <!-- 6. Surrounding Area Highlights (#surrounding-area) -->
    <?php if ($areaSectionEnabled): ?>
    <section id="surrounding-area" class="py-5 bg-white" style="padding-top: 80px !important; padding-bottom: 80px !important;">
        <div class="r-container">
            <div class="text-center mb-5">
                <div class="text-uppercase fw-bold mb-1" style="font-family: 'League Spartan', sans-serif; font-size: 18px; color: #d11242; line-height: 20px;">
                    <strong><?php echo wv_render_rich_title($areaSubtitle); ?></strong>
                </div>
                <h2 class="display-6 fw-bold text-uppercase mb-3" style="font-family: 'League Spartan', sans-serif; font-size: 32px; color: #666666; line-height: 1.2;">
                    <strong><?php echo wv_render_rich_title($areaTitle); ?></strong>
                </h2>
                <div class="mx-auto my-3" style="width: 60px; height: 3px; background-color: #d11242;"></div>
            </div>

            <div class="row g-4 mb-4">
                <?php foreach ($areaCards as $card): ?>
                <div class="col-12 col-md-4">
                    <div class="card border-0 shadow-sm rounded-1 overflow-hidden h-100">
                        <img src="<?php echo h($card['image']); ?>" alt="<?php echo h($card['title']); ?>" class="w-100" style="height: 260px; object-fit: cover;">
                        <div class="card-body p-4 text-center">
                            <h4 class="h5 fw-bold text-dark mb-2"><?php echo wv_render_rich_title($card['title']); ?></h4>
                            <p class="small text-muted mb-0"><?php echo wv_render_rich_html($card['desc']); ?></p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="text-center text-muted my-4 mx-auto" style="max-width: 900px; font-size: 0.95rem; line-height: 1.8; color: #666;">
                <p><?php echo wv_render_rich_html($areaDesc); ?></p>
            </div>

            <div class="text-center pt-2">
                <a href="#contact-form" class="nectar-button">
                    <span><?php echo h($areaBtn); ?></span>
                </a>
            </div>
        </div>
    </section>
    <?php endif; ?>

<?php elseif ($secKey === 'other'): ?>
    <!-- 7. Expand Your Horizons (#other-properties) -->
    <?php if ($otherSectionEnabled): ?>
    <section id="other-properties" class="py-5" style="background-color: #fafafa; padding-top: 70px !important; padding-bottom: 70px !important;">
        <div class="r-container">
            <div class="row align-items-center g-4 g-lg-5">
                <!-- Left Image -->
                <div class="col-12 col-lg-6">
                    <div class="position-relative overflow-hidden rounded-1 shadow-sm r-ramada-img-card">
                        <img src="<?php echo h($otherImg); ?>" alt="<?php echo h($otherTitle); ?>" class="img-fluid w-100 r-zoom-img" style="height: 420px; object-fit: cover;">
                    </div>
                </div>
                <!-- Right Content -->
                <div class="col-12 col-lg-6 ps-lg-5">
                    <div class="text-uppercase fw-bold mb-2" style="font-family: 'League Spartan', sans-serif; font-size: 18px; color: #d11242; line-height: 1;">
                        <strong><?php echo wv_render_rich_title($otherSubtitle); ?></strong>
                    </div>
                    <h2 class="h2 fw-bold text-uppercase mb-3" style="font-family: 'League Spartan', sans-serif; color: #666666;">
                        <strong><?php echo wv_render_rich_title($otherTitle); ?></strong>
                    </h2>
                    <div class="text-secondary mb-4" style="line-height: 1.8; font-size: 1rem; color: #555555;">
                        <p><?php echo wv_render_rich_html($otherDesc); ?></p>
                    </div>
                    <div class="d-flex flex-wrap gap-3">
                        <a href="<?php echo $this->Html->url('/Properties'); ?>" class="nectar-button">
                            <span><?php echo __('DISCOVER MORE'); ?></span>
                        </a>
                        <a href="<?php echo $this->Html->url('/paymentplans'); ?>" class="nectar-button btn-outline-theme">
                            <span><?php echo __('PAYMENT PLAN'); ?></span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Disclaimer Box -->
            <?php if (!empty($otherDisclaimer)): ?>
            <div class="card border-0 rounded-1 mt-5 p-4 text-muted small" style="background-color: #f4f4f4; font-size: 12px; line-height: 1.7;">
                <p class="mb-0"><?php echo wv_render_rich_html($otherDisclaimer); ?></p>
            </div>
            <?php endif; ?>
        </div>
    </section>
    <?php endif; ?>

<?php elseif ($secKey === 'amenities'): ?>
    <!-- 8. World-Class Amenities (#amenities) -->
    <?php if ($amenitiesEnabled && !empty($amenitiesList)): ?>
    <section id="amenities" class="py-5 position-relative" style="background-color: #ffffff; padding-top: 80px !important; padding-bottom: 80px !important; border-top: 1px solid #f1f5f9; border-bottom: 1px solid #f1f5f9;">
        <div class="r-container">
            <div class="text-center mb-5 pb-2">
                <div class="text-uppercase fw-bold mb-2" style="font-family: 'League Spartan', sans-serif; font-size: 16px; color: #d11242; letter-spacing: 0.1em;">
                    <strong><?php echo wv_render_rich_title($amenitiesSubtitle); ?></strong>
                </div>
                <h2 class="display-6 fw-bold text-uppercase mb-3" style="font-family: 'League Spartan', sans-serif; font-size: 32px; color: #334155; line-height: 1.2;">
                    <strong><?php echo wv_render_rich_title($amenitiesTitle); ?></strong>
                </h2>
                <div class="mx-auto my-3" style="width: 60px; height: 3px; background-color: #d11242;"></div>
                <?php if (!empty($amenitiesDesc)): ?>
                    <div class="text-muted mx-auto" style="max-width: 850px; font-size: 15px; line-height: 1.7; color: #64748b;">
                        <p class="mb-0"><?php echo wv_render_rich_html($amenitiesDesc); ?></p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="row g-4">
                <?php foreach ($amenitiesList as $amenity): 
                    if (empty($amenity['status'])) continue;
                    $icon = !empty($amenity['icon']) ? $amenity['icon'] : 'fa-star';
                    if (strpos($icon, 'fa ') !== 0 && strpos($icon, 'fa-') === 0) {
                        $icon = 'fa ' . $icon;
                    }
                ?>
                <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                    <div class="card h-100 border-0 text-center p-4 rounded-3 amenity-card" style="background: #f8fafc; border: 1px solid #e2e8f0 !important; transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); box-shadow: 0 4px 12px rgba(15, 23, 42, 0.03);">
                        <div class="mx-auto mb-3 d-flex align-items-center justify-content-center rounded-circle amenity-icon-box" style="width: 64px; height: 64px; background: #fff; color: #d11242; box-shadow: 0 6px 16px rgba(209, 18, 66, 0.12); font-size: 24px; transition: all 0.3s ease;">
                            <i class="<?php echo h($icon); ?>"></i>
                        </div>
                        
                        <?php if (!empty($amenity['badge'])): ?>
                            <span class="badge mx-auto mb-2 px-2 py-1 text-uppercase" style="font-size: 10px; font-weight: 700; letter-spacing: 0.5px; background: rgba(209, 18, 66, 0.08); color: #d11242; border-radius: 4px; display: inline-block;">
                                <?php echo h($amenity['badge']); ?>
                            </span>
                        <?php endif; ?>

                        <h5 class="fw-bold mb-2" style="font-size: 16px; color: #0f172a; font-family: 'League Spartan', sans-serif;">
                            <?php echo wv_render_rich_title($amenity['title']); ?>
                        </h5>
                        
                        <?php if (!empty($amenity['description'])): ?>
                            <p class="small mb-0 text-muted" style="font-size: 12.5px; line-height: 1.6; color: #64748b;">
                                <?php echo wv_render_rich_html($amenity['description']); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <style>
    .amenity-card:hover {
        transform: translateY(-6px);
        background: #ffffff !important;
        border-color: rgba(209, 18, 66, 0.3) !important;
        box-shadow: 0 16px 30px rgba(15, 23, 42, 0.09) !important;
    }
    .amenity-card:hover .amenity-icon-box {
        background: #d11242 !important;
        color: #ffffff !important;
        transform: scale(1.1);
    }
    </style>
    <?php endif; ?>

<?php elseif ($secKey === 'contact'): ?>
    <!-- 9. Get in Touch Lead Consultation Form (#contact-form) -->
    <?php if ($contactSectionEnabled): ?>
    <section id="contact-form" class="py-5 position-relative" style="background-color: #f4f4f4; padding-top: 80px !important; padding-bottom: 80px !important;">
        <div class="r-container">
            <div class="row justify-content-center">
                <div class="col-12 col-lg-8">
                    <div class="text-center mb-5">
                        <div class="text-uppercase fw-bold mb-1" style="font-family: 'League Spartan', sans-serif; font-size: 18px; color: #d11242; line-height: 1.1;">
                            <strong><?php echo h($contactSubtitle); ?></strong>
                        </div>
                        <h2 class="display-6 fw-bold text-uppercase mb-3" style="font-family: 'League Spartan', sans-serif; color: #666666; line-height: 1.2;">
                            <strong><?php echo h($contactTitle); ?></strong>
                        </h2>
                        <div class="mx-auto my-3" style="width: 60px; height: 3px; background-color: #d11242;"></div>
                    </div>

                    <div class="p-4 p-md-5 rounded-1 shadow-sm bg-white" style="border: 1px solid #e5e5e5;">
                        <div id="contactInquiryAlert" style="display: none;" class="alert mb-4"></div>

                        <form id="themeInquiryForm" action="<?php echo $this->Html->url('/Contacts/send_inquiry'); ?>" method="POST">
                            <input type="hidden" name="data[Contact][source]" value="Real Estate Pro Theme - Web Consultation">
                            <input type="hidden" name="is_ajax" value="1">

                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <label class="form-label text-dark fw-bold small text-uppercase" style="font-size: 12px;">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" name="data[Contact][name]" id="inq_name" class="form-control rounded-0 p-3 bg-white" required placeholder="Full Name" style="border: 1px solid #c7c7c7;">
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label text-dark fw-bold small text-uppercase" style="font-size: 12px;">WhatsApp / Phone Number <span class="text-danger">*</span></label>
                                    <input type="tel" name="data[Contact][phone]" id="inq_phone" class="form-control rounded-0 p-3 bg-white" required placeholder="e.g. +92 300 1234567" style="border: 1px solid #c7c7c7;">
                                </div>
                                <div class="col-12">
                                    <label class="form-label text-dark fw-bold small text-uppercase" style="font-size: 12px;">Interest / Property Subject</label>
                                    <select name="data[Contact][subject]" id="inq_subject" class="form-select rounded-0 p-3 bg-white" style="border: 1px solid #c7c7c7;">
                                        <option value="Costa Del Sol & White Villas Apartments Booking">Costa Del Sol &amp; White Villas Apartments Booking</option>
                                        <option value="Installment & Payment Plans Inquiry">Installment &amp; Payment Plans Inquiry</option>
                                        <option value="Resort Ownership & High Yield Investment">Resort Ownership &amp; High Yield Investment</option>
                                        <option value="Commercial Plots & Shops Purchase">Commercial Plots &amp; Shops Purchase</option>
                                        <option value="General Consultation & Site Visit Request">General Consultation &amp; Site Visit Request</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label text-dark fw-bold small text-uppercase" style="font-size: 12px;">Message</label>
                                    <textarea name="data[Contact][message]" id="inq_message" rows="6" class="form-control rounded-0 p-3 bg-white" placeholder="Write your inquiry here..." style="border: 1px solid #c7c7c7;"></textarea>
                                </div>
                                <div class="col-12 text-center pt-3">
                                    <button type="submit" id="inqSubmitBtn" class="nectar-button" style="padding: 15px 35px !important; min-width: 200px; margin: 0 auto 10px; cursor: pointer; border: none;">
                                        <span class="btn-text"><?php echo h($contactBtn); ?></span>
                                        <span class="btn-loading d-none"><i class="fa fa-spinner fa-spin me-2"></i> Submitting...</span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

<?php elseif ($secKey === 'newsletter'): ?>
    <!-- 10. Newsletter & Accordion -->
    <?php if ($newsletterSectionEnabled): ?>
    <div class="py-4 text-white position-relative" style="background-color: #d11242; padding-top: 25px !important; padding-bottom: 25px !important;">
        <div class="r-container py-2">
            <div class="row align-items-center justify-content-between g-3">
                <div class="col-12 col-md-auto d-flex align-items-center">
                    <span class="me-3">
                        <svg role="presentation" version="1.1" xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 32 32" fill="#ffffff">
                            <path d="M16.724 27.461c-0.167 0-0.329-0.065-0.453-0.18l-6.196-5.708c-0.271-0.251-0.288-0.673-0.036-0.943 0.249-0.267 0.672-0.289 0.94-0.036l5.615 5.173 12.505-19.365-26.375 8.357 4.355 3.473c0.291 0.229 0.337 0.648 0.105 0.936-0.228 0.291-0.648 0.335-0.935 0.105l-5.331-4.255c-0.195-0.151-0.287-0.401-0.241-0.639 0.044-0.243 0.219-0.44 0.453-0.515l29.333-9.297c0.271-0.083 0.56 0.011 0.735 0.232 0.172 0.224 0.183 0.528 0.028 0.765l-13.94 21.589c-0.107 0.165-0.281 0.275-0.473 0.3-0.035 0.003-0.061 0.005-0.089 0.005zM6.621 26.009c-0.079 0-0.156-0.013-0.233-0.039-0.26-0.097-0.433-0.349-0.433-0.628v-6.635c0-0.368 0.3-0.667 0.667-0.667s0.667 0.299 0.667 0.667v4.844l2.7-3.125c0.243-0.28 0.664-0.307 0.94-0.068 0.279 0.241 0.307 0.661 0.069 0.94l-3.872 4.476c-0.128 0.153-0.313 0.235-0.504 0.235z"></path>
                        </svg>
                    </span>
                    <div>
                        <div class="h4 fw-bold text-white mb-0 text-uppercase" style="font-family: 'League Spartan', sans-serif;"><strong><?php echo h($newsTitle); ?></strong></div>
                        <p class="small text-white mb-0" style="opacity: 0.9;"><?php echo h($newsSubtitle); ?></p>
                    </div>
                </div>
                <div class="col-12 col-md-auto">
                    <a href="#contact-form" class="nectar-button see-through-white" style="min-width: 180px; font-size: 0.85rem; padding: 10px 24px !important;">
                        <span><?php echo h($newsBtn); ?></span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="py-4 position-relative" style="background-color: #f4f4f4; padding-top: 30px !important; padding-bottom: 30px !important;">
        <div class="r-container">
            <div class="accordion accordion-flush" id="accordionAboutResorts">
                <div class="accordion-item bg-transparent border-0">
                    <h3 class="accordion-header" id="headingOne">
                        <button class="accordion-button collapsed bg-transparent text-uppercase fw-bold shadow-none ps-0" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAbout" aria-expanded="false" style="font-family: 'League Spartan', sans-serif; color: #666; font-size: 15px; letter-spacing: 0.5px;">
                            <i class="fa fa-plus me-2 text-danger"></i> <?php echo h($accTitle); ?>
                        </button>
                    </h3>
                    <div id="collapseAbout" class="accordion-collapse collapse" data-bs-parent="#accordionAboutResorts">
                        <div class="accordion-body ps-0 text-muted small" style="line-height: 1.8;">
                            <?php echo $accContent; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

<?php
    endif;
endforeach;
?>

<script>
// Lead Inquiry AJAX Form Handling
document.addEventListener('DOMContentLoaded', function() {
    var form = document.getElementById('themeInquiryForm');
    var alertBox = document.getElementById('contactInquiryAlert');
    var submitBtn = document.getElementById('inqSubmitBtn');
    if (!form) return;

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        var nameInput = document.getElementById('inq_name');
        var phoneInput = document.getElementById('inq_phone');
        if (!nameInput.value.trim() || !phoneInput.value.trim()) {
            alertBox.className = 'alert alert-danger mb-4';
            alertBox.innerHTML = '<i class="fa fa-exclamation-triangle me-2"></i> Please provide your Name and Phone Number.';
            alertBox.style.display = 'block';
            return;
        }

        submitBtn.disabled = true;
        submitBtn.querySelector('.btn-text').classList.add('d-none');
        submitBtn.querySelector('.btn-loading').classList.remove('d-none');
        alertBox.style.display = 'none';

        var formData = new FormData(form);

        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(function(res) {
            return res.json();
        })
        .then(function(data) {
            submitBtn.disabled = false;
            submitBtn.querySelector('.btn-text').classList.remove('d-none');
            submitBtn.querySelector('.btn-loading').classList.add('d-none');

            if (data && data.status === 'success') {
                alertBox.className = 'alert alert-success mb-4';
                alertBox.innerHTML = '<i class="fa fa-check-circle me-2"></i> <strong>Success!</strong> ' + (data.message || 'Your inquiry has been submitted successfully.');
                alertBox.style.display = 'block';
                form.reset();
            } else {
                alertBox.className = 'alert alert-danger mb-4';
                alertBox.innerHTML = '<i class="fa fa-times-circle me-2"></i> ' + (data.message || 'Submission failed. Please try again.');
                alertBox.style.display = 'block';
            }
        })
        .catch(function(err) {
            submitBtn.disabled = false;
            submitBtn.querySelector('.btn-text').classList.remove('d-none');
            submitBtn.querySelector('.btn-loading').classList.add('d-none');

            alertBox.className = 'alert alert-success mb-4';
            alertBox.innerHTML = '<i class="fa fa-check-circle me-2"></i> <strong>Thank you!</strong> Your inquiry has been sent to our sales team.';
            alertBox.style.display = 'block';
            form.reset();
        });
    });
});
</script>



<!-- Lead Capture & PDF Download Modal (Ultra-Clean, Mobile-Optimized & Instant Autofill) -->
<style>
#leadDownloadModal .modal-dialog {
    max-width: 480px;
    margin: 1.75rem auto;
}
#leadDownloadModal .modal-content {
    border: none;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 25px 50px -12px rgba(7, 21, 42, 0.35);
    background: #ffffff;
}
#leadDownloadModal .modal-header {
    background: linear-gradient(135deg, #07152a 0%, #162c4b 100%);
    border-bottom: 3px solid #d11242;
    padding: 20px 24px;
    position: relative;
}
#leadDownloadModal .modal-title {
    font-family: 'League Spartan', sans-serif;
    font-size: 20px;
    font-weight: 800;
    letter-spacing: 0.04em;
    color: #ffffff;
    margin: 0;
    line-height: 1.2;
}
#leadDownloadModal .modal-subtitle {
    font-size: 13px;
    color: #94a3b8;
    margin-top: 4px;
    font-weight: 500;
}
#leadDownloadModal .btn-close-custom {
    background: rgba(255, 255, 255, 0.12);
    border: none;
    color: #ffffff;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.2s ease;
}
#leadDownloadModal .btn-close-custom:hover {
    background: #d11242;
    transform: rotate(90deg);
}
#leadDownloadModal .modal-body {
    padding: 24px;
    background: #f8fafc;
}
#leadDownloadModal .form-group-custom {
    margin-bottom: 18px;
}
#leadDownloadModal .form-label-custom {
    font-family: 'League Spartan', sans-serif;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #334155;
    margin-bottom: 7px;
    display: block;
}
#leadDownloadModal .form-control-custom {
    width: 100%;
    height: 48px;
    background: #ffffff;
    border: 1.5px solid #cbd5e1;
    border-radius: 10px;
    padding: 10px 14px;
    font-size: 15px;
    color: #0f172a;
    font-weight: 600;
    transition: all 0.2s ease;
    -webkit-appearance: none;
}
#leadDownloadModal .form-control-custom:focus {
    border-color: #d11242;
    background: #ffffff;
    box-shadow: 0 0 0 3px rgba(209, 18, 66, 0.15);
    outline: none;
}
#leadDownloadModal .input-group-custom {
    position: relative;
    display: flex;
    align-items: center;
}
#leadDownloadModal .input-icon {
    position: absolute;
    left: 14px;
    color: #25D366;
    font-size: 18px;
    pointer-events: none;
    z-index: 5;
}
#leadDownloadModal .input-group-custom .form-control-custom {
    padding-left: 44px;
}
#leadDownloadModal .privacy-note {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 10px 12px;
    font-size: 12px;
    color: #64748b;
    display: flex;
    align-items: center;
    gap: 8px;
    line-height: 1.4;
}
#leadDownloadModal .modal-footer-custom {
    background: #ffffff;
    padding: 18px 24px 24px;
    border-top: 1px solid #f1f5f9;
    display: flex;
    gap: 12px;
    align-items: center;
}
#leadDownloadModal .btn-download-action {
    flex: 1;
    height: 48px;
    background: #d11242;
    color: #ffffff;
    border: none;
    border-radius: 10px;
    font-family: 'League Spartan', sans-serif;
    font-size: 14px;
    font-weight: 800;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    cursor: pointer;
    box-shadow: 0 4px 14px rgba(209, 18, 66, 0.35);
    transition: all 0.2s ease;
}
#leadDownloadModal .btn-download-action:hover {
    background: #b50e38;
    transform: translateY(-1px);
    box-shadow: 0 6px 18px rgba(209, 18, 66, 0.45);
}
#leadDownloadModal .btn-cancel-action {
    height: 48px;
    padding: 0 20px;
    background: #f1f5f9;
    color: #475569;
    border: none;
    border-radius: 10px;
    font-family: 'League Spartan', sans-serif;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s ease;
}
#leadDownloadModal .btn-cancel-action:hover {
    background: #e2e8f0;
    color: #0f172a;
}
#leadDownloadModal .autofill-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    background: #ecfdf5;
    color: #059669;
    border: 1px solid #a7f3d0;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 700;
    padding: 3px 8px;
    margin-bottom: 12px;
}
@media (max-width: 575px) {
    #leadDownloadModal .modal-dialog {
        margin: 0.5rem;
        max-width: 100%;
    }
    #leadDownloadModal .modal-content {
        border-radius: 14px;
    }
    #leadDownloadModal .modal-header {
        padding: 16px 18px;
    }
    #leadDownloadModal .modal-title {
        font-size: 18px;
    }
    #leadDownloadModal .modal-body {
        padding: 18px 16px;
    }
    #leadDownloadModal .modal-footer-custom {
        padding: 14px 16px 18px;
        flex-direction: column-reverse;
    }
    #leadDownloadModal .btn-download-action,
    #leadDownloadModal .btn-cancel-action {
        width: 100%;
        flex: none;
    }
}
</style>

<div class="modal fade" id="leadDownloadModal" tabindex="-1" aria-labelledby="leadDownloadModalLabel" aria-hidden="true" style="z-index: 1080;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="leadDownloadModalLabel">
                        <i class="fa fa-file-pdf-o text-danger me-1"></i> <?php echo __('Instant Payment Plan Download'); ?>
                    </h5>
                    <div class="modal-subtitle" id="modalPropertySubTitle"><?php echo __('Official Brochure & Payment Schedule'); ?></div>
                </div>
                <button type="button" class="btn-close-custom" data-bs-dismiss="modal" aria-label="Close">
                    <i class="fa fa-times"></i>
                </button>
            </div>
            <form id="leadBrochureForm" action="<?php echo $this->Html->url('/fronts/download_brochure'); ?>" method="POST" autocomplete="on">
                <input type="hidden" name="property_id" id="modalPropertyId" value="">
                <input type="hidden" name="utm_source" id="leadUtmSource" value="">
                <input type="hidden" name="utm_campaign" id="leadUtmCampaign" value="">
                <input type="hidden" name="fbclid" id="leadFbclid" value="">

                <div class="modal-body">
                    <!-- Quick Autofill indicator badge -->
                    <div id="autofillNoticeBadge" class="autofill-badge" style="display: none;">
                        <i class="fa fa-check-circle"></i> <?php echo __('Auto-filled from saved details'); ?>
                    </div>

                    <div id="leadModalAlert" class="alert alert-danger mb-3" style="display: none; font-size: 13px; border-radius: 8px;"></div>

                    <div class="form-group-custom">
                        <label class="form-label-custom" for="leadName"><?php echo __('Your Full Name'); ?> <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="leadName" class="form-control-custom" placeholder="e.g. Muhammad Ali" required autocomplete="name given-name family-name" autocapitalize="words" inputmode="text" spellcheck="false">
                    </div>

                    <div class="form-group-custom">
                        <label class="form-label-custom" for="leadMobile"><?php echo __('WhatsApp / Mobile Number'); ?> <span class="text-danger">*</span></label>
                        <div class="input-group-custom">
                            <span class="input-icon"><i class="fa fa-whatsapp"></i></span>
                            <input type="tel" name="mobile" id="leadMobile" class="form-control-custom" placeholder="e.g. +92 300 1234567" required autocomplete="tel tel-national" inputmode="tel">
                        </div>
                    </div>

                    <div class="privacy-note">
                        <i class="fa fa-lock text-success" style="font-size: 14px;"></i>
                        <span><?php echo __('Your details are secure. Official payment schedule PDF downloads instantly.'); ?></span>
                    </div>
                </div>

                <div class="modal-footer-custom">
                    <button type="button" class="btn-cancel-action" data-bs-dismiss="modal"><?php echo __('Close'); ?></button>
                    <button type="submit" id="btnDownloadSubmit" class="btn-download-action">
                        <span class="btn-text"><i class="fa fa-download"></i> <?php echo __('Download PDF Now'); ?></span>
                        <span class="btn-loading d-none"><i class="fa fa-spinner fa-spin"></i> <?php echo __('Preparing Download...'); ?></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function() {
    // Lead Storage & Cross-Form Autofill Manager
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

    document.addEventListener('DOMContentLoaded', function() {
        var nameInput = document.getElementById('leadName');
        var mobileInput = document.getElementById('leadMobile');
        var utmSourceInput = document.getElementById('leadUtmSource');
        var utmCampaignInput = document.getElementById('leadUtmCampaign');
        var fbclidInput = document.getElementById('leadFbclid');
        var autofillBadge = document.getElementById('autofillNoticeBadge');

        // Capture URL ad data (Facebook Lead Ads / Google UTMs)
        var urlData = WVLeadMemory.fromURL();
        if (utmSourceInput && urlData.utm_source) utmSourceInput.value = urlData.utm_source;
        if (utmCampaignInput && urlData.utm_campaign) utmCampaignInput.value = urlData.utm_campaign;
        if (fbclidInput && urlData.fbclid) fbclidInput.value = urlData.fbclid;

        // Auto-fill inputs from URL or localStorage
        function applyAutoFill() {
            var saved = WVLeadMemory.get();
            var filled = false;
            if (nameInput && !nameInput.value && (urlData.name || saved.name)) {
                nameInput.value = urlData.name || saved.name;
                filled = true;
            }
            if (mobileInput && !mobileInput.value && (urlData.phone || saved.phone)) {
                mobileInput.value = urlData.phone || saved.phone;
                filled = true;
            }
            if (autofillBadge) {
                autofillBadge.style.display = (nameInput && nameInput.value && mobileInput && mobileInput.value) ? 'inline-flex' : 'none';
            }
        }
        applyAutoFill();

        // 1. Setup modal trigger on download button click
        var downloadBtns = document.querySelectorAll('.r-btn-download-plan');
        var modalPropId = document.getElementById('modalPropertyId');
        var modalPropSubtitle = document.getElementById('modalPropertySubTitle');

        downloadBtns.forEach(function(btn) {
            btn.addEventListener('click', function() {
                var propId = this.getAttribute('data-property-id') || '';
                var propName = this.getAttribute('data-property-name') || '';
                var propTitle = this.getAttribute('data-property-title') || propName;
                if (modalPropId) modalPropId.value = propId;
                if (modalPropSubtitle) {
                    modalPropSubtitle.textContent = propTitle ? ('Official Plan for: ' + propTitle) : ('Official Plan for: ' + propName);
                }
                applyAutoFill();
            });
        });

        // Sync inputs from bottom consultation form to memory
        var inqName = document.getElementById('inq_name');
        var inqPhone = document.getElementById('inq_phone');
        if (inqName) {
            var saved = WVLeadMemory.get();
            if (!inqName.value && (urlData.name || saved.name)) inqName.value = urlData.name || saved.name;
            inqName.addEventListener('change', function() { WVLeadMemory.save(this.value, inqPhone ? inqPhone.value : ''); });
        }
        if (inqPhone) {
            var saved = WVLeadMemory.get();
            if (!inqPhone.value && (urlData.phone || saved.phone)) inqPhone.value = urlData.phone || saved.phone;
            inqPhone.addEventListener('change', function() { WVLeadMemory.save(inqName ? inqName.value : '', this.value); });
        }

        // 2. Handle Lead Form AJAX submission & instant download
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
                        leadAlert.className = 'alert alert-danger mb-3';
                        leadAlert.innerHTML = '<i class="fa fa-exclamation-triangle me-2"></i> Please provide your Name and WhatsApp/Mobile number.';
                        leadAlert.style.display = 'block';
                    }
                    return;
                }

                // Save to memory for future instant 1-tap usage
                WVLeadMemory.save(nameVal, mobileVal);

                if (btnSubmit) {
                    btnSubmit.disabled = true;
                    btnSubmit.querySelector('.btn-text').classList.add('d-none');
                    btnSubmit.querySelector('.btn-loading').classList.remove('d-none');
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
                        btnSubmit.querySelector('.btn-text').classList.remove('d-none');
                        btnSubmit.querySelector('.btn-loading').classList.add('d-none');
                    }

                    if (data && data.status === 'success') {
                        if (data.has_file && data.download_url) {
                            if (leadAlert) {
                                leadAlert.className = 'alert alert-success mb-3';
                                leadAlert.innerHTML = '<i class="fa fa-check-circle me-2"></i> ' + (data.message || 'Payment plan downloading...');
                                leadAlert.style.display = 'block';
                            }

                            // Trigger instant file download with clean file name
                            var downloadLink = document.createElement('a');
                            downloadLink.href = data.download_url;
                            downloadLink.target = '_blank';
                            var cleanName = data.download_filename || ((data.property_name ? data.property_name.replace(/[^a-z0-9]/gi, '_') : 'Payment_Plan') + '.pdf');
                            downloadLink.download = cleanName;
                            document.body.appendChild(downloadLink);
                            downloadLink.click();
                            document.body.removeChild(downloadLink);

                            setTimeout(function() {
                                var modalEl = document.getElementById('leadDownloadModal');
                                if (modalEl && typeof bootstrap !== 'undefined') {
                                    var modalObj = bootstrap.Modal.getInstance(modalEl);
                                    if (modalObj) modalObj.hide();
                                }
                            }, 2500);
                        } else {
                            if (leadAlert) {
                                leadAlert.className = 'alert alert-info mb-3';
                                leadAlert.innerHTML = '<i class="fa fa-info-circle me-2"></i> <strong>Latest Payment Plan Not Available</strong><div style="font-size:12px;margin-top:4px;">' + (data.message || 'Our sales team has received your request and will contact you shortly.') + '</div>';
                                leadAlert.style.display = 'block';
                            }

                            setTimeout(function() {
                                var modalEl = document.getElementById('leadDownloadModal');
                                if (modalEl && typeof bootstrap !== 'undefined') {
                                    var modalObj = bootstrap.Modal.getInstance(modalEl);
                                    if (modalObj) modalObj.hide();
                                }
                            }, 4500);
                        }
                    } else {
                        if (leadAlert) {
                            leadAlert.className = 'alert alert-danger mb-3';
                            leadAlert.innerHTML = '<i class="fa fa-times-circle me-2"></i> ' + (data.message || 'Download failed. Please try again.');
                            leadAlert.style.display = 'block';
                        }
                    }
                })
                .catch(function(err) {
                    if (btnSubmit) {
                        btnSubmit.disabled = false;
                        btnSubmit.querySelector('.btn-text').classList.remove('d-none');
                        btnSubmit.querySelector('.btn-loading').classList.add('d-none');
                    }
                    if (leadAlert) {
                        leadAlert.className = 'alert alert-success mb-3';
                        leadAlert.innerHTML = '<i class="fa fa-check-circle me-2"></i> Thank you! Your download will begin shortly.';
                        leadAlert.style.display = 'block';
                    }
                });
            });
        }
    });
})();
</script>