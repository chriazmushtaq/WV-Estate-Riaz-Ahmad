<?php
$app = !empty($setting['AppSetting']) ? $setting['AppSetting'] : array();
$isForceUpdate = !empty($app['force_update']);
$isMaintenance = !empty($app['maintenance_mode']);
?>
<div class="page-title">
    <h2><span class="fa fa-mobile"></span> <?php echo __('Mobile App Management & Control Center'); ?></h2>
</div>

<div class="page-content-wrap">
    <?php echo $this->element('app_management_nav'); ?>

    <!-- Status Cards Row -->
    <div class="row">
        <div class="col-md-3">
            <div class="widget widget-default widget-item-icon" style="border-radius: 8px; border-left: 4px solid #00BCD4;">
                <div class="widget-item-left"><span class="fa fa-code-fork" style="color: #00BCD4;"></span></div>
                <div class="widget-data">
                    <div class="widget-int num-count"><?php echo h(!empty($app['current_version']) ? $app['current_version'] : '1.0.0'); ?></div>
                    <div class="widget-title"><?php echo __('Live App Version'); ?></div>
                    <div class="widget-subtitle">Min: <?php echo h(!empty($app['min_version']) ? $app['min_version'] : '1.0.0'); ?></div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="widget widget-default widget-item-icon" style="border-radius: 8px; border-left: 4px solid <?php echo $isForceUpdate ? '#E91E63' : '#4CAF50'; ?>;">
                <div class="widget-item-left"><span class="fa fa-lock" style="color: <?php echo $isForceUpdate ? '#E91E63' : '#4CAF50'; ?>;"></span></div>
                <div class="widget-data">
                    <div class="widget-int num-count">
                        <?php if ($isForceUpdate): ?>
                            <span class="label label-danger" style="font-size: 13px; font-weight: bold;"><?php echo __('ACTIVE (LOCK)'); ?></span>
                        <?php else: ?>
                            <span class="label label-success" style="font-size: 13px; font-weight: bold;"><?php echo __('OPTIONAL'); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="widget-title"><?php echo __('Mandatory Update Lock'); ?></div>
                    <div class="widget-subtitle"><?php echo $isForceUpdate ? __('Users MUST update to use app') : __('Users can update anytime'); ?></div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="widget widget-default widget-item-icon" style="border-radius: 8px; border-left: 4px solid <?php echo $isMaintenance ? '#FF9800' : '#4CAF50'; ?>;">
                <div class="widget-item-left"><span class="fa fa-wrench" style="color: <?php echo $isMaintenance ? '#FF9800' : '#4CAF50'; ?>;"></span></div>
                <div class="widget-data">
                    <div class="widget-int num-count">
                        <?php if ($isMaintenance): ?>
                            <span class="label label-warning" style="font-size: 13px; font-weight: bold;"><?php echo __('MAINTENANCE ON'); ?></span>
                        <?php else: ?>
                            <span class="label label-success" style="font-size: 13px; font-weight: bold;"><?php echo __('ALL SYSTEMS ONLINE'); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="widget-title"><?php echo __('Maintenance Mode'); ?></div>
                    <div class="widget-subtitle"><?php echo $isMaintenance ? __('App is currently paused') : __('App is operating normally'); ?></div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="widget widget-default widget-item-icon" style="border-radius: 8px; border-left: 4px solid #673AB7;">
                <div class="widget-item-left"><span class="fa fa-paper-plane" style="color: #673AB7;"></span></div>
                <div class="widget-data">
                    <div class="widget-int num-count"><?php echo $totalBroadcasts; ?></div>
                    <div class="widget-title"><?php echo __('Total Broadcasts Sent'); ?></div>
                    <div class="widget-subtitle"><?php echo $totalInAppDelivered; ?> <?php echo __('in-app notices delivered'); ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Action / APK Info Banner -->
    <div class="row" style="margin-top: 10px;">
        <div class="col-md-12">
            <div class="panel panel-default" style="border-radius: 8px;">
                <div class="panel-heading" style="background: #0F172A; color: #fff;">
                    <h3 class="panel-title" style="color: #fff; font-weight: bold;"><i class="fa fa-android"></i> <?php echo __('Live Server APK & Fast Actions'); ?></h3>
                    <div class="pull-right">
                        <?php echo $this->Html->link('<i class="fa fa-cog"></i> ' . __('Configure Version & APK'), array('action' => 'version_control'), array('escape' => false, 'class' => 'btn btn-info btn-sm')); ?>
                        <?php echo $this->Html->link('<i class="fa fa-paper-plane"></i> ' . __('Send New Notification'), array('action' => 'send_notification'), array('escape' => false, 'class' => 'btn btn-success btn-sm')); ?>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-7">
                            <h4 style="margin-top: 0; color: #1E293B; font-weight: bold;"><?php echo h(!empty($app['app_name']) ? $app['app_name'] : 'White Villas Mobile App'); ?></h4>
                            <p style="color: #64748B; font-size: 13px;">
                                <strong><?php echo __('Direct APK Download URL:'); ?></strong><br>
                                <a href="<?php echo h(!empty($app['apk_url']) ? $app['apk_url'] : '#'); ?>" target="_blank" style="word-break: break-all; color: #0288D1; font-weight: bold;">
                                    <?php echo h(!empty($app['apk_url']) ? $app['apk_url'] : __('No APK uploaded yet')); ?>
                                </a>
                            </p>
                            <?php if (!empty($app['release_notes'])): ?>
                                <div style="background: #F8FAFC; border: 1px solid #E2E8F0; padding: 10px 14px; border-radius: 6px; margin-top: 10px;">
                                    <strong style="color: #334155; font-size: 12px;"><?php echo __('Latest Release Notes:'); ?></strong>
                                    <p style="margin: 4px 0 0 0; color: #475569; font-size: 12.5px;"><?php echo nl2br(h($app['release_notes'])); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-5">
                            <div class="well" style="margin-bottom: 0; background: #FAF5FF; border-color: #E9D5FF;">
                                <h5 style="color: #6B21A8; font-weight: bold; margin-top: 0;"><i class="fa fa-info-circle"></i> <?php echo __('How Force Update Works'); ?></h5>
                                <p style="font-size: 12px; color: #581C87; line-height: 1.5; margin-bottom: 0;">
                                    When you release a new app build, set <strong>Current Version</strong> and upload the new APK. Turn on <strong>Force Update</strong> to immediately require all installed apps to download the latest APK before opening their account.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Broadcasts & Client App Users Row -->
    <div class="row" style="margin-top: 10px;">
        <div class="col-md-7">
            <div class="panel panel-default" style="border-radius: 8px;">
                <div class="panel-heading">
                    <h3 class="panel-title"><i class="fa fa-history"></i> <?php echo __('Recent In-App Broadcasts'); ?></h3>
                    <div class="pull-right">
                        <?php echo $this->Html->link(__('View All History ➔'), array('action' => 'notifications_history'), array('class' => 'btn btn-default btn-xs')); ?>
                    </div>
                </div>
                <div class="panel-body table-responsive" style="padding: 0;">
                    <table class="table table-bordered table-striped table-hover" style="margin-bottom: 0;">
                        <thead>
                            <tr style="background: #F1F5F9;">
                                <th><?php echo __('Title'); ?></th>
                                <th><?php echo __('Audience'); ?></th>
                                <th><?php echo __('Sent'); ?></th>
                                <th><?php echo __('Date'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($recentBroadcasts)): ?>
                                <?php foreach ($recentBroadcasts as $b): ?>
                                    <?php $item = $b['AppNotificationBroadcast']; ?>
                                    <tr>
                                        <td>
                                            <strong style="color: #0F172A;"><?php echo h($item['title']); ?></strong><br>
                                            <small style="color: #64748B;"><?php echo h(CakeText::truncate($item['message'], 60)); ?></small>
                                        </td>
                                        <td>
                                            <span class="label label-info"><?php echo h(strtoupper(str_replace('_', ' ', $item['target_audience']))); ?></span>
                                        </td>
                                        <td><span class="badge badge-success"><?php echo (int)$item['total_sent']; ?></span></td>
                                        <td><small style="color: #64748B;"><?php echo !empty($item['created']) ? date('d-M-Y H:i', strtotime($item['created'])) : '-'; ?></small></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center" style="padding: 20px; color: #94A3B8;"><?php echo __('No broadcast notifications sent yet.'); ?></td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="panel panel-default" style="border-radius: 8px;">
                <div class="panel-heading">
                    <h3 class="panel-title"><i class="fa fa-users"></i> <?php echo __('Recent App Client Logins'); ?></h3>
                    <div class="pull-right">
                        <?php echo $this->Html->link(__('View All ➔'), array('action' => 'client_activity'), array('class' => 'btn btn-default btn-xs')); ?>
                    </div>
                </div>
                <div class="panel-body table-responsive" style="padding: 0;">
                    <table class="table table-bordered table-striped" style="margin-bottom: 0;">
                        <thead>
                            <tr style="background: #F1F5F9;">
                                <th><?php echo __('Client'); ?></th>
                                <th><?php echo __('Father / Guardian'); ?></th>
                                <th><?php echo __('Last Mobile Login'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($recentAppClients)): ?>
                                <?php foreach ($recentAppClients as $c): ?>
                                    <?php $cli = $c['Client']; ?>
                                    <tr>
                                        <td>
                                            <strong style="color: #0F172A;"><?php echo h($cli['name']); ?></strong><br>
                                            <small style="color: #64748B;"><?php echo h(!empty($cli['phone']) ? $cli['phone'] : (!empty($cli['email']) ? $cli['email'] : 'ID: ' . $cli['id'])); ?></small>
                                        </td>
                                        <td>
                                            <?php
                                            $fatherName = !empty($cli['fathername']) ? trim((string)$cli['fathername']) : '';
                                            $relationType = !empty($cli['relation_type']) ? trim((string)$cli['relation_type']) : '';
                                            if($fatherName !== ''){
                                                echo '<strong style="color:#1e293b;">' . h($fatherName) . '</strong>';
                                                if($relationType !== ''){
                                                    echo '<small class="text-muted" style="display:block;font-size:11px;font-weight:600;">' . h($relationType) . '</small>';
                                                }
                                            } else {
                                                echo '<span class="text-muted">-</span>';
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <span class="label label-success" style="font-size: 10px;"><i class="fa fa-mobile"></i> <?php echo !empty($cli['app_last_login']) ? date('d-M-Y H:i', strtotime($cli['app_last_login'])) : __('Active'); ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="text-center" style="padding: 20px; color: #94A3B8;"><?php echo __('No client app logins recorded yet.'); ?></td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
