<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css">
<script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js"></script>
<style type="text/css">
.ard-drop-upload{
    position:relative;
    border:2px dashed #cdd7e6;
    border-radius:8px;
    background:#f9fbff;
    padding:10px;
    min-height:128px;
    cursor:pointer;
    transition:all .2s ease;
}
.ard-drop-upload:hover,
.ard-drop-upload.drag-over{
    border-color:#2c90d9;
    background:#eef6ff;
}
.ard-drop-upload .ard-drop-message{
    font-size:12px;
    color:#6c7a92;
    text-align:center;
    margin-top:10px;
}
.ard-drop-upload .ard-drop-actions{
    text-align:center;
    margin-top:8px;
}
.ard-drop-upload .ard-drop-preview{
    text-align:center;
    min-height:74px;
}
.ard-drop-upload .ard-drop-preview img{
    max-width:100%;
    max-height:120px;
    border-radius:6px;
    margin:0 auto;
}
.ard-drop-upload input[type="file"]{
    position:absolute;
    left:-9999px;
    top:-9999px;
}
#hrEmployeeCropModal .modal-dialog{
    width:760px;
    max-width:95%;
}
#hrEmployeeCropWrap{
    width:100%;
    max-height:460px;
    overflow:hidden;
    background:#f4f7fb;
    text-align:center;
}
#hrEmployeeCropImage{
    max-width:100%;
    display:block;
    margin:0 auto;
}
.hr-view-label{
    color:#62789a;
    font-size:12px;
    text-transform:uppercase;
    letter-spacing:.04em;
}
.hr-view-value{
    font-weight:600;
    margin-bottom:10px;
    min-height:22px;
}
.hr-view-media-card{
    border:1px solid #d9e2ef;
    border-radius:8px;
    background:#f9fbff;
    padding:10px;
    margin-bottom:10px;
}
.hr-view-media-title{
    font-size:12px;
    font-weight:700;
    color:#2f4363;
    margin-bottom:8px;
}
.hr-view-media-preview{
    height:130px;
    background:#fff;
    border:1px solid #e5ebf4;
    border-radius:6px;
    display:flex;
    align-items:center;
    justify-content:center;
    overflow:hidden;
}
.hr-view-media-preview img{
    max-height:128px;
    max-width:100%;
}
.hr-view-media-empty{
    color:#8a97ab;
    font-size:12px;
}
.hr-employee-section-head td{
    background:#e7f0ff !important;
    color:#1e3d66 !important;
    font-weight:700;
    border-top:2px solid #c9daf6 !important;
}
.hr-employee-section-head a{
    display:block;
    color:#1e3d66 !important;
    text-decoration:none;
    cursor:pointer;
}
.hr-employee-section-head a:hover,
.hr-employee-section-head a:focus{
    color:#153051 !important;
    background:#dce9ff;
    text-decoration:none;
}
.hr-employee-section-head .badge{
    background:#cfe0ff;
    color:#16345a;
}
.hr-employee-section-head.hr-employee-section-collapsed td{
    background:#edf4ff !important;
}
.hr-employee-section-row-hidden{
    display:none !important;
}
</style>

