<?php
App::uses('CakeTime', 'Utility');
App::uses('CakeNumber', 'Utility');
class DashboardsController extends AdminAppController
{
    public $components = array('HighCharts.HighCharts');
    protected $_dashboardDefaultPeriod = 'calendar_month';
    protected $_dashboardRoutePermissionCache = array();
    protected $_dashboardControllerPermissionCache = array();
    protected function _isGlobalDashboardAdmin()
    {
        return $this->_isSuperAdminSession();
    }

    protected function _dashboardRoleType()
    {
        return strtoupper(trim((string)$this->userType));
    }

    protected function _dashboardUserGroupId()
    {
        if (!empty($this->adminValue['User']['ugroup_id'])) {
            return (int)$this->adminValue['User']['ugroup_id'];
        }
        $sessionUser = (array)$this->Session->read('User');
        if (!empty($sessionUser['User']['ugroup_id'])) {
            return (int)$sessionUser['User']['ugroup_id'];
        }
        return 0;
    }

    protected function _dashboardCanAccessRoute($route, $requiredRight = null)
    {
        if ($this->_isGlobalDashboardAdmin()) {
            return true;
        }
        if (!is_array($route)) {
            return false;
        }
        $controllerName = strtolower(trim((string)(isset($route['controller']) ? $route['controller'] : '')));
        $actionName = strtolower(trim((string)(isset($route['action']) ? $route['action'] : 'index')));
        if ($controllerName === '') {
            return false;
        }
        $requiredRight = trim((string)$requiredRight);
        $cacheKey = $controllerName . '::' . $actionName . '::' . $requiredRight;
        if (isset($this->_dashboardRoutePermissionCache[$cacheKey])) {
            return (bool)$this->_dashboardRoutePermissionCache[$cacheKey];
        }
        if ($this->_isAgencyAdminSession() && !$this->_isSuperAdminOnlyController($controllerName)) {
            $this->_dashboardRoutePermissionCache[$cacheKey] = true;
            return true;
        }
        $ugroupId = $this->_dashboardUserGroupId();
        if ($ugroupId <= 0) {
            $this->_dashboardRoutePermissionCache[$cacheKey] = false;
            return false;
        }
        $permissionRow = $this->_findPermissionPageRow($ugroupId, $controllerName, $actionName);
        if (empty($permissionRow['PageRight'])) {
            $this->_dashboardRoutePermissionCache[$cacheKey] = false;
            return false;
        }
        $normalizedRight = $this->_normalizePageRightRow($permissionRow['PageRight']);
        $requiredField = $requiredRight !== '' ? $requiredRight : $this->_requiredPermissionFieldByAction($actionName);
        $isAllowed = (!empty($normalizedRight['view_right']) && !empty($normalizedRight[$requiredField]));
        $this->_dashboardRoutePermissionCache[$cacheKey] = $isAllowed;
        return $isAllowed;
    }

    protected function _dashboardCanAccessController($controllerName)
    {
        if ($this->_isGlobalDashboardAdmin()) {
            return true;
        }
        $controllerName = strtolower(trim((string)$controllerName));
        if ($controllerName === '') {
            return false;
        }
        if ($this->_isAgencyAdminSession() && !$this->_isSuperAdminOnlyController($controllerName)) {
            return true;
        }
        if (isset($this->_dashboardControllerPermissionCache[$controllerName])) {
            return (bool)$this->_dashboardControllerPermissionCache[$controllerName];
        }
        $ugroupId = $this->_dashboardUserGroupId();
        if ($ugroupId <= 0) {
            $this->_dashboardControllerPermissionCache[$controllerName] = false;
            return false;
        }
        $this->loadModel('Page');
        $row = $this->Page->find('first', array(
            'joins' => array(
                array(
                    'table' => 'page_rights',
                    'alias' => 'PageRight',
                    'type' => 'Inner',
                    'conditions' => array('Page.id=PageRight.page_id')
                )
            ),
            'conditions' => array(
                'PageRight.ugroup_id' => $ugroupId,
                'PageRight.view_right' => 1,
                'LOWER(Page.controller_name)' => $controllerName
            ),
            'fields' => array('Page.id'),
            'recursive' => -1
        ));
        $isAllowed = !empty($row['Page']['id']);
        $this->_dashboardControllerPermissionCache[$controllerName] = $isAllowed;
        return $isAllowed;
    }

    protected function _dashboardCardVisibleForRole($visibility, $allowPersonalFinanceDashboard, $allowAgencyFinanceDashboard)
    {
        $visibility = strtolower(trim((string)$visibility));
        if ($visibility === 'finance') {
            return (bool)$allowPersonalFinanceDashboard;
        }
        if ($visibility === 'agency') {
            return (bool)$allowAgencyFinanceDashboard;
        }
        return true;
    }

    protected function _filterDashboardCards($cards, $allowPersonalFinanceDashboard, $allowAgencyFinanceDashboard)
    {
        $filteredCards = array();
        foreach ((array)$cards as $card) {
            $visibility = isset($card['visibility']) ? $card['visibility'] : 'all';
            if (!$this->_dashboardCardVisibleForRole($visibility, $allowPersonalFinanceDashboard, $allowAgencyFinanceDashboard)) {
                continue;
            }
            if (!empty($card['url']) && !$this->_dashboardCanAccessRoute($card['url'])) {
                continue;
            }
            $filteredCards[] = $card;
        }
        return $filteredCards;
    }

    protected function _filterDashboardLinkedCards($cards)
    {
        $filteredCards = array();
        foreach ((array)$cards as $card) {
            if (!empty($card['url']) && !$this->_dashboardCanAccessRoute($card['url'])) {
                continue;
            }
            $filteredCards[] = $card;
        }
        return $filteredCards;
    }

    protected function _filterDashboardTransactions($transactions, $allowInstallments, $allowVouchers)
    {
        $filteredTransactions = array();
        foreach ((array)$transactions as $transactionRow) {
            $source = strtolower(trim((string)(isset($transactionRow['source']) ? $transactionRow['source'] : '')));
            if ($source === 'installment' && !$allowInstallments) {
                continue;
            }
            if ($source === 'voucher' && !$allowVouchers) {
                continue;
            }
            $filteredTransactions[] = $transactionRow;
        }
        return array_slice($filteredTransactions, 0, 5);
    }

    protected function _dashboardRecentDealReceipts($conditionPro, $agencyList, $dateConditions = array(), $limit = 5)
    {
        $recentPaymentTransactions = array();
        $rows = $this->DealsPayment->find('all', array(
            'joins' => array(
                array('table' => 'deals', 'alias' => 'Deal', 'type' => 'LEFT', 'conditions' => array('Deal.id=DealsPayment.deal_id')),
                array('table' => 'properties', 'alias' => 'Property', 'type' => 'LEFT', 'conditions' => array('Property.id=Deal.property_id')),
                array('table' => 'paymenttypes', 'alias' => 'Paymenttype', 'type' => 'LEFT', 'conditions' => array('Paymenttype.id=DealsPayment.paymenttype_id'))
            ),
            'conditions' => $this->_buildAndConditions($conditionPro, $dateConditions),
            'fields' => array(
                'DealsPayment.id',
                'DealsPayment.payment_date',
                'DealsPayment.total_amount',
                'DealsPayment.receipt_no',
                'DealsPayment.ard_transaction_id',
                'Property.project_id',
                'Deal.invoice_no',
                'Paymenttype.name'
            ),
            'order' => array('DealsPayment.payment_date' => 'DESC', 'DealsPayment.id' => 'DESC'),
            'limit' => max(15, ((int)$limit * 6)),
            'recursive' => -1
        ));
        foreach ((array)$rows as $dealPaymentRow) {
            $paymentDate = isset($dealPaymentRow['DealsPayment']['payment_date']) ? trim((string)$dealPaymentRow['DealsPayment']['payment_date']) : '';
            if ($paymentDate === '' || $paymentDate === '0000-00-00') {
                continue;
            }
            $paymentId = (int)$dealPaymentRow['DealsPayment']['id'];
            $projectIdForRow = isset($dealPaymentRow['Property']['project_id']) ? (int)$dealPaymentRow['Property']['project_id'] : 0;
            $referenceNo = trim((string)$dealPaymentRow['DealsPayment']['receipt_no']);
            if ($referenceNo === '') {
                $referenceNo = trim((string)$dealPaymentRow['Deal']['invoice_no']);
            }
            if ($referenceNo === '') {
                $referenceNo = 'RCPT-' . $paymentId;
            }
            $agencyNameRow = (isset($agencyList[$projectIdForRow]) && trim((string)$agencyList[$projectIdForRow]) !== '') ? (string)$agencyList[$projectIdForRow] : '-';
            $recentPaymentTransactions[] = array(
                'source' => 'installment',
                'source_label' => __('Installment'),
                'date_raw' => $paymentDate,
                'date_text' => date('d-M-Y', strtotime($paymentDate)),
                'reference' => $referenceNo,
                'description' => !empty($dealPaymentRow['Paymenttype']['name']) ? (string)$dealPaymentRow['Paymenttype']['name'] : __('Deal Receipt'),
                'agency_name' => $agencyNameRow,
                'amount' => isset($dealPaymentRow['DealsPayment']['total_amount']) ? (float)$dealPaymentRow['DealsPayment']['total_amount'] : 0,
                'print_route' => array('controller' => 'deals_payments', 'action' => 'printreceipt', $paymentId),
                'print_label' => __('Print Receipt'),
                'linked_transaction_id' => isset($dealPaymentRow['DealsPayment']['ard_transaction_id']) ? (int)$dealPaymentRow['DealsPayment']['ard_transaction_id'] : 0,
                'sort_time' => (int)strtotime($paymentDate . ' 12:00:00'),
                'sort_id' => $paymentId
            );
            if (count($recentPaymentTransactions) >= (int)$limit) {
                break;
            }
        }
        return $recentPaymentTransactions;
    }

    protected function _parseDashboardDate($value)
    {
        $value = trim((string)$value);
        if($value === '')
        {
            return '';
        }
        if(preg_match('/^\d{4}-\d{2}-\d{2}$/', $value))
        {
            return $value;
        }
        if(preg_match('/^\d{2}[-\/]\d{2}[-\/]\d{4}$/', $value))
        {
            $normalized = str_replace('/', '-', $value);
            try
            {
                return $this->CustomFunction->dateFormatBeforeSave($normalized);
            }
            catch(Exception $e)
            {
                // fallback below
            }
        }
        $ts = strtotime($value);
        if($ts === false)
        {
            return '';
        }
        return date('Y-m-d', $ts);
    }

    protected function _resolveDashboardDateRange($period, $startInput, $endInput, $todayYmd)
    {
        $period = strtolower(trim((string)$period));
        if($period === '')
        {
            $period = (string)$this->_dashboardDefaultPeriod;
        }

        $start = '';
        $end = '';
        $label = __('Lifetime');
        switch($period)
        {
            case 'day':
            case 'daily':
                $start = $todayYmd;
                $end = $todayYmd;
                $label = __('Daily');
                break;
            case 'year':
            case 'yearly':
                $year = date('Y', strtotime($todayYmd));
                $start = $year . '-01-01';
                $end = $year . '-12-31';
                $label = __('Yearly');
                break;
            case 'lifetime':
            case 'all':
                $start = '';
                $end = '';
                $label = __('Lifetime');
                break;
            case 'custom':
                $start = $startInput;
                $end = $endInput;
                if($start === '' || $end === '')
                {
                    $start = date('Y-m-01', strtotime($todayYmd));
                    $end = date('Y-m-t', strtotime($todayYmd));
                    $period = (string)$this->_dashboardDefaultPeriod;
                    $label = __('Monthly');
                }
                else
                {
                    $label = __('Custom');
                }
                break;
            case 'calendar_month':
                $start = date('Y-m-01', strtotime($todayYmd));
                $end = date('Y-m-t', strtotime($todayYmd));
                $label = __('Monthly');
                $period = 'calendar_month';
                break;
            case 'month':
            case 'monthly':
            default:
                $start = date('Y-m-d', strtotime($todayYmd . ' -29 days'));
                $end = $todayYmd;
                $label = __('Last 30 Days');
                $period = 'month';
                break;
        }
        if($start !== '' && $end !== '' && strtotime($start) > strtotime($end))
        {
            $tmp = $start;
            $start = $end;
            $end = $tmp;
        }
        return array($period, $start, $end, $label);
    }

