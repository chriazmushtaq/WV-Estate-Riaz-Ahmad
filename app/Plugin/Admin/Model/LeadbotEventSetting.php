<?php
App::uses('AdminAppModel', 'Admin.Model');

class LeadbotEventSetting extends AdminAppModel
{
    public $useTable = 'leadbot_event_settings';

    /**
     * Get all event settings for an agency/project and optionally an agent,
     * merging: Global (0,0) -> Agency (projectId,0) -> Agent (projectId,userId)
     */
    public function getAgencyEvents($projectId = 0, $userId = 0)
    {
        $projectId = (int)$projectId;
        $userId    = (int)$userId;

        // 1. Fetch global templates first (project_id = 0, user_id = 0)
        $globalRows = $this->find('all', array(
            'conditions' => array(
                'LeadbotEventSetting.project_id' => 0,
                'LeadbotEventSetting.user_id'    => 0
            ),
            'order' => array('LeadbotEventSetting.id' => 'ASC'),
            'recursive' => -1
        ));

        // Fallback for older seeded records where user_id might not have been specified
        if (empty($globalRows)) {
            $globalRows = $this->find('all', array(
                'conditions' => array('LeadbotEventSetting.project_id' => 0),
                'order' => array('LeadbotEventSetting.id' => 'ASC'),
                'recursive' => -1
            ));
        }

        $eventsMap = array();
        foreach ($globalRows as $row) {
            $key = $row['LeadbotEventSetting']['event_key'];
            $eventsMap[$key] = $row['LeadbotEventSetting'];
        }

        // 2. Merge Agency overrides (project_id > 0, user_id = 0)
        if ($projectId > 0) {
            $agencyRows = $this->find('all', array(
                'conditions' => array(
                    'LeadbotEventSetting.project_id' => $projectId,
                    'LeadbotEventSetting.user_id'    => 0
                ),
                'order' => array('LeadbotEventSetting.id' => 'ASC'),
                'recursive' => -1
            ));

            foreach ($agencyRows as $row) {
                $key = $row['LeadbotEventSetting']['event_key'];
                $eventsMap[$key] = array_merge(isset($eventsMap[$key]) ? $eventsMap[$key] : array(), $row['LeadbotEventSetting']);
            }
        }

        // 3. Merge Agent personal overrides (user_id > 0)
        if ($userId > 0) {
            $agentRows = $this->find('all', array(
                'conditions' => array('LeadbotEventSetting.user_id' => $userId),
                'order' => array('LeadbotEventSetting.id' => 'ASC'),
                'recursive' => -1
            ));

            foreach ($agentRows as $row) {
                $key = $row['LeadbotEventSetting']['event_key'];
                $eventsMap[$key] = array_merge(isset($eventsMap[$key]) ? $eventsMap[$key] : array(), $row['LeadbotEventSetting']);
            }
        }

        // 4. Attach Category metadata and sort logically by workflow sequence
        $catMap = self::getEventCategoryMap();
        foreach ($eventsMap as $k => &$ev) {
            if (isset($catMap[$k])) {
                $ev['category']       = $catMap[$k]['category'];
                $ev['category_label'] = $catMap[$k]['category_label'];
                $ev['sort_order']     = $catMap[$k]['order'];
                $ev['icon']           = $catMap[$k]['icon'];
                $ev['color']          = $catMap[$k]['color'];
            } else {
                $ev['category']       = 'general';
                $ev['category_label'] = 'General CRM Events';
                $ev['sort_order']     = 999;
                $ev['icon']           = 'fa-bell';
                $ev['color']          = '#64748b';
            }
        }
        unset($ev);

        // Sort by sort_order ASC
        uasort($eventsMap, function($a, $b) {
            $oa = isset($a['sort_order']) ? (int)$a['sort_order'] : 999;
            $ob = isset($b['sort_order']) ? (int)$b['sort_order'] : 999;
            if ($oa === $ob) {
                return strcmp(isset($a['event_label']) ? $a['event_label'] : '', isset($b['event_label']) ? $b['event_label'] : '');
            }
            return ($oa < $ob) ? -1 : 1;
        });

        return $eventsMap;
    }

