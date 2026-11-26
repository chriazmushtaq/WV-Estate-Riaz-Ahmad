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
        if ($action === 'certificate') {
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
                    'Client.cnic' => $term,
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
                'certificate_url' => $certUrl
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
                    'Client.cnic' => $term,
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
