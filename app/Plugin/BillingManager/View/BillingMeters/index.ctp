<div class="page-title">
    <div class="title-env">
        <h1 class="title"><span class="fa fa-plug"></span> <?php echo __('Utility Meters Management'); ?></h1>
        <p class="description"><?php echo __('Manage electricity, water, and maintenance sub-meters assigned to property units.'); ?></p>
    </div>
    <div class="pull-right" style="margin-top:10px;display:flex;gap:8px;flex-wrap:wrap;">
        <a href="<?php echo $this->Html->url(array('controller' => 'billing_reports', 'action' => 'line_loss_audit')); ?>" class="btn btn-warning" style="font-weight:800;">
            <span class="fa fa-flash"></span> <?php echo __('⚡ Main Feeders & Transformers Audit'); ?>
        </a>
        <?php echo $this->Html->link('<span class="fa fa-plus"></span> ' . __('Add New Sub-Meter'), array('action' => 'add'), array('escape' => false, 'class' => 'btn btn-primary', 'style' => 'font-weight:700;')); ?>
    </div>
</div>

<div class="panel">
    <div class="panel-body">
        <?php echo $this->Session->flash(); ?>

        <div class="table-responsive">
            <table class="table table-striped table-bordered" style="vertical-align:middle;">
                <thead style="background:#f1f5f9;">
                    <tr>
                        <th>#</th>
                        <th><?php echo __('Meter Number'); ?></th>
                        <th><?php echo __('Utility Type'); ?></th>
                        <th><?php echo __('Assigned Property'); ?></th>
                        <th><?php echo __('Consumer Name'); ?></th>
                        <th><?php echo __('Multiplier'); ?></th>
                        <th><?php echo __('Initial Reading'); ?></th>
                        <th><?php echo __('Current Reading'); ?></th>
                        <th><?php echo __('Status'); ?></th>
                        <th style="min-width:110px;"><?php echo __('Actions'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($meters)){ ?>
                        <tr><td colspan="10" class="text-center text-muted" style="padding:24px;"><?php echo __('No meters configured.'); ?></td></tr>
                    <?php } else { foreach($meters as $m){ 
                        $mt = $m['BillingMeter']; 
                        $p = $m['BillingProperty']; 
                        $pId = !empty($mt['billing_property_id']) ? (int)$mt['billing_property_id'] : 0;
                        $c = isset($propToClient[$pId]) ? $propToClient[$pId] : (isset($p['BillingClient']) ? $p['BillingClient'] : array());
                        $cName = !empty($c['name']) ? $c['name'] : '-';
                        $cId = !empty($c['id']) ? ('WV70' . str_pad($c['id'], 2, '0', STR_PAD_LEFT)) : '';
                        $cPhone = !empty($c['phone']) ? $c['phone'] : '';
                    ?>
                        <tr>
                            <td><?php echo (int)$mt['id']; ?></td>
                            <td><strong style="color:#0284c7;"><span class="fa fa-tachometer"></span> <?php echo h($mt['meter_number']); ?></strong></td>
                            <td><span class="label label-warning"><?php echo h($mt['utility_type']); ?></span></td>
                            <td>
                                <strong><?php echo h(!empty($p['title']) ? $p['title'] : '-'); ?></strong>
                                <small class="text-muted" style="display:block;"><?php echo h(!empty($p['property_no']) ? $p['property_no'] : ''); ?></small>
                            </td>
                            <td>
                                <strong style="color:#0f172a;display:block;"><?php echo h($cName); ?></strong>
                                <div style="display:flex;gap:6px;align-items:center;margin-top:2px;">
                                    <?php if(!empty($cId)){ ?>
                                        <span class="badge" style="background:#e0f2fe;color:#0369a1;font-weight:700;font-size:10.5px;padding:2px 6px;"><?php echo h($cId); ?></span>
                                    <?php } ?>
                                    <?php if(!empty($cPhone)){ ?>
                                        <small class="text-muted" style="font-size:11px;"><i class="fa fa-phone"></i> <?php echo h($cPhone); ?></small>
                                    <?php } ?>
                                </div>
                            </td>
                            <td><?php echo number_format((float)$mt['multiplier'], 2); ?></td>
                            <td><?php echo number_format((float)$mt['initial_reading'], 2); ?></td>
                            <td><strong><?php echo number_format((float)$mt['current_reading'], 2); ?></strong></td>
                            <td>
                                <?php if($mt['status'] === 'active'){ ?>
                                    <span class="label label-success"><?php echo __('Active'); ?></span>
                                <?php } else { ?>
                                    <span class="label label-default"><?php echo h($mt['status']); ?></span>
                                <?php } ?>
                            </td>
                            <td style="white-space:nowrap;">
                                <a href="<?php echo $this->Html->url(array('action' => 'ledger', $mt['id'])); ?>" class="btn btn-xs btn-info" style="font-weight:700;margin-right:2px;" title="<?php echo __('View Meter & Billing Ledger'); ?>">
                                    <span class="fa fa-line-chart"></span> <?php echo __('Ledger'); ?>
                                </a>
                                <?php echo $this->Html->link('<span class="fa fa-pencil"></span>', array('action' => 'edit', $mt['id']), array('escape' => false, 'class' => 'btn btn-xs btn-default', 'title' => __('Edit'))); ?>
                                <button type="button" class="btn btn-xs btn-warning btn-swap-meter" data-id="<?php echo (int)$mt['id']; ?>" data-number="<?php echo h($mt['meter_number']); ?>" data-property="<?php echo h(!empty($p['title']) ? $p['title'] : ''); ?>" data-client="<?php echo h($cName); ?>" data-curr="<?php echo number_format((float)$mt['current_reading'], 2, '.', ''); ?>" title="<?php echo __('Replace / Swap Dead Meter'); ?>">
                                    <span class="fa fa-refresh"></span> <?php echo __('Swap'); ?>
                                </button>
                                <?php echo $this->Html->link('<span class="fa fa-trash"></span>', array('action' => 'delete', $mt['id']), array('escape' => false, 'class' => 'btn btn-xs btn-danger', 'title' => __('Delete'), 'onclick' => "return confirm('".__('Delete this meter?')."');")); ?>
                            </td>
                        </tr>
                    <?php } } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Replace / Swap Dead Meter -->
