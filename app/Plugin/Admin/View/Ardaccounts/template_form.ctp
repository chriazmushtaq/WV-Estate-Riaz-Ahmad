<?php
$isAdr = ($userType == 'ADR');
$isTemplateEdit = !empty($isTemplateEdit);
$templateFormTitle = isset($templateFormTitle) ? $templateFormTitle : ($isTemplateEdit ? __('Edit Template') : __('Add Template'));
$templateTypeOptions = (isset($templateTypeOptions) && is_array($templateTypeOptions)) ? $templateTypeOptions : array(
    'voucher' => __('Voucher'),
    'salary' => __('Salary Slip'),
    'stock_purchase' => __('Stock Purchase')
);
$templatePlaceholderCatalog = (isset($templatePlaceholderCatalog) && is_array($templatePlaceholderCatalog)) ? $templatePlaceholderCatalog : array();
?>
<div id="resultDiv">
    <div class="page-title">
        <div class="title-env">
            <h1 class="title"><?php echo h($templateFormTitle);?></h1>
        </div>
    </div>
    <div class="panel">
        <div class="panel-body">
            <?php echo $this->Session->flash();?>
            <?php echo $this->Form->create('Ardtemplate', array('class' => 'form-horizontal'));?>
            <?php if(!$isAdr){ echo $this->Form->hidden('project_id', array('value' => $agencyId)); } ?>
            <?php if($isTemplateEdit){ echo $this->Form->hidden('id'); } ?>

            <?php if($isAdr){?>
                <div class="form-group">
                    <label class="col-sm-2 control-label"><?php echo __('Agency');?></label>
                    <div class="col-sm-4">
                        <?php echo $this->Form->select('project_id', $projectName, array('empty' => __('Please Select'), 'class' => 'form-control', 'label' => false, 'div' => false));?>
                    </div>
                </div>
            <?php }?>

            <div class="form-group">
                <label class="col-sm-2 control-label"><?php echo __('Template Type');?></label>
                <div class="col-sm-4">
                    <?php echo $this->Form->select('template_type', $templateTypeOptions, array('id' => 'ArdtemplateTemplateType', 'class' => 'form-control', 'label' => false, 'div' => false));?>
                </div>
                <label class="col-sm-2 control-label"><?php echo __('Template Name');?></label>
                <div class="col-sm-4">
                    <?php echo $this->Form->input('template_name', array('label' => false, 'div' => false, 'class' => 'form-control'));?>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-2 control-label"><?php echo __('Content');?></label>
                <div class="col-sm-10">
                    <?php
                    echo $this->Tinymce->input(
                        'template_content',
                        array(
                            'id' => 'ArdtemplateTemplateContent',
                            'label' => false,
                            'class' => 'form-control',
                            'placeholder' => __('Template HTML'),
                            'div' => false
                        ),
                        array('language' => $configLanguage, 'directionality' => $dirType),
                        'absolute'
                    );
                    ?>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-2 control-label"><?php echo __('Placeholders');?></label>
                <div class="col-sm-10">
                    <div class="panel panel-default" style="margin-bottom:0;">
                        <div class="panel-heading" style="padding:9px 12px;">
                            <strong><?php echo __('Available Placeholders By Category');?></strong>
                            <small class="text-muted" style="margin-left:8px;"><?php echo __('Use these tokens in template content like {{agency_logo_url}} or {{agency_logo_img}}');?></small>
                        </div>
                        <div class="panel-body" style="padding:12px;">
                            <div class="btn-group btn-group-sm" id="TemplatePlaceholderTypeTabs" style="margin-bottom:10px;">
                                <button type="button" class="btn btn-default js-template-type-tab" data-type="voucher"><?php echo __('Voucher');?></button>
                                <button type="button" class="btn btn-default js-template-type-tab" data-type="salary"><?php echo __('Salary Slip');?></button>
                                <button type="button" class="btn btn-default js-template-type-tab" data-type="stock_purchase"><?php echo __('Stock Purchase');?></button>
                            </div>
                            <div id="TemplatePlaceholderContent"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="col-sm-offset-2 col-sm-10">
                    <label><?php echo $this->Form->checkbox('is_active');?> <?php echo __('Set Active');?></label>
                </div>
            </div>

            <div class="form-group">
                <div class="col-sm-offset-2 col-sm-10">
                    <button type="submit" class="btn btn-success"><span class="fa fa-save"></span> <?php echo $isTemplateEdit ? __('Update Template') : __('Save Template');?></button>
                    <?php echo $this->Html->link('<span class="fa fa-list"></span> '.__('Back To Templates'), array('controller'=>'Ardaccounts','action'=>'templates',$currentProjectId), array('escape'=>false, 'class'=>'btn btn-default'));?>
                    <?php echo $this->Html->link('<span class="fa fa-cog"></span> '.__('Back To Settings'), array('controller'=>'Ardaccounts','action'=>'settings',$currentProjectId,'?'=>array('tab'=>'templates')), array('escape'=>false, 'class'=>'btn btn-info'));?>
                </div>
            </div>
            <?php echo $this->Form->end();?>
        </div>
    </div>
</div>

<script type="text/javascript">
jQuery(function($){
    var placeholderCatalog = <?php echo json_encode($templatePlaceholderCatalog);?>;

    function escapeHtml(text)
    {
        return $('<div/>').text(String(text || '')).html();
    }

    function renderPlaceholderGuide(templateType)
    {
        if(!placeholderCatalog[templateType])
        {
            templateType = 'voucher';
        }
        var groups = placeholderCatalog[templateType] || {};
        var html = '';
        $.each(groups, function(groupName, rows){
            html += '<div class="panel panel-default" style="margin-bottom:10px;">';
            html += '<div class="panel-heading" style="padding:7px 10px;"><strong>' + escapeHtml(groupName) + '</strong></div>';
            html += '<div class="table-responsive"><table class="table table-bordered table-striped" style="margin-bottom:0;">';
            html += '<thead><tr><th style="width:34%;">Placeholder</th><th>Description</th></tr></thead><tbody>';
            $.each(rows || [], function(_, row){
                var key = row && row.key ? row.key : '';
                var label = row && row.label ? row.label : '';
                html += '<tr><td><code>' + escapeHtml(key) + '</code></td><td>' + escapeHtml(label) + '</td></tr>';
            });
            html += '</tbody></table></div></div>';
        });
        if(html === '')
        {
            html = '<div class="alert alert-warning" style="margin-bottom:0;"><?php echo addslashes(__('No placeholders found for this template type.'));?></div>';
        }
        $('#TemplatePlaceholderContent').html(html);
    }

    function setTypeTabState(templateType)
    {
        $('.js-template-type-tab').removeClass('btn-primary').addClass('btn-default');
        $('.js-template-type-tab[data-type="' + templateType + '"]').removeClass('btn-default').addClass('btn-primary');
    }

    function syncGuideWithSelect()
    {
        var selectedType = ($('#ArdtemplateTemplateType').val() || 'voucher').toString();
        if(!placeholderCatalog[selectedType])
        {
            selectedType = 'voucher';
        }
        setTypeTabState(selectedType);
        renderPlaceholderGuide(selectedType);
    }

    $('#ArdtemplateTemplateType').on('change', function(){
        syncGuideWithSelect();
    });

    $('.js-template-type-tab').on('click', function(){
        var type = ($(this).data('type') || '').toString();
        if(type === '')
        {
            return;
        }
        $('#ArdtemplateTemplateType').val(type);
        syncGuideWithSelect();
    });

    syncGuideWithSelect();
    if(typeof window.setup === 'function')
    {
        try { window.setup(); } catch(e) {}
    }
});
</script>
