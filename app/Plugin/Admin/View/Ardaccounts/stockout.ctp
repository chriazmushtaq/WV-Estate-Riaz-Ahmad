<?php
$this->Js->JqueryEngine->jQueryObject = 'jQuery';
$this->Paginator->options(array('update' => '#resultDiv', 'evalScripts' => true));
$materialOptions = (isset($constructionMaterialOptions) && is_array($constructionMaterialOptions)) ? $constructionMaterialOptions : array();
$unitOptions = (isset($constructionUnitOptions) && is_array($constructionUnitOptions)) ? $constructionUnitOptions : array();
$materialUnitMap = (isset($constructionMaterialUnitMap) && is_array($constructionMaterialUnitMap)) ? $constructionMaterialUnitMap : array();
$constructionProjectList = (isset($constructionProjectList) && is_array($constructionProjectList)) ? $constructionProjectList : array();
$milestoneList = (isset($milestoneList) && is_array($milestoneList)) ? $milestoneList : array();
$milestoneProjectMap = (isset($milestoneProjectMap) && is_array($milestoneProjectMap)) ? $milestoneProjectMap : array();
?>
<div id="resultDiv">
<div class="page-title"><div class="title-env"><h1 class="title"><?php echo __('Stock Out'); ?></h1></div></div>
<div class="panel"><?php echo $this->Session->flash(); ?>
    <div class="panel-heading">
        <div class="btn-group">
            <?php echo $this->Html->link('<span class="fa fa-home"></span>&nbsp;'.__('Construction'), array('controller' => 'Ardaccounts', 'action' => 'construction', $currentProjectId), array('escape' => false, 'class' => 'btn btn-default')); ?>
            <?php echo $this->Html->link('<span class="fa fa-cubes"></span>&nbsp;'.__('Stock In'), array('controller' => 'Ardaccounts', 'action' => 'stock', $currentProjectId), array('escape' => false, 'class' => 'btn btn-success')); ?>
            <?php echo $this->Html->link('<span class="fa fa-tasks"></span>&nbsp;'.__('Construction Projects'), array('controller' => 'Ardaccounts', 'action' => 'constructionprojects', $currentProjectId), array('escape' => false, 'class' => 'btn btn-info')); ?>
        </div>
    </div>
    <div class="panel-body">
        <?php echo $this->Form->create('Ardstock', array('url' => array('controller' => 'Ardaccounts', 'action' => 'stockout', $currentProjectId), 'class' => 'form-horizontal')); ?>
        <?php if($userType != 'ADR'){ echo $this->Form->hidden('project_id', array('value' => $agencyId)); } ?>
        <div class="row">
            <?php if($userType == 'ADR'){ ?>
            <div class="col-sm-3"><?php echo $this->Form->input('project_id', array('label' => __('Agency'), 'class' => 'form-control', 'options' => $projectName, 'empty' => __('Please Select'))); ?></div>
            <?php } ?>
            <div class="col-sm-3"><?php echo $this->Form->input('movement_date', array('label' => __('Date'), 'type' => 'text', 'class' => 'form-control', 'value' => $date, 'placeholder' => __('YYYY-MM-DD'))); ?></div>
            <div class="col-sm-3"><?php echo $this->Form->input('site_id', array('label' => __('Stock Store'), 'class' => 'form-control', 'options' => $siteList, 'empty' => __('Select Stock Store'))); ?></div>
            <div class="col-sm-3"><?php echo $this->Form->input('construction_project_id', array('label' => __('Construction Project'), 'class' => 'form-control', 'options' => $constructionProjectList, 'empty' => __('Optional'))); ?></div>
        </div>
        <div class="row">
            <div class="col-sm-3"><?php echo $this->Form->input('milestone_id', array('label' => __('Milestone'), 'class' => 'form-control', 'options' => $milestoneList, 'empty' => __('Optional'))); ?></div>
            <div class="col-sm-3"><?php echo $this->Form->input('material_type', array('label' => __('Material'), 'id' => 'StockOutMaterialType', 'class' => 'form-control', 'options' => $materialOptions, 'empty' => __('Please Select'))); ?></div>
            <div class="col-sm-3"><?php echo $this->Form->input('unit_type', array('label' => __('Unit'), 'id' => 'StockOutUnitType', 'class' => 'form-control', 'options' => $unitOptions, 'empty' => __('Please Select'))); ?></div>
            <div class="col-sm-3"><?php echo $this->Form->input('quantity', array('label' => __('Quantity'), 'class' => 'form-control', 'placeholder' => __('Quantity'))); ?></div>
        </div>
        <div class="row">
            <div class="col-sm-3"><?php echo $this->Form->input('unit_cost', array('label' => __('Unit Cost'), 'class' => 'form-control', 'placeholder' => __('Unit Cost'))); ?></div>
            <div class="col-sm-9"><?php echo $this->Form->input('notes', array('label' => __('Notes'), 'class' => 'form-control', 'placeholder' => __('Consumption notes / remarks'))); ?></div>
        </div>
        <div style="margin-top:10px;">
            <button type="submit" class="btn btn-danger"><span class="fa fa-save"></span> <?php echo __('Save Stock Out'); ?></button>
        </div>
        <?php echo $this->Form->end(); ?>
    </div>
