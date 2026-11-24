<div class="page-title">
    <div class="title-env">
        <h1 class="title"><?php echo __('Letters - Add Template');?></h1>
    </div>
</div>

<style type="text/css">
.wv-template-editor-shell {
    width: 100% !important;
    max-width: 100% !important;
    display: block !important;
    clear: both !important;
    box-sizing: border-box !important;
}
.wv-template-editor-shell form,
.wv-template-editor-shell form.form-horizontal,
.wv-template-editor-shell .panel,
.wv-template-editor-shell .panel-body {
    display: block !important;
    width: 100% !important;
    max-width: 100% !important;
    grid-template-columns: none !important;
    box-sizing: border-box !important;
}
.wv-card-section {
    background: #ffffff !important;
    border: 1px solid #dbe5f0 !important;
    border-radius: 12px !important;
    box-shadow: 0 4px 18px rgba(15, 23, 42, 0.05) !important;
    padding: 20px 24px !important;
    margin-bottom: 20px !important;
    width: 100% !important;
    display: block !important;
    clear: both !important;
    box-sizing: border-box !important;
}
.wv-card-header {
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    flex-wrap: wrap !important;
    gap: 12px !important;
    padding-bottom: 14px !important;
    margin-bottom: 16px !important;
    border-bottom: 1px solid #eef2f6 !important;
}
.wv-card-title {
    color: #0f172a !important;
    font-size: 16px !important;
    font-weight: 800 !important;
    margin: 0 !important;
    display: flex !important;
    align-items: center !important;
    gap: 8px !important;
}
.wv-field-group {
    margin-bottom: 0 !important;
}
.wv-field-label {
    display: block !important;
    color: #334155 !important;
    font-size: 13px !important;
    font-weight: 700 !important;
    margin-bottom: 6px !important;
    text-align: left !important;
}
.wv-top-meta-grid {
    display: grid !important;
    grid-template-columns: 280px 1fr 200px !important;
    gap: 18px !important;
    align-items: start !important;
    width: 100% !important;
}
@media (max-width: 991px) {
    .wv-top-meta-grid {
        grid-template-columns: 1fr !important;
    }
}
.wv-signatures-grid {
    display: grid !important;
    grid-template-columns: 1fr 1fr !important;
    gap: 20px !important;
    width: 100% !important;
}
@media (max-width: 767px) {
    .wv-signatures-grid {
        grid-template-columns: 1fr !important;
    }
}
.wv-signature-box {
    background: #f8fafc !important;
    border: 1px solid #e2e8f0 !important;
    border-radius: 10px !important;
    padding: 16px !important;
    box-sizing: border-box !important;
}
.wv-action-bar {
    display: flex !important;
    align-items: center !important;
    gap: 12px !important;
    padding: 16px 24px !important;
    background: #f8fafc !important;
    border: 1px solid #dbe5f0 !important;
    border-radius: 12px !important;
    box-shadow: 0 4px 16px rgba(15, 23, 42, 0.04) !important;
    margin-top: 20px !important;
    margin-bottom: 30px !important;
}
.wv-action-bar .btn {
    padding: 9px 20px !important;
    font-size: 13px !important;
    font-weight: 700 !important;
    border-radius: 7px !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
}
#TemplateDescriptionCode {
    width: 100% !important;
    max-width: 100% !important;
    box-sizing: border-box !important;
}
#wv-code-wrap, #wv-visual-wrap, #wv-preview-wrap {
    width: 100% !important;
    box-sizing: border-box !important;
}
</style>

