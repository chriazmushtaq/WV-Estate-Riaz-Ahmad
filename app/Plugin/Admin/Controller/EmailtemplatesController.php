<?php
App::uses('Router', 'Routing');
class EmailtemplatesController extends AdminAppController {
    public $helpers = array('Html', 'Form','Session','Paginator','Tinymce','Js'=> array('Jquery'));
    public $components = array('Session','Paginator','search-master.Prg');
    public $presetVars = true;
    var $paginate = array('page'=>1,'order'=>array('Emailtemplate.name'=>'asc'));

    protected function _templateScope()
    {
        $sessionUser = (array)$this->Session->read('User');
        return array(
            'user_id' => !empty($sessionUser['User']['id']) ? (int)$sessionUser['User']['id'] : (int)$this->luserId,
            'project_id' => !empty($sessionUser['User']['project_id']) ? (int)$sessionUser['User']['project_id'] : (int)$this->agencyId,
            'is_super_admin' => $this->_isSuperAdminSession($sessionUser)
        );
    }

    protected function _prepareEmailtemplateData($data, $scope)
    {
        $scope = (array)$scope;
        $isSuperAdmin = !empty($scope['is_super_admin']);
        $templateData = isset($data['Emailtemplate']) ? (array)$data['Emailtemplate'] : (array)$data;
        $templateData['name'] = isset($templateData['name']) ? trim((string)$templateData['name']) : '';
        $templateData['description'] = isset($templateData['description']) ? (string)$templateData['description'] : '';
        $templateData['status'] = isset($templateData['status']) ? trim((string)$templateData['status']) : 'Published';
        if($isSuperAdmin)
        {
            $templateType = isset($templateData['type']) ? strtoupper(trim((string)$templateData['type'])) : '';
            $templateData['type'] = ($templateType === '') ? null : $templateType;
        }
        else
        {
            $templateData['type'] = null;
            $templateData['user_id'] = (int)(isset($scope['user_id']) ? $scope['user_id'] : 0);
            $templateData['project_id'] = (int)(isset($scope['project_id']) ? $scope['project_id'] : 0);
        }
        return array('Emailtemplate' => $templateData);
    }

    protected function _messagePlaceholderGuideByCategory()
    {
        return array(
            __('Common (All System Templates)') => array(
                '$siteName',
                '$url',
                '$contactNo',
                '$siteEmailContact',
                '$date',
                '$currency'
            ),
            __('Client / User') => array(
                '$clientName',
                '$memberName',
                '$name',
                '$email',
                '$mobileNo',
                '$userName',
                '$password'
            ),
            __('Payment / Overdue') => array(
                '$invoiceNo',
                '$membershipNo',
                '$receiptNo',
                '$propertyName',
                '$paymentName',
                '$paymentDate',
                '$installment',
                '$installmentStatus',
                '$amount',
                '$paidAmount',
                '$totalAmount',
                '$taxAmount',
                '$installmentAmount',
                '$dueDate',
                '$originalDueDate',
                '$overdueInstallmentCount',
                '$totalOverdueInstallmentCount',
                '$totalOverdueAmount',
                '$totalOverdue',
                '$overdueAge',
                '$agencyName',
                '$projectName',
                '$agencyPhone',
                '$contactNo',
                '$bankAccounts',
                '$paymentAccounts',
                '$primaryBankAccount',
                '$bankName',
                '$accountTitle',
                '$accountNo',
                '$iban',
                '$bankBranch',
                '$swiftCode',
                '{{AGENCY_LOGO_URL}}',
                '{{AGENCY_NAME}}',
                '{{AGENCY_TAGLINE}}',
                '{{AGENCY_ADDRESS}}',
                '{{AGENCY_CONTACT_NO}}',
                '{{AGENCY_EMAIL}}',
                '{{AGENCY_UAN}}',
                '{{AGENCY_WEBSITE}}',
                '{{CLIENT_NAME}}',
                '{{MEMBERSHIP_NO}}',
                '{{RECEIPT_NO}}',
                '{{PROPERTY_FILE_NO}}',
                '{{PAYMENT_NAME}}',
                '{{PAYMENT_DATE}}',
                '{{INSTALLMENT_STATUS}}',
                '{{DUE_DATE}}',
                '{{INSTALLMENT_AMOUNT}}',
                '{{PAID_AMOUNT}}',
                '{{TOTAL_AMOUNT}}',
                '{{TAX_AMOUNT}}',
                '{{TOTAL_OVERDUE_INSTALLMENTS}}',
                '{{TOTAL_OVERDUE_AMOUNT}}',
                '{{OLDEST_OVERDUE_AGE}}',
                '{{BANK_NAME}}',
                '{{ACCOUNT_TITLE}}',
                '{{ACCOUNT_NO}}',
                '{{IBAN}}'
            ),
            __('Letters Notifications') => array(
                '$letterType',
                '$letterTitle',
                '$letterEvent',
                '$letterStatus',
                '$referenceNo',
                '$letterNo',
                '$invoiceNo',
                '$propertyName',
                '$projectName',
                '$allotmentNo'
            ),
            __('Verification / Auth') => array(
                '$code'
            ),
            __('Lead Default (Type Code: LED only)') => array(
                '{{lead_name}}',
                '{{client_name}}',
                '{{lead_mobile}}',
                '{{lead_email}}',
                '{{lead_source}}',
                '{{remarks}}',
                '{{last_contact_date}}',
                '{{follow_up}}',
                '{{property_name}}'
            )
        );
    }

