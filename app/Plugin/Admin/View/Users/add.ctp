<?php if($type =="AY"){$pageName=__('Add Agency');}
elseif($type=="AT"){$pageName=__('Add Agent');}
else{$pageName=__('Add User');}
if(!isset($pageName)){
    $pageName="&nbsp;";}
    ?>
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
.user-admin-form-shell{max-width:1320px;margin:0 auto;}
.user-admin-form-shell .panel{border:1px solid #dbe4ef;border-radius:12px;box-shadow:0 14px 32px rgba(15,23,42,.08);overflow:hidden;}
.user-admin-form-shell .panel-body{padding:18px;background:linear-gradient(180deg,#ffffff 0%,#f7faff 100%);}
.user-admin-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px;align-items:start;}
.user-admin-grid .form-group{margin:0;background:#fff;border:1px solid #dde6f1;border-radius:10px;padding:12px;box-shadow:0 8px 18px rgba(15,23,42,.05);}
.user-admin-grid .form-group:before,.user-admin-grid .form-group:after{display:none;}
.user-admin-grid .control-label,.user-admin-grid [class*="col-sm-"]{float:none;width:100%;max-width:100%;padding:0;text-align:left;}
.user-admin-grid .control-label{font-size:11px;font-weight:800;color:#4a5f79;text-transform:uppercase;letter-spacing:.2px;margin:0 0 8px;}
.user-admin-grid .form-control{height:40px;border-radius:8px;border:1px solid #cfd9e6;box-shadow:none;}
.user-admin-grid textarea.form-control{height:120px;resize:vertical;}
.user-admin-grid select[multiple].form-control{min-height:120px;height:120px;}
.user-admin-grid .help-block,
.user-admin-grid .text-muted{display:block;margin-top:6px;font-size:11px;line-height:1.45;}
.user-admin-grid .user-grid-span-2{grid-column:span 2;}
.user-admin-grid .user-grid-span-3{grid-column:1 / -1;}
.user-admin-grid .user-grid-actions{padding:0;background:transparent;border:0;box-shadow:none;}
.user-admin-grid .user-grid-actions .col-sm-offset-2{margin-left:0;}
.user-admin-grid .user-grid-actions .col-sm-10{width:100%;}
@media (max-width: 1199px){
    .user-admin-grid{grid-template-columns:repeat(2,minmax(0,1fr));}
}
@media (max-width: 767px){
    .user-admin-form-shell .panel-body{padding:14px;}
    .user-admin-grid{grid-template-columns:minmax(0,1fr);gap:12px;}
    .user-admin-grid .user-grid-span-2,
    .user-admin-grid .user-grid-span-3{grid-column:auto;}
}
</style>
<script type="text/javascript">
(function($){
    function syncSystemAgencyVisibility(){
        var $agencyRow = $('#UserAgencySystem');
        if(!$agencyRow.length){return;}
        $agencyRow.show();
    }
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
        syncSystemAgencyVisibility();
        $('.js-front-view-toggle').each(function(){
            syncFrontViewToggle($(this));
        });
    });
})(jQuery);
</script>
<div class="content-title">
	<div class="content-title-inner">
		<div class="container">		
			<h1><?php echo $pageName;?></h1>	
		</div><!-- /.container -->
	</div><!-- /.content-title-inner -->
</div><!-- /.content-title -->
<div class="content">
<div class="container user-admin-form-shell">
<div class="panel">
    <div class="panel-heading">
    </div>
                <div class="panel-body"><?php echo $this->Session->flash();?>
                <?php echo $this->Form->create('User', array('name'=>'post_req','id'=>'post_req','class'=>'form-horizontal','type'=>'file'));?>
            <div class="user-admin-grid">
            <?php if(!isset($type)) {?>
            <?php if($userType!='AGY'){$name="Name";?>
            <div class="form-group">
                <label for="group_name" class="col-sm-2 control-label"><?php echo __('User');?> <?php echo __('Level');?></label>
                <div class="col-sm-4">
                    <?php echo $this->Form->select('ugroup_id',$ugroup,array('empty'=>__('Select'),'label' => false,'class'=>'form-control','div'=>false));?>
                </div>
            </div>
            <div class="form-group" id="UserAgencySystem">
                <label for="group_name" class="col-sm-2 control-label"><?php echo __('Primary Agency');?></label>
                <div class="col-sm-4">
                    <?php echo $this->Form->select('project_id',$project,array('empty'=>__('Select Agency'),'label' => false,'class'=>'form-control','div'=>false));?>
                    <small class="text-muted"><?php echo __('This is the default agency for this user.');?></small>
                </div>
            </div>
            <div class="form-group user-grid-span-2" id="UserAdditionalAgenciesSystem">
                <label for="group_name" class="col-sm-2 control-label"><?php echo __('Additional Agencies');?></label>
                <div class="col-sm-4">
                    <?php echo $this->Form->select('project_ids',$project,array('multiple'=>'multiple','label' => false,'class'=>'form-control','div'=>false));?>
                    <small class="text-muted"><?php echo __('Selected agencies will also show this user in backend agency lists.');?></small>
                </div>
            </div>
            <?php }}
            elseif($type =='AY') {$name='Agency Name';?>
                        <div class="form-group">
                            <label class="col-sm-2 control-label"><?php echo __('Agency Level');?></label>
                            <div class="col-sm-4"><?php echo $this->Form->select('ugroup_id',$agencyRoleOptions,array('empty'=>__('Select'),'class'=>'form-control','required'=>true));?></div>
                        </div>
            <?php }
            elseif($type =='AT') {$name='Agent Name';?>
                        <div class="form-group">
                            <label class="col-sm-2 control-label"><?php echo __('Agent Level');?></label>
                            <div class="col-sm-4"><?php echo $this->Form->select('ugroup_id',$agentRoleOptions,array('empty'=>__('Select'),'class'=>'form-control','required'=>true));?></div>
                        </div>
            <?php
                        if($userType=="AGY"){
                            echo $this->Form->input('project_id',array('value' =>$pId,'type'=>'hidden'));
                        }
                        elseif($userType=='ADR'){?>
                        <div class="form-group">
                            <div id="UserAgency">
                                <label for="group_name" class="col-sm-2 control-label"><?php echo __('Primary Agency');?></label>
                                <div class="col-sm-4">
                                    <?php echo $this->Form->select('project_id',$project,array('empty'=>__('Select'),'label' => false,'class'=>'form-control','div'=>false));?>
                                    <small class="text-muted"><?php echo __('This is the default agency for this user.');?></small>
                                </div>
                            </div>
                        </div>
                        <div class="form-group user-grid-span-2">
                            <div id="UserAdditionalAgencies">
                                <label for="group_name" class="col-sm-2 control-label"><?php echo __('Additional Agencies');?></label>
                                <div class="col-sm-4">
                                    <?php echo $this->Form->select('project_ids',$project,array('multiple'=>'multiple','label' => false,'class'=>'form-control','div'=>false));?>
                                    <small class="text-muted"><?php echo __('Selected agencies will also show this agent in backend agency lists.');?></small>
                                </div>
                            </div>
                        </div>
                        <?php }
            }?>
            
            <div class="form-group">
                <label for="group_name" class="col-sm-2 control-label"><?php echo __($name);?></label>
                <div class="col-sm-4">
                   <?php echo $this->Form->input('name',array('label' => false,'class'=>'form-control','placeholder'=>__($name),'div'=>false));?>
                </div>
            </div>
            <div class="form-group">
                <label for="group_name" class="col-sm-2 control-label"><?php echo __('Username');?></label>
                <div class="col-sm-4">
                   <?php echo $this->Form->input('username',array('label' => false,'class'=>'form-control','placeholder'=>__('User Name'),'div'=>false));?>
                </div>
                <label for="group_name" class="col-sm-2 control-label"><?php echo __('Password');?></label>
                <div class="col-sm-4">
                   <?php echo $this->Form->password('password',array('label' => false,'class'=>'form-control validate[required,minSize[4],maxSize[15]]','required'=>'required','placeholder'=>__('Password'),'div'=>false));?>
                </div>
            </div>
            <div class="form-group user-grid-span-2">
                <label for="group_name" class="col-sm-2 control-label"><?php echo __('Address').':';?></label>
                <div class="col-sm-10">
                   <?php echo $this->Form->input('address',array('label' => false,'class'=>'form-control','placeholder'=>__('Address'),'div'=>false));?>
                </div>
            </div>
            <div class="form-group">
                <label for="group_name" class="col-sm-2 control-label"><?php echo __('Location').':';?></label>
                <div class="col-sm-4">
                   <?php echo $this->Form->select('location_id',$location,array('label' => false,'class'=>'form-control','empty'=>__('Please select'),'div'=>false));?>
                </div>
            </div>
            <div class="form-group">
                <label for="group_name" class="col-sm-2 control-label"><?php echo __('Phone');?></label>
                <div class="col-sm-4">
                   <?php echo $this->Form->input('phone',array('label' => false,'class'=>'form-control','placeholder'=>__('Phone'),'div'=>false));?>
                </div>
                <label for="group_name" class="col-sm-2 control-label"><?php echo __('Mobile');?></label>
                <div class="col-sm-4">
                   <?php echo $this->Form->input('mobile',array('label' => false,'class'=>'form-control','placeholder'=>__('Mobile'),'div'=>false));?>
                </div>
            </div>
            <div class="form-group">
                <label for="group_name" class="col-sm-2 control-label"><?php echo __('Email ID');?></label>
                <div class="col-sm-4">
                   <?php echo $this->Form->input('email',array('label' => false,'class'=>'form-control','placeholder'=>__('Email ID'),'div'=>false));?>
                </div>
                <label for="group_name" class="col-sm-2 control-label"><?php echo __('Skype ID');?></label>
                <div class="col-sm-4">
                   <?php echo $this->Form->input('skype',array('label' => false,'class'=>'form-control','placeholder'=>__('Skype ID'),'div'=>false));?>
                </div>
            </div>
            <div class="form-group">
                <label for="group_name" class="col-sm-2 control-label"><?php echo __('Twitter ID');?></label>
                <div class="col-sm-4">
                   <?php echo $this->Form->input('twitter',array('label' => false,'class'=>'form-control','placeholder'=>__('Twitter ID'),'div'=>false));?>
                </div>
                <label for="group_name" class="col-sm-2 control-label"><?php echo __('Facebook ID');?></label>
                <div class="col-sm-4">
                   <?php echo $this->Form->input('facebook',array('label' => false,'class'=>'form-control','placeholder'=>__('Facebook ID'),'div'=>false));?>
                </div>
            </div>
            <div class="form-group">
                <label for="group_name" class="col-sm-2 control-label"><?php echo __('Linkedin ID');?></label>
                <div class="col-sm-4">
                   <?php echo $this->Form->input('linkedin',array('label' => false,'class'=>'form-control','placeholder'=>__('Linkedin ID'),'div'=>false));?>
                </div>
                <label for="group_name" class="col-sm-2 control-label"><?php echo __('Upload Photo');?></label>
                <div class="col-sm-4">
                    <?php echo $this->Form->input('photo',array('type' => 'file','label' => false,'div'=>false));?>
                </div>	    
            </div>
            <div class="form-group user-grid-span-2">
                <label for="group_name" class="col-sm-2 control-label"><?php echo __('Description').':';?></label>
                <div class="col-sm-10">
                   <?php echo $this->Form->input('description',array('label' => false,'class'=>'form-control','placeholder'=>__('Description'),'div'=>false));?>
                </div>
            </div>
            <?php if(isset($type) && $type==='AT' && !empty($hasSortIdColumn)){?>
            <div class="form-group">
                <label for="group_name" class="col-sm-2 control-label"><?php echo __('Sort ID / Frontend Order');?></label>
                <div class="col-sm-4">
                   <?php echo $this->Form->input('sort_id',array('type'=>'number','min'=>0,'label' => false,'class'=>'form-control','placeholder'=>__('Sort ID'),'div'=>false));?>
                   <small class="text-muted"><?php echo __('Use 1, 2, 3... Lower numbers show first on frontend and in sorted agent tables.');?></small>
                </div>
            </div>
            <?php }?>
            <?php if(isset($type) && ($type==='AY' || $type==='AT')){?>
            <div class="form-group">
                <label for="group_name" class="col-sm-2 control-label"><?php echo __('Frontend View');?></label>
                <div class="col-sm-4">
                   <?php
                   $frontSwitchId='UserFrontViewSwitch';
                   $frontViewValue=isset($this->request->data['User']['front_view']) ? trim((string)$this->request->data['User']['front_view']) : 'On';
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
            <?php }?>
            <div class="form-group text-left user-grid-span-3 user-grid-actions">
                <div class="col-sm-offset-2 col-sm-10">
                    <button type="submit" class="btn btn-success"><span class="fa fa-plus-circle"></span> <?php echo __('Save');?></button>
                    <?php if(!isset($type)) { echo$this->Html->link('<span class="fa fa-close"></span> '.__('Close'),array('controller'=>'Users','action'=>'index'),array('class'=>'btn btn-danger','escape'=>false));}
                    elseif($type =='AY') { echo$this->Html->link('<span class="fa fa-close"></span> '.__('Close'),array('controller'=>'Agencies','action'=>'index'),array('class'=>'btn btn-danger','escape'=>false));}
                    elseif($type =='AT') { echo$this->Html->link('<span class="fa fa-close"></span> '.__('Close'),array('controller'=>'Agents','action'=>'index'),array('class'=>'btn btn-danger','escape'=>false));}
                    ?>
                </div>
            </div>
            </div>
            <?php echo$this->Form->end();?>
        </div>
</div>
</div>
</div>
