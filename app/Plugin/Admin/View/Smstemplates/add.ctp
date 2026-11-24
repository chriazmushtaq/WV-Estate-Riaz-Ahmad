<script type="text/javascript">
jQuery(document).ready(function(){
    function smsSyncTemplateCategory(){
        var category = jQuery('#smsTemplateCategory').val();
        var $type = jQuery('#SmstemplateType');
        if(category==='existing_custom')
        {
            $type.val('');
            $type.prop('readonly', true);
            return;
        }
        if(category==='lead_default')
        {
            $type.val('LED');
            $type.prop('readonly', true);
            return;
        }
        $type.prop('readonly', false);
    }
    jQuery('#smsTemplateCategory').on('change', smsSyncTemplateCategory);
    jQuery('#SmstemplateType').on('input', function(){
        var cleaned = jQuery(this).val().toUpperCase().replace(/[^A-Z0-9]/g,'').substring(0,3);
        jQuery(this).val(cleaned);
    });
    smsSyncTemplateCategory();
});
</script>
<?php $isGlobalTemplateAdmin = !empty($isGlobalTemplateAdmin); ?>
<div class="page-title"> <div class="title-env"> <h1 class="title"><?php echo __('Add SMS Template');?></h1></div></div><div class="panel">
    <div class="panel-heading">
    </div>
                <div class="panel-body"><?php echo $this->Session->flash();?>
                <?php if(!$isGlobalTemplateAdmin){ ?>
                <div class="alert alert-info"><?php echo __('This SMS template will save under your own login only and will be available in your Leads SMS send screens.');?></div>
                <?php } ?>
                <?php echo $this->Form->create('Smstemplate', array( 'controller' => 'Smstemplates', 'action' => 'add','name'=>'post_req','id'=>'post_req','class'=>'form-horizontal'));?>
            <div class="form-group">
                <label for="group_name" class="col-sm-4 control-label"><small><?php echo __('Name');?>:</small></label>
                <div class="col-sm-6">
                   <?php echo $this->Form->input('name',array('label' => false,'class'=>'form-control','placeholder'=>__('Name'),'div'=>false));?>
                </div>
            </div>
            <?php if($isGlobalTemplateAdmin){ ?>
            <div class="form-group">
                <label class="col-sm-4 control-label"><small><?php echo __('Type Code');?>:</small></label>
                <div class="col-sm-6">
                   <?php echo $this->Form->input('type',array('id'=>'SmstemplateType','label' => false,'class'=>'form-control','maxlength'=>3,'placeholder'=>__('Example: LED / PAY / ULC'),'div'=>false));?>
                   <small class="text-muted"><?php echo __('Max 3 characters. Leave empty for custom template.');?></small>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-4 control-label"><small><?php echo __('Template Category');?>:</small></label>
                <div class="col-sm-6">
                    <select id="smsTemplateCategory" class="form-control" name="template_category">
                        <option value="existing_custom"><?php echo __('Existing Custom');?></option>
                        <option value="lead_default"><?php echo __('Lead Default');?></option>
                        <option value="system_auto"><?php echo __('System / Auto');?></option>
                    </select>
                </div>
            </div>
            <?php } ?>
            <div class="form-group">
                <label class="col-sm-4 control-label"><small><?php echo __('Status');?>:</small></label>
                <div class="col-sm-6">
                    <?php
                    $smsStatusOptions=array('Published'=>__('Published'),'Unpublished'=>__('Unpublished'));
                    ?>
                   <?php echo $this->Form->select('status',$smsStatusOptions,array('label' => false,'class'=>'form-control','empty'=>false,'value'=>'Published','div'=>false));?>
                </div>
            </div>
            <div class="form-group js-template-preview-scope">
                <label for="group_name" class="col-sm-4 control-label"><small><?php echo __('Template');?>:</small></label>
                <div class="col-sm-6">
                   <?php echo $this->Form->input('description',array('type'=>'textarea','rows'=>5,'label' => false,'class'=>'form-control js-template-preview-source','placeholder'=>__('Template'),'div'=>false));?>
                   <small class="text-muted"><?php echo __('Reminder placeholders');?>: <code>$clientName</code>, <code>$invoiceNo</code>, <code>$propertyName</code>, <code>$installmentStatus</code>, <code>$dueDate</code>, <code>$amount</code>, <code>$totalOverdueAmount</code></small>
                   <?php echo $this->element('message_template_live_preview', array('previewMode' => 'auto', 'defaultMode' => 'sms', 'typeSelector' => '#SmstemplateType'));?>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-offset-4 col-sm-6">
                    <?php echo $this->element('message_template_placeholders_guide');?>
                </div>
            </div>
            <div class="form-group text-left">
                <div class="col-sm-offset-4 col-sm-6">
                    <button type="submit" class="btn btn-success"><span class="fa fa-plus-circle"></span> <?php echo __('Save');?></button>
                    <?php echo$this->Html->link('<span class="fa fa-close"></span> '.__('Close'),array('controller'=>'Smstemplates','action'=>'index'),array('class'=>'btn btn-danger','escape'=>false));?>
                </div>
            </div>
        <?php echo$this->Form->end();?>
        </div>
            </div>
