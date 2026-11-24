<?php
App::uses('Cache', 'Cache');
class CrmIntegration extends AppModel
{
    public $useTable = 'crm_integrations';
    public $cacheSources = false;

    public function providerOptions()
    {
        return array(
            'facebook_leads' => 'Facebook Lead Ads',
            'whatsapp_cloud' => 'WhatsApp Cloud API'
        );
    }

    public function defaultProviderRow($provider)
    {
        $provider = strtolower(trim((string)$provider));
        $providerOptions = $this->providerOptions();
        $title = isset($providerOptions[$provider]) ? (string)$providerOptions[$provider] : ucwords(str_replace('_', ' ', $provider));
        $defaultLeadSource = $title;
        return array(
            'CrmIntegration' => array(
                'id' => null,
                'provider' => $provider,
                'title' => $title,
                'enabled' => 0,
                'verify_token' => '',
                'access_token' => '',
                'app_id' => '',
                'app_secret' => '',
                'page_id' => '',
                'business_account_id' => '',
                'phone_number_id' => '',
                'default_project_id' => 0,
                'default_ugroup_id' => 0,
                'default_user_id' => 0,
                'default_status_id' => 0,
                'default_lead_source' => $defaultLeadSource,
                'auto_create_contact' => 0,
                'settings_json' => ''
            )
        );
    }

    public function findProviderRow($provider)
    {
        $this->ensureSchema();
        $provider = strtolower(trim((string)$provider));
        $defaults = $this->defaultProviderRow($provider);
        if($provider === '')
        {
            return $defaults;
        }
        $row = (array)$this->find('first', array(
            'conditions' => array('CrmIntegration.provider' => $provider),
            'recursive' => -1
        ));
        if(empty($row['CrmIntegration']))
        {
            return $defaults;
        }
        $row['CrmIntegration'] = array_merge((array)$defaults['CrmIntegration'], (array)$row['CrmIntegration']);
        return $row;
    }

    public function saveProviderRow($provider, $data)
    {
        $this->ensureSchema();
        $provider = strtolower(trim((string)$provider));
        if($provider === '')
        {
            return false;
        }
        $current = $this->findProviderRow($provider);
        $saveData = array_merge((array)$current['CrmIntegration'], (array)$data);
        $saveData['provider'] = $provider;
        if(empty($saveData['title']))
        {
            $providerOptions = $this->providerOptions();
            $saveData['title'] = isset($providerOptions[$provider]) ? (string)$providerOptions[$provider] : ucwords(str_replace('_', ' ', $provider));
        }
        $this->create();
        if(!empty($current['CrmIntegration']['id']))
        {
            $saveData['id'] = (int)$current['CrmIntegration']['id'];
            $this->id = (int)$current['CrmIntegration']['id'];
        }
        return (bool)$this->save(array('CrmIntegration' => $saveData), array('validate' => false));
    }

    public function defaultUserSmsRow($userId = 0)
    {
        return array(
            'CrmUserSmsSetting' => array(
                'id' => null,
                'user_id' => (int)$userId,
                'use_global' => 1,
                'default_sms_template_id' => 0,
                'api' => '',
                'senderid' => '',
                'username' => '',
                'password' => '',
                'husername' => '',
                'hpassword' => '',
                'hmobile' => '',
                'hmessage' => '',
                'hsenderid' => '',
                'type' => 'Get'
            )
        );
    }

    public function findUserSmsRow($userId = 0)
    {
        $this->ensureSchema();
        $userId = (int)$userId;
        $defaults = $this->defaultUserSmsRow($userId);
        if($userId <= 0)
        {
            return $defaults;
        }
        $Model = ClassRegistry::init('CrmUserSmsSetting');
        if(!$Model)
        {
            return $defaults;
        }
        $Model->schema(true);
        $row = (array)$Model->find('first', array(
            'conditions' => array('CrmUserSmsSetting.user_id' => $userId),
            'recursive' => -1
        ));
        if(empty($row['CrmUserSmsSetting']))
        {
            return $defaults;
        }
        $row['CrmUserSmsSetting'] = array_merge((array)$defaults['CrmUserSmsSetting'], (array)$row['CrmUserSmsSetting']);
        return $row;
    }

