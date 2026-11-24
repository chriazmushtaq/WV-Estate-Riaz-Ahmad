<div class="page-title">
    <div class="title-env">
        <h1 class="title"><span class="fa fa-th"></span> <?php echo __('Fast Bulk Reading Grid'); ?></h1>
        <p class="description"><?php echo __('Enter monthly meter readings across all units on a single interactive spreadsheet grid with auto previous reading & unit calculation.'); ?></p>
    </div>
</div>

<div class="panel">
    <div class="panel-body">
        <?php echo $this->Session->flash(); ?>

        <div class="well" style="background:#f8fafc;border:1px solid #cbd5e1;border-radius:8px;padding:16px;margin-bottom:20px;">
            <div class="row">
                <div class="col-md-4">
                    <label style="font-size:12px;font-weight:bold;"><?php echo __('Select Billing Month'); ?>:</label>
                    <input type="month" id="filterMonth" value="<?php echo h($month); ?>" class="form-control" onchange="window.location.href='<?php echo $this->Html->url(array('action' => 'bulk_entry')); ?>?month=' + this.value;" />
                </div>
                <div class="col-md-4">
                    <label style="font-size:12px;font-weight:bold;"><?php echo __('Active Month Grid'); ?>:</label>
                    <div style="font-size:16px;font-weight:800;color:#0284c7;margin-top:6px;">
                        <span class="fa fa-calendar"></span> <?php echo date('F Y', strtotime($month . '-01')); ?>
                    </div>
                </div>
                <div class="col-md-4 text-right" style="padding-top:20px;">
                    <a href="<?php echo $this->Html->url(array('action' => 'index')); ?>" class="btn btn-default">
                        &larr; <?php echo __('Back to Readings List'); ?>
                    </a>
                </div>
            </div>
        </div>

        <?php echo $this->Form->create('BillingReading', array('class' => 'form-horizontal')); ?>
        <input type="hidden" name="data[BillingReading][reading_month]" value="<?php echo h($month); ?>" />

        <div class="row" style="margin-bottom:15px;">
            <div class="col-md-6">
                <div class="form-group">
                    <label class="col-sm-4 control-label" style="text-align:left;font-weight:bold;"><?php echo __('Reading Date'); ?>: <span class="text-danger">*</span></label>
                    <div class="col-sm-8">
                        <input type="date" name="data[BillingReading][reading_date]" value="<?php echo date('Y-m-d'); ?>" class="form-control" required />
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="col-sm-4 control-label" style="text-align:left;font-weight:bold;"><?php echo __('Reader Name'); ?>:</label>
                    <div class="col-sm-8">
                        <input type="text" name="data[BillingReading][reader_name]" value="Admin" class="form-control" />
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-bordered" style="vertical-align:middle;font-size:13px;">
                <thead style="background:#0f172a;color:#fff;">
                    <tr>
                        <th style="width:50px;">#</th>
                        <th><?php echo __('Property Unit'); ?></th>
                        <th><?php echo __('Consumer / Resident'); ?></th>
                        <th><?php echo __('Meter Number'); ?></th>
                        <th style="width:160px;"><?php echo __('Previous Reading'); ?></th>
                        <th style="width:180px;"><?php echo __('Current Reading'); ?> <span class="text-danger">*</span></th>
                        <th style="width:120px;"><?php echo __('Solar Export'); ?></th>
                        <th style="width:140px;"><?php echo __('Units Consumed'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($meterRows)){ ?>
                        <tr><td colspan="8" class="text-center text-muted" style="padding:24px;"><?php echo __('No active meters found.'); ?></td></tr>
                    <?php } else { $idx = 0; foreach($meterRows as $row){ $m = $row['meter']; $p = $row['property']; $c = $row['client']; $idx++; ?>
                        <tr>
                            <td><strong><?php echo $idx; ?></strong></td>
                            <td>
                                <strong style="color:#0f172a;font-size:13.5px;"><?php echo h(!empty($p['title']) ? $p['title'] : 'Unit #' . $m['billing_property_id']); ?></strong>
                                <div class="text-muted" style="font-size:11px;"><?php echo h(!empty($p['property_no']) ? $p['property_no'] : ''); ?></div>
                            </td>
                            <td>
                                <div><strong><?php echo h(!empty($c['name']) ? $c['name'] : '-'); ?></strong></div>
                                <small class="text-muted"><?php echo h(!empty($c['phone']) ? $c['phone'] : ''); ?></small>
                            </td>
                            <td>
                                <strong><span class="fa fa-plug text-warning"></span> <?php echo h($m['meter_number']); ?></strong>
                                <small class="text-muted" style="display:block;"><?php echo h($m['utility_type']); ?></small>
                            </td>
                            <td>
                                <input type="number" step="0.01" readonly name="data[ReadingRow][<?php echo $m['id']; ?>][previous_reading]" id="prev_<?php echo $m['id']; ?>" value="<?php echo number_format((float)$row['previous_reading'], 2, '.', ''); ?>" class="form-control input-sm" style="background:#f1f5f9;font-weight:bold;color:#334155;" />
                                <input type="hidden" name="data[ReadingRow][<?php echo $m['id']; ?>][property_id]" value="<?php echo (int)$m['billing_property_id']; ?>" />
                            </td>
                            <td>
                                <input type="number" step="0.01" name="data[ReadingRow][<?php echo $m['id']; ?>][current_reading]" id="curr_<?php echo $m['id']; ?>" value="<?php echo $row['current_reading'] !== '' ? number_format((float)$row['current_reading'], 2, '.', '') : ''; ?>" placeholder="<?php echo __('Enter reading'); ?>" class="form-control input-sm grid-reading" data-meter-id="<?php echo $m['id']; ?>" style="font-weight:bold;color:#0284c7;background:#fff;" />
                            </td>
                            <td>
                                <input type="number" step="0.01" name="data[ReadingRow][<?php echo $m['id']; ?>][export_reading]" placeholder="0.00" class="form-control input-sm" style="color:#059669;font-weight:bold;" />
                            </td>
                            <td>
                                <span id="units_<?php echo $m['id']; ?>" class="label label-info" style="font-size:12px;display:inline-block;padding:6px 12px;font-weight:bold;">
                                    <?php echo $row['units'] !== '' ? number_format((float)$row['units'], 2) : '0.00'; ?>
                                </span>
                            </td>
                        </tr>
                    <?php } } ?>
                </tbody>
            </table>
        </div>

        <div class="text-right" style="margin-top:20px;">
            <a href="<?php echo $this->Html->url(array('action' => 'index')); ?>" class="btn btn-default btn-lg"><?php echo __('Cancel'); ?></a>
            <button type="submit" class="btn btn-success btn-lg" style="font-weight:800;padding:10px 36px;">
                <span class="fa fa-save"></span> <?php echo __('Save All Meter Readings'); ?>
            </button>
        </div>
        <?php echo $this->Form->end(); ?>

    </div>
</div>

<script type="text/javascript">
document.addEventListener('DOMContentLoaded', function() {
    var inputs = Array.from(document.querySelectorAll('.grid-reading'));
    inputs.forEach(function(input, idx) {
        input.addEventListener('input', function() {
            var meterId = this.getAttribute('data-meter-id');
            var prev = parseFloat(document.getElementById('prev_' + meterId).value) || 0;
            var curr = parseFloat(this.value);
            var span = document.getElementById('units_' + meterId);
            if(isNaN(curr) || this.value.trim() === '') {
                span.innerText = '0.00';
                span.className = 'label label-default';
                return;
            }
            var units = Math.max(0, curr - prev);
            span.innerText = units.toFixed(2);
            if(curr < prev) {
                span.className = 'label label-danger';
                span.innerText = 'Rollback (< Prev)';
            } else if(units > 500) {
                span.className = 'label label-warning';
                span.innerText = units.toFixed(2) + ' (High Spike)';
            } else {
                span.className = 'label label-success';
            }
        });

        // Fast Enter key navigation to next row
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                if (idx < inputs.length - 1) {
                    inputs[idx + 1].focus();
                    inputs[idx + 1].select();
                }
            }
        });
    });
});
</script>
