<?php
$photoUrl=$this->Html->url(array('controller'=>'img'));
$tickerEnabled = isset($featuredTickerEnabled) ? (int)$featuredTickerEnabled : 0;
$tickerSpeed = isset($featuredTickerSpeed) ? (int)$featuredTickerSpeed : 35;
if($tickerSpeed < 10){ $tickerSpeed = 10; }
if($tickerSpeed > 180){ $tickerSpeed = 180; }
$tickerEffect = isset($featuredTickerEffect) ? strtolower(trim((string)$featuredTickerEffect)) : 'linear';
if(!in_array($tickerEffect,array('linear','ease','ease-in-out'))){ $tickerEffect = 'linear'; }
$tickerItems = !empty($featuredTickerProperties) ? $featuredTickerProperties : array();
if(empty($tickerItems) && !empty($allviewedProperty))
{
    $tickerItems = $allviewedProperty;
}
$homeSearchBaseUrl = $this->Html->url('/Properties/index');
$publishedPropertyCount = isset($publishedPropertyCount) ? (int)$publishedPropertyCount : count((array)$allviewedProperty);
$publishedSaleProperties = array();
$publishedRentProperties = array();
foreach((array)$allviewedProperty as $publishedPropertyRow)
{
    $contractNameForSplit = !empty($publishedPropertyRow['Contract']['name']) ? strtolower(trim((string)$publishedPropertyRow['Contract']['name'])) : '';
    if(strpos($contractNameForSplit, 'rent') !== false || strpos($contractNameForSplit, 'lease') !== false)
    {
        $publishedRentProperties[] = $publishedPropertyRow;
    }
    else
    {
        $publishedSaleProperties[] = $publishedPropertyRow;
    }
}
$formatHomePublishedPrice = function($price, $suffix = ''){
    $price = (float)$price;
    if($price >= 10000000)
    {
        return 'PKR '.number_format($price / 10000000, 2).' Crore'.$suffix;
    }
    if($price >= 100000)
    {
        return 'PKR '.number_format($price / 100000, 2).' Lac'.$suffix;
    }
    return 'PKR '.number_format($price).$suffix;
};
$homeCategoryFilters = array(array('id' => 0, 'name' => __('All')));
foreach((array)$propertyType as $homeCategoryFilterRow)
{
    if(empty($homeCategoryFilterRow['Type']['id']))
    {
        continue;
    }
    $homeCategoryFilters[] = array(
        'id' => (int)$homeCategoryFilterRow['Type']['id'],
        'name' => !empty($homeCategoryFilterRow['Type']['name']) ? (string)$homeCategoryFilterRow['Type']['name'] : __('Property')
    );
    if(count($homeCategoryFilters) >= 6)
    {
        break;
    }
}
?>
<style>
.home-hero-panel{
    position:absolute;
    left:0;
    right:0;
    top:62%;
    transform:translateY(-50%);
    z-index:20;
    color:#fff;
}
.home-hero-content{
    max-width:780px;
    margin-left:auto;
    margin-right:auto;
    text-align:center;
    text-shadow:0 2px 12px rgba(0,0,0,.38);
}
.home-hero-eyebrow{
    display:inline-block;
    background:rgba(255,255,255,.14);
    border:1px solid rgba(255,255,255,.24);
    border-radius:18px;
    color:#fff;
    font-size:10px;
    font-weight:800;
    letter-spacing:1.6px;
    padding:4px 10px;
    text-transform:uppercase;
}
.home-hero-title{
    color:#fff;
    font-size:36px;
    font-weight:900;
    line-height:1.12;
    margin:10px 0 7px 0;
}
.home-hero-copy{
    color:#e7edf6;
    font-size:14px;
    line-height:1.5;
    margin:0 0 12px 0;
    max-width:590px;
    margin-left:auto;
    margin-right:auto;
}
.home-search-panel{
    background:rgba(255,255,255,.96);
    border:1px solid rgba(226,232,240,.95);
    border-radius:8px;
    box-shadow:0 16px 36px rgba(15,23,42,.28);
    padding:10px;
    text-shadow:none;
    margin-left:auto;
    margin-right:auto;
    max-width:700px;
}
.home-search-panel .form-control{
    border-color:#d8e0ea;
    box-shadow:none;
    height:38px;
}
.home-search-panel .btn{
    height:38px;
    font-weight:800;
}
.home-trust-strip{
    background:linear-gradient(135deg,#07152a 0%,#101c35 72%,#ff0a46 165%);
    border:1px solid rgba(124,249,255,.16);
    border-radius:12px;
    box-shadow:0 18px 42px rgba(15,23,42,.22);
    color:#fff;
    margin:-40px auto 34px;
    max-width:1120px;
    overflow:hidden;
    padding:18px 20px;
    position:relative;
    text-align:center;
    z-index:30;
}
.home-trust-strip:before{
    background:linear-gradient(90deg,transparent,rgba(124,249,255,.18),transparent);
    content:"";
    height:1px;
    left:7%;
    position:absolute;
    right:7%;
    top:0;
}
.home-trust-row{
    align-items:stretch;
    display:flex;
    justify-content:center;
    margin-left:0;
    margin-right:0;
}
.home-trust-row:before,
.home-trust-row:after{
    display:none;
}
.home-trust-item{
    border-right:1px solid rgba(255,255,255,.12);
    display:flex;
    flex-direction:column;
    justify-content:center;
    min-height:74px;
    padding:8px 18px;
    text-align:center;
}
.home-trust-item:last-child{
    border-right:0;
}
.home-trust-value{
    color:#7cf9ff;
    display:block;
    font-size:38px;
    font-weight:900;
    line-height:1;
    text-shadow:0 0 18px rgba(124,249,255,.34);
    transform:translateY(0);
    animation:homeTrustPop .72s ease both;
}
.home-trust-label{
    color:#eef6ff;
    font-size:13px;
    font-weight:900;
    letter-spacing:.35px;
    margin-top:9px;
    text-transform:uppercase;
}
@keyframes homeTrustPop{
    0%{opacity:0;transform:translateY(9px) scale(.94);}
    100%{opacity:1;transform:translateY(0) scale(1);}
}
.home-section-actions{
    margin-top:8px;
}
.home-section-actions .btn{
    border-radius:4px;
    font-weight:800;
}
.front-home-section{
    margin:34px 0 28px 0;
}
.front-home-section .page-header{
    border-bottom:1px solid #e8edf3;
    margin:0 0 22px 0;
    padding-bottom:14px;
}
.front-home-section .page-header h1,
.front-home-section .page-header h2{
    color:#162235;
    font-weight:800;
    letter-spacing:0;
    margin:0;
}
.front-home-section .page-header p{
    color:#687386;
    font-size:14px;
    margin:7px 0 0 0;
}
.published-property-section{
    background:#ffffff;
    border:0;
    border-radius:0;
    box-shadow:none;
    padding:0;
}
.published-property-section .listing-box-wrapper{
    margin-top:0;
}
.verified-property-block{
    margin:0 0 70px 0;
}
.verified-property-head{
    display:flex;
    align-items:flex-end;
    justify-content:space-between;
    gap:18px;
    margin-bottom:22px;
}
.verified-property-title h1,
.verified-property-title h2{
    color:#020817;
    font-size:27px;
    font-weight:900;
    line-height:1.15;
    margin:0 0 8px 0;
}
.verified-property-title p{
    color:#42526b;
    font-size:13px;
    line-height:1.45;
    margin:0;
    max-width:560px;
}
.verified-property-actions{
    display:flex;
    align-items:center;
    gap:12px;
    flex-wrap:wrap;
    justify-content:flex-end;
}
.verified-filter-pills{
    align-items:center;
    background:#ffffff;
    border:1px solid #dce5ef;
    border-radius:24px;
    box-shadow:0 5px 14px rgba(15,23,42,.06);
    display:flex;
    gap:4px;
    padding:4px;
}
.verified-filter-pills span{
    border-radius:18px;
    color:#193255;
    display:inline-block;
    font-size:10px;
    font-weight:900;
    min-width:68px;
    padding:8px 13px;
    text-align:center;
    text-transform:uppercase;
}
.verified-filter-pills a{
    border-radius:18px;
    color:#193255;
    display:inline-block;
    font-size:10px;
    font-weight:900;
    min-width:68px;
    padding:8px 13px;
    text-align:center;
    text-decoration:none !important;
    text-transform:uppercase;
}
.verified-filter-pills span.active{
    background:#ff0a46;
    color:#ffffff;
}
.verified-filter-pills a.active{
    background:#ff0a46;
    color:#ffffff;
}
.verified-filter-pills a:hover{
    background:#fff1f4;
    color:#ff0a46;
}
.verified-view-all{
    background:#081225;
    border-radius:24px;
    color:#ffffff !important;
    display:inline-flex;
    align-items:center;
    gap:12px;
    font-size:12px;
    font-weight:900;
    padding:12px 17px;
    text-decoration:none !important;
}
.verified-view-all .fa{
    background:rgba(255,255,255,.16);
    border-radius:50%;
    height:22px;
    line-height:22px;
    text-align:center;
    width:22px;
}
.verified-property-grid{
    display:grid;
    grid-template-columns:repeat(4, minmax(0, 1fr));
    gap:18px;
}
.verified-property-card{
    background:#ffffff;
    border:1px solid #f4a400;
    border-radius:8px;
    box-shadow:0 10px 24px rgba(15,23,42,.08);
    overflow:hidden;
}
.verified-property-media{
    background:#dbe3ec;
    height:122px;
    position:relative;
}
.verified-property-media a{
    display:block;
    height:100%;
}
.verified-property-media-img{
    background-position:center;
    background-size:cover;
    height:100%;
}
.verified-badge{
    border-radius:15px;
    color:#ffffff;
    font-size:10px;
    font-weight:900;
    left:10px;
    padding:5px 10px;
    position:absolute;
    top:9px;
}
.verified-badge.gold{
    background:#f5a400;
}
.verified-badge.green{
    background:#10b981;
}
.verified-photo-count{
    background:#172033;
    border-radius:15px;
    color:#ffffff;
    font-size:10px;
    font-weight:800;
    padding:5px 9px;
    position:absolute;
    right:9px;
    top:9px;
}
.verified-card-price{
    background:linear-gradient(135deg,#ff0a46,#b00035);
    border-radius:999px;
    bottom:10px;
    box-shadow:0 10px 22px rgba(176,0,53,.28);
    color:#ffffff;
    font-size:16px;
    font-weight:900;
    left:12px;
    line-height:1;
    max-width:calc(100% - 24px);
    overflow:hidden;
    padding:9px 14px;
    position:absolute;
    text-overflow:ellipsis;
    text-shadow:none;
    white-space:nowrap;
}
.verified-property-body{
    padding:12px 12px 10px 12px;
}
.verified-property-name{
    color:#07152a;
    display:block;
    font-size:13px;
    font-weight:800;
    line-height:1.35;
    min-height:36px;
    text-decoration:none !important;
}
.verified-property-name:hover{
    color:#ff0a46;
}
.verified-property-specs{
    color:#617089;
    display:flex;
    flex-wrap:wrap;
    gap:12px;
    font-size:11px;
    margin:11px 0 12px 0;
}
.verified-property-specs strong{
    color:#193255;
    font-weight:900;
}
.verified-card-footer{
    align-items:center;
    border-top:1px solid #eef2f7;
    display:flex;
    gap:9px;
    padding-top:10px;
}
.verified-owner-icon{
    background:#ef1717;
    border-radius:50%;
    color:#fff;
    height:24px;
    line-height:24px;
    text-align:center;
    width:24px;
}
.verified-owner-name{
    color:#07152a;
    font-size:11px;
    font-weight:900;
    line-height:1.1;
}
.verified-owner-name span{
    color:#ff0a46;
    font-size:8px;
    margin-left:2px;
    text-transform:uppercase;
}
.verified-owner-commission{
    color:#059669;
    font-size:9px;
    font-weight:700;
    margin-top:2px;
}
.verified-card-arrow{
    background:#fff1f4;
    border-radius:50%;
    color:#ff0a46;
    height:26px;
    line-height:26px;
    margin-left:auto;
    text-align:center;
    text-decoration:none !important;
    width:26px;
}
.verified-empty{
    background:#f8fafc;
    border:1px dashed #cbd5e1;
    border-radius:8px;
    color:#64748b;
    font-weight:700;
    padding:24px;
}
.portal-category-grid{
    display:grid;
    grid-template-columns:repeat(4, minmax(0, 1fr));
    gap:18px;
}
.portal-category-card{
    background:#ffffff;
    border:1px solid #e2e8f0;
    border-radius:8px;
    box-shadow:0 10px 24px rgba(15,23,42,.08);
    min-height:210px;
    overflow:hidden;
    position:relative;
}
.portal-category-card-bg{
    background-position:center;
    background-size:cover;
    height:124px;
    position:relative;
}
.portal-category-card-bg:after{
    background:linear-gradient(180deg, rgba(2,8,23,0) 0%, rgba(2,8,23,.72) 100%);
    bottom:0;
    content:"";
    left:0;
    position:absolute;
    right:0;
    top:0;
}
.portal-category-count{
    background:#081225;
    border-radius:15px;
    color:#ffffff;
    font-size:10px;
    font-weight:900;
    padding:5px 10px;
    position:absolute;
    right:10px;
    top:10px;
    z-index:2;
}
.portal-category-title{
    bottom:12px;
    color:#ffffff;
    font-size:18px;
    font-weight:900;
    left:12px;
    position:absolute;
    right:12px;
    text-shadow:0 2px 8px rgba(0,0,0,.5);
    z-index:2;
}
.portal-category-body{
    padding:12px;
}
.portal-category-body p{
    color:#64748b;
    font-size:12px;
    line-height:1.45;
    margin:0 0 12px 0;
    min-height:34px;
}
.portal-category-link{
    align-items:center;
    color:#ff0a46 !important;
    display:flex;
    font-size:12px;
    font-weight:900;
    justify-content:space-between;
    text-decoration:none !important;
}
.portal-category-link .fa{
    background:#fff1f4;
    border-radius:50%;
    height:26px;
    line-height:26px;
    text-align:center;
    width:26px;
}
.featured-home-section{
    background:linear-gradient(135deg,#07152a 0%,#0d1b2f 58%,#101827 100%);
    border:1px solid rgba(148,163,184,.22);
    border-radius:10px;
    box-shadow:0 20px 44px rgba(2,8,23,.24);
    margin-top:28px;
    overflow:hidden;
    padding:24px;
    position:relative;
}
.featured-home-section:before{
    background:linear-gradient(90deg,#ff0a46 0%,#7cf9ff 58%,rgba(124,249,255,0) 100%);
    content:"";
    height:3px;
    left:0;
    position:absolute;
    right:0;
    top:0;
}
.featured-home-section .page-header{
    align-items:flex-end;
    border-bottom:1px solid rgba(226,232,240,.14);
    display:flex;
    gap:18px;
    justify-content:space-between;
    margin-bottom:22px;
    padding-bottom:16px;
}
.featured-home-section .page-header h2{
    color:#ffffff;
    font-size:28px;
    font-weight:900;
    letter-spacing:0;
    margin-bottom:0;
}
.featured-home-section .page-header p{
    color:#b7c5d8;
    font-size:13px;
    line-height:1.5;
    max-width:520px;
}
.featured-home-section .home-section-actions{
    margin-top:0;
}
.featured-home-section .home-section-actions .btn{
    background:#ff0a46!important;
    border:0;
    border-radius:999px;
    box-shadow:0 12px 26px rgba(255,10,70,.28);
    color:#ffffff!important;
    font-size:12px;
    font-weight:900;
    padding:12px 18px;
}
.featured-home-section .listing-box-background{
    background:rgba(255,255,255,.045);
    border:1px solid rgba(148,163,184,.22);
    border-radius:10px;
    box-shadow:inset 0 1px 0 rgba(255,255,255,.06);
    padding:26px 24px;
}
.featured-home-section .listing-box{
    background:#ffffff;
    border:1px solid rgba(226,232,240,.95);
    border-radius:10px;
    box-shadow:0 16px 32px rgba(2,8,23,.24);
    overflow:hidden;
    transition:transform .18s ease, box-shadow .18s ease, border-color .18s ease;
}
.featured-home-section .listing-box:hover{
    border-color:rgba(255,10,70,.35);
    box-shadow:0 22px 40px rgba(2,8,23,.32);
    transform:translateY(-3px);
}
.featured-home-section .listing-box-image{
    border-bottom:1px solid #e7edf5;
    min-height:160px;
}
.featured-home-section .listing-box-image-label{
    background:#0ea66b !important;
    border-radius:999px;
    box-shadow:0 8px 18px rgba(14,166,107,.26);
    color:#ffffff;
    font-size:10px;
    font-weight:900;
    padding:6px 11px;
    text-transform:uppercase;
}
.featured-home-section .listing-box-title{
    background:#ffffff;
    border-bottom:1px solid #edf2f7;
    padding:16px 18px 14px 18px;
}
.featured-home-section .listing-box-title h2 a{
    color:#07152a;
    font-weight:900;
}
.featured-home-section .listing-box-title h2{
    font-size:16px;
    line-height:1.25;
    margin:0;
}
.featured-home-section .listing-box-title h3{
    background:linear-gradient(135deg,#ff0a46 0%,#c80738 100%);
    border-radius:999px;
    box-shadow:0 10px 18px rgba(255,10,70,.22);
    color:#ffffff;
    display:inline-block;
    font-size:15px;
    font-weight:900;
    margin:10px 0 0 0;
    padding:7px 12px;
}
.featured-home-section .listing-box-content{
    background:#ffffff;
    padding:14px 18px 18px 18px;
}
.featured-home-section .listing-box-content dt{
    color:#64748b;
    font-size:12px;
    font-weight:700;
}
.featured-home-section .listing-box-content dd{
    color:#07152a;
    font-weight:800;
}
.featured-home-section .listing-box-content dd:nth-of-type(3){
    color:#047857;
}
.featured-home-section .listing-carousel-wrapper .owl-prev,
.featured-home-section .listing-carousel-wrapper .owl-next{
    background:#ff0a46 !important;
    border:3px solid #ffffff!important;
    border-radius:50%;
    box-shadow:0 10px 24px rgba(2,8,23,.28);
    color:#ffffff !important;
}
.front-categories-section{
    margin-top:30px;
}
.front-categories-section .page-header{
    border-bottom:1px solid #e8edf3;
    display:flex;
    align-items:flex-end;
    justify-content:space-between;
    gap:18px;
    text-align:left;
}
.front-categories-section .page-header h1{
    color:#020817;
    font-size:27px;
    font-weight:900;
}
.front-categories-section .page-header p{
    color:#42526b;
    max-width:520px;
}
.front-categories-section .category{
    border-radius:8px;
    overflow:hidden;
    box-shadow:0 10px 24px rgba(15,23,42,.10);
    border:1px solid #e2e8f0;
    min-height:230px;
}
.front-categories-section .category:before{
    background:linear-gradient(180deg, rgba(2,8,23,.05) 0%, rgba(2,8,23,.82) 100%);
}
.front-categories-section .category-content{
    padding:22px;
}
.front-categories-section .category-title{
    color:#ffffff;
    font-size:23px;
    font-weight:900;
    text-shadow:0 2px 8px rgba(0,0,0,.45);
}
.front-categories-section .category-subtitle{
    color:#e2e8f0;
    font-size:12px;
    font-weight:800;
    letter-spacing:0;
}
.front-categories-section .category .btn{
    background:#ff0a46;
    border:0;
    border-radius:24px;
    color:#ffffff;
    font-size:12px;
    font-weight:900;
    padding:9px 16px;
}
.featured-property-ticker-wrap{
    background:linear-gradient(135deg,#081225 0%,#0f1d33 58%,#121827 100%);
    border:1px solid rgba(255,255,255,.12);
    border-top:3px solid #ff0a46;
    border-radius:10px;
    margin:0 auto;
    box-shadow:0 0 0 1px rgba(255,255,255,.04) inset, 0 18px 34px rgba(2,8,23,.36);
    max-width:1180px;
}
.featured-property-ticker-overlay{
    position:absolute;
    left:0;
    right:0;
    top:24px;
    z-index:25;
}
.cover.cover-center{
    position:relative;
    max-height:520px;
    overflow:hidden;
}
.cover.cover-center .cover-carousel,
.cover.cover-center .cover-carousel-item,
.cover.cover-center .cover-image{
    height:520px;
    min-height:520px;
}
.featured-property-ticker{
    display:block;
    padding:10px 12px 12px 12px;
    overflow:hidden;
}
.featured-property-ticker-header{
    display:flex;
    align-items:center;
    justify-content:flex-start;
    margin-bottom:7px;
}
.featured-property-ticker-title-row{
    display:flex;
    align-items:baseline;
    gap:10px;
    flex-wrap:wrap;
}
.featured-property-ticker-title{
    background:#ff0a46;
    border-radius:999px;
    color:#fff;
    font-weight:900;
    text-transform:uppercase;
    letter-spacing:1px;
    font-size:10px;
    line-height:1.2;
    padding:5px 11px;
}
.featured-property-ticker-sub{
    color:#cbd5e1;
    font-size:12px;
    font-weight:700;
    margin-top:0;
}
.featured-property-ticker-viewport{
    overflow:hidden;
    width:100%;
}
.featured-property-ticker-track{
    display:flex;
    align-items:center;
    white-space:nowrap;
    min-width:fit-content;
    animation-name:featuredTickerSlide;
    animation-iteration-count:infinite;
}
.featured-property-ticker-viewport:hover .featured-property-ticker-track{
    animation-play-state: paused;
}
.featured-property-ticker-item{
    background:linear-gradient(180deg,#ffffff 0%,#f8fbff 100%);
    border:1px solid rgba(226,232,240,.95);
    border-radius:9px;
    padding:10px 12px;
    margin-right:12px;
    min-width:350px;
    box-shadow:0 10px 22px rgba(2,8,23,.28);
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:8px;
    white-space:normal;
}
.featured-property-ticker-item-link{
    text-decoration:none !important;
    color:inherit !important;
    cursor:pointer;
    transition:border-color .2s ease, box-shadow .2s ease, transform .2s ease;
}
.featured-property-ticker-item-link:hover,
.featured-property-ticker-item-link:focus{
    border-color:#ff0a46;
    box-shadow:0 14px 28px rgba(2,8,23,.34);
    transform:translateY(-2px);
}
.featured-property-ticker-item-link:hover .featured-property-ticker-name,
.featured-property-ticker-item-link:focus .featured-property-ticker-name{
    color:#0b2f68;
}
.featured-property-ticker-item-link:hover .featured-property-ticker-cta,
.featured-property-ticker-item-link:focus .featured-property-ticker-cta{
    color:#0b2f68;
}
.featured-property-ticker-left{
    min-width:0;
    flex:1 1 auto;
}
.featured-property-ticker-tag{
    display:inline-block;
    border:1px solid #0b4d36;
    background:#0b4d36;
    border-color:#0b4d36;
    color:#ffffff;
    border-radius:999px;
    font-size:9px;
    font-weight:900;
    letter-spacing:1px;
    text-transform:uppercase;
    padding:4px 8px;
    margin-bottom:5px;
}
.featured-property-ticker-name{
    color:#1b1f2a;
    font-size:14px;
    font-weight:900;
    line-height:1.2;
    margin-bottom:2px;
    white-space:nowrap;
    overflow:hidden;
    text-overflow:ellipsis;
}
.featured-property-ticker-meta{
    color:#596579;
    font-size:11px;
    white-space:nowrap;
    overflow:hidden;
    text-overflow:ellipsis;
}
.featured-property-ticker-right{
    flex:0 0 auto;
    text-align:right;
}
.featured-property-ticker-price{
    color:#0b2f68;
    font-family:"Trebuchet MS","Segoe UI",Arial,sans-serif;
    font-weight:900;
    font-size:18px;
    line-height:1.2;
    margin-bottom:3px;
}
.featured-property-ticker-cta{
    background:#fff1f4;
    border-radius:999px;
    color:#ff0a46;
    text-transform:uppercase;
    letter-spacing:1px;
    font-size:9px;
    font-weight:900;
    padding:4px 8px;
    display:inline-block;
}
@keyframes featuredTickerSlide{
    0%{ transform:translateX(0); }
    100%{ transform:translateX(-50%); }
}
@media (max-width:767px){
    .cover.cover-center{
        max-height:none;
    }
    .cover.cover-center .cover-carousel,
    .cover.cover-center .cover-carousel-item,
    .cover.cover-center .cover-image{
        height:260px;
        min-height:260px;
    }
    .home-hero-panel{
        position:relative;
        top:auto;
        transform:none;
        background:#0f172a;
        padding:14px 0;
    }
    .home-hero-title{
        font-size:24px;
    }
    .home-hero-copy{
        font-size:14px;
    }
    .home-search-panel .form-control,
    .home-search-panel .btn{
        margin-bottom:8px;
    }
    .home-trust-strip{
        margin:18px 0 24px 0;
    }
    .home-trust-item{
        border-right:0;
        border-bottom:1px solid rgba(255,255,255,.12);
    }
    .home-trust-item:last-child{
        border-bottom:0;
    }
    .verified-property-head{
        display:block;
    }
    .verified-property-actions{
        justify-content:flex-start;
        margin-top:14px;
    }
    .verified-filter-pills{
        overflow-x:auto;
        width:100%;
    }
    .verified-property-grid{
        grid-template-columns:1fr;
    }
    .portal-category-grid{
        grid-template-columns:1fr;
    }
    .featured-home-section .page-header,
    .front-categories-section .page-header{
        display:block;
    }
    .featured-home-section .home-section-actions{
        margin-top:12px;
    }
    .featured-property-ticker-wrap{ border-radius:10px; margin-top:8px; max-width:100%; }
    .featured-property-ticker{ padding:8px; }
    .featured-property-ticker-title-row{ gap:8px; }
    .featured-property-ticker-title{ font-size:12px; letter-spacing:2px; }
    .featured-property-ticker-sub{ font-size:11px; }
    .featured-property-ticker-item{ min-width:300px; padding:9px 10px; margin-right:8px; }
    .featured-property-ticker-name{ font-size:13px; }
    .featured-property-ticker-meta{ font-size:11px; }
    .featured-property-ticker-price{ font-size:17px; }
    .featured-property-ticker-overlay{ top:82px; }
}

/* Enterprise home refinement: tighter rhythm, cleaner section surfaces, fewer empty bands. */
.main-wrapper,
.main-wrapper .main,
.main-wrapper .main-inner,
.main-wrapper .content{
    background:#f4f7fb;
}
.main-wrapper .container{
    max-width:1180px;
}
.cover.cover-center{
    background:#07152a;
    max-height:455px;
}
.cover.cover-center .cover-carousel,
.cover.cover-center .cover-carousel-item,
.cover.cover-center .cover-image{
    height:455px;
    min-height:455px;
}
.cover.cover-center .cover-image{
    position:relative;
}
.cover.cover-center .cover-image:after{
    background:linear-gradient(180deg,rgba(7,21,42,.64) 0%,rgba(7,21,42,.24) 42%,rgba(7,21,42,.74) 100%);
    content:"";
    inset:0;
    position:absolute;
}
.featured-property-ticker-overlay{
    top:12px;
}
.featured-property-ticker-wrap{
    border-radius:14px;
    max-width:1250px;
}
.featured-property-ticker{
    padding:9px 12px 10px;
}
.featured-property-ticker-item{
    min-width:330px;
    padding:9px 12px;
}
.home-hero-panel{
    top:58%;
}
.home-hero-title{
    font-size:34px;
    margin:8px 0 6px;
}
.home-hero-copy{
    margin-bottom:10px;
}
.home-search-panel{
    border-radius:12px;
    max-width:760px;
    padding:9px;
}
.home-search-panel .row{
    margin-left:-5px;
    margin-right:-5px;
}
.home-search-panel [class*="col-"]{
    padding-left:5px;
    padding-right:5px;
}
.home-search-panel .form-control,
.home-search-panel .btn{
    border-radius:9px;
}
.home-trust-strip{
    border:1px solid rgba(255,255,255,.08);
    border-radius:14px;
    margin:-24px 0 22px;
    padding:13px 16px;
}
.home-trust-item{
    min-height:50px;
    padding:4px 16px;
}
.home-trust-value{
    font-size:23px;
}
.home-trust-label{
    font-size:11px;
    margin-top:5px;
}
.front-home-section{
    margin:22px 0;
}
.front-home-section .page-header{
    margin-bottom:16px;
    padding-bottom:12px;
}
.published-property-section{
    background:transparent;
}
.verified-property-block{
    margin:0 0 34px;
}
.verified-property-head{
    align-items:center;
    margin-bottom:16px;
}
.verified-property-title h1,
.verified-property-title h2{
    font-size:25px;
    margin-bottom:6px;
}
.verified-property-title p{
    color:#526174;
    font-size:13px;
}
.verified-filter-pills{
    border-radius:999px;
}
.verified-filter-pills a,
.verified-filter-pills span{
    min-width:auto;
    padding:8px 12px;
}
.verified-property-grid,
.portal-category-grid{
    gap:16px;
}
.verified-property-card,
.portal-category-card{
    border-radius:12px;
    box-shadow:0 12px 26px rgba(15,23,42,.08);
}
.verified-property-media{
    height:136px;
}
.verified-property-body{
    padding:12px;
}
.verified-property-specs{
    gap:10px;
    margin:10px 0 11px;
}
.portal-category-card{
    min-height:196px;
}
.portal-category-card-bg{
    height:116px;
}
.portal-category-body{
    padding:11px 12px 12px;
}
.portal-category-body p{
    min-height:0;
}
.featured-home-section{
    border-radius:16px;
    margin:18px 0 28px;
    padding:22px;
}
.featured-home-section .page-header{
    margin-bottom:16px;
    padding-bottom:14px;
}
.featured-home-section .page-header h2{
    font-size:26px;
}
.featured-home-section .listing-box-background{
    border-radius:14px;
    padding:18px;
}
.featured-home-section .listing-box-image{
    min-height:150px;
}
@media (max-width:767px){
    .cover.cover-center .cover-carousel,
    .cover.cover-center .cover-carousel-item,
    .cover.cover-center .cover-image{
        height:245px;
        min-height:245px;
    }
    .home-hero-panel{
        background:#07152a;
        padding:14px 0 16px;
    }
    .featured-property-ticker-overlay{
        top:72px;
    }
    .home-trust-strip{
        margin:14px auto 18px;
        padding:8px 12px;
    }
    .home-trust-row{
        display:block;
    }
    .home-trust-item{
        border-right:0;
        border-bottom:1px solid rgba(255,255,255,.12);
        min-height:68px;
        padding:14px 10px;
    }
    .home-trust-item:last-child{
        border-bottom:0;
    }
    .home-trust-value{
        font-size:34px;
    }
    .home-trust-label{
        font-size:12px;
    }
    .front-home-section{
        margin:18px 0;
    }
    .verified-property-block{
        margin-bottom:26px;
    }
    .featured-home-section{
        padding:16px;
    }
    .featured-home-section .listing-box-background{
        padding:12px;
    }
}
</style>
<div class="main-wrapper">
	    <div class="main">
	        <div class="main-inner">
	        	

	            <div class="content">
	                <div class="cover cover-center">
                        <div class="home-hero-panel">
                            <div class="container">
                                <div class="home-hero-content">
                                    <span class="home-hero-eyebrow"><?php echo __('White Villas Property Portal');?></span>
                                    <h1 class="home-hero-title"><?php echo __('Find Your Next Property With Confidence');?></h1>
                                    <p class="home-hero-copy"><?php echo __('Browse latest published listings, compare options, and connect with our team for verified property details.');?></p>
                                    <form id="homePropertySearchForm" class="home-search-panel" action="<?php echo h($homeSearchBaseUrl);?>" method="get">
                                        <div class="row">
                                            <div class="col-sm-5">
                                                <input type="text" name="keyword" class="form-control" placeholder="<?php echo __('Search by property, area, or keyword');?>">
                                            </div>
                                            <div class="col-sm-2">
                                                <input type="number" name="start_price" class="form-control" placeholder="<?php echo __('Min Price');?>">
                                            </div>
                                            <div class="col-sm-2">
                                                <input type="number" name="end_price" class="form-control" placeholder="<?php echo __('Max Price');?>">
                                            </div>
                                            <div class="col-sm-3">
                                                <button type="submit" class="btn btn-primary btn-block"><span class="fa fa-search"></span> <?php echo __('Search Property');?></button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php if($tickerEnabled && !empty($tickerItems)){ ?>
                        <div class="featured-property-ticker-overlay">
                            <div class="container">
                                <div class="featured-property-ticker-wrap">
                                    <div class="featured-property-ticker">
                                        <div class="featured-property-ticker-header">
                                        <div>
                                            <div class="featured-property-ticker-title-row">
                                                <span class="featured-property-ticker-title"><?php echo __('Latest Listings');?></span>
                                                <span class="featured-property-ticker-sub"><?php echo __('Fresh inventory moving live right now');?></span>
                                            </div>
                                        </div>
                                    </div>
                                        <div class="featured-property-ticker-viewport">
                                            <div class="featured-property-ticker-track" style="animation-duration: <?php echo (int)$tickerSpeed;?>s; animation-timing-function: <?php echo h($tickerEffect);?>;">
                                                <?php for($tickerLoop=0;$tickerLoop<2;$tickerLoop++){ ?>
                                                    <?php foreach($tickerItems as $tickerRow){
                                                        $tickerName = !empty($tickerRow['Property']['name']) ? $tickerRow['Property']['name'] : '-';
                                                        $tickerPrice = isset($tickerRow['Property']['price']) ? ($currency.number_format((float)$tickerRow['Property']['price'])) : '-';
                                                        $tickerType = !empty($tickerRow['Type']['name']) ? $tickerRow['Type']['name'] : '-';
                                                        $tickerAddress = !empty($tickerRow['Property']['address']) ? $tickerRow['Property']['address'] : '-';
                                                        $tickerPropertyId = !empty($tickerRow['Property']['id']) ? (int)$tickerRow['Property']['id'] : 0;
                                                        $tickerSlug = trim(preg_replace('/[^A-Za-z0-9]+/','-',$tickerName),'-');
                                                        if($tickerSlug==='')$tickerSlug='property-'.$tickerPropertyId;
                                                        $tickerViewUrl = ($tickerPropertyId > 0) ? $this->Html->url('/Properties/'.$tickerSlug) : '#';
                                                        $tickerArea = '-';
                                                        if(isset($tickerRow['Property']['area']) && $tickerRow['Property']['area']!=='')
                                                        {
                                                            $tickerArea = $tickerRow['Property']['area'];
                                                            if(!empty($tickerRow['Unit']['name']))
                                                            {
                                                                $tickerArea .= ' '.$tickerRow['Unit']['name'];
                                                            }
                                                        }
                                                        $tickerContract = !empty($tickerRow['Contract']['name']) ? $tickerRow['Contract']['name'] : '-';
                                                    ?>
                                                    <a class="featured-property-ticker-item featured-property-ticker-item-link" href="<?php echo h($tickerViewUrl);?>">
                                                        <div class="featured-property-ticker-left">
                                                            <span class="featured-property-ticker-tag"><?php echo h($tickerContract);?></span>
                                                            <div class="featured-property-ticker-name"><?php echo h($tickerName);?> - <?php echo h($tickerType);?></div>
                                                            <div class="featured-property-ticker-meta"><?php echo h($tickerAddress);?> | <?php echo __('Area');?>: <?php echo h($tickerArea);?></div>
                                                        </div>
                                                        <div class="featured-property-ticker-right">
                                                            <div class="featured-property-ticker-price"><?php echo h($tickerPrice);?></div>
                                                            <div class="featured-property-ticker-cta"><?php echo __('Tap to View');?></div>
                                                        </div>
                                                    </a>
                                                    <?php } ?>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php } ?>
		<div class="cover-carousel">
		<?php if($frontSlides==1){?>
		      <?php foreach($slides as $k=>$value): $photoImg=$photoUrl.'/'.$value['Slide']['dirt'].'_thumb/'.$value['Slide']['photo'];?>
		<div class="cover-carousel-item">
			<div class="cover-image" id="camera_wrap_4" style="background-image: url('<?php echo $photoImg;?>');"></div><!-- /.cover-image -->
		</div><!-- /.cover-carousel-item -->
		<?php endforeach;unset($k);unset($value);?>
	<?php }?>
		
		
		
	</div><!-- /.cover-carousel -->
</div><!-- /.cover -->

<div class="container">
    <div class="home-trust-strip">
        <div class="row home-trust-row">
            <div class="col-sm-4 home-trust-item">
                <div class="home-trust-value js-home-count" data-count="<?php echo (int)$publishedPropertyCount;?>" data-suffix="+">0+</div>
                <div class="home-trust-label"><?php echo __('Latest Published Listings');?></div>
            </div>
            <div class="col-sm-4 home-trust-item">
                <div class="home-trust-value js-home-count" data-count="<?php echo count((array)$allProperty);?>" data-suffix="+">0+</div>
                <div class="home-trust-label"><?php echo __('Featured Opportunities');?></div>
            </div>
            <div class="col-sm-4 home-trust-item">
                <div class="home-trust-value js-home-count" data-count="<?php echo count((array)$propertyType);?>" data-suffix="+">0+</div>
                <div class="home-trust-label"><?php echo __('Property Categories');?></div>
            </div>
        </div>
    </div>
    <div class="front-home-section published-property-section">
        <?php
        $verifiedPublishedSections = array(
            array(
                'title' => __('Verified Properties for Sale'),
                'copy' => __('Explore high-impact investment opportunities and verified luxury residences across Pakistan.'),
                'button' => __('View All Inventory'),
                'badge' => __('Gold Verified'),
                'badgeClass' => 'gold',
                'items' => array_slice($publishedSaleProperties, 0, 4),
                'priceSuffix' => ''
            )
        );
        foreach($verifiedPublishedSections as $verifiedSection){
        ?>
        <div class="verified-property-block">
            <div class="verified-property-head">
                <div class="verified-property-title">
                    <h1><?php echo h($verifiedSection['title']);?></h1>
                    <p><?php echo h($verifiedSection['copy']);?></p>
                </div>
                <div class="verified-property-actions">
                    <div class="verified-filter-pills">
                        <?php foreach($homeCategoryFilters as $filterIndex => $filterRow){
                            $filterUrl = ((int)$filterRow['id'] > 0) ? array('controller'=>'Properties','action'=>'index','type:'.(int)$filterRow['id']) : array('controller'=>'Properties','action'=>'index');
                        ?>
                        <?php echo $this->Html->link(h($filterRow['name']),$filterUrl,array('escape'=>false,'class'=>($filterIndex === 0 ? 'active' : ''),'data-filter-type'=>(int)$filterRow['id'],'data-filter-target'=>'published'));?>
                        <?php } ?>
                    </div>
                    <?php echo $this->Html->link(h($verifiedSection['button']).' <span class="fa fa-arrow-right"></span>',array('controller'=>'Properties','action'=>'index'),array('escape'=>false,'class'=>'verified-view-all'));?>
                </div>
            </div>
            <?php if(!empty($verifiedSection['items'])){ ?>
            <div class="verified-property-grid">
                <?php foreach((array)$verifiedSection['items'] as $publishedCard){
                    $publishedCardId = !empty($publishedCard['Property']['id']) ? (int)$publishedCard['Property']['id'] : 0;
                    $publishedCardName = !empty($publishedCard['Property']['name']) ? $publishedCard['Property']['name'] : '-';
                    $publishedCardPrice = isset($publishedCard['Property']['price']) ? $formatHomePublishedPrice($publishedCard['Property']['price'], $verifiedSection['priceSuffix']) : '-';
                    $publishedCardPhoto = $photoUrl.'/nia.png';
                    $publishedCardPhotoCount = !empty($publishedCard['PropertiesPhoto']) && is_array($publishedCard['PropertiesPhoto']) ? count($publishedCard['PropertiesPhoto']) : 0;
                    if(!empty($publishedCard['PropertiesPhoto']) && is_array($publishedCard['PropertiesPhoto']))
                    {
                        foreach($publishedCard['PropertiesPhoto'] as $publishedPhoto)
                        {
                            if(empty($publishedPhoto['photo']))
                            {
                                continue;
                            }
                            $photoDir = !empty($publishedPhoto['dir']) ? $publishedPhoto['dir'] : (!empty($publishedPhoto['dirt']) ? $publishedPhoto['dirt'] : '');
                            $candidatePhoto = $photoUrl.'/'.trim($photoDir).'_thumb/'.$publishedPhoto['photo'];
                            if(!empty($publishedPhoto['home']) && $publishedPhoto['home'] === 'Yes')
                            {
                                $publishedCardPhoto = $candidatePhoto;
                                break;
                            }
                            $publishedCardPhoto = $candidatePhoto;
                        }
                    }
                    $publishedOwner = !empty($publishedCard['User']['name']) ? $publishedCard['User']['name'] : __('Owner');
                    $publishedBeds = !empty($publishedCard['Property']['bedroom']) ? $publishedCard['Property']['bedroom'] : '-';
                    $publishedBaths = !empty($publishedCard['Property']['bathroom']) ? $publishedCard['Property']['bathroom'] : '-';
                    $publishedArea = !empty($publishedCard['Property']['area']) ? $publishedCard['Property']['area'] : '-';
                    if(!empty($publishedCard['Unit']['name']) && $publishedArea !== '-')
                    {
                        $publishedArea .= ' '.$publishedCard['Unit']['name'];
                    }
                    $publishedCardSlug = trim(preg_replace('/[^A-Za-z0-9]+/','-',$publishedCardName),'-');
                    if($publishedCardSlug==='')$publishedCardSlug='property-'.$publishedCardId;
                    $publishedCardUrl = $this->Html->url('/Properties/'.$publishedCardSlug);
                ?>
                <article class="verified-property-card" data-property-type-id="<?php echo !empty($publishedCard['Property']['type_id']) ? (int)$publishedCard['Property']['type_id'] : 0;?>">
                    <div class="verified-property-media">
                        <a href="<?php echo h($publishedCardUrl);?>">
                            <div class="verified-property-media-img" style="background-image:url('<?php echo h($publishedCardPhoto);?>');"></div>
                        </a>
                        <span class="verified-badge <?php echo h($verifiedSection['badgeClass']);?>"><span class="fa fa-star"></span> <?php echo h($verifiedSection['badge']);?></span>
                        <span class="verified-photo-count"><span class="fa fa-camera"></span> <?php echo (int)$publishedCardPhotoCount;?> <?php echo __('photos');?></span>
                        <div class="verified-card-price"><?php echo h($publishedCardPrice);?></div>
                    </div>
                    <div class="verified-property-body">
                        <?php echo $this->Html->link(h($publishedCardName),$publishedCardUrl,array('escape'=>false,'class'=>'verified-property-name'));?>
                        <div class="verified-property-specs">
                            <span><span class="fa fa-bed"></span> <strong><?php echo h($publishedBeds);?></strong> <?php echo __('Beds');?></span>
                            <span><span class="fa fa-bath"></span> <strong><?php echo h($publishedBaths);?></strong> <?php echo __('Baths');?></span>
                            <span><span class="fa fa-expand"></span> <strong><?php echo h($publishedArea);?></strong></span>
                        </div>
                        <div class="verified-card-footer">
                            <div class="verified-owner-icon"><span class="fa fa-user"></span></div>
                            <div>
                                <div class="verified-owner-name"><?php echo h($publishedOwner);?><span><?php echo __('Owner');?></span></div>
                                <div class="verified-owner-commission"><span class="fa fa-check-circle"></span> <?php echo __('Direct Owner - 0% Commission');?></div>
                            </div>
                            <?php echo $this->Html->link('<span class="fa fa-angle-right"></span>',$publishedCardUrl,array('escape'=>false,'class'=>'verified-card-arrow'));?>
                        </div>
                    </div>
                </article>
                <?php } ?>
            </div>
            <?php } else { ?>
            <div class="verified-empty"><?php echo __('No published properties found in this section.');?></div>
            <?php } ?>
        </div>
        <?php } ?>
        <div class="verified-property-block portal-category-block">
            <div class="verified-property-head">
                <div class="verified-property-title">
                    <h1><?php echo __('Popular Property Categories');?></h1>
                    <p><?php echo __('Browse your portal inventory by category and quickly open the listings that match your buyers.');?></p>
                </div>
                <div class="verified-property-actions">
                    <div class="verified-filter-pills">
                        <?php foreach($homeCategoryFilters as $filterIndex => $filterRow){
                            $filterUrl = ((int)$filterRow['id'] > 0) ? array('controller'=>'Properties','action'=>'index','type:'.(int)$filterRow['id']) : array('controller'=>'Properties','action'=>'index');
                        ?>
                        <?php echo $this->Html->link(h($filterRow['name']),$filterUrl,array('escape'=>false,'class'=>($filterIndex === 0 ? 'active' : ''),'data-filter-type'=>(int)$filterRow['id'],'data-filter-target'=>'categories'));?>
                        <?php } ?>
                    </div>
                    <?php echo $this->Html->link(__('View Property Categories').' <span class="fa fa-arrow-right"></span>',array('controller'=>'Properties','action'=>'index'),array('escape'=>false,'class'=>'verified-view-all'));?>
                </div>
            </div>
            <?php if(!empty($propertyType)){ ?>
            <div class="portal-category-grid">
                <?php foreach(array_slice((array)$propertyType, 0, 4) as $categoryRow){
                    if(empty($categoryRow['Type']['photo']))
                    {
                        $categoryPhoto = $photoUrl.'/nia.png';
                    }
                    else
                    {
                        $categoryPhoto = $photoUrl.'/'.$categoryRow['Type']['dirt'].'_thumb/'.$categoryRow['Type']['photo'];
                    }
                    $categoryId = !empty($categoryRow['Type']['id']) ? (int)$categoryRow['Type']['id'] : 0;
                    $categoryName = !empty($categoryRow['Type']['name']) ? $categoryRow['Type']['name'] : __('Property');
                    $categoryCount = isset($categoryRow['Type']['total_count']) ? (int)$categoryRow['Type']['total_count'] : 0;
                ?>
                <article class="portal-category-card" data-category-type-id="<?php echo (int)$categoryId;?>">
                    <div class="portal-category-card-bg" style="background-image:url('<?php echo h($categoryPhoto);?>');">
                        <span class="portal-category-count"><?php echo (int)$categoryCount;?> <?php echo __('Properties');?></span>
                        <div class="portal-category-title"><?php echo h($categoryName);?></div>
                    </div>
                    <div class="portal-category-body">
                        <p><?php echo __('Open verified published inventory for this category from your White Villas portal.');?></p>
                        <?php echo $this->Html->link(__('View List').' <span class="fa fa-angle-right"></span>',array('controller'=>'Properties','action'=>'index','type:'.$categoryId),array('escape'=>false,'class'=>'portal-category-link'));?>
                    </div>
                </article>
                <?php } ?>
            </div>
            <?php } else { ?>
            <div class="verified-empty"><?php echo __('No property categories found.');?></div>
            <?php } ?>
        </div>
    </div>
<div class="front-home-section featured-home-section">
	<div class="page-header">
		<h2><?php echo __('Featured Properties');?></h2>
        <p><?php echo __('Hand-picked properties from the latest inventory.');?></p>
        <div class="home-section-actions">
            <?php echo $this->Html->link(__('Browse Properties'),array('controller'=>'Properties','action'=>'index'),array('class'=>'btn btn-default'));?>
        </div>
	</div>

	<div class="listing-box-wrapper listing-box-background">
	<div class="listing-carousel-wrapper">
		<div class="listing-carousel">		
						
       <?php foreach($allProperty as $post):$id=$post['Property']['id'];
        $this->Function->gridProperty($record,$post,$currency,'<div>');echo$record;?>
<?php unset($post);endforeach;?>	
		</div>
	</div>
</div>
	</div><!-- /.featured-home-section -->
</div><!-- /.container -->

	            </div><!-- /.content -->
	        </div><!-- /.main-inner -->
	    </div><!-- /.main -->
    </div><!-- /.main-wrapper -->

<script type="text/javascript">
(function($){
    $('[data-filter-type]').on('click', function(e){
        e.preventDefault();
        var $link = $(this);
        var filterType = parseInt($link.attr('data-filter-type'), 10) || 0;
        var target = $.trim(String($link.attr('data-filter-target') || ''));
        var $pillGroup = $link.closest('.verified-filter-pills');
        $pillGroup.find('a').removeClass('active');
        $link.addClass('active');

        if(target === 'published')
        {
            var visibleCount = 0;
            $('.verified-property-card').each(function(){
                var cardType = parseInt($(this).attr('data-property-type-id'), 10) || 0;
                var shouldShow = (filterType === 0 || cardType === filterType);
                $(this).toggle(shouldShow);
                if(shouldShow){ visibleCount++; }
            });
            $('.verified-property-grid').toggle(visibleCount > 0);
            $('.verified-property-block').first().find('.verified-empty').remove();
            if(visibleCount <= 0)
            {
                $('.verified-property-block').first().append('<div class="verified-empty"><?php echo addslashes(__('No published properties found in this category.'));?></div>');
            }
            return false;
        }

        if(target === 'categories')
        {
            var categoryVisibleCount = 0;
            $('.portal-category-card').each(function(){
                var categoryType = parseInt($(this).attr('data-category-type-id'), 10) || 0;
                var shouldShowCategory = (filterType === 0 || categoryType === filterType);
                $(this).toggle(shouldShowCategory);
                if(shouldShowCategory){ categoryVisibleCount++; }
            });
            $('.portal-category-grid').toggle(categoryVisibleCount > 0);
            $('.portal-category-block').find('.verified-empty.dynamic-empty').remove();
            if(categoryVisibleCount <= 0)
            {
                $('.portal-category-block').append('<div class="verified-empty dynamic-empty"><?php echo addslashes(__('No property categories found for this filter.'));?></div>');
            }
            return false;
        }
        return false;
    });

    $('#homePropertySearchForm').on('submit', function(e){
        e.preventDefault();
        var baseUrl = '<?php echo addslashes($homeSearchBaseUrl);?>';
        var parts = [];
        var keyword = $.trim($(this).find('[name="keyword"]').val() || '');
        var startPrice = $.trim($(this).find('[name="start_price"]').val() || '');
        var endPrice = $.trim($(this).find('[name="end_price"]').val() || '');
        if(keyword !== '')parts.push('keyword:' + encodeURIComponent(keyword));
        if(startPrice !== '')parts.push('start_price:' + encodeURIComponent(startPrice));
        if(endPrice !== '')parts.push('end_price:' + encodeURIComponent(endPrice));
        window.location.href = baseUrl + (parts.length ? '/' + parts.join('/') : '');
        return false;
    });

    $('.js-home-count').each(function(){
        var $counter = $(this);
        var target = parseInt($counter.attr('data-count'), 10) || 0;
        var suffix = $counter.attr('data-suffix') || '';
        var duration = 1050;
        var startTime = null;
        var runCounter = function(timestamp){
            if(startTime === null){ startTime = timestamp; }
            var progress = Math.min((timestamp - startTime) / duration, 1);
            var eased = 1 - Math.pow(1 - progress, 3);
            $counter.text(Math.round(target * eased) + suffix);
            if(progress < 1 && window.requestAnimationFrame)
            {
                window.requestAnimationFrame(runCounter);
            }
            else
            {
                $counter.text(target + suffix);
            }
        };
        if(window.requestAnimationFrame)
        {
            window.requestAnimationFrame(runCounter);
        }
        else
        {
            $counter.text(target + suffix);
        }
    });
})(jQuery);
</script>
    
