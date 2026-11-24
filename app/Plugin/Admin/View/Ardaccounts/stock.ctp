<?php
$this->Js->JqueryEngine->jQueryObject = 'jQuery';
$this->Paginator->options(array('update' => '#resultDiv','evalScripts' => true));
$isEdit = !empty($this->request->data['Ardstock']['id']);
$materialOptions = (isset($constructionMaterialOptions) && is_array($constructionMaterialOptions)) ? $constructionMaterialOptions : array();
$unitOptions = (isset($constructionUnitOptions) && is_array($constructionUnitOptions)) ? $constructionUnitOptions : array();
$materialUnitMap = (isset($constructionMaterialUnitMap) && is_array($constructionMaterialUnitMap)) ? $constructionMaterialUnitMap : array();
$openNew = !empty($this->request->query['new']);
$defaultMovement = isset($this->request->query['movement']) ? strtolower(trim((string)$this->request->query['movement'])) : 'in';
if(!in_array($defaultMovement, array('in', 'out')))
{
    $defaultMovement = 'in';
}
?>
<div id="resultDiv">
<div class="page-title"><div class="title-env"><h1 class="title"><?php echo __('Construction Stock');?></h1></div></div>
<div class="panel"><?php echo $this->Session->flash();?>
    <div class="panel-heading">
        <div class="btn-group">
            <?php echo $this->Html->link('<span class="fa fa-home"></span>&nbsp;'.__('ARD Dashboard'),array('controller'=>'Ardaccounts','action'=>'dashboard',$currentProjectId),array('escape'=>false,'class'=>'btn btn-default'));?>
            <?php echo $this->Html->link('<span class="fa fa-map-marker"></span>&nbsp;'.__('Stock Stores'),array('controller'=>'Ardaccounts','action'=>'stockstore',$currentProjectId),array('escape'=>false,'class'=>'btn btn-info'));?>
            <button type="button" class="btn btn-success" id="openStockInModal"><span class="fa fa-plus"></span> <?php echo $isEdit ? __('Edit Stock') : __('Add Stock In');?></button>
            <?php echo $this->Html->link('<span class="fa fa-arrow-circle-down"></span> '.__('Stock Out'),array('controller'=>'Ardaccounts','action'=>'stockout',$currentProjectId),array('escape'=>false,'class'=>'btn btn-danger'));?>
        </div>
    </div>
    <div class="panel-body">
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <tr>
                    <th>#</th>
                    <th><?php echo __('Date');?></th>
                    <th><?php echo __('Movement');?></th>
                    <th><?php echo __('Vendor');?></th>
                    <th><?php echo __('Stock Store');?></th>
                    <th><?php echo __('Material');?></th>
                    <th><?php echo __('Qty');?></th>
                    <th><?php echo __('Cost');?></th>
                    <th><?php echo __('Total');?></th>
                    <th><?php echo __('Action');?></th>
                </tr>
                <?php
                $pageParams = $this->Paginator->params();
                $limit = $pageParams['limit'];
                $page = $pageParams['page'];
                $serialNo = 1*$limit*($page-1)+1;
                foreach($rows as $row):
                    $id=(int)$row['Ardstock']['id'];
                ?>
                <tr>
                    <td><?php echo $serialNo++;?></td>
                    <td><?php echo (!empty($row['Ardstock']['movement_date']) && $row['Ardstock']['movement_date'] !== '0000-00-00') ? date('d-M-Y', strtotime((string)$row['Ardstock']['movement_date'])) : '-';?></td>
                    <td><?php echo h(strtoupper($row['Ardstock']['movement_type']));?></td>
                    <td><?php echo h($row['Ardvendor']['vendor_name']);?></td>
                    <td><?php echo h($row['Ardsite']['site_name']);?></td>
                    <td><?php echo h($row['Ardstock']['material_type']);?></td>
                    <td><?php echo number_format((float)$row['Ardstock']['quantity'], 3, '.', ',') . ' ' . h($row['Ardstock']['unit_type']);?></td>
                    <td><?php echo $currency.$this->Number->format($row['Ardstock']['unit_cost']);?></td>
                    <td><?php echo $currency.$this->Number->format($row['Ardstock']['total_cost']);?></td>
                    <td>
                        <?php
                        echo $this->Html->link('<span class="fa fa-edit"></span> '.__('Edit'),array('controller'=>'Ardaccounts','action'=>'stock',$currentProjectId,'edit:'.$id),array('escape'=>false,'class'=>'btn btn-xs btn-warning'));
                        echo ' ';
                        echo $this->Html->link('<span class="fa fa-trash"></span> '.__('Delete'),array('controller'=>'Ardaccounts','action'=>'deletestock',$id,$currentProjectId),array('escape'=>false,'class'=>'btn btn-xs btn-danger','onclick'=>"return confirm('".__('Are you sure?')."');"));
                        ?>
                    </td>
                </tr>
                <?php endforeach;?>
            </table>
        </div>
        <?php echo $this->element('pagination');?>
    </div>
</div>

