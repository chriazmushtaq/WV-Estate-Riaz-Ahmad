<?php
$this->set('title_for_layout', __('LeadBot Gateway - Notification Dispatch Logs'));

$logs = isset($logs) ? $logs : array();
$isSuperAdmin = !empty($isSuperAdmin);
$targetAgencyId = isset($targetAgencyId) ? (int)$targetAgencyId : 0;
?>

<div class="row" style="margin-top: 15px;">
    <div class="col-md-12">
        <div class="panel panel-default" style="border-radius: 12px; box-shadow: 0 4px 16px rgba(15,23,42,0.06); border: 1px solid #e2e8f0;">
            <div class="panel-heading" style="padding: 18px 22px; background: #f8fafc; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <a href="<?php echo $this->Html->url(array('action' => 'index', '?' => array('project_id' => $targetAgencyId))); ?>" class="btn btn-default btn-sm" style="border-radius: 6px; font-weight: 700;">
                        <i class="fa fa-arrow-left"></i> <?php echo __('Back to Gateway Hub'); ?>
                    </a>
                    <h4 style="margin: 0; font-weight: 800; font-size: 17px; color: #0f172a;">
                        <i class="fa fa-history text-primary"></i> <?php echo __('Notification Dispatch Audit Trail'); ?>
                    </h4>
                </div>

                <!-- Search & Filters Form -->
                <form method="get" action="<?php echo $this->Html->url(array('action' => 'logs')); ?>" class="form-inline" style="display: flex; gap: 8px; flex-wrap: wrap;">
                    <input type="hidden" name="project_id" value="<?php echo $targetAgencyId; ?>">

                    <select name="channel" class="form-control input-sm" style="font-weight: 700; border-radius: 6px;">
                        <option value=""><?php echo __('All Channels'); ?></option>
                        <option value="whatsapp" <?php echo (isset($this->request->query['channel']) && $this->request->query['channel'] === 'whatsapp') ? 'selected' : ''; ?>>WhatsApp</option>
                        <option value="sms" <?php echo (isset($this->request->query['channel']) && $this->request->query['channel'] === 'sms') ? 'selected' : ''; ?>>SMS</option>
                    </select>

                    <select name="status" class="form-control input-sm" style="font-weight: 700; border-radius: 6px;">
                        <option value=""><?php echo __('All Statuses'); ?></option>
                        <option value="sent" <?php echo (isset($this->request->query['status']) && $this->request->query['status'] === 'sent') ? 'selected' : ''; ?>>Sent</option>
                        <option value="queued" <?php echo (isset($this->request->query['status']) && $this->request->query['status'] === 'queued') ? 'selected' : ''; ?>>Queued</option>
                        <option value="failed" <?php echo (isset($this->request->query['status']) && $this->request->query['status'] === 'failed') ? 'selected' : ''; ?>>Failed</option>
                    </select>

                    <div class="input-group input-group-sm">
                        <input type="text" name="q" class="form-control" placeholder="<?php echo __('Search phone, name, text...'); ?>" value="<?php echo isset($this->request->query['q']) ? h($this->request->query['q']) : ''; ?>" style="border-radius: 6px 0 0 6px;">
                        <span class="input-group-btn">
                            <button type="submit" class="btn btn-primary" style="border-radius: 0 6px 6px 0; font-weight: 700;">
                                <i class="fa fa-search"></i>
                            </button>
                        </span>
                    </div>

                    <?php if (!empty($this->request->query['q']) || !empty($this->request->query['channel']) || !empty($this->request->query['status'])): ?>
                        <a href="<?php echo $this->Html->url(array('action' => 'logs', '?' => array('project_id' => $targetAgencyId))); ?>" class="btn btn-default btn-sm" title="Reset Filters" style="border-radius: 6px;">
                            <i class="fa fa-times"></i>
                        </a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="panel-body" style="padding: 0;">
                <?php if (!empty($logs)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-striped" style="margin: 0;">
                            <thead>
                                <tr style="background: #f8fafc; font-size: 12.5px; color: #475569;">
                                    <th>#</th>
                                    <th><?php echo __('Channel'); ?></th>
                                    <th><?php echo __('Event Key'); ?></th>
                                    <th><?php echo __('Recipient'); ?></th>
                                    <th><?php echo __('Message Content'); ?></th>
                                    <th><?php echo __('Routing Gateway'); ?></th>
                                    <th><?php echo __('Status'); ?></th>
                                    <th><?php echo __('Date & Time'); ?></th>
                                    <th class="text-right"><?php echo __('Payload'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($logs as $lg): 
                                    $log = $lg['LeadbotNotificationLog'];
                                    $isWa = ($log['channel'] === 'whatsapp');
                                    $isOk = in_array($log['status'], array('sent', 'queued', 'delivered'), true);
                                ?>
                                    <tr>
                                        <td style="font-size: 12px; color: #94a3b8;"><?php echo $log['id']; ?></td>
                                        <td>
                                            <?php if ($isWa): ?>
                                                <span class="label" style="background: #25D366; font-size: 11px;"><i class="fa fa-whatsapp"></i> WhatsApp</span>
                                            <?php else: ?>
                                                <span class="label" style="background: #f59e0b; font-size: 11px;"><i class="fa fa-comment-o"></i> SMS</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge" style="background: #e2e8f0; color: #334155; font-size: 11px;"><?php echo h($log['event_key']); ?></span>
                                            <?php if (!empty($log['is_test'])): ?>
                                                <span class="badge badge-warning" style="font-size: 10px;">TEST</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong style="color: #0f172a; font-size: 13px;"><?php echo h($log['recipient_phone']); ?></strong>
                                            <?php if (!empty($log['recipient_name'])): ?>
                                                <div style="font-size: 11px; color: #64748b;"><?php echo h($log['recipient_name']); ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td style="max-width: 300px; font-size: 12.5px; color: #334155;">
                                            <?php echo nl2br(h($log['message'])); ?>
                                            <?php if (!empty($log['error_message'])): ?>
                                                <div style="color: #dc2626; font-size: 11.5px; margin-top: 4px;">
                                                    <i class="fa fa-warning"></i> <?php echo h($log['error_message']); ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td style="font-size: 12px; color: #64748b; font-family: monospace;">
                                            <?php echo h($log['gateway_profile_or_device']); ?>
                                        </td>
                                        <td>
                                            <?php if ($isOk): ?>
                                                <span class="label label-success" style="font-size: 11px;"><i class="fa fa-check"></i> <?php echo strtoupper($log['status']); ?></span>
                                            <?php else: ?>
                                                <span class="label label-danger" style="font-size: 11px;"><i class="fa fa-times"></i> FAILED</span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="font-size: 12px; color: #64748b; white-space: nowrap;">
                                            <?php echo date('d M Y, h:i:s A', strtotime($log['created'])); ?>
                                        </td>
                                        <td class="text-right">
                                            <?php if (!empty($log['response_payload'])): ?>
                                                <button type="button" class="btn btn-default btn-xs" onclick="showPayloadModal(<?php echo htmlspecialchars(json_encode($log['response_payload']), ENT_QUOTES, 'UTF-8'); ?>)" style="border-radius: 4px;">
                                                    <i class="fa fa-code"></i> JSON
                                                </button>
                                            <?php else: ?>
                                                <span class="text-muted" style="font-size: 11px;">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div style="padding: 16px 22px; border-top: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                        <span style="font-size: 13px; color: #64748b;">
                            <?php echo $this->Paginator->counter(array('format' => __('Page {:page} of {:pages}, showing {:current} records out of {:count} total'))); ?>
                        </span>
                        <ul class="pagination pagination-sm" style="margin: 0;">
                            <?php
                            echo $this->Paginator->prev('&laquo;', array('tag' => 'li', 'escape' => false), null, array('class' => 'disabled', 'tag' => 'li', 'disabledTag' => 'a'));
                            echo $this->Paginator->numbers(array('separator' => '', 'currentTag' => 'a', 'currentClass' => 'active', 'tag' => 'li', 'first' => 1));
                            echo $this->Paginator->next('&raquo;', array('tag' => 'li', 'escape' => false), null, array('class' => 'disabled', 'tag' => 'li', 'disabledTag' => 'a'));
                            ?>
                        </ul>
                    </div>
                <?php else: ?>
                    <div style="padding: 60px; text-align: center; color: #64748b;">
                        <i class="fa fa-inbox" style="font-size: 48px; color: #cbd5e1; margin-bottom: 12px;"></i>
                        <h4 style="margin: 0; font-weight: 800; color: #334155;"><?php echo __('No notification logs match your query.'); ?></h4>
                        <p style="margin: 6px 0 0;"><?php echo __('Try clearing your filter or searching with different keywords.'); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal for raw payload -->
<div class="modal fade" id="payloadModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="border-radius: 12px;">
            <div class="modal-header" style="background: #0f172a; color: #fff; border-radius: 12px 12px 0 0;">
                <button type="button" class="close" data-dismiss="modal" style="color: #fff;">&times;</button>
                <h4 class="modal-title" style="font-weight: 800;"><i class="fa fa-terminal"></i> Gateway Response Payload</h4>
            </div>
            <div class="modal-body" style="background: #1e293b; padding: 20px;">
                <pre id="payloadContent" style="background: #0f172a; color: #38bdf8; border: none; font-size: 12px; border-radius: 8px; max-height: 400px; overflow: auto;"></pre>
            </div>
        </div>
    </div>
</div>

<script>
function showPayloadModal(raw) {
    try {
        var parsed = JSON.parse(raw);
        $('#payloadContent').text(JSON.stringify(parsed, null, 2));
    } catch(e) {
        $('#payloadContent').text(raw);
    }
    $('#payloadModal').modal('show');
}
</script>
