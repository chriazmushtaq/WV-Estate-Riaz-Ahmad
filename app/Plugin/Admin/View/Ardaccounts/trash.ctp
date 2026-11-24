<?php
$this->Js->JqueryEngine->jQueryObject = 'jQuery';
$this->Paginator->options(array('update' => '#resultDiv', 'evalScripts' => true));
?>
<style type="text/css">
.ard-trash-wrap .panel-body{background:#f7f9fc;}
.ard-trash-empty{padding:26px 10px;color:#7e8ea6;text-align:center;font-size:12px;border:1px dashed #d0dceb;border-radius:10px;background:#fafcff;}
.ard-trash-table th,.ard-trash-table td{vertical-align:middle !important;}
.ard-trash-record-title{color:#1f3556;font-weight:700;}
.ard-trash-record-meta{display:block;margin-top:4px;color:#667085;font-size:12px;line-height:1.45;}
.ard-trash-record-details{display:flex;flex-wrap:wrap;gap:4px 12px;margin-top:7px;padding-top:7px;border-top:1px solid #e5ebf3;}
.ard-trash-record-detail{color:#344054;font-size:11px;line-height:1.4;min-width:145px;}
.ard-trash-record-detail strong{color:#1f3556;font-weight:700;}
.ard-trash-payment-badge{display:inline-block;margin-top:5px;padding:3px 8px;border-radius:999px;background:#e8f1ff;color:#1d4ed8;font-size:11px;font-weight:700;}
.ard-trash-actions{white-space:nowrap;}
.ard-trash-actions .btn{margin:1px 2px 1px 0;}
.ard-trash-icon-btn{width:26px;height:24px;padding:0 !important;display:inline-flex;align-items:center;justify-content:center;border-radius:4px;font-size:11px;line-height:1;}
.ard-trash-toolbar{display:block;width:100%;margin-bottom:16px;}
.ard-trash-bulkbar{display:block;width:100%;margin-bottom:14px;padding:14px;border:1px solid #d8e3f0;border-radius:10px;background:#fff;}
.ard-trash-bulkbar-left{display:block;width:100%;margin-bottom:12px;}
.ard-trash-bulkbar-label{font-size:12px;font-weight:700;color:#41556f;}
.ard-trash-bulkbar-left .text-muted{display:block;margin-top:5px;}
.ard-trash-bulkbar-actions{display:flex;align-items:center;gap:8px;flex-wrap:wrap;width:100%;}
.ard-trash-bulkbar .btn{min-width:170px;}
.ard-trash-top-pagination,.ard-trash-bottom-pagination{display:block;clear:both;width:100%;}
.ard-trash-top-pagination{margin-bottom:14px;}
.ard-trash-top-pagination .pagination,.ard-trash-bottom-pagination .pagination{margin:6px 0;}
.ard-trash-top-pagination small,.ard-trash-bottom-pagination small{display:block;margin-top:6px;}
.ard-trash-table-wrap{display:block;clear:both;width:100%;}
@media (max-width: 767px){
    .ard-trash-bulkbar{align-items:stretch;}
    .ard-trash-bulkbar-left,.ard-trash-bulkbar-actions{width:100%;}
    .ard-trash-bulkbar .btn{width:100%;min-width:0;}
}
</style>

<div id="resultDiv">
    <div class="page-title">
        <div class="title-env">
            <h1 class="title"><?php echo __('System Trash');?></h1>
            <p class="description"><?php echo __('Restore or permanently delete removed records');?></p>
        </div>
    </div>

    <div class="panel ard-trash-wrap">
        <?php echo $this->Session->flash();?>
        <div class="panel-body">
            <?php echo $this->Form->create('Ardtrash', array('url' => array('controller' => 'Ardaccounts', 'action' => 'trash', $currentProjectId), 'class' => 'form-inline', 'id' => 'ard-trash-bulk-form'));?>
            <?php echo $this->Form->hidden('selected_ids_csv', array('id' => 'ArdtrashSelectedIdsCsv', 'value' => ''));?>
            <?php echo $this->Form->hidden('bulk_action', array('id' => 'ArdtrashBulkAction', 'value' => ''));?>
            <div class="ard-trash-toolbar">
                <div class="ard-trash-bulkbar">
                    <div class="ard-trash-bulkbar-left">
                        <span class="ard-trash-bulkbar-label"><?php echo __('Bulk Actions');?></span>
                        <small class="text-muted"><?php echo __('Select rows from the table, then restore or delete them permanently.');?></small>
                    </div>
                    <div class="ard-trash-bulkbar-actions">
                        <button type="button" class="btn btn-success js-trash-bulk-submit" data-action="restore" data-confirm="<?php echo h(__('Restore selected records?'));?>">
                            <span class="fa fa-undo"></span> <?php echo __('Restore Selected');?>
                        </button>
                        <button type="button" class="btn btn-danger js-trash-bulk-submit" data-action="delete" data-confirm="<?php echo h(__('Delete selected records permanently?'));?>">
                            <span class="fa fa-trash"></span> <?php echo __('Delete Selected Permanently');?>
                        </button>
                    </div>
                </div>
                <div class="ard-trash-top-pagination">
                    <ul class="pagination pagination-sm">
                        <?php
                        echo $this->Paginator->prev('&larr; '.__('Prev'),array('tag'=>'li','escape'=>false),'<a>&larr;'.__('Prev').'</a>',array('class'=>'disabled','tag'=>'li','escape'=>false));
                        echo $this->Paginator->numbers(array('tag'=>'li','separator'=>null,'currentClass'=>'active','currentTag'=>'a','modulus'=>'4','first' => 2, 'last' => 2,'ellipsis'=>'<li><a>...</a></li>'));
                        echo $this->Paginator->next(__('Next').' &rarr;',array('tag'=>'li','escape'=>false),'<a>&rarr; '.__('Next').'</a>',array('class'=>'disabled','tag'=>'li','escape'=>false));
                        ?>
                    </ul>
                    <small><?php echo $this->Paginator->counter(__d('default','Showing {:start} to {:current} of {:count} entries'));?></small>
                </div>
            </div>

            <hr>
            <div class="table-responsive ard-trash-table-wrap">
                <table class="table table-striped table-bordered ard-trash-table">
                    <thead>
                        <tr>
                            <th style="width:40px;"><input type="checkbox" id="trashSelectAll"></th>
                            <th style="width:70px;">#</th>
                            <th style="width:165px;"><?php echo __('Deleted At');?></th>
                            <th style="width:150px;"><?php echo __('Deleted By');?></th>
                            <th style="width:160px;"><?php echo __('Type');?></th>
                            <th><?php echo __('Record');?></th>
                            <th style="width:90px;"><?php echo __('ID');?></th>
                            <th style="width:105px;"><?php echo __('Action');?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $pageParams = $this->Paginator->params();
                        $limit = isset($pageParams['limit']) ? (int)$pageParams['limit'] : 20;
                        $page = isset($pageParams['page']) ? (int)$pageParams['page'] : 1;
                        $serialNo = ($limit * max(0, $page - 1)) + 1;
                        if(empty($rows))
                        {
                            ?>
                            <tr>
                                <td colspan="7"><div class="ard-trash-empty"><?php echo __('Trash is empty.');?></div></td>
                            </tr>
                            <?php
                        }
                        foreach((array)$rows as $row)
                        {
                            $trash = isset($row['Ardtrash']) ? $row['Ardtrash'] : array();
                            $trashId = isset($trash['id']) ? (int)$trash['id'] : 0;
                            $sourceTable = isset($trash['source_table']) ? strtolower(trim((string)$trash['source_table'])) : '';
                            $sourceModel = isset($trash['source_model']) ? strtolower(trim((string)$trash['source_model'])) : '';
                            $isPaymentTrash = ($sourceTable === 'deals_payments' || $sourceModel === 'dealspayment');
                            $deletedAt = '-';
                            if(!empty($trash['deleted_at']) && $trash['deleted_at'] !== '0000-00-00 00:00:00')
                            {
                                $deletedAt = date('d-M-Y H:i', strtotime((string)$trash['deleted_at']));
                            }
                            ?>
                            <tr>
                                <td>
                                    <input type="checkbox" class="js-trash-item" name="trash_ids[]" value="<?php echo $trashId;?>">
                                </td>
                                <td><?php echo $serialNo++;?></td>
                                <td><?php echo h($deletedAt);?></td>
                                <td>
                                    <strong><?php echo h(isset($trash['deleted_by_name']) ? $trash['deleted_by_name'] : '-');?></strong>
                                    <?php if(!empty($trash['deleted_by_role'])){?><small class="ard-trash-record-meta"><?php echo __('Role');?>: <?php echo h($trash['deleted_by_role']);?></small><?php }?>
                                    <?php if(!empty($trash['deleted_by'])){?><small class="ard-trash-record-meta"><?php echo __('User ID');?>: <?php echo (int)$trash['deleted_by'];?></small><?php }?>
                                </td>
                                <td>
                                    <div><strong><?php echo h(isset($trash['source_model']) ? $trash['source_model'] : '-');?></strong></div>
                                    <small><?php echo h(isset($trash['source_table']) ? $trash['source_table'] : '-');?></small>
                                </td>
                                <td>
                                    <?php
                                    $displayTitle = isset($trash['display_title']) ? trim((string)$trash['display_title']) : '';
                                    if($displayTitle === '')
                                    {
                                        $displayTitle = (isset($trash['entry_title']) && trim((string)$trash['entry_title']) !== '') ? trim((string)$trash['entry_title']) : __('Untitled Record');
                                    }
                                    $displayMeta = isset($trash['display_meta']) ? trim((string)$trash['display_meta']) : '';
                                    ?>
                                    <div class="ard-trash-record-title"><?php echo h($displayTitle);?></div>
                                    <?php if($isPaymentTrash){?><span class="ard-trash-payment-badge"><?php echo __('Payment Receipt');?></span><?php }?>
                                    <?php if($displayMeta !== ''){?><small class="ard-trash-record-meta"><?php echo h($displayMeta);?></small><?php }?>
                                    <?php $displayDetails = isset($trash['display_details']) && is_array($trash['display_details']) ? $trash['display_details'] : array(); ?>
                                    <?php if(!empty($displayDetails)){ ?>
                                    <div class="ard-trash-record-details">
                                        <?php foreach($displayDetails as $displayDetail){ ?>
                                        <span class="ard-trash-record-detail"><strong><?php echo h(isset($displayDetail['label']) ? $displayDetail['label'] : '');?>:</strong> <?php echo h(isset($displayDetail['value']) ? $displayDetail['value'] : '');?></span>
                                        <?php } ?>
                                    </div>
                                    <?php } ?>
                                </td>
                                <td><?php echo (int)(isset($trash['source_id']) ? $trash['source_id'] : 0);?></td>
                                <td class="ard-trash-actions">
                                    <?php
                                    echo $this->Html->link(
                                        '<span class="fa fa-eye"></span>',
                                        '#',
                                        array('escape' => false, 'class' => 'btn btn-xs btn-info ard-trash-icon-btn', 'title' => __('View Deleted'), 'onclick' => "show_modal('".$this->Html->url(array('controller' => 'Ardaccounts', 'action' => 'viewtrash', $trashId, $currentProjectId))."'); return false;")
                                    );
                                    echo ' ';
                                    echo $this->Html->link(
                                        '<span class="fa fa-undo"></span>',
                                        array('controller' => 'Ardaccounts', 'action' => 'restoretrash', $trashId, $currentProjectId),
                                        array('escape' => false, 'class' => 'btn btn-xs btn-success ard-trash-icon-btn', 'title' => __('Restore'), 'onclick' => "return confirm('".__('Restore this record?')."');")
                                    );
                                    echo ' ';
                                    echo $this->Html->link(
                                        '<span class="fa fa-trash"></span>',
                                        array('controller' => 'Ardaccounts', 'action' => 'deletetrash', $trashId, $currentProjectId),
                                        array('escape' => false, 'class' => 'btn btn-xs btn-danger ard-trash-icon-btn', 'title' => __('Delete Permanently'), 'onclick' => "return confirm('".__('Delete permanently from trash?')."');")
                                    );
                                    ?>
                                </td>
                            </tr>
                            <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <div class="ard-trash-bottom-pagination">
                <ul class="pagination pagination-sm">
                    <?php
                    echo $this->Paginator->prev('&larr; '.__('Prev'),array('tag'=>'li','escape'=>false),'<a>&larr;'.__('Prev').'</a>',array('class'=>'disabled','tag'=>'li','escape'=>false));
                    echo $this->Paginator->numbers(array('tag'=>'li','separator'=>null,'currentClass'=>'active','currentTag'=>'a','modulus'=>'4','first' => 2, 'last' => 2,'ellipsis'=>'<li><a>...</a></li>'));
                    echo $this->Paginator->next(__('Next').' &rarr;',array('tag'=>'li','escape'=>false),'<a>&rarr; '.__('Next').'</a>',array('class'=>'disabled','tag'=>'li','escape'=>false));
                    ?>
                </ul>
                <small><?php echo $this->Paginator->counter(__d('default','Showing {:start} to {:current} of {:count} entries'));?></small>
            </div>
            <?php echo $this->Form->end();?>
        </div>
    </div>
</div>

<script type="text/javascript">
jQuery(function($){
    function collectTrashIdsCsv() {
        var ids = [];
        $('.js-trash-item:checked').each(function(){
            var v = parseInt($(this).val(), 10);
            if(!isNaN(v) && v > 0){
                ids.push(v);
            }
        });
        $('#ArdtrashSelectedIdsCsv').val(ids.join(','));
    }
    $('#trashSelectAll').on('change', function(){
        $('.js-trash-item').prop('checked', $(this).is(':checked'));
        collectTrashIdsCsv();
    });
    $(document).on('change', '.js-trash-item', function(){
        collectTrashIdsCsv();
    });
    $('.js-trash-bulk-submit').on('click', function(){
        var action = $(this).data('action') || '';
        var confirmText = $(this).data('confirm') || '<?php echo addslashes(__('Apply selected action?'));?>';
        collectTrashIdsCsv();
        if(!$('#ArdtrashSelectedIdsCsv').val()){
            alert('<?php echo addslashes(__('Please select at least one trash item.'));?>');
            return false;
        }
        $('#ArdtrashBulkAction').val(action);
        if(confirm(confirmText)){
            $('#ard-trash-bulk-form').trigger('submit');
        }
        return false;
    });
    $('#ard-trash-bulk-form').on('submit', function(){
        collectTrashIdsCsv();
    });
});
</script>