<div class="modal fade" id="stockEntryModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><?php echo $isEdit ? __('Edit Stock Entry') : __('Add Stock Entry');?></h4>
            </div>
            <div class="modal-body">
                <?php echo $this->Form->create('Ardstock',array('url'=>array('controller'=>'Ardaccounts','action'=>'stock',$currentProjectId),'class'=>'form-horizontal'));?>
                <?php if($userType!='ADR'){echo $this->Form->hidden('project_id',array('value'=>$agencyId));}?>
                <?php if($isEdit){echo $this->Form->hidden('id');}?>
                <?php if($userType=='ADR'){?>
                <div class="form-group">
                    <label class="col-sm-2 control-label"><?php echo __('Agency');?></label>
                    <div class="col-sm-4"><?php echo $this->Form->select('project_id',$projectName,array('empty'=>__('Please Select'),'class'=>'form-control','label'=>false,'div'=>false));?></div>
                    <label class="col-sm-2 control-label"><?php echo __('Date');?></label>
                    <div class="col-sm-4"><?php echo $this->Form->input('movement_date',array('type'=>'text','default'=>$date,'label'=>false,'div'=>false,'class'=>'form-control','placeholder'=>__('YYYY-MM-DD')));?></div>
                </div>
                <?php } else { ?>
                <div class="form-group">
                    <label class="col-sm-2 control-label"><?php echo __('Date');?></label>
                    <div class="col-sm-4"><?php echo $this->Form->input('movement_date',array('type'=>'text','default'=>$date,'label'=>false,'div'=>false,'class'=>'form-control','placeholder'=>__('YYYY-MM-DD')));?></div>
                </div>
                <?php } ?>
                <div class="form-group">
                    <label class="col-sm-2 control-label"><?php echo __('Movement');?></label>
                    <div class="col-sm-4"><?php echo $this->Form->select('movement_type',array('in'=>__('Stock In'),'out'=>__('Stock Out')),array('empty'=>false,'class'=>'form-control','label'=>false,'div'=>false,'id'=>'StockMovementType'));?></div>
                    <label class="col-sm-2 control-label"><?php echo __('Vendor');?></label>
                    <div class="col-sm-4"><?php echo $this->Form->select('vendor_id',$vendorList,array('empty'=>__('Select Vendor'),'class'=>'form-control','label'=>false,'div'=>false));?></div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label"><?php echo __('Stock Store');?></label>
                    <div class="col-sm-4"><?php echo $this->Form->select('site_id',$siteList,array('empty'=>__('Select Stock Store'),'class'=>'form-control','label'=>false,'div'=>false));?></div>
                    <label class="col-sm-2 control-label"><?php echo __('Material');?></label>
                    <div class="col-sm-4"><?php echo $this->Form->select('material_type',$materialOptions,array('id'=>'StockMaterialType','empty'=>__('Please Select'),'class'=>'form-control','label'=>false,'div'=>false));?></div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label"><?php echo __('Unit');?></label>
                    <div class="col-sm-4"><?php echo $this->Form->select('unit_type',$unitOptions,array('id'=>'StockUnitType','empty'=>__('Please Select'),'class'=>'form-control','label'=>false,'div'=>false));?></div>
                    <label class="col-sm-2 control-label"><?php echo __('Quantity');?></label>
                    <div class="col-sm-4"><?php echo $this->Form->input('quantity',array('label'=>false,'div'=>false,'class'=>'form-control','placeholder'=>__('Quantity')));?></div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label"><?php echo __('Unit Cost');?></label>
                    <div class="col-sm-4"><?php echo $this->Form->input('unit_cost',array('label'=>false,'div'=>false,'class'=>'form-control','placeholder'=>__('Unit Cost')));?></div>
                    <label class="col-sm-2 control-label"><?php echo __('Notes');?></label>
                    <div class="col-sm-4"><?php echo $this->Form->input('notes',array('label'=>false,'div'=>false,'class'=>'form-control','placeholder'=>__('Notes')));?></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success"><span class="fa fa-save"></span> <?php echo ($isEdit?__('Update Stock'):__('Save Stock'));?></button>
                <?php if($isEdit){echo $this->Html->link('<span class="fa fa-close"></span> '.__('Cancel Edit'),array('controller'=>'Ardaccounts','action'=>'stock',$currentProjectId),array('escape'=>false,'class'=>'btn btn-danger'));}?>
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Close');?></button>
                <?php echo $this->Form->end();?>
            </div>
        </div>
    </div>
</div>
</div>

<script type="text/javascript">
jQuery(function($){
    var $stockModal = $('#stockEntryModal');
    var materialUnitMap = <?php echo json_encode($materialUnitMap);?>;
    var defaultMovement = <?php echo json_encode($defaultMovement);?>;

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

    bindMaterialUnitLink('#StockMaterialType', '#StockUnitType');

    $('#openStockInModal').on('click', function(e){
        e.preventDefault();
        $('#StockMovementType').val('in');
        $stockModal.modal('show');
    });
    <?php if($isEdit){?>
    $stockModal.modal('show');
    <?php } elseif($openNew){?>
    $('#StockMovementType').val(defaultMovement);
    $stockModal.modal('show');
    <?php }?>
});
</script>
