<?php
App::uses('AdminAppModel', 'Admin.Model');

class NotificationSetting extends AdminAppModel
{
    public $useTable = 'notification_settings';

    protected static $_cachedSettings = null;

    public function getAllSettings($refresh = false)
    {
        if (self::$_cachedSettings !== null && !$refresh) {
            return self::$_cachedSettings;
        }
        $rows = array();
        try {
            $rows = (array)$this->find('all', array(
                'order' => array('NotificationSetting.id' => 'ASC'),
                'recursive' => -1
            ));
        } catch (Exception $e) {
            $rows = array();
        }
        $map = array();
        foreach ($rows as $row) {
            $key = trim((string)$row['NotificationSetting']['setting_key']);
            if ($key !== '') {
                $map[$key] = $row['NotificationSetting'];
            }
        }
        self::$_cachedSettings = $map;
        return $map;
    }

    public function isEnabled($key, $default = true)
    {
        $settings = $this->getAllSettings();
        if (isset($settings[$key])) {
            $val = trim((string)$settings[$key]['setting_value']);
            return ($val === '1' || strtolower($val) === 'true' || strtolower($val) === 'yes');
        }
        return (bool)$default;
    }

    public function getSettingValue($key, $default = '')
    {
        $settings = $this->getAllSettings();
        if (isset($settings[$key])) {
            return $settings[$key]['setting_value'];
        }
        return $default;
    }

    public function saveSettings(array $keyValues)
    {
        foreach ($keyValues as $k => $v) {
            $k = trim((string)$k);
            if ($k === '') {
                continue;
            }
            try {
                $existing = $this->find('first', array(
                    'conditions' => array('NotificationSetting.setting_key' => $k),
                    'fields' => array('NotificationSetting.id'),
                    'recursive' => -1
                ));
                if (!empty($existing['NotificationSetting']['id'])) {
                    $this->updateAll(
                        array(
                            'NotificationSetting.setting_value' => "'" . addslashes((string)$v) . "'",
                            'NotificationSetting.modified' => "'" . date('Y-m-d H:i:s') . "'"
                        ),
                        array('NotificationSetting.id' => (int)$existing['NotificationSetting']['id'])
                    );
                } else {
                    $this->create();
                    $this->save(array(
                        'setting_key' => $k,
                        'setting_value' => (string)$v,
                        'created' => date('Y-m-d H:i:s'),
                        'modified' => date('Y-m-d H:i:s')
                    ), array('validate' => false));
                }
            } catch (Exception $e) {
                // Table may need healing or field error
            }
        }
        self::$_cachedSettings = null; // Invalidate memory cache
        return true;
    }
}
