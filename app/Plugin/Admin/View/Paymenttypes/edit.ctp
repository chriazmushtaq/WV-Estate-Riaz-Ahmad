<script type="text/javascript">
    $(document).ready(function(){
        $('#post_req').validationEngine();
    });
</script>
<div <?php if(!$isError){?>class="container-fluid" style="padding: 15px;"<?php }?>>
    <div class="panel panel-flat">
        <div class="panel-heading" style="display:flex; justify-content:space-between; align-items:center;">
            <h5 class="panel-title" style="font-weight:700;"><i class="fa fa-edit text-primary"></i> <?php echo __('Edit Payment Type');?></h5>
            <?php if(!$isError){?><button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button><?php }?>
        </div>
        <div class="panel-body">
            <?php echo $this->Session->flash();?>
            <?php echo $this->Form->create('Paymenttype', array('controller' => 'Paymenttypes','name'=>'post_req','id'=>'post_req','class'=>'form-horizontal'));?>
            <?php foreach ($Paymenttype as $k=>$post): $id=$post['Paymenttype']['id'];$form_no=$k+1;?>
                <div class="panel panel-default" style="border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 16px;">
                    <div class="panel-heading" style="background:#f8fafc; padding: 10px 15px;">
                        <strong class="text-primary"><?php echo __('Type #').$form_no;?></strong>
                    </div>
                    <div class="panel-body" style="padding: 15px;">
                        <div class="form-group required">
                            <label class="control-label col-xs-12 col-sm-3"><?php echo __('Payment Type Name').':';?></label>
                            <div class="col-xs-12 col-sm-9">
                                <?php echo $this->Form->input("$k.Paymenttype.name",array('label' => false,'class'=>'form-control','placeholder'=>__('Payment Type'),'div'=>false));?>
                            </div>
                        </div>
                        <?php echo $this->Form->input("$k.Paymenttype.id",array('type' => 'hidden'));?>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php unset($post); ?>
            
            <div class="wv-form-footer form-actions" style="margin-top: 15px;">
                <?php if(!$isError){?><button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-times"></i> <?php echo __('Cancel');?></button><?php }?>
                <button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> <?php echo __('Update Payment Type');?></button>
            </div>
            <?php echo $this->Form->end();?>
        </div>
    </div>
</div>
