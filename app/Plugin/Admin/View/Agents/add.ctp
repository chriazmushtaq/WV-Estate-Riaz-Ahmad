<style type="text/css">
.front-view-switch-wrap{display:flex;align-items:center;gap:10px;}
.front-view-switch{position:relative;display:inline-block;width:58px;height:30px;margin:0;}
.front-view-switch input{display:none;}
.front-view-slider{position:absolute;cursor:pointer;top:0;left:0;right:0;bottom:0;background:#d9534f;border-radius:30px;transition:all .2s ease;}
.front-view-slider:before{position:absolute;content:'';height:24px;width:24px;left:3px;top:3px;background:#fff;border-radius:50%;transition:all .2s ease;}
.front-view-switch input:checked + .front-view-slider{background:#5cb85c;}
.front-view-switch input:checked + .front-view-slider:before{transform:translateX(28px);}
.front-view-state-label{font-weight:600;font-size:12px;min-width:24px;}
.front-view-state-label.is-on{color:#3c763d;}
.front-view-state-label.is-off{color:#a94442;}
</style>
<script type="text/javascript">
(function($){
    function syncFrontViewToggle($toggle){
        var isOn = $toggle.is(':checked');
        var $stateLabel = $toggle.closest('.front-view-switch-wrap').find('.front-view-state-label');
        $stateLabel.text(isOn ? 'On' : 'Off');
        $stateLabel.toggleClass('is-on',isOn).toggleClass('is-off',!isOn);
    }
    $(document).on('change','.js-front-view-toggle',function(){
        syncFrontViewToggle($(this));
    });
    $(function(){
        $('.js-front-view-toggle').each(function(){
            syncFrontViewToggle($(this));
        });
    });
})(jQuery);
</script>
<div class="page-title"> <div class="title-env"> <h1 class="title"><?php echo __('Add Agents');?></h1></div></div><div class="panel">
    <div class="panel-heading">
    </div>
                <div class="panel-body"><?php echo $this->Session->flash();?>
                <?php echo $this->Form->create('Agent', array( 'controller' => 'Agents', 'action' => 'add','name'=>'post_req','id'=>'post_req','class'=>'form-horizontal','type'=>'file'));?>
            <div class="form-group">
                <label for="group_name" class="col-sm-4 control-label"><small><?php echo __('Name').':';?></small></label>
                <div class="col-sm-6">
                   <?php echo $this->Form->input('name',array('label' => false,'class'=>'form-control','placeholder'=>__('Name'),'div'=>false));?>
                </div>
            </div>
            <div class="form-group">
                <label for="group_name" class="col-sm-4 control-label"><small><?php echo __('Address').':';?></small></label>
                <div class="col-sm-6">
                   <?php echo $this->Form->input('address',array('label' => false,'class'=>'form-control','placeholder'=>__('Address'),'div'=>false));?>
                </div>
            </div>
            <div class="form-group">
                <label for="group_name" class="col-sm-4 control-label"><small><?php echo __('E-Mail').':';?></small></label>
                <div class="col-sm-6">
                   <?php echo $this->Form->input('email',array('label' => false,'class'=>'form-control','placeholder'=>__('E-Mail'),'div'=>false));?>
                </div>
            </div>
            <div class="form-group">
                <label for="group_name" class="col-sm-4 control-label"><small><?php echo __('Facebook ID').':';?></small></label>
                <div class="col-sm-6">
                   <?php echo $this->Form->input('facebook',array('label' => false,'class'=>'form-control','placeholder'=>__('Facebook ID'),'div'=>false));?>
                </div>
            </div>
            <div class="form-group">
                <label for="group_name" class="col-sm-4 control-label"><small><?php echo __('Skype ID').':';?></small></label>
                <div class="col-sm-6">
                   <?php echo $this->Form->input('skype',array('label' => false,'class'=>'form-control','placeholder'=>__('Skype ID'),'div'=>false));?>
                </div>
            </div>
            <div class="form-group">
                <label for="group_name" class="col-sm-4 control-label"><small><?php echo __('Twitter ID').':';?></small></label>
                <div class="col-sm-6">
                   <?php echo $this->Form->input('twitter',array('label' => false,'class'=>'form-control','placeholder'=>__('Twitter ID'),'div'=>false));?>
                </div>
            </div>
            <div class="form-group">
                <label for="group_name" class="col-sm-4 control-label"><small><?php echo __('Linkedin ID').':';?></small></label>
                <div class="col-sm-6">
                   <?php echo $this->Form->input('linkedin',array('label' => false,'class'=>'form-control','placeholder'=>__('Linkedin ID'),'div'=>false));?>
                </div>
            </div>
            <div class="form-group">
                <label for="group_name" class="col-sm-4 control-label"><small><?php echo __('Phone').':';?></small></label>
                <div class="col-sm-6">
                   <?php echo $this->Form->input('phone',array('label' => false,'class'=>'form-control','placeholder'=>__('Phone'),'div'=>false));?>
                </div>
            </div>
            <div class="form-group">
                <label for="group_name" class="col-sm-4 control-label"><small><?php echo __('Mobile').':';?></small></label>
                <div class="col-sm-6">
                   <?php echo $this->Form->input('mobile',array('label' => false,'class'=>'form-control','placeholder'=>__('Mobile'),'div'=>false));?>
                </div>
            </div>
            <div class="form-group">
                <label for="group_name" class="col-sm-4 control-label"><small><?php echo __('Description').':';?></small></label>
                <div class="col-sm-6">
                   <?php echo $this->Form->input('description',array('label' => false,'class'=>'form-control','placeholder'=>__('Description'),'div'=>false));?>
                </div>
            </div>
            <?php if(!empty($hasSortIdColumn)){?>
            <div class="form-group">
                <label for="group_name" class="col-sm-4 control-label"><small><?php echo __('Sort ID').':';?></small></label>
                <div class="col-sm-6">
                   <?php echo $this->Form->input('sort_id',array('type'=>'number','min'=>0,'label' => false,'class'=>'form-control','placeholder'=>__('Sort ID'),'div'=>false));?>
                   <small class="text-muted"><?php echo __('Use 1, 2, 3... Numbered agents show first on frontend.');?></small>
                </div>
            </div>
            <?php }?>
            <div class="form-group">
                <label for="group_name" class="col-sm-4 control-label"><small><?php echo __('Login Status').':';?></small></label>
                <div class="col-sm-6">
		<?php
		$loginStatusOpt = array('Active'=>__('Active'),'Blocked'=>__('Blocked'));
		?>
                   <?php echo $this->Form->select('login_status',$loginStatusOpt,array('label' => false,'class'=>'form-control','empty'=>false,'default'=>'Active','div'=>false));?>
                   <small class="text-muted"><?php echo __('If blocked, this agent cannot login to their account.');?></small>
                </div>
            </div>
            <div class="form-group">
                <label for="group_name" class="col-sm-4 control-label"><small><?php echo __('Frontend View').':';?></small></label>
                <div class="col-sm-6">
                   <?php
                   $frontSwitchId='AgentFrontViewSwitch';
                   $frontViewValue=isset($this->request->data['Agent']['front_view']) ? trim((string)$this->request->data['Agent']['front_view']) : 'On';
                   $isFrontViewOn=($frontViewValue!=='Off');
                   ?>
                   <div class="front-view-switch-wrap">
                       <label class="front-view-switch" for="<?php echo $frontSwitchId;?>">
                           <?php echo $this->Form->checkbox('front_view',array('id'=>$frontSwitchId,'class'=>'js-front-view-toggle','label'=>false,'div'=>false,'hiddenField'=>'Off','value'=>'On','checked'=>$isFrontViewOn));?>
                           <span class="front-view-slider"></span>
                       </label>
                       <span class="front-view-state-label <?php if($isFrontViewOn){?>is-on<?php }else{?>is-off<?php }?>"><?php echo ($isFrontViewOn ? __('On') : __('Off'));?></span>
                   </div>
                </div>
            </div>
            <div class="form-group">
                <label for="group_name" class="col-sm-4 control-label"><small><?php echo __('Upload Photo');?></small></label>
                <div class="col-sm-6">
                    <?php echo $this->Form->input('photo',array('type' => 'file','label' => false,'div'=>false));?>
                </div>	    
            </div>  
                <div class="form-group text-left">
                <div class="col-sm-offset-4 col-sm-6">
                    <button type="submit" class="btn btn-success"><span class="fa fa-plus-circle"></span>&nbsp;<?php echo __('Save');?></button>
                    <?php echo$this->Html->link('<span class="fa fa-close"></span>&nbsp;'.__('Close'),array('controller'=>'Agents','action'=>'index'),array('class'=>'btn btn-danger','escape'=>false));?>
                </div>
            </div>
        <?php echo$this->Form->end();?>
                </div>
            </div>
