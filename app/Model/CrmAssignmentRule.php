<?php
class CrmAssignmentRule extends AppModel
{
    public $useTable = 'crm_assignment_rules';
    public $cacheSources = false;

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
                "CREATE TABLE IF NOT EXISTS `crm_assignment_rules` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `project_id` int(11) NOT NULL DEFAULT 0,
                    `title` varchar(191) NOT NULL,
                    `priority` int(11) NOT NULL DEFAULT 0,
                    `status` varchar(20) NOT NULL DEFAULT 'Active',
                    `match_source` varchar(191) DEFAULT NULL,
                    `match_city` varchar(150) DEFAULT NULL,
                    `budget_min` decimal(15,2) DEFAULT NULL,
                    `budget_max` decimal(15,2) DEFAULT NULL,
                    `match_language` varchar(80) DEFAULT NULL,
                    `target_ugroup_id` int(11) NOT NULL DEFAULT 0,
                    `target_user_id` int(11) NOT NULL DEFAULT 0,
                    `notes` text,
                    `created_by` int(11) NOT NULL DEFAULT 0,
                    `modified_by` int(11) NOT NULL DEFAULT 0,
                    `created` datetime DEFAULT NULL,
                    `modified` datetime DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `project_id` (`project_id`),
                    KEY `status` (`status`),
                    KEY `target_user_id` (`target_user_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8"
            );

            $requiredColumns = array(
                'title' => "ALTER TABLE `crm_assignment_rules` ADD `title` varchar(191) NOT NULL",
                'priority' => "ALTER TABLE `crm_assignment_rules` ADD `priority` int(11) NOT NULL DEFAULT 0",
                'status' => "ALTER TABLE `crm_assignment_rules` ADD `status` varchar(20) NOT NULL DEFAULT 'Active'",
                'match_source' => "ALTER TABLE `crm_assignment_rules` ADD `match_source` varchar(191) DEFAULT NULL",
                'match_city' => "ALTER TABLE `crm_assignment_rules` ADD `match_city` varchar(150) DEFAULT NULL",
                'budget_min' => "ALTER TABLE `crm_assignment_rules` ADD `budget_min` decimal(15,2) DEFAULT NULL",
                'budget_max' => "ALTER TABLE `crm_assignment_rules` ADD `budget_max` decimal(15,2) DEFAULT NULL",
                'match_language' => "ALTER TABLE `crm_assignment_rules` ADD `match_language` varchar(80) DEFAULT NULL",
                'target_ugroup_id' => "ALTER TABLE `crm_assignment_rules` ADD `target_ugroup_id` int(11) NOT NULL DEFAULT 0",
                'target_user_id' => "ALTER TABLE `crm_assignment_rules` ADD `target_user_id` int(11) NOT NULL DEFAULT 0",
                'notes' => "ALTER TABLE `crm_assignment_rules` ADD `notes` text",
                'created_by' => "ALTER TABLE `crm_assignment_rules` ADD `created_by` int(11) NOT NULL DEFAULT 0",
                'modified_by' => "ALTER TABLE `crm_assignment_rules` ADD `modified_by` int(11) NOT NULL DEFAULT 0"
            );
            foreach($requiredColumns as $columnName => $sql)
            {
                $exists = (array)$this->query("SHOW COLUMNS FROM `crm_assignment_rules` LIKE '".addslashes($columnName)."'");
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

    public function defaultRuleRow($projectId = 0)
    {
        return array(
            'CrmAssignmentRule' => array(
                'id' => 0,
                'project_id' => (int)$projectId,
                'title' => '',
                'priority' => 0,
                'status' => 'Active',
                'match_source' => '',
                'match_city' => '',
                'budget_min' => '',
                'budget_max' => '',
                'match_language' => '',
                'target_ugroup_id' => 0,
                'target_user_id' => 0,
                'notes' => '',
                'created_by' => 0,
                'modified_by' => 0
            )
        );
    }

    protected function _sanitizeMoneyValue($value)
    {
        if($value === null)
        {
            return null;
        }
        $text = trim((string)$value);
        if($text === '')
        {
            return null;
        }
        $text = preg_replace('/[^0-9\.\-]/', '', $text);
        if($text === '' || !is_numeric($text))
        {
            return null;
        }
        return round((float)$text, 2);
    }

    public function normalizeRuleData($rawData = array(), $projectId = 0)
    {
        $rawData = (array)$rawData;
        $status = (!empty($rawData['status']) && trim((string)$rawData['status']) === 'Inactive') ? 'Inactive' : 'Active';
        return array(
            'project_id' => (int)$projectId,
            'title' => trim((string)(isset($rawData['title']) ? $rawData['title'] : '')),
            'priority' => (int)(isset($rawData['priority']) ? $rawData['priority'] : 0),
            'status' => $status,
            'match_source' => trim((string)(isset($rawData['match_source']) ? $rawData['match_source'] : '')),
            'match_city' => trim((string)(isset($rawData['match_city']) ? $rawData['match_city'] : '')),
            'budget_min' => $this->_sanitizeMoneyValue(isset($rawData['budget_min']) ? $rawData['budget_min'] : null),
            'budget_max' => $this->_sanitizeMoneyValue(isset($rawData['budget_max']) ? $rawData['budget_max'] : null),
            'match_language' => trim((string)(isset($rawData['match_language']) ? $rawData['match_language'] : '')),
            'target_ugroup_id' => (int)(isset($rawData['target_ugroup_id']) ? $rawData['target_ugroup_id'] : 0),
            'target_user_id' => (int)(isset($rawData['target_user_id']) ? $rawData['target_user_id'] : 0),
            'notes' => trim((string)(isset($rawData['notes']) ? $rawData['notes'] : ''))
        );
    }

    public function findProjectRules($projectId = 0, $includeInactive = false)
    {
        $this->ensureSchema();
        $projectId = (int)$projectId;
        if($projectId <= 0)
        {
            return array();
        }
        $conditions = array('CrmAssignmentRule.project_id' => $projectId);
        if(!$includeInactive)
        {
            $conditions['CrmAssignmentRule.status'] = 'Active';
        }
        return (array)$this->find('all', array(
            'conditions' => $conditions,
            'order' => array('CrmAssignmentRule.priority' => 'desc', 'CrmAssignmentRule.id' => 'asc'),
            'recursive' => -1
        ));
    }

    protected function _payloadText($payload, $key)
    {
        return trim((string)(isset($payload[$key]) ? $payload[$key] : ''));
    }

    protected function _payloadBudget($payload)
    {
        $payload = (array)$payload;
        if(isset($payload['lead_budget']) && trim((string)$payload['lead_budget']) !== '')
        {
            $budget = $this->_sanitizeMoneyValue($payload['lead_budget']);
            if($budget !== null)
            {
                return $budget;
            }
        }
        $remarks = trim((string)(isset($payload['remarks']) ? $payload['remarks'] : ''));
        if($remarks !== '' && preg_match('/budget\s*[:=]?\s*([0-9\.,]+)/i', $remarks, $matches) && !empty($matches[1]))
        {
            return $this->_sanitizeMoneyValue($matches[1]);
        }
        return null;
    }

    protected function _textMatches($needle, $haystack)
    {
        $needle = strtolower(trim((string)$needle));
        $haystack = strtolower(trim((string)$haystack));
        if($needle === '')
        {
            return true;
        }
        if($haystack === '')
        {
            return false;
        }
        return (strpos($haystack, $needle) !== false);
    }

    protected function _validateTargetUser($projectId, $ugroupId, $userId)
    {
        $projectId = (int)$projectId;
        $ugroupId = (int)$ugroupId;
        $userId = (int)$userId;
        if($projectId <= 0 || $userId <= 0)
        {
            return 0;
        }
        try
        {
            $User = ClassRegistry::init('User');
            $conditions = array($User->assignableScopeConditions($projectId, 'User'));
            $conditions[] = array('User.id' => $userId);
            if($ugroupId > 0)
            {
                $conditions[] = array('User.ugroup_id' => $ugroupId);
            }
            $row = (array)$User->find('first', array(
                'fields' => array('User.id'),
                'conditions' => $conditions,
                'recursive' => -1
            ));
            if(!empty($row['User']['id']))
            {
                return (int)$row['User']['id'];
            }
        }
        catch(Exception $e)
        {
        }
        return 0;
    }

    protected function _ruleMatchesPayload($rule, $payload)
    {
        $rule = isset($rule['CrmAssignmentRule']) ? (array)$rule['CrmAssignmentRule'] : (array)$rule;
        $payload = (array)$payload;
        if(!$this->_textMatches(isset($rule['match_source']) ? $rule['match_source'] : '', $this->_payloadText($payload, 'lead_source')))
        {
            return false;
        }
        if(!$this->_textMatches(isset($rule['match_city']) ? $rule['match_city'] : '', $this->_payloadText($payload, 'lead_city')))
        {
            return false;
        }
        if(!$this->_textMatches(isset($rule['match_language']) ? $rule['match_language'] : '', $this->_payloadText($payload, 'lead_language')))
        {
            return false;
        }
        $budget = $this->_payloadBudget($payload);
        $budgetMin = $this->_sanitizeMoneyValue(isset($rule['budget_min']) ? $rule['budget_min'] : null);
        $budgetMax = $this->_sanitizeMoneyValue(isset($rule['budget_max']) ? $rule['budget_max'] : null);
        if($budgetMin !== null || $budgetMax !== null)
        {
            if($budget === null)
            {
                return false;
            }
            if($budgetMin !== null && $budget < $budgetMin)
            {
                return false;
            }
            if($budgetMax !== null && $budget > $budgetMax)
            {
                return false;
            }
        }
        return true;
    }

    protected function _appendRemarkText($existingText, $appendText)
    {
        $existingText = trim((string)$existingText);
        $appendText = trim((string)$appendText);
        if($appendText === '')
        {
            return $existingText;
        }
        if($existingText === '')
        {
            return $appendText;
        }
        if(stripos($existingText, $appendText) !== false)
        {
            return $existingText;
        }
        return $existingText."\n".$appendText;
    }

    public function applyToLeadPayload($payload = array(), $settings = array(), $options = array())
    {
        $this->ensureSchema();
        $payload = (array)$payload;
        $settings = isset($settings['CrmAutomationSetting']) ? (array)$settings['CrmAutomationSetting'] : (array)$settings;
        $options = (array)$options;
        $projectId = !empty($payload['project_id']) ? (int)$payload['project_id'] : 0;
        if($projectId <= 0)
        {
            return array('success' => true, 'payload' => $payload, 'matched_rule' => array());
        }

        if(empty($settings['auto_assignment_enabled']))
        {
            return array('success' => true, 'payload' => $payload, 'matched_rule' => array());
        }

        $scope = strtolower(trim((string)(isset($options['scope']) ? $options['scope'] : 'manual')));
        if(!in_array($scope, array('manual', 'import', 'webhook'), true))
        {
            $scope = 'manual';
        }
        $scopeFlag = 'assignment_apply_on_'.$scope;
        if(isset($settings[$scopeFlag]) && empty($settings[$scopeFlag]))
        {
            return array('success' => true, 'payload' => $payload, 'matched_rule' => array());
        }

        $rules = $this->findProjectRules($projectId, false);
        if(empty($rules))
        {
            return array('success' => true, 'payload' => $payload, 'matched_rule' => array());
        }

        foreach($rules as $ruleRow)
        {
            $rule = isset($ruleRow['CrmAssignmentRule']) ? (array)$ruleRow['CrmAssignmentRule'] : array();
            if(empty($rule['id']) || !$this->_ruleMatchesPayload($rule, $payload))
            {
                continue;
            }

            $resolvedUserId = $this->_validateTargetUser($projectId, isset($rule['target_ugroup_id']) ? $rule['target_ugroup_id'] : 0, isset($rule['target_user_id']) ? $rule['target_user_id'] : 0);
            if($resolvedUserId <= 0)
            {
                continue;
            }

            $shouldOverride = !isset($settings['assignment_override_user']) || !empty($settings['assignment_override_user']);
            if($shouldOverride || empty($payload['user_id']))
            {
                $payload['user_id'] = $resolvedUserId;
            }
            $title = trim((string)(isset($rule['title']) ? $rule['title'] : ''));
            if($title !== '')
            {
                $payload['remarks'] = $this->_appendRemarkText(
                    isset($payload['remarks']) ? $payload['remarks'] : '',
                    '[Auto Assigned: '.$title.']'
                );
            }
            return array(
                'success' => true,
                'payload' => $payload,
                'matched_rule' => $rule
            );
        }

        return array('success' => true, 'payload' => $payload, 'matched_rule' => array());
    }
}
