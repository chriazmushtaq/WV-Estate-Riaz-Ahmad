<?php
$this->set('title_for_layout', __('Notification Center'));
$tab = isset($tab) ? $tab : 'all';
$unreadCount = isset($unreadCount) ? (int)$unreadCount : 0;
$totalCount = isset($totalCount) ? (int)$totalCount : 0;
$searchQuery = isset($searchQuery) ? $searchQuery : '';
$isSuperAdmin = !empty($isSuperAdmin);
?>
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default notif-panel">
            <div class="panel-heading notif-header" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; padding: 16px 20px;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div class="notif-header-icon" style="width: 44px; height: 44px; border-radius: 12px; background: linear-gradient(135deg, #1e3a8a, #3b82f6); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 20px; box-shadow: 0 4px 12px rgba(59,130,246,0.35);">
                        <i class="fa fa-bell"></i>
                    </div>
                    <div>
                        <h4 style="margin: 0; font-weight: 800; font-size: 18px; color: #0f172a;"><?php echo __('Notification Center'); ?></h4>
                        <span style="font-size: 12.5px; color: #64748b;">
                            <?php echo sprintf(__('%d unread of %d total notifications'), $unreadCount, $totalCount); ?>
                        </span>
                    </div>
                </div>

                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                    <?php if ($unreadCount > 0): ?>
                        <a href="<?php echo $this->Html->url(array('action' => 'mark_all_read')); ?>" class="btn btn-sm btn-outline-primary" style="font-weight: 700; border-radius: 8px;" onclick="return confirm('<?php echo __('Mark all notifications as read?'); ?>');">
                            <i class="fa fa-check-circle"></i> <?php echo __('Mark All as Read'); ?>
                        </a>
                    <?php endif; ?>

                    <?php if ($isSuperAdmin): ?>
                        <a href="<?php echo $this->Html->url(array('action' => 'broadcast')); ?>" class="btn btn-sm btn-success" style="font-weight: 700; border-radius: 8px;">
                            <i class="fa fa-bullhorn"></i> <?php echo __('Send Custom Broadcast'); ?>
                        </a>
                        <a href="<?php echo $this->Html->url(array('action' => 'settings')); ?>" class="btn btn-sm btn-default" style="font-weight: 700; border-radius: 8px;">
                            <i class="fa fa-sliders"></i> <?php echo __('Notification Settings'); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Tabs & Search -->
            <div class="panel-body" style="padding: 16px 20px 0 20px; border-bottom: 1px solid #e2e8f0; background: #f8fafc;">
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                    <ul class="nav nav-tabs notif-tabs" style="border-bottom: none; margin-bottom: 0;">
                        <li class="<?php echo ($tab === 'all') ? 'active' : ''; ?>">
                            <a href="<?php echo $this->Html->url(array('action' => 'index', '?' => array('tab' => 'all'))); ?>" style="font-weight: 700; border-radius: 8px 8px 0 0;">
                                <i class="fa fa-inbox"></i> <?php echo __('All'); ?>
                            </a>
                        </li>
                        <li class="<?php echo ($tab === 'unread') ? 'active' : ''; ?>">
                            <a href="<?php echo $this->Html->url(array('action' => 'index', '?' => array('tab' => 'unread'))); ?>" style="font-weight: 700; border-radius: 8px 8px 0 0;">
                                <i class="fa fa-envelope-o"></i> <?php echo __('Unread'); ?>
                                <?php if ($unreadCount > 0): ?>
                                    <span class="badge badge-danger" style="margin-left: 4px;"><?php echo $unreadCount; ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="<?php echo ($tab === 'leads') ? 'active' : ''; ?>">
                            <a href="<?php echo $this->Html->url(array('action' => 'index', '?' => array('tab' => 'leads'))); ?>" style="font-weight: 700; border-radius: 8px 8px 0 0;">
                                <i class="fa fa-users"></i> <?php echo __('Leads & CRM'); ?>
                            </a>
                        </li>
                        <li class="<?php echo ($tab === 'followup') ? 'active' : ''; ?>">
                            <a href="<?php echo $this->Html->url(array('action' => 'index', '?' => array('tab' => 'followup'))); ?>" style="font-weight: 700; border-radius: 8px 8px 0 0;">
                                <i class="fa fa-calendar-check-o"></i> <?php echo __('Follow-ups'); ?>
                            </a>
                        </li>
                        <li class="<?php echo ($tab === 'finance') ? 'active' : ''; ?>">
                            <a href="<?php echo $this->Html->url(array('action' => 'index', '?' => array('tab' => 'finance'))); ?>" style="font-weight: 700; border-radius: 8px 8px 0 0;">
                                <i class="fa fa-money"></i> <?php echo __('Deals & Finance'); ?>
                            </a>
                        </li>
                        <li class="<?php echo ($tab === 'broadcast') ? 'active' : ''; ?>">
                            <a href="<?php echo $this->Html->url(array('action' => 'index', '?' => array('tab' => 'broadcast'))); ?>" style="font-weight: 700; border-radius: 8px 8px 0 0;">
                                <i class="fa fa-bullhorn"></i> <?php echo __('Announcements'); ?>
                            </a>
                        </li>
                    </ul>

                    <form method="get" action="<?php echo $this->Html->url(array('action' => 'index')); ?>" style="display: flex; align-items: center; gap: 6px; margin-bottom: 8px;">
                        <input type="hidden" name="tab" value="<?php echo h($tab); ?>">
                        <div class="input-group" style="width: 240px;">
                            <input type="text" name="q" class="form-control input-sm" placeholder="<?php echo __('Search alerts...'); ?>" value="<?php echo h($searchQuery); ?>" style="border-radius: 6px 0 0 6px;">
                            <span class="input-group-btn">
                                <button class="btn btn-sm btn-default" type="submit" style="border-radius: 0 6px 6px 0;"><i class="fa fa-search"></i></button>
                            </span>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Notifications Feed Table/List -->
            <div class="panel-body notif-body" style="padding: 0;">
                <?php if (empty($notifications)): ?>
                    <div style="text-align: center; padding: 60px 20px; color: #94a3b8;">
                        <i class="fa fa-bell-slash-o" style="font-size: 54px; margin-bottom: 14px; opacity: 0.5;"></i>
                        <h4 style="font-weight: 700; color: #475569;"><?php echo __('No notifications found'); ?></h4>
                        <p style="font-size: 13px; max-width: 400px; margin: 0 auto;"><?php echo __('You are all caught up! When new leads are assigned, follow-ups are due, or announcements arrive, they will appear here.'); ?></p>
                    </div>
                <?php else: ?>
                    <div class="list-group notif-list" style="margin-bottom: 0;">
                        <?php foreach ($notifications as $notifRow):
                            $n = $notifRow['UserNotification'];
                            $isRead = !empty($n['is_read']);
                            $priorityColor = '#3b82f6';
                            if ($n['priority'] === 'urgent') $priorityColor = '#ef4444';
                            elseif ($n['priority'] === 'high') $priorityColor = '#f59e0b';
                            elseif ($n['type'] === 'broadcast') $priorityColor = '#8b5cf6';
                            elseif (strpos($n['type'], 'deal') !== false || strpos($n['type'], 'won') !== false) $priorityColor = '#10b981';
                        ?>
                            <div class="list-group-item notif-item <?php echo $isRead ? 'read' : 'unread'; ?>" style="padding: 16px 20px; border-left: 4px solid <?php echo $isRead ? '#cbd5e1' : $priorityColor; ?>; background: <?php echo $isRead ? '#ffffff' : '#f8fafc'; ?>; display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; transition: background 0.2s;">
                                <div style="display: flex; align-items: flex-start; gap: 14px; flex: 1;">
                                    <div class="notif-type-icon" style="width: 38px; height: 38px; border-radius: 10px; background: <?php echo $priorityColor; ?>15; color: <?php echo $priorityColor; ?>; display: flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0; margin-top: 2px;">
                                        <i class="fa <?php echo h(!empty($n['icon']) ? $n['icon'] : 'fa-bell'); ?>"></i>
                                    </div>
                                    <div style="flex: 1;">
                                        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                            <h5 style="margin: 0; font-weight: 800; font-size: 14.5px; color: #0f172a;">
                                                <?php echo h($n['title']); ?>
                                            </h5>
                                            <?php if (!$isRead): ?>
                                                <span class="badge badge-primary" style="font-size: 9.5px; font-weight: 700; background: #2563eb;"><?php echo __('NEW'); ?></span>
                                            <?php endif; ?>
                                            <?php if ($n['priority'] === 'urgent'): ?>
                                                <span class="badge badge-danger" style="font-size: 9.5px;"><?php echo __('URGENT'); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div style="margin-top: 5px; font-size: 13.5px; color: #334155; line-height: 1.5;">
                                            <?php echo nl2br(h($n['message'])); ?>
                                        </div>
                                        <div style="margin-top: 8px; display: flex; align-items: center; gap: 14px; font-size: 11.5px; color: #64748b;">
                                            <span><i class="fa fa-clock-o"></i> <?php echo date('d M Y, h:i A', strtotime($n['created'])); ?></span>
                                            <?php if (!empty($n['action_url'])): ?>
                                                <a href="<?php echo h($this->Html->url($n['action_url'])); ?>" class="notif-action-link" style="font-weight: 700; color: #2563eb; text-decoration: none;">
                                                    <?php echo __('View Details'); ?> <i class="fa fa-arrow-circle-right"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <?php if (!$isRead): ?>
                                        <button type="button" class="btn btn-xs btn-default btn-mark-read" data-id="<?php echo (int)$n['id']; ?>" title="<?php echo __('Mark as read'); ?>" style="border-radius: 6px;">
                                            <i class="fa fa-check text-muted"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <?php if ($this->Paginator->hasPage(null, 2)): ?>
                <div class="panel-footer" style="background: #ffffff; border-top: 1px solid #e2e8f0; padding: 12px 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap;">
                    <div style="font-size: 12px; color: #64748b;">
                        <?php echo $this->Paginator->counter(array('format' => __('Page {:page} of {:pages}, showing {:current} of {:count} total'))); ?>
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

<script type="text/javascript">
$(document).ready(function() {
    $('.btn-mark-read').on('click', function(e) {
        e.preventDefault();
        var btn = $(this);
        var id = btn.data('id');
        var item = btn.closest('.notif-item');
        $.ajax({
            url: '<?php echo $this->Html->url(array('action' => 'mark_read')); ?>',
            type: 'POST',
            data: { id: id },
            dataType: 'json',
            success: function(resp) {
                if (resp && resp.status === 'success') {
                    item.removeClass('unread').addClass('read');
                    item.find('.badge-primary').remove();
                    btn.fadeOut();
                    if (window.WhiteVillasNotifications) {
                        window.WhiteVillasNotifications.updateBadge(resp.unread_count);
                    }
                }
            }
        });
    });
});
</script>
