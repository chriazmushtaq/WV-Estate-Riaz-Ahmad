<?php
if(!function_exists('wv_payment_plan_detail_money'))
{
    function wv_payment_plan_detail_money($amount)
    {
        $amount = (float)$amount;
        $decimals = (abs($amount - round($amount)) > 0.0001) ? 2 : 0;
        return 'Rs '.number_format($amount, $decimals);
    }
}
if(!function_exists('wv_payment_plan_detail_value'))
{
    function wv_payment_plan_detail_value($amount)
    {
        $value = trim((string)$amount);
        return ($value !== '' && is_numeric(str_replace(',', '', $value))) ? wv_payment_plan_detail_money(str_replace(',', '', $value)) : h($value);
    }
}
$plan = !empty($page['AgencyPaymentPlanPage']) ? (array)$page['AgencyPaymentPlanPage'] : array();
$agency = !empty($page['Agency']) ? (array)$page['Agency'] : array();
$amountPlans = !empty($plan['amount_plans']) ? (array)$plan['amount_plans'] : array();
$logo = !empty($frontLogo) ? $frontLogo : (!empty($themeSettings['brand_logo_image']) ? $themeSettings['brand_logo_image'] : 'logo-website.fw.png');
$brandTitle = !empty($agency['name']) ? $agency['name'] : (!empty($siteOrganization) ? $siteOrganization : (!empty($siteName) ? $siteName : 'White Villas'));
$phone = !empty($agency['phone_display']) ? $agency['phone_display'] : (!empty($contact) ? $contact : '');
$email = !empty($agency['email_display']) ? $agency['email_display'] : (!empty($siteEmail) ? $siteEmail : '');
$summaryTitle = !empty($plan['summary_title']) ? $plan['summary_title'] : __('Main Amounts');
$highlightsTitle = !empty($plan['highlights_title']) ? $plan['highlights_title'] : '';
$footerTitle = !empty($plan['footer_title']) ? $plan['footer_title'] : '';
$footerText = !empty($plan['footer_text']) ? $plan['footer_text'] : '';
?>
<style type="text/css">
.wv-plan-detail{background:linear-gradient(180deg,#eef4fb 0%,#ffffff 54%,#f6f9fc 100%);color:#07152a;margin:-1px 0 0;padding:34px 0 56px;}
.wv-plan-detail .wv-wrap{margin:0 auto;max-width:1180px;padding:0 18px;}
.wv-detail-hero{background:linear-gradient(124deg,#07152a 0%,#11192e 54%,#6f0027 130%);border:1px solid rgba(255,10,70,.34);border-radius:14px;box-shadow:0 24px 54px rgba(7,21,42,.22);color:#fff;display:grid;grid-template-columns:minmax(0,1fr) 320px;gap:28px;margin-bottom:24px;overflow:hidden;padding:32px;position:relative;}
.wv-detail-hero:before{background:linear-gradient(90deg,#ff0a46 0%,#8ff7ff 48%,rgba(143,247,255,0) 100%);content:"";height:3px;left:0;position:absolute;right:0;top:0;}
.wv-detail-hero:after{background:linear-gradient(135deg,rgba(255,255,255,.12),rgba(255,255,255,0));content:"";height:100%;position:absolute;right:-22%;top:0;transform:skewX(-14deg);width:44%;}
.wv-detail-copy{position:relative;z-index:1;}
.wv-detail-brand{align-items:center;display:flex;gap:15px;margin-bottom:20px;}
.wv-detail-brand img{background:#fff;border:1px solid rgba(255,255,255,.58);border-radius:12px;box-shadow:0 14px 30px rgba(0,0,0,.22);height:78px;object-fit:contain;padding:9px;width:78px;}
.wv-detail-brand span{background:rgba(143,247,255,.12);border:1px solid rgba(143,247,255,.28);border-radius:999px;color:#8ff7ff;display:inline-flex;font-size:10px;font-weight:900;letter-spacing:.05em;margin-bottom:8px;padding:6px 10px;text-transform:uppercase;}
.wv-detail-brand strong{color:#fff;display:block;font-size:22px;font-weight:900;line-height:1.15;}
.wv-detail-hero h1{color:#fff;font-size:34px;font-weight:900;letter-spacing:0;line-height:1.12;margin:0 0 10px;max-width:760px;}
.wv-detail-highlight-title{background:rgba(143,247,255,.12);border:1px solid rgba(143,247,255,.3);border-radius:999px;color:#8ff7ff;display:inline-flex;font-size:12px;font-weight:900;letter-spacing:.03em;margin:0 0 13px;padding:7px 12px;text-transform:uppercase;}
.wv-detail-hero p{border-left:3px solid #ff0a46;color:#dceaff;font-size:14px;font-weight:800;line-height:1.65;margin:0;max-width:700px;padding-left:12px;}
.wv-hero-contact{align-self:stretch;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.2);border-radius:12px;box-shadow:inset 0 1px 0 rgba(255,255,255,.08),0 18px 36px rgba(0,0,0,.16);display:flex;flex-direction:column;gap:11px;justify-content:center;padding:20px;position:relative;z-index:1;}
.wv-hero-contact:before{background:#ff0a46;border-radius:12px 0 0 12px;bottom:12px;content:"";left:0;position:absolute;top:12px;width:4px;}
.wv-hero-contact h2{color:#fff;font-size:20px;font-weight:900;line-height:1.2;margin:0 0 5px;}
.wv-contact-line{align-items:center;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.11);border-radius:8px;color:#eaf4ff;display:flex;font-size:13px;font-weight:900;gap:9px;line-height:1.35;min-width:0;padding:10px 11px;}
.wv-contact-line i{color:#8ff7ff;flex:0 0 15px;margin:0;text-align:center;width:15px;}
.wv-contact-button{background:#ff0a46;border-radius:8px;box-shadow:0 14px 28px rgba(255,10,70,.28);color:#fff!important;display:inline-flex;font-size:12px;font-weight:900;justify-content:center;line-height:1;margin-top:6px;padding:14px 14px;text-decoration:none;text-transform:uppercase;transition:background .18s ease,transform .18s ease;}
.wv-contact-button:hover{background:#c70032;color:#fff!important;transform:translateY(-1px);}
.wv-metric-grid{align-items:stretch;display:flex;flex-wrap:wrap;gap:8px;justify-content:center;margin-bottom:18px;}
.wv-metric{background:#fff;border:1px solid #dde8f3;border-radius:10px;box-shadow:0 12px 26px rgba(15,23,42,.06);flex:0 0 auto;min-width:118px;padding:11px 13px;position:relative;}
.wv-metric:before{background:#ff0a46;border-radius:10px 10px 0 0;content:"";height:3px;left:0;position:absolute;right:0;top:0;}
.wv-metric span{color:#64748b;display:block;font-size:9px;font-weight:900;letter-spacing:0;line-height:1.3;text-transform:uppercase;white-space:nowrap;}
.wv-metric strong{color:#07152a;display:block;font-size:16px;font-weight:900;margin-top:5px;white-space:nowrap;}
.wv-metric.total{background:#9f002d;border-color:#9f002d;box-shadow:0 18px 34px rgba(159,0,45,.28);}
.wv-metric.total:before{background:rgba(255,255,255,.42);}
.wv-metric.total span{color:#ffe6ee;}
.wv-metric.total{min-width:150px;padding-right:17px;}
.wv-metric.total strong{color:#fff;font-size:25px;line-height:1.15;text-shadow:0 2px 10px rgba(0,0,0,.18);white-space:normal;}
.wv-main-amount-info{background:#fff;border:1px solid #dde8f3;border-left:4px solid #ff0a46;border-radius:10px;box-shadow:0 12px 26px rgba(15,23,42,.06);margin:-4px 0 20px;padding:16px 18px;}
.wv-main-amount-info h3{color:#07152a;font-size:16px;font-weight:900;margin:0 0 6px;}
.wv-main-amount-info p{color:#435166;font-size:13px;font-weight:700;line-height:1.7;margin:0;}
.wv-amount-plan-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;margin:0 0 20px;}
.wv-amount-plan-card{background:#fff;border:1px solid #dde8f3;border-radius:12px;box-shadow:0 14px 30px rgba(15,23,42,.07);overflow:hidden;padding:14px;position:relative;}
.wv-amount-plan-card:before{background:linear-gradient(90deg,#ff0a46,#9f002d);content:"";height:4px;left:0;position:absolute;right:0;top:0;}
.wv-amount-plan-card .wv-amount-section-label{color:#ff0a46;font-size:10px;font-weight:900;margin:0 0 5px;text-transform:uppercase;}
.wv-amount-plan-card h3{color:#07152a;font-size:18px;font-weight:900;margin:0 0 4px;}
.wv-amount-highlight-title{color:#ff0a46;font-size:11px;font-weight:900;margin:0 0 5px;text-transform:uppercase;}
.wv-amount-plan-card .wv-amount-unit{color:#64748b;font-size:11px;font-weight:900;margin-bottom:12px;text-transform:uppercase;}
.wv-amount-plan-values{align-items:stretch;display:grid;gap:6px;grid-template-columns:repeat(6,minmax(0,1fr));width:100%;}
.wv-amount-plan-values div{background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;min-width:0;padding:7px 6px;}
.wv-amount-plan-values span{color:#64748b;display:block;font-size:6.5px;font-weight:900;letter-spacing:0;line-height:1.15;overflow:hidden;text-overflow:ellipsis;text-transform:uppercase;white-space:nowrap;}
.wv-amount-plan-values strong{color:#07152a;display:block;font-size:clamp(9px,.66vw,10.5px);font-weight:900;line-height:1.15;margin-top:5px;overflow:hidden;overflow-wrap:normal;text-overflow:clip;white-space:nowrap;word-break:normal;}
.wv-amount-plan-values .total{background:#9f002d;border-color:#9f002d;box-shadow:0 14px 28px rgba(159,0,45,.22);grid-column:1/-1;padding:13px 14px;}
.wv-amount-plan-values .total span{color:#ffe6ee;}
.wv-amount-plan-values .total strong{color:#fff;font-size:22px;line-height:1.15;text-shadow:0 2px 10px rgba(0,0,0,.18);white-space:normal;}
.wv-amount-note{color:#435166;font-size:12px;font-weight:700;line-height:1.6;margin:10px 0 0;}
.wv-amount-plan-footer{background:#f8fafc;border:1px solid #e2e8f0;border-radius:7px;margin-top:12px;padding:11px;}
.wv-amount-plan-footer strong{color:#07152a;display:block;font-size:12px;font-weight:900;margin-bottom:4px;}
.wv-amount-plan-footer p{color:#435166;font-size:12px;font-weight:700;line-height:1.6;margin:0;}
.wv-section-heading{display:block;margin:0 0 13px;text-align:center;}
.wv-section-heading h2{color:#07152a;font-size:22px;font-weight:900;letter-spacing:0;margin:0;}
.wv-main-highlight-title{background:#fff1f4;border:1px solid #ffd0dc;border-radius:999px;color:#ff0a46;display:inline-flex;font-size:11px;font-weight:900;line-height:1.25;margin:8px auto 0;padding:6px 12px;text-transform:uppercase;}
.wv-back-link{display:inline-flex;margin-bottom:14px;}
.wv-back-link a{background:#fff;border:1px solid #d8e4f0;border-radius:8px;color:#07152a!important;font-size:12px;font-weight:900;padding:10px 12px;text-decoration:none;text-transform:uppercase;}
.wv-back-link a:hover{border-color:#ff0a46;color:#ff0a46!important;}
@media(max-width:1100px){.wv-metric-grid{flex-wrap:wrap}.wv-detail-hero{grid-template-columns:1fr}.wv-hero-contact{align-self:auto}.wv-amount-plan-grid{grid-template-columns:1fr}.wv-amount-plan-values strong{font-size:12px}}
@media(max-width:760px){.wv-plan-detail{padding:22px 0 36px}.wv-detail-hero{padding:22px}.wv-detail-hero h1{font-size:26px}.wv-detail-hero:after{display:none}.wv-amount-plan-grid{grid-template-columns:1fr}.wv-amount-plan-values{display:grid;grid-template-columns:1fr}.wv-amount-plan-values div{min-width:0}.wv-metric-grid{display:grid;grid-template-columns:1fr}.wv-detail-brand{align-items:flex-start}.wv-detail-brand img{height:58px;width:58px}.wv-detail-brand strong{font-size:18px}.wv-hero-contact h2{font-size:18px}}
</style>

<section class="wv-plan-detail">
    <div class="wv-wrap">
        <div class="wv-back-link">
            <?php echo $this->Html->link('<i class="fa fa-angle-left"></i> '.__('All Payment Plans'),array('controller'=>'paymentplans','action'=>'index'),array('escape'=>false));?>
        </div>

        <div class="wv-detail-hero">
            <div class="wv-detail-copy">
                <div class="wv-detail-brand">
                    <?php echo $this->Html->image($logo,array('alt'=>$siteName));?>
                    <div>
                        <span><?php echo h(!empty($plan['unit_label']) ? $plan['unit_label'] : __('Apartment'));?></span>
                        <strong><?php echo h(!empty($agency['name']) ? $agency['name'] : $siteName);?></strong>
                    </div>
                </div>
                <h1><?php echo h(!empty($plan['page_title']) ? $plan['page_title'] : __('Apartment Payment Plan'));?></h1>
                <?php if($highlightsTitle !== ''){ ?><div class="wv-detail-highlight-title"><?php echo h($highlightsTitle);?></div><?php } ?>
                <p><?php echo h(!empty($plan['hero_subtitle']) ? str_replace('apartment ', '', $plan['hero_subtitle']) : __('Flexible booking and installment details'));?></p>
            </div>
            <aside class="wv-hero-contact">
                <h2><?php echo h(!empty($plan['contact_heading']) ? $plan['contact_heading'] : __('Contact Agency'));?></h2>
                <?php if($phone !== ''){ ?><div class="wv-contact-line"><i class="fa fa-phone"></i><?php echo h($phone);?></div><?php } ?>
                <?php if($email !== ''){ ?><div class="wv-contact-line"><i class="fa fa-envelope"></i><?php echo h($email);?></div><?php } ?>
                <?php if($phone !== ''){ ?><a class="wv-contact-button" href="tel:<?php echo h(preg_replace('/[^0-9\+]/','',$phone));?>"><?php echo h(!empty($plan['cta_label']) ? $plan['cta_label'] : __('Contact Agency'));?></a><?php } ?>
            </aside>
        </div>

        <div class="wv-section-heading">
            <h2><?php echo h($summaryTitle);?></h2>
            <?php if($highlightsTitle !== ''){ ?><div class="wv-main-highlight-title"><?php echo h($highlightsTitle);?></div><?php } ?>
        </div>
        <div class="wv-metric-grid">
            <div class="wv-metric"><span><?php echo __('Booking');?></span><strong><?php echo wv_payment_plan_detail_money(!empty($plan['booking_amount']) ? $plan['booking_amount'] : 0);?></strong></div>
            <div class="wv-metric"><span><?php echo __('Confirmation');?></span><strong><?php echo wv_payment_plan_detail_money(!empty($plan['confirmation_amount']) ? $plan['confirmation_amount'] : 0);?></strong></div>
            <div class="wv-metric"><span><?php echo __('Allotment');?></span><strong><?php echo wv_payment_plan_detail_money(!empty($plan['allotment_amount']) ? $plan['allotment_amount'] : 0);?></strong></div>
            <div class="wv-metric"><span><?php echo __('Monthly');?></span><strong><?php echo wv_payment_plan_detail_money(!empty($plan['monthly_installment']) ? $plan['monthly_installment'] : 0);?></strong></div>
            <div class="wv-metric"><span><?php echo __('Bi-Annualy');?></span><strong><?php echo wv_payment_plan_detail_money(!empty($plan['quarterly_installment']) ? $plan['quarterly_installment'] : 0);?></strong></div>
            <div class="wv-metric"><span><?php echo __('Possession');?></span><strong><?php echo wv_payment_plan_detail_money(!empty($plan['possession_amount']) ? $plan['possession_amount'] : 0);?></strong></div>
            <div class="wv-metric total"><span><?php echo __('Total Price');?></span><strong><?php echo wv_payment_plan_detail_money(!empty($plan['total_price']) ? $plan['total_price'] : 0);?></strong></div>
        </div>
        <?php if(!empty($amountPlans)){ ?>
        <div class="wv-amount-plan-grid">
            <?php foreach($amountPlans as $amountPlan){
                $planTitle = !empty($amountPlan['plan_title']) ? $amountPlan['plan_title'] : __('Additional Plan');
                $unitTitle = !empty($amountPlan['unit_label']) ? $amountPlan['unit_label'] : '';
                $amountSummaryTitle = !empty($amountPlan['summary_title']) ? $amountPlan['summary_title'] : '';
                $amountHighlightsTitle = !empty($amountPlan['highlights_title']) ? $amountPlan['highlights_title'] : '';
                $amountFooterTitle = !empty($amountPlan['footer_title']) ? $amountPlan['footer_title'] : '';
                $amountFooterText = !empty($amountPlan['footer_text']) ? $amountPlan['footer_text'] : '';
            ?>
            <article class="wv-amount-plan-card">
                <?php if($amountSummaryTitle !== ''){ ?><div class="wv-amount-section-label"><?php echo h($amountSummaryTitle);?></div><?php } ?>
                <h3><?php echo h($planTitle);?></h3>
                <?php if($amountHighlightsTitle !== ''){ ?><div class="wv-amount-highlight-title"><?php echo h($amountHighlightsTitle);?></div><?php } ?>
                <?php if($unitTitle !== ''){ ?><div class="wv-amount-unit"><?php echo h($unitTitle);?></div><?php } ?>
                <div class="wv-amount-plan-values">
                    <div><span><?php echo __('Booking');?></span><strong><?php echo wv_payment_plan_detail_value(isset($amountPlan['booking_amount']) ? $amountPlan['booking_amount'] : '');?></strong></div>
                    <div><span><?php echo __('Confirmation');?></span><strong><?php echo wv_payment_plan_detail_value(isset($amountPlan['confirmation_amount']) ? $amountPlan['confirmation_amount'] : '');?></strong></div>
                    <div><span><?php echo __('Allotment');?></span><strong><?php echo wv_payment_plan_detail_value(isset($amountPlan['allotment_amount']) ? $amountPlan['allotment_amount'] : '');?></strong></div>
                    <div><span><?php echo __('Monthly');?></span><strong><?php echo wv_payment_plan_detail_value(isset($amountPlan['monthly_installment']) ? $amountPlan['monthly_installment'] : '');?></strong></div>
                    <div><span><?php echo __('Bi-Annualy');?></span><strong><?php echo wv_payment_plan_detail_value(isset($amountPlan['quarterly_installment']) ? $amountPlan['quarterly_installment'] : '');?></strong></div>
                    <div><span><?php echo __('Possession');?></span><strong><?php echo wv_payment_plan_detail_value(isset($amountPlan['possession_amount']) ? $amountPlan['possession_amount'] : '');?></strong></div>
                    <div class="total"><span><?php echo __('Total');?></span><strong><?php echo wv_payment_plan_detail_value(isset($amountPlan['total_price']) ? $amountPlan['total_price'] : '');?></strong></div>
                </div>
                <?php if(!empty($amountPlan['note'])){ ?><p class="wv-amount-note"><?php echo h($amountPlan['note']);?></p><?php } ?>
                <?php if($amountFooterTitle !== '' || $amountFooterText !== ''){ ?>
                <div class="wv-amount-plan-footer">
                    <?php if($amountFooterTitle !== ''){ ?><strong><?php echo h($amountFooterTitle);?></strong><?php } ?>
                    <?php if($amountFooterText !== ''){ ?><p><?php echo nl2br(h($amountFooterText));?></p><?php } ?>
                </div>
                <?php } ?>
            </article>
            <?php } ?>
        </div>
        <?php } ?>
        <?php if($footerTitle !== '' || $footerText !== ''){ ?>
        <div class="wv-main-amount-info">
            <?php if($footerTitle !== ''){ ?><h3><?php echo h($footerTitle);?></h3><?php } ?>
            <?php if($footerText !== ''){ ?><p><?php echo nl2br(h($footerText));?></p><?php } ?>
        </div>
        <?php } ?>

    </div>
</section>
