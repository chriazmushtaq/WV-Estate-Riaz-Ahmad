<div class="page-title">
    <h2><span class="fa fa-users"></span> <?php echo __('Client Mobile App Users & Activity'); ?></h2>
</div>

<div class="page-content-wrap">
    <?php echo $this->element('app_management_nav'); ?>

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default" style="border-radius: 8px;">
                <div class="panel-heading">
                    <h3 class="panel-title"><?php echo __('Registered Client App Users'); ?></h3>
                </div>
                <div class="panel-body table-responsive" style="padding: 0;">
                    <table class="table table-bordered table-striped table-hover" style="margin-bottom: 0;">
                        <thead>
                            <tr style="background: #F1F5F9;">
                                <th style="width: 70px;"><?php echo __('ID'); ?></th>
                                <th><?php echo __('Client Name'); ?></th>
                                <th><?php echo __('Father / Guardian'); ?></th>
                                <th><?php echo __('Phone / WhatsApp'); ?></th>
                                <th><?php echo __('Email'); ?></th>
                                <th><?php echo __('App Status'); ?></th>
                                <th><?php echo __('Last App Login'); ?></th>
                                <th style="width: 120px; text-align: center;"><?php echo __('Quick Action'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($clients)): ?>
                                <?php foreach ($clients as $c): ?>
                                    <?php $cli = $c['Client']; ?>
                                    <tr>
                                        <td>#<?php echo $cli['id']; ?></td>
                                        <td><strong style="color: #0F172A;"><?php echo h($cli['name']); ?></strong></td>
                                        <td>
                                            <?php
                                            $fatherName = !empty($cli['fathername']) ? trim((string)$cli['fathername']) : '';
                                            $relationType = !empty($cli['relation_type']) ? trim((string)$cli['relation_type']) : '';
                                            if($fatherName !== ''){
                                                echo '<strong style="color:#1e293b;">' . h($fatherName) . '</strong>';
                                                if($relationType !== ''){
                                                    echo '<small class="text-muted" style="display:block;font-size:11px;font-weight:600;">' . h($relationType) . '</small>';
                                                }
                                            } else {
                                                echo '<span class="text-muted">-</span>';
                                            }
                                            ?>
                                        </td>
                                        <td><?php echo h(!empty($cli['phone']) ? $cli['phone'] : '-'); ?></td>
                                        <td><?php echo h(!empty($cli['email']) ? $cli['email'] : '-'); ?></td>
                                        <td>
                                            <?php if (!empty($cli['app_login_status'])): ?>
                                                <span class="label label-success"><i class="fa fa-check-circle"></i> <?php echo __('Logged In'); ?></span>
                                            <?php else: ?>
                                                <span class="label label-default"><?php echo __('Not Logged In'); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <small style="color: #64748B; font-weight: 500;">
                                                <?php echo !empty($cli['app_last_login']) ? date('d-M-Y h:i A', strtotime($cli['app_last_login'])) : __('Never'); ?>
                                            </small>
                                        </td>
                                        <td class="text-center">
                                            <?php echo $this->Html->link(
                                                '<i class="fa fa-paper-plane"></i> ' . __('Notify'),
                                                array('action' => 'send_notification', '?' => array('client_id' => $cli['id'])),
                                                array('escape' => false, 'class' => 'btn btn-primary btn-xs')
                                            ); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center" style="padding: 30px; color: #94A3B8;">
                                        <?php echo __('No client app users found.'); ?>
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
