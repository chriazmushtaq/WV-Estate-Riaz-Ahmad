<?php
$nextFormIndex = is_array($PlansPayment) ? count($PlansPayment) : 0;
$dealIdForNew = 0;
$initialScheduleTotal = 0;
if(!empty($PlansPayment) && is_array($PlansPayment))
{
    foreach($PlansPayment as $p)
    {
        if(isset($p['PlansPayment']['amount']))
        {
            $initialScheduleTotal += (float)$p['PlansPayment']['amount'];
        }
    }
    if(!empty($PlansPayment[0]['PlansPayment']['deal_id']))
    {
        $dealIdForNew = (int)$PlansPayment[0]['PlansPayment']['deal_id'];
    }
}
$dealAmountForEditVal = (isset($dealAmountForEdit) && (float)$dealAmountForEdit > 0) ? (float)$dealAmountForEdit : $initialScheduleTotal;
$initialDiff = abs($dealAmountForEditVal - $initialScheduleTotal);
?>
<script type="text/javascript">
    $(document).ready(function(){
        var dpFormat = <?php echo json_encode($dpFormat); ?>;
        var nextIndex = <?php echo (int)$nextFormIndex; ?>;
        var dealIdForNew = <?php echo (int)$dealIdForNew; ?>;
        var dealTotalAmount = <?php echo json_encode((float)$dealAmountForEditVal); ?>;
        var currencySymbol = <?php echo json_encode(isset($currency) ? $currency : 'Rs'); ?>;
        var draftTimer = null;

        function updateDraftStatus(text, cls){
            var $status = $('#plansDraftStatus');
            if($status.length === 0){ return; }
            $status.removeClass('text-muted text-success text-warning text-danger');
            $status.addClass(cls || 'text-muted');
            $status.text(text || '');
        }

        function initDatePicker($scope){
            $scope.find('.startDate').each(function(){
                var $picker = $(this);
                if (!$picker.data('DateTimePicker')) {
                    $picker.datetimepicker({format: dpFormat});
                }
            });
        }

        function parseInstallmentNo(name){
            var match = (name || '').match(/(?:(\d+)\s*Installment|Overdue\s*Plan\s*(\d+))/i);
            return match ? parseInt(match[1] || match[2], 10) : 0;
        }

        function parseDateDmy(dateText){
            var value = $.trim(dateText || '');
            var match = value.match(/^(\d{1,2})[-\/.](\d{1,2})[-\/.](\d{2,4})$/);
            if(!match){ return null; }
            var d = parseInt(match[1],10), m = parseInt(match[2],10)-1, y = parseInt(match[3],10);
            if(y < 100){ y += 2000; }
            var dt = new Date(y,m,d);
            if(isNaN(dt.getTime())){ return null; }
            return dt;
        }

        function formatDateDmy(dt){
            if(!dt){ return ''; }
            var d = dt.getDate();
            var m = dt.getMonth()+1;
            var y = dt.getFullYear();
            return (d<10?'0':'') + d + '-' + (m<10?'0':'') + m + '-' + y;
        }

        function buildNextDateFromLast(){
            var lastDateVal = $.trim($('#plans-edit-rows .plan-edit-block:not(.is-deleted):last .due-date-input').val() || '');
            var baseDate = parseDateDmy(lastDateVal);
            if(!baseDate){ return ''; }
            var next = new Date(baseDate.getTime());
            next.setMonth(next.getMonth() + 1);
            return formatDateDmy(next);
        }

        function getNextInstallmentName(){
            var maxNo = 0;
            $('#plans-edit-rows .plan-edit-block:not(.is-deleted) .installment-name-input').each(function(){
                var parsed = parseInstallmentNo($(this).val());
                if(parsed > maxNo){ maxNo = parsed; }
            });
            if(maxNo > 0){
                return (maxNo + 1) + ' Installment';
            }
            return ($('#plans-edit-rows .plan-edit-block:not(.is-deleted)').length + 1) + ' Installment';
        }

        function renumberForms(){
            var visibleNo = 1;
            $('#plans-edit-rows .plan-edit-block').each(function(){
                var $row = $(this);
                if(!$row.hasClass('is-deleted') && $row.find('.delete-flag').val() !== '1'){
                    $row.find('.plan-form-no').text(visibleNo++);
                }
            });
        }

        function toNumber(value){
            if(value === null || value === undefined){ return 0; }
            value = value.toString().replace(/,/g,'').replace(/\s/g,'').trim();
            if(value === ''){ return 0; }
            var parsed = parseFloat(value);
            return isNaN(parsed) ? 0 : parsed;
        }

        function formatAmount(value){
            var decimals = (Math.abs(value % 1) > 0) ? 2 : 0;
            return value.toLocaleString('en-US', {minimumFractionDigits: decimals, maximumFractionDigits: 2});
        }

        function renderAmount(value){
            var sym = currencySymbol ? currencySymbol.trim() + ' ' : '';
            return sym + formatAmount(value);
        }

        function recalcScheduleTotals(){
            var scheduleTotal = 0;
            $('#plans-edit-rows .plan-edit-block').each(function(){
                var $row = $(this);
                if($row.hasClass('is-deleted') || $row.find('.delete-flag').val() === '1'){
                    return;
                }
                var $amtInput = $row.find('.amount-input');
                if($amtInput.is(':disabled')){ return; }
                scheduleTotal += toNumber($amtInput.val());
            });

            var diff = dealTotalAmount - scheduleTotal;
            $('#scheduleTotalAmount').text(renderAmount(scheduleTotal));
            $('#scheduleDealAmount').text(renderAmount(dealTotalAmount));
            $('#scheduleDiffAmount').text(renderAmount(Math.abs(diff)));

            if(Math.abs(diff) < 0.01){
                $('#scheduleDiffLabel').text('<?php echo __('Difference');?>');
                $('#scheduleDiffStatus').text('<?php echo __('Matched');?>').removeClass('text-danger text-warning').addClass('text-success');
            }else if(diff > 0){
                $('#scheduleDiffLabel').text('<?php echo __('Remaining');?>');
                $('#scheduleDiffStatus').text('<?php echo __('Short');?>').removeClass('text-success text-warning').addClass('text-danger');
            }else{
                $('#scheduleDiffLabel').text('<?php echo __('Exceeded');?>');
                $('#scheduleDiffStatus').text('<?php echo __('Over');?>').removeClass('text-success text-danger').addClass('text-warning');
            }
        }

        function queueDraftSave(){
            if(draftTimer){ clearTimeout(draftTimer); }
            draftTimer = setTimeout(function(){
                updateDraftStatus('<?php echo __('Ready to save');?>', 'text-muted');
            }, 300);
        }

        function applyDeleteState($row){
            $row.addClass('is-deleted').hide();
            $row.find('.delete-flag').val('1');
            $row.find('input,select,textarea').not('.delete-flag,.plan-id-input').prop('disabled', true);
        }

        $('#plans-edit-rows').on('click', '.btn-delete-plan-row', function(e){
            e.preventDefault();
            var $row = $(this).closest('.plan-edit-block');
            var planId = $.trim($row.find('.plan-id-input').val() || '');
            if(planId !== ''){
                applyDeleteState($row);
            } else {
                $row.remove();
            }
            renumberForms();
            recalcScheduleTotals();
            queueDraftSave();
        });

        $('#addNextInstallmentBtn').on('click', function(e){
            e.preventDefault();
            var nameValue = getNextInstallmentName();
            var lastAmt = $.trim($('#plans-edit-rows .plan-edit-block:not(.is-deleted):last .amount-input').val() || '');
            var dateValue = buildNextDateFromLast();
            var templateHtml = $('#plan-edit-row-template').html();
            templateHtml = templateHtml.replace(/__INDEX__/g, nextIndex);
            templateHtml = templateHtml.replace(/__FORMNO__/g, ($('#plans-edit-rows .plan-edit-block:not(.is-deleted)').length + 1));
            templateHtml = templateHtml.replace(/__NAME__/g, $('<div/>').text(nameValue).html());
            templateHtml = templateHtml.replace(/__AMOUNT__/g, $('<div/>').text(lastAmt).html());
            templateHtml = templateHtml.replace(/__DATE__/g, $('<div/>').text(dateValue).html());
            templateHtml = templateHtml.replace(/__DEAL_ID__/g, dealIdForNew);
            var $newBlock = $(templateHtml);
            $('#plans-edit-rows').append($newBlock);
            initDatePicker($newBlock);
            renumberForms();
            recalcScheduleTotals();
            queueDraftSave();
            nextIndex++;
        });
        
        $(document).on('keyup change input', '#plans-edit-rows .amount-input', function(){
            recalcScheduleTotals();
            queueDraftSave();
        });

        $(document).on('keyup change input', '#plans-edit-rows .installment-name-input, #plans-edit-rows .due-date-input', function(){
            queueDraftSave();
        });

        initDatePicker($(document));
        renumberForms();
        recalcScheduleTotals();

        // Ensure calculation runs when modal finishes opening
        if(typeof jQuery.fn.modal === 'function'){
            $('#targetModal').one('shown.bs.modal', function(){
                recalcScheduleTotals();
                initDatePicker($(this));
            });
        }
        setTimeout(function(){
            recalcScheduleTotals();
        }, 100);
        setTimeout(function(){
            recalcScheduleTotals();
        }, 300);

        if($('#plansDraftStatus').text().trim() === ''){
            updateDraftStatus('<?php echo __('Draft autosave ready');?>', 'text-muted');
        }
    });
