<?php
 $this->Js->JqueryEngine->jQueryObject = 'jQuery';
// Paginator options
$this->Paginator->options(array(
  'update' => '#resultDiv',
  'evalScripts' => true,
));
?>
<div id="resultDiv">
<div class="page-header-content wv-page-header">
    <div class="page-title wv-page-title-box">
        <h4 class="wv-page-title"><i class="fa fa-comment-o text-primary"></i> <?php echo __('SMS & WhatsApp Templates');?></h4>
        <small class="wv-page-subtitle"><?php echo __('Configure templates for automated SMS alerts, reminders, receipts, and WhatsApp text templates');?></small>
    </div>
    <div class="heading-elements wv-page-header-actions">
        <?php $url=$this->Html->url(array('controller'=>'Smstemplates'));?>
        <?php echo $this->Html->link('<i class="fa fa-plus"></i> '.(empty($isGlobalTemplateAdmin) ? __('Add My SMS Template') : __('Add New SMS Template')),array('controller'=>'Smstemplates','action'=>'add'),array('escape'=>false,'class'=>'btn btn-primary'));?>
        <?php echo $this->Html->link('<i class="fa fa-edit"></i> '.__('Edit'),'#',array('name'=>'editallfrm','id'=>'editallfrm','onclick'=>"check_perform_edit('$url');",'escape'=>false,'class'=>'btn btn-default'));?>
        <?php echo $this->Html->link('<i class="fa fa-trash"></i> '.__('Delete'),'#',array('name'=>'deleteallfrm','id'=>'deleteallfrm','onclick'=>'check_perform_delete();','escape'=>false,'class'=>'btn btn-default btn-action-delete'));?>
    </div>
</div>

<div class="panel panel-flat">
    <?php echo $this->Session->flash();?>
    <?php if(empty($isGlobalTemplateAdmin)){ ?>
    <div class="alert alert-info" style="margin: 15px 20px 0; border-radius: 8px;">
        <i class="fa fa-info-circle"></i> <?php echo __('These are your own SMS templates only. They are isolated by login and used in your Leads SMS send popup.');?>
    </div>
    <?php } ?>

    <?php
    $pageParams = $this->Paginator->params();
    $limit = $pageParams['limit'];
    $page = $pageParams['page'];
    $serialNo = 1*$limit*($page-1)+1;
    ?>
    <?php echo $this->Form->create(array('name'=>'deleteallfrm','action' => 'deleteall'));?>
    <div id="printable_content">
        <?php echo $this->Html->css('print');?>
        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th style="width: 40px;" class="text-center"><?php echo $this->Form->checkbox('checkbox', array('value'=>'deleteall','name'=>'selectAll','label'=>false,'id'=>'selectAll','hiddenField'=>false));?></th>
                        <th style="width: 60px;" class="text-center"><?php echo $this->Paginator->sort('id', __('#'), array('direction' => 'desc'));?></th>
                        <th><?php echo $this->Paginator->sort('name', __('Name'), array('direction' => 'asc'));?></th>
                        <th><?php echo $this->Paginator->sort('description', __('Message Preview'), array('direction' => 'asc'));?></th>
                        <th><?php echo __('Type Code');?></th>
                        <th><?php echo __('Template Category');?></th>
                        <th class="text-center" style="width: 120px;"><?php echo $this->Paginator->sort('status', __('Status'), array('direction' => 'asc'));?></th>
                        <th class="text-center" style="width: 110px;"><?php echo __('Action');?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($Smstemplate)){ ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted" style="padding: 30px;">
                            <i class="fa fa-info-circle fa-2x" style="display:block; margin-bottom: 8px; color: #94a3b8;"></i>
                            <?php echo __('No SMS templates configured.'); ?>
                        </td>
                    </tr>
                    <?php } else { ?>
                    <?php foreach ($Smstemplate as $post):
                    $id=$post['Smstemplate']['id'];
                    $smsTypeCode = strtoupper(trim((string)$post['Smstemplate']['type']));
                    $smsTypeLabel = ($smsTypeCode==='') ? __('N/A') : $smsTypeCode;
                    if($smsTypeCode==='')
                    {
                        $smsCategory = __('Existing Custom');
                    }
                    elseif(in_array($smsTypeCode,array('LED','LDS'),true))
                    {
                        $smsCategory = __('Lead Default');
                    }
                    else
                    {
                        $smsCategory = __('System / Auto');
                    }
                    ?>
                    <tr>
                        <td class="text-center"><?php echo $this->Form->checkbox(false,array('value' => $post['Smstemplate']['id'],'name'=>'data[Smstemplate][id][]','id'=>"DeleteCheckbox$id",'class'=>'chkselect'));?></td>
                        <td class="text-center"><?php echo $serialNo++;?></td>
                        <td style="font-weight: 600; color: #0f172a;"><?php echo h($post['Smstemplate']['name']);?></td>
                        <td style="max-width: 320px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: #475569;" title="<?php echo h($post['Smstemplate']['description']);?>">
                            <?php echo h($post['Smstemplate']['description']);?>
                        </td>
                        <td><code><?php echo h($smsTypeLabel);?></code></td>
                        <td><span class="label label-info"><?php echo h($smsCategory);?></span></td>
                        <td class="text-center">
                            <span class="badge-status label label-<?php if($post['Smstemplate']['status']=="Published")echo"success";else echo"danger";?>">
                                <i class="fa fa-circle" style="font-size:7px;"></i> <?php echo __($post['Smstemplate']['status']); ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="btn-group">
                                <button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown" aria-expanded="false" style="padding: 5px 10px; border-radius: 6px;">
                                    <?php echo __('Action');?> <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-right" role="menu">
                                    <li><?php echo $this->Html->link('<i class="fa fa-eye"></i> '.__('View Template'),'#',array('onclick'=>"show_modal('$url/View/$id');",'escape'=>false));?></li>
                                    <li><?php echo $this->Html->link('<i class="fa fa-edit"></i> '.__('Edit'),'#',array('name'=>'editallfrm','onclick'=>"check_perform_sedit('$url','$id');",'escape'=>false));?></li>
                                    <?php if($post['Smstemplate']['type']==null){?>
                                    <li class="divider"></li>
                                    <li><?php echo $this->Html->Link('<i class="fa fa-trash text-danger"></i> <span class="text-danger">'.__('Delete').'</span>','#',array('onclick'=>"check_perform_sdelete('$id');",'escape'=>false));?></li>
                                    <?php }?>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach;?>
                    <?php unset($post);?>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php echo $this->Form->end();?>
    <div class="card-footer table-footer">
        <?php echo $this->element('pagination');?>
    </div>
</div>
</div>
