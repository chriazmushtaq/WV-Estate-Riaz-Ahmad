<?php
echo $this->Html->css('select2/select2');
echo $this->Html->css('select2/select2-bootstrap');
echo $this->fetch('css');
echo $this->Html->script('select2.min');
echo $this->fetch('script');
$clientUrl=$this->Html->url(array('controller'=>'Leads','action'=>'clientsearch'));
$propertyUrl=$this->Html->url(array('controller'=>'Leads','action'=>'propertysearch'));
$agentAjaxUrl = $this->Html->url(array('controller'=>'Ajaxcontents','action'=>'agent'));
$existingClientId = !empty($leadRow['Lead']['client_id']) ? (int)$leadRow['Lead']['client_id'] : 0;
$existingClientName = !empty($leadRow['Client']['name']) ? $leadRow['Client']['name'] : '';
$leadDatePickerFormat = (isset($dpFormat) && trim((string)$dpFormat) !== '') ? trim((string)$dpFormat) : 'DD-MMM-YYYY';
$followUpDisplayFormat = trim((isset($dtFormat) ? $dtFormat : 'd-M-Y').' h:i A');
$followUpFieldValue = isset($this->request->data['Lead']['follow_up']) ? trim((string)$this->request->data['Lead']['follow_up']) : '';
if($followUpFieldValue !== '')
{
    $followUpTimestamp = strtotime($followUpFieldValue);
    if($followUpTimestamp !== false)
    {
        $followUpFieldValue = $this->Time->format($followUpDisplayFormat, $followUpFieldValue);
    }
}
?>
<script type="text/javascript">
jQuery(document).ready(function(){
    if(jQuery('#leadFollowUpDate').length){
        jQuery('#leadFollowUpDate').datetimepicker({locale:'<?php echo$configLanguage;?>',format:'<?php echo $leadDatePickerFormat;?> hh:mm A',useCurrent:true});
    }

    jQuery('#leadPropertyId').select2({
        minimumInputLength: 1,
        ajax: {
            url: '<?php echo $propertyUrl;?>',
            dataType: 'json',
            data: function (term) {
                return {
                    q: term,
                    q1: jQuery('#leadTypeId').val(),
                    q2: jQuery('#leadProjectId').val()
                };
            },
            results: function (data) { return { results: data }; }
        }
    });

    jQuery('#leadClientId').select2({
        minimumInputLength: 1,
        ajax: {
            url: '<?php echo $clientUrl;?>',
            dataType: 'json',
            data: function (term) { return { q: term }; },
            results: function (data) { return { results: data }; }
        }
    });

    <?php if($existingClientId>0 && strlen(trim($existingClientName))>0){ ?>
    var existingOption = new Option('<?php echo addslashes($existingClientName);?>','<?php echo (int)$existingClientId;?>', true, true);
    jQuery('#leadClientId').append(existingOption).trigger('change');
    <?php } ?>

    jQuery('#leadProjectId').on('change', function(){
        var selectedValue = jQuery(this).val();
        if(!selectedValue){
            jQuery('#leadAgentId').html('<option value=""><?php echo addslashes(__('Please Select'));?></option>');
            return;
        }
        jQuery.ajax({
            type: 'get',
            url: '<?php echo $agentAjaxUrl;?>?id=' + selectedValue,
            success: function(response){
                if(response){
                    jQuery('#leadAgentId').html(response);
                }
            }
        });
    });
});
</script>

