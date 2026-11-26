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
        <h4 class="wv-page-title"><i class="fa fa-cubes text-primary"></i> <?php echo __('Property Types');?></h4>
        <small class="wv-page-subtitle"><?php echo __('Configure building, unit, and property classifications with icons and photos');?></small>
    </div>
    <div class="heading-elements wv-page-header-actions">
        <?php $url=$this->Html->url(array('controller'=>'Types'));?>
        <?php echo $this->Html->link('<i class="fa fa-plus"></i> '.__('Add New Type'),array('controller'=>'Types','action'=>'add'),array('escape'=>false,'class'=>'btn btn-primary'));?>
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
                        <th><?php echo $this->Paginator->sort('name', __('Type Name'), array('direction' => 'asc'));?></th>
                        <th class="text-center" style="width: 120px;"><?php echo __('Photo / Icon');?></th>
                        <th class="text-center" style="width: 110px;"><?php echo __('Action');?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($Type)){ ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted" style="padding: 30px;">
                            <i class="fa fa-info-circle fa-2x" style="display:block; margin-bottom: 8px; color: #94a3b8;"></i>
                            <?php echo __('No property types found.'); ?>
                        </td>
                    </tr>
                    <?php } else { ?>
                    <?php foreach ($Type as $post):
                    $id=$post['Type']['id'];?>
                    <tr>
                        <td class="text-center"><?php echo $this->Form->checkbox(false,array('value' => $post['Type']['id'],'name'=>'data[Type][id][]','id'=>"DeleteCheckbox$id",'class'=>'chkselect'));?></td>
                        <td class="text-center"><?php echo $serialNo++;?></td>
                        <td style="font-weight: 600; color: #0f172a;"><?php echo h($post['Type']['name']);?></td>
                        <td class="text-center">
                            <?php if(strlen($post['Type']['photo'])>0){ ?>
                                <?php echo $this->Html->image($post['Type']['dirt'].'_thumb/'.$post['Type']['photo'], array('style' => 'width: 50px; height: 38px; object-fit: cover; border-radius: 6px; border: 1px solid #e2e8f0;'));?>
                            <?php }else{ ?>
                                <?php echo $this->Html->image('nia.png',array('style' => 'width: 50px; height: 38px; object-fit: cover; border-radius: 6px; opacity: 0.6;'));?>
                            <?php } ?>
                        </td>
                        <td class="text-center">
                            <div class="btn-group">
                                <button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown" aria-expanded="false" style="padding: 5px 10px; border-radius: 6px;">
                                    <?php echo __('Action');?> <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-right" role="menu">
                                    <?php if(strlen($post['Type']['photo'])>0){?>
                                    <li><?php echo $this->Html->link('<i class="fa fa-picture-o text-warning"></i> '.__('Delete Photo'),array('controller'=>'Types','action'=>'deletephoto',$id),array('escape'=>false));?></li>
                                    <?php }?>
                                    <li><?php echo $this->Html->link('<i class="fa fa-edit"></i> '.__('Edit'),'#',array('name'=>'editallfrm','onclick'=>"check_perform_sedit('$url','$id');",'escape'=>false));?></li>
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
