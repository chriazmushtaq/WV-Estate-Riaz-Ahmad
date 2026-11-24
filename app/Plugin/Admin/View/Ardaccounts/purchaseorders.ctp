<?php
$isAdr = ($userType == 'ADR');
$editFormId = !empty($this->request->data['Ardpurchaseorder']['id'])
    ? (int)$this->request->data['Ardpurchaseorder']['id']
    : (!empty($this->request->query['edit']) ? (int)$this->request->query['edit'] : 0);
$isEdit = ($editFormId > 0);
$materialOptions = (isset($constructionMaterialOptions) && is_array($constructionMaterialOptions)) ? $constructionMaterialOptions : array();
$unitOptions = (isset($constructionUnitOptions) && is_array($constructionUnitOptions)) ? $constructionUnitOptions : array();
$materialUnitMap = (isset($constructionMaterialUnitMap) && is_array($constructionMaterialUnitMap)) ? $constructionMaterialUnitMap : array();
$purchaseOrderLineRows = (isset($purchaseOrderLineRows) && is_array($purchaseOrderLineRows)) ? $purchaseOrderLineRows : array();
$nextPoNoMap = (isset($nextPoNoMap) && is_array($nextPoNoMap)) ? $nextPoNoMap : array();
$poFormUrl = array('controller' => 'Ardaccounts', 'action' => 'purchaseorders', $currentProjectId);
if($isEdit)
{
    $poFormUrl['?'] = array('edit' => $editFormId);
}
?>
<div class="page-title">
    <div class="title-env">
        <h1 class="title"><?php echo __('Construction Purchase Orders'); ?></h1>
    </div>
