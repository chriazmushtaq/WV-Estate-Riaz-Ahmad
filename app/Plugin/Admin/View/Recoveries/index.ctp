<?php
$currencySymbol = isset($currency) ? $currency : '';
$limitOptions = array(10, 20, 30, 50, 100, 200, 500);
$pagerQuery = (array)$query;
unset($pagerQuery['page']);
$currentLimit = !empty($pagination['limit']) ? (int)$pagination['limit'] : 30;
if(!in_array($currentLimit, $limitOptions, true))
{
    $limitOptions[] = $currentLimit;
    sort($limitOptions);
}
?>
<style type="text/css">
.wv-recovery-page{color:#06162b;}
.wv-recovery-head{align-items:center;background:linear-gradient(135deg,#07152a,#0d2747 58%,#087b63);border-radius:8px;color:#fff;display:flex;gap:12px;justify-content:space-between;margin-bottom:10px;padding:12px 14px;}
.wv-recovery-head h2{font-size:20px;font-weight:900;margin:0;}
.wv-recovery-head p{color:#dceeff;font-size:12px;font-weight:700;margin:2px 0 0;}
.wv-recovery-actions .btn{font-weight:900;margin-left:4px;}
.wv-recovery-alert{border-radius:8px;display:none;font-size:13px;font-weight:800;margin:0 0 10px;padding:10px 12px;}
.wv-recovery-alert.is-success{background:#e8f7ef;border:1px solid #bfe8cf;color:#08733d;}
.wv-recovery-alert.is-error{background:#fff0f2;border:1px solid #ffc3cc;color:#a70029;}
.wv-recovery-stats{display:grid;gap:8px;grid-template-columns:repeat(5,minmax(0,1fr));margin-bottom:10px;}
.wv-recovery-stat{background:#fff;border:1px solid #dbe4ee;border-radius:8px;box-shadow:0 10px 24px rgba(7,21,42,.06);padding:10px 12px;}
.wv-recovery-stat span{color:#5c7088;display:block;font-size:11px;font-weight:900;text-transform:uppercase;}
.wv-recovery-stat strong{color:#9f002d;display:block;font-size:23px;font-weight:900;line-height:1.1;margin-top:4px;}
.wv-recovery-filter,.wv-recovery-panel{background:#fff;border:1px solid #dbe4ee;border-radius:8px;margin-bottom:10px;padding:10px 12px;}
.wv-recovery-filter-grid{align-items:end;display:grid;gap:8px;grid-template-columns:1fr 1fr 1fr 1fr 1.5fr 84px 112px;}
.wv-recovery-filter label{color:#24384f;font-size:11px;font-weight:900;margin-bottom:4px;text-transform:uppercase;}
.wv-recovery-table{min-width:1180px;table-layout:auto;width:100%;}
.wv-recovery-table th{background:#07152a;color:#fff;font-size:11px;text-transform:uppercase;white-space:nowrap;}
.wv-recovery-table td{font-size:12px;vertical-align:middle!important;}
.wv-recovery-table .wv-cell-sr{width:46px;text-align:center;white-space:nowrap;}
.wv-recovery-table .wv-cell-client{min-width:260px;}
.wv-recovery-table .wv-cell-due{min-width:160px;white-space:nowrap;}
.wv-recovery-table .wv-cell-current{min-width:190px;}
.wv-recovery-table .wv-cell-send{min-width:118px;text-align:center;white-space:nowrap;}
.wv-recovery-table .wv-cell-assign{min-width:255px;}
.wv-recovery-table .wv-cell-follow{min-width:485px;}
.wv-recovery-case-form{display:grid;gap:5px;grid-template-columns:minmax(105px,1fr) minmax(95px,1fr);}
.wv-recovery-follow-form{align-items:center;display:flex;gap:5px;min-width:0;}
.wv-recovery-follow-form .form-control{flex:0 0 auto;}
.wv-recovery-follow-form select.form-control{width:95px;}
.wv-recovery-follow-form input.form-control{width:92px;}
.wv-recovery-follow-form textarea.form-control{flex:1 1 155px;min-width:135px;}
.wv-recovery-table .form-control{font-size:12px;height:30px;padding:4px 6px;}
.wv-recovery-table textarea.form-control{height:30px;resize:vertical;}
.wv-recovery-send-actions{display:inline-flex;gap:5px;justify-content:center;}
.wv-recovery-send-btn{align-items:center;border:1px solid #cbd8e6;border-radius:6px;color:#0d2747;display:inline-flex;height:30px;justify-content:center;text-decoration:none!important;width:30px;}
.wv-recovery-send-btn:hover,.wv-recovery-send-btn:focus{background:#eff6ff;border-color:#99b8dc;color:#07152a;}
.wv-recovery-send-btn.is-busy{opacity:.65;pointer-events:none;}
.wv-recovery-send-btn.is-success{background:#e9f8ef;border-color:#29a665;color:#09743d;}
.wv-recovery-send-btn.is-error{background:#fff0f2;border-color:#e15a6b;color:#a70029;}
.wv-status{border-radius:999px;color:#fff;display:inline-block;font-size:11px;font-weight:900;padding:4px 8px;}
.wv-status-New{background:#6b7b8c}.wv-status-Contacted{background:#2477c7}.wv-status-Promise{background:#168a3a}.wv-status-No-Answer{background:#b7791f}.wv-status-Dispute,.wv-status-Refused{background:#c51f32}.wv-status-Legal-Notice{background:#8b1a5a}.wv-status-Paid,.wv-status-Closed{background:#008a7a}
.wv-priority{border-radius:999px;display:inline-block;font-size:11px;font-weight:900;padding:4px 8px;}
.wv-priority-Normal{background:#eef4fb;color:#34506c}.wv-priority-High{background:#fff2cf;color:#9a6100}.wv-priority-Urgent{background:#ffe3e8;color:#9f002d}
.wv-recovery-muted{color:#60758c;font-size:11px;font-weight:700;}
.wv-recovery-pager{align-items:center;display:flex;flex-wrap:wrap;gap:10px;justify-content:space-between;margin-top:10px;}
.wv-recovery-pager form{margin:0}.wv-recovery-pager label{font-size:12px;font-weight:900;margin:0}.wv-recovery-pager select{height:30px;margin:0 4px;min-width:66px}
@media(max-width:1299px){.wv-recovery-stats{grid-template-columns:repeat(2,minmax(0,1fr));}.wv-recovery-filter-grid{grid-template-columns:repeat(3,minmax(0,1fr));}}
@media(max-width:767px){.wv-recovery-head{align-items:stretch;flex-direction:column}.wv-recovery-stats,.wv-recovery-filter-grid{grid-template-columns:1fr}.wv-recovery-actions .btn{margin:2px 0}.wv-recovery-case-form{grid-template-columns:1fr}.wv-recovery-follow-form{align-items:stretch;flex-direction:column}.wv-recovery-follow-form .form-control,.wv-recovery-follow-form select.form-control,.wv-recovery-follow-form input.form-control,.wv-recovery-follow-form textarea.form-control{width:100%;}}
</style>

<div class="wv-recovery-page">
    <?php echo $this->Session->flash(); ?>
    <div id="wvRecoveryAjaxStatus" class="wv-recovery-alert"></div>

    <div class="wv-recovery-head">
        <div>
            <h2><?php echo __('Recovery Dashboard'); ?></h2>
            <p><?php echo __('Officer-wise follow-up for payment reminders. One recovery case is maintained per deal.'); ?></p>
        </div>
        <div class="wv-recovery-actions">
            <?php echo $this->Html->link('<span class="fa fa-users"></span> '.__('Recovery Officers'), array('action'=>'officers'), array('escape'=>false,'class'=>'btn btn-default btn-sm')); ?>
            <?php echo $this->Html->link('<span class="fa fa-cog"></span> '.__('Settings'), array('action'=>'settings'), array('escape'=>false,'class'=>'btn btn-default btn-sm')); ?>
            <?php echo $this->Html->link('<span class="fa fa-bell"></span> '.__('Payment Reminders'), array('controller'=>'Paymentreminders','action'=>'index'), array('escape'=>false,'class'=>'btn btn-primary btn-sm')); ?>
        </div>
    </div>

    <div class="wv-recovery-stats">
        <div class="wv-recovery-stat"><span><?php echo __('Visible Cases'); ?></span><strong><?php echo (int)$summary['total']; ?></strong></div>
        <div class="wv-recovery-stat"><span><?php echo __('Unassigned'); ?></span><strong><?php echo (int)$summary['unassigned']; ?></strong></div>
        <div class="wv-recovery-stat"><span><?php echo __('Promise Today'); ?></span><strong><?php echo (int)$summary['promise_today']; ?></strong></div>
        <div class="wv-recovery-stat"><span><?php echo __('Missed Promise'); ?></span><strong><?php echo (int)$summary['missed_promise']; ?></strong></div>
        <div class="wv-recovery-stat"><span><?php echo __('High Priority'); ?></span><strong><?php echo (int)$summary['high_priority']; ?></strong></div>
    </div>

    <div class="wv-recovery-filter">
        <form method="get" action="<?php echo $this->Html->url(array('controller'=>'Recoveries','action'=>'index')); ?>">
            <div class="wv-recovery-filter-grid">
                <?php if(!empty($agencyOptions) && count($agencyOptions)>1){ ?>
                <div class="form-group">
                    <label><?php echo __('Agency'); ?></label>
                    <select name="project_id" class="form-control">
                        <option value=""><?php echo __('All Agencies'); ?></option>
                        <?php foreach($agencyOptions as $agencyId => $agencyName){ ?>
                            <option value="<?php echo (int)$agencyId; ?>" <?php echo ((int)$selectedAgencyId === (int)$agencyId) ? 'selected="selected"' : ''; ?>><?php echo h($agencyName); ?></option>
                        <?php } ?>
                    </select>
                </div>
                <?php } else { ?>
                    <input type="hidden" name="project_id" value="<?php echo (int)$selectedAgencyId; ?>" />
                <?php } ?>
                <div class="form-group">
                    <label><?php echo __('Reminder'); ?></label>
                    <select name="status" class="form-control">
                        <option value="overdue" <?php echo $status === 'overdue' ? 'selected="selected"' : ''; ?>><?php echo __('Overdue'); ?></option>
                        <option value="due_soon" <?php echo $status === 'due_soon' ? 'selected="selected"' : ''; ?>><?php echo __('Before Due'); ?></option>
                        <option value="all" <?php echo $status === 'all' ? 'selected="selected"' : ''; ?>><?php echo __('All Due + Overdue'); ?></option>
                    </select>
                </div>
                <div class="form-group">
                    <label><?php echo __('Officer'); ?></label>
                    <select name="officer_id" class="form-control">
                        <option value=""><?php echo __('All Officers'); ?></option>
                        <?php foreach($officerOptions as $officerId => $officerName){ ?>
                            <option value="<?php echo (int)$officerId; ?>" <?php echo ((int)$officerFilter === (int)$officerId) ? 'selected="selected"' : ''; ?>><?php echo h($officerName); ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-group">
                    <label><?php echo __('Recovery Status'); ?></label>
                    <select name="case_status" class="form-control">
                        <option value=""><?php echo __('All Status'); ?></option>
                        <?php foreach($caseStatuses as $statusKey => $statusName){ ?>
                            <option value="<?php echo h($statusKey); ?>" <?php echo ((string)$caseStatusFilter === (string)$statusKey) ? 'selected="selected"' : ''; ?>><?php echo h($statusName); ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-group">
                    <label><?php echo __('Search'); ?></label>
                    <input type="text" name="keyword" class="form-control" value="<?php echo h($keyword); ?>" placeholder="<?php echo __('Client, membership, property'); ?>" />
                </div>
                <div class="form-group">
                    <label><?php echo __('Days'); ?></label>
                    <input type="number" name="days_before" min="0" max="90" class="form-control" value="<?php echo (int)$daysBefore; ?>" />
                </div>
                <div class="form-group">
                    <label>&nbsp;</label>
                    <button class="btn btn-primary btn-block" type="submit"><span class="fa fa-search"></span> <?php echo __('Search'); ?></button>
                </div>
            </div>
        </form>
    </div>

    <div class="wv-recovery-panel">
        <div class="table-responsive">
            <table class="table table-bordered table-striped wv-recovery-table">
                <thead>
                    <tr>
                        <th class="wv-cell-sr"><?php echo __('#'); ?></th>
                        <th class="wv-cell-client"><?php echo __('Client / Deal'); ?></th>
                        <th><?php echo __('Father / Guardian'); ?></th>
                        <th class="wv-cell-due"><?php echo __('Due / Amount'); ?></th>
                        <th class="wv-cell-current"><?php echo __('Current Recovery'); ?></th>
                        <th class="wv-cell-send"><?php echo __('Send'); ?></th>
                        <th class="wv-cell-assign"><?php echo __('Assign Officer'); ?></th>
                        <th class="wv-cell-follow"><?php echo __('Quick Follow-up'); ?></th>
                    </tr>
                </thead>
                <tbody>
                <?php if(!empty($rows)){ $sr = !empty($pagination['start']) ? (int)$pagination['start'] : 1; foreach($rows as $row){
                    $dealId = (int)Hash::get($row, 'Deal.id', 0);
                    $planId = !empty($row['_RecoveryAggregate']['plan_ids'][0]) ? (int)$row['_RecoveryAggregate']['plan_ids'][0] : (int)Hash::get($row, 'PlansPayment.id', 0);
                    $case = !empty($row['RecoveryCase']) ? $row['RecoveryCase'] : array();
                    $caseStatus = !empty($case['status']) ? $case['status'] : 'New';
                    $priority = !empty($case['priority']) ? $case['priority'] : 'Normal';
                    $statusClass = 'wv-status-'.str_replace(' ', '-', $caseStatus);
                    $priorityClass = 'wv-priority-'.str_replace(' ', '-', $priority);
                    $mobile = !empty($row['Client']['mobile']) ? $row['Client']['mobile'] : (!empty($row['Client']['phone']) ? $row['Client']['phone'] : '-');
                    $dueDate = !empty($row['PlansPayment']['date']) ? $row['PlansPayment']['date'] : '';
                    $dueDateDisplay = $dueDate !== '' ? date('d-M-Y', strtotime($dueDate)) : '-';
                    $promiseDate = (!empty($case['promise_date']) && $case['promise_date'] !== '0000-00-00') ? date('d-M-Y', strtotime($case['promise_date'])) : '';
                    $nextDate = (!empty($case['next_action_date']) && $case['next_action_date'] !== '0000-00-00') ? date('d-M-Y', strtotime($case['next_action_date'])) : '';
                    $smsUrl = $this->Html->url(array('controller'=>'Paymentreminders','action'=>'sendsms',$planId,'?'=>array('ajax'=>1)));
                    $whatsappUrl = $this->Html->url(array('controller'=>'Paymentreminders','action'=>'whatsapp',$planId,'?'=>array('ajax'=>1)));
                    $emailUrl = $this->Html->url(array('controller'=>'Paymentreminders','action'=>'sendemail',$planId,'?'=>array('ajax'=>1)));
                ?>
                    <tr>
                        <td class="wv-cell-sr"><?php echo (int)$sr++; ?></td>
                        <td class="wv-cell-client">
                            <strong><?php echo $this->Html->link(h(Hash::get($row, 'Client.name', '-')), array('controller'=>'deals_payments','action'=>'index',$dealId), array('escape'=>false)); ?></strong><br>
                            <span class="wv-recovery-muted"><?php echo h(Hash::get($row, 'Deal.invoice_no', '-')); ?> | <?php echo h(Hash::get($row, 'Property.name', '-')); ?></span><br>
                            <span class="fa fa-phone"></span> <?php echo h($mobile); ?>
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
                        <td class="wv-cell-due">
                            <strong><?php echo h($dueDateDisplay); ?></strong><br>
                            <span class="wv-recovery-muted"><?php echo h(Hash::get($row, 'PlansPayment.name', '-')); ?></span><br>
                            <strong><?php echo h($currencySymbol.number_format((float)Hash::get($row, 'PlansPayment.amount', 0))); ?></strong>
                        </td>
                        <td class="wv-cell-current">
                            <span class="wv-status <?php echo h($statusClass); ?>"><?php echo h($caseStatus); ?></span>
                            <span class="wv-priority <?php echo h($priorityClass); ?>"><?php echo h($priority); ?></span><br>
                            <span class="wv-recovery-muted"><?php echo __('Promise'); ?>: <?php echo $promiseDate !== '' ? h($promiseDate) : '-'; ?></span><br>
                            <span class="wv-recovery-muted"><?php echo __('Next'); ?>: <?php echo $nextDate !== '' ? h($nextDate) : '-'; ?></span><br>
                            <?php if(!empty($case['last_note'])){ ?><span class="wv-recovery-muted"><?php echo h($case['last_note']); ?></span><?php } ?>
                        </td>
                        <td class="wv-cell-send">
                            <div class="wv-recovery-send-actions">
                                <a href="#" class="wv-recovery-send-btn js-recovery-reminder-btn" data-method="post" data-channel="sms" data-url="<?php echo h($smsUrl); ?>" title="<?php echo __('Send SMS Reminder'); ?>"><span class="fa fa-comment"></span></a>
                                <a href="#" class="wv-recovery-send-btn js-recovery-reminder-btn" data-method="get" data-channel="whatsapp" data-url="<?php echo h($whatsappUrl); ?>" title="<?php echo __('Send WhatsApp Reminder'); ?>"><span class="fa fa-whatsapp"></span></a>
                                <a href="#" class="wv-recovery-send-btn js-recovery-reminder-btn" data-method="post" data-channel="email" data-url="<?php echo h($emailUrl); ?>" title="<?php echo __('Send Email Reminder'); ?>"><span class="fa fa-envelope"></span></a>
                            </div>
                        </td>
                        <td class="wv-cell-assign">
                            <?php echo $this->Form->create('Recovery', array('url'=>array('controller'=>'Recoveries','action'=>'assign'), 'class'=>'wv-recovery-case-form')); ?>
                            <?php echo $this->Form->hidden('deal_id', array('value'=>$dealId)); ?>
                            <?php echo $this->Form->select('recovery_officer_id', $officerOptions, array('empty'=>__('Unassigned'), 'value'=>!empty($case['recovery_officer_id']) ? (int)$case['recovery_officer_id'] : 0, 'class'=>'form-control')); ?>
                            <?php echo $this->Form->select('status', $caseStatuses, array('value'=>$caseStatus, 'class'=>'form-control')); ?>
                            <?php echo $this->Form->select('priority', $priorityOptions, array('value'=>$priority, 'class'=>'form-control')); ?>
                            <button class="btn btn-success btn-sm" type="submit"><span class="fa fa-save"></span> <?php echo __('Save'); ?></button>
                            <?php echo $this->Form->end(); ?>
                        </td>
                        <td class="wv-cell-follow">
                            <?php echo $this->Form->create('Recovery', array('url'=>array('controller'=>'Recoveries','action'=>'followup'), 'class'=>'wv-recovery-follow-form')); ?>
                            <?php echo $this->Form->hidden('deal_id', array('value'=>$dealId)); ?>
                            <?php echo $this->Form->hidden('plans_payment_id', array('value'=>$planId)); ?>
                            <?php echo $this->Form->select('recovery_officer_id', $officerOptions, array('empty'=>__('Officer'), 'value'=>!empty($case['recovery_officer_id']) ? (int)$case['recovery_officer_id'] : 0, 'class'=>'form-control')); ?>
                            <?php echo $this->Form->select('action_type', $actionTypes, array('value'=>'Call', 'class'=>'form-control')); ?>
                            <?php echo $this->Form->select('status', $caseStatuses, array('value'=>$caseStatus === 'New' ? 'Contacted' : $caseStatus, 'class'=>'form-control')); ?>
                            <?php echo $this->Form->text('promise_date', array('class'=>'form-control js-recovery-date', 'placeholder'=>'Promise')); ?>
                            <?php echo $this->Form->text('next_action_date', array('class'=>'form-control js-recovery-date', 'placeholder'=>'Next')); ?>
                            <?php echo $this->Form->textarea('note', array('class'=>'form-control', 'placeholder'=>__('Recovery notes'), 'rows'=>1)); ?>
                            <button class="btn btn-primary btn-sm" type="submit"><span class="fa fa-phone"></span> <?php echo __('Log'); ?></button>
                            <?php echo $this->Form->end(); ?>
                        </td>
                    </tr>
                <?php }} else { ?>
                    <tr><td colspan="7" class="text-center"><?php echo __('No recovery reminders found for this filter.'); ?></td></tr>
                <?php } ?>
                </tbody>
            </table>
        </div>

        <div class="wv-recovery-pager">
            <form method="get" action="<?php echo $this->Html->url(array('controller'=>'Recoveries','action'=>'index')); ?>">
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
                $prevQuery = $pagerQuery; $prevQuery['limit'] = $currentLimit; $prevQuery['page'] = max(1, (int)$pagination['page'] - 1);
                $nextQuery = $pagerQuery; $nextQuery['limit'] = $currentLimit; $nextQuery['page'] = min((int)$pagination['pageCount'], (int)$pagination['page'] + 1);
                echo $this->Html->link('&larr; '.__('Prev'), array('action'=>'index','?'=>$prevQuery), array('escape'=>false,'class'=>'btn btn-default btn-sm '.(((int)$pagination['page']<=1)?'disabled':'')));
                echo ' <span class="wv-recovery-muted">'.sprintf(__('Showing %s to %s of %s entries'), (int)$pagination['start'], (int)$pagination['end'], (int)$pagination['count']).'</span> ';
                echo $this->Html->link(__('Next').' &rarr;', array('action'=>'index','?'=>$nextQuery), array('escape'=>false,'class'=>'btn btn-default btn-sm '.(((int)$pagination['page']>=(int)$pagination['pageCount'])?'disabled':'')));
                ?>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
(function($){
    function showRecoveryMessage(success, message){
        var box = $('#wvRecoveryAjaxStatus');
        box.removeClass('is-success is-error').addClass(success ? 'is-success' : 'is-error').text(message || '').stop(true, true).fadeIn(120);
    }

    if($.fn.datepicker){
        $('.js-recovery-date').datepicker({format:'dd-M-yyyy',autoclose:true,todayHighlight:true});
    } else if($.fn.datetimepicker){
        $('.js-recovery-date').datetimepicker({format:'DD-MMM-YYYY',useCurrent:false});
    }

    $(document).on('click', '.js-recovery-reminder-btn', function(e){
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
            showRecoveryMessage(ok, response && response.message ? response.message : (ok ? '<?php echo __('Reminder sent.'); ?>' : '<?php echo __('Unable to send reminder.'); ?>'));
        }).fail(function(xhr){
            btn.removeClass('is-busy').addClass('is-error');
            showRecoveryMessage(false, xhr && xhr.statusText ? xhr.statusText : '<?php echo __('Unable to send reminder.'); ?>');
        });
        return false;
    });
})(jQuery);
</script>
