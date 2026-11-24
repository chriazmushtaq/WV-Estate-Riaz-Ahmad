<div class="page-title">
    <h2><span class="fa fa-cloud-upload"></span> <?php echo __('App Version Control & Server APK Updates'); ?></h2>
</div>

<div class="page-content-wrap">
    <?php echo $this->element('app_management_nav'); ?>

    <?php echo $this->Form->create('AppSetting', array('type' => 'file', 'class' => 'form-horizontal')); ?>

    <div class="row">
        <!-- App Version & APK Section -->
        <div class="col-md-6">
            <div class="panel panel-default" style="border-radius: 8px;">
                <div class="panel-heading" style="background: #0288D1; color: #fff;">
                    <h3 class="panel-title" style="color: #fff; font-weight: bold;"><i class="fa fa-code-fork"></i> <?php echo __('App Versioning & Update Strategy'); ?></h3>
                </div>
                <div class="panel-body">
                    <div class="form-group">
                        <label class="col-md-4 control-label"><?php echo __('App Name'); ?></label>
                        <div class="col-md-8">
                            <?php echo $this->Form->input('app_name', array('class' => 'form-control', 'label' => false, 'div' => false)); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-4 control-label"><?php echo __('Package Name'); ?></label>
                        <div class="col-md-8">
                            <?php echo $this->Form->input('package_name', array('class' => 'form-control', 'label' => false, 'div' => false)); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-4 control-label"><?php echo __('Latest Released Version'); ?> <span class="text-danger">*</span></label>
                        <div class="col-md-8">
                            <?php echo $this->Form->input('current_version', array('class' => 'form-control', 'placeholder' => 'e.g. 1.0.0', 'label' => false, 'div' => false)); ?>
                            <span class="help-block"><?php echo __('The latest release version (e.g. 1.0.1, 2.0.0).'); ?></span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-4 control-label"><?php echo __('Minimum Required Version'); ?></label>
                        <div class="col-md-8">
                            <?php echo $this->Form->input('min_version', array('class' => 'form-control', 'placeholder' => 'e.g. 1.0.0', 'label' => false, 'div' => false)); ?>
                            <span class="help-block"><?php echo __('Apps with versions below this will be forced to update.'); ?></span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-4 control-label"><?php echo __('Mandatory Update Lock'); ?></label>
                        <div class="col-md-8">
                            <?php echo $this->Form->input('force_update', array(
                                'type' => 'select',
                                'options' => array(
                                    '0' => __('NO (Optional Update - Users can dismiss)'),
                                    '1' => __('YES (MANDATORY LOCK - App will NOT work without updating)')
                                ),
                                'class' => 'form-control',
                                'label' => false,
                                'div' => false
                            )); ?>
                            <span class="help-block text-danger" style="font-weight: 500;">
                                <?php echo __('If enabled, users cannot bypass the update screen until they install the latest APK.'); ?>
                            </span>
                        </div>
                    </div>

                    <hr style="margin: 15px 0;">

                    <div class="form-group">
                        <label class="col-md-4 control-label"><?php echo __('Upload New APK File'); ?></label>
                        <div class="col-md-8">
                            <input type="file" name="apk_file" class="form-control" accept=".apk,.zip">
                            <span class="help-block"><?php echo __('Upload a new Android APK file. It will be hosted directly on your server.'); ?></span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-4 control-label"><?php echo __('Direct APK Download URL'); ?></label>
                        <div class="col-md-8">
                            <?php echo $this->Form->input('apk_url', array('class' => 'form-control', 'placeholder' => 'http://yoursite.com/downloads/whitevillas-latest.apk', 'label' => false, 'div' => false)); ?>
                            <span class="help-block"><?php echo __('Server download link or external APK hosting link.'); ?></span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-4 control-label"><?php echo __('Release Notes'); ?></label>
                        <div class="col-md-8">
                            <?php echo $this->Form->input('release_notes', array('type' => 'textarea', 'rows' => 3, 'class' => 'form-control', 'placeholder' => 'What is new in this version...', 'label' => false, 'div' => false)); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Maintenance Mode & Support Contacts Section -->
        <div class="col-md-6">
            <div class="panel panel-default" style="border-radius: 8px;">
                <div class="panel-heading" style="background: #E65100; color: #fff;">
                    <h3 class="panel-title" style="color: #fff; font-weight: bold;"><i class="fa fa-wrench"></i> <?php echo __('App Maintenance Mode'); ?></h3>
                </div>
                <div class="panel-body">
                    <div class="form-group">
                        <label class="col-md-4 control-label"><?php echo __('Maintenance Mode'); ?></label>
                        <div class="col-md-8">
                            <?php echo $this->Form->input('maintenance_mode', array(
                                'type' => 'select',
                                'options' => array(
                                    '0' => __('DISABLED (App is fully online and accessible)'),
                                    '1' => __('ENABLED (Block app access and display maintenance screen)')
                                ),
                                'class' => 'form-control',
                                'label' => false,
                                'div' => false
                            )); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-4 control-label"><?php echo __('Maintenance Title'); ?></label>
                        <div class="col-md-8">
                            <?php echo $this->Form->input('maintenance_title', array('class' => 'form-control', 'placeholder' => 'e.g. System Under Maintenance', 'label' => false, 'div' => false)); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-4 control-label"><?php echo __('Maintenance Message'); ?></label>
                        <div class="col-md-8">
                            <?php echo $this->Form->input('maintenance_message', array('type' => 'textarea', 'rows' => 3, 'class' => 'form-control', 'placeholder' => 'Notice shown to users during downtime...', 'label' => false, 'div' => false)); ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel panel-default" style="border-radius: 8px; margin-top: 15px;">
                <div class="panel-heading" style="background: #334155; color: #fff;">
                    <h3 class="panel-title" style="color: #fff; font-weight: bold;"><i class="fa fa-headphones"></i> <?php echo __('Support Contacts (Displayed in App)'); ?></h3>
                </div>
                <div class="panel-body">
                    <div class="form-group">
                        <label class="col-md-4 control-label"><?php echo __('Support Phone'); ?></label>
                        <div class="col-md-8">
                            <?php echo $this->Form->input('support_phone', array('class' => 'form-control', 'placeholder' => '+92 300 1234567', 'label' => false, 'div' => false)); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-4 control-label"><?php echo __('WhatsApp Support'); ?></label>
                        <div class="col-md-8">
                            <?php echo $this->Form->input('support_whatsapp', array('class' => 'form-control', 'placeholder' => '+92 300 1234567', 'label' => false, 'div' => false)); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-4 control-label"><?php echo __('Support Email'); ?></label>
                        <div class="col-md-8">
                            <?php echo $this->Form->input('support_email', array('class' => 'form-control', 'placeholder' => 'support@whitevillas.com', 'label' => false, 'div' => false)); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-body text-center" style="padding: 15px;">
                    <button type="submit" class="btn btn-primary btn-lg" style="padding: 10px 30px; font-weight: bold;">
                        <i class="fa fa-save"></i> <?php echo __('Save App Settings & Update Strategy'); ?>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <?php echo $this->Form->end(); ?>
</div>
