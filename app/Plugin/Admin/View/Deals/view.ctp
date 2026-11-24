<?php
$deal = !empty($post['Deal']) ? $post['Deal'] : array();
$client = !empty($post['Client']) ? $post['Client'] : array();
$property = !empty($post['Property']) ? $post['Property'] : array();
$agent = !empty($post['User']) ? $post['User'] : array();
$agency = !empty($agencyProject['Project']) ? $agencyProject['Project'] : array();
$plan = !empty($post['Plan']) ? $post['Plan'] : array();
$type = !empty($post['Type']) ? $post['Type'] : array();
$contract = !empty($post['Contract']) ? $post['Contract'] : array();
$unit = !empty($post['Unit']) ? $post['Unit'] : array();
$dealTotal = isset($deal['total_amount']) ? (float)$deal['total_amount'] : 0;
$balanceDue = max(0, $dealTotal - (float)$paidAmount);
$money = function($value) use ($currency) {
    return $currency.number_format((float)$value, 0);
};
$show = function($value) {
    $value = trim((string)$value);
    return $value !== '' ? h($value) : '<span class="deal-view-empty">-</span>';
};
$showDate = function($value) {
    if(empty($value) || $value === '0000-00-00' || $value === '0000-00-00 00:00:00') {
        return '<span class="deal-view-empty">-</span>';
    }
    return h(date('d-M-Y', strtotime($value)));
};
?>
<style>
.deal-view-modal{width:min(1280px,calc(100vw - 34px));margin:18px auto;color:#102746}
.deal-view-modal .panel{border:0;border-radius:8px;overflow:hidden;box-shadow:0 18px 45px rgba(15,39,70,.22);margin:0}
.deal-view-header{display:flex;align-items:center;justify-content:space-between;padding:15px 20px;background:#102746;color:#fff}
.deal-view-header h3{margin:0;color:#fff;font-size:20px;font-weight:700;letter-spacing:0}
.deal-view-header .close{width:34px;height:34px;color:#fff;opacity:1;font-size:25px;line-height:28px;text-shadow:none}
.deal-view-body{padding:16px;background:#f5f8fc}
.deal-view-summary{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin-bottom:12px}
.deal-view-stat{min-height:76px;padding:12px 14px;background:#fff;border:1px solid #d5e0ee;border-left:4px solid #185adb;border-radius:7px}
.deal-view-stat span{display:block;color:#637792;font-size:11px;font-weight:700;text-transform:uppercase}
.deal-view-stat strong{display:block;margin-top:6px;color:#102746;font-size:17px;line-height:1.25;overflow-wrap:anywhere}
.deal-view-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px}
.deal-view-section{background:#fff;border:1px solid #d5e0ee;border-radius:7px;overflow:hidden}
.deal-view-section h4{display:flex;align-items:center;gap:8px;margin:0;padding:11px 13px;background:#eaf2fd;color:#102746;font-size:14px;font-weight:700;letter-spacing:0}
.deal-view-list{margin:0;padding:5px 13px 10px}
.deal-view-row{display:grid;grid-template-columns:118px minmax(0,1fr);gap:10px;padding:8px 0;border-bottom:1px solid #edf1f7}
.deal-view-row:last-child{border-bottom:0}
.deal-view-row dt{margin:0;color:#657892;font-size:11px;font-weight:700;text-transform:uppercase}
.deal-view-row dd{margin:0;color:#102746;font-size:13px;font-weight:600;line-height:1.4;overflow-wrap:anywhere}
.deal-view-empty{color:#9aa8b9;font-weight:400}
.deal-view-wide{grid-column:span 2}
.deal-view-status{display:inline-block;padding:4px 9px;border-radius:12px;background:#e5f8ef;color:#087a4e;font-size:11px;font-weight:700}
@media(max-width:991px){.deal-view-summary{grid-template-columns:repeat(2,minmax(0,1fr))}.deal-view-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.deal-view-wide{grid-column:span 2}}
@media(max-width:640px){.deal-view-modal{width:calc(100vw - 14px);margin:7px auto}.deal-view-body{padding:10px}.deal-view-summary,.deal-view-grid{grid-template-columns:1fr}.deal-view-wide{grid-column:auto}.deal-view-row{grid-template-columns:105px minmax(0,1fr)}.deal-view-header{padding:12px 14px}}
</style>

<div class="deal-view-modal">
    <div class="panel panel-custom">
        <div class="deal-view-header">
            <h3><i class="fa fa-briefcase"></i> <?php echo __('Deal Details');?></h3>
            <button type="button" class="close" data-dismiss="modal" aria-label="<?php echo h(__('Close'));?>">&times;</button>
        </div>
        <div class="deal-view-body">
            <?php echo $this->Session->flash();?>
            <div class="deal-view-summary">
                <div class="deal-view-stat"><span><?php echo __('Membership No.');?></span><strong><?php echo $show(isset($deal['invoice_no']) ? $deal['invoice_no'] : '');?></strong></div>
                <div class="deal-view-stat"><span><?php echo __('Deal Total');?></span><strong><?php echo $money($dealTotal);?></strong></div>
                <div class="deal-view-stat"><span><?php echo __('Paid Amount');?></span><strong><?php echo $money($paidAmount);?></strong></div>
                <div class="deal-view-stat"><span><?php echo __('Balance Due');?></span><strong><?php echo $money($balanceDue);?></strong></div>
            </div>

            <div class="deal-view-grid">
                <section class="deal-view-section">
                    <h4><i class="fa fa-file-text-o"></i> <?php echo __('Deal Information');?></h4>
                    <dl class="deal-view-list">
                        <div class="deal-view-row"><dt><?php echo __('Invoice Date');?></dt><dd><?php echo $showDate(isset($deal['date']) ? $deal['date'] : '');?></dd></div>
                        <div class="deal-view-row"><dt><?php echo __('Status');?></dt><dd><span class="deal-view-status"><?php echo $show(isset($deal['deal_status']) ? $deal['deal_status'] : 'Active');?></span></dd></div>
                        <div class="deal-view-row"><dt><?php echo __('Payment Plan');?></dt><dd><?php echo $show(isset($plan['name']) ? $plan['name'] : '');?></dd></div>
                        <div class="deal-view-row"><dt><?php echo __('Schedule');?></dt><dd><?php echo (int)$scheduleCount.' '.__('payments');?></dd></div>
                        <div class="deal-view-row"><dt><?php echo __('Booking');?></dt><dd><?php echo $money(isset($deal['booking_amount']) ? $deal['booking_amount'] : 0);?></dd></div>
                        <div class="deal-view-row"><dt><?php echo __('Discount');?></dt><dd><?php echo $money(isset($deal['discount']) ? $deal['discount'] : 0);?></dd></div>
                    </dl>
                </section>

                <section class="deal-view-section deal-view-wide">
                    <h4><i class="fa fa-building-o"></i> <?php echo __('Property Information');?></h4>
                    <dl class="deal-view-list">
                        <div class="deal-view-row"><dt><?php echo __('Property');?></dt><dd><?php echo $show(isset($property['name']) ? $property['name'] : '');?></dd></div>
                        <div class="deal-view-row"><dt><?php echo __('Office ID');?></dt><dd><?php echo $show(isset($property['uniq_id']) ? $property['uniq_id'] : '');?></dd></div>
                        <div class="deal-view-row"><dt><?php echo __('Agency');?></dt><dd><?php echo $show(isset($agency['name']) ? $agency['name'] : '');?></dd></div>
                        <div class="deal-view-row"><dt><?php echo __('Type / Contract');?></dt><dd><?php echo $show(trim((isset($type['name']) ? $type['name'] : '').' / '.(isset($contract['name']) ? $contract['name'] : ''), ' /'));?></dd></div>
                        <div class="deal-view-row"><dt><?php echo __('Area');?></dt><dd><?php echo $show(trim((isset($property['area']) ? $property['area'] : '').' '.(isset($unit['name']) ? $unit['name'] : '')));?></dd></div>
                        <div class="deal-view-row"><dt><?php echo __('Listed Price');?></dt><dd><?php echo $money(isset($property['price']) ? $property['price'] : 0);?></dd></div>
                        <div class="deal-view-row"><dt><?php echo __('Bedrooms / Baths');?></dt><dd><?php echo (int)(isset($property['bedroom']) ? $property['bedroom'] : 0).' / '.(int)(isset($property['bathroom']) ? $property['bathroom'] : 0);?></dd></div>
                        <div class="deal-view-row"><dt><?php echo __('Address');?></dt><dd><?php echo $show(!empty($property['address']) ? $property['address'] : (isset($property['address_hierarchy_path']) ? $property['address_hierarchy_path'] : ''));?></dd></div>
                    </dl>
                </section>

                <section class="deal-view-section">
                    <h4><i class="fa fa-user"></i> <?php echo __('Client Information');?></h4>
                    <dl class="deal-view-list">
                        <div class="deal-view-row"><dt><?php echo __('Client Name');?></dt><dd><?php echo $show(isset($client['name']) ? $client['name'] : '');?></dd></div>
                        <div class="deal-view-row"><dt><?php echo __('Father Name');?></dt><dd><?php echo $show(isset($client['fathername']) ? $client['fathername'] : '');?></dd></div>
                        <div class="deal-view-row"><dt><?php echo __('Occupation');?></dt><dd><?php echo $show(isset($deal['occupation']) ? $deal['occupation'] : '');?></dd></div>
                        <div class="deal-view-row"><dt><?php echo __('Mobile');?></dt><dd><?php echo $show(isset($client['mobile']) ? $client['mobile'] : '');?></dd></div>
                        <div class="deal-view-row"><dt><?php echo __('Phone');?></dt><dd><?php echo $show(isset($client['phone']) ? $client['phone'] : '');?></dd></div>
                        <div class="deal-view-row"><dt><?php echo __('Email');?></dt><dd><?php echo $show(isset($client['email']) ? $client['email'] : '');?></dd></div>
                        <div class="deal-view-row"><dt><?php echo __('Address');?></dt><dd><?php echo $show(!empty($client['address']) ? $client['address'] : (isset($client['address_hierarchy_path']) ? $client['address_hierarchy_path'] : ''));?></dd></div>
                    </dl>
                </section>

                <section class="deal-view-section">
                    <h4><i class="fa fa-id-card-o"></i> <?php echo __('Nominee Information');?></h4>
                    <dl class="deal-view-list">
                        <div class="deal-view-row"><dt><?php echo __('Nominee');?></dt><dd><?php echo $show(isset($deal['nominee_name']) ? $deal['nominee_name'] : '');?></dd></div>
                        <div class="deal-view-row"><dt><?php echo __('Relation');?></dt><dd><?php echo $show(isset($deal['nominee_relation']) ? $deal['nominee_relation'] : '');?></dd></div>
                        <div class="deal-view-row"><dt><?php echo __('CNIC');?></dt><dd><?php echo $show(isset($deal['nominee_cnic']) ? $deal['nominee_cnic'] : '');?></dd></div>
                        <div class="deal-view-row"><dt><?php echo __('Mobile');?></dt><dd><?php echo $show(isset($deal['nominee_mobile']) ? $deal['nominee_mobile'] : '');?></dd></div>
                    </dl>
                </section>

                <section class="deal-view-section">
                    <h4><i class="fa fa-user-circle-o"></i> <?php echo __('Assigned Agent');?></h4>
                    <dl class="deal-view-list">
                        <div class="deal-view-row"><dt><?php echo __('Agent Name');?></dt><dd><?php echo $show(isset($agent['name']) ? $agent['name'] : '');?></dd></div>
                        <div class="deal-view-row"><dt><?php echo __('Agency');?></dt><dd><?php echo $show(isset($agency['name']) ? $agency['name'] : '');?></dd></div>
                        <div class="deal-view-row"><dt><?php echo __('Mobile');?></dt><dd><?php echo $show(isset($agent['mobile']) ? $agent['mobile'] : '');?></dd></div>
                        <div class="deal-view-row"><dt><?php echo __('Phone');?></dt><dd><?php echo $show(isset($agent['phone']) ? $agent['phone'] : '');?></dd></div>
                        <div class="deal-view-row"><dt><?php echo __('Email');?></dt><dd><?php echo $show(isset($agent['email']) ? $agent['email'] : '');?></dd></div>
                        <div class="deal-view-row"><dt><?php echo __('Address');?></dt><dd><?php echo $show(isset($agent['address']) ? $agent['address'] : '');?></dd></div>
                    </dl>
                </section>

                <section class="deal-view-section deal-view-wide">
                    <h4><i class="fa fa-info-circle"></i> <?php echo __('Additional Information');?></h4>
                    <dl class="deal-view-list">
                        <div class="deal-view-row"><dt><?php echo __('Remarks');?></dt><dd><?php echo $show(isset($deal['remarks']) ? $deal['remarks'] : '');?></dd></div>
                        <div class="deal-view-row"><dt><?php echo __('Status Reason');?></dt><dd><?php echo $show(isset($deal['deal_status_reason']) ? $deal['deal_status_reason'] : '');?></dd></div>
                        <div class="deal-view-row"><dt><?php echo __('Created');?></dt><dd><?php echo $showDate(isset($deal['created']) ? $deal['created'] : '');?></dd></div>
                        <div class="deal-view-row"><dt><?php echo __('Last Updated');?></dt><dd><?php echo $showDate(isset($deal['modified']) ? $deal['modified'] : '');?></dd></div>
                    </dl>
                </section>
            </div>
        </div>
    </div>
</div>