</div>

<div class="panel">
    <div class="panel-heading"><h4 style="margin:0;"><?php echo __('Stock Out History'); ?></h4></div>
    <div class="panel-body">
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <tr>
                    <th>#</th>
                    <th><?php echo __('Date'); ?></th>
                    <th><?php echo __('Stock Store'); ?></th>
                    <th><?php echo __('Material'); ?></th>
                    <th><?php echo __('Qty'); ?></th>
                    <th><?php echo __('Cost'); ?></th>
                    <th><?php echo __('Total'); ?></th>
                    <th><?php echo __('Note'); ?></th>
                </tr>
                <?php
                $pageParams = $this->Paginator->params();
                $limit = isset($pageParams['limit']) ? (int)$pageParams['limit'] : 20;
                $page = isset($pageParams['page']) ? (int)$pageParams['page'] : 1;
                $serialNo = 1 * $limit * ($page - 1) + 1;
                foreach((array)$rows as $row):
                ?>
                <tr>
                    <td><?php echo $serialNo++; ?></td>
                    <td><?php echo (!empty($row['Ardstock']['movement_date']) && $row['Ardstock']['movement_date'] !== '0000-00-00') ? date('d-M-Y', strtotime((string)$row['Ardstock']['movement_date'])) : '-'; ?></td>
                    <td><?php echo isset($row['Ardsite']['site_name']) ? h($row['Ardsite']['site_name']) : '-'; ?></td>
                    <td><?php echo h($row['Ardstock']['material_type']); ?></td>
                    <td><?php echo number_format((float)$row['Ardstock']['quantity'], 3, '.', ',') . ' ' . h($row['Ardstock']['unit_type']); ?></td>
                    <td><?php echo $currency . $this->Number->format((float)$row['Ardstock']['unit_cost']); ?></td>
                    <td><?php echo $currency . $this->Number->format((float)$row['Ardstock']['total_cost']); ?></td>
                    <td><?php echo h($row['Ardstock']['notes']); ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
        <?php echo $this->element('pagination'); ?>
    </div>
</div>
</div>

<script type="text/javascript">
jQuery(function($){
    var materialUnitMap = <?php echo json_encode($materialUnitMap); ?>;
    var milestoneProjectMap = <?php echo json_encode($milestoneProjectMap); ?>;

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

        $material.on('change', applyUnitFilter);
        applyUnitFilter();
    }

    function bindMilestoneFilter()
    {
        var $project = $('#ArdstockConstructionProjectId');
        var $milestone = $('#ArdstockMilestoneId');
        if(!$project.length || !$milestone.length)
        {
            return;
        }
        var allMilestones = [];
        $milestone.find('option').each(function(){
            allMilestones.push({
                value: normalizeText($(this).attr('value')),
                text: normalizeText($(this).text()),
                projectId: parseInt(milestoneProjectMap[normalizeText($(this).attr('value'))] || 0, 10)
            });
        });
        function applyMilestones()
        {
            var selectedProjectId = parseInt($project.val() || 0, 10);
            var currentValue = normalizeText($milestone.val());
            $milestone.empty();
            $milestone.append($('<option></option>').attr('value', '').text('<?php echo addslashes(__('Optional')); ?>'));
            $.each(allMilestones, function(_, item){
                if(item.value === '')
                {
                    return;
                }
                if(selectedProjectId <= 0 || item.projectId === selectedProjectId)
                {
                    $milestone.append($('<option></option>').attr('value', item.value).text(item.text));
                }
            });
            if(currentValue !== '')
            {
                $milestone.val(currentValue);
            }
        }
        $project.on('change', applyMilestones);
        applyMilestones();
    }

    bindMaterialUnitLink('#StockOutMaterialType', '#StockOutUnitType');
    bindMilestoneFilter();
});
</script>
