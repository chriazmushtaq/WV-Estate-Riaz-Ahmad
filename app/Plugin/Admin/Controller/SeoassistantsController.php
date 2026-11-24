<?php
App::uses('CakeText', 'Utility');
App::uses('Router', 'Routing');

class SeoassistantsController extends AdminAppController
{
    public $helpers = array('Html','Form','Session');
    public $components = array('Session','CustomFunction');
    public $uses = array('Property','PropertySeo','Admin.Agency','Admin.AgencySeo');

    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->_ensureSeoTables();
    }

    public function index()
    {
        $type = isset($this->request->query['type']) ? strtolower(trim((string)$this->request->query['type'])) : 'property';
        if(!in_array($type, array('property','agency'), true))
        {
            $type = 'property';
        }
        $entityId = isset($this->request->query['entity_id']) ? (int)$this->request->query['entity_id'] : 0;

        if($this->request->is('post'))
        {
            $data = isset($this->request->data['SeoSuggestion']) ? (array)$this->request->data['SeoSuggestion'] : array();
            $saveType = !empty($data['type']) && $data['type'] === 'agency' ? 'agency' : 'property';
            $saveId = !empty($data['entity_id']) ? (int)$data['entity_id'] : 0;
            if($saveId > 0)
            {
                $saved = $this->_saveSuggestion($saveType, $saveId, $data);
                $this->Session->setFlash($saved ? __('SEO suggestion saved.') : __('Unable to save SEO suggestion.'), 'flash', array('alert'=>$saved ? 'success' : 'danger'));
                return $this->redirect(array('action'=>'index','?'=>array('type'=>$saveType,'entity_id'=>$saveId)));
            }
            $this->Session->setFlash(__('Please select a valid record.'), 'flash', array('alert'=>'danger'));
        }

        $propertyOptions = $this->_propertyOptions();
        $agencyOptions = $this->CustomFunction->agencyWiseList($this->userType, $this->agencyId);
        if($entityId <= 0)
        {
            $firstOptions = ($type === 'agency') ? $agencyOptions : $propertyOptions;
            if(!empty($firstOptions))
            {
                $ids = array_keys($firstOptions);
                $entityId = (int)reset($ids);
            }
        }

        $selected = array();
        $existingSeo = array();
        $suggestion = array();
        if($entityId > 0)
        {
            if($type === 'agency')
            {
                $selected = $this->_agencyRow($entityId);
                $existingSeo = $this->AgencySeo->find('first', array('conditions'=>array('AgencySeo.agency_id'=>$entityId),'recursive'=>-1));
                if(!empty($selected['Agency']['id']))
                {
                    $suggestion = $this->_agencySuggestion($selected);
                }
            }
            else
            {
                $selected = $this->_propertyRow($entityId);
                $existingSeo = $this->PropertySeo->find('first', array('conditions'=>array('PropertySeo.property_id'=>$entityId),'recursive'=>-1));
                if(!empty($selected['Property']['id']))
                {
                    $suggestion = $this->_propertySuggestion($selected);
                }
            }
        }
        $this->set(compact('type','entityId','propertyOptions','agencyOptions','selected','existingSeo','suggestion'));
    }

    protected function _ensureSeoTables()
    {
        try
        {
            $this->PropertySeo->query("CREATE TABLE IF NOT EXISTS `property_seos` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `property_id` int(11) NOT NULL DEFAULT 0,
                `slug` varchar(191) DEFAULT '',
                `meta_title` varchar(191) DEFAULT '',
                `meta_description` text,
                `canonical_url` varchar(255) DEFAULT '',
                `schema_enabled` tinyint(1) NOT NULL DEFAULT 1,
                `created` datetime DEFAULT NULL,
                `modified` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `property_id` (`property_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8");
        }
        catch(Exception $e)
        {
        }
        $this->_ensureColumn($this->PropertySeo, 'property_seos', 'canonical_url', "ALTER TABLE `property_seos` ADD COLUMN `canonical_url` varchar(255) DEFAULT ''");
        $this->_ensureColumn($this->PropertySeo, 'property_seos', 'schema_enabled', "ALTER TABLE `property_seos` ADD COLUMN `schema_enabled` tinyint(1) NOT NULL DEFAULT 1");
        try
        {
            $this->AgencySeo->query("CREATE TABLE IF NOT EXISTS `agency_seos` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `agency_id` int(11) NOT NULL DEFAULT 0,
                `slug` varchar(191) DEFAULT '',
                `meta_title` varchar(191) DEFAULT '',
                `meta_description` text,
                `canonical_url` varchar(255) DEFAULT '',
                `schema_enabled` tinyint(1) NOT NULL DEFAULT 1,
                `created` datetime DEFAULT NULL,
                `modified` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `agency_id` (`agency_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8");
        }
        catch(Exception $e)
        {
        }
    }

    protected function _ensureColumn($model, $table, $column, $sql)
    {
        try
        {
            $rows = (array)$model->query("SHOW COLUMNS FROM `{$table}` LIKE '".addslashes($column)."'");
            if(empty($rows))
            {
                $model->query($sql);
            }
        }
        catch(Exception $e)
        {
        }
    }

    protected function _propertyOptions()
    {
        $conditions = array();
        $permission = $this->CustomFunction->userWisePermission(null, $this->luserId, $this->userType, $this->agencyId, 'Property.project_id', 'Property.user_id');
        if(!empty($permission))
        {
            $conditions[] = $permission;
        }
        return (array)$this->Property->find('list', array(
            'fields'=>array('Property.id','Property.name'),
            'conditions'=>$conditions,
            'order'=>array('Property.id'=>'desc'),
            'limit'=>200,
            'recursive'=>-1
        ));
    }

    protected function _propertyRow($id)
    {
        return (array)$this->Property->find('first', array(
            'fields'=>array('Property.*','Project.name','Location.name','Type.name','Contract.name','Unit.name'),
            'joins'=>array(
                array('table'=>'projects','alias'=>'Project','type'=>'LEFT','conditions'=>array('Project.id=Property.project_id')),
                array('table'=>'locations','alias'=>'Location','type'=>'LEFT','conditions'=>array('Location.id=Property.location_id')),
                array('table'=>'types','alias'=>'Type','type'=>'LEFT','conditions'=>array('Type.id=Property.type_id')),
                array('table'=>'contracts','alias'=>'Contract','type'=>'LEFT','conditions'=>array('Contract.id=Property.contract_id')),
                array('table'=>'units','alias'=>'Unit','type'=>'LEFT','conditions'=>array('Unit.id=Property.unit_id'))
            ),
            'conditions'=>array('Property.id'=>(int)$id),
            'recursive'=>-1
        ));
    }

    protected function _agencyRow($id)
    {
        return (array)$this->Agency->find('first', array(
            'fields'=>array('Agency.*','User.name','User.description','User.mobile','User.email','Location.name'),
            'conditions'=>array('Agency.id'=>(int)$id),
            'recursive'=>0
        ));
    }

    protected function _slug($text, $fallback)
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', (string)$text), '-'));
        if($slug === '')
        {
            $slug = $fallback;
        }
        return substr($slug, 0, 180);
    }

    protected function _limitText($text, $length)
    {
        $text = trim(preg_replace('/\s+/', ' ', strip_tags((string)$text)));
        if(strlen($text) <= $length)
        {
            return $text;
        }
        return CakeText::truncate($text, $length, array('ellipsis'=>'','exact'=>false));
    }

    protected function _propertySuggestion($row)
    {
        $p = (array)$row['Property'];
        $location = !empty($row['Location']['name']) ? $row['Location']['name'] : '';
        $type = !empty($row['Type']['name']) ? $row['Type']['name'] : __('Property');
        $project = !empty($row['Project']['name']) ? $row['Project']['name'] : $this->siteName;
        $name = !empty($p['name']) ? $p['name'] : $type;
        $title = trim($name.' '.$type.($location !== '' ? ' in '.$location : '').' | '.$project);
        $descBase = $this->_limitText(!empty($p['description']) ? $p['description'] : '', 150);
        if($descBase === '')
        {
            $parts = array($name, $type, $project);
            if($location !== ''){$parts[] = $location;}
            if(!empty($p['area'])){$parts[] = $p['area'].' '.(!empty($row['Unit']['name']) ? $row['Unit']['name'] : '');}
            $descBase = implode(' - ', array_filter($parts)).'. Contact '.$this->siteName.' for details, price, location and booking information.';
        }
        return array(
            'slug' => $this->_slug($name.' '.$location.' '.$type, 'property-'.$p['id']),
            'meta_title' => $this->_limitText($title, 70),
            'meta_description' => $this->_limitText($descBase, 158),
            'canonical_url' => Router::url(array('plugin'=>false,'admin'=>false,'controller'=>'Properties','action'=>'view',(int)$p['id']), true),
            'schema_enabled' => 1
        );
    }

    protected function _agencySuggestion($row)
    {
        $agency = (array)$row['Agency'];
        $user = !empty($row['User']) ? (array)$row['User'] : array();
        $location = !empty($row['Location']['name']) ? $row['Location']['name'] : '';
        $name = !empty($agency['name']) ? $agency['name'] : (!empty($user['name']) ? $user['name'] : __('Agency'));
        $title = trim($name.($location !== '' ? ' Real Estate Agency in '.$location : ' Real Estate Agency').' | '.$this->siteName);
        $desc = $this->_limitText(!empty($user['description']) ? $user['description'] : (!empty($agency['description']) ? $agency['description'] : ''), 155);
        if($desc === '')
        {
            $desc = $name.' offers real estate listings, agent support, property visits, inquiries and booking guidance with '.$this->siteName.'.';
        }
        return array(
            'slug' => $this->_slug($name.' '.$location.' real estate agency', 'agency-'.$agency['id']),
            'meta_title' => $this->_limitText($title, 70),
            'meta_description' => $this->_limitText($desc, 158),
            'canonical_url' => Router::url(array('plugin'=>false,'admin'=>false,'controller'=>'Agencies','action'=>'view',(int)$agency['id']), true),
            'schema_enabled' => 1
        );
    }

    protected function _saveSuggestion($type, $id, $data)
    {
        $save = array(
            'slug' => $this->_slug(isset($data['slug']) ? $data['slug'] : '', $type.'-'.$id),
            'meta_title' => $this->_limitText(isset($data['meta_title']) ? $data['meta_title'] : '', 191),
            'meta_description' => $this->_limitText(isset($data['meta_description']) ? $data['meta_description'] : '', 255),
            'canonical_url' => trim((string)(isset($data['canonical_url']) ? $data['canonical_url'] : '')),
            'schema_enabled' => !empty($data['schema_enabled']) ? 1 : 0
        );
        if($type === 'agency')
        {
            $existing = $this->AgencySeo->find('first', array('conditions'=>array('AgencySeo.agency_id'=>$id),'recursive'=>-1));
            $save['agency_id'] = (int)$id;
            if(!empty($existing['AgencySeo']['id'])){$save['id'] = (int)$existing['AgencySeo']['id'];}
            $this->AgencySeo->create();
            return (bool)$this->AgencySeo->save(array('AgencySeo'=>$save), array('validate'=>false));
        }
        $existing = $this->PropertySeo->find('first', array('conditions'=>array('PropertySeo.property_id'=>$id),'recursive'=>-1));
        $save['property_id'] = (int)$id;
        if(!empty($existing['PropertySeo']['id'])){$save['id'] = (int)$existing['PropertySeo']['id'];}
        $this->PropertySeo->create();
        return (bool)$this->PropertySeo->save(array('PropertySeo'=>$save), array('validate'=>false));
    }
}
?>
