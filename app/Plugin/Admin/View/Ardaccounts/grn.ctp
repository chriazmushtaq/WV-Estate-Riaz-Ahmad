<?php
$isAdr = ($userType=='ADR');
$isEdit = !empty($this->request->data['Ardgrn']['id']);
$materialOptions = (isset($constructionMaterialOptions) && is_array($constructionMaterialOptions)) ? $constructionMaterialOptions : array();
$unitOptions = (isset($constructionUnitOptions) && is_array($constructionUnitOptions)) ? $constructionUnitOptions : array();
$materialUnitMap = (isset($constructionMaterialUnitMap) && is_array($constructionMaterialUnitMap)) ? $constructionMaterialUnitMap : array();
?>
<div class="page-title"><div class="title-env"><h1 class="title"><?php echo __('Construction GRN');?></h1></div></div>
<div class="panel">
    <div class="panel-heading">
        <div class="btn-group">
            <?php echo $this->Html->link('<span class="fa fa-arrow-left"></span> '.__('Construction'),array('controller'=>'Ardaccounts','action'=>'construction',$currentProjectId),array('escape'=>false,'class'=>'btn btn-default'));?>
            <button type="button" class="btn btn-success" id="openGrnModal"><span class="fa fa-plus"></span> <?php echo $isEdit ? __('Edit GRN') : __('Add GRN');?></button>
        </div>
    </div>
    <div class="panel-body">
        <?php echo $this->Session->flash();?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <tr>
                    <th><?php echo __('GRN No');?></th>
                    <th><?php echo __('PO');?></th>
                    <th><?php echo __('Vendor');?></th>
                    <th><?php echo __('Stock Store');?></th>
                    <th><?php echo __('Material');?></th>
                    <th><?php echo __('Amount');?></th>
                    <th><?php echo __('Date');?></th>
                    <th><?php echo __('Action');?></th>
                </tr>
                <?php foreach($rows as $row): $rid=(int)$row['Ardgrn']['id']; ?>
                <tr>
                    <td><?php echo h($row['Ardgrn']['grn_no']);?></td>
                    <td><?php echo isset($poList[$row['Ardgrn']['po_id']]) ? h($poList[$row['Ardgrn']['po_id']]) : '-';?></td>
                    <td><?php echo isset($vendorList[$row['Ardgrn']['vendor_id']]) ? h($vendorList[$row['Ardgrn']['vendor_id']]) : '-';?></td>
                    <td><?php echo isset($siteList[$row['Ardgrn']['site_id']]) ? h($siteList[$row['Ardgrn']['site_id']]) : '-';?></td>
                    <td><?php echo h($row['Ardgrn']['material_type']);?></td>
                    <td><?php echo $currency.$this->Number->format($row['Ardgrn']['total_cost']);?></td>
                    <td><?php echo (!empty($row['Ardgrn']['grn_date']) && $row['Ardgrn']['grn_date'] !== '0000-00-00') ? date('d-M-Y', strtotime((string)$row['Ardgrn']['grn_date'])) : '-';?></td>
                    <td>
                        <?php echo $this->Html->link('<span class="fa fa-edit"></span>',array('controller'=>'Ardaccounts','action'=>'grn',$currentProjectId,'edit:'.$rid),array('escape'=>false,'class'=>'btn btn-xs btn-warning'));?>
                        <?php echo $this->Html->link('<span class="fa fa-trash"></span>',array('controller'=>'Ardaccounts','action'=>'deletegrn',$rid,$currentProjectId),array('escape'=>false,'class'=>'btn btn-xs btn-danger','onclick'=>"return confirm('".__('Are you sure?')."');"));?>
                    </td>
                </tr>
                <?php endforeach;?>
            </table>
        </div>
        <?php echo $this->element('pagination');?>
    </div>
</div>

<div class="modal fade" id="grnEntryModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><?php echo $isEdit ? __('Edit GRN') : __('Add GRN');?></h4>
            </div>
            <div class="modal-body">
                <?php echo $this->Form->create('Ardgrn',array('url'=>array('controller'=>'Ardaccounts','action'=>'grn',$currentProjectId),'class'=>'form-horizontal'));?>
                <?php if(!$isAdr){ echo $this->Form->hidden('project_id',array('value'=>$agencyId)); }?>
                <?php if($isEdit){echo $this->Form->hidden('id');}?>
                <div class="row">
                    <?php if($isAdr){?><div class="col-sm-3"><?php echo $this->Form->input('project_id',array('label'=>__('Agency'),'class'=>'form-control','options'=>$projectName,'empty'=>__('Please Select')));?></div><?php }?>
                    <div class="col-sm-3"><?php echo $this->Form->input('grn_no',array('label'=>__('GRN No'),'class'=>'form-control'));?></div>
                    <div class="col-sm-3"><?php echo $this->Form->input('po_id',array('label'=>__('PO'),'class'=>'form-control','options'=>$poList,'empty'=>__('Please Select')));?></div>
                    <div class="col-sm-3"><?php echo $this->Form->input('vendor_id',array('label'=>__('Vendor'),'class'=>'form-control','options'=>$vendorList,'empty'=>__('Please Select')));?></div>
                </div>
                <div class="row">
                    <div class="col-sm-3"><?php echo $this->Form->input('site_id',array('label'=>__('Stock Store'),'class'=>'form-control','options'=>$siteList,'empty'=>__('Please Select')));?></div>
                    <div class="col-sm-3"><?php echo $this->Form->select('material_type',$materialOptions,array('id'=>'GrnMaterialType','empty'=>__('Please Select'),'class'=>'form-control','label'=>__('Material'),'div'=>array('class'=>'input text required')));?></div>
                    <div class="col-sm-2"><?php echo $this->Form->select('unit_type',$unitOptions,array('id'=>'GrnUnitType','empty'=>__('Please Select'),'class'=>'form-control','label'=>__('Unit'),'div'=>array('class'=>'input text required')));?></div>
                    <div class="col-sm-2"><?php echo $this->Form->input('received_qty',array('label'=>__('Qty'),'class'=>'form-control'));?></div>
                    <div class="col-sm-2"><?php echo $this->Form->input('unit_cost',array('label'=>__('Unit Cost'),'class'=>'form-control'));?></div>
                </div>
                <div class="row">
                    <div class="col-sm-3"><?php echo $this->Form->input('total_cost',array('label'=>__('Total Cost'),'class'=>'form-control'));?></div>
                    <div class="col-sm-3"><?php echo $this->Form->input('grn_date',array('label'=>__('GRN Date'),'class'=>'form-control','type'=>'text'));?></div>
                    <div class="col-sm-6"><?php echo $this->Form->input('note',array('label'=>__('Note'),'class'=>'form-control'));?></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success"><span class="fa fa-save"></span> <?php echo ($isEdit?__('Update'):__('Save'));?></button>
                <?php if($isEdit){echo $this->Html->link('<span class="fa fa-close"></span> '.__('Cancel Edit'),array('controller'=>'Ardaccounts','action'=>'grn',$currentProjectId),array('escape'=>false,'class'=>'btn btn-danger'));}?>
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Close');?></button>
                <?php echo $this->Form->end();?>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
jQuery(function($){
    var $grnModal = $('#grnEntryModal');
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

    bindMaterialUnitLink('#GrnMaterialType', '#GrnUnitType');

    $('#openGrnModal').on('click', function(e){
        e.preventDefault();
        $grnModal.modal('show');
    });
    <?php if($isEdit){?>
    $grnModal.modal('show');
    <?php }?>
});
</script>
