<?php
$this->Js->JqueryEngine->jQueryObject = 'jQuery';
$this->Paginator->options(array(
  'update' => '#resultDiv',
  'evalScripts' => true,
));
$dealChargeTypes = (isset($dealChargeTypes) && is_array($dealChargeTypes)) ? $dealChargeTypes : array();
$dealChargeCycleTypeOptions = (isset($dealChargeCycleTypeOptions) && is_array($dealChargeCycleTypeOptions)) ? $dealChargeCycleTypeOptions : array('M'=>__('Monthly'),'Q'=>__('Quarterly'),'H'=>__('Biannual'),'Y'=>__('Yearly'));
$dealChargeSummaryMap = (isset($dealChargeSummaryMap) && is_array($dealChargeSummaryMap)) ? $dealChargeSummaryMap : array();
$dealChargeCycleRows = (isset($dealChargeCycleRows) && is_array($dealChargeCycleRows)) ? $dealChargeCycleRows : array();
$dealChargeTypeDropdownOptions = array();
foreach((array)$dealChargeTypes as $singleChargeType)
{
    $singleChargeType = trim((string)$singleChargeType);
    if($singleChargeType === '')
    {
        continue;
    }
    $dealChargeTypeDropdownOptions[$singleChargeType] = $singleChargeType;
}
?>
<script type="text/javascript">
$(document).ready(function(){
    function initDealChargeDatePicker(selector){
        if(typeof jQuery === 'undefined' || !jQuery(selector).length || typeof jQuery(selector).datetimepicker !== 'function'){
            return;
        }
        jQuery(selector).datetimepicker({format: '<?php echo $dpFormat;?>'});
    }
    initDealChargeDatePicker('#dealChargeStartDatePicker');
    initDealChargeDatePicker('#dealChargeEditStartDatePicker');

    function updateDealChargeTotalPreview(countSelector, amountSelector, outputSelector){
        var countVal = parseInt(jQuery(countSelector).val(),10);
        if(isNaN(countVal) || countVal < 1){
            countVal = 1;
        }
        var amountRaw = (jQuery(amountSelector).val() || '').toString().replace(/,/g,'');
        var amountVal = parseFloat(amountRaw);
        if(isNaN(amountVal) || amountVal < 0){
            amountVal = 0;
        }
        var totalVal = (countVal * amountVal).toFixed(2);
        jQuery(outputSelector).text(totalVal);
    }
    jQuery(document).on('keyup change','#DealChargeCycleCount,#DealChargeCycleAmount',function(){
        updateDealChargeTotalPreview('#DealChargeCycleCount','#DealChargeCycleAmount','#DealChargeTotalPreview');
    });
    jQuery(document).on('keyup change','#DealChargeEditCycleCount,#DealChargeEditCycleAmount',function(){
        updateDealChargeTotalPreview('#DealChargeEditCycleCount','#DealChargeEditCycleAmount','#DealChargeEditTotalPreview');
    });
    updateDealChargeTotalPreview('#DealChargeCycleCount','#DealChargeCycleAmount','#DealChargeTotalPreview');
    updateDealChargeTotalPreview('#DealChargeEditCycleCount','#DealChargeEditCycleAmount','#DealChargeEditTotalPreview');

    function hasChargeTypeOption($select, value){
        value = jQuery.trim(String(value || ''));
        if(value === ''){
            return false;
        }
        var hasMatch = false;
        $select.find('option').each(function(){
            if(jQuery.trim(String(jQuery(this).val() || '')) === value){
                hasMatch = true;
                return false;
            }
        });
        return hasMatch;
    }

    function syncChargeTypeUi(selectSelector, customInputSelector){
        var $select = jQuery(selectSelector);
        var $customInput = jQuery(customInputSelector);
        if(!$select.length || !$customInput.length){
            return;
        }
        var selectedValue = jQuery.trim(String($select.val() || ''));
        var $customWrap = $customInput.closest('.deal-charge-custom-wrap');
        if(selectedValue === '__custom__'){
            $select.removeAttr('name').prop('required', false);
            $customInput.attr('name','data[DealCharge][charge_name]').prop('required', true);
            $customWrap.show();
            return;
        }
        $select.attr('name','data[DealCharge][charge_name]').prop('required', true);
        $customInput.removeAttr('name').prop('required', false);
        $customWrap.hide();
    }

    function setChargeTypeUiValue(selectSelector, customInputSelector, rawValue){
        var $select = jQuery(selectSelector);
        var $customInput = jQuery(customInputSelector);
        if(!$select.length || !$customInput.length){
            return;
        }
        var value = jQuery.trim(String(rawValue || ''));
        $customInput.val('');
        if(value !== '' && hasChargeTypeOption($select, value)){
            $select.val(value);
        }else if(value !== ''){
            $select.val('__custom__');
            $customInput.val(value);
        }else{
            $select.val('');
        }
        syncChargeTypeUi(selectSelector, customInputSelector);
    }

    jQuery(document).on('change', '#DealChargeChargeNameSelect', function(){
        syncChargeTypeUi('#DealChargeChargeNameSelect', '#DealChargeChargeNameCustom');
    });
    jQuery(document).on('change', '#DealChargeEditNameSelect', function(){
        syncChargeTypeUi('#DealChargeEditNameSelect', '#DealChargeEditNameCustom');
    });

    setChargeTypeUiValue('#DealChargeChargeNameSelect', '#DealChargeChargeNameCustom', '');
    setChargeTypeUiValue('#DealChargeEditNameSelect', '#DealChargeEditNameCustom', '');

    jQuery(document).on('click','.openDealChargeEditModal',function(e){
        e.preventDefault();
        var $btn = jQuery(this);
        jQuery('#DealChargeEditId').val($btn.attr('data-charge-id') || '');
        setChargeTypeUiValue('#DealChargeEditNameSelect', '#DealChargeEditNameCustom', ($btn.attr('data-charge-name') || ''));
        jQuery('#DealChargeEditCycleType').val($btn.attr('data-cycle-type') || 'M');
        jQuery('#DealChargeEditCycleCount').val($btn.attr('data-cycle-count') || '1');
        jQuery('#DealChargeEditCycleAmount').val($btn.attr('data-cycle-amount') || '');
        jQuery('#DealChargeEditStartDate').val($btn.attr('data-start-date') || '');
        updateDealChargeTotalPreview('#DealChargeEditCycleCount','#DealChargeEditCycleAmount','#DealChargeEditTotalPreview');
        if(typeof jQuery.fn.modal === 'function'){
            jQuery('#dealChargeEditModal').modal('show');
        }
    });
});
</script>
<div id="resultDiv">
<div class="page-title"> <div class="title-env"> <h1 class="title"><?php echo __('Plans Payments');?></h1></div></div>
<div class="panel"><?php echo $this->Session->flash();?>
    <div class="panel-heading">
        <div class="btn-group">
            <?php $url=$this->Html->url(array('controller'=>'plans_payments')); if(!$Plan)echo $this->Html->link('<span class="fa fa-plus"></span>&nbsp;'.__('Add  Payments'),array('controller'=>'plans_payments','action'=>'add',$id),array('escape'=>false,'class'=>'btn btn-success'));?>
            <?php echo $this->Html->link('<span class="fa fa-flag"></span>&nbsp;'.__('Milestone'),array('controller'=>'plans_payments','action'=>'milestone',$id),array('escape'=>false,'class'=>'btn btn-primary'));?>
            <?php if(!empty($allowAddAction)){?>
            <a href="javascript:void(0);" class="btn btn-info" data-toggle="modal" data-target="#dealChargeModal"><span class="fa fa-plus-circle"></span>&nbsp;<?php echo __('Add Deal Charge');?></a>
            <?php }?>
            <?php echo $this->Html->link('<span class="fa fa-edit"></span>&nbsp;'.__('Edit'),'#',array('name'=>'editallfrm','id'=>'editallfrm','onclick'=>"editallplan();",'escape'=>false,'class'=>'btn btn-warning'));?>
	    <?php echo $this->Html->link('<span class="fa fa-arrow-left"></span>&nbsp;'.__('Back To Deal'),array('controller' => 'Deals','action'=>'index'),array('escape' => false,'class'=>'btn btn-default'));?>
	</div>
    </div>
        <?php echo $this->element('pagination');
        $pageParams = $this->Paginator->params();
        $limit = $pageParams['limit'];
        $page = $pageParams['page'];
        $serialNo = 1*$limit*($page-1)+1;?>
        <?php echo $this->Form->create(array('name'=>'deleteallfrm','action' => 'deleteall'));?>
