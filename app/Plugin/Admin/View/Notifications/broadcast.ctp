<?php
$this->set('title_for_layout', __('Send Custom Broadcast'));
?>
<div class="row">
    <div class="col-md-8">
        <div class="panel panel-default notif-panel">
            <div class="panel-heading" style="display: flex; align-items: center; justify-content: space-between; padding: 16px 20px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 40px; height: 40px; border-radius: 10px; background: linear-gradient(135deg, #10b981, #059669); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 18px; box-shadow: 0 4px 10px rgba(16,185,129,0.35);">
                        <i class="fa fa-bullhorn"></i>
                    </div>
                    <div>
                        <h4 style="margin: 0; font-weight: 800; font-size: 17px; color: #0f172a;"><?php echo __('Send Custom Notification Broadcast'); ?></h4>
                        <span style="font-size: 12px; color: #64748b;"><?php echo __('Dispatch custom alerts to all staff, selected user levels, or individuals'); ?></span>
                    </div>
                </div>
                <a href="<?php echo $this->Html->url(array('action' => 'broadcast_history')); ?>" class="btn btn-sm btn-default" style="font-weight: 700; border-radius: 6px;">
                    <i class="fa fa-history"></i> <?php echo __('Broadcast History'); ?>
                </a>
            </div>

            <div class="panel-body" style="padding: 24px;">
                <?php echo $this->Form->create(false, array('url' => array('action' => 'broadcast'), 'id' => 'broadcastForm')); ?>

                <!-- Target Audience -->
                <div class="form-group" style="margin-bottom: 22px;">
                    <label style="font-weight: 800; font-size: 13.5px; color: #1e293b; display: block; margin-bottom: 8px;">
                        <?php echo __('Target Audience'); ?> <span class="text-danger">*</span>
                    </label>
                    <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                        <label style="font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                            <input type="radio" name="target_type" value="all" checked id="targetAll">
                            <span><i class="fa fa-globe text-primary"></i> <?php echo __('All Active Staff'); ?></span>
                        </label>
                        <label style="font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                            <input type="radio" name="target_type" value="roles" id="targetRoles">
                            <span><i class="fa fa-users text-warning"></i> <?php echo __('By User Role / Level'); ?></span>
                        </label>
                        <label style="font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                            <input type="radio" name="target_type" value="users" id="targetUsers">
                            <span><i class="fa fa-user text-success"></i> <?php echo __('Specific Selected Users'); ?></span>
                        </label>
                    </div>
                </div>

                <!-- Roles Selector Container -->
                <div class="form-group" id="rolesSelectWrap" style="display: none; background: #f8fafc; padding: 14px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 20px;">
                    <label style="font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px; display: block;">
                        <?php echo __('Select Target Roles'); ?>:
                    </label>
                    <div style="display: flex; flex-wrap: wrap; gap: 14px;">
                        <?php foreach ($roles as $roleId => $roleName): ?>
                            <label style="font-weight: 500; font-size: 13px; cursor: pointer; margin-bottom: 0;">
                                <input type="checkbox" name="roles[]" value="<?php echo $roleId; ?>"> <?php echo h($roleName); ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Users Selector Container -->
                <div class="form-group" id="usersSelectWrap" style="display: none; background: #f8fafc; padding: 14px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 20px;">
                    <label style="font-weight: 700; font-size: 13px; color: #334155; margin-bottom: 8px; display: block;">
                        <?php echo __('Select Target Users (Hold Ctrl/Cmd to select multiple)'); ?>:
                    </label>
                    <select name="users[]" multiple="multiple" class="form-control" style="height: 140px; border-radius: 6px;">
                        <?php foreach ($userOptions as $uId => $uLabel): ?>
                            <option value="<?php echo $uId; ?>"><?php echo h($uLabel); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Title -->
                <div class="form-group" style="margin-bottom: 18px;">
                    <label style="font-weight: 800; font-size: 13.5px; color: #1e293b;" for="bTitle">
                        <?php echo __('Notification Title'); ?> <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="title" id="bTitle" class="form-control" placeholder="<?php echo __('e.g., Important Team Meeting at 4:00 PM'); ?>" required style="border-radius: 8px; font-weight: 600;">
                </div>

                <!-- Message -->
                <div class="form-group" style="margin-bottom: 18px;">
                    <label style="font-weight: 800; font-size: 13.5px; color: #1e293b;" for="bMessage">
                        <?php echo __('Message Content'); ?> <span class="text-danger">*</span>
                    </label>
                    <textarea name="message" id="bMessage" class="form-control" rows="4" placeholder="<?php echo __('Type your announcement message here...'); ?>" required style="border-radius: 8px; line-height: 1.5;"></textarea>
                </div>

                <!-- Priority & Icon -->
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group" style="margin-bottom: 18px;">
                            <label style="font-weight: 700; font-size: 13px; color: #334155;" for="bPriority">
                                <?php echo __('Priority / Urgency'); ?>
                            </label>
                            <select name="priority" id="bPriority" class="form-control" style="border-radius: 8px;">
                                <option value="normal"><?php echo __('Normal (Blue)'); ?></option>
                                <option value="high"><?php echo __('High (Orange)'); ?></option>
                                <option value="urgent"><?php echo __('Urgent (Red Alert)'); ?></option>
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group" style="margin-bottom: 18px;">
                            <label style="font-weight: 700; font-size: 13px; color: #334155;" for="bIcon">
                                <?php echo __('Icon'); ?>
                            </label>
                            <select name="icon" id="bIcon" class="form-control" style="border-radius: 8px;">
                                <option value="fa-bullhorn"><?php echo __('Bullhorn / Announcement'); ?></option>
                                <option value="fa-bell"><?php echo __('Bell Alert'); ?></option>
                                <option value="fa-exclamation-triangle"><?php echo __('Warning Triangle'); ?></option>
                                <option value="fa-trophy"><?php echo __('Trophy / Celebration'); ?></option>
                                <option value="fa-calendar"><?php echo __('Calendar / Meeting'); ?></option>
                                <option value="fa-info-circle"><?php echo __('Info Circle'); ?></option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Action URL -->
                <div class="form-group" style="margin-bottom: 24px;">
                    <label style="font-weight: 700; font-size: 13px; color: #334155;" for="bActionUrl">
                        <?php echo __('Action Link / Destination (Optional)'); ?>
                    </label>
                    <input type="text" name="action_url" id="bActionUrl" class="form-control" placeholder="<?php echo __('e.g., /admin/Leads/dashboard or https://...'); ?>" style="border-radius: 8px;">
                    <span class="help-block" style="font-size: 11.5px; color: #94a3b8;"><?php echo __('Recipients can click on the notification to navigate directly to this URL.'); ?></span>
                </div>

                <div style="border-top: 1px solid #e2e8f0; padding-top: 18px; display: flex; align-items: center; justify-content: flex-end; gap: 10px;">
                    <a href="<?php echo $this->Html->url(array('action' => 'index')); ?>" class="btn btn-default" style="font-weight: 700; border-radius: 8px;">
                        <?php echo __('Cancel'); ?>
                    </a>
                    <button type="submit" class="btn btn-primary" style="font-weight: 800; border-radius: 8px; padding: 8px 24px;">
                        <i class="fa fa-paper-plane"></i> <?php echo __('Dispatch Notification Now'); ?>
                    </button>
                </div>

                <?php echo $this->Form->end(); ?>
            </div>
        </div>
    </div>

    <!-- Live Preview Box -->
    <div class="col-md-4">
        <div class="panel panel-default" style="border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 4px 14px rgba(15,23,42,0.06); position: sticky; top: 20px;">
            <div class="panel-heading" style="padding: 14px 18px; font-weight: 800; font-size: 14px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; color: #1e293b;">
                <i class="fa fa-eye text-primary"></i> <?php echo __('Live Recipient Preview'); ?>
            </div>
            <div class="panel-body" style="padding: 18px;">
                <p style="font-size: 12px; color: #64748b; margin-bottom: 12px;"><?php echo __('This is how the notification card will appear in recipients\' inboxes and dropdowns:'); ?></p>
                <div id="previewCard" style="border-left: 4px solid #3b82f6; background: #f8fafc; padding: 14px; border-radius: 8px; border-top: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0;">
                    <div style="display: flex; align-items: flex-start; gap: 10px;">
                        <div id="previewIcon" style="width: 32px; height: 32px; border-radius: 8px; background: rgba(59,130,246,0.15); color: #3b82f6; display: flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0;">
                            <i class="fa fa-bullhorn"></i>
                        </div>
                        <div style="flex: 1;">
                            <h5 id="previewTitle" style="margin: 0 0 4px 0; font-weight: 800; font-size: 13.5px; color: #0f172a;">Notification Title Preview</h5>
                            <p id="previewMessage" style="margin: 0; font-size: 12.5px; color: #475569; line-height: 1.4;">Message text will appear right here as you type...</p>
                            <span style="font-size: 10.5px; color: #94a3b8; display: block; margin-top: 6px;"><i class="fa fa-clock-o"></i> <?php echo __('Just now'); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function() {
    $('input[name="target_type"]').on('change', function() {
        var val = $(this).val();
        if (val === 'roles') {
            $('#rolesSelectWrap').slideDown();
            $('#usersSelectWrap').slideUp();
        } else if (val === 'users') {
            $('#usersSelectWrap').slideDown();
            $('#rolesSelectWrap').slideUp();
        } else {
            $('#rolesSelectWrap').slideUp();
            $('#usersSelectWrap').slideUp();
        }
    });

    function updatePreview() {
        var title = $('#bTitle').val() || 'Notification Title Preview';
        var msg = $('#bMessage').val() || 'Message text will appear right here as you type...';
        var icon = $('#bIcon').val() || 'fa-bullhorn';
        var priority = $('#bPriority').val() || 'normal';

        $('#previewTitle').text(title);
        $('#previewMessage').text(msg);
        $('#previewIcon i').attr('class', 'fa ' + icon);

        var color = '#3b82f6';
        if (priority === 'urgent') color = '#ef4444';
        else if (priority === 'high') color = '#f59e0b';

        $('#previewCard').css('border-left-color', color);
        $('#previewIcon').css('color', color).css('background', color + '22');
    }

    $('#bTitle, #bMessage, #bIcon, #bPriority').on('input change', updatePreview);
});
</script>
