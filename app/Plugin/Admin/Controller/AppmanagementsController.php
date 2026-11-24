<?php
App::uses('AdminAppController', 'Admin.Controller');

class AppmanagementsController extends AdminAppController {
    public $helpers = array('Html', 'Form', 'Session', 'Paginator');
    public $components = array('Session', 'search-master.Prg', 'Paginator');
    public $uses = array(
        'AppSetting',
        'AppNotificationBroadcast',
        'ClientNotification',
        'Client',
        'MyDeal',
        'PlansPayment',
        'WebsiteContact'
    );

    /**
     * Dashboard Overview
     */
    public function index()
    {
        $this->set('title_for_layout', __('Mobile App Management'));

        // Load App Settings
        $setting = $this->AppSetting->find('first', array('conditions' => array('AppSetting.id' => 1)));
        if (empty($setting)) {
            $this->AppSetting->create();
            $this->AppSetting->save(array(
                'id' => 1,
                'app_name' => 'White Villas Mobile App',
                'package_name' => 'com.whitevillas.app',
                'current_version' => '1.0.0',
                'min_version' => '1.0.0',
                'force_update' => 0,
                'apk_url' => Router::url('/downloads/whitevillas-latest.apk', true),
                'maintenance_mode' => 0,
                'maintenance_title' => 'System Under Maintenance',
                'maintenance_message' => 'Our mobile services are currently undergoing maintenance. Please check back shortly.',
                'support_phone' => '+92 300 1234567',
                'support_whatsapp' => '+92 300 1234567',
                'support_email' => 'support@whitevillas.com',
                'updated' => date('Y-m-d H:i:s')
            ));
            $setting = $this->AppSetting->find('first', array('conditions' => array('AppSetting.id' => 1)));
        }

        // Metrics
        $totalClients = $this->Client->find('count');
        $appLoginClients = 0;
        if ($this->Client->hasField('app_login_status')) {
            $appLoginClients = $this->Client->find('count', array('conditions' => array('Client.app_login_status' => 1)));
        }
        $totalBroadcasts = $this->AppNotificationBroadcast->find('count');
        $totalInAppDelivered = $this->ClientNotification->find('count');

        // Recent Broadcasts
        $recentBroadcasts = $this->AppNotificationBroadcast->find('all', array(
            'order' => array('AppNotificationBroadcast.id' => 'DESC'),
            'limit' => 5
        ));

        // Recent App Client Logins
        $recentAppClients = array();
        if ($this->Client->hasField('app_last_login')) {
            $recentAppClients = $this->Client->find('all', array(
                'conditions' => array('Client.app_last_login IS NOT NULL'),
                'order' => array('Client.app_last_login' => 'DESC'),
                'limit' => 8
            ));
        }

        $this->set(compact(
            'setting',
            'totalClients',
            'appLoginClients',
            'totalBroadcasts',
            'totalInAppDelivered',
            'recentBroadcasts',
            'recentAppClients'
        ));
    }

    /**
     * Version Control & Server APK Updates
     */
    public function version_control()
    {
        $this->set('title_for_layout', __('App Version & APK Management'));

        $setting = $this->AppSetting->find('first', array('conditions' => array('AppSetting.id' => 1)));
        $id = !empty($setting['AppSetting']['id']) ? (int)$setting['AppSetting']['id'] : 1;

        if ($this->request->is(array('post', 'put'))) {
            $saveData = $this->request->data['AppSetting'];
            $saveData['id'] = $id;
            $saveData['updated'] = date('Y-m-d H:i:s');

            // Handle APK File Upload
            if (!empty($_FILES['apk_file']['name']) && $_FILES['apk_file']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['apk_file'];
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                if ($ext === 'apk' || $ext === 'zip') {
                    $uploadDir = WWW_ROOT . 'downloads' . DS;
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    $verClean = preg_replace('/[^0-9\.]/', '', !empty($saveData['current_version']) ? $saveData['current_version'] : '1.0.0');
                    $newFileName = 'whitevillas-v' . $verClean . '.apk';
                    $targetPath = $uploadDir . $newFileName;
                    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                        // Also copy as whitevillas-latest.apk
                        @copy($targetPath, $uploadDir . 'whitevillas-latest.apk');
                        $saveData['apk_url'] = Router::url('/downloads/' . $newFileName, true);
                    }
                } else {
                    $this->Session->setFlash(__('Please upload a valid .apk file.'), 'flash', array('alert' => 'danger'));
                    return $this->redirect(array('action' => 'version_control'));
                }
            }

            $this->AppSetting->id = $id;
            if ($this->AppSetting->save($saveData)) {
                $this->Session->setFlash(__('App configuration and update settings have been saved.'), 'flash', array('alert' => 'success'));
                return $this->redirect(array('action' => 'version_control'));
            } else {
                $this->Session->setFlash(__('Failed to save settings. Please try again.'), 'flash', array('alert' => 'danger'));
            }
        }

