<?php
 $this->Js->JqueryEngine->jQueryObject = 'jQuery';
// Paginator options
$this->Paginator->options(array(
  'update' => '#resultDiv',
  'evalScripts' => true,
));
?>
<div id="resultDiv">
<div class="page-title"> <div class="title-env"> <h1 class="title"><?php echo __('Email Templates');?></h1></div></div>
<div class="panel"><?php echo $this->Session->flash();?>
    <?php if(empty($isGlobalTemplateAdmin)){ ?>
    <div class="panel-body">
        <div class="alert alert-info"><?php echo __('These are your own email templates only. They are isolated by login and used in your Leads Email send popup.');?></div>
    </div>
    <?php } ?>
    <div class="panel-heading">
        <div class="btn-group">
            <?php $url=$this->Html->url(array('controller'=>'Emailtemplates'));?>
	    <?php echo $this->Html->link('<span class="fa fa-plus"></span>&nbsp;'.(empty($isGlobalTemplateAdmin) ? __('Add My Email Template') : __('Add New Email Template')),array('controller'=>'Emailtemplates','action'=>'add'),array('escape'=>false,'class'=>'btn btn-success'));?>
            <?php echo $this->Html->link('<span class="fa fa-edit"></span>&nbsp;'.__('Edit'),'#',array('name'=>'editallfrm','id'=>'editallfrm','onclick'=>"check_perform_edit('$url');",'escape'=>false,'class'=>'btn btn-warning'));?>
            <?php echo $this->Html->link('<span class="fa fa-trash"></span>&nbsp;'.__('Delete'),'#',array('name'=>'deleteallfrm','id'=>'deleteallfrm','onclick'=>'check_perform_delete();','escape'=>false,'class'=>'btn btn-danger'));?>
        </div>
    </div>
        <?php echo $this->element('pagination');
        $pageParams = $this->Paginator->params();
        $limit = $pageParams['limit'];
        $page = $pageParams['page'];
        $serialNo = 1*$limit*($page-1)+1;?>
        <?php echo $this->Form->create(array('name'=>'deleteallfrm','action' => 'deleteall'));?>
<?php echo $this->Session->flash();?>
    <div class="panel-body">
			</div>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <tr>
                            <th><?php echo $this->Form->checkbox('checkbox', array('value'=>'deleteall','name'=>'selectAll','label'=>false,'id'=>'selectAll','hiddenField'=>false));?></th>
                            <th><?php echo $this->Paginator->sort('id', __('#'), array('direction' => 'desc'));?></th>
                            <th><?php echo $this->Paginator->sort('name', __('Name'), array('direction' => 'asc'));?></th>
			    <th><?php echo __('Type Code');?></th>
			    <th><?php echo __('Template Category');?></th>
			    <th><?php echo $this->Paginator->sort('status', __('Status'), array('direction' => 'asc'));?></th>
                            <th><?php echo __('Action');?></th>
                        </tr>
                        <?php foreach ($Emailtemplate as $post):
                        $id=$post['Emailtemplate']['id'];
                        $emailTypeCode = strtoupper(trim((string)$post['Emailtemplate']['type']));
                        $emailTypeLabel = ($emailTypeCode==='') ? __('N/A') : $emailTypeCode;
                        if($emailTypeCode==='')
                        {
                            $emailCategory = __('Existing Custom');
                        }
                        elseif(in_array($emailTypeCode,array('LED','LDE'),true))
                        {
                            $emailCategory = __('Lead Default');
                        }
                        else
                        {
                            $emailCategory = __('System / Auto');
                        }
                        ?>
                        <tr>
                            <td><?php echo $this->Form->checkbox(false,array('value' => $post['Emailtemplate']['id'],'name'=>'data[Emailtemplate][id][]','id'=>"DeleteCheckbox$id",'class'=>'chkselect'));?></td>
                            <td><?php echo $serialNo++;?></td>
                            <td><?php echo h($post['Emailtemplate']['name']);?></td>
			    <td><?php echo h($emailTypeLabel);?></td>
			    <td><?php echo h($emailCategory);?></td>
			    <td><span class="label label-<?php if($post['Emailtemplate']['status']=="Published")echo"success";else echo"danger";?>"><?php echo __($post['Emailtemplate']['status']); ?></span></td>
                            <td><div class="btn-group">
			    <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
			    <?php echo __('Action');?>&nbsp;<span class="caret"></span>
			    </button>
			    <ul class="dropdown-menu" role="menu">
			    <li><?php echo $this->Html->link('<span class="fa fa-arrows-alt"></span>&nbsp;'.__('View'),'#',array('onclick'=>"show_modal('$url/View/$id');",'escape'=>false));?></li>
			    <li><?php echo $this->Html->link('<span class="fa fa-edit"></span>&nbsp;'.__('Edit'),'#',array('name'=>'editallfrm','onclick'=>"check_perform_sedit('$url','$id');",'escape'=>false));?></li>
                            <?php if($post['Emailtemplate']['type']==null){?><li><?php echo $this->Html->Link('<span class="fa fa-trash"></span>&nbsp;'.__('Delete'),'#',array('onclick'=>"check_perform_sdelete('$id');",'escape'=>false));?></li><?php }?>
			    </ul>
			  </div></td>
                        </tr>
                        <?php endforeach;?>
                        <?php unset($post);?>
                        </table>
                </div>
<?php echo $this->Form->end();?>
<?php echo $this->element('pagination');?>
</div>
