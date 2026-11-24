<?php
$this->Paginator->options(array('url' => array('?' => (array)$this->request->query)));
$levelLabels = array(
    'country' => __('Country'),
    'province' => __('Province'),
    'district' => __('District'),
    'tehsil' => __('Tehsil'),
    'city' => __('City'),
    'town' => __('Town'),
    'block' => __('Block')
);
$limitOptions = isset($limitOptions) ? (array)$limitOptions : array(5=>5,10=>10,20=>20,25=>25,30=>30,50=>50,100=>100,200=>200,500=>500);
$currentLimit = isset($currentLimit) ? (int)$currentLimit : 50;
$query = isset($this->request->query) ? (array)$this->request->query : array();
$selectedLevelType = isset($selectedLevelType) ? (string)$selectedLevelType : (isset($query['level_type']) ? (string)$query['level_type'] : '');
$searchQuery = isset($searchQuery) ? (string)$searchQuery : (isset($query['q']) ? (string)$query['q'] : '');
$pageParams = (array)$this->Paginator->params();
$serialNo = !empty($pageParams['start']) ? (int)$pageParams['start'] : 1;
$pageCount = !empty($pageParams['pageCount']) ? (int)$pageParams['pageCount'] : 1;
$currentPage = !empty($pageParams['page']) ? (int)$pageParams['page'] : 1;
?>
<style type="text/css">
.wv-address-page{background:#f5f8fc;border:1px solid #dbe5f0;border-radius:12px;padding:16px;}
.wv-address-head{align-items:center;background:linear-gradient(135deg,#07152a 0%,#15304f 72%,#ff0a46 155%);border-radius:12px;color:#fff;display:flex;justify-content:space-between;margin-bottom:14px;padding:18px 20px;}
.wv-address-head h2{color:#fff;font-size:22px;font-weight:900;margin:0;}
.wv-address-head p{color:#d8e3f2;font-size:12px;font-weight:700;margin:4px 0 0;}
.wv-address-head .btn{border-radius:8px;font-weight:900;margin-left:6px;}
.wv-address-card{background:#fff;border:1px solid #dce6f2;border-radius:12px;box-shadow:0 12px 28px rgba(15,23,42,.06);margin-bottom:14px;padding:16px;}
.wv-address-filter{display:grid;grid-template-columns:220px 1fr 180px 120px;gap:10px;}
.wv-address-filter .form-control{border:1px solid #cfdbea;border-radius:8px;box-shadow:none;height:40px;}
.wv-address-filter label{color:#07152a;display:block;font-size:11px;font-weight:900;margin:0 0 4px;text-transform:uppercase;}
.wv-address-table{min-width:1240px;}
.wv-address-table th{background:#07152a!important;color:#fff!important;font-size:11px;text-transform:uppercase;white-space:nowrap;}
.wv-address-table th a{color:#fff!important;}
.wv-address-table td{vertical-align:middle!important;}
.wv-address-table .sr-cell{color:#07152a;font-weight:900;text-align:center;width:54px;}
.wv-address-table .path-cell{font-weight:800;color:#07152a;}
.wv-address-table .muted-cell{color:#94a3b8;font-weight:700;}
.wv-address-toolbar{align-items:center;display:flex;gap:8px;justify-content:space-between;margin-bottom:10px;}
.wv-address-toolbar .btn{border-radius:8px;font-weight:900;}
.wv-address-footer{align-items:center;background:#f8fbff;border:1px solid #dce6f2;border-radius:10px;display:grid;gap:12px;grid-template-columns:220px 1fr auto;margin-top:14px;padding:10px 12px;}
.wv-address-footer-limit label{align-items:center;color:#52627a;display:flex;font-size:12px;font-weight:900;gap:7px;margin:0;white-space:nowrap;}
.wv-address-footer-limit select{border:1px solid #cfdbea;border-radius:8px;height:32px;min-width:76px;padding:4px 8px;}
.wv-address-footer .pagination{display:flex;margin:0;}
.wv-address-footer .pagination li{display:inline-block;}
.wv-address-footer .pagination li a,.wv-address-footer .pagination li span{background:#fff;border:1px solid #cfdbea;color:#07152a;display:block;font-weight:800;margin-left:4px;padding:5px 10px;}
.wv-address-footer .pagination li.active a,.wv-address-footer .pagination li.active span{background:#0b5ed7;border-color:#0b5ed7;color:#fff;}
.wv-address-footer .pagination li.disabled a,.wv-address-footer .pagination li.disabled span{background:#eef3f8;color:#94a3b8;}
.wv-address-counter{color:#52627a;font-size:12px;font-weight:800;text-align:center;}
@media(max-width:900px){.wv-address-head{display:block}.wv-address-head .btn{margin:8px 4px 0 0}.wv-address-filter{grid-template-columns:1fr}.wv-address-toolbar{align-items:stretch;display:block}.wv-address-toolbar .btn{margin-bottom:6px;width:100%;}.wv-address-footer{align-items:flex-start;display:block}.wv-address-footer-limit,.wv-address-counter,.wv-address-footer .pagination{margin-top:8px;text-align:left}.wv-address-footer .pagination{flex-wrap:wrap;}}
</style>

<div class="page-title">
    <div class="title-env">
        <h1 class="title"><?php echo __('Pakistan Address Hierarchy');?></h1>
    </div>
</div>

<div class="wv-address-page">
    <div class="wv-address-head">
        <div>
            <h2><?php echo __('Pakistan Address Hierarchy');?></h2>
            <p><?php echo __('Manage complete paths: Country > Province > District > Tehsil > City > Town > Block.');?></p>
        </div>
        <div>
            <?php echo $this->Html->link('<span class="fa fa-plus"></span> '.__('Add Address Level'), array('action'=>'add'), array('escape'=>false,'class'=>'btn btn-success'));?>
            <?php echo $this->Html->link('<span class="fa fa-upload"></span> '.__('Import CSV / XLSX'), array('action'=>'import'), array('escape'=>false,'class'=>'btn btn-info'));?>
            <?php echo $this->Html->link('<span class="fa fa-download"></span> '.__('Sample File'), array('action'=>'sample'), array('escape'=>false,'class'=>'btn btn-default'));?>
            <?php echo $this->Html->link('<span class="fa fa-cogs"></span> '.__('Configurations'), array('controller'=>'Configurations','action'=>'index'), array('escape'=>false,'class'=>'btn btn-default'));?>
        </div>
    </div>
    <?php echo $this->Session->flash();?>

    <div class="wv-address-card">
        <form method="get" action="<?php echo h($this->Html->url(array('action'=>'index')));?>">
        <div class="wv-address-filter">
            <div>
                <label><?php echo __('Level');?></label>
                <select name="level_type" class="form-control">
                    <option value=""><?php echo __('All Levels');?></option>
                    <?php foreach($levelOptions as $levelValue => $levelLabel){ ?>
                        <option value="<?php echo h($levelValue);?>" <?php echo $selectedLevelType === (string)$levelValue ? 'selected="selected"' : '';?>><?php echo h($levelLabel);?></option>
                    <?php } ?>
                </select>
            </div>
            <div>
                <label><?php echo __('Search');?></label>
                <input type="text" name="q" class="form-control" placeholder="<?php echo h(__('Search country, province, district, city, town or block'));?>" value="<?php echo h($searchQuery);?>">
            </div>
            <div>
                <label><?php echo __('Show Entries');?></label>
                <select name="limit" class="form-control js-address-limit-select">
                    <?php foreach($limitOptions as $limitValue => $limitLabel){ ?>
                        <option value="<?php echo (int)$limitValue;?>" <?php echo (int)$currentLimit === (int)$limitValue ? 'selected="selected"' : '';?>><?php echo h($limitLabel);?></option>
                    <?php } ?>
                </select>
            </div>
            <div>
                <label>&nbsp;</label>
                <button class="btn btn-primary btn-block" type="submit"><?php echo __('Filter');?></button>
            </div>
        </div>
        </form>
    </div>

    <div class="wv-address-card">
        <div class="wv-address-toolbar">
            <strong><?php echo __('Address Paths');?></strong>
            <?php if(!empty($isSuperAdmin)){ ?>
                <button type="button" class="btn btn-danger" id="AddressBulkDelete"><span class="fa fa-trash"></span> <?php echo __('Delete Selected');?></button>
            <?php } ?>
        </div>
        <?php echo $this->Form->create('Addresshierarchy', array('url'=>array('action'=>'bulkdelete'), 'id'=>'AddressBulkForm'));?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped wv-address-table">
                <thead>
                    <tr>
                        <?php if(!empty($isSuperAdmin)){ ?><th width="34"><?php echo $this->Form->checkbox('select_all', array('id'=>'AddressSelectAll','hiddenField'=>false));?></th><?php } ?>
                        <th class="sr-cell"><?php echo __('Sr');?></th>
                        <?php foreach($levelLabels as $label){ ?><th><?php echo h($label);?></th><?php } ?>
                        <th><?php echo $this->Paginator->sort('level_type', __('Current Level'));?></th>
                        <th><?php echo $this->Paginator->sort('sort_order', __('Sort'));?></th>
                        <th><?php echo $this->Paginator->sort('status', __('Status'));?></th>
                        <th><?php echo __('Action');?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($rows)){ ?><tr><td colspan="<?php echo !empty($isSuperAdmin) ? 13 : 12;?>" class="text-center"><?php echo __('No address levels found.');?></td></tr><?php } ?>
                    <?php foreach((array)$rows as $row){ $a=$row['Addresshierarchy']; $path = isset($pathMap[(int)$a['id']]) ? $pathMap[(int)$a['id']] : array(); ?>
                    <tr>
                        <?php if(!empty($isSuperAdmin)){ ?><td><?php echo $this->Form->checkbox('id.'.$a['id'], array('value'=>(int)$a['id'], 'class'=>'address-row-check', 'hiddenField'=>false));?></td><?php } ?>
                        <td class="sr-cell"><?php echo (int)$serialNo++;?></td>
                        <?php foreach(array_keys($levelLabels) as $level){ ?>
                            <?php $value = isset($path[$level]) ? trim((string)$path[$level]) : ''; ?>
                            <td class="<?php echo $value === '' ? 'muted-cell' : 'path-cell';?>"><?php echo $value !== '' ? h($value) : '-';?></td>
                        <?php } ?>
                        <td><?php echo h(ucfirst($a['level_type']));?></td>
                        <td><?php echo (int)$a['sort_order'];?></td>
                        <td><?php echo !empty($a['status']) ? '<span class="label label-success">'.__('Active').'</span>' : '<span class="label label-default">'.__('Off').'</span>';?></td>
                        <td>
                            <?php echo $this->Html->link('<span class="fa fa-pencil"></span> '.__('Edit'), array('action'=>'edit',(int)$a['id']), array('escape'=>false,'class'=>'btn btn-xs btn-warning'));?>
                            <button type="button" class="btn btn-xs btn-danger js-address-single-delete" data-url="<?php echo h($this->Html->url(array('action'=>'delete',(int)$a['id'])));?>"><span class="fa fa-trash"></span> <?php echo __('Delete');?></button>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        <?php echo $this->Form->end();?>
        <form method="post" id="AddressSingleDeleteForm" action="" style="display:none;"></form>
        <div class="wv-address-footer">
            <div class="wv-address-footer-limit">
                <form method="get" action="<?php echo h($this->Html->url(array('action'=>'index')));?>">
                    <input type="hidden" name="level_type" value="<?php echo h($selectedLevelType);?>">
                    <input type="hidden" name="q" value="<?php echo h($searchQuery);?>">
                    <label>
                        <?php echo __('Show');?>
                        <select name="limit" class="js-address-limit-select">
                            <?php foreach($limitOptions as $limitValue => $limitLabel){ ?>
                                <option value="<?php echo (int)$limitValue;?>" <?php echo (int)$currentLimit === (int)$limitValue ? 'selected="selected"' : '';?>><?php echo h($limitLabel);?></option>
                            <?php } ?>
                        </select>
                        <?php echo __('entries');?>
                    </label>
                </form>
            </div>
            <div class="wv-address-counter">
                <?php echo $this->Paginator->counter(__('Showing {:start} to {:end} of {:count} entries'));?>
            </div>
            <ul class="pagination pagination-sm">
                <?php
                if($pageCount > 1)
                {
                    echo $this->Paginator->prev('&larr; '.__('Prev'), array('tag'=>'li','escape'=>false), '<a>&larr; '.__('Prev').'</a>', array('tag'=>'li','escape'=>false,'class'=>'disabled'));
                    echo $this->Paginator->numbers(array('tag'=>'li','separator'=>null,'currentClass'=>'active','currentTag'=>'a','modulus'=>'4','first'=>2,'last'=>2,'ellipsis'=>'<li><a>...</a></li>'));
                    echo $this->Paginator->next(__('Next').' &rarr;', array('tag'=>'li','escape'=>false), '<a>'.__('Next').' &rarr;</a>', array('tag'=>'li','escape'=>false,'class'=>'disabled'));
                }
                else
                {
                    echo '<li class="disabled"><a>&larr; '.__('Prev').'</a></li>';
                    echo '<li class="active"><a>'.sprintf(__('Page %s'), (int)$currentPage).'</a></li>';
                    echo '<li class="disabled"><a>'.__('Next').' &rarr;</a></li>';
                }
                ?>
            </ul>
        </div>
    </div>
</div>

<script type="text/javascript">
jQuery(function($){
    $('#AddressSelectAll').on('change', function(){
        $('.address-row-check').prop('checked', $(this).is(':checked'));
    });
    $('#AddressBulkDelete').on('click', function(){
        if($('.address-row-check:checked').length <= 0){
            alert(<?php echo json_encode(__('Please select address records first.'));?>);
            return false;
        }
        if(confirm(<?php echo json_encode(__('Delete selected address records? Records with children will be disabled.'));?>)){
            $('#AddressBulkForm').submit();
        }
    });
    $('.js-address-single-delete').on('click', function(){
        if(confirm(<?php echo json_encode(__('Delete this address level? If it has children it will be disabled.'));?>)){
            $('#AddressSingleDeleteForm').attr('action', $(this).data('url')).submit();
        }
    });
    $('.js-address-limit-select').on('change', function(){
        $(this).closest('form').submit();
    });
});
</script>