<?php echo $this->Session->flash();?>
<div id="printable_content">
<?php echo $this->Html->css('print');?>
    <div class="panel-body"></div>
    <div class="table-responsive">
	<table class="table table-striped table-bordered">
	    <tr>
		<th><?php echo $this->Form->checkbox('checkbox', array('value'=>'deleteall','name'=>'selectAll','label'=>false,'id'=>'selectAll','hiddenField'=>false));?></th>
		<th><?php echo $this->Paginator->sort('id', __('#'), array('direction' => 'desc'));?></th>
		<th><?php echo $this->Paginator->sort('name', __('Installment'), array('direction' => 'asc'));?></th>
		<th><?php echo $this->Paginator->sort('amount', __('Scheduled Amount'), array('direction' => 'asc'));?></th>
		<th><?php echo __('Paid Amount');?></th>
		<th><?php echo __('Balance');?></th>
		<th><?php echo $this->Paginator->sort('date', __('Due Date'), array('direction' => 'asc'));?></th>
                <th><?php echo __('Status');?></th>
	    </tr>
	    <?php
            $totalScheduled = 0;
            $totalPaid = 0;
            $totalPending = 0;
	    foreach ($Plan as $post):
                $planId = (int)$post['PlansPayment']['id'];
                $scheduledAmt = (float)$post['PlansPayment']['amount'];
                $paidAmt = isset($planStatusMap[$planId]['paid']) ? (float)$planStatusMap[$planId]['paid'] : 0;
                $pendingAmt = isset($planStatusMap[$planId]['pending']) ? (float)$planStatusMap[$planId]['pending'] : max(0, $scheduledAmt - $paidAmt);

                $totalScheduled += $scheduledAmt;
                $totalPaid += $paidAmt;
                $totalPending += $pendingAmt;

                $statusText = 'Un-Paid';
                $statusClass = 'warning';
                if(isset($planStatusMap[$planId]['status']))
                {
                    $statusText = $planStatusMap[$planId]['status'];
                }
                if($statusText === 'Paid')
                {
                    $statusClass = 'success';
                }
                elseif($statusText === 'Partial')
                {
                    $statusClass = 'info';
                }
                elseif($statusText === 'Overdue')
                {
                    $statusClass = 'danger';
                }
	    ?>
	    <tr>
		<td><?php echo $this->Form->checkbox(false,array('value' => $post['PlansPayment']['id'],'name'=>'data[PlansPayment][id][]','id'=>"DeleteCheckbox$planId",'class'=>'chkselect'));?></td>
		<td><?php echo $serialNo++; ?></td>
		<td><strong><?php echo h($post['PlansPayment']['name']);?></strong></td>
		<td><?php echo $currency . $this->Number->format($scheduledAmt);?></td>
		<td><strong style="color:#10b981;"><?php echo $currency . $this->Number->format($paidAmt);?></strong></td>
		<td><span style="<?php echo $pendingAmt > 0 ? 'color:#e11d48;font-weight:700;' : 'color:#64748b;'; ?>"><?php echo $currency . $this->Number->format($pendingAmt);?></span></td>
		<td><?php echo $this->Time->format($sysDay.$dateSep.$sysMonth.$dateSep.$sysYear,h($post['PlansPayment']['date']));?></td>
                <td><span class="label label-<?php echo $statusClass;?>"><?php echo __($statusText);?></span></td>
	    </tr>
	    <?php endforeach; ?>
	    <?php unset($post);?>
            <tr class="summary-row" style="background:#f8fafc;font-weight:800;">
                <td></td>
                <td></td>
                <td align="right"><strong><?php echo __('Total');?></strong></td>
                <td><strong><?php echo $currency . $this->Number->format($totalScheduled);?></strong></td>
                <td><strong style="color:#10b981;"><?php echo $currency . $this->Number->format($totalPaid);?></strong></td>
                <td><strong style="color:#e11d48;"><?php echo $currency . $this->Number->format($totalPending);?></strong></td>
                <td></td>
                <td></td>
            </tr>
	</table>
    </div>

    <div class="panel panel-default deal-charge-panel">
        <div class="panel-heading"><strong><?php echo __('Deal Charges (Embankment Schedule)');?></strong></div>
        <div class="panel-body">
            <?php if(empty($dealChargeSummaryMap)){?>
                <div class="alert alert-info" style="margin-bottom:0;"><?php echo __('No deal charges added yet. Use "Add Deal Charge" to schedule Development / Extra Work or any custom charge type.');?></div>
            <?php }else{?>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <tr>
                            <th><?php echo __('#');?></th>
                            <th><?php echo __('Charge Type');?></th>
                            <th><?php echo __('Cycle');?></th>
                            <th><?php echo __('No. of Cycles');?></th>
                            <th><?php echo __('1st Cycle Start');?></th>
                            <th><?php echo __('Per Cycle');?></th>
                            <th><?php echo __('Total Charge');?></th>
                            <th><?php echo __('Paid');?></th>
                            <th><?php echo __('Pending');?></th>
                            <th><?php echo __('Action');?></th>
                        </tr>
                        <?php $chargeSn = 1; $chargeGrandTotal = 0; $chargePaidTotal = 0; $chargePendingTotal = 0; foreach($dealChargeSummaryMap as $chargeId=>$chargePack):
                            $chargeRow = isset($chargePack['row']) ? $chargePack['row'] : array();
                            $chargeTotal = isset($chargeRow['total_amount']) ? (float)$chargeRow['total_amount'] : 0;
                            $chargePaid = isset($chargePack['paid']) ? (float)$chargePack['paid'] : 0;
                            $chargePending = isset($chargePack['pending']) ? (float)$chargePack['pending'] : 0;
                            $chargeGrandTotal += $chargeTotal;
                            $chargePaidTotal += $chargePaid;
                            $chargePendingTotal += $chargePending;
                            $cycleKey = isset($chargeRow['cycle_type']) ? strtoupper((string)$chargeRow['cycle_type']) : 'M';
                            $cycleLabel = isset($dealChargeCycleTypeOptions[$cycleKey]) ? $dealChargeCycleTypeOptions[$cycleKey] : $cycleKey;
                            $startDateDisplay = !empty($chargeRow['start_date']) ? $this->Time->format($sysDay.$dateSep.$sysMonth.$dateSep.$sysYear,$chargeRow['start_date']) : '';
                        ?>
                        <tr>
                            <td><?php echo $chargeSn++;?></td>
                            <td><?php echo h(isset($chargeRow['charge_name']) ? $chargeRow['charge_name'] : '-');?></td>
                            <td><?php echo h($cycleLabel);?></td>
                            <td><?php echo (int)(isset($chargeRow['cycle_count']) ? $chargeRow['cycle_count'] : 0);?></td>
                            <td><?php echo !empty($chargeRow['start_date']) ? $this->Time->format($sysDay.$dateSep.$sysMonth.$dateSep.$sysYear,$chargeRow['start_date']) : '-';?></td>
                            <td><?php echo $currency.$this->Number->format(isset($chargeRow['cycle_amount']) ? $chargeRow['cycle_amount'] : 0);?></td>
                            <td><strong><?php echo $currency.$this->Number->format($chargeTotal);?></strong></td>
                            <td><?php echo $currency.$this->Number->format($chargePaid);?></td>
                            <td><?php echo $currency.$this->Number->format($chargePending);?></td>
                            <td>
                                <?php if(!empty($allowEditAction)){ ?>
                                    <a href="javascript:void(0);" class="btn btn-xs btn-warning openDealChargeEditModal"
                                       data-charge-id="<?php echo (int)$chargeId;?>"
                                       data-charge-name="<?php echo h(isset($chargeRow['charge_name']) ? $chargeRow['charge_name'] : '');?>"
                                       data-cycle-type="<?php echo h($cycleKey);?>"
                                       data-cycle-count="<?php echo (int)(isset($chargeRow['cycle_count']) ? $chargeRow['cycle_count'] : 1);?>"
                                       data-cycle-amount="<?php echo h(isset($chargeRow['cycle_amount']) ? $chargeRow['cycle_amount'] : 0);?>"
                                       data-start-date="<?php echo h($startDateDisplay);?>">
                                        <span class="fa fa-edit"></span> <?php echo __('Edit');?>
                                    </a>
                                <?php }?>
                                <?php if(!empty($allowDeleteAction)){ echo $this->Form->postLink('<span class="fa fa-trash"></span> '.__('Delete'),array('controller'=>'plans_payments','action'=>'deletedealcharge',$id,(int)$chargeId),array('escape'=>false,'class'=>'btn btn-xs btn-danger'),__('Delete this deal charge and all unpaid cycles?')); }elseif(empty($allowEditAction)){ echo '-'; } ?>
                            </td>
                        </tr>
                        <?php endforeach;?>
                        <tr class="summary-row">
                            <td></td>
                            <td colspan="5" align="right"><strong><?php echo __('Total Charges');?></strong></td>
                            <td><strong><?php echo $currency.$this->Number->format($chargeGrandTotal);?></strong></td>
                            <td><strong><?php echo $currency.$this->Number->format($chargePaidTotal);?></strong></td>
                            <td><strong><?php echo $currency.$this->Number->format($chargePendingTotal);?></strong></td>
                            <td></td>
                        </tr>
                    </table>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <tr>
                            <th><?php echo __('#');?></th>
                            <th><?php echo __('Charge Cycle');?></th>
                            <th><?php echo __('Due Date');?></th>
                            <th><?php echo __('Amount');?></th>
                            <th><?php echo __('Paid');?></th>
                            <th><?php echo __('Pending');?></th>
                            <th><?php echo __('Status');?></th>
                        </tr>
                        <?php $cycleSn = 1; foreach($dealChargeCycleRows as $cycleRow):
                            $statusText = isset($cycleRow['status']) ? $cycleRow['status'] : 'Un-Paid';
                            $statusClass = 'warning';
                            if($statusText === 'Paid'){$statusClass='success';}
                            elseif($statusText === 'Overdue'){$statusClass='danger';}
                        ?>
                        <tr>
                            <td><?php echo $cycleSn++;?></td>
                            <td><?php echo h(isset($cycleRow['name']) ? $cycleRow['name'] : '-');?></td>
                            <td><?php echo !empty($cycleRow['date']) ? $this->Time->format($sysDay.$dateSep.$sysMonth.$dateSep.$sysYear,$cycleRow['date']) : '-';?></td>
                            <td><?php echo $currency.$this->Number->format(isset($cycleRow['amount']) ? $cycleRow['amount'] : 0);?></td>
                            <td><?php echo $currency.$this->Number->format(isset($cycleRow['paid']) ? $cycleRow['paid'] : 0);?></td>
                            <td><?php echo $currency.$this->Number->format(isset($cycleRow['pending']) ? $cycleRow['pending'] : 0);?></td>
                            <td><span class="label label-<?php echo $statusClass;?>"><?php echo __($statusText);?></span></td>
                        </tr>
                        <?php endforeach;?>
                    </table>
                </div>
            <?php }?>
        </div>
    </div>