<?php
$this->Js->JqueryEngine->jQueryObject = 'jQuery';
$this->Paginator->options(array('update' => '#resultDiv', 'evalScripts' => true));
$isAdr = ($userType == 'ADR');
$departmentOptions = (isset($departmentOptions) && is_array($departmentOptions)) ? $departmentOptions : array();
$designationOptions = (isset($designationOptions) && is_array($designationOptions)) ? $designationOptions : array();
$attendanceStatusByEmployee = (isset($attendanceStatusByEmployee) && is_array($attendanceStatusByEmployee)) ? $attendanceStatusByEmployee : array();
$linkedUsersByProject = (isset($linkedUsersByProject) && is_array($linkedUsersByProject)) ? $linkedUsersByProject : array();
$linkedUsersMetaByProject = (isset($linkedUsersMetaByProject) && is_array($linkedUsersMetaByProject)) ? $linkedUsersMetaByProject : array();
$linkedUsersMetaByUserId = (isset($linkedUsersMetaByUserId) && is_array($linkedUsersMetaByUserId)) ? $linkedUsersMetaByUserId : array();
$labourLedgerNamesById = (isset($labourLedgerNamesById) && is_array($labourLedgerNamesById)) ? $labourLedgerNamesById : array();
$departmentOptionsByProject = (isset($departmentOptionsByProject) && is_array($departmentOptionsByProject)) ? $departmentOptionsByProject : array();
$designationOptionsByProject = (isset($designationOptionsByProject) && is_array($designationOptionsByProject)) ? $designationOptionsByProject : array();
$employeeScope = isset($employeeScope) ? (string)$employeeScope : 'all';
$employeeScopeLabel = isset($employeeScopeLabel) ? (string)$employeeScopeLabel : __('All Employees');
$employeeScopeQuery = (isset($employeeScopeQuery) && is_array($employeeScopeQuery)) ? $employeeScopeQuery : array();
$isLabourScope = ($employeeScope === 'labour');
$employeePageTitle = $isLabourScope ? __('Labour Employees') : __('HR Employees');
$defaultAttendanceDate = date('Y-m-d');
$selectedProjectForModal = (int)$currentProjectId;
if($selectedProjectForModal <= 0 && $isAdr)
{
    foreach((array)$projectName as $projectId => $projectLabel)
    {
        if((int)$projectId > 0)
        {
            $selectedProjectForModal = (int)$projectId;
            break;
        }
    }
}
if($selectedProjectForModal <= 0 && !$isAdr)
{
    $selectedProjectForModal = (int)$agencyId;
}
?>
<div id="resultDiv">
<div class="page-title"><div class="title-env"><h1 class="title"><?php echo h($employeePageTitle);?></h1></div></div>
<div class="panel"><?php echo $this->Session->flash();?>
    <div class="panel-body">
        <div class="btn-group" style="margin-bottom:10px;">
            <?php if($isLabourScope){ ?>
                <?php echo $this->Html->link('<span class="fa fa-users"></span> '.__('All Employees'), array('controller' => 'Ardaccounts', 'action' => 'hremployees', $currentProjectId), array('escape' => false, 'class' => 'btn btn-default'));?>
            <?php } else { ?>
                <?php echo $this->Html->link('<span class="fa fa-users"></span> '.__('Labour Control'), array('controller' => 'Ardaccounts', 'action' => 'hremployees', $currentProjectId, '?' => array('employee_scope' => 'labour')), array('escape' => false, 'class' => 'btn btn-warning'));?>
            <?php } ?>
            <?php echo $this->Html->link('<span class="fa fa-calendar"></span> '.__('Payroll Entries'), array('controller' => 'Ardaccounts', 'action' => 'hrpayrollentries', $currentProjectId, '?' => $employeeScopeQuery), array('escape' => false, 'class' => 'btn btn-info'));?>
            <?php echo $this->Html->link('<span class="fa fa-money"></span> '.__('Payroll Payments'), array('controller' => 'Ardaccounts', 'action' => 'hrpayments', $currentProjectId, '?' => $employeeScopeQuery), array('escape' => false, 'class' => 'btn btn-primary'));?>
            <a href="javascript:void(0);" class="btn btn-success" id="OpenCreateHrEmployeeModalBtn"><span class="fa fa-plus"></span> <?php echo $isLabourScope ? __('Create Labour') : __('Create Employee');?></a>
        </div>
        <div class="alert alert-info" style="margin-bottom:12px;">
            <strong><?php echo __('Current Scope');?>:</strong> <?php echo h($employeeScopeLabel);?>
        </div>
    </div>

    <?php echo $this->element('pagination');?>
    <div class="table-responsive">
        <table class="table table-striped table-bordered" id="HrEmployeesAccordionTable">
            <thead>
            <tr>
                <th>#</th>
                <th><?php echo __('Name');?></th>
                <th><?php echo __('Code');?></th>
                <th><?php echo __('Department');?></th>
                <th><?php echo __('Linked User');?></th>
                <th><?php echo __('Salary');?></th>
                <th><?php echo __('Status');?></th>
                <th><?php echo __('Last Attendance');?></th>
                <th><?php echo __('Action');?></th>
            </tr>
            </thead>
            <tbody>
            <?php
            $pageParams = $this->Paginator->params();
            $limit = $pageParams['limit'];
            $page = $pageParams['page'];
            $serialNo = 1 * $limit * ($page - 1) + 1;
            $hrRows = array();
            $labourRows = array();
            foreach((array)$rows as $employeeRowType)
            {
                $salaryTypeValue = strtolower(trim((string)(isset($employeeRowType['Ardhremployee']['salary_type']) ? $employeeRowType['Ardhremployee']['salary_type'] : 'monthly')));
                if($salaryTypeValue === 'daily')
                {
                    $labourRows[] = $employeeRowType;
                }
                else
                {
                    $hrRows[] = $employeeRowType;
                }
            }
            $employeeSections = array();
            if($employeeScope === 'labour')
            {
                $employeeSections[] = array('key' => 'labour', 'label' => __('Labour Employees'), 'rows' => $labourRows);
            }
            elseif($employeeScope === 'staff')
            {
                $employeeSections[] = array('key' => 'hr', 'label' => __('HR Employees'), 'rows' => $hrRows);
            }
            else
            {
                $employeeSections[] = array('key' => 'hr', 'label' => __('HR Employees'), 'rows' => $hrRows);
                $employeeSections[] = array('key' => 'labour', 'label' => __('Labour Employees'), 'rows' => $labourRows);
            }
            foreach((array)$employeeSections as $sectionIndex => $employeeSection):
                $sectionRows = isset($employeeSection['rows']) ? (array)$employeeSection['rows'] : array();
                $sectionLabel = isset($employeeSection['label']) ? (string)$employeeSection['label'] : __('Employees');
                $sectionKey = isset($employeeSection['key']) ? (string)$employeeSection['key'] : ('section_' . (int)$sectionIndex);
                $sectionRowClass = 'hr-employee-section-group-' . (int)$sectionIndex;
            ?>
                <tr class="hr-employee-section-head">
                    <td colspan="9">
                        <a href="javascript:void(0);" class="js-toggle-employee-section" data-section-key="<?php echo h($sectionKey);?>" data-target-class="<?php echo h($sectionRowClass);?>" aria-expanded="true">
                            <span class="fa fa-caret-down js-section-caret"></span> <?php echo h($sectionLabel);?>
                            <span class="badge pull-right"><?php echo count($sectionRows);?></span>
                        </a>
                    </td>
                </tr>
            <?php
            if(empty($sectionRows))
            {
            ?>
                <tr class="<?php echo h($sectionRowClass);?>" data-employee-section="<?php echo h($sectionKey);?>">
                    <td colspan="9" class="text-center text-muted"><?php echo __('No employees in this section.');?></td>
                </tr>
            <?php
            }
            else
            {
            foreach((array)$sectionRows as $row):
                $rid = (int)$row['Ardhremployee']['id'];
                $rowProjectId = (int)(isset($row['Ardhremployee']['project_id']) ? $row['Ardhremployee']['project_id'] : 0);
                $atsProjectId = ($rowProjectId > 0) ? $rowProjectId : (int)$currentProjectId;
                $linkedUserId = (int)(isset($row['Ardhremployee']['linked_user_id']) ? $row['Ardhremployee']['linked_user_id'] : 0);
                $linkedUserLabel = '-';
                $linkedUserRoleType = '';
                $linkedUserRoleName = '';
                $linkedUserWalletBalance = 0.0;
                $linkedUserHasWallet = 0;
                $linkedUserOptionLabel = '';
                $linkedUserWalletLabel = '-';
                if($linkedUserId > 0 && !empty($linkedUsersByProject[$rowProjectId][$linkedUserId]))
                {
                    $linkedUserLabel = (string)$linkedUsersByProject[$rowProjectId][$linkedUserId];
                    $linkedMeta = !empty($linkedUsersMetaByProject[$rowProjectId][$linkedUserId]) ? (array)$linkedUsersMetaByProject[$rowProjectId][$linkedUserId] : array();
                    $linkedUserRoleName = trim((string)(isset($linkedMeta['role_name']) ? $linkedMeta['role_name'] : ''));
                    $linkedUserRoleType = strtoupper(trim((string)(isset($linkedMeta['role_type']) ? $linkedMeta['role_type'] : '')));
                    $linkedUserWalletBalance = isset($linkedMeta['wallet_balance']) ? (float)$linkedMeta['wallet_balance'] : 0.0;
                    $linkedUserHasWallet = isset($linkedMeta['has_wallet']) ? (int)$linkedMeta['has_wallet'] : 0;
                    $linkedUserOptionLabel = trim((string)(isset($linkedMeta['name']) ? $linkedMeta['name'] : ''));
                    if($linkedUserOptionLabel === '')
                    {
                        $linkedUserOptionLabel = $linkedUserLabel;
                    }
                }
                elseif($linkedUserId > 0 && !empty($row['LinkedUser']['id']))
                {
                    $linkedUserNameOnly = trim((string)(isset($row['LinkedUser']['name']) ? $row['LinkedUser']['name'] : ''));
                    $joinedRoleName = trim((string)(isset($row['LinkedUserGroup']['name']) ? $row['LinkedUserGroup']['name'] : ''));
                    if($linkedUserNameOnly !== '')
                    {
                        $linkedUserLabel = $linkedUserNameOnly;
                        if($joinedRoleName !== '')
                        {
                            $linkedUserLabel .= ' ['.$joinedRoleName.']';
                        }
                        $linkedUserOptionLabel = $linkedUserNameOnly;
                    }
                    $linkedUserRoleName = $joinedRoleName;
                    $linkedUserRoleType = strtoupper(trim((string)(isset($row['LinkedUserGroup']['type']) ? $row['LinkedUserGroup']['type'] : '')));
                    $linkedMeta = !empty($linkedUsersMetaByUserId[$linkedUserId]) ? (array)$linkedUsersMetaByUserId[$linkedUserId] : array();
                    $linkedUserWalletBalance = isset($linkedMeta['wallet_balance']) ? (float)$linkedMeta['wallet_balance'] : 0.0;
                    $linkedUserHasWallet = isset($linkedMeta['has_wallet']) ? (int)$linkedMeta['has_wallet'] : 0;
                }
                elseif($linkedUserId > 0 && !empty($linkedUsersMetaByUserId[$linkedUserId]))
                {
                    $linkedMeta = (array)$linkedUsersMetaByUserId[$linkedUserId];
                    $metaName = trim((string)(isset($linkedMeta['name']) ? $linkedMeta['name'] : ''));
                    $metaRoleName = trim((string)(isset($linkedMeta['role_name']) ? $linkedMeta['role_name'] : ''));
                    if($metaName !== '')
                    {
                        $linkedUserLabel = $metaName;
                        if($metaRoleName !== '')
                        {
                            $linkedUserLabel .= ' ['.$metaRoleName.']';
                        }
                        $linkedUserOptionLabel = $metaName;
                    }
                    $linkedUserRoleName = $metaRoleName;
                    $linkedUserRoleType = strtoupper(trim((string)(isset($linkedMeta['role_type']) ? $linkedMeta['role_type'] : '')));
                    $linkedUserWalletBalance = isset($linkedMeta['wallet_balance']) ? (float)$linkedMeta['wallet_balance'] : 0.0;
                    $linkedUserHasWallet = isset($linkedMeta['has_wallet']) ? (int)$linkedMeta['has_wallet'] : 0;
                }
                if($linkedUserId > 0 && $linkedUserLabel === '-')
                {
                    $linkedUserLabel = __('User')." #".$linkedUserId;
                }
                if($linkedUserId > 0 && $linkedUserOptionLabel === '')
                {
                    $linkedUserOptionLabel = $linkedUserLabel;
                }
                if((int)$linkedUserHasWallet > 0 || $linkedUserRoleType === 'AGT' || stripos($linkedUserRoleName, 'agent') !== false)
                {
                    $linkedUserWalletLabel = $currency.$this->Number->format($linkedUserWalletBalance);
                }
                $labourLedgerId = isset($row['Ardhremployee']['labour_ledger_id']) ? (int)$row['Ardhremployee']['labour_ledger_id'] : 0;
                $labourLedgerName = ($labourLedgerId > 0 && isset($labourLedgerNamesById[$labourLedgerId])) ? (string)$labourLedgerNamesById[$labourLedgerId] : '';
                $profilePhoto = trim((string)(isset($row['Ardhremployee']['profile_photo']) ? $row['Ardhremployee']['profile_photo'] : ''));
                $profilePhotoDir = trim((string)(isset($row['Ardhremployee']['profile_photo_dir']) ? $row['Ardhremployee']['profile_photo_dir'] : ''));
                if($profilePhotoDir === '')
                {
                    $profilePhotoDir = 'hr_employee';
                }
                $profilePhotoUrl = ($profilePhoto !== '') ? $this->Html->url('/img/'.$profilePhotoDir.'/'.$profilePhoto) : '';

                $cnicFront = trim((string)(isset($row['Ardhremployee']['cnic_front']) ? $row['Ardhremployee']['cnic_front'] : ''));
                $cnicFrontDir = trim((string)(isset($row['Ardhremployee']['cnic_front_dir']) ? $row['Ardhremployee']['cnic_front_dir'] : ''));
                if($cnicFrontDir === '')
                {
                    $cnicFrontDir = 'hr_employee_cnic_front';
                }
                $cnicFrontUrl = ($cnicFront !== '') ? $this->Html->url('/img/'.$cnicFrontDir.'/'.$cnicFront) : '';

                $cnicBack = trim((string)(isset($row['Ardhremployee']['cnic_back']) ? $row['Ardhremployee']['cnic_back'] : ''));
                $cnicBackDir = trim((string)(isset($row['Ardhremployee']['cnic_back_dir']) ? $row['Ardhremployee']['cnic_back_dir'] : ''));
                if($cnicBackDir === '')
                {
                    $cnicBackDir = 'hr_employee_cnic_back';
                }
                $cnicBackUrl = ($cnicBack !== '') ? $this->Html->url('/img/'.$cnicBackDir.'/'.$cnicBack) : '';
                $rowAddressAttr = str_replace(array("\r", "\n"), ' ', (string)(isset($row['Ardhremployee']['address']) ? $row['Ardhremployee']['address'] : ''));
            ?>
            <tr class="<?php echo h($sectionRowClass);?>" data-employee-section="<?php echo h($sectionKey);?>">
                <td><?php echo $serialNo++;?></td>
                <td><?php echo h($row['Ardhremployee']['full_name']);?></td>
                <td><?php echo h($row['Ardhremployee']['employee_code']);?></td>
                <td><?php echo h($row['Ardhremployee']['department']);?></td>
                <td>
                    <?php echo h($linkedUserLabel);?>
                    <?php if($linkedUserRoleType === 'AGT' || stripos($linkedUserRoleName, 'agent') !== false || (int)$linkedUserHasWallet > 0){ ?>
                        <br><small><?php echo __('Wallet');?>: <?php echo $currency.$this->Number->format($linkedUserWalletBalance);?></small>
                    <?php } ?>
                </td>
                <td><?php echo $currency.$this->Number->format($row['Ardhremployee']['base_salary']);?></td>
                <td><?php echo h(ucfirst($row['Ardhremployee']['status']));?></td>
                <td>
                    <?php
                    $lastAttendance = isset($attendanceStatusByEmployee[$rid]) ? $attendanceStatusByEmployee[$rid] : array();
                    $lastAttendanceDate = isset($lastAttendance['date']) ? trim((string)$lastAttendance['date']) : '';
                    $lastAttendanceStatus = isset($lastAttendance['status']) ? trim((string)$lastAttendance['status']) : '';
                    if($lastAttendanceDate !== '')
                    {
                        $lastAttendanceLabel = date('d-M-Y', strtotime($lastAttendanceDate));
                        if($lastAttendanceStatus !== '')
                        {
                            $lastAttendanceLabel .= ' - ' . ucwords(str_replace('_', ' ', $lastAttendanceStatus));
                        }
                        echo h($lastAttendanceLabel);
                    }
                    else
                    {
                        echo '-';
                    }
                    ?>
                </td>
                <td>
                    <?php
                    echo '<a href="javascript:void(0);" class="btn btn-xs btn-primary js-view-hr-employee"'
                        . ' data-full-name="' . h((string)$row['Ardhremployee']['full_name'], true) . '"'
                        . ' data-father-name="' . h((string)(isset($row['Ardhremployee']['father_name']) ? $row['Ardhremployee']['father_name'] : ''), true) . '"'
                        . ' data-employee-code="' . h((string)$row['Ardhremployee']['employee_code'], true) . '"'
                        . ' data-department="' . h((string)$row['Ardhremployee']['department'], true) . '"'
                        . ' data-designation="' . h((string)$row['Ardhremployee']['designation'], true) . '"'
                        . ' data-mobile-no="' . h((string)$row['Ardhremployee']['mobile_no'], true) . '"'
                        . ' data-email="' . h((string)(isset($row['Ardhremployee']['email']) ? $row['Ardhremployee']['email'] : ''), true) . '"'
                        . ' data-cnic="' . h((string)(isset($row['Ardhremployee']['cnic']) ? $row['Ardhremployee']['cnic'] : ''), true) . '"'
                        . ' data-dob="' . h((string)(isset($row['Ardhremployee']['dob']) ? $row['Ardhremployee']['dob'] : ''), true) . '"'
                        . ' data-address="' . h((string)$rowAddressAttr, true) . '"'
                        . ' data-reference="' . h((string)(isset($row['Ardhremployee']['reference']) ? $row['Ardhremployee']['reference'] : ''), true) . '"'
                        . ' data-linked-user="' . h((string)$linkedUserLabel, true) . '"'
                        . ' data-linked-wallet="' . h((string)$linkedUserWalletLabel, true) . '"'
                        . ' data-labour-ledger="' . h((string)(($labourLedgerName !== '') ? $labourLedgerName : (($labourLedgerId > 0) ? ('#'.$labourLedgerId) : '-')), true) . '"'
                        . ' data-profile-photo-url="' . h((string)$profilePhotoUrl, true) . '"'
                        . ' data-cnic-front-url="' . h((string)$cnicFrontUrl, true) . '"'
                        . ' data-cnic-back-url="' . h((string)$cnicBackUrl, true) . '"'
                        . '><span class="fa fa-eye"></span> ' . __('View') . '</a>';
                    echo ' ';
                    echo '<a href="javascript:void(0);" class="btn btn-xs btn-info js-open-attendance-popup" data-employee-id="' . (int)$rid . '" data-employee-name="' . h((string)$row['Ardhremployee']['full_name'], true) . '" data-project-id="' . (int)$rowProjectId . '"><span class="fa fa-calendar-check-o"></span> ' . __('Attendance') . '</a>';
                    echo ' ';
                    echo $this->Html->link(
                        '<span class="fa fa-table"></span> ATS',
                        array(
                            'controller' => 'Ardaccounts',
                            'action' => 'hrpayrollentries',
                            (int)$atsProjectId,
                            '?' => array(
                                'employee_scope' => $employeeScope,
                                'sheet_month' => date('Y-m'),
                                'sheet_employee_id' => (int)$rid
                            )
                        ),
                        array('escape' => false, 'class' => 'btn btn-xs btn-success', 'title' => __('Attendance Sheet'))
                    );
                    echo ' ';
                    if($labourLedgerId > 0)
                    {
                        echo $this->Html->link(
                            '<span class="fa fa-book"></span> ' . __('Ledger'),
                            array('controller' => 'Ardaccounts', 'action' => 'ledgerstatement', (int)$labourLedgerId, (int)$rowProjectId),
                            array('escape' => false, 'class' => 'btn btn-xs btn-default')
                        );
                        echo ' ';
                    }
                    echo '<a href="javascript:void(0);" class="btn btn-xs btn-warning js-edit-hr-employee"'
                        . ' data-employee-id="' . (int)$rid . '"'
                        . ' data-project-id="' . (int)$rowProjectId . '"'
                        . ' data-linked-user-id="' . (int)$linkedUserId . '"'
                        . ' data-linked-user-label="' . h((string)$linkedUserOptionLabel, true) . '"'
                        . ' data-full-name="' . h((string)$row['Ardhremployee']['full_name'], true) . '"'
                        . ' data-father-name="' . h((string)(isset($row['Ardhremployee']['father_name']) ? $row['Ardhremployee']['father_name'] : ''), true) . '"'
                        . ' data-employee-code="' . h((string)$row['Ardhremployee']['employee_code'], true) . '"'
                        . ' data-department="' . h((string)$row['Ardhremployee']['department'], true) . '"'
                        . ' data-designation="' . h((string)$row['Ardhremployee']['designation'], true) . '"'
                        . ' data-cnic="' . h((string)(isset($row['Ardhremployee']['cnic']) ? $row['Ardhremployee']['cnic'] : ''), true) . '"'
                        . ' data-dob="' . h((string)(isset($row['Ardhremployee']['dob']) ? $row['Ardhremployee']['dob'] : ''), true) . '"'
                        . ' data-email="' . h((string)(isset($row['Ardhremployee']['email']) ? $row['Ardhremployee']['email'] : ''), true) . '"'
                        . ' data-mobile-no="' . h((string)$row['Ardhremployee']['mobile_no'], true) . '"'
                        . ' data-address="' . h((string)$rowAddressAttr, true) . '"'
                        . ' data-reference="' . h((string)(isset($row['Ardhremployee']['reference']) ? $row['Ardhremployee']['reference'] : ''), true) . '"'
                        . ' data-base-salary="' . h((string)$row['Ardhremployee']['base_salary'], true) . '"'
                        . ' data-salary-type="' . h((string)$row['Ardhremployee']['salary_type'], true) . '"'
                        . ' data-status="' . h((string)$row['Ardhremployee']['status'], true) . '"'
                        . ' data-profile-photo-url="' . h((string)$profilePhotoUrl, true) . '"'
                        . ' data-cnic-front-url="' . h((string)$cnicFrontUrl, true) . '"'
                        . ' data-cnic-back-url="' . h((string)$cnicBackUrl, true) . '"'
                        . '><span class="fa fa-edit"></span> ' . __('Edit') . '</a>';
                    echo ' ';
                    echo $this->Html->link('<span class="fa fa-trash"></span> ' . __('Delete'), array('controller' => 'Ardaccounts', 'action' => 'deletehremployee', $rid, $currentProjectId, '?' => $employeeScopeQuery), array('escape' => false, 'class' => 'btn btn-xs btn-danger', 'onclick' => "return confirm('" . __('Are you sure?') . "');"));
                    ?>
                </td>
            </tr>
            <?php endforeach; } endforeach;?>
            </tbody>
        </table>
    </div>
    <?php echo $this->element('pagination');?>
