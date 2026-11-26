<?php
App::uses('AppController', 'Controller');
App::uses('Router', 'Routing');

class BallotingResultsController extends AppController {
    public $helpers = array('Html', 'Form', 'Session', 'Time', 'Number');
    public $uses = array(
        'Admin.BallotingAllotment',
        'Admin.BallotingUnit',
        'Admin.BallotingSetting',
        'MyDeal',
        'Property',
        'Client'
    );

    public function beforeFilter() {
        parent::beforeFilter();
        $this->layout = 'default';
        $action = !empty($this->request->params['action']) ? strtolower($this->request->params['action']) : '';
        // Public actions
        if (in_array($action, array('verify', 'search'), true)) {
            return;
        }
        // Protected actions
        if ($action === 'certificate' || $action === 'allotmentletter') {
            // Allow if valid token is provided or client is authenticated
            if (!empty($this->request->query['token'])) {
                return;
            }
        }
        $this->authenticate();
        $this->userId = !empty($this->userValue['Client']['id']) ? (int)$this->userValue['Client']['id'] : 0;
    }

    public function index() {
        $clientId = $this->userId;
        $clientDeals = array();
        $allotments = array();
        $searchResult = null;
        $searchPerformed = false;
        $searchQuery = '';

        // 1. If logged in, get client's allotted plots and deals
        if ($clientId > 0) {
            $allotments = $this->BallotingAllotment->find('all', array(
                'joins' => array(
                    array('table' => 'projects', 'alias' => 'Project', 'type' => 'LEFT', 'conditions' => array('BallotingAllotment.project_id=Project.id')),
                    array('table' => 'deals', 'alias' => 'Deal', 'type' => 'LEFT', 'conditions' => array('BallotingAllotment.deal_id=Deal.id')),
                    array('table' => 'properties', 'alias' => 'Property', 'type' => 'LEFT', 'conditions' => array('Deal.property_id=Property.id')),
                    array('table' => 'balloting_units', 'alias' => 'BallotingUnit', 'type' => 'LEFT', 'conditions' => array('BallotingAllotment.balloting_unit_id=BallotingUnit.id'))
                ),
                'fields' => array('BallotingAllotment.*', 'Project.*', 'Deal.*', 'Property.*', 'BallotingUnit.*'),
                'conditions' => array(
                    'BallotingAllotment.client_id' => $clientId,
                    'BallotingAllotment.status' => 'Allotted'
                ),
                'order' => array('BallotingAllotment.id' => 'DESC'),
                'recursive' => -1
            ));

            // Deals that do not have an allotment yet
            $allottedDealIds = array();
            foreach ($allotments as $a) {
                if (!empty($a['BallotingAllotment']['deal_id'])) {
                    $allottedDealIds[] = (int)$a['BallotingAllotment']['deal_id'];
                }
            }

            $dealConds = array(
                'MyDeal.client_id' => $clientId,
                'MyDeal.deal_status' => array('Active', 'Blocked', null, '')
            );
            if (!empty($allottedDealIds)) {
                $dealConds['NOT'] = array('MyDeal.id' => $allottedDealIds);
            }

            $clientDeals = $this->MyDeal->find('all', array(
                'joins' => array(
                    array('table' => 'properties', 'alias' => 'Property', 'type' => 'LEFT', 'conditions' => array('MyDeal.property_id=Property.id')),
                    array('table' => 'projects', 'alias' => 'Project', 'type' => 'LEFT', 'conditions' => array('Property.project_id=Project.id'))
                ),
                'fields' => array('MyDeal.*', 'Property.*', 'Project.*'),
                'conditions' => $dealConds,
                'order' => array('MyDeal.id' => 'DESC'),
                'recursive' => -1
            ));
        }

        // 2. Handle Live Status Search Query
        if (!empty($this->request->query['q'])) {
            $searchQuery = trim((string)$this->request->query['q']);
            $searchPerformed = true;
            $searchResult = $this->_performLiveSearch($searchQuery);
        }

        // 3. Project settings & announcement
        $settingsList = $this->BallotingSetting->find('all', array(
            'conditions' => array('BallotingSetting.status' => 'Active'),
            'recursive' => -1
        ));
        $announcements = array();
        foreach ($settingsList as $s) {
            if (!empty($s['BallotingSetting']['portal_announcement'])) {
                $announcements[] = $s['BallotingSetting']['portal_announcement'];
            }
        }

        $this->set(compact('allotments', 'clientDeals', 'searchResult', 'searchPerformed', 'searchQuery', 'announcements', 'clientId'));
    }

