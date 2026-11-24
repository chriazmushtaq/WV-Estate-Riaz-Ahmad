<?php
App::uses('Router', 'Routing');
class MyRentalsController extends AppController
{
    public $uses = array('Admin.DealRental', 'Admin.DealRentalSchedule', 'Admin.DealRentalReceipt', 'Admin.DealRentalReceiptAllocation', 'Admin.DealRentalMaintenance', 'Admin.DealRentalInspection');
    public $helpers = array('Html', 'Form', 'Session', 'Number', 'Time', 'NumToWord');

    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->authenticate();
        $this->userId = !empty($this->userValue['Client']['id']) ? (int)$this->userValue['Client']['id'] : 0;
        if ($this->userId <= 0) {
            return $this->redirect(array('controller' => 'Clients', 'action' => 'login'));
        }
    }

    public function index($id = null)
    {
        $rentals = $this->_clientRentals();
        $rentalIds = array();
        foreach ($rentals as $row) {
            $rentalIds[(int)$row['DealRental']['id']] = true;
        }
        $selectedId = (int)$id;
        if ($selectedId <= 0 && !empty($rentals[0]['DealRental']['id'])) {
            $selectedId = (int)$rentals[0]['DealRental']['id'];
        }
        if ($selectedId > 0 && empty($rentalIds[$selectedId])) {
            throw new NotFoundException(__('Invalid rental agreement'));
        }
        $selectedRental = $selectedId > 0 ? $this->_clientRental($selectedId) : array();
        if ($this->request->is('post') && !empty($selectedRental['DealRental']['id'])) {
            if ($this->_saveClientMaintenance($selectedRental)) {
                return $this->redirect(array('action' => 'index', $selectedId, '#' => 'maintenance'));
            }
        }
        $details = $selectedId > 0 ? $this->_rentalDetails($selectedId) : $this->_emptyDetails();
        $this->set(array(
            'rentals' => $rentals,
            'selectedRental' => $selectedRental,
            'schedules' => $details['schedules'],
            'scheduleReceiptMap' => $details['scheduleReceiptMap'],
            'receipts' => $details['receipts'],
            'maintenanceRequests' => $details['maintenance'],
            'inspections' => $details['inspections'],
            'rentalSummary' => $details['summary']
        ));
    }

    public function statement($id = null)
    {
        $this->layout = null;
        $rental = $this->_clientRental((int)$id);
        if (empty($rental['DealRental']['id'])) {
            throw new NotFoundException(__('Invalid rental agreement'));
        }
        $month = !empty($this->request->query['month']) && preg_match('/^\d{4}-\d{2}$/', $this->request->query['month']) ? $this->request->query['month'] : '';
        $details = $this->_rentalDetails((int)$id, $month);
        $paidSchedules = array();
        foreach((array)$details['schedules'] as $scheduleRow) {
            if(!empty($scheduleRow['DealRentalSchedule']) && (float)$scheduleRow['DealRentalSchedule']['balance_amount'] <= 0.009 && (float)$scheduleRow['DealRentalSchedule']['paid_amount'] > 0.009) {
                if(!empty($scheduleRow['DealRentalSchedule']['due_date'])) {
                    $scheduleRow['DealRentalSchedule']['due_date'] = date('d-M-Y', strtotime($scheduleRow['DealRentalSchedule']['due_date']));
                }
                if(!empty($scheduleRow['DealRentalSchedule']['rent_month'])) {
                    $scheduleRow['DealRentalSchedule']['rent_month'] = date('M Y', strtotime($scheduleRow['DealRentalSchedule']['rent_month'].'-01'));
                }
                $paidSchedules[] = $scheduleRow;
            }
        }
        foreach((array)$details['receipts'] as $receiptKey => $receiptRow) {
            if(!empty($receiptRow['DealRentalReceipt']['receipt_date'])) {
                $details['receipts'][$receiptKey]['DealRentalReceipt']['receipt_date'] = date('d-M-Y', strtotime($receiptRow['DealRentalReceipt']['receipt_date']));
            }
            if(!empty($receiptRow['DealRentalReceipt']['rent_month'])) {
                $details['receipts'][$receiptKey]['DealRentalReceipt']['rent_month'] = date('M Y', strtotime($receiptRow['DealRentalReceipt']['rent_month'].'-01'));
            }
        }
        foreach((array)$details['maintenance'] as $maintenanceKey => $maintenanceRow) {
            if(!empty($maintenanceRow['DealRentalMaintenance']['request_date'])) {
                $details['maintenance'][$maintenanceKey]['DealRentalMaintenance']['request_date'] = date('d-M-Y', strtotime($maintenanceRow['DealRentalMaintenance']['request_date']));
            }
        }
        $this->set(array('rental' => $rental, 'month' => $month, 'schedules' => $paidSchedules, 'receipts' => $details['receipts'], 'maintenanceRequests' => $details['maintenance'], 'rentalSummary' => $details['summary']));
    }

    public function agreement($id = null)
    {
        $this->layout = null;
        $rental = $this->_clientRental((int)$id);
        if (empty($rental['DealRental']['id'])) {
            throw new NotFoundException(__('Invalid rental agreement'));
        }
        $html = $this->_clientRentalAgreementHtml($rental);
        $this->set(compact('rental', 'html'));
    }

    public function receipt($id = null)
    {
        $this->layout = null;
        $receipt = $this->DealRentalReceipt->find('first', array(
            'conditions' => array('DealRentalReceipt.id' => (int)$id, 'DealRentalReceipt.client_id' => $this->userId),
            'recursive' => -1
        ));
        if (empty($receipt['DealRentalReceipt']['id'])) {
            throw new NotFoundException(__('Invalid rental receipt'));
        }
        $rental = $this->_clientRental((int)$receipt['DealRentalReceipt']['deal_rental_id']);
        if (empty($rental['DealRental']['id'])) {
            throw new NotFoundException(__('Invalid rental receipt'));
        }
        $rentalId = (int)$rental['DealRental']['id'];
        $details = $this->_rentalDetails($rentalId);
        $allocations = $this->DealRentalReceiptAllocation->find('all', array(
            'conditions' => array(
                'DealRentalReceiptAllocation.receipt_id' => (int)$receipt['DealRentalReceipt']['id'],
                'DealRentalReceiptAllocation.deal_rental_id' => $rentalId
            ),
            'order' => array('DealRentalSchedule.due_date' => 'asc', 'DealRentalSchedule.id' => 'asc'),
            'recursive' => 0
        ));
        $printData = $this->_rentalReceiptPrintData($receipt, $rental, $details, $allocations);
        $receiptTemplateBody = $printData['body'];
        $receiptReplacements = $printData['replacements'];
        $this->set(compact('receipt', 'rental', 'allocations', 'receiptTemplateBody', 'receiptReplacements'));
    }

    protected function _clientRentals()
    {
        return (array)$this->DealRental->find('all', array(
            'joins' => array(
                array('table' => 'deals', 'alias' => 'PortalDeal', 'type' => 'LEFT', 'conditions' => array('PortalDeal.id=DealRental.deal_id')),
                array('table' => 'properties', 'alias' => 'PortalProperty', 'type' => 'LEFT', 'conditions' => array('PortalProperty.id=PortalDeal.property_id')),
                array('table' => 'projects', 'alias' => 'PortalAgency', 'type' => 'LEFT', 'conditions' => array('PortalAgency.id=DealRental.project_id'))
            ),
            'fields' => array('DealRental.*', 'PortalDeal.invoice_no', 'PortalProperty.name', 'PortalProperty.address', 'PortalAgency.*'),
            'conditions' => array('DealRental.client_id' => $this->userId, 'DealRental.client_portal_access' => 'Active'),
            'order' => array('DealRental.status' => 'asc', 'DealRental.id' => 'desc'),
            'recursive' => -1
        ));
    }

    protected function _clientRental($id)
    {
        $rows = $this->DealRental->find('all', array(
            'joins' => array(
                array('table' => 'deals', 'alias' => 'PortalDeal', 'type' => 'LEFT', 'conditions' => array('PortalDeal.id=DealRental.deal_id')),
                array('table' => 'properties', 'alias' => 'PortalProperty', 'type' => 'LEFT', 'conditions' => array('PortalProperty.id=PortalDeal.property_id')),
                array('table' => 'projects', 'alias' => 'PortalAgency', 'type' => 'LEFT', 'conditions' => array('PortalAgency.id=DealRental.project_id'))
            ),
            'fields' => array('DealRental.*', 'PortalDeal.invoice_no', 'PortalProperty.name', 'PortalProperty.address', 'PortalAgency.*'),
            'conditions' => array('DealRental.id' => (int)$id, 'DealRental.client_id' => $this->userId, 'DealRental.client_portal_access' => 'Active'),
            'limit' => 1,
            'recursive' => -1
        ));
        return !empty($rows[0]) ? $rows[0] : array();
    }

    protected function _rentalDetails($rentalId, $month = '')
    {
        $scheduleConditions = array('DealRentalSchedule.deal_rental_id' => (int)$rentalId, 'DealRentalSchedule.client_id' => $this->userId);
        $receiptConditions = array('DealRentalReceipt.deal_rental_id' => (int)$rentalId, 'DealRentalReceipt.client_id' => $this->userId);
        $maintenanceConditions = array('DealRentalMaintenance.deal_rental_id' => (int)$rentalId, 'DealRentalMaintenance.client_id' => $this->userId);
        if ($month !== '') {
            $scheduleConditions['DealRentalSchedule.rent_month'] = $month;
            $receiptConditions['DealRentalReceipt.rent_month'] = $month;
            $maintenanceConditions['DealRentalMaintenance.request_date >='] = $month.'-01';
            $maintenanceConditions['DealRentalMaintenance.request_date <='] = date('Y-m-t', strtotime($month.'-01'));
        }
        $schedules = $this->DealRentalSchedule->find('all', array('conditions' => $scheduleConditions, 'order' => array('DealRentalSchedule.due_date' => 'asc'), 'recursive' => -1));
        $receipts = $this->DealRentalReceipt->find('all', array('conditions' => $receiptConditions, 'order' => array('DealRentalReceipt.receipt_date' => 'desc', 'DealRentalReceipt.id' => 'desc'), 'recursive' => -1));
        $receiptLabels = array();
        foreach ($receipts as $row) {
            $receipt = $row['DealRentalReceipt'];
            $receiptLabels[(int)$receipt['id']] = !empty($receipt['receipt_no']) ? $receipt['receipt_no'] : '#'.(int)$receipt['id'];
        }
        $scheduleReceiptMap = array();
        if (!empty($receiptLabels)) {
            $allocations = $this->DealRentalReceiptAllocation->find('all', array(
                'fields' => array('DealRentalReceiptAllocation.schedule_id', 'DealRentalReceiptAllocation.receipt_id'),
                'conditions' => array(
                    'DealRentalReceiptAllocation.deal_rental_id' => (int)$rentalId,
                    'DealRentalReceiptAllocation.receipt_id' => array_keys($receiptLabels)
                ),
                'order' => array('DealRentalReceiptAllocation.id' => 'asc'),
                'recursive' => -1
            ));
            foreach ($allocations as $row) {
                $allocation = $row['DealRentalReceiptAllocation'];
                $scheduleId = (int)$allocation['schedule_id'];
                $receiptId = (int)$allocation['receipt_id'];
                if ($scheduleId > 0 && isset($receiptLabels[$receiptId])) {
                    $scheduleReceiptMap[$scheduleId][$receiptId] = $receiptLabels[$receiptId];
                }
            }
        }
        $maintenance = $this->DealRentalMaintenance->find('all', array('conditions' => $maintenanceConditions, 'order' => array('DealRentalMaintenance.request_date' => 'desc', 'DealRentalMaintenance.id' => 'desc'), 'recursive' => -1));
        $inspections = $this->DealRentalInspection->find('all', array('conditions' => array('DealRentalInspection.deal_rental_id' => (int)$rentalId, 'DealRentalInspection.client_id' => $this->userId), 'order' => array('DealRentalInspection.inspection_date' => 'desc'), 'recursive' => -1));
        $summary = array('scheduled' => 0, 'paid' => 0, 'outstanding' => 0, 'upcoming' => 0, 'overdue' => 0, 'owner_charges' => 0, 'net_received' => 0);
        foreach ($schedules as $row) {
            $s = $row['DealRentalSchedule'];
            $summary['scheduled'] += (float)$s['rent_amount'];
            $summary['paid'] += (float)$s['paid_amount'];
            $summary['outstanding'] += (float)$s['balance_amount'];
            if ((float)$s['balance_amount'] > 0 && !empty($s['due_date']) && $s['due_date'] < date('Y-m-d')) {
                $summary['overdue'] += (float)$s['balance_amount'];
            } elseif ((float)$s['balance_amount'] > 0) {
                $summary['upcoming'] += (float)$s['balance_amount'];
            }
        }
        foreach ($maintenance as $row) {
            $summary['owner_charges'] += (float)$row['DealRentalMaintenance']['owner_charge_amount'];
        }
        $summary['net_received'] = $summary['paid'] - $summary['owner_charges'];
        foreach ($summary as $key => $value) {
            $summary[$key] = round((float)$value, 2);
        }
        return compact('schedules', 'scheduleReceiptMap', 'receipts', 'maintenance', 'inspections', 'summary');
    }

    protected function _emptyDetails()
    {
        return array('schedules' => array(), 'scheduleReceiptMap' => array(), 'receipts' => array(), 'maintenance' => array(), 'inspections' => array(), 'summary' => array('scheduled' => 0, 'paid' => 0, 'outstanding' => 0, 'upcoming' => 0, 'overdue' => 0, 'owner_charges' => 0, 'net_received' => 0));
    }

    protected function _rentalReceiptPrintData($receipt, $rental, $details, $allocations)
    {
        $this->loadModel('Admin.Template');
        $this->loadModel('Admin.Deal');
        $template = (array)$this->Template->find('first', array(
            'conditions' => array('Template.type' => 'RLT_RENTAL_RECEIPT', 'Template.status' => 'Published'),
            'order' => array('Template.id' => 'asc'),
            'recursive' => -1
        ));
        $body = !empty($template['Template']['description']) ? (string)$template['Template']['description'] : $this->_rentalReceiptTemplateBody();
        $x = (array)$receipt['DealRentalReceipt'];
        $r = (array)$rental['DealRental'];
        $deal = (array)$this->Deal->find('first', array('conditions' => array('Deal.id' => (int)$r['deal_id']), 'recursive' => 0));
        $agency = !empty($rental['PortalAgency']) ? (array)$rental['PortalAgency'] : array();
        $agencyName = !empty($agency['name']) ? $agency['name'] : $this->siteName;
        $agencyAddress = !empty($agency['address']) ? $agency['address'] : (!empty($this->viewVars['siteAddress']) ? $this->viewVars['siteAddress'] : '');
        $contactParts = array();
        foreach (array('mobile', 'phone', 'email') as $field) {
            if (!empty($agency[$field]) && !in_array(trim((string)$agency[$field]), $contactParts, true)) {
                $contactParts[] = trim((string)$agency[$field]);
            }
        }
        if (empty($contactParts) && !empty($this->siteEmail)) {
            $contactParts[] = $this->siteEmail;
        }
        $allocatedMonths = array();
        foreach ((array)$allocations as $row) {
            if (empty($row['DealRentalSchedule']['rent_month'])) {
                continue;
            }
            $amount = !empty($row['DealRentalReceiptAllocation']['allocated_amount']) ? (float)$row['DealRentalReceiptAllocation']['allocated_amount'] : 0;
            $allocatedMonths[] = date('M Y', strtotime($row['DealRentalSchedule']['rent_month'].'-01')).' ('.$this->_rentalCurrency($amount).')';
        }
        if (empty($allocatedMonths)) {
            $allocatedMonths[] = !empty($x['rent_month']) ? date('M Y', strtotime($x['rent_month'].'-01')) : '-';
        }
        $summary = (array)$details['summary'];
        $received = 0;
        foreach ((array)$details['receipts'] as $row) {
            $received += (float)$row['DealRentalReceipt']['rent_amount'];
        }
        $credit = max(0, $received-(float)$summary['paid']);
        $propertyName = !empty($deal['Property']['name']) ? $deal['Property']['name'] : (!empty($rental['PortalProperty']['name']) ? $rental['PortalProperty']['name'] : '');
        $invoiceNo = !empty($deal['Deal']['invoice_no']) ? $deal['Deal']['invoice_no'] : '#'.(int)$r['deal_id'];
        $clientName = !empty($deal['Client']['name']) ? $deal['Client']['name'] : $r['tenant_name'];
        $qrText = implode("\n", array('Rental Receipt: '.$x['receipt_no'], 'Agreement: '.$r['agreement_no'], 'Tenant: '.$r['tenant_name'], 'Rent Month: '.$x['rent_month'], 'Amount: '.$this->_rentalCurrency((float)$x['rent_amount'])));
        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=360x360&ecc=M&qzone=2&format=png&data='.rawurlencode($qrText);
        $logoUrl = $this->_rentalAgencyLogoUrl($agency);
        $templateRow = !empty($template['Template']) ? (array)$template['Template'] : array();
        $replacements = array(
            '{{receipt_no}}' => h($x['receipt_no']), '{{receipt_date}}' => h(!empty($x['receipt_date']) ? date('d-M-Y', strtotime($x['receipt_date'])) : ''),
            '{{print_date}}' => h(date('d-M-Y')), '{{rent_month}}' => h($x['rent_month']), '{{rent_month_label}}' => h(!empty($x['rent_month']) ? date('M Y', strtotime($x['rent_month'].'-01')) : ''),
            '{{rent_amount}}' => h($this->_rentalCurrency((float)$x['rent_amount'])), '{{rent_amount_words}}' => '', '{{expense_amount}}' => h($this->_rentalCurrency((float)$x['expense_amount'])),
            '{{net_amount}}' => h($this->_rentalCurrency((float)$x['net_amount'])), '{{payment_mode}}' => h($x['payment_mode']), '{{payment_reference}}' => h($x['remarks']),
            '{{allocated_months}}' => h(implode(', ', $allocatedMonths)), '{{agreement_no}}' => h($r['agreement_no']), '{{tenant_name}}' => h($r['tenant_name']),
            '{{tenant_cnic}}' => h($r['tenant_cnic']), '{{tenant_mobile}}' => h($r['tenant_mobile']), '{{tenant_email}}' => h($r['tenant_email']), '{{tenant_address}}' => h($r['tenant_address']),
            '{{client_name}}' => h($clientName), '{{deal_invoice_no}}' => h($invoiceNo), '{{property_name}}' => h($propertyName), '{{agency_name}}' => h($agencyName), '{{project_name}}' => h($agencyName),
            '{{agency_address}}' => h($agencyAddress), '{{agency_contact}}' => h(implode(' | ', $contactParts)), '{{agency_phone}}' => h(!empty($agency['phone']) ? $agency['phone'] : (!empty($agency['mobile']) ? $agency['mobile'] : '')),
            '{{agency_email}}' => h(!empty($agency['email']) ? $agency['email'] : ''), '{{site_organization}}' => h(!empty($this->viewVars['siteOrganization']) ? $this->viewVars['siteOrganization'] : $agencyName),
            '{{total_rent_paid}}' => h($this->_rentalCurrency((float)$summary['paid'])), '{{outstanding_rent}}' => h($this->_rentalCurrency((float)$summary['outstanding'])),
            '{{overdue_rent}}' => h($this->_rentalCurrency((float)$summary['overdue'])), '{{advance_credit}}' => h($this->_rentalCurrency($credit)), '{{receipt_qr_url}}' => h($qrUrl),
            '{{receipt_qr}}' => '<img src="'.h($qrUrl).'" alt="Rental Receipt QR">', '{{agency_logo_url}}' => h($logoUrl),
            '{{agency_logo}}' => $logoUrl !== '' ? '<img src="'.h($logoUrl).'" alt="'.h($agencyName).'">' : '',
            '{{ceo_signature}}' => $this->_rentalTemplateSignatureHtml($templateRow, 'ceo'), '{{chairman_signature}}' => $this->_rentalTemplateSignatureHtml($templateRow, 'chairman')
        );
        return array('body' => $body, 'replacements' => $replacements);
    }

    protected function _rentalReceiptTemplateBody()
    {
        $file = APP.'Lib'.DS.'LetterTemplates'.DS.'rental_receipt.html';
        return is_readable($file) ? (string)file_get_contents($file) : '<h2>RENTAL PAYMENT RECEIPT</h2><p>{{receipt_no}} | {{rent_amount}}</p>';
    }

    protected function _rentalCurrency($amount)
    {
        return trim((string)$this->currency).' '.number_format((float)$amount, 2);
    }

    protected function _rentalAgencyLogoUrl($agency)
    {
        $candidates = array();
        foreach (array('logo', 'image', 'photo') as $field) {
            if (!empty($agency[$field])) {
                $candidates[] = 'agency_thumb/'.trim((string)$agency[$field]);
                $candidates[] = trim((string)$agency[$field]);
            }
        }
        if (!empty($this->viewVars['frontLogo'])) {
            $candidates[] = trim((string)$this->viewVars['frontLogo']);
        }
        foreach ($candidates as $candidate) {
            if (preg_match('/^https?:\/\//i', $candidate)) {
                return $candidate;
            }
            $relative = trim(str_replace('\\', '/', $candidate), '/');
            if ($relative !== '' && file_exists(WWW_ROOT.'img'.DS.str_replace('/', DS, $relative))) {
                return Router::url('/img/'.$relative, true);
            }
        }
        return '';
    }

    protected function _rentalTemplateSignatureHtml($template, $type)
    {
        $fileField = $type.'_signature';
        $dirField = $type.'_signature_dir';
        if (empty($template[$fileField])) {
            return '';
        }
        $directory = !empty($template[$dirField]) ? trim((string)$template[$dirField], '/') : 'letter_signatures';
        $relative = trim($directory.'/'.$template[$fileField], '/');
        if (!file_exists(WWW_ROOT.'img'.DS.str_replace('/', DS, $relative))) {
            return '';
        }
        return '<img src="'.h(Router::url('/img/'.$relative, true)).'" alt="Signature" style="max-height:70px;max-width:180px;object-fit:contain;">';
    }

    protected function _clientRentalAgreementHtml($rental)
    {
        $this->loadModel('Admin.Template');
        $this->loadModel('Admin.Deal');
        $template = (array)$this->Template->find('first', array(
            'conditions' => array('Template.type' => 'RLT_RENTAL_AGREEMENT', 'Template.status' => 'Published'),
            'order' => array('Template.id' => 'desc'),
            'recursive' => -1
        ));
        $body = !empty($template['Template']['description']) ? (string)$template['Template']['description'] : $this->_clientRentalAgreementTemplateBody();
        $templateRow = !empty($template['Template']) ? (array)$template['Template'] : array();
        $r = (array)$rental['DealRental'];
        $deal = (array)$this->Deal->find('first', array('conditions' => array('Deal.id' => (int)$r['deal_id']), 'recursive' => 0));
        $agency = !empty($rental['PortalAgency']) ? (array)$rental['PortalAgency'] : array();
        $client = !empty($deal['Client']) ? (array)$deal['Client'] : array();
        $property = !empty($deal['Property']) ? (array)$deal['Property'] : array();
        $agencyName = !empty($agency['name']) ? $agency['name'] : (!empty($this->viewVars['siteOrganization']) ? $this->viewVars['siteOrganization'] : 'White Villas');
        $agencyAddress = !empty($agency['address']) ? $agency['address'] : (!empty($this->viewVars['siteAddress']) ? $this->viewVars['siteAddress'] : '');
        $agencyPhone = !empty($agency['phone']) ? $agency['phone'] : (!empty($agency['mobile']) ? $agency['mobile'] : '');
        $agencyEmail = !empty($agency['email']) ? $agency['email'] : (!empty($this->viewVars['siteEmail']) ? $this->viewVars['siteEmail'] : '');
        $agencyLogoUrl = $this->_rentalAgencyLogoUrl($agency);
        $replacements = array(
            '{{letter_date}}' => date('d-M-Y'), '{{today}}' => date('d-M-Y'), '{{print_date}}' => date('d-M-Y'),
            '{{agreement_no}}' => h($r['agreement_no']), '{{tenant_name}}' => h($r['tenant_name']), '{{tenant_cnic}}' => h($r['tenant_cnic']),
            '{{tenant_mobile}}' => h($r['tenant_mobile']), '{{tenant_email}}' => h($r['tenant_email']), '{{tenant_address}}' => h($r['tenant_address']),
            '{{monthly_rent}}' => h($this->_rentalCurrency((float)$r['monthly_rent'])), '{{security_deposit}}' => h($this->_rentalCurrency((float)$r['security_deposit'])),
            '{{start_date}}' => h(!empty($r['start_date']) ? date('d-M-Y', strtotime($r['start_date'])) : ''),
            '{{end_date}}' => h(!empty($r['end_date']) ? date('d-M-Y', strtotime($r['end_date'])) : ''), '{{due_day}}' => (int)$r['due_day'],
            '{{deal_invoice_no}}' => h(!empty($deal['Deal']['invoice_no']) ? $deal['Deal']['invoice_no'] : '#'.(int)$r['deal_id']),
            '{{deal_date}}' => h(!empty($deal['Deal']['date']) ? date('d-M-Y', strtotime($deal['Deal']['date'])) : ''),
            '{{deal_status}}' => h(!empty($deal['Deal']['deal_status']) ? $deal['Deal']['deal_status'] : ''),
            '{{property_name}}' => h(!empty($property['name']) ? $property['name'] : ''), '{{property_file}}' => h(!empty($property['name']) ? $property['name'] : ''),
            '{{property_area}}' => h(!empty($property['area']) ? $property['area'] : ''), '{{property_unit}}' => h(!empty($deal['Unit']['name']) ? $deal['Unit']['name'] : ''),
            '{{property_price}}' => h($this->_rentalCurrency(!empty($property['price']) ? (float)$property['price'] : 0)),
            '{{property_type}}' => h(!empty($deal['Type']['name']) ? $deal['Type']['name'] : ''), '{{property_contract}}' => h(!empty($deal['Contract']['name']) ? $deal['Contract']['name'] : ''),
            '{{client_name}}' => h(!empty($client['name']) ? $client['name'] : $r['tenant_name']), '{{client_cnic}}' => h(!empty($client['pincode']) ? $client['pincode'] : ''),
            '{{client_phone}}' => h(!empty($client['phone']) ? $client['phone'] : $r['tenant_mobile']), '{{client_mobile}}' => h(!empty($client['mobile']) ? $client['mobile'] : $r['tenant_mobile']),
            '{{client_email}}' => h(!empty($client['email']) ? $client['email'] : $r['tenant_email']), '{{client_address}}' => h(!empty($client['address']) ? $client['address'] : $r['tenant_address']),
            '{{agency_name}}' => h($agencyName), '{{project_name}}' => h($agencyName), '{{agency_address}}' => h($agencyAddress), '{{agency_phone}}' => h($agencyPhone),
            '{{agency_email}}' => h($agencyEmail), '{{agency_contact}}' => h(implode(' | ', array_filter(array($agencyPhone, $agencyEmail)))),
            '{{agency_logo_url}}' => h($agencyLogoUrl), '{{agency_logo}}' => $agencyLogoUrl !== '' ? '<img src="'.h($agencyLogoUrl).'" alt="'.h($agencyName).'">' : '',
            '{{site_name}}' => h(!empty($this->viewVars['siteName']) ? $this->viewVars['siteName'] : $agencyName),
            '{{site_organization}}' => h(!empty($this->viewVars['siteOrganization']) ? $this->viewVars['siteOrganization'] : $agencyName),
            '{{site_address}}' => h(!empty($this->viewVars['siteAddress']) ? $this->viewVars['siteAddress'] : $agencyAddress),
            '{{site_contact}}' => h(!empty($this->viewVars['contact']) ? $this->viewVars['contact'] : $agencyPhone),
            '{{site_email}}' => h(!empty($this->viewVars['siteEmail']) ? $this->viewVars['siteEmail'] : $agencyEmail),
            '{{ceo_signature}}' => $this->_rentalTemplateSignatureHtml($templateRow, 'ceo'), '{{chairman_signature}}' => $this->_rentalTemplateSignatureHtml($templateRow, 'chairman')
        );
        return strtr($body, $replacements);
    }

    protected function _clientRentalAgreementTemplateBody()
    {
        return '<div style="font-family:Arial,Helvetica,sans-serif;color:#111;line-height:1.6;font-size:13px;"><h2 style="text-align:center;margin:0 0 12px;">RENTAL AGREEMENT</h2><p><strong>Agreement No:</strong> {{agreement_no}}<br><strong>Date:</strong> {{letter_date}}</p><p>This rental agreement is made between <strong>{{site_organization}}</strong> and tenant <strong>{{tenant_name}}</strong> CNIC <strong>{{tenant_cnic}}</strong> for property/file <strong>{{property_name}}</strong> under deal <strong>{{deal_invoice_no}}</strong>.</p><table style="width:100%;border-collapse:collapse;margin:12px 0;"><tr><td style="border:1px solid #ccc;padding:7px;"><strong>Monthly Rent</strong><br>{{monthly_rent}}</td><td style="border:1px solid #ccc;padding:7px;"><strong>Security Deposit</strong><br>{{security_deposit}}</td><td style="border:1px solid #ccc;padding:7px;"><strong>Period</strong><br>{{start_date}} to {{end_date}}</td></tr></table><p>The tenant shall pay rent every month on or before day {{due_day}}.</p></div>';
    }

    protected function _saveClientMaintenance($rental)
    {
        $posted = isset($this->request->data['DealRentalMaintenance']) ? (array)$this->request->data['DealRentalMaintenance'] : array();
        $title = substr(trim((string)(isset($posted['title']) ? $posted['title'] : '')), 0, 180);
        if ($title === '') {
            $this->Session->setFlash(__('Please enter a maintenance request title.'), 'flash', array('alert' => 'danger'));
            return false;
        }
        $r = $rental['DealRental'];
        $priorities = array('Low','Normal','High','Urgent');
        $this->DealRentalMaintenance->create();
        $saved = $this->DealRentalMaintenance->save(array('DealRentalMaintenance' => array(
            'deal_rental_id' => (int)$r['id'], 'deal_id' => (int)$r['deal_id'], 'client_id' => $this->userId, 'project_id' => (int)$r['project_id'],
            'request_no' => 'MR-'.(int)$r['project_id'].'-'.date('YmdHis').'-'.mt_rand(100,999),
            'request_date' => date('Y-m-d'),
            'category' => !empty($posted['category']) ? substr(trim((string)$posted['category']), 0, 60) : 'General',
            'priority' => in_array(isset($posted['priority']) ? $posted['priority'] : '', $priorities, true) ? $posted['priority'] : 'Normal',
            'status' => 'Submitted', 'title' => $title,
            'description' => trim((string)(isset($posted['description']) ? $posted['description'] : '')),
            'submitted_from' => 'Client', 'created_by' => $this->userId
        )), false);
        $this->Session->setFlash($saved ? __('Maintenance request submitted.') : __('Maintenance request could not be submitted.'), 'flash', array('alert' => $saved ? 'success' : 'danger'));
        return (bool)$saved;
    }
}
?>
