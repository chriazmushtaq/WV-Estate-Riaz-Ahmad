<?php
class PropertyenhancementsController extends AdminAppController
{
    public $helpers = array('Html', 'Form', 'Session', 'Paginator');
    public $components = array('Session', 'Paginator', 'CustomFunction');
    public $paginate = array('page' => 1, 'limit' => 30, 'order' => array('Property.id' => 'desc'));

    protected function _canAccessPropertyRecord($propertyRow)
    {
        if($this->_isSuperAdminSession() || strtoupper(trim((string)$this->userType)) === 'ADR')
        {
            return true;
        }
        $propertyRow = (array)$propertyRow;
        if(empty($propertyRow['Property']['id']))
        {
            return false;
        }
        if(strtoupper(trim((string)$this->userType)) === 'AGY')
        {
            return ((int)$propertyRow['Property']['project_id'] === (int)$this->agencyId);
        }
        return ((int)$propertyRow['Property']['user_id'] === (int)$this->luserId);
    }

    protected function _propertyOrRedirect($id)
    {
        $id = (int)$id;
        $this->loadModel('Property');
        $property = $this->Property->find('first', array(
            'conditions' => array('Property.id' => $id),
            'recursive' => 1
        ));
        if(empty($property) || !$this->_canAccessPropertyRecord($property))
        {
            $this->Session->setFlash(__('Invalid Post'), 'flash', array('alert' => 'danger'));
            return false;
        }
        return $property;
    }

    protected function _propertySlug($value, $id = null)
    {
        $slug = trim(preg_replace('/[^A-Za-z0-9]+/', '-', (string)$value), '-');
        if($slug === '')
        {
            $slug = 'property';
            if($id !== null && (int)$id > 0)
            {
                $slug .= '-'.(int)$id;
            }
        }
        return strtolower($slug);
    }

    protected function _qualityChecklist($property)
    {
        $propertyId = (int)$property['Property']['id'];
        $this->loadModel('PropertiesPhoto');
        $this->loadModel('PropertiesDocument');
        $this->loadModel('PropertiesFloorplan');
        $this->loadModel('PropertiesFeature');
        $this->loadModel('PropertySeo');

        $frontPhotoCount = (int)$this->PropertiesPhoto->find('count', array(
            'conditions' => array('PropertiesPhoto.property_id' => $propertyId, 'PropertiesPhoto.home' => 'Yes'),
            'recursive' => -1
        ));
        $photoCount = (int)$this->PropertiesPhoto->find('count', array(
            'conditions' => array('PropertiesPhoto.property_id' => $propertyId),
            'recursive' => -1
        ));
        $documentConditions = array('PropertiesDocument.property_id' => $propertyId);
        if($this->PropertiesDocument->hasField('public_visible'))
        {
            $documentConditions['PropertiesDocument.public_visible'] = 1;
        }
        $documentCount = (int)$this->PropertiesDocument->find('count', array(
            'conditions' => $documentConditions,
            'recursive' => -1
        ));
        $floorplanCount = (int)$this->PropertiesFloorplan->find('count', array(
            'conditions' => array('PropertiesFloorplan.property_id' => $propertyId),
            'recursive' => -1
        ));
        $featureCount = (int)$this->PropertiesFeature->find('count', array(
            'conditions' => array('PropertiesFeature.property_id' => $propertyId),
            'recursive' => -1
        ));
        $seo = $this->PropertySeo->find('first', array(
            'conditions' => array('PropertySeo.property_id' => $propertyId),
            'recursive' => -1
        ));

        $lat = trim((string)(isset($property['Property']['latitude']) ? $property['Property']['latitude'] : ''));
        $lng = trim((string)(isset($property['Property']['longitude']) ? $property['Property']['longitude'] : ''));
        $checks = array(
            array('key' => 'front_photo', 'label' => __('Main front photo selected'), 'done' => $frontPhotoCount > 0),
            array('key' => 'gallery', 'label' => __('Gallery photos available'), 'done' => $photoCount > 1),
            array('key' => 'price', 'label' => __('Valid property price'), 'done' => ((float)$property['Property']['price'] > 0)),
            array('key' => 'description', 'label' => __('Useful description added'), 'done' => strlen(trim(strip_tags((string)$property['Property']['description']))) >= 40),
            array('key' => 'map', 'label' => __('Map latitude and longitude added'), 'done' => ($lat !== '' && $lng !== '' && $lat !== '0' && $lng !== '0')),
            array('key' => 'taxonomy', 'label' => __('Type, contract, location, and unit selected'), 'done' => (!empty($property['Property']['type_id']) && !empty($property['Property']['contract_id']) && !empty($property['Property']['location_id']) && !empty($property['Property']['unit_id']))),
            array('key' => 'agent', 'label' => __('Assigned agent/owner selected'), 'done' => !empty($property['Property']['user_id'])),
            array('key' => 'amenities', 'label' => __('Amenities selected'), 'done' => $featureCount > 0),
            array('key' => 'documents', 'label' => __('Public document available'), 'done' => $documentCount > 0),
            array('key' => 'floorplans', 'label' => __('Floor plan available'), 'done' => $floorplanCount > 0),
            array('key' => 'seo', 'label' => __('SEO title and description added'), 'done' => (!empty($seo['PropertySeo']['meta_title']) && !empty($seo['PropertySeo']['meta_description'])))
        );
        $done = 0;
        $missing = array();
        foreach($checks as $check)
        {
            if(!empty($check['done']))
            {
                $done++;
            }
            else
            {
                $missing[] = $check['label'];
            }
        }
        $score = count($checks) > 0 ? (int)round(($done / count($checks)) * 100) : 0;
        return array('score' => $score, 'checks' => $checks, 'missing' => $missing);
    }

