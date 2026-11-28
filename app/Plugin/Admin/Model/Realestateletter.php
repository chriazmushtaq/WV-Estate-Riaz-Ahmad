<?php
class Realestateletter extends AppModel
{
    public $validationDomain = 'validation';
    public $useTable = 'real_estate_letters';
    public $actsAs = array('search-master.Searchable');
    public $filterArgs = array(
        'keyword' => array('type' => 'like', 'field' => 'Realestateletter.title')
    );
    public $belongsTo = array(
        'Deal' => array('className' => 'Deal', 'foreignKey' => 'deal_id'),
        'Template' => array('className' => 'Template', 'foreignKey' => 'template_id')
    );
    public $validate = array(
        'deal_id' => array(
            'rule' => 'numeric',
            'required' => true,
            'allowEmpty' => false,
            'message' => 'Please select a valid deal'
        ),
        'letter_type' => array(
            'rule' => 'notBlank',
            'required' => true,
            'allowEmpty' => false,
            'message' => 'Please select letter type'
        ),
        'title' => array(
            'rule' => 'notBlank',
            'required' => true,
            'allowEmpty' => false,
            'message' => 'Please enter letter title'
        ),
        'description' => array(
            'rule' => 'notBlank',
            'required' => true,
            'allowEmpty' => false,
            'message' => 'Please enter letter content'
        )
    );

    public function beforeValidate($options = array())
    {
        if (isset($this->data['Realestateletter']['letter_date']) && strlen(trim((string)$this->data['Realestateletter']['letter_date'])) > 0) {
            $this->data['Realestateletter']['letter_date'] = $this->dateFormatBeforeSave($this->data['Realestateletter']['letter_date']);
        }
        return true;
    }

    public function beforeSave($options = array())
    {
        parent::beforeSave($options);
        if (empty($this->id) && empty($this->data[$this->alias]['id'])) {
            $this->_ensureTableAutoIncrement();
            try {
                $cols = $this->query("SHOW FULL COLUMNS FROM `real_estate_letters` LIKE 'id'");
                $hasAi = false;
                if (!empty($cols[0]['COLUMNS'])) {
                    $extra = strtolower(isset($cols[0]['COLUMNS']['Extra']) ? $cols[0]['COLUMNS']['Extra'] : '');
                    $hasAi = (strpos($extra, 'auto_increment') !== false);
                }
                if (!$hasAi) {
                    $maxRow = $this->query("SELECT MAX(`id`) AS max_id FROM `real_estate_letters`");
                    $maxId = !empty($maxRow[0][0]['max_id']) ? (int)$maxRow[0][0]['max_id'] : (!empty($maxRow[0]['real_estate_letters']['max_id']) ? (int)$maxRow[0]['real_estate_letters']['max_id'] : 0);
                    $this->data[$this->alias]['id'] = $maxId + 1;
                }
            } catch (Exception $e) {}
        }
        return true;
    }

    public function _ensureTableAutoIncrement()
    {
        static $ensured = false;
        if ($ensured) return;
        $ensured = true;
        foreach (array('real_estate_letters', 'real_estate_letters_trash') as $tName) {
            try {
                $cols = $this->query("SHOW FULL COLUMNS FROM `{$tName}` LIKE 'id'");
                if (!empty($cols[0]['COLUMNS'])) {
                    $col = $cols[0]['COLUMNS'];
                    $extra = strtolower(isset($col['Extra']) ? $col['Extra'] : '');
                    $key = strtoupper(isset($col['Key']) ? $col['Key'] : '');
                    $type = isset($col['Type']) ? $col['Type'] : 'int(11)';
                    if (strpos($extra, 'auto_increment') === false) {
                        if ($key === 'PRI') {
                            $this->query("ALTER TABLE `{$tName}` MODIFY `id` {$type} NOT NULL AUTO_INCREMENT");
                        } else {
                            $this->query("ALTER TABLE `{$tName}` MODIFY `id` {$type} NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (`id`)");
                        }
                        $this->schema(true);
                    }
                }
                $maxRow = $this->query("SELECT MAX(`id`) AS max_id FROM `{$tName}`");
                $maxId = !empty($maxRow[0][0]['max_id']) ? (int)$maxRow[0][0]['max_id'] : (!empty($maxRow[0][$tName]['max_id']) ? (int)$maxRow[0][$tName]['max_id'] : 0);
                if ($maxId > 0) {
                    $st = $this->query("SHOW TABLE STATUS LIKE '{$tName}'");
                    $curAi = !empty($st[0]['TABLES']['Auto_increment']) ? (int)$st[0]['TABLES']['Auto_increment'] : (!empty($st[0][0]['Auto_increment']) ? (int)$st[0][0]['Auto_increment'] : 0);
                    if ($curAi <= $maxId) {
                        $this->query("ALTER TABLE `{$tName}` AUTO_INCREMENT = " . ($maxId + 1));
                    }
                }
            } catch (Exception $e) {}
        }
    }
}
?>