</div>
<div class="panel">
    <div class="panel-heading">
        <div class="btn-group">
            <?php echo $this->Html->link('<span class="fa fa-arrow-left"></span> ' . __('Construction'), array('controller' => 'Ardaccounts', 'action' => 'construction', $currentProjectId), array('escape' => false, 'class' => 'btn btn-default')); ?>
            <button type="button" class="btn btn-success" id="openPoModal">
                <span class="fa fa-plus"></span> <?php echo $isEdit ? __('Edit Purchase Order') : __('Add Purchase Order'); ?>
            </button>
        </div>
    </div>
    <div class="panel-body">
        <?php echo $this->Session->flash(); ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <tr>
                    <th><?php echo __('PO No'); ?></th>
                    <th><?php echo __('Vendor'); ?></th>
                    <th><?php echo __('Stock Store'); ?></th>
                    <th><?php echo __('Materials'); ?></th>
                    <th><?php echo __('Qty / Lines'); ?></th>
                    <th><?php echo __('Amount'); ?></th>
                    <th><?php echo __('Date'); ?></th>
                    <th><?php echo __('Status'); ?></th>
                    <th><?php echo __('Action'); ?></th>
                </tr>
                <?php foreach($rows as $row): $rid = (int)$row['Ardpurchaseorder']['id']; ?>
                <?php
                    $summary = isset($poItemSummary[$rid]) ? $poItemSummary[$rid] : array();
                    $lineCount = isset($summary['count']) ? (int)$summary['count'] : 1;
                    $firstLabel = isset($summary['first_label']) ? trim((string)$summary['first_label']) : trim((string)$row['Ardpurchaseorder']['material_type'] . ' ' . $row['Ardpurchaseorder']['unit_type']);
                    if($firstLabel === '')
                    {
                        $firstLabel = '-';
                    }
                    $displayMaterial = $firstLabel;
                    if($lineCount > 1)
                    {
                        $displayMaterial .= ' + ' . ($lineCount - 1) . ' ' . __('more');
                    }
                    $displayQty = ($lineCount > 1)
                        ? ($lineCount . ' ' . __('line items'))
                        : ($this->Number->format((float)$row['Ardpurchaseorder']['quantity'], array('places' => 3)) . ' ' . h($row['Ardpurchaseorder']['unit_type']));
                    $displayAmount = isset($summary['total_cost']) ? (float)$summary['total_cost'] : (float)$row['Ardpurchaseorder']['total_cost'];
                ?>
                <tr>
                    <td><?php echo h($row['Ardpurchaseorder']['po_no']); ?></td>
                    <td><?php echo isset($vendorList[$row['Ardpurchaseorder']['vendor_id']]) ? h($vendorList[$row['Ardpurchaseorder']['vendor_id']]) : '-'; ?></td>
                    <td><?php echo isset($siteList[$row['Ardpurchaseorder']['site_id']]) ? h($siteList[$row['Ardpurchaseorder']['site_id']]) : '-'; ?></td>
                    <td><?php echo h($displayMaterial); ?></td>
                    <td><?php echo h($displayQty); ?></td>
                    <td><?php echo $currency . $this->Number->format($displayAmount); ?></td>
                    <td><?php echo (!empty($row['Ardpurchaseorder']['po_date']) && $row['Ardpurchaseorder']['po_date'] !== '0000-00-00') ? date('d-M-Y', strtotime((string)$row['Ardpurchaseorder']['po_date'])) : '-'; ?></td>
                    <td><?php echo h(ucfirst($row['Ardpurchaseorder']['status'])); ?></td>
                    <td>
                        <a href="javascript:void(0);" class="btn btn-xs btn-info js-view-po"
                           data-po-id="<?php echo (int)$rid;?>"
                           data-po-no="<?php echo h((string)$row['Ardpurchaseorder']['po_no'], true);?>"
                           data-vendor="<?php echo isset($vendorList[$row['Ardpurchaseorder']['vendor_id']]) ? h((string)$vendorList[$row['Ardpurchaseorder']['vendor_id']], true) : '-';?>"
                           data-store="<?php echo isset($siteList[$row['Ardpurchaseorder']['site_id']]) ? h((string)$siteList[$row['Ardpurchaseorder']['site_id']], true) : '-';?>"
                           data-material="<?php echo h((string)$displayMaterial, true);?>"
                           data-qty="<?php echo h((string)$displayQty, true);?>"
                           data-amount="<?php echo h((string)($currency . $this->Number->format($displayAmount)), true);?>"
                           data-date="<?php echo (!empty($row['Ardpurchaseorder']['po_date']) && $row['Ardpurchaseorder']['po_date'] !== '0000-00-00') ? h((string)date('d-M-Y', strtotime((string)$row['Ardpurchaseorder']['po_date'])), true) : '-';?>"
                           data-status="<?php echo h((string)ucfirst($row['Ardpurchaseorder']['status']), true);?>"
                           data-note="<?php echo h((string)$row['Ardpurchaseorder']['note'], true);?>">
                            <span class="fa fa-eye"></span>
                        </a>
                        <?php echo $this->Html->link('<span class="fa fa-edit"></span>', array('controller' => 'Ardaccounts', 'action' => 'purchaseorders', $currentProjectId, '?' => array('edit' => $rid)), array('escape' => false, 'class' => 'btn btn-xs btn-warning')); ?>
                        <?php echo $this->Html->link('<span class="fa fa-trash"></span>', array('controller' => 'Ardaccounts', 'action' => 'deletepurchaseorder', $rid, $currentProjectId), array('escape' => false, 'class' => 'btn btn-xs btn-danger', 'onclick' => "return confirm('" . __('Are you sure?') . "');")); ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
        <?php echo $this->element('pagination'); ?>
    </div>
</div>

