<div class="page-title">
    <div class="title-env">
        <h1 class="title"><i class="fa fa-users text-primary"></i> <?php echo __('1-by-1 Looping Agent Assignment Rules');?></h1>
        <p class="description"><?php echo __('Distribute incoming Facebook leads automatically to selected active sales agents in round-robin sequence.');?></p>
    </div>
</div>

<div class="panel">
    <div class="panel-heading" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
        <div class="btn-group" style="display:flex;flex-wrap:wrap;gap:4px;">
            <?php echo $this->Html->link('<i class="fa fa-dashboard"></i> '.__('Overview'), array('action'=>'index'), array('class'=>'btn btn-default', 'escape'=>false));?>
            <?php echo $this->Html->link('<i class="fa fa-cogs"></i> '.__('API Accounts'), array('action'=>'settings'), array('class'=>'btn btn-default', 'escape'=>false));?>
            <?php echo $this->Html->link('<i class="fa fa-users"></i> '.__('Agent Looper'), array('action'=>'agents'), array('class'=>'btn btn-primary', 'escape'=>false));?>
            <?php echo $this->Html->link('<i class="fa fa-list-alt"></i> '.__('Form Mapping'), array('action'=>'forms'), array('class'=>'btn btn-default', 'escape'=>false));?>
            <?php echo $this->Html->link('<i class="fa fa-bell"></i> '.__('Notifications'), array('action'=>'notifications'), array('class'=>'btn btn-default', 'escape'=>false));?>
            <?php echo $this->Html->link('<i class="fa fa-whatsapp"></i> '.__('WhatsApp API'), array('action'=>'whatsapp'), array('class'=>'btn btn-default', 'escape'=>false));?>
            <?php echo $this->Html->link('<i class="fa fa-history"></i> '.__('Sync Logs'), array('action'=>'logs'), array('class'=>'btn btn-default', 'escape'=>false));?>
            <?php echo $this->Html->link('<i class="fa fa-book"></i> '.__('Documentation'), array('action'=>'docs'), array('class'=>'btn btn-default', 'escape'=>false));?>
        </div>

        <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
            <?php echo $this->Html->link('<i class="fa fa-sort-numeric-asc"></i> '.__('Auto-Sort Queue (1, 2, 3...)'), array('action'=>'resequence_agents'), array('class'=>'btn btn-info', 'escape'=>false, 'onclick'=>"return confirm('".__('Automatically re-number all queue orders to 1, 2, 3... sequentially?')."');"));?>
            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#addAgentModal">
                <i class="fa fa-user-plus"></i> <?php echo __('Add Agent to Rotation Pool');?>
            </button>
        </div>
    </div>

    <div class="panel-body">
        <?php echo $this->Session->flash();?>

        <!-- Current Agent Rotation Table -->
        <div class="panel panel-default" style="border-radius:10px;overflow:hidden;box-shadow:0 2px 10px rgba(0,0,0,0.05);">
            <div class="panel-heading" style="background:#f8fafc;font-weight:700;display:flex;justify-content:space-between;align-items:center;">
                <div>
                    <i class="fa fa-refresh text-primary"></i> <?php echo __('Active 1-by-1 Looping Queue (Round-Robin)');?>
                </div>
                <div style="font-size:12px;font-weight:normal;color:#64748b;">
                    <?php echo count($assignments);?> <?php echo __('agents currently in pool');?>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-striped table-bordered table-hover" style="margin-bottom:0;vertical-align:middle;">
                    <thead>
                        <tr style="background:#f1f5f9;color:#334155;">
                            <th style="width:100px;text-align:center;"><?php echo __('Queue Order');?></th>
                            <th><?php echo __('Agent Name & Agency');?></th>
                            <th><?php echo __('Target Project Filter');?></th>
                            <th><?php echo __('Total FB Leads Assigned');?></th>
                            <th><?php echo __('Last Lead Assigned At');?></th>
                            <th><?php echo __('Queue Status');?></th>
                            <th style="width:230px;"><?php echo __('Action');?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($assignments)){ foreach($assignments as $a){ 
                            $assignmentId = (int)$a['FacebookAgentAssignment']['id'];
                            $agentName = !empty($a['User']['name']) ? $a['User']['name'] : (!empty($a['User']['username']) ? $a['User']['username'] : 'Agent #'.$a['FacebookAgentAssignment']['user_id']);
                            $agencyName = !empty($userAgencyMap[$assignmentId]) ? $userAgencyMap[$assignmentId] : 'White Villas';
                            $targetProjectId = !empty($a['FacebookAgentAssignment']['project_id']) ? (int)$a['FacebookAgentAssignment']['project_id'] : 0;
                            $targetProjectName = !empty($projects[$targetProjectId]) ? $projects[$targetProjectId] : ($targetProjectId > 0 ? 'Project #'.$targetProjectId : 'All Projects');
                        ?>
                        <tr>
                            <td style="text-align:center;">
                                <span class="badge" style="background:#0284c7;font-size:14px;padding:6px 12px;border-radius:20px;">
                                    #<?php echo (int)$a['FacebookAgentAssignment']['sort_order'];?>
                                </span>
                            </td>
                            <td>
                                <div style="display:flex;align-items:flex-start;gap:8px;">
                                    <i class="fa fa-user-circle text-primary fa-2x" style="margin-top:2px;"></i>
                                    <div>
                                        <strong style="font-size:14px;color:#0f172a;"><?php echo h($agentName);?></strong>
                                        <div style="margin-top:3px;">
                                            <span class="label label-default" style="font-size:11px;background:#e2e8f0;color:#334155;border:1px solid #cbd5e1;padding:3px 7px;">
                                                <i class="fa fa-briefcase text-primary"></i> Agency: <?php echo h($agencyName);?>
                                            </span>
                                        </div>
                                        <?php if(!empty($a['User']['email'])){ ?>
                                            <div style="font-size:11px;color:#64748b;margin-top:2px;"><i class="fa fa-envelope-o"></i> <?php echo h($a['User']['email']);?></div>
                                        <?php } ?>
                                        <?php if(!empty($a['User']['mobile'])){ ?>
                                            <div style="font-size:11px;color:#64748b;margin-top:1px;"><i class="fa fa-phone"></i> <?php echo h($a['User']['mobile']);?></div>
                                        <?php } ?>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php if($targetProjectId > 0){ ?>
                                    <span class="label label-primary" style="font-size:11px;padding:4px 8px;">
                                        <i class="fa fa-building-o"></i> <?php echo h($targetProjectName);?>
                                    </span>
                                <?php } else { ?>
                                    <span class="label label-default" style="font-size:11px;background:#f1f5f9;color:#475569;border:1px solid #cbd5e1;padding:4px 8px;">
                                        <i class="fa fa-globe text-primary"></i> <?php echo __('All Projects');?>
                                    </span>
                                <?php } ?>
                            </td>
                            <td>
                                <span class="badge badge-info" style="font-size:13px;padding:6px 12px;background:#0284c7;">
                                    <i class="fa fa-user-plus"></i> <?php echo (int)$a['FacebookAgentAssignment']['total_assigned_count'];?> leads
                                </span>
                            </td>
                            <td style="font-size:12px;">
                                <?php echo !empty($a['FacebookAgentAssignment']['last_assigned_at']) ? h($a['FacebookAgentAssignment']['last_assigned_at']) : '<span class="text-muted"><em>'.__('Never').'</em></span>';?>
                            </td>
                            <td>
                                <?php if(!empty($a['FacebookAgentAssignment']['is_active'])){ ?>
                                    <span class="label label-success" style="font-size:11px;padding:5px 9px;"><i class="fa fa-check"></i> <?php echo __('Active in Loop');?></span>
                                <?php } else { ?>
                                    <span class="label label-danger" style="font-size:11px;padding:5px 9px;"><i class="fa fa-pause"></i> <?php echo __('Paused');?></span>
                                <?php } ?>
                            </td>
                            <td>
                                <div style="display:flex;gap:4px;flex-wrap:wrap;align-items:center;">
                                    <!-- Toggle Active / Pause -->
                                    <?php echo $this->Form->create('AgentToggle', array('style'=>'display:inline;margin:0;'));?>
                                    <?php echo $this->Form->hidden('AgentToggle.id', array('value'=>$assignmentId));?>
                                    <?php echo $this->Form->hidden('AgentToggle.is_active', array('value'=>!empty($a['FacebookAgentAssignment']['is_active']) ? 0 : 1));?>
                                    <?php if(!empty($a['FacebookAgentAssignment']['is_active'])){ ?>
                                        <button type="submit" class="btn btn-xs btn-warning" title="<?php echo __('Pause this agent from receiving leads');?>">
                                            <i class="fa fa-pause"></i> <?php echo __('Pause');?>
                                        </button>
                                    <?php } else { ?>
                                        <button type="submit" class="btn btn-xs btn-success" title="<?php echo __('Activate this agent in rotation loop');?>">
                                            <i class="fa fa-play"></i> <?php echo __('Activate');?>
                                        </button>
                                    <?php } ?>
                                    <?php echo $this->Form->end();?>

                                    <!-- Edit Button (Opens Modal) -->
                                    <button type="button" class="btn btn-xs btn-info js-edit-agent-btn" 
                                            data-id="<?php echo $assignmentId;?>" 
                                            data-name="<?php echo h($agentName);?>"
                                            data-agency="<?php echo h($agencyName);?>"
                                            data-sort="<?php echo (int)$a['FacebookAgentAssignment']['sort_order'];?>" 
                                            data-project="<?php echo $targetProjectId;?>" 
                                            data-active="<?php echo !empty($a['FacebookAgentAssignment']['is_active']) ? 1 : 0;?>"
                                            title="<?php echo __('Edit queue order, project filter, and status');?>">
                                        <i class="fa fa-pencil"></i> <?php echo __('Edit');?>
                                    </button>

                                    <!-- Remove / Delete Button -->
                                    <?php echo $this->Html->link('<i class="fa fa-trash"></i> '.__('Remove'), 
                                        array('action'=>'delete_agent', $assignmentId), 
                                        array(
                                            'class'=>'btn btn-xs btn-danger', 
                                            'escape'=>false, 
                                            'title'=>__('Remove this agent from the rotation pool'),
                                            'onclick'=>"return confirm('".__('Are you sure you want to remove this agent from the rotation loop?')."');"
                                        )
                                    );?>
                                </div>
                            </td>
                        </tr>
                        <?php }} else { ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted" style="padding:40px;">
                                <i class="fa fa-users fa-3x" style="color:#94a3b8;margin-bottom:12px;"></i><br/>
                                <h4 style="color:#64748b;"><?php echo __('No agents in the round-robin looping pool yet.');?></h4>
                                <p style="font-size:13px;color:#94a3b8;"><?php echo __('Click the button below to add your active registered sales agents to the round-robin distribution.');?></p>
                                <button type="button" class="btn btn-success mt-2" data-toggle="modal" data-target="#addAgentModal">
                                    <i class="fa fa-user-plus"></i> <?php echo __('Add First Agent to Rotation Pool');?>
                                </button>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ================= MODAL: ADD AGENT TO ROTATION POOL ================= -->
<div class="modal fade" id="addAgentModal" tabindex="-1" role="dialog" aria-labelledby="addAgentModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="border-radius:12px;overflow:hidden;">
            <?php echo $this->Form->create('AddAgent');?>
            <div class="modal-header" style="background:#10b981;color:#fff;padding:16px 20px;">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:#fff;opacity:0.9;">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="addAgentModalLabel" style="font-weight:800;color:#fff;">
                    <i class="fa fa-user-plus"></i> <?php echo __('Add Agent to Round-Robin Looping Pool');?>
                </h4>
            </div>

            <div class="modal-body" style="padding:20px;">
                <div class="form-group">
                    <label><strong><?php echo __('Select Agent (with Agency):');?> *</strong></label>
                    <?php echo $this->Form->select('AddAgent.user_id', $allUsers, array('empty'=>__('-- Choose Active Agent / User --'), 'class'=>'form-control', 'required'=>true));?>
                    <p class="help-block" style="font-size:11px;"><?php echo __('Only active registered agents from your database are listed.');?></p>
                </div>

                <div class="form-group">
                    <label><strong><?php echo __('Target Project Filter:');?></strong></label>
                    <?php echo $this->Form->select('AddAgent.project_id', $projects, array('empty'=>__('All Projects (Default)'), 'class'=>'form-control'));?>
                    <p class="help-block" style="font-size:11px;"><?php echo __('Leave as All Projects to receive leads from any campaign.');?></p>
                </div>

                <div class="form-group">
                    <label><strong><?php echo __('Loop Priority / Queue Order (Sort ID):');?></strong></label>
                    <?php $nextSort = count($assignments) + 1;?>
                    <?php echo $this->Form->input('AddAgent.sort_order', array('type'=>'number', 'label'=>false, 'class'=>'form-control', 'value'=>$nextSort, 'min'=>1));?>
                    <p class="help-block" style="font-size:11px;"><?php echo __('Lower numbers are prioritized earlier in the round-robin rotation sequence.');?></p>
                </div>
            </div>

            <div class="modal-footer" style="background:#f8fafc;padding:14px 20px;">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Cancel');?></button>
                <button type="submit" class="btn btn-success" style="font-weight:700;"><i class="fa fa-plus"></i> <?php echo __('Add to Rotation');?></button>
            </div>
            <?php echo $this->Form->end();?>
        </div>
    </div>
</div>

<!-- ================= MODAL: EDIT AGENT IN ROTATION POOL ================= -->
<div class="modal fade" id="editAgentModal" tabindex="-1" role="dialog" aria-labelledby="editAgentModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="border-radius:12px;overflow:hidden;">
            <?php echo $this->Form->create('EditAgent');?>
            <?php echo $this->Form->hidden('EditAgent.id', array('id'=>'edit_agent_id'));?>
            <div class="modal-header" style="background:#0284c7;color:#fff;padding:16px 20px;">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:#fff;opacity:0.9;">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="editAgentModalLabel" style="font-weight:800;color:#fff;">
                    <i class="fa fa-pencil-square-o"></i> <?php echo __('Edit Looping Agent Settings');?>
                </h4>
            </div>

            <div class="modal-body" style="padding:20px;">
                <div class="alert alert-info" style="margin-bottom:15px;padding:10px 14px;border-radius:8px;">
                    <i class="fa fa-user"></i> <strong id="edit_agent_name_display">Agent</strong> &nbsp;|&nbsp; 
                    <i class="fa fa-briefcase"></i> <span id="edit_agent_agency_display">Agency</span>
                </div>

                <div class="form-group">
                    <label><strong><?php echo __('Queue Order / Sort ID:');?> *</strong></label>
                    <?php echo $this->Form->input('EditAgent.sort_order', array('type'=>'number', 'label'=>false, 'class'=>'form-control', 'id'=>'edit_agent_sort', 'required'=>true, 'min'=>1));?>
                    <p class="help-block" style="font-size:11px;"><?php echo __('Set this agent\'s exact position in the round-robin rotation sequence (e.g. 1, 2, 3...).');?></p>
                </div>

                <div class="form-group">
                    <label><strong><?php echo __('Target Project Filter:');?></strong></label>
                    <?php echo $this->Form->select('EditAgent.project_id', $projects, array('empty'=>__('All Projects'), 'class'=>'form-control', 'id'=>'edit_agent_project'));?>
                    <p class="help-block" style="font-size:11px;"><?php echo __('Select a specific project or leave as All Projects.');?></p>
                </div>

                <div class="form-group">
                    <label><strong><?php echo __('Queue Status:');?></strong></label>
                    <?php echo $this->Form->select('EditAgent.is_active', array(1=>__('Active in Loop'), 0=>__('Paused')), array('class'=>'form-control', 'id'=>'edit_agent_active', 'empty'=>false));?>
                </div>
            </div>

            <div class="modal-footer" style="background:#f8fafc;padding:14px 20px;">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Cancel');?></button>
                <button type="submit" class="btn btn-primary" style="font-weight:700;"><i class="fa fa-check"></i> <?php echo __('Save Changes');?></button>
            </div>
            <?php echo $this->Form->end();?>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('.js-edit-agent-btn').on('click', function(e) {
        e.preventDefault();
        var aId = $(this).data('id');
        var aName = $(this).data('name');
        var aAgency = $(this).data('agency');
        var aSort = $(this).data('sort');
        var aProj = $(this).data('project');
        var aActive = $(this).data('active');

        $('#edit_agent_id').val(aId);
        $('#edit_agent_name_display').text(aName);
        $('#edit_agent_agency_display').text(aAgency);
        $('#edit_agent_sort').val(aSort);
        $('#edit_agent_project').val(aProj ? aProj : '');
        $('#edit_agent_active').val(aActive);

        $('#editAgentModal').modal('show');
    });
});
</script>