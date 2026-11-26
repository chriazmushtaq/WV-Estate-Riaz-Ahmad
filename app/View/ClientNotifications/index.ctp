<style type="text/css">
.wv-notif-page {
    background: #f4f7fb;
    margin: -10px -5px 0;
    padding: 14px;
    min-height: 85vh;
}
.wv-notif-header {
    background: linear-gradient(135deg, #07152a 0%, #102746 100%);
    border-radius: 16px;
    padding: 24px 28px;
    color: #ffffff;
    margin-bottom: 24px;
    box-shadow: 0 14px 30px rgba(7, 21, 42, 0.12);
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
}
.wv-notif-header h1 {
    font-size: 24px;
    font-weight: 900;
    margin: 0 0 6px;
    color: #ffffff !important;
    display: flex;
    align-items: center;
    gap: 12px;
}
.wv-notif-header p {
    font-size: 13px;
    color: #cbd5e1;
    margin: 0;
}
.wv-notif-stats {
    display: flex;
    gap: 12px;
}
.wv-notif-stat-pill {
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.18);
    border-radius: 999px;
    padding: 8px 16px;
    font-size: 12px;
    font-weight: 800;
    color: #ffffff;
    display: flex;
    align-items: center;
    gap: 8px;
}
.wv-notif-filters {
    display: flex;
    gap: 8px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}
.wv-notif-filter-btn {
    background: #ffffff;
    border: 1px solid #dfe9f5;
    border-radius: 999px;
    padding: 8px 16px;
    font-size: 12px;
    font-weight: 800;
    color: #475569;
    text-decoration: none !important;
    transition: all 0.2s ease;
}
.wv-notif-filter-btn:hover {
    background: #f1f5f9;
    color: #0f172a;
}
.wv-notif-filter-btn.active {
    background: #ff0a46;
    border-color: #ff0a46;
    color: #ffffff;
    box-shadow: 0 4px 12px rgba(255, 10, 70, 0.25);
}
.wv-notif-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.wv-notif-card {
    background: #ffffff;
    border: 1px solid #dfe9f5;
    border-radius: 14px;
    padding: 18px 20px;
    display: flex;
    align-items: flex-start;
    gap: 16px;
    transition: all 0.2s ease;
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.04);
    position: relative;
}
.wv-notif-card:hover {
    box-shadow: 0 8px 20px rgba(15, 23, 42, 0.08);
    transform: translateY(-1px);
}
.wv-notif-card.unread {
    background: #fbfdff;
    border-left: 4px solid #ff0a46;
}
.wv-notif-icon-wrap {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex: 0 0 44px;
}
.wv-notif-content {
    flex: 1 1 auto;
}
.wv-notif-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 4px;
    gap: 12px;
}
.wv-notif-title {
    font-size: 15px;
    font-weight: 800;
    color: #07152a;
    margin: 0;
}
.wv-notif-time {
    font-size: 11px;
    font-weight: 700;
    color: #94a3b8;
    white-space: nowrap;
}
.wv-notif-message {
    font-size: 13px;
    color: #475569;
    margin: 0 0 10px;
    line-height: 1.5;
}
.wv-notif-actions {
    display: flex;
    align-items: center;
    gap: 10px;
}
.wv-notif-action-btn {
    background: #07152a;
    color: #ffffff !important;
    border-radius: 999px;
    padding: 6px 14px;
    font-size: 11px;
    font-weight: 800;
    text-decoration: none !important;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s ease;
}
.wv-notif-action-btn:hover {
    background: #ff0a46;
}
.wv-notif-empty {
    background: #ffffff;
    border: 1px solid #dfe9f5;
    border-radius: 14px;
    padding: 48px 24px;
    text-align: center;
    color: #64748b;
}
.wv-notif-empty i {
    font-size: 48px;
    color: #cbd5e1;
    margin-bottom: 12px;
}
</style>

