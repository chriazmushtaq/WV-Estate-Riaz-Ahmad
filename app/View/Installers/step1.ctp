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
                    <p class="btn btn-primary"><?php echo __('Step 2 of 4'); ?></p>
                    <h3><?php echo __('Installation Terms'); ?></h3>

                    <div class="well" style="max-height:320px; overflow:auto; margin-top:15px;">
                        <p><?php echo __('This installer will configure your application for hosting, write database settings, import database structure/data and create an installation lock file.'); ?></p>
                        <p><?php echo __('Before continuing, ensure you are installing on the correct domain and target database.'); ?></p>
                        <p><?php echo __('You can remove app/tmp/installed.txt only when you intentionally want to reinstall.'); ?></p>
                        <p><?php echo __('Do not run installer on a production database that already contains business data unless you have full backup.'); ?></p>
                    </div>

                    <?php echo $this->Form->create('Installer', array('url' => array('controller' => 'installers', 'action' => 'step2'), 'class' => 'form-horizontal')); ?>
                    <div class="checkbox">
                        <label>
                            <?php echo $this->Form->checkbox('accept', array('hiddenField' => false, 'required' => true)); ?>
                            <?php echo __('I have backup and I accept to continue installation'); ?>
                        </label>
                    </div>

                    <?php echo $this->Html->link(__('Back'), array('action' => 'index'), array('class' => 'btn btn-default')); ?>
                    <?php if (!empty($allRequiredOk)) { ?>
                        <?php echo $this->Form->button(__('Continue to Database Setup'), array('class' => 'btn btn-success')); ?>
                    <?php } else { ?>
                        <?php echo $this->Form->button(__('Continue to Database Setup'), array('class' => 'btn btn-success', 'disabled' => 'disabled')); ?>
                    <?php } ?>
                    <?php echo $this->Form->end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
