<style type="text/css">
.wv-overdue-page{background:#f4f7fb;margin:-10px -5px 0;padding:14px;}
.wv-overdue-page *{box-sizing:border-box;}
.wv-overdue-hero{background:linear-gradient(135deg,#07152a 0%,#1e293b 72%,#dc2626 165%);border-radius:10px;box-shadow:0 14px 30px rgba(15,23,42,.13);color:#fff;margin-bottom:14px;padding:18px;position:relative;}
.wv-overdue-hero-inner{align-items:center;display:flex;gap:18px;justify-content:space-between;}
.wv-overdue-kicker{color:#fca5a5;font-size:11px;font-weight:900;letter-spacing:0;margin-bottom:4px;text-transform:uppercase;}
.wv-overdue-hero h1{color:#fff!important;font-size:26px;font-weight:900;line-height:1.15;margin:0 0 5px;}
.wv-overdue-hero p{color:#f1f5f9!important;font-size:12px;font-weight:700;line-height:1.45;margin:0;}
.wv-overdue-hero-actions{display:flex;flex-wrap:wrap;gap:8px;justify-content:flex-end;}
.wv-overdue-hero-actions .btn{align-items:center;border:0;border-radius:10px;display:flex;font-size:11px;font-weight:900;gap:6px;padding:9px 14px;text-transform:uppercase;color:#fff!important;box-shadow:0 2px 6px rgba(0,0,0,.15);text-decoration:none!important;}
.wv-overdue-hero-actions .btn *{color:#fff!important;}
.wv-overdue-hero-actions .btn-deal{background:linear-gradient(135deg,#0284c7,#0369a1)!important;}
.wv-overdue-hero-actions .btn-pay{background:linear-gradient(135deg,#10b981,#059669)!important;}

.wv-overdue-stats{display:grid;gap:10px;grid-template-columns:repeat(3,minmax(0,1fr));margin-bottom:14px;}
.wv-overdue-stat{background:#fff;border:1px solid #dfe9f5;border-radius:10px;box-shadow:0 6px 16px rgba(15,23,42,.05);padding:12px 14px;position:relative;overflow:hidden;}
.wv-overdue-stat:before{background:#dc2626;border-radius:999px;content:"";height:44px;opacity:.08;position:absolute;right:-13px;top:-13px;width:44px;}
.wv-overdue-stat span{color:#64748b;display:block;font-size:10px;font-weight:900;letter-spacing:0;margin-bottom:4px;text-transform:uppercase;}
.wv-overdue-stat strong{color:#07152a;display:block;font-size:20px;font-weight:900;line-height:1.15;}
.wv-overdue-stat strong.text-danger{color:#dc2626!important;}
.wv-overdue-stat small{color:#64748b;display:block;font-size:11px;font-weight:700;margin-top:4px;}

.wv-overdue-card{background:#fff;border:1px solid #dfe9f5;border-radius:10px;box-shadow:0 8px 20px rgba(15,23,42,.06);overflow:hidden;}
.wv-overdue-card-header{align-items:center;background:#07152a;color:#fff;display:flex;justify-content:space-between;padding:12px 16px;}
.wv-overdue-card-header h3{color:#fff!important;font-size:14px;font-weight:900;letter-spacing:0;margin:0;text-transform:uppercase;}
.wv-overdue-card-header span{background:#dc2626;border-radius:6px;color:#fff;font-size:11px;font-weight:900;padding:4px 8px;}

.wv-table-wrap{overflow-x:auto;}
.wv-table-wrap .table{margin-bottom:0;width:100%;}
.wv-table-wrap .table thead th{background:#f8fafc;border-bottom:2px solid #e2e8f0;color:#475569;font-size:11px;font-weight:900;padding:11px 12px;text-transform:uppercase;white-space:nowrap;}
.wv-table-wrap .table tbody td{border-top:1px solid #edf2f7;color:#1e293b;font-size:12px;font-weight:700;padding:11px 12px;vertical-align:middle;}
.wv-table-wrap .table tbody tr:hover{background:#fef2f2;}
.wv-table-wrap .table tfoot td{background:#f8fafc;border-top:2px solid #cbd5e1;color:#07152a;font-size:13px;font-weight:900;padding:12px;}

.wv-overdue-badge{background:linear-gradient(135deg,#dc2626,#b91c1c)!important;border-radius:6px;box-shadow:0 2px 5px rgba(220,38,38,.35);color:#ffffff!important;display:inline-flex;align-items:center;gap:4px;font-size:10px;font-weight:900;letter-spacing:0.3px;padding:5px 9px;text-transform:uppercase;white-space:nowrap;}
.wv-overdue-badge i,.wv-overdue-badge span{color:#ffffff!important;}

.wv-balance-cell{display:flex;align-items:center;justify-content:flex-end;gap:8px;flex-wrap:wrap;}
.wv-balance-amount{color:#dc2626;font-size:13px;font-weight:900;}

.wv-empty-overdue{background:#fff;border:1px dashed #cbd5e1;border-radius:10px;color:#059669;font-size:14px;font-weight:900;padding:32px 20px;text-align:center;}
.wv-empty-overdue i{display:block;font-size:36px;margin-bottom:10px;color:#10b981;}

@media(max-width:767px){
    .wv-overdue-hero{padding:14px;}
    .wv-overdue-hero-inner{display:block;}
    .wv-overdue-hero-actions{justify-content:flex-start;margin-top:12px;}
    .wv-overdue-hero-actions .btn{width:100%;}
    .wv-overdue-stats{grid-template-columns:1fr;}
}
</style>

<div class="wv-overdue-page">
    <?php echo $this->Session->flash();?>

    <?php 
    $overdueCount = count($deal);
    $totalFullPayment = 0;
    $maxDelayDays = 0;
    $nowTs = time();

    foreach($deal as $item) {
        $amt = isset($item['MyInvpastdue']['amount']) ? (float)$item['MyInvpastdue']['amount'] : 0;
        $totalFullPayment += $amt;
        $dDate = !empty($item['MyInvpastdue']['date']) ? $item['MyInvpastdue']['date'] : '';
        if(!empty($dDate)) {
            $dTs = strtotime($dDate);
            if($nowTs > $dTs) {
                $del = max(1, (int)floor(($nowTs - $dTs) / 86400));
                if($del > $maxDelayDays) {
                    $maxDelayDays = $del;
                }
            }
        }
    }
    ?>

    <!-- Hero Header -->
    <div class="wv-overdue-hero">
        <div class="wv-overdue-hero-inner">
            <div>
                <div class="wv-overdue-kicker"><?php echo __('Client Billing Center');?></div>
                <h1><?php echo __('Overdue Installments');?></h1>
                <p><?php echo __('Track pending past-due installment balances, overdue duration, and settlement details.');?></p>
            </div>
            <div class="wv-overdue-hero-actions">
                <?php echo $this->Html->link('<i class="fa fa-home"></i> '.__('My Deals'),array('plugin'=>false,'admin'=>false,'controller'=>'MyDeals','action'=>'index'),array('class'=>'btn btn-deal','escape'=>false));?>
                <?php echo $this->Html->link('<i class="fa fa-list"></i> '.__('Payment Ledger'),array('plugin'=>false,'admin'=>false,'controller'=>'MyPayments','action'=>'index'),array('class'=>'btn btn-pay','escape'=>false));?>
            </div>
        </div>
    </div>

    <!-- Statistics KPIs -->
    <div class="wv-overdue-stats">
        <div class="wv-overdue-stat">
            <span><?php echo __('Overdue Installments');?></span>
            <strong class="text-danger"><?php echo (int)$overdueCount;?></strong>
            <small><?php echo __('Pending unpaid installments');?></small>
        </div>
        <div class="wv-overdue-stat">
            <span><?php echo __('Total Overdue Balance');?></span>
            <strong class="text-danger"><?php echo $currency.$this->Number->format($totalFullPayment);?></strong>
            <small><?php echo __('Cumulative balance due');?></small>
        </div>
        <div class="wv-overdue-stat">
            <span><?php echo __('Maximum Delay');?></span>
            <strong><?php echo $maxDelayDays > 0 ? $maxDelayDays.' '.__('Days') : __('Current');?></strong>
            <small><?php echo __('Longest overdue duration');?></small>
        </div>
    </div>

    <!-- Table Card -->
    <div class="wv-overdue-card">
        <div class="wv-overdue-card-header">
            <h3><i class="fa fa-exclamation-triangle" style="color:#f87171;margin-right:6px;"></i> <?php echo __('Past-Due Payment Schedule');?></h3>
            <span><?php echo (int)$overdueCount;?> <?php echo __('Records Found');?></span>
        </div>

        <?php if(!empty($deal)){ ?>
            <div class="wv-table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width:40px;text-align:center;"><?php echo __('#');?></th>
                            <th><?php echo __('Property');?></th>
                            <th><?php echo __('Flat/Plot');?></th>
                            <th><?php echo __('Invoice No.');?></th>
                            <th><?php echo __('Installment');?></th>
                            <th><?php echo __('Due Date');?></th>
                            <th><?php echo __('Days Past Due');?></th>
                            <th style="text-align:right;"><?php echo __('Balance & Status');?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $serialNo = 0;
                        foreach ($deal as $post):
                            $serialNo++;
                            $fullPayment = isset($post['MyInvpastdue']['amount']) ? (float)$post['MyInvpastdue']['amount'] : 0;
                            $dueDate = !empty($post['MyInvpastdue']['date']) ? $post['MyInvpastdue']['date'] : '';
                            $daysPast = 0;
                            if(!empty($dueDate)) {
                                $dTs = strtotime($dueDate);
                                if($nowTs > $dTs) {
                                    $daysPast = max(1, (int)floor(($nowTs - $dTs) / 86400));
                                }
                            }
                            $propName = !empty($post['Property']['name']) ? $post['Property']['name'] : '-';
                            $flatName = !empty($post['PropertiesFlat']['name']) ? $post['PropertiesFlat']['name'] : (!empty($post['Property']['flat_no']) ? $post['Property']['flat_no'] : '-');
                            $invoiceNo = !empty($post['Deal']['invoice_no']) ? $post['Deal']['invoice_no'] : '-';
                            $instName = !empty($post['MyInvpastdue']['name']) ? $post['MyInvpastdue']['name'] : __('Installment');
                        ?>
                        <tr>
                            <td style="text-align:center;color:#64748b;font-weight:900;"><?php echo $serialNo;?></td>
                            <td><strong style="color:#0f172a;"><?php echo h($propName);?></strong></td>
                            <td><span class="badge" style="background:#f1f5f9;color:#334155;border:1px solid #cbd5e1;font-weight:800;"><?php echo h($flatName);?></span></td>
                            <td><span style="color:#0284c7;font-weight:900;"><?php echo h($invoiceNo);?></span></td>
                            <td><strong style="color:#1e293b;"><?php echo h($instName);?></strong></td>
                            <td><i class="fa fa-calendar" style="color:#94a3b8;margin-right:4px;"></i> <?php echo $this->Time->format($sysDay.$dateSep.$sysMonth.$dateSep.$sysYear, h($dueDate));?></td>
                            <td>
                                <?php if($daysPast > 0){ ?>
                                    <span style="color:#dc2626;font-weight:900;"><i class="fa fa-clock-o"></i> <?php echo $daysPast;?> <?php echo __('days ago');?></span>
                                <?php } else { ?>
                                    <span style="color:#f59e0b;font-weight:900;"><?php echo __('Today');?></span>
                                <?php } ?>
                            </td>
                            <td>
                                <div class="wv-balance-cell">
                                    <strong class="wv-balance-amount"><?php echo $currency.$this->Number->format($fullPayment);?></strong>
                                    <span class="wv-overdue-badge">
                                        <i class="fa fa-exclamation-triangle"></i> <?php echo __('OVERDUE');?><?php if($daysPast > 0){ echo ' ('.$daysPast.'d)'; } ?>
                                    </span>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; unset($post); ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="7" style="text-align:right;text-transform:uppercase;">
                                <strong><?php echo __('Total Overdue Balance');?>:</strong>
                            </td>
                            <td style="text-align:right;">
                                <strong style="color:#dc2626;font-size:15px;"><?php echo $currency.$this->Number->format($totalFullPayment);?></strong>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        <?php } else { ?>
            <div class="wv-empty-overdue">
                <i class="fa fa-check-circle"></i>
                <h4 style="color:#059669;font-weight:900;margin-bottom:6px;"><?php echo __('No Overdue Installments');?></h4>
                <p style="color:#64748b;font-size:13px;margin:0;"><?php echo __('All scheduled installment payments are completely up to date. Thank you!');?></p>
            </div>
        <?php } ?>
    </div>
</div>
