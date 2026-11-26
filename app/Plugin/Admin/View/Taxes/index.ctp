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
        <h4 class="wv-page-title"><i class="fa fa-percent text-primary"></i> <?php echo __('Tax Rates & Duties');?></h4>
        <small class="wv-page-subtitle"><?php echo __('Configure sales taxes, transfer duties, and property levy percentages');?></small>
    </div>
    <div class="heading-elements wv-page-header-actions">
        <?php $url=$this->Html->url(array('controller'=>'Taxes'));?>
        <?php echo $this->Html->link('<i class="fa fa-plus"></i> '.__('Add New Tax'),array('controller'=>'Taxes','action'=>'add'),array('escape'=>false,'class'=>'btn btn-primary'));?>
        <?php echo $this->Html->link('<i class="fa fa-edit"></i> '.__('Edit'),'#',array('name'=>'editallfrm','id'=>'editallfrm','onclick'=>"check_perform_edit('$url');",'escape'=>false,'class'=>'btn btn-default'));?>
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
                        <th><?php echo $this->Paginator->sort('name', __('Tax Name'), array('direction' => 'asc'));?></th>
                        <th class="text-right" style="width: 150px;"><?php echo $this->Paginator->sort('percent', __('Percentage (%)'), array('direction' => 'asc'));?></th>
                        <th class="text-center" style="width: 120px;"><?php echo __('Action');?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($Tax)){ ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted" style="padding: 30px;">
                            <i class="fa fa-info-circle fa-2x" style="display:block; margin-bottom: 8px; color: #94a3b8;"></i>
                            <?php echo __('No tax rates configured.'); ?>
                        </td>
                    </tr>
                    <?php } else { ?>
                    <?php foreach ($Tax as $post):
                    $id=$post['Tax']['id'];?>
                    <tr>
                        <td class="text-center"><?php echo $this->Form->checkbox(false,array('value' => $post['Tax']['id'],'name'=>'data[Tax][id][]','id'=>"DeleteCheckbox$id",'class'=>'chkselect'));?></td>
                        <td class="text-center"><?php echo $serialNo++;?></td>
                        <td style="font-weight: 600; color: #0f172a;"><?php echo h($post['Tax']['name']);?></td>
                        <td class="text-right" style="font-family: monospace; font-size: 13px; font-weight: 700; color: #2563eb;">
                            <?php echo h($post['Tax']['percent']);?> %
                        </td>
                        <td class="text-center">
                            <div class="btn-group-actions">
                                <?php echo $this->Html->link('<i class="fa fa-edit"></i> '.__('Edit'),'#',array('name'=>'editallfrm','onclick'=>"check_perform_sedit('$url','$id');",'escape'=>false,'class'=>'btn btn-default btn-xs btn-action-edit'));?>
                                <?php echo $this->Html->Link('<i class="fa fa-trash"></i>','javascript:void(0);',array('onclick'=>"check_perform_sdelete('$id');",'escape'=>false,'class'=>'btn btn-default btn-xs btn-action-delete','title'=>__('Delete')));?>
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
