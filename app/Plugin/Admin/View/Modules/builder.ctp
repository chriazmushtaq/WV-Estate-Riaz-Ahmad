<div class="page-title">
    <h2><span class="fa fa-magic"></span> <?php echo __('Module Builder & Scaffolder'); ?></h2>
</div>

<div class="page-content-wrap">
    <?php echo $this->element('modules_nav'); ?>

    <div class="row">
        <div class="col-md-8">
            <div class="panel panel-default" style="border-radius: 8px; box-shadow: 0 1px 4px rgba(0,0,0,0.08);">
                <div class="panel-heading">
                    <h3 class="panel-title font-weight-bold">
                        <span class="fa fa-pencil-square-o"></span> <?php echo __('Scaffold New Application Module'); ?>
                    </h3>
                </div>

                <div class="panel-body">
                    <p class="text-muted" style="margin-bottom: 20px;">
                        <?php echo __('Generate a clean, isolated CakePHP 2 module with starter controllers, views, models, database schema, and manifest without altering any existing codebase.'); ?>
                    </p>

                    <form method="post" action="<?php echo $this->Html->url(array('action' => 'builder')); ?>" class="form-horizontal">
                        <!-- Module Name -->
                        <div class="form-group">
                            <label class="col-md-3 control-label"><?php echo __('Module Name'); ?> <span class="text-danger">*</span></label>
                            <div class="col-md-9">
                                <input type="text" name="data[ModuleBuilder][name]" id="mod_name" class="form-control" placeholder="e.g. SMS Gateway, Payment Gateway V2, Analytics Hub" required>
                                <span class="help-block text-muted"><?php echo __('Human-readable title displayed in module lists and menus.'); ?></span>
                            </div>
                        </div>

                        <!-- Module Slug -->
                        <div class="form-group">
                            <label class="col-md-3 control-label"><?php echo __('Module Slug / Plugin Name'); ?> <span class="text-danger">*</span></label>
                            <div class="col-md-9">
                                <input type="text" name="data[ModuleBuilder][slug]" id="mod_slug" class="form-control" placeholder="e.g. SmsGateway, AnalyticsHub" required pattern="[A-Za-z0-9_]+">
                                <span class="help-block text-muted"><?php echo __('Plugin folder name inside <code>app/Plugin/</code> (CamelCase, letters and numbers only).'); ?></span>
                            </div>
                        </div>

                        <!-- Version & Author -->
                        <div class="form-group">
                            <label class="col-md-3 control-label"><?php echo __('Version & Author'); ?></label>
                            <div class="col-md-4">
                                <input type="text" name="data[ModuleBuilder][version]" class="form-control" value="1.0.0" placeholder="1.0.0">
                                <span class="help-block text-muted"><?php echo __('Initial Version'); ?></span>
                            </div>
                            <div class="col-md-5">
                                <input type="text" name="data[ModuleBuilder][author]" class="form-control" value="White Villas Team" placeholder="Author / Company">
                                <span class="help-block text-muted"><?php echo __('Developer / Maintainer'); ?></span>
                            </div>
                        </div>

                        <!-- Icon & Menu Title -->
                        <div class="form-group">
                            <label class="col-md-3 control-label"><?php echo __('Icon & Menu Title'); ?></label>
                            <div class="col-md-4">
                                <div class="input-group">
                                    <span class="input-group-addon"><i id="icon-preview" class="fa fa-cube"></i></span>
                                    <input type="text" name="data[ModuleBuilder][icon]" id="mod_icon" class="form-control" value="fa fa-cube" placeholder="fa fa-cube">
                                </div>
                                <span class="help-block text-muted"><?php echo __('FontAwesome icon class'); ?></span>
                            </div>
                            <div class="col-md-5">
                                <input type="text" name="data[ModuleBuilder][menu_title]" id="mod_menu" class="form-control" placeholder="Sidebar Menu Title">
                                <span class="help-block text-muted"><?php echo __('Text in Super Admin sidebar'); ?></span>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="form-group">
                            <label class="col-md-3 control-label"><?php echo __('Description'); ?></label>
                            <div class="col-md-9">
                                <textarea name="data[ModuleBuilder][description]" class="form-control" rows="3" placeholder="Describe the purpose, features, and capabilities of this module..."></textarea>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="form-group">
                            <div class="col-md-9 col-md-offset-3" style="margin-top: 10px;">
                                <button type="submit" class="btn btn-primary btn-lg" style="font-weight: 600;">
                                    <span class="fa fa-magic"></span> <?php echo __('Generate & Register Module'); ?>
                                </button>
                                <a href="<?php echo $this->Html->url(array('action' => 'index')); ?>" class="btn btn-default btn-lg" style="margin-left: 10px;">
                                    <?php echo __('Cancel'); ?>
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Architecture & Guidance Column -->
        <div class="col-md-4">
            <div class="panel panel-info" style="border-radius: 8px;">
                <div class="panel-heading">
                    <h3 class="panel-title font-weight-bold"><i class="fa fa-info-circle"></i> <?php echo __('Module Standard & Structure'); ?></h3>
                </div>
                <div class="panel-body">
                    <p>Scaffolding will create an independent module with:</p>
                    <ul style="padding-left: 20px; line-height: 1.8;">
                        <li><code>module.json</code> &mdash; Manifest for metadata and auto-menu.</li>
                        <li><code>Controller/</code> &mdash; Isolated action handlers.</li>
                        <li><code>Model/</code> &mdash; Dedicated database model and table.</li>
                        <li><code>View/</code> &mdash; Clean admin templates.</li>
                        <li><code>Config/schema.sql</code> &mdash; Auto-installed database tables.</li>
                        <li><code>Config/uninstall.sql</code> &mdash; Clean teardown logic.</li>
                    </ul>
                    <hr>
                    <div class="alert alert-success" style="font-size: 12px; margin-bottom: 0;">
                        <i class="fa fa-shield"></i> <strong>Safe & Non-Destructive:</strong> Existing deals, accounts, properties, and core CRM tables remain completely untouched.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function(){
    $('#mod_name').on('input', function(){
        var val = $(this).val();
        var camel = val.replace(/[^a-zA-Z0-9 ]/g, '').replace(/(?:^\w|[A-Z]|\b\w)/g, function(letter, index) {
            return letter.toUpperCase();
        }).replace(/\s+/g, '');
        if($('#mod_slug').val() === '' || $('#mod_slug').data('auto') !== false){
            $('#mod_slug').val(camel);
        }
        if($('#mod_menu').val() === '' || $('#mod_menu').data('auto') !== false){
            $('#mod_menu').val(val);
        }
    });
    $('#mod_slug').on('input', function(){
        $(this).data('auto', false);
    });
    $('#mod_menu').on('input', function(){
        $(this).data('auto', false);
    });
    $('#mod_icon').on('input', function(){
        $('#icon-preview').attr('class', $(this).val());
    });
});
</script>
