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
        echo $this->fetch('meta');
        echo $this->fetch('css');
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

                    <p class="btn btn-primary"><?php echo __('Step 1 of 4'); ?></p>
                    <h3><?php echo __('Server Configuration & Setup Requirements'); ?></h3>

                    <div class="alert alert-info" style="margin-top:10px;">
                        <strong><?php echo __('Server'); ?>:</strong> <?php echo h((string)$serverSoftware); ?>
                        &nbsp;|&nbsp;
                        <strong><?php echo __('PHP'); ?>:</strong> <?php echo h((string)$phpVersion); ?>
                        &nbsp;|&nbsp;
                        <strong><?php echo __('MySQL Client'); ?>:</strong> <?php echo h((string)$mysqlClientVersion); ?>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="panel panel-default">
                                <div class="panel-heading"><strong><?php echo __('Requirements'); ?></strong></div>
                                <div class="table-responsive">
                                    <table class="table table-striped" style="margin-bottom:0;">
                                        <thead>
                                        <tr>
                                            <th><?php echo __('Item'); ?></th>
                                            <th><?php echo __('Status'); ?></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php foreach ((array)$requirements as $row) { ?>
                                            <?php
                                                $ok = !empty($row['ok']);
                                                $required = !empty($row['required']);
                                                $labelClass = $ok ? 'label-success' : ($required ? 'label-danger' : 'label-warning');
                                                $labelText = $ok ? __('OK') : ($required ? __('Required') : __('Optional'));
                                            ?>
                                            <tr>
                                                <td>
                                                    <?php echo h((string)$row['label']); ?>
                                                    <?php if (!$required) { ?><span class="text-muted">(<?php echo __('Recommended'); ?>)</span><?php } ?>
                                                    <div class="text-muted" style="font-size:12px;"><?php echo h((string)$row['value']); ?></div>
                                                </td>
                                                <td><span class="label <?php echo $labelClass; ?>"><?php echo $labelText; ?></span></td>
                                            </tr>
                                        <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="panel panel-default">
                                <div class="panel-heading"><strong><?php echo __('Permissions'); ?></strong></div>
                                <div class="table-responsive">
                                    <table class="table table-striped" style="margin-bottom:0;">
                                        <thead>
                                        <tr>
                                            <th><?php echo __('Path'); ?></th>
                                            <th><?php echo __('Status'); ?></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php foreach ((array)$permissions as $row) { ?>
                                            <?php
                                                $ok = !empty($row['ok']);
                                                $required = !empty($row['required']);
                                                $labelClass = $ok ? 'label-success' : ($required ? 'label-danger' : 'label-warning');
                                                $labelText = $ok ? __('Writable') : h((string)$row['value']);
                                            ?>
                                            <tr>
                                                <td><?php echo h((string)$row['label']); ?></td>
                                                <td><span class="label <?php echo $labelClass; ?>"><?php echo $labelText; ?></span></td>
                                            </tr>
                                        <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if (empty($allRequiredOk)) { ?>
                        <div class="alert alert-danger">
                            <strong><?php echo __('Installer stopped for server compatibility.'); ?></strong><br>
                            <?php echo __('Please select PHP 7.2 up to PHP 8.3 in cPanel MultiPHP Manager. Recommended: PHP 8.1 or 8.2. Then enable PDO, PDO MySQL, MySQLi, MBString, OpenSSL, XML/DOM, and GD or Imagick from PHP Extensions.'); ?>
                        </div>
                    <?php } ?>

                    <?php
                        echo $this->Form->create('Installer', array(
                            'url' => array('controller' => 'installers', 'action' => 'step1'),
                            'id' => 'post_req'
                        ));
                    ?>
                    <?php echo $this->Html->link(__('Refresh'), array('action' => 'index'), array('class' => 'btn btn-default')); ?>
                    <?php if (!empty($allRequiredOk)) { ?>
                        <?php echo $this->Form->button(__('Continue'), array('class' => 'btn btn-success')); ?>
                    <?php } else { ?>
                        <?php echo $this->Form->button(__('Continue'), array('class' => 'btn btn-success', 'disabled' => 'disabled')); ?>
                    <?php } ?>
                    <?php echo $this->Form->end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
