<?php
App::uses('AdminAppController', 'Admin.Controller');

class BillingManagerAppController extends AdminAppController
{
    public $layout = 'Admin.default';
    public $helpers = array('Html', 'Form', 'Session', 'Paginator', 'Js');

    public function beforeFilter()
    {
        $currAction = strtolower((string)$this->action);
        $publicActions = array('print_bill', 'print_receipt', 'get_meter_bill_preview', 'admin_get_meter_bill_preview', 'get_bulk_preflight_audit', 'admin_get_bulk_preflight_audit', 'bulk_print', 'admin_bulk_print');
        if (in_array($currAction, $publicActions, true)) {
            AppController::beforeFilter();
            return;
        }

        parent::beforeFilter();
        $this->layout = 'Admin.default';
        if (!isset($this->request->params['pass']) || !is_array($this->request->params['pass'])) {
            $this->request->params['pass'] = array();
        }
        if (!isset($this->request->params['named']) || !is_array($this->request->params['named'])) {
            $this->request->params['named'] = array();
        }
        $this->set('allowViewAction', true);
        $this->set('allowAddAction', true);
        $this->set('allowEditAction', true);
        $this->set('allowDeleteAction', true);
        $this->set('isSuperAdminSession', $this->_isSuperAdminSession());
        $this->set('isAgencyAdminSession', $this->_isAgencyAdminSession());
        $this->set('currentPlugin', 'BillingManager');
        $this->_ensureBillingPages();
    }

    public function __call($method, $args)
    {
        if (strpos($method, 'admin_') === 0) {
            $nonAdmin = substr($method, 6);
            if (method_exists($this, $nonAdmin)) {
                $result = call_user_func_array(array($this, $nonAdmin), $args);
                if ($result instanceof CakeResponse) {
                    return $result;
                }
                if ($this->hasRendered || (is_object($this->response) && $this->response->statusCode() == 302)) {
                    return $this->response;
                }
                return $this->render($nonAdmin);
            }
        } else {
            $adminMethod = 'admin_' . $method;
            if (method_exists($this, $adminMethod)) {
                $result = call_user_func_array(array($this, $adminMethod), $args);
                if ($result instanceof CakeResponse) {
                    return $result;
                }
                if ($this->hasRendered || (is_object($this->response) && $this->response->statusCode() == 302)) {
                    return $this->response;
                }
                return $this->render($adminMethod);
            }
        }
        if (method_exists('AdminAppController', '__call')) {
            return parent::__call($method, $args);
        }
        trigger_error("Call to undefined method " . get_class($this) . "::{$method}()", E_USER_ERROR);
    }

    public function render($view = null, $layout = null)
    {
        if ($view === null) {
            $view = !empty($this->view) ? $this->view : $this->action;
        }

        if (is_string($view) && strpos($view, 'admin_') === 0) {
            $nonAdminView = substr($view, 6);
            $pluginViewDir = APP . 'Plugin' . DS . 'BillingManager' . DS . 'View' . DS . $this->name . DS;
            if (!file_exists($pluginViewDir . $view . '.ctp') && file_exists($pluginViewDir . $nonAdminView . '.ctp')) {
                $view = $nonAdminView;
            }
        }

        if (is_string($view) && strpos($view, '.') === false && strpos($view, '/') === false) {
            $view = 'BillingManager.' . $this->name . '/' . $view;
        }

        return parent::render($view, $layout);
    }

    protected function _getSetting($key, $default = null)
    {
        try {
            $Setting = ClassRegistry::init('BillingManager.BillingSetting');
            $row = $Setting->find('first', array(
                'conditions' => array('BillingSetting.setting_key' => $key)
            ));
            if (!empty($row['BillingSetting']['setting_value'])) {
                return $row['BillingSetting']['setting_value'];
            }
        } catch (Exception $e) {}
        return $default;
    }

    protected function _getActiveSocietyId()
    {
        $socId = $this->Session->read('BillingManager.society_id');
        return !empty($socId) ? (int)$socId : 1;
    }

