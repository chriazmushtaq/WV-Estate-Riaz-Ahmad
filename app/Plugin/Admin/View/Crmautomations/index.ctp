<?php
$settings = !empty($settingsRow['CrmAutomationSetting']) ? (array)$settingsRow['CrmAutomationSetting'] : array();
$isSuperAdmin = !empty($isSuperAdmin);
?>
<div class="page-title">
    <div class="title-env">
        <h1 class="title"><?php echo __('CRM Automation Settings');?></h1>
    </div>
</div>

<div class="panel">
    <div class="panel-heading">
        <div class="btn-group">
            <?php echo $this->Html->link('<span class="fa fa-cogs"></span>&nbsp;'.__('Automation Settings'), array('controller' => 'Crmautomations', 'action' => 'index'), array('escape' => false, 'class' => 'btn btn-success'));?>
            <?php echo $this->Html->link('<span class="fa fa-random"></span>&nbsp;'.__('Assignment Rules'), array('controller' => 'Crmautomations', 'action' => 'assignmentrules', '?' => array('project_id' => $selectedProjectId)), array('escape' => false, 'class' => 'btn btn-default'));?>
            <?php echo $this->Html->link('<span class="fa fa-clock-o"></span>&nbsp;'.__('Follow-up Queue'), array('controller' => 'Crmautomations', 'action' => 'queue'), array('escape' => false, 'class' => 'btn btn-default'));?>
            <?php echo $this->Html->link('<span class="fa fa-bullseye"></span>&nbsp;'.__('Lead Reactivation'), array('controller' => 'Crmautomations', 'action' => 'reactivation'), array('escape' => false, 'class' => 'btn btn-primary'));?>
            <?php echo $this->Html->link('<span class="fa fa-pie-chart"></span>&nbsp;'.__('Source ROI'), array('controller' => 'Crmautomations', 'action' => 'sourceroi', '?' => array('project_id' => $selectedProjectId)), array('escape' => false, 'class' => 'btn btn-info'));?>
            <?php echo $this->Html->link('<span class="fa fa-plug"></span>&nbsp;'.__('CRM Settings'), array('controller' => 'Crmintegrations', 'action' => 'index'), array('escape' => false, 'class' => 'btn btn-info'));?>
        </div>
    </div>
    <div class="panel-body">
        <?php echo $this->Session->flash();?>

        <div class="alert alert-info">
            <strong><?php echo __('What This Controls');?></strong><br/>
            <?php echo __('Duplicate handling can allow, flag, or block repeated leads in the same agency. Auto follow-up can automatically create the first follow-up for new leads when follow-up is empty.');?>
        </div>

        <?php echo $this->Form->create('CrmAutomationSetting', array('class' => 'form-horizontal'));?>
        <?php if($isSuperAdmin){ ?>
        <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo __('Agency');?></label>
            <div class="col-sm-10">
                <?php echo $this->Form->select('project_id', (array)$projectOptions, array('empty' => __('Please Select'), 'label' => false, 'class' => 'form-control', 'div' => false, 'value' => $selectedProjectId));?>
            </div>
        </div>
        <?php } else {
            echo $this->Form->hidden('project_id', array('value' => $selectedProjectId));
        ?>
        <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo __('Agency');?></label>
            <div class="col-sm-10">
                <div class="form-control" style="background:#f9f9f9;"><?php echo h($currentAgencyName !== '' ? $currentAgencyName : __('Current Agency'));?></div>
            </div>
        </div>
        <?php } ?>

        <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo __('Duplicate Mode');?></label>
            <div class="col-sm-10">
                <?php echo $this->Form->select('duplicate_mode', array('allow' => __('Allow'), 'flag' => __('Flag In Remarks'), 'block' => __('Block Save')), array('label' => false, 'class' => 'form-control', 'div' => false, 'value' => isset($settings['duplicate_mode']) ? $settings['duplicate_mode'] : 'allow'));?>
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo __('Duplicate Match');?></label>
            <div class="col-sm-10">
                <label style="margin-right:20px;">
                    <?php echo $this->Form->checkbox('duplicate_by_mobile', array('value' => 1, 'hiddenField' => false, 'checked' => !empty($settings['duplicate_by_mobile'])));?>
                    <?php echo __('Match by Mobile');?>
                </label>
                <label>
                    <?php echo $this->Form->checkbox('duplicate_by_email', array('value' => 1, 'hiddenField' => false, 'checked' => !empty($settings['duplicate_by_email'])));?>
                    <?php echo __('Match by Email');?>
                </label>
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo __('Auto Follow-up');?></label>
            <div class="col-sm-10">
                <label>
                    <?php echo $this->Form->checkbox('auto_followup_enabled', array('value' => 1, 'hiddenField' => false, 'checked' => !empty($settings['auto_followup_enabled'])));?>
                    <?php echo __('Enable automatic first follow-up when follow-up is empty');?>
                </label>
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo __('Follow-up After');?></label>
            <div class="col-sm-3">
                <?php echo $this->Form->input('auto_followup_value', array('type' => 'number', 'min' => 1, 'label' => false, 'class' => 'form-control', 'div' => false, 'value' => isset($settings['auto_followup_value']) ? $settings['auto_followup_value'] : 1));?>
            </div>
            <div class="col-sm-3">
                <?php echo $this->Form->select('auto_followup_unit', array('hour' => __('Hour'), 'day' => __('Day'), 'week' => __('Week')), array('label' => false, 'class' => 'form-control', 'div' => false, 'value' => isset($settings['auto_followup_unit']) ? $settings['auto_followup_unit'] : 'day'));?>
            </div>
            <div class="col-sm-4 help-block" style="padding-top:7px;">
                <?php echo __('This applies only when follow-up is empty on new lead save.');?>
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo __('Apply On');?></label>
            <div class="col-sm-10">
                <label style="margin-right:20px;">
                    <?php echo $this->Form->checkbox('apply_on_manual', array('value' => 1, 'hiddenField' => false, 'checked' => !empty($settings['apply_on_manual'])));?>
                    <?php echo __('Manual Add/Edit');?>
                </label>
                <label style="margin-right:20px;">
                    <?php echo $this->Form->checkbox('apply_on_import', array('value' => 1, 'hiddenField' => false, 'checked' => !empty($settings['apply_on_import'])));?>
                    <?php echo __('CSV / Excel Import');?>
                </label>
                <label>
                    <?php echo $this->Form->checkbox('apply_on_webhook', array('value' => 1, 'hiddenField' => false, 'checked' => !empty($settings['apply_on_webhook'])));?>
                    <?php echo __('Facebook / WhatsApp Webhooks');?>
                </label>
            </div>
        </div>

        <hr/>
        <h4 style="color:#4f46e5;font-weight:bold;margin-bottom:15px;"><i class="fa fa-pie-chart"></i> <?php echo __('📉 Win / Loss Reason Settings');?></h4>
        <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo __('Lost Reasons List');?></label>
            <div class="col-sm-10">
                <?php
                $defaultLostReasons = "Price too high / Budget issue\nLocation not preferred\nBought from competitor\nInvalid / Wrong number\nUnresponsive after multiple calls\nNot interested / Plan postponed\nOther";
                $lostReasonsVal = !empty($settings['lost_reasons_list']) ? $settings['lost_reasons_list'] : $defaultLostReasons;
                echo $this->Form->textarea('lost_reasons_list', array('rows' => 6, 'class' => 'form-control', 'value' => $lostReasonsVal));
                ?>
                <p class="help-block"><?php echo __('Enter each reason on a new line. These options will appear when an agent marks a lead as Cancelled/Lost or moves cards to Lost on Kanban.');?></p>
            </div>
        </div>

        <hr/>
        <h4 style="color:#059669;font-weight:bold;margin-bottom:15px;"><i class="fa fa-bell"></i> <?php echo __('🔔 Live Follow-up Notifications & Daily Agenda');?></h4>
        <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo __('Daily Agenda Popup');?></label>
            <div class="col-sm-10">
                <label>
                    <?php echo $this->Form->checkbox('agenda_popup_enabled', array('value' => 1, 'hiddenField' => false, 'checked' => (isset($settings['agenda_popup_enabled']) ? !empty($settings['agenda_popup_enabled']) : true)));?>
                    <?php echo __('Show morning Daily Agenda popup summary when agents log in (Overdue, Today Follow-ups & Overseas Leads)');?>
                </label>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo __('Browser Audio Chime');?></label>
            <div class="col-sm-10">
                <label>
                    <?php echo $this->Form->checkbox('audio_chime_enabled', array('value' => 1, 'hiddenField' => false, 'checked' => (isset($settings['audio_chime_enabled']) ? !empty($settings['audio_chime_enabled']) : true)));?>
                    <?php echo __('Play pleasant sound chime and desktop alert when a client follow-up is due');?>
                </label>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo __('Alert Timing');?></label>
            <div class="col-sm-3">
                <div class="input-group">
                    <?php echo $this->Form->input('notification_minutes_before', array('type' => 'number', 'min' => 1, 'max' => 60, 'label' => false, 'class' => 'form-control', 'div' => false, 'value' => isset($settings['notification_minutes_before']) ? (int)$settings['notification_minutes_before'] : 10));?>
                    <span class="input-group-addon"><?php echo __('Minutes Before');?></span>
                </div>
            </div>
        </div>

        <hr/>
        <h4 style="color:#2563eb;font-weight:bold;margin-bottom:15px;"><i class="fa fa-file-text-o"></i> <?php echo __('📄 Digital Quotation & Payment Plan Defaults');?></h4>
        <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo __('Default Down Payment');?></label>
            <div class="col-sm-3">
                <div class="input-group">
                    <?php echo $this->Form->input('default_downpayment_pct', array('type' => 'number', 'min' => 5, 'max' => 90, 'label' => false, 'class' => 'form-control', 'div' => false, 'value' => isset($settings['default_downpayment_pct']) ? (int)$settings['default_downpayment_pct'] : 20));?>
                    <span class="input-group-addon">%</span>
                </div>
            </div>
            <label class="col-sm-2 control-label"><?php echo __('Default Duration');?></label>
            <div class="col-sm-3">
                <div class="input-group">
                    <?php echo $this->Form->input('default_duration_years', array('type' => 'number', 'min' => 1, 'max' => 10, 'label' => false, 'class' => 'form-control', 'div' => false, 'value' => isset($settings['default_duration_years']) ? (int)$settings['default_duration_years'] : 3));?>
                    <span class="input-group-addon"><?php echo __('Years');?></span>
                </div>
            </div>
        </div>

        <div class="form-group text-left" style="margin-top:25px;">
            <div class="col-sm-offset-2 col-sm-10">
                <button type="submit" class="btn btn-success btn-lg"><span class="fa fa-save"></span>&nbsp;<?php echo __('Save All CRM Settings');?></button>
            </div>
        </div>
        <?php echo $this->Form->end();?>
    </div>
</div>
