<?php
App::uses('ConnectionManager', 'Model');

class Ardpaymentbankaccount extends AppModel
{
    public $useTable = 'ardaccounts_payment_bank_accounts';
    public $cacheSources = false;

    public function ensureSchema()
    {
        $db = ConnectionManager::getDataSource($this->useDbConfig);
        $db->rawQuery("CREATE TABLE IF NOT EXISTS `ardaccounts_payment_bank_accounts` (
            `id` bigint(20) NOT NULL AUTO_INCREMENT,
            `project_id` int(11) NOT NULL DEFAULT 0,
            `bank_name` varchar(160) DEFAULT '',
            `account_title` varchar(180) DEFAULT '',
            `account_no` varchar(100) DEFAULT '',
            `iban` varchar(120) DEFAULT '',
            `branch` varchar(180) DEFAULT '',
            `swift_code` varchar(80) DEFAULT '',
            `instructions` text,
            `is_primary` tinyint(1) NOT NULL DEFAULT 0,
            `status` varchar(20) NOT NULL DEFAULT 'active',
            `ordering` int(11) NOT NULL DEFAULT 0,
            `created` datetime DEFAULT NULL,
            `modified` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `project_id` (`project_id`),
            KEY `is_primary` (`is_primary`),
            KEY `status` (`status`),
            KEY `ordering` (`ordering`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }

    public function activeRowsForProject($projectId)
    {
        $this->ensureSchema();
        $projectId = (int)$projectId;
        if($projectId <= 0)
        {
            return array();
        }
        return (array)$this->find('all', array(
            'conditions' => array(
                'Ardpaymentbankaccount.project_id' => $projectId,
                'Ardpaymentbankaccount.status' => 'active'
            ),
            'order' => array(
                'Ardpaymentbankaccount.is_primary' => 'desc',
                'Ardpaymentbankaccount.ordering' => 'asc',
                'Ardpaymentbankaccount.id' => 'asc'
            ),
            'recursive' => -1
        ));
    }

    public function rowsForProject($projectId)
    {
        $this->ensureSchema();
        $projectId = (int)$projectId;
        if($projectId <= 0)
        {
            return array();
        }
        return (array)$this->find('all', array(
            'conditions' => array('Ardpaymentbankaccount.project_id' => $projectId),
            'order' => array(
                'Ardpaymentbankaccount.is_primary' => 'desc',
                'Ardpaymentbankaccount.ordering' => 'asc',
                'Ardpaymentbankaccount.id' => 'asc'
            ),
            'recursive' => -1
        ));
    }

    public function primaryRow($rows)
    {
        foreach((array)$rows as $row)
        {
            $data = !empty($row['Ardpaymentbankaccount']) ? $row['Ardpaymentbankaccount'] : $row;
            if(!empty($data['is_primary']))
            {
                return $data;
            }
        }
        foreach((array)$rows as $row)
        {
            return !empty($row['Ardpaymentbankaccount']) ? $row['Ardpaymentbankaccount'] : $row;
        }
        return array();
    }

    public function formatRowsForMessage($rows)
    {
        $blocks = array();
        foreach((array)$rows as $row)
        {
            $data = !empty($row['Ardpaymentbankaccount']) ? $row['Ardpaymentbankaccount'] : $row;
            $lineParts = array();
            if(!empty($data['bank_name']))
            {
                $lineParts[] = 'Bank: '.trim((string)$data['bank_name']);
            }
            if(!empty($data['account_title']))
            {
                $lineParts[] = 'Account Title: '.trim((string)$data['account_title']);
            }
            if(!empty($data['account_no']))
            {
                $lineParts[] = 'Account No: '.trim((string)$data['account_no']);
            }
            if(!empty($data['iban']))
            {
                $lineParts[] = 'IBAN: '.trim((string)$data['iban']);
            }
            if(!empty($data['branch']))
            {
                $lineParts[] = 'Branch: '.trim((string)$data['branch']);
            }
            if(!empty($data['swift_code']))
            {
                $lineParts[] = 'SWIFT: '.trim((string)$data['swift_code']);
            }
            if(!empty($data['instructions']))
            {
                $lineParts[] = 'Note: '.trim((string)$data['instructions']);
            }
            if(!empty($lineParts))
            {
                $blocks[] = implode("\n", $lineParts);
            }
        }
        return implode("\n\n", $blocks);
    }

    public function formatPrimaryForMessage($rows)
    {
        $primary = $this->primaryRow($rows);
        if(empty($primary))
        {
            return '';
        }
        return $this->formatRowsForMessage(array($primary));
    }
}
?>
