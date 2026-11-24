<div class="page-title">
    <div class="title-env">
        <h1 class="title"><span class="fa fa-university"></span> <?php echo __('Bank Reconciliation & Online Payments'); ?></h1>
        <p class="description"><?php echo __('Verify resident bank transfers, approve transaction slips, and reconcile statement feeds.'); ?></p>
    </div>
</div>

<div class="panel">
    <div class="panel-body">
        <?php echo $this->Session->flash(); ?>

        <h4 style="font-weight:800;color:#0f172a;margin-bottom:14px;"><span class="fa fa-clock-o text-warning"></span> <?php echo __('Resident Payment Submissions Awaiting Approval'); ?></h4>
        <div class="table-responsive" style="margin-bottom:30px;">
            <table class="table table-striped table-bordered" style="vertical-align:middle;">
                <thead style="background:#f1f5f9;">
                    <tr>
                        <th>#</th>
                        <th><?php echo __('Bill ID'); ?></th>
                        <th><?php echo __('Amount (PKR)'); ?></th>
                        <th><?php echo __('Bank'); ?></th>
                        <th><?php echo __('Transaction Ref'); ?></th>
                        <th><?php echo __('Submitted Date'); ?></th>
                        <th><?php echo __('Action'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($pendingPayments)){ ?>
                        <tr><td colspan="7" class="text-center text-muted" style="padding:20px;"><?php echo __('No pending payment proofs.'); ?></td></tr>
                    <?php } else { foreach($pendingPayments as $pp){ $p = $pp['BillingPaymentRef']; ?>
                        <tr>
                            <td><?php echo (int)$p['id']; ?></td>
                            <td><strong>Bill #<?php echo (int)$p['bill_id']; ?></strong></td>
                            <td><strong style="color:#10b981;font-size:14px;">PKR <?php echo number_format((float)$p['amount'], 2); ?></strong></td>
                            <td><?php echo h($p['bank_name']); ?></td>
                            <td><code><?php echo h($p['transaction_ref']); ?></code></td>
                            <td><?php echo date('d-M-Y H:i', strtotime((string)$p['submission_date'])); ?></td>
                            <td>
                                <?php echo $this->Html->link('<span class="fa fa-check"></span> ' . __('Approve & Post'), array('action' => 'approve_payment', $p['id']), array('escape' => false, 'class' => 'btn btn-xs btn-success', 'style' => 'font-weight:700;')); ?>
                            </td>
                        </tr>
                    <?php } } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