<div class="modal fade" id="poEntryModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><?php echo $isEdit ? __('Edit Purchase Order') : __('Add Purchase Order'); ?></h4>
            </div>
            <div class="modal-body">
                <?php echo $this->Form->create('Ardpurchaseorder', array('url' => $poFormUrl, 'class' => 'form-horizontal')); ?>
                <?php if(!$isAdr){ echo $this->Form->hidden('project_id', array('value' => $agencyId)); } ?>
                <?php if($isEdit){ echo $this->Form->hidden('id', array('value' => $editFormId)); } ?>
                <div class="row">
                    <?php if($isAdr){ ?>
                    <div class="col-sm-3"><?php echo $this->Form->input('project_id', array('label' => __('Agency'), 'class' => 'form-control', 'options' => $projectName, 'empty' => __('Please Select'), 'id' => 'PoProjectId')); ?></div>
                    <?php } ?>
                    <div class="col-sm-3">
                        <?php echo $this->Form->input('po_no', array('label' => __('PO No'), 'class' => 'form-control', 'id' => 'PoNoInput')); ?>
                        <p class="help-block" style="margin-bottom:0;"><?php echo __('Next Auto No:'); ?> <strong id="PoNextNoPreview"><?php echo h($nextPoNo); ?></strong></p>
                    </div>
                    <div class="col-sm-3"><?php echo $this->Form->input('vendor_id', array('label' => __('Vendor'), 'class' => 'form-control', 'options' => $vendorList, 'empty' => __('Please Select'))); ?></div>
                    <div class="col-sm-3"><?php echo $this->Form->input('site_id', array('label' => __('Stock Store'), 'class' => 'form-control', 'options' => $siteList, 'empty' => __('Please Select'))); ?></div>
                </div>

                <hr style="margin-top:10px;margin-bottom:10px;">
                <h5 style="margin-top:0;"><?php echo __('Material Lines'); ?></h5>
                <div class="table-responsive">
                    <table class="table table-bordered" id="poLinesTable" style="margin-bottom:8px;">
                        <thead>
                            <tr>
                                <th style="width:28%;"><?php echo __('Material'); ?></th>
                                <th style="width:18%;"><?php echo __('Unit'); ?></th>
                                <th style="width:12%;"><?php echo __('Qty'); ?></th>
                                <th style="width:16%;"><?php echo __('Unit Cost'); ?></th>
                                <th style="width:18%;"><?php echo __('Line Total'); ?></th>
                                <th style="width:8%;"><?php echo __('Remove'); ?></th>
                            </tr>
                        </thead>
                        <tbody id="poLinesBody">
                            <tr class="po-main-line">
                                <td>
                                    <?php echo $this->Form->select('material_type', $materialOptions, array('id' => 'PoMaterialType', 'empty' => __('Please Select'), 'class' => 'form-control js-material')); ?>
                                </td>
                                <td>
                                    <?php echo $this->Form->select('unit_type', $unitOptions, array('id' => 'PoUnitType', 'empty' => __('Please Select'), 'class' => 'form-control js-unit')); ?>
                                </td>
                                <td>
                                    <?php echo $this->Form->input('quantity', array('label' => false, 'div' => false, 'class' => 'form-control js-qty', 'placeholder' => __('Qty'))); ?>
                                </td>
                                <td>
                                    <?php echo $this->Form->input('unit_cost', array('label' => false, 'div' => false, 'class' => 'form-control js-unit-cost', 'placeholder' => __('Unit Cost'))); ?>
                                </td>
                                <td>
                                    <?php echo $this->Form->input('total_cost', array('label' => false, 'div' => false, 'class' => 'form-control js-line-total', 'placeholder' => __('Line Total'))); ?>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-xs btn-danger js-remove-main-line" title="<?php echo h(__('Remove Material')); ?>">
                                        <span class="fa fa-trash"></span>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <button type="button" class="btn btn-info btn-sm" id="addPoLineBtn"><span class="fa fa-plus"></span> <?php echo __('Add More Material'); ?></button>
                <div class="text-right" style="margin-top:8px;">
                    <strong><?php echo __('PO Grand Total:'); ?> <span id="poGrandTotal"><?php echo $currency; ?>0.00</span></strong>
                </div>

                <div class="row" style="margin-top:10px;">
                    <div class="col-sm-3"><?php echo $this->Form->input('po_date', array('label' => __('PO Date'), 'class' => 'form-control js-po-date-field', 'type' => 'text', 'id' => 'PoDateInput', 'autocomplete' => 'off', 'placeholder' => 'YYYY-MM-DD')); ?></div>
                    <div class="col-sm-3"><?php echo $this->Form->input('expected_date', array('label' => __('Expected Date'), 'class' => 'form-control js-po-date-field', 'type' => 'text', 'id' => 'PoExpectedDateInput', 'autocomplete' => 'off', 'placeholder' => 'YYYY-MM-DD')); ?></div>
                    <div class="col-sm-3"><?php echo $this->Form->input('status', array('label' => __('Status'), 'class' => 'form-control', 'options' => array('draft' => __('Draft'), 'open' => __('Open'), 'closed' => __('Closed'), 'cancelled' => __('Cancelled')))); ?></div>
                    <div class="col-sm-3" style="padding-top:26px;"><?php echo $this->Form->input('auto_generated', array('label' => __('Auto Generated'), 'type' => 'checkbox')); ?></div>
                </div>
                <div class="row">
                    <div class="col-sm-12"><?php echo $this->Form->input('note', array('label' => __('Note'), 'class' => 'form-control')); ?></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success"><span class="fa fa-save"></span> <?php echo ($isEdit ? __('Update') : __('Save')); ?></button>
                <?php if($isEdit){ echo $this->Html->link('<span class="fa fa-close"></span> ' . __('Cancel Edit'), array('controller' => 'Ardaccounts', 'action' => 'purchaseorders', $currentProjectId), array('escape' => false, 'class' => 'btn btn-danger')); } ?>
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Close'); ?></button>
                <?php echo $this->Form->end(); ?>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="poViewModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><?php echo __('Purchase Order Details');?></h4>
            </div>
            <div class="modal-body">
                <table class="table table-bordered" style="margin-bottom:0;">
                    <tr><th style="width:35%;"><?php echo __('PO No');?></th><td id="PoViewPoNo">-</td></tr>
                    <tr><th><?php echo __('Vendor');?></th><td id="PoViewVendor">-</td></tr>
                    <tr><th><?php echo __('Stock Store');?></th><td id="PoViewStore">-</td></tr>
                    <tr><th><?php echo __('Material');?></th><td id="PoViewMaterial">-</td></tr>
                    <tr><th><?php echo __('Qty / Lines');?></th><td id="PoViewQty">-</td></tr>
                    <tr><th><?php echo __('Amount');?></th><td id="PoViewAmount">-</td></tr>
                    <tr><th><?php echo __('Date');?></th><td id="PoViewDate">-</td></tr>
                    <tr><th><?php echo __('Status');?></th><td id="PoViewStatus">-</td></tr>
                    <tr><th><?php echo __('Note');?></th><td id="PoViewNote">-</td></tr>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Close');?></button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
