<?php
App::uses('CakeTime', 'Utility');
App::uses('CakeNumber', 'Utility');
App::uses('CakeEmail', 'Network/Email');
ini_set('max_execution_time', 900);
class InvpastduesController extends AdminAppController
{
    public $components = array('Session','Paginator','search-master.Prg');
    public $helpers=array('NumToWord','Paginator','Js'=> array('Jquery'));
    public $presetVars = true;
    var $paginate = array('page'=>1,'order'=>array('Client.name'=>'asc'));
    var $fieldIdArr=array('Type.name','Client.id','Client.name','Client.fathername','Client.relation_type','Client.gender','Client.phone','Client.mobile','Client.address','Client.email','Property.name','Property.address','Property.project_id','Project.name','Deal.id','Deal.plan_id','Deal.invoice_no','Invpastdue.id','Invpastdue.name','Invpastdue.date','Deal.total_amount','Invpastdue.amount');
    protected $_dealOverdueBlockedStatuses = array('Transferd','Transferred','Permanent Blocked','Pemanent Blocked','Refund','Refund-Running');
    public function index()
    {
        $overdueTillDate = CakeTime::format('Y-m-d',CakeTime::convert(time(),$this->siteTimezone));
        if(isset($this->request->named['isSearch']))
        {
            $requestStartDate=$this->CustomFunction->dateFormatBeforeSave($this->request->named['start_date']);
            $requestEndDate=$this->CustomFunction->dateFormatBeforeSave($this->request->named['end_date']);
            $conditions=array();
            if(strlen($requestEndDate)>0)
            {
                $overdueTillDate=$requestEndDate;
            }
            elseif(strlen($requestStartDate)>0)
            {
                $overdueTillDate=$requestStartDate;
            }
            if(strlen($requestStartDate)>0 && strlen($requestEndDate)>0)
            $conditions=array('Invpastdue.date BETWEEN ? AND ?'=>array($requestStartDate,$requestEndDate));
            elseif(strlen($requestEndDate)>0)
            $conditions=array('Invpastdue.date <='=>$requestEndDate);
            elseif(strlen($requestStartDate)>0)
            $conditions=array('Invpastdue.date >='=>$requestStartDate);
        }
        else
        {
            $startDate=CakeTime::format('Y-m-d',CakeTime::convert(time(),$this->siteTimezone));
            $conditions=array('Invpastdue.date <='=>$startDate);
            $overdueTillDate=$startDate;
        }
        $selectedAgencyId = $this->_selectedInvoiceAgencyId();
        $agencyCondition = $this->_invoiceAgencyCondition($selectedAgencyId);
        $condition=$this->CustomFunction->userWisePermission(NULL,$this->luserId,$this->userType,$this->agencyId,'Property.project_id','Property.user_id');
        $dealStatusCondition = $this->_getOverdueInvoiceDealStatusCondition();
        $this->Prg->commonProcess();
        $pageLimit = $this->_invoicePaginatorLimit();
        $this->Paginator->settings = $this->paginate;
        $this->Paginator->settings['fields'] = $this->fieldIdArr;
        $this->Paginator->settings['conditions'] = array($this->Invpastdue->parseCriteria($this->Prg->parsedParams()),'DealsPayment.id IS NULL',$conditions,$condition,$agencyCondition,$dealStatusCondition);
        // Show one overdue row per deal, so the selected page limit and total record count match the visible table.
        $this->Paginator->settings['group'] = array('Deal.id');
        $this->Paginator->settings['limit']=$pageLimit;
        $this->Paginator->settings['maxLimit']=max((int)$this->maxLimit, $pageLimit, 500);
        $dealRows = $this->Paginator->paginate();
        $reportAgencyId = $selectedAgencyId>0 ? $selectedAgencyId : ($this->_isSuperAdminSession((array)$this->adminValue) ? 0 : (int)$this->agencyId);
        $this->set('reportAgency',$this->_fetchReportAgencyHeader($reportAgencyId));
        $this->_setInvoiceAgencyFilterVars($selectedAgencyId);
        $this->set('deal', $this->_summarizeOverduesByDeal($dealRows,$overdueTillDate));
	if ($this->request->is('ajax'))
        {
            $this->render('index','ajax'); // View, Layout
        }
    }
    public function printletter($clientId,$dealId,$plansPaymentId)
    {
        $this->layout = null;
        $letterData = $this->_getDemandLetterDealData($clientId,$dealId,$plansPaymentId);
        if(empty($letterData['deal']))
        {
            $this->Session->setFlash(__('Invalid Post.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        $this->set('autoPrint',true);
        $this->set('enablePdfDownload',false);
        $this->set('pdfFileName','Demand_Letter_'.$letterData['deal']['Deal']['invoice_no']);
        $this->set('overdueInstallmentCount',$letterData['overdueInstallmentCount']);
        $this->set('totalOverdueAmountValue',$letterData['totalOverdueAmountValue']);
        $this->set('totalDueCount',isset($letterData['totalDueCount']) ? $letterData['totalDueCount'] : $letterData['overdueInstallmentCount']);
        $this->set('bankDetails',isset($letterData['bankDetails']) ? $letterData['bankDetails'] : array());
        $this->set('agencyData',isset($letterData['agencyData']) ? $letterData['agencyData'] : array());
        $this->set('deal',$letterData['deal']);
    }
    public function downloadletterpdf($clientId,$dealId,$plansPaymentId)
    {
        $this->layout = null;
        $letterData = $this->_getDemandLetterDealData($clientId,$dealId,$plansPaymentId);
        if(empty($letterData['deal']))
        {
            $this->Session->setFlash(__('Invalid Post.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        $this->set('autoPrint',false);
        $this->set('enablePdfDownload',true);
        $this->set('pdfFileName','Demand_Letter_'.$letterData['deal']['Deal']['invoice_no']);
        $this->set('overdueInstallmentCount',$letterData['overdueInstallmentCount']);
        $this->set('totalOverdueAmountValue',$letterData['totalOverdueAmountValue']);
        $this->set('totalDueCount',isset($letterData['totalDueCount']) ? $letterData['totalDueCount'] : $letterData['overdueInstallmentCount']);
        $this->set('bankDetails',isset($letterData['bankDetails']) ? $letterData['bankDetails'] : array());
        $this->set('agencyData',isset($letterData['agencyData']) ? $letterData['agencyData'] : array());
        $this->set('deal',$letterData['deal']);
        $this->render('printletter');
    }
    public function lettercontent($clientId,$dealId,$plansPaymentId)
    {
        $this->layout = null;
        $letterData = $this->_getDemandLetterDealData($clientId,$dealId,$plansPaymentId);
        if(empty($letterData['deal']))
        {
            throw new NotFoundException(__('Invalid post'));
        }
        $this->set('autoPrint',false);
        $this->set('enablePdfDownload',false);
        $this->set('pdfFileName','Demand_Letter_'.$letterData['deal']['Deal']['invoice_no']);
        $this->set('overdueInstallmentCount',$letterData['overdueInstallmentCount']);
        $this->set('totalOverdueAmountValue',$letterData['totalOverdueAmountValue']);
        $this->set('totalDueCount',isset($letterData['totalDueCount']) ? $letterData['totalDueCount'] : $letterData['overdueInstallmentCount']);
        $this->set('bankDetails',isset($letterData['bankDetails']) ? $letterData['bankDetails'] : array());
        $this->set('agencyData',isset($letterData['agencyData']) ? $letterData['agencyData'] : array());
        $this->set('deal',$letterData['deal']);
        $this->render('printletter');
    }
    public function bulkletterpdf()
    {
        $this->layout = null;
        $rawIds = array();
        if(isset($this->request->data['ids']))
        {
            $rawIds = $this->request->data['ids'];
        }
        if(!is_array($rawIds))
        {
            $rawIds = array();
        }
        $letters = array();
        $processedDeals = array();
        foreach($rawIds as $value)
        {
            if(!$value)
            {
                continue;
            }
            $expArr = explode(",",$value);
            if(count($expArr)<3)
            {
                continue;
            }
            $clientId = $expArr[0];
            $dealId = $expArr[1];
            $plansPaymentId = $expArr[2];
            $dealKey = $clientId.'-'.$dealId;
            if(isset($processedDeals[$dealKey]))
            {
                continue;
            }
            $processedDeals[$dealKey] = true;
            $letterData = $this->_getDemandLetterDealData($clientId,$dealId,$plansPaymentId);
            if(empty($letterData['deal']))
            {
                continue;
            }
            $letters[] = array(
                'deal'=>$letterData['deal'],
                'overdueInstallmentCount'=>$letterData['overdueInstallmentCount'],
                'totalOverdueAmountValue'=>$letterData['totalOverdueAmountValue'],
                'totalDueCount'=>isset($letterData['totalDueCount']) ? $letterData['totalDueCount'] : $letterData['overdueInstallmentCount'],
                'bankDetails'=>isset($letterData['bankDetails']) ? $letterData['bankDetails'] : array(),
                'agencyData'=>isset($letterData['agencyData']) ? $letterData['agencyData'] : array()
            );
        }
        if(empty($letters))
        {
            $this->Session->setFlash(__('Please select at least one valid record.'),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        $this->set('letters',$letters);
        $this->set('bulkPdfFileName','Demand_Letters_'.date('Ymd_His'));
    }
    public function exportcsv()
    {
        $this->autoRender = false;
        $this->layout = null;
        $overdueTillDate = CakeTime::format('Y-m-d',CakeTime::convert(time(),$this->siteTimezone));
        if(isset($this->request->params['named']['isSearch']))
        {
            $requestStartDate=$this->CustomFunction->dateFormatBeforeSave(isset($this->request->params['named']['start_date']) ? $this->request->params['named']['start_date'] : null);
            $requestEndDate=$this->CustomFunction->dateFormatBeforeSave(isset($this->request->params['named']['end_date']) ? $this->request->params['named']['end_date'] : null);
            $conditions=array();
            if(strlen($requestEndDate)>0)
            {
                $overdueTillDate=$requestEndDate;
            }
            elseif(strlen($requestStartDate)>0)
            {
                $overdueTillDate=$requestStartDate;
            }
            if(strlen($requestStartDate)>0 && strlen($requestEndDate)>0)
            $conditions=array('Invpastdue.date BETWEEN ? AND ?'=>array($requestStartDate,$requestEndDate));
            elseif(strlen($requestEndDate)>0)
            $conditions=array('Invpastdue.date <='=>$requestEndDate);
            elseif(strlen($requestStartDate)>0)
            $conditions=array('Invpastdue.date >='=>$requestStartDate);
        }
        else
        {
            $startDate=CakeTime::format('Y-m-d',CakeTime::convert(time(),$this->siteTimezone));
            $conditions=array('Invpastdue.date <='=>$startDate);
            $overdueTillDate=$startDate;
        }
        $selectedAgencyId = $this->_selectedInvoiceAgencyId();
        $agencyCondition = $this->_invoiceAgencyCondition($selectedAgencyId);
        $condition=$this->CustomFunction->userWisePermission(NULL,$this->luserId,$this->userType,$this->agencyId,'Property.project_id','Property.user_id');
        $dealStatusCondition = $this->_getOverdueInvoiceDealStatusCondition();
        $searchCriteria = $this->Invpastdue->parseCriteria($this->request->params['named']);
        $rows = $this->Invpastdue->find('all',array(
            'fields'=>$this->fieldIdArr,
            'conditions'=>array($searchCriteria,'DealsPayment.id IS NULL',$conditions,$condition,$agencyCondition,$dealStatusCondition),
            'order'=>array('Client.name'=>'asc')
        ));
        $rows = $this->_summarizeOverduesByDeal($rows,$overdueTillDate);
        $filename = "due_invoices_export_" . date("Y-m-d_H-i-s") . ".csv";
        header("Content-Type: text/csv; charset=utf-8");
        header("Content-Disposition: attachment; filename=\"".$filename."\"");
        header("Pragma: no-cache");
        header("Expires: 0");
        $output = fopen("php://output", "w");
        fputcsv($output, array('Client Name','Phone','Property','Invoice No','Payment Due On','Overdue Installments','Balance','Overdue+Late Fees','Late Fees','Due Date'));
        foreach($rows as $row)
        {
            $overdueCount = isset($row['Invpastdue']['overdue_count']) ? (int)$row['Invpastdue']['overdue_count'] : 0;
            $paymentDueOn = $overdueCount>1 ? __('Overdue Installments').': '.$overdueCount : $row['Invpastdue']['name'];
            $totalOverdue = 0;
            if($row['Deal']['plan_id']!=1)
            {
                if(isset($row['Invpastdue']['total_overdue']))
                $totalOverdue=(float)$row['Invpastdue']['total_overdue'];
                else
                $totalOverdue=(float)$row['Invpastdue']['amount'];
            }
            $lateFeeAmount = isset($row['Invpastdue']['total_late_fee']) ? (float)$row['Invpastdue']['total_late_fee'] : 0;
            $overdueWithLate = isset($row['Invpastdue']['total_overdue_with_late']) ? (float)$row['Invpastdue']['total_overdue_with_late'] : ($totalOverdue + $lateFeeAmount);
            $dueDate = $this->Time->format($this->sysDay.$this->dateSep.$this->sysMonth.$this->dateSep.$this->sysYear,h($row['Invpastdue']['date']));
            fputcsv($output, array(
                $row['Client']['name'],
                $row['Client']['phone'],
                $row['Property']['name'],
                $row['Deal']['invoice_no'],
                $paymentDueOn,
                $overdueCount,
                $this->currency.CakeNumber::format($totalOverdue),
                $this->currency.CakeNumber::format($overdueWithLate),
                $this->currency.CakeNumber::format($lateFeeAmount),
                $dueDate
            ));
        }
        fclose($output);
        exit();
    }
    public function exportpdf()
    {
        $this->layout = false;
        $overdueTillDate = CakeTime::format('Y-m-d',CakeTime::convert(time(),$this->siteTimezone));
        if(isset($this->request->params['named']['isSearch']))
        {
            $requestStartDate=$this->CustomFunction->dateFormatBeforeSave(isset($this->request->params['named']['start_date']) ? $this->request->params['named']['start_date'] : null);
            $requestEndDate=$this->CustomFunction->dateFormatBeforeSave(isset($this->request->params['named']['end_date']) ? $this->request->params['named']['end_date'] : null);
            $conditions=array();
            if(strlen($requestEndDate)>0)
            {
                $overdueTillDate=$requestEndDate;
            }
            elseif(strlen($requestStartDate)>0)
            {
                $overdueTillDate=$requestStartDate;
            }
            if(strlen($requestStartDate)>0 && strlen($requestEndDate)>0)
            {
                $conditions=array('Invpastdue.date BETWEEN ? AND ?'=>array($requestStartDate,$requestEndDate));
            }
            elseif(strlen($requestEndDate)>0)
            {
                $conditions=array('Invpastdue.date <='=>$requestEndDate);
            }
            elseif(strlen($requestStartDate)>0)
            {
                $conditions=array('Invpastdue.date >='=>$requestStartDate);
            }
        }
        else
        {
            $overdueTillDate=CakeTime::format('Y-m-d',CakeTime::convert(time(),$this->siteTimezone));
            $conditions=array('Invpastdue.date <='=>$overdueTillDate);
        }
        $selectedAgencyId = $this->_selectedInvoiceAgencyId();
        $agencyCondition = $this->_invoiceAgencyCondition($selectedAgencyId);
        $permissionCondition=$this->CustomFunction->userWisePermission(NULL,$this->luserId,$this->userType,$this->agencyId,'Property.project_id','Property.user_id');
        $dealStatusCondition = $this->_getOverdueInvoiceDealStatusCondition();
        $searchCriteria = $this->Invpastdue->parseCriteria($this->request->params['named']);
        $rows = $this->Invpastdue->find('all',array(
            'fields'=>$this->fieldIdArr,
            'conditions'=>array($searchCriteria,'DealsPayment.id IS NULL',$conditions,$permissionCondition,$agencyCondition,$dealStatusCondition),
            'order'=>array('Client.name'=>'asc')
        ));
        $reportAgencyId = $selectedAgencyId>0 ? $selectedAgencyId : ($this->_isSuperAdminSession((array)$this->adminValue) ? 0 : (int)$this->agencyId);
        $this->set('deal',$this->_summarizeOverduesByDeal($rows,$overdueTillDate));
        $this->set('reportAgency',$this->_fetchReportAgencyHeader($reportAgencyId));
        $this->set('pdfFilename','due_invoices_'.date('Ymd_His').'.pdf');
    }
    public function deleteall()
    {
        $action = isset($this->request->data['action']) ? $this->request->data['action'] : null;
        try
        {
            if ($this->request->is('post'))
            {
                if (!in_array($action, array('Email', 'SMS', 'WhatsApp'))) {
                    $this->Session->setFlash(__('Invalid action.'),'flash',array('alert'=>'danger'));
                    return $this->redirect(array('action' => 'index'));
                }
                $processedDeals = array();
                $targetCount = 0;
                $successCount = 0;
                $failedCount = 0;
                if (!empty($this->data['Invpastdue']['id']))
                {
                    foreach($this->data['Invpastdue']['id'] as $key => $value)
                    {
                        if($value!=0)
                        {
                            $expArr=explode(",",$value);
                            if (count($expArr) < 2) {
                                continue;
                            }
                            $clientId = $expArr[0];
                            $dealId = $expArr[1];
                            $plansPaymentId = isset($expArr[2]) ? $expArr[2] : null;
                            $dealKey = $clientId . '-' . $dealId;
                            // Send one message per deal even if multiple overdue rows are selected.
                            if (isset($processedDeals[$dealKey])) {
                                continue;
                            }
                            $processedDeals[$dealKey] = true;
                            $targetCount++;
                            if($action=="Email")
                            {
                                if ($this->sendmail($clientId,$dealId,$plansPaymentId,false)) {
                                    $successCount++;
                                } else {
                                    $failedCount++;
                                }
                            }
                            if($action=="SMS")
                            {
                                if ($this->sendsms($clientId,$dealId,$plansPaymentId,false)) {
                                    $successCount++;
                                } else {
                                    $failedCount++;
                                }
                            }
                            if($action=="WhatsApp")
                            {
                                if ($this->sendwhatsapp($clientId,$dealId,$plansPaymentId,false)) {
                                    $successCount++;
                                } else {
                                    $failedCount++;
                                }
                            }
                        }
                    }
                }
                if($targetCount===0)
                {
                    $this->Session->setFlash(__('Please select at least one record.'),'flash',array('alert'=>'danger'));
                }
                elseif($successCount===$targetCount)
                {
                    $this->Session->setFlash(sprintf(__('%s sent successfully to %d selected record(s).'),$action,$successCount),'flash',array('alert'=>'success'));
                }
                elseif($successCount>0)
                {
                    $this->Session->setFlash(sprintf(__('%s sent to %d selected record(s). Failed for %d selected record(s).'),$action,$successCount,$failedCount),'flash',array('alert'=>'danger'));
                }
                else
                {
                    $this->Session->setFlash(sprintf(__('%s could not be sent to selected record(s).'),$action),'flash',array('alert'=>'danger'));
                }
            }        
            $this->redirect(array('action' => 'index'));
        }
        catch (Exception $e)
        {
            $this->Session->setFlash($e->getMessage(),'flash',array('alert'=>'danger'));
            return $this->redirect(array('action' => 'index'));
        }
    }
    public function invoicemail($clientId,$dealId,$plansPaymentId)
    {
        $this->sendmail($clientId,$dealId,$plansPaymentId);
        $this->redirect(array('action' => 'index'));
    }
    public function invoicesms($clientId,$dealId,$plansPaymentId)
    {
        $this->sendsms($clientId,$dealId,$plansPaymentId);
        $this->redirect(array('action' => 'index'));
    }
    public function invoicewhatsapp($clientId,$dealId,$plansPaymentId)
    {
        $result = $this->sendwhatsapp($clientId,$dealId,$plansPaymentId,true);
        if(is_array($result) && !empty($result['url']))
        {
            return $this->redirect($result['url']);
        }
        return $this->redirect(array('action' => 'index'));
    }
    public function sendmail($clientId,$dealId,$plansPaymentId,$setFlash=true)
    {
        $this->layout=null;
        try
        {
            $mailOverdueTillDate = CakeTime::format('Y-m-d',CakeTime::convert(time(),$this->siteTimezone));
            $dealStatusCondition = $this->_getOverdueInvoiceDealStatusCondition();
            $mailRows = $this->Invpastdue->find('all',array(
                'fields'=>$this->fieldIdArr,
                'conditions'=>array(
                    'DealsPayment.id IS NULL',
                    'Deal.client_id'=>$clientId,
                    'Deal.id'=>$dealId,
                    'Invpastdue.date <='=>$mailOverdueTillDate,
                    $dealStatusCondition
                ),
                'order'=>array('Invpastdue.date'=>'asc')
            ));
            if(empty($mailRows))
            {
                if($setFlash)
                $this->Session->setFlash(__('No email to send!'),'flash',array('alert'=>'danger'));
                return false;
            }
            $deal = $mailRows[0];
            $oldestDueDate = $mailRows[0]['Invpastdue']['date'];
            $totalOverdueAmountValue = 0;
            $overdueInstallmentCount = 0;
            foreach($mailRows as $mailRow)
            {
                $overdueInstallmentCount++;
                if($mailRow['Deal']['plan_id']!=1)
                {
                    $totalOverdueAmountValue += (float)$mailRow['Invpastdue']['amount'];
                }
                if($mailRow['Invpastdue']['date'] < $oldestDueDate)
                {
                    $oldestDueDate = $mailRow['Invpastdue']['date'];
                    $deal = $mailRow;
                }
            }
            if($totalOverdueAmountValue<=0)
            {
                $totalOverdueAmountValue=(float)$deal['Invpastdue']['amount'];
            }
            $totalOverdueAmount = CakeNumber::format($totalOverdueAmountValue);
            $totalOverdueInstallmentCount = $overdueInstallmentCount;
            $deal['Invpastdue']['date'] = $oldestDueDate;
            $area=array();
            $subject="Demand For Due Payment";
            $email=$deal['Client']['email'];
            $this->loadModel('Emailsetting');
            $emailSettingArr=$this->Emailsetting->findById(1);
            $settingType=$emailSettingArr['Emailsetting']['type'];
            if($email)
            {
                if($this->emailNotification)
                {                          
                    /* Send Email */
                    $Email = new CakeEmail();
                    $Email->transport($this->emailSettype);
                    if($this->emailSettype=="Smtp")
                    $Email->config(array('host' => $this->emailHost,'port' =>  $this->emailPort,'username' => $this->emailUsername,'password' => $this->emailPassword));
                    $Email->from(array($this->siteEmail =>$this->siteName));
                    $Email->to($email);
                    $Email->viewVars(array('deal'=>$deal,
                                           'dateSep'=>$this->dateSep,
                                           'sysDay'=>$this->sysDay,
                                           'sysMonth'=>$this->sysMonth,
                                           'sysYear'=>$this->sysYear,
                                           'siteAccount'=>$this->siteAccount,
                                           'siteOrganization'=>$this->siteOrganization,
                                           'contact'=>$this->contact,
                                           'currencyName'=>$this->currencyName,
                                           'overdueInstallmentCount'=>$overdueInstallmentCount,
                                           'totalOverdueInstallmentCount'=>$totalOverdueInstallmentCount,
                                           'totalOverdueAmount'=>$totalOverdueAmount,
                                           'totalOverdueAmountValue'=>$totalOverdueAmountValue));
                    $Email->from(array($this->siteEmail =>$this->siteName));
                    $Email->to($email);
                    $Email->template('mailinvoice','default');
                    $Email->emailFormat('html');
                    $Email->subject($subject);
                    $Email->send();
                    if($setFlash)
                    $this->Session->setFlash(__('Email has been sent.'),'flash',array('alert'=>'success'));
                    return true;
                    /* End Email */
                }
                if($setFlash)
                $this->Session->setFlash(__('Email notification is disabled.'),'flash',array('alert'=>'danger'));
                return false;
            }
            else
            {
                if($setFlash)
                $this->Session->setFlash(__('No email to send!'),'flash',array('alert'=>'danger'));
                return false;
            }
        }
        catch (Exception $e)
        {
            if($setFlash)
            $this->Session->setFlash($e->getMessage(),'flash',array('alert'=>'danger'));
            return false;
        }
    }
    public function sendsms($clientId,$dealId,$plansPaymentId,$setFlash=true)
    {
        $this->layout=null;
        try
        {
            $smsOverdueTillDate = CakeTime::format('Y-m-d',CakeTime::convert(time(),$this->siteTimezone));
            $dealStatusCondition = $this->_getOverdueInvoiceDealStatusCondition();
            $dealRows = $this->Invpastdue->find('all',array('fields'=>$this->fieldIdArr,
                                                       'conditions'=>array('DealsPayment.id IS NULL','Deal.client_id'=>$clientId,'Deal.id'=>$dealId,'Invpastdue.date <='=>$smsOverdueTillDate,$dealStatusCondition),
                                                       'order'=>array('Invpastdue.date'=>'asc')));
            if (empty($dealRows)) {
                if($setFlash)
                $this->Session->setFlash(__('No sms to send!'),'flash',array('alert'=>'danger'));
                return false;
            }
            $deal = $dealRows[0];
            $oldestDueDate = $dealRows[0]['Invpastdue']['date'];
            $totalOverdueAmountValue = 0;
            $overdueInstallmentCount = 0;
            foreach ($dealRows as $row) {
                $overdueInstallmentCount++;
                if ($row['Deal']['plan_id'] != 1) {
                    $totalOverdueAmountValue += (float)$row['Invpastdue']['amount'];
                }
                if ($row['Invpastdue']['date'] < $oldestDueDate) {
                    $oldestDueDate = $row['Invpastdue']['date'];
                    $deal = $row;
                }
            }
            if ($totalOverdueAmountValue <= 0) {
                $totalOverdueAmountValue = (float)$deal['Invpastdue']['amount'];
            }
            $mobileNo=$deal['Client']['phone'];$siteName=$this->siteName;
            if($mobileNo)
            {
                if($this->smsNotification)
                {
                    /* Send Sms */
                    $this->loadModel('Smstemplate');
                    $smsTemplateArr=$this->Smstemplate->findByType('IDL');
                    if($smsTemplateArr['Smstemplate']['status']=="Published")
                    {
                        $area=array();
                        $clientName=$deal['Client']['name'];
                        $dueDate=CakeTime::format($this->sysDay.$this->dateSep.$this->sysMonth.$this->dateSep.$this->sysYear,$oldestDueDate);
                        $totalOverdueAmount=CakeNumber::format($totalOverdueAmountValue);
                        $totalOverdueInstallmentCount=$overdueInstallmentCount;
                        $amount=$totalOverdueAmount;
                        $totalOverdue=$totalOverdueAmount;
                        $overdueAge = (int)floor((strtotime($smsOverdueTillDate) - strtotime($oldestDueDate)) / 86400);
                        if ($overdueAge < 0) {
                            $overdueAge = 0;
                        }
                        $message=$this->_renderTemplateString((string)$smsTemplateArr['Smstemplate']['description'], get_defined_vars());
                        $url="$this->siteDomain";
                        if (stripos($message,'total overdue installments') === false) {
                            $message = trim($message) . ' Total Overdue Installments: ' . $overdueInstallmentCount . '.';
                        }
                        if (stripos($message,'total overdue amount') === false) {
                            $message = trim($message) . ' Total Overdue Amount: ' . $totalOverdueAmount . '.';
                        }
                        if (stripos($message,'overdue age') === false) {
                            $message = trim($message) . ' Overdue Age: ' . $overdueAge . ' days.';
                        }
                        $this->CustomFunction->sendSms($mobileNo,$message,$this->smsSettingArr);
                        if($setFlash)
                        $this->Session->setFlash(__('Sms has been sent.'),'flash',array('alert'=>'success'));
                        return true;
                    }
                    if($setFlash)
                    $this->Session->setFlash(__('SMS template is not published.'),'flash',array('alert'=>'danger'));
                    return false;
                    /* End Sms */
                }
                if($setFlash)
                $this->Session->setFlash(__('Sms notification is disabled.'),'flash',array('alert'=>'danger'));
                return false;
            }
            else
            {
                if($setFlash)
                $this->Session->setFlash(__('No sms to send!'),'flash',array('alert'=>'danger'));
                return false;
            }
        }
        catch (Exception $e)
        {
            if($setFlash)
            $this->Session->setFlash($e->getMessage(),'flash',array('alert'=>'danger'));
            return false;
        }
    }
    public function sendwhatsapp($clientId,$dealId,$plansPaymentId,$setFlash=true)
    {
        $this->layout = null;
        try
        {
            $waOverdueTillDate = CakeTime::format('Y-m-d', CakeTime::convert(time(), $this->siteTimezone));
            $dealStatusCondition = $this->_getOverdueInvoiceDealStatusCondition();
            $dealRows = $this->Invpastdue->find('all', array(
                'fields' => $this->fieldIdArr,
                'conditions' => array(
                    'DealsPayment.id IS NULL',
                    'Deal.client_id' => $clientId,
                    'Deal.id' => $dealId,
                    'Invpastdue.date <=' => $waOverdueTillDate,
                    $dealStatusCondition
                ),
                'order' => array('Invpastdue.date' => 'asc')
            ));
            if (empty($dealRows)) {
                if ($setFlash) {
                    $this->Session->setFlash(__('No overdue records found for WhatsApp notification!'), 'flash', array('alert' => 'danger'));
                }
                return false;
            }

            $deal = $dealRows[0];
            $oldestDueDate = $dealRows[0]['Invpastdue']['date'];
            $totalOverdueAmountValue = 0;
            $overdueInstallmentCount = 0;
            foreach ($dealRows as $row) {
                $overdueInstallmentCount++;
                if ($row['Deal']['plan_id'] != 1) {
                    $totalOverdueAmountValue += (float)$row['Invpastdue']['amount'];
                }
                if ($row['Invpastdue']['date'] < $oldestDueDate) {
                    $oldestDueDate = $row['Invpastdue']['date'];
                    $deal = $row;
                }
            }
            if ($totalOverdueAmountValue <= 0) {
                $totalOverdueAmountValue = (float)$deal['Invpastdue']['amount'];
            }

            // Recipient WhatsApp phone number
            $rawMobile = !empty($deal['Client']['mobile']) ? trim((string)$deal['Client']['mobile']) : (!empty($deal['Client']['phone']) ? trim((string)$deal['Client']['phone']) : '');
            $cleanWhatsappNo = preg_replace('/[^0-9]/', '', (string)$rawMobile);
            if (substr($cleanWhatsappNo, 0, 2) === '03' && strlen($cleanWhatsappNo) === 11) {
                $cleanWhatsappNo = '92' . substr($cleanWhatsappNo, 1);
            } elseif (substr($cleanWhatsappNo, 0, 1) === '3' && strlen($cleanWhatsappNo) === 10) {
                $cleanWhatsappNo = '92' . $cleanWhatsappNo;
            }

            if ($cleanWhatsappNo === '') {
                if ($setFlash) {
                    $this->Session->setFlash(__('Client WhatsApp / mobile phone number is missing.'), 'flash', array('alert' => 'danger'));
                }
                return false;
            }

            // Backend notification toggle for Demand Letter PDF attachment
            $NotificationSetting = ClassRegistry::init('Admin.NotificationSetting');
            $includePdf = true;
            if ($NotificationSetting) {
                $includePdf = $NotificationSetting->isEnabled('whatsapp_demand_pdf_attachment', true);
            }

            $clientName = !empty($deal['Client']['name']) ? trim((string)$deal['Client']['name']) : 'Valued Client';
            $invoiceNo = !empty($deal['Deal']['invoice_no']) ? trim((string)$deal['Deal']['invoice_no']) : '-';
            $propertyName = !empty($deal['Property']['name']) ? trim((string)$deal['Property']['name']) : '-';
            $projectName = !empty($deal['Project']['name']) ? trim((string)$deal['Project']['name']) : (!empty($this->siteOrganization) ? $this->siteOrganization : 'White Villas');
            $currencySym = !empty($this->currency) ? $this->currency : 'PKR ';
            $formattedAmount = $currencySym . CakeNumber::format($totalOverdueAmountValue);
            $formattedDueDate = CakeTime::format($this->sysDay . $this->dateSep . $this->sysMonth . $this->dateSep . $this->sysYear, $oldestDueDate);
            $overdueAge = (int)floor((strtotime($waOverdueTillDate) - strtotime($oldestDueDate)) / 86400);
            if ($overdueAge < 0) {
                $overdueAge = 0;
            }

            // Demand letter PDF download URL
            $pdfUrl = Router::url(array(
                'plugin' => 'admin',
                'controller' => 'Invpastdues',
                'action' => 'downloadletterpdf',
                $clientId,
                $dealId,
                $plansPaymentId
            ), true);

            // Compose WhatsApp message
            $lines = array();
            if ($includePdf) {
                $lines[] = '📄 *DEMAND NOTICE - OVERDUE PAYMENT*';
                $lines[] = '━━━━━━━━━━━━━━━━━━━━';
                $lines[] = 'Dear *' . $clientName . '*,';
                $lines[] = '';
                $lines[] = 'This is an official notice regarding overdue installments for your booking in *' . $projectName . '*.';
                $lines[] = '';
                $lines[] = '🧾 *Membership / Deal No:* ' . $invoiceNo;
                $lines[] = '🏡 *Property / Unit:* ' . $propertyName;
                $lines[] = '💰 *Total Overdue Amount:* ' . $formattedAmount;
                $lines[] = '🔢 *Overdue Installments:* ' . $overdueInstallmentCount;
                $lines[] = '📅 *Oldest Due Date:* ' . $formattedDueDate . ' (' . $overdueAge . ' days past due)';
                $lines[] = '';
                $lines[] = '📥 *Download Official Demand Letter PDF:*';
                $lines[] = $pdfUrl;
                $lines[] = '';
                $lines[] = 'Please clear your outstanding dues promptly to safeguard your booking and avoid additional late surcharges.';
                $lines[] = '━━━━━━━━━━━━━━━━━━━━';
                $lines[] = '🏢 *' . (!empty($this->siteOrganization) ? $this->siteOrganization : $projectName) . '*';
                if (!empty($this->contact)) {
                    $lines[] = '📞 Contact: ' . $this->contact;
                }
            } else {
                $lines[] = '⚠️ *OVERDUE PAYMENT REMINDER*';
                $lines[] = '━━━━━━━━━━━━━━━━━━━━';
                $lines[] = 'Dear *' . $clientName . '*,';
                $lines[] = '';
                $lines[] = 'This is a reminder regarding overdue installments for your booking in *' . $projectName . '*.';
                $lines[] = '';
                $lines[] = '🧾 *Membership / Deal No:* ' . $invoiceNo;
                $lines[] = '🏡 *Property / Unit:* ' . $propertyName;
                $lines[] = '💰 *Total Overdue Amount:* ' . $formattedAmount;
                $lines[] = '🔢 *Overdue Installments:* ' . $overdueInstallmentCount;
                $lines[] = '📅 *Oldest Due Date:* ' . $formattedDueDate . ' (' . $overdueAge . ' days past due)';
                $lines[] = '';
                $lines[] = 'Please clear your pending dues promptly to avoid additional late surcharges.';
                $lines[] = '━━━━━━━━━━━━━━━━━━━━';
                $lines[] = '🏢 *' . (!empty($this->siteOrganization) ? $this->siteOrganization : $projectName) . '*';
                if (!empty($this->contact)) {
                    $lines[] = '📞 Contact: ' . $this->contact;
                }
            }
            $messageText = implode("\n", $lines);

            // Attempt LeadBot Gateway dispatch
            $projectId = !empty($deal['Property']['project_id']) ? (int)$deal['Property']['project_id'] : (!empty($deal['Deal']['project_id']) ? (int)$deal['Deal']['project_id'] : (!empty($this->agencyId) ? (int)$this->agencyId : 0));
            $userId = !empty($deal['Deal']['user_id']) ? (int)$deal['Deal']['user_id'] : (!empty($this->luserId) ? (int)$this->luserId : 0);

            App::uses('LeadbotGatewayClient', 'Lib');
            $GatewayModel = ClassRegistry::init('Admin.LeadbotGatewaySetting');
            $LogModel = ClassRegistry::init('Admin.LeadbotNotificationLog');

            $gatewaySettings = $GatewayModel ? $GatewayModel->getSettings($projectId, $userId, true) : array();
            $hasLeadbotApi = !empty($gatewaySettings['api_key']);
            $leadbotAutoWa = !empty($gatewaySettings['auto_send_whatsapp']);

            if ($hasLeadbotApi && $leadbotAutoWa) {
                $gatewayUrl = !empty($gatewaySettings['gateway_url']) ? $gatewaySettings['gateway_url'] : LeadbotGatewayClient::DEFAULT_LOCAL_URL;
                $apiKey = $gatewaySettings['api_key'];
                $waSession = !empty($gatewaySettings['selected_wa_session']) ? $gatewaySettings['selected_wa_session'] : '';

                $params = array(
                    'recipient' => $cleanWhatsappNo,
                    'message'   => $messageText,
                    'session'   => $waSession,
                    'agency_id' => $projectId
                );
                if ($includePdf) {
                    $pdfHost = strtolower((string)parse_url($pdfUrl, PHP_URL_HOST));
                    $isLocal = in_array($pdfHost, array('localhost', '127.0.0.1', '::1'), true);
                    $isRemote = (strpos($gatewayUrl, 'localhost') === false && strpos($gatewayUrl, '127.0.0.1') === false);
                    $hasBinaryExt = preg_match('/\.(pdf)$/i', (string)parse_url($pdfUrl, PHP_URL_PATH));
                    if ((!$isLocal || !$isRemote) && $hasBinaryExt) {
                        $params['media_url'] = $pdfUrl;
                    }
                }

                $sendResult = LeadbotGatewayClient::sendWhatsApp($gatewayUrl, $apiKey, $params);
                $isSuccess = !empty($sendResult['success']);
                $errStr = !empty($sendResult['error']) ? $sendResult['error'] : '';

                if ($LogModel) {
                    $LogModel->recordLog(array(
                        'project_id'                => $projectId,
                        'user_id'                   => $userId > 0 ? $userId : null,
                        'channel'                   => 'whatsapp',
                        'event_key'                 => 'demand_letter_whatsapp',
                        'recipient_phone'           => $cleanWhatsappNo,
                        'recipient_name'            => $clientName,
                        'message'                   => $messageText,
                        'gateway_profile_or_device' => !empty($waSession) ? $waSession : 'Default WhatsApp Profile',
                        'status'                    => $isSuccess ? 'sent' : 'failed',
                        'response_code'             => isset($sendResult['latency_ms']) ? $sendResult['latency_ms'] : 0,
                        'response_payload'          => isset($sendResult['raw_response']) ? $sendResult['raw_response'] : '',
                        'error_message'             => $errStr,
                        'is_test'                   => 0
                    ));
                }

                if ($isSuccess) {
                    if ($setFlash) {
                        $this->Session->setFlash(__('WhatsApp demand notice sent successfully to %s.', $cleanWhatsappNo), 'flash', array('alert' => 'success'));
                    }
                    return true;
                } else {
                    if ($setFlash) {
                        $this->Session->setFlash(__('WhatsApp delivery failed: %s', $errStr), 'flash', array('alert' => 'danger'));
                    }
                    return false;
                }
            }

            // Direct wa.me fallback
            $directWaUrl = 'https://wa.me/' . $cleanWhatsappNo . '?text=' . rawurlencode($messageText);
            if ($setFlash) {
                return array('success' => true, 'url' => $directWaUrl);
            }
            return true;
        }
        catch (Exception $e)
        {
            if ($setFlash) {
                $this->Session->setFlash($e->getMessage(), 'flash', array('alert' => 'danger'));
            }
            return false;
        }
    }
    protected function _summarizeOverduesByDeal($rows,$overdueTillDate=null)
    {
        $summary = array();
        if(!$overdueTillDate)
        {
            $overdueTillDate = CakeTime::format('Y-m-d',CakeTime::convert(time(),$this->siteTimezone));
        }
        $graceDays = (int)$this->dueDays;
        if($graceDays < 0)
        {
            $graceDays = 0;
        }
        $lateFeePercent = (float)$this->lateFees;
        if($lateFeePercent < 0)
        {
            $lateFeePercent = 0;
        }
        $overdueTillTs = strtotime($overdueTillDate);
        $dealStatusCondition = $this->_getOverdueInvoiceDealStatusCondition();
        foreach ($rows as $row) {
            $dealId = $row['Deal']['id'];
            $clientId = $row['Client']['id'];
            if (isset($summary[$dealId])) {
                continue;
            }
            $overdueRows = $this->Invpastdue->find('all',array(
                'fields' => $this->fieldIdArr,
                'conditions' => array(
                    'DealsPayment.id IS NULL',
                    'Deal.client_id' => $clientId,
                    'Deal.id' => $dealId,
                    'Invpastdue.date <=' => $overdueTillDate,
                    $dealStatusCondition
                ),
                'order' => array('Invpastdue.date' => 'asc')
            ));
            if (empty($overdueRows)) {
                continue;
            }
            $item = $overdueRows[0];
            $oldestDueDate = $overdueRows[0]['Invpastdue']['date'];
            $totalOverdue = 0;
            $totalLateFee = 0;
            $overdueCount = 0;
            foreach ($overdueRows as $overdueRow) {
                $overdueCount++;
                if ($overdueRow['Deal']['plan_id'] != 1) {
                    $rowAmount = (float)$overdueRow['Invpastdue']['amount'];
                    $totalOverdue += $rowAmount;
                    if($lateFeePercent > 0 && $overdueTillTs !== false)
                    {
                        $rowDueDate = isset($overdueRow['Invpastdue']['date']) ? $overdueRow['Invpastdue']['date'] : null;
                        $rowDueTs = strtotime((string)$rowDueDate);
                        if($rowDueTs !== false)
                        {
                            $graceTillTs = strtotime('+'.$graceDays.' days', $rowDueTs);
                            if($graceTillTs !== false && $graceTillTs < $overdueTillTs)
                            {
                                $lateDays = (int)floor(($overdueTillTs - $rowDueTs) / 86400);
                                if($lateDays > 0)
                                {
                                    $totalLateFee += round((($rowAmount * $lateFeePercent) / 100) * $lateDays / 365, 2);
                                }
                            }
                        }
                    }
                }
                if ($overdueRow['Invpastdue']['date'] < $oldestDueDate) {
                    $oldestDueDate = $overdueRow['Invpastdue']['date'];
                    $item['Invpastdue']['id'] = $overdueRow['Invpastdue']['id'];
                    $item['Invpastdue']['name'] = $overdueRow['Invpastdue']['name'];
                    $item['Invpastdue']['amount'] = $overdueRow['Invpastdue']['amount'];
                }
            }
            $overdueAge = (int)floor((strtotime($overdueTillDate) - strtotime($oldestDueDate)) / 86400);
            if ($overdueAge < 0) {
                $overdueAge = 0;
            }
            $item['Invpastdue']['date'] = $oldestDueDate;
            $item['Invpastdue']['total_overdue'] = $totalOverdue;
            $item['Invpastdue']['overdue_count'] = $overdueCount;
            $item['Invpastdue']['overdue_age'] = $overdueAge;
            $item['Invpastdue']['total_late_fee'] = round($totalLateFee,2);
            $item['Invpastdue']['total_overdue_with_late'] = round($totalOverdue + $totalLateFee,2);
            $summary[$dealId] = $item;
        }
        return array_values($summary);
    }
    protected function _getDemandLetterDealData($clientId,$dealId,$plansPaymentId,$overdueTillDate=null)
    {
        if(!$overdueTillDate)
        {
            $overdueTillDate = CakeTime::format('Y-m-d',CakeTime::convert(time(),$this->siteTimezone));
        }
        $dealStatusCondition = $this->_getOverdueInvoiceDealStatusCondition();
        $overdueRows = $this->Invpastdue->find('all',array(
            'fields'=>$this->fieldIdArr,
            'conditions'=>array(
                'DealsPayment.id IS NULL',
                'Deal.client_id'=>$clientId,
                'Deal.id'=>$dealId,
                'Invpastdue.date <='=>$overdueTillDate,
                $dealStatusCondition
            ),
            'order'=>array('Invpastdue.date'=>'asc')
        ));
        if(empty($overdueRows))
        {
            $deal=$this->Invpastdue->find('first',array(
                'fields'=>$this->fieldIdArr,
                'conditions'=>array('DealsPayment.id IS NULL','Deal.client_id'=>$clientId,'Deal.id'=>$dealId,'Invpastdue.id'=>$plansPaymentId,$dealStatusCondition)
            ));
            if(empty($deal))
            {
                return array('deal'=>null,'overdueInstallmentCount'=>0,'totalOverdueAmountValue'=>0);
            }
            $overdueInstallmentCount = 1;
            $totalOverdueAmountValue = (float)$deal['Invpastdue']['amount'];
        }
        else
        {
            $deal = $overdueRows[0];
            $oldestDueDate = $overdueRows[0]['Invpastdue']['date'];
            $overdueInstallmentCount = 0;
            $totalOverdueAmountValue = 0;
            foreach($overdueRows as $overdueRow)
            {
                $overdueInstallmentCount++;
                if($overdueRow['Deal']['plan_id']!=1)
                {
                    $totalOverdueAmountValue += (float)$overdueRow['Invpastdue']['amount'];
                }
                if($overdueRow['Invpastdue']['date'] < $oldestDueDate)
                {
                    $oldestDueDate = $overdueRow['Invpastdue']['date'];
                    $deal = $overdueRow;
                }
            }
            if($totalOverdueAmountValue<=0)
            {
                $totalOverdueAmountValue=(float)$deal['Invpastdue']['amount'];
            }
            $deal['Invpastdue']['date'] = $oldestDueDate;
        }
        $deal['Invpastdue']['amount'] = $totalOverdueAmountValue;

        $totalDueCount = $this->Invpastdue->find('count', array(
            'conditions' => array(
                'Invpastdue.deal_id' => $dealId,
                'Invpastdue.date <=' => $overdueTillDate
            )
        ));
        if ($totalDueCount < $overdueInstallmentCount) {
            $totalDueCount = $overdueInstallmentCount;
        }

        $projectId = !empty($deal['Property']['project_id']) ? (int)$deal['Property']['project_id'] : 0;
        $agencyData = $this->_fetchReportAgencyHeader($projectId);
        $bankDetails = $this->_getDealPaymentBankDetails($projectId);

        return array(
            'deal'=>$deal,
            'overdueInstallmentCount'=>$overdueInstallmentCount,
            'totalOverdueAmountValue'=>$totalOverdueAmountValue,
            'totalDueCount'=>$totalDueCount,
            'bankDetails'=>$bankDetails,
            'agencyData'=>$agencyData
        );
    }
    protected function _getDealPaymentBankDetails($projectId=0)
    {
        $bank = array(
            'account_title' => 'WHITE VILLAS',
            'account_no' => '0773310485346',
            'iban' => 'PK50UNIL0109000310485346',
            'bank_name' => 'UBL (United Bank Limited)',
            'branch_code' => '0773'
        );
        $projectId = (int)$projectId;
        if($projectId > 0)
        {
            try {
                $bankModel = ClassRegistry::init('Admin.Ardpaymentbankaccount');
                $rows = $bankModel->activeRowsForProject($projectId);
                if(!empty($rows[0]['Ardpaymentbankaccount']))
                {
                    $row = $rows[0]['Ardpaymentbankaccount'];
                    if(!empty($row['account_title'])) $bank['account_title'] = $row['account_title'];
                    if(!empty($row['account_no'])) $bank['account_no'] = $row['account_no'];
                    if(!empty($row['iban'])) $bank['iban'] = $row['iban'];
                    if(!empty($row['bank_name'])) $bank['bank_name'] = $row['bank_name'];
                    if(!empty($row['branch'])) $bank['branch_code'] = $row['branch'];
                    return $bank;
                }
            } catch(Exception $e) {}
        }
        if(!empty($this->siteAccount))
        {
            $text = $this->siteAccount;
            if(preg_match('/Account\s*Title\s*:\s*([^\r\n]+)/i', $text, $m)) {
                $bank['account_title'] = trim($m[1], " \t\n\r\0\x0B\"'");
            }
            if(preg_match('/Account\s*NO\s*:\s*([^\r\n]+)/i', $text, $m)) {
                $bank['account_no'] = trim($m[1], " \t\n\r\0\x0B\"'");
            }
            if(preg_match('/IBAN\s*:\s*([^\r\n]+)/i', $text, $m)) {
                $bank['iban'] = trim($m[1], " \t\n\r\0\x0B\"'");
            }
            if(preg_match('/BRANCH\s*CODE\s*:\s*([^\r\n]+)/i', $text, $m)) {
                $bank['branch_code'] = trim($m[1], " \t\n\r\0\x0B\"'");
            }
            if(preg_match('/Bank\s*Name\s*:\s*([^\r\n]+)/i', $text, $m)) {
                $bName = trim($m[1], " \t\n\r\0\x0B\"'");
                if (stripos($bName, 'UBL') !== false && stripos($bName, 'United Bank Limited') !== false) {
                    $bName = 'UBL (United Bank Limited)';
                }
                $bank['bank_name'] = $bName;
            }
        }
        return $bank;
    }
    protected function _getOverdueInvoiceDealStatusCondition()
    {
        if(!isset($this->Invpastdue->Deal) || !$this->Invpastdue->Deal->hasField('deal_status'))
        {
            return array();
        }
        return array(
            'OR'=>array(
                array('Deal.deal_status'=>null),
                array('Deal.deal_status'=>''),
                array('NOT'=>array('Deal.deal_status'=>$this->_dealOverdueBlockedStatuses))
            )
        );
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
        elseif(isset($this->request->data['Invpastdue']['limit']))
        {
            $limit = (int)$this->request->data['Invpastdue']['limit'];
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
        if(!empty($agencyRow['Agency']))
        {
            $reportAgency = $agencyRow['Agency'];
        }
        else
        {
            $reportAgency['id'] = $reportAgencyId;
        }

        $this->loadModel('User');
        $userFields = array('User.id');
        foreach(array('name','address','phone','mobile','email','photo','dir','ugroup_id') as $userHeaderField)
        {
            if($this->User->hasField($userHeaderField))
            {
                $userFields[] = 'User.'.$userHeaderField;
            }
        }
        $agencyUser = $this->User->find('first',array(
            'fields'=>$userFields,
            'conditions'=>array('User.project_id'=>$reportAgencyId),
            'order'=>array(
                'CASE WHEN User.ugroup_id=4 THEN 0 ELSE 1 END',
                'User.id'=>'asc'
            ),
            'recursive'=>-1
        ));
        if(!empty($agencyUser['User']))
        {
            $u = $agencyUser['User'];
            if(empty($reportAgency['name']) && !empty($u['name'])) $reportAgency['name'] = $u['name'];
            if(empty($reportAgency['address']) && !empty($u['address'])) $reportAgency['address'] = $u['address'];
            if(empty($reportAgency['phone']) && !empty($u['phone'])) $reportAgency['phone'] = $u['phone'];
            if(empty($reportAgency['mobile']) && !empty($u['mobile'])) $reportAgency['mobile'] = $u['mobile'];
            if(empty($reportAgency['email']) && !empty($u['email'])) $reportAgency['email'] = $u['email'];
            if(empty($reportAgency['photo']) && !empty($u['photo'])) $reportAgency['photo'] = $u['photo'];
            if(empty($reportAgency['dir']) && !empty($u['dir'])) $reportAgency['dir'] = $u['dir'];
        }
        return $reportAgency;
    }
}
