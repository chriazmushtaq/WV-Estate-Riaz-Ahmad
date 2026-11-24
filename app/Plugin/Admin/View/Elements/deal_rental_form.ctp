<?php
echo $this->Html->css('select2/select2');
echo $this->Html->css('select2/select2-bootstrap');
echo $this->Html->script('select2.min');
$formTitle = isset($formTitle) ? $formTitle : __('Rental Agreement');
$submitText = isset($submitText) ? $submitText : __('Save Agreement');
$clientPortalAccess = !empty($this->request->data['DealRental']['client_portal_access']) && strcasecmp((string)$this->request->data['DealRental']['client_portal_access'], 'Blocked') === 0 ? 'Blocked' : 'Active';
?>
<style>
.wv-rental-form{background:#fff;border:1px solid #dbe6f3;border-radius:12px;padding:18px;box-shadow:0 12px 34px rgba(15,35,58,.07)}
.wv-rental-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px}
.wv-rental-field.full{grid-column:1/-1}
.wv-rental-field.double{grid-column:span 2}
.wv-rental-field label{display:block;font-size:11px;font-weight:900;text-transform:uppercase;color:#0b2344;margin-bottom:5px}
.wv-rental-field .form-control{height:39px;border-radius:8px;border:1px solid #cbd8ea;box-shadow:none;font-weight:700}
.wv-rental-field .select2-container{width:100%!important}.wv-rental-field .select2-container .select2-choice{height:39px;line-height:37px;border:1px solid #cbd8ea;border-radius:8px;font-weight:700}.wv-rental-field .select2-container .select2-choice .select2-arrow b{background-position:0 6px}
.wv-rental-field .wv-client-access-select.is-blocked{border-color:#dc2626!important;color:#dc2626!important;-webkit-text-fill-color:#dc2626;font-weight:900}
.wv-rental-field .wv-client-access-select option[value="Blocked"]{color:#dc2626;font-weight:900}
.wv-client-access-current{color:#64748b;display:block;font-size:10px;font-weight:800;margin-top:5px}
.wv-client-access-current.is-blocked{color:#dc2626;font-weight:900}
.wv-rental-field textarea.form-control{height:auto;min-height:85px}
.wv-rental-actions{border-top:1px solid #e3ebf6;margin-top:16px;padding-top:14px;display:flex;gap:10px}
@media(max-width:900px){.wv-rental-grid{grid-template-columns:1fr}.wv-rental-field.double{grid-column:1/-1}}
</style>
<div class="page-title"><div class="title-env"><h1 class="title"><?php echo h($formTitle);?></h1></div></div>
<div class="panel"><div class="panel-body">
<?php echo $this->Session->flash();?>
<div class="wv-rental-form">
<?php echo $this->Form->create('DealRental', array('class'=>'wv-no-auto-layout'));?>
    <div class="wv-rental-grid">
        <div class="wv-rental-field double"><label><?php echo __('Deal');?></label><?php echo $this->Form->select('deal_id', $dealOptions, array('id'=>'RentalDealId','empty'=>__('Please Select Deal'), 'class'=>'form-control'));?></div>
        <div class="wv-rental-field"><label><?php echo __('Agreement No');?></label><?php echo $this->Form->text('agreement_no', array('class'=>'form-control','placeholder'=>__('Auto if empty')));?></div>
        <div class="wv-rental-field"><label><?php echo __('Tenant Name');?></label><?php echo $this->Form->text('tenant_name', array('class'=>'form-control','required'=>true));?></div>
        <div class="wv-rental-field"><label><?php echo __('Tenant CNIC');?></label><?php echo $this->Form->text('tenant_cnic', array('class'=>'form-control'));?></div>
        <div class="wv-rental-field"><label><?php echo __('Tenant Mobile');?></label><?php echo $this->Form->text('tenant_mobile', array('class'=>'form-control'));?></div>
        <div class="wv-rental-field"><label><?php echo __('Tenant Email');?></label><?php echo $this->Form->text('tenant_email', array('class'=>'form-control'));?></div>
        <div class="wv-rental-field"><label><?php echo __('Monthly Rent');?></label><?php echo $this->Form->text('monthly_rent', array('class'=>'form-control','required'=>true));?></div>
        <div class="wv-rental-field"><label><?php echo __('Security Deposit');?></label><?php echo $this->Form->text('security_deposit', array('class'=>'form-control'));?></div>
        <div class="wv-rental-field"><label><?php echo __('Rent Due Day');?></label><?php echo $this->Form->text('due_day', array('class'=>'form-control','value'=>isset($this->request->data['DealRental']['due_day']) ? $this->request->data['DealRental']['due_day'] : 1));?></div>
        <div class="wv-rental-field"><label><?php echo __('Start Date');?></label><?php echo $this->Form->text('start_date', array('id'=>'RentalStartDate','class'=>'form-control datepicker','autocomplete'=>'off','placeholder'=>__('Select Start Date')));?></div>
        <div class="wv-rental-field"><label><?php echo __('End Date');?></label><?php echo $this->Form->text('end_date', array('id'=>'RentalEndDate','class'=>'form-control datepicker','autocomplete'=>'off','placeholder'=>__('Select End Date')));?></div>
        <div class="wv-rental-field"><label><?php echo __('Status');?></label><?php echo $this->Form->select('status', $statusOptions, array('class'=>'form-control','default'=>'Active'));?></div>
        <div class="wv-rental-field"><label><?php echo __('Annual Rent Increment');?></label><?php echo $this->Form->select('rent_increment_enabled',array(0=>__('Off'),1=>__('On')),array('class'=>'form-control','empty'=>false));?></div>
        <div class="wv-rental-field"><label><?php echo __('Increment Type');?></label><?php echo $this->Form->select('rent_increment_type',array('Percentage'=>__('Percentage'),'Fixed'=>__('Fixed Amount')),array('class'=>'form-control','empty'=>false));?></div>
        <div class="wv-rental-field"><label><?php echo __('Annual Increase Value');?></label><?php echo $this->Form->text('rent_increment_value',array('class'=>'form-control','placeholder'=>'0'));?></div>
        <div class="wv-rental-field"><label><?php echo __('First Increment Date');?></label><?php echo $this->Form->text('rent_increment_first_date',array('class'=>'form-control datepicker','autocomplete'=>'off','placeholder'=>__('Defaults to one year after start')));?></div>
        <div class="wv-rental-field">
            <label for="DealRentalClientPortalAccess"><?php echo __('Client Portal Access');?></label>
            <select id="DealRentalClientPortalAccess" name="data[DealRental][client_portal_access]" class="form-control wv-client-access-select<?php echo $clientPortalAccess === 'Blocked' ? ' is-blocked' : ''; ?>" data-current-access="<?php echo h($clientPortalAccess);?>">
                <option value="Active"<?php echo $clientPortalAccess === 'Active' ? ' selected="selected"' : '';?>><?php echo __('Active');?></option>
                <option value="Blocked"<?php echo $clientPortalAccess === 'Blocked' ? ' selected="selected"' : '';?>><?php echo __('Blocked');?></option>
            </select>
            <span id="RentalClientAccessCurrent" class="wv-client-access-current<?php echo $clientPortalAccess === 'Blocked' ? ' is-blocked' : ''; ?>"><?php echo __('Current');?>: <strong><?php echo h($clientPortalAccess);?></strong></span>
        </div>
        <div class="wv-rental-field full"><label><?php echo __('Tenant Address');?></label><?php echo $this->Form->textarea('tenant_address', array('class'=>'form-control','rows'=>3));?></div>
        <div class="wv-rental-field full"><label><?php echo __('Notes');?></label><?php echo $this->Form->textarea('notes', array('class'=>'form-control','rows'=>3));?></div>
    </div>
    <div class="wv-rental-actions">
        <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> <?php echo h($submitText);?></button>
        <?php echo $this->Html->link(__('Back'), array('controller'=>'Dealrentals','action'=>'index'), array('class'=>'btn btn-primary'));?>
    </div>
<?php echo $this->Form->end();?>
</div>
</div></div>
<script type="text/javascript">
jQuery(function($){
    var $rentalDeal = $('#RentalDealId');
    if($rentalDeal.length && $.fn.select2){
        $rentalDeal.select2({
            placeholder: '<?php echo addslashes(__('Search Client Name or Deal'));?>',
            allowClear: true,
            width: '100%'
        });
    }
    var $clientAccess = $('#DealRentalClientPortalAccess');
    var $clientAccessCurrent = $('#RentalClientAccessCurrent');
    if($clientAccess.length){
        var storedAccess = $clientAccess.attr('data-current-access') === 'Blocked' ? 'Blocked' : 'Active';
        $clientAccess.val(storedAccess);
        var refreshClientAccessStyle = function(){
            var isBlocked = $clientAccess.val() === 'Blocked';
            $clientAccess.toggleClass('is-blocked', isBlocked);
            $clientAccessCurrent.toggleClass('is-blocked', isBlocked).html('<?php echo addslashes(__('Current'));?>: <strong>'+(isBlocked ? '<?php echo addslashes(__('Blocked'));?>' : '<?php echo addslashes(__('Active'));?>')+'</strong>');
        };
        refreshClientAccessStyle();
        $clientAccess.on('change', refreshClientAccessStyle);
    }
    var $rentalDates = $('#RentalStartDate,#RentalEndDate');
    if(!$rentalDates.length){
        return;
    }
    if($.fn.datetimepicker){
        $('#RentalStartDate').datetimepicker({
            locale: '<?php echo $configLanguage;?>',
            format: '<?php echo $dpFormat;?>',
            useCurrent: false
        });
        $('#RentalEndDate').datetimepicker({
            locale: '<?php echo $configLanguage;?>',
            format: '<?php echo $dpFormat;?>',
            useCurrent: false
        });
        $('#RentalStartDate').on('dp.change', function(e){
            var endPicker = $('#RentalEndDate').data('DateTimePicker');
            if(endPicker && e.date){
                endPicker.minDate(e.date);
            }
        });
    } else if($.fn.datepicker){
        $rentalDates.datepicker({
            format: 'dd-M-yyyy',
            autoclose: true,
            todayHighlight: true
        });
    }
});
</script>
