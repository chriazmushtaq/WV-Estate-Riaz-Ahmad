<style>
/* Modern Professional Modules View */
.wv-mod-header {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 20px 24px;
    margin-bottom: 24px;
    box-shadow: 0 2px 10px rgba(15, 23, 42, 0.04);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
}
.wv-mod-header-left {
    display: flex;
    align-items: center;
    gap: 16px;
}
.wv-mod-icon-badge {
    width: 48px;
    height: 48px;
    border-radius: 10px;
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}
.wv-mod-title {
    margin: 0;
    font-size: 20px;
    font-weight: 800;
    color: #0f172a;
    letter-spacing: -0.02em;
}
.wv-mod-subtitle {
    margin: 4px 0 0 0;
    font-size: 13px;
    color: #64748b;
}

/* Nav Tabs */
.wv-nav-pills {
    display: inline-flex;
    background: #f1f5f9;
    padding: 4px;
    border-radius: 8px;
    gap: 4px;
}
.wv-nav-pills .wv-nav-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    font-size: 13px;
    font-weight: 700;
    color: #475569;
    border-radius: 6px;
    text-decoration: none;
    transition: all 0.2s ease;
    border: none;
    background: transparent;
}
.wv-nav-pills .wv-nav-link:hover {
    color: #1e293b;
    background: rgba(255, 255, 255, 0.6);
}
.wv-nav-pills .wv-nav-link.active {
    background: #ffffff;
    color: #2563eb;
    box-shadow: 0 2px 6px rgba(0,0,0,0.06);
}

