<?php
$agencyOptions = isset($agencyOptions) ? (array)$agencyOptions : array();
$selectedAgencyId = isset($selectedAgencyId) ? (int)$selectedAgencyId : 0;
$userMeta = isset($userMeta) ? (array)$userMeta : array();
$officerProjectMap = isset($officerProjectMap) ? (array)$officerProjectMap : array();
$statusOptions = array('Active' => __('Active'), 'Inactive' => __('Inactive'));
?>
<style type="text/css">
.wv-recovery-page{color:#06162b;}
.wv-recovery-head{align-items:center;background:linear-gradient(135deg,#07152a,#0d2747 58%,#087b63);border-radius:8px;color:#fff;display:flex;gap:12px;justify-content:space-between;margin-bottom:12px;padding:14px 16px;}
.wv-recovery-head h2{font-size:21px;font-weight:900;margin:0;}
.wv-recovery-head p{color:#cde4f4;font-size:12px;font-weight:700;margin:3px 0 0;}
.wv-recovery-head .btn{border-radius:7px;font-weight:900;margin-left:5px;}
.wv-recovery-card{background:#fff;border:1px solid #dbe4ee;border-radius:8px;box-shadow:0 10px 24px rgba(7,21,42,.06);padding:14px;}
.wv-recovery-toolbar{align-items:center;display:flex;gap:8px;justify-content:space-between;margin-bottom:12px;flex-wrap:wrap;}
.wv-recovery-filter{align-items:center;display:flex;gap:7px;margin:0;}
.wv-recovery-filter .form-control{height:34px;min-width:240px;}
.wv-recovery-table{margin:0;}
.wv-recovery-table th{background:#07152a;color:#fff;font-size:11px;text-transform:uppercase;white-space:nowrap;}
.wv-recovery-table td{font-size:12px;vertical-align:middle!important;}
.wv-officer-name{font-size:13px;font-weight:900;}
.wv-muted{color:#60758d;font-size:11px;font-weight:700;}
.wv-status{border-radius:999px;color:#fff;display:inline-block;font-size:10px;font-weight:900;line-height:1;padding:5px 8px;text-transform:uppercase;}
.wv-status-Active{background:#168a3a}.wv-status-Inactive{background:#6b7b8c}.wv-login-on{background:#e7f8ef;border:1px solid #b8ecd0;border-radius:7px;color:#07592a;display:inline-block;font-size:11px;font-weight:900;padding:5px 8px;}.wv-login-off{background:#f2f5f8;border:1px solid #dce5ef;border-radius:7px;color:#6b7b8c;display:inline-block;font-size:11px;font-weight:900;padding:5px 8px;}
.wv-icon-actions{display:flex;gap:6px;justify-content:flex-end;white-space:nowrap;}
.wv-icon-btn{align-items:center;border-radius:6px;display:inline-flex;height:30px;justify-content:center;width:32px;}
.wv-modal .modal-content{border:0;border-radius:10px;overflow:hidden;}
.wv-modal .modal-header{background:#07152a;color:#fff;}
.wv-modal .modal-title{font-weight:900;}
.wv-modal label{color:#273c55;font-size:11px;font-weight:900;text-transform:uppercase;}
.wv-modal .form-control{border:1px solid #c9d8e9;border-radius:7px;box-shadow:none;font-weight:800;min-height:36px;}
.wv-modal select[multiple].form-control{height:126px;}
.wv-modal .help-block{font-size:11px;font-weight:700;margin-bottom:0;}
.wv-login-box{background:#f8fbff;border:1px solid #dbe8f5;border-radius:8px;margin-top:8px;padding:10px;}
.wv-role-pill{background:#e9f6ff;border:1px solid #c5e5ff;border-radius:999px;color:#075072;display:inline-block;font-size:11px;font-weight:900;margin-top:4px;padding:5px 10px;}
@media(max-width:767px){.wv-recovery-head{align-items:stretch;flex-direction:column}.wv-recovery-toolbar{align-items:stretch;flex-direction:column}.wv-recovery-filter{align-items:stretch;flex-direction:column}.wv-recovery-filter .form-control{min-width:0;width:100%;}.wv-icon-actions{justify-content:flex-start;}}
</style>

<div class="wv-recovery-page">
    <?php echo $this->Session->flash(); ?>
    <div class="wv-recovery-head">
        <div>
            <h2><?php echo __('Recovery Officers'); ?></h2>
            <p><?php echo __('Create officer logins with Recovery Officer role. Access is limited to Dashboard and Payment Recovery.'); ?></p>
        </div>
        <div>
            <button type="button" class="btn btn-success btn-sm js-open-officer-modal" data-mode="add">
                <span class="fa fa-plus"></span> <?php echo __('Add Officer'); ?>
            </button>
            <?php echo $this->Html->link('<span class="fa fa-dashboard"></span> '.__('Dashboard'), array('action'=>'index'), array('escape'=>false,'class'=>'btn btn-default btn-sm')); ?>
            <?php echo $this->Html->link('<span class="fa fa-cog"></span> '.__('Settings'), array('action'=>'settings'), array('escape'=>false,'class'=>'btn btn-default btn-sm')); ?>
        </div>
    </div>

    <div class="wv-recovery-card">
        <div class="wv-recovery-toolbar">
            <strong><?php echo __('Officer List'); ?></strong>
            <form method="get" action="<?php echo $this->Html->url(array('controller'=>'Recoveries','action'=>'officers')); ?>" class="wv-recovery-filter">
                <?php echo $this->Form->select('project_id', $agencyOptions, array('empty'=>__('All Agencies'), 'value'=>$selectedAgencyId, 'class'=>'form-control input-sm')); ?>
                <button class="btn btn-primary btn-sm" type="submit"><span class="fa fa-search"></span> <?php echo __('Filter'); ?></button>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped wv-recovery-table">
                <thead>
                    <tr>
                        <th style="width:54px;"><?php echo __('#'); ?></th>
                        <th><?php echo __('Officer'); ?></th>
                        <th><?php echo __('Agency'); ?></th>
                        <th><?php echo __('Mobile'); ?></th>
                        <th><?php echo __('Email'); ?></th>
                        <th><?php echo __('Login / Role'); ?></th>
                        <th><?php echo __('Status'); ?></th>
                        <th class="text-right" style="width:105px;"><?php echo __('Action'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($officers)){ $s=1; foreach($officers as $row){
                        $officer = (array)$row['RecoveryOfficer'];
                        $officerId = !empty($officer['id']) ? (int)$officer['id'] : 0;
                        $projectId = !empty($officer['project_id']) ? (int)$officer['project_id'] : 0;
                        $userId = !empty($officer['user_id']) ? (int)$officer['user_id'] : 0;
                        $login = ($userId > 0 && !empty($userMeta[$userId])) ? $userMeta[$userId] : array();
                        $assignedProjectIds = !empty($officerProjectMap[$officerId]) ? array_map('intval', (array)$officerProjectMap[$officerId]) : array($projectId);
                        $agencyNames = array();
                        foreach($assignedProjectIds as $assignedProjectId){
                            if(isset($agencyOptions[$assignedProjectId])){
                                $agencyNames[] = $agencyOptions[$assignedProjectId];
                            } elseif($assignedProjectId > 0) {
                                $agencyNames[] = __('Agency ID').' '.$assignedProjectId;
                            }
                        }
                        $agencyName = !empty($agencyNames) ? implode(', ', $agencyNames) : '-';
                    ?>
                    <tr>
                        <td><?php echo $s++; ?></td>
                        <td>
                            <div class="wv-officer-name"><?php echo h($officer['name']); ?></div>
                            <div class="wv-muted"><?php echo __('Officer ID'); ?>: <?php echo $officerId; ?></div>
                        </td>
                        <td><?php echo h($agencyName); ?></td>
                        <td><?php echo h($officer['mobile']); ?></td>
                        <td><?php echo h($officer['email']); ?></td>
                        <td>
                            <?php if(!empty($login)){ ?>
                                <span class="wv-login-on"><span class="fa fa-user"></span> <?php echo h($login['username']); ?></span>
                                <div class="wv-muted"><?php echo h($login['role']); ?> | <?php echo h($login['status']); ?></div>
                            <?php } else { ?>
                                <span class="wv-login-off"><?php echo __('No Login'); ?></span>
                            <?php } ?>
                        </td>
                        <td><span class="wv-status wv-status-<?php echo h($officer['status']); ?>"><?php echo h($officer['status']); ?></span></td>
                        <td class="text-right">
                            <div class="wv-icon-actions">
                                <button type="button"
                                    class="btn btn-primary btn-xs wv-icon-btn js-open-officer-modal"
                                    title="<?php echo h(__('Edit')); ?>"
                                    data-mode="edit"
                                    data-id="<?php echo $officerId; ?>"
                                    data-project-id="<?php echo $projectId; ?>"
                                    data-project-ids="<?php echo h(implode(',', $assignedProjectIds)); ?>"
                                    data-user-id="<?php echo $userId; ?>"
                                    data-name="<?php echo h($officer['name']); ?>"
                                    data-mobile="<?php echo h($officer['mobile']); ?>"
                                    data-email="<?php echo h($officer['email']); ?>"
                                    data-username="<?php echo h(!empty($login['username']) ? $login['username'] : ''); ?>"
                                    data-status="<?php echo h($officer['status']); ?>">
                                    <span class="fa fa-pencil"></span>
                                </button>
                                <?php echo $this->Form->postLink('<span class="fa fa-trash"></span>', array('controller'=>'Recoveries','action'=>'deleteofficer',$officerId), array('escape'=>false,'class'=>'btn btn-danger btn-xs wv-icon-btn','title'=>__('Delete')), __('Delete this recovery officer?')); ?>
                            </div>
                        </td>
                    </tr>
                    <?php }} else { ?>
                    <tr><td colspan="8" class="text-center"><?php echo __('No recovery officers found.'); ?></td></tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade wv-modal" id="wvOfficerModal" tabindex="-1" role="dialog" aria-labelledby="wvOfficerModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="post" action="<?php echo $this->Html->url(array('controller'=>'Recoveries','action'=>'officers')); ?>" id="wvOfficerForm">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="<?php echo h(__('Close')); ?>"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="wvOfficerModalLabel"><?php echo __('Add Recovery Officer'); ?></h4>
                    <span class="wv-role-pill"><?php echo __('Role'); ?>: <?php echo __('Recovery Officer'); ?></span>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="data[RecoveryOfficer][id]" id="RecoveryOfficerId" value="">
                    <input type="hidden" name="data[RecoveryOfficer][user_id]" id="RecoveryOfficerUserId" value="">
                    <input type="hidden" name="data[RecoveryOfficer][project_id]" id="RecoveryOfficerProjectId" value="<?php echo (int)$selectedAgencyId; ?>">
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label><?php echo __('Agencies'); ?></label>
                                <?php echo $this->Form->select('RecoveryOfficer.project_ids', $agencyOptions, array('multiple'=>true, 'value'=>($selectedAgencyId > 0 ? array($selectedAgencyId) : array()), 'class'=>'form-control', 'id'=>'RecoveryOfficerProjectIds', 'name'=>'data[RecoveryOfficer][project_ids][]')); ?>
                                <p class="help-block"><?php echo __('Hold Ctrl to select multiple agencies for this officer login.'); ?></p>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label><?php echo __('Status'); ?></label>
                                <?php echo $this->Form->select('RecoveryOfficer.status', $statusOptions, array('value'=>'Active','class'=>'form-control','id'=>'RecoveryOfficerStatus','name'=>'data[RecoveryOfficer][status]')); ?>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label><?php echo __('Officer Name'); ?></label>
                                <input type="text" name="data[RecoveryOfficer][name]" id="RecoveryOfficerName" class="form-control" placeholder="<?php echo h(__('Officer Name')); ?>" required>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label><?php echo __('Mobile'); ?></label>
                                <input type="text" name="data[RecoveryOfficer][mobile]" id="RecoveryOfficerMobile" class="form-control" placeholder="<?php echo h(__('Mobile')); ?>">
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label><?php echo __('Email'); ?></label>
                                <input type="email" name="data[RecoveryOfficer][email]" id="RecoveryOfficerEmail" class="form-control" placeholder="<?php echo h(__('Email')); ?>">
                            </div>
                        </div>
                    </div>
                    <div class="wv-login-box">
                        <label style="display:block;margin-bottom:8px;">
                            <input type="checkbox" name="data[RecoveryOfficer][create_login]" id="RecoveryOfficerCreateLogin" value="1" checked>
                            <?php echo __('Create / Update login for this officer'); ?>
                        </label>
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label><?php echo __('Login Username'); ?></label>
                                    <input type="text" name="data[RecoveryOfficer][username]" id="RecoveryOfficerUsername" class="form-control" placeholder="<?php echo h(__('Letters and numbers only')); ?>">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label><?php echo __('Password'); ?></label>
                                    <input type="password" name="data[RecoveryOfficer][password]" id="RecoveryOfficerPassword" class="form-control" placeholder="<?php echo h(__('Required for new login')); ?>">
                                    <p class="help-block"><?php echo __('Leave blank while editing to keep the existing password.'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Cancel'); ?></button>
                    <button type="submit" class="btn btn-success"><span class="fa fa-save"></span> <?php echo __('Save Officer'); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
(function($){
    function cleanLoginSeed(value){
        value = (value || '').toString().replace(/[^A-Za-z0-9]/g, '');
        return value;
    }
    function openOfficerModal($btn){
        var mode = $btn.data('mode') || 'add';
        var isEdit = mode === 'edit';
        $('#wvOfficerModalLabel').text(isEdit ? '<?php echo h(__('Edit Recovery Officer')); ?>' : '<?php echo h(__('Add Recovery Officer')); ?>');
        $('#RecoveryOfficerId').val(isEdit ? ($btn.data('id') || '') : '');
        $('#RecoveryOfficerUserId').val(isEdit ? ($btn.data('user-id') || '') : '');
        var projectIds = [];
        if(isEdit){
            var rawProjectIds = ($btn.attr('data-project-ids') || '').toString();
            $.each(rawProjectIds.split(','), function(_, value){
                value = $.trim(value);
                if(value !== ''){
                    projectIds.push(value);
                }
            });
        } else if(<?php echo (int)$selectedAgencyId; ?> > 0) {
            projectIds.push('<?php echo (int)$selectedAgencyId; ?>');
        }
        $('#RecoveryOfficerProjectIds').val(projectIds);
        $('#RecoveryOfficerProjectId').val(projectIds.length ? projectIds[0] : '');
        $('#RecoveryOfficerStatus').val(isEdit ? ($btn.data('status') || 'Active') : 'Active');
        $('#RecoveryOfficerName').val(isEdit ? ($btn.data('name') || '') : '');
        $('#RecoveryOfficerMobile').val(isEdit ? ($btn.data('mobile') || '') : '');
        $('#RecoveryOfficerEmail').val(isEdit ? ($btn.data('email') || '') : '');
        $('#RecoveryOfficerUsername').val(isEdit ? ($btn.data('username') || '') : '');
        $('#RecoveryOfficerPassword').val('');
        $('#RecoveryOfficerCreateLogin').prop('checked', !isEdit || parseInt($btn.data('user-id') || 0, 10) > 0);
        $('#wvOfficerModal').modal('show');
    }
    $(document).on('click', '.js-open-officer-modal', function(){
        openOfficerModal($(this));
    });
    $('#wvOfficerForm').on('submit', function(){
        var selected = $('#RecoveryOfficerProjectIds').val() || [];
        $('#RecoveryOfficerProjectId').val(selected.length ? selected[0] : '');
    });
    $(document).on('blur', '#RecoveryOfficerName,#RecoveryOfficerMobile', function(){
        if($('#RecoveryOfficerUsername').val() !== ''){
            return;
        }
        var seed = cleanLoginSeed($('#RecoveryOfficerMobile').val());
        if(seed === ''){
            seed = cleanLoginSeed($('#RecoveryOfficerName').val()).substr(0, 18);
        }
        $('#RecoveryOfficerUsername').val(seed);
    });
})(jQuery);
</script>
