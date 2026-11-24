<?php
App::uses('AdminAppModel', 'Admin.Model');

class LeadbotNotificationLog extends AdminAppModel
{
    public $useTable = 'leadbot_notification_logs';

    /**
     * Record a notification attempt
     */
    public function recordLog(array $data)
    {
        $saveData = array(
            'project_id'                => isset($data['project_id']) ? (int)$data['project_id'] : 0,
            'user_id'                   => isset($data['user_id']) ? (int)$data['user_id'] : null,
            'channel'                   => strtolower(trim((string)(isset($data['channel']) ? $data['channel'] : 'whatsapp'))),
            'event_key'                 => trim((string)(isset($data['event_key']) ? $data['event_key'] : 'general')),
            'recipient_phone'           => trim((string)(isset($data['recipient_phone']) ? $data['recipient_phone'] : '')),
            'recipient_name'            => isset($data['recipient_name']) ? trim((string)$data['recipient_name']) : null,
            'message'                   => trim((string)(isset($data['message']) ? $data['message'] : '')),
            'gateway_profile_or_device' => isset($data['gateway_profile_or_device']) ? trim((string)$data['gateway_profile_or_device']) : null,
            'status'                    => !empty($data['status']) ? $data['status'] : 'sent',
            'response_code'             => isset($data['response_code']) ? (int)$data['response_code'] : null,
            'response_payload'          => isset($data['response_payload']) ? (is_array($data['response_payload']) ? json_encode($data['response_payload'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : (string)$data['response_payload']) : null,
            'error_message'             => isset($data['error_message']) ? (string)$data['error_message'] : null,
            'is_test'                   => !empty($data['is_test']) ? 1 : 0,
            'created'                   => date('Y-m-d H:i:s'),
            'modified'                  => date('Y-m-d H:i:s')
        );

        $this->create();
        return (bool)$this->save($saveData, array('validate' => false));
    }

    /**
     * Get real-time queue counts for WhatsApp, SMS, and Total
     */
    public function getQueueCounts($projectId = 0, $isSuperAdmin = false)
    {
        $baseCond = array();
        if (!$isSuperAdmin || $projectId > 0) {
            $baseCond['LeadbotNotificationLog.project_id'] = (int)$projectId;
        }

        $waCond = array_merge($baseCond, array(
            'LeadbotNotificationLog.channel' => 'whatsapp',
            'LeadbotNotificationLog.status'  => array('queued', 'pending')
        ));
        $waCount = (int)$this->find('count', array('conditions' => $waCond));

        $smsCond = array_merge($baseCond, array(
            'LeadbotNotificationLog.channel' => 'sms',
            'LeadbotNotificationLog.status'  => array('queued', 'pending')
        ));
        $smsCount = (int)$this->find('count', array('conditions' => $smsCond));

        $allQueueCond = array_merge($baseCond, array(
            'LeadbotNotificationLog.status' => array('queued', 'pending')
        ));
        $totalCount = (int)$this->find('count', array('conditions' => $allQueueCond));

        $cancelledCond = array_merge($baseCond, array(
            'LeadbotNotificationLog.status' => 'cancelled'
        ));
        $cancelledCount = (int)$this->find('count', array('conditions' => $cancelledCond));

        $failedCond = array_merge($baseCond, array(
            'LeadbotNotificationLog.status' => 'failed'
        ));
        $failedCount = (int)$this->find('count', array('conditions' => $failedCond));

        return array(
            'total'     => $totalCount,
            'whatsapp'  => $waCount,
            'sms'       => $smsCount,
            'cancelled' => $cancelledCount,
            'failed'    => $failedCount
        );
    }

    /**
     * Get queue items matching role-based filters
     */
    public function getQueueItems($projectId = 0, $isSuperAdmin = false, array $filters = array(), $limit = 150)
    {
        $conditions = array();
        if (!$isSuperAdmin || $projectId > 0) {
            $conditions['LeadbotNotificationLog.project_id'] = (int)$projectId;
        }

        if (!empty($filters['channel']) && in_array(strtolower($filters['channel']), array('whatsapp', 'sms'), true)) {
            $conditions['LeadbotNotificationLog.channel'] = strtolower($filters['channel']);
        }

        if (isset($filters['status']) && $filters['status'] !== 'all' && $filters['status'] !== '') {
            if ($filters['status'] === 'queued') {
                $conditions['LeadbotNotificationLog.status'] = array('queued', 'pending');
            } else {
                $conditions['LeadbotNotificationLog.status'] = $filters['status'];
            }
        }

        if (!empty($filters['search'])) {
            $s = trim((string)$filters['search']);
            $conditions['OR'] = array(
                'LeadbotNotificationLog.recipient_phone LIKE' => "%$s%",
                'LeadbotNotificationLog.recipient_name LIKE'  => "%$s%",
                'LeadbotNotificationLog.message LIKE'         => "%$s%",
                'LeadbotNotificationLog.event_key LIKE'        => "%$s%"
            );
        }

        return (array)$this->find('all', array(
            'conditions' => $conditions,
            'order'      => array('LeadbotNotificationLog.id' => 'DESC'),
            'limit'      => (int)$limit,
            'recursive'  => -1
        ));
    }

    /**
     * Cancel a single queued notification
     * Validates Super Admin or Agency Admin ownership
     */
    public function cancelQueueItem($id, $projectId = 0, $isSuperAdmin = false, $adminName = 'Admin')
    {
        $id = (int)$id;
        if ($id <= 0) return false;

        $conditions = array('LeadbotNotificationLog.id' => $id);
        if (!$isSuperAdmin) {
            $conditions['LeadbotNotificationLog.project_id'] = (int)$projectId;
        }

        $item = $this->find('first', array(
            'conditions' => $conditions,
            'fields'     => array('LeadbotNotificationLog.id', 'LeadbotNotificationLog.status'),
            'recursive'  => -1
        ));

        if (empty($item)) {
            return false;
        }

        $this->id = $id;
        return (bool)$this->save(array(
            'status'        => 'cancelled',
            'error_message' => 'Cancelled by ' . $adminName . ' on ' . date('Y-m-d H:i:s'),
            'modified'      => date('Y-m-d H:i:s')
        ), array('validate' => false));
    }

    /**
     * Bulk cancel queued notifications
     */
    public function bulkCancelQueue(array $ids, $projectId = 0, $isSuperAdmin = false, $adminName = 'Admin')
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if (empty($ids)) return 0;

        $conditions = array('LeadbotNotificationLog.id' => $ids);
        if (!$isSuperAdmin) {
            $conditions['LeadbotNotificationLog.project_id'] = (int)$projectId;
        }

        return $this->updateAll(
            array(
                'LeadbotNotificationLog.status'        => "'cancelled'",
                'LeadbotNotificationLog.error_message' => "'Cancelled in bulk by " . addslashes($adminName) . " on " . date('Y-m-d H:i:s') . "'",
                'LeadbotNotificationLog.modified'      => "'" . date('Y-m-d H:i:s') . "'"
            ),
            $conditions
        );
    }

    /**
     * Delete a single queued notification
     */
    public function deleteQueueItem($id, $projectId = 0, $isSuperAdmin = false)
    {
        $id = (int)$id;
        if ($id <= 0) return false;

        $conditions = array('LeadbotNotificationLog.id' => $id);
        if (!$isSuperAdmin) {
            $conditions['LeadbotNotificationLog.project_id'] = (int)$projectId;
        }

        return (bool)$this->deleteAll($conditions, false);
    }

    /**
     * Bulk delete queued notifications
     */
    public function bulkDeleteQueue(array $ids, $projectId = 0, $isSuperAdmin = false)
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if (empty($ids)) return 0;

        $conditions = array('LeadbotNotificationLog.id' => $ids);
        if (!$isSuperAdmin) {
            $conditions['LeadbotNotificationLog.project_id'] = (int)$projectId;
        }

        return (bool)$this->deleteAll($conditions, false);
    }
}
