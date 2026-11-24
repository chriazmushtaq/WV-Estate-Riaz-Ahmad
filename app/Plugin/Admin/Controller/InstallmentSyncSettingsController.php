<?php
App::uses('AdminAppController', 'Admin.Controller');

class InstallmentSyncSettingsController extends AdminAppController
{
    public $helpers = array('Html', 'Form', 'Session');
    public $uses = array('Admin.InstallmentSyncSetting', 'Admin.Project');

    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->_ensureSuperAdminOrCeo();
    }

    protected function _ensureSuperAdminOrCeo()
    {
        $sessionUser = (array)$this->adminValue;
        $isSuper = $this->_isSuperAdminSession($sessionUser);
        $isCeo = $this->_isCeoRoleContext($sessionUser);

        if (!$isSuper && !$isCeo) {
            $this->Session->setFlash(__('Access denied. Only Super Admin and CEO can manage Installment Sync & Discount Settings.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('controller' => 'Dashboards', 'action' => 'index'));
        }
    }

    public function index()
    {
        $this->InstallmentSyncSetting->ensureSchema();
        $projectId = 0;
        if (!empty($this->request->query['project_id'])) {
            $projectId = (int)$this->request->query['project_id'];
        }

        $projects = $this->Project->find('list', array(
            'fields' => array('Project.id', 'Project.name'),
            'order' => array('Project.name' => 'ASC'),
            'recursive' => -1
        ));

        if ($this->request->is(array('post', 'put'))) {
            $data = $this->request->data;
            if (isset($data['InstallmentSyncSetting'])) {
                $saveData = $data['InstallmentSyncSetting'];
                $saveData['project_id'] = (int)$projectId;

                // Existing record check
                $existing = $this->InstallmentSyncSetting->find('first', array(
                    'conditions' => array('InstallmentSyncSetting.project_id' => $saveData['project_id']),
                    'recursive' => -1
                ));

                if (!empty($existing['InstallmentSyncSetting']['id'])) {
                    $saveData['id'] = (int)$existing['InstallmentSyncSetting']['id'];
                } else {
                    $this->InstallmentSyncSetting->create();
                }

                if ($this->InstallmentSyncSetting->save($saveData)) {
                    $this->Session->setFlash(__('Installment Sync & Early Payment Discount Settings saved successfully!'), 'flash', array('alert' => 'success'));
                    return $this->redirect(array('action' => 'index', '?' => array('project_id' => $projectId)));
                } else {
                    $this->Session->setFlash(__('Unable to save settings. Please verify input values.'), 'flash', array('alert' => 'danger'));
                }
            }
        }

        $settings = $this->InstallmentSyncSetting->getSettings($projectId);
        $this->request->data['InstallmentSyncSetting'] = $settings;

        $this->set(compact('settings', 'projects', 'projectId'));
    }
}
