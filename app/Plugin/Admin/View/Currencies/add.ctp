<div class="page-header-content wv-page-header">
    <div class="page-title wv-page-title-box">
        <h4 class="wv-page-title"><i class="fa fa-money text-primary"></i> <?php echo __('Add Currency');?></h4>
        <small class="wv-page-subtitle"><?php echo __('Define a new financial denomination and currency symbol');?></small>
    </div>
    <div class="heading-elements wv-page-header-actions">
        <?php echo $this->Html->link('<i class="fa fa-arrow-left"></i> '.__('Back to Currencies'),array('controller'=>'Currencies','action'=>'index'),array('class'=>'btn btn-default','escape'=>false));?>
    </div>
</div>

<div class="panel panel-flat">
    <div class="panel-body">
        <?php echo $this->Session->flash();?>
        <?php if(isset($hasCurrencySymbolColumn) && !$hasCurrencySymbolColumn){ ?>
        <div class="alert alert-warning">
            <?php echo __('Currency symbol column could not be created automatically. Please update currencies table.'); ?>
        </div>
        <?php } ?>
        <?php echo $this->Form->create('Currency', array('controller' => 'Currencies', 'action' => 'add','name'=>'post_req','id'=>'post_req','class'=>'form-horizontal'));?>
        
        <div class="row">
            <div class="col-xs-12 col-md-8 col-md-offset-2">
                <div class="form-group required">
                    <label class="control-label"><?php echo __('Currency Name');?></label>
                    <?php echo $this->Form->input('name',array('label' => false,'class'=>'form-control','placeholder'=>__('e.g. Pakistani Rupee, US Dollar, UAE Dirham'),'div'=>false));?>
                </div>

                <div class="form-group required">
                    <label class="control-label"><?php echo __('Currency Symbol / Code');?></label>
                    <?php echo $this->Form->input('symbol',array('type' => 'text','label' => false,'class'=>'form-control','placeholder'=>__('e.g. Rs, $, AED'),'maxlength'=>20,'div'=>false));?>
                </div>
            </div>
        </div>

        <div class="wv-form-footer form-actions">
            <?php echo $this->Html->link('<i class="fa fa-times"></i> '.__('Cancel'),array('controller'=>'Currencies','action'=>'index'),array('class'=>'btn btn-default','escape'=>false));?>
            <button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> <?php echo __('Save Currency');?></button>
        </div>
        <?php echo $this->Form->end();?>
    </div>
</div>