    public function saveUserSmsRow($userId = 0, $data = array())
    {
        $this->ensureSchema();
        $userId = (int)$userId;
        if($userId <= 0)
        {
            return false;
        }
        $current = $this->findUserSmsRow($userId);
        $saveData = array_merge((array)$current['CrmUserSmsSetting'], (array)$data);
        $saveData['user_id'] = $userId;
        $saveData['use_global'] = !empty($saveData['use_global']) ? 1 : 0;
        $saveData['default_sms_template_id'] = !empty($saveData['default_sms_template_id']) ? (int)$saveData['default_sms_template_id'] : 0;
        if(empty($saveData['type']))
        {
            $saveData['type'] = 'Get';
        }
        $Model = ClassRegistry::init('CrmUserSmsSetting');
        if(!$Model)
        {
            return false;
        }
        $Model->schema(true);
        $Model->create();
        if(!empty($current['CrmUserSmsSetting']['id']))
        {
            $saveData['id'] = (int)$current['CrmUserSmsSetting']['id'];
            $Model->id = (int)$current['CrmUserSmsSetting']['id'];
        }
        return (bool)$Model->save(array('CrmUserSmsSetting' => $saveData), array('validate' => false));
    }

    public function defaultUserEmailRow($userId = 0)
    {
        return array(
            'CrmUserEmailSetting' => array(
                'id' => null,
                'user_id' => (int)$userId,
                'use_global' => 1,
                'default_email_template_id' => 0,
                'type' => 'Mail',
                'host' => '',
                'port' => '',
                'username' => '',
                'password' => ''
            )
        );
    }

    public function findUserEmailRow($userId = 0)
    {
        $this->ensureSchema();
        $userId = (int)$userId;
        $defaults = $this->defaultUserEmailRow($userId);
        if($userId <= 0)
        {
            return $defaults;
        }
        $Model = ClassRegistry::init('CrmUserEmailSetting');
        if(!$Model)
        {
            return $defaults;
        }
        $Model->schema(true);
        $row = (array)$Model->find('first', array(
            'conditions' => array('CrmUserEmailSetting.user_id' => $userId),
            'recursive' => -1
        ));
        if(empty($row['CrmUserEmailSetting']))
        {
            return $defaults;
        }
        $row['CrmUserEmailSetting'] = array_merge((array)$defaults['CrmUserEmailSetting'], (array)$row['CrmUserEmailSetting']);
        return $row;
    }

    public function saveUserEmailRow($userId = 0, $data = array())
    {
        $this->ensureSchema();
        $userId = (int)$userId;
        if($userId <= 0)
        {
            return false;
        }
        $current = $this->findUserEmailRow($userId);
        $saveData = array_merge((array)$current['CrmUserEmailSetting'], (array)$data);
        $saveData['user_id'] = $userId;
        $saveData['use_global'] = !empty($saveData['use_global']) ? 1 : 0;
        $saveData['default_email_template_id'] = !empty($saveData['default_email_template_id']) ? (int)$saveData['default_email_template_id'] : 0;
        if(empty($saveData['type']))
        {
            $saveData['type'] = 'Mail';
        }
        $Model = ClassRegistry::init('CrmUserEmailSetting');
        if(!$Model)
        {
            return false;
        }
        $Model->schema(true);
        $Model->create();
        if(!empty($current['CrmUserEmailSetting']['id']))
        {
            $saveData['id'] = (int)$current['CrmUserEmailSetting']['id'];
            $Model->id = (int)$current['CrmUserEmailSetting']['id'];
        }
        return (bool)$Model->save(array('CrmUserEmailSetting' => $saveData), array('validate' => false));
    }

