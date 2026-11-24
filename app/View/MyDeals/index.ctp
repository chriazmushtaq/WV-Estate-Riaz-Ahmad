<?php
$dealPaymentSummary = isset($dealPaymentSummary) && is_array($dealPaymentSummary) ? $dealPaymentSummary : array();
$refundRequestBlocks = isset($refundRequestBlocks) && is_array($refundRequestBlocks) ? $refundRequestBlocks : array();
$runningRefundNotice = isset($runningRefundNotice) && is_array($runningRefundNotice) ? $runningRefundNotice : array();
$totalDeals = count((array)$postArr);
$totalDealValue = 0;
$totalPaidValue = 0;
$totalDueValue = 0;
foreach((array)$postArr as $summaryDeal)
{
    $summaryDealId = !empty($summaryDeal['MyDeal']['id']) ? (int)$summaryDeal['MyDeal']['id'] : 0;
    $summaryTotal = isset($summaryDeal['MyDeal']['total_amount']) ? (float)$summaryDeal['MyDeal']['total_amount'] : 0;
    $summaryPaid = isset($dealPaymentSummary[$summaryDealId]['paid_amount']) ? (float)$dealPaymentSummary[$summaryDealId]['paid_amount'] : 0;
    $totalDealValue += $summaryTotal;
    $totalPaidValue += $summaryPaid;
    $summaryDue = $summaryTotal - $summaryPaid;
    if($summaryDue > 0)
    {
        $totalDueValue += $summaryDue;
    }
}
$propertyActionUrl=$this->Html->url(array('controller'=>'Myproperties'));
$useAdminDealLedger = $this->Session->check('User');
?>
<style type="text/css">
.wv-mydeals-page{background:#f4f7fb;margin:-10px -5px 0;padding:14px;}
.wv-mydeals-page *{box-sizing:border-box;}
.wv-mydeals-hero{background:linear-gradient(135deg,#07152a 0%,#102746 72%,#ff0a46 165%);border-radius:10px;box-shadow:0 14px 30px rgba(15,23,42,.13);color:#fff;margin-bottom:12px;overflow:hidden;padding:18px;position:relative;}
.wv-mydeals-hero:after{content:"";display:none;}
.wv-mydeals-hero-inner{align-items:center;display:flex;gap:18px;justify-content:space-between;position:relative;z-index:2;}
.wv-mydeals-kicker{color:#7cf9ff;font-size:11px;font-weight:900;letter-spacing:0;margin-bottom:4px;text-transform:uppercase;}
.wv-mydeals-hero h1{color:#fff!important;font-size:27px;font-weight:900;line-height:1.15;margin:0 0 5px;}
.wv-mydeals-hero p{color:#eef6ff!important;font-size:12px;font-weight:800;line-height:1.45;margin:0;}
.wv-mydeals-hero-actions{display:flex;flex-wrap:wrap;gap:9px;justify-content:flex-end;}
.wv-mydeals-hero-actions .btn{border:0;border-radius:10px;font-size:11px;font-weight:900;padding:9px 14px;text-transform:uppercase;color:#fff!important;box-shadow:0 2px 6px rgba(0,0,0,.15);}
.wv-mydeals-hero-actions .btn *{color:#fff!important;}
.wv-mydeals-hero-actions .btn-primary{background:linear-gradient(135deg,#ff0a46,#dc2626)!important;color:#fff!important;}
.wv-mydeals-hero-actions .btn-info{background:linear-gradient(135deg,#0284c7,#0369a1)!important;color:#fff!important;}
.wv-mydeals-stats{display:grid;gap:10px;grid-template-columns:repeat(4,minmax(0,1fr));margin-bottom:12px;}
.wv-mydeals-stat{background:#fff;border:1px solid #dfe9f5;border-radius:10px;box-shadow:0 8px 20px rgba(15,23,42,.06);padding:12px;position:relative;overflow:hidden;}
.wv-mydeals-stat:before{background:#ff0a46;border-radius:999px;content:"";height:44px;opacity:.09;position:absolute;right:-13px;top:-13px;width:44px;}
.wv-mydeals-stat span{color:#64748b;display:block;font-size:10px;font-weight:900;letter-spacing:0;margin-bottom:5px;text-transform:uppercase;}
.wv-mydeals-stat strong{color:#07152a;display:block;font-size:20px;font-weight:900;line-height:1.15;word-break:break-word;}
.wv-mydeals-stat small{color:#64748b;display:block;font-size:11px;font-weight:800;margin-top:5px;}
.wv-mydeals-refund-alert{align-items:center;background:#ecfdf5;border:1px solid #a7f3d0;border-radius:10px;color:#065f46;display:flex;gap:10px;justify-content:space-between;margin-bottom:12px;padding:12px 14px;}
.wv-mydeals-refund-alert strong{display:block;font-size:13px;font-weight:900;text-transform:uppercase;}
.wv-mydeals-refund-alert span{display:block;font-size:12px;font-weight:800;margin-top:3px;}
.wv-mydeals-refund-alert .btn{border-radius:10px;font-size:11px;font-weight:900;text-transform:uppercase;white-space:nowrap;color:#fff!important;}
.wv-mydeals-grid{display:grid;gap:12px;grid-template-columns:repeat(2,minmax(0,1fr));}
.wv-deal-card{background:#fff;border:1px solid #dfe9f5;border-radius:10px;box-shadow:0 8px 20px rgba(15,23,42,.07);overflow:hidden;}
.wv-deal-card-head{align-items:flex-start;background:#07152a;display:flex;gap:10px;justify-content:space-between;padding:13px 14px;}
.wv-deal-card-head .wv-deal-title,.wv-deal-card-head .wv-deal-title a{color:#fff!important;font-size:16px;font-weight:900;line-height:1.25;margin:0;text-shadow:0 1px 1px rgba(0,0,0,.18);}
.wv-deal-card-head .wv-deal-sub,.wv-deal-card-head .wv-deal-sub *{color:#e2e8f0!important;font-size:11px;font-weight:800;margin-top:4px;}
.wv-deal-badge{background:#ff0a46;border-radius:8px;color:#fff!important;display:inline-block;font-size:10px;font-weight:900;padding:6px 8px;white-space:nowrap;}
.wv-deal-body{padding:12px 14px 14px;}
.wv-deal-money{display:grid;gap:8px;grid-template-columns:repeat(3,minmax(0,1fr));margin-bottom:10px;}
.wv-deal-money-box{background:#f8fbff;border:1px solid #e2eaf4;border-radius:8px;padding:9px;}
.wv-deal-money-box span{color:#64748b;display:block;font-size:10px;font-weight:900;letter-spacing:0;margin-bottom:4px;text-transform:uppercase;}
.wv-deal-money-box strong{color:#07152a;display:block;font-size:13px;font-weight:900;line-height:1.2;word-break:break-word;}
.wv-deal-money-box.paid strong{color:#047857;}
.wv-deal-money-box.due strong{color:#ff0a46;}
.wv-deal-progress{align-items:center;background:#e8eef6;border-radius:999px;display:flex;height:20px;margin-bottom:11px;overflow:hidden;position:relative;}
.wv-deal-progress-fill{background:linear-gradient(90deg,#ff0a46,#1aa6b7,#047857);display:block;height:20px;min-width:34px;position:relative;width:0;}
.wv-deal-progress-fill.is-zero{background:transparent;min-width:34px;}
.wv-deal-progress-label{color:#fff;display:block;font-size:10px;font-weight:900;line-height:20px;position:absolute;right:7px;text-align:right;text-shadow:0 1px 2px rgba(0,0,0,.45);top:0;white-space:nowrap;}
.wv-deal-progress-fill.is-zero .wv-deal-progress-label{color:#07152a;text-shadow:0 1px 0 rgba(255,255,255,.72);}
.wv-deal-meta{display:grid;gap:8px;grid-template-columns:repeat(2,minmax(0,1fr));margin-bottom:11px;}
.wv-deal-meta-item{background:#fbfdff;border:1px solid #edf3f8;border-radius:8px;padding:8px;}
.wv-deal-meta-item span{color:#64748b;display:block;font-size:10px;font-weight:900;letter-spacing:0;text-transform:uppercase;}
.wv-deal-meta-item strong{color:#111827;display:block;font-size:12px;font-weight:900;margin-top:3px;word-break:break-word;}
.wv-deal-refund-status{align-items:center;background:#fff7ed;border:1px solid #fed7aa;border-radius:8px;color:#9a3412;display:flex;gap:8px;justify-content:space-between;margin:0 0 10px;padding:8px;}
.wv-deal-refund-status strong{font-size:11px;font-weight:900;text-transform:uppercase;}
.wv-deal-refund-status .btn{border-radius:10px;font-size:10px;font-weight:900;padding:5px 10px;text-transform:uppercase;white-space:nowrap;color:#fff!important;background:#ea580c!important;}
.wv-deal-refund-status .btn *{color:#fff!important;}
.wv-deal-actions{align-items:stretch;display:grid;gap:6px;grid-template-columns:repeat(6,minmax(0,1fr));margin-top:12px;}
.wv-deal-actions .wv-action-btn,
.wv-deal-actions a.wv-action-btn,
.wv-deal-actions a.wv-action-btn:link,
.wv-deal-actions a.wv-action-btn:visited,
.wv-deal-actions a.wv-action-btn:hover,
.wv-deal-actions a.wv-action-btn:focus,
.wv-deal-actions a.wv-action-btn:active,
.wv-deal-actions button.wv-action-btn{align-items:center;border:0;border-radius:10px!important;display:flex;flex-direction:column;font-size:10px;font-weight:900;justify-content:center;line-height:1.15;letter-spacing:0.3px;min-height:46px;padding:7px 4px;text-align:center;text-transform:uppercase;text-decoration:none!important;box-shadow:0 3px 8px rgba(15,23,42,.12);transition:all .2s cubic-bezier(0.4,0,0.2,1);color:#ffffff!important;text-shadow:0 1px 2px rgba(0,0,0,.35)!important;cursor:pointer;}
.wv-deal-actions .wv-action-btn .fa,
.wv-deal-actions .wv-action-btn .glyphicon,
.wv-deal-actions .wv-action-btn span,
.wv-deal-actions .wv-action-btn i,
.wv-deal-actions .wv-action-btn *{color:#ffffff!important;fill:#ffffff!important;text-shadow:0 1px 2px rgba(0,0,0,.35)!important;}
.wv-deal-actions .wv-action-btn .fa,
.wv-deal-actions .wv-action-btn .glyphicon{display:block;font-size:14px;margin-bottom:3px;margin-right:0;}
.wv-deal-actions .wv-action-btn:hover{filter:brightness(1.16);transform:translateY(-2px);box-shadow:0 6px 14px rgba(15,23,42,.22);color:#ffffff!important;}

.wv-btn-calendar{background:linear-gradient(135deg,#0284c7,#0369a1)!important;}
.wv-btn-discount{background:linear-gradient(135deg,#f59e0b,#d97706)!important;}
.wv-btn-invoice{background:linear-gradient(135deg,#0284c7,#0ea5e9)!important;}
.wv-btn-ledger{background:linear-gradient(135deg,#059669,#047857)!important;}
.wv-btn-pdf{background:linear-gradient(135deg,#dc2626,#b91c1c)!important;}
.wv-btn-details{background:linear-gradient(135deg,#4f46e5,#4338ca)!important;}

.wv-btn-letters{background:linear-gradient(135deg,#2563eb,#1d4ed8)!important;}
.wv-btn-vault{background:linear-gradient(135deg,#7c3aed,#6d28d9)!important;}
.wv-btn-refund{background:linear-gradient(135deg,#ea580c,#c2410c)!important;}
.wv-btn-photos{background:linear-gradient(135deg,#475569,#334155)!important;}
.wv-btn-documents{background:linear-gradient(135deg,#0f766e,#115e59)!important;}
.wv-btn-pay{background:linear-gradient(135deg,#16a34a,#15803d)!important;}

.wv-deal-actions .wv-action-btn.disabled{cursor:not-allowed;opacity:.72;pointer-events:none;}
.wv-mydeals-empty{background:#fff;border:1px dashed #cbd5e1;border-radius:10px;color:#64748b;font-size:14px;font-weight:900;padding:22px;text-align:center;}
@media(max-width:1199px){.wv-mydeals-grid{grid-template-columns:1fr;}}
@media(max-width:991px){.wv-mydeals-stats{grid-template-columns:repeat(2,minmax(0,1fr));}.wv-deal-actions{grid-template-columns:repeat(3,minmax(0,1fr));}}
@media(max-width:767px){.wv-mydeals-page{margin:0;padding:10px;}.wv-mydeals-hero{padding:14px;}.wv-mydeals-hero-inner{display:block;}.wv-mydeals-hero h1{font-size:23px;}.wv-mydeals-hero-actions{justify-content:flex-start;margin-top:12px;}.wv-mydeals-hero-actions .btn{width:100%;}.wv-mydeals-refund-alert{align-items:flex-start;display:block;}.wv-mydeals-refund-alert .btn{margin-top:9px;width:100%;}.wv-deal-card-head{align-items:flex-start;display:flex;}.wv-deal-badge{margin-top:0;}.wv-deal-money{grid-template-columns:repeat(3,minmax(0,1fr));}.wv-deal-meta{grid-template-columns:repeat(2,minmax(0,1fr));}.dashboard-content{overflow:visible!important;}.dashboard-content .container-fluid{overflow:visible!important;}}
@media(max-width:480px){.wv-mydeals-stats,.wv-deal-money,.wv-deal-meta{grid-template-columns:1fr;}.wv-deal-actions{grid-template-columns:repeat(2,minmax(0,1fr));}.wv-deal-card-head{display:block;}.wv-deal-badge{margin-top:8px;}.wv-deal-actions .wv-action-btn{width:100%;}.wv-mydeals-stat strong{font-size:18px;}}
</style>

<div class="wv-mydeals-page">
    <?php echo $this->Session->flash();?>
    <div class="wv-mydeals-hero">
        <div class="wv-mydeals-hero-inner">
            <div>
                <div class="wv-mydeals-kicker"><?php echo __('Client Deal Center');?></div>
                <h1><?php echo __('My Deals');?></h1>
                <p><?php echo __('Review booked properties, invoices, receipts, letters, photos, and documents from one place.');?></p>
            </div>
            <div class="wv-mydeals-hero-actions">
                <?php echo $this->Html->link('<i class="fa fa-dollar"></i> '.__('Payment Ledger'),array('plugin'=>false,'admin'=>false,'controller'=>'MyPayments','action'=>'index'),array('class'=>'btn btn-info','escape'=>false));?>
                <?php echo $this->Html->link('<i class="fa fa-building"></i> '.__('Search Properties'),array('plugin'=>false,'admin'=>false,'controller'=>'Properties','action'=>'index'),array('class'=>'btn btn-primary','escape'=>false));?>
            </div>
        </div>
    </div>

    <div class="wv-mydeals-stats">
        <div class="wv-mydeals-stat">
            <span><?php echo __('Total Deals');?></span>
            <strong><?php echo (int)$totalDeals;?></strong>
            <small><?php echo __('Visible in client portal');?></small>
        </div>
        <div class="wv-mydeals-stat">
            <span><?php echo __('Deal Value');?></span>
            <strong><?php echo $currency.$this->Number->format($totalDealValue);?></strong>
            <small><?php echo __('Total invoice amount');?></small>
        </div>
        <div class="wv-mydeals-stat">
            <span><?php echo __('Paid');?></span>
            <strong><?php echo $currency.$this->Number->format($totalPaidValue);?></strong>
            <small><?php echo __('Received payments');?></small>
        </div>
        <div class="wv-mydeals-stat">
            <span><?php echo __('Balance');?></span>
            <strong><?php echo $currency.$this->Number->format($totalDueValue);?></strong>
            <small><?php echo __('Remaining amount');?></small>
        </div>
    </div>

    <?php if(!empty($runningRefundNotice['request_id'])){ ?>
        <div class="wv-mydeals-refund-alert">
            <div>
                <strong><span class="fa fa-refresh"></span> <?php echo __('Your Deal Refund is running');?></strong>
                <span>
                    <?php echo __('Pending Balance');?>:
                    <?php echo h($currency.$this->Number->format((float)$runningRefundNotice['pending_total']));?>
                    <?php if(!empty($runningRefundNotice['request_no'])){ ?>
                        | <?php echo __('Request');?>: <?php echo h($runningRefundNotice['request_no']);?>
                    <?php } ?>
                </span>
            </div>
            <?php echo $this->Html->link(__('View Schedule'),array('plugin'=>false,'admin'=>false,'controller'=>'MyRefundrequests','action'=>'view',(int)$runningRefundNotice['request_id']),array('class'=>'btn btn-success'));?>
        </div>
    <?php } ?>

    <?php if(empty($postArr)){ ?>
        <div class="wv-mydeals-empty"><?php echo __('No deals found.');?></div>
    <?php }else{ ?>
        <div class="wv-mydeals-grid">
            <?php foreach($postArr as $post):
                $id=(int)$post['MyDeal']['id'];
                $propertyId=(int)$post['MyDeal']['property_id'];
                $propertyName = !empty($post['Property']['name']) ? $post['Property']['name'] : __('Property');
                $dealTotal = isset($post['MyDeal']['total_amount']) ? (float)$post['MyDeal']['total_amount'] : 0;
                $dealPaid = isset($dealPaymentSummary[$id]['paid_amount']) ? (float)$dealPaymentSummary[$id]['paid_amount'] : 0;
                $dealDue = $dealTotal - $dealPaid;
                if($dealDue < 0){ $dealDue = 0; }
                $receiptCount = isset($dealPaymentSummary[$id]['receipt_count']) ? (int)$dealPaymentSummary[$id]['receipt_count'] : 0;
                $progressPercent = $dealTotal > 0 ? round(($dealPaid / $dealTotal) * 100) : 0;
                if($progressPercent > 100){ $progressPercent = 100; }
                $dealArea = !empty($post['Property']['area']) ? $post['Property']['area'] : '-';
                $dealUnit = !empty($post['Unit']['name']) ? ' '.$post['Unit']['name'] : '';
                $planName = !empty($post['Plan']['name']) ? $post['Plan']['name'] : '-';
                $dealLedgerUrl = $useAdminDealLedger ? array('plugin'=>'admin','controller'=>'deals_payments','action'=>'index',$id,'?'=>array('client_preview'=>1)) : array('plugin'=>false,'admin'=>false,'controller'=>'MyPayments','action'=>'index',$id);
                $dealLedgerPdfUrl = array('plugin'=>false,'admin'=>false,'controller'=>'MyDeals','action'=>'ledgerpdf',$id);
                $clientRefundAllowed = !empty($post['MyDeal']['client_refund_allowed']);
                $refundRequestBlock = isset($refundRequestBlocks[$id]) ? (array)$refundRequestBlocks[$id] : array();
                $refundRequestStatus = !empty($refundRequestBlock['status']) ? $refundRequestBlock['status'] : __('In Process');
                $refundRequestId = !empty($refundRequestBlock['id']) ? (int)$refundRequestBlock['id'] : 0;
                $canApplyRefund = !empty($clientRefundAllowed) && (empty($refundRequestBlock) || $refundRequestStatus === 'Cancelled');
                $dealBilling = !empty($dealsBillingMap[$id]) ? $dealsBillingMap[$id] : array();
                $hasBillingMeter = !empty($dealBilling['has_billing']);
            ?>
                <div class="wv-deal-card">
                    <div class="wv-deal-card-head">
                        <div>
                            <h2 class="wv-deal-title"><?php echo h($propertyName);?></h2>
                            <div class="wv-deal-sub">
                                <?php echo __('Invoice');?>: <?php echo h(!empty($post['MyDeal']['invoice_no']) ? $post['MyDeal']['invoice_no'] : '-');?>
                            </div>
                        </div>
                        <span class="wv-deal-badge"><?php echo (int)$progressPercent;?>% <?php echo __('Paid');?></span>
                    </div>
                    <div class="wv-deal-body">
                        <div class="wv-deal-money">
                            <div class="wv-deal-money-box">
                                <span><?php echo __('Total');?></span>
                                <strong><?php echo $currency.$this->Number->format($dealTotal);?></strong>
                            </div>
                            <div class="wv-deal-money-box paid">
                                <span><?php echo __('Paid');?></span>
                                <strong><?php echo $currency.$this->Number->format($dealPaid);?></strong>
                            </div>
                            <div class="wv-deal-money-box due">
                                <span><?php echo __('Balance');?></span>
                                <strong><?php echo $currency.$this->Number->format($dealDue);?></strong>
                            </div>
                        </div>
                        <div class="wv-deal-progress" aria-label="<?php echo h(__('Payment Progress'));?>">
                            <span class="wv-deal-progress-fill<?php echo (int)$progressPercent <= 0 ? ' is-zero' : '';?>" style="width:<?php echo (int)$progressPercent;?>%;">
                                <span class="wv-deal-progress-label"><?php echo (int)$progressPercent;?>%</span>
                            </span>
                        </div>
                        <div class="wv-deal-meta">
                            <div class="wv-deal-meta-item">
                                <span><?php echo __('Invoice Date');?></span>
                                <strong><?php echo $this->Time->format($sysDay.$dateSep.$sysMonth.$dateSep.$sysYear,h($post['MyDeal']['date']));?></strong>
                            </div>
                            <div class="wv-deal-meta-item">
                                <span><?php echo __('Area');?></span>
                                <strong><?php echo h($dealArea.$dealUnit);?></strong>
                            </div>
                            <div class="wv-deal-meta-item">
                                <span><?php echo __('Payment Plan');?></span>
                                <strong><?php echo h($planName);?></strong>
                            </div>
                            <div class="wv-deal-meta-item">
                                <span><?php echo __('Receipts');?></span>
                                <strong><?php echo (int)$receiptCount;?></strong>
                            </div>
                        </div>
                        <?php if(!empty($refundRequestId)){ ?>
                            <div class="wv-deal-refund-status">
                                <strong><span class="fa fa-refresh"></span> <?php echo __('Refund Status');?>: <?php echo h($refundRequestStatus);?></strong>
                                <?php echo $this->Html->link(__('View'),array('plugin'=>false,'admin'=>false,'controller'=>'MyRefundrequests','action'=>'view',$refundRequestId),array('class'=>'btn btn-warning'));?>
                            </div>
                        <?php } ?>
                        <div class="wv-deal-actions">
                            <!-- LINE 1 (6 Buttons) -->
                            <?php echo $this->Html->link('<span class="fa fa-calendar" style="color:#ffffff!important;display:block;font-size:14px;margin-bottom:3px;"></span><span style="color:#ffffff!important;font-weight:900;">'.__('Calendar').'</span>',array('plugin'=>false,'admin'=>false,'controller'=>'MyDeals','action'=>'calendar_ics',$id),array('class'=>'wv-action-btn wv-btn-calendar','style'=>'color:#ffffff!important;','escape'=>false,'title'=>__('Add Installment Schedule to Apple, Google or Outlook Calendar')));?>
                            <button type="button" class="wv-action-btn wv-btn-discount" style="color:#ffffff!important;" onclick="openEarlyDiscountModal(<?php echo $id;?>);" title="<?php echo __('Calculate Early Settlement Discount');?>"><span class="fa fa-calculator" style="color:#ffffff!important;display:block;font-size:14px;margin-bottom:3px;"></span><span style="color:#ffffff!important;font-weight:900;"><?php echo __('Discount');?></span></button>
                            <?php echo $this->Html->link('<span class="fa fa-print" style="color:#ffffff!important;display:block;font-size:14px;margin-bottom:3px;"></span><span style="color:#ffffff!important;font-weight:900;">'.__('Payment Plan').'</span>',array('plugin'=>false,'admin'=>false,'controller'=>'MyDeals','action'=>'printinvoice',$id),array('class'=>'wv-action-btn wv-btn-invoice','style'=>'color:#ffffff!important;','target'=>'_blank','escape'=>false,'title'=>__('Print Payment Plan')));?>
                            <?php echo $this->Html->link('<span class="fa fa-list" style="color:#ffffff!important;display:block;font-size:14px;margin-bottom:3px;"></span><span style="color:#ffffff!important;font-weight:900;">'.__('My Payments').'</span>',$dealLedgerUrl,array('class'=>'wv-action-btn wv-btn-ledger','style'=>'color:#ffffff!important;','escape'=>false,'title'=>__('View My Payments')));?>
                            <?php echo $this->Html->link('<span class="fa fa-file-pdf-o" style="color:#ffffff!important;display:block;font-size:14px;margin-bottom:3px;"></span><span style="color:#ffffff!important;font-weight:900;">'.__('Ledger').'</span>',$dealLedgerPdfUrl,array('class'=>'wv-action-btn wv-btn-pdf','style'=>'color:#ffffff!important;','target'=>'_blank','rel'=>'noopener','escape'=>false,'title'=>__('Download Ledger PDF')));?>
                            <?php echo $this->Html->link('<span class="glyphicon glyphicon-fullscreen" style="color:#ffffff!important;display:block;font-size:14px;margin-bottom:3px;"></span><span style="color:#ffffff!important;font-weight:900;">'.__('Details').'</span>','#',array('onclick'=>"show_modal('MyDeals/view/$id');",'class'=>'wv-action-btn wv-btn-details','style'=>'color:#ffffff!important;','escape'=>false,'title'=>__('View Deal Details')));?>

                            <!-- LINE 2 (6 Buttons) -->
                            <?php echo $this->Html->link('<span class="fa fa-file-text-o" style="color:#ffffff!important;display:block;font-size:14px;margin-bottom:3px;"></span><span style="color:#ffffff!important;font-weight:900;">'.__('Letters').'</span>',array('plugin'=>false,'admin'=>false,'controller'=>'MyDeals','action'=>'letters',$id),array('class'=>'wv-action-btn wv-btn-letters','style'=>'color:#ffffff!important;','escape'=>false,'title'=>__('Deal Letters & Notices')));?>
                            <?php echo $this->Html->link('<span class="fa fa-lock" style="color:#ffffff!important;display:block;font-size:14px;margin-bottom:3px;"></span><span style="color:#ffffff!important;font-weight:900;">'.__('Vault').'</span>',array('plugin'=>false,'admin'=>false,'controller'=>'MyDocuments','action'=>'index'),array('class'=>'wv-action-btn wv-btn-vault','style'=>'color:#ffffff!important;','escape'=>false,'title'=>__('Document Vault')));?>
                            <?php 
                            if(!empty($refundRequestId)){
                                echo $this->Html->link('<span class="fa fa-refresh" style="color:#ffffff!important;display:block;font-size:14px;margin-bottom:3px;"></span><span style="color:#ffffff!important;font-weight:900;">'.__('Refund').'</span>',array('plugin'=>false,'admin'=>false,'controller'=>'MyRefundrequests','action'=>'view',$refundRequestId),array('class'=>'wv-action-btn wv-btn-refund','style'=>'color:#ffffff!important;','escape'=>false,'title'=>__('View Refund Status').': '.$refundRequestStatus));
                            } elseif(!empty($canApplyRefund)){
                                echo $this->Html->link('<span class="fa fa-undo" style="color:#ffffff!important;display:block;font-size:14px;margin-bottom:3px;"></span><span style="color:#ffffff!important;font-weight:900;">'.__('Refund').'</span>',array('plugin'=>false,'admin'=>false,'controller'=>'MyRefundrequests','action'=>'apply',$id),array('class'=>'wv-action-btn wv-btn-refund','style'=>'color:#ffffff!important;','escape'=>false,'title'=>__('Apply for Refund')));
                            } else {
                                echo $this->Html->link('<span class="fa fa-undo" style="color:#ffffff!important;display:block;font-size:14px;margin-bottom:3px;"></span><span style="color:#ffffff!important;font-weight:900;">'.__('Refund').'</span>','#',array('class'=>'wv-action-btn wv-btn-refund disabled','style'=>'color:#ffffff!important;opacity:0.6;','escape'=>false,'title'=>__('Refund not available')));
                            }
                            ?>
                            <?php echo $this->Html->link('<span class="fa fa-image" style="color:#ffffff!important;display:block;font-size:14px;margin-bottom:3px;"></span><span style="color:#ffffff!important;font-weight:900;">'.__('Photos').'</span>','#',array('onclick'=>"show_modal('$propertyActionUrl/photo/$propertyId');",'class'=>'wv-action-btn wv-btn-photos','style'=>'color:#ffffff!important;','escape'=>false,'title'=>__('Property Photos')));?>
                            <?php echo $this->Html->link('<span class="fa fa-file" style="color:#ffffff!important;display:block;font-size:14px;margin-bottom:3px;"></span><span style="color:#ffffff!important;font-weight:900;">'.__('Docs').'</span>','#',array('onclick'=>"show_modal('$propertyActionUrl/document/$propertyId');",'class'=>'wv-action-btn wv-btn-documents','style'=>'color:#ffffff!important;','escape'=>false,'title'=>__('Property Documents')));?>
                            <?php if(!empty($hasBillingMeter)){ ?>
                                <?php echo $this->Html->link('<span class="fa fa-bolt" style="color:#ffffff!important;display:block;font-size:14px;margin-bottom:3px;"></span><span style="color:#ffffff!important;font-weight:900;">'.__('Utility Bill').'</span>',array('plugin'=>false,'admin'=>false,'controller'=>'UtilityBills','action'=>'index'),array('class'=>'wv-action-btn wv-btn-pay','style'=>'color:#ffffff!important;','escape'=>false,'title'=>__('View & Pay Utility Bills')));?>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; unset($post); ?>
        </div>
    <?php } ?>
</div>

<!-- =========================================================================
     MODAL: Early Payment & Lump-Sum Discount Calculator
     ========================================================================= -->
<div class="modal fade" id="earlyDiscountModal" tabindex="-1" role="dialog" aria-labelledby="earlyDiscountModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" style="border-radius:14px; overflow:hidden; border:none; box-shadow:0 20px 40px rgba(15,23,42,.25);">
            <div class="modal-header" style="background:linear-gradient(135deg,#07152a 0%,#1e3a8a 100%); color:#fff; padding:16px 20px;">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true" style="color:#fff; opacity:.85; font-size:24px;">&times;</button>
                <h4 class="modal-title" id="earlyDiscountModalLabel" style="font-weight:800; display:flex; align-items:center; gap:8px;">
                    <i class="fa fa-calculator text-warning"></i> <?php echo __('Early Payment & Lump-Sum Discount Calculator'); ?>
                </h4>
            </div>
            <div class="modal-body" style="padding:20px; background:#f8fafc;">
                <div id="earlyDiscountLoader" class="text-center" style="padding:40px 0;">
                    <i class="fa fa-spinner fa-spin fa-3x text-primary"></i>
                    <p style="margin-top:10px; font-weight:600; color:#64748b;"><?php echo __('Loading installment schedule & discount rules...'); ?></p>
                </div>

                <div id="earlyDiscountContent" style="display:none;">
                    <!-- Deal Meta Banner -->
                    <div style="background:#fff; border:1px solid #e2e8f0; border-radius:10px; padding:12px 16px; margin-bottom:14px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
                        <div>
                            <span style="font-size:11px; text-transform:uppercase; color:#64748b; font-weight:700;"><?php echo __('Property / Deal'); ?></span>
                            <h4 id="calcDealTitle" style="margin:2px 0 0; color:#0f172a; font-weight:800;">-</h4>
                        </div>
                        <div class="text-right">
                            <span style="font-size:11px; text-transform:uppercase; color:#64748b; font-weight:700;"><?php echo __('Total Remaining Balance'); ?></span>
                            <h4 id="calcTotalPending" style="margin:2px 0 0; color:#dc2626; font-weight:800;">-</h4>
                        </div>
                    </div>

                    <!-- Overdue Notice Banner (Visible only when overdue installments exist) -->
                    <div id="calcOverdueBanner" style="display:none; background:#fef2f2; border:1px solid #fecaca; border-left:5px solid #dc2626; border-radius:8px; padding:12px 14px; margin-bottom:14px;">
                        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
                            <div>
                                <strong style="color:#991b1b; font-size:13px; display:block;">
                                    <i class="fa fa-exclamation-triangle"></i> <span id="calcOverdueCountText">0 Overdue Installments</span> (<span id="calcOverdueAmountText">Rs. 0</span>)
                                </strong>
                                <span style="font-size:12px; color:#b91c1c;">
                                    <?php echo __('Important: Early payment discounts apply ONLY to upcoming advance installments (0% on overdue). All overdue installments must be selected and cleared.'); ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Dynamic Overdue Selection Warning Toast -->
                    <div id="calcOverdueWarning" style="display:none; background:#fffbeb; border:1px solid #fde68a; border-left:5px solid #f59e0b; border-radius:8px; padding:10px 14px; margin-bottom:14px;">
                        <strong style="color:#92400e; font-size:12px;">
                            <i class="fa fa-warning"></i> <?php echo __('Overdue Clearance Required:'); ?>
                        </strong>
                        <span id="calcOverdueWarningText" style="font-size:12px; color:#b45309;">
                            <?php echo __('You must select all overdue installments to unlock early payment discounts on upcoming installments.'); ?>
                        </span>
                    </div>

                    <!-- Preset Quick Selectors -->
                    <div style="margin-bottom:14px;">
                        <label style="font-size:12px; font-weight:700; color:#334155; text-transform:uppercase; margin-bottom:6px; display:block;">
                            <?php echo __('Settlement Packages & Advance Selectors:'); ?>
                        </label>
                        <div style="display:flex; flex-wrap:wrap; gap:8px;">
                            <button type="button" id="btnOnlyOverdue" class="btn btn-danger btn-sm" style="font-weight:700; border-radius:6px;" onclick="selectOverdueOnly();">
                                <i class="fa fa-exclamation-circle"></i> <?php echo __('Clear All Overdue Only'); ?>
                            </button>
                            <button type="button" class="btn btn-warning btn-sm" style="background:#f59e0b; border-color:#f59e0b; color:#fff; font-weight:700; border-radius:6px;" onclick="selectOverduePlusUpcoming(3);">
                                <i class="fa fa-plus-circle"></i> <?php echo __('Allow Discount (Overdue + Next 3 Months)'); ?>
                            </button>
                            <button type="button" class="btn btn-warning btn-sm" style="background:#d97706; border-color:#d97706; color:#fff; font-weight:700; border-radius:6px;" onclick="selectOverduePlusUpcoming(6);">
                                <i class="fa fa-plus-circle"></i> <?php echo __('Allow Discount (Overdue + Next 6 Months)'); ?>
                            </button>
                            <button type="button" id="btnSpecialOverdueDiscount" class="btn btn-info btn-sm" style="font-weight:700; border-radius:6px; background:#0284c7; border-color:#0284c7; display:none;" onclick="selectSpecialOverduePackage();">
                                <i class="fa fa-gift"></i> <?php echo __('Special Overdue Clearance Package'); ?>
                            </button>
                            <button type="button" class="btn btn-primary btn-sm" style="background:#ff0a46; border-color:#ff0a46; font-weight:700; border-radius:6px;" onclick="selectFullDealSettlement();">
                                <i class="fa fa-star"></i> <?php echo __('100% Full Deal Settlement'); ?>
                            </button>
                        </div>
                    </div>

                    <!-- Installments Checklist Table -->
                    <div class="table-responsive" style="max-height:230px; overflow-y:auto; background:#fff; border:1px solid #e2e8f0; border-radius:8px; margin-bottom:14px;">
                        <table class="table table-condensed table-hover" style="margin:0; font-size:13px;">
                            <thead style="background:#f1f5f9; position:sticky; top:0; z-index:2;">
                                <tr>
                                    <th width="40" class="text-center"><input type="checkbox" id="calcSelectAll" onchange="toggleAllInstallmentCheckboxes(this.checked);"></th>
                                    <th><?php echo __('Installment Name'); ?></th>
                                    <th><?php echo __('Due Date / Status'); ?></th>
                                    <th class="text-center"><?php echo __('Discount Eligibility'); ?></th>
                                    <th class="text-right"><?php echo __('Amount Due'); ?></th>
                                </tr>
                            </thead>
                            <tbody id="calcInstallmentsBody">
                            </tbody>
                        </table>
                    </div>

                    <!-- Calculation Live Summary Card -->
                    <div style="background:linear-gradient(135deg,#f0fdf4 0%,#dcfce7 100%); border:2px solid #86efac; border-radius:10px; padding:14px; margin-bottom:12px;">
                        <div class="row">
                            <div class="col-xs-6 col-sm-3 text-center" style="border-right:1px dashed #bbf7d0;">
                                <span style="font-size:11px; font-weight:700; color:#166534; text-transform:uppercase;"><?php echo __('Selected Subtotal'); ?></span>
                                <div id="calcSubtotalDisplay" style="font-size:16px; font-weight:800; color:#14532d; margin-top:2px;">Rs. 0</div>
                                <small id="calcBreakdownNote" style="font-size:10px; color:#15803d; display:block;">-</small>
                            </div>
                            <div class="col-xs-6 col-sm-3 text-center" style="border-right:1px dashed #bbf7d0;">
                                <span style="font-size:11px; font-weight:700; color:#166534; text-transform:uppercase;"><?php echo __('Upcoming Discount Rate'); ?></span>
                                <div id="calcRateDisplay" style="font-size:16px; font-weight:800; color:#15803d; margin-top:2px;">0%</div>
                                <small style="font-size:10px; color:#15803d; display:block;"><?php echo __('(0% on Overdue)'); ?></small>
                            </div>
                            <div class="col-xs-6 col-sm-3 text-center" style="border-right:1px dashed #bbf7d0;">
                                <span style="font-size:11px; font-weight:700; color:#166534; text-transform:uppercase;"><?php echo __('Total Savings'); ?></span>
                                <div id="calcSavingsDisplay" style="font-size:17px; font-weight:900; color:#059669; margin-top:2px;">- Rs. 0</div>
                                <small id="calcSavingsDetail" style="font-size:10px; color:#047857; display:block;">Rs. 0 saved</small>
                            </div>
                            <div class="col-xs-6 col-sm-3 text-center">
                                <span style="font-size:11px; font-weight:700; color:#166534; text-transform:uppercase;"><?php echo __('Net Payable Total'); ?></span>
                                <div id="calcNetPayableDisplay" style="font-size:18px; font-weight:900; color:#047857; margin-top:2px;">Rs. 0</div>
                                <small style="font-size:10px; color:#065f46; display:block; font-weight:700;"><?php echo __('Ready for Online Pay'); ?></small>
                            </div>
                        </div>
                    </div>

                    <!-- Terms Notes -->
                    <div style="background:#fff; border:1px solid #e2e8f0; border-radius:8px; padding:10px 14px; font-size:12px; color:#475569;">
                        <div id="calcTermsEn" style="margin-bottom:4px;"></div>
                        <div id="calcTermsUr" style="direction:rtl; font-family:'Jameel Noori Nastaleeq',serif; font-size:15px; color:#334155;"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="background:#fff; border-top:1px solid #e2e8f0; padding:12px 20px;">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Close'); ?></button>
                <a id="calcSyncIcsBtn" href="#" class="btn btn-info" style="background:#0284c7; border-color:#0284c7; font-weight:700;">
                    <i class="fa fa-calendar"></i> <?php echo __('Download .ICS Calendar'); ?>
                </a>
                <a href="<?php echo $this->Html->url(array('controller'=>'UtilityBills','action'=>'index')); ?>" class="btn btn-success" style="background:#16a34a; border-color:#16a34a; font-weight:700;">
                    <i class="fa fa-credit-card"></i> <?php echo __('Proceed to Pay Online'); ?>
                </a>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
var currentCalcData = null;
var isSpecialOverdueMode = false;

function openEarlyDiscountModal(dealId) {
    $('#earlyDiscountModal').modal('show');
    $('#earlyDiscountLoader').show();
    $('#earlyDiscountContent').hide();
    isSpecialOverdueMode = false;

    $.ajax({
        url: '<?php echo $this->Html->url(array("controller"=>"MyDeals","action"=>"early_discount_data")); ?>/' + dealId,
        type: 'GET',
        dataType: 'json',
        success: function(resp) {
            $('#earlyDiscountLoader').hide();
            if(resp && resp.status === 'success') {
                currentCalcData = resp;
                renderEarlyDiscountModal(resp);
                $('#earlyDiscountContent').show();
            } else {
                alert(resp.message || '<?php echo __("Unable to load discount data.");?>');
                $('#earlyDiscountModal').modal('hide');
            }
        },
        error: function() {
            $('#earlyDiscountLoader').hide();
            alert('<?php echo __("Failed to communicate with server.");?>');
            $('#earlyDiscountModal').modal('hide');
        }
    });
}

function renderEarlyDiscountModal(data) {
    var deal = data.deal;
    var settings = data.settings;
    var installments = data.installments;

    $('#calcDealTitle').text(deal.property_name + ' (' + deal.invoice_no + ')');
    $('#calcTotalPending').text('Rs. ' + Number(deal.total_pending_balance).toLocaleString());
    $('#calcTermsEn').text(settings.terms_en || '');
    $('#calcTermsUr').text(settings.terms_ur || '');
    $('#calcSyncIcsBtn').attr('href', '<?php echo $this->Html->url(array("controller"=>"MyDeals","action"=>"calendar_ics")); ?>/' + deal.id);

    // Overdue banner visibility
    if(deal.total_overdue_count > 0) {
        $('#calcOverdueBanner').show();
        $('#calcOverdueCountText').text(deal.total_overdue_count + ' <?php echo __("Overdue Installment(s)");?>');
        $('#calcOverdueAmountText').text('Rs. ' + Number(deal.total_overdue_amount).toLocaleString());
        $('#btnOnlyOverdue').show();
        if(settings.allow_overdue_discount_package && parseFloat(settings.overdue_special_discount_percent) > 0) {
            $('#btnSpecialOverdueDiscount').show();
        } else {
            $('#btnSpecialOverdueDiscount').hide();
        }
    } else {
        $('#calcOverdueBanner').hide();
        $('#btnOnlyOverdue').hide();
        $('#btnSpecialOverdueDiscount').hide();
    }

    var html = '';
    for(var i = 0; i < installments.length; i++) {
        var row = installments[i];
        var isOverdue = row.is_overdue;
        var rowBg = isOverdue ? 'background:#fff1f2;' : '';
        var statusBadge = isOverdue 
            ? '<span class="badge" style="background:#dc2626; font-size:10px; padding:3px 6px;"><?php echo __("OVERDUE");?> (' + row.overdue_days + 'd)</span>'
            : '<span class="badge" style="background:#2563eb; font-size:10px; padding:3px 6px;"><?php echo __("UPCOMING");?></span>';
        var discTag = isOverdue
            ? '<span style="color:#dc2626; font-weight:700; font-size:11px;"><i class="fa fa-times-circle"></i> 0% <?php echo __("No Discount");?></span>'
            : '<span style="color:#16a34a; font-weight:700; font-size:11px;"><i class="fa fa-check-circle"></i> <?php echo __("Eligible");?></span>';

        html += '<tr style="cursor:pointer; ' + rowBg + '" onclick="toggleRowCheckbox(' + i + ', event);">' +
            '<td class="text-center"><input type="checkbox" class="calc-inst-checkbox" data-index="' + i + '" data-amount="' + row.pending_amount + '" data-overdue="' + (isOverdue ? 1 : 0) + '" data-months="' + row.months_ahead + '" onchange="recalculateDiscount();"></td>' +
            '<td><strong>' + escapeHtml(row.name) + '</strong></td>' +
            '<td>' + escapeHtml(row.due_date_formatted) + ' &nbsp;' + statusBadge + '</td>' +
            '<td class="text-center">' + discTag + '</td>' +
            '<td class="text-right" style="font-weight:700; color:' + (isOverdue ? '#dc2626' : '#0f172a') + ';">Rs. ' + Number(row.pending_amount).toLocaleString() + '</td>' +
        '</tr>';
    }
    $('#calcInstallmentsBody').html(html);

    // If there are overdue installments, default select ALL overdue + next upcoming
    if(deal.total_overdue_count > 0) {
        selectOverduePlusUpcoming(3);
    } else {
        var defaultSelectCount = Math.min(installments.length, Math.max(3, settings.min_installments || 3));
        selectUpcomingOnly(defaultSelectCount);
    }
}

function escapeHtml(text) {
    return $('<div>').text(text).html();
}

function toggleRowCheckbox(index, evt) {
    if(evt.target.tagName.toLowerCase() === 'input') {
        return;
    }
    var $cb = $('.calc-inst-checkbox[data-index="' + index + '"]');
    $cb.prop('checked', !$cb.prop('checked'));
    recalculateDiscount();
}

function toggleAllInstallmentCheckboxes(checked) {
    isSpecialOverdueMode = false;
    $('.calc-inst-checkbox').prop('checked', checked);
    recalculateDiscount();
}

function selectOverdueOnly() {
    isSpecialOverdueMode = false;
    $('.calc-inst-checkbox').prop('checked', false);
    $('.calc-inst-checkbox[data-overdue="1"]').prop('checked', true);
    recalculateDiscount();
}

function selectUpcomingOnly(count) {
    isSpecialOverdueMode = false;
    $('.calc-inst-checkbox').prop('checked', false);
    var $upcoming = $('.calc-inst-checkbox[data-overdue="0"]');
    var max = (count === 'all') ? $upcoming.length : Math.min($upcoming.length, parseInt(count));
    for(var i = 0; i < max; i++) {
        $($upcoming[i]).prop('checked', true);
    }
    recalculateDiscount();
}

function selectOverduePlusUpcoming(upcomingCount) {
    isSpecialOverdueMode = false;
    $('.calc-inst-checkbox').prop('checked', false);
    // Select ALL overdue
    $('.calc-inst-checkbox[data-overdue="1"]').prop('checked', true);
    // Select next upcoming
    var $upcoming = $('.calc-inst-checkbox[data-overdue="0"]');
    var max = Math.min($upcoming.length, parseInt(upcomingCount));
    for(var i = 0; i < max; i++) {
        $($upcoming[i]).prop('checked', true);
    }
    recalculateDiscount();
}

function selectFullDealSettlement() {
    isSpecialOverdueMode = false;
    $('.calc-inst-checkbox').prop('checked', true);
    recalculateDiscount();
}

function selectSpecialOverduePackage() {
    isSpecialOverdueMode = true;
    $('.calc-inst-checkbox').prop('checked', false);
    $('.calc-inst-checkbox[data-overdue="1"]').prop('checked', true);
    recalculateDiscount();
}

function recalculateDiscount() {
    if(!currentCalcData) return;
    var deal = currentCalcData.deal;
    var settings = currentCalcData.settings;

    var totalOverdueInList = deal.total_overdue_count || 0;
    var selectedOverdueCount = 0;
    var selectedOverdueAmount = 0;

    var selectedUpcomingCount = 0;
    var selectedUpcomingAmount = 0;
    var upcomingDiscount = 0;

    $('.calc-inst-checkbox:checked').each(function() {
        var isOverdue = parseInt($(this).data('overdue')) === 1;
        var amt = parseFloat($(this).data('amount')) || 0;
        var monthsAhead = parseInt($(this).data('months')) || 0;

        if(isOverdue) {
            selectedOverdueCount++;
            selectedOverdueAmount += amt;
        } else {
            selectedUpcomingCount++;
            selectedUpcomingAmount += amt;

            if(settings.discount_mode === 'annual_rate') {
                var annualRate = parseFloat(settings.annual_rate) || 8.0;
                var disc = amt * (annualRate / 100.0) * (monthsAhead / 12.0);
                upcomingDiscount += Math.max(0, disc);
            } else {
                var flatPercent = parseFloat(settings.discount_percent) || 5.0;
                var disc = amt * (flatPercent / 100.0);
                upcomingDiscount += Math.max(0, disc);
            }
        }
    });

    // Check overdue clearance condition:
    // If client selected ANY upcoming installment while missing ANY overdue installment
    var hasMissingOverdue = (totalOverdueInList > 0 && selectedOverdueCount < totalOverdueInList);
    var requireAllOverdue = parseInt(settings.require_all_overdue) === 1;

    if(hasMissingOverdue && selectedUpcomingCount > 0 && requireAllOverdue) {
        var missingCount = totalOverdueInList - selectedOverdueCount;
        $('#calcOverdueWarning').show();
        $('#calcOverdueWarningText').html('<strong>' + missingCount + ' <?php echo __("overdue installment(s) are unchecked.");?></strong> <?php echo __("You must select all overdue installments (0% discount) to qualify for early payment discounts on upcoming installments.");?>');
        // Strip discount on upcoming until all overdue are selected
        upcomingDiscount = 0;
    } else {
        $('#calcOverdueWarning').hide();
    }

    // Special Overdue Discount / Surcharge Waiver Mode:
    var overdueDiscount = 0;
    if(isSpecialOverdueMode && selectedOverdueCount === totalOverdueInList && totalOverdueInList > 0) {
        var specialRate = parseFloat(settings.overdue_special_discount_percent) || 0;
        if(specialRate > 0) {
            overdueDiscount = selectedOverdueAmount * (specialRate / 100.0);
        }
    }

    // Check min upcoming installments threshold for regular discount
    var minRequired = parseInt(settings.min_installments) || 1;
    var effectiveUpcomingDiscount = 0;
    var effectiveRateLabel = '0%';

    if(selectedUpcomingCount >= minRequired && selectedUpcomingAmount > 0 && (!hasMissingOverdue || !requireAllOverdue)) {
        effectiveUpcomingDiscount = Math.round(upcomingDiscount);
        var effectivePercent = ((effectiveUpcomingDiscount / selectedUpcomingAmount) * 100).toFixed(1);
        effectiveRateLabel = effectivePercent + '%';
    } else if(selectedUpcomingCount > 0) {
        if(hasMissingOverdue && requireAllOverdue) {
            effectiveRateLabel = '<span style="font-size:11px; color:#dc2626; font-weight:700;"><?php echo __("Locked (Clear Overdue First)");?></span>';
        } else {
            effectiveRateLabel = '<span style="font-size:11px; color:#d97706; font-weight:700;">(Min ' + minRequired + ' <?php echo __("Required");?>)</span>';
        }
    }

    var totalSelected = selectedOverdueAmount + selectedUpcomingAmount;
    var totalEffectiveDiscount = effectiveUpcomingDiscount + Math.round(overdueDiscount);
    var netPayable = Math.max(0, Math.round(totalSelected - totalEffectiveDiscount));

    $('#calcSubtotalDisplay').text('Rs. ' + totalSelected.toLocaleString());
    $('#calcBreakdownNote').text('Overdue: Rs. ' + selectedOverdueAmount.toLocaleString() + ' | Upcoming: Rs. ' + selectedUpcomingAmount.toLocaleString());
    $('#calcRateDisplay').html(effectiveRateLabel);
    $('#calcSavingsDisplay').text('- Rs. ' + totalEffectiveDiscount.toLocaleString());
    $('#calcSavingsDetail').text('Rs. ' + totalEffectiveDiscount.toLocaleString() + ' <?php echo __("net total savings");?>');
    $('#calcNetPayableDisplay').text('Rs. ' + netPayable.toLocaleString());

    // Update select all checkbox state
    var totalCbs = $('.calc-inst-checkbox').length;
    var totalChecked = $('.calc-inst-checkbox:checked').length;
    $('#calcSelectAll').prop('checked', totalCbs > 0 && totalChecked === totalCbs);
}
</script>