</div>

<div class="modal fade" id="HrEmployeeModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="HrEmployeeModalTitle"><?php echo __('Create Employee');?></h4>
            </div>
            <div class="modal-body">
                <?php echo $this->Form->create('Ardhremployee', array('class' => 'form-horizontal', 'id' => 'HrEmployeePopupForm', 'type' => 'file'));?>
                <?php echo $this->Form->hidden('id', array('id' => 'HrEmployeeId'));?>
                <?php echo $this->Form->hidden('form_mode', array('id' => 'HrEmployeeFormMode', 'value' => 'create'));?>
                <input type="hidden" name="data[Ardhremployee][employee_scope]" value="<?php echo h($employeeScope);?>" />
                <?php if(!$isAdr){echo $this->Form->hidden('project_id', array('value' => (int)$selectedProjectForModal, 'id' => 'HrEmployeeProjectId'));}?>
                <?php if($isAdr){?>
                <div class="form-group">
                    <label class="col-sm-2 control-label"><?php echo __('Agency');?></label>
                    <div class="col-sm-10">
                        <?php echo $this->Form->select('project_id', (array)$projectName, array('empty' => __('Please Select'), 'class' => 'form-control', 'label' => false, 'div' => false, 'id' => 'HrEmployeeProjectId', 'value' => (int)$selectedProjectForModal));?>
                    </div>
                </div>
                <?php }?>
                <div class="form-group">
                    <label class="col-sm-2 control-label"><?php echo __('Select User');?></label>
                    <div class="col-sm-10">
                        <?php echo $this->Form->select('linked_user_id', array(), array('empty' => __('Please Select'), 'class' => 'form-control', 'label' => false, 'div' => false, 'id' => 'HrEmployeeLinkedUserId'));?>
                        <p class="help-block" style="margin-bottom:0;"><?php echo __('Pick Agent or any user role to link as employee. Name/mobile/email can auto-fill.');?></p>
                    </div>
                </div>
                <div class="form-group hidden" id="HrEmployeeRoleInfoWrap">
                    <label class="col-sm-2 control-label"><?php echo __('Selected Role');?></label>
                    <div class="col-sm-10" style="padding-top:7px;">
                        <span id="HrEmployeeRoleInfoText">-</span>
                    </div>
                </div>
                <div class="form-group hidden" id="HrEmployeeWalletInfoWrap">
                    <label class="col-sm-2 control-label"><?php echo __('Agent Wallet');?></label>
                    <div class="col-sm-10" style="padding-top:7px;">
                        <strong id="HrEmployeeWalletInfoText">-</strong>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label"><?php echo __('Full Name');?></label>
                    <div class="col-sm-4"><?php echo $this->Form->input('full_name', array('label' => false, 'div' => false, 'class' => 'form-control', 'id' => 'HrEmployeeFullName'));?></div>
                    <label class="col-sm-2 control-label"><?php echo __('Father Name');?></label>
                    <div class="col-sm-4"><?php echo $this->Form->input('father_name', array('label' => false, 'div' => false, 'class' => 'form-control', 'id' => 'HrEmployeeFatherName'));?></div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label"><?php echo __('Employee Code');?></label>
                    <div class="col-sm-4">
                        <?php echo $this->Form->input('employee_code', array('label' => false, 'div' => false, 'class' => 'form-control text-muted', 'id' => 'HrEmployeeCode', 'readonly' => 'readonly', 'placeholder' => __('Auto Generated'), 'value' => __('Auto Generated')));?>
                        <p class="help-block" style="margin-bottom:0;"><?php echo __('This field shows the next auto-generated code from agency Employee ID settings.');?></p>
                    </div>
                    <label class="col-sm-2 control-label"><?php echo __('CNIC');?></label>
                    <div class="col-sm-4"><?php echo $this->Form->input('cnic', array('label' => false, 'div' => false, 'class' => 'form-control', 'id' => 'HrEmployeeCnic', 'placeholder' => '35301-1234567-1', 'maxlength' => 15));?></div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label"><?php echo __('DOB');?></label>
                    <div class="col-sm-4"><input type="date" name="data[Ardhremployee][dob]" class="form-control" id="HrEmployeeDob" /></div>
                    <label class="col-sm-2 control-label"><?php echo __('Email-ID');?></label>
                    <div class="col-sm-4"><?php echo $this->Form->input('email', array('label' => false, 'div' => false, 'class' => 'form-control', 'id' => 'HrEmployeeEmail'));?></div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label"><?php echo __('Department');?></label>
                    <div class="col-sm-4">
                        <?php echo $this->Form->select('department', (array)$departmentOptions, array('empty' => __('Please Select'), 'class' => 'form-control', 'label' => false, 'div' => false, 'id' => 'HrEmployeeDepartment'));?>
                    </div>
                    <label class="col-sm-2 control-label"><?php echo __('Designation');?></label>
                    <div class="col-sm-4">
                        <?php echo $this->Form->select('designation', (array)$designationOptions, array('empty' => __('Please Select'), 'class' => 'form-control', 'label' => false, 'div' => false, 'id' => 'HrEmployeeDesignation'));?>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label"><?php echo __('Mobile');?></label>
                    <div class="col-sm-4"><?php echo $this->Form->input('mobile_no', array('label' => false, 'div' => false, 'class' => 'form-control', 'id' => 'HrEmployeeMobileNo'));?></div>
                    <label class="col-sm-2 control-label"><?php echo __('Base Salary');?></label>
                    <div class="col-sm-4"><?php echo $this->Form->input('base_salary', array('type' => 'number', 'step' => '0.01', 'label' => false, 'div' => false, 'class' => 'form-control', 'id' => 'HrEmployeeBaseSalary'));?></div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label"><?php echo __('Salary Type');?></label>
                    <div class="col-sm-4"><?php echo $this->Form->select('salary_type', array('monthly' => __('Monthly'), 'daily' => __('Daily')), array('class' => 'form-control', 'label' => false, 'div' => false, 'id' => 'HrEmployeeSalaryType'));?></div>
                    <label class="col-sm-2 control-label"><?php echo __('Status');?></label>
                    <div class="col-sm-4"><?php echo $this->Form->select('status', array('active' => __('Active'), 'inactive' => __('Inactive')), array('class' => 'form-control', 'label' => false, 'div' => false, 'id' => 'HrEmployeeStatus'));?></div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label"><?php echo __('Address');?></label>
                    <div class="col-sm-4"><?php echo $this->Form->textarea('address', array('label' => false, 'div' => false, 'class' => 'form-control', 'rows' => 2, 'id' => 'HrEmployeeAddress'));?></div>
                    <label class="col-sm-2 control-label"><?php echo __('Reference');?></label>
                    <div class="col-sm-4"><?php echo $this->Form->input('reference', array('label' => false, 'div' => false, 'class' => 'form-control', 'id' => 'HrEmployeeReference'));?></div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label"><?php echo __('Profile Photo');?></label>
                    <div class="col-sm-4">
                        <div class="ard-drop-upload" data-input-id="HrEmployeeProfilePhoto">
                            <div class="ard-drop-preview" id="HrEmployeeProfilePhotoPreview"></div>
                            <div class="ard-drop-message"><?php echo __('Drag & drop image here');?></div>
                            <div class="ard-drop-actions"><button type="button" class="btn btn-default btn-xs js-ard-drop-browse"><?php echo __('Browse');?></button></div>
                            <?php echo $this->Form->input('profile_photo', array('type' => 'file', 'label' => false, 'div' => false, 'accept' => 'image/*', 'id' => 'HrEmployeeProfilePhoto'));?>
                        </div>
                    </div>
                    <label class="col-sm-2 control-label"><?php echo __('CNIC Front');?></label>
                    <div class="col-sm-4">
                        <div class="ard-drop-upload" data-input-id="HrEmployeeCnicFront">
                            <div class="ard-drop-preview" id="HrEmployeeCnicFrontPreview"></div>
                            <div class="ard-drop-message"><?php echo __('Drag & drop image here');?></div>
                            <div class="ard-drop-actions"><button type="button" class="btn btn-default btn-xs js-ard-drop-browse"><?php echo __('Browse');?></button></div>
                            <?php echo $this->Form->input('cnic_front', array('type' => 'file', 'label' => false, 'div' => false, 'accept' => 'image/*', 'id' => 'HrEmployeeCnicFront'));?>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label"><?php echo __('CNIC Back');?></label>
                    <div class="col-sm-4">
                        <div class="ard-drop-upload" data-input-id="HrEmployeeCnicBack">
                            <div class="ard-drop-preview" id="HrEmployeeCnicBackPreview"></div>
                            <div class="ard-drop-message"><?php echo __('Drag & drop image here');?></div>
                            <div class="ard-drop-actions"><button type="button" class="btn btn-default btn-xs js-ard-drop-browse"><?php echo __('Browse');?></button></div>
                            <?php echo $this->Form->input('cnic_back', array('type' => 'file', 'label' => false, 'div' => false, 'accept' => 'image/*', 'id' => 'HrEmployeeCnicBack'));?>
                        </div>
                    </div>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <div class="col-sm-offset-2 col-sm-10">
                        <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Close');?></button>
                        <button type="submit" class="btn btn-success" id="HrEmployeeSubmitBtn"><span class="fa fa-save"></span> <?php echo __('Save Employee');?></button>
                    </div>
                </div>
                <?php echo $this->Form->end();?>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="HrEmployeeViewModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php echo __('Employee Details');?></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-4">
                        <div class="hr-view-label"><?php echo __('Full Name');?></div>
                        <div class="hr-view-value" id="HrViewFullName">-</div>
                    </div>
                    <div class="col-sm-4">
                        <div class="hr-view-label"><?php echo __('Father Name');?></div>
                        <div class="hr-view-value" id="HrViewFatherName">-</div>
                    </div>
                    <div class="col-sm-4">
                        <div class="hr-view-label"><?php echo __('Employee Code');?></div>
                        <div class="hr-view-value" id="HrViewCode">-</div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-3">
                        <div class="hr-view-label"><?php echo __('Linked User');?></div>
                        <div class="hr-view-value" id="HrViewLinkedUser">-</div>
                    </div>
                    <div class="col-sm-3">
                        <div class="hr-view-label"><?php echo __('Agent Wallet');?></div>
                        <div class="hr-view-value" id="HrViewWallet">-</div>
                    </div>
                    <div class="col-sm-3">
                        <div class="hr-view-label"><?php echo __('Department');?></div>
                        <div class="hr-view-value" id="HrViewDepartment">-</div>
                    </div>
                    <div class="col-sm-3">
                        <div class="hr-view-label"><?php echo __('Designation');?></div>
                        <div class="hr-view-value" id="HrViewDesignation">-</div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <div class="hr-view-label"><?php echo __('HR Ledger');?></div>
                        <div class="hr-view-value" id="HrViewLabourLedger">-</div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-4">
                        <div class="hr-view-label"><?php echo __('Mobile');?></div>
                        <div class="hr-view-value" id="HrViewMobile">-</div>
                    </div>
                    <div class="col-sm-4">
                        <div class="hr-view-label"><?php echo __('Email-ID');?></div>
                        <div class="hr-view-value" id="HrViewEmail">-</div>
                    </div>
                    <div class="col-sm-4">
                        <div class="hr-view-label"><?php echo __('DOB');?></div>
                        <div class="hr-view-value" id="HrViewDob">-</div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-4">
                        <div class="hr-view-label"><?php echo __('CNIC');?></div>
                        <div class="hr-view-value" id="HrViewCnic">-</div>
                    </div>
                    <div class="col-sm-8">
                        <div class="hr-view-label"><?php echo __('Reference');?></div>
                        <div class="hr-view-value" id="HrViewReference">-</div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <div class="hr-view-label"><?php echo __('Address');?></div>
                        <div class="hr-view-value" id="HrViewAddress">-</div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-4">
                        <div class="hr-view-media-card">
                            <div class="hr-view-media-title"><?php echo __('Profile Photo');?></div>
                            <div class="hr-view-media-preview" id="HrViewProfileWrap"><span class="hr-view-media-empty"><?php echo __('N/A');?></span></div>
                            <div style="margin-top:8px;">
                                <a href="javascript:void(0);" class="btn btn-xs btn-primary hidden" id="HrViewProfileLink" target="_blank"><?php echo __('View Full');?></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="hr-view-media-card">
                            <div class="hr-view-media-title"><?php echo __('CNIC Front');?></div>
                            <div class="hr-view-media-preview" id="HrViewCnicFrontWrap"><span class="hr-view-media-empty"><?php echo __('N/A');?></span></div>
                            <div style="margin-top:8px;">
                                <a href="javascript:void(0);" class="btn btn-xs btn-primary hidden" id="HrViewCnicFrontLink" target="_blank"><?php echo __('View Full');?></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="hr-view-media-card">
                            <div class="hr-view-media-title"><?php echo __('CNIC Back');?></div>
                            <div class="hr-view-media-preview" id="HrViewCnicBackWrap"><span class="hr-view-media-empty"><?php echo __('N/A');?></span></div>
                            <div style="margin-top:8px;">
                                <a href="javascript:void(0);" class="btn btn-xs btn-primary hidden" id="HrViewCnicBackLink" target="_blank"><?php echo __('View Full');?></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="hrEmployeeCropModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php echo __('Crop Image');?></h4>
            </div>
            <div class="modal-body">
                <div id="hrEmployeeCropWrap">
                    <img id="hrEmployeeCropImage" src="" alt="Crop Image">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Cancel');?></button>
                <button type="button" class="btn btn-success" id="hrEmployeeCropApply"><?php echo __('Apply Crop');?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="HrEmployeeAttendanceModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php echo __('Employee Attendance');?></h4>
            </div>
            <div class="modal-body">
                <?php echo $this->Form->create('Ardhattendance',array('url'=>array('controller'=>'Ardaccounts','action'=>'hremployees',$currentProjectId),'class'=>'form-horizontal','id'=>'HrEmployeeAttendanceForm'));?>
                <input type="hidden" name="data[attendance_popup_submit]" value="1" />
                <input type="hidden" name="data[employee_scope]" value="<?php echo h($employeeScope);?>" />
                <?php echo $this->Form->hidden('Ardhattendance.project_id', array('value' => $currentProjectId, 'id' => 'HrAttendanceProjectId'));?>
                <?php echo $this->Form->hidden('Ardhattendance.employee_id', array('value' => '', 'id' => 'HrAttendanceEmployeeId'));?>
                <div class="alert alert-info" style="margin-bottom:12px;">
                    <strong><?php echo __('Employee');?>:</strong> <span id="HrAttendanceEmployeeName">-</span><br>
                    <small><?php echo __('If attendance already exists on the same date, this will update that record.');?></small>
                </div>
                <div class="form-group">
                    <label class="col-sm-4 control-label"><?php echo __('Attendance Date');?></label>
                    <div class="col-sm-8"><input type="date" name="data[Ardhattendance][attendance_date]" class="form-control" id="HrAttendanceDate" value="<?php echo h($defaultAttendanceDate);?>" /></div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4 control-label"><?php echo __('Status');?></label>
                    <div class="col-sm-8"><?php echo $this->Form->select('Ardhattendance.attendance_status', array('present' => __('Present'), 'absent' => __('Absent'), 'leave' => __('Leave'), 'half_day' => __('Half Day')), array('class' => 'form-control', 'label' => false, 'div' => false, 'id' => 'HrAttendanceStatus', 'value' => 'present'));?></div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4 control-label"><?php echo __('In Time');?></label>
                    <div class="col-sm-8"><input type="time" name="data[Ardhattendance][in_time]" class="form-control" id="HrAttendanceInTime" /></div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4 control-label"><?php echo __('Out Time');?></label>
                    <div class="col-sm-8"><input type="time" name="data[Ardhattendance][out_time]" class="form-control" id="HrAttendanceOutTime" /></div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4 control-label"><?php echo __('Overtime Hours');?></label>
                    <div class="col-sm-8"><?php echo $this->Form->input('Ardhattendance.overtime_hours', array('type' => 'number', 'step' => '0.25', 'min' => '0', 'label' => false, 'div' => false, 'class' => 'form-control', 'id' => 'HrAttendanceOvertimeHours', 'value' => '0'));?></div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4 control-label"><?php echo __('Remarks');?></label>
                    <div class="col-sm-8"><?php echo $this->Form->textarea('Ardhattendance.note', array('label' => false, 'div' => false, 'class' => 'form-control', 'rows' => 3, 'id' => 'HrAttendanceNote'));?></div>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <div class="col-sm-offset-4 col-sm-8">
                        <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Close');?></button>
                        <button type="submit" class="btn btn-success"><span class="fa fa-save"></span> <?php echo __('Save Attendance');?></button>
                    </div>
                </div>
                <?php echo $this->Form->end();?>
            </div>
        </div>
    </div>
