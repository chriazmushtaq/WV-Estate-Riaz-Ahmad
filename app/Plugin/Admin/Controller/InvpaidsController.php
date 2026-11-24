<?php
App::uses('CakeTime', 'Utility');
App::uses('CakeNumber', 'Utility');
App::uses('CakeEmail', 'Network/Email');
App::uses('Router', 'Routing');
class InvpaidsController extends AdminAppController
{
    public $components = array('Session','Paginator','search-master.Prg');
    public $helpers=array('Paginator','Js'=> array('Jquery'));
    public $presetVars = true;
    var $paginate = array('page'=>1,'order'=>array('DealsPayment.payment_date'=>'desc','DealsPayment.id'=>'desc'));

    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->_ensurePaidInvoiceTemplates();
    }
    
    public function index()
    {
        if(isset($this->request->named['isSearch']))
        {
            $requestStartDate=$this->CustomFunction->dateFormatBeforeSave($this->request->named['start_date']);
            $requestEndDate=$this->CustomFunction->dateFormatBeforeSave($this->request->named['end_date']);
            $conditions=array();
            if(strlen($requestStartDate)>0 && strlen($requestEndDate)>0)
            $conditions=array('DealsPayment.payment_date BETWEEN ? AND ?'=>array($requestStartDate,$requestEndDate));            
        }
        else
        {
            $startDate=CakeTime::format('Y-m-01',CakeTime::convert(time(),$this->siteTimezone));
            $endDate=CakeTime::format('Y-m-31',CakeTime::convert(time(),$this->siteTimezone));
            $conditions=array('DealsPayment.payment_date BETWEEN ? AND ?'=>array($startDate,$endDate));
        }
        $selectedAgencyId = $this->_selectedInvoiceAgencyId();
        $agencyCondition = $this->_invoiceAgencyCondition($selectedAgencyId);
        $condition=$this->CustomFunction->userWisePermission(NULL,$this->luserId,$this->userType,$this->agencyId,'Property.project_id','Property.user_id');
        $this->Prg->commonProcess();
        $this->_setPaidInvoiceSortVirtualFields();
        $this->Paginator->settings = $this->paginate;
        $this->Paginator->settings['order'] = $this->_paidInvoiceSortOrder();
        $this->Paginator->settings['fields'] = $this->_paidInvoiceGroupFields();
        $this->Paginator->settings['group'] = $this->_paidInvoiceGroupExpression();
        $this->Paginator->settings['conditions'] = array($this->Invpaid->parseCriteria($this->Prg->parsedParams()),$conditions,$condition,$agencyCondition);
        $this->Paginator->settings['limit']=$this->_invoicePaginatorLimit();
        $this->Paginator->settings['maxLimit']=max((int)$this->maxLimit,500);
        $dealRows = $this->_normalizePaidInvoiceRows($this->Paginator->paginate());
        $this->set('deal', $dealRows);
        $reportAgencyId = $selectedAgencyId>0 ? $selectedAgencyId : ($this->_isSuperAdminSession((array)$this->adminValue) ? 0 : (int)$this->agencyId);
        $this->set('reportAgency',$this->_fetchReportAgencyHeader($reportAgencyId));
        $this->_setInvoiceAgencyFilterVars($selectedAgencyId);
	if ($this->request->is('ajax'))
        {
            $this->render('index','ajax'); // View, Layout
        }
	}

    public function sendsms($paymentIds = null)
    {
        $row = $this->_getPaidInvoiceNotificationRow($paymentIds);
        if(empty($row))
        {
            $this->Session->setFlash(__('Paid invoice record was not found.'), 'flash', array('alert'=>'danger'));
            return $this->redirect($this->_paidInvoiceReturnUrl());
        }
        $result = $this->_sendPaidInvoiceSms($row);
        $this->Session->setFlash($result['message'], 'flash', array('alert'=>!empty($result['success']) ? 'success' : 'danger'));
        return $this->redirect($this->_paidInvoiceReturnUrl());
    }

    public function sendemail($paymentIds = null)
    {
        $row = $this->_getPaidInvoiceNotificationRow($paymentIds);
        if(empty($row))
        {
            $message = __('Paid invoice record was not found.');
            if($this->_paidInvoicePopupJsonRequested())
            {
                return $this->_paidInvoiceJsonResponse(array('success'=>false, 'message'=>$message));
            }
            $this->Session->setFlash($message, 'flash', array('alert'=>'danger'));
            return $this->redirect($this->_paidInvoiceReturnUrl());
        }
        $result = $this->_sendPaidInvoiceEmail($row);
        if($this->_paidInvoicePopupJsonRequested())
        {
            return $this->_paidInvoiceJsonResponse($result);
        }
        $this->Session->setFlash($result['message'], 'flash', array('alert'=>!empty($result['success']) ? 'success' : 'danger'));
        return $this->redirect($this->_paidInvoiceReturnUrl());
    }

    public function whatsapp($paymentIds = null)
    {
        $row = $this->_getPaidInvoiceNotificationRow($paymentIds);
        if(empty($row))
        {
            $message = __('Paid invoice record was not found.');
            if($this->_paidInvoicePopupJsonRequested())
            {
                return $this->_paidInvoiceJsonResponse(array('success'=>false, 'message'=>$message));
            }
            $this->Session->setFlash($message, 'flash', array('alert'=>'danger'));
            return $this->redirect($this->_paidInvoiceReturnUrl());
        }

        // Backend notification toggle for Payment Receipt PDF attachment
        $NotificationSetting = ClassRegistry::init('Admin.NotificationSetting');
        $includePdf = true;
        if($NotificationSetting)
        {
            $includePdf = $NotificationSetting->isEnabled('whatsapp_receipt_pdf_attachment', true);
        }
        $receiptPdfUrl = $includePdf ? $this->_paidInvoiceReceiptPdfUrl($row) : '';

        $mobileNo = $this->_paidInvoiceClientMobile($row);
        $whatsappNo = $this->_paidInvoiceWhatsappNumber($mobileNo);
        $message = $this->_buildPaidInvoiceMessage($row, 'whatsapp');

        if($whatsappNo === '')
        {
            $errMsg = __('Client WhatsApp number is missing.');
            if($this->_paidInvoicePopupJsonRequested())
            {
                return $this->_paidInvoiceJsonResponse(array('success'=>false, 'message'=>$errMsg));
            }
            $this->Session->setFlash($errMsg, 'flash', array('alert'=>'danger'));
            return $this->redirect($this->_paidInvoiceReturnUrl());
        }

        $projectId = !empty($row['Property']['project_id']) ? (int)$row['Property']['project_id'] : (!empty($row['Deal']['project_id']) ? (int)$row['Deal']['project_id'] : (!empty($this->agencyId) ? (int)$this->agencyId : 0));
        $userId = !empty($row['Deal']['user_id']) ? (int)$row['Deal']['user_id'] : (!empty($this->luserId) ? (int)$this->luserId : 0);

        App::uses('LeadbotGatewayClient', 'Lib');
        $GatewayModel = ClassRegistry::init('Admin.LeadbotGatewaySetting');
        $EventModel   = ClassRegistry::init('Admin.LeadbotEventSetting');
        $LogModel     = ClassRegistry::init('Admin.LeadbotNotificationLog');

        $settings = $GatewayModel->getSettings($projectId, $userId, true);
        $eventConfig = $EventModel->getEventConfig('payment_received', $projectId, $userId);

        if(!empty($settings['api_key']))
        {
            // LeadBot Gateway Attached - Check Toggles
            $waMaster = !empty($settings['auto_send_whatsapp']);
            $waEvent  = !empty($eventConfig['whatsapp_enabled']);

            if(!$waMaster)
            {
                $toggleErr = __('WhatsApp messaging is toggled OFF in your LeadBot Gateway settings.');
                if($this->_paidInvoicePopupJsonRequested())
                {
                    return $this->_paidInvoiceJsonResponse(array('success'=>false, 'message'=>$toggleErr));
                }
                $this->Session->setFlash($toggleErr, 'flash', array('alert'=>'warning'));
                return $this->redirect($this->_paidInvoiceReturnUrl());
            }

            if(!$waEvent)
            {
                $toggleErr = __('WhatsApp notification toggle is OFF for payment received events in Gateway settings.');
                if($this->_paidInvoicePopupJsonRequested())
                {
                    return $this->_paidInvoiceJsonResponse(array('success'=>false, 'message'=>$toggleErr));
                }
                $this->Session->setFlash($toggleErr, 'flash', array('alert'=>'warning'));
                return $this->redirect($this->_paidInvoiceReturnUrl());
            }

            // Toggles are ON -> Send directly via Leadbot WhatsApp API
            $gatewayUrl = !empty($settings['gateway_url']) ? $settings['gateway_url'] : LeadbotGatewayClient::DEFAULT_LOCAL_URL;
            $apiKey     = $settings['api_key'];
            $waSession  = !empty($settings['selected_wa_session']) ? $settings['selected_wa_session'] : '';
            $desc       = !empty($waSession) ? $waSession : 'Default WhatsApp Profile';
            if(!empty($settings['is_agent_specific'])) $desc = 'Agent Key - ' . $desc;

            $waPayload = array(
                'recipient' => $whatsappNo,
                'message'   => $message,
                'session'   => $waSession,
                'agency_id' => $projectId
            );
            if(!empty($receiptPdfUrl))
            {
                $receiptHost = strtolower((string)parse_url($receiptPdfUrl, PHP_URL_HOST));
                $isLocal = in_array($receiptHost, array('localhost', '127.0.0.1', '::1'), true);
                $isRemote = (strpos($gatewayUrl, 'localhost') === false && strpos($gatewayUrl, '127.0.0.1') === false);
                $hasBinaryExt = preg_match('/\.(pdf)$/i', (string)parse_url($receiptPdfUrl, PHP_URL_PATH));
                if((!$isLocal || !$isRemote) && $hasBinaryExt)
                {
                    $waPayload['media_url'] = $receiptPdfUrl;
                }
            }

            $waResult = LeadbotGatewayClient::sendWhatsApp($gatewayUrl, $apiKey, $waPayload);

            $isSuccess = !empty($waResult['success']);
            $errStr = !empty($waResult['error']) ? $waResult['error'] : '';

            $LogModel->recordLog(array(
                'project_id'                => $projectId,
                'user_id'                   => $userId > 0 ? $userId : null,
                'channel'                   => 'whatsapp',
                'event_key'                 => 'payment_received',
                'recipient_phone'           => $whatsappNo,
                'recipient_name'            => !empty($row['Client']['name']) ? $row['Client']['name'] : 'Client',
                'message'                   => $message,
                'gateway_profile_or_device' => $desc,
                'status'                    => $isSuccess ? 'sent' : 'failed',
                'response_code'             => isset($waResult['latency_ms']) ? $waResult['latency_ms'] : 0,
                'response_payload'          => isset($waResult['raw_response']) ? $waResult['raw_response'] : '',
                'error_message'             => $errStr,
                'is_test'                   => 0
            ));

            $resPayload = array(
                'success' => $isSuccess,
                'message' => $isSuccess ? __('WhatsApp receipt sent successfully via LeadBot profile (%s).', $desc) : ($errStr !== '' ? $errStr : __('WhatsApp delivery failed.')),
                'url'     => 'https://wa.me/'.$whatsappNo.'?text='.rawurlencode($message)
            );

            if($this->_paidInvoicePopupJsonRequested())
            {
                return $this->_paidInvoiceJsonResponse($resPayload);
            }

            $this->Session->setFlash($resPayload['message'], 'flash', array('alert' => $isSuccess ? 'success' : 'danger'));
            return $this->redirect($this->_paidInvoiceReturnUrl());
        }

        // Legacy Fallback to wa.me link
        $result = $this->_buildPaidInvoiceWhatsappLink($row);
        if($this->_paidInvoicePopupJsonRequested())
        {
            return $this->_paidInvoiceJsonResponse($result);
        }
        if(empty($result['success']))
        {
            $this->Session->setFlash($result['message'], 'flash', array('alert'=>'danger'));
            return $this->redirect($this->_paidInvoiceReturnUrl());
        }
        return $this->redirect($result['url']);
    }

    public function sendwhatsapp($paymentIds = null)
    {
        return $this->whatsapp($paymentIds);
    }

    public function exportcsv()
    {
        $this->autoRender = false;
        $this->layout = null;
        if(isset($this->request->params['named']['isSearch']))
        {
            $requestStartDate=$this->CustomFunction->dateFormatBeforeSave(isset($this->request->params['named']['start_date']) ? $this->request->params['named']['start_date'] : null);
            $requestEndDate=$this->CustomFunction->dateFormatBeforeSave(isset($this->request->params['named']['end_date']) ? $this->request->params['named']['end_date'] : null);
            $conditions=array();
            if(strlen($requestStartDate)>0 && strlen($requestEndDate)>0)
            {
                $conditions=array('DealsPayment.payment_date BETWEEN ? AND ?'=>array($requestStartDate,$requestEndDate));
            }
            elseif(strlen($requestStartDate)>0)
            {
                $conditions=array('DealsPayment.payment_date >='=>$requestStartDate);
            }
            elseif(strlen($requestEndDate)>0)
            {
                $conditions=array('DealsPayment.payment_date <='=>$requestEndDate);
            }
        }
        else
        {
            $startDate=CakeTime::format('Y-m-01',CakeTime::convert(time(),$this->siteTimezone));
            $endDate=CakeTime::format('Y-m-31',CakeTime::convert(time(),$this->siteTimezone));
            $conditions=array('DealsPayment.payment_date BETWEEN ? AND ?'=>array($startDate,$endDate));
        }
        $selectedAgencyId = $this->_selectedInvoiceAgencyId();
        $agencyCondition = $this->_invoiceAgencyCondition($selectedAgencyId);
        $condition=$this->CustomFunction->userWisePermission(NULL,$this->luserId,$this->userType,$this->agencyId,'Property.project_id','Property.user_id');
        $searchCriteria = $this->Invpaid->parseCriteria($this->request->params['named']);
        $this->_setPaidInvoiceSortVirtualFields();
        $rows = $this->Invpaid->find('all',array(
            'fields'=>$this->_paidInvoiceGroupFields(),
            'conditions'=>array($searchCriteria,$conditions,$condition,$agencyCondition),
            'group'=>$this->_paidInvoiceGroupExpression(),
            'order'=>$this->_paidInvoiceSortOrder()
        ));
        $rows = $this->_normalizePaidInvoiceRows($rows);
        $filename = "paid_invoices_export_" . date("Y-m-d_H-i-s") . ".csv";
        header("Content-Type: text/csv; charset=utf-8");
        header("Content-Disposition: attachment; filename=\"".$filename."\"");
        header("Pragma: no-cache");
        header("Expires: 0");
        $output = fopen("php://output", "w");
        fputcsv($output, array('Client Name','Phone','Property','Receipt No','Payment On','Payment Date','Amount','Tax Amount','Total Amount'));
        foreach($rows as $row)
        {
            fputcsv($output, array(
                $row['Client']['name'],
                $row['Client']['phone'],
                $row['Property']['name'],
                $row['DealsPayment']['receipt_no'],
                $row['PlansPayment']['name'],
                CakeTime::format($this->sysDay.$this->dateSep.$this->sysMonth.$this->dateSep.$this->sysYear,$row['DealsPayment']['payment_date']),
                $this->currency.CakeNumber::format($row['DealsPayment']['total_amount']),
                $this->currency.CakeNumber::format($row['DealsPayment']['tax_amount']),
                $this->currency.CakeNumber::format($row['DealsPayment']['amount'])
            ));
        }
        fclose($output);
        exit();
    }

    public function exportpdf()
    {
        $this->layout = false;
        if(isset($this->request->params['named']['isSearch']))
        {
            $requestStartDate=$this->CustomFunction->dateFormatBeforeSave(isset($this->request->params['named']['start_date']) ? $this->request->params['named']['start_date'] : null);
            $requestEndDate=$this->CustomFunction->dateFormatBeforeSave(isset($this->request->params['named']['end_date']) ? $this->request->params['named']['end_date'] : null);
            $conditions=array();
            if(strlen($requestStartDate)>0 && strlen($requestEndDate)>0)
            {
                $conditions=array('DealsPayment.payment_date BETWEEN ? AND ?'=>array($requestStartDate,$requestEndDate));
            }
            elseif(strlen($requestStartDate)>0)
            {
                $conditions=array('DealsPayment.payment_date >='=>$requestStartDate);
            }
            elseif(strlen($requestEndDate)>0)
            {
                $conditions=array('DealsPayment.payment_date <='=>$requestEndDate);
            }
        }
        else
        {
            $startDate=CakeTime::format('Y-m-01',CakeTime::convert(time(),$this->siteTimezone));
            $endDate=CakeTime::format('Y-m-31',CakeTime::convert(time(),$this->siteTimezone));
            $conditions=array('DealsPayment.payment_date BETWEEN ? AND ?'=>array($startDate,$endDate));
        }
        $selectedAgencyId = $this->_selectedInvoiceAgencyId();
        $agencyCondition = $this->_invoiceAgencyCondition($selectedAgencyId);
        $permissionCondition=$this->CustomFunction->userWisePermission(NULL,$this->luserId,$this->userType,$this->agencyId,'Property.project_id','Property.user_id');
        $searchCriteria = $this->Invpaid->parseCriteria($this->request->params['named']);
        $this->_setPaidInvoiceSortVirtualFields();
        $rows = $this->Invpaid->find('all',array(
            'fields'=>$this->_paidInvoiceGroupFields(),
            'conditions'=>array($searchCriteria,$conditions,$permissionCondition,$agencyCondition),
            'group'=>$this->_paidInvoiceGroupExpression(),
            'order'=>$this->_paidInvoiceSortOrder()
        ));
        $reportAgencyId = $selectedAgencyId>0 ? $selectedAgencyId : ($this->_isSuperAdminSession((array)$this->adminValue) ? 0 : (int)$this->agencyId);
        $this->set('deal',$this->_normalizePaidInvoiceRows($rows));
        $this->set('reportAgency',$this->_fetchReportAgencyHeader($reportAgencyId));
        $this->set('pdfFilename','paid_invoices_'.date('Ymd_His').'.pdf');
    }
    protected function _paidInvoiceGroupExpression()
    {
        return "CASE WHEN DealsPayment.receipt_no IS NOT NULL AND DealsPayment.receipt_no<>'' THEN CONCAT(DealsPayment.deal_id,':',DealsPayment.receipt_no) ELSE CONCAT('P:',DealsPayment.id) END";
    }
    protected function _selectedInvoiceAgencyId()
    {
        if(!$this->_isSuperAdminSession((array)$this->adminValue) && (int)$this->agencyId>0)
        {
            return (int)$this->agencyId;
        }
        $selectedAgencyId = 0;
        if(isset($this->request->params['named']['project_id']))
        {
            $selectedAgencyId = (int)$this->request->params['named']['project_id'];
        }
        elseif(isset($this->request->data['project_id']))
        {
            $selectedAgencyId = (int)$this->request->data['project_id'];
        }
        return $selectedAgencyId>0 ? $selectedAgencyId : 0;
    }
    protected function _invoicePaginatorLimit()
    {
        $limit = 0;
        if(isset($this->request->params['named']['limit']))
        {
            $limit = (int)$this->request->params['named']['limit'];
        }
        elseif(isset($this->request->params['limit']))
        {
            $limit = (int)$this->request->params['limit'];
        }
        elseif(isset($this->passedArgs['limit']))
        {
            $limit = (int)$this->passedArgs['limit'];
        }
        elseif(isset($this->request->query['limit']))
        {
            $limit = (int)$this->request->query['limit'];
        }
        elseif(isset($this->request->data['limit']))
        {
            $limit = (int)$this->request->data['limit'];
        }
        elseif(isset($this->request->data['Invpaid']['limit']))
        {
            $limit = (int)$this->request->data['Invpaid']['limit'];
        }
        if($limit<=0 && !empty($this->request->params['pass']))
        {
            foreach((array)$this->request->params['pass'] as $passedValue)
            {
                if(preg_match('/^limit[:=](\d+)$/',(string)$passedValue,$matches))
                {
                    $limit = (int)$matches[1];
                    break;
                }
                if(is_numeric($passedValue))
                {
                    $limit = (int)$passedValue;
                    break;
                }
            }
        }
        if($limit<=0 && !empty($this->passedArgs))
        {
            foreach((array)$this->passedArgs as $passedKey=>$passedValue)
            {
                if((string)$passedKey === 'limit')
                {
                    $limit = (int)$passedValue;
                    break;
                }
                if(preg_match('/^limit[:=](\d+)$/',(string)$passedValue,$matches))
                {
                    $limit = (int)$matches[1];
                    break;
                }
            }
        }
        if($limit<=0)
        {
            $limit = (int)$this->pageLimit;
        }
        $maxLimit = max((int)$this->maxLimit, 500);
        if($maxLimit<=0)
        {
            $maxLimit = 500;
        }
        $limit = max(1,min($limit,$maxLimit));
        $this->request->params['named']['limit'] = $limit;
        $this->passedArgs['limit'] = $limit;
        return $limit;
    }
    protected function _invoiceAgencyCondition($selectedAgencyId)
    {
        $selectedAgencyId = (int)$selectedAgencyId;
        if($selectedAgencyId>0)
        {
            return array('Property.project_id'=>$selectedAgencyId);
        }
        return array();
    }
    protected function _setInvoiceAgencyFilterVars($selectedAgencyId)
    {
        $isSuperAdmin = $this->_isSuperAdminSession((array)$this->adminValue);
        $conditions = array();
        if(!$isSuperAdmin && (int)$this->agencyId>0)
        {
            $conditions['Agency.id'] = (int)$this->agencyId;
        }
        $this->loadModel('Agency');
        $agencyOptions = (array)$this->Agency->find('list',array(
            'fields'=>array('Agency.id','Agency.name'),
            'conditions'=>$conditions,
            'order'=>array('Agency.name'=>'asc'),
            'recursive'=>-1
        ));
        $this->set('agencyOptions',$agencyOptions);
        $this->set('selectedAgencyId',(int)$selectedAgencyId);
        $this->set('canUseAgencyFilters',$isSuperAdmin);
    }
    protected function _paidInvoiceReceiptNumberExpression()
    {
        return "COALESCE(NULLIF(DealsPayment.receipt_no,''),Invpaid.invoice_no)";
    }
    protected function _paidInvoiceReceiptSuffixExpression()
    {
        return "CAST(SUBSTRING_INDEX(".$this->_paidInvoiceReceiptNumberExpression().",'-',-1) AS UNSIGNED)";
    }
    protected function _setPaidInvoiceSortVirtualFields()
    {
        $this->Invpaid->virtualFields = array_merge((array)$this->Invpaid->virtualFields,array(
            'paid_sort_payment_date'=>'MAX(DealsPayment.payment_date)',
            'paid_sort_receipt_suffix'=>'MAX('.$this->_paidInvoiceReceiptSuffixExpression().')',
            'paid_sort_receipt_no'=>'MAX('.$this->_paidInvoiceReceiptNumberExpression().')',
            'paid_sort_payment_id'=>'MAX(DealsPayment.id)'
        ));
    }
    protected function _paidInvoiceSortOrder()
    {
        return array(
            'Invpaid.paid_sort_payment_date'=>'desc',
            'Invpaid.paid_sort_receipt_suffix'=>'desc',
            'Invpaid.paid_sort_receipt_no'=>'desc',
            'Invpaid.paid_sort_payment_id'=>'desc'
        );
    }
    protected function _paidInvoiceGroupFields()
    {
        return array(
            $this->_paidInvoiceGroupExpression().' AS paid_invoice_group_key',
            'MAX(Client.name) AS client_name',
            'MAX(Client.fathername) AS client_fathername',
            'MAX(Client.relation_type) AS client_relation_type',
            'MAX(Client.gender) AS client_gender',
            'MAX(Client.phone) AS client_phone',
            'MAX(Client.mobile) AS client_mobile',
            'MAX(Property.name) AS property_name',
            'MAX(Property.project_id) AS property_project_id',
            'MAX(Invpaid.invoice_no) AS invoice_no',
            'MAX(DealsPayment.receipt_no) AS receipt_no',
            'MAX(DealsPayment.deal_id) AS deal_id',
            'MIN(DealsPayment.id) AS primary_payment_id',
            "GROUP_CONCAT(DealsPayment.id ORDER BY DealsPayment.id ASC SEPARATOR ',') AS payment_ids",
            'SUM(DealsPayment.total_amount) AS total_amount',
            'MAX(DealsPayment.payment_date) AS payment_date',
            'SUM(DealsPayment.tax_amount) AS tax_amount',
            'SUM(DealsPayment.amount) AS amount',
            "GROUP_CONCAT(DISTINCT PlansPayment.name ORDER BY PlansPayment.id ASC SEPARATOR ', ') AS payment_name"
        );
    }
    protected function _normalizePaidInvoiceRows($rows)
    {
        foreach((array)$rows as $key=>$row)
        {
            $summary = !empty($row[0]) ? $row[0] : array();
            $rows[$key]['Client']['name'] = isset($summary['client_name']) ? $summary['client_name'] : '';
            $rows[$key]['Client']['fathername'] = isset($summary['client_fathername']) ? $summary['client_fathername'] : '';
            $rows[$key]['Client']['relation_type'] = isset($summary['client_relation_type']) ? $summary['client_relation_type'] : '';
            $rows[$key]['Client']['gender'] = isset($summary['client_gender']) ? $summary['client_gender'] : '';
            $rows[$key]['Client']['phone'] = isset($summary['client_phone']) ? $summary['client_phone'] : '';
            $rows[$key]['Client']['mobile'] = isset($summary['client_mobile']) ? $summary['client_mobile'] : '';
            $rows[$key]['Property']['name'] = isset($summary['property_name']) ? $summary['property_name'] : '';
            $rows[$key]['Property']['project_id'] = isset($summary['property_project_id']) ? $summary['property_project_id'] : '';
            $rows[$key]['Invpaid']['invoice_no'] = isset($summary['invoice_no']) ? $summary['invoice_no'] : '';
            $rows[$key]['DealsPayment']['receipt_no'] = isset($summary['receipt_no']) && strlen(trim((string)$summary['receipt_no']))>0 ? $summary['receipt_no'] : $rows[$key]['Invpaid']['invoice_no'];
            $rows[$key]['DealsPayment']['deal_id'] = isset($summary['deal_id']) ? $summary['deal_id'] : '';
            $rows[$key]['DealsPayment']['id'] = isset($summary['payment_ids']) && strlen($summary['payment_ids'])>0 ? $summary['payment_ids'] : (isset($summary['primary_payment_id']) ? $summary['primary_payment_id'] : '');
            $rows[$key]['DealsPayment']['total_amount'] = isset($summary['total_amount']) ? $summary['total_amount'] : 0;
            $rows[$key]['DealsPayment']['payment_date'] = isset($summary['payment_date']) ? $summary['payment_date'] : null;
            $rows[$key]['DealsPayment']['tax_amount'] = isset($summary['tax_amount']) ? $summary['tax_amount'] : 0;
            $rows[$key]['DealsPayment']['amount'] = isset($summary['amount']) ? $summary['amount'] : 0;
            $rows[$key]['PlansPayment']['name'] = isset($summary['payment_name']) ? $summary['payment_name'] : '';
        }
        return $rows;
    }

    protected function _paidInvoiceReturnUrl()
    {
        return $this->referer(array('action'=>'index'), true);
    }

    protected function _paidInvoicePopupJsonRequested()
    {
        return !empty($this->request->query['notification_popup']);
    }

    protected function _paidInvoiceJsonResponse($result)
    {
        $this->autoRender = false;
        $this->response->type('json');
        if(!is_array($result))
        {
            $result = array('success'=>false, 'message'=>__('Unable to send notification.'));
        }
        echo json_encode($result);
        return $this->response;
    }

    protected function _paidInvoicePaymentIds($paymentIds)
    {
        $ids = array();
        foreach(preg_split('/[^0-9]+/', (string)$paymentIds) as $id)
        {
            $id = (int)$id;
            if($id > 0)
            {
                $ids[$id] = $id;
            }
        }
        return array_values($ids);
    }

    protected function _getPaidInvoiceNotificationRow($paymentIds)
    {
        $ids = $this->_paidInvoicePaymentIds($paymentIds);
        if(empty($ids))
        {
            return array();
        }
        $conditions = array('DealsPayment.id' => $ids);
        $permission = $this->CustomFunction->userWisePermission(NULL, $this->luserId, $this->userType, $this->agencyId, 'Property.project_id', 'Property.user_id');
        if(!empty($permission))
        {
            $conditions[] = $permission;
        }
        $row = $this->Invpaid->find('first', array(
            'fields' => array(
                'MAX(Invpaid.id) AS deal_id',
                'MAX(Invpaid.invoice_no) AS invoice_no',
                'MAX(Client.name) AS client_name',
                'MAX(Client.fathername) AS client_fathername',
                'MAX(Client.relation_type) AS client_relation_type',
                'MAX(Client.gender) AS client_gender',
                'MAX(Client.phone) AS client_phone',
                'MAX(Client.mobile) AS client_mobile',
                'MAX(Client.email) AS client_email',
                'MAX(Property.name) AS property_name',
                'MAX(Property.project_id) AS property_project_id',
                'MAX(DealsPayment.receipt_no) AS receipt_no',
                'MAX(DealsPayment.deal_id) AS payment_deal_id',
                "GROUP_CONCAT(DealsPayment.id ORDER BY DealsPayment.id ASC SEPARATOR ',') AS payment_ids",
                'SUM(DealsPayment.total_amount) AS total_amount',
                'SUM(DealsPayment.tax_amount) AS tax_amount',
                'SUM(DealsPayment.amount) AS amount',
                'MAX(DealsPayment.payment_date) AS payment_date',
                "GROUP_CONCAT(DISTINCT PlansPayment.name ORDER BY PlansPayment.id ASC SEPARATOR ', ') AS payment_name"
            ),
            'conditions' => $conditions,
            'recursive' => 0
        ));
        if(empty($row[0]) || empty($row[0]['payment_ids']))
        {
            return array();
        }
        $summary = $row[0];
        return array(
            'Client' => array(
                'name' => isset($summary['client_name']) ? $summary['client_name'] : '',
                'fathername' => isset($summary['client_fathername']) ? $summary['client_fathername'] : '',
                'relation_type' => isset($summary['client_relation_type']) ? $summary['client_relation_type'] : '',
                'gender' => isset($summary['client_gender']) ? $summary['client_gender'] : '',
                'phone' => isset($summary['client_phone']) ? $summary['client_phone'] : '',
                'mobile' => isset($summary['client_mobile']) ? $summary['client_mobile'] : '',
                'email' => isset($summary['client_email']) ? $summary['client_email'] : ''
            ),
            'Property' => array(
                'name' => isset($summary['property_name']) ? $summary['property_name'] : '',
                'project_id' => isset($summary['property_project_id']) ? (int)$summary['property_project_id'] : 0
            ),
            'Deal' => array(
                'id' => isset($summary['deal_id']) ? (int)$summary['deal_id'] : 0,
                'invoice_no' => isset($summary['invoice_no']) ? $summary['invoice_no'] : ''
            ),
            'DealsPayment' => array(
                'id' => isset($summary['payment_ids']) ? $summary['payment_ids'] : '',
                'deal_id' => isset($summary['payment_deal_id']) ? (int)$summary['payment_deal_id'] : 0,
                'receipt_no' => isset($summary['receipt_no']) && strlen(trim((string)$summary['receipt_no']))>0 ? $summary['receipt_no'] : (isset($summary['invoice_no']) ? $summary['invoice_no'] : ''),
                'total_amount' => isset($summary['total_amount']) ? (float)$summary['total_amount'] : 0,
                'tax_amount' => isset($summary['tax_amount']) ? (float)$summary['tax_amount'] : 0,
                'amount' => isset($summary['amount']) ? (float)$summary['amount'] : 0,
                'payment_date' => isset($summary['payment_date']) ? $summary['payment_date'] : null
            ),
            'PlansPayment' => array(
                'name' => isset($summary['payment_name']) ? $summary['payment_name'] : ''
            )
        );
    }

    protected function _sendPaidInvoiceSms($row)
    {
        $mobileNo = $this->_paidInvoiceClientMobile($row);
        $message = $this->_buildPaidInvoiceMessage($row, 'sms');
        if($mobileNo === '')
        {
            return array('success'=>false, 'message'=>__('Client mobile number is missing.'));
        }
        if(!$this->smsNotification)
        {
            return array('success'=>false, 'message'=>__('SMS notification is disabled.'));
        }
        try
        {
            $this->CustomFunction->sendSms($mobileNo, $message, $this->smsSettingArr);
            return array('success'=>true, 'message'=>__('Paid invoice SMS notification has been sent.'));
        }
        catch(Exception $e)
        {
            return array('success'=>false, 'message'=>$e->getMessage());
        }
    }

    protected function _sendPaidInvoiceEmail($row)
    {
        $email = !empty($row['Client']['email']) ? trim((string)$row['Client']['email']) : '';
        $message = $this->_buildPaidInvoiceMessage($row, 'email');
        if($email === '')
        {
            return array('success'=>false, 'message'=>__('Client email is missing.'));
        }
        if(!$this->emailNotification)
        {
            return array('success'=>false, 'message'=>__('Email notification is disabled.'));
        }
        try
        {
            $Email = new CakeEmail();
            $Email->transport($this->emailSettype);
            if(strtolower((string)$this->emailSettype) === 'smtp')
            {
                $Email->config(array(
                    'host' => $this->emailHost,
                    'port' => $this->emailPort,
                    'username' => $this->emailUsername,
                    'password' => $this->emailPassword,
                    'timeout' => 90
                ));
            }
            elseif(strtolower((string)$this->emailSettype) === 'mail')
            {
                $Email->config(array('additionalParameters' => ''));
            }
            $Email->from(array($this->siteEmail => $this->siteName));
            $Email->to($email);
            $Email->subject($this->_buildPaidInvoiceSubject($row));
            $Email->emailFormat($this->_paidInvoiceMessageLooksHtml($message) ? 'html' : 'text');
            $Email->send($message);
            return array('success'=>true, 'message'=>__('Paid invoice email notification has been sent.'));
        }
        catch(Exception $e)
        {
            return array('success'=>false, 'message'=>$e->getMessage());
        }
    }

    protected function _buildPaidInvoiceWhatsappLink($row)
    {
        $mobileNo = $this->_paidInvoiceClientMobile($row);
        $whatsappNo = $this->_paidInvoiceWhatsappNumber($mobileNo);
        $message = $this->_buildPaidInvoiceMessage($row, 'whatsapp');
        if($whatsappNo === '')
        {
            return array('success'=>false, 'message'=>__('Client WhatsApp number is missing.'));
        }
        return array(
            'success'=>true,
            'message'=>__('WhatsApp notification is ready.'),
            'url'=>'https://wa.me/'.$whatsappNo.'?text='.rawurlencode($message)
        );
    }

    protected function _buildPaidInvoiceSubject($row)
    {
        $receiptNo = !empty($row['DealsPayment']['receipt_no']) ? $row['DealsPayment']['receipt_no'] : '-';
        return __('Payment Received').' - '.$receiptNo;
    }

    protected function _buildPaidInvoiceMessage($row, $channel = 'sms')
    {
        $channel = strtolower(trim((string)$channel));
        $context = $this->_paidInvoiceMessageContext($row, $channel);
        if($channel === 'email')
        {
            $templateMessage = $this->_renderPaidInvoiceTemplate('Emailtemplate', 'PAY', $context);
            return $templateMessage !== '' ? $templateMessage : $this->_defaultPaidInvoiceEmailMessage($context);
        }
        if($channel === 'whatsapp')
        {
            $templateMessage = $this->_renderPaidInvoiceTemplate('Smstemplate', 'PAW', $context);
            if($templateMessage !== '')
            {
                return $templateMessage;
            }
            return $this->_defaultPaidInvoiceWhatsappMessage($context);
        }
        $templateMessage = $this->_renderPaidInvoiceTemplate('Smstemplate', 'PAY', $context);
        if($templateMessage !== '')
        {
            return $templateMessage;
        }
        return $this->_defaultPaidInvoiceSmsMessage($context);
    }

    protected function _renderPaidInvoiceTemplate($modelName, $type, $context)
    {
        $type = strtoupper(trim((string)$type));
        if($type === '')
        {
            return '';
        }
        try
        {
            $this->loadModel('Admin.'.$modelName);
            $template = (array)$this->{$modelName}->find('first', array(
                'fields' => array($modelName.'.id', $modelName.'.description'),
                'conditions' => array(
                    $modelName.'.type' => $type,
                    $modelName.'.status' => 'Published'
                ),
                'recursive' => -1
            ));
        }
        catch(Exception $e)
        {
            return '';
        }
        if(empty($template[$modelName]['description']))
        {
            return '';
        }
        $rendered = trim($this->_replacePaidInvoiceTemplatePlaceholders($template[$modelName]['description'], $context));
        $rendered = $this->_cleanupPaidInvoiceReceiptUrlOutput($rendered, $context);
        if($type === 'PAW')
        {
            $rendered = $this->_repairPaidInvoiceWhatsappEmojiText($rendered);
        }
        return $rendered;
    }

    protected function _paidInvoiceMessageContext($row, $channel = 'sms')
    {
        $projectId = !empty($row['Property']['project_id']) ? (int)$row['Property']['project_id'] : 0;
        $agency = $this->_fetchReportAgencyHeader($projectId);
        $agencyName = !empty($agency['name']) ? trim((string)$agency['name']) : (!empty($this->siteName) ? $this->siteName : 'White Villas');
        $agencyPhone = '';
        foreach(array('mobile', 'phone') as $field)
        {
            if(!empty($agency[$field]))
            {
                $agencyPhone = trim((string)$agency[$field]);
                break;
            }
        }
        if($agencyPhone === '' && !empty($this->contact))
        {
            $agencyPhone = trim((string)$this->contact);
        }
        $paymentDate = !empty($row['DealsPayment']['payment_date']) ? CakeTime::format($this->sysDay.$this->dateSep.$this->sysMonth.$this->dateSep.$this->sysYear, $row['DealsPayment']['payment_date']) : '';
        $currencyLabel = trim((string)(strtolower((string)$channel) === 'email' && !empty($this->currencyFull) ? $this->currencyFull : (!empty($this->currencyType) ? $this->currencyType : $this->currency)));
        $paidAmountNumber = !empty($row['DealsPayment']['amount']) ? (float)$row['DealsPayment']['amount'] : 0;
        $paidAmount = CakeNumber::format($paidAmountNumber);
        $baseAmount = CakeNumber::format(!empty($row['DealsPayment']['total_amount']) ? (float)$row['DealsPayment']['total_amount'] : 0);
        $taxAmount = CakeNumber::format(!empty($row['DealsPayment']['tax_amount']) ? (float)$row['DealsPayment']['tax_amount'] : 0);
        $receiptNo = !empty($row['DealsPayment']['receipt_no']) ? trim((string)$row['DealsPayment']['receipt_no']) : '-';
        $receiptUrl = $this->_paidInvoiceReceiptShareEnabled($projectId) ? $this->_paidInvoiceReceiptUrl($row) : '';
        $NotificationSetting = ClassRegistry::init('Admin.NotificationSetting');
        $includePdf = true;
        if($NotificationSetting)
        {
            $includePdf = $NotificationSetting->isEnabled('whatsapp_receipt_pdf_attachment', true);
        }
        $receiptPdfUrl = ($includePdf && $this->_paidInvoiceReceiptShareEnabled($projectId)) ? $this->_paidInvoiceReceiptPdfUrl($row) : '';
        return array(
            'client_name' => !empty($row['Client']['name']) ? trim((string)$row['Client']['name']) : __('Customer'),
            'client_phone' => !empty($row['Client']['phone']) ? trim((string)$row['Client']['phone']) : '',
            'client_mobile' => !empty($row['Client']['mobile']) ? trim((string)$row['Client']['mobile']) : '',
            'client_email' => !empty($row['Client']['email']) ? trim((string)$row['Client']['email']) : '',
            'invoice_no' => !empty($row['Deal']['invoice_no']) ? trim((string)$row['Deal']['invoice_no']) : '-',
            'receipt_no' => $receiptNo,
            'property_name' => !empty($row['Property']['name']) ? trim((string)$row['Property']['name']) : '-',
            'payment_name' => !empty($row['PlansPayment']['name']) ? trim((string)$row['PlansPayment']['name']) : __('Payment'),
            'payment_date' => $paymentDate,
            'date' => $paymentDate,
            'amount' => $paidAmount,
            'paid_amount' => $paidAmount,
            'amount_words' => $this->_paidInvoiceAmountInWords($paidAmountNumber, $currencyLabel),
            'total_amount' => $baseAmount,
            'tax_amount' => $taxAmount,
            'currency' => $currencyLabel,
            'amount_with_currency' => trim($currencyLabel.' '.$paidAmount),
            'total_amount_with_currency' => trim($currencyLabel.' '.$baseAmount),
            'tax_amount_with_currency' => trim($currencyLabel.' '.$taxAmount),
            'receipt_url' => $receiptUrl,
            'print_receipt_url' => $receiptUrl,
            'receipt_pdf_url' => $receiptPdfUrl,
            'agency_name' => $agencyName,
            'agency_phone' => $agencyPhone,
            'agency_email' => !empty($agency['email']) ? trim((string)$agency['email']) : (!empty($this->siteEmail) ? $this->siteEmail : ''),
            'agency_address' => !empty($agency['address']) ? trim((string)$agency['address']) : (!empty($this->siteAddress) ? $this->siteAddress : ''),
            'site_name' => !empty($this->siteName) ? $this->siteName : $agencyName,
            'contact_no' => $agencyPhone,
            'url' => !empty($this->siteDomain) ? $this->siteDomain : Router::url('/', true)
        );
    }

    protected function _replacePaidInvoiceTemplatePlaceholders($template, $context)
    {
        $replacements = array(
            '$clientName' => $context['client_name'],
            '$memberName' => $context['client_name'],
            '$name' => $context['client_name'],
            '$invoiceNo' => $context['invoice_no'],
            '$membershipNo' => $context['invoice_no'],
            '$receiptNo' => $context['receipt_no'],
            '$propertyName' => $context['property_name'],
            '$paymentName' => $context['payment_name'],
            '$date' => $context['date'],
            '$paymentDate' => $context['payment_date'],
            '$amount' => $context['amount'],
            '$paidAmount' => $context['paid_amount'],
            '$amountWords' => $context['amount_words'],
            '$paidAmountWords' => $context['amount_words'],
            '$totalAmount' => $context['total_amount'],
            '$taxAmount' => $context['tax_amount'],
            '$currency' => $context['currency'],
            '$receiptUrl' => $context['receipt_url'],
            '$printReceiptUrl' => $context['print_receipt_url'],
            '$receiptPdfUrl' => !empty($context['receipt_pdf_url']) ? $context['receipt_pdf_url'] : '',
            '$agencyName' => $context['agency_name'],
            '$projectName' => $context['agency_name'],
            '$agencyPhone' => $context['agency_phone'],
            '$contactNo' => $context['contact_no'],
            '$agencyEmail' => $context['agency_email'],
            '$agencyAddress' => $context['agency_address'],
            '$siteName' => $context['site_name'],
            '$url' => $context['url']
        );
        $replacements += array(
            '{{CLIENT_NAME}}' => $context['client_name'],
            '{{MEMBERSHIP_NO}}' => $context['invoice_no'],
            '{{INVOICE_NO}}' => $context['invoice_no'],
            '{{RECEIPT_NO}}' => $context['receipt_no'],
            '{{PROPERTY_FILE_NO}}' => $context['property_name'],
            '{{PROPERTY_NAME}}' => $context['property_name'],
            '{{PAYMENT_NAME}}' => $context['payment_name'],
            '{{PAYMENT_DATE}}' => $context['payment_date'],
            '{{DATE}}' => $context['date'],
            '{{AMOUNT}}' => $context['amount'],
            '{{PAID_AMOUNT}}' => $context['paid_amount'],
            '{{AMOUNT_WORDS}}' => $context['amount_words'],
            '{{PAID_AMOUNT_WORDS}}' => $context['amount_words'],
            '{{TOTAL_AMOUNT}}' => $context['total_amount'],
            '{{TAX_AMOUNT}}' => $context['tax_amount'],
            '{{AMOUNT_WITH_CURRENCY}}' => $context['amount_with_currency'],
            '{{PAID_AMOUNT_WITH_CURRENCY}}' => $context['amount_with_currency'],
            '{{TOTAL_AMOUNT_WITH_CURRENCY}}' => $context['total_amount_with_currency'],
            '{{TAX_AMOUNT_WITH_CURRENCY}}' => $context['tax_amount_with_currency'],
            '{{CURRENCY}}' => $context['currency'],
            '{{RECEIPT_URL}}' => $context['receipt_url'],
            '{{PRINT_RECEIPT_URL}}' => $context['print_receipt_url'],
            '{{RECEIPT_PDF_URL}}' => !empty($context['receipt_pdf_url']) ? $context['receipt_pdf_url'] : '',
            '{{receipt_pdf_url}}' => !empty($context['receipt_pdf_url']) ? $context['receipt_pdf_url'] : '',
            '{{RECEIPT_PDF}}' => !empty($context['receipt_pdf_url']) ? $context['receipt_pdf_url'] : '',
            '{{receipt_pdf}}' => !empty($context['receipt_pdf_url']) ? $context['receipt_pdf_url'] : '',
            '{{AGENCY_NAME}}' => $context['agency_name'],
            '{{PROJECT_NAME}}' => $context['agency_name'],
            '{{AGENCY_CONTACT_NO}}' => $context['agency_phone'],
            '{{AGENCY_PHONE}}' => $context['agency_phone'],
            '{{AGENCY_EMAIL}}' => $context['agency_email'],
            '{{AGENCY_ADDRESS}}' => $context['agency_address'],
            '{{SITE_NAME}}' => $context['site_name'],
            '{{URL}}' => $context['url'],
            '{{client_name}}' => $context['client_name'],
            '{{invoice_no}}' => $context['invoice_no'],
            '{{membership_no}}' => $context['invoice_no'],
            '{{receipt_no}}' => $context['receipt_no'],
            '{{property_name}}' => $context['property_name'],
            '{{payment_name}}' => $context['payment_name'],
            '{{payment_date}}' => $context['payment_date'],
            '{{amount}}' => $context['amount'],
            '{{paid_amount}}' => $context['paid_amount'],
            '{{amount_words}}' => $context['amount_words'],
            '{{paid_amount_words}}' => $context['amount_words'],
            '{{amount_with_currency}}' => $context['amount_with_currency'],
            '{{receipt_url}}' => $context['receipt_url'],
            '{{print_receipt_url}}' => $context['print_receipt_url'],
            '{{agency_name}}' => $context['agency_name'],
            '{{agency_contact_no}}' => $context['agency_phone'],
            '{{contact_no}}' => $context['agency_phone']
        );
        $replacements += $this->_paidInvoiceEmojiMap();
        uksort($replacements, function($a, $b) {
            return strlen($b) - strlen($a);
        });
        return strtr((string)$template, $replacements);
    }

    protected function _defaultPaidInvoiceSmsMessage($context)
    {
        return 'Dear '.$context['client_name'].', received '.$context['amount_with_currency'].' ('.$context['amount_words'].') for '.$context['payment_name'].' on '.$context['payment_date'].'. Receipt No: '.$context['receipt_no'].'. Regards, '.$context['agency_name'].' '.$context['agency_phone'];
    }

    protected function _defaultPaidInvoiceWhatsappMessage($context)
    {
        $includePdf = !empty($context['receipt_pdf_url']);
        return $this->_cleanupPaidInvoiceReceiptUrlOutput($this->_replacePaidInvoiceTemplatePlaceholders($this->_defaultPaidInvoiceWhatsappTemplate($includePdf), $context), $context);
    }

    protected function _defaultPaidInvoiceWhatsappTemplate($includePdf = true)
    {
        $lines = array(
            '{{EMOJI_CHECK}} *Payment Received - $agencyName*',
            '',
            'Dear *$clientName*,',
            '',
            'Thank you for your payment. We have received *$currency $amount* against your *$paymentName*, paid on *$paymentDate*.',
            '',
            '{{EMOJI_RECEIPT}} *Receipt No:* $receiptNo',
            '{{EMOJI_MONEY}} *Amount Received:* $currency $amount ($amountWords)',
            '{{EMOJI_PIN}} *Payment Against:* $paymentName',
            '{{EMOJI_CALENDAR}} *Payment Date:* $paymentDate',
            '{{EMOJI_LINK}} *Receipt Snapshot:* $receiptUrl'
        );
        if($includePdf)
        {
            $lines[] = '{{EMOJI_PDF}} *Download Official Receipt PDF:* $receiptPdfUrl';
        }
        $lines = array_merge($lines, array(
            '',
            'We appreciate your timely payment and trust in *$agencyName*.',
            'Thank you for choosing us.',
            '',
            'Regards,',
            '*$agencyName*',
            '{{EMOJI_PHONE}} *Contact:* $contactNo'
        ));
        return implode("\n", $lines);
    }

    protected function _paidInvoiceEmojiMap()
    {
        static $map = null;
        if($map !== null)
        {
            return $map;
        }
        $map = array(
            '{{EMOJI_CHECK}}' => $this->_jsonUnicodeChar('\u2705'),
            '{{EMOJI_RECEIPT}}' => $this->_jsonUnicodeChar('\uD83E\uDDFE'),
            '{{EMOJI_MONEY}}' => $this->_jsonUnicodeChar('\uD83D\uDCB0'),
            '{{EMOJI_PIN}}' => $this->_jsonUnicodeChar('\uD83D\uDCCC'),
            '{{EMOJI_CALENDAR}}' => $this->_jsonUnicodeChar('\uD83D\uDCC5'),
            '{{EMOJI_LINK}}' => $this->_jsonUnicodeChar('\uD83D\uDD17'),
            '{{EMOJI_PDF}}' => $this->_jsonUnicodeChar('\uD83D\uDCE5'),
            '{{EMOJI_PHONE}}' => $this->_jsonUnicodeChar('\uD83D\uDCDE')
        );
        return $map;
    }

    protected function _paidInvoiceReceiptUrl($row)
    {
        $ids = $this->_paidInvoicePaymentIds(!empty($row['DealsPayment']['id']) ? $row['DealsPayment']['id'] : '');
        if(empty($ids))
        {
            return '';
        }
        sort($ids);
        $idString = implode(',', $ids);
        $token = $this->_paidInvoiceReceiptShareToken($ids, $row);
        return Router::url(array(
            'plugin' => false,
            'admin' => false,
            'controller' => 'paid_receipts',
            'action' => 'view',
            $idString,
            $token
        ), true);
    }

    protected function _paidInvoiceReceiptPdfUrl($row)
    {
        $url = $this->_paidInvoiceReceiptUrl($row);
        if($url === '')
        {
            return '';
        }
        return $url . (strpos($url, '?') !== false ? '&' : '?') . 'download=1';
    }

    protected function _paidInvoiceReceiptShareEnabled($projectId)
    {
        $projectId = (int)$projectId;
        if($projectId <= 0)
        {
            return true;
        }
        try
        {
            $this->loadModel('Admin.Ardsetting');
            $rows = $this->Ardsetting->query("SELECT `paid_receipt_share_link_enabled` FROM `ardaccounts_settings` WHERE `project_id`=".(int)$projectId." ORDER BY `id` DESC LIMIT 1");
            if(!empty($rows[0]['ardaccounts_settings']) && array_key_exists('paid_receipt_share_link_enabled', $rows[0]['ardaccounts_settings']))
            {
                return (int)$rows[0]['ardaccounts_settings']['paid_receipt_share_link_enabled'] === 1;
            }
            if(!empty($rows[0][0]) && array_key_exists('paid_receipt_share_link_enabled', $rows[0][0]))
            {
                return (int)$rows[0][0]['paid_receipt_share_link_enabled'] === 1;
            }
        }
        catch(Exception $e)
        {
            return true;
        }
        return true;
    }

    protected function _cleanupPaidInvoiceReceiptUrlOutput($message, $context)
    {
        $message = (string)$message;
        if(empty($context['receipt_url']))
        {
            if($this->_paidInvoiceMessageLooksHtml($message))
            {
                $message = preg_replace('/<p\b[^>]*>\s*<a\b[^>]*href=(["\']?)\s*\1[^>]*>.*?(View\s*\/\s*Print Receipt|Receipt Snapshot).*?<\/a>\s*<\/p>/is', '', $message);
                $message = preg_replace('/<p\b[^>]*>[^<]*(View\s*\/\s*Print Receipt|Receipt Snapshot|Print Receipt)[^<]*<\/p>/is', '', $message);
            }
            else
            {
                $message = preg_replace('/(^|\R)[^\r\n]*(Receipt Snapshot|View\s*\/\s*Print Receipt|Print Receipt)[^\r\n]*(?=\R|$)/i', '', $message);
            }
        }
        if(empty($context['receipt_pdf_url']))
        {
            if($this->_paidInvoiceMessageLooksHtml($message))
            {
                $message = preg_replace('/<p\b[^>]*>\s*<a\b[^>]*href=(["\']?)\s*\1[^>]*>.*?(Download Official Receipt PDF|Download Receipt PDF|Official Receipt PDF).*?<\/a>\s*<\/p>/is', '', $message);
                $message = preg_replace('/<p\b[^>]*>[^<]*(Download Official Receipt PDF|Download Receipt PDF|Official Receipt PDF)[^<]*<\/p>/is', '', $message);
            }
            else
            {
                $message = preg_replace('/(^|\R)[^\r\n]*(Download Official Receipt PDF|Download Receipt PDF|Official Receipt PDF)[^\r\n]*(?=\R|$)/i', '', $message);
            }
        }
        return trim($message);
    }

    protected function _paidInvoiceReceiptShareToken($ids, $row)
    {
        $parts = array(
            implode(',', (array)$ids),
            !empty($row['DealsPayment']['receipt_no']) ? trim((string)$row['DealsPayment']['receipt_no']) : '',
            isset($row['DealsPayment']['amount']) ? number_format((float)$row['DealsPayment']['amount'], 2, '.', '') : '0.00',
            Configure::read('Security.salt')
        );
        return substr(hash('sha256', implode('|', $parts)), 0, 32);
    }

    protected function _jsonUnicodeChar($escape)
    {
        $decoded = json_decode('"'.(string)$escape.'"');
        return is_string($decoded) ? $decoded : '';
    }

    protected function _paidInvoiceAmountInWords($amount, $currencySymbol = 'Rs')
    {
        $amount = round(abs((float)$amount), 2);
        $whole = (int)floor($amount);
        $fraction = (int)round(($amount - $whole) * 100);
        if($fraction >= 100)
        {
            $whole++;
            $fraction = 0;
        }
        $words = $this->_paidInvoiceIntegerInWords($whole).' '.$this->_paidInvoiceCurrencyWord($currencySymbol);
        if($fraction > 0)
        {
            $words .= ' And '.$this->_paidInvoiceIntegerInWords($fraction).' Paisa';
        }
        return trim($words).' Only';
    }

    protected function _paidInvoiceCurrencyWord($currencySymbol)
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

    protected function _paidInvoiceIntegerInWords($number)
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
            $label = trim((string)$groups[$i][1]);
            $parts[] = trim($this->_paidInvoiceNumberBelowThousandInWords($chunk).($label !== '' ? ' '.$label : ''));
        }
        return implode(' ', $parts);
    }

    protected function _paidInvoiceNumberBelowThousandInWords($number)
    {
        $number = (int)$number;
        $ones = array(
            0 => '',
            1 => 'One',
            2 => 'Two',
            3 => 'Three',
            4 => 'Four',
            5 => 'Five',
            6 => 'Six',
            7 => 'Seven',
            8 => 'Eight',
            9 => 'Nine',
            10 => 'Ten',
            11 => 'Eleven',
            12 => 'Twelve',
            13 => 'Thirteen',
            14 => 'Fourteen',
            15 => 'Fifteen',
            16 => 'Sixteen',
            17 => 'Seventeen',
            18 => 'Eighteen',
            19 => 'Nineteen'
        );
        $tens = array(
            2 => 'Twenty',
            3 => 'Thirty',
            4 => 'Forty',
            5 => 'Fifty',
            6 => 'Sixty',
            7 => 'Seventy',
            8 => 'Eighty',
            9 => 'Ninety'
        );
        $parts = array();
        if($number >= 100)
        {
            $parts[] = $ones[(int)floor($number / 100)].' Hundred';
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
                $parts[] = trim($tens[$ten].($unit > 0 ? ' '.$ones[$unit] : ''));
            }
        }
        return implode(' ', $parts);
    }

    protected function _repairPaidInvoiceWhatsappEmojiText($message)
    {
        $emoji = $this->_paidInvoiceEmojiMap();
        $message = (string)$message;
        $message = $this->_repairPaidInvoiceWhatsappLineIcon($message, 'Payment Received', $emoji['{{EMOJI_CHECK}}']);
        $message = $this->_repairPaidInvoiceWhatsappLineIcon($message, 'Receipt No:', $emoji['{{EMOJI_RECEIPT}}']);
        $message = $this->_repairPaidInvoiceWhatsappLineIcon($message, 'Amount Received:', $emoji['{{EMOJI_MONEY}}']);
        $message = $this->_repairPaidInvoiceWhatsappLineIcon($message, 'Payment Against:', $emoji['{{EMOJI_PIN}}']);
        $message = $this->_repairPaidInvoiceWhatsappLineIcon($message, 'Payment Date:', $emoji['{{EMOJI_CALENDAR}}']);
        $message = $this->_repairPaidInvoiceWhatsappLineIcon($message, 'Receipt Snapshot:', $emoji['{{EMOJI_LINK}}']);
        $message = $this->_repairPaidInvoiceWhatsappLineIcon($message, 'Contact:', $emoji['{{EMOJI_PHONE}}']);
        return $message;
    }

    protected function _repairPaidInvoiceWhatsappLineIcon($message, $label, $emoji)
    {
        $label = preg_quote((string)$label, '/');
        return preg_replace('/(^|\n)[^\r\n]{1,18}\s+(\*'.$label.'\*)/', '$1'.$emoji.' $2', (string)$message);
    }

    protected function _normalizePaidInvoiceWhatsappTemplate($description)
    {
        $description = (string)$description;
        if(trim($description) === '' || $this->_paidInvoiceWhatsappTemplateHasBrokenEmoji($description))
        {
            return $this->_defaultPaidInvoiceWhatsappTemplate();
        }
        $tokens = array();
        foreach($this->_paidInvoiceEmojiMap() as $token => $emoji)
        {
            if($emoji !== '')
            {
                $tokens[$emoji] = $token;
            }
        }
        $normalized = strtr($description, $tokens);
        $normalized = $this->_paidInvoiceWhatsappTemplateAddAmountWords($normalized);
        $normalized = $this->_paidInvoiceWhatsappTemplateAddReceiptUrl($normalized);
        if(strpos($normalized, '{{EMOJI_') === false && stripos($normalized, 'Payment Received') !== false)
        {
            return $this->_defaultPaidInvoiceWhatsappTemplate();
        }
        return $normalized;
    }

    protected function _defaultPaidInvoiceEmailMessage($context)
    {
        return '<p>Dear <strong>'.h($context['client_name']).'</strong>,</p>'
            .'<p>We have received your payment successfully.</p>'
            .'<table cellpadding="8" cellspacing="0" border="1" style="border-collapse:collapse;width:100%;">'
            .'<tr><td><strong>Membership No</strong></td><td>'.h($context['invoice_no']).'</td></tr>'
            .'<tr><td><strong>Receipt No</strong></td><td>'.h($context['receipt_no']).'</td></tr>'
            .'<tr><td><strong>Property/File</strong></td><td>'.h($context['property_name']).'</td></tr>'
            .'<tr><td><strong>Payment On</strong></td><td>'.h($context['payment_name']).'</td></tr>'
            .'<tr><td><strong>Payment Date</strong></td><td>'.h($context['payment_date']).'</td></tr>'
            .'<tr><td><strong>Paid Amount</strong></td><td><strong>'.h($context['amount_with_currency']).'</strong><br><small>'.h($context['amount_words']).'</small></td></tr>'
            .'</table>'
            .($context['receipt_url'] !== '' ? '<p><a href="'.h($context['receipt_url']).'" style="display:inline-block;background:#0b3d2e;color:#ffffff;text-decoration:none;padding:10px 16px;border-radius:5px;font-weight:bold;">View / Print Receipt</a></p>' : '')
            .'<p>Thank you for your payment.</p>'
            .'<p>Regards,<br><strong>'.h($context['agency_name']).'</strong><br>'.h($context['agency_phone']).'</p>';
    }

    protected function _paidInvoiceMessageLooksHtml($message)
    {
        return preg_match('/<(html|body|table|div|p|br|h[1-6]|span|strong|img|a)\b/i', (string)$message) === 1;
    }

    protected function _paidInvoiceClientMobile($row)
    {
        if(!empty($row['Client']['mobile']))
        {
            return trim((string)$row['Client']['mobile']);
        }
        if(!empty($row['Client']['phone']))
        {
            return trim((string)$row['Client']['phone']);
        }
        return '';
    }

    protected function _paidInvoiceWhatsappNumber($mobileNo)
    {
        $mobileNo = preg_replace('/\D+/', '', (string)$mobileNo);
        if($mobileNo === '')
        {
            return '';
        }
        if(substr($mobileNo, 0, 2) === '00')
        {
            $mobileNo = substr($mobileNo, 2);
        }
        if(substr($mobileNo, 0, 1) === '0')
        {
            $mobileNo = '92'.substr($mobileNo, 1);
        }
        return $mobileNo;
    }

    protected function _ensurePaidInvoiceTemplates()
    {
        $defaults = array(
            array('model' => 'Smstemplate', 'data' => array(
                'name' => 'Payment',
                'description' => 'Dear $clientName, Received $currency $amount ($amountWords) towards $paymentName paid on $date. Receipt No: $receiptNo. Regards, $siteName $contactNo',
                'type' => 'PAY',
                'status' => 'Published'
            )),
            array('model' => 'Smstemplate', 'data' => array(
                'name' => 'Payment Received WhatsApp',
                'description' => $this->_defaultPaidInvoiceWhatsappTemplate(),
                'type' => 'PAW',
                'status' => 'Published'
            )),
            array('model' => 'Emailtemplate', 'data' => array(
                'name' => 'Payment',
                'description' => '<p>Dear $clientName,</p><p>Received $currency $amount ($amountWords) towards $paymentName paid on $date.</p><p>Receipt No: <strong>$receiptNo</strong></p><p><a href="$receiptUrl">View / Print Receipt</a></p><p>Regards,<br>$siteName<br>$contactNo</p>',
                'type' => 'PAY',
                'status' => 'Published'
            ))
        );
        foreach($defaults as $templateDefault)
        {
            try
            {
                $modelName = $templateDefault['model'];
                $data = $templateDefault['data'];
                $this->loadModel('Admin.'.$modelName);
                if(method_exists($this->{$modelName}, 'ensureOwnershipSchema'))
                {
                    $this->{$modelName}->ensureOwnershipSchema();
                }
                $existing = (array)$this->{$modelName}->find('first', array(
                    'fields' => array($modelName.'.id', $modelName.'.description'),
                    'conditions' => array($modelName.'.type' => $data['type']),
                    'recursive' => -1
                ));
                if(!empty($existing[$modelName]['id']))
                {
                    if($data['type'] === 'PAW' && $this->_paidInvoiceWhatsappTemplateNeedsRepair($existing[$modelName]['description']))
                    {
                        $this->{$modelName}->id = (int)$existing[$modelName]['id'];
                        $this->{$modelName}->saveField('description', $this->_normalizePaidInvoiceWhatsappTemplate($existing[$modelName]['description']), array('validate' => false));
                    }
                    continue;
                }
                if(empty($existing[$modelName]['id']))
                {
                    $this->{$modelName}->create();
                    $this->{$modelName}->save(array($modelName => $data), false);
                }
            }
            catch(Exception $e)
            {
                // Template creation is best-effort; fallback messages still work.
            }
        }
    }

    protected function _paidInvoiceWhatsappTemplateNeedsRepair($description)
    {
        $description = (string)$description;
        if(trim($description) === '')
        {
            return true;
        }
        if(strpos($description, '{{EMOJI_CHECK}}') !== false)
        {
            return !$this->_paidInvoiceWhatsappTemplateHasAmountWords($description)
                || !$this->_paidInvoiceWhatsappTemplateHasReceiptUrl($description);
        }
        if($this->_paidInvoiceWhatsappTemplateHasBrokenEmoji($description))
        {
            return true;
        }
        foreach($this->_paidInvoiceEmojiMap() as $emoji)
        {
            if($emoji !== '' && strpos($description, $emoji) !== false)
            {
                return true;
            }
        }
        return false;
    }

    protected function _paidInvoiceWhatsappTemplateHasAmountWords($description)
    {
        $description = (string)$description;
        return strpos($description, '$amountWords') !== false
            || strpos($description, '$paidAmountWords') !== false
            || strpos($description, '{{AMOUNT_WORDS}}') !== false
            || strpos($description, '{{PAID_AMOUNT_WORDS}}') !== false
            || strpos($description, '{{amount_words}}') !== false
            || strpos($description, '{{paid_amount_words}}') !== false;
    }

    protected function _paidInvoiceWhatsappTemplateHasReceiptUrl($description)
    {
        $description = (string)$description;
        return strpos($description, '$receiptUrl') !== false
            || strpos($description, '$printReceiptUrl') !== false
            || strpos($description, '{{RECEIPT_URL}}') !== false
            || strpos($description, '{{PRINT_RECEIPT_URL}}') !== false
            || strpos($description, '{{receipt_url}}') !== false
            || strpos($description, '{{print_receipt_url}}') !== false;
    }

    protected function _paidInvoiceWhatsappTemplateAddAmountWords($description)
    {
        $description = (string)$description;
        if($this->_paidInvoiceWhatsappTemplateHasAmountWords($description))
        {
            return $description;
        }
        $updated = str_replace(
            '{{EMOJI_MONEY}} *Amount Received:* $currency $amount',
            '{{EMOJI_MONEY}} *Amount Received:* $currency $amount ($amountWords)',
            $description
        );
        $updated = str_replace(
            '{{EMOJI_MONEY}} *Amount Received:* {{CURRENCY}} {{AMOUNT}}',
            '{{EMOJI_MONEY}} *Amount Received:* {{CURRENCY}} {{AMOUNT}} ({{AMOUNT_WORDS}})',
            $updated
        );
        if($updated === $description && stripos($description, 'Payment Received') !== false)
        {
            return $this->_defaultPaidInvoiceWhatsappTemplate();
        }
        return $updated;
    }

    protected function _paidInvoiceWhatsappTemplateAddReceiptUrl($description)
    {
        $description = (string)$description;
        if($this->_paidInvoiceWhatsappTemplateHasReceiptUrl($description))
        {
            return $description;
        }
        $line = '{{EMOJI_CALENDAR}} *Payment Date:* $paymentDate';
        $updated = str_replace($line, $line."\n".'{{EMOJI_LINK}} *Receipt Snapshot:* $receiptUrl', $description);
        if($updated === $description && stripos($description, 'Payment Received') !== false)
        {
            return $this->_defaultPaidInvoiceWhatsappTemplate();
        }
        return $updated;
    }

    protected function _paidInvoiceWhatsappTemplateHasBrokenEmoji($description)
    {
        $description = (string)$description;
        if(strpos($description, "\xEF\xBF\xBD") !== false)
        {
            return true;
        }
        if(strpos($description, "\xC3\xA2") !== false || strpos($description, "\xC3\xB0") !== false)
        {
            return true;
        }
        return preg_match('/(^|\n)\?\s+\*(Payment Received|Receipt No|Amount Received|Payment Against|Payment Date|Receipt Snapshot|Contact)/', $description) === 1;
    }

    protected function _fetchReportAgencyHeader($reportAgencyId=0)
    {
        $reportAgency = array();
        $reportAgencyId = (int)$reportAgencyId;
        if($reportAgencyId<=0)
        {
            return $reportAgency;
        }
        $this->loadModel('Agency');
        $agencyFields = array('Agency.id');
        foreach(array('name','photo','address','phone','mobile','email') as $agencyHeaderField)
        {
            if($this->Agency->hasField($agencyHeaderField))
            {
                $agencyFields[] = 'Agency.'.$agencyHeaderField;
            }
        }
        $agencyRow = $this->Agency->find('first',array(
            'fields'=>$agencyFields,
            'conditions'=>array('Agency.id'=>$reportAgencyId),
            'recursive'=>-1
        ));
        if(empty($agencyRow['Agency']))
        {
            return $reportAgency;
        }
        $reportAgency = $agencyRow['Agency'];
        if(empty($reportAgency['address']) || empty($reportAgency['phone']) || empty($reportAgency['mobile']) || empty($reportAgency['email']))
        {
            $this->loadModel('User');
            $userFields = array('User.id');
            foreach(array('address','phone','mobile','email') as $userHeaderField)
            {
                if($this->User->hasField($userHeaderField))
                {
                    $userFields[] = 'User.'.$userHeaderField;
                }
            }
            $agencyUser = $this->User->find('first',array(
                'fields'=>$userFields,
                'conditions'=>array('User.project_id'=>$reportAgencyId),
                'order'=>array('User.id'=>'asc'),
                'recursive'=>-1
            ));
            if(!empty($agencyUser['User']))
            {
                if(empty($reportAgency['address']) && !empty($agencyUser['User']['address']))$reportAgency['address'] = $agencyUser['User']['address'];
                if(empty($reportAgency['phone']) && !empty($agencyUser['User']['phone']))$reportAgency['phone'] = $agencyUser['User']['phone'];
                if(empty($reportAgency['mobile']) && !empty($agencyUser['User']['mobile']))$reportAgency['mobile'] = $agencyUser['User']['mobile'];
                if(empty($reportAgency['email']) && !empty($agencyUser['User']['email']))$reportAgency['email'] = $agencyUser['User']['email'];
            }
        }
        return $reportAgency;
    }
}
