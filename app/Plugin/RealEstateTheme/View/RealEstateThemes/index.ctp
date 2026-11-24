<?php
$ts = !empty($themeSetting) ? $themeSetting : array();
$topHeaderOn = !empty($ts['top_header_enabled']);
?>
<ul class="breadcrumb">
    <li><a href="<?php echo $this->Html->url('/admin/Dashboards'); ?>"><?php echo __('Dashboard'); ?></a></li>
    <li><a href="<?php echo $this->Html->url('/admin/real_estate_themes'); ?>"><?php echo __('Theme Manager'); ?></a></li>
    <li class="active"><?php echo __('Theme Overview'); ?></li>
</ul>

<div class="page-title">
    <h2><span class="fa fa-paint-brush"></span> <?php echo __('Real Estate Pro Theme Overview'); ?></h2>
</div>

<div class="page-content-wrap">
    <div class="row">
        <div class="col-md-8">
            <div class="panel panel-default" style="border-radius: 4px; box-shadow: 0 1px 4px rgba(0,0,0,0.08);">
                <div class="panel-heading">
                    <h3 class="panel-title font-weight-bold"><i class="fa fa-info-circle text-info"></i> <?php echo __('Theme Status & Information'); ?></h3>
                </div>
                <div class="panel-body">
                    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px; padding: 20px; background: #f8fafc; border-radius: 4px; border: 1px solid #e2e8f0; margin-bottom: 20px;">
                        <div>
                            <h4 style="margin: 0 0 5px 0; font-weight: 700; color: #1e293b;">
                                <i class="fa fa-paint-brush text-primary"></i> Real Estate Pro Luxury Theme
                            </h4>
                            <p class="text-muted" style="margin: 0;">Designed with full hero video backgrounds, luxury split features, dynamic database property showcases, and a customizable top-header bar.</p>
                        </div>
                        <div>
                            <?php if (!empty($isActive)): ?>
                                <span class="label label-success" style="font-size: 14px; padding: 6px 12px; font-weight: bold; border-radius: 4px;">
                                    <i class="fa fa-check-circle"></i> <?php echo __('ACTIVE ON FRONTEND'); ?>
                                </span>
                            <?php else: ?>
                                <span class="label label-default" style="font-size: 14px; padding: 6px 12px; font-weight: bold; border-radius: 4px; background: #94a3b8;">
                                    <i class="fa fa-pause-circle"></i> <?php echo __('DISABLED / INACTIVE'); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="alert alert-light border" style="line-height: 1.8;">
                        <ul class="mb-0">
                            <li><strong>Top Header Status:</strong> <?php echo $topHeaderOn ? '<span class="text-success font-weight-bold">ON (Visible)</span>' : '<span class="text-danger font-weight-bold">OFF (Hidden)</span>'; ?></li>
                            <li><strong>Hero Video:</strong> <code><?php echo h(!empty($ts['hero_video_url']) ? $ts['hero_video_url'] : 'Default Video'); ?></code></li>
                            <li><strong>Safe Fallback:</strong> If disabled via <a href="<?php echo $this->Html->url('/admin/modules'); ?>">Module Manager</a>, the homepage instantly reverts to your default WhiteVillas template.</li>
                        </ul>
                    </div>

                    <div style="margin-top: 20px;">
                        <a href="<?php echo $this->Html->url('/admin/real_estate_themes/settings'); ?>" class="btn btn-primary btn-lg" style="font-weight: 600;">
                            <i class="fa fa-sliders"></i> <?php echo __('Configure Theme Options'); ?>
                        </a>
                        <a href="<?php echo $this->Html->url('/'); ?>" target="_blank" class="btn btn-info btn-lg" style="margin-left: 10px; font-weight: 600;">
                            <i class="fa fa-external-link"></i> <?php echo __('Preview Live Website'); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="panel panel-info" style="border-radius: 4px;">
                <div class="panel-heading">
                    <h3 class="panel-title font-weight-bold"><i class="fa fa-lightbulb-o"></i> <?php echo __('Quick Switch Guide'); ?></h3>
                </div>
                <div class="panel-body" style="font-size: 13px; line-height: 1.7;">
                    <p><strong>To Toggle Top Header:</strong> Go to <a href="<?php echo $this->Html->url('/admin/real_estate_themes/settings'); ?>">Theme Settings</a> and toggle the top notification switch.</p>
                    <p><strong>To Disable Module:</strong> Open <a href="<?php echo $this->Html->url('/admin/modules'); ?>">Module Manager</a> &rarr; click <em>Deactivate</em>.</p>
                </div>
            </div>
        </div>
    </div>
</div>
