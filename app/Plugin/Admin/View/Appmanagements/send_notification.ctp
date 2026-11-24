<div class="page-title">
    <h2><span class="fa fa-paper-plane"></span> <?php echo __('Compose & Dispatch In-App Notification'); ?></h2>
</div>

<div class="page-content-wrap">
    <?php echo $this->element('app_management_nav'); ?>

    <?php echo $this->Form->create('AppNotification', array('class' => 'form-horizontal')); ?>

    <div class="row">
        <div class="col-md-8">
            <div class="panel panel-default" style="border-radius: 8px;">
                <div class="panel-heading" style="background: #0F172A; color: #fff;">
                    <h3 class="panel-title" style="color: #fff; font-weight: bold;"><i class="fa fa-envelope-o"></i> <?php echo __('Notification Message Details'); ?></h3>
                </div>
                <div class="panel-body">
                    <div class="form-group">
                        <label class="col-md-3 control-label"><?php echo __('Target Audience'); ?> <span class="text-danger">*</span></label>
                        <div class="col-md-9">
                            <?php echo $this->Form->input('target_audience', array(
                                'id' => 'targetAudienceSelect',
                                'type' => 'select',
                                'options' => array(
                                    'all_clients' => __('All Registered Clients (Broadcast to Everyone)'),
                                    'deal_active' => __('Clients with Active Deals Only'),
                                    'deal_blocked' => __('Clients with Blocked Deals Only'),
                                    'deal_overdue' => __('Clients with Overdue Installments Only'),
                                    'all_contacts' => __('All Website Leads & Contacts'),
                                    'specific_client' => __('Single Specific Client (Select Below)')
                                ),
                                'class' => 'form-control',
                                'label' => false,
                                'div' => false
                            )); ?>
                        </div>
                    </div>

                    <div class="form-group" id="specificClientGroup" style="display: none;">
                        <label class="col-md-3 control-label"><?php echo __('Select Client'); ?> <span class="text-danger">*</span></label>
                        <div class="col-md-9">
                            <?php echo $this->Form->input('specific_client_id', array(
                                'type' => 'select',
                                'options' => $clients,
                                'empty' => __('--- Choose a Client ---'),
                                'class' => 'form-control select',
                                'data-live-search' => 'true',
                                'label' => false,
                                'div' => false
                            )); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-3 control-label"><?php echo __('Notification Type'); ?> <span class="text-danger">*</span></label>
                        <div class="col-md-9">
                            <?php echo $this->Form->input('type', array(
                                'type' => 'select',
                                'options' => array(
                                    'admin_notice' => __('Official Admin Notice (Navy Alert)'),
                                    'announcement' => __('Important Announcement (Blue Info)'),
                                    'urgent_alert' => __('Urgent Alert (Red Warning)'),
                                    'offer' => __('Promotional Offer / Update (Green)'),
                                    'letter_issued' => __('Official Letter Issued (Cyan)'),
                                    'deal_status_changed' => __('Deal Status Update (Purple)')
                                ),
                                'class' => 'form-control',
                                'label' => false,
                                'div' => false
                            )); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-3 control-label"><?php echo __('Notification Title'); ?> <span class="text-danger">*</span></label>
                        <div class="col-md-9">
                            <?php echo $this->Form->input('title', array(
                                'id' => 'notifTitleInput',
                                'class' => 'form-control',
                                'placeholder' => 'e.g. Office Holiday Notice / Payment Verification Update',
                                'required' => true,
                                'label' => false,
                                'div' => false
                            )); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-3 control-label"><?php echo __('Message Body'); ?> <span class="text-danger">*</span></label>
                        <div class="col-md-9">
                            <?php echo $this->Form->input('message', array(
                                'id' => 'notifMessageInput',
                                'type' => 'textarea',
                                'rows' => 5,
                                'class' => 'form-control',
                                'placeholder' => 'Write your detailed in-app notification message here...',
                                'required' => true,
                                'label' => false,
                                'div' => false
                            )); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-3 control-label"><?php echo __('In-App Action Button (Optional)'); ?></label>
                        <div class="col-md-9">
                            <?php echo $this->Form->input('action_route', array(
                                'type' => 'select',
                                'options' => array(
                                    '' => __('None (Standard Notification)'),
                                    'overdue' => __('Open Overdue Installments Screen'),
                                    'payments' => __('Open Payment Receipts Ledger'),
                                    'deals' => __('Open My Deals List'),
                                    'profile' => __('Open My Profile / Account')
                                ),
                                'class' => 'form-control',
                                'label' => false,
                                'div' => false
                            )); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-md-offset-3 col-md-9">
                            <button type="submit" class="btn btn-success btn-lg" style="padding: 10px 25px; font-weight: bold;">
                                <i class="fa fa-paper-plane"></i> <?php echo __('Dispatch In-App Notification Now'); ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Live Card Preview Column -->
        <div class="col-md-4">
            <div class="panel panel-default" style="border-radius: 8px;">
                <div class="panel-heading" style="background: #334155; color: #fff;">
                    <h3 class="panel-title" style="color: #fff; font-weight: bold;"><i class="fa fa-eye"></i> <?php echo __('Mobile Live Card Preview'); ?></h3>
                </div>
                <div class="panel-body" style="background: #F8FAFC;">
                    <div style="background: #fff; border: 1.5px solid #0288D1; border-radius: 10px; padding: 12px; box-shadow: 0 2px 6px rgba(0,0,0,0.06);">
                        <div style="display: flex; align-items: center; margin-bottom: 8px;">
                            <div style="width: 28px; height: 28px; background: rgba(2,136,209,0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 8px;">
                                <i class="fa fa-bell" style="color: #0288D1; font-size: 13px;"></i>
                            </div>
                            <div style="flex: 1;">
                                <div id="previewTitle" style="font-size: 12px; font-weight: bold; color: #0F172A; line-height: 1.2;">
                                    <?php echo __('Notification Title'); ?>
                                </div>
                                <div style="font-size: 9.5px; color: #0288D1; font-weight: 600; margin-top: 2px;">
                                    <?php echo date('d-M-Y. \T\i\m\e h:i A'); ?>
                                </div>
                            </div>
                            <div>
                                <span class="badge badge-danger" style="font-size: 8px; font-weight: bold;"><?php echo __('Mark Read'); ?></span>
                            </div>
                        </div>
                        <div id="previewMessage" style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 6px; padding: 8px; font-size: 10.5px; color: #334155; line-height: 1.4;">
                            <?php echo __('Your notification message body will appear here in the user mobile app in real-time.'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php echo $this->Form->end(); ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var audienceSel = document.getElementById('targetAudienceSelect');
    var specificGroup = document.getElementById('specificClientGroup');
    var titleInput = document.getElementById('notifTitleInput');
    var msgInput = document.getElementById('notifMessageInput');
    var prevTitle = document.getElementById('previewTitle');
    var prevMsg = document.getElementById('previewMessage');

    function checkAudience() {
        if (audienceSel.value === 'specific_client') {
            specificGroup.style.display = 'block';
        } else {
            specificGroup.style.display = 'none';
        }
    }

    audienceSel.addEventListener('change', checkAudience);
    checkAudience();

    if (titleInput && prevTitle) {
        titleInput.addEventListener('input', function() {
            prevTitle.textContent = titleInput.value || 'Notification Title';
        });
    }

    if (msgInput && prevMsg) {
        msgInput.addEventListener('input', function() {
            prevMsg.textContent = msgInput.value || 'Your notification message body will appear here in the user mobile app in real-time.';
        });
    }
});
</script>
