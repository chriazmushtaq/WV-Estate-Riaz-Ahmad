<div class="panel panel-custom">
    <div class="panel-heading"><?php echo __('Add Level Users');?></div>
    <div class="panel-body"><?php echo $this->Session->flash();?>
    <?php echo $this->Form->create('User', array('class'=>'form-horizontal'));?>
    <div class="form-group">
        <label for="UserRoleCategory" class="col-sm-3 control-label"><small><?php echo __('User Category');?></small></label>
        <div class="col-sm-9">
            <?php echo $this->Form->select('role_category',$roleCategoryOptions,array('id'=>'UserRoleCategory','empty'=>__('Please Select'),'class'=>'form-control','required'=>true));?>
            <small class="help-block" id="roleCategoryHelp"><?php echo __('Select the category first. Related permissions will be copied automatically and can be customized after saving.');?></small>
        </div>
    </div>
    <div class="form-group">
        <label for="group_name" class="col-sm-3 control-label"><small><?php echo __('Level User');?></small></label>
        <div class="col-sm-9">
           <?php echo $this->Form->input('name',array('label' => false,'class'=>'form-control','placeholder'=>__('Level User'),'div'=>false,'required'=>true));?>
        </div>
    </div>
    <div class="form-group text-left">
        <div class="col-sm-offset-3 col-sm-7">
            <?php echo$this->Form->button('<span class="fa fa-plus-circle"></span>&nbsp;'.__('Save'),array('class'=>'btn btn-success','escpae'=>false));?>
                <?php echo$this->Html->link('<span class="fa fa-close"></span>&nbsp;'.__('Close'),array('action'=>'assignrights'),array('class'=>'btn btn-danger','escape'=>false));?>
        </div>
    </div>
    <?php echo $this->Form->end();?>
    </div>
</div>
<script type="text/javascript">
jQuery(function($){
    var categoryHelp = {
        system_user: '<?php echo addslashes(__('Copies the Manager/System User permission set.'));?>',
        agency: '<?php echo addslashes(__('Copies the Agency permission set.'));?>',
        agent: '<?php echo addslashes(__('Copies the Agent permission set.'));?>'
    };
    $('#UserRoleCategory').on('change',function(){
        var value=$(this).val()||'';
        $('#roleCategoryHelp').text(categoryHelp[value]||'<?php echo addslashes(__('Select the category first. Related permissions will be copied automatically and can be customized after saving.'));?>');
    }).trigger('change');
});
</script>
