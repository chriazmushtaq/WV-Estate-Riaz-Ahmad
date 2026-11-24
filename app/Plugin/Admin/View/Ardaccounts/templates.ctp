<?php
$isAdr = ($userType == 'ADR');
?>
<div id="resultDiv">
    <div class="page-title">
        <div class="title-env">
            <h1 class="title"><?php echo __('Templates');?></h1>
        </div>
    </div>
    <div class="panel">
        <div class="panel-body">
            <?php echo $this->Session->flash();?>
            <div class="btn-group" style="margin-bottom:12px;">
                <?php echo $this->Html->link('<span class="fa fa-arrow-left"></span> '.__('Back To Settings'),array('controller'=>'Ardaccounts','action'=>'settings',$currentProjectId,'?'=>array('tab'=>'templates')),array('escape'=>false,'class'=>'btn btn-default'));?>
                <?php echo $this->Html->link('<span class="fa fa-plus"></span> '.__('Add Template'),array('controller'=>'Ardaccounts','action'=>'templateadd',$currentProjectId),array('escape'=>false,'class'=>'btn btn-success'));?>
            </div>
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <tr>
                        <th>#</th>
                        <?php if($isAdr){?><th><?php echo __('Agency');?></th><?php }?>
                        <th><?php echo __('Type');?></th>
                        <th><?php echo __('Name');?></th>
                        <th><?php echo __('Active');?></th>
                        <th><?php echo __('Action');?></th>
                    </tr>
                    <?php if(empty($rows)){?>
                        <tr>
                            <td colspan="<?php echo $isAdr ? 6 : 5;?>" class="text-center"><?php echo __('No templates found.');?></td>
                        </tr>
                    <?php } else { $serialNo = 1; foreach($rows as $row): $rid = (int)$row['Ardtemplate']['id']; ?>
                        <tr>
                            <td><?php echo $serialNo++;?></td>
                            <?php if($isAdr){?><td><?php echo h(!empty($row['Agency']['name']) ? $row['Agency']['name'] : '-');?></td><?php }?>
                            <td><?php echo h($row['Ardtemplate']['template_type']);?></td>
                            <td><?php echo h($row['Ardtemplate']['template_name']);?></td>
                            <td><?php echo ((int)$row['Ardtemplate']['is_active'] === 1 ? __('Yes') : __('No'));?></td>
                            <td>
                                <?php echo $this->Html->link('<span class="fa fa-eye"></span> '.__('View'),array('controller'=>'Ardaccounts','action'=>'templateview',$rid,$currentProjectId),array('escape'=>false,'class'=>'btn btn-xs btn-info','target'=>'_blank'));?>
                                <?php echo $this->Html->link('<span class="fa fa-edit"></span> '.__('Edit'),array('controller'=>'Ardaccounts','action'=>'templateedit',$rid,$currentProjectId),array('escape'=>false,'class'=>'btn btn-xs btn-warning'));?>
                                <?php echo $this->Html->link('<span class="fa fa-trash"></span> '.__('Delete'),array('controller'=>'Ardaccounts','action'=>'deletetemplate',$rid,$currentProjectId),array('escape'=>false,'class'=>'btn btn-xs btn-danger','onclick'=>"return confirm('".__('Are you sure?')."');"));?>
                            </td>
                        </tr>
                    <?php endforeach; } ?>
                </table>
            </div>
        </div>
    </div>
</div>
