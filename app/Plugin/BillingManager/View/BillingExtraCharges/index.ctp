<div class="page-title">
    <div class="title-env">
        <h1 class="title"><span class="fa fa-list-alt"></span> <?php echo __('Society Maintenance & Extra Charges'); ?></h1>
        <p class="description"><?php echo __('Manage recurring and fixed charges (Sanitation, Security, Street Light, Sewerage, Community Fund).'); ?></p>
    </div>
    <div class="pull-right" style="margin-top:10px;">
        <?php echo $this->Html->link('<span class="fa fa-plus"></span> ' . __('Add Charge Head'), array('action' => 'add'), array('escape' => false, 'class' => 'btn btn-primary', 'style' => 'font-weight:700;')); ?>
    </div>
</div>

<div class="panel">
    <div class="panel-body">
        <?php echo $this->Session->flash(); ?>

        <!-- Society Filter -->
        <div class="well" style="background:#f8fafc;border:1px solid #cbd5e1;border-radius:8px;padding:12px 16px;margin-bottom:16px;">
            <div class="row" style="align-items:center;">
                <div class="col-md-5">
                    <label style="font-size:12px;font-weight:bold;margin-bottom:4px;"><?php echo __('Filter by Society / Agency'); ?>:</label>
                    <select class="form-control" onchange="window.location.href='<?php echo $this->Html->url(array('action' => 'index')); ?>?society_id=' + this.value;">
                        <option value="all" <?php if($selectedSocId === 'all') echo 'selected'; ?>><?php echo __('All Societies / Agencies (Super Admin)'); ?></option>
                        <?php foreach($societies as $sId => $sName){ ?>
                            <option value="<?php echo $sId; ?>" <?php if((string)$selectedSocId === (string)$sId) echo 'selected'; ?>>
                                🏢 <?php echo h($sName); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-md-7 text-right" style="padding-top:20px;">
                    <span class="badge" style="background:#0284c7;font-size:12px;padding:6px 12px;">
                        <?php echo count($charges); ?> <?php echo __('Charge Head(s) Loaded'); ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-bordered" style="vertical-align:middle;">
                <thead style="background:#f1f5f9;">
                    <tr>
                        <th>#</th>
                        <th><?php echo __('Society / Agency'); ?></th>
                        <th><?php echo __('Charge Title'); ?></th>
                        <th><?php echo __('Amount (PKR)'); ?></th>
                        <th><?php echo __('Type'); ?></th>
                        <th><?php echo __('Application'); ?></th>
                        <th><?php echo __('Status'); ?></th>
                        <th style="min-width:110px;"><?php echo __('Actions'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($charges)){ ?>
                        <tr><td colspan="8" class="text-center text-muted" style="padding:24px;"><?php echo __('No extra charges configured.'); ?></td></tr>
                    <?php } else { foreach($charges as $ch){ $c = $ch['BillingExtraCharge']; 
                        $socName = isset($societies[$c['society_id']]) ? $societies[$c['society_id']] : ('Society #' . $c['society_id']);
                    ?>
                        <tr>
                            <td><?php echo (int)$c['id']; ?></td>
                            <td><strong style="color:#0369a1;"><i class="fa fa-building"></i> <?php echo h($socName); ?></strong></td>
                            <td><strong style="font-size:14px;color:#0f172a;"><?php echo h($c['title']); ?></strong></td>
                            <td><strong style="color:#0284c7;font-size:14px;">PKR <?php echo number_format((float)$c['amount'], 2); ?></strong></td>
                            <td><span class="label label-info"><?php echo ucfirst(h($c['charge_type'])); ?></span></td>
                            <td><span class="label label-default"><?php echo ucfirst(h($c['apply_to'])); ?></span></td>
                            <td><span class="label label-success"><?php echo ucfirst(h($c['status'])); ?></span></td>
                            <td>
                                <?php echo $this->Html->link('<span class="fa fa-pencil"></span>', array('action' => 'edit', $c['id']), array('escape' => false, 'class' => 'btn btn-xs btn-default', 'title' => __('Edit'))); ?>
                                <?php echo $this->Html->link('<span class="fa fa-trash"></span>', array('action' => 'delete', $c['id']), array('escape' => false, 'class' => 'btn btn-xs btn-danger', 'title' => __('Delete'), 'onclick' => "return confirm('".__('Delete this charge head?')."');")); ?>
                            </td>
                        </tr>
                    <?php } } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Maintenance Breakdown (Conditional Rules by Property Type & Area) -->
<div class="panel" style="margin-top:24px;border:1px solid #cbd5e1;border-radius:8px;overflow:hidden;width:100%;">
    <div class="panel-heading" style="background:#0f172a;color:#ffffff;padding:16px 20px;">
        <h3 class="panel-title" style="font-weight:800;font-size:16px;color:#ffffff;margin:0;">
            <span class="fa fa-sliders text-warning"></span> <?php echo __('Society Maintenance Breakdown (Conditional by Property Type & Area)'); ?>
        </h3>
    </div>
    <div class="panel-body" style="padding:20px;width:100%;">
        <?php echo $this->Form->create('BillingExtraCharge', array('url' => array('controller' => 'billing_extra_charges', 'action' => 'save_maintenance_rules'))); ?>
        <input type="hidden" name="data[society_id]" value="<?php echo (int)($selectedSocId === 'all' ? $activeSocId : $selectedSocId); ?>" />

        <div class="alert alert-info" style="font-size:12.5px;margin-bottom:16px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">
            <div>
                <strong><i class="fa fa-info-circle"></i> <?php echo __('Isolated Society Maintenance Rules'); ?>:</strong>
                <?php echo __('These maintenance breakdown rules are stored separately for'); ?> 
                <strong style="color:#0369a1;font-size:13.5px;">🏢 <?php echo h(isset($societies[$selectedSocId]) ? $societies[$selectedSocId] : 'Selected Society'); ?></strong>.
            </div>
            <button type="button" class="btn btn-xs btn-warning" data-toggle="modal" data-target="#copyRulesModal" style="font-weight:700;">
                <i class="fa fa-copy"></i> <?php echo __('Copy Rules from another Society'); ?>
            </button>
        </div>

        <div class="table-responsive" style="width:100%;">
            <table class="table table-bordered table-striped" id="mHeadsTable" style="width:100%;vertical-align:middle;">
                <thead style="background:#f1f5f9;">
                    <tr>
                        <th style="width:26%;"><?php echo __('Charge Item Name'); ?></th>
                        <th style="width:18%;"><?php echo __('Property Category'); ?></th>
                        <th style="width:12%;"><?php echo __('Min Area'); ?></th>
                        <th style="width:12%;"><?php echo __('Max Area'); ?></th>
                        <th style="width:12%;"><?php echo __('Area Unit'); ?></th>
                        <th style="width:14%;"><?php echo __('Amount (PKR)'); ?></th>
                        <th style="width:6%;text-align:center;"><?php echo __('Action'); ?></th>
                    </tr>
                </thead>
                <tbody id="mHeadsTbody">
                    <?php if(!empty($maintenanceHeads)){ foreach($maintenanceHeads as $mk => $m){ ?>
                        <tr id="mHeadRow_<?php echo $mk; ?>">
                            <td>
                                <input type="text" name="data[m_head_name][]" value="<?php echo h($m['name']); ?>" class="form-control" placeholder="e.g. Services Chargers" required style="font-weight:700;" />
                            </td>
                            <td>
                                <select name="data[m_head_property_type][]" class="form-control">
                                    <?php foreach($propertyTypes as $pt){ ?>
                                        <option value="<?php echo $pt; ?>" <?php if(!empty($m['property_type']) && $m['property_type'] == $pt) echo 'selected'; ?>><?php echo $pt; ?></option>
                                    <?php } ?>
                                </select>
                            </td>
                            <td>
                                <input type="number" step="0.1" name="data[m_head_min_area][]" value="<?php echo isset($m['min_area']) ? h($m['min_area']) : ''; ?>" class="form-control" placeholder="Min" />
                            </td>
                            <td>
                                <input type="number" step="0.1" name="data[m_head_max_area][]" value="<?php echo isset($m['max_area']) ? h($m['max_area']) : ''; ?>" class="form-control" placeholder="Max" />
                            </td>
                            <td>
                                <select name="data[m_head_unit][]" class="form-control">
                                    <?php foreach($areaUnits as $au){ ?>
                                        <option value="<?php echo $au; ?>" <?php if(!empty($m['unit']) && $m['unit'] == $au) echo 'selected'; ?>><?php echo $au; ?></option>
                                    <?php } ?>
                                </select>
                            </td>
                            <td>
                                <input type="number" step="0.01" name="data[m_head_amount][]" value="<?php echo isset($m['amount']) ? h($m['amount']) : '0'; ?>" class="form-control" style="font-weight:800;color:#0284c7;" required />
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-danger remove-mhead-row" data-row="#mHeadRow_<?php echo $mk; ?>"><span class="fa fa-trash"></span></button>
                            </td>
                        </tr>
                    <?php } } ?>
                </tbody>
            </table>
        </div>

        <div style="display:flex;justify-content:space-between;align-items:center;margin-top:16px;border-top:1px solid #cbd5e1;padding-top:16px;">
            <button type="button" class="btn btn-primary" id="addMHeadBtn" style="font-weight:700;padding:8px 20px;">
                <span class="fa fa-plus"></span> <?php echo __('+ Add Maintenance Item'); ?>
            </button>
            <button type="submit" class="btn btn-success btn-lg" style="font-weight:800;padding:10px 36px;">
                <span class="fa fa-save"></span> <?php echo __('Save Society Breakdown Rules'); ?>
            </button>
        </div>
        <?php echo $this->Form->end(); ?>
    </div>
</div>

<script type="text/javascript">
document.addEventListener('DOMContentLoaded', function() {
    var mHeadIdx = 900;
    $('#addMHeadBtn').on('click', function() {
        mHeadIdx++;
        var html = '<tr id="mHeadRow_' + mHeadIdx + '">' +
            '<td><input type="text" name="data[m_head_name][]" class="form-control" style="font-weight:700;" placeholder="e.g. Services Chargers" required /></td>' +
            '<td><select name="data[m_head_property_type][]" class="form-control">' +
                <?php foreach($propertyTypes as $pt){ echo "'<option value=\"" . h($pt) . "\">" . h($pt) . "</option>' + "; } ?>
            '</select></td>' +
            '<td><input type="number" step="0.1" name="data[m_head_min_area][]" class="form-control" placeholder="Min" /></td>' +
            '<td><input type="number" step="0.1" name="data[m_head_max_area][]" class="form-control" placeholder="Max" /></td>' +
            '<td><select name="data[m_head_unit][]" class="form-control">' +
                <?php foreach($areaUnits as $au){ echo "'<option value=\"" . h($au) . "\">" . h($au) . "</option>' + "; } ?>
            '</select></td>' +
            '<td><input type="number" step="0.01" name="data[m_head_amount][]" value="0" class="form-control" style="font-weight:800;color:#0284c7;" required /></td>' +
            '<td class="text-center"><button type="button" class="btn btn-sm btn-danger remove-mhead-row" data-row="#mHeadRow_' + mHeadIdx + '"><span class="fa fa-trash"></span></button></td>' +
        '</tr>';
        $('#mHeadsTbody').append(html);
    });

    $(document).on('click', '.remove-mhead-row', function() {
        $($(this).data('row')).remove();
    });
});
</script>

<!-- Modal: Copy Society Breakdown Rules -->
<div class="modal fade" id="copyRulesModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <?php echo $this->Form->create('BillingSetting', array('url' => array('action' => 'copy_maintenance_rules'), 'class' => 'form-horizontal')); ?>
            <div class="modal-header" style="background:#0f172a;color:#ffffff;border-radius:5px 5px 0 0;">
                <button type="button" class="close" data-dismiss="modal" style="color:#ffffff;">&times;</button>
                <h4 class="modal-title" style="font-weight:800;"><i class="fa fa-copy text-warning"></i> <?php echo __('Copy Breakdown Rules Between Societies'); ?></h4>
            </div>
            <div class="modal-body" style="padding:20px;">
                <div class="form-group">
                    <label class="col-sm-4 control-label" style="font-weight:bold;"><?php echo __('Source Society'); ?>:</label>
                    <div class="col-sm-8">
                        <select name="from_society_id" class="form-control" required style="font-weight:bold;">
                            <option value=""><?php echo __('Select Source'); ?></option>
                            <?php foreach($societies as $sId => $sName){ ?>
                                <option value="<?php echo $sId; ?>">🏢 <?php echo h($sName); ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-4 control-label" style="font-weight:bold;"><?php echo __('Target Society'); ?>:</label>
                    <div class="col-sm-8">
                        <select name="to_society_id" class="form-control" required style="font-weight:bold;">
                            <option value=""><?php echo __('Select Target'); ?></option>
                            <?php foreach($societies as $sId => $sName){ ?>
                                <option value="<?php echo $sId; ?>" <?php if((string)$selectedSocId === (string)$sId) echo 'selected'; ?>>🏢 <?php echo h($sName); ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="background:#f8fafc;">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Cancel'); ?></button>
                <button type="submit" class="btn btn-warning" style="font-weight:800;padding:8px 24px;">
                    <i class="fa fa-copy"></i> <?php echo __('Copy Rules Now'); ?>
                </button>
            </div>
            <?php echo $this->Form->end(); ?>
        </div>
    </div>
</div>
