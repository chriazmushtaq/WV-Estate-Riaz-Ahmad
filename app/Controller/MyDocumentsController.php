<?php
App::uses('AppController', 'Controller');

class MyDocumentsController extends AppController
{
    public $helpers = array('Html', 'Session', 'Time', 'Number');
    public $uses = array('MyDeal', 'MyPayment', 'Admin.DealDocument', 'Admin.Realestateletter');
    protected $_portalVisibleDealStatuses = array('Active', 'Blocked');

    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->authenticate();
        $this->userId = $this->userValue['Client']['id'];
        $this->_ensureClientDealDocumentsTable();
    }

    protected function _dealVisibilityCondition($alias = 'Deal')
    {
        if(!$this->MyDeal->hasField('deal_status'))
        {
            return array();
        }
        return array('OR' => array(
            array($alias.'.deal_status' => null),
            array($alias.'.deal_status' => ''),
            array($alias.'.deal_status' => $this->_portalVisibleDealStatuses)
        ));
    }

    public function index()
    {
        $deals = $this->MyDeal->find('all', array(
            'conditions' => array('MyDeal.client_id' => $this->userId, $this->_dealVisibilityCondition('MyDeal')),
            'order' => array('MyDeal.id' => 'desc'),
            'recursive' => 0
        ));
        $dealIds = array();
        foreach((array)$deals as $row)
        {
            if(!empty($row['MyDeal']['id']))
            {
                $dealIds[] = (int)$row['MyDeal']['id'];
            }
        }

        $receipts = $letters = $vaultDocuments = array();
        if(!empty($dealIds))
        {
            $receipts = $this->MyPayment->find('all', array(
                'fields' => array('MyPayment.*', 'Deal.id', 'Deal.invoice_no', 'Property.name'),
                'joins' => array(
                    array('table' => 'deals', 'alias' => 'Deal', 'type' => 'LEFT', 'conditions' => array('MyPayment.deal_id=Deal.id')),
                    array('table' => 'properties', 'alias' => 'Property', 'type' => 'LEFT', 'conditions' => array('Deal.property_id=Property.id'))
                ),
                'conditions' => array('MyPayment.deal_id' => $dealIds),
                'order' => array('MyPayment.id' => 'desc'),
                'recursive' => -1
            ));
            $letters = $this->Realestateletter->find('all', array(
                'fields' => array('Realestateletter.*', 'Deal.id', 'Deal.invoice_no', 'Property.name'),
                'joins' => array(
                    array('table' => 'deals', 'alias' => 'Deal', 'type' => 'LEFT', 'conditions' => array('Realestateletter.deal_id=Deal.id')),
                    array('table' => 'properties', 'alias' => 'Property', 'type' => 'LEFT', 'conditions' => array('Deal.property_id=Property.id'))
                ),
                'conditions' => array('Realestateletter.deal_id' => $dealIds, 'Realestateletter.status' => 'Published'),
                'order' => array('Realestateletter.id' => 'desc'),
                'recursive' => -1
            ));
            $vaultDocuments = $this->DealDocument->find('all', array(
                'fields' => array('DealDocument.*', 'Deal.id', 'Deal.invoice_no', 'Property.name'),
                'joins' => array(
                    array('table' => 'deals', 'alias' => 'Deal', 'type' => 'LEFT', 'conditions' => array('DealDocument.deal_id=Deal.id')),
                    array('table' => 'properties', 'alias' => 'Property', 'type' => 'LEFT', 'conditions' => array('Deal.property_id=Property.id'))
                ),
                'conditions' => array('DealDocument.deal_id' => $dealIds, 'DealDocument.client_id' => $this->userId),
                'order' => array('DealDocument.id' => 'desc'),
                'recursive' => -1
            ));
        }
        $this->set(compact('deals', 'receipts', 'letters', 'vaultDocuments'));
    }

    public function download($id = null)
    {
        return $this->_serveVaultDocument($id, true);
    }

    public function viewfile($id = null)
    {
        return $this->_serveVaultDocument($id, false);
    }

    protected function _serveVaultDocument($id, $download)
    {
        $row = $this->_clientVaultDocument($id);
        if(empty($row['DealDocument']['id']))
        {
            $this->Session->setFlash(__('Document not found.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        $path = $this->_resolveDocumentPath($row['DealDocument']);
        if($path === '' || !file_exists($path))
        {
            $this->Session->setFlash(__('Document file is missing.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        $downloadName = $this->_downloadName($row);
        $this->response->file($path, array('download' => $download, 'name' => $downloadName));
        return $this->response;
    }

    protected function _clientVaultDocument($id)
    {
        $id = (int)$id;
        if($id <= 0)
        {
            return array();
        }
        return (array)$this->DealDocument->find('first', array(
            'fields' => array('DealDocument.*', 'Deal.id', 'Deal.invoice_no', 'Property.name'),
            'joins' => array(
                array('table' => 'deals', 'alias' => 'Deal', 'type' => 'LEFT', 'conditions' => array('DealDocument.deal_id=Deal.id')),
                array('table' => 'properties', 'alias' => 'Property', 'type' => 'LEFT', 'conditions' => array('Deal.property_id=Property.id'))
            ),
            'conditions' => array('DealDocument.id' => $id, 'DealDocument.client_id' => $this->userId, 'Deal.client_id' => $this->userId),
            'recursive' => -1
        ));
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
            return APP.'DocumentVault'.DS.$file;
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

    protected function _ensureClientDealDocumentsTable()
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
            // The page can still load receipts/letters if vault table creation is blocked.
        }
    }
}
?>
