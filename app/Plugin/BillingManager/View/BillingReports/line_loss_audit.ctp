<style>
.loss-kpi-card { background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 18px; box-shadow: 0 2px 6px rgba(0,0,0,0.04); }
.loss-kpi-label { font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; margin-bottom: 6px; }
.loss-kpi-val { font-size: 24px; font-weight: 900; line-height: 1.1; }
.loss-kpi-sub { font-size: 11.5px; color: #64748b; margin-top: 6px; font-weight: 600; }
.alert-theft-critical { background: #fee2e2; border: 2px solid #ef4444; border-radius: 8px; padding: 16px 20px; color: #991b1b; margin-bottom: 24px; display: flex; align-items: center; gap: 16px; }
.alert-theft-normal { background: #f0fdf4; border: 1.5px solid #86efac; border-radius: 8px; padding: 14px 18px; color: #166534; margin-bottom: 24px; display: flex; align-items: center; gap: 14px; font-weight: 600; }
.feeder-badge { font-size: 11px; font-weight: 800; padding: 3px 8px; border-radius: 6px; text-transform: uppercase; }
.feeder-badge-elec { background: #fef3c7; color: #b45309; border: 1px solid #fde68a; }
.feeder-badge-water { background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }
.feeder-badge-gas { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
.water-kpi-card { background: #ffffff; border: 1.5px solid #bae6fd; border-radius: 10px; padding: 16px; box-shadow: 0 2px 6px rgba(2,132,199,0.06); }
.gas-kpi-card { background: #ffffff; border: 1.5px solid #fecaca; border-radius: 10px; padding: 16px; box-shadow: 0 2px 6px rgba(185,28,28,0.06); }
</style>

<div class="subm-admin-wrap" style="padding:18px;background:#f8fafc;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;">

    <!-- Top Action Bar -->
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:20px;">
        <div>
            <h3 style="font-weight:900;color:#0f172a;margin:0 0 4px 0;font-size:20px;">
                <i class="fa fa-flash text-warning"></i> <?php echo __('Utility Master Reconciliation & Cost Propagation Hub'); ?>
            </h3>
            <span style="font-size:13px;color:#64748b;font-weight:600;">
                <?php echo __('Society:'); ?> <strong><?php echo h($societyName); ?></strong> | <?php echo __('Billing Period:'); ?> <strong><?php echo date('F Y', strtotime($month . '-01')); ?></strong>
            </span>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <button type="button" class="btn btn-success" style="font-weight:800;" onclick="openAddFeederModal('transformer')">
                <i class="fa fa-plus-circle"></i> <?php echo __('+ Add Transformer'); ?>
            </button>
            <button type="button" class="btn btn-info" style="font-weight:800;" onclick="openAddFeederModal('water_pump')">
                <i class="fa fa-tint"></i> <?php echo __('+ Add Water Pump / Tube-Well'); ?>
            </button>
            <button type="button" class="btn btn-danger" style="font-weight:800;" onclick="openAddFeederModal('gas_station')">
                <i class="fa fa-fire"></i> <?php echo __('+ Add Gas Incomer'); ?>
            </button>
            <button type="button" class="btn btn-warning" style="font-weight:800;color:#1e293b;" onclick="openPropagateModal('water')">
                <i class="fa fa-share-alt"></i> <?php echo __('⚡ Propagate Utility to Bills'); ?>
            </button>
            <a href="<?php echo $this->Html->url(array('controller' => 'billing_readings', 'action' => 'bulk_entry', '?' => array('month' => $month))); ?>" class="btn btn-default" style="font-weight:700;">
                <i class="fa fa-tachometer text-primary"></i> <?php echo __('Resident Sub-Meters Grid'); ?>
            </a>
            <a href="<?php echo $this->Html->url(array('action' => 'index', '?' => array('society_id' => $societyId))); ?>" class="btn btn-default" style="font-weight:700;">
                &larr; <?php echo __('Reports Hub'); ?>
            </a>
            <button onclick="window.print();" class="btn btn-primary" style="font-weight:800;">
                <i class="fa fa-print"></i> <?php echo __('Print Audit'); ?>
            </button>
        </div>
    </div>

    <?php echo $this->Session->flash(); ?>

    <?php if($enabled === '0' || $enabled === 0){ ?>
        <div class="alert alert-warning" style="font-weight:600;">
            <i class="fa fa-exclamation-triangle"></i> <?php echo __('Notice: Feeder Line Loss & Power Theft Audit is currently switched OFF in society Feature Toggles.'); ?>
        </div>
    <?php } ?>

    <!-- Filter Bar (Society & Month) -->
    <div style="background:#ffffff;border:1.5px solid #cbd5e1;border-radius:10px;padding:14px 20px;margin-bottom:22px;box-shadow:0 2px 6px rgba(0,0,0,0.03);">
        <form method="get" action="<?php echo $this->Html->url(array('action' => 'line_loss_audit')); ?>" class="form-inline" style="display:flex;gap:16px;align-items:center;flex-wrap:wrap;">
            <div>
                <label style="font-weight:800;font-size:12px;color:#334155;text-transform:uppercase;margin-right:8px;"><?php echo __('Society:'); ?></label>
                <select name="society_id" class="form-control" style="font-weight:bold;height:36px;" onchange="this.form.submit()">
                    <?php foreach($societies as $sId => $sName){ ?>
                        <option value="<?php echo $sId; ?>" <?php if($sId == $societyId) echo 'selected'; ?>><?php echo h($sName); ?></option>
                    <?php } ?>
                </select>
            </div>
            <div>
                <label style="font-weight:800;font-size:12px;color:#334155;text-transform:uppercase;margin-right:8px;"><?php echo __('Audit Month:'); ?></label>
                <select name="month" class="form-control" style="font-weight:bold;height:36px;" onchange="this.form.submit()">
                    <?php foreach($availableMonths as $mKey => $mLbl){ ?>
                        <option value="<?php echo $mKey; ?>" <?php if($mKey === $month) echo 'selected'; ?>><?php echo $mLbl; ?></option>
                    <?php } ?>
                </select>
            </div>
            <div style="margin-left:auto;font-size:13px;font-weight:700;display:flex;gap:16px;flex-wrap:wrap;">
                <span style="color:#b45309;"><i class="fa fa-bolt"></i> <?php echo count($transformers); ?> <?php echo __('Transformers'); ?></span>
                <span style="color:#0369a1;"><i class="fa fa-tint"></i> <?php echo count($waterPumps); ?> <?php echo __('Water Pumps'); ?></span>
                <span style="color:#b91c1c;"><i class="fa fa-fire"></i> <?php echo count($gasStations); ?> <?php echo __('Gas Incomers'); ?></span>
                <span style="color:#059669;"><i class="fa fa-home"></i> <?php echo $activePropertiesCount; ?> <?php echo __('Active Villas'); ?></span>
            </div>
        </form>
    </div>

    <!-- ======================================================== -->
    <!-- MONTHLY PROPAGATED CHARGES STATUS STRIP (ACTIVE ON BILLS) -->
    <!-- ======================================================== -->
    <div style="background:#ffffff;border:1.5px solid #cbd5e1;border-radius:10px;padding:16px 20px;margin-bottom:24px;box-shadow:0 2px 8px rgba(0,0,0,0.04);">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;flex-wrap:wrap;gap:8px;">
            <div>
                <strong style="font-size:14px;color:#0f172a;text-transform:uppercase;letter-spacing:0.5px;">
                    <i class="fa fa-share-alt text-warning"></i> <?php echo __('Monthly Propagated Charges to Resident Bills'); ?>
                </strong>
                <span style="font-size:12px;color:#64748b;margin-left:8px;">
                    (<?php echo __('Period:'); ?> <strong><?php echo date('F Y', strtotime($month . '-01')); ?></strong>)
                </span>
            </div>
            <span style="font-size:12px;color:#64748b;">
                <?php echo __('When posted, these recurring/monthly utility surcharges appear on every resident bill during bill generation.'); ?>
            </span>
        </div>

        <div class="row">
            <!-- Water Charge Status -->
            <div class="col-md-4">
                <div style="border:1.5px solid <?php echo !empty($postedUtilityMap['water']) ? '#86efac' : '#cbd5e1'; ?>;background:<?php echo !empty($postedUtilityMap['water']) ? '#f0fdf4' : '#f8fafc'; ?>;border-radius:8px;padding:14px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                        <span style="font-weight:800;font-size:13px;color:#0369a1;">
                            <i class="fa fa-tint"></i> <?php echo __('Water Supply Charge'); ?>
                        </span>
                        <?php if(!empty($postedUtilityMap['water'])){ ?>
                            <span class="label label-success" style="font-size:10.5px;padding:3px 8px;font-weight:800;">
                                <i class="fa fa-check-circle"></i> <?php echo __('POSTED'); ?>
                            </span>
                        <?php } else { ?>
                            <span class="label label-default" style="font-size:10.5px;padding:3px 8px;">
                                <?php echo __('NOT POSTED'); ?>
                            </span>
                        <?php } ?>
                    </div>
                    <?php if(!empty($postedUtilityMap['water'])){ ?>
                        <div style="font-size:18px;font-weight:900;color:#166534;">
                            PKR <?php echo number_format($postedUtilityMap['water']['amount'], 2); ?> <span style="font-size:11px;font-weight:600;color:#15803d;">/ villa</span>
                        </div>
                        <div style="font-size:11.5px;color:#166534;margin:4px 0 10px 0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="<?php echo h($postedUtilityMap['water']['title']); ?>">
                            <?php echo h($postedUtilityMap['water']['title']); ?>
                        </div>
                        <div style="display:flex;gap:6px;">
                            <button type="button" class="btn btn-xs btn-warning" onclick="openPropagateModal('water')" style="font-weight:700;">
                                <i class="fa fa-pencil"></i> <?php echo __('Edit / Re-post'); ?>
                            </button>
                            <a href="<?php echo $this->Html->url(array('action' => 'delete_propagated_charge', $postedUtilityMap['water']['id'], '?' => array('society_id' => $societyId, 'month' => $month))); ?>" class="btn btn-xs btn-danger" onclick="return confirm('<?php echo __('Are you sure you want to DELETE and remove this Water charge from resident bills?'); ?>');" style="font-weight:700;">
                                <i class="fa fa-trash"></i> <?php echo __('Delete / Un-post'); ?>
                            </a>
                        </div>
                    <?php } else { ?>
                        <div style="font-size:18px;font-weight:900;color:#64748b;">
                            PKR <?php echo number_format($waterCostPerVilla, 2); ?> <span style="font-size:11px;font-weight:600;">/ villa (Est)</span>
                        </div>
                        <div style="font-size:11.5px;color:#64748b;margin:4px 0 10px 0;">
                            <?php echo __('No charge active. Click propagate to apply to bills.'); ?>
                        </div>
                        <button type="button" class="btn btn-xs btn-info" onclick="openPropagateModal('water')" style="font-weight:700;">
                            <i class="fa fa-share-alt"></i> <?php echo __('⚡ Propagate Water'); ?>
                        </button>
                    <?php } ?>
                </div>
            </div>

            <!-- Electricity Line Loss Charge Status -->
            <div class="col-md-4">
                <div style="border:1.5px solid <?php echo !empty($postedUtilityMap['electricity']) ? '#86efac' : '#cbd5e1'; ?>;background:<?php echo !empty($postedUtilityMap['electricity']) ? '#f0fdf4' : '#f8fafc'; ?>;border-radius:8px;padding:14px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                        <span style="font-weight:800;font-size:13px;color:#b45309;">
                            <i class="fa fa-flash"></i> <?php echo __('Electricity Line Loss'); ?>
                        </span>
                        <?php if(!empty($postedUtilityMap['electricity'])){ ?>
                            <span class="label label-success" style="font-size:10.5px;padding:3px 8px;font-weight:800;">
                                <i class="fa fa-check-circle"></i> <?php echo __('POSTED'); ?>
                            </span>
                        <?php } else { ?>
                            <span class="label label-default" style="font-size:10.5px;padding:3px 8px;">
                                <?php echo __('NOT POSTED'); ?>
                            </span>
                        <?php } ?>
                    </div>
                    <?php if(!empty($postedUtilityMap['electricity'])){ ?>
                        <div style="font-size:18px;font-weight:900;color:#166534;">
                            PKR <?php echo number_format($postedUtilityMap['electricity']['amount'], 2); ?> <span style="font-size:11px;font-weight:600;color:#15803d;">/ villa</span>
                        </div>
                        <div style="font-size:11.5px;color:#166534;margin:4px 0 10px 0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="<?php echo h($postedUtilityMap['electricity']['title']); ?>">
                            <?php echo h($postedUtilityMap['electricity']['title']); ?>
                        </div>
                        <div style="display:flex;gap:6px;">
                            <button type="button" class="btn btn-xs btn-warning" onclick="openPropagateModal('electricity')" style="font-weight:700;">
                                <i class="fa fa-pencil"></i> <?php echo __('Edit / Re-post'); ?>
                            </button>
                            <a href="<?php echo $this->Html->url(array('action' => 'delete_propagated_charge', $postedUtilityMap['electricity']['id'], '?' => array('society_id' => $societyId, 'month' => $month))); ?>" class="btn btn-xs btn-danger" onclick="return confirm('<?php echo __('Are you sure you want to DELETE and remove this Electricity surcharge from resident bills?'); ?>');" style="font-weight:700;">
                                <i class="fa fa-trash"></i> <?php echo __('Delete / Un-post'); ?>
                            </a>
                        </div>
                    <?php } else { ?>
                        <div style="font-size:18px;font-weight:900;color:#64748b;">
                            PKR <?php echo number_format($elecDeficitPerVilla, 2); ?> <span style="font-size:11px;font-weight:600;">/ villa (Est)</span>
                        </div>
                        <div style="font-size:11.5px;color:#64748b;margin:4px 0 10px 0;">
                            <?php echo __('No charge active. Click propagate to apply to bills.'); ?>
                        </div>
                        <button type="button" class="btn btn-xs btn-warning" onclick="openPropagateModal('electricity')" style="font-weight:700;color:#0f172a;">
                            <i class="fa fa-share-alt"></i> <?php echo __('⚡ Propagate Electricity'); ?>
                        </button>
                    <?php } ?>
                </div>
            </div>

            <!-- Gas Supply Charge Status -->
            <div class="col-md-4">
                <div style="border:1.5px solid <?php echo !empty($postedUtilityMap['gas']) ? '#86efac' : '#cbd5e1'; ?>;background:<?php echo !empty($postedUtilityMap['gas']) ? '#f0fdf4' : '#f8fafc'; ?>;border-radius:8px;padding:14px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                        <span style="font-weight:800;font-size:13px;color:#b91c1c;">
                            <i class="fa fa-fire"></i> <?php echo __('Gas Incomer Cost'); ?>
                        </span>
                        <?php if(!empty($postedUtilityMap['gas'])){ ?>
                            <span class="label label-success" style="font-size:10.5px;padding:3px 8px;font-weight:800;">
                                <i class="fa fa-check-circle"></i> <?php echo __('POSTED'); ?>
                            </span>
                        <?php } else { ?>
                            <span class="label label-default" style="font-size:10.5px;padding:3px 8px;">
                                <?php echo __('NOT POSTED'); ?>
                            </span>
                        <?php } ?>
                    </div>
                    <?php if(!empty($postedUtilityMap['gas'])){ ?>
                        <div style="font-size:18px;font-weight:900;color:#166534;">
                            PKR <?php echo number_format($postedUtilityMap['gas']['amount'], 2); ?> <span style="font-size:11px;font-weight:600;color:#15803d;">/ villa</span>
                        </div>
                        <div style="font-size:11.5px;color:#166534;margin:4px 0 10px 0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="<?php echo h($postedUtilityMap['gas']['title']); ?>">
                            <?php echo h($postedUtilityMap['gas']['title']); ?>
                        </div>
                        <div style="display:flex;gap:6px;">
                            <button type="button" class="btn btn-xs btn-warning" onclick="openPropagateModal('gas')" style="font-weight:700;">
                                <i class="fa fa-pencil"></i> <?php echo __('Edit / Re-post'); ?>
                            </button>
                            <a href="<?php echo $this->Html->url(array('action' => 'delete_propagated_charge', $postedUtilityMap['gas']['id'], '?' => array('society_id' => $societyId, 'month' => $month))); ?>" class="btn btn-xs btn-danger" onclick="return confirm('<?php echo __('Are you sure you want to DELETE and remove this Gas charge from resident bills?'); ?>');" style="font-weight:700;">
                                <i class="fa fa-trash"></i> <?php echo __('Delete / Un-post'); ?>
                            </a>
                        </div>
                    <?php } else { ?>
                        <div style="font-size:18px;font-weight:900;color:#64748b;">
                            PKR <?php echo number_format($gasCostPerVilla, 2); ?> <span style="font-size:11px;font-weight:600;">/ villa (Est)</span>
                        </div>
                        <div style="font-size:11.5px;color:#64748b;margin:4px 0 10px 0;">
                            <?php echo __('No charge active. Click propagate to apply to bills.'); ?>
                        </div>
                        <button type="button" class="btn btn-xs btn-danger" onclick="openPropagateModal('gas')" style="font-weight:700;">
                            <i class="fa fa-share-alt"></i> <?php echo __('⚡ Propagate Gas'); ?>
                        </button>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Banner based on Overall Consolidated Risk Level -->
    <?php if($overallRiskLevel === 'CRITICAL THEFT ALERT'){ ?>
        <div class="alert-theft-critical">
            <i class="fa fa-warning" style="font-size:32px;"></i>
            <div>
                <strong style="font-size:15px;text-transform:uppercase;display:block;"><?php echo __('CRITICAL POWER THEFT & UNACCOUNTED LOSS DETECTED ('); ?><?php echo $overallLossPercent; ?>% LOSS)</strong>
                <span><?php echo __('Society-wide energy deficit exceeds technical tolerance threshold. Review individual transformer metrics below to pinpoint the high-theft sector.'); ?></span>
            </div>
        </div>
    <?php } else { ?>
        <div class="alert-theft-normal">
            <i class="fa fa-check-circle" style="font-size:24px;"></i>
            <div>
                <strong><?php echo __('Consolidated Distribution Normal:'); ?></strong>
                <span><?php echo __('Consolidated line loss is within standard technical range ('); ?><?php echo $overallLossPercent; ?>%<?php echo __('). Auxiliary water pump electric loads have been successfully deducted from transformers.'); ?></span>
            </div>
        </div>
    <?php } ?>

    <!-- 5 Consolidated Executive KPI Cards -->
    <div class="row" style="margin-bottom:24px;">
        <div class="col-md-3">
            <div class="loss-kpi-card">
                <div class="loss-kpi-label"><i class="fa fa-plug text-primary"></i> <?php echo __('Total Bulk Incomer (Transformers)'); ?></div>
                <div class="loss-kpi-val" style="color:#0284c7;"><?php echo number_format($totalIncomerUnits, 1); ?> <span style="font-size:13px;font-weight:700;">kWh</span></div>
                <div class="loss-kpi-sub"><?php echo __('Total imported from WAPDA / LESCO'); ?></div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="loss-kpi-card">
                <div class="loss-kpi-label"><i class="fa fa-users text-success"></i> <?php echo __('Consumer Sub-Meters'); ?></div>
                <div class="loss-kpi-val" style="color:#059669;"><?php echo number_format($totalConsumerUnits, 1); ?> <span style="font-size:13px;font-weight:700;">kWh</span></div>
                <div class="loss-kpi-sub"><?php echo __('Billed residential units'); ?></div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="loss-kpi-card" style="border-color:#7dd3fc;background:#f0f9ff;">
                <div class="loss-kpi-label" style="color:#0369a1;"><i class="fa fa-tint text-info"></i> <?php echo __('Water Pumps Power'); ?></div>
                <div class="loss-kpi-val" style="color:#0284c7;"><?php echo number_format($totalWaterPumpUnits, 1); ?> <span style="font-size:13px;font-weight:700;">kWh</span></div>
                <div class="loss-kpi-sub" style="color:#0284c7;">PKR <?php echo number_format($totalWaterPumpCostPkr, 0); ?> (Deducted)</div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="loss-kpi-card">
                <div class="loss-kpi-label"><i class="fa fa-minus-circle text-danger"></i> <?php echo __('Net Unaccounted Loss'); ?></div>
                <div class="loss-kpi-val" style="color:#dc2626;"><?php echo number_format($totalLostUnits, 1); ?> <span style="font-size:13px;font-weight:700;">kWh</span></div>
                <div class="loss-kpi-sub">Loss: <strong style="color:<?php echo ($overallLossPercent > 12) ? '#dc2626' : '#d97706'; ?>;"><?php echo $overallLossPercent; ?>%</strong></div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="loss-kpi-card">
                <div class="loss-kpi-label"><i class="fa fa-money text-danger"></i> <?php echo __('Net Financial Deficit'); ?></div>
                <div class="loss-kpi-val" style="color:#dc2626;font-size:22px;">PKR <?php echo number_format($overallFinancialLossPkr, 0); ?></div>
                <div class="loss-kpi-sub">Calculated @ PKR <?php echo number_format($elecRate, 2); ?>/unit</div>
            </div>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- SECTION 1: TRANSFORMERS LINE LOSS & POWER THEFT RECON    -->
    <!-- ======================================================== -->
    <div class="panel panel-default" style="border:1.5px solid #cbd5e1;border-radius:10px;box-shadow:0 2px 6px rgba(0,0,0,0.04);overflow:hidden;margin-bottom:28px;">
        <div class="panel-heading" style="background:#0f172a;color:#ffffff;padding:14px 18px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;">
            <div style="font-weight:800;font-size:15px;">
                <i class="fa fa-flash text-warning"></i> <?php echo __('1. Main Electricity Transformers (Line Loss & Theft Reconciliation)'); ?>
            </div>
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <button type="button" class="btn btn-xs btn-success" style="font-weight:800;padding:4px 12px;" onclick="openAddFeederModal('transformer')">
                    <i class="fa fa-plus"></i> <?php echo __('+ Add Transformer'); ?>
                </button>
                <button type="button" class="btn btn-xs btn-warning" style="font-weight:800;color:#0f172a;padding:4px 12px;" onclick="openPropagateModal('electricity')">
                    <i class="fa fa-share-alt"></i> <?php echo __('⚡ Propagate Electricity Surcharge'); ?>
                </button>
                <?php if(!empty($postedUtilityMap['electricity'])){ ?>
                    <a href="<?php echo $this->Html->url(array('action' => 'delete_propagated_charge', $postedUtilityMap['electricity']['id'], '?' => array('society_id' => $societyId, 'month' => $month))); ?>" class="btn btn-xs btn-danger" onclick="return confirm('<?php echo __('Are you sure you want to DELETE and remove the Electricity Surcharge from resident bills?'); ?>');" style="font-weight:800;padding:4px 12px;">
                        <i class="fa fa-trash"></i> <?php echo __('Delete Surcharge (PKR '); ?><?php echo number_format($postedUtilityMap['electricity']['amount'], 0); ?>)
                    </a>
                <?php } ?>
            </div>
        </div>
        <div class="panel-body" style="padding:0;overflow-x:auto;">
            <table class="table table-bordered table-striped" style="margin:0;font-size:12.5px;vertical-align:middle;">
                <thead>
                    <tr style="background:#f8fafc;color:#1e293b;">
                        <th>#</th>
                        <th><?php echo __('Transformer Station'); ?></th>
                        <th><?php echo __('Master Meter / Capacity'); ?></th>
                        <th><?php echo __('Connected Villas'); ?></th>
                        <th><?php echo __('Villas Sub-Meters (kWh)'); ?></th>
                        <th style="background:#f0f9ff;color:#0369a1;"><?php echo __('Auxiliary Water Pumps (kWh)'); ?></th>
                        <th><?php echo __('WAPDA Incomer (kWh)'); ?></th>
                        <th><?php echo __('Net Lost (kWh)'); ?></th>
                        <th><?php echo __('Loss %'); ?></th>
                        <th><?php echo __('Financial Deficit'); ?></th>
                        <th><?php echo __('Theft Risk Status'); ?></th>
                        <th style="min-width:140px;text-align:center;"><?php echo __('Actions'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($transformerAuditList)){ ?>
                        <tr>
                            <td colspan="12" class="text-center text-muted" style="padding:28px;">
                                <?php echo __('No Main Transformers configured yet. Click "+ Add Transformer" to add your first master station.'); ?>
                            </td>
                        </tr>
                    <?php } else { $idx = 1; foreach($transformerAuditList as $fa){ 
                        $f = $fa['feeder'];
                        $r = $fa['reading'];
                        $isTheft = ($fa['status'] === 'CRITICAL THEFT ALERT');
                        $isMod = ($fa['status'] === 'MODERATE LOSS RISK');
                    ?>
                        <tr style="<?php if($isTheft) echo 'background:#fef2f2;'; ?>">
                            <td style="font-weight:800;color:#64748b;"><?php echo $idx++; ?></td>
                            <td>
                                <strong style="font-size:13.5px;color:#0f172a;display:block;">
                                    <i class="fa fa-flash text-warning"></i> <?php echo h($f['name']); ?>
                                </strong>
                                <span class="feeder-badge feeder-badge-elec"><?php echo h(!empty($f['code']) ? $f['code'] : 'TF'); ?></span>
                                <small class="text-muted" style="margin-left:4px;"><?php echo h(!empty($f['location_block']) ? $f['location_block'] : 'Main Grid'); ?></small>
                            </td>
                            <td>
                                <div><strong style="color:#0284c7;font-family:monospace;"><?php echo h(!empty($f['meter_number']) ? $f['meter_number'] : '-'); ?></strong></div>
                                <small class="text-muted"><?php echo h(!empty($f['capacity']) ? $f['capacity'] : '200 kVA'); ?></small>
                            </td>
                            <td>
                                <strong style="color:#0f172a;"><?php echo (int)$fa['sub_meters_count']; ?></strong> <?php echo __('villas'); ?>
                            </td>
                            <td>
                                <strong style="color:#059669;font-size:13.5px;"><?php echo number_format($fa['sub_meter_units'], 1); ?></strong>
                                <?php if($fa['export_units'] > 0){ ?>
                                    <div style="font-size:10.5px;color:#059669;">Solar Export: <?php echo number_format($fa['export_units'], 1); ?></div>
                                <?php } ?>
                            </td>
                            <td style="background:#f0f9ff;">
                                <?php if($fa['aux_pump_units'] > 0){ ?>
                                    <strong style="color:#0284c7;font-size:13px;"><?php echo number_format($fa['aux_pump_units'], 1); ?> kWh</strong>
                                    <span class="label label-info" style="font-size:10px;display:block;margin-top:2px;">&#10004; <?php echo __('Deducted Aux Load'); ?></span>
                                <?php } else { ?>
                                    <span class="text-muted">0.0</span>
                                <?php } ?>
                            </td>
                            <td>
                                <strong style="color:#0284c7;font-size:14px;"><?php echo number_format($fa['incomer_units'], 1); ?> kWh</strong>
                                <?php if(empty($r)){ ?>
                                    <span class="label label-default" style="font-size:10px;display:block;margin-top:2px;"><?php echo __('Estimated Baseline'); ?></span>
                                <?php } else { ?>
                                    <span class="label label-success" style="font-size:10px;display:block;margin-top:2px;">&#10004; <?php echo __('Recorded Index'); ?></span>
                                <?php } ?>
                            </td>
                            <td>
                                <strong style="color:#dc2626;font-size:13.5px;"><?php echo number_format($fa['lost_units'], 1); ?></strong>
                            </td>
                            <td>
                                <span class="badge" style="background:<?php echo $isTheft ? '#dc2626' : ($isMod ? '#d97706' : '#059669'); ?>;font-weight:900;font-size:12px;padding:4px 8px;">
                                    <?php echo $fa['loss_percent']; ?>%
                                </span>
                            </td>
                            <td>
                                <strong style="color:<?php echo ($fa['deficit_pkr'] > 0) ? '#dc2626' : '#059669'; ?>;font-size:13px;">
                                    PKR <?php echo number_format($fa['deficit_pkr'], 0); ?>
                                </strong>
                            </td>
                            <td>
                                <?php if($isTheft){ ?>
                                    <span class="label label-danger" style="font-size:11px;font-weight:800;padding:4px 8px;display:inline-block;">
                                        <i class="fa fa-warning"></i> <?php echo __('THEFT DETECTED'); ?>
                                    </span>
                                <?php } elseif($isMod){ ?>
                                    <span class="label label-warning" style="font-size:11px;font-weight:800;padding:4px 8px;display:inline-block;">
                                        <i class="fa fa-exclamation-circle"></i> <?php echo __('HIGH LOSS'); ?>
                                    </span>
                                <?php } else { ?>
                                    <span class="label label-success" style="font-size:11px;font-weight:800;padding:4px 8px;display:inline-block;">
                                        <i class="fa fa-check"></i> <?php echo __('NORMAL'); ?>
                                    </span>
                                <?php } ?>
                            </td>
                            <td style="text-align:center;">
                                <button type="button" class="btn btn-xs btn-primary" onclick="openReadingModal(<?php echo htmlspecialchars(json_encode($f)); ?>, <?php echo htmlspecialchars(json_encode($r)); ?>)" title="<?php echo __('Enter / Update Monthly Reading'); ?>" style="font-weight:700;">
                                    <i class="fa fa-pencil"></i> <?php echo __('Reading'); ?>
                                </button>
                                <button type="button" class="btn btn-xs btn-default" onclick="openEditFeederModal(<?php echo htmlspecialchars(json_encode($f)); ?>)" title="<?php echo __('Edit Station Details'); ?>">
                                    <i class="fa fa-cog"></i>
                                </button>
                                <a href="<?php echo $this->Html->url(array('action' => 'delete_feeder', $f['id'], '?' => array('society_id' => $societyId, 'month' => $month))); ?>" class="btn btn-xs btn-danger" onclick="return confirm('<?php echo __('Delete this transformer station?'); ?>');" title="<?php echo __('Delete Station'); ?>">
                                    <i class="fa fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php } } ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- SECTION 2: MULTIPLE WATER PUMPS & COST PROPAGATION       -->
    <!-- ======================================================== -->
    <div class="panel panel-info" style="border:1.5px solid #0284c7;border-radius:10px;box-shadow:0 3px 10px rgba(2,132,199,0.08);overflow:hidden;margin-bottom:28px;">
        <div class="panel-heading" style="background:#0284c7;color:#ffffff;padding:14px 18px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
            <div>
                <strong style="font-size:16px;">
                    <i class="fa fa-tint"></i> <?php echo __('2. Main Water Pumps, Tube-Wells & Bulk Supply Operations'); ?>
                </strong>
                <span style="font-size:12px;opacity:0.9;display:block;margin-top:2px;">
                    <?php echo __('Track multi-bore electric consumption, water production volume, and propagate costs fairly across all resident bills.'); ?>
                </span>
            </div>
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <button type="button" class="btn btn-success btn-sm" style="font-weight:800;" onclick="openAddFeederModal('water_pump')">
                    <i class="fa fa-plus-circle"></i> <?php echo __('+ Add Water Pump / Tube-Well'); ?>
                </button>
                <button type="button" class="btn btn-warning btn-sm" style="font-weight:800;color:#0f172a;" onclick="openPropagateModal('water')">
                    <i class="fa fa-share-alt"></i> <?php echo __('⚡ Propagate Water Cost to Bills'); ?>
                </button>
                <?php if(!empty($postedUtilityMap['water'])){ ?>
                    <a href="<?php echo $this->Html->url(array('action' => 'delete_propagated_charge', $postedUtilityMap['water']['id'], '?' => array('society_id' => $societyId, 'month' => $month))); ?>" class="btn btn-danger btn-sm" onclick="return confirm('<?php echo __('Are you sure you want to DELETE and remove the Water Charge from resident bills?'); ?>');" style="font-weight:800;">
                        <i class="fa fa-trash"></i> <?php echo __('Delete Water (PKR '); ?><?php echo number_format($postedUtilityMap['water']['amount'], 0); ?>)
                    </a>
                <?php } ?>
            </div>
        </div>

        <!-- 4 Water Operating KPI Cards -->
        <div style="background:#f0f9ff;padding:16px;border-bottom:1px solid #bae6fd;">
            <div class="row">
                <div class="col-md-3">
                    <div class="water-kpi-card">
                        <div class="loss-kpi-label" style="color:#0369a1;"><i class="fa fa-bar-chart"></i> <?php echo __('Total Bulk Water Pumped'); ?></div>
                        <div class="loss-kpi-val" style="color:#0284c7;"><?php echo number_format($totalWaterFlowUnits, 0); ?> <span style="font-size:12px;font-weight:700;">Gallons / m³</span></div>
                        <div class="loss-kpi-sub"><?php echo __('Total supply into overhead reservoirs'); ?></div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="water-kpi-card">
                        <div class="loss-kpi-label" style="color:#0369a1;"><i class="fa fa-bolt"></i> <?php echo __('Total Pump Electricity Power'); ?></div>
                        <div class="loss-kpi-val" style="color:#0284c7;"><?php echo number_format($totalWaterPumpUnits, 1); ?> <span style="font-size:12px;font-weight:700;">kWh</span></div>
                        <div class="loss-kpi-sub"><?php echo __('Consumed by 3-phase pump motors'); ?></div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="water-kpi-card">
                        <div class="loss-kpi-label" style="color:#0369a1;"><i class="fa fa-money"></i> <?php echo __('Total Pump Electric Cost'); ?></div>
                        <div class="loss-kpi-val" style="color:#0369a1;font-size:22px;">PKR <?php echo number_format($totalWaterPumpCostPkr, 0); ?></div>
                        <div class="loss-kpi-sub">@ PKR <?php echo number_format($elecRate, 2); ?>/unit</div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="water-kpi-card" style="background:#fefce8;border-color:#fde047;">
                        <div class="loss-kpi-label" style="color:#854d0e;"><i class="fa fa-calculator"></i> <?php echo __('Propagated Cost Per Villa'); ?></div>
                        <div class="loss-kpi-val" style="color:#a16207;font-size:22px;">PKR <?php echo number_format($waterCostPerVilla, 0); ?> <span style="font-size:12px;font-weight:700;">/ villa</span></div>
                        <div class="loss-kpi-sub" style="color:#854d0e;">Active Water Charge: <strong>PKR <?php echo number_format($currentWaterChargeAmt, 0); ?></strong></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="panel-body" style="padding:0;overflow-x:auto;">
            <table class="table table-bordered table-striped" style="margin:0;font-size:12.5px;vertical-align:middle;">
                <thead>
                    <tr style="background:#f8fafc;color:#1e293b;">
                        <th>#</th>
                        <th><?php echo __('Pump / Tube-Well Station'); ?></th>
                        <th><?php echo __('Motor Rating & Parent Transformer'); ?></th>
                        <th><?php echo __('Electric Sub-Meter No'); ?></th>
                        <th><?php echo __('Flow Meter No'); ?></th>
                        <th><?php echo __('Electricity Consumed (kWh)'); ?></th>
                        <th><?php echo __('Monthly Electric Cost'); ?></th>
                        <th><?php echo __('Water Pumped (Gallons/m³)'); ?></th>
                        <th><?php echo __('Running Hours'); ?></th>
                        <th><?php echo __('Efficiency (Gal/kWh)'); ?></th>
                        <th style="min-width:140px;text-align:center;"><?php echo __('Actions'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($waterPumpAuditList)){ ?>
                        <tr>
                            <td colspan="11" class="text-center text-muted" style="padding:28px;">
                                <?php echo __('No Main Water Pumps configured yet. Click "+ Add Water Pump / Tube-Well" to add your community bore or booster station.'); ?>
                            </td>
                        </tr>
                    <?php } else { $wIdx = 1; foreach($waterPumpAuditList as $wpa){ 
                        $f = $wpa['feeder'];
                        $r = $wpa['reading'];
                    ?>
                        <tr>
                            <td style="font-weight:800;color:#64748b;"><?php echo $wIdx++; ?></td>
                            <td>
                                <strong style="font-size:13.5px;color:#0369a1;display:block;">
                                    <i class="fa fa-tint text-info"></i> <?php echo h($f['name']); ?>
                                </strong>
                                <span class="feeder-badge feeder-badge-water"><?php echo h(!empty($f['code']) ? $f['code'] : 'PUMP'); ?></span>
                                <small class="text-muted" style="margin-left:4px;"><?php echo h(!empty($f['location_block']) ? $f['location_block'] : 'Water Works'); ?></small>
                            </td>
                            <td>
                                <div><strong style="color:#0f172a;"><?php echo h(!empty($f['pump_hp']) ? $f['pump_hp'] : (!empty($f['capacity']) ? $f['capacity'] : '50 HP')); ?></strong></div>
                                <small class="text-muted"><i class="fa fa-plug text-warning"></i> <?php echo h($wpa['connected_transformer_name']); ?></small>
                            </td>
                            <td>
                                <strong style="font-family:monospace;color:#0284c7;"><?php echo h(!empty($f['meter_number']) ? $f['meter_number'] : '-'); ?></strong>
                            </td>
                            <td>
                                <span style="font-family:monospace;color:#059669;font-weight:700;"><?php echo h(!empty($f['flow_meter_no']) ? $f['flow_meter_no'] : '-'); ?></span>
                            </td>
                            <td>
                                <strong style="color:#0284c7;font-size:13.5px;"><?php echo number_format($wpa['units_consumed'], 1); ?> kWh</strong>
                            </td>
                            <td>
                                <strong style="color:#0369a1;font-size:13.5px;">PKR <?php echo number_format($wpa['operating_cost_pkr'], 0); ?></strong>
                            </td>
                            <td>
                                <strong style="color:#059669;font-size:13.5px;"><?php echo number_format($wpa['flow_units'], 0); ?></strong>
                            </td>
                            <td>
                                <strong style="color:#475569;"><?php echo number_format($wpa['operating_hours'], 1); ?> hrs</strong>
                            </td>
                            <td>
                                <span class="badge" style="background:#0284c7;font-weight:800;"><?php echo $wpa['efficiency']; ?></span>
                            </td>
                            <td style="text-align:center;">
                                <button type="button" class="btn btn-xs btn-info" onclick="openReadingModal(<?php echo htmlspecialchars(json_encode($f)); ?>, <?php echo htmlspecialchars(json_encode($r)); ?>)" title="<?php echo __('Enter / Update Monthly Pump Log'); ?>" style="font-weight:700;">
                                    <i class="fa fa-tachometer"></i> <?php echo __('Log Reading'); ?>
                                </button>
                                <button type="button" class="btn btn-xs btn-default" onclick="openEditFeederModal(<?php echo htmlspecialchars(json_encode($f)); ?>)" title="<?php echo __('Edit Pump Details'); ?>">
                                    <i class="fa fa-cog"></i>
                                </button>
                                <a href="<?php echo $this->Html->url(array('action' => 'delete_feeder', $f['id'], '?' => array('society_id' => $societyId, 'month' => $month))); ?>" class="btn btn-xs btn-danger" onclick="return confirm('<?php echo __('Delete this water pump station?'); ?>');" title="<?php echo __('Delete Pump'); ?>">
                                    <i class="fa fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php } } ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- SECTION 3: GAS MASTER INCOMERS & PROPAGATION             -->
    <!-- ======================================================== -->
    <div class="panel panel-danger" style="border:1.5px solid #ef4444;border-radius:10px;box-shadow:0 3px 10px rgba(239,68,68,0.08);overflow:hidden;margin-bottom:28px;">
        <div class="panel-heading" style="background:#b91c1c;color:#ffffff;padding:14px 18px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
            <div>
                <strong style="font-size:16px;">
                    <i class="fa fa-fire"></i> <?php echo __('3. Gas Master Incomers & Central Gas Supply Operations'); ?>
                </strong>
                <span style="font-size:12px;opacity:0.9;display:block;margin-top:2px;">
                    <?php echo __('Track SNGPL bulk meter incomers / central LPG tanks, monitor line leakage, and propagate fair share to resident bills.'); ?>
                </span>
            </div>
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <button type="button" class="btn btn-success btn-sm" style="font-weight:800;" onclick="openAddFeederModal('gas_station')">
                    <i class="fa fa-plus-circle"></i> <?php echo __('+ Add Gas Incomer'); ?>
                </button>
                <button type="button" class="btn btn-warning btn-sm" style="font-weight:800;color:#0f172a;" onclick="openPropagateModal('gas')">
                    <i class="fa fa-share-alt"></i> <?php echo __('⚡ Propagate Gas Cost to Bills'); ?>
                </button>
                <?php if(!empty($postedUtilityMap['gas'])){ ?>
                    <a href="<?php echo $this->Html->url(array('action' => 'delete_propagated_charge', $postedUtilityMap['gas']['id'], '?' => array('society_id' => $societyId, 'month' => $month))); ?>" class="btn btn-danger btn-sm" onclick="return confirm('<?php echo __('Are you sure you want to DELETE and remove the Gas Charge from resident bills?'); ?>');" style="font-weight:800;">
                        <i class="fa fa-trash"></i> <?php echo __('Delete Gas (PKR '); ?><?php echo number_format($postedUtilityMap['gas']['amount'], 0); ?>)
                    </a>
                <?php } ?>
            </div>
        </div>

        <!-- 3 Gas Operating KPI Cards -->
        <div style="background:#fff1f2;padding:16px;border-bottom:1px solid #fecaca;">
            <div class="row">
                <div class="col-md-4">
                    <div class="gas-kpi-card">
                        <div class="loss-kpi-label" style="color:#b91c1c;"><i class="fa fa-fire"></i> <?php echo __('Total Gas Inflow (Bulk SNGPL / LPG)'); ?></div>
                        <div class="loss-kpi-val" style="color:#b91c1c;"><?php echo number_format($totalGasUnits, 1); ?> <span style="font-size:12px;font-weight:700;">Units / MMBTU</span></div>
                        <div class="loss-kpi-sub"><?php echo __('Total incoming gas measured at manifolds'); ?></div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="gas-kpi-card">
                        <div class="loss-kpi-label" style="color:#b91c1c;"><i class="fa fa-money"></i> <?php echo __('Total Gas Operating Cost'); ?></div>
                        <div class="loss-kpi-val" style="color:#b91c1c;font-size:22px;">PKR <?php echo number_format($totalGasCostPkr, 0); ?></div>
                        <div class="loss-kpi-sub">Calculated @ PKR <?php echo number_format($gasRate, 2); ?>/unit</div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="gas-kpi-card" style="background:#fefce8;border-color:#fde047;">
                        <div class="loss-kpi-label" style="color:#854d0e;"><i class="fa fa-calculator"></i> <?php echo __('Propagated Gas Cost Per Villa'); ?></div>
                        <div class="loss-kpi-val" style="color:#a16207;font-size:22px;">PKR <?php echo number_format($gasCostPerVilla, 0); ?> <span style="font-size:12px;font-weight:700;">/ villa</span></div>
                        <div class="loss-kpi-sub" style="color:#854d0e;">Active Gas Charge: <strong>PKR <?php echo number_format($currentGasChargeAmt, 0); ?></strong></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="panel-body" style="padding:0;overflow-x:auto;">
            <table class="table table-bordered table-striped" style="margin:0;font-size:12.5px;vertical-align:middle;">
                <thead>
                    <tr style="background:#f8fafc;color:#1e293b;">
                        <th>#</th>
                        <th><?php echo __('Gas Incomer / Manifold'); ?></th>
                        <th><?php echo __('Capacity / Rating'); ?></th>
                        <th><?php echo __('Master Meter / SNGPL Reference'); ?></th>
                        <th><?php echo __('Incomer Consumption (Units/MMBTU)'); ?></th>
                        <th><?php echo __('Monthly Gas Cost'); ?></th>
                        <th style="min-width:140px;text-align:center;"><?php echo __('Actions'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($gasAuditList)){ ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted" style="padding:28px;">
                                <?php echo __('No Main Gas Incomers configured yet. Click "+ Add Gas Incomer" to add your SNGPL master connection or central LPG manifold.'); ?>
                            </td>
                        </tr>
                    <?php } else { $gIdx = 1; foreach($gasAuditList as $ga){ 
                        $f = $ga['feeder'];
                        $r = $ga['reading'];
                    ?>
                        <tr>
                            <td style="font-weight:800;color:#64748b;"><?php echo $gIdx++; ?></td>
                            <td>
                                <strong style="font-size:13.5px;color:#b91c1c;display:block;">
                                    <i class="fa fa-fire text-danger"></i> <?php echo h($f['name']); ?>
                                </strong>
                                <span class="feeder-badge feeder-badge-gas"><?php echo h(!empty($f['code']) ? $f['code'] : 'GAS'); ?></span>
                                <small class="text-muted" style="margin-left:4px;"><?php echo h(!empty($f['location_block']) ? $f['location_block'] : 'Central Manifold'); ?></small>
                            </td>
                            <td>
                                <strong style="color:#0f172a;"><?php echo h(!empty($f['capacity']) ? $f['capacity'] : 'Standard Inflow'); ?></strong>
                            </td>
                            <td>
                                <strong style="font-family:monospace;color:#b91c1c;"><?php echo h(!empty($f['meter_number']) ? $f['meter_number'] : '-'); ?></strong>
                            </td>
                            <td>
                                <strong style="color:#b91c1c;font-size:14px;"><?php echo number_format($ga['units_consumed'], 1); ?></strong>
                            </td>
                            <td>
                                <strong style="color:#b91c1c;font-size:14px;">PKR <?php echo number_format($ga['operating_cost_pkr'], 0); ?></strong>
                            </td>
                            <td style="text-align:center;">
                                <button type="button" class="btn btn-xs btn-danger" onclick="openReadingModal(<?php echo htmlspecialchars(json_encode($f)); ?>, <?php echo htmlspecialchars(json_encode($r)); ?>)" title="<?php echo __('Enter / Update Monthly Gas Reading'); ?>" style="font-weight:700;">
                                    <i class="fa fa-pencil"></i> <?php echo __('Reading'); ?>
                                </button>
                                <button type="button" class="btn btn-xs btn-default" onclick="openEditFeederModal(<?php echo htmlspecialchars(json_encode($f)); ?>)" title="<?php echo __('Edit Station Details'); ?>">
                                    <i class="fa fa-cog"></i>
                                </button>
                                <a href="<?php echo $this->Html->url(array('action' => 'delete_feeder', $f['id'], '?' => array('society_id' => $societyId, 'month' => $month))); ?>" class="btn btn-xs btn-danger" onclick="return confirm('<?php echo __('Delete this gas station?'); ?>');" title="<?php echo __('Delete Station'); ?>">
                                    <i class="fa fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php } } ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- ======================================================== -->
<!-- MODAL 1: ADD / EDIT STATION (TRANSFORMER / PUMP / GAS)   -->
<!-- ======================================================== -->
<div class="modal fade" id="modalFeederForm" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:12px;overflow:hidden;">
            <?php echo $this->Form->create(false, array('url' => array('action' => 'save_feeder', '?' => array('society_id' => $societyId, 'month' => $month)), 'class' => 'form-horizontal')); ?>
            <input type="hidden" name="data[BillingFeeder][id]" id="feederFormId" value="" />
            <input type="hidden" name="data[BillingFeeder][society_id]" value="<?php echo (int)$societyId; ?>" />

            <div class="modal-header" style="background:#0f172a;color:#fff;padding:14px 20px;">
                <button type="button" class="close" data-dismiss="modal" style="color:#fff;opacity:0.8;">&times;</button>
                <h4 class="modal-title" id="feederModalTitle" style="font-weight:800;font-size:16px;">
                    <i class="fa fa-flash text-warning"></i> <?php echo __('Add Main Station'); ?>
                </h4>
            </div>

            <div class="modal-body" style="padding:22px;">
                <div class="form-group">
                    <label class="col-sm-4 control-label"><?php echo __('Station Category'); ?> <span class="text-danger">*</span></label>
                    <div class="col-sm-8">
                        <select name="data[BillingFeeder][feeder_category]" id="feederCategory" class="form-control" style="font-weight:bold;" onchange="toggleFeederCategoryFields()">
                            <option value="transformer"><?php echo __('Main Transformer (Electricity Incomer)'); ?></option>
                            <option value="water_pump"><?php echo __('Water Pump / Tube-Well / Booster Station'); ?></option>
                            <option value="gas_station"><?php echo __('Gas Master Incomer / Central LPG Tank'); ?></option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-4 control-label"><?php echo __('Station Name'); ?> <span class="text-danger">*</span></label>
                    <div class="col-sm-8">
                        <input type="text" name="data[BillingFeeder][name]" id="feederName" required placeholder="e.g. Transformer T-01 North, Tube-Well #1, or SNGPL Incomer" class="form-control" style="font-weight:bold;" />
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-4 control-label"><?php echo __('Station Code'); ?></label>
                    <div class="col-sm-8">
                        <input type="text" name="data[BillingFeeder][code]" id="feederCode" placeholder="e.g. TF-01, PUMP-01, or GAS-01" class="form-control" />
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-4 control-label"><?php echo __('Utility Type'); ?></label>
                    <div class="col-sm-8">
                        <select name="data[BillingFeeder][utility_type]" id="feederUtilityType" class="form-control" style="font-weight:bold;">
                            <option value="Electricity">Electricity</option>
                            <option value="Water">Water</option>
                            <option value="Gas">Gas</option>
                        </select>
                    </div>
                </div>

                <!-- Water Pump Specific Fields -->
                <div id="waterPumpSpecificFields" style="display:none;background:#f0f9ff;border:1px solid #bae6fd;border-radius:8px;padding:12px;margin-bottom:15px;">
                    <div class="form-group" style="margin-bottom:12px;">
                        <label class="col-sm-4 control-label" style="color:#0369a1;"><?php echo __('Motor Power (HP/kW)'); ?></label>
                        <div class="col-sm-8">
                            <input type="text" name="data[BillingFeeder][pump_hp]" id="feederPumpHp" placeholder="e.g. 50 HP or 37 kW" class="form-control" />
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom:12px;">
                        <label class="col-sm-4 control-label" style="color:#0369a1;"><?php echo __('Parent Transformer'); ?></label>
                        <div class="col-sm-8">
                            <select name="data[BillingFeeder][connected_feeder_id]" id="feederConnectedTransformer" class="form-control" style="font-weight:bold;">
                                <option value=""><?php echo __('-- None / Direct Grid Incomer --'); ?></option>
                                <?php foreach($transformers as $tr){ ?>
                                    <option value="<?php echo $tr['BillingFeeder']['id']; ?>">
                                        <?php echo h($tr['BillingFeeder']['name']); ?> (<?php echo h(!empty($tr['BillingFeeder']['code']) ? $tr['BillingFeeder']['code'] : 'TF'); ?>)
                                    </option>
                                <?php } ?>
                            </select>
                            <small class="text-muted"><?php echo __('Selecting parent transformer automatically deducts pump electricity from transformer line loss.'); ?></small>
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom:0;">
                        <label class="col-sm-4 control-label" style="color:#0369a1;"><?php echo __('Flow Meter Serial #'); ?></label>
                        <div class="col-sm-8">
                            <input type="text" name="data[BillingFeeder][flow_meter_no]" id="feederFlowMeterNo" placeholder="e.g. FLW-TW01-881" class="form-control" />
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-4 control-label"><?php echo __('Meter / Consumer Reference #'); ?></label>
                    <div class="col-sm-8">
                        <input type="text" name="data[BillingFeeder][meter_number]" id="feederMeterNo" placeholder="e.g. LESCO-MTR-98210, SNGPL-MTR-44021" class="form-control" style="font-weight:bold;color:#0284c7;" />
                    </div>
                </div>

                <div class="form-group" id="capacityGroup">
                    <label class="col-sm-4 control-label"><?php echo __('Capacity / Rating'); ?></label>
                    <div class="col-sm-8">
                        <input type="text" name="data[BillingFeeder][capacity]" id="feederCapacity" placeholder="e.g. 200 kVA, 50 HP, or 1000 MMBTU/hr" class="form-control" />
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-4 control-label"><?php echo __('Location / Sector Block'); ?></label>
                    <div class="col-sm-8">
                        <input type="text" name="data[BillingFeeder][location_block]" id="feederLocation" placeholder="e.g. Awami Block North, Central Manifold" class="form-control" />
                    </div>
                </div>
            </div>

            <div class="modal-footer" style="background:#f8fafc;padding:12px 20px;">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Cancel'); ?></button>
                <button type="submit" class="btn btn-success" style="font-weight:800;padding:8px 24px;">
                    <i class="fa fa-save"></i> <?php echo __('Save Station'); ?>
                </button>
            </div>
            <?php echo $this->Form->end(); ?>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL 2: ENTER MONTHLY INCOMER / PUMP CONSUMPTION LOG     -->
<!-- ======================================================== -->
<div class="modal fade" id="modalReadingForm" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:12px;overflow:hidden;">
            <?php echo $this->Form->create(false, array('url' => array('action' => 'save_feeder_reading'), 'class' => 'form-horizontal')); ?>
            <input type="hidden" name="feeder_id" id="readingFeederId" value="" />
            <input type="hidden" name="society_id" value="<?php echo (int)$societyId; ?>" />
            <input type="hidden" name="reading_month" value="<?php echo h($month); ?>" />

            <div class="modal-header" style="background:#0284c7;color:#fff;padding:14px 20px;">
                <button type="button" class="close" data-dismiss="modal" style="color:#fff;opacity:0.8;">&times;</button>
                <h4 class="modal-title" style="font-weight:800;font-size:16px;">
                    <i class="fa fa-tachometer"></i> <?php echo __('Record Monthly Consumption & Index'); ?>
                </h4>
            </div>

            <div class="modal-body" style="padding:22px;">
                <div style="background:#f0f9ff;border:1px solid #bae6fd;border-radius:8px;padding:12px 16px;margin-bottom:18px;">
                    <div style="font-weight:800;font-size:14px;color:#0369a1;" id="readingFeederName"></div>
                    <div style="font-size:12px;color:#0c4a6e;margin-top:2px;">
                        Month: <strong><?php echo date('F Y', strtotime($month . '-01')); ?></strong> | Meter / Ref: <strong id="readingMeterLabel"></strong>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-4 control-label"><?php echo __('Previous Reading Index'); ?></label>
                    <div class="col-sm-8">
                        <input type="number" step="0.1" name="previous_reading" id="feederPrevReading" class="form-control" style="font-weight:bold;" />
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-4 control-label"><?php echo __('Current Reading Index'); ?></label>
                    <div class="col-sm-8">
                        <input type="number" step="0.1" name="current_reading" id="feederCurrReading" class="form-control" style="font-weight:bold;color:#0284c7;" />
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-4 control-label"><?php echo __('Multiplier (MF)'); ?></label>
                    <div class="col-sm-8">
                        <input type="number" step="0.0001" name="multiplier" id="feederMultiplier" value="1.0" class="form-control" />
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-4 control-label"><?php echo __('Consumed Units'); ?> <span class="text-danger">*</span></label>
                    <div class="col-sm-8">
                        <input type="number" step="0.1" name="units_consumed" id="feederUnits" class="form-control" style="font-size:16px;font-weight:900;color:#059669;" placeholder="Auto-calculated or enter directly" />
                        <small class="text-muted"><?php echo __('Auto-calculates as (Current - Previous) * Multiplier, or enter bill units directly.'); ?></small>
                    </div>
                </div>

                <!-- Water Pump Production Fields -->
                <div id="pumpReadingFields" style="display:none;background:#f8fafc;border:1.5px solid #cbd5e1;border-radius:8px;padding:14px;margin-bottom:15px;">
                    <div class="form-group" style="margin-bottom:12px;">
                        <label class="col-sm-4 control-label" style="color:#0369a1;"><?php echo __('Water Pumped (Gallons/m³)'); ?></label>
                        <div class="col-sm-8">
                            <input type="number" step="1" name="flow_units" id="feederFlowUnits" placeholder="e.g. 450000" class="form-control" style="font-weight:bold;color:#059669;" />
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom:12px;">
                        <label class="col-sm-4 control-label" style="color:#0369a1;"><?php echo __('Pump Running Hours'); ?></label>
                        <div class="col-sm-8">
                            <input type="number" step="0.5" name="operating_hours" id="feederOpHours" placeholder="e.g. 210" class="form-control" />
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom:0;">
                        <label class="col-sm-4 control-label" style="color:#0369a1;"><?php echo __('Total Operating Cost (PKR)'); ?></label>
                        <div class="col-sm-8">
                            <input type="number" step="1" name="operating_cost_pkr" id="feederOpCost" placeholder="Auto-calculates from units * rate or enter direct" class="form-control" style="font-weight:bold;color:#0369a1;" />
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-4 control-label"><?php echo __('Inspector Notes'); ?></label>
                    <div class="col-sm-8">
                        <textarea name="notes" id="feederNotes" rows="2" class="form-control" placeholder="Optional notes..."></textarea>
                    </div>
                </div>
            </div>

            <div class="modal-footer" style="background:#f8fafc;padding:12px 20px;">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Cancel'); ?></button>
                <button type="submit" class="btn btn-primary" style="font-weight:800;padding:8px 24px;">
                    <i class="fa fa-save"></i> <?php echo __('Save Consumption Record'); ?>
                </button>
            </div>
            <?php echo $this->Form->end(); ?>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL 3: PROPAGATE UTILITY COST TO RESIDENT BILLS        -->
<!-- ======================================================== -->
<div class="modal fade" id="modalPropagateUtility" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:12px;overflow:hidden;">
            <?php echo $this->Form->create(false, array('url' => array('action' => 'propagate_utility_charge'), 'class' => 'form-horizontal')); ?>
            <input type="hidden" name="society_id" value="<?php echo (int)$societyId; ?>" />
            <input type="hidden" name="month" value="<?php echo h($month); ?>" />

            <div class="modal-header" style="background:#d97706;color:#fff;padding:14px 20px;">
                <button type="button" class="close" data-dismiss="modal" style="color:#fff;opacity:0.8;">&times;</button>
                <h4 class="modal-title" style="font-weight:800;font-size:16px;">
                    <i class="fa fa-share-alt"></i> <?php echo __('Propagate Utility Cost to Resident Monthly Bills'); ?>
                </h4>
            </div>

            <div class="modal-body" style="padding:22px;">
                <div class="form-group">
                    <label class="col-sm-4 control-label"><?php echo __('Select Utility'); ?> <span class="text-danger">*</span></label>
                    <div class="col-sm-8">
                        <select name="utility_category" id="propagateUtilitySelect" class="form-control" style="font-weight:bold;height:38px;" onchange="updatePropagateForm()">
                            <option value="water"><?php echo __('💧 Water (Tube-Wells & Supply Operations)'); ?></option>
                            <option value="electricity"><?php echo __('⚡ Electricity (Line Loss Deficit & Streetlights)'); ?></option>
                            <option value="gas"><?php echo __('🔥 Gas (Bulk SNGPL / Central LPG Supply)'); ?></option>
                        </select>
                    </div>
                </div>

                <!-- Alert if this Utility is Already Posted for this period -->
                <div id="propagatePostedAlert" style="display:none;background:#fef2f2;border:1.5px solid #ef4444;border-radius:8px;padding:12px 16px;margin-bottom:16px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;">
                        <div>
                            <strong style="color:#b91c1c;font-size:13px;display:block;">
                                <i class="fa fa-exclamation-triangle"></i> <?php echo __('Currently Posted on Resident Bills'); ?>
                            </strong>
                            <span id="propagatePostedText" style="font-size:12px;color:#991b1b;"></span>
                        </div>
                        <a id="propagateModalDeleteBtn" href="#" class="btn btn-sm btn-danger" style="font-weight:800;" onclick="return confirm('<?php echo __('Are you sure you want to un-post and DELETE this charge from resident bills?'); ?>');">
                            <i class="fa fa-trash"></i> <?php echo __('Delete / Un-post Now'); ?>
                        </a>
                    </div>
                </div>

                <div id="propagateContextBox" style="background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:14px;margin-bottom:18px;">
                    <span id="propagateExplanation" style="font-size:12.5px;color:#78350f;line-height:1.5;display:block;"></span>
                </div>

                <div class="row" style="margin-bottom:16px;">
                    <div class="col-xs-6">
                        <div style="background:#f8fafc;padding:12px;border:1px solid #cbd5e1;border-radius:6px;text-align:center;">
                            <span style="font-size:11px;color:#64748b;font-weight:700;text-transform:uppercase;"><?php echo __('Total Monthly Cost'); ?></span>
                            <div id="propagateTotalCostDisplay" style="font-size:18px;font-weight:900;color:#0284c7;">PKR 0</div>
                        </div>
                    </div>
                    <div class="col-xs-6">
                        <div style="background:#f8fafc;padding:12px;border:1px solid #cbd5e1;border-radius:6px;text-align:center;">
                            <span style="font-size:11px;color:#64748b;font-weight:700;text-transform:uppercase;"><?php echo __('Active Villas Count'); ?></span>
                            <div style="font-size:18px;font-weight:900;color:#059669;"><?php echo $activePropertiesCount; ?> <?php echo __('Units'); ?></div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-5 control-label"><?php echo __('Calculated Fair Share / Villa'); ?></label>
                    <div class="col-sm-7">
                        <p id="propagateFairShareDisplay" class="form-control-static" style="font-size:16px;font-weight:900;color:#0369a1;">
                            PKR 0
                        </p>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-5 control-label"><?php echo __('Propagated Amount (PKR)'); ?> <span class="text-danger">*</span></label>
                    <div class="col-sm-7">
                        <input type="number" step="1" name="charge_amount" id="propagateChargeAmount" value="0" class="form-control" style="font-size:16px;font-weight:900;color:#059669;" required />
                        <small class="text-muted"><?php echo __('You can accept exact split or round to a clean monthly fee (e.g. 1000 or 1500).'); ?></small>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-5 control-label"><?php echo __('Billing Charge Label'); ?></label>
                    <div class="col-sm-7">
                        <input type="text" name="charge_title" id="propagateChargeTitle" value="Water Supply & Tube-well Operations" class="form-control" style="font-weight:bold;" />
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-5 control-label"><?php echo __('Propagation Mode'); ?></label>
                    <div class="col-sm-7">
                        <label style="font-weight:600;display:block;">
                            <input type="radio" name="update_existing" value="1" checked /> <?php echo __('Update active recurring charge for all residents (Recommended)'); ?>
                        </label>
                        <label style="font-weight:600;display:block;">
                            <input type="radio" name="update_existing" value="0" /> <?php echo __('Create new monthly specific charge'); ?>
                        </label>
                    </div>
                </div>
            </div>

            <div class="modal-footer" style="background:#f8fafc;padding:12px 20px;display:flex;justify-content:space-between;align-items:center;">
                <div>
                    <a id="propagateModalFooterDeleteBtn" href="#" class="btn btn-danger" style="display:none;font-weight:800;" onclick="return confirm('<?php echo __('Are you sure you want to un-post and DELETE this charge from resident bills?'); ?>');">
                        <i class="fa fa-trash"></i> <?php echo __('Delete / Remove Charge'); ?>
                    </a>
                </div>
                <div style="display:flex;gap:8px;">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Cancel'); ?></button>
                    <button type="submit" class="btn btn-warning" style="font-weight:800;color:#1e293b;padding:8px 24px;">
                        <i class="fa fa-check-circle"></i> <?php echo __('Confirm & Propagate to Bills'); ?>
                    </button>
                </div>
            </div>
            <?php echo $this->Form->end(); ?>
        </div>
    </div>
</div>

<script type="text/javascript">
var elecRateGlobal = <?php echo json_encode($elecRate); ?>;
var gasRateGlobal = <?php echo json_encode($gasRate); ?>;

var baseDeleteUrl = <?php echo json_encode($this->Html->url(array('action' => 'delete_propagated_charge'))); ?>;
var currentSocId = <?php echo json_encode($societyId); ?>;
var currentMonth = <?php echo json_encode($month); ?>;

var utilityData = {
    water: {
        totalCost: <?php echo json_encode($totalWaterPumpCostPkr); ?>,
        perVilla: <?php echo json_encode($waterCostPerVilla); ?>,
        activeAmount: <?php echo json_encode($currentWaterChargeAmt); ?>,
        defaultTitle: 'Water Supply & Tube-well Operations',
        explanation: 'Apportions the monthly electric power and operating costs of community tube-wells/pumps across all occupied homes.',
        isPosted: <?php echo !empty($postedUtilityMap['water']) ? 'true' : 'false'; ?>,
        postedId: <?php echo !empty($postedUtilityMap['water']['id']) ? (int)$postedUtilityMap['water']['id'] : 0; ?>,
        postedAmount: <?php echo !empty($postedUtilityMap['water']['amount']) ? (float)$postedUtilityMap['water']['amount'] : 0; ?>,
        postedTitle: <?php echo !empty($postedUtilityMap['water']['title']) ? json_encode($postedUtilityMap['water']['title']) : "''"; ?>
    },
    electricity: {
        totalCost: <?php echo json_encode($overallFinancialLossPkr); ?>,
        perVilla: <?php echo json_encode($elecDeficitPerVilla); ?>,
        activeAmount: <?php echo json_encode($currentElecChargeAmt); ?>,
        defaultTitle: 'Common Electricity & Line Loss Surcharge',
        explanation: 'Recovers the financial deficit from unmetered common area electricity (streetlights, gates, mosque, fountains) and transformer line losses across all villas.',
        isPosted: <?php echo !empty($postedUtilityMap['electricity']) ? 'true' : 'false'; ?>,
        postedId: <?php echo !empty($postedUtilityMap['electricity']['id']) ? (int)$postedUtilityMap['electricity']['id'] : 0; ?>,
        postedAmount: <?php echo !empty($postedUtilityMap['electricity']['amount']) ? (float)$postedUtilityMap['electricity']['amount'] : 0; ?>,
        postedTitle: <?php echo !empty($postedUtilityMap['electricity']['title']) ? json_encode($postedUtilityMap['electricity']['title']) : "''"; ?>
    },
    gas: {
        totalCost: <?php echo json_encode($totalGasCostPkr); ?>,
        perVilla: <?php echo json_encode($gasCostPerVilla); ?>,
        activeAmount: <?php echo json_encode($currentGasChargeAmt); ?>,
        defaultTitle: 'Gas Supply & Incomer Cost',
        explanation: 'Apportions the monthly master SNGPL gas bill or central LPG tanker delivery across all residential units.',
        isPosted: <?php echo !empty($postedUtilityMap['gas']) ? 'true' : 'false'; ?>,
        postedId: <?php echo !empty($postedUtilityMap['gas']['id']) ? (int)$postedUtilityMap['gas']['id'] : 0; ?>,
        postedAmount: <?php echo !empty($postedUtilityMap['gas']['amount']) ? (float)$postedUtilityMap['gas']['amount'] : 0; ?>,
        postedTitle: <?php echo !empty($postedUtilityMap['gas']['title']) ? json_encode($postedUtilityMap['gas']['title']) : "''"; ?>
    }
};

function toggleFeederCategoryFields() {
    var cat = document.getElementById('feederCategory').value;
    var wpFields = document.getElementById('waterPumpSpecificFields');
    var utilType = document.getElementById('feederUtilityType');
    
    if(cat === 'water_pump') {
        wpFields.style.display = 'block';
        utilType.value = 'Water';
    } else if(cat === 'gas_station') {
        wpFields.style.display = 'none';
        utilType.value = 'Gas';
    } else {
        wpFields.style.display = 'none';
        utilType.value = 'Electricity';
    }
}

function openAddFeederModal(category) {
    document.getElementById('feederFormId').value = '';
    document.getElementById('feederName').value = '';
    document.getElementById('feederCode').value = '';
    document.getElementById('feederMeterNo').value = '';
    document.getElementById('feederLocation').value = 'Awami Block';
    
    if(category === 'water_pump') {
        document.getElementById('feederCategory').value = 'water_pump';
        document.getElementById('feederUtilityType').value = 'Water';
        document.getElementById('feederPumpHp').value = '50 HP';
        document.getElementById('feederCapacity').value = '50 HP Deep Bore';
        document.getElementById('feederModalTitle').innerHTML = '<i class="fa fa-tint text-info"></i> Add Community Water Pump / Tube-Well';
    } else if(category === 'gas_station') {
        document.getElementById('feederCategory').value = 'gas_station';
        document.getElementById('feederUtilityType').value = 'Gas';
        document.getElementById('feederCapacity').value = '1000 MMBTU / hr';
        document.getElementById('feederModalTitle').innerHTML = '<i class="fa fa-fire text-danger"></i> Add Gas Master Incomer / Central LPG';
    } else {
        document.getElementById('feederCategory').value = 'transformer';
        document.getElementById('feederUtilityType').value = 'Electricity';
        document.getElementById('feederCapacity').value = '200 kVA';
        document.getElementById('feederModalTitle').innerHTML = '<i class="fa fa-flash text-warning"></i> Add Main Feeder / Transformer Station';
    }
    toggleFeederCategoryFields();
    $('#modalFeederForm').modal('show');
}

function openEditFeederModal(feeder) {
    document.getElementById('feederFormId').value = feeder.id || '';
    document.getElementById('feederName').value = feeder.name || '';
    document.getElementById('feederCode').value = feeder.code || '';
    document.getElementById('feederUtilityType').value = feeder.utility_type || 'Electricity';
    document.getElementById('feederMeterNo').value = feeder.meter_number || '';
    document.getElementById('feederCapacity').value = feeder.capacity || '';
    document.getElementById('feederLocation').value = feeder.location_block || '';

    var cat = feeder.feeder_category;
    if(!cat) {
        if(feeder.utility_type === 'Water') cat = 'water_pump';
        else if(feeder.utility_type === 'Gas') cat = 'gas_station';
        else cat = 'transformer';
    }
    document.getElementById('feederCategory').value = cat;
    
    if(document.getElementById('feederPumpHp')) document.getElementById('feederPumpHp').value = feeder.pump_hp || '';
    if(document.getElementById('feederFlowMeterNo')) document.getElementById('feederFlowMeterNo').value = feeder.flow_meter_no || '';
    if(document.getElementById('feederConnectedTransformer')) document.getElementById('feederConnectedTransformer').value = feeder.connected_feeder_id || '';

    document.getElementById('feederModalTitle').innerHTML = '<i class="fa fa-pencil text-info"></i> Edit Station #' + feeder.id + ' (' + (feeder.name || '') + ')';
    toggleFeederCategoryFields();
    $('#modalFeederForm').modal('show');
}

function openReadingModal(feeder, reading) {
    document.getElementById('readingFeederId').value = feeder.id;
    document.getElementById('readingFeederName').innerText = feeder.name;
    document.getElementById('readingMeterLabel').innerText = feeder.meter_number || 'N/A';

    var isPump = (feeder.feeder_category === 'water_pump' || feeder.utility_type === 'Water');
    var pumpBox = document.getElementById('pumpReadingFields');
    if(pumpBox) {
        pumpBox.style.display = isPump ? 'block' : 'none';
    }

    if(reading) {
        document.getElementById('feederPrevReading').value = reading.previous_reading || 0;
        document.getElementById('feederCurrReading').value = reading.current_reading || 0;
        document.getElementById('feederMultiplier').value = reading.multiplier || 1.0;
        document.getElementById('feederUnits').value = reading.units_consumed || 0;
        document.getElementById('feederNotes').value = reading.notes || '';
        if(document.getElementById('feederFlowUnits')) document.getElementById('feederFlowUnits').value = reading.flow_units || 0;
        if(document.getElementById('feederOpHours')) document.getElementById('feederOpHours').value = reading.operating_hours || 0;
        if(document.getElementById('feederOpCost')) document.getElementById('feederOpCost').value = reading.operating_cost_pkr || 0;
    } else {
        document.getElementById('feederPrevReading').value = '';
        document.getElementById('feederCurrReading').value = '';
        document.getElementById('feederMultiplier').value = '1.0';
        document.getElementById('feederUnits').value = '';
        document.getElementById('feederNotes').value = '';
        if(document.getElementById('feederFlowUnits')) document.getElementById('feederFlowUnits').value = '';
        if(document.getElementById('feederOpHours')) document.getElementById('feederOpHours').value = '';
        if(document.getElementById('feederOpCost')) document.getElementById('feederOpCost').value = '';
    }

    $('#modalReadingForm').modal('show');
}

function updatePropagateForm() {
    var uType = document.getElementById('propagateUtilitySelect').value;
    var d = utilityData[uType] || utilityData.water;

    document.getElementById('propagateExplanation').innerText = d.explanation;
    document.getElementById('propagateTotalCostDisplay').innerText = 'PKR ' + Math.round(d.totalCost).toLocaleString();
    document.getElementById('propagateFairShareDisplay').innerText = 'PKR ' + Math.round(d.perVilla).toLocaleString() + ' / villa';
    
    var amt = (d.isPosted && d.postedAmount > 0) ? Math.round(d.postedAmount) : ((d.perVilla > 0) ? Math.round(d.perVilla) : Math.round(d.activeAmount));
    document.getElementById('propagateChargeAmount').value = amt;
    document.getElementById('propagateChargeTitle').value = (d.isPosted && d.postedTitle) ? d.postedTitle : d.defaultTitle;

    var alertBox = document.getElementById('propagatePostedAlert');
    var alertText = document.getElementById('propagatePostedText');
    var modalDelBtn = document.getElementById('propagateModalDeleteBtn');
    var footerDelBtn = document.getElementById('propagateModalFooterDeleteBtn');

    if(d.isPosted && d.postedId > 0) {
        var deleteUrl = baseDeleteUrl + '/' + d.postedId + '?society_id=' + encodeURIComponent(currentSocId) + '&month=' + encodeURIComponent(currentMonth);
        if(alertBox) alertBox.style.display = 'block';
        if(alertText) alertText.innerText = 'Active charge "' + d.postedTitle + '" of PKR ' + Math.round(d.postedAmount).toLocaleString() + ' per villa is currently active on resident bills. You can update it or delete/un-post it.';
        if(modalDelBtn) {
            modalDelBtn.href = deleteUrl;
            modalDelBtn.style.display = 'inline-block';
        }
        if(footerDelBtn) {
            footerDelBtn.href = deleteUrl;
            footerDelBtn.style.display = 'inline-block';
        }
    } else {
        if(alertBox) alertBox.style.display = 'none';
        if(modalDelBtn) modalDelBtn.style.display = 'none';
        if(footerDelBtn) footerDelBtn.style.display = 'none';
    }
}

function openPropagateModal(selectedUtility) {
    if(selectedUtility && document.getElementById('propagateUtilitySelect')) {
        document.getElementById('propagateUtilitySelect').value = selectedUtility;
    }
    updatePropagateForm();
    $('#modalPropagateUtility').modal('show');
}

document.addEventListener('DOMContentLoaded', function() {
    var prev = document.getElementById('feederPrevReading');
    var curr = document.getElementById('feederCurrReading');
    var mult = document.getElementById('feederMultiplier');
    var units = document.getElementById('feederUnits');
    var opCost = document.getElementById('feederOpCost');

    function calcUnits() {
        var p = parseFloat(prev.value) || 0;
        var c = parseFloat(curr.value) || 0;
        var m = parseFloat(mult.value) || 1.0;
        if(c >= p && curr.value !== '') {
            var calculatedUnits = (c - p) * m;
            units.value = calculatedUnits.toFixed(1);
            if(opCost && (opCost.value === '' || opCost.value === '0')) {
                opCost.value = Math.round(calculatedUnits * elecRateGlobal);
            }
        }
    }

    if(curr) curr.addEventListener('input', calcUnits);
    if(prev) prev.addEventListener('input', calcUnits);
    if(mult) mult.addEventListener('input', calcUnits);
});
</script>
