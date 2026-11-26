<div class="page-header-content wv-page-header">
    <div class="page-title wv-page-title-box">
        <h4 class="wv-page-title"><i class="fa fa-percent text-primary"></i> <?php echo __('Add Tax Rate');?></h4>
        <small class="wv-page-subtitle"><?php echo __('Define a tax or duty rate with its percentage calculation');?></small>
    </div>
    <div class="heading-elements wv-page-header-actions">
        <?php echo $this->Html->link('<i class="fa fa-arrow-left"></i> '.__('Back to Taxes'),array('controller'=>'Taxes','action'=>'index'),array('class'=>'btn btn-default','escape'=>false));?>
    </div>
</div>

<div class="panel panel-flat">
    <div class="panel-body">
        <?php echo $this->Session->flash();?>
        <?php echo $this->Form->create('Tax', array('controller' => 'Taxes', 'action' => 'add','name'=>'post_req','id'=>'post_req','class'=>'form-horizontal'));?>
        
        <div class="row">
            <div class="col-xs-12 col-md-8 col-md-offset-2">
                <div class="form-group required">
                    <label class="control-label"><?php echo __('Tax Name');?></label>
                    <?php echo $this->Form->input('name',array('label' => false,'class'=>'form-control','placeholder'=>__('e.g. Sales Tax, Transfer Duty, Advance Tax'),'div'=>false));?>
                </div>

                <div class="form-group required">
                    <label class="control-label"><?php echo __('Tax Percentage (%)');?></label>
                    <div class="input-group">
                        <?php echo $this->Form->input('percent',array('type'=>'number','step'=>'0.01','label' => false,'class'=>'form-control','placeholder'=>__('e.g. 5.00'),'div'=>false));?>
                        <span class="input-group-addon">%</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="wv-form-footer form-actions">
            <?php echo $this->Html->link('<i class="fa fa-times"></i> '.__('Cancel'),array('controller'=>'Taxes','action'=>'index'),array('class'=>'btn btn-default','escape'=>false));?>
            <button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> <?php echo __('Save Tax Rate');?></button>
        </div>
        <?php echo $this->Form->end();?>
    </div>
</div>
