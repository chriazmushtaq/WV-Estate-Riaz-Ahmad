<?php
App::uses('CakeTime', 'Utility');

class LeadHistory extends AppModel
{
    public $useTable = 'lead_histories';
    public $cacheSources = false;

    protected $_labelMap = array(
        'lead_name' => 'Lead Name',
        'lead_mobile' => 'Lead Mobile',
        'lead_email' => 'Lead Email',
        'lead_address' => 'Lead Address',
        'lead_city' => 'City',
        'lead_budget' => 'Budget',
        'lead_language' => 'Language',
        'lead_source' => 'Lead Source',
        'source_form_id' => 'Source Form ID',
        'source_ad_id' => 'Source Ad ID',
        'utm_source' => 'UTM Source',
        'utm_campaign' => 'UTM Campaign',
        'utm_medium' => 'UTM Medium',
        'utm_term' => 'UTM Term',
        'utm_content' => 'UTM Content',
        'project_id' => 'Agency',
        'user_id' => 'Assigned User',
        'client_id' => 'Client',
        'property_id' => 'Property',
        'status_id' => 'Status',
        'last_contact_date' => 'Last Contact Date',
        'follow_up' => 'Follow Up',
        'remarks' => 'Remarks',
        'lead_temperature' => 'Lead Temperature',
        'type_id' => 'Lead Type'
    );

