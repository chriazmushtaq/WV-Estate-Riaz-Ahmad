<?php
App::uses('AdminAppController', 'Admin.Controller');

class QrScanLogsController extends AdminAppController
{
    public $uses = array('QrScanLog');
    public $components = array('Paginator', 'Session', 'RequestHandler');

    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->_checkAccessRights();
        $this->_ensureQrScanLogsMenu();
    }

    /**
     * Restrict access strictly to Super Admin and Agency Admin
     */
    protected function _checkAccessRights()
    {
        if (php_sapi_name() === 'cli' || defined('STDIN')) {
            return true;
        }

        $sessionUser = (array)$this->Session->read('User');
        $userType = !empty($sessionUser['User']['user_type']) ? strtolower(trim((string)$sessionUser['User']['user_type'])) : '';
        $ugroupId = !empty($sessionUser['User']['ugroup_id']) ? (int)$sessionUser['User']['ugroup_id'] : 0;
        
        $isSuper = ($this->_isSuperAdminSession($sessionUser) || $userType === 'admin' || $ugroupId === 1);
        $isAgency = ($userType === 'agency' || $ugroupId === 4);

        if (!$isSuper && !$isAgency) {
            $this->Session->setFlash(__('Access denied. Only Super Admin and Agency Admin can access QR Scan Audit Logs.'), 'flash', array('alert' => 'danger'));
            $this->redirect(array('controller' => 'Dashboards', 'action' => 'index', 'admin' => true));
            return false;
        }
        return true;
    }

    /**
     * Self-healing menu registration in Pages & PageRights
     */
    public function _ensureQrScanLogsMenu()
    {
        try {
            $Page = ClassRegistry::init('Page');
            $PageRight = ClassRegistry::init('PageRight');
            if (!$Page || !$PageRight) {
                return;
            }

            // 1. Locate parent Letters page (ID 113 or parent_id = 0)
            $parent = $Page->find('first', array(
                'conditions' => array(
                    'OR' => array(
                        array('Page.id' => 113),
                        array('LOWER(Page.page_name)' => 'letters', 'Page.parent_id' => 0)
                    )
                ),
                'recursive' => -1
            ));

            $parentId = !empty($parent['Page']['id']) ? (int)$parent['Page']['id'] : 113;

            // Ensure parent Letters has rights for Agency (ugroup 4) as well
            $agencyParentRight = $PageRight->find('first', array(
                'conditions' => array('PageRight.page_id' => $parentId, 'PageRight.ugroup_id' => 4),
                'recursive' => -1
            ));
            if (empty($agencyParentRight)) {
                $PageRight->create();
                $PageRight->save(array('PageRight' => array(
                    'page_id' => $parentId,
                    'ugroup_id' => 4,
                    'save_right' => 1,
                    'update_right' => 1,
                    'view_right' => 1,
                    'search_right' => 1
                )));
            } elseif (empty($agencyParentRight['PageRight']['view_right'])) {
                $PageRight->id = $agencyParentRight['PageRight']['id'];
                $PageRight->saveField('view_right', 1);
            }

            // 2. Locate or create QR Scan Audit Logs child page
            $pageRow = $Page->find('first', array(
                'conditions' => array(
                    'Page.parent_id' => $parentId,
                    'OR' => array(
                        array('LOWER(Page.controller_name)' => 'qrscanlogs'),
                        array('LOWER(Page.page_name)' => 'qr scan audit logs')
                    )
                ),
                'recursive' => -1
            ));

            $pageData = array(
                'model_name' => 'QrScanLog',
                'page_name' => 'QR Scan Audit Logs',
                'controller_name' => 'QrScanLogs',
                'action_name' => 'index',
                'icon' => 'fa fa-shield',
                'parent_id' => $parentId,
                'ordering' => 4,
                'published' => 'Yes',
                'sel_name' => 'qrscanlogs'
            );

            if (!empty($pageRow['Page']['id'])) {
                $pageData['id'] = (int)$pageRow['Page']['id'];
            }

            $Page->create();
            $Page->save(array('Page' => $pageData), array('validate' => false));
            $pageId = !empty($pageData['id']) ? (int)$pageData['id'] : (int)$Page->id;

            if ($pageId > 0) {
                // Assign all rights to Super Admin (1) and Agency Admin (4)
                foreach (array(1, 4) as $ugroupId) {
                    $right = $PageRight->find('first', array(
                        'conditions' => array('PageRight.page_id' => $pageId, 'PageRight.ugroup_id' => $ugroupId),
                        'recursive' => -1
                    ));
                    $rightData = array(
                        'page_id' => $pageId,
                        'ugroup_id' => $ugroupId,
                        'save_right' => 1,
                        'update_right' => 1,
                        'view_right' => 1,
                        'search_right' => 1
                    );
                    if (!empty($right['PageRight']['id'])) {
                        $rightData['id'] = (int)$right['PageRight']['id'];
                    }
                    $PageRight->create();
                    $PageRight->save(array('PageRight' => $rightData));
                }

                if (method_exists($this, '_bumpMenuPermissionVersion')) {
                    $this->_bumpMenuPermissionVersion(1);
                    $this->_bumpMenuPermissionVersion(4);
                }
            }
        } catch (Exception $e) {
            // Keep app flow safe
        }
    }

    /**
     * Main Audit Logs Dashboard
     */
    public function admin_index()
    {
        $this->layout = 'default';

        // 1. Compute Security Statistics
        $totalScans = $this->QrScanLog->find('count');
        $verifiedScans = $this->QrScanLog->find('count', array('conditions' => array('QrScanLog.scan_status' => 'VERIFIED')));
        $invalidScans = $this->QrScanLog->find('count', array('conditions' => array('QrScanLog.scan_status !=' => 'VERIFIED')));
        $suspiciousAlerts = $this->QrScanLog->find('count', array('conditions' => array('QrScanLog.is_suspicious' => 1)));
        $todayScans = $this->QrScanLog->find('count', array(
            'conditions' => array('QrScanLog.created >=' => date('Y-m-d 00:00:00'))
        ));

        // 2. Query Filters
        $conditions = array();

        $statusFilter = !empty($this->request->query['status']) ? trim($this->request->query['status']) : '';
        if ($statusFilter !== '' && $statusFilter !== 'ALL') {
            $conditions['QrScanLog.scan_status'] = $statusFilter;
        }

        $alertFilter = isset($this->request->query['alert']) && $this->request->query['alert'] !== '' ? (int)$this->request->query['alert'] : null;
        if ($alertFilter !== null) {
            $conditions['QrScanLog.is_suspicious'] = $alertFilter;
        }

        $deviceFilter = !empty($this->request->query['device']) ? trim($this->request->query['device']) : '';
        if ($deviceFilter !== '' && $deviceFilter !== 'ALL') {
            $conditions['QrScanLog.device_type'] = $deviceFilter;
        }

        $searchQuery = !empty($this->request->query['q']) ? trim($this->request->query['q']) : '';
        if ($searchQuery !== '') {
            $conditions['OR'] = array(
                'QrScanLog.reference_no LIKE' => '%' . $searchQuery . '%',
                'QrScanLog.client_name LIKE' => '%' . $searchQuery . '%',
                'QrScanLog.property_name LIKE' => '%' . $searchQuery . '%',
                'QrScanLog.ip_address LIKE' => '%' . $searchQuery . '%',
                'QrScanLog.token_attempted LIKE' => '%' . $searchQuery . '%',
                'QrScanLog.suspicious_reason LIKE' => '%' . $searchQuery . '%'
            );
        }

        if (!empty($this->request->query['date_from'])) {
            $conditions['QrScanLog.created >='] = date('Y-m-d 00:00:00', strtotime($this->request->query['date_from']));
        }
        if (!empty($this->request->query['date_to'])) {
            $conditions['QrScanLog.created <='] = date('Y-m-d 23:59:59', strtotime($this->request->query['date_to']));
        }

        // 3. Paginate
        $this->Paginator->settings = array(
            'conditions' => $conditions,
            'limit' => 25,
            'order' => array('QrScanLog.id' => 'DESC')
        );

        $logs = $this->Paginator->paginate('QrScanLog');

        $this->set(compact(
            'logs',
            'totalScans',
            'verifiedScans',
            'invalidScans',
            'suspiciousAlerts',
            'todayScans',
            'statusFilter',
            'alertFilter',
            'deviceFilter',
            'searchQuery'
        ));
    }

    /**
     * View Detailed Audit Log Record (Modal or standalone)
     */
    public function admin_view($id = null)
    {
        $id = (int)$id;
        $log = $this->QrScanLog->findById($id);
        if (empty($log)) {
            if ($this->request->is('ajax')) {
                $this->autoRender = false;
                echo '<div class="alert alert-danger">' . __('Record not found.') . '</div>';
                return;
            }
            $this->Session->setFlash(__('Scan audit record not found.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }

        $this->set('log', $log);

        if ($this->request->is('ajax')) {
            $this->layout = 'ajax';
        }
    }

    /**
     * Delete an individual log entry
     */
    public function admin_delete($id = null)
    {
        $id = (int)$id;
        if ($this->request->is(array('post', 'get', 'delete'))) {
            if ($this->QrScanLog->delete($id)) {
                $this->Session->setFlash(__('Audit log entry #%d successfully deleted.', $id), 'flash', array('alert' => 'success'));
            } else {
                $this->Session->setFlash(__('Failed to delete log entry.', $id), 'flash', array('alert' => 'danger'));
            }
        }
        return $this->redirect(array('action' => 'index'));
    }

    /**
     * Clear all logs or clear non-suspicious logs
     */
    public function admin_clear_all()
    {
        if ($this->request->is('post')) {
            $type = !empty($this->request->data['clear_type']) ? $this->request->data['clear_type'] : 'all';
            if ($type === 'non_suspicious') {
                $this->QrScanLog->deleteAll(array('QrScanLog.is_suspicious' => 0), false);
                $this->Session->setFlash(__('Cleared all routine verified scan logs. Suspicious alerts preserved.', 'flash', array('alert' => 'success')));
            } else {
                $this->QrScanLog->deleteAll(array('1 = 1'), false);
                $this->Session->setFlash(__('All QR scan audit logs have been successfully cleared.', 'flash', array('alert' => 'success')));
            }
        }
        return $this->redirect(array('action' => 'index'));
    }

    /**
     * Export Logs to CSV
     */
    public function admin_export()
    {
        $this->autoRender = false;
        $logs = $this->QrScanLog->find('all', array(
            'order' => array('QrScanLog.id' => 'DESC'),
            'limit' => 5000
        ));

        $filename = 'QR_Scan_Audit_Logs_' . date('Y-m-d_His') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $out = fopen('php://output', 'w');
        // BOM for Excel UTF-8 support
        fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($out, array(
            'Log ID',
            'Scan Timestamp',
            'Scan Status',
            'Suspicious Alert',
            'Suspicious Reason',
            'Reference No',
            'Client Name',
            'Property Name',
            'Letter ID',
            'Deal ID',
            'Token Scanned',
            'IP Address',
            'City',
            'Country',
            'Device Type',
            'Browser',
            'Operating System',
            'User Agent'
        ));

        foreach ($logs as $row) {
            $item = $row['QrScanLog'];
            fputcsv($out, array(
                $item['id'],
                $item['created'],
                $item['scan_status'],
                $item['is_suspicious'] ? 'YES (ALERT)' : 'NO',
                $item['suspicious_reason'],
                $item['reference_no'],
                $item['client_name'],
                $item['property_name'],
                $item['letter_id'],
                $item['deal_id'],
                $item['token_attempted'],
                $item['ip_address'],
                $item['city'],
                $item['country'],
                $item['device_type'],
                $item['browser'],
                $item['os'],
                $item['user_agent']
            ));
        }

        fclose($out);
        exit;
    }
}
