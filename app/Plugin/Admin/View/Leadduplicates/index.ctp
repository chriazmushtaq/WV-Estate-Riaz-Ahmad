<?php
$rows = isset($rows) ? (array)$rows : array();
?>
<style type="text/css">
.wv-dup-page{background:#f6f9fd;border:1px solid #dbe6f4;border-radius:10px;padding:14px;}
.wv-dup-head{align-items:center;display:flex;gap:10px;justify-content:space-between;margin-bottom:12px;}
.wv-dup-head h2{color:#07152a;font-size:22px;font-weight:900;margin:0;}
.wv-dup-filter{background:#fff;border:1px solid #dbe6f4;border-radius:8px;margin-bottom:12px;padding:10px;}
.wv-dup-filter .form-control{border-radius:7px;font-weight:800;height:36px;min-width:190px;}
.wv-dup-table th{background:#07152a!important;color:#fff!important;font-size:11px!important;text-transform:uppercase;}
.wv-dup-table td{font-size:12px!important;vertical-align:middle!important;}
.wv-dup-empty{background:#fff;border:1px dashed #cbd5e1;border-radius:8px;color:#64748b;font-weight:900;padding:18px;text-align:center;}
@media(max-width:767px){.wv-dup-head{display:block}.wv-dup-filter .form-control,.wv-dup-filter .btn{margin-bottom:6px;width:100%;}.wv-dup-table{min-width:980px;}}
</style>
<div class="wv-dup-page">
    <?php echo $this->Session->flash();?>
    <div class="wv-dup-head">
        <h2><?php echo __('Duplicate Lead Detector');?></h2>
        <?php echo $this->Html->link('<span class="fa fa-plus"></span> '.__('Add Lead'),array('controller'=>'Leads','action'=>'leadadd'),array('escape'=>false,'class'=>'btn btn-success'));?>
    </div>
    <div class="wv-dup-filter">
        <?php echo $this->Form->create(false,array('type'=>'get','url'=>array('controller'=>'Leadduplicates','action'=>'index'),'class'=>'form-inline'));?>
        <?php echo $this->Form->input('mobile',array('value'=>$mobile,'label'=>false,'div'=>false,'class'=>'form-control','placeholder'=>__('Mobile')));?>
        <?php echo $this->Form->input('email',array('value'=>$email,'label'=>false,'div'=>false,'class'=>'form-control','placeholder'=>__('Email')));?>
        <?php echo $this->Form->input('keyword',array('value'=>$keyword,'label'=>false,'div'=>false,'class'=>'form-control','placeholder'=>__('Name / Deal / Property')));?>
        <button type="submit" class="btn btn-info"><span class="fa fa-search"></span> <?php echo __('Search');?></button>
        <?php echo $this->Html->link(__('Reset'),array('controller'=>'Leadduplicates','action'=>'index'),array('class'=>'btn btn-default'));?>
        <?php echo $this->Form->end();?>
    </div>
    <?php if($mobile==='' && $email==='' && $keyword===''){ ?>
        <div class="wv-dup-empty"><?php echo __('Enter mobile, email, or keyword to check previous lead history.');?></div>
    <?php } else { ?>
    <div class="table-responsive">
        <table class="table table-bordered table-striped wv-dup-table">
            <thead><tr><th><?php echo __('#');?></th><th><?php echo __('Lead');?></th><th><?php echo __('Father / Guardian');?></th><th><?php echo __('Mobile');?></th><th><?php echo __('Email');?></th><th><?php echo __('Agency');?></th><th><?php echo __('Agent');?></th><th><?php echo __('Property');?></th><th><?php echo __('Deal');?></th><th><?php echo __('Status');?></th><th><?php echo __('Follow Up');?></th><th><?php echo __('Action');?></th></tr></thead>
            <tbody>
            <?php if(!empty($rows)){ $i=1; foreach($rows as $row){ ?>
                <tr>
                    <td><?php echo $i++;?></td>
                    <td><?php echo h(!empty($row['Lead']['lead_name']) ? $row['Lead']['lead_name'] : (!empty($row['Client']['name']) ? $row['Client']['name'] : '-'));?></td>
                    <td>
                        <?php
                        $fatherName = !empty($row['Client']['fathername']) ? trim((string)$row['Client']['fathername']) : '';
                        $relationType = !empty($row['Client']['relation_type']) ? trim((string)$row['Client']['relation_type']) : '';
                        if($fatherName !== ''){
                            echo '<strong style="color:#1e293b;">' . h($fatherName) . '</strong>';
                            if($relationType !== ''){
                                echo '<small class="text-muted" style="display:block;font-size:11px;font-weight:600;">' . h($relationType) . '</small>';
                            }
                        } else {
                            echo '<span class="text-muted">-</span>';
                        }
                        ?>
                    </td>
                    <td><?php echo h(!empty($row['Lead']['lead_mobile']) ? $row['Lead']['lead_mobile'] : (!empty($row['Client']['mobile']) ? $row['Client']['mobile'] : $row['Client']['phone']));?></td>
                    <td><?php echo h(!empty($row['Lead']['lead_email']) ? $row['Lead']['lead_email'] : (!empty($row['Client']['email']) ? $row['Client']['email'] : '-'));?></td>
                    <td><?php echo h(!empty($row['Project']['name']) ? $row['Project']['name'] : '-');?></td>
                    <td><?php echo h(!empty($row['AssignedUser']['name']) ? $row['AssignedUser']['name'] : '-');?></td>
                    <td><?php echo h(!empty($row['Property']['name']) ? $row['Property']['name'] : '-');?></td>
                    <td><?php echo h(!empty($row['Deal']['invoice_no']) ? $row['Deal']['invoice_no'] : '-');?></td>
                    <td><?php echo h(!empty($row['Status']['name']) ? $row['Status']['name'] : '-');?></td>
                    <td><?php echo !empty($row['Lead']['follow_up']) ? h(date('d-M-Y h:i A',strtotime($row['Lead']['follow_up']))) : '-';?></td>
                    <td><?php echo $this->Html->link(__('View'),array('controller'=>'Leads','action'=>'leadview',(int)$row['Lead']['id']),array('class'=>'btn btn-xs btn-info'));?></td>
                </tr>
            <?php }}else{ ?>
                <tr><td colspan="12" class="text-center"><?php echo __('No matching lead history found.');?></td></tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
    <?php echo $this->element('pagination');?>
    <?php } ?>
</div>
