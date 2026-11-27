<?php
App::uses('AppModel', 'Model');
App::uses('ConnectionManager', 'Model');

class AgencyPortalMenu extends AppModel
{
    public $useTable = 'agency_portal_menus';
    public $cacheSources = false;

    /**
     * Ensure database table exists with correct schema
     */
    public function ensureSchema()
    {
        try {
            $db = ConnectionManager::getDataSource($this->useDbConfig);
            $tablePrefix = isset($db->config['prefix']) ? $db->config['prefix'] : '';
            $tableName = $tablePrefix . 'agency_portal_menus';
            $sql = "CREATE TABLE IF NOT EXISTS `{$tableName}` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `agency_id` int(11) NOT NULL DEFAULT 0,
              `menu_key` varchar(60) NOT NULL,
              `is_enabled` tinyint(1) NOT NULL DEFAULT 1,
              `ordering` int(11) NOT NULL DEFAULT 0,
              `custom_label` varchar(120) DEFAULT NULL,
              `updated_by` int(11) DEFAULT NULL,
              `created` datetime DEFAULT NULL,
              `modified` datetime DEFAULT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `uq_agency_menu` (`agency_id`, `menu_key`),
              KEY `idx_agency` (`agency_id`),
              KEY `idx_ordering` (`agency_id`, `ordering`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

            if (method_exists($db, 'rawQuery')) {
                $db->rawQuery($sql);
            } elseif (method_exists($db, 'query')) {
                $db->query($sql);
            }

            try {
                $cols = $db->query("SHOW COLUMNS FROM `{$tableName}` LIKE 'ordering'");
                if (empty($cols)) {
                    $db->query("ALTER TABLE `{$tableName}` ADD COLUMN `ordering` INT(11) NOT NULL DEFAULT 0 AFTER `is_enabled`");
                }
            } catch (Exception $eCol) {}

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Standard client portal menu definitions
     */
    public function getMenuDefinitions()
    {
        return array(
            'dashboards' => array(
                'key' => 'dashboards',
                'name' => __('Dashboard'),
                'controller' => 'Dashboards',
                'action' => 'index',
                'icon' => 'fa fa-dashboard',
                'color' => '#0284c7',
                'bg' => '#e0f2fe',
                'description' => __('Client portal executive dashboard, quick KPI metrics cards, and recent booking activity.')
            ),
            'clientnotifications' => array(
                'key' => 'clientnotifications',
                'name' => __('Notifications'),
                'controller' => 'ClientNotifications',
                'action' => 'index',
                'icon' => 'fa fa-bell',
                'color' => '#f59e0b',
                'bg' => '#fef3c7',
                'description' => __('Official client activity stream, verification notifications, payment receipts, and announcements.')
            ),
            'mydeals' => array(
                'key' => 'mydeals',
                'name' => __('My Deals'),
                'controller' => 'MyDeals',
                'action' => 'index',
                'icon' => 'fa fa-thumbs-up',
                'color' => '#059669',
                'bg' => '#ecfdf5',
                'description' => __('Active client property bookings, deal ledger, invoices, issued allotment letters, and contract summaries.')
            ),
            'ballotingresults' => array(
                'key' => 'ballotingresults',
                'name' => __('e-Balloting Results'),
                'controller' => 'BallotingResults',
                'action' => 'index',
                'icon' => 'fa fa-cube',
                'color' => '#0284c7',
                'bg' => '#e0f2fe',
                'description' => __('Digital plot allocation search, duplicate allotment letters, QR certificates, and queued booking draws.')
            ),
            'utilitybills' => array(
                'key' => 'utilitybills',
                'name' => __('Utility Bills'),
                'controller' => 'UtilityBills',
                'action' => 'index',
                'icon' => 'fa fa-bolt',
                'color' => '#d97706',
                'bg' => '#fef3c7',
                'description' => __('Monthly electricity and maintenance bills, meter reading snapshots, payment slips, and account statement.')
            ),
            'mypayments' => array(
                'key' => 'mypayments',
                'name' => __('My Payments'),
                'controller' => 'MyPayments',
                'action' => 'index',
                'icon' => 'fa fa-dollar',
                'color' => '#10b981',
                'bg' => '#ecfdf5',
                'description' => __('Complete verified payment receipt history, paid installments, bank references, and printable receipts.')
            ),
            'myinvpastdues' => array(
                'key' => 'myinvpastdues',
                'name' => __('Overdue Installments'),
                'controller' => 'MyInvpastdues',
                'action' => 'index',
                'icon' => 'fa fa-thumb-tack',
                'color' => '#dc2626',
                'bg' => '#fee2e2',
                'description' => __('Installment due calendar, overdue aging alerts, unpaid installment ledger, and pending payment reminders.')
            ),
            'mydocuments' => array(
                'key' => 'mydocuments',
                'name' => __('My Documents'),
                'controller' => 'MyDocuments',
                'action' => 'index',
                'icon' => 'fa fa-lock',
                'color' => '#4f46e5',
                'bg' => '#e0e7ff',
                'description' => __('Secured client document vault, official scanned forms, verified CNIC attachments, and submitted proofs.')
            ),
            'myrentals' => array(
                'key' => 'myrentals',
                'name' => __('My Rentals'),
                'controller' => 'MyRentals',
                'action' => 'index',
                'icon' => 'fa fa-key',
                'color' => '#0891b2',
                'bg' => '#cffafe',
                'description' => __('Property asset rental agreements, owner payout schedule, maintenance requests, and inspection reports.')
            ),
            'myrefundrequests' => array(
                'key' => 'myrefundrequests',
                'name' => __('Refund Requests'),
                'controller' => 'MyRefundrequests',
                'action' => 'index',
                'icon' => 'fa fa-undo',
                'color' => '#ea580c',
                'bg' => '#ffedd5',
                'description' => __('Online client refund application, document upload crop verification, review status tracking, and appeals.')
            ),
            'properties' => array(
                'key' => 'properties',
                'name' => __('Search Properties'),
                'controller' => 'Properties',
                'action' => 'index',
                'icon' => 'fa fa-building',
                'color' => '#2563eb',
                'bg' => '#dbeafe',
                'description' => __('Published property listings catalog, multi-criteria filtering, price search, and project showcase.')
            ),
            'favouriteproperties' => array(
                'key' => 'favouriteproperties',
                'name' => __('Favourite Properties'),
                'controller' => 'Favouriteproperties',
                'action' => 'index',
                'icon' => 'fa fa-heart',
                'color' => '#e11d48',
                'bg' => '#ffe4e6',
                'description' => __('Saved client properties, shortlisted units, property comparisons, and bookmark management.')
            ),
            'myprofiles' => array(
                'key' => 'myprofiles',
                'name' => __('My Profile'),
                'controller' => 'MyProfiles',
                'action' => 'index',
                'icon' => 'fa fa-user',
                'color' => '#0f172a',
                'bg' => '#f1f5f9',
                'description' => __('Client personal profile, registered contact information, postal address, and verified client details.')
            ),
            'changepasswords' => array(
                'key' => 'changepasswords',
                'name' => __('Change Password'),
                'controller' => 'Changepasswords',
                'action' => 'index',
                'icon' => 'fa fa-cog',
                'color' => '#64748b',
                'bg' => '#f1f5f9',
                'description' => __('Client portal credentials update, login security guidelines, and password management.')
            )
        );
    }

    /**
     * Map a controller name to its menu key
     */
    public function controllerToMenuKey($controllerName)
    {
        $c = strtolower(trim((string)$controllerName));
        $map = array(
            'dashboards' => 'dashboards',
            'clientnotifications' => 'clientnotifications',
            'client_notifications' => 'clientnotifications',
            'mydeals' => 'mydeals',
            'my_deals' => 'mydeals',
            'ballotingresults' => 'ballotingresults',
            'balloting_results' => 'ballotingresults',
            'myballoting' => 'ballotingresults',
            'utilitybills' => 'utilitybills',
            'utility_bills' => 'utilitybills',
            'myutilitybills' => 'utilitybills',
            'my_utility_bills' => 'utilitybills',
            'myutilitybill' => 'utilitybills',
            'billingportals' => 'utilitybills',
            'mypayments' => 'mypayments',
            'my_payments' => 'mypayments',
            'myinvpastdues' => 'myinvpastdues',
            'my_invpastdues' => 'myinvpastdues',
            'mydocuments' => 'mydocuments',
            'my_documents' => 'mydocuments',
            'myrentals' => 'myrentals',
            'my_rentals' => 'myrentals',
            'myrefundrequests' => 'myrefundrequests',
            'my_refundrequests' => 'myrefundrequests',
            'properties' => 'properties',
            'favouriteproperties' => 'favouriteproperties',
            'favourite_properties' => 'favouriteproperties',
            'myprofiles' => 'myprofiles',
            'my_profiles' => 'myprofiles',
            'changepasswords' => 'changepasswords',
            'changepassword' => 'changepasswords',
            'change_passwords' => 'changepasswords',
        );
        return isset($map[$c]) ? $map[$c] : null;
    }

    /**
     * Get settings for a specific agency (project) with fallback to global default (0)
     */
    public function getSettingsForAgency($agencyId = 0)
    {
        $this->ensureSchema();
        $agencyId = (int)$agencyId;
        $definitions = $this->getMenuDefinitions();
        $result = array();

        // 1. Initialize with defaults (all enabled with sequential default order)
        $defaultOrder = 1;
        foreach ($definitions as $key => $def) {
            $result[$key] = array(
                'menu_key' => $key,
                'name' => $def['name'],
                'controller' => $def['controller'],
                'action' => $def['action'],
                'icon' => $def['icon'],
                'color' => $def['color'],
                'bg' => $def['bg'],
                'description' => $def['description'],
                'is_enabled' => 1,
                'ordering' => $defaultOrder,
                'custom_label' => '',
                'is_customized' => 0
            );
            $defaultOrder++;
        }

        // 2. Fetch Global Defaults (agency_id = 0)
        $globalRows = $this->find('all', array(
            'conditions' => array('AgencyPortalMenu.agency_id' => 0),
            'order' => array('AgencyPortalMenu.ordering' => 'ASC'),
            'recursive' => -1
        ));
        foreach ($globalRows as $row) {
            $item = $row['AgencyPortalMenu'];
            $k = strtolower(trim($item['menu_key']));
            if (isset($result[$k])) {
                $result[$k]['is_enabled'] = (int)$item['is_enabled'];
                if (isset($item['ordering']) && (int)$item['ordering'] > 0) {
                    $result[$k]['ordering'] = (int)$item['ordering'];
                }
                if (!empty($item['custom_label'])) {
                    $result[$k]['custom_label'] = $item['custom_label'];
                }
            }
        }

        // 3. Fetch Agency-specific overrides (if agencyId > 0)
        if ($agencyId > 0) {
            $agencyRows = $this->find('all', array(
                'conditions' => array('AgencyPortalMenu.agency_id' => $agencyId),
                'order' => array('AgencyPortalMenu.ordering' => 'ASC'),
                'recursive' => -1
            ));
            foreach ($agencyRows as $row) {
                $item = $row['AgencyPortalMenu'];
                $k = strtolower(trim($item['menu_key']));
                if (isset($result[$k])) {
                    $result[$k]['is_enabled'] = (int)$item['is_enabled'];
                    $result[$k]['is_customized'] = 1;
                    if (isset($item['ordering']) && (int)$item['ordering'] > 0) {
                        $result[$k]['ordering'] = (int)$item['ordering'];
                    }
                    if (!empty($item['custom_label'])) {
                        $result[$k]['custom_label'] = $item['custom_label'];
                    }
                }
            }
        }

        // 4. Sort $result by ordering ASC
        uasort($result, function($a, $b) {
            $oa = isset($a['ordering']) ? (int)$a['ordering'] : 99;
            $ob = isset($b['ordering']) ? (int)$b['ordering'] : 99;
            if ($oa === $ob) {
                return 0;
            }
            return ($oa < $ob) ? -1 : 1;
        });

        return $result;
    }

    /**
     * Check if a specific menu key is enabled for an agency
     */
    public function isMenuEnabled($agencyId, $menuKey)
    {
        $agencyId = (int)$agencyId;
        $menuKey = strtolower(trim($menuKey));

        // 1. Check agency specific rule
        if ($agencyId > 0) {
            $record = $this->find('first', array(
                'conditions' => array(
                    'AgencyPortalMenu.agency_id' => $agencyId,
                    'AgencyPortalMenu.menu_key' => $menuKey
                ),
                'recursive' => -1
            ));
            if (!empty($record['AgencyPortalMenu'])) {
                return ((int)$record['AgencyPortalMenu']['is_enabled'] === 1);
            }
        }

        // 2. Check global rule (agency_id = 0)
        $globalRecord = $this->find('first', array(
            'conditions' => array(
                'AgencyPortalMenu.agency_id' => 0,
                'AgencyPortalMenu.menu_key' => $menuKey
            ),
            'recursive' => -1
        ));
        if (!empty($globalRecord['AgencyPortalMenu'])) {
            return ((int)$globalRecord['AgencyPortalMenu']['is_enabled'] === 1);
        }

        // 3. Default is enabled
        return true;
    }

    /**
     * Check if a controller is enabled for a given client ID or client data
     */
    public function isControllerEnabledForClient($clientId, $clientData, $controllerName)
    {
        $menuKey = $this->controllerToMenuKey($controllerName);
        if (!$menuKey) {
            return true; // Not a restricted portal controller
        }

        // Determine client's agency / project ID
        $agencyId = 0;
        if (!empty($clientData['Client']['project_id'])) {
            $agencyId = (int)$clientData['Client']['project_id'];
        } elseif (!empty($clientData['project_id'])) {
            $agencyId = (int)$clientData['project_id'];
        }

        // If client has deals in other agencies/projects, we also check if enabled in any of them
        return $this->isMenuEnabled($agencyId, $menuKey);
    }

    /**
     * Save a single toggle setting (via AJAX or form)
     */
    public function saveToggle($agencyId, $menuKey, $isEnabled, $userId = 0, $ordering = null)
    {
        $this->ensureSchema();
        $agencyId = (int)$agencyId;
        $menuKey = strtolower(trim($menuKey));
        $isEnabled = !empty($isEnabled) ? 1 : 0;
        $userId = (int)$userId;

        $existing = $this->find('first', array(
            'conditions' => array(
                'AgencyPortalMenu.agency_id' => $agencyId,
                'AgencyPortalMenu.menu_key' => $menuKey
            ),
            'recursive' => -1
        ));

        $data = array(
            'agency_id' => $agencyId,
            'menu_key' => $menuKey,
            'is_enabled' => $isEnabled,
            'updated_by' => $userId
        );

        if ($ordering !== null) {
            $data['ordering'] = (int)$ordering;
        }

        if (!empty($existing['AgencyPortalMenu']['id'])) {
            $data['id'] = (int)$existing['AgencyPortalMenu']['id'];
        } else {
            $this->create();
        }

        return $this->save($data);
    }

    /**
     * Save ordering for a list of menu keys
     * @param int $agencyId
     * @param array $orderedKeys Ordered list of menu keys
     * @param int $userId
     * @return bool
     */
    public function saveOrder($agencyId, $orderedKeys, $userId = 0)
    {
        $this->ensureSchema();
        $agencyId = (int)$agencyId;
        $userId = (int)$userId;
        $order = 1;

        foreach ((array)$orderedKeys as $key) {
            $key = strtolower(trim((string)$key));
            if (empty($key)) {
                continue;
            }

            $existing = $this->find('first', array(
                'conditions' => array(
                    'AgencyPortalMenu.agency_id' => $agencyId,
                    'AgencyPortalMenu.menu_key' => $key
                ),
                'recursive' => -1
            ));

            if (!empty($existing['AgencyPortalMenu']['id'])) {
                $this->id = (int)$existing['AgencyPortalMenu']['id'];
                $this->saveField('ordering', $order);
            } else {
                $this->create();
                $this->save(array(
                    'agency_id' => $agencyId,
                    'menu_key' => $key,
                    'is_enabled' => 1,
                    'ordering' => $order,
                    'updated_by' => $userId
                ));
            }
            $order++;
        }
        return true;
    }
}
