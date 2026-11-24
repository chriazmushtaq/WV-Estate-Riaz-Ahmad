<?php
if(empty($allAgents)) {
    $rawUsers = ClassRegistry::init('User')->find('all', array(
        'conditions' => array('User.status' => 'Active'),
        'fields' => array('User.id', 'User.name', 'User.project_id'),
        'order' => array('User.name' => 'ASC'),
        'recursive' => -1
    ));
    $rawProjects = ClassRegistry::init('Project')->find('list', array('fields' => array('Project.id', 'Project.name')));
    $allAgents = array();
    foreach($rawUsers as $ru) {
        $uId = (int)$ru['User']['id'];
        $uName = trim((string)$ru['User']['name']);
        $pId = !empty($ru['User']['project_id']) ? (int)$ru['User']['project_id'] : 0;
        $agName = !empty($rawProjects[$pId]) ? $rawProjects[$pId] : 'White Villas';
        $allAgents[$uId] = $uName . ' | ' . $agName;
    }
}
$selectedAgentFilter = isset($selectedAgentFilter) ? (int)$selectedAgentFilter : 0;
$agentLeadCounts = isset($agentLeadCounts) && is_array($agentLeadCounts) ? $agentLeadCounts : array();
?>
<div class="page-title">
    <div class="title-env">
        <h1 class="title"><i class="fa fa-facebook-square text-primary"></i> <?php echo __('Facebook CRM Integration Overview');?></h1>
        <p class="description"><?php echo __('Automated Lead Ingestion, Webhooks & 1-by-1 Looping Agent Assignment');?></p>
    </div>
</div>

