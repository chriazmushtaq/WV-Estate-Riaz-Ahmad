<?php
$this->Js->JqueryEngine->jQueryObject='jQuery';
$this->Paginator->options(array('update'=>'#resultDiv','evalScripts'=>true));
?>
<style>
.wv-news-admin{background:#f6f8fb;padding:14px}.wv-news-card{background:#fff;border:1px solid #dbe5f2;border-radius:8px;box-shadow:0 10px 24px rgba(15,23,42,.06);overflow:hidden}.wv-news-head{align-items:center;background:linear-gradient(135deg,#07152a,#102746 65%,#ff0a46 150%);color:#fff;display:flex;justify-content:space-between;padding:16px}.wv-news-head h3{color:#fff;font-size:20px;font-weight:900;margin:0}.wv-news-table th{background:#132945!important;color:#fff!important;font-size:11px;text-transform:uppercase}.wv-thumb{background:#e5edf7;border-radius:7px;height:54px;object-fit:cover;width:82px}.wv-status{border-radius:999px;color:#fff;font-size:10px;font-weight:900;padding:5px 9px}.wv-status-published{background:#047857}.wv-status-draft{background:#f59e0b}.wv-status-unpublished{background:#dc2626}
</style>
<div id="resultDiv" class="wv-news-admin">
    <?php echo $this->Session->flash();?>
    <div class="wv-news-card">
        <div class="wv-news-head">
            <h3><i class="fa fa-newspaper-o"></i> <?php echo __('News / Blog Posts');?></h3>
            <div>
                <?php echo $this->Html->link('<i class="fa fa-plus"></i> '.__('Add New Post'),array('action'=>'add'),array('escape'=>false,'class'=>'btn btn-success'));?>
                <?php echo $this->Html->link('<i class="fa fa-folder-open"></i> '.__('Categories'),array('controller'=>'NewsCategories','action'=>'index'),array('escape'=>false,'class'=>'btn btn-info'));?>
                <?php echo $this->Html->link('<i class="fa fa-globe"></i> '.__('Frontend'),array('plugin'=>false,'admin'=>false,'controller'=>'News','action'=>'index'),array('escape'=>false,'class'=>'btn btn-default','target'=>'_blank'));?>
            </div>
        </div>
        <?php echo $this->element('pagination');?>
        <?php $pageParams=$this->Paginator->params(); $serial=($pageParams['limit']*($pageParams['page']-1))+1;?>
        <?php echo $this->Form->create('NewsPost',array('action'=>'deleteall'));?>
        <div class="table-responsive">
            <table class="table table-striped table-bordered wv-news-table">
                <tr>
                    <th><?php echo $this->Form->checkbox('selectAll',array('name'=>'selectAll','id'=>'selectAll','hiddenField'=>false));?></th>
                    <th>#</th>
                    <th><?php echo __('Thumb');?></th>
                    <th><?php echo $this->Paginator->sort('title',__('Title'));?></th>
                    <th><?php echo __('Category');?></th>
                    <th><?php echo __('Status');?></th>
                    <th><?php echo __('Featured');?></th>
                    <th><?php echo __('Views');?></th>
                    <th><?php echo __('Published');?></th>
                    <th><?php echo __('Action');?></th>
                </tr>
                <?php foreach($posts as $row): $id=(int)$row['NewsPost']['id']; $status=$row['NewsPost']['status']; ?>
                <tr>
                    <td><?php echo $this->Form->checkbox('id.',array('value'=>$id,'class'=>'chkselect','hiddenField'=>false));?></td>
                    <td><?php echo $serial++;?></td>
                    <td><?php if(!empty($row['NewsPost']['thumbnail'])){ echo $this->Html->image('news/'.$row['NewsPost']['thumbnail'],array('class'=>'wv-thumb')); } ?></td>
                    <td><strong><?php echo h($row['NewsPost']['title']);?></strong><br><small><?php echo h($row['NewsPost']['slug']);?></small></td>
                    <td><?php echo h(!empty($row['NewsCategory']['name']) ? $row['NewsCategory']['name'] : '-');?></td>
                    <td><span class="wv-status wv-status-<?php echo strtolower($status);?>"><?php echo h($status);?></span></td>
                    <td><?php echo !empty($row['NewsPost']['is_featured']) ? '<span class="label label-info">'.__('Yes').'</span>' : '-';?></td>
                    <td><?php echo (int)$row['NewsPost']['views'];?></td>
                    <td><?php echo h($row['NewsPost']['published_at']);?></td>
                    <td>
                        <div class="btn-group">
                            <?php echo $this->Html->link('<i class="fa fa-eye"></i>',array('plugin'=>false,'admin'=>false,'controller'=>'News','action'=>'view',$row['NewsPost']['slug']),array('escape'=>false,'class'=>'btn btn-xs btn-default','target'=>'_blank'));?>
                            <?php echo $this->Html->link('<i class="fa fa-edit"></i> '.__('Edit'),array('action'=>'edit',$id),array('escape'=>false,'class'=>'btn btn-xs btn-warning'));?>
                            <?php echo $this->Html->link(__('Publish'),array('action'=>'status',$id,'Published'),array('class'=>'btn btn-xs btn-success'));?>
                            <?php echo $this->Html->link(__('Draft'),array('action'=>'status',$id,'Draft'),array('class'=>'btn btn-xs btn-info'));?>
                            <?php echo $this->Html->link(__('Unpublish'),array('action'=>'status',$id,'Unpublished'),array('class'=>'btn btn-xs btn-danger'));?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
        <div class="panel-footer">
            <button type="submit" class="btn btn-danger" onclick="return confirm('<?php echo __('Delete selected posts?');?>');"><i class="fa fa-trash"></i> <?php echo __('Delete Selected');?></button>
            <?php echo $this->element('pagination');?>
        </div>
        <?php echo $this->Form->end();?>
    </div>
</div>