    protected function _messagePlaceholderTypeHints()
    {
        return array(
            __('LED') => __('Use {{lead_*}} placeholders (Lead bulk/single message templates).'),
            __('PAY, IDL, PRE, PDE') => __('PAY is paid invoice receipt notification. Use variables like $clientName, $invoiceNo, $receiptNo, $paymentName, $paymentDate, $amount, $amountWords, $receiptUrl, $propertyName. PRE/PDE support overdue HTML with agency and bank placeholders like {{AGENCY_NAME}} and {{ACCOUNT_NO}}.'),
            __('CLC, ULC, AFU') => __('Use account/client variables like $clientName, $userName, $password, $email.'),
            __('SRN, RVN, CFP, AFP') => __('Use verification/reset variables like $code, $url, $siteName.'),
            __('INQ') => __('Use inquiry/client variables like $clientName, $mobileNo, $email, $url.'),
            __('LWL, LBK, LDM, LMP, NOC, LAT, PDN, PAC, POS, TRF, CAN, LIS, LCN') => __('Use letters variables like $letterType, $referenceNo, $invoiceNo, $propertyName.')
        );
    }

    protected function _setTemplatePreviewSample($scope = array())
    {
        $this->set('templatePreviewSample', $this->_templatePreviewSample($scope));
    }