    protected function _saveQualitySnapshot($propertyId, $quality)
    {
        $this->loadModel('PropertyQualityCheck');
        $this->PropertyQualityCheck->create();
        return $this->PropertyQualityCheck->save(array('PropertyQualityCheck' => array(
            'property_id' => (int)$propertyId,
            'completeness_score' => (int)$quality['score'],
            'missing_items_json' => json_encode((array)$quality['missing']),
            'checked_by' => (int)$this->luserId,
            'created' => date('Y-m-d H:i:s')
        )), false);
    }

    protected function _scopedPropertyConditions()
    {
        return $this->CustomFunction->userWisePermission(null, $this->luserId, $this->userType, $this->agencyId, 'Property.project_id', 'Property.user_id');
    }

    public function index($id = null)
    {
        if($id)
        {
            return $this->redirect(array('action' => 'quality', (int)$id));
        }
        $this->loadModel('Property');
        $conditions = array($this->_scopedPropertyConditions());
        $latestProperties = $this->Property->find('all', array(
            'conditions' => $conditions,
            'fields' => array('Property.id', 'Property.uniq_id', 'Property.name', 'Property.price', 'Property.published', 'Property.latitude', 'Property.longitude'),
            'order' => array('Property.id' => 'desc'),
            'limit' => 12,
            'recursive' => -1
        ));
        $qualityRows = array();
        foreach($latestProperties as $propertyRow)
        {
            $fullProperty = $this->Property->find('first', array('conditions' => array('Property.id' => (int)$propertyRow['Property']['id']), 'recursive' => 1));
            if($fullProperty)
            {
                $qualityRows[] = array('property' => $propertyRow, 'quality' => $this->_qualityChecklist($fullProperty));
            }
        }
        $this->set('qualityRows', $qualityRows);
    }

    public function dashboard($id = null)
    {
        return $this->redirect(array('action' => 'index', $id));
    }

    public function quality($id = null)
    {
        $property = $this->_propertyOrRedirect($id);
        if(!$property)
        {
            return $this->redirect(array('controller' => 'Properties', 'action' => 'index'));
        }
        $quality = $this->_qualityChecklist($property);
        if($this->request->is(array('post', 'put')))
        {
            $this->_saveQualitySnapshot($id, $quality);
            $this->Session->setFlash(__('Property quality snapshot saved.'), 'flash', array('alert' => 'success'));
            return $this->redirect(array('action' => 'quality', $id));
        }
        $this->loadModel('PropertyQualityCheck');
        $snapshots = $this->PropertyQualityCheck->find('all', array(
            'conditions' => array('PropertyQualityCheck.property_id' => (int)$id),
            'order' => array('PropertyQualityCheck.created' => 'desc', 'PropertyQualityCheck.id' => 'desc'),
            'limit' => 10,
            'recursive' => -1
        ));
        $this->set(compact('property', 'quality', 'snapshots'));
    }

