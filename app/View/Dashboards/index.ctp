<?php
$dashboardClient = isset($portalClient) && is_array($portalClient) ? $portalClient : array();
if(empty($dashboardClient))
{
    $frontDashboardUser = (array)$this->Session->read('frontUser');
    $dashboardClient = !empty($frontDashboardUser['Client']) && is_array($frontDashboardUser['Client']) ? $frontDashboardUser['Client'] : array();
}
$clientName = !empty($dashboardClient['name']) ? $dashboardClient['name'] : __('Client');
$clientEmail = !empty($dashboardClient['email']) ? $dashboardClient['email'] : '';
$clientMobile = !empty($dashboardClient['mobile']) ? $dashboardClient['mobile'] : (!empty($dashboardClient['phone']) ? $dashboardClient['phone'] : '');
$photoUrl = $this->Html->url(array('controller'=>'img'));
$clientPhoto = !empty($frontClientPhotoUrl) ? $frontClientPhotoUrl : $photoUrl.'/user-icon.png';
if(empty($frontClientPhotoUrl) && !empty($dashboardClient['photo']))
{
    $clientPhotoDir = !empty($dashboardClient['dir']) ? $dashboardClient['dir'] : (!empty($dashboardClient['dirt']) ? $dashboardClient['dirt'] : 'client');
    $clientPhoto = $photoUrl.'/'.trim($clientPhotoDir).'_thumb/'.$dashboardClient['photo'];
}
$paidAmount = isset($dashboardSummary['paid_amount']) ? (float)$dashboardSummary['paid_amount'] : 0;
$dueAmount = isset($dashboardSummary['due_amount']) ? (float)$dashboardSummary['due_amount'] : 0;
$overdueAmount = isset($dashboardSummary['overdue_amount']) ? (float)$dashboardSummary['overdue_amount'] : 0;
$totalTracked = $paidAmount + $dueAmount;
$paidPercent = $totalTracked > 0 ? round(($paidAmount / $totalTracked) * 100) : 0;
if($paidPercent > 100){ $paidPercent = 100; }
$dealsPaymentSections = isset($dealsPaymentSections) && is_array($dealsPaymentSections) ? $dealsPaymentSections : array();
$runningRefundNotice = isset($runningRefundNotice) && is_array($runningRefundNotice) ? $runningRefundNotice : array();
$rentalDashboardSummary = isset($rentalDashboardSummary) && is_array($rentalDashboardSummary) ? $rentalDashboardSummary : array();
$useAdminDealLedger = $this->Session->check('User');
?>
<style type="text/css">
body{background:#f4f7fb;}
.wv-client-dashboard{background:#f4f7fb;margin:0;padding:0;}
.wv-client-hero{background:linear-gradient(135deg,#07152a 0%,#102746 70%,#ff0a46 165%);border-radius:16px;box-shadow:0 20px 42px rgba(15,23,42,.16);color:#fff;margin-bottom:18px;overflow:hidden;padding:24px;position:relative;}
.wv-client-hero:after{background:radial-gradient(circle at 88% 4%,rgba(124,249,255,.24),rgba(124,249,255,0) 38%);content:"";inset:0;position:absolute;}
.wv-client-hero-inner{align-items:center;display:flex;gap:18px;justify-content:space-between;position:relative;z-index:2;}
.wv-client-person{align-items:center;display:flex;gap:16px;min-width:0;}
.wv-client-photo{background:#fff center/cover no-repeat;border:3px solid rgba(255,255,255,.82);border-radius:50%;box-shadow:0 14px 28px rgba(0,0,0,.25);flex:0 0 78px;height:78px;width:78px;}
.wv-client-kicker{color:#7cf9ff;font-size:12px;font-weight:900;letter-spacing:.7px;margin-bottom:5px;text-transform:uppercase;}
.wv-client-hero h1{color:#fff!important;font-size:30px;font-weight:900;line-height:1.15;margin:0 0 8px;text-shadow:0 2px 14px rgba(0,0,0,.45);}
.wv-client-contact{align-items:center;color:#eaf4ff!important;display:flex;flex-wrap:wrap;font-size:13px;font-weight:800;gap:9px;}
.wv-client-contact span{align-items:center;background:rgba(255,255,255,.10);border:1px solid rgba(255,255,255,.14);border-radius:999px;color:#fff!important;display:inline-flex;gap:6px;padding:6px 9px;}
.wv-client-actions{display:flex;flex-wrap:wrap;gap:9px;justify-content:flex-end;}
.wv-client-actions .btn{border:0;border-radius:999px;font-size:12px;font-weight:900;padding:10px 14px;text-transform:uppercase;}
.wv-client-actions .btn-primary{background:#ff0a46!important;color:#fff!important;}
.wv-client-actions .btn-info{background:#7cf9ff!important;color:#07152a!important;}
.wv-client-actions .btn-default{background:rgba(255,255,255,.14)!important;border:1px solid rgba(255,255,255,.22)!important;color:#fff!important;}
.wv-client-impersonation{background:#fff7ed;border:1px solid #fed7aa;border-radius:10px;color:#9a3412;font-size:12px;font-weight:900;margin-bottom:14px;padding:11px 13px;}
.wv-client-refund-alert{align-items:center;background:#ecfdf5;border:1px solid #a7f3d0;border-radius:12px;color:#065f46;display:flex;gap:12px;justify-content:space-between;margin-bottom:14px;padding:13px 15px;}
.wv-client-refund-alert strong{display:block;font-size:14px;font-weight:900;}
.wv-client-refund-alert span{display:block;font-size:12px;font-weight:800;margin-top:3px;}
.wv-client-refund-alert .btn{border-radius:999px;font-size:11px;font-weight:900;text-transform:uppercase;white-space:nowrap;}
.wv-client-stats{display:grid;gap:14px;grid-template-columns:repeat(4,minmax(0,1fr));margin-bottom:18px;}
.wv-client-stat{background:#fff;border:1px solid #dfe9f5;border-radius:14px;box-shadow:0 12px 26px rgba(15,23,42,.07);min-height:132px;padding:17px;position:relative;overflow:hidden;}
.wv-client-stat:before{background:#ff0a46;border-radius:999px;content:"";height:46px;opacity:.09;position:absolute;right:-12px;top:-12px;width:46px;}
.wv-client-stat-icon{align-items:center;background:#f1f7ff;border-radius:12px;color:#07152a;display:flex;font-size:18px;height:40px;justify-content:center;margin-bottom:12px;width:40px;}
.wv-client-stat.paid .wv-client-stat-icon{background:#ecfdf5;color:#047857;}
.wv-client-stat.due .wv-client-stat-icon{background:#fff7ed;color:#c2410c;}
.wv-client-stat.overdue .wv-client-stat-icon{background:#fff1f4;color:#ff0a46;}
.wv-client-stat-label{color:#64748b;font-size:11px;font-weight:900;letter-spacing:.35px;margin-bottom:6px;text-transform:uppercase;}
.wv-client-stat-value{color:#07152a;font-size:24px;font-weight:900;line-height:1.15;word-break:break-word;}
.wv-client-stat-meta{color:#64748b;font-size:12px;font-weight:800;line-height:1.45;margin-top:7px;}
.wv-rental-dashboard{margin-bottom:18px;}
.wv-rental-dashboard-head{align-items:center;display:flex;justify-content:space-between;margin:0 2px 10px;}
.wv-rental-dashboard-title{color:#07152a;font-size:17px;font-weight:900;margin:0;}
.wv-rental-dashboard-link{color:#ff0a46!important;font-size:12px;font-weight:900;text-decoration:none!important;}
.wv-rental-stats{display:grid;gap:12px;grid-template-columns:repeat(6,minmax(0,1fr));}
.wv-rental-stat{background:#fff;border:1px solid #dfe9f5;border-radius:8px;color:#07152a!important;display:block;min-height:118px;padding:14px;position:relative;text-decoration:none!important;transition:box-shadow .18s ease,transform .18s ease;}
.wv-rental-stat:hover{box-shadow:0 12px 24px rgba(15,23,42,.10);transform:translateY(-2px);}
.wv-rental-stat-icon{align-items:center;background:#eef6ff;border-radius:8px;color:#0b67c2;display:flex;font-size:16px;height:34px;justify-content:center;margin-bottom:10px;width:34px;}
.wv-rental-stat.paid .wv-rental-stat-icon,.wv-rental-stat.net .wv-rental-stat-icon{background:#ecfdf5;color:#047857;}
.wv-rental-stat.upcoming .wv-rental-stat-icon{background:#fff7ed;color:#c2410c;}
.wv-rental-stat.overdue .wv-rental-stat-icon{background:#fff1f4;color:#ff0a46;}
.wv-rental-stat.charges .wv-rental-stat-icon{background:#f4f1ff;color:#6d28d9;}
.wv-rental-stat-label{color:#64748b;font-size:10px;font-weight:900;margin-bottom:5px;text-transform:uppercase;}
.wv-rental-stat-value{color:#07152a;font-size:18px;font-weight:900;line-height:1.25;overflow-wrap:anywhere;}
.wv-rental-stat.period .wv-rental-stat-value{font-size:12px;line-height:1.45;white-space:nowrap;}
.wv-rental-stat-meta{color:#64748b;font-size:11px;font-weight:800;margin-top:5px;}
.wv-client-grid{display:grid;gap:16px;grid-template-columns:minmax(0,1fr) 310px;}
.wv-client-panel{background:#fff;border:1px solid #dfe9f5;border-radius:14px;box-shadow:0 12px 26px rgba(15,23,42,.07);overflow:hidden;}
.wv-client-panel-head{align-items:center;border-bottom:1px solid #edf2f7;display:flex;gap:12px;justify-content:space-between;padding:16px 18px;}
.wv-client-panel-title{color:#07152a;font-size:18px;font-weight:900;margin:0;}
.wv-client-panel-sub{color:#64748b;font-size:12px;font-weight:800;margin-top:3px;}
.wv-client-panel-head .btn{border:0;border-radius:999px;font-size:12px;font-weight:900;padding:8px 12px;}
.wv-client-table-wrap{overflow-x:auto;-webkit-overflow-scrolling:touch;}
.wv-client-table{margin:0!important;min-width:780px;}
.wv-client-table thead tr{background:#07152a;}
.wv-client-table th{border:0!important;color:#fff!important;font-size:11px;font-weight:900;letter-spacing:.35px;padding:13px 14px!important;text-transform:uppercase;white-space:nowrap;}
.wv-client-table td{border-color:#edf2f7!important;color:#334155;font-size:12px;font-weight:800;padding:13px 14px!important;vertical-align:middle!important;}
.wv-client-table tbody tr:hover td{background:#fbfdff;}
.wv-receipt-pill{background:#fff1f4;border:1px solid #ffd6df;border-radius:999px;color:#ff0a46;display:inline-block;font-weight:900;padding:5px 9px;}
.wv-payment-amount{color:#047857;font-size:13px;font-weight:900;}
.wv-empty-payments{color:#64748b;font-size:13px;font-weight:900;padding:22px!important;text-align:center;}
.wv-payment-sections{display:grid;gap:14px;padding:14px;}
.wv-payment-deal-section{background:#fbfdff;border:1px solid #dfe9f5;border-radius:13px;overflow:hidden;}
.wv-payment-deal-head{align-items:center;background:linear-gradient(135deg,#07152a 0%,#102746 100%);display:flex;gap:12px;justify-content:space-between;padding:14px 15px;}
.wv-payment-deal-title{color:#fff!important;font-size:15px;font-weight:900;line-height:1.25;margin:0;}
.wv-payment-deal-meta{color:#cbd5e1!important;font-size:12px;font-weight:800;line-height:1.45;margin-top:4px;}
.wv-payment-deal-stats{align-items:center;display:flex;flex-wrap:wrap;gap:8px;justify-content:flex-end;}
.wv-payment-deal-pill{background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.16);border-radius:999px;color:#fff!important;display:inline-flex;font-size:11px;font-weight:900;gap:5px;padding:7px 9px;white-space:nowrap;}
.wv-payment-deal-pill.received{background:#7cf9ff;color:#07152a!important;}
.wv-payment-ledger-link{align-items:center;background:#ff0a46!important;border:0!important;border-radius:999px!important;color:#fff!important;display:inline-flex;font-size:11px!important;font-weight:900!important;gap:6px;padding:8px 10px!important;text-decoration:none!important;text-transform:uppercase;}
.wv-payment-section-table{background:#fff;}
.wv-payment-section-table .wv-client-table{min-width:960px;}
.wv-side-card{padding:18px;}
.wv-side-title{color:#07152a;font-size:16px;font-weight:900;margin:0 0 12px;}
.wv-progress-shell{align-items:center;background:#e8eef6;border-radius:999px;display:flex;height:20px;margin-bottom:9px;overflow:hidden;position:relative;}
.wv-progress-bar{background:linear-gradient(90deg,#ff0a46,#1aa6b7,#047857);display:block;height:20px;min-width:34px;position:relative;width:0;}
.wv-progress-bar.is-zero{background:transparent;min-width:34px;}
.wv-progress-label{color:#fff;display:block;font-size:10px;font-weight:900;line-height:20px;position:absolute;right:7px;text-align:right;text-shadow:0 1px 2px rgba(0,0,0,.45);top:0;white-space:nowrap;}
.wv-progress-bar.is-zero .wv-progress-label{color:#07152a;text-shadow:0 1px 0 rgba(255,255,255,.72);}
.wv-progress-meta{align-items:center;color:#64748b;display:flex;font-size:12px;font-weight:900;justify-content:space-between;margin-top:0;}
.wv-quick-links{display:grid;gap:10px;margin-top:18px;}
.wv-quick-link{align-items:center;background:#f8fbff;border:1px solid #dfe9f5;border-radius:11px;color:#07152a!important;display:flex;font-size:13px;font-weight:900;gap:10px;padding:12px;text-decoration:none!important;}
.wv-quick-link i{align-items:center;background:#07152a;border-radius:10px;color:#7cf9ff;display:flex;height:32px;justify-content:center;width:32px;}
.wv-quick-link:hover{background:#fff1f4;border-color:#ffc7d5;}
.wv-overdue-note{background:#fff1f4;border:1px solid #ffd6df;border-radius:12px;color:#9f1239;font-size:12px;font-weight:900;line-height:1.45;margin-top:14px;padding:12px;}
@media(max-width:1199px){
  .wv-client-stats{grid-template-columns:repeat(2,minmax(0,1fr));}
  .wv-rental-stats{grid-template-columns:repeat(3,minmax(0,1fr));}
  .wv-client-grid{grid-template-columns:1fr;}
}
@media(max-width:767px){
  .wv-client-dashboard{margin:0;padding:0;}
  .wv-client-hero{padding:18px 14px;}
  .wv-client-hero-inner{align-items:flex-start;display:block;}
  .wv-client-person{align-items:center;}
  .wv-client-photo{height:58px;width:58px;flex:0 0 58px;}
  .wv-client-hero h1{font-size:22px;}
  .wv-client-contact{gap:6px;}
  .wv-client-contact span{font-size:11px;padding:4px 8px;}
  .wv-client-actions{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:14px;width:100%;}
  .wv-client-actions .btn{justify-content:center;padding:9px 10px;text-align:center;width:100%;}
  .wv-client-refund-alert{align-items:flex-start;display:block;}
  .wv-client-refund-alert .btn{margin-top:10px;width:100%;}
  .wv-client-stats{grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;}
  .wv-client-stat{min-height:auto;padding:14px 12px;}
  .wv-client-stat-value{font-size:19px;}
  .wv-rental-stats{grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;}
  .wv-client-panel-head{align-items:flex-start;display:block;}
  .wv-client-panel-head .btn{margin-top:10px;width:100%;}
  .wv-client-table{min-width:720px;}
}
@media(max-width:480px){
  .wv-client-actions{grid-template-columns:1fr;}
  .wv-client-stats{grid-template-columns:1fr;}
  .wv-rental-stats{grid-template-columns:1fr;}
}
</style>

<div class="wv-client-dashboard">
    <?php echo $this->Session->flash();?>
    <?php if(!empty($isFrontImpersonation)){ ?>
        <div class="wv-client-impersonation">
            <i class="fa fa-user-secret"></i>
            <?php echo __('Admin view is active for this client account. Dashboard data is loaded from the selected client record.');?>
        </div>
    <?php } ?>
    <?php if(!empty($runningRefundNotice['request_id'])){ ?>
        <div class="wv-client-refund-alert">
            <div>
                <strong><i class="fa fa-refresh"></i> <?php echo __('Your Deal Refund is running.');?></strong>
                <span>
                    <?php echo __('Pending Balance');?>:
                    <?php echo h($currency.$this->Number->format((float)$runningRefundNotice['pending_total']));?>
                    <?php if(!empty($runningRefundNotice['request_no'])){ ?>
                        | <?php echo __('Request');?>: <?php echo h($runningRefundNotice['request_no']);?>
                    <?php } ?>
                </span>
            </div>
            <?php echo $this->Html->link(__('View Schedule'), array('plugin'=>false,'admin'=>false,'controller'=>'MyRefundrequests','action'=>'view',(int)$runningRefundNotice['request_id']), array('class'=>'btn btn-success'));?>
        </div>
    <?php } ?>

    <div class="wv-client-hero">
        <div class="wv-client-hero-inner">
            <div class="wv-client-person">
                <div class="wv-client-photo" style="background-image:url('<?php echo h($clientPhoto);?>');"></div>
                <div>
                    <div class="wv-client-kicker"><?php echo __('Client Portal');?></div>
                    <h1><?php echo h($clientName);?></h1>
                    <div class="wv-client-contact">
                        <?php if($clientMobile !== ''){ ?><span><i class="fa fa-phone"></i><?php echo h($clientMobile);?></span><?php } ?>
                        <?php if($clientEmail !== ''){ ?><span><i class="fa fa-envelope"></i><?php echo h($clientEmail);?></span><?php } ?>
                    </div>
                </div>
            </div>
            <div class="wv-client-actions">
                <?php echo $this->Html->link('<i class="fa fa-thumbs-up"></i> '.__('My Deals'), array('plugin'=>false,'admin'=>false,'controller'=>'MyDeals','action'=>'index'), array('class'=>'btn btn-primary','escape'=>false));?>
                <?php echo $this->Html->link('<i class="fa fa-dollar"></i> '.__('Payments'), array('plugin'=>false,'admin'=>false,'controller'=>'MyPayments','action'=>'index'), array('class'=>'btn btn-info','escape'=>false));?>
                <?php echo $this->Html->link('<i class="fa fa-building"></i> '.__('Properties'), array('plugin'=>false,'admin'=>false,'controller'=>'Properties','action'=>'index'), array('class'=>'btn btn-default','escape'=>false));?>
            </div>
        </div>
    </div>

    <div class="wv-client-stats">
        <div class="wv-client-stat deals">
            <div class="wv-client-stat-icon"><i class="fa fa-briefcase"></i></div>
            <div class="wv-client-stat-label"><?php echo __('Visible Deals');?></div>
            <div class="wv-client-stat-value"><?php echo (int)$dashboardSummary['deal_count']; ?></div>
            <div class="wv-client-stat-meta"><?php echo __('Active and blocked deals in your portal.');?></div>
        </div>
        <div class="wv-client-stat paid">
            <div class="wv-client-stat-icon"><i class="fa fa-check"></i></div>
            <div class="wv-client-stat-label"><?php echo __('Paid Amount');?></div>
            <div class="wv-client-stat-value"><?php echo $currency.$this->Number->format($paidAmount); ?></div>
            <div class="wv-client-stat-meta"><?php echo __('Receipts');?>: <?php echo (int)$dashboardSummary['payment_count']; ?></div>
        </div>
        <div class="wv-client-stat due">
            <div class="wv-client-stat-icon"><i class="fa fa-clock-o"></i></div>
            <div class="wv-client-stat-label"><?php echo __('Due Amount');?></div>
            <div class="wv-client-stat-value"><?php echo $currency.$this->Number->format($dueAmount); ?></div>
            <div class="wv-client-stat-meta"><?php echo __('Deals With Due');?>: <?php echo (int)$dashboardSummary['due_deal_count']; ?></div>
        </div>
        <div class="wv-client-stat overdue">
            <div class="wv-client-stat-icon"><i class="fa fa-exclamation"></i></div>
            <div class="wv-client-stat-label"><?php echo __('Overdue');?></div>
            <div class="wv-client-stat-value"><?php echo $currency.$this->Number->format($overdueAmount); ?></div>
            <div class="wv-client-stat-meta"><?php echo __('Installments');?>: <?php echo (int)$dashboardSummary['overdue_installment_count']; ?></div>
        </div>
    </div>

    <?php if(!empty($rentalDashboardSummary['rental_count'])){
        $myRentalsUrl = array('plugin'=>false,'admin'=>false,'controller'=>'MyRentals','action'=>'index');
        $rentalStart = !empty($rentalDashboardSummary['start_date']) ? date('d-M-Y', strtotime($rentalDashboardSummary['start_date'])) : '-';
        $rentalEnd = !empty($rentalDashboardSummary['end_date']) ? date('d-M-Y', strtotime($rentalDashboardSummary['end_date'])) : '-';
    ?>
        <section class="wv-rental-dashboard">
            <div class="wv-rental-dashboard-head">
                <h2 class="wv-rental-dashboard-title"><i class="fa fa-home"></i> <?php echo __('My Rentals');?></h2>
                <?php echo $this->Html->link(__('View Rentals').' <i class="fa fa-arrow-right"></i>', $myRentalsUrl, array('class'=>'wv-rental-dashboard-link','escape'=>false));?>
            </div>
            <div class="wv-rental-stats">
                <?php echo $this->Html->link('<div class="wv-rental-stat-icon"><i class="fa fa-calendar"></i></div><div class="wv-rental-stat-label">'.__('Rent Period').'</div><div class="wv-rental-stat-value">'.h($rentalStart).' '.__('to').' '.h($rentalEnd).'</div><div class="wv-rental-stat-meta">'.__('Total Rent').': '.h($currency.$this->Number->format((float)$rentalDashboardSummary['scheduled'])).'</div>', $myRentalsUrl, array('class'=>'wv-rental-stat period','escape'=>false));?>
                <?php echo $this->Html->link('<div class="wv-rental-stat-icon"><i class="fa fa-money"></i></div><div class="wv-rental-stat-label">'.__('Paid to You').'</div><div class="wv-rental-stat-value">'.h($currency.$this->Number->format((float)$rentalDashboardSummary['paid'])).'</div>', $myRentalsUrl, array('class'=>'wv-rental-stat paid','escape'=>false));?>
                <?php echo $this->Html->link('<div class="wv-rental-stat-icon"><i class="fa fa-clock-o"></i></div><div class="wv-rental-stat-label">'.__('Up-Coming').'</div><div class="wv-rental-stat-value">'.h($currency.$this->Number->format((float)$rentalDashboardSummary['upcoming'])).'</div>', $myRentalsUrl, array('class'=>'wv-rental-stat upcoming','escape'=>false));?>
                <?php echo $this->Html->link('<div class="wv-rental-stat-icon"><i class="fa fa-exclamation-triangle"></i></div><div class="wv-rental-stat-label">'.__('Overdue').'</div><div class="wv-rental-stat-value">'.h($currency.$this->Number->format((float)$rentalDashboardSummary['overdue'])).'</div>', $myRentalsUrl, array('class'=>'wv-rental-stat overdue','escape'=>false));?>
                <?php echo $this->Html->link('<div class="wv-rental-stat-icon"><i class="fa fa-wrench"></i></div><div class="wv-rental-stat-label">'.__('Owner Charges').'</div><div class="wv-rental-stat-value">'.h($currency.$this->Number->format((float)$rentalDashboardSummary['owner_charges'])).'</div>', $myRentalsUrl, array('class'=>'wv-rental-stat charges','escape'=>false));?>
                <?php echo $this->Html->link('<div class="wv-rental-stat-icon"><i class="fa fa-check-circle"></i></div><div class="wv-rental-stat-label">'.__('Net Received').'</div><div class="wv-rental-stat-value">'.h($currency.$this->Number->format((float)$rentalDashboardSummary['net_received'])).'</div>', $myRentalsUrl, array('class'=>'wv-rental-stat net','escape'=>false));?>
            </div>
        </section>
    <?php } ?>

    <?php if(!empty($utilityDashboardSummary['has_bills'])){
        $myUtilityUrl = array('plugin'=>false,'admin'=>false,'controller'=>'MyUtilityBills','action'=>'index');
        $uTotalBilled = isset($utilityDashboardSummary['total_billed']) ? (float)$utilityDashboardSummary['total_billed'] : 0.0;
        $uTotalPaid = isset($utilityDashboardSummary['total_paid']) ? (float)$utilityDashboardSummary['total_paid'] : 0.0;
        $uBalance = isset($utilityDashboardSummary['balance_outstanding']) ? (float)$utilityDashboardSummary['balance_outstanding'] : 0.0;
        $uUnpaidCount = isset($utilityDashboardSummary['unpaid_count']) ? (int)$utilityDashboardSummary['unpaid_count'] : 0;
        $uBillsCount = isset($utilityDashboardSummary['bills_count']) ? (int)$utilityDashboardSummary['bills_count'] : 0;
    ?>
        <section class="wv-rental-dashboard" style="margin-bottom:20px;">
            <div class="wv-rental-dashboard-head">
                <h2 class="wv-rental-dashboard-title"><i class="fa fa-bolt" style="color:#f59e0b;"></i> <?php echo __('Utility Bills & Power Ledger');?></h2>
                <?php echo $this->Html->link(__('View Full Utility Ledger').' <i class="fa fa-arrow-right"></i>', $myUtilityUrl, array('class'=>'wv-rental-dashboard-link','style'=>'color:#0284c7!important;','escape'=>false));?>
            </div>
            <div class="wv-rental-stats" style="grid-template-columns:repeat(4,minmax(0,1fr));">
                <?php echo $this->Html->link('<div class="wv-rental-stat-icon" style="background:#e0f2fe;color:#0284c7;"><i class="fa fa-file-text-o"></i></div><div class="wv-rental-stat-label">'.__('Total Bills').'</div><div class="wv-rental-stat-value">'.(int)$uBillsCount.' '.__('Bills').'</div><div class="wv-rental-stat-meta">'.__('Total Billed').': PKR '.number_format($uTotalBilled, 2).'</div>', $myUtilityUrl, array('class'=>'wv-rental-stat','escape'=>false));?>
                <?php echo $this->Html->link('<div class="wv-rental-stat-icon" style="background:#ecfdf5;color:#10b981;"><i class="fa fa-check-circle"></i></div><div class="wv-rental-stat-label">'.__('Total Paid').'</div><div class="wv-rental-stat-value" style="color:#10b981;">PKR '.number_format($uTotalPaid, 2).'</div><div class="wv-rental-stat-meta">'.__('Cleared Payments').'</div>', $myUtilityUrl, array('class'=>'wv-rental-stat paid','escape'=>false));?>
                <?php echo $this->Html->link('<div class="wv-rental-stat-icon" style="background:'.($uBalance > 0 ? '#fee2e2' : '#ecfdf5').';color:'.($uBalance > 0 ? '#dc2626' : '#10b981').';"><i class="fa '.($uBalance > 0 ? 'fa-exclamation-triangle' : 'fa-check').'"></i></div><div class="wv-rental-stat-label">'.__('Balance Due').'</div><div class="wv-rental-stat-value" style="color:'.($uBalance > 0 ? '#dc2626' : '#10b981').';">PKR '.number_format($uBalance, 2).'</div><div class="wv-rental-stat-meta">'.((int)$uUnpaidCount > 0 ? ((int)$uUnpaidCount.' '.__('Unpaid / Partial Invoices')) : __('All Dues Cleared')).'</div>', $myUtilityUrl, array('class'=>'wv-rental-stat '.($uBalance > 0 ? 'overdue' : 'paid'),'escape'=>false));?>
                <?php echo $this->Html->link('<div class="wv-rental-stat-icon" style="background:#f1f5f9;color:#0f172a;"><i class="fa fa-book"></i></div><div class="wv-rental-stat-label">'.__('Account Statement').'</div><div class="wv-rental-stat-value" style="font-size:14px;color:#0284c7;">'.__('View Statement').'</div><div class="wv-rental-stat-meta">'.__('Click to Open Full Ledger').'</div>', $myUtilityUrl, array('class'=>'wv-rental-stat','escape'=>false));?>
            </div>
        </section>
    <?php } ?>

    <div class="wv-client-grid">
        <div class="wv-client-panel">
            <div class="wv-client-panel-head">
                <div>
                    <h2 class="wv-client-panel-title"><?php echo __('Payment Receipts By Deal');?></h2>
                    <div class="wv-client-panel-sub"><?php echo __('All receipt transactions grouped deal wise.');?></div>
                </div>
                <?php echo $this->Html->link('<i class="fa fa-list"></i> '.__('Show All Payments'), array('plugin'=>false,'admin'=>false,'controller' => 'MyPayments', 'action' => 'index'), array('class' => 'btn btn-info btn-sm','escape'=>false));?>
            </div>
            <?php if(!empty($runningRefundNotice['request_id'])){ ?>
                <div class="wv-client-refund-alert" style="margin:14px 14px 0;">
                    <div>
                        <strong><i class="fa fa-refresh"></i> <?php echo __('Your Deal Refund is running.');?></strong>
                        <span>
                            <?php echo __('Pending Balance');?>:
                            <?php echo h($currency.$this->Number->format((float)$runningRefundNotice['pending_total']));?>
                            <?php if(!empty($runningRefundNotice['request_no'])){ ?>
                                | <?php echo __('Request');?>: <?php echo h($runningRefundNotice['request_no']);?>
                            <?php } ?>
                        </span>
                    </div>
                    <?php echo $this->Html->link(__('View Schedule'), array('plugin'=>false,'admin'=>false,'controller'=>'MyRefundrequests','action'=>'view',(int)$runningRefundNotice['request_id']), array('class'=>'btn btn-success'));?>
                </div>
            <?php } ?>
            <?php if(!empty($dealsPaymentSections)){ ?>
                <div class="wv-payment-sections">
                    <?php foreach($dealsPaymentSections as $sectionDealId=>$paymentSection):
                        $sectionDeal = !empty($paymentSection['Deal']) ? $paymentSection['Deal'] : array();
                        $sectionProperty = !empty($paymentSection['Property']) ? $paymentSection['Property'] : array();
                        $sectionPayments = !empty($paymentSection['payments']) ? (array)$paymentSection['payments'] : array();
                        $sectionInvoice = !empty($sectionDeal['invoice_no']) ? $sectionDeal['invoice_no'] : __('Deal').' #'.(int)$sectionDealId;
                        $sectionPropertyName = !empty($sectionProperty['name']) ? $sectionProperty['name'] : __('Property');
                        $sectionReceiptCount = !empty($paymentSection['receipt_count']) ? (int)$paymentSection['receipt_count'] : count($sectionPayments);
                        $sectionReceivedTotal = isset($paymentSection['received_total']) ? (float)$paymentSection['received_total'] : 0;
                        $sectionLedgerUrl = $useAdminDealLedger ? array('plugin'=>'admin','controller'=>'deals_payments','action'=>'index',(int)$sectionDealId,'?'=>array('client_preview'=>1)) : array('plugin'=>false,'admin'=>false,'controller'=>'MyPayments','action'=>'index',(int)$sectionDealId);
                    ?>
                        <div class="wv-payment-deal-section">
                            <div class="wv-payment-deal-head">
                                <div>
                                    <h3 class="wv-payment-deal-title"><?php echo h($sectionInvoice);?></h3>
                                    <div class="wv-payment-deal-meta"><i class="fa fa-building"></i> <?php echo h($sectionPropertyName);?></div>
                                </div>
                                <div class="wv-payment-deal-stats">
                                    <span class="wv-payment-deal-pill"><i class="fa fa-file-text-o"></i><?php echo (int)$sectionReceiptCount;?> <?php echo __('Receipts');?></span>
                                    <span class="wv-payment-deal-pill received"><i class="fa fa-check"></i><?php echo $currency.$this->Number->format($sectionReceivedTotal);?></span>
                                    <?php echo $this->Html->link('<i class="fa fa-list"></i> '.__('Ledger'),$sectionLedgerUrl,array('class'=>'wv-payment-ledger-link','escape'=>false));?>
                                </div>
                            </div>
                            <div class="wv-client-table-wrap wv-payment-section-table">
                                <table class="table table-users wv-client-table">
                                    <thead>
                                        <tr>
                                            <th><?php echo __('Receipt No.');?></th>
                                            <th><?php echo __('Paid Amount');?></th>
                                            <th><?php echo __('Installment+Late fee');?></th>
                                            <th><?php echo __('Payment Name');?></th>
                                            <th><?php echo __('Paid Date');?></th>
                                            <th><?php echo __('Payment Type');?></th>
                                            <th><?php echo __('Transaction Reference');?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($sectionPayments as $post):
                                            $firstPaymentRow = !empty($post['first_row']) ? $post['first_row'] : array();
                                            $paymentName = !empty($post['payment_names']) ? implode(', ', (array)$post['payment_names']) : (!empty($firstPaymentRow['PlansPayment']['name']) ? $firstPaymentRow['PlansPayment']['name'] : (!empty($firstPaymentRow['DealsPayment']['name']) ? $firstPaymentRow['DealsPayment']['name'] : '-'));
                                            $paidAmount = isset($post['paid_amount']) ? (float)$post['paid_amount'] : 0;
                                            $lateFees = isset($post['late_fees']) ? (float)$post['late_fees'] : 0;
                                        ?>
                                            <tr>
                                                <td><span class="wv-receipt-pill"><?php echo h(!empty($post['receipt_no']) ? $post['receipt_no'] : '-');?></span></td>
                                                <td><span class="wv-payment-amount"><?php echo $currency.$this->Number->format($paidAmount);?></span></td>
                                                <td>
                                                    <span class="wv-payment-amount"><?php echo $currency.$this->Number->format($paidAmount);?></span>
                                                    <span style="color:#888;"> + </span>
                                                    <span style="color:<?php echo $lateFees > 0 ? '#d9534f' : '#334155';?>;font-weight:900;"><?php echo $currency.$this->Number->format($lateFees);?></span>
                                                </td>
                                                <td><?php echo h($paymentName);?></td>
                                                <td><?php echo !empty($firstPaymentRow['DealsPayment']['payment_date']) ? $this->Time->format($sysDay.$dateSep.$sysMonth.$dateSep.$sysYear,h($firstPaymentRow['DealsPayment']['payment_date'])) : '-';?></td>
                                                <td><?php echo h(!empty($firstPaymentRow['Paymenttype']['name']) ? $firstPaymentRow['Paymenttype']['name'] : '-');?></td>
                                                <td><?php echo h(!empty($firstPaymentRow['DealsPayment']['remarks']) ? $firstPaymentRow['DealsPayment']['remarks'] : '-');?></td>
                                            </tr>
                                        <?php endforeach; unset($post); ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endforeach; unset($paymentSection); ?>
                </div>
            <?php }else{ ?>
                <div class="wv-empty-payments"><?php echo __('No payments found.');?></div>
            <?php } ?>
        </div>

        <div class="wv-client-panel">
            <div class="wv-side-card">
                <h3 class="wv-side-title"><?php echo __('Payment Progress');?></h3>
                <div class="wv-progress-shell">
                    <div class="wv-progress-bar<?php echo (int)$paidPercent <= 0 ? ' is-zero' : '';?>" style="width:<?php echo (int)$paidPercent;?>%;">
                        <span class="wv-progress-label"><?php echo (int)$paidPercent;?>%</span>
                    </div>
                </div>
                <div class="wv-progress-meta">
                    <span><?php echo __('Paid');?></span>
                    <strong><?php echo (int)$paidPercent;?>%</strong>
                </div>
                <?php if($overdueAmount > 0){ ?>
                    <div class="wv-overdue-note">
                        <i class="fa fa-warning"></i>
                        <?php echo __('Some installments are overdue. Please review your payment schedule.');?>
                    </div>
                <?php } ?>
                <div class="wv-quick-links">
                    <?php echo $this->Html->link('<i class="fa fa-thumbs-up"></i><span>'.__('View My Deals').'</span>', array('plugin'=>false,'admin'=>false,'controller'=>'MyDeals','action'=>'index'), array('class'=>'wv-quick-link','escape'=>false));?>
                    <?php echo $this->Html->link('<i class="fa fa-thumb-tack"></i><span>'.__('Invoices Past Due').'</span>', array('plugin'=>false,'admin'=>false,'controller'=>'MyInvpastdues','action'=>'index'), array('class'=>'wv-quick-link','escape'=>false));?>
                    <?php echo $this->Html->link('<i class="fa fa-user"></i><span>'.__('My Profile').'</span>', array('plugin'=>false,'admin'=>false,'controller'=>'MyProfiles','action'=>'index'), array('class'=>'wv-quick-link','escape'=>false));?>
                </div>
            </div>
        </div>
    </div>
</div>
