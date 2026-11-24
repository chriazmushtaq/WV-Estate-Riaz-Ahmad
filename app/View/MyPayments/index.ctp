<style type="text/css">
.wv-mypayments-page{background:#f4f7fb;margin:-10px -5px 0;padding:14px;}
.wv-mypayments-page *{box-sizing:border-box;}
.wv-mypayments-hero{background:linear-gradient(135deg,#07152a 0%,#1e293b 72%,#0284c7 165%);border-radius:10px;box-shadow:0 14px 30px rgba(15,23,42,.13);color:#fff;margin-bottom:14px;padding:18px;position:relative;}
.wv-mypayments-hero-inner{align-items:center;display:flex;gap:18px;justify-content:space-between;}
.wv-mypayments-kicker{color:#7dd3fc;font-size:11px;font-weight:900;letter-spacing:0;margin-bottom:4px;text-transform:uppercase;}
.wv-mypayments-hero h1{color:#fff!important;font-size:26px;font-weight:900;line-height:1.15;margin:0 0 5px;}
.wv-mypayments-hero p{color:#e0f2fe!important;font-size:12px;font-weight:700;line-height:1.45;margin:0;}
.wv-mypayments-hero-actions{display:flex;flex-wrap:wrap;gap:8px;justify-content:flex-end;}
.wv-mypayments-hero-actions .btn{align-items:center;border:0;border-radius:10px;display:flex;font-size:11px;font-weight:900;gap:6px;padding:9px 14px;text-transform:uppercase;color:#fff!important;box-shadow:0 2px 6px rgba(0,0,0,.15);text-decoration:none!important;}
.wv-mypayments-hero-actions .btn *{color:#fff!important;}
.wv-mypayments-hero-actions .btn-deals{background:linear-gradient(135deg,#ff0a46,#dc2626)!important;}
.wv-mypayments-hero-actions .btn-overdue{background:linear-gradient(135deg,#f59e0b,#d97706)!important;}

.wv-mypayments-stats{display:grid;gap:10px;grid-template-columns:repeat(5,minmax(0,1fr));margin-bottom:14px;}
.wv-mypayments-stat{background:#fff;border:1px solid #dfe9f5;border-radius:10px;box-shadow:0 6px 16px rgba(15,23,42,.05);padding:12px 14px;position:relative;overflow:hidden;}
.wv-mypayments-stat:before{background:#0284c7;border-radius:999px;content:"";height:44px;opacity:.08;position:absolute;right:-13px;top:-13px;width:44px;}
.wv-mypayments-stat.stat-early:before{background:#0ea5e9;}
.wv-mypayments-stat.stat-ontime:before{background:#10b981;}
.wv-mypayments-stat.stat-late:before{background:#ef4444;}
.wv-mypayments-stat span{color:#64748b;display:block;font-size:10px;font-weight:900;letter-spacing:0;margin-bottom:4px;text-transform:uppercase;}
.wv-mypayments-stat strong{color:#07152a;display:block;font-size:19px;font-weight:900;line-height:1.15;}
.wv-mypayments-stat.stat-early strong{color:#0284c7;}
.wv-mypayments-stat.stat-ontime strong{color:#059669;}
.wv-mypayments-stat.stat-late strong{color:#dc2626;}
.wv-mypayments-stat small{color:#64748b;display:block;font-size:11px;font-weight:700;margin-top:4px;}

.my-payment-deal-block{background:#fff;border:1px solid #dfe9f5;border-radius:10px;box-shadow:0 8px 20px rgba(15,23,42,.06);margin-bottom:16px;overflow:hidden;}
.my-payment-deal-head{align-items:center;background:#07152a;color:#fff;display:flex;flex-wrap:wrap;gap:12px;justify-content:space-between;padding:12px 16px;}
.my-payment-deal-title{color:#fff!important;font-size:15px;font-weight:900;margin:0;}
.my-payment-deal-meta{align-items:center;display:flex;flex-wrap:wrap;gap:10px;font-size:11px;font-weight:800;color:#cbd5e1;}
.my-payment-deal-meta strong{color:#fff;}
.my-payment-deal-meta .pill-chip{background:rgba(255,255,255,.12);border-radius:6px;padding:3px 7px;font-size:10px;font-weight:900;text-transform:uppercase;}
.my-payment-deal-meta .pill-early{background:rgba(14,165,233,.28);color:#7dd3fc;}
.my-payment-deal-meta .pill-ontime{background:rgba(16,185,129,.28);color:#a7f3d0;}
.my-payment-deal-meta .pill-late{background:rgba(239,68,68,.28);color:#fca5a5;}

.my-payment-filter-banner{align-items:center;background:#fff7ed;border:1px solid #fed7aa;border-radius:10px;color:#9a3412;display:flex;flex-wrap:wrap;font-size:12px;font-weight:900;gap:10px;justify-content:space-between;margin-bottom:14px;padding:11px 14px;}
.my-payment-filter-banner .btn{border:0;border-radius:10px;font-size:11px;font-weight:900;padding:7px 14px;background:#f59e0b;color:#fff!important;}

.wv-table-wrap{overflow-x:auto;}
.wv-table-wrap .table{margin-bottom:0;width:100%;}
.wv-table-wrap .table thead th{background:#f8fafc;border-bottom:2px solid #e2e8f0;color:#475569;font-size:11px;font-weight:900;padding:11px 12px;text-transform:uppercase;white-space:nowrap;}
.wv-table-wrap .table tbody td{border-top:1px solid #edf2f7;color:#1e293b;font-size:12px;font-weight:700;padding:11px 12px;vertical-align:middle;}
.wv-table-wrap .table tbody tr:hover{background:#f8fafc;}

/* Timing Analysis Badges */
.wv-timing-badge{border-radius:6px;box-shadow:0 2px 5px rgba(0,0,0,.1);color:#ffffff!important;display:inline-flex;align-items:center;gap:4px;font-size:10px;font-weight:900;letter-spacing:0.3px;padding:5px 9px;text-transform:uppercase;white-space:nowrap;}
.wv-timing-badge i,.wv-timing-badge span{color:#ffffff!important;}

.wv-timing-early{background:linear-gradient(135deg,#0284c7,#0369a1)!important;box-shadow:0 2px 6px rgba(2,132,199,.32);}
.wv-timing-ontime{background:linear-gradient(135deg,#10b981,#059669)!important;box-shadow:0 2px 6px rgba(16,185,129,.32);}
.wv-timing-late{background:linear-gradient(135deg,#ef4444,#dc2626)!important;box-shadow:0 2px 6px rgba(239,68,68,.35);}
.wv-timing-advance{background:linear-gradient(135deg,#6366f1,#4f46e5)!important;box-shadow:0 2px 6px rgba(99,102,241,.32);}

.wv-receipt-btn{align-items:center;background:linear-gradient(135deg,#0284c7,#0369a1)!important;border:0;border-radius:8px;box-shadow:0 2px 5px rgba(2,132,199,.25);color:#fff!important;display:inline-flex;font-size:10px;font-weight:900;gap:4px;padding:6px 11px;text-decoration:none!important;text-transform:uppercase;transition:all .2s;}
.wv-receipt-btn *{color:#fff!important;}
.wv-receipt-btn:hover{filter:brightness(1.12);transform:translateY(-1px);color:#fff!important;}

@media(max-width:1199px){.wv-mypayments-stats{grid-template-columns:repeat(3,minmax(0,1fr));}}
@media(max-width:767px){
    .wv-mypayments-hero{padding:14px;}
    .wv-mypayments-hero-inner{display:block;}
    .wv-mypayments-hero-actions{justify-content:flex-start;margin-top:12px;}
    .wv-mypayments-hero-actions .btn{width:100%;}
    .wv-mypayments-stats{grid-template-columns:1fr;}
    .my-payment-deal-head{display:block;}
    .my-payment-deal-meta{margin-top:8px;}
}
</style>

<div class="wv-mypayments-page">
    <?php echo $this->Session->flash();?>

    <!-- Hero Header -->
    <div class="wv-mypayments-hero">
        <div class="wv-mypayments-hero-inner">
            <div>
                <div class="wv-mypayments-kicker"><?php echo __('Client Financial Portal');?></div>
                <h1><?php echo __('My Payment Ledger');?></h1>
                <p><?php echo __('Comprehensive ledger of all confirmed payments, timing analysis, on-time score, and receipt slips.');?></p>
            </div>
            <div class="wv-mypayments-hero-actions">
                <?php echo $this->Html->link('<i class="fa fa-home"></i> '.__('My Deals'),array('plugin'=>false,'admin'=>false,'controller'=>'MyDeals','action'=>'index'),array('class'=>'btn btn-deals','escape'=>false));?>
                <?php echo $this->Html->link('<i class="fa fa-exclamation-triangle"></i> '.__('Overdue Schedule'),array('plugin'=>false,'admin'=>false,'controller'=>'MyInvpastdues','action'=>'index'),array('class'=>'btn btn-overdue','escape'=>false));?>
            </div>
        </div>
    </div>

    <!-- Overall Timing Analytics KPIs -->
    <?php 
    $totPaid = isset($overallStats['total_paid']) ? (float)$overallStats['total_paid'] : 0;
    $recCount = isset($overallStats['receipts_count']) ? (int)$overallStats['receipts_count'] : 0;
    $earlyCount = isset($overallStats['early_count']) ? (int)$overallStats['early_count'] : 0;
    $ontimeCount = isset($overallStats['ontime_count']) ? (int)$overallStats['ontime_count'] : 0;
    $lateCount = isset($overallStats['late_count']) ? (int)$overallStats['late_count'] : 0;
    $advanceCount = isset($overallStats['advance_count']) ? (int)$overallStats['advance_count'] : 0;

    $compliancePercent = 100;
    if(($earlyCount + $ontimeCount + $lateCount) > 0) {
        $compliancePercent = round((($earlyCount + $ontimeCount) / ($earlyCount + $ontimeCount + $lateCount)) * 100);
    }
    ?>
    <div class="wv-mypayments-stats">
        <div class="wv-mypayments-stat">
            <span><?php echo __('Total Paid');?></span>
            <strong><?php echo $currency.$this->Number->format($totPaid);?></strong>
            <small><?php echo (int)$recCount;?> <?php echo __('Verified Receipts');?></small>
        </div>
        <div class="wv-mypayments-stat stat-early">
            <span><?php echo __('Paid Early');?></span>
            <strong><?php echo (int)$earlyCount;?></strong>
            <small><?php echo __('Before due date');?></small>
        </div>
        <div class="wv-mypayments-stat stat-ontime">
            <span><?php echo __('Paid On Time');?></span>
            <strong><?php echo (int)$ontimeCount;?></strong>
            <small><?php echo __('Exactly on due date');?></small>
        </div>
        <div class="wv-mypayments-stat stat-late">
            <span><?php echo __('Paid Late / Overdue');?></span>
            <strong><?php echo (int)$lateCount;?></strong>
            <small><?php echo __('After scheduled due date');?></small>
        </div>
        <div class="wv-mypayments-stat">
            <span><?php echo __('On-Time Score');?></span>
            <strong style="color:<?php echo $compliancePercent >= 80 ? '#059669' : ($compliancePercent >= 50 ? '#f59e0b' : '#dc2626');?>"><?php echo (int)$compliancePercent;?>%</strong>
            <small><?php echo __('Payment discipline rate');?></small>
        </div>
    </div>

    <?php if(!empty($selectedDealId)){ ?>
        <div class="my-payment-filter-banner">
            <span><i class="fa fa-filter"></i> <?php echo __('Filtered Payment Ledger for Deal');?> #<?php echo (int)$selectedDealId;?></span>
            <?php echo $this->Html->link(__('Show All Deals'),array('plugin'=>false,'admin'=>false,'controller'=>'MyPayments','action'=>'index'),array('class'=>'btn'));?>
        </div>
    <?php } ?>

    <?php if(!empty($paymentsByDeal)){ ?>
        <?php foreach($paymentsByDeal as $dealId => $dealGroup): ?>
            <?php
                $deal = isset($dealGroup['Deal']) ? $dealGroup['Deal'] : array();
                $property = isset($dealGroup['Property']) ? $dealGroup['Property'] : array();
                $unit = isset($dealGroup['Unit']) ? $dealGroup['Unit'] : array();
                $rows = isset($dealGroup['rows']) ? $dealGroup['rows'] : array();
                $serial_no = 1;

                $dealArea = !empty($deal['area']) ? $deal['area'] : (!empty($property['area']) ? $property['area'] : '-');
            ?>
            <div class="my-payment-deal-block">
                <div class="my-payment-deal-head">
                    <div>
                        <h3 class="my-payment-deal-title"><?php echo h(!empty($property['name']) ? $property['name'] : __('Deal'));?></h3>
                        <div class="my-payment-deal-meta">
                            <span><?php echo __('Invoice');?>: <strong><?php echo h(!empty($deal['invoice_no']) ? $deal['invoice_no'] : '-');?></strong></span>
                            <span>&bull;</span>
                            <span><?php echo __('Area');?>: <strong><?php echo h($dealArea);?> <?php if(!empty($unit['name'])){ echo h($unit['name']); } ?></strong></span>
                            <span>&bull;</span>
                            <span><?php echo __('Paid');?>: <strong><?php echo $currency.$this->Number->format($dealGroup['total_paid']);?></strong> (<?php echo (int)$dealGroup['receipt_count'];?> <?php echo __('Receipts');?>)</span>
                            <span>&bull;</span>
                            <span><?php echo __('Balance Due');?>: <strong><?php echo $currency.$this->Number->format(isset($dealGroup['due_amount']) ? $dealGroup['due_amount'] : 0);?></strong></span>
                        </div>
                    </div>
                    <div class="my-payment-deal-meta">
                        <?php if(!empty($dealGroup['early_count'])){ ?>
                            <span class="pill-chip pill-early"><i class="fa fa-bolt"></i> <?php echo (int)$dealGroup['early_count'];?> <?php echo __('Early');?></span>
                        <?php } ?>
                        <?php if(!empty($dealGroup['ontime_count'])){ ?>
                            <span class="pill-chip pill-ontime"><i class="fa fa-check-circle"></i> <?php echo (int)$dealGroup['ontime_count'];?> <?php echo __('On Time');?></span>
                        <?php } ?>
                        <?php if(!empty($dealGroup['late_count'])){ ?>
                            <span class="pill-chip pill-late"><i class="fa fa-exclamation-triangle"></i> <?php echo (int)$dealGroup['late_count'];?> <?php echo __('Late');?></span>
                        <?php } ?>
                    </div>
                </div>

                <div class="wv-table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th style="width:40px;text-align:center;"><?php echo __('#');?></th>
                                <th><?php echo __('Receipt No.');?></th>
                                <th><?php echo __('Payment Date');?></th>
                                <th><?php echo __('Installment / Due Date');?></th>
                                <th><?php echo __('Amount');?></th>
                                <th><?php echo __('Payment Method');?></th>
                                <th style="text-align:center;"><?php echo __('Timing Status & Analysis');?></th>
                                <th><?php echo __('Transaction Ref / Remarks');?></th>
                                <th style="text-align:center;"><?php echo __('Action');?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($rows as $post): 
                                $id = $post['MyPayment']['id'];
                                $timing = isset($post['TimingAnalysis']) ? $post['TimingAnalysis'] : array();
                                $status = !empty($timing['status']) ? $timing['status'] : 'advance';
                                $diffDays = isset($timing['diff_days']) ? (int)$timing['diff_days'] : 0;
                                $dueDate = !empty($timing['due_date']) ? $timing['due_date'] : '';
                                $instName = !empty($timing['installment_name']) ? $timing['installment_name'] : '';
                            ?>
                                <tr>
                                    <td style="text-align:center;color:#64748b;font-weight:900;"><?php echo $serial_no++;?></td>
                                    <td><strong style="color:#0284c7;"><?php echo h($post['MyPayment']['receipt_no']);?></strong></td>
                                    <td><i class="fa fa-calendar" style="color:#94a3b8;margin-right:4px;"></i> <?php echo $this->Time->format($sysDay.$dateSep.$sysMonth.$dateSep.$sysYear, h($post['MyPayment']['payment_date']));?></td>
                                    <td>
                                        <?php if(!empty($dueDate)){ ?>
                                            <div><strong style="color:#0f172a;"><?php echo h($instName);?></strong></div>
                                            <small style="color:#64748b;"><i class="fa fa-clock-o"></i> <?php echo $this->Time->format($sysDay.$dateSep.$sysMonth.$dateSep.$sysYear, h($dueDate));?></small>
                                        <?php } else { ?>
                                            <span style="color:#64748b;font-style:italic;"><?php echo __('Direct / Advance');?></span>
                                        <?php } ?>
                                    </td>
                                    <td><strong style="color:#0f172a;font-size:13px;"><?php echo $currency.$this->Number->format($post['MyPayment']['amount']);?></strong></td>
                                    <td><span class="badge" style="background:#f1f5f9;color:#334155;border:1px solid #cbd5e1;font-weight:800;"><?php echo h(!empty($post['Paymenttype']['name']) ? $post['Paymenttype']['name'] : '-');?></span></td>
                                    <td style="text-align:center;">
                                        <?php if($status === 'early'){ ?>
                                            <span class="wv-timing-badge wv-timing-early" title="<?php echo __('Paid').' '.$diffDays.' '.__('days before scheduled due date');?>">
                                                <i class="fa fa-bolt"></i> <?php echo __('Paid Early');?> (<?php echo $diffDays;?>d <?php echo __('before');?>)
                                            </span>
                                        <?php } elseif($status === 'ontime'){ ?>
                                            <span class="wv-timing-badge wv-timing-ontime" title="<?php echo __('Paid on the exact scheduled due date');?>">
                                                <i class="fa fa-check-circle"></i> <?php echo __('Paid On Time');?>
                                            </span>
                                        <?php } elseif($status === 'late'){ ?>
                                            <span class="wv-timing-badge wv-timing-late" title="<?php echo __('Paid').' '.$diffDays.' '.__('days after scheduled due date');?>">
                                                <i class="fa fa-exclamation-triangle"></i> <?php echo __('Paid Late');?> (<?php echo $diffDays;?>d <?php echo __('after due');?>)
                                            </span>
                                        <?php } else { ?>
                                            <span class="wv-timing-badge wv-timing-advance" title="<?php echo __('Advance / Direct payment');?>">
                                                <i class="fa fa-shield"></i> <?php echo __('Advance / Direct');?>
                                            </span>
                                        <?php } ?>
                                    </td>
                                    <td><span style="color:#475569;font-size:11px;"><?php echo h(!empty($post['MyPayment']['remarks']) ? $post['MyPayment']['remarks'] : '-');?></span></td>
                                    <td style="text-align:center;">
                                        <?php echo $this->Html->link('<i class="fa fa-print"></i> '.__('Receipt'),array('plugin'=>false,'admin'=>false,'controller'=>'MyPayments','action'=>'printreceipt',$id),array('class'=>'wv-receipt-btn','target'=>'_blank','escape'=>false,'title'=>__('Print Payment Slip')));?>
                                    </td>
                                </tr>
                            <?php endforeach; unset($post); ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endforeach; ?>
    <?php } else { ?>
        <div class="my-payment-deal-block" style="padding:30px;text-align:center;color:#64748b;">
            <i class="fa fa-file-text-o" style="font-size:36px;color:#cbd5e1;display:block;margin-bottom:10px;"></i>
            <strong><?php echo __('No payment records found.');?></strong>
        </div>
    <?php } ?>
</div>
