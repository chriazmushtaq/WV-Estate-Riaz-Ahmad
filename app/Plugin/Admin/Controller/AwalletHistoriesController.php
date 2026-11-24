<?php
class AwalletHistoriesController extends AdminAppController {
    public $helpers = array('Html', 'Form','Session','Paginator','Js'=> array('Jquery'));
    public $components = array('Session','Paginator','search-master.Prg');
    public $presetVars = true;
    var $paginate = array('page'=>1,'order'=>array('id'=>'desc'));
    protected function _normalizeYmdDate($rawDate)
    {
        $rawDate = trim((string)$rawDate);
        if($rawDate === '')
        {
            return null;
        }
        $timestamp = strtotime($rawDate);
        if($timestamp === false)
        {
            return null;
        }
        return date('Y-m-d', $timestamp);
    }
    protected function _walletHeaderProjectFields()
    {
        $fields = array('Project.id','Project.name');
        $optionalFields = array('address','phone','mobile','email','logo','image','photo');
        $schema = array();
        try
        {
            $schema = (array)$this->Project->schema();
        }
        catch(Exception $e)
        {
            $schema = array();
        }
        foreach($optionalFields as $optionalField)
        {
            if(isset($schema[$optionalField]))
            {
                $fields[] = 'Project.'.$optionalField;
            }
        }
        return $fields;
    }
    protected function _agentWalletProjectFilterMeta()
    {
        $this->loadModel('Project');
        $this->loadModel('User');
        $isSuperAdmin = $this->_isSuperAdminSession((array)$this->Session->read('User'));
        $allowedProjectIds = array();
        if($isSuperAdmin)
        {
            $projectOptions = (array)$this->Project->find('list', array(
                'fields' => array('Project.id','Project.name'),
                'order' => array('Project.name' => 'asc')
            ));
            $allowedProjectIds = array_map('intval', array_keys($projectOptions));
        }
        else
        {
            $assignedProjectIds = array();
            try
            {
                $assignedProjectIds = (array)$this->User->assignedProjectIds((int)$this->luserId, true);
            }
            catch(Exception $e)
            {
                $assignedProjectIds = array();
            }
            if(empty($assignedProjectIds) && (int)$this->agencyId > 0)
            {
                $assignedProjectIds = array((int)$this->agencyId);
            }
            $assignedProjectIds = array_values(array_unique(array_filter(array_map('intval', (array)$assignedProjectIds))));
            if(!empty($assignedProjectIds))
            {
                $projectOptions = (array)$this->Project->find('list', array(
                    'fields' => array('Project.id','Project.name'),
                    'conditions' => array('Project.id' => $assignedProjectIds),
                    'order' => array('Project.name' => 'asc')
                ));
                $allowedProjectIds = array_map('intval', array_keys($projectOptions));
            }
            else
            {
                $projectOptions = array();
            }
        }
        return array(
            'is_super_admin' => $isSuperAdmin ? 1 : 0,
            'project_options' => $projectOptions,
            'allowed_project_ids' => $allowedProjectIds
        );
    }
    protected function _canManageCommissionPayout()
    {
        $type = strtoupper(trim((string)$this->userType));
        return in_array($type, array('ADR','AGY'), true);
    }
    protected function _walletSourceLedgerOptionsByProject($allowedProjectIds = array())
    {
        $this->loadModel('Admin.Ardledger');
        $this->loadModel('Project');
        $allowedProjectIds = array_values(array_unique(array_filter(array_map('intval', (array)$allowedProjectIds))));
        $conditions = array('Ardledger.is_trashed' => 0);
        if(!empty($allowedProjectIds))
        {
            $conditions['Ardledger.project_id'] = $allowedProjectIds;
        }
        $rows = (array)$this->Ardledger->find('all', array(
            'joins' => array(
                array('table' => 'projects', 'alias' => 'Project', 'type' => 'LEFT', 'conditions' => array('Project.id = Ardledger.project_id'))
            ),
            'fields' => array('Ardledger.id','Ardledger.project_id','Ardledger.name','Ardledger.type','Project.name'),
            'conditions' => $conditions,
            'order' => array('Project.name' => 'asc', 'Ardledger.name' => 'asc'),
            'recursive' => -1
        ));
        $byProject = array();
        $flat = array();
        foreach($rows as $row)
        {
            $ledger = isset($row['Ardledger']) ? (array)$row['Ardledger'] : array();
            $ledgerId = isset($ledger['id']) ? (int)$ledger['id'] : 0;
            $projectId = isset($ledger['project_id']) ? (int)$ledger['project_id'] : 0;
            $ledgerType = strtolower(trim((string)(isset($ledger['type']) ? $ledger['type'] : '')));
            if($ledgerId <= 0 || $projectId <= 0 || $ledgerType === 'expense')
            {
                continue;
            }
            if(!isset($byProject[$projectId]))
            {
                $byProject[$projectId] = array();
            }
            $ledgerName = trim((string)(isset($ledger['name']) ? $ledger['name'] : ''));
            if($ledgerName === '')
            {
                $ledgerName = 'Ledger #'.$ledgerId;
            }
            $agencyName = trim((string)(isset($row['Project']['name']) ? $row['Project']['name'] : ''));
            $projectLabel = $ledgerName;
            if($this->_isSuperAdminSession((array)$this->Session->read('User')) && $agencyName !== '')
            {
                $projectLabel = $agencyName.' | '.$ledgerName;
            }
            $byProject[$projectId][$ledgerId] = $projectLabel;
            $flat[$ledgerId] = $projectLabel;
        }
        return array('flat' => $flat, 'by_project' => $byProject);
    }
    protected function _ensurePaidCommissionExpenseCategory()
    {
        $this->loadModel('ExpenseCategory');
        $post = (array)$this->ExpenseCategory->find('first', array(
            'fields' => array('ExpenseCategory.id'),
            'conditions' => array('LOWER(ExpenseCategory.name)' => 'paid commission'),
            'order' => array('ExpenseCategory.id' => 'asc'),
            'recursive' => -1
        ));
        if(!empty($post['ExpenseCategory']['id']))
        {
            return (int)$post['ExpenseCategory']['id'];
        }
        $this->ExpenseCategory->create();
        if($this->ExpenseCategory->save(array('ExpenseCategory' => array('name' => 'Paid Commission', 'status' => 'Active')), false))
        {
            return (int)$this->ExpenseCategory->id;
        }
        return 0;
    }
    protected function _ensurePaidCommissionVendor($projectId)
    {
        $projectId = (int)$projectId;
        if($projectId <= 0)
        {
            return 0;
        }
        $this->loadModel('Vendor');
        $post = (array)$this->Vendor->find('first', array(
            'fields' => array('Vendor.id'),
            'conditions' => array(
                'Vendor.project_id' => $projectId,
                'LOWER(Vendor.name)' => 'agent commission payout'
            ),
            'order' => array('Vendor.id' => 'asc'),
            'recursive' => -1
        ));
        if(!empty($post['Vendor']['id']))
        {
            return (int)$post['Vendor']['id'];
        }
        $this->Vendor->create();
        if($this->Vendor->save(array('Vendor' => array(
            'project_id' => $projectId,
            'name' => 'Agent Commission Payout',
            'contact' => '',
            'address' => '',
            'licence_no' => ''
        )), false))
        {
            return (int)$this->Vendor->id;
        }
        return 0;
    }
    protected function _ensurePaidCommissionExpenseLedger($projectId)
    {
        $projectId = (int)$projectId;
        if($projectId <= 0)
        {
            return 0;
        }
        $this->loadModel('Admin.Ardledger');
        $post = (array)$this->Ardledger->find('first', array(
            'fields' => array('Ardledger.id'),
            'conditions' => array(
                'Ardledger.project_id' => $projectId,
                'Ardledger.is_trashed' => 0,
                'LOWER(Ardledger.type)' => 'expense',
                'LOWER(Ardledger.name)' => 'paid commission'
            ),
            'order' => array('Ardledger.id' => 'asc'),
            'recursive' => -1
        ));
        if(!empty($post['Ardledger']['id']))
        {
            return (int)$post['Ardledger']['id'];
        }
        $this->Ardledger->create();
        if($this->Ardledger->save(array('Ardledger' => array(
            'project_id' => $projectId,
            'name' => 'Paid Commission',
            'type' => 'expense',
            'category' => '',
            'account_no' => '',
            'mobile_no' => '',
            'address' => '',
            'balance' => 0,
            'is_trashed' => 0
        )), false))
        {
            return (int)$this->Ardledger->id;
        }
        return 0;
    }
    protected function _nextWalletCommissionVoucherRefNo($projectId)
    {
        $projectId = (int)$projectId;
        if($projectId <= 0)
        {
            return '';
        }
        $this->loadModel('Admin.Ardsetting');
        $settingRow = (array)$this->Ardsetting->find('first', array(
            'conditions' => array('Ardsetting.project_id' => $projectId),
            'recursive' => -1
        ));
        $settingData = !empty($settingRow['Ardsetting']) ? (array)$settingRow['Ardsetting'] : array();
        $prefix = trim((string)(isset($settingData['expense_voucher_prefix']) ? $settingData['expense_voucher_prefix'] : 'EXP-'));
        if($prefix === '')
        {
            $prefix = 'EXP-';
        }
        $prefix = preg_replace('/[^A-Za-z0-9\-\/]/', '', $prefix);
        if($prefix === '')
        {
            $prefix = 'EXP-';
        }
        if(strlen($prefix) > 20)
        {
            $prefix = substr($prefix, 0, 20);
        }
        $nextNo = max(1, (int)(isset($settingData['expense_voucher_next_no']) ? $settingData['expense_voucher_next_no'] : 1));
        $refNo = $prefix.$nextNo;
        $saveSetting = array(
            'project_id' => $projectId,
            'expense_voucher_prefix' => $prefix,
            'expense_voucher_next_no' => $nextNo + 1
        );
        if(!empty($settingData['id']))
        {
            $saveSetting['id'] = (int)$settingData['id'];
        }
        $this->Ardsetting->create();
        $this->Ardsetting->save(array('Ardsetting' => $saveSetting), false);
        return $refNo;
    }
    protected function _recalculateArdLedgerBalanceLocal($ledgerId)
    {
        return $this->_ardRecalculateLedgerBalanceCompat($ledgerId);
    }
    protected function _syncWalletCommissionJournalEntry($transactionId)
    {
        $this->loadModel('Admin.Ardtransaction');
        $this->loadModel('Admin.Ardledger');
        $this->loadModel('Admin.Ardjournalentry');
        $transactionId = (int)$transactionId;
        if($transactionId <= 0)
        {
            return false;
        }
        $txRow = (array)$this->Ardtransaction->find('first', array(
            'conditions' => array('Ardtransaction.id' => $transactionId),
            'recursive' => -1
        ));
        if(empty($txRow['Ardtransaction']))
        {
            return false;
        }
        $tx = (array)$txRow['Ardtransaction'];
        $sourceLedgerName = 'Ledger #'.(int)$tx['ledger_id'];
        if((int)$tx['ledger_id'] > 0)
        {
            $sourceLedgerName = (string)$this->Ardledger->field('name', array('Ardledger.id' => (int)$tx['ledger_id']));
            if(trim($sourceLedgerName) === '')
            {
                $sourceLedgerName = 'Ledger #'.(int)$tx['ledger_id'];
            }
        }
        $expenseLedgerName = '';
        if((int)$tx['expense_ledger_id'] > 0)
        {
            $expenseLedgerName = (string)$this->Ardledger->field('name', array('Ardledger.id' => (int)$tx['expense_ledger_id']));
        }
        $type = strtolower(trim((string)(isset($tx['type']) ? $tx['type'] : '')));
        $debitAccount = $sourceLedgerName;
        $creditAccount = $sourceLedgerName;
        if($type === 'expense')
        {
            $debitAccount = (trim($expenseLedgerName) !== '') ? trim($expenseLedgerName) : 'Expense Account';
            $creditAccount = $sourceLedgerName;
        }
        elseif($type === 'income')
        {
            $debitAccount = $sourceLedgerName;
            $creditAccount = 'Income Account';
        }
        $existing = (array)$this->Ardjournalentry->find('first', array(
            'fields' => array('Ardjournalentry.id'),
            'conditions' => array('Ardjournalentry.transaction_id' => $transactionId),
            'recursive' => -1
        ));
        $data = array(
            'project_id' => (int)$tx['project_id'],
            'transaction_id' => $transactionId,
            'source_type' => 'voucher',
            'source_id' => $transactionId,
            'ref_no' => isset($tx['ref_no']) ? (string)$tx['ref_no'] : '',
            'entry_date' => isset($tx['entry_date']) ? (string)$tx['entry_date'] : date('Y-m-d'),
            'debit_account' => $debitAccount,
            'credit_account' => $creditAccount,
            'amount' => (float)(isset($tx['amount']) ? $tx['amount'] : 0),
            'description' => isset($tx['description']) ? (string)$tx['description'] : ''
        );
        if(!empty($existing['Ardjournalentry']['id']))
        {
            $data['id'] = (int)$existing['Ardjournalentry']['id'];
        }
        $this->Ardjournalentry->create();
        return (bool)$this->Ardjournalentry->save(array('Ardjournalentry' => $data), false);
    }
    protected function _extractVoucherRefNoFromText($text)
    {
        $text = trim((string)$text);
        if($text === '')
        {
            return '';
        }
        if(preg_match('/\bRef:\s*([A-Za-z0-9\/\-_]+)/i', $text, $matches))
        {
            return trim((string)$matches[1]);
        }
        return '';
    }
    protected function _buildPaidCommissionHistoryConditions($agentId, $scopeCondition = array())
    {
        $agentId = (int)$agentId;
        $conditions = array(
            'AwalletHistory.user_id' => $agentId,
            'AwalletHistory.type' => 'DR',
            'AwalletHistory.amount >' => 0
        );
        $conditions[] = array('AwalletHistory.remarks LIKE' => 'Paid Commission to %');
        $conditions[] = array('AwalletHistory.remarks LIKE' => '%| Ref:%');
        $conditions[] = array(
            'OR' => array(
                array('AwalletHistory.deals_payment_id' => null),
                array('AwalletHistory.deals_payment_id' => 0)
            )
        );
        if(!empty($scopeCondition))
        {
            $conditions[] = $scopeCondition;
        }
        return $conditions;
    }
    protected function _normalizePaidCommissionRemarks($inputRemarks, $fallbackRemarks = '', $refNo = '')
    {
        $inputRemarks = trim((string)$inputRemarks);
        $fallbackRemarks = trim((string)$fallbackRemarks);
        $refNo = trim((string)$refNo);
        if($inputRemarks === '')
        {
            $inputRemarks = $fallbackRemarks;
        }
        if($inputRemarks === '')
        {
            $inputRemarks = 'Paid Commission to Agent';
        }
        if(stripos($inputRemarks, 'Paid Commission to ') !== 0)
        {
            $fallbackPrefix = $fallbackRemarks;
            $refPos = stripos($fallbackPrefix, '| Ref:');
            if($refPos !== false)
            {
                $fallbackPrefix = trim(substr($fallbackPrefix, 0, $refPos));
            }
            $firstPipePos = strpos($fallbackPrefix, '|');
            if($firstPipePos !== false)
            {
                $fallbackPrefix = trim(substr($fallbackPrefix, 0, $firstPipePos));
            }
            if($fallbackPrefix === '')
            {
                $fallbackPrefix = 'Paid Commission to Agent';
            }
            if($inputRemarks !== $fallbackPrefix)
            {
                $inputRemarks = $fallbackPrefix.' | '.$inputRemarks;
            }
            else
            {
                $inputRemarks = $fallbackPrefix;
            }
        }
        if($refNo !== '')
        {
            $inputRemarks = preg_replace('/\s*\|\s*Ref:\s*[A-Za-z0-9\/\-_]+/i', '', $inputRemarks);
            $inputRemarks = trim($inputRemarks).' | Ref: '.$refNo;
        }
        if(strlen($inputRemarks) > 100)
        {
            if($refNo !== '')
            {
                $suffix = ' | Ref: '.$refNo;
                $baseText = preg_replace('/\s*\|\s*Ref:\s*[A-Za-z0-9\/\-_]+/i', '', $inputRemarks);
                $maxBaseLength = 100 - strlen($suffix);
                if($maxBaseLength < 20)
                {
                    $maxBaseLength = 20;
                }
                $inputRemarks = trim(substr($baseText, 0, $maxBaseLength)).$suffix;
            }
            if(strlen($inputRemarks) > 100)
            {
                $inputRemarks = substr($inputRemarks, 0, 100);
            }
        }
        return trim($inputRemarks);
    }
    protected function _updatePaidCommissionPayout($agentId, $editData = array(), $allowedProjectIds = array(), $scopeCondition = array())
    {
        $agentId = (int)$agentId;
        $editData = (array)$editData;
        $allowedProjectIds = array_values(array_unique(array_filter(array_map('intval', (array)$allowedProjectIds))));
        if($agentId <= 0)
        {
            return array('ok' => false, 'message' => __('Invalid agent.'));
        }
        if(!$this->_canManageCommissionPayout())
        {
            return array('ok' => false, 'message' => __('No Permission!'));
        }
        $historyId = isset($editData['history_id']) ? (int)$editData['history_id'] : 0;
        $transactionId = isset($editData['transaction_id']) ? (int)$editData['transaction_id'] : 0;
        $newAmount = round((float)(isset($editData['amount']) ? $editData['amount'] : 0), 2);
        $entryDate = $this->_normalizeYmdDate(isset($editData['entry_date']) ? $editData['entry_date'] : null);
        $inputRemarks = trim((string)(isset($editData['remarks']) ? $editData['remarks'] : ''));
        $postedRefNo = trim((string)(isset($editData['voucher_ref']) ? $editData['voucher_ref'] : ''));
        if($historyId <= 0)
        {
            return array('ok' => false, 'message' => __('Invalid paid commission entry.'));
        }
        if($newAmount <= 0)
        {
            return array('ok' => false, 'message' => __('Please enter valid amount.'));
        }
        if($entryDate === null)
        {
            return array('ok' => false, 'message' => __('Please enter valid date.'));
        }

        $this->loadModel('Awallet');
        $this->loadModel('AwalletHistory');
        $this->loadModel('Admin.Ardtransaction');
        $this->loadModel('Admin.Ardjournalentry');
        $this->loadModel('Expense');
        $this->loadModel('ExpensesPayment');

        $historyConditions = $this->_buildPaidCommissionHistoryConditions($agentId, $scopeCondition);
        $historyConditions['AwalletHistory.id'] = $historyId;
        $historyRow = (array)$this->AwalletHistory->find('first', array(
            'fields' => array('AwalletHistory.id','AwalletHistory.user_id','AwalletHistory.project_id','AwalletHistory.amount','AwalletHistory.date','AwalletHistory.remarks'),
            'conditions' => $historyConditions,
            'recursive' => -1
        ));
        if(empty($historyRow['AwalletHistory']['id']))
        {
            return array('ok' => false, 'message' => __('Paid commission entry not found.'));
        }
        $projectId = (int)$historyRow['AwalletHistory']['project_id'];
        if(!empty($allowedProjectIds) && !in_array($projectId, $allowedProjectIds, true))
        {
            return array('ok' => false, 'message' => __('No Permission!'));
        }

        $oldAmount = round((float)$historyRow['AwalletHistory']['amount'], 2);
        $oldRemarks = trim((string)$historyRow['AwalletHistory']['remarks']);
        $refNo = $this->_extractVoucherRefNoFromText($oldRemarks);
        if($refNo === '' && $postedRefNo !== '')
        {
            $refNo = $postedRefNo;
        }

        $transactionConditions = array(
            'Ardtransaction.project_id' => $projectId,
            'Ardtransaction.type' => 'expense'
        );
        if($transactionId > 0)
        {
            $transactionConditions['Ardtransaction.id'] = $transactionId;
        }
        elseif($refNo !== '')
        {
            $transactionConditions['Ardtransaction.ref_no'] = $refNo;
        }
        $transactionRow = array();
        if($transactionId > 0 || $refNo !== '')
        {
            $transactionRow = (array)$this->Ardtransaction->find('first', array(
                'fields' => array('Ardtransaction.id','Ardtransaction.project_id','Ardtransaction.ledger_id','Ardtransaction.expense_ledger_id','Ardtransaction.ref_no'),
                'conditions' => $transactionConditions,
                'order' => array('Ardtransaction.id' => 'desc'),
                'recursive' => -1
            ));
        }
        if($transactionId > 0 && empty($transactionRow['Ardtransaction']['id']))
        {
            return array('ok' => false, 'message' => __('Linked transaction not found.'));
        }
        if(!empty($transactionRow['Ardtransaction']['ref_no']) && $refNo === '')
        {
            $refNo = trim((string)$transactionRow['Ardtransaction']['ref_no']);
        }

        $normalizedRemarks = $this->_normalizePaidCommissionRemarks($inputRemarks, $oldRemarks, $refNo);
        $historyTimePart = '00:00:00';
        $historyDateRaw = isset($historyRow['AwalletHistory']['date']) ? (string)$historyRow['AwalletHistory']['date'] : '';
        if($historyDateRaw !== '' && strtotime($historyDateRaw) !== false)
        {
            $historyTimePart = date('H:i:s', strtotime($historyDateRaw));
        }
        $historyDateTime = $entryDate.' '.$historyTimePart;

        $deltaAmount = round($newAmount - $oldAmount, 2);
        $sourceLedgerId = !empty($transactionRow['Ardtransaction']['ledger_id']) ? (int)$transactionRow['Ardtransaction']['ledger_id'] : 0;
        $expenseLedgerId = !empty($transactionRow['Ardtransaction']['expense_ledger_id']) ? (int)$transactionRow['Ardtransaction']['expense_ledger_id'] : 0;

        $ds = $this->AwalletHistory->getDataSource();
        $ds->begin();
        try
        {
            if(abs($deltaAmount) > 0.00001)
            {
                $walletRow = (array)$this->Awallet->find('first', array(
                    'conditions' => array('Awallet.user_id' => $agentId),
                    'recursive' => -1
                ));
                if(empty($walletRow['Awallet']['id']))
                {
                    throw new Exception(__('Wallet not found.'));
                }
                $currentDebit = (float)$walletRow['Awallet']['debit'];
                $currentBalance = (float)$walletRow['Awallet']['balance'];
                $newDebit = round($currentDebit + $deltaAmount, 2);
                if($newDebit < 0)
                {
                    $newDebit = 0;
                }
                $newBalance = round($currentBalance - $deltaAmount, 2);
                $walletSave = array(
                    'Awallet' => array(
                        'id' => (int)$walletRow['Awallet']['id'],
                        'user_id' => $agentId,
                        'project_id' => (int)$walletRow['Awallet']['project_id'],
                        'credit' => (float)$walletRow['Awallet']['credit'],
                        'debit' => $newDebit,
                        'balance' => $newBalance
                    )
                );
                if(!$this->Awallet->save($walletSave, false))
                {
                    throw new Exception(__('Unable to update wallet balance.'));
                }
            }

            if(!$this->AwalletHistory->updateAll(array(
                'AwalletHistory.amount' => $newAmount,
                'AwalletHistory.date' => $ds->value($historyDateTime),
                'AwalletHistory.remarks' => $ds->value($normalizedRemarks)
            ), array('AwalletHistory.id' => $historyId)))
            {
                throw new Exception(__('Unable to update paid commission entry.'));
            }

            $transactionUpdated = false;
            $transactionPrimaryId = !empty($transactionRow['Ardtransaction']['id']) ? (int)$transactionRow['Ardtransaction']['id'] : 0;
            if($transactionPrimaryId > 0)
            {
                $txDataSource = $this->Ardtransaction->getDataSource();
                if(!$this->Ardtransaction->updateAll(array(
                    'Ardtransaction.amount' => $newAmount,
                    'Ardtransaction.entry_date' => $txDataSource->value($entryDate),
                    'Ardtransaction.description' => $txDataSource->value($normalizedRemarks),
                    'Ardtransaction.modified' => $txDataSource->value(date('Y-m-d H:i:s'))
                ), array('Ardtransaction.id' => $transactionPrimaryId)))
                {
                    throw new Exception(__('Unable to update linked transaction.'));
                }
                $transactionUpdated = true;
            }

            if($sourceLedgerId > 0)
            {
                $this->_recalculateArdLedgerBalanceLocal($sourceLedgerId);
            }
            if($expenseLedgerId > 0 && $expenseLedgerId !== $sourceLedgerId)
            {
                $this->_recalculateArdLedgerBalanceLocal($expenseLedgerId);
            }
            if($transactionPrimaryId > 0)
            {
                $this->_syncWalletCommissionJournalEntry($transactionPrimaryId);
            }

            if($refNo !== '')
            {
                $expenseRow = (array)$this->Expense->find('first', array(
                    'fields' => array('Expense.id'),
                    'conditions' => array(
                        'Expense.project_id' => $projectId,
                        'Expense.remarks LIKE' => '%Ref: '.$refNo.'%'
                    ),
                    'order' => array('Expense.id' => 'desc'),
                    'recursive' => -1
                ));
                if(!empty($expenseRow['Expense']['id']))
                {
                    $expenseId = (int)$expenseRow['Expense']['id'];
                    $expenseDataSource = $this->Expense->getDataSource();
                    $this->Expense->updateAll(array(
                        'Expense.invoice_date' => $expenseDataSource->value($entryDate),
                        'Expense.invoice_amount' => $newAmount,
                        'Expense.remarks' => $expenseDataSource->value($normalizedRemarks)
                    ), array('Expense.id' => $expenseId));
                    $expensesPaymentDs = $this->ExpensesPayment->getDataSource();
                    $this->ExpensesPayment->updateAll(array(
                        'ExpensesPayment.amount' => $newAmount,
                        'ExpensesPayment.date' => $expensesPaymentDs->value($entryDate),
                        'ExpensesPayment.remarks' => $expensesPaymentDs->value(substr('Commission payout | Voucher '.$refNo, 0, 255))
                    ), array('ExpensesPayment.expense_id' => $expenseId));
                }
            }

            $ds->commit();
            return array(
                'ok' => true,
                'transaction_updated' => $transactionUpdated
            );
        }
        catch(Exception $e)
        {
            $ds->rollback();
            return array('ok' => false, 'message' => $e->getMessage());
        }
    }
    protected function _deletePaidCommissionPayout($agentId, $deleteData = array(), $allowedProjectIds = array(), $scopeCondition = array())
    {
        $agentId = (int)$agentId;
        $deleteData = (array)$deleteData;
        $allowedProjectIds = array_values(array_unique(array_filter(array_map('intval', (array)$allowedProjectIds))));
        if($agentId <= 0)
        {
            return array('ok' => false, 'message' => __('Invalid agent.'));
        }
        if(!$this->_canManageCommissionPayout())
        {
            return array('ok' => false, 'message' => __('No Permission!'));
        }
        $historyId = isset($deleteData['history_id']) ? (int)$deleteData['history_id'] : 0;
        $transactionId = isset($deleteData['transaction_id']) ? (int)$deleteData['transaction_id'] : 0;
        $postedRefNo = trim((string)(isset($deleteData['voucher_ref']) ? $deleteData['voucher_ref'] : ''));
        if($historyId <= 0)
        {
            return array('ok' => false, 'message' => __('Invalid paid commission entry.'));
        }

        $this->loadModel('Awallet');
        $this->loadModel('AwalletHistory');
        $this->loadModel('Admin.Ardtransaction');
        $this->loadModel('Admin.Ardjournalentry');
        $this->loadModel('Expense');
        $this->loadModel('ExpensesPayment');

        $historyConditions = $this->_buildPaidCommissionHistoryConditions($agentId, $scopeCondition);
        $historyConditions['AwalletHistory.id'] = $historyId;
        $historyRow = (array)$this->AwalletHistory->find('first', array(
            'fields' => array('AwalletHistory.id','AwalletHistory.user_id','AwalletHistory.project_id','AwalletHistory.amount','AwalletHistory.remarks'),
            'conditions' => $historyConditions,
            'recursive' => -1
        ));
        if(empty($historyRow['AwalletHistory']['id']))
        {
            return array('ok' => false, 'message' => __('Paid commission entry not found.'));
        }
        $projectId = (int)$historyRow['AwalletHistory']['project_id'];
        if(!empty($allowedProjectIds) && !in_array($projectId, $allowedProjectIds, true))
        {
            return array('ok' => false, 'message' => __('No Permission!'));
        }

        $historyAmount = round((float)$historyRow['AwalletHistory']['amount'], 2);
        if($historyAmount <= 0)
        {
            return array('ok' => false, 'message' => __('Invalid paid amount.'));
        }
        $refNo = $this->_extractVoucherRefNoFromText(isset($historyRow['AwalletHistory']['remarks']) ? $historyRow['AwalletHistory']['remarks'] : '');
        if($refNo === '' && $postedRefNo !== '')
        {
            $refNo = $postedRefNo;
        }

        $transactionRow = array();
        if($transactionId > 0 || $refNo !== '')
        {
            $transactionConditions = array(
                'Ardtransaction.project_id' => $projectId,
                'Ardtransaction.type' => 'expense'
            );
            if($transactionId > 0)
            {
                $transactionConditions['Ardtransaction.id'] = $transactionId;
            }
            elseif($refNo !== '')
            {
                $transactionConditions['Ardtransaction.ref_no'] = $refNo;
            }
            $transactionRow = (array)$this->Ardtransaction->find('first', array(
                'fields' => array('Ardtransaction.id','Ardtransaction.ledger_id','Ardtransaction.expense_ledger_id','Ardtransaction.ref_no'),
                'conditions' => $transactionConditions,
                'order' => array('Ardtransaction.id' => 'desc'),
                'recursive' => -1
            ));
        }
        if($transactionId > 0 && empty($transactionRow['Ardtransaction']['id']))
        {
            return array('ok' => false, 'message' => __('Linked transaction not found.'));
        }
        if(!empty($transactionRow['Ardtransaction']['ref_no']) && $refNo === '')
        {
            $refNo = trim((string)$transactionRow['Ardtransaction']['ref_no']);
        }

        $sourceLedgerId = !empty($transactionRow['Ardtransaction']['ledger_id']) ? (int)$transactionRow['Ardtransaction']['ledger_id'] : 0;
        $expenseLedgerId = !empty($transactionRow['Ardtransaction']['expense_ledger_id']) ? (int)$transactionRow['Ardtransaction']['expense_ledger_id'] : 0;
        $transactionPrimaryId = !empty($transactionRow['Ardtransaction']['id']) ? (int)$transactionRow['Ardtransaction']['id'] : 0;

        $ds = $this->AwalletHistory->getDataSource();
        $ds->begin();
        try
        {
            $walletRow = (array)$this->Awallet->find('first', array(
                'conditions' => array('Awallet.user_id' => $agentId),
                'recursive' => -1
            ));
            if(empty($walletRow['Awallet']['id']))
            {
                throw new Exception(__('Wallet not found.'));
            }
            $newDebit = round(((float)$walletRow['Awallet']['debit']) - $historyAmount, 2);
            if($newDebit < 0)
            {
                $newDebit = 0;
            }
            $newBalance = round(((float)$walletRow['Awallet']['balance']) + $historyAmount, 2);
            $walletSave = array(
                'Awallet' => array(
                    'id' => (int)$walletRow['Awallet']['id'],
                    'user_id' => $agentId,
                    'project_id' => (int)$walletRow['Awallet']['project_id'],
                    'credit' => (float)$walletRow['Awallet']['credit'],
                    'debit' => $newDebit,
                    'balance' => $newBalance
                )
            );
            if(!$this->Awallet->save($walletSave, false))
            {
                throw new Exception(__('Unable to update wallet balance.'));
            }

            if(!$this->AwalletHistory->delete($historyId))
            {
                throw new Exception(__('Unable to delete paid commission entry.'));
            }

            if($transactionPrimaryId > 0)
            {
                $this->Ardjournalentry->deleteAll(array('Ardjournalentry.transaction_id' => $transactionPrimaryId), false);
                if(!$this->Ardtransaction->delete($transactionPrimaryId))
                {
                    throw new Exception(__('Unable to delete linked transaction.'));
                }
            }

            if($sourceLedgerId > 0)
            {
                $this->_recalculateArdLedgerBalanceLocal($sourceLedgerId);
            }
            if($expenseLedgerId > 0 && $expenseLedgerId !== $sourceLedgerId)
            {
                $this->_recalculateArdLedgerBalanceLocal($expenseLedgerId);
            }

            if($refNo !== '')
            {
                $expenseRows = (array)$this->Expense->find('all', array(
                    'fields' => array('Expense.id'),
                    'conditions' => array(
                        'Expense.project_id' => $projectId,
                        'Expense.remarks LIKE' => '%Ref: '.$refNo.'%'
                    ),
                    'recursive' => -1
                ));
                foreach((array)$expenseRows as $expenseRow)
                {
                    $expenseId = isset($expenseRow['Expense']['id']) ? (int)$expenseRow['Expense']['id'] : 0;
                    if($expenseId <= 0)
                    {
                        continue;
                    }
                    $this->ExpensesPayment->deleteAll(array('ExpensesPayment.expense_id' => $expenseId), false);
                    $this->Expense->delete($expenseId);
                }
            }

            $ds->commit();
            return array('ok' => true);
        }
        catch(Exception $e)
        {
            $ds->rollback();
            return array('ok' => false, 'message' => $e->getMessage());
        }
    }
    protected function _saveCommissionPayout($agentId, $payoutData = array(), $allowedProjectIds = array())
    {
        $agentId = (int)$agentId;
        $payoutData = (array)$payoutData;
        $allowedProjectIds = array_values(array_unique(array_filter(array_map('intval', (array)$allowedProjectIds))));
        if($agentId <= 0)
        {
            return array('ok' => false, 'message' => __('Invalid agent.'));
        }
        if(!$this->_canManageCommissionPayout())
        {
            return array('ok' => false, 'message' => __('No Permission!'));
        }
        $projectId = isset($payoutData['project_id']) ? (int)$payoutData['project_id'] : 0;
        $sourceLedgerId = isset($payoutData['ledger_id']) ? (int)$payoutData['ledger_id'] : 0;
        $paymentTypeId = isset($payoutData['paymenttype_id']) ? (int)$payoutData['paymenttype_id'] : 0;
        $amount = round((float)(isset($payoutData['amount']) ? $payoutData['amount'] : 0), 2);
        $entryDate = $this->_normalizeYmdDate(isset($payoutData['entry_date']) ? $payoutData['entry_date'] : null);
        if($entryDate === null)
        {
            $entryDate = date('Y-m-d');
        }
        $remarks = trim((string)(isset($payoutData['remarks']) ? $payoutData['remarks'] : ''));
        if($projectId <= 0 || !in_array($projectId, $allowedProjectIds, true))
        {
            return array('ok' => false, 'message' => __('Please select valid agency.'));
        }
        if($sourceLedgerId <= 0)
        {
            return array('ok' => false, 'message' => __('Please select source account.'));
        }
        if($amount <= 0)
        {
            return array('ok' => false, 'message' => __('Please enter valid amount.'));
        }
        $this->loadModel('User');
        $this->loadModel('Admin.Ardledger');
        $this->loadModel('Admin.Ardtransaction');
        $this->loadModel('Admin.Ardjournalentry');
        $this->loadModel('AwalletHistory');
        $this->loadModel('Expense');
        $this->loadModel('ExpensesPayment');
        $this->loadModel('Paymenttype');

        $agentRow = (array)$this->User->find('first', array(
            'fields' => array('User.id','User.name','User.project_id'),
            'conditions' => array('User.id' => $agentId, 'User.deleted IS NULL'),
            'recursive' => -1
        ));
        if(empty($agentRow['User']['id']))
        {
            return array('ok' => false, 'message' => __('Agent not found.'));
        }
        if(method_exists($this->User, 'userHasProjectAccess') && !$this->User->userHasProjectAccess($agentId, $projectId))
        {
            return array('ok' => false, 'message' => __('Selected agent is not assigned to this agency.'));
        }
        $sourceLedger = (array)$this->Ardledger->find('first', array(
            'fields' => array('Ardledger.id','Ardledger.project_id','Ardledger.name','Ardledger.type','Ardledger.is_trashed'),
            'conditions' => array('Ardledger.id' => $sourceLedgerId),
            'recursive' => -1
        ));
        if(empty($sourceLedger['Ardledger']['id']) || (int)$sourceLedger['Ardledger']['project_id'] !== $projectId || (int)$sourceLedger['Ardledger']['is_trashed'] !== 0)
        {
            return array('ok' => false, 'message' => __('Invalid source account selected.'));
        }
        if(strtolower(trim((string)$sourceLedger['Ardledger']['type'])) === 'expense')
        {
            return array('ok' => false, 'message' => __('Source account must be Bank/Asset.'));
        }
        if($paymentTypeId <= 0)
        {
            $paymentTypeId = (int)$this->Paymenttype->field('id', array('Paymenttype.id >' => 0));
        }
        if($paymentTypeId <= 0)
        {
            return array('ok' => false, 'message' => __('Please create payment type first.'));
        }
        $expenseCategoryId = (int)$this->_ensurePaidCommissionExpenseCategory();
        if($expenseCategoryId <= 0)
        {
            return array('ok' => false, 'message' => __('Unable to prepare Paid Commission expense category.'));
        }
        $vendorId = (int)$this->_ensurePaidCommissionVendor($projectId);
        if($vendorId <= 0)
        {
            return array('ok' => false, 'message' => __('Unable to prepare Paid Commission vendor.'));
        }
        $expenseLedgerId = (int)$this->_ardGetOrCreateLedger($projectId, 'Agent Commission Payable', 'liability', 'Auto Business Payable');
        if($expenseLedgerId <= 0)
        {
            return array('ok' => false, 'message' => __('Unable to prepare Agent Commission Payable account.'));
        }

        $voucherRefNo = $this->_nextWalletCommissionVoucherRefNo($projectId);
        $agentName = trim((string)$agentRow['User']['name']);
        $sourceLedgerName = trim((string)$sourceLedger['Ardledger']['name']);
        $description = 'Paid Commission to '.($agentName !== '' ? $agentName : ('Agent #'.$agentId)).' from '.($sourceLedgerName !== '' ? $sourceLedgerName : ('Ledger #'.$sourceLedgerId));
        if($remarks !== '')
        {
            $description .= ' | '.$remarks;
        }
        if($voucherRefNo !== '')
        {
            $description .= ' | Ref: '.$voucherRefNo;
        }
        $walletRemarks = substr($description, 0, 100);

        $ds = $this->AwalletHistory->getDataSource();
        $ds->begin();
        try
        {
            if(!$this->CustomFunction->awalletInsert($agentId, $amount, 'DR', $walletRemarks, null, true))
            {
                throw new Exception(__('Unable to debit agent wallet.'));
            }
            $walletHistoryRow = (array)$this->AwalletHistory->find('first', array(
                'fields' => array('AwalletHistory.id','AwalletHistory.project_id'),
                'conditions' => array(
                    'AwalletHistory.user_id' => $agentId,
                    'AwalletHistory.type' => 'DR',
                    'AwalletHistory.remarks' => $walletRemarks
                ),
                'order' => array('AwalletHistory.id' => 'desc'),
                'recursive' => -1
            ));
            if(!empty($walletHistoryRow['AwalletHistory']['id']) && (int)$walletHistoryRow['AwalletHistory']['project_id'] !== $projectId)
            {
                $this->AwalletHistory->updateAll(
                    array('AwalletHistory.project_id' => (int)$projectId),
                    array('AwalletHistory.id' => (int)$walletHistoryRow['AwalletHistory']['id'])
                );
            }

            $this->Ardtransaction->create();
            $txSaved = $this->Ardtransaction->save(array('Ardtransaction' => array(
                'project_id' => $projectId,
                'ledger_id' => $sourceLedgerId,
                'expense_ledger_id' => $expenseLedgerId,
                'type' => 'expense',
                'status' => 'Posted',
                'amount' => $amount,
                'description' => $description,
                'ref_no' => $voucherRefNo,
                'entry_date' => $entryDate,
                'construction_site_id' => 0,
                'source_type' => 'agent_commission_payout',
                'source_id' => !empty($walletHistoryRow['AwalletHistory']['id']) ? (int)$walletHistoryRow['AwalletHistory']['id'] : 0,
                'auto_posted' => 1,
                'posted_by' => (int)$this->luserId,
                'posted_at' => date('Y-m-d H:i:s'),
                'created_by' => (int)$this->luserId,
                'modified_by' => (int)$this->luserId
            )), false);
            if(!$txSaved)
            {
                throw new Exception(__('Unable to save expense voucher.'));
            }
            $transactionId = (int)$this->Ardtransaction->id;
            $this->_recalculateArdLedgerBalanceLocal($sourceLedgerId);
            $this->_recalculateArdLedgerBalanceLocal($expenseLedgerId);
            $this->_syncWalletCommissionJournalEntry($transactionId);

            $invoiceNo = 'PCOM-'.$projectId.'-'.date('YmdHis');
            $this->Expense->create();
            $expenseSaved = $this->Expense->save(array('Expense' => array(
                'project_id' => $projectId,
                'expense_category_id' => $expenseCategoryId,
                'vendor_id' => $vendorId,
                'invoice_no' => $invoiceNo,
                'invoice_date' => $entryDate,
                'invoice_amount' => $amount,
                'remarks' => $description
            )), false);
            if(!$expenseSaved)
            {
                throw new Exception(__('Unable to create expense record.'));
            }
            $expenseId = (int)$this->Expense->id;
            $this->ExpensesPayment->create();
            $paymentSaved = $this->ExpensesPayment->save(array('ExpensesPayment' => array(
                'expense_id' => $expenseId,
                'paymenttype_id' => $paymentTypeId,
                'amount' => $amount,
                'entry_type' => 'debit',
                'date' => $entryDate,
                'remarks' => substr('Commission payout | Voucher '.$voucherRefNo, 0, 255)
            )), false);
            if(!$paymentSaved)
            {
                throw new Exception(__('Unable to create expense payment record.'));
            }

            $ds->commit();
            return array(
                'ok' => true,
                'voucher_ref' => $voucherRefNo,
                'transaction_id' => $transactionId,
                'expense_id' => $expenseId
            );
        }
        catch(Exception $e)
        {
            $ds->rollback();
            return array('ok' => false, 'message' => $e->getMessage());
        }
    }
    protected function _exportDealCommissionCsv($agentId,$rows,$selectedProjectId=0,$startDate=null,$endDate=null)
    {
        $agentId = (int)$agentId;
        $rows = (array)$rows;
        $selectedProjectId = (int)$selectedProjectId;
        $startDate = $this->_normalizeYmdDate($startDate);
        $endDate = $this->_normalizeYmdDate($endDate);
        $csvHandle = fopen('php://temp', 'r+');
        fputcsv($csvHandle, array('Agent Wallet - Deal-wise Commission Details'));
        fputcsv($csvHandle, array('Agent ID', $agentId));
        if($selectedProjectId > 0)
        {
            fputcsv($csvHandle, array('Agency ID', $selectedProjectId));
        }
        if($startDate)
        {
            fputcsv($csvHandle, array('Start Date', $startDate));
        }
        if($endDate)
        {
            fputcsv($csvHandle, array('End Date', $endDate));
        }
        fputcsv($csvHandle, array());
        fputcsv($csvHandle, array('#','Deal ID','Invoice','Deal Date','Client','Property','Agency','Deal Amount','Rule','Expected Commission','Installment Count','Installment Paid Amount','Wallet Credited','Credit Entries','Last Credit Date','Status'));
        $sr = 1;
        $totalDealAmount = 0.0;
        $totalExpected = 0.0;
        $totalInstallmentCount = 0;
        $totalInstallmentAmount = 0.0;
        $totalWalletCredited = 0.0;
        foreach($rows as $row)
        {
            $dealAmount = (float)(isset($row['deal_total']) ? $row['deal_total'] : 0);
            $expectedAmount = (float)(isset($row['expected_amount']) ? $row['expected_amount'] : 0);
            $installmentCount = (int)(isset($row['payment_count']) ? $row['payment_count'] : 0);
            $installmentPaidAmount = (float)(isset($row['paid_amount']) ? $row['paid_amount'] : 0);
            $creditedAmount = (float)(isset($row['credited_amount']) ? $row['credited_amount'] : 0);
            $totalDealAmount += $dealAmount;
            $totalExpected += $expectedAmount;
            $totalInstallmentCount += $installmentCount;
            $totalInstallmentAmount += $installmentPaidAmount;
            $totalWalletCredited += $creditedAmount;
            $ruleText = '-';
            $ruleValue = (float)(isset($row['commission_rule']) ? $row['commission_rule'] : 0);
            $ruleType = strtolower(trim((string)(isset($row['commission_type']) ? $row['commission_type'] : '')));
            if($ruleValue > 0)
            {
                if($ruleType === 'percentage')
                {
                    $ruleText = number_format($ruleValue,2,'.','').'%';
                }
                else
                {
                    $ruleText = number_format($ruleValue,2,'.','');
                }
            }
            fputcsv($csvHandle, array(
                $sr++,
                (int)(isset($row['deal_id']) ? $row['deal_id'] : 0),
                (string)(isset($row['invoice_no']) ? $row['invoice_no'] : ''),
                (string)(isset($row['deal_date']) ? $row['deal_date'] : ''),
                (string)(isset($row['client_name']) ? $row['client_name'] : ''),
                (string)(isset($row['property_name']) ? $row['property_name'] : ''),
                (string)(isset($row['project_name']) ? $row['project_name'] : ''),
                number_format($dealAmount,2,'.',''),
                $ruleText,
                number_format($expectedAmount,2,'.',''),
                $installmentCount,
                number_format($installmentPaidAmount,2,'.',''),
                number_format($creditedAmount,2,'.',''),
                (int)(isset($row['history_count']) ? $row['history_count'] : 0),
                (string)(isset($row['last_credit_date']) ? $row['last_credit_date'] : ''),
                (string)(isset($row['status']) ? $row['status'] : '')
            ));
        }
        fputcsv($csvHandle, array(
            '',
            '',
            '',
            '',
            '',
            'Totals',
            '',
            number_format($totalDealAmount,2,'.',''),
            '',
            number_format($totalExpected,2,'.',''),
            $totalInstallmentCount,
            number_format($totalInstallmentAmount,2,'.',''),
            number_format($totalWalletCredited,2,'.',''),
            '',
            '',
            'Deals: '.count($rows)
        ));
        rewind($csvHandle);
        $csvOutput = stream_get_contents($csvHandle);
        fclose($csvHandle);
        $filename = 'agent_wallet_deal_commission_'.$agentId.'_'.date('Ymd_His').'.csv';
        $this->autoRender = false;
        $this->response->type('csv');
        $this->response->download($filename);
        $this->response->body($csvOutput);
        return $this->response;
    }
    protected function _extractDealIdFromCommissionRemarks($remarks)
    {
        $remarks = trim((string)$remarks);
        if($remarks==='')
        {
            return 0;
        }
        if(preg_match('/deal\s*commission\s*#\s*(\d+)/i',$remarks,$matches))
        {
            return (int)$matches[1];
        }
        if(preg_match('/deal\s*#\s*(\d+)/i',$remarks,$matches))
        {
            return (int)$matches[1];
        }
        return 0;
    }
    protected function _isAutoCommissionHistoryRemarks($remarks)
    {
        $remarks = strtolower(trim((string)$remarks));
        if($remarks === '')
        {
            return false;
        }
        return (
            strpos($remarks,'added for generating commission') !== false ||
            strpos($remarks,'deal commission #') !== false ||
            strpos($remarks,'deal commission credited at deal creation') !== false ||
            strpos($remarks,'deal commission moved to new agent') !== false ||
            strpos($remarks,'commission credited from deal') !== false
        );
    }
    protected function _walletCommissionDisplayRemarkText($remarks)
    {
        $remarks = trim((string)$remarks);
        if($remarks === '')
        {
            return $remarks;
        }
        if($this->_isAutoCommissionHistoryRemarks($remarks))
        {
            return '(Deal Commission)';
        }
        return $remarks;
    }
    protected function _decorateWalletHistoryRemarks($records = array())
    {
        $records = (array)$records;
        if(empty($records))
        {
            return $records;
        }

        $dealIds = array();
        $paymentIds = array();
        $unresolvedRows = array();
        foreach($records as $index => $row)
        {
            $history = isset($row['AwalletHistory']) ? (array)$row['AwalletHistory'] : array();
            $remarks = isset($history['remarks']) ? trim((string)$history['remarks']) : '';
            $records[$index]['AwalletHistory']['remarks_display'] = $this->_walletCommissionDisplayRemarkText($remarks);
            if(!$this->_isAutoCommissionHistoryRemarks($remarks))
            {
                continue;
            }
            $dealId = $this->_extractDealIdFromCommissionRemarks($remarks);
            if($dealId > 0)
            {
                $dealIds[$dealId] = $dealId;
                continue;
            }
            $paymentId = !empty($history['deals_payment_id']) ? (int)$history['deals_payment_id'] : 0;
            if($paymentId > 0)
            {
                $paymentIds[$paymentId] = $paymentId;
                continue;
            }
            $unresolvedRows[$index] = array(
                'user_id' => !empty($history['user_id']) ? (int)$history['user_id'] : (!empty($row['User']['id']) ? (int)$row['User']['id'] : 0),
                'amount' => isset($history['amount']) ? round((float)$history['amount'],2) : 0.0,
                'date' => !empty($history['date']) ? (string)$history['date'] : ''
            );
        }

        $paymentDealMap = array();
        if(!empty($paymentIds))
        {
            $this->loadModel('DealsPayment');
            $paymentRows = (array)$this->DealsPayment->find('all', array(
                'fields' => array('DealsPayment.id','DealsPayment.deal_id'),
                'conditions' => array('DealsPayment.id' => array_values($paymentIds)),
                'recursive' => -1
            ));
            foreach($paymentRows as $paymentRow)
            {
                $paymentId = !empty($paymentRow['DealsPayment']['id']) ? (int)$paymentRow['DealsPayment']['id'] : 0;
                $dealId = !empty($paymentRow['DealsPayment']['deal_id']) ? (int)$paymentRow['DealsPayment']['deal_id'] : 0;
                if($paymentId > 0 && $dealId > 0)
                {
                    $paymentDealMap[$paymentId] = $dealId;
                    $dealIds[$dealId] = $dealId;
                }
            }
        }

        $candidateAgentIds = array();
        foreach($unresolvedRows as $meta)
        {
            if($meta['user_id'] > 0)
            {
                $candidateAgentIds[$meta['user_id']] = $meta['user_id'];
            }
        }

        $dealMetaMap = array();
        $candidateDealsByAgent = array();
        if(!empty($dealIds) || !empty($candidateAgentIds))
        {
            $this->loadModel('Deal');
            $conditions = array();
            if(!empty($dealIds) && !empty($candidateAgentIds))
            {
                $conditions['OR'] = array(
                    array('Deal.id' => array_values($dealIds)),
                    array('Deal.user_id' => array_values($candidateAgentIds))
                );
            }
            elseif(!empty($dealIds))
            {
                $conditions['Deal.id'] = array_values($dealIds);
            }
            else
            {
                $conditions['Deal.user_id'] = array_values($candidateAgentIds);
            }
            $dealRows = (array)$this->Deal->find('all', array(
                'joins' => array(
                    array('table'=>'properties','alias'=>'Property','type'=>'LEFT','conditions'=>array('Property.id=Deal.property_id')),
                    array('table'=>'clients','alias'=>'Client','type'=>'LEFT','conditions'=>array('Client.id=Deal.client_id'))
                ),
                'fields' => array(
                    'Deal.id','Deal.user_id','Deal.date','Deal.created','Deal.total_amount','Deal.invoice_no',
                    'Property.name','Property.agent_commission','Property.commission_type',
                    'Client.name'
                ),
                'conditions' => $conditions,
                'recursive' => -1
            ));
            foreach($dealRows as $dealRow)
            {
                $deal = isset($dealRow['Deal']) ? (array)$dealRow['Deal'] : array();
                $property = isset($dealRow['Property']) ? (array)$dealRow['Property'] : array();
                $client = isset($dealRow['Client']) ? (array)$dealRow['Client'] : array();
                $dealId = !empty($deal['id']) ? (int)$deal['id'] : 0;
                if($dealId <= 0)
                {
                    continue;
                }
                $propertyName = !empty($property['name']) ? trim((string)$property['name']) : '';
                $clientName = !empty($client['name']) ? trim((string)$client['name']) : '';
                $detailText = trim($propertyName.($propertyName !== '' && $clientName !== '' ? ' | ' : '').$clientName);
                $commissionRule = isset($property['agent_commission']) ? (float)$property['agent_commission'] : 0.0;
                $commissionType = strtolower(trim((string)(isset($property['commission_type']) ? $property['commission_type'] : '')));
                $dealTotal = isset($deal['total_amount']) ? (float)$deal['total_amount'] : 0.0;
                $expectedAmount = 0.0;
                if($commissionRule > 0)
                {
                    if($commissionType === 'percentage')
                    {
                        $expectedAmount = round(($commissionRule * $dealTotal) / 100, 2);
                    }
                    elseif($commissionType === 'value' || $commissionType === 'fixed')
                    {
                        $expectedAmount = round($commissionRule, 2);
                    }
                }
                $dealMetaMap[$dealId] = array(
                    'detail_text' => $detailText,
                    'invoice_no' => !empty($deal['invoice_no']) ? (string)$deal['invoice_no'] : '',
                    'user_id' => !empty($deal['user_id']) ? (int)$deal['user_id'] : 0,
                    'expected_amount' => $expectedAmount,
                    'anchor_date' => !empty($deal['created']) ? (string)$deal['created'] : (!empty($deal['date']) ? (string)$deal['date'].' 00:00:00' : '')
                );
                $agentId = !empty($deal['user_id']) ? (int)$deal['user_id'] : 0;
                if($agentId > 0)
                {
                    if(!isset($candidateDealsByAgent[$agentId]))
                    {
                        $candidateDealsByAgent[$agentId] = array();
                    }
                    $candidateDealsByAgent[$agentId][] = $dealId;
                }
            }
        }

        foreach($records as $index => $row)
        {
            $history = isset($row['AwalletHistory']) ? (array)$row['AwalletHistory'] : array();
            $remarks = isset($history['remarks']) ? trim((string)$history['remarks']) : '';
            if(!$this->_isAutoCommissionHistoryRemarks($remarks))
            {
                continue;
            }
            $dealId = $this->_extractDealIdFromCommissionRemarks($remarks);
            $paymentId = !empty($history['deals_payment_id']) ? (int)$history['deals_payment_id'] : 0;
            if($dealId <= 0 && $paymentId > 0 && !empty($paymentDealMap[$paymentId]))
            {
                $dealId = (int)$paymentDealMap[$paymentId];
            }
            if($dealId <= 0 && !empty($unresolvedRows[$index]))
            {
                $meta = $unresolvedRows[$index];
                $agentId = (int)$meta['user_id'];
                $bestDealId = 0;
                $bestDistance = null;
                if(!empty($candidateDealsByAgent[$agentId]))
                {
                    foreach((array)$candidateDealsByAgent[$agentId] as $candidateDealId)
                    {
                        if(empty($dealMetaMap[$candidateDealId]))
                        {
                            continue;
                        }
                        $candidate = $dealMetaMap[$candidateDealId];
                        if(abs((float)$candidate['expected_amount'] - (float)$meta['amount']) > 0.009)
                        {
                            continue;
                        }
                        $historyTs = !empty($meta['date']) ? strtotime($meta['date']) : false;
                        $dealTs = !empty($candidate['anchor_date']) ? strtotime($candidate['anchor_date']) : false;
                        $distance = ($historyTs !== false && $dealTs !== false) ? abs($historyTs - $dealTs) : PHP_INT_MAX;
                        if($bestDistance === null || $distance < $bestDistance)
                        {
                            $bestDistance = $distance;
                            $bestDealId = (int)$candidateDealId;
                        }
                    }
                }
                if($bestDealId > 0)
                {
                    $dealId = $bestDealId;
                }
            }
            if($dealId > 0 && !empty($dealMetaMap[$dealId]['detail_text']))
            {
                $records[$index]['AwalletHistory']['remarks_display'] = $dealMetaMap[$dealId]['detail_text']."\n".$this->_walletCommissionDisplayRemarkText($remarks);
            }
        }

        return $records;
    }
    protected function _buildAgentDealCommissionDetails($agentId,$dealScopeCondition=array())
    {
        $agentId=(int)$agentId;
        if($agentId<=0)
        {
            return array();
        }
        $this->loadModel('Deal');
        $this->loadModel('DealsPayment');

        $dealConditions=array('Deal.user_id'=>$agentId);
        if(!empty($dealScopeCondition))
        {
            if(is_array($dealScopeCondition))
            {
                foreach($dealScopeCondition as $scopeCondition)
                {
                    if($scopeCondition !== null && $scopeCondition !== '')
                    {
                        $dealConditions[] = $scopeCondition;
                    }
                }
            }
            else
            {
                $dealConditions[] = $dealScopeCondition;
            }
        }

        $dealRows=(array)$this->Deal->find('all',array(
            'joins'=>array(
                array('table'=>'properties','alias'=>'Property','type'=>'LEFT','conditions'=>array('Property.id=Deal.property_id')),
                array('table'=>'clients','alias'=>'Client','type'=>'LEFT','conditions'=>array('Client.id=Deal.client_id')),
                array('table'=>'projects','alias'=>'Project','type'=>'LEFT','conditions'=>array('Project.id=Property.project_id')),
                array('table'=>'users','alias'=>'Agent','type'=>'LEFT','conditions'=>array('Agent.id=Deal.user_id'))
            ),
            'fields'=>array(
                'Deal.id','Deal.invoice_no','Deal.date','Deal.total_amount','Deal.user_id',
                'Property.id','Property.name','Property.project_id','Property.agent_commission','Property.commission_type',
                'Client.name','Project.name','Agent.name'
            ),
            'conditions'=>$dealConditions,
            'order'=>array('Deal.id'=>'desc'),
            'recursive'=>-1
        ));
        if(empty($dealRows))
        {
            return array();
        }

        $dealIds=array();
        foreach($dealRows as $dealRow)
        {
            $dealId=(int)(isset($dealRow['Deal']['id']) ? $dealRow['Deal']['id'] : 0);
            if($dealId>0)
            {
                $dealIds[$dealId]=$dealId;
            }
        }
        if(empty($dealIds))
        {
            return array();
        }

        $paymentAggMap=array();
        $paymentRows=(array)$this->DealsPayment->find('all',array(
            'fields'=>array(
                'DealsPayment.deal_id',
                'COUNT(DealsPayment.id) AS payment_count',
                'IFNULL(SUM(DealsPayment.amount),0) AS paid_amount'
            ),
            'conditions'=>array('DealsPayment.deal_id'=>array_values($dealIds)),
            'group'=>array('DealsPayment.deal_id'),
            'recursive'=>-1
        ));
        foreach($paymentRows as $paymentRow)
        {
            $dealId=(int)(isset($paymentRow['DealsPayment']['deal_id']) ? $paymentRow['DealsPayment']['deal_id'] : 0);
            if($dealId<=0)
            {
                continue;
            }
            $paymentAggMap[$dealId]=array(
                'payment_count'=>(int)(isset($paymentRow[0]['payment_count']) ? $paymentRow[0]['payment_count'] : 0),
                'paid_amount'=>(float)(isset($paymentRow[0]['paid_amount']) ? $paymentRow[0]['paid_amount'] : 0)
            );
        }

        $paymentDealMap=array();
        $paymentLinkRows=(array)$this->DealsPayment->find('all',array(
            'fields'=>array('DealsPayment.id','DealsPayment.deal_id'),
            'conditions'=>array('DealsPayment.deal_id'=>array_values($dealIds)),
            'recursive'=>-1
        ));
        foreach($paymentLinkRows as $paymentLinkRow)
        {
            $paymentId=(int)(isset($paymentLinkRow['DealsPayment']['id']) ? $paymentLinkRow['DealsPayment']['id'] : 0);
            $dealId=(int)(isset($paymentLinkRow['DealsPayment']['deal_id']) ? $paymentLinkRow['DealsPayment']['deal_id'] : 0);
            if($paymentId>0 && $dealId>0)
            {
                $paymentDealMap[$paymentId]=$dealId;
            }
        }

        $dealCreditMap=array();
        $creditRows=(array)$this->AwalletHistory->find('all',array(
            'fields'=>array('AwalletHistory.id','AwalletHistory.deals_payment_id','AwalletHistory.amount','AwalletHistory.date','AwalletHistory.remarks'),
            'conditions'=>array(
                'AwalletHistory.user_id'=>$agentId,
                'AwalletHistory.type'=>'CR',
                'AwalletHistory.amount >'=>0
            ),
            'order'=>array('AwalletHistory.id'=>'asc'),
            'recursive'=>-1
        ));
        foreach($creditRows as $creditRow)
        {
            $historyMeta=(array)$creditRow['AwalletHistory'];
            $dealId=0;
            $remarks=isset($historyMeta['remarks']) ? (string)$historyMeta['remarks'] : '';
            if(stripos(trim($remarks), 'Paid Commission to ') === 0)
            {
                continue;
            }
            $dealId=$this->_extractDealIdFromCommissionRemarks($remarks);
            if($dealId<=0)
            {
                $paymentId=(int)(isset($historyMeta['deals_payment_id']) ? $historyMeta['deals_payment_id'] : 0);
                if($paymentId>0 && isset($paymentDealMap[$paymentId]))
                {
                    $dealId=(int)$paymentDealMap[$paymentId];
                }
            }
            if($dealId<=0 || !isset($dealIds[$dealId]))
            {
                continue;
            }
            if(!isset($dealCreditMap[$dealId]))
            {
                $dealCreditMap[$dealId]=array(
                    'credited_amount'=>0.0,
                    'history_count'=>0,
                    'last_credit_date'=>null
                );
            }
            $dealCreditMap[$dealId]['credited_amount']+=(float)(isset($historyMeta['amount']) ? $historyMeta['amount'] : 0);
            $dealCreditMap[$dealId]['history_count']++;
            $historyDate=isset($historyMeta['date']) ? $historyMeta['date'] : null;
            if($historyDate && ($dealCreditMap[$dealId]['last_credit_date']===null || strtotime($historyDate)>strtotime($dealCreditMap[$dealId]['last_credit_date'])))
            {
                $dealCreditMap[$dealId]['last_credit_date']=$historyDate;
            }
        }

        $rows=array();
        foreach($dealRows as $dealRow)
        {
            $dealId=(int)(isset($dealRow['Deal']['id']) ? $dealRow['Deal']['id'] : 0);
            if($dealId<=0)
            {
                continue;
            }
            $dealTotal=(float)(isset($dealRow['Deal']['total_amount']) ? $dealRow['Deal']['total_amount'] : 0);
            $commissionRule=(float)(isset($dealRow['Property']['agent_commission']) ? $dealRow['Property']['agent_commission'] : 0);
            $commissionType=strtolower(trim((string)(isset($dealRow['Property']['commission_type']) ? $dealRow['Property']['commission_type'] : '')));
            $expectedAmount=0.0;
            if($commissionRule>0)
            {
                if($commissionType==='percentage')
                {
                    $expectedAmount=round(($commissionRule*$dealTotal)/100,2);
                }
                elseif($commissionType==='value' || $commissionType==='fixed')
                {
                    $expectedAmount=round($commissionRule,2);
                }
            }
            $creditMeta=isset($dealCreditMap[$dealId]) ? $dealCreditMap[$dealId] : array('credited_amount'=>0.0,'history_count'=>0,'last_credit_date'=>null);
            $paymentMeta=isset($paymentAggMap[$dealId]) ? $paymentAggMap[$dealId] : array('payment_count'=>0,'paid_amount'=>0.0);
            $displayCommissionAmount = ((float)$creditMeta['credited_amount'] > 0) ? round((float)$creditMeta['credited_amount'], 2) : $expectedAmount;

            $status='No Rule';
            if($displayCommissionAmount>0)
            {
                if((float)$creditMeta['credited_amount']>0)
                {
                    $status='Credited';
                }
                else
                {
                    $status='Missing';
                }
            }

            $rows[]=array(
                'deal_id'=>$dealId,
                'invoice_no'=>isset($dealRow['Deal']['invoice_no']) ? $dealRow['Deal']['invoice_no'] : '',
                'deal_date'=>isset($dealRow['Deal']['date']) ? $dealRow['Deal']['date'] : null,
                'agent_name'=>isset($dealRow['Agent']['name']) ? $dealRow['Agent']['name'] : '',
                'client_name'=>isset($dealRow['Client']['name']) ? $dealRow['Client']['name'] : '',
                'property_name'=>isset($dealRow['Property']['name']) ? $dealRow['Property']['name'] : '',
                'project_name'=>isset($dealRow['Project']['name']) ? $dealRow['Project']['name'] : '',
                'deal_total'=>$dealTotal,
                'commission_type'=>$commissionType,
                'commission_rule'=>$commissionRule,
                'expected_amount'=>$displayCommissionAmount,
                'payment_count'=>(int)$paymentMeta['payment_count'],
                'paid_amount'=>(float)$paymentMeta['paid_amount'],
                'credited_amount'=>(float)$creditMeta['credited_amount'],
                'history_count'=>(int)$creditMeta['history_count'],
                'last_credit_date'=>$creditMeta['last_credit_date'],
                'status'=>$status
            );
        }
        return $rows;
    }
    protected function _buildUnifiedCommissionLedgerRows($agentId, $dealCommissionDetails = array(), $paidCommissionRecords = array(), $selectedProjectId = 0, $startDate = null, $endDate = null)
    {
        $agentId = (int)$agentId;
        if($agentId <= 0)
        {
            return array();
        }

        $detailMap = array();
        foreach((array)$dealCommissionDetails as $detailRow)
        {
            $dealId = isset($detailRow['deal_id']) ? (int)$detailRow['deal_id'] : 0;
            if($dealId > 0)
            {
                $detailMap[$dealId] = $detailRow;
            }
        }

        $creditConditions = array(
            'AwalletHistory.user_id' => $agentId,
            'AwalletHistory.type' => 'CR',
            'AwalletHistory.amount >' => 0
        );
        if((int)$selectedProjectId > 0)
        {
            $creditConditions['AwalletHistory.project_id'] = (int)$selectedProjectId;
        }
        $normalizedStart = $this->_normalizeYmdDate($startDate);
        $normalizedEnd = $this->_normalizeYmdDate($endDate);
        if($normalizedStart !== null)
        {
            $creditConditions['AwalletHistory.date >='] = $normalizedStart.' 00:00:00';
        }
        if($normalizedEnd !== null)
        {
            $creditConditions['AwalletHistory.date <='] = $normalizedEnd.' 23:59:59';
        }

        $creditHistoryRows = (array)$this->AwalletHistory->find('all', array(
            'joins' => array(
                array('table' => 'projects', 'alias' => 'Project', 'type' => 'LEFT', 'conditions' => array('Project.id=AwalletHistory.project_id'))
            ),
            'fields' => array(
                'AwalletHistory.id',
                'AwalletHistory.project_id',
                'AwalletHistory.deals_payment_id',
                'AwalletHistory.amount',
                'AwalletHistory.date',
                'AwalletHistory.remarks',
                'Project.name'
            ),
            'conditions' => $creditConditions,
            'order' => array('AwalletHistory.date' => 'asc', 'AwalletHistory.id' => 'asc'),
            'recursive' => -1
        ));

        $paymentIds = array();
        foreach((array)$creditHistoryRows as $creditHistoryRow)
        {
            $creditMeta = isset($creditHistoryRow['AwalletHistory']) ? (array)$creditHistoryRow['AwalletHistory'] : array();
            $remarks = isset($creditMeta['remarks']) ? trim((string)$creditMeta['remarks']) : '';
            if($remarks !== '' && stripos($remarks, 'Paid Commission to ') === 0)
            {
                continue;
            }
            $paymentId = isset($creditMeta['deals_payment_id']) ? (int)$creditMeta['deals_payment_id'] : 0;
            if($paymentId > 0)
            {
                $paymentIds[$paymentId] = $paymentId;
            }
        }

        $paymentDealMap = array();
        if(!empty($paymentIds))
        {
            $this->loadModel('DealsPayment');
            $paymentRows = (array)$this->DealsPayment->find('all', array(
                'fields' => array('DealsPayment.id','DealsPayment.deal_id'),
                'conditions' => array('DealsPayment.id' => array_values($paymentIds)),
                'recursive' => -1
            ));
            foreach((array)$paymentRows as $paymentRow)
            {
                $paymentId = isset($paymentRow['DealsPayment']['id']) ? (int)$paymentRow['DealsPayment']['id'] : 0;
                $dealId = isset($paymentRow['DealsPayment']['deal_id']) ? (int)$paymentRow['DealsPayment']['deal_id'] : 0;
                if($paymentId > 0 && $dealId > 0)
                {
                    $paymentDealMap[$paymentId] = $dealId;
                }
            }
        }

        $ledgerRows = array();
        foreach((array)$creditHistoryRows as $creditHistoryRow)
        {
            $creditMeta = isset($creditHistoryRow['AwalletHistory']) ? (array)$creditHistoryRow['AwalletHistory'] : array();
            $remarks = isset($creditMeta['remarks']) ? trim((string)$creditMeta['remarks']) : '';
            if($remarks !== '' && stripos($remarks, 'Paid Commission to ') === 0)
            {
                continue;
            }
            $dealId = $this->_extractDealIdFromCommissionRemarks($remarks);
            $paymentId = isset($creditMeta['deals_payment_id']) ? (int)$creditMeta['deals_payment_id'] : 0;
            if($dealId <= 0 && $paymentId > 0 && isset($paymentDealMap[$paymentId]))
            {
                $dealId = (int)$paymentDealMap[$paymentId];
            }
            $detail = ($dealId > 0 && isset($detailMap[$dealId])) ? (array)$detailMap[$dealId] : array();
            $projectName = !empty($detail['project_name']) ? (string)$detail['project_name'] : (isset($creditHistoryRow['Project']['name']) ? (string)$creditHistoryRow['Project']['name'] : '');
            $reference = !empty($detail['invoice_no']) ? (string)$detail['invoice_no'] : ($dealId > 0 ? 'Deal #'.$dealId : __('Wallet Credit'));
            $detailTextParts = array();
            if(!empty($detail['property_name']))
            {
                $detailTextParts[] = (string)$detail['property_name'];
            }
            if(!empty($detail['client_name']))
            {
                $detailTextParts[] = (string)$detail['client_name'];
            }
            $detailText = !empty($detailTextParts) ? implode(' | ', $detailTextParts) : ($remarks !== '' ? $remarks : __('Commission credited from deal'));
            $ledgerRows[] = array(
                'row_type' => 'credit',
                'history_id' => isset($creditMeta['id']) ? (int)$creditMeta['id'] : 0,
                'project_id' => isset($creditMeta['project_id']) ? (int)$creditMeta['project_id'] : 0,
                'project_name' => $projectName,
                'entry_date' => isset($creditMeta['date']) ? (string)$creditMeta['date'] : '',
                'reference' => $reference,
                'detail' => $detailText,
                'remarks' => $remarks,
                'credit' => (float)(isset($creditMeta['amount']) ? $creditMeta['amount'] : 0),
                'debit' => 0.0,
                'balance' => 0.0,
                'transaction_id' => 0,
                'voucher_ref' => '',
                'can_print' => false,
                'can_delete' => false,
                'sort_stamp' => ((isset($creditMeta['date']) && strtotime($creditMeta['date']) !== false) ? strtotime($creditMeta['date']) : 0).'-'.str_pad((string)(isset($creditMeta['id']) ? (int)$creditMeta['id'] : 0), 10, '0', STR_PAD_LEFT)
            );
        }

        foreach((array)$paidCommissionRecords as $paidRow)
        {
            $paidMeta = isset($paidRow['AwalletHistory']) ? (array)$paidRow['AwalletHistory'] : array();
            $voucherRef = isset($paidMeta['voucher_ref']) ? trim((string)$paidMeta['voucher_ref']) : '';
            $transactionId = isset($paidMeta['transaction_id']) ? (int)$paidMeta['transaction_id'] : 0;
            $ledgerRows[] = array(
                'row_type' => 'debit',
                'history_id' => isset($paidMeta['id']) ? (int)$paidMeta['id'] : 0,
                'project_id' => isset($paidMeta['project_id']) ? (int)$paidMeta['project_id'] : 0,
                'project_name' => isset($paidRow['Project']['name']) ? (string)$paidRow['Project']['name'] : '',
                'entry_date' => isset($paidMeta['date']) ? (string)$paidMeta['date'] : '',
                'reference' => $voucherRef !== '' ? $voucherRef : ($transactionId > 0 ? 'Voucher #'.$transactionId : __('Payout')),
                'detail' => !empty($paidMeta['remarks']) ? (string)$paidMeta['remarks'] : __('Commission paid to agent'),
                'remarks' => !empty($paidMeta['remarks']) ? (string)$paidMeta['remarks'] : '',
                'credit' => 0.0,
                'debit' => (float)(isset($paidMeta['amount']) ? $paidMeta['amount'] : 0),
                'balance' => 0.0,
                'transaction_id' => $transactionId,
                'voucher_ref' => $voucherRef,
                'can_print' => ($transactionId > 0),
                'can_delete' => false,
                'sort_stamp' => ((isset($paidMeta['date']) && strtotime($paidMeta['date']) !== false) ? strtotime($paidMeta['date']) : 0).'-'.str_pad((string)(isset($paidMeta['id']) ? (int)$paidMeta['id'] : 0), 10, '0', STR_PAD_LEFT)
            );
        }

        usort($ledgerRows, function($a, $b){
            return strcmp((string)$a['sort_stamp'], (string)$b['sort_stamp']);
        });

        $runningBalance = 0.0;
        $latestDebitKey = null;
        foreach($ledgerRows as $index => $ledgerRow)
        {
            $runningBalance += (float)$ledgerRow['credit'];
            $runningBalance -= (float)$ledgerRow['debit'];
            $ledgerRows[$index]['balance'] = round($runningBalance, 2);
            if($ledgerRow['row_type'] === 'debit' && (int)$ledgerRow['history_id'] > 0)
            {
                $latestDebitKey = $index;
            }
        }
        if($latestDebitKey !== null && isset($ledgerRows[$latestDebitKey]))
        {
            $ledgerRows[$latestDebitKey]['can_delete'] = true;
        }

        return $ledgerRows;
    }
    protected function _walletVoucherDefaultTemplateContent()
    {
        return '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>{{voucher_no}}</title><style>body{font-family:Arial,sans-serif;font-size:12px;color:#12263f;margin:0;padding:24px;}table{width:100%;border-collapse:collapse;}th,td{border:1px solid #d8e2ef;padding:8px;text-align:left;vertical-align:top;}th{width:190px;background:#f7faff;color:#17324f;} .head{margin-bottom:18px;text-align:center;} .head h1{margin:0;font-size:24px;} .head p{margin:4px 0 0;color:#5f7288;} .logo{max-height:72px;max-width:120px;margin:0 auto 10px;display:block;}</style></head><body><div class=\"head\">{{agency_logo_img}}<h1>{{company_name}}</h1><p>{{agency_address}} | {{agency_phone}} | {{agency_email}}</p><p>{{voucher_type}}</p></div><table><tr><th>Voucher No</th><td>{{voucher_no}}</td></tr><tr><th>Date</th><td>{{entry_date}}</td></tr><tr><th>Pay To</th><td>{{pay_to}}</td></tr><tr><th>Description</th><td>{{description}}</td></tr><tr><th>Amount</th><td>{{amount_with_currency}}</td></tr><tr><th>Amount In Words</th><td>{{amount_words}}</td></tr><tr><th>Debit</th><td>{{debit_amount}}</td></tr><tr><th>Credit</th><td>{{credit_amount}}</td></tr><tr><th>Reference</th><td>{{reference_no}}</td></tr></table></body></html>';
    }
    protected function _walletRenderVoucherTemplateTokens($templateContent, $tokenMap = array())
    {
        $templateContent = (string)$templateContent;
        if($templateContent === '')
        {
            return '';
        }
        if(strpos($templateContent, '\n') !== false || strpos($templateContent, '\r') !== false)
        {
            $templateContent = str_replace(array('\r\n', '\n', '\r'), array("\n", "\n", "\n"), $templateContent);
        }
        $search = array();
        $replace = array();
        foreach((array)$tokenMap as $key => $value)
        {
            $search[] = '{{' . trim((string)$key) . '}}';
            $replace[] = htmlspecialchars(trim((string)$value), ENT_QUOTES, 'UTF-8');
        }
        return str_replace($search, $replace, $templateContent);
    }
    protected function _walletApplyVoucherDateMonthStyle($html)
    {
        $html = (string)$html;
        if($html === '')
        {
            return $html;
        }
        $html = str_replace('Acc/Balance', 'Acc/From', $html);
        if(stripos($html, 'wv-voucher-date-month-style') !== false)
        {
            return $html;
        }
        $style = '<style id="wv-voucher-date-month-style">.date-box .date-cell:nth-child(3),.date-box .date-cell:nth-child(4){background:rgba(128,128,128,.5)!important;}</style>';
        if(stripos($html, '</head>') !== false)
        {
            return preg_replace('/<\/head>/i', $style.'</head>', $html, 1);
        }
        return $style.$html;
    }
    protected function _walletVoucherCurrencyWord($currencySymbol)
    {
        $symbol = strtoupper(trim(strip_tags((string)$currencySymbol)));
        if(in_array($symbol, array('RS', 'RS.', 'PKR', 'RUPEE', 'RUPEES'), true))
        {
            return 'Rupees';
        }
        if(in_array($symbol, array('$', 'USD'), true))
        {
            return 'Dollars';
        }
        if(in_array($symbol, array('AED', 'DH', 'DIRHAM', 'DIRHAMS'), true))
        {
            return 'Dirhams';
        }
        if($symbol === '')
        {
            return 'Rupees';
        }
        return ucwords(strtolower($symbol));
    }
    protected function _walletVoucherNumberBelowThousandInWords($number)
    {
        $number = (int)$number;
        $ones = array(0 => '',1 => 'One',2 => 'Two',3 => 'Three',4 => 'Four',5 => 'Five',6 => 'Six',7 => 'Seven',8 => 'Eight',9 => 'Nine',10 => 'Ten',11 => 'Eleven',12 => 'Twelve',13 => 'Thirteen',14 => 'Fourteen',15 => 'Fifteen',16 => 'Sixteen',17 => 'Seventeen',18 => 'Eighteen',19 => 'Nineteen');
        $tens = array(2 => 'Twenty',3 => 'Thirty',4 => 'Forty',5 => 'Fifty',6 => 'Sixty',7 => 'Seventy',8 => 'Eighty',9 => 'Ninety');
        $parts = array();
        if($number >= 100)
        {
            $parts[] = $ones[(int)floor($number / 100)] . ' Hundred';
            $number = $number % 100;
        }
        if($number > 0)
        {
            if($number < 20)
            {
                $parts[] = $ones[$number];
            }
            else
            {
                $ten = (int)floor($number / 10);
                $unit = $number % 10;
                $parts[] = trim($tens[$ten] . ($unit > 0 ? ' ' . $ones[$unit] : ''));
            }
        }
        return implode(' ', $parts);
    }
    protected function _walletVoucherIntegerInWords($number)
    {
        $number = (int)$number;
        if($number === 0)
        {
            return 'Zero';
        }
        $labels = array('', 'Thousand', 'Lakh', 'Crore', 'Arab', 'Kharab');
        $groups = array();
        $groups[] = array($number % 1000, $labels[0]);
        $number = (int)floor($number / 1000);
        $labelIndex = 1;
        while($number > 0)
        {
            $groups[] = array($number % 100, isset($labels[$labelIndex]) ? $labels[$labelIndex] : '');
            $number = (int)floor($number / 100);
            $labelIndex++;
        }
        $parts = array();
        for($i = count($groups) - 1; $i >= 0; $i--)
        {
            $chunk = (int)$groups[$i][0];
            if($chunk <= 0)
            {
                continue;
            }
            $chunkWords = $this->_walletVoucherNumberBelowThousandInWords($chunk);
            $label = trim((string)$groups[$i][1]);
            $parts[] = trim($chunkWords . ($label !== '' ? ' ' . $label : ''));
        }
        return implode(' ', $parts);
    }
    protected function _walletVoucherAmountInWords($amount, $currencySymbol = 'Rs')
    {
        $amount = round(abs((float)$amount), 2);
        $whole = (int)floor($amount);
        $fraction = (int)round(($amount - $whole) * 100);
        if($fraction >= 100)
        {
            $whole++;
            $fraction = 0;
        }
        $currencyWord = $this->_walletVoucherCurrencyWord($currencySymbol);
        $words = $this->_walletVoucherIntegerInWords($whole) . ' ' . $currencyWord;
        if($fraction > 0)
        {
            $words .= ' And ' . $this->_walletVoucherIntegerInWords($fraction) . ' Paisa';
        }
        return trim($words) . ' Only';
    }
    protected function _walletAmountFieldValue($walletRow, $field)
    {
        $walletRow = (array)$walletRow;
        $field = trim((string)$field);
        if($field === '')
        {
            return 0.0;
        }
        if(isset($walletRow['Awallet'][$field]))
        {
            return (float)$walletRow['Awallet'][$field];
        }
        if(isset($walletRow[$field]))
        {
            return (float)$walletRow[$field];
        }
        return 0.0;
    }
    protected function _buildAgentWalletSummaryMeta($dealCommissionDetails, $walletRow = array(), $paidCommissionTotal = null)
    {
        $dealCommissionDetails = (array)$dealCommissionDetails;
        $walletRow = (array)$walletRow;
        $totalDeals = count($dealCommissionDetails);
        $totalDealAmount = 0.0;
        $totalInstallmentCount = 0;
        $totalInstallmentPaid = 0.0;
        $totalExpectedCommission = 0.0;
        $totalWalletCredited = 0.0;
        $creditedDeals = 0;
        $missingDeals = 0;
        $agencyLabels = array();
        foreach($dealCommissionDetails as $detail)
        {
            $totalDealAmount += (float)(isset($detail['deal_total']) ? $detail['deal_total'] : 0);
            $totalInstallmentCount += (int)(isset($detail['payment_count']) ? $detail['payment_count'] : 0);
            $totalInstallmentPaid += (float)(isset($detail['paid_amount']) ? $detail['paid_amount'] : 0);
            $totalExpectedCommission += (float)(isset($detail['expected_amount']) ? $detail['expected_amount'] : 0);
            $totalWalletCredited += (float)(isset($detail['credited_amount']) ? $detail['credited_amount'] : 0);
            $status = trim((string)(isset($detail['status']) ? $detail['status'] : ''));
            if($status === 'Credited')
            {
                $creditedDeals++;
            }
            elseif($status === 'Missing')
            {
                $missingDeals++;
            }
            $agencyLabel = trim((string)(isset($detail['project_name']) ? $detail['project_name'] : ''));
            if($agencyLabel !== '')
            {
                $agencyLabels[$agencyLabel] = $agencyLabel;
            }
        }
        if($paidCommissionTotal === null)
        {
            $paidCommissionTotal = $this->_walletAmountFieldValue($walletRow, 'debit');
        }
        $walletBalance = $this->_walletAmountFieldValue($walletRow, 'balance');
        $walletCredit = $this->_walletAmountFieldValue($walletRow, 'credit');
        $walletDebit = $this->_walletAmountFieldValue($walletRow, 'debit');
        return array(
            'total_deals' => $totalDeals,
            'total_deal_amount' => round($totalDealAmount, 2),
            'total_installment_count' => $totalInstallmentCount,
            'total_installment_paid' => round($totalInstallmentPaid, 2),
            'total_expected_commission' => round($totalExpectedCommission, 2),
            'total_wallet_credited' => round($totalWalletCredited, 2),
            'total_paid_commission' => round((float)$paidCommissionTotal, 2),
            'wallet_balance' => round($walletBalance, 2),
            'wallet_credit_total' => round($walletCredit, 2),
            'wallet_debit_total' => round($walletDebit, 2),
            'commission_due' => round(max($walletBalance, 0), 2),
            'credited_deals' => $creditedDeals,
            'missing_deals' => $missingDeals,
            'agency_labels' => array_values($agencyLabels),
            'agency_summary' => implode(', ', array_values($agencyLabels))
        );
    }
    protected function _buildWalletAgentCards($walletCondition = array())
    {
        $this->loadModel('Awallet');
        $rows = (array)$this->Awallet->find('all', array(
            'joins' => array(
                array('table' => 'users', 'alias' => 'User', 'type' => 'LEFT', 'conditions' => array('User.id=Awallet.user_id')),
                array('table' => 'projects', 'alias' => 'Project', 'type' => 'LEFT', 'conditions' => array('Project.id=Awallet.project_id'))
            ),
            'fields' => array(
                'Awallet.id','Awallet.user_id','Awallet.project_id','Awallet.credit','Awallet.debit','Awallet.balance',
                'User.id','User.name','User.mobile','User.phone','User.photo','User.project_id',
                'Project.id','Project.name','Project.phone','Project.mobile','Project.email'
            ),
            'conditions' => array($walletCondition),
            'order' => array('User.name' => 'asc', 'Awallet.id' => 'desc'),
            'recursive' => -1
        ));
        $cards = array();
        foreach($rows as $row)
        {
            $agentId = isset($row['Awallet']['user_id']) ? (int)$row['Awallet']['user_id'] : 0;
            if($agentId <= 0)
            {
                continue;
            }
            $dealScope = $this->CustomFunction->userWisePermission($agentId,$this->luserId,$this->userType,$this->agencyId,'Property.project_id','Deal.user_id',2);
            $dealCommissionDetails = $this->_buildAgentDealCommissionDetails($agentId, !empty($dealScope) ? array($dealScope) : array());
            $summary = $this->_buildAgentWalletSummaryMeta($dealCommissionDetails, $row);
            $cards[] = array(
                'agent_id' => $agentId,
                'agent_name' => isset($row['User']['name']) ? (string)$row['User']['name'] : '',
                'agent_mobile' => !empty($row['User']['mobile']) ? (string)$row['User']['mobile'] : (!empty($row['User']['phone']) ? (string)$row['User']['phone'] : ''),
                'agent_photo' => isset($row['User']['photo']) ? (string)$row['User']['photo'] : '',
                'project_id' => isset($row['Awallet']['project_id']) ? (int)$row['Awallet']['project_id'] : 0,
                'agency_name' => isset($row['Project']['name']) ? (string)$row['Project']['name'] : '',
                'agency_phone' => !empty($row['Project']['mobile']) ? (string)$row['Project']['mobile'] : (!empty($row['Project']['phone']) ? (string)$row['Project']['phone'] : ''),
                'agency_email' => isset($row['Project']['email']) ? (string)$row['Project']['email'] : '',
                'summary' => $summary
            );
        }
        return $cards;
    }
    public function beforeFilter()
    {
        $this->_ensureSimpleRootAdminPage(array(
            'model_name' => 'AwalletHistory',
            'page_name' => 'Agent Wallet Print',
            'controller_name' => 'AwalletHistories',
            'action_name' => 'printentry',
            'icon' => 'fa fa-print',
            'ordering' => 99,
            'published' => 'No',
            'sel_name' => 'awallet_histories,agents,ardaccounts',
            'grant_group_types' => array('ADR','AGY')
        ));
        parent::beforeFilter();
    }
    public function index($id=null)
    {
	$condition=$this->CustomFunction->userWisePermission($id,$this->luserId,$this->userType,$this->agencyId,'AwalletHistory.project_id','AwalletHistory.user_id',2);
        $conditionAwallet=$this->CustomFunction->userWisePermission($id,$this->luserId,$this->userType,$this->agencyId,'Awallet.project_id','Awallet.user_id',2);
        $isCommissionPayoutPost = $this->request->is('post') && !empty($this->request->data['CommissionPayout']['submit']);
        $isPaidCommissionEditPost = $this->request->is('post') && !empty($this->request->data['PaidCommissionEdit']['submit']);
        $isPaidCommissionDeletePost = $this->request->is('post') && !empty($this->request->data['PaidCommissionDelete']['submit']);
        if(!$isCommissionPayoutPost && !$isPaidCommissionEditPost && !$isPaidCommissionDeletePost)
        {
            $this->Prg->commonProcess();
        }
        $this->Paginator->settings = $this->paginate;
	
        $cond=array();
        if($id)
        $cond=array('user_id'=>$id);
        $this->Paginator->settings['conditions'] = array($this->AwalletHistory->parseCriteria($this->Prg->parsedParams()),$cond,$condition);
	$this->Paginator->settings['limit']=$this->pageLimit;
        $this->Paginator->settings['maxLimit']=$this->maxLimit;
        $this->loadModel('Awallet');
        $aWalletRow = array();
        if($cond)
        {
        $aWalletRow = (array)$this->Awallet->find('first',array('conditions'=>array($cond,$conditionAwallet)));
        $this->set('aWallet',$aWalletRow);
        }
        else
        {
            $this->Awallet->virtualFields=array('credit'=>'SUM(credit)','debit'=>'SUM(debit)','balance'=>'SUM(balance)');
            $aWalletRow = (array)$this->Awallet->find('first',array('conditions'=>array($cond,$conditionAwallet)));
            $this->set('aWallet',$aWalletRow);
        }
        $walletHistoryRecords = $this->Paginator->paginate();
        $walletHistoryRecords = $this->_decorateWalletHistoryRemarks($walletHistoryRecords);
        $this->set('record', $walletHistoryRecords);
        $walletAgentCards = array();
        $currentAgentSummary = array();
        $dealCommissionDetails=array();
        $paidCommissionRecords=array();
        $commissionLedgerRows=array();
        $paidCommissionTotal=0.0;
        $activeWalletTab='deal';
        $requestedTab = '';
        if(isset($this->request->query['tab']))
        {
            $requestedTab = strtolower(trim((string)$this->request->query['tab']));
        }
        if($requestedTab === '' && $this->request->is('post'))
        {
            if(!empty($this->request->data['PaidCommissionEdit']['filter_tab']))
            {
                $requestedTab = strtolower(trim((string)$this->request->data['PaidCommissionEdit']['filter_tab']));
            }
            elseif(!empty($this->request->data['PaidCommissionDelete']['filter_tab']))
            {
                $requestedTab = strtolower(trim((string)$this->request->data['PaidCommissionDelete']['filter_tab']));
            }
            elseif(!empty($this->request->data['CommissionPayout']['filter_tab']))
            {
                $requestedTab = strtolower(trim((string)$this->request->data['CommissionPayout']['filter_tab']));
            }
        }
        if(in_array($requestedTab, array('deal','paid'), true))
        {
            $activeWalletTab = $requestedTab;
        }
        $dealFilterStart=null;
        $dealFilterEnd=null;
        $selectedProjectId=0;
        $projectFilterOptions=array();
        $payoutCanManage=false;
        $payoutProjectOptions=array();
        $payoutSourceLedgerByProject=array();
        $payoutPaymentTypes=array();
        $walletAgentName='';
        $walletAgentMobile='';
        $walletAgentPhoto='';
        $walletAgentEmail='';
        $walletAgentAddress='';
        $walletAgentPhone='';
        $walletPrintAgencyHeaders=array();
        $commissionPayoutDefaults=array(
            'project_id' => 0,
            'ledger_id' => 0,
            'paymenttype_id' => 0,
            'amount' => '',
            'entry_date' => date('Y-m-d'),
            'remarks' => ''
        );
        $exportCsvUrl=array('controller'=>'AwalletHistories','action'=>'index',$id);
        if((int)$id>0)
        {
            $projectFilterMeta = $this->_agentWalletProjectFilterMeta();
            $projectFilterOptions = (array)$projectFilterMeta['project_options'];
            $allowedProjectIds = array_values(array_unique(array_filter(array_map('intval',(array)$projectFilterMeta['allowed_project_ids']))));
            $payoutAllowedProjectIds = $allowedProjectIds;
            $this->loadModel('User');
            $walletAgentMobileField = '';
            $walletUserSchema = array();
            try
            {
                $walletUserSchema = (array)$this->User->schema();
            }
            catch(Exception $e)
            {
                $walletUserSchema = array();
            }
            foreach(array('mobile','mobile_no','phone','phone_no','contact') as $candidateMobileField)
            {
                if(isset($walletUserSchema[$candidateMobileField]))
                {
                    $walletAgentMobileField = $candidateMobileField;
                    break;
                }
            }
            $walletAgentEmailField = '';
            $walletAgentAddressField = '';
            $walletAgentPhoneField = '';
            foreach(array('email') as $candidateEmailField)
            {
                if(isset($walletUserSchema[$candidateEmailField]))
                {
                    $walletAgentEmailField = $candidateEmailField;
                    break;
                }
            }
            foreach(array('address','full_address','postal_address') as $candidateAddressField)
            {
                if(isset($walletUserSchema[$candidateAddressField]))
                {
                    $walletAgentAddressField = $candidateAddressField;
                    break;
                }
            }
            foreach(array('phone','phone_no','contact') as $candidatePhoneField)
            {
                if(isset($walletUserSchema[$candidatePhoneField]))
                {
                    $walletAgentPhoneField = $candidatePhoneField;
                    break;
                }
            }
            $agentProfileFields = array('User.id','User.name');
            if(isset($walletUserSchema['photo']))
            {
                $agentProfileFields[] = 'User.photo';
            }
            if($walletAgentMobileField !== '')
            {
                $agentProfileFields[] = 'User.'.$walletAgentMobileField;
            }
            if($walletAgentEmailField !== '')
            {
                $agentProfileFields[] = 'User.'.$walletAgentEmailField;
            }
            if($walletAgentAddressField !== '')
            {
                $agentProfileFields[] = 'User.'.$walletAgentAddressField;
            }
            if($walletAgentPhoneField !== '' && $walletAgentPhoneField !== $walletAgentMobileField)
            {
                $agentProfileFields[] = 'User.'.$walletAgentPhoneField;
            }
            $agentProfile = (array)$this->User->find('first', array(
                'fields' => $agentProfileFields,
                'conditions' => array('User.id' => (int)$id),
                'recursive' => -1
            ));
            if(!empty($agentProfile['User']['name']))
            {
                $walletAgentName = trim((string)$agentProfile['User']['name']);
            }
            if($walletAgentMobileField !== '' && !empty($agentProfile['User'][$walletAgentMobileField]))
            {
                $walletAgentMobile = trim((string)$agentProfile['User'][$walletAgentMobileField]);
            }
            if(!empty($agentProfile['User']['photo']))
            {
                $walletAgentPhoto = trim((string)$agentProfile['User']['photo']);
            }
            if($walletAgentEmailField !== '' && !empty($agentProfile['User'][$walletAgentEmailField]))
            {
                $walletAgentEmail = trim((string)$agentProfile['User'][$walletAgentEmailField]);
            }
            if($walletAgentAddressField !== '' && !empty($agentProfile['User'][$walletAgentAddressField]))
            {
                $walletAgentAddress = trim((string)$agentProfile['User'][$walletAgentAddressField]);
            }
            if($walletAgentPhoneField !== '' && !empty($agentProfile['User'][$walletAgentPhoneField]))
            {
                $walletAgentPhone = trim((string)$agentProfile['User'][$walletAgentPhoneField]);
            }
            $agentAssignedProjectIds = array();
            try
            {
                $agentAssignedProjectIds = (array)$this->User->assignedProjectIds((int)$id, true);
            }
            catch(Exception $e)
            {
                $agentAssignedProjectIds = array();
            }
            if(empty($agentAssignedProjectIds))
            {
                $agentPrimaryProjectId = (int)$this->User->field('project_id', array('User.id' => (int)$id));
                if($agentPrimaryProjectId > 0)
                {
                    $agentAssignedProjectIds = array($agentPrimaryProjectId);
                }
            }
            $agentAssignedProjectIds = array_values(array_unique(array_filter(array_map('intval', (array)$agentAssignedProjectIds))));
            if(!empty($agentAssignedProjectIds))
            {
                $payoutAllowedProjectIds = array_values(array_intersect($payoutAllowedProjectIds, $agentAssignedProjectIds));
            }
            $payoutCanManage = $this->_canManageCommissionPayout();
            $payoutProjectOptions = array();
            foreach((array)$payoutAllowedProjectIds as $payoutProjectId)
            {
                if(isset($projectFilterOptions[(int)$payoutProjectId]))
                {
                    $payoutProjectOptions[(int)$payoutProjectId] = $projectFilterOptions[(int)$payoutProjectId];
                }
            }
            if(empty($payoutProjectOptions))
            {
                $payoutCanManage = false;
            }
            $sourceLedgerMeta = $this->_walletSourceLedgerOptionsByProject($payoutAllowedProjectIds);
            $payoutSourceLedgerByProject = isset($sourceLedgerMeta['by_project']) ? (array)$sourceLedgerMeta['by_project'] : array();
            $headerProjectIds = array_values(array_unique(array_filter(array_map('intval', array_keys((array)$projectFilterOptions)))));
            if(empty($headerProjectIds))
            {
                $headerProjectIds = (array)$payoutAllowedProjectIds;
            }
            if(!empty($headerProjectIds))
            {
                $this->loadModel('Project');
                $walletHeaderProjectFields = $this->_walletHeaderProjectFields();
                $headerProjectRows = (array)$this->Project->find('all', array(
                    'fields' => $walletHeaderProjectFields,
                    'conditions' => array('Project.id' => $headerProjectIds),
                    'recursive' => -1
                ));
                foreach((array)$headerProjectRows as $headerProjectRow)
                {
                    $projectData = isset($headerProjectRow['Project']) ? (array)$headerProjectRow['Project'] : array();
                    $projectId = isset($projectData['id']) ? (int)$projectData['id'] : 0;
                    if($projectId <= 0)
                    {
                        continue;
                    }
                    $walletPrintAgencyHeaders[$projectId] = $projectData;
                }
            }
            $this->loadModel('Paymenttype');
            $payoutPaymentTypes = (array)$this->Paymenttype->find('list', array('fields' => array('Paymenttype.id','Paymenttype.name')));
            if(!empty($payoutPaymentTypes))
            {
                $commissionPayoutDefaults['paymenttype_id'] = (int)key($payoutPaymentTypes);
            }
            $requestedProjectId = isset($this->request->query['project_id']) ? (int)$this->request->query['project_id'] : 0;
            if($requestedProjectId > 0 && in_array($requestedProjectId, $allowedProjectIds, true))
            {
                $selectedProjectId = $requestedProjectId;
            }
            $dealFilterStart = $this->_normalizeYmdDate(isset($this->request->query['start']) ? $this->request->query['start'] : null);
            $dealFilterEnd = $this->_normalizeYmdDate(isset($this->request->query['end']) ? $this->request->query['end'] : null);
            if($this->request->is('post') && !empty($this->request->data['PaidCommissionEdit']['submit']) && $payoutCanManage)
            {
                $paidEditFormData = isset($this->request->data['PaidCommissionEdit']) ? (array)$this->request->data['PaidCommissionEdit'] : array();
                $saveResult = $this->_updatePaidCommissionPayout($id, $paidEditFormData, $payoutAllowedProjectIds, $condition);
                if(!empty($saveResult['ok']))
                {
                    $this->Session->setFlash(__('Paid commission transaction updated successfully.'), 'flash', array('alert' => 'success'));
                    $redirectQuery = array();
                    $keepProject = isset($paidEditFormData['filter_project_id']) ? (int)$paidEditFormData['filter_project_id'] : $selectedProjectId;
                    $keepStart = $this->_normalizeYmdDate(isset($paidEditFormData['filter_start']) ? $paidEditFormData['filter_start'] : $dealFilterStart);
                    $keepEnd = $this->_normalizeYmdDate(isset($paidEditFormData['filter_end']) ? $paidEditFormData['filter_end'] : $dealFilterEnd);
                    if($keepProject > 0)
                    {
                        $redirectQuery['project_id'] = $keepProject;
                    }
                    if($keepStart !== null)
                    {
                        $redirectQuery['start'] = $keepStart;
                    }
                    if($keepEnd !== null)
                    {
                        $redirectQuery['end'] = $keepEnd;
                    }
                    $keepTab = strtolower(trim((string)(isset($paidEditFormData['filter_tab']) ? $paidEditFormData['filter_tab'] : 'paid')));
                    if(!in_array($keepTab, array('deal','paid'), true))
                    {
                        $keepTab = 'paid';
                    }
                    $redirectQuery['tab'] = $keepTab;
                    return $this->redirect(array('action' => 'index', $id, '?' => $redirectQuery));
                }
                $this->Session->setFlash(isset($saveResult['message']) ? $saveResult['message'] : __('Unable to update paid commission transaction.'), 'flash', array('alert' => 'danger'));
            }
            if($this->request->is('post') && !empty($this->request->data['PaidCommissionDelete']['submit']) && $payoutCanManage)
            {
                $paidDeleteFormData = isset($this->request->data['PaidCommissionDelete']) ? (array)$this->request->data['PaidCommissionDelete'] : array();
                $deleteResult = $this->_deletePaidCommissionPayout($id, $paidDeleteFormData, $payoutAllowedProjectIds, $condition);
                if(!empty($deleteResult['ok']))
                {
                    $this->Session->setFlash(__('Paid commission transaction deleted successfully.'), 'flash', array('alert' => 'success'));
                    $redirectQuery = array();
                    $keepProject = isset($paidDeleteFormData['filter_project_id']) ? (int)$paidDeleteFormData['filter_project_id'] : $selectedProjectId;
                    $keepStart = $this->_normalizeYmdDate(isset($paidDeleteFormData['filter_start']) ? $paidDeleteFormData['filter_start'] : $dealFilterStart);
                    $keepEnd = $this->_normalizeYmdDate(isset($paidDeleteFormData['filter_end']) ? $paidDeleteFormData['filter_end'] : $dealFilterEnd);
                    if($keepProject > 0)
                    {
                        $redirectQuery['project_id'] = $keepProject;
                    }
                    if($keepStart !== null)
                    {
                        $redirectQuery['start'] = $keepStart;
                    }
                    if($keepEnd !== null)
                    {
                        $redirectQuery['end'] = $keepEnd;
                    }
                    $keepTab = strtolower(trim((string)(isset($paidDeleteFormData['filter_tab']) ? $paidDeleteFormData['filter_tab'] : 'paid')));
                    if(!in_array($keepTab, array('deal','paid'), true))
                    {
                        $keepTab = 'paid';
                    }
                    $redirectQuery['tab'] = $keepTab;
                    return $this->redirect(array('action' => 'index', $id, '?' => $redirectQuery));
                }
                $this->Session->setFlash(isset($deleteResult['message']) ? $deleteResult['message'] : __('Unable to delete paid commission transaction.'), 'flash', array('alert' => 'danger'));
            }
            $dealScopeCondition=$this->CustomFunction->userWisePermission($id,$this->luserId,$this->userType,$this->agencyId,'Property.project_id','Deal.user_id',2);
            $dealScopedConditions=array();
            if(!empty($dealScopeCondition))
            {
                $dealScopedConditions[] = $dealScopeCondition;
            }
            if($selectedProjectId > 0)
            {
                $dealScopedConditions[] = array('Property.project_id' => $selectedProjectId);
            }
            if($dealFilterStart !== null)
            {
                $dealScopedConditions[] = array('Deal.date >=' => $dealFilterStart);
            }
            if($dealFilterEnd !== null)
            {
                $dealScopedConditions[] = array('Deal.date <=' => $dealFilterEnd);
            }
            $dealCommissionDetails=$this->_buildAgentDealCommissionDetails($id,$dealScopedConditions);
            $paidCommissionConditions = $this->_buildPaidCommissionHistoryConditions($id, $condition);
            if($selectedProjectId > 0)
            {
                $paidCommissionConditions['AwalletHistory.project_id'] = (int)$selectedProjectId;
            }
            if($dealFilterStart !== null)
            {
                $paidCommissionConditions['AwalletHistory.date >='] = $dealFilterStart.' 00:00:00';
            }
            if($dealFilterEnd !== null)
            {
                $paidCommissionConditions['AwalletHistory.date <='] = $dealFilterEnd.' 23:59:59';
            }
            $paidCommissionRecords = (array)$this->AwalletHistory->find('all', array(
                'joins' => array(
                    array(
                        'table' => 'projects',
                        'alias' => 'Project',
                        'type' => 'LEFT',
                        'conditions' => array('Project.id=AwalletHistory.project_id')
                    )
                ),
                'fields' => array(
                    'AwalletHistory.id',
                    'AwalletHistory.project_id',
                    'AwalletHistory.amount',
                    'AwalletHistory.date',
                    'AwalletHistory.remarks',
                    'Project.name'
                ),
                'conditions' => $paidCommissionConditions,
                'order' => array('AwalletHistory.date' => 'desc', 'AwalletHistory.id' => 'desc'),
                'recursive' => -1
            ));
            foreach((array)$paidCommissionRecords as $paidRow)
            {
                $paidCommissionTotal += (float)(isset($paidRow['AwalletHistory']['amount']) ? $paidRow['AwalletHistory']['amount'] : 0);
            }
            $paidCommissionRefs = array();
            foreach((array)$paidCommissionRecords as $paidIndex => $paidRow)
            {
                $remarksText = isset($paidRow['AwalletHistory']['remarks']) ? (string)$paidRow['AwalletHistory']['remarks'] : '';
                $refNo = $this->_extractVoucherRefNoFromText($remarksText);
                $paidCommissionRecords[$paidIndex]['AwalletHistory']['voucher_ref'] = $refNo;
                $paidCommissionRecords[$paidIndex]['AwalletHistory']['transaction_id'] = 0;
                $paidCommissionRecords[$paidIndex]['AwalletHistory']['transaction_amount'] = 0;
                $paidCommissionRecords[$paidIndex]['AwalletHistory']['transaction_entry_date'] = null;
                if($refNo !== '')
                {
                    $projectKey = isset($paidRow['AwalletHistory']['project_id']) ? (int)$paidRow['AwalletHistory']['project_id'] : 0;
                    $paidCommissionRefs[$projectKey.'|'.$refNo] = array(
                        'project_id' => $projectKey,
                        'ref_no' => $refNo
                    );
                }
            }
            if(!empty($paidCommissionRefs))
            {
                $this->loadModel('Admin.Ardtransaction');
                $txOrConditions = array();
                foreach((array)$paidCommissionRefs as $refMeta)
                {
                    $txOrConditions[] = array(
                        'Ardtransaction.project_id' => (int)$refMeta['project_id'],
                        'Ardtransaction.ref_no' => (string)$refMeta['ref_no']
                    );
                }
                if(!empty($txOrConditions))
                {
                    $txConditions = array(
                        'Ardtransaction.type' => 'expense',
                        'OR' => $txOrConditions
                    );
                    $txRows = (array)$this->Ardtransaction->find('all', array(
                        'fields' => array('Ardtransaction.id','Ardtransaction.project_id','Ardtransaction.ref_no','Ardtransaction.amount','Ardtransaction.entry_date'),
                        'conditions' => $txConditions,
                        'order' => array('Ardtransaction.id' => 'desc'),
                        'recursive' => -1
                    ));
                    $txMap = array();
                    foreach((array)$txRows as $txRow)
                    {
                        $tx = isset($txRow['Ardtransaction']) ? (array)$txRow['Ardtransaction'] : array();
                        $txProjectId = isset($tx['project_id']) ? (int)$tx['project_id'] : 0;
                        $txRefNo = isset($tx['ref_no']) ? trim((string)$tx['ref_no']) : '';
                        if($txProjectId <= 0 || $txRefNo === '')
                        {
                            continue;
                        }
                        $txKey = $txProjectId.'|'.$txRefNo;
                        if(!isset($txMap[$txKey]))
                        {
                            $txMap[$txKey] = $tx;
                        }
                    }
                    foreach((array)$paidCommissionRecords as $paidIndex => $paidRow)
                    {
                        $refNo = isset($paidRow['AwalletHistory']['voucher_ref']) ? trim((string)$paidRow['AwalletHistory']['voucher_ref']) : '';
                        $projectKey = isset($paidRow['AwalletHistory']['project_id']) ? (int)$paidRow['AwalletHistory']['project_id'] : 0;
                        if($refNo === '' || $projectKey <= 0)
                        {
                            continue;
                        }
                        $lookupKey = $projectKey.'|'.$refNo;
                        if(empty($txMap[$lookupKey]))
                        {
                            continue;
                        }
                        $paidCommissionRecords[$paidIndex]['AwalletHistory']['transaction_id'] = (int)$txMap[$lookupKey]['id'];
                        $paidCommissionRecords[$paidIndex]['AwalletHistory']['transaction_amount'] = (float)$txMap[$lookupKey]['amount'];
                        $paidCommissionRecords[$paidIndex]['AwalletHistory']['transaction_entry_date'] = isset($txMap[$lookupKey]['entry_date']) ? $txMap[$lookupKey]['entry_date'] : null;
                    }
                }
            }
            $commissionLedgerRows = $this->_buildUnifiedCommissionLedgerRows($id, $dealCommissionDetails, $paidCommissionRecords, $selectedProjectId, $dealFilterStart, $dealFilterEnd);

            if($commissionPayoutDefaults['project_id'] <= 0)
            {
                if($selectedProjectId > 0 && in_array((int)$selectedProjectId, (array)$payoutAllowedProjectIds, true))
                {
                    $commissionPayoutDefaults['project_id'] = $selectedProjectId;
                }
                elseif(!empty($payoutAllowedProjectIds))
                {
                    $commissionPayoutDefaults['project_id'] = (int)$payoutAllowedProjectIds[0];
                }
            }
            if((int)$commissionPayoutDefaults['project_id'] > 0 && !empty($payoutSourceLedgerByProject[(int)$commissionPayoutDefaults['project_id']]))
            {
                $firstLedgerKeys = array_keys((array)$payoutSourceLedgerByProject[(int)$commissionPayoutDefaults['project_id']]);
                if(!empty($firstLedgerKeys))
                {
                    $commissionPayoutDefaults['ledger_id'] = (int)$firstLedgerKeys[0];
                }
            }
            if($this->request->is('post') && !empty($this->request->data['CommissionPayout']['submit']) && $payoutCanManage)
            {
                $commissionFormData = isset($this->request->data['CommissionPayout']) ? (array)$this->request->data['CommissionPayout'] : array();
                $saveResult = $this->_saveCommissionPayout($id, $commissionFormData, $payoutAllowedProjectIds);
                if(!empty($saveResult['ok']))
                {
                    $successText = __('Commission payout saved. Wallet debited and expense recorded.');
                    if(!empty($saveResult['voucher_ref']))
                    {
                        $successText .= ' '.__('Voucher').': '.trim((string)$saveResult['voucher_ref']);
                    }
                    if(!empty($saveResult['expense_id']))
                    {
                        $successText .= ' | '.__('Expense').': #'.(int)$saveResult['expense_id'];
                    }
                    $this->Session->setFlash($successText,'flash',array('alert'=>'success'));
                    $redirectQuery = array();
                    $keepProject = isset($commissionFormData['filter_project_id']) ? (int)$commissionFormData['filter_project_id'] : $selectedProjectId;
                    $keepStart = $this->_normalizeYmdDate(isset($commissionFormData['filter_start']) ? $commissionFormData['filter_start'] : $dealFilterStart);
                    $keepEnd = $this->_normalizeYmdDate(isset($commissionFormData['filter_end']) ? $commissionFormData['filter_end'] : $dealFilterEnd);
                    if($keepProject > 0)
                    {
                        $redirectQuery['project_id'] = $keepProject;
                    }
                    if($keepStart !== null)
                    {
                        $redirectQuery['start'] = $keepStart;
                    }
                    if($keepEnd !== null)
                    {
                        $redirectQuery['end'] = $keepEnd;
                    }
                    $keepTab = strtolower(trim((string)(isset($commissionFormData['filter_tab']) ? $commissionFormData['filter_tab'] : $activeWalletTab)));
                    if(in_array($keepTab, array('deal','paid'), true))
                    {
                        $redirectQuery['tab'] = $keepTab;
                    }
                    return $this->redirect(array('action' => 'index', $id, '?' => $redirectQuery));
                }
                $this->Session->setFlash(isset($saveResult['message']) ? $saveResult['message'] : __('Unable to save commission payout.'),'flash',array('alert'=>'danger'));
                $commissionPayoutDefaults = array_merge($commissionPayoutDefaults, $commissionFormData);
            }
            $queryParams = array();
            if($selectedProjectId > 0)
            {
                $queryParams['project_id'] = $selectedProjectId;
            }
            if($dealFilterStart !== null)
            {
                $queryParams['start'] = $dealFilterStart;
            }
            if($dealFilterEnd !== null)
            {
                $queryParams['end'] = $dealFilterEnd;
            }
            if(in_array($activeWalletTab, array('deal','paid'), true))
            {
                $queryParams['tab'] = $activeWalletTab;
            }
            $exportCsvUrl = array('controller'=>'AwalletHistories','action'=>'index',$id,'?' => array_merge($queryParams,array('export'=>'csv')));
            $exportType = strtolower(trim((string)(isset($this->request->query['export']) ? $this->request->query['export'] : '')));
            if($exportType === 'csv')
            {
                return $this->_exportDealCommissionCsv($id,$dealCommissionDetails,$selectedProjectId,$dealFilterStart,$dealFilterEnd);
            }
            $currentAgentSummary = $this->_buildAgentWalletSummaryMeta($dealCommissionDetails, $aWalletRow, $paidCommissionTotal);
        }
        else
        {
            $walletAgentCards = $this->_buildWalletAgentCards($conditionAwallet);
        }
        $this->set('dealCommissionDetails',$dealCommissionDetails);
        $this->set('paidCommissionRecords',$paidCommissionRecords);
        $this->set('commissionLedgerRows',$commissionLedgerRows);
        $this->set('paidCommissionTotal',$paidCommissionTotal);
        $this->set('projectFilterOptions',$projectFilterOptions);
        $this->set('selectedProjectId',$selectedProjectId);
        $this->set('dealFilterStart',$dealFilterStart);
        $this->set('dealFilterEnd',$dealFilterEnd);
        $this->set('activeWalletTab',$activeWalletTab);
        $this->set('exportCsvUrl',$exportCsvUrl);
        $this->set('payoutCanManage',$payoutCanManage);
        $this->set('payoutProjectOptions',$payoutProjectOptions);
        $this->set('payoutSourceLedgerByProject',$payoutSourceLedgerByProject);
        $this->set('payoutPaymentTypes',$payoutPaymentTypes);
        $this->set('commissionPayoutDefaults',$commissionPayoutDefaults);
        $this->set('walletAgentName',$walletAgentName);
        $this->set('walletAgentMobile',$walletAgentMobile);
        $this->set('walletAgentPhoto',$walletAgentPhoto);
        $this->set('walletAgentEmail',$walletAgentEmail);
        $this->set('walletAgentAddress',$walletAgentAddress);
        $this->set('walletAgentPhone',$walletAgentPhone);
        $this->set('walletPrintAgencyHeaders',$walletPrintAgencyHeaders);
        $this->set('walletAgentCards',$walletAgentCards);
        $this->set('currentAgentSummary',$currentAgentSummary);
	$this->set('id',$id);
	if ($this->request->is('ajax'))
        {
            $this->render('index','ajax'); // View, Layout
        }
    }
    public function view($id = null)
    {
        $this->layout = null;
        $this->loadModel('DealsPayment');
	$this->loadModel('PlansPayment');
        if (!$id)
        {
            $this->Session->setFlash(__('Invalid Post.'),'flash',array('alert'=>'danger'));
            $this->redirect(array('action' => 'index'));
        }
        $post = $this->DealsPayment->find('first',array('joins'=>array(array('table'=>'deals','alias'=>'Deal','type'=>'LEFT','conditions'=>array('DealsPayment.deal_id=Deal.id')),
                                                                       array('table'=>'clients','alias'=>'Client','type'=>'LEFT','conditions'=>array('Deal.client_id=Client.id')),
                                                                       array('table'=>'properties','alias'=>'Property','type'=>'LEFT','conditions'=>array('Deal.property_id=Property.id')),
                                                                       array('table'=>'units','alias'=>'Unit','type'=>'LEFT','conditions'=>array('Property.unit_id=Unit.id')),
                                                                       array('table'=>'plans','alias'=>'Plan','type'=>'LEFT','conditions'=>array('Deal.plan_id=Plan.id')),
								       array('table'=>'paymenttypes','alias'=>'Paymenttype','type'=>'LEFT','conditions'=>array('DealsPayment.paymenttype_id=Paymenttype.id')),
                                                                       array('table'=>'plans_payments','alias'=>'PlansPayment','type'=>'LEFT','conditions'=>array('DealsPayment.plans_payment_id=PlansPayment.id'))),
                                                      'fields'=>array('DealsPayment.*','Deal.*','Client.*','Property.*','Unit.*','Plan.*','PlansPayment.*','Paymenttype.*'),
                                                      'conditions'=>array('DealsPayment.id'=>$id)));
        $dealId=$post['Deal']['id'];
        $dueDateArr = $this->PlansPayment->find('first',array('fields'=>array('PlansPayment.*'),
                                                             'conditions'=>array('PlansPayment.deal_id'=>$dealId,'PlansPayment.id >'=>$post['PlansPayment']['id']),
                                                             'order'=>array('PlansPayment.id'=>'asc')));
        if(!$post)
        {
            $this->Session->setFlash(__('Invalid Post.'),'flash',array('alert'=>'danger'));
            $this->redirect(array('action' => 'index'));
        }
        if($dueDateArr)
        $dueDate=$dueDateArr['PlansPayment']['date'];
        else
        $dueDate=null;
        $this->set('dueDate',$dueDate);
        $this->set('post',$post);
    }
    public function printentry($historyId = null)
    {
        $historyId = (int)$historyId;
        if($historyId <= 0)
        {
            $this->Session->setFlash(__('Invalid wallet entry.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'index'));
        }

        $this->loadModel('AwalletHistory');
        $this->loadModel('Admin.Ardsetting');
        $this->loadModel('Admin.Ardtemplate');

        $this->loadModel('Project');
        $projectFields = $this->_walletHeaderProjectFields();
        $projectFields = array_values(array_unique(array_merge($projectFields, array('Project.id','Project.name'))));
        $queryFields = array_merge(
            array('AwalletHistory.*'),
            $projectFields,
            array('User.name','User.mobile','User.phone','User.photo')
        );
        $row = (array)$this->AwalletHistory->find('first', array(
            'joins' => array(
                array('table' => 'projects', 'alias' => 'Project', 'type' => 'LEFT', 'conditions' => array('Project.id=AwalletHistory.project_id')),
                array('table' => 'users', 'alias' => 'User', 'type' => 'LEFT', 'conditions' => array('User.id=AwalletHistory.user_id'))
            ),
            'fields' => $queryFields,
            'conditions' => array('AwalletHistory.id' => $historyId),
            'recursive' => -1
        ));
        if(empty($row['AwalletHistory']))
        {
            $this->Session->setFlash(__('Wallet entry not found.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        $decoratedRows = $this->_decorateWalletHistoryRemarks(array($row));
        if(!empty($decoratedRows[0]['AwalletHistory']))
        {
            $row = $decoratedRows[0];
        }

        $entry = (array)$row['AwalletHistory'];
        $scopeCondition = $this->CustomFunction->userWisePermission((int)$entry['user_id'],$this->luserId,$this->userType,$this->agencyId,'AwalletHistory.project_id','AwalletHistory.user_id',2);
        $allowedRow = (array)$this->AwalletHistory->find('first', array(
            'fields' => array('AwalletHistory.id'),
            'conditions' => array('AwalletHistory.id' => $historyId, $scopeCondition),
            'recursive' => -1
        ));
        if(empty($allowedRow['AwalletHistory']['id']))
        {
            $this->Session->setFlash(__('You are not allowed to access this wallet print.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'index'));
        }

        $project = !empty($row['Project']) ? (array)$row['Project'] : array();
        $agent = !empty($row['User']) ? (array)$row['User'] : array();
        $projectId = isset($entry['project_id']) ? (int)$entry['project_id'] : 0;
        $settings = (array)$this->Ardsetting->find('first', array(
            'conditions' => array('Ardsetting.project_id' => $projectId),
            'order' => array('Ardsetting.id' => 'desc'),
            'recursive' => -1
        ));
        $templateRow = (array)$this->Ardtemplate->find('first', array(
            'conditions' => array(
                'Ardtemplate.project_id' => $projectId,
                'Ardtemplate.template_type' => 'voucher',
                'Ardtemplate.is_active' => 1
            ),
            'order' => array('Ardtemplate.id' => 'desc'),
            'recursive' => -1
        ));
        if(empty($templateRow))
        {
            $templateRow = (array)$this->Ardtemplate->find('first', array(
                'conditions' => array(
                    'Ardtemplate.project_id' => $projectId,
                    'Ardtemplate.template_type' => 'voucher'
                ),
                'order' => array('Ardtemplate.is_active' => 'desc', 'Ardtemplate.id' => 'desc'),
                'recursive' => -1
            ));
        }

        $templateContent = !empty($templateRow['Ardtemplate']['template_content']) ? (string)$templateRow['Ardtemplate']['template_content'] : '';
        if(trim($templateContent) === '')
        {
            $templateContent = $this->_walletVoucherDefaultTemplateContent();
        }

        $companyName = !empty($settings['Ardsetting']['company_name']) ? trim((string)$settings['Ardsetting']['company_name']) : '';
        if($companyName === '')
        {
            $companyName = !empty($project['name']) ? trim((string)$project['name']) : 'White Villas';
        }
        $resolveImagePath = function($candidates = array()) {
            foreach((array)$candidates as $candidate)
            {
                if(!is_string($candidate))
                {
                    continue;
                }
                $cleanPath = trim(str_replace('\\', '/', (string)$candidate), '/');
                if($cleanPath === '')
                {
                    continue;
                }
                $fullPath = WWW_ROOT . 'img' . DS . str_replace('/', DS, $cleanPath);
                if(file_exists($fullPath))
                {
                    return $cleanPath;
                }
            }
            return '';
        };
        $agencyLogoPath = $resolveImagePath(array(
            !empty($project['photo']) ? 'agency_thumb/' . trim((string)$project['photo']) : '',
            !empty($project['photo']) ? 'agency/' . trim((string)$project['photo']) : '',
            !empty($project['photo']) ? trim((string)$project['photo']) : ''
        ));
        $agencyLogoUrl = ($agencyLogoPath !== '') ? Router::url('/img/' . $agencyLogoPath, true) : '';
        $agencyLogoImgTag = ($agencyLogoUrl !== '') ? '<img src="' . htmlspecialchars($agencyLogoUrl, ENT_QUOTES, 'UTF-8') . '" alt="Agency Logo" style="max-width:100%;max-height:100%;object-fit:contain;" />' : '';

        $entryDateRaw = !empty($entry['date']) ? trim((string)$entry['date']) : date('Y-m-d H:i:s');
        $entryTs = strtotime($entryDateRaw);
        if($entryTs === false)
        {
            $entryTs = time();
        }
        $entryDateLabel = date('d-M-Y', $entryTs);
        $dateDigits = str_pad(date('dmY', $entryTs), 8, '0', STR_PAD_RIGHT);
        $amount = round((float)(isset($entry['amount']) ? $entry['amount'] : 0), 2);
        $amountFormatted = number_format($amount, 2, '.', ',');
        $currencySymbol = trim(strip_tags((string)$this->currency));
        if($currencySymbol === '')
        {
            $currencySymbol = 'Rs';
        }
        $amountWithCurrency = $currencySymbol . ' ' . $amountFormatted;
        $typeCode = strtoupper(trim((string)(isset($entry['type']) ? $entry['type'] : '')));
        $voucherType = ($typeCode === 'DR') ? 'Commission Debit Voucher' : 'Commission Credit Voucher';
        $referenceNo = 'AWH-' . $historyId;
        if(!empty($entry['remarks']) && preg_match('/Deal\s+Commission\s*#\s*(\d+)/i', (string)$entry['remarks'], $matches))
        {
            $referenceNo = 'DEAL-COM-' . (int)$matches[1];
        }
        $description = trim((string)(!empty($entry['remarks_display']) ? $entry['remarks_display'] : (!empty($entry['remarks']) ? $entry['remarks'] : 'Agent wallet ledger entry')));
        $payTo = !empty($agent['name']) ? trim((string)$agent['name']) : 'Agent Wallet';
        if($this->_isAutoCommissionHistoryRemarks(isset($entry['remarks']) ? $entry['remarks'] : ''))
        {
            $descriptionLines = preg_split("/\\r\\n|\\r|\\n/", $description);
            $firstLine = !empty($descriptionLines[0]) ? trim((string)$descriptionLines[0]) : '';
            if($firstLine !== '')
            {
                $payTo = $firstLine;
            }
        }
        $tokenMap = array(
            'company_name' => $companyName,
            'agency_name' => !empty($project['name']) ? $project['name'] : $companyName,
            'agency_logo_url' => $agencyLogoUrl,
            'agency_logo' => $agencyLogoUrl,
            'agency_logo_img' => $agencyLogoImgTag,
            'agency_address' => !empty($project['address']) ? $project['address'] : '-',
            'agency_phone' => !empty($project['phone']) ? $project['phone'] : '-',
            'agency_mobile' => !empty($project['mobile']) ? $project['mobile'] : '-',
            'agency_email' => !empty($project['email']) ? $project['email'] : '-',
            'agent_name' => !empty($agent['name']) ? $agent['name'] : '-',
            'agent_username' => !empty($agent['name']) ? $agent['name'] : '-',
            'agent_email' => '-',
            'agent_phone' => !empty($agent['phone']) ? $agent['phone'] : '-',
            'agent_mobile' => !empty($agent['mobile']) ? $agent['mobile'] : '-',
            'agent_address' => '-',
            'agent_photo_url' => '',
            'agent_logo_url' => '',
            'voucher_no' => $referenceNo,
            'reference_no' => $referenceNo,
            'voucher_type' => $voucherType,
            'entry_date' => $entryDateLabel,
            'generated_at' => date('d-M-Y H:i:s'),
            'pay_to' => $payTo,
            'ledger_name' => 'Agent Commission Wallet',
            'source_ledger_name' => 'Agent Commission Wallet',
            'expense_ledger_name' => 'Agent Commission Wallet',
            'site_name' => '-',
            'narration' => $description,
            'description' => $description,
            'amount' => $amountFormatted,
            'amount_number' => $amountFormatted,
            'amount_with_currency' => $amountWithCurrency,
            'amount_words' => $this->_walletVoucherAmountInWords($amount, $currencySymbol),
            'currency_symbol' => $currencySymbol,
            'account_no' => '-',
            'barcode_text' => $referenceNo,
            'debit_amount' => ($typeCode === 'DR') ? $amountWithCurrency : '-',
            'credit_amount' => ($typeCode === 'CR') ? $amountWithCurrency : '-',
            'd1' => substr($dateDigits, 0, 1),
            'd2' => substr($dateDigits, 1, 1),
            'd3' => substr($dateDigits, 2, 1),
            'd4' => substr($dateDigits, 3, 1),
            'd5' => substr($dateDigits, 4, 1),
            'd6' => substr($dateDigits, 5, 1),
            'd7' => substr($dateDigits, 6, 1),
            'd8' => substr($dateDigits, 7, 1)
        );

        $html = $this->_walletRenderVoucherTemplateTokens($templateContent, $tokenMap);
        if(stripos($html, '<html') === false)
        {
            $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>' . htmlspecialchars($referenceNo, ENT_QUOTES, 'UTF-8') . '</title></head><body>' . $html . '</body></html>';
        }
        $html = $this->_walletApplyVoucherDateMonthStyle($html);

        $this->layout = false;
        $this->autoRender = false;
        $this->response->type('html');
        $this->response->body($html);
        return $this->response;
    }
    protected function _buildCommissionDuplicateCleanupPlan()
    {
        $rows = $this->AwalletHistory->find('all', array(
            'joins' => array(
                array(
                    'table' => 'deals_payments',
                    'alias' => 'DealsPayment',
                    'type' => 'INNER',
                    'conditions' => array('DealsPayment.id = AwalletHistory.deals_payment_id')
                )
            ),
            'fields' => array(
                'AwalletHistory.id',
                'AwalletHistory.user_id',
                'AwalletHistory.project_id',
                'AwalletHistory.deals_payment_id',
                'AwalletHistory.amount',
                'AwalletHistory.type',
                'AwalletHistory.date',
                'AwalletHistory.remarks',
                'DealsPayment.deal_id'
            ),
            'conditions' => array(
                'AwalletHistory.type' => 'CR',
                'AwalletHistory.amount >' => 0,
                'AwalletHistory.deals_payment_id >' => 0,
                'OR' => array(
                    array('AwalletHistory.remarks LIKE' => '%commission%'),
                    array('AwalletHistory.remarks LIKE' => '%Deal Commission #%')
                )
            ),
            'order' => array(
                'DealsPayment.deal_id' => 'asc',
                'AwalletHistory.user_id' => 'asc',
                'AwalletHistory.id' => 'asc'
            ),
            'recursive' => -1
        ));

        $grouped = array();
        foreach((array)$rows as $row)
        {
            $dealId = isset($row['DealsPayment']['deal_id']) ? (int)$row['DealsPayment']['deal_id'] : 0;
            $userId = isset($row['AwalletHistory']['user_id']) ? (int)$row['AwalletHistory']['user_id'] : 0;
            if($dealId <= 0 || $userId <= 0)
            {
                continue;
            }
            $groupKey = $dealId.'_'.$userId;
            if(!isset($grouped[$groupKey]))
            {
                $grouped[$groupKey] = array(
                    'deal_id' => $dealId,
                    'user_id' => $userId,
                    'rows' => array()
                );
            }
            $grouped[$groupKey]['rows'][] = $row;
        }

        $groups = array();
        $totalDuplicateRows = 0;
        $totalDuplicateAmount = 0.0;
        foreach($grouped as $group)
        {
            $rowsInGroup = isset($group['rows']) ? (array)$group['rows'] : array();
            if(count($rowsInGroup) <= 1)
            {
                continue;
            }

            $dealId = (int)$group['deal_id'];
            $keepIndex = 0;
            foreach($rowsInGroup as $idx => $r)
            {
                $remarks = strtolower(trim((string)(isset($r['AwalletHistory']['remarks']) ? $r['AwalletHistory']['remarks'] : '')));
                if($remarks !== '' && strpos($remarks, 'deal commission #'.$dealId) !== false)
                {
                    $keepIndex = (int)$idx;
                    break;
                }
            }

            $keepRow = $rowsInGroup[$keepIndex];
            $duplicateRows = array();
            $duplicateAmount = 0.0;
            foreach($rowsInGroup as $idx => $r)
            {
                if((int)$idx === (int)$keepIndex)
                {
                    continue;
                }
                $duplicateRows[] = $r;
                $duplicateAmount += (float)(isset($r['AwalletHistory']['amount']) ? $r['AwalletHistory']['amount'] : 0);
            }

            if(empty($duplicateRows))
            {
                continue;
            }
            $groups[] = array(
                'deal_id' => $dealId,
                'user_id' => (int)$group['user_id'],
                'keep_row' => $keepRow,
                'duplicate_rows' => $duplicateRows,
                'duplicate_count' => count($duplicateRows),
                'duplicate_amount' => round($duplicateAmount, 2)
            );
            $totalDuplicateRows += count($duplicateRows);
            $totalDuplicateAmount += $duplicateAmount;
        }

        return array(
            'groups' => $groups,
            'total_groups' => count($groups),
            'total_duplicate_rows' => (int)$totalDuplicateRows,
            'total_duplicate_amount' => round($totalDuplicateAmount, 2)
        );
    }
    public function cleanupduplicates()
    {
        $isSuperAdmin = $this->_isSuperAdminSession((array)$this->Session->read('User'));
        if(!$isSuperAdmin)
        {
            $this->Session->setFlash(__('No Permission!'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action'=>'index'));
        }

        $this->loadModel('Deal');
        $this->loadModel('User');
        $plan = $this->_buildCommissionDuplicateCleanupPlan();
        $groups = isset($plan['groups']) ? (array)$plan['groups'] : array();

        $dealNameMap = array();
        $userNameMap = array();
        foreach($groups as $group)
        {
            $dealId = (int)$group['deal_id'];
            $userId = (int)$group['user_id'];
            if($dealId > 0 && !isset($dealNameMap[$dealId]))
            {
                $dealRow = $this->Deal->find('first', array(
                    'fields' => array('Deal.id','Deal.invoice_no'),
                    'conditions' => array('Deal.id' => $dealId),
                    'recursive' => -1
                ));
                $dealNameMap[$dealId] = !empty($dealRow['Deal']['invoice_no']) ? $dealRow['Deal']['invoice_no'] : ('Deal #'.$dealId);
            }
            if($userId > 0 && !isset($userNameMap[$userId]))
            {
                $userRow = $this->User->find('first', array(
                    'fields' => array('User.id','User.name'),
                    'conditions' => array('User.id' => $userId),
                    'recursive' => -1
                ));
                $userNameMap[$userId] = !empty($userRow['User']['name']) ? $userRow['User']['name'] : ('User #'.$userId);
            }
        }

        if($this->request->is('post') && !empty($this->request->data['AwalletHistory']['run_cleanup']))
        {
            $ds = $this->AwalletHistory->getDataSource();
            $ds->begin();
            try
            {
                $deletedRows = 0;
                $deletedAmount = 0.0;
                foreach($groups as $group)
                {
                    foreach((array)$group['duplicate_rows'] as $dupRow)
                    {
                        $historyId = isset($dupRow['AwalletHistory']['id']) ? (int)$dupRow['AwalletHistory']['id'] : 0;
                        $agentId = isset($dupRow['AwalletHistory']['user_id']) ? (int)$dupRow['AwalletHistory']['user_id'] : 0;
                        $amount = isset($dupRow['AwalletHistory']['amount']) ? (float)$dupRow['AwalletHistory']['amount'] : 0.0;
                        if($historyId <= 0 || $agentId <= 0 || $amount <= 0)
                        {
                            continue;
                        }
                        if(!$this->CustomFunction->awalletDelete($agentId, $amount))
                        {
                            throw new Exception(__('Unable to reverse wallet amount for duplicate commission.'));
                        }
                        if(!$this->AwalletHistory->delete($historyId))
                        {
                            throw new Exception(__('Unable to remove duplicate commission history row.'));
                        }
                        $deletedRows++;
                        $deletedAmount += $amount;
                    }
                }
                $ds->commit();
                $deletedAmountFormatted = number_format((float)$deletedAmount, 2, '.', ',');
                $this->Session->setFlash(
                    __('Cleanup completed. Removed duplicate rows: ').$deletedRows.' | '.__('Amount reversed: ').$this->currency.$deletedAmountFormatted,
                    'flash',
                    array('alert'=>'success')
                );
                return $this->redirect(array('action'=>'cleanupduplicates'));
            }
            catch(Exception $e)
            {
                $ds->rollback();
                $this->Session->setFlash($e->getMessage(),'flash',array('alert'=>'danger'));
            }
        }

        $this->set('plan', $plan);
        $this->set('dealNameMap', $dealNameMap);
        $this->set('userNameMap', $userNameMap);
    }
    
}