    public function seo($id = null)
    {
        $property = $this->_propertyOrRedirect($id);
        if(!$property)
        {
            return $this->redirect(array('controller' => 'Properties', 'action' => 'index'));
        }
        $this->loadModel('PropertySeo');
        $seo = $this->PropertySeo->find('first', array(
            'conditions' => array('PropertySeo.property_id' => (int)$id),
            'recursive' => -1
        ));
        if($this->request->is(array('post', 'put')))
        {
            $data = isset($this->request->data['PropertySeo']) ? $this->request->data['PropertySeo'] : array();
            $save = array(
                'property_id' => (int)$id,
                'slug' => $this->_propertySlug(!empty($data['slug']) ? $data['slug'] : $property['Property']['name'], $id),
                'meta_title' => trim((string)(isset($data['meta_title']) ? $data['meta_title'] : '')),
                'meta_description' => trim((string)(isset($data['meta_description']) ? $data['meta_description'] : '')),
                'canonical_url' => trim((string)(isset($data['canonical_url']) ? $data['canonical_url'] : '')),
                'schema_enabled' => !empty($data['schema_enabled']) ? 1 : 0
            );
            if(!empty($seo['PropertySeo']['id']))
            {
                $save['id'] = (int)$seo['PropertySeo']['id'];
            }
            $this->PropertySeo->create();
            if($this->PropertySeo->save(array('PropertySeo' => $save), false))
            {
                $this->Session->setFlash(__('SEO details saved.'), 'flash', array('alert' => 'success'));
                return $this->redirect(array('action' => 'seo', $id));
            }
            $this->Session->setFlash(__('SEO details could not be saved.'), 'flash', array('alert' => 'danger'));
        }
        if(!$this->request->data)
        {
            $this->request->data = !empty($seo) ? $seo : array('PropertySeo' => array(
                'property_id' => (int)$id,
                'slug' => $this->_propertySlug($property['Property']['name'], $id),
                'meta_title' => trim($property['Property']['name'].' - '.$this->siteName),
                'meta_description' => substr(trim(strip_tags((string)$property['Property']['description'])), 0, 155),
                'schema_enabled' => 1
            ));
        }
        $this->set(compact('property', 'seo'));
    }

    public function media($id = null)
    {
        $property = $this->_propertyOrRedirect($id);
        if(!$property)
        {
            return $this->redirect(array('controller' => 'Properties', 'action' => 'index'));
        }
        $this->loadModel('PropertiesPhoto');
        $this->loadModel('PropertiesDocument');
        $this->loadModel('PropertiesFloorplan');
        if($this->request->is(array('post', 'put')))
        {
            foreach(array('PropertiesPhoto', 'PropertiesDocument', 'PropertiesFloorplan') as $modelName)
            {
                if(empty($this->request->data[$modelName]) || !is_array($this->request->data[$modelName]))
                {
                    continue;
                }
                foreach($this->request->data[$modelName] as $rowId => $row)
                {
                    $rowId = (int)$rowId;
                    if($rowId <= 0)
                    {
                        continue;
                    }
                    $model = $this->{$modelName};
                    $existing = $model->find('first', array(
                        'conditions' => array($modelName.'.id' => $rowId, $modelName.'.property_id' => (int)$id),
                        'recursive' => -1
                    ));
                    if(empty($existing))
                    {
                        continue;
                    }
                    $save = array('id' => $rowId);
                    if($model->hasField('title'))$save['title'] = trim((string)(isset($row['title']) ? $row['title'] : ''));
                    if($model->hasField('alt_text'))$save['alt_text'] = trim((string)(isset($row['alt_text']) ? $row['alt_text'] : ''));
                    if($model->hasField('sort_order'))$save['sort_order'] = (int)(isset($row['sort_order']) ? $row['sort_order'] : 0);
                    if($modelName === 'PropertiesDocument' && $model->hasField('public_visible'))$save['public_visible'] = !empty($row['public_visible']) ? 1 : 0;
                    $model->create();
                    $model->save(array($modelName => $save), false);
                }
            }
            $this->Session->setFlash(__('Media details saved.'), 'flash', array('alert' => 'success'));
            return $this->redirect(array('action' => 'media', $id));
        }
        $photos = $this->PropertiesPhoto->find('all', array(
            'conditions' => array('PropertiesPhoto.property_id' => (int)$id),
            'order' => array('PropertiesPhoto.sort_order' => 'asc', 'PropertiesPhoto.id' => 'asc'),
            'recursive' => -1
        ));
        $documents = $this->PropertiesDocument->find('all', array(
            'conditions' => array('PropertiesDocument.property_id' => (int)$id),
            'order' => array('PropertiesDocument.sort_order' => 'asc', 'PropertiesDocument.id' => 'asc'),
            'recursive' => -1
        ));
        $floorplans = $this->PropertiesFloorplan->find('all', array(
            'conditions' => array('PropertiesFloorplan.property_id' => (int)$id),
            'order' => array('PropertiesFloorplan.sort_order' => 'asc', 'PropertiesFloorplan.id' => 'asc'),
            'recursive' => -1
        ));
        $this->set(compact('property', 'photos', 'documents', 'floorplans'));
    }

