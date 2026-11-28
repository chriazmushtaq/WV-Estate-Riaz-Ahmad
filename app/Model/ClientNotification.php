<?php
App::uses('AppModel', 'Model');

class ClientNotification extends AppModel {
    public $useTable = 'client_notifications';

    public function beforeSave($options = array()) {
        parent::beforeSave($options);
        if (empty($this->id) && empty($this->data[$this->alias]['id'])) {
            $this->_ensureTableAutoIncrement();
            try {
                $cols = $this->query("SHOW FULL COLUMNS FROM `client_notifications` LIKE 'id'");
                $hasAi = false;
                if (!empty($cols[0]['COLUMNS'])) {
                    $extra = strtolower(isset($cols[0]['COLUMNS']['Extra']) ? $cols[0]['COLUMNS']['Extra'] : '');
                    $hasAi = (strpos($extra, 'auto_increment') !== false);
                }
                if (!$hasAi) {
                    $maxRow = $this->query("SELECT MAX(`id`) AS max_id FROM `client_notifications`");
                    $maxId = !empty($maxRow[0][0]['max_id']) ? (int)$maxRow[0][0]['max_id'] : (!empty($maxRow[0]['client_notifications']['max_id']) ? (int)$maxRow[0]['client_notifications']['max_id'] : 0);
                    $this->data[$this->alias]['id'] = $maxId + 1;
                }
            } catch (Exception $e) {}
        }
        return true;
    }

    protected function _ensureTableAutoIncrement() {
        static $ensured = false;
        if ($ensured) return;
        $ensured = true;
        try {
            $cols = $this->query("SHOW FULL COLUMNS FROM `client_notifications` LIKE 'id'");
            if (!empty($cols[0]['COLUMNS'])) {
                $col = $cols[0]['COLUMNS'];
                $extra = strtolower(isset($col['Extra']) ? $col['Extra'] : '');
                $key = strtoupper(isset($col['Key']) ? $col['Key'] : '');
                $type = isset($col['Type']) ? $col['Type'] : 'bigint(20) unsigned';
                if (strpos($extra, 'auto_increment') === false) {
                    if ($key === 'PRI') {
                        $this->query("ALTER TABLE `client_notifications` MODIFY `id` {$type} NOT NULL AUTO_INCREMENT");
                    } else {
                        $this->query("ALTER TABLE `client_notifications` MODIFY `id` {$type} NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (`id`)");
                    }
                    $this->schema(true);
                }
            }
            $maxRow = $this->query("SELECT MAX(`id`) AS max_id FROM `client_notifications`");
            $maxId = !empty($maxRow[0][0]['max_id']) ? (int)$maxRow[0][0]['max_id'] : 0;
            if ($maxId > 0) {
                $st = $this->query("SHOW TABLE STATUS LIKE 'client_notifications'");
                $curAi = !empty($st[0]['TABLES']['Auto_increment']) ? (int)$st[0]['TABLES']['Auto_increment'] : (!empty($st[0][0]['Auto_increment']) ? (int)$st[0][0]['Auto_increment'] : 0);
                if ($curAi <= $maxId) {
                    $this->query("ALTER TABLE `client_notifications` AUTO_INCREMENT = " . ($maxId + 1));
                }
            }
        } catch (Exception $e) {}
    }
}
