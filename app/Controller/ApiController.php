<?php
App::uses('AppController', 'Controller');

class ApiController extends AppController
{
    public $uses = array(
        'Property', 
        'PropertiesPhoto', 
        'Type', 
        'Contract', 
        'Location', 
        'User', 
        'Client',
        'Register',
        'Contact', 
        'WebsiteContact',
        'MyDeal',
        'MyPayment',
        'PlansPayment',
        'MyInvpastdue',
        'NewsPost',
        'NewsCategory',
        'ClientNotification',
        'AppSetting'
    );

    protected $_allowedClientDealStatuses = array(
        'Active',
        'Blocked',
        'active',
        'blocked'
    );

    protected function _isDealVisibleToClient($dealRow)
    {
        $d = isset($dealRow['MyDeal']) ? $dealRow['MyDeal'] : (isset($dealRow['Deal']) ? $dealRow['Deal'] : $dealRow);
        if (empty($d) || !is_array($d)) {
            return false;
        }

        $status = !empty($d['deal_status']) ? trim((string)$d['deal_status']) : (!empty($d['status']) ? trim((string)$d['status']) : 'Active');
        $statusLower = strtolower($status);

        // Strict: ONLY 'Active' and 'Blocked' statuses are visible to clients
        if ($statusLower === 'active' || $statusLower === 'blocked') {
            return true;
        }

        // All other statuses ('Transferd', 'Permanent Blocked', 'Refund', 'Refund-Running', 'Transferred', 'Cancelled', 'Deleted', etc.) are hidden from client
        return false;
    }

    public function beforeFilter()
    {
        parent::beforeFilter();
        
        // Enable CORS for mobile app and web clients
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        
        if ($this->request->is('options')) {
            $this->response->statusCode(200);
            $this->response->send();
            $this->_stop();
        }

        $this->autoRender = false;
        $this->response->type('application/json');
        $this->_ensureNotificationsTable();
    }

    protected function _jsonResponse($data, $statusCode = 200)
    {
        $this->response->statusCode($statusCode);
        $this->response->body(json_encode($data));
        return $this->response;
    }

