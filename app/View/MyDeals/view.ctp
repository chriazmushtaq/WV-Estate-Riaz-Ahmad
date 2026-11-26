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
.wv-deal-modal{background:#f8fafc;border-radius:14px;overflow:hidden;border:1px solid #e2e8f0;touch-action:auto;overflow-x:auto;-webkit-overflow-scrolling:touch;width:100%;}
.wv-deal-modal-head{align-items:flex-start;background:#ffffff;border-bottom:1px solid #e2e8f0;border-top:4px solid #0284c7;color:#0f172a;display:flex;gap:12px;justify-content:space-between;padding:18px 22px;position:relative;}
.wv-deal-modal-title{margin:0;}
.wv-deal-modal-title small{color:#0284c7;display:block;font-size:11px;font-weight:900;letter-spacing:.7px;margin-bottom:4px;text-transform:uppercase;}
.wv-deal-modal-title strong{color:#0f172a;display:block;font-size:22px;font-weight:900;line-height:1.2;}
.wv-deal-modal-title span{color:#64748b;display:block;font-size:12px;font-weight:800;margin-top:4px;}
.wv-deal-modal-close{background:#f1f5f9!important;border:1px solid #e2e8f0!important;border-radius:50%;color:#475569!important;font-size:22px!important;height:36px;line-height:32px;opacity:1!important;text-align:center;width:36px;transition:all 0.15s;}
.wv-deal-modal-close:hover{background:#e2e8f0!important;color:#0f172a!important;}
.wv-deal-modal-body{padding:18px;}
.wv-deal-modal-actions{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:15px;overflow-x:auto;-webkit-overflow-scrolling:touch;touch-action:pan-x pan-y;padding-bottom:4px;}
.wv-deal-modal-actions .btn{border:0;border-radius:999px;font-size:12px;font-weight:900;padding:9px 14px;text-transform:uppercase;white-space:nowrap;display:inline-flex;align-items:center;gap:6px;}
.wv-deal-modal-actions .btn-success{background:#059669!important;color:#fff!important;}
.wv-deal-modal-actions .btn-info{background:#0284c7!important;color:#fff!important;}
.wv-deal-modal-actions .btn-primary{background:#4f46e5!important;color:#fff!important;}
.wv-deal-modal-actions .btn-warning{background:#f59e0b!important;color:#0f172a!important;}
.wv-deal-modal-actions .btn-default{background:#f1f5f9!important;color:#334155!important;border:1px solid #e2e8f0!important;}
.wv-deal-modal-actions .btn.disabled{cursor:not-allowed;opacity:.72;pointer-events:none;}
.wv-deal-modal-money{display:grid;gap:10px;grid-template-columns:repeat(3,minmax(0,1fr));margin-bottom:15px;}
.wv-deal-modal-money-card{background:#fff;border:1px solid #e2e8f0;border-radius:12px;box-shadow:0 4px 12px rgba(15,23,42,.04);padding:13px;}
.wv-deal-modal-money-card span{color:#64748b;display:block;font-size:10px;font-weight:900;letter-spacing:.35px;margin-bottom:5px;text-transform:uppercase;}
.wv-deal-modal-money-card strong{color:#0f172a;display:block;font-size:17px;font-weight:900;line-height:1.2;}
.wv-deal-modal-money-card.discount strong{color:#ea580c;}
.wv-deal-modal-money-card.total strong{color:#059669;}
.wv-deal-info-grid{background:#fff;border:1px solid #e2e8f0;border-radius:12px;box-shadow:0 4px 12px rgba(15,23,42,.04);display:grid;grid-template-columns:repeat(2,minmax(0,1fr));overflow:hidden;}
.wv-deal-info-item{border-bottom:1px solid #f1f5f9;padding:13px 15px;}
.wv-deal-info-item:nth-child(odd){border-right:1px solid #f1f5f9;}
.wv-deal-info-item span{color:#64748b;display:block;font-size:10px;font-weight:900;letter-spacing:.35px;text-transform:uppercase;}
.wv-deal-info-item strong{color:#0f172a;display:block;font-size:13px;font-weight:900;margin-top:4px;word-break:break-word;}
.wv-deal-remarks{background:#fff;border:1px solid #e2e8f0;border-radius:12px;box-shadow:0 4px 12px rgba(15,23,42,.04);margin-top:14px;padding:14px 15px;}
.wv-deal-remarks span{color:#64748b;display:block;font-size:10px;font-weight:900;letter-spacing:.35px;text-transform:uppercase;}
.wv-deal-remarks p{color:#334155;font-size:13px;font-weight:700;line-height:1.5;margin:7px 0 0;}
@media(max-width:767px){
  .wv-deal-modal-head{padding:14px 16px;}
  .wv-deal-modal-body{padding:14px;}
  .wv-deal-modal-money,.wv-deal-info-grid{grid-template-columns:1fr;}
  .wv-deal-info-item:nth-child(odd){border-right:0;}
}
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
