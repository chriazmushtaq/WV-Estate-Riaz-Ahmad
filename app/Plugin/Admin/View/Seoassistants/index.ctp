<?php
$suggestion = isset($suggestion) ? (array)$suggestion : array();
$existingSeo = isset($existingSeo) ? (array)$existingSeo : array();
$type = isset($type) ? $type : 'property';
$entityId = isset($entityId) ? (int)$entityId : 0;
$seoModel = $type === 'agency' ? 'AgencySeo' : 'PropertySeo';
$currentSeo = !empty($existingSeo[$seoModel]) ? (array)$existingSeo[$seoModel] : array();
$formDefaults = array_merge($suggestion, array_filter($currentSeo, function($v){ return trim((string)$v) !== ''; }));
?>
<style type="text/css">
.wv-seo-page{background:#f6f9fd;border:1px solid #dbe6f4;border-radius:10px;padding:14px;}
.wv-seo-head{align-items:center;display:flex;gap:10px;justify-content:space-between;margin-bottom:12px;}
.wv-seo-head h2{color:#07152a;font-size:22px;font-weight:900;margin:0;}
.wv-seo-filter,.wv-seo-card{background:#fff;border:1px solid #dbe6f4;border-radius:8px;margin-bottom:12px;padding:12px;}
.wv-seo-grid{display:grid;gap:12px;grid-template-columns:1fr 1fr;}
.wv-seo-card h3{color:#07152a;font-size:16px;font-weight:900;margin:0 0 10px;}
.wv-seo-card label{color:#334155;font-size:12px;font-weight:900;}
.wv-seo-card .form-control{border-radius:7px;font-weight:800;}
.wv-seo-preview{background:#f8fbff;border:1px solid #e2eaf4;border-radius:8px;padding:12px;}
.wv-seo-preview .title{color:#1a0dab;font-size:18px;font-weight:700;line-height:1.25;}
.wv-seo-preview .url{color:#047857;font-size:12px;margin:5px 0;}
.wv-seo-preview .desc{color:#4d5156;font-size:13px;line-height:1.45;}
@media(max-width:991px){.wv-seo-grid{grid-template-columns:1fr}.wv-seo-filter .form-control,.wv-seo-filter .btn{margin-bottom:6px;width:100%;}.wv-seo-head{display:block}}
</style>
<div class="wv-seo-page">
    <?php echo $this->Session->flash();?>
    <div class="wv-seo-head">
        <h2><?php echo __('SEO Auto Suggestions');?></h2>
    </div>
    <div class="wv-seo-filter">
        <?php echo $this->Form->create(false,array('type'=>'get','url'=>array('controller'=>'Seoassistants','action'=>'index'),'class'=>'form-inline'));?>
        <?php echo $this->Form->input('type',array('type'=>'select','options'=>array('property'=>__('Property'),'agency'=>__('Agency')),'value'=>$type,'label'=>false,'div'=>false,'class'=>'form-control','onchange'=>'this.form.submit();'));?>
        <?php echo $this->Form->input('entity_id',array('type'=>'select','options'=>($type==='agency' ? $agencyOptions : $propertyOptions),'value'=>$entityId,'empty'=>__('Please Select'),'label'=>false,'div'=>false,'class'=>'form-control'));?>
        <button type="submit" class="btn btn-info"><span class="fa fa-magic"></span> <?php echo __('Generate');?></button>
        <?php echo $this->Form->end();?>
    </div>
    <?php if(!empty($suggestion)){ ?>
    <div class="wv-seo-grid">
        <div class="wv-seo-card">
            <h3><?php echo __('Suggestion');?></h3>
            <?php echo $this->Form->create('SeoSuggestion',array('url'=>array('controller'=>'Seoassistants','action'=>'index'),'class'=>'form-horizontal'));?>
            <?php echo $this->Form->hidden('type',array('value'=>$type));?>
            <?php echo $this->Form->hidden('entity_id',array('value'=>$entityId));?>
            <div class="form-group">
                <label class="col-sm-3 control-label"><?php echo __('Slug');?></label>
                <div class="col-sm-9"><?php echo $this->Form->input('slug',array('value'=>isset($formDefaults['slug']) ? $formDefaults['slug'] : $suggestion['slug'],'label'=>false,'div'=>false,'class'=>'form-control'));?></div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label"><?php echo __('Meta Title');?></label>
                <div class="col-sm-9"><?php echo $this->Form->input('meta_title',array('value'=>isset($formDefaults['meta_title']) ? $formDefaults['meta_title'] : $suggestion['meta_title'],'label'=>false,'div'=>false,'class'=>'form-control'));?></div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label"><?php echo __('Description');?></label>
                <div class="col-sm-9"><?php echo $this->Form->input('meta_description',array('type'=>'textarea','rows'=>4,'value'=>isset($formDefaults['meta_description']) ? $formDefaults['meta_description'] : $suggestion['meta_description'],'label'=>false,'div'=>false,'class'=>'form-control'));?></div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label"><?php echo __('Canonical');?></label>
                <div class="col-sm-9"><?php echo $this->Form->input('canonical_url',array('value'=>isset($formDefaults['canonical_url']) ? $formDefaults['canonical_url'] : $suggestion['canonical_url'],'label'=>false,'div'=>false,'class'=>'form-control'));?></div>
            </div>
            <div class="form-group">
                <div class="col-sm-offset-3 col-sm-9">
                    <label><?php echo $this->Form->checkbox('schema_enabled',array('checked'=>!isset($formDefaults['schema_enabled']) || !empty($formDefaults['schema_enabled'])));?> <?php echo __('Enable schema');?></label>
                </div>
            </div>
            <button type="submit" class="btn btn-success"><span class="fa fa-save"></span> <?php echo __('Apply Suggestions');?></button>
            <?php echo $this->Form->end();?>
        </div>
        <div class="wv-seo-card">
            <h3><?php echo __('Search Preview');?></h3>
            <div class="wv-seo-preview">
                <div class="title"><?php echo h(isset($formDefaults['meta_title']) ? $formDefaults['meta_title'] : $suggestion['meta_title']);?></div>
                <div class="url"><?php echo h(isset($formDefaults['canonical_url']) ? $formDefaults['canonical_url'] : $suggestion['canonical_url']);?></div>
                <div class="desc"><?php echo h(isset($formDefaults['meta_description']) ? $formDefaults['meta_description'] : $suggestion['meta_description']);?></div>
            </div>
            <?php if(!empty($currentSeo)){ ?>
            <hr>
            <strong><?php echo __('Existing saved SEO is loaded into the form.');?></strong>
            <?php } ?>
        </div>
    </div>
    <?php } else { ?>
        <div class="wv-seo-card"><?php echo __('Select a record to generate suggestions.');?></div>
    <?php } ?>
</div>