</script>

<style type="text/css">
/* Tight modal width to 680px for best preview */
#targetModal .modal-dialog {
    max-width: 680px !important;
    width: 96% !important;
    margin: 15px auto !important;
}
.plans-edit-wrapper {
    max-width: 680px;
    margin: 0 auto;
    font-family: inherit;
}
.plans-edit-wrapper .panel-custom-card {
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    background: #ffffff;
    box-shadow: 0 4px 20px rgba(15, 23, 42, 0.08);
    overflow: hidden;
}
.plans-edit-wrapper .modal-header-custom {
    background: #0f172a;
    color: #ffffff;
    padding: 10px 14px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.plans-edit-wrapper .modal-header-custom h4 {
    margin: 0;
    font-size: 14px;
    font-weight: 700;
    color: #ffffff;
    letter-spacing: 0.3px;
}
.plans-edit-wrapper .modal-header-custom .close-btn {
    color: #94a3b8;
    background: transparent;
    border: none;
    font-size: 20px;
    line-height: 1;
    cursor: pointer;
    transition: color 0.2s;
}
.plans-edit-wrapper .modal-header-custom .close-btn:hover {
    color: #ffffff;
}
.plans-edit-wrapper .modal-body-custom {
    padding: 10px 12px;
    background: #f8fafc;
    max-height: 65vh;
    overflow-y: auto;
}
/* Individual Installment Row Card */
.plans-edit-wrapper .plan-edit-block {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    margin-bottom: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
    overflow: hidden;
    transition: border-color 0.2s, box-shadow 0.2s;
}
.plans-edit-wrapper .plan-edit-block:hover {
    border-color: #cbd5e1;
    box-shadow: 0 2px 6px rgba(15, 23, 42, 0.06);
}
.plans-edit-wrapper .plan-block-header {
    background: #0f172a;
    padding: 4px 10px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.plans-edit-wrapper .plan-block-header .form-badge {
    font-size: 10.5px;
    font-weight: 700;
    color: #ffffff;
    letter-spacing: 0.5px;
}
.plans-edit-wrapper .plan-block-header .btn-delete-plan-row {
    background: #ef4444;
    color: #ffffff;
    border: none;
    border-radius: 4px;
    padding: 2px 7px;
    font-size: 10px;
    font-weight: 600;
    transition: background 0.2s;
}
.plans-edit-wrapper .plan-block-header .btn-delete-plan-row:hover {
    background: #dc2626;
}
/* Compact Horizontal Fields inside each card */
.plans-edit-wrapper .plan-block-body {
    padding: 8px 10px;
}
.plans-edit-wrapper .plan-row-grid {
    display: flex;
    align-items: center;
    gap: 8px;
}
.plans-edit-wrapper .plan-col-name {
    flex: 1 1 38%;
}
.plans-edit-wrapper .plan-col-amount {
    flex: 1 1 31%;
}
.plans-edit-wrapper .plan-col-date {
    flex: 1 1 31%;
}
.plans-edit-wrapper .field-label {
    display: block;
    font-size: 9.5px;
    font-weight: 700;
    color: #475569;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    margin-bottom: 2px;
}
.plans-edit-wrapper .form-control {
    height: 30px;
    padding: 3px 7px;
    font-size: 11.5px;
    border-radius: 5px;
    border: 1px solid #cbd5e1;
    color: #0f172a;
    background-color: #ffffff;
    transition: border-color 0.15s, box-shadow 0.15s;
}
.plans-edit-wrapper .form-control:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.15);
}
.plans-edit-wrapper .input-group-addon {
    padding: 3px 7px;
    background: #f1f5f9;
    border: 1px solid #cbd5e1;
    border-left: none;
    border-radius: 0 5px 5px 0;
    font-size: 10.5px;
    color: #64748b;
}