        if (!$this->request->data) {
            $this->request->data = $setting;
        }
        $this->set('setting', $setting);
    }

    /**
     * Send Custom In-App Notification Composer
     */
    public function send_notification()
    {
        $this->set('title_for_layout', __('Send In-App Notification'));

        // Prepare client list for specific target dropdown
        $clients = $this->Client->find('list', array(
            'fields' => array('Client.id', 'Client.name'),
            'order' => array('Client.name' => 'ASC'),
            'limit' => 500
        ));

        if ($this->request->is('post')) {
            $data = $this->request->data['AppNotification'];

            $title = !empty($data['title']) ? trim($data['title']) : '';
            $message = !empty($data['message']) ? trim($data['message']) : '';
            $type = !empty($data['type']) ? $data['type'] : 'admin_notice';
            $audience = !empty($data['target_audience']) ? $data['target_audience'] : 'all_clients';
            $specificClientId = !empty($data['specific_client_id']) ? (int)$data['specific_client_id'] : null;
            $actionRoute = !empty($data['action_route']) ? $data['action_route'] : null;
            $actionUrl = !empty($data['action_url']) ? trim($data['action_url']) : null;

            if (empty($title) || empty($message)) {
                $this->Session->setFlash(__('Title and Message are required.'), 'flash', array('alert' => 'danger'));
                return $this->redirect(array('action' => 'send_notification'));
            }

            // Determine Target Recipient Client IDs
            $recipientIds = array();

            if ($audience === 'specific_client' && $specificClientId > 0) {
                $recipientIds[] = $specificClientId;
            } elseif ($audience === 'all_clients') {
                $recipientIds = $this->Client->find('list', array('fields' => array('Client.id', 'Client.id')));
                $recipientIds = array_values($recipientIds);
            } elseif ($audience === 'deal_active') {
                $deals = $this->MyDeal->find('all', array(
                    'fields' => array('DISTINCT MyDeal.client_id'),
                    'conditions' => array('MyDeal.deal_status' => array('Active', 'active'))
                ));
                foreach ($deals as $d) {
                    if (!empty($d['MyDeal']['client_id'])) $recipientIds[] = (int)$d['MyDeal']['client_id'];
                }
            } elseif ($audience === 'deal_blocked') {
                $deals = $this->MyDeal->find('all', array(
                    'fields' => array('DISTINCT MyDeal.client_id'),
                    'conditions' => array('MyDeal.deal_status' => array('Blocked', 'blocked'))
                ));
                foreach ($deals as $d) {
                    if (!empty($d['MyDeal']['client_id'])) $recipientIds[] = (int)$d['MyDeal']['client_id'];
                }
            } elseif ($audience === 'deal_overdue') {
                // Find clients who have overdue payments
                $today = date('Y-m-d');
                $overduePlans = $this->PlansPayment->find('all', array(
                    'fields' => array('DISTINCT PlansPayment.deal_id'),
                    'conditions' => array('PlansPayment.date <=' => $today)
                ));
                $dealIds = array();
                foreach ($overduePlans as $op) {
                    if (!empty($op['PlansPayment']['deal_id'])) $dealIds[] = (int)$op['PlansPayment']['deal_id'];
                }
                if (!empty($dealIds)) {
                    $deals = $this->MyDeal->find('all', array(
                        'fields' => array('DISTINCT MyDeal.client_id'),
                        'conditions' => array('MyDeal.id' => $dealIds)
                    ));
                    foreach ($deals as $d) {
                        if (!empty($d['MyDeal']['client_id'])) $recipientIds[] = (int)$d['MyDeal']['client_id'];
                    }
                }
            } elseif ($audience === 'all_contacts') {
                // For website contacts, broadcast is registered for all client app users with notice type
                $recipientIds = $this->Client->find('list', array('fields' => array('Client.id', 'Client.id')));
                $recipientIds = array_values($recipientIds);
            }

            $recipientIds = array_unique(array_filter($recipientIds));
            $sentCount = 0;
            $now = date('Y-m-d H:i:s');
            $broadcastKey = 'broadcast_' . time() . '_' . rand(100, 999);

            // Dispatch to client_notifications
            foreach ($recipientIds as $cid) {
                $this->ClientNotification->create();
                $this->ClientNotification->save(array(
                    'client_id' => $cid,
                    'notif_key' => $broadcastKey . '_' . $cid,
                    'title' => $title,
                    'type' => $type,
                    'message' => $message,
                    'target_audience' => $audience,
                    'action_route' => $actionRoute,
                    'action_url' => $actionUrl,
                    'is_read' => 0,
                    'created' => $now
                ));
                $sentCount++;
            }

            // Also record a broadcast log
            $adminUserId = $this->Session->read('User.id');
            $this->AppNotificationBroadcast->create();
            $this->AppNotificationBroadcast->save(array(
                'title' => $title,
                'message' => $message,
                'type' => $type,
                'target_audience' => $audience,
                'specific_client_id' => $specificClientId,
                'action_route' => $actionRoute,
                'action_url' => $actionUrl,
                'total_sent' => $sentCount,
                'created_by' => $adminUserId,
                'created' => $now
            ));

            $this->Session->setFlash(
                sprintf(__('In-app notification successfully sent to %d recipient(s).'), $sentCount),
                'flash',
                array('alert' => 'success')
            );

            return $this->redirect(array('action' => 'notifications_history'));
        }

        $this->set('clients', $clients);
    }

    /**
     * Broadcast Notifications History
     */
    public function notifications_history()
    {
        $this->set('title_for_layout', __('In-App Broadcast History'));

        $this->Paginator->settings = array(
            'AppNotificationBroadcast' => array(
                'order' => array('AppNotificationBroadcast.id' => 'DESC'),
                'limit' => 20
            )
        );

        $broadcasts = $this->Paginator->paginate('AppNotificationBroadcast');
        $this->set('broadcasts', $broadcasts);
    }

    /**
     * Delete Broadcast Record
     */
    public function delete_broadcast($id = null)
    {
        if ($this->request->is(array('post', 'delete'))) {
            $id = (int)$id;
            if ($id > 0) {
                $this->AppNotificationBroadcast->delete($id);
                $this->Session->setFlash(__('Broadcast notification log deleted.'), 'flash', array('alert' => 'success'));
            }
        }
        return $this->redirect(array('action' => 'notifications_history'));
    }

    /**
     * Client Users & App Activity
     */
    public function client_activity()
    {
        $this->set('title_for_layout', __('Client Mobile App Activity'));

        $conditions = array();
        if ($this->Client->hasField('app_login_status')) {
            $conditions = array('Client.app_login_status' => 1);
        }

        $this->Paginator->settings = array(
            'Client' => array(
                'conditions' => $conditions,
                'order' => array('Client.app_last_login' => 'DESC'),
                'limit' => 25
            )
        );

        $clients = $this->Paginator->paginate('Client');
        $this->set('clients', $clients);
    }
}
