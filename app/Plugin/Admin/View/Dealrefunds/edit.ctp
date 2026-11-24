<?php
$refund = isset($refundRow['Dealrefund']) ? $refundRow['Dealrefund'] : array();
$defaultDeduction = isset($defaultDeduction) ? (float)$defaultDeduction : 0;
?>
<div class="page-title">
    <div class="title-env">
        <h1 class="title"><?php echo __('Edit Deal Refund');?></h1>
    </div>
</div>

<div class="panel">
    <div class="panel-body">
        <?php echo $this->Session->flash();?>

        <?php echo $this->Form->create('Dealrefund', array('url' => array('controller' => 'Dealrefunds', 'action' => 'edit', (int)$refund['id']), 'class' => 'form-horizontal'));?>
        <div class="form-group">
            <label class="col-sm-3 control-label"><?php echo __('Deal');?></label>
            <div class="col-sm-9">
                <?php echo $this->Form->select('deal_id', $dealOptions, array('empty' => __('Please Select'), 'class' => 'form-control', 'disabled' => true, 'value' => (int)$refund['deal_id']));?>
                <?php echo $this->Form->hidden('deal_id', array('value' => (int)$refund['deal_id']));?>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 control-label"><?php echo __('Request Date');?></label>
            <div class="col-sm-3">
                <?php echo $this->Form->text('request_date', array('class' => 'form-control datepicker', 'value' => isset($refund['request_date']) ? $refund['request_date'] : '', 'autocomplete' => 'off', 'data-provide' => 'datepicker'));?>
            </div>
            <label class="col-sm-3 control-label"><?php echo __('Deduction/Markup %');?></label>
            <div class="col-sm-3">
                <?php echo $this->Form->text('deduction_percent', array('class' => 'form-control', 'value' => isset($refund['deduction_percent']) ? $refund['deduction_percent'] : $defaultDeduction));?>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 control-label"><?php echo __('Cycle Count');?></label>
            <div class="col-sm-3">
                <?php echo $this->Form->text('cycle_count', array('class' => 'form-control', 'value' => isset($refund['cycle_count']) ? $refund['cycle_count'] : 1));?>
            </div>
            <label class="col-sm-3 control-label"><?php echo __('Frequency');?></label>
            <div class="col-sm-3">
                <?php echo $this->Form->select('cycle_frequency', array('Monthly' => 'Monthly', 'Quarterly' => 'Quarterly', 'Biannually' => 'Biannually', 'Yearly' => 'Yearly'), array('class' => 'form-control', 'value' => isset($refund['cycle_frequency']) ? $refund['cycle_frequency'] : 'Monthly'));?>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 control-label"><?php echo __('Cycle Start');?></label>
            <div class="col-sm-3">
                <?php echo $this->Form->text('cycle_start_date', array('class' => 'form-control datepicker', 'value' => isset($refund['cycle_start_date']) ? $refund['cycle_start_date'] : '', 'autocomplete' => 'off', 'data-provide' => 'datepicker'));?>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 control-label"><?php echo __('Reason');?></label>
            <div class="col-sm-9">
                <?php echo $this->Form->textarea('reason', array('class' => 'form-control', 'rows' => 3, 'value' => isset($refund['reason']) ? $refund['reason'] : ''));?>
            </div>
        </div>
        <hr />
        <div class="form-group">
            <label class="col-sm-3 control-label"><?php echo __('Applicant Name');?></label>
            <div class="col-sm-3"><?php echo $this->Form->text('applicant_name', array('class' => 'form-control', 'value' => isset($refund['applicant_name']) ? $refund['applicant_name'] : ''));?></div>
            <label class="col-sm-3 control-label"><?php echo __('Relationship');?></label>
            <div class="col-sm-3"><?php echo $this->Form->select('relation_type', array(
                'Father' => __('Father (S/O, D/O)'),
                'Husband' => __('Husband (W/O)'),
                'Wife' => __('Wife'),
                'Mother' => __('Mother'),
                'Guardian' => __('Guardian'),
                'Son' => __('Son'),
                'Daughter' => __('Daughter'),
                'Other' => __('Other')
            ), array('class' => 'form-control', 'value' => isset($refund['relation_type']) ? $refund['relation_type'] : 'Father', 'empty' => __('Select Relationship')));?></div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 control-label"><?php echo __('Father / Guardian Name');?></label>
            <div class="col-sm-3"><?php echo $this->Form->text('father_name', array('class' => 'form-control', 'value' => isset($refund['father_name']) ? $refund['father_name'] : ''));?></div>
            <label class="col-sm-3 control-label"><?php echo __('CNIC');?></label>
            <div class="col-sm-3"><?php echo $this->Form->text('cnic', array('class' => 'form-control', 'value' => isset($refund['cnic']) ? $refund['cnic'] : ''));?></div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 control-label"><?php echo __('Passport');?></label>
            <div class="col-sm-3"><?php echo $this->Form->text('passport_no', array('class' => 'form-control', 'value' => isset($refund['passport_no']) ? $refund['passport_no'] : ''));?></div>
            <label class="col-sm-3 control-label"><?php echo __('Mobile');?></label>
            <div class="col-sm-3"><?php echo $this->Form->text('mobile_no', array('class' => 'form-control', 'value' => isset($refund['mobile_no']) ? $refund['mobile_no'] : ''));?></div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 control-label"><?php echo __('Email');?></label>
            <div class="col-sm-9"><?php echo $this->Form->text('email', array('class' => 'form-control', 'value' => isset($refund['email']) ? $refund['email'] : ''));?></div>
        </div>
        <hr />
        <div class="form-group">
            <label class="col-sm-3 control-label"><?php echo __('Account Title');?></label>
            <div class="col-sm-3"><?php echo $this->Form->text('account_title', array('class' => 'form-control', 'value' => isset($refund['account_title']) ? $refund['account_title'] : ''));?></div>
            <label class="col-sm-3 control-label"><?php echo __('Account No/IBAN');?></label>
            <div class="col-sm-3"><?php echo $this->Form->text('account_no', array('class' => 'form-control', 'value' => isset($refund['account_no']) ? $refund['account_no'] : ''));?></div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 control-label"><?php echo __('Bank Name');?></label>
            <div class="col-sm-3"><?php echo $this->Form->text('bank_name', array('class' => 'form-control', 'value' => isset($refund['bank_name']) ? $refund['bank_name'] : ''));?></div>
            <label class="col-sm-3 control-label"><?php echo __('Branch & City');?></label>
            <div class="col-sm-3"><?php echo $this->Form->text('bank_branch_city', array('class' => 'form-control', 'value' => isset($refund['bank_branch_city']) ? $refund['bank_branch_city'] : ''));?></div>
        </div>
        <div class="form-group">
            <div class="col-sm-offset-3 col-sm-9">
                <button type="submit" class="btn btn-success"><span class="fa fa-save"></span> <?php echo __('Save Changes');?></button>
                <?php echo $this->Html->link(__('Cancel'), array('controller' => 'Dealrefunds', 'action' => 'index'), array('class' => 'btn btn-default'));?>
            </div>
        </div>
        <?php echo $this->Form->end();?>
    </div>
</div>

<script type="text/javascript">
jQuery(function($){
    if($.fn.datetimepicker){
        $('#DealrefundRequestDate, #DealrefundCycleStartDate').datetimepicker({
            format: 'DD-MMM-YYYY',
            useCurrent: true
        });
    }
});
</script>