    public function effectiveSmsGatewayForUser($userId = 0, $globalSmsRow = array())
    {
        $this->ensureSchema();
        $userId = (int)$userId;
        $userRow = $this->findUserSmsRow($userId);
        $useGlobal = empty($userRow['CrmUserSmsSetting']) || !empty($userRow['CrmUserSmsSetting']['use_global']);
        if($useGlobal)
        {
            return array(
                'use_global' => 1,
                'settings' => (array)$globalSmsRow
            );
        }
        return array(
            'use_global' => 0,
            'settings' => array(
                'Smssetting' => array(
                    'api' => isset($userRow['CrmUserSmsSetting']['api']) ? (string)$userRow['CrmUserSmsSetting']['api'] : '',
                    'senderid' => isset($userRow['CrmUserSmsSetting']['senderid']) ? (string)$userRow['CrmUserSmsSetting']['senderid'] : '',
                    'username' => isset($userRow['CrmUserSmsSetting']['username']) ? (string)$userRow['CrmUserSmsSetting']['username'] : '',
                    'password' => isset($userRow['CrmUserSmsSetting']['password']) ? (string)$userRow['CrmUserSmsSetting']['password'] : '',
                    'husername' => isset($userRow['CrmUserSmsSetting']['husername']) ? (string)$userRow['CrmUserSmsSetting']['husername'] : '',
                    'hpassword' => isset($userRow['CrmUserSmsSetting']['hpassword']) ? (string)$userRow['CrmUserSmsSetting']['hpassword'] : '',
                    'hmobile' => isset($userRow['CrmUserSmsSetting']['hmobile']) ? (string)$userRow['CrmUserSmsSetting']['hmobile'] : '',
                    'hmessage' => isset($userRow['CrmUserSmsSetting']['hmessage']) ? (string)$userRow['CrmUserSmsSetting']['hmessage'] : '',
                    'hsenderid' => isset($userRow['CrmUserSmsSetting']['hsenderid']) ? (string)$userRow['CrmUserSmsSetting']['hsenderid'] : '',
                    'type' => isset($userRow['CrmUserSmsSetting']['type']) ? (string)$userRow['CrmUserSmsSetting']['type'] : 'Get'
                )
            )
        );
    }

    public function defaultSmsTemplateIdForUser($userId = 0)
    {
        return (int)$this->storedSmsTemplateIdForUser((int)$userId);
    }

    public function storedSmsTemplateIdForUser($userId = 0)
    {
        $this->ensureSchema();
        $userId = (int)$userId;
        if($userId <= 0)
        {
            return 0;
        }
        $Model = ClassRegistry::init('CrmUserSmsSetting');
        if(!$Model)
        {
            return 0;
        }
        $Model->schema(true);
        $row = (array)$Model->find('first', array(
            'fields' => array('CrmUserSmsSetting.default_sms_template_id'),
            'conditions' => array('CrmUserSmsSetting.user_id' => $userId),
            'recursive' => -1
        ));
        return !empty($row['CrmUserSmsSetting']['default_sms_template_id']) ? (int)$row['CrmUserSmsSetting']['default_sms_template_id'] : 0;
    }

    public function forceStoredSmsTemplateIdForUser($userId = 0, $templateId = 0)
    {
        $this->ensureSchema();
        $userId = (int)$userId;
        $templateId = (int)$templateId;
        if($userId <= 0)
        {
            return false;
        }
        $Model = ClassRegistry::init('CrmUserSmsSetting');
        if(!$Model)
        {
            return false;
        }
        $Model->schema(true);
        $current = $this->findUserSmsRow($userId);
        $saveData = array(
            'user_id' => $userId,
            'default_sms_template_id' => $templateId
        );
        if(!empty($current['CrmUserSmsSetting']['id']))
        {
            $saveData['id'] = (int)$current['CrmUserSmsSetting']['id'];
        }
        $Model->create();
        return (bool)$Model->save(array('CrmUserSmsSetting' => $saveData), array('validate' => false, 'callbacks' => false));
    }

    public function effectiveEmailGatewayForUser($userId = 0, $globalEmailRow = array())
    {
        $this->ensureSchema();
        $userId = (int)$userId;
        $userRow = $this->findUserEmailRow($userId);
        $useGlobal = empty($userRow['CrmUserEmailSetting']) || !empty($userRow['CrmUserEmailSetting']['use_global']);
        if($useGlobal)
        {
            return array(
                'use_global' => 1,
                'settings' => (array)$globalEmailRow
            );
        }
        return array(
            'use_global' => 0,
            'settings' => array(
                'Emailsetting' => array(
                    'type' => isset($userRow['CrmUserEmailSetting']['type']) ? (string)$userRow['CrmUserEmailSetting']['type'] : 'Mail',
                    'host' => isset($userRow['CrmUserEmailSetting']['host']) ? (string)$userRow['CrmUserEmailSetting']['host'] : '',
                    'port' => isset($userRow['CrmUserEmailSetting']['port']) ? (string)$userRow['CrmUserEmailSetting']['port'] : '',
                    'username' => isset($userRow['CrmUserEmailSetting']['username']) ? (string)$userRow['CrmUserEmailSetting']['username'] : '',
                    'password' => isset($userRow['CrmUserEmailSetting']['password']) ? (string)$userRow['CrmUserEmailSetting']['password'] : ''
                )
            )
        );
    }

