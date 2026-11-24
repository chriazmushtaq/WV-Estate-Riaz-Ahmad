<?php
$isAdr = !empty($isGlobalAdmin);
$settings = isset($settings) && is_array($settings) ? $settings : array();
$backupRows = isset($backupRows) && is_array($backupRows) ? $backupRows : array();
$currentProjectId = isset($currentProjectId) ? (int)$currentProjectId : 0;
$projectName = isset($projectName) && is_array($projectName) ? $projectName : array();
$dbStats = isset($dbStats) && is_array($dbStats) ? $dbStats : array(
    'total_tables' => 0,
    'total_rows' => 0,
    'db_size_mb' => 0,
    'disk_free_human' => 'Available',
    'backup_dir_size_human' => '0 B',
    'backup_count' => 0,
    'server_version' => 'MySQL'
);
?>
<div class="page-title">
    <div class="title-env">
        <h1 class="title"><span class="fa fa-database"></span> <?php echo __('Enterprise Backup & Disaster Recovery Center'); ?></h1>
        <p class="description"><?php echo __('Full database snapshots, selective module exports, automated schedules, and safe one-click restores.'); ?></p>
    </div>
</div>

<div class="panel">
    <div class="panel-body">
        <?php echo $this->Session->flash(); ?>

        <!-- Top Database Health & Metrics Overview -->
        <div class="row" style="margin-bottom:16px;">
            <div class="col-md-3 col-sm-6">
                <div class="well" style="background:#0f172a;color:#fff;border-radius:8px;padding:14px;border:none;box-shadow:0 4px 12px rgba(0,0,0,0.12);">
                    <div style="font-size:11px;text-transform:uppercase;color:#94a3b8;font-weight:700;letter-spacing:.5px;"><?php echo __('Database Size'); ?></div>
                    <div style="font-size:24px;font-weight:800;color:#38bdf8;margin:4px 0;"><?php echo $dbStats['db_size_mb']; ?> <span style="font-size:14px;">MB</span></div>
                    <div style="font-size:11px;color:#cbd5e1;"><?php echo __('Total Tables'); ?>: <strong><?php echo $dbStats['total_tables']; ?></strong></div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="well" style="background:#0f172a;color:#fff;border-radius:8px;padding:14px;border:none;box-shadow:0 4px 12px rgba(0,0,0,0.12);">
                    <div style="font-size:11px;text-transform:uppercase;color:#94a3b8;font-weight:700;letter-spacing:.5px;"><?php echo __('Total Records'); ?></div>
                    <div style="font-size:24px;font-weight:800;color:#10b981;margin:4px 0;"><?php echo number_format($dbStats['total_rows']); ?></div>
                    <div style="font-size:11px;color:#cbd5e1;"><?php echo __('Database Server'); ?>: <strong><?php echo h(substr($dbStats['server_version'], 0, 15)); ?></strong></div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="well" style="background:#0f172a;color:#fff;border-radius:8px;padding:14px;border:none;box-shadow:0 4px 12px rgba(0,0,0,0.12);">
                    <div style="font-size:11px;text-transform:uppercase;color:#94a3b8;font-weight:700;letter-spacing:.5px;"><?php echo __('Storage & Free Space'); ?></div>
                    <div style="font-size:24px;font-weight:800;color:#f59e0b;margin:4px 0;"><?php echo h($dbStats['disk_free_human']); ?></div>
                    <div style="font-size:11px;color:#cbd5e1;"><?php echo __('Backup Storage'); ?>: <strong><?php echo h($dbStats['backup_dir_size_human']); ?></strong> (<?php echo (int)$dbStats['backup_count']; ?> files)</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="well" style="background:#0f172a;color:#fff;border-radius:8px;padding:14px;border:none;box-shadow:0 4px 12px rgba(0,0,0,0.12);">
                    <div style="font-size:11px;text-transform:uppercase;color:#94a3b8;font-weight:700;letter-spacing:.5px;"><?php echo __('Safety Shield'); ?></div>
                    <div style="font-size:18px;font-weight:800;color:#a855f7;margin:7px 0;"><span class="fa fa-shield"></span> <?php echo __('Active & Protected'); ?></div>
                    <div style="font-size:11px;color:#cbd5e1;"><?php echo __('Auto Safety Rollback snapshot before every restore'); ?></div>
                </div>
            </div>
        </div>

        <!-- 1-Click Instant SQL Export Section -->
        <div class="well" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:14px 18px;margin-bottom:20px;">
            <div class="row" style="display:flex;align-items:center;flex-wrap:wrap;gap:10px;">
                <div class="col-md-4 col-sm-12">
                    <h4 style="margin:0;font-size:15px;color:#1e293b;font-weight:800;"><span class="fa fa-bolt text-warning"></span> <?php echo __('Instant 1-Click SQL Export'); ?></h4>
                    <p style="margin:2px 0 0 0;font-size:12px;color:#64748b;"><?php echo __('Stream raw SQL database dump directly to your browser without waiting for archive creation.'); ?></p>
                </div>
                <div class="col-md-8 col-sm-12 text-right">
                    <?php echo $this->Html->link('<span class="fa fa-download"></span> ' . __('Full Database (.sql)'), array('controller'=>'Ardaccounts','action'=>'directsqlexport','full',$currentProjectId), array('escape'=>false,'class'=>'btn btn-success btn-sm','style'=>'font-weight:700;margin:2px;')); ?>
                    <?php echo $this->Html->link('<span class="fa fa-building"></span> ' . __('Deals & Real Estate'), array('controller'=>'Ardaccounts','action'=>'directsqlexport','realestate',$currentProjectId), array('escape'=>false,'class'=>'btn btn-primary btn-sm','style'=>'font-weight:700;margin:2px;')); ?>
                    <?php echo $this->Html->link('<span class="fa fa-book"></span> ' . __('Accounts & Ledgers'), array('controller'=>'Ardaccounts','action'=>'directsqlexport','accounts',$currentProjectId), array('escape'=>false,'class'=>'btn btn-info btn-sm','style'=>'font-weight:700;margin:2px;')); ?>
                    <?php echo $this->Html->link('<span class="fa fa-users"></span> ' . __('CRM & Leads'), array('controller'=>'Ardaccounts','action'=>'directsqlexport','crm',$currentProjectId), array('escape'=>false,'class'=>'btn btn-warning btn-sm','style'=>'font-weight:700;margin:2px;')); ?>
                    <?php echo $this->Html->link('<span class="fa fa-code"></span> ' . __('Clean Schema Only'), array('controller'=>'Ardaccounts','action'=>'directsqlexport','schema_only',$currentProjectId), array('escape'=>false,'class'=>'btn btn-default btn-sm','style'=>'font-weight:700;margin:2px;')); ?>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Create Backup Form -->
            <div class="col-md-6">
                <div class="well" style="background:#fff;border:1px solid #e2e8f0;border-radius:8px;">
                    <h4 style="margin-top:0;font-size:15px;font-weight:800;color:#0f172a;border-bottom:1px solid #f1f5f9;padding-bottom:8px;"><span class="fa fa-plus-circle text-success"></span> <?php echo __('Create Compressed Archive Backup'); ?></h4>
                    <form method="post" action="<?php echo $this->Html->url(array('controller'=>'Ardaccounts','action'=>'backups',$currentProjectId)); ?>" class="form-horizontal">
                        <input type="hidden" name="data[Ardbackup][task]" value="create_backup" />
                        <div class="form-group">
                            <label class="col-sm-4 control-label"><?php echo __('Agency / Project'); ?></label>
                            <div class="col-sm-8">
                                <?php if($isAdr){ ?>
                                    <?php echo $this->Form->select('Ardbackup.project_id', $projectName, array('id'=>'CreateBackupAgencySelect','class'=>'form-control','label'=>false,'div'=>false,'value'=>$currentProjectId)); ?>
                                <?php } else { ?>
                                    <input type="hidden" name="data[Ardbackup][project_id]" value="<?php echo (int)$agencyId; ?>" />
                                    <input type="text" class="form-control" readonly value="<?php echo h(isset($projectName[(int)$agencyId]) ? $projectName[(int)$agencyId] : '-'); ?>" />
                                <?php } ?>
                            </div>
                        </div>
                        <?php if($isAdr){ ?>
                        <div class="form-group">
                            <label class="col-sm-4 control-label"><?php echo __('Backup Scope'); ?></label>
                            <div class="col-sm-8">
                                <select name="data[Ardbackup][backup_scope]" id="BackupScopeSelect" class="form-control">
                                    <option value="global"><?php echo __('Full System (All 152 Tables + Superadmin)'); ?></option>
                                    <option value="agency"><?php echo __('Agency Data (Isolated)'); ?></option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group" id="IncludeFilesWrap" style="display:none;">
                            <div class="col-sm-offset-4 col-sm-8">
                                <label style="font-weight:normal;color:#475569;">
                                    <input type="checkbox" name="data[Ardbackup][include_files]" value="1" />
                                    <?php echo __('Include complete application files & uploads snapshot (.zip)'); ?>
                                </label>
                            </div>
                        </div>
                        <?php } else { ?>
                            <input type="hidden" name="data[Ardbackup][backup_scope]" value="agency" />
                        <?php } ?>
                        <div class="form-group">
                            <label class="col-sm-4 control-label"><?php echo __('Notes / Tag'); ?></label>
                            <div class="col-sm-8">
                                <textarea name="data[Ardbackup][notes]" class="form-control" rows="2" placeholder="<?php echo __('Optional description e.g. Pre-upgrade backup, Monthly audit'); ?>"></textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-4 control-label"><?php echo __('Encryption Password'); ?></label>
                            <div class="col-sm-8">
                                <input type="password" name="data[Ardbackup][backup_password]" class="form-control" placeholder="<?php echo __('Optional ZIP password for encryption'); ?>" autocomplete="new-password" />
                            </div>
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <div class="col-sm-offset-4 col-sm-8">
                                <button type="submit" class="btn btn-success" style="font-weight:700;"><span class="fa fa-database"></span> <?php echo __('Generate Backup Archive'); ?></button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Auto Backup Settings & Upload Restore -->
            <div class="col-md-6">
                <div class="well" style="background:#fff;border:1px solid #e2e8f0;border-radius:8px;">
                    <h4 style="margin-top:0;font-size:15px;font-weight:800;color:#0f172a;border-bottom:1px solid #f1f5f9;padding-bottom:8px;"><span class="fa fa-clock-o text-primary"></span> <?php echo __('Automated Backup Schedule'); ?></h4>
                    <form method="post" action="<?php echo $this->Html->url(array('controller'=>'Ardaccounts','action'=>'backups',$currentProjectId)); ?>" class="form-horizontal">
                        <input type="hidden" name="data[Ardbackup][task]" value="save_auto_settings" />
                        <div class="form-group">
                            <label class="col-sm-4 control-label"><?php echo __('Agency'); ?></label>
                            <div class="col-sm-8">
                                <?php if($isAdr){ ?>
                                    <?php echo $this->Form->select('Ardbackup.project_id', $projectName, array('empty'=>__('Please Select'),'class'=>'form-control','label'=>false,'div'=>false,'value'=>$currentProjectId)); ?>
                                <?php } else { ?>
                                    <input type="hidden" name="data[Ardbackup][project_id]" value="<?php echo (int)$agencyId; ?>" />
                                    <input type="text" class="form-control" readonly value="<?php echo h(isset($projectName[(int)$agencyId]) ? $projectName[(int)$agencyId] : '-'); ?>" />
                                <?php } ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-4 control-label"><?php echo __('Automated Backups'); ?></label>
                            <div class="col-sm-8" style="padding-top:7px;">
                                <label style="font-weight:normal;">
                                    <input type="checkbox" name="data[Ardbackup][daily_backup_enabled]" value="1" <?php echo !empty($settings['daily_backup_enabled']) ? 'checked="checked"' : ''; ?> />
                                    <strong><?php echo __('Active / Enabled'); ?></strong>
                                </label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-4 control-label"><?php echo __('Frequency'); ?></label>
                            <div class="col-sm-8">
                                <select name="data[Ardbackup][auto_backup_frequency]" class="form-control">
                                    <option value="daily" <?php echo (isset($settings['auto_backup_frequency']) && $settings['auto_backup_frequency']==='daily') ? 'selected="selected"' : ''; ?>><?php echo __('Daily'); ?></option>
                                    <option value="weekly" <?php echo (isset($settings['auto_backup_frequency']) && $settings['auto_backup_frequency']==='weekly') ? 'selected="selected"' : ''; ?>><?php echo __('Weekly'); ?></option>
                                    <option value="monthly" <?php echo (isset($settings['auto_backup_frequency']) && $settings['auto_backup_frequency']==='monthly') ? 'selected="selected"' : ''; ?>><?php echo __('Monthly'); ?></option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-4 control-label"><?php echo __('Run Time (HH:MM)'); ?></label>
                            <div class="col-sm-8">
                                <input type="text" name="data[Ardbackup][auto_backup_time]" value="<?php echo h(isset($settings['auto_backup_time']) ? $settings['auto_backup_time'] : '02:00'); ?>" class="form-control" placeholder="02:00" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-4 control-label"><?php echo __('Retention Policy'); ?></label>
                            <div class="col-sm-8">
                                <input type="number" min="5" max="200" name="data[Ardbackup][backup_keep_count]" value="<?php echo (int)(isset($settings['backup_keep_count']) ? $settings['backup_keep_count'] : 30); ?>" class="form-control" />
                                <span class="help-block" style="font-size:11px;margin-bottom:0;"><?php echo __('Automatically keep last X backups and purge older ones.'); ?></span>
                            </div>
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <div class="col-sm-offset-4 col-sm-8">
                                <button type="submit" class="btn btn-primary" style="font-weight:700;"><span class="fa fa-save"></span> <?php echo __('Save Schedule'); ?></button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Upload & Safe Restore Form -->
            <div class="col-md-6">
                <div class="well" style="background:#fff;border:1px solid #e2e8f0;border-radius:8px;">
                    <h4 style="margin-top:0;font-size:15px;font-weight:800;color:#0f172a;border-bottom:1px solid #f1f5f9;padding-bottom:8px;"><span class="fa fa-upload text-warning"></span> <?php echo __('Upload & Safe Restore'); ?></h4>
                    <div class="alert alert-info" style="font-size:12px;padding:8px 12px;margin-bottom:12px;">
                        <span class="fa fa-shield"></span> <strong><?php echo __('Zero Data Loss Guarantee'); ?>:</strong> <?php echo __('An automatic rollback snapshot is taken immediately before any restore starts.'); ?>
                    </div>
                    <form method="post" enctype="multipart/form-data" action="<?php echo $this->Html->url(array('controller'=>'Ardaccounts','action'=>'backups',$currentProjectId)); ?>" class="form-horizontal">
                        <input type="hidden" name="data[Ardbackup][task]" value="upload_restore" />
                        <div class="form-group">
                            <label class="col-sm-4 control-label"><?php echo __('Agency'); ?></label>
                            <div class="col-sm-8">
                                <?php if($isAdr){ ?>
                                    <?php echo $this->Form->select('Ardbackup.project_id', $projectName, array('empty'=>__('Please Select'),'class'=>'form-control','label'=>false,'div'=>false,'value'=>$currentProjectId)); ?>
                                <?php } else { ?>
                                    <input type="hidden" name="data[Ardbackup][project_id]" value="<?php echo (int)$agencyId; ?>" />
                                    <input type="text" class="form-control" readonly value="<?php echo h(isset($projectName[(int)$agencyId]) ? $projectName[(int)$agencyId] : '-'); ?>" />
                                <?php } ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-4 control-label"><?php echo __('Backup File'); ?></label>
                            <div class="col-sm-8">
                                <input type="file" name="data[Ardbackup][upload_file]" class="form-control" required />
                                <p class="help-block" style="font-size:11px;margin-bottom:0;"><?php echo __('Accepted formats: .zip or .sql'); ?></p>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-4 control-label"><?php echo __('Backup Password'); ?></label>
                            <div class="col-sm-8">
                                <input type="password" name="data[Ardbackup][restore_password]" class="form-control" placeholder="<?php echo __('Only if ZIP is password protected'); ?>" autocomplete="off" />
                            </div>
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <div class="col-sm-offset-4 col-sm-8">
                                <button type="submit" class="btn btn-warning" style="font-weight:700;" onclick="return confirm('<?php echo __('Restore will apply backup records over current data (A safety snapshot will be created automatically first). Proceed?'); ?>');"><span class="fa fa-upload"></span> <?php echo __('Upload & Safe Restore'); ?></button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Manual Trigger & Quick Actions -->
            <div class="col-md-6">
                <div class="well" style="background:#fff;border:1px solid #e2e8f0;border-radius:8px;">
                    <h4 style="margin-top:0;font-size:15px;font-weight:800;color:#0f172a;border-bottom:1px solid #f1f5f9;padding-bottom:8px;"><span class="fa fa-cogs text-info"></span> <?php echo __('Quick Operations'); ?></h4>
                    <form method="post" action="<?php echo $this->Html->url(array('controller'=>'Ardaccounts','action'=>'backups',$currentProjectId)); ?>" style="margin-bottom:12px;">
                        <input type="hidden" name="data[Ardbackup][task]" value="run_auto_now" />
                        <input type="hidden" name="data[Ardbackup][project_id]" value="<?php echo (int)$currentProjectId; ?>" />
                        <button type="submit" class="btn btn-primary btn-block" style="font-weight:700;padding:10px;"><span class="fa fa-play"></span> <?php echo __('Trigger Background Auto-Backup Worker Now'); ?></button>
                    </form>
                    <div style="font-size:12px;color:#64748b;line-height:1.5;">
                        <p style="margin-bottom:6px;"><span class="fa fa-check text-success"></span> <strong><?php echo __('Agency Admin'); ?>:</strong> <?php echo __('Can backup and restore their isolated agency records safely.'); ?></p>
                        <p style="margin-bottom:6px;"><span class="fa fa-check text-success"></span> <strong><?php echo __('Super Administrator'); ?>:</strong> <?php echo __('Can backup all 152 system tables, files snapshot, and execute selective module exports.'); ?></p>
                        <p style="margin-bottom:0;"><span class="fa fa-check text-success"></span> <strong><?php echo __('Encrypted Archives'); ?>:</strong> <?php echo __('Supports AES password-protected ZIP archives.'); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Backups List Table -->
        <h4 style="margin-top:20px;font-size:16px;font-weight:800;color:#0f172a;"><span class="fa fa-history"></span> <?php echo __('Backup Vault & History'); ?></h4>
        <div class="table-responsive">
            <table class="table table-striped table-bordered" style="vertical-align:middle;">
                <thead style="background:#f1f5f9;">
                    <tr>
                        <th><?php echo __('Date & Time'); ?></th>
                        <?php if($isAdr){ ?><th><?php echo __('Agency'); ?></th><?php } ?>
                        <th><?php echo __('Scope'); ?></th>
                        <th><?php echo __('Backup Name / Tag'); ?></th>
                        <th><?php echo __('Protected'); ?></th>
                        <th><?php echo __('File Size'); ?></th>
                        <th><?php echo __('Format'); ?></th>
                        <th style="min-width:280px;"><?php echo __('Actions'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($backupRows)){ ?>
                        <tr><td colspan="<?php echo $isAdr ? 8 : 7; ?>" class="text-center" style="padding:20px;color:#94a3b8;"><?php echo __('No backups found in vault. Use "Create Backup" or "Instant SQL Export" above.'); ?></td></tr>
                    <?php } else { foreach($backupRows as $row){ $b = $row['Ardbackup']; ?>
                        <tr>
                            <td><strong><?php echo !empty($b['created']) ? date('d-M-Y H:i:s', strtotime((string)$b['created'])) : '-'; ?></strong></td>
                            <?php if($isAdr){ ?>
                                <td><?php echo ((int)$b['project_id'] > 0) ? h(isset($row['Agency']['name']) ? $row['Agency']['name'] : '-') : '<span class="label label-primary">' . __('All Agencies / Global') . '</span>'; ?></td>
                            <?php } ?>
                            <td>
                                <?php if((string)$b['backup_scope'] === 'global'){ ?>
                                    <span class="label label-info"><?php echo __('Full System'); ?></span>
                                <?php } else { ?>
                                    <span class="label label-default"><?php echo __('Agency Data'); ?></span>
                                <?php } ?>
                            </td>
                            <td>
                                <div><strong><?php echo h($b['backup_name']); ?></strong></div>
                                <?php if(!empty($b['notes'])){ ?>
                                    <small class="text-muted"><?php echo h($b['notes']); ?></small>
                                <?php } ?>
                            </td>
                            <td>
                                <?php $isProtected = !empty($b['is_password_protected']); ?>
                                <span class="label label-<?php echo $isProtected ? 'warning' : 'default'; ?>"><?php echo $isProtected ? '<span class="fa fa-lock"></span> ' . __('Encrypted') : __('Standard'); ?></span>
                            </td>
                            <td><strong><?php echo h(isset($b['file_size_human']) ? $b['file_size_human'] : '0 B'); ?></strong></td>
                            <td><span class="label label-success"><?php echo h(strtoupper((string)$b['backup_format'])); ?></span></td>
                            <td style="white-space:nowrap;">
                                <?php echo $this->Html->link('<span class="fa fa-download"></span> ' . __('Download'), array('controller'=>'Ardaccounts','action'=>'downloadbackup',(int)$b['id'],$currentProjectId), array('escape'=>false,'class'=>'btn btn-xs btn-success','style'=>'font-weight:700;')); ?>
                                
                                <form method="post" action="<?php echo $this->Html->url(array('controller'=>'Ardaccounts','action'=>'backups',$currentProjectId)); ?>" style="display:inline-block;margin-left:4px;">
                                    <input type="hidden" name="data[Ardbackup][task]" value="restore_existing" />
                                    <input type="hidden" name="data[Ardbackup][project_id]" value="<?php echo (int)$currentProjectId; ?>" />
                                    <input type="hidden" name="data[Ardbackup][backup_id]" value="<?php echo (int)$b['id']; ?>" />
                                    <?php if($isProtected){ ?>
                                        <input type="password" name="data[Ardbackup][restore_password]" value="" style="width:90px;display:inline-block;height:24px;padding:2px 6px;font-size:11px;" class="form-control" placeholder="<?php echo __('Password'); ?>" autocomplete="off" required />
                                    <?php } ?>
                                    <button type="submit" class="btn btn-xs btn-info" style="font-weight:700;" title="<?php echo __('Safe Restore'); ?>" onclick="return confirm('<?php echo __('Restore this backup? Current data will be replaced with backup data (A safety rollback snapshot will be taken first automatically).'); ?>');"><span class="fa fa-undo"></span> <?php echo __('Restore'); ?></button>
                                </form>

                                <form method="post" action="<?php echo $this->Html->url(array('controller'=>'Ardaccounts','action'=>'backups',$currentProjectId)); ?>" style="display:inline-block;margin-left:4px;">
                                    <input type="hidden" name="data[Ardbackup][task]" value="delete_backup" />
                                    <input type="hidden" name="data[Ardbackup][project_id]" value="<?php echo (int)$currentProjectId; ?>" />
                                    <input type="hidden" name="data[Ardbackup][backup_id]" value="<?php echo (int)$b['id']; ?>" />
                                    <button type="submit" class="btn btn-xs btn-danger" title="<?php echo __('Delete'); ?>" onclick="return confirm('<?php echo __('Delete this backup record and file?'); ?>');"><span class="fa fa-trash"></span></button>
                                </form>
                            </td>
                        </tr>
                    <?php } } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if($isAdr){ ?>
<script type="text/javascript">
jQuery(function($){
    function syncAgencyAndScope(){
        var agencyVal = $('#CreateBackupAgencySelect').val();
        if(agencyVal === '0' || agencyVal === 0 || agencyVal === '') {
            $('#BackupScopeSelect').val('global');
        }
        toggleIncludeFiles();
    }
    function toggleIncludeFiles(){
        var mode = $('#BackupScopeSelect').val() || 'global';
        $('#IncludeFilesWrap').toggle(mode === 'global');
    }
    $(document).on('change', '#CreateBackupAgencySelect', syncAgencyAndScope);
    $(document).on('change', '#BackupScopeSelect', toggleIncludeFiles);
    syncAgencyAndScope();
});
</script>
<?php } ?>
