<?php
class Addresshierarchy extends AppModel
{
    public $useTable = 'address_hierarchies';
    public $actsAs = array('Containable');

    public $levelOrder = array('country','province','district','tehsil','city','town','block');

    public function stripBom($str)
    {
        if(!is_string($str))
        {
            return '';
        }
        if(substr($str, 0, 3) === "\xEF\xBB\xBF")
        {
            $str = substr($str, 3);
        }
        elseif(substr($str, 0, 2) === "\xFE\xFF" || substr($str, 0, 2) === "\xFF\xFE")
        {
            $str = substr($str, 2);
        }
        return $str;
    }

    public function ensureSchema()
    {
        try
        {
            $this->query("CREATE TABLE IF NOT EXISTS address_hierarchies (
                id int(11) NOT NULL AUTO_INCREMENT,
                parent_id int(11) NOT NULL DEFAULT 0,
                level_type varchar(30) NOT NULL,
                name varchar(160) NOT NULL,
                slug varchar(180) NOT NULL,
                sort_order int(11) NOT NULL DEFAULT 0,
                status tinyint(1) NOT NULL DEFAULT 1,
                created datetime DEFAULT NULL,
                modified datetime DEFAULT NULL,
                PRIMARY KEY (id),
                KEY parent_id (parent_id),
                KEY level_type (level_type),
                KEY status (status),
                UNIQUE KEY uniq_address_node (parent_id,level_type,slug)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3");

            // Guarantee id is AUTO_INCREMENT and remove corrupt 0 records
            try
            {
                $cols = $this->query("SHOW COLUMNS FROM address_hierarchies LIKE 'id'");
                $isAuto = false;
                if(!empty($cols[0]['COLUMNS']['Extra']))
                {
                    $isAuto = (stripos((string)$cols[0]['COLUMNS']['Extra'], 'auto_increment') !== false);
                }
                if(!$isAuto)
                {
                    $this->query("DELETE FROM address_hierarchies WHERE id = 0");
                    $this->query("ALTER TABLE address_hierarchies MODIFY id int(11) NOT NULL AUTO_INCREMENT");
                }
            }
            catch(Exception $ex) {}

            $this->schema(true);
            $this->seedPakistanDefaults();
            $this->ensureRegionalDefaults();
            return true;
        }
        catch(Exception $e)
        {
            CakeLog::write('error', 'Address hierarchy schema failed: '.$e->getMessage());
            return false;
        }
    }

    public function ensureTargetColumns($tableName)
    {
        $tableName = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$tableName);
        if($tableName === '')
        {
            return false;
        }
        $columns = array(
            'address_country_id',
            'address_province_id',
            'address_district_id',
            'address_tehsil_id',
            'address_city_id',
            'address_town_id',
            'address_block_id'
        );
        foreach($columns as $column)
        {
            try
            {
                $exists = $this->query("SHOW COLUMNS FROM `{$tableName}` LIKE '{$column}'");
                if(empty($exists))
                {
                    $this->query("ALTER TABLE `{$tableName}` ADD `{$column}` int(11) NOT NULL DEFAULT 0");
                }
            }
            catch(Exception $e) {}
        }
        try
        {
            $exists = $this->query("SHOW COLUMNS FROM `{$tableName}` LIKE 'address_hierarchy_path'");
            if(empty($exists))
            {
                $this->query("ALTER TABLE `{$tableName}` ADD `address_hierarchy_path` varchar(700) DEFAULT ''");
            }
        }
        catch(Exception $e) {}
        return true;
    }

    public function levelOptions($levelType, $parentId = null)
    {
        $conditions = array(
            'Addresshierarchy.level_type' => $levelType,
            'Addresshierarchy.status' => 1
        );
        if($parentId !== null)
        {
            $conditions['Addresshierarchy.parent_id'] = (int)$parentId;
        }
        return (array)$this->find('list', array(
            'fields' => array('Addresshierarchy.id','Addresshierarchy.name'),
            'conditions' => $conditions,
            'order' => array('Addresshierarchy.sort_order' => 'asc', 'Addresshierarchy.name' => 'asc'),
            'recursive' => -1
        ));
    }

