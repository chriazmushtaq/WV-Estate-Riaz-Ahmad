<div class="page-title">
    <div class="title-env">
        <h1 class="title"><span class="fa fa-comments"></span> <?php echo __('Smart Collections & SMS/WhatsApp Reminders'); ?></h1>
        <p class="description"><?php echo __('Broadcast due date notices, overdue reminders, and payment confirmations via WhatsApp and SMS.'); ?></p>
    </div>
</div>

<div class="panel">
    <div class="panel-body">
        <?php echo $this->Session->flash(); ?>

        <div class="well" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:20px;margin-bottom:24px;">
            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:14px;">
                <h4 style="font-weight:800;color:#0f172a;margin:0;"><span class="fa fa-paper-plane text-primary"></span> <?php echo __('Broadcast Unpaid Bill Reminders'); ?></h4>
                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                    <a href="<?php echo $this->Html->url(array('action' => 'run_drip_reminders')); ?>" class="btn btn-info btn-sm" style="font-weight:700;" onclick="return confirm('Evaluate multi-stage drip schedule and dispatch WhatsApp notices according to configured days?');">
                        <span class="fa fa-calendar-check-o"></span> <?php echo __('Run Drip Reminders (By Configured Days)'); ?>
                    </a>
                    <a href="<?php echo $this->Html->url(array('action' => 'send_executive_digest')); ?>" class="btn btn-primary btn-sm" style="font-weight:700;" onclick="return confirm('Compile and dispatch daily executive KPI digest to management WhatsApp numbers?');">
                        <span class="fa fa-briefcase"></span> <?php echo __('Send Executive Digest to WhatsApp'); ?>
                    </a>
                    <a href="<?php echo $this->Html->url(array('controller' => 'billing_settings', 'action' => 'index', '?' => array('tab' => 'upgrades'))); ?>" class="btn btn-default btn-sm" style="font-weight:700;color:#059669;border-color:#10b981;">
                        <span class="fa fa-rocket text-success"></span> <?php echo __('Configure Upgrades & Days'); ?>
                    </a>
                </div>
            </div>
            <form method="post" action="<?php echo $this->Html->url(array('action' => 'send_reminder')); ?>" class="form-inline">
                <div class="form-group" style="margin-right:12px;">
                    <label style="margin-right:6px;font-size:12px;"><?php echo __('Channel'); ?>:</label>
                    <select name="data[Reminder][channel]" class="form-control">
                        <option value="whatsapp">WhatsApp Gateway</option>
                        <option value="sms">SMS Gateway</option>
                    </select>
                </div>
                <div class="form-group" style="margin-right:12px;">
                    <label style="margin-right:6px;font-size:12px;"><?php echo __('Notice Type'); ?>:</label>
                    <select name="data[Reminder][type]" class="form-control">
                        <option value="due_date">Due Date Approaching</option>
                        <option value="overdue">Overdue Arrears Notice</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-success" style="font-weight:800;" onclick="return confirm('Broadcast reminders to all consumers with unpaid bills?');">
                    <span class="fa fa-send"></span> <?php echo __('Dispatch to All Unpaid Consumers'); ?>
                </button>
            </form>
        </div>

        <h4 style="font-weight:800;color:#0f172a;margin-bottom:14px;"><span class="fa fa-history"></span> <?php echo __('Reminder Dispatch Logs'); ?></h4>
        <div class="table-responsive">
            <table class="table table-striped table-bordered" style="vertical-align:middle;font-size:12.5px;">
                <thead style="background:#f1f5f9;">
                    <tr>
                        <th>#</th>
                        <th><?php echo __('Recipient'); ?></th>
                        <th><?php echo __('Channel'); ?></th>
                        <th><?php echo __('Message'); ?></th>
                        <th><?php echo __('Status'); ?></th>
                        <th><?php echo __('Sent Time'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($logs)){ ?>
                        <tr><td colspan="6" class="text-center text-muted" style="padding:20px;"><?php echo __('No reminder logs found.'); ?></td></tr>
                    <?php } else { foreach($logs as $l){ $lg = $l['BillingReminderLog']; ?>
                        <tr>
                            <td><?php echo (int)$lg['id']; ?></td>
                            <td><strong><?php echo h($lg['recipient']); ?></strong></td>
                            <td><span class="label label-info"><?php echo strtoupper(h($lg['channel'])); ?></span></td>
                            <td><?php echo h(isset($lg['message']) ? $lg['message'] : ''); ?></td>
                            <td><span class="label label-success"><?php echo ucfirst(h($lg['status'])); ?></span></td>
                            <td><?php echo date('d-M-Y H:i', strtotime((string)$lg['sent_at'])); ?></td>
                        </tr>
                    <?php } } ?>
                </tbody>
            </table>
        </div>

    </div>
</div>
