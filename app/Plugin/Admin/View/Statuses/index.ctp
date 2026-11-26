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
        <h4 class="wv-page-title"><i class="fa fa-flag text-primary"></i> <?php echo __('Workflow Statuses');?></h4>
        <small class="wv-page-subtitle"><?php echo __('Configure status stages, color accents, and process labels for leads and deals');?></small>
    </div>
    <div class="heading-elements wv-page-header-actions">
        <?php $url=$this->Html->url(array('controller'=>'Statuses'));?>
        <?php echo $this->Html->link('<i class="fa fa-plus"></i> '.__('Add New Status'),array('controller'=>'Statuses','action'=>'add'),array('escape'=>false,'class'=>'btn btn-primary'));?>
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
                        <th><?php echo $this->Paginator->sort('name', __('Status Label'), array('direction' => 'asc'));?></th>
                        <th class="text-center" style="width: 140px;"><?php echo $this->Paginator->sort('color', __('Badge Color'), array('direction' => 'asc'));?></th>
                        <th class="text-center" style="width: 120px;"><?php echo __('Action');?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($posts)){ ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted" style="padding: 30px;">
                            <i class="fa fa-info-circle fa-2x" style="display:block; margin-bottom: 8px; color: #94a3b8;"></i>
                            <?php echo __('No statuses found.'); ?>
                        </td>
                    </tr>
                    <?php } else { ?>
                    <?php foreach ($posts as $post):
                    $id=$post['Status']['id'];?>
                    <tr>
                        <td class="text-center"><?php echo $this->Form->checkbox(false,array('value' => $post['Status']['id'],'name'=>'data[Status][id][]','id'=>"DeleteCheckbox$id",'class'=>'chkselect'));?></td>
                        <td class="text-center"><?php echo $serialNo++;?></td>
                        <td style="font-weight: 600; color: #0f172a;">
                            <span class="badge-status" style="background-color: <?php echo h(!empty($post['Status']['color']) ? $post['Status']['color'] : '#64748b');?>; color: #ffffff; padding: 4px 10px; border-radius: 999px;">
                                <i class="fa fa-circle" style="font-size:7px; opacity:0.8;"></i> <?php echo h($post['Status']['name']); ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <div style="display:inline-flex; align-items:center; gap:8px;">
                                <span style="display:inline-block; width:18px; height:18px; border-radius:50%; background-color:<?php echo h($post['Status']['color']);?>; border: 1px solid rgba(0,0,0,0.15); box-shadow: 0 1px 2px rgba(0,0,0,0.1);"></span>
                                <code><?php echo h($post['Status']['color']);?></code>
                            </div>
                        </td>
                        <td class="text-center">
                            <div class="btn-group-actions">
                                <?php echo $this->Html->link('<i class="fa fa-edit"></i> '.__('Edit'),'#',array('name'=>'editallfrm','onclick'=>"check_perform_sedit('$url','$id');",'escape'=>false,'class'=>'btn btn-default btn-xs btn-action-edit'));?>
                                <?php if($post['Status']['type']==null){?>
                                <?php echo $this->Html->Link('<i class="fa fa-trash"></i>','javascript:void(0);',array('onclick'=>"check_perform_sdelete('$id');",'escape'=>false,'class'=>'btn btn-default btn-xs btn-action-delete','title'=>__('Delete')));?>
                                <?php }?>
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
