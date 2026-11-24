<?php
$this->Js->JqueryEngine->jQueryObject='jQuery';
$this->Paginator->options(array('update'=>'#resultDiv','evalScripts'=>true));
?>
<style>
.wv-news-admin{background:#f6f8fb;padding:14px}.wv-news-card{background:#fff;border:1px solid #dbe5f2;border-radius:8px;box-shadow:0 10px 24px rgba(15,23,42,.06);overflow:hidden}.wv-news-head{align-items:center;background:linear-gradient(135deg,#07152a,#102746 65%,#ff0a46 150%);color:#fff;display:flex;justify-content:space-between;padding:16px}.wv-news-head h3{color:#fff;font-size:20px;font-weight:900;margin:0}.wv-news-table th{background:#132945!important;color:#fff!important;font-size:11px;text-transform:uppercase}.wv-status{border-radius:999px;color:#fff;font-size:10px;font-weight:900;padding:5px 9px}.wv-status-published{background:#047857}.wv-status-draft{background:#f59e0b}.wv-status-unpublished{background:#dc2626}
</style>
<div id="resultDiv" class="wv-news-admin">
    <?php echo $this->Session->flash();?>
    <div class="wv-news-card">
        <div class="wv-news-head">
            <h3><i class="fa fa-folder-open"></i> <?php echo __('News Categories');?></h3>
            <div>
                <?php echo $this->Html->link('<i class="fa fa-plus"></i> '.__('Add Category'),array('action'=>'add'),array('escape'=>false,'class'=>'btn btn-success'));?>
                <?php echo $this->Html->link('<i class="fa fa-newspaper-o"></i> '.__('News Posts'),array('controller'=>'NewsPosts','action'=>'index'),array('escape'=>false,'class'=>'btn btn-info'));?>
            </div>
        </div>
        <?php echo $this->element('pagination');?>
        <?php $pageParams=$this->Paginator->params(); $serial=($pageParams['limit']*($pageParams['page']-1))+1;?>
        <?php echo $this->Form->create('NewsCategory',array('action'=>'deleteall'));?>
        <div class="table-responsive">
            <table class="table table-striped table-bordered wv-news-table">
                <tr>
                    <th><?php echo $this->Form->checkbox('selectAll',array('name'=>'selectAll','id'=>'selectAll','hiddenField'=>false));?></th>
                    <th>#</th>
                    <th><?php echo $this->Paginator->sort('name',__('Category'));?></th>
                    <th><?php echo $this->Paginator->sort('slug',__('Slug'));?></th>
                    <th><?php echo __('Description');?></th>
                    <th><?php echo $this->Paginator->sort('sort_order',__('Sort'));?></th>
                    <th><?php echo __('Status');?></th>
                    <th><?php echo __('Action');?></th>
                </tr>
                <?php foreach($categories as $row): $id=(int)$row['NewsCategory']['id']; $status=$row['NewsCategory']['status']; ?>
                <tr>
                    <td><?php echo $this->Form->checkbox('id.',array('value'=>$id,'class'=>'chkselect','hiddenField'=>false));?></td>
                    <td><?php echo $serial++;?></td>
                    <td><strong><?php echo h($row['NewsCategory']['name']);?></strong></td>
                    <td><?php echo h($row['NewsCategory']['slug']);?></td>
                    <td><?php echo h($row['NewsCategory']['description']);?></td>
                    <td><?php echo (int)$row['NewsCategory']['sort_order'];?></td>
                    <td><span class="wv-status wv-status-<?php echo strtolower($status);?>"><?php echo h($status);?></span></td>
                    <td>
                        <div class="btn-group">
                            <?php echo $this->Html->link('<i class="fa fa-edit"></i> '.__('Edit'),array('action'=>'edit',$id),array('escape'=>false,'class'=>'btn btn-xs btn-warning'));?>
                            <?php echo $this->Html->link(__('Publish'),array('action'=>'status',$id,'Published'),array('class'=>'btn btn-xs btn-success'));?>
                            <?php echo $this->Html->link(__('Unpublish'),array('action'=>'status',$id,'Unpublished'),array('class'=>'btn btn-xs btn-danger'));?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
        <div class="panel-footer">
            <button type="submit" class="btn btn-danger" onclick="return confirm('<?php echo __('Delete selected categories?');?>');"><i class="fa fa-trash"></i> <?php echo __('Delete Selected');?></button>
            <?php echo $this->element('pagination');?>
        </div>
        <?php echo $this->Form->end();?>
    </div>
</div>