    protected function _toFloat($value)
    {
        if($value === null || $value === false || $value === '')
        {
            return 0.0;
        }
        return (float)$value;
    }

    protected function _buildAndConditions()
    {
        $conditionParts = func_get_args();
        $conditions = array();
        foreach($conditionParts as $condition)
        {
            if($condition === null || $condition === false)
            {
                continue;
            }
            if(is_array($condition) && empty($condition))
            {
                continue;
            }
            if(is_string($condition) && trim($condition) === '')
            {
                continue;
            }
            $conditions[] = $condition;
        }
        if(empty($conditions))
        {
            return array();
        }
        if(count($conditions) === 1)
        {
            return $conditions[0];
        }
        return $conditions;
    }

    protected function _dashboardQuickDealOptions($projectId = 0, $limit = 200)
    {
        $projectId = (int)$projectId;
        if($projectId <= 0)
        {
            return array();
        }
        $rows = (array)$this->Deal->find('all', array(
            'joins' => array(
                array('table' => 'properties', 'alias' => 'Property', 'type' => 'INNER', 'conditions' => array('Property.id = Deal.property_id')),
                array('table' => 'clients', 'alias' => 'Client', 'type' => 'LEFT', 'conditions' => array('Client.id = Deal.client_id'))
            ),
            'conditions' => array('Property.project_id' => $projectId),
            'fields' => array('Deal.id', 'Deal.invoice_no', 'Client.name', 'Property.name'),
            'order' => array('Deal.id' => 'desc'),
            'limit' => max(1, min(250, (int)$limit)),
            'recursive' => -1
        ));
        $options = array();
        foreach($rows as $row)
        {
            $dealId = !empty($row['Deal']['id']) ? (int)$row['Deal']['id'] : 0;
            if($dealId <= 0)
            {
                continue;
            }
            $clientName = !empty($row['Client']['name']) ? trim((string)$row['Client']['name']) : '';
            $invoiceNo = !empty($row['Deal']['invoice_no']) ? trim((string)$row['Deal']['invoice_no']) : '';
            $propertyName = !empty($row['Property']['name']) ? trim((string)$row['Property']['name']) : '';
            $parts = array();
            if($clientName !== '')
            {
                $parts[] = $clientName;
            }
            if($invoiceNo !== '')
            {
                $parts[] = $invoiceNo;
            }
            if($propertyName !== '')
            {
                $parts[] = $propertyName;
            }
            if(empty($parts))
            {
                $parts[] = __('Deal') . ' #' . $dealId;
            }
            $options[$dealId] = implode(' | ', $parts);
        }
        return $options;
    }

    protected function _dashboardExpenseApprovalActions($projectId, $isGlobalAdmin = false)
    {
        $projectId = (int)$projectId;
        $result = array('show' => false, 'project_id' => $projectId, 'actions' => array());
        if($projectId <= 0)
        {
            return $result;
        }
        try
        {
            $this->loadModel('Admin.Ardsetting');
            $this->loadModel('Admin.Ardexpenseapprovalrequest');
            $setting = (array)$this->Ardsetting->find('first', array(
                'fields' => array('Ardsetting.expense_approval_enabled', 'Ardsetting.expense_approval_ceo_user_id'),
                'conditions' => array('Ardsetting.project_id' => $projectId),
                'order' => array('Ardsetting.id' => 'desc'),
                'recursive' => -1
            ));
            if(empty($setting['Ardsetting']['expense_approval_enabled']))
            {
                return $result;
            }
            $groupId = $this->_dashboardUserGroupId();
            $groupName = '';
            if(!empty($this->adminValue['Ugroup']['name']))
            {
                $groupName = strtolower(trim((string)$this->adminValue['Ugroup']['name']));
            }
            $userId = !empty($this->adminValue['User']['id']) ? (int)$this->adminValue['User']['id'] : (int)$this->luserId;
            $isAccountant = ($groupId === 6 || strpos($groupName, 'account') !== false);
            $isManager = ($groupId === 3 || strpos($groupName, 'manager') !== false);
            $configuredCeoId = !empty($setting['Ardsetting']['expense_approval_ceo_user_id']) ? (int)$setting['Ardsetting']['expense_approval_ceo_user_id'] : 0;
            $isCeo = ($groupId === 4 || $groupName === 'ceo' || $groupName === 'agency' || ($configuredCeoId > 0 && $configuredCeoId === $userId));
            $canOpenApprovals = $this->_dashboardCanAccessRoute(array('controller' => 'Ardaccounts', 'action' => 'expenseapprovals', $projectId));

            if(($isGlobalAdmin || $isAccountant) && $this->_dashboardCanAccessRoute(array('controller' => 'Ardaccounts', 'action' => 'transactions', $projectId)))
            {
                $result['actions'][] = array(
                    'key' => 'create',
                    'label' => __('Create Expense Request'),
                    'role' => __('Accountant'),
                    'count' => null,
                    'icon' => 'fa-plus-circle',
                    'class' => 'success',
                    'url' => array('controller' => 'Ardaccounts', 'action' => 'transactions', $projectId, '?' => array('kind' => 'expense'))
                );
            }
            if($canOpenApprovals && $isAccountant)
            {
                $requestedPending = (int)$this->Ardexpenseapprovalrequest->find('count', array(
                    'conditions' => array(
                        'Ardexpenseapprovalrequest.project_id' => $projectId,
                        'Ardexpenseapprovalrequest.created_by' => $userId,
                        'Ardexpenseapprovalrequest.status' => array('Pending Manager', 'Pending CEO')
                    ),
                    'recursive' => -1
                ));
                $result['actions'][] = array(
                    'key' => 'requested',
                    'label' => __('Requested Vouchers'),
                    'role' => __('Awaiting approval'),
                    'count' => $requestedPending,
                    'icon' => 'fa-file-text-o',
                    'class' => 'requested',
                    'url' => array('controller' => 'Ardaccounts', 'action' => 'expenseapprovals', $projectId)
                );
            }
            if($canOpenApprovals && ($isGlobalAdmin || $isManager))
            {
                $managerPending = (int)$this->Ardexpenseapprovalrequest->find('count', array(
                    'conditions' => array('Ardexpenseapprovalrequest.project_id' => $projectId, 'Ardexpenseapprovalrequest.status' => 'Pending Manager'),
                    'recursive' => -1
                ));
                $result['actions'][] = array(
                    'key' => 'manager',
                    'label' => __('Manager Approvals'),
                    'role' => __('Pending verification'),
                    'count' => $managerPending,
                    'icon' => 'fa-check-square-o',
                    'class' => 'warning',
                    'url' => array('controller' => 'Ardaccounts', 'action' => 'expenseapprovals', $projectId, '?' => array('status' => 'Pending Manager'))
                );
            }
            if($canOpenApprovals && ($isGlobalAdmin || $isCeo))
            {
                $ceoPending = (int)$this->Ardexpenseapprovalrequest->find('count', array(
                    'conditions' => array('Ardexpenseapprovalrequest.project_id' => $projectId, 'Ardexpenseapprovalrequest.status' => 'Pending CEO'),
                    'recursive' => -1
                ));
                $result['actions'][] = array(
                    'key' => 'ceo',
                    'label' => __('CEO Approvals'),
                    'role' => __('Pending final approval'),
                    'count' => $ceoPending,
                    'icon' => 'fa-gavel',
                    'class' => 'primary',
                    'url' => array('controller' => 'Ardaccounts', 'action' => 'expenseapprovals', $projectId, '?' => array('status' => 'Pending CEO'))
                );
            }
            $result['show'] = !empty($result['actions']);
        }
        catch(Exception $e)
        {
            CakeLog::write('error', 'Dashboard expense approval actions failed: '.$e->getMessage());
        }
        return $result;
    }

