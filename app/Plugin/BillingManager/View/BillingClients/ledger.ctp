<?php $c = $client['BillingClient']; $bills = $client['BillingBill']; $props = $client['BillingProperty']; ?>
<div class="page-title">
    <div class="title-env">
        <h1 class="title"><span class="fa fa-book"></span> <?php echo __('Consumer Account Ledger'); ?> &mdash; <?php echo h($c['name']); ?></h1>
        <p class="description"><?php echo __('Complete billing transaction history, payments recorded, and balance statement.'); ?></p>
    </div>
    <div class="pull-right" style="margin-top:10px;">
        <?php echo $this->Html->link('&larr; ' . __('Back to Directory'), array('action' => 'index'), array('escape' => false, 'class' => 'btn btn-default')); ?>
    </div>
</div>

<div class="panel">
    <div class="panel-body">
        <?php echo $this->Session->flash(); ?>

        <!-- Client Info Box -->
        <div class="well" style="background:#0f172a;color:#fff;border-radius:10px;padding:18px;margin-bottom:20px;">
            <div class="row">
                <div class="col-md-4">
                    <div style="font-size:11px;color:#94a3b8;text-transform:uppercase;font-weight:700;"><?php echo __('Consumer Details'); ?></div>
                    <div style="font-size:18px;font-weight:800;color:#38bdf8;margin:4px 0;"><?php echo h($c['name']); ?></div>
                    <div style="font-size:12px;color:#cbd5e1;"><?php echo __('Phone'); ?>: <?php echo h($c['phone']); ?> | <?php echo __('Email'); ?>: <?php echo h($c['email']); ?></div>
                </div>
                <div class="col-md-4">
                    <div style="font-size:11px;color:#94a3b8;text-transform:uppercase;font-weight:700;"><?php echo __('Assigned Units'); ?></div>
                    <div style="margin-top:6px;">
                        <?php if(!empty($props)){ foreach($props as $p){ ?>
                            <span class="label label-info" style="font-size:12px;margin-right:4px;"><?php echo h($p['title']); ?> (<?php echo h($p['property_no']); ?>)</span>
                        <?php } } else { echo '<span class="text-muted">' . __('No property assigned') . '</span>'; } ?>
                    </div>
                </div>
                <div class="col-md-4 text-right">
                    <div style="font-size:11px;color:#94a3b8;text-transform:uppercase;font-weight:700;"><?php echo __('Total Invoices Count'); ?></div>
                    <div style="font-size:24px;font-weight:800;color:#10b981;margin:4px 0;"><?php echo count($bills); ?></div>
                </div>
            </div>
        </div>

        <!-- Ledger Table -->
        <h4 style="font-weight:800;color:#0f172a;margin-bottom:14px;"><span class="fa fa-list"></span> <?php echo __('Invoice & Payment History'); ?></h4>
        <div class="table-responsive">
            <table class="table table-striped table-bordered" style="vertical-align:middle;font-size:12.5px;">
                <thead style="background:#f1f5f9;">
                    <tr>
                        <th><?php echo __('Bill #'); ?></th>
                        <th><?php echo __('Period'); ?></th>
                        <th><?php echo __('Issue Date'); ?></th>
                        <th><?php echo __('Due Date'); ?></th>
                        <th><?php echo __('Total Amount'); ?></th>
                        <th><?php echo __('Paid Amount'); ?></th>
                        <th><?php echo __('Balance'); ?></th>
                        <th><?php echo __('Payment Date / Method'); ?></th>
                        <th><?php echo __('Status'); ?></th>
                        <th><?php echo __('Action'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($bills)){ ?>
                        <tr><td colspan="10" class="text-center text-muted" style="padding:20px;"><?php echo __('No bills generated for this consumer yet.'); ?></td></tr>
                    <?php } else { foreach($bills as $b){ ?>
                        <tr>
                            <td><strong><?php echo h($b['bill_number']); ?></strong></td>
                            <td><span class="label label-default"><?php echo h($b['billing_period']); ?></span></td>
                            <td><?php echo !empty($b['issue_date']) ? date('d-M-Y', strtotime((string)$b['issue_date'])) : '-'; ?></td>
                            <td><?php echo !empty($b['due_date']) ? date('d-M-Y', strtotime((string)$b['due_date'])) : '-'; ?></td>
                            <td><strong style="color:#0284c7;">PKR <?php echo number_format((float)$b['total_amount'], 2); ?></strong></td>
                            <td><strong style="color:#10b981;">PKR <?php echo number_format((float)$b['paid_amount'], 2); ?></strong></td>
                            <td><strong style="color:#ef4444;">PKR <?php echo number_format((float)$b['balance_amount'], 2); ?></strong></td>
                            <td>
                                <?php if(!empty($b['payment_date'])){ ?>
                                    <div><?php echo date('d-M-Y', strtotime((string)$b['payment_date'])); ?></div>
                                    <small class="text-muted"><?php echo h($b['payment_method']); ?></small>
                                <?php } else { echo '<span class="text-muted">-</span>'; } ?>
                            </td>
                            <td>
                                <?php if($b['payment_status'] === 'paid'){ ?>
                                    <span class="label label-success"><?php echo __('PAID'); ?></span>
                                <?php } elseif($b['payment_status'] === 'partial'){ ?>
                                    <span class="label label-warning"><?php echo __('PARTIAL'); ?></span>
                                <?php } else { ?>
                                    <span class="label label-danger"><?php echo __('UNPAID'); ?></span>
                                <?php } ?>
                            </td>
                            <td>
                                <?php echo $this->Html->link('<span class="fa fa-print"></span> ' . __('Print'), array('controller' => 'BillingBills', 'action' => 'print_bill', $b['id']), array('escape' => false, 'target' => '_blank', 'class' => 'btn btn-xs btn-default')); ?>
                            </td>
                        </tr>
                    <?php } } ?>
                </tbody>
            </table>
        </div>

    </div>
</div>
