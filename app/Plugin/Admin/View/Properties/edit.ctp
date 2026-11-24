<?php
$mapProvider = (isset($defaultMapProvider) && strtolower($defaultMapProvider)==='google') ? 'google' : 'osm';
if($mapProvider==='google')
{
    echo $this->Html->script('https://maps.googleapis.com/maps/api/js?key='.$mapKey.'&libraries=places');
}
else
{
    echo $this->Html->css('/design1000/assets/css/leaflet.css');
    echo $this->Html->script('/design1000/assets/js/leaflet.js');
}
?>
<style>
.property-admin-form {
    max-width: 1280px;
    margin: 0 auto 24px;
    float: none !important;
    width: auto !important;
}
.property-admin-edit-form.property-admin-form {
    clear: both;
    display: block !important;
    float: none !important;
    max-width: none !important;
    margin: 0 0 24px;
    padding-left: 0;
    padding-right: 0;
    width: 100% !important;
}
body.wv-admin-theme .content > .property-admin-edit-form.property-admin-form {
    max-width: none !important;
    width: 100% !important;
}
.property-admin-edit-form .panel,
.property-admin-edit-form .panel-body,
.property-admin-edit-form form,
.property-admin-edit-form .property-edit-record,
.property-admin-edit-form .property-edit-record-body,
.property-admin-edit-form .property-edit-toolbar,
.property-admin-edit-form .form-group {
    box-sizing: border-box;
    max-width: none !important;
    width: 100% !important;
}
.property-admin-edit-form .property-edit-record,
.property-admin-edit-form .property-edit-record-body {
    display: block;
}
body.wv-admin-theme .content .property-admin-edit-form form.property-edit-form-layout {
    display: block !important;
    margin: 0 !important;
    max-width: none !important;
    width: 100% !important;
}
.property-admin-form .panel {
    border: 1px solid #dbe5ef;
    border-radius: 12px;
    box-shadow: 0 14px 36px rgba(15, 23, 42, 0.08);
    overflow: hidden;
}
.property-admin-form .panel-heading {
    background: #082f49 !important;
    color: #ffffff !important;
    border-bottom: 0;
    padding: 15px 20px;
    font-size: 17px;
    font-weight: 800;
}
.property-admin-form .panel-body {
    background: #f8fafc;
    padding: 20px;
}
.property-edit-record {
    background: transparent;
    border: 0;
    box-shadow: none;
    margin: 0;
}
.property-edit-record-title {
    align-items: center;
    background: #eef6ff;
    border: 1px solid #cfe0f4;
    border-radius: 10px;
    color: #082f49;
    display: flex;
    font-size: 13px;
    font-weight: 900;
    justify-content: space-between;
    margin: 0 0 14px;
    padding: 11px 14px;
}
.property-edit-record-title small {
    color: #0f3b63;
    font-size: 12px;
    font-weight: 900;
}
.property-edit-record-body {
    background: transparent;
    padding: 0;
}
.property-admin-form .form-group {
    align-items: start;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    display: grid;
    gap: 7px 18px;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    margin: 0 0 14px;
    padding: 15px;
}
.property-admin-form .form-group:before,
.property-admin-form .form-group:after {
    display: none;
}
.property-admin-form .form-group > label.control-label,
.property-admin-form .form-group > div[class*="col-sm-"] {
    float: none !important;
    margin: 0 !important;
    min-width: 0;
    padding: 0 !important;
    width: auto !important;
}
.property-admin-form .form-group > label.control-label:nth-child(1) {
    grid-column: 1;
    grid-row: 1;
}
.property-admin-form .form-group > div:nth-child(2) {
    grid-column: 1;
    grid-row: 2;
}
.property-admin-form .form-group > label.control-label:nth-child(3) {
    grid-column: 2;
    grid-row: 1;
}
.property-admin-form .form-group > div:nth-child(4) {
    grid-column: 2;
    grid-row: 2;
}
.property-admin-form .form-group > label.control-label:nth-child(5) {
    grid-column: 1;
    grid-row: 3;
}
.property-admin-form .form-group > div:nth-child(6) {
    grid-column: 1;
    grid-row: 4;
}
.property-admin-form .form-group > div.col-sm-10,
.property-admin-form .form-group > div.col-sm-12,
.property-admin-form .form-group > div.col-sm-offset-2 {
    grid-column: 1 / -1 !important;
    grid-row: auto !important;
}
.property-admin-edit-form .form-group {
    grid-template-columns: repeat(3, minmax(0, 1fr));
}
.property-admin-edit-form .form-group > label.control-label:nth-child(1) {
    grid-column: 1;
    grid-row: 1;
}
.property-admin-edit-form .form-group > div:nth-child(2) {
    grid-column: 1;
    grid-row: 2;
}
.property-admin-edit-form .form-group > label.control-label:nth-child(3) {
    grid-column: 2;
    grid-row: 1;
}
.property-admin-edit-form .form-group > div:nth-child(4) {
    grid-column: 2;
    grid-row: 2;
}
.property-admin-edit-form .form-group > label.control-label:nth-child(5) {
    grid-column: 3;
    grid-row: 1;
}
.property-admin-edit-form .form-group > div:nth-child(6) {
    grid-column: 3;
    grid-row: 2;
}
.property-admin-edit-form .form-group > div.col-sm-10,
.property-admin-edit-form .form-group > div.col-sm-12,
.property-admin-edit-form .form-group > div.col-sm-offset-2,
.property-admin-edit-form .property-map-row > div,
.property-admin-edit-form .property-photo-manager-row > div {
    grid-column: 1 / -1 !important;
    grid-row: auto !important;
}
.property-admin-form .control-label {
    color: #0f2742;
    display: block;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .04em;
    line-height: 1.25;
    margin: 0;
    text-align: left;
    text-transform: uppercase;
}
.property-admin-form .control-label small {
    color: inherit;
    font-size: inherit;
    font-weight: inherit;
}
.property-admin-form .form-control {
    background: #fbfdff;
    height: 40px;
    border-color: #cbd5e1;
    border-radius: 8px;
    box-shadow: none;
    color: #07152a;
    font-weight: 700;
    max-width: none !important;
    width: 100% !important;
}
.property-admin-form .form-control:focus {
    background: #ffffff;
    border-color: #ff0a46;
    box-shadow: 0 0 0 3px rgba(255, 10, 70, 0.10);
}
.property-admin-form textarea.form-control {
    height: auto;
}
.property-admin-form select[multiple].form-control {
    height: 130px;
}
.property-admin-form input[type="checkbox"] {
    height: auto !important;
    margin: 0;
    width: auto !important;
}
.property-admin-form input[type="file"] {
    background: #fbfdff;
    border: 1px dashed #a9bdd5;
    border-radius: 8px;
    color: #334155;
    display: block;
    font-weight: 700;
    padding: 10px;
    width: 100%;
}
.property-photo-manager-row {
    background: #f8fbff !important;
    border-color: #cfe0f4 !important;
}
.property-photo-manager-row > div {
    grid-column: 1 / -1 !important;
}
.property-photo-upload-grid {
    display: grid;
    gap: 14px;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    margin-bottom: 14px;
}
.property-photo-upload-box label,
.property-gallery-title {
    color: #0f2742;
    display: block;
    font-size: 11px;
    font-weight: 900;
    letter-spacing: .04em;
    line-height: 1.25;
    margin: 0 0 7px;
    text-transform: uppercase;
}
.property-photo-upload-box .help-block {
    color: #52677f;
    font-size: 12px;
    font-weight: 700;
    line-height: 1.35;
    margin: 6px 0 0;
}
.property-gallery-list {
    display: grid;
    gap: 12px;
    grid-template-columns: repeat(auto-fill, minmax(145px, 1fr));
}
.property-gallery-card {
    background: #fff;
    border: 1px solid #dbe5ef;
    border-radius: 10px;
    overflow: hidden;
}
.property-gallery-card img {
    display: block;
    height: 102px;
    object-fit: cover;
    width: 100%;
}
.property-gallery-choice {
    align-items: center;
    color: #10233f;
    display: flex;
    font-size: 12px;
    font-weight: 900;
    gap: 7px;
    margin: 0;
    padding: 10px;
}
.property-gallery-choice input {
    margin: 0;
}
.property-gallery-empty {
    background: #fff;
    border: 1px dashed #a9bdd5;
    border-radius: 8px;
    color: #52677f;
    font-weight: 800;
    padding: 14px;
}
.property-edit-toolbar {
    align-items: center;
    background: #ffffff;
    border: 1px solid #dbe5ef;
    border-radius: 10px;
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    justify-content: space-between;
    margin-bottom: 14px;
    padding: 12px 15px;
}
.property-edit-toolbar-title {
    color: #07152a;
    font-size: 13px;
    font-weight: 900;
}
.property-edit-toolbar-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}
.property-edit-actions {
    background: #ffffff !important;
    border-color: #dbe5ef !important;
    align-items: center;
}
.property-edit-actions > div {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}
.property-admin-form .radio-inline {
    background: #f8fafc;
    border: 1px solid #dbe5ef;
    border-radius: 999px;
    color: #10233f;
    font-weight: 800;
    margin: 0 8px 8px 0;
    padding: 8px 12px 8px 30px;
}
.property-price-field {
    background: #fff7ed !important;
    border-color: #fb923c !important;
    box-shadow: 0 10px 22px rgba(251, 146, 60, 0.13);
}
.property-price-field .control-label {
    color: #9a3412;
}
.property-price-input {
    height: 48px !important;
    border: 2px solid #fb923c !important;
    background: #fffaf0 !important;
    color: #0f3b63 !important;
    font-size: 22px;
    font-weight: 900;
}
.property-map-row {
    padding: 16px !important;
}
.property-map-row > div {
    grid-column: 1 / -1 !important;
}
.property-map-row label {
    display: block;
    margin-bottom: 10px;
    color: #0f2742;
    font-weight: 800;
}
.submit-map {
    width: 100% !important;
    min-height: 430px;
    border: 1px solid #cbd5e1;
    border-radius: 12px;
    overflow: hidden;
    background: #e2e8f0;
}
.property-admin-form .btn {
    border: 0 !important;
    border-radius: 8px;
    box-shadow: 0 8px 16px rgba(15, 23, 42, 0.10);
    font-weight: 800;
    padding: 10px 15px;
}
.property-admin-form .btn-success {
    background: #047857 !important;
    color: #ffffff !important;
}
.property-admin-form .btn-danger {
    background: #07152a !important;
    color: #ffffff !important;
}
.property-admin-form .mce-tinymce,
.property-admin-form .tox-tinymce {
    border: 1px solid #cbd5e1 !important;
    border-radius: 8px !important;
    overflow: hidden;
}
@media (max-width: 767px) {
    .property-admin-form .panel-body {
        padding: 12px;
    }
    .property-admin-form .form-group {
        grid-template-columns: 1fr;
    }
    .property-admin-edit-form.property-admin-form {
        padding-left: 8px;
        padding-right: 8px;
    }
    .property-photo-upload-grid {
        grid-template-columns: 1fr;
    }
    .property-edit-toolbar {
        align-items: stretch;
        display: block;
    }
    .property-edit-toolbar-actions {
        margin-top: 10px;
    }
    .property-admin-form .form-group > label.control-label,
    .property-admin-form .form-group > div[class*="col-sm-"] {
        grid-column: 1 / -1 !important;
        grid-row: auto !important;
    }
    .submit-map {
        min-height: 320px;
    }
}
</style>
	