    public function ensureSchema()
    {
        static $isEnsured = null;
        if($isEnsured !== null)
        {
            return $isEnsured;
        }
        $isEnsured = false;
        try
        {
            $this->query(
                "CREATE TABLE IF NOT EXISTS `lead_histories` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `lead_id` int(11) NOT NULL,
                    `project_id` int(11) DEFAULT NULL,
                    `lead_user_id` int(11) DEFAULT NULL,
                    `actor_user_id` int(11) DEFAULT NULL,
                    `actor_name` varchar(191) DEFAULT NULL,
                    `actor_role` varchar(191) DEFAULT NULL,
                    `entry_type` varchar(50) NOT NULL DEFAULT 'change',
                    `title` varchar(255) DEFAULT NULL,
                    `field_name` varchar(100) DEFAULT NULL,
                    `field_label` varchar(191) DEFAULT NULL,
                    `old_value` text,
                    `new_value` text,
                    `recipient` varchar(255) DEFAULT NULL,
                    `message_body` longtext,
                    `meta_json` longtext,
                    `created` datetime DEFAULT NULL,
                    `modified` datetime DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `lead_histories_lead_created` (`lead_id`,`created`),
                    KEY `lead_histories_project` (`project_id`),
                    KEY `lead_histories_actor` (`actor_user_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8"
            );

            $requiredColumns = array(
                'project_id' => "ALTER TABLE `lead_histories` ADD `project_id` int(11) DEFAULT NULL",
                'lead_user_id' => "ALTER TABLE `lead_histories` ADD `lead_user_id` int(11) DEFAULT NULL",
                'actor_user_id' => "ALTER TABLE `lead_histories` ADD `actor_user_id` int(11) DEFAULT NULL",
                'actor_name' => "ALTER TABLE `lead_histories` ADD `actor_name` varchar(191) DEFAULT NULL",
                'actor_role' => "ALTER TABLE `lead_histories` ADD `actor_role` varchar(191) DEFAULT NULL",
                'entry_type' => "ALTER TABLE `lead_histories` ADD `entry_type` varchar(50) NOT NULL DEFAULT 'change'",
                'title' => "ALTER TABLE `lead_histories` ADD `title` varchar(255) DEFAULT NULL",
                'field_name' => "ALTER TABLE `lead_histories` ADD `field_name` varchar(100) DEFAULT NULL",
                'field_label' => "ALTER TABLE `lead_histories` ADD `field_label` varchar(191) DEFAULT NULL",
                'old_value' => "ALTER TABLE `lead_histories` ADD `old_value` text NULL",
                'new_value' => "ALTER TABLE `lead_histories` ADD `new_value` text NULL",
                'recipient' => "ALTER TABLE `lead_histories` ADD `recipient` varchar(255) DEFAULT NULL",
                'message_body' => "ALTER TABLE `lead_histories` ADD `message_body` longtext NULL",
                'meta_json' => "ALTER TABLE `lead_histories` ADD `meta_json` longtext NULL"
            );
            foreach($requiredColumns as $columnName => $sql)
            {
                $exists = (array)$this->query("SHOW COLUMNS FROM `lead_histories` LIKE '".addslashes($columnName)."'");
                if(empty($exists))
                {
                    $this->query($sql);
                }
            }
            $isEnsured = true;
        }
        catch(Exception $e)
        {
            $isEnsured = false;
        }
        return $isEnsured;
    }

    public function fieldLabel($fieldName)
    {
        $fieldName = trim((string)$fieldName);
        if($fieldName === '')
        {
            return '';
        }
        if(isset($this->_labelMap[$fieldName]))
        {
            return (string)$this->_labelMap[$fieldName];
        }
        return ucwords(str_replace('_', ' ', $fieldName));
    }

    public function trackableFields()
    {
        return array_keys($this->_labelMap);
    }

    public function ignoredFields()
    {
        return array(
            'id',
            'created',
            'modified',
            'kanban_position',
            'google_calendar_event_id',
            'google_calendar_synced_at'
        );
    }

    public function createEntry($data = array())
    {
        $this->ensureSchema();
        $saveData = array(
            'lead_id' => !empty($data['lead_id']) ? (int)$data['lead_id'] : 0,
            'project_id' => isset($data['project_id']) ? (int)$data['project_id'] : null,
            'lead_user_id' => isset($data['lead_user_id']) ? (int)$data['lead_user_id'] : null,
            'actor_user_id' => isset($data['actor_user_id']) ? (int)$data['actor_user_id'] : null,
            'actor_name' => isset($data['actor_name']) ? trim((string)$data['actor_name']) : '',
            'actor_role' => isset($data['actor_role']) ? trim((string)$data['actor_role']) : '',
            'entry_type' => isset($data['entry_type']) ? trim((string)$data['entry_type']) : 'change',
            'title' => isset($data['title']) ? trim((string)$data['title']) : '',
            'field_name' => isset($data['field_name']) ? trim((string)$data['field_name']) : '',
            'field_label' => isset($data['field_label']) ? trim((string)$data['field_label']) : '',
            'old_value' => isset($data['old_value']) ? (string)$data['old_value'] : null,
            'new_value' => isset($data['new_value']) ? (string)$data['new_value'] : null,
            'recipient' => isset($data['recipient']) ? trim((string)$data['recipient']) : '',
            'message_body' => isset($data['message_body']) ? (string)$data['message_body'] : null,
            'meta_json' => null
        );
        if(isset($data['meta_json']))
        {
            if(is_array($data['meta_json']))
            {
                $saveData['meta_json'] = json_encode($data['meta_json']);
            }
            else
            {
                $saveData['meta_json'] = (string)$data['meta_json'];
            }
        }
        $this->create();
        return (bool)$this->save(array('LeadHistory' => $saveData), array('validate' => false));
    }

    public function buildCreateSummary($leadRow = array())
    {
        $leadData = isset($leadRow['Lead']) ? (array)$leadRow['Lead'] : array();
        $parts = array();
        $name = trim((string)(isset($leadData['lead_name']) ? $leadData['lead_name'] : ''));
        if($name !== '')
        {
            $parts[] = 'Lead Name: '.$name;
        }
        $mobile = trim((string)(isset($leadData['lead_mobile']) ? $leadData['lead_mobile'] : ''));
        if($mobile !== '')
        {
            $parts[] = 'Lead Mobile: '.$mobile;
        }
        $email = trim((string)(isset($leadData['lead_email']) ? $leadData['lead_email'] : ''));
        if($email !== '')
        {
            $parts[] = 'Lead Email: '.$email;
        }
        $source = trim((string)(isset($leadData['lead_source']) ? $leadData['lead_source'] : ''));
        if($source !== '')
        {
            $parts[] = 'Lead Source: '.$source;
        }
        $lastContact = $this->displayValue('last_contact_date', isset($leadData['last_contact_date']) ? $leadData['last_contact_date'] : null);
        if($lastContact !== '')
        {
            $parts[] = 'Last Contact Date: '.$lastContact;
        }
        $followUp = $this->displayValue('follow_up', isset($leadData['follow_up']) ? $leadData['follow_up'] : null);
        if($followUp !== '')
        {
            $parts[] = 'Follow Up: '.$followUp;
        }
        $remarks = trim((string)(isset($leadData['remarks']) ? $leadData['remarks'] : ''));
        if($remarks !== '')
        {
            $parts[] = 'Remarks: '.$remarks;
        }
        return implode("\n", $parts);
    }

    public function displayValue($fieldName, $value, $context = array())
    {
        if($value === null)
        {
            return '';
        }
        if(is_string($value))
        {
            $value = trim($value);
        }
        if($value === '' || $value === '0000-00-00' || $value === '0000-00-00 00:00:00')
        {
            return '';
        }
        switch((string)$fieldName)
        {
            case 'last_contact_date':
                $ts = strtotime((string)$value);
                return $ts ? date('d-M-Y', $ts) : (string)$value;
            case 'follow_up':
                $ts = strtotime((string)$value);
                return $ts ? date('d-M-Y h:i A', $ts) : (string)$value;
            case 'project_id':
                $mapped = $this->_lookupName('Project', (int)$value);
                return $mapped !== '' ? $mapped : (string)$value;
            case 'user_id':
                $mapped = $this->_lookupName('User', (int)$value);
                return $mapped !== '' ? $mapped : (string)$value;
            case 'client_id':
                $mapped = $this->_lookupName('Client', (int)$value);
                return $mapped !== '' ? $mapped : (string)$value;
            case 'property_id':
                $mapped = $this->_lookupName('Property', (int)$value);
                return $mapped !== '' ? $mapped : (string)$value;
            case 'status_id':
                $mapped = $this->_lookupName('Status', (int)$value);
                return $mapped !== '' ? $mapped : (string)$value;
            case 'type_id':
                $mapped = $this->_lookupName('Type', (int)$value);
                return $mapped !== '' ? $mapped : (string)$value;
            case 'lead_budget':
                return is_numeric($value) ? number_format((float)$value, 2) : (string)$value;
        }
        return is_scalar($value) ? (string)$value : json_encode($value);
    }

    protected function _lookupName($modelName, $id)
    {
        static $cache = array();
        $id = (int)$id;
        if($id <= 0)
        {
            return '';
        }
        if(isset($cache[$modelName][$id]))
        {
            return $cache[$modelName][$id];
        }
        $cache[$modelName][$id] = '';
        try
        {
            $Model = ClassRegistry::init($modelName);
            if($Model)
            {
                $displayField = property_exists($Model, 'displayField') && !empty($Model->displayField) ? (string)$Model->displayField : 'name';
                $name = $Model->field($displayField, array($modelName.'.id' => $id));
                if($name === null && $displayField !== 'name')
                {
                    $name = $Model->field('name', array($modelName.'.id' => $id));
                }
                if($name === null && $displayField !== 'title')
                {
                    $name = $Model->field('title', array($modelName.'.id' => $id));
                }
                $cache[$modelName][$id] = trim((string)$name);
            }
        }
        catch(Exception $e)
        {
            $cache[$modelName][$id] = '';
        }
        return $cache[$modelName][$id];
    }
}
