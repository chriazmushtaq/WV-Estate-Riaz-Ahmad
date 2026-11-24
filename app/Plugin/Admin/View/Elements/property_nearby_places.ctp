<?php
$wrapperId = !empty($wrapperId) ? $wrapperId : 'PropertyNearbyPlaces';
$latId = !empty($latId) ? $latId : 'latitude';
$lngId = !empty($lngId) ? $lngId : 'longitude';
$inputName = !empty($inputName) ? $inputName : 'data[Property][nearby_places_json]';
$currentJson = !empty($currentJson) ? (string)$currentJson : '[]';
$mapProvider = (isset($mapProvider) && strtolower($mapProvider)==='google') ? 'google' : 'osm';
$propertyId = !empty($propertyId) ? (int)$propertyId : 0;
$saveUrl = $propertyId>0 ? $this->Html->url(array('controller'=>'Properties','action'=>'savesurroundings',$propertyId)) : '';
?>
<style type="text/css">
.wv-nearby-box{background:#f8fbff;border:1px solid #cfe0f4;border-radius:12px;padding:14px;width:100%;}
.wv-nearby-head{align-items:center;display:flex;gap:10px;justify-content:space-between;margin-bottom:10px;}
.wv-nearby-title{color:#0f2742;font-size:13px;font-weight:900;text-transform:uppercase;}
.wv-nearby-help{color:#52677f;font-size:12px;font-weight:700;margin:0 0 10px;}
.wv-nearby-actions{display:flex;flex-wrap:wrap;gap:8px;}
.wv-nearby-actions .btn{border-radius:8px!important;font-size:12px!important;padding:8px 11px!important;}
.wv-nearby-save-row{align-items:center;border-top:1px solid #dbe5ef;display:flex;flex-wrap:wrap;gap:8px;justify-content:space-between;margin-top:12px;padding-top:12px;}
.wv-nearby-save-row .wv-nearby-save-note{color:#52677f;font-size:12px;font-weight:800;}
.wv-nearby-save-row .btn{border-radius:8px!important;font-size:12px!important;font-weight:900!important;padding:9px 13px!important;}
.wv-nearby-save-status{color:#047857;font-size:12px;font-weight:900;margin-left:6px;}
.wv-nearby-save-status.error{color:#dc2626;}
.wv-nearby-grid{display:grid;gap:9px;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));}
.wv-nearby-item{align-items:flex-start;background:#fff;border:1px solid #dbe5ef;border-radius:10px;display:flex;gap:9px;padding:10px;}
.wv-nearby-item input{margin-top:4px!important;}
.wv-nearby-icon{align-items:center;background:#fff1f4;border-radius:50%;color:#ff0a46;display:flex;flex:0 0 30px;height:30px;justify-content:center;width:30px;}
.wv-nearby-name{color:#07152a;font-size:12px;font-weight:900;line-height:1.25;}
.wv-nearby-meta{color:#64748b;font-size:11px;font-weight:800;margin-top:2px;}
.wv-nearby-empty{background:#fff;border:1px dashed #a9bdd5;border-radius:9px;color:#52677f;font-size:12px;font-weight:800;padding:12px;}
</style>
<div class="wv-nearby-box" id="<?php echo h($wrapperId);?>" data-provider="<?php echo h($mapProvider);?>" data-lat-id="<?php echo h($latId);?>" data-lng-id="<?php echo h($lngId);?>" data-save-url="<?php echo h($saveUrl);?>">
    <input type="hidden" class="wv-nearby-json" name="<?php echo h($inputName);?>" value="<?php echo h($currentJson);?>">
    <div class="wv-nearby-head">
        <div class="wv-nearby-title"><i class="fa fa-map-signs"></i> <?php echo __('Surroundings');?></div>
        <div class="wv-nearby-actions">
            <button type="button" class="btn btn-info wv-nearby-refresh"><i class="fa fa-search"></i> <?php echo __('Suggest From Map');?></button>
            <button type="button" class="btn btn-success wv-nearby-select-all"><i class="fa fa-check-square-o"></i> <?php echo __('Select All');?></button>
            <button type="button" class="btn btn-warning wv-nearby-unselect-all"><i class="fa fa-square-o"></i> <?php echo __('Unselect All');?></button>
            <button type="button" class="btn btn-default wv-nearby-clear"><i class="fa fa-times"></i> <?php echo __('Clear');?></button>
        </div>
    </div>
    <p class="wv-nearby-help"><?php echo __('Search or drag the map marker. Nearby roads, schools, cafes, hospitals, parks and other useful places within 8km will be suggested and selected; unselect anything you do not want to show.');?></p>
    <div class="wv-nearby-grid"></div>
    <div class="wv-nearby-save-row">
        <div class="wv-nearby-save-note"><?php echo __('Selected surroundings will be saved with this property and displayed below the map on the public property page.');?></div>
        <button type="<?php echo $saveUrl !== '' ? 'button' : 'submit';?>" class="btn btn-primary wv-nearby-submit"><i class="fa fa-save"></i> <?php echo __('Save Selected Surroundings');?></button>
        <span class="wv-nearby-save-status"></span>
    </div>
</div>
<script type="text/javascript">
jQuery(function($){
    var root = $('#<?php echo h($wrapperId);?>');
    if(!root.length || root.data('nearbyReady')){return;}
    root.data('nearbyReady', true);
    var grid = root.find('.wv-nearby-grid');
    var hidden = root.find('.wv-nearby-json');
    var provider = String(root.data('provider') || 'osm');
    var latId = String(root.data('lat-id') || 'latitude');
    var lngId = String(root.data('lng-id') || 'longitude');
    var saveUrl = String(root.data('save-url') || '');
    var nearbyRadius = 8000;
    var nearbyLimit = 40;
    var selected = [];
    try{selected = JSON.parse(hidden.val() || '[]') || [];}catch(e){selected = [];}
    function esc(v){return $('<div>').text(v == null ? '' : String(v)).html();}
    function iconFor(category){
        category = String(category || '').toLowerCase();
        if(category.indexOf('school')>=0 || category.indexOf('college')>=0 || category.indexOf('university')>=0){return 'fa-graduation-cap';}
        if(category.indexOf('cafe')>=0 || category.indexOf('restaurant')>=0 || category.indexOf('food')>=0){return 'fa-coffee';}
        if(category.indexOf('road')>=0 || category.indexOf('street')>=0){return 'fa-road';}
        if(category.indexOf('hospital')>=0 || category.indexOf('clinic')>=0 || category.indexOf('pharmacy')>=0){return 'fa-medkit';}
        if(category.indexOf('park')>=0 || category.indexOf('garden')>=0){return 'fa-tree';}
        if(category.indexOf('bank')>=0){return 'fa-bank';}
        if(category.indexOf('market')>=0 || category.indexOf('shop')>=0 || category.indexOf('mall')>=0){return 'fa-shopping-cart';}
        if(category.indexOf('city')>=0 || category.indexOf('town')>=0 || category.indexOf('village')>=0 || category.indexOf('neighbourhood')>=0 || category.indexOf('neighborhood')>=0 || category.indexOf('suburb')>=0 || category.indexOf('residential')>=0 || category.indexOf('complex')>=0 || category.indexOf('locality')>=0){return 'fa-building-o';}
        if(category.indexOf('mosque')>=0 || category.indexOf('place')>=0){return 'fa-university';}
        if(category.indexOf('bus')>=0 || category.indexOf('transport')>=0){return 'fa-bus';}
        return 'fa-map-marker';
    }
    function cleanItem(item){
        return {
            name: String(item.name || '').substr(0,120),
            category: String(item.category || 'Nearby').substr(0,50),
            distance: Math.max(0, parseInt(item.distance || 0, 10) || 0)
        };
    }
    function formatDistance(distance){
        distance = Math.max(0, parseInt(distance || 0, 10) || 0);
        if(!distance){return '';}
        if(distance < 1000){return distance + 'm';}
        var km = distance / 1000;
        return (km >= 10 ? Math.round(km) : km.toFixed(1).replace(/\.0$/,'')) + 'km';
    }
    function keyOf(item){return String(item.name || '').toLowerCase() + '|' + String(item.category || '').toLowerCase();}
    function save(){
        var rows = [];
        grid.find('.wv-nearby-check:checked').each(function(){
            rows.push(cleanItem({
                name: $(this).data('name'),
                category: $(this).data('category'),
                distance: $(this).data('distance')
            }));
        });
        hidden.val(JSON.stringify(rows));
    }
    function render(items, autoCheckNew){
        var merged = [];
        var seen = {};
        $.each(selected.concat(items || []), function(_, item){
            item = cleanItem(item);
            if(!item.name){return;}
            var key = keyOf(item);
            if(seen[key]){return;}
            seen[key] = true;
            merged.push(item);
        });
        if(!merged.length){
            grid.html('<div class="wv-nearby-empty"><?php echo h(__('No nearby places selected yet. Click Suggest From Map after selecting a map location.'));?></div>');
            hidden.val('[]');
            return;
        }
        var selectedMap = {};
        $.each(selected, function(_, item){selectedMap[keyOf(item)] = true;});
        var html = '';
        $.each(merged, function(_, item){
            var checked = (autoCheckNew || selectedMap[keyOf(item)]) ? ' checked="checked"' : '';
            html += '<label class="wv-nearby-item">'
                + '<input type="checkbox" class="wv-nearby-check"'+checked+' data-name="'+esc(item.name)+'" data-category="'+esc(item.category)+'" data-distance="'+esc(item.distance)+'">'
                + '<span class="wv-nearby-icon"><i class="fa '+esc(iconFor(item.category))+'"></i></span>'
                + '<span><span class="wv-nearby-name">'+esc(item.name)+'</span><span class="wv-nearby-meta">'+esc(item.category)+(item.distance ? ' &middot; '+esc(formatDistance(item.distance)) : '')+'</span></span>'
                + '</label>';
        });
        grid.html(html);
        save();
    }
    function latLng(){
        var lat = parseFloat($('#'+latId).val());
        var lng = parseFloat($('#'+lngId).val());
        return (!isNaN(lat) && !isNaN(lng) && (Math.abs(lat)>1 || Math.abs(lng)>1)) ? {lat:lat,lng:lng} : null;
    }
    function distanceM(lat1,lng1,lat2,lng2){
        var R=6371000, dLat=(lat2-lat1)*Math.PI/180, dLng=(lng2-lng1)*Math.PI/180;
        var a=Math.sin(dLat/2)*Math.sin(dLat/2)+Math.cos(lat1*Math.PI/180)*Math.cos(lat2*Math.PI/180)*Math.sin(dLng/2)*Math.sin(dLng/2);
        return Math.round(R*2*Math.atan2(Math.sqrt(a),Math.sqrt(1-a)));
    }
    function osmCategory(tags){
        tags = tags || {};
        var raw = tags.place || tags.landuse || tags.residential || tags.amenity || tags.shop || tags.highway || tags.leisure || tags.tourism || tags.public_transport || tags.office || tags.building || 'Nearby';
        if(tags.place && (tags.place === 'city' || tags.place === 'town' || tags.place === 'village' || tags.place === 'suburb' || tags.place === 'neighbourhood' || tags.place === 'neighborhood' || tags.place === 'quarter' || tags.place === 'hamlet' || tags.place === 'locality')){
            raw = tags.place + ' area';
        }
        if(tags.landuse === 'residential'){
            raw = 'residential area';
        }
        raw = String(raw).replace(/_/g,' ');
        return raw.charAt(0).toUpperCase()+raw.slice(1);
    }
    function fetchOsm(pos){
        grid.html('<div class="wv-nearby-empty"><?php echo h(__('Loading nearby suggestions...'));?></div>');
        var query = '[out:json][timeout:22];('
            + 'node(around:'+nearbyRadius+','+pos.lat+','+pos.lng+')[name][amenity];'
            + 'node(around:'+nearbyRadius+','+pos.lat+','+pos.lng+')[name][shop];'
            + 'node(around:'+nearbyRadius+','+pos.lat+','+pos.lng+')[name][highway];'
            + 'node(around:'+nearbyRadius+','+pos.lat+','+pos.lng+')[name][leisure];'
            + 'node(around:'+nearbyRadius+','+pos.lat+','+pos.lng+')[name][tourism];'
            + 'node(around:'+nearbyRadius+','+pos.lat+','+pos.lng+')[name][public_transport];'
            + 'node(around:'+nearbyRadius+','+pos.lat+','+pos.lng+')[name][place~"^(city|town|village|suburb|neighbourhood|neighborhood|quarter|hamlet|locality)$"];'
            + 'way(around:'+nearbyRadius+','+pos.lat+','+pos.lng+')[name][place~"^(city|town|village|suburb|neighbourhood|neighborhood|quarter|hamlet|locality)$"];'
            + 'relation(around:'+nearbyRadius+','+pos.lat+','+pos.lng+')[name][place~"^(city|town|village|suburb|neighbourhood|neighborhood|quarter|hamlet|locality)$"];'
            + 'way(around:'+nearbyRadius+','+pos.lat+','+pos.lng+')[name][landuse=residential];'
            + 'relation(around:'+nearbyRadius+','+pos.lat+','+pos.lng+')[name][landuse=residential];'
            + 'way(around:'+nearbyRadius+','+pos.lat+','+pos.lng+')[name][building~"^(apartments|residential|commercial)$"];'
            + ');out center '+(nearbyLimit * 2)+';';
        $.ajax({url:'https://overpass-api.de/api/interpreter', method:'POST', data:{data:query}, timeout:22000}).done(function(res){
            var rows = [];
            $.each((res && res.elements) ? res.elements : [], function(_, el){
                var tags = el.tags || {};
                if(!tags.name){return;}
                var eLat = parseFloat(el.lat || (el.center && el.center.lat));
                var eLng = parseFloat(el.lon || (el.center && el.center.lon));
                if(isNaN(eLat) || isNaN(eLng)){return;}
                rows.push({name:tags.name, category:osmCategory(tags), distance:distanceM(pos.lat,pos.lng,eLat,eLng)});
            });
            rows.sort(function(a,b){return a.distance-b.distance;});
            render(rows.slice(0, nearbyLimit), true);
        }).fail(function(){render([]);});
    }
    function fetchGoogle(pos){
        if(!(window.google && google.maps && google.maps.places)){
            fetchOsm(pos);
            return;
        }
        grid.html('<div class="wv-nearby-empty"><?php echo h(__('Loading nearby suggestions...'));?></div>');
        var service = new google.maps.places.PlacesService(document.createElement('div'));
        service.nearbySearch({location:new google.maps.LatLng(pos.lat,pos.lng), radius:nearbyRadius}, function(results, status){
            if(status !== google.maps.places.PlacesServiceStatus.OK || !results){render([]);return;}
            var rows = [];
            $.each(results, function(_, place){
                if(!place.name || !place.geometry || !place.geometry.location){return;}
                var types = place.types || [];
                var preferredType = '';
                $.each(['locality','sublocality','sublocality_level_1','neighborhood','political','premise','establishment'], function(_, candidate){
                    if(!preferredType && $.inArray(candidate, types) >= 0){preferredType = candidate;}
                });
                var type = preferredType || (types.length ? String(types[0]) : 'Nearby');
                type = String(type).replace(/_/g,' ');
                if(type === 'locality' || type === 'sublocality' || type === 'sublocality level 1' || type === 'neighborhood' || type === 'political'){
                    type = 'Locality area';
                }
                rows.push({name:place.name, category:type.charAt(0).toUpperCase()+type.slice(1), distance:distanceM(pos.lat,pos.lng,place.geometry.location.lat(),place.geometry.location.lng())});
            });
            rows.sort(function(a,b){return a.distance-b.distance;});
            render(rows.slice(0, nearbyLimit), true);
        });
    }
    var autoTimer = null, lastAutoKey = '';
    function autoSuggest(){
        clearTimeout(autoTimer);
        autoTimer = setTimeout(function(){
            var pos = latLng();
            if(!pos){return;}
            var key = pos.lat.toFixed(5)+','+pos.lng.toFixed(5);
            if(key === lastAutoKey){return;}
            lastAutoKey = key;
            selected = [];
            try{selected = JSON.parse(hidden.val() || '[]') || [];}catch(e){selected=[];}
            if(provider === 'google'){fetchGoogle(pos);}else{fetchOsm(pos);}
        }, 900);
    }
    root.on('change', '.wv-nearby-check', save);
    root.find('.wv-nearby-select-all').on('click', function(){grid.find('.wv-nearby-check').prop('checked', true); save();});
    root.find('.wv-nearby-unselect-all').on('click', function(){grid.find('.wv-nearby-check').prop('checked', false); save();});
    root.find('.wv-nearby-clear').on('click', function(){selected=[];hidden.val('[]');render([]);});
    root.find('.wv-nearby-submit').on('click', function(){
        save();
        if(!saveUrl){return;}
        var status = root.find('.wv-nearby-save-status');
        status.removeClass('error').text('<?php echo h(__('Saving...'));?>');
        $.ajax({
            url: saveUrl,
            method: 'POST',
            dataType: 'json',
            data: {nearby_places_json: hidden.val()}
        }).done(function(res){
            if(res && res.success){
                status.removeClass('error').text('<?php echo h(__('Saved'));?>');
                selected = [];
                try{selected = JSON.parse(hidden.val() || '[]') || [];}catch(e){selected=[];}
                render([]);
            }else{
                status.addClass('error').text((res && res.message) ? res.message : '<?php echo h(__('Could not save'));?>');
            }
        }).fail(function(){
            status.addClass('error').text('<?php echo h(__('Could not save'));?>');
        });
    });
    root.closest('form').on('submit', function(){save();});
    root.find('.wv-nearby-refresh').on('click', function(){
        var pos = latLng();
        if(!pos){grid.html('<div class="wv-nearby-empty"><?php echo h(__('Please select a valid map location first.'));?></div>');return;}
        selected = [];
        try{selected = JSON.parse(hidden.val() || '[]') || [];}catch(e){selected=[];}
        if(provider === 'google'){fetchGoogle(pos);}else{fetchOsm(pos);}
    });
    $('#'+latId+', #'+lngId).on('change', autoSuggest);
    $(document).on('wv-property-map-location-changed', function(e, changedLatId, changedLngId){
        if(String(changedLatId) === latId && String(changedLngId) === lngId){autoSuggest();}
    });
    render([]);
});
</script>
