<?php
$settings = !empty($settingsRow['CrmAutomationSetting']) ? (array)$settingsRow['CrmAutomationSetting'] : array();
$ruleForm = !empty($formRow['CrmAssignmentRule']) ? (array)$formRow['CrmAssignmentRule'] : array();
$isSuperAdmin = !empty($isSuperAdmin);
?>
<div class="page-title">
    <div class="title-env">
        <h1 class="title"><?php echo __('CRM Assignment Rules');?></h1>
    </div>
</div>

<div class="panel">
    <div class="panel-heading">
        <div class="btn-group">
            <?php echo $this->Html->link('<span class="fa fa-cogs"></span>&nbsp;'.__('Automation Settings'), array('controller' => 'Crmautomations', 'action' => 'index', '?' => array('project_id' => $selectedProjectId)), array('escape' => false, 'class' => 'btn btn-default'));?>
            <?php echo $this->Html->link('<span class="fa fa-random"></span>&nbsp;'.__('Assignment Rules'), array('controller' => 'Crmautomations', 'action' => 'assignmentrules', '?' => array('project_id' => $selectedProjectId)), array('escape' => false, 'class' => 'btn btn-success'));?>
            <?php echo $this->Html->link('<span class="fa fa-clock-o"></span>&nbsp;'.__('Follow-up Queue'), array('controller' => 'Crmautomations', 'action' => 'queue'), array('escape' => false, 'class' => 'btn btn-default'));?>
            <?php echo $this->Html->link('<span class="fa fa-bullseye"></span>&nbsp;'.__('Lead Reactivation'), array('controller' => 'Crmautomations', 'action' => 'reactivation'), array('escape' => false, 'class' => 'btn btn-primary'));?>
            <?php echo $this->Html->link('<span class="fa fa-pie-chart"></span>&nbsp;'.__('Source ROI'), array('controller' => 'Crmautomations', 'action' => 'sourceroi', '?' => array('project_id' => $selectedProjectId)), array('escape' => false, 'class' => 'btn btn-info'));?>
            <?php echo $this->Html->link('<span class="fa fa-plug"></span>&nbsp;'.__('CRM Settings'), array('controller' => 'Crmintegrations', 'action' => 'index'), array('escape' => false, 'class' => 'btn btn-info'));?>
        </div>
    </div>
    <div class="panel-body">
        <?php echo $this->Session->flash();?>

        <div class="alert alert-info">
            <strong><?php echo __('How It Works');?></strong><br/>
            <?php echo __('When auto assignment is enabled, the first active rule that matches source, city, budget, or language will assign the lead to the selected user. Blank match fields act like a fallback rule.');?>
        </div>

        <div class="row">
            <div class="col-md-5">
                <div class="panel panel-default">
                    <div class="panel-heading"><strong><?php echo __('Assignment Settings');?></strong></div>
                    <div class="panel-body">
                        <?php echo $this->Form->create('CrmAutomationSetting', array('class' => 'form-horizontal'));?>
                        <?php echo $this->Form->hidden('CrmAssignmentRule.task', array('value' => 'save_settings'));?>
                        <?php if($isSuperAdmin){ ?>
                        <div class="form-group">
                            <label class="col-sm-4 control-label"><?php echo __('Agency');?></label>
                            <div class="col-sm-8">
                                <?php echo $this->Form->select('project_id', (array)$projectOptions, array('empty' => __('Please Select'), 'label' => false, 'class' => 'form-control', 'div' => false, 'value' => $selectedProjectId));?>
                            </div>
                        </div>
                        <?php } else {
                            echo $this->Form->hidden('project_id', array('value' => $selectedProjectId));
                        ?>
                        <div class="form-group">
                            <label class="col-sm-4 control-label"><?php echo __('Agency');?></label>
                            <div class="col-sm-8">
                                <div class="form-control" style="background:#f9f9f9;"><?php echo h($currentAgencyName !== '' ? $currentAgencyName : __('Current Agency'));?></div>
                            </div>
                        </div>
                        <?php } ?>
                        <div class="form-group">
                            <label class="col-sm-4 control-label"><?php echo __('Enable');?></label>
                            <div class="col-sm-8" style="padding-top:7px;">
                                <label>
                                    <?php echo $this->Form->checkbox('auto_assignment_enabled', array('value' => 1, 'hiddenField' => false, 'checked' => !empty($settings['auto_assignment_enabled'])));?>
                                    <?php echo __('Use rule-based lead assignment');?>
                                </label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-4 control-label"><?php echo __('Mode');?></label>
                            <div class="col-sm-8" style="padding-top:7px;">
                                <label>
                                    <?php echo $this->Form->checkbox('assignment_override_user', array('value' => 1, 'hiddenField' => false, 'checked' => !empty($settings['assignment_override_user'])));?>
                                    <?php echo __('Override selected/default user when rule matches');?>
                                </label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-4 control-label"><?php echo __('Apply On');?></label>
                            <div class="col-sm-8" style="padding-top:7px;">
                                <div><label><?php echo $this->Form->checkbox('assignment_apply_on_manual', array('value' => 1, 'hiddenField' => false, 'checked' => !empty($settings['assignment_apply_on_manual'])));?> <?php echo __('Manual Add/Edit');?></label></div>
                                <div><label><?php echo $this->Form->checkbox('assignment_apply_on_import', array('value' => 1, 'hiddenField' => false, 'checked' => !empty($settings['assignment_apply_on_import'])));?> <?php echo __('CSV / Excel Import');?></label></div>
                                <div><label><?php echo $this->Form->checkbox('assignment_apply_on_webhook', array('value' => 1, 'hiddenField' => false, 'checked' => !empty($settings['assignment_apply_on_webhook'])));?> <?php echo __('Facebook / WhatsApp Webhooks');?></label></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-offset-4 col-sm-8">
                                <button type="submit" class="btn btn-success"><span class="fa fa-save"></span>&nbsp;<?php echo __('Save Settings');?></button>
                            </div>
                        </div>
                        <?php echo $this->Form->end();?>
                    </div>
                </div>
            </div>

            <div class="col-md-7">
                <div class="panel panel-default">
                    <div class="panel-heading"><strong><?php echo !empty($ruleForm['id']) ? __('Edit Assignment Rule') : __('Add Assignment Rule');?></strong></div>
                    <div class="panel-body">
                        <?php echo $this->Form->create('CrmAssignmentRule', array('class' => 'form-horizontal'));?>
                        <?php echo $this->Form->hidden('task', array('value' => 'save_rule'));?>
                        <?php echo $this->Form->hidden('id', array('value' => !empty($ruleForm['id']) ? (int)$ruleForm['id'] : 0));?>
                        <?php if($isSuperAdmin){ ?>
                        <div class="form-group">
                            <label class="col-sm-3 control-label"><?php echo __('Agency');?></label>
                            <div class="col-sm-9">
                                <?php echo $this->Form->select('project_id', (array)$projectOptions, array('empty' => __('Please Select'), 'label' => false, 'class' => 'form-control', 'div' => false, 'value' => !empty($ruleForm['project_id']) ? (int)$ruleForm['project_id'] : $selectedProjectId));?>
                            </div>
                        </div>
                        <?php } else {
                            echo $this->Form->hidden('project_id', array('value' => $selectedProjectId));
                        } ?>
                        <div class="form-group">
                            <label class="col-sm-3 control-label"><?php echo __('Rule Title');?></label>
                            <div class="col-sm-9">
                                <?php echo $this->Form->input('title', array('label' => false, 'class' => 'form-control', 'div' => false, 'value' => isset($ruleForm['title']) ? $ruleForm['title'] : ''));?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label"><?php echo __('Priority');?></label>
                            <div class="col-sm-3">
                                <?php echo $this->Form->input('priority', array('type' => 'number', 'label' => false, 'class' => 'form-control', 'div' => false, 'value' => isset($ruleForm['priority']) ? $ruleForm['priority'] : 0));?>
                            </div>
                            <label class="col-sm-2 control-label"><?php echo __('Status');?></label>
                            <div class="col-sm-4">
                                <?php echo $this->Form->select('status', array('Active' => __('Active'), 'Inactive' => __('Inactive')), array('label' => false, 'class' => 'form-control', 'div' => false, 'value' => isset($ruleForm['status']) ? $ruleForm['status'] : 'Active'));?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label"><?php echo __('Source Contains');?></label>
                            <div class="col-sm-9">
                                <?php echo $this->Form->input('match_source', array('label' => false, 'class' => 'form-control', 'div' => false, 'value' => isset($ruleForm['match_source']) ? $ruleForm['match_source'] : ''));?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label"><?php echo __('City Contains');?></label>
                            <div class="col-sm-9">
                                <?php echo $this->Form->input('match_city', array('label' => false, 'class' => 'form-control', 'div' => false, 'value' => isset($ruleForm['match_city']) ? $ruleForm['match_city'] : ''));?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label"><?php echo __('Budget Range');?></label>
                            <div class="col-sm-4">
                                <?php echo $this->Form->input('budget_min', array('label' => false, 'placeholder' => __('Min'), 'class' => 'form-control', 'div' => false, 'value' => isset($ruleForm['budget_min']) ? $ruleForm['budget_min'] : ''));?>
                            </div>
                            <div class="col-sm-5">
                                <?php echo $this->Form->input('budget_max', array('label' => false, 'placeholder' => __('Max'), 'class' => 'form-control', 'div' => false, 'value' => isset($ruleForm['budget_max']) ? $ruleForm['budget_max'] : ''));?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label"><?php echo __('Language');?></label>
                            <div class="col-sm-9">
                                <?php echo $this->Form->input('match_language', array('label' => false, 'class' => 'form-control', 'div' => false, 'value' => isset($ruleForm['match_language']) ? $ruleForm['match_language'] : ''));?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label"><?php echo __('Assign To User');?></label>
                            <div class="col-sm-9">
                                <?php echo $this->Form->select('target_user_id', (array)$userOptions, array('empty' => __('Please Select'), 'label' => false, 'class' => 'form-control', 'div' => false, 'value' => isset($ruleForm['target_user_id']) ? (int)$ruleForm['target_user_id'] : 0));?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label"><?php echo __('Notes');?></label>
                            <div class="col-sm-9">
                                <?php echo $this->Form->textarea('notes', array('label' => false, 'class' => 'form-control', 'div' => false, 'value' => isset($ruleForm['notes']) ? $ruleForm['notes'] : '', 'rows' => 3));?>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-offset-3 col-sm-9">
                                <button type="submit" class="btn btn-success"><span class="fa fa-save"></span>&nbsp;<?php echo __('Save Rule');?></button>
                                <?php echo $this->Html->link('<span class="fa fa-refresh"></span>&nbsp;'.__('Reset'), array('controller' => 'Crmautomations', 'action' => 'assignmentrules', '?' => array('project_id' => $selectedProjectId)), array('escape' => false, 'class' => 'btn btn-default'));?>
                            </div>
                        </div>
                        <?php echo $this->Form->end();?>
                    </div>
                </div>
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading"><strong><?php echo __('Active Rules');?></strong></div>
            <div class="panel-body" style="overflow:auto;">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th><?php echo __('#');?></th>
                            <th><?php echo __('Priority');?></th>
                            <th><?php echo __('Rule');?></th>
                            <th><?php echo __('Match Conditions');?></th>
                            <th><?php echo __('Assigned User');?></th>
                            <th><?php echo __('User Level');?></th>
                            <th><?php echo __('Status');?></th>
                            <th><?php echo __('Action');?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if(!empty($ruleRows)){ $sr=1; foreach($ruleRows as $row){
                        $rule = (array)$row['CrmAssignmentRule'];
                        $parts = array();
                        if(trim((string)$rule['match_source']) !== ''){$parts[] = __('Source').': '.h($rule['match_source']);}
                        if(trim((string)$rule['match_city']) !== ''){$parts[] = __('City').': '.h($rule['match_city']);}
                        if(trim((string)$rule['match_language']) !== ''){$parts[] = __('Language').': '.h($rule['match_language']);}
                        if(trim((string)$rule['budget_min']) !== '' || trim((string)$rule['budget_max']) !== ''){$parts[] = __('Budget').': '.h($rule['budget_min']).' - '.h($rule['budget_max']);}
                        if(empty($parts)){$parts[] = __('Fallback rule for any lead in this agency');}
                    ?>
                        <tr>
                            <td><?php echo $sr++;?></td>
                            <td><?php echo (int)$rule['priority'];?></td>
                            <td>
                                <strong><?php echo h($rule['title']);?></strong>
                                <?php if(trim((string)$rule['notes']) !== ''){ ?>
                                <div class="small text-muted"><?php echo nl2br(h($rule['notes']));?></div>
                                <?php } ?>
                            </td>
                            <td><?php echo implode('<br/>', $parts);?></td>
                            <td><?php echo !empty($row['User']['name']) ? h($row['User']['name']) : '-';?></td>
                            <td><?php echo !empty($row['Ugroup']['name']) ? h($row['Ugroup']['name']) : '-';?></td>
                            <td><?php echo h($rule['status']);?></td>
                            <td style="white-space:nowrap;">
                                <?php echo $this->Html->link('<span class="fa fa-pencil"></span>&nbsp;'.__('Edit'), array('controller' => 'Crmautomations', 'action' => 'assignmentrules', '?' => array('project_id' => $selectedProjectId, 'edit' => (int)$rule['id'])), array('escape' => false, 'class' => 'btn btn-xs btn-primary'));?>
                                <?php echo $this->Form->create('CrmAssignmentRule', array('url' => array('controller' => 'Crmautomations', 'action' => 'assignmentrules', '?' => array('project_id' => $selectedProjectId)), 'style' => 'display:inline-block;margin-left:4px;'));?>
                                <?php echo $this->Form->hidden('task', array('value' => 'delete_rule'));?>
                                <?php echo $this->Form->hidden('id', array('value' => (int)$rule['id']));?>
                                <button type="submit" class="btn btn-xs btn-danger" onclick="return confirm('<?php echo h(__('Are you sure you want to delete this assignment rule?'));?>');"><span class="fa fa-trash"></span>&nbsp;<?php echo __('Delete');?></button>
                                <?php echo $this->Form->end();?>
                            </td>
                        </tr>
                    <?php } } else { ?>
                        <tr><td colspan="8" class="text-center text-muted"><?php echo __('No assignment rules found for selected agency.');?></td></tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
