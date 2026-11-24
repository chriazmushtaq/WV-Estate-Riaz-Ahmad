<?php $dealPlanTemplatePreviewUrl=$this->Html->url(array('controller'=>'Deals','action'=>'plantemplatepreview')); ?>
<script type="text/javascript">
    function formatNomineeCnic(value){
        var digits = (value || '').replace(/\D/g,'');
        if(digits.length > 13){
            digits = digits.substring(0,13);
        }
        var out = '';
        if(digits.length > 0){
            out = digits.substring(0, Math.min(5, digits.length));
        }
        if(digits.length > 5){
            out += '-' + digits.substring(5, Math.min(12, digits.length));
        }
        if(digits.length > 12){
            out += '-' + digits.substring(12,13);
        }
        return out;
    }
    $(document).ready(function(){
        $('#startDate').datetimepicker({locale:'<?php echo$configLanguage;?>',format:'<?php echo $dpFormat;?>'});
        $('#DealNomineeCnic').on('input blur', function(){
            $(this).val(formatNomineeCnic($(this).val()));
        });
        function updateDealEditCalculations(){
            var gross = <?php echo json_encode((float)$dealPlanGrossTotal); ?>;
            var rawDisc = String($('#DealDiscount').val() || '0').replace(/,/g, '');
            var discount = Number(rawDisc);
            if(isNaN(discount) || discount < 0) discount = 0;
            if(discount > gross){
                discount = gross;
                $('#DealDiscount').val(gross);
            }
            var net = Math.max(0, gross - discount);
            var currency = <?php echo json_encode($currency); ?>;
            $('#dealEditPayableTotal').text(currency + net.toLocaleString('en-US'));
            $('#dealEditPayableSubtext').text(
                '<?php echo addslashes(__('Gross'));?>: ' + currency + gross.toLocaleString('en-US') + 
                ' | <?php echo addslashes(__('Discount'));?>: ' + currency + discount.toLocaleString('en-US')
            );
            loadDealPlanSummary();
        }
        function loadDealPlanSummary(){
            var planId=$('#DealPlanId').val();
            var $summary=$('#dealEditPlanSummary');
            if(!planId){$summary.text('<?php echo addslashes(__('Select a payment plan.'));?>');return;}
            $summary.text('<?php echo addslashes(__('Checking payment plan...'));?>');
            var gross=<?php echo json_encode((float)$dealPlanGrossTotal);?>;
            var rawDisc = String($('#DealDiscount').val() || '0').replace(/,/g, '');
            var discount = Number(rawDisc);
            if(isNaN(discount) || discount < 0) discount = 0;
            var net = Math.max(0, gross - discount);
            $.ajax({dataType:'json',cache:false,url:'<?php echo $dealPlanTemplatePreviewUrl;?>',data:{id:planId,deal_amount:gross,discount:discount}}).done(function(plan){
                if(!plan||!plan.enabled){
                    $summary.removeClass('is-match').addClass('is-warning').text('<?php echo addslashes(__('This plan has manual installments. Unpaid installments will be safely adjusted from the end backwards.'));?>');
                    return;
                }
                var generated=Number(plan.schedule_total||0);
                var ready=plan.ready!==false&&Math.abs(generated-net)<0.01;
                var adjustment=plan.adjustment_type==='extended'?'<?php echo addslashes(__('Schedule extended'));?>':(plan.adjustment_type==='reduced'?'<?php echo addslashes(__('Ending payments reduced'));?>':'<?php echo addslashes(__('Base schedule used'));?>');
                $summary.toggleClass('is-match',ready).toggleClass('is-warning',!ready).html(
                    '<?php echo addslashes(__('Plan Base Total'));?>: Rs '+Number(plan.total_amount).toLocaleString('en-US')+
                    ' &nbsp;|&nbsp; <?php echo addslashes(__('Discount'));?>: Rs '+discount.toLocaleString('en-US')+
                    ' &nbsp;|&nbsp; <?php echo addslashes(__('Deal Total'));?>: Rs '+net.toLocaleString('en-US')+
                    ' &nbsp;|&nbsp; <?php echo addslashes(__('Generated Schedule'));?>: Rs '+generated.toLocaleString('en-US')+
                    ' &nbsp;|&nbsp; <?php echo addslashes(__('Payments'));?>: '+Number(plan.payment_count).toLocaleString('en-US')+
                    ' &nbsp;|&nbsp; '+adjustment+
                    ' &nbsp;|&nbsp; '+(ready?'<?php echo addslashes(__('Ready'));?>':'<?php echo addslashes(__('Please check values'));?>')
                );
            }).fail(function(){
                $summary.removeClass('is-match').addClass('is-warning').text('<?php echo addslashes(__('Unable to load payment-plan details.'));?>');
            });
        }
        $('#DealPlanId').on('change',loadDealPlanSummary);
        $('#DealDiscount').on('input change blur',updateDealEditCalculations);
        loadDealPlanSummary();
    });
    function setDealEditActionMode(mode){
        var field = document.getElementById('DealActionMode');
        if(field){
            field.value = mode || 'save_deal';
        }
        return true;
    }