/* Summary Bar */
.plans-edit-wrapper .schedule-summary-bar {
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    padding: 8px 12px;
    margin-top: 10px;
    margin-bottom: 10px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.04);
}
.plans-edit-wrapper .summary-metrics-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
}
.plans-edit-wrapper .summary-metric-item {
    font-size: 11.5px;
    color: #334155;
}
.plans-edit-wrapper .summary-metric-item strong {
    font-weight: 700;
    color: #0f172a;
}
.plans-edit-wrapper .summary-metric-item span {
    font-weight: 700;
    color: #0f172a;
}
.plans-edit-wrapper #scheduleTotalAmount {
    color: #0f172a;
    font-weight: 700;
}
.plans-edit-wrapper .status-tag {
    font-size: 10.5px;
    font-weight: 700;
    padding: 1px 5px;
    border-radius: 3px;
    margin-left: 3px;
}
.plans-edit-wrapper .text-success { color: #16a34a !important; }
.plans-edit-wrapper .text-danger { color: #dc2626 !important; }
.plans-edit-wrapper .text-warning { color: #d97706 !important; }

/* Bottom Action Buttons */
.plans-edit-wrapper .modal-footer-custom {
    padding: 8px 12px;
    background: #f1f5f9;
    border-top: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.plans-edit-wrapper .btn-action {
    border-radius: 5px;
    padding: 5px 12px;
    font-size: 11.5px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    border: none;
    cursor: pointer;
    transition: transform 0.1s, opacity 0.2s;
}
.plans-edit-wrapper .btn-action:hover {
    opacity: 0.92;
}
.plans-edit-wrapper .btn-add-inst {
    background: #0284c7;
    color: #ffffff;
}
.plans-edit-wrapper .btn-save-plan {
    background: #16a34a;
    color: #ffffff;
}
.plans-edit-wrapper .btn-cancel-plan {
    background: #ef4444;
    color: #ffffff;
@media (max-width: 767px) {
    .plans-edit-wrapper .plan-row-grid {
        flex-direction: column;
        align-items: stretch;
        gap: 6px;
    }
    .plans-edit-wrapper .summary-metrics-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 6px;
    }
    .plans-edit-wrapper .modal-footer-custom {
        flex-direction: column;
        gap: 8px;
        align-items: stretch;
    }
}
</style>

<div class="plans-edit-wrapper">
    <div class="panel-custom-card">
        <div class="modal-header-custom">
            <h4><span class="fa fa-pencil-square-o"></span> <?php echo __('Edit Payment Plan Schedule');?></h4>
            <?php if(!$isError){?>
                <button type="button" class="close-btn" data-dismiss="modal" aria-hidden="true">&times;</button>
            <?php }?>
        </div>

        <div class="modal-body-custom">
            <?php echo $this->Session->flash();?>
            <?php echo $this->Form->create('PlansPayment', array('controller' => 'PlansPayments', 'action' => "edit/$id", 'name' => 'post_req', 'id' => 'post_req'));?>
            
            <div id="plans-edit-rows">
                <?php foreach ($PlansPayment as $k => $post): $planRowId = $post['PlansPayment']['id']; $form_no = $k + 1;?>
                    <div class="plan-edit-block">
                        <div class="plan-block-header">
                            <span class="form-badge"><span class="fa fa-list-ol"></span> <?php echo __('Form');?> <span class="plan-form-no"><?php echo $form_no; ?></span></span>
                            <button type="button" class="btn-delete-plan-row"><span class="fa fa-trash"></span> <?php echo __('Delete');?></button>
                        </div>
                        <div class="plan-block-body">
                            <div class="plan-row-grid">
                                <div class="plan-col-name">
                                    <label class="field-label"><?php echo __('Installment Name');?></label>
                                    <?php echo $this->Form->input("$k.PlansPayment.name", array('label' => false, 'class' => 'form-control installment-name-input', 'placeholder' => __('Installment Name'), 'div' => false));?>
                                </div>
                                <div class="plan-col-amount">
                                    <label class="field-label"><?php echo __('Amount');?> (<?php echo (isset($currency)?$currency:'Rs');?>)</label>
                                    <?php echo $this->Form->input("$k.PlansPayment.amount", array('label' => false, 'class' => 'form-control amount-input', 'placeholder' => __('Amount'), 'div' => false));?>
                                </div>
                                <div class="plan-col-date">
                                    <label class="field-label"><?php echo __('Due Date');?></label>
                                    <div class="input-group startDate" style="width:100%;">
                                        <?php echo $this->Form->input("$k.PlansPayment.date", array('type' => 'text', 'value' => $this->Time->format($sysDay.$dateSep.$sysMonth.$dateSep.$sysYear, $post['PlansPayment']['date']), 'label' => false, 'class' => 'form-control due-date-input', 'div' => false));?>
                                        <span class="input-group-addon"><span class="fa fa-calendar"></span></span>
                                    </div>
                                </div>
                            </div>
                            <?php echo $this->Form->input("$k.PlansPayment.id", array('type' => 'hidden', 'class' => 'plan-id-input'));?>
                            <?php echo $this->Form->input("$k.PlansPayment._delete", array('type' => 'hidden', 'value' => '0', 'class' => 'delete-flag'));?>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php unset($post); ?>
            </div>

            <!-- Schedule Total Summary Bar -->
            <div class="schedule-summary-bar">
                <div class="summary-metrics-row">
                    <div class="summary-metric-item">
                        <strong><?php echo __('Schedule Total');?>:</strong> 
                        <span id="scheduleTotalAmount"><?php echo (isset($currency)?$currency:'Rs').' '.$this->Number->format($initialScheduleTotal);?></span>
                    </div>
                    <div class="summary-metric-item">
                        <strong><?php echo __('Deal Total');?>:</strong> 
                        <span id="scheduleDealAmount"><?php echo (isset($currency)?$currency:'Rs').' '.$this->Number->format($dealAmountForEditVal);?></span>
                    </div>
                    <div class="summary-metric-item">
                        <strong id="scheduleDiffLabel"><?php echo ($initialDiff < 0.01) ? __('Difference') : (($dealAmountForEditVal > $initialScheduleTotal) ? __('Remaining') : __('Exceeded'));?>:</strong> 
                        <span id="scheduleDiffAmount"><?php echo (isset($currency)?$currency:'Rs').' '.$this->Number->format($initialDiff);?></span> 
                        <span id="scheduleDiffStatus" class="status-tag <?php echo ($initialDiff < 0.01) ? 'text-success' : 'text-danger';?>"><?php echo ($initialDiff < 0.01) ? __('Matched') : (($dealAmountForEditVal > $initialScheduleTotal) ? __('Short') : __('Over'));?></span>
                    </div>
                </div>
            </div>

            <!-- Dynamic Row Template -->
            <script type="text/template" id="plan-edit-row-template">
                <div class="plan-edit-block">
                    <div class="plan-block-header">
                        <span class="form-badge"><span class="fa fa-list-ol"></span> <?php echo __('Form');?> <span class="plan-form-no">__FORMNO__</span></span>
                        <button type="button" class="btn-delete-plan-row"><span class="fa fa-trash"></span> <?php echo __('Delete');?></button>
                    </div>
                    <div class="plan-block-body">
                        <div class="plan-row-grid">
                            <div class="plan-col-name">
                                <label class="field-label"><?php echo __('Installment Name');?></label>
                                <input type="text" name="data[__INDEX__][PlansPayment][name]" class="form-control installment-name-input" placeholder="<?php echo __('Installment Name');?>" value="__NAME__">
                            </div>
                            <div class="plan-col-amount">
                                <label class="field-label"><?php echo __('Amount');?> (<?php echo (isset($currency)?$currency:'Rs');?>)</label>
                                <input type="text" name="data[__INDEX__][PlansPayment][amount]" class="form-control amount-input" placeholder="<?php echo __('Amount');?>" value="__AMOUNT__">
                            </div>
                            <div class="plan-col-date">
                                <label class="field-label"><?php echo __('Due Date');?></label>
                                <div class="input-group startDate" style="width:100%;">
                                    <input type="text" name="data[__INDEX__][PlansPayment][date]" class="form-control due-date-input" value="__DATE__">
                                    <span class="input-group-addon"><span class="fa fa-calendar"></span></span>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="data[__INDEX__][PlansPayment][deal_id]" value="__DEAL_ID__">
                        <input type="hidden" name="data[__INDEX__][PlansPayment][_delete]" value="0" class="delete-flag">
                    </div>
                </div>
            </script>

            <div class="modal-footer-custom">
                <div style="display:flex; align-items:center; gap:8px;">
                    <button type="button" id="addNextInstallmentBtn" class="btn-action btn-add-inst">
                        <span class="fa fa-plus-circle"></span> <?php echo __('Add Next Installment');?>
                    </button>
                    <button type="submit" class="btn-action btn-save-plan">
                        <span class="fa fa-check-circle"></span> <?php echo __('Update Payment Plan');?>
                    </button>
                    <?php if(!$isError){?>
                        <button type="button" class="btn-action btn-cancel-plan" data-dismiss="modal">
                            <span class="fa fa-times"></span> <?php echo __('Cancel');?>
                        </button>
                    <?php }?>
                </div>
                <small id="plansDraftStatus" class="text-muted"></small>
            </div>
            <?php echo $this->Form->end();?>
        </div>
    </div>
</div>