    public function treePayload()
    {
        $rows = (array)$this->find('all', array(
            'fields' => array('Addresshierarchy.id','Addresshierarchy.parent_id','Addresshierarchy.level_type','Addresshierarchy.name','Addresshierarchy.status'),
            'conditions' => array('Addresshierarchy.status' => 1),
            'order' => array('Addresshierarchy.sort_order' => 'asc', 'Addresshierarchy.name' => 'asc'),
            'recursive' => -1
        ));
        $payload = array();
        foreach($this->levelOrder as $level)
        {
            $payload[$level] = array();
        }
        foreach($rows as $row)
        {
            $node = $row['Addresshierarchy'];
            $level = isset($node['level_type']) ? $node['level_type'] : '';
            if(!isset($payload[$level]))
            {
                continue;
            }
            $parentId = (int)$node['parent_id'];
            if(!isset($payload[$level][$parentId]))
            {
                $payload[$level][$parentId] = array();
            }
            $payload[$level][$parentId][] = array('id' => (int)$node['id'], 'name' => (string)$node['name']);
        }
        return $payload;
    }

    public function buildPathFromData($data)
    {
        $data = (array)$data;
        $ids = array();
        foreach($this->levelOrder as $level)
        {
            $field = 'address_'.$level.'_id';
            if(!empty($data[$field]))
            {
                $ids[] = (int)$data[$field];
            }
        }
        if(empty($ids))
        {
            return '';
        }
        $rows = (array)$this->find('list', array(
            'fields' => array('Addresshierarchy.id','Addresshierarchy.name'),
            'conditions' => array('Addresshierarchy.id' => $ids),
            'recursive' => -1
        ));
        $parts = array();
        foreach($ids as $id)
        {
            if(!empty($rows[$id]))
            {
                $parts[] = $rows[$id];
            }
        }
        return implode(', ', $parts);
    }

    public function normalizeHierarchyData(&$data)
    {
        if(!is_array($data))
        {
            return;
        }
        foreach($this->levelOrder as $level)
        {
            $field = 'address_'.$level.'_id';
            if(isset($data[$field]))
            {
                $data[$field] = (int)$data[$field];
            }
        }
        $data['address_hierarchy_path'] = $this->buildPathFromData($data);
        if(!empty($data['address_district_id']))
        {
            $district = $this->field('name', array('Addresshierarchy.id' => (int)$data['address_district_id']));
            if($district !== false && $district !== null && trim((string)$district) !== '')
            {
                $data['district'] = (string)$district;
            }
        }
        if(!empty($data['address_province_id']))
        {
            $province = $this->field('name', array('Addresshierarchy.id' => (int)$data['address_province_id']));
            if($province !== false && $province !== null && trim((string)$province) !== '')
            {
                $data['state'] = (string)$province;
            }
        }
    }

    public function importPath($path)
    {
        $path = (array)$path;
        $parentId = 0;
        $lastId = 0;
        foreach($this->levelOrder as $index => $level)
        {
            $name = '';
            if(isset($path[$level]))
            {
                $name = $this->stripBom(trim((string)$path[$level]));
            }
            elseif(isset($path[$index]))
            {
                $name = $this->stripBom(trim((string)$path[$index]));
            }
            if($name === '')
            {
                continue;
            }
            $nodeId = $this->findOrCreateNode($level, $name, $parentId);
            if($nodeId <= 0)
            {
                break;
            }
            $lastId = $nodeId;
            $parentId = $nodeId;
        }
        return $lastId;
    }

