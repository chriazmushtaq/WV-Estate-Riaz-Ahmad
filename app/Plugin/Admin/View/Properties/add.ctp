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
.property-admin-form .form-group > label.control-label {
    grid-row: auto;
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
.property-photo-upload-row {
    background: #f8fbff !important;
    border-color: #cfe0f4 !important;
}
.property-photo-upload-row .help-block {
    color: #52677f;
    font-size: 12px;
    font-weight: 700;
    line-height: 1.35;
    margin: 6px 0 0;
}
.property-photo-upload-row input[type="file"] {
    background: #ffffff;
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
#submit-map,
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
    .property-admin-form .form-group > label.control-label,
    .property-admin-form .form-group > div[class*="col-sm-"] {
        grid-column: 1 / -1 !important;
        grid-row: auto !important;
    }
    #submit-map,
    .submit-map {
        min-height: 320px;
    }
}
</style>
<div class="col-sm-12 property-admin-form property-admin-add-form">
    <?php echo $this->Session->flash();?>
    <div class="panel panel-default">
        <div class="panel-heading"><?php echo __('Add Property');?></div>
        <div class="panel-body">
        <?php echo $this->Form->create('Property', array( 'controller' => 'Properties', 'action' => 'add','name'=>'post_req','id'=>'post_req','class'=>'form-horizontal','type' => 'file'));?>
            <?php if($userType!='AGT' && $userType!='AGY'){?>
	    <div class="form-group">
                <label for="group_name" class="col-sm-2 control-label"><small><?php echo __('Agency');?>:</small></label>
                <div class="col-sm-4">
                   <?php echo $this->Form->select('project_id',$agency,array('label' => false,'class'=>'form-control','empty'=>__('No Agency'),'div'=>false));?>
                </div>                
            </div>
	    <?php }?>
	    <div class="form-group">
                <label for="group_name" class="col-sm-2 control-label"><small><?php echo __('Title');?>:</small></label>
                <div class="col-sm-4">
                   <?php echo $this->Form->input('name',array('label' => false,'class'=>'form-control','placeholder'=>__('Title'),'div'=>false));?>
                </div>
		<label for="group_name" class="col-sm-2 control-label"><small><?php echo __('Type');?>:</small></label>
		<div class="col-sm-4">
		   <?php echo $this->Form->select('type_id',$type,array('legend'=>false,'label' => false,'empty'=>__('Select'),'div'=>false,'class'=>'form-control'));?>
		</div>
            </div>
            <div class="form-group">
                <div class="col-sm-12">
                    <?php echo $this->element('address_hierarchy_fields', array('fieldPrefix'=>'', 'current'=>isset($this->request->data['Property']) ? $this->request->data['Property'] : array(), 'wrapperId'=>'PropertyAddAddressHierarchy'));?>
                </div>
            </div>
            <div class="form-group">
                <label for="group_name" class="col-sm-2 control-label"><small><?php echo __('Location');?>:</small></label>
                <div class="col-sm-4">
                   <?php echo $this->Form->select('location_id',$location,array('label' => false,'class'=>'form-control','empty'=>__('Select'),'div'=>false));?>
                </div>
                <label for="group_name" class="col-sm-2 control-label"><small><?php echo __('Address');?>:</small></label>
                <div class="col-sm-4">
                   <?php echo $this->Form->input('address',array('id'=>'address-map','label' => false,'class'=>'form-control','placeholder'=>__('Address'),'div'=>false));?>
                </div>		
            </div>
	    <div class="form-group">
                <label for="group_name" class="col-sm-2 control-label"><small><?php echo __('Latitude');?>:</small></label>
                <div class="col-sm-2">
                   <?php echo $this->Form->input('latitude',array('id'=>'latitude','label' => false,'class'=>'form-control','placeholder'=>__('Latitude'),'div'=>false));?>
                </div>
                <label for="group_name" class="col-sm-1 control-label"><small><?php echo __('Longitude');?>:</small></label>
                <div class="col-sm-2">
                   <?php echo $this->Form->input('longitude',array('id'=>'longitude','label' => false,'class'=>'form-control','placeholder'=>__('Longitude'),'div'=>false));?>
                </div>
            </div>
	    <div class="form-group property-map-row">
		<div class="col-sm-12">
		    <label for="address-map"><?php echo __('Or drag the marker to property position');?></label>
		    <div id="submit-map"></div>
		</div>
            </div>
            <div class="form-group">
                <div class="col-sm-12">
                    <?php echo $this->element('property_nearby_places', array(
                        'wrapperId'=>'PropertyAddNearbyPlaces',
                        'latId'=>'latitude',
                        'lngId'=>'longitude',
                        'inputName'=>'data[Property][nearby_places_json]',
                        'currentJson'=>isset($this->request->data['Property']['nearby_places_json']) ? $this->request->data['Property']['nearby_places_json'] : '[]',
                        'mapProvider'=>$mapProvider
                    ));?>
                </div>
            </div>
	    <div class="form-group property-price-field">
                <label for="group_name" class="col-sm-2 control-label"><small><?php echo __('Price');?>:</small></label>
                <div class="col-sm-4">
                   <?php echo $this->Form->input('price',array('label' => false,'class'=>'form-control property-price-input','placeholder'=>__('Price'),'div'=>false));?>
                </div>
                <label for="group_name" class="col-sm-2 control-label"><small><?php echo __('Contract');?>:</small></label>
                <div class="col-sm-4">
                   <?php echo $this->Form->select('contract_id',$contract,array('label' => false,'class'=>'form-control','empty'=>__('Select'),'div'=>false));?>
                </div>
            </div>
	    <div class="form-group">
                <label for="group_name" class="col-sm-2 control-label"><small><?php echo __('Area');?>:</small></label>
                <div class="col-sm-4">
                   <?php echo $this->Form->input('area',array('label' => false,'class'=>'form-control','placeholder'=>__('Area'),'div'=>false));?>
                </div>
                <label for="group_name" class="col-sm-2 control-label"><small><?php echo __('Unit');?>:</small></label>
                <div class="col-sm-4">
                   <?php echo $this->Form->select('unit_id',$unitName,array('label' => false,'class'=>'form-control','empty'=>__('Select'),'div'=>false));?>
                </div>
            </div>
	    <div class="form-group">
                <label for="group_name" class="col-sm-2 control-label"><small><?php echo __('Number of Floors');?>:</small></label>
                <div class="col-sm-4">
                   <?php echo $this->Form->input('floor',array('label' => false,'class'=>'form-control','placeholder'=>__('Number of Floors'),'div'=>false));?>
                </div>
                <label for="group_name" class="col-sm-2 control-label"><small><?php echo __('Number of Bedrooms');?>:</small></label>
                <div class="col-sm-4">
                   <?php echo $this->Form->input('bedroom',array('label' => false,'class'=>'form-control','placeholder'=>__('Number of Bedrooms'),'div'=>false));?>
                </div>
            </div>
	    <div class="form-group">
                <label for="group_name" class="col-sm-2 control-label"><small><?php echo __('Number of Bathrooms');?>:</small></label>
                <div class="col-sm-4">
                   <?php echo $this->Form->input('bathroom',array('label' => false,'class'=>'form-control','placeholder'=>__('Number of Bathrooms'),'div'=>false));?>
                </div>
                <label for="group_name" class="col-sm-2 control-label"><small><?php echo __('Add Features');?>:</small></label>
                <div class="col-sm-4">
                   <?php echo $this->Form->select("PropertiesFeature.feature",$feature, array('label'=>false,'multiple'=>true,'class'=>'form-control multiselectgrp')); ?>
                </div>
            </div>
	    <div class="form-group">
                <label for="group_name" class="col-sm-2 control-label"><small><?php echo __("Agent's Commission Type");?>:</small></label>
                <div class="col-sm-4">
                   <?php echo $this->Form->input('commission_type',array('type'=>'radio','options'=>array("Percentage"=>__("Percentage"),"Value"=>__("Value")),'legend'=>false,'before' => '<label class="radio-inline">','separator' => '</label><label class="radio-inline">','label' => false,'div'=>false,'class'=>''));?>
                </div>
                <label for="group_name" class="col-sm-2 control-label"><small><?php echo __("Agent's Commission");?>:</small></label>
                <div class="col-sm-4">
                   <?php echo $this->Form->input('agent_commission',array('label' => false,'class'=>'form-control','placeholder'=>__("Agent's Commission"),'div'=>false));?>
                </div>
            </div>
	    <div class="form-group">
                <label for="group_name" class="col-sm-2 control-label"><small><?php echo __('Allow User Rating');?>:</small></label>
                <div class="col-sm-1">
                   <?php echo $this->Form->checkbox('allow_rating', array('label'=>false,'class'=>'form-control','div'=>false)); ?>
                </div>
                <label for="group_name" class="col-sm-2 control-label"><small><?php echo __('Allow User Comment');?>:</small></label>
                <div class="col-sm-1">
                   <?php echo $this->Form->checkbox('allow_comment', array('label'=>false,'class'=>'form-control','div'=>false)); ?>
                </div>
            </div>
            <div class="form-group property-photo-upload-row">
                <label for="PropertyMainPhoto" class="col-sm-2 control-label"><small><?php echo __('Main Front Photo');?>:</small></label>
                <div class="col-sm-4">
                   <?php echo $this->Form->input('Pr.MainPhoto', array('type' => 'file','label'=>false,'class'=>'','accept'=>'image/*','id'=>'PropertyMainPhoto')); ?>
                   <p class="help-block"><?php echo __('This photo will be used first on the frontend property card and single property gallery.');?></p>
                </div>
                <label for="PropertyGalleryPhotos" class="col-sm-2 control-label"><small><?php echo __('Gallery Images');?>:</small></label>
                <div class="col-sm-4">
                   <?php echo $this->Form->input('Pr.PropertiesPhoto.', array('type' => 'file','label'=>false,'multiple'=>'multiple','class'=>'','accept'=>'image/*','id'=>'PropertyGalleryPhotos')); ?>
                   <p class="help-block"><?php echo __('Upload multiple images for the property gallery.');?></p>
                </div>
            </div>
            <div class="form-group" style="background: #f0fdf4 !important; border-color: #86efac !important;">
                <label for="PropertyPaymentPlanPdf" class="col-sm-2 control-label"><small><i class="fa fa-file-pdf-o text-danger"></i> <?php echo __('Payment Plan / Brochure PDF');?>:</small></label>
                <div class="col-sm-4">
                   <input type="file" name="payment_plan_pdf_file" accept=".pdf,application/pdf" class="form-control" id="PropertyPaymentPlanPdf">
                   <p class="help-block" style="color: #166534; font-size: 11px; margin-top: 5px;"><i class="fa fa-info-circle"></i> <?php echo __('Upload official PDF schedule for instant download & lead capture on frontend.');?></p>
                </div>
                <label for="PropertyAssignedAgent" class="col-sm-2 control-label"><small><i class="fa fa-user text-primary"></i> <?php echo __('Assigned Sales Agent');?>:</small></label>
                <div class="col-sm-4">
                   <?php echo $this->Form->select('assigned_agent_id', !empty($agentName) ? $agentName : array(), array('label' => false, 'class' => 'form-control', 'empty' => __('Select Sales Agent (Default / Super Admin)'), 'div' => false, 'id' => 'PropertyAssignedAgent')); ?>
                   <p class="help-block" style="color: #166534; font-size: 11px; margin-top: 5px;"><i class="fa fa-check-circle"></i> <?php echo __('Shows verified agent badge & direct WhatsApp chat on listings.'); ?></p>
                </div>
            </div>
            <div class="form-group">
                <label for="PropertyDocument" class="col-sm-2 control-label"><small><?php echo __('Add Documents');?>:</small></label>
                <div class="col-sm-4">
                   <?php echo $this->Form->input('Pr.PropertiesDocument.', array('type' => 'file','label'=>false,'multiple'=>'multiple','class'=>'form-control','accept'=>'*/*','id'=>'PropertyDocument')); ?>
                   <p class="help-block" style="font-size: 11px; margin-top: 5px;"><?php echo __('Upload deeds, registry, approvals or legal documents.'); ?></p>
                </div>
                <label for="PropertyFloorplan" class="col-sm-2 control-label"><small><?php echo __('Add Floor Plans');?>:</small></label>
                <div class="col-sm-4">
                   <?php echo $this->Form->input('Pr.PropertiesFloorplan.', array('type' => 'file','label'=>false,'multiple'=>'multiple','class'=>'form-control','accept'=>'image/*,.pdf','id'=>'PropertyFloorplan')); ?>
                   <p class="help-block" style="font-size: 11px; margin-top: 5px;"><?php echo __('Upload 2D/3D layout blueprints and floor maps.'); ?></p>
                </div>
            </div>
            <div class="form-group">
                <label for="group_name" class="col-sm-2 control-label"><small><?php echo __('Embed Video');?>:</small></label>
                <div class="col-sm-10">
                   <?php echo $this->Form->input('video',array('label' => false,'class'=>'form-control','placeholder'=>__('Embed Video'),'div'=>false));?>
                </div>
            </div>
            <div class="form-group">
                <label for="group_name" class="col-sm-2 control-label"><small><?php echo __('Description');?>:</small></label>
                <div class="col-sm-10">
                   <?php echo $this->Tinymce->input('description', array('class'=>'form-control','label' => false),array('language'=>$configLanguage,'directionality'=>$dirType),'full');?>
                </div>
            </div> 
	    <div class="form-group text-left">
                <div class="col-sm-offset-2 col-sm-7">
                    <button type="submit" class="btn btn-success"><span class="fa fa-plus-circle"></span> <?php echo __('Save');?></button>
            <?php echo$this->Html->link('<span class="fa fa-close"></span> '.__('Close'),array('controller'=>'Properties','action'=>'index'),array('class'=>'btn btn-danger','escape'=>false));?>
                </div>
            </div>
        <?php echo$this->Form->end();?>
        </div>
    </div>
</div>
<?php if($mapProvider==='google'){ ?>
<script>
    var _latitude = parseFloat($('#latitude').val());
    var _longitude = parseFloat($('#longitude').val());
    if (isNaN(_latitude) || isNaN(_longitude) || (Math.abs(_latitude) < 1 && Math.abs(_longitude) < 1)) {
        _latitude = 31.5204;
        _longitude = 74.3587;
    }
    google.maps.event.addDomListener(window, 'load', function () {
        initSubmitMap(_latitude, _longitude);
    });
    function initSubmitMap(_latitude,_longitude){
        var mapCenter = new google.maps.LatLng(_latitude,_longitude);
        var mapOptions = {
            zoom: 15,
            center: mapCenter,
            disableDefaultUI: false
        };
        var mapElement = document.getElementById('submit-map');
        var map = new google.maps.Map(mapElement, mapOptions);
        var marker = new google.maps.Marker({
            position: mapCenter,
            map: map,
            icon: '<?php echo$this->Html->url(array('controller'=>'../img')).'/map-marker.png';?>',
            draggable: true
        });
        $('#submit-map').removeClass('fade-map');
        google.maps.event.addListener(marker, "mouseup", function () {
            $('#latitude').val(this.position.lat());
            $('#longitude').val(this.position.lng());
            $(document).trigger('wv-property-map-location-changed', ['latitude','longitude']);
        });
        function setMapPosition(position, zoomLevel) {
            map.setCenter(position);
            if (zoomLevel) {
                map.setZoom(zoomLevel);
            }
            marker.setPosition(position);
            marker.setVisible(true);
            $('#latitude').val(marker.getPosition().lat());
            $('#longitude').val(marker.getPosition().lng());
            $(document).trigger('wv-property-map-location-changed', ['latitude','longitude']);
        }
        var input = document.getElementById('address-map');
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
            var address = $.trim($('#address-map').val());
            if (address.length < 3) {
                return;
            }
            geocoder.geocode({address: address}, function(results, status) {
                if (status === 'OK' && results && results.length) {
                    setMapPosition(results[0].geometry.location, 17);
                }
            });
        }
        $('#address-map').on('input keyup', function () {
            clearTimeout(addressTimer);
            addressTimer = setTimeout(geocodeTypedAddress, 700);
        }).on('change', geocodeTypedAddress);
        google.maps.event.trigger(map, 'resize');
    }
</script>
<?php } else { ?>
<script>
(function () {
    var defaultLat = parseFloat($('#latitude').val());
    var defaultLng = parseFloat($('#longitude').val());
    if (isNaN(defaultLat) || isNaN(defaultLng) || (Math.abs(defaultLat) < 1 && Math.abs(defaultLng) < 1)) {
        defaultLat = 31.5204;
        defaultLng = 74.3587;
    }
    var map = L.map('submit-map').setView([defaultLat, defaultLng], 15);
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
    var marker = L.marker([defaultLat, defaultLng], {draggable: true, icon: osmIcon}).addTo(map);
    setTimeout(function () {
        map.invalidateSize();
    }, 200);
    setTimeout(function () {
        map.invalidateSize();
    }, 900);
    function setLatLng(lat, lng) {
        $('#latitude').val(parseFloat(lat).toFixed(8));
        $('#longitude').val(parseFloat(lng).toFixed(8));
        $(document).trigger('wv-property-map-location-changed', ['latitude','longitude']);
    }
    setLatLng(defaultLat, defaultLng);
    marker.on('dragend', function (e) {
        var p = e.target.getLatLng();
        setLatLng(p.lat, p.lng);
    });
    $('#latitude, #longitude').on('change', function () {
        var lat = parseFloat($('#latitude').val());
        var lng = parseFloat($('#longitude').val());
        if (!isNaN(lat) && !isNaN(lng)) {
            marker.setLatLng([lat, lng]);
            map.setView([lat, lng], map.getZoom());
        }
    });
    function geocodeAddress() {
        var address = $.trim($('#address-map').val());
        if (address.length < 3) {
            return;
        }
        $.getJSON('https://nominatim.openstreetmap.org/search', {
            format: 'json',
            q: address,
            limit: 1
        }).done(function (rows) {
            if (rows && rows.length > 0) {
                var lat = parseFloat(rows[0].lat);
                var lng = parseFloat(rows[0].lon);
                if (!isNaN(lat) && !isNaN(lng)) {
                    marker.setLatLng([lat, lng]);
                    map.setView([lat, lng], 16);
                    setLatLng(lat, lng);
                }
            }
        });
    }
    var addressTimer = null;
    $('#address-map').on('input keyup', function () {
        clearTimeout(addressTimer);
        addressTimer = setTimeout(geocodeAddress, 700);
    }).on('change', geocodeAddress);
    $('#address-map').on('keypress', function (e) {
        if (e.which === 13) {
            e.preventDefault();
            geocodeAddress();
        }
    });
})();
</script>
<?php } ?>
