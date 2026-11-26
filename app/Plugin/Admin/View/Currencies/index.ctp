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
        <h4 class="wv-page-title"><i class="fa fa-money text-primary"></i> <?php echo __('Currencies');?></h4>
        <small class="wv-page-subtitle"><?php echo __('Configure supported transaction currencies and display symbols (e.g. PKR, AED, USD)');?></small>
    </div>
    <div class="heading-elements wv-page-header-actions">
        <?php $url=$this->Html->url(array('controller'=>'Currencies')); echo $this->Html->link('<i class="fa fa-plus"></i> '.__('Add New Currency'),array('controller'=>'Currencies','action'=>'add'),array('escape'=>false,'class'=>'btn btn-primary'));?>
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
                        <th><?php echo $this->Paginator->sort('name', __('Currency Name'), array('direction' => 'asc'));?></th>
                        <th class="text-center" style="width: 140px;"><?php echo __('Currency Symbol');?></th>
                        <th class="text-center" style="width: 100px;"><?php echo __('Action');?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($Currency)){ ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted" style="padding: 30px;">
                            <i class="fa fa-info-circle fa-2x" style="display:block; margin-bottom: 8px; color: #94a3b8;"></i>
                            <?php echo __('No currencies found.'); ?>
                        </td>
                    </tr>
                    <?php } else { ?>
                    <?php foreach ($Currency as $post):
                    $id=$post['Currency']['id'];?>
                    <tr>
                        <td class="text-center"><?php echo $this->Form->checkbox(false,array('value' => $post['Currency']['id'],'name'=>'data[Currency][id][]','id'=>"DeleteCheckbox$id",'class'=>'chkselect'));?></td>
                        <td class="text-center"><?php echo $serialNo++;?></td>
                        <td style="font-weight: 600; color: #0f172a;"><?php echo h($post['Currency']['name']);?></td>
                        <td class="text-center">
                            <?php
                            $symbol = '';
                            if(isset($post['Currency']['symbol']) && strlen(trim($post['Currency']['symbol']))>0)
                            {
                                $symbol = $post['Currency']['symbol'];
                            }
                            elseif(isset($post['Currency']['short']) && strlen(trim($post['Currency']['short']))>0)
                            {
                                $symbol = $post['Currency']['short'];
                            }
                            elseif(isset($post['Currency']['photo']) && strlen(trim($post['Currency']['photo']))>0)
                            {
                                $symbol = $post['Currency']['photo'];
                            }
                            ?>
                            <span class="label label-info" style="font-size: 13px; font-weight: 700; padding: 4px 12px;"><?php echo h($symbol);?></span>
                        </td>
                        <td class="text-center">
                            <div class="btn-group-actions">
                                <?php if($id!=1){ ?>
                                <?php echo $this->Html->Link('<i class="fa fa-trash"></i>',array('controller'=>'Currencies','action'=>'delete',$id),array('confirm'=>__('Are you sure you want to delete this currency?'),'escape'=>false,'class'=>'btn btn-default btn-xs btn-action-delete','title'=>__('Delete')));?>
                                <?php } ?>
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