<style>
.wv-lead-edit-page{background:#f3f6fa;margin:-10px -10px 0;padding:18px;}
.wv-lead-edit-hero{align-items:center;background:linear-gradient(135deg,#07152a 0%,#102746 72%,#ff0a46 165%);border-radius:12px;box-shadow:0 18px 36px rgba(15,23,42,.16);color:#fff;display:flex;gap:18px;justify-content:space-between;margin-bottom:16px;overflow:hidden;padding:22px;position:relative;}
.wv-lead-edit-hero:after{background:radial-gradient(circle at 82% 0%,rgba(124,249,255,.22),rgba(124,249,255,0) 36%);content:"";inset:0;position:absolute;}
.wv-lead-edit-hero>*{position:relative;z-index:2;}
.wv-lead-edit-title{align-items:center;display:flex;gap:14px;}
.wv-lead-edit-icon{align-items:center;background:rgba(255,255,255,.13);border:1px solid rgba(255,255,255,.22);border-radius:12px;color:#7cf9ff;display:flex;font-size:24px;height:54px;justify-content:center;width:54px;}
.wv-lead-edit-title h1{color:#fff;font-size:28px;font-weight:900;line-height:1.15;margin:0 0 5px;}
.wv-lead-edit-title p{color:#d8e3f2;font-size:13px;font-weight:700;line-height:1.45;margin:0;}
.wv-lead-edit-id{background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.20);border-radius:999px;color:#fff;font-size:12px;font-weight:900;padding:9px 13px;white-space:nowrap;}
.wv-lead-edit-card{background:#fff;border:1px solid #dde7f3;border-radius:12px;box-shadow:0 12px 28px rgba(15,23,42,.08);overflow:hidden;}
.wv-lead-edit-toolbar{align-items:center;border-bottom:1px solid #edf2f7;display:flex;gap:12px;justify-content:space-between;padding:16px 18px;}
.wv-lead-edit-toolbar strong{color:#07152a;display:block;font-size:16px;font-weight:900;}
.wv-lead-edit-toolbar span{color:#64748b;display:block;font-size:12px;font-weight:700;margin-top:3px;}
.wv-lead-edit-actions{display:flex;flex-wrap:wrap;gap:8px;}
.wv-lead-edit-actions .btn{border:0;border-radius:8px;font-size:12px;font-weight:900;padding:9px 12px;}
.wv-lead-edit-actions .btn-default{background:#eff6ff!important;color:#0f2742!important;}
.wv-lead-edit-actions .btn-primary{background:#07152a!important;color:#fff!important;}
.wv-lead-edit-actions .btn-success{background:#047857!important;color:#fff!important;}
.wv-lead-edit-body{padding:18px;}
.wv-lead-edit-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px;align-items:start;}
.wv-lead-field{min-width:0;}
.wv-lead-field label{color:#334155;display:block;font-size:11px;font-weight:900;letter-spacing:.04em;line-height:1.25;margin:0 0 7px;text-align:left;text-transform:uppercase;}
.wv-lead-field .form-control,.wv-lead-field select,.wv-lead-field textarea{background:#fbfdff;border:1px solid #cbd8e8;border-radius:8px;box-shadow:none;color:#0f172a;font-weight:700;height:42px;max-width:none!important;min-width:0;width:100%!important;}
.wv-lead-field textarea.form-control{height:auto;min-height:112px;resize:vertical;}
.wv-lead-field .form-control:focus,.wv-lead-field select:focus,.wv-lead-field textarea:focus{background:#fff;border-color:#ff0a46;box-shadow:0 0 0 3px rgba(255,10,70,.10);}
.wv-lead-span-2{grid-column:span 2;}
.wv-lead-span-3{grid-column:span 3;}
.wv-lead-field .select2-container{width:100%!important;}
.wv-lead-field .select2-container .select2-choice{background:#fbfdff;border:1px solid #cbd8e8;border-radius:8px;color:#0f172a;font-weight:700;height:42px;line-height:40px;}
.wv-lead-field .input-group{width:100%;}
.wv-lead-field .input-group .form-control{border-bottom-right-radius:0;border-top-right-radius:0;}
.wv-lead-field .input-group-addon{background:#fff;border-color:#cbd8e8;border-bottom-right-radius:8px;border-top-right-radius:8px;color:#ff0a46;}
.wv-lead-check{align-items:center;background:#f8fafc;border:1px solid #e5edf7;border-radius:10px;color:#334155;display:flex;font-size:13px;font-weight:800;gap:9px;margin:4px 0 0;padding:12px 13px;}
.wv-lead-check input{margin:0;}
.wv-lead-edit-footer{background:#f8fafc;border-top:1px solid #edf2f7;display:flex;flex-wrap:wrap;gap:8px;margin:18px -18px -18px;padding:16px 18px;}
.wv-lead-edit-footer .btn{border:0;border-radius:8px;font-size:12px;font-weight:900;padding:10px 15px;}
.wv-lead-edit-footer .btn-success{background:#047857!important;color:#fff!important;}
.wv-lead-edit-footer .btn-danger{background:#07152a!important;color:#fff!important;}
@media(max-width:991px){.wv-lead-edit-page{margin:0;padding:12px}.wv-lead-edit-hero,.wv-lead-edit-toolbar{display:block}.wv-lead-edit-id,.wv-lead-edit-actions{margin-top:12px}.wv-lead-edit-grid{grid-template-columns:repeat(2,minmax(0,1fr));}.wv-lead-span-2,.wv-lead-span-3{grid-column:span 2;}}
@media(max-width:640px){.wv-lead-edit-grid{grid-template-columns:1fr}.wv-lead-span-2,.wv-lead-span-3{grid-column:span 1}.wv-lead-edit-title h1{font-size:23px}}
</style>

<div class="wv-lead-edit-page">
    <?php echo $this->Session->flash();?>
    <div class="wv-lead-edit-hero">
        <div class="wv-lead-edit-title">
            <div class="wv-lead-edit-icon"><i class="fa fa-pencil"></i></div>
            <div>
                <h1><?php echo __('Edit Lead Contact');?></h1>
                <p><?php echo __('Customer inquiry and follow-up record.');?></p>
            </div>
        </div>
        <div class="wv-lead-edit-id"><?php echo __('Lead');?> #<?php echo (int)$leadRow['Lead']['id'];?></div>
    </div>

    <div class="wv-lead-edit-card">
        <div class="wv-lead-edit-toolbar">
            <div>
                <strong><?php echo __('Lead Information');?></strong>
                <span><?php echo __('Lead');?> #<?php echo (int)$leadRow['Lead']['id'];?></span>
            </div>
            <div class="wv-lead-edit-actions">
                <?php echo $this->Html->link('<span class="fa fa-dashboard"></span>&nbsp;'.__('CRM Dashboard'),array('controller'=>'Leads','action'=>'dashboard'),array('escape'=>false,'class'=>'btn btn-default'));?>
                <?php echo $this->Html->link('<span class="fa fa-eye"></span>&nbsp;'.__('View Lead'),array('controller'=>'Leads','action'=>'leadview',$leadRow['Lead']['id']),array('escape'=>false,'class'=>'btn btn-primary'));?>
                <?php echo $this->Html->link('<span class="fa fa-user-plus"></span>&nbsp;'.__('Convert To Contact'),array('controller'=>'Leads','action'=>'convertcontact',$leadRow['Lead']['id']),array('escape'=>false,'class'=>'btn btn-success','onclick'=>'return confirm("Convert this lead to customer contact?");'));?>
            </div>
        </div>

        <?php echo $this->Form->create('Lead', array('controller' => 'Leads', 'action' => 'leadedit/'.$leadRow['Lead']['id'], 'class' => 'form-horizontal', 'novalidate' => 'novalidate'));?>
        <div class="wv-lead-edit-body">
            <div class="wv-lead-edit-grid">
                <?php if($userType!='AGT'){ ?>
                    <?php if($userType!='AGY'){ ?>
                        <div class="wv-lead-field">
                            <label><?php echo __('Agency');?></label>
                            <?php echo $this->Form->select('project_id',$projectOptions,array('id'=>'leadProjectId','label'=>false,'class'=>'form-control','empty'=>__('Please Select'),'div'=>false));?>
                        </div>
                    <?php } else { echo $this->Form->hidden('project_id',array('value'=>$agencyId,'id'=>'leadProjectId')); } ?>
                    <div class="wv-lead-field">
                        <label><?php echo __('Agent');?></label>
                        <?php echo $this->Form->select('user_id',$agentOptions,array('id'=>'leadAgentId','label'=>false,'class'=>'form-control','empty'=>__('Please Select'),'div'=>false));?>
                    </div>
                <?php } else {
                    echo $this->Form->hidden('project_id',array('value'=>$agencyId,'id'=>'leadProjectId'));
                    echo $this->Form->hidden('user_id',array('value'=>$luserId,'id'=>'leadAgentId'));
                } ?>

                <div class="wv-lead-field wv-lead-span-3">
                    <label><?php echo __('Existing Contact (Optional)');?></label>
                    <?php echo $this->Form->input('client_id',array('type'=>'text','id'=>'leadClientId','label'=>false,'placeholder'=>__('Search existing contact name'),'class'=>'form-control','div'=>false,'required'=>false));?>
                </div>

                <div class="wv-lead-field">
                    <label><?php echo __('Lead Name');?></label>
                    <?php echo $this->Form->input('lead_name',array('label'=>false,'class'=>'form-control','placeholder'=>__('Lead Name'),'div'=>false));?>
                </div>

                <div class="wv-lead-field">
                    <label><?php echo __('Mobile');?></label>
                    <?php echo $this->Form->input('lead_mobile',array('label'=>false,'class'=>'form-control','placeholder'=>__('923001234567'),'div'=>false));?>
                </div>

                <div class="wv-lead-field">
                    <label><?php echo __('Email');?></label>
                    <?php echo $this->Form->input('lead_email',array('label'=>false,'class'=>'form-control','placeholder'=>__('Email'),'div'=>false));?>
                </div>

                <div class="wv-lead-field">
                    <label><?php echo __('Lead Source');?></label>
                    <?php echo $this->Form->input('lead_source',array('label'=>false,'class'=>'form-control','placeholder'=>__('Facebook / Reference / Campaign'),'div'=>false));?>
                </div>

                <div class="wv-lead-field">
                    <label><?php echo __('City');?></label>
                    <?php echo $this->Form->input('lead_city',array('label'=>false,'class'=>'form-control','placeholder'=>__('City'),'div'=>false));?>
                </div>

                <div class="wv-lead-field">
                    <label><?php echo __('Budget');?></label>
                    <?php echo $this->Form->input('lead_budget',array('label'=>false,'class'=>'form-control','placeholder'=>__('Budget'),'div'=>false));?>
                </div>

                <div class="wv-lead-field">
                    <label><?php echo __('Language');?></label>
                    <?php echo $this->Form->input('lead_language',array('label'=>false,'class'=>'form-control','placeholder'=>__('Language'),'div'=>false));?>
                </div>

                <div class="wv-lead-field wv-lead-span-3">
                    <label><?php echo __('Address');?></label>
                    <?php echo $this->Form->input('lead_address',array('label'=>false,'class'=>'form-control','placeholder'=>__('Address'),'div'=>false));?>
                </div>

                <div class="wv-lead-field">
                    <label><?php echo __('Lead For');?></label>
                    <?php echo $this->Form->select('type_id',$typeOptions,array('id'=>'leadTypeId','label'=>false,'class'=>'form-control','empty'=>__('All'),'div'=>false));?>
                </div>

                <div class="wv-lead-field wv-lead-span-2">
                    <label><?php echo __('Property');?></label>
                    <?php echo $this->Form->input('property_id',array('type'=>'text','id'=>'leadPropertyId','label'=>false,'class'=>'form-control','placeholder'=>__('Search Property Name'),'div'=>false));?>
                </div>

                <div class="wv-lead-field">
                    <label><?php echo __('Follow Up');?></label>
                    <div class="input-group date" id="leadFollowUpDate">
                        <?php echo $this->Form->input('follow_up',array('type'=>'text','label'=>false,'class'=>'form-control','div'=>false,'value'=>$followUpFieldValue,'placeholder'=>__('Select date & time')));?>
                        <span class="input-group-addon"><span class="fa fa-calendar"></span></span>
                    </div>
                </div>

                <div class="wv-lead-field">
                    <label><?php echo __('Status');?></label>
                    <?php echo $this->Form->select('status_id',$statusOptions,array('label'=>false,'class'=>'form-control','empty'=>__('Please Select'),'div'=>false));?>
                </div>

                <div class="wv-lead-field wv-lead-span-3">
                    <label><?php echo __('Remarks');?></label>
                    <?php echo $this->Form->textarea('remarks',array('label'=>false,'class'=>'form-control','placeholder'=>__('Remarks / Follow-up notes'),'div'=>false,'rows'=>4));?>
                </div>

                <div class="wv-lead-field wv-lead-span-3">
                    <label class="wv-lead-check">
                        <?php echo $this->Form->checkbox('create_contact',array('value'=>1,'hiddenField'=>false));?>
                        <span><?php echo __('Create/Link as Customer Contact now');?></span>
                    </label>
                </div>
            </div>

            <div class="wv-lead-edit-footer">
                <button type="submit" class="btn btn-success"><span class="fa fa-save"></span> <?php echo __('Update');?></button>
                <?php echo $this->Html->link('<span class="fa fa-close"></span> '.__('Close'),array('controller'=>'Leads','action'=>'leadview',$leadRow['Lead']['id']),array('class'=>'btn btn-danger','escape'=>false));?>
            </div>
        </div>
        <?php echo $this->Form->end();?>
    </div>
</div>
