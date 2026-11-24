<div class="panel">
    <div class="panel-heading"><?php echo __('Communication Automation'); ?></div>
    <div class="panel-body wv-comm-wrap">
        <?php echo $this->Session->flash(); ?>
        <?php echo $this->element('communication_automation_nav'); ?>
        <h2 class="wv-comm-title"><?php echo __('Automation Dashboard'); ?></h2>
        <p class="wv-comm-sub"><?php echo __('SMS, WhatsApp, email, letter tasks, queue, logs, and client communication timeline.'); ?></p>

        <div class="wv-comm-stats">
            <div class="wv-comm-stat"><span><?php echo __('Templates'); ?></span><strong><?php echo (int)$templateCount; ?></strong></div>
            <div class="wv-comm-stat"><span><?php echo __('Rules'); ?></span><strong><?php echo (int)$ruleCount; ?></strong></div>
            <div class="wv-comm-stat"><span><?php echo __('Pending'); ?></span><strong><?php echo (int)$pendingCount; ?></strong></div>
            <div class="wv-comm-stat"><span><?php echo __('Failed'); ?></span><strong><?php echo (int)$failedCount; ?></strong></div>
            <div class="wv-comm-stat"><span><?php echo __('Sent / Prepared'); ?></span><strong><?php echo (int)$sentCount; ?></strong></div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="wv-comm-card">
                    <h4><?php echo __('Recent Queue'); ?></h4>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped wv-comm-table">
                            <thead><tr><th>#</th><th><?php echo __('Channel'); ?></th><th><?php echo __('Recipient'); ?></th><th><?php echo __('Status'); ?></th><th><?php echo __('Scheduled'); ?></th></tr></thead>
                            <tbody>
                            <?php if(!empty($recentQueue)){ foreach($recentQueue as $row){ $q=$row['CommunicationQueue']; ?>
                                <tr>
                                    <td><?php echo (int)$q['id']; ?></td>
                                    <td><?php echo h($q['channel']); ?></td>
                                    <td><?php echo h($q['recipient_name']); ?></td>
                                    <td><span class="wv-comm-badge <?php echo h(strtolower($q['status'])); ?>"><?php echo h($q['status']); ?></span></td>
                                    <td><?php echo h($q['scheduled_at']); ?></td>
                                </tr>
                            <?php }}else{ ?>
                                <tr><td colspan="5" class="text-center"><?php echo __('No queue items yet.'); ?></td></tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="wv-comm-card">
                    <h4><?php echo __('Recent Logs'); ?></h4>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped wv-comm-table">
                            <thead><tr><th>#</th><th><?php echo __('Channel'); ?></th><th><?php echo __('Recipient'); ?></th><th><?php echo __('Status'); ?></th><th><?php echo __('Date'); ?></th></tr></thead>
                            <tbody>
                            <?php if(!empty($recentLogs)){ foreach($recentLogs as $row){ $l=$row['CommunicationLog']; ?>
                                <tr>
                                    <td><?php echo (int)$l['id']; ?></td>
                                    <td><?php echo h($l['channel']); ?></td>
                                    <td><?php echo h($l['recipient_name']); ?></td>
                                    <td><span class="wv-comm-badge <?php echo h(strtolower($l['status'])); ?>"><?php echo h($l['status']); ?></span></td>
                                    <td><?php echo !empty($l['created']) ? h(date('d-M-Y H:i',strtotime($l['created']))) : '-'; ?></td>
                                </tr>
                            <?php }}else{ ?>
                                <tr><td colspan="5" class="text-center"><?php echo __('No logs yet.'); ?></td></tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
