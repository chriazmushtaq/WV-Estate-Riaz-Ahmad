<?php
$this->Js->JqueryEngine->jQueryObject = 'jQuery';
$tabProjectId = (!empty($currentProjectId) && (int)$currentProjectId > 0) ? (int)$currentProjectId : null;
$labourStats = isset($labourStats) ? $labourStats : array('total_labours'=>0,'present_today'=>0,'total_paid'=>0);
$labours = isset($labours) ? $labours : array();
$labourList = isset($labourList) ? $labourList : array();
$recentAttendance = isset($recentAttendance) ? $recentAttendance : array();
$recentPayments = isset($recentPayments) ? $recentPayments : array();
$paymentLedgers = isset($paymentLedgers) ? $paymentLedgers : array();
?>
<style type="text/css">
.ard-stat-card{background:#ffffff;border:1px solid #e2e8f0;border-radius:10px;padding:12px 16px;margin-bottom:15px;box-shadow:0 1px 3px rgba(0,0,0,0.05);}
.ard-stat-card h5{margin:0 0 5px 0;font-size:11px;text-transform:uppercase;color:#64748b;font-weight:700;letter-spacing:.5px;}
.ard-stat-card strong{font-size:20px;color:#0f172a;display:block;}
.ard-att-pill{display:inline-block;padding:2px 8px;border-radius:12px;font-size:11px;font-weight:700;}
.ard-att-present{background:#dcfce7;color:#15803d;}
.ard-att-half_day{background:#fef3c7;color:#b45309;}
.ard-att-absent{background:#fee2e2;color:#b91c1c;}
.ard-tab-head{display:flex;gap:10px;border-bottom:2px solid #e2e8f0;margin-bottom:15px;}
.ard-tab-head a{padding:8px 16px;font-weight:700;font-size:13px;color:#64748b;text-decoration:none;border-bottom:2px solid transparent;margin-bottom:-2px;}
.ard-tab-head a.active{color:#0284c7;border-bottom-color:#0284c7;}
</style>

<div id="resultDiv">
    <div class="page-title">
        <div class="title-env">
            <h1 class="title"><?php echo __('Site Labour Muster Roll & Daily Wage Management');?></h1>
        </div>
    </div>

    <div class="panel">
        <div class="panel-heading">
            <div class="btn-group">
                <?php echo $this->Html->link('<span class="fa fa-home"></span>&nbsp;'.__('Construction'), array('controller'=>'Ardaccounts','action'=>'construction',$tabProjectId), array('escape'=>false,'class'=>'btn btn-default'));?>
                <?php echo $this->Html->link('<span class="fa fa-tasks"></span>&nbsp;'.__('Projects'), array('controller'=>'Ardaccounts','action'=>'constructionprojects',$tabProjectId), array('escape'=>false,'class'=>'btn btn-default'));?>
                <?php echo $this->Html->link('<span class="fa fa-briefcase"></span>&nbsp;'.__('Contracts'), array('controller'=>'Ardaccounts','action'=>'contracts',$tabProjectId), array('escape'=>false,'class'=>'btn btn-default'));?>
                <?php echo $this->Html->link('<span class="fa fa-file-text-o"></span>&nbsp;'.__('Running Bills (IPC)'), array('controller'=>'Ardaccounts','action'=>'contractbills',$tabProjectId), array('escape'=>false,'class'=>'btn btn-default'));?>
                <?php echo $this->Html->link('<span class="fa fa-users"></span>&nbsp;'.__('Site Labour'), array('controller'=>'Ardaccounts','action'=>'labourcontrol',$tabProjectId), array('escape'=>false,'class'=>'btn btn-warning active'));?>
                <?php echo $this->Html->link('<span class="fa fa-id-badge"></span>&nbsp;'.__('Staff HR Payroll'), array('controller'=>'Ardaccounts','action'=>'hrpayroll',$tabProjectId), array('escape'=>false,'class'=>'btn btn-default'));?>
                <button type="button" class="btn btn-success" data-toggle="modal" data-target="#labourModal"><span class="fa fa-user-plus"></span>&nbsp;<?php echo __('Add Worker');?></button>
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#attModal"><span class="fa fa-calendar-check-o"></span>&nbsp;<?php echo __('Mark Attendance');?></button>
                <button type="button" class="btn btn-info" data-toggle="modal" data-target="#payModal"><span class="fa fa-money"></span>&nbsp;<?php echo __('Disburse Wages');?></button>
            </div>
        </div>
        <div class="panel-body">
            <?php echo $this->Session->flash();?>

            <div class="row">
                <div class="col-sm-4">
                    <div class="ard-stat-card">
                        <h5><?php echo __('Registered Site Workers');?></h5>
                        <strong><?php echo (int)$labourStats['total_labours'];?> <?php echo __('Workers');?></strong>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="ard-stat-card">
                        <h5><?php echo __('Present Today on Site');?></h5>
                        <strong style="color:#15803d;"><?php echo (int)$labourStats['present_today'];?> <?php echo __('Present');?></strong>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="ard-stat-card">
                        <h5><?php echo __('Total Labour Wages Disbursed');?></h5>
                        <strong style="color:#0284c7;">Rs. <?php echo number_format($labourStats['total_paid'], 2);?></strong>
                    </div>
                </div>
            </div>

            <!-- Tabs: Workers Directory, Attendance Muster Roll, Wage Payments -->
            <ul class="nav nav-tabs">
                <li class="active"><a href="#tabWorkers" data-toggle="tab"><i class="fa fa-users"></i> <?php echo __('Site Workers Directory');?> (<?php echo count($labours);?>)</a></li>
                <li><a href="#tabAttendance" data-toggle="tab"><i class="fa fa-calendar-check-o"></i> <?php echo __('Daily Muster Roll / Attendance');?></a></li>
                <li><a href="#tabPayments" data-toggle="tab"><i class="fa fa-money"></i> <?php echo __('Wage Payment Vouchers');?></a></li>
            </ul>

            <div class="tab-content" style="padding-top:15px;">
                <!-- Tab 1: Workers Directory -->
                <div class="tab-pane active" id="tabWorkers">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th style="width:35px;">#</th>
                                    <th><?php echo __('Worker Name');?></th>
                                    <th><?php echo __('Trade / Category');?></th>
                                    <th><?php echo __('Phone');?></th>
                                    <th><?php echo __('Address');?></th>
                                    <th style="text-align:right;"><?php echo __('Daily Wage Rate');?></th>
                                    <th style="text-align:center;"><?php echo __('Status');?></th>
                                    <th style="width:80px;text-align:center;"><?php echo __('Action');?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($labours)):?>
                                    <tr><td colspan="8" class="text-center" style="padding:20px;"><?php echo __('No site labourers registered yet.');?></td></tr>
                                <?php else:
                                    $i = 1;
                                    foreach($labours as $l):
                                        $w = $l['Ardlabour'];
                                        $wid = (int)$w['id'];
                                ?>
                                    <tr>
                                        <td><?php echo $i++;?></td>
                                        <td><strong><?php echo h($w['full_name']);?></strong></td>
                                        <td><span class="label label-info"><?php echo h(!empty($w['labour_type']) ? $w['labour_type'] : __('General Mazdoor'));?></span></td>
                                        <td><?php echo h(!empty($w['phone']) ? $w['phone'] : '-');?></td>
                                        <td><?php echo h(!empty($w['address']) ? $w['address'] : '-');?></td>
                                        <td style="text-align:right;font-weight:700;">Rs. <?php echo number_format($w['daily_rate'], 2);?> / day</td>
                                        <td style="text-align:center;"><span class="label label-success"><?php echo h(ucfirst($w['status']));?></span></td>
                                        <td style="text-align:center;">
                                            <?php echo $this->Html->link('<i class="fa fa-trash"></i>', array('action'=>'deletelabour',$wid,$tabProjectId), array('escape'=>false,'class'=>'btn btn-xs btn-danger','title'=>__('Delete Worker'),'confirm'=>__('Are you sure you want to delete this worker?')));?>
                                        </td>
                                    </tr>
                                <?php endforeach; endif;?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tab 2: Attendance Muster Roll -->
                <div class="tab-pane" id="tabAttendance">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th style="width:35px;">#</th>
                                    <th><?php echo __('Date');?></th>
                                    <th><?php echo __('Worker Name');?></th>
                                    <th><?php echo __('Trade');?></th>
                                    <th style="text-align:center;"><?php echo __('Attendance Status');?></th>
                                    <th style="text-align:center;"><?php echo __('Units / Days');?></th>
                                    <th><?php echo __('Remarks');?></th>
                                    <th style="width:70px;text-align:center;"><?php echo __('Action');?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($recentAttendance)):?>
                                    <tr><td colspan="8" class="text-center" style="padding:20px;"><?php echo __('No attendance records found.');?></td></tr>
                                <?php else:
                                    $ai = 1;
                                    foreach($recentAttendance as $att):
                                        $a = $att['Ardlabourattendance'];
                                        $aid = (int)$a['id'];
                                        $stClass = 'ard-att-' . strtolower(trim((string)$a['attendance_status']));
                                ?>
                                    <tr>
                                        <td><?php echo $ai++;?></td>
                                        <td><?php echo date('d-M-Y', strtotime($a['attendance_date']));?></td>
                                        <td><strong><?php echo h(isset($att['Ardlabour']['full_name']) ? $att['Ardlabour']['full_name'] : '-');?></strong></td>
                                        <td><?php echo h(isset($att['Ardlabour']['labour_type']) ? $att['Ardlabour']['labour_type'] : '-');?></td>
                                        <td style="text-align:center;"><span class="ard-att-pill <?php echo $stClass;?>"><?php echo h(ucwords(str_replace('_',' ',(string)$a['attendance_status'])));?></span></td>
                                        <td style="text-align:center;"><?php echo number_format($a['units'], 1);?></td>
                                        <td><?php echo h(!empty($a['note']) ? $a['note'] : '-');?></td>
                                        <td style="text-align:center;">
                                            <?php echo $this->Html->link('<i class="fa fa-trash"></i>', array('action'=>'deletelabourattendance',$aid,$tabProjectId), array('escape'=>false,'class'=>'btn btn-xs btn-danger','title'=>__('Delete Entry'),'confirm'=>__('Delete attendance entry?')));?>
                                        </td>
                                    </tr>
                                <?php endforeach; endif;?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tab 3: Wage Payments -->
                <div class="tab-pane" id="tabPayments">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th style="width:35px;">#</th>
                                    <th><?php echo __('Payment Date');?></th>
                                    <th><?php echo __('Voucher / Ref #');?></th>
                                    <th><?php echo __('Worker Name');?></th>
                                    <th><?php echo __('Paid From Account');?></th>
                                    <th style="text-align:right;"><?php echo __('Amount Disbursed');?></th>
                                    <th><?php echo __('Remarks');?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($recentPayments)):?>
                                    <tr><td colspan="7" class="text-center" style="padding:20px;"><?php echo __('No wage disbursements recorded yet.');?></td></tr>
                                <?php else:
                                    $pi = 1;
                                    foreach($recentPayments as $pay):
                                        $p = $pay['Ardlabourpayment'];
                                ?>
                                    <tr>
                                        <td><?php echo $pi++;?></td>
                                        <td><?php echo date('d-M-Y', strtotime($p['payment_date']));?></td>
                                        <td><strong><?php echo h($p['reference_no']);?></strong></td>
                                        <td><strong><?php echo h(isset($pay['Ardlabour']['full_name']) ? $pay['Ardlabour']['full_name'] : '-');?></strong></td>
                                        <td><?php echo h(isset($pay['Ardledger']['name']) ? $pay['Ardledger']['name'] : '-');?></td>
                                        <td style="text-align:right;font-weight:700;color:#15803d;">Rs. <?php echo number_format($p['amount'], 2);?></td>
                                        <td><?php echo h(!empty($p['note']) ? $p['note'] : '-');?></td>
                                    </tr>
                                <?php endforeach; endif;?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal 1: Add Worker -->
<div class="modal fade" id="labourModal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><?php echo __('Register Site Worker / Labourer');?></h4>
            </div>
            <?php echo $this->Form->create('Ardlabour', array('url' => array('action' => 'labourcontrol', $tabProjectId)));?>
            <input type="hidden" name="_form_type" value="labour" />
            <div class="modal-body">
                <?php if($userType == 'ADR'): ?>
                    <div class="form-group"><?php echo $this->Form->input('project_id', array('label'=>__('Agency / Project'),'class'=>'form-control','options'=>$projectName,'value'=>$tabProjectId));?></div>
                <?php else: ?>
                    <?php echo $this->Form->hidden('project_id', array('value'=>$agencyId));?>
                <?php endif; ?>
                <div class="form-group"><?php echo $this->Form->input('full_name', array('label'=>__('Worker Full Name'),'class'=>'form-control','placeholder'=>'e.g. Muhammad Aslam (Mistri)','required'=>true));?></div>
                <div class="row">
                    <div class="col-sm-6 form-group">
                        <?php echo $this->Form->input('labour_type', array('label'=>__('Category / Trade'),'class'=>'form-control','options'=>array(
                            'Head Mistri' => __('Head Mistri / Foreman'),
                            'Mason' => __('Mason (Mistri)'),
                            'Carpenter' => __('Carpenter (Shuttering)'),
                            'Steel Fixer' => __('Steel Fixer'),
                            'Plumber' => __('Plumber'),
                            'Electrician' => __('Electrician'),
                            'Painter' => __('Painter'),
                            'Mazdoor' => __('Unskilled Mazdoor / Helper'),
                            'Chowkidar' => __('Site Security / Guard')
                        )));?>
                    </div>
                    <div class="col-sm-6 form-group">
                        <?php echo $this->Form->input('daily_rate', array('label'=>__('Daily Wage Rate (Rs.)'),'class'=>'form-control','type'=>'number','step'=>'10','placeholder'=>'e.g. 1500','required'=>true));?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6 form-group"><?php echo $this->Form->input('phone', array('label'=>__('Phone / Mobile #'),'class'=>'form-control','placeholder'=>'0300-xxxxxxx'));?></div>
                    <div class="col-sm-6 form-group"><?php echo $this->Form->input('status', array('label'=>__('Status'),'class'=>'form-control','options'=>array('active'=>__('Active'),'inactive'=>__('Inactive'))));?></div>
                </div>
                <div class="form-group"><?php echo $this->Form->input('address', array('label'=>__('Address / CNIC Details'),'class'=>'form-control'));?></div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> <?php echo __('Save Worker');?></button>
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Close');?></button>
            </div>
            <?php echo $this->Form->end();?>
        </div>
    </div>
</div>

<!-- Modal 2: Mark Attendance -->
<div class="modal fade" id="attModal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><?php echo __('Mark Site Daily Attendance');?></h4>
            </div>
            <?php echo $this->Form->create('Ardlabourattendance', array('url' => array('action' => 'labourcontrol', $tabProjectId)));?>
            <input type="hidden" name="_form_type" value="attendance" />
            <div class="modal-body">
                <?php if($userType == 'ADR'): ?>
                    <div class="form-group"><?php echo $this->Form->input('project_id', array('label'=>__('Agency / Project'),'class'=>'form-control','options'=>$projectName,'value'=>$tabProjectId));?></div>
                <?php else: ?>
                    <?php echo $this->Form->hidden('project_id', array('value'=>$agencyId));?>
                <?php endif; ?>
                <div class="form-group"><?php echo $this->Form->input('attendance_date', array('label'=>__('Date'),'class'=>'form-control js-datepicker','type'=>'text','default'=>date('Y-m-d'),'required'=>true));?></div>
                <div class="form-group"><?php echo $this->Form->input('labour_id', array('label'=>__('Select Worker'),'class'=>'form-control','options'=>$labourList,'empty'=>__('-- Select Worker --'),'required'=>true));?></div>
                <div class="row">
                    <div class="col-sm-6 form-group">
                        <?php echo $this->Form->input('attendance_status', array('label'=>__('Attendance Status'),'class'=>'form-control','options'=>array(
                            'present' => __('Full Day Present (1.0)'),
                            'half_day' => __('Half Day (0.5)'),
                            'absent' => __('Absent (0.0)')
                        )));?>
                    </div>
                    <div class="col-sm-6 form-group">
                        <?php echo $this->Form->input('units', array('label'=>__('Working Units / Days'),'class'=>'form-control','type'=>'number','step'=>'0.25','default'=>'1.0'));?>
                    </div>
                </div>
                <div class="form-group"><?php echo $this->Form->input('note', array('label'=>__('Remarks / Site Area'),'class'=>'form-control','placeholder'=>'e.g. Worked on Villa 10 first floor roof slab...'));?></div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> <?php echo __('Record Attendance');?></button>
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Close');?></button>
            </div>
            <?php echo $this->Form->end();?>
        </div>
    </div>
</div>

<!-- Modal 3: Disburse Wages -->
<div class="modal fade" id="payModal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><?php echo __('Disburse Site Labour Wages');?></h4>
            </div>
            <?php echo $this->Form->create('Ardlabourpayment', array('url' => array('action' => 'labourcontrol', $tabProjectId)));?>
            <input type="hidden" name="_form_type" value="payment" />
            <div class="modal-body">
                <?php if($userType == 'ADR'): ?>
                    <div class="form-group"><?php echo $this->Form->input('project_id', array('label'=>__('Agency / Project'),'class'=>'form-control','options'=>$projectName,'value'=>$tabProjectId));?></div>
                <?php else: ?>
                    <?php echo $this->Form->hidden('project_id', array('value'=>$agencyId));?>
                <?php endif; ?>
                <div class="form-group"><?php echo $this->Form->input('labour_id', array('label'=>__('Worker / Labourer'),'class'=>'form-control','options'=>$labourList,'empty'=>__('-- Select Worker --'),'required'=>true));?></div>
                <div class="row">
                    <div class="col-sm-6 form-group"><?php echo $this->Form->input('payment_date', array('label'=>__('Payment Date'),'class'=>'form-control js-datepicker','type'=>'text','default'=>date('Y-m-d'),'required'=>true));?></div>
                    <div class="col-sm-6 form-group"><?php echo $this->Form->input('amount', array('label'=>__('Wage Amount (Rs.)'),'class'=>'form-control','type'=>'number','step'=>'1','required'=>true));?></div>
                </div>
                <div class="row">
                    <div class="col-sm-6 form-group"><?php echo $this->Form->input('debit_ledger_id', array('label'=>__('Paid From (Cash / Bank Account)'),'class'=>'form-control','options'=>$paymentLedgers,'empty'=>__('-- Select Account --'),'required'=>true));?></div>
                    <div class="col-sm-6 form-group"><?php echo $this->Form->input('reference_no', array('label'=>__('Voucher / Receipt # (Auto if empty)'),'class'=>'form-control','placeholder'=>'LPAY-0001'));?></div>
                </div>
                <div class="form-group"><?php echo $this->Form->input('note', array('label'=>__('Payment Narration'),'class'=>'form-control','placeholder'=>'e.g. Weekly wage disbursement for foundation masonry work...'));?></div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success"><i class="fa fa-money"></i> <?php echo __('Disburse & Post Voucher');?></button>
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Close');?></button>
            </div>
            <?php echo $this->Form->end();?>
        </div>
    </div>
</div>
