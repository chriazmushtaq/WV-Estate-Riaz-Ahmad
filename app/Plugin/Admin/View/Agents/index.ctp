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
        <h4 class="wv-page-title"><i class="fa fa-user-secret text-primary"></i> <?php echo __('Agents Management');?></h4>
        <small class="wv-page-subtitle"><?php echo __('Manage real estate agents, login access, wallet commissions, and linked agencies');?></small>
    </div>
    <div class="heading-elements wv-page-header-actions">
        <?php $url=$this->Html->url(array('controller'=>'Agents'));$viewUrl=$this->Html->url(array('controller'=>'Users'));?>
        <?php echo $this->Html->link('<i class="fa fa-plus"></i> '.__('Add New Agent'),array('controller'=>'Users','action'=>'add','AT'),array('escape'=>false,'class'=>'btn btn-primary'));?>
        <?php echo $this->Html->link('<i class="fa fa-edit"></i> '.__('Edit'),'javascript:void(0);',array('name'=>'editallfrm','id'=>'editallfrm','onclick'=>"check_perform_edit('$viewUrl');",'escape'=>false,'class'=>'btn btn-default'));?>
        <?php echo $this->Html->link('<i class="fa fa-trash"></i> '.__('Delete'),'javascript:void(0);',array('name'=>'deleteallfrm','id'=>'deleteallfrm','onclick'=>'check_perform_delete();','escape'=>false,'class'=>'btn btn-default btn-action-delete'));?>
        <?php echo $this->Html->link('<i class="fa fa-money"></i> '.__('Wallet'),'#',array('name'=>'walletallfrm','id'=>'walletallfrm','onclick'=>"check_perform_select('$url','wallet');",'escape'=>false,'class'=>'btn btn-default'));?>
        <?php echo $this->Html->link('<i class="fa fa-history"></i> '.__('Wallet History'),array('controller'=>'awallet_histories','action'=>'index'),array('escape'=>false,'class'=>'btn btn-default'));?>
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
                        <?php if(!empty($hasSortIdColumn)){?><th><?php echo $this->Paginator->sort('sort_id', __('Sort ID'), array('direction' => 'asc'));?></th><?php }?>
                        <th><?php echo __('Agencies');?></th>
                        <th><?php echo $this->Paginator->sort('address', __('Address'), array('direction' => 'asc'));?></th>
                        <th><?php echo $this->Paginator->sort('email', __('E-Mail'), array('direction' => 'asc'));?></th>
                        <th><?php echo $this->Paginator->sort('mobile', __('Mobile'), array('direction' => 'asc'));?></th>
                        <th class="text-center"><?php echo $this->Paginator->sort('status', __('Status'), array('direction' => 'asc'));?></th>
                        <th class="text-center"><?php echo $this->Paginator->sort('login_status', __('Login Access'), array('direction' => 'asc'));?></th>
                        <th class="text-center"><?php echo __('Frontend Visibility');?></th>
                        <th class="text-right"><?php echo $this->Paginator->sort('Awallet.balance', __('Balance'), array('direction' => 'asc'));?></th>
                        <th class="text-center" style="width: 110px;"><?php echo __('Action');?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($Agent)){ ?>
                    <tr>
                        <td colspan="<?php echo !empty($hasSortIdColumn) ? '13' : '12'; ?>" class="text-center text-muted" style="padding: 30px;">
                            <i class="fa fa-info-circle fa-2x" style="display:block; margin-bottom: 8px; color: #94a3b8;"></i>
                            <?php echo __('No agents found.'); ?>
                        </td>
                    </tr>
                    <?php } else { ?>
                    <?php foreach ($Agent as $post):
                    $id=$post['Agent']['id'];?>
                    <tr>
                        <td class="text-center"><?php echo $this->Form->checkbox(false,array('value' => $post['Agent']['id'],'name'=>'data[Agent][id][]','id'=>"DeleteCheckbox$id",'class'=>'chkselect'));?></td>
                        <td class="text-center"><?php echo $serialNo++;?></td>
                        <td style="font-weight: 600; color: #0f172a;"><?php echo h($post['Agent']['name']);?></td>
                        <?php if(!empty($hasSortIdColumn)){?><td><code><?php echo isset($post['Agent']['sort_id']) ? (int)$post['Agent']['sort_id'] : 0;?></code></td><?php }?>
                        <td><?php echo h(!empty($agentAgencyNames[$id]) ? $agentAgencyNames[$id] : '-');?></td>
                        <td><?php echo h($post['Agent']['address']);?></td>
                        <td><?php echo h($post['Agent']['email']);?></td>
                        <td><?php echo h($post['Agent']['mobile']);?></td>
                        <td class="text-center">
                            <span class="badge-status label label-<?php if($post['Agent']['status']=="Active")echo"success";else echo"danger";?>">
                                <i class="fa fa-circle" style="font-size:7px;"></i> <?php echo __($post['Agent']['status']); ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <?php
                            $loginStatusVal = isset($post['Agent']['login_status']) ? trim((string)$post['Agent']['login_status']) : 'Active';
                            if ($loginStatusVal === '') {
                                $loginStatusVal = 'Active';
                            }
                            $isLoginBlocked = (strcasecmp($loginStatusVal, 'Blocked') === 0);
                            ?>
                            <div style="display:inline-flex;align-items:center;gap:6px;flex-wrap:wrap;justify-content:center;">
                                <span class="badge-status label label-<?php echo $isLoginBlocked ? 'danger' : 'success';?>">
                                    <i class="fa fa-<?php echo $isLoginBlocked ? 'lock' : 'check-circle';?>"></i> <?php echo $isLoginBlocked ? __('Blocked') : __('Active');?>
                                </span>
                                <?php if ($isLoginBlocked): ?>
                                    <?php echo $this->Html->link(
                                        '<i class="fa fa-check"></i> '.__('Activate'),
                                        array('controller'=>'Agents','action'=>'change_login_status',$id,'Active'),
                                        array(
                                            'escape'=>false,
                                            'class'=>'btn btn-success btn-xs',
                                            'title'=>__('Activate login for this agent'),
                                            'onclick'=>"return confirm('".__('Are you sure you want to activate login for this agent?')."');"
                                        )
                                    );?>
                                <?php else: ?>
                                    <?php echo $this->Html->link(
                                        '<i class="fa fa-ban"></i> '.__('Block'),
                                        array('controller'=>'Agents','action'=>'change_login_status',$id,'Blocked'),
                                        array(
                                            'escape'=>false,
                                            'class'=>'btn btn-danger btn-xs',
                                            'title'=>__('Block login for this agent'),
                                            'onclick'=>"return confirm('".__('Are you sure you want to block login for this agent? If blocked, this user cannot login to their account.')."');"
                                        )
                                    );?>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="text-center">
                            <?php
                            $frontViewValue = isset($post['Agent']['front_view']) ? trim((string)$post['Agent']['front_view']) : '';
                            $isFrontVisible = (strcasecmp($frontViewValue,'Off') !== 0);
                            ?>
                            <span class="badge-status label label-<?php echo $isFrontVisible ? 'success' : 'danger';?>">
                                <i class="fa fa-circle" style="font-size:7px;"></i> <?php echo $isFrontVisible ? __('On') : __('Off');?>
                            </span>
                        </td>
                        <td class="text-right" style="font-family: monospace; font-size: 13px; font-weight: 600;">
                            <?php
                            $agentBalance = isset($agentWalletBalances[$id]) ? (float)$agentWalletBalances[$id] : 0.0;
                            echo $currency.$this->Number->format($agentBalance);
                            ?>
                        </td>
                        <td class="text-center">
                            <div class="btn-group">
                                <button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown" aria-expanded="false" style="padding: 5px 10px; border-radius: 6px;">
                                    <?php echo __('Action');?> <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-right" role="menu">
                                    <li><?php echo $this->Html->link('<i class="fa fa-money"></i> '.__('Wallet'),'#',array('onclick'=>"show_modal('$url/wallet/$id');",'escape'=>false));?></li>
                                    <li><?php echo $this->Html->link('<i class="fa fa-briefcase"></i> '.__('Show Commission'),array('controller'=>'awallet_histories','action'=>'index',$id),array('escape'=>false));?></li>
                                    <li><?php echo $this->Html->link('<i class="fa fa-eye"></i> '.__('View Profile'),'#',array('onclick'=>"show_modal('$viewUrl/View/$id');",'escape'=>false));?></li>
                                    <li><?php echo $this->Html->link('<i class="fa fa-edit"></i> '.__('Edit'),'javascript:void(0);',array('name'=>'editallfrm','onclick'=>"check_perform_sedit('$viewUrl','$id');",'escape'=>false));?></li>
                                    <?php if ($isLoginBlocked): ?>
                                    <li><?php echo $this->Html->link('<i class="fa fa-unlock text-success"></i> '.__('Activate Login'),array('controller'=>'Agents','action'=>'change_login_status',$id,'Active'),array('escape'=>false,'onclick'=>"return confirm('".__('Are you sure you want to activate login for this agent?')."');"));?></li>
                                    <?php else: ?>
                                    <li><?php echo $this->Html->link('<i class="fa fa-ban text-danger"></i> '.__('Block Login'),array('controller'=>'Agents','action'=>'change_login_status',$id,'Blocked'),array('escape'=>false,'onclick'=>"return confirm('".__('Are you sure you want to block login for this agent? If blocked, this user cannot login to their account.')."');"));?></li>
                                    <?php endif; ?>
                                    <?php
                                    $canLoginAsAgent = (($userType == 'ADR' || !empty($isSuperAdminSession) || $userType == 'AGY' || !empty($isAgencyAdminSession)) && (int)$id !== 1);
                                    if($canLoginAsAgent){ ?>
                                    <?php if (!$isLoginBlocked): ?>
                                    <li><?php echo $this->Html->link('<i class="fa fa-sign-in"></i> '.__('Login As Agent'),array('controller'=>'Users','action'=>'loginas',$id),array('escape'=>false,'onclick'=>"return confirm('".__('Are you sure you want to login as this agent?')."');"));?></li>
                                    <?php else: ?>
                                    <li class="disabled"><a href="javascript:void(0);" onclick="alert('<?php echo __('Login is blocked for this agent.');?>');" style="color:#aaa;"><i class="fa fa-lock text-danger"></i> <?php echo __('Login As Agent (Blocked)');?></a></li>
                                    <?php endif; ?>
                                    <?php } ?>
                                    <li class="divider"></li>
                                    <li><?php if($id!=1){if($userType=='ADR' || ($userType=='AGY' && $post['Ugroup']['type']!="AGY")){echo $this->Html->Link('<i class="fa fa-trash text-danger"></i> <span class="text-danger">'.__('Delete').'</span>','javascript:void(0);',array('onclick'=>"check_perform_sdelete('$id');",'escape'=>false));}}?></li>
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