    public function search() {
        $this->autoRender = false;
        $this->response->type('json');
        $q = isset($this->request->query['q']) ? trim((string)$this->request->query['q']) : '';
        if ($q === '') {
            echo json_encode(array('success' => false, 'message' => 'Please enter a search term.'));
            return;
        }

        $result = $this->_performLiveSearch($q);
        echo json_encode(array('success' => true, 'result' => $result));
        return;
    }

    public function certificate($id = null) {
        $id = (int)$id;
        if ($id <= 0) {
            $this->Session->setFlash(__('Invalid allotment certificate reference.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }

        $allotment = $this->BallotingAllotment->find('first', array(
            'joins' => array(
                array('table' => 'projects', 'alias' => 'Project', 'type' => 'LEFT', 'conditions' => array('BallotingAllotment.project_id=Project.id')),
                array('table' => 'deals', 'alias' => 'Deal', 'type' => 'LEFT', 'conditions' => array('BallotingAllotment.deal_id=Deal.id')),
                array('table' => 'properties', 'alias' => 'Property', 'type' => 'LEFT', 'conditions' => array('Deal.property_id=Property.id')),
                array('table' => 'clients', 'alias' => 'Client', 'type' => 'LEFT', 'conditions' => array('BallotingAllotment.client_id=Client.id')),
                array('table' => 'balloting_units', 'alias' => 'BallotingUnit', 'type' => 'LEFT', 'conditions' => array('BallotingAllotment.balloting_unit_id=BallotingUnit.id'))
            ),
            'fields' => array('BallotingAllotment.*', 'Project.*', 'Deal.*', 'Property.*', 'Client.*', 'BallotingUnit.*'),
            'conditions' => array('BallotingAllotment.id' => $id),
            'recursive' => -1
        ));

        if (empty($allotment['BallotingAllotment']['id'])) {
            $this->Session->setFlash(__('Allotment record not found.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }

        $expectedToken = $this->_generateAllotmentToken($id, $allotment['BallotingAllotment']['allotment_no']);
        $providedToken = isset($this->request->query['token']) ? trim((string)$this->request->query['token']) : '';

        // Authorization check: either valid secure token OR logged in client owns this allotment
        $isOwner = (!empty($this->userId) && (int)$allotment['BallotingAllotment']['client_id'] === (int)$this->userId);
        $hasToken = ($providedToken !== '' && hash_equals($expectedToken, $providedToken));

        if (!$isOwner && !$hasToken) {
            $this->Session->setFlash(__('Access denied. Please login to your account or provide a valid verification link.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }

        // Check if certificate download is enabled for this project
        $projectId = !empty($allotment['BallotingAllotment']['project_id']) ? (int)$allotment['BallotingAllotment']['project_id'] : 0;
        $bSetting = $this->BallotingSetting->find('first', array(
            'conditions' => array('BallotingSetting.project_id' => $projectId),
            'recursive' => -1
        ));
        if (isset($bSetting['BallotingSetting']['portal_certificate_enabled']) && (int)$bSetting['BallotingSetting']['portal_certificate_enabled'] === 0) {
            $this->Session->setFlash(__('Plot allocation certificate downloads are temporarily disabled for this project.'), 'flash', array('alert' => 'warning'));
            return $this->redirect(array('action' => 'index'));
        }

        // Verification URL for QR code
        $verifyUrl = Router::url(array('controller' => 'BallotingResults', 'action' => 'verify', $id, '?' => array('token' => $expectedToken)), true);
        $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&ecc=H&margin=4&format=png&data=' . rawurlencode($verifyUrl);

        $this->layout = 'print_letter';
        $this->set(compact('allotment', 'verifyUrl', 'qrCodeUrl', 'expectedToken', 'id'));
    }

    public function allotmentletter($id = null) {
        $id = (int)$id;
        if ($id <= 0) {
            $this->Session->setFlash(__('Invalid allotment reference.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }

        $allotment = $this->BallotingAllotment->find('first', array(
            'joins' => array(
                array('table' => 'projects', 'alias' => 'Project', 'type' => 'LEFT', 'conditions' => array('BallotingAllotment.project_id=Project.id')),
                array('table' => 'deals', 'alias' => 'Deal', 'type' => 'LEFT', 'conditions' => array('BallotingAllotment.deal_id=Deal.id')),
                array('table' => 'properties', 'alias' => 'Property', 'type' => 'LEFT', 'conditions' => array('Deal.property_id=Property.id')),
                array('table' => 'clients', 'alias' => 'Client', 'type' => 'LEFT', 'conditions' => array('BallotingAllotment.client_id=Client.id')),
                array('table' => 'balloting_units', 'alias' => 'BallotingUnit', 'type' => 'LEFT', 'conditions' => array('BallotingAllotment.balloting_unit_id=BallotingUnit.id'))
            ),
            'fields' => array('BallotingAllotment.*', 'Project.*', 'Deal.*', 'Property.*', 'Client.*', 'BallotingUnit.*'),
            'conditions' => array('BallotingAllotment.id' => $id),
            'recursive' => -1
        ));

        if (empty($allotment['BallotingAllotment']['id'])) {
            $this->Session->setFlash(__('Allotment record not found.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }

        $expectedToken = $this->_generateAllotmentToken($id, $allotment['BallotingAllotment']['allotment_no']);
        $providedToken = isset($this->request->query['token']) ? trim((string)$this->request->query['token']) : '';

        // Authorization check: either valid secure token OR logged in client owns this allotment
        $isOwner = (!empty($this->userId) && (int)$allotment['BallotingAllotment']['client_id'] === (int)$this->userId);
        $hasToken = ($providedToken !== '' && hash_equals($expectedToken, $providedToken));

        if (!$isOwner && !$hasToken) {
            $this->Session->setFlash(__('Access denied. Please login to your account or provide a valid verification link.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }

        $dealId = !empty($allotment['BallotingAllotment']['deal_id']) ? (int)$allotment['BallotingAllotment']['deal_id'] : 0;
        $this->loadModel('Admin.Realestateletter');
        $this->loadModel('Admin.Template');

        $letterRow = array();
        if ($dealId > 0) {
            $letterRow = $this->Realestateletter->find('first', array(
                'fields' => array(
                    'Realestateletter.*',
                    'Deal.*',
                    'Client.*',
                    'Property.*',
                    'Type.name','Contract.name','Unit.name',
                    'Project.*',
                    'Template.*'
                ),
                'joins' => array(
                    array('table' => 'deals', 'alias' => 'Deal', 'type' => 'LEFT', 'conditions' => array('Deal.id=Realestateletter.deal_id')),
                    array('table' => 'clients', 'alias' => 'Client', 'type' => 'LEFT', 'conditions' => array('Client.id=Deal.client_id')),
                    array('table' => 'properties', 'alias' => 'Property', 'type' => 'LEFT', 'conditions' => array('Property.id=Deal.property_id')),
                    array('table' => 'types', 'alias' => 'Type', 'type' => 'LEFT', 'conditions' => array('Type.id=Property.type_id')),
                    array('table' => 'contracts', 'alias' => 'Contract', 'type' => 'LEFT', 'conditions' => array('Contract.id=Property.contract_id')),
                    array('table' => 'units', 'alias' => 'Unit', 'type' => 'LEFT', 'conditions' => array('Unit.id=Property.unit_id')),
                    array('table' => 'projects', 'alias' => 'Project', 'type' => 'LEFT', 'conditions' => array('Project.id=Property.project_id')),
                    array('table' => 'templates', 'alias' => 'Template', 'type' => 'LEFT', 'conditions' => array('Template.id=Realestateletter.template_id'))
                ),
                'conditions' => array(
                    'Realestateletter.deal_id' => $dealId,
                    'Realestateletter.letter_type' => 'RLT_ALLOT',
                    'Realestateletter.status' => 'Published'
                ),
                'order' => array('Realestateletter.id' => 'DESC'),
                'recursive' => -1
            ));
        }

        if (empty($letterRow['Realestateletter']['id'])) {
            // Find active Allotment template
            $templateRow = $this->Template->find('first', array(
                'conditions' => array(
                    'Template.type' => 'RLT_ALLOT',
                    'Template.status' => 'Published'
                ),
                'order' => array('Template.id' => 'DESC'),
                'recursive' => -1
            ));

            $letterRow = array(
                'Realestateletter' => array(
                    'id' => 0,
                    'deal_id' => $dealId,
                    'client_id' => !empty($allotment['BallotingAllotment']['client_id']) ? $allotment['BallotingAllotment']['client_id'] : 0,
                    'letter_type' => 'RLT_ALLOT',
                    'title' => !empty($templateRow['Template']['name']) ? $templateRow['Template']['name'] : 'Allotment Letter',
                    'reference_no' => 'ALLOT/WV-' . (!empty($allotment['BallotingAllotment']['allotment_no']) ? $allotment['BallotingAllotment']['allotment_no'] : $id),
                    'letter_date' => !empty($allotment['BallotingAllotment']['allotment_date']) ? $allotment['BallotingAllotment']['allotment_date'] : date('Y-m-d'),
                    'description' => !empty($templateRow['Template']['description']) ? $templateRow['Template']['description'] : ''
                ),
                'Deal' => isset($allotment['Deal']) ? $allotment['Deal'] : array(),
                'Client' => isset($allotment['Client']) ? $allotment['Client'] : array(),
                'Property' => isset($allotment['Property']) ? $allotment['Property'] : array(),
                'Project' => isset($allotment['Project']) ? $allotment['Project'] : array(),
                'Template' => isset($templateRow['Template']) ? $templateRow['Template'] : array()
            );
        }

        $rawDescription = '';
        if (!empty($letterRow['Template']['description']) && trim($letterRow['Template']['description']) !== '') {
            $rawDescription = (string)$letterRow['Template']['description'];
        } elseif (!empty($letterRow['Realestateletter']['description'])) {
            $rawDescription = (string)$letterRow['Realestateletter']['description'];
        }

        $renderedDescription = $this->_renderLetterPlaceholders($rawDescription, $allotment, $letterRow);

        $autoPrint = isset($this->request->query['print']) ? (bool)$this->request->query['print'] : false;
        $allotmentNo = !empty($allotment['BallotingAllotment']['allotment_no']) ? $allotment['BallotingAllotment']['allotment_no'] : $id;

        $this->set('dealId', $dealId);
        $this->set('post', $letterRow);
        $this->set('renderedDescription', $renderedDescription);
        $this->set('isFullHtmlTemplate', (stripos($renderedDescription, '<html') !== false || stripos($renderedDescription, '<body') !== false));
        $this->set('letterType', 'RLT_ALLOT');
        $this->set('letterTypeLabel', __('Allotment Letter'));
        $this->set('letterTitle', __('Allotment Letter (Duplicate Copy)'));
        $this->set('pdfFileName', 'Allotment-Letter-Duplicate-' . $allotmentNo . '.pdf');
        $this->set('clientWatermark', true);
        $this->set('showClientDuplicateWatermark', true);
        $this->set('autoPrint', $autoPrint);
        $this->set('mode', 'print');

        $this->layout = false;
        $this->render('/BallotingResults/allotmentletter');
    }

    protected function _renderLetterPlaceholders($templateText, $allotment, $letterRow) {
        $ballot = isset($allotment['BallotingAllotment']) ? $allotment['BallotingAllotment'] : array();
        $deal = isset($allotment['Deal']) ? $allotment['Deal'] : (isset($letterRow['Deal']) ? $letterRow['Deal'] : array());
        $client = isset($allotment['Client']) ? $allotment['Client'] : (isset($letterRow['Client']) ? $letterRow['Client'] : array());
        $property = isset($allotment['Property']) ? $allotment['Property'] : (isset($letterRow['Property']) ? $letterRow['Property'] : array());
        $project = isset($allotment['Project']) ? $allotment['Project'] : (isset($letterRow['Project']) ? $letterRow['Project'] : array());
        $letter = isset($letterRow['Realestateletter']) ? $letterRow['Realestateletter'] : array();

        $allotNo = !empty($ballot['allotment_no']) ? $ballot['allotment_no'] : (!empty($ballot['id']) ? ('WV-' . $ballot['id']) : 'N/A');
        $propNo = !empty($ballot['property_no']) ? $ballot['property_no'] : (!empty($property['name']) ? $property['name'] : $allotNo);
        $blockName = !empty($ballot['block_name']) ? $ballot['block_name'] : 'Main Block';
        $streetNo = !empty($ballot['street_no']) ? $ballot['street_no'] : 'Main';
        $streetAddress = !empty($ballot['street_address']) ? $ballot['street_address'] : ('Street ' . $streetNo . ', ' . $blockName);
        $unitArea = !empty($ballot['unit_area']) ? ($ballot['unit_area'] . ' ' . (!empty($ballot['area_unit']) ? $ballot['area_unit'] : 'Marla')) : (!empty($property['area']) ? $property['area'] : 'N/A');
        $category = !empty($ballot['permanent_type']) ? $ballot['permanent_type'] : 'Standard';
        $allotDate = !empty($ballot['allotment_date']) ? date('d-M-Y', strtotime($ballot['allotment_date'])) : date('d-M-Y');
        $refNo = !empty($letter['reference_no']) ? $letter['reference_no'] : ('ALLOT/WV-' . $allotNo);
        $letterDate = !empty($letter['letter_date']) ? date('d-M-Y', strtotime($letter['letter_date'])) : $allotDate;
        $clientName = !empty($client['name']) ? $client['name'] : 'Allottee';
        $clientFather = !empty($client['fathername']) ? $client['fathername'] : '';
        $clientCnic = !empty($client['pincode']) ? $client['pincode'] : '';
        $clientMobile = !empty($client['mobile']) ? $client['mobile'] : '';
        $clientPhone = !empty($client['phone']) ? $client['phone'] : $clientMobile;
        $clientEmail = !empty($client['email']) ? $client['email'] : '';
        $clientAddress = !empty($client['address']) ? $client['address'] : '';
        $dealInvoice = !empty($deal['invoice_no']) ? $deal['invoice_no'] : 'N/A';
        $dealDate = !empty($deal['date']) ? date('d-M-Y', strtotime($deal['date'])) : '';
        $dealTotal = !empty($deal['total_amount']) ? number_format((float)$deal['total_amount'], 2) : '0.00';
        $projectName = !empty($project['name']) ? $project['name'] : 'White Villas Lahore';
        $propertyName = !empty($property['name']) ? $property['name'] : ('Plot ' . $propNo);
        $siteOrg = !empty($this->siteOrganization) ? $this->siteOrganization : 'WHITE VILLAS LAHORE';

        $map = array(
            '{{letter_date}}' => $letterDate,
            '{{letter_ref_no}}' => $refNo,
            '{{client_name}}' => $clientName,
            '{{client_father}}' => $clientFather,
            '{{client_father_name}}' => $clientFather,
            '{{client_cnic}}' => $clientCnic,
            '{{cnic}}' => $clientCnic,
            '{{client_mobile}}' => $clientMobile,
            '{{mobile}}' => $clientMobile,
            '{{client_phone}}' => $clientPhone,
            '{{phone}}' => $clientPhone,
            '{{client_email}}' => $clientEmail,
            '{{email}}' => $clientEmail,
            '{{client_address}}' => $clientAddress,
            '{{address}}' => $clientAddress,
            '{{property_name}}' => $propertyName,
            '{{property_type}}' => 'Residential',
            '{{property_contract}}' => 'Standard',
            '{{project_name}}' => $projectName,
            '{{deal_invoice_no}}' => $dealInvoice,
            '{{deal_invoice}}' => $dealInvoice,
            '{{deal_date}}' => $dealDate,
            '{{property_area}}' => $unitArea,
            '{{deal_total_amount}}' => $dealTotal,
            '{{site_organization}}' => $siteOrg,
            '{{site_name}}' => $siteOrg,
            '{{ballot_allotment_no}}' => $allotNo,
            '{{allotment_no}}' => $allotNo,
            '{{ballot_property_no}}' => $propNo,
            '{{property_no}}' => $propNo,
            '{{plot_no}}' => $propNo,
            '{{ballot_block_name}}' => $blockName,
            '{{block_name}}' => $blockName,
            '{{ballot_street_address}}' => $streetAddress,
            '{{street_no}}' => $streetNo,
            '{{street_address}}' => $streetAddress,
            '{{ballot_area_full}}' => $unitArea,
            '{{unit_area}}' => $unitArea,
            '{{plot_size}}' => $unitArea,
            '{{allotment_date}}' => $allotDate,
            '{{permanent_type}}' => $category,
            '{{category}}' => $category
        );

        $rendered = strtr($templateText, $map);
        $rendered = preg_replace('/\{\{\s*[^}]+\s*\}\}/', 'N/A', $rendered);
        return $rendered;
    }

    public function verify($id = null) {
        $id = (int)$id;
        $token = isset($this->request->query['token']) ? trim((string)$this->request->query['token']) : '';

        $allotment = $this->BallotingAllotment->find('first', array(
            'joins' => array(
                array('table' => 'projects', 'alias' => 'Project', 'type' => 'LEFT', 'conditions' => array('BallotingAllotment.project_id=Project.id')),
                array('table' => 'deals', 'alias' => 'Deal', 'type' => 'LEFT', 'conditions' => array('BallotingAllotment.deal_id=Deal.id')),
                array('table' => 'properties', 'alias' => 'Property', 'type' => 'LEFT', 'conditions' => array('Deal.property_id=Property.id')),
                array('table' => 'clients', 'alias' => 'Client', 'type' => 'LEFT', 'conditions' => array('BallotingAllotment.client_id=Client.id'))
            ),
            'fields' => array('BallotingAllotment.*', 'Project.*', 'Deal.*', 'Property.*', 'Client.*'),
            'conditions' => array('BallotingAllotment.id' => $id),
            'recursive' => -1
        ));

        $isValid = false;
        if (!empty($allotment['BallotingAllotment']['id'])) {
            $expectedToken = $this->_generateAllotmentToken($id, $allotment['BallotingAllotment']['allotment_no']);
            if ($token !== '' && hash_equals($expectedToken, $token)) {
                $isValid = true;
            }
        }

        $this->layout = 'default';
        $this->set(compact('allotment', 'isValid', 'id'));
    }

    protected function _generateAllotmentToken($id, $allotmentNo) {
        return md5('wv_ballot_cert_' . (int)$id . '_' . trim((string)$allotmentNo) . '_salt2026');
    }

    protected function _performLiveSearch($term) {
        $term = trim((string)$term);
        if ($term === '') {
            return null;
        }

        // Clean query: strip non-alphanumeric except hyphen
        $cleanTerm = preg_replace('/[^a-zA-Z0-9\-]/', '', $term);

        // Search 1: In balloting_allotments (by allotment_no, property_no, or deal invoice_no)
        $allotment = $this->BallotingAllotment->find('first', array(
            'joins' => array(
                array('table' => 'projects', 'alias' => 'Project', 'type' => 'LEFT', 'conditions' => array('BallotingAllotment.project_id=Project.id')),
                array('table' => 'deals', 'alias' => 'Deal', 'type' => 'LEFT', 'conditions' => array('BallotingAllotment.deal_id=Deal.id')),
                array('table' => 'properties', 'alias' => 'Property', 'type' => 'LEFT', 'conditions' => array('Deal.property_id=Property.id')),
                array('table' => 'clients', 'alias' => 'Client', 'type' => 'LEFT', 'conditions' => array('BallotingAllotment.client_id=Client.id')),
                array('table' => 'balloting_units', 'alias' => 'BallotingUnit', 'type' => 'LEFT', 'conditions' => array('BallotingAllotment.balloting_unit_id=BallotingUnit.id'))
            ),
            'fields' => array('BallotingAllotment.*', 'Project.*', 'Deal.*', 'Property.*', 'Client.*', 'BallotingUnit.*'),
            'conditions' => array(
                'BallotingAllotment.status' => 'Allotted',
                'OR' => array(
                    'BallotingAllotment.allotment_no' => $term,
                    'BallotingAllotment.property_no' => $term,
                    'Deal.invoice_no' => $term,
                    'Client.pincode' => $term,
                    'Client.mobile LIKE' => '%' . substr($cleanTerm, -9)
                )
            ),
            'order' => array('BallotingAllotment.id' => 'DESC'),
            'recursive' => -1
        ));

        if (!empty($allotment['BallotingAllotment']['id'])) {
            $aid = (int)$allotment['BallotingAllotment']['id'];
            $certToken = $this->_generateAllotmentToken($aid, $allotment['BallotingAllotment']['allotment_no']);
            $certUrl = Router::url(array('controller' => 'BallotingResults', 'action' => 'certificate', $aid, '?' => array('token' => $certToken)), true);
            $allotmentLetterUrl = Router::url(array('controller' => 'BallotingResults', 'action' => 'allotmentletter', $aid, '?' => array('token' => $certToken)), true);

            return array(
                'status' => 'ALLOTTED',
                'allotment_id' => $aid,
                'allotment_no' => !empty($allotment['BallotingAllotment']['allotment_no']) ? $allotment['BallotingAllotment']['allotment_no'] : ('WV-' . $aid),
                'property_no' => !empty($allotment['BallotingAllotment']['property_no']) ? $allotment['BallotingAllotment']['property_no'] : 'Assigned',
                'block_name' => !empty($allotment['BallotingAllotment']['block_name']) ? $allotment['BallotingAllotment']['block_name'] : 'Main Block',
                'street_no' => !empty($allotment['BallotingAllotment']['street_no']) ? $allotment['BallotingAllotment']['street_no'] : 'Main',
                'street_address' => !empty($allotment['BallotingAllotment']['street_address']) ? $allotment['BallotingAllotment']['street_address'] : '',
                'unit_area' => $allotment['BallotingAllotment']['unit_area'] . ' ' . (!empty($allotment['BallotingAllotment']['area_unit']) ? $allotment['BallotingAllotment']['area_unit'] : 'Marla'),
                'category' => !empty($allotment['BallotingAllotment']['permanent_type']) ? $allotment['BallotingAllotment']['permanent_type'] : 'Standard',
                'project_name' => !empty($allotment['Project']['name']) ? $allotment['Project']['name'] : 'White Villas',
                'client_name' => !empty($allotment['Client']['name']) ? $allotment['Client']['name'] : 'Allottee',
                'deal_invoice' => !empty($allotment['Deal']['invoice_no']) ? $allotment['Deal']['invoice_no'] : '',
                'allotment_date' => !empty($allotment['BallotingAllotment']['allotment_date']) ? date('d-M-Y', strtotime($allotment['BallotingAllotment']['allotment_date'])) : date('d-M-Y'),
                'certificate_url' => $certUrl,
                'allotment_letter_url' => $allotmentLetterUrl
            );
        }

        // Search 2: Deal exists but not yet allotted
        $deal = $this->MyDeal->find('first', array(
            'joins' => array(
                array('table' => 'properties', 'alias' => 'Property', 'type' => 'LEFT', 'conditions' => array('MyDeal.property_id=Property.id')),
                array('table' => 'projects', 'alias' => 'Project', 'type' => 'LEFT', 'conditions' => array('Property.project_id=Project.id')),
                array('table' => 'clients', 'alias' => 'Client', 'type' => 'LEFT', 'conditions' => array('MyDeal.client_id=Client.id'))
            ),
            'fields' => array('MyDeal.*', 'Property.*', 'Project.*', 'Client.*'),
            'conditions' => array(
                'OR' => array(
                    'MyDeal.invoice_no' => $term,
                    'Client.pincode' => $term,
                    'Client.mobile LIKE' => '%' . substr($cleanTerm, -9)
                )
            ),
            'recursive' => -1
        ));

        if (!empty($deal['MyDeal']['id'])) {
            return array(
                'status' => 'PENDING_BALLOTING',
                'deal_invoice' => !empty($deal['MyDeal']['invoice_no']) ? $deal['MyDeal']['invoice_no'] : ('DEAL-' . $deal['MyDeal']['id']),
                'property_name' => !empty($deal['Property']['name']) ? $deal['Property']['name'] : 'Plot/Villa',
                'project_name' => !empty($deal['Project']['name']) ? $deal['Project']['name'] : 'White Villas',
                'client_name' => !empty($deal['Client']['name']) ? $deal['Client']['name'] : 'Client',
                'message' => 'Deal verified. Your property is queued for the upcoming balloting run. Please ensure your installments are cleared to maintain top balloting priority.'
            );
        }

        return array(
            'status' => 'NOT_FOUND',
            'message' => 'No balloting allotment or registration record found matching "' . htmlspecialchars($term) . '". Please check your registration number or CNIC.'
        );
    }
}
