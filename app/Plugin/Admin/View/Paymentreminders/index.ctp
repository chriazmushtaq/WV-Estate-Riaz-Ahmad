<?php
$currencySymbol = isset($currency) ? $currency : '';
$paging = !empty($this->request->params['paging']['PlansPayment']) ? $this->request->params['paging']['PlansPayment'] : array();
$limitOptions = array(10, 20, 30, 50, 100, 200, 500);
$currentLimit = !empty($paging['limit']) ? (int)$paging['limit'] : (!empty($this->request->query['limit']) ? (int)$this->request->query['limit'] : 30);
if(!in_array($currentLimit, $limitOptions, true))
{
    $limitOptions[] = $currentLimit;
    sort($limitOptions);
}
$pagerQuery = (array)$this->request->query;
unset($pagerQuery['page']);
$page = !empty($paging['page']) ? (int)$paging['page'] : 1;
$pageCount = !empty($paging['pageCount']) ? (int)$paging['pageCount'] : 1;
$count = !empty($paging['count']) ? (int)$paging['count'] : count((array)$rows);
$start = $count > 0 ? (($page - 1) * $currentLimit) + 1 : 0;
$end = min($count, $page * $currentLimit);
?>
<style type="text/css">
.wv-reminder-page{color:#06162b;}
.wv-reminder-alert{border-radius:8px;display:none;font-size:13px;font-weight:800;margin:0 0 10px;padding:10px 12px;}
.wv-reminder-alert.is-success{background:#e8f7ef;border:1px solid #bfe8cf;color:#08733d;}
.wv-reminder-alert.is-error{background:#fff0f2;border:1px solid #ffc3cc;color:#a70029;}
.wv-reminder-top{align-items:center;background:#fff;border:1px solid #dbe4ee;border-radius:8px;display:flex;gap:10px;justify-content:space-between;margin-bottom:10px;padding:10px 12px;}
.wv-reminder-top strong{font-size:13px;font-weight:900;}
.wv-reminder-top span{color:#526982;font-size:12px;font-weight:700;}
.wv-reminder-stats{display:grid;gap:8px;grid-template-columns:repeat(3,minmax(0,1fr));margin-bottom:10px;}
.wv-reminder-stat{background:#fff;border:1px solid #dbe4ee;border-radius:8px;box-shadow:0 10px 24px rgba(7,21,42,.06);padding:10px 12px;}
.wv-reminder-stat span{color:#5c7088;display:block;font-size:11px;font-weight:900;text-transform:uppercase;}
.wv-reminder-stat strong{color:#9f002d;display:block;font-size:24px;font-weight:900;line-height:1.1;margin-top:4px;}
.wv-reminder-stat small{color:#526982;font-weight:800;}
.wv-reminder-filter,.wv-reminder-panel{background:#fff;border:1px solid #dbe4ee;border-radius:8px;margin-bottom:10px;padding:10px 12px;}
.wv-reminder-filter-grid{align-items:end;display:grid;gap:8px;grid-template-columns:minmax(155px,1fr) 90px minmax(220px,1.4fr) minmax(145px,1fr) 130px 130px 110px 120px;}
.wv-reminder-filter label{color:#24384f;font-size:11px;font-weight:900;margin-bottom:4px;text-transform:uppercase;}
.wv-reminder-tabs{border-bottom:1px solid #dbe4ee;margin-bottom:10px;}
.wv-reminder-tabs>li>a{font-weight:900;}
.wv-reminder-table{min-width:1120px;table-layout:auto;width:100%;}
.wv-reminder-table th{background:#07152a;color:#fff;font-size:11px;text-transform:uppercase;white-space:nowrap;}
.wv-reminder-table td{font-size:12px;vertical-align:middle!important;}
.wv-reminder-table .wv-cell-check{width:36px;text-align:center;}
.wv-reminder-table .wv-cell-sr{width:44px;text-align:center;white-space:nowrap;}
.wv-reminder-table .wv-cell-client{min-width:230px;}
.wv-reminder-table .wv-cell-due{min-width:130px;white-space:nowrap;}
.wv-reminder-table .wv-cell-amount{min-width:125px;white-space:nowrap;}
.wv-reminder-table .wv-cell-status{min-width:112px;white-space:nowrap;}
.wv-reminder-table .wv-cell-send{min-width:118px;text-align:center;white-space:nowrap;}
.wv-reminder-muted{color:#60758c;font-size:11px;font-weight:700;}
.wv-reminder-send-actions{display:inline-flex;gap:5px;justify-content:center;}
.wv-reminder-send-btn{align-items:center;border:1px solid #cbd8e6;border-radius:6px;color:#0d2747;display:inline-flex;height:30px;justify-content:center;text-decoration:none!important;width:30px;}
.wv-reminder-send-btn:hover,.wv-reminder-send-btn:focus{background:#eff6ff;border-color:#99b8dc;color:#07152a;}
.wv-reminder-send-btn.is-busy{opacity:.65;pointer-events:none;}
.wv-reminder-send-btn.is-success{background:#e9f8ef;border-color:#29a665;color:#09743d;}
.wv-reminder-send-btn.is-error{background:#fff0f2;border-color:#e15a6b;color:#a70029;}
.wv-reminder-status{border-radius:999px;color:#fff;display:inline-block;font-size:11px;font-weight:900;padding:4px 8px;}
.wv-reminder-status-overdue{background:#c51f32}.wv-reminder-status-due{background:#2477c7}.wv-reminder-status-sent{background:#168a3a}.wv-reminder-status-failed{background:#c51f32}.wv-reminder-status-opened,.wv-reminder-status-prepared{background:#2477c7}
.wv-bulk-bar{align-items:center;display:flex;flex-wrap:wrap;gap:6px;justify-content:space-between;margin-bottom:8px;}
.wv-bulk-bar .btn{font-weight:900;}
.wv-reminder-promise{height:30px;max-width:116px;}
.wv-reminder-pager{align-items:center;display:flex;flex-wrap:wrap;gap:10px;justify-content:space-between;margin-top:10px;}
.wv-reminder-pager form{margin:0}.wv-reminder-pager label{font-size:12px;font-weight:900;margin:0}.wv-reminder-pager select{height:30px;margin:0 4px;min-width:66px}
@media(max-width:1299px){.wv-reminder-filter-grid{grid-template-columns:repeat(4,minmax(0,1fr));}.wv-reminder-stats{grid-template-columns:repeat(3,minmax(0,1fr));}}
@media(max-width:767px){.wv-reminder-top{align-items:stretch;flex-direction:column}.wv-reminder-filter-grid,.wv-reminder-stats{grid-template-columns:1fr;}}
</style>

<div class="wv-reminder-page">
    <?php echo $this->Session->flash(); ?>
    <div id="wvReminderAjaxStatus" class="wv-reminder-alert"></div>

    <div class="wv-reminder-top">
        <div>
            <strong><?php echo __('Auto Reminder'); ?>: <?php echo !empty($autoSettings['auto_enabled']) ? __('Enabled') : __('Disabled'); ?></strong><br>
            <span><?php echo __('Channels'); ?>:
                <?php
                $channels = array();
                if(!empty($autoSettings['send_sms'])) $channels[] = 'SMS';
                if(!empty($autoSettings['send_email'])) $channels[] = 'Email';
                if(!empty($autoSettings['send_whatsapp'])) $channels[] = 'WhatsApp';
                echo h(!empty($channels) ? implode(', ', $channels) : __('None'));
                ?>
                | <?php echo __('Days before'); ?>: <?php echo !empty($autoSettings['days_before']) ? (int)$autoSettings['days_before'] : 0; ?>
                | <?php echo __('Repeat after'); ?>: <?php echo !empty($autoSettings['repeat_after_days']) ? (int)$autoSettings['repeat_after_days'] : 0; ?> <?php echo __('days'); ?>
            </span>
        </div>
        <div>
            <?php echo $this->Html->link('<span class="fa fa-cog"></span> '.__('Settings'), array('action'=>'settings'), array('escape'=>false,'class'=>'btn btn-default btn-sm')); ?>
            <?php echo $this->Html->link('<span class="fa fa-play"></span> '.__('Run Now'), array('action'=>'run'), array('escape'=>false,'class'=>'btn btn-primary btn-sm')); ?>
        </div>
    </div>

    <div class="wv-reminder-stats">
        <div class="wv-reminder-stat"><span><?php echo __('Total Unpaid In Filter'); ?></span><strong><?php echo (int)Hash::get($summary, 'total', 0); ?></strong></div>
        <div class="wv-reminder-stat"><span><?php echo __('Before Due Date'); ?></span><strong><?php echo (int)Hash::get($summary, 'due_soon', 0); ?></strong></div>
        <div class="wv-reminder-stat"><span><?php echo __('Overdue Unpaid'); ?></span><strong><?php echo (int)Hash::get($summary, 'overdue', 0); ?></strong><small><?php echo (int)Hash::get($summary, 'overdue_deals', 0); ?> <?php echo __('deal reminders'); ?></small></div>
    </div>

    <div class="wv-reminder-filter">
        <form method="get" action="<?php echo $this->Html->url(array('controller'=>'Paymentreminders','action'=>'index')); ?>">
            <div class="wv-reminder-filter-grid">
                <div class="form-group">
                    <label><?php echo __('Reminder Type'); ?></label>
                    <select name="status" class="form-control">
                        <option value="all" <?php echo $status === 'all' ? 'selected="selected"' : ''; ?>><?php echo __('All Due + Overdue'); ?></option>
                        <option value="due_soon" <?php echo $status === 'due_soon' ? 'selected="selected"' : ''; ?>><?php echo __('Before Due'); ?></option>
                        <option value="overdue" <?php echo $status === 'overdue' ? 'selected="selected"' : ''; ?>><?php echo __('Overdue'); ?></option>
                    </select>
                </div>
                <div class="form-group">
                    <label><?php echo __('Days Before'); ?></label>
                    <input type="number" name="days_before" min="0" max="90" class="form-control" value="<?php echo (int)$daysBefore; ?>" />
                </div>
                <div class="form-group">
                    <label><?php echo __('Search'); ?></label>
                    <input type="text" name="keyword" class="form-control" value="<?php echo h($keyword); ?>" placeholder="<?php echo __('Client, file, property, agency'); ?>" />
                </div>
                <div class="form-group">
                    <label><?php echo __('Promise Date Filter'); ?></label>
                    <select name="promise_filter" class="form-control">
                        <?php foreach(array('all'=>__('All Promise Dates'), 'day'=>__('Today'), 'week'=>__('This Week'), 'month'=>__('This Month'), 'year'=>__('This Year'), 'custom'=>__('Custom')) as $filterKey => $filterName){ ?>
                            <option value="<?php echo h($filterKey); ?>" <?php echo $promiseFilter === $filterKey ? 'selected="selected"' : ''; ?>><?php echo h($filterName); ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-group">
                    <label><?php echo __('Promise Start Date'); ?></label>
                    <input type="text" name="promise_start_date" class="form-control js-reminder-date" value="<?php echo !empty($promiseStartDate) ? h(date('d-M-Y', strtotime($promiseStartDate))) : ''; ?>" placeholder="DD-MMM-YYYY" />
                </div>
                <div class="form-group">
                    <label><?php echo __('Promise End Date'); ?></label>
                    <input type="text" name="promise_end_date" class="form-control js-reminder-date" value="<?php echo !empty($promiseEndDate) ? h(date('d-M-Y', strtotime($promiseEndDate))) : ''; ?>" placeholder="DD-MMM-YYYY" />
                </div>
                <div class="form-group">
                    <label>&nbsp;</label>
                    <button class="btn btn-primary btn-block" type="submit"><span class="fa fa-search"></span> <?php echo __('Search'); ?></button>
                </div>
                <div class="form-group">
                    <label>&nbsp;</label>
                    <?php echo $this->Html->link('<span class="fa fa-refresh"></span> '.__('Reset'), array('action'=>'index'), array('escape'=>false,'class'=>'btn btn-default btn-block')); ?>
                </div>
            </div>
        </form>
    </div>

    <div class="wv-reminder-panel">
        <ul class="nav nav-tabs wv-reminder-tabs">
            <li class="active"><a href="#wvReminderList" data-toggle="tab"><?php echo __('Reminders'); ?></a></li>
            <li><a href="#wvReminderLog" data-toggle="tab"><?php echo __('Recent Reminder Log'); ?></a></li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane active" id="wvReminderList">
                <?php echo $this->Form->create('PaymentReminder', array('url'=>array('controller'=>'Paymentreminders','action'=>'bulk'), 'id'=>'wvReminderBulkForm')); ?>
                <div class="wv-bulk-bar">
                    <div class="wv-reminder-muted"><?php echo __('Select records, then send selected reminders.'); ?></div>
                    <div>
                        <button type="submit" name="data[PaymentReminder][channel]" value="sms" class="btn btn-default btn-sm"><span class="fa fa-comment"></span> <?php echo __('SMS'); ?></button>
                        <button type="submit" name="data[PaymentReminder][channel]" value="whatsapp" class="btn btn-default btn-sm"><span class="fa fa-whatsapp"></span> <?php echo __('WhatsApp'); ?></button>
                        <button type="submit" name="data[PaymentReminder][channel]" value="email" class="btn btn-default btn-sm"><span class="fa fa-envelope"></span> <?php echo __('Email'); ?></button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped wv-reminder-table">
                        <thead>
                            <tr>
                                <th class="wv-cell-check"><input type="checkbox" id="wvReminderCheckAll" /></th>
                                <th class="wv-cell-sr"><?php echo __('#'); ?></th>
                                <th class="wv-cell-client"><?php echo __('Client / Deal'); ?></th>
                                <th><?php echo __('Father / Guardian'); ?></th>
                                <th><?php echo __('Property'); ?></th>
                                <th class="wv-cell-due"><?php echo __('Due Date'); ?></th>
                                <th><?php echo __('Installment'); ?></th>
                                <th class="wv-cell-amount"><?php echo __('Amount'); ?></th>
                                <th><?php echo __('Promise Date'); ?></th>
                                <th class="wv-cell-status"><?php echo __('Status'); ?></th>
                                <th class="wv-cell-send"><?php echo __('Send'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if(!empty($rows)){ $sr = $start > 0 ? $start : 1; foreach($rows as $row){
                            $planId = !empty($row['_ReminderAggregate']['plan_ids'][0]) ? (int)$row['_ReminderAggregate']['plan_ids'][0] : (int)Hash::get($row, 'PlansPayment.id', 0);
                            $dealId = (int)Hash::get($row, 'Deal.id', 0);
                            $dueDate = Hash::get($row, 'PlansPayment.date', '');
                            $isOverdue = ($dueDate !== '' && $dueDate < $today);
                            $promiseDate = Hash::get($row, 'PlansPayment.recovery_promise_date', '');
                            $smsUrl = $this->Html->url(array('controller'=>'Paymentreminders','action'=>'sendsms',$planId,'?'=>array('ajax'=>1)));
                            $whatsappUrl = $this->Html->url(array('controller'=>'Paymentreminders','action'=>'whatsapp',$planId,'?'=>array('ajax'=>1)));
                            $emailUrl = $this->Html->url(array('controller'=>'Paymentreminders','action'=>'sendemail',$planId,'?'=>array('ajax'=>1)));
                        ?>
                            <tr>
                                <td class="wv-cell-check"><input type="checkbox" class="wv-reminder-check" name="data[PaymentReminder][plan_ids][]" value="<?php echo (int)$planId; ?>" /></td>
                                <td class="wv-cell-sr"><?php echo (int)$sr++; ?></td>
                                <td class="wv-cell-client">
                                    <strong><?php echo $this->Html->link(h(Hash::get($row, 'Client.name', '-')), array('controller'=>'deals_payments','action'=>'index',$dealId), array('escape'=>false)); ?></strong><br>
                                    <span class="wv-reminder-muted"><?php echo h(Hash::get($row, 'Deal.invoice_no', '-')); ?></span><br>
                                    <span class="fa fa-phone"></span> <?php echo h(!empty($row['Client']['mobile']) ? $row['Client']['mobile'] : Hash::get($row, 'Client.phone', '-')); ?>
                                </td>
                                <td>
                                    <?php
                                    $fatherName = !empty($row['Client']['fathername']) ? trim((string)$row['Client']['fathername']) : '';
                                    $relationType = !empty($row['Client']['relation_type']) ? trim((string)$row['Client']['relation_type']) : '';
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
                                <td><?php echo h(Hash::get($row, 'Property.name', '-')); ?><br><span class="wv-reminder-muted"><?php echo h(Hash::get($row, 'Project.name', '-')); ?></span></td>
                                <td class="wv-cell-due"><strong><?php echo !empty($dueDate) ? h(date('d-M-Y', strtotime($dueDate))) : '-'; ?></strong></td>
                                <td><?php echo h(Hash::get($row, 'PlansPayment.name', '-')); ?></td>
                                <td class="wv-cell-amount"><strong><?php echo h($currencySymbol.number_format((float)Hash::get($row, 'PlansPayment.amount', 0))); ?></strong></td>
                                <td><input type="text" class="form-control wv-reminder-promise js-reminder-date js-reminder-promise" data-plan-id="<?php echo (int)$planId; ?>" value="<?php echo !empty($promiseDate) && $promiseDate !== '0000-00-00' ? h(date('d-M-Y', strtotime($promiseDate))) : ''; ?>" placeholder="DD-MMM-YYYY" /></td>
                                <td class="wv-cell-status"><span class="wv-reminder-status <?php echo $isOverdue ? 'wv-reminder-status-overdue' : 'wv-reminder-status-due'; ?>"><?php echo $isOverdue ? __('Overdue') : __('Due'); ?></span></td>
                                <td class="wv-cell-send">
                                    <div class="wv-reminder-send-actions">
                                        <a href="#" class="wv-reminder-send-btn js-reminder-send-btn" data-method="post" data-url="<?php echo h($smsUrl); ?>" title="<?php echo __('Send SMS Reminder'); ?>"><span class="fa fa-comment"></span></a>
                                        <a href="#" class="wv-reminder-send-btn js-reminder-send-btn" data-method="get" data-url="<?php echo h($whatsappUrl); ?>" title="<?php echo __('Send WhatsApp Reminder'); ?>"><span class="fa fa-whatsapp"></span></a>
                                        <a href="#" class="wv-reminder-send-btn js-reminder-send-btn" data-method="post" data-url="<?php echo h($emailUrl); ?>" title="<?php echo __('Send Email Reminder'); ?>"><span class="fa fa-envelope"></span></a>
                                    </div>
                                </td>
                            </tr>
                        <?php }} else { ?>
                            <tr><td colspan="10" class="text-center"><?php echo __('No reminder found for this filter.'); ?></td></tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
                <?php echo $this->Form->end(); ?>

                <div class="wv-reminder-pager">
                    <form method="get" action="<?php echo $this->Html->url(array('controller'=>'Paymentreminders','action'=>'index')); ?>">
                        <?php foreach($pagerQuery as $k=>$v){ if($k === 'limit' || is_array($v)) continue; echo '<input type="hidden" name="'.h($k).'" value="'.h($v).'" />'; } ?>
                        <label><small><?php echo __('Show'); ?></small>
                            <select name="limit" onchange="this.form.submit();">
                                <?php foreach($limitOptions as $limitOption){ ?>
                                    <option value="<?php echo (int)$limitOption; ?>" <?php echo (int)$limitOption === (int)$currentLimit ? 'selected="selected"' : ''; ?>><?php echo (int)$limitOption; ?></option>
                                <?php } ?>
                            </select>
                            <small><?php echo __('entries'); ?></small>
                        </label>
                    </form>
                    <div>
                        <?php
                        $prevQuery = $pagerQuery; $prevQuery['limit'] = $currentLimit; $prevQuery['page'] = max(1, $page - 1);
                        $nextQuery = $pagerQuery; $nextQuery['limit'] = $currentLimit; $nextQuery['page'] = min($pageCount, $page + 1);
                        echo $this->Html->link('&larr; '.__('Prev'), array('action'=>'index','?'=>$prevQuery), array('escape'=>false,'class'=>'btn btn-default btn-sm '.(($page<=1)?'disabled':'')));
                        echo ' <span class="wv-reminder-muted">'.sprintf(__('Showing %s to %s of %s entries'), (int)$start, (int)$end, (int)$count).'</span> ';
                        echo $this->Html->link(__('Next').' &rarr;', array('action'=>'index','?'=>$nextQuery), array('escape'=>false,'class'=>'btn btn-default btn-sm '.(($page>=$pageCount)?'disabled':'')));
                        ?>
                    </div>
                </div>
            </div>

            <div class="tab-pane" id="wvReminderLog">
                <div class="wv-bulk-bar">
                    <strong><?php echo __('Recent Reminder Log'); ?></strong>
                    <?php if(!empty($canClearReminderLogs)){ ?>
                        <?php echo $this->Form->postLink('<span class="fa fa-trash"></span> '.__('Clear Recent Logs'), array('action'=>'clearlogs'), array('escape'=>false,'class'=>'btn btn-danger btn-sm','confirm'=>__('Are you sure you want to clear reminder logs?'))); ?>
                    <?php } ?>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped wv-reminder-table">
                        <thead>
                            <tr>
                                <th><?php echo __('#'); ?></th>
                                <th><?php echo __('Date / Time'); ?></th>
                                <th><?php echo __('Client'); ?></th>
                                <th><?php echo __('Channel'); ?></th>
                                <th><?php echo __('Amount'); ?></th>
                                <th><?php echo __('Status'); ?></th>
                                <th><?php echo __('Error'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if(!empty($history)){ $hsr = 1; foreach($history as $historyRow){ $log = $historyRow['PaymentReminder']; ?>
                            <tr>
                                <td><?php echo (int)$hsr++; ?></td>
                                <td><?php echo !empty($log['created']) ? h(date('d-M-Y h:i A', strtotime($log['created']))) : '-'; ?></td>
                                <td><?php echo h(!empty($log['recipient_name']) ? $log['recipient_name'] : '-'); ?></td>
                                <td><span class="fa fa-<?php echo strtolower($log['channel']) === 'email' ? 'envelope' : (strtolower($log['channel']) === 'whatsapp' ? 'whatsapp' : 'comment'); ?>"></span> <?php echo h(strtoupper($log['channel'])); ?></td>
                                <td><strong><?php echo h($currencySymbol.number_format((float)$log['amount'])); ?></strong></td>
                                <td><span class="wv-reminder-status wv-reminder-status-<?php echo h(strtolower($log['status'])); ?>"><?php echo h(ucfirst($log['status'])); ?></span></td>
                                <td><?php echo h(!empty($log['error_text']) ? $log['error_text'] : '-'); ?></td>
                            </tr>
                        <?php }} else { ?>
                            <tr><td colspan="7" class="text-center"><?php echo __('No reminder has been sent yet.'); ?></td></tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
(function($){
    function showReminderMessage(success, message){
        var box = $('#wvReminderAjaxStatus');
        box.removeClass('is-success is-error').addClass(success ? 'is-success' : 'is-error').text(message || '').stop(true, true).fadeIn(120);
    }

    if($.fn.datepicker){
        $('.js-reminder-date').datepicker({format:'dd-M-yyyy',autoclose:true,todayHighlight:true});
    } else if($.fn.datetimepicker){
        $('.js-reminder-date').datetimepicker({format:'DD-MMM-YYYY',useCurrent:false});
    }

    $('#wvReminderCheckAll').on('change', function(){
        $('.wv-reminder-check').prop('checked', $(this).is(':checked'));
    });

    $(document).on('click', '.js-reminder-send-btn', function(e){
        e.preventDefault();
        var btn = $(this);
        if(btn.hasClass('is-busy')) return false;
        btn.removeClass('is-success is-error').addClass('is-busy');
        $.ajax({
            url: btn.data('url'),
            type: (btn.data('method') || 'post').toUpperCase(),
            dataType: 'json',
            data: {ajax: 1},
            headers: {'X-Requested-With': 'XMLHttpRequest'}
        }).done(function(response){
            var ok = !!(response && response.success);
            btn.removeClass('is-busy').addClass(ok ? 'is-success' : 'is-error');
            if(ok && response.url){
                window.open(response.url, '_blank');
            }
            showReminderMessage(ok, response && response.message ? response.message : (ok ? '<?php echo __('Reminder sent.'); ?>' : '<?php echo __('Unable to send reminder.'); ?>'));
        }).fail(function(xhr){
            btn.removeClass('is-busy').addClass('is-error');
            showReminderMessage(false, xhr && xhr.statusText ? xhr.statusText : '<?php echo __('Unable to send reminder.'); ?>');
        });
        return false;
    });

    $(document).on('change', '.js-reminder-promise', function(){
        var input = $(this);
        $.ajax({
            url: '<?php echo $this->Html->url(array('controller'=>'Paymentreminders','action'=>'savepromise')); ?>',
            type: 'POST',
            dataType: 'json',
            data: {ajax: 1, plan_id: input.data('plan-id'), promise_date: input.val()},
            headers: {'X-Requested-With': 'XMLHttpRequest'}
        }).done(function(response){
            showReminderMessage(!!(response && response.success), response && response.message ? response.message : '');
            if(response && response.success && response.display){
                input.val(response.display);
            }
        }).fail(function(xhr){
            showReminderMessage(false, xhr && xhr.statusText ? xhr.statusText : '<?php echo __('Promise date could not be saved.'); ?>');
        });
    });
})(jQuery);
</script>
