<?php
$isAdr = ($userType=='ADR');
$isEdit = !empty($this->request->data['Ardreorderrule']['id']);
$materialOptions = (isset($constructionMaterialOptions) && is_array($constructionMaterialOptions)) ? $constructionMaterialOptions : array();
$unitOptions = (isset($constructionUnitOptions) && is_array($constructionUnitOptions)) ? $constructionUnitOptions : array();
$materialUnitMap = (isset($constructionMaterialUnitMap) && is_array($constructionMaterialUnitMap)) ? $constructionMaterialUnitMap : array();
?>
<div class="page-title"><div class="title-env"><h1 class="title"><?php echo __('Construction Reorder Rules');?></h1></div></div>
<div class="panel">
    <div class="panel-heading">
        <div class="btn-group">
            <?php echo $this->Html->link('<span class="fa fa-arrow-left"></span> '.__('Construction'),array('controller'=>'Ardaccounts','action'=>'construction',$currentProjectId),array('escape'=>false,'class'=>'btn btn-default'));?>
            <button type="button" class="btn btn-success" id="openReorderModal"><span class="fa fa-plus"></span> <?php echo $isEdit ? __('Edit Reorder Rule') : __('Add Reorder Rule');?></button>
        </div>
    </div>
    <div class="panel-body">
        <?php echo $this->Session->flash();?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <tr>
                    <th><?php echo __('Material');?></th>
                    <th><?php echo __('Unit');?></th>
                    <th><?php echo __('Min Qty');?></th>
                    <th><?php echo __('Reorder Qty');?></th>
                    <th><?php echo __('Vendor');?></th>
                    <th><?php echo __('Stock Store');?></th>
                    <th><?php echo __('Enabled');?></th>
                    <th><?php echo __('Action');?></th>
                </tr>
                <?php foreach($rows as $row): $rid=(int)$row['Ardreorderrule']['id']; ?>
                <tr>
                    <td><?php echo h($row['Ardreorderrule']['material_type']);?></td>
                    <td><?php echo h($row['Ardreorderrule']['unit_type']);?></td>
                    <td><?php echo h($row['Ardreorderrule']['min_qty']);?></td>
                    <td><?php echo h($row['Ardreorderrule']['reorder_qty']);?></td>
                    <td><?php echo isset($vendorList[$row['Ardreorderrule']['preferred_vendor_id']]) ? h($vendorList[$row['Ardreorderrule']['preferred_vendor_id']]) : '-';?></td>
                    <td><?php echo isset($siteList[$row['Ardreorderrule']['preferred_site_id']]) ? h($siteList[$row['Ardreorderrule']['preferred_site_id']]) : '-';?></td>
                    <td><?php echo ((int)$row['Ardreorderrule']['enabled']===1)?__('Yes'):__('No');?></td>
                    <td>
                        <?php echo $this->Html->link('<span class="fa fa-edit"></span>',array('controller'=>'Ardaccounts','action'=>'reorderrules',$currentProjectId,'edit:'.$rid),array('escape'=>false,'class'=>'btn btn-xs btn-warning'));?>
                        <?php echo $this->Html->link('<span class="fa fa-trash"></span>',array('controller'=>'Ardaccounts','action'=>'deletereorderrule',$rid,$currentProjectId),array('escape'=>false,'class'=>'btn btn-xs btn-danger','onclick'=>"return confirm('".__('Are you sure?')."');"));?>
                    </td>
                </tr>
                <?php endforeach;?>
            </table>
        </div>
        <?php echo $this->element('pagination');?>
    </div>
</div>

