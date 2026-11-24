<?php
$selectedBlock = isset($this->request->data['BallotingUnit']['block_name']) ? trim((string)$this->request->data['BallotingUnit']['block_name']) : '';
$selectedStreet = isset($this->request->data['BallotingUnit']['street_address']) ? trim((string)$this->request->data['BallotingUnit']['street_address']) : '';
$blockOptions = (isset($blockOptions) && is_array($blockOptions)) ? $blockOptions : array();
$streetOptions = (isset($streetOptions) && is_array($streetOptions)) ? $streetOptions : array();
$addressOptionsByProject = (isset($addressOptionsByProject) && is_array($addressOptionsByProject)) ? $addressOptionsByProject : array();
if ($selectedBlock !== '' && !isset($blockOptions[$selectedBlock])) {
    $blockOptions[$selectedBlock] = $selectedBlock;
}
if ($selectedStreet !== '' && !isset($streetOptions[$selectedStreet])) {
    $streetOptions[$selectedStreet] = $selectedStreet;
}
?>
<div class="page-title">
    <div class="title-env">
        <h1 class="title"><?php echo __('Add Balloting Unit');?></h1>
    </div>
</div>

<div class="panel">
    <div class="panel-body">
        <?php echo $this->Session->flash();?>
        <?php echo $this->Form->create('BallotingUnit', array('class' => 'form-horizontal'));?>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label class="col-sm-4 control-label"><?php echo __('Agency');?></label>
                    <div class="col-sm-8">
                        <?php if(!empty($canUseAgencyFilters)){?>
                        <?php echo $this->Form->select('project_id', $projectName, array('empty' => __('Select Agency'), 'label' => false, 'class' => 'form-control', 'div' => false, 'value' => $projectId, 'id' => 'BallotingUnitProjectId'));?>
                        <?php } else { ?>
                        <?php echo $this->Form->hidden('project_id', array('value' => $projectId));?>
                        <input type="text" class="form-control" value="<?php echo h(isset($projectName[$projectId]) ? $projectName[$projectId] : __('Current Agency'));?>" readonly="readonly">
                        <?php } ?>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4 control-label"><?php echo __('Allotment Number');?></label>
                    <div class="col-sm-8"><?php echo $this->Form->input('allotment_no', array('label' => false, 'class' => 'form-control', 'div' => false));?></div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4 control-label"><?php echo __('Property Number');?></label>
                    <div class="col-sm-8"><?php echo $this->Form->input('property_no', array('label' => false, 'class' => 'form-control', 'div' => false));?></div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4 control-label"><?php echo __('Street Number');?></label>
                    <div class="col-sm-8"><?php echo $this->Form->input('street_no', array('label' => false, 'class' => 'form-control', 'div' => false));?></div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="col-sm-4 control-label"><?php echo __('Block');?></label>
                    <div class="col-sm-8">
                        <?php echo $this->Form->select('block_name', $blockOptions, array('empty' => __('Select Block'), 'label' => false, 'class' => 'form-control', 'div' => false, 'id' => 'BallotingUnitBlockName'));?>
                        <p id="BallotingBlockHelp" class="help-block"<?php echo empty($blockOptions) ? '' : ' style="display:none;"';?>><?php echo __('Add Block list in Accounts Settings for selected agency.');?></p>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4 control-label"><?php echo __('Street Address');?></label>
                    <div class="col-sm-8">
                        <?php echo $this->Form->select('street_address', $streetOptions, array('empty' => __('Select Street Address'), 'label' => false, 'class' => 'form-control', 'div' => false, 'id' => 'BallotingUnitStreetAddress'));?>
                        <p id="BallotingStreetHelp" class="help-block"<?php echo empty($streetOptions) ? '' : ' style="display:none;"';?>><?php echo __('Add Street list in Accounts Settings for selected agency.');?></p>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4 control-label"><?php echo __('Property Type');?></label>
                    <div class="col-sm-8"><?php echo $this->Form->select('property_type_id', $propertyTypeOptions, array('empty' => __('Select'), 'label' => false, 'class' => 'form-control', 'div' => false));?></div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4 control-label"><?php echo __('Unit');?></label>
                    <div class="col-sm-8"><?php echo $this->Form->select('unit_id', $unitOptions, array('empty' => __('Select'), 'label' => false, 'class' => 'form-control', 'div' => false));?></div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label class="col-sm-4 control-label"><?php echo __('Area');?></label>
                    <div class="col-sm-8"><?php echo $this->Form->input('unit_area', array('type' => 'number', 'step' => '0.01', 'min' => '0', 'label' => false, 'class' => 'form-control', 'div' => false));?></div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="col-sm-4 control-label"><?php echo __('Status');?></label>
                    <div class="col-sm-8"><?php echo $this->Form->select('status', $unitStatusArr, array('label' => false, 'class' => 'form-control', 'div' => false, 'empty' => false));?></div>
                </div>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo __('Notes');?></label>
            <div class="col-sm-10"><?php echo $this->Form->input('notes', array('type' => 'textarea', 'rows' => 3, 'label' => false, 'class' => 'form-control', 'div' => false));?></div>
        </div>
        <div class="form-group">
            <div class="col-sm-12">
                <button type="submit" class="btn btn-success"><span class="fa fa-save"></span>&nbsp;<?php echo __('Save Unit');?></button>
                <?php echo $this->Html->link('<span class="fa fa-list"></span>&nbsp;'.__('Manage Units'), array('action' => 'units', '?' => array('project_id' => $projectId)), array('escape' => false, 'class' => 'btn btn-primary'));?>
            </div>
        </div>
        <?php echo $this->Form->end();?>
    </div>
