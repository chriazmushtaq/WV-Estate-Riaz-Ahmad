<?php
$selectedTypes = isset($this->request->data['DealRentalSetting']['property_type_ids']) ? (array)$this->request->data['DealRentalSetting']['property_type_ids'] : array();
?>
<style>
.wv-rental-settings{background:#fff;border:1px solid #dbe6f3;border-radius:12px;padding:18px;box-shadow:0 12px 34px rgba(15,35,58,.07)}
.wv-rental-settings-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px}
.wv-rental-settings-field.full{grid-column:1/-1}
.wv-rental-settings-field.double{grid-column:span 2}
.wv-rental-settings-field label{display:block;font-size:11px;font-weight:900;text-transform:uppercase;color:#0b2344;margin-bottom:5px}
.wv-rental-settings-field .form-control{height:39px;border-radius:8px;border:1px solid #cbd8ea;box-shadow:none;font-weight:700}
.wv-rental-settings-field textarea.form-control{height:auto;min-height:96px}
.wv-rental-settings-box{border:1px solid #dbe6f3;border-radius:10px;padding:12px;background:#f8fbff}
.wv-rental-agency-bar{align-items:end;background:#f8fbff;border:1px solid #dbe6f3;border-radius:8px;display:flex;gap:12px;margin-bottom:16px;padding:12px}
.wv-rental-agency-bar label{color:#0b2344;display:block;font-size:11px;font-weight:900;margin-bottom:5px;text-transform:uppercase}
.wv-rental-agency-bar .form-control{height:39px;min-width:280px}
.wv-rental-agency-name{color:#0b2344;font-size:14px;font-weight:900;line-height:39px}
.wv-rental-type-list{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:8px 12px}
.wv-rental-type-list label{background:#fff;border:1px solid #dbe6f3;border-radius:8px;padding:8px 10px;margin:0;text-transform:none;font-size:12px}
.wv-rental-actions{border-top:1px solid #e3ebf6;margin-top:16px;padding-top:14px;display:flex;gap:10px}
@media(max-width:900px){.wv-rental-settings-grid,.wv-rental-type-list{grid-template-columns:1fr}.wv-rental-settings-field.double{grid-column:1/-1}}
</style>
<div class="page-title"><div class="title-env"><h1 class="title"><?php echo __('Rent Manager');?> / <?php echo __('Rental Settings');?></h1></div></div>
<div class="panel"><div class="panel-body">
<?php echo $this->Session->flash();?>
<div class="wv-rental-settings">
    <div class="wv-rental-agency-bar">
        <div>
            <label><?php echo __('Agency');?></label>
            <?php if(!empty($isSuperAdmin)): ?>
                <select class="form-control" onchange="if(this.value){window.location.href='<?php echo h($this->Html->url(array('action'=>'settings')));?>/'+encodeURIComponent(this.value);}">
                    <?php foreach((array)$projectOptions as $agencyProjectId => $agencyName): ?>
                        <option value="<?php echo (int)$agencyProjectId;?>" <?php echo (int)$agencyProjectId===(int)$projectId?'selected="selected"':'';?>><?php echo h($agencyName);?></option>
                    <?php endforeach; ?>
                </select>
            <?php else: ?>
                <div class="wv-rental-agency-name"><?php echo h(isset($projectOptions[$projectId])?$projectOptions[$projectId]:__('Current Agency'));?></div>
            <?php endif; ?>
        </div>
    </div>
<?php echo $this->Form->create('DealRentalSetting', array('class'=>'wv-no-auto-layout'));?>
    <?php echo $this->Form->hidden('id');?>
    <?php echo $this->Form->hidden('project_id', array('value'=>(int)$projectId));?>
    <div class="wv-rental-settings-grid">
        <div class="wv-rental-settings-field"><label><?php echo __('Agreement Letter Prefix');?></label><?php echo $this->Form->text('agreement_prefix', array('class'=>'form-control','placeholder'=>'RA-'));?></div>
        <div class="wv-rental-settings-field"><label><?php echo __('Agreement Next No');?></label><?php echo $this->Form->text('agreement_next_no', array('class'=>'form-control','placeholder'=>'1'));?></div>
        <div class="wv-rental-settings-field"><label><?php echo __('Allowed Deals');?></label><?php echo $this->Form->select('eligible_deal_type', $dealTypeOptions, array('class'=>'form-control','empty'=>false));?></div>

        <div class="wv-rental-settings-field"><label><?php echo __('Receipt Prefix');?></label><?php echo $this->Form->text('receipt_prefix', array('class'=>'form-control','placeholder'=>'RR-'));?></div>
        <div class="wv-rental-settings-field"><label><?php echo __('Receipt Next No');?></label><?php echo $this->Form->text('receipt_next_no', array('class'=>'form-control','placeholder'=>'1'));?></div>
        <div class="wv-rental-settings-field"><label><?php echo __('Rent Due Reminder Days');?></label><?php echo $this->Form->text('rent_due_days_before', array('class'=>'form-control','placeholder'=>'3'));?></div>

        <div class="wv-rental-settings-field full">
            <div class="wv-rental-settings-box">
                <label><input type="checkbox" name="data[DealRentalSetting][rent_increment_enabled]" value="1" <?php echo !empty($this->request->data['DealRentalSetting']['rent_increment_enabled']) ? 'checked="checked"' : '';?>> <?php echo __('Enable Rent Increment Management by Default');?></label>
                <small class="text-muted"><?php echo __('Creates annual increment proposals for new rentals. Every proposal requires admin approval before schedules change.');?></small>
            </div>
        </div>
        <div class="wv-rental-settings-field"><label><?php echo __('Default Increment Type');?></label><?php echo $this->Form->select('rent_increment_type',array('Percentage'=>__('Percentage'),'Fixed'=>__('Fixed Amount')),array('class'=>'form-control','empty'=>false));?></div>
        <div class="wv-rental-settings-field"><label><?php echo __('Default Annual Increase');?></label><?php echo $this->Form->text('rent_increment_value',array('class'=>'form-control','placeholder'=>'0'));?></div>

        <div class="wv-rental-settings-field full">
            <label><?php echo __('Allowed Property Types');?></label>
            <div class="wv-rental-settings-box">
                <div class="wv-rental-type-list">
                    <?php foreach((array)$typeOptions as $typeId => $typeName): ?>
                    <label><input type="checkbox" name="data[DealRentalSetting][property_type_ids][]" value="<?php echo (int)$typeId;?>" <?php echo in_array((int)$typeId, array_map('intval',$selectedTypes), true) ? 'checked="checked"' : '';?>> <?php echo h($typeName);?></label>
                    <?php endforeach; ?>
                </div>
                <small class="text-muted"><?php echo __('Leave all unchecked to allow all property types.');?></small>
            </div>
        </div>

        <div class="wv-rental-settings-field full">
            <label><input type="checkbox" name="data[DealRentalSetting][sms_enabled]" value="1" <?php echo !empty($this->request->data['DealRentalSetting']['sms_enabled']) ? 'checked="checked"' : '';?>> <?php echo __('Enable Rental SMS Template');?></label>
            <?php echo $this->Form->textarea('sms_template', array('class'=>'form-control','rows'=>3));?>
        </div>
        <div class="wv-rental-settings-field">
            <label><input type="checkbox" name="data[DealRentalSetting][email_enabled]" value="1" <?php echo !empty($this->request->data['DealRentalSetting']['email_enabled']) ? 'checked="checked"' : '';?>> <?php echo __('Enable Email Template');?></label>
            <?php echo $this->Form->text('email_subject', array('class'=>'form-control','placeholder'=>__('Email Subject')));?>
        </div>
        <div class="wv-rental-settings-field double">
            <label><?php echo __('Email Body');?></label>
            <?php echo $this->Form->textarea('email_body', array('class'=>'form-control','rows'=>4));?>
        </div>
        <div class="wv-rental-settings-field full">
            <label><input type="checkbox" name="data[DealRentalSetting][whatsapp_enabled]" value="1" <?php echo !empty($this->request->data['DealRentalSetting']['whatsapp_enabled']) ? 'checked="checked"' : '';?>> <?php echo __('Enable WhatsApp Template');?></label>
            <?php echo $this->Form->textarea('whatsapp_template', array('class'=>'form-control','rows'=>3));?>
        </div>
        <div class="wv-rental-settings-field full">
            <div class="alert alert-info" style="margin:0;">
                <?php echo __('Available placeholders');?>:
                <strong>{{agreement_no}}</strong>,
                <strong>{{tenant_name}}</strong>,
                <strong>{{property_name}}</strong>,
                <strong>{{deal_invoice_no}}</strong>,
                <strong>{{monthly_rent}}</strong>,
                <strong>{{start_date}}</strong>,
                <strong>{{end_date}}</strong>
            </div>
        </div>
    </div>
    <div class="wv-rental-actions">
        <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> <?php echo __('Save Rental Settings');?></button>
        <?php echo $this->Html->link(__('Back'), array('controller'=>'Dealrentals','action'=>'index'), array('class'=>'btn btn-primary'));?>
    </div>
<?php echo $this->Form->end();?>
</div>
</div></div>
