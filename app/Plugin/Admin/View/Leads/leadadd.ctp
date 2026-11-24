<?php
echo $this->Html->css('select2/select2');
echo $this->Html->css('select2/select2-bootstrap');
echo $this->fetch('css');
echo $this->Html->script('select2.min');
echo $this->fetch('script');
$clientUrl=$this->Html->url(array('controller'=>'Leads','action'=>'clientsearch'));
$propertyUrl=$this->Html->url(array('controller'=>'Leads','action'=>'propertysearch'));
$agentAjaxUrl = $this->Html->url(array('controller'=>'Ajaxcontents','action'=>'agent'));
$leadDatePickerFormat = (isset($dpFormat) && trim((string)$dpFormat) !== '') ? trim((string)$dpFormat) : 'DD-MMM-YYYY';
$duplicateLeadRows = isset($duplicateLeadRows) ? (array)$duplicateLeadRows : array();
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

<div class="page-title">
    <div class="title-env">
        <h1 class="title"><?php echo __('Add Lead Contact');?></h1>
    </div>
</div>
<div class="panel">
    <div class="panel-heading">
        <div class="btn-group">
            <?php echo $this->Html->link('<span class="fa fa-dashboard"></span>&nbsp;'.__('CRM Dashboard'),array('controller'=>'Leads','action'=>'dashboard'),array('escape'=>false,'class'=>'btn btn-default'));?>
            <?php echo $this->Html->link('<span class="fa fa-columns"></span>&nbsp;'.__('Kanban'),array('controller'=>'Leads','action'=>'kanban'),array('escape'=>false,'class'=>'btn btn-primary'));?>
            <?php echo $this->Html->link('<span class="fa fa-upload"></span>&nbsp;'.__('Import Leads'),array('controller'=>'Leads','action'=>'import'),array('escape'=>false,'class'=>'btn btn-info'));?>
            <?php echo $this->Html->link('<span class="fa fa-clone"></span>&nbsp;'.__('Duplicate Detector'),array('controller'=>'Leadduplicates','action'=>'index'),array('escape'=>false,'class'=>'btn btn-warning'));?>
        </div>
    </div>
    <div class="panel-body">
        <?php echo $this->Session->flash();?>
        <?php if(!empty($duplicateLeadRows)){ ?>
        <div class="alert alert-warning">
            <strong><?php echo __('Duplicate history found');?></strong>
            <div class="table-responsive" style="margin-top:8px;">
                <table class="table table-bordered table-condensed">
                    <tr>
                        <th><?php echo __('Lead');?></th>
                        <th><?php echo __('Mobile');?></th>
                        <th><?php echo __('Email');?></th>
                        <th><?php echo __('Agency');?></th>
                        <th><?php echo __('Agent');?></th>
                        <th><?php echo __('Status');?></th>
                        <th><?php echo __('Created');?></th>
                        <th><?php echo __('Open');?></th>
                    </tr>
                    <?php foreach($duplicateLeadRows as $dup){ ?>
                    <tr>
                        <td><?php echo h(!empty($dup['Lead']['lead_name']) ? $dup['Lead']['lead_name'] : (!empty($dup['Client']['name']) ? $dup['Client']['name'] : '-'));?></td>
                        <td><?php echo h(!empty($dup['Lead']['lead_mobile']) ? $dup['Lead']['lead_mobile'] : (!empty($dup['Client']['mobile']) ? $dup['Client']['mobile'] : $dup['Client']['phone']));?></td>
                        <td><?php echo h(!empty($dup['Lead']['lead_email']) ? $dup['Lead']['lead_email'] : (!empty($dup['Client']['email']) ? $dup['Client']['email'] : '-'));?></td>
                        <td><?php echo h(!empty($dup['Project']['name']) ? $dup['Project']['name'] : '-');?></td>
                        <td><?php echo h(!empty($dup['AssignedUser']['name']) ? $dup['AssignedUser']['name'] : '-');?></td>
                        <td><?php echo h(!empty($dup['Status']['name']) ? $dup['Status']['name'] : '-');?></td>
                        <td><?php echo !empty($dup['Lead']['created']) ? h(date('d-M-Y H:i',strtotime($dup['Lead']['created']))) : '-';?></td>
                        <td><?php echo $this->Html->link(__('View'),array('controller'=>'Leads','action'=>'leadview',(int)$dup['Lead']['id']),array('class'=>'btn btn-xs btn-info'));?></td>
                    </tr>
                    <?php } ?>
                </table>
            </div>
        </div>
        <?php } ?>
        <?php echo $this->Form->create('Lead', array('controller' => 'Leads', 'action' => 'leadadd', 'class' => 'form-horizontal', 'novalidate' => 'novalidate'));?>

        <?php if($userType!='AGT'){ ?>
        <div class="form-group">
            <?php if($userType!='AGY'){ ?>
            <label class="col-sm-2 control-label"><small><?php echo __('Agency');?>:</small></label>
            <div class="col-sm-4">
                <?php echo $this->Form->select('project_id',$projectOptions,array('id'=>'leadProjectId','label'=>false,'class'=>'form-control','empty'=>__('Please Select'),'div'=>false,'value'=>$selectedProjectId));?>
            </div>
            <?php } else { echo $this->Form->hidden('project_id',array('value'=>$agencyId,'id'=>'leadProjectId')); } ?>
            <label class="col-sm-2 control-label"><small><?php echo __('Agent');?>:</small></label>
            <div class="col-sm-4">
                <?php echo $this->Form->select('user_id',$agentOptions,array('id'=>'leadAgentId','label'=>false,'class'=>'form-control','empty'=>__('Please Select'),'div'=>false,'value'=>$selectedAgentId));?>
            </div>
        </div>
        <?php } else {
            echo $this->Form->hidden('project_id',array('value'=>$agencyId,'id'=>'leadProjectId'));
            echo $this->Form->hidden('user_id',array('value'=>$luserId,'id'=>'leadAgentId'));
        } ?>

        <div class="form-group">
            <label class="col-sm-2 control-label"><small><?php echo __('Existing Contact (Optional)');?>:</small></label>
            <div class="col-sm-10">
                <?php echo $this->Form->input('client_id',array('type'=>'text','id'=>'leadClientId','label'=>false,'placeholder'=>__('Search existing contact name'),'class'=>'form-control','div'=>false,'required'=>false));?>
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label"><small><?php echo __('Lead Name');?>:</small></label>
            <div class="col-sm-4">
                <?php echo $this->Form->input('lead_name',array('label'=>false,'class'=>'form-control','placeholder'=>__('Lead Name'),'div'=>false,'required'=>true));?>
            </div>
            <label class="col-sm-2 control-label"><small><?php echo __('Mobile');?>:</small></label>
            <div class="col-sm-4">
                <?php echo $this->Form->input('lead_mobile',array('label'=>false,'class'=>'form-control','placeholder'=>__('923001234567'),'div'=>false,'required'=>true));?>
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label"><small><?php echo __('Email');?>:</small></label>
            <div class="col-sm-4">
                <?php echo $this->Form->input('lead_email',array('label'=>false,'class'=>'form-control','placeholder'=>__('Email'),'div'=>false));?>
            </div>
            <label class="col-sm-2 control-label"><small><?php echo __('Lead Source');?>:</small></label>
            <div class="col-sm-4">
                <?php echo $this->Form->input('lead_source',array('label'=>false,'class'=>'form-control','placeholder'=>__('Facebook / Reference / Campaign'),'div'=>false));?>
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label"><small><?php echo __('Address');?>:</small></label>
            <div class="col-sm-10">
                <?php echo $this->Form->input('lead_address',array('label'=>false,'class'=>'form-control','placeholder'=>__('Address'),'div'=>false));?>
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label"><small><?php echo __('City');?>:</small></label>
            <div class="col-sm-4">
                <?php echo $this->Form->input('lead_city',array('label'=>false,'class'=>'form-control','placeholder'=>__('City'),'div'=>false));?>
            </div>
            <label class="col-sm-2 control-label"><small><?php echo __('Budget');?>:</small></label>
            <div class="col-sm-4">
                <?php echo $this->Form->input('lead_budget',array('label'=>false,'class'=>'form-control','placeholder'=>__('Budget'),'div'=>false));?>
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label"><small><?php echo __('Language');?>:</small></label>
            <div class="col-sm-4">
                <?php echo $this->Form->input('lead_language',array('label'=>false,'class'=>'form-control','placeholder'=>__('Language'),'div'=>false));?>
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label"><small><?php echo __('Lead For');?>:</small></label>
            <div class="col-sm-3">
                <?php echo $this->Form->select('type_id',$typeOptions,array('id'=>'leadTypeId','label'=>false,'class'=>'form-control','empty'=>__('All'),'div'=>false));?>
            </div>
            <div class="col-sm-7">
                <?php echo $this->Form->input('property_id',array('type'=>'text','id'=>'leadPropertyId','label'=>false,'class'=>'form-control','placeholder'=>__('Search Property Name'),'div'=>false));?>
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label"><small><?php echo __('Follow Up');?>:</small></label>
            <div class="col-sm-4">
                <div class="input-group date" id="leadFollowUpDate">
                    <?php echo $this->Form->input('follow_up',array('type'=>'text','label'=>false,'class'=>'form-control','div'=>false,'placeholder'=>__('Select date & time')));?>
                    <span class="input-group-addon"><span class="fa fa-calendar"></span></span>
                </div>
            </div>
            <label class="col-sm-2 control-label"><small><?php echo __('Status');?>:</small></label>
            <div class="col-sm-4">
                <?php echo $this->Form->select('status_id',$statusOptions,array('label'=>false,'class'=>'form-control','empty'=>__('Please Select'),'div'=>false,'value'=>$defaultStatusId));?>
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-2 control-label"><small><?php echo __('Remarks');?>:</small></label>
            <div class="col-sm-10">
                <?php echo $this->Form->textarea('remarks',array('label'=>false,'class'=>'form-control','placeholder'=>__('Remarks / Follow-up notes'),'div'=>false,'rows'=>4));?>
            </div>
        </div>

        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
                <label>
                    <?php echo $this->Form->checkbox('create_contact',array('value'=>1,'hiddenField'=>false));?>
                    <?php echo __('Create/Link as Customer Contact now');?>
                </label>
                <label style="margin-left:18px;">
                    <?php echo $this->Form->checkbox('allow_duplicate',array('value'=>1,'hiddenField'=>false));?>
                    <?php echo __('Allow duplicate after review');?>
                </label>
            </div>
        </div>

        <div class="form-group text-left">
            <div class="col-sm-offset-2 col-sm-10">
                <button type="submit" class="btn btn-success"><span class="fa fa-save"></span> <?php echo __('Save');?></button>
                <?php echo $this->Html->link('<span class="fa fa-close"></span> '.__('Close'),array('controller'=>'Leads','action'=>'dashboard'),array('class'=>'btn btn-danger','escape'=>false));?>
            </div>
        </div>
        <?php echo $this->Form->end();?>
    </div>
</div>
