<?php $b = $bill['BillingBill']; $p = $bill['BillingProperty']; $c = $bill['BillingClient']; ?>
<div class="page-title">
    <div class="title-env">
        <h1 class="title"><span class="fa fa-money text-success"></span> <?php echo __('Record Utility Bill Payment'); ?></h1>
        <p class="description"><?php echo __('Enter payment details and update consumer ledger for Bill #') . h($b['bill_number']); ?></p>
    </div>
</div>

<div class="panel">
    <div class="panel-body">
        <?php echo $this->Session->flash(); ?>

        <div class="row">
            <div class="col-md-5">
                <div class="well" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:20px;">
                    <h4 style="margin-top:0;font-size:16px;font-weight:800;border-bottom:1px solid #e2e8f0;padding-bottom:10px;"><?php echo __('Invoice Summary'); ?></h4>
                    <table class="table table-condensed" style="font-size:13px;margin-bottom:0;">
                        <tr><td><strong><?php echo __('Bill Number'); ?>:</strong></td><td><?php echo h($b['bill_number']); ?></td></tr>
                        <tr><td><strong><?php echo __('Property Unit'); ?>:</strong></td><td><?php echo h(!empty($p['title']) ? $p['title'] : 'Unit #' . $b['billing_property_id']); ?></td></tr>
                        <tr><td><strong><?php echo __('Consumer'); ?>:</strong></td><td><?php echo h(!empty($c['name']) ? $c['name'] : '-'); ?></td></tr>
                        <tr><td><strong><?php echo __('Billing Period'); ?>:</strong></td><td><?php echo h($b['billing_period']); ?></td></tr>
                        <tr><td><strong><?php echo __('Total Invoiced'); ?>:</strong></td><td><strong style="color:#0284c7;">PKR <?php echo number_format((float)$b['total_amount'], 2); ?></strong></td></tr>
                        <tr><td><strong><?php echo __('Already Paid'); ?>:</strong></td><td>PKR <?php echo number_format((float)$b['paid_amount'], 2); ?></td></tr>
                        <tr><td><strong><?php echo __('Remaining Due'); ?>:</strong></td><td><strong style="color:#ef4444;font-size:15px;">PKR <?php echo number_format((float)$b['balance_amount'], 2); ?></strong></td></tr>
                    </table>
                </div>
            </div>

            <div class="col-md-7">
                <?php echo $this->Form->create('BillingBill', array('class' => 'form-horizontal')); ?>
                    <div class="form-group">
                        <label class="col-sm-4 control-label"><?php echo __('Payment Amount (PKR)'); ?> <span class="text-danger">*</span></label>
                        <div class="col-sm-8">
                            <input type="number" step="0.01" name="data[BillingBill][paid_amount]" value="<?php echo (float)$b['total_amount']; ?>" class="form-control input-lg" required style="font-size:18px;font-weight:800;color:#10b981;" />
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-4 control-label"><?php echo __('Payment Date'); ?> <span class="text-danger">*</span></label>
                        <div class="col-sm-8">
                            <input type="date" name="data[BillingBill][payment_date]" value="<?php echo date('Y-m-d'); ?>" class="form-control" required />
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-4 control-label"><?php echo __('Payment Method'); ?></label>
                        <div class="col-sm-8">
                            <select name="data[BillingBill][payment_method]" class="form-control">
                                <option value="Cash"><?php echo __('Cash'); ?></option>
                                <option value="Bank Transfer"><?php echo __('Bank Transfer'); ?></option>
                                <option value="Cheque"><?php echo __('Cheque'); ?></option>
                                <option value="JazzCash"><?php echo __('JazzCash'); ?></option>
                                <option value="EasyPaisa"><?php echo __('EasyPaisa'); ?></option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-4 control-label"><?php echo __('Transaction Ref / Slip #'); ?></label>
                        <div class="col-sm-8">
                            <input type="text" name="data[BillingBill][transaction_ref]" placeholder="<?php echo __('e.g. TRX-1029384'); ?>" class="form-control" />
                        </div>
                    </div>

                    <div class="form-group" style="margin-top:20px;">
                        <div class="col-sm-offset-4 col-sm-8">
                            <button type="submit" class="btn btn-success btn-lg" style="font-weight:800;padding:10px 28px;">
                                <span class="fa fa-check-circle"></span> <?php echo __('Confirm & Record Payment'); ?>
                            </button>
                            <a href="<?php echo $this->Html->url(array('action' => 'index')); ?>" class="btn btn-default btn-lg"><?php echo __('Cancel'); ?></a>
                        </div>
                    </div>
                <?php echo $this->Form->end(); ?>
            </div>
        </div>

    </div>
</div>