    protected function _templatePreviewSample($scope = array())
    {
        $scope = (array)$scope;
        $projectId = !empty($scope['project_id']) ? (int)$scope['project_id'] : (int)$this->agencyId;
        $config = array();
        try
        {
            $this->loadModel('Configuration');
            $configRow = (array)$this->Configuration->find('first', array('recursive' => -1));
            $config = !empty($configRow['Configuration']) ? (array)$configRow['Configuration'] : array();
        }
        catch(Exception $e)
        {
            $config = array();
        }

        $project = array();
        if($projectId > 0)
        {
            try
            {
                $this->loadModel('Admin.Agency');
                $projectRow = (array)$this->Agency->find('first', array(
                    'fields' => array('Agency.id', 'Agency.name', 'Agency.phone', 'Agency.mobile', 'Agency.email'),
                    'conditions' => array('Agency.id' => $projectId),
                    'recursive' => -1
                ));
                $project = !empty($projectRow['Agency']) ? (array)$projectRow['Agency'] : array();
            }
            catch(Exception $e)
            {
                $project = array();
            }
        }

        $agencyUser = array();
        if($projectId > 0)
        {
            try
            {
                $this->loadModel('Admin.User');
                $userRow = (array)$this->User->find('first', array(
                    'fields' => array('User.id', 'User.name', 'User.phone', 'User.mobile', 'User.email', 'User.address', 'User.photo'),
                    'conditions' => array('User.project_id' => $projectId, 'User.ugroup_id' => 4, 'User.status' => 'Active'),
                    'order' => array('User.id' => 'asc'),
                    'recursive' => -1
                ));
                $agencyUser = !empty($userRow['User']) ? (array)$userRow['User'] : array();
            }
            catch(Exception $e)
            {
                $agencyUser = array();
            }
        }

        $bank = array();
        $bankAccounts = '';
        if($projectId > 0)
        {
            try
            {
                $this->loadModel('Admin.Ardpaymentbankaccount');
                $this->Ardpaymentbankaccount->ensureSchema();
                $bankRows = $this->Ardpaymentbankaccount->activeRowsForProject($projectId);
                $bank = $this->Ardpaymentbankaccount->primaryRow($bankRows);
                $bankAccounts = $this->Ardpaymentbankaccount->formatRowsForMessage($bankRows);
            }
            catch(Exception $e)
            {
                $bank = array();
                $bankAccounts = '';
            }
        }

        $agencyName = !empty($project['name']) ? trim((string)$project['name']) : (!empty($agencyUser['name']) ? trim((string)$agencyUser['name']) : '');
        if($agencyName === '')
        {
            $agencyName = !empty($config['organization_name']) ? trim((string)$config['organization_name']) : (!empty($this->siteName) ? $this->siteName : 'White Villas');
        }
        $agencyPhone = $this->_firstTemplatePreviewValue(array(
            isset($agencyUser['mobile']) ? $agencyUser['mobile'] : '',
            isset($agencyUser['phone']) ? $agencyUser['phone'] : '',
            isset($project['mobile']) ? $project['mobile'] : '',
            isset($project['phone']) ? $project['phone'] : '',
            isset($config['contact']) ? $config['contact'] : ''
        ));
        $agencyEmail = $this->_firstTemplatePreviewValue(array(
            isset($project['email']) ? $project['email'] : '',
            isset($agencyUser['email']) ? $agencyUser['email'] : '',
            isset($config['email']) ? $config['email'] : '',
            isset($this->siteEmail) ? $this->siteEmail : ''
        ));
        $agencyAddress = $this->_firstTemplatePreviewValue(array(
            isset($agencyUser['address']) ? $agencyUser['address'] : '',
            isset($config['address']) ? $config['address'] : ''
        ));
        $logoUrl = $this->_templatePreviewImageUrl(array(
            !empty($agencyUser['photo']) ? 'user_thumb/'.$agencyUser['photo'] : '',
            !empty($agencyUser['photo']) ? 'user/'.$agencyUser['photo'] : '',
            !empty($config['photo']) ? $config['photo'] : '',
            'logo-website.fw.png'
        ));

        return array(
            'client_name' => 'SANA ULLAH S/O MUHAMMAD SHARIF',
            'invoice_no' => 'WV-L /AB/2024-4012',
            'receipt_no' => 'WV-L-4012-18',
            'property_name' => 'ML2138492-SF',
            'payment_name' => '16 Installment',
            'payment_date' => date('d-M-Y'),
            'receipt_url' => Router::url('/paid_receipts/view/1010/sample-token', true),
            'installment_status' => 'Overdue Installments (16)',
            'due_date' => date('d-M-Y', strtotime('+5 days')),
            'original_due_date' => date('d-M-Y'),
            'amount' => $this->currency.' 640,000',
            'amount_words' => 'Six Lakh Forty Thousand Rupees Only',
            'paid_amount' => $this->currency.' 640,000',
            'paid_amount_words' => 'Six Lakh Forty Thousand Rupees Only',
            'total_amount' => $this->currency.' 640,000',
            'tax_amount' => $this->currency.' 0',
            'total_overdue_installments' => '16',
            'total_overdue_amount' => $this->currency.' 640,000',
            'oldest_overdue_age' => '472 days',
            'overdue_age' => '472',
            'agency_name' => $agencyName,
            'agency_logo_url' => $logoUrl,
            'agency_tagline' => !empty($config['meta_desc']) ? trim((string)$config['meta_desc']) : '',
            'agency_address' => $agencyAddress,
            'agency_contact_no' => $agencyPhone,
            'agency_email' => $agencyEmail,
            'agency_uan' => !empty($config['contact']) ? trim((string)$config['contact']) : $agencyPhone,
            'agency_website' => !empty($config['domain_name']) ? trim((string)$config['domain_name']) : Router::url('/', true),
            'bank_accounts' => $bankAccounts,
            'primary_bank_account' => $bankAccounts !== '' ? $bankAccounts : '',
            'bank_name' => !empty($bank['bank_name']) ? trim((string)$bank['bank_name']) : '',
            'account_title' => !empty($bank['account_title']) ? trim((string)$bank['account_title']) : '',
            'account_no' => !empty($bank['account_no']) ? trim((string)$bank['account_no']) : '',
            'iban' => !empty($bank['iban']) ? trim((string)$bank['iban']) : '',
            'bank_branch' => !empty($bank['branch']) ? trim((string)$bank['branch']) : '',
            'swift_code' => !empty($bank['swift_code']) ? trim((string)$bank['swift_code']) : ''
        );
    }

