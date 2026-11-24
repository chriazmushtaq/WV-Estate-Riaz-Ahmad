<?php echo $this->element('property_enhancement_nav', array('property'=>$property));?>
<?php echo $this->Session->flash();?>
<div class="wv-enhance-card">
    <h3><?php echo __('SEO Settings');?></h3>
    <?php echo $this->Form->create('PropertySeo', array('url'=>array('action'=>'seo',(int)$property['Property']['id']),'class'=>'form-horizontal'));?>
    <div class="form-group">
        <label class="col-sm-2 control-label"><?php echo __('SEO Slug');?></label>
        <div class="col-sm-10"><?php echo $this->Form->input('slug', array('label'=>false,'class'=>'form-control','div'=>false));?></div>
    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label"><?php echo __('Meta Title');?></label>
        <div class="col-sm-10"><?php echo $this->Form->input('meta_title', array('label'=>false,'class'=>'form-control','div'=>false));?></div>
    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label"><?php echo __('Meta Description');?></label>
        <div class="col-sm-10"><?php echo $this->Form->input('meta_description', array('type'=>'textarea','rows'=>4,'label'=>false,'class'=>'form-control','div'=>false));?></div>
    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label"><?php echo __('Canonical URL');?></label>
        <div class="col-sm-10"><?php echo $this->Form->input('canonical_url', array('label'=>false,'class'=>'form-control','div'=>false,'placeholder'=>__('Optional')));?></div>
    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label"><?php echo __('Schema');?></label>
        <div class="col-sm-10">
            <label><?php echo $this->Form->checkbox('schema_enabled');?> <?php echo __('Enable property schema on frontend');?></label>
        </div>
    </div>
    <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> <?php echo __('Save SEO');?></button>
    <?php echo $this->Form->end();?>
</div>
