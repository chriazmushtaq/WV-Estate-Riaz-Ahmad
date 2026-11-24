<?php
$levelLabels = isset($levelLabels) ? (array)$levelLabels : array(
    'country' => __('Country'),
    'province' => __('Province'),
    'district' => __('District'),
    'tehsil' => __('Tehsil'),
    'city' => __('City'),
    'town' => __('Town'),
    'block' => __('Block')
);
$levelOptions = isset($levelOptions) ? (array)$levelOptions : $levelLabels;
$addressHierarchyTree = isset($addressHierarchyTree) ? (array)$addressHierarchyTree : array();
$treeJson = json_encode($addressHierarchyTree);
?>
<style type="text/css">
.wv-address-form{background:#f5f8fc;border:1px solid #dbe5f0;border-radius:12px;padding:16px;}
.wv-address-card{background:#fff;border:1px solid #dce6f2;border-radius:12px;box-shadow:0 12px 28px rgba(15,23,42,.06);padding:18px;}
.wv-address-card h3{color:#07152a;font-size:20px;font-weight:900;margin:0 0 6px;}
.wv-address-card p{color:#607086;font-size:12px;font-weight:700;margin:0 0 16px;}
.wv-address-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px;}
.wv-address-grid label{color:#07152a;display:block;font-size:11px;font-weight:900;margin:0 0 6px;text-transform:uppercase;}
.wv-address-grid .form-control{border:1px solid #cfdbea;border-radius:8px;box-shadow:none;height:42px;width:100%;}
.wv-parent-chain{background:#f8fbff;border:1px dashed #c6d6e8;border-radius:12px;margin-top:14px;padding:14px;}
.wv-parent-chain h4{color:#07152a;font-size:14px;font-weight:900;margin:0 0 12px;}
.wv-parent-field.is-hidden{display:none;}
.wv-address-actions{border-top:1px solid #edf2f7;margin-top:18px;padding-top:14px;}
.wv-required-star{color:#f30b46;font-weight:900;}
@media(max-width:900px){.wv-address-grid{grid-template-columns:1fr}}
</style>

<div class="page-title">
    <div class="title-env"><h1 class="title"><?php echo __('Add Address Level');?></h1></div>
</div>
<div class="wv-address-form">
    <?php echo $this->Session->flash();?>
    <div class="wv-address-card" id="AddressBoundForm" data-tree='<?php echo h($treeJson);?>'>
        <h3><?php echo __('Country > Province > District > Tehsil > City > Town > Block');?></h3>
        <p><?php echo __('Select the level you want to add, then choose its parent chain. Only the new level name is typed manually.');?></p>
        <?php echo $this->Form->create('AddressAdd', array('url'=>array('controller'=>'Addresshierarchies','action'=>'add','admin'=>true)));?>
        <div class="wv-address-grid">
            <div>
                <label><?php echo __('What do you want to add?');?> <span class="wv-required-star">*</span></label>
                <?php echo $this->Form->select('level_type', $levelOptions, array('empty'=>__('Select Level'), 'class'=>'form-control', 'id'=>'AddressAddLevelType', 'required'=>true));?>
            </div>
            <div>
                <label><?php echo __('New Name');?> <span class="wv-required-star">*</span></label>
                <?php echo $this->Form->text('name', array('class'=>'form-control', 'placeholder'=>__('Type new country, province, district, city, town or block'), 'required'=>true));?>
            </div>
            <div>
                <label><?php echo __('Sort');?></label>
                <?php echo $this->Form->text('sort_order', array('class'=>'form-control', 'type'=>'number', 'value'=>0));?>
            </div>
        </div>

        <div class="wv-parent-chain" id="AddressParentChain">
            <h4><?php echo __('Parent Chain');?></h4>
            <div class="wv-address-grid">
                <?php
                $parentLevels = array('country','province','district','tehsil','city','town');
                foreach($parentLevels as $level){
                ?>
                    <div class="wv-parent-field" data-level="<?php echo h($level);?>">
                        <label><?php echo h($levelLabels[$level]);?> <span class="wv-required-star">*</span></label>
                        <?php echo $this->Form->select('AddressParent.'.$level.'_id', array(), array('empty'=>__('Select '.$levelLabels[$level]), 'class'=>'form-control wv-parent-select', 'data-level'=>$level, 'id'=>'AddressParent'.ucfirst($level).'Id'));?>
                    </div>
                <?php } ?>
            </div>
        </div>

        <div class="wv-address-actions">
            <label style="margin-right:15px;"><?php echo $this->Form->checkbox('status', array('checked'=>true, 'hiddenField'=>false));?> <?php echo __('Active');?></label>
            <button type="submit" class="btn btn-success"><span class="fa fa-save"></span> <?php echo __('Save Address Level');?></button>
            <?php echo $this->Html->link(__('Back'), array('action'=>'index'), array('class'=>'btn btn-default'));?>
        </div>
        <?php echo $this->Form->end();?>
    </div>
</div>

<script type="text/javascript">
(function(){
    var root = document.getElementById('AddressBoundForm');
    if(!root){return;}
    var levels = ['country','province','district','tehsil','city','town','block'];
    var parentLevels = ['country','province','district','tehsil','city','town'];
    var labels = <?php echo json_encode($levelLabels);?>;
    var tree = {};
    try{tree = JSON.parse(root.getAttribute('data-tree') || '{}');}catch(e){tree = {};}
    var levelSelect = document.getElementById('AddressAddLevelType');
    var selects = {};

    function ucfirst(value){
        return value ? value.charAt(0).toUpperCase() + value.slice(1) : value;
    }

    function clearSelect(select, label){
        select.innerHTML = '';
        var empty = document.createElement('option');
        empty.value = '';
        empty.text = label;
        select.appendChild(empty);
    }

    function fillSelect(level, parentId){
        var select = selects[level];
        if(!select){return;}
        clearSelect(select, 'Select ' + (labels[level] || ucfirst(level)));
        var bucket = tree[level] || {};
        var rows = bucket[String(parentId || 0)] || bucket[parentId || 0] || [];
        for(var i=0;i<rows.length;i++){
            var option = document.createElement('option');
            option.value = rows[i].id;
            option.text = rows[i].name;
            select.appendChild(option);
        }
    }

    function resetAfter(level){
        var index = parentLevels.indexOf(level);
        for(var i=index+1;i<parentLevels.length;i++){
            if(selects[parentLevels[i]]){
                clearSelect(selects[parentLevels[i]], 'Select ' + (labels[parentLevels[i]] || ucfirst(parentLevels[i])));
            }
        }
    }

    function requiredParentLevels(){
        var selectedLevel = levelSelect ? levelSelect.value : '';
        var selectedIndex = levels.indexOf(selectedLevel);
        if(selectedIndex <= 0){return [];}
        return levels.slice(0, selectedIndex);
    }

    function refreshVisibility(){
        var required = requiredParentLevels();
        var requiredMap = {};
        for(var i=0;i<required.length;i++){requiredMap[required[i]] = true;}
        var fields = root.querySelectorAll('.wv-parent-field');
        for(var j=0;j<fields.length;j++){
            var level = fields[j].getAttribute('data-level');
            var visible = !!requiredMap[level];
            fields[j].className = visible ? 'wv-parent-field' : 'wv-parent-field is-hidden';
            if(selects[level]){
                selects[level].disabled = !visible;
                selects[level].required = visible;
                if(!visible){selects[level].value = '';}
            }
        }
        fillSelect('country', 0);
        chainChanged('country');
    }

    function chainChanged(level){
        resetAfter(level);
        var index = parentLevels.indexOf(level);
        for(var i=index+1;i<parentLevels.length;i++){
            var parentLevel = parentLevels[i-1];
            var parentValue = selects[parentLevel] ? selects[parentLevel].value : '';
            if(!parentValue){break;}
            fillSelect(parentLevels[i], parentValue);
        }
    }

    for(var i=0;i<parentLevels.length;i++){
        var level = parentLevels[i];
        selects[level] = root.querySelector('.wv-parent-select[data-level="' + level + '"]');
        if(selects[level]){
            selects[level].onchange = (function(lvl){
                return function(){chainChanged(lvl);};
            })(level);
        }
    }
    if(levelSelect){
        levelSelect.onchange = refreshVisibility;
        levelSelect.onkeyup = refreshVisibility;
    }
    if(document.readyState === 'loading'){
        document.addEventListener('DOMContentLoaded', refreshVisibility);
    }else{
        refreshVisibility();
    }
})();
</script>
