<?php $pageName=__('Edit User');
if($isError){echo $this->element('start-middle',array('pageName'=>$pageName));?><?php }?>
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
.user-drop-upload{
    position:relative;
    border:2px dashed #cdd7e6;
    border-radius:8px;
    background:#f9fbff;
    padding:10px;
    min-height:130px;
    cursor:pointer;
    transition:all .2s ease;
}
.user-drop-upload:hover,
.user-drop-upload.drag-over{
    border-color:#2c90d9;
    background:#eef6ff;
}
.user-drop-upload .user-drop-preview{
    text-align:center;
    min-height:72px;
}
.user-drop-upload .user-drop-preview img{
    max-width:100%;
    max-height:120px;
    border-radius:6px;
}
.user-drop-upload .user-drop-message{
    font-size:12px;
    color:#6c7a92;
    text-align:center;
    margin-top:12px;
}
.user-drop-upload.has-image .user-drop-message{
    margin-top:6px;
}
.user-drop-upload .user-drop-actions{
    text-align:center;
    margin-top:8px;
}
.user-drop-upload input[type="file"]{
    position:absolute;
    left:-9999px;
    top:-9999px;
}
.user-admin-form-shell{max-width:1320px;margin:0 auto;}
.user-admin-form-shell .panel{border:1px solid #dbe4ef;border-radius:12px;box-shadow:0 14px 32px rgba(15,23,42,.08);overflow:hidden;}
.user-admin-form-shell .panel-body{padding:18px;background:linear-gradient(180deg,#ffffff 0%,#f7faff 100%);}
.user-edit-section{margin-bottom:16px;border:1px solid #dbe4ef;border-radius:12px;box-shadow:0 10px 24px rgba(15,23,42,.06);overflow:hidden;}
.user-edit-section > .panel-heading{background:#f6f9fe;border-bottom:1px solid #dde6f1;}
.user-edit-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px;align-items:start;}
.user-edit-grid .form-group{margin:0;background:#fff;border:1px solid #dde6f1;border-radius:10px;padding:12px;box-shadow:0 8px 18px rgba(15,23,42,.05);}
.user-edit-grid .form-group:before,.user-edit-grid .form-group:after{display:none;}
.user-edit-grid .control-label,.user-edit-grid [class*="col-sm-"]{float:none;width:100%;max-width:100%;padding:0;text-align:left;}
.user-edit-grid .control-label{font-size:11px;font-weight:800;color:#4a5f79;text-transform:uppercase;letter-spacing:.2px;margin:0 0 8px;}
.user-edit-grid .form-control{height:40px;border-radius:8px;border:1px solid #cfd9e6;box-shadow:none;}
.user-edit-grid textarea.form-control{height:120px;resize:vertical;}
.user-edit-grid select[multiple].form-control{min-height:120px;height:120px;}
.user-edit-grid .help-block,
.user-edit-grid .text-muted{display:block;margin-top:6px;font-size:11px;line-height:1.45;}
.user-grid-span-2{grid-column:span 2;}
.user-grid-span-3{grid-column:1 / -1;}
.user-edit-actions{padding:0;background:transparent;border:0;box-shadow:none;}
.user-edit-actions .col-sm-offset-2{margin-left:0;}
.user-edit-actions .col-sm-7,
.user-edit-actions .col-sm-10{width:100%;}
@media (max-width: 1199px){
    .user-edit-grid{grid-template-columns:repeat(2,minmax(0,1fr));}
}
@media (max-width: 767px){
    .user-admin-form-shell .panel-body{padding:14px;}
    .user-edit-grid{grid-template-columns:minmax(0,1fr);gap:12px;}
    .user-grid-span-2,
    .user-grid-span-3{grid-column:auto;}
}
</style>
<script type="text/javascript">
(function($){
    function syncFrontViewToggle($toggle){
        var isOn = $toggle.is(':checked');
        var $stateLabel = $toggle.closest('.front-view-switch-wrap').find('.front-view-state-label');
        $stateLabel.text(isOn ? 'On' : 'Off');
        $stateLabel.toggleClass('is-on',isOn).toggleClass('is-off',!isOn);
    }
    function bindUserDropUpload(scope){
        scope.find('.user-drop-upload').each(function(){
            var $box = $(this);
            var inputId = $box.attr('data-input-id');
            var $input = $('#' + inputId);
            var $preview = $box.find('.user-drop-preview');
            if(!$input.length){
                return;
            }

            var showPreview = function(file){
                if(!file || !file.type || file.type.indexOf('image/') !== 0){
                    return;
                }
                var reader = new FileReader();
                reader.onload = function(e){
                    $preview.html('<img src="' + e.target.result + '" alt="Preview">');
                    $box.addClass('has-image');
                };
                reader.readAsDataURL(file);
            };

            $box.off('click.userDrop').on('click.userDrop', function(e){
                if($(e.target).hasClass('js-user-drop-browse') || $(e.target).closest('.js-user-drop-browse').length){
                    return;
                }
                $input[0].click();
            });
            $box.find('.js-user-drop-browse').off('click.userDrop').on('click.userDrop', function(e){
                e.preventDefault();
                e.stopPropagation();
                $input[0].click();
            });
            $input.off('change.userDrop').on('change.userDrop', function(){
                if(this.files && this.files.length){
                    showPreview(this.files[0]);
                }
            });
            $box.off('dragenter.userDrop dragover.userDrop').on('dragenter.userDrop dragover.userDrop', function(e){
                e.preventDefault();
                e.stopPropagation();
                $box.addClass('drag-over');
            });
            $box.off('dragleave.userDrop').on('dragleave.userDrop', function(e){
                e.preventDefault();
                e.stopPropagation();
                $box.removeClass('drag-over');
            });
            $box.off('drop.userDrop').on('drop.userDrop', function(e){
                e.preventDefault();
                e.stopPropagation();
                $box.removeClass('drag-over');
                var dt = e.originalEvent.dataTransfer;
                if(dt && dt.files && dt.files.length){
                    try{
                        $input[0].files = dt.files;
                    }catch(err){}
                    if($input[0].files && $input[0].files.length){
                        $input.trigger('change');
                    }else{
                        showPreview(dt.files[0]);
                    }
                }
            });
        });
    }
    $(document).on('change','.js-front-view-toggle',function(){
        syncFrontViewToggle($(this));
    });
    $(function(){
        $('.js-front-view-toggle').each(function(){
            syncFrontViewToggle($(this));
        });
        bindUserDropUpload($(document));
    });
})(jQuery);
</script>
<div class="<?php echo !$isError ? 'container user-admin-form-shell' : 'user-admin-form-shell';?>">
    <div class="panel panel-custom mrg">
	<div class="panel-heading"><?php echo$pageName;?><?php if(!$isError){?><button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button><?php }?></div>            
                <div class="panel-body"><?php echo $this->Session->flash();?>
                <?php echo $this->Form->create('User', array( 'controller' => 'User','name'=>'post_req','id'=>'post_req','class'=>'form-horizontal','type'=>'file'));?>
					<?php foreach ($User as $k=>$post): if($userType=='ADR' || ($userType=='AGY' && $luserId==$post['User']['project_id'])) { $id=$post['User']['id']; $form_no=$k;?>
						<div class="panel panel-default user-edit-section">
							<div class="panel-heading"><strong><span class="text-danger"><?php echo __('Form');?> <?php echo$form_no?></span></strong></div>
							<div class="panel-body user-edit-grid">
								<?php if($userType!='AGY'){?>
								<div class="form-group">
									<?php if($id!=1){?>
									<label for="group_name" class="col-sm-2 control-label"><?php echo __('User Level');?></label>
									<div class="col-sm-4">
									   <?php echo $this->Form->select("$k.User.ugroup_id",$ugroup,array('empty'=>null,'label' => false,'class'=>'form-control input-sm','div'=>false));?>
									</div>
									<?php }?>
									<div id="UserAgency<?php echo$k;?>">
									    <label for="group_name" class="col-sm-2 control-label"><?php echo __('Primary Agency');?></label>
									    <div class="col-sm-4">
									       <?php echo $this->Form->select("$k.User.project_id",$project,array('empty'=>__('Select'),'label' => false,'class'=>'form-control input-sm','div'=>false));?>
									       <small class="text-muted"><?php echo __('This is the default agency for this user.');?></small>
									    </div>
									</div>
								</div>
								<?php if($id!=1){?>
								<div class="form-group user-grid-span-2">
								    <label for="group_name" class="col-sm-2 control-label"><?php echo __('Additional Agencies');?></label>
								    <div class="col-sm-4">
								       <?php echo $this->Form->select("$k.User.project_ids",$project,array('multiple'=>'multiple','label' => false,'class'=>'form-control input-sm','div'=>false));?>
								       <?php
								       $agencyHelpGroupId = isset($post['User']['ugroup_id']) ? (int)$post['User']['ugroup_id'] : 0;
								       $agencyHelpText = ($agencyHelpGroupId===2) ? __('Selected agencies will also show this agent in backend agency lists.') : __('Selected agencies will also show this user in backend agency lists.');
								       ?>
								       <small class="text-muted"><?php echo $agencyHelpText;?></small>
								    </div>
								</div>
								<?php }?>
								 <?php } else {echo $this->Form->input("$k.User.project_id",array('value'=>$post['User']['project_id'],'type'=>'hidden'));}?>
								<div class="form-group">
									<label for="group_name" class="col-sm-2 control-label"><?php echo __($post['Ugroup']['name'].' Name');?></label>
									<div class="col-sm-4">
									   <?php echo $this->Form->input("$k.User.name",array('label' => false,'class'=>'form-control','placeholder'=>__($post['Ugroup']['name'].' Name'),'div'=>false));?>
									</div>
								</div>
								<div class="form-group">
									<label for="group_name" class="col-sm-2 control-label"><?php echo __('User Name');?></label>
									<div class="col-sm-4">
									   <?php echo $this->Form->input("$k.User.username",array('label' => false,'class'=>'form-control','placeholder'=>__('User Name'),'div'=>false));?>
									</div>
									<label for="group_name" class="col-sm-2 control-label"><?php echo __('Password');?></label>
									<div class="col-sm-4">
									   <?php echo $this->Form->password("$k.User.password",array('value'=>'','label' => false,'class'=>'form-control input-sm validate[minSize[4],maxSize[15]]','placeholder'=>__('Password'),'div'=>false));?>
									</div>
								</div>
								<div class="form-group user-grid-span-2">
								    <label for="group_name" class="col-sm-2 control-label"><?php echo __('Address').':';?></label>
								    <div class="col-sm-10">
								       <?php echo $this->Form->input("$k.User.address",array('label' => false,'class'=>'form-control','placeholder'=>__('Address'),'div'=>false));?>
								    </div>
								</div>
								<div class="form-group">
								    <label for="group_name" class="col-sm-2 control-label"><?php echo __('Location').':';?></label>
								    <div class="col-sm-10">
								       <?php echo $this->Form->select("$k.User.location_id",$location,array('label' => false,'class'=>'form-control','empty'=>__('Please select'),'div'=>false));?>
								    </div>
								</div>
								<div class="form-group">
									<label for="group_name" class="col-sm-2 control-label"><?php echo __('Phone');?></label>
									<div class="col-sm-4">
									   <?php echo $this->Form->input("$k.User.phone",array('label' => false,'class'=>'form-control','placeholder'=>__('Phone'),'div'=>false));?>
									</div>
									<label for="group_name" class="col-sm-2 control-label"><?php echo __('Mobile');?></label>
									<div class="col-sm-4">
									   <?php echo $this->Form->input("$k.User.mobile",array('label' => false,'class'=>'form-control','placeholder'=>__('Mobile'),'div'=>false));?>
									</div>
								</div>
								<div class="form-group">
									<label for="group_name" class="col-sm-2 control-label"><?php echo __('Email');?></label>
									<div class="col-sm-4">
									   <?php echo $this->Form->input("$k.User.email",array('label' => false,'class'=>'form-control','placeholder'=>__('Email'),'div'=>false));?>
									</div>
									<label for="group_name" class="col-sm-2 control-label"><?php echo __('Skype ID');?></label>
									<div class="col-sm-4">
									   <?php echo $this->Form->input("$k.User.skype",array('label' => false,'class'=>'form-control','placeholder'=>__('Skype ID'),'div'=>false));?>
									</div>
								</div>
								<div class="form-group">
									<label for="group_name" class="col-sm-2 control-label"><?php echo __('Twitter ID');?></label>
									<div class="col-sm-4">
									   <?php echo $this->Form->input("$k.User.twitter",array('label' => false,'class'=>'form-control','placeholder'=>__('Twitter ID'),'div'=>false));?>
									</div>
									<label for="group_name" class="col-sm-2 control-label"><?php echo __('Facebook ID');?></label>
									<div class="col-sm-4">
									   <?php echo $this->Form->input("$k.User.facebook",array('label' => false,'class'=>'form-control','placeholder'=>__('Facebook ID'),'div'=>false));?>
									</div>
								</div>
								<div class="form-group">
								    <label for="group_name" class="col-sm-2 control-label"><?php echo __('Linkedin ID');?></label>
								    <div class="col-sm-4">
								       <?php echo $this->Form->input("$k.User.linkedin",array('label' => false,'class'=>'form-control','placeholder'=>__('Linkedin ID'),'div'=>false));?>
								    </div>
								    <label for="group_name" class="col-sm-2 control-label"><?php echo __('Upload Photo');?></label>
								    <div class="col-sm-4">
									<?php
									    $userPhotoThumb = '';
									    if(!empty($post['User']['photo']))
									    {
									        $photoFileName = trim((string)$post['User']['photo']);
									        if($photoFileName !== '')
									        {
									            $thumbPath = WWW_ROOT.'img'.DS.'user_thumb'.DS.$photoFileName;
									            $fullPath = WWW_ROOT.'img'.DS.$photoFileName;
									            if(file_exists($thumbPath))
									            {
									                $userPhotoThumb = 'user_thumb/'.$photoFileName;
									            }
									            elseif(file_exists($fullPath))
									            {
									                $userPhotoThumb = $photoFileName;
									            }
									        }
									    }
									    $userPhotoInputId = 'User'.$k.'Photo';
									?>
									<div class="user-drop-upload<?php if(strlen($userPhotoThumb)>0){?> has-image<?php }?>" data-input-id="<?php echo h($userPhotoInputId);?>">
									    <div class="user-drop-preview">
									        <?php if(strlen($userPhotoThumb)>0){ echo $this->Html->image($userPhotoThumb,array('alt'=>__('User Photo')));}?>
									    </div>
									    <div class="user-drop-message"><?php echo __('Drag & drop image here');?></div>
									    <div class="user-drop-actions"><button type="button" class="btn btn-default btn-xs js-user-drop-browse"><?php echo __('Browse');?></button></div>
									    <?php echo $this->Form->input("$k.User.photo",array('type' => 'file','label' => false,'div'=>false,'accept'=>'image/*','id'=>$userPhotoInputId));?>
									</div>
								    </div>	    
								</div>
								<?php if($id!=1){?>
								<div class="form-group">
									<label for="group_name" class="col-sm-2 control-label"><?php echo __('Status');?></label>
									<div class="col-sm-4">
									   <?php echo $this->Form->select("$k.User.status",array('Active'=>__('Active'),'Suspend'=>__('Suspend')),array('empty'=>null,'label' => false,'class'=>'form-control','div'=>false));?>
									</div>
								</div>
								<?php }?>
								<?php
								$currentUgroupId = null;
								if(isset($this->request->data[$k]['User']['ugroup_id']))
								{
									$currentUgroupId = (int)$this->request->data[$k]['User']['ugroup_id'];
								}
								elseif(isset($post['User']['ugroup_id']))
								{
									$currentUgroupId = (int)$post['User']['ugroup_id'];
								}
								$showFrontViewToggle = in_array($currentUgroupId,array(2,4));
								if(!empty($hasSortIdColumn) && $currentUgroupId===2){
								?>
								<div class="form-group">
									<label for="group_name" class="col-sm-2 control-label"><?php echo __('Sort ID / Frontend Order');?></label>
									<div class="col-sm-4">
									   <?php echo $this->Form->input("$k.User.sort_id",array('type'=>'number','min'=>0,'label' => false,'class'=>'form-control','placeholder'=>__('Sort ID'),'div'=>false));?>
									   <small class="text-muted"><?php echo __('Use 1, 2, 3... Lower numbers show first on frontend and in sorted agent tables.');?></small>
									</div>
								</div>
								<?php }?>
								<?php if($showFrontViewToggle){
								?>
								<div class="form-group">
									<label for="group_name" class="col-sm-2 control-label"><?php echo __('Frontend View');?></label>
									<div class="col-sm-4">
									   <?php
									   $frontSwitchId=$k.'UserFrontViewSwitch';
									   $frontViewValue=isset($this->request->data[$k]['User']['front_view']) ? trim((string)$this->request->data[$k]['User']['front_view']) : 'On';
									   $isFrontViewOn=($frontViewValue!=='Off');
									   ?>
									   <div class="front-view-switch-wrap">
									       <label class="front-view-switch" for="<?php echo $frontSwitchId;?>">
										   <?php echo $this->Form->checkbox("$k.User.front_view",array('id'=>$frontSwitchId,'class'=>'js-front-view-toggle','label'=>false,'div'=>false,'hiddenField'=>'Off','value'=>'On','checked'=>$isFrontViewOn));?>
										   <span class="front-view-slider"></span>
									       </label>
									       <span class="front-view-state-label <?php if($isFrontViewOn){?>is-on<?php }else{?>is-off<?php }?>"><?php echo ($isFrontViewOn ? __('On') : __('Off'));?></span>
									   </div>
									</div>
								</div>
								<?php }?>
								<div class="form-group user-grid-span-2">
									<label for="group_name" class="col-sm-2 control-label"><?php echo __('Description');?></label>
									<div class="col-sm-10">
									   <?php echo $this->Form->input("$k.User.description",array('label' => false,'class'=>'form-control','placeholder'=>__('Description'),'div'=>false));?>
									</div>
								</div>
								<div class="form-group text-left user-grid-span-3 user-edit-actions">
									<div class="col-sm-offset-2 col-sm-7">
										<?php echo $this->Form->input("$k.User.id", array('type' => 'hidden'));?>
									</div>
								</div>
							</div>	
						</div>						
                    <?php } endforeach;?>
                        <?php unset($post);?>
                        <div class="form-group text-left user-grid-span-3 user-edit-actions">
                        <div class="col-sm-offset-2 col-sm-7">                            
                            <button type="submit" class="btn btn-success"><span class="fa fa-refresh"></span> <?php echo __('Update');?></button>
                            <?php if(!$isError){?><button type="button" class="btn btn-danger" data-dismiss="modal"><span class="fa fa-remove"></span> <?php echo __('Cancel');?></button><?php }?>
                        </div>
                    </div>
                <?php echo$this->Form->end();?>
        </div>
    </div>
    </div>
<?php if($isError){echo $this->element('end-middle');?><?php }?>
