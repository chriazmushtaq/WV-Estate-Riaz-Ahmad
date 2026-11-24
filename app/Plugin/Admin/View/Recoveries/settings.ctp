<style type="text/css">
.wv-recovery-page{color:#06162b;}
.wv-recovery-head{align-items:center;background:linear-gradient(135deg,#07152a,#0d2747 58%,#087b63);border-radius:8px;color:#fff;display:flex;gap:12px;justify-content:space-between;margin-bottom:10px;padding:12px 14px;}
.wv-recovery-head h2{font-size:20px;font-weight:900;margin:0;}
.wv-recovery-head p{color:#dceeff;font-size:12px;font-weight:700;margin:2px 0 0;}
.wv-recovery-actions .btn{font-weight:900;margin-left:4px;}
.wv-recovery-card{background:#fff;border:1px solid #dbe4ee;border-radius:8px;box-shadow:0 10px 24px rgba(7,21,42,.06);max-width:820px;padding:14px;}
.wv-recovery-card label{color:#24384f;font-size:11px;font-weight:900;text-transform:uppercase;}
.wv-recovery-help{background:#f7faff;border:1px solid #dbe4ee;border-radius:8px;color:#60758c;font-size:12px;font-weight:700;line-height:1.6;margin-top:12px;padding:10px;}
@media(max-width:767px){.wv-recovery-head{align-items:stretch;flex-direction:column}.wv-recovery-actions .btn{margin:2px 0}}
</style>

<div class="wv-recovery-page">
    <?php echo $this->Session->flash(); ?>
    <div class="wv-recovery-head">
        <div>
            <h2><?php echo __('Recovery Settings'); ?></h2>
            <p><?php echo __('Agency-wise defaults for recovery follow-up workflow.'); ?></p>
        </div>
        <div class="wv-recovery-actions">
            <?php echo $this->Html->link('<span class="fa fa-dashboard"></span> '.__('Dashboard'), array('action'=>'index'), array('escape'=>false,'class'=>'btn btn-default btn-sm')); ?>
            <?php echo $this->Html->link('<span class="fa fa-users"></span> '.__('Recovery Officers'), array('action'=>'officers'), array('escape'=>false,'class'=>'btn btn-default btn-sm')); ?>
        </div>
    </div>

    <div class="wv-recovery-card">
        <?php echo $this->Form->create('RecoverySetting', array('url'=>array('controller'=>'Recoveries','action'=>'settings'))); ?>
        <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    <label><?php echo __('Agency'); ?></label>
                    <?php echo $this->Form->select('project_id', $agencyOptions, array('empty'=>__('Select Agency'), 'value'=>$selectedAgencyId, 'class'=>'form-control')); ?>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label><?php echo __('Default Next Action Days'); ?></label>
                    <?php echo $this->Form->input('default_next_action_days', array('type'=>'number','min'=>0,'max'=>30,'label'=>false,'div'=>false,'class'=>'form-control')); ?>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label><?php echo __('Escalation Days'); ?></label>
                    <?php echo $this->Form->input('escalation_days', array('type'=>'number','min'=>1,'max'=>365,'label'=>false,'div'=>false,'class'=>'form-control')); ?>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label><?php echo __('High Priority Amount'); ?></label>
                    <?php echo $this->Form->input('high_priority_amount', array('type'=>'number','min'=>0,'step'=>'1','label'=>false,'div'=>false,'class'=>'form-control')); ?>
                </div>
            </div>
            <div class="col-sm-12">
                <label style="display:block;">
                    <?php echo $this->Form->checkbox('auto_assign_enabled', array('hiddenField'=>true)); ?>
                    <?php echo __('Enable auto assign placeholder'); ?>
                </label>
            </div>
        </div>
        <button type="submit" class="btn btn-success"><span class="fa fa-save"></span> <?php echo __('Save Settings'); ?></button>
        <?php echo $this->Form->end(); ?>

        <div class="wv-recovery-help">
            <strong><?php echo __('Note'); ?>:</strong>
            <?php echo __('These settings are saved agency-wise. Auto assign is stored for future automation; current officer assignment remains manual from the Recovery Dashboard so existing payment/reminder logic is not changed.'); ?>
        </div>
    </div>
</div>
