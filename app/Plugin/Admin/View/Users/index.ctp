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
        <h4 class="wv-page-title"><i class="fa fa-users text-primary"></i> <?php echo __('Users Management');?></h4>
        <small class="wv-page-subtitle"><?php echo __('Manage system administrators, agency staff, and operational user accounts');?></small>
    </div>
    <div class="heading-elements wv-page-header-actions">
        <?php $url=$this->Html->url(array('controller'=>'Users')); echo $this->Html->link('<i class="fa fa-plus"></i> '.__('Add New User'),array('controller'=>'Users','action'=>'add'),array('escape'=>false,'class'=>'btn btn-primary'));?>
        <?php echo $this->Html->link('<i class="fa fa-edit"></i> '.__('Edit'),'javascript:void(0);',array('name'=>'editallfrm','id'=>'editallfrm','onclick'=>"check_perform_edit('$url');",'escape'=>false,'class'=>'btn btn-default'));?>
        <?php echo $this->Html->link('<i class="fa fa-trash"></i> '.__('Delete'),'#',array('id'=>'btn-deleteall','onclick'=>'check_perform_delete(); return false;','escape'=>false,'class'=>'btn btn-default btn-action-delete'));?>
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
                        <th><?php echo $this->Paginator->sort('name', __('Name'), array('direction' => 'asc'));?></th>
                        <th><?php echo $this->Paginator->sort('Ugroup.name', __('User Role'), array('direction' => 'desc'));?></th>
                        <th><?php echo __('Agency Name');?></th>
                        <th class="text-right"><?php echo __('Balance');?></th>
                        <th><?php echo $this->Paginator->sort('username', __('User Name'), array('direction' => 'asc'));?></th>                            
                        <th><?php echo $this->Paginator->sort('email', __('Email'), array('direction' => 'asc'));?></th>
                        <th><?php echo $this->Paginator->sort('mobile', __('Mobile'), array('direction' => 'asc'));?></th>
                        <th class="text-center"><?php echo $this->Paginator->sort('status', __('Status'), array('direction' => 'asc'));?></th>
                        <th class="text-center" style="width: 110px;"><?php echo __('Action');?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($User)){ ?>
                    <tr>
                        <td colspan="11" class="text-center text-muted" style="padding: 30px;">
                            <i class="fa fa-info-circle fa-2x" style="display:block; margin-bottom: 8px; color: #94a3b8;"></i>
                            <?php echo __('No users found.'); ?>
                        </td>
                    </tr>
                    <?php } else { ?>
                    <?php foreach ($User as $post):
                    $id=$post['User']['id'];?>
                    <tr>
                        <td class="text-center"><?php echo $this->Form->checkbox(false,array('value' => $post['User']['id'],'name'=>'data[User][id][]','id'=>"DeleteCheckbox$id",'class'=>'chkselect','hiddenField'=>false));?></td>
                        <td class="text-center"><?php echo $serialNo++;?></td>
                        <td style="font-weight: 600; color: #0f172a;"><?php echo h($post['User']['name']);?></td>
                        <td><span class="label label-info"><?php echo h($post['Ugroup']['name']);?></span></td>
                        <td><?php echo h(!empty($userAgencyNames[$id]) ? $userAgencyNames[$id] : '-');?></td>
                        <td class="text-right" style="font-family: monospace; font-size: 13px; font-weight: 600;">
                            <?php
                            if(isset($userWalletBalances[$id]))
                            {
                                echo 'Rs '.number_format((float)$userWalletBalances[$id], 2);
                            }
                            else
                            {
                                echo '-';
                            }
                            ?>
                        </td>
                        <td><code><?php echo h($post['User']['username']);?></code></td>                            
                        <td><?php echo h($post['User']['email']);?></td>
                        <td><?php echo h($post['User']['mobile']);?></td>
                        <td class="text-center">
                            <span class="badge-status label label-<?php if(h($post['User']['status'])=="Active")echo"success";else echo"danger";?>">
                                <i class="fa fa-circle" style="font-size:7px;"></i> <?php echo __($post['User']['status']);?>
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="btn-group">
                                <button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown" aria-expanded="false" style="padding: 5px 10px; border-radius: 6px;">
                                    <?php echo __('Action');?> <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-right" role="menu">
                                    <li><?php echo $this->Html->link('<i class="fa fa-eye"></i> '.__('View'),'#',array('onclick'=>"show_modal('$url/View/$id');",'escape'=>false));?></li>
                                    <li><?php echo $this->Html->link('<i class="fa fa-edit"></i> '.__('Edit'),'#',array('name'=>'editallfrm','onclick'=>"check_perform_sedit('$url','$id');",'escape'=>false));?></li>
                                    <?php if(($userType=='ADR' || !empty($isSuperAdminSession)) && (int)$id!==1){ ?>
                                    <li><?php echo $this->Html->link('<i class="fa fa-sign-in"></i> '.__('Login As User'),array('controller'=>'Users','action'=>'loginas',$id),array('escape'=>false,'target'=>'_blank','rel'=>'noopener','onclick'=>"return confirm('".__('Login as this user in a new tab?')."');"));?></li>
                                    <?php } ?>
                                    <li class="divider"></li>
                                    <li><?php if($id!=1){if($userType=='ADR' || ($userType=='AGY' && $post['Ugroup']['type']!="AGY")){echo $this->Html->Link('<i class="fa fa-trash text-danger"></i> <span class="text-danger">'.__('Delete').'</span>','#',array('onclick'=>"check_perform_sdelete('$id'); return false;",'escape'=>false));}}?></li>
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
