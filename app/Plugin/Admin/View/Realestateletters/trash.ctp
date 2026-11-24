<?php
$this->Js->JqueryEngine->jQueryObject = 'jQuery';
$this->Paginator->options(array(
    'update' => '#resultDiv',
    'evalScripts' => true,
));
$lettersProjectId = isset($lettersProjectId) ? (int)$lettersProjectId : 0;
$selectedType = isset($selectedType) ? (string)$selectedType : '';
$searchKeyword = isset($searchKeyword) ? (string)$searchKeyword : '';
$trashRows = isset($trashRows) ? (array)$trashRows : array();
$autoLetterTypeArr = isset($autoLetterTypeArr) ? (array)$autoLetterTypeArr : array();
?>
<div id="resultDiv">
    <div class="page-title">
        <div class="title-env">
            <h1 class="title"><?php echo __('Letters - Trash');?></h1>
        </div>
    </div>
    <div class="panel"><?php echo $this->Session->flash();?>
        <div class="panel-heading">
            <?php
            $dashboardUrl = array('controller' => 'Realestateletters', 'action' => 'dashboard');
            $listUrl = array('controller' => 'Realestateletters', 'action' => 'index');
            if ($lettersProjectId > 0) {
                $dashboardUrl['?'] = array('project_id' => $lettersProjectId);
                $listUrl['?'] = array('project_id' => $lettersProjectId);
            }
            echo $this->Html->link('<span class="fa fa-dashboard"></span>&nbsp;'.__('Dashboard'), $dashboardUrl, array('escape'=>false,'class'=>'btn btn-primary'));
            echo $this->Html->link('<span class="fa fa-list"></span>&nbsp;'.__('Manage Letters'), $listUrl, array('escape'=>false,'class'=>'btn btn-default'));
            ?>
            <div style="margin-top:10px;">
                <?php echo $this->Form->create(false,array('type'=>'get','url'=>array('controller'=>'Realestateletters','action'=>'trash'),'class'=>'form-inline'));?>
                <?php if(!empty($lettersCanUseAgencyFilters)){?>
                <?php echo $this->Form->input('project_id',array('type'=>'select','options'=>(array)$lettersProjectName,'empty'=>__('All Agency'),'value'=>$lettersProjectId,'label'=>false,'div'=>false,'class'=>'form-control'));?>
                <?php } else { ?>
                <?php echo $this->Form->hidden('project_id', array('value' => $lettersProjectId));?>
                <?php } ?>
                <?php echo $this->Form->input('type',array('type'=>'select','options'=>$letterTypeArr,'empty'=>__('All Letter Types'),'value'=>$selectedType,'label'=>false,'div'=>false,'class'=>'form-control'));?>
                <?php echo $this->Form->input('keyword',array('value'=>$searchKeyword,'label'=>false,'div'=>false,'class'=>'form-control','placeholder'=>__('Search by title/client/property/membership')));?>
                <button type="submit" class="btn btn-info"><?php echo __('Search');?></button>
                <?php
                $resetUrl = array('controller' => 'Realestateletters', 'action' => 'trash');
                if (!$lettersCanUseAgencyFilters || $lettersProjectId > 0) {
                    $resetUrl['?'] = array('project_id' => $lettersProjectId);
                }
                echo $this->Html->link(__('Reset'), $resetUrl, array('class'=>'btn btn-default'));
                ?>
                <?php echo $this->Form->end();?>
            </div>
        </div>
        <?php
        echo $this->Form->create('RealestateletterTrash', array(
            'url' => array('controller' => 'Realestateletters', 'action' => 'trash', '?' => array('project_id' => $lettersProjectId)),
            'class' => 'form-inline'
        ));
        ?>
        <div class="panel-body" style="padding-bottom:0;">
            <div class="form-group">
                <label><?php echo __('Bulk Action');?>&nbsp;</label>
                <?php
                $bulkOptions = array(
                    '' => __('Select'),
                    'restore' => __('Restore Selected')
                );
                if(!empty($canDeleteTrashPermanently))
                {
                    $bulkOptions['delete'] = __('Delete Selected Permanently');
                }
                echo $this->Form->select('bulk_action', $bulkOptions, array('class' => 'form-control', 'label' => false, 'div' => false));
                ?>
            </div>
            <button type="submit" class="btn btn-danger" onclick="return confirm('<?php echo addslashes(__('Apply selected action?'));?>');">
                <span class="fa fa-check"></span> <?php echo __('Apply');?>
            </button>
        </div>
        <div class="panel-body" style="padding-top:0;">
            <ul class="pagination pagination-sm" style="margin:6px 0;">
                <?php
                echo $this->Paginator->prev('&larr; '.__('Prev'), array('tag'=>'li','escape'=>false), '<a>&larr;'.__('Prev').'</a>', array('class'=>'disabled','tag'=>'li','escape'=>false));
                echo $this->Paginator->numbers(array('tag'=>'li','separator'=>null,'currentClass'=>'active','currentTag'=>'a','modulus'=>'4','first' => 2, 'last' => 2, 'ellipsis'=>'<li><a>...</a></li>'));
                echo $this->Paginator->next(__('Next').' &rarr;', array('tag'=>'li','escape'=>false), '<a>&rarr; '.__('Next').'</a>', array('class'=>'disabled','tag'=>'li','escape'=>false));
                ?>
            </ul>
            <small><?php echo $this->Paginator->counter(__d('default','Showing {:start} to {:current} of {:count} entries'));?></small>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <tr>
                    <th style="width:40px;"><input type="checkbox" id="trashSelectAll"></th>
                    <th><?php echo __('#');?></th>
                    <th><?php echo __('Ref No');?></th>
                    <th><?php echo __('Type');?></th>
                    <th><?php echo __('Client');?></th>
                    <th><?php echo __('Property');?></th>
                    <th><?php echo __('Membership');?></th>
                    <th><?php echo __('Deleted At');?></th>
                    <th><?php echo __('Action');?></th>
                </tr>
                <?php
                $pageParams = $this->Paginator->params();
                $limit = isset($pageParams['limit']) ? (int)$pageParams['limit'] : 0;
                $page = isset($pageParams['page']) ? (int)$pageParams['page'] : 1;
                $serialNo = 1*$limit*($page-1)+1;
                foreach($trashRows as $row):
                    $id = (int)$row['RealestateletterTrash']['id'];
                    $typeCode = isset($row['RealestateletterTrash']['letter_type']) ? (string)$row['RealestateletterTrash']['letter_type'] : '';
                    $typeLabel = isset($letterTypeArr[$typeCode]) ? $letterTypeArr[$typeCode] : $typeCode;
                ?>
                <tr>
                    <td><input type="checkbox" class="js-trash-item" name="trash_ids[]" value="<?php echo $id;?>"></td>
                    <td><?php echo $serialNo++;?></td>
                    <td><?php echo h($row['RealestateletterTrash']['reference_no']);?></td>
                    <td>
                        <?php echo h($typeLabel);?>
                        <?php if(isset($autoLetterTypeArr[$typeCode])){?>
                        <span class="label label-info" style="margin-left:4px;"><?php echo __('Auto');?></span>
                        <?php }?>
                    </td>
                    <td><?php echo h(isset($row['Client']['name']) ? $row['Client']['name'] : '');?></td>
                    <td><?php echo h(isset($row['Property']['name']) ? $row['Property']['name'] : '');?></td>
                    <td><?php echo h(isset($row['Deal']['invoice_no']) ? $row['Deal']['invoice_no'] : '');?></td>
                    <td>
                        <?php
                        $deletedAt = isset($row['RealestateletterTrash']['deleted_at']) ? trim((string)$row['RealestateletterTrash']['deleted_at']) : '';
                        echo ($deletedAt !== '' && $deletedAt !== '0000-00-00 00:00:00') ? $this->Time->format($sysDay.$dateSep.$sysMonth.$dateSep.$sysYear.' %H:%M', $deletedAt) : '';
                        ?>
                    </td>
                    <td>
                        <?php
                        $restoreUrl = array('controller' => 'Realestateletters', 'action' => 'restoretrash', $id);
                        if ($lettersProjectId > 0) {
                            $restoreUrl['?'] = array('project_id' => $lettersProjectId);
                        }
                        echo $this->Html->link('<span class="fa fa-undo"></span>', $restoreUrl, array('escape'=>false,'class'=>'btn btn-success btn-xs','title'=>__('Restore')));
                        if(!empty($canDeleteTrashPermanently)){
                            $deleteUrl = array('controller' => 'Realestateletters', 'action' => 'deletetrash', $id);
                            if ($lettersProjectId > 0) {
                                $deleteUrl['?'] = array('project_id' => $lettersProjectId);
                            }
                            echo ' ';
                            echo $this->Html->link('<span class="fa fa-trash"></span>', $deleteUrl, array('escape'=>false,'class'=>'btn btn-danger btn-xs','title'=>__('Delete Permanently'),'confirm'=>__('Delete permanently from trash?')));
                        }
                        ?>
                    </td>
                </tr>
                <?php endforeach;?>
                <?php if(empty($trashRows)){?>
                <tr><td colspan="9" class="text-center"><?php echo __('Trash is empty.');?></td></tr>
                <?php }?>
            </table>
        </div>
        <div class="panel-body" style="padding-top:0;">
            <ul class="pagination pagination-sm" style="margin:6px 0;">
                <?php
                echo $this->Paginator->prev('&larr; '.__('Prev'), array('tag'=>'li','escape'=>false), '<a>&larr;'.__('Prev').'</a>', array('class'=>'disabled','tag'=>'li','escape'=>false));
                echo $this->Paginator->numbers(array('tag'=>'li','separator'=>null,'currentClass'=>'active','currentTag'=>'a','modulus'=>'4','first' => 2, 'last' => 2, 'ellipsis'=>'<li><a>...</a></li>'));
                echo $this->Paginator->next(__('Next').' &rarr;', array('tag'=>'li','escape'=>false), '<a>&rarr; '.__('Next').'</a>', array('class'=>'disabled','tag'=>'li','escape'=>false));
                ?>
            </ul>
        </div>
        <?php echo $this->Form->end();?>
    </div>
</div>
<script type="text/javascript">
jQuery(function($){
    $('#trashSelectAll').on('change', function(){
        $('.js-trash-item').prop('checked', $(this).is(':checked'));
    });
});
</script>
