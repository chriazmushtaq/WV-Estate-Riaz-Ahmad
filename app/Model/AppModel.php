<?php
/**
 * Application model for CakePHP.
 *
 * This file is application-wide model file. You can put all
 * application-wide model-related methods here.
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Model
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

App::uses('Model', 'Model');
App::uses('SimplePasswordHasher', 'Controller/Component/Auth');
App::uses('CakeSession', 'Model/Datasource');

/**
 * Application model for Cake.
 *
 * Add your application-wide methods in the class below, your models
 * will inherit them.
 *
 * @package       app.Model
 */
class AppModel extends Model {
    protected static $_trashCaptureCache = array();
    protected static $_trashTableReady = false;
    protected static $_trashTableInitFailed = false;

    /**
     * Keep deal displays tied to the live property record instead of a stale
     * title carried by an older association result.
     */
    protected function _refreshLinkedPropertyNames($results, $dealAlias)
    {
        if(empty($results) || !is_array($results))
        {
            return $results;
        }
        $propertyIds = array();
        foreach($results as $row)
        {
            if(!empty($row[$dealAlias]['property_id']))
            {
                $propertyIds[] = (int)$row[$dealAlias]['property_id'];
            }
        }
        $propertyIds = array_values(array_unique(array_filter($propertyIds)));
        if(empty($propertyIds))
        {
            return $results;
        }

        $nameMap = array();
        try
        {
            $rows = $this->query(
                'SELECT `CurrentProperty`.`id`, `CurrentProperty`.`name` '.
                'FROM `properties` AS `CurrentProperty` '.
                'WHERE `CurrentProperty`.`id` IN ('.implode(',', $propertyIds).')'
            );
            foreach((array)$rows as $propertyRow)
            {
                $data = !empty($propertyRow['CurrentProperty']) ? $propertyRow['CurrentProperty'] : array();
                if(isset($data['id']))
                {
                    $nameMap[(int)$data['id']] = isset($data['name']) ? (string)$data['name'] : '';
                }
            }
        }
        catch(Exception $e)
        {
            return $results;
        }

        foreach($results as $key=>$row)
        {
            $propertyId = !empty($row[$dealAlias]['property_id']) ? (int)$row[$dealAlias]['property_id'] : 0;
            if($propertyId > 0 && array_key_exists($propertyId, $nameMap))
            {
                if(empty($results[$key]['Property']) || !is_array($results[$key]['Property']))
                {
                    $results[$key]['Property'] = array('id'=>$propertyId);
                }
                $results[$key]['Property']['id'] = $propertyId;
                $results[$key]['Property']['name'] = $nameMap[$propertyId];
            }
        }
        return $results;
    }

    protected function _shouldSkipTrashCapture()
    {
        $tableName = strtolower(trim((string)$this->useTable));
        if($tableName === '')
        {
            return true;
        }
        $systemTables = array(
            'ardaccounts_trash_bin',
            'page_rights',
            'pages'
        );
        if(in_array($tableName, $systemTables, true))
        {
            return true;
        }
        // ARD Accounts module already has dedicated trash payload logic.
        if(strpos($tableName, 'ardaccounts_') === 0)
        {
            return true;
        }
        return false;
    }

