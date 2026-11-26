<script type="text/javascript">
jQuery(document).ready(function(){
    if(jQuery('#booking_from').length){
        jQuery('#booking_from').datetimepicker({locale:'<?php echo $configLanguage;?>',format:'<?php echo $dpFormat;?>'});
    }
    if(jQuery('#booking_to').length){
        jQuery('#booking_to').datetimepicker({locale:'<?php echo $configLanguage;?>',format:'<?php echo $dpFormat;?>'});
    }
    var $agencySelect = jQuery('#BallotingSettingsProjectId');
    if($agencySelect.length){
        $agencySelect.on('change', function(){
            var projectId = parseInt(jQuery(this).val(), 10) || 0;
            if(projectId > 0){
                window.location = <?php echo json_encode($this->Html->url(array('controller' => 'Balloting', 'action' => 'settings')));?> + '?project_id=' + encodeURIComponent(projectId);
            }
        });
    }
});
</script>

<div class="page-title">
    <div class="title-env">
        <h1 class="title"><?php echo __('Balloting Settings');?></h1>
    </div>
</div>

<div class="panel">
    <div class="panel-body">
        <?php echo $this->Session->flash();?>
        <?php echo $this->Form->create('BallotingSetting', array('class' => 'form-horizontal'));?>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label class="col-sm-4 control-label"><?php echo __('Agency');?></label>
                    <div class="col-sm-8">
                        <?php if(!empty($canUseAgencyFilters)){?>
                        <?php echo $this->Form->select('project_id', $projectName, array('empty' => __('Select Agency'), 'label' => false, 'class' => 'form-control', 'div' => false, 'value' => $projectId, 'id' => 'BallotingSettingsProjectId'));?>
                        <?php } else { ?>
                        <?php echo $this->Form->hidden('project_id', array('value' => $projectId));?>
                        <input type="text" class="form-control" value="<?php echo h(isset($projectName[$projectId]) ? $projectName[$projectId] : __('Current Agency'));?>" readonly="readonly">
                        <?php } ?>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4 control-label"><?php echo __('Booking Date From');?></label>
                    <div class="col-sm-8">
                        <div class="input-group date" id="booking_from">
                            <?php echo $this->Form->input('booking_date_from', array('type' => 'text', 'label' => false, 'class' => 'form-control', 'div' => false, 'placeholder' => __('Start booking date')));?>
                            <span class="input-group-addon"><span class="fa fa-calendar"></span></span>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4 control-label"><?php echo __('Booking Date To');?></label>
                    <div class="col-sm-8">
                        <div class="input-group date" id="booking_to">
                            <?php echo $this->Form->input('booking_date_to', array('type' => 'text', 'label' => false, 'class' => 'form-control', 'div' => false, 'placeholder' => __('End booking date')));?>
                            <span class="input-group-addon"><span class="fa fa-calendar"></span></span>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4 control-label"><?php echo __('Min Paid Percent');?></label>
                    <div class="col-sm-8">
                        <?php echo $this->Form->input('min_paid_percent', array('type' => 'number', 'step' => '0.01', 'min' => 0, 'label' => false, 'class' => 'form-control', 'div' => false));?>
                        <span class="help-block"><?php echo __('Example: 25 means only deals with at least 25% paid are eligible');?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="col-sm-4 control-label"><?php echo __('Max Overdue Installments');?></label>
                    <div class="col-sm-8">
                        <?php echo $this->Form->input('max_overdue_installments', array('type' => 'number', 'min' => 0, 'label' => false, 'class' => 'form-control', 'div' => false));?>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4 control-label"><?php echo __('Max Overdue Days');?></label>
                    <div class="col-sm-8">
                        <?php echo $this->Form->input('max_overdue_days', array('type' => 'number', 'min' => 0, 'label' => false, 'class' => 'form-control', 'div' => false));?>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4 control-label"><?php echo __('Selection Order');?></label>
                    <div class="col-sm-8">
                        <?php echo $this->Form->select('selection_order', $selectionOrderArr, array('label' => false, 'class' => 'form-control', 'div' => false, 'empty' => false));?>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4 control-label"><?php echo __('Require Active Deal');?></label>
                    <div class="col-sm-8" style="padding-top:7px;">
                        <?php echo $this->Form->checkbox('require_active_deal', array('hiddenField' => true));?>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4 control-label"><?php echo __('Status');?></label>
                    <div class="col-sm-8">
                        <?php echo $this->Form->select('status', $statusOptions, array('label' => false, 'class' => 'form-control', 'div' => false, 'empty' => false));?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Client Portal & Digital Allotment Hub Settings -->
        <div class="panel" style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; margin-top:20px; padding:18px;">
            <h4 style="margin:0 0 16px; font-weight:800; color:#0f172a; display:flex; align-items:center; gap:8px;">
                <i class="fa fa-desktop text-primary"></i> <?php echo __('Client Portal & Digital e-Balloting Hub Settings (Project-Specific)'); ?>
            </h4>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="col-sm-6 control-label"><?php echo __('Enable e-Balloting in Client Portal'); ?></label>
                        <div class="col-sm-6" style="padding-top:7px;">
                            <?php echo $this->Form->checkbox('portal_enabled', array('hiddenField' => true, 'checked' => !empty($this->request->data['BallotingSetting']['portal_enabled']))); ?>
                            <span class="help-block" style="font-size:11px; margin-top:2px;"><?php echo __('Allow clients of this project to view balloting status & allotted plots.'); ?></span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-6 control-label"><?php echo __('Enable Live Allotment Search'); ?></label>
                        <div class="col-sm-6" style="padding-top:7px;">
                            <?php echo $this->Form->checkbox('portal_live_results', array('hiddenField' => true, 'checked' => !empty($this->request->data['BallotingSetting']['portal_live_results']))); ?>
                            <span class="help-block" style="font-size:11px; margin-top:2px;"><?php echo __('Let clients & file holders search live by Deal No / CNIC / Reg No.'); ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="col-sm-6 control-label"><?php echo __('Downloadable Plot Certificate'); ?></label>
                        <div class="col-sm-6" style="padding-top:7px;">
                            <?php echo $this->Form->checkbox('portal_certificate_enabled', array('hiddenField' => true, 'checked' => !empty($this->request->data['BallotingSetting']['portal_certificate_enabled']))); ?>
                            <span class="help-block" style="font-size:11px; margin-top:2px;"><?php echo __('Enable QR-coded verifiable digital plot allocation certificate.'); ?></span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label"><?php echo __('Portal Notice / Announcement'); ?></label>
                        <div class="col-sm-8">
                            <?php echo $this->Form->textarea('portal_announcement', array('class' => 'form-control', 'rows' => 3, 'placeholder' => __('e.g. Official Phase 1 Balloting completed. Check your allotted plot details below.'))); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="col-sm-12">
                <button type="submit" class="btn btn-success"><span class="fa fa-save"></span>&nbsp;<?php echo __('Save Settings');?></button>
                <?php echo $this->Html->link('<span class="fa fa-dashboard"></span>&nbsp;'.__('Dashboard'), array('action' => 'dashboard', '?' => array('project_id' => $projectId)), array('escape' => false, 'class' => 'btn btn-primary'));?>
            </div>
        </div>
        <?php echo $this->Form->end();?>
    </div>
</div>