    protected function _getAgencyDropdownList()
    {
        try {
            $Society = ClassRegistry::init('BillingManager.BillingSociety');
            return $Society->find('list', array(
                'fields' => array('BillingSociety.id', 'BillingSociety.name'),
                'conditions' => array('BillingSociety.status' => 'active'),
                'order' => array('BillingSociety.id' => 'ASC')
            ));
        } catch (Exception $e) {
            return array(1 => 'White Villas Lahore');
        }
    }

    protected function _ensureBillingPages()
    {
        try {
            $Page = ClassRegistry::init('Page');
            $PageRight = ClassRegistry::init('PageRight');

            $rootCheck = $Page->find('first', array(
                'conditions' => array('Page.id' => 1051)
            ));

            $rootPageId = 0;
            if(empty($rootCheck['Page']['id']))
            {
                $Page->create();
                $Page->save(array('Page' => array(
                    'id' => 1051,
                    'model_name' => 'BillingManager',
                    'page_name' => 'Billing System',
                    'controller_name' => 'BillingDashboards',
                    'action_name' => 'index',
                    'icon' => 'fa fa-calculator',
                    'parent_id' => 0,
                    'ordering' => 10,
                    'published' => 'Yes',
                    'sel_name' => 'billingdashboards,billingbills,billingpaymentrequests,billingreadings,billingmeters,billingproperties,billingclients,billingextracharges,billingtemplates,billingbankreconciliations,billingreminders,billingsocieties,billingauditlogs,billingsettings,billingmigrations'
                )));
                $rootPageId = (int)$Page->id;
            }
            else
            {
                $rootPageId = (int)$rootCheck['Page']['id'];
            }

            if($rootPageId > 0)
            {
                $right = $PageRight->find('first', array(
                    'conditions' => array('PageRight.page_id' => $rootPageId, 'PageRight.ugroup_id' => 1)
                ));
                if(empty($right['PageRight']['id']))
                {
                    $PageRight->create();
                    $PageRight->save(array('PageRight' => array(
                        'page_id' => $rootPageId,
                        'ugroup_id' => 1,
                        'save_right' => 1,
                        'update_right' => 1,
                        'view_right' => 1,
                        'search_right' => 1
                    )));
                }

                $subPages = array(
                    array('name' => 'Billing Dashboard', 'ctrl' => 'BillingDashboards', 'act' => 'index', 'icon' => 'fa fa-bolt', 'order' => 1),
                    array('name' => 'Bills & Payments', 'ctrl' => 'BillingBills', 'act' => 'index', 'icon' => 'fa fa-file-text-o', 'order' => 2),
                    array('name' => 'Reports & Recovery Hub', 'ctrl' => 'BillingReports', 'act' => 'index', 'icon' => 'fa fa-bar-chart', 'order' => 3),
                    array('name' => 'Transformer & Line Loss Audit', 'ctrl' => 'BillingReports', 'act' => 'line_loss_audit', 'icon' => 'fa fa-flash', 'order' => 4),
                    array('name' => 'Payment Paid Request', 'ctrl' => 'BillingPaymentRequests', 'act' => 'index', 'icon' => 'fa fa-credit-card', 'order' => 5),
                    array('name' => 'Meter Readings', 'ctrl' => 'BillingReadings', 'act' => 'index', 'icon' => 'fa fa-tachometer', 'order' => 6),
                    array('name' => 'Utility Meters', 'ctrl' => 'BillingMeters', 'act' => 'index', 'icon' => 'fa fa-plug', 'order' => 7),
                    array('name' => 'Property Units', 'ctrl' => 'BillingProperties', 'act' => 'index', 'icon' => 'fa fa-building-o', 'order' => 8),
                    array('name' => 'Billing Consumers', 'ctrl' => 'BillingClients', 'act' => 'index', 'icon' => 'fa fa-users', 'order' => 9),
                    array('name' => 'Maintenance Charges', 'ctrl' => 'BillingExtraCharges', 'act' => 'index', 'icon' => 'fa fa-list-alt', 'order' => 10),
                    array('name' => 'Print Templates', 'ctrl' => 'BillingTemplates', 'act' => 'index', 'icon' => 'fa fa-print', 'order' => 11),
                    array('name' => 'Bank Reconciliation', 'ctrl' => 'BillingBankReconciliations', 'act' => 'index', 'icon' => 'fa fa-university', 'order' => 12),
                    array('name' => 'SMS & WhatsApp', 'ctrl' => 'BillingReminders', 'act' => 'index', 'icon' => 'fa fa-comments', 'order' => 13),
                    array('name' => 'Society Branding', 'ctrl' => 'BillingSocieties', 'act' => 'index', 'icon' => 'fa fa-home', 'order' => 14),
                    array('name' => 'Audit Trail', 'ctrl' => 'BillingAuditLogs', 'act' => 'index', 'icon' => 'fa fa-history', 'order' => 15),
                    array('name' => 'Billing Settings', 'ctrl' => 'BillingSettings', 'act' => 'index', 'icon' => 'fa fa-cogs', 'order' => 16),
                    array('name' => 'Backup & Migration', 'ctrl' => 'BillingMigrations', 'act' => 'index', 'icon' => 'fa fa-cloud-upload', 'order' => 17)
                );

                foreach($subPages as $sp)
                {
                    $childCheck = $Page->find('first', array(
                        'conditions' => array(
                            'LOWER(Page.controller_name)' => strtolower($sp['ctrl']),
                            'LOWER(Page.action_name)' => strtolower($sp['act']),
                            'Page.parent_id' => $rootPageId
                        )
                    ));
                    $childId = 0;
                    if(empty($childCheck['Page']['id']))
                    {
                        $Page->create();
                        $Page->save(array('Page' => array(
                            'model_name' => 'BillingManager',
                            'page_name' => $sp['name'],
                            'controller_name' => $sp['ctrl'],
                            'action_name' => $sp['act'],
                            'icon' => $sp['icon'],
                            'parent_id' => $rootPageId,
                            'ordering' => $sp['order'],
                            'published' => 'Yes',
                            'sel_name' => strtolower($sp['ctrl'])
                        )));
                        $childId = (int)$Page->id;
                    }
                    else
                    {
                        $childId = (int)$childCheck['Page']['id'];
                    }

                    if($childId > 0)
                    {
                        for($ugId = 1; $ugId <= 10; $ugId++)
                        {
                            $cRight = $PageRight->find('first', array(
                                'conditions' => array('PageRight.page_id' => $childId, 'PageRight.ugroup_id' => $ugId)
                            ));
                            if(empty($cRight['PageRight']['id']))
                            {
                                $PageRight->create();
                                $PageRight->save(array('PageRight' => array(
                                    'page_id' => $childId,
                                    'ugroup_id' => $ugId,
                                    'save_right' => 1,
                                    'update_right' => 1,
                                    'view_right' => 1,
                                    'search_right' => 1
                                )));
                            }
                        }
                    }
                }
            }
        } catch(Exception $e) {}
    }

