<div class="page-title">
    <div class="title-env">
        <h1 class="title"><span class="fa fa-cloud-upload"></span> <?php echo __('Billing System Backup & Migration Hub'); ?></h1>
        <p class="description"><?php echo __('Export complete utility billing database, download JSON backups, or re-run migration synchronization.'); ?></p>
    </div>
</div>

<div class="panel">
    <div class="panel-body">
        <?php echo $this->Session->flash(); ?>

        <!-- Database Counts Status -->
        <div class="row" style="margin-bottom:20px;">
            <div class="col-md-3 col-sm-6">
                <div class="well" style="background:#0f172a;color:#fff;border-radius:10px;padding:16px;border:none;">
                    <div style="font-size:11px;text-transform:uppercase;color:#94a3b8;font-weight:700;"><?php echo __('Total Invoices'); ?></div>
                    <div style="font-size:22px;font-weight:800;color:#38bdf8;margin:6px 0;"><?php echo (int)$stats['bills']; ?></div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="well" style="background:#0f172a;color:#fff;border-radius:10px;padding:16px;border:none;">
                    <div style="font-size:11px;text-transform:uppercase;color:#94a3b8;font-weight:700;"><?php echo __('Meter Readings'); ?></div>
                    <div style="font-size:22px;font-weight:800;color:#10b981;margin:6px 0;"><?php echo (int)$stats['readings']; ?></div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="well" style="background:#0f172a;color:#fff;border-radius:10px;padding:16px;border:none;">
                    <div style="font-size:11px;text-transform:uppercase;color:#94a3b8;font-weight:700;"><?php echo __('Utility Meters'); ?></div>
                    <div style="font-size:22px;font-weight:800;color:#f59e0b;margin:6px 0;"><?php echo (int)$stats['meters']; ?></div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="well" style="background:#0f172a;color:#fff;border-radius:10px;padding:16px;border:none;">
                    <div style="font-size:11px;text-transform:uppercase;color:#94a3b8;font-weight:700;"><?php echo __('Consumers & Units'); ?></div>
                    <div style="font-size:22px;font-weight:800;color:#a855f7;margin:6px 0;"><?php echo (int)$stats['clients'] . ' / ' . (int)$stats['properties']; ?></div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="well" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:24px;text-align:center;">
                    <h3 style="font-weight:800;color:#0f172a;margin-top:0;"><span class="fa fa-download text-success"></span> <?php echo __('Export Billing Backup (.JSON)'); ?></h3>
                    <p style="color:#64748b;font-size:13px;margin-bottom:20px;">
                        <?php echo __('Download a complete standalone JSON backup of all utility bills, meters, consumers, historical readings, maintenance heads, and print templates.'); ?>
                    </p>
                    <?php echo $this->Html->link('<span class="fa fa-download"></span> ' . __('Download Backup (JSON)'), array('action' => 'export_json'), array('escape' => false, 'class' => 'btn btn-success btn-lg', 'style' => 'font-weight:800;padding:12px 24px;')); ?>
                </div>
            </div>

            <div class="col-md-6">
                <div class="well" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:24px;text-align:center;">
                    <h3 style="font-weight:800;color:#0f172a;margin-top:0;"><span class="fa fa-upload text-info"></span> <?php echo __('Restore / Import Backup (.JSON)'); ?></h3>
                    <p style="color:#64748b;font-size:13px;margin-bottom:20px;">
                        <?php echo __('Upload an existing JSON backup file to restore utility bills, meters, readings, and consumer records.'); ?>
                    </p>
                    <?php echo $this->Form->create('BillingMigration', array('action' => 'import_json', 'type' => 'file', 'style' => 'display:inline-block;')); ?>
                        <div class="form-group" style="margin-bottom:12px;">
                            <input type="file" name="backup_file" accept=".json" class="form-control" required style="display:inline-block;width:auto;" />
                        </div>
                        <button type="submit" class="btn btn-info btn-lg" style="font-weight:800;padding:10px 24px;" onclick="return confirm('Restore database from uploaded JSON backup?');">
                            <span class="fa fa-upload"></span> <?php echo __('Restore Backup'); ?>
                        </button>
                    <?php echo $this->Form->end(); ?>
                </div>
            </div>
        </div>

    </div>
</div>
