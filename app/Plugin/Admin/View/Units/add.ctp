<div class="page-header-content wv-page-header">
    <div class="page-title wv-page-title-box">
        <h4 class="wv-page-title"><i class="fa fa-arrows-alt text-primary"></i> <?php echo __('Add Measurement Unit');?></h4>
        <small class="wv-page-subtitle"><?php echo __('Define plot, building, or land measurement units');?></small>
    </div>
    <div class="heading-elements wv-page-header-actions">
        <?php echo $this->Html->link('<i class="fa fa-arrow-left"></i> '.__('Back to Units'),array('controller'=>'Units','action'=>'index'),array('class'=>'btn btn-default','escape'=>false));?>
    </div>
</div>

<div class="panel panel-flat">
    <div class="panel-body">
        <?php echo $this->Session->flash();?>
        <?php echo $this->Form->create('Unit', array('controller' => 'Units', 'action' => 'add','name'=>'post_req','id'=>'post_req','class'=>'form-horizontal'));?>
        
        <div class="row">
            <div class="col-xs-12 col-md-8 col-md-offset-2">
                <div class="form-group required">
                    <label class="control-label"><?php echo __('Unit Name / Symbol');?></label>
                    <?php echo $this->Form->input('name',array('label' => false,'class'=>'form-control','placeholder'=>__('e.g. Marla, Kanal, Sq. Ft., Sq. Yd.'),'div'=>false));?>
                </div>
            </div>
        </div>

        <div class="wv-form-footer form-actions">
            <?php echo $this->Html->link('<i class="fa fa-times"></i> '.__('Cancel'),array('controller'=>'Units','action'=>'index'),array('class'=>'btn btn-default','escape'=>false));?>
            <button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> <?php echo __('Save Unit');?></button>
        </div>
        <?php echo $this->Form->end();?>
    </div>
</div>