    public function findOrCreateNode($levelType, $name, $parentId = 0)
    {
        $name = $this->stripBom(trim((string)$name));
        $levelType = trim((string)$levelType);
        $parentId = (int)$parentId;
        if($name === '' || !in_array($levelType, $this->levelOrder, true))
        {
            return 0;
        }
        $slug = $this->slugify($name);

        $row = $this->find('first', array(
            'fields' => array('Addresshierarchy.id'),
            'conditions' => array(
                'Addresshierarchy.parent_id' => $parentId,
                'Addresshierarchy.level_type' => $levelType,
                'Addresshierarchy.slug' => $slug
            ),
            'recursive' => -1
        ));
        if(!empty($row['Addresshierarchy']['id']))
        {
            return (int)$row['Addresshierarchy']['id'];
        }

        $row = $this->find('first', array(
            'fields' => array('Addresshierarchy.id'),
            'conditions' => array(
                'Addresshierarchy.parent_id' => $parentId,
                'Addresshierarchy.level_type' => $levelType,
                'Addresshierarchy.name' => $name
            ),
            'recursive' => -1
        ));
        if(!empty($row['Addresshierarchy']['id']))
        {
            return (int)$row['Addresshierarchy']['id'];
        }

        try
        {
            $this->create();
            $saved = $this->save(array('Addresshierarchy' => array(
                'parent_id' => $parentId,
                'level_type' => $levelType,
                'name' => $name,
                'slug' => $slug,
                'sort_order' => 0,
                'status' => 1
            )), false);
            $newId = !empty($this->id) ? (int)$this->id : (int)$this->getInsertID();
            if($saved && $newId > 0)
            {
                return $newId;
            }
        }
        catch(Exception $e)
        {
            $row = $this->find('first', array(
                'fields' => array('Addresshierarchy.id'),
                'conditions' => array(
                    'Addresshierarchy.parent_id' => $parentId,
                    'Addresshierarchy.level_type' => $levelType,
                    'Addresshierarchy.slug' => $slug
                ),
                'recursive' => -1
            ));
            if(!empty($row['Addresshierarchy']['id']))
            {
                return (int)$row['Addresshierarchy']['id'];
            }
        }
        return 0;
    }

    public function slugify($value)
    {
        $value = $this->stripBom((string)$value);
        $original = trim($value);
        $value = mb_strtolower($original, 'UTF-8');
        $value = preg_replace('/[^\p{L}\p{N}]+/u', '-', $value);
        $value = trim($value, '-');
        return $value !== '' ? $value : md5(mb_strtolower($original, 'UTF-8'));
    }

    public function seedPakistanDefaults()
    {
        $exists = (int)$this->find('count', array('recursive' => -1));
        if($exists > 0)
        {
            return false;
        }
        $rows = array(
            array('Pakistan','Khyber Pakhtunkhwa','Mansehra','Balakot','Naran','River View','Main'),
            array('Pakistan','Khyber Pakhtunkhwa','Mansehra','Balakot','Naran','Bazar','Block A'),
            array('Pakistan','Khyber Pakhtunkhwa','Mansehra','Balakot','Kaghan','Kaghan Valley','Main'),
            array('Pakistan','Khyber Pakhtunkhwa','Mansehra','Mansehra','Mansehra','City Center','Block 1'),
            array('Pakistan','Khyber Pakhtunkhwa','Abbottabad','Abbottabad','Abbottabad','Cantt','Phase 1'),
            array('Pakistan','Khyber Pakhtunkhwa','Swat','Babuzai','Mingora','Saidu Sharif','Main'),
            array('Pakistan','Khyber Pakhtunkhwa','Peshawar','Peshawar City','Peshawar','Hayatabad','Phase 1'),
            array('Pakistan','Khyber Pakhtunkhwa','Mardan','Mardan','Mardan','Cantt','Block A'),
            array('Pakistan','Islamabad Capital Territory','Islamabad','Islamabad','Islamabad','G-13','Block 1'),
            array('Pakistan','Islamabad Capital Territory','Islamabad','Islamabad','Islamabad','F-6','Sector F-6'),
            array('Pakistan','Islamabad Capital Territory','Islamabad','Islamabad','Islamabad','Blue Area','Jinnah Avenue'),
            array('Pakistan','Punjab','Lahore','Lahore City','Lahore','Model Town','Block A'),
            array('Pakistan','Punjab','Lahore','Raiwind','Raiwind','Bahria Orchard','Block A'),
            array('Pakistan','Punjab','Lahore','Shalimar','Lahore','DHA','Phase 1'),
            array('Pakistan','Punjab','Rawalpindi','Rawalpindi','Rawalpindi','Bahria Town','Phase 7'),
            array('Pakistan','Punjab','Multan','Multan City','Multan','Cantonment','Block A'),
            array('Pakistan','Sindh','Karachi East','Gulshan Town','Karachi','Gulshan-e-Iqbal','Block 13'),
            array('Pakistan','Sindh','Karachi South','Saddar Town','Karachi','Clifton','Block 2'),
            array('Pakistan','Balochistan','Quetta','Quetta City','Quetta','Cantt','Block A')
        );
        foreach($rows as $row)
        {
            $this->importPath($row);
        }
        return true;
    }

