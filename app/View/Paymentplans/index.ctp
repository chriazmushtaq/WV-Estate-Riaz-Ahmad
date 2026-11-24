<?php
if(!function_exists('wv_payment_plan_money'))
{
    function wv_payment_plan_money($amount)
    {
        $amount = (float)$amount;
        $decimals = (abs($amount - round($amount)) > 0.0001) ? 2 : 0;
        return 'Rs '.number_format($amount, $decimals);
    }
}
$logo = !empty($frontLogo) ? $frontLogo : 'logo-website.fw.png';
?>
<style type="text/css">
.wv-public-plans{background:#f3f7fc;color:#07152a;margin:-1px 0 0;padding:34px 0 54px;}
.wv-public-plans .wv-wrap{margin:0 auto;max-width:1180px;padding:0 18px;}
.wv-public-hero{align-items:center;background:#07152a;border-bottom:3px solid #ff0a46;border-radius:8px;color:#fff;display:grid;grid-template-columns:88px 1fr;gap:18px;margin-bottom:22px;padding:24px;box-shadow:0 18px 34px rgba(7,21,42,.16);}
.wv-public-hero img{background:#fff;border-radius:8px;height:72px;object-fit:contain;padding:8px;width:72px;}
.wv-public-hero h1{color:#fff;font-size:30px;font-weight:900;letter-spacing:0;margin:0 0 6px;}
.wv-public-hero p{color:#cfe2ff;font-size:14px;font-weight:700;line-height:1.6;margin:0;}
.wv-plan-list{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:20px;}
.wv-plan-card{background:#fff;border:1px solid #dbe7f4;border-radius:8px;box-shadow:0 16px 34px rgba(15,23,42,.08);display:flex;flex-direction:column;min-height:330px;overflow:hidden;padding:0;position:relative;transition:border-color .2s ease,box-shadow .2s ease,transform .2s ease;}
.wv-plan-card:before{background:#ff0a46;content:"";height:4px;left:0;position:absolute;right:0;top:0;}
.wv-plan-card:hover{border-color:rgba(255,10,70,.28);box-shadow:0 24px 44px rgba(15,23,42,.14);transform:translateY(-3px);}
.wv-plan-card-head{align-items:center;background:#fff;display:grid;grid-template-columns:54px 1fr;gap:13px;padding:18px 18px 14px;}
.wv-plan-card-logo{background:#f8fafc;border:1px solid #e0e8f2;border-radius:8px;height:54px;object-fit:contain;padding:7px;width:54px;}
.wv-plan-card h2{color:#07152a;font-size:18px;font-weight:900;letter-spacing:0;line-height:1.25;margin:0;}
.wv-plan-agency{color:#ff0a46;font-size:11px;font-weight:900;line-height:1.3;text-transform:uppercase;}
.wv-plan-card-body{display:flex;flex:1;flex-direction:column;padding:0 18px 18px;}
.wv-plan-property-title{align-items:center;background:#f8fafc;border:1px solid #e1ebf5;border-radius:8px;color:#07152a;display:flex;font-size:20px;font-weight:900;gap:9px;line-height:1.25;margin:0 0 14px;padding:13px 14px;}
.wv-plan-property-title .fa{color:#ff0a46;font-size:15px;}
.wv-plan-price{background:#ff0a46;border:1px solid #ff0a46;border-radius:8px;box-shadow:0 14px 26px rgba(255,10,70,.22);margin-bottom:14px;padding:16px 18px;}
.wv-plan-price span{color:#fff;display:block;font-size:11px;font-weight:900;text-transform:uppercase;}
.wv-plan-price strong{color:#fff;display:block;font-size:31px;font-weight:900;line-height:1.12;margin-top:5px;}
.wv-plan-meta{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin:0 0 14px;}
.wv-plan-meta div{background:#fbfdff;border:1px solid #e1ebf5;border-radius:8px;padding:12px;}
.wv-plan-meta span{color:#64748b;display:block;font-size:10px;font-weight:900;text-transform:uppercase;}
.wv-plan-meta strong{color:#07152a;display:block;font-size:14px;font-weight:900;margin-top:4px;}
.wv-plan-intro{color:#435166;font-size:13px;font-weight:700;line-height:1.65;margin:0 0 18px;}
.wv-plan-actions{align-items:center;display:flex;gap:10px;margin-top:auto;}
.wv-plan-actions a{align-items:center;background:#ff0a46;border-radius:7px;box-shadow:0 10px 18px rgba(255,10,70,.20);color:#fff!important;display:inline-flex;font-size:12px;font-weight:900;gap:8px;line-height:1;padding:13px 15px;text-decoration:none;text-transform:uppercase;}
.wv-plan-actions a:hover{background:#d9083d;color:#fff!important;}
.wv-empty-plan{background:#fff;border:1px dashed #cbd5e1;border-radius:8px;color:#435166;font-size:14px;font-weight:700;line-height:1.7;padding:24px;text-align:center;}
@media(max-width:991px){.wv-plan-list{grid-template-columns:1fr 1fr}.wv-public-hero{grid-template-columns:64px 1fr}.wv-public-hero h1{font-size:24px}.wv-plan-price strong{font-size:26px}}
@media(max-width:820px){.wv-plan-list{grid-template-columns:1fr}}
@media(max-width:640px){.wv-public-plans{padding:22px 0 34px}.wv-plan-list,.wv-public-hero{grid-template-columns:1fr}.wv-public-hero img{height:64px;width:64px}.wv-plan-meta{grid-template-columns:1fr}}
</style>

<section class="wv-public-plans">
    <div class="wv-wrap">
        <div class="wv-public-hero">
            <?php echo $this->Html->image($logo,array('alt'=>$siteName));?>
            <div>
                <h1><?php echo __('Our Projects Latest Payment Plans');?></h1>
                <p><?php echo __('Select an agency to view its current apartment booking amounts, installment schedule, and contact details.');?></p>
            </div>
        </div>

        <?php if(!empty($pages)){ ?>
        <div class="wv-plan-list">
            <?php foreach((array)$pages as $pageRow){
                $plan = !empty($pageRow['AgencyPaymentPlanPage']) ? (array)$pageRow['AgencyPaymentPlanPage'] : array();
                $agency = !empty($pageRow['Agency']) ? (array)$pageRow['Agency'] : array();
                $amountPlans = !empty($plan['amount_plans']) ? (array)$plan['amount_plans'] : array();
                $firstAmountPlan = !empty($amountPlans[0]) ? (array)$amountPlans[0] : array();
                $propertyTitle = !empty($plan['summary_title']) ? $plan['summary_title'] : (!empty($plan['page_title']) ? $plan['page_title'] : (!empty($agency['name']) ? $agency['name'] : __('Project')));
                $totalPrice = !empty($plan['total_price']) ? $plan['total_price'] : (!empty($firstAmountPlan['total_price']) ? $firstAmountPlan['total_price'] : 0);
                $bookingAmount = !empty($plan['booking_amount']) ? $plan['booking_amount'] : (!empty($firstAmountPlan['booking_amount']) ? $firstAmountPlan['booking_amount'] : 0);
                $monthlyAmount = !empty($plan['monthly_installment']) ? $plan['monthly_installment'] : (!empty($firstAmountPlan['monthly_installment']) ? $firstAmountPlan['monthly_installment'] : 0);
            ?>
            <article class="wv-plan-card">
                <div class="wv-plan-card-head">
                    <?php echo $this->Html->image($logo,array('alt'=>$siteName,'class'=>'wv-plan-card-logo'));?>
                    <div>
                        <h2><?php echo h(!empty($plan['page_title']) ? $plan['page_title'] : __('Payment Plan'));?></h2>
                        <div class="wv-plan-agency"><?php echo h(!empty($agency['name']) ? $agency['name'] : __('Agency'));?></div>
                    </div>
                </div>
                <div class="wv-plan-card-body">
                    <div class="wv-plan-property-title"><span class="fa fa-building"></span><?php echo h($propertyTitle);?></div>
                    <div class="wv-plan-price">
                        <span><?php echo __('Total Price');?></span>
                        <strong><?php echo wv_payment_plan_money($totalPrice);?></strong>
                    </div>
                    <div class="wv-plan-meta">
                        <div>
                            <span><?php echo __('Booking');?></span>
                            <strong><?php echo wv_payment_plan_money($bookingAmount);?></strong>
                        </div>
                        <div>
                            <span><?php echo __('Monthly');?></span>
                            <strong><?php echo wv_payment_plan_money($monthlyAmount);?></strong>
                        </div>
                    </div>
                    <p class="wv-plan-intro"><?php echo __('View the current agency-wise apartment payment plan.');?></p>
                    <div class="wv-plan-actions">
                        <?php echo $this->Html->link(__('View Plan').' <span class="fa fa-angle-right"></span>',!empty($plan['public_url']) ? $plan['public_url'] : array('controller'=>'paymentplans','action'=>'view',$plan['slug']),array('escape'=>false));?>
                    </div>
                </div>
            </article>
            <?php } ?>
        </div>
        <?php } else { ?>
        <div class="wv-empty-plan">
            <?php echo __('No public agency payment plan is currently enabled. Please check again later or contact the office for the latest payment schedule.');?>
        </div>
        <?php } ?>
    </div>
</section>
