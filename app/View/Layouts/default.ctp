<?php
/**
 *
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.View.Layouts
 * @since         CakePHP(tm) v 0.10.0.1076
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

$wvReqUri = isset($_SERVER['REQUEST_URI']) ? strtolower((string)$_SERVER['REQUEST_URI']) : '';
$wvIsAdminRoute = (!empty($this->params['admin']) || 
                   (!empty($this->params['prefix']) && $this->params['prefix'] === 'admin') || 
                   (!empty($this->params['plugin']) && strcasecmp($this->params['plugin'], 'Admin') === 0) || 
                   (strpos($wvReqUri, '/admin/') !== false) ||
                   (isset($this->params['controller']) && in_array(strtolower((string)$this->params['controller']), array('leads','crmautomations','leadduplicates'), true)));

if ($wvIsAdminRoute) {
    include CakePlugin::path('Admin') . 'View' . DS . 'Layouts' . DS . 'default.ctp';
    return;
}

$wvLayoutController=isset($this->params['controller']) ? strtolower((string)$this->params['controller']) : '';
$wvLayoutAction=isset($this->params['action']) ? strtolower((string)$this->params['action']) : '';
$wvLayoutMapProvider=isset($defaultMapProvider) ? strtolower((string)$defaultMapProvider) : 'osm';
$wvLayoutNeedsCarousel=($wvLayoutController==='fronts');
$wvLayoutNeedsPropertyMap=($wvLayoutController==='properties' && $wvLayoutAction==='view');
$wvLayoutNeedsLeaflet=($wvLayoutNeedsPropertyMap && $wvLayoutMapProvider!=='google');
$wvLayoutNeedsGoogleMap=($wvLayoutNeedsPropertyMap && $wvLayoutMapProvider==='google' && $this->Session->check('frontUser'));
$wvLayoutNeedsPopup=($wvLayoutController==='properties' && in_array($wvLayoutAction,array('view','printproperty'),true));
$wvLayoutNeedsValidation=in_array($wvLayoutController,array('clients','forgots','changepasswords','registers','contacts','myprofiles'),true);

$portalControllers = array(
    'dashboards',
    'mydeals',
    'my_deals',
    'mypayments',
    'my_payments',
    'myinvpastdues',
    'my_invpastdues',
    'mydocuments',
    'my_documents',
    'myrentals',
    'my_rentals',
    'myrefundrequests',
    'my_refundrequests',
    'myprofiles',
    'my_profiles',
    'changepasswords',
    'change_passwords',
    'utilitybills',
    'utility_bills',
    'myutilitybills',
    'my_utility_bills',
    'myutilitybill',
    'my_utility_bill',
    'clientnotifications',
    'client_notifications',
    'ballotingresults',
    'balloting_results',
    'myballoting',
    'favouriteproperties',
    'favourite_properties'
);

$currentLayoutCtrl = !empty($this->params['controller']) ? strtolower(trim((string)$this->params['controller'])) : (!empty($this->name) ? strtolower(trim((string)$this->name)) : '');
$isPortalPage = in_array($currentLayoutCtrl, $portalControllers, true) 
    || (strpos($currentLayoutCtrl, 'my') === 0) 
    || ($currentLayoutCtrl === 'dashboards') 
    || ($currentLayoutCtrl === 'changepasswords')
    || (strpos($currentLayoutCtrl, 'clientnotif') === 0)
    || (strpos($currentLayoutCtrl, 'ballot') === 0)
    || (strpos($currentLayoutCtrl, 'favourite') === 0);
$hasFrontClientSession = ($isPortalPage || $this->Session->check('frontUser') || !empty($UserArr['Client']['id']) || !empty($frontUser['Client']['id']) || !empty($portalClient['id']) || $this->Session->check('Impersonation.front'));
$isFrontPublicController = in_array($currentLayoutCtrl, array('properties','fronts','registers','clients','agents','contents','contacts','agencies','paymentplans'), true) && !$isPortalPage;

if(($isPortalPage || $hasFrontClientSession) && !$isFrontPublicController){?>
<!DOCTYPE html>
  <html lang="<?php echo h($configLanguage);?>" dir="<?php echo h($dirType);?>">
    <head>
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
	  <meta name="description" content="<?php echo h($siteDescription);?>" />
	  <meta name="author" content="<?php echo h($siteName);?>" />
	  <meta name="google-translate-customization" content="839d71f7ff6044d0-328a2dc5159d6aa2-gd17de6447c9ba810-f">
	  <?php echo $this->Html->charset();?>
	  <title><?php echo h($siteTitle);?></title>
<?php
$siteFaviconFile = !empty($siteFavicon) ? $siteFavicon : '';
$ogLogo = !empty($frontLogo) ? $frontLogo : 'organisation-logo-20260511101817-recovered.png';
if (strpos($ogLogo, 'http://') === 0 || strpos($ogLogo, 'https://') === 0) {
    $ogImageUrl = $ogLogo;
} else {
    $ogImageUrl = Router::url('/img/' . ltrim($ogLogo, '/'), true);
}
$ogSiteName = !empty($siteName) ? $siteName : 'White Villas';
$ogPageTitle = !empty($title_for_layout) ? ($title_for_layout . ' — ' . $ogSiteName) : (!empty($siteTitle) ? $siteTitle : $ogSiteName);
$ogPageDesc = !empty($siteDescription) ? $siteDescription : 'White Villas — Premier Real Estate, Luxury Lodges & Verified Investment Opportunities.';
$ogPageUrl = Router::url(null, true);
?>
    <!-- Favicon (Browser Tab Icon) -->
    <?php if (!empty($siteFaviconFile)): ?>
    <link rel="icon" type="image/x-icon" href="<?php echo $this->Html->url('/img/' . $siteFaviconFile); ?>">
    <link rel="shortcut icon" href="<?php echo $this->Html->url('/img/' . $siteFaviconFile); ?>">
    <link rel="apple-touch-icon" href="<?php echo $this->Html->url('/img/' . $siteFaviconFile); ?>">
    <?php else: ?>
    <link rel="icon" type="image/x-icon" href="<?php echo $this->Html->url('/favicon.ico'); ?>">
    <?php endif; ?>

    <!-- Social Media Sharing & Open Graph Meta Tags (WhatsApp, Facebook, Twitter, LinkedIn) -->
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="<?php echo h($ogSiteName); ?>">
    <meta property="og:title" content="<?php echo h($ogPageTitle); ?>">
    <meta property="og:description" content="<?php echo h($ogPageDesc); ?>">
    <meta property="og:image" content="<?php echo h($ogImageUrl); ?>">
    <meta property="og:image:secure_url" content="<?php echo h($ogImageUrl); ?>">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="<?php echo h($ogSiteName); ?>">
    <meta property="og:url" content="<?php echo h($ogPageUrl); ?>">

    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo h($ogPageTitle); ?>">
    <meta name="twitter:description" content="<?php echo h($ogPageDesc); ?>">
    <meta name="twitter:image" content="<?php echo h($ogImageUrl); ?>">
	<meta name="description" content="<?php echo h($siteDescription);?>"/>
	
		<?php echo $this->Html->meta('icon');
		echo $this->Html->css('https://fonts.googleapis.com/css?family=Raleway:300,400,500,600,300,700&subset=latin,latin-ext');
		echo $this->Html->css('style');
		echo $this->Html->css('bootstrap.min');
		if(!empty($wvLayoutNeedsCarousel)){ echo $this->Html->css('/design1000/assets/libraries/owl-carousel/owl.carousel.css'); }
		echo $this->Html->css('font-awesome.min');
		if(!empty($wvLayoutNeedsLeaflet)){
		    echo $this->Html->css('/design1000/assets/css/leaflet.css');
		}
		if(!empty($wvLayoutNeedsPopup)){ echo $this->Html->css('/design1000/assets/css/magnific-popup.css'); }
		echo $this->Html->css('/design1000/assets/css/villareal-turquoise.css');
		if(!empty($wvLayoutNeedsValidation)){ echo $this->Html->css('validationEngine.jquery'); }
		echo $this->Html->css('wv-theme-tokens');
		echo $this->Html->css('wv-portal-theme');
		if(!empty($themeDynamicCss)){ echo '<style type="text/css" id="wv-dynamic-theme">'.$themeDynamicCss.'</style>'; }
		echo $this->fetch('meta');		
		echo $this->fetch('css');
		echo $this->Html->script('/design1000/assets/js/jquery.js');
		if(!empty($wvLayoutNeedsValidation)){
		    echo $this->Html->script('jquery.validationEngine-en');
                    echo $this->Html->script('jquery.validationEngine');
                }
		echo $this->Html->script('/design1000/assets/js/tether.min.js');
		echo $this->Html->script('/design1000/assets/js/bootstrap.min.js');		
		if(!empty($wvLayoutNeedsLeaflet)){
		    echo $this->Html->script('/design1000/assets/js/leaflet.js');
		}
		if(!empty($wvLayoutNeedsCarousel)){ echo $this->Html->script('/design1000/assets/libraries/owl-carousel/owl.carousel.min.js'); }
		if(!empty($wvLayoutNeedsPopup)){ echo $this->Html->script('/design1000/assets/js/jquery.magnific-popup.min.js'); }
		if(!empty($wvLayoutNeedsGoogleMap)){
		    echo $this->Html->script('https://maps.googleapis.com/maps/api/js?key='.$mapKey);
		}
		echo $this->Html->script('/design1000/assets/js/custom.js');
		echo $this->Html->script('waiting-dialog.min');
		echo $this->Html->script('custom.min');
		echo $this->Html->script('wv-theme');
		echo $this->Html->script("langs/$configLanguage");
		echo $this->fetch('script');
                echo $this->Js->writeBuffer();
		$UserArr=$this->Session->read('frontUser');
 ?>
<style type="text/css">
.dashboard-user-image{
  border:4px solid #fff!important;
  border-radius:50%!important;
  box-shadow:0 10px 22px rgba(15,23,42,.18)!important;
  height:82px!important;
  margin:0 auto 10px!important;
  width:82px!important;
  background-position:center!important;
  background-size:cover!important;
}
.dashboard-user strong{
  display:block;
  text-align:center;
}
@media (max-width: 991px){
  html,
  body{
    overflow-x:hidden;
  }
  body:not(.client-sidebar-open){
    overflow-y:auto !important;
    position:static !important;
    width:auto !important;
  }
  .page-wrapper,
  .dashboard-wrapper,
  .dashboard-content{
    min-height:100vh;
    overflow:visible !important;
  }
  .dashboard-content .table-responsive,
  .dashboard-content .table-responsive.client-scroll-wrap{
    overflow-x:auto !important;
    overflow-y:visible;
    -webkit-overflow-scrolling:touch;
    touch-action:pan-x pan-y;
    border:1px solid rgba(0, 0, 0, 0.1);
    border-radius:8px;
    background:#fff;
    margin-bottom:12px;
  }
  .dashboard-content .dataTables_wrapper{
    overflow-x:auto !important;
    -webkit-overflow-scrolling:touch;
    touch-action:pan-x pan-y;
  }
  .dashboard-content .dataTables_scrollBody{
    overflow-x:auto !important;
    -webkit-overflow-scrolling:touch;
  }
  .dashboard-content .table-responsive > .table,
  .dashboard-content .table-responsive > table,
  .dashboard-content .dataTables_wrapper table,
  .dashboard-content table.table.table-users{
    width:auto !important;
    max-width:none;
    min-width:860px;
    margin-bottom:0;
  }
  .dashboard-content .table-responsive > .table > thead > tr > th,
  .dashboard-content .table-responsive > .table > tbody > tr > td,
  .dashboard-content .table-responsive > table > thead > tr > th,
  .dashboard-content .table-responsive > table > tbody > tr > td{
    white-space:nowrap;
  }
  .dashboard-sidebar{
    position:relative;
    z-index:1070;
  }
  .dashboard-title{
    position:relative;
    padding-left:74px;
    padding-right:16px;
  }
  .dashboard-title .navbar-toggler{
    display:block !important;
    left:16px;
    right:auto;
    top:50%;
    transform:translateY(-50%);
    width:40px;
    height:36px;
    line-height:30px;
    padding:2px 0;
    text-align:center;
    border:2px solid rgba(0, 0, 0, 0.2);
    border-radius:4px;
    background:#fff;
    z-index:1062;
  }
  .dashboard-title .navbar-toggler span{
    display:none;
  }
  .dashboard-title .navbar-toggler:before{
    content:"\f0c9";
    font-family:FontAwesome;
    font-size:18px;
    color:rgba(0, 0, 0, 0.55);
  }
  .dashboard-nav-primary{
    background:#fff;
    border-top:0;
    border-right:1px solid rgba(0, 0, 0, 0.08);
    position:fixed;
    top:0;
    left:0;
    right:auto;
    width:82%;
    max-width:300px;
    height:100vh !important;
    height:100dvh !important;
    max-height:none !important;
    display:block !important;
    overflow-y:auto;
    overflow-x:hidden;
    transform:translateX(-105%);
    transition:transform .25s ease;
    box-shadow:none;
    z-index:1071;
    -webkit-overflow-scrolling:touch;
    touch-action:pan-y;
    pointer-events:auto;
  }
  .dashboard-nav-primary.in,
  .dashboard-nav-primary.collapse.in,
  body.client-sidebar-open .dashboard-nav-primary{
    transform:translateX(0);
    box-shadow:12px 0 28px rgba(0, 0, 0, 0.18);
  }
  .dashboard-nav-primary.collapse,
  .dashboard-nav-primary.collapsing{
    display:block !important;
    height:100vh !important;
  }
  .client-sidebar-backdrop{
    background:rgba(0, 0, 0, 0.35);
    bottom:0;
    display:none;
    left:0;
    position:fixed;
    right:0;
    top:0;
    z-index:1060;
  }
  .dashboard-nav-primary .nav,
  .dashboard-nav-primary .nav-item,
  .dashboard-nav-primary .nav-link{
    position:relative;
    z-index:1072;
    pointer-events:auto;
  }
  body.client-sidebar-open .client-sidebar-backdrop{
    display:block;
  }
  body.client-sidebar-open{
    overflow:hidden;
  }
  .dashboard-nav-primary .nav{
    padding:14px!important;
  }
  .dashboard-nav-primary .nav-item{
    margin:0 0 8px!important;
  }
  .dashboard-nav-primary .nav-link{
    align-items:center;
    border:1px solid rgba(15,23,42,.08);
    border-radius:12px;
    color:#07152a!important;
    display:flex!important;
    font-size:13px;
    font-weight:900;
    gap:10px;
    min-height:44px;
    padding:10px 12px!important;
  }
  .dashboard-nav-primary .nav-link i,
  .dashboard-nav-primary .nav-link span[class^="fa"],
  .dashboard-nav-primary .nav-link span[class*=" fa-"]{
    align-items:center;
    background:#fff1f4;
    border-radius:10px;
    color:#ff0a46!important;
    display:inline-flex;
    flex:0 0 32px;
    height:32px;
    justify-content:center;
    width:32px;
  }
  .dashboard-nav-primary .nav-link:hover,
  .dashboard-nav-primary .nav-item.active .nav-link{
    background:#07152a!important;
    color:#ffffff!important;
  }
}
</style>
<?php if($translate>0){?>
<div id="google_translate_element"></div><script type="text/javascript">
function googleTranslateElementInit() {
  new google.translate.TranslateElement({pageLanguage: 'en', layout: google.translate.TranslateElement.InlineLayout.SIMPLE}, 'google_translate_element');
}
</script><script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit" async defer></script>
<?php }?>
</head>
   

<body class="wv-client-theme">
	<div class="page-wrapper">
		<div class="dashboard-wrapper">
			<div class="dashboard-sidebar">
				<div class="dashboard-title">
					<a href="#">
					  <?php if(strlen($frontLogo)>0){?><?php echo$this->Html->image($frontLogo,array('alt'=>$siteName,'class'=>'','height'=>'40'));} else{?>
						<span class="logo-shape"></span> <?php echo h($siteName);
						}?>
					</a>

					<button class="navbar-toggler pull-xs-right hidden-lg-up" type="button" data-toggle="collapse" data-target=".dashboard-nav-primary">
                        <span></span>
                        <span></span>
                        <span></span>
                    </button>						
				</div><!-- /.dashboard-title -->

				<div class="dashboard-user hidden-md-down">
				<?php 
				$currentClientArr = !empty($UserArr['Client']) ? $UserArr['Client'] : (!empty($frontUser['Client']) ? $frontUser['Client'] : (!empty($portalClient) ? $portalClient : array()));
				$photoUrl=$this->Html->url(array('controller'=>'img'));
				if(!empty($frontClientPhotoUrl)){
					$urlPhoto=$frontClientPhotoUrl;
				} elseif(empty($currentClientArr['photo'])){
					$urlPhoto=$photoUrl.'/user-icon.png';
				} else {
					$clientPhotoDir=!empty($currentClientArr['dir']) ? $currentClientArr['dir'] : (!empty($currentClientArr['dirt']) ? $currentClientArr['dirt'] : 'client');
					$urlPhoto=$photoUrl.'/'.trim($clientPhotoDir).'_thumb/'.$currentClientArr['photo'];
				}
				?>
				     	<div class="dashboard-user-image" style="background-image: url('<?php echo h($urlPhoto);?>')">
					</div><!-- /.dashboar-user-image -->
					<strong><?php echo h(!empty($currentClientArr['name']) ? $currentClientArr['name'] : __('Client'));?></strong>
				</div><!-- /.dashboard-user -->

				<div class="dashboard-nav-primary collapse navbar-toggleable-md">
					<ul class="nav nav-stacked">

	      <?php foreach ($menuArr as $menu):
	      $menuIcon=$menu['Page']['icon'];$menuName=$menu['Page']['model_name'];?>
	      <li class="nav-item <?php echo (strtolower($this->params['controller'])==strtolower($menu['Page']['controller_name']) || in_array(strtolower($this->params['controller']),explode(",",strtolower($menu['Page']['sel_name'])))) ? 'active' : ''; ?>"><?php echo $this->Html->link("<i class=\"$menuIcon\"></i>&nbsp;<span>$menuName</span>",array('plugin'=>false,'admin'=>false,'controller' => $menu['Page']['controller_name'],'action'=>$menu['Page']['action_name']),array('escape' => false,'class'=>'nav-link'));?></li>
	      <?php endforeach;unset($menu);?>					
					</ul>
				</div><!-- /.dashboard-nav-primary -->
				
			</div><!-- /.dashboard-sidebar -->

			<div class="dashboard-content">	
				<div class="container-fluid">
					<div class="dashboard-header wv-client-top-bar">
						<div class="wv-client-top-left">
							<button class="wv-mobile-menu-trigger hidden-lg-up" type="button" id="wvMobileMenuTrigger" aria-label="<?php echo __('Toggle Navigation'); ?>">
								<i class="fa fa-bars"></i>
							</button>
							<span class="wv-portal-badge"><i class="fa fa-shield"></i> <?php echo __('White Villas Client Portal'); ?></span>
						</div>
						<div class="wv-client-top-right">
							<!-- In-Portal Notification Bell Dropdown -->
							<div class="wv-client-notif-dropdown dropdown" id="wvClientNotifDropdown">
								<a href="javascript:void(0);" class="wv-client-bell-btn dropdown-toggle" id="wvNotifBellBtn" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="<?php echo __('Notifications'); ?>">
									<i class="fa fa-bell-o"></i>
									<?php $unreadNotifDisplay = !empty($clientUnreadNotifCount) ? (int)$clientUnreadNotifCount : 0; ?>
									<span class="wv-bell-badge <?php echo ($unreadNotifDisplay > 0) ? 'has-unread' : ''; ?>" id="wvBellBadge" style="<?php echo ($unreadNotifDisplay > 0) ? '' : 'display:none;'; ?>">
										<?php echo $unreadNotifDisplay; ?>
									</span>
								</a>
								<div class="dropdown-menu dropdown-menu-right wv-notif-menu" aria-labelledby="wvNotifBellBtn">
									<div class="wv-notif-menu-header">
										<div class="wv-notif-menu-title">
											<i class="fa fa-bell text-warning"></i> <?php echo __('Notifications'); ?>
										</div>
										<a href="javascript:void(0);" id="wvMarkAllReadBtn" class="wv-notif-mark-read">
											<i class="fa fa-check"></i> <?php echo __('Mark all read'); ?>
										</a>
									</div>
									<div class="wv-notif-items-wrap" id="wvNotifItemsWrap">
										<div class="wv-notif-loading"><i class="fa fa-spinner fa-spin"></i> <?php echo __('Loading updates...'); ?></div>
									</div>
									<div class="wv-notif-menu-footer">
										<a href="<?php echo $this->Html->url(array('controller' => 'ClientNotifications', 'action' => 'index')); ?>" class="wv-notif-see-all">
											<?php echo __('View All Notifications'); ?> <i class="fa fa-arrow-right"></i>
										</a>
									</div>
								</div>
							</div>

							<div class="wv-client-user-pill hidden-xs-down">
								<i class="fa fa-user-circle"></i>
								<span><?php echo h(!empty($currentClientArr['name']) ? $currentClientArr['name'] : __('Client')); ?></span>
							</div>
						</div>
					</div><!-- /.dashboard-header -->
                                       <?php echo $this->fetch('content');?>
<style type="text/css" id="wv-client-readable-style">
body.wv-client-theme,
body.wv-client-theme .dashboard-wrapper,
body.wv-client-theme .dashboard-content{
  background:#f4f7fb!important;
  color:#111827!important;
  font-family:var(--wv-font, Raleway, Arial, sans-serif)!important;
}
body.wv-client-theme .dashboard-sidebar{
  background:linear-gradient(180deg,#07152a 0%,#0b1d36 100%)!important;
  border-right:1px solid rgba(124,249,255,.16)!important;
  box-shadow:14px 0 34px rgba(15,23,42,.18)!important;
}
body.wv-client-theme .dashboard-title{
  background:#07152a!important;
  border-bottom:1px solid rgba(255,255,255,.12)!important;
  min-height:72px!important;
  padding:16px!important;
}
body.wv-client-theme .dashboard-title a{
  align-items:center!important;
  color:#ffffff!important;
  display:flex!important;
  font-size:17px!important;
  font-weight:900!important;
  justify-content:center!important;
  line-height:1.25!important;
  text-decoration:none!important;
}
body.wv-client-theme .dashboard-title img{
  background:#ffffff!important;
  border-radius:10px!important;
  max-height:48px!important;
  max-width:135px!important;
  object-fit:contain!important;
  padding:5px!important;
}
body.wv-client-theme .dashboard-user{
  background:rgba(255,255,255,.07)!important;
  border:1px solid rgba(255,255,255,.12)!important;
  border-radius:16px!important;
  margin:16px 14px!important;
  padding:16px 12px!important;
}
body.wv-client-theme .dashboard-user strong{
  color:#ffffff!important;
  display:block!important;
  font-size:14px!important;
  font-weight:900!important;
  line-height:1.35!important;
  text-align:center!important;
  text-shadow:0 2px 10px rgba(0,0,0,.45)!important;
}
body.wv-client-theme .dashboard-user-image{
  border:3px solid rgba(124,249,255,.78)!important;
}
body.wv-client-theme .dashboard-nav-primary{
  background:transparent!important;
  padding:10px 12px 22px!important;
}
body.wv-client-theme .dashboard-nav-primary .nav,
body.wv-client-theme .dashboard-nav-primary ul{
  list-style:none!important;
  margin:0!important;
  padding:0!important;
}
body.wv-client-theme .dashboard-nav-primary .nav-item{
  display:block!important;
  margin:0 0 8px!important;
  width:100%!important;
}
body.wv-client-theme .dashboard-nav-primary .nav-link{
  align-items:center!important;
  background:rgba(255,255,255,.055)!important;
  border:1px solid rgba(255,255,255,.09)!important;
  border-radius:12px!important;
  color:#e5eefb!important;
  display:flex!important;
  font-size:13px!important;
  font-weight:900!important;
  gap:10px!important;
  line-height:1.25!important;
  min-height:44px!important;
  padding:10px 12px!important;
  text-decoration:none!important;
  white-space:normal!important;
  width:100%!important;
}
body.wv-client-theme .dashboard-nav-primary .nav-link i,
body.wv-client-theme .dashboard-nav-primary .nav-link span[class^="fa"],
body.wv-client-theme .dashboard-nav-primary .nav-link span[class*=" fa-"],
body.wv-client-theme .dashboard-nav-primary .nav-link span[class^="glyphicon"],
body.wv-client-theme .dashboard-nav-primary .nav-link span[class*=" glyphicon-"]{
  align-items:center!important;
  background:rgba(124,249,255,.12)!important;
  border-radius:10px!important;
  color:#8ff7ff!important;
  display:inline-flex!important;
  flex:0 0 32px!important;
  height:32px!important;
  justify-content:center!important;
  margin:0!important;
  text-align:center!important;
  width:32px!important;
}
body.wv-client-theme .dashboard-nav-primary .nav-link:hover,
body.wv-client-theme .dashboard-nav-primary .nav-item.active .nav-link,
body.wv-client-theme .dashboard-nav-primary .nav-link.active{
  background:#ff0a46!important;
  border-color:#ff0a46!important;
  color:#ffffff!important;
  box-shadow:0 12px 24px rgba(255,10,70,.22)!important;
}
body.wv-client-theme .dashboard-nav-primary .nav-link:hover i,
body.wv-client-theme .dashboard-nav-primary .nav-item.active .nav-link i,
body.wv-client-theme .dashboard-nav-primary .nav-link.active i{
  background:rgba(255,255,255,.18)!important;
  color:#ffffff!important;
}
body.wv-client-theme .dashboard-content{
  min-height:100vh!important;
}
body.wv-client-theme .dashboard-content .container-fluid{
  padding:22px!important;
}
body.wv-client-theme .dashboard-subheader{
  align-items:center!important;
  background:linear-gradient(135deg,#07152a 0%,#102746 72%,#ff0a46 170%)!important;
  border:0!important;
  border-radius:16px!important;
  box-shadow:0 18px 38px rgba(15,23,42,.14)!important;
  color:#ffffff!important;
  display:flex!important;
  justify-content:space-between!important;
  margin:0 0 18px!important;
  min-height:86px!important;
  padding:20px 22px!important;
}
body.wv-client-theme .dashboard-subheader h1,
body.wv-client-theme .dashboard-subheader h2,
body.wv-client-theme .dashboard-subheader h3{
  color:#ffffff!important;
  font-size:26px!important;
  font-weight:900!important;
  line-height:1.2!important;
  margin:0!important;
}
body.wv-client-theme .dashboard-subheader h1 span,
body.wv-client-theme .dashboard-subheader h2 span,
body.wv-client-theme .dashboard-subheader h3 span{
  color:#7cf9ff!important;
}
body.wv-client-theme .dashboard-content p,
body.wv-client-theme .dashboard-content li,
body.wv-client-theme .dashboard-content td,
body.wv-client-theme .dashboard-content th,
body.wv-client-theme .dashboard-content label,
body.wv-client-theme .dashboard-content small,
body.wv-client-theme .dashboard-content strong,
body.wv-client-theme .dashboard-content span{
  text-rendering:optimizeLegibility;
}
body.wv-client-theme .dashboard-content p,
body.wv-client-theme .dashboard-content li{
  color:#334155!important;
  font-weight:700!important;
}
body.wv-client-theme .dashboard-content small,
body.wv-client-theme .dashboard-content .text-muted{
  color:#64748b!important;
  font-weight:800!important;
}
body.wv-client-theme .dashboard-content .text-danger,
body.wv-client-theme .dashboard-content small.text-danger{
  color:#be123c!important;
  font-weight:900!important;
}
body.wv-client-theme .dashboard-content a:not(.btn):not(.nav-link):not(.wv-quick-link){
  color:#0f4c81!important;
  font-weight:900!important;
}
body.wv-client-theme .dashboard-content .panel,
body.wv-client-theme .dashboard-content .card,
body.wv-client-theme .dashboard-content .well,
body.wv-client-theme .dashboard-content .my-payment-deal-block,
body.wv-client-theme .dashboard-content table.table-users{
  background:#ffffff!important;
  border:1px solid #dfe9f5!important;
  border-radius:14px!important;
  box-shadow:0 12px 26px rgba(15,23,42,.07)!important;
}
body.wv-client-theme .dashboard-content .panel-heading,
body.wv-client-theme .dashboard-content .my-payment-deal-head{
  background:#f8fbff!important;
  border-bottom:1px solid #dfe9f5!important;
  color:#07152a!important;
}
body.wv-client-theme .dashboard-content .my-payment-deal-title,
body.wv-client-theme .dashboard-content .panel-heading h1,
body.wv-client-theme .dashboard-content .panel-heading h2,
body.wv-client-theme .dashboard-content .panel-heading h3{
  color:#07152a!important;
  font-weight:900!important;
}
body.wv-client-theme .dashboard-content .my-payment-deal-meta{
  color:#475569!important;
  font-weight:800!important;
  line-height:1.55!important;
}
body.wv-client-theme .dashboard-content table.table{
  background:#ffffff!important;
  border-collapse:separate!important;
  border-spacing:0!important;
  color:#172033!important;
  margin-bottom:0!important;
  width:100%;
}
body.wv-client-theme .dashboard-content table.table > thead > tr > th{
  background:#07152a!important;
  border-color:#07152a!important;
  color:#ffffff!important;
  font-size:11px!important;
  font-weight:900!important;
  letter-spacing:.25px!important;
  padding:12px 10px!important;
  text-transform:uppercase!important;
  white-space:nowrap!important;
}
body.wv-client-theme .dashboard-content table.table > tbody > tr > td{
  background:#ffffff!important;
  border-color:#edf2f7!important;
  color:#1f2937!important;
  font-size:13px!important;
  font-weight:800!important;
  line-height:1.45!important;
  padding:12px 10px!important;
  vertical-align:middle!important;
}
body.wv-client-theme .dashboard-content table.table > tbody > tr:hover > td{
  background:#f8fbff!important;
}
body.wv-client-theme .dashboard-content table.table strong{
  color:#07152a!important;
  font-weight:900!important;
}
body.wv-client-theme .dashboard-content .btn{
  align-items:center!important;
  border-radius:999px!important;
  box-shadow:0 2px 6px rgba(15,23,42,.08)!important;
  display:inline-flex!important;
  font-size:13px!important;
  font-weight:900!important;
  gap:6px!important;
  justify-content:center!important;
  line-height:1.2!important;
  padding:8px 16px!important;
  text-decoration:none!important;
  transition:all .18s ease!important;
}
body.wv-client-theme .dashboard-content .btn:hover{
  box-shadow:0 6px 14px rgba(15,23,42,.14)!important;
  transform:translateY(-1px)!important;
}
body.wv-client-theme .dashboard-content .btn-xs{
  font-size:11px!important;
  min-height:26px!important;
  padding:4px 10px!important;
}
body.wv-client-theme .dashboard-content .btn-sm{
  font-size:12px!important;
  min-height:32px!important;
  padding:6px 12px!important;
}
body.wv-client-theme .dashboard-content .btn-lg{
  font-size:15px!important;
  padding:12px 24px!important;
}
body.wv-client-theme .dashboard-content .btn-info{
  background:#0f4c81!important;
  border-color:#0f4c81!important;
  color:#ffffff!important;
}
body.wv-client-theme .dashboard-content .btn-success{
  background:#047857!important;
  border-color:#047857!important;
  color:#ffffff!important;
}
body.wv-client-theme .dashboard-content .btn-warning{
  background:#f59e0b!important;
  border-color:#f59e0b!important;
  color:#07152a!important;
}
body.wv-client-theme .dashboard-content .btn-primary{
  background:#ff0a46!important;
  border-color:#ff0a46!important;
  color:#ffffff!important;
}
body.wv-client-theme .dashboard-content .btn-danger{
  background:#dc2626!important;
  border-color:#dc2626!important;
  color:#ffffff!important;
}
body.wv-client-theme .dashboard-content .btn-default{
  background:#ffffff!important;
  border-color:#cbd5e1!important;
  color:#07152a!important;
}
/* Absolute universal high contrast text and icon color guarantee */
body.wv-client-theme .dashboard-content .btn-primary,
body.wv-client-theme .dashboard-content .btn-primary *,
body.wv-client-theme .dashboard-content .btn-danger,
body.wv-client-theme .dashboard-content .btn-danger *,
body.wv-client-theme .dashboard-content .btn-success,
body.wv-client-theme .dashboard-content .btn-success *,
body.wv-client-theme .dashboard-content .btn-info,
body.wv-client-theme .dashboard-content .btn-info *{
  color:#ffffff!important;
}
body.wv-client-theme .dashboard-content .btn-default,
body.wv-client-theme .dashboard-content .btn-default *,
body.wv-client-theme .dashboard-content .btn-warning,
body.wv-client-theme .dashboard-content .btn-warning *{
  color:#07152a!important;
}
.wv-mobile-menu-trigger{
  align-items:center!important;
  background:#07152a!important;
  border:0!important;
  border-radius:8px!important;
  color:#7cf9ff!important;
  cursor:pointer!important;
  display:inline-flex!important;
  font-size:16px!important;
  height:36px!important;
  justify-content:center!important;
  margin-right:10px!important;
  transition:all .18s ease!important;
  width:36px!important;
}
.wv-mobile-menu-trigger:hover,
.wv-mobile-menu-trigger:focus{
  background:#ff0a46!important;
  color:#ffffff!important;
  outline:none!important;
}
@media(min-width:992px){
  .wv-mobile-menu-trigger{
    display:none!important;
  }
}
body.wv-client-theme .dashboard-content .table-responsive,
body.wv-client-theme .dashboard-content .client-scroll-wrap,
body.wv-client-theme .dashboard-content .wv-client-table-wrap{
  background:#ffffff!important;
  border:1px solid #dfe9f5!important;
  border-radius:14px!important;
  box-shadow:0 12px 26px rgba(15,23,42,.07)!important;
  overflow-x:auto!important;
  -webkit-overflow-scrolling:touch!important;
}
@media(min-width:992px){
  body.wv-client-theme .dashboard-wrapper{
    display:flex!important;
    min-height:100vh!important;
  }
  body.wv-client-theme .dashboard-sidebar{
    flex:0 0 260px!important;
    min-height:100vh!important;
    width:260px!important;
  }
  body.wv-client-theme .dashboard-content{
    flex:1 1 auto!important;
    min-width:0!important;
  }
}
@media(max-width:991px){
  body.wv-client-theme .dashboard-title{
    align-items:center!important;
    display:flex!important;
    min-height:60px!important;
    padding:10px 14px 10px 64px!important;
    position:relative!important;
    text-align:left!important;
  }
  body.wv-client-theme .dashboard-title a{
    justify-content:flex-start!important;
  }
  body.wv-client-theme .dashboard-title img{
    max-height:42px!important;
    max-width:118px!important;
  }
  body.wv-client-theme .dashboard-title .navbar-toggler{
    align-items:center!important;
    background:rgba(124,249,255,.14)!important;
    border:1px solid rgba(124,249,255,.28)!important;
    border-radius:11px!important;
    display:inline-flex!important;
    height:40px!important;
    justify-content:center!important;
    left:14px!important;
    padding:0!important;
    position:absolute!important;
    top:50%!important;
    transform:translateY(-50%)!important;
    width:40px!important;
    z-index:1074!important;
  }
  body.wv-client-theme .dashboard-title .navbar-toggler:before{
    color:#8ff7ff!important;
  }
  body.wv-client-theme .dashboard-nav-primary{
    background:#ffffff!important;
    border-right:1px solid #dfe9f5!important;
    box-shadow:12px 0 30px rgba(15,23,42,.18)!important;
    max-width:286px!important;
    padding:10px!important;
    width:78vw!important;
    width:min(78vw,286px)!important;
    z-index:1075!important;
  }
  body.wv-client-theme .dashboard-nav-primary .nav{
    padding:4px!important;
  }
  body.wv-client-theme .dashboard-nav-primary .nav-item{
    margin:0 0 7px!important;
  }
  body.wv-client-theme .dashboard-nav-primary .nav-link{
    background:#f8fbff!important;
    border:1px solid #e4edf7!important;
    color:#07152a!important;
    font-size:12px!important;
    gap:8px!important;
    line-height:1.25!important;
    min-height:42px!important;
    overflow-wrap:anywhere!important;
    padding:9px 10px!important;
    white-space:normal!important;
  }
  body.wv-client-theme .dashboard-nav-primary .nav-link i,
  body.wv-client-theme .dashboard-nav-primary .nav-link span[class^="fa"],
  body.wv-client-theme .dashboard-nav-primary .nav-link span[class*=" fa-"]{
    background:#fff1f4!important;
    color:#ff0a46!important;
    flex:0 0 30px!important;
    height:30px!important;
    width:30px!important;
  }
  body.wv-client-theme .dashboard-nav-primary .nav-link span:not([class^="fa"]):not([class*=" fa-"]){
    flex:1 1 auto!important;
    min-width:0!important;
  }
  body.wv-client-theme .dashboard-content .container-fluid{
    padding:14px!important;
  }
  body.wv-client-theme .dashboard-subheader{
    display:block!important;
    min-height:0!important;
    padding:16px!important;
  }
  body.wv-client-theme .dashboard-subheader h1,
  body.wv-client-theme .dashboard-subheader h2,
  body.wv-client-theme .dashboard-subheader h3{
    font-size:22px!important;
  }
}
@media(max-width:575px){
  body.wv-client-theme .dashboard-nav-primary{
    max-width:274px!important;
    width:82vw!important;
    width:min(82vw,274px)!important;
  }
  body.wv-client-theme .dashboard-content .container-fluid{
    padding:10px!important;
  }
  body.wv-client-theme .dashboard-content .btn{
    display:inline-flex!important;
    justify-content:center!important;
    margin-bottom:5px!important;
    white-space:normal!important;
  }
  body.wv-client-theme .dashboard-content table.table > tbody > tr > td,
  body.wv-client-theme .dashboard-content table.table > thead > tr > th{
    font-size:12px!important;
  }
}
</style>
					
				</div><!-- /.container -->						
			</div><!-- /.dashboard-content -->			
		</div><!-- /.dashboard -->
	</div><!-- /.page-wrapper -->