<?php
$editPropertyIds = array();
foreach((array)$Property as $editPropertyRow)
{
    if(!empty($editPropertyRow['Property']['id']))
    {
        $editPropertyIds[] = (int)$editPropertyRow['Property']['id'];
    }
}
$editPropertyPass = implode(',', $editPropertyIds);
?>
<div class="property-admin-form property-admin-edit-form">
    <?php echo $this->Session->flash();?>
    <div class="panel panel-default">
	<div class="panel-heading"><?php echo count($editPropertyIds)>1 ? __('Edit Properties') : __('Edit Property');?></div>            
                <div class="panel-body">
		<?php echo $this->Form->create('Property', array( 'url'=>array('controller'=>'Properties','action'=>'edit',$editPropertyPass),'name'=>'post_req','id'=>'post_req','class'=>'form-horizontal wv-no-auto-layout property-edit-form-layout','type'=>'file'));?>
					<?php foreach ($Property as $k=>$post): $id=$post['Property']['id'];$form_no=$k;?>
		<div class="property-edit-record">
		    <?php if(count($editPropertyIds)>1){ ?>
		    <div class="property-edit-record-title"><span><?php echo __('Property Form');?> <?php echo$form_no?></span><small><?php echo !empty($post['Property']['uniq_id']) ? h($post['Property']['uniq_id']) : '#'.(int)$id;?></small></div>
		    <?php } else { ?>
		    <div class="property-edit-toolbar">
		        <div class="property-edit-toolbar-title"><?php echo !empty($post['Property']['uniq_id']) ? __('Office ID').': '.h($post['Property']['uniq_id']) : __('Update property details');?></div>
		        <div class="property-edit-toolbar-actions">
		            <?php echo $this->Html->link('<span class="fa fa-magic"></span> '.__('Enhancements'),array('controller'=>'Propertyenhancements','action'=>'quality',(int)$id),array('class'=>'btn btn-info','escape'=>false));?>
		            <?php echo $this->Html->link('<span class="fa fa-search"></span> '.__('SEO'),array('controller'=>'Propertyenhancements','action'=>'seo',(int)$id),array('class'=>'btn btn-primary','escape'=>false));?>
		            <?php echo $this->Html->link('<span class="fa fa-list"></span> '.__('Back To Properties'),array('controller'=>'Properties','action'=>'index'),array('class'=>'btn btn-danger','escape'=>false));?>
		        </div>
		    </div>
		    <?php } ?>
		    <div class="property-edit-record-body">
                <?php if($userType!='AGT' && $userType!='AGY'){?>
		<div class="form-group">
			<label for="group_name" class="col-sm-2 control-label"><small><?php echo __('Agency');?>:</small></label>
			<div class="col-sm-4">
			   <?php
			   echo $this->Form->select("$k.Property.project_id",$agency,array('label' => false,'class'=>'form-control','empty'=>__('Select'),'div'=>false));?>
			</div>			
		    </div>
		    <?php }?>
                    <div class="form-group">
			<label for="group_name" class="col-sm-2 control-label"><small><?php echo __('Title');?>:</small></label>
                        <div class="col-sm-4">
                           <?php echo $this->Form->input("$k.Property.name",array('label' => false,'class'=>'form-control','placeholder'=>__('Title'),'div'=>false));?>
                        </div>
                        <label for="group_name" class="col-sm-2 control-label"><small><?php echo __('Type');?>:</small></label>
                        <div class="col-sm-4">
                           <?php echo $this->Form->select("$k.Property.type_id",$type,array('legend'=>false,'label' => false,'empty'=>__('Select'),'div'=>false,'class'=>'form-control'));?>
                        </div>
                    </div>
		    <div class="form-group">
			<div class="col-sm-12">
			    <?php
			    $currentPropertyData = !empty($this->request->data[$k]['Property']) ? $this->request->data[$k]['Property'] : (!empty($post['Property']) ? $post['Property'] : array());
			    if (!empty($currentPropertyData) && class_exists('ClassRegistry')) {
			        try {
			            $AddresshierarchyModel = ClassRegistry::init('Admin.Addresshierarchy');
			            if ($AddresshierarchyModel && method_exists($AddresshierarchyModel, 'resolveHierarchyFromRow')) {
			                $resolvedHierarchy = $AddresshierarchyModel->resolveHierarchyFromRow($currentPropertyData);
			                if (!empty($resolvedHierarchy)) {
			                    $currentPropertyData = array_merge($currentPropertyData, $resolvedHierarchy);
			                }
			            }
			        } catch(Exception $e) {}
			    }
			    echo $this->element('address_hierarchy_fields', array('fieldPrefix'=>$k.'.Property', 'current'=>$currentPropertyData, 'wrapperId'=>'PropertyEditAddressHierarchy'.$k));
			    ?>
			</div>
		    </div>
		    <div class="form-group">
			<label for="group_name" class="col-sm-2 control-label"><small><?php echo __('Location');?>:</small></label>
			<div class="col-sm-4">
			   <?php echo $this->Form->select("$k.Property.location_id",$location,array('label' => false,'class'=>'form-control','empty'=>__('Select'),'div'=>false));?>
			</div>
			<label for="group_name" class="col-sm-2 control-label"><small><?php echo __('Address');?>:</small></label>
			<div class="col-sm-4">
			   <?php echo$this->Form->input("$k.Property.address",array('id'=>'address-map'.$k,'label' => false,'class'=>'form-control','placeholder'=>__('Address'),'div'=>false));?>
			</div>
		    </div>
		    <div class="form-group">
			<label for="group_name" class="col-sm-2 control-label"><small><?php echo __('Latitude');?>:</small></label>
			<div class="col-sm-2">
			   <?php echo $this->Form->input("$k.Property.latitude",array('id'=>$k.'latitude','readonly'=>true,'label' => false,'class'=>'form-control','placeholder'=>__('Latitude'),'div'=>false));?>
			</div>
			<label for="group_name" class="col-sm-1 control-label"><small><?php echo __('Longitude');?>:</small></label>
			<div class="col-sm-2">
			   <?php echo $this->Form->input("$k.Property.longitude",array('id'=>$k.'longitude','readonly'=>true,'label' => false,'class'=>'form-control','placeholder'=>__('Longitude'),'div'=>false));?>
			</div>
		    </div>
		    <div class="form-group property-map-row">
			<div class="col-sm-12">
			    <label for="address-map<?php echo$k;?>"><?php echo __('Or drag the marker to property position');?></label>
			    <div id="<?php echo$k;?>submit-map" class="submit-map"></div>
			</div>
		    </div>
                    <div class="form-group">
                        <div class="col-sm-12">
                            <?php echo $this->element('property_nearby_places', array(
                                'wrapperId'=>'PropertyEditNearbyPlaces'.$k,
                                'latId'=>$k.'latitude',
                                'lngId'=>$k.'longitude',
                                'inputName'=>'data['.(int)$k.'][Property][nearby_places_json]',
                                'currentJson'=>isset($post['Property']['nearby_places_json']) ? $post['Property']['nearby_places_json'] : '[]',
                                'propertyId'=>(int)$id,
                                'mapProvider'=>$mapProvider
                            ));?>
                        </div>
                    </div>
		    <div class="form-group property-price-field">
			<label for="group_name" class="col-sm-2 control-label"><small><?php echo __('Price');?>:</small></label>
			<div class="col-sm-4">
			   <?php echo $this->Form->input("$k.Property.price",array('label' => false,'class'=>'form-control property-price-input','placeholder'=>__('Price'),'div'=>false));?>
			</div>
			<label for="group_name" class="col-sm-2 control-label"><small><?php echo __('Contract');?>:</small></label>
                        <div class="col-sm-4">
                           <?php echo $this->Form->select("$k.Property.contract_id",$contract,array('label' => false,'class'=>'form-control','empty'=>__('Select'),'div'=>false));?>
                        </div>
                    </div>
		    <div class="form-group">
			<label for="group_name" class="col-sm-2 control-label"><small><?php echo __('Area');?>:</small></label>
			<div class="col-sm-4">
			   <?php echo $this->Form->input("$k.Property.area",array("$k.Property.area",'label' => false,'class'=>'form-control','div'=>false));?>
			</div>
			<label for="group_name" class="col-sm-2 control-label"><small><?php echo __('Unit');?>:</small></label>
			<div class="col-sm-4">
			   <?php echo $this->Form->select("$k.Property.unit_id",$unitName,array('label' => false,'class'=>'form-control','empty'=>__('Select'),'div'=>false));?>
			</div>
		    </div>
		    <div class="form-group">
			<label for="group_name" class="col-sm-2 control-label"><small><?php echo __('Number of Floors');?>:</small></label>
			<div class="col-sm-4">
			   <?php echo $this->Form->input("$k.Property.floor",array('label' => false,'class'=>'form-control','placeholder'=>__('Number of Floors'),'div'=>false));?>
			</div>
			<label for="group_name" class="col-sm-2 control-label"><small><?php echo __('Number of Bedrooms');?>:</small></label>
			<div class="col-sm-4">
			   <?php echo $this->Form->input("$k.Property.bedroom",array('label' => false,'class'=>'form-control','placeholder'=>__('Number of Bedrooms'),'div'=>false));?>
			</div>
		    </div>
		    <div class="form-group">
			<label for="group_name" class="col-sm-2 control-label"><small><?php echo __('Number of Bathrooms');?>:</small></label>
			<div class="col-sm-4">
			   <?php echo $this->Form->input("$k.Property.bathroom",array('label' => false,'class'=>'form-control','placeholder'=>__('Number of Bathrooms'),'div'=>false));?>
			</div>
			<label for="group_name" class="col-sm-2 control-label"><small><?php echo __('Add Features');?>:</small></label>
			<div class="col-sm-4">
			    <?php $gp2=array();
				foreach($post['Feature'] as $featureName):
				 $gp2[]= $featureName['id'];?>
				 <?php endforeach;unset($featureName);?>
			    <?php echo $this->Form->select("$k.PropertyFeature.feature",$featureId,array('value'=>$gp2,'multiple'=>true,'label' => false,'class'=>'form-control multiselectgrp','div'=>false, 'hidden'=>false));unset($gp2);?>
			</div>
		    </div>
		    <div class="form-group">
			<label for="group_name" class="col-sm-2 control-label"><small><?php echo __("Agent's Commission Type");?>:</small></label>
			<div class="col-sm-4">
			   <?php echo $this->Form->input("$k.Property.commission_type",array('type'=>'radio','options'=>array("Percentage"=>__("Percentage"),"Value"=>__("Value")),'legend'=>false,'before' => '<label class="radio-inline">','separator' => '</label><label class="radio-inline">','label' => false,'div'=>false,'class'=>''));?>
			</div>
			<label for="group_name" class="col-sm-2 control-label"><small><?php echo __("Agent's Commission");?>:</small></label>
			<div class="col-sm-4">
			   <?php echo $this->Form->input("$k.Property.agent_commission",array('label' => false,'class'=>'form-control','placeholder'=>__("Agent's Commission"),'div'=>false));?>
			</div>
		    </div>
		    <div class="form-group">
			<label for="group_name" class="col-sm-2 control-label"><small><?php echo __('Allow User Rating');?>:</small></label>
                        <div class="col-sm-1">
                           <?php echo $this->Form->checkbox("$k.Property.allow_rating", array('label'=>false,'class'=>'form-control','div'=>false)); ?>
                        </div>
			<label for="group_name" class="col-sm-2 control-label"><small><?php echo __('Allow User Comment');?>:</small></label>
                        <div class="col-sm-1">
                           <?php echo $this->Form->checkbox("$k.Property.allow_comment", array('label'=>false,'class'=>'form-control','div'=>false)); ?>
                        </div>
			<label for="group_name" class="col-sm-2 control-label"><small><?php echo __('Status');?>:</small></label>
                        <div class="col-sm-4">
                           <?php echo $this->Form->select("$k.Property.status",array('Available'=>__('Available'),'Sold'=>__('Sold')),array('empty'=>null,'label' => false,'class'=>'form-control','div'=>false));?>
                        </div>
		    </div> 
                    <div class="form-group property-photo-manager-row">
                        <div class="col-sm-12">
                            <div class="property-photo-upload-grid">
                                <div class="property-photo-upload-box">
                                    <label for="Property<?php echo $k;?>MainPhoto"><?php echo __('Main Front Photo');?></label>
                                    <?php echo $this->Form->input("$k.Pr.MainPhoto", array('type'=>'file','label'=>false,'div'=>false,'accept'=>'image/*','id'=>'Property'.$k.'MainPhoto'));?>
                                    <p class="help-block"><?php echo __('Upload a new front photo. It will become the main frontend photo.');?></p>
                                </div>
                                <div class="property-photo-upload-box">
                                    <label for="Property<?php echo $k;?>GalleryPhotos"><?php echo __('Gallery Images');?></label>
                                    <?php echo $this->Form->input("$k.Pr.PropertiesPhoto.", array('type'=>'file','label'=>false,'div'=>false,'multiple'=>'multiple','accept'=>'image/*','id'=>'Property'.$k.'GalleryPhotos'));?>
                                    <p class="help-block"><?php echo __('Add more images to the existing property gallery.');?></p>
                                </div>
                            </div>
                            <div class="property-gallery-title"><?php echo __('Existing Gallery / Select Front Photo');?></div>
                            <?php if(!empty($post['PropertiesPhoto'])){ ?>
                                <div class="property-gallery-list">
                                    <?php foreach((array)$post['PropertiesPhoto'] as $galleryPhoto){
                                        if(empty($galleryPhoto['photo']))continue;
                                        $galleryId = (int)$galleryPhoto['id'];
                                        $galleryDir = !empty($galleryPhoto['dir']) ? $galleryPhoto['dir'] : 'propertiesphotos';
                                        $isFrontPhoto = (!empty($galleryPhoto['home']) && $galleryPhoto['home']==='Yes');
                                    ?>
                                    <div class="property-gallery-card">
                                        <?php echo $this->Html->image($galleryDir.'_thumb/'.$galleryPhoto['photo'],array('alt'=>__('Property Photo')));?>
                                        <label class="property-gallery-choice">
                                            <input type="radio" name="data[<?php echo (int)$k;?>][PropertiesPhoto][front_photo_id]" value="<?php echo $galleryId;?>" <?php echo $isFrontPhoto ? 'checked="checked"' : '';?>>
                                            <span><?php echo $isFrontPhoto ? __('Current Front Photo') : __('Use as Front Photo');?></span>
                                        </label>
                                    </div>
                                    <?php } ?>
                                </div>
                            <?php } else { ?>
                                <div class="property-gallery-empty"><?php echo __('No gallery photos uploaded yet. Use the fields above to add front and gallery images.');?></div>
                            <?php } ?>
                        </div>
                    </div>

                    <div class="form-group" style="background: #f0fdf4 !important; border-color: #86efac !important;">
                        <label for="Property<?php echo $k;?>PaymentPlanPdf" class="col-sm-2 control-label"><small><i class="fa fa-file-pdf-o text-danger"></i> <?php echo __('Payment Plan / Brochure PDF');?>:</small></label>
                        <div class="col-sm-4">
                           <input type="file" name="payment_plan_pdf_file" accept=".pdf,application/pdf" class="form-control" id="Property<?php echo $k;?>PaymentPlanPdf">
                           <p class="help-block" style="color: #166534; font-size: 11px; margin-top: 5px;"><i class="fa fa-info-circle"></i> <?php echo __('Upload official PDF schedule for instant download & lead capture on frontend.');?></p>
                           <?php 
                           $currentPdf = !empty($post['Property']['payment_plan_pdf']) ? $post['Property']['payment_plan_pdf'] : '';
                           if (!empty($currentPdf)): ?>
                               <div style="margin-top: 8px; font-size: 12px; font-weight: 700;">
                                   <a href="<?php echo $this->Html->url('/files/payment_plans/' . $currentPdf); ?>" target="_blank" class="btn btn-xs btn-info">
                                       <i class="fa fa-download"></i> <?php echo __('View Uploaded PDF (%s)', h($currentPdf)); ?>
                                   </a>
                                   <label style="margin-left: 10px; color: #dc2626; cursor: pointer;">
                                       <input type="checkbox" name="data[<?php echo (int)$k;?>][Property][remove_payment_plan_pdf]" value="1"> <?php echo __('Remove PDF'); ?>
                                   </label>
                               </div>
                           <?php endif; ?>
                        </div>
                        <label for="Property<?php echo $k;?>AssignedAgent" class="col-sm-2 control-label"><small><i class="fa fa-user text-primary"></i> <?php echo __('Assigned Sales Agent');?>:</small></label>
                        <div class="col-sm-4">
                           <?php echo $this->Form->select("$k.Property.assigned_agent_id", !empty($agentName) ? $agentName : array(), array('value' => !empty($post['Property']['user_id']) ? $post['Property']['user_id'] : '', 'label' => false, 'class' => 'form-control', 'empty' => __('Select Sales Agent (Default / Super Admin)'), 'div' => false, 'id' => 'Property'.$k.'AssignedAgent')); ?>
                           <p class="help-block" style="color: #166534; font-size: 11px; margin-top: 5px;"><i class="fa fa-check-circle"></i> <?php echo __('Shows verified agent badge & direct WhatsApp chat on listings.'); ?></p>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="Property<?php echo $k;?>Document" class="col-sm-2 control-label"><small><?php echo __('Add Documents');?>:</small></label>
                        <div class="col-sm-4">
                           <?php echo $this->Form->input("$k.Pr.PropertiesDocument.", array('type' => 'file','label'=>false,'multiple'=>'multiple','class'=>'form-control','accept'=>'*/*','id'=>'Property'.$k.'Document')); ?>
                           <p class="help-block" style="font-size: 11px; margin-top: 5px;"><?php echo __('Upload deeds, registry, approvals or legal documents.'); ?></p>
                        </div>
                        <label for="Property<?php echo $k;?>Floorplan" class="col-sm-2 control-label"><small><?php echo __('Add Floor Plans');?>:</small></label>
                        <div class="col-sm-4">
                           <?php echo $this->Form->input("$k.Pr.PropertiesFloorplan.", array('type' => 'file','label'=>false,'multiple'=>'multiple','class'=>'form-control','accept'=>'image/*,.pdf','id'=>'Property'.$k.'Floorplan')); ?>
                           <p class="help-block" style="font-size: 11px; margin-top: 5px;"><?php echo __('Upload 2D/3D layout blueprints and floor maps.'); ?></p>
                        </div>
                    </div>
		    <div class="form-group">
			<label for="group_name" class="col-sm-2 control-label"><small><?php echo __('Embed Video');?>:</small></label>
			<div class="col-sm-10">
			   <?php echo $this->Form->input("$k.Property.video",array('label' => false,'class'=>'form-control','placeholder'=>__('Embed Video'),'div'=>false));?>
			</div>
		    </div>
            <div class="form-group">
			<label for="group_name" class="col-sm-2 control-label"><small><?php echo __('Description');?>:</small></label>
			<div class="col-sm-10">
			   <?php echo $this->Tinymce->input("$k.Property.description", array('class'=>'form-control','label' => false),array('language'=>$configLanguage,'directionality'=>$dirType),'full');?>
			</div>
		    </div> 
		    <div class="form-group text-left">
			    <div class="col-sm-offset-2 col-sm-7">
			    <?php echo $this->Form->input("$k.Property.id", array('type' => 'hidden'));?>
			    </div>
			</div>
							
						
