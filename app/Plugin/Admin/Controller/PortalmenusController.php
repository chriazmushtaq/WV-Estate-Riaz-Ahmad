<?php
App::uses('AdminAppController', 'Admin.Controller');

class PortalmenusController extends AdminAppController
{
    public $helpers = array('Html', 'Form', 'Session');
    public $uses = array('AgencyPortalMenu', 'Agency');

    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->_checkPortalMenuAccess();
    }

    /**
     * Ensure user has permission to manage client portal menu settings (Super Admin & Agency Admin)
     */
    protected function _checkPortalMenuAccess()
    {
        $sessionUser = (array)$this->Session->read('User');
        if (empty($sessionUser)) {
            $sessionUser = (array)$this->adminValue;
        }

        $ugroupId = !empty($sessionUser['User']['ugroup_id']) ? (int)$sessionUser['User']['ugroup_id'] : (isset($this->adminValue['User']['ugroup_id']) ? (int)$this->adminValue['User']['ugroup_id'] : 0);

        $isSuper = ($this->_isSuperAdminSession($sessionUser) || $this->_isSuperAdminSession((array)$this->adminValue) || $ugroupId === 1 || $this->_isCeoRoleContext($sessionUser) || $this->_isCeoRoleContext((array)$this->adminValue));

        $isAgencyAdmin = ($this->_isAgencyAdminSession($sessionUser) || $this->_isAgencyAdminSession((array)$this->adminValue) || $ugroupId === 4 || (isset($this->userType) && $this->userType === 'AGY') || $this->_isAgencySystemManagerRoleContext($sessionUser) || $this->_isAgencySystemManagerRoleContext((array)$this->adminValue));

        if (!$isSuper && !$isAgencyAdmin) {
            $this->Session->setFlash(__('Access denied. You do not have permission to manage Client Portal Menu settings.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('controller' => 'Dashboards', 'action' => 'index'));
        }
    }

    /**
     * Main settings interface for Agency-based Client Portal Menu Access
     */
    public function index()
    {
        $this->AgencyPortalMenu->ensureSchema();
        $sessionUser = (array)$this->Session->read('User');
        if (empty($sessionUser)) {
            $sessionUser = (array)$this->adminValue;
        }
        $ugroupId = !empty($sessionUser['User']['ugroup_id']) ? (int)$sessionUser['User']['ugroup_id'] : (isset($this->adminValue['User']['ugroup_id']) ? (int)$this->adminValue['User']['ugroup_id'] : 0);

        $isSuper = ($this->_isSuperAdminSession($sessionUser) || $this->_isSuperAdminSession((array)$this->adminValue) || $ugroupId === 1 || $this->_isCeoRoleContext($sessionUser) || $this->_isCeoRoleContext((array)$this->adminValue));

        // Load agencies list
        $agencyConditions = array();
        if (!$isSuper && (int)$this->agencyId > 0) {
            $agencyConditions['Agency.id'] = (int)$this->agencyId;
        }

        $agencyList = (array)$this->Agency->find('list', array(
            'fields' => array('Agency.id', 'Agency.name'),
            'conditions' => $agencyConditions,
            'order' => array('Agency.name' => 'ASC'),
            'recursive' => -1
        ));

        // Determine active agency ID
        $selectedAgencyId = 0;
        if (!$isSuper && (int)$this->agencyId > 0) {
            $selectedAgencyId = (int)$this->agencyId;
        } elseif (isset($this->request->query['agency_id'])) {
            $selectedAgencyId = (int)$this->request->query['agency_id'];
        } elseif (isset($this->request->query['project_id'])) {
            $selectedAgencyId = (int)$this->request->query['project_id'];
        }

        // Handle full form submit
        if ($this->request->is(array('post', 'put'))) {
            $submittedAgencyId = isset($this->request->data['AgencyPortalMenu']['agency_id']) ? (int)$this->request->data['AgencyPortalMenu']['agency_id'] : $selectedAgencyId;
            if (!$isSuper && (int)$this->agencyId > 0) {
                $submittedAgencyId = (int)$this->agencyId;
            }

            $menuToggles = isset($this->request->data['MenuToggle']) ? (array)$this->request->data['MenuToggle'] : array();
            $orderedKeys = isset($this->request->data['ordered_keys']) ? explode(',', (string)$this->request->data['ordered_keys']) : array();
            $menuOrders = isset($this->request->data['MenuOrder']) ? (array)$this->request->data['MenuOrder'] : array();
            $definitions = $this->AgencyPortalMenu->getMenuDefinitions();
            $savedCount = 0;

            if (!empty($orderedKeys) && count(array_filter($orderedKeys)) > 0) {
                $orderIndex = 1;
                foreach ($orderedKeys as $key) {
                    $key = strtolower(trim($key));
                    if (isset($definitions[$key])) {
                        $isEnabled = !empty($menuToggles[$key]) ? 1 : 0;
                        $this->AgencyPortalMenu->saveToggle($submittedAgencyId, $key, $isEnabled, (int)$this->luserId, $orderIndex);
                        $savedCount++;
                        $orderIndex++;
                    }
                }
            } else {
                $orderIndex = 1;
                foreach ($definitions as $key => $def) {
                    $isEnabled = !empty($menuToggles[$key]) ? 1 : 0;
                    $ord = isset($menuOrders[$key]) ? (int)$menuOrders[$key] : $orderIndex;
                    $this->AgencyPortalMenu->saveToggle($submittedAgencyId, $key, $isEnabled, (int)$this->luserId, $ord);
                    $savedCount++;
                    $orderIndex++;
                }
            }

            $agencyLabel = ($submittedAgencyId > 0 && isset($agencyList[$submittedAgencyId])) ? $agencyList[$submittedAgencyId] : __('Global Default (All Agencies)');
            $this->Session->setFlash(sprintf(__('Client portal menu settings and ordering for "%s" saved successfully!'), $agencyLabel), 'flash', array('alert' => 'success'));
            return $this->redirect(array('controller' => 'Portalmenus', 'action' => 'index', '?' => array('agency_id' => $submittedAgencyId)));
        }

        $menus = $this->AgencyPortalMenu->getSettingsForAgency($selectedAgencyId);

        // Count enabled and disabled
        $enabledCount = 0;
        $disabledCount = 0;
        foreach ($menus as $m) {
            if (!empty($m['is_enabled'])) {
                $enabledCount++;
            } else {
                $disabledCount++;
            }
        }

        $selectedAgencyName = ($selectedAgencyId > 0 && isset($agencyList[$selectedAgencyId])) ? $agencyList[$selectedAgencyId] : __('Global Default (All Agencies)');

        $this->set(compact('menus', 'agencyList', 'selectedAgencyId', 'selectedAgencyName', 'isSuper', 'enabledCount', 'disabledCount'));
    }

    /**
     * AJAX action for instant real-time toggle
     */
    public function toggle()
    {
        $this->autoRender = false;
        $this->response->type('json');

        if (!$this->request->is('post')) {
            echo json_encode(array('status' => 'error', 'message' => __('Invalid request method.')));
            return;
        }

        $sessionUser = (array)$this->Session->read('User');
        if (empty($sessionUser)) {
            $sessionUser = (array)$this->adminValue;
        }
        $ugroupId = !empty($sessionUser['User']['ugroup_id']) ? (int)$sessionUser['User']['ugroup_id'] : (isset($this->adminValue['User']['ugroup_id']) ? (int)$this->adminValue['User']['ugroup_id'] : 0);
        $isSuper = ($this->_isSuperAdminSession($sessionUser) || $this->_isSuperAdminSession((array)$this->adminValue) || $ugroupId === 1 || $this->_isCeoRoleContext($sessionUser) || $this->_isCeoRoleContext((array)$this->adminValue));

        $agencyId = isset($this->request->data['agency_id']) ? (int)$this->request->data['agency_id'] : 0;
        $menuKey = isset($this->request->data['menu_key']) ? trim((string)$this->request->data['menu_key']) : '';
        $isEnabled = !empty($this->request->data['is_enabled']) ? 1 : 0;

        if (!$isSuper && (int)$this->agencyId > 0) {
            $agencyId = (int)$this->agencyId;
        }

        if (empty($menuKey)) {
            echo json_encode(array('status' => 'error', 'message' => __('Missing menu key.')));
            return;
        }

        $success = $this->AgencyPortalMenu->saveToggle($agencyId, $menuKey, $isEnabled, (int)$this->luserId);

        if ($success) {
            $definitions = $this->AgencyPortalMenu->getMenuDefinitions();
            $menuName = isset($definitions[$menuKey]['name']) ? $definitions[$menuKey]['name'] : $menuKey;
            $msg = $isEnabled ? sprintf(__('%s is now enabled on the client portal sidebar.'), $menuName) : sprintf(__('%s is now disabled and blocked for clients.'), $menuName);
            echo json_encode(array(
                'status' => 'success',
                'is_enabled' => $isEnabled,
                'menu_key' => $menuKey,
                'agency_id' => $agencyId,
                'message' => $msg
            ));
        } else {
            echo json_encode(array('status' => 'error', 'message' => __('Failed to save toggle setting.')));
        }
    }

    /**
     * Bulk toggle action (enable all or disable all)
     */
    public function bulk_toggle()
    {
        if (!$this->request->is('post')) {
            return $this->redirect(array('action' => 'index'));
        }

        $sessionUser = (array)$this->Session->read('User');
        if (empty($sessionUser)) {
            $sessionUser = (array)$this->adminValue;
        }
        $ugroupId = !empty($sessionUser['User']['ugroup_id']) ? (int)$sessionUser['User']['ugroup_id'] : (isset($this->adminValue['User']['ugroup_id']) ? (int)$this->adminValue['User']['ugroup_id'] : 0);
        $isSuper = ($this->_isSuperAdminSession($sessionUser) || $this->_isSuperAdminSession((array)$this->adminValue) || $ugroupId === 1 || $this->_isCeoRoleContext($sessionUser) || $this->_isCeoRoleContext((array)$this->adminValue));

        $agencyId = isset($this->request->data['agency_id']) ? (int)$this->request->data['agency_id'] : 0;
        $action = isset($this->request->data['bulk_action']) ? trim((string)$this->request->data['bulk_action']) : 'enable_all';

        if (!$isSuper && (int)$this->agencyId > 0) {
            $agencyId = (int)$this->agencyId;
        }

        $definitions = $this->AgencyPortalMenu->getMenuDefinitions();
        $targetState = ($action === 'enable_all') ? 1 : 0;

        foreach ($definitions as $key => $def) {
            $this->AgencyPortalMenu->saveToggle($agencyId, $key, $targetState, (int)$this->luserId);
        }

        $msg = ($targetState === 1) ? __('All client portal menus have been enabled.') : __('All client portal menus have been disabled.');
        $this->Session->setFlash($msg, 'flash', array('alert' => 'success'));
        return $this->redirect(array('controller' => 'Portalmenus', 'action' => 'index', '?' => array('agency_id' => $agencyId)));
    }

    /**
     * AJAX action to save menu drag-and-drop / up-down ordering
     */
    public function reorder()
    {
        $this->autoRender = false;
        $this->response->type('json');

        if (!$this->request->is('post')) {
            echo json_encode(array('status' => 'error', 'message' => __('Invalid request method.')));
            return;
        }

        $sessionUser = (array)$this->Session->read('User');
        if (empty($sessionUser)) {
            $sessionUser = (array)$this->adminValue;
        }
        $ugroupId = !empty($sessionUser['User']['ugroup_id']) ? (int)$sessionUser['User']['ugroup_id'] : (isset($this->adminValue['User']['ugroup_id']) ? (int)$this->adminValue['User']['ugroup_id'] : 0);
        $isSuper = ($this->_isSuperAdminSession($sessionUser) || $this->_isSuperAdminSession((array)$this->adminValue) || $ugroupId === 1 || $this->_isCeoRoleContext($sessionUser) || $this->_isCeoRoleContext((array)$this->adminValue));

        $agencyId = isset($this->request->data['agency_id']) ? (int)$this->request->data['agency_id'] : 0;
        if (!$isSuper && (int)$this->agencyId > 0) {
            $agencyId = (int)$this->agencyId;
        }

        $orderData = isset($this->request->data['order']) ? $this->request->data['order'] : array();
        if (is_string($orderData)) {
            $orderData = explode(',', $orderData);
        }
        $orderData = array_values(array_filter(array_map('trim', (array)$orderData)));

        if (empty($orderData)) {
            echo json_encode(array('status' => 'error', 'message' => __('No order data received.')));
            return;
        }

        $success = $this->AgencyPortalMenu->saveOrder($agencyId, $orderData, (int)$this->luserId);

        if ($success) {
            echo json_encode(array(
                'status' => 'success',
                'message' => __('Menu ordering saved! The client portal sidebar will display in this exact order.')
            ));
        } else {
            echo json_encode(array('status' => 'error', 'message' => __('Failed to save menu ordering.')));
        }
    }
}
