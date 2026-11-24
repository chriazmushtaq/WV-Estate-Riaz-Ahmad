<?php
$this->Js->JqueryEngine->jQueryObject = 'jQuery';
$this->Paginator->options(array('update' => '#resultDiv', 'evalScripts' => true));
$contractForm = isset($this->request->data['Ardcontract']) ? $this->request->data['Ardcontract'] : array();
$isEdit = !empty($contractForm['id']);
$tabProjectId = (!empty($currentProjectId) && (int)$currentProjectId > 0) ? (int)$currentProjectId : null;
?>
<style type="text/css">
.ard-stat-card{background:#ffffff;border:1px solid #e2e8f0;border-radius:10px;padding:12px 16px;margin-bottom:15px;box-shadow:0 1px 3px rgba(0,0,0,0.05);}
.ard-stat-card h5{margin:0 0 5px 0;font-size:11px;text-transform:uppercase;color:#64748b;font-weight:700;letter-spacing:.5px;}
.ard-stat-card strong{font-size:20px;color:#0f172a;display:block;}
.ard-contract-pill{display:inline-block;padding:2px 8px;border-radius:12px;font-size:11px;font-weight:700;}
.ard-contract-active{background:#dcfce7;color:#15803d;}
.ard-contract-completed{background:#e0f2fe;color:#0369a1;}
.ard-contract-hold{background:#fef3c7;color:#b45309;}
.ard-contract-terminated{background:#fee2e2;color:#b91c1c;}
</style>

<div id="resultDiv">
    <div class="page-title">
        <div class="title-env">
            <h1 class="title"><?php echo __('Contractor Work Orders & Contracts');?></h1>
        </div>
    </div>

    <div class="panel">
        <div class="panel-heading">
            <div class="btn-group">
                <?php echo $this->Html->link('<span class="fa fa-home"></span>&nbsp;'.__('Construction'), array('controller'=>'Ardaccounts','action'=>'construction',$tabProjectId), array('escape'=>false,'class'=>'btn btn-default'));?>
                <?php echo $this->Html->link('<span class="fa fa-tasks"></span>&nbsp;'.__('Projects'), array('controller'=>'Ardaccounts','action'=>'constructionprojects',$tabProjectId), array('escape'=>false,'class'=>'btn btn-default'));?>
                <?php echo $this->Html->link('<span class="fa fa-briefcase"></span>&nbsp;'.__('Contracts'), array('controller'=>'Ardaccounts','action'=>'contracts',$tabProjectId), array('escape'=>false,'class'=>'btn btn-primary active'));?>
                <?php echo $this->Html->link('<span class="fa fa-file-text-o"></span>&nbsp;'.__('Running Bills (IPC)'), array('controller'=>'Ardaccounts','action'=>'contractbills',$tabProjectId), array('escape'=>false,'class'=>'btn btn-info'));?>
                <?php echo $this->Html->link('<span class="fa fa-users"></span>&nbsp;'.__('Site Labour'), array('controller'=>'Ardaccounts','action'=>'labourcontrol',$tabProjectId), array('escape'=>false,'class'=>'btn btn-warning'));?>
                <button type="button" class="btn btn-success" data-toggle="modal" data-target="#contractModal"><span class="fa fa-plus"></span>&nbsp;<?php echo __('Add Contract');?></button>
            </div>
        </div>
        <div class="panel-body">
            <?php echo $this->Session->flash();?>

            <?php
            $totalContractVal = 0;
            $totalCertifiedVal = 0;
            $totalPaidVal = 0;
            $totalRetentionVal = 0;
            foreach((array)$rows as $r) {
                $c = $r['Ardcontract'];
                $totalContractVal += (float)$c['contract_amount'];
                $cid = (int)$c['id'];
                if(isset($billStats[$cid])) {
                    $totalCertifiedVal += (float)$billStats[$cid]['total_certified'];
                    $totalPaidVal += (float)$billStats[$cid]['total_paid'];
                    $totalRetentionVal += (float)$billStats[$cid]['total_retention'];
                }
            }
            ?>
            <div class="row">
                <div class="col-sm-3">
                    <div class="ard-stat-card">
                        <h5><?php echo __('Total Contract Value');?></h5>
                        <strong>Rs. <?php echo number_format($totalContractVal, 2);?></strong>
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="ard-stat-card">
                        <h5><?php echo __('Total Certified (Billed)');?></h5>
                        <strong style="color:#0284c7;">Rs. <?php echo number_format($totalCertifiedVal, 2);?></strong>
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="ard-stat-card">
                        <h5><?php echo __('Retention Money Held');?></h5>
                        <strong style="color:#b45309;">Rs. <?php echo number_format($totalRetentionVal, 2);?></strong>
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="ard-stat-card">
                        <h5><?php echo __('Total Paid to Contractors');?></h5>
                        <strong style="color:#15803d;">Rs. <?php echo number_format($totalPaidVal, 2);?></strong>
                    </div>
                </div>
            </div>

            <?php echo $this->element('pagination');?>
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th style="width:35px;">#</th>
                            <th><?php echo __('Contract #');?></th>
                            <th><?php echo __('Contractor');?></th>
                            <th><?php echo __('Title / Scope');?></th>
                            <th><?php echo __('Project');?></th>
                            <th><?php echo __('Contract Type');?></th>
                            <th style="text-align:right;"><?php echo __('Contract Amount');?></th>
                            <th style="text-align:center;"><?php echo __('Retention %');?></th>
                            <th style="text-align:right;"><?php echo __('Certified (Gross)');?></th>
                            <th style="text-align:right;"><?php echo __('Net Paid');?></th>
                            <th style="text-align:center;"><?php echo __('Status');?></th>
                            <th style="width:140px;text-align:center;"><?php echo __('Action');?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($rows)):?>
                            <tr><td colspan="12" class="text-center" style="padding:20px;"><?php echo __('No contractor contracts found.');?></td></tr>
                        <?php else:
                            $pageParams = $this->Paginator->params();
                            $serial = 1 * $pageParams['limit'] * ($pageParams['page'] - 1) + 1;
                            foreach($rows as $row):
                                $c = $row['Ardcontract'];
                                $cid = (int)$c['id'];
                                $stats = isset($billStats[$cid]) ? $billStats[$cid] : array('total_certified'=>0,'total_retention'=>0,'total_paid'=>0);
                                $statusClass = 'ard-contract-' . strtolower(trim((string)$c['status']));
                        ?>
                            <tr>
                                <td><?php echo $serial++;?></td>
                                <td><strong><?php echo h($c['contract_no']);?></strong></td>
                                <td><?php echo h(isset($row['Ardvendor']['vendor_name']) ? $row['Ardvendor']['vendor_name'] : '-');?></td>
                                <td>
                                    <strong><?php echo h($c['title']);?></strong>
                                    <?php if(!empty($c['scope_of_work'])):?>
                                        <div style="font-size:11px;color:#64748b;"><?php echo h(substr($c['scope_of_work'], 0, 70));?>...</div>
                                    <?php endif;?>
                                </td>
                                <td><?php echo h(isset($row['Ardconstructionproject']['name']) ? $row['Ardconstructionproject']['name'] : '-');?></td>
                                <td><?php echo h(ucwords(str_replace('_', ' ', (string)$c['contract_type'])));?></td>
                                <td style="text-align:right;font-weight:700;">Rs. <?php echo number_format($c['contract_amount'], 2);?></td>
                                <td style="text-align:center;"><?php echo number_format($c['retention_percent'], 1);?>%</td>
                                <td style="text-align:right;color:#0284c7;font-weight:600;">Rs. <?php echo number_format($stats['total_certified'], 2);?></td>
                                <td style="text-align:right;color:#15803d;font-weight:600;">Rs. <?php echo number_format($stats['total_paid'], 2);?></td>
                                <td style="text-align:center;"><span class="ard-contract-pill <?php echo $statusClass;?>"><?php echo h(ucfirst($c['status']));?></span></td>
                                <td style="text-align:center;">
                                    <?php echo $this->Html->link('<i class="fa fa-file-text-o"></i>', array('action'=>'contractbills',$tabProjectId,'?'=>array('contract_id'=>$cid)), array('escape'=>false,'class'=>'btn btn-xs btn-info','title'=>__('View IPC Running Bills')));?>
                                    <?php echo $this->Html->link('<i class="fa fa-pencil"></i>', array('action'=>'contracts',$tabProjectId,'edit'=>$cid), array('escape'=>false,'class'=>'btn btn-xs btn-primary','title'=>__('Edit Contract')));?>
                                    <?php echo $this->Html->link('<i class="fa fa-trash"></i>', array('action'=>'deletecontract',$cid,$tabProjectId), array('escape'=>false,'class'=>'btn btn-xs btn-danger','title'=>__('Delete Contract'),'confirm'=>__('Are you sure you want to delete this contract?')));?>
                                </td>
                            </tr>
                        <?php endforeach; endif;?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add / Edit Contract Modal -->
<div class="modal fade <?php echo $isEdit ? 'in' : '';?>" id="contractModal" tabindex="-1" role="dialog" style="<?php echo $isEdit ? 'display:block;' : '';?>">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><?php echo $isEdit ? __('Edit Contractor Work Order / Contract') : __('Create Contractor Work Order / Contract');?></h4>
            </div>
            <?php echo $this->Form->create('Ardcontract', array('url' => array('action' => 'contracts', $tabProjectId)));?>
            <div class="modal-body">
                <?php if($isEdit) { echo $this->Form->hidden('id'); } ?>
                <div class="row">
                    <?php if($userType == 'ADR'): ?>
                        <div class="col-sm-4"><?php echo $this->Form->input('project_id', array('label'=>__('Agency / Project'),'class'=>'form-control','options'=>$projectName,'value'=>$tabProjectId));?></div>
                    <?php else: ?>
                        <?php echo $this->Form->hidden('project_id', array('value'=>$agencyId));?>
                    <?php endif; ?>
                    <div class="col-sm-4"><?php echo $this->Form->input('vendor_id', array('label'=>__('Contractor / Subcontractor'),'class'=>'form-control','options'=>$contractorList,'empty'=>__('-- Select Contractor --'),'required'=>true));?></div>
                    <div class="col-sm-4"><?php echo $this->Form->input('construction_project_id', array('label'=>__('Construction Site / Unit Project'),'class'=>'form-control','options'=>$constructionProjectList,'empty'=>__('-- Select Project / Villa --'),'required'=>true));?></div>
                </div>
                <div class="row" style="margin-top:10px;">
                    <div class="col-sm-4"><?php echo $this->Form->input('contract_no', array('label'=>__('Work Order # (Auto if blank)'),'class'=>'form-control','placeholder'=>'WO-2026-0001'));?></div>
                    <div class="col-sm-4"><?php echo $this->Form->input('title', array('label'=>__('Work Order Title'),'class'=>'form-control','placeholder'=>'e.g. Civil Grey Structure Labour & Tile Work','required'=>true));?></div>
                    <div class="col-sm-4"><?php echo $this->Form->input('contract_type', array('label'=>__('Contract Type'),'class'=>'form-control','options'=>array('item_rate'=>__('Item Rate / Measurement'),'lump_sum'=>__('Lump Sum Contract'),'per_sqft'=>__('Per Sq. Ft. Rate'))));?></div>
                </div>
                <div class="row" style="margin-top:10px;">
                    <div class="col-sm-4"><?php echo $this->Form->input('contract_amount', array('label'=>__('Total Contract Value (Rs.)'),'class'=>'form-control','type'=>'number','step'=>'0.01','required'=>true));?></div>
                    <div class="col-sm-4"><?php echo $this->Form->input('retention_percent', array('label'=>__('Retention Deduction (%)'),'class'=>'form-control','type'=>'number','step'=>'0.1','default'=>'5.0'));?></div>
                    <div class="col-sm-4"><?php echo $this->Form->input('status', array('label'=>__('Status'),'class'=>'form-control','options'=>array('active'=>__('Active'),'completed'=>__('Completed'),'hold'=>__('Hold'),'terminated'=>__('Terminated'))));?></div>
                </div>
                <div class="row" style="margin-top:10px;">
                    <div class="col-sm-6"><?php echo $this->Form->input('start_date', array('label'=>__('Commencement Date'),'class'=>'form-control js-datepicker','type'=>'text','placeholder'=>'YYYY-MM-DD'));?></div>
                    <div class="col-sm-6"><?php echo $this->Form->input('expected_end_date', array('label'=>__('Expected Completion Date'),'class'=>'form-control js-datepicker','type'=>'text','placeholder'=>'YYYY-MM-DD'));?></div>
                </div>
                <div class="row" style="margin-top:10px;">
                    <div class="col-sm-12"><?php echo $this->Form->input('scope_of_work', array('label'=>__('Scope of Work & Terms'),'class'=>'form-control','type'=>'textarea','rows'=>3,'placeholder'=>'Details of work items, milestone specifications, and payment terms...'));?></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> <?php echo $isEdit ? __('Update Contract') : __('Save Contract');?></button>
                <?php if($isEdit):?>
                    <a href="<?php echo $this->Html->url(array('action'=>'contracts',$tabProjectId));?>" class="btn btn-default"><?php echo __('Cancel');?></a>
                <?php else:?>
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Close');?></button>
                <?php endif;?>
            </div>
            <?php echo $this->Form->end();?>
        </div>
    </div>
</div>
