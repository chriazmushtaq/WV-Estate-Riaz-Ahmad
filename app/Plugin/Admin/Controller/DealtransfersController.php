<?php
App::uses('CakeNumber', 'Utility');
App::uses('Router', 'Routing');

class DealtransfersController extends AdminAppController
{
    public $helpers = array('Html', 'Form', 'Session', 'Paginator', 'Js' => array('Jquery'));
    public $components = array('Session', 'Paginator');
    public $paginate = array('page' => 1, 'order' => array('Dealtransfer.id' => 'desc'));

    protected $_transferTypes = array(
        'Transfer' => 'Transfer Deal',
        'Merge' => 'Merge Deal'
    );

    protected $_feeTypes = array(
        'Fixed' => 'Fixed Amount',
        'Percent' => 'Percentage',
        'Waived' => 'Waived',
        'Custom' => 'Custom Amount'
    );

    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->loadModel('Admin.Dealtransfer');
        $this->loadModel('Admin.Dealtransfersetting');
        if($this->_isSuperAdminSession((array)$this->adminValue))
        {
            $this->userPermissionArr = $this->_defaultFullPermission();
            $this->set('userPermissionArr', $this->userPermissionArr);
            $this->set('allowAddAction', true);
            $this->set('allowEditAction', true);
            $this->set('allowDeleteAction', true);
            $this->set('allowViewAction', true);
        }
        $runBootstrap = !$this->_transferTablesReady();
        if(method_exists($this, '_shouldRunControllerBootstrap'))
        {
            $runBootstrap = $runBootstrap || $this->_shouldRunControllerBootstrap('dealtransfers.schema', '2026-06-02.1');
        }
        if($runBootstrap)
        {
            $this->_ensureTransferSchema();
            $this->_ensureBusinessAccountingSchema();
            $this->_refreshTransferModels();
        }
    }

    public function index()
    {
        $conditions = array();
        if(!empty($this->request->query['type']) && isset($this->_transferTypes[$this->request->query['type']]))
        {
            $conditions['Dealtransfer.transfer_type'] = $this->request->query['type'];
        }
        if(!empty($this->request->query['status']))
        {
            $conditions['Dealtransfer.status'] = $this->request->query['status'];
        }
        if(!$this->_isSuperAdminSession((array)$this->adminValue))
        {
            $conditions[] = array('OR' => array(
                'Dealtransfer.source_project_id' => (int)$this->agencyId,
                'Dealtransfer.target_project_id' => (int)$this->agencyId
            ));
        }
        $this->Paginator->settings = $this->paginate;
        $this->Paginator->settings['conditions'] = $conditions;
        $this->Paginator->settings['recursive'] = 0;
        $this->Paginator->settings['limit'] = $this->pageLimit;
        $this->Paginator->settings['maxLimit'] = $this->maxLimit;
        $transferRows = $this->Paginator->paginate('Dealtransfer');
        foreach($transferRows as $idx => $transferRow)
        {
            $txId = !empty($transferRow['Dealtransfer']['fee_voucher_transaction_id']) ? (int)$transferRow['Dealtransfer']['fee_voucher_transaction_id'] : 0;
            $transferRows[$idx]['Dealtransfer']['fee_voucher_exists'] = $this->_voucherExists($txId) ? 1 : 0;
        }
        $statusOptions = array('Draft' => 'Draft', 'Finalized' => 'Finalized', 'Reversed' => 'Reversed', 'Rejected' => 'Rejected');
        $typeOptions = $this->_transferTypes;
        $isSuperAdminSession = $this->_isSuperAdminSession((array)$this->adminValue);
        $canEditDealTransferFee = ($isSuperAdminSession || !empty($this->userPermissionArr['update_right']));
        $this->set(compact('transferRows', 'statusOptions', 'typeOptions', 'isSuperAdminSession', 'canEditDealTransferFee'));
    }

    public function add()
    {
        $defaultSettings = $this->_settingsForProject((int)$this->agencyId, true);
        if($this->request->is('post'))
        {
            $save = $this->_buildTransferSaveData($this->request->data);
            if(empty($save['ok']))
            {
                $this->Session->setFlash($save['message'], 'flash', array('alert' => 'danger'));
            }
            else
            {
                $this->Dealtransfer->create();
                if($this->Dealtransfer->save(array('Dealtransfer' => $save['data']), array('validate' => false)))
                {
                    $id = (int)$this->Dealtransfer->id;
                    $this->Session->setFlash(__('Deal transfer request saved successfully.'), 'flash', array('alert' => 'success'));
                    if(!empty($this->request->data['Dealtransfer']['finalize_now']))
                    {
                        return $this->redirect(array('action' => 'finalize', $id));
                    }
                    return $this->redirect(array('action' => 'view', $id));
                }
                $this->Session->setFlash(__('Unable to save deal transfer request.'), 'flash', array('alert' => 'danger'));
            }
        }
        $this->_setFormVars($defaultSettings);
    }

    public function edit($id = null)
    {
        $id = (int)$id;
        $row = $this->Dealtransfer->find('first', array('conditions' => array('Dealtransfer.id' => $id), 'recursive' => -1));
        if(empty($row['Dealtransfer']['id']))
        {
            $this->Session->setFlash(__('Invalid deal transfer record.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        if($row['Dealtransfer']['status'] !== 'Draft')
        {
            $this->Session->setFlash(__('Only Draft transfer requests can be edited.'), 'flash', array('alert' => 'warning'));
            return $this->redirect(array('action' => 'view', $id));
        }
        if(!$this->_isAllowedProject((int)$row['Dealtransfer']['source_project_id']))
        {
            $this->Session->setFlash(__('No Permission!'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        $defaultSettings = $this->_settingsForProject((int)$row['Dealtransfer']['source_project_id'], true);
        if($this->request->is(array('post', 'put')))
        {
            $save = $this->_buildTransferSaveData($this->request->data, $id);
            if(empty($save['ok']))
            {
                $this->Session->setFlash($save['message'], 'flash', array('alert' => 'danger'));
            }
            else
            {
                $save['data']['id'] = $id;
                $save['data']['transfer_no'] = $row['Dealtransfer']['transfer_no'];
                if($this->Dealtransfer->save(array('Dealtransfer' => $save['data']), array('validate' => false)))
                {
                    $this->Session->setFlash(__('Deal transfer request updated successfully.'), 'flash', array('alert' => 'success'));
                    return $this->redirect(array('action' => 'view', $id));
                }
                $this->Session->setFlash(__('Unable to update deal transfer request.'), 'flash', array('alert' => 'danger'));
            }
        }
        else
        {
            $this->request->data = $row;
        }
        $this->_setFormVars($defaultSettings);
    }

    public function view($id = null)
    {
        $id = (int)$id;
        $transfer = $this->Dealtransfer->find('first', array('conditions' => array('Dealtransfer.id' => $id), 'recursive' => 0));
        if(empty($transfer['Dealtransfer']['id']))
        {
            $this->Session->setFlash(__('Invalid deal transfer record.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        if(!$this->_isAllowedProject((int)$transfer['Dealtransfer']['source_project_id']) && !$this->_isAllowedProject((int)$transfer['Dealtransfer']['target_project_id']))
        {
            $this->Session->setFlash(__('No Permission!'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        $sourceDeal = $this->_fetchDealDetails((int)$transfer['Dealtransfer']['source_deal_id']);
        $targetDeal = $this->_fetchDealDetails((int)$transfer['Dealtransfer']['target_deal_id']);
        $steps = $this->_decodeJson(isset($transfer['Dealtransfer']['steps_json']) ? $transfer['Dealtransfer']['steps_json'] : '');
        $snapshot = $this->_decodeJson(isset($transfer['Dealtransfer']['snapshot_json']) ? $transfer['Dealtransfer']['snapshot_json'] : '');
        $isSuperAdminSession = $this->_isSuperAdminSession((array)$this->adminValue);
        $this->set(compact('transfer', 'sourceDeal', 'targetDeal', 'steps', 'snapshot', 'isSuperAdminSession'));
    }

    public function settings()
    {
        $projectId = $this->_selectedSettingsProjectId();
        $settings = $this->_settingsForProject($projectId, true);
        if($this->request->is(array('post', 'put')))
        {
            $row = isset($this->request->data['Dealtransfersetting']) ? (array)$this->request->data['Dealtransfersetting'] : array();
            $save = array(
                'project_id' => $projectId,
                'module_enabled' => !empty($row['module_enabled']) ? 1 : 0,
                'transfer_enabled' => !empty($row['transfer_enabled']) ? 1 : 0,
                'merge_enabled' => !empty($row['merge_enabled']) ? 1 : 0,
                'agency_transfer_enabled' => !empty($row['agency_transfer_enabled']) ? 1 : 0,
                'same_client_merge_only' => !empty($row['same_client_merge_only']) ? 1 : 0,
                'require_reason' => !empty($row['require_reason']) ? 1 : 0,
                'create_fee_charge' => !empty($row['create_fee_charge']) ? 1 : 0,
                'create_fee_voucher' => !empty($row['create_fee_voucher']) ? 1 : 0,
                'fee_voucher_ledger_id' => max(0, (int)(isset($row['fee_voucher_ledger_id']) ? $row['fee_voucher_ledger_id'] : 0)),
                'default_fee_type' => isset($row['default_fee_type']) && isset($this->_feeTypes[$row['default_fee_type']]) ? $row['default_fee_type'] : 'Fixed',
                'default_transfer_fee' => max(0, (float)(isset($row['default_transfer_fee']) ? $row['default_transfer_fee'] : 0)),
                'default_merge_fee' => max(0, (float)(isset($row['default_merge_fee']) ? $row['default_merge_fee'] : 0)),
                'default_fee_percent' => max(0, (float)(isset($row['default_fee_percent']) ? $row['default_fee_percent'] : 0)),
                'notes' => isset($row['notes']) ? $row['notes'] : ''
            );
            if(!empty($settings['id'])) { $save['id'] = (int)$settings['id']; }
            $this->Dealtransfersetting->create();
            if($this->Dealtransfersetting->save(array('Dealtransfersetting' => $save), array('validate' => false)))
            {
                $this->Session->setFlash(__('Deal transfer settings saved successfully.'), 'flash', array('alert' => 'success'));
                return $this->redirect(array('action' => 'settings', '?' => array('project_id' => $projectId)));
            }
            $this->Session->setFlash(__('Unable to save deal transfer settings.'), 'flash', array('alert' => 'danger'));
        }
        else
        {
            $this->request->data = array('Dealtransfersetting' => $settings);
        }
        $agencyOptions = $this->_agencyOptions(false);
        $feeLedgerOptions = $this->_feeVoucherLedgerOptions($projectId);
        $feeTypes = $this->_feeTypes;
        $canUseAgencyFilters = $this->_isSuperAdminSession((array)$this->adminValue);
        $this->set(compact('projectId', 'agencyOptions', 'feeLedgerOptions', 'feeTypes', 'canUseAgencyFilters'));
    }

    public function finalize($id = null)
    {
        $id = (int)$id;
        $transfer = $this->Dealtransfer->find('first', array('conditions' => array('Dealtransfer.id' => $id), 'recursive' => -1));
        if(empty($transfer['Dealtransfer']['id']))
        {
            $this->Session->setFlash(__('Invalid deal transfer record.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        $row = $transfer['Dealtransfer'];
        if($row['status'] !== 'Draft')
        {
            $this->Session->setFlash(__('Only Draft transfer requests can be finalized.'), 'flash', array('alert' => 'warning'));
            return $this->redirect(array('action' => 'view', $id));
        }
        if(!$this->_isAllowedProject((int)$row['source_project_id']))
        {
            $this->Session->setFlash(__('No Permission!'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        $settings = $this->_settingsForProject((int)$row['source_project_id'], true);
        $allowed = $this->_validateAgainstSettings($row, $settings);
        if(empty($allowed['ok']))
        {
            $this->Session->setFlash($allowed['message'], 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'view', $id));
        }
        if((float)$row['processing_fee'] > 0 && !$this->_voucherExists(isset($row['fee_voucher_transaction_id']) ? (int)$row['fee_voucher_transaction_id'] : 0))
        {
            $this->Session->setFlash(__('Please pay the processing fee before finalizing this deal transfer.'), 'flash', array('alert' => 'warning'));
            return $this->redirect(array('action' => 'view', $id));
        }

        $ds = $this->Dealtransfer->getDataSource();
        $ds->begin();
        try
        {
            $this->loadModel('Admin.Deal');
            $this->loadModel('Admin.Property');
            $snapshot = $this->_decodeJson(isset($row['snapshot_json']) ? $row['snapshot_json'] : '');
            if($row['transfer_type'] === 'Transfer')
            {
                $this->Deal->id = (int)$row['source_deal_id'];
                if(!$this->Deal->save(array('Deal' => array('id' => (int)$row['source_deal_id'], 'client_id' => (int)$row['target_client_id'])), false, array('client_id')))
                {
                    throw new Exception(__('Unable to update deal client.'));
                }
                if((int)$row['target_project_id'] > 0 && (int)$row['target_project_id'] !== (int)$row['source_project_id'])
                {
                    $this->Property->id = (int)$row['source_property_id'];
                    if(!$this->Property->save(array('Property' => array('id' => (int)$row['source_property_id'], 'project_id' => (int)$row['target_project_id'])), false, array('project_id')))
                    {
                        throw new Exception(__('Unable to update deal agency.'));
                    }
                }
            }
            else
            {
                if((int)$row['target_deal_id'] <= 0)
                {
                    throw new Exception(__('Target deal is required for merge.'));
                }
                $snapshot['merge_summary_payment'] = $this->_createMergeSummaryPayment($row);
                $this->_markSourceDealMerged((int)$row['source_deal_id'], (int)$row['target_deal_id']);
            }

            $feeChargeId = 0;
            $feeVoucher = $this->_feeVoucherInfo(isset($row['fee_voucher_transaction_id']) ? (int)$row['fee_voucher_transaction_id'] : 0);
            if(!empty($settings['create_fee_charge']) && (float)$row['processing_fee'] > 0)
            {
                $chargeDealId = ($row['transfer_type'] === 'Merge' && (int)$row['target_deal_id'] > 0) ? (int)$row['target_deal_id'] : (int)$row['source_deal_id'];
                $chargeProjectId = ($row['transfer_type'] === 'Merge' && (int)$row['target_project_id'] > 0) ? (int)$row['target_project_id'] : (int)$row['source_project_id'];
                $feeChargeId = $this->_createProcessingFeeCharge($chargeDealId, $chargeProjectId, (float)$row['processing_fee']);
            }
            if((float)$row['processing_fee'] > 0 && empty($feeVoucher['id']))
            {
                throw new Exception(__('Please pay the processing fee before finalizing this deal transfer.'));
            }
            $this->Dealtransfer->id = $id;
            if(!$this->Dealtransfer->save(array('Dealtransfer' => array(
                'id' => $id,
                'status' => 'Finalized',
                'fee_charge_id' => $feeChargeId,
                'fee_voucher_transaction_id' => (int)$feeVoucher['id'],
                'steps_json' => json_encode($this->_finalizedSteps($row, $feeChargeId, $feeVoucher)),
                'snapshot_json' => json_encode($snapshot),
                'finalized_by' => (int)$this->luserId,
                'finalized_at' => date('Y-m-d H:i:s')
            )), array('validate' => false)))
            {
                throw new Exception(__('Unable to finalize transfer record.'));
            }
            $ds->commit();
            $this->Session->setFlash(__('Deal transfer finalized successfully.'), 'flash', array('alert' => 'success'));
        }
        catch(Exception $e)
        {
            $ds->rollback();
            $this->Session->setFlash($e->getMessage(), 'flash', array('alert' => 'danger'));
        }
        return $this->redirect(array('action' => 'view', $id));
    }

    public function reverse($id = null)
    {
        $id = (int)$id;
        $transfer = $this->Dealtransfer->find('first', array('conditions' => array('Dealtransfer.id' => $id), 'recursive' => -1));
        if(empty($transfer['Dealtransfer']['id']))
        {
            $this->Session->setFlash(__('Invalid deal transfer record.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        $row = $transfer['Dealtransfer'];
        if($row['status'] !== 'Finalized')
        {
            $this->Session->setFlash(__('Only finalized transfer requests can be reversed.'), 'flash', array('alert' => 'warning'));
            return $this->redirect(array('action' => 'view', $id));
        }
        if(!$this->_isSuperAdminSession((array)$this->adminValue))
        {
            $this->Session->setFlash(__('Only admin can reverse a finalized deal transfer.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'view', $id));
        }

        $ds = $this->Dealtransfer->getDataSource();
        $ds->begin();
        try
        {
            $this->loadModel('Admin.Deal');
            $this->loadModel('Admin.Property');
            $snapshot = $this->_decodeJson(isset($row['snapshot_json']) ? $row['snapshot_json'] : '');
            if($row['transfer_type'] === 'Transfer')
            {
                $this->Deal->id = (int)$row['source_deal_id'];
                if(!$this->Deal->save(array('Deal' => array('id' => (int)$row['source_deal_id'], 'client_id' => (int)$row['source_client_id'])), false, array('client_id')))
                {
                    throw new Exception(__('Unable to restore original deal client.'));
                }
                if((int)$row['source_property_id'] > 0)
                {
                    $this->Property->id = (int)$row['source_property_id'];
                    if(!$this->Property->save(array('Property' => array('id' => (int)$row['source_property_id'], 'project_id' => (int)$row['source_project_id'])), false, array('project_id')))
                    {
                        throw new Exception(__('Unable to restore original deal agency.'));
                    }
                }
            }
            else
            {
                $this->_restoreMergedDealRows($row, $snapshot);
                $this->_restoreSourceDealAfterMerge((int)$row['source_deal_id'], $snapshot);
            }

            $this->_reverseFeeCharge(isset($row['fee_charge_id']) ? (int)$row['fee_charge_id'] : 0);
            $this->_reverseFeeVoucher(isset($row['fee_voucher_transaction_id']) ? (int)$row['fee_voucher_transaction_id'] : 0);
            $steps = $this->_appendTransferStep($this->_decodeJson(isset($row['steps_json']) ? $row['steps_json'] : ''), ($row['transfer_type'] === 'Merge') ? 'Deal merge reversed by admin' : 'Deal transfer reversed by admin');
            $save = array(
                'id' => $id,
                'status' => 'Reversed',
                'fee_charge_id' => 0,
                'fee_voucher_transaction_id' => 0,
                'steps_json' => json_encode($steps)
            );
            if($this->Dealtransfer->hasField('reversed_by')) { $save['reversed_by'] = (int)$this->luserId; }
            if($this->Dealtransfer->hasField('reversed_at')) { $save['reversed_at'] = date('Y-m-d H:i:s'); }
            $this->Dealtransfer->id = $id;
            if(!$this->Dealtransfer->save(array('Dealtransfer' => $save), array('validate' => false)))
            {
                throw new Exception(__('Unable to mark transfer as reversed.'));
            }
            $ds->commit();
            $this->Session->setFlash(__('Deal transfer reversed successfully.'), 'flash', array('alert' => 'success'));
        }
        catch(Exception $e)
        {
            $ds->rollback();
            $this->Session->setFlash($e->getMessage(), 'flash', array('alert' => 'danger'));
        }
        return $this->redirect(array('action' => 'view', $id));
    }

    public function reject($id = null)
    {
        $id = (int)$id;
        $row = $this->Dealtransfer->find('first', array('conditions' => array('Dealtransfer.id' => $id), 'recursive' => -1));
        if(empty($row['Dealtransfer']['id']))
        {
            $this->Session->setFlash(__('Invalid deal transfer record.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        if($row['Dealtransfer']['status'] !== 'Draft')
        {
            $this->Session->setFlash(__('Only Draft transfer requests can be rejected.'), 'flash', array('alert' => 'warning'));
            return $this->redirect(array('action' => 'view', $id));
        }
        $this->Dealtransfer->id = $id;
        $this->Dealtransfer->save(array('Dealtransfer' => array('id' => $id, 'status' => 'Rejected')), false);
        $this->Session->setFlash(__('Deal transfer request rejected.'), 'flash', array('alert' => 'success'));
        return $this->redirect(array('action' => 'index'));
    }

    public function delete($id = null)
    {
        $id = (int)$id;
        $row = $this->Dealtransfer->find('first', array('conditions' => array('Dealtransfer.id' => $id), 'recursive' => -1));
        if(empty($row['Dealtransfer']['id']))
        {
            $this->Session->setFlash(__('Invalid deal transfer record.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        if(!in_array($row['Dealtransfer']['status'], array('Draft', 'Reversed'), true))
        {
            $this->Session->setFlash(__('Only Draft or Reversed transfer requests can be deleted.'), 'flash', array('alert' => 'warning'));
            return $this->redirect(array('action' => 'view', $id));
        }
        if($this->Dealtransfer->delete($id))
        {
            $this->Session->setFlash(__('Deal transfer request deleted.'), 'flash', array('alert' => 'success'));
        }
        return $this->redirect(array('action' => 'index'));
    }

    public function updatefee($id = null)
    {
        $id = (int)$id;
        if(!$this->request->is(array('post', 'put')))
        {
            $this->Session->setFlash(__('Invalid fee update request.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        $transfer = $this->Dealtransfer->find('first', array('conditions' => array('Dealtransfer.id' => $id), 'recursive' => -1));
        if(empty($transfer['Dealtransfer']['id']))
        {
            $this->Session->setFlash(__('Invalid deal transfer record.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        $isSuperAdminSession = $this->_isSuperAdminSession((array)$this->adminValue);
        $canEditDealTransferFee = ($isSuperAdminSession || !empty($this->userPermissionArr['update_right']));
        if(!$canEditDealTransferFee)
        {
            $this->Session->setFlash(__('You do not have permission to edit deal transfer fees.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        $row = $transfer['Dealtransfer'];
        if(!$isSuperAdminSession && !$this->_isAllowedProject((int)$row['source_project_id']) && !$this->_isAllowedProject((int)$row['target_project_id']))
        {
            $this->Session->setFlash(__('No Permission!'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        if($row['status'] !== 'Draft')
        {
            $this->Session->setFlash(__('Only Draft transfer fees can be edited.'), 'flash', array('alert' => 'warning'));
            return $this->redirect(array('action' => 'view', $id));
        }
        if($this->_voucherExists(isset($row['fee_voucher_transaction_id']) ? (int)$row['fee_voucher_transaction_id'] : 0))
        {
            $this->Session->setFlash(__('Processing fee is already paid, so fee editing is locked.'), 'flash', array('alert' => 'warning'));
            return $this->redirect(array('action' => 'view', $id));
        }
        $data = isset($this->request->data['Dealtransfer']) ? (array)$this->request->data['Dealtransfer'] : (array)$this->request->data;
        $rawFee = isset($data['processing_fee']) ? str_replace(',', '', (string)$data['processing_fee']) : '0';
        $fee = round(max(0, (float)$rawFee), 2);
        $steps = $this->_appendTransferStep($this->_decodeJson(isset($row['steps_json']) ? $row['steps_json'] : ''), 'Processing fee updated by admin to '.number_format($fee, 2, '.', ''));
        $this->Dealtransfer->id = $id;
        if($this->Dealtransfer->save(array('Dealtransfer' => array(
            'id' => $id,
            'processing_fee_type' => 'Custom',
            'processing_fee_percent' => 0,
            'processing_fee' => $fee,
            'steps_json' => json_encode($steps)
        )), false, array('processing_fee_type', 'processing_fee_percent', 'processing_fee', 'steps_json')))
        {
            $this->Session->setFlash(__('Processing fee updated. Unpaid voucher will print with the new amount.'), 'flash', array('alert' => 'success'));
        }
        else
        {
            $this->Session->setFlash(__('Unable to update processing fee.'), 'flash', array('alert' => 'danger'));
        }
        return $this->redirect($this->referer(array('action' => 'index'), true));
    }

    public function createfeevoucher($id = null)
    {
        $id = (int)$id;
        $transfer = $this->Dealtransfer->find('first', array('conditions' => array('Dealtransfer.id' => $id), 'recursive' => -1));
        if(empty($transfer['Dealtransfer']['id']))
        {
            $this->Session->setFlash(__('Invalid deal transfer record.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        $row = $transfer['Dealtransfer'];
        if(!$this->_isAllowedProject((int)$row['source_project_id']) && !$this->_isAllowedProject((int)$row['target_project_id']))
        {
            $this->Session->setFlash(__('No Permission!'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        if($row['status'] === 'Rejected')
        {
            $this->Session->setFlash(__('Rejected transfer requests cannot create a fee voucher.'), 'flash', array('alert' => 'warning'));
            return $this->redirect(array('action' => 'view', $id));
        }
        if((float)$row['processing_fee'] <= 0)
        {
            $this->Session->setFlash(__('This transfer has no processing fee to voucher.'), 'flash', array('alert' => 'warning'));
            return $this->redirect(array('action' => 'view', $id));
        }
        if($this->_voucherExists(isset($row['fee_voucher_transaction_id']) ? (int)$row['fee_voucher_transaction_id'] : 0))
        {
            $this->Session->setFlash(__('Processing fee voucher already exists.'), 'flash', array('alert' => 'info'));
            return $this->redirect(array('action' => 'view', $id));
        }
        $settings = $this->_settingsForProject((int)$row['source_project_id'], true);
        $ds = $this->Dealtransfer->getDataSource();
        $ds->begin();
        try
        {
            $feeVoucher = $this->_createProcessingFeeVoucher($row, $settings);
            if(empty($feeVoucher['id']))
            {
                throw new Exception(__('Unable to create processing fee voucher.'));
            }
            $row['fee_voucher_transaction_id'] = (int)$feeVoucher['id'];
            $steps = ($row['status'] === 'Finalized') ? $this->_finalizedSteps($row, (int)$row['fee_charge_id'], $feeVoucher) : $this->_feeVoucherCreatedSteps($row, $feeVoucher);
            $this->Dealtransfer->id = $id;
            if(!$this->Dealtransfer->save(array('Dealtransfer' => array(
                'id' => $id,
                'fee_voucher_transaction_id' => (int)$feeVoucher['id'],
                'steps_json' => json_encode($steps)
            )), false, array('fee_voucher_transaction_id', 'steps_json')))
            {
                throw new Exception(__('Unable to link processing fee voucher.'));
            }
            $ds->commit();
            $this->Session->setFlash(__('Processing fee payment voucher created successfully.'), 'flash', array('alert' => 'success'));
        }
        catch(Exception $e)
        {
            $ds->rollback();
            $this->Session->setFlash($e->getMessage(), 'flash', array('alert' => 'danger'));
        }
        return $this->redirect(array('action' => 'view', $id));
    }

    public function payfeevoucher($id = null)
    {
        return $this->createfeevoucher($id);
    }

    public function printfeevoucher($id = null)
    {
        $id = (int)$id;
        $transfer = $this->Dealtransfer->find('first', array('conditions' => array('Dealtransfer.id' => $id), 'recursive' => -1));
        if(empty($transfer['Dealtransfer']['id']))
        {
            $this->Session->setFlash(__('Invalid deal transfer record.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        $row = $transfer['Dealtransfer'];
        if(!$this->_isAllowedProject((int)$row['source_project_id']) && !$this->_isAllowedProject((int)$row['target_project_id']))
        {
            $this->Session->setFlash(__('No Permission!'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        if((float)$row['processing_fee'] <= 0)
        {
            $this->Session->setFlash(__('This transfer has no processing fee to print.'), 'flash', array('alert' => 'warning'));
            return $this->redirect(array('action' => 'view', $id));
        }
        $sourceDeal = $this->_fetchDealDetails((int)$row['source_deal_id']);
        $targetDeal = $this->_fetchDealDetails((int)$row['target_deal_id']);
        $payerClient = $this->_fetchAgreementClient((int)$row['target_client_id']);
        $sellerClient = $this->_fetchAgreementClient((int)$row['source_client_id']);
        $voucherProjectId = (!empty($row['transfer_type']) && $row['transfer_type'] === 'Merge' && !empty($row['target_project_id'])) ? (int)$row['target_project_id'] : (int)$row['source_project_id'];
        $agencyInfo = $this->_agreementAgencyInfo($voucherProjectId);
        $feeVoucherNo = 'DTV-'.preg_replace('/[^A-Za-z0-9\-]/', '', !empty($row['transfer_no']) ? (string)$row['transfer_no'] : (string)$id);
        if(strlen($feeVoucherNo) > 30) { $feeVoucherNo = substr($feeVoucherNo, 0, 30); }
        $feePaid = $this->_voucherExists(isset($row['fee_voucher_transaction_id']) ? (int)$row['fee_voucher_transaction_id'] : 0);
        $voucherDate = !empty($row['request_date']) ? date('d-M-Y', strtotime($row['request_date'])) : date('d-M-Y');
        $this->layout = null;
        $this->set(compact('transfer', 'sourceDeal', 'targetDeal', 'payerClient', 'sellerClient', 'agencyInfo', 'feeVoucherNo', 'feePaid', 'voucherDate'));
    }

    public function dealmeta($dealId = null)
    {
        $this->autoRender = false;
        $this->response->type('json');
        $deal = $this->_fetchDealDetails((int)$dealId);
        if(empty($deal['Deal']['id'])) { return json_encode(array('ok' => false)); }
        $projectId = (int)$deal['Property']['project_id'];
        if(!$this->_isAllowedProject($projectId)) { return json_encode(array('ok' => false)); }
        $paid = $this->_dealTotalPaid((int)$deal['Deal']['id']);
        $total = isset($deal['Deal']['total_amount']) ? (float)$deal['Deal']['total_amount'] : 0;
        $settings = $this->_settingsForProject($projectId, true);
        return json_encode(array(
            'ok' => true,
            'deal_id' => (int)$deal['Deal']['id'],
            'client_id' => (int)$deal['Deal']['client_id'],
            'client_name' => isset($deal['Client']['name']) ? $deal['Client']['name'] : '',
            'project_id' => $projectId,
            'property_id' => (int)$deal['Deal']['property_id'],
            'total_amount' => $total,
            'paid_amount' => $paid,
            'balance_amount' => max(0, $total - $paid),
            'default_fee_type' => $settings['default_fee_type'],
            'default_transfer_fee' => (float)$settings['default_transfer_fee'],
            'default_merge_fee' => (float)$settings['default_merge_fee'],
            'default_fee_percent' => (float)$settings['default_fee_percent']
        ));
    }

    public function agreement($id = null)
    {
        $id = (int)$id;
        $transfer = $this->Dealtransfer->find('first', array('conditions' => array('Dealtransfer.id' => $id), 'recursive' => -1));
        if(empty($transfer['Dealtransfer']['id']))
        {
            $this->Session->setFlash(__('Invalid deal transfer record.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        $row = $transfer['Dealtransfer'];
        if($row['status'] !== 'Finalized')
        {
            $this->Session->setFlash(__('Finalize the transfer before printing the agreement.'), 'flash', array('alert' => 'warning'));
            return $this->redirect(array('action' => 'view', $id));
        }
        if(!$this->_isAllowedProject((int)$row['source_project_id']) && !$this->_isAllowedProject((int)$row['target_project_id']))
        {
            $this->Session->setFlash(__('No Permission!'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        $sourceDeal = $this->_fetchAgreementDealDetails((int)$row['source_deal_id']);
        $targetDeal = $this->_fetchAgreementDealDetails((int)$row['target_deal_id']);
        if(empty($sourceDeal['Deal']['id']))
        {
            $this->Session->setFlash(__('Source deal not found.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'view', $id));
        }
        $transferor = $this->_fetchAgreementClient((int)$row['source_client_id']);
        if(empty($transferor['Client']['id']) && !empty($sourceDeal['Client']['id'])) { $transferor = array('Client' => $sourceDeal['Client']); }
        $transferee = $this->_fetchAgreementClient((int)$row['target_client_id']);
        if(empty($transferee['Client']['id']) && !empty($targetDeal['Client']['id'])) { $transferee = array('Client' => $targetDeal['Client']); }
        $agreementDeal = ($row['transfer_type'] === 'Merge' && !empty($targetDeal['Deal']['id'])) ? $targetDeal : $sourceDeal;
        $agreementProjectId = !empty($row['target_project_id']) ? (int)$row['target_project_id'] : (int)$row['source_project_id'];
        $agencyInfo = $this->_agreementAgencyInfo($agreementProjectId);
        $paymentSummary = $this->_agreementPaymentSummary((int)$agreementDeal['Deal']['id'], $agreementDeal);
        $agreementTitle = ($row['transfer_type'] === 'Merge') ? __('DEAL MERGE AGREEMENT') : __('DEAL TRANSFER AGREEMENT');
        $requestDate = !empty($row['request_date']) ? date('F d, Y', strtotime($row['request_date'])) : date('F d, Y');
        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=160x160&data='.rawurlencode($this->_agreementQrText($row, $transferor, $transferee, $agreementDeal, $paymentSummary, $agreementTitle));
        $agreementImages = array(
            'transferor_photo' => $this->_agreementClientPhotoUrl($transferor),
            'transferee_photo' => $this->_agreementClientPhotoUrl($transferee),
            'transferor_id_front' => $this->_agreementClientDocUrl($transferor, 'id_front', 'id_front_dir', 'contact_id_front'),
            'transferee_id_front' => $this->_agreementClientDocUrl($transferee, 'id_front', 'id_front_dir', 'contact_id_front')
        );
        $this->layout = null;
        $this->set(compact('transfer', 'sourceDeal', 'targetDeal', 'agreementDeal', 'transferor', 'transferee', 'agencyInfo', 'paymentSummary', 'agreementTitle', 'requestDate', 'agreementImages', 'qrUrl'));
    }

    protected function _setFormVars($defaultSettings = array())
    {
        $dealOptions = $this->_dealOptions();
        $clientOptions = $this->_clientOptions();
        $agencyOptions = $this->_agencyOptions(false);
        $typeOptions = $this->_transferTypes;
        $feeTypes = $this->_feeTypes;
        $this->set(compact('dealOptions', 'clientOptions', 'agencyOptions', 'typeOptions', 'feeTypes', 'defaultSettings'));
    }

    protected function _buildTransferSaveData($post, $id = 0)
    {
        $row = isset($post['Dealtransfer']) ? (array)$post['Dealtransfer'] : array();
        $type = isset($row['transfer_type']) && isset($this->_transferTypes[$row['transfer_type']]) ? $row['transfer_type'] : 'Transfer';
        $sourceDeal = $this->_fetchDealDetails(isset($row['source_deal_id']) ? (int)$row['source_deal_id'] : 0);
        if(empty($sourceDeal['Deal']['id'])) { return array('ok' => false, 'message' => __('Source deal is required.')); }
        $sourceProjectId = (int)$sourceDeal['Property']['project_id'];
        if(!$this->_isAllowedProject($sourceProjectId)) { return array('ok' => false, 'message' => __('No Permission!')); }
        $settings = $this->_settingsForProject($sourceProjectId, true);
        if(empty($settings['module_enabled'])) { return array('ok' => false, 'message' => __('Deal Transfer module is disabled for this agency.')); }
        $targetDeal = array();
        if($type === 'Merge')
        {
            if(empty($settings['merge_enabled'])) { return array('ok' => false, 'message' => __('Deal merge is disabled for this agency.')); }
            $targetDeal = $this->_fetchDealDetails(isset($row['target_deal_id']) ? (int)$row['target_deal_id'] : 0);
            if(empty($targetDeal['Deal']['id'])) { return array('ok' => false, 'message' => __('Target deal is required for merge.')); }
            $targetClientId = (int)$targetDeal['Deal']['client_id'];
            $targetProjectId = (int)$targetDeal['Property']['project_id'];
            $targetPropertyId = (int)$targetDeal['Deal']['property_id'];
        }
        else
        {
            if(empty($settings['transfer_enabled'])) { return array('ok' => false, 'message' => __('Deal transfer is disabled for this agency.')); }
            $targetClientId = !empty($row['target_client_id']) ? (int)$row['target_client_id'] : (int)$sourceDeal['Deal']['client_id'];
            $targetProjectId = !empty($row['target_project_id']) ? (int)$row['target_project_id'] : $sourceProjectId;
            $targetPropertyId = (int)$sourceDeal['Deal']['property_id'];
        }
        $check = $this->_validateAgainstSettings(array(
            'transfer_type' => $type,
            'source_client_id' => (int)$sourceDeal['Deal']['client_id'],
            'target_client_id' => $targetClientId,
            'source_project_id' => $sourceProjectId,
            'target_project_id' => $targetProjectId,
            'reason' => isset($row['reason']) ? $row['reason'] : ''
        ), $settings);
        if(empty($check['ok'])) { return $check; }
        $total = isset($sourceDeal['Deal']['total_amount']) ? (float)$sourceDeal['Deal']['total_amount'] : 0.0;
        $paid = $this->_dealTotalPaid((int)$sourceDeal['Deal']['id']);
        $feeType = isset($row['processing_fee_type']) && isset($this->_feeTypes[$row['processing_fee_type']]) ? $row['processing_fee_type'] : $settings['default_fee_type'];
        $feePercent = max(0, (float)(isset($row['processing_fee_percent']) ? $row['processing_fee_percent'] : 0));
        $fee = max(0, (float)(isset($row['processing_fee']) ? $row['processing_fee'] : 0));
        if($feeType === 'Waived') { $fee = 0; $feePercent = 0; }
        if($feeType === 'Percent') { $fee = round(($total * $feePercent) / 100, 2); }
        if($fee <= 0 && $feeType === 'Fixed') { $fee = (float)($type === 'Merge' ? $settings['default_merge_fee'] : $settings['default_transfer_fee']); }
        $requestDate = $this->_normalizeDate(isset($row['request_date']) ? $row['request_date'] : date('Y-m-d'));
        $data = array(
            'transfer_no' => !empty($row['transfer_no']) ? $row['transfer_no'] : $this->_nextTransferNo(),
            'transfer_type' => $type,
            'source_deal_id' => (int)$sourceDeal['Deal']['id'],
            'target_deal_id' => !empty($targetDeal['Deal']['id']) ? (int)$targetDeal['Deal']['id'] : 0,
            'source_client_id' => (int)$sourceDeal['Deal']['client_id'],
            'target_client_id' => $targetClientId,
            'source_project_id' => $sourceProjectId,
            'target_project_id' => $targetProjectId,
            'source_property_id' => (int)$sourceDeal['Deal']['property_id'],
            'target_property_id' => $targetPropertyId,
            'deal_total_amount' => $total,
            'deal_paid_amount' => $paid,
            'deal_balance_amount' => max(0, $total - $paid),
            'processing_fee_type' => $feeType,
            'processing_fee_percent' => $feePercent,
            'processing_fee' => $fee,
            'status' => 'Draft',
            'request_date' => $requestDate,
            'reason' => isset($row['reason']) ? trim((string)$row['reason']) : '',
            'steps_json' => json_encode($this->_defaultSteps($type, $fee)),
            'snapshot_json' => json_encode(array('sourceDeal' => $sourceDeal, 'targetDeal' => $targetDeal)),
            'created_by' => (int)$this->luserId
        );
        if($id > 0) { unset($data['created_by']); }
        return array('ok' => true, 'data' => $data);
    }

    protected function _validateAgainstSettings($row, $settings)
    {
        $row = (array)$row;
        $settings = (array)$settings;
        if(empty($settings['module_enabled'])) { return array('ok' => false, 'message' => __('Deal Transfer module is disabled for this agency.')); }
        if((isset($row['transfer_type']) ? $row['transfer_type'] : '') === 'Transfer' && empty($settings['transfer_enabled'])) { return array('ok' => false, 'message' => __('Deal transfer is disabled for this agency.')); }
        if((isset($row['transfer_type']) ? $row['transfer_type'] : '') === 'Merge' && empty($settings['merge_enabled'])) { return array('ok' => false, 'message' => __('Deal merge is disabled for this agency.')); }
        if(!empty($settings['require_reason']) && trim((string)(isset($row['reason']) ? $row['reason'] : '')) === '') { return array('ok' => false, 'message' => __('Reason is required.')); }
        if((int)(isset($row['source_project_id']) ? $row['source_project_id'] : 0) !== (int)(isset($row['target_project_id']) ? $row['target_project_id'] : 0) && empty($settings['agency_transfer_enabled']))
        {
            return array('ok' => false, 'message' => __('Agency to agency transfer is disabled for this agency.'));
        }
        if((isset($row['transfer_type']) ? $row['transfer_type'] : '') === 'Merge' && !empty($settings['same_client_merge_only']) && (int)(isset($row['source_client_id']) ? $row['source_client_id'] : 0) !== (int)(isset($row['target_client_id']) ? $row['target_client_id'] : 0))
        {
            return array('ok' => false, 'message' => __('Merge is allowed only for same client.'));
        }
        return array('ok' => true);
    }

    protected function _transferTablesReady()
    {
        try
        {
            return !empty($this->Dealtransfer->query("SHOW TABLES LIKE 'deal_transfers'")) && !empty($this->Dealtransfer->query("SHOW TABLES LIKE 'deal_transfer_settings'"));
        }
        catch(Exception $e) { return false; }
    }

    protected function _ensureTransferSchema()
    {
        $sql = array(
            "CREATE TABLE IF NOT EXISTS `deal_transfer_settings` (
                `id` bigint(20) NOT NULL AUTO_INCREMENT,
                `project_id` int(11) NOT NULL DEFAULT 0,
                `module_enabled` tinyint(1) NOT NULL DEFAULT 1,
                `transfer_enabled` tinyint(1) NOT NULL DEFAULT 1,
                `merge_enabled` tinyint(1) NOT NULL DEFAULT 1,
                `agency_transfer_enabled` tinyint(1) NOT NULL DEFAULT 1,
                `same_client_merge_only` tinyint(1) NOT NULL DEFAULT 1,
                `require_reason` tinyint(1) NOT NULL DEFAULT 1,
                `create_fee_charge` tinyint(1) NOT NULL DEFAULT 1,
                `create_fee_voucher` tinyint(1) NOT NULL DEFAULT 1,
                `fee_voucher_ledger_id` bigint(20) NOT NULL DEFAULT 0,
                `default_fee_type` varchar(20) NOT NULL DEFAULT 'Fixed',
                `default_transfer_fee` decimal(15,2) NOT NULL DEFAULT 0.00,
                `default_merge_fee` decimal(15,2) NOT NULL DEFAULT 0.00,
                `default_fee_percent` decimal(8,2) NOT NULL DEFAULT 0.00,
                `notes` text,
                `created` datetime DEFAULT NULL,
                `modified` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `project_id` (`project_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3",
            "CREATE TABLE IF NOT EXISTS `deal_transfers` (
                `id` bigint(20) NOT NULL AUTO_INCREMENT,
                `transfer_no` varchar(40) DEFAULT '',
                `transfer_type` varchar(20) NOT NULL DEFAULT 'Transfer',
                `source_deal_id` bigint(20) NOT NULL DEFAULT 0,
                `target_deal_id` bigint(20) NOT NULL DEFAULT 0,
                `source_client_id` bigint(20) NOT NULL DEFAULT 0,
                `target_client_id` bigint(20) NOT NULL DEFAULT 0,
                `source_project_id` int(11) NOT NULL DEFAULT 0,
                `target_project_id` int(11) NOT NULL DEFAULT 0,
                `source_property_id` bigint(20) NOT NULL DEFAULT 0,
                `target_property_id` bigint(20) NOT NULL DEFAULT 0,
                `deal_total_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
                `deal_paid_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
                `deal_balance_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
                `processing_fee_type` varchar(20) NOT NULL DEFAULT 'Fixed',
                `processing_fee_percent` decimal(8,2) NOT NULL DEFAULT 0.00,
                `processing_fee` decimal(15,2) NOT NULL DEFAULT 0.00,
                `status` varchar(20) NOT NULL DEFAULT 'Draft',
                `request_date` date DEFAULT NULL,
                `reason` text,
                `steps_json` longtext,
                `snapshot_json` longtext,
                `fee_charge_id` bigint(20) NOT NULL DEFAULT 0,
                `fee_voucher_transaction_id` bigint(20) NOT NULL DEFAULT 0,
                `created_by` int(11) NOT NULL DEFAULT 0,
                `finalized_by` int(11) NOT NULL DEFAULT 0,
                `finalized_at` datetime DEFAULT NULL,
                `reversed_by` int(11) NOT NULL DEFAULT 0,
                `reversed_at` datetime DEFAULT NULL,
                `created` datetime DEFAULT NULL,
                `modified` datetime DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3"
        );
        foreach($sql as $q) { try { $this->Dealtransfer->query($q); } catch(Exception $e) {} }
        foreach(array(
            "ALTER TABLE `deal_transfer_settings` ADD COLUMN `create_fee_voucher` tinyint(1) NOT NULL DEFAULT 1 AFTER `create_fee_charge`",
            "ALTER TABLE `deal_transfer_settings` ADD COLUMN `fee_voucher_ledger_id` bigint(20) NOT NULL DEFAULT 0 AFTER `create_fee_voucher`",
            "ALTER TABLE `deal_transfers` ADD COLUMN `fee_voucher_transaction_id` bigint(20) NOT NULL DEFAULT 0 AFTER `fee_charge_id`",
            "ALTER TABLE `deal_transfers` ADD COLUMN `reversed_by` int(11) NOT NULL DEFAULT 0 AFTER `finalized_at`",
            "ALTER TABLE `deal_transfers` ADD COLUMN `reversed_at` datetime DEFAULT NULL AFTER `reversed_by`"
        ) as $q) { try { $this->Dealtransfer->query($q); } catch(Exception $e) {} }
    }

    protected function _refreshTransferModels()
    {
        try { $this->Dealtransfer->schema(true); } catch(Exception $e) {}
        try { $this->Dealtransfersetting->schema(true); } catch(Exception $e) {}
    }

    protected function _dealOptions()
    {
        $this->loadModel('Admin.Deal');
        $conditions = array();
        if(!$this->_isSuperAdminSession((array)$this->adminValue))
        {
            $conditions['Property.project_id'] = (int)$this->agencyId;
        }
        $rows = $this->Deal->find('all', array(
            'fields' => array('Deal.id', 'Deal.invoice_no', 'Client.name', 'Property.name', 'Property.project_id'),
            'joins' => array(
                array('table' => 'clients', 'alias' => 'Client', 'type' => 'LEFT', 'conditions' => array('Client.id = Deal.client_id')),
                array('table' => 'properties', 'alias' => 'Property', 'type' => 'LEFT', 'conditions' => array('Property.id = Deal.property_id'))
            ),
            'conditions' => $conditions,
            'order' => array('Deal.id' => 'desc'),
            'recursive' => -1,
            'limit' => 5000
        ));
        $options = array();
        foreach((array)$rows as $r)
        {
            $parts = array();
            if(!empty($r['Client']['name'])) { $parts[] = $r['Client']['name']; }
            if(!empty($r['Deal']['invoice_no'])) { $parts[] = $r['Deal']['invoice_no']; }
            $parts[] = '';
            if(!empty($r['Property']['name'])) { $parts[] = $r['Property']['name']; }
            if(!empty($r['Property']['project_id'])) { $parts[] = 'Agency #'.(int)$r['Property']['project_id']; }
            $options[(int)$r['Deal']['id']] = implode(' | ', $parts);
        }
        return $options;
    }

    protected function _clientOptions()
    {
        $this->loadModel('Client');
        $conditions = array();
        if(!$this->_isSuperAdminSession((array)$this->adminValue) && $this->Client->hasField('project_id')) { $conditions['Client.project_id'] = (int)$this->agencyId; }
        return (array)$this->Client->find('list', array('fields' => array('Client.id', 'Client.name'), 'conditions' => $conditions, 'order' => array('Client.name' => 'asc'), 'recursive' => -1));
    }

    protected function _agencyOptions($includeAll = false)
    {
        $this->loadModel('Admin.Agency');
        $conditions = array();
        if(!$includeAll && !$this->_isSuperAdminSession((array)$this->adminValue)) { $conditions['Agency.id'] = (int)$this->agencyId; }
        return (array)$this->Agency->find('list', array('fields' => array('Agency.id', 'Agency.name'), 'conditions' => $conditions, 'order' => array('Agency.name' => 'asc'), 'recursive' => -1));
    }

    protected function _fetchDealDetails($dealId)
    {
        $this->loadModel('Admin.Deal');
        $dealId = (int)$dealId;
        if($dealId <= 0) { return array(); }
        return (array)$this->Deal->find('first', array(
            'fields' => array('Deal.id', 'Deal.invoice_no', 'Deal.client_id', 'Deal.property_id', 'Deal.total_amount', 'Deal.deal_status', 'Client.id', 'Client.name', 'Client.phone', 'Client.mobile', 'Client.email', 'Client.pincode', 'Property.id', 'Property.name', 'Property.project_id', 'Property.price', 'Property.status'),
            'joins' => array(
                array('table' => 'clients', 'alias' => 'Client', 'type' => 'LEFT', 'conditions' => array('Client.id = Deal.client_id')),
                array('table' => 'properties', 'alias' => 'Property', 'type' => 'LEFT', 'conditions' => array('Property.id = Deal.property_id'))
            ),
            'conditions' => array('Deal.id' => $dealId),
            'recursive' => -1
        ));
    }

    protected function _fetchAgreementDealDetails($dealId)
    {
        $this->loadModel('Admin.Deal');
        $dealId = (int)$dealId;
        if($dealId <= 0) { return array(); }
        return (array)$this->Deal->find('first', array(
            'fields' => array(
                'Deal.id', 'Deal.invoice_no', 'Deal.client_id', 'Deal.property_id', 'Deal.total_amount', 'Deal.booking_amount', 'Deal.discount', 'Deal.date', 'Deal.recurring', 'Deal.deal_status',
                'Client.id', 'Client.name', 'Client.fathername', 'Client.phone', 'Client.mobile', 'Client.email', 'Client.pincode', 'Client.address', 'Client.photo', 'Client.dirt', 'Client.id_front', 'Client.id_front_dir', 'Client.id_back', 'Client.id_back_dir',
                'Property.id', 'Property.name', 'Property.project_id', 'Property.price', 'Property.status', 'Property.area', 'Property.type_id', 'Property.unit_id', 'Property.contract_id', 'Property.location_id',
                'Project.id', 'Project.name', 'Project.phone', 'Project.mobile', 'Project.email', 'Type.id', 'Type.name', 'Unit.id', 'Unit.name', 'Contract.id', 'Contract.name', 'Location.id', 'Location.name'
            ),
            'joins' => array(
                array('table' => 'clients', 'alias' => 'Client', 'type' => 'LEFT', 'conditions' => array('Client.id = Deal.client_id')),
                array('table' => 'properties', 'alias' => 'Property', 'type' => 'LEFT', 'conditions' => array('Property.id = Deal.property_id')),
                array('table' => 'projects', 'alias' => 'Project', 'type' => 'LEFT', 'conditions' => array('Project.id = Property.project_id')),
                array('table' => 'types', 'alias' => 'Type', 'type' => 'LEFT', 'conditions' => array('Type.id = Property.type_id')),
                array('table' => 'units', 'alias' => 'Unit', 'type' => 'LEFT', 'conditions' => array('Unit.id = Property.unit_id')),
                array('table' => 'contracts', 'alias' => 'Contract', 'type' => 'LEFT', 'conditions' => array('Contract.id = Property.contract_id')),
                array('table' => 'locations', 'alias' => 'Location', 'type' => 'LEFT', 'conditions' => array('Location.id = Property.location_id'))
            ),
            'conditions' => array('Deal.id' => $dealId),
            'recursive' => -1
        ));
    }

    protected function _fetchAgreementClient($clientId)
    {
        $this->loadModel('Client');
        $clientId = (int)$clientId;
        if($clientId <= 0) { return array(); }
        return (array)$this->Client->find('first', array(
            'fields' => array('Client.id', 'Client.name', 'Client.fathername', 'Client.phone', 'Client.mobile', 'Client.email', 'Client.pincode', 'Client.address', 'Client.photo', 'Client.dirt', 'Client.id_front', 'Client.id_front_dir', 'Client.id_back', 'Client.id_back_dir'),
            'conditions' => array('Client.id' => $clientId),
            'recursive' => -1
        ));
    }

    protected function _dealTotalPaid($dealId)
    {
        $this->loadModel('Admin.DealsPayment');
        $row = $this->DealsPayment->find('first', array('fields' => array('SUM(DealsPayment.emi) AS paid_total'), 'conditions' => array('DealsPayment.deal_id' => (int)$dealId), 'recursive' => -1));
        return isset($row[0]['paid_total']) ? (float)$row[0]['paid_total'] : 0.0;
    }

    protected function _settingsForProject($projectId, $create = false)
    {
        $projectId = (int)$projectId;
        if($projectId <= 0) { $projectId = (int)$this->agencyId; }
        $defaults = array('project_id' => $projectId, 'module_enabled' => 1, 'transfer_enabled' => 1, 'merge_enabled' => 1, 'agency_transfer_enabled' => 1, 'same_client_merge_only' => 1, 'require_reason' => 1, 'create_fee_charge' => 1, 'create_fee_voucher' => 1, 'fee_voucher_ledger_id' => 0, 'default_fee_type' => 'Fixed', 'default_transfer_fee' => 0, 'default_merge_fee' => 0, 'default_fee_percent' => 0, 'notes' => '');
        $row = $this->Dealtransfersetting->find('first', array('conditions' => array('Dealtransfersetting.project_id' => $projectId), 'recursive' => -1));
        if(!empty($row['Dealtransfersetting'])) { return array_merge($defaults, $row['Dealtransfersetting']); }
        if($create)
        {
            $this->Dealtransfersetting->create();
            $this->Dealtransfersetting->save(array('Dealtransfersetting' => $defaults), array('validate' => false));
            $defaults['id'] = (int)$this->Dealtransfersetting->id;
        }
        return $defaults;
    }

    protected function _selectedSettingsProjectId()
    {
        if($this->_isSuperAdminSession((array)$this->adminValue))
        {
            $projectId = isset($this->request->query['project_id']) ? (int)$this->request->query['project_id'] : 0;
            if($projectId > 0) { return $projectId; }
            foreach($this->_agencyOptions(false) as $id => $name) { return (int)$id; }
        }
        return (int)$this->agencyId;
    }

    protected function _mergeDealRows($sourceDealId, $targetDealId, $targetPropertyId)
    {
        $this->loadModel('Admin.DealsPayment');
        $this->loadModel('Admin.PlansPayment');
        $this->DealsPayment->updateAll(array('DealsPayment.deal_id' => (int)$targetDealId, 'DealsPayment.property_id' => (int)$targetPropertyId), array('DealsPayment.deal_id' => (int)$sourceDealId));
        $this->PlansPayment->updateAll(array('PlansPayment.deal_id' => (int)$targetDealId), array('PlansPayment.deal_id' => (int)$sourceDealId));
    }

    protected function _createMergeSummaryPayment($row)
    {
        $this->loadModel('Admin.DealsPayment');
        $this->loadModel('Admin.PlansPayment');
        $row = (array)$row;
        $sourceDealId = isset($row['source_deal_id']) ? (int)$row['source_deal_id'] : 0;
        $targetDealId = isset($row['target_deal_id']) ? (int)$row['target_deal_id'] : 0;
        $targetPropertyId = isset($row['target_property_id']) ? (int)$row['target_property_id'] : 0;
        if($sourceDealId <= 0 || $targetDealId <= 0 || $targetPropertyId <= 0)
        {
            return array('amount' => 0, 'payment_ids' => array(), 'created_plan_ids' => array(), 'receipt_no' => '');
        }

        $sourceRows = $this->DealsPayment->find('all', array(
            'fields' => array(
                'DealsPayment.id', 'DealsPayment.receipt_no', 'DealsPayment.paymenttype_id',
                'DealsPayment.emi', 'DealsPayment.extra_payment', 'DealsPayment.tax_amount',
                'DealsPayment.amount', 'DealsPayment.payment_date'
            ),
            'conditions' => array('DealsPayment.deal_id' => $sourceDealId),
            'order' => array('DealsPayment.payment_date' => 'asc', 'DealsPayment.id' => 'asc'),
            'recursive' => -1
        ));

        $sourcePaid = 0.0;
        $sourceExtra = 0.0;
        $sourceTax = 0.0;
        $sourcePaymentIds = array();
        $sourceReceipts = array();
        foreach((array)$sourceRows as $sourceRow)
        {
            $payment = isset($sourceRow['DealsPayment']) ? (array)$sourceRow['DealsPayment'] : array();
            if(!empty($payment['id'])) { $sourcePaymentIds[] = (int)$payment['id']; }
            $sourcePaid += isset($payment['emi']) ? (float)$payment['emi'] : 0;
            $sourceExtra += isset($payment['extra_payment']) ? (float)$payment['extra_payment'] : 0;
            $sourceTax += isset($payment['tax_amount']) ? (float)$payment['tax_amount'] : 0;
            $receipt = isset($payment['receipt_no']) ? trim((string)$payment['receipt_no']) : '';
            if($receipt !== '' && !in_array($receipt, $sourceReceipts, true))
            {
                $sourceReceipts[] = $receipt;
            }
        }
        $sourcePaid = round($sourcePaid, 2);
        if($sourcePaid <= 0)
        {
            return array('amount' => 0, 'payment_ids' => array(), 'created_plan_ids' => array(), 'receipt_no' => '', 'source_payment_ids' => $sourcePaymentIds);
        }

        $sourceDeal = $this->_fetchDealDetails($sourceDealId);
        $targetDeal = $this->_fetchDealDetails($targetDealId);
        $receiptNo = $this->_mergeReceiptNo($row);
        $remarks = $this->_mergeSummaryRemarks($row, $sourceDeal, $targetDeal, $sourceReceipts);
        $paymentTypeId = $this->_mergePaymentTypeId($sourceRows);
        if($paymentTypeId <= 0)
        {
            throw new Exception(__('Payment type is required before posting merge payment.'));
        }

        $paymentDate = $this->_normalizeDate(isset($row['request_date']) ? $row['request_date'] : date('Y-m-d'));
        $mergePlanId = $this->_createMergeCreditPlan($targetDealId, $sourcePaid, $paymentDate);
        $this->DealsPayment->create();
        if(!$this->DealsPayment->save(array('DealsPayment' => array(
            'property_id' => $targetPropertyId,
            'deal_id' => $targetDealId,
            'paymenttype_id' => $paymentTypeId,
            'plans_payment_id' => $mergePlanId,
            'receipt_no' => $receiptNo,
            'emi' => $sourcePaid,
            'extra_payment' => round($sourceExtra, 2),
            'total_amount' => $sourcePaid,
            'tax_amount' => round($sourceTax, 2),
            'amount' => round($sourcePaid + $sourceExtra + $sourceTax, 2),
            'payment_date' => $paymentDate,
            'remarks' => $remarks,
            'ard_transaction_id' => 0,
            'ard_ledger_id' => 0
        )), false))
        {
            throw new Exception(__('Unable to post merged payment on target deal.'));
        }
        $createdPaymentId = (int)$this->DealsPayment->id;

        return array(
            'amount' => $sourcePaid,
            'extra_payment' => round($sourceExtra, 2),
            'tax_amount' => round($sourceTax, 2),
            'receipt_no' => $receiptNo,
            'payment_ids' => array($createdPaymentId),
            'created_plan_ids' => array($mergePlanId),
            'source_payment_ids' => $sourcePaymentIds,
            'source_receipts' => $sourceReceipts
        );
    }

    protected function _targetMergePaymentAllocations($targetDealId, $amount, $paymentDate)
    {
        $this->loadModel('Admin.DealsPayment');
        $this->loadModel('Admin.PlansPayment');
        $targetDealId = (int)$targetDealId;
        $remaining = round((float)$amount, 2);
        $allocations = array();
        $createdPlanIds = array();
        if($targetDealId <= 0 || $remaining <= 0)
        {
            return array('allocations' => $allocations, 'created_plan_ids' => $createdPlanIds);
        }

        $plans = $this->_targetPlansWithPaid($targetDealId);
        foreach($plans as $plan)
        {
            if($remaining <= 0) { break; }
            $pending = isset($plan['pending']) ? round((float)$plan['pending'], 2) : 0;
            if($pending <= 0) { continue; }
            $allocated = ($remaining >= $pending) ? $pending : $remaining;
            $allocated = round($allocated, 2);
            if($allocated <= 0) { continue; }
            $allocations[] = array('plan_id' => (int)$plan['id'], 'amount' => $allocated);
            $remaining = round($remaining - $allocated, 2);
        }

        if($remaining > 0)
        {
            $planId = $this->_createMergeCreditPlan($targetDealId, $remaining, $paymentDate);
            $createdPlanIds[] = $planId;
            $allocations[] = array('plan_id' => $planId, 'amount' => $remaining);
        }

        return array('allocations' => $allocations, 'created_plan_ids' => $createdPlanIds);
    }

    protected function _targetPlansWithPaid($targetDealId)
    {
        $this->loadModel('Admin.DealsPayment');
        $this->loadModel('Admin.PlansPayment');
        $plansRaw = $this->PlansPayment->find('all', array(
            'fields' => array('PlansPayment.id', 'PlansPayment.amount', 'PlansPayment.date'),
            'conditions' => array('PlansPayment.deal_id' => (int)$targetDealId),
            'order' => array('PlansPayment.id' => 'asc'),
            'recursive' => -1
        ));
        $paidRaw = $this->DealsPayment->find('all', array(
            'fields' => array('DealsPayment.plans_payment_id', 'SUM(DealsPayment.emi) AS paid_total'),
            'conditions' => array('DealsPayment.deal_id' => (int)$targetDealId),
            'group' => array('DealsPayment.plans_payment_id'),
            'recursive' => -1
        ));
        $paidMap = array();
        foreach((array)$paidRaw as $paidRow)
        {
            $planId = isset($paidRow['DealsPayment']['plans_payment_id']) ? (int)$paidRow['DealsPayment']['plans_payment_id'] : 0;
            if($planId > 0)
            {
                $paidMap[$planId] = isset($paidRow[0]['paid_total']) ? round((float)$paidRow[0]['paid_total'], 2) : 0;
            }
        }
        $plans = array();
        foreach((array)$plansRaw as $row)
        {
            $planId = (int)$row['PlansPayment']['id'];
            $amount = isset($row['PlansPayment']['amount']) ? round((float)$row['PlansPayment']['amount'], 2) : 0;
            $paid = isset($paidMap[$planId]) ? round((float)$paidMap[$planId], 2) : 0;
            $pending = round($amount - $paid, 2);
            if($pending < 0) { $pending = 0; }
            $plans[] = array('id' => $planId, 'amount' => $amount, 'paid' => $paid, 'pending' => $pending);
        }
        return $plans;
    }

    protected function _createMergeCreditPlan($targetDealId, $amount, $paymentDate)
    {
        $this->loadModel('Admin.PlansPayment');
        $amount = round((float)$amount, 2);
        if((int)$targetDealId <= 0 || $amount <= 0)
        {
            throw new Exception(__('Unable to create target merge credit plan.'));
        }
        $this->PlansPayment->create();
        if(!$this->PlansPayment->save(array('PlansPayment' => array(
            'deal_id' => (int)$targetDealId,
            'name' => __('Merged Transfer Credit'),
            'amount' => $amount,
            'date' => $paymentDate
        )), false))
        {
            throw new Exception(__('Unable to create target merge credit plan.'));
        }
        return (int)$this->PlansPayment->id;
    }

    protected function _mergePaymentTypeId($sourceRows)
    {
        foreach((array)$sourceRows as $sourceRow)
        {
            if(!empty($sourceRow['DealsPayment']['paymenttype_id']))
            {
                return (int)$sourceRow['DealsPayment']['paymenttype_id'];
            }
        }
        $this->loadModel('Admin.Paymenttype');
        $row = $this->Paymenttype->find('first', array(
            'fields' => array('Paymenttype.id'),
            'order' => array('Paymenttype.id' => 'asc'),
            'recursive' => -1
        ));
        return !empty($row['Paymenttype']['id']) ? (int)$row['Paymenttype']['id'] : 0;
    }

    protected function _mergeReceiptNo($row)
    {
        $ref = !empty($row['transfer_no']) ? preg_replace('/[^A-Za-z0-9\-]/', '', (string)$row['transfer_no']) : (string)(isset($row['id']) ? (int)$row['id'] : time());
        $receiptNo = 'DMG-'.$ref;
        if(strlen($receiptNo) > 30)
        {
            $receiptNo = substr($receiptNo, 0, 30);
        }
        return $receiptNo;
    }

    protected function _mergeSummaryRemarks($row, $sourceDeal, $targetDeal, $sourceReceipts = array())
    {
        $sourceInvoice = !empty($sourceDeal['Deal']['invoice_no']) ? $sourceDeal['Deal']['invoice_no'] : ('#'.(int)$row['source_deal_id']);
        $targetInvoice = !empty($targetDeal['Deal']['invoice_no']) ? $targetDeal['Deal']['invoice_no'] : ('#'.(int)$row['target_deal_id']);
        $sourceClient = !empty($sourceDeal['Client']['name']) ? $sourceDeal['Client']['name'] : __('Source Client');
        $targetClient = !empty($targetDeal['Client']['name']) ? $targetDeal['Client']['name'] : __('Target Client');
        $parts = array(
            'Internal merge credit',
            'from '.$sourceClient.' '.$sourceInvoice,
            'to '.$targetClient.' '.$targetInvoice
        );
        if(!empty($row['transfer_no']))
        {
            $parts[] = 'Transfer '.$row['transfer_no'];
        }
        if(!empty($sourceReceipts))
        {
            $parts[] = 'Source receipts '.implode(',', array_slice((array)$sourceReceipts, 0, 5));
        }
        $remarks = implode(' | ', $parts);
        return strlen($remarks) > 255 ? substr($remarks, 0, 255) : $remarks;
    }

    protected function _markSourceDealMerged($sourceDealId, $targetDealId)
    {
        $this->loadModel('Admin.Deal');
        $save = array('id' => (int)$sourceDealId, 'deal_status' => 'Transferd');
        if($this->Deal->hasField('deal_status_reason')) { $save['deal_status_reason'] = 'Merged into deal #'.(int)$targetDealId; }
        if($this->Deal->hasField('deal_status_changed')) { $save['deal_status_changed'] = date('Y-m-d H:i:s'); }
        $this->Deal->id = (int)$sourceDealId;
        $this->Deal->save(array('Deal' => $save), false);
    }

    protected function _captureMergeMovedIds($sourceDealId)
    {
        $this->loadModel('Admin.DealsPayment');
        $this->loadModel('Admin.PlansPayment');
        $sourceDealId = (int)$sourceDealId;
        return array(
            'deals_payment_ids' => array_values((array)$this->DealsPayment->find('list', array(
                'fields' => array('DealsPayment.id', 'DealsPayment.id'),
                'conditions' => array('DealsPayment.deal_id' => $sourceDealId),
                'recursive' => -1
            ))),
            'plans_payment_ids' => array_values((array)$this->PlansPayment->find('list', array(
                'fields' => array('PlansPayment.id', 'PlansPayment.id'),
                'conditions' => array('PlansPayment.deal_id' => $sourceDealId),
                'recursive' => -1
            )))
        );
    }

    protected function _restoreMergedDealRows($row, $snapshot)
    {
        $this->loadModel('Admin.DealsPayment');
        $this->loadModel('Admin.PlansPayment');
        $row = (array)$row;
        $snapshot = (array)$snapshot;
        if(!empty($snapshot['merge_summary_payment']) && is_array($snapshot['merge_summary_payment']))
        {
            $this->_removeMergeSummaryPayment($snapshot['merge_summary_payment']);
            return;
        }
        $moved = isset($snapshot['merge_moved_ids']) && is_array($snapshot['merge_moved_ids']) ? $snapshot['merge_moved_ids'] : array();
        $paymentIds = isset($moved['deals_payment_ids']) ? array_filter(array_map('intval', (array)$moved['deals_payment_ids'])) : array();
        $planIds = isset($moved['plans_payment_ids']) ? array_filter(array_map('intval', (array)$moved['plans_payment_ids'])) : array();
        if(empty($paymentIds) && empty($planIds))
        {
            throw new Exception(__('This merge was finalized before reversible row tracking was available. Please restore this merge manually from backup/audit details.'));
        }
        if(!empty($paymentIds))
        {
            $this->DealsPayment->updateAll(
                array('DealsPayment.deal_id' => (int)$row['source_deal_id'], 'DealsPayment.property_id' => (int)$row['source_property_id']),
                array('DealsPayment.id' => $paymentIds, 'DealsPayment.deal_id' => (int)$row['target_deal_id'])
            );
        }
        if(!empty($planIds))
        {
            $this->PlansPayment->updateAll(
                array('PlansPayment.deal_id' => (int)$row['source_deal_id']),
                array('PlansPayment.id' => $planIds, 'PlansPayment.deal_id' => (int)$row['target_deal_id'])
            );
        }
    }

    protected function _removeMergeSummaryPayment($summary)
    {
        $this->loadModel('Admin.DealsPayment');
        $this->loadModel('Admin.PlansPayment');
        $summary = (array)$summary;
        $paymentIds = isset($summary['payment_ids']) ? array_filter(array_map('intval', (array)$summary['payment_ids'])) : array();
        $planIds = isset($summary['created_plan_ids']) ? array_filter(array_map('intval', (array)$summary['created_plan_ids'])) : array();
        if(!empty($paymentIds))
        {
            $this->DealsPayment->deleteAll(array('DealsPayment.id' => $paymentIds), false, false);
        }
        if(!empty($planIds))
        {
            $this->PlansPayment->deleteAll(array('PlansPayment.id' => $planIds), false, false);
        }
    }

    protected function _restoreSourceDealAfterMerge($sourceDealId, $snapshot)
    {
        $this->loadModel('Admin.Deal');
        $sourceDealId = (int)$sourceDealId;
        $sourceStatus = '';
        if(isset($snapshot['sourceDeal']['Deal']['deal_status']))
        {
            $sourceStatus = $snapshot['sourceDeal']['Deal']['deal_status'];
        }
        if($sourceStatus === '') { $sourceStatus = 'Active'; }
        $save = array('id' => $sourceDealId);
        if($this->Deal->hasField('deal_status')) { $save['deal_status'] = $sourceStatus; }
        if($this->Deal->hasField('deal_status_reason')) { $save['deal_status_reason'] = 'Merge reversed by admin'; }
        if($this->Deal->hasField('deal_status_changed')) { $save['deal_status_changed'] = date('Y-m-d H:i:s'); }
        $this->Deal->id = $sourceDealId;
        $this->Deal->save(array('Deal' => $save), false);
    }

    protected function _reverseFeeCharge($feeChargeId)
    {
        $this->loadModel('Admin.DealCharge');
        $feeChargeId = (int)$feeChargeId;
        if($feeChargeId > 0)
        {
            $this->DealCharge->delete($feeChargeId);
        }
    }

    protected function _reverseFeeVoucher($transactionId)
    {
        $this->loadModel('Admin.Ardtransaction');
        $transactionId = (int)$transactionId;
        if($transactionId <= 0) { return; }
        $row = $this->Ardtransaction->find('first', array(
            'fields' => array('Ardtransaction.id', 'Ardtransaction.ledger_id', 'Ardtransaction.expense_ledger_id', 'Ardtransaction.income_ledger_id', 'Ardtransaction.status', 'Ardtransaction.entry_date', 'Ardtransaction.project_id'),
            'conditions' => array('Ardtransaction.id' => $transactionId),
            'recursive' => -1
        ));
        if(empty($row['Ardtransaction']['id'])) { return; }
        if($this->_ardTransactionLockedMessage($row['Ardtransaction'], (int)$row['Ardtransaction']['project_id']) !== '')
        {
            return;
        }
        $ledgerIds = array();
        if(!empty($row['Ardtransaction']['ledger_id'])) { $ledgerIds[] = (int)$row['Ardtransaction']['ledger_id']; }
        if(!empty($row['Ardtransaction']['expense_ledger_id'])) { $ledgerIds[] = (int)$row['Ardtransaction']['expense_ledger_id']; }
        if(!empty($row['Ardtransaction']['income_ledger_id'])) { $ledgerIds[] = (int)$row['Ardtransaction']['income_ledger_id']; }
        $this->Ardtransaction->id = $transactionId;
        $this->Ardtransaction->save(array('Ardtransaction' => $this->_ardDecorateTransactionPayload(array(
            'id' => $transactionId,
            'status' => 'Reversed',
            'lock_note' => 'Reversed with deal transfer'
        ), array('status' => 'Reversed'))), false);
        foreach(array_unique($ledgerIds) as $ledgerId)
        {
            $this->_recalculateLedgerBalance($ledgerId);
        }
    }

    protected function _createProcessingFeeCharge($dealId, $projectId, $fee)
    {
        $this->loadModel('Admin.DealCharge');
        if((float)$fee <= 0) { return 0; }
        $this->DealCharge->create();
        if($this->DealCharge->save(array('DealCharge' => array('deal_id' => (int)$dealId, 'project_id' => (int)$projectId, 'charge_name' => 'Deal Transfer Processing Fee', 'cycle_type' => 'O', 'cycle_count' => 1, 'cycle_amount' => (float)$fee, 'total_amount' => (float)$fee, 'start_date' => date('Y-m-d'), 'status' => 1, 'created_by' => (int)$this->luserId)), array('validate' => false)))
        {
            return (int)$this->DealCharge->id;
        }
        return 0;
    }

    protected function _createProcessingFeeVoucher($transferRow, $settings = array())
    {
        $this->loadModel('Admin.Ardtransaction');
        $row = (array)$transferRow;
        $amount = round((float)(isset($row['processing_fee']) ? $row['processing_fee'] : 0), 2);
        if($amount <= 0) { return array('id' => 0, 'ref_no' => '', 'ledger_id' => 0); }
        $projectId = ($row['transfer_type'] === 'Merge' && !empty($row['target_project_id'])) ? (int)$row['target_project_id'] : (int)$row['source_project_id'];
        $ledgerId = $this->_resolveFeeVoucherLedger($projectId, $settings);
        if($projectId <= 0 || $ledgerId <= 0) { return array('id' => 0, 'ref_no' => '', 'ledger_id' => 0); }
        $refNo = $this->_nextVoucherReferenceNo($projectId, 'income');
        $dealId = ($row['transfer_type'] === 'Merge' && !empty($row['target_deal_id'])) ? (int)$row['target_deal_id'] : (int)$row['source_deal_id'];
        $description = 'Deal transfer processing fee';
        if(!empty($row['transfer_no'])) { $description .= ' '.$row['transfer_no']; }
        if($dealId > 0) { $description .= ' - Deal #'.$dealId; }
        $txId = $this->_ardPostAutoVoucher(
            $projectId,
            $ledgerId,
            'income',
            $amount,
            $refNo,
            $this->_normalizeDate(isset($row['request_date']) ? $row['request_date'] : date('Y-m-d')),
            $description,
            array(
                'income_settings_field' => 'transfer_fee_income_ledger_id',
                'income_fallback' => 'Transfer Fee Income',
                'source_type' => 'deal_transfer_fee',
                'source_id' => !empty($row['id']) ? (int)$row['id'] : 0,
                'status' => 'Posted'
            )
        );
        if($txId > 0)
        {
            return array('id' => $txId, 'ref_no' => $refNo, 'ledger_id' => $ledgerId);
        }
        return array('id' => 0, 'ref_no' => '', 'ledger_id' => 0);
    }

    protected function _feeVoucherLedgerOptions($projectId)
    {
        $this->loadModel('Admin.Ardledger');
        $options = array(0 => __('Auto Cash/Bank Account'));
        if((int)$projectId <= 0) { return $options; }
        $rows = $this->Ardledger->find('list', array('fields' => array('Ardledger.id', 'Ardledger.name'), 'conditions' => array('Ardledger.project_id' => (int)$projectId, 'Ardledger.is_trashed' => 0, 'Ardledger.type !=' => 'expense'), 'order' => array('Ardledger.name' => 'asc'), 'recursive' => -1));
        return $options + (array)$rows;
    }

    protected function _resolveFeeVoucherLedger($projectId, $settings = array())
    {
        $this->loadModel('Admin.Ardledger');
        $this->loadModel('Admin.Ardsetting');
        $projectId = (int)$projectId;
        if($projectId <= 0) { return 0; }
        $candidateIds = array();
        if(!empty($settings['fee_voucher_ledger_id'])) { $candidateIds[] = (int)$settings['fee_voucher_ledger_id']; }
        $ardSettings = $this->Ardsetting->find('first', array('conditions' => array('Ardsetting.project_id' => $projectId), 'recursive' => -1));
        if(!empty($ardSettings['Ardsetting']['deal_receipt_cash_ledger_id'])) { $candidateIds[] = (int)$ardSettings['Ardsetting']['deal_receipt_cash_ledger_id']; }
        if(!empty($ardSettings['Ardsetting']['deal_receipt_online_ledger_id'])) { $candidateIds[] = (int)$ardSettings['Ardsetting']['deal_receipt_online_ledger_id']; }
        foreach($candidateIds as $candidateId) { if($this->_validFeeVoucherLedger($candidateId, $projectId)) { return (int)$candidateId; } }
        $fallback = $this->Ardledger->find('first', array('fields' => array('Ardledger.id'), 'conditions' => array('Ardledger.project_id' => $projectId, 'Ardledger.is_trashed' => 0, 'Ardledger.type !=' => 'expense'), 'order' => array('Ardledger.id' => 'asc'), 'recursive' => -1));
        if(!empty($fallback['Ardledger']['id'])) { return (int)$fallback['Ardledger']['id']; }
        $this->Ardledger->create();
        if($this->Ardledger->save(array('Ardledger' => array('project_id' => $projectId, 'name' => 'Deal Transfer Fee Cash', 'type' => 'bank', 'category' => 'Auto Receipt Account', 'balance' => 0, 'is_trashed' => 0)), array('validate' => false))) { return (int)$this->Ardledger->id; }
        return 0;
    }

    protected function _validFeeVoucherLedger($ledgerId, $projectId)
    {
        $this->loadModel('Admin.Ardledger');
        return (int)$this->Ardledger->find('count', array('conditions' => array('Ardledger.id' => (int)$ledgerId, 'Ardledger.project_id' => (int)$projectId, 'Ardledger.is_trashed' => 0, 'Ardledger.type !=' => 'expense'), 'recursive' => -1)) > 0;
    }

    protected function _nextVoucherReferenceNo($projectId, $type = 'income')
    {
        return $this->_nextReferenceNo((int)$projectId, 'income', 'INC-');
    }

    protected function _nextReferenceNo($projectId, $sequenceKey, $fallbackPrefix = 'REF-')
    {
        $this->loadModel('Admin.Ardsetting');
        $projectId = (int)$projectId;
        $row = $this->Ardsetting->find('first', array('conditions' => array('Ardsetting.project_id' => $projectId), 'recursive' => -1));
        $data = !empty($row['Ardsetting']) ? $row['Ardsetting'] : array();
        $prefix = !empty($data['income_voucher_prefix']) ? $data['income_voucher_prefix'] : $fallbackPrefix;
        $nextNo = max(1, (int)(isset($data['income_voucher_next_no']) ? $data['income_voucher_next_no'] : 1));
        $refNo = $prefix.$nextNo;
        $data['project_id'] = $projectId;
        $data['income_voucher_prefix'] = $prefix;
        $data['income_voucher_next_no'] = $nextNo + 1;
        if(!empty($row['Ardsetting']['id'])) { $data['id'] = (int)$row['Ardsetting']['id']; }
        $this->Ardsetting->create();
        $this->Ardsetting->save(array('Ardsetting' => $data), false);
        return $refNo;
    }

    protected function _voucherExists($transactionId)
    {
        $this->loadModel('Admin.Ardtransaction');
        return (int)$transactionId > 0 && (int)$this->Ardtransaction->find('count', array('conditions' => array('Ardtransaction.id' => (int)$transactionId), 'recursive' => -1)) > 0;
    }

    protected function _feeVoucherInfo($transactionId)
    {
        $this->loadModel('Admin.Ardtransaction');
        $transactionId = (int)$transactionId;
        if($transactionId <= 0) { return array('id' => 0, 'ref_no' => '', 'ledger_id' => 0); }
        $row = $this->Ardtransaction->find('first', array('fields' => array('Ardtransaction.id', 'Ardtransaction.ref_no', 'Ardtransaction.ledger_id'), 'conditions' => array('Ardtransaction.id' => $transactionId), 'recursive' => -1));
        if(empty($row['Ardtransaction']['id'])) { return array('id' => 0, 'ref_no' => '', 'ledger_id' => 0); }
        return array('id' => (int)$row['Ardtransaction']['id'], 'ref_no' => isset($row['Ardtransaction']['ref_no']) ? $row['Ardtransaction']['ref_no'] : '', 'ledger_id' => isset($row['Ardtransaction']['ledger_id']) ? (int)$row['Ardtransaction']['ledger_id'] : 0);
    }

    protected function _recalculateLedgerBalance($ledgerId)
    {
        return $this->_ardRecalculateLedgerBalanceCompat($ledgerId);
    }

    protected function _defaultSteps($type, $fee)
    {
        $steps = array('Request created', 'Source deal and client verified', 'Processing fee calculated');
        if($type === 'Merge')
        {
            $steps[] = 'Target deal selected for same-client merge';
            $steps[] = 'Source payment history will remain on source deal for audit';
            $steps[] = 'Single merge payment receipt will be posted on target deal';
        }
        else
        {
            $steps[] = 'Deal client/agency will be updated on finalization';
        }
        if((float)$fee > 0)
        {
            $steps[] = 'Processing fee charge will be created on finalization';
            $steps[] = 'Processing fee voucher must be paid before finalization';
        }
        $steps[] = 'Original deal history remains available';
        return $steps;
    }

    protected function _feeVoucherCreatedSteps($row, $feeVoucher)
    {
        $steps = $this->_decodeJson(isset($row['steps_json']) ? $row['steps_json'] : '');
        if(empty($steps)) { $steps = $this->_defaultSteps(isset($row['transfer_type']) ? $row['transfer_type'] : 'Transfer', isset($row['processing_fee']) ? $row['processing_fee'] : 0); }
        $ref = !empty($feeVoucher['ref_no']) ? ' '.$feeVoucher['ref_no'] : ' #'.(int)$feeVoucher['id'];
        return $this->_appendTransferStep($steps, 'Processing fee voucher created'.$ref);
    }

    protected function _finalizedSteps($row, $feeChargeId = 0, $feeVoucher = array())
    {
        $steps = $this->_decodeJson(isset($row['steps_json']) ? $row['steps_json'] : '');
        if(empty($steps)) { $steps = $this->_defaultSteps(isset($row['transfer_type']) ? $row['transfer_type'] : 'Transfer', isset($row['processing_fee']) ? $row['processing_fee'] : 0); }
        $steps = $this->_appendTransferStep($steps, 'Deal transfer finalized');
        if(isset($row['transfer_type']) && $row['transfer_type'] === 'Merge')
        {
            $steps = $this->_appendTransferStep($steps, 'Target merge payment receipt created');
            $steps = $this->_appendTransferStep($steps, 'Source deal payment history kept for audit');
        }
        if((int)$feeChargeId > 0) { $steps = $this->_appendTransferStep($steps, 'Processing fee charge created'); }
        if(!empty($feeVoucher['id']))
        {
            $ref = !empty($feeVoucher['ref_no']) ? ' '.$feeVoucher['ref_no'] : ' #'.(int)$feeVoucher['id'];
            $steps = $this->_appendTransferStep($steps, 'Processing fee voucher created'.$ref);
        }
        elseif((float)(isset($row['processing_fee']) ? $row['processing_fee'] : 0) > 0)
        {
            $steps = $this->_appendTransferStep($steps, 'Processing fee voucher pending');
        }
        $agreementStep = (isset($row['transfer_type']) && $row['transfer_type'] === 'Merge') ? 'Deal merge agreement ready to print' : 'Deal transfer agreement ready to print';
        return $this->_appendTransferStep($steps, $agreementStep);
    }

    protected function _appendTransferStep($steps, $step)
    {
        $steps = is_array($steps) ? $steps : array();
        $step = trim((string)$step);
        if($step === '') { return $steps; }
        foreach($steps as $existing) { if(trim((string)$existing) === $step) { return $steps; } }
        $steps[] = $step;
        return $steps;
    }

    protected function _nextTransferNo()
    {
        return 'DTR-'.date('Ymd-His');
    }

    protected function _decodeJson($json)
    {
        $data = json_decode((string)$json, true);
        return is_array($data) ? $data : array();
    }

    protected function _normalizeDate($value)
    {
        $value = trim((string)$value);
        if($value === '') { return date('Y-m-d'); }
        if(isset($this->CustomFunction) && method_exists($this->CustomFunction, 'dateFormatBeforeSave'))
        {
            $converted = $this->CustomFunction->dateFormatBeforeSave($value);
            if($converted) { return $converted; }
        }
        $time = strtotime($value);
        return $time ? date('Y-m-d', $time) : date('Y-m-d');
    }

    protected function _isAllowedProject($projectId)
    {
        $projectId = (int)$projectId;
        if($projectId <= 0) { return true; }
        if($this->_isSuperAdminSession((array)$this->adminValue)) { return true; }
        return $projectId === (int)$this->agencyId;
    }

    protected function _agreementAgencyInfo($projectId)
    {
        $this->loadModel('Admin.Agency');
        $this->loadModel('Configuration');
        $project = (array)$this->Agency->find('first', array('fields' => array('Agency.id', 'Agency.name', 'Agency.phone', 'Agency.mobile', 'Agency.email'), 'conditions' => array('Agency.id' => (int)$projectId), 'recursive' => -1));
        $config = (array)$this->Configuration->find('first', array('recursive' => -1));
        $agency = !empty($project['Agency']) ? $project['Agency'] : array();
        $cfg = !empty($config['Configuration']) ? $config['Configuration'] : array();
        return array(
            'name' => $this->_firstNonEmpty(array(isset($agency['name']) ? $agency['name'] : '', isset($cfg['organization_name']) ? $cfg['organization_name'] : '', isset($cfg['name']) ? $cfg['name'] : '', __('Agency Name'))),
            'address' => isset($cfg['address']) ? $cfg['address'] : '',
            'email' => $this->_firstNonEmpty(array(isset($agency['email']) ? $agency['email'] : '', isset($cfg['email']) ? $cfg['email'] : '')),
            'mobile' => $this->_firstNonEmpty(array(isset($agency['mobile']) ? $agency['mobile'] : '', isset($agency['phone']) ? $agency['phone'] : '', isset($cfg['contact']) ? $cfg['contact'] : '')),
            'logo_url' => $this->_agreementImageUrl(array(isset($cfg['photo']) ? $cfg['photo'] : '', 'logo-website.fw.png'), 'logo-website.fw.png'),
            'user_name' => $this->_agreementAdminName()
        );
    }

    protected function _agreementPaymentSummary($dealId, $deal = array())
    {
        $dealData = isset($deal['Deal']) ? $deal['Deal'] : array();
        $total = isset($dealData['total_amount']) ? (float)$dealData['total_amount'] : 0.0;
        $paid = $this->_dealTotalPaid((int)$dealId);
        $balance = max(0, $total - $paid);
        $plans = array();
        try
        {
            $plans = $this->Dealtransfer->query("SELECT pp.id, pp.name, pp.amount, pp.date, COALESCE(SUM(dp.emi),0) AS paid_amount FROM plans_payments pp LEFT JOIN deals_payments dp ON dp.plans_payment_id = pp.id AND dp.deal_id = pp.deal_id WHERE pp.deal_id = ".(int)$dealId." AND COALESCE(pp.is_deal_charge,0) = 0 GROUP BY pp.id, pp.name, pp.amount, pp.date ORDER BY pp.date ASC, pp.id ASC");
        }
        catch(Exception $e) {}
        $totalPlans = count($plans);
        $paidPlans = 0;
        $nextIndex = 0;
        $nextDue = 0.0;
        foreach($plans as $idx => $plan)
        {
            $planAmount = isset($plan['pp']['amount']) ? (float)$plan['pp']['amount'] : 0.0;
            $paidAmount = isset($plan[0]['paid_amount']) ? (float)$plan[0]['paid_amount'] : 0.0;
            if($planAmount > 0 && $paidAmount + 0.01 >= $planAmount) { $paidPlans++; continue; }
            if($nextIndex === 0) { $nextIndex = $idx + 1; $nextDue = max(0, $planAmount - $paidAmount); }
        }
        $installmentLabel = $totalPlans > 0 ? ((($nextIndex > 0) ? $nextIndex : $paidPlans).' / '.$totalPlans) : '-';
        $nextLabel = $totalPlans > 0 ? (($nextIndex > 0) ? __('Next: Inst. #').$nextIndex : __('Complete')) : ($balance > 0 ? __('Balance Due') : __('Complete'));
        if($totalPlans <= 0) { $nextDue = $balance; }
        $cycle = isset($dealData['recurring']) ? trim((string)$dealData['recurring']) : '';
        if($cycle === '' || $cycle === '0') { $cycle = __('Monthly'); }
        return array('total_paid' => $paid, 'total_paid_text' => $this->_agreementMoney($paid), 'installments' => $installmentLabel, 'due_amount' => $nextDue, 'due_amount_text' => $this->_agreementMoney($nextDue), 'cycle' => $cycle, 'next_label' => $nextLabel, 'balance' => $balance, 'balance_text' => $this->_agreementMoney($balance));
    }

    protected function _agreementQrText($row, $transferor, $transferee, $deal, $paymentSummary, $agreementTitle)
    {
        $seller = isset($transferor['Client']) ? $transferor['Client'] : array();
        $buyer = isset($transferee['Client']) ? $transferee['Client'] : array();
        $dealData = isset($deal['Deal']) ? $deal['Deal'] : array();
        $property = isset($deal['Property']) ? $deal['Property'] : array();
        $type = isset($deal['Type']) ? $deal['Type'] : array();
        return implode("\n", array('Doc Type: '.$agreementTitle, 'Transfer No: '.(isset($row['transfer_no']) ? $row['transfer_no'] : ''), 'Date: '.(!empty($row['request_date']) ? $row['request_date'] : date('Y-m-d')), 'Transferor: '.(isset($seller['name']) ? $seller['name'] : ''), 'Transferee: '.(isset($buyer['name']) ? $buyer['name'] : ''), 'File: '.(isset($dealData['invoice_no']) ? $dealData['invoice_no'] : ''), 'Property: '.(isset($property['name']) ? $property['name'] : ''), 'Category: '.(isset($type['name']) ? $type['name'] : ''), 'Paid: '.(isset($paymentSummary['total_paid_text']) ? $paymentSummary['total_paid_text'] : ''), 'Due: '.(isset($paymentSummary['due_amount_text']) ? $paymentSummary['due_amount_text'] : '')));
    }

    protected function _agreementMoney($amount)
    {
        $currency = isset($this->currency) ? trim((string)$this->currency) : '';
        if($currency === '') { $currency = 'Rs'; }
        return trim($currency.' '.number_format((float)$amount, 2, '.', ','));
    }

    protected function _agreementClientPhotoUrl($client)
    {
        $client = isset($client['Client']) ? $client['Client'] : (array)$client;
        $photo = isset($client['photo']) ? trim((string)$client['photo']) : '';
        if($photo === '') { return $this->_agreementImageUrl(array(), 'user-icon.png'); }
        $dir = isset($client['dirt']) ? trim((string)$client['dirt']) : '';
        $candidates = array();
        if(strpos($photo, '/') !== false || strpos($photo, '\\') !== false) { $candidates[] = $photo; }
        if($dir !== '') { $candidates[] = $dir.'_thumb/'.$photo; $candidates[] = $dir.'/'.$photo; }
        $candidates[] = 'client_thumb/'.$photo;
        $candidates[] = 'client/'.$photo;
        $candidates[] = 'user_thumb/'.$photo;
        $candidates[] = 'user/'.$photo;
        return $this->_agreementImageUrl($candidates, 'user-icon.png');
    }

    protected function _agreementClientDocUrl($client, $field, $dirField, $defaultDir)
    {
        $client = isset($client['Client']) ? $client['Client'] : (array)$client;
        $file = isset($client[$field]) ? trim((string)$client[$field]) : '';
        if($file === '') { return ''; }
        $dir = isset($client[$dirField]) ? trim((string)$client[$dirField]) : '';
        if($dir === '') { $dir = $defaultDir; }
        return $this->_agreementImageUrl(array($dir.'_thumb/'.$file, $dir.'/'.$file, $defaultDir.'_thumb/'.$file, $defaultDir.'/'.$file), '');
    }

    protected function _agreementImageUrl($candidates, $fallback = '')
    {
        foreach((array)$candidates as $candidate)
        {
            $url = $this->_agreementResolveLocalImage($candidate);
            if($url !== '') { return $url; }
        }
        return $this->_agreementResolveLocalImage($fallback);
    }

    protected function _agreementResolveLocalImage($value)
    {
        $value = trim((string)$value);
        if($value === '') { return ''; }
        if(preg_match('/^https?:\/\//i', $value)) { return $value; }
        $value = str_replace('\\', '/', $value);
        $value = preg_replace('/^\//', '', $value);
        $value = preg_replace('/^img\//i', '', $value);
        $base = trim((string)$this->request->base, '/');
        if($base !== '') { $value = preg_replace('/^'.preg_quote($base, '/').'\/img\//i', '', $value); }
        $path = WWW_ROOT.'img'.DS.str_replace('/', DS, ltrim($value, '/'));
        return is_file($path) ? Router::url('/img/'.ltrim($value, '/'), false) : '';
    }

    protected function _firstNonEmpty($values)
    {
        foreach((array)$values as $value)
        {
            $value = trim((string)$value);
            if($value !== '') { return $value; }
        }
        return '';
    }

    protected function _agreementAdminName()
    {
        $admin = (array)$this->adminValue;
        foreach(array('name', 'username', 'email') as $field)
        {
            if(isset($admin[$field]) && trim((string)$admin[$field]) !== '') { return trim((string)$admin[$field]); }
            if(isset($admin['User'][$field]) && trim((string)$admin['User'][$field]) !== '') { return trim((string)$admin['User'][$field]); }
            if(isset($admin['Admin'][$field]) && trim((string)$admin['Admin'][$field]) !== '') { return trim((string)$admin['Admin'][$field]); }
        }
        return __('Accounts / Admin');
    }
}