<div class="panel">
    <div class="panel-heading" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
        <div class="btn-group" style="display:flex;flex-wrap:wrap;gap:4px;">
            <?php echo $this->Html->link('<i class="fa fa-dashboard"></i> '.__('Overview'), array('action'=>'index'), array('class'=>'btn btn-primary', 'escape'=>false));?>
            <?php echo $this->Html->link('<i class="fa fa-cogs"></i> '.__('API Accounts'), array('action'=>'settings'), array('class'=>'btn btn-default', 'escape'=>false));?>
            <?php echo $this->Html->link('<i class="fa fa-users"></i> '.__('Agent Looper'), array('action'=>'agents'), array('class'=>'btn btn-default', 'escape'=>false));?>
            <?php echo $this->Html->link('<i class="fa fa-list-alt"></i> '.__('Form Mapping'), array('action'=>'forms'), array('class'=>'btn btn-default', 'escape'=>false));?>
            <?php echo $this->Html->link('<i class="fa fa-bell"></i> '.__('Notifications'), array('action'=>'notifications'), array('class'=>'btn btn-default', 'escape'=>false));?>
            <?php echo $this->Html->link('<i class="fa fa-whatsapp"></i> '.__('WhatsApp API'), array('action'=>'whatsapp'), array('class'=>'btn btn-default', 'escape'=>false));?>
            <?php echo $this->Html->link('<i class="fa fa-history"></i> '.__('Sync Logs'), array('action'=>'logs'), array('class'=>'btn btn-default', 'escape'=>false));?>
            <?php echo $this->Html->link('<i class="fa fa-book"></i> '.__('Documentation'), array('action'=>'docs'), array('class'=>'btn btn-default', 'escape'=>false));?>
        </div>

        <div>
            <?php echo $this->Html->link('<i class="fa fa-refresh fa-spin-hover"></i> '.__('Sync All Accounts Now'), array('action'=>'sync_now'), array('class'=>'btn btn-success', 'escape'=>false, 'style'=>'font-weight:700;'));?>
        </div>
    </div>

    <div class="panel-body">
        <?php echo $this->Session->flash();?>

        <!-- KPI Cards Grid -->
        <div class="row" style="margin-bottom:24px;">
            <div class="col-md-3 col-sm-6">
                <div style="background:#1877f2;color:#fff;border-radius:12px;padding:20px;box-shadow:0 4px 14px rgba(24,119,242,0.25);">
                    <div style="font-size:12px;text-transform:uppercase;font-weight:800;letter-spacing:.5px;"><?php echo __('Total Facebook Leads');?></div>
                    <div style="font-size:32px;font-weight:900;margin-top:4px;"><?php echo (int)$totalFbLeads;?></div>
                    <div style="font-size:11px;opacity:0.9;margin-top:4px;"><i class="fa fa-check-circle"></i> Ingested into CRM Leads</div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6">
                <div style="background:#10b981;color:#fff;border-radius:12px;padding:20px;box-shadow:0 4px 14px rgba(16,185,129,0.25);">
                    <div style="font-size:12px;text-transform:uppercase;font-weight:800;letter-spacing:.5px;"><?php echo __('Today\'s FB Leads');?></div>
                    <div style="font-size:32px;font-weight:900;margin-top:4px;"><?php echo (int)$todayFbLeads;?></div>
                    <div style="font-size:11px;opacity:0.9;margin-top:4px;"><i class="fa fa-calendar"></i> <?php echo date('d-M-Y');?></div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6">
                <div style="background:#8b5cf6;color:#fff;border-radius:12px;padding:20px;box-shadow:0 4px 14px rgba(139,92,246,0.25);">
                    <div style="font-size:12px;text-transform:uppercase;font-weight:800;letter-spacing:.5px;"><?php echo __('Active Looper Agents');?></div>
                    <div style="font-size:32px;font-weight:900;margin-top:4px;"><?php echo (int)$activeAgentsCount;?></div>
                    <div style="font-size:11px;opacity:0.9;margin-top:4px;"><i class="fa fa-refresh"></i> 1-by-1 Round-Robin Rotation</div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6">
                <div style="background:#0f172a;color:#fff;border-radius:12px;padding:20px;box-shadow:0 4px 14px rgba(15,23,42,0.25);">
                    <div style="font-size:12px;text-transform:uppercase;font-weight:800;letter-spacing:.5px;"><?php echo __('Connected Pages');?></div>
                    <div style="font-size:32px;font-weight:900;margin-top:4px;"><?php echo (int)$activeAccountsCount;?></div>
                    <div style="font-size:11px;opacity:0.9;margin-top:4px;"><i class="fa fa-check"></i> Active & Syncing</div>
                </div>
            </div>
        </div>

        <!-- Assigned Agents Breakdown / 1-Click Quick Filter Bar -->
        <div class="panel panel-default" style="border-radius:10px;margin-bottom:20px;box-shadow:0 2px 8px rgba(0,0,0,0.04);background:#ffffff;">
            <div class="panel-body" style="padding:14px 18px;">
                <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:10px;">
                    <div style="font-weight:800;font-size:13px;color:#1e293b;text-transform:uppercase;letter-spacing:0.5px;display:flex;align-items:center;gap:6px;">
                        <i class="fa fa-users text-primary"></i> <?php echo __('Leads by Assigned Agent:');?>
                    </div>
                    <div style="font-size:11px;color:#64748b;font-weight:600;">
                        <i class="fa fa-mouse-pointer"></i> <?php echo __('Click any agent pill below to filter preview table instantly:');?>
                    </div>
                </div>
                <div id="agentPillsContainer" style="display:flex;flex-wrap:wrap;gap:8px;">
                    <button type="button" class="btn btn-sm btn-filter-agent active" data-agent-id="all" style="border-radius:20px;font-weight:700;padding:6px 14px;border:1.5px solid #1877f2;background:#1877f2;color:#fff;cursor:pointer;transition:all .15s ease;">
                        <i class="fa fa-globe"></i> <?php echo __('All Agents');?> 
                        <span class="badge" style="background:#fff;color:#1877f2;margin-left:4px;"><?php echo count($recentLogs);?></span>
                    </button>
                    <?php
                    if(!empty($allAgents)) {
                        foreach($allAgents as $agentId => $agentLabel) {
                            $leadCount = !empty($agentLeadCounts[$agentId]) ? (int)$agentLeadCounts[$agentId] : 0;
                            if($leadCount > 0 || (!empty($selectedAgentFilter) && (int)$selectedAgentFilter === (int)$agentId)) {
                                $isActive = (!empty($selectedAgentFilter) && (int)$selectedAgentFilter === (int)$agentId);
                    ?>
                        <button type="button" class="btn btn-sm btn-filter-agent <?php echo $isActive ? 'active' : '';?>" 
                                data-agent-id="<?php echo (int)$agentId;?>" 
                                style="border-radius:20px;font-weight:700;padding:6px 14px;border:1.5px solid <?php echo $isActive ? '#1877f2' : '#cbd5e1';?>;background:<?php echo $isActive ? '#1877f2' : '#f8fafc';?>;color:<?php echo $isActive ? '#fff' : '#334155';?>;cursor:pointer;transition:all .15s ease;">
                            <i class="fa fa-user"></i> <?php echo h($agentLabel);?> 
                            <span class="badge" style="background:<?php echo $isActive ? '#fff' : '#e2e8f0';?>;color:<?php echo $isActive ? '#1877f2' : '#0f172a';?>;margin-left:4px;">
                                <?php echo $leadCount;?>
                            </span>
                        </button>
                    <?php 
                            }
                        }
                    }
                    $unassignedCount = !empty($agentLeadCounts[0]) ? (int)$agentLeadCounts[0] : 0;
                    if($unassignedCount > 0) {
                    ?>
                        <button type="button" class="btn btn-sm btn-filter-agent" data-agent-id="0" style="border-radius:20px;font-weight:700;padding:6px 14px;border:1.5px solid #f59e0b;background:#fffbeb;color:#b45309;cursor:pointer;">
                            <i class="fa fa-question-circle"></i> <?php echo __('Unassigned');?> 
                            <span class="badge" style="background:#f59e0b;color:#fff;margin-left:4px;"><?php echo $unassignedCount;?></span>
                        </button>
                    <?php } ?>
                </div>
            </div>
        </div>

        <!-- Recent Ingested Leads Table with Full-Width 1-Click Agent Dropdown -->
        <div class="panel panel-default" style="border-radius:10px;overflow:hidden;box-shadow:0 2px 10px rgba(0,0,0,0.05);">
            <div class="panel-heading" style="background:#f8fafc;font-weight:700;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;padding:14px 18px;">
                <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                    <span style="font-size:15px;font-weight:800;color:#0f172a;"><i class="fa fa-list text-primary"></i> <?php echo __('All Ingested Facebook Leads & Real-Time Agent Assignment');?></span>
                    <span id="visibleCountBadge" class="badge" style="background:#1877f2;color:#fff;font-size:12px;padding:5px 12px;border-radius:14px;font-weight:800;">
                        <?php echo count($recentLogs);?> <?php echo __('Total Leads');?>
                    </span>
                </div>
                <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                    <!-- Filter by Agent Dropdown -->
                    <div style="display:flex;align-items:center;gap:6px;">
                        <label for="fbAgentFilterSelect" style="margin-bottom:0;font-size:12px;font-weight:700;color:#64748b;white-space:nowrap;"><?php echo __('Agent:');?></label>
                        <select id="fbAgentFilterSelect" class="form-control input-sm" style="width:200px;font-size:12px;font-weight:700;border-radius:6px;background:#fff;color:#0f172a;">
                            <option value="all"><?php echo __('All Assigned Agents');?> (<?php echo count($recentLogs);?>)</option>
                            <?php if(!empty($allAgents)){ foreach($allAgents as $aId => $aLabel){ 
                                $cnt = !empty($agentLeadCounts[$aId]) ? (int)$agentLeadCounts[$aId] : 0;
                            ?>
                                <option value="<?php echo (int)$aId;?>" <?php echo (!empty($selectedAgentFilter) && (int)$selectedAgentFilter === (int)$aId) ? 'selected' : '';?>>
                                    <?php echo h($aLabel);?> (<?php echo $cnt;?>)
                                </option>
                            <?php }} ?>
                            <?php if(!empty($agentLeadCounts[0])){ ?>
                                <option value="0"><?php echo __('-- Unassigned --');?> (<?php echo (int)$agentLeadCounts[0];?>)</option>
                            <?php } ?>
                        </select>
                    </div>

                    <!-- Quick search box -->
                    <div class="input-group" style="width:240px;">
                        <input type="text" id="fbLeadsFilterInput" class="form-control input-sm" placeholder="<?php echo __('Search name, phone, form...');?>" style="border-radius:6px 0 0 6px;">
                        <span class="input-group-addon" style="border-radius:0 6px 6px 0;background:#f1f5f9;"><i class="fa fa-search text-muted"></i></span>
                    </div>

                    <!-- Reset Filters Button -->
                    <button type="button" id="btnResetFilters" class="btn btn-sm btn-default" title="<?php echo __('Reset all filters');?>" style="border-radius:6px;font-weight:600;">
                        <i class="fa fa-undo"></i>
                    </button>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-striped table-bordered table-hover" style="margin-bottom:0;vertical-align:middle;">
                    <thead>
                        <tr style="background:#f1f5f9;color:#334155;">
                            <th><?php echo __('#');?></th>
                            <th><?php echo __('Lead Name');?></th>
                            <th><?php echo __('Mobile / Phone');?></th>
                            <th><?php echo __('Email');?></th>
                            <th><?php echo __('City');?></th>
                            <th style="min-width:220px;"><?php echo __('Assigned Agent (Change to Auto-Save)');?></th>
                            <th><?php echo __('Form Name');?></th>
                            <th><?php echo __('Status');?></th>
                            <th><?php echo __('Date & Time');?></th>
                            <th style="width:70px;text-align:center;"><?php echo __('View');?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $serialNo = !empty($recentLogs) ? count($recentLogs) : 0;
                        if(!empty($recentLogs)){ foreach($recentLogs as $log){ 
                            $logId = (int)$log['FacebookLeadLog']['id'];
                            $leadId = !empty($log['FacebookLeadLog']['lead_id']) ? (int)$log['FacebookLeadLog']['lead_id'] : 0;
                            $currentUserId = !empty($log['FacebookLeadLog']['assigned_user_id']) ? (int)$log['FacebookLeadLog']['assigned_user_id'] : 0;
                        ?>
                        <tr id="log-row-<?php echo $logId;?>" class="fb-lead-row" data-agent-id="<?php echo $currentUserId;?>" data-lead-id="<?php echo $leadId;?>">
                            <td style="text-align:center;font-weight:700;color:#64748b;"><?php echo $serialNo--;?></td>
                            <td><strong style="color:#0f172a;font-size:13px;"><?php echo h($log['FacebookLeadLog']['lead_name']);?></strong></td>
                            <td>
                                <?php if(!empty($log['FacebookLeadLog']['lead_mobile'])){ ?>
                                    <a href="tel:<?php echo h($log['FacebookLeadLog']['lead_mobile']);?>" style="font-weight:700;color:#0284c7;">
                                        <i class="fa fa-phone"></i> <?php echo h($log['FacebookLeadLog']['lead_mobile']);?>
                                    </a>
                                <?php } else { echo '-'; } ?>
                            </td>
                            <td><?php echo h(!empty($log['FacebookLeadLog']['lead_email']) ? $log['FacebookLeadLog']['lead_email'] : '-');?></td>
                            <td><?php echo h(!empty($log['FacebookLeadLog']['lead_city']) ? $log['FacebookLeadLog']['lead_city'] : '-');?></td>
                            
                            <!-- Full-Width 1-Click Interactive Agent Dropdown -->
                            <td style="min-width:240px;">
                                <div style="display:flex;align-items:center;gap:6px;">
                                    <select class="form-control select-fb-agent" 
                                            data-log-id="<?php echo $logId;?>" 
                                            data-lead-id="<?php echo $leadId;?>" 
                                            style="width:100%;font-size:12px;font-weight:700;height:36px;border-radius:8px;border:1.5px solid <?php echo ($currentUserId > 0) ? '#93c5fd' : '#cbd5e1';?>;background:<?php echo ($currentUserId > 0) ? '#f0f9ff' : '#fff';?>;color:#0f172a;cursor:pointer;">
                                        <option value="0" <?php echo ($currentUserId === 0) ? 'selected' : '';?> style="color:#64748b;font-weight:normal;">-- <?php echo __('Unassigned');?> --</option>
                                        <?php if(!empty($allAgents)){ foreach($allAgents as $uId => $uName){ ?>
                                            <option value="<?php echo (int)$uId;?>" <?php echo ($currentUserId === (int)$uId) ? 'selected' : '';?> style="color:#0f172a;font-weight:600;">
                                                <?php echo h($uName);?>
                                            </option>
                                        <?php }} ?>
                                    </select>
                                    <span class="save-indicator" id="save-status-<?php echo $logId;?>" style="display:none;font-size:11px;font-weight:800;color:#10b981;white-space:nowrap;">
                                        <i class="fa fa-check-circle"></i> Saved
                                    </span>
                                </div>
                            </td>

                            <td><span class="badge" style="background:#f1f5f9;color:#334155;border:1px solid #cbd5e1;"><?php echo h(!empty($log['FacebookLeadLog']['form_name']) ? $log['FacebookLeadLog']['form_name'] : 'Facebook Lead Ads');?></span></td>
                            <td>
                                <?php if($log['FacebookLeadLog']['status'] === 'Success'){ ?>
                                    <span class="label label-success"><i class="fa fa-check"></i> <?php echo __('Success');?></span>
                                <?php } elseif($log['FacebookLeadLog']['status'] === 'Duplicate') { ?>
                                    <span class="label label-warning"><?php echo __('Duplicate');?></span>
                                <?php } else { ?>
                                    <span class="label label-danger"><?php echo __('Error');?></span>
                                <?php } ?>
                            </td>
                            <td style="font-size:12px;color:#334155;white-space:nowrap;">
                                <?php
                                if(!empty($log['FacebookLeadLog']['created']) && $log['FacebookLeadLog']['created'] !== '0000-00-00 00:00:00') {
                                    $logCreatedFmt = (!empty($sysDay) && !empty($sysMonth) && !empty($sysYear))
                                        ? $this->Time->format($sysDay.$dateSep.$sysMonth.$dateSep.$sysYear.$dateGap.$sysHour.$timeSep.$sysMin.$dateGap.$sysMer, $log['FacebookLeadLog']['created'])
                                        : date('d-M-Y h:i A', strtotime($log['FacebookLeadLog']['created']));
                                    echo h($logCreatedFmt);
                                } else {
                                    echo '-';
                                }
                                ?>
                            </td>
                            <td style="text-align:center;">
                                <?php if($leadId > 0){ ?>
                                    <?php echo $this->Html->link('<i class="fa fa-eye"></i>', array('controller'=>'Leads', 'action'=>'leadview', $leadId), array('class'=>'btn btn-xs btn-primary', 'escape'=>false, 'title'=>__('View CRM Lead')));?>
                                <?php } ?>
                            </td>
                        </tr>
                        <?php }} ?>

                        <!-- Empty state if no records in database -->
                        <?php if(empty($recentLogs)){ ?>
                        <tr>
                            <td colspan="10" class="text-center text-muted" style="padding:30px;">
                                <i class="fa fa-info-circle fa-2x" style="color:#94a3b8;margin-bottom:8px;"></i><br/>
                                <?php echo __('No Facebook leads ingested yet. Click "Sync All Accounts Now" to fetch live leads.');?>
                            </td>
                        </tr>
                        <?php } ?>

                        <!-- Filter empty state -->
                        <tr id="noMatchingLeadsRow" style="display:none;">
                            <td colspan="10" class="text-center text-muted" style="padding:35px;">
                                <i class="fa fa-search fa-2x" style="color:#94a3b8;margin-bottom:8px;"></i><br/>
                                <strong style="color:#475569;font-size:14px;"><?php echo __('No leads found matching the selected agent or search criteria.');?></strong><br/>
                                <button type="button" class="btn btn-xs btn-primary" onclick="$('#btnResetFilters').click();" style="margin-top:10px;">
                                    <i class="fa fa-refresh"></i> <?php echo __('Reset Filter & Show All Leads');?>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function() {
    function filterFbLeadsTable() {
        var selectedAgent = $('#fbAgentFilterSelect').val();
        var query = $('#fbLeadsFilterInput').val().toLowerCase().trim();
        var visibleCount = 0;

        $('.fb-lead-row').each(function() {
            var rowAgentId = $(this).attr('data-agent-id') || '0';
            var rowText = $(this).text().toLowerCase();

            var agentMatch = (selectedAgent === 'all' || selectedAgent === rowAgentId);
            var textMatch = (query === '' || rowText.indexOf(query) > -1);

            if (agentMatch && textMatch) {
                $(this).show();
                visibleCount++;
            } else {
                $(this).hide();
            }
        });

        $('#visibleCountBadge').text(visibleCount + ' Leads');
        if (visibleCount === 0 && $('.fb-lead-row').length > 0) {
            $('#noMatchingLeadsRow').show();
        } else {
            $('#noMatchingLeadsRow').hide();
        }
    }

    // Agent dropdown change
    $('#fbAgentFilterSelect').on('change', function() {
        var agentId = $(this).val();
        $('.btn-filter-agent').removeClass('active').css({'background':'#f8fafc', 'color':'#334155', 'border-color':'#cbd5e1'});
        var matchedPill = $('.btn-filter-agent[data-agent-id="' + agentId + '"]');
        if (matchedPill.length) {
            matchedPill.addClass('active').css({'background':'#1877f2', 'color':'#fff', 'border-color':'#1877f2'});
        }
        filterFbLeadsTable();
    });

    // Search box keyup
    $('#fbLeadsFilterInput').on('keyup', function() {
        filterFbLeadsTable();
    });

    // Agent pill click
    $(document).on('click', '.btn-filter-agent', function(e) {
        e.preventDefault();
        var agentId = $(this).attr('data-agent-id');
        $('#fbAgentFilterSelect').val(agentId);
        $('.btn-filter-agent').removeClass('active').css({'background':'#f8fafc', 'color':'#334155', 'border-color':'#cbd5e1'});
        $(this).addClass('active').css({'background':'#1877f2', 'color':'#fff', 'border-color':'#1877f2'});
        filterFbLeadsTable();
    });

    // Reset button
    $('#btnResetFilters').on('click', function() {
        $('#fbAgentFilterSelect').val('all');
        $('#fbLeadsFilterInput').val('');
        $('.btn-filter-agent').removeClass('active').css({'background':'#f8fafc', 'color':'#334155', 'border-color':'#cbd5e1'});
        $('.btn-filter-agent[data-agent-id="all"]').addClass('active').css({'background':'#1877f2', 'color':'#fff', 'border-color':'#1877f2'});
        filterFbLeadsTable();
    });

    // Row agent dropdown change (AJAX auto-save)
    $(document).on('change', '.select-fb-agent', function() {
        var selectEl = $(this);
        var logId = selectEl.attr('data-log-id');
        var leadId = selectEl.attr('data-lead-id');
        var newUserId = selectEl.val();
        var indicator = $('#save-status-' + logId);
        var trRow = $('#log-row-' + logId);

        selectEl.css({'border-color': '#3b82f6', 'background-color': '#eff6ff'});

        $.ajax({
            url: '<?php echo $this->Html->url(array("plugin" => "admin", "controller" => "facebook_integrations", "action" => "reassign_agent_ajax")); ?>',
            type: 'POST',
            data: {
                log_id: logId,
                lead_id: leadId,
                user_id: newUserId
            },
            dataType: 'json',
            success: function(response) {
                if (response && response.success) {
                    trRow.attr('data-agent-id', newUserId);
                    if (parseInt(newUserId) > 0) {
                        selectEl.css({'border-color': '#93c5fd', 'background-color': '#f0f9ff'});
                    } else {
                        selectEl.css({'border-color': '#cbd5e1', 'background-color': '#fff'});
                    }
                    indicator.fadeIn(200).delay(1500).fadeOut(400);

                    // Re-apply filter in case active agent filter hides this row
                    var activeFilter = $('#fbAgentFilterSelect').val();
                    if (activeFilter !== 'all' && activeFilter !== newUserId) {
                        trRow.fadeOut(300, function() {
                            filterFbLeadsTable();
                        });
                    }
                } else {
                    selectEl.css({'border-color': '#ef4444', 'background-color': '#fef2f2'});
                    alert(response.message || 'Could not update agent.');
                }
            },
            error: function() {
                selectEl.css({'border-color': '#ef4444', 'background-color': '#fef2f2'});
                alert('Connection error while updating agent.');
            }
        });
    });
});
</script>