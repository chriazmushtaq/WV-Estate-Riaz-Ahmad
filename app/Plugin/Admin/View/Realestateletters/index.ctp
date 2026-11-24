<?php
$this->Js->JqueryEngine->jQueryObject = 'jQuery';
$this->Paginator->options(array(
    'update' => '#resultDiv',
    'evalScripts' => true,
));
$currentDealId = !empty($currentDealId) ? (int)$currentDealId : 0;
$currentLetterType = !empty($currentLetterType) ? $currentLetterType : '';
$typeLabelHeading = ($currentLetterType!=='' && isset($letterTypeArr[$currentLetterType])) ? $letterTypeArr[$currentLetterType] : __('All Types');
$lettersProjectId = isset($lettersProjectId) ? (int)$lettersProjectId : 0;
$autoLetterTypeArr = isset($autoLetterTypeArr) ? (array)$autoLetterTypeArr : array();
$letterStatusArr = isset($letterStatusArr) ? (array)$letterStatusArr : array('Published' => __('Issued'), 'Unpublished' => __('Cancle'));
?>
<style type="text/css">
body.wv-admin-theme .wv-letters-panel{background:#fff!important;border:1px solid #d7e3f2!important;border-radius:12px!important;box-shadow:0 14px 34px rgba(15,23,42,.08)!important;overflow:hidden!important;}
body.wv-admin-theme .panel-heading.wv-letter-topbar{background:linear-gradient(135deg,#071a31 0%,#11314f 58%,#32173f 100%)!important;border:0!important;display:block!important;padding:14px 16px 16px!important;}
.wv-letter-toolbar-line{align-items:flex-start;display:flex;gap:12px;justify-content:space-between;margin:0 0 10px;}
.wv-letter-main-actions,
.wv-letter-add-actions{align-items:center;display:flex!important;flex-wrap:wrap;gap:8px;margin:0!important;}
.wv-letter-main-actions{flex:1 1 auto;}
.wv-letter-bulk-actions{align-items:center;display:flex;flex:0 0 auto;gap:8px;justify-content:flex-end;}
.wv-letter-add-actions{background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.12);border-radius:10px;padding:9px;}
.wv-letter-topbar .btn{align-items:center;border:0!important;border-radius:8px!important;box-shadow:0 8px 18px rgba(0,0,0,.12)!important;display:inline-flex!important;font-size:12px!important;font-weight:900!important;gap:6px;justify-content:center;letter-spacing:0;line-height:1.1!important;min-height:34px;padding:8px 12px!important;text-decoration:none!important;text-shadow:none!important;white-space:nowrap;}
.wv-letter-topbar .btn .fa{font-size:13px;line-height:1;}
.wv-letter-main-actions .btn-primary{background:#2563eb!important;color:#fff!important;}
.wv-letter-main-actions .btn-info{background:#0e7490!important;color:#fff!important;}
.wv-letter-main-actions .btn-warning{background:#f59e0b!important;color:#111827!important;}
.wv-letter-bulk-actions .btn-danger{background:#dc2626!important;color:#fff!important;}
.wv-letter-add-actions .btn{background:#047857!important;color:#fff!important;min-height:32px!important;padding:7px 11px!important;}
.wv-letter-topbar .btn:hover,
.wv-letter-topbar .btn:focus{filter:brightness(1.06);transform:translateY(-1px);}
.wv-letter-filter-shell{align-items:center;display:flex;flex-wrap:wrap;gap:10px;justify-content:space-between;margin-top:10px;}
.wv-letter-filter-form{align-items:center;background:#f8fbff;border:1px solid #cfe0f3;border-radius:12px;box-shadow:0 10px 24px rgba(15,23,42,.12);display:flex!important;flex:1 1 760px;flex-wrap:wrap;gap:8px;justify-content:flex-start;padding:9px 10px;}
.wv-letter-filter-form .form-control,
.wv-letter-filter-form select.form-control,
.wv-letter-filter-form input.form-control{background:#fff!important;border:1px solid #bfd3ea!important;border-radius:8px!important;box-shadow:none!important;color:#0f172a!important;-webkit-text-fill-color:#0f172a;font-weight:800;height:36px!important;min-width:185px;}
.wv-letter-filter-form .wv-letter-filter-input{min-width:185px;}
.wv-letter-filter-form .wv-letter-keyword-filter{min-width:310px;}
.wv-letter-filter-form select.form-control option{background:#fff!important;color:#0f172a!important;}
.wv-letter-filter-form .form-control::-webkit-input-placeholder{color:#64748b!important;-webkit-text-fill-color:#64748b;opacity:1;}
.wv-letter-filter-form .form-control:-moz-placeholder{color:#64748b!important;opacity:1;}
.wv-letter-filter-form .form-control::-moz-placeholder{color:#64748b!important;opacity:1;}
.wv-letter-filter-form .form-control:-ms-input-placeholder{color:#64748b!important;opacity:1;}
.wv-letter-filter-form .btn{box-shadow:none!important;height:36px!important;min-height:36px!important;padding:8px 14px!important;}
.wv-letter-filter-form .btn-info{background:#0e7490!important;color:#fff!important;}
.wv-letter-filter-form .btn-default{background:#fff!important;border:1px solid #cbd8e8!important;color:#0f172a!important;}
body.wv-admin-theme .panel-heading .wv-letter-filter-summary.alert{align-items:center;background:#e8f5ff!important;border:1px solid #b7daf2!important;border-radius:10px!important;color:#0f2f4a!important;display:inline-flex;font-size:12px;font-weight:900;margin:0!important;min-height:38px;padding:10px 14px!important;white-space:nowrap;}
body.wv-admin-theme .panel-heading .wv-letter-filter-summary.alert strong{color:#082f49!important;}
body.wv-admin-theme .wv-letter-deal-alert{background:#fff7ed!important;border:1px solid #fdba74!important;border-radius:10px!important;color:#7c2d12!important;font-weight:800;margin:10px 0 0!important;padding:9px 12px!important;}
.wv-letter-table-wrap{padding:0 12px 12px;}
.wv-letter-table{background:#fff;border:1px solid #dbe6f4!important;margin-bottom:0!important;}
.wv-letter-table>tbody>tr>th{background:#f1f6fb!important;border-color:#dbe6f4!important;color:#0f2742!important;font-size:11px!important;font-weight:900!important;white-space:nowrap;}
.wv-letter-table>tbody>tr>td{border-color:#e2eaf4!important;color:#0f172a;vertical-align:middle!important;}
.wv-letter-table .label{border-radius:999px;display:inline-block;font-size:10px;font-weight:900;padding:4px 8px;}
.wv-letter-actions-cell{min-width:214px;white-space:normal!important;}
.wv-letter-row-actions{display:flex;flex-wrap:wrap;gap:4px;}
.wv-letter-row-actions .btn{align-items:center;border:0!important;border-radius:6px!important;display:inline-flex;height:26px;justify-content:center;margin:0!important;min-width:26px;padding:0!important;}
@media(max-width:1199px){
    .wv-letter-toolbar-line{display:block;}
    .wv-letter-bulk-actions{justify-content:flex-start;margin-top:8px;}
}
@media(max-width:991px){
    .wv-letter-filter-shell{justify-content:flex-start;}
    .wv-letter-filter-form,
    .wv-letter-filter-form .form-control,
    .wv-letter-filter-form .btn,
    .wv-letter-filter-summary.alert{width:100%!important;}
    .wv-letter-filter-form .wv-letter-keyword-filter,
    .wv-letter-filter-form .wv-letter-filter-input{min-width:0;}
}
</style>
<div id="resultDiv">
    <div class="panel wv-letters-panel"><?php echo $this->Session->flash();?>
        <div class="panel-heading wv-letter-topbar">
            <div class="wv-letter-toolbar-line">
                <div class="btn-group wv-letter-main-actions">
                    <?php
                    $dashboardUrl = array('controller' => 'Realestateletters', 'action' => 'dashboard');
                    if ($lettersProjectId > 0) {
                        $dashboardUrl['?'] = array('project_id' => $lettersProjectId);
                    }
                    echo $this->Html->link('<span class="fa fa-dashboard"></span>&nbsp;'.__('Dashboard'), $dashboardUrl, array('escape'=>false,'class'=>'btn btn-primary'));
                    ?>
                    <?php echo $this->Html->link('<span class="fa fa-files-o"></span>&nbsp;'.__('Templates'),array('controller'=>'Realestateletters','action'=>'templates'),array('escape'=>false,'class'=>'btn btn-info'));?>
                    <?php
                    $trashUrl = array('controller' => 'Realestateletters', 'action' => 'trash');
                    if ($lettersProjectId > 0) {
                        $trashUrl['?'] = array('project_id' => $lettersProjectId);
                    }
                    echo $this->Html->link('<span class="fa fa-trash"></span>&nbsp;'.__('Trash'), $trashUrl, array('escape'=>false,'class'=>'btn btn-warning'));
                    ?>
                </div>
                <div class="wv-letter-bulk-actions">
                    <?php echo $this->Html->link('<span class="fa fa-trash"></span>&nbsp;'.__('Delete Selected'),'#',array('name'=>'deleteallfrm','id'=>'deleteallfrm','onclick'=>'check_perform_delete();','escape'=>false,'class'=>'btn btn-danger'));?>
                </div>
            </div>
            <div class="btn-group wv-letter-add-actions">
                <?php foreach($letterTypeArr as $typeCode=>$typeLabel){
                    $addUrl=array('controller'=>'Realestateletters','action'=>'add',$typeCode);
                    if($currentDealId>0){$addUrl[]=$currentDealId;}
                    if($lettersProjectId>0){$addUrl['?']=array('project_id'=>$lettersProjectId);}
                    echo $this->Html->link('<span class="fa fa-plus"></span>&nbsp;'.$typeLabel,$addUrl,array('escape'=>false,'class'=>'btn btn-success btn-sm'));
                }?>
            </div>
            <div class="wv-letter-filter-shell">
                <?php echo $this->Form->create(false,array('type'=>'get','url'=>array('controller'=>'Realestateletters','action'=>'index'),'class'=>'form-inline wv-letter-filter-form'));?>
                <?php if(!empty($lettersCanUseAgencyFilters)){?>
                <?php echo $this->Form->input('project_id',array('type'=>'select','options'=>(array)$lettersProjectName,'empty'=>__('All Agency'),'value'=>$lettersProjectId,'label'=>false,'div'=>false,'class'=>'form-control wv-letter-filter-select'));?>
                <?php } else { ?>
                <?php echo $this->Form->hidden('project_id', array('value' => $lettersProjectId));?>
                <?php } ?>
                <?php echo $this->Form->input('type',array('type'=>'select','options'=>$letterTypeArr,'empty'=>__('All Letter Types'),'value'=>$currentLetterType,'label'=>false,'div'=>false,'class'=>'form-control wv-letter-filter-select'));?>
                <?php echo $this->Form->input('deal_id',array('type'=>'text','value'=>($currentDealId>0 ? $currentDealId : ''),'label'=>false,'div'=>false,'class'=>'form-control wv-letter-filter-input','placeholder'=>__('Deal ID optional')));?>
                <?php echo $this->Form->input('keyword',array('value'=>$searchKeyword,'label'=>false,'div'=>false,'class'=>'form-control wv-letter-filter-input wv-letter-keyword-filter','placeholder'=>__('Search by title/client/property/membership')));?>
                <button type="submit" class="btn btn-info"><?php echo __('Search');?></button>
                <?php
                $resetUrl = array('controller' => 'Realestateletters', 'action' => 'index');
                if (!$lettersCanUseAgencyFilters || $lettersProjectId > 0) {
                    $resetUrl['?'] = array('project_id' => $lettersProjectId);
                }
                echo $this->Html->link(__('Reset'), $resetUrl, array('class'=>'btn btn-default'));
                ?>
                <?php echo $this->Form->end();?>
                <div class="alert alert-info wv-letter-filter-summary">
                    <strong><?php echo __('Type Filter');?>:</strong> <?php echo h($typeLabelHeading);?>
                    <?php if($currentDealId>0){?> | <strong><?php echo __('Deal Filter');?>:</strong> #<?php echo (int)$currentDealId;?><?php }?>
                </div>
            </div>
            <?php if(!empty($dealHeader['Deal']['id'])){?>
            <div class="alert alert-warning wv-letter-deal-alert">
                <strong><?php echo __('Deal');?>:</strong> <?php echo h($dealHeader['Deal']['invoice_no']);?> |
                <strong><?php echo __('Client');?>:</strong> <?php echo h($dealHeader['Client']['name']);?> |
                <strong><?php echo __('Property');?>:</strong> <?php echo h($dealHeader['Property']['name']);?>
            </div>
            <?php }?>
        </div>
        <?php echo $this->element('pagination');
        $pageParams = $this->Paginator->params();
        $limit = $pageParams['limit'];
        $page = $pageParams['page'];
        $serialNo = 1*$limit*($page-1)+1;?>
        <?php echo $this->Form->create(array('name'=>'deleteallfrm','action'=>'deleteall'));?>
        <?php echo $this->Form->hidden('project_id', array('value' => $lettersProjectId));?>
        <div class="table-responsive wv-letter-table-wrap">
            <table class="table table-striped table-bordered wv-letter-table">
                <tr>
                    <th><?php echo $this->Form->checkbox('checkbox', array('value'=>'deleteall','name'=>'selectAll','label'=>false,'id'=>'selectAll','hiddenField'=>false));?></th>
                    <th><?php echo $this->Paginator->sort('id', __('#'), array('direction' => 'desc'));?></th>
                    <th><?php echo $this->Paginator->sort('reference_no', __('Ref No'), array('direction' => 'desc'));?></th>
                    <th><?php echo $this->Paginator->sort('letter_date', __('Date'), array('direction' => 'desc'));?></th>
                    <th><?php echo __('Type');?></th>
                    <th><?php echo __('Client');?></th>
                    <th><?php echo __('Father / Guardian');?></th>
                    <th><?php echo __('Property');?></th>
                    <th><?php echo __('Membership');?></th>
                    <th><?php echo $this->Paginator->sort('status', __('Status'), array('direction' => 'asc'));?></th>
                    <th><?php echo __('Action');?></th>
                </tr>
                <?php foreach ($Realestateletter as $post):
                    $id = (int)$post['Realestateletter']['id'];
                    $typeCode = isset($post['Realestateletter']['letter_type']) ? $post['Realestateletter']['letter_type'] : '';
                    $typeLabel = isset($letterTypeArr[$typeCode]) ? $letterTypeArr[$typeCode] : $typeCode;
                    $isAutoLetter = isset($autoLetterTypeArr[$typeCode]);
                    ?>
                <tr>
                    <td><?php echo $this->Form->checkbox(false,array('value'=>$id,'name'=>'data[Realestateletter][id][]','id'=>"DeleteCheckbox$id",'class'=>'chkselect'));?></td>
                    <td><?php echo $serialNo++;?></td>
                    <td><?php echo h($post['Realestateletter']['reference_no']);?></td>
                    <td><?php echo !empty($post['Realestateletter']['letter_date']) ? $this->Time->format($sysDay.$dateSep.$sysMonth.$dateSep.$sysYear,$post['Realestateletter']['letter_date']) : '';?></td>
                    <td>
                        <?php echo h($typeLabel);?>
                        <?php if($isAutoLetter){?>
                        <span class="label label-info" style="margin-left:4px;"><?php echo __('Auto');?></span>
                        <?php }?>
                    </td>
                    <td><?php echo h($post['Client']['name']);?></td>
                    <td>
                        <?php
                        $fatherName = !empty($post['Client']['fathername']) ? trim((string)$post['Client']['fathername']) : '';
                        $relationType = !empty($post['Client']['relation_type']) ? trim((string)$post['Client']['relation_type']) : '';
                        $gender = !empty($post['Client']['gender']) ? trim((string)$post['Client']['gender']) : '';
                        $clientName = !empty($post['Client']['name']) ? trim((string)$post['Client']['name']) : '';

                        // If fathername is not separate in DB, check if client name contains S/O, W/O, D/O
                        if ($fatherName === '' && preg_match('/^(.*?)\s*(?:(S\s*\/\s*O|D\s*\/\s*O|W\s*\/\s*O|SON\s+OF|DAUGHTER\s+OF|WIFE\s+OF))\s+(.+)$/i', $clientName, $pm)) {
                            $fatherName = trim($pm[3]);
                            $relWord = strtoupper(str_replace(' ', '', $pm[2]));
                            if (strpos($relWord, 'W') !== false) {
                                $relationType = 'Husband';
                            } elseif (strpos($relWord, 'D') !== false) {
                                $relationType = 'Father';
                            } else {
                                $relationType = 'Father';
                            }
                        }

                        if($fatherName !== ''){
                            $relLower = strtolower($relationType);
                            $genderLower = strtolower($gender);
                            $prefix = 'S/O';
                            $relBadgeBg = '#e0f2fe';
                            $relBadgeColor = '#0369a1';
                            $relBadgeBorder = '#bae6fd';
                            $relLabel = ($relationType !== '') ? $relationType : 'Father';

                            if ($relLower === 'husband' || strpos($relLower, 'wife') !== false || strpos($relLower, 'w/o') !== false) {
                                $prefix = 'W/O';
                                $relLabel = 'Husband';
                                $relBadgeBg = '#fce7f3';
                                $relBadgeColor = '#9d174d';
                                $relBadgeBorder = '#fbcfe8';
                            } elseif ($genderLower === 'female' || $genderLower === 'f' || $relLower === 'daughter' || strpos($relLower, 'd/o') !== false) {
                                $prefix = 'D/O';
                                $relLabel = 'Father';
                                $relBadgeBg = '#fef3c7';
                                $relBadgeColor = '#92400e';
                                $relBadgeBorder = '#fde68a';
                            } elseif ($relLower === 'guardian') {
                                $prefix = 'Guardian';
                                $relLabel = 'Guardian';
                                $relBadgeBg = '#f1f5f9';
                                $relBadgeColor = '#334155';
                                $relBadgeBorder = '#cbd5e1';
                            } elseif ($relLower === 'mother') {
                                $prefix = 'Mother';
                                $relLabel = 'Mother';
                                $relBadgeBg = '#d1fae5';
                                $relBadgeColor = '#065f46';
                                $relBadgeBorder = '#a7f3d0';
                            }
                            ?>
                            <div style="font-weight:700;color:#0f172a;line-height:1.35;">
                                <span class="badge" style="background:<?php echo $relBadgeBg;?>;color:<?php echo $relBadgeColor;?>;border:1px solid <?php echo $relBadgeBorder;?>;font-size:10px;font-weight:800;padding:2px 6px;margin-right:4px;border-radius:4px;letter-spacing:.02em;"><?php echo h($prefix);?></span>
                                <?php echo h($fatherName);?>
                            </div>
                            <small class="text-muted" style="display:block;font-size:11px;font-weight:600;margin-top:3px;color:#64748b;">
                                <?php echo __('Relation');?>: <strong style="color:#1e293b;"><?php echo h($relLabel);?></strong>
                            </small>
                            <?php
                        } else {
                            echo '<span class="text-muted">-</span>';
                        }
                        ?>
                    </td>
                    <td><?php echo h($post['Property']['name']);?></td>
                    <td><?php echo h($post['Deal']['invoice_no']);?></td>
                    <?php
                    $statusText = isset($post['Realestateletter']['status']) ? trim((string)$post['Realestateletter']['status']) : 'Published';
                    $statusLabel = isset($letterStatusArr[$statusText]) ? $letterStatusArr[$statusText] : $statusText;
                    ?>
                    <td><span class="label label-<?php echo ($statusText==='Published') ? 'success' : 'danger';?>"><?php echo h($statusLabel);?></span></td>
                    <td class="wv-letter-actions-cell">
                        <div class="wv-letter-row-actions">
                        <?php
                        $editUrl=array('controller'=>'Realestateletters','action'=>'edit',$id);
                        $viewUrl=array('controller'=>'Realestateletters','action'=>'view',$id);
                        $printUrl=array('controller'=>'Realestateletters','action'=>'printletter',$id);
                        $pdfUrl=array('controller'=>'Realestateletters','action'=>'exportletterpdf',$id);
                        $toggleStatus = ($statusText==='Published') ? 'Unpublished' : 'Published';
                        $statusUrl = array('controller'=>'Realestateletters','action'=>'letterstatus',$id,$toggleStatus,'?' => array('project_id' => $lettersProjectId));
                        $smsUrl = array('controller'=>'Realestateletters','action'=>'sendsms',$id,'?' => array('project_id' => $lettersProjectId));
                        $whatsappUrl = array('controller'=>'Realestateletters','action'=>'whatsapp',$id,'?' => array('project_id' => $lettersProjectId));
                        $emailUrl = array('controller'=>'Realestateletters','action'=>'sendemail',$id,'?' => array('project_id' => $lettersProjectId));
                        echo $this->Html->link('<span class="fa fa-eye"></span>', $viewUrl, array('escape'=>false,'class'=>'btn btn-default btn-xs','title'=>__('View')));
                        echo ' ';
                        echo $this->Html->link('<span class="fa fa-edit"></span>', $editUrl, array('escape'=>false,'class'=>'btn btn-primary btn-xs','title'=>__('Edit')));
                        echo ' ';
                        echo $this->Html->link('<span class="fa fa-print"></span>', $printUrl, array('escape'=>false,'class'=>'btn btn-success btn-xs','title'=>__('Print'),'target'=>'_blank'));
                        echo ' ';
                        echo $this->Html->link('<span class="fa fa-file-pdf-o"></span>', $pdfUrl, array('escape'=>false,'class'=>'btn btn-danger btn-xs','title'=>__('Export PDF'),'target'=>'_blank'));
                        echo ' ';
                        echo $this->Html->link('<span class="fa fa-comment"></span>', $smsUrl, array('escape'=>false,'class'=>'btn btn-info btn-xs','title'=>__('Send SMS')));
                        echo ' ';
                        echo $this->Html->link('<span class="fa fa-whatsapp"></span>', $whatsappUrl, array('escape'=>false,'class'=>'btn btn-success btn-xs','title'=>__('Send Letter on WhatsApp'),'target'=>'_blank'));
                        echo ' ';
                        echo $this->Html->link('<span class="fa fa-envelope"></span>', $emailUrl, array('escape'=>false,'class'=>'btn btn-info btn-xs','title'=>__('Send Email')));
                        echo ' ';
                        echo $this->Html->link('<span class="fa '.($statusText==='Published' ? 'fa-toggle-on' : 'fa-toggle-off').'"></span>', $statusUrl, array('escape'=>false,'class'=>'btn btn-warning btn-xs','title'=>__('Change Status')));
                        echo ' ';
                        echo $this->Html->link('<span class="fa fa-trash"></span>','#',array('escape'=>false,'class'=>'btn btn-danger btn-xs','title'=>__('Delete'),'onclick'=>"check_perform_sdelete('$id');"));
                        ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; unset($post);?>
            </table>
        </div>
        <?php echo $this->Form->end();?>
        <?php echo $this->element('pagination');?>
    </div>
</div>
