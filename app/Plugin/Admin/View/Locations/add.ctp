<div class="page-header-content wv-page-header">
    <div class="page-title wv-page-title-box">
        <h4 class="wv-page-title"><i class="fa fa-map-marker text-primary"></i> <?php echo __('Add Location');?></h4>
        <small class="wv-page-subtitle"><?php echo __('Define a city, territorial sector, or geographic project area');?></small>
    </div>
    <div class="heading-elements wv-page-header-actions">
        <?php echo $this->Html->link('<i class="fa fa-arrow-left"></i> '.__('Back to Locations'),array('controller'=>'Locations','action'=>'index'),array('class'=>'btn btn-default','escape'=>false));?>
    </div>
</div>

<div class="panel panel-flat">
    <div class="panel-body">
        <?php echo $this->Session->flash();?>
        <?php echo $this->Form->create('Location', array('controller' => 'Locations', 'action' => 'add','name'=>'post_req','id'=>'post_req','class'=>'form-horizontal'));?>
        
        <div class="row">
            <div class="col-xs-12 col-md-8 col-md-offset-2">
                <div class="form-group required">
                    <label class="control-label"><?php echo __('Location / City Name');?></label>
                    <?php echo $this->Form->input('name',array('label' => false,'class'=>'form-control','placeholder'=>__('e.g. Islamabad, Lahore, Murree, Rawalpindi'),'div'=>false));?>
                </div>
            </div>
        </div>

        <div class="wv-form-footer form-actions">
            <?php echo $this->Html->link('<i class="fa fa-times"></i> '.__('Cancel'),array('controller'=>'Locations','action'=>'index'),array('class'=>'btn btn-default','escape'=>false));?>
            <button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> <?php echo __('Save Location');?></button>
        </div>
        <?php echo $this->Form->end();?>
    </div>
</div>
