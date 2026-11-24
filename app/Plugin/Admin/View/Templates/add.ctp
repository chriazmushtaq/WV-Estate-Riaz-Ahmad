<div class="page-title"> <div class="title-env"> <h1 class="title"><?php echo __('Add  Template');?></h1></div></div><div class="panel">
    <div class="panel-heading">
    </div>
                <div class="panel-body"><?php echo $this->Session->flash();?>
                <?php echo $this->Form->create('Template', array( 'controller' => 'Templates', 'action' => $this->request->params['action'],'name'=>'post_req','id'=>'post_req','class'=>'form-horizontal'));?>
            <div class="form-group">
                <label for="group_name" class="col-sm-2 control-label"><small><?php echo __('Type').':';?></small></label>
                <div class="col-sm-10">
                   <?php echo $this->Form->select('type',$typeArr,array('required'=>true,'empty'=>__('Please Select'),'label' => false,'class'=>'form-control','div'=>false));?>
                </div>
            </div>
            
            <div class="form-group">
                <label for="group_name" class="col-sm-2 control-label"><small><?php echo __('Name').':';?></small></label>
                <div class="col-sm-10">
                   <?php echo $this->Form->input('name',array('label' => false,'class'=>'form-control','placeholder'=>__('Name'),'div'=>false));?>
                </div>
            </div>
            <div class="form-group">
                <label for="group_name" class="col-sm-2 control-label"><small><?php echo __('Status').':';?></small></label>
                <div class="col-sm-3">
                   <?php echo $this->Form->select('status',array('Published'=>__('Published'),'Unpublished'=>__('Unpublished')),array('label' => false,'class'=>'form-control','empty'=>false,'div'=>false,'value'=>'Published'));?>
                </div>
            </div>
            <div class="form-group">
                <label for="group_name" class="col-sm-2 control-label"><small><?php echo __('Template').':';?></small></label>
                <div class="col-sm-10">
                    <?php echo $this->Form->textarea('description',array('label' => false,'class'=>'form-control','placeholder'=>__('Paste full HTML template code here'),'div'=>false,'rows'=>28,'style'=>'font-family:Consolas,Monaco,monospace;font-size:13px;line-height:1.45;'));?>
                    <p class="help-block"><?php echo __('Full HTML documents are saved as source code so doctype, head, style, fonts and body layout stay unchanged.');?></p>
                </div>
            </div>
            <div class="form-group text-left">
                <div class="col-sm-offset-2 col-sm-6">
                    <button type="submit" class="btn btn-success"><span class="fa fa-plus-circle"></span>&nbsp;<?php echo __('Save');?></button>
                    <?php echo$this->Html->link('<span class="fa fa-close"></span>&nbsp;'.__('Close'),array('controller'=>'Templates','action'=>'index'),array('class'=>'btn btn-danger','escape'=>false));?>
                </div>
            </div>
            <?php echo $this->Form->input('is_new',array('type'=>'hidden','value'=>'Yes'));?>
        <?php echo$this->Form->end();?>
        </div>
            </div>
