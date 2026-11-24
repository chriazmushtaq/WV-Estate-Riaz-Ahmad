<?php
 $this->Js->JqueryEngine->jQueryObject = 'jQuery';
// Paginator options
$this->Paginator->options(array(
  'update' => '#resultDiv',
  'evalScripts' => true,
));
?>
<div id="resultDiv">
<div class="page-title"> <div class="title-env"> <h1 class="title"><?php echo __('Agents');?></h1></div></div>
<div class="panel"><?php echo $this->Session->flash();?>
    <div class="panel-heading">
        <div class="btn-group">
            <?php $url=$this->Html->url(array('controller'=>'Agents'));$viewUrl=$this->Html->url(array('controller'=>'Users'));?>
	    <?php echo $this->Html->link('<span class="fa fa-plus"></span>&nbsp;'.__('Add New Agent'),array('controller'=>'Users','action'=>'add','AT'),array('escape'=>false,'class'=>'btn btn-success'));?>
            <?php echo $this->Html->link('<span class="fa fa-edit"></span>&nbsp;'.__('Edit'),'javascript:void(0);',array('name'=>'editallfrm','id'=>'editallfrm','onclick'=>"check_perform_edit('$viewUrl');",'escape'=>false,'class'=>'btn btn-warning'));?>
            <?php echo $this->Html->link('<span class="fa fa-trash"></span>&nbsp;'.__('Delete'),'javascript:void(0);',array('name'=>'deleteallfrm','id'=>'deleteallfrm','onclick'=>'check_perform_delete();','escape'=>false,'class'=>'btn btn-danger'));?>
	    <?php echo $this->Html->link('<span class="fa fa-shopping-cart"></span>&nbsp;'.__('Wallet'),'#',array('name'=>'walletallfrm','id'=>'walletallfrm','onclick'=>"check_perform_select('$url','wallet');",'escape'=>false,'class'=>'btn btn-info'));?>
            <?php echo $this->Html->link('<span class="fa fa-briefcase"></span>&nbsp;'.__('Wallet History'),array('controller'=>'awallet_histories','action'=>'index'),array('escape'=>false,'class'=>'btn btn-primary'));?>
        </div>
    </div>
        <?php echo $this->element('pagination');
        $pageParams = $this->Paginator->params();
        $limit = $pageParams['limit'];
        $page = $pageParams['page'];
        $serialNo = 1*$limit*($page-1)+1;?>
        <?php echo $this->Form->create(array('name'=>'deleteallfrm','action' => 'deleteall'));?>
