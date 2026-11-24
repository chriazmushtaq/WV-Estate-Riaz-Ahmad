<script type="text/javascript">
    $(document).ready(function(){
        $('#milestoneDate').datetimepicker({format: '<?php echo$dpFormat;?>'});
    });
</script>
<div class="page-title"> <div class="title-env"> <h1 class="title"><?php echo __('Add Milestone');?></h1></div></div>
<div class="panel">
    <div class="panel-heading"></div>
    <div class="panel-body"><?php echo $this->Session->flash();?>
        <?php echo $this->Form->create('PlansPayment',array('class'=>'form-horizontal'));?>
        <div class="form-group">
            <label class="col-sm-3 control-label"><small><?php echo __('Outstanding Balance');?>:</small></label>
            <div class="col-sm-6">
                <p class="form-control-static"><strong><?php echo $currency.$this->Number->format($outstanding);?></strong></p>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 control-label"><small><?php echo __('Name');?>:</small></label>
            <div class="col-sm-6">
                <?php echo $this->Form->input('name',array('label'=>false,'class'=>'form-control','div'=>false,'placeholder'=>__('Installment Name')));?>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 control-label"><small><?php echo __('Amount');?>:</small></label>
            <div class="col-sm-6">
                <?php echo $this->Form->input('amount',array('label'=>false,'class'=>'form-control','div'=>false,'placeholder'=>__('Amount')));?>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 control-label"><small><?php echo __('Due Date');?>:</small></label>
            <div class="col-sm-6">
                <div class="input-group date" id="milestoneDate">
                    <?php echo $this->Form->input('date',array('type'=>'text','label'=>false,'class'=>'form-control','div'=>false));?>
                    <span class="input-group-addon"><span class="fa fa-calendar"></span></span>
                </div>
            </div>
        </div>
        <div class="form-group text-left">
            <div class="col-sm-offset-3 col-sm-6">
                <button type="submit" class="btn btn-success"><span class="fa fa-plus-circle"></span>&nbsp;<?php echo __('Save');?></button>
                <?php echo $this->Html->link('<span class="fa fa-close"></span>&nbsp;'.__('Close'),array('controller'=>'plans_payments','action'=>'index',$id),array('class'=>'btn btn-danger','escape'=>false));?>
            </div>
        </div>
        <?php echo $this->Form->end();?>
    </div>
</div>