</div>
</div>

<script type="text/javascript">
(function($){
    var linkedUsersByProject = <?php echo json_encode($linkedUsersByProject);?>;
    var linkedUsersMetaByProject = <?php echo json_encode($linkedUsersMetaByProject);?>;
    var linkedUsersMetaByUserId = <?php echo json_encode($linkedUsersMetaByUserId);?>;
    var departmentOptionsByProject = <?php echo json_encode($departmentOptionsByProject);?>;
    var designationOptionsByProject = <?php echo json_encode($designationOptionsByProject);?>;
    var currencyText = '<?php echo addslashes(trim(strip_tags((string)$currency)));?>';
    var defaultProjectId = <?php echo (int)$selectedProjectForModal;?>;
    var labourScope = <?php echo $isLabourScope ? 'true' : 'false';?>;
    var createTitle = '<?php echo addslashes($isLabourScope ? __('Create Labour') : __('Create Employee'));?>';
    var updateTitle = '<?php echo addslashes($isLabourScope ? __('Update Labour') : __('Update Employee'));?>';
    var saveBtnText = '<?php echo addslashes($isLabourScope ? __('Save Labour') : __('Save Employee'));?>';
    var updateBtnText = '<?php echo addslashes($isLabourScope ? __('Update Labour') : __('Update Employee'));?>';
    var pleaseSelectText = '<?php echo addslashes(__('Please Select'));?>';
    var autoGeneratedText = '<?php echo addslashes(__('Auto Generated'));?>';
    var employeeCodePreviewByProject = <?php echo json_encode($employeeCodePreviewByProject);?>;
    var employeeCodePreviewUrl = '<?php echo addslashes($this->Html->url(array('plugin' => 'admin', 'admin' => true, 'controller' => 'Ardaccounts', 'action' => 'hremployeecodepreview')));?>';
    var employeeCodePreviewXhr = null;

    var hrCropState = {
        cropper: null,
        objectUrl: null,
        input: null,
        box: null,
        preview: null,
        fallbackPreview: null,
        fileName: 'image.jpg',
        fileType: 'image/jpeg'
    };

    function escHtml(text)
    {
        return $('<div/>').text(text || '').html();
    }
    function formatEmployeeCnic(value)
    {
        var digits = String(value || '').replace(/\D/g, '');
        if(digits.length > 13)
        {
            digits = digits.substring(0, 13);
        }
        if(digits.length <= 5)
        {
            return digits;
        }
        if(digits.length <= 12)
        {
            return digits.substring(0, 5) + '-' + digits.substring(5);
        }
        return digits.substring(0, 5) + '-' + digits.substring(5, 12) + '-' + digits.substring(12, 13);
    }
    function formatYmdToDmy(value)
    {
        var v = String(value || '').trim();
        if(v === '' || v === '0000-00-00')
        {
            return '-';
        }
        var parts = v.split('-');
        if(parts.length === 3)
        {
            return parts[2] + '-' + parts[1] + '-' + parts[0];
        }
        return v;
    }
    function nvl(text, fallback)
    {
        var value = $.trim(String(text || ''));
        if(value === '')
        {
            return (typeof fallback === 'undefined') ? '-' : fallback;
        }
        return value;
    }
    function projectKey(projectId)
    {
        projectId = parseInt(projectId, 10);
        if(isNaN(projectId) || projectId < 0)
        {
            projectId = 0;
        }
        return String(projectId);
    }
    function userMeta(projectId, userId)
    {
        var byProject = linkedUsersMetaByProject[projectKey(projectId)] || {};
        var userKey = String(userId || '');
        if(userKey !== '' && byProject[userKey])
        {
            return byProject[userKey];
        }
        if(userKey !== '' && linkedUsersMetaByUserId[userKey])
        {
            return linkedUsersMetaByUserId[userKey];
        }
        return null;
    }
    function employeeCodePreview(projectId)
    {
        var key = projectKey(projectId);
        var code = $.trim(String((employeeCodePreviewByProject && employeeCodePreviewByProject[key]) ? employeeCodePreviewByProject[key] : ''));
        if(code === '' && key !== '0')
        {
            code = $.trim(String((employeeCodePreviewByProject && employeeCodePreviewByProject[parseInt(key, 10)]) ? employeeCodePreviewByProject[parseInt(key, 10)] : ''));
        }
        if(code === '')
        {
            code = autoGeneratedText;
        }
        return code;
    }
    function refreshEmployeeCodePreview(projectId)
    {
        var mode = String($('#HrEmployeeFormMode').val() || 'create').toLowerCase();
        if(mode !== 'create')
        {
            return;
        }
        var projectIdInt = parseInt(projectId, 10) || 0;
        $('#HrEmployeeCode')
            .val(employeeCodePreview(projectIdInt))
            .attr('readonly', true)
            .attr('placeholder', autoGeneratedText)
            .addClass('text-muted');
        if(projectIdInt <= 0 || !employeeCodePreviewUrl)
        {
            return;
        }
        if(employeeCodePreviewXhr && employeeCodePreviewXhr.readyState !== 4)
        {
            employeeCodePreviewXhr.abort();
        }
        employeeCodePreviewXhr = $.ajax({
            url: employeeCodePreviewUrl,
            type: 'GET',
            data: {
                project_id: projectIdInt,
                _: (new Date()).getTime()
            },
            dataType: 'json',
            cache: false
        });
        employeeCodePreviewXhr.done(function(resp){
            if(!resp || parseInt(resp.ok, 10) !== 1)
            {
                return;
            }
            var liveCode = $.trim(String(resp.code || ''));
            if(liveCode === '')
            {
                return;
            }
            var currentMode = String($('#HrEmployeeFormMode').val() || 'create').toLowerCase();
            var currentProjectId = parseInt($('#HrEmployeeProjectId').val(), 10) || 0;
            if(currentMode !== 'create' || currentProjectId !== projectIdInt)
            {
                return;
            }
            employeeCodePreviewByProject[projectKey(projectIdInt)] = liveCode;
            $('#HrEmployeeCode').val(liveCode);
        });
    }
    function renderLinkedUserOptions(projectId, selectedUserId, selectedUserLabel)
    {
        var byProject = linkedUsersByProject[projectKey(projectId)] || {};
        var html = '<option value="">' + escHtml(pleaseSelectText) + '</option>';
        var selectedKey = String(parseInt(selectedUserId, 10) || 0);
        var foundSelected = false;
        $.each(byProject, function(userId, label){
            var userKey = String(userId);
            if(selectedKey !== '0' && userKey === selectedKey)
            {
                foundSelected = true;
            }
            html += '<option value="' + escHtml(userKey) + '">' + escHtml(String(label)) + '</option>';
        });
        if(selectedKey !== '0' && !foundSelected)
        {
            var fallbackLabel = $.trim(String(selectedUserLabel || ''));
            if(fallbackLabel === '')
            {
                fallbackLabel = 'User #' + selectedKey;
            }
            html += '<option value="' + escHtml(selectedKey) + '" selected="selected">' + escHtml(fallbackLabel) + '</option>';
        }
        $('#HrEmployeeLinkedUserId').html(html);
        if(selectedUserId)
        {
            $('#HrEmployeeLinkedUserId').val(String(selectedUserId));
        }
    }
    function renderProjectSettingOptions(selector, optionMap, selectedValue)
    {
        var selected = $.trim(String(selectedValue || ''));
        var html = '<option value="">' + escHtml(pleaseSelectText) + '</option>';
        var hasSelected = false;
        $.each((optionMap || {}), function(optionValue, optionLabel){
            var optionKey = String(optionValue);
            if(selected !== '' && optionKey === selected)
            {
                hasSelected = true;
            }
            html += '<option value="' + escHtml(optionKey) + '">' + escHtml(String(optionLabel)) + '</option>';
        });
        if(selected !== '' && !hasSelected)
        {
            html += '<option value="' + escHtml(selected) + '" selected="selected">' + escHtml(selected) + '</option>';
        }
        $(selector).html(html);
        if(selected !== '')
        {
            $(selector).val(selected);
        }
    }
    function refreshDepartmentDesignationOptions(projectId, selectedDepartment, selectedDesignation)
    {
        var deptOptions = departmentOptionsByProject[projectKey(projectId)] || {};
        var desigOptions = designationOptionsByProject[projectKey(projectId)] || {};
        renderProjectSettingOptions('#HrEmployeeDepartment', deptOptions, selectedDepartment || '');
        renderProjectSettingOptions('#HrEmployeeDesignation', desigOptions, selectedDesignation || '');
    }
    function enforceScopeSalaryType()
    {
        if(!labourScope)
        {
            return;
        }
        var $salaryType = $('#HrEmployeeSalaryType');
        if(!$salaryType.length)
        {
            return;
        }
        $salaryType.val('daily');
        $salaryType.find('option[value="monthly"]').prop('disabled', true).hide();
        $salaryType.find('option[value="daily"]').prop('disabled', false).show();
    }
    function bindHrDropUpload(scope)
    {
        scope.find('.ard-drop-upload').each(function(){
            var $box = $(this);
            var inputId = $box.attr('data-input-id');
            var $input = $('#' + inputId);
            var $preview = $box.find('.ard-drop-preview');

            var showPreview = function(file){
                if(!file || !file.type || file.type.indexOf('image/') !== 0)
                {
                    return;
                }
                var reader = new FileReader();
                reader.onload = function(e){
                    $preview.html('<img src="' + e.target.result + '" alt="Preview">');
                    $box.addClass('has-image');
                };
                reader.readAsDataURL(file);
            };

            if(!$input.length)
            {
                return;
            }

            $box.off('click.hrDrop').on('click.hrDrop', function(e){
                if($(e.target).hasClass('js-ard-drop-browse') || $(e.target).closest('.js-ard-drop-browse').length)
                {
                    return;
                }
                $input[0].click();
            });
            $box.find('.js-ard-drop-browse').off('click.hrDrop').on('click.hrDrop', function(e){
                e.preventDefault();
                e.stopPropagation();
                $input[0].click();
            });
            $input.off('change.hrDrop').on('change.hrDrop', function(){
                if(this.files && this.files.length)
                {
                    openHrEmployeeCropper(this.files[0], $input, $box, $preview, showPreview);
                }
            });
            $box.off('dragenter.hrDrop dragover.hrDrop').on('dragenter.hrDrop dragover.hrDrop', function(e){
                e.preventDefault();
                e.stopPropagation();
                $box.addClass('drag-over');
            });
            $box.off('dragleave.hrDrop').on('dragleave.hrDrop', function(e){
                e.preventDefault();
                e.stopPropagation();
                $box.removeClass('drag-over');
            });
            $box.off('drop.hrDrop').on('drop.hrDrop', function(e){
                e.preventDefault();
                e.stopPropagation();
                $box.removeClass('drag-over');
                var dt = e.originalEvent.dataTransfer;
                if(dt && dt.files && dt.files.length)
                {
                    try{
                        $input[0].files = dt.files;
                        $input.trigger('change');
                    }catch(err){
                        openHrEmployeeCropper(dt.files[0], $input, $box, $preview, showPreview);
                    }
                }
            });
        });
    }
    function openHrEmployeeCropper(file, $input, $box, $preview, fallbackPreview)
    {
        if(!file || !file.type || file.type.indexOf('image/') !== 0)
        {
            return;
        }
        if(typeof Cropper === 'undefined')
        {
            fallbackPreview(file);
            return;
        }
        if(hrCropState.objectUrl)
        {
            URL.revokeObjectURL(hrCropState.objectUrl);
            hrCropState.objectUrl = null;
        }
        hrCropState.input = $input;
        hrCropState.box = $box;
        hrCropState.preview = $preview;
        hrCropState.fallbackPreview = fallbackPreview;
        hrCropState.fileName = file.name || 'image.jpg';
        hrCropState.fileType = file.type || 'image/jpeg';
        hrCropState.objectUrl = URL.createObjectURL(file);
        $('#hrEmployeeCropImage').attr('src', hrCropState.objectUrl);
        $('#hrEmployeeCropModal').modal('show');
    }
    function initHrEmployeeCropperModal()
    {
        $('#hrEmployeeCropModal').off('shown.bs.modal.hrCrop').on('shown.bs.modal.hrCrop', function(){
            if(typeof Cropper === 'undefined')
            {
                return;
            }
            var image = document.getElementById('hrEmployeeCropImage');
            if(hrCropState.cropper)
            {
                hrCropState.cropper.destroy();
                hrCropState.cropper = null;
            }
            hrCropState.cropper = new Cropper(image, {
                viewMode: 1,
                autoCropArea: 1,
                responsive: true,
                background: false
            });
        });
        $('#hrEmployeeCropModal').off('hidden.bs.modal.hrCrop').on('hidden.bs.modal.hrCrop', function(){
            if(hrCropState.cropper)
            {
                hrCropState.cropper.destroy();
                hrCropState.cropper = null;
            }
            if(hrCropState.objectUrl)
            {
                URL.revokeObjectURL(hrCropState.objectUrl);
                hrCropState.objectUrl = null;
            }
        });
        $('#hrEmployeeCropApply').off('click.hrCrop').on('click.hrCrop', function(){
            if(!hrCropState.cropper || !hrCropState.input || !hrCropState.preview)
            {
                $('#hrEmployeeCropModal').modal('hide');
                return;
            }
            var mime = 'image/jpeg';
            var safeName = (hrCropState.fileName || 'image').replace(/\.[^/.]+$/, '') + '.jpg';
            var canvas = hrCropState.cropper.getCroppedCanvas({
                maxWidth: 1600,
                maxHeight: 1600,
                imageSmoothingEnabled: true,
                imageSmoothingQuality: 'high'
            });
            if(!canvas)
            {
                $('#hrEmployeeCropModal').modal('hide');
                return;
            }
            canvas.toBlob(function(blob){
                if(!blob)
                {
                    $('#hrEmployeeCropModal').modal('hide');
                    return;
                }
                var croppedFile = new File([blob], safeName, {type: mime, lastModified: Date.now()});
                try{
                    var dt = new DataTransfer();
                    dt.items.add(croppedFile);
                    hrCropState.input[0].files = dt.files;
                }catch(err){}
                hrCropState.preview.html('<img src="' + canvas.toDataURL(mime, 0.92) + '" alt="Preview">');
                hrCropState.box.addClass('has-image');
                $('#hrEmployeeCropModal').modal('hide');
            }, mime, 0.92);
        });
    }
    function setDropPreviewByInput(inputId, imageUrl)
    {
        var $box = $('.ard-drop-upload[data-input-id="' + inputId + '"]');
        if(!$box.length)
        {
            return;
        }
        var $preview = $box.find('.ard-drop-preview');
        if($.trim(String(imageUrl || '')) !== '')
        {
            $preview.html('<img src="' + escHtml(imageUrl) + '" alt="Preview">');
            $box.addClass('has-image');
        }
        else
        {
            $preview.html('');
            $box.removeClass('has-image');
        }
    }
    function resetDropPreviews()
    {
        setDropPreviewByInput('HrEmployeeProfilePhoto', '');
        setDropPreviewByInput('HrEmployeeCnicFront', '');
        setDropPreviewByInput('HrEmployeeCnicBack', '');
    }
    function setViewMedia(wrapId, linkId, imageUrl)
    {
        var $wrap = $('#' + wrapId);
        var $link = $('#' + linkId);
        if($.trim(String(imageUrl || '')) === '')
        {
            $wrap.html('<span class="hr-view-media-empty"><?php echo addslashes(__('N/A'));?></span>');
            $link.addClass('hidden').attr('href', 'javascript:void(0);');
            return;
        }
        $wrap.html('<img src="' + escHtml(imageUrl) + '" alt="Image">');
        $link.removeClass('hidden').attr('href', imageUrl);
    }
    function formatMoney(value)
    {
        var amount = parseFloat(value || 0);
        if(isNaN(amount))
        {
            amount = 0;
        }
        return currencyText + ' ' + amount.toLocaleString(undefined, {minimumFractionDigits: 0, maximumFractionDigits: 2});
    }
    function refreshLinkedUserInfo(autoFill)
    {
        var projectId = parseInt($('#HrEmployeeProjectId').val(), 10) || 0;
        var linkedUserId = parseInt($('#HrEmployeeLinkedUserId').val(), 10) || 0;
        var meta = userMeta(projectId, linkedUserId);
        if(!meta)
        {
            $('#HrEmployeeRoleInfoWrap').addClass('hidden');
            $('#HrEmployeeWalletInfoWrap').addClass('hidden');
            return;
        }
        $('#HrEmployeeRoleInfoText').text(meta.role_name || '-');
        $('#HrEmployeeRoleInfoWrap').removeClass('hidden');
        var roleType = String(meta.role_type || '').toUpperCase();
        var roleName = String(meta.role_name || '').toLowerCase();
        var hasWallet = parseInt(meta.has_wallet || 0, 10) > 0;
        if(roleType === 'AGT' || roleName.indexOf('agent') !== -1 || hasWallet)
        {
            $('#HrEmployeeWalletInfoText').text(formatMoney(meta.wallet_balance || 0));
            $('#HrEmployeeWalletInfoWrap').removeClass('hidden');
        }
        else
        {
            $('#HrEmployeeWalletInfoWrap').addClass('hidden');
        }
        if(autoFill)
        {
            if(meta.name)
            {
                $('#HrEmployeeFullName').val(meta.name);
            }
            if(meta.mobile)
            {
                $('#HrEmployeeMobileNo').val(meta.mobile);
            }
            if(meta.email)
            {
                $('#HrEmployeeEmail').val(meta.email);
            }
        }
    }
    function openCreateEmployeeModal()
    {
        $('#HrEmployeePopupForm')[0].reset();
        resetDropPreviews();
        $('#HrEmployeeId').val('');
        $('#HrEmployeeFormMode').val('create');
        $('#HrEmployeeCode').val(autoGeneratedText).attr('readonly', true).attr('placeholder', autoGeneratedText).addClass('text-muted');
        $('#HrEmployeeModalTitle').text(createTitle);
        $('#HrEmployeeSubmitBtn').html('<span class="fa fa-save"></span> ' + saveBtnText);
        var projectId = parseInt($('#HrEmployeeProjectId').val(), 10) || defaultProjectId || 0;
        if(projectId > 0)
        {
            $('#HrEmployeeProjectId').val(String(projectId));
        }
        refreshEmployeeCodePreview(projectId);
        renderLinkedUserOptions(projectId, 0, '');
        refreshDepartmentDesignationOptions(projectId, '', '');
        refreshLinkedUserInfo(false);
        enforceScopeSalaryType();
        $('#HrEmployeeModal').modal('show');
    }

    $('#OpenCreateHrEmployeeModalBtn').on('click', function(e){
        e.preventDefault();
        openCreateEmployeeModal();
    });
    $(document).on('change', '#HrEmployeeProjectId', function(){
        var projectId = parseInt($(this).val(), 10) || 0;
        refreshEmployeeCodePreview(projectId);
        renderLinkedUserOptions(projectId, 0, '');
        refreshDepartmentDesignationOptions(projectId, '', '');
        refreshLinkedUserInfo(false);
    });
    $(document).on('change', '#HrEmployeeLinkedUserId', function(){
        refreshLinkedUserInfo(true);
    });
    $(document).on('input blur', '#HrEmployeeCnic', function(){
        $(this).val(formatEmployeeCnic($(this).val()));
    });
    $(document).on('click', '.js-edit-hr-employee', function(e){
        e.preventDefault();
        var $btn = $(this);
        var projectId = parseInt($btn.attr('data-project-id'), 10) || 0;
        var linkedUserId = parseInt($btn.attr('data-linked-user-id'), 10) || 0;
        $('#HrEmployeeId').val(String(parseInt($btn.attr('data-employee-id'), 10) || 0));
        $('#HrEmployeeFormMode').val('edit');
        $('#HrEmployeeCode').attr('readonly', false).attr('placeholder', '').removeClass('text-muted');
        $('#HrEmployeeProjectId').val(String(projectId));
        renderLinkedUserOptions(projectId, linkedUserId, ($btn.attr('data-linked-user-label') || ''));
        refreshDepartmentDesignationOptions(projectId, ($btn.attr('data-department') || ''), ($btn.attr('data-designation') || ''));
        $('#HrEmployeeFullName').val($btn.attr('data-full-name') || '');
        $('#HrEmployeeFatherName').val($btn.attr('data-father-name') || '');
        $('#HrEmployeeCode').val($btn.attr('data-employee-code') || '');
        $('#HrEmployeeCnic').val(formatEmployeeCnic($btn.attr('data-cnic') || ''));
        $('#HrEmployeeDob').val($btn.attr('data-dob') || '');
        $('#HrEmployeeEmail').val($btn.attr('data-email') || '');
        $('#HrEmployeeMobileNo').val($btn.attr('data-mobile-no') || '');
        $('#HrEmployeeAddress').val($btn.attr('data-address') || '');
        $('#HrEmployeeReference').val($btn.attr('data-reference') || '');
        $('#HrEmployeeBaseSalary').val($btn.attr('data-base-salary') || '');
        $('#HrEmployeeSalaryType').val($btn.attr('data-salary-type') || 'monthly');
        $('#HrEmployeeStatus').val($btn.attr('data-status') || 'active');
        enforceScopeSalaryType();
        setDropPreviewByInput('HrEmployeeProfilePhoto', $btn.attr('data-profile-photo-url') || '');
        setDropPreviewByInput('HrEmployeeCnicFront', $btn.attr('data-cnic-front-url') || '');
        setDropPreviewByInput('HrEmployeeCnicBack', $btn.attr('data-cnic-back-url') || '');
        $('#HrEmployeeModalTitle').text(updateTitle);
        $('#HrEmployeeSubmitBtn').html('<span class="fa fa-save"></span> ' + updateBtnText);
        refreshLinkedUserInfo(false);
        $('#HrEmployeeModal').modal('show');
    });
    $(document).on('click', '.js-view-hr-employee', function(e){
        e.preventDefault();
        var $btn = $(this);
        $('#HrViewFullName').text(nvl($btn.attr('data-full-name')));
        $('#HrViewFatherName').text(nvl($btn.attr('data-father-name')));
        $('#HrViewCode').text(nvl($btn.attr('data-employee-code')));
        $('#HrViewLinkedUser').text(nvl($btn.attr('data-linked-user')));
        $('#HrViewWallet').text(nvl($btn.attr('data-linked-wallet')));
        $('#HrViewLabourLedger').text(nvl($btn.attr('data-labour-ledger')));
        $('#HrViewDepartment').text(nvl($btn.attr('data-department')));
        $('#HrViewDesignation').text(nvl($btn.attr('data-designation')));
        $('#HrViewMobile').text(nvl($btn.attr('data-mobile-no')));
        $('#HrViewEmail').text(nvl($btn.attr('data-email')));
        $('#HrViewDob').text(formatYmdToDmy($btn.attr('data-dob')));
        $('#HrViewCnic').text(nvl($btn.attr('data-cnic')));
        $('#HrViewAddress').text(nvl($btn.attr('data-address')));
        $('#HrViewReference').text(nvl($btn.attr('data-reference')));
        setViewMedia('HrViewProfileWrap', 'HrViewProfileLink', $btn.attr('data-profile-photo-url') || '');
        setViewMedia('HrViewCnicFrontWrap', 'HrViewCnicFrontLink', $btn.attr('data-cnic-front-url') || '');
        setViewMedia('HrViewCnicBackWrap', 'HrViewCnicBackLink', $btn.attr('data-cnic-back-url') || '');
        $('#HrEmployeeViewModal').modal('show');
    });

    $(document).on('click', '.js-open-attendance-popup', function(e){
        e.preventDefault();
        var button = $(this);
        var employeeId = parseInt(button.attr('data-employee-id'), 10) || 0;
        var employeeName = button.attr('data-employee-name') || '';
        var projectId = parseInt(button.attr('data-project-id'), 10) || 0;
        $('#HrAttendanceEmployeeId').val(employeeId);
        $('#HrAttendanceProjectId').val(projectId);
        $('#HrAttendanceEmployeeName').text(employeeName !== '' ? employeeName : '-');
        $('#HrAttendanceDate').val('<?php echo $defaultAttendanceDate;?>');
        $('#HrAttendanceStatus').val('present');
        $('#HrAttendanceInTime').val('');
        $('#HrAttendanceOutTime').val('');
        $('#HrAttendanceOvertimeHours').val('0');
        $('#HrAttendanceNote').val('');
        $('#HrEmployeeAttendanceModal').modal('show');
    });
    $(document).on('click', '.js-toggle-employee-section', function(e){
        e.preventDefault();
        var $link = $(this);
        var $headerRow = $link.closest('tr.hr-employee-section-head');
        var sectionKey = String($link.attr('data-section-key') || '');
        var targetClass = String($link.attr('data-target-class') || '');
        var $rows = $();
        if(sectionKey !== '')
        {
            $rows = $('#HrEmployeesAccordionTable tbody').find('tr[data-employee-section="' + sectionKey + '"]');
        }
        if(!$rows.length && targetClass !== '')
        {
            $rows = $('#HrEmployeesAccordionTable tbody').find('tr.' + targetClass);
        }
        if(!$rows.length)
        {
            return;
        }
        var collapseNow = !$headerRow.hasClass('hr-employee-section-collapsed');
        $rows.toggleClass('hr-employee-section-row-hidden', collapseNow);
        $headerRow.toggleClass('hr-employee-section-collapsed', collapseNow);
        $link.find('.js-section-caret')
            .toggleClass('fa-caret-right', collapseNow)
            .toggleClass('fa-caret-down', !collapseNow);
        $link.attr('aria-expanded', collapseNow ? 'false' : 'true');
    });

    bindHrDropUpload($(document));
    initHrEmployeeCropperModal();

    if(defaultProjectId > 0 || $('#HrEmployeeProjectId').val())
    {
        var initialProjectId = parseInt($('#HrEmployeeProjectId').val(), 10) || defaultProjectId || 0;
        refreshEmployeeCodePreview(initialProjectId);
        renderLinkedUserOptions(initialProjectId, 0, '');
        refreshDepartmentDesignationOptions(initialProjectId, $('#HrEmployeeDepartment').val() || '', $('#HrEmployeeDesignation').val() || '');
        refreshLinkedUserInfo(false);
        enforceScopeSalaryType();
    }
    var quickAttendanceEmployeeId = <?php echo isset($this->request->query['attendance_employee']) ? (int)$this->request->query['attendance_employee'] : 0;?>;
    if(quickAttendanceEmployeeId > 0)
    {
        var $quickAttendanceBtn = $('.js-open-attendance-popup[data-employee-id="' + quickAttendanceEmployeeId + '"]').first();
        if($quickAttendanceBtn.length)
        {
            $quickAttendanceBtn.trigger('click');
        }
    }
})(jQuery);
</script>
