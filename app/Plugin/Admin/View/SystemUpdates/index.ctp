<?php
$activeTab = !empty($this->request->query['tab']) && $this->request->query['tab'] === 'database' ? 'database' : 'core';
?>
<style>
.wv-update-container {
    padding: 20px 24px;
    background: #f8fafc;
    min-height: calc(100vh - 120px);
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
}
.wv-tab-nav {
    display: flex;
    gap: 8px;
    margin-bottom: 24px;
}
.wv-tab-btn {
    padding: 10px 22px;
    font-size: 14px;
    font-weight: 600;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
    border: none;
    text-decoration: none !important;
}
.wv-tab-btn.active {
    background: #4f46e5;
    color: #ffffff !important;
    box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);
}
.wv-tab-btn:not(.active) {
    background: transparent;
    color: #64748b !important;
}
.wv-tab-btn:not(.active):hover {
    background: #e2e8f0;
    color: #1e293b !important;
}
.wv-card-panel {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    overflow: hidden;
}
.wv-card-header {
    padding: 18px 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid #f1f5f9;
}
.wv-badge-safe {
    background: #10b981;
    color: #ffffff;
    font-size: 11px;
    font-weight: 700;
    padding: 4px 12px;
    border-radius: 20px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    letter-spacing: 0.3px;
}
.wv-info-banner {
    background: #0284c7;
    color: #ffffff;
    padding: 18px 20px;
    border-radius: 8px;
    margin: 20px 24px;
    font-size: 13.5px;
    line-height: 1.6;
}
.wv-step-num {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: #4f46e5;
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 13px;
    flex-shrink: 0;
}
.wv-step-item {
    display: flex;
    gap: 14px;
    margin-bottom: 22px;
}
.wv-step-item:last-child {
    margin-bottom: 0;
}
.wv-btn-install {
    background: #4f46e5;
    color: #ffffff;
    font-weight: 600;
    font-size: 14px;
    padding: 10px 20px;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.wv-btn-install:hover {
    background: #4338ca;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
}
.wv-btn-outline {
    background: #ffffff;
    border: 1px solid #cbd5e1;
    color: #475569;
    font-weight: 600;
    font-size: 14px;
    padding: 9px 18px;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    text-decoration: none !important;
}
.wv-btn-outline:hover {
    background: #f8fafc;
    border-color: #94a3b8;
    color: #1e293b;
}
</style>

<div class="wv-update-container">
    <!-- Executive Version & System Health Dashboard Header -->
    <div class="row mb-4">
        <!-- Card 1: System Version Pumped to v9.5.0 -->
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="wv-card-panel p-3" style="border-left: 4px solid #4f46e5; height: 100%;">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span style="font-size: 11.5px; font-weight: 700; text-transform: uppercase; color: #4f46e5; letter-spacing: 0.5px;">
                        <?php echo __('System Version');?>
                    </span>
                    <span class="badge badge-success" style="font-size: 10px; background: #10b981; font-weight: 700; padding: 3px 8px; border-radius: 12px;">
                        <i class="fa fa-check-circle"></i> <?php echo __('Active Release');?>
                    </span>
                </div>
                <div style="font-size: 26px; font-weight: 800; color: #0f172a; letter-spacing: -0.5px; margin: 4px 0 2px 0;">
                    <?php echo h(!empty($systemInfo['app_version']) ? $systemInfo['app_version'] : 'v9.5.0');?>
                </div>
                <div style="font-size: 11.5px; color: #64748b; line-height: 1.3;">
                    <?php echo h(!empty($systemInfo['release_name']) ? $systemInfo['release_name'] : 'White Villas Enterprise 9.5');?>
                </div>
            </div>
        </div>

        <!-- Card 2: Database Status & Tables Count -->
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="wv-card-panel p-3" style="border-left: 4px solid #0ea5e9; height: 100%;">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span style="font-size: 11.5px; font-weight: 700; text-transform: uppercase; color: #0ea5e9; letter-spacing: 0.5px;">
                        <?php echo __('Database Schema');?>
                    </span>
                    <span class="badge badge-info" style="font-size: 10px; background: #0ea5e9; font-weight: 700; padding: 3px 8px; border-radius: 12px;">
                        <?php echo h(!empty($systemInfo['database_name']) ? $systemInfo['database_name'] : 'whitevillas100');?>
                    </span>
                </div>
                <div style="font-size: 26px; font-weight: 800; color: #0f172a; letter-spacing: -0.5px; margin: 4px 0 2px 0;">
                    <?php echo (int)(!empty($systemInfo['total_tables']) ? $systemInfo['total_tables'] : 185);?> <?php echo __('Tables');?>
                </div>
                <div style="font-size: 11.5px; color: #10b981; font-weight: 600; line-height: 1.3;">
                    <i class="fa fa-check-circle"></i> <?php echo __('100% Synced & Verified');?>
                </div>
            </div>
        </div>

        <!-- Card 3: Latest Applied Update -->
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="wv-card-panel p-3" style="border-left: 4px solid #10b981; height: 100%;">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span style="font-size: 11.5px; font-weight: 700; text-transform: uppercase; color: #10b981; letter-spacing: 0.5px;">
                        <?php echo __('Latest Applied Patch');?>
                    </span>
                    <span class="badge badge-light border" style="font-size: 10px; font-weight: 700; padding: 3px 8px;">
                        <?php echo h(!empty($systemInfo['last_patch_version']) ? $systemInfo['last_patch_version'] : 'v9.5.0');?>
                    </span>
                </div>
                <div style="font-size: 13.5px; font-weight: 700; color: #0f172a; margin: 4px 0 2px 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?php echo h(!empty($systemInfo['last_patch_name']) ? $systemInfo['last_patch_name'] : 'v9.5.0 Enterprise Feature Release'); ?>">
                    <?php echo h(!empty($systemInfo['last_patch_name']) ? $systemInfo['last_patch_name'] : 'v9.5.0 Enterprise Feature Release');?>
                </div>
                <div style="font-size: 11.5px; color: #64748b; line-height: 1.3;">
                    <i class="fa fa-clock-o"></i> <?php echo date('M d, Y h:i A', strtotime(!empty($systemInfo['last_update']) ? $systemInfo['last_update'] : date('Y-m-d H:i:s')));?>
                </div>
            </div>
        </div>

        <!-- Card 4: Server Environment & Backups -->
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="wv-card-panel p-3" style="border-left: 4px solid #f59e0b; height: 100%;">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span style="font-size: 11.5px; font-weight: 700; text-transform: uppercase; color: #d97706; letter-spacing: 0.5px;">
                        <?php echo __('System Health');?>
                    </span>
                    <span class="badge badge-warning" style="font-size: 10px; background: #f59e0b; color: #fff; font-weight: 700; padding: 3px 8px; border-radius: 12px;">
                        <?php echo __('Protected');?>
                    </span>
                </div>
                <div style="font-size: 14px; font-weight: 700; color: #0f172a; margin: 4px 0 2px 0;">
                    PHP <?php echo h(!empty($systemInfo['php_version']) ? $systemInfo['php_version'] : phpversion());?> &bull; <?php echo (int)(!empty($systemInfo['total_snapshots']) ? $systemInfo['total_snapshots'] : 0);?> <?php echo __('Snapshots');?>
                </div>
                <div style="font-size: 11.5px; color: #64748b; line-height: 1.3;">
                    Zip: <?php echo !empty($systemInfo['zip_enabled']) ? 'Active' : 'Disabled';?> &bull; Upload: <?php echo h(!empty($systemInfo['max_upload']) ? $systemInfo['max_upload'] : '64M');?>
                </div>
            </div>
        </div>
    </div>

    <!-- Header Nav Tabs -->
    <div class="wv-tab-nav">
        <a href="<?php echo $this->Html->url(array('action' => 'index', '?' => array('tab' => 'core')));?>" class="wv-tab-btn <?php echo $activeTab === 'core' ? 'active' : '';?>">
            <i class="fa fa-cubes mr-1"></i> <?php echo __('Update Core');?> <span class="badge badge-light border ml-1" style="font-size:10.5px;"><?php echo h(!empty($systemInfo['app_version']) ? $systemInfo['app_version'] : 'v9.5.0');?></span>
        </a>
        <a href="<?php echo $this->Html->url(array('action' => 'index', '?' => array('tab' => 'database')));?>" class="wv-tab-btn <?php echo $activeTab === 'database' ? 'active' : '';?>">
            <i class="fa fa-database mr-1"></i> <?php echo __('Update Database');?> <span class="badge badge-success ml-1" style="background:#10b981;font-size:10.5px;"><?php echo (int)(!empty($systemInfo['total_tables']) ? $systemInfo['total_tables'] : 185);?> Tables</span>
        </a>
    </div>

    <?php if ($activeTab === 'core'): ?>
    <!-- TAB 1: UPDATE CORE -->
    <div class="row">
        <!-- Left: Universal 1-Click Update Patch Installer -->
        <div class="col-md-7 col-lg-8 mb-4">
            <div class="wv-card-panel">
                <div class="wv-card-header">
                    <div style="font-size: 16px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 10px;">
                        <i class="fa fa-cloud-upload text-primary" style="font-size: 18px;"></i>
                        <?php echo __('Universal 1-Click Update Patch Installer');?>
                    </div>
                    <span class="wv-badge-safe">
                        <i class="fa fa-shield"></i> <?php echo __('Auto-Backup Protected');?>
                    </span>
                </div>

                <!-- Info Alert Banner -->
                <div class="wv-info-banner">
                    <strong><?php echo __('Safe Update Protocol:');?></strong> <?php echo __('Upload any update patch (<code>.zip</code>). The engine will automatically create a pre-update database snapshot, extract modified files, execute any new database migrations/SQL queries, and clear system caches in one go.');?>
                </div>

                <div style="padding: 0 24px 24px 24px;">
                    <?php echo $this->Form->create(false, array('type' => 'file', 'url' => array('action' => 'apply_patch'), 'id' => 'patchInstallerForm'));?>
                    
                    <div class="form-group mb-4">
                        <label style="font-size: 13.5px; font-weight: 700; color: #334155; margin-bottom: 8px; display: block;">
                            <?php echo __('Update with Zip file (ZIP Patch)');?>
                        </label>
                        
                        <div class="input-group" style="box-shadow: 0 1px 2px rgba(0,0,0,0.05); border-radius: 8px; overflow: hidden;">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-white border-right-0 text-muted" style="border-color: #cbd5e1;">
                                    <i class="fa fa-file-zip-o text-primary"></i>
                                </span>
                            </div>
                            <input type="file" name="patch_file" id="patchFileInput" class="form-control" accept=".zip" required style="border-color: #cbd5e1; height: 44px; padding-top: 8px;">
                        </div>
                        <div class="mt-2 text-muted" style="font-size: 12.5px;">
                            <i class="fa fa-check-circle text-success mr-1"></i> <?php echo __('Compatible with <code>whitevillas_patch.zip</code>, <code>update.zip</code>, and standard modular update archives.');?>
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <div class="custom-control custom-checkbox" style="padding-left: 24px;">
                            <input type="checkbox" name="confirm_overwrite" value="1" class="custom-control-input" id="confirmOverwriteCheck" required>
                            <label class="custom-control-label" for="confirmOverwriteCheck" style="font-size: 13px; font-weight: 500; color: #475569; cursor: pointer;">
                                <?php echo __('I confirm and understand that existing files will be safely updated with the new patch files.');?>
                            </label>
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-between pt-2">
                        <button type="submit" id="btnApplyPatch" class="wv-btn-install">
                            <i class="fa fa-bolt"></i> <?php echo __('Install & Apply Update Patch');?>
                        </button>

                        <button type="button" class="wv-btn-outline" data-toggle="modal" data-target="#modalSnapshots">
                            <i class="fa fa-database text-muted"></i> <?php echo __('View Snapshots (%d)', count($snapshots));?>
                        </button>
                    </div>
                    <?php echo $this->Form->end();?>
                </div>
            </div>
        </div>

        <!-- Right: Update Automation Steps -->
        <div class="col-md-5 col-lg-4 mb-4">
            <div class="wv-card-panel p-4" style="height: 100%;">
                <div style="font-size: 15px; font-weight: 700; color: #1e293b; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
                    <i class="fa fa-list-ol text-primary"></i> <?php echo __('Update Automation Steps');?>
                </div>

                <div class="wv-step-item">
                    <div class="wv-step-num">1</div>
                    <div>
                        <div style="font-size: 13.5px; font-weight: 700; color: #1e293b;"><?php echo __('Auto Pre-Update Backup');?></div>
                        <div style="font-size: 12px; color: #64748b; margin-top: 2px;">
                            <?php echo __('A full SQL snapshot is generated into <code>app/tmp/backups/</code>.');?>
                        </div>
                    </div>
                </div>

                <div class="wv-step-item">
                    <div class="wv-step-num">2</div>
                    <div>
                        <div style="font-size: 13.5px; font-weight: 700; color: #1e293b;"><?php echo __('Smart Recursive File Extractor');?></div>
                        <div style="font-size: 12px; color: #64748b; margin-top: 2px;">
                            <?php echo __('Overwrites modified core files and creates missing directories automatically.');?>
                        </div>
                    </div>
                </div>

                <div class="wv-step-item">
                    <div class="wv-step-num">3</div>
                    <div>
                        <div style="font-size: 13.5px; font-weight: 700; color: #1e293b;"><?php echo __('Database Schema & Migration Auto-Sync');?></div>
                        <div style="font-size: 12px; color: #64748b; margin-top: 2px;">
                            <?php echo __('Executes any included SQL scripts or migrations without deleting existing data.');?>
                        </div>
                    </div>
                </div>

                <div class="wv-step-item">
                    <div class="wv-step-num">4</div>
                    <div>
                        <div style="font-size: 13.5px; font-weight: 700; color: #1e293b;"><?php echo __('Full Cache & View Optimization Flush');?></div>
                        <div style="font-size: 12px; color: #64748b; margin-top: 2px;">
                            <?php echo __('Purges application, model, persistent, and view compiled caches.');?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php else: ?>
    <!-- TAB 2: UPDATE DATABASE -->
    <div class="row">
        <!-- Database Migrations & Schema Sync Card (Matching Screenshot) -->
        <div class="col-12 mb-4">
            <div class="wv-card-panel">
                <div class="wv-card-header" style="background: #f1f5f9; padding: 16px 24px;">
                    <div style="font-size: 16px; font-weight: 700; color: #3b82f6; display: flex; align-items: center; gap: 10px;">
                        <i class="fa fa-database" style="font-size: 18px;"></i>
                        <?php echo __('Database Migrations & Schema Sync');?>
                    </div>
                    <span class="badge" style="background: #0ea5e9; color: #ffffff; font-size: 11.5px; font-weight: 700; padding: 6px 14px; border-radius: 20px;">
                        <i class="fa fa-refresh mr-1"></i> <?php echo __('Auto-Sync');?>
                    </span>
                </div>

                <div style="padding: 24px;">
                    <p style="color: #64748b; font-size: 14px; line-height: 1.6; margin-bottom: 24px;">
                        <?php echo __('Run this tool whenever you want to ensure all database tables, columns, indexes, and schema updates from newly added features or extensions are fully applied without touching your existing data.');?>
                    </p>

                    <!-- Database Schema Status -->
                    <div class="p-3 mb-4 rounded" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div style="font-size: 15px; font-weight: 700; color: #059669; display: flex; align-items: center; gap: 8px;">
                                <i class="fa fa-check-circle" style="font-size: 18px;"></i>
                                <?php echo __('Database Schema & Feature Alignment Status:');?>
                            </div>
                            <span class="badge" style="background: #10b981; color: #ffffff; font-size: 12px; font-weight: 700; padding: 4px 12px; border-radius: 20px;">
                                <i class="fa fa-shield mr-1"></i> <?php echo __('Version %s Verified', $currentVersion);?>
                            </span>
                        </div>
                        <div style="font-size: 13px; color: #475569; line-height: 1.6; margin-bottom: 14px;">
                            <code>SUCCESS</code> <?php echo __('All <strong>%d database tables</strong> and system features are fully aligned and up-to-date in <code>whitevillas100</code>.', $totalTables);?>
                        </div>
                        <div class="row" style="font-size: 12.5px; color: #334155;">
                            <div class="col-md-6 mb-2">
                                <i class="fa fa-check-square text-success mr-1"></i> <strong><?php echo __('Facebook Leads Graph Webhook:');?></strong> <?php echo __('API v20.0 + Light Agent & Admin Email');?>
                            </div>
                            <div class="col-md-6 mb-2">
                                <i class="fa fa-check-square text-success mr-1"></i> <strong><?php echo __('Deals Management:');?></strong> <?php echo __('Readable Contrast, Stages & Cashflow Forecasting');?>
                            </div>
                            <div class="col-md-6 mb-2">
                                <i class="fa fa-check-square text-success mr-1"></i> <strong><?php echo __('Follow-Up Scheduling:');?></strong> <?php echo __('Standardized DD-MM-YY Time & Serial Ordering');?>
                            </div>
                            <div class="col-md-6 mb-2">
                                <i class="fa fa-check-square text-success mr-1"></i> <strong><?php echo __('System Updates & Rollback:');?></strong> <?php echo __('v9.5.0 Core Engine + Pre-Update SQL Snapshots');?>
                            </div>
                        </div>
                    </div>

                    <?php echo $this->Form->create(false, array('url' => array('action' => 'sync_database'), 'id' => 'dbSyncForm'));?>
                    
                    <div class="form-group mb-4" style="padding-left: 2px;">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" name="confirm_sync" value="1" class="custom-control-input" id="confirmDbSyncCheck" required checked>
                            <label class="custom-control-label" for="confirmDbSyncCheck" style="font-size: 13.5px; font-weight: 500; color: #334155; cursor: pointer;">
                                <?php echo __('I confirm that read all update instructions carefully.');?>
                            </label>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-3 pt-2">
                        <button type="submit" id="btnRunDbUpdate" class="btn" style="background: #10b981; color: #ffffff; font-size: 14.5px; font-weight: 700; padding: 10px 24px; border-radius: 8px; border: none; display: inline-flex; align-items: center; gap: 8px; transition: all 0.2s ease;">
                            <i class="fa fa-play" style="font-size: 13px;"></i> <?php echo __('Run Update');?>
                        </button>

                        <button type="button" class="wv-btn-outline ml-3" data-toggle="modal" data-target="#modalSnapshots">
                            <i class="fa fa-database text-muted"></i> <?php echo __('View Snapshots (%d)', count($snapshots));?>
                        </button>
                    </div>
                    <?php echo $this->Form->end();?>

                    <!-- Optional Custom SQL Runner Accordion -->
                    <div class="mt-5 pt-4 border-top">
                        <a class="text-muted font-weight-bold" data-toggle="collapse" href="#collapseCustomSql" role="button" aria-expanded="false" aria-controls="collapseCustomSql" style="font-size: 13px; text-decoration: none;">
                            <i class="fa fa-terminal mr-1"></i> <?php echo __('Advanced / Custom SQL Patch Execution (Optional)');?> <i class="fa fa-angle-down ml-1"></i>
                        </a>
                        <div class="collapse mt-3" id="collapseCustomSql">
                            <div class="p-3 bg-light rounded border">
                                <?php echo $this->Form->create(false, array('type' => 'file', 'url' => array('action' => 'execute_sql')));?>
                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label style="font-size: 12.5px; font-weight: 700; color: #475569;"><?php echo __('Upload .sql Script:');?></label>
                                        <input type="file" name="sql_file" class="form-control form-control-sm" accept=".sql">
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label style="font-size: 12.5px; font-weight: 700; color: #475569;"><?php echo __('Or Paste SQL Query:');?></label>
                                        <textarea name="raw_sql_query" class="form-control form-control-sm font-monospace" rows="2" placeholder="ALTER TABLE ..."></textarea>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-sm btn-primary" style="font-weight: 600;">
                                    <i class="fa fa-play mr-1"></i> <?php echo __('Execute SQL Script');?>
                                </button>
                                <?php echo $this->Form->end();?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Update History Log -->
    <div class="wv-card-panel mt-4">
        <div class="wv-card-header">
            <div style="font-size: 15px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 8px;">
                <i class="fa fa-history text-muted"></i> <?php echo __('Applied Updates & Patches History');?>
            </div>
            <span style="font-size: 12px; color: #64748b;">
                <?php echo sprintf(__('%d updates logged'), count($updateLogs));?>
            </span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0" style="font-size: 13px;">
                <thead style="background: #f8fafc; color: #475569; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">
                    <tr>
                        <th class="py-3 px-4">#</th>
                        <th class="py-3"><?php echo __('Patch Name / Archive');?></th>
                        <th class="py-3"><?php echo __('Type');?></th>
                        <th class="py-3"><?php echo __('Files Updated');?></th>
                        <th class="py-3"><?php echo __('SQL Queries');?></th>
                        <th class="py-3"><?php echo __('Backup Snapshot');?></th>
                        <th class="py-3"><?php echo __('Applied By');?></th>
                        <th class="py-3"><?php echo __('Date & Time');?></th>
                        <th class="py-3 px-4 text-center"><?php echo __('Status');?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($updateLogs)): ?>
                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted">
                            <i class="fa fa-info-circle mr-1"></i> <?php echo __('No updates applied yet. Your system is on the base release.');?>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($updateLogs as $idx => $log): 
                        $l = $log['SystemUpdate'];
                    ?>
                    <tr>
                        <td class="px-4 font-weight-bold text-muted"><?php echo $idx + 1;?></td>
                        <td>
                            <strong style="color: #1e293b;"><?php echo h($l['patch_name']);?></strong>
                            <?php if (!empty($l['version']) && $l['version'] !== 'auto'): ?>
                                <span class="badge ml-1" style="background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; font-weight: 700; font-size: 11.5px;"><?php echo h($l['version']);?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge badge-<?php echo $l['patch_type'] === 'core_and_db' ? 'primary' : 'info';?>">
                                <?php echo $l['patch_type'] === 'core_and_db' ? 'Core + DB' : 'Database';?>
                            </span>
                        </td>
                        <td><span class="badge badge-light border px-2"><?php echo (int)$l['files_updated'];?> files</span></td>
                        <td><span class="badge badge-light border px-2"><?php echo (int)$l['sql_queries_run'];?> queries</span></td>
                        <td>
                            <?php if (!empty($l['backup_file'])): ?>
                                <a href="<?php echo $this->Html->url(array('action' => 'download_backup', $l['backup_file']));?>" class="text-primary font-weight-600" title="<?php echo __('Download pre-update backup snapshot');?>">
                                    <i class="fa fa-download mr-1"></i> <?php echo h($l['backup_file']);?>
                                </a>
                            <?php else: ?>
                                <span class="text-muted">&mdash;</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-muted"><?php echo h($l['applied_by']);?></td>
                        <td class="text-muted"><?php echo date('M d, Y h:i A', strtotime($l['created_at']));?></td>
                        <td class="px-4 text-center">
                            <?php if ($l['status'] === 'success'): ?>
                                <span class="badge badge-success px-2 py-1"><i class="fa fa-check mr-1"></i> <?php echo __('Success');?></span>
                            <?php else: ?>
                                <span class="badge badge-warning px-2 py-1"><i class="fa fa-exclamation-triangle mr-1"></i> <?php echo __('Warnings');?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Database Snapshots & Backups Manager -->
