<?php
echo $this->Html->css('select2/select2');
echo $this->Html->css('select2/select2-bootstrap');
echo $this->Html->script('select2.min');
$clientUrl=$this->Html->url(array('controller'=>'Deals','action'=>'clientsearch'));
$propertyUrl=$this->Html->url(array('controller'=>'Deals','action'=>'propertysearch'));
$propertyFlat=$this->Html->url(array('controller'=>'Deals','action'=>'propertyflat'));
$flatDetails=$this->Html->url(array('controller'=>'Deals','action'=>'flatdetails'));
$propertyDetails=$this->Html->url(array('controller'=>'Deals','action'=>'propertydetails'));
$propertyPlans=$this->Html->url(array('controller'=>'Deals','action'=>'plandetails'));
$invoicePreviewUrl=$this->Html->url(array('controller'=>'Deals','action'=>'dealinvoicepreview'));
$planTemplatePreviewUrl=$this->Html->url(array('controller'=>'Deals','action'=>'plantemplatepreview'));
?>
<script type="text/javascript">
    var selectedDealPlanPreview=null;
    var dealPlanPreviewTimer=null;
    var dealPlanPreviewRequest=null;
    function renderDealPlanComparison(){
        var $info=$('#dealPlanTemplateInfo');
        if(!selectedDealPlanPreview){return;}
        var gross=Number(String($('#DealAmount').val()||'0').replace(/,/g,''));
        var discount=Number(String($('#DealDiscount').val()||'0').replace(/,/g,''));
        var dealTotal=Math.max(0,gross-discount);
        var planTotal=Number(selectedDealPlanPreview.total_amount||0);
        var adjustedSchedule=Number(selectedDealPlanPreview.schedule_total||0);
        var ready=selectedDealPlanPreview.ready!==false&&Math.abs(adjustedSchedule-dealTotal)<0.01;
        var adjustmentType=selectedDealPlanPreview.adjustment_type||'exact';
        var adjustmentLabel=adjustmentType==='extended'?'Schedule extended':(adjustmentType==='reduced'?'Ending payments reduced':'Base schedule used');
        if(selectedDealPlanPreview.message){adjustmentLabel=selectedDealPlanPreview.message;}
        $info.css('color',ready?'#08785d':'#b45309').html(
            'Plan Base Total: Rs '+planTotal.toLocaleString('en-US')+
            ' &nbsp;|&nbsp; Property Amount: Rs '+gross.toLocaleString('en-US')+
            ' &nbsp;|&nbsp; Discount: Rs '+discount.toLocaleString('en-US')+
            ' &nbsp;|&nbsp; Deal Total: Rs '+dealTotal.toLocaleString('en-US')+
            ' &nbsp;|&nbsp; Generated Schedule: Rs '+adjustedSchedule.toLocaleString('en-US')+
            ' &nbsp;|&nbsp; Payments: '+Number(selectedDealPlanPreview.payment_count||0).toLocaleString('en-US')+
            ' &nbsp;|&nbsp; '+adjustmentLabel+
            ' &nbsp;|&nbsp; '+(ready?'Ready':'Please check values')
        );
    }
    function loadDealPlanPreview(){
        var planId=$('#DealPlanId').val();
        if(!planId){selectedDealPlanPreview=null;return;}
        if(dealPlanPreviewRequest){dealPlanPreviewRequest.abort();}
        dealPlanPreviewRequest=$.ajax({
            dataType:'json',cache:false,url:'<?php echo $planTemplatePreviewUrl;?>',
            data:{
                id:planId,
                deal_amount:String($('#DealAmount').val()||'0').replace(/,/g,''),
                discount:String($('#DealDiscount').val()||'0').replace(/,/g,'')
            }
        }).done(function(plan){
            if(plan&&plan.enabled){
                selectedDealPlanPreview=plan;
                $('#DealBookingAmount').val(plan.booking_amount).prop('readonly',true);
                renderDealPlanComparison();
            }else{
                selectedDealPlanPreview=null;
                $('#DealBookingAmount').prop('readonly',false);
                $('#dealPlanTemplateInfo').css('color','#b45309').text('This plan has no automatic schedule template.');
            }
        });
    }
    function queueDealPlanPreview(){
        clearTimeout(dealPlanPreviewTimer);
        dealPlanPreviewTimer=setTimeout(loadDealPlanPreview,180);
    }
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
        calculateAmount();
        $('#startDate').datetimepicker({locale:'<?php echo$configLanguage;?>',format:'DD-MMM-YYYY'});
        $('#startDate').on('dp.show', function(){
            $('#DealDate').trigger('focus');
        });
        $('#startDate .input-group-addon, #DealDate').on('click', function(){
            $('#startDate').data('DateTimePicker').show();
        });
        $('#evocate_date').datetimepicker({locale:'<?php echo$configLanguage;?>',format:'<?php echo $dpFormat;?>'});
        $('#recurring').hide();
        $('#rec_type').click(function () {$("#recurring").toggle(this.checked);});
        $('#DealDiscount').on('input change blur',function (){calculateAmount();});
        $('#DealPlanId').on('change',function(){
            var planId=$(this).val();
            $('#dealPlanTemplateInfo').empty();
            $('#DealBookingAmount').prop('readonly',false);
            selectedDealPlanPreview=null;
            if(!planId)return;
            loadDealPlanPreview();
        });
        $('#clientId').select2({
        placeholder: "Search Client Name",
        minimumInputLength: 1,
        ajax: {
          url: '<?php echo$clientUrl;?>',
          dataType: 'json',
          data: function (term, page) {
            return {
              q: term
            };
          },
          
          results: function (data, page) {
            return { results: data };
          }
        }
      });
        $('#propertyId').select2({
        placeholder: "Search Property Name",
        minimumInputLength: 1,
        ajax: {
          url: '<?php echo$propertyUrl;?>',
          dataType: 'json',
          data: function (term, page) {
            return {
              q: term,
              //q1: $('input[name="typelead"]:checked').val(),
              //q2: $('input[name="availiable"]:checked').val(),
              q1: $('#typeId').val(),
              q2: $('#contractId').val(),
              q3: $('#dealProjectId').val()
            };
          },
          results: function (data, page) {
            return { results: data };
          }
        }
        }).on("change", function(e) {
          $.ajax({
      dataType: 'json',
      cache: false ,
      url: '<?php echo$propertyDetails;?>',
      data: {id: e.val}})
      .done(function(data) {
        $.each(data,function(index,item){
           if(index=="#agentName"){
            $('#agentName').html(item);
           }
           else{
            $(index).val(item);
           }
           });
        calculateAmount();
        if($('#contractId').val()==1){$('#EvocateDate').show();}else $('#EvocateDate').hide();
      });
        });
        $('#dealProjectId').change(function(){
            if($('#propertyId').data('select2')){
                $('#propertyId').select2('val','');
            }else{
                $('#propertyId').val('');
            }
            $('#DealArea').val('');
            $('#DealAmount').val('');
            $('#agentName').html('<option value=""><?php echo addslashes(__('Please Select'));?></option>');
            $.ajax({
                dataType: 'json',
                cache: false,
                url: '<?php echo $invoicePreviewUrl;?>',
                data: {project_id: $('#dealProjectId').val()}
            }).done(function(res){
                if(res && res.invoice_no){
                    $('#DealInvoiceNo').val(res.invoice_no);
                }
            });
            calculateAmount();
        });
        if($('#dealProjectId').length && $('#dealProjectId').val()){
            $('#dealProjectId').trigger('change');
        }
    });
    function calculateAmount()
    {
        var discount=0;
        dealArea=$('#DealArea').val();
        dealAmount=$('#DealAmount').val();
        dealDiscount=$('#DealDiscount').val();
        if(dealDiscount!="")
        discount=dealDiscount;
        totalAmount=(parseFloat(dealAmount)-parseFloat(discount));
        if(isNaN(totalAmount))
        totalAmount=0;
        $('#totalAmount').html(getLocale('Area')+' : ' + dealArea+ '<br/>'+getLocale('Amount')+' : ' + dealAmount+ '<br/>'+getLocale('Discount')+' : ' + discount+ '<br/>'+getLocale('Payable Amount')+' : '+totalAmount);
        queueDealPlanPreview();
    }
