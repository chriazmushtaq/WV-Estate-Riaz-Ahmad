<div class="page-title">
    <div class="title-env">
        <h1 class="title"><?php echo __('Manual Balloting Allotment');?></h1>
    </div>
</div>

<div class="panel">
    <div class="panel-heading">
        <?php echo $this->Html->link('<span class="fa fa-dashboard"></span>&nbsp;'.__('Dashboard'), array('action' => 'dashboard', '?' => array('project_id' => $projectId)), array('escape' => false, 'class' => 'btn btn-primary'));?>
        <?php echo $this->Html->link('<span class="fa fa-list"></span>&nbsp;'.__('Allotments'), array('action' => 'allotments', '?' => array('project_id' => $projectId)), array('escape' => false, 'class' => 'btn btn-default'));?>
        <?php echo $this->Html->link('<span class="fa fa-random"></span>&nbsp;'.__('Run Balloting'), array('action' => 'run', '?' => array('project_id' => $projectId)), array('escape' => false, 'class' => 'btn btn-success'));?>
    </div>
    <div class="panel-body">
        <?php echo $this->Session->flash();?>

        <?php echo $this->Form->create(false, array('type' => 'get', 'class' => 'form-inline', 'style' => 'margin-bottom:15px;'));?>
        <div class="form-group">
            <?php if(!empty($canUseAgencyFilters)){?>
            <?php echo $this->Form->select('project_id', $projectName, array('empty' => __('Select Agency'), 'value' => $projectId, 'label' => false, 'div' => false, 'class' => 'form-control', 'id' => 'BallotingManualFilterProjectId'));?>
            <?php } else { ?>
            <?php echo $this->Form->hidden('project_id', array('value' => $projectId));?>
            <?php } ?>
        </div>
        <div class="form-group">
            <?php echo $this->Form->input('deal_keyword', array('value' => $dealKeyword, 'label' => false, 'div' => false, 'class' => 'form-control', 'placeholder' => __('Search Deals')));?>
        </div>
        <div class="form-group">
            <?php echo $this->Form->input('unit_keyword', array('value' => $unitKeyword, 'label' => false, 'div' => false, 'class' => 'form-control', 'placeholder' => __('Search Units')));?>
        </div>
        <button type="submit" class="btn btn-info"><?php echo __('Load');?></button>
        <?php echo $this->Html->link(__('Reset'), array('action' => 'manualallotment', '?' => array('project_id' => $projectId)), array('class' => 'btn btn-link'));?>
        <?php echo $this->Form->end();?>

        <?php echo $this->Form->create('BallotingAllotment', array('class' => 'form-horizontal'));?>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label class="col-sm-4 control-label"><?php echo __('Agency');?></label>
                    <div class="col-sm-8">
                        <?php if(!empty($canUseAgencyFilters)){?>
                        <?php echo $this->Form->select('project_id', $projectName, array('empty' => __('Select Agency'), 'value' => $projectId, 'label' => false, 'div' => false, 'class' => 'form-control', 'id' => 'BallotingManualProjectId'));?>
                        <?php } else { ?>
                        <?php echo $this->Form->hidden('project_id', array('value' => $projectId));?>
                        <input type="text" class="form-control" readonly="readonly" value="<?php echo h(isset($projectName[$projectId]) ? $projectName[$projectId] : __('Current Agency'));?>">
                        <?php } ?>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4 control-label"><?php echo __('Select Deal');?></label>
                    <div class="col-sm-8">
                        <?php echo $this->Form->select('deal_id', $dealOptions, array('empty' => __('Select Deal'), 'label' => false, 'div' => false, 'class' => 'form-control'));?>
                        <span class="help-block"><?php echo __('Only deals without active allotment are shown.');?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="col-sm-4 control-label"><?php echo __('Select Unit');?></label>
                    <div class="col-sm-8">
                        <?php echo $this->Form->select('balloting_unit_id', $unitOptions, array('empty' => __('Select Available Unit'), 'label' => false, 'div' => false, 'class' => 'form-control'));?>
                        <span class="help-block"><?php echo __('Only units with Available status are shown.');?></span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4 control-label"><?php echo __('Notes');?></label>
                    <div class="col-sm-8">
                        <?php echo $this->Form->input('notes', array('type' => 'textarea', 'rows' => 3, 'label' => false, 'div' => false, 'class' => 'form-control', 'placeholder' => __('Optional notes for manual allotment')));?>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-12">
                <button type="submit" class="btn btn-success" onclick="return confirm('<?php echo addslashes(__('Save manual allotment now?'));?>');"><span class="fa fa-check-circle"></span>&nbsp;<?php echo __('Save Manual Allotment');?></button>
            </div>
        </div>
        <?php echo $this->Form->end();?>
    </div>
</div>

<?php if(!empty($canUseAgencyFilters)){?>
<script type="text/javascript">
jQuery(function($){
    var $formAgency = $('#BallotingManualProjectId');
    var $filterAgency = $('#BallotingManualFilterProjectId');
    function go(projectId){
        projectId = parseInt(projectId, 10) || 0;
        if(projectId > 0){
            var baseUrl = <?php echo json_encode($this->Html->url(array('controller' => 'Balloting', 'action' => 'manualallotment')));?>;
            var dealId = parseInt($('#BallotingAllotmentDealId').val(), 10) || 0;
            var targetUrl = baseUrl + '?project_id=' + encodeURIComponent(projectId);
            if(dealId > 0){
                targetUrl += '&deal_id=' + encodeURIComponent(dealId);
            }
            window.location = targetUrl;
        }
    }
    if($formAgency.length){
        $formAgency.on('change', function(){ go($(this).val()); });
    }
    if($filterAgency.length){
        $filterAgency.on('change', function(){ go($(this).val()); });
    }
});
</script>
<?php }?>
