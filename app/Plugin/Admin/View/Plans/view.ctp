<?php
$p = $plan['Plan'];
$difference = round($planTotal-$scheduleTotal,2);
$isBalanced = !empty($scheduleRows) && abs($difference) <= 0.01;
?>
<div class="page-header-content wv-page-header">
    <div class="page-title wv-page-title-box">
        <h4 class="wv-page-title"><i class="fa fa-calendar-check-o text-primary"></i> <?php echo h($p['name']);?></h4>
        <small class="wv-page-subtitle"><?php echo __('Installment breakdown, payment milestones, and schedule balance.');?></small>
    </div>
    <div class="heading-elements wv-page-header-actions">
        <?php echo $this->Html->link('<i class="fa fa-arrow-left"></i> '.__('Back to Plans'),array('action'=>'index'),array('escape'=>false,'class'=>'btn btn-default'));?>
        <?php echo $this->Html->link('<i class="fa fa-edit"></i> '.__('Edit Plan'),array('action'=>'edit',(int)$p['id']),array('escape'=>false,'class'=>'btn btn-primary'));?>
    </div>
</div>

<div class="panel panel-flat">
    <div class="panel-body">
        <?php echo $this->Session->flash();?>
        <div class="row" style="margin-bottom: 20px;">
            <div class="col-xs-12 col-sm-6 col-md-3" style="margin-bottom: 12px;">
                <div class="panel panel-body" style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; margin:0; padding:16px;">
                    <small class="text-muted" style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px;"><?php echo __('Total Plan Value');?></small>
                    <div style="font-size:22px; font-weight:800; color:#0f172a; margin-top:4px;">Rs <?php echo CakeNumber::format($planTotal);?></div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-6 col-md-3" style="margin-bottom: 12px;">
                <div class="panel panel-body" style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; margin:0; padding:16px;">
                    <small class="text-muted" style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px;"><?php echo __('Scheduled Payments');?></small>
                    <div style="font-size:22px; font-weight:800; color:#0f172a; margin-top:4px;"><?php echo (int)$paymentCount;?> <?php echo __('Stages');?></div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-6 col-md-3" style="margin-bottom: 12px;">
                <div class="panel panel-body" style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; margin:0; padding:16px;">
                    <small class="text-muted" style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px;"><?php echo __('Plan Status');?></small>
                    <div style="margin-top:6px;">
                        <span class="badge-status label label-<?php if($p['status']=="Active")echo"success";else echo"danger";?>">
                            <i class="fa fa-circle" style="font-size:7px;"></i> <?php echo h($p['status']);?>
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-6 col-md-3" style="margin-bottom: 12px;">
                <div class="panel panel-body" style="background:<?php echo $isBalanced?'#f0fdf4':'#fff7ed';?>; border:1px solid <?php echo $isBalanced?'#bbf7d0':'#fed7aa';?>; border-radius:10px; margin:0; padding:16px;">
                    <small class="<?php echo $isBalanced?'text-success':'text-warning';?>" style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px;"><?php echo __('Schedule Difference');?></small>
                    <div style="font-size:22px; font-weight:800; color:<?php echo $isBalanced?'#15803d':'#c2410c';?>; margin-top:4px;">Rs <?php echo CakeNumber::format($difference);?></div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <?php if(empty($scheduleRows)):?>
                <div class="text-center text-muted" style="padding: 40px;">
                    <i class="fa fa-info-circle fa-2x" style="display:block; margin-bottom: 10px; color: #94a3b8;"></i>
                    <?php echo __('This is a legacy payment plan and has no detailed schedule template.');?>
                </div>
            <?php else:?>
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th style="width: 50px;" class="text-center">#</th>
                        <th><?php echo __('Payment Stage');?></th>
                        <th><?php echo __('Installment Title');?></th>
                        <th class="text-center"><?php echo __('Amount Mode');?></th>
                        <th class="text-right"><?php echo __('Configured Value');?></th>
                        <th class="text-center"><?php echo __('Count');?></th>
                        <th class="text-center"><?php echo __('Interval (Months)');?></th>
                        <th class="text-right"><?php echo __('Per Installment');?></th>
                        <th class="text-right"><?php echo __('Stage Subtotal');?></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach($scheduleRows as $index=>$row):?>
                    <tr>
                        <td class="text-center"><?php echo $index+1;?></td>
                        <td><span class="label label-info"><?php echo h(Inflector::humanize($row['payment_type']));?></span></td>
                        <td style="font-weight: 600; color: #0f172a;"><?php echo h($row['title']);?></td>
                        <td class="text-center"><?php echo $row['amount_mode']==='percentage'?__('Percentage'):__('Fixed Amount');?></td>
                        <td class="text-right" style="font-family: monospace; font-size: 13px; font-weight: 600;">
                            <?php echo $row['amount_mode']==='percentage'?h($row['amount_value']).'%':'Rs '.CakeNumber::format($row['amount_value']);?>
                        </td>
                        <td class="text-center"><?php echo (int)$row['repeat_count'];?></td>
                        <td class="text-center"><?php echo (int)$row['interval_months'];?></td>
                        <td class="text-right" style="font-family: monospace; font-size: 13px;">Rs <?php echo CakeNumber::format($row['unit_amount']);?></td>
                        <td class="text-right" style="font-family: monospace; font-size: 13.5px; font-weight: 700; color: #1e293b;">Rs <?php echo CakeNumber::format($row['row_total']);?></td>
                    </tr>
                <?php endforeach;?>
                </tbody>
                <tfoot>
                    <tr style="background:#f8fafc; font-weight:700;">
                        <td colspan="8" class="text-right" style="font-size:14px;"><?php echo __('Schedule Total');?>:</td>
                        <td class="text-right" style="font-family: monospace; font-size:15px; font-weight:800; color:#2563eb;">Rs <?php echo CakeNumber::format($scheduleTotal);?></td>
                    </tr>
                </tfoot>
            </table>
            <?php endif;?>
        </div>
    </div>
</div>