</script>
<script type="text/javascript">
    $(document).ready(function(){
        //$('#EvocateDate').hide();
        //$('#DealAvailiableRent').click(function(){$('#EvocateDate').show();});
        //$('#DealAvailiableSale').click(function(){$('#EvocateDate').hide();});
        $('#EvocateDate').hide();
        $('#contractId').change(function(){ if(this.value==1)$('#EvocateDate').show();else $('#EvocateDate').hide();});
        if($('#contractId').val()==1){$('#EvocateDate').show();}else $('#EvocateDate').hide();
        $('#DealNomineeCnic').on('input blur', function(){
            $(this).val(formatNomineeCnic($(this).val()));
        });
        $('#post_req').on('submit', function(e){
            var propVal = $('#propertyId').val();
            if(!propVal || propVal === '' || propVal === '0'){
                e.preventDefault();
                alert('<?php echo addslashes(__('Please search and select a property from the dropdown list before saving.')); ?>');
                if($('#propertyId').data('select2')){
                    $('#propertyId').select2('focus');
                } else {
                    $('#propertyId').focus();
                }
                return false;
            }
            var clientVal = $('#clientId').val();
            if(!clientVal || clientVal === '' || clientVal === '0'){
                e.preventDefault();
                alert('<?php echo addslashes(__('Please search and select a client from the dropdown list before saving.')); ?>');
                if($('#clientId').data('select2')){
                    $('#clientId').select2('focus');
                } else {
                    $('#clientId').focus();
                }
                return false;
            }
        });
    });
