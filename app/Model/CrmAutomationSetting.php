<?php
class CrmAutomationSetting extends AppModel
{
    public $useTable = 'crm_automation_settings';
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
                "CREATE TABLE IF NOT EXISTS `crm_automation_settings` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `project_id` int(11) NOT NULL,
                    `duplicate_mode` varchar(20) NOT NULL DEFAULT 'allow',
                    `duplicate_by_mobile` tinyint(1) NOT NULL DEFAULT 1,
                    `duplicate_by_email` tinyint(1) NOT NULL DEFAULT 1,
                    `auto_followup_enabled` tinyint(1) NOT NULL DEFAULT 0,
                    `auto_followup_value` int(11) NOT NULL DEFAULT 1,
                    `auto_followup_unit` varchar(20) NOT NULL DEFAULT 'day',
                    `apply_on_manual` tinyint(1) NOT NULL DEFAULT 1,
                    `apply_on_import` tinyint(1) NOT NULL DEFAULT 1,
                    `apply_on_webhook` tinyint(1) NOT NULL DEFAULT 1,
                    `auto_assignment_enabled` tinyint(1) NOT NULL DEFAULT 0,
                    `assignment_override_user` tinyint(1) NOT NULL DEFAULT 1,
                    `assignment_apply_on_manual` tinyint(1) NOT NULL DEFAULT 1,
                    `assignment_apply_on_import` tinyint(1) NOT NULL DEFAULT 1,
                    `assignment_apply_on_webhook` tinyint(1) NOT NULL DEFAULT 1,
                    `created` datetime DEFAULT NULL,
                    `modified` datetime DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `crm_automation_settings_project_unique` (`project_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8"
            );

            $requiredColumns = array(
                'duplicate_mode' => "ALTER TABLE `crm_automation_settings` ADD `duplicate_mode` varchar(20) NOT NULL DEFAULT 'allow'",
                'duplicate_by_mobile' => "ALTER TABLE `crm_automation_settings` ADD `duplicate_by_mobile` tinyint(1) NOT NULL DEFAULT 1",
                'duplicate_by_email' => "ALTER TABLE `crm_automation_settings` ADD `duplicate_by_email` tinyint(1) NOT NULL DEFAULT 1",
                'auto_followup_enabled' => "ALTER TABLE `crm_automation_settings` ADD `auto_followup_enabled` tinyint(1) NOT NULL DEFAULT 0",
                'auto_followup_value' => "ALTER TABLE `crm_automation_settings` ADD `auto_followup_value` int(11) NOT NULL DEFAULT 1",
                'auto_followup_unit' => "ALTER TABLE `crm_automation_settings` ADD `auto_followup_unit` varchar(20) NOT NULL DEFAULT 'day'",
                'apply_on_manual' => "ALTER TABLE `crm_automation_settings` ADD `apply_on_manual` tinyint(1) NOT NULL DEFAULT 1",
                'apply_on_import' => "ALTER TABLE `crm_automation_settings` ADD `apply_on_import` tinyint(1) NOT NULL DEFAULT 1",
                'apply_on_webhook' => "ALTER TABLE `crm_automation_settings` ADD `apply_on_webhook` tinyint(1) NOT NULL DEFAULT 1",
                'auto_assignment_enabled' => "ALTER TABLE `crm_automation_settings` ADD `auto_assignment_enabled` tinyint(1) NOT NULL DEFAULT 0",
                'assignment_override_user' => "ALTER TABLE `crm_automation_settings` ADD `assignment_override_user` tinyint(1) NOT NULL DEFAULT 1",
                'assignment_apply_on_manual' => "ALTER TABLE `crm_automation_settings` ADD `assignment_apply_on_manual` tinyint(1) NOT NULL DEFAULT 1",
                'assignment_apply_on_import' => "ALTER TABLE `crm_automation_settings` ADD `assignment_apply_on_import` tinyint(1) NOT NULL DEFAULT 1",
                'assignment_apply_on_webhook' => "ALTER TABLE `crm_automation_settings` ADD `assignment_apply_on_webhook` tinyint(1) NOT NULL DEFAULT 1",
                'lost_reasons_list' => "ALTER TABLE `crm_automation_settings` ADD `lost_reasons_list` text DEFAULT NULL",
                'agenda_popup_enabled' => "ALTER TABLE `crm_automation_settings` ADD `agenda_popup_enabled` tinyint(1) NOT NULL DEFAULT 1",
                'audio_chime_enabled' => "ALTER TABLE `crm_automation_settings` ADD `audio_chime_enabled` tinyint(1) NOT NULL DEFAULT 1",
                'notification_minutes_before' => "ALTER TABLE `crm_automation_settings` ADD `notification_minutes_before` int(11) NOT NULL DEFAULT 10",
                'default_downpayment_pct' => "ALTER TABLE `crm_automation_settings` ADD `default_downpayment_pct` int(11) NOT NULL DEFAULT 20",
                'default_duration_years' => "ALTER TABLE `crm_automation_settings` ADD `default_duration_years` int(11) NOT NULL DEFAULT 3"
            );
            foreach($requiredColumns as $columnName => $sql)
            {
                $exists = (array)$this->query("SHOW COLUMNS FROM `crm_automation_settings` LIKE '".addslashes($columnName)."'");
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

    public function defaultProjectRow($projectId = 0)
    {
        return array(
            'CrmAutomationSetting' => array(
                'id' => 0,
                'project_id' => (int)$projectId,
                'duplicate_mode' => 'allow',
                'duplicate_by_mobile' => 1,
                'duplicate_by_email' => 1,
                'auto_followup_enabled' => 0,
                'auto_followup_value' => 1,
                'auto_followup_unit' => 'day',
                'apply_on_manual' => 1,
                'apply_on_import' => 1,
                'apply_on_webhook' => 1,
                'auto_assignment_enabled' => 0,
                'assignment_override_user' => 1,
                'assignment_apply_on_manual' => 1,
                'assignment_apply_on_import' => 1,
                'assignment_apply_on_webhook' => 1,
                'lost_reasons_list' => "Price too high / Budget issue\nLocation not preferred\nBought from competitor\nInvalid / Wrong number\nUnresponsive after multiple calls\nNot interested / Plan postponed\nOther",
                'agenda_popup_enabled' => 1,
                'audio_chime_enabled' => 1,
                'notification_minutes_before' => 10,
                'default_downpayment_pct' => 20,
                'default_duration_years' => 3
            )
        );
    }

    public function normalizeSettingsData($rawData = array(), $projectId = 0)
    {
        $rawData = (array)$rawData;
        $duplicateMode = strtolower(trim((string)(isset($rawData['duplicate_mode']) ? $rawData['duplicate_mode'] : 'allow')));
        if(!in_array($duplicateMode, array('allow', 'flag', 'block'), true))
        {
            $duplicateMode = 'allow';
        }
        $autoUnit = strtolower(trim((string)(isset($rawData['auto_followup_unit']) ? $rawData['auto_followup_unit'] : 'day')));
        if(!in_array($autoUnit, array('hour', 'day', 'week'), true))
        {
            $autoUnit = 'day';
        }
        $followupValue = (int)(isset($rawData['auto_followup_value']) ? $rawData['auto_followup_value'] : 1);
        if($followupValue <= 0)
        {
            $followupValue = 1;
        }
        $notificationMins = (int)(isset($rawData['notification_minutes_before']) ? $rawData['notification_minutes_before'] : 10);
        if($notificationMins <= 0) $notificationMins = 10;
        $downpaymentPct = (int)(isset($rawData['default_downpayment_pct']) ? $rawData['default_downpayment_pct'] : 20);
        if($downpaymentPct <= 0 || $downpaymentPct > 100) $downpaymentPct = 20;
        $durationYears = (int)(isset($rawData['default_duration_years']) ? $rawData['default_duration_years'] : 3);
        if($durationYears <= 0 || $durationYears > 20) $durationYears = 3;
        $lostReasonsList = isset($rawData['lost_reasons_list']) ? trim((string)$rawData['lost_reasons_list']) : "Price too high / Budget issue\nLocation not preferred\nBought from competitor\nInvalid / Wrong number\nUnresponsive after multiple calls\nNot interested / Plan postponed\nOther";

        return array(
            'project_id' => (int)$projectId,
            'duplicate_mode' => $duplicateMode,
            'duplicate_by_mobile' => !empty($rawData['duplicate_by_mobile']) ? 1 : 0,
            'duplicate_by_email' => !empty($rawData['duplicate_by_email']) ? 1 : 0,
            'auto_followup_enabled' => !empty($rawData['auto_followup_enabled']) ? 1 : 0,
            'auto_followup_value' => $followupValue,
            'auto_followup_unit' => $autoUnit,
            'apply_on_manual' => !empty($rawData['apply_on_manual']) ? 1 : 0,
            'apply_on_import' => !empty($rawData['apply_on_import']) ? 1 : 0,
            'apply_on_webhook' => !empty($rawData['apply_on_webhook']) ? 1 : 0,
            'lost_reasons_list' => $lostReasonsList,
            'agenda_popup_enabled' => !empty($rawData['agenda_popup_enabled']) ? 1 : 0,
            'audio_chime_enabled' => !empty($rawData['audio_chime_enabled']) ? 1 : 0,
            'notification_minutes_before' => $notificationMins,
            'default_downpayment_pct' => $downpaymentPct,
            'default_duration_years' => $durationYears
        );
    }

    public function findProjectRow($projectId = 0)
    {
        $this->ensureSchema();
        $projectId = (int)$projectId;
        if($projectId <= 0)
        {
            return $this->defaultProjectRow($projectId);
        }
        $row = (array)$this->find('first', array(
            'conditions' => array('CrmAutomationSetting.project_id' => $projectId),
            'recursive' => -1
        ));
        if(empty($row['CrmAutomationSetting']))
        {
            return $this->defaultProjectRow($projectId);
        }
        $row['CrmAutomationSetting'] = array_merge(
            (array)$this->defaultProjectRow($projectId)['CrmAutomationSetting'],
            (array)$row['CrmAutomationSetting']
        );
        return $row;
    }

    public function saveProjectRow($projectId = 0, $rawData = array())
    {
        $this->ensureSchema();
        $projectId = (int)$projectId;
        if($projectId <= 0)
        {
            return false;
        }
        $current = $this->findProjectRow($projectId);
        $saveData = array_merge(
            (array)$current['CrmAutomationSetting'],
            (array)$this->normalizeSettingsData($rawData, $projectId)
        );
        if(!empty($current['CrmAutomationSetting']['id']))
        {
            $saveData['id'] = (int)$current['CrmAutomationSetting']['id'];
            $this->id = (int)$current['CrmAutomationSetting']['id'];
        }
        $this->create();
        return (bool)$this->save(array('CrmAutomationSetting' => $saveData), array('validate' => false));
    }

    public function normalizeAssignmentSettingsData($rawData = array(), $projectId = 0)
    {
        $rawData = (array)$rawData;
        return array(
            'project_id' => (int)$projectId,
            'auto_assignment_enabled' => !empty($rawData['auto_assignment_enabled']) ? 1 : 0,
            'assignment_override_user' => !empty($rawData['assignment_override_user']) ? 1 : 0,
            'assignment_apply_on_manual' => !empty($rawData['assignment_apply_on_manual']) ? 1 : 0,
            'assignment_apply_on_import' => !empty($rawData['assignment_apply_on_import']) ? 1 : 0,
            'assignment_apply_on_webhook' => !empty($rawData['assignment_apply_on_webhook']) ? 1 : 0
        );
    }

    public function saveProjectAssignmentSettings($projectId = 0, $rawData = array())
    {
        $this->ensureSchema();
        $projectId = (int)$projectId;
        if($projectId <= 0)
        {
            return false;
        }
        $current = $this->findProjectRow($projectId);
        $saveData = array_merge(
            (array)$current['CrmAutomationSetting'],
            (array)$this->normalizeAssignmentSettingsData($rawData, $projectId)
        );
        if(!empty($current['CrmAutomationSetting']['id']))
        {
            $saveData['id'] = (int)$current['CrmAutomationSetting']['id'];
            $this->id = (int)$current['CrmAutomationSetting']['id'];
        }
        $this->create();
        return (bool)$this->save(array('CrmAutomationSetting' => $saveData), array('validate' => false));
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

    public function findDuplicateLeadId($projectId = 0, $mobileNo = '', $emailAddress = '', $excludeLeadId = 0, $settingsRow = array())
    {
        $projectId = (int)$projectId;
        $excludeLeadId = (int)$excludeLeadId;
        $mobileNo = trim((string)$mobileNo);
        $emailAddress = strtolower(trim((string)$emailAddress));
        $settings = !empty($settingsRow['CrmAutomationSetting']) ? (array)$settingsRow['CrmAutomationSetting'] : (array)$settingsRow;

        if($projectId <= 0)
        {
            return 0;
        }

        $orConditions = array();
        if(!empty($settings['duplicate_by_mobile']) && $mobileNo !== '')
        {
            $orConditions[] = array('Lead.lead_mobile' => $mobileNo);
        }
        if(!empty($settings['duplicate_by_email']) && $emailAddress !== '')
        {
            $orConditions[] = array('LOWER(Lead.lead_email)' => $emailAddress);
        }
        if(empty($orConditions))
        {
            return 0;
        }

        $whereParts = array("`project_id`=".(int)$projectId);
        if($excludeLeadId > 0)
        {
            $whereParts[] = "`id`!=".(int)$excludeLeadId;
        }
        $orSql = array();
        if(!empty($settings['duplicate_by_mobile']) && $mobileNo !== '')
        {
            $orSql[] = "`lead_mobile`=".$this->getDataSource()->value($mobileNo, 'string');
        }
        if(!empty($settings['duplicate_by_email']) && $emailAddress !== '')
        {
            $quotedEmail = $this->getDataSource()->value($emailAddress, 'string');
            $orSql[] = "LOWER(`lead_email`)=".$quotedEmail;
        }
        if(empty($orSql))
        {
            return 0;
        }
        $sql = "SELECT `id` FROM `leads` WHERE ".implode(' AND ', $whereParts)." AND (".implode(' OR ', $orSql).") ORDER BY `id` DESC LIMIT 1";
        $rows = (array)$this->query($sql);
        if(!empty($rows[0]['leads']['id']))
        {
            return (int)$rows[0]['leads']['id'];
        }
        if(!empty($rows[0][0]['id']))
        {
            return (int)$rows[0][0]['id'];
        }
        return 0;
    }

    public function computeFollowUpDateTime($value = 1, $unit = 'day', $baseDateTime = null)
    {
        $value = (int)$value;
        if($value <= 0)
        {
            $value = 1;
        }
        $unit = strtolower(trim((string)$unit));
        if(!in_array($unit, array('hour', 'day', 'week'), true))
        {
            $unit = 'day';
        }
        $baseText = trim((string)$baseDateTime);
        $baseTimestamp = ($baseText !== '') ? strtotime($baseText) : time();
        if($baseTimestamp === false)
        {
            $baseTimestamp = time();
        }
        if($unit === 'hour')
        {
            return date('Y-m-d H:i:s', strtotime('+'.$value.' hour', $baseTimestamp));
        }
        if($unit === 'week')
        {
            return date('Y-m-d H:i:s', strtotime('+'.$value.' week', $baseTimestamp));
        }
        return date('Y-m-d H:i:s', strtotime('+'.$value.' day', $baseTimestamp));
    }

    public function applyToLeadPayload($payload = array(), $options = array())
    {
        $this->ensureSchema();
        $payload = (array)$payload;
        $options = (array)$options;
        $projectId = !empty($payload['project_id']) ? (int)$payload['project_id'] : 0;
        if($projectId <= 0)
        {
            return array('success' => true, 'payload' => $payload, 'duplicate_lead_id' => 0);
        }

        $scope = strtolower(trim((string)(isset($options['scope']) ? $options['scope'] : 'manual')));
        if(!in_array($scope, array('manual', 'import', 'webhook'), true))
        {
            $scope = 'manual';
        }
        $excludeLeadId = !empty($options['exclude_lead_id']) ? (int)$options['exclude_lead_id'] : 0;
        $settingsRow = $this->findProjectRow($projectId);
        $settings = !empty($settingsRow['CrmAutomationSetting']) ? (array)$settingsRow['CrmAutomationSetting'] : array();
        if(empty($settings))
        {
            return array('success' => true, 'payload' => $payload, 'duplicate_lead_id' => 0);
        }

        $applyScopeFlagKey = 'apply_on_'.$scope;
        $shouldApply = isset($settings[$applyScopeFlagKey]) ? !empty($settings[$applyScopeFlagKey]) : true;
        if(!$shouldApply)
        {
            return array('success' => true, 'payload' => $payload, 'duplicate_lead_id' => 0);
        }

        $duplicateLeadId = $this->findDuplicateLeadId(
            $projectId,
            isset($payload['lead_mobile']) ? $payload['lead_mobile'] : '',
            isset($payload['lead_email']) ? $payload['lead_email'] : '',
            $excludeLeadId,
            $settings
        );

        if($duplicateLeadId > 0)
        {
            $duplicateMode = strtolower(trim((string)(isset($settings['duplicate_mode']) ? $settings['duplicate_mode'] : 'allow')));
            if($duplicateMode === 'block')
            {
                return array(
                    'success' => false,
                    'message' => __('Duplicate lead already exists').' #'.$duplicateLeadId,
                    'payload' => $payload,
                    'duplicate_lead_id' => $duplicateLeadId
                );
            }
            if($duplicateMode === 'flag')
            {
                $payload['remarks'] = $this->_appendRemarkText(
                    isset($payload['remarks']) ? $payload['remarks'] : '',
                    '[Possible Duplicate Lead #'.$duplicateLeadId.']'
                );
            }
        }

        if(empty($payload['follow_up']) && !empty($settings['auto_followup_enabled']))
        {
            $payload['follow_up'] = $this->computeFollowUpDateTime(
                isset($settings['auto_followup_value']) ? $settings['auto_followup_value'] : 1,
                isset($settings['auto_followup_unit']) ? $settings['auto_followup_unit'] : 'day',
                !empty($payload['last_contact_date']) ? $payload['last_contact_date'] : null
            );
        }

        try
        {
            $CrmAssignmentRule = ClassRegistry::init('CrmAssignmentRule');
            if($CrmAssignmentRule)
            {
                $CrmAssignmentRule->ensureSchema();
                $assignmentResult = (array)$CrmAssignmentRule->applyToLeadPayload($payload, $settingsRow, $options);
                if(isset($assignmentResult['payload']) && is_array($assignmentResult['payload']))
                {
                    $payload = (array)$assignmentResult['payload'];
                }
            }
        }
        catch(Exception $e)
        {
        }

        return array(
            'success' => true,
            'payload' => $payload,
            'duplicate_lead_id' => $duplicateLeadId
        );
    }
}