    /**
     * Standard Real Estate Notification Category Mapping & Sort Orders
     */
    public static function getEventCategoryMap()
    {
        return array(
            // 1. Contacts & Client Portal (Onboarding)
            'contact_registered'     => array('category' => 'contacts',   'category_label' => 'Contacts & Portal',     'order' => 10, 'icon' => 'fa-user-plus', 'color' => '#6366f1'),
            'client_welcome'         => array('category' => 'contacts',   'category_label' => 'Contacts & Portal',     'order' => 20, 'icon' => 'fa-id-card', 'color' => '#6366f1'),
            'client_registration'    => array('category' => 'contacts',   'category_label' => 'Contacts & Portal',     'order' => 25, 'icon' => 'fa-id-badge', 'color' => '#6366f1'),
            'client_login_credentials'=> array('category' => 'contacts',  'category_label' => 'Contacts & Portal',     'order' => 28, 'icon' => 'fa-key', 'color' => '#6366f1'),

            // 2. Leads & CRM Follow-ups
            'lead_assignment'        => array('category' => 'leads',      'category_label' => 'Leads & Inquiries',     'order' => 30, 'icon' => 'fa-user-circle', 'color' => '#0284c7'),
            'facebook_sync'          => array('category' => 'leads',      'category_label' => 'Leads & Inquiries',     'order' => 40, 'icon' => 'fa-facebook-square', 'color' => '#0284c7'),
            'followup_reminder'      => array('category' => 'leads',      'category_label' => 'Leads & Inquiries',     'order' => 50, 'icon' => 'fa-calendar-check-o', 'color' => '#0284c7'),
            'followup_due'           => array('category' => 'leads',      'category_label' => 'Leads & Inquiries',     'order' => 55, 'icon' => 'fa-clock-o', 'color' => '#0284c7'),
            'overdue_alert'          => array('category' => 'leads',      'category_label' => 'Leads & Inquiries',     'order' => 60, 'icon' => 'fa-exclamation-triangle', 'color' => '#dc2626'),
            'followup_overdue'       => array('category' => 'leads',      'category_label' => 'Leads & Inquiries',     'order' => 65, 'icon' => 'fa-exclamation-circle', 'color' => '#dc2626'),
            'site_visit_scheduled'   => array('category' => 'leads',      'category_label' => 'Leads & Inquiries',     'order' => 70, 'icon' => 'fa-map-marker', 'color' => '#0284c7'),

            // 3. Deals & Sales Bookings
            'deal_closed'            => array('category' => 'deals',      'category_label' => 'Deals & Bookings',      'order' => 80, 'icon' => 'fa-handshake-o', 'color' => '#10b981'),
            'deal_transfer'          => array('category' => 'deals',      'category_label' => 'Deals & Bookings',      'order' => 90, 'icon' => 'fa-exchange', 'color' => '#10b981'),
            'balloting_result'       => array('category' => 'deals',      'category_label' => 'Deals & Bookings',      'order' => 100, 'icon' => 'fa-trophy', 'color' => '#10b981'),
            'letter_issued'          => array('category' => 'deals',      'category_label' => 'Deals & Bookings',      'order' => 110, 'icon' => 'fa-file-text-o', 'color' => '#10b981'),

            // 4. Accounts, Payments & Installments
            'payment_received'       => array('category' => 'finance',    'category_label' => 'Payments & Accounts',   'order' => 120, 'icon' => 'fa-money', 'color' => '#059669'),
            'payment_posted'         => array('category' => 'finance',    'category_label' => 'Payments & Accounts',   'order' => 130, 'icon' => 'fa-check-circle', 'color' => '#059669'),
            'installment_reminder'   => array('category' => 'finance',    'category_label' => 'Payments & Accounts',   'order' => 140, 'icon' => 'fa-clock-o', 'color' => '#d97706'),
            'installment_overdue'    => array('category' => 'finance',    'category_label' => 'Payments & Accounts',   'order' => 150, 'icon' => 'fa-bell', 'color' => '#dc2626'),
            'refund_processed'       => array('category' => 'finance',    'category_label' => 'Payments & Accounts',   'order' => 160, 'icon' => 'fa-reply', 'color' => '#7c3aed'),
            'expense_approval'       => array('category' => 'finance',    'category_label' => 'Payments & Accounts',   'order' => 170, 'icon' => 'fa-calculator', 'color' => '#475569'),

            // 5. Operations, Construction & Broadcast
            'construction_milestone' => array('category' => 'operations', 'category_label' => 'Operations & Milestone', 'order' => 180, 'icon' => 'fa-building', 'color' => '#ea580c'),
            'broadcast'              => array('category' => 'operations', 'category_label' => 'Operations & Milestone', 'order' => 190, 'icon' => 'fa-bullhorn', 'color' => '#d946ef')
        );
    }