<?php echo $this->Session->flash();?>
<div id="printable_content">
<?php echo $this->Html->css('print');?>
    <div class="panel-body">
			</div>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <tr>
                            <th><?php echo $this->Form->checkbox('checkbox', array('value'=>'deleteall','name'=>'selectAll','label'=>false,'id'=>'selectAll','hiddenField'=>false));?></th>
                            <th><?php echo $this->Paginator->sort('id', __('#'), array('direction' => 'desc'));?></th>
                            <th><?php echo $this->Paginator->sort('name', __('Name'), array('direction' => 'asc'));?></th>
			    <?php if(!empty($hasSortIdColumn)){?><th><?php echo $this->Paginator->sort('sort_id', __('Sort ID'), array('direction' => 'asc'));?></th><?php }?>
			    <th><?php echo __('Agencies');?></th>
			    <th><?php echo $this->Paginator->sort('address', __('Address'), array('direction' => 'asc'));?></th>
			    <th><?php echo $this->Paginator->sort('email', __('E-Mail'), array('direction' => 'asc'));?></th>
			    <th><?php echo $this->Paginator->sort('mobile', __('Mobile'), array('direction' => 'asc'));?></th>
			    <th><?php echo $this->Paginator->sort('status', __('Status'), array('direction' => 'asc'));?></th>
			    <th><?php echo $this->Paginator->sort('login_status', __('Login Status'), array('direction' => 'asc'));?></th>
			    <th><?php echo __('Visible on Frontend');?></th>
			    <th><?php echo $this->Paginator->sort('Awallet.balance', __('Balance'), array('direction' => 'asc'));?></th>
                            <th><?php echo __('Action');?></th>
                        </tr>
                        <?php foreach ($Agent as $post):
                        $id=$post['Agent']['id'];?>
                        <tr>
                            <td><?php echo $this->Form->checkbox(false,array('value' => $post['Agent']['id'],'name'=>'data[Agent][id][]','id'=>"DeleteCheckbox$id",'class'=>'chkselect'));?></td>
                            <td><?php echo $serialNo++;?></td>
                            <td><?php echo h($post['Agent']['name']);?></td>
			    <?php if(!empty($hasSortIdColumn)){?><td><?php echo isset($post['Agent']['sort_id']) ? (int)$post['Agent']['sort_id'] : 0;?></td><?php }?>
			    <td><?php echo h(!empty($agentAgencyNames[$id]) ? $agentAgencyNames[$id] : '-');?></td>
			    <td><?php echo h($post['Agent']['address']);?></td>
			    <td><?php echo h($post['Agent']['email']);?></td>
			    <td><?php echo h($post['Agent']['mobile']);?></td>
			    <td><span class="label label-<?php if($post['Agent']['status']=="Active")echo"success";else echo"danger";?>"><?php echo __($post['Agent']['status']); ?></span></td>
			    <td>
				<?php
				$loginStatusVal = isset($post['Agent']['login_status']) ? trim((string)$post['Agent']['login_status']) : 'Active';
				if ($loginStatusVal === '') {
				    $loginStatusVal = 'Active';
				}
				$isLoginBlocked = (strcasecmp($loginStatusVal, 'Blocked') === 0);
				?>
				<div style="display:inline-flex;align-items:center;gap:6px;flex-wrap:wrap;">
				    <span class="label label-<?php echo $isLoginBlocked ? 'danger' : 'success';?>">
					<i class="fa fa-<?php echo $isLoginBlocked ? 'lock' : 'unlock';?>"></i>&nbsp;<?php echo $isLoginBlocked ? __('Blocked') : __('Active');?>
				    </span>
				    <?php if ($isLoginBlocked): ?>
					<?php echo $this->Html->link(
					    '<span class="fa fa-check"></span>&nbsp;'.__('Active Login'),
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
					    '<span class="fa fa-ban"></span>&nbsp;'.__('Block Login'),
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
			    <td>
				<?php
				$frontViewValue = isset($post['Agent']['front_view']) ? trim((string)$post['Agent']['front_view']) : '';
				$isFrontVisible = (strcasecmp($frontViewValue,'Off') !== 0);
				?>
				<span class="label label-<?php echo $isFrontVisible ? 'success' : 'danger';?>"><?php echo $isFrontVisible ? __('On') : __('Off');?></span>
			    </td>
			    <td>
				<?php
				$agentBalance = isset($agentWalletBalances[$id]) ? (float)$agentWalletBalances[$id] : 0.0;
				echo $currency.$this->Number->format($agentBalance);
				?>
			    </td>
			    <td><div class="btn-group">
			    <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
			    <?php echo __('Action').' ';?><span class="caret"></span>
			    </button>
			    <ul class="dropdown-menu" role="menu">
			    <li><?php echo $this->Html->link('<span class="fa fa-shopping-cart"></span>&nbsp;'.__('Wallet'),'#',array('onclick'=>"show_modal('$url/wallet/$id');",'escape'=>false));?></li>
			    <li><?php echo $this->Html->link('<span class="fa fa-briefcase"></span>&nbsp;'.__('Show Commission'),array('controller'=>'awallet_histories','action'=>'index',$id),array('escape'=>false));?></li>
			    <li><?php echo $this->Html->link('<span class="fa fa-arrows-alt"></span>&nbsp;'.__('View'),'#',array('onclick'=>"show_modal('$viewUrl/View/$id');",'escape'=>false));?></li>
			    <li><?php echo $this->Html->link('<span class="fa fa-edit"></span>&nbsp;'.__('Edit'),'javascript:void(0);',array('name'=>'editallfrm','onclick'=>"check_perform_sedit('$viewUrl','$id');",'escape'=>false));?></li>
			    <?php if ($isLoginBlocked): ?>
			    <li><?php echo $this->Html->link('<span class="fa fa-unlock text-success"></span>&nbsp;'.__('Active Login'),array('controller'=>'Agents','action'=>'change_login_status',$id,'Active'),array('escape'=>false,'onclick'=>"return confirm('".__('Are you sure you want to activate login for this agent?')."');"));?></li>
			    <?php else: ?>
			    <li><?php echo $this->Html->link('<span class="fa fa-ban text-danger"></span>&nbsp;'.__('Block Login'),array('controller'=>'Agents','action'=>'change_login_status',$id,'Blocked'),array('escape'=>false,'onclick'=>"return confirm('".__('Are you sure you want to block login for this agent? If blocked, this user cannot login to their account.')."');"));?></li>
			    <?php endif; ?>
			    <?php
			    $canLoginAsAgent = (($userType == 'ADR' || !empty($isSuperAdminSession) || $userType == 'AGY' || !empty($isAgencyAdminSession)) && (int)$id !== 1);
			    if($canLoginAsAgent){ ?>
			    <?php if (!$isLoginBlocked): ?>
			    <li><?php echo $this->Html->link('<span class="fa fa-sign-in"></span>&nbsp;'.__('Login As Agent'),array('controller'=>'Users','action'=>'loginas',$id),array('escape'=>false,'onclick'=>"return confirm('".__('Are you sure you want to login as this agent?')."');"));?></li>
			    <?php else: ?>
			    <li class="disabled"><a href="javascript:void(0);" onclick="alert('<?php echo __('Login is blocked for this agent.');?>');" style="color:#aaa;"><span class="fa fa-lock text-danger"></span>&nbsp;<?php echo __('Login As Agent (Blocked)');?></a></li>
			    <?php endif; ?>
			    <?php } ?>
			    <li><?php if($id!=1){if($userType=='ADR' || ($userType=='AGY' && $post['Ugroup']['type']!="AGY")){echo $this->Html->Link('<span class="fa fa-trash"></span>&nbsp;'.__('Delete'),'javascript:void(0);',array('onclick'=>"check_perform_sdelete('$id');",'escape'=>false));}}?></li>
			    </ul>
			  </div></td>
                        </tr>
                        <?php endforeach;?>
                        <?php unset($post);?>
                        </table>
                </div>
        </div>
<?php echo $this->Form->end();?>
<?php echo $this->element('pagination');?>
</div>