    protected function _ensureTrashTable()
    {
        if(self::$_trashTableReady)
        {
            return true;
        }
        if(self::$_trashTableInitFailed)
        {
            return false;
        }
        $sql = "CREATE TABLE IF NOT EXISTS `ardaccounts_trash_bin` (
            `id` bigint(20) NOT NULL AUTO_INCREMENT,
            `project_id` int(11) NOT NULL DEFAULT 0,
            `source_table` varchar(120) NOT NULL,
            `source_model` varchar(80) DEFAULT '',
            `source_id` bigint(20) NOT NULL DEFAULT 0,
            `entry_title` varchar(255) DEFAULT '',
            `payload_json` longtext,
            `deleted_by` int(11) NOT NULL DEFAULT 0,
            `deleted_at` datetime DEFAULT NULL,
            `created` datetime DEFAULT NULL,
            `modified` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `project_id` (`project_id`),
            KEY `source_table` (`source_table`),
            KEY `source_id` (`source_id`),
            KEY `deleted_at` (`deleted_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3";
        try
        {
            $this->query($sql);
            self::$_trashTableReady = true;
            return true;
        }
        catch(Exception $e)
        {
            self::$_trashTableInitFailed = true;
            return false;
        }
    }

    protected function _trashSessionMeta()
    {
        $meta = array(
            'user_id' => 0,
            'project_id' => 0
        );

        $adminUser = CakeSession::read('User');
        if(is_array($adminUser))
        {
            if(isset($adminUser['User']['id']) && (int)$adminUser['User']['id'] > 0)
            {
                $meta['user_id'] = (int)$adminUser['User']['id'];
            }
            if(isset($adminUser['User']['project_id']) && (int)$adminUser['User']['project_id'] > 0)
            {
                $meta['project_id'] = (int)$adminUser['User']['project_id'];
            }
        }

        if($meta['user_id'] <= 0 || $meta['project_id'] <= 0)
        {
            $frontUser = CakeSession::read('frontUser');
            if(is_array($frontUser))
            {
                if($meta['user_id'] <= 0 && isset($frontUser['Client']['id']) && (int)$frontUser['Client']['id'] > 0)
                {
                    $meta['user_id'] = (int)$frontUser['Client']['id'];
                }
                if($meta['project_id'] <= 0 && isset($frontUser['Client']['project_id']) && (int)$frontUser['Client']['project_id'] > 0)
                {
                    $meta['project_id'] = (int)$frontUser['Client']['project_id'];
                }
            }
        }
        return $meta;
    }

    protected function _trashEntryTitle($recordData)
    {
        $recordData = is_array($recordData) ? $recordData : array();
        $titleFields = array(
            'name',
            'title',
            'property_name',
            'invoice_no',
            'ref_no',
            'email',
            'mobile',
            'phone',
            'username',
            'description'
        );
        foreach($titleFields as $field)
        {
            if(isset($recordData[$field]) && trim((string)$recordData[$field]) !== '')
            {
                return trim((string)$recordData[$field]);
            }
        }
        if(isset($recordData[$this->primaryKey]) && trim((string)$recordData[$this->primaryKey]) !== '')
        {
            return trim((string)$this->alias) . ' #' . trim((string)$recordData[$this->primaryKey]);
        }
        return trim((string)$this->alias);
    }

    protected function _captureDeleteToTrashById($recordId)
    {
        $recordId = trim((string)$recordId);
        if($recordId === '' || $this->_shouldSkipTrashCapture())
        {
            return;
        }
        if(!$this->_ensureTrashTable())
        {
            return;
        }

        $tableName = strtolower(trim((string)$this->useTable));
        $cacheKey = $tableName . '|' . $recordId;
        if(isset(self::$_trashCaptureCache[$cacheKey]))
        {
            return;
        }

        $record = array();
        try
        {
            $record = $this->find('first', array(
                'conditions' => array($this->alias . '.' . $this->primaryKey => $recordId),
                'recursive' => -1
            ));
        }
        catch(Exception $e)
        {
            $record = array();
        }
        if(empty($record[$this->alias]) || !is_array($record[$this->alias]))
        {
            return;
        }
        $row = $record[$this->alias];
        $sessionMeta = $this->_trashSessionMeta();
        $projectId = 0;
        if(isset($row['project_id']) && (int)$row['project_id'] > 0)
        {
            $projectId = (int)$row['project_id'];
        }
        elseif((int)$sessionMeta['project_id'] > 0)
        {
            $projectId = (int)$sessionMeta['project_id'];
        }

        $payloadJson = json_encode(array('record' => $row));
        if($payloadJson === false)
        {
            $payloadJson = '{"record":{}}';
        }

        $ds = $this->getDataSource();
        $safeTable = preg_replace('/[^a-zA-Z0-9_]/', '', $tableName);
        if($safeTable === '')
        {
            return;
        }

        $sourceId = 0;
        if(isset($row[$this->primaryKey]) && is_numeric($row[$this->primaryKey]))
        {
            $sourceId = (int)$row[$this->primaryKey];
        }

        $sql = 'INSERT INTO `ardaccounts_trash_bin` (`project_id`,`source_table`,`source_model`,`source_id`,`entry_title`,`payload_json`,`deleted_by`,`deleted_at`,`created`,`modified`) VALUES (' .
            (int)$projectId . ',' .
            $ds->value($safeTable) . ',' .
            $ds->value($this->alias) . ',' .
            (int)$sourceId . ',' .
            $ds->value($this->_trashEntryTitle($row)) . ',' .
            $ds->value($payloadJson) . ',' .
            (int)$sessionMeta['user_id'] . ',' .
            $ds->value(date('Y-m-d H:i:s')) . ',' .
            $ds->value(date('Y-m-d H:i:s')) . ',' .
            $ds->value(date('Y-m-d H:i:s')) .
            ')';
        try
        {
            $this->query($sql);
            self::$_trashCaptureCache[$cacheKey] = true;
        }
        catch(Exception $e)
        {
            // Do not interrupt main delete flow.
        }
    }

    public function beforeDelete($cascade = true)
    {
        if($this->id !== null && $this->id !== '')
        {
            $this->_captureDeleteToTrashById($this->id);
        }
        return true;
    }

    public function deleteAll($conditions, $cascade = true, $callbacks = false)
    {
        if(!$this->_shouldSkipTrashCapture())
        {
            $recordIds = array();
            try
            {
                $rows = $this->find('all', array(
                    'fields' => array($this->alias . '.' . $this->primaryKey),
                    'conditions' => $conditions,
                    'recursive' => -1
                ));
                foreach((array)$rows as $row)
                {
                    if(isset($row[$this->alias][$this->primaryKey]))
                    {
                        $recordIds[] = (string)$row[$this->alias][$this->primaryKey];
                    }
                }
            }
            catch(Exception $e)
            {
                $recordIds = array();
            }
            foreach(array_unique($recordIds) as $recordId)
            {
                $this->_captureDeleteToTrashById($recordId);
            }
        }
        return parent::deleteAll($conditions, $cascade, $callbacks);
    }

    function unbindValidation($type, $fields, $require=false) 
    { 
        if ($type === 'remove') 
        { 
            $this->validate = array_diff_key($this->validate, array_flip($fields)); 
        } 
        else 
        if ($type === 'keep') 
        { 
            $this->validate = array_intersect_key($this->validate, array_flip($fields)); 
        } 
         
        if ($require === true) 
        { 
            foreach ($this->validate as $field=>$rules) 
            { 
                if (is_array($rules)) 
                { 
                    $rule = key($rules); 
                     
                    $this->validate[$field][$rule]['required'] = true; 
                } 
                else 
                { 
                    $ruleName = (ctype_alpha($rules)) ? $rules : 'required'; 
                     
                    $this->validate[$field] = array($ruleName=>array('rule'=>$rules,'required'=>true)); 
                } 
            } 
        } 
    }
    protected function _looksLegacyPasswordHash($hash)
    {
        return (bool)preg_match('/^[a-f0-9]{64}$/i', trim((string)$hash));
    }

    protected function _looksModernPasswordHash($hash)
    {
        $hash = trim((string)$hash);
        if($hash === '')
        {
            return false;
        }
        if(strpos($hash, '$2y$') === 0 || strpos($hash, '$2a$') === 0 || strpos($hash, '$2b$') === 0)
        {
            return true;
        }
        if(strpos($hash, '$argon2') === 0)
        {
            return true;
        }
        return false;
    }

    protected function _hashPasswordValue($plainPassword)
    {
        $plainPassword = (string)$plainPassword;
        if(function_exists('password_hash'))
        {
            return password_hash($plainPassword, PASSWORD_BCRYPT);
        }
        $passwordHasher = new SimplePasswordHasher(array('hashType' => 'sha256'));
        return $passwordHasher->hash($plainPassword);
    }

    public function beforeSave($options = array())
    {
        $alias = $this->alias;
        // Never hash credentials for SMTP, SMS, Facebook, or WhatsApp configuration models
        if(in_array($alias, array('Emailsetting', 'Smssetting', 'FacebookSetting', 'WhatsappSetting', 'WhatsappProjectGateway')))
        {
            return parent::beforeSave($options);
        }
        if(isset($this->data[$alias]) && is_array($this->data[$alias]) && array_key_exists('password', $this->data[$alias]))
        {
            $password = (string)$this->data[$alias]['password'];
            if($password !== '' && !$this->_looksLegacyPasswordHash($password) && !$this->_looksModernPasswordHash($password))
            {
                $this->data[$alias]['password'] = $this->_hashPasswordValue($password);
            }
        }
        return parent::beforeSave($options);
    }
    public function alphaNumericCustom($check) {
        // $data array is passed using the form field name as the key
        // have to extract the value to make the function generic
        $value = array_values($check);
        $value = $value[0];
        return true;
        //return preg_match('/^[a-zA-Z0-9 \/ \s\\\\,-.:;"~!@#$&%&*{}\\[\\]()_=+|?]*$/i', $value);
    }
    public function passwordHasher($plainPassword)
    {
        return $this->_hashPasswordValue($plainPassword);
    }
    public function dateFormatBeforeSave($dateString)
    {
      return date('Y-m-d', strtotime($dateString));
    }
    public function dateTimeFormatBeforeSave($dateString)
    {
      return date('Y-m-d H:i:s', strtotime($dateString));
    }
}
