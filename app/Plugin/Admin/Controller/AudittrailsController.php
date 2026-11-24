<?php
class AudittrailsController extends AdminAppController
{
    public $helpers = array('Html', 'Form','Session','Paginator','Tinymce','Js'=> array('Jquery'));
    public $components = array('Session','Paginator');
    public $paginate = array('page' => 1, 'order' => array('Audittrail.id' => 'desc'));

    public function beforeFilter()
    {
        parent::beforeFilter();
        if(!$this->_isSuperAdminSession((array)$this->adminValue))
        {
            $this->Session->setFlash(__('No Permission!'),'flash',array('alert'=>'danger'));
            $this->redirect(array('controller' => 'Dashboards', 'action' => 'index'));
            exit();
        }
        $this->_ensureAuditTrailSchema();
    }

    protected function _ensureAuditTrailSchema()
    {
        $shouldRun = $this->_shouldRunControllerBootstrap('audittrails.schema', '2026-04-09.2');
        $tableExists = false;
        try
        {
            $existsRows = $this->Audittrail->query("SHOW TABLES LIKE 'audit_trails'");
            $tableExists = !empty($existsRows);
        }
        catch(Exception $e)
        {
            $tableExists = false;
        }
        if(!$shouldRun && $tableExists)
        {
            return;
        }
        $sql = "CREATE TABLE IF NOT EXISTS `audit_trails` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `user_id` int(11) DEFAULT NULL,
            `ugroup_id` int(11) DEFAULT NULL,
            `project_id` int(11) DEFAULT NULL,
            `controller` varchar(100) DEFAULT NULL,
            `action` varchar(100) DEFAULT NULL,
            `activity` varchar(50) DEFAULT NULL,
            `method` varchar(10) DEFAULT NULL,
            `url` text,
            `ip_address` varchar(45) DEFAULT NULL,
            `user_agent` varchar(255) DEFAULT NULL,
            `request_data` longtext,
            `created` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_user_id` (`user_id`),
            KEY `idx_project_id` (`project_id`),
            KEY `idx_created` (`created`),
            KEY `idx_controller` (`controller`),
            KEY `idx_activity` (`activity`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        try
        {
            $this->Audittrail->query($sql);
        }
        catch(Exception $e)
        {
            // Keep flow safe if schema creation fails.
        }
    }

    public function index()
    {
        $query = (array)$this->request->query;
        $selectedUser = 0;
        $selectedAgency = 0;
        $selectedActivity = '';
        $controllerFilter = '';
        $startDate = '';
        $endDate = '';
        $keyword = '';
        $conditions = $this->_buildAuditConditions($query, $selectedUser, $selectedAgency, $selectedActivity, $controllerFilter, $startDate, $endDate, $keyword);

        $this->Paginator->settings = $this->paginate;
        $this->Paginator->settings['conditions'] = $conditions;
        $this->Paginator->settings['limit'] = $this->pageLimit;
        $this->Paginator->settings['maxLimit'] = $this->maxLimit;
        $this->Paginator->settings['recursive'] = 0;

        $auditRows = $this->Paginator->paginate('Audittrail');
        $auditRequestDisplays = array();
        foreach((array)$auditRows as $auditRow)
        {
            $auditId = !empty($auditRow['Audittrail']['id']) ? (int)$auditRow['Audittrail']['id'] : 0;
            if($auditId > 0)
            {
                $auditRequestDisplays[$auditId] = $this->_formatAuditRequestData(isset($auditRow['Audittrail']['request_data']) ? $auditRow['Audittrail']['request_data'] : '');
            }
        }

        $userOptions = $this->Audittrail->User->find('list', array(
            'fields' => array('User.id', 'User.name'),
            'conditions' => array('User.status' => 'Active'),
            'order' => array('User.name' => 'asc')
        ));

        $agencyOptions = $this->Audittrail->Agency->find('list', array(
            'fields' => array('Agency.id', 'Agency.name'),
            'order' => array('Agency.name' => 'asc')
        ));

        $activityOptions = array(
            'view' => __('View'),
            'add' => __('Add'),
            'edit' => __('Edit'),
            'delete' => __('Delete'),
            'login' => __('Login'),
            'logout' => __('Logout'),
            'other' => __('Other')
        );

        $this->set(compact(
            'auditRows',
            'userOptions',
            'agencyOptions',
            'activityOptions',
            'selectedUser',
            'selectedAgency',
            'selectedActivity',
            'controllerFilter',
            'startDate',
            'endDate',
            'keyword'
        ));
        $this->set('auditRequestDisplays', $auditRequestDisplays);
    }
    public function exportcsv()
    {
        $this->autoRender = false;
        $this->layout = null;
        $query = (array)$this->request->query;
        $selectedUser = 0;
        $selectedAgency = 0;
        $selectedActivity = '';
        $controllerFilter = '';
        $startDate = '';
        $endDate = '';
        $keyword = '';
        $conditions = $this->_buildAuditConditions($query, $selectedUser, $selectedAgency, $selectedActivity, $controllerFilter, $startDate, $endDate, $keyword);

        $rows = $this->Audittrail->find('all', array(
            'conditions' => $conditions,
            'order' => array('Audittrail.id' => 'desc'),
            'recursive' => 0
        ));

        $filename = "audit_trail_" . date("Y-m-d_H-i-s") . ".csv";
        header("Content-Type: text/csv; charset=utf-8");
        header("Content-Disposition: attachment; filename=\"".$filename."\"");
        header("Pragma: no-cache");
        header("Expires: 0");

        $output = fopen("php://output", "w");
        fputcsv($output, array('Date','User','Agency','Activity','Controller','Action','Method','IP','URL','Request'));
        foreach($rows as $row)
        {
            $log = isset($row['Audittrail']) ? $row['Audittrail'] : array();
            $userName = !empty($row['User']['name']) ? $row['User']['name'] : '';
            $agencyName = !empty($row['Agency']['name']) ? $row['Agency']['name'] : '';
            fputcsv($output, array(
                !empty($log['created']) ? date('d-M-Y H:i:s', strtotime($log['created'])) : '',
                $userName,
                $agencyName,
                isset($log['activity']) ? $log['activity'] : '',
                isset($log['controller']) ? $log['controller'] : '',
                isset($log['action']) ? $log['action'] : '',
                isset($log['method']) ? $log['method'] : '',
                isset($log['ip_address']) ? $log['ip_address'] : '',
                isset($log['url']) ? $log['url'] : '',
                $this->_formatAuditRequestData(isset($log['request_data']) ? $log['request_data'] : '')['text']
            ));
        }
        fclose($output);
        return;
    }
    protected function _buildAuditConditions($query, &$selectedUser, &$selectedAgency, &$selectedActivity, &$controllerFilter, &$startDate, &$endDate, &$keyword)
    {
        $conditions = array();
        $selectedUser = !empty($query['user_id']) ? (int)$query['user_id'] : 0;
        if($selectedUser > 0)
        {
            $conditions['Audittrail.user_id'] = $selectedUser;
        }

        $selectedAgency = !empty($query['project_id']) ? (int)$query['project_id'] : 0;
        if($selectedAgency > 0)
        {
            $conditions['Audittrail.project_id'] = $selectedAgency;
        }

        $selectedActivity = !empty($query['activity']) ? strtolower(trim((string)$query['activity'])) : '';
        if($selectedActivity !== '')
        {
            $conditions['Audittrail.activity'] = $selectedActivity;
        }

        $controllerFilter = !empty($query['controller']) ? trim((string)$query['controller']) : '';
        if($controllerFilter !== '')
        {
            $conditions['Audittrail.controller LIKE'] = '%'.$controllerFilter.'%';
        }

        $startDateRaw = !empty($query['start']) ? trim((string)$query['start']) : '';
        $startDate = $this->_normalizeAuditFilterDate($startDateRaw);
        if($startDate !== '')
        {
            $conditions['Audittrail.created >='] = $startDate.' 00:00:00';
        }
        $endDateRaw = !empty($query['end']) ? trim((string)$query['end']) : '';
        $endDate = $this->_normalizeAuditFilterDate($endDateRaw);
        if($endDate !== '')
        {
            $conditions['Audittrail.created <='] = $endDate.' 23:59:59';
        }

        $keyword = !empty($query['q']) ? trim((string)$query['q']) : '';
        if($keyword !== '')
        {
            $conditions['OR'] = array(
                'Audittrail.url LIKE' => '%'.$keyword.'%',
                'Audittrail.ip_address LIKE' => '%'.$keyword.'%',
                'Audittrail.request_data LIKE' => '%'.$keyword.'%',
                'Audittrail.user_agent LIKE' => '%'.$keyword.'%'
            );
        }
        return $conditions;
    }
    protected function _normalizeAuditFilterDate($raw)
    {
        $raw = trim((string)$raw);
        if($raw === '')
        {
            return '';
        }
        $timestamp = strtotime($raw);
        if($timestamp === false)
        {
            return '';
        }
        return date('Y-m-d', $timestamp);
    }
    protected function _formatAuditRequestData($rawRequestData)
    {
        $rawRequestData = trim((string)$rawRequestData);
        if($rawRequestData === '' || $rawRequestData === '[]' || $rawRequestData === '{}')
        {
            return array('items' => array(), 'preview' => __('No request values'), 'text' => __('No request values'));
        }
        $decoded = json_decode($rawRequestData, true);
        if(!is_array($decoded))
        {
            $fallback = strlen($rawRequestData) > 1000 ? substr($rawRequestData, 0, 1000).'...' : $rawRequestData;
            return array('items' => array(array('label' => __('Request'), 'value' => $fallback)), 'preview' => $fallback, 'text' => $fallback);
        }

        $items = array();
        $flatten = function($value, $path = array()) use (&$flatten, &$items)
        {
            if(is_array($value))
            {
                foreach($value as $key => $childValue)
                {
                    $keyText = trim((string)$key);
                    $isWrapper = empty($path) && in_array(strtolower($keyText), array('query','data'), true);
                    $isModelWrapper = count($path) <= 1 && $keyText !== '' && ctype_upper(substr($keyText, 0, 1)) && is_array($childValue);
                    $childPath = ($isWrapper || $isModelWrapper) ? $path : array_merge($path, array($keyText));
                    $flatten($childValue, $childPath);
                }
                return;
            }
            if($value === null || $value === '')
            {
                return;
            }
            $labelParts = array();
            foreach($path as $part)
            {
                if((string)(int)$part === (string)$part)
                {
                    continue;
                }
                $part = preg_replace('/([a-z])([A-Z])/', '$1 $2', str_replace(array('_','-'), ' ', (string)$part));
                $labelParts[] = ucwords(trim($part));
            }
            $label = !empty($labelParts) ? implode(' / ', $labelParts) : __('Value');
            if(is_bool($value))
            {
                $value = $value ? __('Yes') : __('No');
            }
            elseif(is_object($value))
            {
                $value = __('Object');
            }
            $value = trim((string)$value);
            if(strlen($value) > 500)
            {
                $value = substr($value, 0, 500).'...';
            }
            $items[] = array('label' => $label, 'value' => $value);
        };
        $flatten($decoded);

        if(empty($items))
        {
            return array('items' => array(), 'preview' => __('No request values'), 'text' => __('No request values'));
        }
        $lines = array();
        $previewParts = array();
        foreach($items as $index => $item)
        {
            $lines[] = $item['label'].': '.$item['value'];
            if($index < 3)
            {
                $previewValue = strlen($item['value']) > 70 ? substr($item['value'], 0, 70).'...' : $item['value'];
                $previewParts[] = $item['label'].': '.$previewValue;
            }
        }
        if(count($items) > 3)
        {
            $previewParts[] = '+'.(count($items) - 3).' '.__('more');
        }
        return array('items' => $items, 'preview' => implode(' | ', $previewParts), 'text' => implode("\n", $lines));
    }
}
?>