</div>
<script type="text/javascript">
jQuery(function($){
    var $project = $('#BallotingUnitProjectId');
    var $block = $('#BallotingUnitBlockName');
    var $street = $('#BallotingUnitStreetAddress');
    var $blockHelp = $('#BallotingBlockHelp');
    var $streetHelp = $('#BallotingStreetHelp');
    var selectedBlock = <?php echo json_encode($selectedBlock);?>;
    var selectedStreet = <?php echo json_encode($selectedStreet);?>;
    var addressMap = <?php echo json_encode($addressOptionsByProject);?>;
    function fillSelect($el, options, placeholder, preferred){
        var keep = (typeof preferred === 'string' && preferred.length) ? preferred : ($el.val() || '');
        $el.empty().append($('<option/>', { value: '', text: placeholder }));
        if(options && typeof options === 'object'){
            $.each(options, function(val, text){
                var value = (typeof val === 'string') ? val : (text || '');
                var label = (typeof text === 'string' && text.length) ? text : value;
                $el.append($('<option/>', { value: value, text: label }));
            });
        }
        if(keep){
            var hasValue = false;
            $el.find('option').each(function(){
                if($(this).val() === keep){
                    hasValue = true;
                    return false;
                }
            });
            if(hasValue){
                $el.val(keep);
            }
        }
    }
    function refreshAddressOptions(projectId){
        projectId = parseInt(projectId, 10) || 0;
        var row = (addressMap && (addressMap[projectId] || addressMap[String(projectId)])) ? (addressMap[projectId] || addressMap[String(projectId)]) : null;
        var blocks = (row && row.blockOptions) ? row.blockOptions : {};
        var streets = (row && row.streetOptions) ? row.streetOptions : {};
        if(projectId <= 0){
            fillSelect($block, {}, <?php echo json_encode(__('Select Block'));?>, '');
            fillSelect($street, {}, <?php echo json_encode(__('Select Street Address'));?>, '');
            $blockHelp.show();
            $streetHelp.show();
            return;
        }
        fillSelect($block, blocks, <?php echo json_encode(__('Select Block'));?>, selectedBlock);
        fillSelect($street, streets, <?php echo json_encode(__('Select Street Address'));?>, selectedStreet);
        $blockHelp.toggle($.isEmptyObject(blocks));
        $streetHelp.toggle($.isEmptyObject(streets));
        selectedBlock = '';
        selectedStreet = '';
    }
    if($project.length){
        refreshAddressOptions($project.val());
        $project.on('change', function(){
            selectedBlock = '';
            selectedStreet = '';
            refreshAddressOptions($(this).val());
        });
    }
});
</script>
