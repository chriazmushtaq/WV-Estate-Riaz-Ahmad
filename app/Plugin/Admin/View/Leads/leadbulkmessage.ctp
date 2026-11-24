<?php
$modeKey = strtolower(trim((string)$mode));
$isEmailMode = ($modeKey === 'email');
$pageTitle = $isEmailMode ? __('Send Lead Emails') : __('Send Lead SMS');
$manageUrl = $this->Html->url(array('controller' => 'Leads', 'action' => 'manage'), true);
$scheduleSmsUrl = $this->Html->url(array('controller' => 'Leads', 'action' => 'schedulesms', implode(',', (array)$selectedLeadIds)), true);
$templateManagerUrl = array('controller' => $isEmailMode ? 'Emailtemplates' : 'Smstemplates', 'action' => 'index');
$templateManagerLabel = $isEmailMode ? __('My Email Templates') : __('My SMS Templates');
$gatewaySourceMode = isset($gatewaySourceMode) ? (string)$gatewaySourceMode : 'global';
$gatewaySourceLabel = isset($gatewaySourceLabel) ? (string)$gatewaySourceLabel : ($isEmailMode ? __('Global Email Settings') : __('Global SMS Settings'));
$gatewayMetaLabel = isset($gatewayMetaLabel) ? (string)$gatewayMetaLabel : '';
$gatewayConfigured = !isset($gatewayConfigured) ? true : (bool)$gatewayConfigured;
$isSuperAdmin = !empty($isSuperAdmin);
$smsGatewayMode = isset($smsGatewayMode) ? (string)$smsGatewayMode : 'default';
$hasTemplateOptions = !empty($templateOptions);

if(!isset($this->request->data['Lead']['template_id']) || (int)$this->request->data['Lead']['template_id']<=0)
{
    $this->request->data['Lead']['template_id'] = (int)$selectedTemplateId;
}
if($isEmailMode && !isset($this->request->data['Lead']['subject']))
{
    $this->request->data['Lead']['subject'] = (string)$defaultSubject;
}
if(!isset($this->request->data['Lead']['message']) || trim((string)$this->request->data['Lead']['message'])==='')
{
    $this->request->data['Lead']['message'] = (string)$defaultMessage;
}

$templateMapJson = '{}';
if(!empty($templateMap))
{
    $templateMapJson = json_encode($templateMap);
}
?>
<script type="text/javascript">
jQuery(document).ready(function(){
    var templateMap = <?php echo $templateMapJson ? $templateMapJson : '{}';?>;
    var selectedTemplateId = '<?php echo (int)$selectedTemplateId;?>';
    var isEmailMode = <?php echo $isEmailMode ? 'true' : 'false';?>;
    function applyTemplate(forceApply){
        var templateId = parseInt(jQuery('#leadTemplateId').val() || '0', 10);
        if(!templateMap[templateId]){
            return;
        }
        var tpl = templateMap[templateId];
        var messageField = jQuery('#LeadMessage');
        if(forceApply || jQuery.trim(messageField.val())==='' || messageField.data('autofill')===1){
            messageField.val(tpl.description || '');
            messageField.data('autofill',1);
            if(typeof tinymce !== 'undefined' && tinymce.get('LeadMessage')){
                tinymce.get('LeadMessage').setContent(tpl.description || '');
            }
        }
        if(isEmailMode){
            var subjectField = jQuery('#LeadSubject');
            if(forceApply || jQuery.trim(subjectField.val())==='' || subjectField.data('autofill')===1){
                subjectField.val(tpl.name || '');
                subjectField.data('autofill',1);
            }
        }
    }

    jQuery('#leadTemplateId').on('change', function(){ applyTemplate(true); });
    jQuery('#LeadMessage').on('input', function(){ jQuery(this).data('autofill',0); });
    jQuery('#LeadSubject').on('input', function(){ jQuery(this).data('autofill',0); });
    if(selectedTemplateId !== '0')
    {
        jQuery('#leadTemplateId').val(selectedTemplateId);
    }
    applyTemplate(false);
});
</script>

<div class="page-title">
    <div class="title-env">
        <h1 class="title"><?php echo h($pageTitle);?></h1>
    </div>
</div>