<div class="modal fade" id="targetModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
<div class="modal-content">        
</div>
</div>
<script type="text/javascript">
(function($){
  function ensureClientTableScroll(){
    var isMobile = window.matchMedia('(max-width: 991px)').matches;
    $('.dashboard-content table.table').each(function(){
      var $table = $(this);
      var $wrap = $table.closest('.table-responsive');
      if(!$wrap.length){
        $table.wrap('<div class="table-responsive client-scroll-wrap"></div>');
        $wrap = $table.closest('.table-responsive');
      }else{
        $wrap.addClass('client-scroll-wrap');
      }
      if(!isMobile){
        $table.css({
          'width': '',
          'max-width': '',
          'min-width': ''
        });
        return;
      }
      var colCount = $table.find('thead th').length;
      if(!colCount){
        colCount = $table.find('tbody tr:first td').length;
      }
      if(colCount >= 2){
        $table.css({
          'width': (colCount * 170) + 'px',
          'max-width': 'none',
          'min-width': '860px'
        });
      }else{
        $table.css({
          'width': '860px',
          'max-width': 'none',
          'min-width': '860px'
        });
      }
    });
  }

  function bindClientSidebarMobile(){
    var $nav = $('.dashboard-nav-primary');
    if(!$nav.length){
      return;
    }
    if(!$('.client-sidebar-backdrop').length){
      $('body').append('<div class="client-sidebar-backdrop"></div>');
    }
    var $backdrop = $('.client-sidebar-backdrop');
    var $toggle = $('.dashboard-title .navbar-toggler, #wvMobileMenuTrigger');

    function isMobileView(){
      return window.matchMedia('(max-width: 991px)').matches;
    }

    function openSidebar(){
      if(!isMobileView()){
        return;
      }
      $nav.addClass('in').attr('aria-expanded','true');
      $toggle.attr('aria-expanded','true');
      $('body').addClass('client-sidebar-open');
    }

    function closeSidebar(){
      $nav.removeClass('in').attr('aria-expanded','false');
      $toggle.attr('aria-expanded','false');
      $('body').removeClass('client-sidebar-open');
    }

    function toggleSidebar(){
      if($('body').hasClass('client-sidebar-open') || $nav.hasClass('in')){
        closeSidebar();
      }else{
        openSidebar();
      }
    }

    $toggle.off('click.clientMobileManual').on('click.clientMobileManual', function(e){
      if(isMobileView()){
        e.preventDefault();
        e.stopImmediatePropagation();
        toggleSidebar();
      }
    });

    if($.fn.collapse){
      $nav.off('shown.bs.collapse.clientMobile hidden.bs.collapse.clientMobile');
      $nav.on('shown.bs.collapse.clientMobile', function(){
        if(window.matchMedia('(max-width: 991px)').matches){
          $('body').addClass('client-sidebar-open');
        }
      });
      $nav.on('hidden.bs.collapse.clientMobile', function(){
        $('body').removeClass('client-sidebar-open');
      });
    }

    $backdrop.off('click.clientMobile').on('click.clientMobile', function(){
      closeSidebar();
    });

    $('.dashboard-nav-primary .nav-link').off('click.clientMobile').on('click.clientMobile', function(){
      if(isMobileView()){
        closeSidebar();
      }
    });

    $(window).off('resize.clientMobile').on('resize.clientMobile', function(){
      if(!isMobileView()){
        closeSidebar();
      }
    });
  }

  $(function(){
    ensureClientTableScroll();
    bindClientSidebarMobile();
    setTimeout(ensureClientTableScroll, 250);
    setTimeout(ensureClientTableScroll, 1000);
  });
  $(window).on('resize orientationchange', function(){
    ensureClientTableScroll();
  });
  $(document).ajaxComplete(function(){
    ensureClientTableScroll();
  });
})(jQuery);
</script>
<style type="text/css">
.wv-client-top-bar {
  display: flex !important;
  align-items: center;
  justify-content: space-between;
  background: #ffffff;
  border: 1px solid #dfe9f5;
  border-radius: 12px;
  padding: 10px 18px;
  margin-bottom: 20px;
  box-shadow: 0 4px 14px rgba(15, 23, 42, 0.04);
}
@media(max-width:767px){
  .wv-client-top-bar {
    padding: 8px 12px !important;
    margin-bottom: 14px !important;
  }
  .wv-portal-badge {
    font-size: 11px !important;
  }
  .wv-client-user-pill {
    display: none !important;
  }
  .wv-notif-menu {
    position: fixed !important;
    top: 56px !important;
    right: 12px !important;
    left: 12px !important;
    width: auto !important;
    max-width: none !important;
  }
}
.wv-client-top-left {
  display: flex;
  align-items: center;
}
.wv-portal-badge {
  font-size: 13px;
  font-weight: 800;
  color: #07152a;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}
.wv-client-top-right {
  display: flex;
  align-items: center;
  gap: 14px;
}
.wv-client-bell-btn {
  position: relative;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: #f1f5f9;
  border: 1px solid #e2e8f0;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  color: #07152a !important;
  font-size: 18px;
  text-decoration: none !important;
  transition: all 0.2s ease;
}
.wv-client-bell-btn:hover {
  background: #fff1f4;
  color: #ff0a46 !important;
  border-color: #ffd6df;
}
.wv-bell-badge {
  position: absolute;
  top: -4px;
  right: -4px;
  background: #ff0a46;
  color: #ffffff;
  font-size: 10px;
  font-weight: 900;
  min-width: 18px;
  height: 18px;
  line-height: 18px;
  border-radius: 999px;
  text-align: center;
  padding: 0 4px;
  box-shadow: 0 2px 6px rgba(255, 10, 70, 0.4);
}
.wv-bell-badge.has-unread {
  animation: pulse-ring 2s infinite;
}
@keyframes pulse-ring {
  0% { transform: scale(1); }
  50% { transform: scale(1.15); }
  100% { transform: scale(1); }
}
.wv-client-user-pill {
  font-size: 13px;
  font-weight: 800;
  color: #334155;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  padding: 6px 14px;
  border-radius: 999px;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}
.wv-notif-menu {
  width: 340px;
  max-width: 90vw;
  border-radius: 14px;
  box-shadow: 0 16px 36px rgba(15, 23, 42, 0.16);
  border: 1px solid #dfe9f5;
  padding: 0;
  margin-top: 10px;
  right: 0;
  left: auto !important;
  overflow: hidden;
  z-index: 1080;
}
.wv-notif-menu-header {
  background: #07152a;
  color: #ffffff;
  padding: 12px 16px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.wv-notif-menu-title {
  font-size: 13px;
  font-weight: 800;
}
.wv-notif-mark-read {
  font-size: 11px;
  color: #7cf9ff !important;
  font-weight: 700;
  text-decoration: none !important;
}
.wv-notif-items-wrap {
  max-height: 320px;
  overflow-y: auto;
  background: #ffffff;
}
.wv-notif-item {
  padding: 12px 14px;
  border-bottom: 1px solid #f1f5f9;
  display: flex;
  gap: 12px;
  align-items: flex-start;
  text-decoration: none !important;
  transition: background 0.15s;
}
.wv-notif-item:hover {
  background: #f8fafc;
}
.wv-notif-item.unread {
  background: #fbfdff;
}
.wv-notif-item-icon {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
  flex: 0 0 32px;
}
.wv-notif-item-body {
  flex: 1 1 auto;
}
.wv-notif-item-title {
  font-size: 12px;
  font-weight: 800;
  color: #07152a;
  margin: 0 0 2px;
}
.wv-notif-item-msg {
  font-size: 11.5px;
  color: #64748b;
  margin: 0 0 4px;
  line-height: 1.35;
}
.wv-notif-item-time {
  font-size: 10px;
  color: #94a3b8;
  font-weight: 700;
}
.wv-notif-menu-footer {
  background: #f8fafc;
  padding: 10px;
  text-align: center;
  border-top: 1px solid #edf2f7;
}
.wv-notif-see-all {
  font-size: 12px;
  font-weight: 800;
  color: #ff0a46 !important;
  text-decoration: none !important;
}
.wv-notif-loading, .wv-notif-none {
  padding: 20px;
  text-align: center;
  font-size: 12px;
  color: #94a3b8;
}
</style>

<script type="text/javascript">
(function($){
  var notifPollUrl = '<?php echo $this->Html->url(array("controller" => "ClientNotifications", "action" => "poll")); ?>';
  var markAllUrl = '<?php echo $this->Html->url(array("controller" => "ClientNotifications", "action" => "mark_all_read")); ?>';

  function fetchNotifications() {
    $.getJSON(notifPollUrl, function(data){
      if (data && data.status === 'success') {
        var count = parseInt(data.unread_count, 10) || 0;
        var $badge = $('#wvBellBadge');
        if (count > 0) {
          $badge.text(count).show().addClass('has-unread');
        } else {
          $badge.text(0).hide().removeClass('has-unread');
        }

        var $wrap = $('#wvNotifItemsWrap');
        if (data.items && data.items.length > 0) {
          var html = '';
          $.each(data.items, function(i, item){
            html += '<a href="' + item.action_url + '" class="wv-notif-item ' + (item.is_read ? '' : 'unread') + '">';
            html += '<div class="wv-notif-item-icon" style="background:' + item.badge_color + '18; color:' + item.badge_color + ';"><i class="fa ' + item.icon + '"></i></div>';
            html += '<div class="wv-notif-item-body">';
            html += '<div class="wv-notif-item-title">' + item.title + '</div>';
            html += '<div class="wv-notif-item-msg">' + item.message + '</div>';
            html += '<div class="wv-notif-item-time">' + item.time_ago + '</div>';
            html += '</div></a>';
          });
          $wrap.html(html);
        } else {
          $wrap.html('<div class="wv-notif-none"><i class="fa fa-bell-slash-o"></i> <?php echo __("No recent notifications"); ?></div>');
        }
      }
    });
  }

  $(function(){
    $('#wvClientNotifDropdown').on('show.bs.dropdown', function(){
      fetchNotifications();
    });

    $('#wvMarkAllReadBtn').on('click', function(e){
      e.preventDefault();
      e.stopPropagation();
      $.post(markAllUrl, function(){
        $('#wvBellBadge').hide().removeClass('has-unread');
        $('.wv-notif-item').removeClass('unread');
      });
    });

    setTimeout(fetchNotifications, 1000);
    setInterval(fetchNotifications, 60000);
  });
})(jQuery);
</script>
</body>   
    
    
</html>
        <?php } else{?>
<!DOCTYPE html>
  <html lang="<?php echo h($configLanguage);?>" dir="<?php echo h($dirType);?>">
    <head>
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
	  <meta name="description" content="<?php echo h($siteDescription);?>" />
	  <meta name="author" content="<?php echo h($siteName);?>" />
	  <meta name="google-translate-customization" content="839d71f7ff6044d0-328a2dc5159d6aa2-gd17de6447c9ba810-f">
	  <?php echo $this->Html->charset();?>
	  <title><?php echo h($siteTitle);?></title>
<?php
$siteFaviconFile = !empty($siteFavicon) ? $siteFavicon : '';
$ogLogo = !empty($frontLogo) ? $frontLogo : 'organisation-logo-20260511101817-recovered.png';
if (strpos($ogLogo, 'http://') === 0 || strpos($ogLogo, 'https://') === 0) {
    $ogImageUrl = $ogLogo;
} else {
    $ogImageUrl = Router::url('/img/' . ltrim($ogLogo, '/'), true);
}
$ogSiteName = !empty($siteName) ? $siteName : 'White Villas';
$ogPageTitle = !empty($title_for_layout) ? ($title_for_layout . ' — ' . $ogSiteName) : (!empty($siteTitle) ? $siteTitle : $ogSiteName);
$ogPageDesc = !empty($siteDescription) ? $siteDescription : 'White Villas — Premier Real Estate, Luxury Lodges & Verified Investment Opportunities.';
$ogPageUrl = Router::url(null, true);
?>
    <!-- Favicon (Browser Tab Icon) -->
    <?php if (!empty($siteFaviconFile)): ?>
    <link rel="icon" type="image/x-icon" href="<?php echo $this->Html->url('/img/' . $siteFaviconFile); ?>">
    <link rel="shortcut icon" href="<?php echo $this->Html->url('/img/' . $siteFaviconFile); ?>">
    <link rel="apple-touch-icon" href="<?php echo $this->Html->url('/img/' . $siteFaviconFile); ?>">
    <?php else: ?>
    <link rel="icon" type="image/x-icon" href="<?php echo $this->Html->url('/favicon.ico'); ?>">
    <?php endif; ?>

    <!-- Social Media Sharing & Open Graph Meta Tags (WhatsApp, Facebook, Twitter, LinkedIn) -->
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="<?php echo h($ogSiteName); ?>">
    <meta property="og:title" content="<?php echo h($ogPageTitle); ?>">
    <meta property="og:description" content="<?php echo h($ogPageDesc); ?>">
    <meta property="og:image" content="<?php echo h($ogImageUrl); ?>">
    <meta property="og:image:secure_url" content="<?php echo h($ogImageUrl); ?>">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="<?php echo h($ogSiteName); ?>">
    <meta property="og:url" content="<?php echo h($ogPageUrl); ?>">

    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo h($ogPageTitle); ?>">
    <meta name="twitter:description" content="<?php echo h($ogPageDesc); ?>">
    <meta name="twitter:image" content="<?php echo h($ogImageUrl); ?>">
	<meta name="description" content="<?php echo h($siteDescription);?>"/>
		<?php echo $this->Html->meta('icon');
		echo $this->Html->css('https://fonts.googleapis.com/css?family=Raleway:300,400,500,600,300,700&subset=latin,latin-ext');
		echo $this->Html->css('bootstrap.min');
		if(!empty($wvLayoutNeedsCarousel)){ echo $this->Html->css('/design1000/assets/libraries/owl-carousel/owl.carousel.css'); }
		echo $this->Html->css('font-awesome.min');
		if(!empty($wvLayoutNeedsLeaflet)){
		    echo $this->Html->css('/design1000/assets/css/leaflet.css');
		}
		if(!empty($wvLayoutNeedsPopup)){ echo $this->Html->css('/design1000/assets/css/magnific-popup.css'); }
		echo $this->Html->css('/design1000/assets/css/villareal-turquoise.css');
		echo $this->Html->css('/design1000/assets/css/style.css');
		if(!empty($wvLayoutNeedsValidation)){ echo $this->Html->css('validationEngine.jquery'); }
		echo $this->Html->css('wv-theme-tokens');
		echo $this->Html->css('wv-portal-theme');
		if(!empty($themeDynamicCss)){ echo '<style type="text/css" id="wv-dynamic-theme">'.$themeDynamicCss.'</style>'; }
		echo $this->fetch('meta');		
		echo $this->fetch('css');
		echo $this->Html->script('/design1000/assets/js/jquery.js');
		if(!empty($wvLayoutNeedsValidation)){
		    echo $this->Html->script('jquery.validationEngine-en');
                    echo $this->Html->script('jquery.validationEngine');
                }
		echo $this->Html->script('/design1000/assets/js/tether.min.js');
		echo $this->Html->script('/design1000/assets/js/bootstrap.min.js');		
		if(!empty($wvLayoutNeedsLeaflet)){
		    echo $this->Html->script('/design1000/assets/js/leaflet.js');
		}
		if(!empty($wvLayoutNeedsCarousel)){ echo $this->Html->script('/design1000/assets/libraries/owl-carousel/owl.carousel.min.js'); }
		if(!empty($wvLayoutNeedsPopup)){ echo $this->Html->script('/design1000/assets/js/jquery.magnific-popup.min.js'); }
		echo $this->Html->script('/design1000/assets/js/custom.js');
		echo $this->Html->script('waiting-dialog.min');
		echo $this->Html->script('custom.min');
		echo $this->Html->script('wv-theme');
		echo $this->Html->script("langs/$configLanguage");
		echo $this->fetch('script');
                echo $this->Js->writeBuffer();
?>   
<style type="text/css">
body.header-sticky,
body.header-transparent{
  background:#f7f9fc;
  color:#1f2937;
}
.header-wrapper .header{
  background:#fff;
  box-shadow:0 10px 26px rgba(15,23,42,.08);
}
.header-wrapper .header,
.header-wrapper .header.header-small,
.header-wrapper .header.affix,
.header-wrapper .header.is-sticky,
.header-wrapper .header.sticky,
.header-wrapper .header.scrolled{
  min-height:auto!important;
  transform:none!important;
}
.header-wrapper .header.header-small .header-top,
.header-wrapper .header.affix .header-top,
.header-wrapper .header.is-sticky .header-top,
.header-wrapper .header.sticky .header-top,
.header-wrapper .header.scrolled .header-top{
  padding:14px 0!important;
}
.header-wrapper .header.header-small .header-logo img,
.header-wrapper .header.affix .header-logo img,
.header-wrapper .header.is-sticky .header-logo img,
.header-wrapper .header.sticky .header-logo img,
.header-wrapper .header.scrolled .header-logo img{
  height:40px!important;
  max-height:56px!important;
}
.header-wrapper .header.header-small .nav-primary .nav-link,
.header-wrapper .header.affix .nav-primary .nav-link,
.header-wrapper .header.is-sticky .nav-primary .nav-link,
.header-wrapper .header.sticky .nav-primary .nav-link,
.header-wrapper .header.scrolled .nav-primary .nav-link{
  padding:13px 10px!important;
}
.header-wrapper .nav-topbar{
  align-items:center;
  background:linear-gradient(135deg,#07152a 0%,#102746 72%,#ff0a46 170%);
  border-bottom:0;
  display:flex;
  flex-wrap:wrap;
  gap:8px;
  justify-content:flex-end;
  margin:0;
  min-height:40px;
  padding:7px 20px;
}
.header-wrapper .nav-topbar .wv-topbar-menu-toggle{
  display:none;
}
.header-wrapper .nav-topbar .wv-topbar-menu-button{
  align-items:center;
  background:rgba(124,249,255,.13)!important;
  border:1px solid rgba(124,249,255,.25)!important;
  border-radius:9px!important;
  color:#ffffff!important;
  display:inline-flex;
  height:32px;
  justify-content:center;
  padding:0!important;
  width:34px;
}
.header-wrapper .nav-topbar .wv-topbar-menu-button i{
  color:#8ff7ff!important;
}
.header-wrapper .nav-topbar .nav-link,
.header-wrapper .nav-topbar a{
  color:#e5f6ff!important;
  font-size:12px;
  font-weight:800;
  padding:4px 7px;
}
.header-wrapper .nav-topbar a:hover{
  color:#8ff7ff!important;
}
.header-wrapper .nav-topbar .wv-top-contact{
  align-items:center;
  color:#fff;
  display:flex;
  flex-wrap:nowrap;
  font-size:12px;
  font-weight:900;
  gap:5px;
  margin-right:auto;
  padding:4px 10px 4px 0;
  white-space:nowrap;
}
.header-wrapper .nav-topbar .wv-top-contact a{
  color:#fff!important;
  padding:0;
}
.header-wrapper .nav-topbar .wv-top-address{
  color:#fff;
  display:inline-block;
  max-width:360px;
  overflow:hidden;
  padding:0;
  text-overflow:ellipsis;
  text-decoration:none!important;
  vertical-align:middle;
}
.header-wrapper .nav-topbar .wv-top-address:hover{
  color:#8ff7ff!important;
}
.header-wrapper .nav-topbar .wv-top-contact i{
  color:#8ff7ff;
  margin-right:5px;
}
.header-wrapper .nav-topbar .wv-top-divider{
  color:rgba(255,255,255,.35);
  margin:0 10px;
}
.header-wrapper .nav-topbar .wv-front-profile{
  align-items:center;
  display:flex;
  padding:0 4px;
}
.header-wrapper .nav-topbar .wv-front-profile-link{
  align-items:center;
  background:rgba(255,255,255,.10);
  border:1px solid rgba(255,255,255,.18);
  border-radius:999px;
  display:flex;
  gap:8px;
  min-height:34px;
  padding:3px 9px 3px 3px!important;
  text-decoration:none!important;
}
.header-wrapper .nav-topbar .wv-front-profile-link:hover{
  background:rgba(255,255,255,.18);
}
.header-wrapper .nav-topbar .wv-front-profile-photo{
  background-color:#fff;
  background-position:center;
  background-repeat:no-repeat;
  background-size:cover;
  border:2px solid rgba(255,255,255,.85);
  border-radius:50%;
  box-shadow:0 6px 14px rgba(2,8,23,.22);
  display:inline-block;
  height:30px;
  width:30px;
}
.header-wrapper .nav-topbar .wv-front-profile-name{
  color:#fff;
  display:inline-block;
  font-size:12px;
  font-weight:900;
  max-width:120px;
  overflow:hidden;
  text-overflow:ellipsis;
  white-space:nowrap;
}
.header-wrapper .nav-topbar .wv-front-profile-caret{
  color:#8ff7ff;
  font-size:10px;
}
.wv-mobile-front-auth{
  display:none;
}
.header-inner{
  background:#fff;
  border-bottom:3px solid #ff0a46;
}
.header-wrapper{
  margin-bottom:0!important;
}
.header-wrapper + .main-wrapper,
.page-wrapper > .main-wrapper{
  margin-top:0!important;
  padding-top:0!important;
}
.main-wrapper .main,
.main-wrapper .main-inner,
.main-wrapper .content{
  margin-top:0!important;
  padding-top:0!important;
}
.header-inner > .container{
  align-items:center;
  display:flex;
  gap:26px;
  min-height:56px;
}
.header-top{
  flex:0 0 300px;
  padding:8px 0;
}
.header-top-inner{
  align-items:center;
  display:flex;
}
.header-bottom{
  background:#fff;
  border:0;
  flex:1 1 auto;
  min-width:0;
}
.header-bottom-inner{
  align-items:center;
  display:flex;
  justify-content:flex-end;
  min-height:56px;
}
.nav-primary-wrapper{
  display:block!important;
  height:auto!important;
  visibility:visible!important;
  width:100%;
}
.nav-primary{
  align-items:center;
  display:flex;
  flex-wrap:nowrap;
  gap:0;
  justify-content:flex-end;
  margin:0;
  white-space:nowrap;
  width:100%;
}
.nav-primary .nav-item{
  display:block;
  float:none;
  margin:0!important;
}
.nav-primary .nav-brand-item{
  margin-right:auto!important;
}
.nav-primary .nav-brand-link{
  align-items:center;
  display:flex!important;
  gap:10px;
  min-height:56px;
  padding:6px 18px 6px 0!important;
  text-decoration:none!important;
  white-space:nowrap;
}
.nav-primary .nav-brand-link:hover{
  background:transparent!important;
}
.nav-primary .nav-brand-link img{
  display:block!important;
  height:auto!important;
  max-height:44px!important;
  max-width:105px!important;
  object-fit:contain;
}
.nav-primary .nav-brand-text{
  color:#07152a!important;
  display:inline-block!important;
  font-size:13px;
  font-weight:900;
  line-height:1.1;
  text-transform:uppercase;
}
.header-logo img{
  display:block!important;
  height:auto!important;
  max-height:56px;
}
.header-logo{
  align-items:center;
  display:flex!important;
  gap:10px;
  justify-content:flex-start;
  max-width:none!important;
  min-height:48px;
  min-width:280px;
  overflow:visible!important;
  text-indent:0!important;
  text-decoration:none!important;
  white-space:nowrap!important;
  width:280px!important;
}
.header-logo .organization-logo{
  display:block!important;
  float:none!important;
  height:auto!important;
  max-height:46px!important;
  max-width:105px!important;
  min-width:70px;
  object-fit:contain;
  opacity:1!important;
  position:static!important;
  visibility:visible!important;
}
.header-logo-text{
  color:#07152a!important;
  display:inline-block!important;
  font-size:13px;
  font-weight:900;
  line-height:1.1;
  opacity:1!important;
  overflow:visible!important;
  position:static!important;
  text-transform:uppercase;
  visibility:visible!important;
}
.header-information{
  display:none!important;
}
.header-information i{
  align-items:center;
  background:#fff1f4;
  border-radius:50%;
  color:#ff0a46;
  display:inline-flex;
  height:38px;
  justify-content:center;
  width:38px;
}
.header-information strong{
  color:#07152a;
  font-weight:900;
}
.header-information span{
  color:#64748b;
  font-weight:700;
}
.nav-primary .nav-link{
  color:#07152a!important;
  font-size:12px;
  font-weight:900;
  letter-spacing:0;
  border-radius:0;
  padding:18px 9px!important;
  text-transform:uppercase;
}
.nav-primary .nav-link.active,
.nav-primary .nav-link:hover{
  background:#fff1f4!important;
  color:#ff0a46!important;
}
@media(max-width:767px){
  .header-wrapper .nav-topbar{
    justify-content:center;
    padding:7px 10px;
  }
  .header-wrapper .nav-topbar .wv-top-contact{
    flex:0 1 auto;
    margin-right:0;
    text-align:center;
    white-space:nowrap;
  }
  .header-wrapper .nav-topbar .wv-top-divider{
    margin:0 6px;
  }
  .header-information{
    display:none;
  }
  .header-inner > .container{
    display:block;
    min-height:auto;
  }
  .header-top-inner{
    justify-content:space-between;
  }
  .header-top{
    padding:10px 0;
  }
  .header-bottom-inner{
    display:block;
    min-height:auto;
  }
  .nav-primary-wrapper.collapse{
    display:none!important;
  }
  .nav-primary-wrapper.collapse.in,
  .nav-primary-wrapper.collapsing{
    display:block!important;
  }
  .nav-primary{
    display:block;
    white-space:normal;
  }
  .nav-primary .nav-link{
    padding:10px 12px!important;
  }
}
@media(min-width:768px){
  .header-inner > .container{
    display:block;
  }
  .header-top{
    display:none!important;
  }
  .header-bottom-inner{
    justify-content:flex-start;
  }
}
@media(max-width:991px){
  body.wv-front-theme{
    overflow-x:hidden;
  }
  body.wv-front-theme .header-wrapper .nav-topbar{
    display:flex!important;
    flex-wrap:nowrap!important;
    gap:7px!important;
    justify-content:space-between!important;
    min-height:42px!important;
    overflow-x:hidden!important;
    overflow-y:hidden!important;
    padding:6px 8px!important;
    white-space:nowrap!important;
    -webkit-overflow-scrolling:touch!important;
  }
  body.wv-front-theme .header-wrapper .nav-topbar::-webkit-scrollbar{
    display:none;
  }
  body.wv-front-theme .header-wrapper .nav-topbar .nav-item{
    flex:0 0 auto!important;
  }
  body.wv-front-theme .header-wrapper .nav-topbar .wv-topbar-menu-toggle{
    display:flex!important;
    order:1!important;
  }
  body.wv-front-theme .header-wrapper .nav-topbar .wv-topbar-menu-button{
    height:32px!important;
    margin:0!important;
    min-width:34px!important;
    pointer-events:auto!important;
    position:static!important;
    transform:none!important;
    width:34px!important;
    z-index:1090!important;
  }
  body.wv-front-theme .header-wrapper .nav-topbar .wv-topbar-extra,
  body.wv-front-theme .header-wrapper .nav-topbar .wv-topbar-register{
    display:none!important;
  }
  body.wv-front-theme .header-wrapper .nav-topbar .wv-top-contact{
    flex:1 1 auto!important;
    margin-right:0!important;
    min-width:0!important;
    order:2!important;
    overflow:hidden!important;
    padding:0!important;
    text-overflow:ellipsis!important;
  }
  body.wv-front-theme .header-wrapper .nav-topbar .wv-top-contact a{
    font-size:11px!important;
    max-width:115px!important;
    overflow:hidden!important;
    text-overflow:ellipsis!important;
    vertical-align:middle!important;
  }
  body.wv-front-theme .header-wrapper .nav-topbar .wv-top-address{
    font-size:11px!important;
    max-width:170px!important;
    overflow:hidden!important;
    text-overflow:ellipsis!important;
    vertical-align:middle!important;
  }
  body.wv-front-theme .header-wrapper .nav-topbar .wv-top-divider{
    margin:0 4px!important;
  }
  body.wv-front-theme .header-wrapper .nav-topbar .wv-topbar-auth,
  body.wv-front-theme .header-wrapper .nav-topbar .wv-front-profile{
    display:flex!important;
    margin-left:auto!important;
    order:3!important;
  }
  body.wv-front-theme .header-wrapper .nav-topbar .wv-topbar-auth a{
    align-items:center!important;
    background:rgba(255,255,255,.12)!important;
    border:1px solid rgba(255,255,255,.18)!important;
    border-radius:999px!important;
    color:#ffffff!important;
    display:inline-flex!important;
    font-size:11px!important;
    font-weight:900!important;
    min-height:30px!important;
    padding:5px 10px!important;
  }
  body.wv-front-theme .header-wrapper .nav-topbar .wv-front-profile-name,
  body.wv-front-theme .header-wrapper .nav-topbar .wv-front-profile-caret{
    display:none!important;
  }
  body.wv-front-theme .header-wrapper .nav-topbar .wv-front-profile-link{
    min-height:32px!important;
    padding:1px!important;
  }
  body.wv-front-theme .header-wrapper .nav-topbar .wv-front-profile-photo{
    height:30px!important;
    width:30px!important;
  }
  body.wv-front-theme .header-wrapper .header{
    position:sticky!important;
    top:0!important;
    z-index:1095!important;
  }
  body.wv-front-theme .header-inner{
    border-bottom:2px solid #ff0a46;
    box-shadow:0 8px 22px rgba(15,23,42,.10);
  }
  body.wv-front-theme .header-inner > .container{
    display:block!important;
    min-height:auto!important;
  }
  body.wv-front-theme .header-top{
    display:block!important;
    flex:0 0 auto!important;
    padding:8px 0!important;
    width:100%!important;
  }
  body.wv-front-theme .header-top-inner{
    align-items:center!important;
    display:flex!important;
    gap:12px!important;
    justify-content:center!important;
    min-height:48px!important;
    position:relative!important;
  }
  body.wv-front-theme .header-logo{
    align-items:center!important;
    display:flex!important;
    flex:0 1 auto!important;
    justify-content:center!important;
    min-height:44px!important;
    min-width:0!important;
    text-align:center!important;
    width:100%!important;
    z-index:1!important;
  }
  body.wv-front-theme .header-logo .organization-logo,
  body.wv-front-theme .header-logo img{
    background:#ffffff!important;
    border-radius:8px!important;
    display:block!important;
    height:auto!important;
    max-height:42px!important;
    max-width:128px!important;
    object-fit:contain!important;
    opacity:1!important;
    position:relative!important;
    text-indent:0!important;
    visibility:visible!important;
    z-index:2!important;
  }
  body.wv-front-theme .header-top-inner > .navbar-toggler{
    align-items:center!important;
    background:#07152a!important;
    border:1px solid rgba(124,249,255,.26)!important;
    border-radius:12px!important;
    box-shadow:0 10px 24px rgba(15,23,42,.18)!important;
    color:#ffffff!important;
    display:inline-flex!important;
    flex:0 0 42px!important;
    height:40px!important;
    justify-content:center!important;
    line-height:1!important;
    left:0!important;
    margin:0!important;
    padding:0!important;
    pointer-events:auto!important;
    position:absolute!important;
    right:auto!important;
    top:50%!important;
    transform:translateY(-50%)!important;
    width:42px!important;
    z-index:1090!important;
  }
  body.wv-front-theme .header-top-inner > .navbar-toggler span{
    display:none!important;
  }
  body.wv-front-theme .header-top-inner > .navbar-toggler:before{
    color:#8ff7ff;
    content:"\f0c9";
    font-family:FontAwesome;
    font-size:18px;
  }
  body.wv-front-theme .wv-mobile-front-auth{
    align-items:center!important;
    background:#07152a!important;
    border:1px solid rgba(124,249,255,.26)!important;
    border-radius:12px!important;
    box-shadow:0 10px 24px rgba(15,23,42,.16)!important;
    color:#ffffff!important;
    display:inline-flex!important;
    height:40px!important;
    justify-content:center!important;
    min-width:42px!important;
    padding:0 10px!important;
    position:absolute!important;
    right:0!important;
    text-decoration:none!important;
    top:50%!important;
    transform:translateY(-50%)!important;
    z-index:1090!important;
  }
  body.wv-front-theme .header-top-inner > .navbar-toggler,
  body.wv-front-theme .wv-mobile-front-auth{
    display:none!important;
  }
  body.wv-front-theme .wv-mobile-front-auth i{
    color:#8ff7ff!important;
    font-size:15px!important;
  }
  body.wv-front-theme .wv-mobile-front-auth-label{
    color:#ffffff!important;
    display:inline-block!important;
    font-size:11px!important;
    font-weight:900!important;
    margin-left:6px!important;
  }
  body.wv-front-theme .wv-mobile-front-auth-photo{
    background-color:#ffffff!important;
    background-position:center!important;
    background-repeat:no-repeat!important;
    background-size:cover!important;
    border:2px solid rgba(255,255,255,.85)!important;
    border-radius:50%!important;
    display:inline-block!important;
    height:30px!important;
    width:30px!important;
  }
  body.wv-front-theme .header-bottom{
    display:block!important;
    min-height:0!important;
  }
  body.wv-front-theme .header-bottom-inner{
    display:block!important;
    min-height:0!important;
  }
  body.wv-front-theme .nav-primary-wrapper{
    background:#ffffff!important;
    border-right:1px solid #dce7f3;
    bottom:0;
    box-shadow:18px 0 44px rgba(2,8,23,.22);
    display:block!important;
    height:100vh!important;
    height:100dvh!important;
    left:0;
    max-height:none!important;
    max-width:320px;
    overflow-x:hidden;
    overflow-y:auto;
    padding:56px 12px 12px;
    position:fixed!important;
    right:auto;
    top:0;
    transform:translateX(-105%);
    transition:transform .24s ease;
    visibility:visible!important;
    width:86vw;
    z-index:1100;
    pointer-events:none;
    -webkit-overflow-scrolling:touch;
  }
  body.wv-front-theme.front-mobile-menu-open .nav-primary-wrapper,
  body.wv-front-theme .nav-primary-wrapper.in{
    pointer-events:auto;
    transform:translateX(0);
  }
  body.wv-front-theme .nav-primary{
    display:block!important;
    margin:0!important;
    padding:0!important;
    white-space:normal!important;
  }
  body.wv-front-theme .nav-primary .nav-item{
    display:block!important;
    margin:0 0 8px!important;
    width:100%!important;
  }
  body.wv-front-theme .nav-primary .nav-brand-item{
    border-bottom:1px solid #e6eef7;
    margin:0 0 14px!important;
    padding:6px 0 16px;
  }
  body.wv-front-theme .nav-primary .nav-brand-link{
    justify-content:flex-start!important;
    min-height:54px!important;
    padding:6px 0!important;
  }
  body.wv-front-theme .nav-primary .nav-brand-link img{
    max-height:48px!important;
    max-width:136px!important;
    object-fit:contain!important;
  }
  body.wv-front-theme .nav-primary .nav-link{
    align-items:center!important;
    background:#f8fbff!important;
    border:1px solid #e4edf7!important;
    border-radius:12px!important;
    color:#07152a!important;
    display:flex!important;
    font-size:13px!important;
    font-weight:900!important;
    min-height:44px!important;
    padding:11px 12px!important;
    text-transform:none!important;
    width:100%!important;
  }
  body.wv-front-theme .nav-primary .nav-link.active,
  body.wv-front-theme .nav-primary .nav-link:hover{
    background:#07152a!important;
    border-color:#07152a!important;
    color:#ffffff!important;
  }
  body.wv-front-theme .front-sidebar-backdrop{
    background:rgba(2,8,23,.50);
    bottom:0;
    display:none;
    left:0;
    position:fixed;
    right:0;
    top:0;
    z-index:1085;
  }
  body.wv-front-theme.front-mobile-menu-open .front-sidebar-backdrop{
    display:block;
  }
  body.wv-front-theme.front-mobile-menu-open{
    overflow:hidden!important;
  }
}
.content-title{
  background:linear-gradient(135deg,#07152a 0%,#102746 65%,#ff0a46 155%);
}
.content-title h1{
  color:#fff;
  font-weight:900;
}
.footer-wrapper{
  background:linear-gradient(135deg,#07152a 0%,#0f1f36 70%,#102746 100%);
  color:#cbd5e1;
  margin-top:0;
}
.footer-wrapper a{
  color:#8ff7ff!important;
}
.wv-site-footer{
  border-bottom:1px solid rgba(255,255,255,.10);
  padding:34px 0 26px;
}
.wv-site-footer-grid{
  display:grid;
  gap:24px;
  grid-template-columns:minmax(0,1.4fr) minmax(180px,.8fr) minmax(220px,1fr);
}
.wv-footer-brand{
  max-width:430px;
}
.wv-footer-logo{
  align-items:center;
  display:flex;
  gap:12px;
  margin-bottom:14px;
}
.wv-footer-logo img{
  background:#fff;
  border-radius:10px;
  box-shadow:0 12px 28px rgba(2,8,23,.22);
  display:block;
  max-height:62px;
  max-width:135px;
  object-fit:contain;
  padding:8px;
}
.wv-footer-logo strong{
  color:#fff;
  display:block;
  font-size:18px;
  font-weight:900;
  line-height:1.2;
}
.wv-footer-logo span{
  color:#7cf9ff;
  display:block;
  font-size:11px;
  font-weight:900;
  letter-spacing:.8px;
  margin-top:4px;
  text-transform:uppercase;
}
.wv-footer-about{
  color:#b7c5d8;
  font-size:13px;
  font-weight:700;
  line-height:1.7;
  margin:0;
}
.wv-footer-title{
  color:#fff;
  font-size:15px;
  font-weight:900;
  margin:0 0 14px;
  padding-bottom:10px;
  position:relative;
}
.wv-footer-title:after{
  background:#ff0a46;
  border-radius:999px;
  bottom:0;
  content:"";
  height:3px;
  left:0;
  position:absolute;
  width:42px;
}
.wv-footer-links{
  display:grid;
  gap:8px;
  list-style:none;
  margin:0;
  padding:0;
}
.wv-footer-links a{
  align-items:center;
  color:#d8e3f2!important;
  display:inline-flex;
  font-size:13px;
  font-weight:800;
  gap:8px;
  text-decoration:none!important;
}
.wv-footer-links a:before{
  color:#ff0a46;
  content:"\f105";
  font-family:FontAwesome;
}
.wv-footer-links a:hover{
  color:#7cf9ff!important;
}
.wv-footer-contact-list{
  display:grid;
  gap:10px;
}
.wv-footer-contact-item{
  align-items:flex-start;
  background:rgba(255,255,255,.055);
  border:1px solid rgba(255,255,255,.09);
  border-radius:9px;
  display:flex;
  gap:10px;
  padding:11px;
}
.wv-footer-contact-item i{
  align-items:center;
  background:#ff0a46;
  border-radius:8px;
  color:#fff;
  display:flex;
  flex:0 0 32px;
  height:32px;
  justify-content:center;
  width:32px;
}
.wv-footer-contact-item strong{
  color:#7cf9ff;
  display:block;
  font-size:10px;
  font-weight:900;
  letter-spacing:.4px;
  text-transform:uppercase;
}
.wv-footer-contact-item span,
.wv-footer-contact-item a{
  color:#fff!important;
  display:block;
  font-size:12px;
  font-weight:800;
  line-height:1.45;
  text-decoration:none!important;
  word-break:break-word;
}
.footer-bottom{
  display:flex;
  justify-content:center;
  padding:14px 0;
  text-align:center;
}
.footer-left{
  color:#cbd5e1;
  display:block;
  float:none!important;
  font-size:12px;
  font-weight:800;
  line-height:1.7;
  margin:0 auto;
  text-align:center!important;
  width:100%;
}
@media(max-width:991px){
  .wv-site-footer-grid{
    grid-template-columns:1fr;
  }
}
@media(max-width:575px){
  .wv-footer-logo{
    align-items:flex-start;
    display:block;
  }
  .wv-footer-logo img{
    margin-bottom:12px;
  }
}
.btn-primary,
.btn-two{
  background:#ff0a46!important;
  border-color:#ff0a46!important;
  color:#fff!important;
  font-weight:900;
}
.btn-danger{
  background:#07152a!important;
  border-color:#07152a!important;
}
.form-control{
  border-color:#d8e0ea;
  box-shadow:none;
}
</style>
	
</head>
    
<body class="header-sticky wv-front-theme">
<div class="page-wrapper">
  <?php if(true){?>
  <div class="header-wrapper">
	<div class="header">
	<ul class="nav nav-pills nav-topbar">
                            <li class="nav-item wv-topbar-menu-toggle">
                                <button class="navbar-toggler wv-topbar-menu-button" type="button" data-toggle="collapse" data-target=".nav-primary-wrapper" aria-label="<?php echo h(__('Toggle Menu'));?>">
                                    <i class="fa fa-bars"></i>
                                </button>
                            </li>
                            <li class="nav-item wv-top-contact">
                                <i class="fa fa-phone"></i> <a href="tel:<?php echo h(preg_replace('/[^0-9\+]/','',$contact));?>"><?php echo h($contact);?></a>
                                <span class="wv-top-divider">|</span>
                                <i class="fa fa-envelope"></i> <a href="mailto:<?php echo h($siteEmail);?>"><?php echo h($siteEmail);?></a>
                                <?php if(!empty($siteAddress)){ $siteAddressMapUrl='https://www.google.com/maps/search/?api=1&query='.rawurlencode($siteAddress); ?><span class="wv-top-divider">|</span><i class="fa fa-map-marker"></i> <a class="wv-top-address" href="<?php echo h($siteAddressMapUrl);?>" target="_blank" rel="noopener"><?php echo h($siteAddress);?></a><?php } ?>
                            </li>
							<li class="nav-item wv-topbar-extra"><?php echo $this->Html->link('<i class="fa fa-heart"></i> '.__('Favourite Properties'),array('controller'=>'Favouriteproperties','action'=>'index'),array('escape'=>false));?></li>
							<li class="nav-item wv-topbar-extra"><?php echo $this->Html->link('<i class="fa fa-balance-scale"></i> '.__('Compare Properties'),array('controller'=>'Compareproperties','action'=>'index'),array('escape'=>false));?></li>
							<?php if(!$this->Session->check('frontUser') && !$this->Session->check('User')){ if($frontRegistration){?><li class="nav-item wv-topbar-extra wv-topbar-register"><?php echo $this->Html->link('<i class="fa fa-user-plus"></i> '.__('Register'),array('controller'=>'Registers','action'=>'index'),array('escape'=>false));?></li><?php }?>
							<li class="nav-item wv-topbar-auth"><?php echo $this->Html->link('<i class="fa fa-lock"></i> '.__('Login'),array('plugin'=>false,'admin'=>false,'controller'=>'Clients','action'=>'login'),array('escape'=>false));?></li><?php }else{
                                $frontProfilePhotoBase = $this->Html->url(array('controller'=>'img'));
                                $frontProfilePhoto = $frontProfilePhotoBase.'/user-icon.png';
                                $frontProfileName = __('Dashboard');
                                $frontProfileLink = $this->Html->url(array('plugin'=>false,'admin'=>false,'controller'=>'Dashboards','action'=>'index'));
                                if($this->Session->check('frontUser') && (!$this->Session->check('User') || $this->Session->check('Impersonation.front')))
                                {
                                    $frontHeaderUser = (array)$this->Session->read('frontUser');
                                    $frontHeaderClient = !empty($frontHeaderUser['Client']) && is_array($frontHeaderUser['Client']) ? $frontHeaderUser['Client'] : array();
                                    $frontProfileName = !empty($frontHeaderClient['name']) ? $frontHeaderClient['name'] : __('Client Dashboard');
                                    if(!empty($frontClientPhotoUrl))
                                    {
                                        $frontProfilePhoto = $frontClientPhotoUrl;
                                    }
                                    elseif(!empty($frontHeaderClient['photo']))
                                    {
                                        $frontProfileDir = !empty($frontHeaderClient['dir']) ? $frontHeaderClient['dir'] : (!empty($frontHeaderClient['dirt']) ? $frontHeaderClient['dirt'] : 'client');
                                        $frontProfilePhoto = $frontProfilePhotoBase.'/'.trim($frontProfileDir).'_thumb/'.$frontHeaderClient['photo'];
                                    }
                                }
                                elseif($this->Session->check('User'))
                                {
                                    $frontHeaderAdmin = (array)$this->Session->read('User');
                                    $frontHeaderAdminUser = !empty($frontHeaderAdmin['User']) && is_array($frontHeaderAdmin['User']) ? $frontHeaderAdmin['User'] : array();
                                    $frontHeaderAdminGroup = !empty($frontHeaderAdmin['Ugroup']['name']) ? $frontHeaderAdmin['Ugroup']['name'] : __('Admin');
                                    $frontProfileName = !empty($frontHeaderAdminUser['name']) ? $frontHeaderAdminUser['name'] : $frontHeaderAdminGroup;
                                    $frontProfileLink = $this->Html->url('/admin/Dashboards');
                                    if(!empty($frontHeaderAdminUser['photo']))
                                    {
                                        $frontProfilePhoto = $frontProfilePhotoBase.'/user_thumb/'.$frontHeaderAdminUser['photo'];
                                    }
                                }
                                elseif($this->Session->check('frontUser'))
                                {
                                    $frontHeaderUser = (array)$this->Session->read('frontUser');
                                    $frontHeaderClient = !empty($frontHeaderUser['Client']) && is_array($frontHeaderUser['Client']) ? $frontHeaderUser['Client'] : array();
                                    $frontProfileName = !empty($frontHeaderClient['name']) ? $frontHeaderClient['name'] : __('Client Dashboard');
                                    if(!empty($frontClientPhotoUrl))
                                    {
                                        $frontProfilePhoto = $frontClientPhotoUrl;
                                    }
                                    elseif(!empty($frontHeaderClient['photo']))
                                    {
                                        $frontProfileDir = !empty($frontHeaderClient['dir']) ? $frontHeaderClient['dir'] : (!empty($frontHeaderClient['dirt']) ? $frontHeaderClient['dirt'] : 'client');
                                        $frontProfilePhoto = $frontProfilePhotoBase.'/'.trim($frontProfileDir).'_thumb/'.$frontHeaderClient['photo'];
                                    }
                                }
                            ?>
                            <li class="nav-item wv-front-profile">
                                <a class="wv-front-profile-link" href="<?php echo h($frontProfileLink);?>" title="<?php echo h($frontProfileName);?>">
                                    <span class="wv-front-profile-photo" style="background-image:url('<?php echo h($frontProfilePhoto);?>');"></span>
                                    <span class="wv-front-profile-name"><?php echo h($frontProfileName);?></span>
                                    <span class="fa fa-chevron-down wv-front-profile-caret"></span>
                                </a>
                            </li>
                            <?php }?>
						</ul>
		<div class="header-inner">
			<div class="container">
					<div class="header-top">
					<div class="header-top-inner">
						<a class="header-logo" href="<?php echo $this->Html->url('/');?>">
							  <?php $organizationLogo = strlen($frontLogo)>0 ? $frontLogo : 'logo-website.fw.png'; echo $this->Html->image($organizationLogo,array('alt'=>$siteName,'class'=>'organization-logo','height'=>'40'));?>
							
						</a><!-- /.header-logo -->

						<div class="header-information">
							<i class="fa fa-at"></i>
							<div class="header-information-block">
								<strong><?php echo h($siteEmail);?></strong>
								<span><?php echo __('Ask Us Anything');?></span>
							</div><!-- /.header-information-block -->
						</div><!-- /.header-information -->

						<div class="header-information">
							<i class="fa fa-phone"></i>

							<div class="header-information-block">
								<strong><?php echo h($contact);?></strong>
								<span><?php echo __('Call Us Monday To Friday');?></span>
							</div><!-- /.header-information-block -->
						</div><!-- /.header-information -->

						<button class="navbar-toggler pull-xs-right hidden-md-up" type="button" data-toggle="collapse" data-target=".nav-primary-wrapper">
	                        <span></span>
	                        <span></span>
	                        <span></span>
	                    </button>
                        <?php
                            $mobileFrontProfilePhotoBase = $this->Html->url(array('controller'=>'img'));
                            $mobileFrontProfilePhoto = $mobileFrontProfilePhotoBase.'/user-icon.png';
                            $mobileFrontProfileName = __('Client Login');
                            $mobileFrontProfileLink = $this->Html->url(array('plugin'=>false,'admin'=>false,'controller'=>'Clients','action'=>'login'));
                            $mobileFrontProfileLogged = false;
                            if($this->Session->check('frontUser') && (!$this->Session->check('User') || $this->Session->check('Impersonation.front')))
                            {
                                $mobileHeaderUser = (array)$this->Session->read('frontUser');
                                $mobileHeaderClient = !empty($mobileHeaderUser['Client']) && is_array($mobileHeaderUser['Client']) ? $mobileHeaderUser['Client'] : array();
                                $mobileFrontProfileName = !empty($mobileHeaderClient['name']) ? $mobileHeaderClient['name'] : __('Client Dashboard');
                                $mobileFrontProfileLink = $this->Html->url(array('plugin'=>false,'admin'=>false,'controller'=>'Dashboards','action'=>'index'));
                                $mobileFrontProfileLogged = true;
                                if(!empty($frontClientPhotoUrl))
                                {
                                    $mobileFrontProfilePhoto = $frontClientPhotoUrl;
                                }
                                elseif(!empty($mobileHeaderClient['photo']))
                                {
                                    $mobileFrontProfileDir = !empty($mobileHeaderClient['dir']) ? $mobileHeaderClient['dir'] : (!empty($mobileHeaderClient['dirt']) ? $mobileHeaderClient['dirt'] : 'client');
                                    $mobileFrontProfilePhoto = $mobileFrontProfilePhotoBase.'/'.trim($mobileFrontProfileDir).'_thumb/'.$mobileHeaderClient['photo'];
                                }
                            }
                            elseif($this->Session->check('User'))
                            {
                                $mobileHeaderAdmin = (array)$this->Session->read('User');
                                $mobileHeaderAdminUser = !empty($mobileHeaderAdmin['User']) && is_array($mobileHeaderAdmin['User']) ? $mobileHeaderAdmin['User'] : array();
                                $mobileHeaderAdminGroup = !empty($mobileHeaderAdmin['Ugroup']['name']) ? $mobileHeaderAdmin['Ugroup']['name'] : __('Admin');
                                $mobileFrontProfileName = !empty($mobileHeaderAdminUser['name']) ? $mobileHeaderAdminUser['name'] : $mobileHeaderAdminGroup;
                                $mobileFrontProfileLink = $this->Html->url('/admin/Dashboards');
                                $mobileFrontProfileLogged = true;
                                if(!empty($mobileHeaderAdminUser['photo']))
                                {
                                    $mobileFrontProfilePhoto = $mobileFrontProfilePhotoBase.'/user_thumb/'.$mobileHeaderAdminUser['photo'];
                                }
                            }
                            elseif($this->Session->check('frontUser'))
                            {
                                $mobileHeaderUser = (array)$this->Session->read('frontUser');
                                $mobileHeaderClient = !empty($mobileHeaderUser['Client']) && is_array($mobileHeaderUser['Client']) ? $mobileHeaderUser['Client'] : array();
                                $mobileFrontProfileName = !empty($mobileHeaderClient['name']) ? $mobileHeaderClient['name'] : __('Client Dashboard');
                                $mobileFrontProfileLink = $this->Html->url(array('plugin'=>false,'admin'=>false,'controller'=>'Dashboards','action'=>'index'));
                                $mobileFrontProfileLogged = true;
                                if(!empty($frontClientPhotoUrl))
                                {
                                    $mobileFrontProfilePhoto = $frontClientPhotoUrl;
                                }
                                elseif(!empty($mobileHeaderClient['photo']))
                                {
                                    $mobileFrontProfileDir = !empty($mobileHeaderClient['dir']) ? $mobileHeaderClient['dir'] : (!empty($mobileHeaderClient['dirt']) ? $mobileHeaderClient['dirt'] : 'client');
                                    $mobileFrontProfilePhoto = $mobileFrontProfilePhotoBase.'/'.trim($mobileFrontProfileDir).'_thumb/'.$mobileHeaderClient['photo'];
                                }
                            }
                        ?>
                        <a class="wv-mobile-front-auth <?php echo $mobileFrontProfileLogged ? 'is-profile' : 'is-login';?>" href="<?php echo h($mobileFrontProfileLink);?>" title="<?php echo h($mobileFrontProfileName);?>">
                            <?php if($mobileFrontProfileLogged){ ?>
                            <span class="wv-mobile-front-auth-photo" style="background-image:url('<?php echo h($mobileFrontProfilePhoto);?>');"></span>
                            <?php }else{ ?>
                            <i class="fa fa-lock"></i><span class="wv-mobile-front-auth-label"><?php echo __('Login');?></span>
                            <?php } ?>
                        </a>
					</div><!-- /.header-top-inner -->
				</div><!-- /.header-top -->

				<div class="header-bottom">
					<div class="header-bottom-inner">
						
<div class="nav-primary-wrapper collapse navbar-toggleable-sm">
	<ul class="nav nav-pills nav-primary">
        <li class="nav-item nav-brand-item">
            <a class="nav-brand-link" href="<?php echo $this->Html->url('/');?>">
                <?php $menuOrganizationLogo = strlen($frontLogo)>0 ? $frontLogo : 'logo-website.fw.png'; echo $this->Html->image($menuOrganizationLogo,array('alt'=>$siteName,'class'=>'organization-logo-menu','height'=>'40'));?>
            </a>
        </li>
        <?php $paymentPlanActive = (strtolower($this->params['controller'])=='paymentplans') ? 'nav-link active' : 'nav-link'; $paymentPlanMenuHtml = '<li class="nav-item">'.$this->Html->link('<span class="fa fa-list-alt"></span>&nbsp;'.__('Payment Plans'),array('plugin'=>false,'admin'=>false,'controller'=>'paymentplans','action'=>'index'),array('class'=>$paymentPlanActive,'escape'=>false)).'</li>'; $paymentPlanInserted = false;?>
	<?php if($this->Session->check('frontUser')){ foreach($frontmenuArr as $menuName=>$menu): $menuIcon=$menu['icon'];h($menuName);if($this->params['controller']=="pages"){$this->params['controller']="";}$isMenu=true;$activeMenu='nav-link';?>
		<?php if($isMenu==true && strtolower($this->params['controller'])==strtolower($menu['controller'])){$activeMenu="nav-link active";}else{$activeMenu="nav-link";}?>
	      <li class="nav-item"><?php echo $this->Html->link("<span class=\"$menuIcon\"></span>&nbsp;$menuName",array('plugin'=>false,'admin'=>false,'controller' => $menu['controller'],'action'=>$menu['action']),array('class'=>$activeMenu,'escape' => false));?></li>
	      <?php endforeach;unset($menu);unset($menuName);unset($menuIcon);}?>
	      <?php foreach($contents as $menu):
          $contentPageIcon = isset($menu['Content']['page_icon']) ? $menu['Content']['page_icon'] : '';
          $contentLinkNameRaw = isset($menu['Content']['link_name']) ? $menu['Content']['link_name'] : '';
          $contentUrlRaw = isset($menu['Content']['url']) ? $menu['Content']['url'] : '';
          $contentIsUrl = isset($menu['Content']['is_url']) ? $menu['Content']['is_url'] : '';
          $contentPageUrlRaw = isset($menu['Content']['page_url']) ? $menu['Content']['page_url'] : '';
          $contentUrlTarget = isset($menu['Content']['url_target']) ? $menu['Content']['url_target'] : '_self';
          $menuName='<span class="'.h($contentPageIcon).'"></span>&nbsp;'.$contentLinkNameRaw;
	      if(($contentUrlRaw!="{siteDomain}/Registers" || (!$this->Session->check('frontUser') && !$this->Session->check('User') && $frontRegistration==1)) && ($contentUrlRaw!="{siteDomain}/Clients" || (!$this->Session->check('frontUser') && !$this->Session->check('User')))){
	      if($contentIsUrl=="External")
	      $pageUrl=strtolower(str_replace("{siteDomain}/","",$contentUrlRaw));
	      else
	      $pageUrl=strtolower($contentPageUrlRaw);
	      if(strtolower($this->params['controller'])==strtolower($pageUrl)){$activeMenu="nav-link active";}else{$activeMenu="nav-link";}
	      if($contentIsUrl=="External"){?><li class="nav-item"><?php echo$this->Html->link('<span class="'.$contentPageIcon.'"></span>&nbsp;'.$contentLinkNameRaw,str_replace("{siteDomain}",$siteDomain,$contentUrlRaw),array('class'=>$activeMenu,'target'=>$contentUrlTarget,'escape'=>false));?></li><?php }
	      else{?><li <?php echo (strtolower($this->params['controller'])==strtolower($contentPageUrlRaw))?>" class="nav-item"><?php echo $this->Html->link($menuName,array('controller' => 'Contents','action'=>'pages',$contentPageUrlRaw),array('class'=>$activeMenu,'escape' => false));?></li><?php }
          $contentLinkName = strtolower(trim((string)$contentLinkNameRaw));
          $contentUrlCheck = strtolower(trim(str_replace(array('{siteDomain}','/'),array('',''),(string)$contentUrlRaw)));
          $contentPageUrlCheck = strtolower(trim((string)$contentPageUrlRaw));
          if(!$paymentPlanInserted && ($contentLinkName === 'home' || $contentUrlCheck === '' || $contentPageUrlCheck === 'home')){ echo $paymentPlanMenuHtml; $paymentPlanInserted = true; }
          }?>
	      <?php endforeach; if(!$paymentPlanInserted){ echo $paymentPlanMenuHtml; } unset($menu);unset($menuName);?>																	
	</ul><!-- /.nav -->
</div><!-- /.nav-primary -->


						
					</div><!-- /.header-bottom-inner -->
				</div><!-- /.header-bottom -->
			</div><!-- /.container -->
		</div><!-- /.header-inner -->
	</div><!-- /.header -->
</div><!-- /.header-wrapper-->
<?php }else{?>
<div class="header-wrapper">
	<div class="header header-small">
		<div class="header-inner">
			<div class="container">
				<div class="header-top">
					<div class="header-top-inner">
					<ul class="nav nav-pills nav-topbar">
                            <li class="nav-item wv-topbar-menu-toggle">
                                <button class="navbar-toggler wv-topbar-menu-button" type="button" data-toggle="collapse" data-target=".nav-primary-wrapper" aria-label="<?php echo h(__('Toggle Menu'));?>">
                                    <i class="fa fa-bars"></i>
                                </button>
                            </li>
                            <li class="nav-item wv-top-contact">
                                <i class="fa fa-phone"></i> <a href="tel:<?php echo h(preg_replace('/[^0-9\+]/','',$contact));?>"><?php echo h($contact);?></a>
                                <span class="wv-top-divider">|</span>
                                <i class="fa fa-envelope"></i> <a href="mailto:<?php echo h($siteEmail);?>"><?php echo h($siteEmail);?></a>
                                <?php if(!empty($siteAddress)){ $siteAddressMapUrl='https://www.google.com/maps/search/?api=1&query='.rawurlencode($siteAddress); ?><span class="wv-top-divider">|</span><i class="fa fa-map-marker"></i> <a class="wv-top-address" href="<?php echo h($siteAddressMapUrl);?>" target="_blank" rel="noopener"><?php echo h($siteAddress);?></a><?php } ?>
                            </li>
							<?php if($frontRegistration){?><li class="nav-item wv-topbar-extra wv-topbar-register"><?php echo $this->Html->link('<i class="fa fa-user-plus"></i> '.__('Register'),array('controller'=>'Registers','action'=>'index'),array('escape'=>false));?></li><?php }?>
							<li class="nav-item wv-topbar-auth"><?php echo $this->Html->link('<i class="fa fa-lock"></i> '.__('Login'),array('plugin'=>false,'admin'=>false,'controller'=>'Clients','action'=>'login'),array('escape'=>false));?></li>
							<li class="nav-item wv-topbar-extra"><?php echo $this->Html->link('<i class="fa fa-heart"></i> '.__('Favourite Properties'),array('controller'=>'Favouriteproperties','action'=>'index'),array('escape'=>false));?></li>
							<li class="nav-item wv-topbar-extra"><?php echo $this->Html->link('<i class="fa fa-balance-scale"></i> '.__('Compare Properties'),array('controller'=>'Compareproperties','action'=>'index'),array('escape'=>false));?></li>							
						</ul>
						<a class="header-logo" href="<?php echo $this->Html->url('/');?>">
							  <?php if(strlen($frontLogo)>0){?><?php echo$this->Html->image($frontLogo,array('alt'=>$siteName,'class'=>'','height'=>'40'));} else{?>
							  <span class="header-logo-text"><?php echo h($siteName);?></span>
							  <?php }?>
							
						</a><!-- /.header-logo -->

						
						
<div class="nav-primary-wrapper collapse navbar-toggleable-sm">
	<ul class="nav nav-pills nav-primary">
        <?php $paymentPlanActive = (strtolower($this->params['controller'])=='paymentplans') ? 'nav-link active' : 'nav-link'; $paymentPlanMenuHtml = '<li class="nav-item">'.$this->Html->link(__('Payment Plans'),array('plugin'=>false,'admin'=>false,'controller'=>'paymentplans','action'=>'index'),array('class'=>$paymentPlanActive,'escape'=>false)).'</li>'; $paymentPlanInserted = false;?>
	<?php if($this->Session->check('frontUser')){ foreach($frontmenuArr as $menuName=>$menu): $menuIcon=$menu['icon'];h($menuName);if($this->params['controller']=="pages"){$this->params['controller']="";}$isMenu=true;$activeMenu='nav-link';?>
		<?php if($isMenu==true && strtolower($this->params['controller'])==strtolower($menu['controller'])){$activeMenu="nav-link active";}else{$activeMenu="nav-link";}?>
	      <li class="nav-item"><?php echo $this->Html->link("<span class=\"$menuIcon\"></span>&nbsp;$menuName",array('plugin'=>false,'admin'=>false,'controller' => $menu['controller'],'action'=>$menu['action']),array('class'=>$activeMenu,'escape' => false));?></li>
	      <?php endforeach;unset($menu);unset($menuName);unset($menuIcon);}?>
	      <?php foreach($contents as $menu): $menuName=h($menu['Content']['link_name']);
          $currentContentId = isset($contentId) ? strtolower($contentId) : strtolower($this->params['controller']);
	      if($currentContentId==strtolower($menu['Content']['page_url'])){$activeMenu="nav-link active";}else{$activeMenu="nav-link";}
	      if($menu['Content']['is_url']=="External"){?><li class="nav-item"><?php echo$this->Html->Link($menu['Content']['link_name'],str_replace("{siteDomain}",$siteDomain,$menu['Content']['url']),array('class'=>'nav-link','target'=>$menu['Content']['url_target']));?></li><?php }else{?><li <?php echo (strtolower($contentId)==strtolower($menu['Content']['page_url']))?>" class="nav-item"><?php echo $this->Html->link($menuName,array('controller' => 'Contents','action'=>'pages',$menu['Content']['page_url']),array('class'=>$activeMenu,'escape' => false));?></li><?php }
          $contentLinkName = strtolower(trim((string)$menu['Content']['link_name']));
          $contentUrlCheck = strtolower(trim(str_replace(array('{siteDomain}','/'),array('',''),(string)$menu['Content']['url'])));
          $contentPageUrlCheck = strtolower(trim((string)$menu['Content']['page_url']));
          if(!$paymentPlanInserted && ($contentLinkName === 'home' || $contentUrlCheck === '' || $contentPageUrlCheck === 'home')){ echo $paymentPlanMenuHtml; $paymentPlanInserted = true; }
          ?>
	      <?php endforeach; if(!$paymentPlanInserted){ echo $paymentPlanMenuHtml; } unset($menu);unset($menuName);?>																	
	</ul><!-- /.nav -->
</div><!-- /.nav-primary -->


						<button class="navbar-toggler pull-xs-right hidden-md-up" type="button" data-toggle="collapse" data-target=".nav-primary-wrapper">
	                        <span></span>
	                        <span></span>
	                        <span></span>
	                    </button>						
					</div><!-- /.header-top-inner -->
				</div><!-- /.header-top -->
			</div><!-- /.container -->
		</div><!-- /.header-inner -->
	</div><!-- /.header -->
</div><!-- /.header-wrapper-->


<?php }?>
<?php echo $this->fetch('content');?>
<div class="footer-wrapper">
	<div class="container">
		<div class="footer-inner">
			<?php
			$footerLogo = strlen(trim((string)$frontLogo))>0 ? $frontLogo : 'logo-website.fw.png';
			$footerPhoneClean = preg_replace('/[^0-9\+]/','',$contact);
			$footerAddress = isset($siteAddress) ? trim((string)$siteAddress) : '';
			$footerAddressMapUrl = strlen($footerAddress)>0 ? 'https://www.google.com/maps/search/?api=1&query='.rawurlencode($footerAddress) : '';
			?>
			<div class="wv-site-footer">
				<div class="wv-site-footer-grid">
					<div class="wv-footer-brand">
						<div class="wv-footer-logo">
							<?php echo $this->Html->image($footerLogo,array('alt'=>$siteName));?>
							<div>
								<strong><?php echo h($siteName);?></strong>
								<span><?php echo __('White Villas Property Portal');?></span>
							</div>
						</div>
						<p class="wv-footer-about"><?php echo __('White Villas helps clients explore published property listings, compare opportunities, and connect with the right team for verified real estate guidance.');?></p>
					</div>
					<div>
						<h3 class="wv-footer-title"><?php echo __('Quick Links');?></h3>
						<ul class="wv-footer-links">
							<li><?php echo $this->Html->link(__('Home'),'/');?></li>
							<li><?php echo $this->Html->link(__('Search Property'),array('controller'=>'Properties','action'=>'index'));?></li>
							<li><?php echo $this->Html->link(__('Payment Plans'),array('controller'=>'paymentplans','action'=>'index'));?></li>
							<li><?php echo $this->Html->link(__('Agencies'),array('controller'=>'Agencies','action'=>'index'));?></li>
							<li><?php echo $this->Html->link(__('Agents'),array('controller'=>'Agents','action'=>'index'));?></li>
							<li><?php echo $this->Html->link(__('About Us'),array('controller'=>'Contents','action'=>'pages','About-Us'));?></li>
							<li><?php echo $this->Html->link(__('Contact'),array('controller'=>'Contacts','action'=>'index'));?></li>
						</ul>
					</div>
					<div>
						<h3 class="wv-footer-title"><?php echo __('Contact Details');?></h3>
						<div class="wv-footer-contact-list">
							<?php if(strlen($footerAddress)>0){ ?>
							<div class="wv-footer-contact-item">
								<i class="fa fa-map-marker"></i>
								<div><strong><?php echo __('Address');?></strong><a href="<?php echo h($footerAddressMapUrl);?>" target="_blank" rel="noopener"><?php echo h($footerAddress);?></a></div>
							</div>
							<?php } ?>
							<div class="wv-footer-contact-item">
								<i class="fa fa-phone"></i>
								<div><strong><?php echo __('Phone');?></strong><a href="tel:<?php echo h($footerPhoneClean);?>"><?php echo h($contact);?></a></div>
							</div>
							<div class="wv-footer-contact-item">
								<i class="fa fa-envelope"></i>
								<div><strong><?php echo __('Email');?></strong><a href="mailto:<?php echo h($siteEmail);?>"><?php echo h($siteEmail);?></a></div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="footer-bottom">
				<div class="footer-left">
					<?php echo h($siteName);?> &copy; <?php echo $this->Time->format('Y',$siteTimezone);?>&nbsp;&nbsp;&nbsp;&nbsp;
					<?php echo __('Time');?> <span><?php echo $this->Time->format('d-M-Y h:i:s A',$siteTimezone);?></span>&nbsp;&nbsp;&nbsp;&nbsp;
					<?php echo __('Powered by');?> <?php echo$this->Html->Link(__('Al-Riaz Dev 03074432729'),'https://alriazdevelopers.com/',array('target'=>'_blank'));?>
							
				</div><!-- /.footer-left -->
			</div><!-- /.footer-bottom -->
		</div><!-- /.footer-inner -->
	</div><!-- /.container -->
</div><!-- /.footer-wrapper -->
</div><!-- /.page-wrapper -->
<p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p>

<?php if($this->params['controller']!="fronts"){echo $this->Html->script('/design1000/assets/js/scrollPosStyler.js');}?>
<div class="modal fade" id="targetModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
<div class="modal-content">        
</div>
</div>
<script type="text/javascript">
(function($){
  function ensureClientTableScroll(){
    if(!window.matchMedia('(max-width: 991px)').matches){
      return;
    }
    $('.dashboard-content table.table').each(function(){
      var $table = $(this);
      if($table.closest('.table-responsive').length === 0){
        $table.wrap('<div class="table-responsive client-scroll-wrap"></div>');
      }else{
        $table.closest('.table-responsive').addClass('client-scroll-wrap');
      }
      var colCount = $table.find('thead th').length;
      if(!colCount){
        colCount = $table.find('tbody tr:first td').length;
      }
      var minWidth = Math.max(760, colCount * 130);
      $table.css('min-width', minWidth + 'px');
    });
  }

  function bindClientSidebarMobile(){
    var $nav = $('.dashboard-nav-primary');
    if(!$nav.length){
      return;
    }
    if(!$('.client-sidebar-backdrop').length){
      $('body').append('<div class="client-sidebar-backdrop"></div>');
    }
    var $backdrop = $('.client-sidebar-backdrop');

    function closeSidebar(){
      $nav.collapse('hide');
      $('body').removeClass('client-sidebar-open');
    }

    $nav.off('shown.bs.collapse.clientMobile hidden.bs.collapse.clientMobile');
    $nav.on('shown.bs.collapse.clientMobile', function(){
      if(window.matchMedia('(max-width: 991px)').matches){
        $('body').addClass('client-sidebar-open');
      }
    });
    $nav.on('hidden.bs.collapse.clientMobile', function(){
      $('body').removeClass('client-sidebar-open');
    });

    $backdrop.off('click.clientMobile').on('click.clientMobile', function(){
      closeSidebar();
    });

    $('.dashboard-nav-primary .nav-link').off('click.clientMobile').on('click.clientMobile', function(){
      if(window.matchMedia('(max-width: 991px)').matches){
        closeSidebar();
      }
    });

    $(window).off('resize.clientMobile').on('resize.clientMobile', function(){
      if(!window.matchMedia('(max-width: 991px)').matches){
        $('body').removeClass('client-sidebar-open');
        $backdrop.hide();
      }
    });
  }

  function bindFrontMenuMobile(){
    var $nav = $('.nav-primary-wrapper').first();
    var $toggle = $('.header-wrapper .navbar-toggler[data-target=".nav-primary-wrapper"]');
    if(!$nav.length || !$toggle.length){
      return;
    }
    if(!$('.front-sidebar-backdrop').length){
      $('body').append('<div class="front-sidebar-backdrop"></div>');
    }
    var $backdrop = $('.front-sidebar-backdrop');

    function isMobileView(){
      return window.matchMedia('(max-width: 991px)').matches;
    }

    function openMenu(){
      if(!isMobileView()){
        return;
      }
      $nav.addClass('in').attr('aria-expanded','true');
      $toggle.attr('aria-expanded','true');
      $('body').addClass('front-mobile-menu-open');
    }

    function closeMenu(){
      $nav.removeClass('in').attr('aria-expanded','false');
      $toggle.attr('aria-expanded','false');
      $('body').removeClass('front-mobile-menu-open');
    }

    $nav.attr('id','wv-front-mobile-menu');
    $toggle.attr('aria-controls','wv-front-mobile-menu');
    $toggle.off('click.frontMobileMenu').on('click.frontMobileMenu', function(e){
      if(isMobileView()){
        e.preventDefault();
        e.stopImmediatePropagation();
        e.stopPropagation();
        if($('body').hasClass('front-mobile-menu-open')){
          closeMenu();
        }else{
          openMenu();
        }
        return false;
      }
    });
    $(document).off('click.frontMobileMenuToggle', '.header-wrapper .navbar-toggler[data-target=".nav-primary-wrapper"]')
      .on('click.frontMobileMenuToggle', '.header-wrapper .navbar-toggler[data-target=".nav-primary-wrapper"]', function(e){
        if(isMobileView()){
          e.preventDefault();
          e.stopImmediatePropagation();
          e.stopPropagation();
          if($('body').hasClass('front-mobile-menu-open')){
            closeMenu();
          }else{
            openMenu();
          }
          return false;
        }
      });
    $backdrop.off('click.frontMobileMenu').on('click.frontMobileMenu', function(){
      closeMenu();
    });
    $('.nav-primary-wrapper .nav-link').off('click.frontMobileMenu').on('click.frontMobileMenu', function(){
      if(isMobileView()){
        closeMenu();
      }
    });
    $(document).off('keyup.frontMobileMenu').on('keyup.frontMobileMenu', function(e){
      if(e.keyCode === 27){
        closeMenu();
      }
    });
    $(window).off('resize.frontMobileMenu orientationchange.frontMobileMenu').on('resize.frontMobileMenu orientationchange.frontMobileMenu', function(){
      if(!isMobileView()){
        closeMenu();
      }
    });
  }

  $(function(){
    ensureClientTableScroll();
    bindClientSidebarMobile();
    bindFrontMenuMobile();
  });
})(jQuery);
</script>
</body>   
</html>
<?php }?>
