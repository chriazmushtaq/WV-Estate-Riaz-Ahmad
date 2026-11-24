<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php echo $this->Html->charset(); ?>
    <title><?php echo __('Installation'); ?></title>
    <?php
        echo $this->Html->meta('icon');
        echo $this->Html->css('bootstrap.min');
        echo $this->Html->css('style');
        echo $this->Html->script('jquery-1.8.2.min');
        echo $this->Html->script('html5shiv');
        echo $this->Html->script('respond.min');
        echo $this->Html->script('bootstrap.min');
        echo $this->fetch('script');
        echo $this->Js->writeBuffer();
    ?>
</head>
<body>
<div class="container">
    <div class="row">
        <div class="col-md-12 mrg">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <?php echo $this->Html->image('logo-website.fw.png', array('class' => 'img-responsive')); ?>
                </div>
                <div class="panel-body">
                    <?php echo $this->Session->flash(); ?>
                    <p class="btn btn-primary"><?php echo __('Step 4 of 4'); ?></p>
                    <h3><?php echo __('Website Setup'); ?></h3>

                    <div class="alert alert-warning">
                        <?php echo __('This step imports SQL and writes main configuration. Use an empty/new database for first install.'); ?>
                    </div>

                    <?php echo $this->Form->create('Installer', array('url' => array('controller' => 'installers', 'action' => 'step3'), 'class' => 'form-horizontal')); ?>
                    <div class="form-group">
                        <label class="col-sm-2 control-label"><?php echo __('Site Name'); ?></label>
                        <div class="col-sm-10">
                            <?php echo $this->Form->input('name', array('label' => false, 'div' => false, 'class' => 'form-control', 'required' => true)); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label"><?php echo __('Domain Name'); ?></label>
                        <div class="col-sm-10">
                            <?php echo $this->Form->input('domain_name', array('label' => false, 'div' => false, 'class' => 'form-control', 'required' => true, 'placeholder' => 'https://portal.whitevillas.com.pk')); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label"><?php echo __('Meta Title'); ?></label>
                        <div class="col-sm-10">
                            <?php echo $this->Form->input('meta_title', array('label' => false, 'div' => false, 'class' => 'form-control', 'required' => true)); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label"><?php echo __('Meta Description'); ?></label>
                        <div class="col-sm-10">
                            <?php echo $this->Form->input('meta_desc', array('label' => false, 'div' => false, 'class' => 'form-control')); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label"><?php echo __('Timezone'); ?></label>
                        <div class="col-sm-10">
                            <?php
                                echo $this->Form->select(
                                    'timezone',
                                    (array)$timezoneOptions,
                                    array(
                                        'empty' => __('Please Select Timezone'),
                                        'class' => 'form-control',
                                        'required' => true,
                                        'label' => false,
                                        'div' => false
                                    )
                                );
                            ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label"><?php echo __('Organization Name'); ?></label>
                        <div class="col-sm-10">
                            <?php echo $this->Form->input('organization_name', array('label' => false, 'div' => false, 'class' => 'form-control', 'required' => true)); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label"><?php echo __('Organization Email'); ?></label>
                        <div class="col-sm-10">
                            <?php echo $this->Form->input('email', array('label' => false, 'div' => false, 'class' => 'form-control', 'required' => true, 'type' => 'email')); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label"><?php echo __('Installation Mode'); ?></label>
                        <div class="col-sm-10" style="padding-top:7px;">
                            <div class="radio" style="margin-bottom:15px;">
                                <label style="font-weight:600; font-size:14px; color:#2c3e50;">
                                    <input type="radio" name="data[Installer][installdata]" id="installdata_clean" value="0" <?php echo empty($this->request->data['Installer']['installdata']) ? 'checked' : ''; ?>>
                                    <span class="label label-success" style="font-size:12px; margin-right:5px;"><?php echo __('Recommended'); ?></span>
                                    <strong><?php echo __('Fresh Clean Installation (Production)'); ?></strong>
                                    <div class="text-muted" style="font-weight:normal; font-size:12px; margin-top:4px; margin-left:20px;">
                                        <?php echo __('Creates a clean enterprise database with all tables, only Super Admin (admin), pre-configured user roles, email/SMS/letter templates, auto-ledger provisioning, and an empty media gallery. Zero demo or sample data.'); ?>
                                    </div>
                                </label>
                            </div>
                            <div class="radio">
                                <label style="font-weight:600; font-size:14px; color:#34495e;">
                                    <input type="radio" name="data[Installer][installdata]" id="installdata_demo" value="1" <?php echo !empty($this->request->data['Installer']['installdata']) ? 'checked' : ''; ?>>
                                    <span class="label label-default" style="font-size:12px; margin-right:5px;"><?php echo __('Demo / Testing'); ?></span>
                                    <strong><?php echo __('Install with Sample / Demo Data'); ?></strong>
                                    <div class="text-muted" style="font-weight:normal; font-size:12px; margin-top:4px; margin-left:20px;">
                                        <?php echo __('Populates the database with demo properties, mock leads, sample agencies, agents, invoices, and accounting records for testing.'); ?>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <?php echo $this->Form->hidden('step3', array('value' => 1)); ?>

                    <div class="form-group">
                        <div class="col-sm-offset-2 col-sm-10">
                            <?php echo $this->Html->link(__('Back'), array('action' => 'step2'), array('class' => 'btn btn-default')); ?>
                            <?php echo $this->Form->button(__('Install Now'), array('class' => 'btn btn-success')); ?>
                        </div>
                    </div>
                    <?php echo $this->Form->end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