    protected function _ensureNotificationsTable()
    {
        static $ready = null;
        if ($ready !== null) return $ready;
        try {
            $this->Client->query("CREATE TABLE IF NOT EXISTS `client_notifications` (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `client_id` bigint(20) NOT NULL,
                `title` varchar(191) NOT NULL,
                `type` varchar(50) NOT NULL DEFAULT 'admin_notice',
                `message` text NOT NULL,
                `is_read` tinyint(1) NOT NULL DEFAULT 0,
                `created` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `client_id` (`client_id`),
                KEY `is_read` (`is_read`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8");
            $ready = true;
        } catch (Exception $e) {
            $ready = false;
        }
        return $ready;
    }

    /**
     * GET /api/properties
     */
    public function properties()
    {
        try {
            $conditions = array(
                'OR' => array(
                    'Property.published' => array('Yes', 'yes', '1', 1),
                    'Property.published IS NULL'
                )
            );
            $query = $this->request->query;
            
            if (!empty($query['keyword'])) {
                $kw = '%' . trim($query['keyword']) . '%';
                $conditions['OR']['Property.name LIKE'] = $kw;
                $conditions['OR']['Property.address LIKE'] = $kw;
                $conditions['OR']['Property.uniq_id LIKE'] = $kw;
            }

            if (!empty($query['type'])) {
                $conditions['Property.type_id'] = $query['type'];
            }

            if (!empty($query['contract'])) {
                $conditions['Property.contract_id'] = $query['contract'];
            }

            if (!empty($query['location'])) {
                $conditions['Property.location_id'] = $query['location'];
            }

            if (!empty($query['bedroom'])) {
                $conditions['Property.bedroom'] = $query['bedroom'];
            }

            if (!empty($query['bathroom'])) {
                $conditions['Property.bathroom'] = $query['bathroom'];
            }

            if (!empty($query['start_price']) && !empty($query['end_price'])) {
                $conditions['Property.price BETWEEN ? AND ?'] = array($query['start_price'], $query['end_price']);
            } elseif (!empty($query['start_price'])) {
                $conditions['Property.price >='] = $query['start_price'];
            } elseif (!empty($query['end_price'])) {
                $conditions['Property.end_price <='] = $query['end_price'];
            }

            $page = !empty($query['page']) ? (int)$query['page'] : 1;
            $limit = !empty($query['limit']) ? (int)$query['limit'] : 30;

            $properties = $this->Property->find('all', array(
                'conditions' => $conditions,
                'contain' => array(
                    'Type', 
                    'Contract', 
                    'Location', 
                    'User' => array('fields' => array('id', 'name', 'email', 'phone', 'mobile', 'photo')),
                    'PropertiesPhoto' => array('order' => array('PropertiesPhoto.sort_order' => 'ASC', 'PropertiesPhoto.id' => 'ASC'))
                ),
                'order' => array('Property.id' => 'DESC'),
                'limit' => $limit,
                'page' => $page
            ));

            $formatted = array();
            foreach ($properties as $p) {
                $formatted[] = $this->_formatProperty($p);
            }

            return $this->_jsonResponse(array(
                'status' => true,
                'count' => count($formatted),
                'page' => $page,
                'data' => $formatted
            ));
        } catch (Exception $e) {
            return $this->_jsonResponse(array(
                'status' => false,
                'message' => $e->getMessage()
            ), 500);
        }
    }

    /**
     * GET /api/properties/view/{id}
     */
    public function view($id = null)
    {
        if (empty($id)) {
            return $this->_jsonResponse(array('status' => false, 'message' => 'Property ID is required'), 400);
        }

        $property = $this->Property->find('first', array(
            'conditions' => array(
                'Property.id' => $id
            ),
            'contain' => array(
                'Type', 
                'Contract', 
                'Location', 
                'User' => array('fields' => array('id', 'name', 'email', 'phone', 'mobile', 'photo')),
                'PropertiesPhoto' => array('order' => array('PropertiesPhoto.sort_order' => 'ASC', 'PropertiesPhoto.id' => 'ASC')),
                'PropertiesDocument',
                'PropertiesFloorplan'
            )
        ));

        if (empty($property)) {
            return $this->_jsonResponse(array('status' => false, 'message' => 'Property not found'), 404);
        }

        return $this->_jsonResponse(array(
            'status' => true,
            'data' => $this->_formatProperty($property, true)
        ));
    }

    /**
     * GET /api/meta
     */
    public function meta()
    {
        $types = $this->Type->find('all', array('fields' => array('id', 'name')));
        $contracts = $this->Contract->find('all', array('fields' => array('id', 'name')));
        $locations = $this->Location->find('all', array('fields' => array('id', 'name')));

        $cleanTypes = array();
        foreach ($types as $t) { $cleanTypes[] = $t['Type']; }

        $cleanContracts = array();
        foreach ($contracts as $c) { $cleanContracts[] = $c['Contract']; }

        $cleanLocations = array();
        foreach ($locations as $l) { $cleanLocations[] = $l['Location']; }

        return $this->_jsonResponse(array(
            'status' => true,
            'currency' => 'Rs',
            'support_email' => 'info@whitevillas.com.pk',
            'data' => array(
                'types' => $cleanTypes,
                'contracts' => $cleanContracts,
                'locations' => $cleanLocations
            )
        ));
    }

    /**
     * POST /api/contact
     */
    public function contact()
    {
        $inputData = json_decode($this->request->input(), true);
        if (empty($inputData)) {
            $inputData = $this->request->data;
        }

        if (empty($inputData['name']) || empty($inputData['email']) || empty($inputData['message'])) {
            return $this->_jsonResponse(array('status' => false, 'message' => 'Name, email and message are required'), 400);
        }

        $contactData = array(
            'name' => trim($inputData['name']),
            'email' => trim($inputData['email']),
            'phone' => !empty($inputData['phone']) ? trim($inputData['phone']) : '',
            'message' => trim($inputData['message']),
            'property_id' => !empty($inputData['property_id']) ? $inputData['property_id'] : null,
            'created' => date('Y-m-d H:i:s')
        );

        $this->Contact->create();
        if ($this->Contact->save($contactData)) {
            return $this->_jsonResponse(array('status' => true, 'message' => 'Inquiry submitted successfully!'));
        }

        return $this->_jsonResponse(array('status' => false, 'message' => 'Failed to save inquiry'), 500);
    }

    /**
     * POST /api/login
     */
    public function login()
    {
        $inputData = json_decode($this->request->input(), true);
        if (empty($inputData)) {
            $inputData = $this->request->data;
        }

        if (empty($inputData['email']) || empty($inputData['password'])) {
            return $this->_jsonResponse(array('status' => false, 'message' => 'Email and password are required'), 400);
        }

        $email = trim($inputData['email']);
        $password = trim($inputData['password']);
        $userType = !empty($inputData['user_type']) ? strtolower(trim($inputData['user_type'])) : 'auto';

        // 1. Check Client table
        if ($userType === 'client' || $userType === 'auto') {
            $client = $this->Client->find('first', array(
                'conditions' => array('Client.email' => $email),
                'recursive' => -1
            ));

            if (!empty($client)) {
                if (empty($client['Client']['password'])) {
                    return $this->_jsonResponse(array(
                        'status' => false,
                        'message' => 'Your account does not have a portal password set. Please contact administrator.'
                    ), 401);
                }

                if ($this->_verifyPassword($password, $client['Client']['password'])) {
                    $clientStatus = !empty($client['Client']['status']) ? trim((string)$client['Client']['status']) : 'Active';
                    if (strcasecmp($clientStatus, 'Active') !== 0) {
                        return $this->_jsonResponse(array(
                            'status' => false,
                            'message' => 'Your account has been blocked. Please contact administrator to restore login access.'
                        ), 403);
                    }
                    $clientData = $client['Client'];
                    unset($clientData['password']);
                    $clientData['account_type'] = 'client';
                    $clientData['role'] = 'Client';
                    $clientData['app_login_status'] = 1;
                    $this->_markClientAppLogin($clientData['id']);
                    return $this->_jsonResponse(array(
                        'status' => true,
                        'message' => 'Client login successful',
                        'user' => $clientData
                    ));
                }
            }
        }

        // 2. Check User table (Admin / Agent / Staff)
        if ($userType === 'admin' || $userType === 'agent' || $userType === 'auto') {
            $user = $this->User->find('first', array(
                'conditions' => array('User.email' => $email),
                'recursive' => -1
            ));

            if (!empty($user)) {
                if (!empty($user['User']['login_status']) && strcasecmp(trim((string)$user['User']['login_status']), 'Blocked') === 0) {
                    return $this->_jsonResponse(array(
                        'status' => false,
                        'message' => 'Login is blocked. Please contact administrator.'
                    ), 403);
                }
                if (!empty($user['User']['password']) && $this->_verifyPassword($password, $user['User']['password'])) {
                    $userData = $user['User'];
                    unset($userData['password']);
                    $userData['account_type'] = 'admin';
                    $userData['role'] = !empty($userData['role']) ? $userData['role'] : ($userType === 'agent' ? 'Agent' : 'Administrator');
                    return $this->_jsonResponse(array(
                        'status' => true,
                        'message' => 'Login successful',
                        'user' => $userData
                    ));
                }
            }
        }

        // Strict: If credentials are not found in the database or password is incorrect
        return $this->_jsonResponse(array(
            'status' => false,
            'message' => 'Invalid email or password. No matching account found in database.'
        ), 401);
    }

    protected function _calculateOverdueData($dealIds, $dealsMap = array(), $currentDateTime = null)
    {
        if (empty($currentDateTime)) {
            $currentDateTime = date('Y-m-d');
        }
        if (empty($dealIds)) {
            return array('overdue_amount' => 0, 'overdue_count' => 0, 'overdue_list' => array());
        }

        $this->loadModel('PlansPayment');
        $this->loadModel('MyPayment');

        // Sum paid amounts per installment plan across all visible deals
        $paidByPlan = array();
        $planPaidRows = $this->MyPayment->find('all', array(
            'fields' => array('MyPayment.plans_payment_id', 'SUM(COALESCE(NULLIF(MyPayment.emi, 0), MyPayment.amount, 0)) AS paid_total'),
            'conditions' => array(
                'MyPayment.deal_id' => $dealIds,
                'MyPayment.plans_payment_id >' => 0
            ),
            'group' => array('MyPayment.plans_payment_id'),
            'recursive' => -1
        ));

        foreach ((array)$planPaidRows as $planPaidRow) {
            $planId = isset($planPaidRow['MyPayment']['plans_payment_id']) ? (int)$planPaidRow['MyPayment']['plans_payment_id'] : 0;
            if ($planId > 0) {
                $paidByPlan[$planId] = isset($planPaidRow[0]['paid_total']) ? (float)$planPaidRow[0]['paid_total'] : (isset($planPaidRow['MyPayment']['paid_total']) ? (float)$planPaidRow['MyPayment']['paid_total'] : 0);
            }
        }

        $planConditions = array('PlansPayment.deal_id' => $dealIds);
        if ($this->PlansPayment->hasField('is_deal_charge')) {
            $planConditions['OR'] = array(
                array('PlansPayment.is_deal_charge' => 0),
                array('PlansPayment.is_deal_charge' => null)
            );
        }

        // Fetch all installment plans for visible deals
        $planRows = $this->PlansPayment->find('all', array(
            'fields' => array(
                'PlansPayment.id',
                'PlansPayment.deal_id',
                'PlansPayment.name',
                'PlansPayment.amount',
                'PlansPayment.date',
                'PlansPayment.recovery_promise_date'
            ),
            'conditions' => $planConditions,
            'order' => array('PlansPayment.date' => 'ASC', 'PlansPayment.id' => 'ASC'),
            'recursive' => -1
        ));

        $overdueList = array();
        $overdueTotal = 0;

        foreach ((array)$planRows as $p) {
            if (empty($p['PlansPayment']['id']) || empty($p['PlansPayment']['deal_id'])) {
                continue;
            }
            $pId = (int)$p['PlansPayment']['id'];
            $dealId = (int)$p['PlansPayment']['deal_id'];
            $planAmt = isset($p['PlansPayment']['amount']) ? (float)$p['PlansPayment']['amount'] : 0;
            $planPaid = isset($paidByPlan[$pId]) ? (float)$paidByPlan[$pId] : 0;
            $pendingAmt = round($planAmt - $planPaid, 2);
            $dueDate = !empty($p['PlansPayment']['date']) ? $p['PlansPayment']['date'] : null;

            if ($pendingAmt > 0 && !empty($dueDate) && $dueDate <= $currentDateTime) {
                $dealInfo = isset($dealsMap[$dealId]) ? $dealsMap[$dealId] : array();
                $code = !empty($dealInfo['code']) ? $dealInfo['code'] : 'MA'.$dealId;
                $propName = !empty($dealInfo['property_name']) ? $dealInfo['property_name'] : 'White Villas Property';
                $dealStatus = !empty($dealInfo['deal_status']) ? $dealInfo['deal_status'] : 'Active';

                $overdueTotal += $pendingAmt;
                $overdueList[] = array(
                    'id' => $pId,
                    'deal_id' => $dealId,
                    'deal_code' => $code,
                    'property_name' => $propName,
                    'deal_status' => $dealStatus,
                    'installment_name' => !empty($p['PlansPayment']['name']) ? $p['PlansPayment']['name'] : 'Installment #'.$pId,
                    'due_amount' => $pendingAmt,
                    'amount' => $planAmt,
                    'paid_amount' => $planPaid,
                    'due_date' => date('d-M-Y', strtotime($dueDate)),
                    'raw_due_date' => $dueDate,
                    'late_surcharge' => 0,
                    'status' => 'OVERDUE'
                );
            }
        }

        return array(
            'overdue_amount' => round($overdueTotal, 2),
            'overdue_count' => count($overdueList),
            'overdue_list' => $overdueList
        );
    }

    /**
     * GET /api/dashboard
     */
    public function dashboard()
    {
        $clientId = !empty($this->request->query['client_id']) ? (int)$this->request->query['client_id'] : (!empty($this->request->query['user_id']) ? (int)$this->request->query['user_id'] : 0);

        if ($clientId <= 0) {
            return $this->_jsonResponse(array('status' => false, 'message' => 'Client ID is required'), 400);
        }

        if ($this->_isClientBlocked($clientId)) {
            return $this->_jsonResponse(array(
                'status' => false,
                'message' => 'Your account has been blocked. Please contact administrator to restore login access.'
            ), 403);
        }

        $clientRow = $this->Client->find('first', array(
            'conditions' => array('Client.id' => $clientId),
            'recursive' => -1
        ));

        $this->_markClientAppLogin($clientId);

        $clientInfo = !empty($clientRow['Client']) ? $clientRow['Client'] : array('id' => $clientId, 'name' => 'Client');
        unset($clientInfo['password']);

        // Fetch client deals strictly filtered to Active and Blocked only
        $dealConditions = array('MyDeal.client_id' => $clientId);
        if ($this->MyDeal->hasField('deal_status')) {
            $dealConditions['MyDeal.deal_status'] = array('Active', 'Blocked', 'active', 'blocked');
        }

        $dealRows = $this->MyDeal->find('all', array(
            'conditions' => $dealConditions,
            'contain' => array('Property'),
            'recursive' => 1
        ));

        $dealIds = array();
        $totalDealsAmount = 0;
        $dealsMap = array();

        foreach ($dealRows as $d) {
            if (!$this->_isDealVisibleToClient($d)) {
                continue;
            }
            $did = (int)$d['MyDeal']['id'];
            $dealIds[] = $did;
            $amt = isset($d['MyDeal']['total_amount']) ? (float)$d['MyDeal']['total_amount'] : 0;
            $totalDealsAmount += $amt;
            $code = !empty($d['MyDeal']['code']) ? $d['MyDeal']['code'] : (!empty($d['Property']['uniq_id']) ? $d['Property']['uniq_id'] : 'MA'.$did);
            $propName = !empty($d['Property']['name']) ? $d['Property']['name'] : 'White Villas Block A - 3 Marla Executive';

            $rawStatus = !empty($d['MyDeal']['deal_status']) ? trim($d['MyDeal']['deal_status']) : 'Active';
            $normalizedStatus = (strtolower($rawStatus) === 'blocked') ? 'Blocked' : 'Active';

            $dealsMap[$did] = array(
                'deal_id' => $did,
                'code' => $code,
                'property_name' => $propName,
                'deal_status' => $normalizedStatus,
                'is_blocked' => ($normalizedStatus === 'Blocked'),
                'total_amount' => $amt,
                'receipts' => array(),
                'total_paid' => 0
            );
        }

        $totalPaidAmount = 0;
        $totalReceiptsCount = 0;

        if (!empty($dealIds)) {
            $payments = $this->MyPayment->find('all', array(
                'conditions' => array('MyPayment.deal_id' => $dealIds),
                'order' => array('MyPayment.id' => 'DESC')
            ));

            foreach ($payments as $p) {
                $pay = $p['MyPayment'];
                $did = (int)$pay['deal_id'];
                $pamt = isset($pay['total_amount']) ? (float)$pay['total_amount'] : (isset($pay['amount']) ? (float)$pay['amount'] : 0);
                $totalPaidAmount += $pamt;
                $totalReceiptsCount++;

                $rawDate = !empty($pay['payment_date']) ? $pay['payment_date'] : (!empty($pay['paid_date']) ? $pay['paid_date'] : (!empty($pay['date']) ? $pay['date'] : (!empty($pay['created']) ? $pay['created'] : '')));
                $paidDateStr = (!empty($rawDate) && $rawDate !== '0000-00-00' && $rawDate !== '0000-00-00 00:00:00') ? date('d-M-Y', strtotime($rawDate)) : '-';
                $txRef = !empty($pay['remarks']) ? $pay['remarks'] : (!empty($pay['transaction_id']) ? $pay['transaction_id'] : (!empty($pay['cheque_no']) ? $pay['cheque_no'] : (!empty($pay['ref_no']) ? $pay['ref_no'] : 'TID#'.$pay['id'])));

                $formattedReceipt = array(
                    'id' => $pay['id'],
                    'receipt_no' => !empty($pay['receipt_no']) ? $pay['receipt_no'] : 'R-'.$pay['id'],
                    'paid_amount' => $pamt,
                    'installment_fee' => isset($pay['emi']) ? (float)$pay['emi'] : 0,
                    'late_fee' => isset($pay['late_fee']) ? (float)$pay['late_fee'] : 0,
                    'payment_name' => !empty($pay['name']) ? $pay['name'] : 'Installment Payment',
                    'paid_date' => $paidDateStr,
                    'payment_date' => !empty($pay['payment_date']) ? $pay['payment_date'] : $rawDate,
                    'payment_type' => !empty($pay['payment_mode']) ? $pay['payment_mode'] : 'Online Bank',
                    'transaction_ref' => $txRef
                );

                if (isset($dealsMap[$did])) {
                    $dealsMap[$did]['receipts'][] = $formattedReceipt;
                    $dealsMap[$did]['total_paid'] += $pamt;
                }
            }
        }

        if (empty($dealsMap)) {
            $dealsMap = array();
            $totalPaidAmount = 0;
            $totalDealsAmount = 0;
            $totalReceiptsCount = 0;
        }

        $overdueData = $this->_calculateOverdueData($dealIds, $dealsMap, date('Y-m-d'));
        $dueAmount = max(0, $totalDealsAmount - $totalPaidAmount);
        $totalSum = max(1, $totalDealsAmount);
        $progressPercentage = $totalDealsAmount > 0 ? round(($totalPaidAmount / $totalSum) * 100, 1) : 0;

        return $this->_jsonResponse(array(
            'status' => true,
            'client_info' => $clientInfo,
            'summary' => array(
                'visible_deals' => count($dealsMap),
                'paid_amount' => $totalPaidAmount,
                'receipts_count' => $totalReceiptsCount,
                'due_amount' => $dueAmount,
                'overdue_amount' => $overdueData['overdue_amount'],
                'overdue_installments' => $overdueData['overdue_count'],
                'progress_percentage' => $progressPercentage
            ),
            'deals_with_receipts' => array_values($dealsMap),
            'deals_with_payments' => array_values($dealsMap),
            'overdue_installments' => $overdueData['overdue_list']
        ));
    }

    /**
     * GET /api/my_deals
     */
    public function my_deals()
    {
        $userId = !empty($this->request->query['user_id']) ? (int)$this->request->query['user_id'] : (!empty($this->request->query['client_id']) ? (int)$this->request->query['client_id'] : 0);
        
        if ($userId > 0 && $this->_isClientBlocked($userId)) {
            return $this->_jsonResponse(array(
                'status' => false,
                'message' => 'Your account has been blocked. Please contact administrator to restore login access.'
            ), 403);
        }

        $conditions = array();
        if ($userId > 0) {
            $conditions['OR'] = array('MyDeal.user_id' => $userId, 'MyDeal.client_id' => $userId);
        }
        if ($this->MyDeal->hasField('deal_status')) {
            $conditions['MyDeal.deal_status'] = array('Active', 'Blocked', 'active', 'blocked');
        }

        $deals = $this->MyDeal->find('all', array(
            'conditions' => $conditions,
            'contain' => array('Client', 'Property', 'User'),
            'order' => array('MyDeal.id' => 'desc'),
            'limit' => 50
        ));

        $dealIds = array();
        $cleanDeals = array();
        $dealsMap = array();
        foreach ($deals as $d) {
            if (!$this->_isDealVisibleToClient($d)) {
                continue;
            }
            $did = (int)$d['MyDeal']['id'];
            $dealIds[] = $did;

            $rawStatus = !empty($d['MyDeal']['deal_status']) ? trim($d['MyDeal']['deal_status']) : 'Active';
            $normalizedStatus = (strtolower($rawStatus) === 'blocked') ? 'Blocked' : 'Active';
            $d['MyDeal']['deal_status'] = $normalizedStatus;
            $d['MyDeal']['is_blocked'] = ($normalizedStatus === 'Blocked');

            $code = !empty($d['MyDeal']['code']) ? $d['MyDeal']['code'] : (!empty($d['Property']['uniq_id']) ? $d['Property']['uniq_id'] : 'MA'.$did);
            $propName = !empty($d['Property']['name']) ? $d['Property']['name'] : 'White Villas Property';

            $dealsMap[$did] = array(
                'deal_id' => $did,
                'code' => $code,
                'property_name' => $propName,
                'deal_status' => $normalizedStatus
            );

            $cleanDeals[$did] = $d;
        }

        if (!empty($dealIds)) {
            $paymentRows = $this->MyPayment->find('all', array(
                'fields' => array('MyPayment.deal_id', 'SUM(MyPayment.amount) AS paid_amount', 'COUNT(MyPayment.id) AS receipt_count'),
                'conditions' => array('MyPayment.deal_id' => $dealIds),
                'group' => array('MyPayment.deal_id'),
                'recursive' => -1
            ));
            foreach ($paymentRows as $pr) {
                $did = (int)$pr['MyPayment']['deal_id'];
                $paid = isset($pr[0]['paid_amount']) ? (float)$pr[0]['paid_amount'] : 0;
                $rcount = isset($pr[0]['receipt_count']) ? (int)$pr[0]['receipt_count'] : 0;
                if (isset($cleanDeals[$did])) {
                    $totalAmt = isset($cleanDeals[$did]['MyDeal']['total_amount']) ? (float)$cleanDeals[$did]['MyDeal']['total_amount'] : 7500000;
                    $cleanDeals[$did]['MyDeal']['paid_amount'] = $paid;
                    $cleanDeals[$did]['MyDeal']['balance_amount'] = max(0, $totalAmt - $paid);
                    $cleanDeals[$did]['MyDeal']['paid_percentage'] = round(($paid / max(1, $totalAmt)) * 100);
                    $cleanDeals[$did]['MyDeal']['receipts_count'] = $rcount;
                }
            }

            // Also compute overdue per deal
            $overdueData = $this->_calculateOverdueData($dealIds, $dealsMap, date('Y-m-d'));
            $overdueByDeal = array();
            $overdueCountByDeal = array();
            foreach ($overdueData['overdue_list'] as $ovItem) {
                $ovDid = (int)$ovItem['deal_id'];
                if (!isset($overdueByDeal[$ovDid])) {
                    $overdueByDeal[$ovDid] = 0;
                    $overdueCountByDeal[$ovDid] = 0;
                }
                $overdueByDeal[$ovDid] += (float)$ovItem['due_amount'];
                $overdueCountByDeal[$ovDid]++;
            }
            foreach ($cleanDeals as $did => $cDeal) {
                $cleanDeals[$did]['MyDeal']['overdue_amount'] = isset($overdueByDeal[$did]) ? $overdueByDeal[$did] : 0;
                $cleanDeals[$did]['MyDeal']['overdue_installments'] = isset($overdueCountByDeal[$did]) ? $overdueCountByDeal[$did] : 0;
            }
        }

        $resultList = array_values($cleanDeals);
        if (empty($resultList)) {
            $resultList = array();
        }

        return $this->_jsonResponse(array(
            'status' => true,
            'count' => count($resultList),
            'data' => $resultList
        ));
    }

    /**
     * GET /api/my_payments
     */
    public function my_payments()
    {
        $userId = !empty($this->request->query['client_id']) ? (int)$this->request->query['client_id'] : (!empty($this->request->query['user_id']) ? (int)$this->request->query['user_id'] : 0);
        
        if ($userId > 0 && $this->_isClientBlocked($userId)) {
            return $this->_jsonResponse(array(
                'status' => false,
                'message' => 'Your account has been blocked. Please contact administrator to restore login access.'
            ), 403);
        }

        $clientInfo = array();
        if ($userId > 0) {
            $clientRow = $this->Client->find('first', array(
                'conditions' => array('Client.id' => $userId),
                'recursive' => -1
            ));
            if (!empty($clientRow['Client'])) {
                $clientInfo = array(
                    'id' => $clientRow['Client']['id'],
                    'name' => !empty($clientRow['Client']['name']) ? $clientRow['Client']['name'] : 'Client',
                    'email' => !empty($clientRow['Client']['email']) ? $clientRow['Client']['email'] : '',
                    'phone' => !empty($clientRow['Client']['phone']) ? $clientRow['Client']['phone'] : ''
                );
            }
        }

        // Fetch all visible deals for this client
        $dealConditions = array();
        if ($userId > 0) {
            $dealConditions['OR'] = array(
                'MyDeal.client_id' => $userId,
                'MyDeal.user_id' => $userId
            );
        }
        if ($this->MyDeal->hasField('deal_status')) {
            $dealConditions['MyDeal.deal_status'] = array('Active', 'Blocked', 'active', 'blocked');
        }

        $dealRows = $this->MyDeal->find('all', array(
            'conditions' => $dealConditions,
            'contain' => array('Property'),
            'recursive' => 1
        ));

        $dealIds = array();
        $totalDealsAmount = 0;
        $dealsMap = array();

        foreach ($dealRows as $d) {
            if (!$this->_isDealVisibleToClient($d)) {
                continue;
            }
            $did = (int)$d['MyDeal']['id'];
            $dealIds[] = $did;
            $amt = isset($d['MyDeal']['total_amount']) ? (float)$d['MyDeal']['total_amount'] : 0;
            $totalDealsAmount += $amt;
            $code = !empty($d['MyDeal']['code']) ? $d['MyDeal']['code'] : (!empty($d['Property']['uniq_id']) ? $d['Property']['uniq_id'] : 'MA'.$did);
            $propName = !empty($d['Property']['name']) ? $d['Property']['name'] : 'White Villas Block A - 3 Marla Executive';

            $rawStatus = !empty($d['MyDeal']['deal_status']) ? trim($d['MyDeal']['deal_status']) : 'Active';
            $normalizedStatus = (strtolower($rawStatus) === 'blocked') ? 'Blocked' : 'Active';

            $dealsMap[$did] = array(
                'deal_id' => $did,
                'code' => $code,
                'property_name' => $propName,
                'deal_status' => $normalizedStatus,
                'is_blocked' => ($normalizedStatus === 'Blocked'),
                'total_amount' => $amt,
                'receipts' => array(),
                'total_paid' => 0
            );
        }

        $totalPaidAmount = 0;
        $totalReceiptsCount = 0;

        if (!empty($dealIds)) {
            $payments = $this->MyPayment->find('all', array(
                'conditions' => array('MyPayment.deal_id' => $dealIds),
                'order' => array('MyPayment.id' => 'DESC')
            ));

            foreach ($payments as $p) {
                $pay = $p['MyPayment'];
                $did = (int)$pay['deal_id'];
                $pamt = isset($pay['total_amount']) ? (float)$pay['total_amount'] : (isset($pay['amount']) ? (float)$pay['amount'] : 0);
                $totalPaidAmount += $pamt;
                $totalReceiptsCount++;

                $rawDate = !empty($pay['payment_date']) ? $pay['payment_date'] : (!empty($pay['paid_date']) ? $pay['paid_date'] : (!empty($pay['date']) ? $pay['date'] : (!empty($pay['created']) ? $pay['created'] : '')));
                $paidDateStr = (!empty($rawDate) && $rawDate !== '0000-00-00' && $rawDate !== '0000-00-00 00:00:00') ? date('d-M-Y', strtotime($rawDate)) : '-';
                $txRef = !empty($pay['remarks']) ? $pay['remarks'] : (!empty($pay['transaction_id']) ? $pay['transaction_id'] : (!empty($pay['cheque_no']) ? $pay['cheque_no'] : (!empty($pay['ref_no']) ? $pay['ref_no'] : 'TID#'.$pay['id'])));

                $formattedReceipt = array(
                    'id' => $pay['id'],
                    'receipt_no' => !empty($pay['receipt_no']) ? $pay['receipt_no'] : 'R-'.$pay['id'],
                    'paid_amount' => $pamt,
                    'installment_fee' => isset($pay['emi']) ? (float)$pay['emi'] : $pamt,
                    'late_fee' => isset($pay['late_fee']) ? (float)$pay['late_fee'] : 0,
                    'payment_name' => !empty($pay['name']) ? $pay['name'] : 'Installment Payment',
                    'paid_date' => $paidDateStr,
                    'payment_date' => !empty($pay['payment_date']) ? $pay['payment_date'] : $rawDate,
                    'payment_type' => !empty($pay['payment_mode']) ? $pay['payment_mode'] : 'Online Bank',
                    'transaction_ref' => $txRef
                );

                if (isset($dealsMap[$did])) {
                    $dealsMap[$did]['receipts'][] = $formattedReceipt;
                    $dealsMap[$did]['total_paid'] += $pamt;
                }
            }
        }

        if (empty($dealsMap)) {
            $dealsMap = array();
            $totalPaidAmount = 0;
            $totalDealsAmount = 0;
            $totalReceiptsCount = 0;
        }

        $overdueData = $this->_calculateOverdueData($dealIds, $dealsMap, date('Y-m-d'));
        $dueAmount = max(0, $totalDealsAmount - $totalPaidAmount);
        $totalSum = max(1, $totalDealsAmount);
        $progressPercentage = $totalDealsAmount > 0 ? round(($totalPaidAmount / $totalSum) * 100, 1) : 0;

        return $this->_jsonResponse(array(
            'status' => true,
            'client_info' => $clientInfo,
            'summary' => array(
                'visible_deals' => count($dealsMap),
                'paid_amount' => $totalPaidAmount,
                'receipts_count' => $totalReceiptsCount,
                'due_amount' => $dueAmount,
                'overdue_amount' => $overdueData['overdue_amount'],
                'overdue_installments' => $overdueData['overdue_count'],
                'progress_percentage' => $progressPercentage
            ),
            'deals_with_receipts' => array_values($dealsMap),
            'deals_with_payments' => array_values($dealsMap),
            'overdue_installments' => $overdueData['overdue_list']
        ));
    }

    /**
     * GET /api/agents
     */
    public function agents()
    {
        $this->loadModel('User');
        $this->loadModel('Property');

        $conditions = array();
        
        // 1. Status Active
        if ($this->User->hasField('status')) {
            $conditions['User.status'] = 'Active';
        }
        
        // 2. User group ID 2 (or 1) if ugroup_id exists
        if ($this->User->hasField('ugroup_id')) {
            $conditions['User.ugroup_id'] = array(1, 2);
        }
        
        // 3. Front view condition
        if ($this->User->hasField('front_view')) {
            $conditions['OR'] = array(
                array('User.front_view' => 'On'),
                array('User.front_view' => null),
                array('User.front_view' => '')
            );
        }

        // 4. Sort order matching AgentsController
        $order = array();
        if ($this->User->hasField('sort_id')) {
            $order = array(
                '(CASE WHEN User.sort_id > 0 THEN 0 ELSE 1 END)' => 'asc',
                'User.sort_id' => 'asc',
                'User.name' => 'asc',
                'User.id' => 'asc'
            );
        } elseif ($this->User->hasField('sort')) {
            $order['User.sort'] = 'asc';
            $order['User.name'] = 'asc';
        } else {
            $order['User.name'] = 'asc';
            $order['User.id'] = 'asc';
        }

        $agents = $this->User->find('all', array(
            'conditions' => $conditions,
            'order' => $order,
            'limit' => 50
        ));

        // Fallback if strict conditions returned empty
        if (empty($agents)) {
            $fallbackConditions = array();
            if ($this->User->hasField('status')) {
                $fallbackConditions['User.status'] = 'Active';
            }
            $agents = $this->User->find('all', array(
                'conditions' => $fallbackConditions,
                'order' => array('User.id' => 'asc'),
                'limit' => 50
            ));
        }

        $clean = array();
        foreach ($agents as $a) {
            $u = $a['User'];
            unset($u['password']);
            
            // Count published properties assigned to this agent
            $propCount = $this->Property->find('count', array(
                'conditions' => array(
                    'Property.user_id' => $u['id'],
                    'Property.published' => 'Yes'
                )
            ));
            if ($propCount == 0) {
                $propCount = $this->Property->find('count', array(
                    'conditions' => array('Property.user_id' => $u['id'])
                ));
            }

            $u['properties_count'] = $propCount;
            $clean[] = $u;
        }

        return $this->_jsonResponse(array(
            'status' => true,
            'count' => count($clean),
            'data' => $clean
        ));
    }

    /**
     * GET /api/news
     */
    public function news()
    {
        $posts = $this->NewsPost->find('all', array(
            'conditions' => array('NewsPost.status' => 'Published'),
            'contain' => array('NewsCategory'),
            'order' => array('NewsPost.id' => 'DESC'),
            'limit' => 15
        ));

        return $this->_jsonResponse(array(
            'status' => true,
            'data' => $posts
        ));
    }

    protected function _formatProperty($p, $isDetail = false)
    {
        $prop = $p['Property'];
        $prop['type_name'] = !empty($p['Type']['name']) ? $p['Type']['name'] : 'Property';
        $prop['contract_name'] = !empty($p['Contract']['name']) ? $p['Contract']['name'] : 'Sale';
        $prop['location_name'] = !empty($p['Location']['name']) ? $p['Location']['name'] : '';

        // Agent details
        if (!empty($p['User'])) {
            $prop['agent'] = array(
                'id' => $p['User']['id'],
                'name' => $p['User']['name'],
                'email' => $p['User']['email'],
                'phone' => !empty($p['User']['phone']) ? $p['User']['phone'] : (!empty($p['User']['mobile']) ? $p['User']['mobile'] : '')
            );
        } else {
            $prop['agent'] = null;
        }

        // Photos formatting
        $photos = array();
        if (!empty($p['PropertiesPhoto'])) {
            foreach ($p['PropertiesPhoto'] as $ph) {
                if (!empty($ph['photo'])) {
                    $photos[] = $ph['photo'];
                }
            }
        }
        if (empty($photos) && !empty($prop['photo'])) {
            $photos[] = $prop['photo'];
        }
        if (empty($photos) && !empty($prop['image'])) {
            $photos[] = $prop['image'];
        }

        $formattedPhotos = array();
        foreach ($photos as $phName) {
            $phClean = trim($phName);
            if (strpos($phClean, 'http://') === 0 || strpos($phClean, 'https://') === 0) {
                $formattedPhotos[] = $phClean;
            } else {
                $phClean = ltrim($phClean, '/');
                if (strpos($phClean, 'img/propertiesphotos/') === 0) {
                    $formattedPhotos[] = $phClean;
                } else {
                    $formattedPhotos[] = 'img/propertiesphotos/' . $phClean;
                }
            }
        }

        $prop['photos'] = $formattedPhotos;
        $prop['cover_photo'] = !empty($formattedPhotos) ? $formattedPhotos[0] : null;

        if ($isDetail) {
            $prop['documents'] = !empty($p['PropertiesDocument']) ? $p['PropertiesDocument'] : array();
            $prop['floorplans'] = !empty($p['PropertiesFloorplan']) ? $p['PropertiesFloorplan'] : array();
        }

        return $prop;
    }

    /**
     * GET /api/notifications
     */
    public function notifications()
    {
        $clientId = !empty($this->request->query['client_id']) ? (int)$this->request->query['client_id'] : (!empty($this->request->query['user_id']) ? (int)$this->request->query['user_id'] : 0);

        if ($clientId > 0 && $this->_isClientBlocked($clientId)) {
            return $this->_jsonResponse(array(
                'status' => false,
                'message' => 'Your account has been blocked. Please contact administrator to restore login access.'
            ), 403);
        }

        $notifications = array();

        if ($clientId > 0) {
            // Find existing records in client_notifications for this client
            $existingRecords = $this->ClientNotification->find('all', array(
                'conditions' => array('ClientNotification.client_id' => $clientId),
                'recursive' => -1
            ));

            $existingByKey = array();
            foreach ($existingRecords as $rec) {
                $k = !empty($rec['ClientNotification']['notif_key']) ? $rec['ClientNotification']['notif_key'] : (string)$rec['ClientNotification']['id'];
                $existingByKey[$k] = $rec['ClientNotification'];
            }

            // Find client deals strictly filtered to Active and Blocked only
            $conditions = array('OR' => array('MyDeal.user_id' => $clientId, 'MyDeal.client_id' => $clientId));
            if ($this->MyDeal->hasField('deal_status')) {
                $conditions['MyDeal.deal_status'] = array('Active', 'Blocked', 'active', 'blocked');
            }

            $deals = $this->MyDeal->find('all', array(
                'conditions' => $conditions,
                'contain' => array('Property'),
                'order' => array('MyDeal.id' => 'DESC')
            ));

            $dealIds = array();
            $dealsMap = array();
            foreach ($deals as $d) {
                if (!$this->_isDealVisibleToClient($d)) {
                    continue;
                }
                $did = (int)$d['MyDeal']['id'];
                $dealIds[] = $did;

                $code = !empty($d['MyDeal']['code']) ? $d['MyDeal']['code'] : (!empty($d['Property']['uniq_id']) ? $d['Property']['uniq_id'] : 'MA'.$did);
                $propName = !empty($d['Property']['name']) ? $d['Property']['name'] : 'White Villas Property';
                $rawStatus = !empty($d['MyDeal']['deal_status']) ? trim($d['MyDeal']['deal_status']) : 'Active';
                $normalizedStatus = (strtolower($rawStatus) === 'blocked') ? 'Blocked' : 'Active';

                $dealsMap[$did] = array(
                    'deal_id' => $did,
                    'code' => $code,
                    'property_name' => $propName,
                    'deal_status' => $normalizedStatus
                );
            }

            $fallbackId = 1000;

            // 1. Generate Payment Overdue Notifications
            if (!empty($dealIds)) {
                $overdueData = $this->_calculateOverdueData($dealIds, $dealsMap, date('Y-m-d'));
                foreach ($overdueData['overdue_list'] as $ov) {
                    $notifKey = 'overdue_' . $ov['id'];
                    $realPropName = !empty($ov['property_name']) && $ov['property_name'] !== 'White Villas Property' ? $ov['property_name'] : $ov['deal_code'];
                    $title = 'Payment Overdue Alert - ' . $realPropName;
                    $msg = $ov['installment_name'] . ' of Rs ' . number_format($ov['due_amount']) . ' for ' . $realPropName . ' was due on ' . $ov['due_date'] . '. Please review and clear your overdue balance.';
                    $created = !empty($ov['raw_due_date']) ? $ov['raw_due_date'] . ' 09:00:00' : date('Y-m-d H:i:s');

                    $isRead = false;
                    $dbId = 0;

                    if (isset($existingByKey[$notifKey])) {
                        $isRead = !empty($existingByKey[$notifKey]['is_read']);
                        $dbId = (int)$existingByKey[$notifKey]['id'];
                    } else {
                        try {
                            $this->ClientNotification->create();
                            $saved = $this->ClientNotification->save(array(
                                'client_id' => $clientId,
                                'notif_key' => $notifKey,
                                'title' => $title,
                                'type' => 'payment_overdue',
                                'message' => $msg,
                                'is_read' => 0,
                                'created' => $created
                            ));
                            if ($saved && !empty($this->ClientNotification->id)) {
                                $dbId = (int)$this->ClientNotification->id;
                                $existingByKey[$notifKey] = array('id' => $dbId, 'is_read' => 0);
                            }
                        } catch (Exception $e) {}
                    }

                    if ($dbId <= 0) {
                        $dbId = $fallbackId++;
                    }

                    $notifications[] = array(
                        'id' => $dbId,
                        'key' => $notifKey,
                        'type' => 'payment_overdue',
                        'title' => $title,
                        'message' => $msg,
                        'created' => $created,
                        'is_read' => $isRead,
                        'deal_code' => $ov['deal_code'],
                        'deal_id' => $ov['deal_id'],
                        'due_amount' => $ov['due_amount'],
                        'due_date' => $ov['due_date'],
                        'installment_name' => $ov['installment_name'],
                        'property_name' => $realPropName
                    );
                }

                // 2. Generate Payment Receipts Notifications
                $payments = $this->MyPayment->find('all', array(
                    'conditions' => array('MyPayment.deal_id' => $dealIds),
                    'order' => array('MyPayment.id' => 'DESC'),
                    'limit' => 15
                ));

                foreach ($payments as $p) {
                    $pay = $p['MyPayment'];
                    $did = (int)$pay['deal_id'];
                    $notifKey = 'receipt_' . $pay['id'];
                    $recNo = !empty($pay['receipt_no']) ? $pay['receipt_no'] : 'R-'.$pay['id'];
                    $amt = isset($pay['total_amount']) ? (float)$pay['total_amount'] : (isset($pay['amount']) ? (float)$pay['amount'] : 0);
                    $amtFormatted = 'Rs ' . number_format($amt, 0);
                    $payName = !empty($pay['name']) ? $pay['name'] : 'Installment Payment';
                    $dealCode = isset($dealsMap[$did]['code']) ? $dealsMap[$did]['code'] : 'WV-DEAL';
                    $propName = isset($dealsMap[$did]['property_name']) ? $dealsMap[$did]['property_name'] : 'White Villas Property';
                    $realPropName = !empty($propName) && $propName !== 'White Villas Property' ? $propName : $dealCode;
                    $actualPaidDate = !empty($pay['payment_date']) ? $pay['payment_date'] : (!empty($pay['paid_date']) ? $pay['paid_date'] : (!empty($pay['date']) ? $pay['date'] : (!empty($pay['trans_date']) ? $pay['trans_date'] : (!empty($pay['created']) ? $pay['created'] : ''))));
                    $created = !empty($actualPaidDate) ? (strpos($actualPaidDate, ':') !== false ? $actualPaidDate : $actualPaidDate . ' 12:00:00') : (!empty($pay['created']) ? $pay['created'] : date('Y-m-d H:i:s'));
                    $paidDateFormatted = (!empty($actualPaidDate) && $actualPaidDate !== '0000-00-00') ? date('d-M-Y', strtotime($actualPaidDate)) : '';
                    $datePhrase = !empty($paidDateFormatted) ? ' paid on ' . $paidDateFormatted : '';
                    $title = 'Payment Receipt Issued - ' . $realPropName . ' (' . $recNo . ')';
                    $msg = $payName . ' of ' . $amtFormatted . ' for ' . $realPropName . $datePhrase . ' has been verified and recorded.';

                    $isRead = false;
                    $dbId = 0;

                    if (isset($existingByKey[$notifKey])) {
                        $isRead = !empty($existingByKey[$notifKey]['is_read']);
                        $dbId = (int)$existingByKey[$notifKey]['id'];
                        if (!empty($actualPaidDate) && !empty($existingByKey[$notifKey]['created'])) {
                            $currDate = date('Y-m-d', strtotime($existingByKey[$notifKey]['created']));
                            $realDateOnly = date('Y-m-d', strtotime($created));
                            if ($currDate !== $realDateOnly) {
                                try {
                                    $this->ClientNotification->id = $dbId;
                                    $this->ClientNotification->saveField('created', $created);
                                } catch (Exception $e) {}
                            }
                        }
                    } else {
                        try {
                            $this->ClientNotification->create();
                            $saved = $this->ClientNotification->save(array(
                                'client_id' => $clientId,
                                'notif_key' => $notifKey,
                                'title' => $title,
                                'type' => 'payment_received',
                                'message' => $msg,
                                'is_read' => 0,
                                'created' => $created
                            ));
                            if ($saved && !empty($this->ClientNotification->id)) {
                                $dbId = (int)$this->ClientNotification->id;
                                $existingByKey[$notifKey] = array('id' => $dbId, 'is_read' => 0);
                            }
                        } catch (Exception $e) {}
                    }

                    if ($dbId <= 0) {
                        $dbId = $fallbackId++;
                    }

                    $notifications[] = array(
                        'id' => $dbId,
                        'key' => $notifKey,
                        'type' => 'payment_received',
                        'title' => $title,
                        'message' => $msg,
                        'created' => $created,
                        'paid_date' => !empty($actualPaidDate) ? $actualPaidDate : $created,
                        'is_read' => $isRead,
                        'receipt_no' => $recNo,
                        'deal_code' => $dealCode,
                        'deal_id' => $did,
                        'amount' => $amt,
                        'property_name' => $realPropName
                    );
                }
            }


            // Clean up any obsolete overdue records in DB where the installment is now fully paid
            $activeOverdueKeys = array();
            if (!empty($overdueData['overdue_list'])) {
                foreach ($overdueData['overdue_list'] as $ov) {
                    $activeOverdueKeys['overdue_' . $ov['id']] = true;
                }
            }
            foreach ($existingRecords as $rec) {
                $cn = $rec['ClientNotification'];
                $key = !empty($cn['notif_key']) ? $cn['notif_key'] : '';
                if (!empty($key) && strpos($key, 'overdue_') === 0 && !isset($activeOverdueKeys[$key])) {
                    try {
                        $this->ClientNotification->delete($cn['id']);
                    } catch (Exception $e) {}
                }
            }

            // 3. Include any custom admin / system notices from client_notifications
            foreach ($existingRecords as $rec) {
                $cn = $rec['ClientNotification'];
                $key = !empty($cn['notif_key']) ? $cn['notif_key'] : '';
                // Skip auto-generated items or stale overdue items
                if (!empty($key) && (strpos($key, 'overdue_') === 0 || strpos($key, 'receipt_') === 0)) {
                    continue;
                }
                $notifications[] = array(
                    'id' => (int)$cn['id'],
                    'key' => !empty($cn['notif_key']) ? $cn['notif_key'] : 'notice_' . $cn['id'],
                    'type' => !empty($cn['type']) ? $cn['type'] : 'admin_notice',
                    'title' => !empty($cn['title']) ? $cn['title'] : 'Notification Alert',
                    'message' => !empty($cn['message']) ? $cn['message'] : '',
                    'created' => !empty($cn['created']) ? $cn['created'] : date('Y-m-d H:i:s'),
                    'is_read' => !empty($cn['is_read']),
                );
            }
        }

        // Welcome system notification if empty
        if (empty($notifications)) {
            $notifications[] = array(
                'id' => 101,
                'key' => 'welcome_' . $clientId,
                'type' => 'welcome',
                'title' => 'Welcome to White Villas Portal',
                'message' => 'Your client account is active. All payment receipts, installment schedules, and official notices will appear here.',
                'created' => date('Y-m-d H:i:s'),
                'is_read' => false
            );
        }

        // Sort notifications: newest/latest at top (descending by timestamp)
        usort($notifications, function($a, $b) {
            $tA = !empty($a['created']) ? strtotime($a['created']) : 0;
            $tB = !empty($b['created']) ? strtotime($b['created']) : 0;
            return $tB - $tA;
        });

        $unreadCount = count(array_filter($notifications, function($n) { return empty($n['is_read']); }));

        return $this->_jsonResponse(array(
            'status' => true,
            'count' => count($notifications),
            'unread_count' => $unreadCount,
            'notifications' => $notifications,
            'data' => $notifications
        ));
    }

    /**
     * POST /api/notifications_read
     */
    public function notifications_read()
    {
        $input = $this->request->input('json_decode', true);
        if (empty($input)) {
            $input = $this->request->data;
        }

        $clientId = !empty($input['client_id']) ? (int)$input['client_id'] : (!empty($input['user_id']) ? (int)$input['user_id'] : (!empty($this->request->query['client_id']) ? (int)$this->request->query['client_id'] : 0));

        $id = !empty($input['id']) ? $input['id'] : (!empty($this->request->query['id']) ? $this->request->query['id'] : null);
        $key = !empty($input['key']) ? $input['key'] : (!empty($this->request->query['key']) ? $this->request->query['key'] : null);

        if ($clientId > 0) {
            // Overdue reminders cannot be marked read or dismissed manually; they only disappear once paid
            $cond = array(
                'ClientNotification.client_id' => $clientId,
                'ClientNotification.type !=' => 'payment_overdue',
                'ClientNotification.notif_key NOT LIKE' => 'overdue_%'
            );
            if (!empty($id) || !empty($key)) {
                $targetId = !empty($id) ? $id : $key;
                $cond['AND']['OR'] = array(
                    'ClientNotification.id' => $targetId,
                    'ClientNotification.notif_key' => $targetId
                );
                if (!empty($key)) {
                    $cond['AND']['OR']['ClientNotification.notif_key'] = $key;
                }
            }
            $this->ClientNotification->updateAll(
                array('ClientNotification.is_read' => 1),
                $cond
            );
        }


        return $this->_jsonResponse(array(
            'status' => true,
            'message' => 'Notifications marked as read.'
        ));
    }

    /**
     * GET /api/app_config
     * GET /api/check_update
     */
    public function app_config()
    {
        $setting = $this->AppSetting->find('first', array('conditions' => array('AppSetting.id' => 1)));
        $app = !empty($setting['AppSetting']) ? $setting['AppSetting'] : array();

        $defaultApkUrl = Router::url('/downloads/whitevillas-latest.apk', true);
        $apkUrl = !empty($app['apk_url']) ? $app['apk_url'] : $defaultApkUrl;

        $latestVersion = !empty($app['current_version']) ? $app['current_version'] : '1.0.0';
        $minVersion = !empty($app['min_version']) ? $app['min_version'] : '1.0.0';

        // Check if client supplied their version
        $clientVersion = '';
        if (!empty($this->request->query['version'])) {
            $clientVersion = trim($this->request->query['version']);
        } elseif (!empty($this->request->query['current_version'])) {
            $clientVersion = trim($this->request->query['current_version']);
        } elseif (!empty($this->request->query['version_code'])) {
            $clientVersion = trim($this->request->query['version_code']);
        } elseif (!empty($this->request->data['version'])) {
            $clientVersion = trim($this->request->data['version']);
        }

        $updateAvailable = false;
        if (!empty($clientVersion)) {
            // Compare versions: true if client version is lower than latest server version
            if (version_compare($clientVersion, $latestVersion, '<')) {
                $updateAvailable = true;
            }
        } else {
            $updateAvailable = true;
        }

        return $this->_jsonResponse(array(
            'status' => true,
            'update_available' => $updateAvailable,
            'show_update_dialog' => $updateAvailable,
            'app_name' => !empty($app['app_name']) ? $app['app_name'] : 'White Villas Mobile App',
            'package_name' => !empty($app['package_name']) ? $app['package_name'] : 'com.whitevillas.app',
            'latest_version' => $latestVersion,
            'current_version' => $latestVersion,
            'min_version' => $minVersion,
            'force_update' => !empty($app['force_update']),
            'apk_url' => $apkUrl,
            'playstore_url' => !empty($app['playstore_url']) ? $app['playstore_url'] : '',
            'appstore_url' => !empty($app['appstore_url']) ? $app['appstore_url'] : '',
            'release_notes' => !empty($app['release_notes']) ? $app['release_notes'] : 'Latest bug fixes and performance improvements.',
            'maintenance_mode' => !empty($app['maintenance_mode']),
            'maintenance_title' => !empty($app['maintenance_title']) ? $app['maintenance_title'] : 'System Under Maintenance',
            'maintenance_message' => !empty($app['maintenance_message']) ? $app['maintenance_message'] : 'Our mobile application services are currently undergoing maintenance. Please check back shortly.',
            'support_phone' => !empty($app['support_phone']) ? $app['support_phone'] : '+92 300 1234567',
            'support_whatsapp' => !empty($app['support_whatsapp']) ? $app['support_whatsapp'] : '+92 300 1234567',
            'support_email' => !empty($app['support_email']) ? $app['support_email'] : 'support@whitevillas.com'
        ));
    }

    /**
     * GET /api/statement
     */
    public function statement()
    {
        $dealId = !empty($this->request->query['deal_id']) ? (int)$this->request->query['deal_id'] : (!empty($this->request->query['id']) ? (int)$this->request->query['id'] : 0);
        $clientId = !empty($this->request->query['client_id']) ? (int)$this->request->query['client_id'] : (!empty($this->request->query['user_id']) ? (int)$this->request->query['user_id'] : 0);

        if ($dealId <= 0) {
            return $this->_jsonResponse(array('status' => false, 'message' => 'Deal ID is required.'), 400);
        }

        $deal = $this->MyDeal->find('first', array(
            'joins' => array(
                array('table' => 'clients', 'alias' => 'Client', 'type' => 'LEFT', 'conditions' => array('MyDeal.client_id=Client.id')),
                array('table' => 'properties', 'alias' => 'Property', 'type' => 'LEFT', 'conditions' => array('MyDeal.property_id=Property.id')),
                array('table' => 'projects', 'alias' => 'Project', 'type' => 'LEFT', 'conditions' => array('Property.project_id=Project.id')),
                array('table' => 'units', 'alias' => 'Unit', 'type' => 'LEFT', 'conditions' => array('Property.unit_id=Unit.id')),
                array('table' => 'plans', 'alias' => 'Plan', 'type' => 'LEFT', 'conditions' => array('MyDeal.plan_id=Plan.id'))
            ),
            'fields' => array('MyDeal.*', 'Client.*', 'Property.*', 'Project.*', 'Unit.*', 'Plan.*'),
            'conditions' => array('MyDeal.id' => $dealId),
            'recursive' => -1
        ));

        if (empty($deal['MyDeal']['id'])) {
            return $this->_jsonResponse(array('status' => false, 'message' => 'Deal record not found.'), 404);
        }

        $salt = Configure::read('Security.salt');
        $cId = !empty($deal['Client']['id']) ? (int)$deal['Client']['id'] : $clientId;
        $inv = !empty($deal['MyDeal']['invoice_no']) ? (string)$deal['MyDeal']['invoice_no'] : '';
        $tot = isset($deal['MyDeal']['total_amount']) ? (float)$deal['MyDeal']['total_amount'] : 0;
        $token = substr(hash('sha256', "WV_STATEMENT_{$dealId}_{$cId}_{$inv}_{$tot}_{$salt}"), 0, 32);

        $statementUrl = Router::url(array(
            'controller' => 'my_payments',
            'action' => 'statement',
            $dealId,
            '?' => array('token' => $token)
        ), true);

        return $this->_jsonResponse(array(
            'status' => true,
            'deal_id' => $dealId,
            'deal_code' => !empty($deal['MyDeal']['invoice_no']) ? $deal['MyDeal']['invoice_no'] : (!empty($deal['MyDeal']['code']) ? $deal['MyDeal']['code'] : 'WV-DEAL-'.$dealId),
            'client_name' => !empty($deal['Client']['name']) ? $deal['Client']['name'] : 'Valued Client',
            'property_name' => !empty($deal['Property']['name']) ? $deal['Property']['name'] : 'White Villas Property',
            'statement_url' => $statementUrl,
            'token' => $token,
            'total_amount' => $tot
        ));
    }

    public function check_update()
    {
        return $this->app_config();
    }

    protected function _markClientAppLogin($clientId)
    {
        $clientId = (int)$clientId;
        if ($clientId <= 0) return;
        try {
            if ($this->Client->hasField('app_login_status')) {
                $this->Client->updateAll(
                    array(
                        'Client.app_login_status' => 1,
                        'Client.app_last_login' => "'" . date('Y-m-d H:i:s') . "'"
                    ),
                    array('Client.id' => $clientId)
                );
            }
        } catch (Exception $e) {}
    }

    protected function _isClientBlocked($clientId)
    {
        $clientId = (int)$clientId;
        if ($clientId <= 0) {
            return false;
        }
        try {
            $client = $this->Client->find('first', array(
                'conditions' => array('Client.id' => $clientId),
                'fields' => array('Client.id', 'Client.status'),
                'recursive' => -1
            ));
            if (!empty($client) && !empty($client['Client']['status']) && strcasecmp(trim((string)$client['Client']['status']), 'Active') !== 0) {
                if ($this->Client->hasField('app_login_status')) {
                    $this->Client->updateAll(
                        array('Client.app_login_status' => 0),
                        array('Client.id' => $clientId)
                    );
                }
                return true;
            }
        } catch (Exception $e) {}
        return false;
    }
}


