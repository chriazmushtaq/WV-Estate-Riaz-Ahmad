<?php
$dealId = !empty($post['MyDeal']['id']) ? (int)$post['MyDeal']['id'] : 0;
$flatPlot = !empty($post['Property']['name']) ? $post['Property']['name'] : (!empty($post['PropertiesFlat']['name']) ? $post['PropertiesFlat']['name'] : (!empty($post['MyDeal']['properties_flat_id']) ? $post['MyDeal']['properties_flat_id'] : '-'));
$areaValue = !empty($post['PropertiesFlat']['area']) ? $post['PropertiesFlat']['area'] : (!empty($post['Property']['area']) ? $post['Property']['area'] : '-');
$amountValue = !empty($post['PropertiesFlat']['price']) ? $post['PropertiesFlat']['price'] : (!empty($post['Property']['price']) ? $post['Property']['price'] : 0);
$typeLabel = !empty($post['Property']['type']) ? $post['Property']['type'] : '-';
$contractTypeLabel = !empty($contractTypeName) ? $contractTypeName : '-';
$unitLabel = !empty($post['Unit']['name']) ? ' '.$post['Unit']['name'] : '';
$propertyName = !empty($post['Property']['name']) ? $post['Property']['name'] : __('Property');
$invoiceNo = !empty($post['MyDeal']['invoice_no']) ? $post['MyDeal']['invoice_no'] : '-';
$discountAmount = isset($post['MyDeal']['discount']) ? (float)$post['MyDeal']['discount'] : 0;
$totalAmount = isset($post['MyDeal']['total_amount']) ? (float)$post['MyDeal']['total_amount'] : 0;
$dealRemarks = isset($post['MyDeal']['remarks']) ? trim((string)$post['MyDeal']['remarks']) : '';
$ledgerUrl = $this->Session->check('User') ? array('plugin'=>'admin','controller'=>'deals_payments','action'=>'index',$dealId,'?'=>array('client_preview'=>1)) : array('plugin'=>false,'admin'=>false,'controller'=>'MyPayments','action'=>'index',$dealId);
$refundRequestBlock = isset($refundRequestBlock) && is_array($refundRequestBlock) ? $refundRequestBlock : array();
$refundRequestStatus = !empty($refundRequestBlock['status']) ? $refundRequestBlock['status'] : __('In Process');
?>
<style type="text/css">
.wv-deal-modal{background:#f4f7fb;border-radius:14px;overflow:hidden;}
.wv-deal-modal-head{align-items:flex-start;background:linear-gradient(135deg,#07152a 0%,#102746 72%,#ff0a46 170%);color:#fff;display:flex;gap:12px;justify-content:space-between;padding:20px 22px;position:relative;}
.wv-deal-modal-title{margin:0;}
.wv-deal-modal-title small{color:#7cf9ff;display:block;font-size:11px;font-weight:900;letter-spacing:.7px;margin-bottom:5px;text-transform:uppercase;}
.wv-deal-modal-title strong{color:#fff;display:block;font-size:22px;font-weight:900;line-height:1.2;}
.wv-deal-modal-title span{color:#dce8f7;display:block;font-size:12px;font-weight:800;margin-top:6px;}
.wv-deal-modal-close{background:rgba(255,255,255,.12)!important;border:1px solid rgba(255,255,255,.25)!important;border-radius:50%;color:#fff!important;font-size:24px!important;height:38px;line-height:34px;opacity:1!important;text-align:center;width:38px;}
.wv-deal-modal-body{padding:18px;}
.wv-deal-modal-actions{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:15px;}
.wv-deal-modal-actions .btn{border:0;border-radius:999px;font-size:12px;font-weight:900;padding:9px 13px;text-transform:uppercase;}
.wv-deal-modal-actions .btn-success{background:#047857!important;color:#fff!important;}
.wv-deal-modal-actions .btn-info{background:#7cf9ff!important;color:#07152a!important;}
.wv-deal-modal-actions .btn-primary{background:#ff0a46!important;color:#fff!important;}
.wv-deal-modal-actions .btn-warning{background:#f59e0b!important;color:#07152a!important;}
.wv-deal-modal-actions .btn-default{background:#f1f5f9!important;color:#07152a!important;}
.wv-deal-modal-actions .btn.disabled{cursor:not-allowed;opacity:.72;pointer-events:none;}
.wv-deal-modal-money{display:grid;gap:10px;grid-template-columns:repeat(3,minmax(0,1fr));margin-bottom:15px;}
.wv-deal-modal-money-card{background:#fff;border:1px solid #dfe9f5;border-radius:12px;box-shadow:0 8px 18px rgba(15,23,42,.06);padding:13px;}
.wv-deal-modal-money-card span{color:#64748b;display:block;font-size:10px;font-weight:900;letter-spacing:.35px;margin-bottom:5px;text-transform:uppercase;}
.wv-deal-modal-money-card strong{color:#07152a;display:block;font-size:17px;font-weight:900;line-height:1.2;}
.wv-deal-modal-money-card.discount strong{color:#c2410c;}
.wv-deal-modal-money-card.total strong{color:#047857;}
.wv-deal-info-grid{background:#fff;border:1px solid #dfe9f5;border-radius:12px;box-shadow:0 8px 18px rgba(15,23,42,.06);display:grid;grid-template-columns:repeat(2,minmax(0,1fr));overflow:hidden;}
.wv-deal-info-item{border-bottom:1px solid #edf2f7;padding:13px 15px;}
.wv-deal-info-item:nth-child(odd){border-right:1px solid #edf2f7;}
.wv-deal-info-item span{color:#64748b;display:block;font-size:10px;font-weight:900;letter-spacing:.35px;text-transform:uppercase;}
.wv-deal-info-item strong{color:#1f2937;display:block;font-size:13px;font-weight:900;margin-top:5px;word-break:break-word;}
.wv-deal-remarks{background:#fff;border:1px solid #dfe9f5;border-radius:12px;box-shadow:0 8px 18px rgba(15,23,42,.06);margin-top:14px;padding:14px 15px;}
.wv-deal-remarks span{color:#64748b;display:block;font-size:10px;font-weight:900;letter-spacing:.35px;text-transform:uppercase;}
.wv-deal-remarks p{color:#334155;font-size:13px;font-weight:800;line-height:1.5;margin:7px 0 0;}
@media(max-width:767px){.wv-deal-modal-head{padding:17px;}.wv-deal-modal-body{padding:14px;}.wv-deal-modal-actions .btn{width:100%;}.wv-deal-modal-money,.wv-deal-info-grid{grid-template-columns:1fr;}.wv-deal-info-item:nth-child(odd){border-right:0;}}
</style>

<div class="wv-deal-modal">
    <?php echo $this->Session->flash();?>
    <div class="wv-deal-modal-head">
        <h4 class="wv-deal-modal-title">
            <small><?php echo __('Deal Details');?></small>
            <strong><?php echo h($propertyName);?></strong>
            <span><?php echo __('Invoice');?>: <?php echo h($invoiceNo);?></span>
        </h4>
        <button type="button" class="close wv-deal-modal-close" data-dismiss="modal" aria-hidden="true">&times;</button>
    </div>
    <div class="wv-deal-modal-body">
        <div class="wv-deal-modal-actions">
            <?php echo $this->Html->link('<i class="fa fa-list"></i> '.__('Open Payment Ledger'),$ledgerUrl,array('class'=>'btn btn-success','escape'=>false));?>
            <?php echo $this->Html->link('<i class="fa fa-print"></i> '.__('Print Invoice'),array('plugin'=>false,'admin'=>false,'controller'=>'MyDeals','action'=>'printinvoice',$dealId),array('class'=>'btn btn-info','target'=>'_blank','escape'=>false));?>
            <?php echo $this->Html->link('<i class="fa fa-file-text-o"></i> '.__('Letters'),array('plugin'=>false,'admin'=>false,'controller'=>'MyDeals','action'=>'letters',$dealId),array('class'=>'btn btn-primary','escape'=>false));?>
            <?php if(!empty($post['MyDeal']['client_refund_allowed']) && empty($refundRequestBlock)){ echo $this->Html->link('<i class="fa fa-undo"></i> '.__('Apply Refund'),array('plugin'=>false,'admin'=>false,'controller'=>'MyRefundrequests','action'=>'apply',$dealId),array('class'=>'btn btn-warning','escape'=>false)); } ?>
            <?php if(!empty($post['MyDeal']['client_refund_allowed']) && !empty($refundRequestBlock)){ ?><span class="btn btn-default disabled" title="<?php echo h(__('Current refund request status').': '.$refundRequestStatus);?>"><i class="fa fa-clock-o"></i> <?php echo __('Refund In Process');?></span><?php } ?>
        </div>

        <div class="wv-deal-modal-money">
            <div class="wv-deal-modal-money-card">
                <span><?php echo __('Property Price');?></span>
                <strong><?php echo $currency.$this->Number->format($amountValue);?></strong>
            </div>
            <div class="wv-deal-modal-money-card discount">
                <span><?php echo __('Discount');?></span>
                <strong><?php echo $currency.$this->Number->format($discountAmount);?></strong>
            </div>
            <div class="wv-deal-modal-money-card total">
                <span><?php echo __('Total Amount');?></span>
                <strong><?php echo $currency.$this->Number->format($totalAmount);?></strong>
            </div>
        </div>

        <div class="wv-deal-info-grid">
            <div class="wv-deal-info-item">
                <span><?php echo __('Client Name');?></span>
                <strong><?php echo h(!empty($post['Client']['name']) ? $post['Client']['name'] : '-');?></strong>
            </div>
            <div class="wv-deal-info-item">
                <span><?php echo __('Property Name');?></span>
                <strong><?php echo h($propertyName);?></strong>
            </div>
            <div class="wv-deal-info-item">
                <span><?php echo __('Type');?></span>
                <strong><?php echo h($typeLabel);?></strong>
            </div>
            <div class="wv-deal-info-item">
                <span><?php echo __('Contract Type');?></span>
                <strong><?php echo h($contractTypeLabel);?></strong>
            </div>
            <div class="wv-deal-info-item">
                <span><?php echo __('Flat/Plot');?></span>
                <strong><?php echo h($flatPlot);?></strong>
            </div>
            <div class="wv-deal-info-item">
                <span><?php echo __('Payment Plan');?></span>
                <strong><?php echo h(!empty($post['Plan']['name']) ? $post['Plan']['name'] : '-');?></strong>
            </div>
            <div class="wv-deal-info-item">
                <span><?php echo __('Invoice Date');?></span>
                <strong><?php echo $this->Time->format($sysDay.$dateSep.$sysMonth.$dateSep.$sysYear,h($post['MyDeal']['date']));?></strong>
            </div>
            <div class="wv-deal-info-item">
                <span><?php echo __('Area');?></span>
                <strong><?php echo h($areaValue.$unitLabel);?></strong>
            </div>
        </div>

        <div class="wv-deal-remarks">
            <span><?php echo __('Comments/Remarks');?></span>
            <p><?php echo $dealRemarks !== '' ? h($dealRemarks) : __('No remarks added.');?></p>
        </div>
    </div>
</div>