    public function ensureRegionalDefaults()
    {
        try
        {
            $hasNaran = (int)$this->find('count', array(
                'conditions' => array('Addresshierarchy.slug' => 'naran', 'Addresshierarchy.level_type' => 'city'),
                'recursive' => -1
            ));
            if($hasNaran <= 0)
            {
                $kpkChains = array(
                    array('Pakistan','Khyber Pakhtunkhwa','Mansehra','Balakot','Naran','River View','Main'),
                    array('Pakistan','Khyber Pakhtunkhwa','Mansehra','Balakot','Naran','Bazar','Block A'),
                    array('Pakistan','Khyber Pakhtunkhwa','Mansehra','Balakot','Kaghan','Kaghan Valley','Main'),
                    array('Pakistan','Khyber Pakhtunkhwa','Mansehra','Mansehra','Mansehra','City Center','Block 1'),
                    array('Pakistan','Khyber Pakhtunkhwa','Abbottabad','Abbottabad','Abbottabad','Cantt','Phase 1'),
                    array('Pakistan','Khyber Pakhtunkhwa','Swat','Babuzai','Mingora','Saidu Sharif','Main')
                );
                foreach($kpkChains as $chain)
                {
                    $this->importPath($chain);
                }
            }
        }
        catch(Exception $e) {}
    }

    public function resolveHierarchyFromRow($propertyRow)
    {
        $data = (array)$propertyRow;
        $resolved = array();
        $hasAnyId = false;
        foreach($this->levelOrder as $level)
        {
            $field = 'address_'.$level.'_id';
            $val = !empty($data[$field]) ? (int)$data[$field] : 0;
            $resolved[$field] = $val;
            if($val > 0 && $level !== 'country')
            {
                $hasAnyId = true;
            }
        }
        if($hasAnyId)
        {
            return $resolved;
        }

        $textSources = array();
        if(!empty($data['address_hierarchy_path']))
        {
            $textSources[] = (string)$data['address_hierarchy_path'];
        }
        if(!empty($data['address']))
        {
            $textSources[] = (string)$data['address'];
        }

        foreach($textSources as $sourceText)
        {
            $parts = array_map('trim', explode(',', (string)$sourceText));
            foreach($parts as $part)
            {
                if($part === '') continue;
                $slug = $this->slugify($part);
                $node = $this->find('first', array(
                    'conditions' => array(
                        'OR' => array(
                            'Addresshierarchy.name LIKE' => '%'.$part.'%',
                            'Addresshierarchy.slug' => $slug
                        ),
                        'Addresshierarchy.status' => 1
                    ),
                    'order' => array('Addresshierarchy.id' => 'desc'),
                    'recursive' => -1
                ));
                if(!empty($node['Addresshierarchy']))
                {
                    $lvl = $node['Addresshierarchy']['level_type'];
                    $resolved['address_'.$lvl.'_id'] = (int)$node['Addresshierarchy']['id'];
                    $curr = $node['Addresshierarchy'];
                    while(!empty($curr['parent_id']))
                    {
                        $parent = $this->find('first', array(
                            'conditions' => array('Addresshierarchy.id' => (int)$curr['parent_id']),
                            'recursive' => -1
                        ));
                        if(!empty($parent['Addresshierarchy']))
                        {
                            $pLvl = $parent['Addresshierarchy']['level_type'];
                            if(empty($resolved['address_'.$pLvl.'_id']))
                            {
                                $resolved['address_'.$pLvl.'_id'] = (int)$parent['Addresshierarchy']['id'];
                            }
                            $curr = $parent['Addresshierarchy'];
                        }
                        else
                        {
                            break;
                        }
                    }
                }
            }
        }
        return $resolved;
    }
}
?>
