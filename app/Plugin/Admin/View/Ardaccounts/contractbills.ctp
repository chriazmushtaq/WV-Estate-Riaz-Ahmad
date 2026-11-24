<?php
$this->Js->JqueryEngine->jQueryObject = 'jQuery';
$this->Paginator->options(array('update' => '#resultDiv', 'evalScripts' => true));
$billForm = isset($this->request->data['Ardcontractbill']) ? $this->request->data['Ardcontractbill'] : array();
$isEdit = !empty($billForm['id']);
$tabProjectId = (!empty($currentProjectId) && (int)$currentProjectId > 0) ? (int)$currentProjectId : null;
$contractMap = isset($contractMap) ? $contractMap : array();
?>
<style type="text/css">
.ard-stat-card{background:#ffffff;border:1px solid #e2e8f0;border-radius:10px;padding:12px 16px;margin-bottom:15px;box-shadow:0 1px 3px rgba(0,0,0,0.05);}
.ard-stat-card h5{margin:0 0 5px 0;font-size:11px;text-transform:uppercase;color:#64748b;font-weight:700;letter-spacing:.5px;}
.ard-stat-card strong{font-size:20px;color:#0f172a;display:block;}
.ard-bill-pill{display:inline-block;padding:2px 8px;border-radius:12px;font-size:11px;font-weight:700;}
.ard-bill-certified{background:#e0f2fe;color:#0369a1;}
.ard-bill-paid{background:#dcfce7;color:#15803d;}
.ard-bill-partial{background:#fef3c7;color:#b45309;}
.ard-bill-cancelled{background:#fee2e2;color:#b91c1c;}
.ard-net-highlight{background:#f8fafc;padding:10px 15px;border-radius:8px;border:1px dashed #cbd5e1;margin-top:10px;}
</style>

<div id="resultDiv">
    <div class="page-title">
        <div class="title-env">
            <h1 class="title"><?php echo __('Contractor Running Bills & Interim Payment Certificates (IPC)');?></h1>
        </div>
    </div>

    <div class="panel">
        <div class="panel-heading">
            <div class="btn-group">
                <?php echo $this->Html->link('<span class="fa fa-home"></span>&nbsp;'.__('Construction'), array('controller'=>'Ardaccounts','action'=>'construction',$tabProjectId), array('escape'=>false,'class'=>'btn btn-default'));?>
                <?php echo $this->Html->link('<span class="fa fa-tasks"></span>&nbsp;'.__('Projects'), array('controller'=>'Ardaccounts','action'=>'constructionprojects',$tabProjectId), array('escape'=>false,'class'=>'btn btn-default'));?>
                <?php echo $this->Html->link('<span class="fa fa-briefcase"></span>&nbsp;'.__('Contracts'), array('controller'=>'Ardaccounts','action'=>'contracts',$tabProjectId), array('escape'=>false,'class'=>'btn btn-default'));?>
                <?php echo $this->Html->link('<span class="fa fa-file-text-o"></span>&nbsp;'.__('Running Bills (IPC)'), array('controller'=>'Ardaccounts','action'=>'contractbills',$tabProjectId), array('escape'=>false,'class'=>'btn btn-info active'));?>
                <?php echo $this->Html->link('<span class="fa fa-users"></span>&nbsp;'.__('Site Labour'), array('controller'=>'Ardaccounts','action'=>'labourcontrol',$tabProjectId), array('escape'=>false,'class'=>'btn btn-warning'));?>
                <button type="button" class="btn btn-success" data-toggle="modal" data-target="#ipcModal"><span class="fa fa-plus"></span>&nbsp;<?php echo __('Certify Running Bill (IPC)');?></button>
            </div>
        </div>
        <div class="panel-body">
            <?php echo $this->Session->flash();?>

            <?php
            $sumGross = 0;
            $sumRetention = 0;
            $sumNet = 0;
            $sumPaid = 0;
            foreach((array)$rows as $r) {
                $b = $r['Ardcontractbill'];
                $sumGross += (float)$b['gross_amount'];
                $sumRetention += (float)$b['retention_amount'];
                $sumNet += (float)$b['net_payable'];
                $sumPaid += (float)$b['paid_amount'];
            }
            ?>
            <div class="row">
                <div class="col-sm-3">
                    <div class="ard-stat-card">
                        <h5><?php echo __('Gross Work Certified');?></h5>
                        <strong>Rs. <?php echo number_format($sumGross, 2);?></strong>
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="ard-stat-card">
                        <h5><?php echo __('Retention Money Held');?></h5>
                        <strong style="color:#b45309;">Rs. <?php echo number_format($sumRetention, 2);?></strong>
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="ard-stat-card">
                        <h5><?php echo __('Net Payable to Contractors');?></h5>
                        <strong style="color:#0284c7;">Rs. <?php echo number_format($sumNet, 2);?></strong>
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="ard-stat-card">
                        <h5><?php echo __('Total Amount Paid');?></h5>
                        <strong style="color:#15803d;">Rs. <?php echo number_format($sumPaid, 2);?></strong>
                    </div>
                </div>
            </div>

            <?php echo $this->element('pagination');?>
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th style="width:35px;">#</th>
                            <th><?php echo __('IPC / Bill #');?></th>
                            <th><?php echo __('Date');?></th>
                            <th><?php echo __('Contractor');?></th>
                            <th><?php echo __('Work Order / Title');?></th>
                            <th><?php echo __('Project / Villa');?></th>
                            <th style="text-align:right;"><?php echo __('Gross Certified');?></th>
                            <th style="text-align:right;color:#b45309;"><?php echo __('Retention Ded.');?></th>
                            <th style="text-align:right;color:#d9534f;"><?php echo __('Other Ded.');?></th>
                            <th style="text-align:right;color:#0284c7;font-weight:700;"><?php echo __('Net Payable');?></th>
                            <th style="text-align:right;color:#15803d;font-weight:700;"><?php echo __('Paid');?></th>
                            <th style="text-align:center;"><?php echo __('Status');?></th>
                            <th style="width:110px;text-align:center;"><?php echo __('Action');?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($rows)):?>
                            <tr><td colspan="13" class="text-center" style="padding:20px;"><?php echo __('No contractor running bills (IPCs) recorded yet.');?></td></tr>
                        <?php else:
                            $pageParams = $this->Paginator->params();
                            $serial = 1 * $pageParams['limit'] * ($pageParams['page'] - 1) + 1;
                            foreach($rows as $row):
                                $b = $row['Ardcontractbill'];
                                $bid = (int)$b['id'];
                                $otherDed = (float)$b['material_deduction'] + (float)$b['advance_deduction'] + (float)$b['tax_deduction'];
                                $statusClass = 'ard-bill-' . strtolower(trim((string)$b['status']));
                        ?>
                            <tr>
                                <td><?php echo $serial++;?></td>
                                <td><strong><?php echo h($b['bill_no']);?></strong></td>
                                <td><?php echo date('d-M-Y', strtotime($b['bill_date']));?></td>
                                <td><?php echo h(isset($row['Ardvendor']['vendor_name']) ? $row['Ardvendor']['vendor_name'] : '-');?></td>
                                <td>
                                    <strong><?php echo h(isset($row['Ardcontract']['contract_no']) ? $row['Ardcontract']['contract_no'] : '');?></strong>
                                    <?php if(!empty($row['Ardcontract']['title'])):?>
                                        - <?php echo h($row['Ardcontract']['title']);?>
                                    <?php endif;?>
                                </td>
                                <td><?php echo h(isset($row['Ardconstructionproject']['name']) ? $row['Ardconstructionproject']['name'] : '-');?></td>
                                <td style="text-align:right;font-weight:600;">Rs. <?php echo number_format($b['gross_amount'], 2);?></td>
                                <td style="text-align:right;color:#b45309;">- Rs. <?php echo number_format($b['retention_amount'], 2);?> <small>(<?php echo number_format($b['retention_percent'],1);?>%)</small></td>
                                <td style="text-align:right;color:#d9534f;"><?php echo $otherDed > 0 ? ('- Rs. ' . number_format($otherDed, 2)) : '-';?></td>
                                <td style="text-align:right;font-weight:700;color:#0284c7;">Rs. <?php echo number_format($b['net_payable'], 2);?></td>
                                <td style="text-align:right;font-weight:700;color:#15803d;">Rs. <?php echo number_format($b['paid_amount'], 2);?></td>
                                <td style="text-align:center;"><span class="ard-bill-pill <?php echo $statusClass;?>"><?php echo h(ucfirst($b['status']));?></span></td>
                                <td style="text-align:center;">
                                    <?php echo $this->Html->link('<i class="fa fa-pencil"></i>', array('action'=>'contractbills',$tabProjectId,'edit'=>$bid), array('escape'=>false,'class'=>'btn btn-xs btn-primary','title'=>__('Edit Bill')));?>
                                    <?php echo $this->Html->link('<i class="fa fa-trash"></i>', array('action'=>'deletecontractbill',$bid,$tabProjectId), array('escape'=>false,'class'=>'btn btn-xs btn-danger','title'=>__('Delete Bill'),'confirm'=>__('Are you sure you want to delete this running bill?')));?>
                                </td>
                            </tr>
                        <?php endforeach; endif;?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Certify Running Bill (IPC) Modal -->
<div class="modal fade <?php echo $isEdit ? 'in' : '';?>" id="ipcModal" tabindex="-1" role="dialog" style="<?php echo $isEdit ? 'display:block;' : '';?>">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><?php echo $isEdit ? __('Edit Contractor Running Bill (IPC)') : __('Certify Contractor Running Bill / IPC');?></h4>
            </div>
            <?php echo $this->Form->create('Ardcontractbill', array('url' => array('action' => 'contractbills', $tabProjectId)));?>
            <div class="modal-body">
                <?php if($isEdit) { echo $this->Form->hidden('id'); } ?>
                <div class="row">
                    <?php if($userType == 'ADR'): ?>
                        <div class="col-sm-4"><?php echo $this->Form->input('project_id', array('label'=>__('Agency / Project'),'class'=>'form-control','options'=>$projectName,'value'=>$tabProjectId));?></div>
                    <?php else: ?>
                        <?php echo $this->Form->hidden('project_id', array('value'=>$agencyId));?>
                    <?php endif; ?>
                    <div class="col-sm-4"><?php echo $this->Form->input('contract_id', array('label'=>__('Select Work Order / Contract'),'class'=>'form-control','id'=>'ipcContractSelect','options'=>$contractList,'empty'=>__('-- Select Contract --'),'required'=>true));?></div>
                    <div class="col-sm-4"><?php echo $this->Form->input('bill_no', array('label'=>__('IPC / Running Bill #'),'class'=>'form-control','placeholder'=>'IPC-0001'));?></div>
                </div>
                <div class="row" style="margin-top:10px;">
                    <div class="col-sm-4"><?php echo $this->Form->input('bill_date', array('label'=>__('Certification Date'),'class'=>'form-control js-datepicker','type'=>'text','default'=>date('Y-m-d'),'required'=>true));?></div>
                    <div class="col-sm-4"><?php echo $this->Form->input('period_from', array('label'=>__('Measurement Period (From)'),'class'=>'form-control js-datepicker','type'=>'text','placeholder'=>'YYYY-MM-DD'));?></div>
                    <div class="col-sm-4"><?php echo $this->Form->input('period_to', array('label'=>__('Measurement Period (To)'),'class'=>'form-control js-datepicker','type'=>'text','placeholder'=>'YYYY-MM-DD'));?></div>
                </div>
                <hr style="margin:15px 0 10px 0;"/>
                <div class="row">
                    <div class="col-sm-3"><?php echo $this->Form->input('gross_amount', array('label'=>__('Gross Work Certified (Rs.)'),'class'=>'form-control','id'=>'ipcGross','type'=>'number','step'=>'0.01','required'=>true));?></div>
                    <div class="col-sm-3"><?php echo $this->Form->input('retention_percent', array('label'=>__('Retention Deduction (%)'),'class'=>'form-control','id'=>'ipcRetentionPct','type'=>'number','step'=>'0.1','default'=>'5.0'));?></div>
                    <div class="col-sm-3"><?php echo $this->Form->input('material_deduction', array('label'=>__('Material Deduction (Rs.)'),'class'=>'form-control','id'=>'ipcMaterialDed','type'=>'number','step'=>'0.01','default'=>'0.00','title'=>__('Value of cement, steel, etc. supplied by developer to contractor')));?></div>
                    <div class="col-sm-3"><?php echo $this->Form->input('advance_deduction', array('label'=>__('Advance Recovery (Rs.)'),'class'=>'form-control','id'=>'ipcAdvanceDed','type'=>'number','step'=>'0.01','default'=>'0.00'));?></div>
                </div>
                <div class="row" style="margin-top:10px;">
                    <div class="col-sm-3"><?php echo $this->Form->input('tax_deduction', array('label'=>__('WHT / Tax Ded. (Rs.)'),'class'=>'form-control','id'=>'ipcTaxDed','type'=>'number','step'=>'0.01','default'=>'0.00'));?></div>
                    <div class="col-sm-9">
                        <div class="ard-net-highlight">
                            <div class="row">
                                <div class="col-xs-6">
                                    <span style="font-size:12px;color:#64748b;"><?php echo __('Retention Amount Withheld:');?></span><br/>
                                    <strong id="retentionAmountPreview" style="color:#b45309;font-size:16px;">Rs. 0.00</strong>
                                </div>
                                <div class="col-xs-6 text-right">
                                    <span style="font-size:12px;color:#64748b;"><?php echo __('Net Bill Payable to Contractor:');?></span><br/>
                                    <strong id="netPayablePreview" style="color:#0284c7;font-size:18px;">Rs. 0.00</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row" style="margin-top:10px;">
                    <div class="col-sm-12"><?php echo $this->Form->input('note', array('label'=>__('Certification Remarks / Measurement Details'),'class'=>'form-control','type'=>'textarea','rows'=>2,'placeholder'=>'Measurement book reference (MB page #), work details verified by site engineer...'));?></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> <?php echo $isEdit ? __('Update Running Bill') : __('Certify & Post Running Bill');?></button>
                <?php if($isEdit):?>
                    <a href="<?php echo $this->Html->url(array('action'=>'contractbills',$tabProjectId));?>" class="btn btn-default"><?php echo __('Cancel');?></a>
                <?php else:?>
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Close');?></button>
                <?php endif;?>
            </div>
            <?php echo $this->Form->end();?>
        </div>
    </div>
</div>

<script type="text/javascript">
(function(){
    var contractMap = <?php echo json_encode($contractMap);?>;
    function calcNet() {
        var gross = parseFloat($('#ipcGross').val()) || 0;
        var retPct = parseFloat($('#ipcRetentionPct').val()) || 0;
        var matDed = parseFloat($('#ipcMaterialDed').val()) || 0;
        var advDed = parseFloat($('#ipcAdvanceDed').val()) || 0;
        var taxDed = parseFloat($('#ipcTaxDed').val()) || 0;

        var retAmt = (gross * (retPct / 100));
        var net = gross - retAmt - matDed - advDed - taxDed;

        $('#retentionAmountPreview').text('Rs. ' + retAmt.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        $('#netPayablePreview').text('Rs. ' + net.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
    }

    $('#ipcGross, #ipcRetentionPct, #ipcMaterialDed, #ipcAdvanceDed, #ipcTaxDed').on('input change', calcNet);
    $('#ipcContractSelect').on('change', function(){
        var cid = parseInt($(this).val(), 10);
        if(contractMap && contractMap[cid] && contractMap[cid].retention_percent) {
            $('#ipcRetentionPct').val(contractMap[cid].retention_percent);
        }
        calcNet();
    });
    calcNet();
})();
</script>