    /**
     * Get specific event configuration for an agency or agent
     */
    public function getEventConfig($eventKey, $projectId = 0, $userId = 0)
    {
        $eventKey  = trim((string)$eventKey);
        $projectId = (int)$projectId;
        $userId    = (int)$userId;

        // 1. Check Agent personal override
        if ($userId > 0) {
            $row = $this->find('first', array(
                'conditions' => array(
                    'LeadbotEventSetting.user_id'   => $userId,
                    'LeadbotEventSetting.event_key' => $eventKey
                ),
                'recursive' => -1
            ));
            if (!empty($row['LeadbotEventSetting'])) {
                return $row['LeadbotEventSetting'];
            }
        }

        // 2. Check Agency override
        if ($projectId > 0) {
            $row = $this->find('first', array(
                'conditions' => array(
                    'LeadbotEventSetting.project_id' => $projectId,
                    'LeadbotEventSetting.user_id'    => 0,
                    'LeadbotEventSetting.event_key'  => $eventKey
                ),
                'recursive' => -1
            ));
            if (!empty($row['LeadbotEventSetting'])) {
                return $row['LeadbotEventSetting'];
            }
        }

        // 3. Fallback to Global default (project_id = 0, user_id = 0)
        $global = $this->find('first', array(
            'conditions' => array(
                'LeadbotEventSetting.project_id' => 0,
                'LeadbotEventSetting.event_key'  => $eventKey
            ),
            'recursive' => -1
        ));

        return !empty($global['LeadbotEventSetting']) ? $global['LeadbotEventSetting'] : null;
    }

    /**
     * Save all events configuration for an agency or agent
     */
    public function saveAgencyEvents($projectId, array $eventsPayload, $userId = 0)
    {
        $projectId = (int)$projectId;
        $userId    = (int)$userId;

        foreach ($eventsPayload as $eventKey => $evtData) {
            $eventKey = trim((string)$eventKey);
            if ($eventKey === '' || !is_array($evtData)) {
                continue;
            }

            $existing = $this->find('first', array(
                'conditions' => array(
                    'LeadbotEventSetting.project_id' => $projectId,
                    'LeadbotEventSetting.user_id'    => $userId,
                    'LeadbotEventSetting.event_key'  => $eventKey
                ),
                'fields' => array('LeadbotEventSetting.id'),
                'recursive' => -1
            ));

            $saveData = array(
                'project_id'        => $projectId,
                'user_id'           => $userId,
                'event_key'         => $eventKey,
                'event_label'       => !empty($evtData['event_label']) ? $evtData['event_label'] : ucwords(str_replace('_', ' ', $eventKey)),
                'event_description' => isset($evtData['event_description']) ? $evtData['event_description'] : '',
                'whatsapp_enabled'  => !empty($evtData['whatsapp_enabled']) ? 1 : 0,
                'sms_enabled'       => !empty($evtData['sms_enabled']) ? 1 : 0,
                'whatsapp_template' => isset($evtData['whatsapp_template']) ? $evtData['whatsapp_template'] : '',
                'sms_template'      => isset($evtData['sms_template']) ? $evtData['sms_template'] : '',
                'notify_agent'      => !empty($evtData['notify_agent']) ? 1 : 0,
                'notify_client'     => !empty($evtData['notify_client']) ? 1 : 0,
                'modified'          => date('Y-m-d H:i:s')
            );

            if (!empty($existing['LeadbotEventSetting']['id'])) {
                $saveData['id'] = (int)$existing['LeadbotEventSetting']['id'];
            } else {
                $saveData['created'] = date('Y-m-d H:i:s');
                $this->create();
            }

            $this->save($saveData, array('validate' => false));
        }

        return true;
    }