    public function defaultEmailTemplateIdForUser($userId = 0)
    {
        return (int)$this->storedEmailTemplateIdForUser((int)$userId);
    }

    public function storedEmailTemplateIdForUser($userId = 0)
    {
        $this->ensureSchema();
        $userId = (int)$userId;
        if($userId <= 0)
        {
            return 0;
        }
        $Model = ClassRegistry::init('CrmUserEmailSetting');
        if(!$Model)
        {
            return 0;
        }
        $Model->schema(true);
        $row = (array)$Model->find('first', array(
            'fields' => array('CrmUserEmailSetting.default_email_template_id'),
            'conditions' => array('CrmUserEmailSetting.user_id' => $userId),
            'recursive' => -1
        ));
        return !empty($row['CrmUserEmailSetting']['default_email_template_id']) ? (int)$row['CrmUserEmailSetting']['default_email_template_id'] : 0;
    }

    public function forceStoredEmailTemplateIdForUser($userId = 0, $templateId = 0)
    {
        $this->ensureSchema();
        $userId = (int)$userId;
        $templateId = (int)$templateId;
        if($userId <= 0)
        {
            return false;
        }
        $Model = ClassRegistry::init('CrmUserEmailSetting');
        if(!$Model)
        {
            return false;
        }
        $Model->schema(true);
        $current = $this->findUserEmailRow($userId);
        $saveData = array(
            'user_id' => $userId,
            'default_email_template_id' => $templateId
        );
        if(!empty($current['CrmUserEmailSetting']['id']))
        {
            $saveData['id'] = (int)$current['CrmUserEmailSetting']['id'];
        }
        $Model->create();
        return (bool)$Model->save(array('CrmUserEmailSetting' => $saveData), array('validate' => false, 'callbacks' => false));
    }

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
                "CREATE TABLE IF NOT EXISTS `crm_integrations` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `provider` varchar(50) NOT NULL,
                    `title` varchar(120) DEFAULT NULL,
                    `enabled` tinyint(1) NOT NULL DEFAULT 0,
                    `verify_token` varchar(191) DEFAULT NULL,
                    `access_token` longtext NULL,
                    `app_id` varchar(191) DEFAULT NULL,
                    `app_secret` varchar(191) DEFAULT NULL,
                    `page_id` varchar(191) DEFAULT NULL,
                    `business_account_id` varchar(191) DEFAULT NULL,
                    `phone_number_id` varchar(191) DEFAULT NULL,
                    `default_project_id` int(11) DEFAULT NULL,
                    `default_ugroup_id` int(11) DEFAULT NULL,
                    `default_user_id` int(11) DEFAULT NULL,
                    `default_status_id` int(11) DEFAULT NULL,
                    `default_lead_source` varchar(191) DEFAULT NULL,
                    `auto_create_contact` tinyint(1) NOT NULL DEFAULT 0,
                    `settings_json` longtext NULL,
                    `created` datetime DEFAULT NULL,
                    `modified` datetime DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `crm_integrations_provider_unique` (`provider`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8"
            );
            $this->query(
                "CREATE TABLE IF NOT EXISTS `crm_integration_routes` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `provider` varchar(50) NOT NULL,
                    `match_type` varchar(50) NOT NULL,
                    `external_id` varchar(191) NOT NULL,
                    `title` varchar(191) DEFAULT NULL,
                    `project_id` int(11) DEFAULT NULL,
                    `ugroup_id` int(11) DEFAULT NULL,
                    `user_id` int(11) DEFAULT NULL,
                    `status_id` int(11) DEFAULT NULL,
                    `lead_source` varchar(191) DEFAULT NULL,
                    `auto_create_contact` tinyint(1) NOT NULL DEFAULT 0,
                    `is_active` tinyint(1) NOT NULL DEFAULT 1,
                    `extra_json` longtext NULL,
                    `created` datetime DEFAULT NULL,
                    `modified` datetime DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `crm_integration_routes_provider` (`provider`),
                    KEY `crm_integration_routes_external_id` (`external_id`),
                    KEY `crm_integration_routes_lookup` (`provider`,`match_type`,`external_id`,`is_active`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8"
            );
            $this->query(
                "CREATE TABLE IF NOT EXISTS `crm_integration_logs` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `provider` varchar(50) NOT NULL,
                    `event_type` varchar(80) DEFAULT NULL,
                    `external_ref` varchar(191) DEFAULT NULL,
                    `project_id` int(11) DEFAULT NULL,
                    `user_id` int(11) DEFAULT NULL,
                    `lead_id` int(11) DEFAULT NULL,
                    `status` varchar(50) DEFAULT NULL,
                    `message` text NULL,
                    `payload_json` longtext NULL,
                    `created` datetime DEFAULT NULL,
                    `modified` datetime DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `crm_integration_logs_provider` (`provider`),
                    KEY `crm_integration_logs_external_ref` (`external_ref`),
                    KEY `crm_integration_logs_status` (`status`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8"
            );
            $this->query(
                "CREATE TABLE IF NOT EXISTS `crm_user_sms_settings` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `user_id` int(11) NOT NULL,
                    `use_global` tinyint(1) NOT NULL DEFAULT 1,
                    `default_sms_template_id` int(11) NOT NULL DEFAULT 0,
                    `api` varchar(500) DEFAULT NULL,
                    `senderid` varchar(191) DEFAULT NULL,
                    `username` varchar(191) DEFAULT NULL,
                    `password` varchar(191) DEFAULT NULL,
                    `husername` varchar(191) DEFAULT NULL,
                    `hpassword` varchar(191) DEFAULT NULL,
                    `hmobile` varchar(191) DEFAULT NULL,
                    `hmessage` varchar(191) DEFAULT NULL,
                    `hsenderid` varchar(191) DEFAULT NULL,
                    `type` varchar(10) NOT NULL DEFAULT 'Get',
                    `created` datetime DEFAULT NULL,
                    `modified` datetime DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `crm_user_sms_settings_user_unique` (`user_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8"
            );
            $this->query(
                "CREATE TABLE IF NOT EXISTS `crm_user_email_settings` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `user_id` int(11) NOT NULL,
                    `use_global` tinyint(1) NOT NULL DEFAULT 1,
                    `default_email_template_id` int(11) NOT NULL DEFAULT 0,
                    `type` varchar(20) NOT NULL DEFAULT 'Mail',
                    `host` varchar(191) DEFAULT NULL,
                    `port` varchar(50) DEFAULT NULL,
                    `username` varchar(191) DEFAULT NULL,
                    `password` varchar(191) DEFAULT NULL,
                    `created` datetime DEFAULT NULL,
                    `modified` datetime DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `crm_user_email_settings_user_unique` (`user_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8"
            );
            $this->_ensureUserGatewayTemplateColumns();
            $this->_ensureLeadCrmColumns();
            $this->_ensureLeadClientNullable();
            Cache::clear(false, '_cake_model_');
            $this->schema(true);
            $isEnsured = true;
        }
        catch(Exception $e)
        {
            $isEnsured = false;
        }
        return $isEnsured;
    }

    protected function _ensureUserGatewayTemplateColumns()
    {
        $columnSqlMap = array(
            'crm_user_sms_settings' => array(
                'default_sms_template_id' => "ALTER TABLE `crm_user_sms_settings` ADD `default_sms_template_id` int(11) NOT NULL DEFAULT 0 AFTER `use_global`"
            ),
            'crm_user_email_settings' => array(
                'default_email_template_id' => "ALTER TABLE `crm_user_email_settings` ADD `default_email_template_id` int(11) NOT NULL DEFAULT 0 AFTER `use_global`"
            )
        );
        foreach($columnSqlMap as $tableName => $columns)
        {
            foreach($columns as $columnName => $sql)
            {
                try
                {
                    $exists = $this->query("SHOW COLUMNS FROM `{$tableName}` LIKE '".addslashes($columnName)."'");
                    if(empty($exists))
                    {
                        $this->query($sql);
                    }
                }
                catch(Exception $e)
                {
                }
            }
        }
        return true;
    }

    protected function _ensureLeadCrmColumns()
    {
        $Lead = ClassRegistry::init('Lead');
        if(!$Lead)
        {
            return false;
        }
        $columns = array(
            'lead_name' => "varchar(255) DEFAULT NULL",
            'lead_mobile' => "varchar(60) DEFAULT NULL",
            'lead_email' => "varchar(255) DEFAULT NULL",
            'lead_address' => "text NULL",
            'lead_city' => "varchar(150) DEFAULT NULL",
            'lead_budget' => "decimal(15,2) DEFAULT NULL",
            'lead_language' => "varchar(80) DEFAULT NULL",
            'lead_source' => "varchar(150) DEFAULT NULL",
            'source_form_id' => "varchar(120) DEFAULT NULL",
            'source_ad_id' => "varchar(120) DEFAULT NULL",
            'utm_source' => "varchar(191) DEFAULT NULL",
            'utm_campaign' => "varchar(191) DEFAULT NULL",
            'utm_medium' => "varchar(191) DEFAULT NULL",
            'utm_term' => "varchar(191) DEFAULT NULL",
            'utm_content' => "varchar(191) DEFAULT NULL",
            'last_contact_date' => "date DEFAULT NULL",
            'lead_temperature' => "varchar(12) DEFAULT 'warm'",
            'kanban_position' => "int(11) DEFAULT 0"
        );
        foreach($columns as $columnName => $columnSql)
        {
            try
            {
                $exists = $Lead->query("SHOW COLUMNS FROM `leads` LIKE '".addslashes($columnName)."'");
                if(empty($exists))
                {
                    $Lead->query("ALTER TABLE `leads` ADD `{$columnName}` {$columnSql}");
                }
            }
            catch(Exception $e)
            {
            }
        }
        return true;
    }

    protected function _ensureLeadClientNullable()
    {
        $Lead = ClassRegistry::init('Lead');
        if(!$Lead)
        {
            return false;
        }
        try
        {
            $clientColumnRows = (array)$Lead->query("SHOW COLUMNS FROM `leads` LIKE 'client_id'");
            if(empty($clientColumnRows))
            {
                return false;
            }
            $columnMeta = reset($clientColumnRows);
            $columnInfo = array();
            if(isset($columnMeta['COLUMNS']))
            {
                $columnInfo = (array)$columnMeta['COLUMNS'];
            }
            elseif(isset($columnMeta[0]))
            {
                $columnInfo = (array)$columnMeta[0];
            }
            $isNullable = !empty($columnInfo['Null']) && strtoupper((string)$columnInfo['Null']) === 'YES';
            if($isNullable)
            {
                return true;
            }

            $constraintName = '';
            $constraintRows = (array)$Lead->query(
                "SELECT `CONSTRAINT_NAME` FROM `information_schema`.`KEY_COLUMN_USAGE` ".
                "WHERE `TABLE_SCHEMA` = DATABASE() AND `TABLE_NAME` = 'leads' AND `COLUMN_NAME` = 'client_id' ".
                "AND `REFERENCED_TABLE_NAME` = 'clients' LIMIT 1"
            );
            if(!empty($constraintRows[0]['KEY_COLUMN_USAGE']['CONSTRAINT_NAME']))
            {
                $constraintName = (string)$constraintRows[0]['KEY_COLUMN_USAGE']['CONSTRAINT_NAME'];
            }
            elseif(!empty($constraintRows[0][0]['CONSTRAINT_NAME']))
            {
                $constraintName = (string)$constraintRows[0][0]['CONSTRAINT_NAME'];
            }

            if($constraintName !== '')
            {
                $Lead->query("ALTER TABLE `leads` DROP FOREIGN KEY `".addslashes($constraintName)."`");
            }
            $Lead->query("ALTER TABLE `leads` MODIFY `client_id` int(11) DEFAULT NULL");
            $Lead->query(
                "ALTER TABLE `leads` ADD CONSTRAINT `leads_ibfk_5` FOREIGN KEY (`client_id`) ".
                "REFERENCES `clients` (`id`) ON DELETE CASCADE ON UPDATE CASCADE"
            );
            return true;
        }
        catch(Exception $e)
        {
            return false;
        }
    }
}
?>
