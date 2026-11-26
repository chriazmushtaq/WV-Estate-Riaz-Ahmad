<div class="page-header-content wv-page-header">
    <div class="page-title wv-page-title-box">
        <h4 class="wv-page-title"><i class="fa fa-flag text-primary"></i> <?php echo __('Add Status');?></h4>
        <small class="wv-page-subtitle"><?php echo __('Define a workflow stage name and theme accent color');?></small>
    </div>
    <div class="heading-elements wv-page-header-actions">
        <?php echo $this->Html->link('<i class="fa fa-arrow-left"></i> '.__('Back to Statuses'),array('controller'=>'Statuses','action'=>'index'),array('class'=>'btn btn-default','escape'=>false));?>
    </div>
</div>

<div class="panel panel-flat">
    <div class="panel-body">
        <?php echo $this->Session->flash();?>
        <?php echo $this->Form->create('Status', array('controller' => 'Statuses', 'action' => 'add','name'=>'post_req','id'=>'post_req','class'=>'form-horizontal'));?>
        
        <div class="row">
            <div class="col-xs-12 col-md-8 col-md-offset-2">
                <div class="form-group required">
                    <label class="control-label"><?php echo __('Status Name');?></label>
                    <?php echo $this->Form->input('name',array('label' => false,'class'=>'form-control','placeholder'=>__('e.g. Lead New, Contacted, In Review, Completed'),'div'=>false));?>
                </div>

                <div class="form-group required">
                    <label class="control-label"><?php echo __('Badge Color');?></label>
                    <div style="display:flex; align-items:center; gap:12px;">
                        <?php echo $this->Form->input('color',array('type'=>'color','label' => false,'class'=>'form-control','style'=>'width: 70px; height: 42px; padding: 2px; cursor: pointer;','div'=>false));?>
                        <span class="text-muted" style="font-size: 13px;"><?php echo __('Choose the accent color displayed in badges and tables across the admin portal.'); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="wv-form-footer form-actions">
            <?php echo $this->Html->link('<i class="fa fa-times"></i> '.__('Cancel'),array('controller'=>'Statuses','action'=>'index'),array('class'=>'btn btn-default','escape'=>false));?>
            <button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> <?php echo __('Save Status');?></button>
        </div>
        <?php echo $this->Form->end();?>
    </div>
</div>
