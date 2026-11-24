<?php
$fieldPrefix = isset($fieldPrefix) ? (string)$fieldPrefix : '';
$current = isset($current) && is_array($current) ? $current : array();
$wrapperId = isset($wrapperId) ? preg_replace('/[^a-zA-Z0-9_]/', '', (string)$wrapperId) : ('AddressHierarchy'.mt_rand(1000,9999));
$countryOptions = isset($addressCountryOptions) && is_array($addressCountryOptions) ? $addressCountryOptions : array();
$tree = isset($addressHierarchyTree) && is_array($addressHierarchyTree) ? $addressHierarchyTree : array();
$labels = array(
    'country' => __('Country'),
    'province' => __('Province'),
    'district' => __('District'),
    'tehsil' => __('Tehsil'),
    'city' => __('City'),
    'town' => __('Town'),
    'block' => __('Block')
);
$levels = array('country','province','district','tehsil','city','town','block');
$fieldName = function($level) use ($fieldPrefix) {
    return ($fieldPrefix !== '' ? $fieldPrefix.'.' : '').'address_'.$level.'_id';
};
$fieldId = function($level) use ($wrapperId) {
    return $wrapperId.ucfirst($level);
};

// Compute server-side parent IDs so dropdowns render populated on initial load
$parentMap = array(
    'country' => 0,
    'province' => !empty($current['address_country_id']) ? (int)$current['address_country_id'] : 0,
    'district' => !empty($current['address_province_id']) ? (int)$current['address_province_id'] : 0,
    'tehsil' => !empty($current['address_district_id']) ? (int)$current['address_district_id'] : 0,
    'city' => !empty($current['address_tehsil_id']) ? (int)$current['address_tehsil_id'] : 0,
    'town' => !empty($current['address_city_id']) ? (int)$current['address_city_id'] : 0,
    'block' => !empty($current['address_town_id']) ? (int)$current['address_town_id'] : 0
);
if ($parentMap['province'] === 0 && count($countryOptions) === 1) {
    reset($countryOptions);
    $parentMap['province'] = (int)key($countryOptions);
}
?>
<div class="wv-address-hierarchy js-address-hierarchy" id="<?php echo h($wrapperId);?>" data-tree="<?php echo h(json_encode($tree), true);?>">
    <?php foreach($levels as $level){ 
        $currentVal = !empty($current['address_'.$level.'_id']) ? (int)$current['address_'.$level.'_id'] : 0;
        if($level === 'country') {
            $options = $countryOptions;
            if($currentVal === 0 && count($countryOptions) === 1) {
                reset($countryOptions);
                $currentVal = (int)key($countryOptions);
            }
        } else {
            $parentId = $parentMap[$level];
            $options = array();
            if($parentId > 0 && !empty($tree[$level][$parentId])) {
                foreach($tree[$level][$parentId] as $nodeItem) {
                    $options[$nodeItem['id']] = $nodeItem['name'];
                }
            }
        }
    ?>
        <div class="wv-address-node">
            <label><?php echo $labels[$level];?></label>
            <?php
            echo $this->Form->select($fieldName($level), $options, array(
                'empty' => __('Select').' '.$labels[$level],
                'class' => 'form-control js-address-level',
                'id' => $fieldId($level),
                'data-level' => $level,
                'data-current' => $currentVal,
                'value' => ($currentVal > 0 ? $currentVal : null)
            ));
            ?>
        </div>
    <?php } ?>
</div>
<?php if(empty($GLOBALS['WV_ADDRESS_HIERARCHY_ELEMENT_RENDERED'])){ $GLOBALS['WV_ADDRESS_HIERARCHY_ELEMENT_RENDERED'] = true; ?>
<style type="text/css">
.wv-address-hierarchy{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px 12px;margin-bottom:10px;}
.wv-address-hierarchy .wv-address-node{min-width:0;}
.wv-address-hierarchy label{color:#334155;display:block;font-size:11px;font-weight:900;letter-spacing:.03em;margin:0 0 6px;text-transform:uppercase;}
.wv-address-hierarchy .form-control{background:#fbfdff;border:1px solid #cbd8e8;border-radius:8px;box-shadow:none;color:#0f172a;font-weight:700;height:40px;width:100%!important;}
@media(max-width:991px){.wv-address-hierarchy{grid-template-columns:repeat(2,minmax(0,1fr));}}
@media(max-width:560px){.wv-address-hierarchy{grid-template-columns:1fr;}}
</style>
<script type="text/javascript">
jQuery(function($){
    var levelOrder = ['country','province','district','tehsil','city','town','block'];

    function fillSelect($select, items, selectedId){
        var label = $select.closest('.wv-address-node').find('label').text() || '';
        var html = '<option value=""><?php echo h(__('Select'));?> '+label+'</option>';
        var found = false;
        $.each(items || [], function(_, item){
            var id = parseInt(item.id, 10);
            var isSel = (parseInt(selectedId, 10) === id);
            if(isSel){ found = true; }
            html += '<option value="'+id+'"'+(isSel ? ' selected="selected"' : '')+'>'+ $('<div/>').text(item.name).html() +'</option>';
        });
        $select.html(html);
        if(found && selectedId > 0){
            $select.val(String(selectedId));
        }
    }

    function syncHierarchy($wrap, changedLevel){
        var tree = $wrap.data('tree') || {};
        var startIndex = changedLevel ? ($.inArray(changedLevel, levelOrder) + 1) : 1;

        for(var i = startIndex; i < levelOrder.length; i++){
            var level = levelOrder[i];
            var parentLevel = levelOrder[i - 1];
            var $parentSelect = $wrap.find('.js-address-level[data-level="'+parentLevel+'"]');
            var parentVal = parseInt($parentSelect.val() || 0, 10);
            var $childSelect = $wrap.find('.js-address-level[data-level="'+level+'"]');
            
            var items = (parentVal > 0 && tree[level] && tree[level][parentVal]) ? tree[level][parentVal] : [];
            var targetVal = parseInt($childSelect.attr('data-current') || $childSelect.val() || 0, 10);
            
            fillSelect($childSelect, items, targetVal);

            // If user explicitly changed a higher level, clear data-current for deeper downstream levels
            if(changedLevel){
                $childSelect.attr('data-current', '0');
            }
        }
    }

    $('.js-address-hierarchy').each(function(){
        var $wrap = $(this);
        var rawTree = $wrap.attr('data-tree') || '{}';
        try { 
            $wrap.data('tree', typeof rawTree === 'object' ? rawTree : JSON.parse(rawTree)); 
        } catch(e) { 
            $wrap.data('tree', {}); 
        }
        // Run initial synchronization without resetting saved values
        syncHierarchy($wrap, null);
    });

    $(document).on('change', '.js-address-level', function(){
        var $select = $(this);
        var $wrap = $select.closest('.js-address-hierarchy');
        $select.attr('data-current', $select.val() || '0');
        syncHierarchy($wrap, $select.data('level'));
    });
});
</script>
<?php } ?>
