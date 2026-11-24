<div class="page-title">
    <h2><span class="fa fa-history"></span> <?php echo __('In-App Broadcast Notifications History'); ?></h2>
</div>

<div class="page-content-wrap">
    <?php echo $this->element('app_management_nav'); ?>

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default" style="border-radius: 8px;">
                <div class="panel-heading">
                    <h3 class="panel-title"><?php echo __('Dispatched Broadcasts Log'); ?></h3>
                    <div class="pull-right">
                        <?php echo $this->Html->link('<i class="fa fa-paper-plane"></i> ' . __('Send New Notification'), array('action' => 'send_notification'), array('escape' => false, 'class' => 'btn btn-success btn-sm')); ?>
                    </div>
                </div>
                <div class="panel-body table-responsive" style="padding: 0;">
                    <table class="table table-bordered table-striped table-hover" style="margin-bottom: 0;">
                        <thead>
                            <tr style="background: #F1F5F9;">
                                <th style="width: 60px;"><?php echo __('ID'); ?></th>
                                <th><?php echo __('Title & Content'); ?></th>
                                <th><?php echo __('Type'); ?></th>
                                <th><?php echo __('Target Audience'); ?></th>
                                <th><?php echo __('Total Sent'); ?></th>
                                <th><?php echo __('Date Sent'); ?></th>
                                <th style="width: 100px; text-align: center;"><?php echo __('Actions'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($broadcasts)): ?>
                                <?php foreach ($broadcasts as $b): ?>
                                    <?php $item = $b['AppNotificationBroadcast']; ?>
                                    <tr>
                                        <td>#<?php echo $item['id']; ?></td>
                                        <td>
                                            <strong style="color: #0F172A; font-size: 13px;"><?php echo h($item['title']); ?></strong><br>
                                            <small style="color: #475569;"><?php echo nl2br(h($item['message'])); ?></small>
                                            <?php if (!empty($item['action_route'])): ?>
                                                <div style="margin-top: 4px;">
                                                    <span class="label label-default" style="font-size: 9.5px;"><i class="fa fa-link"></i> Action: <?php echo h($item['action_route']); ?></span>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="label label-primary"><?php echo h(strtoupper(str_replace('_', ' ', $item['type']))); ?></span>
                                        </td>
                                        <td>
                                            <span class="label label-info"><?php echo h(strtoupper(str_replace('_', ' ', $item['target_audience']))); ?></span>
                                        </td>
                                        <td>
                                            <span class="badge badge-success" style="font-size: 12px; font-weight: bold;"><?php echo (int)$item['total_sent']; ?></span>
                                        </td>
                                        <td>
                                            <small style="color: #64748B; font-weight: 500;"><?php echo !empty($item['created']) ? date('d-M-Y h:i A', strtotime($item['created'])) : '-'; ?></small>
                                        </td>
                                        <td class="text-center">
                                            <?php echo $this->Form->postLink(
                                                '<i class="fa fa-trash-o"></i> ' . __('Delete'),
                                                array('action' => 'delete_broadcast', $item['id']),
                                                array('escape' => false, 'class' => 'btn btn-danger btn-xs', 'confirm' => __('Are you sure you want to delete this broadcast log?'))
                                            ); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center" style="padding: 30px; color: #94A3B8;">
                                        <i class="fa fa-paper-plane-o fa-2x"></i><br><br>
                                        <?php echo __('No broadcast notifications have been sent yet.'); ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php if ($this->Paginator->hasPage(2)): ?>
                    <div class="panel-footer">
                        <ul class="pagination pagination-sm pull-right">
                            <?php
                            echo $this->Paginator->prev('«', array('tag' => 'li'), null, array('tag' => 'li', 'class' => 'disabled', 'disabledTag' => 'a'));
                            echo $this->Paginator->numbers(array('separator' => '', 'currentTag' => 'a', 'currentClass' => 'active', 'tag' => 'li', 'first' => 1));
                            echo $this->Paginator->next('»', array('tag' => 'li'), null, array('tag' => 'li', 'class' => 'disabled', 'disabledTag' => 'a'));
                            ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
