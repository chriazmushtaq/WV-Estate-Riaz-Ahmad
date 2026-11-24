<?php
$this->Js->JqueryEngine->jQueryObject = 'jQuery';
$this->Paginator->options(array('update' => '#resultDiv','evalScripts' => true));
$editHiddenId = !empty($this->request->data['Ardsite']['id']) ? (int)$this->request->data['Ardsite']['id'] : (!empty($editIdFromUrl) ? (int)$editIdFromUrl : 0);
$isEdit = ($editHiddenId > 0);
$basePrefix = trim((string)$this->request->base, '/');
if($basePrefix !== '')
{
    $basePrefix = '/' . $basePrefix;
}
$stockStoreBaseUrl = $basePrefix . '/admin/Ardaccounts/stockstore/' . (int)$currentProjectId;
$scheme = (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off') ? 'https' : 'http';
$host = isset($_SERVER['HTTP_HOST']) ? (string)$_SERVER['HTTP_HOST'] : '';
$stockStoreAbsoluteUrl = ($host !== '') ? ($scheme . '://' . $host . $stockStoreBaseUrl) : $stockStoreBaseUrl;
?>
<div id="resultDiv">
<div class="page-title"><div class="title-env"><h1 class="title"><?php echo __('Construction Stock Stores');?></h1></div></div>
<div class="panel"><?php echo $this->Session->flash();?>
    <div class="panel-heading">
        <div class="btn-group">
            <?php echo $this->Html->link('<span class="fa fa-home"></span>&nbsp;'.__('ARD Dashboard'),array('controller'=>'Ardaccounts','action'=>'dashboard',$currentProjectId),array('escape'=>false,'class'=>'btn btn-default'));?>
            <?php echo $this->Html->link('<span class="fa fa-cubes"></span>&nbsp;'.__('Stock'),array('controller'=>'Ardaccounts','action'=>'stock',$currentProjectId),array('escape'=>false,'class'=>'btn btn-warning'));?>
            <button type="button" class="btn btn-success" id="openSiteModal"><span class="fa fa-plus"></span> <?php echo $isEdit ? __('Edit Stock Store') : __('Add Stock Store');?></button>
        </div>
    </div>
    <div class="panel-body">
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <tr>
                    <th>#</th>
                    <th><?php echo __('Stock Store Name');?></th>
                    <th><?php echo __('Code');?></th>
                    <th><?php echo __('Phone');?></th>
                    <th><?php echo __('Address');?></th>
                    <th><?php echo __('Status');?></th>
                    <th><?php echo __('Action');?></th>
                </tr>
                <?php
                $pageParams = $this->Paginator->params();
                $limit = $pageParams['limit'];
                $page = $pageParams['page'];
                $serialNo = 1*$limit*($page-1)+1;
                foreach($rows as $row):
                    $id=(int)$row['Ardsite']['id'];
                    $rowProjectId = (int)$row['Ardsite']['project_id'];
                ?>
                <tr>
                    <td><?php echo $serialNo++;?></td>
                    <td><?php echo h($row['Ardsite']['site_name']);?></td>
                    <td><?php echo h($row['Ardsite']['site_code']);?></td>
                    <td><?php echo h($row['Ardsite']['phone']);?></td>
                    <td><?php echo h($row['Ardsite']['address']);?></td>
                    <td><span class="label label-<?php echo (strtolower($row['Ardsite']['status'])==='active'?'success':'danger');?>"><?php echo h(ucfirst($row['Ardsite']['status']));?></span></td>
                    <td>
                        <?php
                        $rowStockStoreBaseUrl = $basePrefix . '/admin/Ardaccounts/stockstore/' . $rowProjectId;
                        $rowStockStoreAbsoluteUrl = ($host !== '') ? ($scheme . '://' . $host . $rowStockStoreBaseUrl) : $rowStockStoreBaseUrl;
                        $editUrl = $rowStockStoreAbsoluteUrl . '?edit=' . $id;
                        echo '<a href="'.h($editUrl).'" class="btn btn-xs btn-warning"><span class="fa fa-edit"></span> '.__('Edit').'</a>';
                        echo ' ';
                        echo $this->Html->link('<span class="fa fa-trash"></span> '.__('Delete'),array('controller'=>'Ardaccounts','action'=>'deletestockstore',$id,$rowProjectId),array('escape'=>false,'class'=>'btn btn-xs btn-danger','onclick'=>"return confirm('".__('Are you sure?')."');"));
                        ?>
                    </td>
                </tr>
                <?php endforeach;?>
            </table>
        </div>
        <?php echo $this->element('pagination');?>
    </div>
</div>

<div class="modal fade" id="siteEntryModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><?php echo $isEdit ? __('Edit Stock Store') : __('Add Stock Store');?></h4>
            </div>
            <div class="modal-body">
                <?php echo $this->Form->create('Ardsite',array('url'=>$stockStoreAbsoluteUrl,'class'=>'form-horizontal'));?>
                <?php if($userType!='ADR'){echo $this->Form->hidden('project_id',array('value'=>$agencyId));}?>
                <?php if($isEdit){echo $this->Form->hidden('id',array('value'=>$editHiddenId));}?>
                <?php if($userType=='ADR'){?>
                <div class="form-group">
                    <label class="col-sm-2 control-label"><?php echo __('Agency');?></label>
                    <div class="col-sm-4"><?php echo $this->Form->select('project_id',$projectName,array('empty'=>__('Please Select'),'class'=>'form-control','label'=>false,'div'=>false));?></div>
                    <label class="col-sm-2 control-label"><?php echo __('Status');?></label>
                    <div class="col-sm-4"><?php echo $this->Form->select('status',array('active'=>__('Active'),'inactive'=>__('Inactive')),array('empty'=>false,'class'=>'form-control','label'=>false,'div'=>false));?></div>
                </div>
                <?php } else { ?>
                <div class="form-group">
                    <label class="col-sm-2 control-label"><?php echo __('Status');?></label>
                    <div class="col-sm-4"><?php echo $this->Form->select('status',array('active'=>__('Active'),'inactive'=>__('Inactive')),array('empty'=>false,'class'=>'form-control','label'=>false,'div'=>false));?></div>
                </div>
                <?php } ?>
                <div class="form-group">
                    <label class="col-sm-2 control-label"><?php echo __('Stock Store Name');?></label>
                    <div class="col-sm-4"><?php echo $this->Form->input('site_name',array('label'=>false,'div'=>false,'class'=>'form-control','placeholder'=>__('Stock Store Name')));?></div>
                    <label class="col-sm-2 control-label"><?php echo __('Store Code');?></label>
                    <div class="col-sm-4"><?php echo $this->Form->input('site_code',array('label'=>false,'div'=>false,'class'=>'form-control','placeholder'=>__('Store Code')));?></div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label"><?php echo __('Phone');?></label>
                    <div class="col-sm-4"><?php echo $this->Form->input('phone',array('label'=>false,'div'=>false,'class'=>'form-control','placeholder'=>__('Phone')));?></div>
                    <label class="col-sm-2 control-label"><?php echo __('Address');?></label>
                    <div class="col-sm-4"><?php echo $this->Form->input('address',array('type'=>'text','label'=>false,'div'=>false,'class'=>'form-control','placeholder'=>__('Address')));?></div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label"><?php echo __('Notes');?></label>
                    <div class="col-sm-10"><?php echo $this->Form->input('notes',array('label'=>false,'div'=>false,'class'=>'form-control','placeholder'=>__('Notes')));?></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success"><span class="fa fa-save"></span> <?php echo ($isEdit?__('Update Stock Store'):__('Save Stock Store'));?></button>
                <?php if($isEdit){echo '<a href="'.h($stockStoreAbsoluteUrl).'" class="btn btn-danger"><span class="fa fa-close"></span> '.__('Cancel Edit').'</a>';}?>
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Close');?></button>
                <?php echo $this->Form->end();?>
            </div>
        </div>
    </div>
</div>
</div>

<script type="text/javascript">
jQuery(function($){
    var $siteModal = $('#siteEntryModal');
    $('#openSiteModal').on('click', function(e){
        e.preventDefault();
        $siteModal.modal('show');
    });
    <?php if($isEdit){?>
    $siteModal.modal('show');
    <?php }?>
});
</script>