<div class="panel">
    <div class="panel-heading">
        <div class="btn-group">
            <a href="<?php echo h($manageUrl);?>" class="btn btn-default"><span class="fa fa-list"></span>&nbsp;<?php echo __('Manage Leads');?></a>
            <?php if(!$isEmailMode){ ?>
                <a href="<?php echo h($scheduleSmsUrl);?>" class="btn btn-success"><span class="fa fa-clock-o"></span>&nbsp;<?php echo __('Schedule SMS');?></a>
            <?php } ?>
            <?php echo $this->Html->link('<span class="fa fa-dashboard"></span>&nbsp;'.__('CRM Dashboard'),array('controller'=>'Leads','action'=>'dashboard'),array('escape'=>false,'class'=>'btn btn-primary'));?>
            <?php echo $this->Html->link('<span class="fa fa-files-o"></span>&nbsp;'.h($templateManagerLabel),$templateManagerUrl,array('escape'=>false,'class'=>'btn btn-info'));?>
        </div>
    </div>
    <div class="panel-body">
        <?php echo $this->Session->flash();?>
        <div class="alert alert-info">
            <?php echo __('Selected Leads');?>: <strong><?php echo count((array)$leadRows);?></strong>
            <div style="margin-top:6px;"><?php echo __('Custom templates are isolated by the logged-in user and are used here automatically from your own template list.');?></div>
        </div>
        <div class="alert <?php echo $gatewayConfigured ? 'alert-success' : 'alert-warning';?>">
            <strong><?php echo __('Send Using');?></strong>:
            <?php echo h($gatewaySourceLabel);?>
            <?php if(trim($gatewayMetaLabel) !== ''){ ?>
                <span style="margin-left:8px;"><?php echo h($gatewayMetaLabel);?></span>
            <?php } ?>
            <span style="margin-left:8px;" class="label label-<?php echo $gatewaySourceMode === 'user' ? 'primary' : 'default';?>">
                <?php echo $gatewaySourceMode === 'user' ? __('CRM Selected') : __('Global Selected');?>
            </span>
            <?php if(!$gatewayConfigured){ ?>
                <div style="margin-top:6px;"><?php echo __('This selected gateway looks incomplete. Please check CRM Settings before sending.');?></div>
            <?php } ?>
        </div>

        <?php echo $this->Form->create('Lead', array('url' => array('controller'=>'Leads','action'=>'leadbulkmessage',$modeKey), 'class' => 'form-horizontal'));?>
        <?php foreach((array)$selectedLeadIds as $k => $leadId){ echo $this->Form->hidden('id.'.$k, array('value' => (int)$leadId)); } ?>

        <?php if(!$isEmailMode && $isSuperAdmin){ ?>
        <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo __('Send Gateway');?></label>
            <div class="col-sm-10">
                <?php
                echo $this->Form->select(
                    'sms_gateway_mode',
                    array(
                        'assigned_user' => __('Assigned User Gateway (Per Lead)'),
                        'global' => __('Force Global SMS Gateway')
                    ),
                    array(
                        'id' => 'LeadSmsGatewayMode',
                        'label' => false,
                        'class' => 'form-control',
                        'div' => false,
                        'empty' => false,
                        'selected' => ($smsGatewayMode === 'global' ? 'global' : 'assigned_user')
                    )
                );
                ?>
                <p class="help-block"><?php echo __('Super admin only: choose to send with each lead assigned user gateway mode, or force global gateway for all selected leads.');?></p>
            </div>
        </div>
        <?php } ?>

        <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo __('Select Template');?></label>
            <div class="col-sm-10">
                <?php echo $this->Form->select('template_id',$templateOptions,array('id'=>'leadTemplateId','label'=>false,'class'=>'form-control','empty'=>__('Please Select'),'div'=>false,'selected'=>(int)$selectedTemplateId));?>
                <div style="margin-top:8px;">
                    <button type="submit" name="submit_action" value="save_default" class="btn btn-default">
                        <span class="fa fa-bookmark"></span>&nbsp;<?php echo __('Save as My Default Template');?>
                    </button>
                </div>
                <?php if($hasTemplateOptions){ ?>
                    <p class="help-block"><?php echo __('This works like Schedule SMS: select template and message will auto-fill for easy bulk send.');?></p>
                <?php } else { ?>
                    <p class="help-block text-danger"><?php echo __('No template found. Please create/publish your template first.');?></p>
                <?php } ?>
            </div>
        </div>

        <?php if($isEmailMode){ ?>
        <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo __('Subject');?></label>
            <div class="col-sm-10">
                <?php echo $this->Form->input('subject',array('id'=>'LeadSubject','type'=>'text','label'=>false,'class'=>'form-control','div'=>false,'required'=>true));?>
            </div>
        </div>
        <?php } ?>

        <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo __('Message');?></label>
            <div class="col-sm-10">
                <?php if($isEmailMode){ ?>
                    <?php echo $this->Tinymce->input('message',array('id'=>'LeadMessage','label'=>false,'class'=>'form-control','div'=>false),array('language'=>$configLanguage,'directionality'=>$dirType),'full');?>
                <?php } else { ?>
                    <?php echo $this->Form->textarea('message',array('id'=>'LeadMessage','label'=>false,'class'=>'form-control','div'=>false,'rows'=>6,'required'=>true));?>
                <?php } ?>
            </div>
        </div>

        <div class="form-group text-left">
            <div class="col-sm-offset-2 col-sm-10">
                <button type="submit" name="submit_action" value="send" class="btn btn-success"><span class="fa fa-send"></span>&nbsp;<?php echo $isEmailMode ? __('Send Emails') : __('Send SMS');?></button>
                <a href="<?php echo h($manageUrl);?>" class="btn btn-danger"><span class="fa fa-close"></span>&nbsp;<?php echo __('Close');?></a>
            </div>
        </div>
        <?php echo $this->Form->end();?>

        <hr />
        <h4><?php echo __('Recipients');?></h4>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th><?php echo __('#');?></th>
                        <th><?php echo __('Lead Name');?></th>
                        <th><?php echo __('Mobile');?></th>
                        <th><?php echo __('Email');?></th>
                        <th><?php echo __('Agency');?></th>
                        <th><?php echo __('Agent');?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $sr = 1; foreach((array)$leadRows as $row){ ?>
                    <?php
                        $leadName = trim((string)(isset($row['Lead']['lead_name']) ? $row['Lead']['lead_name'] : ''));
                        if($leadName === '')
                        {
                            $leadName = trim((string)(isset($row['Client']['name']) ? $row['Client']['name'] : ''));
                        }
                        $leadMobile = trim((string)(isset($row['Lead']['lead_mobile']) ? $row['Lead']['lead_mobile'] : ''));
                        if($leadMobile === '')
                        {
                            $leadMobile = trim((string)(isset($row['Client']['mobile']) ? $row['Client']['mobile'] : ''));
                        }
                        if($leadMobile === '')
                        {
                            $leadMobile = trim((string)(isset($row['Client']['phone']) ? $row['Client']['phone'] : ''));
                        }
                        $leadCallNo = preg_replace('/[^0-9\+]/', '', (string)$leadMobile);
                        $leadEmail = trim((string)(isset($row['Lead']['lead_email']) ? $row['Lead']['lead_email'] : ''));
                        if($leadEmail === '')
                        {
                            $leadEmail = trim((string)(isset($row['Client']['email']) ? $row['Client']['email'] : ''));
                        }
                        $agencyName = trim((string)(isset($row['Project']['name']) ? $row['Project']['name'] : ''));
                        $agentName = trim((string)(isset($row['User']['name']) ? $row['User']['name'] : ''));
                    ?>
                    <tr>
                        <td><?php echo (int)$sr++;?></td>
                        <td><?php echo h($leadName);?></td>
                        <td>
                            <?php
                            if($leadMobile === '')
                            {
                                echo '-';
                            }
                            elseif($leadCallNo !== '')
                            {
                                echo $this->Html->link(h($leadMobile), 'tel:'.$leadCallNo, array('escape' => false));
                            }
                            else
                            {
                                echo h($leadMobile);
                            }
                            ?>
                        </td>
                        <td><?php echo h($leadEmail);?></td>
                        <td><?php echo h($agencyName);?></td>
                        <td><?php echo h($agentName);?></td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