</script>
<style>
.deal-edit-shell{margin:0 auto;max-width:1180px;padding:0;}
.deal-edit-panel{border:1px solid #d8e4f0;border-radius:12px;box-shadow:0 14px 34px rgba(15,23,42,.08);overflow:hidden;}
.deal-edit-panel .panel-heading{align-items:center;background:linear-gradient(135deg,#11233c,#354764);color:#fff;display:flex;justify-content:space-between;padding:16px 20px;}
.deal-edit-panel .panel-heading .close{color:#fff;opacity:1;text-shadow:none;}
.deal-edit-panel .panel-body{background:#f7faff;padding:14px;}
.deal-edit-form-grid{display:grid;gap:12px;grid-template-columns:repeat(3,minmax(0,1fr));}
.deal-edit-card{background:#fff;border:1px solid #d9e3ef;border-radius:8px;box-shadow:0 6px 14px rgba(15,23,42,.04);padding:14px;}
.deal-edit-card h4{color:#132a46;font-size:16px;font-weight:800;margin:0 0 12px;}
.deal-edit-field{margin-bottom:10px;}
.deal-edit-field:last-child{margin-bottom:0;}
.deal-edit-field label{color:#50627a;display:block;font-size:11px;font-weight:800;letter-spacing:.02em;margin:0 0 6px;text-transform:uppercase;}
.deal-edit-field .form-control,.deal-edit-field .input-group-addon{border-color:#cfdceb;box-shadow:none;}
.deal-edit-field .form-control{border-radius:8px;height:42px;}
.deal-edit-field textarea.form-control{height:auto;min-height:90px;resize:vertical;}
.deal-edit-field .input-group.date{width:100%;}
.deal-edit-field .input-group.date .form-control{border-radius:8px 0 0 8px;}
.deal-edit-field .input-group-addon{background:#eef5fb;border-radius:0 8px 8px 0;}
.deal-edit-static{background:#f8fbff;border:1px solid #d8e6f3;border-radius:8px;color:#11233c;font-size:18px;font-weight:800;min-height:42px;padding:10px 12px;}
.deal-edit-static-sub{color:#5f7288;display:block;font-size:11px;font-weight:700;margin-top:4px;}
.deal-edit-span-2{grid-column:span 2;}
.deal-edit-span-3{grid-column:1 / -1;}
.deal-edit-nominee-grid{display:grid;gap:10px 12px;grid-template-columns:repeat(2,minmax(0,1fr));}
.deal-edit-nominee-grid .deal-edit-field{margin-bottom:0;}
.deal-edit-info{background:#edf7ff;border:1px solid #cfe6fb;border-radius:10px;color:#21476f;font-size:13px;font-weight:700;margin-bottom:14px;padding:12px 14px;}
.deal-edit-commission-grid{display:grid;gap:14px;grid-template-columns:repeat(3,minmax(0,1fr));}
.deal-edit-actions{align-items:center;display:flex;flex-wrap:wrap;gap:10px;justify-content:flex-start;}
.deal-edit-actions .btn{border-radius:8px;font-weight:800;min-width:120px;padding:10px 16px;}
.deal-edit-submit-bar{background:#fff;border:1px solid #d9e3ef;border-radius:10px;box-shadow:0 8px 18px rgba(15,23,42,.04);display:flex;gap:10px;justify-content:flex-start;margin-top:18px;padding:16px;}
.deal-edit-plan-summary{background:#f7fafc;border:1px solid #d8e4f0;border-radius:8px;color:#52657a;font-size:12px;font-weight:800;line-height:1.7;margin-top:10px;padding:10px 12px;}
.deal-edit-plan-summary.is-match{background:#ecfdf5;border-color:#a7f3d0;color:#08785d;}
.deal-edit-plan-summary.is-warning{background:#fff7ed;border-color:#fed7aa;color:#9a4c08;}
.deal-edit-plan-lock{background:#fff7ed;border:1px solid #fed7aa;border-radius:8px;color:#9a4c08;font-size:12px;font-weight:700;margin-top:10px;padding:10px 12px;}
@media(max-width:991px){
    .deal-edit-form-grid,.deal-edit-commission-grid{grid-template-columns:repeat(2,minmax(0,1fr));}
    .deal-edit-span-2{grid-column:span 2;}
}
@media(max-width:640px){
    .deal-edit-panel .panel-body{padding:14px;}
    .deal-edit-form-grid,.deal-edit-commission-grid{grid-template-columns:1fr;}
    .deal-edit-span-2,.deal-edit-span-3{grid-column:auto;}
    .deal-edit-nominee-grid{grid-template-columns:1fr;}
    .deal-edit-submit-bar{flex-direction:column;}
    .deal-edit-actions .btn{width:100%;}
}
</style>
<div class="deal-edit-shell<?php if(!$isError){ echo ' container-fluid'; } ?>">
    <div class="panel panel-custom mrg deal-edit-panel">
        <div class="panel-heading">
            <span><?php echo __('Edit Deal');?></span>
            <?php if(!$isError){?><button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button><?php }?>
        </div>
        <div class="panel-body">
            <?php echo $this->Session->flash();?>
            <?php if(isset($hasNomineeColumns) && !$hasNomineeColumns){ ?>
            <div class="alert alert-warning">
                <?php echo __('Nominee DB columns could not be created automatically. Please update deals table to save nominee data.'); ?>
            </div>
            <?php } ?>
            <?php echo $this->Form->create('Deal', array('controller' => 'Deals', 'action' => 'edit', $post['Deal']['id'], 'name'=>'post_req', 'id'=>'post_req'));?>
            <?php echo $this->Form->hidden('action_mode',array('value'=>'save_deal'));?>

            <div class="deal-edit-form-grid">
                <div class="deal-edit-card">
                    <h4><?php echo __('Deal Basics');?></h4>
                    <div class="deal-edit-field">
                        <label><?php echo __('Invoice Date');?></label>
                        <div class="input-group date" id="startDate">
                            <?php echo $this->Form->input('date',array('type'=>'text','value'=>$this->Time->format($dtFormat,$post['Deal']['date']),'label'=>false,'class'=>'form-control','div'=>false));?>
                            <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
                        </div>
                    </div>
                    <div class="deal-edit-field">
                        <label><?php echo __('Membership').' #';?></label>
                        <?php echo $this->Form->input('invoice_no',array('label'=>false,'class'=>'form-control','div'=>false));?>
                    </div>
                    <div class="deal-edit-field">
                        <label><?php echo __('Occupation');?></label>
                        <input type="text" name="data[Deal][occupation]" id="DealOccupation" class="form-control" maxlength="150" placeholder="<?php echo h(__('Client Occupation'));?>" value="<?php echo h(isset($post['Deal']['occupation']) ? $post['Deal']['occupation'] : '');?>">
                    </div>
                </div>

                <div class="deal-edit-card">
                    <h4><?php echo __('Price & Discount');?></h4>
                    <div class="deal-edit-field">
                        <label><?php echo __('Property Price (Gross)');?></label>
                        <div class="deal-edit-static" id="dealEditGrossPrice">
                            <?php echo $currency . $this->Number->format($dealPlanGrossTotal); ?>
                        </div>
                    </div>
                    <div class="deal-edit-field">
                        <label><?php echo __('Discount');?></label>
                        <?php echo $this->Form->input('discount', array('id' => 'DealDiscount', 'type' => 'text', 'value' => $dealPlanDiscount, 'label' => false, 'class' => 'form-control', 'placeholder' => __('Enter Discount Amount'), 'div' => false)); ?>
                    </div>
                    <div class="deal-edit-field">
                        <label><?php echo __('Net Payable Total');?></label>
                        <div class="deal-edit-static" id="dealEditPayableTotal" style="color: #08785d; font-weight: 800;">
                            <?php echo $currency . $this->Number->format($dealPlanRegularTotal); ?>
                        </div>
                        <span class="deal-edit-static-sub" id="dealEditPayableSubtext">
                            <?php echo sprintf(__('Gross: %s | Discount: %s'), $currency . $this->Number->format($dealPlanGrossTotal), $currency . $this->Number->format($dealPlanDiscount)); ?>
                        </span>
                    </div>
                </div>

                <div class="deal-edit-card">
                    <h4><?php echo __('Payment Plan');?></h4>
                    <div class="deal-edit-field">
                        <label><?php echo __('Selected Plan');?></label>
                        <?php echo $this->Form->select('plan_id',$dealPlanOptions,array('id'=>'DealPlanId','empty'=>__('Please Select'),'label'=>false,'class'=>'form-control','div'=>false));?>
                    </div>
                    <div id="dealEditPlanSummary" class="deal-edit-plan-summary"></div>
                    <span class="deal-edit-static-sub"><?php echo sprintf(__('Current schedule: %d payments'),(int)$dealPlanScheduleCount);?></span>
                    <?php if(!empty($dealPlanReceiptCount)){ ?>
                    <div class="deal-edit-plan-lock">
                        <?php echo sprintf(__('%d receipt(s) are linked. The current plan is protected from replacement.'),(int)$dealPlanReceiptCount);?>
                    </div>
                    <?php } ?>
                </div>

                <div class="deal-edit-card">
                    <h4><?php echo __('Nominee & Identity');?></h4>
                    <div class="deal-edit-nominee-grid">
                        <div class="deal-edit-field">
                            <label><?php echo __('Nominee');?></label>
                            <?php echo $this->Form->input('nominee_name',array('label'=>false,'class'=>'form-control','placeholder'=>__('Nominee Name'),'div'=>false));?>
                        </div>
                        <div class="deal-edit-field">
                            <label><?php echo __('N.Mobile');?></label>
                            <?php echo $this->Form->input('nominee_mobile',array('label'=>false,'class'=>'form-control','placeholder'=>__('+923001234567'),'div'=>false));?>
                        </div>
                        <div class="deal-edit-field">
                            <label><?php echo __('N.CNIC');?></label>
                            <?php echo $this->Form->input('nominee_cnic',array('label'=>false,'class'=>'form-control','placeholder'=>__('35301-1657035-3'),'maxlength'=>'15','div'=>false));?>
                        </div>
                        <div class="deal-edit-field">
                            <label><?php echo __('Relation');?></label>
                            <?php echo $this->Form->select('nominee_relation',$nomineeRelationOptions,array('empty'=>__('Please Select'),'label'=>false,'class'=>'form-control','div'=>false));?>
                        </div>
                    </div>
                </div>

                <div class="deal-edit-card deal-edit-span-3">
                    <h4><?php echo __('Remarks');?></h4>
                    <div class="deal-edit-field">
                        <?php echo $this->Form->textarea('remarks',array('label'=>false,'class'=>'form-control','placeholder'=>__('Comment/Remarks'),'div'=>false,'rows'=>4));?>
                    </div>
                </div>

                <?php if(!empty($canTransferCommissionAgent)){ ?>
                <div class="deal-edit-card deal-edit-span-3">
                    <h4><?php echo __('Change Commission Agent');?></h4>
                    <div class="deal-edit-info">
                        <?php echo __('This safely moves the one-time deal commission credit from the current agent wallet to a new agent and updates the deal agent record.'); ?>
                    </div>
                    <div class="deal-edit-commission-grid">
                        <div class="deal-edit-field">
                            <label><?php echo __('Current Agent');?></label>
                            <div class="deal-edit-static">
                                <?php echo !empty($currentAgentDisplay) ? h($currentAgentDisplay) : (!empty($post['User']['name']) ? h($post['User']['name']) : __('No Agent Assigned'));?>
                            </div>
                        </div>
                        <div class="deal-edit-field">
                            <label><?php echo __('Current Commission');?></label>
                            <div class="deal-edit-static">
                                <?php echo $currency.$this->Number->format(!empty($currentDealCommissionAmount) ? $currentDealCommissionAmount : 0);?>
                            </div>
                        </div>
                        <div class="deal-edit-field">
                            <label><?php echo __('New Agent');?></label>
                            <?php echo $this->Form->select('CommissionTransfer.new_agent_id',$commissionTransferAgents,array('empty'=>__('Keep Current Agent'),'class'=>'form-control','label'=>false,'div'=>false));?>
                        </div>
                        <div class="deal-edit-field">
                            <label><?php echo __('New Commission');?></label>
                            <?php echo $this->Form->input('CommissionTransfer.commission_amount',array('type'=>'text','value'=>!empty($currentDealCommissionAmount) ? $currentDealCommissionAmount : 0,'class'=>'form-control','label'=>false,'div'=>false,'placeholder'=>__('Commission Amount')));?>
                        </div>
                        <div class="deal-edit-field deal-edit-span-2">
                            <label><?php echo __('Reason');?></label>
                            <?php echo $this->Form->textarea('CommissionTransfer.reason',array('rows'=>3,'class'=>'form-control','label'=>false,'div'=>false,'placeholder'=>__('Optional internal note for this agent change')));?>
                        </div>
                        <div class="deal-edit-field">
                            <label>&nbsp;</label>
                            <div class="deal-edit-actions">
                                <button type="submit" class="btn btn-warning" onclick="setDealEditActionMode('transfer_commission_agent'); return confirm('<?php echo addslashes(__('Move this deal commission and assigned agent to the selected new agent?'));?>');">
                                    <span class="fa fa-random"></span>&nbsp;<?php echo __('Change Commission Agent');?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>

            <div class="deal-edit-submit-bar">
                <button type="submit" class="btn btn-success" onclick="setDealEditActionMode('save_deal');"><span class="fa fa-plus-circle"></span>&nbsp;<?php echo __('Save');?></button>
                <?php if(!$isError){?><button type="button" class="btn btn-danger" data-dismiss="modal"><span class="fa fa-remove"></span>&nbsp;<?php echo __('Cancel');?></button><?php }?>
            </div>
            <?php echo $this->Form->end();?>
        </div>
    </div>
</div>
