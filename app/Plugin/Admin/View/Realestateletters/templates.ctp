<?php
$this->Js->JqueryEngine->jQueryObject = 'jQuery';
$this->Paginator->options(array(
    'update' => '#resultDiv',
    'evalScripts' => true,
));
?>
<style type="text/css">
/* Letters Templates - Tight Professional Table View */
.wv-templates-wrap {
    background: #ffffff;
    border: 1px solid #dce5ef;
    border-radius: 12px;
    box-shadow: 0 4px 18px rgba(15, 23, 42, 0.05);
    overflow: hidden;
    margin-bottom: 24px;
}
.wv-tpl-toolbar {
    display: flex !important;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
    padding: 12px 16px !important;
    background: #ffffff;
    border-bottom: 1px solid #e2e8f0;
}
.wv-tpl-btn-group {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}
.wv-tpl-btn-group .btn {
    display: inline-flex !important;
    align-items: center;
    justify-content: center;
    gap: 6px;
    height: 32px !important;
    min-height: 32px !important;
    font-size: 12px !important;
    font-weight: 700 !important;
    padding: 0 13px !important;
    border-radius: 6px !important;
    line-height: 1 !important;
    box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    transition: all 0.15s ease-in-out;
}
.wv-tpl-btn-group .btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 3px 8px rgba(0,0,0,0.1);
}
.wv-tpl-filter-form {
    display: flex !important;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
    margin: 0 !important;
}
.wv-tpl-filter-form .form-control {
    height: 32px !important;
    min-height: 32px !important;
    font-size: 12px !important;
    padding: 4px 10px !important;
    border-radius: 6px !important;
    border: 1px solid #cbd5e1 !important;
    background: #ffffff !important;
    color: #0f172a !important;
    box-shadow: none !important;
}
.wv-tpl-filter-form select.form-control {
    min-width: 155px;
    cursor: pointer;
}
.wv-tpl-filter-form input.form-control {
    min-width: 185px;
}
.wv-tpl-filter-form .btn {
    height: 32px !important;
    min-height: 32px !important;
    font-size: 12px !important;
    font-weight: 700 !important;
    padding: 0 12px !important;
    border-radius: 6px !important;
    display: inline-flex !important;
    align-items: center;
    gap: 5px;
    line-height: 1 !important;
}