<div class="modal fade" id="replaceMeterModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <?php echo $this->Form->create('BillingMeter', array('url' => array('action' => 'replace_meter'), 'class' => 'form-horizontal')); ?>
            <input type="hidden" name="data[BillingMeter][old_meter_id]" id="swapOldMeterId" value="" />
            
            <div class="modal-header" style="background:#0f172a;color:#ffffff;border-radius:5px 5px 0 0;">
                <button type="button" class="close" data-dismiss="modal" style="color:#ffffff;opacity:0.9;">&times;</button>
                <h4 class="modal-title" style="font-weight:800;"><span class="fa fa-refresh text-warning"></span> <?php echo __('Replace / Swap Dead Meter'); ?></h4>
            </div>

            <div class="modal-body" style="padding:20px;">
                <div class="alert alert-info" style="font-size:12.5px;">
                    <strong><i class="fa fa-info-circle"></i> Meter Replacement Workflow:</strong>
                    This wizard will automatically set the old meter status to <strong>Replaced / Inactive</strong> with its final reading, activate the <strong>New Meter starting at 0.00</strong>, and optionally post a <strong>Replacement Charge / Penalty Fee</strong> to the client's next bill.
                </div>

                <div class="well" style="background:#f8fafc;padding:12px 16px;border-radius:6px;margin-bottom:15px;">
                    <div class="row">
                        <div class="col-sm-4">
                            <small class="text-muted" style="text-transform:uppercase;font-size:10px;font-weight:700;"><?php echo __('Old Meter Number'); ?>:</small>
                            <div id="swapOldMeterNum" style="font-size:15px;font-weight:800;color:#0284c7;"></div>
                        </div>
                        <div class="col-sm-4">
                            <small class="text-muted" style="text-transform:uppercase;font-size:10px;font-weight:700;"><?php echo __('Property Unit'); ?>:</small>
                            <div id="swapOldProperty" style="font-size:13.5px;font-weight:700;color:#0f172a;"></div>
                        </div>
                        <div class="col-sm-4">
                            <small class="text-muted" style="text-transform:uppercase;font-size:10px;font-weight:700;"><?php echo __('Resident / Client'); ?>:</small>
                            <div id="swapOldClient" style="font-size:13.5px;font-weight:700;color:#0f172a;"></div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <h5 style="font-weight:800;color:#dc2626;border-bottom:2px solid #fecaca;padding-bottom:6px;margin-bottom:12px;">
                            1. <?php echo __('Old Meter Final Reading'); ?>
                        </h5>
                        <div class="form-group">
                            <label class="col-sm-5 control-label" style="text-align:left;font-weight:bold;"><?php echo __('Final Dead Reading'); ?>: <span class="text-danger">*</span></label>
                            <div class="col-sm-7">
                                <input type="number" step="0.01" name="data[BillingMeter][old_final_reading]" id="swapOldFinalReading" class="form-control" required style="font-weight:bold;color:#dc2626;" placeholder="e.g. 5690.00" />
                                <small class="help-block" style="font-size:11px;"><?php echo __('Final recorded reading before meter stopped.'); ?></small>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-5 control-label" style="text-align:left;font-weight:bold;"><?php echo __('Replacement Reason'); ?>:</label>
                            <div class="col-sm-7">
                                <input type="text" name="data[BillingMeter][replacement_reason]" value="Dead / Faulty Meter Replacement" class="form-control" />
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <h5 style="font-weight:800;color:#16a34a;border-bottom:2px solid #bbf7d0;padding-bottom:6px;margin-bottom:12px;">
                            2. <?php echo __('New Meter Configuration'); ?>
                        </h5>
                        <div class="form-group">
                            <label class="col-sm-5 control-label" style="text-align:left;font-weight:bold;"><?php echo __('New Meter Number'); ?>: <span class="text-danger">*</span></label>
                            <div class="col-sm-7">
                                <input type="text" name="data[BillingMeter][new_meter_number]" class="form-control" required style="font-weight:bold;color:#16a34a;" placeholder="e.g. MTR-9002" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-5 control-label" style="text-align:left;font-weight:bold;"><?php echo __('Initial Reading'); ?>:</label>
                            <div class="col-sm-7">
                                <input type="number" step="0.01" name="data[BillingMeter][new_initial_reading]" value="0.00" class="form-control" style="font-weight:bold;" />
                                <small class="help-block" style="font-size:11px;"><?php echo __('Starts fresh from 0.00.'); ?></small>
                            </div>
                        </div>
                    </div>
                </div>

                <hr style="margin:15px 0;" />

                <h5 style="font-weight:800;color:#d97706;border-bottom:2px solid #fef3c7;padding-bottom:6px;margin-bottom:12px;">
                    3. <?php echo __('Recovery Charge & Penalty (Optional)'); ?>
                </h5>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="col-sm-5 control-label" style="text-align:left;font-weight:bold;"><?php echo __('Replacement Charge (PKR)'); ?>:</label>
                            <div class="col-sm-7">
                                <input type="number" step="0.01" name="data[BillingMeter][replacement_fee]" value="5000.00" class="form-control" style="font-weight:bold;color:#d97706;" placeholder="e.g. 5000" />
                                <small class="help-block" style="font-size:11px;"><?php echo __('Auto adds to client property extra charges for next bill.'); ?></small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="col-sm-5 control-label" style="text-align:left;font-weight:bold;"><?php echo __('Charge Description'); ?>:</label>
                            <div class="col-sm-7">
                                <input type="text" name="data[BillingMeter][replacement_fee_title]" value="Dead Meter Replacement & Penalty Charge" class="form-control" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer" style="background:#f8fafc;">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Cancel'); ?></button>
                <button type="submit" class="btn btn-warning" style="font-weight:800;padding:8px 24px;">
                    <span class="fa fa-refresh"></span> <?php echo __('Complete Meter Replacement'); ?>
                </button>
            </div>
            <?php echo $this->Form->end(); ?>
        </div>
    </div>
</div>

<script type="text/javascript">
document.addEventListener('DOMContentLoaded', function() {
    $('.btn-swap-meter').on('click', function() {
        var btn = $(this);
        $('#swapOldMeterId').val(btn.data('id'));
        $('#swapOldMeterNum').text(btn.data('number'));
        $('#swapOldProperty').text(btn.data('property'));
        $('#swapOldClient').text(btn.data('client'));
        $('#swapOldFinalReading').val(btn.data('curr'));
        $('#replaceMeterModal').modal('show');
    });
});
</script>
