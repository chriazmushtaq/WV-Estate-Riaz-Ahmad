<?php
$agentRows = isset($agentRows) ? (array)$agentRows : array();
$agencyRows = isset($agencyRows) ? (array)$agencyRows : array();
$totals = isset($totals) ? (array)$totals : array();
?>
<style type="text/css">
.wv-perf-page{background:#f6f9fd;border:1px solid #dbe6f4;border-radius:10px;padding:14px;}
.wv-perf-head{align-items:center;display:flex;gap:10px;justify-content:space-between;margin-bottom:12px;}
.wv-perf-head h2{color:#07152a;font-size:22px;font-weight:900;margin:0;}
.wv-perf-filter,.wv-perf-card{background:#fff;border:1px solid #dbe6f4;border-radius:8px;margin-bottom:12px;padding:12px;}
.wv-perf-filter .form-control{border-radius:7px;font-weight:800;height:36px;min-width:150px;}
.wv-perf-stats{display:grid;gap:10px;grid-template-columns:repeat(7,minmax(0,1fr));margin-bottom:12px;}
.wv-perf-stat{background:#fff;border:1px solid #dbe6f4;border-radius:8px;padding:10px;}
.wv-perf-stat span{color:#64748b;display:block;font-size:10px;font-weight:900;text-transform:uppercase;}
.wv-perf-stat strong{color:#07152a;display:block;font-size:20px;font-weight:900;margin-top:4px;}
.wv-perf-table th{background:#07152a!important;color:#fff!important;font-size:11px!important;text-transform:uppercase;}
.wv-perf-table td{font-size:12px!important;vertical-align:middle!important;}
.wv-perf-card h3{color:#07152a;font-size:16px;font-weight:900;margin:0 0 10px;}
@media(max-width:1199px){.wv-perf-stats{grid-template-columns:repeat(3,minmax(0,1fr));}}
@media(max-width:767px){.wv-perf-head{display:block}.wv-perf-filter .form-control,.wv-perf-filter .btn{margin-bottom:6px;width:100%;}.wv-perf-stats{grid-template-columns:repeat(2,minmax(0,1fr));}.wv-perf-table{min-width:900px;}}
</style>
<div class="wv-perf-page">
    <?php echo $this->Session->flash();?>
    <div class="wv-perf-head"><h2><?php echo __('Agent / Agency Performance');?></h2></div>
    <div class="wv-perf-filter">
        <?php echo $this->Form->create(false,array('type'=>'get','url'=>array('controller'=>'Agentperformances','action'=>'index'),'class'=>'form-inline'));?>
        <?php if($userType!='AGY' && $userType!='AGT'){ echo $this->Form->input('project_id',array('type'=>'select','options'=>$agencyOptions,'empty'=>__('All Agency'),'value'=>$projectId,'label'=>false,'div'=>false,'class'=>'form-control')); } ?>
        <?php if($userType!='AGT'){ echo $this->Form->input('agent_id',array('type'=>'select','options'=>$agentOptions,'empty'=>__('All Agents'),'value'=>$agentId,'label'=>false,'div'=>false,'class'=>'form-control')); } ?>
        <?php echo $this->Form->input('date_from',array('type'=>'text','value'=>$dateFrom,'label'=>false,'div'=>false,'class'=>'form-control','placeholder'=>__('From YYYY-MM-DD')));?>
        <?php echo $this->Form->input('date_to',array('type'=>'text','value'=>$dateTo,'label'=>false,'div'=>false,'class'=>'form-control','placeholder'=>__('To YYYY-MM-DD')));?>
        <button type="submit" class="btn btn-info"><span class="fa fa-search"></span> <?php echo __('Filter');?></button>
        <?php echo $this->Html->link(__('Reset'),array('controller'=>'Agentperformances','action'=>'index'),array('class'=>'btn btn-default'));?>
        <?php echo $this->Form->end();?>
    </div>
    <div class="wv-perf-stats">
        <div class="wv-perf-stat"><span><?php echo __('Leads');?></span><strong><?php echo (int)$totals['leads'];?></strong></div>
        <div class="wv-perf-stat"><span><?php echo __('Properties');?></span><strong><?php echo (int)$totals['properties'];?></strong></div>
        <div class="wv-perf-stat"><span><?php echo __('Views');?></span><strong><?php echo (int)$totals['views'];?></strong></div>
        <div class="wv-perf-stat"><span><?php echo __('WhatsApp');?></span><strong><?php echo (int)$totals['whatsapp'];?></strong></div>
        <div class="wv-perf-stat"><span><?php echo __('Calls');?></span><strong><?php echo (int)$totals['calls'];?></strong></div>
        <div class="wv-perf-stat"><span><?php echo __('Deals');?></span><strong><?php echo (int)$totals['deals'];?></strong></div>
        <div class="wv-perf-stat"><span><?php echo __('Conversion');?></span><strong><?php echo h($totals['conversion_rate']);?>%</strong></div>
    </div>
    <div class="wv-perf-card">
        <h3><?php echo __('Agent Performance');?></h3>
        <div class="table-responsive">
            <table class="table table-bordered table-striped wv-perf-table">
                <thead><tr><th><?php echo __('Agent');?></th><th><?php echo __('Mobile');?></th><th><?php echo __('Assigned Properties');?></th><th><?php echo __('Views');?></th><th><?php echo __('Leads');?></th><th><?php echo __('WhatsApp');?></th><th><?php echo __('Calls');?></th><th><?php echo __('Emails');?></th><th><?php echo __('Deals');?></th><th><?php echo __('Conversion');?></th></tr></thead>
                <tbody>
                <?php if(!empty($agentRows)){ foreach($agentRows as $row){ ?>
                    <tr>
                        <td><?php echo h($row['agent_name']);?></td>
                        <td><?php echo h($row['mobile']);?></td>
                        <td><?php echo (int)$row['properties'];?></td>
                        <td><?php echo (int)$row['views'];?></td>
                        <td><?php echo (int)$row['leads'];?></td>
                        <td><?php echo (int)$row['whatsapp'];?></td>
                        <td><?php echo (int)$row['calls'];?></td>
                        <td><?php echo (int)$row['emails'];?></td>
                        <td><?php echo (int)$row['deals'];?></td>
                        <td><?php echo h($row['conversion_rate']);?>%</td>
                    </tr>
                <?php }}else{ ?><tr><td colspan="10" class="text-center"><?php echo __('No agent data found.');?></td></tr><?php } ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="wv-perf-card">
        <h3><?php echo __('Agency Performance');?></h3>
        <div class="table-responsive">
            <table class="table table-bordered table-striped wv-perf-table">
                <thead><tr><th><?php echo __('Agency');?></th><th><?php echo __('Properties');?></th><th><?php echo __('Views');?></th><th><?php echo __('Leads');?></th><th><?php echo __('WhatsApp');?></th><th><?php echo __('Calls');?></th><th><?php echo __('Emails');?></th><th><?php echo __('Deals');?></th><th><?php echo __('Conversion');?></th></tr></thead>
                <tbody>
                <?php if(!empty($agencyRows)){ foreach($agencyRows as $row){ ?>
                    <tr>
                        <td><?php echo h($row['agency_name']);?></td>
                        <td><?php echo (int)$row['properties'];?></td>
                        <td><?php echo (int)$row['views'];?></td>
                        <td><?php echo (int)$row['leads'];?></td>
                        <td><?php echo (int)$row['whatsapp'];?></td>
                        <td><?php echo (int)$row['calls'];?></td>
                        <td><?php echo (int)$row['emails'];?></td>
                        <td><?php echo (int)$row['deals'];?></td>
                        <td><?php echo h($row['conversion_rate']);?>%</td>
                    </tr>
                <?php }}else{ ?><tr><td colspan="9" class="text-center"><?php echo __('No agency data found.');?></td></tr><?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