/* Pagination bar tight styling */
.wv-tpl-pagination-bar {
    padding: 6px 16px;
    background: #f8fafc;
    border-bottom: 1px solid #e8eff6;
}
.wv-tpl-pagination-bar.bottom-pagination {
    border-bottom: 0;
    border-top: 1px solid #e8eff6;
}
.wv-tpl-pagination-bar .row {
    margin: 0 !important;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
}
.wv-tpl-pagination-bar .col-md-2,
.wv-tpl-pagination-bar .col-md-10,
.wv-tpl-pagination-bar .col-md-6,
.wv-tpl-pagination-bar .col-md-3 {
    padding: 0 !important;
    float: none !important;
}
.wv-tpl-pagination-bar .col-md-2 {
    display: flex;
    align-items: center;
}
.wv-tpl-pagination-bar .col-md-10 {
    display: flex;
    align-items: center;
    justify-content: flex-end;
}
.wv-tpl-pagination-bar .col-md-10 > .row {
    width: 100%;
    justify-content: flex-end;
    gap: 14px;
}
.wv-tpl-pagination-bar label {
    margin: 0 !important;
    font-size: 11px;
    font-weight: 700;
    color: #475569;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.wv-tpl-pagination-bar select.input-sm {
    height: 26px !important;
    padding: 2px 6px !important;
    border-radius: 5px !important;
    border: 1px solid #cbd5e1 !important;
    font-size: 11px !important;
    font-weight: 700;
    line-height: 1;
}
.wv-tpl-pagination-bar .pagination {
    margin: 0 !important;
    padding: 2px !important;
    border-radius: 6px !important;
    border: 1px solid #cbd5e1 !important;
    background: #ffffff !important;
    display: inline-flex !important;
    gap: 2px;
}
.wv-tpl-pagination-bar .pagination > li > a,
.wv-tpl-pagination-bar .pagination > li > span {
    padding: 3px 8px !important;
    font-size: 11px !important;
    font-weight: 700 !important;
    min-width: 24px !important;
    height: 22px !important;
    line-height: 16px !important;
    border: 0 !important;
    border-radius: 4px !important;
    color: #334155 !important;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.wv-tpl-pagination-bar .pagination > .active > a,
.wv-tpl-pagination-bar .pagination > .active > span {
    background: #0f172a !important;
    color: #ffffff !important;
}
.wv-tpl-pagination-bar small {
    font-size: 11.5px !important;
    font-weight: 700 !important;
    color: #475569 !important;
}

/* Tight & Professional Table */
.wv-tpl-table-wrap {
    margin: 0 !important;
    border: 0 !important;
    border-radius: 0 !important;
    box-shadow: none !important;
    overflow-x: auto;
}
.wv-tpl-table {
    width: 100% !important;
    margin: 0 !important;
    border-collapse: collapse !important;
    border: 0 !important;
    table-layout: auto;
}
.wv-tpl-table > thead > tr > th {
    background: linear-gradient(135deg, #071a31 0%, #11314f 100%) !important;
    color: #ffffff !important;
    font-size: 11px !important;
    font-weight: 800 !important;
    letter-spacing: 0.5px !important;
    text-transform: uppercase !important;
    padding: 9px 10px !important;
    border: 0 !important;
    border-bottom: 1px solid #0f2742 !important;
    vertical-align: middle !important;
    white-space: nowrap !important;
    line-height: 1.2 !important;
}
.wv-tpl-table > tbody > tr > td {
    padding: 6px 10px !important;
    vertical-align: middle !important;
    border-top: 1px solid #e9edf2 !important;
    border-bottom: 0 !important;
    border-left: 0 !important;
    border-right: 0 !important;
    font-size: 12px !important;
    color: #1e293b !important;
    line-height: 1.25 !important;
}
.wv-tpl-table > tbody > tr:first-child > td {
    border-top: 0 !important;
}
.wv-tpl-table > tbody > tr:hover > td {
    background: #f1f6fd !important;
}
.wv-tpl-table > tbody > tr:nth-of-type(even) > td {
    background-color: #fbfdff;
}
.wv-tpl-table > tbody > tr:nth-of-type(even):hover > td {
    background-color: #f1f6fd !important;
}

/* Columns */
.wv-col-check {
    width: 36px !important;
    min-width: 36px !important;
    max-width: 36px !important;
    text-align: center !important;
}
.wv-col-check input[type="checkbox"] {
    margin: 0 !important;
    vertical-align: middle !important;
    cursor: pointer;
    width: 15px;
    height: 15px;
}
.wv-col-sr {
    width: 44px !important;
    min-width: 44px !important;
    max-width: 44px !important;
    text-align: center !important;
    font-weight: 700 !important;
    color: #64748b !important;
    font-size: 11.5px !important;
}
.wv-col-type {
    width: 180px !important;
    min-width: 170px !important;
    text-align: left !important;
}
.wv-col-name {
    text-align: left !important;
}
.wv-col-category {
    width: 135px !important;
    min-width: 135px !important;
    text-align: center !important;
}
.wv-col-status {
    width: 105px !important;
    min-width: 105px !important;
    text-align: center !important;
}
.wv-col-actions {
    width: 195px !important;
    min-width: 195px !important;
    text-align: center !important;
    white-space: nowrap !important;
}

/* Badges */
.wv-tpl-type-badge {
    background: #0f2742 !important;
    color: #ffffff !important;
    border: 1px solid #1e3a5f !important;
    font-size: 10.5px !important;
    font-weight: 800 !important;
    letter-spacing: 0.3px;
    padding: 3px 8px !important;
    border-radius: 999px !important;
    display: inline-block;
    line-height: 1.25;
    text-transform: uppercase;
    white-space: nowrap;
}
.wv-tpl-name {
    font-weight: 700;
    color: #0f172a;
    font-size: 12.5px;
    line-height: 1.3;
}
.wv-tpl-cat-badge {
    display: inline-block;
    font-size: 10px !important;
    font-weight: 800 !important;
    letter-spacing: 0.25px;
    padding: 3px 8px !important;
    border-radius: 999px !important;
    line-height: 1.25;
    text-transform: uppercase;
    white-space: nowrap;
}
.wv-tpl-cat-custom {
    background: #e0f2fe !important;
    color: #0369a1 !important;
    border: 1px solid #bae6fd !important;
}
.wv-tpl-cat-default {
    background: #f8fafc !important;
    color: #475569 !important;
    border: 1px solid #cbd5e1 !important;
}
.wv-tpl-status-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 10px !important;
    font-weight: 800 !important;
    letter-spacing: 0.25px;
    padding: 3px 8px !important;
    border-radius: 999px !important;
    line-height: 1.25;
    text-transform: uppercase;
    white-space: nowrap;
}
.wv-tpl-status-published {
    background: #ecfdf5 !important;
    color: #047857 !important;
    border: 1px solid #a7f3d0 !important;
}
.wv-tpl-status-unpublished {
    background: #fef2f2 !important;
    color: #b91c1c !important;
    border: 1px solid #fecaca !important;
}

/* Action Buttons: tight, single horizontal row, no wrap */
.wv-tpl-row-actions {
    display: inline-flex !important;
    align-items: center;
    justify-content: center;
    gap: 3px !important;
    flex-wrap: nowrap !important;
    white-space: nowrap !important;
    margin: 0 !important;
}
.wv-tpl-row-actions .wv-action-btn {
    width: 26px !important;
    height: 26px !important;
    min-width: 26px !important;
    max-width: 26px !important;
    min-height: 26px !important;
    max-height: 26px !important;
    border-radius: 6px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    font-size: 11.5px !important;
    line-height: 1 !important;
    padding: 0 !important;
    margin: 0 !important;
    text-decoration: none !important;
    border: 1px solid transparent !important;
    box-shadow: 0 1px 2px rgba(0,0,0,0.06);
    transition: all 0.12s ease-in-out;
    cursor: pointer;
}
.wv-tpl-row-actions .wv-action-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 3px 6px rgba(0,0,0,0.14);
}
.wv-tpl-row-actions .wv-action-view {
    background: #f8fafc !important;
    color: #334155 !important;
    border-color: #cbd5e1 !important;
}
.wv-tpl-row-actions .wv-action-view:hover {
    background: #0f172a !important;
    color: #ffffff !important;
    border-color: #0f172a !important;
}
.wv-tpl-row-actions .wv-action-print {
    background: #0284c7 !important;
    color: #ffffff !important;
    border-color: #0284c7 !important;
}
.wv-tpl-row-actions .wv-action-print:hover {
    background: #0369a1 !important;
    border-color: #0369a1 !important;
    color: #ffffff !important;
}
.wv-tpl-row-actions .wv-action-edit {
    background: #2563eb !important;
    color: #ffffff !important;
    border-color: #2563eb !important;
}
.wv-tpl-row-actions .wv-action-edit:hover {
    background: #1d4ed8 !important;
    border-color: #1d4ed8 !important;
    color: #ffffff !important;
}
.wv-tpl-row-actions .wv-action-add {
    background: #059669 !important;
    color: #ffffff !important;
    border-color: #059669 !important;
}
.wv-tpl-row-actions .wv-action-add:hover {
    background: #047857 !important;
    border-color: #047857 !important;
    color: #ffffff !important;
}
.wv-tpl-row-actions .wv-action-toggle.is-published {
    background: #f59e0b !important;
    color: #ffffff !important;
    border-color: #f59e0b !important;
}
.wv-tpl-row-actions .wv-action-toggle.is-published:hover {
    background: #d97706 !important;
    border-color: #d97706 !important;
    color: #ffffff !important;
}
.wv-tpl-row-actions .wv-action-toggle.is-unpublished {
    background: #94a3b8 !important;
    color: #ffffff !important;
    border-color: #94a3b8 !important;
}
.wv-tpl-row-actions .wv-action-toggle.is-unpublished:hover {
    background: #64748b !important;
    border-color: #64748b !important;
    color: #ffffff !important;
}
.wv-tpl-row-actions .wv-action-delete {
    background: #ef4444 !important;
    color: #ffffff !important;
    border-color: #ef4444 !important;
}
.wv-tpl-row-actions .wv-action-delete:hover {
    background: #dc2626 !important;
    border-color: #dc2626 !important;
    color: #ffffff !important;
}
</style>