    public function leads($id = null)
    {
        $property = $this->_propertyOrRedirect($id);
        if(!$property)
        {
            return $this->redirect(array('controller' => 'Properties', 'action' => 'index'));
        }
        $this->loadModel('PropertyLeadClick');
        $clickSummary = $this->PropertyLeadClick->find('all', array(
            'fields' => array('PropertyLeadClick.click_type', 'COUNT(PropertyLeadClick.id) AS total'),
            'conditions' => array('PropertyLeadClick.property_id' => (int)$id),
            'group' => array('PropertyLeadClick.click_type'),
            'recursive' => -1
        ));
        $clickRows = $this->PropertyLeadClick->find('all', array(
            'conditions' => array('PropertyLeadClick.property_id' => (int)$id),
            'order' => array('PropertyLeadClick.created' => 'desc', 'PropertyLeadClick.id' => 'desc'),
            'limit' => 100,
            'recursive' => -1
        ));
        $this->set(compact('property', 'clickSummary', 'clickRows'));
    }

    public function pricehistory($id = null)
    {
        $property = $this->_propertyOrRedirect($id);
        if(!$property)
        {
            return $this->redirect(array('controller' => 'Properties', 'action' => 'index'));
        }
        $this->loadModel('PropertyPriceHistory');
        $priceRows = $this->PropertyPriceHistory->find('all', array(
            'conditions' => array('PropertyPriceHistory.property_id' => (int)$id),
            'order' => array('PropertyPriceHistory.created' => 'desc', 'PropertyPriceHistory.id' => 'desc'),
            'recursive' => -1
        ));
        $this->set(compact('property', 'priceRows'));
    }

    public function savedsearches()
    {
        $this->loadModel('PropertySavedSearch');
        $savedSearches = $this->PropertySavedSearch->find('all', array(
            'order' => array('PropertySavedSearch.created' => 'desc', 'PropertySavedSearch.id' => 'desc'),
            'limit' => 100,
            'recursive' => -1
        ));
        $this->set(compact('savedSearches'));
    }

    public function missinglocations()
    {
        $this->loadModel('Property');
        $conditions = array(
            $this->_scopedPropertyConditions(),
            'OR' => array(
                array('Property.latitude' => null),
                array('Property.latitude' => ''),
                array('Property.latitude' => '0'),
                array('Property.longitude' => null),
                array('Property.longitude' => ''),
                array('Property.longitude' => '0')
            )
        );
        $properties = $this->Property->find('all', array(
            'conditions' => $conditions,
            'fields' => array('Property.id', 'Property.uniq_id', 'Property.name', 'Property.address', 'Property.latitude', 'Property.longitude', 'Property.published'),
            'order' => array('Property.id' => 'desc'),
            'limit' => 200,
            'recursive' => -1
        ));
        $this->set(compact('properties'));
    }
}
?>
