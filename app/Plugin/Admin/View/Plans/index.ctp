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
        <h4 class="wv-page-title"><i class="fa fa-calendar-check-o text-primary"></i> <?php echo __('Installment & Payment Plans');?></h4>
        <small class="wv-page-subtitle"><?php echo __('Configure structured payment schedules, milestone installments, and pricing templates');?></small>
    </div>
    <div class="heading-elements wv-page-header-actions">
        <?php $url=$this->Html->url(array('controller'=>'Plans'));?>
        <?php echo $this->Html->link('<i class="fa fa-plus"></i> '.__('Add New Plan'),array('controller'=>'Plans','action'=>'add'),array('escape'=>false,'class'=>'btn btn-primary'));?>
        <?php echo $this->Html->link('<i class="fa fa-trash"></i> '.__('Delete'),'#',array('name'=>'deleteallfrm','id'=>'deleteallfrm','onclick'=>'check_perform_delete();','escape'=>false,'class'=>'btn btn-default btn-action-delete'));?>
    </div>
</div>

<div class="panel panel-flat">
    <?php echo $this->Session->flash();?>
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
                        <th><?php echo $this->Paginator->sort('name', __('Plan Title'), array('direction' => 'asc'));?></th>
                        <th class="text-right"><?php echo $this->Paginator->sort('total_amount', __('Total Payment'), array('direction' => 'asc'));?></th>
                        <th class="text-center"><?php echo __('Installments');?></th>
                        <th class="text-center" style="width: 120px;"><?php echo $this->Paginator->sort('status', __('Status'), array('direction' => 'asc'));?></th>
                        <th class="text-center" style="width: 110px;"><?php echo __('Action');?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($Plan)){ ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted" style="padding: 30px;">
                            <i class="fa fa-info-circle fa-2x" style="display:block; margin-bottom: 8px; color: #94a3b8;"></i>
                            <?php echo __('No payment plans configured.'); ?>
                        </td>
                    </tr>
                    <?php } else { ?>
                    <?php foreach ($Plan as $post):
                    $id=$post['Plan']['id'];?>
                    <tr>
                        <td class="text-center"><?php echo $this->Form->checkbox(false,array('value' => $post['Plan']['id'],'name'=>'data[Plan][id][]','id'=>"DeleteCheckbox$id",'class'=>'chkselect'));?></td>
                        <td class="text-center"><?php echo $serialNo++;?></td>
                        <td style="font-weight: 600; color: #0f172a;"><?php echo h($post['Plan']['name']);?></td>
                        <td class="text-right" style="font-family: monospace; font-size: 13.5px; font-weight: 700; color: #1e293b;">
                            <?php echo (float)$post['Plan']['total_amount']>0 ? 'Rs '.CakeNumber::format($post['Plan']['total_amount']) : '<span class="label label-default">'.__('Legacy').'</span>';?>
                        </td>
                        <td class="text-center">
                            <span class="label label-info" style="font-size: 12px; font-weight: 600;">
                                <?php echo !empty($scheduleCountMap[$id]) ? (int)$scheduleCountMap[$id].' '.__('stages') : '-';?>
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge-status label label-<?php if($post['Plan']['status']=="Active")echo"success";else echo"danger";?>">
                                <i class="fa fa-circle" style="font-size:7px;"></i> <?php echo __($post['Plan']['status']); ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="btn-group">
                                <button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown" aria-expanded="false" style="padding: 5px 10px; border-radius: 6px;">
                                    <?php echo __('Action');?> <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-right" role="menu">
                                    <li><?php echo $this->Html->link('<i class="fa fa-eye"></i> '.__('View Payment Plan'),array('controller'=>'Plans','action'=>'view',$id),array('escape'=>false));?></li>
                                    <li><?php echo $this->Html->link('<i class="fa fa-edit"></i> '.__('Edit Payment Plan'),array('controller'=>'Plans','action'=>'edit',$id),array('escape'=>false));?></li>
                                    <li class="divider"></li>
                                    <li><?php echo $this->Html->Link('<i class="fa fa-trash text-danger"></i> <span class="text-danger">'.__('Delete').'</span>','#',array('onclick'=>"check_perform_sdelete('$id');",'escape'=>false));?></li>
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