    public function index()
    {
        try
        {
            $this->loadModel('Favourite');
            $this->loadModel('Deal');
            $this->loadModel('DealsPayment');
            $this->loadModel('ExpensesPayment');
            $this->loadModel('PurchasesPayment');
            $this->loadModel('Property');
            $this->loadModel('Calendar');
            $this->loadModel('Lead');
            $this->loadModel('Realestateletter');
            $this->loadModel('BallotingUnit');
            $this->loadModel('BallotingAllotment');
            $this->loadModel('Admin.Ardtransaction');
            $this->loadModel('Admin.Ardledger');
            $this->loadModel('Admin.Ardconstructionproject');
            $this->loadModel('Admin.Ardstock');
            $this->loadModel('Admin.Ardpurchaseorder');
            $this->loadModel('Admin.Ardgrn');
            $currency = !empty($this->currency) ? $this->currency : 'Rs ';

            $convertedNow = CakeTime::convert(time(), $this->siteTimezone);
            if($convertedNow === false || $convertedNow === null)
            {
                $convertedNow = time();
            }
            $today = CakeTime::format('Y-m-d', $convertedNow);
            $currentDateTime = CakeTime::format('Y-m-d H:i:s', $convertedNow);
            $this->Favourite->deleteAll(array('client_id' => NULL, 'created <' => $today));

            $isGlobalAdmin = $this->_isGlobalDashboardAdmin();
            $roleTypeKey = $this->_dashboardRoleType();
            $isCeoDashboardUser = $this->_isCeoRoleContext((array)$this->adminValue);
            $isSystemManagerDashboardUser = $this->_isAgencySystemManagerRoleContext((array)$this->adminValue);
            $isAgencyDashboardUser = ($roleTypeKey === 'AGY' || $isSystemManagerDashboardUser);
            $isAgentDashboardUser = ($roleTypeKey === 'AGT');
            $allowPersonalFinanceDashboard = ($isGlobalAdmin || $isAgencyDashboardUser || $isAgentDashboardUser);
            $allowAgencyFinanceDashboard = ($isGlobalAdmin || $isAgencyDashboardUser);
            $agencyList = $isSystemManagerDashboardUser ? $this->_assignedAgencyProjectList((int)$this->luserId) : $this->CustomFunction->agencyWiseList($this->userType, $this->agencyId);
            $canSelectDashboardAgency = ($isGlobalAdmin || $isSystemManagerDashboardUser);
            $assignedDashboardProjectIds = array_values(array_unique(array_filter(array_map('intval', array_keys((array)$agencyList)))));
            $isAssignedAgencyAggregate = ($isSystemManagerDashboardUser && array_key_exists('project_id', (array)$this->request->query) && (int)$this->request->query['project_id'] === 0);

            $selectedProjectId = 0;
            if($isGlobalAdmin)
            {
                $selectedProjectId = isset($this->request->query['project_id']) ? (int)$this->request->query['project_id'] : 0;
            }
            else
            {
                $selectedProjectId = $isAssignedAgencyAggregate ? 0 : (int)$this->agencyId;
            }
            if($isSystemManagerDashboardUser && !$isAssignedAgencyAggregate && !isset($agencyList[$selectedProjectId]))
            {
                $assignedProjectIds = array_keys($agencyList);
                $selectedProjectId = !empty($assignedProjectIds) ? (int)reset($assignedProjectIds) : 0;
            }
            if(!$isGlobalAdmin && !$isAssignedAgencyAggregate && $selectedProjectId <= 0)
            {
                $selectedProjectId = (int)$this->agencyId;
            }

            $defaultPeriodForRole = (string)$this->_dashboardDefaultPeriod;
            $selectedPeriod = isset($this->request->query['period']) ? strtolower(trim((string)$this->request->query['period'])) : (string)$defaultPeriodForRole;
            $requestedStart = $this->_parseDashboardDate(isset($this->request->query['start_date']) ? $this->request->query['start_date'] : '');
            $requestedEnd = $this->_parseDashboardDate(isset($this->request->query['end_date']) ? $this->request->query['end_date'] : '');
            list($selectedPeriod, $filterStartDate, $filterEndDate, $filterLabel) = $this->_resolveDashboardDateRange($selectedPeriod, $requestedStart, $requestedEnd, $today);

            $quickActionProjectId = (int)$selectedProjectId;
            if(!$isGlobalAdmin && !$isAssignedAgencyAggregate && $quickActionProjectId <= 0)
            {
                $quickActionProjectId = (int)$this->agencyId;
            }
            $selectedQuickDealId = isset($this->request->query['quick_deal_id']) ? (int)$this->request->query['quick_deal_id'] : 0;
            $dashboardQuickActions = array(
                'show' => $allowAgencyFinanceDashboard,
                'project_id' => $quickActionProjectId,
                'can_add_income' => false,
                'can_add_expense' => false,
                'can_journal_voucher' => false,
                'can_pay_installment' => false,
                'income_url' => array('controller' => 'Ardaccounts', 'action' => 'transactions', $quickActionProjectId, '?' => array('kind' => 'income')),
                'expense_url' => array('controller' => 'Ardaccounts', 'action' => 'transactions', $quickActionProjectId, '?' => array('kind' => 'expense')),
                'journal_url' => array('controller' => 'Ardaccounts', 'action' => 'transfer', $quickActionProjectId),
                'deal_options' => array(),
                'selected_deal_id' => $selectedQuickDealId,
                'needs_project' => ($quickActionProjectId <= 0),
                'pay_installment_base_route' => array('controller' => 'DealsPayments', 'action' => 'add')
            );
            if($allowAgencyFinanceDashboard && $quickActionProjectId > 0)
            {
                $dashboardQuickActions['can_add_income'] = $this->_dashboardCanAccessRoute(array('controller' => 'Ardaccounts', 'action' => 'transactions', $quickActionProjectId), 'save_right');
                $dashboardQuickActions['can_add_expense'] = $dashboardQuickActions['can_add_income'];
                $dashboardQuickActions['can_journal_voucher'] = $this->_dashboardCanAccessRoute(array('controller' => 'Ardaccounts', 'action' => 'transfer', $quickActionProjectId), 'save_right');
                $dashboardQuickActions['can_pay_installment'] = $this->_dashboardCanAccessRoute(array('controller' => 'DealsPayments', 'action' => 'add', 1));
                if($dashboardQuickActions['can_pay_installment'])
                {
                    $dashboardQuickActions['deal_options'] = $this->_dashboardQuickDealOptions($quickActionProjectId, 200);
                    if($selectedQuickDealId > 0 && !isset($dashboardQuickActions['deal_options'][$selectedQuickDealId]))
                    {
                        $dashboardQuickActions['selected_deal_id'] = 0;
                    }
                }
            }
            $dashboardApprovalActions = $this->_dashboardExpenseApprovalActions($quickActionProjectId, $isGlobalAdmin);

            $permissionProjectId = $isGlobalAdmin ? ($selectedProjectId > 0 ? $selectedProjectId : null) : (int)$this->agencyId;
            if($isAssignedAgencyAggregate)
            {
                $permissionProjectId = null;
                $conditionPur = array('Purchase.project_id' => $assignedDashboardProjectIds);
                $conditionExp = array('Expense.project_id' => $assignedDashboardProjectIds);
                $conditionPro = array('Property.project_id' => $assignedDashboardProjectIds);
            }
            else
            {
                $conditionPur = $this->CustomFunction->agencyWisePermission($permissionProjectId, $this->userType, $this->agencyId, 'Purchase.project_id');
                $conditionExp = $this->CustomFunction->agencyWisePermission($permissionProjectId, $this->userType, $this->agencyId, 'Expense.project_id');
                $conditionPro = $this->CustomFunction->userWisePermission($permissionProjectId, $this->luserId, $this->userType, $this->agencyId, 'Property.project_id', 'Property.user_id');
            }
            if(!$isGlobalAdmin && !$isAssignedAgencyAggregate && (int)$this->agencyId > 0)
            {
                if(empty($conditionPur))
                {
                    $conditionPur = array('Purchase.project_id' => (int)$this->agencyId);
                }
                if(empty($conditionExp))
                {
                    $conditionExp = array('Expense.project_id' => (int)$this->agencyId);
                }
                if(empty($conditionPro))
                {
                    $conditionPro = array('Property.project_id' => (int)$this->agencyId);
                }
            }

            $dealPaymentDateCond = array();
            $expenseDateCond = array();
            $purchaseDateCond = array();
            $dealDateCond = array();
            if($filterStartDate !== '' && $filterEndDate !== '')
            {
                $dealPaymentDateCond = array('DealsPayment.payment_date BETWEEN ? AND ?' => array($filterStartDate, $filterEndDate));
                $expenseDateCond = array('ExpensesPayment.date BETWEEN ? AND ?' => array($filterStartDate, $filterEndDate));
                $purchaseDateCond = array('PurchasesPayment.date BETWEEN ? AND ?' => array($filterStartDate, $filterEndDate));
                $dealDateCond = array('Deal.date BETWEEN ? AND ?' => array($filterStartDate, $filterEndDate));
            }

            $propertyCreatedCond = array();
            if($filterStartDate !== '' && $filterEndDate !== '' && $this->Property->hasField('created'))
            {
                $propertyCreatedCond = array('Property.created BETWEEN ? AND ?' => array($filterStartDate . ' 00:00:00', $filterEndDate . ' 23:59:59'));
            }
            $propertyConditions = $this->_buildAndConditions($conditionPro, $propertyCreatedCond);
            $propertyCount = (int)$this->Property->find('count', array('conditions' => $propertyConditions));

            $dealCount = (int)$this->Deal->find('count', array(
                'joins' => array(
                    array('table' => 'properties', 'alias' => 'Property', 'type' => 'LEFT', 'conditions' => array('Deal.property_id=Property.id'))
                ),
                'conditions' => $this->_buildAndConditions($conditionPro, $dealDateCond),
                'recursive' => -1
            ));

            $expenseArr = $this->ExpensesPayment->find('all', array(
                'joins' => array(
                    array('table' => 'expenses', 'alias' => 'Expense', 'type' => 'LEFT', 'conditions' => array('Expense.id=ExpensesPayment.expense_id'))
                ),
                'conditions' => $this->_buildAndConditions($conditionExp, $expenseDateCond),
                'fields' => array('SUM(ExpensesPayment.amount) AS expense'),
                'recursive' => -1
            ));
            $totalExpenses = $this->_toFloat(isset($expenseArr[0][0]['expense']) ? $expenseArr[0][0]['expense'] : 0);

            $earningArr = $this->DealsPayment->find('all', array(
                'conditions' => $this->_buildAndConditions(
                    $conditionPro,
                    $dealPaymentDateCond,
                    array('NOT' => array('Salesreport.deal_status' => array(
                        'Refund', 'Refunded', 'Refund-Running', 'Refund Running', 'Running Refund',
                        'Transferd', 'Transferred', 'Permanent Blocked', 'Pemanent Blocked'
                    )))
                ),
                'fields' => array('SUM(DealsPayment.total_amount) AS earning', 'COUNT(DealsPayment.id) AS receipts_count'),
                'joins' => array(
                    array('table' => 'deals', 'alias' => 'Salesreport', 'type' => 'LEFT', 'conditions' => array('Salesreport.id=DealsPayment.deal_id')),
                    array('table' => 'properties', 'alias' => 'Property', 'type' => 'LEFT', 'conditions' => array('Salesreport.property_id=Property.id'))
                ),
                'recursive' => -1
            ));
            $totalSale = $this->_toFloat(isset($earningArr[0][0]['earning']) ? $earningArr[0][0]['earning'] : 0);
            $totalReceipts = (int)(isset($earningArr[0][0]['receipts_count']) ? $earningArr[0][0]['receipts_count'] : 0);

            $purchaseArr = $this->PurchasesPayment->find('all', array(
                'joins' => array(
                    array('table' => 'purchases', 'alias' => 'Purchase', 'type' => 'LEFT', 'conditions' => array('Purchase.id=PurchasesPayment.purchase_id'))
                ),
                'fields' => array('SUM(PurchasesPayment.amount) AS purchase'),
                'conditions' => $this->_buildAndConditions($conditionPur, $purchaseDateCond),
                'recursive' => -1
            ));
            $totalPurchase = $this->_toFloat(isset($purchaseArr[0][0]['purchase']) ? $purchaseArr[0][0]['purchase'] : 0);

            $dealAmountArr = $this->Deal->find('all', array(
                'joins' => array(
                    array('table' => 'properties', 'alias' => 'Property', 'type' => 'LEFT', 'conditions' => array('Deal.property_id=Property.id'))
                ),
                'conditions' => $this->_buildAndConditions($conditionPro, $dealDateCond),
                'fields' => array('SUM(Deal.total_amount) AS deal_total'),
                'recursive' => -1
            ));
            $totalDealValue = $this->_toFloat(isset($dealAmountArr[0][0]['deal_total']) ? $dealAmountArr[0][0]['deal_total'] : 0);

            $eligibleSalesExcludedStatuses = array(
                'Refund', 'Refunded', 'Refund-Running', 'Refund Running', 'Running Refund',
                'Transferd', 'Transferred', 'Permanent Blocked', 'Pemanent Blocked'
            );
            $eligibleDealTotalsRow = $this->Deal->find('first', array(
                'joins' => array(
                    array('table' => 'properties', 'alias' => 'Property', 'type' => 'LEFT', 'conditions' => array('Deal.property_id=Property.id'))
                ),
                'conditions' => $this->_buildAndConditions(
                    $conditionPro,
                    $dealDateCond,
                    array('NOT' => array('Deal.deal_status' => $eligibleSalesExcludedStatuses))
                ),
                'fields' => array(
                    'SUM(Deal.total_amount) AS eligible_deal_total',
                    'COALESCE(SUM((SELECT SUM(EligibleDealPayment.emi) FROM deals_payments EligibleDealPayment WHERE EligibleDealPayment.deal_id=Deal.id)),0) AS eligible_deal_paid'
                ),
                'recursive' => -1
            ));
            $eligibleDealTotal = $this->_toFloat(isset($eligibleDealTotalsRow[0]['eligible_deal_total']) ? $eligibleDealTotalsRow[0]['eligible_deal_total'] : 0);
            $eligibleDealPaid = $this->_toFloat(isset($eligibleDealTotalsRow[0]['eligible_deal_paid']) ? $eligibleDealTotalsRow[0]['eligible_deal_paid'] : 0);
            $eligibleDealBalance = round($eligibleDealTotal - $eligibleDealPaid, 2);

            $netBalance = round($totalSale - $totalExpenses - $totalPurchase, 2);
            $outstandingReceivable = round(max($totalDealValue - $totalSale, 0), 2);
            $collectionRate = ($totalDealValue > 0) ? round(($totalSale / $totalDealValue) * 100, 2) : 0;
            $lettersCount = 0;
            $ballotingUnitsCount = 0;
            $ballotingAllotmentsCount = 0;

            try
            {
                $lettersDateCond = array();
                if($filterStartDate !== '' && $filterEndDate !== '' && $this->Realestateletter->hasField('created'))
                {
                    $lettersDateCond = array('Realestateletter.created BETWEEN ? AND ?' => array($filterStartDate . ' 00:00:00', $filterEndDate . ' 23:59:59'));
                }
                $lettersConditions = $this->_buildAndConditions($conditionPro, $lettersDateCond);
                $lettersCount = (int)$this->Realestateletter->find('count', array(
                    'joins' => array(
                        array('table' => 'deals', 'alias' => 'Deal', 'type' => 'LEFT', 'conditions' => array('Deal.id=Realestateletter.deal_id')),
                        array('table' => 'properties', 'alias' => 'Property', 'type' => 'LEFT', 'conditions' => array('Property.id=Deal.property_id'))
                    ),
                    'conditions' => $lettersConditions,
                    'recursive' => -1
                ));
            }
            catch(Exception $e)
            {
                $lettersCount = 0;
            }

            try
            {
                $ballotingUnitConditions = array();
                $ballotingAllotmentConditions = array();
                if($selectedProjectId > 0)
                {
                    $ballotingUnitConditions['BallotingUnit.project_id'] = (int)$selectedProjectId;
                    $ballotingAllotmentConditions['BallotingAllotment.project_id'] = (int)$selectedProjectId;
                }
                elseif($isAssignedAgencyAggregate)
                {
                    $ballotingUnitConditions['BallotingUnit.project_id'] = $assignedDashboardProjectIds;
                    $ballotingAllotmentConditions['BallotingAllotment.project_id'] = $assignedDashboardProjectIds;
                }
                elseif(!$isGlobalAdmin)
                {
                    $ballotingUnitConditions['BallotingUnit.project_id'] = (int)$this->agencyId;
                    $ballotingAllotmentConditions['BallotingAllotment.project_id'] = (int)$this->agencyId;
                }
                if($filterStartDate !== '' && $filterEndDate !== '' && $this->BallotingUnit->hasField('created'))
                {
                    $ballotingUnitConditions['BallotingUnit.created >='] = $filterStartDate . ' 00:00:00';
                    $ballotingUnitConditions['BallotingUnit.created <='] = $filterEndDate . ' 23:59:59';
                }
                if($filterStartDate !== '' && $filterEndDate !== '' && $this->BallotingAllotment->hasField('allotment_date'))
                {
                    $ballotingAllotmentConditions['BallotingAllotment.allotment_date >='] = $filterStartDate;
                    $ballotingAllotmentConditions['BallotingAllotment.allotment_date <='] = $filterEndDate;
                }
                elseif($filterStartDate !== '' && $filterEndDate !== '' && $this->BallotingAllotment->hasField('created'))
                {
                    $ballotingAllotmentConditions['BallotingAllotment.created >='] = $filterStartDate . ' 00:00:00';
                    $ballotingAllotmentConditions['BallotingAllotment.created <='] = $filterEndDate . ' 23:59:59';
                }
                $ballotingUnitsCount = (int)$this->BallotingUnit->find('count', array(
                    'conditions' => $ballotingUnitConditions,
                    'recursive' => -1
                ));
                $ballotingAllotmentsCount = (int)$this->BallotingAllotment->find('count', array(
                    'conditions' => $ballotingAllotmentConditions,
                    'recursive' => -1
                ));
            }
            catch(Exception $e)
            {
                $ballotingUnitsCount = 0;
                $ballotingAllotmentsCount = 0;
            }

            $accountsCards = array();
            $constructionCards = array();
            $recentPaymentTransactions = array();
            $dashboardDealReceivedTotal = 0.0;
            $dashboardRecordedExpenseTotal = 0.0;
            $dashboardAccountsReportProjectId = (int)$selectedProjectId;
            $dashboardAccountsReportPreset = ($filterStartDate === '' && $filterEndDate === '') ? 'lifetime' : 'custom';
            try
            {
                $ardProjectId = (int)$selectedProjectId;
                if(!$isGlobalAdmin && !$isAssignedAgencyAggregate && $ardProjectId <= 0)
                {
                    $ardProjectId = (int)$this->agencyId;
                }

                $ardTxConditions = array();
                $ardLedgerConditions = array('Ardledger.is_trashed' => 0);
                $ardConstructionProjectConditions = array();
                $ardStockConditions = array();
                $ardPurchaseOrderConditions = array();
                $ardGrnConditions = array();
                if($ardProjectId > 0)
                {
                    $ardTxConditions['Ardtransaction.project_id'] = $ardProjectId;
                    $ardLedgerConditions['Ardledger.project_id'] = $ardProjectId;
                    $ardConstructionProjectConditions['Ardconstructionproject.project_id'] = $ardProjectId;
                    $ardStockConditions['Ardstock.project_id'] = $ardProjectId;
                    $ardPurchaseOrderConditions['Ardpurchaseorder.project_id'] = $ardProjectId;
                    $ardGrnConditions['Ardgrn.project_id'] = $ardProjectId;
                }
                elseif($isAssignedAgencyAggregate)
                {
                    $ardTxConditions['Ardtransaction.project_id'] = $assignedDashboardProjectIds;
                    $ardLedgerConditions['Ardledger.project_id'] = $assignedDashboardProjectIds;
                    $ardConstructionProjectConditions['Ardconstructionproject.project_id'] = $assignedDashboardProjectIds;
                    $ardStockConditions['Ardstock.project_id'] = $assignedDashboardProjectIds;
                    $ardPurchaseOrderConditions['Ardpurchaseorder.project_id'] = $assignedDashboardProjectIds;
                    $ardGrnConditions['Ardgrn.project_id'] = $assignedDashboardProjectIds;
                }
                if($filterStartDate !== '' && $filterEndDate !== '')
                {
                    $ardTxConditions['Ardtransaction.entry_date >='] = $filterStartDate;
                    $ardTxConditions['Ardtransaction.entry_date <='] = $filterEndDate;
                    $ardStockConditions['Ardstock.movement_date >='] = $filterStartDate;
                    $ardStockConditions['Ardstock.movement_date <='] = $filterEndDate;
                    if($this->Ardpurchaseorder->hasField('po_date'))
                    {
                        $ardPurchaseOrderConditions['Ardpurchaseorder.po_date >='] = $filterStartDate;
                        $ardPurchaseOrderConditions['Ardpurchaseorder.po_date <='] = $filterEndDate;
                    }
                    if($this->Ardgrn->hasField('grn_date'))
                    {
                        $ardGrnConditions['Ardgrn.grn_date >='] = $filterStartDate;
                        $ardGrnConditions['Ardgrn.grn_date <='] = $filterEndDate;
                    }
                    elseif($this->Ardgrn->hasField('created'))
                    {
                        $ardGrnConditions['Ardgrn.created >='] = $filterStartDate . ' 00:00:00';
                        $ardGrnConditions['Ardgrn.created <='] = $filterEndDate . ' 23:59:59';
                    }
                    if(!$this->Ardpurchaseorder->hasField('po_date') && $this->Ardpurchaseorder->hasField('created'))
                    {
                        $ardPurchaseOrderConditions['Ardpurchaseorder.created >='] = $filterStartDate . ' 00:00:00';
                        $ardPurchaseOrderConditions['Ardpurchaseorder.created <='] = $filterEndDate . ' 23:59:59';
                    }
                    if($this->Ardledger->hasField('created'))
                    {
                        $ardLedgerConditions['Ardledger.created >='] = $filterStartDate . ' 00:00:00';
                        $ardLedgerConditions['Ardledger.created <='] = $filterEndDate . ' 23:59:59';
                    }
                    if($this->Ardconstructionproject->hasField('start_date'))
                    {
                        $ardConstructionProjectConditions['Ardconstructionproject.start_date >='] = $filterStartDate;
                        $ardConstructionProjectConditions['Ardconstructionproject.start_date <='] = $filterEndDate;
                    }
                    elseif($this->Ardconstructionproject->hasField('created'))
                    {
                        $ardConstructionProjectConditions['Ardconstructionproject.created >='] = $filterStartDate . ' 00:00:00';
                        $ardConstructionProjectConditions['Ardconstructionproject.created <='] = $filterEndDate . ' 23:59:59';
                    }
                }
                $ardTxConditions['Ardtransaction.status'] = 'Posted';

                $ardTotals = $this->Ardtransaction->find('first', array(
                    'fields' => array(
                        'SUM(CASE WHEN Ardtransaction.type="income" THEN Ardtransaction.amount ELSE 0 END) AS income_total',
                        'SUM(CASE WHEN Ardtransaction.type="expense" THEN Ardtransaction.amount ELSE 0 END) AS expense_total',
                        'COUNT(Ardtransaction.id) AS voucher_count'
                    ),
                    'conditions' => $ardTxConditions,
                    'recursive' => -1
                ));
                $ardIncomeTotal = $this->_toFloat(isset($ardTotals[0]['income_total']) ? $ardTotals[0]['income_total'] : 0);
                $ardExpenseTotal = $this->_toFloat(isset($ardTotals[0]['expense_total']) ? $ardTotals[0]['expense_total'] : 0);
                $ardVoucherCount = (int)(isset($ardTotals[0]['voucher_count']) ? $ardTotals[0]['voucher_count'] : 0);
                $ardNet = round($ardIncomeTotal - $ardExpenseTotal, 2);
                $ardDashboardPreset = ($filterStartDate === '' && $filterEndDate === '') ? 'lifetime' : 'custom';
                $dashboardAccountsReportProjectId = $ardProjectId;
                $dashboardAccountsReportPreset = $ardDashboardPreset;
                $dashboardRecordedExpenseTotal = $ardExpenseTotal;

                $dealReceivedConditions = $ardTxConditions;
                $dealReceivedConditions['Ardtransaction.type'] = 'income';
                $dealReceivedConditions['Ardtransaction.amount >'] = 0;
                $dealReceivedConditions['OR'] = array(
                    'Ardtransaction.source_type' => 'deal_receipt',
                    'Ardtransaction.description LIKE' => 'Deal receipt %'
                );
                $dealReceivedRow = $this->Ardtransaction->find('first', array(
                    'fields' => array('SUM(Ardtransaction.amount) AS received_total'),
                    'conditions' => $dealReceivedConditions,
                    'recursive' => -1
                ));
                $dashboardDealReceivedTotal = $this->_toFloat(isset($dealReceivedRow[0]['received_total']) ? $dealReceivedRow[0]['received_total'] : 0);

                $bankLedgerCount = (int)$this->Ardledger->find('count', array(
                    'conditions' => array_merge($ardLedgerConditions, array('Ardledger.type' => 'bank')),
                    'recursive' => -1
                ));
                $expenseLedgerCount = (int)$this->Ardledger->find('count', array(
                    'conditions' => array_merge($ardLedgerConditions, array('Ardledger.type' => 'expense')),
                    'recursive' => -1
                ));

                $accountsCards = array(
                    array(
                        'title' => __('Accounts Income'),
                        'value' => $currency . ' ' . CakeNumber::format($ardIncomeTotal),
                        'subtitle' => __('All posted income vouchers in the selected range'),
                        'icon' => 'fa-arrow-down',
                        'color' => 'success',
                        'url' => array('controller' => 'Ardaccounts', 'action' => 'dashboard', $ardProjectId, '?' => array('preset' => $ardDashboardPreset, 'start' => $filterStartDate, 'end' => $filterEndDate))
                    ),
                    array(
                        'title' => __('Accounts Expense'),
                        'value' => $currency . ' ' . CakeNumber::format($ardExpenseTotal),
                        'subtitle' => __('All posted expense vouchers in the selected range'),
                        'icon' => 'fa-arrow-up',
                        'color' => 'danger',
                        'url' => array('controller' => 'Ardaccounts', 'action' => 'dashboard', $ardProjectId, '?' => array('preset' => $ardDashboardPreset, 'start' => $filterStartDate, 'end' => $filterEndDate))
                    ),
                    array(
                        'title' => __('Accounts Net'),
                        'value' => $currency . ' ' . CakeNumber::format($ardNet),
                        'subtitle' => __('Posted income minus posted expenses'),
                        'icon' => 'fa-balance-scale',
                        'color' => ($ardNet >= 0 ? 'profit' : 'loss'),
                        'url' => array('controller' => 'Ardaccounts', 'action' => 'dashboard', $ardProjectId, '?' => array('preset' => $ardDashboardPreset, 'start' => $filterStartDate, 'end' => $filterEndDate))
                    ),
                    array(
                        'title' => __('Vouchers / Ledgers'),
                        'value' => (string)$ardVoucherCount,
                        'subtitle' => __('Posted vouchers') . ': ' . (string)$ardVoucherCount . ' | ' . __('Bank ledgers') . ': ' . (string)$bankLedgerCount . ' | ' . __('Expense ledgers') . ': ' . (string)$expenseLedgerCount,
                        'icon' => 'fa-book',
                        'color' => 'indigo',
                        'url' => array('controller' => 'Ardaccounts', 'action' => 'ledgers', $ardProjectId)
                    )
                );

                $constructionProjectCount = (int)$this->Ardconstructionproject->find('count', array(
                    'conditions' => $ardConstructionProjectConditions,
                    'recursive' => -1
                ));
                $purchaseOrderCount = (int)$this->Ardpurchaseorder->find('count', array(
                    'conditions' => $ardPurchaseOrderConditions,
                    'recursive' => -1
                ));
                $grnCount = (int)$this->Ardgrn->find('count', array(
                    'conditions' => $ardGrnConditions,
                    'recursive' => -1
                ));
                $stockTotals = $this->Ardstock->find('first', array(
                    'fields' => array(
                        'SUM(CASE WHEN Ardstock.movement_type="in" THEN Ardstock.total_cost ELSE 0 END) AS stock_in_total',
                        'SUM(CASE WHEN Ardstock.movement_type="out" THEN Ardstock.total_cost ELSE 0 END) AS stock_out_total'
                    ),
                    'conditions' => $ardStockConditions,
                    'recursive' => -1
                ));
                $stockInTotal = $this->_toFloat(isset($stockTotals[0]['stock_in_total']) ? $stockTotals[0]['stock_in_total'] : 0);
                $stockOutTotal = $this->_toFloat(isset($stockTotals[0]['stock_out_total']) ? $stockTotals[0]['stock_out_total'] : 0);
                $stockNet = round($stockInTotal - $stockOutTotal, 2);

                $constructionCards = array(
                    array(
                        'title' => __('Construction Projects'),
                        'value' => (string)$constructionProjectCount,
                        'subtitle' => __('Construction projects started in the selected range'),
                        'icon' => 'fa-sitemap',
                        'color' => 'primary',
                        'url' => array('controller' => 'Ardaccounts', 'action' => 'construction', $ardProjectId)
                    ),
                    array(
                        'title' => __('Purchase Orders'),
                        'value' => (string)$purchaseOrderCount,
                        'subtitle' => __('Purchase orders created in the selected range'),
                        'icon' => 'fa-list-alt',
                        'color' => 'warning',
                        'url' => array('controller' => 'Ardaccounts', 'action' => 'purchaseorders', $ardProjectId)
                    ),
                    array(
                        'title' => __('GRN Entries'),
                        'value' => (string)$grnCount,
                        'subtitle' => __('Goods receipt notes recorded in the selected range'),
                        'icon' => 'fa-truck',
                        'color' => 'teal',
                        'url' => array('controller' => 'Ardaccounts', 'action' => 'grn', $ardProjectId)
                    ),
                    array(
                        'title' => __('Stock Net Value'),
                        'value' => $currency . ' ' . CakeNumber::format($stockNet),
                        'subtitle' => __('Stock received minus stock issued in the selected range'),
                        'icon' => 'fa-cubes',
                        'color' => 'info',
                        'url' => array('controller' => 'Ardaccounts', 'action' => 'stock', $ardProjectId)
                    )
                );

                $latestDealPayments = $this->DealsPayment->find('all', array(
                    'joins' => array(
                        array('table' => 'deals', 'alias' => 'Deal', 'type' => 'LEFT', 'conditions' => array('Deal.id=DealsPayment.deal_id')),
                        array('table' => 'properties', 'alias' => 'Property', 'type' => 'LEFT', 'conditions' => array('Property.id=Deal.property_id')),
                        array('table' => 'paymenttypes', 'alias' => 'Paymenttype', 'type' => 'LEFT', 'conditions' => array('Paymenttype.id=DealsPayment.paymenttype_id'))
                    ),
                    'conditions' => $this->_buildAndConditions($conditionPro, $dealPaymentDateCond),
                    'fields' => array(
                        'DealsPayment.id',
                        'DealsPayment.payment_date',
                        'DealsPayment.total_amount',
                        'DealsPayment.receipt_no',
                        'DealsPayment.remarks',
                        'Property.project_id',
                        'Deal.invoice_no',
                        'Paymenttype.name'
                    ),
                    'order' => array('DealsPayment.payment_date' => 'DESC', 'DealsPayment.id' => 'DESC'),
                    'limit' => 15,
                    'recursive' => -1
                ));

                $latestArdTransactions = $this->Ardtransaction->find('all', array(
                    'joins' => array(
                        array('table' => 'ardaccounts_ledgers', 'alias' => 'Ardledger', 'type' => 'LEFT', 'conditions' => array('Ardledger.id=Ardtransaction.ledger_id'))
                    ),
                    'conditions' => $ardTxConditions,
                    'fields' => array(
                        'Ardtransaction.id',
                        'Ardtransaction.project_id',
                        'Ardtransaction.entry_date',
                        'Ardtransaction.amount',
                        'Ardtransaction.type',
                        'Ardtransaction.ref_no',
                        'Ardtransaction.description',
                        'Ardledger.name'
                    ),
                    'order' => array('Ardtransaction.entry_date' => 'DESC', 'Ardtransaction.id' => 'DESC'),
                    'limit' => 15,
                    'recursive' => -1
                ));

                $combinedTransactions = array();
                foreach($latestDealPayments as $dealPaymentRow)
                {
                    $paymentDate = isset($dealPaymentRow['DealsPayment']['payment_date']) ? trim((string)$dealPaymentRow['DealsPayment']['payment_date']) : '';
                    if($paymentDate === '' || $paymentDate === '0000-00-00')
                    {
                        continue;
                    }
                    $paymentId = (int)$dealPaymentRow['DealsPayment']['id'];
                    $projectIdForRow = isset($dealPaymentRow['Property']['project_id']) ? (int)$dealPaymentRow['Property']['project_id'] : 0;
                    $referenceNo = trim((string)$dealPaymentRow['DealsPayment']['receipt_no']);
                    if($referenceNo === '')
                    {
                        $referenceNo = trim((string)$dealPaymentRow['Deal']['invoice_no']);
                    }
                    if($referenceNo === '')
                    {
                        $referenceNo = 'RCPT-' . $paymentId;
                    }
                    $agencyNameRow = (isset($agencyList[$projectIdForRow]) && $agencyList[$projectIdForRow] !== '') ? (string)$agencyList[$projectIdForRow] : '-';
                    $combinedTransactions[] = array(
                        'source' => 'installment',
                        'source_label' => __('Installment'),
                        'date_raw' => $paymentDate,
                        'date_text' => date('d-M-Y', strtotime($paymentDate)),
                        'reference' => $referenceNo,
                        'description' => !empty($dealPaymentRow['Paymenttype']['name']) ? (string)$dealPaymentRow['Paymenttype']['name'] : __('Deal Receipt'),
                        'agency_name' => $agencyNameRow,
                        'amount' => isset($dealPaymentRow['DealsPayment']['total_amount']) ? (float)$dealPaymentRow['DealsPayment']['total_amount'] : 0,
                        'print_route' => array('controller' => 'deals_payments', 'action' => 'printreceipt', $paymentId),
                        'print_label' => __('Print Receipt'),
                        'sort_time' => (int)strtotime($paymentDate . ' 12:00:00'),
                        'sort_id' => $paymentId
                    );
                }
                foreach($latestArdTransactions as $ardTxRow)
                {
                    $entryDate = isset($ardTxRow['Ardtransaction']['entry_date']) ? trim((string)$ardTxRow['Ardtransaction']['entry_date']) : '';
                    if($entryDate === '' || $entryDate === '0000-00-00')
                    {
                        continue;
                    }
                    $txId = (int)$ardTxRow['Ardtransaction']['id'];
                    $projectIdForRow = isset($ardTxRow['Ardtransaction']['project_id']) ? (int)$ardTxRow['Ardtransaction']['project_id'] : 0;
                    $referenceNo = trim((string)$ardTxRow['Ardtransaction']['ref_no']);
                    if($referenceNo === '')
                    {
                        $referenceNo = 'VCH-' . $txId;
                    }
                    $txTypeRaw = strtolower(trim((string)$ardTxRow['Ardtransaction']['type']));
                    $txTypeLabel = ucwords(str_replace('_', ' ', $txTypeRaw));
                    $agencyNameRow = (isset($agencyList[$projectIdForRow]) && $agencyList[$projectIdForRow] !== '') ? (string)$agencyList[$projectIdForRow] : '-';
                    $printRoute = array('controller' => 'ardaccounts', 'action' => 'printvoucher', $txId);
                    if($projectIdForRow > 0)
                    {
                        $printRoute[] = $projectIdForRow;
                    }
                    $combinedTransactions[] = array(
                        'source' => 'voucher',
                        'source_label' => __('Voucher'),
                        'date_raw' => $entryDate,
                        'date_text' => date('d-M-Y', strtotime($entryDate)),
                        'reference' => $referenceNo,
                        'description' => !empty($ardTxRow['Ardledger']['name']) ? (string)$ardTxRow['Ardledger']['name'] . ' (' . $txTypeLabel . ')' : $txTypeLabel,
                        'agency_name' => $agencyNameRow,
                        'amount' => isset($ardTxRow['Ardtransaction']['amount']) ? (float)$ardTxRow['Ardtransaction']['amount'] : 0,
                        'print_route' => $printRoute,
                        'print_label' => __('Print Voucher'),
                        'sort_time' => (int)strtotime($entryDate . ' 12:00:00'),
                        'sort_id' => $txId
                    );
                }
                usort($combinedTransactions, function($a, $b) {
                    if((int)$a['sort_time'] === (int)$b['sort_time'])
                    {
                        return ((int)$b['sort_id'] - (int)$a['sort_id']);
                    }
                    return ((int)$b['sort_time'] - (int)$a['sort_time']);
                });
                $recentPaymentTransactions = array_slice($combinedTransactions, 0, 5);
            }
            catch(Exception $ardEx)
            {
                $accountsCards = array();
                $constructionCards = array();
            }

            // Build the latest transactions inside the selected agency and date range.
            $recentPaymentTransactions = array();
            $recentCombinedTransactions = array();
            $recentLinkedTransactionIds = array();
            $recentProjectId = (int)$selectedProjectId;
            if(!$isGlobalAdmin && !$isAssignedAgencyAggregate && $recentProjectId <= 0)
            {
                $recentProjectId = (int)$this->agencyId;
            }
            try
            {
                $recentDealConditions = array();
                if($recentProjectId > 0)
                {
                    $recentDealConditions['Property.project_id'] = $recentProjectId;
                }
                elseif($isAssignedAgencyAggregate)
                {
                    $recentDealConditions['Property.project_id'] = $assignedDashboardProjectIds;
                }
                $recentDealConditions = $this->_buildAndConditions($recentDealConditions, $dealPaymentDateCond);
                $latestDealPayments = $this->DealsPayment->find('all', array(
                    'joins' => array(
                        array('table' => 'deals', 'alias' => 'Deal', 'type' => 'LEFT', 'conditions' => array('Deal.id=DealsPayment.deal_id')),
                        array('table' => 'properties', 'alias' => 'Property', 'type' => 'LEFT', 'conditions' => array('Property.id=Deal.property_id')),
                        array('table' => 'paymenttypes', 'alias' => 'Paymenttype', 'type' => 'LEFT', 'conditions' => array('Paymenttype.id=DealsPayment.paymenttype_id'))
                    ),
                    'conditions' => $recentDealConditions,
                    'fields' => array(
                        'DealsPayment.id',
                        'DealsPayment.payment_date',
                        'DealsPayment.total_amount',
                        'DealsPayment.receipt_no',
                        'DealsPayment.ard_transaction_id',
                        'DealsPayment.remarks',
                        'Property.project_id',
                        'Deal.invoice_no',
                        'Paymenttype.name'
                    ),
                    'order' => array('DealsPayment.payment_date' => 'DESC', 'DealsPayment.id' => 'DESC'),
                    'limit' => 30,
                    'recursive' => -1
                ));
                foreach($latestDealPayments as $dealPaymentRow)
                {
                    $paymentDate = isset($dealPaymentRow['DealsPayment']['payment_date']) ? trim((string)$dealPaymentRow['DealsPayment']['payment_date']) : '';
                    if($paymentDate === '' || $paymentDate === '0000-00-00')
                    {
                        continue;
                    }
                    $paymentId = (int)$dealPaymentRow['DealsPayment']['id'];
                    $linkedTransactionId = isset($dealPaymentRow['DealsPayment']['ard_transaction_id']) ? (int)$dealPaymentRow['DealsPayment']['ard_transaction_id'] : 0;
                    if($linkedTransactionId > 0)
                    {
                        $recentLinkedTransactionIds[$linkedTransactionId] = true;
                    }
                    $projectIdForRow = isset($dealPaymentRow['Property']['project_id']) ? (int)$dealPaymentRow['Property']['project_id'] : 0;
                    $referenceNo = trim((string)$dealPaymentRow['DealsPayment']['receipt_no']);
                    if($referenceNo === '')
                    {
                        $referenceNo = trim((string)$dealPaymentRow['Deal']['invoice_no']);
                    }
                    if($referenceNo === '')
                    {
                        $referenceNo = 'RCPT-' . $paymentId;
                    }
                    if($projectIdForRow <= 0)
                    {
                        $projectIdForRow = $recentProjectId;
                    }
                    $agencyNameRow = (isset($agencyList[$projectIdForRow]) && trim((string)$agencyList[$projectIdForRow]) !== '') ? (string)$agencyList[$projectIdForRow] : '-';
                    $recentCombinedTransactions[] = array(
                        'source' => 'installment',
                        'source_label' => __('Installment'),
                        'date_raw' => $paymentDate,
                        'date_text' => date('d-M-Y', strtotime($paymentDate)),
                        'reference' => $referenceNo,
                        'description' => !empty($dealPaymentRow['Paymenttype']['name']) ? (string)$dealPaymentRow['Paymenttype']['name'] : __('Deal Receipt'),
                        'agency_name' => $agencyNameRow,
                        'amount' => isset($dealPaymentRow['DealsPayment']['total_amount']) ? (float)$dealPaymentRow['DealsPayment']['total_amount'] : 0,
                        'print_route' => array('controller' => 'deals_payments', 'action' => 'printreceipt', $paymentId),
                        'print_label' => __('Print Receipt'),
                        'linked_transaction_id' => $linkedTransactionId,
                        'sort_time' => (int)strtotime($paymentDate . ' 12:00:00'),
                        'sort_id' => $paymentId
                    );
                }
            }
            catch(Exception $recentDealEx)
            {
                // Keep running; vouchers can still render.
            }
            try
            {
                $recentArdConditions = array();
                if($recentProjectId > 0)
                {
                    $recentArdConditions['Ardtransaction.project_id'] = $recentProjectId;
                }
                elseif($isAssignedAgencyAggregate)
                {
                    $recentArdConditions['Ardtransaction.project_id'] = $assignedDashboardProjectIds;
                }
                if($filterStartDate !== '' && $filterEndDate !== '')
                {
                    $recentArdConditions['Ardtransaction.entry_date >='] = $filterStartDate;
                    $recentArdConditions['Ardtransaction.entry_date <='] = $filterEndDate;
                }
                $latestArdTransactions = $this->Ardtransaction->find('all', array(
                    'joins' => array(
                        array('table' => 'ardaccounts_ledgers', 'alias' => 'Ardledger', 'type' => 'LEFT', 'conditions' => array('Ardledger.id=Ardtransaction.ledger_id')),
                        array('table' => 'ardaccounts_journal_entries', 'alias' => 'Ardjournalentry', 'type' => 'LEFT', 'conditions' => array('Ardjournalentry.transaction_id=Ardtransaction.id'))
                    ),
                    'conditions' => $recentArdConditions,
                    'fields' => array(
                        'Ardtransaction.id',
                        'Ardtransaction.project_id',
                        'Ardtransaction.entry_date',
                        'Ardtransaction.amount',
                        'Ardtransaction.type',
                        'Ardtransaction.ref_no',
                        'Ardtransaction.description',
                        'Ardledger.name',
                        'Ardjournalentry.source_type',
                        'Ardjournalentry.source_id'
                    ),
                    'order' => array('Ardtransaction.entry_date' => 'DESC', 'Ardtransaction.id' => 'DESC'),
                    'limit' => 30,
                    'recursive' => -1
                ));
                foreach($latestArdTransactions as $ardTxRow)
                {
                    $entryDate = isset($ardTxRow['Ardtransaction']['entry_date']) ? trim((string)$ardTxRow['Ardtransaction']['entry_date']) : '';
                    if($entryDate === '' || $entryDate === '0000-00-00')
                    {
                        continue;
                    }
                    $txId = (int)$ardTxRow['Ardtransaction']['id'];
                    $journalSourceType = strtolower(trim((string)(isset($ardTxRow['Ardjournalentry']['source_type']) ? $ardTxRow['Ardjournalentry']['source_type'] : '')));
                    $journalSourceId = isset($ardTxRow['Ardjournalentry']['source_id']) ? (int)$ardTxRow['Ardjournalentry']['source_id'] : 0;
                    if($journalSourceType === 'deal_receipt' && !empty($recentLinkedTransactionIds[$txId]))
                    {
                        continue;
                    }
                    $projectIdForRow = isset($ardTxRow['Ardtransaction']['project_id']) ? (int)$ardTxRow['Ardtransaction']['project_id'] : 0;
                    $referenceNo = trim((string)$ardTxRow['Ardtransaction']['ref_no']);
                    if($referenceNo === '')
                    {
                        $referenceNo = 'VCH-' . $txId;
                    }
                    if($projectIdForRow <= 0)
                    {
                        $projectIdForRow = $recentProjectId;
                    }
                    $txTypeRaw = strtolower(trim((string)$ardTxRow['Ardtransaction']['type']));
                    $txTypeLabel = ucwords(str_replace('_', ' ', $txTypeRaw));
                    $agencyNameRow = (isset($agencyList[$projectIdForRow]) && trim((string)$agencyList[$projectIdForRow]) !== '') ? (string)$agencyList[$projectIdForRow] : '-';
                    $sourceKey = 'voucher';
                    $sourceLabel = __('Voucher');
                    $printRoute = array('controller' => 'ardaccounts', 'action' => 'printvoucher', $txId);
                    $printLabel = __('Print Voucher');
                    if($journalSourceType === 'deal_receipt' && $journalSourceId > 0)
                    {
                        $sourceKey = 'installment';
                        $sourceLabel = __('Installment');
                        $printRoute = array('controller' => 'deals_payments', 'action' => 'printreceipt', $journalSourceId);
                        $printLabel = __('Print Receipt');
                    }
                    elseif($projectIdForRow > 0)
                    {
                        $printRoute[] = $projectIdForRow;
                    }
                    $recentCombinedTransactions[] = array(
                        'source' => $sourceKey,
                        'source_label' => $sourceLabel,
                        'date_raw' => $entryDate,
                        'date_text' => date('d-M-Y', strtotime($entryDate)),
                        'reference' => $referenceNo,
                        'description' => !empty($ardTxRow['Ardledger']['name']) ? (string)$ardTxRow['Ardledger']['name'] . ' (' . $txTypeLabel . ')' : $txTypeLabel,
                        'agency_name' => $agencyNameRow,
                        'amount' => isset($ardTxRow['Ardtransaction']['amount']) ? (float)$ardTxRow['Ardtransaction']['amount'] : 0,
                        'print_route' => $printRoute,
                        'print_label' => $printLabel,
                        'sort_time' => (int)strtotime($entryDate . ' 12:00:00'),
                        'sort_id' => $txId
                    );
                }
            }
            catch(Exception $recentArdEx)
            {
                // Keep running; receipts can still render.
            }
            if(!empty($recentCombinedTransactions))
            {
                usort($recentCombinedTransactions, function($a, $b) {
                    if((int)$a['sort_time'] === (int)$b['sort_time'])
                    {
                        return ((int)$b['sort_id'] - (int)$a['sort_id']);
                    }
                    return ((int)$b['sort_time'] - (int)$a['sort_time']);
                });
                $recentPaymentTransactions = array_slice($recentCombinedTransactions, 0, 5);
            }

            $graphMonth = array();
            $earningChartData = array();
            $expenseChartData = array();
            $purchaseChartData = array();
            $profitChartData = array();
            $monthlySalesRows = $this->DealsPayment->find('all', array(
                'fields' => array('DATE_FORMAT(DealsPayment.payment_date, "%Y-%m") AS period_key', 'SUM(DealsPayment.total_amount) AS total_value'),
                'joins' => array(
                    array('table' => 'deals', 'alias' => 'Deal', 'type' => 'LEFT', 'conditions' => array('Deal.id=DealsPayment.deal_id')),
                    array('table' => 'properties', 'alias' => 'Property', 'type' => 'LEFT', 'conditions' => array('Property.id=Deal.property_id'))
                ),
                'conditions' => $this->_buildAndConditions($conditionPro, $dealPaymentDateCond),
                'group' => array('DATE_FORMAT(DealsPayment.payment_date, "%Y-%m")'),
                'order' => array('period_key' => 'asc'),
                'recursive' => -1
            ));
            $monthlyExpenseRows = $this->ExpensesPayment->find('all', array(
                'fields' => array('DATE_FORMAT(ExpensesPayment.date, "%Y-%m") AS period_key', 'SUM(ExpensesPayment.amount) AS total_value'),
                'joins' => array(
                    array('table' => 'expenses', 'alias' => 'Expense', 'type' => 'LEFT', 'conditions' => array('Expense.id=ExpensesPayment.expense_id'))
                ),
                'conditions' => $this->_buildAndConditions($conditionExp, $expenseDateCond),
                'group' => array('DATE_FORMAT(ExpensesPayment.date, "%Y-%m")'),
                'order' => array('period_key' => 'asc'),
                'recursive' => -1
            ));
            $monthlyPurchaseRows = $this->PurchasesPayment->find('all', array(
                'fields' => array('DATE_FORMAT(PurchasesPayment.date, "%Y-%m") AS period_key', 'SUM(PurchasesPayment.amount) AS total_value'),
                'joins' => array(
                    array('table' => 'purchases', 'alias' => 'Purchase', 'type' => 'LEFT', 'conditions' => array('Purchase.id=PurchasesPayment.purchase_id'))
                ),
                'conditions' => $this->_buildAndConditions($conditionPur, $purchaseDateCond),
                'group' => array('DATE_FORMAT(PurchasesPayment.date, "%Y-%m")'),
                'order' => array('period_key' => 'asc'),
                'recursive' => -1
            ));
            $monthlyMaps = array('sales' => array(), 'expenses' => array(), 'purchase' => array());
            foreach(array('sales' => $monthlySalesRows, 'expenses' => $monthlyExpenseRows, 'purchase' => $monthlyPurchaseRows) as $mapName => $monthlyRows)
            {
                foreach((array)$monthlyRows as $monthlyRow)
                {
                    $periodKey = !empty($monthlyRow[0]['period_key']) ? trim((string)$monthlyRow[0]['period_key']) : '';
                    if(preg_match('/^\d{4}-\d{2}$/', $periodKey))
                    {
                        $monthlyMaps[$mapName][$periodKey] = $this->_toFloat(isset($monthlyRow[0]['total_value']) ? $monthlyRow[0]['total_value'] : 0);
                    }
                }
            }
            if($filterStartDate !== '' && $filterEndDate !== '')
            {
                $chartStartKey = date('Y-m', strtotime($filterStartDate));
                $chartEndKey = date('Y-m', strtotime($filterEndDate));
            }
            else
            {
                $availableMonthKeys = array_unique(array_merge(array_keys($monthlyMaps['sales']), array_keys($monthlyMaps['expenses']), array_keys($monthlyMaps['purchase'])));
                sort($availableMonthKeys);
                $chartStartKey = !empty($availableMonthKeys) ? reset($availableMonthKeys) : date('Y-m', strtotime($today));
                $chartEndKey = !empty($availableMonthKeys) ? end($availableMonthKeys) : date('Y-m', strtotime($today));
            }
            $chartCursor = strtotime($chartStartKey . '-01');
            $chartEndCursor = strtotime($chartEndKey . '-01');
            while($chartCursor !== false && $chartCursor <= $chartEndCursor)
            {
                $periodKey = date('Y-m', $chartCursor);
                $monthSale = isset($monthlyMaps['sales'][$periodKey]) ? (float)$monthlyMaps['sales'][$periodKey] : 0;
                $monthExpense = isset($monthlyMaps['expenses'][$periodKey]) ? (float)$monthlyMaps['expenses'][$periodKey] : 0;
                $monthPurchase = isset($monthlyMaps['purchase'][$periodKey]) ? (float)$monthlyMaps['purchase'][$periodKey] : 0;
                $graphMonth[] = date('M Y', $chartCursor);
                $earningChartData[] = round($monthSale, 2);
                $expenseChartData[] = round($monthExpense, 2);
                $purchaseChartData[] = round($monthPurchase, 2);
                $profitChartData[] = round($monthSale - $monthExpense - $monthPurchase, 2);
                $nextCursor = strtotime('+1 month', $chartCursor);
                if($nextCursor === false || $nextCursor <= $chartCursor)
                {
                    break;
                }
                $chartCursor = $nextCursor;
            }

            $tooltipFormatFunction ="function() { return '<b>'+ this.series.name +'</b><br/>'+ this.x +': '+''+ this.y ;}";
            $chartName = "My Chartsr";
            $mychart = $this->HighCharts->create($chartName, 'line');
            $this->HighCharts->setChartParams($chartName, array(
                'renderTo' => "mywrappersr",
                'titleAlign' => 'center',
                'creditsEnabled' => FALSE,
                'xAxisLabelsEnabled' => TRUE,
                'xAxisCategories' => $graphMonth,
                'yAxisTitleText' => '',
                'tooltipEnabled' => TRUE,
                'tooltipFormatter' => $tooltipFormatFunction,
                'enableAutoStep' => FALSE,
                'plotOptionsShowInLegend' => TRUE,
            ));
            $series = $this->HighCharts->addChartSeries();
            $series->addName(__('Sales Report'))->addData($earningChartData);
            $mychart->addSeries($series);
            $series = $this->HighCharts->addChartSeries();
            $series->addName(__('Purchase Report'))->addData($purchaseChartData);
            $mychart->addSeries($series);
            $series = $this->HighCharts->addChartSeries();
            $series->addName(__('Expense Report'))->addData($expenseChartData);
            $mychart->addSeries($series);
            $series = $this->HighCharts->addChartSeries();
            $series->addName(__('Profit & Loss Report'))->addData($profitChartData);
            $mychart->addSeries($series);

            $calendarStartDate = ($filterStartDate !== '' && $filterEndDate !== '') ? $filterStartDate : date('Y-m-d', strtotime($currentDateTime . '-1 month'));
            $calendarEndDate = ($filterStartDate !== '' && $filterEndDate !== '') ? $filterEndDate : date('Y-m-d', strtotime($currentDateTime . '+3 month'));
            $calendarStartDateTime = $calendarStartDate . ' 00:00:00';
            $calendarEndDateTime = $calendarEndDate . ' 23:59:59';
            $calendarCondition = $this->CustomFunction->userWisePermission($permissionProjectId, $this->luserId, $this->userType, $this->agencyId, 'User.project_id', 'Calendar.user_id');
            $leadCondition = $this->CustomFunction->userWisePermission($permissionProjectId, $this->luserId, $this->userType, $this->agencyId, 'Lead.project_id', 'Lead.user_id');
            if(!$isGlobalAdmin && ($isAgentDashboardUser || $this->userType === 'AGT') && (int)$this->luserId > 0)
            {
                $calendarCondition = array('Calendar.user_id' => (int)$this->luserId);
                $leadCondition = array('Lead.user_id' => (int)$this->luserId);
            }
            $schedule = $this->Calendar->find('all', array(
                'fields' => array('User.name', 'Calendar.*'),
                'joins' => array(array('table' => 'users', 'alias' => 'User', 'type' => 'LEFT', 'conditions' => array('Calendar.user_id=User.id'))),
                'conditions' => $this->_buildAndConditions(
                    array('Calendar.start_date >=' => $calendarStartDateTime, 'Calendar.start_date <=' => $calendarEndDateTime),
                    $calendarCondition
                ),
                'recursive' => -1
            ));
            $leads = $this->Lead->find('all', array(
                'fields' => array('Client.name', 'Lead.*', 'Status.*'),
                'joins' => array(
                    array('table' => 'clients', 'alias' => 'Client', 'type' => 'LEFT', 'conditions' => array('Lead.client_id=Client.id')),
                    array('table' => 'statuses', 'alias' => 'Status', 'type' => 'LEFT', 'conditions' => array('Lead.status_id=Status.id'))
                ),
                'conditions' => $this->_buildAndConditions(
                    array('Lead.follow_up >=' => $calendarStartDateTime, 'Lead.follow_up <=' => $calendarEndDateTime),
                    $leadCondition
                ),
                'recursive' => -1
            ));

            $canAccessLeads = ($this->_dashboardCanAccessController('leads') || $isAgentDashboardUser || $this->_dashboardCanAccessRoute(array('controller' => 'Leads', 'action' => 'dashboard')));
            $leadCards = array();
            if ($canAccessLeads) {
                $this->loadModel('Status');
                $todayStart = date('Y-m-d 00:00:00');
                $todayEnd = date('Y-m-d 23:59:59');
                $next7DaysEnd = date('Y-m-d 23:59:59', strtotime('+7 days'));

                $leadTotalCount = (int)$this->Lead->find('count', array(
                    'conditions' => $leadCondition,
                    'recursive' => -1
                ));

                $leadTodayCount = (int)$this->Lead->find('count', array(
                    'conditions' => $this->_buildAndConditions($leadCondition, array(
                        'Lead.follow_up IS NOT NULL',
                        'Lead.follow_up !=' => '0000-00-00 00:00:00',
                        'Lead.follow_up >=' => $todayStart,
                        'Lead.follow_up <=' => $todayEnd
                    )),
                    'recursive' => -1
                ));

                $leadOverdueCount = (int)$this->Lead->find('count', array(
                    'conditions' => $this->_buildAndConditions($leadCondition, array(
                        'Lead.follow_up IS NOT NULL',
                        'Lead.follow_up !=' => '0000-00-00 00:00:00',
                        'Lead.follow_up <' => $todayStart
                    )),
                    'recursive' => -1
                ));

                $leadUpcomingCount = (int)$this->Lead->find('count', array(
                    'conditions' => $this->_buildAndConditions($leadCondition, array(
                        'Lead.follow_up IS NOT NULL',
                        'Lead.follow_up !=' => '0000-00-00 00:00:00',
                        'Lead.follow_up >' => $todayEnd,
                        'Lead.follow_up <=' => $next7DaysEnd
                    )),
                    'recursive' => -1
                ));

                $statusRows = (array)$this->Status->find('all', array(
                    'fields' => array('Status.id', 'Status.name'),
                    'recursive' => -1
                ));
                $newStatusIds = array();
                $activeStatusIds = array();
                foreach ($statusRows as $stRow) {
                    $stId = (int)$stRow['Status']['id'];
                    $stName = strtolower(trim((string)$stRow['Status']['name']));
                    if (strpos($stName, 'not') === false && preg_match('/in process|visit|qualified|interested|already-invest/', $stName)) {
                        $activeStatusIds[] = $stId;
                    }
                    if (preg_match('/new|brochure/', $stName)) {
                        $newStatusIds[] = $stId;
                    }
                }

                $leadNewCount = !empty($newStatusIds) ? (int)$this->Lead->find('count', array(
                    'conditions' => $this->_buildAndConditions($leadCondition, array('Lead.status_id' => $newStatusIds)),
                    'recursive' => -1
                )) : 0;

                $leadActiveCount = !empty($activeStatusIds) ? (int)$this->Lead->find('count', array(
                    'conditions' => $this->_buildAndConditions($leadCondition, array('Lead.status_id' => $activeStatusIds)),
                    'recursive' => -1
                )) : 0;

                $newStatusParam = 1;
                foreach ($statusRows as $stRow) {
                    if (strtolower(trim((string)$stRow['Status']['name'])) === 'new') {
                        $newStatusParam = (int)$stRow['Status']['id'];
                        break;
                    }
                }

                $leadCards = array(
                    array(
                        'group' => 'leads',
                        'title' => __('Total Leads'),
                        'value' => (string)$leadTotalCount,
                        'subtitle' => __('All assigned leads in CRM'),
                        'icon' => 'fa-users',
                        'color' => 'primary',
                        'url' => array('controller' => 'Leads', 'action' => 'dashboard', '?' => array('scope' => 'all')),
                        'visibility' => 'all'
                    ),
                    array(
                        'group' => 'leads',
                        'title' => __('Follow-up Today'),
                        'value' => (string)$leadTodayCount,
                        'subtitle' => __('Scheduled for follow-up today'),
                        'icon' => 'fa-calendar-check-o',
                        'color' => 'warning',
                        'url' => array('controller' => 'Leads', 'action' => 'dashboard', '?' => array('scope' => 'today', 'followup_period' => 'today')),
                        'visibility' => 'all'
                    ),
                    array(
                        'group' => 'leads',
                        'title' => __('Overdue Follow-ups'),
                        'value' => (string)$leadOverdueCount,
                        'subtitle' => __('Follow-ups missed or past due'),
                        'icon' => 'fa-exclamation-triangle',
                        'color' => 'danger',
                        'url' => array('controller' => 'Leads', 'action' => 'dashboard', '?' => array('scope' => 'overdue', 'followup_period' => 'overdue')),
                        'visibility' => 'all'
                    ),
                    array(
                        'group' => 'leads',
                        'title' => __('Upcoming Follow-ups'),
                        'value' => (string)$leadUpcomingCount,
                        'subtitle' => __('Follow-ups in next 7 days'),
                        'icon' => 'fa-calendar',
                        'color' => 'indigo',
                        'url' => array('controller' => 'Leads', 'action' => 'dashboard', '?' => array('followup_period' => 'week')),
                        'visibility' => 'all'
                    ),
                    array(
                        'group' => 'leads',
                        'title' => __('New Inquiries'),
                        'value' => (string)$leadNewCount,
                        'subtitle' => __('Fresh leads awaiting contact'),
                        'icon' => 'fa-user-plus',
                        'color' => 'teal',
                        'url' => array('controller' => 'Leads', 'action' => 'dashboard', '?' => array('status_id' => $newStatusParam)),
                        'visibility' => 'all'
                    ),
                    array(
                        'group' => 'leads',
                        'title' => __('Active Prospects'),
                        'value' => (string)$leadActiveCount,
                        'subtitle' => __('In process & qualified prospects'),
                        'icon' => 'fa-fire',
                        'color' => 'profit',
                        'url' => array('controller' => 'Leads', 'action' => 'dashboard'),
                        'visibility' => 'all'
                    )
                );
            }

            $dashboardCards = array_merge($leadCards, array(
                array('group' => 'sales', 'title' => __('Total Properties'), 'value' => (string)$propertyCount, 'subtitle' => __('Properties added in the selected range'), 'icon' => 'fa-building-o', 'color' => 'primary', 'url' => array('controller' => 'Properties', 'action' => 'index', 'agency' => $selectedProjectId), 'visibility' => 'all'),
                array('group' => 'sales', 'title' => __('Total Deals'), 'value' => (string)$dealCount, 'subtitle' => __('Deals booked in the selected range'), 'icon' => 'fa-thumbs-up', 'color' => 'danger', 'url' => array('controller' => 'Deals', 'action' => 'index', 'project_id' => $selectedProjectId, 'start_date' => $filterStartDate, 'end_date' => $filterEndDate), 'visibility' => 'all'),
                array('group' => 'sales', 'title' => __('Total Sales'), 'value' => $currency . ' ' . CakeNumber::format($eligibleDealTotal), 'subtitle' => __('Paid') . ': ' . $currency . ' ' . CakeNumber::format($eligibleDealPaid) . ' | ' . __('Balance') . ': ' . $currency . ' ' . CakeNumber::format($eligibleDealBalance), 'icon' => 'fa-money', 'color' => 'success', 'url' => array('controller' => 'Deals', 'action' => 'index', 'limit' => 500, 'sales_scope' => 'eligible', 'project_id' => $selectedProjectId, 'start_date' => $filterStartDate, 'end_date' => $filterEndDate), 'visibility' => 'finance'),
                array('group' => 'costs', 'title' => __('Legacy Expenses'), 'value' => $currency . ' ' . CakeNumber::format($totalExpenses), 'subtitle' => __('Legacy expense payments in the selected range'), 'icon' => 'fa-credit-card', 'color' => 'info', 'url' => array('controller' => 'Expensereports', 'action' => 'index', '?' => array('project_id' => $selectedProjectId, 'start_date' => $filterStartDate, 'end_date' => $filterEndDate)), 'visibility' => 'agency'),
                array('group' => 'costs', 'title' => __('Total Purchase'), 'value' => $currency . ' ' . CakeNumber::format($totalPurchase), 'subtitle' => __('Property purchase payments in the selected range'), 'icon' => 'fa-shopping-cart', 'color' => 'warning', 'url' => array('controller' => 'Purchases', 'action' => 'index', $selectedProjectId), 'visibility' => 'agency'),
                array('group' => 'costs', 'title' => __('Net Profit / Loss'), 'value' => $currency . ' ' . CakeNumber::format($netBalance), 'subtitle' => __('Sales minus legacy expenses and purchases'), 'icon' => 'fa-balance-scale', 'color' => ($netBalance >= 0 ? 'profit' : 'loss'), 'url' => array('controller' => 'Profitreports', 'action' => 'index', '?' => array('project_id' => $selectedProjectId, 'start_date' => $filterStartDate, 'end_date' => $filterEndDate)), 'visibility' => 'agency'),
                array('group' => 'sales', 'title' => __('Outstanding Receivable'), 'value' => $currency . ' ' . CakeNumber::format($outstandingReceivable), 'subtitle' => __('Deal value still outstanding after collections'), 'icon' => 'fa-line-chart', 'color' => 'purple', 'url' => array('controller' => 'Invpastdues', 'action' => 'index', 'project_id' => $selectedProjectId), 'visibility' => 'finance'),
                array('group' => 'sales', 'title' => __('Collection Rate'), 'value' => CakeNumber::format($collectionRate, array('places' => 2, 'before' => '', 'thousands' => ',', 'decimals' => '.')) . '%', 'subtitle' => __('Percentage of deal value collected'), 'icon' => 'fa-percent', 'color' => 'indigo', 'url' => array('controller' => 'Salesreports', 'action' => 'index', '?' => array('project_id' => $selectedProjectId, 'start_date' => $filterStartDate, 'end_date' => $filterEndDate)), 'visibility' => 'finance'),
                array('group' => 'sales', 'title' => __('Letters'), 'value' => (string)$lettersCount, 'subtitle' => __('Client and deal letters created in the selected range'), 'icon' => 'fa-envelope-o', 'color' => 'warning', 'url' => array('controller' => 'Realestateletters', 'action' => 'dashboard', '?' => array('project_id' => $selectedProjectId)), 'visibility' => 'all'),
                array('group' => 'sales', 'title' => __('Balloting'), 'value' => (string)$ballotingAllotmentsCount, 'subtitle' => __('Allotments in range; available units') . ': ' . (string)$ballotingUnitsCount, 'icon' => 'fa-random', 'color' => 'primary', 'url' => array('controller' => 'Balloting', 'action' => 'dashboard', '?' => array('project_id' => $selectedProjectId)), 'visibility' => 'agency'),
                array('group' => 'costs', 'title' => __('Total Received'), 'value' => $currency . ' ' . CakeNumber::format($dashboardDealReceivedTotal), 'subtitle' => __('Posted booking, installment and paid fee receipts'), 'icon' => 'fa-arrow-circle-down', 'color' => 'success', 'url' => array('controller' => 'Ardaccounts', 'action' => 'reports', $dashboardAccountsReportProjectId, '?' => array('tab' => 'daily', 'preset' => $dashboardAccountsReportPreset, 'start' => $filterStartDate, 'end' => $filterEndDate, 'project_id' => $dashboardAccountsReportProjectId)), 'visibility' => 'agency'),
                array('group' => 'costs', 'title' => __('Total Expense'), 'value' => $currency . ' ' . CakeNumber::format($dashboardRecordedExpenseTotal), 'subtitle' => __('Posted accounting expense vouchers in the selected range'), 'icon' => 'fa-arrow-circle-up', 'color' => 'danger', 'url' => array('controller' => 'Ardaccounts', 'action' => 'reports', $dashboardAccountsReportProjectId, '?' => array('tab' => 'daily', 'preset' => $dashboardAccountsReportPreset, 'start' => $filterStartDate, 'end' => $filterEndDate, 'project_id' => $dashboardAccountsReportProjectId)), 'visibility' => 'agency')
            ));

            $propertyTypeSales = array(
                'labels' => array(),
                'sold' => array(),
                'revenue' => array(),
                'rows' => array()
            );
            try
            {
                $typeSalesRows = $this->Deal->find('all', array(
                    'joins' => array(
                        array('table' => 'properties', 'alias' => 'Property', 'type' => 'LEFT', 'conditions' => array('Deal.property_id=Property.id')),
                        array('table' => 'types', 'alias' => 'Type', 'type' => 'LEFT', 'conditions' => array('Type.id=Property.type_id'))
                    ),
                    'conditions' => $this->_buildAndConditions($conditionPro, $dealDateCond),
                    'fields' => array(
                        'COALESCE(Type.name, "Unknown") AS type_name',
                        'COUNT(Deal.id) AS sold_count',
                        'SUM(Deal.total_amount) AS sold_revenue'
                    ),
                    'group' => array('Type.id', 'Type.name'),
                    'order' => array('COUNT(Deal.id) DESC', 'SUM(Deal.total_amount) DESC'),
                    'recursive' => -1
                ));
                foreach((array)$typeSalesRows as $typeSalesRow)
                {
                    $typeName = isset($typeSalesRow[0]['type_name']) ? trim((string)$typeSalesRow[0]['type_name']) : '';
                    if($typeName === '')
                    {
                        $typeName = __('Unknown');
                    }
                    $soldCount = isset($typeSalesRow[0]['sold_count']) ? (int)$typeSalesRow[0]['sold_count'] : 0;
                    $soldRevenue = $this->_toFloat(isset($typeSalesRow[0]['sold_revenue']) ? $typeSalesRow[0]['sold_revenue'] : 0);
                    if($soldCount <= 0 && $soldRevenue <= 0)
                    {
                        continue;
                    }
                    $soldRevenue = round($soldRevenue, 2);
                    $propertyTypeSales['labels'][] = $typeName;
                    $propertyTypeSales['sold'][] = $soldCount;
                    $propertyTypeSales['revenue'][] = $soldRevenue;
                    $propertyTypeSales['rows'][] = array(
                        'type' => $typeName,
                        'sold' => $soldCount,
                        'revenue' => $soldRevenue
                    );
                }
            }
            catch(Exception $typeSalesEx)
            {
                $propertyTypeSales = array(
                    'labels' => array(),
                    'sold' => array(),
                    'revenue' => array(),
                    'rows' => array()
                );
            }

            $dashboardCards = $this->_filterDashboardCards($dashboardCards, $allowPersonalFinanceDashboard, $allowAgencyFinanceDashboard);
            $dashboardCardSections = array(
                'leads' => array('title' => __('Leads & CRM Follow-ups'), 'cards' => array()),
                'sales' => array('title' => __('Sales & Property'), 'cards' => array()),
                'costs' => array('title' => __('Cashflow & Costs'), 'cards' => array())
            );
            foreach($dashboardCards as $dashboardCard)
            {
                $dashboardCardGroup = isset($dashboardCard['group']) ? (string)$dashboardCard['group'] : 'sales';
                if(!isset($dashboardCardSections[$dashboardCardGroup]))
                {
                    $dashboardCardGroup = 'sales';
                }
                $dashboardCardSections[$dashboardCardGroup]['cards'][] = $dashboardCard;
            }
            foreach(array_keys($dashboardCardSections) as $dashboardSectionKey)
            {
                if(empty($dashboardCardSections[$dashboardSectionKey]['cards']))
                {
                    unset($dashboardCardSections[$dashboardSectionKey]);
                }
            }
            if ($allowAgencyFinanceDashboard) {
                $accountsCards = $this->_filterDashboardLinkedCards($accountsCards);
                $constructionCards = $this->_filterDashboardLinkedCards($constructionCards);
            } else {
                $accountsCards = array();
                $constructionCards = array();
            }

            $canViewSalesReport = $this->_dashboardCanAccessController('salesreports');
            $canViewExpenseReport = $this->_dashboardCanAccessController('expenses');
            $canViewPurchaseReport = $this->_dashboardCanAccessController('purchases');
            $canViewProfitReport = $this->_dashboardCanAccessController('profitreports');
            $canViewDealsData = ($this->_dashboardCanAccessController('deals') || $canViewSalesReport || $this->_dashboardCanAccessController('invpaids') || $this->_dashboardCanAccessController('invpastdues'));
            $canViewAccountsData = $this->_dashboardCanAccessController('ardaccounts');
            if ($isAgentDashboardUser) {
                if ($canViewDealsData) {
                    $recentPaymentTransactions = $this->_dashboardRecentDealReceipts($conditionPro, $agencyList, $dealPaymentDateCond, 5);
                } else {
                    $recentPaymentTransactions = array();
                }
            } else {
                $recentPaymentTransactions = $this->_filterDashboardTransactions($recentPaymentTransactions, $canViewDealsData, ($allowAgencyFinanceDashboard && $canViewAccountsData));
            }
            $showRecentPaymentTransactions = (($canViewDealsData && ($allowAgencyFinanceDashboard || $isAgentDashboardUser)) || ($allowAgencyFinanceDashboard && $canViewAccountsData));
            if (!$showRecentPaymentTransactions) {
                $recentPaymentTransactions = array();
            }

            $showFinancialCharts = ($allowAgencyFinanceDashboard && $canViewSalesReport && $canViewExpenseReport && $canViewPurchaseReport && $canViewProfitReport);
            if (!$showFinancialCharts) {
                $graphMonth = array();
                $earningChartData = array();
                $expenseChartData = array();
                $purchaseChartData = array();
                $profitChartData = array();
                $totalExpenses = 0;
                $totalPurchase = 0;
            }

            $showPropertyTypeSales = ($this->_dashboardCanAccessController('properties') || $this->_dashboardCanAccessController('deals'));
            if (!$showPropertyTypeSales) {
                $propertyTypeSales = array(
                    'labels' => array(),
                    'sold' => array(),
                    'revenue' => array(),
                    'rows' => array()
                );
            }

            $showCalendarSection = ($this->_dashboardCanAccessController('calendars') || $this->_dashboardCanAccessController('leads'));
            if (!$showCalendarSection) {
                $schedule = array();
                $leads = array();
            }

            $this->set('leads', $leads);
            $this->set('Schedule', $schedule);
            $this->set('currentDateTime', $currentDateTime);
            $this->set('propertyCount', $propertyCount);
            $this->set('dealCount', $dealCount);
            $this->set('totalExpenses', $totalExpenses);
            $this->set('totalSale', $totalSale);
            $this->set('totalbalance', $netBalance);
            $this->set('totalPurchase', $totalPurchase);
            $this->set('totalDealValue', $totalDealValue);
            $this->set('outstandingReceivable', $outstandingReceivable);
            $this->set('totalReceipts', $totalReceipts);
            $this->set('collectionRate', $collectionRate);
            $this->set('dashboardCards', $dashboardCards);
            $this->set('dashboardCardSections', array_values($dashboardCardSections));
            $this->set('agencyList', $agencyList);
            $this->set('isGlobalDashboardAdmin', $isGlobalAdmin);
            $this->set('canSelectDashboardAgency', $canSelectDashboardAgency);
            $this->set('isCeoDashboardUser', $isCeoDashboardUser);
            $this->set('isSystemManagerDashboardUser', $isSystemManagerDashboardUser);
            $this->set('selectedProjectId', $selectedProjectId);
            $this->set('selectedPeriod', $selectedPeriod);
            $this->set('filterStartDate', $filterStartDate);
            $this->set('filterEndDate', $filterEndDate);
            $this->set('filterLabel', $filterLabel);
            $this->set('dashboardQuickActions', $dashboardQuickActions);
            $this->set('dashboardApprovalActions', $dashboardApprovalActions);
            $this->set('financePieData', ($showFinancialCharts ? array('sales' => round($totalSale, 2), 'expenses' => round($totalExpenses, 2), 'purchase' => round($totalPurchase, 2)) : array('sales' => 0, 'expenses' => 0, 'purchase' => 0)));
            $this->set('chartMonthly', ($showFinancialCharts ? array('labels' => $graphMonth, 'sales' => $earningChartData, 'expenses' => $expenseChartData, 'purchase' => $purchaseChartData, 'profit' => $profitChartData) : array('labels' => array(), 'sales' => array(), 'expenses' => array(), 'purchase' => array(), 'profit' => array())));
            $this->set('accountsCards', $accountsCards);
            $this->set('constructionCards', $constructionCards);
            $this->set('recentPaymentTransactions', $recentPaymentTransactions);
            $this->set('propertyTypeSales', $propertyTypeSales);
            $this->set('showRecentPaymentTransactions', $showRecentPaymentTransactions);
            $this->set('showFinancialCharts', $showFinancialCharts);
            $this->set('showPropertyTypeSales', $showPropertyTypeSales);
            $this->set('showCalendarSection', $showCalendarSection);
        }
        catch (Exception $e)
        {
            $this->Session->setFlash($e->getMessage(), 'flash', array('alert' => 'danger'));
        }
    }
}