<?php if($mapProvider==='google'){ ?>
<script type="text/javascript">
(function () {
    var lat = parseFloat($('#<?php echo$k;?>latitude').val());
    var lng = parseFloat($('#<?php echo$k;?>longitude').val());
    if (isNaN(lat) || isNaN(lng) || (Math.abs(lat) < 1 && Math.abs(lng) < 1)) {
        lat = 31.5204;
        lng = 74.3587;
    }
    var mapCenter = new google.maps.LatLng(lat, lng);
    var map = new google.maps.Map(document.getElementById('<?php echo$k;?>submit-map'), {
        zoom: 15,
        center: mapCenter,
        disableDefaultUI: false
    });
    var marker = new google.maps.Marker({
        position: mapCenter,
        map: map,
        icon: '<?php echo$this->Html->url(array('controller'=>'../img')).'/map-marker.png';?>',
        draggable: true
    });
    google.maps.event.addListener(marker, 'mouseup', function () {
        $('#<?php echo$k;?>latitude').val(this.position.lat());
        $('#<?php echo$k;?>longitude').val(this.position.lng());
        $(document).trigger('wv-property-map-location-changed', ['<?php echo$k;?>latitude','<?php echo$k;?>longitude']);
    });
    function setMapPosition(position, zoomLevel) {
        map.setCenter(position);
        if (zoomLevel) {
            map.setZoom(zoomLevel);
        }
        marker.setPosition(position);
        marker.setVisible(true);
        $('#<?php echo$k;?>latitude').val(marker.getPosition().lat());
        $('#<?php echo$k;?>longitude').val(marker.getPosition().lng());
        $(document).trigger('wv-property-map-location-changed', ['<?php echo$k;?>latitude','<?php echo$k;?>longitude']);
    }
    var input = document.getElementById('address-map<?php echo$k;?>');
    var autocomplete = new google.maps.places.Autocomplete(input);
    var geocoder = new google.maps.Geocoder();
    var addressTimer = null;
    autocomplete.bindTo('bounds', map);
    google.maps.event.addListener(autocomplete, 'place_changed', function() {
        var place = autocomplete.getPlace();
        if (!place.geometry) {
            return;
        }
        if (place.geometry.viewport) {
            map.fitBounds(place.geometry.viewport);
        } else {
            map.setCenter(place.geometry.location);
            map.setZoom(17);
        }
        setMapPosition(place.geometry.location, 17);
    });
    function geocodeTypedAddress() {
        var address = $.trim($('#address-map<?php echo$k;?>').val());
        if (address.length < 3) {
            return;
        }
        geocoder.geocode({address: address}, function(results, status) {
            if (status === 'OK' && results && results.length) {
                setMapPosition(results[0].geometry.location, 17);
            }
        });
    }
    $('#address-map<?php echo$k;?>').on('input keyup', function () {
        clearTimeout(addressTimer);
        addressTimer = setTimeout(geocodeTypedAddress, 700);
    }).on('change', geocodeTypedAddress);
    google.maps.event.trigger(map, 'resize');
})();
</script>
<?php } else { ?>
<script type="text/javascript">
(function () {
    var lat = parseFloat($('#<?php echo$k;?>latitude').val());
    var lng = parseFloat($('#<?php echo$k;?>longitude').val());
    if (isNaN(lat) || isNaN(lng) || (Math.abs(lat) < 1 && Math.abs(lng) < 1)) {
        lat = 31.5204;
        lng = 74.3587;
    }
    var map = L.map('<?php echo$k;?>submit-map').setView([lat, lng], 15);
    var fallbackLoaded = false;
    var tileLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);
    tileLayer.on('tileerror', function () {
        if (fallbackLoaded) {
            return;
        }
        fallbackLoaded = true;
        map.removeLayer(tileLayer);
        L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors &copy; CARTO'
        }).addTo(map);
    });
    var osmIcon = L.icon({
        iconUrl: '<?php echo$this->Html->url(array('controller'=>'../img')).'/map-marker.png';?>',
        iconSize: [30, 40],
        iconAnchor: [15, 40]
    });
    var marker = L.marker([lat, lng], {draggable: true, icon: osmIcon}).addTo(map);
    setTimeout(function () {
        map.invalidateSize();
    }, 200);
    setTimeout(function () {
        map.invalidateSize();
    }, 900);
    function setLatLng(newLat, newLng) {
        $('#<?php echo$k;?>latitude').val(parseFloat(newLat).toFixed(8));
        $('#<?php echo$k;?>longitude').val(parseFloat(newLng).toFixed(8));
        $(document).trigger('wv-property-map-location-changed', ['<?php echo$k;?>latitude','<?php echo$k;?>longitude']);
    }
    setLatLng(lat, lng);
    marker.on('dragend', function (e) {
        var p = e.target.getLatLng();
        setLatLng(p.lat, p.lng);
    });
    $('#<?php echo$k;?>latitude, #<?php echo$k;?>longitude').on('change', function () {
        var iLat = parseFloat($('#<?php echo$k;?>latitude').val());
        var iLng = parseFloat($('#<?php echo$k;?>longitude').val());
        if (!isNaN(iLat) && !isNaN(iLng)) {
            marker.setLatLng([iLat, iLng]);
            map.setView([iLat, iLng], map.getZoom());
        }
    });
    function geocodeAddress() {
        var address = $.trim($('#address-map<?php echo$k;?>').val());
        if (address.length < 3) {
            return;
        }
        $.getJSON('https://nominatim.openstreetmap.org/search', {
            format: 'json',
            q: address,
            limit: 1
        }).done(function (rows) {
            if (rows && rows.length > 0) {
                var rLat = parseFloat(rows[0].lat);
                var rLng = parseFloat(rows[0].lon);
                if (!isNaN(rLat) && !isNaN(rLng)) {
                    marker.setLatLng([rLat, rLng]);
                    map.setView([rLat, rLng], 16);
                    setLatLng(rLat, rLng);
                }
            }
        });
    }
    var addressTimer = null;
    $('#address-map<?php echo$k;?>').on('input keyup', function () {
        clearTimeout(addressTimer);
        addressTimer = setTimeout(geocodeAddress, 700);
    }).on('change', geocodeAddress);
    $('#address-map<?php echo$k;?>').on('keypress', function (e) {
        if (e.which === 13) {
            e.preventDefault();
            geocodeAddress();
        }
    });
})();
</script>
<?php } ?>
		</div>
		</div>
                    <?php endforeach;?>
                        <?php unset($post);?>
                        <div class="form-group text-left property-edit-actions">
                        <div class="col-sm-offset-2 col-sm-7">                            
                            <button type="submit" class="btn btn-success"><span class="fa fa-refresh"></span> <?php echo __('Update');?></button>
                            <?php if(!$isError){?><?php echo$this->Html->link('<span class="fa fa-remove"></span> '.__('Cancel'),array('controller'=>'Properties','action'=>'index'),array('class'=>'btn btn-danger','escape'=>false));?><?php }?>
                        </div>
                    </div>
                <?php echo$this->Form->end();?>
		</div>
		</div>
</div>