    /**
     * Quick 1-click toggle update via AJAX
     */
    public function updateSingleToggle($projectId, $userId, $eventKey, $field, $state)
    {
        $projectId = (int)$projectId;
        $userId    = (int)$userId;
        $eventKey  = trim((string)$eventKey);
        $field     = trim((string)$field);
        $state     = !empty($state) ? 1 : 0;

        $allowedFields = array('whatsapp_enabled', 'sms_enabled', 'notify_agent', 'notify_client');
        if (!in_array($field, $allowedFields, true)) {
            return false;
        }

        $existing = $this->find('first', array(
            'conditions' => array(
                'LeadbotEventSetting.project_id' => $projectId,
                'LeadbotEventSetting.user_id'    => $userId,
                'LeadbotEventSetting.event_key'  => $eventKey
            ),
            'recursive' => -1
        ));

        if (!empty($existing['LeadbotEventSetting']['id'])) {
            $this->id = (int)$existing['LeadbotEventSetting']['id'];
            return (bool)$this->saveField($field, $state, array('validate' => false));
        }

        // If no custom row exists yet, inherit from global template and create custom row
        $global = $this->getEventConfig($eventKey, 0, 0);
        $createData = array(
            'project_id'        => $projectId,
            'user_id'           => $userId,
            'event_key'         => $eventKey,
            'event_label'       => !empty($global['event_label']) ? $global['event_label'] : ucwords(str_replace('_', ' ', $eventKey)),
            'event_description' => !empty($global['event_description']) ? $global['event_description'] : '',
            'whatsapp_enabled'  => !empty($global['whatsapp_enabled']) ? 1 : 0,
            'sms_enabled'       => !empty($global['sms_enabled']) ? 1 : 0,
            'whatsapp_template' => !empty($global['whatsapp_template']) ? $global['whatsapp_template'] : '',
            'sms_template'      => !empty($global['sms_template']) ? $global['sms_template'] : '',
            'notify_agent'      => !empty($global['notify_agent']) ? 1 : 0,
            'notify_client'     => !empty($global['notify_client']) ? 1 : 0,
            $field              => $state,
            'created'           => date('Y-m-d H:i:s'),
            'modified'          => date('Y-m-d H:i:s')
        );

        $this->create();
        return (bool)$this->save($createData, array('validate' => false));
    }

    /**
     * Bulk update toggles for all events or a specific category for an agency or agent
     */
    public function updateBulkChannelToggles($projectId, $userId, $field, $state, $category = null)
    {
        $projectId = (int)$projectId;
        $userId    = (int)$userId;
        $field     = trim((string)$field);
        $state     = !empty($state) ? 1 : 0;

        $allowedFields = array('whatsapp_enabled', 'sms_enabled', 'notify_agent', 'notify_client');
        if (!in_array($field, $allowedFields, true)) {
            return false;
        }

        $allEvents = $this->getAgencyEvents($projectId, $userId);
        $updatedCount = 0;

        foreach ($allEvents as $eventKey => $eventRow) {
            if ($category && $category !== 'all' && isset($eventRow['category']) && $eventRow['category'] !== $category) {
                continue;
            }
            if ($this->updateSingleToggle($projectId, $userId, $eventKey, $field, $state)) {
                $updatedCount++;
            }
        }

        return $updatedCount;
    }
}