<div class="modal fade" id="reorderEntryModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><?php echo $isEdit ? __('Edit Reorder Rule') : __('Add Reorder Rule');?></h4>
            </div>
            <div class="modal-body">
                <?php echo $this->Form->create('Ardreorderrule',array('url'=>array('controller'=>'Ardaccounts','action'=>'reorderrules',$currentProjectId),'class'=>'form-horizontal'));?>
                <?php if(!$isAdr){ echo $this->Form->hidden('project_id',array('value'=>$agencyId)); }?>
                <?php if($isEdit){echo $this->Form->hidden('id');}?>
                <div class="row">
                    <?php if($isAdr){?><div class="col-sm-3"><?php echo $this->Form->input('project_id',array('label'=>__('Agency'),'class'=>'form-control','options'=>$projectName,'empty'=>__('Please Select')));?></div><?php }?>
                    <div class="col-sm-3"><?php echo $this->Form->select('material_type',$materialOptions,array('id'=>'ReorderMaterialType','empty'=>__('Please Select'),'class'=>'form-control','label'=>__('Material'),'div'=>array('class'=>'input text required')));?></div>
                    <div class="col-sm-2"><?php echo $this->Form->select('unit_type',$unitOptions,array('id'=>'ReorderUnitType','empty'=>__('Please Select'),'class'=>'form-control','label'=>__('Unit'),'div'=>array('class'=>'input text required')));?></div>
                    <div class="col-sm-2"><?php echo $this->Form->input('min_qty',array('label'=>__('Min Qty'),'class'=>'form-control'));?></div>
                    <div class="col-sm-2"><?php echo $this->Form->input('reorder_qty',array('label'=>__('Reorder Qty'),'class'=>'form-control'));?></div>
                </div>
                <div class="row">
                    <div class="col-sm-4"><?php echo $this->Form->input('preferred_vendor_id',array('label'=>__('Preferred Vendor'),'class'=>'form-control','options'=>$vendorList,'empty'=>__('Please Select')));?></div>
                    <div class="col-sm-4"><?php echo $this->Form->input('preferred_site_id',array('label'=>__('Preferred Stock Store'),'class'=>'form-control','options'=>$siteList,'empty'=>__('Please Select')));?></div>
                    <div class="col-sm-4" style="padding-top:26px;"><?php echo $this->Form->input('enabled',array('label'=>__('Enabled'),'type'=>'checkbox'));?></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success"><span class="fa fa-save"></span> <?php echo ($isEdit?__('Update'):__('Save'));?></button>
                <?php if($isEdit){echo $this->Html->link('<span class="fa fa-close"></span> '.__('Cancel Edit'),array('controller'=>'Ardaccounts','action'=>'reorderrules',$currentProjectId),array('escape'=>false,'class'=>'btn btn-danger'));}?>
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Close');?></button>
                <?php echo $this->Form->end();?>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
jQuery(function($){
    var $reorderModal = $('#reorderEntryModal');
    var materialUnitMap = <?php echo json_encode($materialUnitMap);?>;

    function normalizeText(value)
    {
        return $.trim((value || '').toString());
    }

    function bindMaterialUnitLink(materialSelector, unitSelector)
    {
        var $material = $(materialSelector);
        var $unit = $(unitSelector);
        if(!$material.length || !$unit.length)
        {
            return;
        }
        var allUnitOptions = [];
        $unit.find('option').each(function(){
            allUnitOptions.push({
                value: normalizeText($(this).attr('value')),
                text: normalizeText($(this).text())
            });
        });

        function applyUnitFilter()
        {
            var selectedMaterial = normalizeText($material.val());
            var allowed = [];
            if(selectedMaterial !== '' && materialUnitMap && $.isArray(materialUnitMap[selectedMaterial]))
            {
                allowed = materialUnitMap[selectedMaterial];
            }
            var allowedLookup = {};
            $.each(allowed, function(_, unitName){
                var key = normalizeText(unitName);
                if(key !== '')
                {
                    allowedLookup[key] = true;
                }
            });
            var currentValue = normalizeText($unit.val());
            var nextOptions = [];
            if(allowed.length > 0)
            {
                $.each(allUnitOptions, function(_, optionRow){
                    if(optionRow.value === '' || allowedLookup[optionRow.value] || allowedLookup[optionRow.text])
                    {
                        nextOptions.push(optionRow);
                    }
                });
            }
            if(nextOptions.length === 0)
            {
                nextOptions = allUnitOptions.slice(0);
            }
            $unit.empty();
            $.each(nextOptions, function(_, optionRow){
                $unit.append($('<option></option>').attr('value', optionRow.value).text(optionRow.text));
            });
            var valueRestored = false;
            if(currentValue !== '')
            {
                $unit.find('option').each(function(){
                    if(normalizeText($(this).val()) === currentValue)
                    {
                        $unit.val($(this).val());
                        valueRestored = true;
                        return false;
                    }
                });
            }
            if(!valueRestored && allowed.length === 1)
            {
                var onlyUnit = normalizeText(allowed[0]);
                $unit.find('option').each(function(){
                    if(normalizeText($(this).val()) === onlyUnit || normalizeText($(this).text()) === onlyUnit)
                    {
                        $unit.val($(this).val());
                        valueRestored = true;
                        return false;
                    }
                });
            }
            if(!valueRestored)
            {
                $unit.val('');
            }
        }

        $material.on('change', applyUnitFilter);
        applyUnitFilter();
    }

    bindMaterialUnitLink('#ReorderMaterialType', '#ReorderUnitType');

    $('#openReorderModal').on('click', function(e){
        e.preventDefault();
        $reorderModal.modal('show');
    });
    <?php if($isEdit){?>
    $reorderModal.modal('show');
    <?php }?>
});
</script>
