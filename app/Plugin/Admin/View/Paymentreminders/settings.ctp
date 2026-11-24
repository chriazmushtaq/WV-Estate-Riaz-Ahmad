<div class="page-title">
    <div class="title-env">
        <h1 class="title"><?php echo __('Auto Payment Reminder Settings'); ?></h1>
    </div>
</div>
<?php echo $this->Session->flash(); ?>

<style type="text/css">
.wv-auto-settings .panel-heading { font-weight:900; }
.wv-auto-settings .help-block { color:#60758c; font-size:12px; font-weight:700; }
.wv-auto-settings code { background:#f6f9fc; color:#9f002d; display:block; white-space:normal; }
.wv-auto-actions { margin-bottom:12px; text-align:right; }
</style>

<div class="wv-auto-settings col-sm-12">
    <div class="wv-auto-actions">
        <?php
        echo $this->Html->link('<span class="fa fa-arrow-left"></span>&nbsp;'.__('Back to Reminders'), array('action'=>'index'), array('escape'=>false, 'class'=>'btn btn-default'));
        echo '&nbsp;';
        echo $this->Html->link('<span class="fa fa-comment"></span>&nbsp;'.__('SMS Settings'), array('controller'=>'Smssettings', 'action'=>'index'), array('escape'=>false, 'class'=>'btn btn-success'));
        echo '&nbsp;';
        echo $this->Html->link('<span class="fa fa-envelope"></span>&nbsp;'.__('Email Settings'), array('controller'=>'Emailsettings', 'action'=>'index'), array('escape'=>false, 'class'=>'btn btn-info'));
        echo '&nbsp;';
        echo $this->Form->postLink('<span class="fa fa-play"></span>&nbsp;'.__('Run Auto Reminder Now'), array('action'=>'runauto'), array('escape'=>false, 'class'=>'btn btn-primary'), __('Run auto reminders now?'));
        ?>
    </div>

    <?php echo $this->Form->create('PaymentReminderSetting', array('controller'=>'Paymentreminders', 'action'=>'settings', 'class'=>'form-horizontal')); ?>
    <?php echo $this->Form->hidden('id', array('value'=>1)); ?>

    <div class="panel panel-default">
        <div class="panel-heading"><?php echo __('Auto Schedule'); ?></div>
        <div class="panel-body">
            <div class="form-group">
                <label class="col-sm-3 control-label"><?php echo __('Enable Auto Reminder'); ?></label>
                <div class="col-sm-9">
                    <label class="checkbox-inline">
                        <?php echo $this->Form->checkbox('auto_enabled', array('hiddenField'=>true)); ?>
                        <?php echo __('Auto reminder active'); ?>
                    </label>
                    <p class="help-block"><?php echo __('The scheduler command below must run from Windows Task Scheduler or cron.'); ?></p>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label"><?php echo __('Channels'); ?></label>
                <div class="col-sm-9">
                    <label class="checkbox-inline"><?php echo $this->Form->checkbox('send_sms', array('hiddenField'=>true)); ?> <?php echo __('SMS'); ?></label>
                    <label class="checkbox-inline"><?php echo $this->Form->checkbox('send_email', array('hiddenField'=>true)); ?> <?php echo __('Email'); ?></label>
                    <label class="checkbox-inline"><?php echo $this->Form->checkbox('send_whatsapp', array('hiddenField'=>true)); ?> <?php echo __('WhatsApp'); ?></label>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label"><?php echo __('Days Before Due'); ?></label>
                <div class="col-sm-3">
                    <?php echo $this->Form->input('days_before', array('type'=>'number', 'min'=>0, 'max'=>90, 'label'=>false, 'class'=>'form-control', 'div'=>false)); ?>
                </div>
                <label class="col-sm-3 control-label"><?php echo __('Repeat After Days'); ?></label>
                <div class="col-sm-3">
                    <?php echo $this->Form->input('repeat_after_days', array('type'=>'number', 'min'=>0, 'max'=>365, 'label'=>false, 'class'=>'form-control', 'div'=>false)); ?>
                    <p class="help-block"><?php echo __('Successful reminders are skipped for this many days. Use 0 to never repeat an already sent reminder automatically.'); ?></p>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label"><?php echo __('Overdue Deals'); ?></label>
                <div class="col-sm-3">
                    <label class="checkbox-inline"><?php echo $this->Form->checkbox('include_overdue', array('hiddenField'=>true)); ?> <?php echo __('Include overdue'); ?></label>
                </div>
                <label class="col-sm-3 control-label"><?php echo __('Daily Send Limit'); ?></label>
                <div class="col-sm-3">
                    <?php echo $this->Form->input('daily_limit', array('type'=>'number', 'min'=>1, 'max'=>1000, 'label'=>false, 'class'=>'form-control', 'div'=>false)); ?>
                    <p class="help-block"><?php echo __('Maximum SMS, email, or WhatsApp messages in one scheduled run. When reached, the next run continues with remaining reminders.'); ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading"><?php echo __('WhatsApp API Settings'); ?></div>
        <div class="panel-body">
            <div class="form-group">
                <label class="col-sm-3 control-label"><?php echo __('WhatsApp Mode'); ?></label>
                <div class="col-sm-9">
                    <?php echo $this->Form->input('whatsapp_mode', array('type'=>'select', 'options'=>array('link'=>__('Manual Link Only'), 'api'=>__('API Gateway')), 'label'=>false, 'class'=>'form-control', 'div'=>false)); ?>
                    <p class="help-block"><?php echo __('Automatic WhatsApp sending and bulk sending without opening chats requires an API gateway. WhatsApp Web/Windows links can prepare the message but still require the final Send click.'); ?></p>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label"><?php echo __('API URL'); ?></label>
                <div class="col-sm-6">
                    <?php echo $this->Form->input('whatsapp_api', array('label'=>false, 'class'=>'form-control', 'placeholder'=>__('https://example.com/send'), 'div'=>false)); ?>
                </div>
                <label class="col-sm-1 control-label"><?php echo __('Type'); ?></label>
                <div class="col-sm-2">
                    <?php echo $this->Form->input('whatsapp_type', array('type'=>'select', 'options'=>array('Post'=>'Post', 'Get'=>'Get'), 'label'=>false, 'class'=>'form-control', 'div'=>false)); ?>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label"><?php echo __('Mobile Field'); ?></label>
                <div class="col-sm-3">
                    <?php echo $this->Form->input('whatsapp_mobile_param', array('label'=>false, 'class'=>'form-control', 'placeholder'=>'number', 'div'=>false)); ?>
                </div>
                <label class="col-sm-3 control-label"><?php echo __('Message Field'); ?></label>
                <div class="col-sm-3">
                    <?php echo $this->Form->input('whatsapp_message_param', array('label'=>false, 'class'=>'form-control', 'placeholder'=>'message', 'div'=>false)); ?>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label"><?php echo __('Token Header'); ?></label>
                <div class="col-sm-3">
                    <?php echo $this->Form->input('whatsapp_token_header', array('label'=>false, 'class'=>'form-control', 'placeholder'=>'Authorization', 'div'=>false)); ?>
                </div>
                <label class="col-sm-3 control-label"><?php echo __('Token / API Key'); ?></label>
                <div class="col-sm-3">
                    <?php echo $this->Form->input('whatsapp_token', array('type'=>'password', 'label'=>false, 'class'=>'form-control', 'div'=>false)); ?>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label"><?php echo __('Extra Params'); ?></label>
                <div class="col-sm-9">
                    <?php echo $this->Form->input('whatsapp_extra_params', array('type'=>'textarea', 'rows'=>3, 'label'=>false, 'class'=>'form-control', 'placeholder'=>"key=value\ninstance_id=123", 'div'=>false)); ?>
                </div>
            </div>
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading"><?php echo __('Scheduler Command'); ?></div>
        <div class="panel-body">
            <div class="form-group">
                <label class="col-sm-3 control-label"><?php echo __('Command'); ?></label>
                <div class="col-sm-9">
                    <code><?php echo h($schedulerCommand); ?></code>
                </div>
            </div>
            <div class="form-group text-left">
                <div class="col-sm-offset-3 col-sm-9">
                    <button type="submit" class="btn btn-success"><span class="fa fa-save"></span>&nbsp;<?php echo __('Save Settings'); ?></button>
                </div>
            </div>
        </div>
    </div>
    <?php echo $this->Form->end(); ?>
</div>