    protected function _firstTemplatePreviewValue($values)
    {
        foreach((array)$values as $value)
        {
            $value = trim((string)$value);
            if($value !== '')
            {
                return $value;
            }
        }
        return '';
    }

    protected function _templatePreviewImageUrl($candidates)
    {
        foreach((array)$candidates as $candidate)
        {
            $candidate = trim(str_replace('\\', '/', (string)$candidate), '/');
            if($candidate === '')
            {
                continue;
            }
            if(preg_match('/^https?:\/\//i', $candidate))
            {
                return $candidate;
            }
            $candidate = preg_replace('#^img/#i', '', $candidate);
            if(@file_exists(WWW_ROOT.'img'.DS.str_replace('/', DS, $candidate)))
            {
                return Router::url('/img/'.$candidate, true);
            }
        }
        return '';
    }

    public function index()
    {
        $this->Emailtemplate->ensureOwnershipSchema();
        $scope = $this->_templateScope();
        $this->set('isGlobalTemplateAdmin', !empty($scope['is_super_admin']));
        $this->Prg->commonProcess();
        $this->Paginator->settings = $this->paginate;
        $conditions = (array)$this->Emailtemplate->parseCriteria($this->Prg->parsedParams());
        if(empty($scope['is_super_admin']))
        {
            $conditions[] = $this->Emailtemplate->scopedCustomConditions((int)$scope['user_id'], false, 'Emailtemplate');
        }
        $this->Paginator->settings['conditions'] = $conditions;
		$this->Paginator->settings['limit']=$this->pageLimit;
        $this->Paginator->settings['maxLimit']=$this->maxLimit;
        $this->set('Emailtemplate', $this->Paginator->paginate());     
		 if ($this->request->is('ajax'))
        {
            $this->render('index','ajax'); // View, Layout
        }
    }    
    public function add()
    {
        $this->Emailtemplate->ensureOwnershipSchema();
        $scope = $this->_templateScope();
        $this->set('isGlobalTemplateAdmin', !empty($scope['is_super_admin']));
        $this->set('placeholderGuideByCategory', $this->_messagePlaceholderGuideByCategory());
        $this->set('placeholderTypeHints', $this->_messagePlaceholderTypeHints());
        $this->_setTemplatePreviewSample($scope);
        if ($this->request->is('post'))
        {
            $this->Emailtemplate->create();
            try
            {
                $saveData = $this->_prepareEmailtemplateData($this->request->data, $scope);
                if ($this->Emailtemplate->save($saveData))
                {
                    $this->Session->setFlash(__('Email template has been saved.'),'flash',array('alert'=>'success'));
                    return $this->redirect(array('action' => 'add'));
                }
            }
            catch (Exception $e)
            {
                $this->Session->setFlash(__('Invalid Post','flash'),array('alert'=>'danger'));
                return $this->redirect(array('action' => 'index'));
            }
        }
    }
    public function edit($id = null)
    {
        $this->Emailtemplate->ensureOwnershipSchema();
        $scope = $this->_templateScope();
        $this->set('isGlobalTemplateAdmin', !empty($scope['is_super_admin']));
        $this->set('placeholderGuideByCategory', $this->_messagePlaceholderGuideByCategory());
        $this->set('placeholderTypeHints', $this->_messagePlaceholderTypeHints());
        $this->_setTemplatePreviewSample($scope);
        if (!$id)
        {
            throw new NotFoundException(__('Invalid post'));
        }
        $ids=explode(",",$id);
        $post=array();
        $allowedIds = array();
        foreach($ids as $id)
        {
            $conditions = array('Emailtemplate.id' => (int)$id);
            if(empty($scope['is_super_admin']))
            {
                $conditions[] = $this->Emailtemplate->scopedCustomConditions((int)$scope['user_id'], false, 'Emailtemplate');
            }
            $row = $this->Emailtemplate->find('first', array(
                'conditions' => $conditions,
                'recursive' => -1
            ));
            if(!empty($row['Emailtemplate']['id']))
            {
                $allowedIds[] = (int)$row['Emailtemplate']['id'];
                $post[] = $row;
            }
        }
        $this->set('Emailtemplate',$post);
        if (!$post)
        {
            throw new NotFoundException(__('Invalid post'));
        }
        if ($this->request->is(array('post', 'put')))
        {
            try
            {
                $saveRows = array();
                foreach((array)$this->request->data as $rowData)
                {
                    $rowId = !empty($rowData['Emailtemplate']['id']) ? (int)$rowData['Emailtemplate']['id'] : 0;
                    if($rowId <= 0 || !in_array($rowId, $allowedIds, true))
                    {
                        continue;
                    }
                    $saveRows[] = $this->_prepareEmailtemplateData($rowData, $scope);
                }
                if(!empty($saveRows) && $this->Emailtemplate->saveAll($saveRows))
                {
                    $this->Session->setFlash(__('Email template has been saved.'),'flash',array('alert'=>'success'));
                    return $this->redirect(array('action' => 'index'));
                }
            }
            catch (Exception $e)
            {
                $this->Session->setFlash(__('Invalid Post.'),'flash',array('alert'=>'danger'));
                return $this->redirect(array('action' => 'index'));
            }
            $this->set('isError',true);
        }
        else
        {
            $this->layout = 'tinymce-absolute';
            $this->set('isError',false);
        }
        if (!$this->request->data)
        {
            $this->request->data = $post;
        }
    }    
    public function deleteall()
    {
        $this->Emailtemplate->ensureOwnershipSchema();
        $scope = $this->_templateScope();
        try
        {
            if ($this->request->is('post'))
            {
                foreach($this->data['Emailtemplate']['id'] as $key => $value)
                {
                    $conditions = array(
                        'Emailtemplate.id' => (int)$value,
                        'OR' => array(
                            array('Emailtemplate.type' => null),
                            array('Emailtemplate.type' => '')
                        )
                    );
                    if(empty($scope['is_super_admin']))
                    {
                        $conditions['Emailtemplate.user_id'] = (int)$scope['user_id'];
                    }
                    $this->Emailtemplate->deleteAll($conditions, false);
                }
                $this->Session->setFlash(__('Email template has been deleted.'),'flash',array('alert'=>'success'));
            }        
            $this->redirect(array('action' => 'index'));
        }
        catch (Exception $e)
        {
            $this->Session->setFlash(__('Please delete related record first.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'index'));
        }
    }
    public function view($id = null)
    {
        $this->layout = null;
        $this->_setTemplatePreviewSample($this->_templateScope());
        if (!$id)
        {
            $this->Session->setFlash(__('Invalid Post.'),'flash',array('alert'=>'danger'));
            $this->redirect(array('action' => 'index'));
        }
        $post = $this->Emailtemplate->findById($id);
        $this->set('post',$post);
    }
}