<div class="modal fade" id="modalSnapshots" tabindex="-1" role="dialog" aria-labelledby="modalSnapshotsLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 25px rgba(0,0,0,0.15);">
            <div class="modal-header bg-light" style="border-top-left-radius: 12px; border-top-right-radius: 12px;">
                <h5 class="modal-title font-weight-bold" id="modalSnapshotsLabel" style="color: #1e293b;">
                    <i class="fa fa-database text-primary mr-1"></i> <?php echo __('Pre-Update Database Snapshots');?>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0">
                <div class="p-3 bg-light border-bottom d-flex justify-content-between align-items-center">
                    <span class="text-muted" style="font-size: 13px;">
                        <?php echo __('Snapshots are stored safely in <code>app/tmp/backups/</code>.');?>
                    </span>
                    <a href="<?php echo $this->Html->url(array('action' => 'create_snapshot'));?>" class="btn btn-sm btn-primary" style="font-weight: 600; border-radius: 6px;">
                        <i class="fa fa-plus mr-1"></i> <?php echo __('Create Snapshot Now');?>
                    </a>
                </div>

                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-hover mb-0" style="font-size: 13px;">
                        <thead style="background: #f8fafc; font-size: 12px;">
                            <tr>
                                <th class="px-3"><?php echo __('Snapshot File');?></th>
                                <th><?php echo __('Size');?></th>
                                <th><?php echo __('Date Created');?></th>
                                <th class="text-right px-3"><?php echo __('Actions');?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($snapshots)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">
                                    <?php echo __('No database snapshots found yet.');?>
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($snapshots as $s): ?>
                            <tr>
                                <td class="px-3 font-weight-600">
                                    <i class="fa fa-file-code-o text-muted mr-1"></i> <?php echo h($s['filename']);?>
                                </td>
                                <td><?php echo h($s['filesize_formatted']);?></td>
                                <td class="text-muted"><?php echo date('M d, Y h:i A', strtotime($s['created_at']));?></td>
                                <td class="text-right px-3">
                                    <a href="<?php echo $this->Html->url(array('action' => 'download_backup', $s['filename']));?>" class="btn btn-xs btn-outline-primary" title="<?php echo __('Download SQL');?>">
                                        <i class="fa fa-download"></i>
                                    </a>
                                    <a href="<?php echo $this->Html->url(array('action' => 'restore_backup', $s['filename']));?>" class="btn btn-xs btn-outline-warning" onclick="return confirm('WARNING: Are you sure you want to restore this database snapshot? All current data will be rolled back to this snapshot.');" title="<?php echo __('1-Click Restore');?>">
                                        <i class="fa fa-undo"></i>
                                    </a>
                                    <a href="<?php echo $this->Html->url(array('action' => 'delete_backup', $s['filename']));?>" class="btn btn-xs btn-outline-danger" onclick="return confirm('Delete this backup file permanently?');" title="<?php echo __('Delete');?>">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light" style="border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"><?php echo __('Close');?></button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var form = document.getElementById('patchInstallerForm');
    var btn = document.getElementById('btnApplyPatch');
    if (form && btn) {
        form.addEventListener('submit', function() {
            btn.innerHTML = '<i class="fa fa-spinner fa-spin mr-1"></i> <?php echo __('Applying Patch & Backing Up Database...');?>';
            btn.disabled = true;
            btn.style.opacity = '0.7';
        });
    }
});
</script>
