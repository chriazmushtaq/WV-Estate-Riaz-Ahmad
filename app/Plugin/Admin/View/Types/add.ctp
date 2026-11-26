<div class="page-header-content wv-page-header">
    <div class="page-title wv-page-title-box">
        <h4 class="wv-page-title"><i class="fa fa-cube text-primary"></i> <?php echo __('Add Property Type');?></h4>
        <small class="wv-page-subtitle"><?php echo __('Create a new classification type with optional icon or thumbnail image');?></small>
    </div>
    <div class="heading-elements wv-page-header-actions">
        <?php echo $this->Html->link('<i class="fa fa-arrow-left"></i> '.__('Back to Types'),array('controller'=>'Types','action'=>'index'),array('class'=>'btn btn-default','escape'=>false));?>
    </div>
</div>

<div class="panel panel-flat">
    <div class="panel-body">
        <?php echo $this->Session->flash();?>
        <?php echo $this->Form->create('Type', array('controller' => 'Types', 'action' => 'add','name'=>'post_req','id'=>'post_req','class'=>'form-horizontal','type'=>'file'));?>
        
        <div class="row">
            <div class="col-xs-12 col-md-8 col-md-offset-2">
                <div class="form-group required">
                    <label class="control-label"><?php echo __('Type Name');?></label>
                    <?php echo $this->Form->input('name',array('label' => false,'class'=>'form-control','placeholder'=>__('e.g. Apartment, Penthouse, Duplex, Office'),'div'=>false));?>
                </div>

                <div class="form-group">
                    <label class="control-label"><?php echo __('Type Photo / Icon Image');?></label>
                    <?php echo $this->Form->input('photo',array('type'=>'file','label' => false,'class'=>'form-control','div'=>false));?>
                    <span class="help-block text-muted"><?php echo __('Recommended square or 4:3 ratio image for best display on portal.'); ?></span>
                </div>
            </div>
        </div>

        <div class="wv-form-footer form-actions">
            <?php echo $this->Html->link('<i class="fa fa-times"></i> '.__('Cancel'),array('controller'=>'Types','action'=>'index'),array('class'=>'btn btn-default','escape'=>false));?>
            <button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> <?php echo __('Save Property Type');?></button>
        </div>
        <?php echo $this->Form->end();?>
    </div>
</div>