jQuery(function($){
    var $poModal = $('#poEntryModal');
    var $poViewModal = $('#poViewModal');
    var openViewId = <?php echo !empty($this->request->query['view']) ? (int)$this->request->query['view'] : 0;?>;
    var materialUnitMap = <?php echo json_encode($materialUnitMap); ?>;
    var materialOptions = <?php echo json_encode($materialOptions); ?>;
    var unitOptions = <?php echo json_encode($unitOptions); ?>;
    var existingLineRows = <?php echo json_encode($purchaseOrderLineRows); ?>;
    var nextPoNoMap = <?php echo json_encode($nextPoNoMap); ?>;
    var isEdit = <?php echo $isEdit ? 'true' : 'false'; ?>;
    var currencyPrefix = <?php echo json_encode((string)$currency); ?>;
    var fallbackPoView = <?php echo json_encode(isset($poViewRow) ? $poViewRow : array()); ?>;

    function normalizeText(value)
    {
        return $.trim((value || '').toString());
    }
    function nvl(value)
    {
        var text = normalizeText(value);
        return text === '' ? '-' : text;
    }

    function numberValue(value)
    {
        var n = parseFloat((value || '').toString().replace(/,/g, ''));
        if(isNaN(n))
        {
            return 0;
        }
        return n;
    }

    function formatAmount(value)
    {
        var n = numberValue(value);
        return n.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }

    function snapshotOptions($unit)
    {
        var options = [];
        $unit.find('option').each(function(){
            options.push({
                value: normalizeText($(this).attr('value')),
                text: normalizeText($(this).text())
            });
        });
        return options;
    }

    function applyUnitFilterForRow($material, $unit, allUnitOptions)
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

        var restored = false;
        if(currentValue !== '')
        {
            $unit.find('option').each(function(){
                if(normalizeText($(this).val()) === currentValue || normalizeText($(this).text()) === currentValue)
                {
                    $unit.val($(this).val());
                    restored = true;
                    return false;
                }
            });
        }
        if(!restored && allowed.length === 1)
        {
            var only = normalizeText(allowed[0]);
            $unit.find('option').each(function(){
                if(normalizeText($(this).val()) === only || normalizeText($(this).text()) === only)
                {
                    $unit.val($(this).val());
                    restored = true;
                    return false;
                }
            });
        }
        if(!restored)
        {
            $unit.val('');
        }
    }

    function bindMaterialUnitRow($row)
    {
        var $material = $row.find('.js-material');
        var $unit = $row.find('.js-unit');
        if(!$material.length || !$unit.length)
        {
            return;
        }
        var allUnitOptions = snapshotOptions($unit);
        $material.off('change.poMaterialUnit').on('change.poMaterialUnit', function(){
            applyUnitFilterForRow($material, $unit, allUnitOptions);
        });
        applyUnitFilterForRow($material, $unit, allUnitOptions);
    }

    function recalcLineTotal($row)
    {
        var qty = numberValue($row.find('.js-qty').val());
        var unitCost = numberValue($row.find('.js-unit-cost').val());
        var lineTotal = numberValue($row.find('.js-line-total').val());
        if(qty > 0 && unitCost > 0)
        {
            lineTotal = qty * unitCost;
            $row.find('.js-line-total').val(lineTotal.toFixed(2));
        }
        return lineTotal;
    }

    function recalcGrandTotal()
    {
        var total = 0;
        $('#poLinesBody').find('tr').each(function(){
            total += recalcLineTotal($(this));
        });
        $('#poGrandTotal').text(currencyPrefix + formatAmount(total));
    }

    function buildSelect(name, optionsMap, selectedValue, cssClass, emptyLabel)
    {
        var $select = $('<select></select>').attr('name', name).addClass('form-control ' + cssClass);
        $select.append($('<option></option>').attr('value', '').text(emptyLabel));
        $.each(optionsMap, function(value, label){
            var textValue = normalizeText(value);
            var $opt = $('<option></option>').attr('value', textValue).text(label);
            if(normalizeText(selectedValue) === textValue)
            {
                $opt.prop('selected', true);
            }
            $select.append($opt);
        });
        return $select;
    }

    function addLineRow(lineData)
    {
        lineData = lineData || {};
        var $row = $('<tr class="po-extra-line"></tr>');
        var $material = buildSelect('data[Ardpurchaseorder][line_material_type][]', materialOptions, lineData.material_type || '', 'js-material', '<?php echo addslashes(__('Please Select')); ?>');
        var $unit = buildSelect('data[Ardpurchaseorder][line_unit_type][]', unitOptions, lineData.unit_type || '', 'js-unit', '<?php echo addslashes(__('Please Select')); ?>');
        var $qty = $('<input type="text" class="form-control js-qty" />').attr('name', 'data[Ardpurchaseorder][line_quantity][]').val(lineData.quantity || '');
        var $unitCost = $('<input type="text" class="form-control js-unit-cost" />').attr('name', 'data[Ardpurchaseorder][line_unit_cost][]').val(lineData.unit_cost || '');
        var $lineTotal = $('<input type="text" class="form-control js-line-total" />').attr('name', 'data[Ardpurchaseorder][line_total_cost][]').val(lineData.total_cost || '');
        var $lineId = $('<input type="hidden" />').attr('name', 'data[Ardpurchaseorder][line_id][]').val(lineData.id || 0);
        var $removeBtn = $('<button type="button" class="btn btn-xs btn-danger"><span class="fa fa-trash"></span></button>');

        $row.append($('<td></td>').append($material).append($lineId));
        $row.append($('<td></td>').append($unit));
        $row.append($('<td></td>').append($qty));
        $row.append($('<td></td>').append($unitCost));
        $row.append($('<td></td>').append($lineTotal));
        $row.append($('<td class="text-center"></td>').append($removeBtn));
        $('#poLinesBody').append($row);

        bindMaterialUnitRow($row);
        recalcLineTotal($row);
        recalcGrandTotal();

        $removeBtn.on('click', function(){
            $row.remove();
            recalcGrandTotal();
        });
    }

    function bindAllLineEvents()
    {
        $('#poLinesBody').on('input', '.js-qty, .js-unit-cost, .js-line-total', function(){
            recalcGrandTotal();
        });
    }

    function bindPoNoAuto()
    {
        var $project = $('#PoProjectId');
        var $poNo = $('#PoNoInput');
        var $preview = $('#PoNextNoPreview');
        if(!$project.length || isEdit)
        {
            return;
        }
        $project.on('change', function(){
            var selectedProject = parseInt($(this).val(), 10) || 0;
            var nextNo = (selectedProject > 0 && typeof nextPoNoMap[selectedProject] !== 'undefined') ? nextPoNoMap[selectedProject] : '';
            if(nextNo !== '')
            {
                $poNo.val(nextNo);
                $preview.text(nextNo);
            }
            else
            {
                $preview.text('-');
            }
        }).trigger('change');
    }

    function initDatePickers()
    {
        if(typeof $.fn.datetimepicker !== 'function')
        {
            return;
        }
        $('.js-po-date-field').each(function(){
            var $field = $(this);
            var currentValue = normalizeText($field.val());
            if(currentValue === '0000-00-00')
            {
                $field.val('');
            }
            if($field.data('DateTimePicker'))
            {
                return;
            }
            $field.datetimepicker({
                format: 'YYYY-MM-DD',
                useCurrent: false,
                showTodayButton: true
            });
        });
    }

    bindMaterialUnitRow($('.po-main-line'));
    bindAllLineEvents();
    bindPoNoAuto();
    initDatePickers();
    recalcGrandTotal();

    if($.isArray(existingLineRows) && existingLineRows.length > 0)
    {
        $.each(existingLineRows, function(_, lineData){
            addLineRow(lineData);
        });
    }

    $('#addPoLineBtn').on('click', function(e){
        e.preventDefault();
        addLineRow();
    });

    $('#poLinesBody').on('click', '.js-remove-main-line', function(e){
        e.preventDefault();
        var $row = $(this).closest('tr');
        $row.find('.js-material').val('').trigger('change');
        $row.find('.js-unit').val('');
        $row.find('.js-qty, .js-unit-cost, .js-line-total').val('');
        recalcGrandTotal();
    });

    $('#openPoModal').on('click', function(e){
        e.preventDefault();
        $poModal.modal('show');
    });
    $(document).on('click', '.js-view-po', function(e){
        e.preventDefault();
        var $btn = $(this);
        $('#PoViewPoNo').text(nvl($btn.attr('data-po-no')));
        $('#PoViewVendor').text(nvl($btn.attr('data-vendor')));
        $('#PoViewStore').text(nvl($btn.attr('data-store')));
        $('#PoViewMaterial').text(nvl($btn.attr('data-material')));
        $('#PoViewQty').text(nvl($btn.attr('data-qty')));
        $('#PoViewAmount').text(nvl($btn.attr('data-amount')));
        $('#PoViewDate').text(nvl($btn.attr('data-date')));
        $('#PoViewStatus').text(nvl($btn.attr('data-status')));
        $('#PoViewNote').text(nvl($btn.attr('data-note')));
        $poViewModal.modal('show');
    });

    $poModal.on('shown.bs.modal', function(){
        initDatePickers();
    });

    <?php if($isEdit){ ?>
    $poModal.modal('show');
    <?php } ?>
    if(openViewId > 0)
    {
        var $targetView = $('.js-view-po[data-po-id="' + openViewId + '"]').first();
        if($targetView.length)
        {
            $targetView.trigger('click');
        }
        else if(fallbackPoView && typeof fallbackPoView === 'object' && (parseInt(fallbackPoView.id || 0, 10) === openViewId))
        {
            $('#PoViewPoNo').text(nvl(fallbackPoView.po_no));
            $('#PoViewVendor').text(nvl(fallbackPoView.vendor));
            $('#PoViewStore').text(nvl(fallbackPoView.store));
            $('#PoViewMaterial').text(nvl(fallbackPoView.material));
            $('#PoViewQty').text(nvl(fallbackPoView.qty));
            $('#PoViewAmount').text(nvl(fallbackPoView.amount));
            $('#PoViewDate').text(nvl(fallbackPoView.date));
            $('#PoViewStatus').text(nvl(fallbackPoView.status));
            $('#PoViewNote').text(nvl(fallbackPoView.note));
            $poViewModal.modal('show');
        }
    }
});
</script>