<div id="resultDiv">
    <?php echo $this->Session->flash();?>
    <div class="wv-templates-wrap">
        <div class="wv-tpl-toolbar">
            <div class="wv-tpl-btn-group">
                <?php echo $this->Html->link('<span class="fa fa-dashboard"></span>&nbsp;'.__('Dashboard'),array('controller'=>'Realestateletters','action'=>'dashboard'),array('escape'=>false,'class'=>'btn btn-primary'));?>
                <?php echo $this->Html->link('<span class="fa fa-envelope-o"></span>&nbsp;'.__('Letters'),array('controller'=>'Realestateletters','action'=>'index'),array('escape'=>false,'class'=>'btn btn-info'));?>
                <?php echo $this->Html->link('<span class="fa fa-plus"></span>&nbsp;'.__('Add Template'),array('controller'=>'Realestateletters','action'=>'templateadd'),array('escape'=>false,'class'=>'btn btn-success'));?>
                <?php echo $this->Html->link('<span class="fa fa-trash"></span>&nbsp;'.__('Delete Selected'),'#',array('name'=>'deleteallfrm','id'=>'deleteallfrm','onclick'=>'check_perform_delete(); return false;','escape'=>false,'class'=>'btn btn-danger'));?>
            </div>
            <div class="wv-tpl-filter-wrap">
                <?php echo $this->Form->create(false,array('type'=>'get','url'=>array('controller'=>'Realestateletters','action'=>'templates'),'class'=>'form-inline wv-tpl-filter-form'));?>
                <?php echo $this->Form->input('type',array('type'=>'select','options'=>$letterTypeArr,'empty'=>__('All Types'),'value'=>$selectedType,'label'=>false,'div'=>false,'class'=>'form-control'));?>
                <?php echo $this->Form->input('keyword',array('value'=>$searchKeyword,'label'=>false,'div'=>false,'class'=>'form-control','placeholder'=>__('Search templates')));?>
                <button type="submit" class="btn btn-info"><span class="fa fa-search"></span> <?php echo __('Search');?></button>
                <?php echo $this->Html->link(__('Reset'),array('controller'=>'Realestateletters','action'=>'templates'),array('class'=>'btn btn-default'));?>
                <?php echo $this->Form->end();?>
            </div>
        </div>

        <div class="wv-tpl-pagination-bar top-pagination">
            <?php echo $this->element('pagination', array('IsSearch' => 'yes', 'IsAction' => 'templates'));?>
        </div>

        <?php
        $pageParams=$this->Paginator->params();
        $limit=$pageParams['limit'];
        $page=$pageParams['page'];
        $serialNo=1*$limit*($page-1)+1;
        ?>

        <?php echo $this->Form->create('Template', array('name'=>'deleteallfrm','id'=>'deleteallfrm','url'=>array('controller'=>'Realestateletters','action'=>'templatedeleteall')));?>
        <div class="table-responsive wv-tpl-table-wrap">
            <table class="table table-hover wv-tpl-table">
                <thead>
                    <tr>
                        <th class="wv-col-check"><?php echo $this->Form->checkbox('checkbox', array('value'=>'deleteall','name'=>'selectAll','label'=>false,'id'=>'selectAll','hiddenField'=>false));?></th>
                        <th class="wv-col-sr"><?php echo __('#');?></th>
                        <th class="wv-col-type"><?php echo __('Type');?></th>
                        <th class="wv-col-name"><?php echo __('Template Name');?></th>
                        <th class="wv-col-category"><?php echo __('Category');?></th>
                        <th class="wv-col-status"><?php echo __('Status');?></th>
                        <th class="wv-col-actions"><?php echo __('Actions');?></th>
                    </tr>
                </thead>
                <tbody>
                <?php if(!empty($Template)): ?>
                    <?php foreach((array)$Template as $row):
                        $id=(int)$row['Template']['id'];
                        $typeCode=isset($row['Template']['type']) ? $row['Template']['type'] : '';
                        $typeLabel=isset($letterTypeArr[$typeCode]) ? $letterTypeArr[$typeCode] : $typeCode;
                        $isCustomTemplate = (isset($row['Template']['is_new']) && strtolower(trim((string)$row['Template']['is_new']))==='yes');
                        $templateCategory = $isCustomTemplate ? __('Custom Template') : __('Default Template');
                        $currentStatus = isset($row['Template']['status']) ? trim((string)$row['Template']['status']) : 'Published';
                        $toggleStatus = ($currentStatus==='Published') ? 'Unpublished' : 'Published';
                        $toggleStatusUrl = array('controller'=>'Realestateletters','action'=>'templatestatus',$id,$toggleStatus,'?' => array('type' => ($selectedType!=='' ? $selectedType : $typeCode)));
                        $createLetterUrl = array('controller'=>'Realestateletters','action'=>'add',$typeCode);
                        ?>
                    <tr class="wv-tpl-row">
                        <td class="wv-col-check"><?php echo $this->Form->checkbox(false,array('value'=>$id,'name'=>'data[Template][id][]','id'=>"DeleteCheckbox$id",'class'=>'chkselect'));?></td>
                        <td class="wv-col-sr"><?php echo $serialNo++;?></td>
                        <td class="wv-col-type"><span class="wv-tpl-type-badge"><?php echo h($typeLabel);?></span></td>
                        <td class="wv-col-name"><span class="wv-tpl-name"><?php echo h($row['Template']['name']);?></span></td>
                        <td class="wv-col-category">
                            <span class="wv-tpl-cat-badge <?php echo $isCustomTemplate ? 'wv-tpl-cat-custom' : 'wv-tpl-cat-default';?>">
                                <?php echo h($templateCategory);?>
                            </span>
                        </td>
                        <td class="wv-col-status">
                            <span class="wv-tpl-status-badge <?php echo ($currentStatus==='Published') ? 'wv-tpl-status-published' : 'wv-tpl-status-unpublished';?>">
                                <i class="fa <?php echo ($currentStatus==='Published') ? 'fa-check-circle' : 'fa-times-circle';?>"></i>
                                <?php echo h($currentStatus);?>
                            </span>
                        </td>
                        <td class="wv-col-actions">
                            <div class="wv-tpl-row-actions">
                                <?php echo $this->Html->link('<span class="fa fa-eye"></span>',array('controller'=>'Realestateletters','action'=>'templateview',$id),array('escape'=>false,'class'=>'wv-action-btn wv-action-view','title'=>__('View Preview')));?>
                                <?php echo $this->Html->link('<span class="fa fa-print"></span>',array('controller'=>'Realestateletters','action'=>'templateprint',$id),array('escape'=>false,'class'=>'wv-action-btn wv-action-print','target'=>'_blank','title'=>__('Print Preview')));?>
                                <?php echo $this->Html->link('<span class="fa fa-edit"></span>',array('controller'=>'Realestateletters','action'=>'templateedit',$id),array('escape'=>false,'class'=>'wv-action-btn wv-action-edit','title'=>__('Edit Template')));?>
                                <?php if(!in_array($typeCode,array('RLT_RENTAL_AGREEMENT','RLT_RENTAL_RECEIPT'),true)){ echo $this->Html->link('<span class="fa fa-plus"></span>', $createLetterUrl, array('escape'=>false,'class'=>'wv-action-btn wv-action-add','title'=>__('Create Letter from Template'))); }?>
                                <?php echo $this->Html->link('<span class="fa '.($currentStatus==='Published' ? 'fa-toggle-on' : 'fa-toggle-off').'"></span>', $toggleStatusUrl, array('escape'=>false,'class'=>'wv-action-btn wv-action-toggle '.($currentStatus==='Published' ? 'is-published' : 'is-unpublished'),'title'=>__('Toggle Status')));?>
                                <?php if($isCustomTemplate){ echo $this->Html->link('<span class="fa fa-trash"></span>','#',array('escape'=>false,'class'=>'wv-action-btn wv-action-delete','title'=>__('Delete'),'onclick'=>"check_perform_sdelete('$id'); return false;")); }?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach;?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center" style="padding: 24px 10px !important; color: #64748b; font-weight: 600;">
                            <i class="fa fa-info-circle" style="font-size: 18px; margin-bottom: 6px; display: block; color: #94a3b8;"></i>
                            <?php echo __('No letter templates found.');?>
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php echo $this->Form->end();?>

        <div class="wv-tpl-pagination-bar bottom-pagination">
            <?php echo $this->element('pagination', array('IsSearch' => 'yes', 'IsAction' => 'templates'));?>
        </div>
    </div>
</div>