</script>
<style>
.deal-add-shell{margin:0 auto;max-width:1400px;}
.deal-add-panel{border:1px solid #d8e4f0;border-radius:12px;box-shadow:0 14px 34px rgba(15,23,42,.08);overflow:hidden;}
.deal-add-panel .panel-heading{background:linear-gradient(135deg,#11233c,#5a2a58);min-height:42px;padding:0;}
.deal-add-panel .panel-body{background:#f7faff;padding:18px;}
.deal-add-grid{display:grid;gap:18px;grid-template-columns:repeat(3,minmax(0,1fr));}
.deal-add-card{background:#fff;border:1px solid #d9e3ef;border-radius:10px;box-shadow:0 8px 18px rgba(15,23,42,.04);padding:16px;}
.deal-add-card h4{color:#132a46;font-size:16px;font-weight:800;margin:0 0 14px;}
.deal-add-field{margin-bottom:14px;}
.deal-add-field:last-child{margin-bottom:0;}
.deal-add-field label{color:#50627a;display:block;font-size:11px;font-weight:800;letter-spacing:.02em;margin:0 0 6px;text-transform:uppercase;}
.deal-add-field .form-control,.deal-add-field .input-group-addon{border-color:#cfdceb;box-shadow:none;}
.deal-add-field .form-control{border-radius:8px;height:42px;}
.deal-add-field textarea.form-control{height:auto;min-height:114px;resize:vertical;}
.deal-add-field .input-group.date{width:100%;}
.deal-add-field .input-group.date .form-control{border-radius:8px 0 0 8px;}
.deal-add-field .input-group-addon{background:#eef5fb;border-radius:0 8px 8px 0;}
.deal-add-date-half{max-width:250px;}
.deal-add-date-half .input-group-addon{cursor:pointer;min-width:46px;text-align:center;}
.deal-add-date-half .form-control{cursor:pointer;}
.deal-add-span-2{grid-column:span 2;}
.deal-add-span-3{grid-column:1 / -1;}
.deal-add-summary{background:#f8fbff;border:1px solid #d8e6f3;border-radius:10px;color:#11233c;line-height:1.8;min-height:114px;padding:14px;}
.deal-add-summary-card{background:linear-gradient(135deg,#132845,#355178);border:0;box-shadow:0 16px 32px rgba(17,35,60,.16);color:#fff;}
.deal-add-summary-card h4{color:#ffffff !important;font-size:17px;font-weight:900;margin-bottom:14px;}
.deal-add-summary-card .deal-add-summary{background:rgba(255,255,255,.10);border:1px solid rgba(255,255,255,.20);border-radius:12px;color:#ffffff !important;font-size:16px;font-weight:800;min-height:184px;padding:20px 18px;}
.deal-add-summary-card #totalAmount{color:#ffffff !important;display:block;line-height:2.1;text-shadow:0 1px 1px rgba(0,0,0,.18);}
.deal-add-actions{background:#fff;border:1px solid #d9e3ef;border-radius:10px;box-shadow:0 8px 18px rgba(15,23,42,.04);display:flex;gap:10px;justify-content:flex-start;margin-top:18px;padding:16px;}
.deal-add-actions .btn{border-radius:8px;font-weight:800;min-width:110px;padding:10px 16px;}
@media(max-width:1199px){.deal-add-grid{grid-template-columns:repeat(2,minmax(0,1fr));}.deal-add-span-2{grid-column:span 2;}}
@media(max-width:640px){.deal-add-grid{grid-template-columns:1fr;}.deal-add-span-2,.deal-add-span-3{grid-column:auto;}.deal-add-actions{flex-direction:column;}.deal-add-actions .btn{width:100%;}}
</style>

<div class="page-title"> <div class="title-env"> <h1 class="title"><?php echo __('Add Deal');?></h1></div></div>
<div class="panel deal-add-panel">
    <div class="panel-heading"></div>
    <div class="panel-body"><?php echo $this->Session->flash();?>
        <?php if(isset($hasNomineeColumns) && !$hasNomineeColumns){ ?>
        <div class="alert alert-warning">
            <?php echo __('Nominee DB columns could not be created automatically. Please update deals table to save nominee data.'); ?>
        </div>
        <?php } ?>
        <?php echo $this->Form->create('Deal', array('controller' => 'Deals', 'action' => 'add','name'=>'post_req','id'=>'post_req'));?>
        <div class="deal-add-shell">
            <div class="deal-add-grid">
                <div class="deal-add-card">
                    <h4><?php echo __('Agency & Client');?></h4>
                    <?php if(!empty($canUseAgencyFilters)){ ?>
                    <div class="deal-add-field">
                        <label><?php echo __('Agency');?></label>
                        <?php echo $this->Form->select('Filter.project_id',$projectName,array('id'=>'dealProjectId','label'=>false,'class'=>'form-control','div'=>false,'empty'=>__('Please Select'),'value'=>$selectedDealProjectId));?>
                    </div>
                    <?php } else { ?>
                    <?php echo $this->Form->hidden('Filter.project_id',array('id'=>'dealProjectId','value'=>$selectedDealProjectId));?>
                    <?php } ?>
                    <div class="deal-add-field">
                        <label><?php echo __('Deal On');?></label>
                        <?php echo $this->Form->input('client_id',array('type'=>'text','id'=>'clientId','placeholder'=>__('Client Name'),'label'=>false,'class'=>'form-control','div'=>false));?>
                    </div>
                    <div class="deal-add-field">
                        <label><?php echo __('Occupation');?></label>
                        <?php echo $this->Form->input('occupation',array('type'=>'text','maxlength'=>150,'placeholder'=>__('Client Occupation'),'label'=>false,'class'=>'form-control','div'=>false));?>
                    </div>
                </div>

                <div class="deal-add-card">
                    <h4><?php echo __('Property Selection');?></h4>
                    <div class="deal-add-field">
                        <label><?php echo __('Deal For');?></label>
                        <?php echo $this->Form->select('type_id',$type,array('id'=>'typeId','label'=>false,'class'=>'form-control','empty'=>__('Please Select'),'div'=>false));?>
                    </div>
                    <div class="deal-add-field">
                        <label><?php echo __('Available For');?></label>
                        <?php echo $this->Form->select('contract_id',$contract,array('id'=>'contractId','label'=>false,'class'=>'form-control','empty'=>__('Please Select'),'div'=>false));?>
                    </div>
                    <div class="deal-add-field">
                        <label><?php echo __('Property');?></label>
                        <?php echo $this->Form->input('property_id',array('type'=>'text','id'=>'propertyId','placeholder'=>__('Property Name'),'label'=>false,'class'=>'form-control','div'=>false));?>
                    </div>
                </div>

                <div class="deal-add-card">
                    <h4><?php echo __('Invoice & Booking');?></h4>
                    <div class="deal-add-field" id="EvocateDate">
                        <label><?php echo __('Evocate Date');?></label>
                        <div class="input-group date" id="evocate_date">
                            <?php echo $this->Form->input('evocate_date',array('type'=>'text','label'=>false,'class'=>'form-control','placeholder'=>__('Evocate Date'),'div'=>false));?>
                            <span class="input-group-addon"><span class="fa fa-calendar"></span></span>
                        </div>
                    </div>
                    <div class="deal-add-field">
                        <label><?php echo __('Invoice').' #';?></label>
                        <?php echo $this->Form->input('invoice_no',array('value'=>$invoiceNo,'label'=>false,'class'=>'form-control','div'=>false));?>
                    </div>
                    <div class="deal-add-field">
                        <label><?php echo __('Invoice Date');?></label>
                        <div class="input-group date deal-add-date-half" id="startDate">
                            <?php echo $this->Form->input('date',array('type'=>'text','value'=>date('d-M-Y'),'label'=>false,'placeholder'=>'02-Jun-2026','class'=>'form-control','div'=>false));?>
                            <span class="input-group-addon"><span class="fa fa-calendar"></span></span>
                        </div>
                    </div>
                    <div class="deal-add-field">
                        <label><?php echo __('Booking Amount');?></label>
                        <?php echo $this->Form->input('booking_amount',array('label'=>false,'placeholder'=>__('Enter amount'),'class'=>'form-control','div'=>false));?>
                    </div>
                </div>

                <div class="deal-add-card">
                    <h4><?php echo __('Nominee');?></h4>
                    <div class="deal-add-field">
                        <label><?php echo __('Nominee');?></label>
                        <?php echo $this->Form->input('nominee_name',array('label'=>false,'class'=>'form-control','placeholder'=>__('Nominee Name'),'div'=>false));?>
                    </div>
                    <div class="deal-add-field">
                        <label><?php echo __('N.CNIC');?></label>
                        <?php echo $this->Form->input('nominee_cnic',array('label'=>false,'class'=>'form-control','placeholder'=>__('35301-1657035-3'),'maxlength'=>'15','div'=>false));?>
                    </div>
                </div>

                <div class="deal-add-card">
                    <h4><?php echo __('Nominee Contact');?></h4>
                    <div class="deal-add-field">
                        <label><?php echo __('N.Mobile');?></label>
                        <?php echo $this->Form->input('nominee_mobile',array('label'=>false,'class'=>'form-control','placeholder'=>__('+923001234567'),'div'=>false));?>
                    </div>
                    <div class="deal-add-field">
                        <label><?php echo __('Relation');?></label>
                        <?php echo $this->Form->select('nominee_relation',$nomineeRelationOptions,array('empty'=>__('Please Select'),'label'=>false,'class'=>'form-control','div'=>false));?>
                    </div>
                </div>

                <div class="deal-add-card">
                    <h4><?php echo __('Price Details');?></h4>
                    <div class="deal-add-field">
                        <label><?php echo __('Area');?></label>
                        <?php echo $this->Form->input('area',array('label'=>false,'class'=>'form-control','placeholder'=>__('Area'),'div'=>false,'readonly'=>'readonly'));?>
                    </div>
                    <div class="deal-add-field">
                        <label><?php echo __('Amount');?></label>
                        <?php echo $this->Form->input('amount',array('label'=>false,'class'=>'form-control','placeholder'=>__('Amount'),'div'=>false,'readonly'=>'readonly'));?>
                    </div>
                    <div class="deal-add-field">
                        <label><?php echo __('Discount');?></label>
                        <?php echo $this->Form->input('discount',array('label'=>false,'class'=>'form-control','placeholder'=>__('Discount'),'div'=>false));?>
                    </div>
                </div>

                <div class="deal-add-card">
                    <h4><?php echo __('Plan & Agent');?></h4>
                    <div class="deal-add-field">
                        <label><?php echo __('Payment Plan');?></label>
                        <?php echo $this->Form->select('plan_id',$plan,array('id'=>'DealPlanId','label'=>false,'class'=>'form-control','empty'=>__('Please Select'),'div'=>false));?>
                        <small id="dealPlanTemplateInfo" style="color:#08785d;display:block;font-weight:700;margin-top:6px;"></small>
                    </div>
                    <div class="deal-add-field">
                        <label><?php echo __('Recurring');?></label>
                        <?php echo$this->Form->select('recurring',array('M'=>__('Monthly'),'Q'=>__('Quarterly'),'H'=>__('Half Yearly'),'Y'=>__('Yearly'),'U'=>__('Manually')),array('empty'=>null,'label'=>false,'class'=>'form-control'));?>
                    </div>
                    <div class="deal-add-field">
                        <label><?php echo __('Agent if any');?></label>
                        <?php echo $this->Form->select('user_id',isset($agent)?$agent:array(),array('id'=>'agentName','label'=>false,'class'=>'form-control','empty'=>__('Please Select'),'div'=>false));?>
                    </div>
                </div>

                <div class="deal-add-card">
                    <h4><?php echo __('Remarks');?></h4>
                    <div class="deal-add-field">
                        <label><?php echo __('Comment/Remarks');?></label>
                        <?php echo $this->Form->textarea('remarks',array('label'=>false,'class'=>'form-control','placeholder'=>__('Comment/Remarks'),'div'=>false,'rows'=>4));?>
                    </div>
                </div>

                <div class="deal-add-card deal-add-summary-card">
                    <h4><?php echo __('Live Calculation Summary');?></h4>
                    <div class="deal-add-summary"><strong id="totalAmount"></strong></div>
                </div>
            </div>

            <div class="deal-add-actions">
                <button type="submit" class="btn btn-success"><span class="fa fa-plus-circle"></span>&nbsp;<?php echo __('Save');?></button>
                <?php echo$this->Html->link('<span class="fa fa-close"></span>&nbsp;'.__('Close'),array('controller'=>'Deals','action'=>'index'),array('class'=>'btn btn-danger','escape'=>false));?>
            </div>
        </div>
        <?php echo$this->Form->end();?>
    </div>
</div>
