<?php
 $this->Js->JqueryEngine->jQueryObject = 'jQuery';
// Paginator options
$this->Paginator->options(array(
  'update' => '#resultDiv',
  'evalScripts' => true,
));
?>
<div id="resultDiv">
<div class="page-title"> <div class="title-env"> <h1 class="title"><?php echo __('Users');?></h1></div></div>
<div class="panel"><?php echo $this->Session->flash();?>
    <div class="panel-heading">
        <div class="btn-group">
            <?php $url=$this->Html->url(array('controller'=>'Users')); echo $this->Html->link('<span class="fa fa-plus"></span>&nbsp;'.__('Add New User'),array('controller'=>'Users','action'=>'add'),array('escape'=>false,'class'=>'btn btn-success'));?>
            <?php echo $this->Html->link('<span class="fa fa-edit"></span>&nbsp;'.__('Edit'),'javascript:void(0);',array('name'=>'editallfrm','id'=>'editallfrm','onclick'=>"check_perform_edit('$url');",'escape'=>false,'class'=>'btn btn-warning'));?>
            <?php echo $this->Html->link('<span class="fa fa-trash"></span>&nbsp;'.__('Delete'),'#',array('id'=>'btn-deleteall','onclick'=>'check_perform_delete(); return false;','escape'=>false,'class'=>'btn btn-danger'));?>
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
			    <th><?php echo $this->Paginator->sort('Ugroup.name', __('User Role'), array('direction' => 'desc'));?></th>
                            <th><?php echo __('Agency Name');?></th>
                            <th><?php echo __('Balance');?></th>
                            <th><?php echo $this->Paginator->sort('username', __('User Name'), array('direction' => 'asc'));?></th>                            
                            <th><?php echo $this->Paginator->sort('email', __('Email'), array('direction' => 'asc'));?></th>
                            <th><?php echo $this->Paginator->sort('mobile', __('Mobile'), array('direction' => 'asc'));?></th>
                            <th><?php echo $this->Paginator->sort('status', __('Status'), array('direction' => 'asc'));?></th>
                            <th><?php echo __('Action');?></th>
                        </tr>
                        <?php foreach ($User as $post):
                        $id=$post['User']['id'];?>
                        <tr>
                            <td><?php echo $this->Form->checkbox(false,array('value' => $post['User']['id'],'name'=>'data[User][id][]','id'=>"DeleteCheckbox$id",'class'=>'chkselect','hiddenField'=>false));?></td>
                            <td><?php echo $serialNo++;?></td>
			    <td><?php echo h($post['User']['name']);?></td>
                            <td><?php echo h($post['Ugroup']['name']);?></td>
                            <td><?php echo h(!empty($userAgencyNames[$id]) ? $userAgencyNames[$id] : '-');?></td>
                            <td>
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
                            <td><?php echo h($post['User']['username']);?></td>                            
                            <td><?php echo h($post['User']['email']);?></td>
                            <td><?php echo h($post['User']['mobile']);?></td>
                            <td><span class="label label-<?php if(h($post['User']['status'])=="Active")echo"success";else echo"danger";?>"><?php echo __($post['User']['status']);?></span></td>
			    <td><div class="btn-group">
			    <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
			    <?php echo __('Action');?> <span class="caret"></span>
			    </button>
			    <ul class="dropdown-menu" role="menu">
			    <li><?php echo $this->Html->link('<span class="fa fa-arrows-alt"></span>&nbsp;'.__('View'),'#',array('onclick'=>"show_modal('$url/View/$id');",'escape'=>false));?></li>
			    <li><?php echo $this->Html->link('<span class="fa fa-edit"></span>&nbsp;'.__('Edit'),'#',array('name'=>'editallfrm','onclick'=>"check_perform_sedit('$url','$id');",'escape'=>false));?></li>
			    <?php if(($userType=='ADR' || !empty($isSuperAdminSession)) && (int)$id!==1){ ?>
			    <li><?php echo $this->Html->link('<span class="fa fa-sign-in"></span>&nbsp;'.__('Login As User'),array('controller'=>'Users','action'=>'loginas',$id),array('escape'=>false,'target'=>'_blank','rel'=>'noopener','onclick'=>"return confirm('".__('Login as this user in a new tab?')."');"));?></li>
			    <?php } ?>
                            <li><?php if($id!=1){if($userType=='ADR' || ($userType=='AGY' && $post['Ugroup']['type']!="AGY")){echo $this->Html->Link('<span class="fa fa-trash"></span>&nbsp;'.__('Delete'),'#',array('onclick'=>"check_perform_sdelete('$id'); return false;",'escape'=>false));}}?></li>
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
