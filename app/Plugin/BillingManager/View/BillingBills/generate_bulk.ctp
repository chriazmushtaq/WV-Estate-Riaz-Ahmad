<div class="page-title">
    <div class="title-env">
        <h1 class="title"><span class="fa fa-cubes"></span> <?php echo __('Automated Bulk Bill Generator'); ?></h1>
        <p class="description"><?php echo __('Generate utility and maintenance invoices for all society properties in 1 click.'); ?></p>
    </div>
</div>

<div class="panel">
    <div class="panel-body">
        <?php echo $this->Session->flash(); ?>

        <div class="alert alert-info">
            <span class="fa fa-info-circle"></span> <strong><?php echo __('Automated Intelligence'); ?>:</strong>
            <?php echo __('The bulk generator will automatically scan each property unit, fetch the latest recorded meter reading for the selected month, apply recurring maintenance charges, and carry forward any previous unpaid balance as arrears.'); ?>
        </div>

        <?php echo $this->Form->create('BillingBill', array('class' => 'form-horizontal', 'style' => 'max-width:700px;margin:20px auto;')); ?>
            <div class="form-group">
                <label class="col-sm-4 control-label"><?php echo __('Billing Period (Month)'); ?> <span class="text-danger">*</span></label>
                <div class="col-sm-8">
                    <input type="month" name="data[BillingBill][billing_period]" value="<?php echo date('Y-m'); ?>" class="form-control" required />
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-4 control-label"><?php echo __('Default Electricity Unit Rate'); ?> <span class="text-danger">*</span></label>
                <div class="col-sm-8">
                    <input type="number" step="0.01" name="data[BillingBill][unit_rate]" value="25.00" class="form-control" required />
                    <span class="help-block" style="font-size:11px;"><?php echo __('PKR per electricity unit consumed.'); ?></span>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-4 control-label"><?php echo __('Bill Due Date'); ?> <span class="text-danger">*</span></label>
                <div class="col-sm-8">
                    <input type="date" name="data[BillingBill][due_date]" value="<?php echo date('Y-m-d', strtotime('+10 days')); ?>" class="form-control" required />
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-4 control-label"><?php echo __('Include Maintenance Charges'); ?></label>
                <div class="col-sm-8">
                    <label style="font-weight:normal;margin-top:7px;">
                        <input type="checkbox" name="data[BillingBill][include_maintenance]" value="1" checked /> <?php echo __('Apply recurring society maintenance charges (Security, Sanitation, etc.)'); ?>
                    </label>
                </div>
            </div>

            <div class="form-group" style="margin-top:24px;">
                <div class="col-sm-offset-4 col-sm-8">
                    <button type="submit" class="btn btn-success btn-lg btn-block" style="font-weight:800;" onclick="return confirm('<?php echo __('Generate bulk bills for all active properties for this month?'); ?>');">
                        <span class="fa fa-play-circle"></span> <?php echo __('Run Batch Bill Generation'); ?>
                    </button>
                </div>
            </div>
        <?php echo $this->Form->end(); ?>

    </div>
</div>