<div class="wv-notif-page">
    <?php echo $this->Session->flash(); ?>

    <div class="wv-notif-header">
        <div>
            <h1><i class="fa fa-bell text-warning"></i> <?php echo __('Notification Center & Activity Stream'); ?></h1>
            <p><?php echo __('Live updates regarding your property installments, verified receipts, official letters, and e-balloting results.'); ?></p>
        </div>
        <div class="wv-notif-stats">
            <div class="wv-notif-stat-pill">
                <i class="fa fa-envelope-open-o"></i> <?php echo __('Unread:'); ?> <span class="badge" style="background:#ff0a46;"><?php echo (int)$unreadCount; ?></span>
            </div>
            <?php if($unreadCount > 0): ?>
            <a href="<?php echo $this->Html->url(array('action' => 'mark_all_read')); ?>" class="btn btn-sm" style="background:rgba(255,255,255,0.2); color:#fff; border-radius:999px; font-weight:800; font-size:12px;">
                <i class="fa fa-check"></i> <?php echo __('Mark All as Read'); ?>
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Filters -->
    <div class="wv-notif-filters">
        <a href="<?php echo $this->Html->url(array('action' => 'index')); ?>" class="wv-notif-filter-btn <?php echo (empty($type) || $type === 'all') ? 'active' : ''; ?>">
            <i class="fa fa-list"></i> <?php echo __('All Notifications'); ?>
        </a>
        <a href="<?php echo $this->Html->url(array('action' => 'index', '?' => array('type' => 'payments'))); ?>" class="wv-notif-filter-btn <?php echo ($type === 'payments') ? 'active' : ''; ?>">
            <i class="fa fa-money"></i> <?php echo __('Payments & Invoices'); ?>
        </a>
        <a href="<?php echo $this->Html->url(array('action' => 'index', '?' => array('type' => 'letters'))); ?>" class="wv-notif-filter-btn <?php echo ($type === 'letters') ? 'active' : ''; ?>">
            <i class="fa fa-file-text-o"></i> <?php echo __('Official Letters'); ?>
        </a>
        <a href="<?php echo $this->Html->url(array('action' => 'index', '?' => array('type' => 'balloting'))); ?>" class="wv-notif-filter-btn <?php echo ($type === 'balloting') ? 'active' : ''; ?>">
            <i class="fa fa-cube"></i> <?php echo __('e-Balloting Allotments'); ?>
        </a>
        <a href="<?php echo $this->Html->url(array('action' => 'index', '?' => array('type' => 'reminders'))); ?>" class="wv-notif-filter-btn <?php echo ($type === 'reminders') ? 'active' : ''; ?>">
            <i class="fa fa-clock-o"></i> <?php echo __('Due Reminders'); ?>
        </a>
    </div>

    <!-- Notifications List -->
    <?php if(empty($notifications)): ?>
    <div class="wv-notif-empty">
        <i class="fa fa-bell-slash-o"></i>
        <h4 style="font-weight:800; color:#0f172a; margin-bottom:6px;"><?php echo __('No Notifications Found'); ?></h4>
        <p><?php echo __('You are all caught up! When new receipts, letters, or balloting updates arrive, they will appear here.'); ?></p>
    </div>
    <?php else: ?>
    <div class="wv-notif-list">
        <?php foreach($notifications as $row):
            $n = $row['ClientNotification'];
            $isUnread = empty($n['is_read']);
            $icon = 'fa-bell';
            $bg = '#eff6ff';
            $color = '#3b82f6';

            if ($n['type'] === 'payment_received') {
                $icon = 'fa-check-circle';
                $bg = '#ecfdf5';
                $color = '#10b981';
            } elseif ($n['type'] === 'payment_overdue') {
                $icon = 'fa-exclamation-triangle';
                $bg = '#fff1f2';
                $color = '#f43f5e';
            } elseif ($n['type'] === 'letter_issued') {
                $icon = 'fa-file-text-o';
                $bg = '#f5f3ff';
                $color = '#8b5cf6';
            } elseif ($n['type'] === 'balloting_allotment') {
                $icon = 'fa-cube';
                $bg = '#fffbeb';
                $color = '#f59e0b';
            } elseif ($n['type'] === 'installment_reminder') {
                $icon = 'fa-calendar';
                $bg = '#ecfeff';
                $color = '#06b6d4';
            }
        ?>
        <div class="wv-notif-card <?php echo $isUnread ? 'unread' : ''; ?>">
            <div class="wv-notif-icon-wrap" style="background:<?php echo $bg; ?>; color:<?php echo $color; ?>;">
                <i class="fa <?php echo $icon; ?>"></i>
            </div>
            <div class="wv-notif-content">
                <div class="wv-notif-top">
                    <h4 class="wv-notif-title">
                        <?php echo h($n['title']); ?>
                        <?php if($isUnread): ?>
                        <span class="badge" style="background:#ff0a46; font-size:10px; margin-left:6px;"><?php echo __('NEW'); ?></span>
                        <?php endif; ?>
                    </h4>
                    <span class="wv-notif-time"><?php echo CakeTime::timeAgoInWords($n['created']); ?></span>
                </div>
                <p class="wv-notif-message"><?php echo h($n['message']); ?></p>
                <div class="wv-notif-actions">
                    <?php if(!empty($n['action_url'])): ?>
                    <a href="<?php echo h($n['action_url']); ?>" class="wv-notif-action-btn">
                        <?php echo __('View Details'); ?> <i class="fa fa-arrow-right"></i>
                    </a>
                    <?php endif; ?>
                    <?php if($isUnread): ?>
                    <a href="<?php echo $this->Html->url(array('action' => 'mark_read', $n['id'])); ?>" class="btn btn-xs btn-default" style="border-radius:999px; font-size:11px; font-weight:700;">
                        <i class="fa fa-check"></i> <?php echo __('Mark as Read'); ?>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
