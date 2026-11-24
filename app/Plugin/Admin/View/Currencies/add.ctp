<div class="page-title"> <div class="title-env"> <h1 class="title"><?php echo __('Add Currency');?></h1></div></div><div class="panel">
    <div class="panel-heading">
    </div>
                <div class="panel-body"><?php echo $this->Session->flash();?>
                <?php if(isset($hasCurrencySymbolColumn) && !$hasCurrencySymbolColumn){ ?>
                <div class="alert alert-warning">
                    <?php echo __('Currency symbol column could not be created automatically. Please update currencies table.'); ?>
                </div>
                <?php } ?>
                <?php echo $this->Form->create('Currency', array( 'controller' => 'Currencies', 'action' => 'add','name'=>'post_req','id'=>'post_req','class'=>'form-horizontal'));?>
                    <div class="form-group">
                        <label for="group_name" class="col-sm-3 control-label"><small><?php echo __('Currency Name');?></small></label>
                        <div class="col-sm-9">
                           <?php echo $this->Form->input('name',array('label' => false,'class'=>'form-control','placeholder'=>__('Currency Name'),'div'=>false));?>
                        </div>
                    </div>                    
                    <div class="form-group">
                        <label for="group_name" class="col-sm-3 control-label"><small><?php echo __('Currency Symbol');?></small></label>
                        <div class="col-sm-9">
                           <?php echo $this->Form->input('symbol',array('type' => 'text','label' => false,'class'=>'form-control','placeholder'=>__('Rs, $, AED'),'maxlength'=>20,'div'=>false));?>
                        </div>
                    </div>
                    <div class="form-group text-left">
                        <div class="col-sm-offset-3 col-sm-7">
                            <button type="submit" class="btn btn-success"><span class="fa fa-plus-circle"></span>&nbsp;<?php echo __('Save');?></button>
                    <?php echo$this->Html->link('<span class="fa fa-close"></span>&nbsp;'.__('Close'),array('controller'=>'Currencies','action'=>'index'),array('class'=>'btn btn-danger','escape'=>false));?>
                </div>
            </div>
        <?php echo$this->Form->end();?></div>
            </div>