<div class="wv-template-editor-shell">
    <?php echo $this->Session->flash();?>
    <?php echo $this->Form->create('Template', array(
        'controller' => 'Realestateletters',
        'action' => 'templateadd',
        'name' => 'post_req',
        'id' => 'post_req',
        'class' => 'form-horizontal wv-no-auto-layout wv-template-form',
        'type' => 'file'
    ));?>
    <?php echo $this->Form->hidden('is_new', array('value' => 'Yes'));?>

    <!-- 1. TOP METADATA ROW: Type, Name, Status -->
    <div class="wv-card-section">
        <div class="wv-card-header">
            <h3 class="wv-card-title"><span class="fa fa-sliders text-primary"></span> <?php echo __('Template Details');?></h3>
            <span class="text-muted" style="font-size:12px;"><?php echo __('Configure basic template attributes and publish status.');?></span>
        </div>
        <div class="wv-top-meta-grid">
            <!-- Type -->
            <div class="wv-field-group">
                <label class="wv-field-label" for="TemplateType"><span class="fa fa-folder-open text-primary"></span> <?php echo __('Type').':';?></label>
                <?php echo $this->Form->select('type', $letterTypeArr, array(
                    'empty' => __('Please Select'),
                    'label' => false,
                    'class' => 'form-control',
                    'div' => false,
                    'id' => 'TemplateType',
                    'required' => true
                ));?>
            </div>
            <!-- Name -->
            <div class="wv-field-group">
                <label class="wv-field-label" for="TemplateName"><span class="fa fa-tag text-info"></span> <?php echo __('Name').':';?></label>
                <?php echo $this->Form->input('name', array(
                    'label' => false,
                    'class' => 'form-control',
                    'placeholder' => __('Template Name'),
                    'div' => false,
                    'id' => 'TemplateName',
                    'required' => true
                ));?>
            </div>
            <!-- Status -->
            <div class="wv-field-group">
                <label class="wv-field-label" for="TemplateStatus"><span class="fa fa-toggle-on text-success"></span> <?php echo __('Status').':';?></label>
                <?php echo $this->Form->select('status', array(
                    'Published' => __('Published'),
                    'Unpublished' => __('Unpublished')
                ), array(
                    'empty' => false,
                    'label' => false,
                    'class' => 'form-control',
                    'div' => false,
                    'id' => 'TemplateStatus'
                ));?>
            </div>
        </div>
    </div>

    <!-- 2. TEMPLATE BODY EDITOR: Full Width Single Column Auto-Fit to Screen -->
    <div class="wv-card-section">
        <div class="wv-card-header">
            <h3 class="wv-card-title"><span class="fa fa-code text-primary"></span> <?php echo __('Template Body');?></h3>
            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-primary" id="wv-mode-html"><span class="fa fa-code"></span>&nbsp;<?php echo __('HTML Code');?></button>
                    <button type="button" class="btn btn-default" id="wv-mode-visual"><span class="fa fa-pencil"></span>&nbsp;<?php echo __('Visual Editor');?></button>
                    <button type="button" class="btn btn-default" id="wv-mode-preview"><span class="fa fa-eye"></span>&nbsp;<?php echo __('Live Preview');?></button>
                </div>
                <div id="wv-preview-controls" style="display:none;align-items:center;gap:6px;">
                    <button type="button" class="btn btn-info btn-sm" id="wv-refresh-preview"><span class="fa fa-refresh"></span>&nbsp;<?php echo __('Refresh Preview');?></button>
                    <button type="button" class="btn btn-default btn-sm" id="wv-popup-preview"><span class="fa fa-external-link"></span>&nbsp;<?php echo __('Open in Tab');?></button>
                </div>
            </div>
        </div>

        <input type="hidden" id="TemplateEditorMode" value="html">

        <!-- HTML Code Editor -->
        <div id="wv-code-wrap">
            <?php
            echo $this->Form->textarea('description', array(
                'label' => false,
                'class' => 'form-control',
                'id' => 'TemplateDescriptionCode',
                'placeholder' => __('Template Body (HTML allowed)'),
                'div' => false,
                'rows' => 28,
                'style' => 'width:100%;min-height:640px;font-family:Consolas,Monaco,Lucida Console,Courier New,monospace;font-size:13px;line-height:1.5;background:#1e293b;color:#f8fafc;border:1px solid #334155;border-radius:8px;padding:14px;box-shadow:inset 0 2px 4px rgba(0,0,0,0.2);'
            ));
            ?>
        </div>

        <!-- Visual Editor -->
        <div id="wv-visual-wrap" style="display:none;">
            <?php
            echo $this->Tinymce->input('description_visual', array(
                'label' => false,
                'id' => 'TemplateDescriptionVisual',
                'class' => 'form-control',
                'placeholder' => __('Visual Template Body'),
                'div' => false
            ), array('language' => $configLanguage, 'directionality' => $dirType), 'absolute');
            ?>
        </div>

        <!-- Live Preview Pane -->
        <div id="wv-preview-wrap" style="display:none;background:#e2e8f0;border:1px solid #cbd5e1;border-radius:8px;padding:16px;overflow:hidden;">
            <div style="background:#fff;border-radius:6px;box-shadow:0 4px 12px rgba(0,0,0,0.12);min-height:750px;overflow:hidden;">
                <iframe id="wv-live-preview-frame" style="width:100%;min-height:850px;border:none;display:block;"></iframe>
            </div>
        </div>

        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;margin-top:14px;">
            <div id="wv-sync-buttons">
                <button type="button" class="btn btn-default btn-xs" id="wv-sync-visual"><span class="fa fa-arrow-down text-info"></span>&nbsp;<?php echo __('Code -> Visual');?></button>
                <button type="button" class="btn btn-default btn-xs" id="wv-sync-code"><span class="fa fa-arrow-up text-primary"></span>&nbsp;<?php echo __('Visual -> Code');?></button>
            </div>
            <div class="text-muted" style="font-size:12px;">
                <strong><?php echo __('Editor Mode');?>:</strong> <?php echo __('Use HTML Code for exact full templates (doctype/head/style/script/Tailwind). Use Live Preview to see the formatted document rendered with sample data.');?>
            </div>
        </div>
    </div>

    <!-- 3. SIGNATURES UPLOADER: Placed right AFTER Template Body Editor -->
    <div class="wv-card-section">
        <div class="wv-card-header">
            <h3 class="wv-card-title"><span class="fa fa-pencil-square-o text-warning"></span> <?php echo __('Signatures Uploader');?></h3>
            <span class="text-muted" style="font-size:12px;"><?php echo __('Upload authorized signature images to appear dynamically in generated letters.');?></span>
        </div>
        <div class="wv-signatures-grid">
            <!-- CEO Signature -->
            <div class="wv-signature-box">
                <label class="wv-field-label"><strong><span class="fa fa-user"></span> <?php echo __('CEO Signature');?></strong></label>
                <div style="margin-top:6px;">
                    <?php echo $this->Form->input('ceo_signature_upload',array('type'=>'file','label'=>false,'div'=>false,'class'=>'form-control','accept'=>'image/*'));?>
                </div>
                <p class="help-block" style="margin-top:8px;font-size:12px;"><?php echo __('Use placeholder');?> <code style="background:#e2e8f0;color:#0f172a;font-weight:700;padding:2px 6px;border-radius:4px;">{{ceo_signature}}</code> <?php echo __('in your template body');?></p>
            </div>

            <!-- Chairman Signature -->
            <div class="wv-signature-box">
                <label class="wv-field-label"><strong><span class="fa fa-user-secret"></span> <?php echo __('Chairman Signature');?></strong></label>
                <div style="margin-top:6px;">
                    <?php echo $this->Form->input('chairman_signature_upload',array('type'=>'file','label'=>false,'div'=>false,'class'=>'form-control','accept'=>'image/*'));?>
                </div>
                <p class="help-block" style="margin-top:8px;font-size:12px;"><?php echo __('Use placeholder');?> <code style="background:#e2e8f0;color:#0f172a;font-weight:700;padding:2px 6px;border-radius:4px;">{{chairman_signature}}</code> <?php echo __('in your template body');?></p>
            </div>
        </div>
    </div>

    <!-- 4. UPGRADED LETTER PLACEHOLDERS GUIDE -->
    <div class="wv-card-section" style="padding:16px 20px;">
        <?php echo $this->element('letter_placeholders_guide');?>
    </div>

    <!-- 5. ACTIONS BAR -->
    <div class="wv-action-bar">
        <button type="submit" class="btn btn-success"><span class="fa fa-plus-circle"></span>&nbsp;<?php echo __('Save Template');?></button>
        <?php echo $this->Html->link('<span class="fa fa-close"></span>&nbsp;'.__('Close'),array('controller'=>'Realestateletters','action'=>'templates'),array('escape'=>false,'class'=>'btn btn-danger'));?>
    </div>
    <?php echo $this->Form->end();?>
