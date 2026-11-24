<?php
App::uses('ConnectionManager', 'Model');

class PaymentReminderSetting extends AppModel
{
    public $useTable = 'payment_reminder_settings';
    public $cacheSources = false;
    public $validationDomain = 'validation';

    public function ensureSchema()
    {
        $db = ConnectionManager::getDataSource($this->useDbConfig);
        $db->rawQuery("CREATE TABLE IF NOT EXISTS `payment_reminder_settings` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `auto_enabled` tinyint(1) NOT NULL DEFAULT 0,
            `send_sms` tinyint(1) NOT NULL DEFAULT 1,
            `send_email` tinyint(1) NOT NULL DEFAULT 0,
            `send_whatsapp` tinyint(1) NOT NULL DEFAULT 0,
            `days_before` int(11) NOT NULL DEFAULT 5,
            `include_overdue` tinyint(1) NOT NULL DEFAULT 1,
            `repeat_after_days` int(11) NOT NULL DEFAULT 3,
            `daily_limit` int(11) NOT NULL DEFAULT 100,
            `whatsapp_mode` varchar(20) NOT NULL DEFAULT 'link',
            `whatsapp_api` varchar(255) DEFAULT '',
            `whatsapp_type` varchar(10) NOT NULL DEFAULT 'Post',
            `whatsapp_token` varchar(255) DEFAULT '',
            `whatsapp_token_header` varchar(100) DEFAULT '',
            `whatsapp_mobile_param` varchar(100) NOT NULL DEFAULT 'number',
            `whatsapp_message_param` varchar(100) NOT NULL DEFAULT 'message',
            `whatsapp_extra_params` text,
            `created` datetime DEFAULT NULL,
            `modified` datetime DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8");
        $this->seedDefault();
    }

    public function defaultSettings()
    {
        return array(
            'id' => 1,
            'auto_enabled' => 0,
            'send_sms' => 1,
            'send_email' => 0,
            'send_whatsapp' => 0,
            'days_before' => 5,
            'include_overdue' => 1,
            'repeat_after_days' => 3,
            'daily_limit' => 100,
            'whatsapp_mode' => 'link',
            'whatsapp_api' => '',
            'whatsapp_type' => 'Post',
            'whatsapp_token' => '',
            'whatsapp_token_header' => '',
            'whatsapp_mobile_param' => 'number',
            'whatsapp_message_param' => 'message',
            'whatsapp_extra_params' => ''
        );
    }

    public function seedDefault()
    {
        $existing = (int)$this->find('count', array(
            'conditions' => array('PaymentReminderSetting.id' => 1),
            'recursive' => -1
        ));
        if($existing <= 0)
        {
            $this->create();
            $this->save(array('PaymentReminderSetting' => $this->defaultSettings()), array('validate' => false));
        }
    }

    public function currentSettings()
    {
        $this->ensureSchema();
        $row = (array)$this->findById(1);
        $settings = $this->defaultSettings();
        if(!empty($row['PaymentReminderSetting']))
        {
            $settings = array_merge($settings, (array)$row['PaymentReminderSetting']);
        }
        return $settings;
    }

    public function normalizeSettings($data)
    {
        $data = array_merge($this->defaultSettings(), (array)$data);
        $data['id'] = 1;
        foreach(array('auto_enabled', 'send_sms', 'send_email', 'send_whatsapp', 'include_overdue') as $field)
        {
            $data[$field] = !empty($data[$field]) ? 1 : 0;
        }
        foreach(array('days_before', 'repeat_after_days', 'daily_limit') as $field)
        {
            $data[$field] = (int)$data[$field];
        }
        if($data['days_before'] < 0)
        {
            $data['days_before'] = 0;
        }
        if($data['days_before'] > 90)
        {
            $data['days_before'] = 90;
        }
        if($data['repeat_after_days'] < 0)
        {
            $data['repeat_after_days'] = 0;
        }
        if($data['repeat_after_days'] > 365)
        {
            $data['repeat_after_days'] = 365;
        }
        if($data['daily_limit'] <= 0)
        {
            $data['daily_limit'] = 100;
        }
        if($data['daily_limit'] > 1000)
        {
            $data['daily_limit'] = 1000;
        }
        $data['whatsapp_mode'] = strtolower(trim((string)$data['whatsapp_mode'])) === 'api' ? 'api' : 'link';
        $data['whatsapp_type'] = strtolower(trim((string)$data['whatsapp_type'])) === 'get' ? 'Get' : 'Post';
        foreach(array('whatsapp_api', 'whatsapp_token', 'whatsapp_token_header', 'whatsapp_mobile_param', 'whatsapp_message_param', 'whatsapp_extra_params') as $field)
        {
            $data[$field] = isset($data[$field]) ? trim((string)$data[$field]) : '';
        }
        if($data['whatsapp_mobile_param'] === '')
        {
            $data['whatsapp_mobile_param'] = 'number';
        }
        if($data['whatsapp_message_param'] === '')
        {
            $data['whatsapp_message_param'] = 'message';
        }
        return $data;
    }
}
?>
