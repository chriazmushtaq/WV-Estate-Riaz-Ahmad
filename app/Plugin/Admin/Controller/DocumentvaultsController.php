<?php
App::uses('CakeTime', 'Utility');

class DocumentvaultsController extends AdminAppController
{
    public $components = array('Session', 'Paginator');
    public $helpers = array('Paginator', 'Js' => array('Jquery'));
    public $uses = array('Admin.DealDocument');

    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->_ensureDealDocumentsTable();
        $this->loadModel('Configuration');
        $cfg = $this->Configuration->findById(1);
        $dealVaultEnabled = !isset($cfg['Configuration']['deal_vault_enabled']) || (int)$cfg['Configuration']['deal_vault_enabled'] === 1;
        $dealAdminOnly = !isset($cfg['Configuration']['deal_features_admin_only']) || (int)$cfg['Configuration']['deal_features_admin_only'] === 1;
        if(!$dealVaultEnabled)
        {
            $this->Session->setFlash(__('Deal Document Vault is currently disabled in system configurations.'), 'flash', array('alert' => 'warning'));
            return $this->redirect(array('controller' => 'Deals', 'action' => 'index'));
        }
        $isAdmin = ($this->_isSuperAdminSession((array)$this->adminValue) || $this->userType === 'admin' || $this->_isCeoRoleContext((array)$this->adminValue));
        if($dealAdminOnly && !$isAdmin)
        {
            $this->Session->setFlash(__('Document Vault management is restricted to Administrators.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('controller' => 'Deals', 'action' => 'index'));
        }
    }

    public function index()
    {
        $this->loadModel('Deal');

        $documentTypeOptions = $this->_documentTypeOptions();
        $dealId = isset($this->request->query['deal_id']) ? (int)$this->request->query['deal_id'] : 0;
        $docType = isset($this->request->query['document_type']) ? trim((string)$this->request->query['document_type']) : '';
        $keyword = isset($this->request->query['keyword']) ? trim((string)$this->request->query['keyword']) : '';

        if($this->request->is('post'))
        {
            $saveResult = $this->_handleVaultUpload();
            $this->Session->setFlash($saveResult['message'], 'flash', array('alert' => $saveResult['ok'] ? 'success' : 'danger'));
            $dealIdForRedirect = !empty($this->request->data['DealDocument']['deal_id']) ? (int)$this->request->data['DealDocument']['deal_id'] : $dealId;
            if(!empty($this->request->data['DealDocument']['return_to_deal']) && $dealIdForRedirect > 0)
            {
                return $this->redirect(array('controller' => 'DealsPayments', 'action' => 'index', $dealIdForRedirect, '#' => 'deal-documents-vault-section'));
            }
            return $this->redirect(array('action' => 'index', '?' => array(
                'deal_id' => $dealIdForRedirect
            )));
        }

        $joins = $this->_documentJoins();
        $conditions = array();
        if($dealId > 0)
        {
            $conditions['DealDocument.deal_id'] = $dealId;
        }
        if($docType !== '' && isset($documentTypeOptions[$docType]))
        {
            $conditions['DealDocument.document_type'] = $docType;
        }
        if($keyword !== '')
        {
            $conditions[] = array('OR' => array(
                'Client.name LIKE' => '%'.$keyword.'%',
                'Client.phone LIKE' => '%'.$keyword.'%',
                'Client.mobile LIKE' => '%'.$keyword.'%',
                'Deal.invoice_no LIKE' => '%'.$keyword.'%',
                'Property.name LIKE' => '%'.$keyword.'%',
                'Project.name LIKE' => '%'.$keyword.'%',
                'DealDocument.document_type LIKE' => '%'.$keyword.'%',
                'DealDocument.photo LIKE' => '%'.$keyword.'%'
            ));
        }
        $permission = $this->CustomFunction->userWisePermission(null, $this->luserId, $this->userType, $this->agencyId, 'Property.project_id', 'Deal.user_id');
        if(!empty($permission))
        {
            $conditions[] = $permission;
        }

        $this->Paginator->settings = array(
            'DealDocument' => array(
                'fields' => $this->_documentFields(),
                'joins' => $joins,
                'conditions' => $conditions,
                'order' => array('DealDocument.id' => 'desc'),
                'limit' => $this->pageLimit,
                'maxLimit' => $this->maxLimit,
                'recursive' => -1
            )
        );
        try
        {
            $documents = $this->Paginator->paginate('DealDocument');
        }
        catch(Exception $e)
        {
            $documents = array();
            $this->Session->setFlash(__('Document vault table could not be prepared. Import app/Config/deal_document_vault_table.sql in phpMyAdmin, or give this database user CREATE/ALTER permission once.'), 'flash', array('alert' => 'danger'));
        }
        $dealOptions = $this->_dealOptions($dealId);
        $summary = $this->_vaultSummary($conditions, $joins);

        $this->set(compact('documents', 'dealOptions', 'documentTypeOptions', 'dealId', 'docType', 'keyword', 'summary'));
    }

    public function viewfile($id = null)
    {
        return $this->_serveDocument($id, false);
    }

    public function download($id = null)
    {
        return $this->_serveDocument($id, true);
    }

    public function delete($id = null)
    {
        if(!$this->request->is('post'))
        {
            throw new MethodNotAllowedException(__('Invalid request method.'));
        }
        $row = $this->_getDocumentRow($id);
        if(empty($row))
        {
            $this->Session->setFlash(__('Document not found or access denied.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        $doc = $row['DealDocument'];
        $path = $this->_resolveDocumentPath($doc);
        if($this->DealDocument->delete((int)$doc['id']))
        {
            if($path !== '' && file_exists($path))
            {
                @unlink($path);
            }
            $this->Session->setFlash(__('Document deleted from secure vault.'), 'flash', array('alert' => 'success'));
        }
        else
        {
            $this->Session->setFlash(__('Unable to delete document.'), 'flash', array('alert' => 'danger'));
        }
        if(!empty($this->request->query['deal_id']))
        {
            return $this->redirect(array('controller' => 'DealsPayments', 'action' => 'index', (int)$this->request->query['deal_id'], '#' => 'deal-documents-vault-section'));
        }
        return $this->redirect(array('action' => 'index'));
    }

    protected function _handleVaultUpload()
    {
        $dealId = !empty($this->request->data['DealDocument']['deal_id']) ? (int)$this->request->data['DealDocument']['deal_id'] : 0;
        $dealMeta = $this->_getDealMeta($dealId);
        if(empty($dealMeta))
        {
            return array('ok' => false, 'message' => __('Please select a valid deal/client.'));
        }
        $documentTypeOptions = $this->_documentTypeOptions();
        $docType = !empty($this->request->data['DealDocument']['document_type']) ? trim((string)$this->request->data['DealDocument']['document_type']) : 'Others';
        if(!isset($documentTypeOptions[$docType]))
        {
            $docType = 'Others';
        }
        $docDate = $this->_normalizeVaultDate(!empty($this->request->data['DealDocument']['document_date']) ? $this->request->data['DealDocument']['document_date'] : '');
        if($docDate === null)
        {
            $docDate = date('Y-m-d');
        }
        $upload = !empty($this->request->data['DealDocument']['upload_file']) ? $this->request->data['DealDocument']['upload_file'] : array();
        $uploadResult = $this->_saveVaultUpload($upload);
        if(!$uploadResult['ok'])
        {
            return $uploadResult;
        }
        $saveData = array(
            'deal_id' => (int)$dealMeta['Deal']['id'],
            'project_id' => !empty($dealMeta['Property']['project_id']) ? (int)$dealMeta['Property']['project_id'] : 0,
            'client_id' => !empty($dealMeta['Client']['id']) ? (int)$dealMeta['Client']['id'] : 0,
            'document_type' => $docType,
            'document_date' => $docDate,
            'photo' => $uploadResult['file'],
            'dir' => 'dealvault',
            'mime_type' => $uploadResult['mime'],
            'is_image' => (int)$uploadResult['is_image'],
            'created_by' => (int)$this->luserId
        );
        $saved = false;
        try
        {
            $this->DealDocument->create();
            $saved = (bool)$this->DealDocument->save(array('DealDocument' => $saveData), array('validate' => false));
        }
        catch(Exception $e)
        {
            $saved = false;
        }
        if($saved)
        {
            return array('ok' => true, 'message' => __('Document uploaded to secure vault successfully.'));
        }
        $path = $this->_vaultBasePath().$uploadResult['file'];
        if(file_exists($path))
        {
            @unlink($path);
        }
        return array('ok' => false, 'message' => __('Unable to save document record. Please confirm deal_documents table permissions.'));
    }

    protected function _serveDocument($id, $download)
    {
        $row = $this->_getDocumentRow($id);
        if(empty($row))
        {
            $this->Session->setFlash(__('Document not found or access denied.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        $doc = $row['DealDocument'];
        $path = $this->_resolveDocumentPath($doc);
        if($path === '' || !file_exists($path))
        {
            $this->Session->setFlash(__('Document file is missing from storage.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        $downloadName = $this->_downloadName($row);
        $this->response->file($path, array('download' => $download, 'name' => $downloadName));
        return $this->response;
    }

    protected function _getDocumentRow($id)
    {
        $id = (int)$id;
        if($id <= 0)
        {
            return array();
        }
        $conditions = array('DealDocument.id' => $id);
        $permission = $this->CustomFunction->userWisePermission(null, $this->luserId, $this->userType, $this->agencyId, 'Property.project_id', 'Deal.user_id');
        if(!empty($permission))
        {
            $conditions[] = $permission;
        }
        return (array)$this->DealDocument->find('first', array(
            'fields' => $this->_documentFields(),
            'joins' => $this->_documentJoins(),
            'conditions' => $conditions,
            'recursive' => -1
        ));
    }

    protected function _getDealMeta($dealId)
    {
        $this->loadModel('Deal');
        $dealId = (int)$dealId;
        if($dealId <= 0)
        {
            return array();
        }
        $conditions = array('Deal.id' => $dealId);
        $permission = $this->CustomFunction->userWisePermission(null, $this->luserId, $this->userType, $this->agencyId, 'Property.project_id', 'Deal.user_id');
        if(!empty($permission))
        {
            $conditions[] = $permission;
        }
        return (array)$this->Deal->find('first', array(
            'joins' => array(
                array('table' => 'clients', 'alias' => 'Client', 'type' => 'LEFT', 'conditions' => array('Deal.client_id=Client.id')),
                array('table' => 'properties', 'alias' => 'Property', 'type' => 'LEFT', 'conditions' => array('Deal.property_id=Property.id')),
                array('table' => 'projects', 'alias' => 'Project', 'type' => 'LEFT', 'conditions' => array('Property.project_id=Project.id'))
            ),
            'fields' => array('Deal.id', 'Deal.invoice_no', 'Deal.user_id', 'Client.id', 'Client.name', 'Property.id', 'Property.name', 'Property.project_id', 'Project.id', 'Project.name'),
            'conditions' => $conditions,
            'recursive' => -1
        ));
    }

    protected function _dealOptions($selectedDealId = 0)
    {
        $this->loadModel('Deal');
        $conditions = array();
        $permission = $this->CustomFunction->userWisePermission(null, $this->luserId, $this->userType, $this->agencyId, 'Property.project_id', 'Deal.user_id');
        if(!empty($permission))
        {
            $conditions[] = $permission;
        }
        if((int)$selectedDealId > 0)
        {
            $conditions[] = array('OR' => array('Deal.id' => (int)$selectedDealId, 'Deal.id >' => 0));
        }
        $rows = (array)$this->Deal->find('all', array(
            'joins' => array(
                array('table' => 'clients', 'alias' => 'Client', 'type' => 'LEFT', 'conditions' => array('Deal.client_id=Client.id')),
                array('table' => 'properties', 'alias' => 'Property', 'type' => 'LEFT', 'conditions' => array('Deal.property_id=Property.id')),
                array('table' => 'projects', 'alias' => 'Project', 'type' => 'LEFT', 'conditions' => array('Property.project_id=Project.id'))
            ),
            'fields' => array('Deal.id', 'Deal.invoice_no', 'Client.name', 'Property.name', 'Project.name'),
            'conditions' => $conditions,
            'order' => array('Deal.id' => 'desc'),
            'limit' => 300,
            'recursive' => -1
        ));
        $options = array();
        foreach($rows as $row)
        {
            $label = trim((!empty($row['Client']['name']) ? $row['Client']['name'] : '-').' | '.(!empty($row['Deal']['invoice_no']) ? $row['Deal']['invoice_no'] : '-').' | '.(!empty($row['Property']['name']) ? $row['Property']['name'] : '-').' | '.(!empty($row['Project']['name']) ? $row['Project']['name'] : '-'));
            $options[(int)$row['Deal']['id']] = $label;
        }
        return $options;
    }

    protected function _documentJoins()
    {
        return array(
            array('table' => 'deals', 'alias' => 'Deal', 'type' => 'LEFT', 'conditions' => array('DealDocument.deal_id=Deal.id')),
            array('table' => 'clients', 'alias' => 'Client', 'type' => 'LEFT', 'conditions' => array('DealDocument.client_id=Client.id')),
            array('table' => 'properties', 'alias' => 'Property', 'type' => 'LEFT', 'conditions' => array('Deal.property_id=Property.id')),
            array('table' => 'projects', 'alias' => 'Project', 'type' => 'LEFT', 'conditions' => array('DealDocument.project_id=Project.id'))
        );
    }

    protected function _documentFields()
    {
        return array(
            'DealDocument.*',
            'Deal.id', 'Deal.invoice_no', 'Deal.user_id',
            'Client.id', 'Client.name', 'Client.phone', 'Client.mobile', 'Client.email',
            'Property.id', 'Property.name', 'Property.project_id',
            'Project.id', 'Project.name'
        );
    }

    protected function _documentTypeOptions()
    {
        return array(
            'CNIC' => __('CNIC'),
            'Booking Form' => __('Booking Form'),
            'Allotment Letter' => __('Allotment Letter'),
            'Agreement' => __('Agreement'),
            'Payment Proof' => __('Payment Proof'),
            'Others' => __('Others')
        );
    }

    protected function _vaultSummary($conditions, $joins)
    {
        $summary = array('total' => 0, 'cnic' => 0, 'agreement' => 0, 'payment_proof' => 0);
        try
        {
            $rows = (array)$this->DealDocument->find('all', array(
                'fields' => array('DealDocument.id', 'DealDocument.document_type'),
                'joins' => $joins,
                'conditions' => $conditions,
                'recursive' => -1
            ));
            foreach($rows as $row)
            {
                $summary['total']++;
                $type = !empty($row['DealDocument']['document_type']) ? $row['DealDocument']['document_type'] : '';
                if($type === 'CNIC')
                {
                    $summary['cnic']++;
                }
                elseif($type === 'Agreement')
                {
                    $summary['agreement']++;
                }
                elseif($type === 'Payment Proof')
                {
                    $summary['payment_proof']++;
                }
            }
        }
        catch(Exception $e)
        {
            // Summary cards are optional.
        }
        return $summary;
    }

    protected function _saveVaultUpload($upload)
    {
        $allowedExt = array('pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'gif', 'webp');
        $result = array('ok' => false, 'message' => __('Please select a valid document file.'), 'file' => '', 'mime' => '', 'is_image' => 0);
        if(empty($upload) || empty($upload['tmp_name']))
        {
            $result['message'] = __('Please select a document file.');
            return $result;
        }
        $tmpName = (string)$upload['tmp_name'];
        $isUploaded = @is_uploaded_file($tmpName);
        if(!$isUploaded && !@file_exists($tmpName))
        {
            $result['message'] = __('Uploaded file could not be validated.');
            return $result;
        }
        $originalName = isset($upload['name']) ? trim((string)$upload['name']) : '';
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        if($extension === '' || !in_array($extension, $allowedExt))
        {
            $result['message'] = __('Invalid file type. Allowed: PDF, DOC, DOCX, JPG, JPEG, PNG, GIF, WEBP.');
            return $result;
        }
        $targetDir = $this->_vaultBasePath();
        if(!is_dir($targetDir))
        {
            @mkdir($targetDir, 0775, true);
        }
        if(!is_dir($targetDir) || !is_writable($targetDir))
        {
            $result['message'] = __('Secure vault storage is not writable.');
            return $result;
        }
        $safeBase = preg_replace('/[^A-Za-z0-9_-]/', '', pathinfo($originalName, PATHINFO_FILENAME));
        if($safeBase === '')
        {
            $safeBase = 'vault_document';
        }
        $fileName = date('YmdHis').'_'.mt_rand(1000, 9999).'_'.$safeBase.'.'.$extension;
        $targetFile = $targetDir.$fileName;
        $moved = false;
        if($isUploaded)
        {
            $moved = @move_uploaded_file($tmpName, $targetFile);
        }
        if(!$moved)
        {
            $moved = @rename($tmpName, $targetFile);
        }
        if(!$moved)
        {
            $moved = @copy($tmpName, $targetFile);
            if($moved)
            {
                @unlink($tmpName);
            }
        }
        if(!$moved)
        {
            $result['message'] = __('Unable to upload file to secure vault.');
            return $result;
        }
        $mime = '';
        if(function_exists('mime_content_type'))
        {
            $detected = @mime_content_type($targetFile);
            if($detected !== false)
            {
                $mime = (string)$detected;
            }
        }
        $result['ok'] = true;
        $result['message'] = __('Uploaded successfully.');
        $result['file'] = $fileName;
        $result['mime'] = $mime;
        $result['is_image'] = in_array($extension, array('jpg', 'jpeg', 'png', 'gif', 'webp')) ? 1 : 0;
        return $result;
    }

    protected function _normalizeVaultDate($rawDate)
    {
        $rawDate = trim((string)$rawDate);
        if($rawDate === '')
        {
            return null;
        }
        if(preg_match('/^\d{4}-\d{2}-\d{2}$/', $rawDate))
        {
            return $rawDate;
        }
        $ts = strtotime(str_replace('/', '-', $rawDate));
        if($ts === false)
        {
            return null;
        }
        return date('Y-m-d', $ts);
    }

    protected function _vaultBasePath()
    {
        return APP.'DocumentVault'.DS;
    }

    protected function _resolveDocumentPath($doc)
    {
        $file = !empty($doc['photo']) ? basename($doc['photo']) : '';
        if($file === '')
        {
            return '';
        }
        $dir = !empty($doc['dir']) ? trim(str_replace('\\', '/', $doc['dir']), '/') : 'dealvault';
        if($dir === 'dealvault')
        {
            return $this->_vaultBasePath().$file;
        }
        $dir = str_replace('..', '', $dir);
        return WWW_ROOT.'img'.DS.str_replace('/', DS, $dir).DS.$file;
    }

    protected function _downloadName($row)
    {
        $doc = $row['DealDocument'];
        $ext = strtolower(pathinfo(!empty($doc['photo']) ? $doc['photo'] : '', PATHINFO_EXTENSION));
        $base = (!empty($row['Deal']['invoice_no']) ? $row['Deal']['invoice_no'].'_' : '').(!empty($doc['document_type']) ? $doc['document_type'] : 'document');
        $base = preg_replace('/[^A-Za-z0-9_-]/', '_', $base);
        return $base.($ext !== '' ? '.'.$ext : '');
    }

    protected function _ensureDealDocumentsTable()
    {
        try
        {
            $this->DealDocument->query("CREATE TABLE IF NOT EXISTS `deal_documents` (
                `id` bigint(20) NOT NULL AUTO_INCREMENT,
                `deal_id` int(11) NOT NULL DEFAULT 0,
                `project_id` int(11) NOT NULL DEFAULT 0,
                `client_id` int(11) NOT NULL DEFAULT 0,
                `document_type` varchar(40) NOT NULL DEFAULT 'Others',
                `document_date` date DEFAULT NULL,
                `photo` varchar(255) DEFAULT '',
                `dir` varchar(120) DEFAULT 'dealvault',
                `mime_type` varchar(120) DEFAULT '',
                `is_image` tinyint(1) NOT NULL DEFAULT 0,
                `created_by` int(11) NOT NULL DEFAULT 0,
                `created` datetime DEFAULT NULL,
                `modified` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `deal_id` (`deal_id`),
                KEY `project_id` (`project_id`),
                KEY `client_id` (`client_id`),
                KEY `document_type` (`document_type`),
                KEY `document_date` (`document_date`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8");
        }
        catch(Exception $e)
        {
            // Live cPanel without CREATE permission can import app/Config/deal_document_vault_table.sql.
        }
        $this->_ensureDealDocumentColumns();
    }

    protected function _ensureDealDocumentColumns()
    {
        $columns = array();
        try
        {
            $rows = (array)$this->DealDocument->query("SHOW COLUMNS FROM `deal_documents`");
            foreach($rows as $row)
            {
                $field = '';
                if(!empty($row['COLUMNS']['Field']))
                {
                    $field = $row['COLUMNS']['Field'];
                }
                elseif(!empty($row[0]['Field']))
                {
                    $field = $row[0]['Field'];
                }
                if($field !== '')
                {
                    $columns[$field] = true;
                }
            }
        }
        catch(Exception $e)
        {
            return;
        }
        $alterMap = array(
            'project_id' => "ALTER TABLE `deal_documents` ADD COLUMN `project_id` int(11) NOT NULL DEFAULT 0 AFTER `deal_id`",
            'client_id' => "ALTER TABLE `deal_documents` ADD COLUMN `client_id` int(11) NOT NULL DEFAULT 0 AFTER `project_id`",
            'photo' => "ALTER TABLE `deal_documents` ADD COLUMN `photo` varchar(255) DEFAULT '' AFTER `document_date`",
            'dir' => "ALTER TABLE `deal_documents` ADD COLUMN `dir` varchar(120) DEFAULT 'dealvault' AFTER `photo`",
            'mime_type' => "ALTER TABLE `deal_documents` ADD COLUMN `mime_type` varchar(120) DEFAULT '' AFTER `dir`",
            'is_image' => "ALTER TABLE `deal_documents` ADD COLUMN `is_image` tinyint(1) NOT NULL DEFAULT 0 AFTER `mime_type`",
            'created_by' => "ALTER TABLE `deal_documents` ADD COLUMN `created_by` int(11) NOT NULL DEFAULT 0 AFTER `is_image`"
        );
        foreach($alterMap as $field => $sql)
        {
            if(empty($columns[$field]))
            {
                try
                {
                    $this->DealDocument->query($sql);
                }
                catch(Exception $e)
                {
                    // Missing ALTER permission will be reported when saving if a required column is unavailable.
                }
            }
        }
    }
}
?>
