<?php
$this->set('title_for_layout', __('Broadcast History'));
?>
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default notif-panel">
            <div class="panel-heading" style="display: flex; align-items: center; justify-content: space-between; padding: 16px 20px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 40px; height: 40px; border-radius: 10px; background: linear-gradient(135deg, #6366f1, #4f46e5); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 18px; box-shadow: 0 4px 10px rgba(99,102,241,0.35);">
                        <i class="fa fa-history"></i>
                    </div>
                    <div>
                        <h4 style="margin: 0; font-weight: 800; font-size: 17px; color: #0f172a;"><?php echo __('Broadcast History'); ?></h4>
                        <span style="font-size: 12px; color: #64748b;"><?php echo __('Log of custom notifications dispatched by administrators'); ?></span>
                    </div>
                </div>
                <a href="<?php echo $this->Html->url(array('action' => 'broadcast')); ?>" class="btn btn-sm btn-primary" style="font-weight: 700; border-radius: 6px;">
                    <i class="fa fa-plus"></i> <?php echo __('Send New Broadcast'); ?>
                </a>
            </div>

            <div class="panel-body" style="padding: 0;">
                <?php if (empty($broadcasts)): ?>
                    <div style="text-align: center; padding: 60px 20px; color: #94a3b8;">
                        <i class="fa fa-history" style="font-size: 48px; margin-bottom: 12px; opacity: 0.5;"></i>
                        <h4 style="font-weight: 700; color: #475569;"><?php echo __('No broadcast history'); ?></h4>
                        <p style="font-size: 13px;"><?php echo __('When you send custom notifications to staff or user groups, records will appear here.'); ?></p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover" style="margin-bottom: 0;">
                            <thead>
                                <tr style="background: #f8fafc; font-size: 12px; color: #475569; text-transform: uppercase;">
                                    <th style="padding: 12px 18px;"><?php echo __('Date & Time'); ?></th>
                                    <th style="padding: 12px 18px;"><?php echo __('Sender'); ?></th>
                                    <th style="padding: 12px 18px;"><?php echo __('Target Audience'); ?></th>
                                    <th style="padding: 12px 18px;"><?php echo __('Title & Content'); ?></th>
                                    <th style="padding: 12px 18px; text-align: center;"><?php echo __('Recipients'); ?></th>
                                    <th style="padding: 12px 18px;"><?php echo __('Priority'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($broadcasts as $b):
                                    $row = $b['NotificationBroadcast'];
                                    $senderName = !empty($b['Sender']['name']) ? $b['Sender']['name'] : __('Admin');
                                    $targetLabel = __('All Staff');
                                    if ($row['target_type'] === 'roles') $targetLabel = __('User Roles');
                                    elseif ($row['target_type'] === 'users') $targetLabel = __('Selected Users');
                                ?>
                                    <tr style="vertical-align: middle;">
                                        <td style="padding: 14px 18px; font-size: 12.5px; color: #64748b; white-space: nowrap;">
                                            <i class="fa fa-calendar-o"></i> <?php echo date('d M Y, h:i A', strtotime($row['created'])); ?>
                                        </td>
                                        <td style="padding: 14px 18px; font-weight: 700; font-size: 13px; color: #1e293b;">
                                            <i class="fa fa-user-circle text-muted"></i> <?php echo h($senderName); ?>
                                        </td>
                                        <td style="padding: 14px 18px;">
                                            <span class="badge" style="background: #e2e8f0; color: #334155; font-size: 11px; font-weight: 700; padding: 4px 8px;">
                                                <?php echo h($targetLabel); ?>
                                            </span>
                                        </td>
                                        <td style="padding: 14px 18px; max-width: 320px;">
                                            <div style="font-weight: 800; font-size: 13.5px; color: #0f172a; margin-bottom: 3px;">
                                                <i class="fa <?php echo h(!empty($row['icon']) ? $row['icon'] : 'fa-bullhorn'); ?> text-primary"></i> <?php echo h($row['title']); ?>
                                            </div>
                                            <div style="font-size: 12px; color: #475569; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                                <?php echo h($row['message']); ?>
                                            </div>
                                            <?php if (!empty($row['action_url'])): ?>
                                                <a href="<?php echo h($row['action_url']); ?>" target="_blank" style="font-size: 11px; color: #2563eb; display: inline-block; margin-top: 4px;">
                                                    <i class="fa fa-external-link"></i> <?php echo h($row['action_url']); ?>
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                        <td style="padding: 14px 18px; text-align: center;">
                                            <span class="badge badge-success" style="font-size: 12px; padding: 5px 10px; font-weight: 800;">
                                                <?php echo (int)$row['total_recipients']; ?>
                                            </span>
                                        </td>
                                        <td style="padding: 14px 18px;">
                                            <?php if ($row['priority'] === 'urgent'): ?>
                                                <span class="badge badge-danger" style="font-size: 10px;"><?php echo __('URGENT'); ?></span>
                                            <?php elseif ($row['priority'] === 'high'): ?>
                                                <span class="badge badge-warning" style="font-size: 10px;"><?php echo __('HIGH'); ?></span>
                                            <?php else: ?>
                                                <span class="badge badge-info" style="font-size: 10px;"><?php echo __('NORMAL'); ?></span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($this->Paginator->hasPage(null, 2)): ?>
                <div class="panel-footer" style="background: #ffffff; border-top: 1px solid #e2e8f0; padding: 12px 20px; display: flex; align-items: center; justify-content: space-between;">
                    <div style="font-size: 12px; color: #64748b;">
                        <?php echo $this->Paginator->counter(array('format' => __('Page {:page} of {:pages}'))); ?>
                    </div>
                    <ul class="pagination pagination-sm" style="margin: 0;">
                        <?php
                        echo $this->Paginator->prev('« ' . __('Previous'), array('tag' => 'li'), null, array('class' => 'disabled', 'tag' => 'li', 'disabledTag' => 'span'));
                        echo $this->Paginator->numbers(array('separator' => '', 'currentTag' => 'span', 'currentClass' => 'active', 'tag' => 'li'));
                        echo $this->Paginator->next(__('Next') . ' »', array('tag' => 'li'), null, array('class' => 'disabled', 'tag' => 'li', 'disabledTag' => 'span'));
                        ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
