<?php
$approvalOptions = array('Draft'=>__('Draft'),'Approved'=>__('Approved'),'Issued'=>__('Issued'));
$projectId = isset($projectId) ? (int)$projectId : 0;
$approvalStatus = isset($approvalStatus) ? (string)$approvalStatus : '';
$keyword = isset($keyword) ? (string)$keyword : '';
?>
<style type="text/css">
.wv-approval-page{background:#f6f9fd;border:1px solid #dbe6f4;border-radius:10px;padding:14px;}
.wv-approval-head{align-items:center;display:flex;gap:10px;justify-content:space-between;margin-bottom:12px;}
.wv-approval-head h2{color:#07152a;font-size:22px;font-weight:900;margin:0;}
.wv-approval-filter{background:#fff;border:1px solid #dbe6f4;border-radius:8px;margin-bottom:12px;padding:10px;}
.wv-approval-filter .form-control{border-radius:7px;font-weight:800;height:36px;}
.wv-approval-table th{background:#07152a!important;color:#fff!important;font-size:11px!important;text-transform:uppercase;}
.wv-approval-table td{font-size:12px!important;vertical-align:middle!important;}
.wv-approval-status{border-radius:999px;display:inline-block;font-size:10px;font-weight:900;padding:5px 9px;text-transform:uppercase;}
.wv-approval-status.Draft{background:#fef3c7;color:#92400e;}
.wv-approval-status.Approved{background:#dbeafe;color:#1d4ed8;}
.wv-approval-status.Issued{background:#dcfce7;color:#047857;}
.wv-approval-actions{display:flex;flex-wrap:wrap;gap:4px;}
.wv-approval-actions .btn{border:0;border-radius:6px;font-size:11px;font-weight:900;padding:6px 8px;}
@media(max-width:767px){.wv-approval-head{display:block}.wv-approval-head .btn{margin-top:8px;width:100%;}.wv-approval-table{min-width:920px;}}
</style>
<div class="wv-approval-page">
    <?php echo $this->Session->flash();?>
    <div class="wv-approval-head">
        <h2><?php echo __('Letter Approval Flow');?></h2>
        <?php echo $this->Html->link('<span class="fa fa-files-o"></span> '.__('All Letters'), array('controller'=>'Realestateletters','action'=>'index','?'=>array('project_id'=>$projectId)), array('escape'=>false,'class'=>'btn btn-default'));?>
    </div>
    <div class="wv-approval-filter">
        <?php echo $this->Form->create(false,array('type'=>'get','url'=>array('controller'=>'Realestateletters','action'=>'approvals'),'class'=>'form-inline'));?>
        <?php if(!empty($lettersCanUseAgencyFilters)){ ?>
            <?php echo $this->Form->input('project_id',array('type'=>'select','options'=>(array)$lettersProjectName,'empty'=>__('All Agency'),'value'=>$projectId,'label'=>false,'div'=>false,'class'=>'form-control'));?>
        <?php } else { ?>
            <?php echo $this->Form->hidden('project_id',array('value'=>$projectId));?>
        <?php } ?>
        <?php echo $this->Form->input('approval_status',array('type'=>'select','options'=>$approvalOptions,'empty'=>__('All Status'),'value'=>$approvalStatus,'label'=>false,'div'=>false,'class'=>'form-control'));?>
        <?php echo $this->Form->input('keyword',array('value'=>$keyword,'label'=>false,'div'=>false,'class'=>'form-control','placeholder'=>__('Search client, property, ref no')));?>
        <button type="submit" class="btn btn-info"><span class="fa fa-search"></span> <?php echo __('Search');?></button>
        <?php echo $this->Html->link(__('Reset'), array('controller'=>'Realestateletters','action'=>'approvals','?'=>array('project_id'=>$projectId)), array('class'=>'btn btn-default'));?>
        <?php echo $this->Form->end();?>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-striped wv-approval-table">
            <thead>
                <tr>
                    <th><?php echo __('#');?></th>
                    <th><?php echo __('Ref No');?></th>
                    <th><?php echo __('Date');?></th>
                    <th><?php echo __('Client');?></th>
                    <th><?php echo __('Property');?></th>
                    <th><?php echo __('Deal');?></th>
                    <th><?php echo __('Status');?></th>
                    <th><?php echo __('Approval');?></th>
                    <th><?php echo __('Actions');?></th>
                </tr>
            </thead>
            <tbody>
                <?php if(!empty($letters)){ $i=1; foreach($letters as $row){ $letter=$row['Realestateletter']; $status=!empty($letter['approval_status']) ? $letter['approval_status'] : (!empty($letter['status']) && $letter['status']==='Published' ? 'Issued' : 'Draft'); ?>
                <tr>
                    <td><?php echo $i++;?></td>
                    <td><?php echo h(!empty($letter['reference_no']) ? $letter['reference_no'] : 'LTR-'.$letter['id']);?></td>
                    <td><?php echo !empty($letter['letter_date']) ? h(date('d-M-Y',strtotime($letter['letter_date']))) : '-';?></td>
                    <td><?php echo h(!empty($row['Client']['name']) ? $row['Client']['name'] : '-');?></td>
                    <td><?php echo h(!empty($row['Property']['name']) ? $row['Property']['name'] : '-');?></td>
                    <td><?php echo h(!empty($row['Deal']['invoice_no']) ? $row['Deal']['invoice_no'] : '-');?></td>
                    <td><span class="label label-<?php echo (!empty($letter['status']) && $letter['status']==='Published') ? 'success' : 'warning';?>"><?php echo h(!empty($letter['status']) && $letter['status']==='Published' ? __('Issued') : __('Draft/Hidden'));?></span></td>
                    <td><span class="wv-approval-status <?php echo h($status);?>"><?php echo h($status);?></span></td>
                    <td>
                        <div class="wv-approval-actions">
                            <?php echo $this->Html->link('<span class="fa fa-pencil"></span> '.__('Draft'), array('action'=>'draftletter',(int)$letter['id'],'?'=>array('project_id'=>$projectId)), array('escape'=>false,'class'=>'btn btn-warning'));?>
                            <?php echo $this->Html->link('<span class="fa fa-check"></span> '.__('Approve'), array('action'=>'approveletter',(int)$letter['id'],'?'=>array('project_id'=>$projectId)), array('escape'=>false,'class'=>'btn btn-info'));?>
                            <?php echo $this->Html->link('<span class="fa fa-certificate"></span> '.__('Issue'), array('action'=>'issueletter',(int)$letter['id'],'?'=>array('project_id'=>$projectId)), array('escape'=>false,'class'=>'btn btn-success'));?>
                            <?php echo $this->Html->link('<span class="fa fa-print"></span> '.__('Print'), array('action'=>'printletter',(int)$letter['id']), array('escape'=>false,'target'=>'_blank','class'=>'btn btn-primary'));?>
                        </div>
                    </td>
                </tr>
                <?php }}else{ ?>
                <tr><td colspan="9" class="text-center"><?php echo __('No letters found.');?></td></tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
    <?php echo $this->element('pagination');?>
</div>