    public function _syncBillPaidIncomeLedgerTransaction($billId)
    {
        try {
            $billId = (int)$billId;
            if (!$billId) return false;

            $BillModel = ClassRegistry::init('BillingManager.BillingBill');
            $bill = $BillModel->find('first', array(
                'conditions' => array('BillingBill.id' => $billId),
                'contain' => array('BillingClient', 'BillingProperty')
            ));

            if (empty($bill) || empty($bill['BillingBill']) || (float)$bill['BillingBill']['paid_amount'] <= 0) {
                return false;
            }

            $b = $bill['BillingBill'];
            $c = !empty($bill['BillingClient']) ? $bill['BillingClient'] : array();
            $p = !empty($bill['BillingProperty']) ? $bill['BillingProperty'] : array();

            $paidAmount = (float)$b['paid_amount'];
            
            // Resolve society ID from property first for 100% project accuracy
            $societyId = !empty($p['society_id']) ? (int)$p['society_id'] : (!empty($b['society_id']) ? (int)$b['society_id'] : 1);
            $projectId = ($societyId == 2) ? 22 : 20;

            $db = ConnectionManager::getDataSource('default');

            // 1. Ensure billing_income_ledger_id in ardaccounts_settings
            $ledgerId = 0;
            $sRes = $db->query("SELECT billing_income_ledger_id FROM ardaccounts_settings WHERE project_id = {$projectId} LIMIT 1");
            if (!empty($sRes[0]['ardaccounts_settings']['billing_income_ledger_id'])) {
                $ledgerId = (int)$sRes[0]['ardaccounts_settings']['billing_income_ledger_id'];
            }

            if (!$ledgerId) {
                $lRes = $db->query("SELECT id FROM ardaccounts_ledgers WHERE project_id = {$projectId} AND (type = 'income' AND name LIKE '%Billing%') AND is_trashed = 0 LIMIT 1");
                if (!empty($lRes[0]['ardaccounts_ledgers']['id'])) {
                    $ledgerId = (int)$lRes[0]['ardaccounts_ledgers']['id'];
                }
            }

            if (!$ledgerId) {
                $lName = $db->value("Billing Utility Revenue - Society #" . $societyId);
                $now = $db->value(date('Y-m-d H:i:s'));
                $db->query("INSERT INTO ardaccounts_ledgers (project_id, name, type, category, balance, is_trashed, created, modified) VALUES ({$projectId}, {$lName}, 'income', 'Utility Billing Income', 0.00, 0, {$now}, {$now})");
                $ledgerId = (int)$db->lastInsertId();

                $db->query("UPDATE ardaccounts_settings SET billing_income_ledger_id = {$ledgerId} WHERE project_id = {$projectId}");
            }

            // Find receiving cash/bank account for this project
            $receiptLedgerId = 0;
            $sRes = $db->query("SELECT deal_receipt_cash_ledger_id, deal_receipt_online_ledger_id FROM ardaccounts_settings WHERE project_id = {$projectId} LIMIT 1");
            if (!empty($sRes[0]['ardaccounts_settings']['deal_receipt_cash_ledger_id'])) {
                $receiptLedgerId = (int)$sRes[0]['ardaccounts_settings']['deal_receipt_cash_ledger_id'];
            }
            if (!$receiptLedgerId) {
                $cRes = $db->query("SELECT id FROM ardaccounts_ledgers WHERE project_id = {$projectId} AND type IN ('bank','asset') AND is_trashed = 0 ORDER BY id ASC LIMIT 1");
                if (!empty($cRes[0]['ardaccounts_ledgers']['id'])) {
                    $receiptLedgerId = (int)$cRes[0]['ardaccounts_ledgers']['id'];
                }
            }
            if (!$receiptLedgerId) {
                $receiptLedgerId = $ledgerId;
            }

            // 2. Check if transaction exists
            $tRes = $db->query("SELECT id FROM ardaccounts_transactions WHERE source_type = 'billing_bill' AND source_id = {$billId} LIMIT 1");

            $billNo = !empty($b['bill_number']) ? $b['bill_number'] : ('WB9' . str_pad($billId, 3, '0', STR_PAD_LEFT));
            $clientName = !empty($c['name']) ? $c['name'] : 'Resident';
            $propNo = !empty($p['property_no']) ? $p['property_no'] : 'WV Unit';
            $descStr = $db->value("Paid Utility Bill #{$billNo} - Consumer: {$clientName} ({$propNo})");
            $refStr = $db->value($billNo);
            $entryDateStr = !empty($b['payment_date']) && $b['payment_date'] !== '0000-00-00 00:00:00' ? $db->value(date('Y-m-d', strtotime($b['payment_date']))) : $db->value(date('Y-m-d'));
            $nowStr = $db->value(date('Y-m-d H:i:s'));

            $savedTxId = 0;
            if (!empty($tRes[0]['ardaccounts_transactions']['id'])) {
                $savedTxId = (int)$tRes[0]['ardaccounts_transactions']['id'];
                $db->query("UPDATE ardaccounts_transactions SET project_id = {$projectId}, ledger_id = {$receiptLedgerId}, income_ledger_id = {$ledgerId}, amount = {$paidAmount}, entry_date = {$entryDateStr}, description = {$descStr}, modified = {$nowStr} WHERE id = {$savedTxId}");
            } else {
                $db->query("INSERT INTO ardaccounts_transactions 
                    (project_id, ledger_id, income_ledger_id, type, status, amount, description, ref_no, entry_date, source_type, source_id, auto_posted, created_by, created, modified) 
                    VALUES ({$projectId}, {$receiptLedgerId}, {$ledgerId}, 'income', 'Posted', {$paidAmount}, {$descStr}, {$refStr}, {$entryDateStr}, 'billing_bill', {$billId}, 1, 1, {$nowStr}, {$nowStr})");
                $savedTxId = (int)$db->lastInsertId();
            }

            // Sync journal entry for double-entry ledger balance
            if ($savedTxId > 0) {
                $recRow = $db->query("SELECT name FROM ardaccounts_ledgers WHERE id = {$receiptLedgerId} LIMIT 1");
                $recName = !empty($recRow[0]['ardaccounts_ledgers']['name']) ? $recRow[0]['ardaccounts_ledgers']['name'] : 'Cash-inhand';
                $incRow = $db->query("SELECT name FROM ardaccounts_ledgers WHERE id = {$ledgerId} LIMIT 1");
                $incName = !empty($incRow[0]['ardaccounts_ledgers']['name']) ? $incRow[0]['ardaccounts_ledgers']['name'] : 'Utility Revenue';
                $debitAccStr = $db->value($recName);
                $creditAccStr = $db->value($incName);

                $jRes = $db->query("SELECT id FROM ardaccounts_journal_entries WHERE transaction_id = {$savedTxId} LIMIT 1");
                if (!empty($jRes[0]['ardaccounts_journal_entries']['id'])) {
                    $jId = (int)$jRes[0]['ardaccounts_journal_entries']['id'];
                    $db->query("UPDATE ardaccounts_journal_entries SET debit_account = {$debitAccStr}, credit_account = {$creditAccStr}, amount = {$paidAmount}, entry_date = {$entryDateStr}, description = {$descStr}, modified = {$nowStr} WHERE id = {$jId}");
                } else {
                    $db->query("INSERT INTO ardaccounts_journal_entries (project_id, transaction_id, source_type, source_id, ref_no, entry_date, debit_account, credit_account, amount, description, created, modified) VALUES ({$projectId}, {$savedTxId}, 'billing_bill', {$billId}, {$refStr}, {$entryDateStr}, {$debitAccStr}, {$creditAccStr}, {$paidAmount}, {$descStr}, {$nowStr}, {$nowStr})");
                }
            }

            // 3. Recalculate Ledger Balance
            $balRes = $db->query("SELECT SUM(amount) as total_bal FROM ardaccounts_transactions WHERE income_ledger_id = {$ledgerId} AND status = 'Posted'");
            $totBal = !empty($balRes[0][0]['total_bal']) ? (float)$balRes[0][0]['total_bal'] : 0.00;
            $db->query("UPDATE ardaccounts_ledgers SET balance = {$totBal} WHERE id = {$ledgerId}");

            return true;
        } catch (Exception $ex) {
            return false;
        }
    }

    public function _ensureSocietyBillingIncomeLedger($societyId, $societyName)
    {
        try {
            $societyId = (int)$societyId;
            $projectId = ($societyId == 2) ? 22 : 20;
            $db = ConnectionManager::getDataSource('default');

            $lRes = $db->query("SELECT id FROM ardaccounts_ledgers WHERE project_id = {$projectId} AND (type = 'income' AND name LIKE '%Billing%') AND is_trashed = 0 LIMIT 1");
            if (!empty($lRes[0]['ardaccounts_ledgers']['id'])) {
                $ledgerId = (int)$lRes[0]['ardaccounts_ledgers']['id'];
            } else {
                $lName = $db->value("Billing Utility Revenue - " . $societyName);
                $now = $db->value(date('Y-m-d H:i:s'));
                $db->query("INSERT INTO ardaccounts_ledgers (project_id, name, type, category, balance, is_trashed, created, modified) VALUES ({$projectId}, {$lName}, 'income', 'Utility Billing Income', 0.00, 0, {$now}, {$now})");
                $ledgerId = (int)$db->lastInsertId();
            }

            $db->query("UPDATE ardaccounts_settings SET billing_income_ledger_id = {$ledgerId} WHERE project_id = {$projectId}");
            return $ledgerId;
        } catch (Exception $e) {
            return 0;
        }
    }
}
