<?php
$templateBodyJson = json_encode((array)$templateBodyMap);
$templateTypeJson = json_encode((array)$templateTypeMap);
$currentId = 0;
if (isset($this->request->data['Realestateletter']['id']))
{
    $currentId = (int)$this->request->data['Realestateletter']['id'];
}
$letterBodyTinyOptions = array(
    'language' => $configLanguage,
    'directionality' => $dirType,
    'plugins' => 'advlist,autolink,lists,link,image,charmap,print,preview,hr,anchor,pagebreak,searchreplace,wordcount,visualblocks,visualchars,code,fullscreen,fullpage,insertdatetime,media,nonbreaking,save,table,contextmenu,directionality,emoticons,template,paste,textcolor,youtube,colorpicker',
    'entity_encoding' => 'raw',
    'verify_html' => false,
    'cleanup' => false,
    'extended_valid_elements' => 'html[lang|dir],head,body[class|style|dir],meta[charset|name|content|http-equiv],link[href|rel|type|media],style[type|media|scoped],script[type|src|id|async|defer],div[*],span[*],table[*],thead[*],tbody[*],tfoot[*],tr[*],td[*],th[*],img[*],svg[*],path[*]',
    'valid_children' => '+html[head|body],+head[meta|link|style|script|title],+body[style|script|link|meta]'
);
?>
<style type="text/css">
.wv-letter-form{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;align-items:start;}
.wv-letter-form>.form-group{background:#fff;border:1px solid #cfe0f3;border-radius:8px;box-shadow:0 8px 18px rgba(15,23,42,.04);margin:0;padding:14px;}
.wv-letter-form>.form-group>label,.wv-letter-form>.form-group>.control-label{display:block;float:none!important;text-align:left!important;width:100%!important;padding:0!important;margin:0 0 8px!important;color:#244469;text-transform:uppercase;}
.wv-letter-form>.form-group>[class*="col-sm-"]{float:none!important;width:100%!important;padding-left:0!important;padding-right:0!important;}
.wv-letter-form>.form-group .control-label{display:block;text-align:left!important;}
.wv-letter-form .form-control{width:100%;}
.wv-letter-form .input-group{width:100%;}
.wv-letter-body-full{grid-column:1 / -1!important;padding:16px!important;}
.wv-letter-body-full .mce-tinymce,.wv-letter-body-full .mce-container,.wv-letter-body-full table.mceLayout{width:100%!important;}
.wv-letter-body-full iframe{min-height:520px!important;}
.wv-charge-agreement-fields,.wv-deal-agreement-fields{grid-column:1 / -1!important;}
.wv-charge-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;}
.wv-charge-grid .wide{grid-column:span 2;}
.wv-charge-grid .full{grid-column:1 / -1;}
.wv-charge-grid label{display:block;margin-bottom:5px;color:#244469;text-transform:uppercase;font-size:11px;}
.wv-charge-grid textarea{min-height:86px;resize:vertical;}
.wv-letter-actions{grid-column:1 / -1!important;background:transparent!important;border:0!important;box-shadow:none!important;padding:0!important;margin-top:2px!important;}
.wv-letter-actions>[class*="col-sm-"]{margin-left:0!important;}
@media(max-width:1199px){.wv-letter-form{grid-template-columns:repeat(2,minmax(0,1fr));}.wv-charge-grid{grid-template-columns:repeat(2,minmax(0,1fr));}}
@media(max-width:767px){.wv-letter-form{display:block;}.wv-letter-form>.form-group{margin-bottom:12px;}.wv-charge-grid{grid-template-columns:1fr;}.wv-charge-grid .wide,.wv-charge-grid .full{grid-column:1;}}
</style>
<script type="text/javascript">
var letterTemplateBodies = <?php echo $templateBodyJson ? $templateBodyJson : '{}';?>;
var letterTemplateTypes = <?php echo $templateTypeJson ? $templateTypeJson : '{}';?>;

function refreshLetterTemplatesByType() {
    var selectedType = jQuery('#RealestateletterLetterType').val();
    jQuery('#RealestateletterTemplateId option').each(function(){
        var optionValue = jQuery(this).attr('value');
        if(!optionValue){
            jQuery(this).show();
            return;
        }
        var typeCode = letterTemplateTypes[optionValue] || '';
        if(selectedType==='' || selectedType===typeCode){
            jQuery(this).show();
        }else{
            jQuery(this).hide();
        }
    });
}

function loadSelectedTemplateBody() {
    var templateId = jQuery('#RealestateletterTemplateId').val();
    if(!templateId){ return; }
    if(!letterTemplateBodies[templateId]){ return; }
    if(typeof(tinymce)!=='undefined' && tinymce.get('RealestateletterDescription')){
        tinymce.get('RealestateletterDescription').setContent(letterTemplateBodies[templateId]);
    }else{
        jQuery('#RealestateletterDescription').val(letterTemplateBodies[templateId]);
    }
}

function refreshChargeAgreementFields() {
    var selectedType = jQuery('#RealestateletterLetterType').val();
    if(selectedType === 'RLT_CHARGE' || selectedType === 'RLT_DEAL_AGREEMENT'){
        jQuery('.wv-charge-agreement-fields').show();
    }else{
        jQuery('.wv-charge-agreement-fields').hide();
    }
    if(selectedType === 'RLT_DEAL_AGREEMENT'){
        jQuery('.wv-deal-agreement-fields').show();
    }else{
        jQuery('.wv-deal-agreement-fields').hide();
    }
}

jQuery(document).ready(function(){
    refreshLetterTemplatesByType();
    refreshChargeAgreementFields();
    jQuery('#RealestateletterLetterType').change(function(){refreshLetterTemplatesByType();refreshChargeAgreementFields();});
    if(jQuery('#letterDate').length){
        jQuery('#letterDate').datetimepicker({locale:'<?php echo $configLanguage;?>',format:'<?php echo $dpFormat;?>'});
    }
});
</script>

<div class="page-title">
    <div class="title-env">
        <h1 class="title"><?php echo __('Edit Real Estate Letter');?></h1>
    </div>
</div>
<div class="panel">
    <div class="panel-body">
        <?php echo $this->Session->flash();?>
        <?php echo $this->Form->create('Realestateletter', array('controller'=>'Realestateletters','action'=>'edit',$currentId,'name'=>'post_req','id'=>'post_req','class'=>'form-horizontal wv-letter-form'));?>
        <?php echo $this->Form->hidden('project_id', array('value' => isset($lettersProjectId) ? (int)$lettersProjectId : 0));?>
        <div class="form-group">
            <label class="col-sm-2 control-label"><small><?php echo __('Deal').':';?></small></label>
            <div class="col-sm-10">
                <?php echo $this->Form->select('deal_id',$dealOptions,array('empty'=>__('Please Select'),'label'=>false,'class'=>'form-control','div'=>false));?>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label"><small><?php echo __('Letter Type').':';?></small></label>
            <div class="col-sm-4">
                <?php echo $this->Form->select('letter_type',$letterTypeArr,array('empty'=>__('Please Select'),'label'=>false,'class'=>'form-control','div'=>false));?>
            </div>
            <label class="col-sm-2 control-label"><small><?php echo __('Template').':';?></small></label>
            <div class="col-sm-4">
                <?php echo $this->Form->select('template_id',$templateOptions,array('empty'=>__('Please Select'),'label'=>false,'class'=>'form-control','div'=>false));?>
                <div style="margin-top:5px;">
                    <button type="button" class="btn btn-default btn-xs" onclick="loadSelectedTemplateBody();"><span class="fa fa-download"></span> <?php echo __('Load Template');?></button>
                </div>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label"><small><?php echo __('Reference No').':';?></small></label>
            <div class="col-sm-4">
                <?php echo $this->Form->input('reference_no',array('label'=>false,'class'=>'form-control','placeholder'=>__('Reference No'),'div'=>false));?>
            </div>
            <label class="col-sm-2 control-label"><small><?php echo __('Letter Date').':';?></small></label>
            <div class="col-sm-4">
                <div class="input-group date" id="letterDate">
                    <?php echo $this->Form->input('letter_date',array('type'=>'text','label'=>false,'class'=>'form-control','placeholder'=>__('Letter Date'),'div'=>false));?>
                    <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
                </div>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label"><small><?php echo __('Title').':';?></small></label>
            <div class="col-sm-7">
                <?php echo $this->Form->input('title',array('label'=>false,'class'=>'form-control','placeholder'=>__('Title'),'div'=>false));?>
            </div>
            <label class="col-sm-1 control-label"><small><?php echo __('Status').':';?></small></label>
            <div class="col-sm-2">
                <?php echo $this->Form->select('status',$letterStatusArr,array('empty'=>false,'label'=>false,'class'=>'form-control','div'=>false));?>
            </div>
        </div>
        <div class="form-group wv-charge-agreement-fields">
            <label class="control-label"><small><?php echo __('Agreement Details').':';?></small></label>
            <div class="wv-charge-grid">
                <div class="full">
                    <label><?php echo __('Extra Work Details');?></label>
                    <?php echo $this->Form->textarea('charge_work_details',array('label'=>false,'class'=>'form-control','placeholder'=>__('Extra work / charges details'),'div'=>false));?>
                </div>
                <div>
                    <label><?php echo __('Work Status');?></label>
                    <?php echo $this->Form->select('charge_work_status',$chargeWorkStatusArr,array('empty'=>false,'label'=>false,'class'=>'form-control','div'=>false));?>
                </div>
                <div>
                    <label><?php echo __('Payment Method');?></label>
                    <?php echo $this->Form->input('charge_payment_method',array('label'=>false,'class'=>'form-control','placeholder'=>__('Payment method'),'div'=>false));?>
                </div>
                <div>
                    <label><?php echo __('Payment Schedule / Deadline');?></label>
                    <?php echo $this->Form->input('charge_payment_deadline',array('label'=>false,'class'=>'form-control','placeholder'=>__('Payment schedule / deadline'),'div'=>false));?>
                </div>
                <div>
                    <label><?php echo __('Agreement Place');?></label>
                    <?php echo $this->Form->input('agreement_place',array('label'=>false,'class'=>'form-control','placeholder'=>__('Agreement place'),'div'=>false));?>
                </div>
                <div class="wide">
                    <label><?php echo __('Agency Representative Name');?></label>
                    <?php echo $this->Form->input('agency_representative_name',array('label'=>false,'class'=>'form-control','placeholder'=>__('Representative name'),'div'=>false));?>
                </div>
                <div>
                    <label><?php echo __('Representative Designation');?></label>
                    <?php echo $this->Form->input('agency_representative_designation',array('label'=>false,'class'=>'form-control','placeholder'=>__('Designation'),'div'=>false));?>
                </div>
                <div>
                    <label><?php echo __('Witness 1 Name');?></label>
                    <?php echo $this->Form->input('witness_one_name',array('label'=>false,'class'=>'form-control','placeholder'=>__('Witness 1 name'),'div'=>false));?>
                </div>
                <div>
                    <label><?php echo __('Witness 1 CNIC');?></label>
                    <?php echo $this->Form->input('witness_one_cnic',array('label'=>false,'class'=>'form-control','placeholder'=>__('Witness 1 CNIC'),'div'=>false));?>
                </div>
                <div>
                    <label><?php echo __('Witness 1 Contact');?></label>
                    <?php echo $this->Form->input('witness_one_contact',array('label'=>false,'class'=>'form-control','placeholder'=>__('Witness 1 contact'),'div'=>false));?>
                </div>
                <div>
                    <label><?php echo __('Witness 1 Address');?></label>
                    <?php echo $this->Form->input('witness_one_address',array('label'=>false,'class'=>'form-control','placeholder'=>__('Witness 1 address'),'div'=>false));?>
                </div>
                <div>
                    <label><?php echo __('Witness 2 Name');?></label>
                    <?php echo $this->Form->input('witness_two_name',array('label'=>false,'class'=>'form-control','placeholder'=>__('Witness 2 name'),'div'=>false));?>
                </div>
                <div>
                    <label><?php echo __('Witness 2 CNIC');?></label>
                    <?php echo $this->Form->input('witness_two_cnic',array('label'=>false,'class'=>'form-control','placeholder'=>__('Witness 2 CNIC'),'div'=>false));?>
                </div>
                <div>
                    <label><?php echo __('Witness 2 Contact');?></label>
                    <?php echo $this->Form->input('witness_two_contact',array('label'=>false,'class'=>'form-control','placeholder'=>__('Witness 2 contact'),'div'=>false));?>
                </div>
                <div>
                    <label><?php echo __('Witness 2 Address');?></label>
                    <?php echo $this->Form->input('witness_two_address',array('label'=>false,'class'=>'form-control','placeholder'=>__('Witness 2 address'),'div'=>false));?>
                </div>
            </div>
        </div>
        <div class="form-group wv-deal-agreement-fields">
            <label class="control-label"><small><?php echo __('Deal Agreement Ashtam Details').':';?></small></label>
            <div class="wv-charge-grid">
                <div>
                    <label><?php echo __('Representative Father Name');?></label>
                    <?php echo $this->Form->input('agency_representative_father',array('label'=>false,'class'=>'form-control','placeholder'=>__('Representative father name'),'div'=>false));?>
                </div>
                <div>
                    <label><?php echo __('Representative CNIC');?></label>
                    <?php echo $this->Form->input('agency_representative_cnic',array('label'=>false,'class'=>'form-control','placeholder'=>__('Representative CNIC'),'div'=>false));?>
                </div>
                <div class="wide">
                    <label><?php echo __('Payment Plan');?></label>
                    <?php echo $this->Form->input('agreement_payment_plan',array('label'=>false,'class'=>'form-control','placeholder'=>__('Payment plan'),'div'=>false));?>
                </div>
                <div>
                    <label><?php echo __('Penalty Rate');?></label>
                    <?php echo $this->Form->input('agreement_penalty_rate',array('label'=>false,'class'=>'form-control','placeholder'=>__('Penalty rate'),'div'=>false));?>
                </div>
                <div>
                    <label><?php echo __('Possession / Completion Time');?></label>
                    <?php echo $this->Form->input('agreement_possession_months',array('label'=>false,'class'=>'form-control','placeholder'=>__('36 months'),'div'=>false));?>
                </div>
                <div>
                    <label><?php echo __('Extra Work Amount');?></label>
                    <?php echo $this->Form->input('agreement_extra_work_amount',array('label'=>false,'class'=>'form-control','placeholder'=>__('Extra work amount'),'div'=>false));?>
                </div>
                <div class="wide">
                    <label><?php echo __('Extra Work Schedule');?></label>
                    <?php echo $this->Form->input('agreement_extra_work_schedule',array('label'=>false,'class'=>'form-control','placeholder'=>__('Extra work schedule'),'div'=>false));?>
                </div>
                <div>
                    <label><?php echo __('Cancellation Deduction');?></label>
                    <?php echo $this->Form->input('agreement_cancellation_deduction_rate',array('label'=>false,'class'=>'form-control','placeholder'=>__('20%'),'div'=>false));?>
                </div>
                <div>
                    <label><?php echo __('Refund Period');?></label>
                    <?php echo $this->Form->input('agreement_refund_period',array('label'=>false,'class'=>'form-control','placeholder'=>__('Refund period'),'div'=>false));?>
                </div>
                <div>
                    <label><?php echo __('Court Jurisdiction');?></label>
                    <?php echo $this->Form->input('agreement_court_jurisdiction',array('label'=>false,'class'=>'form-control','placeholder'=>__('Court jurisdiction'),'div'=>false));?>
                </div>
                <div>
                    <label><?php echo __('Block / Sector');?></label>
                    <?php echo $this->Form->input('agreement_property_block',array('label'=>false,'class'=>'form-control','placeholder'=>__('Block / sector'),'div'=>false));?>
                </div>
                <div>
                    <label><?php echo __('Unit / File No');?></label>
                    <?php echo $this->Form->input('agreement_property_unit_no',array('label'=>false,'class'=>'form-control','placeholder'=>__('Unit / file no'),'div'=>false));?>
                </div>
                <div>
                    <label><?php echo __('Floor');?></label>
                    <?php echo $this->Form->input('agreement_property_floor',array('label'=>false,'class'=>'form-control','placeholder'=>__('Floor'),'div'=>false));?>
                </div>
                <div>
                    <label><?php echo __('Covered Area');?></label>
                    <?php echo $this->Form->input('agreement_property_covered_area',array('label'=>false,'class'=>'form-control','placeholder'=>__('Covered area'),'div'=>false));?>
                </div>
                <div class="wide">
                    <label><?php echo __('Location / Street');?></label>
                    <?php echo $this->Form->input('agreement_property_location',array('label'=>false,'class'=>'form-control','placeholder'=>__('Location / street'),'div'=>false));?>
                </div>
            </div>
        </div>
        <div class="form-group wv-letter-body-full">
            <div class="col-sm-12">
                <label class="control-label" style="display:block;text-align:left;margin-bottom:6px;"><small><?php echo __('Letter Body').':';?></small></label>
                <?php echo $this->Tinymce->input('description',array('label'=>false,'class'=>'form-control','placeholder'=>__('Letter Body'),'div'=>false),$letterBodyTinyOptions,'absolute');?>
                <?php echo $this->element('letter_placeholders_guide');?>
            </div>
        </div>
        <div class="form-group text-left wv-letter-actions">
            <div class="col-sm-offset-2 col-sm-10">
                <button type="submit" class="btn btn-success"><span class="fa fa-refresh"></span>&nbsp;<?php echo __('Update');?></button>
                <?php
                $closeUrl = array('controller' => 'Realestateletters', 'action' => 'index');
                if (!empty($lettersProjectId)) {
                    $closeUrl['?'] = array('project_id' => (int)$lettersProjectId);
                }
                echo $this->Html->link('<span class="fa fa-close"></span>&nbsp;'.__('Close'), $closeUrl, array('escape'=>false,'class'=>'btn btn-danger'));
                ?>
            </div>
        </div>
        <?php echo $this->Form->end();?>
    </div>
</div>
