<?php
$isAdr = ($userType=='ADR');
$isEdit = !empty($this->request->data['Ardhpayroll']['id']);
$employeeScope = isset($employeeScope) ? (string)$employeeScope : 'all';
$employeeScopeLabel = isset($employeeScopeLabel) ? (string)$employeeScopeLabel : __('All Employees');
$employeeScopeQuery = (isset($employeeScopeQuery) && is_array($employeeScopeQuery)) ? $employeeScopeQuery : array();
$isLabourScope = ($employeeScope === 'labour');
$pageTitle = $isLabourScope ? __('Labour Payroll Entries') : __('HR Payroll Entries');
$attendanceSheetMonth = isset($attendanceSheetMonth) ? (string)$attendanceSheetMonth : date('Y-m');
$attendanceSheetEmployeeId = isset($attendanceSheetEmployeeId) ? (int)$attendanceSheetEmployeeId : 0;
$attendanceSheetRows = (isset($attendanceSheetRows) && is_array($attendanceSheetRows)) ? $attendanceSheetRows : array();
$attendanceSheetYear = isset($attendanceSheetYear) ? (int)$attendanceSheetYear : (int)date('Y');
$attendanceSheetCalendarEmployee = (isset($attendanceSheetCalendarEmployee) && is_array($attendanceSheetCalendarEmployee)) ? $attendanceSheetCalendarEmployee : array();
$attendanceSheetCalendarMap = (isset($attendanceSheetCalendarMap) && is_array($attendanceSheetCalendarMap)) ? $attendanceSheetCalendarMap : array();
$sheetFormProjectId = $isAdr ? 0 : (int)$currentProjectId;
$payrollMonthRaw = '';
if(!empty($this->request->data['Ardhpayroll']['payroll_month']))
{
    $payrollMonthRaw = trim((string)$this->request->data['Ardhpayroll']['payroll_month']);
}
elseif(!empty($attendanceSheetMonth))
{
    $payrollMonthRaw = trim((string)$attendanceSheetMonth);
}
if($payrollMonthRaw === '')
{
    $payrollMonthRaw = date('Y-m');
}
$payrollMonthDisplay = '';
$payrollMonthTs = strtotime($payrollMonthRaw . '-01');
if($payrollMonthTs !== false)
{
    $payrollMonthDisplay = date('d-M-Y', $payrollMonthTs);
}
if($payrollMonthDisplay === '')
{
    $payrollMonthDisplay = date('d-M-Y');
}
$weekdayShort = array(__('Mon'), __('Tue'), __('Wed'), __('Thu'), __('Fri'), __('Sat'), __('Sun'));
$monthNames = array(
    1 => __('January'),
    2 => __('February'),
    3 => __('March'),
    4 => __('April'),
    5 => __('May'),
    6 => __('June'),
    7 => __('July'),
    8 => __('August'),
    9 => __('September'),
    10 => __('October'),
    11 => __('November'),
    12 => __('December')
);
?>
<style type="text/css">
.ats-year-head{margin-bottom:10px;padding:10px 12px;border:1px solid #d6e2ef;background:#f5f9ff;}
.ats-legend{margin-bottom:10px;}
.ats-legend .label{display:inline-block;margin-right:6px;padding:4px 7px;font-size:11px;}
.ats-calendar-col{margin-bottom:14px;}
.ats-month-card{border:1px solid #dce5f0;background:#fff;}
.ats-month-title{padding:7px 10px;background:#eef4fb;border-bottom:1px solid #dce5f0;font-weight:700;color:#27415d;}
.ats-month-grid{width:100%;border-collapse:collapse;table-layout:fixed;}
.ats-month-grid th{font-size:11px;font-weight:600;text-align:center;padding:5px 2px;border:1px solid #eef2f7;background:#fafcff;color:#5a6f84;}
.ats-month-grid td{height:42px;vertical-align:top;padding:3px;border:1px solid #eef2f7;}
.ats-empty-day{background:#fbfcfe;}
.ats-day-no{font-size:11px;font-weight:700;line-height:1;color:#263645;}
.ats-day-status{margin-top:4px;font-size:10px;line-height:1.1;display:inline-block;padding:2px 4px;border-radius:2px;}
.ats-status-none{background:#f4f6f8;color:#6f7f8f;}
.ats-status-present{background:#dff0d8;color:#2f6b2f;}
.ats-status-absent{background:#f2dede;color:#a94442;}
.ats-status-leave{background:#fcf8e3;color:#8a6d3b;}
.ats-status-half_day{background:#d9edf7;color:#31708f;}
</style>
<div class="page-title"><div class="title-env"><h1 class="title"><?php echo h($pageTitle);?></h1></div></div>
<div class="panel">
    <div class="panel-heading">
        <div class="btn-group">
            <?php echo $this->Html->link('<span class="fa fa-arrow-left"></span> '.__('HR Payroll'),array('controller'=>'Ardaccounts','action'=>'hrpayroll',$currentProjectId,'?' => $employeeScopeQuery),array('escape'=>false,'class'=>'btn btn-default'));?>
            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#BulkPayrollModal"><span class="fa fa-cogs"></span> <?php echo __('Generate Bulk Payroll');?></button>
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#HrPayrollEntryModal"><span class="fa fa-plus"></span> <?php echo __('New Payroll Entry');?></button>
            <?php echo $this->Html->link('<span class="fa fa-bank"></span> '.__('Bank Advice Sheet'),array('controller'=>'Ardaccounts','action'=>'bankadvicesheet',$currentProjectId,'?' => array('month' => $attendanceSheetMonth)),array('escape'=>false,'class'=>'btn btn-info','target'=>'_blank'));?>
            <?php echo $this->Html->link('<span class="fa fa-users"></span> '.__('Site Labour Muster Roll'),array('controller'=>'Ardaccounts','action'=>'labourcontrol',$currentProjectId),array('escape'=>false,'class'=>'btn btn-default'));?>
            <?php if($isLabourScope){ ?>
                <?php echo $this->Html->link('<span class="fa fa-users"></span> '.__('All Payroll Entries'),array('controller'=>'Ardaccounts','action'=>'hrpayrollentries',$currentProjectId),array('escape'=>false,'class'=>'btn btn-default'));?>
            <?php } else { ?>
                <?php echo $this->Html->link('<span class="fa fa-filter"></span> '.__('Daily Labour Scope'),array('controller'=>'Ardaccounts','action'=>'hrpayrollentries',$currentProjectId,'?' => array('employee_scope' => 'labour')),array('escape'=>false,'class'=>'btn btn-warning'));?>
            <?php } ?>
        </div>
    </div>
    <div class="panel-body">
        <?php echo $this->Session->flash();?>
        <div class="alert alert-info" style="margin-bottom:15px;">
            <strong><?php echo __('Current Scope');?>:</strong> <?php echo h($employeeScopeLabel);?>
        </div>
        <div class="panel panel-default" style="margin-bottom:15px;">
            <div class="panel-heading"><strong><?php echo __('Attendance Sheet (Employee + Salary/Commission Estimate)');?></strong></div>
            <div class="panel-body">
                <form class="form-inline" method="get" action="<?php echo $this->Html->url(array('controller'=>'Ardaccounts','action'=>'hrpayrollentries',$sheetFormProjectId));?>">
                    <input type="hidden" name="employee_scope" value="<?php echo h($employeeScope);?>">
                    <?php if($isAdr){ ?>
                    <div class="form-group" style="margin-right:10px;">
                        <label><?php echo __('Agency');?></label><br>
                        <select class="form-control" name="project_id" id="HrSheetProjectId">
                            <option value=""><?php echo __('Please Select');?></option>
                            <?php foreach((array)$projectName as $projectId => $projectTitle){ ?>
                            <option value="<?php echo (int)$projectId;?>" <?php echo ((int)$projectId === (int)$currentProjectId) ? 'selected="selected"' : '';?>><?php echo h($projectTitle);?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <?php } ?>
                    <div class="form-group" style="margin-right:10px;">
                        <label><?php echo __('Month');?></label><br>
                        <input type="month" class="form-control" name="sheet_month" value="<?php echo h($attendanceSheetMonth);?>">
                    </div>
                    <div class="form-group" style="margin-right:10px; min-width:260px;">
                        <label><?php echo __('Employee');?></label><br>
                        <select class="form-control" name="sheet_employee_id" style="min-width:260px;">
                            <option value="0"><?php echo __('All Employees');?></option>
                            <?php foreach((array)$employeeList as $employeeId => $employeeTitle){ ?>
                            <option value="<?php echo (int)$employeeId;?>" <?php echo ((int)$employeeId === (int)$attendanceSheetEmployeeId) ? 'selected="selected"' : '';?>><?php echo h($employeeTitle);?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="form-group" style="margin-top:24px;">
                        <button type="submit" class="btn btn-primary"><span class="fa fa-filter"></span> <?php echo __('Apply');?></button>
                        <?php echo $this->Html->link('<span class="fa fa-refresh"></span> '.__('Reset'),array('controller'=>'Ardaccounts','action'=>'hrpayrollentries',$currentProjectId,'?' => $employeeScopeQuery),array('escape'=>false,'class'=>'btn btn-default'));?>
                    </div>
                </form>
                <div class="table-responsive" style="margin-top:12px;">
                    <table class="table table-bordered table-condensed table-striped">
                        <tr>
                            <th><?php echo __('Employee');?></th>
                            <th><?php echo __('Salary Type');?></th>
                            <th><?php echo __('Base Salary');?></th>
                            <th><?php echo __('Working');?></th>
                            <th><?php echo __('Present');?></th>
                            <th><?php echo __('Absent');?></th>
                            <th><?php echo __('Leave');?></th>
                            <th><?php echo __('Half');?></th>
                            <th><?php echo __('OT Hours');?></th>
                            <th><?php echo __('Gross Est.');?></th>
                            <th><?php echo __('Commission Earned');?></th>
                            <th><?php echo __('Commission Paid');?></th>
                            <th><?php echo __('Commission Balance');?></th>
                            <th><?php echo __('Net Est.');?></th>
                            <th><?php echo __('Action');?></th>
                        </tr>
                        <?php if(empty($attendanceSheetRows)){ ?>
                        <tr><td colspan="15" class="text-center text-muted"><?php echo __('No attendance sheet rows found for selected filter.');?></td></tr>
                        <?php } ?>
                        <?php foreach((array)$attendanceSheetRows as $sheetRow){ ?>
                        <tr>
                            <td><?php echo h($sheetRow['employee_name']);?><?php if(!empty($sheetRow['employee_code'])){ ?><br><small><?php echo h($sheetRow['employee_code']);?></small><?php } ?></td>
                            <td><?php echo h(ucfirst((string)$sheetRow['salary_type']));?></td>
                            <td><?php echo $currency.$this->Number->format((float)$sheetRow['base_salary']);?></td>
                            <td><?php echo h($sheetRow['working_days']);?></td>
                            <td><?php echo h($sheetRow['present_days']);?></td>
                            <td><?php echo h($sheetRow['absent_days']);?></td>
                            <td><?php echo h($sheetRow['leave_days']);?></td>
                            <td><?php echo h($sheetRow['half_days']);?></td>
                            <td><?php echo h($sheetRow['overtime_hours']);?></td>
                            <td><?php echo $currency.$this->Number->format((float)$sheetRow['gross_pay']);?></td>
                            <td><?php echo $currency.$this->Number->format((float)$sheetRow['commission_earned']);?></td>
                            <td><?php echo $currency.$this->Number->format((float)$sheetRow['commission_paid']);?></td>
                            <td><?php echo $currency.$this->Number->format((float)$sheetRow['commission_balance']);?></td>
                            <td><?php echo $currency.$this->Number->format((float)$sheetRow['net_pay_estimated']);?></td>
                            <td>
                                <?php
                                echo $this->Html->link('<span class="fa fa-check"></span> '.__('Use in Payroll'),array('controller'=>'Ardaccounts','action'=>'hrpayrollentries',$currentProjectId,'?' => array(
                                    'sheet_month' => $attendanceSheetMonth,
                                    'sheet_employee_id' => (int)$sheetRow['employee_id'],
                                    'prefill_employee_id' => (int)$sheetRow['employee_id'],
                                    'prefill_month' => $attendanceSheetMonth,
                                    'employee_scope' => $employeeScope
                                )),array('escape'=>false,'class'=>'btn btn-xs btn-success'));
                                echo ' '.$this->Html->link('<span class="fa fa-calendar-plus-o"></span> '.__('Add Attendance'),array('controller'=>'Ardaccounts','action'=>'hremployees',(int)$sheetRow['project_id'],'?' => array(
                                    'employee_scope' => $employeeScope,
                                    'attendance_employee' => (int)$sheetRow['employee_id']
                                )),array('escape'=>false,'class'=>'btn btn-xs btn-warning'));
                                if(!empty($sheetRow['linked_user_id']))
                                {
                                    echo ' '.$this->Html->link('<span class="fa fa-wallet"></span> '.__('Commission Ledger'),array('controller'=>'AwalletHistories','action'=>'index',(int)$sheetRow['linked_user_id'],'?' => array('tab' => 'paid')),array('escape'=>false,'class'=>'btn btn-xs btn-info'));
                                }
                                ?>
                            </td>
                        </tr>
                        <?php } ?>
                    </table>
                </div>
            </div>
        </div>
        <div class="panel panel-default" style="margin-bottom:15px;">
            <div class="panel-heading"><strong><?php echo __('ATS Yearly Attendance Calendar');?></strong></div>
            <div class="panel-body">
                <?php
                $calendarEmployeeId = isset($attendanceSheetCalendarEmployee['Ardhremployee']['id']) ? (int)$attendanceSheetCalendarEmployee['Ardhremployee']['id'] : 0;
                if($calendarEmployeeId <= 0 && (int)$attendanceSheetEmployeeId > 0)
                {
                    $calendarEmployeeId = (int)$attendanceSheetEmployeeId;
                }
                if($calendarEmployeeId <= 0)
                {
                ?>
                    <div class="alert alert-warning" style="margin-bottom:0;"><?php echo __('Select one employee in Attendance Sheet filter to view full yearly ATS calendar.');?></div>
                <?php
                }
                else
                {
                    $calendarEmployeeName = '';
                    $calendarEmployeeCode = '';
                    if(!empty($attendanceSheetCalendarEmployee['Ardhremployee']))
                    {
                        $calendarEmployeeName = trim((string)(isset($attendanceSheetCalendarEmployee['Ardhremployee']['full_name']) ? $attendanceSheetCalendarEmployee['Ardhremployee']['full_name'] : ''));
                        $calendarEmployeeCode = trim((string)(isset($attendanceSheetCalendarEmployee['Ardhremployee']['employee_code']) ? $attendanceSheetCalendarEmployee['Ardhremployee']['employee_code'] : ''));
                    }
                    if($calendarEmployeeName === '' && isset($employeeList[$calendarEmployeeId]))
                    {
                        $calendarEmployeeName = (string)$employeeList[$calendarEmployeeId];
                    }
                    if($calendarEmployeeName === '')
                    {
                        $calendarEmployeeName = __('Employee').' #'.$calendarEmployeeId;
                    }
                ?>
                <div class="ats-year-head">
                    <strong><?php echo __('Employee');?>:</strong> <?php echo h($calendarEmployeeName);?>
                    <?php if($calendarEmployeeCode !== ''){ ?> | <strong><?php echo __('Code');?>:</strong> <?php echo h($calendarEmployeeCode);?><?php } ?>
                    | <strong><?php echo __('Year');?>:</strong> <?php echo (int)$attendanceSheetYear;?>
                </div>
                <div class="ats-legend">
                    <span class="label ats-status-present"><?php echo __('Present (P)');?></span>
                    <span class="label ats-status-absent"><?php echo __('Absent (A)');?></span>
                    <span class="label ats-status-leave"><?php echo __('Leave (L)');?></span>
                    <span class="label ats-status-half_day"><?php echo __('Half Day (H)');?></span>
                    <span class="label ats-status-none"><?php echo __('No Entry (-)');?></span>
                </div>
                <div class="row">
                    <?php for($monthNo = 1; $monthNo <= 12; $monthNo++){ ?>
                    <?php
                    $monthTs = strtotime(sprintf('%04d-%02d-01', (int)$attendanceSheetYear, (int)$monthNo));
                    if($monthTs === false)
                    {
                        continue;
                    }
                    $daysInMonth = (int)date('t', $monthTs);
                    $firstWeekday = (int)date('N', $monthTs); // 1=Mon ... 7=Sun
                    $dayCounter = 1;
                    $cellCursor = 1;
                    $totalCells = ($firstWeekday - 1) + $daysInMonth;
                    $weeks = (int)ceil($totalCells / 7);
                    if($weeks < 5)
                    {
                        $weeks = 5;
                    }
                    $monthTitle = isset($monthNames[$monthNo]) ? (string)$monthNames[$monthNo] : date('F', $monthTs);
                    ?>
                    <div class="col-md-4 col-sm-6 ats-calendar-col">
                        <div class="ats-month-card">
                            <div class="ats-month-title"><?php echo h($monthTitle).' '.(int)$attendanceSheetYear;?></div>
                            <table class="ats-month-grid">
                                <thead>
                                    <tr>
                                        <?php foreach($weekdayShort as $weekdayLabel){ ?>
                                        <th><?php echo h($weekdayLabel);?></th>
                                        <?php } ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php for($week = 1; $week <= $weeks; $week++){ ?>
                                    <tr>
                                        <?php for($weekday = 1; $weekday <= 7; $weekday++){ ?>
                                        <?php
                                        if($cellCursor < $firstWeekday || $dayCounter > $daysInMonth)
                                        {
                                            echo '<td class="ats-empty-day"></td>';
                                            $cellCursor++;
                                            continue;
                                        }
                                        $dateKey = sprintf('%04d-%02d-%02d', (int)$attendanceSheetYear, (int)$monthNo, (int)$dayCounter);
                                        $dayAttendance = isset($attendanceSheetCalendarMap[$dateKey]) ? (array)$attendanceSheetCalendarMap[$dateKey] : array();
                                        $dayStatus = strtolower(trim((string)(isset($dayAttendance['status']) ? $dayAttendance['status'] : '')));
                                        $statusClass = 'ats-status-none';
                                        $statusText = '-';
                                        $statusTitle = __('No Entry');
                                        if($dayStatus === 'present')
                                        {
                                            $statusClass = 'ats-status-present';
                                            $statusText = 'P';
                                            $statusTitle = __('Present');
                                        }
                                        elseif($dayStatus === 'absent')
                                        {
                                            $statusClass = 'ats-status-absent';
                                            $statusText = 'A';
                                            $statusTitle = __('Absent');
                                        }
                                        elseif($dayStatus === 'leave')
                                        {
                                            $statusClass = 'ats-status-leave';
                                            $statusText = 'L';
                                            $statusTitle = __('Leave');
                                        }
                                        elseif($dayStatus === 'half_day')
                                        {
                                            $statusClass = 'ats-status-half_day';
                                            $statusText = 'H';
                                            $statusTitle = __('Half Day');
                                        }
                                        $dayTooltip = date('d-M-Y', strtotime($dateKey)).' - '.$statusTitle;
                                        $inTime = trim((string)(isset($dayAttendance['in_time']) ? $dayAttendance['in_time'] : ''));
                                        $outTime = trim((string)(isset($dayAttendance['out_time']) ? $dayAttendance['out_time'] : ''));
                                        $otHours = isset($dayAttendance['overtime_hours']) ? (float)$dayAttendance['overtime_hours'] : 0;
                                        $note = trim((string)(isset($dayAttendance['note']) ? $dayAttendance['note'] : ''));
                                        if($inTime !== '' || $outTime !== '')
                                        {
                                            $dayTooltip .= ' | '.__('In').': '.$inTime.' '.__('Out').': '.$outTime;
                                        }
                                        if($otHours > 0)
                                        {
                                            $dayTooltip .= ' | '.__('OT').': '.rtrim(rtrim(number_format($otHours, 2, '.', ''), '0'), '.');
                                        }
                                        if($note !== '')
                                        {
                                            $dayTooltip .= ' | '.$note;
                                        }
                                        ?>
                                        <td title="<?php echo h($dayTooltip);?>">
                                            <div class="ats-day-no"><?php echo sprintf('%02d', (int)$dayCounter);?></div>
                                            <span class="ats-day-status <?php echo h($statusClass);?>"><?php echo h($statusText);?></span>
                                        </td>
                                        <?php
                                        $dayCounter++;
                                        $cellCursor++;
                                        ?>
                                        <?php } ?>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php } ?>
                </div>
                <?php } ?>
            </div>
        </div>

        <div style="margin-bottom:12px;">
            <a href="javascript:void(0);" class="btn btn-success" id="OpenHrPayrollEntryModalBtn"><span class="fa fa-plus"></span> <?php echo $isEdit ? __('Update Payroll Entry') : __('Create Payroll Entry');?></a>
            <?php if($isEdit){echo ' '.$this->Html->link('<span class="fa fa-close"></span> '.__('Cancel Edit'),array('controller'=>'Ardaccounts','action'=>'hrpayrollentries',$currentProjectId,'?' => $employeeScopeQuery),array('escape'=>false,'class'=>'btn btn-danger'));}?>
        </div>

        <hr>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <tr>
                    <th><?php echo __('Employee');?></th>
                    <th><?php echo __('Month');?></th>
                    <th><?php echo __('Net Pay');?></th>
                    <th><?php echo __('Commission');?></th>
                    <th><?php echo __('Paid');?></th>
                    <th><?php echo __('Due');?></th>
                    <th><?php echo __('Status');?></th>
                    <th><?php echo __('Action');?></th>
                </tr>
                <?php foreach($rows as $row): $rid=(int)$row['Ardhpayroll']['id']; ?>
                <tr>
                    <td><?php echo isset($employeeList[$row['Ardhpayroll']['employee_id']]) ? h($employeeList[$row['Ardhpayroll']['employee_id']]) : '-';?></td>
                    <td>
                        <?php
                        $rowPayrollMonth = trim((string)$row['Ardhpayroll']['payroll_month']);
                        $rowPayrollTs = ($rowPayrollMonth !== '') ? strtotime($rowPayrollMonth . '-01') : false;
                        echo ($rowPayrollTs !== false) ? h(date('d-M-Y', $rowPayrollTs)) : h($rowPayrollMonth);
                        ?>
                    </td>
                    <td><?php echo $currency.$this->Number->format($row['Ardhpayroll']['net_pay']);?></td>
                    <td><?php echo $currency.$this->Number->format(isset($row['Ardhpayroll']['commission_amount']) ? $row['Ardhpayroll']['commission_amount'] : 0);?></td>
                    <td><?php echo $currency.$this->Number->format($row['Ardhpayroll']['paid_amount']);?></td>
                    <td><?php echo $currency.$this->Number->format($row['Ardhpayroll']['due_amount']);?></td>
                    <td><?php echo h(ucfirst($row['Ardhpayroll']['status']));?></td>
                    <td>
                        <?php echo $this->Html->link('<span class="fa fa-print"></span>',array('controller'=>'Ardaccounts','action'=>'printpayslip',$rid),array('escape'=>false,'class'=>'btn btn-xs btn-info','target'=>'_blank','title'=>__('Print Salary Slip')));?>
                        <?php echo $this->Html->link('<span class="fa fa-edit"></span>',array('controller'=>'Ardaccounts','action'=>'hrpayrollentries',$currentProjectId,'edit:'.$rid,'?' => $employeeScopeQuery),array('escape'=>false,'class'=>'btn btn-xs btn-warning','title'=>__('Edit')));?>
                        <?php echo $this->Html->link('<span class="fa fa-trash"></span>',array('controller'=>'Ardaccounts','action'=>'deletehrpayrollentry',$rid,$currentProjectId,'?' => $employeeScopeQuery),array('escape'=>false,'class'=>'btn btn-xs btn-danger','onclick'=>"return confirm('".__('Are you sure?')."');",'title'=>__('Delete')));?>
                    </td>
                </tr>
                <?php endforeach;?>
            </table>
        </div>
        <?php echo $this->element('pagination');?>
    </div>
</div>

<div class="modal fade" id="BulkPayrollModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><span class="fa fa-cogs"></span> <?php echo __('1-Click Bulk Monthly Payroll Generator');?></h4>
            </div>
            <?php echo $this->Form->create('BulkPayroll', array('url' => array('controller' => 'Ardaccounts', 'action' => 'generatebulkpayroll', $currentProjectId), 'class' => 'form-horizontal')); ?>
            <div class="modal-body">
                <div class="alert alert-info" style="font-size:12px;">
                    <strong><i class="fa fa-info-circle"></i> <?php echo __('Automated Payroll Calculation:');?></strong><br>
                    <?php echo __('This process calculates days present, base salary, overtime compensation, and sales commissions for all active employees for the selected month in a single click.');?>
                </div>
                <?php if($isAdr){ ?>
                <div class="form-group">
                    <label class="col-sm-4 control-label"><?php echo __('Agency / Project');?></label>
                    <div class="col-sm-7">
                        <?php echo $this->Form->input('project_id', array('label' => false, 'class' => 'form-control', 'options' => $projectName, 'default' => $currentProjectId)); ?>
                    </div>
                </div>
                <?php } else { ?>
                    <?php echo $this->Form->hidden('project_id', array('value' => $currentProjectId)); ?>
                <?php } ?>
                <div class="form-group">
                    <label class="col-sm-4 control-label"><?php echo __('Payroll Month');?></label>
                    <div class="col-sm-7">
                        <input type="month" class="form-control" name="data[BulkPayroll][payroll_month]" value="<?php echo h($attendanceSheetMonth);?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4 control-label"><?php echo __('Employee Scope');?></label>
                    <div class="col-sm-7">
                        <select class="form-control" name="data[BulkPayroll][employee_scope]">
                            <option value="all" <?php echo ($employeeScope==='all')?'selected':'';?>><?php echo __('All Active Staff & Workers');?></option>
                            <option value="staff" <?php echo ($employeeScope==='staff')?'selected':'';?>><?php echo __('Monthly Office Staff Only');?></option>
                            <option value="labour" <?php echo ($employeeScope==='labour')?'selected':'';?>><?php echo __('Daily Labour Staff Only');?></option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-offset-4 col-sm-7">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="data[BulkPayroll][overwrite]" value="1">
                                <?php echo __('Recalculate unpaid existing payroll entries');?>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Cancel');?></button>
                <button type="submit" class="btn btn-success"><span class="fa fa-play"></span> <?php echo __('Generate Payroll Now');?></button>
            </div>
            <?php echo $this->Form->end(); ?>
        </div>
    </div>
</div>

<div class="modal fade" id="HrPayrollEntryModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php echo $isEdit ? __('Update Payroll Entry') : __('Create Payroll Entry');?></h4>
            </div>
            <div class="modal-body">
                <?php echo $this->Form->create('Ardhpayroll',array('url'=>array('controller'=>'Ardaccounts','action'=>'hrpayrollentries',$currentProjectId),'class'=>'form-horizontal','id'=>'HrPayrollEntryForm'));?>
                <?php echo $this->Form->hidden('id', array('id' => 'HrPayrollId'));?>
                <input type="hidden" name="data[Ardhpayroll][employee_scope]" value="<?php echo h($employeeScope);?>">
                <?php if(!$isAdr){ echo $this->Form->hidden('project_id',array('value'=>$agencyId, 'id' => 'HrPayrollProjectId')); }?>
                <div class="row">
                    <?php if($isAdr){?><div class="col-sm-3"><?php echo $this->Form->input('project_id',array('label'=>__('Agency'),'class'=>'form-control','options'=>$projectName,'empty'=>__('Please Select'), 'id' => 'HrPayrollProjectId'));?></div><?php }?>
                    <div class="col-sm-3"><?php echo $this->Form->input('employee_id',array('label'=>__('Employee'),'class'=>'form-control','options'=>$employeeList,'empty'=>__('Please Select'), 'id' => 'HrPayrollEmployeeId'));?></div>
                    <div class="col-sm-2">
                        <label for="HrPayrollMonthDisplay"><?php echo __('Payroll Month');?></label>
                        <input type="text" class="form-control" id="HrPayrollMonthDisplay" placeholder="01-Apr-2026" value="<?php echo h($payrollMonthDisplay);?>" autocomplete="off" />
                        <?php echo $this->Form->hidden('payroll_month',array('id' => 'HrPayrollMonth', 'value' => $payrollMonthRaw));?>
                    </div>
                    <div class="col-sm-2"><?php echo $this->Form->input('working_days',array('label'=>__('Working Days'),'class'=>'form-control', 'id' => 'HrPayrollWorkingDays'));?></div>
                    <div class="col-sm-2"><?php echo $this->Form->input('present_days',array('label'=>__('Present Days'),'class'=>'form-control', 'id' => 'HrPayrollPresentDays'));?></div>
                </div>
                <div class="row">
                    <div class="col-sm-2"><?php echo $this->Form->input('overtime_hours',array('label'=>__('OT Hours'),'class'=>'form-control', 'id' => 'HrPayrollOvertimeHours'));?></div>
                    <div class="col-sm-2"><?php echo $this->Form->input('gross_pay',array('label'=>__('Gross Pay'),'class'=>'form-control', 'id' => 'HrPayrollGrossPay'));?></div>
                    <div class="col-sm-2"><?php echo $this->Form->input('deductions',array('label'=>__('Deductions'),'class'=>'form-control', 'id' => 'HrPayrollDeductions'));?></div>
                    <div class="col-sm-2"><?php echo $this->Form->input('bonus',array('label'=>__('Bonus'),'class'=>'form-control', 'id' => 'HrPayrollBonus'));?></div>
                    <div class="col-sm-2"><?php echo $this->Form->input('commission_amount',array('label'=>__('Commission'),'class'=>'form-control', 'id' => 'HrPayrollCommissionAmount'));?></div>
                    <div class="col-sm-2"><?php echo $this->Form->input('paid_amount',array('label'=>__('Paid'),'class'=>'form-control', 'id' => 'HrPayrollPaidAmount'));?></div>
                </div>
                <div class="row">
                    <div class="col-sm-2"><?php echo $this->Form->input('status',array('label'=>__('Status'),'class'=>'form-control','options'=>array('unpaid'=>__('Unpaid'),'partial'=>__('Partial'),'paid'=>__('Paid')), 'id' => 'HrPayrollStatus'));?></div>
                    <div class="col-sm-10" style="padding-top:24px;">
                        <button type="button" class="btn btn-info" id="HrPayrollAutoFillBtn"><span class="fa fa-magic"></span> <?php echo __('Auto Fill from Attendance');?></button>
                        <span class="text-muted" id="HrPayrollAutoFillMessage" style="margin-left:10px;"></span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12"><?php echo $this->Form->input('note',array('label'=>__('Note'),'class'=>'form-control', 'id' => 'HrPayrollNote'));?></div>
                </div>
                <div class="form-group" style="margin-top:12px; margin-bottom:0;">
                    <div class="col-sm-12">
                        <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Close');?></button>
                        <button type="submit" class="btn btn-success"><span class="fa fa-save"></span> <?php echo ($isEdit?__('Update'):__('Save'));?></button>
                    </div>
                </div>
                <?php echo $this->Form->end();?>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
(function($){
    var autoFillUrl = '<?php echo addslashes($this->Html->url(array('plugin' => 'admin', 'admin' => true, 'controller' => 'Ardaccounts', 'action' => 'hrpayrollautofill', $currentProjectId)));?>';
    function setPayrollMonthFromDisplay()
    {
        var rawDisplay = $.trim($('#HrPayrollMonthDisplay').val() || '');
        if(rawDisplay === '')
        {
            $('#HrPayrollMonth').val('');
            return false;
        }
        if(typeof moment === 'undefined')
        {
            var jsDate = new Date(rawDisplay);
            if(isNaN(jsDate.getTime()))
            {
                return false;
            }
            var month = String(jsDate.getMonth() + 1);
            if(month.length < 2){ month = '0' + month; }
            $('#HrPayrollMonth').val(String(jsDate.getFullYear()) + '-' + month);
            return true;
        }
        var m = moment(rawDisplay, ['DD-MMM-YYYY','D-MMM-YYYY','DD-MM-YYYY','YYYY-MM-DD','YYYY-MM'], true);
        if(!m.isValid())
        {
            m = moment(rawDisplay);
        }
        if(!m.isValid())
        {
            return false;
        }
        $('#HrPayrollMonth').val(m.format('YYYY-MM'));
        $('#HrPayrollMonthDisplay').val(m.clone().date(1).format('DD-MMM-YYYY'));
        return true;
    }
    function triggerAutoFillIfReady()
    {
        var projectId = parseInt($('#HrPayrollProjectId').val(), 10) || 0;
        var employeeId = parseInt($('#HrPayrollEmployeeId').val(), 10) || 0;
        var payrollMonth = $.trim($('#HrPayrollMonth').val() || '');
        if(projectId > 0 && employeeId > 0 && payrollMonth !== '')
        {
            $('#HrPayrollAutoFillBtn').trigger('click');
        }
    }
    if(typeof $.fn.datetimepicker === 'function')
    {
        $('#HrPayrollMonthDisplay').datetimepicker({
            locale: '<?php echo $configLanguage;?>',
            format: 'DD-MMM-YYYY',
            useCurrent: true
        }).on('dp.change', function(){
            setPayrollMonthFromDisplay();
            triggerAutoFillIfReady();
        });
    }
    $('#HrPayrollMonthDisplay').on('change blur', function(){
        if(setPayrollMonthFromDisplay())
        {
            triggerAutoFillIfReady();
        }
    });
    $(document).on('change', '#HrPayrollProjectId, #HrPayrollEmployeeId', function(){
        setPayrollMonthFromDisplay();
        triggerAutoFillIfReady();
    });
    setPayrollMonthFromDisplay();

    $('#OpenHrPayrollEntryModalBtn').on('click', function(e){
        e.preventDefault();
        $('#HrPayrollEntryModal').modal('show');
    });
    <?php if($isEdit || !empty($this->request->query['prefill_employee_id'])){ ?>
    $('#HrPayrollEntryModal').modal('show');
    <?php } ?>
    $('#HrPayrollAutoFillBtn').on('click', function(e){
        e.preventDefault();
        var projectId = parseInt($('#HrPayrollProjectId').val(), 10) || 0;
        var employeeId = parseInt($('#HrPayrollEmployeeId').val(), 10) || 0;
        var payrollMonth = $.trim($('#HrPayrollMonth').val() || '');
        if(projectId <= 0 || employeeId <= 0 || payrollMonth === '')
        {
            $('#HrPayrollAutoFillMessage').text('<?php echo addslashes(__('Select agency, employee and payroll month first.'));?>');
            return;
        }
        $('#HrPayrollAutoFillMessage').text('<?php echo addslashes(__('Loading attendance summary...'));?>');
        $.ajax({
            url: autoFillUrl,
            type: 'GET',
            dataType: 'json',
            cache: false,
            data: {
                project_id: projectId,
                employee_id: employeeId,
                payroll_month: payrollMonth,
                _: (new Date()).getTime()
            }
        }).done(function(resp){
            if(!resp || parseInt(resp.ok, 10) !== 1 || !resp.summary)
            {
                $('#HrPayrollAutoFillMessage').text((resp && resp.message) ? resp.message : '<?php echo addslashes(__('Unable to load attendance summary.'));?>');
                return;
            }
            var s = resp.summary;
            $('#HrPayrollWorkingDays').val(s.working_days);
            $('#HrPayrollPresentDays').val(s.present_days);
            $('#HrPayrollOvertimeHours').val(s.overtime_hours);
            $('#HrPayrollGrossPay').val(s.gross_pay);
            $('#HrPayrollCommissionAmount').val(s.commission_amount);
            $('#HrPayrollAutoFillMessage').text('<?php echo addslashes(__('Attendance + commission data loaded. You can adjust values before save.'));?>');
        }).fail(function(){
            $('#HrPayrollAutoFillMessage').text('<?php echo addslashes(__('Unable to load attendance summary.'));?>');
        });
    });
})(jQuery);
</script>