/* Stat Cards */
.wv-stat-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 24px;
    box-shadow: 0 2px 10px rgba(15, 23, 42, 0.04);
    display: flex;
    align-items: center;
    gap: 18px;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.wv-stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(15, 23, 42, 0.08);
}
.wv-stat-icon {
    width: 52px;
    height: 52px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    flex-shrink: 0;
}
.wv-stat-blue { background: #eff6ff; color: #2563eb; }
.wv-stat-green { background: #f0fdf4; color: #16a34a; }
.wv-stat-amber { background: #fffbeb; color: #d97706; }

.wv-stat-value {
    font-size: 26px;
    font-weight: 800;
    color: #0f172a;
    line-height: 1;
}
.wv-stat-label {
    font-size: 14px;
    font-weight: 700;
    color: #334155;
    margin-top: 4px;
}
.wv-stat-desc {
    font-size: 12px;
    color: #64748b;
    margin-top: 2px;
}

/* Main Table Card */
.wv-table-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(15, 23, 42, 0.04);
    overflow: hidden;
    margin-bottom: 30px;
}
.wv-table-header {
    padding: 16px 24px;
    background: #fafafa;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
}
.wv-table-title {
    margin: 0;
    font-size: 15px;
    font-weight: 800;
    color: #0f172a;
    display: flex;
    align-items: center;
    gap: 10px;
}
.wv-table {
    width: 100%;
    margin-bottom: 0;
}
.wv-table th {
    background: #0f172a;
    color: #f8fafc !important;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    padding: 14px 16px;
    border: none !important;
    vertical-align: middle;
}
.wv-table td {
    padding: 16px;
    vertical-align: middle !important;
    border-color: #f1f5f9 !important;
    font-size: 13px;
    color: #334155;
}
.wv-table tr:hover td {
    background-color: #f8fafc;
}

/* Module Info Cell */
.wv-module-avatar {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    background: #eff6ff;
    color: #2563eb;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
    border: 1px solid #dbeafe;
}
.wv-module-name {
    font-size: 14px;
    font-weight: 700;
    color: #0f172a;
}
.wv-module-desc {
    font-size: 12px;
    color: #64748b;
    margin-top: 3px;
    line-height: 1.4;
    max-width: 460px;
}

/* Badges */
.wv-path-code {
    background: #f1f5f9;
    color: #0f172a;
    padding: 4px 8px;
    border-radius: 4px;
    font-family: monospace;
    font-size: 12px;
    border: 1px solid #e2e8f0;
}
.wv-version-badge {
    background: #e0f2fe;
    color: #0369a1;
    font-weight: 700;
    font-size: 11px;
    padding: 4px 8px;
    border-radius: 6px;
    display: inline-block;
}
.wv-status-active {
    background: #dcfce7;
    color: #15803d;
    font-weight: 700;
    font-size: 12px;
    padding: 5px 10px;
    border-radius: 20px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border: 1px solid #bbf7d0;
}
.wv-status-active:before {
    content: '';
    width: 7px;
    height: 7px;
    background: #22c55e;
    border-radius: 50%;
    display: inline-block;
}
.wv-status-disabled {
    background: #f1f5f9;
    color: #64748b;
    font-weight: 700;
    font-size: 12px;
    padding: 5px 10px;
    border-radius: 20px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border: 1px solid #e2e8f0;
}

/* Action Buttons */
.wv-btn-action {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    font-size: 12px;
    font-weight: 700;
    border-radius: 6px;
    border: none;
    text-decoration: none !important;
    transition: all 0.15s ease;
}
.wv-btn-settings {
    background: #d11242;
    color: #ffffff !important;
}
.wv-btn-settings:hover {
    background: #b00d35;
}
.wv-btn-disable {
    background: #f59e0b;
    color: #ffffff !important;
}
.wv-btn-disable:hover {
    background: #d97706;
}
.wv-btn-enable {
    background: #10b981;
    color: #ffffff !important;
}
.wv-btn-enable:hover {
    background: #059669;
}
.wv-btn-export {
    background: #0284c7;
    color: #ffffff !important;
}
.wv-btn-export:hover {
    background: #0369a1;
}
.wv-btn-delete {
    background: #ef4444;
    color: #ffffff !important;
    padding: 6px 10px;
}
.wv-btn-delete:hover {
    background: #dc2626;
}
</style>

<div class="page-content-wrap">
    <!-- Top Header Bar -->
    <div class="wv-mod-header">
        <div class="wv-mod-header-left">
            <div class="wv-mod-icon-badge">
                <i class="fa fa-cubes"></i>
            </div>
            <div>
                <h1 class="wv-mod-title"><?php echo __('Module Management & Extensions'); ?></h1>
                <p class="wv-mod-subtitle"><?php echo __('Install, customize, enable, and manage custom feature plugins & themes safely.'); ?></p>
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
            <div class="wv-nav-pills">
                <a href="<?php echo $this->Html->url(array('controller' => 'Modules', 'action' => 'index')); ?>" class="wv-nav-link active">
                    <i class="fa fa-cubes"></i> <?php echo __('Installed'); ?>
                </a>
                <a href="<?php echo $this->Html->url(array('controller' => 'Modules', 'action' => 'builder')); ?>" class="wv-nav-link">
                    <i class="fa fa-magic"></i> <?php echo __('Module Builder'); ?>
                </a>
            </div>

            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modalInstallModule" style="font-weight: 700; border-radius: 6px; padding: 8px 16px; display: inline-flex; align-items: center; gap: 6px;">
                <i class="fa fa-cloud-upload"></i> <?php echo __('Install Module ZIP'); ?>
            </button>
        </div>
    </div>

    <!-- KPI Summary Row -->
    <div class="row">
        <div class="col-md-4">
            <div class="wv-stat-card">
                <div class="wv-stat-icon wv-stat-blue">
                    <i class="fa fa-cubes"></i>
                </div>
                <div>
                    <div class="wv-stat-value"><?php echo (int)$totalModules; ?></div>
                    <div class="wv-stat-label"><?php echo __('Total Modules'); ?></div>
                    <div class="wv-stat-desc"><?php echo __('Registered feature extensions'); ?></div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="wv-stat-card">
                <div class="wv-stat-icon wv-stat-green">
                    <i class="fa fa-check-circle"></i>
                </div>
                <div>
                    <div class="wv-stat-value" style="color: #16a34a;"><?php echo (int)$activeModules; ?></div>
                    <div class="wv-stat-label"><?php echo __('Active Modules'); ?></div>
                    <div class="wv-stat-desc"><?php echo __('Running live in Super Admin & frontend'); ?></div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="wv-stat-card">
                <div class="wv-stat-icon wv-stat-amber">
                    <i class="fa fa-pause-circle"></i>
                </div>
                <div>
                    <div class="wv-stat-value" style="color: #d97706;"><?php echo (int)$inactiveModules; ?></div>
                    <div class="wv-stat-label"><?php echo __('Disabled Modules'); ?></div>
                    <div class="wv-stat-desc"><?php echo __('Safely hidden without data loss'); ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modules Table Card -->
    <div class="wv-table-card">
        <div class="wv-table-header">
            <h3 class="wv-table-title">
                <i class="fa fa-list-ul text-primary"></i> <?php echo __('Installed Application Modules'); ?>
            </h3>
            <div style="font-size: 13px; color: #64748b;">
                <?php echo __('Showing'); ?> <strong><?php echo count($modules); ?></strong> <?php echo __('installed extensions'); ?>
            </div>
        </div>

        <?php if (empty($modules)): ?>
            <div class="text-center" style="padding: 60px 20px;">
                <div style="width: 70px; height: 70px; border-radius: 50%; background: #eff6ff; color: #3b82f6; display: inline-flex; align-items: center; justify-content: center; font-size: 32px; margin-bottom: 16px;">
                    <i class="fa fa-cubes"></i>
                </div>
                <h4 style="font-weight: 700; color: #0f172a; margin-bottom: 6px;"><?php echo __('No Custom Modules Installed Yet'); ?></h4>
                <p class="text-muted" style="max-width: 480px; margin: 0 auto 20px auto; font-size: 13px;">
                    <?php echo __('Use the Module Builder to scaffold your first custom feature or upload a module .zip package.'); ?>
                </p>
                <div style="display: flex; justify-content: center; gap: 10px;">
                    <a href="<?php echo $this->Html->url(array('action' => 'builder')); ?>" class="btn btn-primary" style="font-weight: 700;">
                        <i class="fa fa-magic"></i> <?php echo __('Build New Module'); ?>
                    </a>
                    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modalInstallModule" style="font-weight: 700;">
                        <i class="fa fa-cloud-upload"></i> <?php echo __('Upload Module ZIP'); ?>
                    </button>
                </div>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table wv-table">
                    <thead>
                        <tr>
                            <th style="width: 40px;" class="text-center">#</th>
                            <th><?php echo __('Module Details'); ?></th>
                            <th><?php echo __('Plugin Path'); ?></th>
                            <th style="width: 90px;" class="text-center"><?php echo __('Version'); ?></th>
                            <th><?php echo __('Author'); ?></th>
                            <th style="width: 110px;" class="text-center"><?php echo __('Status'); ?></th>
                            <th style="width: 140px;"><?php echo __('Installed Date'); ?></th>
                            <th style="width: 250px;" class="text-center"><?php echo __('Actions'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($modules as $index => $mod): 
                            $m = $mod['SystemModule'];
                            $isActive = !empty($m['status']);
                        ?>
                            <tr>
                                <td class="text-center" style="font-weight: 700; color: #94a3b8;"><?php echo $index + 1; ?></td>
                                <td>
                                    <div style="display: flex; align-items: flex-start; gap: 12px;">
                                        <div class="wv-module-avatar">
                                            <i class="<?php echo h(!empty($m['icon']) ? $m['icon'] : 'fa fa-cube'); ?>"></i>
                                        </div>
                                        <div>
                                            <div class="wv-module-name"><?php echo h($m['name']); ?></div>
                                            <?php if (!empty($m['description'])): ?>
                                                <div class="wv-module-desc"><?php echo h($m['description']); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="wv-path-code">app/Plugin/<?php echo h($m['slug']); ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="wv-version-badge">v<?php echo h($m['version']); ?></span>
                                </td>
                                <td>
                                    <?php if (!empty($m['author_url'])): ?>
                                        <a href="<?php echo h($m['author_url']); ?>" target="_blank" rel="noopener noreferrer" style="font-weight: 600; color: #2563eb; text-decoration: none;">
                                            <?php echo h($m['author']); ?> <i class="fa fa-external-link" style="font-size: 10px;"></i>
                                        </a>
                                    <?php else: ?>
                                        <span style="font-weight: 600; color: #475569;"><?php echo h(!empty($m['author']) ? $m['author'] : 'System'); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($isActive): ?>
                                        <span class="wv-status-active"><?php echo __('Active'); ?></span>
                                    <?php else: ?>
                                        <span class="wv-status-disabled"><?php echo __('Disabled'); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td style="font-size: 12px; color: #64748b;">
                                    <?php echo h(!empty($m['installed_at']) ? $m['installed_at'] : $m['updated_at']); ?>
                                </td>
                                <td class="text-center">
                                    <div style="display: inline-flex; align-items: center; gap: 6px; flex-wrap: wrap; justify-content: center;">
                                        <!-- Settings Button (for Theme Customizer & settings) -->
                                        <?php if (strtolower($m['slug']) === 'realestatetheme' || strtolower($m['slug']) === 'real_estate_theme'): ?>
                                            <a href="<?php echo $this->Html->url('/admin/RealEstateThemes/settings'); ?>" 
                                               class="wv-btn-action wv-btn-settings" 
                                               title="<?php echo __('Open Theme Customizer & Settings'); ?>">
                                                <i class="fa fa-sliders"></i> <?php echo __('Settings'); ?>
                                            </a>
                                        <?php endif; ?>

                                        <!-- Toggle Status Button -->
                                        <a href="<?php echo $this->Html->url(array('action' => 'toggle_status', $m['id'])); ?>" 
                                           class="wv-btn-action <?php echo $isActive ? 'wv-btn-disable' : 'wv-btn-enable'; ?>" 
                                           title="<?php echo $isActive ? __('Disable Module') : __('Enable Module'); ?>"
                                           onclick="return confirm('<?php echo $isActive ? __('Are you sure you want to disable this module? Its menu will be hidden.') : __('Enable this module now?'); ?>');">
                                            <i class="fa <?php echo $isActive ? 'fa-pause' : 'fa-play'; ?>"></i>
                                            <?php echo $isActive ? __('Disable') : __('Enable'); ?>
                                        </a>

                                        <!-- Export ZIP Button -->
                                        <a href="<?php echo $this->Html->url(array('action' => 'export', $m['id'])); ?>" 
                                           class="wv-btn-action wv-btn-export" 
                                           title="<?php echo __('Download Module ZIP Package'); ?>">
                                            <i class="fa fa-download"></i> <?php echo __('Export'); ?>
                                        </a>

                                        <!-- Uninstall Button -->
                                        <?php if (empty($m['is_system'])): ?>
                                            <button type="button" class="wv-btn-action wv-btn-delete" 
                                                    data-toggle="modal" data-target="#modalUninstall<?php echo $m['id']; ?>"
                                                    title="<?php echo __('Uninstall Module'); ?>">
                                                <i class="fa fa-trash-o"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Uninstall Modal -->
                                    <?php if (empty($m['is_system'])): ?>
                                        <div class="modal fade" id="modalUninstall<?php echo $m['id']; ?>" tabindex="-1" role="dialog">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content text-left" style="border-radius: 10px; overflow: hidden;">
                                                    <form method="post" action="<?php echo $this->Html->url(array('action' => 'uninstall', $m['id'])); ?>">
                                                        <div class="modal-header" style="background: #ef4444; color: white; padding: 14px 20px;">
                                                            <button type="button" class="close text-white" data-dismiss="modal" style="opacity: 0.9;">&times;</button>
                                                            <h4 class="modal-title" style="font-weight: 700;"><i class="fa fa-warning"></i> <?php echo __('Uninstall Module'); ?>: <?php echo h($m['name']); ?></h4>
                                                        </div>
                                                        <div class="modal-body" style="padding: 20px;">
                                                            <p style="font-size: 14px;"><?php echo __('Are you sure you want to uninstall module <strong>%s</strong>?', h($m['name'])); ?></p>
                                                            <p class="text-muted" style="font-size: 13px;"><?php echo __('This will remove the module files from <code>app/Plugin/%s</code> and clean up its sidebar menu.', h($m['slug'])); ?></p>
                                                            <div class="checkbox" style="margin-top: 15px; background: #fff1f2; padding: 12px 14px; border-radius: 6px; border: 1px solid #fecdd3;">
                                                                <label style="margin-bottom: 0;">
                                                                    <input type="checkbox" name="drop_tables" value="1">
                                                                    <strong class="text-danger"><?php echo __('Drop custom database tables created by this module (if uninstall.sql exists)'); ?></strong>
                                                                </label>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer" style="background: #f8fafc; border-top: 1px solid #e2e8f0;">
                                                            <button type="button" class="btn btn-default" data-dismiss="modal" style="font-weight: 600;"><?php echo __('Cancel'); ?></button>
                                                            <button type="submit" class="btn btn-danger" style="font-weight: 700; background: #ef4444;"><i class="fa fa-trash"></i> <?php echo __('Confirm Uninstall'); ?></button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Discovered Unmanaged Plugins Panel (if any) -->
    <?php if (!empty($discoveredPlugins)): ?>
        <div class="wv-table-card">
            <div class="wv-table-header">
                <h3 class="wv-table-title text-info">
                    <i class="fa fa-folder-open"></i> <?php echo __('Discovered Unregistered Plugins on Disk'); ?>
                </h3>
            </div>
            <div style="padding: 20px;">
                <p class="text-muted" style="font-size: 13px; margin-bottom: 15px;"><?php echo __('The following plugin folders exist under <code>app/Plugin/</code> but are not yet registered in the Module Management System:'); ?></p>
                <ul class="list-group" style="margin-bottom: 0;">
                    <?php foreach ($discoveredPlugins as $dp): ?>
                        <li class="list-group-item" style="display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; border-radius: 6px; margin-bottom: 6px;">
                            <div>
                                <strong style="font-size: 14px; color: #0f172a;"><?php echo h($dp['folder']); ?></strong>
                                <span class="text-muted" style="margin-left: 10px;"><code class="wv-path-code">app/Plugin/<?php echo h($dp['folder']); ?></code></span>
                                <?php if (!empty($dp['has_manifest'])): ?>
                                    <span class="label label-success" style="margin-left: 8px;"><i class="fa fa-check"></i> Has module.json</span>
                                <?php endif; ?>
                            </div>
                            <div>
                                <span class="badge" style="background: #94a3b8;"><?php echo __('Unregistered'); ?></span>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Modal: Install Module ZIP -->
<div class="modal fade" id="modalInstallModule" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content" style="border-radius: 12px; overflow: hidden; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
            <form method="post" action="<?php echo $this->Html->url(array('action' => 'install')); ?>" enctype="multipart/form-data">
                <div class="modal-header" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 16px 24px;">
                    <button type="button" class="close text-white" data-dismiss="modal" style="opacity: 0.9;">&times;</button>
                    <h4 class="modal-title" style="font-weight: 800; display: flex; align-items: center; gap: 8px;">
                        <i class="fa fa-cloud-upload"></i> <?php echo __('Install Module from ZIP Package'); ?>
                    </h4>
                </div>
                <div class="modal-body" style="padding: 24px;">
                    <p class="text-muted" style="font-size: 13px; line-height: 1.5; margin-bottom: 20px;">
                        <?php echo __('Select a module ZIP archive. The installer will automatically validate <code>module.json</code>, extract files to <code>app/Plugin/</code>, execute database migrations, and register sidebar menus.'); ?>
                    </p>
                    <div class="form-group">
                        <label style="font-weight: 700; font-size: 13px; color: #0f172a; margin-bottom: 8px;"><?php echo __('Choose Module Archive (.zip):'); ?></label>
                        <input type="file" name="module_zip" class="form-control" accept=".zip" required style="padding: 10px; height: auto; border: 2px dashed #cbd5e1; border-radius: 8px; background: #f8fafc;">
                    </div>
                    <div class="alert alert-info" style="margin-top: 15px; font-size: 12px; margin-bottom: 0; border-radius: 6px; background: #eff6ff; border-color: #bfdbfe; color: #1e40af;">
                        <i class="fa fa-info-circle"></i> <?php echo __('The ZIP file must contain a valid <code>module.json</code> manifest with module metadata, routes, and menu definitions.'); ?>
                    </div>
                </div>
                <div class="modal-footer" style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 14px 24px;">
                    <button type="button" class="btn btn-default" data-dismiss="modal" style="font-weight: 600;"><?php echo __('Cancel'); ?></button>
                    <button type="submit" class="btn btn-success" style="font-weight: 700; background: #10b981; border-color: #10b981;"><i class="fa fa-cloud-upload"></i> <?php echo __('Upload & Install Module'); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>