</div>
<?php echo $this->Form->end();?>
<?php echo $this->element('pagination');?>

<?php if(!empty($allowAddAction)){?>
<div class="modal fade" id="dealChargeModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><?php echo __('Add Deal Charge');?></h4>
            </div>
            <?php echo $this->Form->create('DealCharge',array('url'=>array('controller'=>'plans_payments','action'=>'adddealcharge',$id),'class'=>'form-horizontal','id'=>'DealChargeModalForm'));?>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label class="col-sm-4 control-label"><?php echo __('Charge Type');?></label>
                            <div class="col-sm-8">
                                <select id="DealChargeChargeNameSelect" class="form-control deal-charge-ui-select" name="data[DealCharge][charge_name]" required>
                                    <option value=""><?php echo __('Please Select');?></option>
                                    <?php foreach((array)$dealChargeTypeDropdownOptions as $chargeTypeValue=>$chargeTypeLabel){ ?>
                                        <option value="<?php echo h($chargeTypeValue);?>"><?php echo h($chargeTypeLabel);?></option>
                                    <?php } ?>
                                    <option value="__custom__"><?php echo __('Other (Type Custom)');?></option>
                                </select>
                                <div class="deal-charge-custom-wrap" style="display:none;">
                                    <input type="text" id="DealChargeChargeNameCustom" class="form-control" placeholder="<?php echo __('Type custom charge name');?>">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-4 control-label"><?php echo __('Cycle');?></label>
                            <div class="col-sm-8">
                                <?php echo $this->Form->select('DealCharge.cycle_type',$dealChargeCycleTypeOptions,array('class'=>'form-control','label'=>false,'div'=>false,'id'=>'DealChargeCycleType','empty'=>__('Please Select'),'required'=>true));?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-4 control-label"><?php echo __('No. of Cycles');?></label>
                            <div class="col-sm-8">
                                <input type="number" min="1" step="1" name="data[DealCharge][cycle_count]" id="DealChargeCycleCount" class="form-control" value="1" required>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label class="col-sm-4 control-label"><?php echo __('Amount / Cycle');?></label>
                            <div class="col-sm-8">
                                <input type="number" min="0.01" step="0.01" name="data[DealCharge][cycle_amount]" id="DealChargeCycleAmount" class="form-control" value="" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-4 control-label"><?php echo __('1st Cycle Start');?></label>
                            <div class="col-sm-8">
                                <div class="input-group date" id="dealChargeStartDatePicker">
                                    <input type="text" name="data[DealCharge][start_date]" id="DealChargeStartDate" class="form-control" autocomplete="off" required>
                                    <span class="input-group-addon"><span class="fa fa-calendar"></span></span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-4 control-label"><?php echo __('Total Charge');?></label>
                            <div class="col-sm-8">
                                <p class="form-control-static"><strong><?php echo $currency;?><span id="DealChargeTotalPreview">0.00</span></strong></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="alert alert-info" style="margin-bottom:0;">
                    <?php echo __('Charge cycles are added as a separate Deal Charges section in payment plan, and deal total updates automatically.');?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Close');?></button>
                <button type="submit" class="btn btn-success"><span class="fa fa-save"></span> <?php echo __('Save Deal Charge');?></button>
            </div>
            <?php echo $this->Form->end();?>
        </div>
    </div>
</div>
<?php }?>
<?php if(!empty($allowEditAction)){?>
<div class="modal fade" id="dealChargeEditModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><?php echo __('Edit Deal Charge');?></h4>
            </div>
            <?php echo $this->Form->create('DealCharge',array('url'=>array('controller'=>'plans_payments','action'=>'editdealcharge',$id),'class'=>'form-horizontal','id'=>'DealChargeEditModalForm'));?>
            <?php echo $this->Form->hidden('DealCharge.id',array('id'=>'DealChargeEditId'));?>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label class="col-sm-4 control-label"><?php echo __('Charge Type');?></label>
                            <div class="col-sm-8">
                                <select id="DealChargeEditNameSelect" class="form-control deal-charge-ui-select" name="data[DealCharge][charge_name]" required>
                                    <option value=""><?php echo __('Please Select');?></option>
                                    <?php foreach((array)$dealChargeTypeDropdownOptions as $chargeTypeValue=>$chargeTypeLabel){ ?>
                                        <option value="<?php echo h($chargeTypeValue);?>"><?php echo h($chargeTypeLabel);?></option>
                                    <?php } ?>
                                    <option value="__custom__"><?php echo __('Other (Type Custom)');?></option>
                                </select>
                                <div class="deal-charge-custom-wrap" style="display:none;">
                                    <input type="text" id="DealChargeEditNameCustom" class="form-control" placeholder="<?php echo __('Type custom charge name');?>">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-4 control-label"><?php echo __('Cycle');?></label>
                            <div class="col-sm-8">
                                <?php echo $this->Form->select('DealCharge.cycle_type',$dealChargeCycleTypeOptions,array('class'=>'form-control','label'=>false,'div'=>false,'id'=>'DealChargeEditCycleType','empty'=>__('Please Select'),'required'=>true));?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-4 control-label"><?php echo __('No. of Cycles');?></label>
                            <div class="col-sm-8">
                                <input type="number" min="1" step="1" name="data[DealCharge][cycle_count]" id="DealChargeEditCycleCount" class="form-control" value="1" required>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label class="col-sm-4 control-label"><?php echo __('Amount / Cycle');?></label>
                            <div class="col-sm-8">
                                <input type="number" min="0.01" step="0.01" name="data[DealCharge][cycle_amount]" id="DealChargeEditCycleAmount" class="form-control" value="" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-4 control-label"><?php echo __('1st Cycle Start');?></label>
                            <div class="col-sm-8">
                                <div class="input-group date" id="dealChargeEditStartDatePicker">
                                    <input type="text" name="data[DealCharge][start_date]" id="DealChargeEditStartDate" class="form-control" autocomplete="off" required>
                                    <span class="input-group-addon"><span class="fa fa-calendar"></span></span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-4 control-label"><?php echo __('Total Charge');?></label>
                            <div class="col-sm-8">
                                <p class="form-control-static"><strong><?php echo $currency;?><span id="DealChargeEditTotalPreview">0.00</span></strong></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="alert alert-warning" style="margin-bottom:0;">
                    <?php echo __('Safe Update: already paid cycles stay unchanged. Only unpaid cycles are regenerated with your new values.');?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Close');?></button>
                <button type="submit" class="btn btn-warning"><span class="fa fa-save"></span> <?php echo __('Update Deal Charge');?></button>
            </div>
            <?php echo $this->Form->end();?>
        </div>
    </div>
</div>
<?php }?>
</div>
<script type="text/javascript">
    function editallplan()
    {
        var target = '<?php echo $this->Html->url(array('controller'=>'plans_payments','action'=>'edit',$id));?>';
        show_modal(target);
    }
</script>
<style>
    .chkselect{display:none;}
    #selectAll{display: none;}
    .deal-charge-panel{margin-top:12px;}
    #dealChargeModal .form-control,
    #dealChargeEditModal .form-control{
        border-radius:4px;
    }
    #dealChargeModal select.form-control,
    #dealChargeEditModal select.form-control{
        height:34px;
        line-height:1.42857143;
        padding-right:34px;
        -webkit-appearance:none;
        -moz-appearance:none;
        appearance:none;
        background-color:#fff;
        border:1px solid #d5dce5;
        background-image:
            linear-gradient(45deg, transparent 50%, #5f6b7a 50%),
            linear-gradient(135deg, #5f6b7a 50%, transparent 50%),
            linear-gradient(to right, #e9edf2, #e9edf2);
        background-position:
            calc(100% - 16px) calc(50% - 3px),
            calc(100% - 10px) calc(50% - 3px),
            calc(100% - 2.2em) 0.45em;
        background-size:6px 6px, 6px 6px, 1px 1.8em;
        background-repeat:no-repeat;
    }
    #dealChargeModal .deal-charge-custom-wrap,
    #dealChargeEditModal .deal-charge-custom-wrap{
        margin-top:8px;
    }
</style>
