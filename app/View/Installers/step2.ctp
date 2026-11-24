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
                    <p class="btn btn-primary"><?php echo __('Step 3 of 4'); ?></p>
                    <h3><?php echo __('Database Connection Settings'); ?></h3>

                    <div class="alert alert-<?php echo !empty($dbFileStatus['ok']) ? 'success' : 'danger'; ?>">
                        <strong><?php echo __('database.php'); ?>:</strong>
                        <?php echo h((string)$dbFileStatus['message']); ?>
                    </div>

                    <div class="alert alert-info">
                        <?php echo __('For cPanel shared hosting, host is usually "localhost". Use the exact prefixed database name and user, then make sure that user is added to the database with ALL PRIVILEGES in cPanel.'); ?>
                    </div>

                    <?php echo $this->Form->create('Installer', array('url' => array('controller' => 'installers', 'action' => 'step2'), 'class' => 'form-horizontal')); ?>
                    <div class="form-group">
                        <label class="col-sm-2 control-label"><?php echo __('Host Name'); ?></label>
                        <div class="col-sm-10">
                            <?php echo $this->Form->input('hostname', array('label' => false, 'div' => false, 'class' => 'form-control', 'required' => true, 'placeholder' => 'localhost')); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label"><?php echo __('Database Name'); ?></label>
                        <div class="col-sm-10">
                            <?php echo $this->Form->input('dbname', array('label' => false, 'div' => false, 'class' => 'form-control', 'required' => true, 'placeholder' => __('cpanelprefix_database'))); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label"><?php echo __('Database User'); ?></label>
                        <div class="col-sm-10">
                            <?php echo $this->Form->input('dbuser', array('label' => false, 'div' => false, 'class' => 'form-control', 'required' => true, 'placeholder' => __('cpanelprefix_dbuser'))); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label"><?php echo __('Database Password'); ?></label>
                        <div class="col-sm-10">
                            <?php echo $this->Form->password('dbpassword', array('label' => false, 'div' => false, 'class' => 'form-control', 'required' => false)); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label"><?php echo __('Database Driver'); ?></label>
                        <div class="col-sm-10">
                            <?php
                                echo $this->Form->radio(
                                    'dbtype',
                                    array('Mysql' => 'Mysql', 'Mysqli' => 'Mysqli'),
                                    array(
                                        'value' => (!empty($this->request->data['Installer']['dbtype']) ? $this->request->data['Installer']['dbtype'] : 'Mysqli'),
                                        'legend' => false,
                                        'hiddenField' => false,
                                        'label' => array('style' => 'margin-right:15px;'),
                                        'separator' => '&nbsp;&nbsp;'
                                    )
                                );
                            ?>
                        </div>
                    </div>

                    <?php echo $this->Form->hidden('dbconnection', array('value' => 1)); ?>

                    <div class="form-group">
                        <div class="col-sm-offset-2 col-sm-10">
                            <?php echo $this->Html->link(__('Back'), array('action' => 'step1'), array('class' => 'btn btn-default')); ?>
                            <?php echo $this->Form->button(__('Test Connection & Save'), array('class' => 'btn btn-success')); ?>
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
