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
        <h4 class="wv-page-title"><i class="fa fa-building-o text-primary"></i> <?php echo __('Agencies Management');?></h4>
        <small class="wv-page-subtitle"><?php echo __('Manage real estate agencies, branches, addresses, and partner accounts');?></small>
    </div>
    <div class="heading-elements wv-page-header-actions">
        <?php $url=$this->Html->url(array('controller'=>'Agencies'));$viewUrl=$this->Html->url(array('controller'=>'Users'));?>
        <?php echo $this->Html->link('<i class="fa fa-plus"></i> '.__('Add New Agency'),array('controller'=>'Users','action'=>'add','AY'),array('escape'=>false,'class'=>'btn btn-primary'));?>
        <?php echo $this->Html->link('<i class="fa fa-edit"></i> '.__('Edit'),'javascript:void(0);',array('name'=>'editallfrm','id'=>'editallfrm','onclick'=>"check_perform_edit('$viewUrl');",'escape'=>false,'class'=>'btn btn-default'));?>
        <?php echo $this->Html->link('<i class="fa fa-trash"></i> '.__('Delete'),'javascript:void(0);',array('name'=>'deleteallfrm','id'=>'deleteallfrm','onclick'=>'check_perform_delete();','escape'=>false,'class'=>'btn btn-default btn-action-delete'));?>
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
                        <th style="width: 60px;" class="text-center"><?php echo $this->Paginator->sort('id', '#', array('direction' => 'desc'));?></th>
                        <th><?php echo $this->Paginator->sort('name', __('Agency Name'), array('direction' => 'asc'));?></th>
                        <th><?php echo $this->Paginator->sort('email', __('Email'), array('direction' => 'asc'));?></th>
                        <th><?php echo $this->Paginator->sort('address', __('Address'), array('direction' => 'asc'));?></th>
                        <th><?php echo $this->Paginator->sort('location', __('Location'), array('direction' => 'asc'));?></th>
                        <th class="text-center"><?php echo $this->Paginator->sort('status', __('Status'), array('direction' => 'asc'));?></th>
                        <th class="text-center"><?php echo __('Frontend Visibility');?></th>
                        <th class="text-center" style="width: 100px;"><?php echo __('Actions');?></th>
                        <th class="text-center" style="width: 100px;"><?php echo __('Linked Data');?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($Agency)){ ?>
                    <tr>
                        <td colspan="10" class="text-center text-muted" style="padding: 30px;">
                            <i class="fa fa-info-circle fa-2x" style="display:block; margin-bottom: 8px; color: #94a3b8;"></i>
                            <?php echo __('No agencies found.'); ?>
                        </td>
                    </tr>
                    <?php } else { ?>
                    <?php foreach ($Agency as $post):
                    $id=$post['User']['id'];?>
                    <tr>
                        <td class="text-center"><?php echo $this->Form->checkbox(false,array('value' => $post['User']['id'],'name'=>'data[User][id][]','id'=>"DeleteCheckbox$id",'class'=>'chkselect'));?></td>
                        <td class="text-center"><?php echo $serialNo++;?></td>
                        <td style="font-weight: 600; color: #0f172a;"><?php echo h($post['Agency']['name']);?></td>
                        <td><?php echo h($post['Agency']['email']);?></td>
                        <td><?php echo h($post['User']['address']);?></td>
                        <td><?php echo h($post['Location']['name']);?></td>
                        <td class="text-center">
                            <span class="badge-status label label-<?php if(h($post['User']['status'])=="Active")echo"success";else echo"danger";?>">
                                <i class="fa fa-circle" style="font-size:7px;"></i> <?php echo __($post['User']['status']);?>
                            </span>
                        </td>
                        <td class="text-center">
                            <?php
                            $frontViewValue = isset($post['User']['front_view']) ? trim((string)$post['User']['front_view']) : '';
                            $isFrontVisible = (strcasecmp($frontViewValue,'Off') !== 0);
                            ?>
                            <span class="badge-status label label-<?php echo $isFrontVisible ? 'success' : 'danger';?>">
                                <i class="fa fa-circle" style="font-size:7px;"></i> <?php echo $isFrontVisible ? __('Visible (On)') : __('Hidden (Off)');?>
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="btn-group">
                                <button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown" aria-expanded="false" style="padding: 5px 10px; border-radius: 6px;">
                                    <?php echo __('Action');?> <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-right" role="menu">
                                    <li><?php echo $this->Html->link('<i class="fa fa-eye"></i> '.__('View Agency'),'#',array('onclick'=>"show_modal('$viewUrl/View/$id');",'escape'=>false));?></li>
                                    <li><?php echo $this->Html->link('<i class="fa fa-edit"></i> '.__('Edit Agency'),'javascript:void(0);',array('name'=>'editallfrm','onclick'=>"check_perform_sedit('$viewUrl','$id');",'escape'=>false));?></li>
                                    <?php if(($userType=='ADR' || !empty($isSuperAdminSession)) && (int)$id!==1){ ?>
                                    <li><?php echo $this->Html->link('<i class="fa fa-sign-in"></i> '.__('Login As Agency'),array('controller'=>'Users','action'=>'loginas',$id),array('escape'=>false,'target'=>'_blank','rel'=>'noopener','onclick'=>"return confirm('".__('Login as this agency in a new tab?')."');"));?></li>
                                    <?php } ?>
                                    <li class="divider"></li>
                                    <li><?php if($id!=1){if($userType=='ADR' || ($userType=='AGY' && $post['Ugroup']['type']!="AGY")){echo $this->Html->Link('<i class="fa fa-trash text-danger"></i> <span class="text-danger">'.__('Delete').'</span>','#',array('onclick'=>"check_perform_sdelete('$id');",'escape'=>false));}}?></li>
                                </ul>
                            </div>
                        </td>
                        <td class="text-center">
                            <div class="btn-group">
                                <button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown" aria-expanded="false" style="padding: 5px 10px; border-radius: 6px;">
                                    <i class="fa fa-folder-open-o"></i> <?php echo __('View');?> <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-right" role="menu">
                                    <li><?php echo $this->Html->link('<i class="fa fa-home"></i> '.__('Properties'),array('controller'=>'Properties','action'=>'index',$id),array('escape'=>false));?></li>
                                    <li><?php echo $this->Html->link('<i class="fa fa-user-plus"></i> '.__('Leads'),array('controller'=>'Leads','action'=>'index',$id),array('escape'=>false));?></li>
                                    <li><?php echo $this->Html->link('<i class="fa fa-handshake-o"></i> '.__('Deals'),array('controller'=>'Deals','action'=>'index',$id),array('escape'=>false));?></li>
                                    <li><?php echo $this->Html->link('<i class="fa fa-shopping-cart"></i> '.__('Purchases'),array('controller'=>'Purchases','action'=>'index',$id),array('escape'=>false));?></li>
                                    <li class="divider"></li>
                                    <li><?php echo $this->Html->link('<i class="fa fa-calculator"></i> '.__('Accounts'),array('controller'=>'Ardaccounts','action'=>'dashboard',$id),array('escape'=>false));?></li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php unset($post); ?>
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
