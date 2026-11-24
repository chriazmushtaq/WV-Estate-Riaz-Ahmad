<?php
$this->Js->JqueryEngine->jQueryObject = 'jQuery';
$this->Paginator->options(array('update' => '#resultDiv', 'evalScripts' => true));
$projectForm = isset($this->request->data['Ardconstructionproject']) ? $this->request->data['Ardconstructionproject'] : array();
$milestoneForm = isset($this->request->data['Ardconstructionmilestone']) ? $this->request->data['Ardconstructionmilestone'] : array();
$isProjectEdit = !empty($projectForm['id']);
$isMilestoneEdit = !empty($milestoneForm['id']);
$constructionProjectList = (isset($constructionProjectList) && is_array($constructionProjectList)) ? $constructionProjectList : array();
$milestonesByProject = (isset($milestonesByProject) && is_array($milestonesByProject)) ? $milestonesByProject : array();
$consumptionSummary = (isset($consumptionSummary) && is_array($consumptionSummary)) ? $consumptionSummary : array();
$projectConsumptionBreakdown = (isset($projectConsumptionBreakdown) && is_array($projectConsumptionBreakdown)) ? $projectConsumptionBreakdown : array();
$milestoneProgressById = (isset($milestoneProgressById) && is_array($milestoneProgressById)) ? $milestoneProgressById : array();
$milestoneActualById = (isset($milestoneActualById) && is_array($milestoneActualById)) ? $milestoneActualById : array();
$projectProgressById = (isset($projectProgressById) && is_array($projectProgressById)) ? $projectProgressById : array();
$projectConsumptionChart = (isset($projectConsumptionChart) && is_array($projectConsumptionChart)) ? $projectConsumptionChart : array('labels' => array(), 'material_cost' => array(), 'labour_cost' => array(), 'progress' => array());
$milestoneOptions = (isset($milestoneOptions) && is_array($milestoneOptions)) ? $milestoneOptions : array();
$milestoneProjectMap = (isset($milestoneProjectMap) && is_array($milestoneProjectMap)) ? $milestoneProjectMap : array();
$labourList = (isset($labourList) && is_array($labourList)) ? $labourList : array();
$consStart = isset($consStart) ? (string)$consStart : '';
$consEnd = isset($consEnd) ? (string)$consEnd : '';
$labourConsumptionForm = isset($this->request->data['Ardconstructionconsumption']) ? $this->request->data['Ardconstructionconsumption'] : array();
$openProjectModal = $isProjectEdit || !empty($this->request->query['project_new']);
$openMilestoneModal = $isMilestoneEdit || !empty($this->request->query['milestone_new']);
$openLabourModal = !empty($this->request->query['labour_new']);
?>
<style type="text/css">
.construction-progress-wrap{height:8px;background:#e6ecf5;border-radius:999px;overflow:hidden;margin-top:4px;}
.construction-progress-wrap span{display:block;height:100%;background:linear-gradient(90deg,#2f80ed,#27ae60);}
.construction-kpi{
    border:1px solid #dce5f2;
    border-radius:8px;
    padding:10px 12px;
    background:#f8fbff;
    margin-bottom:12px;
}
.construction-kpi h5{margin:0 0 4px 0;font-size:12px;text-transform:uppercase;color:#5a6d8a;letter-spacing:.4px;}
.construction-kpi strong{font-size:20px;display:block;color:#1c355e;}
.construction-chart-wrap{position:relative;height:300px;}
</style>
<div id="resultDiv">
<div class="page-title"><div class="title-env"><h1 class="title"><?php echo __('Construction Projects'); ?></h1></div></div>

<div class="panel"><?php echo $this->Session->flash(); ?>
    <div class="panel-heading">
        <div class="btn-group">
            <?php echo $this->Html->link('<span class="fa fa-home"></span>&nbsp;'.__('Construction'), array('controller' => 'Ardaccounts', 'action' => 'construction', $currentProjectId), array('escape' => false, 'class' => 'btn btn-default')); ?>
            <?php echo $this->Html->link('<span class="fa fa-briefcase"></span>&nbsp;'.__('Contracts'), array('controller' => 'Ardaccounts', 'action' => 'contracts', $currentProjectId), array('escape' => false, 'class' => 'btn btn-primary')); ?>
            <?php echo $this->Html->link('<span class="fa fa-file-text-o"></span>&nbsp;'.__('Running Bills (IPC)'), array('controller' => 'Ardaccounts', 'action' => 'contractbills', $currentProjectId), array('escape' => false, 'class' => 'btn btn-info')); ?>
            <?php echo $this->Html->link('<span class="fa fa-users"></span>&nbsp;'.__('Site Labour'), array('controller' => 'Ardaccounts', 'action' => 'labourcontrol', $currentProjectId), array('escape' => false, 'class' => 'btn btn-warning')); ?>
            <?php echo $this->Html->link('<span class="fa fa-arrow-circle-down"></span>&nbsp;'.__('Stock Out'), array('controller' => 'Ardaccounts', 'action' => 'stockout', $currentProjectId), array('escape' => false, 'class' => 'btn btn-danger')); ?>
            <?php echo $this->Html->link('<span class="fa fa-line-chart"></span>&nbsp;'.__('Budgets'), array('controller' => 'Ardaccounts', 'action' => 'budgets', $currentProjectId), array('escape' => false, 'class' => 'btn btn-default')); ?>
            <a href="javascript:void(0);" class="btn btn-success" data-toggle="modal" data-target="#constructionProjectModal"><span class="fa fa-plus"></span>&nbsp;<?php echo __('Add Project'); ?></a>
            <a href="javascript:void(0);" class="btn btn-primary" data-toggle="modal" data-target="#constructionMilestoneModal"><span class="fa fa-flag"></span>&nbsp;<?php echo __('Add Milestone'); ?></a>
            <a href="javascript:void(0);" class="btn btn-default" data-toggle="modal" data-target="#labourConsumptionModal"><span class="fa fa-user-plus"></span>&nbsp;<?php echo __('Post Labour'); ?></a>
        </div>
    </div>
</div>

<div class="modal fade" id="constructionProjectModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><?php echo $isProjectEdit ? __('Edit Construction Project') : __('Add Construction Project'); ?></h4>
            </div>
            <div class="modal-body">
                <?php echo $this->Form->create('Ardconstructionproject', array('url' => array('controller' => 'Ardaccounts', 'action' => 'constructionprojects', $currentProjectId), 'class' => 'form-horizontal')); ?>
                <?php echo $this->Form->hidden('_form_type', array('value' => 'project')); ?>
                <input type="hidden" name="_form_type" value="project" />
                <?php if($userType != 'ADR'){ echo $this->Form->hidden('project_id', array('value' => $agencyId)); } ?>
                <?php if($isProjectEdit){ echo $this->Form->hidden('id'); } ?>
                <div class="row">
                    <?php if($userType == 'ADR'){ ?>
                    <div class="col-sm-3"><?php echo $this->Form->input('project_id', array('label' => __('Agency'), 'class' => 'form-control', 'options' => $projectName, 'empty' => __('Please Select'))); ?></div>
                    <?php } ?>
                    <div class="col-sm-3"><?php echo $this->Form->input('site_id', array('label' => __('Stock Store / Site'), 'class' => 'form-control', 'options' => $siteList, 'empty' => __('Please Select'))); ?></div>
                    <div class="col-sm-3"><?php echo $this->Form->input('name', array('label' => __('Project Name'), 'class' => 'form-control')); ?></div>
                    <div class="col-sm-3"><?php echo $this->Form->input('project_code', array('label' => __('Project Code'), 'class' => 'form-control', 'placeholder' => __('Auto if empty'))); ?></div>
                    <div class="col-sm-3"><?php echo $this->Form->input('workflow_stage', array('label' => __('Workflow Stage'), 'class' => 'form-control', 'options' => array('planning' => __('Planning'), 'foundation' => __('Foundation'), 'structure' => __('Structure'), 'finishing' => __('Finishing'), 'handover' => __('Handover')))); ?></div>
                </div>
                <div class="row">
                    <div class="col-sm-2"><?php echo $this->Form->input('start_date', array('label' => __('Start Date'), 'class' => 'form-control', 'type' => 'text', 'placeholder' => 'YYYY-MM-DD')); ?></div>
                    <div class="col-sm-2"><?php echo $this->Form->input('expected_end_date', array('label' => __('Expected End'), 'class' => 'form-control', 'type' => 'text', 'placeholder' => 'YYYY-MM-DD')); ?></div>
                    <div class="col-sm-2"><?php echo $this->Form->input('completed_date', array('label' => __('Completed Date'), 'class' => 'form-control', 'type' => 'text', 'placeholder' => 'YYYY-MM-DD')); ?></div>
                    <div class="col-sm-2"><?php echo $this->Form->input('status', array('label' => __('Status'), 'class' => 'form-control', 'options' => array('active' => __('Active'), 'hold' => __('Hold'), 'completed' => __('Completed'), 'cancelled' => __('Cancelled')))); ?></div>
                    <div class="col-sm-2"><?php echo $this->Form->input('manager_name', array('label' => __('Manager'), 'class' => 'form-control')); ?></div>
                    <div class="col-sm-2"><?php echo $this->Form->input('manager_phone', array('label' => __('Manager Phone'), 'class' => 'form-control')); ?></div>
                </div>
                <div class="row">
                    <div class="col-sm-3"><?php echo $this->Form->input('budget_total', array('label' => __('Total Budget'), 'class' => 'form-control')); ?></div>
                    <div class="col-sm-3"><?php echo $this->Form->input('budget_material', array('label' => __('Material Budget'), 'class' => 'form-control')); ?></div>
                    <div class="col-sm-3"><?php echo $this->Form->input('budget_labour', array('label' => __('Labour Budget'), 'class' => 'form-control')); ?></div>
                    <div class="col-sm-3"><?php echo $this->Form->input('notes', array('label' => __('Notes'), 'class' => 'form-control')); ?></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success"><span class="fa fa-save"></span> <?php echo $isProjectEdit ? __('Update Project') : __('Save Project'); ?></button>
                <?php if($isProjectEdit){ echo $this->Html->link('<span class="fa fa-close"></span> '.__('Cancel Edit'), array('controller' => 'Ardaccounts', 'action' => 'constructionprojects', $currentProjectId), array('escape' => false, 'class' => 'btn btn-danger')); } ?>
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Close'); ?></button>
                <?php echo $this->Form->end(); ?>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="constructionMilestoneModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><?php echo $isMilestoneEdit ? __('Edit Milestone') : __('Add Milestone'); ?></h4>
            </div>
            <div class="modal-body">
                <?php echo $this->Form->create('Ardconstructionmilestone', array('url' => array('controller' => 'Ardaccounts', 'action' => 'constructionprojects', $currentProjectId), 'class' => 'form-horizontal')); ?>
                <?php echo $this->Form->hidden('_form_type', array('value' => 'milestone')); ?>
                <input type="hidden" name="_form_type" value="milestone" />
                <?php if($userType != 'ADR'){ echo $this->Form->hidden('project_id', array('value' => $agencyId)); } ?>
                <?php if($isMilestoneEdit){ echo $this->Form->hidden('id'); } ?>
                <div class="row">
                    <?php if($userType == 'ADR'){ ?>
                    <div class="col-sm-3"><?php echo $this->Form->input('project_id', array('label' => __('Agency'), 'class' => 'form-control', 'options' => $projectName, 'empty' => __('Please Select'))); ?></div>
                    <?php } ?>
                    <div class="col-sm-3"><?php echo $this->Form->input('construction_project_id', array('label' => __('Construction Project'), 'class' => 'form-control', 'options' => $constructionProjectList, 'empty' => __('Please Select'))); ?></div>
                    <div class="col-sm-3"><?php echo $this->Form->input('title', array('label' => __('Milestone Title'), 'class' => 'form-control')); ?></div>
                    <div class="col-sm-2"><?php echo $this->Form->input('target_date', array('label' => __('Target Date'), 'class' => 'form-control', 'type' => 'text', 'placeholder' => 'YYYY-MM-DD')); ?></div>
                    <div class="col-sm-2"><?php echo $this->Form->input('completed_date', array('label' => __('Completed Date'), 'class' => 'form-control', 'type' => 'text', 'placeholder' => 'YYYY-MM-DD')); ?></div>
                    <div class="col-sm-2"><?php echo $this->Form->input('status', array('label' => __('Status'), 'class' => 'form-control', 'options' => array('pending' => __('Pending'), 'in_progress' => __('In Progress'), 'completed' => __('Completed'), 'hold' => __('Hold')))); ?></div>
                </div>
                <div class="row">
                    <div class="col-sm-3"><?php echo $this->Form->input('budget_amount', array('label' => __('Budget Amount'), 'class' => 'form-control')); ?></div>
                    <div class="col-sm-3"><?php echo $this->Form->input('material_cost', array('label' => __('Material Cost'), 'class' => 'form-control')); ?></div>
                    <div class="col-sm-3"><?php echo $this->Form->input('labour_cost', array('label' => __('Labour Cost'), 'class' => 'form-control')); ?></div>
                    <div class="col-sm-3"><?php echo $this->Form->input('notes', array('label' => __('Notes'), 'class' => 'form-control')); ?></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary"><span class="fa fa-save"></span> <?php echo $isMilestoneEdit ? __('Update Milestone') : __('Save Milestone'); ?></button>
                <?php if($isMilestoneEdit){ echo $this->Html->link('<span class="fa fa-close"></span> '.__('Cancel'), array('controller' => 'Ardaccounts', 'action' => 'constructionprojects', $currentProjectId), array('escape' => false, 'class' => 'btn btn-default')); } ?>
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Close'); ?></button>
                <?php echo $this->Form->end(); ?>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script type="text/javascript">
(function(){
    function onReady(fn){
        if(document.readyState === 'loading'){
            document.addEventListener('DOMContentLoaded', fn);
        }else{
            fn();
        }
    }

    onReady(function(){
        var projectSelect = document.querySelector('.js-labour-project');
        var milestoneSelect = document.querySelector('.js-labour-milestone');
        var filterLabourMilestones = function(){
            if(!projectSelect || !milestoneSelect){
                return;
            }
            var selectedProjectId = parseInt(projectSelect.value || '0', 10);
            var selectedMilestoneId = parseInt(milestoneSelect.value || '0', 10);
            Array.prototype.forEach.call(milestoneSelect.options, function(opt){
                var optValue = parseInt(opt.value || '0', 10);
                var optProjectId = parseInt(opt.getAttribute('data-project-id') || '0', 10);
                if(!opt.value){
                    opt.style.display = '';
                    return;
                }
                if(selectedProjectId === 0 || optProjectId === selectedProjectId){
                    opt.style.display = '';
                }else{
                    opt.style.display = 'none';
                    if(selectedMilestoneId === optValue){
                        selectedMilestoneId = 0;
                    }
                }
            });
            if(selectedMilestoneId === 0){
                milestoneSelect.value = '';
            }
        };

        if(projectSelect && milestoneSelect){
            projectSelect.addEventListener('change', filterLabourMilestones);
            filterLabourMilestones();
        }

        var chartLabels = <?php echo json_encode(isset($projectConsumptionChart['labels']) ? (array)$projectConsumptionChart['labels'] : array()); ?>;
        var chartMaterial = <?php echo json_encode(isset($projectConsumptionChart['material_cost']) ? (array)$projectConsumptionChart['material_cost'] : array()); ?>;
        var chartLabour = <?php echo json_encode(isset($projectConsumptionChart['labour_cost']) ? (array)$projectConsumptionChart['labour_cost'] : array()); ?>;
        var chartProgress = <?php echo json_encode(isset($projectConsumptionChart['progress']) ? (array)$projectConsumptionChart['progress'] : array()); ?>;

        if(typeof Chart !== 'undefined' && chartLabels.length > 0){
            var barCanvas = document.getElementById('constructionProjectConsumptionChart');
            if(barCanvas){
                new Chart(barCanvas.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: chartLabels,
                        datasets: [
                            {
                                label: '<?php echo addslashes(__('Material Cost')); ?>',
                                data: chartMaterial,
                                backgroundColor: '#2f80ed'
                            },
                            {
                                label: '<?php echo addslashes(__('Labour Cost')); ?>',
                                data: chartLabour,
                                backgroundColor: '#27ae60'
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {position: 'bottom'}
                        },
                        scales: {
                            x: {stacked: false},
                            y: {beginAtZero: true}
                        }
                    }
                });
            }

            var progressCanvas = document.getElementById('constructionProjectProgressChart');
            if(progressCanvas){
                new Chart(progressCanvas.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: chartLabels,
                        datasets: [{
                            label: '<?php echo addslashes(__('Progress %')); ?>',
                            data: chartProgress,
                            backgroundColor: '#f39c12'
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {display: false}
                        },
                        scales: {
                            x: {
                                beginAtZero: true,
                                max: 100
                            }
                        }
                    }
                });
            }
        }

        if(typeof jQuery !== 'undefined' && typeof jQuery.fn.modal === 'function'){
            var shouldOpenProjectModal = <?php echo $openProjectModal ? 'true' : 'false'; ?>;
            var shouldOpenMilestoneModal = <?php echo $openMilestoneModal ? 'true' : 'false'; ?>;
            var shouldOpenLabourModal = <?php echo $openLabourModal ? 'true' : 'false'; ?>;
            if(shouldOpenProjectModal){
                jQuery('#constructionProjectModal').modal('show');
            }else if(shouldOpenMilestoneModal){
                jQuery('#constructionMilestoneModal').modal('show');
            }else if(shouldOpenLabourModal){
                jQuery('#labourConsumptionModal').modal('show');
            }
        }
    });
})();
</script>

<div class="panel">
    <div class="panel-heading"><h4 style="margin:0;"><?php echo __('Project-wise Consumption Reports'); ?></h4></div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-12">
                <?php echo $this->Form->create(false, array('type' => 'get', 'url' => array('controller' => 'Ardaccounts', 'action' => 'constructionprojects', $currentProjectId), 'class' => 'form-inline')); ?>
                <div class="form-group">
                    <label><?php echo __('Start Date'); ?></label>
                    <input type="text" class="form-control" name="cons_start" placeholder="YYYY-MM-DD" value="<?php echo h($consStart); ?>" />
                </div>
                <div class="form-group" style="margin-left:8px;">
                    <label><?php echo __('End Date'); ?></label>
                    <input type="text" class="form-control" name="cons_end" placeholder="YYYY-MM-DD" value="<?php echo h($consEnd); ?>" />
                </div>
                <button type="submit" class="btn btn-primary" style="margin-left:8px;"><span class="fa fa-filter"></span> <?php echo __('Apply'); ?></button>
                <?php echo $this->Html->link('<span class="fa fa-refresh"></span> '.__('Clear'), array('controller' => 'Ardaccounts', 'action' => 'constructionprojects', $currentProjectId), array('escape' => false, 'class' => 'btn btn-default', 'style' => 'margin-left:6px;')); ?>
                <?php echo $this->Form->end(); ?>
            </div>
        </div>

        <?php
            $reportMaterialTotal = 0.0;
            $reportLabourTotal = 0.0;
            $reportTotalCost = 0.0;
            $reportTotalBudget = 0.0;
            foreach((array)$rows as $reportRow)
            {
                if(empty($reportRow['Ardconstructionproject'])){ continue; }
                $rp = $reportRow['Ardconstructionproject'];
                $rpId = (int)$rp['id'];
                $rpCons = isset($projectConsumptionBreakdown[$rpId]) ? $projectConsumptionBreakdown[$rpId] : array();
                $reportMaterialTotal += isset($rpCons['material_cost']) ? (float)$rpCons['material_cost'] : 0;
                $reportLabourTotal += isset($rpCons['labour_cost']) ? (float)$rpCons['labour_cost'] : 0;
                $reportTotalCost += isset($rpCons['total_cost']) ? (float)$rpCons['total_cost'] : 0;
                $reportTotalBudget += isset($rp['budget_total']) ? (float)$rp['budget_total'] : 0;
            }
            $reportUtilization = ($reportTotalBudget > 0) ? round(($reportTotalCost / $reportTotalBudget) * 100, 2) : 0.00;
        ?>
        <div class="row" style="margin-top:12px;">
            <div class="col-sm-3">
                <div class="construction-kpi">
                    <h5><?php echo __('Material Consumption'); ?></h5>
                    <strong><?php echo $currency.$this->Number->format((float)$reportMaterialTotal); ?></strong>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="construction-kpi">
                    <h5><?php echo __('Labour Consumption'); ?></h5>
                    <strong><?php echo $currency.$this->Number->format((float)$reportLabourTotal); ?></strong>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="construction-kpi">
                    <h5><?php echo __('Total Consumption'); ?></h5>
                    <strong><?php echo $currency.$this->Number->format((float)$reportTotalCost); ?></strong>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="construction-kpi">
                    <h5><?php echo __('Budget Utilization'); ?></h5>
                    <strong><?php echo number_format((float)$reportUtilization, 2, '.', ','); ?>%</strong>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <tr>
                    <th><?php echo __('Project'); ?></th>
                    <th><?php echo __('Material Cost'); ?></th>
                    <th><?php echo __('Labour Cost'); ?></th>
                    <th><?php echo __('Total Cost'); ?></th>
                    <th><?php echo __('Budget'); ?></th>
                    <th><?php echo __('Utilization'); ?></th>
                    <th><?php echo __('Progress'); ?></th>
                </tr>
                <?php foreach((array)$rows as $reportRow){ ?>
                <?php
                    if(empty($reportRow['Ardconstructionproject'])){ continue; }
                    $rp = $reportRow['Ardconstructionproject'];
                    $rpId = (int)$rp['id'];
                    $rpCons = isset($projectConsumptionBreakdown[$rpId]) ? $projectConsumptionBreakdown[$rpId] : array('material_cost' => 0, 'labour_cost' => 0, 'total_cost' => 0);
                    $rpBudget = isset($rp['budget_total']) ? (float)$rp['budget_total'] : 0;
                    $rpUtil = ($rpBudget > 0) ? round((((float)$rpCons['total_cost']) / $rpBudget) * 100, 2) : 0.00;
                    $rpProg = isset($projectProgressById[$rpId]) ? (float)$projectProgressById[$rpId] : 0;
                ?>
                <tr>
                    <td><?php echo h($rp['name']); ?></td>
                    <td><?php echo $currency.$this->Number->format((float)$rpCons['material_cost']); ?></td>
                    <td><?php echo $currency.$this->Number->format((float)$rpCons['labour_cost']); ?></td>
                    <td><?php echo $currency.$this->Number->format((float)$rpCons['total_cost']); ?></td>
                    <td><?php echo $currency.$this->Number->format((float)$rpBudget); ?></td>
                    <td><?php echo number_format((float)$rpUtil, 2, '.', ','); ?>%</td>
                    <td>
                        <div><?php echo number_format((float)$rpProg, 2, '.', ','); ?>%</div>
                        <div class="construction-progress-wrap"><span style="width:<?php echo number_format((float)$rpProg, 2, '.', ''); ?>%;"></span></div>
                    </td>
                </tr>
                <?php } ?>
            </table>
        </div>

        <div class="row">
            <div class="col-md-8">
                <h5><?php echo __('Consumption by Project (Material vs Labour)'); ?></h5>
                <div class="construction-chart-wrap"><canvas id="constructionProjectConsumptionChart"></canvas></div>
            </div>
            <div class="col-md-4">
                <h5><?php echo __('Project Progress %'); ?></h5>
                <div class="construction-chart-wrap"><canvas id="constructionProjectProgressChart"></canvas></div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="labourConsumptionModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><?php echo __('Post Labour Consumption'); ?></h4>
            </div>
            <div class="modal-body">
                <?php echo $this->Form->create('Ardconstructionconsumption', array('url' => array('controller' => 'Ardaccounts', 'action' => 'constructionprojects', $currentProjectId), 'class' => 'form-horizontal')); ?>
                <?php echo $this->Form->hidden('_form_type', array('value' => 'labour_consumption')); ?>
                <input type="hidden" name="_form_type" value="labour_consumption" />
                <?php if(!empty($labourConsumptionForm['id'])){ echo $this->Form->hidden('id'); } ?>
                <?php if($userType != 'ADR'){ echo $this->Form->hidden('project_id', array('value' => $agencyId)); } ?>
                <div class="row">
                    <?php if($userType == 'ADR'){ ?>
                    <div class="col-sm-3"><?php echo $this->Form->input('project_id', array('label' => __('Agency'), 'class' => 'form-control', 'options' => $projectName, 'empty' => __('Please Select'))); ?></div>
                    <?php } ?>
                    <div class="col-sm-3"><?php echo $this->Form->input('construction_project_id', array('label' => __('Construction Project'), 'class' => 'form-control js-labour-project', 'options' => $constructionProjectList, 'empty' => __('Please Select'))); ?></div>
                    <div class="col-sm-3">
                        <label><?php echo __('Milestone'); ?></label>
                        <select name="data[Ardconstructionconsumption][milestone_id]" class="form-control js-labour-milestone">
                            <option value=""><?php echo __('None'); ?></option>
                            <?php
                                $selectedMilestoneId = isset($labourConsumptionForm['milestone_id']) ? (int)$labourConsumptionForm['milestone_id'] : 0;
                                foreach((array)$milestoneOptions as $milestoneId => $milestoneTitle)
                                {
                                    $optionProjectId = isset($milestoneProjectMap[(int)$milestoneId]) ? (int)$milestoneProjectMap[(int)$milestoneId] : 0;
                                    $selectedAttr = ($selectedMilestoneId === (int)$milestoneId) ? 'selected' : '';
                                    echo '<option value="'.(int)$milestoneId.'" data-project-id="'.(int)$optionProjectId.'" '.$selectedAttr.'>'.h($milestoneTitle).'</option>';
                                }
                            ?>
                        </select>
                    </div>
                    <div class="col-sm-3"><?php echo $this->Form->input('labour_id', array('label' => __('Labour'), 'class' => 'form-control', 'options' => $labourList, 'empty' => __('Please Select'))); ?></div>
                </div>
                <div class="row">
                    <div class="col-sm-2"><?php echo $this->Form->input('entry_date', array('label' => __('Entry Date'), 'class' => 'form-control', 'type' => 'text', 'placeholder' => 'YYYY-MM-DD', 'value' => !empty($labourConsumptionForm['entry_date']) ? $labourConsumptionForm['entry_date'] : date('Y-m-d'))); ?></div>
                    <div class="col-sm-2"><?php echo $this->Form->input('unit_type', array('label' => __('Unit'), 'class' => 'form-control', 'options' => array('day' => __('Day'), 'hour' => __('Hour'), 'month' => __('Month'), 'job' => __('Job')), 'value' => !empty($labourConsumptionForm['unit_type']) ? $labourConsumptionForm['unit_type'] : 'day')); ?></div>
                    <div class="col-sm-2"><?php echo $this->Form->input('quantity', array('label' => __('Quantity'), 'class' => 'form-control', 'type' => 'text')); ?></div>
                    <div class="col-sm-2"><?php echo $this->Form->input('unit_cost', array('label' => __('Unit Cost'), 'class' => 'form-control', 'type' => 'text')); ?></div>
                    <div class="col-sm-4"><?php echo $this->Form->input('notes', array('label' => __('Notes'), 'class' => 'form-control', 'type' => 'text')); ?></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success"><span class="fa fa-save"></span> <?php echo __('Save Labour Consumption'); ?></button>
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Close'); ?></button>
                <?php echo $this->Form->end(); ?>
            </div>
        </div>
    </div>
</div>

<div class="panel">
    <div class="panel-heading"><h4 style="margin:0;"><?php echo __('Construction Projects Overview'); ?></h4></div>
    <div class="panel-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <tr>
                    <th>#</th>
                    <th><?php echo __('Project'); ?></th>
                    <th><?php echo __('Site'); ?></th>
                    <th><?php echo __('Workflow'); ?></th>
                    <th><?php echo __('Budget'); ?></th>
                    <th><?php echo __('Material Used'); ?></th>
                    <th><?php echo __('Labour Used'); ?></th>
                    <th><?php echo __('Total Used'); ?></th>
                    <th><?php echo __('Milestones'); ?></th>
                    <th><?php echo __('Progress'); ?></th>
                    <th><?php echo __('Status'); ?></th>
                    <th><?php echo __('Action'); ?></th>
                </tr>
                <?php
                $pageParams = $this->Paginator->params();
                $limit = isset($pageParams['limit']) ? (int)$pageParams['limit'] : 20;
                $page = isset($pageParams['page']) ? (int)$pageParams['page'] : 1;
                $serialNo = 1 * $limit * ($page - 1) + 1;
                foreach((array)$rows as $row):
                    $project = $row['Ardconstructionproject'];
                    $projectId = (int)$project['id'];
                    $siteName = isset($siteList[(int)$project['site_id']]) ? $siteList[(int)$project['site_id']] : '-';
                    $cons = isset($consumptionSummary[$projectId]) ? $consumptionSummary[$projectId] : array('total_qty' => 0, 'total_cost' => 0, 'material_qty' => 0, 'material_cost' => 0, 'labour_qty' => 0, 'labour_cost' => 0);
                    $milestones = isset($milestonesByProject[$projectId]) ? $milestonesByProject[$projectId] : array();
                    $projectProgress = isset($projectProgressById[$projectId]) ? (float)$projectProgressById[$projectId] : 0;
                ?>
                <tr>
                    <td><?php echo $serialNo++; ?></td>
                    <td>
                        <strong><?php echo h($project['name']); ?></strong><br>
                        <small><?php echo h($project['project_code']); ?></small>
                    </td>
                    <td><?php echo h($siteName); ?></td>
                    <td><?php echo h(ucwords(str_replace('_', ' ', (string)$project['workflow_stage']))); ?></td>
                    <td>
                        <div><?php echo __('Total: ').$currency.$this->Number->format((float)$project['budget_total']); ?></div>
                        <div><?php echo __('Material: ').$currency.$this->Number->format((float)$project['budget_material']); ?></div>
                        <div><?php echo __('Labour: ').$currency.$this->Number->format((float)$project['budget_labour']); ?></div>
                    </td>
                    <td>
                        <div><?php echo __('Qty: ').number_format((float)$cons['material_qty'], 3, '.', ','); ?></div>
                        <div><?php echo __('Cost: ').$currency.$this->Number->format((float)$cons['material_cost']); ?></div>
                    </td>
                    <td>
                        <div><?php echo __('Qty: ').number_format((float)$cons['labour_qty'], 3, '.', ','); ?></div>
                        <div><?php echo __('Cost: ').$currency.$this->Number->format((float)$cons['labour_cost']); ?></div>
                    </td>
                    <td>
                        <div><?php echo __('Qty: ').number_format((float)$cons['total_qty'], 3, '.', ','); ?></div>
                        <div><?php echo __('Cost: ').$currency.$this->Number->format((float)$cons['total_cost']); ?></div>
                    </td>
                    <td>
                        <?php if(empty($milestones)){ echo '-'; } else { ?>
                            <?php foreach($milestones as $ms){ ?>
                                <?php
                                    $msId = isset($ms['id']) ? (int)$ms['id'] : 0;
                                    $msProgress = isset($milestoneProgressById[$msId]) ? (float)$milestoneProgressById[$msId] : 0;
                                    $msActual = isset($milestoneActualById[$msId]['total_cost']) ? (float)$milestoneActualById[$msId]['total_cost'] : 0;
                                    $msBudget = isset($ms['budget_amount']) ? (float)$ms['budget_amount'] : 0;
                                ?>
                                <div style="margin-bottom:4px;">
                                    <span class="label label-default"><?php echo h($ms['title']); ?></span>
                                    <small>(<?php echo h(ucfirst((string)$ms['status'])); ?>)</small>
                                    <div style="font-size:11px;margin-top:2px;"><?php echo __('Actual').' '.$currency.$this->Number->format($msActual).' / '.__('Budget').' '.$currency.$this->Number->format($msBudget).' ('.number_format((float)$msProgress, 2, '.', ',').'%)'; ?></div>
                                    <div class="construction-progress-wrap"><span style="width:<?php echo number_format((float)$msProgress, 2, '.', ''); ?>%;"></span></div>
                                    <?php echo $this->Html->link('<span class="fa fa-edit"></span>', array('controller' => 'Ardaccounts', 'action' => 'constructionprojects', $currentProjectId, '?' => array('milestone_edit' => (int)$ms['id'])), array('escape' => false, 'class' => 'btn btn-xs btn-warning')); ?>
                                    <?php echo $this->Html->link('<span class="fa fa-trash"></span>', array('controller' => 'Ardaccounts', 'action' => 'deletemilestone', (int)$ms['id'], $currentProjectId), array('escape' => false, 'class' => 'btn btn-xs btn-danger', 'onclick' => "return confirm('".__('Are you sure?')."');")); ?>
                                </div>
                            <?php } ?>
                        <?php } ?>
                    </td>
                    <td>
                        <div><?php echo number_format((float)$projectProgress, 2, '.', ','); ?>%</div>
                        <div class="construction-progress-wrap"><span style="width:<?php echo number_format((float)$projectProgress, 2, '.', ''); ?>%;"></span></div>
                    </td>
                    <td><?php echo h(ucfirst((string)$project['status'])); ?></td>
                    <td>
                        <?php echo $this->Html->link('<span class="fa fa-edit"></span> '.__('Edit'), array('controller' => 'Ardaccounts', 'action' => 'constructionprojects', $currentProjectId, '?' => array('edit' => $projectId)), array('escape' => false, 'class' => 'btn btn-xs btn-warning')); ?>
                        <?php echo $this->Html->link('<span class="fa fa-trash"></span> '.__('Delete'), array('controller' => 'Ardaccounts', 'action' => 'deleteconstructionproject', $projectId, $currentProjectId), array('escape' => false, 'class' => 'btn btn-xs btn-danger', 'onclick' => "return confirm('".__('Are you sure?')."');")); ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
        <?php echo $this->element('pagination'); ?>
    </div>
</div>
</div>