</div>

<script type="text/javascript">
(function($){
    var previewUrl = '<?php echo $this->Html->url(array("controller"=>"Realestateletters","action"=>"templatepreview"));?>';

    function visualEditor(){
        if(typeof tinymce === 'undefined'){ return null; }
        return tinymce.get('TemplateDescriptionVisual');
    }

    function syncCodeFromVisual(){
        var ed = visualEditor();
        if(!ed){ return; }
        $('#TemplateDescriptionCode').val(ed.getContent());
    }

    function syncVisualFromCode(){
        var ed = visualEditor();
        if(!ed){ return; }
        ed.setContent($('#TemplateDescriptionCode').val() || '');
    }

    function renderLivePreview(){
        var code = $('#TemplateDescriptionCode').val() || '';
        var type = $('#TemplateType').val() || '';

        $.ajax({
            url: previewUrl,
            type: 'POST',
            data: {
                description: code,
                type: type
            },
            success: function(renderedHtml){
                var iframe = document.getElementById('wv-live-preview-frame');
                if(iframe){
                    iframe.srcdoc = renderedHtml;
                }
            },
            error: function(){
                var iframe = document.getElementById('wv-live-preview-frame');
                if(iframe){
                    iframe.srcdoc = code;
                }
            }
        });
    }

    function setMode(mode){
        var currentMode = $('#TemplateEditorMode').val();
        if(currentMode === 'visual'){
            syncCodeFromVisual();
        }

        $('#TemplateEditorMode').val(mode);
        $('#wv-mode-html, #wv-mode-visual, #wv-mode-preview').removeClass('btn-primary').addClass('btn-default');

        if(mode === 'visual'){
            $('#wv-mode-visual').removeClass('btn-default').addClass('btn-primary');
            $('#wv-code-wrap, #wv-preview-wrap, #wv-preview-controls').hide();
            $('#wv-visual-wrap, #wv-sync-buttons').show();
            syncVisualFromCode();
        }else if(mode === 'preview'){
            $('#wv-mode-preview').removeClass('btn-default').addClass('btn-primary');
            $('#wv-code-wrap, #wv-visual-wrap, #wv-sync-buttons').hide();
            $('#wv-preview-wrap').show();
            $('#wv-preview-controls').css('display', 'inline-flex');
            renderLivePreview();
        }else{
            $('#wv-mode-html').removeClass('btn-default').addClass('btn-primary');
            $('#wv-visual-wrap, #wv-preview-wrap, #wv-preview-controls').hide();
            $('#wv-code-wrap, #wv-sync-buttons').show();
        }
    }

    $(function(){
        $('#wv-mode-html').on('click', function(){ setMode('html'); });
        $('#wv-mode-visual').on('click', function(){ setMode('visual'); });
        $('#wv-mode-preview').on('click', function(){ setMode('preview'); });
        $('#wv-refresh-preview').on('click', function(){ renderLivePreview(); });

        $('#wv-popup-preview').on('click', function(){
            var code = $('#TemplateDescriptionCode').val() || '';
            var type = $('#TemplateType').val() || '';
            $.ajax({
                url: previewUrl,
                type: 'POST',
                data: { description: code, type: type },
                success: function(html){
                    var win = window.open('', '_blank');
                    win.document.open();
                    win.document.write(html);
                    win.document.close();
                }
            });
        });

        $('#wv-sync-visual').on('click', function(){ syncVisualFromCode(); });
        $('#wv-sync-code').on('click', function(){ syncCodeFromVisual(); });

        setMode('html');

        $('#post_req').on('submit', function(){
            if($('#TemplateEditorMode').val() === 'visual'){
                syncCodeFromVisual();
            }
            // Ensure visual editor input doesn't overwrite DB description
            $('#TemplateDescriptionVisual').removeAttr('name');
        });
    });
})(jQuery);
</script>
