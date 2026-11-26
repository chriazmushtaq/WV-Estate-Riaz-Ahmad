<?php
App::uses('AppController', 'Controller');
App::uses('CakeTime', 'Utility');

class ClientNotificationsController extends AppController {
    public $helpers = array('Html', 'Form', 'Session', 'Time');
    public $uses = array('ClientNotification', 'MyDeal', 'MyPayment', 'Admin.Realestateletter', 'Admin.BallotingAllotment', 'Admin.ProjectNotificationSetting');

    public function beforeFilter() {
        parent::beforeFilter();
        $this->layout = 'default';
        $this->authenticate();
        if (empty($this->userValue['Client']['id'])) {
            $this->Session->setFlash(__('Please login to view notifications.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('controller' => 'Clients', 'action' => 'login'));
        }
        $this->userId = (int)$this->userValue['Client']['id'];
    }

    public function index() {
        $this->_syncClientNotifications();

        $type = isset($this->request->query['type']) ? trim((string)$this->request->query['type']) : '';
        $conditions = array('ClientNotification.client_id' => $this->userId);

        if ($type !== '' && $type !== 'all') {
            if ($type === 'payments') {
                $conditions['ClientNotification.type'] = array('payment_received', 'payment_overdue');
            } elseif ($type === 'letters') {
                $conditions['ClientNotification.type'] = 'letter_issued';
            } elseif ($type === 'balloting') {
                $conditions['ClientNotification.type'] = 'balloting_allotment';
            } elseif ($type === 'reminders') {
                $conditions['ClientNotification.type'] = 'installment_reminder';
            } else {
                $conditions['ClientNotification.type'] = $type;
            }
        }

        $notifications = $this->ClientNotification->find('all', array(
            'conditions' => $conditions,
            'order' => array('ClientNotification.id' => 'DESC'),
            'limit' => 50,
            'recursive' => -1
        ));

        $unreadCount = $this->ClientNotification->find('count', array(
            'conditions' => array('ClientNotification.client_id' => $this->userId, 'ClientNotification.is_read' => 0),
            'recursive' => -1
        ));

        $totalCount = $this->ClientNotification->find('count', array(
            'conditions' => array('ClientNotification.client_id' => $this->userId),
            'recursive' => -1
        ));

        $this->set(compact('notifications', 'unreadCount', 'totalCount', 'type'));
    }

    public function poll() {
        $this->autoRender = false;
        $this->response->type('json');

        $unreadCount = $this->ClientNotification->find('count', array(
            'conditions' => array('ClientNotification.client_id' => $this->userId, 'ClientNotification.is_read' => 0),
            'recursive' => -1
        ));

        $latest = $this->ClientNotification->find('all', array(
            'conditions' => array('ClientNotification.client_id' => $this->userId),
            'order' => array('ClientNotification.id' => 'DESC'),
            'limit' => 6,
            'recursive' => -1
        ));

        $items = array();
        foreach ($latest as $row) {
            $n = $row['ClientNotification'];
            $icon = 'fa-bell';
            $badgeColor = '#3b82f6';
            if ($n['type'] === 'payment_received') {
                $icon = 'fa-check-circle';
                $badgeColor = '#10b981';
            } elseif ($n['type'] === 'payment_overdue') {
                $icon = 'fa-exclamation-triangle';
                $badgeColor = '#ef4444';
            } elseif ($n['type'] === 'letter_issued') {
                $icon = 'fa-file-text-o';
                $badgeColor = '#8b5cf6';
            } elseif ($n['type'] === 'balloting_allotment') {
                $icon = 'fa-cube';
                $badgeColor = '#f59e0b';
            } elseif ($n['type'] === 'installment_reminder') {
                $icon = 'fa-calendar';
                $badgeColor = '#06b6d4';
            }

            $items[] = array(
                'id' => (int)$n['id'],
                'title' => $n['title'],
                'message' => $n['message'],
                'type' => $n['type'],
                'is_read' => (int)$n['is_read'],
                'action_url' => !empty($n['action_url']) ? $n['action_url'] : Router::url(array('controller' => 'ClientNotifications', 'action' => 'index')),
                'time_ago' => CakeTime::timeAgoInWords($n['created']),
                'icon' => $icon,
                'badge_color' => $badgeColor
            );
        }

        echo json_encode(array(
            'status' => 'success',
            'unread_count' => (int)$unreadCount,
            'items' => $items
        ));
        return;
    }

    public function mark_read($id = null) {
        $this->autoRender = false;
        $id = (int)$id;
        if ($id > 0) {
            $this->ClientNotification->updateAll(
                array('ClientNotification.is_read' => 1),
                array('ClientNotification.id' => $id, 'ClientNotification.client_id' => $this->userId)
            );
        }
        if ($this->request->is('ajax')) {
            $this->response->type('json');
            echo json_encode(array('success' => true));
            return;
        }
        return $this->redirect(array('action' => 'index'));
    }

    public function mark_all_read() {
        $this->autoRender = false;
        $this->ClientNotification->updateAll(
            array('ClientNotification.is_read' => 1),
            array('ClientNotification.client_id' => $this->userId)
        );
        if ($this->request->is('ajax')) {
            $this->response->type('json');
            echo json_encode(array('success' => true));
            return;
        }
        $this->Session->setFlash(__('All notifications marked as read.'), 'flash', array('alert' => 'success'));
        return $this->redirect(array('action' => 'index'));
    }

    protected function _syncClientNotifications() {
        // Sync deals, receipts, letters, and balloting allotments
        $deals = $this->MyDeal->find('all', array(
            'joins' => array(
                array('table' => 'properties', 'alias' => 'Property', 'type' => 'LEFT', 'conditions' => array('MyDeal.property_id=Property.id')),
                array('table' => 'projects', 'alias' => 'Project', 'type' => 'LEFT', 'conditions' => array('Property.project_id=Project.id'))
            ),
            'fields' => array('MyDeal.*', 'Property.*', 'Project.*'),
            'conditions' => array('MyDeal.client_id' => $this->userId),
            'recursive' => -1
        ));

        $dealIds = array();
        $dealsMap = array();
        foreach ($deals as $d) {
            $did = (int)$d['MyDeal']['id'];
            $dealIds[] = $did;
            $dealsMap[$did] = array(
                'property_name' => !empty($d['Property']['name']) ? $d['Property']['name'] : ('Deal #' . $did),
                'project_name' => !empty($d['Project']['name']) ? $d['Project']['name'] : 'White Villas'
            );
        }

        if (empty($dealIds)) {
            return;
        }

        // 1. Sync Receipts into notifications
        $payments = $this->MyPayment->find('all', array(
            'conditions' => array('MyPayment.deal_id' => $dealIds),
            'order' => array('MyPayment.id' => 'DESC'),
            'limit' => 15,
            'recursive' => -1
        ));
        foreach ($payments as $p) {
            $pid = (int)$p['MyPayment']['id'];
            $did = (int)$p['MyPayment']['deal_id'];
            $notifKey = 'receipt_' . $pid;
            $exists = $this->ClientNotification->find('first', array(
                'conditions' => array('ClientNotification.client_id' => $this->userId, 'ClientNotification.notif_key' => $notifKey),
                'recursive' => -1
            ));
            if (empty($exists)) {
                $propName = isset($dealsMap[$did]['property_name']) ? $dealsMap[$did]['property_name'] : 'Plot/Villa';
                $recNo = !empty($p['MyPayment']['receipt_no']) ? $p['MyPayment']['receipt_no'] : 'R-' . $pid;
                $amt = isset($p['MyPayment']['total_amount']) ? (float)$p['MyPayment']['total_amount'] : (isset($p['MyPayment']['amount']) ? (float)$p['MyPayment']['amount'] : 0);
                $paidDate = !empty($p['MyPayment']['payment_date']) ? date('d-M-Y', strtotime($p['MyPayment']['payment_date'])) : date('d-M-Y');
                $this->ClientNotification->create();
                $this->ClientNotification->save(array('ClientNotification' => array(
                    'client_id' => $this->userId,
                    'notif_key' => $notifKey,
                    'title' => 'Payment Receipt Issued (' . $recNo . ')',
                    'type' => 'payment_received',
                    'message' => 'Payment of Rs ' . number_format($amt) . ' for ' . $propName . ' verified on ' . $paidDate . '.',
                    'is_read' => 0,
                    'action_route' => 'MyPayments/printreceipt/' . $pid,
                    'action_url' => Router::url(array('controller' => 'MyPayments', 'action' => 'printreceipt', $pid), true),
                    'created' => !empty($p['MyPayment']['payment_date']) ? $p['MyPayment']['payment_date'] . ' 12:00:00' : date('Y-m-d H:i:s')
                )));
            }
        }

        // 2. Sync Letters into notifications
        $letters = $this->Realestateletter->find('all', array(
            'conditions' => array('Realestateletter.deal_id' => $dealIds, 'Realestateletter.status' => 'Published'),
            'order' => array('Realestateletter.id' => 'DESC'),
            'limit' => 15,
            'recursive' => -1
        ));
        foreach ($letters as $l) {
            $lid = (int)$l['Realestateletter']['id'];
            $did = (int)$l['Realestateletter']['deal_id'];
            $notifKey = 'letter_' . $lid;
            $exists = $this->ClientNotification->find('first', array(
                'conditions' => array('ClientNotification.client_id' => $this->userId, 'ClientNotification.notif_key' => $notifKey),
                'recursive' => -1
            ));
            if (empty($exists)) {
                $propName = isset($dealsMap[$did]['property_name']) ? $dealsMap[$did]['property_name'] : 'Plot/Villa';
                $lType = !empty($l['Realestateletter']['letter_type']) ? $l['Realestateletter']['letter_type'] : 'Official Letter';
                $refNo = !empty($l['Realestateletter']['reference_no']) ? $l['Realestateletter']['reference_no'] : 'REF-' . $lid;
                $this->ClientNotification->create();
                $this->ClientNotification->save(array('ClientNotification' => array(
                    'client_id' => $this->userId,
                    'notif_key' => $notifKey,
                    'title' => 'Official Letter Issued: ' . $lType,
                    'type' => 'letter_issued',
                    'message' => 'Your ' . $lType . ' (Ref: ' . $refNo . ') for ' . $propName . ' has been published.',
                    'is_read' => 0,
                    'action_route' => 'MyDeals/letterview/' . $lid,
                    'action_url' => Router::url(array('controller' => 'MyDeals', 'action' => 'letterview', $lid), true),
                    'created' => !empty($l['Realestateletter']['created']) ? $l['Realestateletter']['created'] : date('Y-m-d H:i:s')
                )));
            }
        }

        // 3. Sync Balloting Allotments into notifications
        $allotments = $this->BallotingAllotment->find('all', array(
            'conditions' => array('BallotingAllotment.client_id' => $this->userId, 'BallotingAllotment.status' => 'Allotted'),
            'order' => array('BallotingAllotment.id' => 'DESC'),
            'recursive' => -1
        ));
        foreach ($allotments as $a) {
            $aid = (int)$a['BallotingAllotment']['id'];
            $notifKey = 'allotment_' . $aid;
            $exists = $this->ClientNotification->find('first', array(
                'conditions' => array('ClientNotification.client_id' => $this->userId, 'ClientNotification.notif_key' => $notifKey),
                'recursive' => -1
            ));
            if (empty($exists)) {
                $plotNo = !empty($a['BallotingAllotment']['property_no']) ? $a['BallotingAllotment']['property_no'] : 'Assigned';
                $block = !empty($a['BallotingAllotment']['block_name']) ? $a['BallotingAllotment']['block_name'] : 'Main Block';
                $allotNo = !empty($a['BallotingAllotment']['allotment_no']) ? $a['BallotingAllotment']['allotment_no'] : ('WV-' . $aid);
                $this->ClientNotification->create();
                $this->ClientNotification->save(array('ClientNotification' => array(
                    'client_id' => $this->userId,
                    'notif_key' => $notifKey,
                    'title' => 'e-Balloting Plot Allotted: Unit ' . $plotNo,
                    'type' => 'balloting_allotment',
                    'message' => 'Congratulations! Plot/Unit ' . $plotNo . ' in ' . $block . ' (Allotment #' . $allotNo . ') has been allotted to you.',
                    'is_read' => 0,
                    'action_route' => 'BallotingResults/certificate/' . $aid,
                    'action_url' => Router::url(array('controller' => 'BallotingResults', 'action' => 'certificate', $aid), true),
                    'created' => !empty($a['BallotingAllotment']['created']) ? $a['BallotingAllotment']['created'] : date('Y-m-d H:i:s')
                )));
            }
        }
    }
}
